<?php

namespace app\v1\user\v0\logic;

use app\v1\common\logic\Alarm;
use app\v1\common\logic\Base;
use app\v1\job\v0\logic\JobController;
use app\v1\common\logic\Log;
use app\v1\opcode\NodeOpcode;
use app\v1\report\v0\logic\Virus;
use app\v1\system\v0\logic\Notice;
use app\v1\tenant\v0\logic\Tenant;
use app\v1\common\logic\Report as ReportHandler;

/**
 * note          用戶管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/6 10:56
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends Base
{
    private $sourceArr = [];

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->sourceArr = xphp_get_config('resource', 'RESOURCE_DIS_TYPE');
    }

    /**
     * 获取用户列表
     * @params
     *  offset. limit
     * sort, order
     * sort 的值 user_name user_type tenant_uuid create_time create_user_name email telephone last_login_time lock_flag
     * @param array $params 参数
     * @return array
     */
    public function getUserList($params = []): array
    {
        // 查询用户关联的所有角色的类型
        $buildSql = "(select GROUP_CONCAT(config) from bd_role where role_uuid in (
										select role_uuid from mt_user_role where user_uuid = bu.user_uuid
										union
										(
											select role_uuid from mt_user_group_role where user_group_uuid in 
											(SELECT user_group_uuid from
											 mt_user_user_group where user_uuid = bu.user_uuid)
										)
									)
								) roles";
        $sql = "select distinct bu.user_uuid, bu.user_name, bu.user_type,
                 unix_timestamp(bu.create_time) create_time, bu.create_user_name, bu.email, bu.telephone, 
            unix_timestamp(bu.last_login_time) last_login_time, bu.lock_flag,bu.manager_uuid, bu.create_user_uuid,
                bu2.user_name manager_name, mut.tenant_uuid, burt.`status`, burt.id resource_id,bt.config,bue.quota,
                (select GROUP_CONCAT(mur.role_uuid) from  mt_user_role mur where mur.user_uuid = bu.user_uuid) role_uuid
                ,{$buildSql}
                from bd_user bu
                     left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid
                     left join bd_user bu2 on bu.manager_uuid = bu2.user_uuid 
                     left join bd_user_resource_transfer burt on 
                     burt.source_user_uuid = bu.user_uuid or burt.target_user_uuid = bu.user_uuid
										 left join bd_tenant bt on
										 bt.tenant_uuid = mut.tenant_uuid
										 left join bd_user_extension bue on bu.user_uuid = bue.user_uuid";
        $sqlCount = "select count(distinct bu.id) as total from bd_user bu ";

        $sqlParams = [];
        $where = '';

        $user = xphp_get_user_info();
        $userId = xphp_get_config('three_powers', 'INIT_USER_LIST');
        if (!xphp_three_powers()) {
            // 不是三权 那么不显示默认的三权用户
            $where .= " where bu.user_level not in (2,3,4,5)";
            /*if ($params['allocation']) {
                // 表示分配 那么不显示默认的三权用户
                $where .= " where bu.user_level not in (2,3,4,5)";
            } else {
                $where .= " where bu.id > 0 ";
            }*/
            $checkAuth = v1_auth_is_admin();
            if ($user['userLevel'] != 1 && !$checkAuth['global_observer']) {
                //如果不是超级管理员,显示自己下级或管理的用户
                $useruuid = $user['userUuid'];
                $where .= " and (bu.create_user_uuid = ? or bu.manager_uuid = ? ) ";
                $sqlParams = array($useruuid, $useruuid);
                // admin管理也不展示
                $where .= ' and bu.user_type != 3 ';
            }
        } else {
            // 是三权 那么根据用户的level来显示对应的用户
            if ($user['userLevel'] == xphp_get_config('three_powers', 'THREE_POWERS_USER')['sysadmin']) {
                // 系统管理员 只能查看level 为 2,4,5的用户
                $where .= " where bu.user_level in (2,4,5)";
            } elseif ($user['userLevel'] == xphp_get_config('three_powers', 'THREE_POWERS_USER')['safeadmin']) {
                // 安全管理员 可以查看 2,3,4,5的用户
                $where .= " where bu.user_level in (2,3,4,5)";
            } else {
                // 审计员和操作员只能查看自身的用户列表
                $where .= " where bu.user_level = " . $user['userLevel'];
            }
        }

        if (!empty($params['search'])) {
            // 有搜索
            $where .= ' and bu.user_name like ?';
            $sqlParams = array_merge($sqlParams, array('%' . $params['search'] . '%'));
        }

        $count = $this->dbSelect($sqlCount . $where, $sqlParams);
        $rows = [];
        $num = intval($count[0]['total']);
        if ($num) {
            $where .= ' group by bu.user_uuid ';
            if ((!empty($params['sort']) && in_array(strtolower($params['order']), ['asc', 'desc']))) {
                // 排序
                if ($params['sort'] == 'tenant_uuid') {
                    $params['sort'] = 'mut.' . $params['sort'];
                } else {
                    $params['sort'] = 'bu.' . $params['sort'];
                }
                $where .= " order by " .  $params['sort'] . ' ' . $params['order'];
            }
            $where .= ' limit ? , ?';
            $sqlParams = array_merge($sqlParams, array($params['offset'], $params['limit']));

            $users = $this->dbSelect($sql . $where, $sqlParams);

            $i = 0;
            $statusArr = [
                1 => xphp_get_lang('UI_TENANT_BUTTON_ENABLE'),
                2 => xphp_get_lang('UI_TENANT_BUTTON_DISABLE'),
            ];
            $userIdArr = array_merge($userId, ['a508b813-19c7-eb4e-d6fa-bb61b25a4de9']);

            $userTypeDes = [
                1 => xphp_get_lang('UI_USER_LOCATION'),
                2 => xphp_get_lang('UI_USER_EXTERNAL'),
                3 => xphp_get_lang('UI_USER_LOCATION'),
            ];
            foreach ($users as $user) {
                $i++;
                $tenantInfo = $this->pGetTenantInfoByUser($user['user_uuid']);
                $tenantName = empty($tenantInfo) ? '--' : $tenantInfo['tenantname'];
                //租户中的用户需要显示为租户\用户名
                $userName = empty($tenantInfo) ? $user['user_name'] : $user['user_name'] . '(' . $tenantName . ')';
                // 存在资源转移过程 并且状态值不为 2（完成） 那么就表示转移中
                $istransfer = (!empty($user['resource_id']) && $user['status'] != 2);
                $backupData = '--';
                $quota = $user['quota'] == -1 ? xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED') : v1_calSize($user['quota'], true);
                if(!empty($tenantInfo)){
                    // 租户
                    // 已备份数据
                    $backupData = v1_calSize((new Tenant())->getTenantBackupData($user['tenant_uuid']), true) . '/' . $quota;
                }else{
                    // 非租户
                    $backupData = v1_calSize($this->getUserBackupData($user['user_uuid']),true) . '/' . $quota;
                }

                // 如果当前用户不是admin，需要判断下它的子用户是不是全局观察者....
                $roles = array_unique(explode(',', $user['roles']));
                // 全局观察者能看到所有的用户，但是不能管理admin创建或管理的
                $is_global_observer_flag = false;
                $is_global_observer_manager_flag = false;

                if($checkAuth['global_observer']){
                    $is_global_observer_flag = true;
                    if($user['create_user_uuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9' && $user['manager_uuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9' && $user['user_uuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9'){
                        $is_global_observer_manager_flag = true;
                    }
                }
                $rows[] = array(
                    'user_uuid'         =>  $user['user_uuid'],
                    'tenant_uuid'       =>  $user['tenant_uuid'] ?? '',
                    'num'               =>  $params['offset'] + $i,
                    'user_name'         =>  $userName,
                    'user_type_des'     =>  $userTypeDes[$user['user_type']] ?? '',
                    'user_type'         =>  $user['user_type'],
                    'tenant_name'       =>  $tenantName,
                    'create_time'       =>  $this->parseDate($user['create_time']),
                    'create_user_name'  =>  $user['create_user_name'],
                    'email'             =>  $user['email'] ?? '',
                    'telephone'         =>  $user['telephone'] ?? '',
                    'last_login_time'   =>  $this->parseDate($user['last_login_time']),
                    'lock_flag'         =>  $user['lock_flag'],
                    'manager_name'      =>  $user['manager_name'] ?? '',
                    'status'            =>  $statusArr[$user['lock_flag']],
                    'checked'           =>  !in_array($user['user_uuid'], $userIdArr) && !$istransfer,
                    'is_transfer'       =>  $istransfer,
                    'role_uuid' => !empty($user['role_uuid']) ? explode(',', $user['role_uuid']) : [],
                    'backup_data'       =>  $backupData,
                    'create_user_uuid'  =>  $user['create_user_uuid'],
                    // 标记是否可操作，针对非admin用户的子用户是否是全局观察者
                    'is_global' => in_array(2, $roles),
                    'login_user_uuid' => $_SESSION['userUuid'],
                    'is_global_observer_flag' => $is_global_observer_flag,  //登录的当前用户
                    'is_global_observer_manager_flag' => $is_global_observer_manager_flag
                );
            }
        }
        return [
            'rows' => $rows,
            'total' => $num
        ];
    }

    /**
     * 添加用户
     *  {"username":"biil2",
     * "password":"fe01be1a17ab56905145e1ea8da4f253",
     * "email":"",
     * "phone":"",
     * "usertype":"1",
     * "domainuuid":"",
     * "roleList":[],
     * "userGroupList":[],
     * "quota":-1}
     * @param array $params 参数
     * @return array
     */
    public function addUser($params = [])
    {
        $username = $params['username'];
        $password = v1_decrypt_js_rsa($params['password']);
        $MD5_password = md5($password);
        $email = $params['email'];
        $phone = $params['phone'];
        $customePassword = $params['customePassword'];
        $position = $params['position'];
        //角色列表
        $roleList = $params['role_list'];
        $userGroupList = $params['user_group_list'];

        $user = xphp_get_user_info();
        //如果是租户内部创建
        $tenantuuid = !empty($user['tenantuuid']) ? $user['tenantuuid'] : '';

        $domainuuid = $params['domainuuid'];
        //1本地用户 2外部用户
        $userType = $params['usertype'];

        $permission = '';
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $createTime = date($dateformat);
        $createUserName = $user['userName'];
        $createUserUUID = $user['userUuid'];
        $language = $user['language'];
        //用户配额
        $quota = intval($params['quota']);

        //检查是否为外部用户
        if ($userType == 2) {
            //外部用户添加先检查用户是否存在,存在获取
            $result = $this->checkDomainUserExist($username, $domainuuid);
            if (is_array($result)) {
                return $result;
            }
            $permission = '';
            $tenantuuid = $this->getDomainTenantuuid($domainuuid);
        }

        //修改密码flag
        $editPwdFlag = v1_parse_bool_to_flag($params['force_edit_pass']);

        //生成一个UUID
        $userUUID = xphp_uuid();
        //添加用户和用户组关联
        if (!empty($userGroupList)) {
            $this->pAddUserUserGroup($userUUID, $userGroupList);
        }
        //添加用户和角色关联
        // 如果不是三权 那么默认的user_level给定为0，否则给5
        $userLevel = 0; // 为了隔离开用户
        // 这里需要对三权模式下进行判断
        if (xphp_three_powers()) {
            $threePowersUser = xphp_get_config('three_powers', 'THREE_POWERS_USER');
            // 安全员和审计员可以添加同级别角色的用户
            if (in_array($user['userLevel'], [$threePowersUser['safeadmin'],$threePowersUser['auditor']])) {
                $roleList = [xphp_get_config('three_powers', 'INIT_ROLE_LIST')[$user['userLevel']]];
                $userLevel = $user['userLevel'];
            } else {
                $userLevel = 5; // 默认都是操作员
            }
        }
        if (!empty($roleList)) {
            $this->pAddUserRole($userUUID, $roleList);
        }
        //添加用户租户关联
        if (!empty($tenantuuid)) {
            $this->pAddUserTenant(array($userUUID), $tenantuuid);
        }
        $manageuuid = $managelist = '';
        // 添加用户和管理用户关联
        if (!empty($params['manage_uuid']) && !empty($params['manage_list'])) {
            // 都不为空的情况
            $managelist = ',' . implode(',', $params['manage_list']) . ',';
            $manageuuid = $params['manage_uuid'];
        };

        //生成用户唯一认证码
        $code = xphp_random_code(4);
        if ($this->checkCodeExist($code)) {
            //验证码重复，需要重新生成
            $code = xphp_random_code(4);
        }


        $sqlExtentionParams = array($userUUID, $quota);
        $sqlParam = [
            $userUUID, $username, $MD5_password, $email, $phone, $userType, $permission, $createTime,
            $createUserName, $createUserUUID, $editPwdFlag, $language, $domainuuid, $userLevel, $manageuuid, $managelist, $code, $customePassword, $position
        ];

        $sql = "insert bd_user (user_uuid, user_name, password, email, telephone, user_type, permission, 
            create_time, create_user_name, create_user_uuid,force_password_change_flag,
             language, domain_uuid, user_level,manager_uuid,manager_auth, auth_code, custome_password, position)
              values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $sqlExtention = "insert bd_user_extension (user_uuid,quota) values (?, ?)";

        $this->dbBeginTransaction(); //事务
        $result = $this->dbQuery($sql, $sqlParam);
        $result = $result && $this->dbQuery($sqlExtention, $sqlExtentionParams);
        if ($result) {
            $this->dbCommit();
            $this->systemLog('SYSTEM_USER_ADD_SUCCESS', array($params['username']));
            $msg = xphp_get_lang('WEB_USERS_ADD_USER') . xphp_get_lang('WEB_PUBLIC_SUCCESS');
            // 如果是三权模式下并且是系统管理员添加用户 这里提示 需要安全管理员分配角色后方可登录
            if (xphp_three_powers() && $user['userLevel'] == 2) {
                $msg .= ',' . xphp_get_lang('WEB_USERS_ADD_USER_SHOW_TIPS');
            }
            return $msg;
        } else {
            $this->dbRollBack();
            return false;
        }
    }

    /**
     * 获取用户信息
     * @param string $userUUID 用户uuid
     * @return array
     */
    public function getUser(string $userUUID): array
    {

        $sql = "select bu.user_uuid, bu.user_name, bu.password, bu.email, bu.telephone, bu.user_type,
                        bu.user_level,bu.permission, bu.domain_uuid, bt.tenant_uuid,bu.manager_uuid,bu.manager_auth,bu.custome_password,bu.position
                from bd_user bu
                left join bd_tenant bt on bt.admin_uuid = bu.user_uuid
                where bu.user_uuid = ?";
        $data = $this->dbSelect($sql, array($userUUID));

        $sqlExtension = "select quota from bd_user_extension where user_uuid = ?";
        $dataExtension = $this->dbSelect($sqlExtension, array($userUUID));

        if (empty($data)) {
            return [];
        }

        $tenantInfo = $this->getUserTenantInfo($userUUID);
        $tenantuuid = !empty($data[0]['tenant_uuid']) ? $data[0]['tenant_uuid'] : '';

        // 需要判断下用户组是否全局观察者
        $isSuperRole = false;
        if (!empty($tenantInfo['role_list'])) {
            $roleStr = implode("','", $tenantInfo['role_list']);
            $role = $this->dbSelect("select config from bd_role where role_uuid in ('{$roleStr}')");
            if (in_array(2, array_column($role, 'config'))) {
                $isSuperRole = true;
            }
        }

        // 关联管理权限
        $managelist = !empty($data[0]['manager_auth']) ?
            array_values(array_filter(explode(',', $data[0]['manager_auth']))) : [];
        return [
            'user_uuid' => $data[0]['user_uuid'],
            'user_name' => $data[0]['user_name'],
            'password' => $data[0]['password'],
            'email' => $data[0]['email'],
            'telephone' => $data[0]['telephone'],
            'usertype' => intval($data[0]['user_type']),
            'userLevel' => intval($data[0]['user_level']),
            'userpermission' => '',
            'quota' => $dataExtension[0]['quota'] ?? 0,
            'tenantuuid' => $tenantuuid,
            "usergroup_list" => $tenantInfo['usergroup_list'],
            'role_list' => $tenantInfo['role_list'],
            'domainuuid' => $data[0]['domain_uuid'],
            'manage_uuid' => $data[0]['manager_uuid'],
            'manage_list' => $managelist,
            'custome_password' => $data[0]['custome_password'],
            'position' => $data[0]['position'],
            'is_super_role' => $isSuperRole
        ];
    }

    /**
     * 获取当前登录用户的密码
     * @return array
     */
    public function getUserPassword(): array
    {
        $user = xphp_get_user_info();
        $sql = "select password from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($user['userUuid']));
        $password = !empty($data) ? $data[0]['password'] : 'vinchin';

        return [
            'user_uuid'  => $user['userUuid'],
            'password'  => $password,
        ];
    }

    /**
     * 编辑用户信息
     * @param array $params 参数
     * @return string|boolean
     */
    public function editUser(array $params)
    {
        $userName = $params['username'];
        $userUUID = $params['users_uuid'];
        $password = $params['password'];
        $email = $params['email'];
        $phone = $params['phone'];
        $customPassword = $params['customePassword'];
        $position = $params['position'];
        //角色列表
        $roleList = $params['role_list'];

        //用户组列表
        $userGroupList = $params['user_group_list'];
        $permission = '';
        $quota = $params['quota'];

        $sql = "select password, user_uuid, email, telephone, user_level, `position` from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($userUUID));

        $sql2 = "select quota from bd_user_extension where user_uuid = ?";
        $data2 = $this->dbSelect($sql2, array($userUUID));

        // 是否能够更改用户组和角色
        $canChangeRu = true;
        if ((!xphp_three_powers() && $data[0]['user_level'] > 1) || xphp_three_powers()) {
            // 因为admin可以查看所有的用户，那么也就包括修改了，所以非三权模式下，不能更改三权用户的角色和用户组
            $canChangeRu = false;
        }

        if ($canChangeRu) {
            //修改前先删除之前关联用户组|角色|租户
            $this->pDeleteUserUserGroup($userUUID);
            $this->pDeleteUserRole($userUUID);
        }

        //获取原始密码比对,如果原始密码的MD5的MD5和新的密码一样,就使用原来的密码,反之用新密码
        //两次MD5是因为发送到界面有一次MD5,然后界面发送回来还有一次MD5
        if ($data) {
            $password = ($password == (md5($data[0]['password']))) ? $data[0]['password'] : $password;
        }
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $editTime = date($dateformat);

        $sqlParam = array($userName, $password, $email, $phone, $permission, $editTime, $customPassword, $position, $userUUID);
        $sql = "update bd_user set user_name = ?, password = ?, email = ?, telephone = ?, permission = ?,
                   create_time = ?, custome_password = ?, position = ?
                where user_uuid = ?";



        $sqlExtension = "update bd_user_extension set quota = ? where user_uuid = ?";

        $this->dbBeginTransaction(); //事务
        $result = $this->dbExec($sql, $sqlParam);
        $result = $result && $this->dbExec($sqlExtension, array($quota, $userUUID));
        if ($result) {
            //添加用户和用户组关联
            $canChangeRu && $this->pAddUserUserGroup($userUUID, $userGroupList);
            //添加用户和角色关联
            // 这里需要对三权模式下进行判断
            if (xphp_three_powers()) {
                $user = xphp_get_user_info();
                $threePowersUser = xphp_get_config('three_powers', 'THREE_POWERS_USER');
                // 安全员和审计员可以添加同级别角色的用户
                if (in_array($user['userLevel'], [$threePowersUser['safeadmin'],$threePowersUser['auditor']])) {
                    $roleList = [xphp_get_config('three_powers', 'INIT_ROLE_LIST')[$user['userLevel']]];
                }
            }
            $canChangeRu && $this->pAddUserRole($userUUID, $roleList);
            $this->dbCommit();
            // 需要判断下修改了用户的什么信息
            $chaneMsg = [];
            if ($data[0]['email'] != $email) {
                $chaneMsg[] = xphp_get_lang('UI_USER_EMAIL');
            }
            if ($data[0]['telephone'] != $phone) {
                $chaneMsg[] = xphp_get_lang('UI_USER_PHONE');
            }
            if ($data2[0]['quota'] != $quota) {
                $chaneMsg[] = xphp_get_lang('UI_USER_BACKUP_STORAGE_CAPACITY');
            }
            if ($data[0]['password'] != $password) {
                $chaneMsg[] = xphp_get_lang('UI_LOGIN_PASSWORD');
            }
            if ($data[0]['position'] != $position) {
                $chaneMsg[] = xphp_get_lang('UI_USER_POSITION');
            }
            $msgs = !empty($chaneMsg) ? ' "' . implode('，', $chaneMsg) . '"' : '';
            $this->systemLog('SYSTEM_USER_MODIFY_SUCCESS', array($params['username'] . $msgs));
            return xphp_get_lang('UI_PLATFORM_EDIT_USER')  . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        } else {
            $this->dbRollBack();
            return false;
        }
    }

    /**
     * 解锁用户
     * @param array $params 用户uuid集合
     * @return bool
     */
    public function unlockUser(array $params): bool
    {

        $usersArr = "'" . implode("','", $params['users']) . "'";

        $this->dbBeginTransaction();

        $sql = "update bd_user set lock_flag = 1,login_error_count = 0 where user_uuid in ( {$usersArr} ) ";

        if (!$this->dbExec($sql, [])) {
            $this->dbRollBack();
            return false;
        }

        $this->dbCommit();

        $userName = $this->getUsername($params['users']);

        $this->systemLog('SYSTEM_USER_UNLOCK_SUCCESS', array($userName));

        return true;
    }

    /**
     * 锁定用户
     * @param array $params 参数
     * @return bool
     */
    public function lockUser(array $params): bool
    {
        //禁用用户强制停止任务
        $this->lockUserStopJob($params['users']);

        $usersArr = "'" . implode("','", $params['users']) . "'";

        $this->dbBeginTransaction();

        $sql = "update bd_user set lock_flag = 2,login_error_count = 0 where user_uuid in ( {$usersArr} )";
        if (!$this->dbExec($sql, [])) {
            $this->dbRollBack();
            return false;
        }

        $this->dbCommit();

        $userName = $this->getUsername($params['users']);

        $this->systemLog('SYSTEM_USER_LOCK_SUCCESS', array($userName));

        return true;
    }

    /**
     * 删除用户
     * @param array $params 参数
     * @return bool
     */
    public function delUser(array $params): bool
    {

        $users = $params['users'];
        // 删除前置判断
        if (!$this->deleteCheck($users)) {
            return false;
        }

        // 判断用户是否还存在资源 存在则不能删除
        // 资源包括 bd_backup_timepoint 表， mt_user_resource 和 mt_user_resource_group
        $userIdStr = "'" . implode("','", $users) . "'";
        $sql = "SELECT id FROM bd_backup_timepoint where user_uuid in ({$userIdStr})
union all
SELECT id from mt_user_resource where user_uuid in ({$userIdStr})
union all
SELECT id from mt_user_resource_group where user_uuid in ({$userIdStr})";
        $check = $this->dbSelect($sql);
        if (!empty($check)) {
            // 有资源存在不能删除，提示先转移资源再删除
            $this->muOpResult(
                false,
                xphp_get_lang('WEB_USERS_DELETE_USER'),
                xphp_get_lang('WEB_ERROR_NODE_SERVER_USER_RESOURCE_EXISTS_ERROR')
            );
        }

        $userIDs = $this->getDeleteUserIDs($users);
        $userName = $this->getUsername($users);

        $this->deleteUserCheck($userIDs);

        //事务外先仔细发送到后台的操作
        $logHandler = new Log();
        $alarmHandler = new Alarm();
        foreach ($users as $user) {
            //删除用户相关历史任务，任务日志和任务告警
            $this->pDeleteUserHistoryTask($user);
            $this->pDeleteUserTaskLog($user, $logHandler);
            $this->pDeleteUserSystemLog($user, $logHandler);
            $this->pDeleteUserTaskAlarm($user, $alarmHandler);
        }

        $this->dbBeginTransaction();

        foreach ($users as $user) {
            $flag = $this->checkDeleteUserAvailable($user);
            if (!$flag) {
                return false;
            }
            //删除用户前删除用户与 用户组|角色|租户关联
            $this->pDeleteUserUserGroup($user);
            $this->pDeleteUserRole($user);
            $this->pDeleteUserTenant($user);

            //删除用户与资源的关联
            $this->pDeleteUserAllResource($user);
            $this->pDeleteUserAllResourceGroup($user);

            //删除用于创建的 用户组 资源组 角色等
            $this->pDeleteUserRoleAndUserCreate($user);
            $this->pDeleteUserResourceGroupAndUserCreate($user);
            $this->pDeleteUserGroupAndUserCreate($user);

            // 删除用户的配额信息
            $sql = "delete from bd_user_extension where user_uuid = ?";
            $this->dbQuery($sql, array($user));

            $sql = "delete from bd_user where user_uuid = ?";
            if (!$this->dbQuery($sql, array($user))) {
                $this->dbRollBack();
                return false;
            }
        }
        $this->dbCommit();

        $this->systemLog('SYSTEM_USER_DELETE_SUCCESS', array($userName));
        return true;
    }

    /**
     * 组装下获取资源的sql联合语句
     * @param string $field     查询的字段
     * @param string $usersUuid 用户uuid
     * @param string $join      额外的join查询语句
     * @param string $where     额外的where限制语句
     * @return array
     */
    private function makeSqlUnion(string $field, string $usersUuid, $join = '', $where = '')
    {
        // 获取的资源
        if ($join) {
            $join = ' left join vm_vcenter vv on vv.vcenter_uuid = vt.vcenter_uuid
             left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid';
        }
        $sql3 = ' mt_user_resource mur,vm_tree vt ' . $join . '
             where mur.resource_type = 3 and mur.resource_uuid = vt.uuid and mur.user_uuid = ? ' . $where;
        if ($join) {
            $join = ' left join mt_user_tenant mut on ba.user_uuid = mut.user_uuid';
        }
        $sql2 = ' mt_user_resource mur,bd_agent ba ' . $join . '
	                where mur.resource_type = 2 and mur.resource_uuid = ba.agent_uuid and mur.user_uuid = ?' . $where;
        $sql7 = ' bd_node bn,mt_user_resource mur
                    where mur.resource_type = 7 and mur.resource_uuid = bn.node_uuid and mur.user_uuid = ? ';
        if ($join) {
            $join = ' left join mt_user_tenant mut on bsr.user_uuid = mut.user_uuid';
        }
        $sql8 = ' mt_user_resource mur,bd_storage_resource bsr ' . $join . '
	              where mur.resource_type = 8 and mur.resource_uuid = bsr.storage_uuid and mur.user_uuid = ?' . $where;
        if ($join) {
            $join = ' left join mt_user_tenant mut on ba.user_uuid = mut.user_uuid';
        }
        $sql10 = ' mt_user_resource mur,bd_agent ba ' . $join . '
	                where mur.resource_type = 10 and mur.resource_uuid = ba.agent_uuid and mur.user_uuid = ?' . $where;
        if ($join) {
            $join = ' left join mt_user_tenant mut on mo.user_uuid = mut.user_uuid';
        }
        $sql56 = ' mt_user_resource mur,m365_organization mo ' . $join . '
	          where mur.resource_type = 56 and mur.resource_uuid = mo.organization_uuid and mur.user_uuid = ?' . $where;
        if ($join) {
            $join = ' left join mt_user_tenant mut on nsr.user_uuid = mut.user_uuid';
        }
        $sql57 = ' mt_user_resource mur,nas_storage_resource nsr ' . $join . '
	                where mur.resource_type = 57 and mur.resource_uuid = nsr.nas_uuid and mur.user_uuid = ?' . $where;
        if ($join) {
            $join = ' left join vm_vcenter vv on vv.vcenter_uuid = vt.vcenter_uuid
             left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid';
        }
        $sql58 = ' mt_user_resource mur,vm_tree vt ' . $join . '
	                where mur.resource_type = 58 and mur.resource_uuid = vt.uuid and mur.user_uuid = ?' . $where;
        if ($join) {
            $join = ' left join mt_user_tenant mut on ors.user_uuid = mut.user_uuid';
        }
        $sql59 = ' mt_user_resource mur,obs_resource ors ' . $join . '
	                where mur.resource_type = 59 and mur.resource_uuid = ors.obs_uuid and mur.user_uuid = ?' . $where;
        if ($join) {
            $join = ' left join mt_user_tenant mut on hc.user_uuid = mut.user_uuid';
        }
        $sql60 = ' mt_user_resource mur,hadoop_cluster hc ' . $join . '
	        where mur.resource_type = 60 and mur.resource_uuid = hc.hadoop_cluster_uuid and mur.user_uuid = ?' . $where;
        if ($join) {
            $join = ' left join vm_vcenter vv on vv.vcenter_uuid = vt.vcenter_uuid
             left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid';
        }
        $sql61 = ' mt_user_resource mur,vm_tree vt ' . $join . '
	                where mur.resource_type = 61 and mur.resource_uuid = vt.uuid and mur.user_uuid = ?' . $where;
        if ($join) {
            $join = 'left join mt_user_tenant mut on kc.user_uuid = mut.user_uuid';
        }
        $sql62 = ' mt_user_resource mur,kube_cluster kc ' . $join . '
	        where mur.resource_type = 62 and mur.resource_uuid = kc.cluster_uuid and mur.user_uuid = ?' . $where;
        $sqlArr = [
            $sql3,
            $sql2,
            $sql7,
            $sql8,
            $sql10,
            $sql56,
            $sql57,
            $sql58,
            $sql59,
            $sql60,
            $sql61,
            $sql62
        ];
        $sql = $sqlparms = [];
        foreach ($sqlArr as $itm) {
            $sql[] = " SELECT {$field} FROM " . $itm;
            $sqlparms[] = $usersUuid;
        }
        $sql = implode('  union all ', $sql);
        return  [
            $sql,
            $sqlparms
        ];
    }

    /**
     * 获取用户资源/组列表 只和用户挂钩，不和用户组关联
     * @param array $params 参数
     * @return array
     */
    public function getResource(array $params)
    {
        $params['type'] = $params['type'] ?? 1;
        $sqlparms = [$params['users_uuid']];
        $user = xphp_get_user_info();
        $join = $where = '';
        if ($params['type'] == 2) {
            if (in_array($user['userLevel'], [1, 2, 3])) {
                // 超级管理员、系统管理员和安全管理不能看租户创建的
                $join = ' left join mt_user_tenant mut on brg.create_user_uuid = mut.user_uuid ';
                $where = ' and mut.tenant_uuid is NULL ';
            }
            // 表示获取的资源组
            $sql = "SELECT DISTINCT brg.resource_group_uuid source_uuid,
                mtrg.resource_type source_type,brg.resource_group_name source_name
                FROM bd_resource_group brg
	            join mt_user_resource_group murg on murg.resource_group_uuid = brg.resource_group_uuid 
	            left join mt_resource_resource_group mtrg on mtrg.resource_group_uuid = brg.resource_group_uuid
                {$join}
	            where murg.user_uuid = ? {$where}";
            $sqlcount = "select count(distinct murg.resource_group_uuid) total
                                from bd_resource_group brg
	            join mt_user_resource_group murg on murg.resource_group_uuid = brg.resource_group_uuid 
	             {$join} where murg.user_uuid = ? $where";
            $count = $this->dbSelect($sqlcount, $sqlparms);
            $orderArr = [
                'source_name' => 'brg.resource_group_name',
                'source_type' => 'mtrg.resource_type',
            ];
        } else {
            if (in_array($user['userLevel'], [1, 2, 3])) {
                // 超级管理员、系统管理员和安全管理不能看租户创建的
                $join = ' left join mt_user_tenant mut on mur.user_uuid = mut.user_uuid ';
                $where = ' and mut.tenant_uuid is NULL ';
            }
            $field = 'DISTINCT mur.resource_uuid,mur.vm_uuid,mur.vcenter_uuid,mur.resource_type source_type';
            $sqlArr = $this->makeSqlUnion($field, $params['users_uuid'], $join, $where);
            $sql = $sqlArr[0];
            $sqlparms = $sqlArr[1];
            // 1.先在 mt_user_resource 根据user_uuid获取resource_uuid vcenter_uuid resource_type
            $orderArr = [
                'source_name' => 'mur.resource_uuid',
                'source_type' => 'mur.resource_type',
            ];
            $field = 'count(DISTINCT mur.resource_uuid) as num';
            $sqlcountArr = $this->makeSqlUnion($field, $params['users_uuid'], $join, $where);
            $sqlcount = $sqlcountArr[0];

            $sqlcount = "SELECT sum(num) total from ($sqlcount) s";
            $count = $this->dbSelect($sqlcount, $sqlparms);
        }

        $total = $count[0]['total'] ?? 0;
        if (empty($total)) {
            return [
                'rows' => [],
                'total' => 0,
            ];
        }
        if ((!empty($params['sort']) && in_array(strtolower($params['order']), ['asc', 'desc']))) {
            // 排序
            if (!empty($orderArr[$params['sort']])) {
                $sql .= " order by " .  $orderArr[$params['sort']] . ' ' . $params['order'];
            }
        }
        $sql .= ' limit ? , ? ';
        $sqlParams = array_merge($sqlparms, array($params['offset'], $params['limit']));
        $list = $this->dbSelect($sql, $sqlParams);
        $lists = [];
        if ($params['type'] == 1) {
            // 处理下资源的信息
            // 2,查询出对应的名称
            // 2.1 虚拟机的话需要关联 vm_tree 查询虚拟机名称 name 根据 uuid 和vm_vcenter里面查询 vcenter_ip 根据 vcenter_uuid 3
            // 2.2 传输代理 在 bd_appliance 里面查询出 nickname 和 ip 根据 appliance_uuid 2
            // 2.3 备份节点 在 bd_node 里面查询 host_name 根据 node_uuid 7
            // 2.4 存储资源 在 bd_storage_resource 查询出 storage_nickname 根据 storage_uuid 8
            // 2.5 客户端 在 bd_agent 查询出 agent_name 和 ip 根据 agent_uuid 10
            foreach ($list as $item) {
                if (in_array($item['source_type'], [3, 58, 61])) {
                    // 虚拟机 和公有云
                    $sqls = "select vt.name, vv.vcenter_ip
                            from vm_tree vt left join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid
                            where vt.vcenter_uuid = ? and vt.uuid = ? ";
                    $names = $this->dbSelect($sqls, [$item['vcenter_uuid'], $item['vm_uuid']]);
                    $name = $names[0]['name'] . '(' . $names[0]['vcenter_ip'] . ')';
                    $item['resource_uuid'] = $item['resource_uuid'] . '|' . $item['vcenter_uuid'];
                } elseif ($item['source_type'] == 7) {
                    // 备份节点
                    $sqls = "select host_name,ip from bd_node where node_uuid = ?";
                    $names = $this->dbSelect($sqls, [$item['resource_uuid']]);
                    $name = $names[0]['host_name'] . '(' . $names[0]['ip'] . ')';
                } elseif ($item['source_type'] == 8) {
                    // 存储资源
                    $sqls = "select storage_nickname from bd_storage_resource where storage_uuid = ?";
                    $names = $this->dbSelect($sqls, [$item['resource_uuid']]);
                    $name = $names[0]['storage_nickname'];
                } elseif ($item['source_type'] == 56) {
                    // 组织管理
                    $sqls = "select organization_name from m365_organization where organization_uuid = ?";
                    $names = $this->dbSelect($sqls, [$item['resource_uuid']]);
                    $name = $names[0]['organization_name'];
                } elseif ($item['source_type'] == 57) {
                    // nas设备
                    $sqls = "select nas_nickname,share_path from nas_storage_resource where nas_uuid = ?";
                    $names = $this->dbSelect($sqls, [$item['resource_uuid']]);
                    $name = $names[0]['nas_nickname'] . '(' . $names[0]['share_path'] . ')';
                } elseif ($item['source_type'] == 59) {
                    // 对象存储
                    $sqls = "select obs_nickname,access_key_id,endpoint_override from obs_resource where obs_uuid = ?";
                    $names = $this->dbSelect($sqls, [$item['resource_uuid']]);
                    $name = $names[0]['obs_nickname'] . '(' . $names[0]['access_key_id'] . '@'
                        . $names[0]['endpoint_override'] .  ')';
                } elseif ($item['source_type'] == 60) {
                    // Hadoop集群
                    $sqls = "select hadoop_cluster_name from hadoop_cluster where hadoop_cluster_uuid = ?";
                    $names = $this->dbSelect($sqls, [$item['resource_uuid']]);
                    $name = $names[0]['hadoop_cluster_name'];
                } elseif ($item['source_type'] == 62) {
                    // kubernetes集群
                    $sqls = "select cluster_name from kube_cluster where cluster_uuid = ?";
                    $names = $this->dbSelect($sqls, [$item['resource_uuid']]);
                    $name = $names[0]['cluster_name'];
                } else {
                    // 客户端 和 传输代理
                    $sqls = "select hostname,ip from bd_agent where agent_uuid = ?";
                    $names = $this->dbSelect($sqls, [$item['resource_uuid']]);
                    $name = $names[0]['hostname'] . '(' . $names[0]['ip'] . ')';
                }
                $rows = [
                    'source_uuid' => $item['resource_uuid'],
                    'source_type' => xphp_get_lang($this->sourceArr[$item['source_type']] ?? ''),
                    'source_name' => $name,
                ];
                $lists[] = $rows;
            }
        } else {
            foreach ($list as $item) {
                $lists[] = [
                    'source_uuid' => $item['source_uuid'],
                    'source_type' => xphp_get_lang($this->sourceArr[$item['source_type']] ?? ''),
                    'source_name' => $item['source_name'],
                ];
            }
        }

        return [
            'rows' => $lists,
            'total' => $total
        ];
    }

    /**
     * 获取用户权限列表
     * @param array $parms 参数
     * @return array
     */
    public function getRoles(array $parms): array
    {
        $useruuid = $parms['users_uuid'];
        $page = xphp_get_menu();
        $users = new User();

        $userPermission = $users->pGetUserAllPermission($useruuid, true);

        $userPermission = $users->gGetPageLatest($userPermission, $useruuid, 0);

        $permissionArr = $users->pGetUserAllPermission($useruuid);
        return (new Group())->getUserAllTreeNodes($page, 0, $userPermission, $permissionArr, true, true);
    }

    /**
     * 查看分配管理用户
     * @param array $params 参数
     * @return array
     */
    public function getManager(array $params)
    {
        $sql = "select manager_uuid,manager_auth from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, [$params['users_uuid']]);
        $return = [
            'manage_uuid' => 0,
            'auth_list' => []
        ];
        if (!empty($data[0]) && !empty($data[0]['manager_uuid'])) {
            $return['manage_uuid'] = $data[0]['manager_uuid'];
            $return['auth_list'] = array_values(array_filter(explode(',', $data[0]['manager_auth'])));
        }
        return $return;
    }

    /**
     * 分配管理用户
     * @param array $params 参数
     * @return bool
     */
    public function doManager(array $params): bool
    {
        $param = ['', ''];
        if (!empty($params['manage_uuid'])) {
            // 表示关联管理用户
            $auth = ',' . implode(',', $params['auth_list']) . ',';
            $param = [$params['manage_uuid'], $auth];
        }
        $sql = "update bd_user set manager_uuid = ?,manager_auth = ? where user_uuid = ?";
        $param = array_merge($param, [$params['users_uuid']]);

        return $this->dbExec($sql, $param);
    }

    /**
     * 权限资源列表
     * @param array $params 参数
     * @return array
     */
    public function getAuth(array $params): array
    {
        if (!empty($params['user_uuid'])) {
            // 获取当前用户的权限
            $permission = (new User())->pGetUserAllPermission($params['user_uuid'], true);
        }
        $list = xphp_get_config('user', 'USER_AUTH_LIST');
        $return = [];
        foreach ($list as $item) {
            if (!empty($params['transfer']) && in_array($item['uuid'], ['data_manager', 'cbrbackup'])) {
                // 资源转移,去除备份数据管理和云存储同步
                continue;
            }
            if (!empty($params['user_uuid']) && !in_array($item['permission'], $permission ?? [])) {
                // 判断下用户是否有权限
                continue;
            }
            $return[] = [
                'uuid' => $item['uuid'],
                'type' => $item['type'],
                'look_auth' => $item['uuid'] . '_look',
                'operate_auth' => $item['uuid'] . '_operate',
                'name' => xphp_get_lang($item['name']),
            ];
        }
        return [
            'rows' => $return,
            'total' => count($return)
        ];
    }

    /**
     * 用户资源转移
     * @param array $params 参数
     * @return string
     */
    public function doTransfer(array $params)
    {
        // 1 判断是否有任务运行 停止所有任务
        $this->lockUserStopJob([$params['users_uuid']]);
        // 2 暂定为发送给后台完成资源转移
        $sourceList = $params['source_list']; // 资源标识
        $userAuthList = xphp_get_config('user', 'USER_AUTH_LIST');
        $moduleListArr = $otherListArr = [];
        // 需要去做个映射关系 模块和功能模块的
        foreach ($userAuthList as $item) {
            if (in_array($item['uuid'], $sourceList)) {
                if ($item['type'] == 1) {
                    // 模块
                    $moduleListArr[] = $item['val'];
                } else {
                    $otherListArr[] = $item['val'];
                }
            }
        }
        $data = [
            'source_user_uuid' => $params['users_uuid'], // 发起用户
            'target_user_uuid' => $params['manage_uuid'], // 接收用户
            'source_user_resource' => [
                'module_resource_list' => $moduleListArr, // 模块类型标识码 对照模块的标识
                'other_resource_list' => $otherListArr, // 功能模块标识码
            ]
        ];

        // 发送消息到后台完成逻辑处理
        $opcodeName = 'NODE_SYS_OP_TRANSFER_USER_RESOURCE';
        $mbResult = $this->service()->doTransfer($opcodeName, $data);

        $result = $mbResult['result'];
        $operate = (new NodeOpcode())->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 用户资源分配
     * @param array $params 参数
     * @return boolean
     */
    public function doAllocation(array $params): bool
    {

        $resourceType = $params['source_type'];
        // 1组装好数据
        // 2 调用公共方法分配资源或资源组给用户
        if ($params['type'] == 1) {
            // 资源
            // 如果是虚拟机资源的话，那么source_list里面的uuid是 vmuuid_vcenteruuid
            $vmuuid = $vcenteruuid = '';
            foreach ($params['source_list'] as $res) {
                $resourceuuid = $res;
                if (in_array($params['source_type'], [3, 58, 61])) {
                    // 虚拟机
                    $res = explode('|', $res);
                    $vmuuid = $resourceuuid = $res[0];
                    $vcenteruuid = $res[1];
                }
                $resourceInfo[] = [
                    'resourceuuid' => $resourceuuid,
                    'vmuuid' => $vmuuid,
                    'vcenteruuid' => $vcenteruuid,
                    'resourceType' => $resourceType
                ];
            }
            // 节点关联判断
            if (!$this->checkNodeByresouce($resourceInfo ?? [], $resourceType, 1, $params['users_uuid'])) {
                return false;
            }
            $result = $this->pAddUserResource($params['users_uuid'], $resourceInfo ?? []);
        } else {
            // 节点关联判断
            if (!$this->checkNodeByresouce($params['source_list'], $resourceType, 2, $params['users_uuid'])) {
                return false;
            }
            // 资源组
            $result = $this->pAddUserResourceGroup($params['users_uuid'], $params['source_list']);
        }
        return $result;
    }

    /**
     * 根据传递的资源类型和资源标识判断用户是否已经分配相应的节点
     * @param array  $uuid         资源uuid数组resourceuuid,vcenteruuid / []
     * @param int    $resourceType 资源类别
     * @param int    $type         资源类型 1资源 2资源组
     * @param string $userUuid     用户uuid
     * @return boolean
     */
    private function checkNodeByresouce(array $uuid, int $resourceType, int $type, string $userUuid): bool
    {
        if ($type == 2) {
            // 资源组 先查询出对应的资源uuid集合
            $sqls = "select resource_uuid resourceuuid,vcenter_uuid
                        from mt_resource_resource_group where resource_group_uuid = ?";
            $uuid = $this->dbSelect($sqls, [$uuid[0]]);
        }
        if (empty($uuid)) {
            // 如果为空 进行一个判断
            return false;
        }

        $isCheck = true; // 是否需要判断
        switch ($resourceType) {
            case 8: // 存储资源
                $param = "'" . implode("','", array_column($uuid, 'resourceuuid'))  . "'";
                $sql = "select node_uuid from bd_storage_resource where storage_uuid in ({$param}) ";
                $nodeUuid = $this->dbSelect($sql);
                break;
            default:
                $isCheck = false;
                break;
        }

        if ($isCheck && !empty($nodeUuid)) {
            // 判断这个节点是否属于用户
            $node = (new \app\v1\resources\v0\logic\Index())->pGetUserAllResource($userUuid, 7);
            return in_array($nodeUuid[0]['node_uuid'], array_column($node, 'resource_uuid'));
        }
        return !$isCheck;
    }

    /**
     * 根据传递的节点和用户获取已经分配了的关联的资源，
     * @param string $nodeUuid 节点uuid
     * @param string $userUuid 用户uuid
     * @return array
     */
    private function getResourceByNode(string $nodeUuid, string $userUuid): array
    {
        $sourceType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        // 关联的资源就给二次提示
        $sqlParam = [$userUuid, $nodeUuid];

        // 存储资源的
        $sqlStorage = "select bsr.storage_nickname,bsr.storage_uuid as resource_uuid
                        from bd_storage_resource bsr,mt_user_resource mur
                        where  mur.user_uuid = ?
                          and bsr.storage_type not in ({$sourceType['CLOUD']},{$sourceType['HUAWEI_CBR']})
                          and mur.resource_uuid = bsr.storage_uuid and bsr.node_uuid = ?
                          and mur.resource_type = 8 GROUP BY bsr.storage_uuid";

        // 关联的是资源组 那么就只给提示，先去解绑资源组，再来解绑节点，如果强制解绑，就解绑整个资源组
        // 存储资源
        $sqlStorages = "select bsr.storage_nickname,brg.resource_group_name,brg.resource_group_uuid
                        from bd_storage_resource bsr,mt_user_resource_group murg,
                             mt_resource_resource_group mrrg,bd_resource_group brg 
                        where murg.user_uuid = ? and mrrg.resource_uuid = bsr.storage_uuid
                          and brg.resource_group_uuid = mrrg.resource_group_uuid
                          and murg.resource_group_uuid = mrrg.resource_group_uuid
                          and bsr.node_uuid = ? and mrrg.resource_type = 8
                          and bsr.storage_type not in ({$sourceType['CLOUD']},{$sourceType['HUAWEI_CBR']})
                            GROUP BY bsr.storage_uuid";

        return [
            $this->dbSelect($sqlStorage, $sqlParam),
            $this->dbSelect($sqlStorages, $sqlParam),
        ];
    }

    /**
     * 接收node数组 进行输出转换
     * @param array  $nodeCheck 节点数组
     * @param string $userUuid  用户uuid
     * @return bool|json
     */
    private function getOutByNode(array $nodeCheck, string $userUuid)
    {
        // 存在备份节点资源
        $nodeArr = [];
        $agentL = xphp_get_lang('UI_JOB_CLIENT');
        $storageL = xphp_get_lang('UI_PLATFORM_STORAGE_RESOURCE');
        $resouceGL = xphp_get_lang('UI_PLATFORM_RESOURCE_GROUP');
        foreach ($nodeCheck as $nodes) {
            $return = $this->getResourceByNode($nodes['resource_uuid'], $userUuid);
            if (!empty($return[0])) {
                $nodeArr[] = $storageL . ':' . implode(',', array_column($return[0], 'storage_nickname'));
            }
            if (!empty($return[1])) {
                $nodeArr[] = $resouceGL . '(' . $return[1][0]['resource_group_name'] . '):'
                    . implode(',', array_column($return[1], 'storage_nickname'));
            }
        }

        if (!empty($nodeArr)) {
            // 返回二次提示
            return $this->muOpResult(
                false,
                xphp_get_lang('UI_PUBLIC_TIPS'),
                xphp_get_lang('UI_PUBLIC_TIPS'),
                '',
                0,
                array_unique($nodeArr)
            );
        }

        return true;
    }

    /**
     * 接收node数组 进行关联资源解绑
     * @param array  $nodeCheck 节点数组
     * @param string $userUuid  用户uuid
     * @return bool|json
     */
    private function delByNode(array $nodeCheck, string $userUuid)
    {
        // 存在备份节点资源
        $resourceUuid = $resourceGrouUuid = [];
        foreach ($nodeCheck as $nodes) {
            $return = $this->getResourceByNode($nodes['resource_uuid'], $userUuid);
            if (!empty($return[0])) {
                $resourceUuid = array_merge($resourceUuid, array_column($return[0], 'resource_uuid'));
            }
            if (!empty($return[1])) {
                $resourceGrouUuid = array_merge($resourceGrouUuid, array_column($return[1], 'resource_group_uuid'));
            }
        }
        if (!empty($resourceUuid)) {
            // 解除资源关联
            $where = " where user_uuid = ? and resource_uuid in ('" . implode("','", $resourceUuid) . "')";
            $sql = "delete from mt_user_resource " . $where;
            $this->dbExec($sql, [$userUuid]);
        }

        if (!empty($resourceGrouUuid)) {
            // 解除资源组关联
            $sql = "delete from mt_user_resource_group
                    where user_uuid = ? and resource_group_uuid in ('" . implode("','", $resourceGrouUuid) . "')";
            $this->dbExec($sql, [$userUuid]);
        }

        return true;
    }

    /**
     * 用户资源/组删除
     * @param array $params 参数
     * @return boolean
     */
    public function delAllocation(array $params): bool
    {
        // 注意，如果是虚拟机资源的删除，那么 source_list 的元素的 resource_uuid_vcenter_uuid
        // 其它的则是 resource_uuid 或 resource_group_uuid
        if ($params['type'] == 1) {
            // 资源
            $resourceUuid = $resourceUuid2 = $vcenter = [];
            // 组合下资源的uuid
            foreach ($params['source_list'] as $item) {
                $items = explode('|', $item);
                if (count($items) == 2) {
                    // 表示是虚拟机资源
                    $resourceUuid2[] = $items[0];
                    $vcenter[] = $items[1];
                } else {
                    $resourceUuid[] = $item;
                }
            }
            $where = [];
            if (!empty($resourceUuid2)) {
                $resourceUuidStr = implode("','", $resourceUuid2);
                //检查虚拟机资源是否正在使用
                $wheres = " ( resource_uuid in ('{$resourceUuidStr}') ";
                // tips 客户端的不知道是否需要判读 ...
                $this->checkDeleteUserVM($params['users_uuid'], $resourceUuid2);
                $vcenter = array_unique($vcenter);
                $vcenterStr = implode("','", $vcenter);
                $wheres .= " and vcenter_uuid in ('{$vcenterStr}') )";
                $where[] = $wheres;
            }

            if (!empty($resourceUuid)) {
                $resourceUuidStr = implode("','", $resourceUuid);
                // 先根据资源组ID查询出所有资源类型为 客户端、nas和组织管理的资源ID集合
                $sqls = "select mur.resource_uuid from mt_user_resource mur
                        where mur.resource_uuid in ('" . $resourceUuidStr . "')";

                // 判断下是否存在节点资源 需要判断资源组是否存在这个节点 做个比较在决定会解绑什么关联的资源
                $sqlNode = $sqls . ' and mur.resource_type = 7';
                $nodeCheck = $this->dbSelect($sqlNode);

                $sqlsR = "select mrrg.resource_uuid from mt_resource_resource_group mrrg,mt_user_resource_group murg
                        where mrrg.resource_type = 7 and mrrg.resource_group_uuid = murg.resource_group_uuid
                          and murg.user_uuid = ? group by mrrg.resource_uuid ";
                $nodeCheckR = $this->dbSelect($sqlsR, [$params['users_uuid']]);
                if (!empty($nodeCheck) && !empty($nodeCheckR)) {
                    $nodeChecks = [];
                    foreach ($nodeCheck as $itemR) {
                        if (!in_array($itemR['resource_uuid'], array_column($nodeCheckR, 'resource_uuid'))) {
                            $nodeChecks[] = $itemR;
                        }
                    }
                } else {
                    $nodeChecks = $nodeCheck;
                }

                if (!empty($nodeChecks) && empty($params['force'])) {
                    $this->getOutByNode($nodeChecks, $params['users_uuid']);
                }

                $sqls2 = $sqls . ' and mur.resource_type in (10, 56, 57)';
                $list = $this->dbSelect($sqls2);
                if (!empty($list)) {
                    // 那么需要判断下是否被使用
                    $sqls2 = $sqls . ' and mur.resource_type = 56';
                    $list2 = $this->dbSelect($sqls2);
                    if (!empty($list2)) {
                        $resourceId = array_column($list2, 'resource_uuid');
                        $this->checkDeleteUserOrgan($params['users_uuid'], $resourceId);
                    }
                    $sqls2 = $sqls . ' and mur.resource_type = 57';
                    $list2 = $this->dbSelect($sqls2);
                    if (!empty($list2)) {
                        $resourceId = array_column($list2, 'resource_uuid');
                        $this->checkDeleteUserNas($params['users_uuid'], $resourceId);
                    }
                    $sqls2 = $sqls . ' and mur.resource_type = 10';
                    $list2 = $this->dbSelect($sqls2);
                    if (!empty($list2)) {
                        $resourceId = array_column($list2, 'resource_uuid');
                        $this->checkDeleteUserAgent($params['users_uuid'], $resourceId);
                    }
                }
                if (!empty($nodeChecks) && !empty($params['force'])) {
                    // 解绑节点，进行强制解绑关联资源
                    $this->delByNode($nodeChecks, $params['users_uuid']);
                }
                $where[] = " ( resource_uuid in ('{$resourceUuidStr}') ) ";
            }
            $where = ' where user_uuid = ? and (' . implode(' or ', $where) . ' )';
            $sql = "delete from mt_user_resource " . $where;
        } else {
            // 资源组
            $resourceGroupDes = implode("','", $params['source_list']);
            // 先根据资源组ID查询出所有资源类型为 虚拟机和客户端、组织管理、nas设备、公有云的资源ID集合
            $sqls = "select mrrg.resource_uuid from mt_resource_resource_group mrrg
                        where mrrg.resource_group_uuid in ('" . $resourceGroupDes . "')";

            // 判断下是否存在节点资源
            $sqlNode = $sqls . ' and mrrg.resource_type = 7';
            $nodeCheck = $this->dbSelect($sqlNode);

            // 判断下资源里面的节点资源
            $sqlsR = "select mur.resource_uuid from mt_user_resource mur
                        where mur.user_uuid = ? and resource_type = 7 group by mur.resource_uuid ";
            $nodeCheckR = $this->dbSelect($sqlsR, [$params['users_uuid']]);
            if (!empty($nodeCheck) && !empty($nodeCheckR)) {
                $nodeChecks = [];
                foreach ($nodeCheck as $itemR) {
                    if (!in_array($itemR['resource_uuid'], array_column($nodeCheckR, 'resource_uuid'))) {
                        $nodeChecks[] = $itemR;
                    }
                }
            } else {
                $nodeChecks = $nodeCheck;
            }

            if (!empty($nodeChecks)  && empty($params['force'])) {
                $this->getOutByNode($nodeChecks, $params['users_uuid']);
            }

            $sqls2 = $sqls . ' and mrrg.resource_type in (3, 10, 56, 57, 58, 61)';
            $list = $this->dbSelect($sqls2);
            if (!empty($list)) {
                // 那么需要判断下是否被使用
                $sqls2 = $sqls . ' and mrrg.resource_type = 3';
                $list2 = $this->dbSelect($sqls2);
                if (!empty($list2)) {
                    $resourceId = array_column($list2, 'resource_uuid');
                    $this->checkDeleteUserVM($params['users_uuid'], $resourceId);
                }
                $sqls2 = $sqls . ' and mrrg.resource_type = 58';
                $list2 = $this->dbSelect($sqls2);
                if (!empty($list2)) {
                    $resourceId = array_column($list2, 'resource_uuid');
                    $this->checkDeleteUserVM($params['users_uuid'], $resourceId);
                }
                $sqls2 = $sqls . ' and mrrg.resource_type = 61';
                $list2 = $this->dbSelect($sqls2);
                if (!empty($list2)) {
                    $resourceId = array_column($list2, 'resource_uuid');
                    $this->checkDeleteUserVM($params['users_uuid'], $resourceId);
                }
                $sqls2 = $sqls . ' and mrrg.resource_type = 56';
                $list2 = $this->dbSelect($sqls2);
                if (!empty($list2)) {
                    $resourceId = array_column($list2, 'resource_uuid');
                    $this->checkDeleteUserOrgan($params['users_uuid'], $resourceId);
                }
                $sqls2 = $sqls . ' and mrrg.resource_type = 57';
                $list2 = $this->dbSelect($sqls2);
                if (!empty($list2)) {
                    $resourceId = array_column($list2, 'resource_uuid');
                    $this->checkDeleteUserNas($params['users_uuid'], $resourceId);
                }
                $sqls2 = $sqls . ' and mrrg.resource_type = 10';
                $list2 = $this->dbSelect($sqls2);
                if (!empty($list2)) {
                    $resourceId = array_column($list2, 'resource_uuid');
                    $this->checkDeleteUserAgent($params['users_uuid'], $resourceId);
                }
            }
            if (!empty($nodeChecks) && !empty($params['force'])) {
                // 解绑节点，进行强制解绑关联资源
                $this->delByNode($nodeChecks, $params['users_uuid']);
            }
            $sql = "delete from mt_user_resource_group
                    where user_uuid = ? and resource_group_uuid in ('" . $resourceGroupDes . "')";
        }

        // 如果是租户管理员的资源被解绑 那么这个租户下面的所有用户的资源也得被解绑
        $tenantUuid = (new User())->getUserPermission($params['users_uuid']);
        if (!empty($tenantUuid)) {
            // 还需要判断是否是管理员
            $sqlTenant = "select id from bd_tenant where admin_uuid = '" .  $params['users_uuid'] . "'";
            if (!empty($this->dbSelect($sqlTenant))) {
                // 表示是租户管理员
                // 获取下面的所有用户
                $userId = (new \app\v1\tenant\v0\logic\Index())->pGetTenantAllUser($tenantUuid);
                if (!empty($userId)) {
                    foreach ($userId as $item) {
                        $this->dbExec($sql, [$item['user_uuid']]);
                    }
                }
            }
        }
        return $this->dbExec($sql, [$params['users_uuid']]);
    }

    /**
     * 分配用户角色权限
     * @param array $params 参数
     * @return bool
     */
    public function doUserRole(array $params): bool
    {

        // 1、解除之前的角色绑定
        $this->pDeleteUserRole($params['users_uuid']);
        // 还要解除之前的用户组绑定
        $this->pDeleteUserUserGroup($params['users_uuid']);
        // 2、增加现在的角色绑定
        $this->pAddUserRole($params['users_uuid'], [$params['role_uuid']]);
        // 这里需要判断下当前角色对应的level
        $userLevel = 0; // 为了隔离开用户
        // 这里需要对三权模式下进行判断
        if (xphp_three_powers()) {
            $threePowersRole = xphp_get_config('three_powers', 'INIT_ROLE_LIST');
            $newthreePowersRole = array_flip($threePowersRole); // 对调数组
            $userLevel = $newthreePowersRole[$params['role_uuid']];
        }
        // 3、往uers表里面的user_auth里面写入json数组（新的权限）
        $sql = "update bd_user set user_auth = ?,user_level = ? where user_uuid = ?";
        return $this->dbExec($sql, [json_encode($params['source_list']), $userLevel, $params['users_uuid']]);
    }

    /**
     * 检查删除虚拟机是否被任务使用
     * @param string $useruuid      用户uuid
     * @param array  $resourceuuids 资源uuid数组集合
     * @return void
     */
    private function checkDeleteUserVM(string $useruuid, array $resourceuuids)
    {
        $resourceDes = implode("','", $resourceuuids);
        $sql = "select vml.machine_id from bd_task bt, vm_machine_list vml
                where bt.task_uuid = vml.task_uuid and bt.task_type = ?
                  and bt.user_uuid = ? and vml.vm_uuid in ('" . $resourceDes . "')";
        $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKTYPE')['BACKUP'], $useruuid));
        if (!empty($data)) {
            exit(
            $this->muOpResult(
                false,
                xphp_get_lang('WEB_USERS_CANCEL_VM'),
                xphp_get_lang('UI_PLATFORM_SELECTED_RES_USING'),
                'warning'
            )
            );
        }
    }

    /**
     * 检查删除nas设备是否被任务使用并且不是停止状态
     * @param string $useruuid      用户uuid
     * @param array  $resourceuuids 资源uuid数组集合
     * @return void
     */
    private function checkDeleteUserNas(string $useruuid, array $resourceuuids)
    {
        $resourceDes = implode("','", $resourceuuids);
        $sql = "select bt.id from bd_task bt,nas_task ns
                where bt.task_status != ? and ns.task_uuid = bt.task_uuid
                  and bt.user_uuid = ? and ns.nas_uuid in ('" . $resourceDes . "')";
        $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKSTATUS')['STOPPED'], $useruuid));
        if (!empty($data)) {
            exit(
            $this->muOpResult(
                false,
                xphp_get_lang('UI_RESOURCE_GROUP_UNBIND_ALLOCATION_RESOURCE'),
                xphp_get_lang('UI_PLATFORM_SELECTED_RES_USING'),
                'warning'
            )
            );
        }
    }

    /**
     * 检查删除组织管理是否被任务使用并且不是停止状态
     * @param string $useruuid      用户uuid
     * @param array  $resourceuuids 资源uuid数组集合
     * @return void
     */
    private function checkDeleteUserOrgan(string $useruuid, array $resourceuuids)
    {
        $resourceDes = implode("','", $resourceuuids);
        $sql = "select bt.id from bd_task bt,m365_task mt
                where bt.task_status != ? and mt.task_uuid = bt.task_uuid
                  and bt.user_uuid = ? and mt.organization_uuid in ('" . $resourceDes . "')";
        $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKSTATUS')['STOPPED'], $useruuid));
        if (!empty($data)) {
            exit(
            $this->muOpResult(
                false,
                xphp_get_lang('UI_RESOURCE_GROUP_UNBIND_ALLOCATION_RESOURCE'),
                xphp_get_lang('UI_PLATFORM_SELECTED_RES_USING'),
                'warning'
            )
            );
        }
    }

    /**
     * 检查删除客户端是否被任务使用并且不是停止状态
     * @param string $useruuid      用户uuid
     * @param array  $resourceuuids 资源uuid数组集合
     * @return void
     */
    private function checkDeleteUserAgent(string $useruuid, array $resourceuuids)
    {
        $resourceDes = implode("','", $resourceuuids);
        $sql = "select bt.id from bd_task bt,bd_task_agent_list btal
                where bt.task_status != ? and btal.task_uuid = bt.task_uuid
                  and bt.user_uuid = ? and btal.agent_uuid in ('" . $resourceDes . "')";
        $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKSTATUS')['STOPPED'], $useruuid));
        if (!empty($data)) {
            exit(
            $this->muOpResult(
                false,
                xphp_get_lang('UI_RESOURCE_GROUP_UNBIND_ALLOCATION_RESOURCE'),
                xphp_get_lang('UI_PLATFORM_SELECTED_RES_USING'),
                'warning'
            )
            );
        }
    }

    /**
     * 添加资源和用户关联
     * @param string $useruuid     用户uuid
     * @param array  $resourceList 资源组装数据
     * @return boolean
     */
    private function pAddUserResource(string $useruuid, array $resourceList): bool
    {
        if (empty($resourceList)) {
            return false;
        }
        $sql = "insert into mt_user_resource (user_uuid, resource_uuid, vm_uuid, vcenter_uuid, resource_type) values ";
        foreach ($resourceList as $l) {
            $resourceuuid = $l['resourceuuid'];
            $vmuuid = $l['vmuuid'];
            $vcenteruuid = $l['vcenteruuid'];
            $resourceType = $l['resourceType'];
            $sql .= "('" . $useruuid . "','" . $resourceuuid . "','" . $vmuuid
                . "','" . $vcenteruuid . "','" . $resourceType . "'),";
        }

        $sql = substr($sql, 0, -1);

        $result = $this->dbExec($sql);

        return !empty($result);
    }

    /**
     * 添加用户和资源组关联
     * @param string $useruuid          用户唯一标识
     * @param array  $resourceGroupList 资源组唯一标识列表
     * @return boolean 执行结果 成功|失败
     *@author luokai@vinchin.com
     */
    private function pAddUserResourceGroup($useruuid, array $resourceGroupList): bool
    {
        if (empty($resourceGroupList)) {
            return false;
        }
        $sql = "insert into mt_user_resource_group (user_uuid, resource_group_uuid) values ";
        foreach ($resourceGroupList as $resourceGroupUUID) {
            $sql .= "('" . $useruuid . "','" . $resourceGroupUUID . "'),";
        }
        $sql = substr($sql, 0, -1);
        $result = $this->dbExec($sql);
        return !empty($result);
    }

    /**
     * 验证用户名是否存在
     * @param array $params 参数
     * @return array
     */
    public function checkUser(array $params): array
    {
        $sql = "select user_uuid from bd_user where user_name = ?";

        $value = $this->dbSelect($sql, [trim($params['user_name'])]);
        return [
            'value' => empty($value)
        ];
    }

    /**
     * 根据用户UUID得到用户名
     * @param string|array $useruuid uuid
     * @return string
     */
    public function getUsername($useruuid): string
    {
        $sql = "select user_name from bd_user where user_uuid = " . $useruuid;
        if (is_array($useruuid)) {
            $useruuid = "('" . implode("','", $useruuid) . "')";
            $sql = "select user_name from bd_user where user_uuid in " . $useruuid;

            $check = true;
        }

        $data = $this->dbSelect($sql, []);

        if (!empty($check)) {
            return empty($data) ? '' : implode(',', array_column($data, 'user_name'));
        }

        return empty($data) ? '' : $data[0]['user_name'];
    }

    /**
     * 获取用户类型来源
     * @param int $userType type
     * @return string
     */
    protected function pGetUserTypeDes(int $userType): string
    {
        return $userType == 1 ? xphp_get_lang('UI_USER_LOCATION') : xphp_get_lang('UI_USER_EXTERNAL');
    }

    /**
     * 公共方法
     * 根据用户唯一标识获取其所在租户信息，没在租户内返回空数组
     * @param string $useruuid uuid
     * @return array()[]
     */
    protected function pGetTenantInfoByUser(string $useruuid): array
    {

        $sql = "select bt.tenant_name from bd_tenant bt, mt_user_tenant mut
            where bt.tenant_uuid = mut.tenant_uuid and mut.user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));

        return !empty($data) ? ['tenantname' => $data[0]['tenant_name']] : [];
    }

    /**
     * 转化时间戳为2012-12-12 12:12:12格式
     * @param unknown $timestamp time
     * @return string
     */
    protected function parseDate($timestamp = ''): string
    {
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        return empty($timestamp) ? xphp_get_config('app', 'TIMESPACE') : date($dateformat, $timestamp);
    }

    /**
     * 得到是否锁定的描述    是/否
     * @param number $lockFlag flag
     * @return string:
     */
    protected function getLockDes($lockFlag): string
    {
        switch ($lockFlag) {
            case 1:
                $lockDes = xphp_get_lang('WEB_PLATFORM_ENABLE');
                break;
            case 2:
                $lockDes = xphp_get_lang('WEB_PLATFORM_DISABLE');
                break;
            default:
                $lockDes = xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN');
        }

        return $lockDes;
    }

    /**
     * 检查添加用户是否存在
     * @param string $username   name
     * @param string $domainuuid uuid
     * @return array|bool
     */
    private function checkDomainUserExist(string $username, string $domainuuid)
    {
        $sql = "select domain_name, domain_ip, port, domain_type, username, password, port, detail
            from bd_domain_server where domain_uuid = ?";
        $data = dbSelect($sql, array($domainuuid));

        if (!empty($data)) {
            $domain = $data[0]['domain_name'];
            $nameIndex = strlen($domain . '\\');
            //本次添加用户名
            $name = substr($username, $nameIndex);
            $ip = $data[0]['domain_ip'];
            $adminname = $data[0]['username'];
            //加密密码
            $password = $data[0]['password'];
            //解密
            $decrype_password = v1_decrypt($data[0]['password']);
            $port = $data[0]['port'];
            //协议类型 1:ldaps 2:ldap
            $protocolNum = json_decode($data[0]['detail'],true)['protocol'];
            $protocolValue = array('',"ldaps://","ldap://");
            $protocol = $protocolValue[$protocolNum];
            $hostname = gethostbyaddr($ip);
            $host = $protocol.$hostname;
            //首先通过管理员账户连接活动目录服务器

            $user = $adminname . '@' . $domain;//域用户名
            $dcList = explode('.', $domain);
            $basedn = '';
            foreach ($dcList as $key => $dc) {
                $count = count($dcList) - 1;
                if ($key == $count) {
                    $basedn .= 'dc=' . $dc;
                } else {
                    $basedn .= 'dc=' . $dc . ',';
                }
            }
//             $basedn = "dc=skkwd,dc=com";
            $justthese = array('mail', 'cn', "samaccountname");
            //设置跳过验证证书步骤
            ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);

            $conn = ldap_connect($host, $port);//不要写成ldap_connect($host.':'.$port)的形式'

            if ($conn) {
                //设置参数
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
                $bd = ldap_bind($conn, $adminname.'@'.$domain, $decrype_password);
                if (!$bd){
                    $msg = xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR').","."#" . ldap_errno($conn) .",". xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ": ".ldap_error($conn);
                    return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_ADD'), $msg, 'warning');
                }
                if ($bd) {
                    //相当于登录成功
                } else {
                    return [
                        'success' => false,
                        'title' => xphp_get_lang('WEB_USERS_ADD_USER'),
                        'message' => xphp_get_lang('WEB_DOMAIN_SERVER_ADD_USER_PASSWORD_ERROR'), 'warning'
                    ];
                }
            } else {


                return [
                    'success' => false,
                    'title' => xphp_get_lang('WEB_USERS_ADD_USER'),
                    'message' => xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR'), 'warning'
                ];

            }
            // 分页查询
            $userArray = [];
            $cookie = '';
            $filter = '(&(objectCategory=person)(objectClass=user)(samaccountname=' . ldap_escape($name) . '))';
            $attributes = ['samaccountname']; // 只获取需要的属性
            $pageSize = 1000;
            ini_set('memory_limit', '0');
            do{
                $controls = [
                    [
                        'oid' => LDAP_CONTROL_PAGEDRESULTS,
                        'value' => [
                            'size' => $pageSize,
                            'cookie' => $cookie
                        ]
                    ]
                ];
                $result = ldap_search($conn, $basedn, $filter, $attributes, 0, $pageSize, 0, LDAP_DEREF_NEVER, $controls);
                if (!$result) {
                    throw new Exception('LDAP search failed: ' . ldap_error($conn));
                }
                // 只检查 count 是否 > 0，不加载全部条目
                $entry = ldap_first_entry($conn, $result);
                if ($entry) {
                    return true;
                }

                ldap_parse_result($conn, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
                ldap_free_result($result); // 必须手动释放
                $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'] ?? '';
            }while(!empty($cookie));
        }
        return [
            'success' => false,
            'title' => xphp_get_lang('WEB_USERS_ADD_USER'),
            'message' => xphp_get_lang('WEB_DOMAIN_SERVER_NOT_EXIST_USER'),
        ];
    }

    /**
     * 通过域唯一标识获取关联租户
     * @param string $domainuuid uuid
     * @return string|fetchAll()
     */
    public function getDomainTenantuuid(string $domainuuid)
    {
        $sql = "select tenant_uuid from bd_domain_server where domain_uuid = ?";
        $data = $this->dbSelect($sql, array($domainuuid));

        return !empty($data) ? $data[0]['tenant_uuid'] : '';
    }

    /**
     * 添加用户和用户组关联
     * @param string $useruuid      用户唯一标识
     * @param array  $usergroupList 用户组唯一标识列表
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pAddUserUserGroup(string $useruuid, array $usergroupList): bool
    {
        if (empty($usergroupList)) {
            return true;
        }
        $sql = "insert into mt_user_user_group(user_uuid,user_group_uuid) values ";
        foreach ($usergroupList as $key => $l) {
            if ($key == (count($usergroupList) - 1)) {
                $sql .= "('" . $useruuid . "','" . $l . "')";
            } else {
                $sql .= "('" . $useruuid . "','" . $l . "'),";
            }
        }

        return $this->dbExec($sql);
    }

    /**
     * 添加用户和角色关联
     * @param string $useruuid 用户唯一标识
     * @param array  $roleList 角色唯一标识列表
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pAddUserRole(string $useruuid, array $roleList): bool
    {
        if (empty($roleList)) {
            return true;
        }
        $sql = "insert into mt_user_role(user_uuid, role_uuid) values ";
        foreach ($roleList as $key => $l) {
            if ($key == (count($roleList) - 1)) {
                $sql .= "('" . $useruuid . "','" . $l . "')";
            } else {
                $sql .= "('" . $useruuid . "','" . $l . "'),";
            }
        }
        return $this->dbExec($sql);
    }

    /**
     * 添加用户和租户关联
     * @param array  $userList   用户唯一标识集合
     * @param string $tenantuuid 租户唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pAddUserTenant(array $userList, string $tenantuuid): bool
    {
        if (empty($userList)) {
            return false;
        }
        $sql = "insert into mt_user_tenant (user_uuid, tenant_uuid) values ";
        foreach ($userList as $key => $userUUID) {
            $sql .= "('" . $userUUID . "','" . $tenantuuid . "')";
            if ($key != (count($userList) - 1)) {
                $sql .= ',';
            }
        }
        return $this->dbExec($sql);
    }

    /**
     * 获取修改用户的租户等信息
     * @param string $useruuid uuid
     * @return array
     */
    public function getUserTenantInfo($useruuid): array
    {
        //获取租户uuid
        $sqlTenant = "select mut.tenant_uuid from bd_user bu, mt_user_tenant mut
            where bu.user_uuid = mut.user_uuid and bu.user_uuid = ? ";
        $dataTenant = dbSelect($sqlTenant, array($useruuid));

        $tenantuuid = !empty($dataTenant) ? $dataTenant[0]['tenant_uuid'] : '';

        //获取用户组列表
        $sqlUsergroup = "select muug.user_group_uuid from bd_user bu, mt_user_user_group muug
                where bu.user_uuid = muug.user_uuid and bu.user_uuid = ?";
        $dataUsergroup = dbSelect($sqlUsergroup, array($useruuid));

        $usergroupList = array();
        if (!empty($dataUsergroup)) {
            foreach ($dataUsergroup as $usergroup) {
                $usergroupList[] = $usergroup['user_group_uuid'];
            }
        }

        //获取角色列表
        $sqlRole = "select mur.role_uuid from bd_user bu, mt_user_role mur
                where bu.user_uuid = mur.user_uuid and bu.user_uuid = ? ";
        $dataRole = dbSelect($sqlRole, array($useruuid));
        $roleList = array();
        if (!empty($dataRole)) {
            foreach ($dataRole as $role) {
                $roleList[] = $role['role_uuid'];
            }
        }

        return [
            'tenant_uuid' => $tenantuuid,
            'usergroup_list' => $usergroupList,
            'role_list' => $roleList
        ];
    }

    /**
     * 取消用户和用户组关联
     * @param string $useruuid 用户唯一标识
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserUserGroup(string $useruuid): bool
    {
        $sql = "delete from mt_user_user_group where user_uuid = ?";
        return $this->dbExec($sql, array($useruuid));
    }

    /**
     * 取消用户和角色关联
     * @param string $useruuid uuid
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserRole(string $useruuid): bool
    {
        $sql = "delete from mt_user_role where user_uuid = ?";
        $return = $this->dbExec($sql, array($useruuid));
        // 同时需要把 user表里的user_auth给清空
        $sqls = "update bd_user set user_auth = '' where user_uuid = ? ";
        $return && $this->dbExec($sqls, array($useruuid));

        return $return;
    }

    /**
     * 禁用用户停止任务
     * @param array $users users
     * @return boolean
     */
    private function lockUserStopJob($users = []): bool
    {
        if (empty($users)) {
            return true;
        }
        $userDes = implode("','", $users);
        $jobHandler = new JobController();
        $i = 0;
        $sql = "select task_uuid, task_type, module_type, task_status
                    from bd_task where task_status != ? and user_uuid in ('" . $userDes . "')";
        $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKSTATUS')['STOPPED']));
        if (!empty($data)) {
            //循环去停止任务
            foreach ($data as $d) {
                $params = array(
                    'stop_uuid' => $d['task_uuid'],
                );
                $jobHandler->stopJob($params);
            }
        }

        //stop all jobs;
        return true;
    }

    /**
     * 删除用户的前置判断
     * @param array $user 参数
     * @return bool|string
     */
    public function deleteCheck(array $user)
    {
        // 普通模式下 只能admin和管理者 删除用户
        // 三权模式下 只能系统管理员可以删除用户,审计员和安全员可以删除同级别的账号,
        $userInfo = xphp_get_user_info();
        $threePowersUser = xphp_get_config('three_powers', 'THREE_POWERS_USER');

        if ($userInfo['userType'] == 3 || $userInfo['userLevel'] ==  $threePowersUser['admin']) {
            // 超级管理员
            return true;
        }

        if (xphp_three_powers()) {
            // 三权模式
            if ($userInfo['userLevel'] == $threePowersUser['sysadmin']) {
                // 系统管理员
                return true;
            }

            if (in_array($userInfo['userLevel'], [$threePowersUser['safeadmin'], $threePowersUser['auditor']])) {
                // 安全员和审计员
                $user = "'" . implode("','", $user) . "'";
                $sql = "select user_level from bd_user where user_uuid in ({$user})";
                $data = $this->dbSelect($sql);
                $level = array_unique(array_column($data, 'user_level'));
                if (count($level) == 1 && $level[0] == $userInfo['userLevel']) {
                    return true;
                }
                // 提示只能删除同级别的用户
                return $this->muOpResult(
                    false,
                    '',
                    xphp_get_lang('WEB_USERS_DELETE_USER_SHOW_TIPS' . $userInfo['userLevel'])
                );
            }
        } else {
            // 普通模式 需要判断这些需要删除的用户是否是属于他管理的
            $user = "'" . implode("','", $user) . "'";
            $sql = "select manager_uuid, create_user_uuid from bd_user where user_uuid in ({$user})";
            $data = $this->dbSelect($sql);
            $managerMerage = array_merge(array_column($data, 'manager_uuid'), array_column($data, 'create_user_uuid'));
            $manager = array_unique($managerMerage);
            if (in_array($userInfo['userUuid'], $manager)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 得到删除用户的用户ID号
     * @param array $userUUIDs uuids
     * @return array    $userIDs
     */
    private function getDeleteUserIDs(array $userUUIDs): array
    {
        foreach ($userUUIDs as &$uuid) {
            $uuid = "'" . $uuid . "'";
        }

        $userUUIDs = "'" . implode("','", $userUUIDs) . "'";

        $sql = "select id from bd_user where user_uuid in  ( ? )";
        $data = $this->dbSelect($sql, [$userUUIDs]);

        return array_column($data, 'id');
    }

    /**
     * 根据用户UUID得到用户ID号
     * @param string $userUUID uuid
     * @return mixed
     */
    private function getUserID(string $userUUID)
    {
        $sql = "select id from bd_user where user_uuid = ? ";
        $sqlParams = array($userUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        return $data[0]['id'];
    }

    /**
     * 删除用户检查
     * @param array $users user
     * @return void
     */
    private function deleteUserCheck(array $users)
    {

        //检查任务
        $this->checkTask($users);
        //检查时间点
        $this->checkTimepoint($users);
        //检查代理
        $this->checkAgent($users);
        //检查虚拟化中心
        $this->checkVcenter($users);
    }

    /**
     * 检查任务
     * @param array $users users
     * @return void
     */
    private function checkTask(array $users): void
    {

        $sql = "select bt.task_uuid from bd_task bt, bd_user bu
                where bt.user_uuid = bu.user_uuid and bu.id in ('" . implode(',', $users) . "')";

        $data = $this->dbSelect($sql, []);
        if (!empty($data)) {
            $this->muOpResult(
                false,
                xphp_get_lang('WEB_USERS_DELETE_USER'),
                xphp_get_lang('UI_PLATFORM_TASK_DEL_FIRST'),
                'warning'
            );
        }
    }

    /**
     * 检查时间点
     * @param array $users users
     * @return void
     */
    private function checkTimepoint(array $users): void
    {

        $sql = "select bbt.timepoint_uuid from bd_backup_timepoint bbt, bd_user bu
                where bbt.user_uuid = bu.user_uuid and bu.id in ('" . implode(',', $users) . "')";

        $data = $this->dbSelect($sql, []);
        if (!empty($data)) {
            $this->muOpResult(
                false,
                xphp_get_lang('WEB_USERS_DELETE_USER'),
                xphp_get_lang('UI_PLATFORM_TIMEPOINT_DEL_FIRST'),
                'warning'
            );
        }
    }

    /**
     * 检查代理端
     * @param array $users users
     * @return void
     */
    private function checkAgent(array $users): void
    {

        $sql = "select ba.hostname, ba.ip, ba.agent_type, bu.user_name from bd_agent ba, bd_user bu 
                where ba.user_uuid = bu.user_uuid and bu.id in ('" . implode(',', $users) . "')";

        $data = $this->dbSelect($sql, []);
        if (!empty($data)) {
            //如果有文件和数据库代理存在
            $des = xphp_get_lang('UI_PLATFORM_FAGENT_DEL_FIRST');
            //数据库代理
            if ($data[0]['agent_type'] == 1) {
                $des = xphp_get_lang('UI_PLATFORM_DBAGENT_DEL_FIRST');
            }
            $msg = xphp_get_lang('WEB_USERS_USER') . "'" . $data[0]['user_name'] . "'" .
                $des . "'" . $data[0]['host_name'] . "'";
            $this->muOpResult(false, xphp_get_lang('WEB_USERS_DELETE_USER'), $msg, 'warning');
        }
    }

    /**
     * 检查VCENTER
     * @param array $users users
     * @return void
     */
    private function checkVcenter(array $users): void
    {

        $sql = "select vv.nickname, bu.user_name from vm_vcenter vv, bd_user bu 
                where vv.user_uuid = bu.user_uuid and bu.id in ('" . implode(',', $users) . "')";

        $data = $this->dbSelect($sql, []);
        if (!empty($data)) {
            //如果虚拟化中心有虚拟机存在于任务中,直接返回
            $msg = xphp_get_lang('WEB_USERS_USER') . "'" . $data[0]['user_name'] . "'" .
                xphp_get_lang('WEB_USERS_DELETE_USER_TIPS') . "'" . $data[0]['nickname'] . "'";
            $this->muOpResult(false, xphp_get_lang('WEB_USERS_DELETE_USER'), $msg, 'warning');
        }
    }

    /**
     * 删除用户对于历史任务
     * @param string $user user
     * @return void|boolean
     */
    public function pDeleteUserHistoryTask(string $user)
    {
        $sql = "select distinct id from bd_history_task where user_uuid = ? ";
        $data = $this->dbSelect($sql, array($user));
        if (empty($data)) {
            return;
        }

        $taskIDArr = array_column($data, 'id');

        $opName = 'BD_TASK_OP_HISTORY_TASK_DELETE';
        $msg = array('id_list' => $taskIDArr);

        $this->mbPFMsg($opName, json_encode($msg));
        return true;
    }

    /**
     * @param array $user       参数
     * @param array $logHandler 参数
     * @return bool|void
     */
    public function pDeleteUserTaskLog(string $user, $logHandler)
    {
        $sql = "select distinct id from bd_task_log where user_uuid = ? ";
        $data = $this->dbSelect($sql, array($user));
        if (empty($data)) {
            return;
        }

        $params = array(
            'id' => array_column($data, 'id'),
            'tenantFlag' => true
        );
        $logHandler->deleteTaskLog($params);

        return true;
    }

    /**
     * @param array $user       参数
     * @param array $logHandler 参数
     * @return bool|void
     */
    public function pDeleteUserSystemLog(string $user, $logHandler)
    {
        $sql = "select distinct id from bd_system_log where user_uuid = ? ";
        $data = $this->dbSelect($sql, array($user));
        if (empty($data)) {
            return;
        }

        $params = array(
            'id' => array_column($data, 'id'),
            'tenantFlag' => true
        );
        $logHandler->deleteSystemLog($params);

        return true;
    }

    /**
     * @param array $user         参数
     * @param array $alarmHandler 参数
     * @return bool|void
     */
    public function pDeleteUserTaskAlarm(string $user, $alarmHandler)
    {
        $sql = "select distinct task_alarm_id from bd_task_alarm where user_uuid = ? ";
        $data = $this->dbSelect($sql, array($user));
        if (empty($data)) {
            return;
        }

        $params = array(
            'id' => array_column($data, 'task_alarm_id'),
            'tenantFlag' => true
        );
        $alarmHandler->deleteTaskAlarm($params);
        return true;
    }

    /**
     * 检测用户是否可以删除
     * @param string $useruuid uuid
     * @return bool
     */
    private function checkDeleteUserAvailable(string $useruuid)
    {
        return true;
    }

    /**
     * 取消用户和所有租户关联
     * @param string $useruuid 用户唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pDeleteUserTenant(string $useruuid): bool
    {
        $sql = "delete from mt_user_tenant where user_uuid = ? ";
        return $this->dbExec($sql, array($useruuid));
    }

    /**
     * 取消用户和所有资源关联
     * @param string $useruuid 用户唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pDeleteUserAllResource(string $useruuid): bool
    {
        $sql = "delete from mt_user_resource where user_uuid = ?";
        return $this->dbExec($sql, array($useruuid));
    }

    /**
     * 取消用户和所有资源组关联
     * @param string $useruuid 用户唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pDeleteUserAllResourceGroup(string $useruuid): bool
    {
        $sql = "delete from mt_user_resource_group where user_uuid = ?";
        return $this->dbExec($sql, array($useruuid));
    }

    /**
     * 公共方法
     * 删除用户创建的角色
     * @param string $useruuid 用户uuid
     * @return bool
     * @author liushuai@vinchin.com
     */
    public function pDeleteUserRoleAndUserCreate(string $useruuid): bool
    {
        $sql = "delete from bd_role where create_user_uuid = ?";
        return $this->dbExec($sql, array($useruuid));
    }

    /**
     * 公共方法
     * 删除用户创建的资源组
     * @param string $useruuid 用户uuid
     * @return bool
     * @author liushuai@vinchin.com
     */
    public function pDeleteUserResourceGroupAndUserCreate(string $useruuid): bool
    {
        $sql = "delete from bd_resource_group where create_user_uuid = ?";
        return $this->dbExec($sql, array($useruuid));
    }

    /**
     * 公共方法
     * 删除用户创建的用户组
     * @param string $useruuid 用户uuid
     * @return bool
     * @author liushuai@vinchin.com
     */
    public function pDeleteUserGroupAndUserCreate(string $useruuid): bool
    {
        $sql = "delete from bd_user_group where create_user_uuid = ?";
        return $this->dbExec($sql, array($useruuid));
    }

    /**
     * 获取登录历史tips
     * @return array
     */
    public function getLoginHistorys()
    {
        $tips = '';
        $cache = xphp_get_cache('history_login');

        $loginTime = $cache['historytime'];
        $loginIP = $cache['historyip'];
        $loginErrorCount = $cache['logincount'];
        $effectTime = $cache['effecttime'];

        if (!empty($loginIP)) {
            $today = time();
            $effectDay = round(($today - $effectTime) / 60 / 60 / 24, 0);

            $sql = "select pass_timeout from bd_account_safe";
            $where = ' where create_user_level = ?';
            $userLevel = 0;
            $userInfo = xphp_get_user_info();
            if ($userInfo['isThreePowers']) {
                // 是三权模式 那么就必须按照当前用户级别来读取配置
                $userLevel = $userInfo['userLevel'] ?? 0;
            }
            $data = $this->dbSelect($sql . $where, [$userLevel]);
            if (!empty($data)) {
                $passwordTime = intval($data[0]['pass_timeout']);
            } else {
                $passwordTime = 7;
            }

            $loseDay = $passwordTime - $effectDay;
            $tips .= xphp_get_lang('WEB_HISTORY_LOGIN_TIME') . $loginTime . '<br>'
                . xphp_get_lang('WEB_HISTORY_LOGIN_IP') . $loginIP .
                '<br>' . xphp_get_lang('WEB_HISTORY_LOGIN_FAIL_NUM') . $loginErrorCount;
            $tips .= '<br>' . xphp_get_lang('WEB_HISTORY_PASSWORD_LEAST') . $loseDay . xphp_get_lang('WEB_UTILS_DAY');
        }
        return [
            'title' => xphp_get_lang('WEB_HISTORY_TITLE'),
            'values' => $tips
        ];
    }

    /**
     * 获取当前登录用户的关联管理用户列表
     * @return array
     */
    public function getAuthLists()
    {
        $user = xphp_get_user_info();
        $sql = "select user_uuid,user_name from bd_user where manager_uuid = ?";

        $data = $this->dbSelect($sql, [$user['userUuid']]);
        if (empty($data)) {
            return [
                'currentUser' => $user['userName'],
                'currentUserUuid' => $user['userUuid'],
                'rows' => [],
                'total' => 0
            ];
        }
        $rows = [];
        foreach ($data as $item) {
            $rows[] = [
                'user_uuid' => $item['user_uuid'],
                'user_name' => $item['user_name']
            ];
        }
        return [
            'currentUser' => $user['userName'],
            'currentUserUuid' => $user['userUuid'],
            'rows' => $rows,
            'total' => count($rows)
        ];
    }

    /**
     * @param array $params [参数]
     * @return array
     * 验证用户名和邮箱
     */
    public function userInfoVerify($params)
    {
        // 判断是否配置了邮箱服务器
        $emailSql = " select *from bd_email_notice where email_notice_flag=?";
        $emailData = $this->dbSelect($emailSql, array(xphp_get_config('app', 'FLAG')['SET']));
        if (empty($emailData)) {
            $info = [
                'result' => 4,
                'message' => xphp_get_lang('UI_FORGETPASSWORD_TIPS_NO_CONFIGEMAIL')
            ];
            return $info;
        }
        $username = v1_decrypt_js_rsa($params['username']);//收件人用户名-已解密
        $flag = false;
        $sql = "select user_type,domain_uuid from bd_user where binary user_name = ?";
        $data = $this->dbSelect($sql, array($username));
        $userType = intval($data[0]['user_type']);
        if ($userType == 2) {
            // 排除域用户
            $result = 6;
            $info = [
                'result' => $result,
                'message' => xphp_get_lang('UI_FORGETPASSWORD_TIPS_SEND_EMAIL_FAILED')
            ];
            return $info;
        } else {
            if (strpos($username, '\\') !== false) {
                $usernameExp = explode('\\', $username);
                // 取租户的名字
                $tenantname = $usernameExp[0];
                $tenantusername = $usernameExp[1];
                // 租户
                $sql = "SELECT * FROM `bd_user` a 
                        INNER JOIN  
                        `mt_user_tenant` b on a.user_uuid=b.user_uuid  
                        inner join 
                        bd_tenant c on  b.tenant_uuid=c.tenant_uuid 
                        where tenant_name = ? and user_name = ?";
                $data = $this->dbSelect($sql, array($tenantname, $tenantusername));
                $flag = true;
            } else {
                // 一般用户
                $sql = "select * from bd_user where binary user_name= ? ";
                $data = $this->dbSelect($sql, array($username));
            }
            $email = v1_decrypt_js_rsa($params['email']);
            if (empty($data)) {
                $info = [
                    'result' => 2,  //用户名不存在
                    'message' => xphp_get_lang('UI_FORGETPASSWORD_TIPS_USERNAME_NOTEXIST')
                ];
                return $info;
            } else {
                // 用户名存在
                if ($flag) {
                    $sql = "SELECT email FROM `bd_user` a 
                            INNER JOIN  `mt_user_tenant` b on a.user_uuid=b.user_uuid  
                            inner join bd_tenant c on  b.tenant_uuid=c.tenant_uuid 
                            where tenant_name = ?";
                    $data = $this->dbSelect($sql, array($tenantname));
                } else {
                    $sql = "select email from bd_user where binary user_name = ?";
                    $data = $this->dbSelect($sql, array($username));
                }
                if ($data[0]['email'] == null) {
                    $info = [
                        'result' => 0,  //用户存在但是没有配置邮箱
                        'message' => xphp_get_lang('UI_FORGETPASSWORD_TIPS_NO_EMAIL')
                    ];
                    return $info;
                } else {
                    if ($flag) {
                        $sql = "SELECT a.user_uuid FROM `bd_user` a 
                                INNER JOIN  
                                `mt_user_tenant` b on a.user_uuid=b.user_uuid  
                                inner join 
                                bd_tenant c on  b.tenant_uuid=c.tenant_uuid 
                                where 
                                tenant_name= ? and email=?";
                        $userData = $this->dbSelect($sql, array($tenantname, $email));
                    } else {
                        $sql = "select user_uuid from bd_user where user_name = ? and email = ?";
                        $userData = $this->dbSelect($sql, array($username, $email));
                    }
                    $userEmail = $data[0]['email'];
                    if (strcasecmp($email, $userEmail) == 0) {  //发送邮件
                        $title = xphp_get_lang('UI_ORGAN_USER_RESET_PASSWORD');
                        $token = [
                            'useruuid' => $userData[0]['user_uuid'],
                            'username' => $username, 'time' => time(),
                        ];
                        $tokenData = json_encode($token, true);
                        $tokenData = v1_encrype($tokenData);  //加密
                        $tokenData = urlencode($tokenData);
                        // 邮件模板内容替换
                        if (
                        !in_array(
                            xphp_get_config('app', 'SYSTEM_INFO')['enterprise'],
                            xphp_get_config('app', 'ENTERPRISE')
                        )
                        ) {
                            $message = file_get_contents(DATA_PATH . 'email/email-reset-pwd-oem.html');
                        } elseif (xphp_get_config('app', 'lang') == 'en-us') {
                            $message = file_get_contents(DATA_PATH . 'email/email-reset-pwd-en.html');
                        } else {
                            $message = file_get_contents(DATA_PATH . 'email/email-reset-pwd.html');
                        }
                        $message = str_replace('reportTime', date('Y-m-d H:i:s'), $message);
                        $loginPageUrl = xphp_get_config('app', 'LOGIN_INFO')['login_url'];
                        // 提取URL末尾部分作为登录版本标识
                        $loginVersion = basename(rtrim($loginPageUrl, '/'));
                        $content = $_SERVER['REQUEST_SCHEME']
                            . '://' . $_SERVER['SERVER_ADDR']
                            . '/login_version/' . $loginVersion
                            . '/reset_pwd.php?key=' . $tokenData;
                        $message = str_replace('resetPwdUrl', $content, $message);
                        $message = str_replace('backupServerHost', (new \app\v1\common\logic\Report())->getMasterNodeIpLink(), $message);
                        $message = str_replace(
                            'supportEmailHref',
                            'mailto: ' . xphp_get_config('app', 'SYSTEM_INFO')['company_email'],
                            $message
                        );
                        $message = str_replace(
                            'supportEmail',
                            xphp_get_config('app', 'SYSTEM_INFO')['company_email'],
                            $message
                        );

                        $attachment = '';  // 附件
                        $sendRes = $this->resetPwdSendEmail($title, $email, $message, $attachment, $username);
                        if ($flag) {
                            $sendRes = $this->resetPwdSendEmail($title, $email, $message, $attachment, $username[1]);
                        }
                        $resultValue = 1; // 邮件服务器正常-邮件服务器已配置
                        $info = [
                            'result' => $resultValue,  // 发送邮箱
                            'message' => xphp_get_lang('WEB_M365_SERVER_SEND_EMAIL_SUCCESS')
                        ];
                        if (!$sendRes) {
                            $resultValue = 5;  // 邮件服务器异常
                            $info = [
                                'result' => $resultValue,  // 发送邮箱
                                'message' => xphp_get_lang('UI_FORGETPASSWORD_TIPS_SEND_EMAIL_FAILED')
                            ];
                        }
                        return $info;
                    } else {
                        $info = [
                            'result' => 3,  // 已配置邮箱但输入邮件错误
                            'message' => xphp_get_lang('UI_FORGETPASSWORD_TIPS_DIFFERENT_EMAIL')
                        ];
                        return $info;
                    }
                }
            }
        }
    }

    /**
     * @param $title      [邮件标题]
     * @param $email      [邮箱地址]
     * @param $content    [邮件内容]
     * @param $attachment [附件]
     * @param $username   [用户名]
     * @return bool
     * 重置密码发送邮件
     */
    public function resetPwdSendEmail($title, $email, $content, $attachment, $username)
    {
        // 直接调用通知那边提供的公共发送邮件接口
        $result = (new Notice())->sendEmail([
            'email' => [$email],
            'title' => $title,
            'info' => $content
        ]);
        return $result['code'] == 0;
    }

    /**
     * @param array $params [参数]
     * @return array
     * 重置密码
     */
    public function resetPassWord($params)
    {
        $password = v1_decrypt_js_rsa($params['password']);
        $passSql = "select pass_complexity from bd_account_safe";
        $passData = $this->dbSelect($passSql);
        $passcomplexity = $passData[0]['pass_complexity'];   //密码强度
        $passwordmd5 = md5($password);//md5加密新密码
        $token = v1_decrypt($_COOKIE['resetPwdToken']);
        $token = json_decode($token, true);
        $useruuid = $token['useruuid'];
        switch ($passcomplexity) {
            case 1:
                $flag = preg_match('/^[A-Za-z0-9]/', $password);
                break;
            case 2:
                $flag = preg_match('/^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/', $password);
                break;
            case 3:
                $flag = preg_match(
                    '/^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/',
                    $password
                );
                break;
        }
        if ($flag) {
            $sql = "update bd_user set password = ? where user_uuid = ? ";
            $data = $this->dbExec($sql, array($passwordmd5, $useruuid));//更新密码,密码是md5加密后的
            $info = array(
                'result' => $data  //密码重置
            );
        } else {
            $info = array(
                'result' => false //失败
            );
        }
        return $info;
    }

    /**
     * @param array $params [参数]
     * @return array
     * 获取密码复杂度
     */
    public function getPassComplexity($params)
    {
        $passSql = "select *from bd_account_safe";
        $passData = $this->dbSelect($passSql);
        $info = array(
            'passlength' => $passData[0]['pass_length'],
            'passcomplexity' => $passData[0]['pass_complexity'],
        );
        return $info;
    }

    /**
     * 检查原来密码是否正确
     * @param array $params [参数]
     * @return string
     */
    public function oldpassAvailable($params)
    {
        $password = md5(html_entity_decode($params['password']));
        $useruuid = $params['useruuid'];
        if (empty($useruuid)) {
            $useruuid = xphp_get_user_info()['userUuid'];
        }
        $sql = "select id from bd_user where user_uuid = ? and password = ? ";
        $sqlParams = array($useruuid, $password);
        $users = parent::dbSelect($sql, $sqlParams);
        $res = !empty($users);
        return [
            'result' => $res
        ];
    }

    /**
     * 修改密码
     * @param array $params [参数]
     * @return string
     */
    public function editPassword($params)
    {
        $useruuid = $params['useruuid'];
        $oldPassword = $params['oldPassword'];
        $newPassword = $params['newPassword'];
        $editTime = date('Y-m-d H:i:s');
        if (empty($useruuid)) {
            $useruuid = xphp_get_user_info()['userUuid'];
        }
        $this->paramsCheck($oldPassword, $newPassword);
        $sql = "update bd_user set password = ?, create_time = ? ,force_password_change_flag = ? 
                where 
                user_uuid = ? 
                and password = ?";
        $result = $this->dbExec(
            $sql,
            array($newPassword, $editTime, xphp_get_config('app')['FLAG']['UNSET'], $useruuid, $oldPassword)
        );
        $sqlUsername = "select user_name from bd_user where user_uuid = ?";
        $sqlUsernameData = $this->dbSelect($sqlUsername, array($useruuid));
        if ($result) {
            $clientIP = v1_get_client_iP();
            $this->loginLog(
                $useruuid,
                $sqlUsernameData[0]['user_name'],
                'SYSTEM_USER_EDIT_PASSWORD_SUCCESS',
                array('S:' . $clientIP)
            );
        }
        return [
            'result' => $this->muOpResult($result, xphp_get_lang('WEB_USERS_EDIT_PASS'))
        ];
    }

    /**
     * 获取租户下用的角色
     * @param unknown $useruuid
     */
    public function getRoleList($params)
    {
        $editFlag = $params['editflag'];
        $userGroupuuid = $params['usergroupuuid'];
        $sqlRole = "select distinct br.role_uuid, br.role_name from bd_role br left join mt_user_group_role mugr on mugr.role_uuid = br.role_uuid ";

        if (!$_SESSION['isThreePowers']) {
            $sqlRole .= " left join mt_user_role mur on mur.role_uuid = br.role_uuid";
        }
        $sqlRole .= " where";


        $where = ' br.lock_flag = ? ';
        $sqlParams = [xphp_get_config('app')['FLAG']['SET']];
        if (!$_SESSION['isThreePowers']) {
            $sqlParams = array(xphp_get_config('app')['FLAG']['SET'], xphp_get_user_info()['userUuid']);
            if (empty($_SESSION['tenantuuid']) && xphp_get_user_info()['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
                $where .= " and (br.create_user_uuid = ? or br.create_user_uuid = '') ";
            } else {
                $where .= " and br.create_user_uuid = ? ";
            }
        }

        if ($editFlag) {
            // $sqlRole .= " or mugr.user_group_uuid =? ";
            $where .= " or mugr.user_group_uuid =? ";
            $sqlParams = array_merge($sqlParams, array($userGroupuuid));
        }

        // 未添加租户 那么屏蔽系统初始化的租户相关的三个角色列表
        $tenantSql = "select tenant_uuid from bd_tenant";
        $tenantData = $this->dbSelect($tenantSql);

        // 未授权租户相关的权限模块 角色列表就不显示租户相关的名称
        // 说明，之所以不直接用模糊查询 租户 关键词， 是因为英文的需要一起兼容
        if (!in_array('tenant_manager', $_SESSION['permission']) || empty($tenantData)) {
            // 未授权 那么屏蔽系统初始化的租户相关的三个角色列表
            $where = '(' . $where . ") 
            and 
            br.role_uuid 
            not in 
            ('eee859d5-341a-b95a-33d0-6ed583d0f7a1',
            '2b214439-8f0b-ec23-a3be-2014ad9baecc',
            '03e12c93-9dc1-371a-6a64-b74965d36723')";
        }

        if (!empty($tenantData)) {
            // 添加租户 那么只展示管理员、操作员、审计员、租户管理员
            $where = '(' . $where . ") and br.role_uuid not in ('
            a30f7728-2ef7-bca0-2224-07deba8ce3e5',
            '2b214439-8f0b-ec23-a3be-2014ad9baecc',
            '03e12c93-9dc1-371a-6a64-b74965d36723'
            )";
        }

        // 三员的模式下，只显示那几个角色，否则不显示三员的角色
        $threeRole = [
            '695cd4e6-34c1-d55a-7526-c3ea8562112f', // 系统管理员
            'c6f7a389-145c-5182-b37b-642a39a68b95', // 安全管理员
            '95cdc633-5eea-644c-3555-f051440608c0', // 安全审计员
            'b7345b13-692d-7d39-dd09-be42ae070238',  // 普通操作员
        ];
        $threeRoleStr = implode("','", $threeRole);
        if ($_SESSION['isThreePowers']) {
            // 三权
            $where = '(' . $where . ") and br.role_uuid in ('{$threeRoleStr}')";
        } else {
            $where = '(' . $where . ") and br.role_uuid not in ('{$threeRoleStr}')";
        }

        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] ==   xphp_get_config('app','VENDOR_LIST')['gmp']) {
            // gmp的需要屏蔽操作员和审计员
            $where = '(' . $where . ") and br.role_uuid not in 
            (
            'a633143d-f5b3-b348-998d-a209d62e27f0',
            '7e891486-9d2c-603b-6fe1-8bc0d332f602'
            )";
        }

        $sqlRole .= $where . " and br.role_uuid != 'a30f7728-2ef7-bca0-2224-07deba8ce3e5'";

        if (!$_SESSION['isThreePowers'] && !empty($params['userUuid'])) {
            $sqlRole .= 'and mur.user_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($params['userUuid']));
        }
        $dataRole = $this->dbSelect($sqlRole, $sqlParams);

        $info = array();
        foreach ($dataRole as $d) {
            $info[] = array(
                'role_uuid' => $d['role_uuid'],
                'role_name' => $d['role_name']
            );
        }
        return $info;
    }

    /**
     * 根据传递来的permission数组组装成树返回
     * @param array $params [参数]
     * @return array
     */
    public function getUserPermissionRole($params)
    {
        $oldpath = xphp_get_oldpath();
        $page = getConfig($oldpath . 'api/xphp/conf/page.php');
        $userPermission = $params['permission'];

        $userTree = $this->getUserAllTreeNodesRole($page, 0, $userPermission);

        $userInfo = array(
            'nodes' => $userTree,
        );

        return $userInfo;
    }

    /**
     * 得到完整的用户权限树(递归)
     * @param array $page 所有配置的页面
     */
    public function getUserAllTreeNodesRole($page, $pid, $userPermission)
    {
        $tree = array();
        foreach ($page as $p) {
            if (!in_array($p['name'], $userPermission)) {
                //角色添加权限页面
                continue;
            }

            $node = array(
                'id' => $p['name'],
                'pid' => $pid,
                'name' => '<i class="' . $p['class'] . '"></i> ' . xphp_get_lang($p['title']),
                'title' => xphp_get_lang($p['title']),
                'open' => true,
                'nocheck' => false,
                'type' => $p['level']
            );
            if (!empty($p['child'])) {
                //如果有子目录
                $node['children'] = $this->getUserAllTreeNodesRole($p['child'], $p['name'], $userPermission);
            }

            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * 获取用户组列表用于分配
     * @param array $params [用户信息]
     * @return array [用户组列表]
     */
    public function getUserGroupList($params)
    {
        $editFlag = $params['editflag'];
        $useruuid = $params['useruuid'];
        $groupType = $params['group_type'];
        //获取用户租户信息
        $tenantInfo = $this->getUserTenantInfo($useruuid);
        $buildSql = ', (select GROUP_CONCAT(config) from bd_role where role_uuid in
                    (select role_uuid from mt_user_group_role where user_group_uuid = bug.user_group_uuid)) roles';
        $sqlUserGroup = "select distinct bug.user_group_uuid, bug.user_group_name {$buildSql}
from bd_user_group bug left join mt_user_group_tenant mugt on bug.user_group_uuid = mugt.user_group_uuid 
    left join mt_user_user_group muug on bug.user_group_uuid = muug.user_group_uuid  ";
        $where = "bug.lock_flag = ? and bug.create_user_uuid = ? ";
        $sqlParams = array(xphp_get_config('app')['FLAG']['SET'], xphp_get_user_info()['userUuid']);
        if (
            empty($_SESSION['tenantuuid'])
            && xphp_get_user_info()['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9'
        ) {
            $where .= " or bug.create_user_uuid = '' ";
        }

        //修改用户显示相应管理的所有用户组
        if ($editFlag) {
            $where .= ' or muug.user_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }
        $sqlUserGroup .= " where ($where) and bug.user_group_uuid != 'e99ae858-d549-4754-9310-457477f4c2e3'";
        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] ==   xphp_get_config('app','VENDOR_LIST')['gmp']) {
            // gmp的需要屏蔽操作组和审计组
            $sqlNew = " and bug.user_group_uuid not in 
            (
            '09d1c727-d87b-494a-81bf-fb9014ed313c',
            'd57a9f76-845d-43a8-bb79-bdafbf405c85'
            )";
            $sqlUserGroup .= $sqlNew;
        }
        $data = $this->dbSelect($sqlUserGroup, $sqlParams);
        $info = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                //如果在租户外修改租户管理员，只显示创建时关联的用户组
                if (empty($_SESSION['tenantuuid']) && !empty($tenantInfo['tenant_uuid'])) {
                    if (!in_array($d['user_group_uuid'], $tenantInfo['usergroup_list'])) {
                        continue;
                    }
                }
                if (!empty($groupType)) {
                    $roles = array_unique(explode(',', $d['roles']));
                    if ($groupType == 2 && !in_array(2, $roles)) {
                        // 只查看全局观察者角色
                        continue;
                    }
                    if ($groupType != 2 && in_array(2, $roles)) {
                        // 排除全局观察者角色
                        continue;
                    }
                }
                $info[] = array(
                    'usergroup_uuid' => $d['user_group_uuid'],
                    'usergroup_name' => $d['user_group_name']
                );
            }
        }
        return $info;
    }

    /**
     * 用户自己修改资料
     * @param array $params [用户名、邮箱、电话、语言]
     * @return string
     */
    public function editSelfInfo($params)
    {
        $username = $params['username'];
        $email = $params['email'];
        $telephone = $params['telephone'];
        $language = $params['language'];
        $oldlang = $params['oldlang'];
        $password = $params['password'];
        $customePassword = $params['custome_password'];
        $sql = "update bd_user set ";
        $params = array();
        $setClauses = array();

        if(!empty($username)){
            $setClauses[] = "user_name = ?";
            $params[] = $username;
        }

        // 邮箱和电话号码可为空
        if($email !== null){
            $setClauses[] = "email = ?";
            $params[] = $email;
        }

        if($telephone !== null){
            $setClauses[] = "telephone = ?";
            $params[] = $telephone;
        }

        if(!empty($language)){
            $setClauses[] = "language = ?";
            $params[] = $language;
        }
        if(!empty($password)){
            $setClauses[] = "password = ?";
            $decrypt_password = v1_decrypt_js_rsa($password);
            $MD5_password = md5($decrypt_password);
            $params[] = $MD5_password;
        }
        if(!empty($customePassword)){
            $setClauses[] = "custome_password = ?";
            $decrypt_custome_password = v1_decrypt_js_rsa($customePassword);
            $MD5_custome_password = md5($decrypt_custome_password);
            $params[] = $MD5_custome_password;
        }

        $sql .= implode(',', $setClauses) . " where user_uuid = ?";
        $params[] = xphp_get_user_info()['userUuid'];
        $result = $this->dbExec($sql, $params);
        if ($result) {
            $this->systemLog('SYSTEM_USER_EDIT_INFO_SUCCESS');
            if ($oldlang != $language) {
                session_start();
                $_SESSION['language'] = $language;
                session_commit();
            }
        }
        return $this->muOpResult($result, xphp_get_lang('WEB_USERS_EDIT_INFO'));
    }

    /**
     * 检查用户名是否已经存在
     * @params array $params [参数]
     * @return boolean
     */
    public function usernameExist($params)
    {
        $username = $params['username'];
        $usertype = intval($params['user_type']);
        $sql = "select id from bd_user where user_name = ? and user_type = ?";
        $sqlParams = array($username,$usertype);
        $users = parent::dbSelect($sql, $sqlParams);
        if(empty($users)){
            // 用户名不重复
            $info = array(
                'success' => true,
                'code' => 0,
                'message' => xphp_get_lang('UI_LOGIN_NAME_NO_EXISTS'),
            );
        }else{
            // 用户名重复
            $info = array(
                'success' => false,
                'code' => 0,
                'message' => xphp_get_lang('UI_LOGIN_NAME_EXISTS'),
            );
        }
        return $info;
    }

    /**
     * 得到用户自己的信息
     * @return array
     */
    public function getUserSelfInfo()
    {
        $useruuid = xphp_get_user_info()['userUuid'];
        $operate = xphp_get_lang('WEB_USERS_GET_CURRENT_INFO');
        $sql = "select user_name, user_type, email, telephone, language, user_level, auth_code, user_type, last_login_time, password, custome_password from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        // 角色
        $role_sql = "SELECT mur.role_uuid, br.role_name
                     FROM mt_user_role mur
                     INNER JOIN bd_role br ON mur.role_uuid = br.role_uuid
                     INNER JOIN bd_user bu ON mur.user_uuid = bu.user_uuid 
                     WHERE bu.user_uuid = ?";
        $role_data = $this->dbSelect($role_sql,array($useruuid));
        $role_str = implode(',', array_filter(array_column($role_data, 'role_name')));
        //产品版本
        $login_product_type = "";
        //获取版本信息
        $sql_product_type = "select settings_content from bd_system_settings where settings_type = 24";
        $result_product_type = $this->dbSelect($sql_product_type);
        if(empty($result_product_type)){
            $login_product_type = "";
        }else{
            $login_product_type = $result_product_type[0]['settings_content'];
        }
        $info = array();
        //获取用户是否属于租户
        $tenantuuid = (new User())->getUserPermission();
        if ($data) {
            $code = $data[0]['auth_code'];
            if (empty($data[0]['auth_code']) && intval($data[0]['user_type']) == 3) {
                //超级管理员admin初始化认证码
                $code = xphp_random_code(4);
                $sql = "update bd_user set auth_code = ? where user_uuid = ?";
                $this->dbExec($sql, array($code, $useruuid));
            }
            $info = array(
                're' => true,
                'username' => $data[0]['user_name'],
                'email' => $data[0]['email'],
                'telephone' => $data[0]['telephone'],
                'language' => $data[0]['language'],
                'list' => $this->getSystemLangList(),
                'tenantuuid' => $tenantuuid,
                'user_uuid' => $useruuid,
                'user_level' => $data[0]['user_level'],
                'is_three_powers' => $_SESSION['isThreePowers'],
                'auth_code' => $code,
                'user_type' => $data[0]['user_type'],
                'product_type' => $login_product_type,
                'user_type_des' => v1_get_user_type_des($data[0]['user_type']),
                'last_login_time' => $data[0]['last_login_time'],
                'password' => $data[0]['password'],
                'custome_password' => $data[0]['custome_password'],
                'role_str' => $role_str
            );
            return $info;
        } else {
            return $this->muOpResult(false, $operate);
        }
    }

    /**
     * 根据用户类型得到用户操作权限
     * @param string $params [usertype]用户类型:administrator,auditor,manager,operator
     * @return string
     */
    public function getUserPermission()
    {
        $oldpath = xphp_get_oldpath();
        $page = getConfig($oldpath . 'api/xphp/conf/page.php');
        $userPermission = (new User())->pGetUserAllPermission(xphp_get_user_info()['userUuid'], true);
        if (!empty(xphp_get_user_info()['tenantuuid'])) {
            // 如果是租户 那么强制的去除 用户管理
            // ps 因为系统管理里面只有用户管理，所以直接系统管理也去掉
            $userPermission = array_diff($userPermission, ['sysmanagement', 'safety']);
        }

        if (!empty(v1_auth_is_admin()['is_admin'])) {
            // 这里判断下是否有全局观察者权限
            $globalRead = in_array('global_read', $userPermission);
            $globalWrite = in_array('global_write', $userPermission);
        }
        // 去除 permission 的全局观察者
        $userPermission = array_diff($userPermission, ['global_observer', 'global_read', 'global_write']);

        $userTree = (new Group())->getUserAllTreeNodes($page, 0, $userPermission);

        return array(
            'nodes' => $userTree,
            'global_read' => $globalRead ?? false,
            'global_write' => $globalWrite ?? false,
        );
    }

    /**
     * 获取系统预定义语言包列表
     * @return array
     */
    private function getSystemLangList()
    {
        $lang = xphp_get_config('lang', 'lang');
        $langDes = xphp_get_config('lang', 'langDes');
        $list = array();
        foreach ($lang as $key => $value) {
            $list[] = array($value, $langDes[$key]);
        }
        return $list;
    }

    /**
     * 检查验证码是否已被使用
     * @param $code
     * @return bool
     */
    public function checkCodeExist($code)
    {
        $sql = "select user_uuid from bd_user where auth_code = ?";
        $data = $this->dbSelect($sql, array($code));
        return !empty($data);
    }

    /**
     * 添加用户组和文件代理关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupFileHost($params)
    {
        $userGroupUUID = $params['userGroupuuid'];
        $resourceList = $params['list'];
        $resourceType = xphp_get_config('resource', 'RESOURCE_TYPE')['FILE_HOST'];
        $opName = xphp_get_lang('WEB_USER_GROUP_ADD_FILE_HOST');
        return $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 公共方法
     * 添加资源与用户组关联
     * @param string $userGroupUUID 用户组唯一标识
     * @param array  $resourceList  资源信息列表
     * @param int    $resourceType  资源类型
     * @param string $opName        添加关联操作描述
     * @author luokai@vinchin.com
     * @return string 统一返回操作结果消息到界面
     */
    public function pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType = '', $opName = '')
    {
        //调用添加用户组与资源关联公共方法
        $resourceInfo =  array();
        if (!empty($resourceList)) {
            foreach ($resourceList as $res) {
                $resourceInfo[] = array(
                    'resourceuuid' => $res['resourceuuid'],
                    'vmuuid' => $res['vmuuid'],
                    'vcenteruuid' => $res['vcenteruuid'],
                    'resourceType' => $resourceType
                );
            }
        }
        $result = $this->pAddUserGroupResource($userGroupUUID, $resourceInfo);
        if ($result) {
            return $this->muOpResult(true, $opName);
        } else {
            return $this->muOpResult(false, $opName, '', 'warning');
        }
    }

    /**
     * 添加用户组和资源关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array  $resourceList  一个或多个资源信息列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserGroupResource($usergroupuuid, $resourceList)
    {
        if (empty($resourceList)) {
            return false;
        }
        $sql = "insert into mt_user_group_resource (user_group_uuid, resource_uuid, vm_uuid, vcenter_uuid, resource_type) values ";
        foreach ($resourceList as $key => $l) {
            $resourceuuid = $l['resourceuuid'];
            $vmuuid = $l['vmuuid'];
            $vcenteruuid = $l['vcenteruuid'];
            $resourceType = $l['resourceType'];
            $sql .= "('" . $usergroupuuid . "','" . $resourceuuid . "','" . $vmuuid . "','" . $vcenteruuid . "','" . $resourceType . "')";
            if ($key != (count($resourceList) - 1)) {
                $sql .= ',';
            }
        }

        $result = $this->dbExec($sql);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证用户密码是否正确
     * @param array $params 请求参数
     * @return array
     */
    public function checkUserPassword(array $params)
    {
        // 先校验输入的密码 和当前用户的密码是一致的
        $password = v1_decrypt_js_rsa($params['password']);
        $sql = 'select password from bd_user where user_uuid = ?';
        $userUuid = !empty($params['user_uuid']) ? $params['user_uuid'] : xphp_get_user_info()['userUuid'];
        $user = $this->dbSelect($sql, [$userUuid]);
        if (empty($user)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_AGENT_NO_USER')
            ];
        }
        if ($user[0]['password'] != md5($password)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_COMMON_VERIFY_PASSWORD_ERROR')
            ];
        }
        return [
            'code' => 0,
            'msg' => xphp_get_lang('UI_JOB_VERIFY_PASSWORD_SUCCESS')
        ];
    }

    /**
     * 获取用户使用的数据量
     * @param string $useruuid
     * @return int
     */
    public function getUserBackupData(string $useruuid)
    {
        $sql = "select sum(write_size) as backup_size from bd_backup_timepoint where user_uuid in ('" . $useruuid . "') 
                and task_type not in (" . xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] . "," . xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] . "," . xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH'] . ")";
        $data = $this->dbSelect($sql);
        return intval($data[0]['backup_size']);
    }

    /**
     * 验证用户操作权限
     * @param array $params 请求参数
     * @return bool|string
     */
    public function checkUserAuth(array $params = [])
    {

        if (!empty($params['type']) && $params['type'] == 1) {
            // 非分配资源操作权限
            return $this->checkAuthByUserUuid($params['user_uuid'], $params['auth']);
        } else {
            // 分配资源操作权限
            return $this->checkAuthBySourceUuid($params['source_uuid'], $params['source_type']);
        }
    }

    /**
     * 检查原独立密码是否正确
     * @param array $params [参数]
     * @return string
     */
    public function oldCustomePassAvailable($params)
    {
        $password = md5($params['custome_password']);
        $useruuid = $params['useruuid'];
        if (empty($useruuid)) {
            $useruuid = xphp_get_user_info()['userUuid'];
        }
        $sql = "select id from bd_user where user_uuid = ? and custome_password = ? ";
        $sqlParams = array($useruuid, $password);
        $users = parent::dbSelect($sql, $sqlParams);
        $res = !empty($users);
        return [
            'result' => $res
        ];
    }

    /**
     * 修改独立密码
     */
    public function editCustomepass($params)
    {
        $user_uuid = $params['user_uuid'];
        $custome_password = $params['customePassword'];
        $decrypt_custome_password = v1_decrypt_js_rsa($custome_password);
        $MD5_custome_password = md5($decrypt_custome_password);
        $sql = "update bd_user set custome_password = ? where user_uuid = ?";
        $res = $this->dbSelect($sql,array($MD5_custome_password,$user_uuid));
        return [
            'result' => $res
        ];
    }
}
