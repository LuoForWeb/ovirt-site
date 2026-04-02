<?php
/******************************************* 
** 资源处理类
** 
** @author       luokai@vinchin.com 
** @date         2022-04-14 
** @version      1.0.0 
** @copyright    Copyright 2022 vinchin.com 
********************************************/
class APIResourceHandler extends OPHandler{
    
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/resource" => array(
            'POST' => 'createResourceGroup',    //创建资源组
            'PUT' => 'editResourceGroup',       //修改资源组
            'DELETE' => 'deleteResourceGroup',  //删除资源组
        ),
        
        "/resource/user" => array(
            'POST' => 'allocateToUser',    //分配资源到指定用户
            'DELETE' => 'deleteUserResource',  //取消资源和用户关联
            'GET' => 'getUserResource',       //获取指定用户下的资源信息
        ),
        "/resource/usergroup" => array(
            'POST' => 'allocateToUserGroup',    //分配资源到指定用户组
            'DELETE' => 'deleteUserGroupResource',  //取消资源和用户组关联
            'GET' => 'getUserGroupResource',       //获取指定用户组下的资源信息
        ),
        "/resource/resourcegroup" => array(
            'POST' => 'allocateToResourceGroup',    //分配资源到指定资源组
            'DELETE' => 'deleteResourceGroupResource',  //取消资源和资源组关联
            'GET' => 'getResourceGroupResource',       //获取指定资源组下的资源信息
        ),
        "/resource/lists" => array(
            'GET' => 'getResourceGroupLists'    //获取资源组列表
        ),
        "/resource/tenant" => array(
            'GET' => 'getTenantResource'    //获取租户下的资源
        ),
        "/resource/user_resource" => array(
            'GET' => 'getUserAllocateResource'    //获取当前用户可分配资源
        ),
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 创建资源组路由控制
     */
    protected function createResourceGroup(){
        //定义方法版本
        $version = array(
            "v1" => "createResourceGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改资源组路由控制
     */
    protected function editResourceGroup(){
        //定义方法版本
        $version = array(
            "v1" => "editResourceGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除资源组路由控制
     */
    protected function deleteResourceGroup(){
        //定义方法版本
        $version = array(
            "v1" => "deleteResourceGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 分配资源到用户路由控制
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
     * 取消用户与资源关联路由控制
     */
    protected function deleteUserResource(){
        //定义方法版本
        $version = array(
            "v1" => "deleteUserResourceV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取指定用户下的资源路由控制
     */
    protected function getUserResource(){
        //定义方法版本
        $version = array(
            "v1" => "getUserResourceV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 分配资源到用户组路由控制
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
     * 取消用户组和资源关联组路由控制
     */
    protected function deleteUserGroupResource(){
        //定义方法版本
        $version = array(
            "v1" => "deleteUserGroupResourceV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取指定用户组下的资源路由控制
     */
    protected function getUserGroupResource(){
        //定义方法版本
        $version = array(
            "v1" => "getUserGroupResourceV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 分配资源到资源组路由控制
     */
    protected function allocateToResourceGroup(){
        //定义方法版本
        $version = array(
            "v1" => "allocateToResourceGroupV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 取消资源组与资源关联路由控制
     */
    protected function deleteResourceGroupResource(){
        //定义方法版本
        $version = array(
            "v1" => "deleteResourceGroupResourceV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取指定资源组下的资源路由控制
     */
    protected function getResourceGroupResource(){
        //定义方法版本
        $version = array(
            "v1" => "getResourceGroupResourceV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     * 获取资源组列表(多个)路由控制
     */
    protected function getResourceGroupLists(){
        //定义方法版本
        $version = array(
            "v1" => "getResourceGroupListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取租户下的资源路由控制
     */
    protected function getTenantResource(){
        //定义方法版本
        $version = array(
            "v1" => "getTenantResourceV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取当前用户可分配资源路由控制
     */
    protected function getUserAllocateResource(){
        //定义方法版本
        $version = array(
            "v1" => "getUserAllocateResourceV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**********createResourceGroup**********/
    private function createResourceGroupV1(){
        //TODO
        $resourceGroupName = $this->params['resource_group_name'];
        $description = !empty($this->params['description'])? $this->params['description']: "";
        //检查参数是否为空
        $this->apiParamsCheck($resourceGroupName, $description);
        $tenantuuid = $this->params['tenantuuid'];
        //如果是租户内部创建
        if(empty($tenantuuid)){
            $tenantuuid = Xphp::$_user['tenantuuid'];
        }
        $createUseruuid = Xphp::$_user['useruuid'];
        $createUsername = Xphp::$_user['username'];
        $utils = Xphp::instance('Utils');
        $uuid = $utils->uuid(); //生成资源组uuid
        $createTime = date('Y-m-d H:i:s');
        $sqlParams = array($uuid, $resourceGroupName, $description, $tenantuuid, $createTime, $createUseruuid, $createUsername);
        $sql = "insert into bd_resource_group (resource_group_uuid, resource_group_name, description, tenant_uuid, create_time, create_user_uuid, create_user_name) values (?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbExec($sql, $sqlParams);
        $data = array();
        //添加资源组返回结果
        if($result){
            //添加资源组与用户关联
            $this->pAddUserResourceGroup($createUseruuid, array($uuid));
            //添加资源组与租户关联
            if(!empty($tenantuuid)){
                $this->pAddResourceGroupTenant($tenantuuid, array($uuid));
            }
            $data = array(
                'resource_group_uuid' => $uuid
            );
        }
        
        return $this->apiResponse($result, "API_CODE_RESOURCE_ADD_GROUP", $data, array());
        
    }
    
    /**********editResourceGroup**********/
    private function editResourceGroupV1(){
        //TODO
        $resourceGroupName = $this->params['resource_group_name'];
        $resourceGroupUUID = $this->params['resource_group_uuid'];
        $description = !empty($this->params['description'])? $this->params['description']: "";
        //检查参数是否为空
        $this->apiParamsCheck($resourceGroupName, $resourceGroupUUID);
        $editTime = date('Y-m-d H:i:s');
        $sqlParams = array($resourceGroupName, $description, $editTime, $resourceGroupUUID);
        $sql = "update bd_resource_group set resource_group_name = ?, description = ?, create_time = ? where resource_group_uuid = ?";
        $result = $this->dbExec($sql, $sqlParams);
        return $this->apiResponse($result, "API_CODE_RESOURCE_EDIT_GROUP");
    }
    
    /**********deleteResourceGroup**********/
    private function deleteResourceGroupV1(){
        //TODO
        $resourceGroupuuid = $this->params['resource_group_uuid'];
        //检查参数是否为空
        $this->apiParamsCheck($resourceGroupuuid);
        $tenantuuid = $this->params['tenantuuid'];
        //如果是租户内部创建
        if(empty($tenantuuid)){
            $tenantuuid = Xphp::$_user['tenantuuid'];
        }
        $sqlParams = array($resourceGroupuuid);
        $sql = "delete from bd_resource_group where resource_group_uuid = ? ";
        $result = $this->dbExec($sql, $sqlParams);
        
        //删除资源组返回结果到界面
        if($result){
            //取消资源组与用户关联
            $this->pDeleteResourceGroupUser(array($resourceGroupuuid));
            //取消资源组与用户组关联
            $this->pDeleteResourceGroupUserGroup(array($resourceGroupuuid));
            //取消资源组与资源关联
            $this->pDeleteResourceGroupAllResource(array($resourceGroupuuid));
            //取消资源组与租户关联
            if(!empty($tenantuuid)){
                $this->pDeleteResourceGroupTenant($tenantuuid, array($resourceGroupuuid));
            }
            
        }
        
        return $this->apiResponse($result, "API_CODE_RESOURCE_DELETE_GROUP");
    }
    
    /**********allocateToUser**********/
    private function allocateToUserV1(){
        //TODO
        $useruuid = $this->params['user_uuid']; //用户唯一标识
        $resourceList = $this->params['resource_list'];//分配资源列表
        $resourceType = $this->params['resource_type'];//资源类型
        //检查传入参数是否为空
        $this->apiParamsCheck($useruuid, $resourceList, $resourceType);
        $result = $this->pAddUserResourceUnify($useruuid, $resourceList, $resourceType);
        return $this->apiResponse($result, "API_CODE_RESOURCE_ALLOCATE_TO_USER");
    }
    
    /**********deleteUserResource**********/
    private function deleteUserResourceV1(){
        //TODO
        $useruuid = $this->params['user_uuid']; //用户唯一标识
        $resourceList = $this->params['resource_list'];    //资源唯一标识集合
        $resourceType = intval($this->params['resource_type']);//资源类型
        //检查传入参数是否为空
        $this->apiParamsCheck($useruuid, $resourceList, $resourceType);
        switch ($resourceType){
            case Xphp::$_config['RESOURCE_TYPE']['VM']:
                //检查虚拟机资源是否正在使用
                $this->checkDeleteUserVM($useruuid, $resourceList);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['NODE']:
                //检查节点资源是否正在使用
                $this->checkDeleteUserNode($useruuid, $resourceList);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['STORAGE']:
                //检查存储资源是否正在使用
                $this->checkDeleteUserStorage($useruuid, $resourceList);
                break;
        }
        
        //调用统一处理取消用户和虚拟机关联
        $result = $this->pDeleteUserResourceUnify($useruuid, $resourceList, $resourceType);
        return $this->apiResponse($result, "API_CODE_RESOURCE_DELETE_RESOURCE_USER");
    }
    
    /**********getUserResource**********/
    private function getUserResourceV1(){
        //TODO
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $userUUID = $this->params['user_uuid']; //用户唯一标识
        $resourceType = intval($this->params['resource_type']); //资源类型
        $this->apiParamsCheck($count, $resourceType, $userUUID);
        
        switch ($resourceType){
            case Xphp::$_config['RESOURCE_TYPE']['VM']:
                //获取虚拟机资源内容
                $resourceInfo = $this->pGetUserResourceVM($userUUID, "tree_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']:
                //获取传输代理资源内容
                $resourceInfo = $this->pGetUserResourceAppliance($userUUID, "id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['NODE']:
                ///获取节点资源内容
                $resourceInfo = $this->pGetUserResourceNode($userUUID, "node_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['STORAGE']:
                ///获取存储资源内容
                $resourceInfo = $this->pGetUserResourceStorage($userUUID, "storage_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['RESOURCE_GROUP']:
                ///获取资源组内容
                $resourceInfo = $this->pGetUserResourceGroup($userUUID, "brg.id", "desc", $begin, $count);
                break;
        }
        $list = $resourceInfo['data'];
        //通过公共方法组合得到的资源
        $records =  $this->pGetResoueceList($list, $resourceType);
        
        $data = array(
            "total" => intval($resourceInfo['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_RESOURCE_GET_RESOURCE_OF_USER", $data);
        
    }
    
    /**********allocateToUserGroup**********/
    private function allocateToUserGroupV1(){
        //TODO
        $userGroupUUID = $this->params['user_group_uuid'];  //用户组唯一标识
        $resourceList = $this->params['resource_list'];//分配资源列表
        $resourceType = $this->params['resource_type'];//资源类型
        //检查传入参数是否为空
        $this->apiParamsCheck($userGroupUUID, $resourceList, $resourceType);
        //分配对应资源到用户组
        $result = $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType);
        return $this->apiResponse($result, "API_CODE_RESOURCE_ALLOCATE_TO_USER_GROUP");
    }
    
    /**********deleteUserGroupResource**********/
    private function deleteUserGroupResourceV1(){
        //TODO
        $userGroupUUID = $this->params['user_group_uuid'];  //用户组唯一标识
        $resourceList = $this->params['resource_list'];    //资源唯一标识集合
        $resourceType = intval($this->params['resource_type']);//资源类型
        //检查传入参数是否为空
        $this->apiParamsCheck($userGroupUUID, $resourceList, $resourceType);
        //调用统一处理取消用户组和资源关联
        $result = $this->pDeleteUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType);
        return $this->apiResponse($result, "API_CODE_RESOURCE_DELETE_RESOURCE_USER_GROUP");
    }
    
    /**********getUserGroupResource**********/
    private function getUserGroupResourceV1(){
        //TODO
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $userGroupUUID = $this->params['user_group_uuid']; //用户组唯一标识
        $resourceType = intval($this->params['resource_type']); //资源类型
        $this->apiParamsCheck($count, $resourceType, $userGroupUUID);
        
        switch ($resourceType){
            case Xphp::$_config['RESOURCE_TYPE']['VM']:
                //获取虚拟机资源内容
                $resourceInfo = $this->pGetUserGroupResourceVM("", $userGroupUUID, "tree_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']:
                //获取传输代理资源内容
                $resourceInfo = $this->pGetUserGroupResourceAppliance("", $userGroupUUID, "id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['NODE']:
                ///获取节点资源内容
                $resourceInfo = $this->pGetUserGroupResourceNode("", $userGroupUUID, "node_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['STORAGE']:
                ///获取存储资源内容
                $resourceInfo = $this->pGetUserGroupResourceStorage("", $userGroupUUID, "storage_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['RESOURCE_GROUP']:
                ///获取资源组内容
                $resourceInfo = $this->pGetUserGroupResourceGroup($userGroupUUID, "id", "desc", $begin, $count);
                break;
        }
        $list = $resourceInfo['data'];
        //通过公共方法组合得到的资源
        $records =  $this->pGetResoueceList($list, $resourceType);
        
        $data = array(
            "total" => intval($resourceInfo['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_RESOURCE_GET_RESOURCE_OF_USER_GROUP", $data);
    }
    
    /**********allocateToResourceGroup**********/
    private function allocateToResourceGroupV1(){
        //TODO
        $resourceGroupUUID = $this->params['resource_group_uuid'];//资源组唯一标识
        $resourceList = $this->params['resource_list'];//分配资源列表
        $resourceType = $this->params['resource_type'];//资源类型
        //检查传入参数是否为空
        $this->apiParamsCheck($resourceGroupUUID, $resourceList, $resourceType);
        $result = $this->pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType);
        return $this->apiResponse($result, "API_CODE_RESOURCE_ALLOCATE_TO_RESOURCE_GROUP");
    }
    
    /**********deleteResourceGroupResource**********/
    private function deleteResourceGroupResourceV1(){
        //TODO
        $resourceGroupUUID = $this->params['resource_group_uuid'];//资源组唯一标识
        $resourceList = $this->params['resource_list'];//分配资源列表
        $resourceType = $this->params['resource_type'];//资源类型
        //检查传入参数是否为空
        $this->apiParamsCheck($resourceGroupUUID, $resourceList, $resourceType);
        //调用统一处理取消资源组和资源关联
        $result = $this->pDeleteResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType);
        return $this->apiResponse($result, "API_CODE_RESOURCE_DELETE_RESOURCE_RESOURCE_GROUP");
    }
    
    /**********getResourceGroupResource**********/
    private function getResourceGroupResourceV1(){
        //TODO
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $resourceGroupUUID = $this->params['resource_group_uuid']; //资源组唯一标识
        $resourceType = intval($this->params['resource_type']); //资源类型
        $this->apiParamsCheck($count, $resourceType, $resourceGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetResourceGroupResource($resourceGroupUUID, $resourceType);
        switch ($resourceType){
            case Xphp::$_config['RESOURCE_TYPE']['VM']:
                //获取虚拟机资源内容
                $resourceInfo = $this->getResourceVM($resourceUUIDList, "tree_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']:
                //获取传输代理资源内容
                $resourceInfo = $this->getResourceAppliance($resourceUUIDList, "id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['NODE']:
                ///获取节点资源内容
                $resourceInfo = $this->getResourceNode($resourceUUIDList, "node_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['STORAGE']:
                ///获取存储资源内容
                $resourceInfo = $this->getResourceStorage($resourceUUIDList, "storage_id", "desc", $begin, $count);
                break;
        }
        $list = $resourceInfo['data'];
        //通过公共方法组合得到的资源
        $records =  $this->pGetResoueceList($list, $resourceType);
        
        $data = array(
            "total" => intval($resourceInfo['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_RESOURCE_GET_RESOURCE_OF_RESOURCE_GROUP", $data);
    }
    
    /**********getResourceGroupLists**********/
    private function getResourceGroupListsV1(){
        //TODO
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $this->apiParamsCheck($count);
        
        $sql = "select unix_timestamp(brg.create_time) create_time, brg.create_user_name, brg.create_user_uuid, brg.resource_group_uuid, brg.resource_group_name, brg.description, brg.config from bd_resource_group brg left join mt_resource_group_tenant mrgt
                on brg.resource_group_uuid = mrgt.resource_group_uuid ";
        $sqlCount = "select count(brg.resource_group_uuid) as total from bd_resource_group brg left join mt_resource_group_tenant mrgt
                on brg.resource_group_uuid = mrgt.resource_group_uuid ";
        
        $sqlParams = array($begin, $count);
        $sqlCountParams = array();
        
        $sql .= " limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $records = array();
        if(!empty($data)){
            foreach ($data as $d){
                $records[] = array(
                    'resource_group_uuid' => $d['resource_group_uuid'],
                    'resource_group_name' => $d['resource_group_name'],
                    'description' => $d['description'],
                    'create_time' => intval($d['create_time']),
                    'create_user_uuid' => $d['create_user_uuid'],
                    'create_user_name' => $d['create_user_name'],
                );
            }
        }
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_RESOURCE_GET_GROUP_LIST", $data);
    }
    
    /**********getTenantResource**********/
    private function getTenantResourceV1(){
        //TODO
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $tenantUUID = $this->params['tenant_uuid']; //租户唯一标识
        $resourceType = intval($this->params['resource_type']); //资源类型
        $this->apiParamsCheck($count, $resourceType, $tenantUUID);
        
        switch ($resourceType){
            case Xphp::$_config['RESOURCE_TYPE']['VM']:
                //获取虚拟机资源内容
                $resourceInfo = $this->pGetTenantResourceVM($tenantUUID, "tree_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']:
                //获取传输代理资源内容
                $resourceInfo = $this->pGetTenantResourceAppliance($tenantUUID, "id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['NODE']:
                ///获取节点资源内容
                $resourceInfo = $this->pGetTenantResourceNode($tenantUUID, "node_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['STORAGE']:
                ///获取存储资源内容
                $resourceInfo = $this->pGetTenantResourceStorage($tenantUUID, "storage_id", "desc", $begin, $count);
                break;
        }
        $list = $resourceInfo['data'];
        //通过公共方法组合得到的资源
        $records =  $this->pGetResoueceList($list, $resourceType);
        
        $data = array(
            "total" => intval($resourceInfo['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_RESOURCE_GET_RESOURCE_OF_TENANT", $data);
    }
    
    /**********getUserAllocateResource**********/
    private function getUserAllocateResourceV1(){
        //TODO
        $begin = intval($this->params['begin']); 
        $count = intval($this->params['count']); 
        $resourceType = intval($this->params['resource_type']); //资源类型
        $this->apiParamsCheck($count, $resourceType);
        switch ($resourceType){
            case Xphp::$_config['RESOURCE_TYPE']['VM']:
                //获取虚拟机资源内容
                $resourceInfo = $this->pGetUserResourceVM(Xphp::$_user['useruuid'], "tree_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']:
                //获取传输代理资源内容
                $resourceInfo = $this->pGetUserResourceAppliance(Xphp::$_user['useruuid'], "id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['NODE']:
                ///获取节点资源内容
                $resourceInfo = $this->pGetUserResourceNode(Xphp::$_user['useruuid'], "node_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['STORAGE']:
                ///获取存储资源内容
                $resourceInfo = $this->pGetUserResourceStorage(Xphp::$_user['useruuid'], "storage_id", "desc", $begin, $count);
                break;
            case Xphp::$_config['RESOURCE_TYPE']['RESOURCE_GROUP']:
                ///获取资源组内容
                $resourceInfo = $this->pGetUserResourceGroup(Xphp::$_user['useruuid'], "brg.id", "desc", $begin, $count);
                break;
        }
        $list = $resourceInfo['data'];
        //通过公共方法组合得到的资源
        $records =  $this->pGetResoueceList($list, $resourceType);
        
        $data = array(
            "total" => intval($resourceInfo['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_RESOURCE_GET_USER_ASSIGNABLE_RESOURCE", $data);
    }
    
    
    /**********************************其他工具方法************************************/
    /**
     * 添加资源组与租户关联
     * @param string $tenantuuid 租户唯一标识
     * @param array $resourceGroupList 资源组列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    private function pAddResourceGroupTenant($tenantuuid, $resourceGroupList){
        if(empty($resourceGroupList)) return false;
        $sql = "insert into mt_resource_group_tenant (resource_group_uuid, tenant_uuid) values ";
        foreach ($resourceGroupList as $key => $l){
            $resourcegroupuuid = $l;
            
            $sql .="('".$resourcegroupuuid."','".$tenantuuid."')";
            if($key != (count($resourceGroupList) - 1)){
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
     * 添加用户和资源组关联
     * @param string $useruuid 用户唯一标识
     * @param array $resourceGroupList 资源组唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    private function pAddUserResourceGroup($useruuid, $resourceGroupList){
        if(empty($resourceGroupList)) return false;
        $sql = "insert into mt_user_resource_group (user_uuid, resource_group_uuid) values ";
        foreach ($resourceGroupList as $key=>$resourceGroupUUID){
            $sql .="('".$useruuid."','".$resourceGroupUUID."')";
            if($key != (count($resourceGroupList) - 1)){
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
     * 取消用户和选中资源组关联
     * @param stirng $useruuid 用户唯一标识
     * @param array $resourceGroupList 资源组唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    private function pDeleteUserResourceGroup($useruuid, $resourceGroupList){
        if(empty($resourceGroupList)) return false;
        $resourceGroupDes = implode("','", $resourceGroupList);
        $sql = "delete from mt_user_resource_group where user_uuid = ? and resource_group_uuid in ('".$resourceGroupDes."')";
        $result = $this->dbExec($sql, array($useruuid));
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 取消资源组和租户关联
     * @param string $tenantuuid 租户唯一标识
     * @param array $resourceGroupList 资源组列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    private function pDeleteResourceGroupTenant($tenantuuid, $resourceGroupList){
        if(empty($resourceGroupList)) return false;
        $resourceGroupDes = implode("','", $resourceGroupList);
        $sql = "delete from mt_resource_group_tenant where tenant_uuid = ? and resource_group_uuid in ('".$resourceGroupDes."')";
        $result = $this->dbExec($sql, array($resourcegroupuuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 公共方法
     * 取消选择资源组与所有用户的关联
     * @param array $list  资源组uuid集合
     * @author luokai@vinchin.com
     * @return boolean
     */
    private function pDeleteResourceGroupUser($list){
        if(empty($list)) return true;
        $listDes = implode("','", $list);
        $sql = "delete from mt_user_resource_group where resource_group_uuid in ('".$listDes."')";
        $result = $this->dbExec($sql);
        
        return true;
    }
    
    /**
     * 公共方法
     * 取消选择资源组与所有用户组的关联
     * @param array $list  资源组uuid集合
     * @author luokai@vinchin.com
     * @return boolean
     */
    private function pDeleteResourceGroupUserGroup($list){
        if(empty($list)) return true;
        $listDes = implode("','", $list);
        $sql = "delete from mt_user_group_resource_group where resource_group_uuid in ('".$listDes."')";
        $result = $this->dbExec($sql);
        
        return true;
    }
    
    /**
     * 公共方法
     * 取消选择资源组与所有用户的关联
     * @param array $list  资源组uuid集合
     * @author luokai@vinchin.com
     * @return boolean
     */
    private function pDeleteResourceGroupAllResource($list){
        if(empty($list)) return true;
        $listDes = implode("','", $list);
        $sql = "delete from mt_resource_resource_group where resource_group_uuid in ('".$listDes."')";
        $result = $this->dbExec($sql);
        
        return true;
    }
    
    /**
     * 组合资源列表成为可查询字符串形式,方便查询如:'uuid1','uuid2','uuid3'
     * @param unknown $resourceUUIDList
     */
    public function groupResourceUUIDToString($resourceUUIDList, $keyword = "resource_uuid"){
        $listStr = "";
        foreach ($resourceUUIDList as $resource){
            if (!empty($listStr)){
                $listStr .= ", '" . $resource[$keyword] . "'";
            }else{
                $listStr .= "'" . $resource[$keyword] . "'";
            }
        }
        return $listStr;
    }
    
    /**
     * 通过资源uuid列表获取虚拟机
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * @param array $search     虚拟机资源搜索
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceVM($resourceUUIDList, $orderby, $sort, $limit, $count, $flag = false, $search = array()){
        //组合资源uuid方便查询
        $vmuuidStr = $this->groupResourceUUIDToString($resourceUUIDList, "vm_uuid");
        $vcenterStr = $this->groupResourceUUIDToString($resourceUUIDList, "vcenter_uuid");
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(vt.tree_id) as total from vm_tree vt left join vm_vcenter vv
        on vt.vcenter_uuid = vv.vcenter_uuid where vt.display_mode = ? and vt.type = ? ";
        $sql = "select vt.vcenter_uuid, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.version, vt.conn_state, vt.power_state, vt.host_uuid, vt.detail from vm_tree vt left join vm_vcenter vv
        on vt.vcenter_uuid = vv.vcenter_uuid where vt.display_mode = ? and vt.type = ? ";
        $sqlParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        $sqlCountParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        if(!$flag){
            if(!empty($vmuuidStr) && !empty($vcenterStr)){
                $sql .= "and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
                $sqlCount .= "and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
            }else{
                $sql .= "and vt.uuid in ('') and vt.vcenter_uuid in ('')";
                $sqlCount .= "and vt.uuid in ('') and vt.vcenter_uuid in ('')";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $hypervisor = $search['hypervisor'];
            if($name && !empty($name)){
                $sql .= " and (vt.name like '%". $name ."%' or vt.dir_path like '%". $name ."%')";
                $sqlCount .= " and (vt.name like '%". $name ."%' or vt.dir_path like '%". $name ."%')";
            }
            
            if(!empty($hypervisor)){
                $sql .= " and vv.hypervisor_type = ? ";
                $sqlCount .= " and vv.hypervisor_type = ? ";
                $sqlParams = array_merge($sqlParams,array($hypervisor));
                $sqlCountParams = array_merge($sqlCountParams,array($hypervisor));
            }
        }
        
        $sql .= " order by $orderby $sort";
        
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array_merge($sqlParams,array($limit, $count));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        return $info;
    }
    
    /**
     * 通过资源uuid列表获取appliance
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @param boolean $flag     是否是master组
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceAppliance($resourceUUIDList, $orderby, $sort, $limit, $count, $search = array(), $flag = false){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(id) as total from bd_appliance where node_uuid = '' ";
        
        $sql = "select appliance_uuid, node_uuid, user_uuid, register_time, nickname, online_flag, ip, 	port,
        progress_server_listen_port, progress_server_start_port, progress_server_end_port, cdp_client_listen_port,
        cdp_client_log_listen_port, log_server_listen_port, system_info, detail from bd_appliance
        where node_uuid = '' ";
        
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " and appliance_uuid in ($listStr)";
                $sqlCount .= " and appliance_uuid in ($listStr)";
            }else{
                $sql .= " and appliance_uuid in ('')";
                $sqlCount .= " and appliance_uuid in ('')";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $sql .= " and ip like '%". $name ."%'";
            $sqlCount .= " and ip like '%". $name ."%'";
        }
        
        $sql .= " order by $orderby $sort ";
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array($limit, $count);
        }
        $sqlCountParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        
        return $info;
    }
    
    /**
     * 通过资源uuid列表获取节点
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * @param array  $search    搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceNode($resourceUUIDList, $orderby, $sort, $limit, $count, $flag = false, $search = array()){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(node_id) as total from bd_node ";
        $sql = "select auto_upgrade_flag, ip, port, node_uuid, host_name, os_type, os_version, process_type, register_time, node_type
        from bd_node ";
        
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " where node_uuid in ($listStr)";
                $sqlCount .= " where node_uuid in ($listStr) ";
            }else{
                $sql .= " where node_uuid in ('')";
                $sqlCount .= " where node_uuid in ('') ";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            if($flag){
                $sql .= " where ";
                $sqlCount .= " where ";
            }else{
                $sql .= " and ";
                $sqlCount .= " and ";
            }
            $sql .= " (host_name like '%". $name ."%' or ip like'%". $name ."%')";
            $sqlCount .= " (host_name like '%". $name ."%' or ip like'%". $name ."%')";
        }
        
        $sql .= " order by $orderby $sort";
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array($limit, $count);
        }
        $sqlCountParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        
        return $info;
    }
    
    /**
     * 通过资源uuid列表获取存储
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * $param array   $search   搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceStorage($resourceUUIDList, $orderby, $sort, $limit, $count, $flag = false, $search = array()){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(storage_id) as total from bd_storage_resource where lan_free_flag = ? and use_mode not in (2,3) ";
        
        $sql = "select storage_nickname, storage_uuid, storage_type, node_uuid, total_size, free_size, status, mount_flag, mount_point,
        global_flag, error_code, storage_config, warning_flag, warning_type, warning_value, use_mode, share_access_flag
        from bd_storage_resource
        where lan_free_flag = ? and use_mode not in (2,3) ";
        
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " and storage_uuid in ($listStr)";
                $sqlCount .= "and storage_uuid in ($listStr) ";
            }else{
                $sql .= " and storage_uuid in ('')";
                $sqlCount .= "and storage_uuid in ('') ";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $sql .= " and storage_nickname like '%". $name ."%'";
            $sqlCount .= " and storage_nickname like '%". $name ."%'";
        }
        
        $sql .= " order by $orderby $sort";
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $limit, $count);
        }
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        
        return $info;
    }
    
    /**
     * 公共方法
     * 得到用户所有资源(只有资源uuid和类型)
     * 用户-资源
     * 用户-资源组-资源
     * 用户-用户组-资源
     * 用户-用户组-资源组-资源
     * @param string $useruuid  用户uuid
     * @param int $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @author xiezhuowei@vinchin.com
     * @return array  资源UUID和资源类型列表
     */
    public function pGetUserAllResource($useruuid, $resourceType=""){
        $this->apiParamsCheck($useruuid);
        //如果按类型查找
        $sqlMore = "";
        $sqlParams = array($useruuid);
        if(!empty($resourceType)){
            $sqlMore = " and resource_type = ? ";
            $sqlParams = array($useruuid, $resourceType);
        }
        //用户-资源
        $sql = "select distinct mur.resource_uuid, mur.vm_uuid, mur.vcenter_uuid, mur.resource_type from
                bd_user bu, mt_user_resource mur
                where bu.user_uuid = mur.user_uuid and bu.user_uuid = ? ";
        $sql .= $sqlMore;
        $data1 = $this->dbSelect($sql, $sqlParams);
        
        //用户-资源组-资源
        $sql = "select distinct mrrg.resource_uuid, mrrg.vm_uuid, mrrg.vcenter_uuid, mrrg.resource_type from
                bd_user bu, mt_user_resource_group murg, mt_resource_resource_group mrrg
                where bu.user_uuid = murg.user_uuid and murg.resource_group_uuid = mrrg.resource_group_uuid
                and bu.user_uuid = ? ";
        $sql .= $sqlMore;
        $data2 = $this->dbSelect($sql, $sqlParams);
        
        //用户-用户组-资源
        $sql = "select distinct mugr.resource_uuid, mugr.vm_uuid, mugr.vcenter_uuid, mugr.resource_type from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_resource mugr
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugr.user_group_uuid and bug.user_group_uuid = mugr.user_group_uuid and bu.user_uuid = ? and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $data3 = $this->dbSelect($sql, $sqlParams);
        
        //用户-用户组-资源组-资源
        $sql = "select distinct mrrg.resource_uuid, mrrg.vm_uuid, mrrg.vcenter_uuid, mrrg.resource_type from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_resource_group mugrg, mt_resource_resource_group mrrg
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugrg.user_group_uuid and bug.user_group_uuid = mugrg.user_group_uuid and
                mugrg.resource_group_uuid = mrrg.resource_group_uuid and bu.user_uuid = ? and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $data4 = $this->dbSelect($sql, $sqlParams);
        
        //合并四个资源,并去重
        $data = array_merge($data1, $data2, $data3, $data4);
        $utils = Xphp::instance('Utils');
        $data = $utils->unique_multidim_array($data, "resource_uuid");
        return $data;
    }
    
    /**
     * 公共方法
     * 得到用户组所有资源(只有资源uuid和类型)
     * 用户-用户组-资源
     * 用户-用户组-资源组-资源
     * @param string $userGroupUUID  用户组uuid
     * @param int $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @author luokai@vinchin.com
     * @return array  资源UUID和资源类型列表
     */
    public function pGetUserGroupAllResource($userGroupUUID, $resourceType=""){
        $this->apiParamsCheck($userGroupUUID);
        //如果按类型查找
        $sqlMore = "";
        $sqlParams = array($userGroupUUID);
        if(!empty($resourceType)){
            $sqlMore = " and resource_type = ? ";
            $sqlParams = array($userGroupUUID, $resourceType);
        }
        //用户组-资源
        $sql = "select distinct mugr.resource_uuid, mugr.vm_uuid, mugr.vcenter_uuid, mugr.resource_type from
                bd_user_group bug, mt_user_group_resource mugr
                where bug.user_group_uuid = mugr.user_group_uuid and mugr.user_group_uuid = ? ";
        $sql .= $sqlMore;
        $data1 = $this->dbSelect($sql, $sqlParams);
        
        //用户组-资源组-资源
        $sql = "select distinct mrrg.resource_uuid, mrrg.vm_uuid, mrrg.vcenter_uuid, mrrg.resource_type from
                bd_user_group bug, mt_user_group_resource_group mugrg, mt_resource_resource_group mrrg
                where bug.user_group_uuid = mugrg.user_group_uuid and
                mugrg.resource_group_uuid = mrrg.resource_group_uuid and bug.user_group_uuid = ? ";
        $sql .= $sqlMore;
        $data2 = $this->dbSelect($sql, $sqlParams);
        
        //合并四个资源,并去重
        $data = array_merge($data1, $data2);
        $utils = Xphp::instance('Utils');
        $data = $utils->unique_multidim_array($data, "resource_uuid");
        return $data;
    }
    
    /**
     * 公共方法
     * 得到租户所有资源(只有资源uuid和类型)
     * @param string $tenantuuid  租户uuid
     * @param int $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @author xiezhuowei@vinchin.com
     * @return array  资源UUID和资源类型列表
     */
    public function pGetTenantAllResource($tenantuuid, $resourceType=""){
        $this->apiParamsCheck($tenantuuid);
        //获取租户下所有用户
        $apitenantHandler = Xphp::instance('APITenantHandler');
        $users = $apitenantHandler->pGetTenantAllUser($tenantuuid);
        
        //获取所有用户的资源列表
        $resourceList = array();
        foreach ($users as $user){
            $resourceUUIDList = $this->pGetUserAllResource($user['user_uuid'], $resourceType);
            $resourceList = array_merge($resourceList, $resourceUUIDList);
        }
        $utils = Xphp::instance('Utils');
        $resourceUUIDList = $utils->unique_multidim_array($resourceList, "resource_uuid");
        
        return $resourceUUIDList;
    }
    
    /**
     * 公共方法
     * 得到用户虚拟机
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  search     虚拟机资源搜索
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceVM($useruuid, $orderby = "tree_id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->apiParamsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['VM']);
        
        //检查用户是否属于Master组
        $apiuserHandler = Xphp::instance("APIUsersHandler");
        $masterFlag = $apiuserHandler->pCheckUserIsMaster($useruuid);
        //获取资源内容
        $resource = $this->getResourceVM($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag, $search);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户组虚拟机
     * @param string $userUUID 当前用户唯一标识
     * @param string $userGroupUUID  用户组uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array $search     查询虚拟机资源
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceVM($userUUID = "", $userGroupUUID, $orderby = "tree_id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->apiParamsCheck($userGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['VM']);
        //检查用户是否属于Master组
        $apiuserHandler = Xphp::instance("APIUsersHandler");
        $masterFlag = $apiuserHandler->pCheckUserIsMaster($userUUID);
        //获取资源内容
        $resource = $this->getResourceVM($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag, $search);
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户虚拟机
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array $search     虚拟机资源搜索
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceVM($tenantuuid, $orderby = "tree_id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->apiParamsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['VM']);
        
        //获取资源内容
        $resource = $this->getResourceVM($resourceUUIDList, $orderby, $sort, $limit, $count, $search);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户appliance
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceAppliance($useruuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->apiParamsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']);
        
        //检查当前用户是否属于Master组
        $apiuserHandler = Xphp::instance("APIUsersHandler");
        $masterFlag = $apiuserHandler->pCheckUserIsMaster($useruuid);
        
        //获取资源内容
        $resource = $this->getResourceAppliance($resourceUUIDList, $orderby, $sort, $limit, $count, $search, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户组appliance
     * @param string $userUUID  用户uuid
     * @param string $userGroupUUID 用户组uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceAppliance($userUUID = "", $userGroupUUID, $orderby = "id", $sort = "desc", $limit = 0, $count = 10){
        $this->apiParamsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']);
        
        //检查当前用户是否属于Master组
        $apiuserHandler = Xphp::instance("APIUsersHandler");
        $masterFlag = $apiuserHandler->pCheckUserIsMaster($userUUID);
        //获取资源内容
        $resource = $this->getResourceAppliance($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户appliance
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceAppliance($tenantuuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10){
        $this->apiParamsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']);
        
        //获取资源内容
        $resource = $this->getResourceAppliance($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户可用节点
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceNode($useruuid, $orderby = "node_id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['NODE']);
        
        //检查用户是否属于Master组
        $apiuserHandler = Xphp::instance("APIUsersHandler");
        $masterFlag = $apiuserHandler->pCheckUserIsMaster($useruuid);
        
        //获取资源内容
        $resource = $this->getResourceNode($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag, $search);
        
        return $resource;
    }
    
    
    /**
     * 公共方法
     * 得到用户组可用节点
     * @param string $userUUID  用户uuid
     * @param string $userGroupUUID 用户组uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceNode($userUUID = "", $userGroupUUID, $orderby = "node_id", $sort = "desc", $limit = 0, $count = 10){
        $this->apiParamsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['NODE']);
        
        //检查当前用户是否属于Master组
        $apiuserHandler = Xphp::instance("APIUsersHandler");
        $masterFlag = $apiuserHandler->pCheckUserIsMaster($userUUID);
        
        //获取资源内容
        $resource = $this->getResourceNode($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户可用节点
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceNode($tenantuuid, $orderby = "node_id", $sort = "desc", $limit = 0, $count = 10){
        $this->apiParamsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['NODE']);
        
        //获取资源内容
        $resource = $this->getResourceNode($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户可用存储
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceStorage($useruuid, $orderby = "storage_id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->apiParamsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
        //检查用户是否属于Master组
        $apiuserHandler = Xphp::instance("APIUsersHandler");
        $masterFlag = $apiuserHandler->pCheckUserIsMaster($useruuid);
        //获取资源内容
        $resource = $this->getResourceStorage($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag, $search);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户组可用存储
     * @param string $userUUID  用户uuid
     * @param string $userGroupUUID 用户组uudi
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceStorage($userUUID = "", $userGroupUUID, $orderby = "storage_id", $sort = "desc", $limit = 0, $count = 10){
        $this->apiParamsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
        //检查用户是否属于Master组
        $apiuserHandler = Xphp::instance("APIUsersHandler");
        $masterFlag = $apiuserHandler->pCheckUserIsMaster($userUUID);
        //获取资源内容
        $resource = $this->getResourceStorage($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户可用存储
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceStorage($tenantuuid, $orderby = "storage_id", $sort = "desc", $limit = 0, $count = 10){
        $this->apiParamsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
        
        //获取资源内容
        $resource = $this->getResourceStorage($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 添加资源与用户关联
     * @param string $userUUID 用户唯一标识
     * @param array $resourceList 资源信息列表
     * @param int $resourceType 资源类型
     * @author luokai@vinchin.com
     * @return string 统一返回操作结果消息到界面
     */
    public function pAddUserResourceUnify($userUUID, $resourceList, $resourceType = ""){
        if($resourceType == Xphp::$_config['RESOURCE_TYPE']['RESOURCE_GROUP']){
            //添加用户与资源组关联
            $list = array();
            foreach ($resourceList as $d){
                $list[] = $d['resource_uuid'];
            }
            //检查用户和资源组是否已添加关联
            $this->pCheckResourceGroupExist(Xphp::$_config['RESOURCE_FROM']['USER'],$resourceList,$userUUID);
            //添加用户和资源组公共方法
            return $this->pAddUserResourceGroup($userUUID, $list);
        }
        //调用添加用户与资源关联公共方法
        $resourceInfo=  array();
        if(!empty($resourceList)){
            foreach ($resourceList as $res){
                $resourceInfo[] = array(
                    'resourceuuid' => $res['resource_uuid'],
                    'vmuuid' => $resourceType == Xphp::$_config['RESOURCE_TYPE']['VM']?$res['resource_uuid']:"",
                    'vcenteruuid' => !empty($res['vcenter_uuid'])?$res['vcenter_uuid']:"",
                    'resourceType' => $resourceType
                );
            }
        }
        //检查用户和资源组是否已添加关联
        $this->pCheckResourceExist(Xphp::$_config['RESOURCE_FROM']['USER'],$resourceInfo,$userUUID);
        //调用户与资源关联公共方法
        return $this->pAddUserResource($userUUID, $resourceInfo);
    }
    
    
    
    /**
     * 公共方法
     * 取消用户和资源关联
     * @param string $userUUID
     * @param array $resourceList
     * @author luokai@vinchin.com
     * @return string
     */
    public function pDeleteUserResourceUnify($userUUID, $resourceList, $resourceType){
        if($resourceType == Xphp::$_config['RESOURCE_TYPE']['RESOURCE_GROUP']){
            //取消用户与资源组关联
            return $this->pDeleteUserResourceGroup($userUUID, $resourceList);
        }
        //调用取消用户与资源关联公共方法
        return $this->pDeleteUserResource($userUUID, $resourceList, $resourceType);
    }
    
    
    /**
     * 添加用户和资源关联
     * @param string $useruuid     用户唯一标识
     * @param array $resourceList 添加的资源信息
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserResource($useruuid, $resourceList){
        if(empty($resourceList)) return false;
        $sql = "insert into mt_user_resource (user_uuid, resource_uuid, vm_uuid, vcenter_uuid, resource_type) values ";
        foreach ($resourceList as $key=>$l){
            $resourceuuid = $l['resourceuuid'];
            $vmuuid = $l['vmuuid'];
            $vcenteruuid = $l['vcenteruuid'];
            $resourceType = $l['resourceType'];
            $sql .="('".$useruuid."','".$resourceuuid."','".$vmuuid."','".$vcenteruuid."','".$resourceType."')";
            if($key != (count($resourceList) - 1)){
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
     * 删除用户和资源关联关系
     * @param string $useruuid 用户唯一标识
     * @param array $resourceList 资源唯一标识列表
     * @param int $resourceType 资源类型
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserResource($useruuid, $resourceList, $resourceType){
        if(empty($resourceList)) return false;
        $resourceDes = implode("','", $resourceList);
        $sql = "delete from mt_user_resource where resource_type = ? and resource_uuid in ('".$resourceDes."') ";
        $sqlParams = array($resourceType);
        if(!empty($useruuid)){
            $sql .= " and user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }
        $result = $this->dbQuery($sql, $sqlParams);
        if(!empty($result)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 公共方法
     * 添加资源与用户组关联
     * @param string $userGroupUUID 用户组唯一标识
     * @param array $resourceList 资源信息列表
     * @param int $resourceType 资源类型
     * @author luokai@vinchin.com
     * @return string 统一返回操作结果消息到界面
     */
    public function pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType = ""){
        if($resourceType == Xphp::$_config['RESOURCE_TYPE']['RESOURCE_GROUP']){
            //添加用户与资源组关联
            $list = array();
            foreach ($resourceList as $d){
                $list[] = $d['resource_uuid'];
            }
            //检查用户组和资源组是否已添加关联
            $this->pCheckResourceGroupExist(Xphp::$_config['RESOURCE_FROM']['USER_GROUP'],$resourceList,$userGroupUUID);
            return $this->pAddUserGroupResourceGroup($userGroupUUID, $list);
        }
        //调用添加用户组与资源关联公共方法
        $resourceInfo=  array();
        if(!empty($resourceList)){
            foreach ($resourceList as $res){
                $resourceInfo[] = array(
                    'resourceuuid' => $res['resource_uuid'],
                    'vmuuid' => $resourceType == Xphp::$_config['RESOURCE_TYPE']['VM']?$res['resource_uuid']:"",
                    'vcenteruuid' => !empty($res['vcenter_uuid'])?$res['vcenter_uuid']:"",
                    'resourceType' => $resourceType
                );
            }
        }
        //检查用户组和资源组是否已添加关联
        $this->pCheckResourceExist(Xphp::$_config['RESOURCE_FROM']['USER_GROUP'],$resourceInfo,$userGroupUUID);
        return $this->pAddUserGroupResource($userGroupUUID, $resourceInfo);
    }
    
    
    
    /**
     * 公共方法
     * 取消用户组和资源关联
     * @param string $userUUID
     * @param array $resourceList
     * @author luokai@vinchin.com
     * @return string
     */
    public function pDeleteUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType){
        if($resourceType == Xphp::$_config['RESOURCE_TYPE']['RESOURCE_GROUP']){
            //取消用户与资源组关联
            return $this->pDeleteUserGroupResourceGroup($userGroupUUID, $resourceList);
        }
        //调用取消用户组与资源关联公共方法
        return $this->pDeleteUserGroupResource($userGroupUUID, $resourceList, $resourceType);
    }
    
    /**
     * 添加用户组和资源关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array $resourceList 一个或多个资源信息列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserGroupResource($usergroupuuid, $resourceList){
        if(empty($resourceList)) return false;
        $sql = "insert into mt_user_group_resource (user_group_uuid, resource_uuid, vm_uuid, vcenter_uuid, resource_type) values ";
        foreach ($resourceList as $key=>$l){
            $resourceuuid = $l['resourceuuid'];
            $vmuuid = $l['vmuuid'];
            $vcenteruuid = $l['vcenteruuid'];
            $resourceType = $l['resourceType'];
            $sql .="('".$usergroupuuid."','".$resourceuuid."','".$vmuuid."','".$vcenteruuid."','".$resourceType."')";
            if($key != (count($resourceList) - 1)){
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
     * 取消用户组与选中资源关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array $resourceList 资源唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserGroupResource($usergroupuuid, $resourceList, $resourceType){
        if(empty($resourceList)) return false;
        $resourceDes = implode("','", $resourceList);
        $sql = "delete from mt_user_group_resource where user_group_uuid = ? and resource_type = ? and resource_uuid in ('".$resourceDes."')";
        $result = $this->dbExec($sql, array($usergroupuuid, $resourceType));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 添加资源与资源组关联公共调用方法
     * @param string $resourceGroupUUID 资源组唯一标识
     * @param array $resourceList 资源信息列表
     * @param int $resourceType 资源类型
     * @author luokai@vinchin.com
     * @return string 统一返回操作结果消息到界面
     */
    public function pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType = ""){
        $resourceInfo=  array();
        if(!empty($resourceList)){
            foreach ($resourceList as $res){
                $resourceInfo[] = array(
                    'resourceuuid' => $res['resource_uuid'],
                    'vmuuid' => $resourceType == Xphp::$_config['RESOURCE_TYPE']['VM']?$res['resource_uuid']:"",
                    'vcenteruuid' => !empty($res['vcenter_uuid'])?$res['vcenter_uuid']:"",
                    'resourceType' => $resourceType
                );
            }
        }
        //检查用户组和资源组是否已添加关联
        $this->pCheckResourceExist(Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP'],$resourceInfo,$resourceGroupUUID);
        //调用添加资源组与资源关联公共方法
        return $this->pAddResourceGroupResource($resourceGroupUUID, $resourceInfo);
    }
    
    
    
    /**
     *
     * @param string $resourceGroupUUID 资源组唯一标识
     * @param array $resourceList  资源集合
     * @param int $resourceType 资源类型
     * @return string
     */
    public function pDeleteResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType){
        //调用取消资源组与资源关联公共方法
        return $this->pDeleteResourceGroupResource($resourceGroupUUID, $resourceList, $resourceType);
    }
    
    /**
     * 添加资源和资源组关联
     * @param string $resourcegroupuuid 资源组唯一标识
     * @param array $resourceList 资源信息列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddResourceGroupResource($resourcegroupuuid, $resourceList){
        if(empty($resourceList)) return false;
        $sql = "insert into mt_resource_resource_group (resource_group_uuid, resource_uuid, vm_uuid, vcenter_uuid, resource_type) values ";
        foreach ($resourceList as $key=>$l){
            $resourceuuid = $l['resourceuuid'];
            $vmuuid = $l['vmuuid'];
            $vcenteruuid = $l['vcenteruuid'];
            $resourceType = $l['resourceType'];
            $sql .="('".$resourcegroupuuid."','".$resourceuuid."','".$vmuuid."','".$vcenteruuid."','".$resourceType."')";
            if($key != (count($resourceList) - 1)){
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
     * 取消资源和资源组关联
     * @param string $resourcegroupuuid 资源组唯一标识
     * @param array $resourceList 资源唯一标识列表
     * @param int $resourceType 资源类型
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteResourceGroupResource($resourcegroupuuid, $resourceList, $resourceType){
        if(empty($resourceList)) return false;
        $resourceDes = implode("','", $resourceList);
        $sql = "delete from mt_resource_resource_group where resource_group_uuid = ? and resource_type = ? and resource_uuid in ('".$resourceDes."')";
        $result = $this->dbExec($sql, array($resourcegroupuuid, $resourceType));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 公共方法
     * 获取对应类型资源列表
     * @param array $data 获取的资源列表
     * @param int $resourceType 资源类型
     * @return unknown[][]|number[][]|NULL[][]
     */
    private function pGetResoueceList($data, $resourceType){
        if(empty($data)){
            exit($this->apiResponse(false,'API_CODE_RESOURCE_GET_FAILED_ERROR'));
        }
        $records = array();
        switch ($resourceType){
            case Xphp::$_config['RESOURCE_TYPE']['VM']:
                foreach ($data as $d){
                    $records[] = array(
                        "vm_uuid" => $d['uuid'],    //虚拟机唯一标识
                        "vm_name" => $d['name'],    //虚拟机名
                        "dir_path" => $d['dir_path'],//虚拟机路径
                        "vcenter_uuid" => $d['vcenter_uuid'],//虚拟机所在虚拟化中心唯一标识
                        "power_state" => $d['power_state'], //虚拟机开机状态
                    );
                }
                break;
            case Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']:
                foreach ($data as $d){
                    $records[] = array(
                        "appliance_uuid" => $d['appliance_uuid'],
                        "appliance_ip" => $d['ip'],
                        "nickname" => $d['nickname'],
                        "port" => $d['port'],
                        "online_flag" => $d['online_flag'],
                    );
                }
                break;
            case Xphp::$_config['RESOURCE_TYPE']['NODE']:
                $apiNodeHandler = Xphp::instance('APINodesHandler');
                $utils = Xphp::instance("Utils");
                foreach ($data as $d){
                    $nodeDeployStatus = $apiNodeHandler->getNodeDeployStatus($d['node_uuid']);
                    $nodeAllStatus = $apiNodeHandler->getNodeAllStatus($d['node_uuid']);
                    $records[] = array(
                        "node_uuid" => $d['node_uuid'],
                        "node_ip" => $d['ip'],
                        "host_name" => $d['host_name'],
                        "deploy_status" => $nodeDeployStatus,
                        "node_status" => $utils->parseBoolToFlag($nodeAllStatus['flag']),
                    );
                }
                break;
            case Xphp::$_config['RESOURCE_TYPE']['STORAGE']:
                $apiNodeHandler = Xphp::instance('APINodesHandler');
                $utils = Xphp::instance("Utils");
                foreach ($data as $d){
                    $nodeAllStatus = $apiNodeHandler->getNodeAllStatus($d['node_uuid']);
                    $nodeStatus = $utils->parseBoolToFlag($nodeAllStatus['flag']);
                    //异地备份系统节点显示
                    if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
                        $storageConfig = json_decode($d['storage_config'], true);
                        $nodeStatus = intval($storageConfig['remote_status']);
                    }
                    $records[] = array(
                        "storage_uuid" => $d['storage_uuid'],
                        "storage_nickname" => $d['storage_nickname'],
                        "storage_type" => $d['storage_type'],
                        "total_size" => $d['total_size'],
                        "free_size" => $d['free_size'],
                        "storage_status" => $d['status'],
                        "node_uuid" => $d['node_uuid'],
                        "node_status" => $nodeStatus,
                    );
                }
                break;
            case Xphp::$_config['RESOURCE_TYPE']['RESOURCE_GROUP']:
                foreach ($data as $d){
                    $records[] = array(
                        "resource_group_uuid" => $d['resource_group_uuid'],
                        "resource_group_name" => $d['resource_group_name'],
                        "description" => $d['description'],
                    );
                }
                break;
        }
        return $records;
    }
    
    /**
     * 公共方法
     * 获取资源组拥有资源唯一标识列表
     * @param string $resourceGroupUUID 资源组唯一标识
     * @param int $resourceType 资源类型
     * @author luokai@vinchin.com
     * @return array 资源组所拥有资源唯一标识列表
     */
    private function pGetResourceGroupResource($resourceGroupUUID, $resourceType=""){
        $sql = "select resource_uuid, vm_uuid, vcenter_uuid from mt_resource_resource_group where resource_group_uuid = ? ";
        $sqlParams = array($resourceGroupUUID);
        if(!empty($resourceType)){
            $sql .= " and resource_type = ?";
            $sqlParams = array($resourceGroupUUID, $resourceType);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $resourceList = array();
        if(!empty($data)){
            $resourceList = $data;
        }
        
        return $resourceList;
        
    }
    
    /**
     * 添加用户组和资源组关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array $resourceGroupList 资源组唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pAddUserGroupResourceGroup($usergroupuuid, $resourceGroupList){
        $sql = "insert into mt_user_group_resource_group (user_group_uuid, resource_group_uuid) values ";
        foreach ($resourceGroupList as $key=>$l){
            $sql .="('".$usergroupuuid."','".$l."')";
            if($key != (count($resourceGroupList) - 1)){
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
     * 取消用户组和选中资源组关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param array $resourceGroupList 资源组唯一标识列表
     * @author luokai@vinchin.com
     * @return boolean 执行结果 成功|失败
     */
    public function pDeleteUserGroupResourceGroup($usergroupuuid, $resourceGroupList){
        if(empty($resourceGroupList)) return false;
        $resourceGroupDes = implode("','", $resourceGroupList);
        $sql = "delete from mt_user_group_resource_group where user_group_uuid = ? and resource_group_uuid in ('".$resourceGroupDes."')";
        $result = $this->dbExec($sql, array($usergroupuuid));
        
        if($result){
            return true;
        }else{
            return false;
        }
        
    }
    
    /**
     * 通过用户UUID获取资源组列表
     * @param string $userUUID 用户唯一标识
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * $param array   $search   搜索参数集合
     * $param boolean $createFlag   分配资源组标记
     * @author luokai@vinchin.com
     * @return array  资源组信息列表
     */
    public function pGetUserResourceGroup($userUUID, $orderby, $sort, $limit, $count, $flag = false, $search = array(), $createFlag = false){
        
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.description, brg.create_user_uuid from bd_resource_group brg, mt_user_resource_group murg where brg.resource_group_uuid = murg.resource_group_uuid ";
        
        
        $sqlCount = "select count(murg.resource_group_uuid) as total from bd_resource_group brg, mt_user_resource_group murg where brg.resource_group_uuid = murg.resource_group_uuid ";
        
        $sqlParams = array();
        $sqlCountParams = array();
        if(!$flag){
            $sql .= " and murg.user_uuid = ? ";
            $sqlCount .= " and murg.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($userUUID));
            $sqlCountParams = array_merge($sqlCountParams, array($userUUID));
        }
        
        //分配资源组只能分配自己创建的
        if($createFlag){
            $sql .= " and brg.create_user_uuid = ? ";
            $sqlCount .= " and brg.create_user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($userUUID));
            $sqlCountParams = array_merge($sqlCountParams, array($userUUID));
        }
        
        if(!empty($search)){
            $name = $search['name'];
            $sql .= " and  brg.resource_group_name like '%". $name ."%'";
            $sqlCount .= " and  brg.resource_group_name like '%". $name ."%'";
        }
        
        
        $sql .= " order by $orderby $sort limit ?, ?";
        $sqlParams = array_merge($sqlParams, array($limit, $count));
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $info = array(
            'total' => $dataCount[0]['total'],
            'data' => $data
        );
        return $info;
    }
    
    /**
     * 通过用组UUID获取资源组列表
     * @param string $userGroupUUID 用户组唯一标识
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * @author luokai@vinchin.com
     * @return array  资源组信息列表
     */
    public function pGetUserGroupResourceGroup($userGroupUUID, $orderby, $sort, $limit, $count, $flag = false){
        
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.description from bd_resource_group brg, mt_user_group_resource_group mugrg where brg.resource_group_uuid = mugrg.resource_group_uuid ";
        
        
        $sqlCount = "select count(mugrg.resource_group_uuid) as total from bd_resource_group brg, mt_user_group_resource_group mugrg where brg.resource_group_uuid = mugrg.resource_group_uuid ";
        
        $sqlParams = array($limit, $count);
        $sqlCountParams = array();
        if(!$flag){
            $sql .= " and mugrg.user_group_uuid = ? ";
            $sqlCount .= " and mugrg.user_group_uuid = ? ";
            $sqlParams = array($userGroupUUID, $limit, $count);
            $sqlCountParams = array($userGroupUUID);
        }
        $sql .= " order by brg.$orderby $sort limit ?, ?";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $info = array(
            'total' => $dataCount[0]['total'],
            'data' => $data
        );
        return $info;
    }
    
    
    /**
     * 检查删除虚拟机是否被任务使用
     * @param string $useruuid
     * @param array $resourceuuids
     */
    private function checkDeleteUserVM($useruuid, $resourceuuids){
        $resourceDes = implode("','", $resourceuuids);
        $sql = "select vml.machine_id from bd_task bt, vm_machine_list vml where bt.task_uuid = vml.task_uuid and bt.task_type = ? and bt.user_uuid = ? and vml.vm_uuid in ('".$resourceDes."')";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKTYPE']['BACKUP'],$useruuid));
        if(!empty($data)){
            exit($this->apiResponse(false, "API_CODE_RESOURCE_SELECT_USING_ERROR"));
        }
        return;
    }
    
    /**
     * 检查删除存储是否被任务使用
     * @param string $useruuid
     * @param array $resourceuuids
     */
    private function checkDeleteUserStorage($useruuid, $resourceuuids){
        $resourceDes = implode("','", $resourceuuids);
        $sql = "select bt.task_uuid from bd_task bt where bt.user_uuid = ? and bt.storage_uuid in ('".$resourceDes."')";
        $data = $this->dbSelect($sql, array($useruuid));
        if(!empty($data)){
            exit($this->apiResponse(false, "API_CODE_RESOURCE_SELECT_USING_ERROR"));
        }
        return;
    }
    
    /**
     * 检查删除节点是否被任务使用
     * @param string $useruuid
     * @param array $resourceuuids
     */
    private function checkDeleteUserNode($useruuid, $resourceuuids){
        $resourceDes = implode("','", $resourceuuids);
        $sql = "select bt.task_uuid from bd_task bt where bt.user_uuid = ? and bt.node_uuid in ('".$resourceDes."')";
        $data = $this->dbSelect($sql, array($useruuid));
        if(!empty($data)){
            exit($this->apiResponse(false, "API_CODE_RESOURCE_SELECT_USING_ERROR"));
        }
        return;
    }
    
    /**
     * 检查资源组是否已被关联
     * @param int $from 资源组关联去向
     * @param array $resourceList 资源组唯一标识集合
     * @param int $uuid 关联的唯一标识
     */
    private function pCheckResourceGroupExist($from, $resourceList, $uuid){
        $resourceGroupDes = implode("','", $resourceList);
        switch ($from){
            case Xphp::$_config['RESOURCE_FROM']['USER']:   //查询用户与资源组关联
                $sql = "select id from mt_user_resource_group where user_uuid = ? and resource_group_uuid in('".$resourceGroupDes."')";
                break;
            case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']://查询用户组与资源组关联
                $sql = "select id from mt_user_group_resource_group where user_group_uuid = ? and resource_group_uuid in('".$resourceGroupDes."')";
                break;
        }
        $sqlParams = array($uuid);
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            exit($this->apiResponse(false, "API_CODE_RESOURCE_RESOURCE_GROUP_EXIST_ERROR"));
        }
        
        return;
    }
    
    /**
     * 
     * @param int $from 资源关联去向
     * @param array $resourceList 资源唯一标识集合
     * @param int $uuid 关联的唯一标识
     */
    private function pCheckResourceExist($from, $resourceList, $uuid){
        $resourceType = $resourceList[0]['resourceType'];
        $resourceuuids = array();
        foreach ($resourceList as $d){
            $resourceuuids[] = $d['resourceuuid'];
        }
        $resourceuuidsDes = implode("','", $resourceuuids);
        switch ($from){
            case Xphp::$_config['RESOURCE_FROM']['USER']:   //查询用户与资源组关联
                $sql = "select id from mt_user_resource where resource_type = ? and resource_uuid in('".$resourceuuidsDes."') ";
                break;
            case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']://查询用户组与资源组关联
                $sql = "select id from mt_user_group_resource where resource_type = ? and resource_uuid in('".$resourceuuidsDes."') ";
                break;
            case Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP']://查询资源组组与资源组关联
                $sql = "select id from mt_resource_resource_group where resource_type = ? and resource_uuid in('".$resourceuuidsDes."') ";
                break;
        }
        $sqlParams = array($resourceType);
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            exit($this->apiResponse(false, "API_CODE_RESOURCE_EXIST_ERROR"));
        }
        
        return;
    }
    
}