<?php
require_once XPHP_PATH.'utils/OPHandler.class.php';
/**
 * 框架引导类
 */
class Xphp{
    
    // 类映射
    private static $_map = array();
    
    // 实例化对象
    private static $_instance = array();
    
    // 系统配置
    public static $_config = array();
    
    //错误码
    public static $_error = array();
    
    // 语言包
    public static $_lang = array();
    
    public static $_user = array();
    
    //api类
    public static $_apiClass;
    
    //api类方法
    public static $_apiFunction;
    
    //api请求方法
    public static $_apiRequestMethod;
    
    //api版本
    public static $_apiVersion;
    
    
    /**
     * 应用程序初始化
     */
    static public function start(){
        
        //设置时区,和操作系统一致
        self::setTimezone();
        
        // 读取配置文件
        self::$_config = require_once API_PATH.'xphp/conf/config.php';
        // 判读下配置文件是否是加密过
        if (self::isEncryptedArray(self::$_config)) {
            // 对数组进行解密
            self::$_config = self::decryptArrayValues(self::$_config);
        }
        
        // 注册AUTOLOAD方法
        spl_autoload_register('Xphp::autoload');
        
        //初始化框架
        //注册工具类
        self::instance("Utils");
        //权限控制
        self::permissionCheck();
        
        //特殊配置
        self::specialConfig();
        
        //安全验证
        //路由控制
        self::route();
        //统一调用
    }
    
    /**
     * 特殊配置,主要用于第三方OEM版本
     * 所有配置文件均在根目录下special文件夹,包括配置文件,logo,图片等所有信息
     */
    static public function specialConfig(){
        $configFile = self::$_config['SPECIAL_DIR'] . self::$_config['SPECIAL_CONFIG'];
        if(file_exists($configFile)){
            $content = file_get_contents($configFile);
            $content = json_decode($content, true);
            self::setSpecialConfigRecursive(self::$_config, $content);
        }
    }
    
    /**
     * 合并两个数组
     * 递归合并数组B到A
     */
    static public function setSpecialConfigRecursive(&$arrA, $arrB){
        foreach ($arrB as $key => $value){
            if(is_array($value)){
                self::setSpecialConfigRecursive($arrA[$key], $value);
            }else{
                $arrA[$key] = $value;
            }
        }
    }
    
    /**
     * 应用程序初始化
     */
    static public function amqStart(){
        
        //设置时区,和操作系统一致
        self::setTimezone();
        
        // 读取配置文件
        self::$_config = require_once API_PATH.'xphp/conf/config.php';
        
        // 注册AUTOLOAD方法
        spl_autoload_register('Xphp::autoload');
        
        //初始化框架
        //注册工具类
        self::instance("Utils");
        //权限控制
        // 	    self::permissionCheck();
        //设置语言
        
        
        //安全验证
        //路由控制
        self::route();
        //统一调用
        
        Xphp::$_lang = require_once LANG_PATH.Xphp::$_config['lang'].Xphp::$_config['ext'];
        // 读取错误码
        self::$_error = require_once API_PATH.'xphp/conf/error.php';
        Xphp::instance('AMQ');
    }
    
    static public function setTimezone(){
        $cmd = "timedatectl |grep Timezone|awk '{print $2}'";
        exec($cmd, $data);
        if(!$data[0]){
            $cmd = "timedatectl |grep Time\\ zone|awk '{print $3}'";
            exec($cmd, $data);
        }
        date_default_timezone_set($data[0]);    		 	 //设置时区（系统本地）
    }
    
    /**
     * 转发到对应的类路由处理
     */
    static public function route(){
        //赋值HTTP 请求方法
        self::$_apiRequestMethod = $_SERVER['REQUEST_METHOD'];
        $params = json_decode(file_get_contents('php://input'), true);
        if("GET" == self::$_apiRequestMethod){
            $params = $_GET;
        }
        //检查begin和count类型
        $params['begin'] = intval($params['begin']);
        $params['count'] = intval($params['count']);
        echo self::instance(self::$_apiClass, 'route', $params);
    }
    
    /**
     * 类库自动加载
     * @param string $class
     * @return void
     */
    static public function autoload($class){
        // 检查是否存在映射
        if(isset(self::$_map[$class])) {
            include self::$_map[$class];
        }else{
            // 根据自动加载路径设置进行尝试搜索
            foreach (self::$_config['APP_AUTOLOAD_PATH'] as $path){
                if(file_exists(API_PATH.$path.$class.".class.php")){
                    // 如果加载类成功则返回
                    self::$_map[$class] = API_PATH.$path.$class.".class.php";
                    include_once self::$_map[$class];
                    return ;
                }
            }
        }
    }
    
    /**
     * 取得对象实例 支持调用类的静态方法
     * @param string $class 对象类名
     * @param string $method 类的静态方法名
     * @param string $params    参数
     * @param boolean $newInstance 是否重新实例化
     * @return object
     */
    static public function instance($class, $method='', $params='', $newInstance = FALSE) {
        $identify = $class.$method;
        if($newInstance || !isset(self::$_instance[$identify])) {
            if(class_exists($class)){
                $o = new $class();
                if(!empty($method)){
                    if(method_exists($o,$method)){
                        self::$_instance[$class] = $o;
                        return call_user_func(array(&$o, $method), $params);
                    }else{
                        exit("Method parameter error:".$method);
                    }
                }
                else{
                    self::$_instance[$identify] = $o;
                }
            }
        }
        return self::$_instance[$identify];
    }
    
    /**
     * 权限验证
     */
    static private function permissionCheck(){
        //         var_dump($_SERVER);
        //         var_dump($_REQUEST);
        //验证URI
        
        //         $this->checkUri();
        Xphp::checkUri();
        
        //验证HTTP_ACCEPT
        Xphp::checkAccept();
        
        //验证HTTP_AUTHORIZATION
        Xphp::checkAuthorization();
        
        return;
    }
    
    /**
     * 验证URI
     * uri合法格式
     * api/{module}/{params}/...
     */
    private static function checkUri(){
        $uri = strtolower($_SERVER['REQUEST_URI']);
        $uriArr = explode("/", $uri);
        $OPHandler = Xphp::instance("OPHandler");
        if(Xphp::$_config['API_CONFIG']['uriHeader'] != $uriArr[1]){
            //访问URL错误
            return $OPHandler->apiResponse(false, 'API_CODE_HEADER_ERROR_404');
            //             return http_response_code(404);
        }
        $apiModule = Xphp::$_config['API'];
        $uriArr[2] = Xphp::filterGetUrl($uriArr[2]);
        if(!$apiModule[$uriArr[2]]){
            //模块错误
            return $OPHandler->apiResponse(false, 'API_CODE_HEADER_ERROR_405');
            //             return http_response_code(405);
        }
        //通过验证,给调用类和类方法赋值
        self::$_apiClass = $apiModule[$uriArr[2]];
        $uri = Xphp::filterGetUrl($uri);
        self::$_apiFunction = substr($uri, 4);
        return true;
    }
    
    /**
     * 验证Accept
     * "application/vnd.vinchin-v3+json"
     */
    private static function checkAccept(){
        $accept = strtolower($_SERVER['HTTP_ACCEPT']);
        $acceptArr = explode('-', $accept);
        $OPHandler = Xphp::instance("OPHandler");
        if(Xphp::$_config['API_CONFIG']['acceptHeader'] != $acceptArr[0]){
            return $OPHandler->apiResponse(false, 'API_CODE_HEADER_ERROR_406');
            //             return http_response_code(406);
        }
        $acceptArr = explode('+', $accept);
        if(Xphp::$_config['API_CONFIG']['acceptDataType'] != $acceptArr[1]){
            return $OPHandler->apiResponse(false, 'API_CODE_HEADER_ERROR_406');
            //             return http_response_code(406);
        }
        //通过验证,给版本号赋值
        $acceptArr = explode('-', $acceptArr[0]);
        self::$_apiVersion = $acceptArr[1];
        //设置消息返回头Content-Type
        header("Content-Type: " . $accept . ";" . Xphp::$_config['API_CONFIG']['charset']);
        return true;
    }
    
    /**
     * 验证Authorization
     */
    private static function checkAuthorization(){
        $uri = strtolower($_SERVER['REQUEST_URI']);
        $uriArr = explode("/", $uri);
        $uriArr[2] = Xphp::filterGetUrl($uriArr[2]);
        
        
        if("access_token" == $uriArr[2]){
            //如果是获取access token就不做检查
            return true;
        }
        
        $authorization = $_SERVER['HTTP_AUTHORIZATION'];
        $checkResult = self::instance('APIAccessTokenHandler', 'checkAccessToken', $authorization);
        
        $OPHandler = Xphp::instance("OPHandler");
        if(!$checkResult){
            //验证失败
            return $OPHandler->apiResponse(false, 'API_CODE_HEADER_ERROR_401');
            //             return http_response_code(401);
        }
        
        return true;
    }
    
    /**
     * 过滤URL
     * 将如:access_token?user_name=123&password=2454
     * 问号后面的过滤掉
     * @param string $url
     */
    private static function filterGetUrl($uri){
        if(false === strpos($uri, '?')){
            //如果没有找到?,直接返回原来的
            return $uri;
        }
        $urlArr = explode('?', $uri);
        return $urlArr[0];
    }

    // 判断数组是否加密
    static public function isEncryptedArray($array) {
        if ($array['ext'] == '.php') {
            return false;
        }
        return true;
    }

    /**
     * 解密
     */
    static public function decryptArrayValues($array) {
        $key = self::createKeyIv(0);
        $iv = self::createKeyIv(1);
        foreach ($array as $k => $value) {
            if (is_array($value)) {
                $array[$k] = self::decryptArrayValues($value); // 修正递归调用时的参数
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

    // 获取秘钥
    static public function createKeyIv($type = 0) {
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