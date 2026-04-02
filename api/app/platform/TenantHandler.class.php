<?php
/*******************************************
 ** 多租户系统调度总类
 *  维护用户基本信息和权限,提供多租户系统中各种资源的统一获取
 **
 ** @author       xiezhuowei@vinchin.com;luokai@vinchin.com;liushuai@vinchin.com
 ** @date         2020-09-10 下午17:04:00
 ** @version      1.0.0
 ** @copyright    Copyright 2020 vinchin.com
 ********************************************/
class TenantHandler extends OPHandler{
    /**
     * 添加租户
     * @param unknown $params
     */
    public function addTenant($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_tenant_manager_add");
        //TODO
        $tenantname = $params['tenantname'];
        $this->checkTenantNameExist($tenantname);
        $nickname = $params['nickname'];
        $username = $params['username'];
        $password = $params['password'];
        $email = $params['email'];
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
        // 改为根据role_uuid 分别查出对应的role_name
        $groupList = $this->dbSelect("SELECT role_name FROM bd_role WHERE role_uuid in ('eee859d5-341a-b95a-33d0-6ed583d0f7a1','2b214439-8f0b-ec23-a3be-2014ad9baecc','03e12c93-9dc1-371a-6a64-b74965d36723')", []);
        //$groupList = array('Tenant Admin', 'Tenant Operator', 'Tenant Auditor');
        foreach ($groupList as $groupname){
            //依次创建默认用户组
            $newgroupname = $tenantname."\\".$groupname['role_name'];
            $this->addTenantAdminUsergroup($tenantuuid, $newgroupname, $adminuuid, $groupname['role_name'], $username);
        }
        $createTime = date('Y-m-d H:i:s');
        $sqlParams = array($tenantuuid, $tenantname, $nickname, $createTime, $adminuuid, $config, Xphp::$_config['FLAG']['SET'], Xphp::$_user['useruuid'], Xphp::$_user['username']);
        $sql = "insert into bd_tenant (tenant_uuid, tenant_name, nick_name, create_time, admin_uuid, config, lock_flag, create_user_uuid,create_user_name) 
                values (?, ?, ?, ?, ?, ?, ?,?,?)";
        $result = $this->dbQuery($sql, $sqlParams);

        if($result){
            //添加默认组织结构
            $params = array(
                'tenantFlag' => true,
                'organname' => $nickname,
                'type' => 1,
                'quota' => -1,
                'tenantuuid' => $tenantuuid
            );
            $organuuid = $this->addOrgan($params);
            //添加默认部门
            $params = array(
                'tenantFlag' => true,
                'departname' => Xphp::$_lang['UI_ORGAN_DEPARTMENT_ONE'],
                'parentuuid' => $organuuid,
                'parentname' => $nickname,
                'type' => 2,
                'quota' => 100 * 1024 *1024 * 1024,
                'tenantuuid' => $tenantuuid
            );
            $this->addDepartment($params);
            //添加系统操作日志
            $this->systemLog('SYSTEM_TENANT_ADD_SUCCESS', array($tenantname));
            return $this->muOpResult(true, Xphp::$_lang['UI_TENANT_ADD']);
        }else{
            return $this->muOpResult(false, Xphp::$_lang['UI_TENANT_ADD'], "", "warning");
        }

    }

    /**
     * 修改租户
     * @param unknown $params
     */
    public function editTenant($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_tenant_manager_edit");
        //TODO
        $tenantuuid = $params['tenantuuid'];
        $tenantname = $params['tenantname'];
        $nickname = $params['nickname'];
        $useruuid = $params['useruuid'];
        $password = $params['password'];
        $email = $params['email'];
        $utils = Xphp::instance('Utils');
        $createTime = date('Y-m-d H:i:s');
        //更新用户
        //获取原始密码比对,如果原始密码的MD5的MD5和新的密码一样,就使用原来的密码,反之用新密码
        //两次MD5是因为发送到界面有一次MD5,然后界面发送回来还有一次MD5
        $sql = "select password from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        if($data){
            $password = ($password == md5(md5($data[0]['password']))) ? $data[0]['password'] : $password;
        }
        parent::dbBeginTransaction(); //事务
        //更新bd_user那张表
        $sqlUser = "update bd_user set password = ?, create_time = ?, email = ? where user_uuid = ? ";
        $sqlUserParams = array($password, $createTime, $email, $useruuid);
        $result = parent::dbExec($sqlUser, $sqlUserParams);

        $sqlParams = array($nickname, $createTime, $tenantuuid);
        $sql = "update bd_tenant set nick_name = ?, create_time = ? where tenant_uuid = ?";
        $result = $result && parent::dbExec($sql, $sqlParams);

        if($result){
            parent::dbCommit();
        }else{
            parent::dbRollBack();
        }

        return $this->muOpResult($result, Xphp::$_lang['UI_TENANT_MODIFY']);

    }

    /**
     * 删除租户
     * @param unknown $params
     */
    public function deleteTenant($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_tenant_manager_delete");
        //TODO
        $tenantuuid = $params['tenantuuid'];
        $sql = "select tenant_name, nick_name from bd_tenant where tenant_uuid = ?";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $tenantName = "";
        if(!empty($data)){
            $tenantName = $data[0]['tenant_name']."(".$data[0]['nick_name'].")";
        }

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
        //删除用户:用户的任务(强制停止,然后再删除),用户的告警,用户的日志, 租户用户关联
        $result = $result && $this->deleteTenantAllJob($userList);
        //删除租户所有数据,虚拟机|文件|数据库时间点
//         $result = $result && $this->deleteTenantAllTimepoint($userList);

        //删除用户的告警和日志
        $result = $result && $this->deleteTenantLogAndAlarm($userList);


        //删除租户内所有的数据库代理
        $result = $result && $this->deleteTenantAllDbAgent($userList);

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
            $this->systemLog('SYSTEM_TENANT_DELETE_SUCCESS', array($tenantName));
            $this->dbCommit();
        }else{
            //删除租户失败，回滚操作
            $this->writeTenantLog("UI_DELETE_TENANT_LOG20_0");
            $this->dbRollBack();
        }
        if($result){
            return $this->muOpResult(true,Xphp::$_lang['UI_TENANT_DELETE']);
        }else{
            return $this->muOpResult(false,Xphp::$_lang['UI_TENANT_DELETE'], '', 'warning');
        }
    }


    /**
     * 获取租户列表
     * @param unknown $params
     */
    public function getTenantLists($params){
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];//排序的参数
        $sortType = $params['sortType'];//排序类型
        $sortArr = array('', '', 'tenant_name', 'nick_name', 'admin_uuid', 'lock_flag', 'create_time');

        $sql = "select tenant_uuid, tenant_name, nick_name, admin_uuid, lock_flag, unix_timestamp(create_time) as create_time
                from bd_tenant ";
        $sqlCount = "select count(id) as total from bd_tenant ";
        $sqlParams = array();
        $sqlCountParams = array();

        //如果不是全局admin管理员
        if(!empty($_SESSION['tenantuuid']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] != "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
            $sql .=  " where create_user_uuid = ? ";
            $sqlCount .= " where create_user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams,array($start, $length));
        $tenants = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $utils = Xphp::instance("Utils");
        $userHandler = Xphp::instance('UsersHandler');
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        foreach ($tenants as $tenant){

            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $tenant['tenant_uuid'] .'">',
                $id,
                $tenant['tenant_name'],
                $tenant['nick_name'],
                $userHandler->getUsername($tenant['admin_uuid']),
                $userHandler->getLockDes($tenant['lock_flag']),
                $this->parseDate($tenant['create_time']),
                $tenant['tenant_uuid']

            );
            $id++;
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);

    }

    //TODO 获取某个租户添加时需要修改的信息
    public function getTenantEditInfo($params){
        $tenantuuid = $params['tenantuuid'];
        $sql = "select bt.admin_uuid, bt.tenant_name, bt.nick_name, bu.user_name, bu.password, bu.email from bd_tenant bt, bd_user bu 
                where bt.admin_uuid = bu.user_uuid and bt.tenant_uuid = ? ";

        $data = $this->dbSelect($sql, array($tenantuuid));
        $info = array();
        if(!empty($data)){
            $info = array(
                'tenantname' => $data[0]['tenant_name'],
                'nickname' => $data[0]['nick_name'],
                'adminname' => $data[0]['user_name'],
                'password' => md5($data[0]['password']),
                'email' => $data[0]['email'],
                'adminuuid' => $data[0]['admin_uuid']

            );
        }

        return json_encode($info);

    }

    //TODO 启用租户
    public function unlockTenant($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_tenant_manager_enable");
        $tenantList = $params['tenantlist'];
        $tenantStr = implode("','", $tenantList);
        $tenantNameDes = $this->getTenantNameList($tenantList);
        $sql = "update bd_tenant set lock_flag = ? where tenant_uuid in ('".$tenantStr."')";
        $result = $this->dbExec($sql, array(Xphp::$_config['FLAG']['SET']));
        $this->systemLog('SYSTEM_TENANT_UNLOCK_SUCCESS', array($tenantNameDes));
        return $this->muOpResult($result, Xphp::$_lang['UI_TENANT_ENABLE']);
    }

    //TODO 停用租户
    public function lockTenant($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_tenant_manager_disable");
        $tenantuuid = $params['tenantuuid'];
        $tenantNameDes = $this->getTenantNameList(array($tenantuuid));
        //获取租户下所有用户
        $userList = $this->getTenantAllUser($tenantuuid);
        //停止租户上所有任务
        $this->checkTaskIfRunning($userList);
        $sql = "update bd_tenant set lock_flag = ? where tenant_uuid = ? ";
        $result = $this->dbExec($sql, array(Xphp::$_config['FLAG']['UNSET'], $tenantuuid));
        $this->systemLog('SYSTEM_TENANT_LOCK_SUCCESS', array($tenantNameDes));
        return $this->muOpResult($result, Xphp::$_lang['UI_TENANT_DISABLE']);
    }


    /**
     * 检查租户是否注册
     * @param string $params [username]
     * @return string
     */
    public function tenantnameAvailable($params){
        $tenantname = $params['tenantname'];
        $sql = "select id from bd_tenant where tenant_name = ?";
        $tenants = parent::dbSelect($sql, array($tenantname));
        return json_encode(empty($tenants));
    }


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
            $createTime, $createUserName, $createUserUUID, $language, 0);
        $sql = "insert bd_user (user_uuid, user_name, password, email, user_type, permission,
            create_time, create_user_name, create_user_uuid, language,user_level) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlExtention = "insert bd_user_extension (user_uuid,quota) values (?, ?)";
        parent::dbBeginTransaction(); //事务
        $result = parent::dbQuery($sql, $sqlParam);
        $result = $result && parent::dbQuery($sqlExtention, $sqlExtentionParams);
        if($result){
            $userHandler = Xphp::instance('UsersHandler');
            //添加用户和租户关系
            $userHandler->pAddUserTenant(array($userUUID), $tenantuuid);
            $this->systemLog('SYSTEM_USER_ADD_SUCCESS', array($username));
            parent::dbCommit();
        }else{
            parent::dbRollBack();
        }

        if(!$result){
            exit($this->muOpResult(false, Xphp::$_lang['UI_TENANT_ADD'], "", "warning"));
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
            $userHandler = Xphp::instance('UsersHandler');
            $userHandler->pAddUserGroupTenant(array($userGroupUUID),$tenantuuid);
            // 这里根据role_uuid查询出role_name
            $tenentAdmin = $this->dbSelect("SELECT role_name FROM bd_role WHERE role_uuid = 'eee859d5-341a-b95a-33d0-6ed583d0f7a1'", []);
            // if($groupname == "Tenant Admin"){
            if (!empty($tenentAdmin) && $groupname == $tenentAdmin[0]['role_name']) {
                $userHandler->pAddUserGroupUser($userGroupUUID, array($adminuuid));
            }
            //添加用户组和角色关联
            $roleUUID = $userHandler->pGetRoleuuidByName($groupname);
            if(!empty($roleUUID)){
                //添加租户管理员和管理员角色关联
                if (!empty($tenentAdmin) && $groupname == $tenentAdmin[0]['role_name']) {
                // if($groupname == "Tenant Admin"){
                    $userHandler->pAddUserRole($adminuuid, array($roleUUID));
                }
                //添加用户组与对应角色关联
                $userHandler->pAddUserGroupRole($userGroupUUID, array($roleUUID));
            }
            return true;
        }else{
            exit($this->muOpResult(false, Xphp::$_lang['UI_TENANT_ADD'], "", "warning"));
        }
    }

    /**
     * 公共方法
     * 获取租户的所有用户
     * @param string $tenantuuid  租户uuid
     * @author xiezhuowei@vinchin.com
     * @return array  用户列表,包含用户部分字段
     */
    public function pGetTenantAllUser($tenantuuid){
        $this->paramsCheck($tenantuuid);

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
        $this->paramsCheck($tenantuuid);

        $sql = "select distinct bug.user_group_uuid, bug.user_group_name, bug.user_group_type, bug.lock_flag, bug.description, bug.config from
                bd_user_group bug, mt_user_group_tenant mugt 
                where bug.user_group_uuid = mugt.user_group_uuid and mugt.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        return $data;
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

    /**
     * 公共方法
     * 获取租户的所有资源组
     * @param string $tenantuuid  租户uuid
     * @author xiezhuowei@vinchin.com
     * @return array  用户组列表,包含bd_resource_group表内容
     */
    public function pGetTenantAllResourceGroup($tenantuuid){
        $this->paramsCheck($tenantuuid);

        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.config, brg.description from
                mt_resource_group_tenant mrgt, bd_resource_group brg 
                where mrgt.resource_group_uuid = brg.resource_group_uuid and mrgt.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        return $data;
    }


    /**
     * 获取租户名字
     * @param string $tenantuuid
     */
    public function getTenantNameByUUID($tenantuuid){
        $sql = "select tenant_name from bd_tenant where tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $tenantName = "";
        if(!empty($data)){
            $tenantName = $data[0]['tenant_name'];
        }

        return $tenantName;

    }


    /**
     * 获取租户基本信息展示
     * @param unknown $params
     */
    public function getTenantBasicInfo($params){
        $tenantuuid = $params['tenantuuid'];
        $sql = "select bt.nick_name, bt.tenant_name, unix_timestamp(bt.create_time) create_time, bu.user_name, bu.email from bd_tenant bt, bd_user bu where bt.admin_uuid = bu.user_uuid and bt.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $info = array();
        if(!empty($data)){
            $email = Xphp::$_config['TIMESPACE'];
            if(!empty($data[0]['email'])){
                $email = $data[0]['email'];
            }
            $info = array(
                "nick_name" => $data[0]['nick_name'],
                "tenant_name" => $data[0]['tenant_name'],
                "admin_name"  => $data[0]['user_name'],
                "admin_email" => $email,
                "create_time" => date("Y-m-d H:i:s", intval($data[0]['create_time'])),
                "user_num" => $this->getTenantUserNum($tenantuuid),
                "user_group_num" => $this->getTenantUserGroupNum($tenantuuid)
            );
        }

        return json_encode($info);
    }

    /**
     * 获取租户用户数量
     * @param string $tenantuuid
     * @return number
     */
    public function getTenantUserNum($tenantuuid){
        $sqlCount = "select count(bu.user_uuid) as total from mt_user_tenant mut, bd_user bu where mut.user_uuid = bu.user_uuid and mut.tenant_uuid = ? ";
        $dataCount = $this->dbSelect($sqlCount, array($tenantuuid));
        $count = 0;
        if(!empty($dataCount)){
            $count = intval($dataCount[0]['total']);
        }

        return $count;
    }

    /**
     * 获取租户用户组数量
     * @param string $tenantuuid
     * @return number
     */
    public function getTenantUserGroupNum($tenantuuid){
        $sqlCount = "select count(bug.user_group_uuid) as total from mt_user_group_tenant mugt, bd_user_group bug where mugt.user_group_uuid = bug.user_group_uuid and mugt.tenant_uuid = ? ";
        $dataCount = $this->dbSelect($sqlCount, array($tenantuuid));
        $count = 0;
        if(!empty($dataCount)){
            $count = intval($dataCount[0]['total']);
        }

        return $count;
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
     * 添加租户高级配置信息
     * @param unknown $params
     */
    public function addTenantHighSettings($params){
        $tenantuuid = $params['tenantuuid'];
        $common = $params['common'];
        $backup = $params['backup'];
        $recover = $params['recover'];
//         $billing = $params['billing'];
        $params = array(
            'tenantuuid' => $tenantuuid
        );
        $oldSettings = $this->getTenantHighSettings($params);
        $oldSettings = json_decode($oldSettings, true);
        if(!empty($common)){
            $oldSettings['common'] = $common;
            //检查授权
            $this->checkTenantAuthInfo($common, $tenantuuid);
            //按数量授权才处理
            if($common['authtype'] == 2){
                //授权个数减少，清空对应模块授权
                $this->checkTenantAuthReduce($common, $tenantuuid);
            }

        }

//         if(!empty($backup)){
//             $oldSettings['backup'] = $backup;
//         }

        if(!empty($recover)){
            $oldSettings['recover'] = $recover;
        }

//         if(!empty($billing)){
//             $oldSettings['billing'] = $billing;
//         }
        $settings = json_encode($oldSettings);
        $sql = "update bd_tenant set config = ? where tenant_uuid = ?";
        $result = $this->dbExec($sql, array($settings, $tenantuuid));

        if($result){
            return $this->muOpResult(true, Xphp::$_lang['WEB_TENANT_SAVE_CONFIG_INFO']);
        }else{
            return $this->muOpResult(false, Xphp::$_lang['WEB_TENANT_SAVE_CONFIG_INFO'], "", "warning");
        }

    }

    /**
     * 获取租户拥有用户列表
     * @param unknown $params
     */
    public function getTenantUserList($params){
        $tenantuuid = $params['tenantuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bu.user_name', '', '');
        $sql = "select bu.user_uuid, bu.user_name from mt_user_tenant mut, bd_user bu where mut.user_uuid = bu.user_uuid and mut.tenant_uuid = ? ";
        $sql .= " order by  $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount = "select count(bu.user_uuid) as total from mt_user_tenant mut, bd_user bu where mut.user_uuid = bu.user_uuid and mut.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid, $start, $length));
        $dataCount = $this->dbSelect($sqlCount, array($tenantuuid));
        $records = array();
        $records['data'] = array();

        $count = intval($dataCount[0]['total']);

        if(!empty($data)){
            foreach ($data as $d){
                $records['data'][] = array(
//                     '<input type="checkbox" id="[]" value="'.$d['user_uuid'].'">',
                    $d['user_name'],
                    $this->getUserUserGroupList($d['user_uuid']),
                    $this->getUserRoleList($d['user_uuid'])
                );
            }
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;

        return  json_encode($records);


    }

    /**
     * 获取租户拥有用户组列表
     * @param unknown $params
     */
    public function getTenantUserGroupList($params){
        $tenantuuid = $params['tenantuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('bug.user_group_name', '', '');
        $sql = "select bug.user_group_uuid, bug.user_group_name from mt_user_group_tenant mugt, bd_user_group bug where mugt.user_group_uuid = bug.user_group_uuid and mugt.tenant_uuid = ? ";
        $sql .= " order by  $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount = "select count(bug.user_group_uuid) as total from mt_user_group_tenant mugt, bd_user_group bug where mugt.user_group_uuid = bug.user_group_uuid and mugt.tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid, $start, $length));
        $dataCount = $this->dbSelect($sqlCount, array($tenantuuid));
        $records = array();
        $records['data'] = array();

        $count = intval($dataCount[0]['total']);

        if(!empty($data)){
            foreach ($data as $d){
                $records['data'][] = array(
//                     '<input type="checkbox" id="[]" value="'.$d['user_group_uuid'].'">',
                    $d['user_group_name'],
                    $this->getUserGroupUserList($d['user_group_uuid']),
                    $this->getUserGroupRoleList($d['user_group_uuid'])
                );
            }
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;

        return  json_encode($records);

    }

    /**
     * 获取租户资源数量统计
     * @param unknown $params
     */
    public function getTenantResourceCount($params){

    }

    /**
     * 获取用户关联用户组集合字符串
     * @param string $useruuid
     */
    public function getUserUserGroupList($useruuid){
        $sql = "select bug.user_group_name from bd_user_group bug, mt_user_user_group muug where bug.user_group_uuid = muug.user_group_uuid and muug.user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        $userGropList = array();
        $userGroupDes = Xphp::$_config['TIMESPACE'];
        if(!empty($data)){
            foreach ($data as $d){
                $userGropList[] = $d['user_group_name'];
            }

            $userGroupDes = implode(" , ", $userGropList);
        }
        return $userGroupDes;
    }

    /**
     * 获取用户组关联用户集合字符串
     * @param string $userGroupuuid
     */
    public function getUserGroupUserList($userGroupuuid){
        $sql = "select bu.user_name from bd_user bu, mt_user_user_group muug where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = ?";
        $data = $this->dbSelect($sql, array($userGroupuuid));
        $userList = array();
        $userDes = Xphp::$_config['TIMESPACE'];
        if(!empty($data)){
            foreach ($data as $d){
                $userList[] = $d['user_name'];
            }

            $userDes = implode(" , ", $userList);
        }
        return $userDes;
    }

    /**
     * 获取用户所拥有角色
     * @param string $useruuid
     */
    public function getUserRoleList($useruuid){
        $sql = "select br.role_name from bd_role br, mt_user_role mur where br.role_uuid = mur.role_uuid and mur.user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        $roleList = array();
        $roleDes = Xphp::$_config['TIMESPACE'];
        if(!empty($data)){
            foreach ($data as $d){
                $roleList[] = $d['role_name'];
            }

            $roleDes = implode(" , ", $roleList);
        }
        return $roleDes;

    }


    /**
     * 获取用户组所拥有角色
     * @param string $usergroupuuid
     */
    public function getUserGroupRoleList($usergroupuuid){
        $sql = "select br.role_name from bd_role br, mt_user_group_role mugr where br.role_uuid = mugr.role_uuid and mugr.user_group_uuid = ? ";
        $data = $this->dbSelect($sql, array($usergroupuuid));
        $roleList = array();
        $roleDes = Xphp::$_config['TIMESPACE'];
        if(!empty($data)){
            foreach ($data as $d){
                $roleList[] = $d['role_name'];
            }

            $roleDes = implode(" , ", $roleList);
        }

        return $roleDes;
    }

    /**
     * 添加用户和租户关联
     * @param unknown $params
     * @return string
     * @author luokai@vinchin.com
     */
    public function addTenantUser($params){
       $tenantUUID = $params['tenantUUID'];
       $userList = $params['userList'];
       $usersHandler = Xphp::instance('UsersHandler');
       //添加租户和用户集合关联
       $result = $usersHandler->pAddUserTenant($userList, $tenantUUID);
       if($result){
           return $this->muOpResult(true, Xphp::$_lang['WEB_TENANT_ADD_USER']);

       }else{
           return $this->muOpResult(false, Xphp::$_lang['WEB_TENANT_ADD_USER']);
       }

    }

    /**
     * 删除用户和租户关联
     * @param unknown $params
     * @return string
     * @author luokai@vinchin.com
     */
    public function deleteTenantUser($params){
        $tenantUUID = $params['tenantUUID'];
        $userList = $params['userList'];
        $usersHandler = Xphp::instance('UsersHandler');
        //取消租户和用户集合关联
        $result = $usersHandler->pDeleteSelectUserTenant($userList, $tenantUUID);
        if($result){
            return $this->muOpResult(true, Xphp::$_lang['WEB_TENANT_CANCEL_USER']);

        }else{
            return $this->muOpResult(false, Xphp::$_lang['WEB_TENANT_CANCEL_USER']);
        }

    }

    /**
     * 添加用户组和租户关联
     * @param unknown $params
     * @return string
     * @author luokai@vinchin.com
     */
    public function addTenantUserGroup($params){
        $tenantUUID = $params['tenantUUID'];
        $userGroupList = $params['userGroupList'];
        $usersHandler = Xphp::instance('UsersHandler');
        //添加租户和用户组集合关联
        $result = $usersHandler->pAddUserGroupTenant($userGroupList, $tenantUUID);
        if($result){
            return $this->muOpResult(true, Xphp::$_lang['WEB_TENANT_ADD_USER_GROUP']);

        }else{
            return $this->muOpResult(false, Xphp::$_lang['WEB_TENANT_ADD_USER_GROUP']);
        }

    }

    /**
     * 删除用户组和租户关联
     * @param unknown $params
     * @return string
     * @author luokai@vinchin.com
     */
    public function deleteTenantUserGroup($params){
        $tenantUUID = $params['tenantUUID'];
        $userGroupList = $params['userGroupList'];
        $usersHandler = Xphp::instance('UsersHandler');
        //取消租户和用户组集合关联
        $result = $usersHandler->pDeleteSelectUserGroupTenant($userGroupList, $tenantUUID);
        if($result){
            return $this->muOpResult(true, Xphp::$_lang['WEB_TENANT_CANCEL_USER_GROUP']);

        }else{
            return $this->muOpResult(false, Xphp::$_lang['WEB_TENANT_CANCEL_USER_GROUP']);
        }

    }


    /**
     * 获取租户添加用户列表
     * @param unknown $params
     * @return string
     * @auth luokai@vinchin.com
     */
    public function getTenantUserAddList($params){
        $tenantuuid = $params['tenantuuid'];
        $sqlUserALL = "select user_uuid, user_name from bd_user where user_uuid != ? and lock_flag = ? ";
        $sqlParams = array("a508b813-19c7-eb4e-d6fa-bb61b25a4de9", Xphp::$_config['FLAG']['SET']);

        $dataUser = $this->dbSelect($sqlUserALL,$sqlParams);
        $info = array();
        foreach ($dataUser as $d){
            $flag = $this->pCheckUserExistTenant($d['user_uuid']);  //检查用户是否已经在租户内
            if($flag) continue;
            $info[] = array(
                'user_uuid' => $d['user_uuid'],
                'user_name' => $d['user_name']
            );

        }
        return json_encode($info);
    }

    /**
     * 获取租户添加用户组列表
     * @param unknown $params
     * @return string
     * @auth luokai@vinchin.com
     */
    public function getTenantUserGroupAddList($params){
        $tenantuuid = $params['tenantuuid'];
        $sqlUserGroupALL = "select user_group_uuid, user_group_name from bd_user_group where lock_flag = ?";
        $sqlParams = array(Xphp::$_config['FLAG']['SET']);
        $dataUserGroup = $this->dbSelect($sqlUserGroupALL,$sqlParams);
        $info = array();
        foreach ($dataUserGroup as $d){
            $flag = $this->pCheckUserGroupExistTenant($d['user_group_uuid']);  //检查用户组是否已经在租户内
            if($flag) continue;
            $info[] = array(
                'user_group_uuid' => $d['user_group_uuid'],
                'user_group_name' => $d['user_group_name']
            );

        }
        return json_encode($info);
    }

    /**
     * 获取当前租户资源统计
     * @param unknown $params
     * @return string
     * @author luokai@vinchin.com
     */
    public function getTenantResourceTotal($params){
        $tenantUUID = $params['tenantUUID'];
        $resourceHandler = Xphp::instance('ResourceHandler');
        //文件代理资源
        $fileInfo = $resourceHandler->pGetTenantResourceFileHost($tenantUUID);
        //数据库定时主机资源
        $dbInfo = $resourceHandler->pGetTenantResourceDbHost($tenantUUID);
        //cpd实时主机资源
        $cdpInfo = $resourceHandler->pGetTenantResourceCDPHost($tenantUUID);
        //虚拟机资源
        $vmInfo = $resourceHandler->pGetTenantResourceVM($tenantUUID);
        //虚拟机备份代理资源
        $applianceInfo = $resourceHandler->pGetTenantResourceAppliance($tenantUUID);
        //节点资源
        $nodeInfo = $resourceHandler->pGetTenantResourceNode($tenantUUID);
        //存储资源
        $storageInfo = $resourceHandler->pGetTenantResourceStorage($tenantUUID);

        $info = array(
            'fileNum' => intval($fileInfo['total']),
            'dbNum' => intval($dbInfo['total']),
            'cdpNum' => intval($cdpInfo['total']),
            'vmNum' => intval($vmInfo['total']),
            'applianceNum' => intval($applianceInfo['total']),
            'nodeNum' => intval($nodeInfo['total']),
            'storageNum' => intval($storageInfo['total'])
        );

        return json_encode($info);

    }

    /**
     * 检查用户是否已经存在于租户中
     * @param string $useruuid
     * @return boolean
     * @auth luokai@vinchin.com
     */
    public function pCheckUserExistTenant($useruuid){
        $sql = "select tenant_uuid from mt_user_tenant where user_uuid = ? ";
        $data = $this->dbSelect($sql, array($useruuid));
        if(!empty($data)){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 检查用户组是否已经存在于租户中
     * @param string $usergroupuuid
     * @return boolean
     * @auth luokai@vinchin.com
     */
    public function pCheckUserGroupExistTenant($usergroupuuid){
        $sql = "select tenant_uuid from mt_user_group_tenant where user_group_uuid = ? ";
        $data = $this->dbSelect($sql, array($usergroupuuid));
        if(!empty($data)){
            return true;
        }else{
            return false;
        }
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
     * 获取租户内部用户所有资源
     * @author luokai@vinchin.com
     * @return fetchAll()[]
     */
    public function pGetTenantUserResource(){
        $info = array();
        $sql = "select resource_uuid from mt_user_resource where user_uuid = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        if(!empty($data)){
           foreach($data as $d){
              $info[] = $d['resource_uuid'];
           }
        }

        return $info;
    }

    /**
     * 获取租户所有用户组
     * @param string $tenantuuid
     * @return unknown[]
     */
    public function getTenantAllUser($tenantuuid){
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
     * 检查并停止当前租户所有任务
     * @param array $userList
     * @return void|boolean
     */
    private function checkTaskIfRunning($userList){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG1_1");
        if(empty($userList)) return true;
        $userDes = implode("','", $userList);
        $stopFlag = false;
        $jobHandler = Xphp::instance('JobHandler');
        $i = 0;
        while(!$stopFlag){
            $sql = "select task_uuid, task_type, module_type, task_status from bd_task where task_status != ? and user_uuid in ('".$userDes."')";
            $data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['STOPPED']));
            $t1 = time();
            if(!empty($data)){
                //循环去停止任务
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
                    $jobHandler->stopJob($params);
                }
                $t2 = time();
                //间隔10秒检查
                sleep(2+($t2-$t1));
                $i++;
                //尝试10次停止任务后，直接失败退出
                if($i == 10){
                    $this->writeTenantLog("UI_DELETE_TENANT_LOG2_2");
                    exit($this->muOpResult(false, Xphp::$_lang['UI_TENANT_DELETE']));
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
     * 删除租户所有备份数据
     * @param array $userList
     * @return boolean
     */
    public function deleteTenantAllTimepoint($userList){
        $this->writeTenantLog("delete all backup data");
        if(empty($userList)) return true;
        $userDes = implode("','", $userList);
        $deleteAllFlag = false;
        while(!$deleteAllFlag){
            $vm = array();
            $file = array();
            $vmCopy = array();
            $fileCopy = array();
            $dbCopy = array();
            $archive = array();
            $db = array();
            $sql = "select bbt.timepoint_uuid, bbt.module_type, bbt.task_type, bsr.node_uuid from bd_backup_timepoint bbt, bd_storage_resource bsr where bbt.storage_uuid = bsr.storage_uuid and bbt.user_uuid in ('".$userDes."') ";
            $data = $this->dbSelect($sql);
            $t1 = time();
            if(!empty($data)){
                foreach ($data as $d){
                    $type = intval($d['module_type']);
                    $taskType = intval($d['task_type']);
                    $moduleType = Xphp::$_config['MODULE_TYPE'];
                    switch ($moduleType){
                        case $moduleType['VM']:
                            $vm[] = $d['timepoint_uuid'];
                            break;
                        case $moduleType['FILE']:
                            $file[] = $d['timepoint_uuid'];
                            break;
                        case $moduleType['DB']:
                            $db[] = $d['timepoint_uuid'];
                            break;
                        case $moduleType['BACKUP_COPY_CLIENT']:
                            //副本|归档
                            if($taskType == Xphp::$_config['TASKTYPE']['BACKUP_COPY']){ //虚拟机副本
                                $vmCopy[] = $d['timepoint_uuid'];
                            }else if($taskType == Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY'] ){//文件副本
                                $fileCopy[] = $d['timepoint_uuid'];
                            }else if($taskType == Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY'] ){//数据库副本
                                $dbCopy[] = $d['timepoint_uuid'];
                            }else if($taskType == Xphp::$_config['TASKTYPE']['ARCHIVE']){
                             //虚拟机归档
                                $archive[] = $d['timepoint_uuid'];
                            }


                            break;
                        default:
                            break;
                    }
                }
                //删除虚拟机备份数据
                $this->deleteAllVmPoint($vm);
                //删除文件备份数据
                $this->deleteAllFilePoint($file);
                //删除数据库备份数据
                $this->deleteAllDbPoint($db);
                //删除虚拟机副本数据
                $this->deleteAllVmCopyPoint($vmCopy);
                //删除文件副本数据
                $this->deleteAllFileCopyPoint($fileCopy);
                //删除数据库副本数据
                $this->deleteAllDbCopyPoint($dbCopy);
                //删除归档数据
                $this->deleteAllArchivePoint($archive);
                $t2 = time();
                //间隔10秒检查
                sleep(5+($t2-$t1));
            }else{
                $deleteAllFlag = true;
                break;
            }

        }
        $this->writeTenantLog("delete all backup data success");
        //delete all backup data;
        return true;
    }

    /**
     * 删除租户所有日志和告警
     * @param array $userList
     * @return boolean
     */
    public function deleteTenantLogAndAlarm($userList){
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
     * 删除租户所有用户
     * @param string $tenantuuid
     * @param array $userList
     * @return boolean
     */
    public function deleteTenantAllUser($tenantuuid, $userList){
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
    public function deleteTenantAllUserGroup($tenantuuid, $userGroupList){
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
    public function deleteTenantAllRole($tenantuuid, $roleList, $permissionList){
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
    public function deleteTenantAllResourceGroup($tenantuuid, $resourceGroupList){
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
    public function deleteTenantAllDomainServer($tenantuuid){
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
    public function deleteTenantAllOrganization($tenantuuid){
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
    public function deleteTenantAllBilling($tenantuuid){
        $this->writeTenantLog("UI_DELETE_TENANT_LOG17_1");
        $billingHandler = Xphp::instance('BillingHandler');

        $result = $billingHandler->pDelTenant($tenantuuid);
        if($result){
            $this->writeTenantLog("UI_DELETE_TENANT_LOG18_1");
        }else{
            $this->writeTenantLog("UI_DELETE_TENANT_LOG18_0");
        }

        return $result;
    }




    //删除虚拟机备份数据
    private function deleteAllVmPoint($timepoints){
        if(empty($timepoints)) return true;
        $timepointDes = implode("','", $timepoints);
        $sql = "select bbt.timepoint_uuid, vbt.hypervisor_type, bsr.node_uuid from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where
                bbt.timepoint_uuid = vbt.timpoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.timpoint_uuid in ('".$timepointDes."')";
        $data = $this->dbSelect($sql);
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                $info[]= array(
                    'timepointuuid' => $d['timepoint_uuid'],
                    'nodeuuid' => $d['node_uuid'],
                    'hypervisor' => intval($d['hypervisor_type'])
                );
            }

            //执行删除虚拟机备份数据
            $vmHandler = Xphp::instance('VmHandler');
            $params = array(
                'timepointlist' => $info,
                'vmlist' => array()
            );
            $vmHandler->deleteSelectTimepoint($params);
        }


        return true;

    }

    //删除文件备份数据
    private function deleteAllFilePoint($timepoints){
        if(empty($timepoints)) return true;
        $timepointDes = implode("','", $timepoints);
        $fileHandler = Xphp::instance('fileHandler');
        foreach ($timepoints as $uuid){
            //单个删除文件时间点
            $params = array('uuid' => $uuid);
            $fileHandler->deleteTimepoint($params);
        }


        return true;
    }

    //删除数据库备份数据
    private function deleteAllDbPoint($timepoints){
        if(empty($timepoints)) return true;
        $timepointDes = implode("','", $timepoints);
        $sql = "select bbt.timepoint_uuid, dbt.db_type, bsr.storage_uuid from bd_backup_timepoint bbt, db_backup_timepoint dbt, bd_storage_resource bsr where
                bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid in ('".$timepointDes."')";
        $data = $this->dbSelect($sql);
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                $info[] = array(
                    'timepointuuid' => $d['timepoint_uuid'],
                    'nodeuuid' => $d['node_uuid'],
                    'dbtype' => intval($d['db_type']),
                );
            }

           $params = array(
               'timepointlist' => $info,
               'dblist' => array()
           );

           $dbProtectHandler = Xphp::instance('DBProtectHandler');
           $dbProtectHandler->deleteDBSelectTimepoint($params);
        }


        return true;
    }

    //删除虚拟机副本数据
    private function deleteAllVmCopyPoint($timepoints){
        if(empty($timepoints)) return true;
        $timepointDes = implode("','", $timepoints);
        $sql = "select bbt.timepoint_uuid, vbt.hypervisor_type, bsr.node_uuid from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where
                bbt.timepoint_uuid = vbt.timpoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.timpoint_uuid in ('".$timepointDes."')";
        $data = $this->dbSelect($sql);
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                $info[]= array(
                    'timepointuuid' => $d['timepoint_uuid'],
                    'nodeuuid' => $d['node_uuid'],
                    'hypervisor' => intval($d['hypervisor_type'])
                );
            }

            //执行删除虚拟机副本数据
            $copyHandler = Xphp::instance('CopyHandler');
            $params = array(
                'timepointList' => $info,
                'vmList' => array()
            );
            $copyHandler->deleteBatchCopypoint($params);
        }

        return true;
    }

    //删除文件副本数据
    private function deleteAllFileCopyPoint($timepoints){
        if(empty($timepoints)) return true;
        $timepointDes = implode("','", $timepoints);
        $fileHandler = Xphp::instance('fileHandler');
        foreach ($timepoints as $uuid){
            //单个删除文件时间点
            $params = array('uuid' => $uuid);
            $fileHandler->deleteTimepoint($params);
        }


        return true;
    }

    //删除数据库副本数据
    private function deleteAllDbCopyPoint($timepoints){
        if(!empty($timepoints)) return true;
        $timepointDes = implode("','", $timepoints);
        $sql = "select bbt.timepoint_uuid, dbt.db_type, bsr.storage_uuid from bd_backup_timepoint bbt, db_backup_timepoint dbt, bd_storage_resource bsr where
                bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid in ('".$timepointDes."')";
        $data = $this->dbSelect($sql);
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                $info[] = array(
                    'timepointuuid' => $d['timepoint_uuid'],
                    'nodeuuid' => $d['node_uuid'],
                    'dbtype' => intval($d['db_type']),
                );
            }

            $params = array(
                'timepointList' => $info,
                'dbList' => array()
            );

            $copyHandler = Xphp::instance('CopyHandler');
            $copyHandler->deleteDbBatchCopypoint($params);
        }


        return true;
    }

    //删除归档数据
    private function deleteAllArchivePoint($timepoints){
        if(empty($timepoints)) return true;
        $timepointDes = implode("','", $timepoints);
        $sql = "select bbt.timepoint_uuid, vbt.hypervisor_type, bsr.node_uuid from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr where
                bbt.timepoint_uuid = vbt.timpoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.timpoint_uuid in ('".$timepointDes."')";
        $data = $this->dbSelect($sql);
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                $info[]= array(
                    'timepointuuid' => $d['timepoint_uuid'],
                    'nodeuuid' => $d['node_uuid'],
                    'hypervisor' => intval($d['hypervisor_type'])
                );
            }

            //执行删除虚拟机归档数据
            $archiveHandler = Xphp::instance('ArchiveHandler');
            $params = array(
                'timepointList' => $info,
                'vmList' => array()
            );
            $archiveHandler->deleteBatchArchivepoint($params);
        }

        return true;
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
        $result = $this->dbExec($sql, array($tenantuuid));

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
        $result = $this->dbExec($sql, array($tenantuuid));

        if($result){
            return true;
        }else{
            return false;
        }
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
     * 读取删除租户日志内容
     * @param unknown $content
     * @return string
     */
    public function readTenantLog(){
        $file = Xphp::$_config['DELETE_TENANT_PATH'];
        if(!file_exists($file)){
            $addFile = "touch ".$file;
            exec($addFile);
        }
        $info = file_get_contents($file);
        $info = explode("\n", $info);
        $str = '';
        foreach ($info as $key){
            //获取每条任务的状态去key的最后一个字符为状态,1为成功,0为失败
            if(!empty($key)){
                $flag_de = substr($key,-1,1);
                if($flag_de == '1'){
                    $str .= '<li><div class="col1"><div class="cont contdetail"><div class="cont-col1"><div class="label label-sm label-success"><i class="fa fa-check"></i></div></div><div class="cont-col2"><div class="desc">'.Xphp::$_lang[$key] .'</div></div></div></div></li>';
                }else{
                    $str .= '<li><div class="col1"><div class="cont contdetail"><div class="cont-col1"><div class="label label-sm label-danger"><i class="fa fa-times"></i></div></div><div class="cont-col2"><div class="desc">'.Xphp::$_lang[$key].'</div></div></div></div></li>';
                }
            }
        }

        return $str;
    }

    /**
     * 检查租户名是否重复
     * @param string $tenantname
     */
    private function checkTenantNameExist($tenantname){
        $sql = "select tenant_uuid from bd_tenant where tenant_name = ? ";
        $data = $this->dbSelect($sql, array($tenantname));
        if(!empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['UI_TENANT_ADD'], Xphp::$_lang['WEB_TENANT_EXIST_NAME_ERROR'], "warning"));
        }

        return true;
    }

    /**
     * 获取租户名列表描述
     * @param array $tenantList
     */
    private function getTenantNameList($tenantList){
        $tenantStr = implode("','", $tenantList);
        $sql = "select tenant_name from bd_tenant where tenant_uuid in ('".$tenantStr."')";
        $data = $this->dbSelect($sql);
        $info = "";
        foreach ($data as $key => $d){
            if($key == count($data) - 1){
                $info .= $d['tenant_name'];
            }else{
                $info .= $d['tenant_name'] . ",";
            }
        }

        return $info;

    }

    /**
     * 获取组织下的用户
     * @param unknown $params
     * @return string
     */
    public function getOrganUsers($params){
        $start = $params['start'];
        $length = $params['length'];
        $organuuid = $params['uuid'];
        $search = $params['search'];
        $sql = "select muo.organization_uuid, bu.user_name, bu.user_uuid, bue.quota, bu.lock_flag, bu.user_type, sum(bbt.write_size) as real_size from bd_user_extension bue, mt_user_organization muo, bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid left join bd_backup_timepoint bbt on bu.user_uuid = bbt.user_uuid where bu.user_uuid = bue.user_uuid and bu.user_uuid =  muo.user_uuid ";
        $sqlParams = array();

        if(!empty($_SESSION['tenantuuid'])){
            $sql .= " and mut.tenant_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($_SESSION['tenantuuid']));
        }
        if(empty($organuuid) || $organuuid != "allusers"){
            $sql .= " and muo.organization_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($organuuid));
        }
        $utils = Xphp::instance('Utils');
        if(!empty($search['name'])){
            $name = $utils->escapeWildcard($search['name']);
            $sql .= " and bu.user_name like '%".$name."%'";
        }
        $sql .= " group by bu.user_uuid limit ? , ?";
        $sqlCount = "select count(bu.user_uuid) as count from mt_user_organization muo, bd_user bu where bu.user_uuid = muo.user_uuid and muo.organization_uuid = ?";
        $count = $this->dbSelect($sqlCount, array($organuuid));
        if($organuuid == "allusers") {
            $sqlCount = "select count(user_uuid) as count from bd_user bu";
            $count = $this->dbSelect($sqlCount);
        }
        $sqlParams = array_merge($sqlParams,array($start,$length));
        $data = $this->dbSelect($sql, $sqlParams);
        $records["data"] = array();

        $userHandler = Xphp::instance('UsersHandler');
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        if(!empty($data)){
            foreach ($data as $d){
                $quotaDes = "";
                if($d['quota'] == -1){
                    $quotaDes = $utils->calSize(intval($d['write_size']), true) . '/' .Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
                }else{
                    $quotaDes = $utils->calSize(intval($d['write_size']), true) . '/' . $utils->calSize(intval($d['quota']), true);
                }
                $quota = $d['quota'];

                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'. $d['user_uuid'] .'">',
                    $d['user_name'],
                    $quotaDes,
                    $userHandler->pGetUserTypeDes(intval($d['user_type'])),
                    $pfDes['USER_GROUP_LOCK_FLAG_DES'][intval($d['lock_flag'])],
                    $d['user_uuid'],
                    intval($d['quota']),
                    $d['organization_uuid']
                );
            }
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]["count"];
        $records["recordsFiltered"] = $count[0]["count"];

        return  json_encode($records);
    }

    /**
     * 获取组织结构树
     * @param unknown $params
     * @return string
     */
    public function getOrganizationTree($params){
        $uuid = $params['uuid'];
        $selectFlag = $params['selectFlag'];
        $node = array();
        $sql = "select organization_uuid, nickname, type, remark, parent_uuid, parent_name, quota, unix_timestamp(create_time) create_time from bd_organization where tenant_uuid = ?";
        $data = $this->dbSelect($sql, array($_SESSION['tenantuuid']));
        $topNode = $this->getDefaultNodes();
        if(!empty($data)){
            foreach ($data as $d){
                $pid = 0;
                if(!empty($d['parent_uuid'])){
                    $pid = $d['parent_uuid'];
                }
                if($selectFlag && $uuid == $d['organization_uuid']){
                    continue;
                }
                $node[] = array(
                    "id" => $d['organization_uuid'],
                    "name" => $d['nickname'],
                    "title" => $d['nickname'],
                    "type" => intval($d['type']),
                    "icon" => $this->pGetOrganIcon(intval($d['type'])),
                    "pId" => $pid,
                    "remark" => $d['remark'],
                    "open" => false,
                    "nocheck" => true,
                    "organuuid" => $d['organization_uuid'],
                    "quota" => intval($d['quota']),
                    "parentname" => $d['parent_name']

                );
            }
        }
        if(!$selectFlag){
            $node = array_merge($topNode, $node);
        }

        return json_encode($node);
    }

    /**
     * 添加组织结构
     * @param unknown $params
     * @return string
     */
    public function addOrgan($params){
        $addTenantFlag = $params['tenantFlag']; //添加租户标记
        $organname = $params['organname'];
        $parentuuid = "";
        $parentname = "";
        $type = $params['type'];
        $tenantuuid = $_SESSION['tenantuuid'];
        if($addTenantFlag){
            $tenantuuid = $params['tenantuuid'];
        }
        $quota = intval($params['quota']);
        //检查配额空间是否符合
        if(!empty($_SESSION['tenantuuid'])){
            $this->checkOrganQuota($quota);
        }
        $createTime = date('Y-m-d H:i:s');
        $utils = Xphp::instance('Utils');
        $uuid = $utils->uuid();
        $operate = Xphp::$_lang['UI_ORGAN_ADD_ORGANIZATION  '];
        $sqlParams = array($uuid, $organname, $parentuuid, $parentname, $type, $quota, $createTime, $tenantuuid);
        $sql = "insert into bd_organization (organization_uuid, nickname, parent_uuid, parent_name, type, quota, create_time, tenant_uuid) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbExec($sql, $sqlParams);
        $newNode = array(
            "id" => $uuid,
            "name" => $organname,
            "title" => $organname,
            "type" => $type,
            "icon" => $this->pGetOrganIcon($type),
            "pId" => $parentuuid,
            "open" => false,
            "nocheck" => true,
            "organuuid" => $uuid,
            "quota" => $quota,
            "parentname" => $parentname
        );
        //如果是从添加租户入口进来的
        if($addTenantFlag){
            return $uuid;
        }
        return $this->muOpResult($result, $operate, null, null, 0, $newNode);
    }

    /**
     * 编辑组织结构
     * @param unknown $params
     * @return string
     */
    public function editOrgan($params){
        $organname = $params['organname'];
        $uuid = $params['uuid'];
        $quota = intval($params['quota']);
        //检查配额空间是否符合
        if(!empty($_SESSION['tenantuuid'])){
            $this->checkOrganQuota($quota);
        }
        $createTime = date('Y-m-d H:i:s');
        $operate = Xphp::$_lang['UI_ORGAN_EDIT_ORGANIZATION'];
        $sqlParams = array($organname, $quota, $createTime, $uuid);
        $sql = "update bd_organization set nickname = ?, quota = ?, create_time = ? where organization_uuid = ?";
        $result = $this->dbExec($sql, $sqlParams);

        return $this->muOpResult($result, $operate);
    }

    /**
     * 删除组织结构
     * @param unknown $params
     * @return string
     */
    public function deleteOrgan($params){
        $uuid = $params['uuid'];
        $operate = Xphp::$_lang['UI_ORGAN_DELETE_ORGANIZATION'];
        $sqlParams = array($uuid);
        $sql = "delete from bd_organization where organization_uuid = ?";
        $result = $this->dbExec($sql, $sqlParams);

        return $this->muOpResult($result, $operate);
    }

    /**
     * 添加部门
     * @param unknown $params
     * @return string
     */
    public function addDepartment($params){
        $addTenantFlag = $params['tenantFlag']; //添加租户标记
        $departname = $params['departname'];
        $parentuuid = $params['parentuuid'];
        $parentname = $params['parentname'];
        $type = $params['type'];
        $tenantuuid = $_SESSION['tenantuuid'];
        //添加租户uuid
        if($addTenantFlag){
            $tenantuuid = $params['tenantuuid'];
        }
        $quota = intval($params['quota']);
        $this->checkDepartQuota($quota,$parentuuid);
        $createTime = date('Y-m-d H:i:s');
        $utils = Xphp::instance('Utils');
        $uuid = $utils->uuid();
        $operate = Xphp::$_lang['UI_ORGAN_ADD_DEPARTMENT'];
        $sqlParams = array($uuid, $departname, $parentuuid, $parentname, $type, $quota, $createTime, $tenantuuid);
        $sql = "insert into bd_organization (organization_uuid, nickname, parent_uuid, parent_name, type, quota, create_time, tenant_uuid) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbExec($sql, $sqlParams);

        $newNode = array(
            "id" => $uuid,
            "name" => $departname,
            "title" => $departname,
            "type" => $type,
            "icon" => $this->pGetOrganIcon($type),
            "pId" => $parentuuid,
            "open" => false,
            "nocheck" => true,
            "organuuid" => $uuid,
            "quota" => $quota,
            "parentname" => $parentname
        );
        return $this->muOpResult($result, $operate, null, null, 0, $newNode);
    }

    /**
     * 编辑部门
     * @param unknown $params
     * @return string
     */
    public function editDepartment($params){
        $departname = $params['departname'];
        $parentuuid = $params['parentuuid'];
        $uuid = $params['uuid'];
        $quota = intval($params['quota']);
        $this->checkDepartQuota($quota,$parentuuid);
        $createTime = date('Y-m-d H:i:s');
        $operate = Xphp::$_lang['UI_ORGAN_EDIT_DEPARTMENT'];
        $sqlParams = array($departname, $quota, $createTime, $uuid);
        $sql = "update bd_organization set nickname = ?, quota = ?, create_time = ? where organization_uuid = ?";
        $result = $this->dbExec($sql, $sqlParams);

        return $this->muOpResult($result, $operate);
    }

    /**
     * 删除部门
     * @param unknown $params
     * @return string
     */
    public function deleteDepartment($params){
        $uuid = $params['uuid'];
        //如果部门下面有子部门或者用户就不让删除
        $this->checkDepartDelete($uuid);
        $operate = Xphp::$_lang['UI_ORGAN_DELETE_DEPARTMENT'];
        $sqlParams = array($uuid);
        $sql = "delete from bd_organization where organization_uuid = ?";
        $result = $this->dbExec($sql, $sqlParams);

        return $this->muOpResult($result, $operate);
    }

    /**
     * 移动部门
     * @param unknown $params
     */
    public function moveDepartTo($params){
        $uuid = $params['uuid'];
        $parentuuid = $params['parentuuid'];
        $parentname = $params['parentname'];
        $operate = Xphp::$_lang['UI_ORGAN_MOVE_DEPARTMENT'];
        $sqlParams = array($parentuuid, $parentname, $uuid);
        $sql = "update bd_organization set parent_uuid = ?, parent_name = ? where organization_uuid = ?";
        $result = $this->dbExec($sql, $sqlParams);

        return $this->muOpResult($result, $operate);
    }


    /**
     * 添加用户到部门
     * @param unknown $params
     * @return string
     */
    public function addUserTo($params){
        $userList = $params['users'];
        $organuuid = $params['uuid'];
        $quota = $params['quota'];
        $allQuota = count($userList) * $quota;
//         $this->checkUserQuota($allQuota, $organuuid);
        $operate = Xphp::$_lang['UI_ORGAN_ADD_USER_TO_DEPARTMENT'];
        $sql = "insert into mt_user_organization (user_uuid, organization_uuid) values ";
        $result = true;
        parent::dbBeginTransaction();
        foreach ($userList as $key=>$l){
            $sql .="('".$l."','".$organuuid."')";
            if($key != (count($userList) - 1)){
                $sql .=",";
            }
            $result = $result && $this->pUpdateUserQuota($l, $quota);
        }
        $result = $result && parent::dbExec($sql);
        if($result){
            parent::dbCommit();
        }else{
            parent::dbRollBack();
        }
        return $this->muOpResult($result, $operate);
    }

    /**
     * 移除用户
     * @param unknown $params
     * @return string
     */
    public function removeUser($params){
        $users = $params['users'];
        $organuuid = $params['organuuid'];
        $usersDes = implode("','", $users);
        $operate = Xphp::$_lang['UI_ORGAN_DELETE_USER'];
        $sql = "delete from mt_user_organization where organization_uuid = ? and user_uuid in ('".$usersDes."')";
        $result = $this->dbExec($sql, array($organuuid));

        return $this->muOpResult($result, $operate);
    }

    /**
     * 移动用户到部门
     * @param unknown $params
     * @return string
     */
    public function moveUserToOrgan($params){
        $users = $params['users'];
        $quota = $this->getUsersQuota($users);
        $organuuid = $params['organuuid'];
        //检查用户配额是否超出组织的最大配额
        $this->checkUserQuota($quota, $organuuid);
        $oldOrganuuid = $params['oldorganuuid'];
        $operate = Xphp::$_lang['UI_ORGAN_MOVE_USER'];

        $usersDes = implode("','", $users);
        $sql = "delete from mt_user_organization where organization_uuid = ? and user_uuid in ('".$usersDes."')";
        $result = $this->dbExec($sql, array($oldOrganuuid));

        $sql = "insert into mt_user_organization (user_uuid, organization_uuid) values ";
        foreach ($users as $key=>$l){
            $sql .="('".$l."','".$organuuid."')";
            if($key != (count($users) - 1)){
                $sql .=",";
            }
        }
        $result = $this->dbExec($sql);

        return $this->muOpResult($result, $operate);
    }
    /**
     * 重置用户密码
     * @param unknown $params
     * @return string
     */
    public function organRepassword($params){
        $uuid = $params['uuid'];
        $password = $params['password'];
        $operate = Xphp::$_lang['UI_ORGAN_USER_RESET_PASSWORD'];
        $sql = "update bd_user set password = ? where user_uuid = ?";
        $result = $this->dbExec($sql, array($password, $uuid));

        return $this->muOpResult($result, $operate);
    }

    /**
     * 设置用户配额
     * @param unknown $params
     * @return string
     */
    public function setUserQuota($params){
        $uuid = $params['uuid'];
        $quota = intval($params['quota']);
        $organuuid = $params['organuuid'];
        $operate = Xphp::$_lang['UI_ORGAN_USER_SET_QUOTA'];
        $this->checkUserQuota($quota, $organuuid);
        $result = $this->pUpdateUserQuota($uuid, $quota);

        return $this->muOpResult($result, $operate);
    }

    /**公共方法
     * 获取组织结构图标
     * @param int $type
     * @return string
     */
    private function pGetOrganIcon($type){
        $icon = '';
        switch ($type){
            case 1:
                $icon = './img/platform/organization.png';
                break;
            case 2:
                $icon = './img/platform/department.png';
                break;
        }

        return $icon;
    }

    /**
     * 获取组织里所有用户
     * @return string[][]|number[][]|boolean[][]
     */
    public function getDefaultNodes(){
        $list = array();
        $allUser = array(
            "id" => "allusers",
            "name" => Xphp::$_lang['UI_ORGAN_ALL_USER'],
            "title" => Xphp::$_lang['UI_ORGAN_ALL_USER'],
            "type" => 2,
            "icon" => $this->pGetOrganIcon(2),
            "pId" => 0,
            "open" => false,
            "nocheck" => true,
            "organuuid" => "allusers",
        );
        $list[] = $allUser;
        return $list;
    }

    /**
     * 获取用户列表用于分配
     * @param unknown $params
     */
    public function getOrganAddUsers($params){
        $organuuid = $params['uuid'];
        $organUsers = $this->getOrganHaveUsers();
        $sqlUserALL = "select bt.tenant_name, bu.user_uuid, bu.user_name from bd_user bu left join
                        mt_user_tenant mut on mut.user_uuid = bu.user_uuid left join bd_tenant bt on mut.tenant_uuid = bt.tenant_uuid where bu.create_user_uuid = ? ";
        $sqlParams = array(Xphp::$_user['useruuid']);

        if(!empty($_SESSION['tenantuuid'])){
            $sqlUserALL .= " and mut.tenant_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($_SESSION['tenantuuid']));
        }

        $dataUser = $this->dbSelect($sqlUserALL,$sqlParams);

        $info = array();
        foreach ($dataUser as $d){
            //不显示超级管理员admin
            if(empty($d['tenant_name']) && $d['user_uuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9") continue;

            //不显示当前用户
            if($d['user_uuid'] == Xphp::$_user['useruuid']) continue;

            //不显示已添加用户
            if(in_array($d['user_uuid'], $organUsers)) continue;

            $username = $d['user_name'];
            if(!empty($d['tenant_name'])){
                $username .= '(' .$d['tenant_name']. ')';
            }
            $info[] = array(
                'user_uuid' => $d['user_uuid'],
                'user_name' => $username
            );

        }
        return json_encode($info);
    }


    /**
     * 获取组织结构已有的用户数组
     * @return fetchAll()[]
     */
    public function getOrganHaveUsers(){
        $sql = "select distinct user_uuid from mt_user_organization ";
        $data = $this->dbSelect($sql, array());
        $list = array();
        if(!empty($data)){
            foreach ($data as $d){
                $list[] = $d['user_uuid'];
            }
        }

        return $list;
    }

    /**
     * 检查组织名是否存在
     * @param string $params [username]
     * @return string
     */
    public function organnameAvailable($params){
        $uuid = $params['uuid'];
        $organname = $params['organname'];
        $type = $params['type'];
        $sql = "select organization_uuid from bd_organization where nickname = ? and type = ? ";
        $sqlParams = array($organname, $type);
        if(!empty($uuid)){
            $sql .= " and organization_uuid != ? ";
            $sqlParams = array_merge($sqlParams, array($uuid));
        }
        $organs = parent::dbSelect($sql, $sqlParams);
        return json_encode(empty($organs));
    }

    /**
     * 获取当前组织|部门配额空间
     * @param unknown $params
     * @return string
     */
    public function getOrganQuotaFree($params){
        $organuuid = $params['uuid'];
        $quota = $params['quota'];
        $type = $params['type'];
        $sql = "select sum(quota) as used_size from bd_organization where parent_uuid = ?";
        $data = $this->dbSelect($sql, array($organuuid));
        $used = 0;
        if(!empty($data)){
            $used = intval($data[0]['used_size']);
        }

        $utils = Xphp::instance('Utils');
        $des = $utils->calSize($used, true) . '/' . $utils->calSize($quota, true);
        //容量无限制
        if($quota == -1){
            $des = $utils->calSize($used, true) . '/' . Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
        }
        $info = array(
            'quotaDes' => $des,
            'icon' => $this->pGetOrganIcon($type)
        );
        return json_encode($info);
    }

    /**
     * 检查组织配额分配情况
     * @param int $quota
     */
    public function checkOrganQuota($quota){
        $tenantInfo = $this->pGetTenantSettings($_SESSION['tenant_uuid']);
        $tenantSize = $tenantInfo['common']['quotaSize'];
        $sql = "select sum(quota) as used_size from bd_organization where tenant_uuid = ? and type = ?";
        $data = $this->dbSelect($sql, array($_SESSION['tenant_uuid'], 1));
        if(!empty($data)){
            $usedSize = $data[0]['used_size'];
            if(!empty($tenantSize) && $tenantSize != -1){
                if($quota > ($tenantSize - intval($usedSize))){
                    exit($this->muOpResult(false, Xphp::$_lang['UI_ORGAN_ALLOCATION_QUOTA'], Xphp::$_lang['UI_ORGAN_ALLOCATION_QUOTA_NOT_ENOUGH_ERROR'], "warning"));
                }
            }
        }
        return true;
    }

    /**
     * 检查部门配额分配情况
     * @param int $quota
     * @param string $parentuuid
     */
    public function checkDepartQuota($quota, $parentuuid){
       $sql = "select quota from bd_organization where organization_uuid = ?";
       $data = $this->dbSelect($sql, array($parentuuid));
       $organSize = 0;
       $sqlSum = "select sum(quota) as used_size from bd_organization where parent_uuid = ?";
       $dataSize = $this->dbSelect($sqlSum, array($parentuuid));
       $used = 0;
       if(!empty($data)){
           $organSize = intval($data[0]['quota']);
           if(!empty($dataSize)){
               $used = intval($data[0]['used_size']);
           }

           if($organSize != -1 && $quota > ($organSize - $used)){
               exit($this->muOpResult(false, Xphp::$_lang['UI_ORGAN_ALLOCATION_QUOTA'], Xphp::$_lang['UI_ORGAN_ALLOCATION_QUOTA_NOT_ENOUGH_ERROR'], "warning"));
           }
       }
       return true;
    }

    /**
     * 检查用户配额分配
     * @param int $quota
     * @param string $organuuid
     * @return boolean
     */
    public function checkUserQuota($quota, $organuuid){
        $sqlOrgan = "select quota from bd_organization where organization_uuid = ? ";
        $dataOrgan = $this->dbSelect($sqlOrgan, array($organuuid));
        $organQuota = 0;
        if(!empty($dataOrgan)){
            $organQuota = intval($dataOrgan[0]['quota']);
        }
        $sql = "select sum(bue.quota) as used_size from mt_user_organization muo, bd_user_extension bue where 
                muo.user_uuid = bue.user_uuid and muo.organization_uuid = ? ";
        $data = $this->dbSelect($sql, array($organuuid));
        $used = 0;
        if(!empty($data)){
            $used = intval($data[0]['used_size']);
        }
        if($quota > ($organQuota - $used)){
            exit($this->muOpResult(false, Xphp::$_lang['UI_ORGAN_ALLOCATION_QUOTA'], Xphp::$_lang['UI_ORGAN_ALLOCATION_QUOTA_NOT_ENOUGH_ERROR'], "warning"));
        }

        return true;
    }

    /**
     * 更新用户配额
     * @param unknown $uuid
     * @param unknown $quota
     * @return boolean
     */
    public function pUpdateUserQuota($uuid, $quota){
        $sql = "update bd_user_extension set quota = ? where user_uuid = ?";

        return $this->dbExec($sql, array($quota,$uuid));
    }

    /**
     * 获取所选用户配额和
     * @param array $users
     * @return number
     */
    public function getUsersQuota($users){
        $userDes = implode("','", $users);
        $sql = "select sum(quota) as all_size from bd_user_extension where user_uuid in ('".$userDes."')";
        $data = $this->dbSelect($sql);
        $allSize = 0;
        if(!empty($data)){
            $allSize = intval($data[0]['all_size']);
        }

        return $allSize;
    }

    /**
     * 检查部门是否可以删除
     * @param unknown $uuid
     * @return boolean
     */
    public function checkDepartDelete($uuid){
        $sql = "select organization_uuid from bd_organization where parent_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));

        $sqlUser = "select user_uuid from mt_user_organization where organization_uuid = ?";
        $dataUser = $this->dbSelect($sqlUser, array($uuid));
        if(!empty($data) || !empty($dataUser)){
            exit($this->muOpResult(false, Xphp::$_lang['UI_ORGAN_DELETE_DEPARTMENT'], Xphp::$_lang['UI_ORGAN_DELETE_DEPARTMENT_HAVA_USER_ERROR'], "warning"));
        }

        return true;
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
            exit($this->muOpResult(false, Xphp::$_lang['UI_TENANT_DELETE'],Xphp::$_lang['UI_PLATFORM_NOT_CLEAR_BAKDATA'], "warning"));
        }

        return true;
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
                        exit($this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_AUTHOR_BY_CAPACITY'], Xphp::$_lang['UI_PLATFORM_SPACE_NOT_ENOUGH'], "warning"));
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
                        exit($this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_AUTHOR_BY_NUM'],Xphp::$_lang['UI_PLATFORM_VIRTU_MACHINE_NOT_ENOUGH'] , "warning"));
                    }
                }
            }

            //检查文件代理和数据库代理授权
            if($authType == 2){
                //租户内按个数
                $allFs =  $tenantAuthInfo['fs'] + intval($info['fs']);
                $allDb = $tenantAuthInfo['db'] + intval($info['db']);
                if($allFs > $fsNum){
                    exit($this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_AUTHOR_BY_NUM'], Xphp::$_lang['UI_PLATFORM_FAGENT_NOT_ENOUGH'], "warning"));
                }

                if($allDb > $dbNum){
                    exit($this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_AUTHOR_BY_NUM'], Xphp::$_lang['UI_PLATFORM_DBAGENT_NOT_ENOUGH'], "warning"));
                }
            }
        }

    }

   /**
     * 获取所有租户授权情况
     * @param string $tenantUuid 租户uuid
     */
    public function getAllTenantAuthInfo($tenantUuid){ 
        $sql = "select config from bd_tenant where tenant_uuid != ?";
        $data = $this->dbSelect($sql, array($tenantUuid));
        $storageSize = 0;
        $vm = 0;
        $aws =0;
        $file = 0;
        $db = 0;
        $os = 0;
        $nas = 0;
        $m365 = 0;
        $obs = 0;
        $hadoop = 0;
        if(!empty($data)){
            foreach ($data as $d) {
                $config = json_decode($d['config'], true);
                $authWay = intval($config['auth_way']);
                if($authWay == 1){
                    //按容量
                    if($config['quota_size'] != -1){
                        $storageSize += intval($config['quota_size']);
                    }
                }else if($authWay == 2){
                    //按个数
                    $vm += intval($config['vm_num']);
                    $aws += intval($config['aws_num']);
                    $file += intval($config['file_num']);
                    $db += intval($config['db_num']);
                    $os += intval($config['os_num']);
                    $nas += intval($config['nas_num']);
                    $m365 += intval($config['m365_num']);
                    $obs += $config['obs_num'] ? intval($config['obs_num']) : 0;//因为是后面加的字段，需要兼容处理
                    $hadoop += $config['hadoop_num'] ? intval($config['hadoop_num']) : 0;
                }
            }
        }
        $info = array(
            'storage_size' => $storageSize,
            'vm_num' => $vm,
            'aws_num' => $aws,
            'file_num' => $file,
            'db_num' => $db,
            'os_num' => $os,
            'nas_num' => $nas,
            'm365_num' => $m365,
            'obs_num' => $obs,
            'hadoop_num' => $hadoop,
        );
        return $info;
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
            $sql = "select sum(write_size) as used_size from bd_backup_timepoint where module_type != ".Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']." and user_uuid in('".$userDes."') ";
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
     * 获取可以给租户分配的剩余容量，限容量授权
     */
    public function getSystemFreeStorage(){
        //租户外检查是否为容量授权
        $systemHandler = Xphp::instance('SystemHandler');
        $licenseTypeInfo = $systemHandler->getSystemLicenseType();
        $licenseTypeInfo = json_decode($licenseTypeInfo, true);
        $licenseType = $licenseTypeInfo['licensetype'];
        $maxSpace = 0;
        //容量授权配额按容量授权总大小显示
        if($licenseType == Xphp::$_config['LISENCE_INFO']['type']['storage']){
            $maxSpace = $licenseTypeInfo['storage_size'];
        }
        $tenantAllInfo = $this->getAllTenantAuthInfo("");
        $freeSize = 0;
        if($maxSpace != 0){
            $freeSize = $maxSpace - $tenantAllInfo['storage'];
        }

        $utils = Xphp::instance('Utils');
        $freeDes = $utils->calSize($freeSize,true);

        $info = array(
            'licenseType' => $licenseType,
            'freeDes' => $freeDes
        );

        return json_encode($info);
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
        $sql ="select agent_uuid, agent_type, hostname from bd_agent where agent_type != 4 and user_uuid in ('".$userDes."')";
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
     * 检查租户内可用数量是否足够
     * @param unknown $vmNum
     */
    public function checkTenantAuth($type, $info, $taskuuid = "", $numFlag = false,$tenantUuid = ''){
        $tenantUuid = $tenantUuid ? $tenantUuid : $_SESSION['tenantuuid'];
        $settings = $this->pGetTenantSettings($tenantUuid);
        if($settings['auth_way'] == 2 || $numFlag){
            //获取租户下所有用户
            $userList = $this->getTenantAllUser($tenantUuid);
            $userDes = implode("','", $userList);
            $sql = "";
            $authNum = 0;
            $currentNum = count($info);
            $sqlParams = array();
            switch (intval($type)){
                case Xphp::$_config['MODULE_TYPE']['VM']:
                    $authNum = intval($settings['vm_num']);
                    $sql = "select count(distinct vml.machine_id) as total from vm_machine_list vml, bd_task bt where bt.task_uuid = vml.task_uuid and bt.user_uuid in('".$userDes."') and bt.task_type = ? ";
                    $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP']);
                    break;
                case Xphp::$_config['MODULE_TYPE']['FS']:
                    $authNum = intval($settings['file_num']);
                    $agentDes = implode("','", $info);
                    $sql = "select count(distinct btal.agent_uuid) as total from bd_task_agent_list btal, bd_task bt where bt.task_uuid = btal.task_uuid and bt.user_uuid in('".$userDes."') and btal.agent_uuid not in ('".$agentDes."') and bt.task_type = ? and bt.sub_module_type = ?";
                    $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['SUBMODULE_TYPE']['FS']);
                    break;
                case Xphp::$_config['MODULE_TYPE']['DB']:
                    $authNum = intval($settings['db_num']);
                    $agentDes = implode("','", $info);
                    $sql = "select count(distinct btal.agent_uuid) as total from bd_task_agent_list btal, bd_task bt where bt.task_uuid = btal.task_uuid and bt.user_uuid in('".$userDes."') and btal.agent_uuid not in ('".$agentDes."') and bt.task_type = ?";
                    $sqlParams = array(Xphp::$_config['TASKTYPE']['DB_BACKUP']);
                    break;
                case Xphp::$_config['MODULE_TYPE']['OS']:
                    $authNum = intval($settings['os_num']);
                    $sql = "select count(distinct ol.agent_uuid) as total from os_list ol, bd_task bt where bt.task_uuid = ol.task_uuid and bt.user_uuid in('".$userDes."') and bt.task_type = ? ";
                    $sqlParams = array(Xphp::$_config['TASKTYPE']['OS_BACKUP']);
                    break;
                case Xphp::$_config['MODULE_TYPE']['NAS']:
                    $currentNum = 1;//因为nas一次任务只能有一个nas,占用一个ip
                    $authNum = intval($settings['nas_num']);
                    $agentDes = implode("','", $info);
                    $sqlNas = "select distinct fpl.agent_uuid as nas_uuid from fs_path_list fpl, bd_task bt where bt.task_uuid = fpl.task_uuid and bt.user_uuid in('".$userDes."') and fpl.agent_uuid not in ('".$agentDes."') and bt.task_type = ? and bt.sub_module_type = ?";
                    $dataNas = $this->dbSelect($sqlNas, array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['SUBMODULE_TYPE']['NAS']));
                    if (!empty($dataNas)) {
                        $nasUuids = array_column($dataNas, 'nas_uuid');
                        $nasDes = implode("','", $nasUuids);
                        $sql = "SELECT COUNT(DISTINCT ip) AS total FROM nas_storage_resource WHERE nas_uuid IN ('".$nasDes."')";
                    } else {
                        if($numFlag){
                            return 0;
                        }
                        return true;
                    }
                    break;
                case Xphp::$_config['MODULE_TYPE']['M365']:
                    $authNum = intval($settings['m365_num']);
                    $sql = "select count(vml.machine_id) as total from vm_machine_list vml, bd_task bt where bt.task_uuid = vml.task_uuid and bt.user_uuid in('".$userDes."') and bt.task_type = ?";
                    break;
                case Xphp::$_config['MODULE_TYPE']['OBS']:
                    $authNum = intval($settings['obs_num']);
                    $sql = "select count(distinct btal.agent_uuid) as total from bd_task_agent_list btal, bd_task bt where bt.task_uuid = btal.task_uuid and bt.user_uuid in('".$userDes."') and bt.task_type = ? and bt.sub_module_type = ?";
                    $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['SUBMODULE_TYPE']['OBS']);
                    break;
                case Xphp::$_config['MODULE_TYPE']['HADOOP']:
                    $authNum = intval($settings['hadoop_num']);
                    $sql = "select count(distinct btal.agent_uuid) as total from bd_task_agent_list btal, bd_task bt where bt.task_uuid = btal.task_uuid and bt.user_uuid in('".$userDes."') and bt.task_type = ? and bt.sub_module_type = ?";
                    $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['SUBMODULE_TYPE']['HADOOP']);
                    break;
            }
            //修改任务检查
            if(!empty($taskuuid)){
                $sql .= " and bt.task_uuid != ? ";
                $sqlParams = array_merge($sqlParams, array($taskuuid));
            }
            $data = $this->dbSelect($sql, $sqlParams);
            $allNum = $currentNum + intval($data[0]['total']);
            if($numFlag){
                return intval($data[0]['total']);
            }
            if($allNum > $authNum){
                exit($this->muOpResult(false, Xphp::$_lang['WEB_TENANT_AVAILABLE_CHECK'], Xphp::$_lang['WEB_TENANT_AVAILABLE_CHECK_ERROR'], "warning"));
            }
        }else if(empty($settings['auth_way'])){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_TENANT_AVAILABLE_CHECK'], Xphp::$_lang['UI_PLATFORM_TENANT_NOT_AUTH'], "warning"));
        }

        return true;
    }


}
?>