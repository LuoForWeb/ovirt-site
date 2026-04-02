<?php
/*******************************************
 ** 角色管理类
 **
 ** @author       xiezhuowei@vinchin.com;luokai@vinchin.com;liushuai@vinchin.com
 ** @date         2020-09-10 下午17:04:00
 ** @version      1.0.0
 ** @copyright    Copyright 2020 vinchin.com
 ********************************************/
class RoleHandler extends OPHandler{
    /**
     * 获取角色列表
     * @param unknown $params
     */
    public function getRoleLists($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];//排序的参数
        $sortType = $params['sortType'];//排序类型
        $sortParams = array('','br.role_name','br.lock_flag','bt.tenant_name', 'br.create_user_name', 'br.create_time');
        $sql = "select br.id, br.role_uuid, br.role_name, br.lock_flag, br.create_user_name, br.create_time, bt.tenant_name, bt.tenant_uuid from bd_role br left join bd_tenant bt on br.tenant_uuid = bt.tenant_uuid ";
        $count_sql = "select count(br.role_uuid) as count_all from bd_role br ";
        $sqlParams = array($start,$length);
        $sqlCountParams = array();
        //检查是否是租户管理员
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
//         if(empty($_SESSION['tenantuuid']) && Xphp::$_user['username'] == "admin"){
            
//             $sqlParams = array($start, $length);
//             $sqlCountParams = array();
//         }else if($tenantMangerFlag){
//             $sql .= " where br.tenant_uuid = ? ";
//             $count_sql .= " where br.tenant_uuid = ?";
//             $sqlParams = array( $_SESSION['tenantuuid'], $start,$length);
//             $sqlCountParams = array( $_SESSION['tenantuuid']);
//         }else{
            /*$sql .= "where br.create_user_uuid = ? ";
            $count_sql .= "where br.create_user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
            $sqlCountParams = array(Xphp::$_user['useruuid']);*/
//         }
        
        $where = "";
        $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
        $sqlCountParams = array(Xphp::$_user['useruuid']);

        if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
            $where .= " br.create_user_uuid = ? or br.create_user_uuid = '' ";
        } else {
            $where .= " br.create_user_uuid = ? ";
        }

        // 未授权租户相关的权限模块 角色列表就不显示租户相关的名称
        // 说明，之所以不直接用模糊查询 租户 关键词， 是因为英文的需要一起兼容
        if (!in_array('tenant', $_SESSION['permission'])) {
            // 未授权 那么屏蔽系统初始化的租户相关的三个角色列表
            $where = "(" . $where . ") and br.role_uuid not in ('eee859d5-341a-b95a-33d0-6ed583d0f7a1','2b214439-8f0b-ec23-a3be-2014ad9baecc','03e12c93-9dc1-371a-6a64-b74965d36723')";
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
            $where = "(" . $where . ") and br.role_uuid in ('{$threeRoleStr}')";
        } else {
            $where = "(" . $where . ") and br.role_uuid not in ('{$threeRoleStr}')";
        }

        $sql .= " where " . $where . " and br.role_uuid != 'a30f7728-2ef7-bca0-2224-07deba8ce3e5'";
        $count_sql .= " where " . $where . " and br.role_uuid != 'a30f7728-2ef7-bca0-2224-07deba8ce3e5'";
        
        $sql .=" order by $sortParams[$sortColumn] $sortType, id  limit ?,?";

        $data = $this->dbSelect($sql, $sqlParams);
        $result_count_role = $this->dbSelect($count_sql, $sqlCountParams);
        $count_role = $result_count_role[0]['count_all'];
        $info = array();
        $info['data'] = array();
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        if(!empty($data)){
            foreach ($data as $d){
                $flag_content = $pfDes['ROLE_LOCK_FLAG_DES'][intval($d['lock_flag'])];
                if($d['tenant_name'] == null){
                    $tenant_name = Xphp::$_lang['UI_ROLE_GLOBAL'];
                    $checkBox = '<input type="checkbox" name="id[]" value="'. $d['role_uuid'] .'">';
                }else{
                    $tenant_name = $d['tenant_name'];
                }

                // 超级管理员权限不能修改
                if ($d['role_uuid'] == 'a30f7728-2ef7-bca0-2224-07deba8ce3e5') {
                    $checkBox = '';
                } else {
                    $checkBox = '<input type="checkbox" name="id[]" value="' . $d['role_uuid'] . '">';
                }
                
                $createTime = $d['create_time'];
                if($d['create_time'] == "0000-00-00 00:00:00"){
                    $createTime = Xphp::$_config['TIMESPACE'];
                }
                
                //出厂默认模板不可操作
//                 if(empty($d['create_user_name'])){
//                     $checkBox = "";
//                 }
                //创建者
                $createUser = $d['create_user_name'];
                if(empty($createUser)){
                    $createUser = Xphp::$_config['NULLSPACE'];
                }
                $info['data'][] = array(
                    $checkBox,
                    $d['role_name'],
                    $flag_content,
                    $tenant_name,
                    $createUser,
                    $createTime,
                    $d['role_uuid'],
                    $d['tenant_uuid'],
                    $d['create_user_name']
                );
                
            }
        }
        $info["draw"] = $params['draw'];
        $info["recordsTotal"] = $count_role;
        $info["recordsFiltered"] = $count_role;
        return json_encode($info);
    }
    
    /**
     * 添加角色
     * @param unknown $params
     */
    public function addRole($params){
        //权限检查
        $this->pOperationPermissionCheckExit("p_safety_role_add");
        $rolename = $params['rolename'];      //角色名字
        $opName = Xphp::$_lang['UI_ROLE_ADD'];
        //检查名字是否重复
        $this->checkRolenameExist($rolename, 'UI_ROLE_ADD','UI_ROLE_IDENTICAL');
        
        $permission = json_encode($params['permission']);   //page权限集合
        $tenantuuid = "";//租户唯一标识
        //如果是租户内部创建
        if(!empty($_SESSION['tenantuuid'])){
            $tenantuuid = $_SESSION['tenantuuid'];
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
        $permission_name = $params['rolename'];
        $permissionParams = array($permissionUUID,$permission_name,$permission_type, $permission);
        $permissionSql = "insert into bd_permission (permission_uuid, name, type, content) values (?, ?, ?, ?)";
        parent::dbBeginTransaction(); //事务开始
        
        $result = parent::dbQuery($roleSql, $roleParams);
        $result = $result && parent::dbQuery($permissionSql, $permissionParams);
        
        if($result){
            //事务结束
            //添加系统操作日志
            $this->systemLog('SYSTEM_ROLE_ADD_SUCCESS', array($rolename));
            parent::dbCommit();
        }else{
            //事务回滚
            parent::dbRollBack();
        }
        
        return $this->muOpResult($result,$opName);
    }
    
    /**
     * 修改角色
     * @param unknown $params
     */
    public function editRole($params){
        //权限检查
        $this->pOperationPermissionCheckExit("p_safety_role_edit");
        $roleUUID = $params['roleuuid'];
        $permissionUUID = $params['permissionuuid'];
        $permission_name = $params['rolename'];

        //检查名字是否重复
        $this->checkRolenameExist($permission_name, 'UI_ROLE_MODIFY','UI_ROLE_IDENTICAL', $roleUUID);
        
        $permission = json_encode($params['permission']);
        $config = "";
        $tenantuuid = "";
        
        //如果是租户内部创建
        if(!empty($_SESSION['tenantuuid'])){
            $tenantuuid = $_SESSION['tenantuuid'];
        }
        $editTime = date('Y-m-d H:i:s');
        //向角色表中插入数据

        $roleParams = array($config, $permission_name, $tenantuuid, $editTime, $roleUUID);
        $roleSql = "update bd_role set config = ?, role_name = ?, tenant_uuid = ?, create_time = ? where role_uuid = ?";
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
        }else{
            //事务回滚
            parent::dbRollBack();
        }
        
        return $this->muOpResult($result,Xphp::$_lang['UI_ROLE_MODIFY']);
    }
    
    /**
     * 删除角色
     * @param unknown $params
     */
    public function deleteRole($params){
        //权限检查
        $this->pOperationPermissionCheckExit("p_safety_role_delete");
        $rolelist = $params['rolelist'];
        $roleStr = implode("','",$rolelist);
        $sql = "delete from bd_role where role_uuid in ('"."$roleStr"."')";
        $result = $this->dbQuery($sql);
        
        return $this->muOpResult($result, Xphp::$_lang['UI_ROLE_DELETE']);
    }
    
    /**
     * 禁用角色
     * @param unknown $params
     */
    public function lockRole($params){
        //权限检查
        $this->pOperationPermissionCheckExit("p_safety_role_disable");
        $rolelist = $params['rolelist'];
        $roleStr = implode("','",$rolelist);
        $sql = "update bd_role set lock_flag= ? where role_uuid in ('"."$roleStr"."')";
        $result = $this->dbExec($sql, array(Xphp::$_config['FLAG']['UNSET']));
        
        return $this->muOpResult($result, Xphp::$_lang['UI_ROLE_DISABLE']);
        
    }
    
    /**
     * 启用角色
     * @param unknown $params
     */
    public function unlockRole($params){
        //权限检查
        $this->pOperationPermissionCheckExit("p_safety_role_enable");
        $rolelist = $params['rolelist'];
        $roleStr = implode("','",$rolelist);
        $sql = "update bd_role set lock_flag= ? where role_uuid in ('"."$roleStr"."')";
        $result = $this->dbExec($sql, array(Xphp::$_config['FLAG']['SET']));
        
        return $this->muOpResult($result,Xphp::$_lang['UI_ROLE_ENABLE']);
    }
    
    /**
     * 得到用户所有角色
     * @param string $useruuid  用户UUID
     * @return array
     */
    public function getUserAllRole($useruuid){
        //TODO 获取用户的角色,再获取用户所在用户组的角色,组合求并
        return array();
    }
    
    
    /**
     * 得到角色用来修改的信息
     * @param unknown $params
     */
    public function getRoleOldInfo($params){
        $roleuuid = $params['roleuuid'];
        $sql = "select br.role_name, br.tenant_uuid, bp.content, bp.permission_uuid  from bd_role br, bd_permission bp where br.permission_uuid = bp.permission_uuid and br.role_uuid = ? ";
        $data = $this->dbSelect($sql, array($roleuuid));
        $info = array();
        
        if(!empty($data)){
            foreach ($data as $d){
                $info = array(
                    'rolename' => $d['role_name'],
                    'tenantuuid' => $d['tenant_uuid'],
                    'permission' => json_decode($d['content'], true),
                    'permissionuuid' => $d['permission_uuid']
                );
            }
        }
        
        return json_encode($info);
    }
    
    /**
     * 公共方法
     * 获取用户角色(直接分配给用户的角色,不包括用户所在用户组的角色)
     * @param string $useruuid  用户uuid
     * @author xiezhuowei@vinchin.com
     * @return array 多维数组,对应bd_role表所有字段
     */
    public function pGetUserRole($useruuid){
        $this->paramsCheck($useruuid);
        $sql = "select distinct br.role_uuid, br.role_name, br.permission_uuid, br.config, br.lock_flag, br.tenant_uuid from
                bd_user bu, mt_user_role mur, bd_role br
                where bu.user_uuid = mur.user_uuid and mur.role_uuid = br.role_uuid and bu.user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        return $data;
    }
    
    /**
     * 公共方法
     * 获取用户所有角色(包括用户所在用户组的角色)
     * @param string $useruuid  用户uuid
     * @author xiezhuowei@vinchin.com
     * @return array 多维数组,对应bd_role表所有字段
     */
    public function pGetUserAllRole($useruuid){
        $this->paramsCheck($useruuid);
        //先获取用户的直接关联角色
        $sql = "select distinct br.role_uuid, br.role_name, br.permission_uuid, br.config, br.lock_flag, br.tenant_uuid from 
                bd_user bu, mt_user_role mur, bd_role br 
                where bu.user_uuid = mur.user_uuid and mur.role_uuid = br.role_uuid and bu.user_uuid = ?";
        $data1 = $this->dbSelect($sql, array($useruuid));
        //再获取用户所在用户组关联的角色
        $sql = "select distinct br.role_uuid, br.role_name, br.permission_uuid, br.config, br.lock_flag, br.tenant_uuid from
                bd_user bu, mt_user_user_group muug, mt_user_group_role mugr, bd_role br
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugr.user_group_uuid 
                and mugr.role_uuid = br.role_uuid and bu.user_uuid = ?";
        $data2 = $this->dbSelect($sql, array($useruuid));
        
        //合并两类角色,先合并,再去重
        $data = array_merge($data1, $data2);
        $utils = Xphp::instance('Utils');
        $data = $utils->unique_multidim_array($data, "role_uuid");
        return $data;
    }

    
    /**
     * 公共方法
     * 获取用户组所有角色
     * @param string $userGoupUUID  用户组uuid
     * @author xiezhuowei@vinchin.com
     * @return array 多维数组,对应bd_role表所有字段
     */
    public function pGetUserGroupRole($userGoupUUID){
        $this->paramsCheck($userGoupUUID);
        $sql = "select distinct br.role_uuid, br.role_name, br.permission_uuid, br.config, br.lock_flag, br.tenant_uuid from
                bd_user_group bug, mt_user_group_role mugr, bd_role br
                where bug.user_group_uuid = mugr.user_group_uuid and mugr.role_uuid = br.role_uuid and bug.user_group_uuid = ?";
        $data = $this->dbSelect($sql, array($userGoupUUID));
        return $data;
    }
    
    /**
     * 公共方法
     * 获取用户所有权限(包括用户和所在用户组的权限)
     * @param string $useruuid  用户uuid
     * @author xiezhuowei@vinchin.com
     * @return array 一维数组,合并后的所有权限
     */
    public function pGetUserAllPermission($useruuid, $pageFlag = false){
        $this->paramsCheck($useruuid);
        $utils = Xphp::instance('Utils');
        if ($useruuid == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            // 超级管理员直接以授权的为准,那么默认的 permission 这个就是读取所有的菜单配置
            $permission = $utils->v1_get_all_name();
        } else {
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
            $data = $utils->unique_multidim_array($data, "permission_uuid");

            //合并所有权限
            $permission = array();
            foreach ($data as $d) {
                $permission = array_merge($permission, json_decode($d['content'], true));
            }
            $permission = array_unique($permission);
        }

        //租户内用户强制不显示副本和归档
        $userHandler = Xphp::instance('UsersHandler');
        /*$tenantuuid = $userHandler->pGetUserTenantUUID($useruuid);
        if(!empty($tenantuuid)){
            $permission = array_diff($permission, ["datacopy", "data_archive", "vm_overview", "orch", "setting_manager", "authorization_module", "storage_report"]);
        }*/
        //角色权限创建以及页面显示
        if($pageFlag){
            return $userHandler->getAuthSoftwarePermission($permission, $useruuid);
        }else{
            $systemHandler = Xphp::instance('SystemHandler');
            $extension = $systemHandler->getExtensionLicense();
            if(!empty($extension)){
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
    * 获取page里面设置的所有的方法操作
     */
    public function pGetPageFunctions()
    {
        $page = require CONF_PATH . 'page.php';
        $utils = Xphp::instance('Utils');
        return $utils->multiToTwos($page);
    }

    /**
    * 获取page权限的最后一层的所有数组
     * 获取到方法层次的所有权限数组
     * @param array $permission 最终的授权左侧菜单模块数组
     * @param string $userUUID 登录的用户ID
     * @return array 二维数组,合并后的所有权限
     */
    public function pGetPageFunction (array $permission, string $userUUID) {
        $page = require CONF_PATH . 'page.php';
        $utils = Xphp::instance('Utils');
        $pageArr = $utils->multiToTwo($page); // 父类(name) => array( 类(name) => array( 'method1', 'method2'))

        // 和左侧的菜单最终授权比较， 保留有权限的子类  子类(name) => array( 'method1', 'method2')
        // 1也就是取出的是最终的授权文件下的所有类和方法
        $array = [];
        foreach ($pageArr as $key=>$item) {
            if (in_array($key, $permission)) {
                // 如果存在这个父类 那么这个类下面的所有子类就保留
                foreach ($item as $key2=>$item2) {
                    $array[$key2] = $item2;
                }
            }
        }

        // 获取后台授权的数组(不包含授权文件)
        $permission2 = $this->pGetUserAllPermission($userUUID);

        // 根据授权的name数组从所有的方法数组里面获得最终的二维方法数组
        // 2这步就是在第一步取出的授权文件下面的类和方法在和后台赋权的交集的所有类和方法
        $return = [];
        foreach ($permission2 as $item) {
            if (isset($array[$item]) && !empty($array[$item])) {
                if (is_array($array[$item])) {
                    foreach ($array[$item] as $items) {
                        $arrays = explode('-', $items);
                        $return[$arrays[0]][] = $arrays[1];
                    }
                } else {
                    $arrays = explode('-', $array[$item]);
                    $return[$arrays[0]][] = $arrays[1];
                }
            }
        }
        return $return;
    }

    /**
     * 公共方法 根据传递来的最终的授权数组， 获取最终后台授权到操作层面的授权数组
     * @param array $permission 最终的授权数组（不包含最底层的方法操作）
     * @param string $typeId 类型ID type为0表示用户ID，1表示角色ID，2表示分组ID
     * @param int $type 类型 默认用户，1角色，2分组
     * @return array 一维数组,合并后的所有权限
     */
    public function gGetPageLatest(array $permission, string $typeId, int $type = 0): array
    {
        $page = require CONF_PATH . 'page.php';
        $utils = Xphp::instance('Utils');
        $pageArr = $utils->multiToTwo($page); // 父类(name) => array( 类(name) => array( 'method1', 'method2'))

        // 和左侧的菜单最终授权比较， 保留有权限的子类  子类(name) => array( 'method1', 'method2')
        // 1也就是取出的是最终的授权文件下的所有类和方法
        $array = [];
        foreach ($pageArr as $key=>$item) {
            if (in_array($key, $permission)) {
                // 如果存在这个父类 那么这个类下面的所有子类就保留
                foreach ($item as $key2=>$item2) {
                    $array[] = $key2;
                }
            }
        }

        // 获取后台授权的数组(不包含授权文件)
        if ($type === 1) {
            $permission2 = $this->pGetRoleAllPermission($typeId, false);
        } elseif ($type === 2) {
            $permission2 = $this->pGetUserGroupAllPermission($typeId, false);
        } else {
            $permission2 = $this->pGetUserAllPermission($typeId);
        }

        // 2取出后台授权数组和所有的操作数组的交集 就是最终得到的所有的操作数组
        $result = array_intersect($permission2, $array);

        $systemHandler = Xphp::instance('SystemHandler');
        $extension = $systemHandler->getExtensionLicense();
        if(!empty($extension)){
            $authFun = $extension['f'];
            // 这里需要判读下可视化大屏的权限，因为这个从授权系统那边过来的
            // 如果是master的话 那么主动追加上 p_visual_screen 大屏权限

            if (
                (
                    ($typeId == 'a30f7728-2ef7-bca0-2224-07deba8ce3e5' && $type == 1) ||
                    ($typeId == 'e99ae858-d549-4754-9310-457477f4c2e3' && $type == 2) ||
                    ($typeId == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9' && $type == 0)
                )
                && $authFun['visualization']
            ) {
                $permission = array_merge($permission, ['p_visual_screen']);
            }

            // 重新授权没有大屏的，那么这里直接给去掉
            if (!$authFun['visualization']) {
                $permission = array_diff($permission, ['p_visual_screen']);
            }
        }

        return array_unique(array_merge($permission, $result)); // 合并返回原来的授权数组
    }
    
    /**
     * 公共方法
     * 获取用户所有权限(包括用户组的权限)
     * @param string $usergroupuuid  用户uuid
     * @author luokai@vinchin.com
     * @return array 一维数组,合并后的所有权限
     */
    public function pGetUserGroupAllPermission($usergroupuuid, $pageFlag = true){
        $this->paramsCheck($usergroupuuid);
        $utils = Xphp::instance('Utils');
        if ($usergroupuuid == 'e99ae858-d549-4754-9310-457477f4c2e3') {
            // 如果是超级管理组 那么以菜单的配置为准
            $permission = $utils->v1_get_all_name();
        } else {
            //再获取用户所在用户组关联的角色对应的权限
            $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content from
                bd_user_group bug, mt_user_group_role mugr, bd_role br, bd_permission bp
                where bug.user_group_uuid = mugr.user_group_uuid and mugr.role_uuid = br.role_uuid and br.permission_uuid = bp.permission_uuid and bug.user_group_uuid = ? and br.lock_flag = ?";
            $data = $this->dbSelect($sql, array($usergroupuuid, Xphp::$_config['FLAG']['SET']));
            $permission = array();

            //合并所有权限
            if(!empty($data)){
                $data = $utils->unique_multidim_array($data, "permission_uuid");
                foreach ($data as $d){
                    $permission = array_merge($permission, json_decode($d['content'], true));
                }
                $permission = array_unique($permission);
            }
        }

        //租户内去掉项目
        /*$userHandler = Xphp::instance('UsersHandler');
        $tenantuuid = $userHandler->pGetUserGroupTenantUUID($usergroupuuid);
        if(!empty($tenantuuid)){
            $permission = array_diff($permission, ["datacopy", "data_archive", "vm_overview", "orch", "setting_manager", "authorization_module", "storage_report"]);
        }*/
        if ($pageFlag) {
            //在根据系统授权筛选
            $permission = $this->pGetSystemMergePermission($permission);
        }

        return $permission;
        
    }
    
    /**
     * 公共方法
     * 获取用户所有权限(包括用户组的权限)
     * @param string $roleuuid  用户uuid
     * @author luokai@vinchin.com
     * @return array 一维数组,合并后的所有权限
     */
    public function pGetRoleAllPermission($roleuuid, $pageFlag = true){
        $this->paramsCheck($roleuuid);
        $utils = Xphp::instance('Utils');
        if ($roleuuid == 'a30f7728-2ef7-bca0-2224-07deba8ce3e5') {
            // 超级管理组以授权的为准,返回所有配置的菜单权限
            $permission = $utils->v1_get_all_name();
        } else {
            //再获取用户所在用户组关联的角色对应的权限
            $sql = "select distinct bp.permission_uuid, bp.name, bp.type, bp.content from
                bd_role br, bd_permission bp where br.permission_uuid = bp.permission_uuid and br.role_uuid = ?";
            $data = $this->dbSelect($sql, array($roleuuid));
            $permission = array();

            //合并所有权限
            if(!empty($data)){

                $data = $utils->unique_multidim_array($data, "permission_uuid");
                foreach ($data as $d){
                    $permission = array_merge($permission, json_decode($d['content'], true));
                }
                $permission = array_unique($permission);
            }
        }

        $roleName = $this->pGetRoleNameById($roleuuid);
        //租户默认角色限制副本、归档、灾难演练
        /*if($roleName == "Tenant Admin" || $roleName == "Tenant Operator"){
            $permission = array_diff($permission, ["datacopy", "data_archive", "vm_overview", "orch", "setting_manager", "authorization_module", "storage_report"]);
        }*/

        if ($pageFlag) {
            //在根据系统授权筛选
            $permission = $this->pGetSystemMergePermission($permission);
        }

        return $permission;
        
    }
    
    
    /**
     * 公共方法
     * 判断某个用户是否有某个操作的权限
     * 只判断到用户是否能够做某种操作,没有检查是否对特定资源是否具有权限
     * @param string $useruuid  用户uuid
     * @param string $operation 操作,对应page页面的每一项
     * @author xiezhuowei@vinchin.com
     * @return boolean  有权限返回true,否则返回false
     */
    public function pOperationPermissionCheck($useruuid, $operation){
        //如果是admin系统管理员直接返回
        if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9") return true;
        $this->paramsCheck($useruuid, $operation);
        $permission = $this->pGetUserAllPermission($useruuid);
        $result = in_array($operation, $permission);
        return $result;
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
    public function pOperationPermissionCheckExit($operation){
        // 权限校验放在登录之后获取保存的session数组里面 permissionArr 这里不需要做任何校验
        return true;
        //如果是admin系统管理员直接返回
        if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9") return true;
        $this->paramsCheck($operation);
        $permission = $this->pGetUserAllPermission(Xphp::$_user['useruuid']);
        $result = in_array($operation, $permission);
        if(!$result){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ROLE_OPERATION_NOT_AUTH'], Xphp::$_lang['WEB_ERROR_BD_ERRNO_EACCES'], "error"));
        }
        return true;
    }
    
    
    
    /**
     * 初始化角色表格数据
     *
     *
     */
    public function getRoleInfo($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];//排序的参数
        $sortType = $params['sortType'];//排序类型
        $sortParams = array('','br.role_name','br.lock_flag','bt.tenant_name');
        $sql = "select br.role_uuid, br.role_name,br.lock_flag, bt.tenant_name, bt.tenant_uuid from bd_role br left join bd_tenant bt on br.tenant_uuid = bt.tenant_uuid";
        $sql .=" order by $sortParams[$sortColumn] $sortType limit ?,?";
        $data = $this->dbSelect($sql,array($start,$length));
        $count_sql = "select count(role_uuid) as count_all from bd_role";
        $result_count_role = $this->dbSelect($count_sql);
        $count_role = $result_count_role[0]['count_all'];
        $info = array();
        $info['data'] = array();
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        foreach ($data as $d){
            $flag_content = $pfDes['ROLE_LOCK_FLAG_DES'][intval($d['lock_flag'])];
            if($d['tenant_name'] == null){
                $tenant_name = Xphp::$_lang['UI_ROLE_GLOBAL'];
            }else{
                $tenant_name = $d['tenant_name'];
            }
            $info['data'][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['role_uuid'] .'">',
                $d['role_name'],
                $flag_content,
                $tenant_name,
                $d['role_uuid'],
                $d['tenant_uuid']
            );
            
        }
        $info["draw"] = $params['draw'];
        $info["recordsTotal"] = $count_role;
        $info["recordsFiltered"] = $count_role;
        return json_encode($info);
    }
    
    /**
     * 检查角色名字是否创建重复
     * @param string $roleName  角色名字
     * @param string $opName 操作提示名称标识
     * @param string $msg 操作提示内容标识
     * @param string $role_uuid 编辑时的角色标识
     * @return boolean
     */
    public function checkRolenameExist (string $roleName, string $opName, string $msg, $role_uuid = '')
    {
        $sql = "select role_uuid from bd_role where role_name = ? ";
        $data = $this->dbSelect($sql, array($roleName));
        if (!empty($data)) {
            if (!empty($role_uuid) && count($data) == 1 && $role_uuid == $data[0]['role_uuid']) {
                // 编辑 并且只存在一个的情况下 并且是相同
                return true;
            }
            // 其它直接抛出
            exit($this->muOpResult(false, Xphp::$_lang[$opName], Xphp::$_lang[$msg], "warning"));
        }
        
        return true;
    }
    
    /**
     * 公共方法
     * 获取角色关联所有用户
     * @param string $roleuuid    角色uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_user表所有字段
     */
    public function pGetRoleAllUser($roleuuid){
        $this->paramsCheck($roleuuid);
        $sql = "select distinct bu.user_uuid, bu.user_name, bu.email, bu.telephone, bu.lock_flag, bu.create_time, bu.language, bu.user_type, bt.tenant_name
                from bd_role br, mt_user_role mur, bd_user bu left join mt_user_tenant mut on mut.user_uuid = bu.user_uuid left join bd_tenant bt on bt.tenant_uuid = mut.tenant_uuid 
                where br.role_uuid = mur.role_uuid and mur.user_uuid = bu.user_uuid and br.role_uuid = ? and bu.user_uuid != ? ";
        $data = $this->dbSelect($sql, array($roleuuid, Xphp::$_user['useruuid']));
        return $data;
    }
    
    /**
     * 公共方法
     * 获取角色关联所有用户组
     * @param string $roleuuid     角色uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_user_group表所有字段
     */
    public function pGetRoleAllUserGroup($roleuuid){
        $this->paramsCheck($roleuuid);
        $sql = "select distinct bug.user_group_uuid, bug.user_group_name, bug.user_group_type, bug.lock_flag, bug.description, bug.config
                from bd_role br, bd_user_group bug, mt_user_group_role mugr
                where br.role_uuid = mugr.role_uuid and mugr.user_group_uuid = bug.user_group_uuid and br.role_uuid = ?";
        $data = $this->dbSelect($sql, array($roleuuid));
        return $data;
    }
    
    /**
     * 获取角色权限树
     * @param unknown $params
     * @return string
     */
    public function getRolePermissionTree($params){
        $roleuuid = $params['roleuuid'];
        $userHandler = Xphp::instance('UsersHandler');
        $permission = require_once CONF_PATH . 'permission.php';
        $page = require_once CONF_PATH . 'page.php';
        $rolePermission = $this->pGetRoleAllPermission($roleuuid);

        // 更改 获取更深层次的操作
        $rolePermission = $this->gGetPageLatest($rolePermission, $roleuuid, 1);

        $userTree = $userHandler->getUserAllTreeNodes($page, 0, $rolePermission, true);
        
        $userInfo = $userTree;


        
        return json_encode($userInfo);
    }
    
    /**
     * 获取用户关联用户组
     * @param unknown $params
     * @return string
     */
    public function getRoleUser($params){
        $roleuuid = $params['roleuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $userList = $this->pGetRoleAllUser($roleuuid);
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $count = count($userList);
        $utils = Xphp::instance('Utils');
        $userList = $utils->arraySort($userList, 'user_name', 'asc', $start, $length);
        foreach ($userList as $d){
            $username = $d['user_name'];
            if(empty($_SESSION['tenantuuid']) && !empty($d['tenant_name'])){
                $username .= '(' .$d['tenant_name']. ')';
            }
            $records["data"][] = array(
                $id,
                $username,
            );
            $id++;
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;
        
        return  json_encode($records);
    }
    
    /**
     * 获取用户关联用户组
     * @param unknown $params
     * @return string
     */
    public function getRoleUserGroup($params){
        $roleuuid = $params['roleuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $userGroupList = $this->pGetRoleAllUserGroup($roleuuid);
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $count = count($userGroupList);
        $utils = Xphp::instance('Utils');
        $userGroupList = $utils->arraySort($userGroupList, 'user_group_name', 'asc', $start, $length);
        foreach ($userGroupList as $d){
            $records["data"][] = array(
                $id,
                $d['user_group_name'],
            );
            $id++;
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;
        
        return  json_encode($records);
    }
    
    /**公共方法
     * 根据角色唯一标识获取角色名
     * @param unknown $roleuuid
     * @return string|fetchAll()
     */
    private function pGetRoleNameById($roleuuid){
        $sql = "select role_name from bd_role where role_uuid = ?";
        $data = $this->dbSelect($sql, array($roleuuid));
        $name = "";
        if(!empty($data)){
            $name = $data[0]['role_name'];
        }
        
        return $name;
    }
    
    /**
     * 查询出的权限合并系统授权展示
     * @param unknown $permission
     * @return unknown|unknown[]
     */
    public function pGetSystemMergePermission($permission){
        //获取系统授权页面
        $systemHandler = Xphp::instance('SystemHandler');
        $extension = $systemHandler->getExtensionLicense();
        $pagelist = array();
        if(!empty($extension)){
            $pagelist = $extension['p'];
        }
        
        $newPermission = array();
        //系统已授权
        if(!empty($pagelist)){
            foreach ($permission as $p){
                if(in_array($p, $pagelist)){
                    $newPermission[] = $p;
                }
            }
        }else{
            //系统未授权用原来的
            $newPermission = $permission;
        }
        
        return $newPermission;
    }


    /**
     * 获取当前任务历史任务的高级搜索任务类型
     */
    public function getTaskByPermission(){
        $info = array();
        //获取授权
        $permission = $_SESSION['permission'];
        if(empty($permission) && !$_SESSION['isThreePowers']){
            // 并且不是三权模式
            return json_encode($info);
        }
        //这里需要检测的模块类型,键值对,键为模块类型的值,值为page里面的name
        $module_type_list = array(
            Xphp::$_config['MODULE_TYPE']['VM'] => 'vmprotect',
            Xphp::$_config['MODULE_TYPE']['PUBLIC_CLOUD'] => 'awsprotect',
            Xphp::$_config['MODULE_TYPE']['PRIVATE_CLOUD'] => 'cloud_platform_private',
            Xphp::$_config['MODULE_TYPE']['FS'] => 'fileprotect',
            Xphp::$_config['MODULE_TYPE']['DB'] => 'db_protect',
            Xphp::$_config['MODULE_TYPE']['OS'] => 'os_protect',
            Xphp::$_config['MODULE_TYPE']['NAS'] => 'nas_protect',
            Xphp::$_config['MODULE_TYPE']['VOL_CDP'] => 'vol_cdp_protect',
            Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'] => 'dbprotect',
            Xphp::$_config['MODULE_TYPE']['M365'] => 'office365_protect',
            Xphp::$_config['MODULE_TYPE']['HADOOP'] => 'hadoop_protect',
            Xphp::$_config['MODULE_TYPE']['OBS'] => 'obs_protect',
            Xphp::$_config['MODULE_TYPE']['FILE_COPY'] => 'file_copy_protect',
        );

        //判断是否存在页面即是否有权限
        foreach ($module_type_list as $key => $value) {
            if (in_array($value, $permission) || $_SESSION['isThreePowers']) {
                $info[$key] = $value;
            }
        }
        return json_encode($info);
    }


    //根据所选模块类型来确定其任务类型有哪些
    public function getTaskTypeByPermission($params){
        $module_type = $params['module_type'];
        $info = array();
        //获取授权
        $permission = $_SESSION['permission'];
        $task_list = array();
        switch ($module_type){
            case Xphp::$_config['MODULE_TYPE']['VM']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['BACKUP'] => 'vmprotect',
                    Xphp::$_config['TASKTYPE']['RECOVERY'] => 'vmprotect_recover',
                    Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'] => 'vminstantrecover',
                    Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'] => 'vminstantrecover',
                    Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'] => 'vmrecovera',
                    // Xphp::$_config['TASKTYPE']['SURE_BACKUP'] => 'add_verification_job',
                    // Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC'] => 'cbrbackup',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['FS']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['BACKUP'] => 'filebackup',
                    Xphp::$_config['TASKTYPE']['RECOVERY'] => 'file_recovery',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['DB']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['DB_BACKUP'] => 'db_protect',
                    Xphp::$_config['TASKTYPE']['DB_RECOVERY'] => 'db_recovery',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['OS']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['OS_BACKUP'] => 'osbackup',
                    Xphp::$_config['TASKTYPE']['OS_RECOVERY'] => 'osrecover',
                    Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY'] => 'osinstantrecover',
                    Xphp::$_config['TASKTYPE']['OS_INSTANT_RECOVERY_MOTION'] => 'osinstantrecover',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['NAS']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['BACKUP'] => 'nas_protect',
                    Xphp::$_config['TASKTYPE']['RECOVERY'] => 'nasrecover',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['VOL_CDP']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] => 'vol_cdp_backup',
                    Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] => 'vol_cdp_recovery',
                    Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER'] => 'vol_cdp_takeover',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['PUBLIC_CLOUD']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['BACKUP'] => 'awsprotect',
                    Xphp::$_config['TASKTYPE']['RECOVERY'] => 'awsprotect_recover',
                    Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'] => 'awsrecovera',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['OEM_DBCDP']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'] => 'dbcdpbackup',
                    Xphp::$_config['TASKTYPE']['DB_CDP_RECOVERY'] => 'dbdataRecovery',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['FILE_COPY']:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['FILE_COPY'] => 'file_copy_protect',
                    Xphp::$_config['TASKTYPE']['FILE_COMPARE'] => 'file_copy_protect',
                );
                break;
            case Xphp::$_config['MODULE_TYPE']['UNKNOWN']:
            default:
                $task_list = array(
                    Xphp::$_config['TASKTYPE']['BACKUP'] => 'vmprotect',
                    Xphp::$_config['TASKTYPE']['RECOVERY'] => 'vmprotect_recover',
                    // Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'] => 'vminstantrecover',
                    // Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'] => 'vminstantrecover',
                    // Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'] => 'vmrecovera',
                    // Xphp::$_config['TASKTYPE']['SURE_BACKUP'] => 'add_verification_job',
                );
                break;

        }
        //判断是否存在页面即是否有权限
        foreach ($task_list as $key => $value) {
            if (in_array($value, $permission) || $_SESSION['isThreePowers']) {
                $info[$key] = $value;
            }
        }
        return json_encode($info);;
    }
    
}
?>