<?php

namespace app\v2\user\v0\logic;

use app\v2\common\logic\Base;
use app\v2\user\v0\logic\User as UserHandler;
use app\v2\user\v0\logic\Group;

/**
 * note          角色管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/8 16:19
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Role extends Base
{
    /**
     * 获取角色列表
     * @param array $params 参数
     * @return array
     */
    public function getRoleList($params = []): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $sortColumn = $params['sort'];//排序的参数
        $sortType = $params['order'];//排序类型
        $sortParams = array('', 'br.role_name', 'br.lock_flag',
            'bt.tenant_name', 'br.create_user_name', 'br.create_time');
        $sql = "select br.id, br.role_uuid, br.role_name, br.lock_flag, br.create_user_name,br.create_user_uuid,
                       br.create_time, bt.tenant_name, bt.tenant_uuid 
                from bd_role br left join bd_tenant bt 
                on br.tenant_uuid = bt.tenant_uuid ";
        $countsql = "select count(br.role_uuid) as count_all 
                    from bd_role br ";

        $user = xphp_get_user_info();
        $checkAuth = v2_auth_is_admin();
        $sqlParams = array();
        $sqlCountParams = array();
        $sqls = [];
        if($checkAuth['global_observer']){
            $sqls[] = " br.create_user_uuid not in ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9')";
        }else if(v2_auth_need_check_look()){
            $userUuidSql = v2_auth_get_users('');
            $sqls[] = " br.create_user_uuid in ({$userUuidSql}) ";
        }

        // 未添加租户 那么屏蔽系统初始化的租户相关的三个角色列表
        $tenantSql = "select tenant_uuid from bd_tenant";
        $tenantData = $this->dbSelect($tenantSql);
        // 未授权租户相关的权限模块 角色列表就不显示租户相关的名称
        // 说明，之所以不直接用模糊查询 租户 关键词， 是因为英文的需要一起兼容
        if (!in_array('tenant_manager', $user['permission']) || empty($tenantData)) {
            // 未授权 那么屏蔽系统初始化的租户相关的三个角色列表
            $sqls[] = " br.role_uuid not in 
            (
            'eee859d5-341a-b95a-33d0-6ed583d0f7a1',
            '2b214439-8f0b-ec23-a3be-2014ad9baecc',
            '03e12c93-9dc1-371a-6a64-b74965d36723',
            'a30f7728-2ef7-bca0-2224-07deba8ce3e5'
            )";
        }

        if(!empty($tenantData)){
            // 添加租户 那么只展示管理员、操作员、审计员、租户管理员
            $sqls[] = " br.role_uuid not in (
            'a30f7728-2ef7-bca0-2224-07deba8ce3e5',
            '2b214439-8f0b-ec23-a3be-2014ad9baecc',
            '03e12c93-9dc1-371a-6a64-b74965d36723'
            )";
        }

        // 非三权屏蔽角色sysadmin、safeadmin、auditor、operator
        if(!xphp_get_user_info()['isThreePowers']){
            $sqls[] = " br.role_uuid not in 
            (
            '695cd4e6-34c1-d55a-7526-c3ea8562112f',
            'c6f7a389-145c-5182-b37b-642a39a68b95',
            '95cdc633-5eea-644c-3555-f051440608c0',
            'b7345b13-692d-7d39-dd09-be42ae070238'
            )";
        }

        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] ==   xphp_get_config('app','VENDOR_LIST')['gmp']) {
            // gmp的需要屏蔽操作员和审计员
            $sqls[] = " br.role_uuid not in 
            (
            'a633143d-f5b3-b348-998d-a209d62e27f0',
            '7e891486-9d2c-603b-6fe1-8bc0d332f602'
            )";
        }

        if (!empty($params['role_type'])) {
            if ($params['role_type'] == 2) {
                // 只查询全局观察者角色
                $sqls[] = ' br.config = 2';
            } else {
                // 排除全局观察者角色
                $sqls[] = ' br.config != 2';
            }
        }

        $checkAuth = v2_auth_is_admin();
        if($checkAuth['global_observer']){
            $sqls[] = " br.role_uuid not in 
            ('32c4bd54-2448-a4ac-50d8-279edda0fb89',
            'a633143d-f5b3-b348-998d-a209d62e27f0',
            '7e891486-9d2c-603b-6fe1-8bc0d332f602',
            'eee859d5-341a-b95a-33d0-6ed583d0f7a1')";
        }else if(v2_auth_need_check_look()){
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v2_auth_get_users('');
            $sqls[] = " br.create_user_uuid in ({$userUuidSql}) ";
        }

        $where = !empty($sqls) ? ' where ' . implode(' and ', $sqls) : '';
        $sql .=  $where;
        $countsql .= $where;

        if (!empty($sortColumn) && !empty($sortType)) {
            $sql .= " order by $sortParams[$sortColumn] $sortType, id ";
        }

        $sql .= ' limit ?,?';
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $resultcountrole = $this->dbSelect($countsql,$sqlCountParams);
        $countrole = $resultcountrole[0]['count_all'];
        $info = array();
        $info['data'] = array();
        $pfDes = xphp_get_desc('Pf', 'ROLE_LOCK_FLAG_DES');
        $info['rows'] = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d['tenant_name'] == null) {
                    $tenantname = xphp_get_lang('UI_ROLE_GLOBAL');
                } else {
                    $tenantname = $d['tenant_name'];
                }

                // 超级管理员权限不能修改
                if ($d['role_uuid'] == 'a30f7728-2ef7-bca0-2224-07deba8ce3e5') {
                    $checkBox = false;
                } else {
                    $checkBox = true;
                }

                $createTime = $d['create_time'];
                if ($d['create_time'] == '0000-00-00 00:00:00') {
                    $createTime = xphp_get_config('app', 'TIMESPACE');
                }

                //出厂默认模板不可操作
//                 if(empty($d['create_user_name'])){
//                     $checkBox = "";
//                 }
                //创建者
                $createUser = $d['create_user_name'];
                if (empty($createUser)) {
                    $createUser = xphp_get_config('app', 'NULLSPACE');
                }
                $info['rows'][] = array(
                    'checked' => $checkBox,
                    'role_name' => $d['role_name'],
                    'lock_flag' => $d['lock_flag'],
                    'tenant_name' => $tenantname,
                    'create_user' => $createUser,
                    'create_time' => $createTime,
                    'role_uuid' => $d['role_uuid'],
                    'tenant_uuid' => $d['tenant_uuid'],
                    'create_user_name' => $d['create_user_name'],
                    'create_user_uuid' => $d['create_user_uuid']
                );
            }
        }
        $info['draw'] = $params['draw'];
        $info['total'] = $countrole;
        $info['recordsFiltered'] = $countrole;

        return $info;
    }

    /**
     * 获取角色列表用于分配
     * @param $params 参数
     * @return array
     */
    public function getRoleLists($params = []): array
    {
        $editFlag = $params['editflag'];
        $userGroupuuid = $params['usergroup_uuid'];
        $sqlRole = "select distinct br.role_uuid, br.role_name 
                    from bd_role br left join mt_user_group_role mugr 
                        on mugr.role_uuid = br.role_uuid where ";

        $where = " br.lock_flag = ? and br.create_user_uuid = ? ";

        $user = xphp_get_user_info();

        $sqlParams = array(xphp_get_config('app', 'FLAG')['SET'], $user['useruuid']);
        if (empty($user['tenantuuid']) && $user['userName'] == "admin") {
            $where .= " or br.create_user_uuid = '' ";
        }

        if ($editFlag && $userGroupuuid) {
            $where .= " or mugr.user_group_uuid =? ";
            $sqlParams = array_merge($sqlParams, array($userGroupuuid));
        }

        // 未授权租户相关的权限模块 角色列表就不显示租户相关的名称
        // 说明，之所以不直接用模糊查询 租户 关键词， 是因为英文的需要一起兼容
        if (!in_array('tenant_manager', $user['permission'])) {
            // 未授权 那么屏蔽系统初始化的租户相关的三个角色列表
            $where = '(' . $where . ") and br.role_uuid not in ('
            eee859d5-341a-b95a-33d0-6ed583d0f7a1',
            '2b214439-8f0b-ec23-a3be-2014ad9baecc',
            '03e12c93-9dc1-371a-6a64-b74965d36723'
            )";
        }

        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] == xphp_get_config('app','VENDOR_LIST')['gmp']) {
            // gmp的需要屏蔽操作员和审计员
            $where = '(' . $where . ") and br.role_uuid not in 
            (
            'a633143d-f5b3-b348-998d-a209d62e27f0',
            '7e891486-9d2c-603b-6fe1-8bc0d332f602'
            )";
        }

        $sqlRole .= $where;

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
     * 获取权限树 编辑/新增 角色的时候会用
     * @param array $params 参数
     * @return array
     */
    public function getUserPermission($params = []): array
    {
        $cachekey = md5('getUserPermission_' . getXphpUrl() . '_' . md5(json_encode(xphp_get_user_info())));
        $cache = cache($cachekey);
        if (empty($cache)) {
            $user = xphp_get_user_info();
            $page = xphp_get_menu();
            $roleHandler = new User();
            $userPermission = $roleHandler->pGetUserAllPermission($user['userUuid'], true);
            $tenantuuid = xphp_get_user_info()['tenantuuid'];
            if (!empty($tenantuuid)) {
                // 租户管理，添加角色不允许有系统管理
                $userPermission = array_diff($userPermission, ['sysmanagement']);
            }

            $cache = (new Group())->getUserAllTreeNodes(
                $page,
                0,
                $userPermission,
                $user['permissionArr']
            );
            cache($cachekey, $cache, 100); // 缓存100秒
        }

        return array(
            'nodes' => $cache,
        );
    }

    /**
     * 添加角色
     * {"rolename":"测试",
     * "permission":["homepage","p_homepage","monitor","task","current_job","p_current_job_manager","history_job","p_history_job_delete","p_history_job_download"]}
     * @param array $params 参数
     * @return array
     */
    public function addRole($params = []): array
    {

        $rolename = $params['rolename'];      //角色名字
        $opName = xphp_get_lang('UI_ROLE_ADD');
        //检查名字是否重复
        $this->checkRolenameExist($rolename, 'UI_ROLE_ADD', 'UI_ROLE_IDENTICAL');

        // 需要判断下是否是全局观察者，是的话，更改 config = 2，为了后面取角色的时候方便
        $isSuperRole = !in_array('global_observer', $params['permission']) ? 0 : 2;

        $permission = json_encode($params['permission']);   //page权限集合

        $user = xphp_get_user_info();

        //如果是租户内部创建
        $tenantuuid = !empty($user['tenantuuid']) ? $user['tenantuuid'] : '';//租户唯一标识

        //生成角色uuid
        $roleUUID = xphp_uuid();
        //生成权限uuid
        $permissionUUID = xphp_uuid();

        //是否上锁，1为启用，2为禁用
        $lockflag = xphp_get_config('app', 'FLAG')['SET'];
        //其他配置
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $createTime = date($dateformat);
        //向角色表中插入数据
        $roleParams = array($roleUUID, $rolename, $lockflag, $permissionUUID,
            $isSuperRole, $tenantuuid, $user['userUuid'], $user['userName'], $createTime);
        $roleSql = "insert into bd_role (role_uuid, role_name, lock_flag, permission_uuid,
                     config, tenant_uuid, create_user_uuid, create_user_name, create_time) 
                     values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        //向权限表中插入数据
        //type为1为系统所有默认权限不可删除，2分配出去的权限
        $permissiontype = xphp_get_config('app', 'FLAG')['UNSET'];
        $permissionname = $rolename;
        $permissionParams = array($permissionUUID, $permissionname, $permissiontype, $permission);
        $permissionSql = "insert into bd_permission (permission_uuid, name, type, content) values (?, ?, ?, ?)";

        $this->dbBeginTransaction(); //事务开始

        $result = $this->dbQuery($roleSql, $roleParams);
        $result = $result && $this->dbQuery($permissionSql, $permissionParams);

        if ($result) {
            //事务结束
            //添加系统操作日志
            $this->systemLog('SYSTEM_ROLE_ADD_SUCCESS', array($rolename));
            $this->dbCommit();
        } else {
            //事务回滚
            $this->dbRollBack();
        }

        return [$result, $opName];
    }

    /**
     * 得到角色用来修改的信息
     * @param string $roleuuid uuid
     * @return array
     */
    public function getRole(string $roleuuid): array
    {
        $sql = "select br.role_name, br.tenant_uuid, bp.content, bp.permission_uuid,br.config
                    from bd_role br, bd_permission bp
                    where br.permission_uuid = bp.permission_uuid and br.role_uuid = ? ";
        $data = $this->dbSelect($sql, array($roleuuid));
        $info = array();

        if (!empty($data)) {
            foreach ($data as $d) {
                $permission = json_decode($d['content'], true);
                $info = array(
                    'role_name' => $d['role_name'],
                    'tenant_uuid' => $d['tenant_uuid'],
                    'permission' => $permission,
                    'permission_uuid' => $d['permission_uuid'],
                );
            }
            $item = [xphp_get_user_info()['userName'], $info['role_name']];
            $this->systemLog('PT_INDUSTRY_USER_ROLE_LOOK', $item);
            if ($data[0]['config'] != 2) {
                // 去除全局观察者权限
                $info['permission'] = array_diff(
                    $info['permission'],
                    ['global_observer', 'global_read', 'global_write']
                );
            }
            // 还要判断当前角色是否已经分配给用户或者用户组.已经分配了那么就不允许更改角色类型
            $sql = "SELECT EXISTS(
                        SELECT 1 FROM mt_user_group_role WHERE role_uuid = '{$roleuuid}'
                        UNION ALL
                        SELECT 1 FROM mt_user_role WHERE role_uuid = '{$roleuuid}'
                        LIMIT 1
                    ) AS is_role_used";
            $check = $this->dbSelect($sql);
            $info['is_used'] = !empty($check[0]['is_role_used']);
        }
        return $info;
    }

    /**
     * 修改角色
     * @param array $params 参数
     * @return array
     */
    public function editRole($params = []): array
    {

        $roleUUID = $params['roleuuid'];
        $permissionUUID = $params['permissionuuid'];
        $permissionname = $params['rolename'];

        //检查名字是否重复
        $this->checkRolenameExist($permissionname, 'UI_ROLE_MODIFY', 'UI_ROLE_IDENTICAL', $roleUUID);

        $permission = json_encode($params['permission']);

        // 需要判断下是否是全局观察者，是的话，更改 config = 2，为了后面取角色的时候方便
        $isSuperRole = !in_array('global_observer', $params['permission']) ? 0 : 2;

        $user = xphp_get_user_info();

        // 如果是租户内部创建
        $tenantuuid = !empty($user['tenantuuid']) ? $user['tenantuuid'] : '';

        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $editTime = date($dateformat);
        //向角色表中插入数据

        $roleParams = array($isSuperRole, $permissionname, $tenantuuid, $editTime, $roleUUID);
        $roleSql = "update bd_role set config = ?, role_name = ?, tenant_uuid = ?, create_time = ?
                    where role_uuid = ?";
        //向权限表中插入数据
        //type为1为系统所有默认权限不可删除，2分配出去的权限
        $permissiontype = xphp_get_config('app', 'FLAG')['UNSET'];

        $permissionParams = array($permission, $permissionUUID);
        $permissionSql = "update bd_permission set content = ? where permission_uuid = ?";

        $this->dbBeginTransaction(); //事务开始

        $result = $this->dbExec($roleSql, $roleParams);
        $result = $result && $this->dbExec($permissionSql, $permissionParams);

        if ($result) {
            //事务结束
            $this->dbCommit();
            $item = [xphp_get_user_info()['userName'], $permissionname];
            $this->systemLog('PT_INDUSTRY_USER_ROLE_EDIT', $item);
        } else {
            //事务回滚
            $this->dbRollBack();
        }

        return [$result, xphp_get_lang('UI_ROLE_MODIFY')];
    }

    /**
     * 启用角色
     * @param array $params 参数
     * @return array
     */
    public function unlockRole($params = []): array
    {
        $rolelist = $params['role_uuid'];
        $roleStr = "('" . implode("','", $rolelist) . "')";
        $sql = "update bd_role set lock_flag= ? where role_uuid in {$roleStr}";
        $result = $this->dbExec($sql, array(xphp_get_config('app', 'FLAG')['SET']));
        $roles = $this->dbSelect("select GROUP_CONCAT(role_name) names from bd_role where role_uuid in {$roleStr}");
        $item = [xphp_get_user_info()['userName'], $roles[0]['names']];
        $this->systemLog('PT_INDUSTRY_USER_ROLE_ENABLE', $item);
        return array(
            'result' => $result
        );
    }

    /**
     * 禁用角色
     * @param array $params 参数
     * @return array
     */
    public function lockRole($params = []): array
    {
        $rolelist = $params['role_uuid'];
        $roleStr = "('" . implode("','", $rolelist) . "')";
        $sql = "update bd_role set lock_flag= ? where role_uuid in {$roleStr}";
        $result = $this->dbExec($sql, array(xphp_get_config('app', 'FLAG')['UNSET']));
        $roles = $this->dbSelect("select GROUP_CONCAT(role_name) names from bd_role where role_uuid in {$roleStr}");
        $item = [xphp_get_user_info()['userName'], $roles[0]['names']];
        $this->systemLog('PT_INDUSTRY_USER_ROLE_DISABLE', $item);
        return array(
            'result' => $result
        );
    }

    /**
     * 删除角色
     * @param array $params 参数
     * @return array
     */
    public function delRole($params = []): array
    {
        $roleStr = "('" . implode("','", $params['role_uuid']) . "')";
        $roles = $this->dbSelect("select GROUP_CONCAT(role_name) names from bd_role where role_uuid in {$roleStr}");
        $sql = "delete from bd_role where role_uuid in {$roleStr}";
        $result = $this->dbQuery($sql);
        $item = [xphp_get_user_info()['userName'], $roles[0]['names']];
        $this->systemLog('PT_INDUSTRY_USER_ROLE_DELETE', $item);
        return [$result, xphp_get_lang('UI_ROLE_DELETE')];
    }

    /**
     * 初始化角色关联用户、用户组列表
     * @param array $params 参数
     * @return array
     */
    public function initAllocationList($params = []): array
    {

        $sqlUser = "select user_uuid from mt_user_role where role_uuid = ?";
        $dataUser = $this->dbSelect($sqlUser, array($params['roleuuid']));

        $sqlUserGroup = "select user_group_uuid from mt_user_group_role where role_uuid = ?";
        $dataUserGroup = $this->dbSelect($sqlUserGroup, array($params['roleuuid']));

        $userList = !empty($dataUser) ? array_column($dataUser, 'user_uuid') : [];

        $userGroupList = !empty($dataUserGroup) ? array_column($dataUserGroup, 'user_group_uuid') : [];

        return [
            'user_list' => $userList,
            'user_group_list' => $userGroupList
        ];
    }

    /**
     * 添加角色和用户关联
     * @param array $params 参数
     * @return array
     */
    public function addUserRoleAllocation($params = []): array
    {

        $roleuuid = $params['roleuuid'];
        $userList = $params['userList'];

        //删除角色关联
        $sqlDelete = "delete from mt_user_role where role_uuid = ?";
        $result = $this->dbExec($sqlDelete, array($roleuuid));
        //未选中默认取消所有关联
        if (!empty($userList)) {
            //添加角色与用户关联
            $result = $this->pAddRoleUser($roleuuid, $userList);
        }
        $roles = $this->dbSelect("select role_name from bd_role where role_uuid = ?", [$roleuuid]);
        $item = [xphp_get_user_info()['userName'], $roles[0]['role_name']];
        $this->systemLog('PT_INDUSTRY_USER_ROLE_ALLOCATION', $item);
        return [$result, xphp_get_lang('WEB_ROLE_ALLOCATION_USER')];
    }

    /**
     * 添加角色和用户组关联
     * @param array $params 参数
     * @return array
     */
    public function addUsergroupRoleAllocation($params = []): array
    {

        $roleuuid = $params['roleuuid'];
        $userGroupList = $params['userGroupList'];

        //删除角色关联
        $sqlDelete = "delete from mt_user_group_role where role_uuid = ?";
        $result = $this->dbExec($sqlDelete, array($roleuuid));

        //未选中默认取消所有关联
        if (!empty($userGroupList)) {
            //添加角色与用户组关联
            $result = $this->pAddRoleUserGroup($roleuuid, $userGroupList);
        }
        $roles = $this->dbSelect("select role_name from bd_role where role_uuid = ?", [$roleuuid]);
        $item = [xphp_get_user_info()['userName'], $roles[0]['role_name']];
        $this->systemLog('PT_INDUSTRY_USER_ROLE_ALLOCATIONS', $item);
        return [$result, xphp_get_lang('WEB_ROLE_ALLOCATION_USER_GROUP')];
    }

    /**
     * 检查角色名字是否创建重复
     * @param string $roleName 角色名字
     * @param string $opName   操作提示名称标识
     * @param string $msg      操作提示内容标识
     * @param string $roleuuid 编辑时的角色标识
     * @return boolean
     */
    public function checkRolenameExist(string $roleName, string $opName, string $msg, $roleuuid = ''): bool
    {
        $sql = "select role_uuid from bd_role where binary role_name = ? ";
        $data = $this->dbSelect($sql, array($roleName));
        if (!empty($data)) {
            if (!empty($roleuuid) && count($data) == 1 && $roleuuid == $data[0]['role_uuid']) {
                // 编辑 并且只存在一个的情况下 并且是相同
                return true;
            }
            // 其它直接抛出
            $this->muOpResult(false, xphp_get_lang($opName), xphp_get_lang($msg), 'warning');
        }
        return true;
    }

    /**
     * 添加角色和用户关联
     * @param string $roleuuid 角色唯一标识
     * @param array  $userList 用户唯一标识列表
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pAddRoleUser(string $roleuuid, $userList = []): bool
    {
        if (empty($userList)) {
            return false;
        }

        $sql = "insert into mt_user_role (role_uuid,user_uuid) values ";
        foreach ($userList as $key => $l) {
            if ($key != (count($userList) - 1)) {
                $sql .= "('" . $roleuuid . "','" . $l . "'),";
            } else {
                $sql .= "('" . $roleuuid . "','" . $l . "')";
            }
        }
        return $this->dbExec($sql);
    }

    /**
     * 添加角色和用户组关联
     * @param string $roleuuid      角色唯一标识
     * @param array  $userGroupList 用户组唯一标识列表
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pAddRoleUserGroup(string $roleuuid, $userGroupList = []): bool
    {
        if (empty($userGroupList)) {
            return false;
        }

        $sql = "insert into mt_user_group_role (role_uuid, user_group_uuid) values ";
        foreach ($userGroupList as $key => $l) {
            $sql .= "('" . $roleuuid . "','" . $l . "')";
            if ($key != (count($userGroupList) - 1)) {
                $sql .= ',';
            }
        }
        return $this->dbExec($sql);
    }

    /**
     * 获取用户列表用于分配
     * @param unknown $params
     */
    public function getUserList($params = [])
    {
        $userGroupFlag = $params['usergroupflag']; //用户组添加修改标志
        $roleType = $params['role_type']; // 全局观察者标识
        $buildSql = ',(select GROUP_CONCAT(config) from bd_role where role_uuid in
         (select role_uuid from mt_user_role where user_uuid = bu.user_uuid)) roles';
        $sqlUserALL = "select bt.tenant_name,bu.user_uuid, bu.user_name {$buildSql} from bd_user bu left join
                        mt_user_tenant mut on mut.user_uuid = bu.user_uuid
                        left join bd_tenant bt on mut.tenant_uuid = bt.tenant_uuid where bu.lock_flag = ? ";
        $sqlParams = array(xphp_get_config('app')['FLAG']['SET']);

        // 如果是三权模式下 那么就只能读取操作员 即user_level 为 5，不然就是 0
        if (xphp_three_powers()) {
            $sqlUserALL .= ' and bu.user_level = 5 ';
        } else {
            // 不是三权 那么不显示默认的三权用户
            $user = xphp_get_user_info();
            // admin管理也不展示
            $where = ' and bu.user_level = 0';
            $checkAuth = v2_auth_is_admin();
            if ($checkAuth['global_observer']) {
                // 全局观察者不能看到admin创建的用户
                $where .= " and (bu.create_user_uuid != ?) and (bu.user_uuid != ?)";
                $sqlParams = array_merge($sqlParams, array('a508b813-19c7-eb4e-d6fa-bb61b25a4de9','a508b813-19c7-eb4e-d6fa-bb61b25a4de9'));
            } elseif ($user['userLevel'] != 1) {
                //如果不是超级管理员,显示自己下级或管理的用户
                $useruuid = $user['userUuid'];
                $where .= " and (bu.create_user_uuid = ? or bu.manager_uuid = ? ) ";
                $sqlParams = array_merge($sqlParams, array($useruuid, $useruuid));
            }
            $sqlUserALL .= $where;
        }

        $dataUser = $this->dbSelect($sqlUserALL, $sqlParams);
        $info = array();
        foreach ($dataUser as $d) {
            $username = $d['user_name'];
            if (!empty($d['tenant_name'])) {
                $username .= '(' . $d['tenant_name'] . ')';
                if (empty(xphp_get_user_info()['tenantuuid']) && $userGroupFlag) {
                    //如果是添加用户组 不与租户管理员关联
                    continue;
                }
            }

            if (!empty($roleType)) {
                $roles = array_unique(explode(',', $d['roles']));
                if ($roleType == 2 && !in_array(2, $roles)) {
                    // 全局观察者但是未包含
                    continue;
                }
                if ($roleType != 2 && in_array(2, $roles)) {
                    // 排除全局观察者
                    continue;
                }
            }
            $info[] = array(
                'user_uuid' => $d['user_uuid'],
                'user_name' => $username
            );
        }
        return $info;
    }

    /**
     * 获取用户组列表用于分配
     * @param unknown $params
     */
    public function getUsergroupList($params = [])
    {
        $editFlag = $params['editflag'];
        $useruuid = $params['useruuid'];
        //获取用户租户信息
        $tenantInfo = $this->getUserTenantInfo($useruuid);
        $sqlUserGroup = "select distinct bug.user_group_uuid, bug.user_group_name 
from bd_user_group bug left join mt_user_group_tenant mugt on bug.user_group_uuid = mugt.user_group_uuid 
    left join mt_user_user_group muug on bug.user_group_uuid = muug.user_group_uuid  ";
        $where = "bug.lock_flag = ? and bug.create_user_uuid = ? ";
        $sqlParams = array(xphp_get_config('app')['FLAG']['SET'], xphp_get_user_info()['userUuid']);
        if (empty(xphp_get_user_info()['tenantuuid']) && xphp_get_user_info()['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            $where .= " or bug.create_user_uuid = '' ";
        }

        //修改用户显示相应管理的所有用户组
        if ($editFlag) {
            $where .= ' or muug.user_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }
        $sqlUserGroup .= " where ($where) and bug.user_group_uuid != 'e99ae858-d549-4754-9310-457477f4c2e3'";
        $data = $this->dbSelect($sqlUserGroup, $sqlParams);
        $info = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                //如果在租户外修改租户管理员，只显示创建时关联的用户组
                if (empty(xphp_get_user_info()['tenantuuid']) && !empty($tenantInfo['tenant_uuid'])) {
                    if (!in_array($d['user_group_uuid'], $tenantInfo['usergroup_list'])) {
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
     * 获取修改用户的租户等信息
     * @param string $useruuid
     */
    public function getUserTenantInfo($useruuid)
    {
        //获取租户uuid
        $sqlTenant = "select mut.tenant_uuid from bd_user bu, mt_user_tenant mut where bu.user_uuid = mut.user_uuid and bu.user_uuid = ? ";
        $dataTenant =  $this->dbSelect($sqlTenant, array($useruuid));
        $tenantuuid = '';
        if (!empty($dataTenant)) {
            $tenantuuid = $dataTenant[0]['tenant_uuid'];
        }

        //获取用户组列表
        $sqlUsergroup = "select muug.user_group_uuid from bd_user bu, mt_user_user_group muug where bu.user_uuid = muug.user_uuid and bu.user_uuid = ?";
        $dataUsergroup = $this->dbSelect($sqlUsergroup, array($useruuid));
        $usergroupList = array();
        if (!empty($dataUsergroup)) {
            foreach ($dataUsergroup as $usergroup) {
                $usergroupList[] = $usergroup['user_group_uuid'];
            }
        }

        //获取角色列表
        $sqlRole = "select mur.role_uuid from bd_user bu, mt_user_role mur where bu.user_uuid = mur.user_uuid and bu.user_uuid = ? ";
        $dataRole = $this->dbSelect($sqlRole, array($useruuid));
        $roleList = array();
        if (!empty($dataRole)) {
            foreach ($dataRole as $role) {
                $roleList[] = $role['role_uuid'];
            }
        }

        $info = array(
            'tenant_uuid' => $tenantuuid,
            'usergroup_list' => $usergroupList,
            'role_list' => $roleList
        );

        return $info;
    }

    /**
     * 获取用户关联用户组
     * @param unknown $params
     * @return string
     */
    public function getRoleUser($params = [])
    {
        $roleuuid = $params['roleuuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $userList = $this->pGetRoleAllUser($roleuuid);
        $records = array();
        $records['rows'] = array();
        $id = $start + 1;
        $count = count($userList);
        $userList = v2_array_sort($userList, 'user_name', 'asc', $start, $length);
        foreach ($userList as $d) {
            $username = $d['user_name'];
            if (empty(xphp_get_user_info()['tenantuuid']) && !empty($d['tenant_name'])) {
                $username .= '(' . $d['tenant_name'] . ')';
            }
            $records['rows'][] = array(
                'id' => $id,
                'username' => $username,
            );
            $id++;
        }
        $records['total'] = $count;
        return $records;
    }

    /**
     * 获取用户关联用户组
     * @param unknown $params
     * @return string
     */
    public function getRoleUserGroup($params = [])
    {
        $roleuuid = $params['roleuuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $userGroupList = $this->pGetRoleAllUserGroup($roleuuid);
        $records = array();
        $records['rows'] = array();
        $id = $start + 1;
        $count = count($userGroupList);
        $userGroupList = v2_array_sort($userGroupList, 'user_group_name', 'asc', $start, $length);
        foreach ($userGroupList as $d) {
            $records['rows'][] = array(
                'id' => $id,
                'user_group_name' => $d['user_group_name'],
            );
            $id++;
        }

        $records['total'] = $count;
        return $records;
    }

    /**
     * 得到角色用来修改的信息
     * @param array $params 请求参数
     * @return array
     */
    public function getRoleOldInfo(array $params)
    {
        $roleuuid = $params['roleuuid'];
        $useruuid = $params['user_uuid'];
        $sql = "select br.role_name, br.tenant_uuid, bp.content, bp.permission_uuid, br.config 
                from bd_role br, bd_permission bp
                where br.permission_uuid = bp.permission_uuid and br.role_uuid = ? ";
        $data = $this->dbSelect($sql, array($roleuuid));
        $info = [];

        if (!empty($data)) {
            foreach ($data as $d) {
                $info = array(
                    'rolename' => $d['role_name'],
                    'user_auth' => [],
                    'tenantuuid' => $d['tenant_uuid'],
                    'permission' => json_decode($d['content'], true),
                    'permissionuuid' => $d['permission_uuid']
                );
            }
            $permission = $info['permission'];
            if (!in_array(2, array_column($data, 'config'))) {
                // 那么删除对应的全局观察者权限
                $info['permission'] = array_diff($permission, ['global_observer', 'global_read', 'global_write']);
            }
            // 还需要和授权系统进行匹配，剔除未授权的
            $info['permission'] = (new User())->getAuthSoftwarePermission($info['permission'], '');
            // 需要把操作那一层的给找回来
            $page = xphp_get_menu('', false, true);

            $pageArr = v2_multi_to_two($page); // 父类(name) => array( 类(name) => array( 'method1', 'method2'))

            // 和左侧的菜单最终授权比较， 保留有权限的子类  子类(name) => array( 'method1', 'method2')
            // 1也就是取出的是最终的授权文件下的所有类和方法
            $array = [];
            foreach ($pageArr as $key => $item) {
                if (in_array($key, $info['permission'])) {
                    // 如果存在这个父类 那么这个类下面的所有子类就保留
                    foreach ($item as $key2 => $item2) {
                        if (in_array($key2, $permission)) {
                            $array[] = $key2;
                        }
                    }
                }
            }
            $info['permission'] = array_merge($info['permission'], $array);
            if (!empty($useruuid)) {
                $auth = dbSelect('select user_auth from bd_user where user_uuid = ?', [$useruuid]);
                $info['user_auth'] = json_decode($auth[0]['user_auth'], true) ?? [];
            }
        }

        return $info;
    }

    /**
     * 获取角色权限树
     * @param array $params 参数
     * @return string
     */
    public function getRolePermissionTree($params)
    {
        $roleuuid = $params['roleuuid'];
        $oldpath = xphp_get_oldpath();
        $page = getConfig($oldpath . 'api/xphp/conf/page.php');
        $rolePermission = (new User())->pGetRoleAllPermission($roleuuid);
        // 更改 获取更深层次的操作
        $rolePermission = (new User())->gGetPageLatest($rolePermission, $roleuuid, 1);

        $userTree = (new Group())->getUserAllTreeNodes($page, 0, $rolePermission, true);
        $userInfo = $userTree;

        return $userInfo;
    }

    /**
     * 公共方法
     * 获取角色关联所有用户组
     * @param string $roleuuid 角色uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_user_group表所有字段
     */
    public function pGetRoleAllUserGroup($roleuuid)
    {
        $this->paramsCheck($roleuuid);
        $sql = "select distinct bug.user_group_uuid, bug.user_group_name, bug.user_group_type, 
                bug.lock_flag, bug.description, bug.config
                from bd_role br, bd_user_group bug, mt_user_group_role mugr
                where br.role_uuid = mugr.role_uuid 
                and mugr.user_group_uuid = bug.user_group_uuid and br.role_uuid = ?";
        $data = $this->dbSelect($sql, array($roleuuid));
        return $data;
    }

    /**
     * 公共方法
     * 获取角色关联所有用户
     * @param string $roleuuid 角色uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_user表所有字段
     */
    public function pGetRoleAllUser($roleuuid)
    {
        $this->paramsCheck($roleuuid);
        $sql = "select distinct bu.user_uuid, bu.user_name, bu.email, bu.telephone, bu.lock_flag, bu.create_time, bu.language, bu.user_type, bt.tenant_name
                from bd_role br, mt_user_role mur, bd_user bu left join mt_user_tenant mut on mut.user_uuid = bu.user_uuid left join bd_tenant bt on bt.tenant_uuid = mut.tenant_uuid 
                where br.role_uuid = mur.role_uuid and mur.user_uuid = bu.user_uuid and br.role_uuid = ? and bu.user_uuid != ? ";
        $data = $this->dbSelect($sql, array($roleuuid, xphp_get_user_info()['userUuid']));
        return $data;
    }

    /**
     * 公共方法
     * 获取用户所有权限(包括用户和所在用户组的权限)
     * @param string $useruuid 用户uuid
     * @author xiezhuowei@vinchin.com
     * @return array 一维数组,合并后的所有权限
     */
    public function pGetUserAllPermission($useruuid, $pageFlag = false)
    {
        $this->paramsCheck($useruuid);
        if ($useruuid == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            // 超级管理员直接以授权的为准,那么默认的 permission 这个就是读取所有的菜单配置
            $permission = v2_get_all_name();
        } else {
            //先获取用户的直接关联角色对应的权限
            $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content from
                bd_user bu, mt_user_role mur, bd_role br, bd_permission bp 
                where bu.user_uuid = mur.user_uuid and mur.role_uuid = br.role_uuid and 
                br.permission_uuid = bp.permission_uuid and bu.user_uuid = ? and br.lock_flag = ?";
            $data1 = $this->dbSelect($sql, array($useruuid, xphp_get_config('app')['FLAG']['SET']));

            //再获取用户所在用户组关联的角色对应的权限
            $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_role mugr, bd_role br, bd_permission bp 
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugr.user_group_uuid and bug.user_group_uuid = mugr.user_group_uuid
                and mugr.role_uuid = br.role_uuid and br.permission_uuid = bp.permission_uuid and bu.user_uuid = ? and br.lock_flag = ? and bug.lock_flag = ?";
            $data2 = $this->dbSelect($sql, array($useruuid,  xphp_get_config('app')['FLAG']['SET'],  xphp_get_config('app')['FLAG']['SET']));

            //合并两个结果权限,先合并,再去重
            $data = array_merge($data1, $data2);
            $data = v2_unique_multidim_array($data, 'permission_uuid');

            //合并所有权限
            $permission = array();
            foreach ($data as $d) {
                $permission = array_merge($permission, json_decode($d['content'], true));
            }
            $permission = array_unique($permission);
        }

        //租户内用户强制不显示副本和归档
        $userHandler = UserHandler::instance();
        /*$tenantuuid = $userHandler->pGetUserTenantUUID($useruuid);
        if(!empty($tenantuuid)){
            $permission = array_diff($permission, ["datacopy", "data_archive", "vm_overview", "orch", "setting_manager", "authorization_module", "storage_report"]);
        }*/
        //角色权限创建以及页面显示
        if ($pageFlag) {
            return $userHandler->getAuthSoftwarePermission($permission, $useruuid);
        } else {
            $extension = (new Index())->getExtensionLicense();
            if (!empty($extension)) {
                $authFun = $extension['f'];
                // 这里需要判读下可视化大屏的权限，因为这个从授权系统那边过来的
                // 如果是master的话 那么主动追加上 p_visual_screen 大屏权限
                if ($userHandler->pCheckUserIsMaster($useruuid) && $authFun['visualization']) {
                    $permission = array_merge($permission, ['p_visual_screen']);
                }

                // 重新授权没有大屏的，那么这里直接给去掉
                if (!$authFun['visualization']) {
                    $permission = array_diff($permission, ['p_visual_screen']);
                }
            }

            return $permission;
        }
    }

    /**
     * 公共方法
     * 判断某个用户是否有某个操作的权限,如果没有会退出程序,慎用
     * 只判断到用户是否能够做某种操作,没有检查是否对特定资源是否具有权限
     * @param string $useruuid  用户uuid
     * @param string $operation 操作,对应page页面的每一项
     * @param stinrg $operate   操作描述,如"删除用户"
     * @author xiezhuowei@vinchin.com
     * @return boolean  有权限返回true,否则直接退出并按标准错误报错
     */
    public function pOperationPermissionCheckExit($operation)
    {
        // 权限校验放在登录之后获取保存的session数组里面 permissionArr 这里不需要做任何校验
        return true;
        //如果是admin系统管理员直接返回
        if (
            empty(xphp_get_user_info()['tenantuuid'])
            && xphp_get_user_info()['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9'
        ) {
            return true;
        }
        $this->paramsCheck($operation);
        $permission = $this->pGetUserAllPermission(xphp_get_user_info()['userUuid']);
        $result = in_array($operation, $permission);
        if (!$result) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_ROLE_OPERATION_NOT_AUTH'), xphp_get_lang('WEB_ERROR_BD_ERRNO_EACCES'), 'error'));
        }
        return true;
    }
}
