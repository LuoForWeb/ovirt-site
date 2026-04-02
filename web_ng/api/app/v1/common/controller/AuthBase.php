<?php

namespace app\v1\common\controller;

/**
 * note          权限管理控制器
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */

class AuthBase extends Base
{
    /**
    * 构造方法
     */
    public function __construct()
    {

        parent::__construct();
        if (!in_array(getXphpUrl(), getDownloadConfig()) && !getEnvs('APP_DEBUG')) {
            if (!$this->checkApiKey()) {
                // 需要校验登录和权限的
                $this->checkAuth();
                $this->permissionCheck();
            }
        }
    }

    /**
     * 登录权限校验
     * @return bool|void|json
     */
    private function checkAuth()
    {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];

        // 如果是大屏的就单独校验  那么token组成是 auth_token_visualscreen_ + accesstoken
        if (strpos($authorization, 'auth_token_visualscreen_') !== false && !empty(getCache($authorization))) {
            $_SERVER['HTTP_AUTHORIZATION'] = str_replace(
                'auth_token_visualscreen_',
                '',
                $_SERVER['HTTP_AUTHORIZATION']
            );
            return true;
        }

        $authtoken = 'auth_token_' . $authorization;

        $cache = getCache($authtoken);

        if (xphp_get_oldpath()) {
            // 兼容下老版本的，都以老版本的web_ui来限制是否失效
            if (empty($cache)) {
                // 加上如果出现意外丢失session 那么清除，让用户自动重新登陆
                setCache($authtoken, null); // 清理过期的缓存信息
                $this->error(xphp_get_lang('API_CODE_ACCESS_TOKEN_TIMEOUT'), [], 910086);
            }
            return true;
        }

        if (empty($cache) || $cache['timeout'] < time()) {
            setCache($authtoken, null); // 清理过期的缓存信息
            $this->error(xphp_get_lang('API_CODE_ACCESS_TOKEN_TIMEOUT'), [], 910086);
        }
    }

    /**
     * 具体的权限校验
     * @return void|json
     */
    private function permissionCheck()
    {

        $cache = getCache('auth_token_' . $_SERVER['HTTP_AUTHORIZATION']);

        $permissionFunction = $cache['permissionFunction'];

        // 这里进行具体的权限校验
        // 需要获取method和具体的url标识
        $url = explode('/', getXphpUrl()); // 返回 v1/login/xx 这种格式
        unset($url[0]); // 去除版本号
        $urls = implode('_', $url); // 获得 login_xx 这种格式

        $method = strtolower($_SERVER['REQUEST_METHOD']); // 请求方法 如 get

        if (
            in_array($method . '-' . $urls, $cache['permissionFunctions']) &&
            (
            empty($permissionFunction[$method]) ||
            (!empty($permissionFunction[$method]) &&
            !in_array($urls, $permissionFunction[$method]) &&
                !in_array('*', $permissionFunction[$method]))
            )
        ) {
            // 1 只校验存在menu里面的路由
            // 2 是否存在当前请求方式
            // 3 当前请求方式的数组里面是否存在这个路由或者当前请求方式是否存在 *
            $this->error(xphp_get_lang('WEB_ILLEGAL_ERROR'));
        }
    }

    /**
     * 处理返回的消息
     * @param array $ret 返回的消息
     * @return void
     */
    protected function outputHandle(array $ret)
    {
        if (false === $ret['success']) {
            $this->error($ret['message'], $ret['data'], $ret['code']);
        }
        $this->success($ret['message'], $ret['data'], $ret['code']);
    }
}
