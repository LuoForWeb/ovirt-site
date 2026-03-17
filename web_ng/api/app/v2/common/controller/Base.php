<?php

namespace app\v2\common\controller;

use app\v2\user\v0\logic\Login;
use xphp\db\Op;
use xphp\Request;
use xphp\Response;

/**
 * note          基类控制器
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Base
{
    // 不验证安全token的路由集合 比如登录，编辑器上传文件等
    private static $permission = [
        'v2/login',
        'v2/get_accestoken',
        'v2/users/third_login',
        'v2/system/get_time',
        'v2/system/upload', // 上传文件
    ];
    // 声明请求
    // phpcs:ignore
    protected $_request;

    // 请求参数获取
    protected $param = [];

    /**
     * 构造方法
     */
    public function __construct()
    {

        $this->_request = new Request();

        // 获取请求参数
        $this->param = $this->_request->param();

        if (!getEnvs('APP_DEBUG')) {
            // 生产模式需要校验
            // 接口请求次数限制
            $this->apiRequest();

            if (!$this->checkApiKey() && $this->checkApiSafe()) {
                $query = getXphpUrl();
                $cleanPath = preg_replace('/^v\d+\//', '', $query); // 去除版本号
                if (!inArray($cleanPath, getDownloadConfig())) {
                    // token校验
                    $this->checkToken();

                    // sign 检查
                    $this->signCheck();
                }
            }
        }
    }

    /**
     * 这里校验是否是apikey的方式
     * @return boolean
     */
    protected function checkApiKey(): bool
    {
        //先检测是否含有apikey
        if (empty($_SERVER['HTTP_X_API_KEY'])) {
            //如果没有apikey,则要进行验证身份信息
            return false;
        }

        $apikey = $_SERVER['HTTP_X_API_KEY'];
        if (strlen($apikey) !== 32) {
            //先对apikey进行解密
            $apikey = xphp_decrypt($apikey);
        }

        //以下如果含有apikey,则先进行apikey的验证
        $sql = "select count(apikey) as total from bd_apikey where apikey = ?";
        $result = dbSelect($sql, array($apikey));
        $total = intval($result[0]['total']);
        if ($total == 0) {
            //如果在数据库中没找到相关apikey,则要进行后续的身份验证
            return false;
        } elseif ($total == 1) {
            //如果刚好找到一条满足条件的apikey,则不进行身份验证
            //先进行身份信息的登录
            $this->setApikeyInfo($apikey);
            return true;
        } else {
            //其他任何返回结果都为false,包括有2条一样apikey的情况
            return false;
        }
    }

    /**
    * 校验是否关闭了系统安全的api安全配置
     * @return boolean
     */
    protected function checkApiSafe(): bool
    {
        // 查询api安全开关
        $apiSafe = dbSelect(
            'select settings_content from bd_system_settings where settings_type = ? order by settings_id desc limit 1',
            [xphp_get_config('app', 'SETTINGS_CONF')['API_SAFE_CONFIG'] ?? 31]
        );
        $apiFlag = true; // 默认开启
        if (!empty($apiSafe[0]['settings_content'])) {
            $apiSafe = json_decode($apiSafe[0]['settings_content'], true);
            $apiFlag = !empty($apiSafe['flag']);
        }
        return $apiFlag;
    }

    /**
    * 根据 apikey 生成对应的用户信息以完成用户信息的获取
     * @param string $apikey apikey
     * @return bool
     */
    private function setApikeyInfo(string $apikey)
    {
        $key = 'auth_apikey_' . $apikey;
        if (empty(getCache($key))) {
            // 查询数据生成缓存信息
            $data = dbSelect("select user_uuid,user_name,user_type,email,`language` from bd_user where user_level = 1");
            if (empty($data)) {
                return false;
            }
            $users = $data[0];
            (new Login())->successLogin(
                $apikey,
                $users['user_name'],
                $users['user_uuid'],
                $users['user_type'],
                $users['email'],
                '',
                $users['language'],
                '',
                ''
            );
            setCache($key, $apikey);
            setCache('auth_apikey_', $apikey);
        }
        return true;
    }

    /**
     * token 校验
     * @return void|json
     */
    private function checkToken()
    {
        $query = getXphpUrl();
        if (!inArray($query, self::$permission)) {
            // 验证token的情况
            $csfToken = $_SERVER['HTTP_X_CSRF_TOKEN'];
            // token是一个资源池，最多的个数根据配置来读取
            $special = xphp_get_config('special');
            $tokenArr = xphp_get_cache('__token_pool__'); // token池
            $chk = true;
            // 先校验失败的
            $tokenCheckNum = $tokenArr[$csfToken] ?: 1;
            if (empty($tokenArr[$csfToken]) || $tokenCheckNum >= $special['token_available_times']) {
                $chk = false;
                // 存在还需要维护清除下过期的
                if (!empty($tokenArr[$csfToken])) {
                    // 删除
                    unset($tokenArr[$csfToken]);
                    if ($tokenCheckNum == $special['token_available_times']) {
                        // 临界点 最后一次校验
                        $chk = true;
                        $tokenCheckNum = 0;
                    }
                }
                $csfToken = token();
                $tokenArr[$csfToken] = 1;
                // token 过期或者失效 未超过个数重新追加一个
                if (count($tokenArr) < $special['token_available_num']) {
                    $csfToken = token();
                    $tokenArr[$csfToken] = 1;
                }
            }
            // 记录token为了返回
            xphp_set_cache('__token__', $csfToken);

            // 增加使用次数记录
            $chk && $tokenArr[$csfToken] = $tokenCheckNum + 1;

            // 更新token资源池
            xphp_set_cache('__token_pool__', $tokenArr);

            !$chk && $this->error(xphp_get_lang('WEB_PLATFORM_TOKEN_ERROR'), [], 910087);
        }
    }

    /**
     * sign 签名验证
     * @return bool|json
     */
    private function signCheck()
    {
        $query = getXphpUrl();
        if (inArray($query, self::$permission)) {
            return true;
        }
        $data = $this->_request->param(1);

        //check timestamp
        // 验证请求， 10分钟失效  // 防止暴力请求
        if (
            empty($data['sign']) || empty($data['timestamp']) || (getEnvs('SIGN_CHECK_TIMELINESS') &&
                (time() - intval($data['timestamp'] / 1000) > getEnvs('SIGN_CHECK_TIMELINESS')))
        ) {
            // 抛出异常结束 暂时放开时间限制
            //  $this->error(xphp_get_lang('WEB_SIGN_ERROR'));
        }
        $clientSign = $data['sign'];
        unset($data['sign']);

        $serverSign = $this->makeSign($data);

        if ($clientSign == $serverSign) {
            return true;
        }
        $this->error(xphp_get_lang('WEB_SIGN_ERROR'));
    }

    /**
     * 生成签名
     * @param $data 数据
     * @return string
     */
    private function makeSign($data): string
    {

        // 1,按照请求参数名称将所有请求参数按照键名进行升序排序
        ksort($data);

        //  2. 数组json化
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // 3, 进行字符串加密
        return xphp_encrypt_js($string);
    }

    /**
     * 接口请求次数限制
     * @return bool|void
     */
    private function apiRequest()
    {
        $sessionkey = 'x_php_request_special_token';

        $special = xphp_get_config('special');

        if (!(isset($special['maxQequest']) && $special['maxQequest'] > 0)) {
            return true;
        }

        $requestNumKey = 'requestTencentNum:' . $sessionkey . getXphpUrl();

        $expire = 1; //过期时间秒
        if (isset($special['unitQequest']) && $special['unitQequest'] > 1) {
            $expire = $special['unitQequest'];
        }
        $requestNum = xphp_get_cache($requestNumKey);

        if (empty($requestNum)) {
            // 不存在缓存 写入
            xphp_set_cache($requestNumKey, [1, TIMESTAMP + $expire]);
            return true;
        }

        // 缓存里面的时间未过期 并且请求次数大于配置的次数 那么不允许走下去
        if ($requestNum[0] >= $special['maxQequest'] && $requestNum[1] > TIMESTAMP) {
            $this->error('request to busy,wait a mount');
        }

        xphp_set_cache($requestNumKey, [$requestNum[0] + 1, $requestNum[1]]);
    }

    /**
     * 参数验证
     * @param string $scene 场景
     * @param string $class 可以为空 表示同级别目录下同名称的验证器 只是个名称 就是同级别下的某个验证器 也可以是绝对的路径
     * @param array  $data  默认是所有的请求参数，也可以自定义
     * @return void|json
     */
    protected function checkParams(string $scene, string $class = '', $data = [])
    {

        try {
            if (empty($data)) {
                // 默认是请求的所有参数
                $data = $this->param;
            }

            $request = $this->_request;

            if (empty($class)) {
                // 默认为同级别的同名称的验证器
                $class = $request->request(4) . '\\validate\\' . $request->request(2);
            } elseif (!(strpos($class, '\app') !== false)) {
                // 表示写的相对路径 就是同级别下的某个验证器
                $class = $request->request(4) . '\\validate\\' . $class;
            }
            validate($class)->scene($scene)->check($data);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * logic 获取
     * @param string $class  可以为空
     *                       表示同级别目录下同名称的logic
     *                       只是个名称 就是同级别下的某个logic
     *                       也可以是绝对的路径
     * @param string $method 方法
     * @param array  $param  数据
     * @return Op
     */
    protected function logic(string $class = '', $method = '', $param = [])
    {
        if (empty($class)) {
            // 默认为同级别的同名称的logic
            $class = $this->_request->request(4) . '\\logic\\' . $this->_request->request(2);
        } elseif (!(strpos($class, '\app') !== false)) {
            // 表示写的相对路径 就是同级别下的某个logic
            $class = $this->_request->request(4) . '\\logic\\' . $class;
        }

        if (empty($method)) {
            return (new $class());
        }

        return (new $class())->$method($param);
    }

    /**
     * 统一的消息返回
     * @param $result  结果bool
     * @param $operate 操作码
     * @param $msg     提示信息
     * @param string $level   级别
     * @param int    $errcode 错误码
     * @param string $extInfo 其它信息
     * @param array  $header  请求头
     * @param string $type    类型
     * @return void
     */
    protected function muOpResult(
        $result,
        $operate,
        $msg = '',
        $level = '',
        $errcode = 0,
        $extInfo = '',
        $header = [],
        $type = 'json'
    ) {

        $data = (new Op())->muOpResult($result, $operate, $msg, $level, $errcode, $extInfo);

        return (new Response())->returns($data, $header, $type);
    }

    /**
     * 输出自定义成功内容
     * @param string $msg    提示语
     * @param array  $data   数据
     * @param int    $code   业务状态码
     * @param array  $header 请求头
     * @param string $type   类型
     * @return void
     */
    protected function success($msg = '', $data = [], $code = 0, $header = [], $type = 'json')
    {

        $datas = [
            'success' => true,
            'code' => $code,
            'message' => $msg ?: xphp_get_lang('WEB_PUBLIC_SUCCESS'),
            'data' => $data ?: [],
        ];

        return (new Response())->returns($datas, $header, $type);
    }

    /**
     * 输出自定义失败内容
     * @param string $msg    提示语
     * @param array  $data   数据
     * @param int    $code   业务状态码
     * @param array  $header 请求头
     * @param string $type   类型
     * @return void
     */
    protected function error($msg = '', $data = [], $code = -1, $header = [], $type = 'json')
    {

        $datas = (new Op())->muOpResult(
            false,
            xphp_get_lang('UI_PUBLIC_TIPS'),
            $msg ?: xphp_get_lang('WEB_PUBLIC_FAILURE'),
            'error',
            $code,
            $data
        );
        if ($data) {
            $datas['data'] = $data;
        }
        if (!empty($msg) && $code == -1) {
            $datas['message'] = $msg;
        }

        // $datas = [
        //     'success' => false,
        //     'code' => $code,
        //     'message' => $msg ?: xphp_get_lang('WEB_PUBLIC_FAILURE'),
        //     'data' => $data ?: [],
        // ];

        return (new Response())->returns($datas, $header, $type);
    }

    /**
     * 统一处理消息
     * @param $flag   标志
     * @param string $msg    提示语
     * @param array  $data   数据
     * @param int    $code   业务状态码
     * @param array  $header 请求头
     * @param string $type   输出类型
     * @return void
     */
    protected function outputMsg($flag, $msg = '', $data = [], $code = 200, $header = [], $type = 'json')
    {
        if ($flag) {
            return $this->success($msg, $data, $code, $header, $type);
        } else {
            return $this->error($msg, $data, $code, $header, $type);
        }
    }
}
