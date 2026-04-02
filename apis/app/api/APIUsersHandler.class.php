<?php
/*******************************************
 ** 用户处理类
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2017-12-14
 ** @version      1.0.0
 ** @copyright    Copyright 2018 vinchin.com
 *
 *edit by
 ** @author       luokai@vinchin.com
 ** @date         2022-04-14
 ** @version      1.0.0
 ** @copyright    Copyright 2022 vinchin.com
 ********************************************/
class APIUsersHandler extends OPHandler{
    
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/users" => array(
            'POST' => 'createUser',     //新建用户(单)
            'PUT' => 'editUser',        //修改用户(单)
            'DELETE' => 'deleteUsers',  //删除用户(1-n)
            'GET' => 'getUserInfo'         //获取单个用户详细信息(单)
        ),
        
        "/users/lock" => array(
            'POST' => 'lockUser'    //禁用用户(1-n)
        ),
        "/users/unlock" => array(
            'POST' => 'unlockUser'    //启用用户(1-n)
        ),
        "/users/lists" => array(
            'GET' => 'getUsersLists'    //获取用户列表(1-n)
        )
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 创建用户路由控制
     */
    protected function createUser(){
        //定义方法版本
        $version = array(
            "v1" => "createUserV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改用户路由控制
     */
    protected function editUser(){
        //定义方法版本
        $version = array(
            "v1" => "editUserV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除用户路由控制
     */
    protected function deleteUsers(){
        //定义方法版本
        $version = array(
            "v1" => "deleteUsersV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取用户信息(单个)路由控制
     */
    protected function getUserInfo(){
        //定义方法版本
        $version = array(
            "v1" => "getUserInfoV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 禁用用户路由控制
     */
    protected function lockUser(){
        //定义方法版本
        $version = array(
            "v1" => "lockUserV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启用用户路由控制
     */
    protected function unlockUser(){
        //定义方法版本
        $version = array(
            "v1" => "unlockUserV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取用户列表(多个)路由控制
     */
    protected function getUsersLists(){
        //定义方法版本
        $version = array(
            "v1" => "getUsersListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**********createUser**********/
    private function createUserV1(){
        
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_user_add");
        //         $this->checkManagerPermission();
        
        $username = $this->params['username'];
        $password = $this->params['password'];
        $email = $this->params['email'];
        $phone = $this->params['phone'];
        //角色列表
        $roleList = $this->params['roleList'];
        $userGroupList = $this->params['userGroupList'];
        
        $this->apiParamsCheck($username, $password);
        
        if(!$this->usernameAvailable($username)){
            //检查用户是否注册
            $data = array("username" => $username);
            return $this->apiResponse(false, "API_CODE_USERS_USER_NAME_EXISTS", $data, array());
        }
        
        $tenantuuid = $this->params['tenantuuid'];
        $createUseruuid = $this->params['create_user_uuid'];
        //通过用户唯一标识获取用户名
        $sql = "select user_name from bd_user where user_uuid = ? ";
        $data = $this->dbSelect($sql, array($createUseruuid));
        if(empty($data)){
            $createUserName = Xphp::$_user['username'];
            $createUserUUID = Xphp::$_user['useruuid'];
        }else{
            $createUserName = $data[0]['user_name'];
            $createUserUUID = $createUseruuid;
        }
        
        //如果是租户内部创建
        if(empty($tenantuuid)){
            $tenantuuid = Xphp::$_user['tenantuuid'];
        }
        //1本地用户 2外部用户
        $userType = 1;
        
        $permission = "";
        $createTime = date("Y-m-d H:i:s");
        $language = Xphp::$_config['lang'];
        //用户配额
        $quota = intval($this->params['quota']);
        //检查用户配额是否超出总配额
//         $this->checkUserMaxStorage($quota, Xphp::$_lang['WEB_USERS_ADD_USER']);
        
        //生成一个UUID
        $utils = Xphp::instance("Utils");
        $userUUID = $utils->uuid();
        //添加用户和用户组关联
        if(!empty($userGroupList)){
            $this->pAddUserUserGroup($userUUID, $userGroupList);
        }
        //添加用户和角色关联
        if(!empty($roleList)){
            $this->pAddUserRole($userUUID, $roleList);
        }
        //添加用户租户关联
        if(!empty($tenantuuid)){
            $this->pAddUserTenant(array($userUUID), $tenantuuid);
        }
        $sqlExtentionParams =array($userUUID,$quota);
        $sqlParam = array($userUUID, $username, $password, $email, $phone, $userType, $permission,
            $createTime, $createUserName, $createUserUUID, $language, 1);
        $sql = "insert bd_user (user_uuid, user_name, password, email, telephone, user_type, permission,
            create_time, create_user_name, create_user_uuid, language, user_level ) values ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlExtention = "insert bd_user_extension (user_uuid,quota) values (?, ?)";
        
        parent::dbBeginTransaction(); //事务
        $result = parent::dbQuery($sql, $sqlParam);
        $result = $result && parent::dbQuery($sqlExtention, $sqlExtentionParams);
        if($result){
            $this->systemLog('SYSTEM_USER_ADD_SUCCESS', array($username));
            parent::dbCommit();
        }else{
            parent::dbRollBack();
        }
        $data = array();
        if($result){
            $data['user_uuid'] = $userUUID;
        }
        return $this->apiResponse($result, "API_CODE_USERS_ADD_USER", $data, array());
        
    }
    
    /**********editUser**********/
    private function editUserV1(){
        
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_user_edit");
        //         $this->checkManagerPermission();
        $username = $this->params['username'];
        $userUUID = $this->params['useruuid'];
        $password = $this->params['password'];
        $email = $this->params['email'];
        $phone = $this->params['phone'];
        //角色列表
        $roleList = $this->params['roleList'];
        //用户组列表
        $userGroupList = $this->params['userGroupList'];
        $permission = "";
        $quota = $this->params['quota'];
        
        $this->apiParamsCheck($userUUID);
        //检查用户是否存在
        if(!$this->checkUserExist($userUUID)){
            return $this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_UUID_NOT_EXIST');
        }
        //检查用户配额是否超出总配额
//         $this->checkUserMaxStorage($quota, Xphp::$_lang['UI_PLATFORM_EDIT_USER']);
        //修改前先删除之前关联用户组|角色|租户
        $this->pDeleteUserUserGroup($userUUID);
        $this->pDeleteUserRole($userUUID);

        $editTime = date('Y-m-d H:i:s');
        $sqlParam = array($password, $email, $phone, $permission, $editTime, $userUUID);
        $sql = "update bd_user set password = ?, email = ?, telephone = ?, permission = ?, create_time = ? where user_uuid = ?";
        $sqlExtension = "update bd_user_extension set quota = ? where user_uuid = ?";
        $this->dbBeginTransaction(); //事务
        $result =  $this->dbExec($sql, $sqlParam);
        $result = $result && $this->dbExec($sqlExtension, array($quota, $userUUID));
        if($result){
            //添加用户和用户组关联
            $this->pAddUserUserGroup($userUUID, $userGroupList);
            //添加用户和角色关联
            $this->pAddUserRole($userUUID, $roleList);
            $this->dbCommit();
        }else{
            $this->dbRollBack();
        }
        $this->systemLog('SYSTEM_USER_MODIFY_SUCCESS', array($username));
        return $this->apiResponse($result, "API_CODE_USERS_EDIT_USER", array(), array());
    }
    
    /**********deleteUsers**********/
    private function deleteUsersV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_user_delete");
        //         $this->checkManagerPermission();

        $users = $this->params['users'];
        if(!is_array($users)) {
            return $this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_EXIST_PARAM');
        }
        $this->apiParamsCheck($users);
        //检查用户是否可以删除,如果用户有子用户(此用户创建了用户),此用户暂时无法删除
        if(!$this->deleteCheck($users)){
            return $this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_EXIST_USER');
        }
        $userIDs = $this->getDeleteUserIDs($users);
        $this->deleteUserCheck($userIDs);
        
        parent::dbBeginTransaction();
        $userName = '';
        $logHandler = Xphp::instance('LogHandler');
        $alarmHandler = Xphp::instance('AlarmHandler');
        foreach ($users as $user){
            //删除用户前删除用户与 用户组|角色|租户关联
            $this->pDeleteUserUserGroup($user);
            $this->pDeleteUserRole($user);
            $this->pDeleteUserTenant($user);
            //api暂时不处理,删除用户相关历史任务，任务日志和任务告警 
//             $this->pDeleteUserHistoryTask($user);
//             $this->pDeleteUserTaskLog($user,$logHandler);
//             $this->pDeleteUserSystemLog($user,$logHandler);
//             $this->pDeleteUserTaskAlarm($user,$alarmHandler);
            
            //删除用户与资源的关联
            $this->pDeleteUserAllResource($user);
            $this->pDeleteUserAllResourceGroup($user);
            
            //删除用于创建的 用户组 资源组 角色等
            $this->pDeleteUserRoleAndUserCreate($user);
            $this->pDeleteUserResourceGroupAndUserCreate($user);
            $this->pDeleteUserGroupAndUserCreate($user);
            
            
            $userName .= $this->getUsername($user) . ", ";
            $sql = "delete from bd_user where user_uuid = ?";
            if(!parent::dbQuery($sql, array($user))){
                parent::dbRollBack();
                $userName = '';
                return $this->apiResponse(false, 'API_CODE_USERS_DELETE_USER');
            }
        }
        parent::dbCommit();
        $userName = substr($userName, 0, -2);
        $this->systemLog('SYSTEM_USER_DELETE_SUCCESS', array($userName));
        
        return $this->apiResponse(true, 'API_CODE_USERS_DELETE_USER');
    }
    
    /**********getUserInfo**********/
    private function getUserInfoV1(){
        $userUUID = $this->params['user_uuid'];
        $this->apiParamsCheck($userUUID);
        $sql = "select user_name, password, email, telephone, user_type, unix_timestamp(create_time) create_time, create_user_name, unix_timestamp(last_login_time) last_login_time from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($userUUID));
        $sqlExtension = "select quota from bd_user_extension where user_uuid = ?";
        $dataExtension = $this->dbSelect($sqlExtension,array($userUUID));
        $info = array();
		$utils = Xphp::instance('Utils');        
        if($data){
            $info = array(
                "user_name" => $data[0]['user_name'],
                "email" => $data[0]['email'],
                "telephone" => $data[0]['telephone'],
                "quota" => intval($dataExtension[0]['quota']),
                "create_user_name" => $data[0]['create_user_name'],
                "create_time" => $data[0]['create_time'],
                "last_login_time" => $data[0]['last_login_time'],
            );
        }
        return $this->apiResponse(true, "API_CODE_USERS_GET_USER_INFO", $info);
    }
    
    /**********lockUser**********/
    private function lockUserV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_user_disable");
        $users = $this->params['users'];
        if(!is_array($users)) {
            return $this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_EXIST_PARAM');
        }
        $this->apiParamsCheck($users);
        //禁用用户强制停止任务
//         $this->lockUserStopJob($users);
        parent::dbBeginTransaction();
        foreach ($users as $user){
            $sql = "update bd_user set lock_flag = '2',login_error_count = '0' where user_uuid = ?";
            if(!parent::dbQuery($sql, array($user))){
                parent::dbRollBack();
                return $this->apiResponse(false, "API_CODE_USERS_LOCK_USER");
    }
        }
        parent::dbCommit();
//         $this->systemLog('SYSTEM_USER_LOCK_SUCCESS', array($userName));
        return $this->apiResponse(true, "API_CODE_USERS_LOCK_USER");
    }
    
    /**********unlockUser**********/
    private function unlockUserV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_user_enable");
        //         $this->checkManagerPermission();
        $users = $this->params['users'];
        if(!is_array($users)) {
            return $this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_EXIST_PARAM');
        }
        $this->apiParamsCheck($users);
        parent::dbBeginTransaction();
        foreach ($users as $user){
            $sql = "update bd_user set lock_flag = '1',login_error_count = '0' where user_uuid = ?";
            if(!parent::dbQuery($sql, array($user))){
                parent::dbRollBack();
                return $this->apiResponse(true, "API_CODE_USERS_UNLOCK_USER");
    }
        }
        parent::dbCommit();
        return $this->apiResponse(true, "API_CODE_USERS_UNLOCK_USER");
    }
    
    /**********getUsersLists**********/
    private function getUsersListsV1(){
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $this->apiParamsCheck($count);
        
        $sql = "select  user_uuid, user_name, user_type, unix_timestamp(create_time) create_time, create_user_name, email, telephone,
            unix_timestamp(last_login_time) last_login_time, lock_flag from bd_user limit ? , ?";
        $sqlCount = "select count(id) as total from bd_user limit ? , ?";
        
        $sqlParams = array($begin, $count);
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlParams);
        $utils = Xphp::instance("Utils");
        $records = array();
        foreach ($data as $d){
            $sqlExtension = "select quota from bd_user_extension where user_uuid = ?";
            $dataExtension = $this->dbSelect($sqlExtension,array($d['user_uuid']));
            $records[] = array(
                "user_uuid" => $d['user_uuid'],
                "user_name" => $d['user_name'],
                "create_time" => $d['create_time'],
                "create_user_name" => $d['create_user_name'],
                "email" => $d['email'],
                "telephone" => $d['telephone'],
                "last_login_time" => $d['last_login_time'],
                "lock_flag" => intval($d['lock_flag']),
                "quota" => intval($dataExtension[0]['quota']),
            );
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_USERS_GET_USER_LIST", $data);
    }
    
    
    
    
    
    /**********************************其他工具方法************************************/
    /**
     * 检查用户名是否存在
     * @param string $username
     */
    private function checkUserNameExist($username){
        $sql = "select task_uuid from bd_task where task_name = ?";
        $data = $this->dbSelect($sql, array(trim($username)));
        if(!empty($data)){
            //如果找到了相同任务名的任务,返回错误
            return $this->apiResponse(false, 'API_CODE_USERS_USER_NAME_EXISTS');
        }
    }
    
    /**
     * 得到新添加用户或修改用户的权限
     * @param string $usertype        用户类型
     * @param int  $permissionType  权限类型
     * @param array $permission            权限
     */
    private function getAddAndEditUserPermission($usertype, $permissionType, $permission){
        $permissionType = intval($permissionType);
        if($permissionType == Xphp::$_config['FLAG']['SET']){
            //如果是默认权限
            $permissionConf = require_once CONF_PATH . 'permission.php';
            $permission = $permissionConf[$usertype];
        }
        $permission = implode("|", array_unique($permission));
        return $permission;
    }
    
    /**
     * 检查是否有管理员权限(管理员和超级管理员)
     * @return bool 是true/否false
     */
    private function checkManagerPermission(){
        $userType = Xphp::$_config['USERTYPE'];
        if(Xphp::$_user['usertype'] == $userType['manager'] or
            Xphp::$_user['usertype'] == $userType['administrator']){
                return true;
        }
        return $this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_CHECK_PERMISSION');
    }
    
    /**
     * 得到是否锁定的描述    是/否
     * @param unknown $lockFlag
     * @return multitype:
     */
    private function getLockDes($lockFlag){
        $lockDes = Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'];
        if(1 == $lockFlag){
            $lockDes = Xphp::$_lang['WEB_PLATFORM_PUBLIC_NO'];
        }elseif(2 == $lockFlag){
            $lockDes = Xphp::$_lang['WEB_PLATFORM_PUBLIC_YES'];
        }
        return $lockDes;
    }
    
    /**
     * 得到删除用户的用户ID号
     * @param array $userUUIDs
     * @return array    $userIDs
     */
    private function getDeleteUserIDs($userUUIDs){
        $ids = array();
        foreach ($userUUIDs as $uuid){
            $ids[] = $this->getUserID($uuid);
        }
        return $ids;
    }
    
    /**
     * 根据用户UUID得到用户ID号
     * @param unknown $userUUID
     */
    private function getUserID($userUUID){
        $sql = "select id from bd_user where user_uuid = ? ";
        $sqlParams = array($userUUID);
        $data = $this->dbSelect($sql, $sqlParams);
        return $data[0]['id'];
    }
    /**
     * 删除用户检查对应ID
     * @param array $users
     */
    private function deleteUserCheck($users){
        $this->checkVcenter($users);
    }
    
    /**
     * 检查VCENTER
     * @param unknown $users
     */
    private function checkVcenter($users){
        $userStr = "";
        foreach ($users as $user){
            $userStr .= $user . " ,";
        }
        if($userStr){
            $userStr = substr($userStr, 0, -1);
        }
        $sql = "select vv.nickname, bu.user_name from vm_vcenter vv, bd_user bu
                where vv.user_uuid = bu.user_uuid and bu.id in ( ? )";
        $sqlParams = array($userStr);
        $data = $this->dbSelect($sql, $sqlParams);
        if($data){
            //如果虚拟化中心有虚拟机存在于任务中,直接返回
            $msg = Xphp::$_lang['WEB_USERS_USER'] . "'" . $data[0]['user_name'] . "'" .
                Xphp::$_lang['WEB_USERS_DELETE_USER_TIPS'] . "'" . $data[0]['nickname'] . "'";
                return $this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_EXIST_JOB');
        }
        return true;
    }
    
    /**
     * 检测用户是否存在
     * @param string $useruuid
     */
    private function checkUserExist($useruuid){
        $sql = "select user_name from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        if(!$data) return false;
        return true;
    }
    
    /**
     * 检测是否是admin
     * @param string $useruuid
     */
    private function checkAdminUser($useruuid){
        $sql = "select user_name from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        //检查是否是admin用户
        if($data[0]['user_name'] == "admin"){
            return $this->apiResponse(false, 'API_CODE_USERS_DELETE_ADMIN_ERROR');
        }
    }
    
    /**
     * 根据用户UUID得到用户名
     * @param string $useruuid
     */
    public function getUsername($useruuid){
        $sql = "select user_name from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        if($data){
            return $data[0]['user_name'];
        }
    }
    
    /**
     * 添加用户和用户组关联
     * @param string $useruuid 用户唯一标识
     * @param array $usergroupList 用户组唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserUserGroup($useruuid, $usergroupList){
        if(empty($usergroupList)) return true;
        $sql = "insert into mt_user_user_group(user_uuid,user_group_uuid) values ";
        foreach ($usergroupList as $key=>$l){
            if($key == (count($usergroupList) - 1)){
                $sql .="('".$useruuid."','".$l."')";
            }else{
                $sql .="('".$useruuid."','".$l."'),";
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
     * 添加用户和角色关联
     * @param string $useruuid 用户唯一标识
     * @param array $roleList 角色唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserRole($useruuid, $roleList){
        if(empty($roleList)) return true;
        $sql = "insert into mt_user_role(user_uuid, role_uuid) values ";
        foreach ($roleList as $key=>$l){
            if($key == (count($roleList) - 1)){
                $sql .="('".$useruuid."','".$l."')";
            }else{
                $sql .="('".$useruuid."','".$l."'),";
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
     * 添加用户和租户关联
     * @param array $userList 用户唯一标识集合
     * @param string $tenantuuid 租户唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserTenant($userList, $tenantuuid){
        if(empty($userList)) return false;
        $sql = "insert into mt_user_tenant (user_uuid, tenant_uuid) values ";
        foreach ($userList as $key=>$userUUID){
            $sql .="('".$userUUID."','".$tenantuuid."')";
            if($key != (count($userList) - 1)){
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
     * 取消用户和用户组关联
     * @param string $useruuid 用户唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserUserGroup($useruuid){
        $sql = "delete from mt_user_user_group where user_uuid = ?";
        $result = $this->dbExec($sql, array($useruuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    
    /**
     * 取消用户和角色关联
     * @param string $useruuid
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserRole($useruuid){
        $sql = "delete from mt_user_role where user_uuid = ?";
        $result = $this->dbExec($sql, array($useruuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 取消用户和所有租户关联
     * @param string $useruuid 用户唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserTenant($useruuid){
        $sql = "delete from mt_user_tenant where user_uuid = ? ";
        $result = $this->dbExec($sql, array($useruuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 删除用户的历史任务
     * @param unknown $user
     * @return void|boolean
     */
    public function pDeleteUserHistoryTask($user){
        $sql = "select distinct id from bd_history_task where user_uuid = ? ";
        $data = $this->dbSelect($sql,array($user));
        if(empty($data)) return;
        
        $taskIDArr = array();
        foreach ($data as $d){
            $taskIDArr[] = intval($d['id']);
        }
        $opName = 'BD_TASK_OP_HISTORY_TASK_DELETE';
        $msg = array('id_list' => $taskIDArr);
        
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        
        return true;
    }
    
    
    /**
     * 检查用户是否注册
     * @param string $params [username]
     * @return string
     */
    private function usernameAvailable($username){
        $sql = "select bu.id from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid where bu.user_name = ? ";
        $sqlParams = array($username);
        if(!empty(Xphp::$_user['tenantuuid'])){
            $tenantuuid = Xphp::$_user['tenantuuid'];
        }
        if(!empty($tenantuuid)){
            $sql .= " and mut.tenant_uuid = ? ";
            $sqlParams = array($username, $tenantuuid);
        }
        $users = $this->dbSelect($sql, $sqlParams);
        return empty($users);
    }
    
    /**
     * 根据tenant_uuid获取当前租户下所有的用户uuid列表
     * @param unknown $tenant_uuid
     * @author liushuai@vinchin.com
     * @return array 返回一个用户uuid数组
     */
    public function pGetUseruuidList($tenant_uuid){
        $sql = "select user_uuid from mt_user_tenant where tenant_uuid = ?";
        $result = $this->dbSelect($sql,array($tenant_uuid));
        $user_uuid_list = array();
        if($result){
            foreach ($result as $op){
                $user_uuid_list[] = $op['user_uuid'];
            };
        }
        return $user_uuid_list;
    }
    
    /**
     * 获取授权文件显示相应模块
     * @param array $permission
     * $param string $username
     */
    public function getAuthSoftwarePermission($permission, $useruuid){
        $extension = Xphp::instance("APISettingsHandler", "getExtensionLicense", array());
        $pagelist = array();
        if(!empty($extension)){
            $pagelist = $extension['p'];
        }
        //获取是否授权
        $sql ="select authorized_flag from bd_system";
        $data = $this->dbSelect($sql);
        $authflag = intval($data[0]['authorized_flag']);
        if($authflag == Xphp::$_config['FLAG']['UNSET']){ //如果备份系统没授权
            //未授权给出初始界面
            $pageList = array(
                "homepage",
                "monitor",
                "task","current_job","history_job",
                "alarm","task_alarm","system_alarm",
                "log","job_log","system_log",
                "report","vm_report","storage_report",
                "vmprotect",
                "vm_overview",
                "vmbackup",
                "vmrecover",
                "vminstantrecover",
                "vmrecovera",
                "vmdata",
                
                "resmanagement",
                "vcenter_manager",
                "appliance_manager",
                "node_manager",
                "storage_manager",
                "storage_lanfree",
                "global_strategy",
                "sysmanagement",
                "setting_manager",
                "system_network",
                "set_time",
                "set_ip",
                "system_dns",
                "nic_teaming",
                "users",
                "authorization_module");
            $permission = $pageList;
        }else{
            //如果备份系统授权
            if(!empty($pagelist)){
                //检查是否是admin超级管理员
                $sql = "select user_name from bd_user where user_uuid = ? and user_type = 3";
                $data = $this->dbSelect($sql, array($useruuid));
                //如果授权文件存在页面信息
                if(empty($data)){
                    $page = array();
                    foreach ($permission as $p){
                        if(in_array($p, $pagelist)){
                            $page[] = $p;
                        }
                    }
                    $permission = $page;
                }else{
                    $permission = $pagelist;
                }
            }
        }
        
        //根据功能授权,再剔除部分页面
        $permission = $this->fromPermissionFilterLicence($permission, $extension['f']);
        $permission = array_merge($permission,array("data_verification", "virtual_lab_manager", "add_data_verification"));
        if(Xphp::$_user['user_type'] == 3){
            //超级管理员不需要全局管理者的权限
            $permission = array_diff($permission, ["global_observer", "global_read", "global_write"]);
        }
        return $permission;
    }
    
    /**
     * 根据license的功能项,从permisssion里面筛掉部分页面权限
     * @param unknown $permission
     * @param unknown $entensionFunctions
     */
    private function fromPermissionFilterLicence($permission, $entensionFunctions){
        foreach ($entensionFunctions as $key=>$value){
            switch ($key){
                case 'nodeExtend':  //节点
                    if(!$value){
                        $deletePage = array("node_manager");
                        $permission = array_diff($permission, $deletePage);
                    }
                    break;
                case 'copy':        //副本
                    if(!$value){
                        $deletePage = array("datacopy", "vmcopy", "vmcopyback", "vmcopydata", "remote_system");
                        $permission = array_diff($permission, $deletePage);
                    }
                    break;
                case 'archive':     //归档
                    if(!$value){
                        $deletePage = array("data_archive", "archive_add", "archive_back", "archive_data", "cloud_storage");
                        $permission = array_diff($permission, $deletePage);
                    }
                    break;
                case 'backupStorageProtect':    //存储保护
                    if(!$value){
                        $deletePage = array("storage_safe");
                        $permission = array_diff($permission, $deletePage);
                    }
                    break;
            }
        }
        
        return $permission;
    }
    
    /**
     * 检查用户是否有创建用户,删除的时候检查用,如果有的话,直接返回false
     * @param unknown $user
     * @return boolean
     */
    private function deleteCheck($useruuids){
        foreach ($useruuids as $useruuid){
            $sql = "select user_uuid, user_name from bd_user where create_user_uuid = ?";
            $data = $this->dbSelect($sql, array($user));
            if($data){
                return false;
            }
        }
        return true;
    }
    
    
    /**
     * 检查任务
     * @param array $users
     * @return boolean
     */
    private function checkTask($users){
        $userStr = "";
        foreach ($users as $user){
            $userStr .= $user . " ,";
        }
        if($userStr){
            $userStr = substr($userStr, 0, -1);
        }
        $sql = "select bt.task_uuid from bd_task bt, bd_user bu
                where bt.user_uuid = bu.user_uuid and bu.id in ('".$userStr."')";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            exit($this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_EXIST_JOB'));
        }
        return true;
    }
    
    /**
     * 检查时间点
     * @param unknown $users
     * @return boolean
     */
    private function checkTimepoint($users){
        $userStr = "";
        foreach ($users as $user){
            $userStr .= $user . " ,";
        }
        if($userStr){
            $userStr = substr($userStr, 0, -1);
        }
        $sql = "select bbt.timepoint_uuid from bd_backup_timepoint bbt, bd_user bu
                where bbt.user_uuid = bu.user_uuid and bu.id in ('".$userStr."')";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            exit($this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_EXIST_TIMEPOINT'));
        }
        return true;
    }
    
    /**
     * 检查代理端
     * @param array $users
     */
    private function checkAgent($users){
        $userStr = "";
        foreach ($users as $user){
            $userStr .= $user . " ,";
        }
        if($userStr){
            $userStr = substr($userStr, 0, -1);
        }
        $sql = "select ba.hostname, ba.ip, ba.agent_type, bu.user_name from bd_agent ba, bd_user bu
                where ba.user_uuid = bu.user_uuid and bu.id in ('".$userStr."')";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            //             //如果有文件和数据库代理存在
            //             $des = Xphp::$_lang['UI_PLATFORM_FAGENT_DEL_FIRST'];
            //             //数据库代理
            //             if($data[0]['agent_type'] == 1){
            //                 $des = Xphp::$_lang['UI_PLATFORM_DBAGENT_DEL_FIRST'];
            //             }
            //             $msg = Xphp::$_lang['WEB_USERS_USER'] . "'" . $data[0]['user_name'] . "'" .
            //             $des . "'" . $data[0]['host_name'] . "'";
            exit($this->apiResponse(false, 'API_CODE_USERS_DELETE_USER_EXIST_AGENT'));
        }
        return true;
    }
    
    
    /**
     * 取消用户和所有资源关联
     * @param string $useruuid 用户唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserAllResource($useruuid){
        $sql = "delete from mt_user_resource where user_uuid = ?";
        $result = $this->dbExec($sql, array($useruuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 取消用户和所有资源组关联
     * @param string $useruuid 用户唯一标识
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserAllResourceGroup($useruuid){
        $sql = "delete from mt_user_resource_group where user_uuid = ?";
        $result = $this->dbExec($sql, array($useruuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 公共方法
     * 删除用户创建的角色
     * @param $useruuid  用户uuid
     * @author liushuai@vinchin.com
     * @return bool
     */
    public function pDeleteUserRoleAndUserCreate($useruuid){
        $sql = "delete from bd_role where create_user_uuid = ?";
        $result = $this->dbExec($sql, array($useruuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 公共方法
     * 删除用户创建的资源组
     * @param $useruuid  用户uuid
     * @author liushuai@vinchin.com
     * @return bool
     */
    public function pDeleteUserResourceGroupAndUserCreate($useruuid){
        $sql = "delete from bd_resource_group where create_user_uuid = ?";
        $result = $this->dbExec($sql, array($useruuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 公共方法
     * 删除用户创建的用户组
     * @param $useruuid  用户uuid
     * @author liushuai@vinchin.com
     * @return bool
     */
    public function pDeleteUserGroupAndUserCreate($useruuid){
        $sql = "delete from bd_user_group where create_user_uuid = ?";
        $result = $this->dbExec($sql, array($useruuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 公共方法
     * 检查用户是否属于Master用户组
     * @param string $useruuid
     * @author luokai@vinchin.com
     * @return boolean 返回检查结果 是|否
     */
    public function pCheckUserIsMaster($useruuid){
        $masterFlag = FALSE;
        //获取用户所在所有用户组
        if(!empty($useruuid)){
            $userGroupInfo = $this->pGetUserAllUserGroup($useruuid);
            $roleInfo = $this->pGetUserAllRole($useruuid);
            if(!empty($userGroupInfo)){
                foreach ($userGroupInfo as $info){
                    if($info['user_group_uuid'] == "e99ae858-d549-4754-9310-457477f4c2e3"){
                        $masterFlag = TRUE;
                    }
                }
            }
            if(!empty($roleInfo)){
                foreach ($roleInfo as $info){
                    if($info['role_uuid'] == "a30f7728-2ef7-bca0-2224-07deba8ce3e5"){
                        $masterFlag = TRUE;
                    }
                }
            }
        }
        
        return $masterFlag;
    }
    
    /**
     * 公共方法
     * 获取用户所在用户组信息
     * @param string $useruuid     用户uuid
     * @author xiezhuowei@vinchin.com
     * @return array    多维数组,对应bd_user_group表所有字段
     */
    public function pGetUserAllUserGroup($useruuid){
        $this->paramsCheck($useruuid);
        $sql = "select distinct bug.user_group_uuid, bug.user_group_name, bug.user_group_type, bug.lock_flag, bug.description, bug.config
                from bd_user bu, bd_user_group bug, mt_user_user_group muug
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = bug.user_group_uuid and bu.user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        return $data;
    }

    /**
     * 公共方法
     * 获取用户关联角色
     * @param string $useruuid     用户uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_role表所有字段
     */
    public function pGetUserAllRole($useruuid){
        $this->paramsCheck($useruuid);
        $sql = "select distinct br.role_uuid, br.role_name, br.lock_flag, br.permission_uuid, br.config
                from bd_user bu, bd_role br, mt_user_role mur
                where bu.user_uuid = mur.user_uuid and mur.role_uuid = br.role_uuid and bu.user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        return $data;
    }
    
}