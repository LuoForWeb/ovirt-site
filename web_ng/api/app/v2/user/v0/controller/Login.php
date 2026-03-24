<?php

namespace app\v2\user\v0\controller;

use app\v2\common\controller\Base;

/**
 * note          用户登录管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:20
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Login extends Base
{
    /**
     * 登录入口
     * @return json
     */
    public function index()
    {

        // 参数验证
        $this->checkParams('login');

        $username = v2_decrypt_js_rsa($this->param['username']);
        $password = v2_decrypt_js_rsa($this->param['password']);
        $lockpage = !empty($this->param['page']) ? v2_decrypt_js_rsa($this->param['page']) : '';  //锁定页面调用
        $lockcount = !empty($this->param['count']) ? v2_decrypt_js_rsa($this->param['count']) : 0; //锁定页面输入密码的次数
        $oemFlag = $this->param['OemFalg'] ?? false;
        $is_checkRememberFlag = true;
        if (!isset($this->param['remember'])) {
            // remember key 不存在
            $is_checkRememberFlag = false;
        } else {
            $remember = $this->param['remember'] ?? false;
        }
        $loginFlag = $this->param['loginFlag'] ?? false;
        //增加一次登录错误记录 成功后会归0 所以不影响
        setCache('login_faild_num', empty(getCache('login_faild_num')) ? 1 : getCache('login_faild_num') + 1);

        // 验证验证码
        if (getCache('login_faild_num') > 3 && !empty($this->param['very_code'])) {
            // 需要验证验证码
            if (strtolower($this->param['very_code']) != getCache('very_code')) {
                return $this->error(
                    9,
                    [
                        'faliCount' => getCache('login_faild_num'),//获取登录错误次数
                        'lockCount' => $lockcount,
                        'message' => xphp_get_lang('UI_LOGIN_ERROR_VERIFY_CODE'),
                        'result' => 9
                    ]
                );
            }
        }

        // 删除缓存目录
        xphp_delete_dir_file(CACHE_PATH);

        $result = $this->logic()->loginVerify(
            $username,
            $password,
            $remember,
            $lockpage,
            $lockcount,
            $oemFlag,
            $loginFlag,
            $is_checkRememberFlag
        );

        if ($result['result'] == 1) {
            // 表示登录成功
            $data = [
                'access_token' => $result['token'],
                'expires_in' => TIMESTAMP + xphp_get_config('special', 'access_token_timeout'),
                'message' => xphp_get_lang('UI_LOGIN_SUCCESS'),
                'result' => $result['result']
            ];
            if (xphp_get_config('special', 'access_token_timeout') == 0) {
                // 过期时间戳加2小时
                $data['expires_in'] = TIMESTAMP + 7200;
            }
            return $this->success($result['result'], $data);
        }
        return $this->error(
            $result['result'],
            [
                'faliCount' => getCache('login_faild_num'),//获取登录错误次数
                'lockCount' => $lockcount,
                'maxCount' => $result['maxCount'] ?? 0,
                'time' => $result['time'] ?? 0,
                'useruuid' => $result['useruuid'] ?? '',
                'username' => $result['username'] ?? '',
                'usertype' => $result['usertype'] ?? '',
                'tokenData' => $result['tokenData'] ?? '',
                'result' => $result['result'],
                'message' => $result['message']
            ]
        );
    }

    /**
     * 第三方登录
     * @return json
     */
    public function thirdLogin()
    {
        // 参数验证
//        $this->checkParams('login');
        $data = urldecode($this->param['AUTHTOKEN']);
        $info = xphp_decrypt($data);
        $info = json_decode($info, true);
        $result = $this->logic()->loginVerifys($info['username'], $info['password']);
        if ($result['success']) {
            echo '<script> window.location.href="/" </script>';
            die();
        } else {
            return $this->error($result['message'], $result['data']);
        }
    }

    /**
     * 单点登录
     * @return json
     */
    public function auth()
    {
        $username = $this->param['username'];
        $password = $this->param['password'];

        $result = $this->logic()->loginVerify($username, $password, false, '', 100, true, false, false);

        if ($result['result'] == 1) {
            //验证成功，error_code返回0
            $tokenid = v2_my_encrype($username . '|' . $password . '|' . time());
            $info = array(
                'error_code' => 0,
                'token_id' => urlencode($tokenid)
            );
            $this->success('', $info);
        } else {
            //验证失败, error_code返回验证错误码
            $info = array(
                'error_code' => $result['result'],
                'token_id' => ''
            );
            $this->error('', $info);
        }
    }

    /**
     * 单独处理单点登录
     * @return string|boolean
     */
    public function ssoLogin()
    {
        $tokenid = v2_my_decrypt($this->param['token_id']);
        $tokenidArr = explode('|', $tokenid);
        if (time() - $tokenidArr[2] > 7200) {
            //如果超过7200秒
            $this->error('', ['result' => false]);
        }
        $username = $tokenidArr[0];
        $password = $tokenidArr[1];
        $loginResult = $this->logic()->loginVerify($username, $password, false, '', 100, true, false, false);
        if (1 == $loginResult['result']) {
            $logger = new \xphp\log\Log();

            $logger->write(print_r([$_SESSION, $loginResult['token']], true));
            //如果登录成功
            if (is_null(getCache('__token__'))) {
                // session只有销毁后才会再次生成
                $token = token();
                setCache('__token_pool__', [
                    $token => 1,
                ]);
                setCache('__token__', $token);
            }
            // 例如，通过 URL 参数传递
            $redirectUrl = '/index.php?csrf_token=' . urlencode(getCache('__token__')) .
                '&access_token=' . urlencode($loginResult['token']) .
                '&username=' . v2_encrypt_js_rsa($username) . '&password=' . v2_encrypt_js_rsa($password);

            // 进行跳转
            header('Location: ' . $redirectUrl);
            die;
        } else {
            //如果失败，返回false跳转到登录页面
            $this->error('', ['result' => false]);
        }
    }

    /**
     * 第三方登录内部
     * @return json
     */
    public function insideTestLogin()
    {
        // 参数验证
        $result = $this->logic()->loginVerifys($this->param['username'], md5($this->param['password']));
        if ($result['success']) {
            // 表示登录成功
            $data = [
                'access_token' => $result['data']['token'],
                'expires_in' => TIMESTAMP + xphp_get_config('special', 'access_token_timeout')
            ];
            $this->success($result['result'], $data);
        } else {
            return $this->error($result['message'], $result['data']);
        }
    }

    /**
     * 退出登录/锁定屏幕
     * @return json
     */
    public function loginOut()
    {
        $params = $this->param;
        if (!empty($params['out'])) {
            // 退出登录需要记录日志
            $user = xphp_get_user_info();
            $opHandler = (new \xphp\db\Op());
            $opHandler->loginLog(
                $user['userUuid'],
                $user['userName'],
                'SYSTEM_USER_LOGINOUT_SUCCESS',
                array('S:' . v2_get_client_iP())
            );
            $msg = xphp_get_lang('UI_USER_LOGIN_OUT');
        } else {
            $msg = xphp_get_lang('UI_USER_LOCK_SCREEN');
        }
        xphp_user_loginout();

        $this->success($msg);
    }
}
