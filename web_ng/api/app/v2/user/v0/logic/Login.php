<?php

namespace app\v2\user\v0\logic;

use app\v2\common\logic\Base;
use app\v2\system\v0\logic\Index;
use app\v2\homepage\v0\logic\homePage;
use app\v2\common\logic\Report as ReportHandler;

/**
 * note          用户登录相关的逻辑 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:24
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Login extends Base
{
    /**
     * 登录验证过程
     * @param string $username 用户名
     * @param string $password 密码
     * @return array|int[]
     * 1:登录成功
     */
    public function loginVerifys(string $username, string $password)
    {
        $token = array(
            'username' => $username,
            'password' => $password,
            'remember' => true
        );
        $token = v2_encrype(json_encode($token));

        //传入的密码已经md5加密过 不需要再加密密码了
//        $password = md5($password);

        $tenantUserName = $username;

        $usernameInfo = explode('\\', $username);
        $tenantuuid = '';
        $domainFlag = false;
        if (count($usernameInfo) > 1) {
            // 域用户、租户
            $domainFlag = true;
            $tenantName = $usernameInfo[0];
            $sql = "select tenant_uuid from bd_tenant where tenant_name = ? ";
            $dataTenant = dbSelect($sql, array($tenantName));

            if (!empty($dataTenant)) {
                $tenantuuid = $dataTenant[0]['tenant_uuid'];
                $username = $usernameInfo[1];
            }
        }

        $sql = "select unix_timestamp(bu.create_time) create_time, bu.create_user_uuid, bu.domain_uuid, bu.user_uuid,
                        bu.password, bu.user_type, bu.email, bu.lock_flag, bu.permission, bu.login_error_count,
                        bu.max_login_error_count, bu.language, bu.user_level
                from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid
                where binary bu.user_name = ? ";
        $sqlParams = array($username);
        if (!empty($tenantuuid)) {
            //租户内
            $sql .= ' and mut.tenant_uuid = ? ';
            $sqlParams = array($username, $tenantuuid);
        } elseif ($domainFlag) {
            //AD域用户
            $username = v2_escape_wildcard($username);
            $sql = 'select unix_timestamp(bu.create_time) create_time, bu.create_user_uuid, bu.domain_uuid,
                            bu.user_uuid, bu.password, bu.user_type, bu.email, bu.lock_flag,
                            bu.permission, bu.login_error_count,
                            bu.max_login_error_count, bu.language 
                    from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid 
                    where binary bu.user_name = "' . $username . '"';
            $sqlParams = array();
        } elseif ($username == "admin") {
            //默认admin用户登录
            $sql .= ' and user_type = 3 ';
        } else {
            //租户外其他用户
            $sql .= ' and mut.tenant_uuid is null ';
        }
        $users = dbSelect($sql, $sqlParams);
        if (empty($users)) {
            // 用户名不存在
            $info = array(
                'success' => false,
                'code' => 0,
                'message' => xphp_get_lang('WEB_USERS_GET_TOKEN_ERROR_USER_NOT_EXIST'),
                'data' => array(
                    'result' => 6,
                    'token' => '',
                ),
            );
            return $info;
        }

        if ($password != $users[0]['password']) {
            // 用户名或密码错误,密码错误
            // 用户名不存在
            $info = array(
                'success' => false,
                'code' => 0,
                'message' => xphp_get_lang('WEB_USERS_GET_TOKEN_ERROR_USER_PASSWORD_ERROR'),
                'data' => array(
                    'result' => 2,
                    'token' => '',
                ),
            );
            return $info;
        }

        $userUUID = $users[0]['user_uuid'];
        $userType = $users[0]['user_type'];
        $email = $users[0]['email'];
        $permission = $users[0]['permission'];
        $language = $users[0]['language'];
        $userLevel = $users[0]['user_level'];

        //登录成功
        $this->successLogin($token, $username, $userUUID, $userType, $email, $permission, $language, $tenantUserName, $tenantuuid);

        $info = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_USERS_GET_TOKEN_SUCCESS'),
            'data' => array(
                'result' => 1,
                'token' => $token,
            ),
        );
        return $info;
    }

    /**
     * 登录验证过程
     * （注意这里auth方法会调用，auth是用作单点登录的，auth会传入用户名和密码，修改的时候不要修改传入参数，可以修改内容）
     * @param string  $username  用户名
     * @param string  $password  密码
     * @param boolean $remember  是否记住密码
     * @param string  $lcokpage  锁定页面调用
     * @param string  $lockcount 锁定页面输入密码的次数
     * @param boolean $oemFlag   识别oem
     * @param boolean $loginFlag 是否修改admin的默认密码
     * @return int[]|string
     * 1:登录成功
     * 2:用户名密码错误
     * 3:用户被锁定
     * 4:域服务器连接错误
     * 5:密码已经过期
     */
    public function loginVerify(
        string $username,
        string $password,
        $remember = false,
        $lcokpage = '',
        $lockcount = 0,
        $oemFlag = false,
        $loginFlag = false,
        $is_checkRememberFlag
    ) {
        $token = array(
            'username' => $username,
            'password' => $password,
            'remember' => $remember
        );
        $token = v2_encrype(json_encode($token));

        $verPassword = $password;

        $password = md5($password);

        $tenantUserName = $username;

        if ($this->accessRestrictLogin()) {
            return $this->accessRestrictLogin();
        }

        $usernameInfo = explode('\\', $username);
        $tenantuuid = '';
        $domainFlag = false;
        if (count($usernameInfo) > 1) {
            // 域用户、租户
            $domainFlag = true;
            $tenantName = $usernameInfo[0];
            $sql = "select tenant_uuid from bd_tenant where tenant_name = ? ";
            $dataTenant = dbSelect($sql, array($tenantName));

            if (!empty($dataTenant)) {
                $tenantuuid = $dataTenant[0]['tenant_uuid'];
                $username = $usernameInfo[1];
            }
        }

        $sql = "select unix_timestamp(bu.create_time) create_time, bu.create_user_uuid, bu.domain_uuid,bu.user_name,
                bu.user_uuid, bu.password, bu.user_type, bu.email, bu.lock_flag, bu.permission, bu.login_error_count,
                bu.last_login_time,bu.user_level,
                bu.max_login_error_count, bu.language 
                from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid
                where binary bu.user_name = ? ";
        $sqlParams = array($username);
        if (!empty($tenantuuid)) {
            //租户内
            $sql .= ' and mut.tenant_uuid = ? ';
            $sqlParams = array($username, $tenantuuid);
        } elseif ($domainFlag) {
            //AD域用户
            $username = v2_escape_wildcard($username);
            $sql = 'select unix_timestamp(bu.create_time) create_time, bu.create_user_uuid, bu.domain_uuid, 
                        bu.user_uuid, bu.password, bu.user_type, bu.email, bu.lock_flag,
                        bu.permission, bu.login_error_count,
                        bu.last_login_time,
                        bu.max_login_error_count, bu.language
                    from bd_user bu 
                    where binary bu.user_name = "' . $username . '"';
            $sqlParams = array();
        } else {
            //租户外其他用户
            $sql .= ' and mut.tenant_uuid is null ';
        }
        $users = dbSelect($sql, $sqlParams);
        if (empty($users)) {
            // 用户名不存在
            return [
                'result' => 2,
                'message' => xphp_get_lang('WEB_ERROR_PF_USER_USER_PASS_ERROR'),
            ];
        }
        // 这里进行一个账号授权判读 三权模式下，未指定角色或角色不对的，都会给截断
        if (!xphp_check_three_user($users[0]['user_uuid'])) {
            // 用户名不存在
            return [
                'result' => 2,
                'message' => xphp_get_lang('WEB_ERROR_PF_USER_USER_PASS_ERROR'),
            ];
        }

        $loginUserLevel = 0;
        if (xphp_three_powers()) {
            // 如果是三权模式
            // 这里需要校验下是否达到锁定的限制
            $loginUserLevel = $users[0]['user_level'] > 3 ? 2 : ($users[0]['user_level'] == 2 ? 3 : 0);
        }
        $userLevel = $users[0]['user_level'];
        // 获取安全配置 可能会根据角色获取不同的配置
        $safeInfo = (new Index())->getAccountSafe($loginUserLevel);

        // 这里开始判断是否是满足连续错误x次以上，x分钟内不允许登录的情况
        if (
            $users[0]['login_error_count'] >= $safeInfo['faild_count'] &&
            !($users[0]['login_error_count'] % $safeInfo['faild_count']) &&
            (time() - strtotime($users[0]['last_login_time']) < $safeInfo['faild_lock_time']) &&
            $users[0]['user_level'] != 0
        ) {
            // 连续错误x次并且x分钟之内 那么不允许再次登录
            $timeSec = strtotime($users[0]['last_login_time']) + $safeInfo['faild_lock_time'] - time();
            return [
                'result' => 10,
                'faliCount' => $_SESSION['login_faild_num'],//获取登录错误次数
                'lockCount' => $lockcount,
                'time' => $timeSec,
                'message' => xphp_get_lang('UI_LOGIN_RESTRICT_LOGIN')
            ];
        }

        $userUUID = $users[0]['user_uuid'];
        // 检查租户是否锁定
        $sqlTenant = "select tenant_uuid, lock_flag from bd_tenant where tenant_uuid = ? ";
        $dataTenant = dbSelect($sqlTenant, array($tenantuuid));
        $lockFlag = $users[0]['lock_flag'];
        // 用户所在租户已经禁用，直接按锁定来
        if (!empty($dataTenant) && intval($dataTenant[0]['lock_flag']) == xphp_get_config('app', 'FLAG')['UNSET']) {
            $lockFlag = xphp_get_config('app', 'FLAG')['UNSET'];
        }

        $userType = $users[0]['user_type'];
        $email = $users[0]['email'];
        $permission = $users[0]['permission'];
        $language = $users[0]['language'];
        $errorCount = $users[0]['login_error_count'];
        $maxCount = $users[0]['max_login_error_count'];
        $userTime = intval($users[0]['create_time']);

        $passTimeout = $safeInfo['password_time'];
        $nowTime = time() - $userTime;
        $overDays = $nowTime / 3600 / 24;

        if (2 == $lockFlag) {
            $check = false;
            if (xphp_three_powers()) {
                // 三权模式下
                if ($userLevel == xphp_get_config('three_powers', 'THREE_POWERS_USER')['sysadmin']) {
                    $check = true;
                }
            } elseif ($userType == 3) {
                $check = true;
            }
            if (!$check) {
                // 排除admin被锁定 加上三权模式下的user_level为2的系统管理员也排除
                // 用户被锁定
                $errorCode = v2_get_error_num('PF_USER_LOGIN_LOCK_ERROR');
                $this->loginLog(
                    $userUUID,
                    $username,
                    'SYSTEM_USER_LOGIN_FAILURE',
                    array(),
                    xphp_get_config('log', 'LOGLEVEL')['WARN'],
                    $errorCode
                );
                return [
                    'result' => 3,
                    'message' => xphp_get_lang('WEB_ERROR_PF_USER_LOGIN_LOCK_ERROR')
                ];
            }
        }

        if (!empty($safeInfo['faild_count'])) {
            $maxCount = $safeInfo['faild_count'];
        }

        // 如果是外部用户-域用户
        if (intval($users[0]['user_type']) == 2) {
            // 测试活动目录服务器连通性
            $sqlCon = "select bt.lock_flag, bds.domain_name, bds.domain_type, bds.domain_ip, bds.port, bds.tenant_uuid, bds.detail 
                       from bd_domain_server bds left join bd_tenant bt on bds.tenant_uuid = bt.tenant_uuid where bds.domain_uuid = ?";
            $data = dbSelect($sqlCon, array($users[0]['domain_uuid']));
            $userDes = $data[0]['domain_name'] . '\\\\';
            $username = substr($username, strlen($userDes));
            $user = $username . '@' . $data[0]['domain_name'];
            $loginPassword = $verPassword;

            //协议类型 1:ldaps 2:ldap
            $protocolNum = json_decode($data[0]['detail'], true)['protocol'];
            $protocolValue = array('', "ldaps://", "ldap://");
            $protocol = $protocolValue[$protocolNum];
            $hostname = gethostbyaddr($data[0]['domain_ip']);
            $host = $protocol . $hostname;
            //设置跳过验证证书步骤
            ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
            $conn = ldap_connect($host, $data[0]['port']);//不要写成ldap_connect($host.':'.$port)的形式'
            if ($conn) {
                // 设置参数
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
                $bd = ldap_bind($conn, $user, $loginPassword);
                // 获取用户所在租户是否被禁用
                $tenantLockFlag = $data[0]['lock_flag'];
                if ($tenantLockFlag == xphp_get_config('app')['FLAG']['UNSET']) {
                    $errorCode = v2_get_error_num('PF_USER_LOGIN_LOCK_ERROR');
                    $this->loginLog(
                        $userUUID,
                        $username,
                        'SYSTEM_USER_LOGIN_FAILURE',
                        array(),
                        xphp_get_config('log', 'LOGLEVEL')['WARN'],
                        $errorCode
                    );

                    return [
                        'result' => 3,
                        'message' => xphp_get_lang('WEB_ERROR_PF_USER_LOGIN_LOCK_ERROR')
                    ];
                }
                if ($bd) {
                    //更新用户密码
                    $sql = "update bd_user set password = ? where user_uuid = ?";
                    dbExec($sql, array($password, $userUUID));
                } else {
                    if ($lcokpage == 'lock') {
                        if ($lockcount == '3') {   //当锁定页面输错密码三次时
                            unsetCaches(['tenantusername']);//清除session
                        }
                    } else {
                        $this->failureLogin($userUUID, $errorCount, $maxCount, $userType, $userLevel, $safeInfo);
                    }

                    $errorCode = v2_get_error_num('PF_USER_USER_PASS_ERROR');
                    $this->loginLog(
                        $userUUID,
                        $username,
                        'SYSTEM_USER_LOGIN_FAILURE',
                        array(),
                        xphp_get_config('log', 'LOGLEVEL')['ERROR'],
                        $errorCode
                    );
                    $users = dbSelect($sql, $sqlParams); //再次查询数据库
                    $errorCount = $users[0]['login_error_count'];
                    $lockFlag = $users[0]['lock_flag'];
                    //锁定
                    if (2 == $lockFlag) { //排除admin被锁定
                        //用户被锁定

                        $errorCode = v2_get_error_num('PF_USER_LOGIN_LOCK_ERROR');
                        $this->loginLog(
                            $userUUID,
                            $username,
                            'SYSTEM_USER_LOGIN_FAILURE',
                            array(),
                            xphp_get_config('log', 'LOGLEVEL')['WARN'],
                            $errorCode
                        );

                        return array(
                            'result' => 3,
                            'message' => xphp_get_lang('WEB_ERROR_PF_USER_LOGIN_LOCK_ERROR')
                        );
                    } else {
                        return array(
                            'result' => 2, // 用户名或密码错误
                            'maxCount' => $maxCount,//最多能登录的次数
                            'usertype' => $userType,
                            'message' => xphp_get_lang('WEB_ERROR_PF_USER_USER_PASS_ERROR'),
                        );
                    }
                }
            } else {
                return array(
                    'result' => 4, // 域服务器连接错误
                    'message' => xphp_get_lang('UI_LOGIN_DOMAIN_CONNECT_ERROR')
                );
            }
        } else {
            if ($password != $users[0]['password']) {
                // 用户名或密码错误,密码错误
                $errorCode = v2_get_error_num('PF_USER_USER_PASS_ERROR');
                $this->loginLog(
                    $userUUID,
                    $username,
                    'SYSTEM_USER_LOGIN_FAILURE',
                    array(),
                    xphp_get_config('log', 'LOGLEVEL')['ERROR'],
                    $errorCode
                );

                $this->failureLogin($userUUID, $errorCount, $maxCount, $userType, $userLevel, $safeInfo);

                if ($lcokpage == 'lock') {
                    if ($lockcount == '3') {   //当锁定页面输错密码三次时
                        unsetCaches(['tenantusername']);//清除session
                    }
                }
                $users = dbSelect($sql, $sqlParams); // 因为failureLogin更新了数据库，再次查询数据库
                $errorCount = $users[0]['login_error_count'];
                $lockFlag = $users[0]['lock_flag'];

                // 锁定
                if (2 == $lockFlag) {
                    $check = false;
                    if (xphp_three_powers()) {
                        // 三权模式下
                        if ($userLevel == xphp_get_config('three_powers', 'THREE_POWERS_USER')['sysadmin']) {
                            $check = true;
                        }
                    } elseif ($userType == 3) {
                        $check = true;
                    }
                    if (!$check) {
                        // 排除admin被锁定 加上三权模式下的user_level为2的系统管理员也排除
                        // 用户被锁定
                        $errorCode = v2_get_error_num('PF_USER_LOGIN_LOCK_ERROR');
                        $this->loginLog(
                            $userUUID,
                            $username,
                            'SYSTEM_USER_LOGIN_FAILURE',
                            array(),
                            xphp_get_config('log', 'LOGLEVEL')['WARN'],
                            $errorCode
                        );
                        return [
                            'result' => 3,
                            'message' => xphp_get_lang('WEB_ERROR_PF_USER_LOGIN_LOCK_ERROR')
                        ];
                    }
                } else {
                    // 更新下最近的登录时间为当前，为了保证锁定的时间为准的
                    $this->dbExec(
                        "update bd_user set last_login_time = ? where user_name = ?",
                        [date('Y-m-d H:i:s'), $username]
                    );

                    // 这里开始判断是否是满足连续错误5次以上，10分钟内不允许登录的情况
                    if (
                        $users[0]['login_error_count'] >= $safeInfo['faild_count'] &&
                        !($users[0]['login_error_count'] % $safeInfo['faild_count']) &&
                        (time() - strtotime($users[0]['last_login_time']) < $safeInfo['faild_lock_time']) &&
                        $users[0]['user_level'] != 0
                    ) {
                        // 连续错误5次并且10分钟之内 那么不允许再次登录
                        $timeSec = strtotime($users[0]['last_login_time']) + $safeInfo['faild_lock_time'] - time();
                        return [
                            'result' => 10,
                            'faliCount' => $_SESSION['login_faild_num'],//获取登录错误次数
                            'lockCount' => $lockcount,
                            'time' => $timeSec,
                            'message' => xphp_get_lang('UI_LOGIN_RESTRICT_LOGIN')
                        ];
                    }
                    // 发送邮件
                    $this->sendEmail($users[0]['user_uuid'], $users[0]['user_name'], $users[0]['email']);
                    return array(
                        'result' => 2,
                        'maxCount' => $maxCount,//最多能登录的次数
                        'usertype' => $userType,
                        'message' => xphp_get_lang('WEB_ERROR_PF_USER_USER_PASS_ERROR')
                    );
                }
            }
        }

        //密码过期
        if ($overDays > $passTimeout && $userType != 2) {
            return array(
                'result' => 5, //密码已过期，请联系管理员
                'useruuid' => $userUUID,
                'message' => xphp_get_lang('UI_LOGIN_PASSWORD_OVERDUE')
            );
        }

        //判断是否是首次登录
        $sql = 'select force_password_change_flag from bd_user where user_uuid = ?';
        $data = $this->dbSelect($sql, array($userUUID));
        if (
            $data[0]['force_password_change_flag'] == xphp_get_config('app')['FLAG']['SET'] && !$oemFlag && !$loginFlag
        ) {
            //首次登录进入强制修改密码页面
            $token = array('username' => $username);
            $tokenData = json_encode($token, true);
            $tokenData = v2_encrype($tokenData);  //加密
            $tokenData = urlencode($tokenData);

            return array(
                'result' => 11,
                'useruuid' => $userUUID,
                'username' => $username,
                'usertype' => $userType,
                'passlength' => $safeInfo['passlength'],
                'passcomplexity' => $safeInfo['passcomplexity'],
                'tokenData' => $tokenData,
                'message' => xphp_get_lang('UI_USER_FORCE_CHANGE_PASSWORD')
            );
        }

        //登录成功
        $this->successLogin($token, $username, $userUUID, $userType, $email, $permission, $language, $tenantUserName, $tenantuuid);

        //记住密码
        if ($is_checkRememberFlag) {
            if ($remember) {
                xphp_set_cache('Token_remember', $token, false, time() + 7 * 24 * 3600);
            } else {
                xphp_set_cache('Token_remember', '', false, time() - 3600);
            }
        }

        // 登录页验证码清除
        setCache('login_faild_num', 0);

        // 强制更新语言包缓存
        // xphp_get_lang('WEB_PUBLIC_SUCCESS', '', true);

        return array(
            'result' => 1,
            'token' => $token,
            'message' => xphp_get_lang('UI_LOGIN_SUCCESS')
        );
    }

    /**
     * 用户登录失败后续处理
     * @param $userUUID   uuid
     * @param $errorCount count
     * @param $maxCount   max
     * @param $userType   type
     * @param $userLevel  level
     * @param $safeInfo   safe
     * @return int
     */
    private function failureLogin($userUUID, $errorCount, $maxCount, $userType, $userLevel = 0, $safeInfo = [])
    {
        //错误登录次数+1,并更新数据库
        $newErrorCount = $errorCount + 1;
        $sqls = " login_error_count = ?";
        $sqlParam = [$newErrorCount];
        // 这里判断当前的错误次数是否是5的倍数，是的情况，那么更新下last_login_time 的时间，为了锁定下次的时间
        if (!($newErrorCount % $safeInfo['faild_count'])) {
            $sqls .= ',last_login_time = ?';
            array_push($sqlParam, date('Y-m-d H:i:s'));
        }
        if (($errorCount + 1) >= $maxCount) {
            //登录错误次数超过最大登录上限,锁定用户
            $userLevel == 0 && $sqls .= ",lock_flag = '2'";
        }
        $sql = "update bd_user set {$sqls} where user_uuid = ?";
        array_push($sqlParam, $userUUID);
        return parent::dbQuery($sql, $sqlParam);
    }

    /**
     * 用户登录成功后续处理
     * @param string $token          token
     * @param string $username       username
     * @param string $userUUID       uuid
     * @param $userType       type
     * @param $email          email
     * @param string $permission     permission
     * @param string $languages      语言
     * @param string $tenantUserName 租户
     * @return void
     */
    public function successLogin(
        string $token,
        string $username,
        string $userUUID,
        $userType,
        $email,
        $permission = '',
        $languages = '',
        $tenantUserName = '',
        $tenantuuid
    ) {
        // 清除cache
        cache();
        // 删除缓存目录
        xphp_delete_dir_file(CACHE_PATH);

        //登录成功后将上一次登录历史存进 session
        $this->initLoginSession($userUUID);

        //查找某个用户的force_password_change_flag字段值
        $sql = "select force_password_change_flag, user_level, auth_code from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, array($userUUID));
        if ($data[0]['force_password_change_flag'] == xphp_get_config('app')['FLAG']['SET']) {
            $sql = "update bd_user set force_password_change_flag = ? where user_uuid = ?";
            dbExec($sql, array(xphp_get_config('app')['FLAG']['UNSET'], $userUUID));
        }
        //获取客户端IP
        $clientIP = v2_get_client_iP();

        $this->loginLog($userUUID, $username, 'SYSTEM_USER_LOGIN_SUCCESS', array('S:' . $clientIP));
        $systemHandler = new Index();
        $userHander = new User();

        $softwareType = $systemHandler->getSoftwareType();
        $extension = $systemHandler->getExtensionLicense();

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
        if (!empty($extension)) {
            $pagelist = $extension['p'];
            $authFun = $extension['f'];
        }

        $pageFlag = true;
        // 这里判断下是否是调试模式，调试模式都已备份系统的授权为准
        if (getEnvs()) {
            $pageFlag = false;
        }

        $permission = $userHander->pGetUserAllPermission($userUUID, $pageFlag);
        if ($data[0]['user_level'] == 1) {
            //超级管理员不需要全局管理者的权限
            $permission = array_diff($permission, ['global_observer', 'global_read', 'global_write']);
        }

        $sucessarray = [
            'userName' => $username,
            'userUuid' => $userUUID,
            'userType' => $userType,
            'email' => $email,
            'userLevel' => $data[0]['user_level'], // 获取用户的级别 1admin 2safe 3sys 4auditor 5operator
            'isThreePowers' => xphp_three_powers(), // 获取是否是三权模式
            'permission' => $permission,  // 这个应该是左侧的菜单的权限和右边的一个大的功能块 但是不包含细致化的操作
            // 主要是为了获取方法cat的name数组 为了在获取用户权限树的时候判断是否有分配详细操作的权限
            'permissionArr' => $userHander->pGetUserAllPermission($userUUID),
            // 用户拥有的具体的方法和类的校验 get=>['路由1','路由2']
            'permissionFunction' => $userHander->pGetPageFunction($permission, $userUUID),
            // page里面所有的方法操作数组  这个之前是因为怕有些接口漏掉，所以权限验证如果路由不在这个里面配置的都可以放过
            'permissionFunctions' => $userHander->pGetPageFunctions(true),
            'language' => $languages,
            'oemFlag' => $systemHandler->getSoftwareIsOem(),
            'softwareType' => $softwareType, // 授权成功后需要再更新
            'authfun' => $authFun,
            'tenantuuid' => $tenantuuid,
            'tenantusername' => $tenantUserName,
            'authUser' => xphp_get_manager_uuid($userUUID), // 获取当前登录用户的所有的权限的管理的关联用户
            'softwareNameDiy' => $extension['softwareNameDiy'] // 获取自定义产品名称
        ];

        // 默认没有大屏权限
        $permissionVisualScreen = false;
        if (empty($sucessarray['tenantuuid'])) {
            $permissionVisualScreen = true;
            //检查当前用户是否属于Master组
            $masterFlag = (new User())->pCheckUserIsMaster($userUUID);
            if (
                (!$authFun['visualization'] || !$masterFlag) &&
                !in_array('p_visual_screen', $sucessarray['permissionArr'])
            ) {
                $permissionVisualScreen = false;
            }
        }

        if ($permissionVisualScreen) {
            $sucessarray['permissionVisualScreen'] = 100;
        } else {
            $sucessarray['permissionVisualScreen'] = false;
        }

        session_start();
        // 更新 sesson_id
        session_regenerate_id(true);

        // 这里记录下当前会话信息
        $sucessarray['BackupSystem'] = session_id();

        // 登录页验证码清除
        $sucessarray['login_faild_num'] = 0;

        // 缓存用户信息
        xphp_set_user_info($token, $sucessarray);

        // 兼容老版本的
        foreach ($sucessarray as $key => $item) {
            if ($key == 'permissionFunction') {
                // 读取老版本的page权限
                $item = $userHander->pGetPageFunction($permission, $userUUID, true);
                $sucessarray[$key] = $item;
            } elseif ($key == 'permissionFunctions') {
                // 读取老版本的page权限
                $item = $userHander->pGetPageFunctions(true);
                $sucessarray[$key] = $item;
            }
        }
        $sucessarray['userUUID'] = $sucessarray['userUuid'];
        setCaches($sucessarray);

        //登录成功后把主题布局等存进session
        $homepageHandler = new homePage();
        $homepageHandler->getThemeInfo();

        //刷新登录错误次数为0
        $sql = "update bd_user set login_error_count = '0', last_login_time = ? 
                where user_uuid = ?";
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        dbQuery($sql, array(date($dateformat), $userUUID));

        // 登录成功后获取auth_code
        if (empty($data[0]['auth_code'])) {
            $code = xphp_random_code(4);
            $sql = "update bd_user set auth_code = ? where user_uuid = ?";
            $this->dbExec($sql, array($code, $userUUID));
        }
    }

    /**
     * 初始化登录历史session
     * @param string $userUUID 用户uuid
     * @return void
     */
    private function initLoginSession(string $userUUID)
    {
        $sql = "select bsl.description_param, bsl.op_time, bu.login_error_count,
                        unix_timestamp(bu.create_time) create_time
                    from bd_system_log bsl, bd_user bu
                    where bsl.user_uuid = bu.user_uuid and bsl.description_key = ?
                      and bu.user_uuid = ? order by op_time desc";
        $data = $this->dbSelect($sql, array('SYSTEM_USER_LOGIN_SUCCESS', $userUUID));

        if (!empty($data)) {
            $loginstr = explode(':', $data[0]['description_param']);
            $loginstr1 = explode('"]', $loginstr[1]);
            $loginIP = $loginstr1[0];
            $loginTime = $data[0]['op_time'];
            $loginCount = intval($data[0]['login_error_count']);
            $effectTime = intval($data[0]['create_time']);
        }

        xphp_set_cache('history_login', [
            'historyip' => $loginIP ?? '',
            'historytime' => $loginTime ?? date('Y-m-d H:i:s'),
            'logincount' => $loginCount ?? 0,
            'effecttime' => $effectTime ?? 0,
        ]);
    }

    /**
     * 登录系统IP是否在黑名单内
     * 白名单内可以正常登录
     * 白名单优先级高于黑名单
     */
    private function accessRestrictLogin()
    {
        $ipAddress = v2_get_client_iP();
        // 判断是IPV4还是IPV6 true:IPV4 false:IPV6
        $ipVersionFlag = $this->checkIPVersion($ipAddress);
        $sql = "select start_ip, end_ip, list_type, start_time, end_time, lock_flag, permanent_access_flag from bd_access_restrict";
        $listData = $this->dbSelect($sql, array());
        $listFlag = false;
        $whiteIPListData = $this->filterList($listData, xphp_get_config('app')['FLAG']['SET']);
        $blackIPListData = $this->filterList($listData, xphp_get_config('app')['FLAG']['UNSET']);



        foreach ($whiteIPListData as $item) {
            // 白名单具体IP
            if (trim($item['start_ip']) == trim($item['end_ip'])) {
                if (v2_parse_flag_to_bool($item['permanent_access_flag']) && trim($ipAddress) == trim($item['start_ip'])) {
                    // 永久时效
                    $listFlag = true;
                    break;
                } else {
                    // 非永久时效
                    if (trim($ipAddress) == trim($item['start_ip']) && time() >= strtotime($item['start_time']) && time() <= strtotime($item['end_time'])) {
                        // 永久时效
                        $listFlag = true;
                        break;
                    }

                }
            } else {
                // 白名单中的ipv4网段
                if ($ipVersionFlag) {
                    if (ip2long($ipAddress) >= ip2long($item['start_ip']) && ip2long($ipAddress) <= ip2long($item['end_ip'])) {
                        if (v2_parse_flag_to_bool($item['permanent_access_flag'])) {
                            // 永久时效
                            $listFlag = true;
                            break;
                        } else {
                            // 非永久时效
                            if (time() >= strtotime($item['start_time']) && time() <= strtotime($item['end_time'])) {
                                $listFlag = true;
                                break;
                            }
                        }

                    }
                } else {
                    // 白名单中的ipv6网段
                    // 将IPv6地址和范围转换为二进制格式
                    $ipBinary = inet_pton($ipAddress);
                    $startBinary = inet_pton($item['start_ip']);
                    $endBinary = inet_pton($item['end_ip']);
                    // 检查IPv6地址是否在范围内
                    if ($ipBinary >= $startBinary && $ipBinary <= $endBinary) {
                        if (v2_parse_flag_to_bool($item['permanent_access_flag'])) {
                            // 永久时效
                            $listFlag = true;
                            break;
                        } else {
                            // 非永久时效
                            if (time() >= strtotime($item['start_time']) && time() <= strtotime($item['end_time'])) {
                                $listFlag = true;
                                break;
                            }
                        }
                    }
                }


            }
        }

        if (!$listFlag) {
            foreach ($blackIPListData as $item) {
                if (trim($item['start_ip']) == trim($item['end_ip'])) {
                    // 黑名单具体IP
                    if (v2_parse_flag_to_bool($item['permanent_access_flag']) && trim($ipAddress) == trim($item['start_ip'])) {
                        // 永久时效
                        return [
                            'result' => 12,
                            'message' => xphp_get_lang('UI_LOGIN_USER_LOGIN_CONTACT_MANAGER')
                        ];
                    } else {
                        // 非永久时效
                        if (trim($ipAddress) == trim($item['start_ip']) && time() >= strtotime($item['start_time']) && time() <= strtotime($item['end_time'])) {
                            return [
                                'result' => 12,
                                'message' => xphp_get_lang('UI_LOGIN_USER_LOGIN_CONTACT_MANAGER')
                            ];
                        }

                    }
                } else {
                    if ($ipVersionFlag) {
                        // 黑名单中的ipv4网段
                        if (ip2long($ipAddress) >= ip2long($item['start_ip']) && ip2long($ipAddress) <= ip2long($item['end_ip'])) {
                            if (v2_parse_flag_to_bool($item['permanent_access_flag'])) {
                                // 永久时效
                                return [
                                    'result' => 12,
                                    'message' => xphp_get_lang('UI_LOGIN_USER_LOGIN_CONTACT_MANAGER')
                                ];
                            } else {
                                // 非永久时效
                                if (time() >= strtotime($item['start_time']) && time() <= strtotime($item['end_time'])) {
                                    return [
                                        'result' => 12,
                                        'message' => xphp_get_lang('UI_LOGIN_USER_LOGIN_CONTACT_MANAGER')
                                    ];
                                }
                            }

                        }
                    } else {
                        // 黑名单中的ipv6网段
                        // 将IPv6地址和范围转换为二进制格式
                        $ipBinary = inet_pton($ipAddress);
                        $startBinary = inet_pton($item['start_ip']);
                        $endBinary = inet_pton($item['end_ip']);
                        // 检查IPv6地址是否在范围内
                        if ($ipBinary >= $startBinary && $ipBinary <= $endBinary) {
                            if (v2_parse_flag_to_bool($item['permanent_access_flag'])) {
                                // 永久时效
                                return [
                                    'result' => 12,
                                    'message' => xphp_get_lang('UI_LOGIN_USER_LOGIN_CONTACT_MANAGER')
                                ];
                            } else {
                                // 非永久时效
                                if (time() >= strtotime($item['start_time']) && time() <= strtotime($item['end_time'])) {
                                    return [
                                        'result' => 12,
                                        'message' => xphp_get_lang('UI_LOGIN_USER_LOGIN_CONTACT_MANAGER')
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    // 返回所有符合条件的白/黑名单
    private function filterList($listData, $flag)
    {
        $IpListData = array_filter($listData, function ($item) use ($flag) {
            // 返回满足条件（启用）的所有白名单
            // 其它判断条件为：时效
            if ($item['list_type'] == $flag && $item['lock_flag'] == xphp_get_config('app')['FLAG']['SET']) {
                return $item;
            }
        });
        return $IpListData;
    }

    /**
     * 判断是IPV4还是IPV6
     * @param $ipAddress
     * @return string
     */
    private function checkIPVersion($ipAddress)
    {
        if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        } elseif (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return false;
        }
    }

    /**
     * 密码错误发送邮件
     */
    private function sendEmail($useruuid, $userName, $email)
    {
        $title = xphp_get_lang('WEB_USERS_LOGIN_PASSWORD_ERROR_NOTICE');
        $token = [
            'useruuid' => $useruuid,
            'username' => $userName,
            'time' => time(),
        ];
        $jsonToken = json_encode($token, true);
        $cryptToken = v2_encrype($jsonToken);  //加密
        $urlToken = urlencode($cryptToken);
        $email = $email;
        if (!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))) {
            $message = file_get_contents(DATA_PATH . 'email/email-login-passwod-error-oem.html');
        } else if (xphp_get_config('app', 'lang') == "en-us") {
            $message = file_get_contents(DATA_PATH . 'email/email-login-passwod-error-en.html');
        } else {
            $message = file_get_contents(DATA_PATH . 'email/email-login-passwod-error.html');
        }
        $loginVersion = xphp_get_config(
            'app',
            'LOGIN_INFO'
        )['login_url']
            == '/login_version/login_project/'
            ? 'login_project' : 'login_professional';
        $content = $_SERVER['REQUEST_SCHEME']
            . '://' . $_SERVER['SERVER_ADDR']
            . '/login_version/' . $loginVersion
            . '/reset_pwd.php?key=' . $urlToken;
        $message = str_replace('reportTime', date('Y-m-d H:i:s'), $message);
        $message = str_replace('backupServerHost', (new ReportHandler())->getMasterNodeIpLink(), $message);
        $message = str_replace('resetPwdUrl', $content, $message);
        $message = str_replace(
            'supportEmailHref',
            'mailto: ' . xphp_get_config('app', 'SYSTEM_INFO')['company_email'],
            $message
        );
        $message = str_replace(
            'supportEmail',
            xphp_get_config('app', 'SYSTEM_INFO')['company_email'],
            $message
        );
        $attachment = '';  // 附件
        (new \app\v2\user\v0\logic\Index())->resetPwdSendEmail($title, $email, $message, $attachment, $userName);
    }
}
