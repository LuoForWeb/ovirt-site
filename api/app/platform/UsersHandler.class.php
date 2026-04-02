<?php

use xphp\Curl;

/*******************************************
** 用户管理处理类
**
** @author       xiezhuowei@vinchin.com
** @date         2015-04-10 下午15:16:44
** @version      1.0.0
** @copyright    Copyright 2015 vinchin.com
********************************************/
class UsersHandler extends OPHandler{
    private $opcodeHandler;

    function __construct(){
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('PFOpcodePrivate');
    }

    /**
     * 登录验证
     * @param array $params [username,password]
     */
    public function login($params){
        $utils = Xphp::instance('utils');
        $username = $utils->decryptJsRsa($params['username']);
        $password = $utils->decryptJsRsa($params['password']);
        $lockpage = $utils->decryptJsRsa($params['page']);  //锁定页面调用
        $lockcount = $utils->decryptJsRsa($params['count']); //锁定页面输入密码的次数
        $remember = $params['remember'];

        // 登录次数记录
        session_start();
        //增加一次登录错误记录 成功后会归0 所以不影响
        $_SESSION['login_faild_num'] = empty($_SESSION['login_faild_num']) ? 1 : $_SESSION['login_faild_num'] + 1;

        // 验证验证码
        if ( $_SESSION['login_faild_num'] > 3 || !empty($params['very_code'])) {
            // 需要验证验证码
            if (strtolower($params['very_code']) !=  $_SESSION['very_code']) {
                $info=array(
                    'result' =>9,
                    'faliCount' => $_SESSION['login_faild_num'],//获取登录错误次数
                    'lockCount'=>$lockcount,

                );
                return json_encode($info);
            }
        }

        return $this->loginVerify($username, $password, $remember,$lockpage,$lockcount, FALSE);
    }

    /**
     * 登录验证过程
     * （注意这里auth方法会调用，auth是用作单点登录的，auth会传入用户名和密码，修改的时候不要修改传入参数，可以修改内容）
     * @param unknown $username 用户名
     * @param unknown $password 密码
     * @param unknown $remember 是否记住密码
     * @param unknown $lcokpage 锁定页面调用
     * @param unknown $lockcount 锁定页面输入密码的次数
     * @param bool $singleFlag 单点登录标志
     * @return string
     * 1:登录成功
     * 2:用户名密码错误
     * 3:用户被锁定
     * 4:域服务器连接错误
     * 5:密码已经过期
     */
    public function loginVerify($username, $password, $remember = FALSE,$lcokpage = '',$lockcount = '', $singleFlag = FALSE){
        $utils = Xphp::instance('utils');
        $token = array(
            'username' => $username,
            'password' => $password,
            'remember' => $remember,
        );
        $token = $utils->encrype(json_encode($token));

        $verPassword = $password;

        //如果是通过单点登录进来，密码已经经过了MD5计算，从登录界面进来需要单独加上MD5
        if(!$singleFlag){
            $password = md5($password);
        }


        $tenantUserName = $username;

        //先设置cookie不可用,js设置有安全隐患,放到这里处理
        setcookie("Token", "", -1, '/', '', true, true);

        $usernameInfo = explode("\\", $username);
        $tenantuuid = "";
        $domainFlag = false;
        if(count($usernameInfo) > 1){   //域用户、租户
            $domainFlag = true;
            $tenantName = $usernameInfo[0];
            $sql = "select tenant_uuid from bd_tenant where tenant_name = ? ";
            $dataTenant = $this->dbSelect($sql, array($tenantName));
            if(!empty($dataTenant)){
                $tenantuuid = $dataTenant[0]['tenant_uuid'];
                $username = !empty($usernameInfo[1])? $usernameInfo[1]: $usernameInfo[2];
            }
        }

        $sql = "select unix_timestamp(bu.create_time) create_time, bu.create_user_uuid, bu.domain_uuid, bu.user_uuid, bu.password, bu.user_type, bu.email, bu.lock_flag, bu.permission, bu.login_error_count,
                bu.max_login_error_count, bu.language from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid where binary bu.user_name = ? ";
        $sqlParams = array($username);
        if(!empty($tenantuuid)){
            //租户内
            $sql .= " and mut.tenant_uuid = ? ";
            $sqlParams = array($username, $tenantuuid);
        }else if($domainFlag){
            //AD域用户
            $username=$utils->escapeWildcard($username);
            $sql = 'select unix_timestamp(bu.create_time) create_time, bu.create_user_uuid, bu.domain_uuid, bu.user_uuid, bu.password, bu.user_type, bu.email, bu.lock_flag, bu.permission, bu.login_error_count,
                bu.max_login_error_count, bu.language from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid where binary bu.user_name = "'.$username.'"';
            $sqlParams = array();
        }else if($username == "admin"){
            //默认admin用户登录
            $sql .= " and user_type = 3 ";
        }else{
            //租户外其他用户
            $sql .= " and mut.tenant_uuid is null ";
        }

        $users=parent::dbSelect($sql,$sqlParams);
        if(empty($users)){
            //用户名不存在
            $result = 2;

            $info=array(
                'result' =>$result,
                'faliCount' => $_SESSION['login_faild_num'],//获取登录错误次数
                'lockCount'=>$lockcount
            );
            return json_encode($info);
        }

        $userUUID = $users[0]['user_uuid'];
        //检查租户是否锁定
        $sqlTenant = "select tenant_uuid, lock_flag from bd_tenant where tenant_uuid = ? ";
        $dataTenant = $this->dbSelect($sqlTenant, array($tenantuuid));
        $lockFlag = $users[0]['lock_flag'];
        //用户所在租户已经禁用，直接按锁定来
        if(!empty($dataTenant) && intval($dataTenant[0]['lock_flag']) == Xphp::$_config['FLAG']['UNSET']){
            $lockFlag = Xphp::$_config['FLAG']['UNSET'];
        }
        $userType = $users[0]['user_type'];
        $email = $users[0]['email'];
        $permission = $users[0]['permission'];
        $language = $users[0]['language'];
        $errorCount = $users[0]['login_error_count'];
        $maxCount = $users[0]['max_login_error_count'];
        $userTime = intval($users[0]['create_time']);
        $platformHandler = Xphp::instance('PlatformHandler');
        $safeInfo = $platformHandler->getAccountSafe();
        $passTimeout = $safeInfo['password_time'];
        $nowTime = time() - $userTime;
        $overDays = $nowTime/3600/24;

        if(2 == $lockFlag && $userType !=3){ //排除admin被锁定
            //用户被锁定
            $utils = Xphp::instance('Utils');
            $logLevel = Xphp::$_config['LOGLEVEL']['WARN'];
            $errorCode = $utils->getErrorNum('PF_USER_LOGIN_LOCK_ERROR');
            $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_FAILURE', array(), $logLevel, $errorCode);
            $result = 3;
            $info = array(
                'result' => $result
            );
            return json_encode($info);
        }

        if(!empty($safeInfo['faild_count'])){
            $maxCount = $safeInfo['faild_count'];
        }
        //如果是外部用户-域用户
        if(intval($users[0]['user_type']) == 2){
            //测试活动目录服务器连通性
            $sqlCon = "select bt.lock_flag, bds.domain_name, bds.domain_type, bds.domain_ip, bds.port, bds.tenant_uuid from bd_domain_server bds left join bd_tenant bt on bds.tenant_uuid = bt.tenant_uuid where bds.domain_uuid = ?";
            $data = $this->dbSelect($sqlCon, array($users[0]['domain_uuid']));
            $userDes = $data[0]['domain_name'] ."\\\\";
            $username = substr($username, strlen($userDes));
            $user = $username."@".$data[0]['domain_name'];
            $loginPassword = $verPassword;
            $conn = ldap_connect($data[0]['domain_ip'], $data[0]['port']);//不要写成ldap_connect($host.':'.$port)的形式'
            if ($conn) {
                //设置参数
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
                $bd = ldap_bind($conn, $user, $loginPassword);
                //获取用户所在租户是否被禁用
                $tenantLockFlag = $data[0]['lock_flag'];
                if($tenantLockFlag == Xphp::$_config['FLAG']['UNSET']){
                    $utils = Xphp::instance('Utils');
                    $logLevel = Xphp::$_config['LOGLEVEL']['WARN'];
                    $errorCode = $utils->getErrorNum('PF_USER_LOGIN_LOCK_ERROR');
                    $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_FAILURE', array(), $logLevel, $errorCode);
                    $result = 3;
                    $info = array(
                        'result' => $result
                    );
                    return json_encode($info);
                }
                if ($bd) {
                    //更新用户密码
                    $sql = "update bd_user set password = ? where user_uuid = ?";
                    $this->dbExec($sql, array($password, $userUUID));
                   //校验验证码
                    $result = 1;//相当于登录成功
                    $info=array(
                        'result' => $result
                    );
                } else {
                    session_start();
                    if($lcokpage == 'lock'){
                        if($lockcount == "3"){   //当锁定页面输错密码三次时
                            unset($_SESSION['tenantusername']); //清除session
                        }
                    }else{
                        $this->failureLogin($userUUID, $errorCount, $maxCount, $userType);
                    }
                    $utils = Xphp::instance('Utils');
                    $logLevel = Xphp::$_config['LOGLEVEL']['ERROR'];
                    $errorCode = $utils->getErrorNum('PF_USER_USER_PASS_ERROR');
                    $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_FAILURE', array(), $logLevel, $errorCode);
                    $users=parent::dbSelect($sql,$sqlParams); //再次查询数据库
                    $errorCount = $users[0]['login_error_count'];
                    $lockFlag = $users[0]['lock_flag'];
                    //锁定
                    if(2 == $lockFlag && $userType !=3){ //排除admin被锁定
                        //用户被锁定
                        $utils = Xphp::instance('Utils');
                        $logLevel = Xphp::$_config['LOGLEVEL']['WARN'];
                        $errorCode = $utils->getErrorNum('PF_USER_LOGIN_LOCK_ERROR');
                        $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_FAILURE', array(), $logLevel, $errorCode);
                        $result = 3;
                        $info = array(
                            'result' => $result
                        );
                        return json_encode($info);
                    }else{
                        $result = 2;//用户名或密码错误
                        $info=array(
                            'result' =>$result,
                            // 'faliCount' => $errorCount,//获取登录错误次数
                            'faliCount' => $_SESSION['login_faild_num'],//获取登录错误次数
                            'maxCount' => $maxCount,//最多能登录的次数
                            'lockCount'=>$lockcount
                        );
                        return json_encode($info);
                    }
                }
            } else {
                $result = 4;//域服务器连接错误
                $info=array(
                    'result'=>$result
                );
                return json_encode($info);
            }
        }else{
            if($password != $users[0]['password']){
                //用户名或密码错误,密码错误
                $utils = Xphp::instance('Utils');
                $logLevel = Xphp::$_config['LOGLEVEL']['ERROR'];
                $errorCode = $utils->getErrorNum('PF_USER_USER_PASS_ERROR');
                $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_FAILURE', array(), $logLevel, $errorCode);
//                 if(empty($tenantuuid) && $username == "admin"){
//                     //全局admin管理员用户不记录登录失败次数
//                 }else{
                    $this->failureLogin($userUUID, $errorCount, $maxCount, $userType);
//                 }
                session_start();
                if($lcokpage == 'lock'){
                    if($lockcount == "3"){   //当锁定页面输错密码三次时
                        unset($_SESSION['tenantusername']); //清除session
                    }
                }else{
                    $this->failureLogin($userUUID, $errorCount, $maxCount, $userType); //更新字段 login_error_count
                }
                $users=parent::dbSelect($sql,$sqlParams); //因为failureLogin更新了数据库，再次查询数据库
                $errorCount = $users[0]['login_error_count'];
                $lockFlag = $users[0]['lock_flag'];
                //锁定
                if(2 == $lockFlag && $userType !=3){ //排除admin被锁定
                    //用户被锁定
                    $utils = Xphp::instance('Utils');
                    $logLevel = Xphp::$_config['LOGLEVEL']['WARN'];
                    $errorCode = $utils->getErrorNum('PF_USER_LOGIN_LOCK_ERROR');
                    $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_FAILURE', array(), $logLevel, $errorCode);
                    $result = 3;
                    $info = array(
                        'result' => $result
                    );
                    return json_encode($info);
                }else{
                    $result = 2;
                    $info=array(
                        'result' => $result,
                        /*'useruuid' => $userUUID,
                        'userType' => $userType,
                        'username' => $username,*/
                        'faliCount' => $_SESSION['login_faild_num'],//获取登录错误次数
                       // 'maxCount' => $maxCount,//最多能登录的次数
                        'lockCount'=>$lockcount
                    );
                    return json_encode($info);
                }
            }
        }
        //密码过期
        if($overDays > $passTimeout && $userType !=2){
            $result = 5;    //密码已过期，请联系管理员
            // 这里进行密码强度和最低长度查询
            $safeInfo = json_decode($this->getPassComplexity(), true);
            $info = array(
                'result' => $result,
                'useruuid' => $userUUID,
                'passlength' => $safeInfo['passlength'],
                'passcomplexity' => $safeInfo['passcomplexity'],
            );
            return json_encode($info);
        }


        //登录成功
        $this->successLogin($username, $userUUID, $userType, $email, $permission, $language, $tenantUserName);
        $result = 1;
        $info=array(
            'result' => $result,
        );
        //设置cookie,如果记住密码才设置cookie
        if($remember){
            setcookie("Token", $token, time() + 99 * 365 * 24 * 3600,'/', '', true, true);
        }
        return json_encode($info);
    }

    /**
     * 单点登录获取auth
     * @param unknown $params
     */
    public function auth($params){
        $username = $params['username'];
        $password = $params['password'];

        $result = $this->loginVerify($username, $password,FALSE,'', 100, TRUE);

        $result = json_decode($result);
        if(1 == $result){
            //验证成功，error_code返回0
            $utils = Xphp::instance('Utils');
            $tokenid = $utils->encrype($username . "|" . $password . "|" . time());
            $info = array(
                'error_code' => 0,
                'token_id' => urlencode($tokenid)
            );
        }else{
            //验证失败, error_code返回验证错误码
            $info = array(
                'error_code' => $result,
                'token_id' => ""
            );
        }

        return json_encode($info);
    }

    /**
     * 用户登录成功后续处理
     * @param string $userUUID
     * @param stirng $permission
     */
    private function successLogin($username, $userUUID, $userType, $email, $permission, $language, $tenantUserName){
        //获取客户端IP
        $utils = Xphp::instance('Utils');
        $clientIP = $utils->getClientIP();
        $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_SUCCESS', array("S:". $clientIP));
        $systemHandler = Xphp::instance('SystemHandler');
        $roleHandler = Xphp::instance('RoleHandler');

        $softwareType = $systemHandler->getSoftwareType();
        $extension = $systemHandler->getExtensionLicense();
        $pagelist = array();
        $authFun = array(
            'visualization' => false,
            'lanfree' => false,
            'dedupication' => false,
            'vcbt' => false,
            'nodeExtend' => false,
            'grain' => false,
            'copy' => false,
            'archive' => false,
        );
        if(!empty($extension)){
            $pagelist = $extension['p'];
            $authFun = $extension['f'];
        }


        $permission  = $roleHandler->pGetUserAllPermission($userUUID, true);
        if($userType == 3){
            //超级管理员不需要全局管理者的权限
            $permission = array_diff($permission, ["global_observer", "global_read", "global_write"]);
        }

        session_start();
        $_SESSION['userName'] = $username;
        $_SESSION['userUUID'] = $userUUID;
        $_SESSION['userType'] = $userType;
        $_SESSION['email'] = $email;
        $_SESSION['permission'] = $permission;
        $_SESSION['permissionArr'] = $roleHandler->pGetUserAllPermission($userUUID); // 主要是为了获取方法cat的name数组
        $_SESSION['permissionFunction'] = $roleHandler->pGetPageFunction($_SESSION['permission'], $userUUID); // 具体的方法和类的校验
        $_SESSION['permissionFunctions'] = $roleHandler->pGetPageFunctions(); // page里面所有的方法操作数组
        $_SESSION['language'] = $language;
        $_SESSION['oemFlag'] = $systemHandler->getSoftwareIsOem();
        $_SESSION['softwareType'] = $softwareType;                                       //授权成功后需要再更新
        $_SESSION['authfun'] = $authFun;
        $_SESSION['tenantuuid'] = $systemHandler->getUserPermission();
        $_SESSION['tenantusername'] = $tenantUserName;

        // 默认没有大屏权限
        $permissionVisualScreen = false;
        if (empty($_SESSION['tenantuuid'])) {
            $permissionVisualScreen = true;
            //检查当前用户是否属于Master组
            $masterFlag = $this->pCheckUserIsMaster($userUUID);
            if ((!$authFun['visualization'] || !$masterFlag) && !in_array('p_visual_screen', $_SESSION['permissionArr'])) {
                $permissionVisualScreen = false;
            }
        }

        if ($permissionVisualScreen) {
            $_SESSION['permissionVisualScreen'] = 100;
        } else {
            $_SESSION['permissionVisualScreen'] = false;
        }

        // 这里记录下当前会话信息
        session_regenerate_id(true); // 重新生成cookie
        $_SESSION['BackupSystem'] = session_id();

        // 登录页验证码清除
        $_SESSION['login_faild_num'] = 0;

        session_commit();

        //刷新登录错误次数为0
        $sql = "update bd_user set login_error_count = '0', last_login_time = ? 
                where user_uuid = ?";
        parent::dbQuery($sql, array(date("Y-m-d H:i:s"), $userUUID));
    }

    /**
     * 系统信息改变后需要更新admin 的 session信息
     * 软件通过授权后可以在版本见切换
     */
    public function updateUserPermissionAndSoftwareType(){
        $systemHandler = Xphp::instance('SystemHandler');
        $extension = $systemHandler->getExtensionLicense();
        $pagelist = array();
        $authFun = array(
            'visualization' => false,
            'lanfree' => false,
            'dedupication' => false,
            'vcbt' => false,
            'nodeExtend' => false,
            'grain' => false,
            'copy' => false,
            'archive' => false,
        );
        if(!empty($extension)){
            $pagelist = $extension['p'];
            $authFun = $extension['f'];
            // 三权模式标识 threepowers 是定义在授权系统里面的授权功能里面
            $mode = !empty($_SESSION['isThreePowers']) ? 1 : 2;
            $mod2Now = !empty($authFun['threepowers']) ? 1 : 2;
            if ($mode != $mod2Now) {
                // 两次授权类型不一致 那么解绑系统的所有资源关联
                // mt_user_resource 和 mt_user_resource_group 两个表清空即可
                $this->dbExec("delete from mt_user_resource");
                $this->dbExec("delete from mt_user_resource_group");
            }
        }
        session_start();
        $userType = intval($_SESSION['userType']);
        if($userType < Xphp::$_config['USERTYPE']['manager']){
            //如果是操作员和审计员,不需要做更新
            session_commit();
            return true;
        }
        $systemHandler = Xphp::instance('SystemHandler');
        $roleHandler = Xphp::instance('RoleHandler');
        $softwareType = $systemHandler->getSoftwareType();
        $_SESSION['permission'] = $roleHandler->pGetUserAllPermission($_SESSION['userUUID'], true);
        $_SESSION['softwareType'] = $softwareType;
        session_commit();
        return true;
    }

    //得到用户的一些信息
    public function getUserExtendInfo($params){
        $info = array(
            'username' => Xphp::$_user['username'],
            'useruuid' => Xphp::$_user['useruuid'],
            'tenantuuid' => $_SESSION['tenantuuid']
        );
        return json_encode($info);
    }

    /**
     * 用户登录失败后续处理
     * @param stirng $username
     */
    private function failureLogin($userUUID, $errorCount, $maxCount, $userType){
        if(($errorCount + 1 ) >= $maxCount && $userType!=3){
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
        $utils = Xphp::instance('Utils');
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_user_add");
//         $this->checkManagerPermission();
        $username = $params['username'];
        $password = $params['password'];
        $email = $params['email'];
        $phone = $params['phone'];
        //角色列表
        $roleList = $params['roleList'];
        $userGroupList = $params['userGroupList'];
        //修改密码flag
        $editPwdFlag =$utils->parseBoolToFlag($params['editPwdFlag']);

        $tenantuuid = "";
        //如果是租户内部创建
        if(!empty($_SESSION['tenantuuid'])){
            $tenantuuid = $_SESSION['tenantuuid'];
        }
        $domainuuid = $params['domainuuid'];
        //1本地用户 2外部用户
        $userType = $params['usertype'];

        $permission = "";
        $createTime = date("Y-m-d H:i:s");
        $createUserName = Xphp::$_user['username'];
        $createUserUUID = Xphp::$_user['useruuid'];
        $language = Xphp::$_config['lang'];
        //用户配额
        $quota = intval($params['quota']);
        //检查用户配额是否超出总配额
        $this->checkUserMaxStorage($quota, Xphp::$_lang['WEB_USERS_ADD_USER']);

        //检查是否为外部用户
        if($userType == 2){
            //外部用户添加先检查用户是否存在,存在获取
            $this->checkDomainUserExist($username, $domainuuid);
            $permission = "";
            $tenantuuid = $this->getDomainTenantuuid($domainuuid);
        }


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
            $createTime, $createUserName, $createUserUUID, $language, $domainuuid, $editPwdFlag);
        $sql = "insert bd_user (user_uuid, user_name, password, email, telephone, user_type, permission, 
            create_time, create_user_name, create_user_uuid, language, domain_uuid, force_password_change_flag) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
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
        return $this->muOpResult($result, Xphp::$_lang['WEB_USERS_ADD_USER']);
    }

    /**
     * 修改用户
     * @param unknown $params
     */
    public function editUser($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_user_edit");
//         $this->checkManagerPermission();
        $username = $params['username'];
        $userUUID = $params['useruuid'];
        $password = $params['password'];
        $email = $params['email'];
        $phone = $params['phone'];
        //角色列表
        $roleList = $params['roleList'];
        //用户组列表
        $userGroupList = $params['userGroupList'];
		$permission = "";
        $quota = $params['quota'];
        //检查用户配额是否超出总配额
        $this->checkUserMaxStorage($quota, Xphp::$_lang['UI_PLATFORM_EDIT_USER']);
		//修改前先删除之前关联用户组|角色|租户
		$this->pDeleteUserUserGroup($userUUID);
		$this->pDeleteUserRole($userUUID);
        //获取原始密码比对,如果原始密码的MD5的MD5和新的密码一样,就使用原来的密码,反之用新密码
        //两次MD5是因为发送到界面有一次MD5,然后界面发送回来还有一次MD5
        $sql = "select password, user_uuid from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($userUUID));
        if($data){
            $password = ($password == md5(md5($data[0]['password']))) ? $data[0]['password'] : $password;
        }
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
        return $this->muOpResult($result, Xphp::$_lang['UI_PLATFORM_EDIT_USER']);
    }

    /**
     * 删除用户
     * @param unknown $params
     */
    public function deleteUser($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $logHandler = Xphp::instance('LogHandler');
        $alarmHandler = Xphp::instance('AlarmHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_user_delete");
//         $this->checkManagerPermission();
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
        //事务外先仔细发送到后台的操作
        foreach ($users as $user){
            //删除用户相关历史任务，任务日志和任务告警
            $this->pDeleteUserHistoryTask($user);
            $this->pDeleteUserTaskLog($user,$logHandler);
            $this->pDeleteUserSystemLog($user,$logHandler);
            $this->pDeleteUserTaskAlarm($user,$alarmHandler);
        }
        parent::dbBeginTransaction();
        $userName = '';
        foreach ($users as $user){
            $flag = $this->checkDeleteUserAvailable($user);
            if(!$flag){
                return parent::muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER'], Xphp::$_lang['WEB_USERS_DELETE_TASK_TIPS']);
            }
            //删除用户前删除用户与 用户组|角色|租户关联
            $this->pDeleteUserUserGroup($user);
            $this->pDeleteUserRole($user);
            $this->pDeleteUserTenant($user);

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
                return parent::muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER']);
            }
        }
        parent::dbCommit();
        $userName = substr($userName, 0, -2);
        $this->systemLog('SYSTEM_USER_DELETE_SUCCESS', array($userName));
        return parent::muOpResult(true, Xphp::$_lang['WEB_USERS_DELETE_USER']);
    }

    /**
     * 删除用户检查
     * @param array $users
     */
    private function deleteUserCheck($users){

        //检查任务
        $this->checkTask($users);
        //检查时间点
        $this->checkTimepoint($users);
        //检查代理
        $this->checkAgent($users);
        //检查虚拟化中心
        $this->checkVcenter($users);
    }


    /**
     * 检查VCENTER
     * @param array $users
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
                where vv.user_uuid = bu.user_uuid and bu.id in ('".$userStr."')";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            //如果虚拟化中心有虚拟机存在于任务中,直接返回
            $msg = Xphp::$_lang['WEB_USERS_USER'] . "'" . $data[0]['user_name'] . "'" .
                    Xphp::$_lang['WEB_USERS_DELETE_USER_TIPS'] . "'" . $data[0]['nickname'] . "'";
            exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER'], $msg, 'warning'));
        }
        return true;
    }

    /**
     * 检查代理端
     * @param array $users
     */
    private function checkAgent($users){
        //TODO
        $userStr = "";
        foreach ($users as $user){
            $userStr .= $user . " ,";
        }
        if($userStr){
            $userStr = substr($userStr, 0, -1);
        }
        $sql = "select ba.hostname, ba.ip, ba.agent_type, bu.user_name from bd_agent ba, bd_user bu 
                where ba.agent_type != 4 and ba.user_uuid = bu.user_uuid and bu.id in ('".$userStr."')";
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            //如果有文件和数据库代理存在
            $des = Xphp::$_lang['UI_PLATFORM_FAGENT_DEL_FIRST'];
            //数据库代理
            if($data[0]['agent_type'] == 1){
                $des = Xphp::$_lang['UI_PLATFORM_DBAGENT_DEL_FIRST'];
            }
            $msg = Xphp::$_lang['WEB_USERS_USER'] . "'" . $data[0]['user_name'] . "'" .
                $des . "'" . $data[0]['host_name'] . "'";
                exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER'], $msg, 'warning'));
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
            exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER'], Xphp::$_lang['UI_PLATFORM_TASK_DEL_FIRST'], 'warning'));
        }
        return true;
    }

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
            exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_DELETE_USER'], Xphp::$_lang['UI_PLATFORM_TIMEPOINT_DEL_FIRST'], 'warning'));
        }
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
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_user_enable");
//         $this->checkManagerPermission();
        $users = $params['users'];
        parent::dbBeginTransaction();
        foreach ($users as $user){
            $sql = "update bd_user set lock_flag = '1',login_error_count = '0' where user_uuid = ?";
            if(!parent::dbQuery($sql, array($user))){
                parent::dbRollBack();
                return parent::muOpResult(false, Xphp::$_lang['UI_USER_ENABLE']);
            }
            $userName .= $this->getUsername($user) . ", ";
        }
        parent::dbCommit();
        $userName = substr($userName, 0, -2);
        $this->systemLog('SYSTEM_USER_UNLOCK_SUCCESS', array($userName));
        return parent::muOpResult(true, Xphp::$_lang['UI_USER_ENABLE']);
    }

    /**
     * 锁定用户
     * @param unknown $params
     */
    public function lockUser($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_user_disable");
        $users = $params['users'];
        //禁用用户强制停止任务
        $this->lockUserStopJob($users);
        parent::dbBeginTransaction();
        foreach ($users as $user){
            $sql = "update bd_user set lock_flag = '2',login_error_count = '0' where user_uuid = ?";
            if(!parent::dbQuery($sql, array($user))){
                parent::dbRollBack();
                return parent::muOpResult(false, Xphp::$_lang['UI_USER_DISABLE']);
            }
            $userName .= $this->getUsername($user) . ", ";
        }
        parent::dbCommit();
        $userName = substr($userName, 0, -2);
        $this->systemLog('SYSTEM_USER_LOCK_SUCCESS', array($userName));
        return parent::muOpResult(true, Xphp::$_lang['UI_USER_DISABLE']);
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
        $sql = "select bu.user_name, bu.password, bu.email, bu.telephone, bu.user_type, bu.permission, bu.domain_uuid, bt.tenant_uuid from bd_user bu left join bd_tenant bt on bt.admin_uuid = bu.user_uuid where bu.user_uuid = ?";
        $data = $this->dbSelect($sql, array($userUUID));
        $sqlExtension = "select quota from bd_user_extension where user_uuid = ?";
        $dataExtension = $this->dbSelect($sqlExtension,array($userUUID));
        $info = array();
        $usertypeDes = array('', 'operator', 'auditor', 'manager');
        $utils = Xphp::instance('Utils');
        if($data){
            //             $userPermission = explode('|', $data[0]['permission']);
            $tenantInfo = $this->getUserTenantInfo($userUUID);
            $tenantuuid = "";
            if(!empty($data[0]['tenant_uuid'])){
                $tenantuuid = $data[0]['tenant_uuid'];
            }
            $info = array(
                "username" => $data[0]['user_name'],
                "pass" => md5($data[0]['password']),
                "email" => $data[0]['email'],
                "telephone" => $data[0]['telephone'],
                "usertype" => intval($data[0]['user_type']),
                "userpermission" => "",
                "quota" => $utils->calSize($dataExtension[0]['quota']),
                "tenantuuid" => $tenantuuid,
                "usergroup_list" => $tenantInfo['usergroup_list'],
                "role_list" => $tenantInfo['role_list'],
                "domainuuid" => $data[0]['domain_uuid']
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
        $page = require CONF_PATH . 'page.php';
        $roleHandler = Xphp::instance('RoleHandler');
        $userPermission = $roleHandler->pGetUserAllPermission(Xphp::$_user['useruuid'], true);
        if (!empty($_SESSION['tenantuuid'])) {
            // 租户管理，添加角色不允许有系统管理
            $userPermission = array_diff($userPermission, ['sysmanagement']);
        }
        $userTree = $this->getUserAllTreeNodes($page, 0, $userPermission);

        $userInfo = array(
            'nodes' => $userTree,
        );

        return json_encode($userInfo);
    }

    /**
     * 根据传递来的permission数组组装成树返回
     * @param string $params
     * @return string
     */
    public function getUserPermissionRole($params){
        $page = require CONF_PATH . 'page.php';
        $userPermission = $params['permission'];

        $userTree = $this->getUserAllTreeNodesRole($page, 0, $userPermission);

        $userInfo = array(
            'nodes' => $userTree,
        );

        return json_encode($userInfo);
    }

    /**
     * 得到完整的用户权限树(递归)
     * @param array $page   所有配置的页面
     */
    public function getUserAllTreeNodesRole($page, $pid, $userPermission){
        $tree = array();
        foreach ($page as $p){
            if(!in_array($p['name'], $userPermission)){
                //角色添加权限页面
                continue;
            }

            $node = array(
                "id" => $p['name'],
                "pid" => $pid,
                "name" => '<i class="' . $p['class'] . '"></i> ' . Xphp::$_lang[$p['title']],
                "title" => Xphp::$_lang[$p['title']],
                "open" => true,
                "nocheck" => false,
                "type" => $p['level']
            );
            if(!empty($p['child'])){
                //如果有子目录
                $node['children'] = $this->getUserAllTreeNodesRole($p['child'], $p['name'], $userPermission);
            }

            $tree[] = $node;
        }

        return $tree;
    }


    /**
     * 得到完整的用户权限树(递归)
     * @param array $page   所有配置的页面
     */
    public function getUserAllTreeNodes($page, $pid, $userPermission, $userManger = false){
        $tree = array();
        $list = array("monitor", "task", "current_job", "p_current_job_manager","history_job", "p_history_job_delete", "p_history_job_download", 'p_homepage');
        foreach ($page as $p){
            if(!in_array($p['name'], $userPermission)){
                if($userManger){
                    //展示页面
                    continue;
                }else if(!$userManger && $p['level'] != 10){
                    //角色添加权限页面
                    continue;
                }
            }

            // 这里对具体的操作方法校验  不和授权文件交集的后台授权的所有的名称
            if (!in_array($p['name'], $_SESSION['permissionArr']) && $p['level'] == 10) {
                continue;
            }

            //租户内暂时不支持副本容灾和数据归档
            // if(!empty($_SESSION['tenantuuid']) && ($p['name'] == "datacopy" || $p['name'] == "data_archive" || $p['name'] == "vm_overview" || $p['name'] == "tenant_manager")) continue;

            $node = array(
                "id" => $p['name'],
                "pid" => $pid,
                "name" => '<i class="' . $p['class'] . '"></i> ' . Xphp::$_lang[$p['title']],
                "title" => Xphp::$_lang[$p['title']],
                "open" => true,
                "nocheck" => false,
                "type" => $p['level']
            );
            //如果是Home节点
            if($p['name'] == "homepage" && !$userManger){
                $node['checked'] = true;
                $node['chkDisabled'] = true;
            }
            //默认勾选上当前任务和历史任务还有首页涉及到的一些接口
            if(in_array($p['name'], $list)){
                $node['checked'] = true;
            }
            if(!empty($p['child'])){
                //如果有子目录
                $node['children'] = $this->getUserAllTreeNodes($p['child'], $p['name'], $userPermission, $userManger);
            }
            if($userManger){
                $node['nocheck'] = true;
                $node['chkDisabled'] = false;
            }

            //不显示异地备份系统和云存储
            if($p['name'] == "remote_system" || $p['name'] == "cloud_storage") continue;
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * 检查用户名是否已经存在
     * @param string $params [username]
     * @param string $params [user_uuid] 存在表示修改
     * @return string
     */
    public function usernameExist($params){
        $username = $params['username'];
        $sql = "select id from bd_user where user_name = ? ";
        $sqlParams = array($username);

        if (!empty($params['user_uuid'])) {
            $sql .= " and user_uuid != ?";
            $sqlParams = array($username, $params['user_uuid']);
        }

        $users = parent::dbSelect($sql, $sqlParams);

        return json_encode(empty($users));
    }

    /**
     * 检查用户是否注册
     * @param string $params [username]
     * @return string
     */
    public function usernameAvailable($params){
        $username = $params['username'];
        $tenantuuid = $params['tenantuuid'];
        $sql = "select bu.user_uuid from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid where bu.user_name = ? ";
        $sqlParams = array($username);
        if(!empty($_SESSION['tenantuuid'])){
            $tenantuuid = $_SESSION['tenantuuid'];
        }
        if(!empty($tenantuuid)){
            $sql .= " and mut.tenant_uuid = ? ";
            $sqlParams = array($username, $tenantuuid);
        }
        $users = parent::dbSelect($sql, $sqlParams);
        if (count($users) == 1 && $users[0]['user_uuid'] == Xphp::$_user['useruuid']) {
            $users = []; // 表示OK
        }
        return json_encode(empty($users));
    }

    /**
     * 得到所有用户信息
     * @param unknown $params
     */
    public function getUsersInfo($params){
        $utils = Xphp::instance('Utils');
        $search = $params['search'];
        $userName = $utils->escapeWildcard($search['username']);
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];//排序的参数
        $sortType = $params['sortType'];//排序类型
        $sortArr = array('', '', 'bu.user_name', 'bu.user_type', 'mut.tenant_uuid', 'bu.create_time', 'bu.create_user_name', 'bu.email', 'bu.telephone', 'bu.last_login_time', 'bu.lock_flag', '' );

        $sql = "select  distinct bu.user_uuid, bu.user_name, bu.user_type, unix_timestamp(bu.create_time) create_time, bu.create_user_name, bu.email, bu.telephone, 
            unix_timestamp(bu.last_login_time) last_login_time, bu.lock_flag, mut.tenant_uuid from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid ";
        $sqlCount = "select count(distinct bu.id) as total from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid ";
        //检查是否是租户管理员
        $tenantMangerFlag = $this->pCheckTenantManager();
//         if(empty($_SESSION['tenantuuid']) && Xphp::$_user['username'] == "admin"){
//             $sql .= "where bu.user_uuid != ? ";
//             $sqlCount .= "where bu.user_uuid != ? ";
//             $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
//             $sqlCountParams = array(Xphp::$_user['useruuid']);
//         }else if($tenantMangerFlag){
//             $sql .= "where mut.tenant_uuid = ? ";
//             $sqlCount .= "where mut.tenant_uuid = ? ";
//             $sqlParams = array($_SESSION['tenantuuid'], $start, $length);
//             $sqlCountParams = array($_SESSION['tenantuuid']);
//         }else{
            $sql .= "where bu.create_user_uuid = ? ";
            $sqlCount .= "where bu.create_user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
            $sqlCountParams = array(Xphp::$_user['useruuid']);
//         }
        if($this->checkEmpty($userName)){
            //按用户名搜索
            $sql .= " and bu.user_name like ? ";
            $sqlCount .= " and bu.user_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$userName.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$userName.'%'));
        }
        //如果是超级管理员,显示所有用户
        if($sortColumn != 10){
            $sql .= " order by $sortArr[$sortColumn] $sortType";
        }
        $sqlParams = array_merge($sqlParams, array($start,$length));
        $sql .= " limit ? , ? ";
        $users = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);
        $utils = Xphp::instance("Utils");

        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $num = intval($count[0]['total']);
        foreach ($users as $user){
            if($user['user_uuid'] == Xphp::$_user['useruuid']){
                $num--;
                continue;
            }
            $userDes = $this->pGetUserTypeDes(intval($user['user_type']));
            $tenantInfo = $this->pGetTenantInfoByUser($user['user_uuid']);
            $tenantName = Xphp::$_lang['UI_ROLE_GLOBAL'];
            if(!empty($tenantInfo)){
                $tenantName = $tenantInfo['tenantname'];
            }
            $checkBox = '<input type="checkbox" name="id[]" value="'. $user['user_uuid'] .'">';
//             if($user['create_user_name'] != Xphp::$_user['username']){
//                 $checkBox = "";
//             }
            $records["data"][] = array(
                $checkBox,
                $id,
                $user['user_name'],
                $userDes,
                //                 $utils->getUserTypeDes($user['user_type']),
                $tenantName,
                $this->parseDate($user['create_time']),
                $user['create_user_name'],
                $user['email'],
                $user['telephone'],
                $this->parseDate($user['last_login_time']),
                $this->getLockDes($user['lock_flag']),
                $user['user_uuid']
            );
            $id++;
        }
        //如果是按租户归宿排序
        if($sortColumn == 10){
            $records["data"] = $utils->arraySort($records["data"], 10, $sortType, $start, $length);
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $num;
        $records["recordsFiltered"] = $num;

        return  json_encode($records);
    }

    /**
     * 修改密码
     * @param unknown $params
     */
    public function editPassword($params){
        $utils = Xphp::instance('utils');
        $useruuid = $params['useruuid'];
        $oldPassword = $params['oldPassword'];
        $newPassword = $params['newPassword'];
        $editTime = date('Y-m-d H:i:s');
        if(empty($useruuid)){
            $useruuid = Xphp::$_user['useruuid'];
        }
        $this->paramsCheck($oldPassword, $newPassword);
        $sql = "update bd_user set password = ?, create_time = ? ,force_password_change_flag = ? where user_uuid = ? and password = ?";
        $result = $this->dbExec($sql, array($newPassword, $editTime, Xphp::$_config['FLAG']['UNSET'], $useruuid, $oldPassword));
        $sqlUsername = "select user_name from bd_user where user_uuid = ?";
        $sqlUsernameData = $this->dbSelect($sqlUsername,array($useruuid));
        if($result){
            $clientIP = $utils->getClientIP();
            $this->loginLog($useruuid, $sqlUsernameData[0]['user_name'], 'SYSTEM_USER_EDIT_PASSWORD_SUCCESS', array("S:". $clientIP));
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
        $sql = "update bd_user set email = ?, telephone = ?, language = ?, user_name = ? where user_uuid = ?";
        $params = array($email, $telephone, $language, $username, Xphp::$_user['useruuid']);
        $result = $this->dbExec($sql, $params);
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
        $useruuid = Xphp::$_user['useruuid'];
        $operate = Xphp::$_lang['WEB_USERS_GET_CURRENT_INFO'];
        $sql = "select user_name, email, telephone, language, user_level, auth_code, user_type from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));
        $info = array();
        //获取用户是否属于租户
        $systemHandler = Xphp::instance('SystemHandler');
        $tenantuuid = $systemHandler->getUserPermission();
        if($data){
            $code = $data[0]['auth_code'];
            if(empty($data[0]['auth_code']) && intval($data[0]['user_type']) == 3){
                //超级管理员admin初始化认证码
                $utils = Xphp::instance('Utils');
                $code = $utils->xphp_random_code(4);
                $sql = "update bd_user set auth_code = ? where user_uuid = ?";
                $this->dbExec($sql, array($code, $useruuid));
            }
            $info = array(
                "re" => true,
                "username" => $data[0]['user_name'],
                "email" => $data[0]['email'],
                "telephone" => $data[0]['telephone'],
                "language" => $data[0]['language'],
                "list" => $this->getSystemLangList(),
                "tenantuuid" => $tenantuuid,
                "user_uuid" => $useruuid,
                "user_level" => $data[0]['user_level'],
                "is_three_powers" => $_SESSION['isThreePowers'],
                "auth_code" => $code
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
        $sql = "select bu.user_uuid, bu.user_name from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid ";
        $sqlParams = $where = [];
        if(!empty($_SESSION['tenantuuid'])){
            $where[] = " mut.tenant_uuid = ? ";
            $sqlParams = array_merge($sqlParams,array($_SESSION['tenantuuid']));
        }

            // 如果是三权模式下 那么就只能读取操作员 即user_level 为 5，不然就是 0
            if ($_SESSION['isThreePowers']) {
                $where[] = " bu.user_level = 5 ";
            } else {
                if ($_SESSION['userLevel'] != 1) {
                    //如果不是超级管理员,显示自己下级或管理的用户
                    $where[] = "  bu.user_level = 0 and bu.manager_uuid = ?";
                    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
                } else {
                    // 超级管理查看所有的普通用户
                    $where[] = "  bu.user_level = 0 ";
                }
            }

        if (!empty($where)) {
            $sql .= ' where ' . implode(' and ', $where);
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        if (empty($_SESSION['isThreePowers'])) {
            // 不是三权模式
            //添加管理员本人
            $info[] = array(
                'uuid' => Xphp::$_user['useruuid'],
                'name' => Xphp::$_user['username'],
            );
        }
        foreach ($data as $d){
            $info[] = array(
                'uuid' => $d['user_uuid'],
                'name' => $d['user_name'],
            );
        }
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
    public function getLockDes($lockFlag){
        $lockDes = Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'];
        if(1 == $lockFlag){
            $lockDes = Xphp::$_lang['WEB_PLATFORM_ENABLE'];
        }elseif(2 == $lockFlag){
            $lockDes = Xphp::$_lang['WEB_PLATFORM_DISABLE'];
        }
        return $lockDes;
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
    * 发送微信模板消息通知
     * @param  array $params
     * @param string $alarmID 告警id
     * @param int $type 类型 2任务1系统
     */
    public function sendTemplate($params, $alarmID, $type = 1)
    {
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sqlupdate);
        if(!empty($wechats)){
            $wechatContent = json_decode($wechats[0]['settings_content'],true);
            // 公众号模板消息
            $title = $params['title'];
            if (strlen($params['content']) > 32 ) {
                // 那么取 desc
                if (strlen($params['desc']) > 32) {
                    // 那么取 任务名称
                    $content = $params['task_name'] ?: $params['title'];
                    $content = strlen($content) > 32 ? $params['title'] : $content;
                } else {
                    $content = $params['desc'];
                }
            } else {
                $content = $params['content'];
            }

            if ($wechatContent['openid'] && $wechatContent['appid'] && $wechatContent['appsecret']) {
                $utils = Xphp::instance('Utils');
                $out_parma =  $utils->xphp_encrpt(json_encode(
                    [
                        'userUuid' => Xphp::$_user['useruuid'],
                        'alarmID' => $alarmID,
                        'type' => $type
                    ]
                ));
                // 发送模板
                $openid = array_column($wechatContent['openid'], 'openid'); // 消息接收人的openid
                $url = $wechatContent['template_url'] ?? $utils->get_current_url(); // 'https://www.vinchin.com'; // 跳转链接
                $url .=  '/alarm.php?param=' . $out_parma;

                return $this->sendWechat($openid,$wechatContent,$title,$content,$url);
            }
        }
        return false;
    }

    /**
    * 发送微信通知封装
     */
    public function sendWechat($openid, $wechatContent, $title, $content, $url = '') {
        $utils = Xphp::instance('Utils');
        $data = array (
            $wechatContent['template_param1'] => array (
                'value' => $title,
                'color' => '#000000'
            ),
            $wechatContent['template_param2'] => array (
                'value' => $content,
                'color' => '#666666'
            ),
        );
        $config = [
            'appid' => $wechatContent['appid'],
            'appsecret' => $wechatContent['appsecret'],
        ];

        return $utils->send_wechat_template ($openid, $wechatContent['template_id'], $data, $url, $config, $wechatContent['wechat_mode']);
    }

    /**
     * 发送企业微信通知
     * @param  array $params
     * @param string $alarmID 告警id
     * @param int $type 类型 2任务1系统
     */
    public function sendTemplate2($params, $alarmID, $type = 1)
    {
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 11";
        $wechats = $this->dbSelect($sqlupdate);
        if(!empty($wechats)){
            $wechatContent = json_decode($wechats[0]['settings_content'],true);
            // 企业微信通知方式
            $utils = Xphp::instance('Utils');
            $config = [
                "CORP_ID"               => $wechatContent['wechat_core_id'],
                "APP_ID"                => $wechatContent['wechat_app_id'],
                "APP_SECRET"            => $wechatContent['wechat_app_secret'],
            ];
            $url = $wechatContent['wechat_url'] ?? $utils->get_current_url(); // 'https://www.vinchin.com'; // 跳转链接
            $out_parma =  $utils->xphp_encrpt(json_encode(
                [
                    'userUuid' => Xphp::$_user['useruuid'],
                    'alarmID' => $alarmID,
                    'type' => $type
                ]
            ));

            $params['url'] = $url . '/alarm.php?param=' . $out_parma;
            return $utils->send_wework_api($config, $params);
        }
        return false;
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
     * 调用远程API(自定义通知)
     */
    private function smsRemoteApi($params, $info){
        // 自定义
        $appId = $params['applicationid'];
        $appsecret = $params['appsecret'];
        $company = $params['company_id_'];
        $timestamp = (int)(microtime(true) * 1000);
        $sign = sha1($appId.$appsecret.$timestamp);
        $url = $params['url'];
        $utils = Xphp::instance("Utils");
        $body = array(
            'applicationid' => $appId,
            'company_id_' => $company,
            'destaddr'=> $info['tels'],
            'extcode' => '',
            'messagecontent' => $info['msg'],
            'msgfmt' => 0,
            'reqdeliveryreport' => 0,
            'requesttime' => date('Y-m-d H:i:s', time()),
            'sendmethod' => 0,
            'sismsid' => $utils->uuid(),
            'appsecret' => $appsecret,
            'url' => $url
        );
        $header = array(
            'appId: ' . $appId,
            'timestamp: ' . $timestamp,
            'sign: ' . $sign,
            'company: ' . $company,
            'Content-Type: application/json'
        );
        $Curl = Xphp::instance('Curl');
        $sendResult = $Curl->post_json($url,$body,$header);
        if($sendResult['data']['code'] != 0){
            return $this->muOpResult(false, $sendResult['data']['message'],xphp_get_lang('WEB_ALARM_SEND_NOTICE_FAILURE'),'warning');
        }else{
            return $sendResult;
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

        $sql = "select sms_device_config from bd_sms_notice";
        $data = $this->dbSelect($sql);
        $deviceConfig = json_decode($data[0]['sms_device_config'], true);
        // 加上短信配置的收件人
        $configTels = $deviceConfig['receive_telephone'] ?? [];
        // 确保配置中的电话号码是数组格式
        if (is_string($configTels)) {
            $configTels = array_filter(array_map('trim', explode(',', $configTels)));
        } elseif (!is_array($configTels)) {
            $configTels = [];
        } else {
            $configTels = array_filter(array_map('trim', $configTels));
        }

        // 合并、去重并再次过滤空值
        $telArr = array_filter(explode(',', $tels));
        $telArr = array_filter(array_unique(array_merge($telArr, $configTels)));
        $tels = implode(',', $telArr);

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
        $remoteApiResult = '';

        if($deviceConfig['sms_mode'] == Xphp::$_config['FLAG']['UNSET']){
            // 自定义
            $telArr = array_filter(explode(',', $tels));
            foreach ($telArr as $item) {
                $smsOp = array(
                    'tels' => $item,        //电话号码
                    'msg' => $msg,          //短信内容
                );
                $remoteApiResult = $this->smsRemoteApi($deviceConfig,$smsOp);
            }
            if($remoteApiResult['data']['code'] == 0){
                $remoteApiResult = json_encode(['re' => true]);
            }
        }else{
            // 系统默认
            $remoteApiResult = $this->remoteApi($operate, $p);
            $this->updateSmsQuantity($remoteApiResult);
        }
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
        $tels = $params['tels'];
        $msg = $params['msg'];
        $operate = Xphp::$_lang['WEB_USERS_SEND_SMS_TO'] . $tels;
        if(empty($pdo)){
            $errorNum = Xphp::instance('Utils', 'getErrorNum', 'PF_SETTING_NOTICE_SMSMODEM_CONNECT_DB_ERROR');
            return $this->muOpResult(false, $operate, '', '', $errorNum);
        }

        $this->dbSelect("set names 'utf8'");

        $sql = "insert into smsserver_out (type,recipient,text,encoding,create_date,gateway_id) values 
                ('O', '" . $tels . "', '" . $msg . "', 'U',now(),'*');";
        $result = $this->dbQuery($sql);

        if(!$result){
            $errorNum = Xphp::instance('Utils', 'getErrorNum', 'PF_SETTING_NOTICE_SMSMODEM_INSERT_DB_ERROR');
            return $this->muOpResult(false, $operate, '', '', $errorNum);
        }
        $id = $this->dbLastInsertId();

        $flag = true;
        $i = 0;
        $sleepTime = Xphp::$_config['SMS_CONFIG']['SLEEPTIME'];
        $checkTime = Xphp::$_config['SMS_CONFIG']['CHECKTIME'];


        $sendResult = false;
        while($flag){
            //循环检查发送结果
            $sql ="select status from smsserver_out where id = $id";
            $result = $this->select($sql);
            if($result[0] != "S" && $i < $checkTime){
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
        $utils = Xphp::instance('Utils');
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
        $email = array_filter($params['email']);
        $info = $params['info'];
        $attachment = $params['attachment'];
        $this->paramsCheck($info, $title, $email);
        $emailStr = implode(',', $email);

        //获取邮件配置
        $sql = "select smtp_config from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql, array());
        $smtpConfig = json_decode($data[0]['smtp_config'], true);

        if ($smtpConfig['email_model'] != 2) {
            $utils = Xphp::instance('Utils');
            $pass = $utils->decrypt($smtpConfig['pass']);
            $encryption = intval($smtpConfig['encryption']);
            $encryption = Xphp::$_config['EMAIL_ENCRYPTION_TYPE'][$encryption];
            //直接调用发送邮件接口
            $emailUtils = Xphp::instance('Email');
            $emailConfig = Xphp::$_config['EMAIL'];
            $emailUtils->config(
                $smtpConfig['host'], $smtpConfig['port'], $emailConfig['authentication'],
                $smtpConfig['email'], $pass, $encryption);
            $result = $emailUtils->sendmail($email, $title, $info, $attachment);
        } else {
            // outlook
            // 借调web_ng的接口
            // 获取当前域名 + 协议 + 端口
            $baseUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $sendMailUrl = $baseUrl . '/api/v1/system/notice/send_email?x-api-version=1.0-rev0';

            $mailData = [
                'email' => $email,
                'title' => $title,
                'info'  => $info,
            ];
            //print_r($mailData);die;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $sendMailUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mailData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 不验证证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 不验证域名是否匹配证书

            $response = curl_exec($ch);
            curl_close($ch);
            if ($response === false) {
                $result = false;
            } else {
                // $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                // echo "HTTP 状态码: $httpCode <br>";
                $data = json_decode($response, true);
                // print_r($data);die;
                if (json_last_error() === JSON_ERROR_NONE) {
                    $result = isset($data['code']) && $data['code'] == 0;
                } else {
                    $result = false;
                }
            }
        }
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
        $result = $curl->oldPost(Xphp::$_config['REMOTE']['api_url'], $params['p']);
        $utils = Xphp::instance('Utils');
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

    /**
     * 单独处理单点登录
     * @param unknown $params
     * @return string|boolean
     */
    public function ssoLogin($tokenid){
        $utils = Xphp::instance('Utils');
        $tokenid = $utils->decrypt($tokenid);
        $tokenidArr = explode("|", $tokenid);

        if(time() - $tokenidArr[2] > 7200){
            //如果超过7200秒
            return false;
        }

        $username = $tokenidArr[0];
        $password = $tokenidArr[1];

        $loginResult = $this->loginVerify($username, $password, FALSE,'', 100, TRUE);
        $loginResult = json_decode($loginResult);
        if(1 == $loginResult){
            //如果登录成功
            return true;
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

	/**
	 * 获取授权的细节功能
	 * @param unknown $params
	 */
	public function getAuthFunc($params){
	    $info = $_SESSION['authfun'];
	    return json_encode($info);
	}

	/**
     * 获取授权文件显示相应模块
     * @param array $permission
     * $param string $username
     */
	public function getAuthSoftwarePermission($permission, $useruuid){
        $systemHandler = Xphp::instance('SystemHandler');
        $extension = $systemHandler->getExtensionLicense();
        $pagelist = array();
        if(!empty($extension)){
            $pagelist = $extension['p'];
        }
        $dbTimingHandler = Xphp::instance('DBTimingHandler');
        $dbCDPHandler = Xphp::instance('DbCDPHandler');
        $rpc = Xphp::instance('DbRPCHandler');
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
                "vmprotect_recover","vmrecover","vminstantrecover","vmrecovera",
                "vmdata",
                "vmprotect_infrastructure","vcenter_manager","storage_lanfree","appliance_manager",
                "resmanagement","node_manager","storage_manager",
                "sysmanagement","setting_manager","system_network","set_time","set_ip","system_dns",
                "authorization_module",
            );
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
	 * 获取用户所在的用户组
	 * @param unknown $useruuid
	 * @return array
	 */
	public function getUserInUserGroup($useruuid){
	    return array();
	}

	/**
	 * 添加用户组
	 * @param unknown $useruuid
	 */
	public function addUserGroup($params){

	    //权限检查
	    $roleHandler = Xphp::instance('RoleHandler');
	    $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_add");
	    $userGroupName = $params['userGroupName'];
	    $userGroupDescription = $params['userGroupDescription'];
	    $userGroupTenantUUID = "";

	    // 验证用户组是否存在
        $this->checkGroupnameExist($userGroupName, 'UI_USER_GROUP_ADD', 'UI_USER_GROUP_IDENTICAL');

	    //如果是租户内部创建
	    if(!empty($_SESSION['tenantuuid'])){
	        $userGroupTenantUUID = $_SESSION['tenantuuid'];
	    }
	    $userGroupRoleUUIDlist = $params['userGroupRoleUUID'];
	    $userGroupUserUUIDlist = $params['userGroupUserUUID'];
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
	        $this->systemLog('SYSTEM_USER_GROUP_ADD_SUCCESS', array($userGroupName));
	        return $this->muOpResult(true, Xphp::$_lang['UI_USER_GROUP_ADD']);

	    }else{
	        return $this->muOpResult(false, Xphp::$_lang['UI_USER_GROUP_ADD'], '', 'warning');
	    };

	}

    /**
     * 检查用户组名字是否创建重复
     * @param string $groupName
     * @param string $opName 操作提示名称标识
     * @param string $msg 操作提示内容标识
     * @param string $group_uuid
     * @return boolean
     */
    public function checkGroupnameExist (string $groupName, string $opName, string $msg, $group_uuid = '')
    {
        $sql = "select user_group_uuid from bd_user_group where user_group_name = ? ";
        $data = $this->dbSelect($sql, array($groupName));
        if (!empty($data)) {
            if (!empty($group_uuid) && count($data) == 1 && $group_uuid == $data[0]['user_group_uuid']) {
                // 编辑 并且只存在一个的情况下 并且是相同
                return true;
            }
            // 其它直接抛出
            exit($this->muOpResult(false, Xphp::$_lang[$opName], Xphp::$_lang[$msg], "warning"));
        }

        return true;
    }

	/**
	 * 获取所有租户名称及租户UUID
	 * @param unknown $useruuid
	 */
	public function getAllTenant(){
	    $tenantuuid = $_SESSION['tenantuuid'];
	    $sql = "select tenant_uuid,tenant_name from bd_tenant where lock_flag = ? ";
	    $sqlParams = array(Xphp::$_config['FLAG']['SET']);
	    if(!empty($tenantuuid)){
	        $sql .= " and tenant_uuid = ? ";
	        $sqlParams = array_merge($sqlParams, array($tenantuuid));
	    }
	    $data = $this->dbSelect($sql, $sqlParams);
	    $info = array();

	    foreach ($data as $d){
	       $info[]=array(
	           'tenant_uuid'=>$d['tenant_uuid'],
	           'tenant_name'=>$d['tenant_name']
	       );
	    }
	    return json_encode($info);
	}

	/**
	 * 获取租户下用的角色
	 * @param unknown $useruuid
	 */
	public function getRoleList($params){
	    $editFlag = $params['editflag'];
	    $userGroupuuid = $params['usergroupuuid'];
	    $sqlRole = "select distinct br.role_uuid, br.role_name from bd_role br left join mt_user_group_role mugr on mugr.role_uuid = br.role_uuid where ";

	    $where = " br.lock_flag = ? ";
        $sqlParams = [Xphp::$_config['FLAG']['SET']];
	    if (!$_SESSION['isThreePowers']) {
            $sqlParams = array(Xphp::$_config['FLAG']['SET'], Xphp::$_user['useruuid']);
            if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
                $where .= " and (br.create_user_uuid = ? or br.create_user_uuid = '') ";
            } else {
                $where .= " and br.create_user_uuid = ? ";
            }
        }

	    if($editFlag){
	        // $sqlRole .= " or mugr.user_group_uuid =? ";
            $where .= " or mugr.user_group_uuid =? ";
	        $sqlParams = array_merge($sqlParams, array($userGroupuuid));
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

        $sqlRole .= $where . " and br.role_uuid != 'a30f7728-2ef7-bca0-2224-07deba8ce3e5'";

	    $dataRole = $this->dbSelect($sqlRole, $sqlParams);

	    $info = array();
	    foreach ($dataRole as $d){
	        $info[] = array(
	            'role_uuid' => $d['role_uuid'],
	            'role_name' => $d['role_name']
	        );
	    }
	    return json_encode($info);


	}


	/**
	 * 获取用户列表用于分配
	 * @param unknown $params
	 */
	public function getUserList($params){
	    $userGroupFlag = $params['usergroupflag']; //用户组添加修改标志
	    $sqlUserALL = "select bt.tenant_name,bu.user_uuid, bu.user_name from bd_user bu left join
                        mt_user_tenant mut on mut.user_uuid = bu.user_uuid left join bd_tenant bt on mut.tenant_uuid = bt.tenant_uuid where bu.lock_flag = ? ";
	    $sqlParams = array(Xphp::$_config['FLAG']['SET']);

        // 如果是三权模式下 那么就只能读取操作员 即user_level 为 5，不然就是 0
        if ($_SESSION['isThreePowers']) {
            $sqlUserALL .= " and bu.user_level = 5 ";
        } else {
            if ($_SESSION['userLevel'] != 1) {
                //如果不是超级管理员,显示自己下级或管理的用户
                $sqlUserALL .= " and bu.user_level = 0 and bu.manager_uuid = ?";
                $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            } else {
                // 超级管理查看所有的普通用户
                $sqlUserALL .= " and bu.user_level = 0 ";
            }
        }

	    $dataUser = $this->dbSelect($sqlUserALL,$sqlParams);
	    $info = array();
	    foreach ($dataUser as $d){
	        $username = $d['user_name'];
	        if(!empty($d['tenant_name'])){
	            $username .= '(' .$d['tenant_name']. ')';
	            if(empty($_SESSION['tenantuuid']) && $userGroupFlag){
	                //如果是添加用户组 不与租户管理员关联
	                continue;
	            }
	        }
	        $info[] = array(
	            'user_uuid' => $d['user_uuid'],
	            'user_name' => $username
	        );

	    }
	    return json_encode($info);
	}

	/**
	 * 获取用户组列表用于分配
	 * @param unknown $params
	 */
    public function getUserGroupList($params){
        $editFlag = $params['editflag'];
        $useruuid = $params['useruuid'];
        //获取用户租户信息
        $tenantInfo = $this->getUserTenantInfo($useruuid);
        $sqlUserGroup = "select distinct bug.user_group_uuid, bug.user_group_name 
from bd_user_group bug left join mt_user_group_tenant mugt on bug.user_group_uuid = mugt.user_group_uuid 
    left join mt_user_user_group muug on bug.user_group_uuid = muug.user_group_uuid  ";
        $where = "bug.lock_flag = ? and bug.create_user_uuid = ? ";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], Xphp::$_user['useruuid']);
        if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
            $where .= " or bug.create_user_uuid = '' ";
        }

        //修改用户显示相应管理的所有用户组
        if($editFlag){
            $where .= " or muug.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }
        $sqlUserGroup .= " where ($where) and bug.user_group_uuid != 'e99ae858-d549-4754-9310-457477f4c2e3'";
        $data = $this->dbSelect($sqlUserGroup, $sqlParams);
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                //如果在租户外修改租户管理员，只显示创建时关联的用户组
                if(empty($_SESSION['tenantuuid']) && !empty($tenantInfo['tenant_uuid'])){
                    if(!in_array($d['user_group_uuid'], $tenantInfo['usergroup_list'])) continue;
                }
                $info[] = array(
                    'usergroup_uuid' => $d['user_group_uuid'],
                    'usergroup_name' => $d['user_group_name']
                );
            }
        }


        return json_encode($info);

    }


	/**
	 * 获取用户组表格数据
	 *
	 */
	public function get_all_user_group($params){
	    $start = $params['start'];
	    $length = $params['length'];
	    $sortColumn = $params['sortColumn'];//排序的参数
	    $sortType = $params['sortType'];//排序类型
	    $params_user_group = array('','bug.user_group_name','bug.user_group_type','bug.lock_flag','bug.description','bt.tenant_name', 'bug.create_user_name', 'bug.create_time');
	    $sql = "select distinct bug.user_group_uuid,bug.user_group_name, bug.user_group_type,bug.lock_flag,bug.description, bug.create_user_name, bug.create_time, bt.tenant_uuid, bt.tenant_name 
                from bd_user_group bug left join mt_user_group_tenant mugt on bug.user_group_uuid = mugt.user_group_uuid left join bd_tenant bt on mugt.tenant_uuid = bt.tenant_uuid ";
	    $sql_count = "select count(distinct bug.user_group_uuid) as count_all from bd_user_group bug left join mt_user_group_tenant mugt on bug.user_group_uuid = mugt.user_group_uuid left join bd_tenant bt on mugt.tenant_uuid = bt.tenant_uuid ";
	    $sqlParams =  array($start,$length);
	    $sqlCountParams = array();
	    //检查是否是租户管理员
// 	    $tenantMangerFlag = $this->pCheckTenantManager();
// 	    if(empty($_SESSION['tenantuuid']) && Xphp::$_user['username'] == "admin"){

// 	        $sqlParams = array($start, $length);
// 	        $sqlCountParams = array();
// 	    }else if($tenantMangerFlag){
// 	        $sql .= " where mugt.tenant_uuid = ? ";
// 	        $sql_count .= " where mugt.tenant_uuid = ? ";
// 	        $sqlParams = array($_SESSION['tenantuuid'], $start,$length);
// 	        $sqlCountParams = array($_SESSION['tenantuuid']);
// 	    }else{
	        $where = " bug.create_user_uuid = ? ";
	        $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
	        $sqlCountParams = array(Xphp::$_user['useruuid']);
// 	    }

	    if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
            $where .= " or bug.create_user_uuid = '' ";
	    }
        $where =  " where ($where) and bug.user_group_uuid != 'e99ae858-d549-4754-9310-457477f4c2e3'";
	    $sql .= $where ." order by $params_user_group[$sortColumn] $sortType limit ?,?";

	    $result_sql_all = $this->dbSelect($sql, $sqlParams);
        $sql_count .= $where;
	    $result_count = $this->dbSelect($sql_count, $sqlCountParams);
	    $count = intval($result_count[0]['count_all']);
	    $info = array();
	    $info['data'] = array();
	    $pfDes = include APP_PATH . "platform/PFDescription.php";
	    foreach ($result_sql_all as $d){
	        $user_group_type_play = $pfDes['USER_GROUP_TYPE_DES'][intval($d['user_group_type'])];
	        $lock_flag_play = $pfDes['USER_GROUP_LOCK_FLAG_DES'][intval($d['lock_flag'])];
	        if ($d['tenant_uuid'] == null){
	            $tenant_uuid ='';
	            $tenant_name = Xphp::$_lang['UI_ROLE_GLOBAL'];
	            $checkBox = '<input type="checkbox" name="id[]" value="'. $d['user_group_uuid'] .'">';
	        }else{
	            $tenant_uuid =$d['tenant_uuid'];
	            $tenant_name =$d['tenant_name'];
// 	            $checkBox = "";
	        }
	        $checkBox = "";
	        if($d['user_group_type'] != Xphp::$_config['USER_GROUP_TYPE']['DEFAULT']){
	            $checkBox = '<input type="checkbox" name="id[]" value="'. $d['user_group_uuid'] .'">';
	        }
	        $createTime = $d['create_time'];
	        if($d['create_time'] == "0000-00-00 00:00:00"){
	            $createTime = Xphp::$_config['TIMESPACE'];
	        }
	        //创建者
	        $createUser = $d['create_user_name'];
	        if(empty($createUser)){
	            $createUser = Xphp::$_config['NULLSPACE'];
	        }
	        $info['data'][] = array(
	            $checkBox,
	            $d['user_group_name'],
	            $user_group_type_play,
	            $lock_flag_play,
	            $d['description'],
	            $tenant_name,
	            $createUser,
	            $createTime,
	            $d['user_group_uuid']
	        );

	    }
	    $info["draw"] = $params['draw'];
	    $info["recordsTotal"] = $count;
	    $info["recordsFiltered"] = $count;

	    return json_encode($info);

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
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pDeleteUserResource($useruuid, $resourceList){
	   if(empty($resourceList)) return false;
	   $resourceDes = implode("','", $resourceList);
	   $sql = "delete from mt_user_resource where resource_uuid in ('".$resourceDes."') ";
	   $sqlParams = array();
	   if(!empty($useruuid)){
	       $sql .= " and user_uuid = ?";
	       $sqlParams = array_merge($sqlParams, array($useruuid));
	   }
	   $result = $this->dbExec($sql, $sqlParams);
       if($result){
           return true;
       }else{
           return false;
       }
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
	 * 添加用户和资源组关联
	 * @param string $useruuid 用户唯一标识
	 * @param array $resourceGroupList 资源组唯一标识列表
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pAddUserResourceGroup($useruuid, $resourceGroupList){
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
	public function pDeleteUserResourceGroup($useruuid, $resourceGroupList){
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
	 * 取消用户和对应租户关联
	 * @param array $userList 用户唯一标识列表
	 * @param string $tenantuuid 租户唯一标识
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pDeleteSelectUserTenant($userList, $tenantuuid){
	    if(empty($userList)) return false;
	    $userDes = implode("','", $userList);
	    $sql = "delete from mt_user_tenant where tenant_uuid = ? and user_uuid in ('". $userDes ."') ";
	    $result = $this->dbExec($sql, array($tenantuuid));

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
	public function pDeleteUserGroupResource($usergroupuuid, $resourceList){
	    if(empty($resourceList)) return false;
	    $resourceDes = implode("','", $resourceList);
	    $sql = "delete from mt_user_group_resource where user_group_uuid = ? and resource_uuid in ('".$resourceDes."')";
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
	 * 取消用户组和租户关联
	 * @param array $userGroupList 用户组唯一标识列表
	 * @param string $tenantuuid 租户唯一标识
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pDeleteSelectUserGroupTenant($userGroupList, $tenantuuid){
	    if(empty($userGroupList)) return false;
	    $userGroupDes = implode("','", $userGroupList);
	    $sql = "delete from mt_user_group_tenant where tenant_uuid = ? and user_group_uuid in ('". $userGroupDes ."')";
	    $result = $this->dbExec($sql, array($tenantuuid));

	    if($result){
	        return true;
	    }else{
	        return false;
	    }

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
	 * 取消角色和用户关联
	 * @param string $roleuuid 角色唯一标识
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pDeleteRoleUser($roleuuid){
	    $sql = "delete from mt_user_role where role_uuid = ?";
	    $result = $this->dbExec($sql, array($roleuuid));

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

	/**
	 * 取消角色和用户组关联
	 * @param string $roleuuid 角色唯一标识
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pDeleteRoleUserGroup($roleuuid){
	    $sql = "delete from mt_user_group_role where role_uuid = ?";
	    $result = $this->dbExec($sql, array($roleuuid));

	    if($result){
	        return true;
	    }else{
	        return false;
	    }

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
	 * 添加资源组与租户关联
	 * @param string $tenantuuid 租户唯一标识
	 * @param array $resourceGroupList 资源组列表
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pAddResourceGroupTenant($tenantuuid, $resourceGroupList){
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
	 * 取消资源组和租户关联
	 * @param string $tenantuuid 租户唯一标识
	 * @param array $resourceGroupList 资源组列表
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pDeleteResourceGroupTenant($tenantuuid, $resourceGroupList){
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
	 * 取消资源和资源组关联
	 * @param string $resourcegroupuuid 资源组唯一标识
	 * @param array $resourceList 资源唯一标识列表
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pDeleteResourceGroupResource($resourcegroupuuid, $resourceList){
	    if(empty($resourceList)) return false;
	    $resourceDes = implode("','", $resourceList);
	    $sql = "delete from mt_resource_resource_group where resource_group_uuid = ? and resource_uuid in ('".$resourceDes."')";
	    $result = $this->dbExec($sql, array($resourcegroupuuid));

	    if($result){
	        return true;
	    }else{
	        return false;
	    }

	}

	/**
	 * 取消所有资源和资源组关联
	 * @param string $resourcegroupuuid 资源组唯一标识
	 * @author luokai@vinchin.com
	 * @return boolean 执行结果 成功|失败
	 */
	public function pDeleteResourceGroupAllResource($resourcegroupuuid){
	    $sql = "delete from mt_resource_resource_group where resource_group_uuid = ?";
	    $result = $this->dbExec($sql, array($resourcegroupuuid));

	    if($result){
	        return true;
	    }else{
	        return false;
	    }

	}

	/**
	 * 获取单个用户组所有信息
	 * @param unknown $params
	 */
	public function get_one_user_group_data($param){
	    $uuid = $param['usergroupuuid'];
	    $sql="select user_group_name,description from bd_user_group where user_group_uuid =?";
	    //查询用户组名称以及描述
	    $result = $this->dbSelect($sql,array($uuid));
	    $sql1 = "select tenant_uuid from mt_user_group_tenant where user_group_uuid =?";
	    //查询用户组对应的租户id
	    $result1 = $this->dbSelect($sql1,array($uuid));
	    $sql2 = "select role_uuid from mt_user_group_role where user_group_uuid =?";
	    //查询用户组对应的角色id集合
	    $result2 = $this->dbSelect($sql2,array($uuid));
	    $sql3 = "select user_uuid from mt_user_user_group where user_group_uuid =?";
	    //查询用户组对应的用户id集合
	    $result3 = $this->dbSelect($sql3,array($uuid));
	    //判断tenant__uuid是否为空，如为空值则赋值为空
	    $info = array();
	    foreach ($result2 as $d){
	        $info_role[]=array($d['role_uuid']);
	    };
	    foreach ($result3 as $d){
	        $info_user[] = array($d['user_uuid']);
	    }
	    $info['user_group_name'] = $result[0]['user_group_name'];
	    $info['description'] = $result[0]['description'];
	    $info['tenant_uuid'] = $result1[0]['tenant_uuid'];
	    $info['role'] = $info_role;
	    $info['user'] = $info_user;
	    return json_encode($info);


	}
	/**
	 * 修改用户组
	 * @param unknown $useruuid
	 */
	public function editUserGroup($params){
	    //权限检查
	    $roleHandler = Xphp::instance('RoleHandler');
	    $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_edit");
	    $userGroupName = $params['userGroupName'];
	    $userGroupDescription = $params['userGroupDescription'];

        $userGroupUUID = $params['userGroupUUID'];

        // 验证用户组是否存在
        $this->checkGroupnameExist($userGroupName, 'UI_USER_GROUP_MODIFY', 'UI_USER_GROUP_IDENTICAL', $userGroupUUID);

	    $userGroupTenantUUID = "";
	    //如果是租户内部创建
	    if(!empty($_SESSION['tenantuuid'])){
	        $userGroupTenantUUID = $_SESSION['tenantuuid'];
	    }
	    $userGroupRoleUUIDlist = $params['userGroupRoleUUID'];
	    $userGroupUserUUIDlist = $params['userGroupUserUUID'];


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
	        return $this->muOpResult(true, Xphp::$_lang['UI_USER_GROUP_MODIFY']);
	    }else{
	        return $this->muOpResult(false, Xphp::$_lang['UI_USER_GROUP_MODIFY'], "", "warning");
	    }

	}

	/**
	 * 删除用户组
	 * @param unknown $useruuid
	 */
	public function deleteUserGroup($params){
	    //权限检查
	    $roleHandler = Xphp::instance('RoleHandler');
	    $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_delete");
	    $data_list = $params['data_list'];
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
	        return $this->muOpResult(true, Xphp::$_lang['UI_USER_GROUP_DELETE']);
	    }else{
	        return $this->muOpResult(false, Xphp::$_lang['UI_USER_GROUP_DELETE'], "", "warning");
	    }

	}
	/**
	 * 禁用用户组
	 * @param unknown $useruuid
	 */
	public function lock_usergroup($params){
	    //权限检查
	    $roleHandler = Xphp::instance('RoleHandler');
	    $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_disable");
	    $data_list = $params['data_list'];
	    $string = implode("','",$data_list);
	    //lock_flag为2为禁用
	    $sql = "update bd_user_group set lock_flag = 2 where user_group_uuid in";
	    $sql .=" ("."'"."$string"."'".")";
	    $result = $this->dbExec($sql);
	    if($result){
	        return $this->muOpResult(true, Xphp::$_lang['UI_USER_GROUP_DISABLE']);
	    }else{
	        return $this->muOpResult(false, Xphp::$_lang['UI_USER_GROUP_DISABLE']);
	    }

	}
	/**
	 * 启用用户组
	 * @param unknown $useruuid
	 */
	public function unlock_usergroup($params){
	    //权限检查
	    $roleHandler = Xphp::instance('RoleHandler');
	    $roleHandler->pOperationPermissionCheckExit("p_safety_usergroup_enable");
	    $data_list = $params['data_list'];
	    $string = implode("','",$data_list);
	    //lock_flag为2为禁用
	    $sql = "update bd_user_group set lock_flag = 1 where user_group_uuid in";
	    $sql .=" ("."'"."$string"."'".")";
	    $result = $this->dbExec($sql);
	    if($result){
	        return $this->muOpResult(true, Xphp::$_lang['UI_USER_GROUP_ENABLE']);
	    }else{
	        return $this->muOpResult(false, Xphp::$_lang['UI_USER_GROUP_ENABLE']);
	    }
	}

	/**
	 * 获取修改用户的租户等信息
	 * @param string $useruuid
	 */
	public function getUserTenantInfo($useruuid){
	    //获取租户uuid
	    $sqlTenant = "select mut.tenant_uuid from bd_user bu, mt_user_tenant mut where bu.user_uuid = mut.user_uuid and bu.user_uuid = ? ";
	    $dataTenant =  $this->dbSelect($sqlTenant, array($useruuid));
	    $tenantuuid = "";
	    if(!empty($dataTenant)){
	        $tenantuuid = $dataTenant[0]['tenant_uuid'];
	    }

	    //获取用户组列表
	    $sqlUsergroup = "select muug.user_group_uuid from bd_user bu, mt_user_user_group muug where bu.user_uuid = muug.user_uuid and bu.user_uuid = ?";
	    $dataUsergroup = $this->dbSelect($sqlUsergroup, array($useruuid));
	    $usergroupList = array();
	    if(!empty($dataUsergroup)){
	        foreach ($dataUsergroup as $usergroup){
	            $usergroupList[] = $usergroup['user_group_uuid'];
	        }
	    }

	    //获取角色列表
	    $sqlRole = "select mur.role_uuid from bd_user bu, mt_user_role mur where bu.user_uuid = mur.user_uuid and bu.user_uuid = ? ";
	    $dataRole = $this->dbSelect($sqlRole, array($useruuid));
	    $roleList = array();
	    if(!empty($dataRole)){
	        foreach ($dataRole as $role){
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
     * 添加角色和用户关联
     * @param unknown $params
     */
    public function addUserRoleAllocation($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_role_allot");
        $roleuuid = $params['roleuuid'];
        $userList = $params['userList'];

        //删除角色关联
        $sqlDelete = "delete from mt_user_role where role_uuid = ?";
        $result = $this->dbExec($sqlDelete, array($roleuuid));
        //未选中默认取消所有关联
        if(!empty($userList)){
            //添加角色与用户关联
            $result = $this->pAddRoleUser($roleuuid, $userList);
        }

        return $this->muOpResult($result, Xphp::$_lang['WEB_ROLE_ALLOCATION_USER']);


    }


    /**
     * 添加角色和用户组关联
     * @param unknown $params
     * @return string
     */
    public function addUsergroupRoleAllocation($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_role_allot");
        $roleuuid = $params['roleuuid'];
        $userGroupList = $params['userGroupList'];

        //删除角色关联
        $sqlDelete = "delete from mt_user_group_role where role_uuid = ?";
        $result = $this->dbExec($sqlDelete, array($roleuuid));

        //未选中默认取消所有关联
        if(!empty($userGroupList)){
            //添加角色与用户组关联
            $result = $this->pAddRoleUserGroup($roleuuid, $userGroupList);
        }

        return $this->muOpResult($result, Xphp::$_lang['WEB_ROLE_ALLOCATION_USER_GROUP']);

    }


    /**
     * 初始化角色关联用户、用户组列表
     * @param unknown $params
     */
    public function initAllocationList($params){
        $roleuuid = $params['roleuuid'];
        $userList = array();
        $userGroupList = array();
        $sqlParams = array($roleuuid);
        $sqlUser = "select user_uuid from mt_user_role where role_uuid = ?";
        $dataUser = $this->dbSelect($sqlUser, $sqlParams);
        $sqlUserGroup = "select user_group_uuid from mt_user_group_role where role_uuid = ?";
        $dataUserGroup = $this->dbSelect($sqlUserGroup, $sqlParams);
        if(!empty($dataUser)){
            foreach ($dataUser as $user){
                $userList[] = $user['user_uuid'];
            }
        }

        if(!empty($dataUserGroup)){
            foreach ($dataUserGroup as $group){
                $userGroupList[] = $group['user_group_uuid'];
            }
        }

        $info = array(
            'user_list' => $userList,
            'user_group_list' => $userGroupList
        );


        return json_encode($info);

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

    /**
     * 公共方法
     * 获取用户关联资源组
     * @param string $useruuid     用户uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_resource_group表所有字段
     */
    public function pGetUserAllResourceGroup($useruuid){
        $this->paramsCheck($useruuid);

        //用户-资源组
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.description, brg.config
                from bd_user bu, bd_resource_group brg, mt_user_resource_group murg
                where bu.user_uuid = murg.user_uuid and murg.resource_group_uuid = brg.resource_group_uuid and bu.user_uuid = ?";
        $data1 = $this->dbSelect($sql, array($useruuid));

        //用户-用户组-资源组
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.description, brg.config
                from mt_user_user_group muug, bd_resource_group brg, mt_user_group_resource_group mugrg
                where muug.user_group_uuid = mugrg.user_group_uuid and mugrg.resource_group_uuid = brg.resource_group_uuid and muug.user_uuid = ?";
        $data2 = $this->dbSelect($sql, array($useruuid));

        //合并资源组,并去重
        $data = array_merge($data1, $data2);
        $utils = Xphp::instance('Utils');
        $data = $utils->unique_multidim_array($data, "resource_group_uuid");
        return $data;
    }

    /**
     * 公共方法
     * 获取用户组所有用户
     * @param string $usergroupuuid     用户uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_user表所有字段
     */
    public function pGetUserGroupAllUser($usergroupuuid){
        $this->paramsCheck($usergroupuuid);
        $sql = "select distinct bu.user_uuid, bu.user_name, bu.email, bu.telephone, bu.lock_flag, bu.create_time, bu.language, bu.user_type, bt.tenant_name
                from bd_user_group bug, mt_user_user_group muug, bd_user bu left join mt_user_tenant mut on mut.user_uuid = bu.user_uuid left join bd_tenant bt on bt.tenant_uuid = mut.tenant_uuid 
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = bug.user_group_uuid and bug.user_group_uuid = ? and bu.user_uuid <> ? ";
        $data = $this->dbSelect($sql, array($usergroupuuid, Xphp::$_user['useruuid']));
        return $data;
    }

    /**
     * 公共方法
     * 获取用户组关联角色
     * @param string $usergroupuuid     用户uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_role表所有字段
     */
    public function pGetUserGroupAllRole($usergroupuuid){
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
     * @param string $useruuid     用户uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_resource_group表所有字段
     */
    public function pGetUserGroupAllResourceGroup($usergroupuuid){
        $this->paramsCheck($usergroupuuid);
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.description, brg.config
                from bd_user_group bug, bd_resource_group brg, mt_user_group_resource_group mugrg
                where bug.user_group_uuid = mugrg.user_group_uuid and mugrg.resource_group_uuid = brg.resource_group_uuid and bug.user_group_uuid = ?";
        $data = $this->dbSelect($sql, array($usergroupuuid));
        return $data;
    }

    /**
     * 公共方法
     * 获取资源组关联用户
     * @param string $resourcegroupuuid    资源组uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_user表所有字段
     */
    public function pGetResourceGroupAllUser($resourcegroupuuid){
        $this->paramsCheck($resourcegroupuuid);
        $sql = "select distinct bu.user_uuid, bu.user_name, bu.email, bu.telephone, bu.lock_flag, bu.create_time, bu.language, bu.user_type, bt.tenant_name
                from bd_resource_group brg, mt_user_resource_group murg, bd_user bu left join mt_user_tenant mut on mut.user_uuid = bu.user_uuid left join bd_tenant bt on bt.tenant_uuid = mut.tenant_uuid 
                where brg.resource_group_uuid = murg.resource_group_uuid and murg.user_uuid = bu.user_uuid and brg.resource_group_uuid = ? and bu.user_uuid <> ? ";
        $data = $this->dbSelect($sql, array($resourcegroupuuid, Xphp::$_user['useruuid']));
        return $data;
    }

    /**
     * 公共方法
     * 获取资源组关联所有用户组
     * @param string $resourcegroupuuid     资源组uuid
     * @author luokai@vinchin.com
     * @return array    多维数组,对应bd_user_group表所有字段
     */
    public function pGetResourceGroupAllUserGroup($resourcegroupuuid){
        $this->paramsCheck($resourcegroupuuid);
        $sql = "select distinct bug.user_group_uuid, bug.user_group_name, bug.user_group_type, bug.lock_flag, bug.description, bug.config
                from bd_resource_group brg, bd_user_group bug, mt_user_group_resource_group mugrg
                where brg.resource_group_uuid = mugrg.resource_group_uuid and mugrg.user_group_uuid = bug.user_group_uuid and brg.resource_group_uuid = ?";
        $data = $this->dbSelect($sql, array($resourcegroupuuid));
        return $data;
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
     * 检查添加用户是否存在
     * @param string $username
     * @param string $domainuuid
     */
    private function checkDomainUserExist($username, $domainuuid){
        $sql = "select domain_name, domain_ip, port, domain_type, username, password, port from bd_domain_server where domain_uuid = ?";
        $domainSql = 'select user_name from bd_user where domain_uuid = ?';
        $data = $this->dbSelect($sql, array($domainuuid));
        $domainData = $this->dbSelect($domainSql, array($domainuuid));
        $utils = Xphp::instance('Utils');
        if(!empty($data)){
            $domain = $data[0]['domain_name'];
            $nameIndex = strlen($domain."\\");
            //本次添加用户名
            $name = substr($username, $nameIndex);
            $ip = $data[0]['domain_ip'];
            $adminname = $data[0]['username'];

            foreach ($domainData as $d){
                $domainUser = substr($d['user_name'], $nameIndex);
                if($name == $domainUser){
                    exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_ADD_USER'], Xphp::$_lang['WEB_USERS_NAME_EXIST'], "warning"));
                }
            };

            //加密密码
            $password = $utils->decrypt($data[0]['password']);
            $port = $data[0]['port'];
            //首先通过管理员账户连接活动目录服务器

            $user = $adminname.'@'.$domain;//域用户名
            $dcList = explode(".", $domain);
            $basedn = "";
            foreach ($dcList as $key=>$dc){
                $count = count($dcList) - 1;
                if($key == $count){
                    $basedn.= "dc=".$dc;
                }else{
                    $basedn.= "dc=".$dc.",";
                }
            }
//             $basedn = "dc=skkwd,dc=com";
            $justthese = array("mail", "cn", "samaccountname");
            $conn = ldap_connect($ip, $port);//不要写成ldap_connect($host.':'.$port)的形式'
            if ($conn) {
                //设置参数
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
                $bd = ldap_bind($conn, $user, $password);
                if ($bd) {
                    //相当于登录成功
                } else {
                    exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_ADD_USER'], Xphp::$_lang['WEB_DOMAIN_SERVER_ADD_USER_PASSWORD_ERROR'], "warning"));
                }
            } else {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_ADD_USER'], Xphp::$_lang['WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR'], "warning"));
            }
            $filter = "(&(objectCategory=person)(objectClass=user))";
            $searchResult = ldap_search($conn, $basedn, $filter);
            $userArray = ldap_get_entries($conn, $searchResult);
            foreach ($userArray as $user){
                if($user['samaccountname'][0] == $name){
                    return true;
                }
            }
        }

        exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_ADD_USER'], Xphp::$_lang['WEB_USERS_ADD_NOT_EXIST'], "warning"));

    }


    /**
     * 公共方法
     * 检查用户是否属于Master用户组或角色
     * @param string $useruuid
     * @author luokai@vinchin.com
     * @return boolean 返回检查结果 是|否
     */
    public function pCheckUserIsMaster($useruuid){
        $masterFlag = FALSE;
        //获取用户所在所有用户组和所有角色
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
     * 检测资源和用户是否存在关联
     * @param string $useruuid 用户唯一标识
     * @param string $resourceuuid 资源唯一标识
     * @return boolean 返回关联标记
     */
    public function pGetUserResourceFlag($useruuid, $resourceuuid, $vmFlag = false){
        $sql = "select id from mt_user_resource where resource_uuid = ? ";
        $sqlParams = array($resourceuuid);
        if(!$vmFlag){
            $sql .= " and user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }else{
            $sql .= " and user_uuid <> ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
        }
        $data= $this->dbSelect($sql, $sqlParams);
        $flag = FALSE;
        if(!empty($data)){
            $flag = TRUE;
        }

        return $flag;
    }

    /**
     * 公共方法
     * 检测虚拟机和用户是否存在关联
     * @param string $useruuid 用户唯一标识
     * @param string $resourceuuid 资源唯一标识
     * @return boolean 返回关联标记
     */
    public function pGetUserVmFlag($resourceuuid){
        $sql = "select id from mt_user_resource where resource_uuid = ? ";
        $data= $this->dbSelect($sql, array($resourceuuid));
        $flag = FALSE;
        if(!empty($data)){
            $flag = TRUE;
        }

        return $flag;
    }

    /**
     * 公共方法
     * 检测资源组和用户是否存在关联
     * @param string $useruuid 用户唯一标识
     * @param string $resourcegroupuuid 资源组唯一标识
     * @return boolean 返回关联标记
     */
    public function pGetUserResourceGroupFlag($useruuid, $resourcegroupuuid, $vmFlag = false){
        $sql = "select distinct brg.id from mt_user_resource_group murg, bd_resource_group brg where brg.resource_group_uuid = 
                murg.resource_group_uuid and brg.resource_group_uuid = ? ";
        $sqlParams = array($resourcegroupuuid);
        if(!$vmFlag){
            $sql .= " and murg.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($useruuid));
        }else{
            $sql .= " and murg.user_uuid <> ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
        }
        $data= $this->dbSelect($sql, $sqlParams);
        $flag = FALSE;
        if(!empty($data)){
            $flag = TRUE;
        }

        return $flag;
    }



    /**
     * 公共方法
     * 检测资源和用户组是否存在关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param string $resourceuuid 资源唯一标识
     * @return boolean 返回关联标记
     */
    public function pGetUserGroupResourceFlag($usergroupuuid, $resourceuuid, $vmFlag = false){
        $sql = "select id from mt_user_group_resource where resource_uuid = ? ";
        $sqlParams = array($resourceuuid);
        //除虚拟机资源以外的资源可以共享分配
        if(!$vmFlag){
            $sql .= " and user_group_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($usergroupuuid));
        }
        $data= $this->dbSelect($sql, $sqlParams);
        $flag = FALSE;
        if(!empty($data)){
            $flag = TRUE;
        }

        return $flag;
    }

    /**
     * 公共方法
     * 检测虚拟机和用户组是否存在关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param string $resourceuuid 资源唯一标识
     * @return boolean 返回关联标记
     */
    public function pGetUserGroupVmFlag($resourceuuid){
        $sql = "select id from mt_user_group_resource where resource_uuid = ? ";
        $data= $this->dbSelect($sql, array($resourceuuid));
        $flag = FALSE;
        if(!empty($data)){
            $flag = TRUE;
        }

        return $flag;
    }

    /**
     * 公共方法
     * 检测资源组和用户组是否存在关联
     * @param string $usergroupuuid 用户组唯一标识
     * @param string $resourcegroupuuid 资源组唯一标识
     * @return boolean 返回关联标记
     */
    public function pGetUserGroupResourceGroupFlag($usergroupuuid, $resourcegroupuuid, $vmFlag){
        $sql = "select id from mt_user_group_resource_group where resource_group_uuid = ? ";
        $sqlParams = array($resourcegroupuuid);
        //除虚拟机资源以外的资源可以共享分配
        if(!$vmFlag){
            $sql .= " and use_group_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($usergroupuuid));
        }
        $data= $this->dbSelect($sql, $sqlParams);
        $flag = FALSE;
        if(!empty($data)){
            $flag = TRUE;
        }

        return $flag;
    }



    /**
     * 添加用户和文件代理关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserFileHost($params){
        $userUUID = $params['userUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['FILE_HOST'];
        $opName = Xphp::$_lang['WEB_USERS_ADD_FILE_HOST'];
        return $this->pAddUserResourceUnify($userUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户和文件代理关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserFileHost($params){
        $userUUID = $params['userUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USERS_CANCEL_FILE_HOST'];

        //调用统一处理取消用户与文件代理关联
        return $this->pDeleteUserResourceUnify($userUUID, $uuids, $opName);

    }

    /**
     * 添加用户和数据库定时主机关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserDbHost($params){
        $userUUID = $params['userUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['DB_HOST'];
        $opName = Xphp::$_lang['WEB_USERS_ADD_DB_HOST'];
        return $this->pAddUserResourceUnify($userUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户和数据库定时主机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserDbHost($params){
        $userUUID = $params['userUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USERS_CANCEL_DB_HOST'];

        //调用统一处理取消用户与文件代理关联
        return $this->pDeleteUserResourceUnify($userUUID, $uuids, $opName);

    }

    /**
     * 添加用户和数据库实时主机关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserCDPHost($params){
        $userUUID = $params['userUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['CDP_HOST'];
        $opName = Xphp::$_lang['WEB_USERS_ADD_CDP_HOST'];
        return $this->pAddUserResourceUnify($userUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户和数据库实时主机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserCDPHost($params){
        $userUUID = $params['userUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USERS_CANCEL_CDP_HOST'];

        //调用统一处理取用户和数据库实时主机关联
        return $this->pDeleteUserResourceUnify($userUUID, $uuids, $opName);

    }

    /**
     * 添加用户和虚拟机关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserVM($params){
        $userUUID = $params['userUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['VM'];
        $opName = Xphp::$_lang['WEB_USERS_ADD_VM'];
        return $this->pAddUserResourceUnify($userUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户和虚拟机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserVM($params){
        $userUUID = $params['userUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USERS_CANCEL_VM'];
        //检查虚拟机资源是否正在使用
        $this->checkDeleteUserVM($userUUID, $uuids);

        //调用统一处理取消用户和虚拟机关联
        return $this->pDeleteUserResourceUnify($userUUID, $uuids, $opName);

    }

    /**
     * 添加用户和虚拟机备份代理关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserAppliance($params){
        $userUUID = $params['userUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM'];
        $opName = Xphp::$_lang['WEB_USERS_ADD_APPLIANCE'];
        return $this->pAddUserResourceUnify($userUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户和虚拟机备份代理关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserAppliance($params){
        $userUUID = $params['userUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USERS_CANCEL_APPLIANCE'];

        //调用统一处理取消用户和虚拟机备份代理关联
        return $this->pDeleteUserResourceUnify($userUUID, $uuids, $opName);

    }

    /**
     * 添加用户和文件代理关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserNode($params){
        $userUUID = $params['userUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['NODE'];
        $opName = Xphp::$_lang['WEB_USERS_ADD_NODE'];
        return $this->pAddUserResourceUnify($userUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户和节点关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserNode($params){
        $userUUID = $params['userUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USERS_CANCEL_NODE'];
        //检查资源是否正在使用
        $this->checkDeleteUserNode($userUUID, $uuids);
        //调用统一处理取消用户和节点关联
        return $this->pDeleteUserResourceUnify($userUUID, $uuids, $opName);

    }

    /**
     * 添加用户和存储关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserStorage($params){
        $userUUID = $params['userUUID'];
        $resourceList = $params['list'];
        $resourceHandler = Xphp::instance('ResourceHandler');
        //关联存储所在节点
        $resourceHandler->addUserStorageNode($userUUID, $resourceList);
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['STORAGE'];
        $opName = Xphp::$_lang['WEB_USERS_ADD_STORAGE'];
        return $this->pAddUserResourceUnify($userUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户和存储关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserStorage($params){
        $userUUID = $params['userUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USERS_CANCEL_STORAGE'];
        //检查资源是否正在使用
        $this->checkDeleteUserStorage($userUUID, $uuids);
        //调用统一处理取消用户和存储关联
        return $this->pDeleteUserResourceUnify($userUUID, $uuids, $opName);

    }

    /**
     * 添加用户和存储关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserResourceGroup($params){
        $userUUID = $params['userUUID'];
        $resourceGroupList = $params['list'];
        $opName = Xphp::$_lang['WEB_USERS_ADD_RESOURCE_GROUP'];
        $result = $this->pAddUserResourceGroup($userUUID, $resourceGroupList);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName, "", "warning");
        }
    }

    /**
     * 取消用户和存储关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserResourceGroup($params){
        $userUUID = $params['userUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USERS_CANCEL_RESOURCE_GROUP'];

        $result = $this->pDeleteUserResourceGroup($userUUID, $uuids);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName, "", "warning");
        }

    }





    /**
     * 公共方法
     * 添加资源与用户关联
     * @param string $userUUID 用户唯一标识
     * @param array $resourceList 资源信息列表
     * @param int $resourceType 资源类型
     * @param string $opName 添加关联操作描述
     * @author luokai@vinchin.com
     * @return string 统一返回操作结果消息到界面
     */
    public function pAddUserResourceUnify($userUUID, $resourceList, $resourceType = "", $opName = ''){
        //调用添加用户与资源关联公共方法
        $resourceInfo=  array();
        if(!empty($resourceList)){
            foreach ($resourceList as $res){
                $resourceInfo[] = array(
                    'resourceuuid' => $res['resourceuuid'],
                    'vmuuid' => $res['vmuuid'],
                    'vcenteruuid' => $res['vcenteruuid'],
                    'resourceType' => $resourceType
                );
            }
        }
        $result = $this->pAddUserResource($userUUID, $resourceInfo);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName,"", "warning");
        }
    }



    /**
     * 公共方法
     * 取消用户和资源关联
     * @param string $userUUID
     * @param array $resourceList
     * @param string $opName
     * @author luokai@vinchin.com
     * @return string
     */
    public function pDeleteUserResourceUnify($userUUID, $resourceList, $opName){
        //删除前检查是否有属于资源组或用户组的资源
        $resourceCount = count($resourceList);
        $resourceDes = implode("','", $resourceList);
        $sql = "select count(resource_uuid) as total from mt_user_resource where user_uuid = ? and resource_uuid in ('".$resourceDes."')";
        $data = $this->dbSelect($sql, array($userUUID));
        $realCount = intval($data[0]['total']);
        $otherFromFlag = ($resourceCount - $realCount) != 0;//选择了来自资源组或用户组关联的资源标志
        //调用取消用户与资源关联公共方法
        $result = $this->pDeleteUserResource($userUUID, $resourceList);
        if($result){
            if($otherFromFlag){
                return $this->muOpResult(true, $opName,Xphp::$_lang['UI_PLATFORM_CANCEL_RESOURCE'], "success");
            }else{
                return $this->muOpResult(true, $opName);
            }
        }else{
            return $this->muOpResult(false, $opName,"", "warning");
        }
    }



    /**
     * 添加用户组和文件代理关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupFileHost($params){
        $userGroupUUID = $params['userGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['FILE_HOST'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_ADD_FILE_HOST'];
        return $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户组和文件代理关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserGroupFileHost($params){
        $userGroupUUID = $params['userGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_CANCEL_FILE_HOST'];

        //调用统一处理取消用户组与文件代理关联
        return $this->pDeleteUserGroupResourceUnify($userGroupUUID, $uuids, $opName);

    }

    /**
     * 添加用户组和数据库定时主机关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupDbHost($params){
        $userGroupUUID = $params['userGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['DB_HOST'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_ADD_DB_HOST'];
        return $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户组和数据库定时主机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserGroupDbHost($params){
        $userGroupUUID = $params['userGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_CANCEL_DB_HOST'];

        //调用统一处理取消用户组与数据库定时主机关联
        return $this->pDeleteUserGroupResourceUnify($userGroupUUID, $uuids, $opName);

    }

    /**
     * 添加用户组和数据库实时主机关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupCDPHost($params){
        $userGroupUUID = $params['userGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['CDP_HOST'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_ADD_CDP_HOST'];
        return $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户组和数据库实时主机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserGroupCDPHost($params){
        $userGroupUUID = $params['userGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_CANCEL_CDP_HOST'];

        //调用统一处理取用户组和数据库实时主机关联
        return $this->pDeleteUserGroupResourceUnify($userGroupUUID, $uuids, $opName);

    }

    /**
     * 添加用户组和虚拟机关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupVM($params){
        $userGroupUUID = $params['userGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['VM'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_ADD_VM'];
        return $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户组和虚拟机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserGroupVM($params){
        $userGroupUUID = $params['userGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_CANCEL_VM'];

        //调用统一处理取消用户组和虚拟机关联
        return $this->pDeleteUserGroupResourceUnify($userGroupUUID, $uuids, $opName);

    }

    /**
     * 添加用户组和虚拟机备份代理关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupAppliance($params){
        $userGroupUUID = $params['userGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_ADD_APPLIANCE'];
        return $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户组和虚拟机备份代理关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserGroupAppliance($params){
        $userGroupUUID = $params['userGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_CANCEL_APPLIANCE'];

        //调用统一处理取消用户组和虚拟机备份代理关联
        return $this->pDeleteUserGroupResourceUnify($userGroupUUID, $uuids, $opName);

    }

    /**
     * 添加用户组和文件代理关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupNode($params){
        $userGroupUUID = $params['userGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['NODE'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_ADD_NODE'];
        return $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户组和节点关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserGroupNode($params){
        $userGroupUUID = $params['userGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_CANCEL_NODE'];

        //调用统一处理取消用户和节点关联
        return $this->pDeleteUserGroupResourceUnify($userGroupUUID, $uuids, $opName);

    }

    /**
     * 添加用户组和存储关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupStorage($params){
        $userGroupUUID = $params['userGroupUUID'];
        $resourceList = $params['list'];
        $resourceHandler = Xphp::instance('ResourceHandler');
        //关联存储所在节点
        $resourceHandler->addUserGroupStorageNode($userGroupUUID, $resourceList);
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['STORAGE'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_ADD_STORAGE'];
        return $this->pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType, $opName);
    }

    /**
     * 取消用户组和存储关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserGroupStorage($params){
        $userGroupUUID = $params['userGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_CANCEL_STORAGE'];

        //调用统一处理取消用户组和存储关联
        return $this->pDeleteUserGroupResourceUnify($userGroupUUID, $uuids, $opName);

    }

    /**
     * 添加用户组和存储关联关系
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addUserGroupResourceGroup($params){
        $userGroupUUID = $params['userGroupUUID'];
        $resourceGroupList = $params['list'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_ADD_RESOURCE_GROUP'];
        $result = $this->pAddUserGroupResourceGroup($userGroupUUID, $resourceGroupList);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName, "", "warning");
        }
    }

    /**
     * 取消用户组和存储关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteUserGroupResourceGroup($params){
        $userGroupUUID = $params['userGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_USER_GROUP_CANCEL_RESOURCE_GROUP'];

        $result = $this->pDeleteUserGroupResourceGroup($userGroupUUID, $uuids);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName, "", "warning");
        }

    }





    /**
     * 公共方法
     * 添加资源与用户组关联
     * @param string $userGroupUUID 用户组唯一标识
     * @param array $resourceList 资源信息列表
     * @param int $resourceType 资源类型
     * @param string $opName 添加关联操作描述
     * @author luokai@vinchin.com
     * @return string 统一返回操作结果消息到界面
     */
    public function pAddUserGroupResourceUnify($userGroupUUID, $resourceList, $resourceType = "", $opName = ''){
        //调用添加用户组与资源关联公共方法
        $resourceInfo=  array();
        if(!empty($resourceList)){
            foreach ($resourceList as $res){
                $resourceInfo[] = array(
                    'resourceuuid' => $res['resourceuuid'],
                    'vmuuid' => $res['vmuuid'],
                    'vcenteruuid' => $res['vcenteruuid'],
                    'resourceType' => $resourceType
                );
            }
        }
        $result = $this->pAddUserGroupResource($userGroupUUID, $resourceInfo);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName,"", "warning");
        }
    }



    /**
     * 公共方法
     * 取消用户组和资源关联
     * @param string $userUUID
     * @param array $resourceList
     * @param string $opName
     * @author luokai@vinchin.com
     * @return string
     */
    public function pDeleteUserGroupResourceUnify($userGroupUUID, $resourceList, $opName){
        //调用取消用户组与资源关联公共方法
        $result = $this->pDeleteUserGroupResource($userGroupUUID, $resourceList);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName,"", "warning");
        }
    }


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
     * 获取用户类型来源
     * @param intval $userType
     * @author luokai@vinchin.com
     * @return string
     */
    public function pGetUserTypeDes($userType){
        if($userType == 1){
            $userDes = Xphp::$_lang['UI_USER_LOCATION'];
        }else{
            $userDes =  Xphp::$_lang['UI_USER_EXTERNAL'];
        }

        return $userDes;
    }

    /**
     * 公共方法
     * 根据用户唯一标识获取其所在租户信息，没在租户内返回空数组
     * @param unknown $useruuid
     * @return array|fetchAll()[]
     */
    public function pGetTenantInfoByUser($useruuid){
        $sql = "select bt.tenant_name from bd_tenant bt, mt_user_tenant mut where bt.tenant_uuid = mut.tenant_uuid and mut.user_uuid = ?";
        $data = $this->dbSelect($sql, array($useruuid));

        $info = array();

        if(!empty($data)){
            $info = array(
                'tenantname' => $data[0]['tenant_name']
            );
        }

        return $info;

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
     * 设置账户安全信息
     * @param unknown $params
     * @return string
     */
    public function setSafeConfig($params){
        $outtime = $params['outtime'];
        $faildcount = $params['faildcount'];
        $passwordtime = $params['passwordtime'];
        $passlength = $params['passlength'];
        $passcomplexity = $params['passcomplexity'];
        $faildlocktime = $params['faild_lock_time'];
        $createuserlevel = $_SESSION['isThreePowers'] ? $_SESSION['userLevel'] : 0;

        $sql = "select id from bd_account_safe where create_user_level = ?";
        $data = $this->dbSelect($sql, array($createuserlevel));
        $sqlParams = array($outtime, $faildcount, $passwordtime, $passlength, $passcomplexity, $faildlocktime, $createuserlevel);
        if (!empty($data)) {
            // 存在，那么就修改
            $sql = "update bd_account_safe set 
                           login_timeout =?, login_failure = ?, pass_timeout = ?, pass_length = ?,
                           pass_complexity = ?, login_failed_lock_time = ?, create_user_level = ? where create_user_level = ?";
            $sqlParams = array_merge($sqlParams, [$createuserlevel]);
        } else {
            // 新增
            $sql = "insert into bd_account_safe (login_timeout,login_failure,pass_timeout,pass_length,pass_complexity,login_failed_lock_time,create_user_level) 
                    values (?,?,?,?,?,?,?);";
        }
        $result = $this->dbExec($sql, $sqlParams);
        $this->unifyWriteSystemLog($result, "SYSTEM_LOG_SETTINGS_ACCOUNT_SAFE");
        return $this->muOpResult($result, Xphp::$_lang['UI_USER_CONFIG_SAFE_INFO']);
    }

    /**
     * 统一写系统日志
     * @param boolean $result
     * @param string $descriptionKey
     * @param array $descriptionParam
     */
    private function unifyWriteSystemLog($result, $descriptionKey, $descriptionParam = array()){
        if($result){
            $this->systemLog($descriptionKey, $descriptionParam);
        }else{
            $this->systemLog($descriptionKey, $descriptionParam, Xphp::$_config['LOGLEVEL']['WARN']);
        }
    }

    /**
     * 获取账户安全配置
     * @return string
     */
    public function getSafeConfig(){
        $sql = "select login_timeout, login_failure, pass_timeout, pass_length, pass_complexity,login_failed_lock_time,create_user_level from bd_account_safe";
        $where = ' where create_user_level = ?';
        $userLevel = 0;
        if ($_SESSION['isThreePowers']) {
            // 是三权模式 那么就必须按照当前用户级别来读取配置
            $userLevel = $_SESSION['userLevel'] ?? 0;
        }
        $data = $this->dbSelect($sql . $where, [$userLevel]);

        $info = array(
            'passcomplexity' => 2,
            'is_three_powers' => $_SESSION['isThreePowers'],
            'user_level' => $_SESSION['userLevel'],
        );
        if(!empty($data)){
            if ($_SESSION['isThreePowers']) {
                $info = array(
                    'out_time' => min($data[0]['login_timeout'], 600),
                    'faild_count' => min($data[0]['login_failure'], 5),
                    'password_time' => min($data[0]['pass_timeout'], 7),
                    'passlength' => max($data[0]['pass_length'], 8),
                    'passcomplexity' => max($data[0]['pass_complexity'], 2),
                    'faild_lock_time' => max($data[0]['login_failed_lock_time'], 1800),
                    'is_three_powers' => $_SESSION['isThreePowers'],
                    'user_level' => $_SESSION['userLevel'],
                );
            } else {
                $info = array(
                    'out_time' => $data[0]['login_timeout'],
                    'faild_count' => $data[0]['login_failure'],
                    'password_time' => $data[0]['pass_timeout'],
                    'passlength' => $data[0]['pass_length'],
                    'passcomplexity' => $data[0]['pass_complexity'],
                    'faild_lock_time' => $data[0]['login_failed_lock_time'],
                    'is_three_powers' => $_SESSION['isThreePowers'],
                    'user_level' => $_SESSION['userLevel'],
                );
            }
        }
        return json_encode($info);
    }

    /**
     * 获取7天内密码过期提示
     * @return string
     */
    public function getLoginHistory(){
        $sql = "select unix_timestamp(create_time) create_time ,user_type from bd_user where user_uuid = ?";
        $users = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $userTime = intval($users[0]['create_time']);
        $userType =intval($users[0]['user_type']);
        $platformHandler = Xphp::instance('PlatformHandler');
        $safeInfo = $platformHandler->getAccountSafe();
        $passTimeout = $safeInfo['password_time'];
        $nowTime = time() - $userTime;
        $overDays = round($nowTime/3600/24);

        $tips = "";
        //密码7天内过期
        if($passTimeout - $overDays <= 7 && $userType!== 2){
            $tips = Xphp::$_lang['WEB_USERS_PASSWORD_EXPIRE_TIPS'];
        }

        $info = array(
            'tips' => $tips
        );

        return json_encode($info);
    }

    /**
     * 检查是否有删除默认组操作
     * @param string $userGroupdes
     * @return boolean
     */
    private function checkIfDefault($userGroupdes){
        $sql = "select user_group_uuid from bd_user_group where user_group_type = ? and user_group_uuid in ('".$userGroupdes."') ";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']));
        if(!empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['UI_USER_GROUP_DELETE'], Xphp::$_lang['WEB_USER_GROUP_DELETE_TIPS'], "warning"));
        }

        return true;
    }

    /**
     * 公共方法
     * 检查是否是租户直属管理员
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pCheckTenantManager(){
        $flag = false;
        if(empty($_SESSION['tenantuuid'])) return $flag;
        $sql = "select admin_uuid from bd_tenant where tenant_uuid = ?";
        $data = $this->dbSelect($sql, array($_SESSION['tenantuuid']));
        if(!empty($data)){
            if($data[0]['admin_uuid'] == Xphp::$_user['useruuid']){
                $flag = true;   //如果是租户直属管理员
            }
        }

        return $flag;
    }

    /**
     * 获取当前用户配额信息
     * @return number[]|fetchAll()[]
     */
    public function getUserQuotaInfo(){
        $utils = Xphp::instance('Utils');
        $sql = "select quota from bd_user_extension where user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $sqlUsed = "select sum(write_size) as used_size from bd_backup_timepoint where user_uuid = ? ";
        $dataUsed = $this->dbSelect($sqlUsed, array(Xphp::$_user['useruuid']));
        $quota = 0;
        $usedSize = 0;
        $freeSize = 0;
        $des = "";
        if(!empty($data)){
            $quota = intval($data[0]['quota']);
        }
        if(!empty($dataUsed)){
            $usedSize = intval($dataUsed[0]['used_size']);
        }

        if($quota == -1){
            $des = Xphp::$_lang['WEB_USERS_QUOTA_UNLIMIT'];
        }else{
            $freeSize = $quota - $usedSize;
            $freeSizeDes = $utils->calSize($freeSize);
            if($freeSize < 0){
                $freeSize = 0;
                $freeSizeDes = 0;
            }
            $des = Xphp::$_lang['WEB_USERS_QUOTA_TOTAL_SIZE'] . ": " . $utils->calSize($quota);
            $des .= "," . Xphp::$_lang['WEB_USERS_QUOTA_FREE_SIZE']. ": " . $freeSizeDes;
        }

        $info = array(
            'quota' => $quota,
            'freeSize' => $freeSize,
            'des' => $des
        );

        return $info;
    }

    /**
     * 通过域唯一标识获取关联租户
     * @param string $domainuuid
     * @return string|fetchAll()
     */
    public function getDomainTenantuuid($domainuuid){
        $sql = "select tenant_uuid from bd_domain_server where domain_uuid = ?";
        $data = $this->dbSelect($sql, array($domainuuid));
        $tenantuuid = "";
        if(!empty($data)){
            $tenantuuid = $data[0]['tenant_uuid'];
        }

        return $tenantuuid;
    }

    /**
     * 检查原来密码是否正确
     * @param unknown $params
     * @return string
     */
    public function oldpassAvailable($params){
        $password = $params['password'];
        $useruuid = $params['useruuid'];
        if(empty($useruuid)){
            $useruuid = Xphp::$_user['useruuid'];
        }
        $sql = "select id from bd_user where user_uuid = ? and password = ? ";
        $sqlParams = array($useruuid, $password);
        $users = parent::dbSelect($sql, $sqlParams);
        return json_encode(!empty($users));
    }

    /**
     * 获取当前用户密码md5
     * @param unknown $params
     * @return string
     */
    public function getUserPassword($params){
        $sql = "select password from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $password = "vinchin";
        if(!empty($data)){
            $password = $data[0]['password'];
        }
        $info = array(
            'password' => $password
        );

        return json_encode($info);

    }

    /**
     * 获取用户权限树
     * @param unknown $params
     * @return string
     */
    public function getUserPermissionTree($params){
        $useruuid = $params['useruuid'];
        $permission = require_once CONF_PATH . 'permission.php';
        $page = require_once CONF_PATH . 'page.php';
        $roleHandler = Xphp::instance('RoleHandler');
        $userPermission = $roleHandler->pGetUserAllPermission($useruuid, true);

        $userPermission = $roleHandler->gGetPageLatest($userPermission, $useruuid, 0);

        $userTree = $this->getUserAllTreeNodes($page, 0, $userPermission, true);

        $userInfo = $userTree;

        return json_encode($userInfo);
    }

    /**
     * 获取用户组权限树
     * @param unknown $params
     * @return string
     */
    public function getUserGroupPermissionTree($params){
        $usergroupuuid = $params['usergroupuuid'];
        $permission = require_once CONF_PATH . 'permission.php';
        $page = require_once CONF_PATH . 'page.php';
        $roleHandler = Xphp::instance('RoleHandler');
        $userPermission = $roleHandler->pGetUserGroupAllPermission($usergroupuuid);

        // 更改 获取更深层次的操作
        $userPermission = $roleHandler->gGetPageLatest($userPermission, $usergroupuuid, 2);

        $userTree = $this->getUserAllTreeNodes($page, 0, $userPermission, true);

        $userInfo = $userTree;

        return json_encode($userInfo);
    }

    /**
     * 获取用户关联用户组
     * @param unknown $params
     * @return string
     */
    public function getUserUserGroup($params){
        $useruuid = $params['useruuid'];
        $start = $params['start'];
        $length = $params['length'];
        $userGroupList = $this->pGetUserAllUserGroup($useruuid);
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

    /**
     * 获取用户关联角色
     * @param unknown $params
     * @return string
     */
    public function getUserRole($params){
        $useruuid = $params['useruuid'];
        $start = $params['start'];
        $length = $params['length'];
        $roleList = $this->pGetUserAllRole($useruuid);
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $count = count($roleList);
        $utils = Xphp::instance('Utils');
        $roleList = $utils->arraySort($roleList, 'role_name', 'asc', $start, $length);
        foreach ($roleList as $d){
            $records["data"][] = array(
                $id,
                $d['role_name'],
            );
            $id++;
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;

        return  json_encode($records);
    }

    /**
     * 获取用户关联资源组
     * @param unknown $params
     * @return string
     */
    public function getUserResourceGroup($params){
        $useruuid = $params['useruuid'];
        $start = $params['start'];
        $length = $params['length'];
        $resourceGroupList = $this->pGetUserAllResourceGroup($useruuid);
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $count = count($resourceGroupList);
        $utils = Xphp::instance('Utils');
        $resourceGroupList = $utils->arraySort($resourceGroupList, 'resource_group_name', 'asc', $start, $length);
        foreach ($resourceGroupList as $d){
            $records["data"][] = array(
                $id,
                $d['resource_group_name'],
            );
            $id++;
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;

        return  json_encode($records);
    }

    /**
     * 获取用户组关联用户
     * @param unknown $params
     * @return string
     */
    public function getUserGroupUser($params){
        $usergroupuuid = $params['usergroupuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $userList = $this->pGetUserGroupAllUser($usergroupuuid);
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
     * 获取用户组关联角色
     * @param unknown $params
     * @return string
     */
    public function getUserGroupRole($params){
        $usergroupuuid = $params['usergroupuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $roleList = $this->pGetUserGroupAllRole($usergroupuuid);
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $count = count($roleList);
        $utils = Xphp::instance('Utils');
        $roleList = $utils->arraySort($roleList, 'role_name', 'asc', $start, $length);
        foreach ($roleList as $d){
            $records["data"][] = array(
                $id,
                $d['role_name'],
            );
            $id++;
        }

        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;

        return  json_encode($records);
    }

    /**
     * 获取用户组关联资源组
     * @param unknown $params
     * @return string
     */
    public function getUserGroupResourceGroup($params){
        $usergroupuuid = $params['usergroupuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $resourceGroupList = $this->pGetUserGroupAllResourceGroup($usergroupuuid);
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $count = count($resourceGroupList);
        $utils = Xphp::instance('Utils');
        $resourceGroupList = $utils->arraySort($resourceGroupList, 'resouce_group_name', 'asc', $start, $length);
        foreach ($resourceGroupList as $d){
            $records["data"][] = array(
                $id,
                $d['resource_group_name'],
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
    public function getResourceGroupUser($params){
        $resourcegroupuuid = $params['resourcegroupuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $userList = $this->pGetResourceGroupAllUser($resourcegroupuuid);
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
    public function getResourceGroupUserGroup($params){
        $resourcegroupuuid = $params['resourcegroupuuid'];
        $start = $params['start'];
        $length = $params['length'];
        $userGroupList = $this->pGetResourceGroupAllUserGroup($resourcegroupuuid);
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

    /**
     * 检查用户配额是否超出限制
     * @param int $quota        配额大小
     * @param string $operate   操作描述
     */
    private function checkUserMaxStorage($quota, $operate){
        $storageHandler = Xphp::instance('StorageHandler');
        $maxStorageInfo = $storageHandler->getMaxStorage();
        $maxStorageInfo = json_decode($maxStorageInfo, true);
        if($maxStorageInfo['svalue'] != -1 && $quota > $maxStorageInfo['svalue']){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_PLATFORM_SIZE_GT_TATAL_REDISTRIBUTE'], "warning"));
        }

        return true;
    }

    /**
     * 禁用用户停止任务
     * @param unknown $users
     * @return boolean
     */
    private function lockUserStopJob($users){
        if(empty($users)) return true;
        $userDes = implode("','", $users);
        $jobHandler = Xphp::instance('JobHandler');
        $i = 0;
        $sql = "select task_uuid, task_type, module_type, task_status from bd_task where task_status != ? and user_uuid in ('".$userDes."')";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['STOPPED']));
        if(!empty($data)){
            //循环去停止任务
            foreach ($data as $d){
                $subModule = $jobHandler->pGetTaskSubmodule($d['task_uuid'], $d['module_type'], $d['task_type']);
                $params = array(
                    "uuid" => $d['task_uuid'],
                    "module" => $d['module_type'],
                    "taskType" => $d['task_type'],
                    "subModule" => $subModule,
                    "status" => $d['task_status']
                );
                $jobHandler->stopJob($params);
            }
        }


        //stop all jobs;
        return true;
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
            exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_CANCEL_VM'], Xphp::$_lang['UI_PLATFORM_SELECTED_RES_USING'], "warning"));
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
            exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_CANCEL_STORAGE'], Xphp::$_lang['UI_PLATFORM_SELECTED_RES_USING'], "warning"));
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
            exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_CANCEL_NODE'], Xphp::$_lang['UI_PLATFORM_SELECTED_RES_USING'], "warning"));
        }
        return;
    }

    /**
     * 公共方法
     * 获取用户所在租户唯一标识
     * @param unknown $useruuid
     * @return string|fetchAll()
     */
    public function pGetUserTenantUUID($useruuid){
        $sql = "select tenant_uuid from mt_user_tenant where user_uuid = ? ";
        $data = $this->dbSelect($sql, array($useruuid));
        $tenantuuid = "";
        if(!empty($data)){
            $tenantuuid = $data[0]['tenant_uuid'];
        }

        return $tenantuuid;
    }

    /**
     * 公共方法
     * 获取用户组所在租户唯一标识
     * @param unknown $usergroupuuid
     * @return string|fetchAll()
     */
    public function pGetUserGroupTenantUUID($usergroupuuid){
        $sql = "select tenant_uuid from mt_user_group_tenant where user_group_uuid = ? ";
        $data = $this->dbSelect($sql, array($usergroupuuid));
        $tenantuuid = "";
        if(!empty($data)){
            $tenantuuid = $data[0]['tenant_uuid'];
        }

        return $tenantuuid;
    }



    /**
     * 删除用户对于历史任务
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

    public function pDeleteUserTaskLog($user, $logHandler){
        $sql = "select distinct id from bd_task_log where user_uuid = ? ";
        $data = $this->dbSelect($sql,array($user));
        if(empty($data)) return;
        $ids = array();
        foreach ($data as $d){
            $ids[] = $d['id'];
        }

        $params = array(
            'id' => $ids,
            'tenantFlag' => true
        );
        $logHandler->deleteTaskLog($params);

        return true;

    }

    public function pDeleteUserSystemLog($user, $logHandler){
        $sql = "select distinct id from bd_system_log where user_uuid = ? ";
        $data = $this->dbSelect($sql,array($user));
        if(empty($data)) return;
        $ids = array();
        foreach ($data as $d){
            $ids[] = $d['id'];
        }

        $params = array(
            'id' => $ids,
            'tenantFlag' => true
        );
        $logHandler->deleteSystemLog($params);

        return true;

    }

    public function pDeleteUserTaskAlarm($user, $alarmHandler){
        $sql = "select distinct task_alarm_id from bd_task_alarm where user_uuid = ? ";
        $data = $this->dbSelect($sql,array($user));
        if(empty($data)) return;
        $ids = array();
        foreach ($data as $d){
            $ids[] = $d['task_alarm_id'];
        }

        $params = array(
            'id' => $ids,
            'tenantFlag' => true
        );
        $alarmHandler->deleteTaskAlarm($params);
        return true;
    }
    /**
     * 忘记密码
     *
     */
    //TODO  去参数校验用户名 用户名是否为空 如不为 是否匹配
//     public function forgetPwd($params){
//         $utils = Xphp::instance('utils');
//         $username = $utils->decryptJsRsa($params['username']);
//         $email= $utils->decryptJsRsa($params['email']);
//         return $this->emailVerify($username, $email);
//     }
    /**
     * 验证用户名和邮箱
     */
    //TODO 查询输入的用户名是否在表中，如果不在则提示 ，如果在则查询输入的邮箱是否匹配
    //TODO 涉及到的数据表 bd_user bd_tenant mt_user_tenant
    public function userInfoVerify($params){
//      判断是否配置了邮箱服务器
        $emailSql = " select *from bd_email_notice where email_notice_flag=?";
        $emailData = $this->dbSelect($emailSql, array(Xphp::$_config['FLAG']['SET']));
        if (empty($emailData)) {
            $info = array('result' => 4);
            return json_encode($info);
        }
        $utils = Xphp::instance('utils');
        $username = $utils->decryptJsRsa($params['username']);//收件人用户名-已解密
        $flag = false;
        $sql = "select user_type,domain_uuid from bd_user where binary user_name = ?";
        $data = $this->dbSelect($sql, array($username));
        $userType = intval($data[0]['user_type']);
        if ($userType == 2) {
            // 排除域用户
            $result = 6;
            $info=array(
                'result' => $result
            );
            return json_encode($info);
        } else {
        if (strpos($username, '\\') !== false) {
            $usernameExp = explode('\\', $username);
            $tenantname = $usernameExp[0];  //  取租户的名字
            $tenantusername = $usernameExp[1];
            $sql = "SELECT * FROM `bd_user` a INNER JOIN  `mt_user_tenant` b on a.user_uuid=b.user_uuid  inner join bd_tenant c on  b.tenant_uuid=c.tenant_uuid where tenant_name = ? and user_name = ?"; //  租户
            $data = $this->dbSelect($sql, array($tenantname, $tenantusername));
            $flag = true;
        } else {
            $sql = "select * from bd_user where binary user_name= ? "; //一般用户
            $data = $this->dbSelect($sql, array($username));
        }
        $email = $utils->decryptJsRsa($params['email']);
        if (empty($data)) {
            $info = array('result' => 2);  //用户名不存在
            return json_encode($info);
        } else {   //用户名存在
            if ($flag) {
                $sql = "SELECT email FROM `bd_user` a INNER JOIN  `mt_user_tenant` b on a.user_uuid=b.user_uuid  inner join bd_tenant c on  b.tenant_uuid=c.tenant_uuid where tenant_name = ?";
                $data = $this->dbSelect($sql, array($tenantname));
            } else {
                $sql = "select email from bd_user where binary user_name = ?";
                $data = $this->dbSelect($sql, array($username));
            }
            if ($data[0]['email'] == null) {
                $info = array('result' => 0  //用户存在但是没有配置邮箱
                );
                return json_encode($info);
            } else {
                if ($flag) {
                    $sql = "SELECT a.user_uuid FROM `bd_user` a INNER JOIN  `mt_user_tenant` b on a.user_uuid=b.user_uuid  inner join bd_tenant c on  b.tenant_uuid=c.tenant_uuid where tenant_name= ? and email=?";
                    $userData = $this->dbSelect($sql, array($tenantname, $email));
                } else {
                    $sql = "select user_uuid from bd_user where user_name = ? and email = ?";
                    $userData = $this->dbSelect($sql, array($username, $email));
                }
                $userEmail = $data[0]['email'];
                $passSql = "select * from bd_account_safe";
                $passData = $this->dbSelect($passSql);
                if (strcasecmp($email,$userEmail) == 0) {  //发送邮件
                    $title = Xphp::$_lang['UI_ORGAN_USER_RESET_PASSWORD'];
                    $token = array('useruuid' => $userData[0]['user_uuid'], 'username' => $username, 'time' => time(),);
                    $tokenData = json_encode($token, true);
                    $tokenData = $utils->encrype($tokenData);  //加密
                    $tokenData = urlencode($tokenData);

                    //邮件模板内容替换
                    if(!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], Xphp::$_config['ENTERPRISE'])){
                        $message = file_get_contents(ROOT_PATH.'/email/email-reset-pwd-oem.html');
                    }else if(Xphp::$_config['lang'] == "en-us"){
                        $message = file_get_contents(ROOT_PATH.'/email/email-reset-pwd-en.html');
                    }else{
                        $message = file_get_contents(ROOT_PATH.'/email/email-reset-pwd.html');
                    }
                    $message = str_replace('reportTime', date('Y-m-d H:i:s'), $message);
                    $loginVersion = Xphp::$_config['LOGIN_INFO']['login_url'] == '/login_version/login_project/' ? 'login_project' : 'login_professional';
                    $content = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_ADDR'] . '/login_version/'.$loginVersion.'/reset_pwd.php?key=' . $tokenData;
                    $message = str_replace('resetPwdUrl', $content, $message);
                    $message = str_replace('backupServerHost', Xphp::instance('SystemHandler')->getMasterNodeIpLink(), $message);
                    $message = str_replace('supportEmailHref', 'mailto: '.Xphp::$_config['SYSTEM_INFO']['company_email'], $message);
                    $message = str_replace('supportEmail', Xphp::$_config['SYSTEM_INFO']['company_email'], $message);

                    $attachment = '';  //附件
                    $sendRes = $this->resetPwdSendEmail($title, $email, $message, $attachment, $username);
                    if ($flag) {
                        $sendRes = $this->resetPwdSendEmail($title, $email, $message, $attachment, $username[1]);
                    }
                    $resultValue = 1; //邮件服务器正常-邮件服务器已配置
                    if (!$sendRes) {
                        $resultValue = 5;  //邮件服务器异常
                    }
                    $info = array('result' => $resultValue,  //发送邮箱
//                       'user_uuid' => $userData[0]['user_uuid'],//获取user_uuid
                    );
                    return json_encode($info);
                } else {
                    $info = array('result' => 3  // 已配置邮箱但输入邮件错误
                    );
                    return json_encode($info);
                }
            }
        }
      }
    }

    //重置密码发送邮件
    private function resetPwdSendEmail($title,$email,$content,$attachment,$username){
        //直接调用发送邮件接口
        $emailUtils = Xphp::instance('Email');
        $emailConfig = Xphp::$_config['EMAIL'];
        $reEmail = array($email);//收件人邮箱地址
        //获取邮件配置
        $sql = "select smtp_config from bd_email_notice";
        $emailConf = Xphp::$_config['EMAIL'];
        $data = $this->dbSelect($sql, array());
        $utils = Xphp::instance('Utils');
        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        $pass = $utils->decrypt($smtpConfig['pass']);
        $encryption = intval($smtpConfig['encryption']);
        $encryption = Xphp::$_config['EMAIL_ENCRYPTION_TYPE'][$encryption];
        $emailUtils->config($smtpConfig['host'], $smtpConfig['port'],true,
            $smtpConfig['email'], $pass, $encryption);
        $result = $emailUtils->sendmail($reEmail, $title, $content, "");
        $operate = Xphp::$_lang['WEB_USERS_SEND_EMAIL_TO'] . $email;
        return $result;
    }
    //重置密码
    public function resetPassWord($params) {
        $utils = Xphp::instance('utils');
        $username = $utils->decryptJsRsa($params['username']);//用户名
        $password = $utils->decryptJsRsa($params['password']);//密码度
        $passSql = "select pass_complexity from bd_account_safe";
        $passData = $this->dbSelect($passSql);
        $passcomplexity=$passData[0]['pass_complexity'];   //密码强度
        $resetpassWord = $utils->decryptJsRsa($params['resetpassWord']);
        $passwordmd5 = md5($password);//md5加密新密码
        $utils = Xphp::instance('utils');
        $token = $utils->decrypt($_COOKIE['resetPwdToken']);
        $token = json_decode($token, true);
        $useruuid = $token['useruuid'];
        $resetpassWord=$utils->decryptJsRsa($params['resetpassWord']);
        switch($passcomplexity){
            case 1:
                $flag=preg_match("/^[A-Za-z0-9]/",$password);
                break;
            case 2:
                $flag=preg_match("/^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/",$password);
                break;
            case 3:
                $flag=preg_match("/^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/",$password);
                break;
        }
         if($flag){
             $sql = "update bd_user set password = ? where user_uuid = ? ";
             $data=$this->dbExec($sql, array($passwordmd5, $useruuid));//更新密码,密码是md5加密后的
             if($data){
                 $info= array(
                     'result' => 1  //密码重置成功
                 );
              return json_encode($info);
             }
        }else{
             $info = array(
                 'result' => 2 //失败
             );
             return json_encode($info);
         }
    }
   //获取到系统的密码复杂度
    public function getPassComplexity(){
        $passSql = "select *from bd_account_safe";
        $passData = $this->dbSelect($passSql);
        $info = array(
            'passlength' => $passData[0]['pass_length'],
            'passcomplexity' => $passData[0]['pass_complexity'],
        );
        return json_encode($info);
    }
}
?>