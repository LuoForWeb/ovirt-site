<?php
/******************************************* 
** 角色处理类
** 
** @author       luokai@vinchin.com 
** @date         2022-04-14 
** @version      1.0.0 
** @copyright    Copyright 2022 vinchin.com 
********************************************/
class APIRolesHandler extends OPHandler{
    
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/roles" => array(
            'POST' => 'createRole',     //创建角色(单)
            'PUT' => 'editRole',        //修改角色(单)
            'DELETE' => 'deleteRoles',  //删除角色(1-n)
            'GET' => 'getRoleInfo'      //获取单个角色详细信息(单)
        ),
        "/roles/lock" => array(
            'POST' => 'lockRole'    //禁用角色(1-n)
        ),
        "/roles/unlock" => array(
            'POST' => 'unlockRole'    //启用角色(1-n)
        ),
        "/roles/allocate_user" => array(
            'POST' => 'allocateToUser'    //分配指定角色到选择用户集合(1对多)
        ),
        "/roles/allocate_usergroup" => array(
            'POST' => 'allocateToUserGroup'    //分配指定角色到选择用户组集合(1对多)
        ),
        "/roles/all_permission" => array(
            'GET' => 'getAllPermission'    //获取系统所有权限
        ),
        "/roles/lists" => array(
            'GET' => 'getRolesLists'    //获取角色列表(1-n)
        )
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 创建角色路由控制
     */
    protected function createRole(){
        //定义方法版本
        $version = array(
            "v1" => "createRoleV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改角色路由控制
     */
    protected function editRole(){
        //定义方法版本
        $version = array(
            "v1" => "editRoleV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除角色路由控制
     */
    protected function deleteRoles(){
        //定义方法版本
        $version = array(
            "v1" => "deleteRolesV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取角色信息(单个)路由控制
     */
    protected function getRoleInfo(){
        //定义方法版本
        $version = array(
            "v1" => "getRoleInfoV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 禁用路由控制
     */
    protected function lockRole(){
        //定义方法版本
        $version = array(
            "v1" => "lockRoleV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启用角色路由控制
     */
    protected function unlockRole(){
        //定义方法版本
        $version = array(
            "v1" => "unlockRoleV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 分配指定角色到选择用户路由控制
     */
    protected function allocateToUser(){
        //定义方法版本
        $version = array(
            "v1" => "allocateToUserV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 分配指定角色到选择用户组路由控制
     */
    protected function allocateToUserGroup(){
        //定义方法版本
        $version = array(
            "v1" => "allocateToUserGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取用户列表(多个)路由控制
     */
    protected function getRolesLists(){
        //定义方法版本
        $version = array(
            "v1" => "getRolesListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 获取系统所有权限集合路由控制
     */
    protected function getAllPermission(){
        //定义方法版本
        $version = array(
            "v1" => "getAllPermissionV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**********createRole**********/
    private function createRoleV1(){
//         //权限检查
//         $this->pOperationPermissionCheckExit("p_safety_role_add");
        $rolename = $this->params['rolename'];      //角色名字
        $permission = $this->params['permission'];  //权限
//         $opName = Xphp::$_lang['UI_ROLE_ADD'];
//         //检查名字是否重复
//         $this->checkRolenameExist($rolename, $opName);
        $this->apiParamsCheck($rolename, $permission);
        $permission = json_encode($this->params['permission']);   //page权限集合
        
        $tenantuuid = $this->params['tenantuuid'];
        //如果是租户内部创建
        if(empty($tenantuuid)){
            $tenantuuid = Xphp::$_user['tenantuuid'];
        }
        
        $utils = Xphp::instance("Utils");
        //生成角色uuid
        $roleUUID = $utils->uuid();
        //生成权限uuid
        $permissionUUID = $utils->uuid();
        //是否上锁，1为启用，2为禁用
        $lock_flag = Xphp::$_config['FLAG']['SET'];
        //其他配置
        $config = "";
        $createTime = date('Y-m-d H:i:s');
        //向角色表中插入数据
        $roleParams = array($roleUUID, $rolename, $lock_flag, $permissionUUID, $config, $tenantuuid, Xphp::$_user['useruuid'], Xphp::$_user['username'], $createTime);
        $roleSql = "insert into bd_role (role_uuid, role_name, lock_flag, permission_uuid, config, tenant_uuid, create_user_uuid, create_user_name, create_time) values
                    (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        //向权限表中插入数据
        //type为1为系统所有默认权限不可删除，2分配出去的权限
        $permission_type = Xphp::$_config['FLAG']['UNSET'];
        $permission_name = $this->params['rolename'];
        $permissionParams = array($permissionUUID,$permission_name,$permission_type, $permission);
        $permissionSql = "insert into bd_permission (permission_uuid, name, type, content) values (?, ?, ?, ?)";
        parent::dbBeginTransaction(); //事务开始
        
        $result = parent::dbQuery($roleSql, $roleParams);
        $result = $result && parent::dbQuery($permissionSql, $permissionParams);
        
        if($result){
            //事务结束
            //添加系统操作日志
//             $this->systemLog('SYSTEM_ROLE_ADD_SUCCESS', array($rolename));
            
            parent::dbCommit();
            return $this->apiResponse(true, "API_CODE_ROLE_ADD");
        }else{
            //事务回滚
            parent::dbRollBack();
            return $this->apiResponse(false, "API_CODE_ROLE_ADD");
        }
    }
    
    /**********editRole**********/
    private function editRoleV1(){
//         //权限检查
//         $this->pOperationPermissionCheckExit("p_safety_role_edit");
        $roleUUID = $this->params['roleuuid'];
        $permission = $this->params['permission'];
        $this->apiParamsCheck($roleUUID);
        $permissionUUID = $this->getPermissionUUIDWithRoleUUID($roleUUID);
        
        $permission = json_encode($this->params['permission']);
        $config = "";
        $tenantuuid = "";
        
        //如果是租户内部创建
        if(!empty(Xphp::$_user['tenantuuid'])){
            $tenantuuid = Xphp::$_user['tenantuuid'];
        }
        $editTime = date('Y-m-d H:i:s');
        //向角色表中插入数据
        $roleParams = array($config, $tenantuuid, $editTime, $roleUUID);
        $roleSql = "update bd_role set config = ?, tenant_uuid = ?, create_time = ? where role_uuid = ?";
        //向权限表中插入数据
        //type为1为系统所有默认权限不可删除，2分配出去的权限
        $permission_type = Xphp::$_config['FLAG']['UNSET'];
        $permissionParams = array($permission, $permissionUUID);
        $permissionSql = "update bd_permission set content = ? where permission_uuid = ?";
        parent::dbBeginTransaction(); //事务开始
        
        $result = parent::dbExec($roleSql, $roleParams);
        $result = $result && parent::dbExec($permissionSql, $permissionParams);
        
        if($result){
            //事务结束
            parent::dbCommit();
            
            return $this->apiResponse(true, "API_CODE_ROLE_EDIT");
        }else{
            //事务回滚
            parent::dbRollBack();
            
            return $this->apiResponse(false, "API_CODE_ROLE_EDIT");
        }
        
    }
    
    /**********deleteRoles**********/
    private function deleteRolesV1(){
        //权限检查
//         $this->pOperationPermissionCheckExit("p_safety_role_delete");
        $rolelist = $this->params['rolelist'];
        $this->apiParamsCheck($rolelist);
        
        $roleStr = implode("','",$rolelist);
        $sql = "delete  bd_role ,bd_permission from bd_role left join bd_permission on bd_role.permission_uuid=bd_permission.permission_uuid
                where bd_role.role_uuid in ('"."$roleStr"."')";
        $result = $this->dbExec($sql);
        
        return $this->apiResponse($result, "API_CODE_ROLE_DELETE");
    }
    
    /**********getRoleInfo**********/
    private function getRoleInfoV1(){
        $roleuuid = $this->params['roleuuid'];
        $this->apiParamsCheck($roleuuid);
        
        $sql = "select br.role_uuid, br.role_name, br.lock_flag, br.tenant_uuid, br.create_user_uuid, br.create_user_name, br.create_time, bp.content 
                from bd_role br, bd_permission bp where br.permission_uuid = bp.permission_uuid and br.role_uuid = ?";
        $data = $this->dbSelect($sql, array($roleuuid));
        $info = array();
        if(!empty($data)){
            $info = array(
                "roleuuid" => $data[0]['role_uuid'],
                "role_name" => $data[0]['role_name'],
                "lock_flag" => $data[0]['lock_flag'],
                "tenant_uuid" => $data[0]['tenant_uuid'],
                "create_user_uuid" => $data[0]['create_user_uuid'],
                "create_user_name" => $data[0]['create_user_name'],
                "create_time" => $data[0]['create_time'],
                "permission" => json_decode($data[0]['content'], true)
            );
        }
        return $this->apiResponse(!empty($data), "API_CODE_ROLE_GET_INFO", $info);
    }
    
    /**********lockRole**********/
    private function lockRoleV1(){
//         //权限检查
//         $this->pOperationPermissionCheckExit("p_safety_role_disable");
        $rolelist = $this->params['rolelist'];
        $this->apiParamsCheck($rolelist);
        
        $roleStr = implode("','",$rolelist);
        $sql = "update bd_role set lock_flag= ? where role_uuid in ('"."$roleStr"."')";
        $result = $this->dbExec($sql, array(Xphp::$_config['FLAG']['UNSET']));
        
        return $this->apiResponse($result, "API_CODE_ROLE_LOCK");
    }
    
    /**********unlockRole**********/
    private function unlockRoleV1(){
//         //权限检查
//         $this->pOperationPermissionCheckExit("p_safety_role_enable");
        $rolelist = $this->params['rolelist'];
        $this->apiParamsCheck($rolelist);
        
        $roleStr = implode("','",$rolelist);
        $sql = "update bd_role set lock_flag= ? where role_uuid in ('"."$roleStr"."')";
        $result = $this->dbExec($sql, array(Xphp::$_config['FLAG']['SET']));
        
        return $this->apiResponse($result, "API_CODE_ROLE_UNLOCK");
    }
    
    /**********allocateToUser**********/
    private function allocateToUserV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_role_allot");
        $roleuuid = $this->params['roleuuid'];
        $userList = $this->params['userList'];
        $this->apiParamsCheck($roleuuid);
        
        //删除角色关联
        $sqlDelete = "delete from mt_user_role where role_uuid = ?";
        $result = $this->dbExec($sqlDelete, array($roleuuid));
        //未选中默认取消所有关联
        if(!empty($userList)){
            //添加角色与用户关联
            $result = $this->pAddRoleUser($roleuuid, $userList);
        }
        
        return $this->apiResponse($result, "API_CODE_ROLE_ALLOCATE_USER_LIST");
    }
    
    /**********allocateToUserGroup**********/
    private function allocateToUserGroupV1(){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_safety_role_allot");
        $roleuuid = $this->params['roleuuid'];
        $userGroupList = $this->params['userGroupList'];
        $this->apiParamsCheck($roleuuid);
        
        //删除角色关联
        $sqlDelete = "delete from mt_user_group_role where role_uuid = ?";
        $result = $this->dbExec($sqlDelete, array($roleuuid));
        
        //未选中默认取消所有关联
        if(!empty($userGroupList)){
            //添加角色与用户组关联
            $result = $this->pAddRoleUserGroup($roleuuid, $userGroupList);
        }
        
        return $this->apiResponse($result, "API_CODE_ROLE_ALLOCATE_USER_GROUP_LIST");
    }
    
    /**********getRolesLists**********/
    private function getRolesListsV1(){
        $begin = $this->params['begin'];
        $count = $this->params['count'];
        $this->apiParamsCheck($count);
        
        $sql = "select br.role_uuid, br.role_name, br.lock_flag, br.tenant_uuid, br.create_user_uuid, br.create_user_name, br.create_time, bp.content 
                from bd_role br, bd_permission bp where br.permission_uuid = bp.permission_uuid limit ? , ?";
        $sqlCount = "select count(id) as total from bd_role";
        
        $sqlParams = array($begin, $count);
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, array());
        $records = array();
        foreach ($data as $d){
            $records[] = array(
                "roleuuid" => $d['role_uuid'],
                "role_name" => $d['role_name'],
                "lock_flag" => $d['lock_flag'],
                "tenant_uuid" => $d['tenant_uuid'],
                "create_user_uuid" => $d['create_user_uuid'],
                "create_user_name" => $d['create_user_name'],
                "create_time" => $d['create_time'],
                "permission" => json_decode($d['content'], true)
            );
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_ROLE_GET_LIST", $data);
    }
    
    
    /**********getAllPermission***********/
    private function getAllPermissionV1(){
        $extention = Xphp::instance("APISettingsHandler", "getExtensionLicense", array());
        $pagelist = array();
        if(!empty($extention)){
            $pagelist = $extention['p'];
        }
        
        $page = require_once CONF_PATH . 'page.php';
        $pageList = $this->getAllPermissionArray($page, $pagelist);
//         var_dump($pageList);
        
        return $this->apiResponse(true, "API_CODE_USER_GROUP_GET_LIST", $pageList);
    }
    
    
    /**********************************其他工具方法************************************/
    /**
     * 通过角色名字获取角色唯一标识
     * @param string $name
     * @author luokai@vinchin.com
     * @return string|fetchAll()
     */
    public function pGetRoleuuidByName($name){
        $sql = "select role_uuid from bd_role where role_name = ?";
        $data = $this->dbSelect($sql, array($name));
        $roleuuid = "";
        if(!empty($data)){
            $roleuuid = $data[0]['role_uuid'];
        }
        
        return $roleuuid;
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
     * 公共方法
     * 获取用户所有权限(包括用户和所在用户组的权限)
     * @param string $useruuid  用户uuid
     * @author xiezhuowei@vinchin.com
     * @return array 一维数组,合并后的所有权限
     */
    public function pGetUserAllPermission($useruuid, $tenantuuid, $pageFlag = false){
        //先获取用户的直接关联角色对应的权限
        $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content from
                bd_user bu, mt_user_role mur, bd_role br, bd_permission bp
                where bu.user_uuid = mur.user_uuid and mur.role_uuid = br.role_uuid and
                br.permission_uuid = bp.permission_uuid and bu.user_uuid = ? and br.lock_flag = ?";
        $data1 = $this->dbSelect($sql, array($useruuid, Xphp::$_config['FLAG']['SET']));
        
        //再获取用户所在用户组关联的角色对应的权限
        $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_role mugr, bd_role br, bd_permission bp
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugr.user_group_uuid and bug.user_group_uuid = mugr.user_group_uuid
                and mugr.role_uuid = br.role_uuid and br.permission_uuid = bp.permission_uuid and bu.user_uuid = ? and br.lock_flag = ? and bug.lock_flag = ?";
        $data2 = $this->dbSelect($sql, array($useruuid, Xphp::$_config['FLAG']['SET'], Xphp::$_config['FLAG']['SET']));
        
        //合并两个结果权限,先合并,再去重
        $data = array_merge($data1, $data2);
        $utils = Xphp::instance('Utils');
        $data = $utils->unique_multidim_array($data, "permission_uuid");
        
        
        //合并所有权限
        $permission = array();
        foreach ($data as $d){
            $permission = array_merge($permission, json_decode($d['content'], true));
        }
        $permission = array_unique($permission);
        
        //租户内用户强制不显示副本和归档
        /*if(!empty($tenantuuid)){
            $permission = array_diff($permission, ["datacopy", "data_archive", "vm_overview", "orch", "setting_manager", "authorization_module", "storage_report"]);
        }*/
        //角色权限创建以及页面显示
        if($pageFlag){
            return Xphp::instance('APIUsersHandler', 'getAuthSoftwarePermission', array($permission, $useruuid));
        }else{
            return $permission;
        }
    }
    
    /**
     * 得到完整的用户权限树(递归)
     * @param array $page   所有配置的页面
     */
    private function getAllPermissionArray($page, $userPermission){
        $tree = array();
        foreach ($page as $p){
            //过滤不在授权中的页面和权限
            if("10" != $p['level']){
                //如果不是操作权限,检查是否在授权中,如果不在授权中,跳过本项,跳过本项后,其child会自动跳过
                if(!in_array($p['name'], $userPermission)) continue;
            }
            $node = array(
                "name" => $p['name'],
                "title" => Xphp::$_lang[$p['title']],
            );
            if(!empty($p['child'])){
                //如果有子目录
                $node['children'] = $this->getAllPermissionArray($p['child'], $userPermission);
            }
            
            $tree[] = $node;
        }
        
        return $tree;
    }
    
    /**
     * 通过角色uuid得到权限uuid
     * @param unknown $roleuuid
     */
    private function getPermissionUUIDWithRoleUUID($roleuuid){
        $sql = "select permission_uuid from bd_role where role_uuid = ?";
        $data = $this->dbSelect($sql, array($roleuuid));
        return $data[0]['permission_uuid'];
    }
    
    /**
     * 添加角色和用户关联
     * @param string $roleuuid 角色唯一标识
     * @param array $userList 用户唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddRoleUser($roleuuid, $userList){
        if(empty($userList)) return false;
        $sql = "insert into mt_user_role (role_uuid,user_uuid) values ";
        foreach ($userList as $key=>$l){
            if($key != (count($userList) - 1)){
                $sql .="('".$roleuuid."','".$l."'),";
            }else{
                $sql .="('".$roleuuid."','".$l."')";
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
     * 添加角色和用户组关联
     * @param string $roleuuid 角色唯一标识
     * @param array $userGroupList 用户组唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddRoleUserGroup($roleuuid, $userGroupList){
        if(empty($userGroupList)) return false;
        $sql = "insert into mt_user_group_role (role_uuid, user_group_uuid) values ";
        foreach ($userGroupList as $key=>$l){
            $sql .="('".$roleuuid."','".$l."')";
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
}