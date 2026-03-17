<?php
// phpcs:ignoreFile -- 系统助手函数
declare (strict_types=1);

/***
 * xphp 助手函数
 * 系统内置的方法 用驼峰法
 */

use xphp\helper\Str;
use xphp\Validate;


/**************** sql封装方法 start *******************/

/**
 *  批量查询方法
 * @param string $sql 完整的查询语句
 * @param array $param SQL条件参数[1,2],每一项按位置对应SQL语句中的"?"，无参数不传
 * @return array fetchAll()
 */
function dbSelect(string $sql, $param = [])
{
    return (new \xphp\db\Op())->dbSelect($sql, $param);
}

/**
 * 数据库带参数，适用于insert,delete
 * @param string $sql SQL语句,如"select a from b where c = ?"
 * @param array $param SQL条件参数[1,2],每一项按位置对应SQL语句中的"?"，无参数不传
 * @return int 受影响的行数
 */
function dbQuery(string $sql, $param = array())
{
    return (new \xphp\db\Op())->dbQuery($sql, $param);
}

/**
 * 查询sql执行结果,成功或失败,适用于insert,update或delete,有的地方会有updates受影响为0行,但是结果是成功的,方便处理
 * @param string $sql
 * @return boolean 返回成功和失败
 */
function dbExec(string $sql, $param = array())
{
    return (new \xphp\db\Op())->dbQuery($sql, $param);

}

/**
 * 得到最后一次插入的id,last_insert_id()
 */
function dbLastInsertId()
{

    return (new \xphp\db\Op())->dbLastInsertId();
}

/**************** sql封装方法 end   *******************/

/**
* 获取系统下载的一些路由配置
 */
function getDownloadConfig($force = false)
{
    $keys = 'xphp_download_config_lists';
    // 强制获取缓存
    $config = cache($keys);
    if (empty($config) || $force) {
        $config = getConfig(CONF_PATH . 'download.php');
        // 缓存600秒
        cache($keys, $config, 600);
    }
    return $config;
}

/**
 * 路由配置文件读取
 * @param bool $force 是否强制刷新
 * @return array|mixed
 */
function getRouteConfig($force = false)
{

    $version = getXphpVersion();
    $url = getXphpUrl();
    if (getEnvs()) {
        // 如果是开发模式 那么强制不读缓存
        $force = true;
    }

    $cache_key = 'xphp_route_' . $force .'_' . $version . '_' . md5($url);
    $cache = cache($cache_key);
    if (empty($cache) || $force) {
        // 路由映射的文件名称
        $route_name = explode('/', $url)[1];

        // 读取出系统的配置文件
        $cache = getConfig(ROUTE_PATH . $route_name . '.php');

        // 合并下当前版本的配置
        $cache = array_merge($cache, getConfig(APP_PATH . $version . '/route/' . $route_name . '.php'));
        if (empty($cache)) {
            // 没有找到映射 那么去找找 route 文件里面的公共配置
            // 读取出系统的配置文件
            $cache = getConfig(ROUTE_PATH . 'route.php');

            // 合并下当前版本的配置
            $cache = array_merge($cache, getConfig(APP_PATH . $version . '/route/route.php'));
        }

        cache($cache_key, $cache);
    }
    return $cache;
}


if (!function_exists('myShutdown')) {
    /**
     * 自定义程序终止的监听
     */
    function myShutdown()
    {

        $err = error_get_last();
        if (!getEnvs('LOG_CLOSE')) {
            $level = xphp_get_config('log')['level'];
            if (empty($level) || (!is_null($err) && in_array($err['type'], $level))) {
                // 日志记录
                $logger = new \xphp\log\Log();

                $logger->write(print_r($err, true));
            }
        }

        if (!is_null($err) && in_array($err['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_USER_ERROR])) {
            // 发送邮件通知
            if (getEnvs('SEND_ERROR_EMAIL')) {
                sendEmail($err);
            }

            // 默认异常返回 致命的错误才会提示
            if (!getEnvs('APP_DEBUG')) {
                // 非开发模式
                (new \xphp\Response())->returns(['code' => -1, 'msg' => xphp_get_lang('WEB_ERROR_BD_HTTP_HEADER_500_ERROR')]);
            }
        }
    }
}

if (!function_exists('getRequestUrl')) {
    /**
     * 统一封装获取地址栏的url信息 为了后面好调整
     * 因为现在默认是 /v1/login/1 或者 /v1/login?id=1&uid=2 或者 /v1/login 这两种请求url
     * 也或者是 /v1/vm/lists/1  /v1/vm/lists?id=1&uid=2  或则 /v1/vm/lists 这两种请求
     * 后面如果需要适配其它url。则需要更改这里就行
     */
    function getRequestUrl()
    {
        $url = trim(str_replace('api/', '', trim($_SERVER['REQUEST_URI'], '/')), '/');

        // 优化 如果url参数有 / 的情况
        // 从第一个问号开始截取
        $position = strpos($url, '?');
        if ($position !== false) {
            $url1 = substr($url, 0, $position);
            $url2 = substr($url, $position);
            $return = array_filter(explode('/', $url1));
            $return[count($return)-1] .= $url2;

            return $return;
        }
        return array_filter(explode('/', $url));

        // return array_filter(explode('/', trim($_SERVER['REQUEST_URI'], '/')));
    }
}

if (!function_exists('getEnvs')) {
    /**
     * 获取env的文件配置
     * @param string $name 名称
     */
    function getEnvs($name = 'APP_DEBUG', $default = false)
    {
        $result = getenv(ENV_PREFIX . strtoupper(str_replace('.', '_', $name)));
        if (false !== $result) {
            return $result;
        } else {
            return $default;
        }
    }
}

if (!function_exists('encryptString')) {
    /**
     * 加密函数示例：使用 AES-256-CBC 加密算法
     * @param string $plainString 需要加密的内容
     * @param string $key         加密的key
     * @param string $iv          偏移量
     * @return string
     */
    function encryptString(string $plainString, $key = '7ebec7acd38b0643c34b09c84ea54393', $iv = 'sNONwyJtvi2ch2in'): string
    {
        return base64_encode(openssl_encrypt($plainString, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv));
    }
}

if (!function_exists('decryptString')) {
    /**
     * 加密函数示例：使用 AES-256-CBC 加密算法
     * @param string $encryptedString  加密后的密文
     * @param string $key              加密的key
     * @param string $iv               偏移量
     * @return string
     */
    // 解密函数示例：使用 AES-256-CBC 加密算法
    function decryptString(string $encryptedString, $key = '7ebec7acd38b0643c34b09c84ea54393', $iv = 'sNONwyJtvi2ch2in'): string
    {
        return openssl_decrypt(base64_decode($encryptedString), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}

if (!function_exists('getDomain')) {
    /**
     * 获取当前请求域名
     * * 获取当前包含协议的域名
     * @access public
     * @param bool $port 是否需要去除端口号
     * @return string
     */
    function getDomain(bool $port = false): string
    {

        return (isSsl() ? 'https' : 'http') . '://' . getHost($port);

    }
}

if (!function_exists('isSsl')) {
    /**
     * 当前是否ssl
     * @access public
     * @return bool
     */
    function isSsl(): bool
    {
        if ($_SERVER['HTTPS'] && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) {
            return true;
        } elseif ('https' == $_SERVER['REQUEST_SCHEME']) {
            return true;
        } elseif ('443' == $_SERVER['SERVER_PORT']) {
            return true;
        } elseif ('https' == $_SERVER['HTTP_X_FORWARDED_PROTO']) {
            return true;
        }

        return false;
    }
}

if (!function_exists('getHost')) {
    /**
     * 当前请求的host
     * @access public
     * @param bool $strict true 仅仅获取HOST
     * @return string
     */
    function getHost(bool $strict = false): string
    {

        $host = strval($_SERVER['HTTP_X_FORWARDED_HOST'] ?: $_SERVER['HTTP_HOST']);

        return true === $strict && strpos($host, ':') ? strstr($host, ':', true) : $host;
    }
}

if (!function_exists('sendEmail')) {
    /**
     * 发送邮件
     */
    function sendEmail($err)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            // 获取邮件配置
            $config = xphp_get_config('email');
            //服务器配置
            $mail->CharSet = "UTF-8";                     //设定邮件编码
            $mail->SMTPDebug = 0;                        // 调试模式输出
            $mail->isSMTP();                             // 使用SMTP
            $mail->Host = $config['host'];                // SMTP服务器
            $mail->SMTPAuth = true;                      // 允许 SMTP 认证
            $mail->Username = $config['username'];                // SMTP 用户名  即邮箱的用户名
            $mail->Password = $config['password'];             // SMTP 密码  部分邮箱是授权码(例如163邮箱)
            $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
            $mail->Port = $config['port'];                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

            $mail->setFrom($config['username'], $config['sendname']);  //发件人
            foreach ($config['recive'] as $item) {
                $mail->addAddress($item['reciveaddress'], $item['recivename']);  // 收件人
            }

            //$mail->addReplyTo('xxxx@163.com', 'info'); //回复的时候回复给哪个邮箱 建议和发件人一致
            //$mail->addCC('cc@example.com');                    //抄送
            //$mail->addBCC('bcc@example.com');                    //密送

            //发送附件
            // $mail->addAttachment('../xy.zip');         // 添加附件
            // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名

            //Content
            $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->Subject = '异常提示:' . getDomain() . '_' . date('Y-m-d H:i');
            $mail->Body = '<h1>' . json_encode($err) . '</h1>' . date(xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s');
            $mail->AltBody = print_r($err, true); // 不支持html的显示这个内容

            $mail->send();
            // echo '邮件发送成功';
        } catch (Exception $e) {
            // echo '邮件发送失败: ', $mail->ErrorInfo;
        }
    }
}

if (!function_exists('getXphpVersion')) {
    /**
     * 获取地址栏的版本
     * @param $type 默认0表示url地址的版本号，1表示模块的版本号，2表示方法的版本号
     * @return string
     */
    function getXphpVersion($type = 0): string
    {

        if ($type == 0) {
            if (defined('API_VERSION_')) {
                return API_VERSION_;
            }
            $query = getRequestUrl();
            // 版本
            return strtolower($query[0]);
        }

        // 也可以支持在地址栏获取版本信息
        $x_api_version = empty($_SERVER['HTTP_X_API_VERSION']) ? $_GET['x-api-version'] : $_SERVER['HTTP_X_API_VERSION'];

        // 兼容 xApiVersion 写法
        $x_api_version = empty($x_api_version) ? $_GET['xApiVersion'] : $x_api_version;

        if (defined('API_VERSION')) {
            // 也支持从定义中取 API_VERSION
            $x_api_version = $x_api_version ?: API_VERSION;
        }

        // header里面携带的版本信息  1.0-rev0
        $version_arr = explode('-', $x_api_version);
        if ($type == 1) {
            // 需要进行优化处理，只取 1.0 的 0
            $version_arr = explode('.', $version_arr[0]);
            return 'v' . ($version_arr[1] ?? 0);
        }
        return $version_arr[$type-1];
    }
}

if (!function_exists('getXphpUrl')) {
    /**
     * 获取地址栏的请求url 不包含参数值
     * url 可能携带多个id参数 如 /v1/jobs/{ID1}/job_log/{ID2} 这种url应该是/v1/jobs/job_log 参数应该是 jobs_uuid = {ID1}. job_log_uuid = {ID2}
     * 其中参数的判断依据是 要么是整型 要么是包含四个 - 的36位字符串
     * @return string
     */
    function getXphpUrl(): string
    {

        $query = getRequestUrl();
        $length = count($query) - 1;
        if ($length < 1) {
            return '';
        }
        // 去除 ? 后面的参数
        $query[$length] = explode('?', $query[$length])[0];

        // 路由校验
        foreach ($query as $item => $value) {
            if (($value == '' .intval($value) && intval($value))  || count(explode('-', $value)) > 3) {
                // 是参数
                unset($query[$item]);
            }
        }

        if (count($query) < 2) {
            return '';
        }

        return implode('/', $query);
    }
}

if (!function_exists('getCache')) {
    /**
     * 获取缓存
     * @param string $key 缓存的key值
     */
    function getCache(string $key)
    {
        return !empty($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
}

if (!function_exists('setCache')) {
    /**
     * 设置缓存
     * @param string $key 缓存的key值
     * @param object $val 缓存的值
     */
    function setCache(string $key, $val = '')
    {

        $sessionWasStarted = session_status() === PHP_SESSION_ACTIVE;
        if (!$sessionWasStarted) {
            session_start();
        }

        $_SESSION[$key] = $val;

        if (!$sessionWasStarted) {
            session_write_close();
        }
    }
}

if (!function_exists('setCaches')) {
    /**
     * 设置缓存
     * @param array $arr 缓存的对象 key => val
     */
    function setCaches(array $arr)
    {
        $sessionWasStarted = session_status() === PHP_SESSION_ACTIVE;
        if (!$sessionWasStarted) {
            session_start();
        }
        $_SESSION = array_merge($_SESSION, $arr);
        if (!$sessionWasStarted) {
            session_write_close();
        }
    }
}

if (!function_exists('unsetCaches')) {
    /**
     * clear 缓存
     * @param array $arr 缓存的对象 key
     */
    function unsetCaches(array $arr)
    {
        $sessionWasStarted = session_status() === PHP_SESSION_ACTIVE;
        if (!$sessionWasStarted) {
            session_start();
        }
        foreach ($arr as $item){
            unset($_SESSION[$item]);
        }
        if (!$sessionWasStarted) {
            session_write_close();
        }
    }
}

if (!function_exists('setMemcached')) {
    /**
     * 设置memcached的服务器连接
     * @param string $host 连接地址
     * @param int $port 连接端口
     * @return object|false
     */
    function setMemcached(string $host = 'localhost', $port = 11211)
    {
        if (!class_exists('Memcached')) {
            // Memcached 类不存在
            return false;
        }
        // 创建Memcached对象
        $memcached = new Memcached();

        // 添加Memcached服务器
        // 注意：如果Memcached服务监听在IPv6地址上，你可能需要使用'[::1]'作为地址
        $memcached->addServer($host, $port);

        // 检查服务器是否成功添加（可选）
        if ($memcached->getServerList()) {
            return $memcached;
        } else {
            return false;
        }
    }

}

if (!function_exists('getConfig')) {
    /**
     * 配置文件加载
     * @param string $file 如果加载当前版本的直接跟着名称就行，否则给完整的路径
     * @return array
     */
    function getConfig(string $file): array
    {
        static $localCache = [];
        static $isEncryptedChecked = false;
        static $isEncrypted = false;

        $key = 'helper_get_config_' . ($file);
        if (isset($localCache[$key])) {
            return $localCache[$key];
        }

        if (strpos($file, '.php') === false) {
            $file = APP_PATH . getXphpVersion() . '/config/' . $file . '.php';
        }

        if (!file_exists($file)) {
            return $localCache[$key] = [];
        }

        $arr = include $file;

        // ✅ 优化：只检查一次 app.php 是否加密
        if (!$isEncryptedChecked) {
            $appFile = APP_PATH . getXphpVersion() . '/config/app.php';
            if (file_exists($appFile)) {
                $appConfig = include $appFile; // 临时加载一次
                $isEncrypted = !empty($appConfig['ext']) && $appConfig['ext'] != '.php';
            }
            $isEncryptedChecked = true;
        }

        // ✅ 优化：用 associative array 替代 in_array
        $encryptMap = [
            APP_PATH . getXphpVersion() . '/config/app.php'         => true,
            APP_PATH . getXphpVersion() . '/config/database.php'   => true,
            APP_PATH . getXphpVersion() . '/config/email.php'      => true,
            APP_PATH . getXphpVersion() . '/config/socket.php'     => true,
            APP_PATH . getXphpVersion() . '/config/three_powers.php' => true,
        ];

        if ($isEncrypted && isset($encryptMap[$file])) {
            $arr = decryptArrayValues($arr);
        }

        return $localCache[$key] = $arr;
    }
}

if (!function_exists('createKeyIv')) {
    /**
     * 获取秘钥
     * @param int $type 类型 默认获取key 1获取iv
     * @return string
     */
    function createKeyIv(int $type = 0): string
    {
        if ($type == 0) {
            // key
            $binary = array (48,101,53,53,99,102,56,98,102,98,97,48,102,100,102,52,97,53,51,51,53,51,99,100,48,100,55,101,50,56,50,101,55,50,55,97,51,99,49,101,53,48,52,49,52,53,49,54,52,97,101,57,55,97,97,57,53,53,52,50,99,97,57,97 );
            $len = 32;
        } else {
            // iv
            $binary = array (100,109,108,117,89,50,104,112,98,106,69,121,77,122,81,49,78,106,99,52,99,50,116,53);
            $len = 16;
        }

        // 将二进制数据转换成明文
        $string = '';
        foreach ($binary as $byte) {
            $string .= chr($byte);
        }
        return substr(md5($string), 0, $len);
    }
}

if (!function_exists('decryptArrayValues')) {
    /***
     * 解密
     * @param array $array 加密后的数组
     * @return array
     */
    function decryptArrayValues(array $array): array
    {
        $key = createKeyIv(0);
        $iv = createKeyIv(1);
        foreach ($array as $k => $value) {
            if (is_array($value)) {
                $array[$k] = decryptArrayValues($value); // 修正递归调用时的参数
            } else {
                $values = openssl_decrypt($value, 'AES-256-CBC', $key, 0, $iv);
                // 还要进行类型判断
                $arrays = explode('!'.md5($iv).'!', $values);
                if (count($arrays) == 2) {
                    // 根据数据类型处理解密后的数据
                    switch (unserialize($arrays[0])) {
                        case 'boolean':
                            // 将字符串 "true" 或 "false" 转换回布尔值
                            $data = $arrays[1] === 'true';
                            break;
                        case 'integer':
                            $data = (int)$arrays[1];
                            break;
                        default:
                            // 字符串不需要额外处理
                            $data = $arrays[1];
                    }
                } else {
                    $data = $values;
                }
                $array[$k] = $data;
            }
        }
        return $array;
    }
}

if (!function_exists('config')) {
    /**
     * 配置文件加载 一般在系统内部才使用
     * @param string $file 文件名称 优先级是 版本->系统
     * @return array
     */
    function config(string $file): array
    {
        // 系统配置
        $config = require CONF_PATH . $file . '.php';

        // 版本配置
        $files = APP_PATH . getXphpVersion() . '/config/' . $file . '.php';

        if (file_exists($files)) {
            $arr2 = require $files;
            $config = array_merge($config, $arr2);
        }

        return $config ?? [];
    }
}

if (!function_exists('dump')) {
    /**
     * 浏览器友好的变量输出
     * @param mixed $vars 要输出的变量
     * @return void
     */
    function dump(...$vars)
    {
        ob_start();
        var_dump(...$vars);

        $output = ob_get_clean();
        $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

        if (PHP_SAPI == 'cli') {
            $output = PHP_EOL . $output . PHP_EOL;
        } else {
            if (!extension_loaded('xdebug')) {
                $output = htmlspecialchars($output, ENT_SUBSTITUTE);
            }
            $output = '<pre>' . $output . '</pre>';
        }

        echo $output;
        die;
    }
}

if (!function_exists('token')) {
    /**
     * 随机token的生成
     * @param int $len
     * @param string $type 加密方式
     * @return string
     */
    function token($len = 32, $type = 'md5'): string
    {
        $key = (float)microtime() * 1000000 . time() . $_SERVER['REQUEST_URI'];

        $chars = array(
            'Q', '@', '8', 'y', '%', '^', '5', 'Z', '(', 'G', '_', 'O', '`', 'S', '-', 'N', '/', '|', ':', '1', 'E', 'L', '4', '&', '6', '7', '#', '9', 'a', 'A', 'b', 'B', '~', 'C', 'd', '>', 'e', '2', 'f', 'P', 'g', ')',
            '?', 'H', 'i', 'X', 'U', 'J', 'k', 'r', 'l', '3', 't', 'M', 'n', '=', 'o', '+', 'p', 'F', 'q', '!', 'K', 'R', 's', 'c', 'm', 'T', 'v', 'j', 'u', 'V', 'w', ',', 'x', 'I', '$', 'Y', 'z', '*'
        );

        $numChars = count($chars) - 1;
        $key .= $chars[mt_rand(0, $numChars)];

        $token = $type($key);

        return substr($token, 0, $len);
    }
}


if (!function_exists('cookie')) {
    /**
     * Cookie管理
     * @param string|array  $name cookie名称，如果为数组表示进行cookie设置
     * @param mixed         $value cookie值
     * @param mixed         $option 参数 可以直接跟时间戳，表示有效时间
     * @return mixed
     */
    function cookie($name, $value = '', $option = null)
    {
        if (is_array($name)) {
            // 初始化
            \xphp\Cookie::init($name);
        } elseif (is_null($name)) {
            // 清除
            \xphp\Cookie::clear($value);
        } elseif ('' === $value) {
            // 获取
            return 0 === strpos($name, '?') ? \xphp\Cookie::has(substr($name, 1), $option) : \xphp\Cookie::get($name, $option);
        } elseif (is_null($value)) {
            // 删除
            return \xphp\Cookie::delete($name);
        } else {
            // 设置
            return \xphp\Cookie::set($name, $value, $option);
        }
    }
}

if (!function_exists('cache')) {
    /**
     * 缓存管理
     * @param mixed $name 缓存名称，如果为数组表示进行缓存设置
     * @param mixed $value 缓存值
     * @param mixed $options 缓存参数 可以直接跟时间戳，表示有效时间
     * @param null $tag 缓存标签
     * @return mixed
     */
    function cache($name = '', $value = '', $options = null, $tag = null)
    {

        if (getEnvs('APP_DEBUG')) {
            // 开发模式下的缓存设置为10秒
           // return !empty($value) ? true : null;
            if (is_numeric($options)) {
                $options = 10;
            }
        }

        if (is_array($options)) {
            // 缓存操作的同时初始化
            \xphp\Cache::connect($options);
        } elseif (is_array($name)) {
            // 缓存初始化
            return \xphp\Cache::connect($name);
        }
        if (is_null($name)) {
            return \xphp\Cache::clear($value);
        } elseif ('' === $value) {
            // 获取缓存
            return 0 === strpos($name, '?') ? \xphp\Cache::has(substr($name, 1)) : \xphp\Cache::get($name);
        } elseif (is_null($value)) {
            // 删除缓存
            return \xphp\Cache::rm($name);
        } elseif (0 === strpos($name, '?') && '' !== $value) {
            $expire = is_numeric($options) ? $options : null;
            return \xphp\Cache::remember(substr($name, 1), $value, $expire);
        } else {
            // 缓存数据
            if (is_array($options)) {
                $expire = $options['expire'] ?? null; //修复查询缓存无法设置过期时间
            } else {
                $expire = is_numeric($options) ? $options : null; //默认快捷缓存设置过期时间
            }
            if (is_null($tag)) {
                return \xphp\Cache::set($name, $value, $expire);
            } else {
                return \xphp\Cache::tag($tag)->set($name, $value, $expire);
            }
        }
    }
}


if (!function_exists('validate')) {
    /**
     * 生成验证对象
     * @param string|array $validate 验证器类名或者验证规则数组
     * @param array $message 错误提示信息
     * @param bool $batch 是否批量验证
     * @param bool $failException 是否抛出异常
     * @return Validate
     */
    function validate($validate = '', array $message = [], bool $batch = false, bool $failException = true): Validate
    {
        if (is_array($validate) || '' === $validate) {
            $v = new Validate();
            if (is_array($validate)) {
                $v->rule($validate);
            }
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }

            $class = false !== strpos($validate, '\\') ? $validate : parseClass('validate', $validate);

            $v = new $class();

            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        return $v->message($message)->batch($batch)->failException($failException);
    }
}

if (!function_exists('parseClass')) {
    /**
     * 解析应用类的类名
     * @access public
     * @param string $layer 层名 controller model ...
     * @param string $name 类名
     * @return string
     */
    function parseClass(string $layer, string $name): string
    {
        $name = str_replace(['/', '.'], '\\', $name);
        $array = explode('\\', $name);
        $class = Str::studly(array_pop($array));
        $path = $array ? implode('\\', $array) . '\\' : '';

        return 'app\\' . $layer . '\\' . $path . $class;
    }
}

if (!function_exists('inArray')) {
    /**
     * 代替in_array函数（in_array 在大数据量中会很慢）
     * @param $item
     * @param $array
     * @return bool
     */
    function inArray($item, $array): bool
    {
        $flipArray = array_flip($array);
        return isset($flipArray[$item]);
    }
}

if (!function_exists('mkdirs')) {
    /**
     * 创建多层目录
     *
     * @param string $dirs
     * @param number $mode
     * @return boolean
     */
    function mkdirs($dirs = '', $mode = 0777) {
        if (! is_dir ( $dirs )) {
            mkdirs ( dirname ( $dirs ), $mode );
            $ret = @mkdir ( $dirs, $mode );
            chmod ( $dirs, $mode );
            return $ret;
        }
        return true;
    }
}
