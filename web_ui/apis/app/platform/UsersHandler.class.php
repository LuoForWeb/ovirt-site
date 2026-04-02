<?php
/******************************************* 
** 用户管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-04-10 下午15:16:44 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
class UsersHandler extends OPHandler{
    /**
     * 登录验证
     * 1:登录成功
     * 2:用户名密码错误
     * 3:用户被锁定
     * @param array $params [username,password]
     */
    public function login($params){
        $username = $params['username'];
        $password = $params['password'];
        
        $sql = "select user_uuid, password, user_type, email, lock_flag, permission, login_error_count, 
                max_login_error_count, language from bd_user where binary user_name = ?";
        $users = parent::dbSelect($sql, array($username));
        
        if(empty($users)){
            //用户名或密码错误,用户名不存在
            $result = 2;
            return json_encode($result);
        }
        
        $userUUID = $users[0]['user_uuid'];
        $lockFlag = $users[0]['lock_flag'];
        $userType = $users[0]['user_type'];
        $email = $users[0]['email'];
        $permission = $users[0]['permission'];
        $language = $users[0]['language'];
        $errorCount = $users[0]['login_error_count'];
        $maxCount = $users[0]['max_login_error_count'];
        
        if(2 == $lockFlag){
            //用户被锁定
            $utils = Xphp::instance('Utils');
            $logLevel = Xphp::$_config['LOGLEVEL']['WARN'];
            $errorCode = $utils->getErrorNum('PF_USER_LOGIN_LOCK_ERROR');
            $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_FAILURE', array(), $logLevel, $errorCode);
            $result = 3;
            return json_encode($result);
        }
        
        if($password != $users[0]['password']){
            //用户名或密码错误,密码错误
            $utils = Xphp::instance('Utils');
            $logLevel = Xphp::$_config['LOGLEVEL']['ERROR'];
            $errorCode = $utils->getErrorNum('PF_USER_USER_PASS_ERROR');
            $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_FAILURE', array(), $logLevel, $errorCode);
            $this->failureLogin($userUUID, $errorCount, $maxCount);
            $result = 2;
            return json_encode($result);
        }
        
        //登录成功
        $this->successLogin($username, $userUUID, $userType, $email, $permission, $language);
        $result = 1;
        return json_encode($result);
    }
    
    /**
     * 用户登录成功后续处理
     * @param string $userUUID
     * @param stirng $permission
     */
    private function successLogin($username, $userUUID, $userType, $email, $permission, $language){
        $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_SUCCESS');
        $systemHandler = Xphp::instance('SystemHandler');
        
        $softwareType = $systemHandler->getSoftwareType();
        
        session_start();
        $_SESSION['userName'] = $username;
        $_SESSION['userUUID'] = $userUUID;
        $_SESSION['userType'] = $userType;
        $_SESSION['email'] = $email;
        $_SESSION['permission'] = $this->getSoftwareAdminPermission($softwareType, $username, explode("|", $permission));      //授权成功后需要再更新
        $_SESSION['language'] = $language;
        $_SESSION['oemFlag'] = $systemHandler->getSoftwareIsOem();
        $_SESSION['softwareType'] = $softwareType;                                       //授权成功后需要再更新
        session_commit();
        //刷新登录错误次数为0
        $sql = "update bd_user set login_error_count = '0', last_login_time = ? 
                where user_uuid = ?";
        return parent::dbQuery($sql, array(date("Y-m-d H:i:s"), $userUUID));
    }
    
    /**
     * 根据软件版本得到不同的系统默认管理员权限
     * @param int $softwareType
     */
    private function getSoftwareAdminPermission($softwareType, $username, $permissionUser = array()){
        //默认是企业版权限,如果是标准版的话改成标准版权限
        $permissionConf = include CONF_PATH . 'permission.php';
        $permission = $permissionConf['administrator'];
        if(!empty($permissionUser) && "admin" != $username){
            $permission = $permissionUser;
        }
        if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD']){
            $permission = $permissionConf['admin'];
        }
        
        return $permission;
    }
    
    /**
     * 系统信息改变后需要更新admin 的 session信息
     * 软件通过授权后可以在版本见切换
     */
    public function updateUserPermissionAndSoftwareType(){
        session_start();
        $userType = intval($_SESSION['userType']);
        if($userType < Xphp::$_config['USERTYPE']['manager']){
            //如果是操作员和审计员,不需要做更新
            session_commit();
            return true;
        }
        $systemHandler = Xphp::instance('SystemHandler');
        $softwareType = $systemHandler->getSoftwareType();
        $_SESSION['permission'] = $this->getSoftwareAdminPermission($softwareType);
        $_SESSION['softwareType'] = $softwareType;
        session_commit();
        return true;
    }
    
    //得到用户的一些信息
    public function getUserExtendInfo($params){
        $info = array(
            'usertype' => Xphp::$_user['usertype'],
        );
        return json_encode($info);
    }
    
    /**
     * 用户登录失败后续处理
     * @param stirng $username
     */
    private function failureLogin($userUUID, $errorCount, $maxCount){
        if(($errorCount + 1 ) >= $maxCount){
            //登录错误次数超过最大登录上限,锁定用户
            $sql = "update bd_user set lock_flag = '2' where user_uuid = ?";
            return parent::dbQuery($sql, array($userUUID));
        }else{
            //错误登录次数+1,并更新数据库
            $newErrorCount = $errorCount + 1;
            $sql = "update bd_user set login_error_count = ? where user_uuid = ?";
            return parent::dbQuery($sql, array($newErrorCount, $userUUID));
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
     * 新建用户
     * @param unknown $params
     */
    public function addUser($params){
        $this->checkManagerPermission();
        $username = $params['username'];
        $password = $params['password'];
        $email = $params['email'];
        $phone = $params['phone'];
        $userType = $params['usertype'];
        $userType = Xphp::$_config['USERTYPE'][$userType];
        $permissionType = $params['permissiontype'];
        $permission = $this->getAddAndEditUserPermission($params['usertype'], $permissionType, $params['permission']);
        $createTime = date("Y-m-d H:i:s");
        $createUserName = Xphp::$_user['username'];
        $createUserUUID = Xphp::$_user['useruuid'];
        $language = Xphp::$_config['lang'];
        //生成一个UUID
        $utils = Xphp::instance("Utils");
        $userUUID = $utils->uuid();
         $quota = $params['quota'];
        $sqlExtentionParams =array($userUUID,$quota);
        $sqlParam = array($userUUID, $username, $password, $email, $phone, $userType, $permission, 
            $createTime, $createUserName, $createUserUUID, $language);
        $sql = "insert bd_user (user_uuid, user_name, password, email, telephone, user_type, permission, 
            create_time, create_user_name, create_user_uuid, language ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlExtention = "insert bd_user_extension (user_uuid,quota) values (?, ?)";
        parent::dbBeginTransaction(); //事务
        $result = parent::dbQuery($sql, $sqlParam);
        $result = $result && parent::dbQuery($sqlExtention, $sqlExtentionParams);
        if($result){
            $this->systemLog('SYSTEM_USER_ADD_SUCCESS', array("S:$username"));
            parent::dbCommit();
        }else{
            parent::dbRollBack();
        }
        
        return $this->muOpResult($result, Xphp::$_lang['WEB_USERS_ADD_USER']);
    }
    
    /**
     * 修改用户
     * @param unknown $params
     */
    public function editUser($params){
        $this->checkManagerPermission();
        $username = $params['username'];
        $password = $params['password'];
        $email = $params['email'];
        $phone = $params['phone'];
        $permissionType = $params['permissiontype'];
        $permission = $this->getAddAndEditUserPermission($params['usertype'], $permissionType, $params['permission']);
		 $quota = $params['quota'];
        //获取原始密码比对,如果原始密码的MD5的MD5和新的密码一样,就使用原来的密码,反之用新密码
        //两次MD5是因为发送到界面有一次MD5,然后界面发送回来还有一次MD5
        $sql = "select password, user_uuid from bd_user where user_name = ?";
        $data = $this->dbSelect($sql, array($username));
		$userUUid = $data[0]['user_uuid'];
        if($data){
            $password = ($password == md5(md5($data[0]['password']))) ? $data[0]['password'] : $password; 
        }
        $sqlParam = array($password, $email, $phone, $permission, $username);
        $sql = "update bd_user set password = ?, email = ?, telephone = ?, permission = ? where user_name = ?";
         $sqlExtension = "update bd_user_extension set quota = ? where user_uuid = ?";
        parent::dbBeginTransaction(); //事务
        $result = parent::dbExec($sql, $sqlParam);
        $result = $result && parent::dbExec($sqlExtension, array($quota, $userUUid));
        if($result){
        	parent::dbCommit();
        }else{
        	parent::dbRollBack();
        }
        return $this->muOpResult($result, Xphp::$_lang['UI_PLATFORM_EDIT_USER']);
    }
    
    /**
     * 删除用户
     * @param unknown $params
     */
    public function deleteUser($params){
        $this->checkManagerPermission();
        $users = $params['users'];
        $flag = $this->deleteCheck($users);
        if($flag == false){
        	$info = array(
        		'flag' => $flag
        	);
        	return json_encode($info);
        }
        $userIDs = $this->getDeleteUserIDs($users);
        $this->deleteUserCheck($userIDs);
        parent::dbBeginTransaction();
        $userName = '';
        foreach ($users as $user){
            $flag = $this->checkDeleteUserAvailable($user);
            if(!$flag){
                return parent::muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER'], Xphp::$_lang['WEB_USERS_DELETE_TASK_TIPS']);
            }
            $userName .= $this->getUsername($user) . ", ";
            $sql = "delete from bd_user where user_uuid = ?";
            if(!parent::dbQuery($sql, array($user))){
                parent::dbRollBack();
                $userName = '';
                return parent::muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER']);
            }
        }
        parent::dbCommit();
        $userName = substr($userName, 0, -2);
        $this->systemLog('SYSTEM_USER_DELETE_SUCCESS', array("S:$userName"));
        return parent::muOpResult(true, Xphp::$_lang['WEB_USERS_DELETE_USER']);
    }
    
    /**
     * 删除用户检查
     * @param array $users
     */
    private function deleteUserCheck($users){
        $this->checkAgent($users);
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
            exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER'], $msg, warning));
        }
        return true;
    }
    
    /**
     * 检查代理端
     * @param unknown $users
     */
    private function checkAgent($users){
        //TODO
        return true;
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
     * 解锁用户
     * @param unknown $params
     */
    public function unlockUser($params){
        $this->checkManagerPermission();
        $users = $params['users'];
        parent::dbBeginTransaction();
        foreach ($users as $user){
            $sql = "update bd_user set lock_flag = '1',login_error_count = '0' where user_uuid = ?";
            if(!parent::dbQuery($sql, array($user))){
                parent::dbRollBack();
                return parent::muOpResult(false, Xphp::$_lang['WEB_USERS_UNLOCK_USER']);
            }
            $userName .= $this->getUsername($user) . ", ";
        }
        parent::dbCommit();
        $userName = substr($userName, 0, -2);
        $this->systemLog('SYSTEM_USER_UNLOCK_SUCCESS', array("S:$userName"));
        return parent::muOpResult(true, Xphp::$_lang['WEB_USERS_UNLOCK_USER']);
    }
    
    /**
     * 锁定用户
     * @param unknown $params
     */
    public function lockUser($params){
        
    }
    
    /**
     * 检测用户是否可以删除
     * @param unknown $useruuid
     */
    private function checkDeleteUserAvailable($useruuid){
        return true;
    }
    
    /**
     * 根据用户UUID获取用户的基本信息,用于修改用户
     * @param unknown $params
     */
    public function getEditUserInfo($params){
        $userUUID = $params['useruuid'];
        $this->paramsCheck($userUUID);
        $sql = "select user_name, password, email, telephone, user_type, permission from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($userUUID));
		$sqlExtension = "select quota from bd_user_extension where user_uuid = ?";
      	$dataExtension = $this->dbSelect($sqlExtension,array($userUUID));
        $info = array();
        $usertypeDes = array('', 'operator', 'auditor', 'manager');
		$utils = Xphp::instance('Utils');        if($data){
            $userPermission = explode('|', $data[0]['permission']);
            $info = array(
                "username" => $data[0]['user_name'],
                "pass" => md5($data[0]['password']),
                "email" => $data[0]['email'],
                "telephone" => $data[0]['telephone'],
                "usertype" => $usertypeDes[intval($data[0]['user_type'])],
                "userpermission" => $userPermission,
				"quota" => $utils->calSize($dataExtension[0]['quota']),
            );
        }
        return json_encode($info);
    }
    
    /**
     * 根据用户类型得到用户操作权限
     * @param string $params [usertype]用户类型:administrator,auditor,manager,operator
     * @return string
     */
    public function getUserPermission($params){
        $permission = require_once CONF_PATH . 'permission.php';
        $page = require_once CONF_PATH . 'page.php';
        
        $userTree = $this->getUserAllTreeNodes($page, 0);
        $userInfo = array(
            'nodes' => $userTree,
            'manager' => $permission["manager"],
            'operator' => $permission["operator"],
            'auditor' => $permission["auditor"],
        );
        
        return json_encode($userInfo);
    }
    
    /**
     * 得到完整的用户权限树(递归)
     * @param array $page   所有配置的页面
     */
    private function getUserAllTreeNodes($page, $pid){
        $tree = array();
        //默认选中的节点
        $checkNode = array("homepage");
        foreach ($page as $p){
            $node = array(
                "id" => $p['name'],
                "pid" => $pid,
                "name" => '<i class="' . $p['class'] . '"></i> ' . Xphp::$_lang[$p['title']],
                "title" => Xphp::$_lang[$p['title']],
                "open" => true,
                "nocheck" => false,
                "type" => $p['level']
            );
            if(in_array($p['name'], $checkNode)){
                $node['checked'] = true;
                $node['chkDisabled'] = true;
            }
            if(!empty($p['child'])){
                //如果有子目录
                if($p['level'] != 2){
                	$node['children'] = $this->getUserAllTreeNodes($p['child'], $p['name']);
                }
            }
            $tree[] = $node;
        }
        
        return $tree;
    }
    
    /**
     * 检查用户是否注册
     * @param string $params [username]
     * @return string
     */
    public function usernameAvailable($params){
        $username = $params['username'];
        $sql = "select id from bd_user where user_name = ?";
        $users = parent::dbSelect($sql, array($username));
        return json_encode(empty($users));
    }
    
    /**
     * 得到所有用户信息
     * @param unknown $params
     */
    public function getUsersInfo($params){
        $this->checkManagerPermission();
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'user_name', 'user_type', 'create_time', 'create_user_name', 'email', 'telephone', 'last_login_time', 'lock_flag');
        
        $sql = "select  user_uuid, user_name, user_type, unix_timestamp(create_time) create_time, create_user_name, email, telephone, 
            unix_timestamp(last_login_time) last_login_time, lock_flag from bd_user ";
        $sqlCount = "select count(id) as total from bd_user ";
        if(Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['administrator']){
            $sql .= "where create_user_uuid = ? ";
            $sqlCount .= "where create_user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
            $sqlCountParams = array(Xphp::$_user['useruuid']);
        }else{
            $sqlParams = array($start, $length);
            $sqlCountParams = array();
        }
        //如果是超级管理员,显示所有用户
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $users = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);
        
        $utils = Xphp::instance("Utils");
        
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        foreach ($users as $user){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $user['user_uuid'] .'">',
                $id,
                $user['user_name'],
                $utils->getUserTypeDes($user['user_type']),
                $this->parseDate($user['create_time']),
                $user['create_user_name'],
                $user['email'],
                $user['telephone'],
                $this->parseDate($user['last_login_time']),
                $this->getLockDes($user['lock_flag']),
            );
            $id++;
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 修改密码
     * @param unknown $params
     */
    public function editPassword($params){
        $username = Xphp::$_user['username'];
        $oldPassword = $params['oldPassword'];
        $newPassword = $params['newPassword'];
        $this->paramsCheck($username, $oldPassword, $newPassword);
        $sql = "update bd_user set password = ? where user_name = ? and password = ?";
        $result = $this->dbQuery($sql, array($newPassword, $username, $oldPassword));
        if($result){
            $this->systemLog('SYSTEM_USER_EDIT_PASSWORD_SUCCESS');
        }
        return $this->muOpResult($result, Xphp::$_lang['WEB_USERS_EDIT_PASS']);
    }
    
    /**
     * 用户自己修改资料
     * @param unknown $params
     */
    public function editSelfInfo($params){
        $username = $params['username'];
        $email = $params['email'];
        $telephone = $params['telephone'];
        $language = $params['language'];
        $oldlang = $params['oldlang'];
        $this->paramsCheck($username, $language);
        $sql = "update bd_user set email = ?, telephone = ?, language = ? where user_name = ? and user_uuid = ?";
        $params = array($email, $telephone, $language, $username, Xphp::$_user['useruuid']);
        $result = $this->dbQuery($sql, $params);
        if($result){
            $this->systemLog('SYSTEM_USER_EDIT_INFO_SUCCESS');
            if($oldlang != $language){
                session_start();
                $_SESSION['language'] = $language;
                session_commit();
            }
        }
        return $this->muOpResult($result, Xphp::$_lang['WEB_USERS_EDIT_INFO']);
    }
    
    /**
     * 得到用户自己的信息
     * @param unknown $params
     */
    public function getUserSelfInfo($params){
        $username = Xphp::$_user['username'];
        $operate = Xphp::$_lang['WEB_USERS_GET_CURRENT_INFO'];
        $sql = "select user_name, email, telephone, language from bd_user where user_name = ?";
        $data = $this->dbSelect($sql, array($username));
        $info = array();
        if($data){
            $info = array(
                "re" => true,
                "username" => $data[0]['user_name'],
                "email" => $data[0]['email'],
                "telephone" => $data[0]['telephone'],
                "language" => $data[0]['language'],
                "list" => $this->getSystemLangList(),
            );
            return json_encode($info);
        }else{
            return $this->muOpResult(false, $operate);
        }
    }
    
    /**
     * 得到当前管理员所管理的所有操作员用户
     * @param unknown $params
     */
    public function getOperatorUsers($params){
        $sql = "select user_uuid, user_name from bd_user where create_user_uuid = ? and user_type = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid'], Xphp::$_config['USERTYPE']['operator']));
        $info = array();
        foreach ($data as $d){
            $info[] = array(
                'uuid' => $d['user_uuid'],
                'name' => $d['user_name'],
            );
        }
        //添加管理员本人
        $info[] = array(
            'uuid' => Xphp::$_user['useruuid'],
            'name' => Xphp::$_user['username'],
        );
        return json_encode($info);
    }
    
    /**
     * 获取系统预定义语言包列表
     */
    private function getSystemLangList(){
        $langConf = include_once CONF_PATH . 'lang_conf.php';
        $lang = $langConf['lang'];
        $langDes = $langConf['langDes'];
        $list = array();
        foreach ($lang as $key => $value){
            $list[] = array($value, $langDes[$key]);
        }
        return $list;
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
     * 根据用户UUID得到用户名
     * @param string $useruuid
     */
    private function getUsername($useruuid){
        $sql = "select user_name from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        if($data){
            return $data[0]['user_name'];
        }
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
        exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_PERMISSION_CHECK']));
    }
    
    /**
     * 提交用户反馈接口
     * @param unknown $params
     */
    public function feedback($params){
        $info = $params['info'];
        $phone = $params['phone'];
        $email = $params['email'];
        $this->paramsCheck($info);
        $p = array(
            'm' => Xphp::$_config['API_MODULE']['Feedback'],
            'f' => 'userFeedback',
            'p' => array(
                'tel' => $phone,
                'email' => $email,
                'info' => $info
            )
        );
        $operate = Xphp::$_lang['WEB_USERS_SUBMIT_FEEDBACK'];
        return $this->remoteApi($operate, $p);
    }
    
    /**
     * 发送短信接口
     * @param unknown $params
     */
    public function sendSms($params){
        $sql = "select sms_send_type, sms_device, sms_device_config from bd_sms_notice";
        $data = $this->dbSelect($sql);
        $smsSendType = intval($data[0]['sms_send_type']);
        if(Xphp::$_config['SMS_CONFIG']['SEND_TYPE']['INTERNET'] == $smsSendType){
            //短信平台发送
            return $this->sendSmsInernet($params);
        }elseif (Xphp::$_config['SMS_CONFIG']['SEND_TYPE']['MODEM'] == $smsSendType){
            //短信猫发送
            return $this->sendSmsModem($params, json_decode($data[0]['sms_device_config'], true));
        }
    }
    
    /**
     * 短信平台发送短信
     * @param unknown $params
     * @return string
     */
    public function sendSmsInernet($params){
        $tels = $params['tels'];
        $msg = $params['msg'];
        $sendTime = $params['sendTime'];    //定时发送时间,传入时间戳
        if(empty($sendTime)){
            $sendTime = '';
        }else{
            $sendTime = date("YmdHis", $sendTime);
        }
        $this->paramsCheck($tels, $msg);
        $p = array(
            'm' => Xphp::$_config['API_MODULE']['Sms'],
            'f' => 'sendSms',
            'p' => array(
                'tels' => $tels,        //电话号码,用英文逗号分隔,支持多个
                'msg' => $msg,          //短信内容
                'sendTime' => $sendTime,//发送时间 ,没有的话为空
            )
        );
        $operate = Xphp::$_lang['WEB_USERS_SEND_SMS_TO'] . $tels;
        $remoteApiResult = $this->remoteApi($operate, $p);
        
        $this->updateSmsQuantity($remoteApiResult);
        return $remoteApiResult;
    }
    
    /**
     * 短信猫发送短信(直接插入短信猫设备MySQL数据库,并检查发送结果)
     * @param unknown $params
     * @param $config 短信猫的数据库配置,这里注意传入的所有参数都参照数据库配置,特别注意存入数据库的密码是加密处理的
     */
    public function sendSmsModem($params, $config){
        $server = $config['ip'] . ":" . $config['port'];
        $username = $config['user'];
        $password = Xphp::instance('Utils', 'decrypt', $config['pass']);
        $database = $config['database'];
        $tels = mysql_real_escape_string($params['tels']);
        $msg = mysql_real_escape_string($params['msg']) ;
        $operate = Xphp::$_lang['WEB_USERS_SEND_SMS_TO'] . $tels;
        
        
        
        $conn=mysql_connect($server, $username, $password);
        if(FALSE === $conn){
            $errorNum = Xphp::instance('Utils', 'getErrorNum', 'PF_SETTING_NOTICE_SMSMODEM_CONNECT_DB_ERROR');
            return $this->muOpResult(false, $operate, '', '', $errorNum);
        }
        
        mysql_query("set names 'utf8'");
        mysql_select_db($database);
        
        $sql = "insert into smsserver_out (type,recipient,text,encoding,create_date,gateway_id) values 
                ('O', '" . $tels . "', '" . $msg . "', 'U',now(),'*');";
        $result = mysql_query($sql,$conn);
        
        if(!$result){
            $errorNum = Xphp::instance('Utils', 'getErrorNum', 'PF_SETTING_NOTICE_SMSMODEM_INSERT_DB_ERROR');
            return $this->muOpResult(false, $operate, '', '', $errorNum);
        }
        $id = mysql_insert_id();
        
        $flag = true;
        $i = 0;
        $sleepTime = Xphp::$_config['SMS_CONFIG']['SLEEPTIME'];
        $checkTime = Xphp::$_config['SMS_CONFIG']['CHECKTIME'];
        
        
        $sendResult = false;
        while($flag){
            //循环检查发送结果
            $sql ="select status from smsserver_out where id = $id";
            $result = mysql_query($sql,$conn);
            $row = mysql_fetch_array($result);
            if($row[0] != "S" && $i < $checkTime){
                //如果发送失败,并且检查次数少于设定的阈值,睡眠几秒后继续检查
                sleep($sleepTime);
                $i++;
                continue;
            }
            $flag = false;
            if($row[0] == "S"){
                //发送成功
                $sendResult = true;
            }
        }
        
        return $this->muOpResult($sendResult, $operate);
    }
    
    /**
     * 更新短信剩余量
     * @param unknown $params
     */
    private function updateSmsQuantity($params){
        $utils = Xphp::instance(Utils);
        $params = $utils->object_array(json_decode($params));
        $quantity = $params['ext']['quantity'];
        if(empty($params['ext'])){
            return true;
        }
        if($quantity >= 0){
            $sql = "update bd_sms_notice set sms_quantity = ?";
            $this->dbQuery($sql, array($quantity));
        }
        return true;
    }
    
    /**
     * 发送邮件接口
     * @param unknown $params
     */
    public function sendEmail($params){
        $title = $params['title'];
        $email = $params['email'];
        $info = $params['info'];
        $attachment = $params['attachment'];
        $this->paramsCheck($info, $title, $email);
        $emailStr = implode($email, ',');
        $p = array(
            'm' => Xphp::$_config['API_MODULE']['Xemail'],
            'f' => 'sendEmail',
            'p' => array(
                'email' => $email,
                'title' => $title,
                'info' => $info,
                'attachment' => $attachment
            )
        );
        //获取邮件配置
        $sql = "select smtp_config from bd_email_notice";
        $emailConf = Xphp::$_config['EMAIL'];
        $data = $this->dbSelect($sql, array());
        $utils = Xphp::instance('Utils');
        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        
        $pass = $utils->decrypt($smtpConfig['pass']);
        $encryption = intval($smtpConfig['encryption']);
        $encryption = Xphp::$_config['EMAIL_ENCRYPTION_TYPE'][$encryption];
        //直接调用发送邮件接口
        $emailUtils = Xphp::instance('Email');
        $emailConfig = Xphp::$_config['EMAIL'];
        $emailUtils->config($smtpConfig['host'], $smtpConfig['port'], $emailConfig['authentication'],
            $smtpConfig['email'], $pass, $encryption);
        $result = $emailUtils->sendmail($email, $title, $info, $attachment);
        $operate = Xphp::$_lang['WEB_USERS_SEND_EMAIL_TO'] . $emailStr;
        
        if($result){
        	$this->writeLog("send Email success!!!!!!!!!!");
            return $this->muOpResult(true, $operate);
        }else {
        	$this->writeLog("send Email failed!!!!!!!!!!");
            return $this->muOpResult(false, $operate);
        }
    }
    
    /**
     * 用户体验改善计划接口
     * @param unknown $params
     */
    public function ueiplan($params){}
    
    /**
     * 调用远程API
     * @param sting $operate    用户操作描述
     */
    public function remoteApi($operate, $params){
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $thumbprint = $mbResult['msg']['thumbprint'];
        if(!$result){
            $operate = $this->opcodeHandler->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $thumbprint, '', $mbResult['errorCode']);
        }
        $params['p']['thumbprint'] = $thumbprint;
        $params['p']['user'] = Xphp::$_config['REMOTE']['api_user'];
        $params['p']['pass'] = md5(Xphp::$_config['REMOTE']['api_pass']);
        $params['p']['enterprise'] = Xphp::$_config['SYSTEM_INFO']['enterprise'];
        $curl = Xphp::instance('Curl');
        $result = $curl->oldPost(Xphp::$_config['REMOTE']['api_url'], $params);
        $utils = Xphp::instance(Utils);
        $result = $utils->object_array(json_decode($result));
        
        if($result['re']){
            //成功
            return $this->muOpResult($result['re'], $operate, '', '', 0, $result['ext']);
        }else{
            //失败
            if(!empty($result['code'])){
                $msg = $operate . Xphp::$_lang['WEB_PUBLIC_FAILURE'] . "," . Xphp::$_lang['WEB_OPHANDLER_ERROR_CODE'] . ":" . $result['code'];
            }
            if(!empty($result['ext']['info'])){
                $msg .= "," . Xphp::$_lang['WEB_USERS_EXTEND_INFO'] . ":" . $result['ext']['info'];
            }
            return $this->muOpResult(false, $operate, $msg, '', 0, $result['ext']);
        }
        
    }
    
	/**
     * 文件备份客户端打开web控制台
     * 1:后台传输agentuuid
     * 2:根据agentuuid找到user的username,password
     * 3:执行this->login
     * 4：返回设置好session的JSON数据
     * @param array $params [username,password]
     */
    public function fileClientLogin($params){
    	$agentuuid = $_GET['agentuuid'];
    	$sql = "select bu.user_uuid, bu.password, bu.user_type,bu.user_name
            from bd_user bu,bd_agent ba where bu.user_uuid = ba.user_uuid and ba.agent_uuid = ?";
    	$users = $this->dbSelect($sql, array($agentuuid));
    	$params = array(
    			'username' => $users[0]['user_name'],
    			'password' => $users[0]['password']
    	);
    
    
    	$loginResult = $this->login($params);
    	if(1 == $loginResult){
    		//如果登录成功，证明这个agentuuid对应的用户名密码是可以用的，这里返回session，首页直接赋值
    		return json_encode($_SESSION);
    	}else{
    		//如果失败，返回false跳转到登录页面
    		return false;
    	}
    
    }
    
	public function deleteCheck($user){
		$user = $user[0];
		$sql = "select user_uuid, user_name from bd_user where create_user_uuid = ?";
		$data = $this->dbSelect($sql, array($user));
		if($data){
			return false;
		}
		return true;
	}  

	public function getSystemLang(){
		$sql = "select language from bd_user";
		$data = $this->dbSelect($sql);
		return $data[0]['language'];
	}
}
?>