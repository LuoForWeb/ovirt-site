<?php
// phpcs:ignoreFile -- 框架类
/**
 * 日志文件
 */

namespace xphp\log;


/**
 * Class Log
 * @package xphp
 *
 */
class Log
{

    // 日志信息
    protected static $log = [];
    // 配置参数
    protected static $config = [];
    // 日志类型
    protected static $type = ['log', 'error', 'info', 'sql', 'notice', 'alert', 'debug'];
    // 日志写入驱动
    protected static $driver;


    /**
     * 日志初始化
     * @param array $config
     * @throws \Exception
     */
    public static function init($config = [])
    {
        $type = isset($config['type']) ? $config['type'] : 'File';
        $class = false !== strpos($type, '\\') ? $type : '\\xphp\\log\\driver\\' . ucwords($type);
        self::$config = $config;
        unset($config['type']);
        if (class_exists($class)) {

            self::$driver = new $class($config);
        } else {
            throw new \Exception('class not exists:' . $class, $class);
        }
        // 记录初始化信息
       // getEnvs('APP_DEBUG') && Log::record('[ LOG ] INIT ' . $type, 'info');
    }


    /**
     * 清空日志信息
     * @return void
     */
    public static function clear()
    {
        self::$log = [];
    }

    /**
     * 实时写入日志信息 并支持行为
     * @param mixed $msg 调试信息
     * @param string $type 信息类型
     * @param bool $force 是否强制写入
     * @return bool
     * @throws \Exception
     */
    public static function write($msg, $type = 'log', $force = false)
    {
        // 封装日志信息
        if (true === $force || empty(self::$config['level'])) {
            $log[$type][] = $msg;
        } elseif (in_array($type, self::$config['level'])) {
            $log[$type][] = $msg;
        } else {
            return false;
        }

        if (is_null(self::$driver)) {
            $config = config('log');
            self::init($config['channels'][$config['default']]);
        }
        // 写入日志
        return self::$driver->save($log, true);
    }

    /**
     * 静态调用
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        if (in_array($method, self::$type)) {
            array_push($args, $method);
            return call_user_func_array('\\xphp\\Log::record', $args);
        }
    }

}
