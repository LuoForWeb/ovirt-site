<?php

// phpcs:ignoreFile -- 框架引导类 不需要校验
use xphp\Request;

/**
 * note          框架引导类
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:28
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Xphp
{

    // 类映射
    private static $_map = [];
    // 公共函数
    private static $_func = []; // 核心
    private static $_function = []; // 公共库的
    private static $_functions = []; // 版本库里面的

    //语言包  这个是兼容老版本的语言包和错误码读取
    public static $_lang = [];
    public static $_error = [];

    /**
     * 应用程序初始化
     * @params boolean $flag 携带了就表示只是加载配置文件
     */
    static public function start($flag = false)
    {

        //初始化框架
        self::init();

        // 注册AUTOLOAD方法
        spl_autoload_register('Xphp::autoload');

        //设置时区,和操作系统一致
        self::setTimezone();

        $request_url = explode('?', $_SERVER['REQUEST_URI']);
        $old_path = xphp_get_oldpath();
        if ($old_path && $request_url[0] != '/api/v1/login') {
            // 读取语言包
            $language = xphp_get_language_type();
            $key = $language . $old_path . '-lang';
            self::$_lang = cache($key);
            if (empty(self::$_lang)) {
                self::$_lang = require_once $old_path . 'lang/' . $language . '.php';
                cache($key, self::$_lang);
            }

            // 读取错误码
            $key = $old_path . $language . '-error_code';
            self::$_error = cache($key);
            if (empty(self::$_error)) {
                self::$_error = require_once $old_path . 'api/xphp/conf/error.php';
                cache($key, self::$_error);
            }
        }

        // 请求解析
        (new Request())::start($flag);
    }

    /**
     * 框架初始化需要加载的的一些东西  这里需要加载配置文件，函数文件，语言包，错误处理类，日志处理等
     */
    static private function init(): void
    {

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',          // 或指定域名
            'secure'   => true,        // ← HTTPS 必须为 true
            'httponly' => true,        // ← 防止 XSS 窃取
            'samesite' => 'Lax'        // ← 推荐，防 CSRF
        ]);

        // 启动session
        session_start();
        // 释放锁
        session_write_close();

        // 加载函数 先加载核心的函数文件
        self::autoloadFile(XPHP_PATH . 'helper', self::$_func);

        //加载公共的函数文件
        self::autoloadFile(API_PATH . 'function', self::$_function);

        // 获取当前请求的版本
        $version = getXphpVersion();

        // 加载版本库里面的公共函数文件
        self::autoloadFile(APP_PATH . $version . '/function', self::$_functions);
    }

    static private function setTimezone(): void
    {

        /*if (getEnvs('APP_DEBUG')) {
            // 开发模式下不进行时区设置
            return;
        }*/

        $cmd = "timedatectl |grep Timezone|awk '{print $2}'";

        exec($cmd, $data);

        if (!$data[0]) {
            $cmd = "timedatectl |grep Time\\ zone|awk '{print $3}'";
            exec($cmd, $data);
        }
        if(!$data[0]){
            $cmd = "date +%Z";
            exec($cmd,$data);
        }

        // 设置时区（系统本地）
        date_default_timezone_set($data[0] ?? 'PRC');
    }

    /**
     * 类库自动加载
     * @param string $class
     * @return void
     */
    static private function autoload(string $class): void
    {
        // 检查是否存在映射
        if (isset(self::$_map[$class])) {
            include self::$_map[$class];
        } else {
            $temp_arr = [];

            // 还需要引入xphp里面的一些定义
            $temp_arr[] = ['dir' => XPHP_PATH . 'libs', 'pre' => 'xphp'];

            //查找app里面相关的类的路径
            // 获取当前请求的版本
            $version = getXphpVersion();

            $temp_arr[] = ['dir' => APP_PATH . $version . '/', 'pre' => 'app\\' . $version];

            foreach ($temp_arr as $item) {
                $arr_file = [];
                if (function_exists('xphp_get_cache') && !empty(xphp_get_cache($item['dir'], true))) {
                    $arr_file = xphp_get_cache($item['dir'], true);
                } else {
                    self::findFile($arr_file, $item['dir']);
                    function_exists('xphp_set_cache') && !getEnvs() && xphp_set_cache($item['dir'], $arr_file, true);
                }

                // 根据自动加载路径设置进行尝试搜索
                foreach ($arr_file as $path) {
                    $path_name_arr = explode('.', $path);
                    $path_name = str_replace('/', '\\', $path_name_arr[0]);

                    if ($item['pre'] . $path_name == $class) {
                        // 如果加载类成功则返回
                        self::$_map[$class] = $item['dir'] . $path;

                        include_once self::$_map[$class];
                        return;
                    }
                }
            }
        }
    }

    /**
     * 文件手动加载
     * @param string $directory 根目录
     * @param array $array 映射存储数组
     * @return void
     */
    static private function autoloadFile(string $directory, array $array): void
    {
        // 检查是否存在映射
        if (empty($array)) {
            //查找相关的类的路径
            $arr_file = [];
            if (function_exists('xphp_get_cache') && !empty(xphp_get_cache($directory, true)) && !getEnvs()) {
                $arr_file = xphp_get_cache($directory, true);
            } else {
                self::findFile($arr_file, $directory);
                function_exists('xphp_set_cache') && xphp_set_cache($directory, $arr_file, true);
            }

            // 根据自动加载路径设置进行尝试搜索
            foreach ($arr_file as $path) {
                $array[$path] = $directory . $path;

                include_once $array[$path];
            }
        }
    }

    /**
     * 自动查找文件
     * @param array $arr_file 返回数组
     * @param string $directory 查找的路径
     * @param string $dir_name 返回的文件路径添加前缀
     */
    static private function findFile(array &$arr_file, string $directory, string $dir_name = ''): void
    {

        if (!is_dir($directory)) {
            return;
        }

        $mydir = dir($directory);
        while ($file = $mydir->read()) {
            if ((is_dir("$directory/$file")) and ($file != ".") and ($file != "..")) {
                self::findFile($arr_file, "$directory/$file", "$dir_name/$file");
            } elseif (($file != ".") and ($file != "..")) {
                $file_parts = explode(".", $file);
                $ext = end($file_parts);
                if ($ext == 'php') {
                    $arr_file[] = "$dir_name/$file";
                }
            }
        }
        $mydir->close();
    }
}
