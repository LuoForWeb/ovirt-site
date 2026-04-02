<?php
/******************************************* 
** 用户组处理类
** 
** @author       luokai@vinchin.com 
** @date         2022-04-14 
** @version      1.0.0 
** @copyright    Copyright 2022 vinchin.com 
********************************************/
class APIUserGroupsHandler extends OPHandler{
    
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/usergroups" => array(
            'POST' => 'createUserGroup',     //新建用户组(单)
            'PUT' => 'editUserGroup',        //修改用户组(单)
            'DELETE' => 'deleteUserGroups',  //删除用户组(1-n)
            'GET' => 'getUserGroupInfo'         //获取单个用户组详细信息(单)
        ),
        
        "/usergroups/lock" => array(
            'POST' => 'lockUserGroup'    //禁用用户组(1-n)
        ),
        "/usergroups/unlock" => array(
            'POST' => 'unlockUserGroup'    //启用用户组(1-n)
        ),
        "/usergroups/lists" => array(
            'GET' => 'getUserGroupLists'    //获取用户组列表(1-n)
        )
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 创建用户组路由控制
     */
    protected function createUserGroup(){
        //定义方法版本
        $version = array(
            "v1" => "createUserGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改用户组路由控制
     */
    protected function editUserGroup(){
        //定义方法版本
        $version = array(
            "v1" => "editUserGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除用户组路由控制
     */
    protected function deleteUserGroups(){
        //定义方法版本
        $version = array(
            "v1" => "deleteUserGroupsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取用户组信息(单个)路由控制
     */
    protected function getUserGroupInfo(){
        //定义方法版本
        $version = array(
            "v1" => "getUserGroupInfoV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 禁用用户组路由控制
     */
    protected function lockUserGroup(){
        //定义方法版本
        $version = array(
            "v1" => "lockUserGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启用用户组路由控制
     */
    protected function unlockUserGroup(){
        //定义方法版本
        $version = array(
            "v1" => "unlockUserGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取用户组列表(多个)路由控制
     */
    protected function getUserGroupLists(){
        //定义方法版本
        $version = array(
            "v1" => "getUserGroupListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**********createUserGroup**********/
    private function createUserGroupV1(){
//         //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_add");
        $userGroupName = $this->params['userGroupName'];
        $userGroupDescription = $this->params['userGroupDescription'];
        $userGroupTenantUUID = "";
        //如果是租户内部创建
        $tenantuuid = $this->params['tenantuuid'];
        //如果是租户内部创建
        if(empty($tenantuuid)){
            $userGroupTenantUUID = Xphp::$_user['tenantuuid'];
        }
        $userGroupRoleUUIDlist = $this->params['userGroupRoleUUID'];
        $userGroupUserUUIDlist = $this->params['userGroupUserUUID'];
        
        $this->apiParamsCheck($userGroupName);
        
        //生成userGroup的uuid
        $utils = Xphp::instance("Utils");
        $userGroupUUID = $utils->uuid();
        //1为默认组不可删除，2为全局组,初始化创建，3为租户组，这里创建默认为2
        $userGroupType = Xphp::$_config['USER_GROUP_TYPE']['GLOBAL'];
        if(!empty($userGroupTenantUUID)){
            $userGroupType = Xphp::$_config['USER_GROUP_TYPE']['TENANT'];
        }
        //默认上锁为启用状态1（2为禁用）
        $lockFlag = Xphp::$_config['FLAG']['SET'];
        //其他配置
        $config = '';
        
        $createTime = date('Y-m-d H:i:s');
        $params_user_group =array($userGroupUUID,$userGroupName,$userGroupType,$lockFlag,$userGroupDescription,$config, Xphp::$_user['useruuid'], Xphp::$_user['username'], $createTime);
        $sql_user_group ="insert into bd_user_group(user_group_uuid,user_group_name,user_group_type,lock_flag,
                description,config,create_user_uuid,create_user_name,create_time) values(?,?,?,?,?,?,?,?,?)";
        $result_user_group = $this->dbExec($sql_user_group,$params_user_group);
        if($result_user_group){
            //插入数据到关联表
            $this->pAddUserGroupTenant(array($userGroupUUID),$userGroupTenantUUID);
            $this->pAddUserGroupRole($userGroupUUID,$userGroupRoleUUIDlist);
            $this->pAddUserGroupUser($userGroupUUID,$userGroupUserUUIDlist);
            
            //添加系统操作日志
//             $this->systemLog('SYSTEM_USER_GROUP_ADD_SUCCESS', array($userGroupName));
//             return $this->muOpResult(true, Xphp::$_lang['UI_USER_GROUP_ADD']);
            return $this->apiResponse(true, 'API_CODE_USER_GROUP_ADD');
            
        }else{
//             return $this->muOpResult(false, Xphp::$_lang['UI_USER_GROUP_ADD'], '', 'warning');
            return $this->apiResponse(false, 'API_CODE_USER_GROUP_ADD');
        }; 
    }
    
    /**********editUserGroup**********/
    private function editUserGroupV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_edit");
        $userGroupName = $this->params['userGroupName'];
        $userGroupDescription = $this->params['userGroupDescription'];
        $userGroupTenantUUID = "";
        //如果是租户内部创建
        if(!empty(Xphp::$_user['tenantuuid'])){
            $userGroupTenantUUID = Xphp::$_user['tenantuuid'];
        }
        $userGroupRoleUUIDlist = $this->params['userGroupRoleUUID'];
        $userGroupUserUUIDlist = $this->params['userGroupUserUUID'];
        $userGroupUUID = $this->params['userGroupUUID'];
        
        $this->apiParamsCheck($userGroupUUID, $userGroupName);
        
        //修改前先删除对应关联关系  用户|角色
        $this->pDeleteUserGroupUser($userGroupUUID ,true);
        $this->pDeleteUserGroupRole($userGroupUUID);
        
        $sql_delete_role = "delete from mt_user_group_role where user_group_uuid = ?";
        $this->dbExec($sql_delete_role,array($userGroupUUID));
        $sql_delete_user = "delete from mt_user_user_group where user_group_uuid = ?";
        $this->dbExec($sql_delete_user,array($userGroupUUID));
        
        $editTime = date('Y-m-d H:i:s');
        $params_user_group =array($userGroupName,$userGroupDescription, $editTime, $userGroupUUID);
        $sql ="update bd_user_group set user_group_name = ?,description = ?,create_time = ? where user_group_uuid = ?";
        $result = $this->dbExec($sql, $params_user_group);
        if($result){
            //插入数据到关联表
            // 	    $this->addUsergroupTenant($userGroupUUID,$userGroupTenantUUID);
            $this->pAddUserGroupRole($userGroupUUID,$userGroupRoleUUIDlist);
            $this->pAddUserGroupUser($userGroupUUID, $userGroupUserUUIDlist);
            return $this->apiResponse(true, 'API_CODE_USER_GROUP_EDIT');
        }else{
            return $this->apiResponse(false, 'API_CODE_USER_GROUP_EDIT');
        }
    }
    
    /**********deleteUserGroups**********/
    private function deleteUserGroupsV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_delete");
        $data_list = $this->params['data_list'];
        $this->apiParamsCheck($data_list);
        
        $usergroupDes = implode("','",$data_list);
        //检查是否有默认组，默认组不让删除
        // 	    $this->checkIfDefault($usergroupDes);
        foreach ($data_list as $uuid){
            //删除用户组与用户|角色|租户|资源|资源组关联
            $this->pDeleteUserGroupUser($uuid);
            $this->pDeleteUserGroupRole($uuid);
            $this->pDeleteUserGroupTenant($uuid);
            $this->pDeleteUserGroupALLResource($uuid);
            $this->pDeleteUserGroupAllResourceGroup($uuid);
        }
        $sql = "delete from bd_user_group where user_group_uuid in ('".$usergroupDes."')";
        $result = $this->dbExec($sql);
        
        if($result){
            return $this->apiResponse(true, 'API_CODE_USER_GROUP_DELETE');
        }else{
            return $this->apiResponse(false, 'API_CODE_USER_GROUP_DELETE');
        }
    }
    
    /**********getUserGroupInfo**********/
    private function getUserGroupInfoV1(){
        $userGroupUUID = $this->params['usergroup_uuid'];
        $this->apiParamsCheck($userGroupUUID);
        $sql = "select user_group_uuid, user_group_name, lock_flag, create_user_name, description, create_time from bd_user_group where user_group_uuid = ?";
        $data = $this->dbSelect($sql, array($userGroupUUID));
        $info = array();
        if($data){
            $info = array(
                "usergroup_uuid" => $data[0]['user_group_uuid'],
                "usergroup_name" => $data[0]['user_group_name'],
                "description" => $data[0]['description'],
                "create_time" => $data[0]['create_time'],
                "create_user_name" => $data[0]['create_user_name'],
                "lock_flag" => $data[0]['lock_flag']

            );
        }
        return $this->apiResponse(true, "API_CODE_USER_GROUP_GET_INFO", $info);
    }
    
    /**********lockUserGroup**********/
    private function lockUserGroupV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_disable");
        $data_list = $this->params['data_list'];
        $string = implode("','",$data_list);
        //lock_flag为2为禁用
        $sql = "update bd_user_group set lock_flag = 2 where user_group_uuid in";
        $sql .=" ("."'"."$string"."'".")";
        $result = $this->dbExec($sql);
        if($result){
            return $this->apiResponse(true, 'API_CODE_USER_GROUP_LOCK');
        }else{
            return $this->apiResponse(false, 'API_CODE_USER_GROUP_LOCK');
        }
    }
    
    /**********unlockUserGroup**********/
    private function unlockUserGroupV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_enable");
        $data_list = $this->params['data_list'];
        $this->apiParamsCheck($data_list);
        
        $string = implode("','",$data_list);
        //lock_flag为2为禁用
        $sql = "update bd_user_group set lock_flag = 1 where user_group_uuid in";
        $sql .=" ("."'"."$string"."'".")";
        $result = $this->dbExec($sql);
        if($result){
            return $this->apiResponse(true, 'API_CODE_USER_GROUP_UNLOCK');
        }else{
            return $this->apiResponse(false, 'API_CODE_USER_GROUP_UNLOCK');
        }
    }
    
    /**********getUserGroupLists**********/
    private function getUserGroupListsV1(){
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $this->apiParamsCheck($count);
        
        $sql = "select user_group_uuid, user_group_name, lock_flag, create_user_name, description, create_time from bd_user_group limit ? , ?";
        $sqlCount = "select count(id) as total from bd_user_group";
        
        $sqlParams = array($begin, $count);
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, array());
        $records = array();
        foreach ($data as $d){
            $records[] = array(
                "usergroup_uuid" => $d['user_group_uuid'],
                "usergroup_name" => $d['user_group_name'],
                "description" => $d['description'],
                "create_time" => $d['create_time'],
                "create_user_name" => $d['create_user_name'],
                "lock_flag" => $d['lock_flag']
            );
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_USER_GROUP_GET_LIST", $data);
    }
    
    
    
    /**********************************其他工具方法************************************/
    
    /**
     * 添加用户组和租户关联
     * @param array $userGroupList 用户组唯一标识列表
     * @param string $tenantuuid 租户唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserGroupTenant($userGroupList, $tenantuuid){
        if(empty($userGroupList)) return false;
        $sql = "insert into mt_user_group_tenant (user_group_uuid, tenant_uuid) values ";
        foreach ($userGroupList as $key=>$userGroupUUID){
            $sql .="('".$userGroupUUID."','".$tenantuuid."')";
            if($key != (count($userGroupList) - 1)){
                $sql .= ",";
            }
        }
        $result = $this->dbExec($sql);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 添加用户组和角色关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array $roleList 角色唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pAddUserGroupRole($usergroupuuid, $roleList){
        if(empty($roleList)) return false;
        $sql = "insert into mt_user_group_role (user_group_uuid, role_uuid) values ";
        foreach ($roleList as $key=>$l){
            $sql .="('".$usergroupuuid."','".$l."')";
            if($key != (count($roleList) - 1)){
                $sql.=",";
            }
        }
        
        $result = $this->dbExec($sql);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 添加用户组和用户关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array $userList 用户唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserGroupUser($usergroupuuid, $userList){
        if(empty($userList)) return false;
        $sql = "insert into mt_user_user_group(user_group_uuid,user_uuid) values ";
        foreach ($userList as $key=>$l){
            $sql .="('".$usergroupuuid."','".$l."')";
            if($key != (count($userList) - 1)){
                $sql .=",";
            }
        }
        
        $result = $this->dbExec($sql);
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 取消用户组和用户关联
     * @param string $usergroupuuid 用户组唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserGroupUser($usergroupuuid, $editFlag = false){
        $sql = "delete from mt_user_user_group where user_group_uuid = ? ";
        $sqlParams = array($usergroupuuid);
        if($editFlag){
            $sql .= " and user_uuid != ?";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
        }
        $result = $this->dbExec($sql, $sqlParams);
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 取消用户组和角色关联
     * @param string $usergroupuuid 用户组唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserGroupRole($usergroupuuid){
        $sql = "delete from mt_user_group_role where user_group_uuid = ?";
        $result = $this->dbExec($sql, array($usergroupuuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 取消用户组和所有租户关联
     * @param string $usergroupuuid 用户组唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserGroupTenant($usergroupuuid){
        $sql = "delete from mt_user_group_tenant where user_group_uuid = ?";
        $result = $this->dbExec($sql, array($usergroupuuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 取消用户组与所有资源关联
     * @param string $usergroupuuid 用户组唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserGroupALLResource($usergroupuuid){
        $sql = "delete from mt_user_group_resource where user_group_uuid = ?";
        $result = $this->dbExec($sql, array($usergroupuuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 取消用户组与所有资源组关联
     * @param string $usergroupuuid 用户组唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserGroupAllResourceGroup($usergroupuuid){
        $sql = "delete from mt_user_group_resource_group where user_group_uuid = ?";
        $result = $this->dbExec($sql, array($usergroupuuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
   
}