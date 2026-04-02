<?php
/******************************************* 
** 租户管理处理类
** 
** @author       luokai@vinchin.com 
** @date         2022-04-14 
** @version      1.0.0 
** @copyright    Copyright 2022 vinchin.com 
********************************************/
class APITenantHandler extends OPHandler{
    
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/tenant" => array(
            'POST' => 'createTenant',   //创建租户(单)
            'PUT' => 'editTenant',      //修改租户(单)
            'DELETE' => 'deleteTenant', //删除租户(单)
            'GET' => 'getTenantInfo'    //获取单个租户详细信息(单)
        ),
        "/tenant/lock" => array(
            'POST' => 'lockTenant'      //禁用租户(单)
        ),
        "/tenant/unlock" => array(
            'POST' => 'unlockTenant'    //启用租户(1-n)
        ),
        "/tenant/auth" => array(
            'POST' => 'authTenant'    //配置租户授权(单)
        ),
        "/tenant/vm_host" => array(
            'POST' => 'setTenantVmHost'    //配置租户虚拟机恢复目标宿主机(单)
        ),
        "/tenant/users" => array(
            'GET' => 'getTenantUsers'      //获取租户下的用户列表(1-n)
        ),
        "/tenant/usergroups" => array(
            'GET' => 'getTenantUserGroups' //获取租户下的用户组篱笆(1-n)
        ),
        "/tenant/resourcegroups" => array(
            'GET' => 'getTenantResourceGroups'  //获取租户下的资源组列表(1-n)
        ),
        "/tenant/lists" => array(   
            'GET' => 'getTenantLists'      //获取租户列表(1-n)
        )
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 创建租户路由控制
     */
    protected function createTenant(){
        //定义方法版本
        $version = array(
            "v1" => "createTenantV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改租户路由控制
     */
    protected function editTenant(){
        //定义方法版本
        $version = array(
            "v1" => "editTenantV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除租户路由控制
     */
    protected function deleteTenant(){
        //定义方法版本
        $version = array(
            "v1" => "deleteTenantV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取租户信息(单个)路由控制
     */
    protected function getTenantInfo(){
        //定义方法版本
        $version = array(
            "v1" => "getTenantInfoV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 禁用租户路由控制
     */
    protected function lockTenant(){
        //定义方法版本
        $version = array(
            "v1" => "lockTenantV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启用租户路由控制
     */
    protected function unlockTenant(){
        //定义方法版本
        $version = array(
            "v1" => "unlockTenantV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 租户授权路由控制
     */
    protected function authTenant(){
        //定义方法版本
        $version = array(
            "v1" => "authTenantV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 配置租户虚拟机恢复目标宿主机路由控制
     */
    protected function setTenantVmHost(){
        //定义方法版本
        $version = array(
            "v1" => "setTenantVmHostV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取租户下的用户列表路由控制
     */
    protected function getTenantUsers(){
        //定义方法版本
        $version = array(
            "v1" => "getTenantUsersV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取租户下的用户组路由控制
     */
    protected function getTenantUserGroups(){
        //定义方法版本
        $version = array(
            "v1" => "getTenantUserGroupsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取租户下的资源组列表路由控制
     */
    protected function getTenantResourceGroups(){
        //定义方法版本
        $version = array(
            "v1" => "getTenantResourceGroupsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取租户列表(多个)路由控制
     */
    protected function getTenantLists(){
        //定义方法版本
        $version = array(
            "v1" => "getTenantListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**********createTenant**********/
    private function createTenantV1(){
        //TODO
        $tenantname = $this->params['tenant_name'];
        $this->checkTenantNameExist($tenantname);
        $nickname = $this->params['nickname'];
        $username = $this->params['user_name'];
        //检查租户名和用户名和密码是否符合规范
        if(!preg_match('/^\w+$/', $tenantname) || !preg_match('/^\w+$/', $username)){
            exit($this->apiResponse(false, "API_CODE_TENANT_INPUT_STANDARD_ERROR"));
        }
        $password = md5($this->params['password']);
        $email = !empty($this->params['email'])?$this->params['email']:"";
        $createUseruuid = Xphp::$_user['useruuid'];
        $createUsername = Xphp::$_user['username'];
        //检查参数是否为空
        $this->apiParamsCheck($tenantname, $nickname, $username, $password);


        //默认给租户管理员管理租户内所有数据权限
        $config = json_encode(array(
            'common' => array(
                'datamanage' => true
            )
        ));
        
        $utils = Xphp::instance('Utils');
        //生成tenantuuid
        $tenantuuid = $utils->uuid();
        //创建管理员用户
        $adminuuid = $this->addTenantAdminUser($tenantname, $username,$password, $email,$tenantuuid);
        
        
        //创建默认用户组
        //$groupList = array('Tenant Admin', 'Tenant Operator', 'Tenant Auditor');
        // 改为根据role_uuid 分别查出对应的role_name
        $groupList = $this->dbSelect("SELECT role_name FROM bd_role WHERE role_uuid in ('eee859d5-341a-b95a-33d0-6ed583d0f7a1','2b214439-8f0b-ec23-a3be-2014ad9baecc','03e12c93-9dc1-371a-6a64-b74965d36723')", []);
        foreach ($groupList as $groupname){
            //依次创建默认用户组
            $newgroupname = $tenantname."\\".$groupname['role_name'];
            $this->addTenantAdminUsergroup($tenantuuid, $newgroupname, $adminuuid, $groupname['role_name'], $username);
        }
        $createTime = date('Y-m-d H:i:s');
        $sqlParams = array($tenantuuid, $tenantname, $nickname, $createTime, $adminuuid, $config, Xphp::$_config['FLAG']['SET'], $createUseruuid, $createUsername);
        $sql = "insert into bd_tenant (tenant_uuid, tenant_name, nick_name, create_time, admin_uuid, config, lock_flag, create_user_uuid,create_user_name)
                values (?, ?, ?, ?, ?, ?, ?,?,?)";
        $result = $this->dbExec($sql, $sqlParams);
        
        if($result){
            //添加成功返回租户uuid
            $data['tenant_uuid'] = $tenantuuid;
            $data['user_uuid'] = $adminuuid;
            return $this->apiResponse(true, "API_CODE_TENANT_ADD", $data, array());
        }else{
            return $this->apiResponse(false, "API_CODE_TENANT_ADD", "", "warning");
        }
    }
    
    /**********editTenant**********/
    private function editTenantV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        $nickname = $this->params['nickname'];
        $useruuid = $this->params['user_uuid'];
        $password = $this->params['password'];
        $email = !empty($this->params['email'])?$this->params['email']:"";
        $utils = Xphp::instance('Utils');
        $createTime = date('Y-m-d H:i:s');
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid, $nickname, $useruuid, $password);
        $this->dbBeginTransaction(); //事务
        //更新bd_user那张表
        $sqlUser = "update bd_user set password = ?, create_time = ?, email = ? where user_uuid = ? ";
        $sqlUserParams = array(md5($password), $createTime, $email, $useruuid);
        $result = $this->dbExec($sqlUser, $sqlUserParams);
        
        $sqlParams = array($nickname, $createTime, $tenantuuid);
        $sql = "update bd_tenant set nick_name = ?, create_time = ? where tenant_uuid = ?";
        $result = $result && $this->dbExec($sql, $sqlParams);
        
        if($result){
            $this->dbCommit();
        }else{
            $this->dbRollBack();
        }
        
        return $this->apiResponse($result, "API_CODE_TENANT_EDIT");
    }
    
    
    /**********deleteTenant**********/
    private function deleteTenantV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid);
        //获取租户下所有用户、用户组、角色
        $userList = $this->getTenantAllUser($tenantuuid);
        $userGroupList = $this->getTenantAllUserGroup($tenantuuid);
        $roleInfo = $this->getTenantAllRole($tenantuuid);
        $roleList = $roleInfo['roleList'];
        $permissionList = $roleInfo['permissionList'];
        $resourceGroupList = $this->getTenantAllResourceGroup($tenantuuid);
        //首先清空删除租户日志信息
        $this->cleanTenantLog();
        
        //删除前先检查租户内是否有备份数据
        $this->checkIfTimepointExist($userList);
        
        //停止租户上所有任务
        $this->checkTaskIfRunning($userList);
        
        //开始事务
        $this->dbBeginTransaction();
        $result = true;
//         //删除用户:用户的任务(强制停止,然后再删除),用户的告警,用户的日志, 租户用户关联
//         $result = $result && $this->deleteTenantAllJob($userList);
        
//         //删除用户的告警和日志
//         $result = $result && $this->deleteTenantLogAndAlarm($userList);
        
        
//         //删除租户内所有的数据库代理
//         $result = $result && $this->deleteTenantAllDbAgent($userList);
        
        //删除用户
        $result = $result && $this->deleteTenantAllUser($tenantuuid, $userList);
        
        //删除用户组
        $result = $result && $this->deleteTenantAllUserGroup($tenantuuid, $userGroupList);
        
        //删除角色
        $result = $result && $this->deleteTenantAllRole($tenantuuid, $roleList, $permissionList);
        
        //删除资源组
        $result = $result && $this->deleteTenantAllResourceGroup($tenantuuid, $resourceGroupList);
        
        //删除租户的域服务器
        $result = $result && $this->deleteTenantAllDomainServer($tenantuuid);
        
        //删除租户的组织结构
        $result = $result && $this->deleteTenantAllOrganization($tenantuuid);
        
        //停止计费 删除租户所有计费
        $result = $result && $this->deleteTenantAllBilling($tenantuuid);
        
        //删除租户
        $sql = "delete from bd_tenant where tenant_uuid = ? ";
        $result = $result && $this->dbExec($sql, array($tenantuuid));
        if($result){
            //成功删除租户
            $this->writeTenantLog("UI_DELETE_TENANT_LOG20_1");
            $this->dbCommit();
        }else{
            //删除租户失败，回滚操作
            $this->writeTenantLog("UI_DELETE_TENANT_LOG20_0");
            $this->dbRollBack();
        }
        return $this->apiResponse($result, "API_CODE_TENANT_DELETE");
    }
    
    
    /**********getTenantInfo**********/
    private function getTenantInfoV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid);
        $info = array();
        //基本信息
        $basicInfo = $this->getTenantBasicInfo($tenantuuid);
        $highInfo = json_decode($this->getTenantHighSettings(array('tenantuuid' => $tenantuuid)), true);
        //授权信息
        $authInfo= array(
            'auth_type' => intval($highInfo['common']['authtype']),
            'vm_num' => intval($highInfo['common']['vm']),
            'fs_num' => intval($highInfo['common']['fs']),
            'db_num' => intval($highInfo['common']['db']),
            'quota_type' => intval($highInfo['common']['quotatype']),
            'quota_size' => intval($highInfo['common']['quotaSize'])
        );
        //宿主机信息
        $hostInfo = array(
            'host_type' => intval($highInfo['recover']['hosttype']),
            'host_uuid' => !empty($highInfo['recover']['host'])?$highInfo['recover']['host']: "",
            'vcenter_uuid' => !empty($highInfo['recover']['vcenter'])?$highInfo['recover']['vcenter']: "",
        );
        //计费策略信息
        $billingInfo = array(
            'billing_name' => $highInfo['billing']['name'],
            'billing_uuid' => $highInfo['billing']['billinguuid']
        );
        $info = array(
            'basic_info' => $basicInfo, 
            'auth_info' => !empty($highInfo['common'])?$authInfo:array(), 
            'host_info' => !empty($highInfo['recover'])?$hostInfo:array(), 
            'billing_info' => !empty($highInfo['billing'])?$billingInfo:array(), 
        );
        
        return $this->apiResponse(true, "API_CODE_TENANT_GET_INFO", $info);
    }
    
    
    /**********lockTenant**********/
    private function lockTenantV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid);
        //获取租户下所有用户
        $userList = $this->getTenantAllUser($tenantuuid);
        //停止租户上所有任务
        $this->checkTaskIfRunning($userList);
        $sql = "update bd_tenant set lock_flag = ? where tenant_uuid = ? ";
        $result = $this->dbExec($sql, array(Xphp::$_config['FLAG']['UNSET'], $tenantuuid));
        return $this->apiResponse($result, "API_CODE_TENANT_LOCK");
    }
    
    
    /**********unlockTenant**********/
    private function unlockTenantV1(){
        //TODO
        $tenantList = $this->params['tenant_uuid_list'];
        //检查参数是否为空
        $this->apiParamsCheck($tenantList);
        $tenantStr = implode("','", $tenantList);
        $sql = "update bd_tenant set lock_flag = ? where tenant_uuid in ('".$tenantStr."')";
        $result = $this->dbExec($sql, array(Xphp::$_config['FLAG']['SET']));
        return $this->apiResponse($result, "API_CODE_TENANT_UNLOCK");
    }
    
    
    /**********authTenant**********/
    private function authTenantV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        $authType = intval($this->params['auth_type']); //授权类型
        $quotaType = !empty($this->params['quota_type'])? intval($this->params['quota_type']) : 0;
        $quotaSize = intval($this->params['quota_size']); //配额大小
        $vmNum = intval($this->params['vm_num']);
        $fsNum = intval($this->params['fs_num']);
        $dbNum = intval($this->params['db_num']);
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid, $authType);
        $utils = Xphp::instance('Utils');
        $quotaList = $utils->calSizeToValueAndUnit($quotaSize, true);
        $oldSettings = $this->getTenantHighSettings(array('tenantuuid' => $tenantuuid));
        $oldSettings = json_decode($oldSettings, true);
        $common = array(
            "datamanage" => true,//备份数据管理默认开启
            "authtype" => $authType,
            "authtypedes" => $authType == 1 ? "按容量授权":"按数量授权",
            "vm" => $vmNum,
            "fs" => $fsNum,
            "db" => $dbNum,
            "quotatype" => $quotaType,
            "quotatypedes" => $quotaType == 1 ? "指定配额":"无限制",
            "quota" => $quotaSize > 0 ? $quotaList['value']: "0",
            "quotaunit" =>  $quotaSize > 0 ? $quotaList['unit']: "GB",
            "quotaSize" => $quotaSize
        );
        $oldSettings['common'] = $common;
        //检查授权
        $this->checkTenantAuthInfo($common, $tenantuuid);
        //按数量授权才处理
        if($common['authtype'] == 2){
            //授权个数减少，清空对应模块授权
            $this->checkTenantAuthReduce($common, $tenantuuid);
        }
        $settings = json_encode($oldSettings);
        $sql = "update bd_tenant set config = ? where tenant_uuid = ?";
        $result = $this->dbExec($sql, array($settings, $tenantuuid));
        
        return $this->apiResponse($result, "API_CODE_TENANT_SET_AUTH");
    }
    
    
    /**********setTenantVmHost**********/
    private function setTenantVmHostV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        $hostType = intval($this->params['host_type']); //主机类型
        $hostuuid = !empty($this->params['host_uuid'])?$this->params['host_uuid']: ""; //宿主机uuid
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid, $hostType);
        $hostInfo = Xphp::instance('APIVcentersHandler', 'pGetSelectHostInfo', $hostuuid);    //获取宿主机信息
        if($hostType == 1 && empty($hostInfo)){
            //指定宿主机信息获取为空
            exit($this->apiResponse(false, "API_CODE_TENANT_VM_HOST_NOT_EXIST"));
        }
        $vcenteruuid = $hostInfo['vcenter_uuid']; //虚拟化中心
        $oldSettings = $this->getTenantHighSettings(array('tenantuuid' => $tenantuuid));
        $oldSettings = json_decode($oldSettings, true);
        $recover = array(
            "hosttype"=> $hostType,
            "hosttypedes"=> $hostType == 1?"指定宿主机":"全部宿主机",
            "host"=> $hostuuid,
            "hostname"=> $hostInfo['host_ip']."(".Xphp::$_config['VMHYPERVISORDES'][intval($hostInfo['hypervisor_type'])].")",
            "vcenter"=> $vcenteruuid,
            "desstoragetype"=> "0",
            "desstoragedes"=> "全部存储",
            "networktype"=> "0",
            "networktypedes"=> "全部网络"
            
        );
        $oldSettings['recover'] = $recover;
        $settings = json_encode($oldSettings);
        $sql = "update bd_tenant set config = ? where tenant_uuid = ?";
        $result = $this->dbExec($sql, array($settings, $tenantuuid));
        
        return $this->apiResponse($result, "API_CODE_TENANT_SET_VM_HOST");
    }
    
    
    /**********getTenantUsers**********/
    private function getTenantUsersV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $utils = Xphp::instance('Utils');
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid, $count);
        $userList = $this->pGetTenantAllUser($tenantuuid);
        $info = array();
        foreach ($userList as $d){
            $info[] = array(
                'user_uuid' => $d['user_uuid'],
                'user_name' => $d['user_name']
            );
        }
        $info = $utils->arraySort($info, "user_uuid", "desc", $begin, $count);
        $data = array(
            "total" => count($userList),
            "begin" => $begin,
            "count" => $count,
            "records" => $info
        );
        return $this->apiResponse(true, "API_CODE_TENANT_GET_USER_LIST", $data);
    }
    
    
    /**********getTenantUserGroups**********/
    private function getTenantUserGroupsV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $utils = Xphp::instance('Utils');
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid, $count);
        $userGroupList = $this->pGetTenantAllUserGroup($tenantuuid);
        $info = array();
        foreach ($userGroupList as $d){
            $info[] = array(
                'user_group_uuid' => $d['user_group_uuid'],
                'user_group_name' => $d['user_group_name']
            );
        }
        $info = $utils->arraySort($info, "user_group_uuid", "desc", $begin, $count);
        $data = array(
            "total" => count($userGroupList),
            "begin" => $begin,
            "count" => $count,
            "records" => $info
        );
        return $this->apiResponse(true, "API_CODE_TENANT_GET_USER_GROUP_LIST", $data);
    }
    
    
    /**********getTenantResourceGroups**********/
    private function getTenantResourceGroupsV1(){
        //TODO
        $tenantuuid = $this->params['tenant_uuid'];
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $utils = Xphp::instance('Utils');
        //检查参数是否为空
        $this->apiParamsCheck($tenantuuid, $count);
        $resourceGroupList = $this->pGetTenantAllResourceGroup($tenantuuid);
        $info = array();
        foreach ($resourceGroupList as $d){
            $info[] = array(
                'resource_group_uuid' => $d['resource_group_uuid'],
                'resource_group_name' => $d['resource_group_name'],
                'description' => $d['description'],
            );
        }
        $info = $utils->arraySort($info, "resource_group_uuid", "desc", $begin, $count);
        $data = array(
            "total" => count($resourceGroupList),
            "begin" => $begin,
            "count" => $count,
            "records" => $info
        );
        return $this->apiResponse(true, "API_CODE_TENANT_GET_RESOURCE_GROUP_LIST", $data);
    }
    
    
    /**********getTenantLists**********/
    private function getTenantListsV1(){
        //TODO
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $this->apiParamsCheck($count);
        $sql = "select tenant_uuid, tenant_name, nick_name, admin_uuid, lock_flag, unix_timestamp(create_time) as create_time, create_user_name, create_user_uuid
                from bd_tenant ";
        $sqlCount = "select count(id) as total from bd_tenant ";
        $sqlParams = array();
        $sqlCountParams = array();
        
        
        $sql .= " limit ?, ? ";
        $sqlParams = array_merge($sqlParams,array($begin, $count));
        $tenants = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $records = array();
        foreach ($tenants as $d){
            
            $records[] = array(
                'tenant_uuid' => $d['tenant_uuid'],
                'tenant_name' => $d['tenant_name'],
                'nickname' => $d['nick_name'],
                'lock_flag' => intval($d['lock_flag']),
                'create_time' => intval($d['create_time']),
                'admin_uuid' => $d['admin_uuid'],
                'create_user_uuid' => $d['create_user_uuid'],
                'create_user_name' => $d['create_user_name']
            );
        }
        
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_TENANT_GET_LIST", $data);
    }
    
    
    
    /**********************************其他工具方法************************************/
    
    /**
     * 创建租户管理员
     * @param string $tenantname
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $tenantuuid
     */
    private function addTenantAdminUser($tenantname, $username,$password, $email, $tenantuuid){
        
        $userName = $username;
        //1本地用户 2外部用户
        $userType = Xphp::$_config['FLAG']['SET'];
        $permission = "";
        $createTime = date("Y-m-d H:i:s");
        $createUserName = Xphp::$_user['username'];
        $createUserUUID = Xphp::$_user['useruuid'];
        $language = Xphp::$_config['lang'];
        //生成一个UUID
        $utils = Xphp::instance("Utils");
        $userUUID = $utils->uuid();
        $quota = -1;
        $sqlExtentionParams =array($userUUID,$quota);
        $sqlParam = array($userUUID, $userName, $password, $email, $userType, $permission,
            $createTime, $createUserName, $createUserUUID, $language, 1);
        $sql = "insert bd_user (user_uuid, user_name, password, email, user_type, permission,
            create_time, create_user_name, create_user_uuid, language, user_level ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlExtention = "insert bd_user_extension (user_uuid,quota) values (?, ?)";
        parent::dbBeginTransaction(); //事务
        $result = parent::dbQuery($sql, $sqlParam);
        $result = $result && parent::dbQuery($sqlExtention, $sqlExtentionParams);
        if($result){
            $apiUserHandler = Xphp::instance('APIUsersHandler');
            //添加用户和租户关系
            $apiUserHandler->pAddUserTenant(array($userUUID), $tenantuuid);
            parent::dbCommit();
        }else{
            parent::dbRollBack();
        }
        
        if(!$result){
            exit($this->apiResponse(false, "API_CODE_TENANT_ADD", "", "warning"));
        }
        
        return $userUUID;
    }
    
    /**
     * 创建租户新增默认用户组
     * @param string $tenantuuid
     * @param string $newgroupname
     * @param string $adminuuid
     * @param string $groupname
     * @param string $adminname
     */
    private function addTenantAdminUsergroup($tenantuuid, $newgroupname, $adminuuid, $groupname, $adminname){
        //生成userGroup的uuid
        $utils = Xphp::instance("Utils");
        $userGroupUUID = $utils->uuid();
        //1为默认组不可删除，2为全局组,初始化创建，3为租户组，这里创建默认为3
        $type = Xphp::$_config['USER_GROUP_TYPE']['TENANT'];
        //默认上锁为启用状态1（2为禁用）
        $lockFlag = Xphp::$_config['FLAG']['SET'];
        //其他配置
        $config = '';
        $createTime = date('Y-m-d H:i:s');
        $params_user_group =array($userGroupUUID,$newgroupname,$type,$lockFlag,$adminuuid, $adminname, $createTime);
        $sql_user_group ="insert into bd_user_group(user_group_uuid,user_group_name,user_group_type,lock_flag, create_user_uuid, create_user_name, create_time) values (?,?,?,?,?,?,?)";
        $result_user_group = $this->dbExec($sql_user_group,$params_user_group);
        if($result_user_group){
            //插入数据到关联表
            $apiUserHandler = Xphp::instance('APIUsersHandler');
            $apiRoleHandler = Xphp::instance('APIRolesHandler');
            $apiUserHandler->pAddUserGroupTenant(array($userGroupUUID),$tenantuuid);
            // 这里根据role_uuid查询出role_name
            $tenentAdmin = $this->dbSelect("SELECT role_name FROM bd_role WHERE role_uuid = 'eee859d5-341a-b95a-33d0-6ed583d0f7a1'", []);
            if (!empty($tenentAdmin) && $groupname == $tenentAdmin[0]['role_name']) {
            //if($groupname == "Tenant Admin"){
                $apiUserHandler->pAddUserGroupUser($userGroupUUID, array($adminuuid));
            }
            //添加用户组和角色关联
            $roleUUID = $apiRoleHandler->pGetRoleuuidByName($groupname);
            if(!empty($roleUUID)){
                //添加租户管理员和管理员角色关联
                if (!empty($tenentAdmin) && $groupname == $tenentAdmin[0]['role_name']) {
                // if($groupname == "Tenant Admin"){
                    $apiRoleHandler->pAddUserRole($adminuuid, array($roleUUID));
                }
                //添加用户组与对应角色关联
                $apiRoleHandler->pAddUserGroupRole($userGroupUUID, array($roleUUID));
            }
            return true;
        }else{
            exit($this->apiResponse(false, "API_CODE_TENANT_ADD", "", "warning"));
        }
    }
    
    /**
     * 检查租户名是否重复
     * @param string $tenantname
     */
    private function checkTenantNameExist($tenantname){
        $sql = "select tenant_uuid from bd_tenant where tenant_name = ? ";
        $data = $this->dbSelect($sql, array($tenantname));
        if(!empty($data)){
            exit($this->apiResponse(false, "API_CODE_TENANT_EXIST_NAME_ERROR"));
        }
        
        return true;
    }
    
    /**
     * 获取租户所有用户组
     * @param string $tenantuuid
     * @return unknown[]
     */
    private function getTenantAllUser($tenantuuid){
        $list = array();
        $sql = "select bu.user_uuid, bu.user_name from bd_user bu, bd_tenant bt where (bu.create_user_uuid = bt.admin_uuid or bu.user_uuid = bt.admin_uuid) and bt.tenant_uuid = ? ";
        $userInfo = $this->dbSelect($sql, array($tenantuuid));
        if(!empty($userInfo)){
            foreach ($userInfo as $user){
                $list[] = $user['user_uuid'];
            }
        }
        
        return $list;
    }
    
    /**
     * 检查并停止当前租户所有任务
     * @param array $userList
     * @return void|boolean
     */
    private function checkTaskIfRunning($userList){
        if(empty($userList)) return true;
        $userDes = implode("','", $userList);
        $stopFlag = false;
        $apiJobHandler = Xphp::instance('APIJobsHandler');
        $i = 0;
        while(!$stopFlag){
            $sql = "select task_uuid, task_type, module_type, task_status from bd_task where task_status != ? and user_uuid in ('".$userDes."')";
            $data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['STOPPED']));
            $t1 = time();
            if(!empty($data)){
                //循环去停止任务
                foreach ($data as $d){
                    $subModule = $apiJobHandler->pGetTaskSubmodule($d['task_uuid'], $d['module_type'], $d['task_type']);
                    $params = array(
                        "uuid" => $d['task_uuid'],
                        "module" => $d['module_type'],
                        "taskType" => $d['task_type'],
                        "subModule" => $subModule,
                        "status" => $d['task_status'],
                        "tenantuuid" => $_SESSION['tenantuuid']
                        
                    );
                    $this->pStopJob($params, $apiJobHandler);
                }
                $t2 = time();
                //间隔10秒检查
                sleep(2+($t2-$t1));
                $i++;
                //尝试10次停止任务后，直接失败退出
                if($i == 10){
                    exit($this->apiResponse(false, "API_CODE_TENANT_STOP_JOB_ERROR"));
                }
            }else{
                $stopFlag = true;
                break;
            }
            
        }
        
        //stop all jobs;
        $this->writeTenantLog("UI_DELETE_TENANT_LOG2_1");
        return true;
        
    }
    
    public function pStopJob($info, $apiJobHandler){
        $taskType = $info['taskType'];
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        }elseif($taskTypeConf['RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }elseif($taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType){
            //停止瞬时恢复任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_INSTANT_RECOVERY_TASK';
        }elseif($taskTypeConf['VM_FILE_RECOVERY'] == $taskType){
            //停止细粒度任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_GRAIN_RECOVERY_TASK';
        }elseif($taskTypeConf['VM_INSTANT_RECOVERY_MOTION'] == $taskType){
            //停止迁移任务
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }elseif($taskTypeConf['ORCH_TASK'] == $taskType){
            //停止演练任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_ORCH_TASK';
        }elseif($taskTypeConf['VM_CDP_BACKUP'] == $taskType){
            //停止CDP任务
            $opName = 'BD_TASK_OP_CDP_BACKUP_STOP';
        }elseif($taskTypeConf['BACKUP_COPY'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY'] == $taskType || $taskTypeConf['DB_BACKUP_COPY'] == $taskType){
            //停止副本备份任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_STOP';
        }elseif($taskTypeConf['BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY_FETCH'] == $taskType || $taskTypeConf['DB_BACKUP_COPY_FETCH'] == $taskType){
            //停止副本恢复任务
            $opName = 'BD_TASK_OP_BACKUP_COPY_FETCH_STOP';
        }elseif($taskTypeConf['ARCHIVE'] == $taskType){
            //停止归档任务
            $opName = 'BD_TASK_OP_ARCHIVE_STOP';
        }elseif($taskTypeConf['ARCHIVE_FETCH'] == $taskType){
            //停止归档恢复任务
            $opName = 'BD_TASK_OP_ARCHIVE_FETCH_STOP';
        }elseif($taskTypeConf['DB_BACKUP'] == $taskType){
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        }elseif($taskTypeConf['DB_RECOVERY'] == $taskType){
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }
        
        return $apiJobHandler->opUnifyMsg($info, $opName);
    }
    
    /**
     * 公共方法
     * 获取租户的所有用户
     * @param string $tenantuuid  租户uuid
     * @author xiezhuowei@vinchin.com
     * @return array  用户列表,包含用户部分字段
     */
    public function pGetTenantAllUser($tenantuuid){
        //用户-租户
        $sql = "select distinct bu.user_uuid, bu.user_name from
                bd_user bu, mt_user_tenant mut
                where bu.user_uuid = mut.user_uuid and mut.tenant_uuid = ? ";
        $data1 = $this->dbSelect($sql, array($tenantuuid));
        
        //用户-用户组-租户
        $sql = "select distinct bu.user_uuid, bu.user_name from
                bd_user bu, mt_user_user_group muug, mt_user_group_tenant mugt
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugt.user_group_uuid and mugt.tenant_uuid = ? ";
        $data2 = $this->dbSelect($sql, array($tenantuuid));
        
        //合并数据
        $data = array_merge($data1, $data2);
        $utils = Xphp::instance('Utils');
        $data = $utils->unique_multidim_array($data, "user_uuid");
        return $data;
    }
    
    /**
     * 公共方法
     * 获取租户的所有用户组
     * @param string $tenantuuid  租户uuid
     * @author xiezhuowei@vinchin.com
     * @return array  用户组列表,包含bd_user_group表内容
     */
    public function pGetTenantAllUserGroup($tenantuuid){
        
        $sql = "select distinct bug.user_group_uuid, bug.user_group_name, bug.user_group_type, bug.lock_flag, bug.description, bug.config from
                bd_user_group bug, mt_user_group_tenant mugt
                where bug.user_group_uuid = mugt.user_group_uuid and mugt.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        return $data;
    }
    
    /**
     * 公共方法
     * 获取租户的所有资源组
     * @param string $tenantuuid  租户uuid
     * @author xiezhuowei@vinchin.com
     * @return array  用户组列表,包含bd_resource_group表内容
     */
    public function pGetTenantAllResourceGroup($tenantuuid){
        
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.config, brg.description from
                mt_resource_group_tenant mrgt, bd_resource_group brg
                where mrgt.resource_group_uuid = brg.resource_group_uuid and mrgt.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        return $data;
    }
    
    /**
     * 获取租户高级配置信息
     * @param unknown $params
     */
    public function getTenantHighSettings($params){
        $tenantuuid = $params['tenantuuid'];
        $settings = $this->pGetTenantSettings($tenantuuid);
        $authInfo = $this->getTenantAuthInfo($tenantuuid, $settings['common'] );
        $settings['common'] =  array_merge($settings['common'], $authInfo);
        $settings['billing'] = $this->pGetTenantBillingStrategy($tenantuuid);
        return json_encode($settings);
        
    }
    
    /**
     * 公共方法
     * 获取租户计费策略信息
     * @param string $tenantuuid
     * @return boolean[]|string[]|fetchAll()[]
     */
    public function pGetTenantBillingStrategy($tenantuuid){
        $sql = "select bb.name,bb.billing_uuid,bb.lock_flag from bd_billing bb, mt_tenant_billing mtb where bb.billing_uuid = mtb.billing_uuid and mtb.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        
        $info = array();
        $billingCheck = false;
        $billingName = Xphp::$_config['NULLSPACE'];
        $uuid = "";
        if(!empty($data)){
            if($data[0]['lock_flag'] ==1){
                $billingCheck = true;
            }else{
                $billingCheck = false;
            }
            $billingName = $data[0]['name'];
            $uuid = $data[0]['billing_uuid'];
        }
        
        $info = array(
            'flag' => $billingCheck,
            'name' => $billingName,
            'billinguuid' => $uuid
        );
        
        return $info;
    }
    
    /**
     * 公共方法
     * 获取租户对应的高级配置信息
     * @param string $tenantuuid
     * @author luokai@vinchin.com
     * @return array|mixed
     */
    public function pGetTenantSettings($tenantuuid){
        $sql = "select config from bd_tenant where tenant_uuid = ?";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $settings = array();
        if(!empty($data)){
            $settings = json_decode($data[0]['config'], true);
        }
        return $settings;
    }
    
    /**
     * 检查租户授权信息
     * @param unknown $info
     */
    private function checkTenantAuthInfo($info, $tenantuuid){
        $sql = "select unix_timestamp(register_time) register_time, license_type, vm_max_num, cpu_count, storage_count, node_count, file_max_num, oracle_max_num,
                days, trial_type, software_type, user_name, extension from bd_license";
        $data = $this->dbSelect($sql);
        
        $tenantAuthInfo = $this->getAllTenantAuthInfo($tenantuuid);
        
        //租户内授权类型
        $authType = intval($info['authtype']);
        //授权类型
        $licenseType = intval($data[0]['license_type']);
        $vmNum = intval($data[0]['vm_max_num']);
        $fsNum = intval($data[0]['file_max_num']);
        $dbNum = intval($data[0]['oracle_max_num']);
        $storageSize = intval($data[0]['storage_count']);
        if($licenseType == Xphp::$_config['LISENCE_INFO']['type']['storage']){
            //容量个数授权
            if($authType == 1){
                if($info['quotaSize'] != -1){
                    //租户内按容量
                    $allStorage = $tenantAuthInfo['storage'] + intval($info['quotaSize']);
                    if($allStorage > $storageSize){
                        exit($this->apiResponse(false, "API_CODE_TENANT_SPACE_NOT_ENOUGH_BY_CAPACITY"));
                    }
                }
            }
            
        }else{
            if($licenseType == Xphp::$_config['LISENCE_INFO']['type']['vm']){
                //虚拟机个数授权
                if($authType == 2){
                    //租户内按个数
                    $allVm =  $tenantAuthInfo['vm'] + intval($info['vm']);
                    if($allVm > $vmNum){
                        exit($this->muOpResult(false, "API_CODE_TENANT_VIRTU_MACHINE_NOT_ENOUGH_BY_VM"));
                    }
                }
            }
            
            //检查文件代理和数据库代理授权
            if($authType == 2){
                //租户内按个数
                $allFs =  $tenantAuthInfo['fs'] + intval($info['fs']);
                $allDb = $tenantAuthInfo['db'] + intval($info['db']);
                if($allFs > $fsNum){
                    exit($this->muOpResult(false, "API_CODE_TENANT_FSAGENT_NOT_ENOUGH"));
                }
                
                if($allDb > $dbNum){
                    exit($this->muOpResult(false, "API_CODE_TENANT_DBAGENT_NOT_ENOUGH"));
                }
            }
        }
        
    }
    
    /**
     * 获取所有租户授权情况
     * @param string $tenantuuid
     */
    private function getAllTenantAuthInfo($tenantuuid){
        $sql = "select config from bd_tenant where tenant_uuid != ?";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $storageSize = 0;
        $vm = 0;
        $fs = 0;
        $db = 0;
        if(!empty($data)){
            foreach ($data as $d){
                $config = json_decode($d['config'], true);
                $authType = intval($config['common']['authtype']);
                if($authType == 1){
                    //按容量
                    if($config['common']['quotaSize'] != -1){
                        $storageSize += intval($config['common']['quotaSize']);
                    }
                }else if($authType == 2){
                    //按个数
                    $vm += intval($config['common']['vm']);
                    $fs += intval($config['common']['fs']);
                    $db += intval($config['common']['db']);
                }
            }
        }
        
        
        $info = array(
            'storage' => $storageSize,
            'vm' => $vm,
            'fs' => $fs,
            'db' => $db
        );
        
        return $info;
    }
    
    /**
     * 检查数量减少,清空已有授权
     * @param unknown $common
     * @param unknown $tenantuuid
     */
    private function checkTenantAuthReduce($common, $tenantuuid){
        $sql = "select config from bd_tenant where tenant_uuid = ?";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $config = json_decode($data[0]['config'],true);
        $authType = $config['common']['authtype'];
        //还未授权直接清除
        if(empty($authType)) return;
        
        //获取租户内所有用户
        $userList = $this->getTenantAllUser($tenantuuid);
        $userDes = implode("','", $userList);
        //获取所有代理
        $sql ="select agent_uuid, agent_type, hostname from bd_agent where user_uuid in ('".$userDes."')";
        $data = $this->dbSelect($sql);
        //如果没有代理
        if(empty($data)) return;
        $fsList = array();
        $dbList = array();
        foreach ($data as $d){
            if($d['agent_type'] == 1){
                $dbList[] = array(
                    'agentUUID' => $d['agent_uuid'],
                    'hostName' => $d['hostname'],
                    'module' => array(
                        'database' => false
                    )
                );
            }else{
                $fsList[] = $d['agent_uuid'];
            }
        }
        
        $agentHandler = Xphp::instance('AgentHandler');
        //如果以前是容量授权
        if($authType == 1){
            //切换授权后清空所有文件代理和数据库代理授权
            if(!empty($fsList)){
                foreach ($fsList as $fs){
                    $params = array(
                        'agentUUID' => $fs
                    );
                    //取消代理注册和授权
                    $agentHandler->unregistAgent($params);
                }
            }
            
            if(!empty($dbList)){
                foreach ($dbList as $db){
                    //数据库代理授权
                    $agentHandler->registDbAgent($db);
                }
            }
            
        }else if($authType == 2){
            //数量授权
            $oldFs = intval($config['common']['fs']);
            $oldDb = intval($config['common']['db']);
            
            $newFs = intval($common['fs']);
            $newDb = intval($common['db']);
            //文件数量减少
            if($newFs < $oldFs && !empty($fsList)){
                foreach ($fsList as $fs){
                    $params = array(
                        'agentUUID' => $fs
                    );
                    //取消代理注册和授权
                    $agentHandler->unregistAgent($params);
                }
            }
            
            //数据库数量减少
            if($newDb < $oldDb  && !empty($dbList)){
                foreach ($dbList as $db){
                    //数据库代理授权
                    $agentHandler->registDbAgent($db);
                }
            }
            
        }
        
        return true;
    }
    
    /**
     * 获取多租户授权信息
     * @param unknown $tenantuuid
     */
    private function getTenantAuthInfo($tenantuuid, $common){
        //授权方式
        $utils = Xphp::instance("Utils");
        $authType = intval($common['authtype']);
        $storageDes = Xphp::$_config['NULLSPACE'];
        $vmDes = Xphp::$_config['NULLSPACE'];
        $fsDes = Xphp::$_config['NULLSPACE'];
        $dbDes = Xphp::$_config['NULLSPACE'];
        $userList = $this->getTenantAllUser($tenantuuid);
        $userDes = implode("','", $userList);
        $quotaSize = $common['quotaSize'];
        $vmNum = intval($common['vm']);
        $fsNum = intval($common['fs']);
        $dbNum = intval($common['db']);
        $fsUsed = 0;
        $dbUsed = 0;
        if($authType == 1 && $quotaSize != -1){
            //按容量
            $sql = "select sum(write_size) as used_size from bd_backup_timepoint where user_uuid in('".$userDes."') ";
            $data = $this->dbSelect($sql);
            $total = $utils->calSize(intval($quotaSize));
            $used = $utils->calSize(intval($data[0]['used_size']), true);
            $free = $utils->calSize(intval($quotaSize) - intval($data[0]['used_size']));
            $storageDes = Xphp::$_lang['UI_PLATFORM_AUTHOR_CAPACITY'] . $used . " / " . $total;
        }else if($authType == 2){
            //按个数
            $sql = "select count(vml.machine_id) as total from vm_machine_list vml, bd_task bt where bt.task_uuid = vml.task_uuid and bt.user_uuid in('".$userDes."') ";
            $data = $this->dbSelect($sql);
            $vmDes = Xphp::$_lang['UI_PLATFORM_AUTHOR_NUM'] . intval($data[0]['total']) . ' / ' . $vmNum;
            
            //文件代理
            $sql = "select authorization_module from bd_agent where agent_type = 0 and user_uuid in('".$userDes."')";
            $data = $this->dbSelect($sql);
            foreach ($data as $d){
                $config = json_decode($d['authorization_module'], true);
                if($config['file']){
                    $fsUsed++;
                }
            }
            $fsDes = Xphp::$_lang['UI_PLATFORM_AUTHOR_NUM'] . $fsUsed . ' / ' . $fsNum;
            
            //数据库代理
            $sql = "select authorization_module from bd_agent where agent_type = 1 and user_uuid in('".$userDes."')";
            $data = $this->dbSelect($sql);
            foreach ($data as $d){
                $config = json_decode($d['authorization_module'], true);
                if($config['database']){
                    $dbUsed++;
                }
            }
            $dbDes = Xphp::$_lang['UI_PLATFORM_AUTHOR_NUM'] . $dbUsed . ' / ' . $dbNum;
        }
        $info = array(
            'storageDes' => $storageDes,
            'vmDes' => $vmDes,
            'fsDes' => $fsDes,
            'dbDes' => $dbDes
        );
        
        return $info;
    }
    
    /**
     * 获取租户基本信息
     * @param string $tenantuuid
     * @return array|string[]|unknown[]|fetchAll()[]
     */
    private function getTenantBasicInfo($tenantuuid){
        $sql = "select bt.nick_name, bt.tenant_name, unix_timestamp(bt.create_time) create_time, bu.user_name, bu.email from bd_tenant bt, bd_user bu where bt.admin_uuid = bu.user_uuid and bt.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $info = array();
        if(!empty($data)){
            $email = Xphp::$_config['TIMESPACE'];
            if(!empty($data[0]['email'])){
                $email = $data[0]['email'];
            }
            $info = array(
                "nickname" => $data[0]['nick_name'],
                "tenant_name" => $data[0]['tenant_name'],
                "admin_name"  => $data[0]['user_name'],
                "admin_email" => $email,
                "create_time" => date("Y-m-d H:i:s", intval($data[0]['create_time']))
            );
        }
        
        return $info;
    }
    
    /**
     * 写入删除租户日志内容
     * @param string $content
     */
    private function writeTenantLog($content){
        $file = Xphp::$_config['DELETE_TENANT_PATH'];
        if(!file_exists($file)){
            $addFile = "touch ".$file;
            exec($addFile);
        }
        $count = file_put_contents($file, $content."\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * 清空删除租户日志文件内容
     */
    private function cleanTenantLog(){
        $file = Xphp::$_config['DELETE_TENANT_PATH'];
        if(!file_exists($file)){
            $addFile = "touch ".$file;
            exec($addFile);
        }
        $count = file_put_contents($file, "", LOCK_EX);
    }
    
    /**
     * 检查租户是否存在备份数据
     * @param array $userList
     * @return boolean
     */
    private function checkIfTimepointExist($userList){
        if(empty($userList)) return true;
        $userDes = implode("','", $userList);
        $sql = "select bbt.timepoint_uuid, bbt.module_type, bbt.task_type, bsr.node_uuid from bd_backup_timepoint bbt, bd_storage_resource bsr where bbt.storage_uuid = bsr.storage_uuid and bbt.user_uuid in ('".$userDes."') and bbt.available_flag = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']));
        if(!empty($data)){
            exit($this->apiResponse(false, "API_CODE_TENANT_TIMEPOINT_EXIST_ERROR"));
        }
        
        return true;
    }
    
    /**
     * 检查并停止当前租户所有任务
     * @param array $userList
     * @return void|boolean
     */
    private function deleteTenantAllJob($userList){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG3_1");
        if(empty($userList)) return true;
        $userDes = implode("','", $userList);
        $jobHandler = Xphp::instance('JobHandler');
        $sql = "select task_uuid, task_type, module_type, task_status from bd_task where user_uuid in ('".$userDes."')";
        $data = $this->dbSelect($sql);
        $t1 = time();
        if(!empty($data)){
            //循环去删除任务
            foreach ($data as $d){
                $subModule = $jobHandler->pGetTaskSubmodule($d['task_uuid'], $d['module_type'], $d['task_type']);
                $params = array(
                    "uuid" => $d['task_uuid'],
                    "module" => $d['module_type'],
                    "taskType" => $d['task_type'],
                    "subModule" => $subModule,
                    "status" => $d['task_status'],
                    "tenantuuid" => $_SESSION['tenantuuid']
                    
                );
                $jobHandler->deleteJob($params);
            }
            $t2 = time();
            //间隔10秒检查
            sleep(2+($t2-$t1));
        }
        //删除历史任务
        $sql = "delete from bd_history_task where user_uuid in ('".$userDes."')";
        $result = $this->dbExec($sql);
        if($result){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG4_1");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG4_0");
        }
        //delete all jobs;
        return $result;
        
    }
    
    /**
     * 删除租户所有日志和告警
     * @param array $userList
     * @return boolean
     */
    private function deleteTenantLogAndAlarm($userList){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG5_1");
        if(empty($userList)) return true;
        $userDes = implode("','", $userList);
        
        $logHandler = Xphp::instance('LogHandler');
        $alarmHandler = Xphp::instance('AlarmHandler');
        //查询任务日志并删除
        $sqlTaskLog = "select id from bd_task_log where user_uuid in ('".$userDes."')";
        $dataTaskLog = $this->dbSelect($sqlTaskLog);
        $t1 = time();
        if(!empty($dataTaskLog)){
            $info = array();
            foreach ($dataTaskLog as $d){
                $info[] = $d['id'];
            }
            $params = array('id' => $info, 'tenantFlag' => true);
            $logHandler->deleteTaskLog($params);
            $t2 = time();
            //间隔1秒检查
            sleep(1+($t2-$t1));
        }
        
        //查询系统日志并删除
        $sqlSystemLog = "select id from bd_system_log where user_uuid in ('".$userDes."')";
        $dataSystemLog = $this->dbSelect($sqlSystemLog);
        $t3 = time();
        if(!empty($dataSystemLog)){
            $info = array();
            foreach ($dataSystemLog as $d){
                $info[] = $d['id'];
            }
            $params = array('id' => $info, 'tenantFlag' => true);
            $logHandler->deleteSystemLog($params);
            $t4 = time();
            //间隔1秒检查
            sleep(1+($t4-$t3));
        }
        
        //查询任务告警并删除
        $sqlTaskAlarm = "select task_alarm_id from bd_task_alarm where user_uuid in ('".$userDes."')";
        $dataTaskAlarm = $this->dbSelect($sqlTaskAlarm);
        $t5 = time();
        if(!empty($dataTaskAlarm)){
            $info = array();
            foreach ($dataTaskAlarm as $d){
                $info[] = $d['task_alarm_id'];
            }
            $params = array('id' => $info, 'tenantFlag' => true);
            $alarmHandler->deleteTaskAlarm($params);
            $t6 = time();
            //间隔1秒检查
            sleep(1+($t6-$t5));
        }
        
        $this->writeTenantLog("UI_DELETE_TENANT_LOG6_1");
        return true;
    }
    
    /**
     * 检查并删除所有租户内代理
     * @param array $userList
     * @return void|boolean
     */
    private function deleteTenantAllDbAgent($userList){
        if(empty($userList)) return true;
        $userDes = implode("','", $userList);
        $dbProtectHandler = Xphp::instance('DBProtectHandler');
        $sql = "select agent_uuid from bd_agent where agent_type = 1 and user_uuid in ('".$userDes."')";
        $data = $this->dbSelect($sql);
        if(empty($data)) return true;
        $uuidList = array();
        foreach ($data as $d){
            $uuidList[] = $d['agent_uuid'];
        }
        $params = array(
            'uuids' => $uuidList,
            'flag' => true
        );
        $dbProtectHandler->deleteAgent($params);
        
        return true;
    }
    
    /**
     * 删除租户所有用户
     * @param string $tenantuuid
     * @param array $userList
     * @return boolean
     */
    private function deleteTenantAllUser($tenantuuid, $userList){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG7_1");
        //取消租户和所有用户关联
        $result = $this->pDeleteTenantUser($tenantuuid);
        //删除租户创建用户
        
        if(empty($userList)) return $result;
        $userDes = implode("','", $userList);
        //删除用户与用户组关联
        $sql = "delete from mt_user_user_group where user_uuid in ('".$userDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户与角色关联
        $sql = "delete from mt_user_role where user_uuid in ('".$userDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户与资源关联
        $sql = "delete from mt_user_resource where user_uuid in ('".$userDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户与资源组关联
        $sql = "delete from mt_user_resource_group where user_uuid in ('".$userDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户与组织结构关联
        $sql = "delete from mt_user_organization where user_uuid in ('".$userDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户
        $sql = "delete from bd_user where user_uuid in ('".$userDes."')";
        $result = $result && $this->dbExec($sql);
        $des = "error";
        if($result){
            $des = "success";
        }
        if($des == 'error'){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG8_0");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG8_1");
        }
        
        return $result;
    }
    
    /**
     * 删除租户所有用户组
     * @param string $tenantuuid
     * @param array $userGroupList
     * @return boolean
     */
    private function deleteTenantAllUserGroup($tenantuuid, $userGroupList){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG9_1");
        //取消租户和所有用户组关联
        $result = $this->pDeleteTenantUserGroup($tenantuuid);
        //删除租户创建用户
        
        if(empty($userGroupList)) return $result;
        $userGroupDes = implode("','", $userGroupList);
        //删除用户组与用户关联
        $sql = "delete from mt_user_user_group where user_group_uuid in ('".$userGroupDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户组与角色关联
        $sql = "delete from mt_user_group_role where user_group_uuid in ('".$userGroupDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户组与资源关联
        $sql = "delete from mt_user_group_resource where user_group_uuid in ('".$userGroupDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户组与资源组关联
        $sql = "delete from mt_user_group_resource_group where user_group_uuid in ('".$userGroupDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除用户组
        $sql = "delete from bd_user_group where user_group_uuid in ('".$userGroupDes."')";
        $result = $result && $this->dbExec($sql);
        
        $des = "error";
        if($result){
            $des = "success";
        }
        if($des == 'error'){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG10_0");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG10_1");
        }
        return $result;
    }
    
    
    /**
     * 删除租户所有角色
     * @param string $tenantuuid
     * @param array $roleList
     * @param array $permissionList
     * @return boolean
     */
    private function deleteTenantAllRole($tenantuuid, $roleList, $permissionList){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG11_1");
        if(empty($roleList) || empty($permissionList)) return true;
        $roleDes = implode("','", $roleList);
        $permissionDes = implode("','", $permissionList);
        //删除用户组与角色关联
        $sql = "delete from mt_user_role where role_uuid in ('".$roleDes."')";
        $result = $this->dbExec($sql);
        
        //删除用户组与角色关联
        $sql = "delete from mt_user_group_role where role_uuid in ('".$roleDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除角色对应权限
        $sql = "delete from bd_permission where permission_uuid in ('".$permissionDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除角色
        $sql = "delete from bd_role where role_uuid in ('".$roleDes."')";
        $result = $result && $this->dbExec($sql);
        
        $des = "error";
        if($result){
            $des = "success";
        }
        if($des == 'error'){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG12_0");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG12_1");
        }
        return $result;
    }
    
    
    /**
     * 删除租户所有资源组
     * @param string $tenantuuid
     * @param array $resourceGroupList
     * @return boolean
     */
    private function deleteTenantAllResourceGroup($tenantuuid, $resourceGroupList){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG13_1");
        if(empty($resourceGroupList)) return true;
        $resourceGroupDes = implode("','", $resourceGroupList);
        
        //删除用户与资源组关联
        $sql = "delete from mt_user_resource_group where resource_group_uuid in ('".$resourceGroupDes."')";
        $result = $this->dbExec($sql);
        
        //删除用户组与资源组关联
        $sql = "delete from mt_user_group_resource_group where resource_group_uuid in ('".$resourceGroupDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除资源组与资源关联
        $sql = "delete from mt_resource_resource_group where resource_group_uuid in ('".$resourceGroupDes."')";
        $result = $result && $this->dbExec($sql);
        
        //删除资源组
        $sql = "delete from bd_resource_group where resource_group_uuid in ('".$resourceGroupDes."')";
        $result = $result && $this->dbExec($sql);
        
        $des = "error";
        if($result){
            $des = "success";
        }
        if($des == 'error'){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG14_0");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG14_1");
        }
        return $result;
    }
    
    
    /**
     * 删除租户所有组织结构
     * @param string $tenantuuid
     * @return boolean
     */
    private function deleteTenantAllDomainServer($tenantuuid){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG21_1");
        if(empty($tenantuuid)) return true;
        //删除与租户内的组织结构
        $sql = "delete from bd_domain_server where tenant_uuid = ?";
        $result = $this->dbExec($sql, array($tenantuuid));
        $des = "error";
        if($result){
            $des = "success";
        }
        if($des == 'error'){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG19_0");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG19_1");
        }
        return $result;
    }
    
    /**
     * 删除租户所有组织结构
     * @param string $tenantuuid
     * @return boolean
     */
    private function deleteTenantAllOrganization($tenantuuid){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG15_1");
        if(empty($tenantuuid)) return true;
        //删除与租户内的组织结构
        $sql = "delete from bd_organization where tenant_uuid = ?";
        $result = $this->dbExec($sql, array($tenantuuid));
        $des = "error";
        if($result){
            $des = "success";
        }
        if($des == 'error'){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG16_0");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG16_1");
        }
        return $result;
    }
    
    /**
     * 取消租户与计费关联
     * @param string $tenantuuid
     * @return boolean
     */
    private function deleteTenantAllBilling($tenantuuid){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG17_1");
        
        $result = $this->pDelTenantBilling($tenantuuid);
        if($result){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG18_1");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG18_0");
        }
        
        return $result;
    }
    
    /**
     * 获取租户所有用户组
     * @param string $tenantuuid
     * @return unknown[]
     */
    public function getTenantAllUserGroup($tenantuuid){
        $list = array();
        $sql = "select bug.user_group_uuid from bd_user_group bug, bd_tenant bt where bug.create_user_uuid = bt.admin_uuid and bt.tenant_uuid = ? ";
        $userGroupInfo = $this->dbSelect($sql, array($tenantuuid));
        if(!empty($userGroupInfo)){
            foreach ($userGroupInfo as $usergroup){
                $list[] = $usergroup['user_group_uuid'];
            }
        }
        
        return $list;
    }
    
    /**
     * 获取租户所有角色
     * @param string $tenantuuid
     * @return unknown[]
     */
    public function getTenantAllRole($tenantuuid){
        $roleList = array();
        $permissionList = array();
        $roleInfo = $this->pGetTenantAllRole($tenantuuid);
        if(!empty($roleInfo)){
            foreach ($roleInfo as $role){
                $roleList[] = $role['role_uuid'];
                $permissionList[] = $role['permission_uuid'];
            }
        }
        $list = array(
            'roleList' => $roleList,
            'permissionList' => $permissionList
        );
        return $list;
    }
    
    /**
     * 获取租户所有资源组
     * @param string $tenantuuid
     * @return unknown[]
     */
    public function getTenantAllResourceGroup($tenantuuid){
        $list = array();
        $sql = "select resource_group_uuid from bd_resource_group where tenant_uuid = ?";
        $info = $this->dbSelect($sql, array($tenantuuid));
        if(!empty($info)){
            foreach ($info as $d){
                $list[] = $d['resource_group_uuid'];
            }
        }
        return $list;
    }
    
    /**
     * 公共方法
     * 取消租户与所有用户关联
     * @param string $tenantuuid
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pDeleteTenantUser($tenantuuid){
        if(empty($tenantuuid)) return true;
        $sql = "delete from mt_user_tenant where tenant_uuid = ?";
        $result = $this->dbExec($sql, [$tenantuuid]);
        
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 公共方法
     * 取消租户与所有用户组关联
     * @param string $tenantuuid
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pDeleteTenantUserGroup($tenantuuid){
        if(empty($tenantuuid)) return true;
        $sql = "delete from mt_user_group_tenant where tenant_uuid = ?";
        $result = $this->dbExec($sql, [$tenantuuid]);
        
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
    * 删除租户费用策略
    * @param unknown $tenant_uuid 租户uuid
    * @author liushuai@vinchin.com
    * @return true 成功   false失败
    */
    public function pDelTenantBilling($tenant_uuid){
        if(empty($tenant_uuid)) return true;
        //先删除关联表
        $sql_mt = "delete from mt_tenant_billing where tenant_uuid = ?";
        $result_mt = $this->dbExec($sql_mt,array($tenant_uuid));
        //删除费用详情表
        $sql_details = "delete from bd_billing_details where tenant_uuid = ?";
        $result_details = $result_mt && $this->dbExec($sql_details,array($tenant_uuid));
        if($result_details){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 公共方法
     * 获取租户的所有角色
     * @param string $tenantuuid  租户uuid
     * @author luokai@vinchin.com
     * @return array  角色列表,包含bd_role表内容
     */
    public function pGetTenantAllRole($tenantuuid){
        $this->paramsCheck($tenantuuid);
        
        $sql = "select distinct role_uuid, role_name, permission_uuid, lock_flag, tenant_uuid from bd_role where tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        return $data;
    }
    
}