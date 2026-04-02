<?php
// phpcs:ignoreFile -- 框架类
declare(strict_types=1);

namespace xphp;

/**
 * note          请求接收类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:09
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Request
{

    // 实例化对象
    private static $_instance = array();

    /**
     * 获取请求 并解析路由和参数
     * @params boolean $flag 携带了就表示只是加载配置文件
     */
    public static function start($flag = false)
    {

        if (!getEnvs('APP_DEBUG')) {
            // 关闭所有错误信息
            error_reporting(0);
        } else {
            if (!ini_get('display_errors')) {
                ini_set('display_errors', 'On');
            }
            // error_reporting(E_ALL);
            error_reporting(E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_RECOVERABLE_ERROR);
        }

        // 实现自己的错误信息展示
        register_shutdown_function('myShutdown');

        if ($flag) {
            return;
        }

        // 这里进行路由跳转判断 如果开启了mock 那么直接是个中转站
        $request_url = explode('?', $_SERVER['REQUEST_URI']);
        // 登录接口不走mock
        if (getEnvs('MOCK_FLAG') && $request_url[0] != '/api/v1/login') {
            // 走mock去
            self::mock();
        } else {
            // 路由跳转
            self::route();
        }
    }

    /**
     * mock请求并返回
     */
    static private function mock()
    {

        // 去除get请求的 ？ 后面的参数
        $request_url = explode('?', $_SERVER['REQUEST_URI']);

        $url = getEnvs('MOCK_URL') . $request_url[0];
        $return = (new Curl())->curl($url, '', $_SERVER['REQUEST_METHOD']);
        $data = json_decode($return, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
        return (new Response())->returns($data);
    }

    /**
     * 获取资源标识对应路由标识 路由有两层的话，那么就以 _ 下划线隔开
     */
    static private function getName()
    {
        $query = getXphpUrl();
        // 校验header里面是否携带了 HTTP_X_API_VERSION 参数并且值的格式为 1.0-rev0
        if (empty($_SERVER['HTTP_X_API_VERSION']) || !(strpos($_SERVER['HTTP_X_API_VERSION'], '-rev') !== false)) {
            // 下载相关的接口配置
            if (!inArray($query, getDownloadConfig())) {
                // 路由不符合规定 header必须携带
                return self::back();
            }
        }

        if (!$query) {
            return self::back();
        }

        $query_arr = explode('/', $query);
        // 去除版本的路由
        array_splice($query_arr, 0, 1);

        // 资源标识 多个以_下划线隔开
        return implode('_', array_filter($query_arr));
    }

    /**
     * 获取路由映射，返回相关的信息
     */
    static private function route()
    {

        // 获取路由的数组
        $route_arr = self::getRouteArr();

        // 版本
        $version = getXphpVersion();
        $version1 = getXphpVersion(1);

        $class = '\app\\' . $version . '\\' . $route_arr['module'] . '\\' . $version1 . '\\controller\\' . $route_arr['class'];

        self::instance($class, $route_arr['method']);
    }

    /**
     * 获取命令空间或控制器或方法名
     * @param int $type 0 默认是命名空间 1模块 2控制器 3方法 4 控制器同级的命名空间
     */
    public function request($type = 0)
    {

        // 获取路由的数组
        $route_arr = self::getRouteArr();

        // 版本
        $version = getXphpVersion();
        $version1 = getXphpVersion(1);

        $array = [
            '\app\\' . $version . '\\' . $route_arr['module'] . '\\' . $version1 . '\controller',
            $route_arr['module'],
            $route_arr['class'],
            $route_arr['method'],
            '\app\\' . $version . '\\' . $route_arr['module'] . '\\' . $version1,
        ];

        return $array[$type];
    }

    /**
     * 获取 route 数组
     * @return mixed ['module' => 'home', 'class' => 'Index', 'method' => 'test'];
     */
    private static function getRouteArr()
    {

        $route_config = getRouteConfig();

        if (empty($route_config)) {
            return self::back();
        }

        $method = strtolower($_SERVER['REQUEST_METHOD']);
        // 获取路由对应的标识
        $name = self::getName();

        $cache_key = 'cache_get_route_arr_' . $name . '_' . $method;

        $cache = cache($cache_key);

        if (empty($cache)) {
            if (!empty($route_config[$name][$method])) {
                $cache = $route_config[$name][$method];
            } else {
                // 那么换另外一种方式读取
                // 模块(module)是第一层键
                foreach ($route_config as $modules => $controllers) {
                    // 类（class）是第二层键
                    foreach ($controllers as $controller => $actions) {
                        // 动作（action）是第三层，并且包含请求方法作为键
                        if (isset($actions[$name]) && isset($actions[$name][$method])) {
                            $module = $modules;
                            $class = $controller; // 这里是控制器名（类名）
                            $methods = $actions[$name][$method];
                            break 2; // 找到后直接跳出两层循环
                        }
                    }
                }
                if (empty($methods) || empty($class) || empty($module)) {
                    return self::back();
                }

                $cache = ['module' => $module, 'class' => $class, 'method' => $methods];
            }

            cache($cache_key, $cache);
        }
        return $cache;

    }

    /**
     * 获取获取当前请求的参数
     * @access public
     * @param int $type 类型 默认读取所有参数 1表示签名获取的参数，不包括url中的参数（?之前的）
     * @return array
     */
    public function param($type = 0): array
    {

        // 请求方式 GET POST PUT DELETE PATCH 5种方式
        $params = json_decode(file_get_contents('php://input'), true);
        if (empty($params)) {
            $params = $_POST;
        }

        // 这里处理下如果是地址栏携带的id参数
        $query = getRequestUrl();

        $length = count($query) - 1;

        // 去除 地址栏的s参数
        unset($_GET['s']);

        // 需要判断是否多个参数 /v1/resources?param1=1&param2=2
        if (strpos($query[$length], '?') !== false) {
            $params = array_merge($_GET, (array) $params);
        }

        if ($type == 0) {
            // 去除 ? 后面的参数
            $query[$length] = explode('?', $query[$length])[0];

            // 判断路由中间是否存在参数
            // 可能是  /v1/jobs/{ID1}/job_log/{ID2} 这种url应该是/v1/jobs/job_log 参数应该是 jobs_uuid = {ID1}， job_log_uuid = {ID2}
            foreach ($query as $key => $val) {
                if (($val == '' . intval($val) && intval($val)) || count(explode('-', $val)) > 3) {
                    // 是参数
                    $params[$query[$key - 1] . '_uuid'] = $val;
                }
            }

            // 不需要过滤参数的路由 直接返回
            if (in_array(getXphpUrl(), xphp_get_config('special', 'SPECIAL_ROUTE'))) {
                return $params ?? [];
            }
        }

        // 参数过滤
        return self::xssFilter($params, $type) ?? [];
    }

    /**
     * 参数过滤
     */
    static private function xssFilter($params = [], $type = 0)
    {
        foreach ($params as $key => $item) {
            if ($item === 0 && strlen((string) $item) == 1) {
                $params[$key] = $type == 1 ? 0 : $item;
                continue;
            }
            if (is_bool($item)) {
                $params[$key] = $item;
                continue;
            }
            if (in_array($item, ['true', 'false'])) {
                $params[$key] = !($item == 'false');
                continue;
            }
            // 整形的不处理
            if ($item == '' . intval($item)) {
                // 针对超大的数字 2的53次方，js会变，所以给转为string处理即可
                if (abs(intval($item)) > 9007199254740992) {
                    $params[$key] = (string) $item;
                } else {
                    $params[$key] = $type == 1 ? intval($item) : $item;
                }
                continue;
            }

            // $type = 1表示签名取的参数 不需要 xphp_xss_remove
            // 这里判断下，如果是 包含 script 的就不过滤
            if (!is_array($item) && is_string($key) && strpos($key, 'script') !== false) {
                $params[$key] = $item;
                continue;
            }
            $params[$key] = is_array($item) ? self::xssFilter($item, $type) : ($type ? $item : xphp_xss_remove($item));
        }

        return $params;
    }

    /**
     * 路由错误统一返回
     */
    static private function back()
    {

       return (new Response())->returns(['code' => -1, 'msg' => xphp_get_lang('WEB_ROUTE_ERROR') . htmlspecialchars($_SERVER['REQUEST_URI'])]);
    }

    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @param boolean $newInstance 是否重新实例化
     * @return object
     */
    public static function instance(string $class, string $method, bool $newInstance = false)
    {
        $identify = $class . $method;

        if ($newInstance || !isset(self::$_instance[$identify])) {
            if (class_exists($class)) {
                //判断类是否定义
                $o = new $class();
                if (!empty($method)) {
                    //判断方法是否为空
                    if (method_exists($o, $method)) {
                        //判断类中是否包含指定方法
                        self::$_instance[$class] = $o;

                        // 跳转到具体的方法里面并传递了请求参数过去
                        return call_user_func(array(&$o, $method));
                    } else {
                        return self::back();
                    }
                } else {
                    self::$_instance[$identify] = $o;
                }
            } else {
                return self::back();
            }
        }
        return self::$_instance[$identify];
    }
}