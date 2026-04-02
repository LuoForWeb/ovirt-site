<?php

namespace app\v1\user\v0\logic;

use app\v1\common\logic\Base;

/**
 * note          用户组 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/7 17:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Group extends Base
{
    /**
     * 获取用户组表格数据
     * @param array $params 参数
     * @return array
     */
    public function getGroupList($params = []): array
    {

        $sql = "select distinct bug.user_group_uuid,bug.user_group_name, bug.user_group_type,
                bug.lock_flag,bug.description,
                bug.create_user_name, bug.create_time, bug.create_user_uuid, bt.tenant_uuid, bt.tenant_name
                from bd_user_group bug left join mt_user_group_tenant mugt on bug.user_group_uuid = mugt.user_group_uuid
                    left join bd_tenant bt on mugt.tenant_uuid = bt.tenant_uuid ";
        $sqlcount = "select count(distinct bug.user_group_uuid) as count_all from bd_user_group bug
    left join mt_user_group_tenant mugt on bug.user_group_uuid = mugt.user_group_uuid
    left join bd_tenant bt on mugt.tenant_uuid = bt.tenant_uuid ";

        $user = xphp_get_user_info();
        if (empty($user['tenantuuid']) && $user['userLevel'] == 1) {
            $sql .= " or bug.create_user_uuid = '' ";
            $sqlcount .= " or bug.create_user_uuid = '' ";
        }
        $checkAuth = v1_auth_is_admin();
        if($checkAuth['global_observer']){
            // 全局观察者不能看到admin创建的用户组
            $sqlNew = " where bug.user_group_uuid not in 
            ('e99ae858-d549-4754-9310-457477f4c2e3',
            '372fa0bf-d1b0-46ec-864c-cd5592813669',
            '09d1c727-d87b-494a-81bf-fb9014ed313c',
            'd57a9f76-845d-43a8-bb79-bdafbf405c85') and 
            bug.create_user_uuid != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9'";
            $sql .= $sqlNew;
            $sqlcount .= $sqlNew;
        }else if(v1_auth_need_check_look()){
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sqlNew = " where bug.create_user_uuid in ({$userUuidSql}) ";
            $sql .= $sqlNew;
            $sqlcount .= $sqlNew;
        }

        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] ==   xphp_get_config('app','VENDOR_LIST')['gmp']) {
            // gmp的需要屏蔽操作组和审计组
            $sqlNew = " where bug.user_group_uuid not in 
            (
            '09d1c727-d87b-494a-81bf-fb9014ed313c',
            'd57a9f76-845d-43a8-bb79-bdafbf405c85'
            )";
            $sql .= $sqlNew;
            $sqlcount .= $sqlNew;
        }

        if (!empty($params['sort']) && in_array(strtolower($params['order']), ['asc', 'desc'])) {
            // 排序
            $paramsusergroup = [
                'user_group_name' => 'bug.user_group_name',
                'user_group_type' => 'bug.user_group_type',
                'lock_flag'       => 'bug.lock_flag',
                'description'     => 'bug.description',
                'tenant_name'     => 'bt.tenant_name',
                'create_user_name' => 'bug.create_user_name',
                'create_time'     => 'bug.create_time',
                'create_user_uuid' => 'bug.create_user_uuid'
            ];

            $order = !empty($paramsusergroup[$params['sort']]) ?
                $paramsusergroup[$params['sort']] : 'bug.user_group_uuid';
            $sql .= " order by " .  $order . ' ' . $params['order'];
        }
        $sql .= ' limit ? , ? ';
        $sqlParams = array($params['offset'], $params['limit']);
        $resultsqlall = $this->dbSelect($sql, $sqlParams);
        $resultcount = $this->dbSelect($sqlcount);

        $info = array();
        $info['rows'] = array();

        $nullspace = xphp_get_config('app', 'NULLSPACE');
        foreach ($resultsqlall as $d) {
            if ($d['tenant_uuid'] == null) {
                $tenantname = xphp_get_lang('UI_ROLE_GLOBAL');
                $checkBox = false;
            } else {
                $tenantname = $d['tenant_name'];
                $checkBox = true;
            }
            $checkBox = false;
            if ($d['user_group_type'] != xphp_get_config('user')['USER_GROUP_TYPE']['DEFAULT']) {
                $checkBox = true;
            }
            $info['rows'][] = [
                'checked' => $checkBox,
                'user_group_uuid'   => $d['user_group_uuid'],
                'user_group_name'   => $d['user_group_name'],
                'user_group_type'   => xphp_get_desc('Pf', 'USER_GROUP_TYPE_DES')[intval($d['user_group_type'])],
                'lock_flag'   => $d['lock_flag'],
                'description'   => $d['description'],
                'tenant_name'   => $tenantname,
                'create_user_name'   => empty($d['create_user_name']) ? $nullspace : $d['create_user_name'],
                'create_time'   => ($d['create_time'] == '0000-00-00 00:00:00') ? $nullspace : $d['create_time'],
                'create_user_uuid' => $d['create_user_uuid']
            ];
        }
        $info['total'] = intval($resultcount[0]['count_all']);
        return $info;
    }

    /**
     * 新建用户组
     * {"userGroupName":"用户组测试",
     * "userGroupDescription":"用户组说明",
     * "userGroupRoleUUID":["32c4bd54-2448-a4ac-50d8-279edda0fb89"],  角色uuid集合
     * "userGroupUserUUID":["272dc8ac-dcc0-beb8-c28c-48c5ea2622a1"]}   用户uuid集合
     * @param array $params 参数
     * @return array
     */
    public function addGroup($params = [])
    {

        $userGroupName = $params['user_group_name'];
        $userGroupDescription = $params['description'];
        $userGroupTenantUUID = '';

        // 验证用户组是否存在
        $this->checkGroupnameExist($userGroupName, 'UI_USER_GROUP_ADD', 'UI_USER_GROUP_IDENTICAL');

        $user = xphp_get_user_info();

        //如果是租户内部创建
        if (!empty($user['tenantuuid'])) {
            $userGroupTenantUUID = $user['tenantuuid'];
        }
        $userGroupRoleUUIDlist = $params['role'];
        $userGroupUserUUIDlist = $params['user'];

        //生成userGroup的uuid
        $userGroupUUID = xphp_uuid();
        //1为默认组不可删除，2为全局组,初始化创建，3为租户组，这里创建默认为2
        $userGroupType = xphp_get_config('user', 'USER_GROUP_TYPE')['GLOBAL'];
        if (!empty($userGroupTenantUUID)) {
            $userGroupType = xphp_get_config('user', 'USER_GROUP_TYPE')['TENANT'];
        }
        //默认上锁为启用状态1（2为禁用）
        $lockFlag = xphp_get_config('app', 'FLAG')['SET'];
        //其他配置
        $config = '';

        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $createTime = date($dateformat);
        $paramsusergroup = array($userGroupUUID, $userGroupName, $userGroupType, $lockFlag,
            $userGroupDescription, $config, $user['userUuid'], $user['userName'], $createTime);
        $sqlusergroup = "insert into bd_user_group(user_group_uuid,user_group_name,user_group_type,lock_flag,
                          description,config,create_user_uuid,create_user_name,create_time) values(?,?,?,?,?,?,?,?,?)";
        $resultusergroup = $this->dbExec($sqlusergroup, $paramsusergroup);

        if ($resultusergroup) {
            //插入数据到关联表
            $this->pAddUserGroupTenant(array($userGroupUUID), $userGroupTenantUUID);
            $this->pAddUserGroupRole($userGroupUUID, $userGroupRoleUUIDlist);
            $this->pAddUserGroupUser($userGroupUUID, $userGroupUserUUIDlist);

            //添加系统操作日志
            $this->systemLog('SYSTEM_USER_GROUP_ADD_SUCCESS', array($userGroupName));
            return true;
        }
        return false;
    }

    /**
     * 获取单个用户组信息
     * @param string $uuid 用户组ID
     * @return array
     */
    public function getGroup(string $uuid)
    {

        $sql = "select user_group_name,description from bd_user_group where user_group_uuid =?";
        //查询用户组名称以及描述
        $result = $this->dbSelect($sql, array($uuid));

        $sql1 = "select tenant_uuid from mt_user_group_tenant where user_group_uuid =?";
        //查询用户组对应的租户id
        $result1 = $this->dbSelect($sql1, array($uuid));

        $sql2 = "select role_uuid from mt_user_group_role where user_group_uuid =?";
        //查询用户组对应的角色id集合
        $result2 = $this->dbSelect($sql2, array($uuid));
        $sql3 = "select user_uuid from mt_user_user_group where user_group_uuid =?";
        //查询用户组对应的用户id集合
        $result3 = $this->dbSelect($sql3, array($uuid));

        //判断tenant__uuid是否为空，如为空值则赋值为空
        foreach ($result2 as $d) {
            $inforole[] = array($d['role_uuid']);
        }
        foreach ($result3 as $d) {
            $infouser[] = array($d['user_uuid']);
        }

        $item = [xphp_get_user_info()['userName'], $result[0]['user_group_name']];
        $this->systemLog('PT_INDUSTRY_USER_GROUP_LOOK', $item);
        return [
            'user_group_uuid'   =>  $uuid,
            'user_group_name'   =>  $result[0]['user_group_name'],
            'description'   =>  $result[0]['description'],
            'tenant_uuid'   =>  $result1[0]['tenant_uuid'],
            'role'   =>  $inforole ?? [],
            'user'   =>  $infouser ?? [],
        ];
    }

    /**
     * 编辑用户组
     * @param array $params 参数
     * @return array
     */
    public function editGroup($params = [])
    {

        $userGroupName = $params['user_group_name'];
        $userGroupDescription = $params['description'];

        $userGroupUUID = $params['user_group_uuid'];

        // 验证用户组是否存在
        $this->checkGroupnameExist($userGroupName, 'UI_USER_GROUP_MODIFY', 'UI_USER_GROUP_IDENTICAL', $userGroupUUID);

        $userGroupRoleUUIDlist = $params['role'];
        $userGroupUserUUIDlist = $params['user'];

        //修改前先删除对应关联关系  用户|角色
        $this->pDeleteUserGroupUser($userGroupUUID, true);
        $this->pDeleteUserGroupRole($userGroupUUID);

        $sqldeleterole = "delete from mt_user_group_role where user_group_uuid = ?";
        $this->dbExec($sqldeleterole, array($userGroupUUID));
        $sqldeleteuser = "delete from mt_user_user_group where user_group_uuid = ?";
        $this->dbExec($sqldeleteuser, array($userGroupUUID));
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $editTime = date($dateformat);
        $paramsusergroup = array($userGroupName, $userGroupDescription, $editTime, $userGroupUUID);
        $sql = "update bd_user_group set user_group_name = ?,description = ?,create_time = ? where user_group_uuid = ?";
        $result = $this->dbExec($sql, $paramsusergroup);
        if ($result) {
            //插入数据到关联表
            //      $this->addUsergroupTenant($userGroupUUID,$userGroupTenantUUID);
            $this->pAddUserGroupRole($userGroupUUID, $userGroupRoleUUIDlist);
            $this->pAddUserGroupUser($userGroupUUID, $userGroupUserUUIDlist);
            $item = [xphp_get_user_info()['userName'], $userGroupName];
            $this->systemLog('PT_INDUSTRY_USER_GROUP_EDIT', $item);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 解锁用户组
     * @param array $datalist list
     * @return array
     */
    public function unlockGroup($datalist = []): array
    {
        $string = "('" . implode("','", $datalist['usergroups_uuid']) . "')";
        //lock_flag为2为禁用
        $sql = "update bd_user_group set lock_flag = 1 where user_group_uuid in {$string}";
        $result = $this->dbExec($sql);
        $group = $this->dbSelect(
            "select GROUP_CONCAT(user_group_name) names from bd_user_group where user_group_uuid in {$string}"
        );
        $item = [xphp_get_user_info()['userName'], $group[0]['names']];
        $this->systemLog('PT_INDUSTRY_USER_GROUP_ENABLE', $item);
        return array(
            'result' => $result
        );
    }

    /**
     * 锁定用户组
     * @param array $datalist list
     * @return array
     */
    public function lockGroup($datalist = []): array
    {

        $string = "('" . implode("','", $datalist['usergroups_uuid']) . "')";
        // lock_flag为2为禁用
        $sql = "update bd_user_group set lock_flag = 2 where user_group_uuid in {$string}";

        $result = $this->dbExec($sql);
        $group = $this->dbSelect(
            "select GROUP_CONCAT(user_group_name) names from bd_user_group where user_group_uuid in {$string}"
        );
        $item = [xphp_get_user_info()['userName'], $group[0]['names']];
        $this->systemLog('PT_INDUSTRY_USER_GROUP_DISABLE', $item);
        return array(
            'result' => $result
        );
    }

    /**
     * 删除用户组
     * @param string $datalist list
     * @return array
     */
    public function delGroup($datalist = [])
    {
        $usergroupDes = implode("','", $datalist['usergroups_uuid']);
        $group = $this->dbSelect(
            "select GROUP_CONCAT(user_group_name) names from bd_user_group
                        where user_group_uuid in ('" . $usergroupDes . "')"
        );
        foreach ($datalist['usergroups_uuid'] as $uuid) {
            // 删除用户组与用户|角色|租户|资源|资源组关联
            $this->pDeleteUserGroupUser($uuid);
            $this->pDeleteUserGroupRole($uuid);
            $this->pDeleteUserGroupTenant($uuid);
            $this->pDeleteUserGroupALLResource($uuid);
            $this->pDeleteUserGroupAllResourceGroup($uuid);
        }
        $sql = "delete from bd_user_group where user_group_uuid in ('" . $usergroupDes . "')";
        $result = $this->dbExec($sql);

        if ($result) {
            $item = [xphp_get_user_info()['userName'], $group[0]['names']];
            $this->systemLog('PT_INDUSTRY_USER_GROUP_DELETE', $item);
            return true;
        }

        return false;
    }

    /**
     * 获取用户组列表用于分配
     * @param array $params 参数
     * @return array
     */
    public function getUserGroupList($params = []): array
    {
        $editFlag = $params['editflag'];
        $useruuid = $params['useruuid'];

        if (!empty($useruuid)) {
            //获取用户租户信息
            $tenantInfo = $this->getUserTenantInfo($useruuid);
        }

        $user = xphp_get_user_info();

        $sqlUserGroup = "select distinct bug.user_group_uuid, bug.user_group_name 
from bd_user_group bug left join mt_user_group_tenant mugt on bug.user_group_uuid = mugt.user_group_uuid 
    left join mt_user_user_group muug on bug.user_group_uuid = muug.user_group_uuid 
where bug.lock_flag = ? and bug.create_user_uuid = ? ";
        $sqlParams = array(xphp_get_config('app', 'FLAG')['SET'], $user['userUuid']);
        if (empty($user['tenantuuid']) && $user['userName'] == "admin") {
            $sqlUserGroup .= " or bug.create_user_uuid = '' ";
        }

        //修改用户显示相应管理的所有用户组
        if ($editFlag) {
            $sqlUserGroup .= ' or muug.user_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }
        $data = $this->dbSelect($sqlUserGroup, $sqlParams);
        $info = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                //如果在租户外修改租户管理员，只显示创建时关联的用户组
                if (empty($user['tenantuuid']) && !empty($tenantInfo['tenant_uuid'])) {
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
     * 检查用户组名字是否创建重复
     * @param string $groupName group
     * @param string $opName    操作提示名称标识
     * @param string $msg       操作提示内容标识
     * @param string $groupuuid uuid
     * @return boolean
     */
    private function checkGroupnameExist(string $groupName, string $opName, string $msg, $groupuuid = '')
    {
        $sql = "select user_group_uuid from bd_user_group where user_group_name = ? ";
        $data = $this->dbSelect($sql, array($groupName));
        if (!empty($data)) {
            if (!empty($groupuuid) && count($data) == 1 && $groupuuid == $data[0]['user_group_uuid']) {
                // 编辑 并且只存在一个的情况下 并且是相同
                return true;
            }
            // 其它直接抛出
            $this->muOpResult(false, xphp_get_lang($opName), xphp_get_lang($msg), 'warning');
        }

        return true;
    }

    /**
     * 添加用户组和租户关联
     * @param array  $userGroupList 用户组唯一标识列表
     * @param string $tenantuuid    租户唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pAddUserGroupTenant(array $userGroupList, string $tenantuuid): bool
    {
        if (empty($userGroupList)) {
            return false;
        }
        $sql = "insert into mt_user_group_tenant (user_group_uuid, tenant_uuid) values ";
        foreach ($userGroupList as $key => $userGroupUUID) {
            $sql .= "('" . $userGroupUUID . "','" . $tenantuuid . "')";
            if ($key != (count($userGroupList) - 1)) {
                $sql .= ',';
            }
        }
        return $this->dbExec($sql);
    }

    /**
     * 添加用户组和角色关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array  $roleList      角色唯一标识列表
     * @return boolean
     * @author luokai@vinchin.com
     */
    public function pAddUserGroupRole(string $usergroupuuid, array $roleList): bool
    {
        if (empty($roleList)) {
            return false;
        }
        $sql = "insert into mt_user_group_role (user_group_uuid, role_uuid) values ";
        foreach ($roleList as $key => $l) {
            $sql .= "('" . $usergroupuuid . "','" . $l . "')";
            if ($key != (count($roleList) - 1)) {
                $sql .= ',';
            }
        }

        return $this->dbExec($sql);
    }

    /**
     * 添加用户组和用户关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array  $userList      用户唯一标识列表
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    public function pAddUserGroupUser(string $usergroupuuid, array $userList): bool
    {
        if (empty($userList)) {
            return false;
        }
        $sql = "insert into mt_user_user_group(user_group_uuid,user_uuid) values ";
        foreach ($userList as $key => $l) {
            $sql .= "('" . $usergroupuuid . "','" . $l . "')";
            if ($key != (count($userList) - 1)) {
                $sql .= ',';
            }
        }

        return $this->dbExec($sql);
    }

    /**
     * 取消用户组和用户关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param string $editFlag      flag
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    private function pDeleteUserGroupUser(string $usergroupuuid, $editFlag = false): bool
    {
        $sql = "delete from mt_user_user_group where user_group_uuid = ? ";
        $sqlParams = array($usergroupuuid);
        if ($editFlag) {
            $sql .= ' and user_uuid != ?';
            $sqlParams = array_merge($sqlParams, array(xphp_get_user_info()['userUuid']));
        }
        return $this->dbExec($sql, $sqlParams);
    }

    /**
     * 取消用户组和角色关联
     * @param string $usergroupuuid 用户组唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    private function pDeleteUserGroupRole(string $usergroupuuid): bool
    {
        $sql = "delete from mt_user_group_role where user_group_uuid = ?";
        return $this->dbExec($sql, array($usergroupuuid));
    }

    /**
     * 检查是否有删除默认组操作
     * @param string $userGroupdes des
     * @return boolean
     */
    private function checkIfDefault(string $userGroupdes)
    {
        $sql = "select user_group_uuid from bd_user_group
where user_group_type = ? and user_group_uuid in ('" . $userGroupdes . "') ";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'FLAG')['SET']));
        if (!empty($data)) {
            $this->muOpResult(
                false,
                xphp_get_lang('UI_USER_GROUP_DELETE'),
                xphp_get_lang('WEB_USER_GROUP_DELETE_TIPS'),
                'warning'
            );
        }

        return true;
    }

    /**
     * 取消用户组和所有租户关联
     * @param string $usergroupuuid 用户组唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    private function pDeleteUserGroupTenant(string $usergroupuuid): bool
    {
        $sql = "delete from mt_user_group_tenant where user_group_uuid = ?";
        return $this->dbExec($sql, array($usergroupuuid));
    }

    /**
     * 取消用户组与所有资源关联
     * @param string $usergroupuuid 用户组唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    private function pDeleteUserGroupALLResource(string $usergroupuuid): bool
    {
        $sql = "delete from mt_user_group_resource where user_group_uuid = ?";
        return $this->dbExec($sql, array($usergroupuuid));
    }

    /**
     * 取消用户组与所有资源组关联
     * @param string $usergroupuuid 用户组唯一标识
     * @return boolean 执行结果 成功|失败
     * @author luokai@vinchin.com
     */
    private function pDeleteUserGroupAllResourceGroup(string $usergroupuuid): bool
    {
        $sql = "delete from mt_user_group_resource_group where user_group_uuid = ?";
        return $this->dbExec($sql, array($usergroupuuid));
    }

    /**
     * 获取修改用户的租户等信息
     * @param string $useruuid uuid
     * @return array
     */
    private function getUserTenantInfo(string $useruuid): array
    {
        //获取租户uuid
        $sqlTenant = "select mut.tenant_uuid from bd_user bu, mt_user_tenant mut
where bu.user_uuid = mut.user_uuid and bu.user_uuid = ? ";
        $dataTenant = $this->dbSelect($sqlTenant, array($useruuid));

        $tenantuuid = !empty($dataTenant) ? $dataTenant[0]['tenant_uuid'] : '';

        //获取用户组列表
        $sqlUsergroup = "select muug.user_group_uuid from bd_user bu, mt_user_user_group muug
where bu.user_uuid = muug.user_uuid and bu.user_uuid = ?";
        $dataUsergroup = $this->dbSelect($sqlUsergroup, array($useruuid));
        $usergroupList = array();
        if (!empty($dataUsergroup)) {
            foreach ($dataUsergroup as $usergroup) {
                $usergroupList[] = $usergroup['user_group_uuid'];
            }
        }

        //获取角色列表
        $sqlRole = "select mur.role_uuid from bd_user bu, mt_user_role mur
where bu.user_uuid = mur.user_uuid and bu.user_uuid = ? ";
        $dataRole = $this->dbSelect($sqlRole, array($useruuid));

        $roleList = !empty($dataRole) ? array_column($dataRole, 'role_uuid') : [];

        return array(
            'tenant_uuid' => $tenantuuid,
            'usergroup_list' => $usergroupList,
            'role_list' => $roleList
        );
    }

    /**
     * 获取用户组关联用户
     */
    public function getUserGroupUser($params = [])
    {
        $usergroupuuid = $params['usergroup_uuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $id = $start + 1;
        $userList = $this->pGetUserGroupAllUser($usergroupuuid);
        $count = count($userList);
        $records = array();
        $userList = v1_array_sort($userList, 'user_name', 'asc', $start, $length);
        foreach ($userList as $d) {
            $username = $d['user_name'];
            if (empty($_SESSION['tenantuuid']) && !empty($d['tenant_name'])) {
                $username .= '(' . $d['tenant_name'] . ')';
            }
            $records['rows'][] = array(
                'id' => $id,
                'username' => $username,
            );
            $id++;
        }

        $records['total'] = $count;
        return  $records;
    }

    /**
     * 加载关联角色
     */
    public function getUserGroupRole($params = [])
    {
        $usergroupuuid = $params['usergroup_uuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $roleList = $this->pGetUserGroupAllRole($usergroupuuid);
        $records = array();
        $records['rows'] = array();
        $id = $start + 1;
        $count = count($roleList);
        $roleList = v1_array_sort($roleList, 'role_name', 'asc', $start, $length);
        foreach ($roleList as $d) {
            $records['rows'][] = array(
                'id' => $id,
                'rolename' => $d['role_name'],
            );
            $id++;
        }

        $records['total'] = $count;
        return $records;
    }

    /**
     * 加载关联资源组
     */
    public function initResourcegroup($params = [])
    {
        $usergroupuuid = $params['usergroup_uuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $resourceGroupList = $this->pGetUserGroupAllResourceGroup($usergroupuuid);
        $records = array();
        $records['rows'] = array();
        $id = $start + 1;
        $count = count($resourceGroupList);
        $resourceGroupList = v1_array_sort($resourceGroupList, 'resouce_group_name', 'asc', $start, $length);
        foreach ($resourceGroupList as $d) {
            $records['rows'][] = array(
                'id' => $id,
                'resource_group_name' => $d['resource_group_name'],
            );
            $id++;
        }

        $records['total'] = $count;
        return $records;
    }

    /**
     * 获取用户组权限树
     * @param array $params 参数
     * @return string
     */
    public function getUserGroupPermissionTree($params)
    {
        $usergroupuuid = $params['usergroupuuid'];
        $oldpath = xphp_get_oldpath();
        $page = getConfig($oldpath . 'api/xphp/conf/page.php');
        $userPermission = (new User())->pGetUserGroupAllPermission($usergroupuuid);

        // 更改 获取更深层次的操作
        $userPermission = (new User())->gGetPageLatest($userPermission, $usergroupuuid, 2);

        $userTree = $this->getUserAllTreeNodes($page, 0, $userPermission, true);

        $userInfo = $userTree;

        return $userInfo;
    }

    /**
     * 得到完整的用户权限树(递归)
     * @param array   $page           所有配置的页面
     * @param int     $pid            父节点id
     * @param array   $userPermission 用户权限
     * @param boolean $userManger     是否是用户管理页面
     * @return array
     */
    public function getUserAllTreeNodes($page, $pid, $userPermission, $userManger = false)
    {
        $tree = array();
        $list = array('p_homepage');
        foreach ($page as $p) {
            if (!in_array($p['name'], $userPermission)) {
                if ($userManger) {
                    //展示页面
                    continue;
                } elseif (!$userManger && $p['level'] != 10) {
                    //角色添加权限页面
                    continue;
                }
            }

            // 这里对具体的操作方法校验  不和授权文件交集的后台授权的所有的名称
            if (!in_array($p['name'], $_SESSION['permissionArr']) && $p['level'] == 10) {
                continue;
            }
            $name = '<i class="' . $p['class'] . '"></i> ' . xphp_get_lang($p['title']);
            if (!empty($p['desc'])) {
                $name .=  ' (' . xphp_get_lang($p['desc']) . ')';
            }
            $node = array(
                'id' => $p['name'],
                'pid' => $pid,
                'name' => $name,
                'title' => xphp_get_lang($p['title']),
                'open' => true,
                'nocheck' => false,
                'type' => $p['level'],
                'is_operate' => !empty($p['is_operate'])
            );
            //如果是Home节点
            if ($p['name'] == 'homepage' && !$userManger) {
                $node['checked'] = true;
                $node['chkDisabled'] = true;
            }
            //默认勾选上当前任务和历史任务还有首页涉及到的一些接口
            if (in_array($p['name'], $list)) {
                $node['checked'] = true;
            }
            if (!empty($p['child'])) {
                //如果有子目录
                $node['children'] = $this->getUserAllTreeNodes($p['child'], $p['name'], $userPermission, $userManger);
            }
            if ($userManger) {
                $node['nocheck'] = true;
                $node['chkDisabled'] = false;
            }

            //不显示异地备份系统和云存储
            if ($p['name'] == 'remote_system' || $p['name'] == 'cloud_storage') {
                continue;
            }
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * 获取单个用户组所有信息
     * @param array $params 参数
     * @return array
     */
    public function getOneUserGroupData($param)
    {
        $uuid = $param['usergroupuuid'];
        $sql = "select user_group_name,description from bd_user_group where user_group_uuid =?";
        //查询用户组名称以及描述
        $result = $this->dbSelect($sql, array($uuid));
        $sql1 = "select tenant_uuid from mt_user_group_tenant where user_group_uuid =?";
        //查询用户组对应的租户id
        $result1 = $this->dbSelect($sql1, array($uuid));
        $sql2 = "select role_uuid from mt_user_group_role where user_group_uuid =?";
        //查询用户组对应的角色id集合
        $result2 = $this->dbSelect($sql2, array($uuid));
        $sql3 = "select user_uuid from mt_user_user_group where user_group_uuid =?";
        //查询用户组对应的用户id集合
        $result3 = $this->dbSelect($sql3, array($uuid));
        //判断tenant__uuid是否为空，如为空值则赋值为空
        $info = array();
        $roleId = [];
        foreach ($result2 as $d) {
            $inforole[] = array($d['role_uuid']);
            $roleId[] = $d['role_uuid'];
        };
        foreach ($result3 as $d) {
            $infouser[] = array($d['user_uuid']);
        }
        // 需要判断下用户组是否全局观察者
        $isSuperRole = false;
        if (!empty($roleId)) {
            $roleStr = implode("','", $roleId);
            $role = $this->dbSelect("select config from bd_role where role_uuid in ('{$roleStr}')");
            if (in_array(2, array_column($role, 'config'))) {
                $isSuperRole = true;
            }
        }
        $info['user_group_name'] = $result[0]['user_group_name'];
        $info['description'] = $result[0]['description'];
        $info['tenant_uuid'] = $result1[0]['tenant_uuid'];
        $info['role'] = $inforole ?? [];
        $info['user'] = $infouser ?? [];
        $info['is_super_role'] = $isSuperRole;
        return $info;
    }

    /**
     * 公共方法
     * 获取用户组所有用户
     * @param string $usergroupuuid 用户uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_user表所有字段
     */
    public function pGetUserGroupAllUser($usergroupuuid)
    {
        $this->paramsCheck($usergroupuuid);
        $sql = "select 
                    distinct bu.user_uuid, bu.user_name, bu.email, bu.telephone, bu.lock_flag, 
                bu.create_time, bu.language, bu.user_type, bt.tenant_name
                from 
                    bd_user_group bug, mt_user_user_group muug, bd_user bu 
                left join 
                    mt_user_tenant mut on mut.user_uuid = bu.user_uuid 
                left join 
                        bd_tenant bt on bt.tenant_uuid = mut.tenant_uuid 
                where 
                    bu.user_uuid = muug.user_uuid 
                and 
                    muug.user_group_uuid = bug.user_group_uuid 
                and 
                    bug.user_group_uuid = ? 
                and 
                    bu.user_uuid <> ? ";
        $data = $this->dbSelect($sql, array($usergroupuuid, xphp_get_user_info()['userUuid']));
        return $data;
    }

    /**
     * 公共方法
     * 获取用户组关联角色
     * @param string $usergroupuuid 用户uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_role表所有字段
     */
    public function pGetUserGroupAllRole($usergroupuuid)
    {
        $this->paramsCheck($usergroupuuid);
        $sql = "select distinct br.role_uuid, br.role_name, br.lock_flag, br.permission_uuid, br.config
                from bd_user_group bug, bd_role br, mt_user_group_role mugr
                where bug.user_group_uuid = mugr.user_group_uuid and mugr.role_uuid = br.role_uuid and bug.user_group_uuid = ?";
        $data = $this->dbSelect($sql, array($usergroupuuid));
        return $data;
    }

    /**
     * 公共方法
     * 获取用户组关联资源组
     * @param string $useruuid 用户uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_resource_group表所有字段
     */
    public function pGetUserGroupAllResourceGroup($usergroupuuid)
    {
        $this->paramsCheck($usergroupuuid);
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.description, brg.config
                from bd_user_group bug, bd_resource_group brg, mt_user_group_resource_group mugrg
                where bug.user_group_uuid = mugrg.user_group_uuid and mugrg.resource_group_uuid = brg.resource_group_uuid and bug.user_group_uuid = ?";
        $data = $this->dbSelect($sql, array($usergroupuuid));
        return $data;
    }
}
