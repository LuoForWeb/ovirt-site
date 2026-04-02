<?php
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

    //平台描述
    public static $_pfdes = array();

    //虚拟机描述
    public static $_vmdes = array();

    //数据库描述
    public static $_dbdes = array();


    //语言包
    public static $_lang = array();

    public static $_user = array();

    //云代理服务商
    public static $_cloud = array();

    //评论
    public static $_comment = array();

    /**
     * 应用程序初始化
     */
	static public function start($flag = TRUE){
		// 读取配置文件
		self::$_config = require API_PATH.'xphp/conf/config.php';
		// 判读下配置文件是否是加密过
		if (self::isEncryptedArray(self::$_config)) {
		    // 对数组进行解密
            self::$_config = self::decryptArrayValues(self::$_config);
        }
		self::$_cloud = require_once API_PATH.'xphp/conf/cloud_vendor.php';
        self::$_comment = require API_PATH.'xphp/conf/user_comment.php';

        // 注册AUTOLOAD方法
        spl_autoload_register('Xphp::autoload');

        //初始化框架
        //注册工具类
        self::instance("Utils");


        //设置时区,和操作系统一致
        self::setTimezone();

        //特殊配置
        self::specialConfig();

        // cookie验证
        if (!self::checkSession()) {
            exit("cookie error:" . htmlspecialchars($_SERVER['HTTP_COOKIE']));
        }

        //安全验证
        //路由控制
	    if($flag){
	        //权限控制
	        self::permissionCheck();
            self::route();
        }
        //统一调用


	}

	/**
	* 校验会话信息是否有效
     */
	static public function checkSession(): bool
    {

        session_start();
        if (!empty($_SESSION['userUUID']) && !empty($_SESSION['BackupSystem'])) {
            // 登录状态下并且存在会话的session
            // 需要验证前端接口传来的 $_SERVER['HTTP_COOKIE'] 的值
            $cookie = $_SERVER['HTTP_COOKIE'];
            if (!empty($cookie)) {
                // 可能的构造是 pagelength=value;BackupSystem=value;other=value
                $cookie_arr = explode(';', $cookie);
                $cookie_arrs = '';
                foreach ($cookie_arr as $item) {
                    if (strpos($item, 'BackupSystem') !== false) {
                        $cookie_arrs = $item;
                        break;
                    }
                }
                if (!$cookie_arrs) {
                    return false;
                }
                $cookie_arr = explode('=', trim($cookie_arrs));
                if (count($cookie_arr)  == 2 && $cookie_arr[0] == 'BackupSystem' && $cookie_arr[1] == $_SESSION['BackupSystem']) {
                    return true;
                }
            }
            return false;
        }

        return true;
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

	static public function setTimezone(){
	    $cmd = "timedatectl |grep Timezone|awk '{print $2}'";
	    exec($cmd, $data);
	    if(!$data[0]){
	        $cmd = "timedatectl |grep Time\\ zone|awk '{print $3}'";
	        exec($cmd, $data);
	    }
	    date_default_timezone_set($data[0]);    		 	 //设置时区（系统本地）
	}

	static public function route(){
        $UIRequest = $_REQUEST;
        $module = $UIRequest['m'];
        $func = $UIRequest['f'];

        $params = array();
        if(array_key_exists('username', $UIRequest)){
            //处理单点登录
            $params['username'] = $UIRequest['username'];
            $params['password'] = $UIRequest['password'];
        }
        if(array_key_exists('p', $UIRequest)){
            $utils = self::$_instance['Utils'];
            $params = $utils->object_array(json_decode($UIRequest['p']));
        }
        //设置分页信息,只针对datatable
        if(array_key_exists('start', $UIRequest) && array_key_exists('length', $UIRequest)){
            $params['start'] = $UIRequest['start'];
            $params['length'] = $UIRequest['length'];
            $params['draw'] = $UIRequest['draw'];
        }
        //设置排序,只针对datatable
        if(array_key_exists('order', $UIRequest)){
            $params['sortColumn'] = intval($UIRequest['order'][0]['column']);
            //只允许这两个值，防止SQL注入
            $params['sortType'] = strtolower($UIRequest['order'][0]['dir']) == "asc" ? "asc" : "desc";
        }

        //xss不可用方法，这些方法不进行xss过滤。比如getVmGrainRecoveryFileDir，细粒度，如果PATH中有&等字符串过滤后会导致操作失败，之后还有需要排除的方法都可以放在这个数组中
        $xssDisabledFunArr = array(
            'getVmGrainRecoveryFileDir',
            'downloadGrainRecoveryDirCheck',
            'addGlobalStrategy',
            'editGlobalStrategy',
            'setVisualInfo',
            "getRecoveryNasDir",//nas恢复目录
            "getRecoverPathTree",//获取nas设备目录
            "getNasDir",//获取nas备份目录
            "getNasSonTree",//nas异步获取子目录
            "getBackupFileDir",//文件恢复目录
            "getRecoverPathTree",//获取代理目录
            "getFileDir",//获取文件备份目录
            "getFileDirSonTree",//异步获取文件子目录
            "sendVerifyEmail",  //发送数据验证报告
            "updatePatchList", //上传升级包升级
            "upgradeCheck", //执行升级包升级
            "addUser",      //添加用户
            "editUser",      //修改用户
            "createFsBackupJob", //创建文件相关的备份任务
            "editFsBackupJob", //修改文件相关的备份任务
        );
        //统一处理xss
        if(!in_array($func, $xssDisabledFunArr)){
            $params = self::xssFilter($params);
        }
        echo self::instance(self::$_config['ROUTE'][$module], $func, $params);
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
     * @return object|OpcodeHandler|Utils|OPHandler|Socket
     */
    static public function instance($class, $method='', $params='', $newInstance = FALSE) {
        $identify = $class.$method;
        if($newInstance || !isset(self::$_instance[$identify])) {
            if(class_exists($class)){  //判断类是否定义
                $o = new $class();
                if(!empty($method)){   //判断方法是否为空
                    if(method_exists($o,$method)){ //判断类中是否包含指定方法
                        self::$_instance[$class] = $o;
                        return call_user_func(array(&$o, $method), $params);
                    }else{
                        exit("Method parameter error:" . htmlspecialchars($method));
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
    static private function permissionCheck ()
    {

        $UIRequest = $_REQUEST;
        if(array_key_exists('m', $UIRequest)){
            $module = $UIRequest['m'];
        }else{
            exit("No corresponding module.");
        }

        if(array_key_exists('f', $UIRequest)){
            $func = $UIRequest['f'];
        }else{
            exit("No corresponding function.");
        }

        //后台调用WEB接口
        if(array_key_exists('k', $UIRequest)){
            $key = $UIRequest['k'];
            if($key != Xphp::$_config['API_MAGIC']){
                exit("No Permission. API Key Error.");
            }

            // 不需要校验权限的后台接口
            $noAuthArr = [
                '11-sendNotice', // 邮件
                '14-sendVerifyNotice', // 验证
                '9-downloadAgent', // 客户端下载
                '11-getTaskAlarmContent', // 三方自动推送内容 获取任务告警内容
                '11-getSystemAlarmContent', // 三方自动推送内容 获取系统告警内容
            ];
            if (!in_array($module.'-'.$func, $noAuthArr)) {
                exit("No Permission.");
            }
        }

        // 2023-05-06 注释 因为这个以前是登录页面调用的，现在登录页面改为内部调用，所以不对外直接开放
        /*if($module == array_search('AgentHandler', self::$_config['ROUTE']) && $func == "getDoloadAgentName"){
            //下载代理端
			// 读取语言包
        	self::$_lang = require_once LANG_PATH . Xphp::$_config['lang'] . Xphp::$_config['ext'];
            return true;
        }*/

        session_start();
        session_commit();

        $not_login_arr = [
             //'0-getConfig',                    // 如果是获取信息   登录页面的都给直接在登录时候密码过期返回了密码强度和长度的数据了
             '4-login',                    // 如果是登录
             '4-auth',                    // 如果是单点登录获取auth
            '4-oldpassAvailable',        // 如果是验证密码 登录之前检测密码是否可用
            '4-editPassword',            // 如果是修改密码
            '4-userInfoVerify',         // 如果是发送邮件  登录之前找回密码
            '4-resetPassWord',          // 如果是找回密码
            '4-getPassComplexity',      // 如果是通过邮件修改密码
            '8-downloadFile', // LIVECD下载
            '9-getDownloadLink',  // 下载客户端链接
        ];

        $check_auth = false;

        if (in_array($module.'-'.$func, $not_login_arr) || !empty($key)) {
            // 这些都不需要校验
            $check_auth = true;
        }

        //验证大屏模块、函数   0、18、38
        $visualScreen = array(
            '0-getScreenTime',
            '0-getDataSurvey',
            '18-writeRefreshLog',
            '38-getOverView',
            '38-getOtherView',
            '38-getConfigure',
            '38-getStatisticData',
            '38-getCurrentTaskAndWarning',
            '38-getSystemLisenceInfo',
            '38-getCurrentTaskList',
            '38-getNodeMonitor',
        );
        if($_SESSION['permissionVisualScreen'] && in_array($module.'-'.$func,$visualScreen)){
            $check_auth = true;
        }

        // 验证系统公共的 把之前首页的提出来
        $loginArr = [
            '0-getConfig',
            '0-getLisenceInfo',
            '4-getUserExtendInfo',
            '39-getDataCenterView',
            '39-getTaskStatusView',
            '39-getModuleAuth',
            '39-getSystemStorageData',
            '37-getNodeUUid',
            '11-getSurveyNoticeInfo',
            '4-getLoginHistory',
            '4-getUserSelfInfo',
            '39-getSystemMonitorData',
            '39-getNetworkChartData',
            '39-getProtectData',
        ];
        if (empty($_SESSION['userUuid']) && in_array($module.'-'.$func, $loginArr)) {
            // 必须要登陆
            exit("No Permission.");
        }

        if (!$check_auth) {
            // 这里面需要校验

            // if(!$_SESSION['userUUID'] && !$key && $UIRequest['m'] != 18 && $UIRequest['m'] != 8){  // 系统配置的走权限去  0 18 38 大屏需要用到的 m
            if (!$_SESSION['userUUID'] && empty($key)) {
                // 没有登录也不是单点登录
                exit("No Permission.");
            }

            // 上传升级包到指定目录/系统工具上传文件 需要校验是否登录
            if (!$_SESSION['userUUID'] && in_array($func, ['uploadPatch','UploadToSystem']) && $UIRequest['m'] == 8) {
                exit("No Permission.");
            }

            // 这里进行方法的权限校验
            $permissionFunction = $_SESSION['permissionFunction'];
            if (in_array($module.'-'.$func, $_SESSION['permissionFunctions']) && (empty($permissionFunction[$module]) || (!empty($permissionFunction[$module]) && !in_array($func, $permissionFunction[$module]) && !in_array('*', $permissionFunction[$module])))) {
                exit("No Permission.");
            }
        }

        $language = $_SESSION['language'];
        if (empty($_SESSION['language'])) {
            $language = Xphp::$_config['lang'];
        }

        self::$_user = array(
            "username" => $_SESSION['userName'],
            "useruuid" => $_SESSION['userUUID'],
            "usertype" => $_SESSION['userType'],
            "permission" => $_SESSION['permission'],
            "language" => $language,
        );
        if (!empty($key)) {
            //如果是API调用,赋值一个用户UUID
            self::$_user['useruuid'] = "a508b813-19c7-eb4e-d6fa-bb61b25a4de9";
        }
        // 读取语言包
        self::$_lang = require_once LANG_PATH.$language.self::$_config['ext'];

        // 读取错误码
        self::$_error = require_once API_PATH.'xphp/conf/error.php';

        // 读取平台操作描述
        self::$_pfdes = require_once APP_PATH.'platform/PFDescription.php';

        // 读取虚拟机操作描述
        self::$_vmdes = require_once APP_PATH.'vm/VmDescription.php';

        // 读取数据库操作描述
        self::$_dbdes = require_once APP_PATH.'dbprotect/DbDescription.php';

        //csrf验证,注意上面登录,单点登录,下载都不在验证范围内,但是要处理后台调用接口
        self::csrfCheck();
    }


    /**
     * csrf验证,这里只验证HTTP_REFERER
     */
    static private function csrfCheck(){
        $UIRequest = $_REQUEST;
        if(array_key_exists('k', $UIRequest)){
            $key = $UIRequest['k'];
            if($key == Xphp::$_config['API_MAGIC']){
                //如果是后台调用,不验证
                return true;
            }
        }

        //以下方法不验证
        $noCheckFunArr = array(
            "auth",    //单点登录
            "getThumbprintFile",    //下载指纹
            "downloadFile",         //下载文件
            "downLoadTaskLog",      //下载任务日志
            "exportExcel",          //导出计费报表
            "downloadAutoBakData",  //下载自动系统备份数据
            "downloadEngineFile",  //下载ovirt平台备份数据
            "downloadGrainRecoveryFile",    //下载细粒度恢复文件
            "downloadGrainRecoveryDir" ,     //下载细粒度恢复目录
			"downLoadPassFile",//下载跳过文件
			"getHostLogFile", //cdp下载主机日志
			"downloadHistory", // 下载升级历史日志

        );

        if(in_array($UIRequest['f'], $noCheckFunArr)){
            //如果是在不验证数组中,不验证
            return true;
        }

        //其他验证
        $httpOrigin = $_SERVER['HTTP_ORIGIN'];
        $httpHost = $_SERVER['HTTP_HOST'];

        $httpOriginArr = explode("//", $httpOrigin);

        if($httpOriginArr[1] != $httpHost){
            //检查https://192.168.8.10/?dbhost链接,以?分隔开,查看是否在权限范围内,不在的话,表示不是从自己的web来的.
            //当然,这里不能100%防止,用户可以写程序伪造,但是可以避免浏览器上操作
            exit("CSRF DENIED.");
        }
    }

    /**
     * 监控程序统一初始化
     */
    static private function monitorInit(){
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

        //注册工具类
        self::instance("Utils");
        //路由控制
        self::route();
        //统一调用
        $userHandler = Xphp::instance('UsersHandler');

        $language = $userHandler->getSystemLang();
        // 读取语言包
        self::$_lang = require_once LANG_PATH.$language.self::$_config['ext'];
    }

    /**
     * xss 过滤
     * @param unknown $params
     */
    static public function xssFilter($params){
        if (is_array($params)) {
            //如果是数组,循环处理每一项
            foreach ($params as $key => $val) {
                // 递归
                $params[$key] = self::xssFilter($val);
            }
        } else {
            //不是字符串或者是json直接返回
            if(!is_string($params) || is_array(json_decode($params, true))) return $params;

            $params = self::string_remove_xss($params);
            if (PHP_VERSION < '5.4.0') {
                $params = htmlspecialchars($params);
            } else {
                if (!defined('CHARSET') || (strtolower(CHARSET) == 'utf-8')) {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $params = htmlspecialchars($params, ENT_NOQUOTES, $charset);
            }
        }
        return $params;
    }

    /**
     * 移除特殊标签
     * @param unknown $html
     * @return mixed
     */
    static private function string_remove_xss($html) {
        $mn = preg_match_all("/\<.+\>/is", str_replace('</br>', '', str_replace('<br>', '', $html)), $matches);
        if ($mn) {
            exit(Xphp::instance('OPHandler')->muOpResult(false, Xphp::$_lang['WEB_PUBLIC_INPUT_TIPS'], Xphp::$_lang['WEB_PUBLIC_FORBIDDEN_INPUT_HTML_TAGS'], "warning"));
        }
        preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

        $searchs[] = '<';
        $replaces[] = '&lt;';
        $searchs[] = '>';
        $replaces[] = '&gt;';

        if ($ms[1]) {
            $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote';
            $ms[1] = array_unique($ms[1]);
            foreach ($ms[1] as $value) {
                $searchs[] = "&lt;".$value."&gt;";
                $value = str_replace(array('\\', '/*'), array('.', '/.'), $value);
                $skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
                    'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
                    'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
                    'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
                    'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
                    'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
                    'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
                    'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
                    'onsubmit','onunload','javascript','script','eval','behaviour','expression','style','class');
                $skipstr = implode('|', $skipkeys);
                $value = preg_replace(array("/($skipstr)/i"), '.', $value);
                $replaces[] = empty($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
            }
        }

        $html = str_replace($searchs, $replaces, $html);

        // sql注入
//        if (!get_magic_quotes_gpc()) // 判断magic_quotes_gpc是否为打开
//        {
//            $html = addslashes($html); // 进行magic_quotes_gpc没有打开的情况对提交数据的过滤
//        }

        return ($html); // 回车转换
    }

    /**
     * 统计监控初始化
     */
    static public function monitorStart(){
        self::monitorInit();
    	Xphp::instance('Monitor');
    }

    /**
     * 数据库CDP监控
     */
    static public function dbCDPMoniterStart(){
        self::monitorInit();
        Xphp::instance('DBCDPMonitor');
    }
    /**
     * 费用监控
     */
    static public function billingMonitorStart(){
        self::monitorInit();
        Xphp::instance('billingMonitor');
    }
    /**
     * 费用监控
     */
    static public function systembakMoniterStart(){
        self::monitorInit();
        Xphp::instance('SystemBakMonitor');
    }

    /**
     * 检查更新监控
     */
    static public function updateMoniterStart(){
        self::monitorInit();
        Xphp::instance('updateMonitor');
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