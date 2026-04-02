<?php
/*********************************************************************************
 *  工具方法库
***********************************************************************************/
include_once XPHP_PATH . 'libs/phpseclib/autoload.php';
use phpseclib3\Crypt\Rijndael as Crypt_Rijndael;
class Utils {
	/**
	 * 方法库-字节转换-转换成MB格式等
	 * @param int $num  数值
	 * @param $valueFlag   是否一定要获取数值,默认为否,为TRUE的时候会返回如0B这样的数据
	 * @return string
	 */
	public function calSize($num, $valueFlag = false) {
        $minusFlag = $num < 0;
        if ($minusFlag) {
            $num = abs($num);
        }
	    $num = floatval($num);
	    if(0 == $num && !$valueFlag){
	        return Xphp::$_config['NULLSPACE'];
	    }
		$type = array( "B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB");
		$j = 0;
		while($num >= 1024) {
    		if( $j >= 11 ) return $num.$type[$j];
    		$num = $num / 1024;
    		$j++;
   		}
   		$num = round($num, 2);
        return $minusFlag ? '-'.$num.$type[$j] : $num.$type[$j];
	}

	/**
	 * 方法库-字节转换-转换成MB格式等 按1000换算
	 * @param int $num  数值
	 * @param $valueFlag   是否一定要获取数值,默认为否,为TRUE的时候会返回如0B这样的数据
	 * @return string
	 */
	public function calSize1000($num, $valueFlag = false) {
		$num = floatval($num);
		if(0 == $num && !$valueFlag){
			return Xphp::$_config['NULLSPACE'];
		}
		$type = array( "B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB");
		$j = 0;
		while($num >= 1000) {
			if( $j >= 11 ) return $num.$type[$j];
			$num = $num / 1000;
			$j++;
		}
		$num = round($num, 2);
		return $num.' '.$type[$j];
	}


	/**
	 * 方法库-字节转换-转换成MB格式等
	 * @param int $num  数值
	 * @param $valueFlag   是否一定要获取数值,默认为否,为TRUE的时候会返回如0B这样的数据
	 * @return array
	 */
	public function calSizeToValueAndUnit($num, $valueFlag = false){
	    $info = array(
	        'value' => Xphp::$_config['NULLSPACE'],
	        'unit' => '',
	    );
	    $num = floatval($num);
	    if(0 == $num && !$valueFlag){
	        return $info;
	    }
	    $type = array( "B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB");
	    $j = 0;
	    while($num >= 1024) {
	        if( $j >= 11 ) return $num.$type[$j];
	        $num = $num / 1024;
	        $j++;
	    }
	    $num = round($num, 2);
	    $info['value'] = $num;
	    $info['unit'] = $type[$j];
	    return $info;
	}

	/**
	 * 计算速度-转换成MB/s格式等
	 * @param int $num
	 * @return string
	 */
	public function calSpeed($num){
	    $speed = $this->calSize($num);
	    if(0 == $speed){
	        return $speed;
	    }
	    return $speed . "/s";
	}

	/**
	 * 计算百分比 返回百分比 23.45%
	 * @param int $total   分母
	 * @param int $value   分子
	 * @return number|string
	 */
	public function calPercent($total, $value){
	    $total = floatval($total);
	    $value = floatval($value);
	    if(0 == $total) return 0 . "%";
	    if($total <= $value) return '100%';
	    $percent = floor(($value * 100 / $total)*100)/100 . "%";
	    return $percent;
	}

	/**
	 * 计算百分比 返回数值23.45
	 * @param int $total   分母
	 * @param int $value   分子
	 * @return number|string
	 */
	public function calPercentValue($total, $value){
	    $total = floatval($total);
	    $value = floatval($value);
	    if(0 == $total) return 0 ;
	    if($total <= $value) return '100';
	    $percent = floor(($value * 100 / $total)*100)/100;
	    return $percent;
	}

    /**
     * object to array
     * @param object $array
     * @return array
     */
    public function object_array($array)
    {
        if(is_object($array))
        {
            $array = (array)$array;
        }
        if(is_array($array))
        {
            foreach($array as $key=>$value)
            {
                $array[$key] = $this->object_array($value);
            }
        }
        return $array;
    }

    /**
     * uuid生成器
     * @return string
     */
    public function uuid(){
        if (function_exists('com_create_guid')){
            $uuid = com_create_guid();
        }else{
            mt_srand((int)((double)microtime() * 10000));
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        }
        return strtolower(substr($uuid, 1, -1));
    }

    /**
     * 得到用户类型的描述        操作员/审计员/管理员/超级管理员
     * @param int $usertype
     */
    public function getUserTypeDes($usertype){
        $User = Xphp::$_config['USERTYPE'];
        switch (intval($usertype)){
            case $User['operator']:
                $typeDes = Xphp::$_lang['WEB_UTILS_USERTYPE_OPERATOR'];
                break;
            case $User['auditor']:
                $typeDes = Xphp::$_lang['WEB_UTILS_USERTYPE_AUDITOR'];
                break;
            case $User['manager']:
                $typeDes = Xphp::$_lang['WEB_UTILS_USERTYPE_MANAGER'];
                break;
            case $User['administrator']:
                $typeDes = Xphp::$_lang['WEB_UTILS_USERTYPE_ADMINISTRATOR'];
                break;
            default:
                $typeDes = Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'];
        }
        return $typeDes;
    }

    /**
     * 转换时间戳为 "3小时前/后"这种格式
     * @param unknown $timestamp
     * @return string
     */
    public function formatDate($timestamp){
        if($timestamp <= 0) return ' ';
        $t = time() - $timestamp;
        $des = Xphp::$_lang['WEB_UTILS_AGO'];
        if($t < 0){
            $des = Xphp::$_lang['WEB_UTILS_AFTER'];
        }
        $t = abs($t);
        $f=array(
            '31536000' => Xphp::$_lang['WEB_UTILS_YEAR'],
            '2592000' => Xphp::$_lang['WEB_UTILS_MONTH'],
            '604800' => Xphp::$_lang['WEB_UTILS_WEEK'],
            '86400' => Xphp::$_lang['WEB_UTILS_DAY'],
            '3600' => Xphp::$_lang['WEB_UTILS_HOUR'],
            '60' => Xphp::$_lang['WEB_UTILS_MINUTE'],
            '1' => Xphp::$_lang['WEB_UTILS_SECOND']
        );
        foreach ($f as $k=>$v)    {
            if (0 != $c=floor($t/(int)$k)) {
                return $c . $v. $des;
            }
        }
    }

    /**
     * 根据错误码,得到错误描述信息
     * @param int $errorInfo
     * @return string
     */
    public function getErrorDes($errorInfo){
        $error = require CONF_PATH.'error.php';
        if(is_numeric($errorInfo)){
            $errorCodeName = $error['errorCode'][$errorInfo];
            $errorCodeDes = $error['errorCodeDes'][$errorCodeName];
        }else{
            $errorCodeDes = $error['errorCodeDes'][$errorInfo];
        }
        //如果获取为空,直接返回错误参数
        $errorCodeDes = empty($errorCodeDes) ? $errorInfo : $errorCodeDes;
        return $errorCodeDes;
    }

    /**
     * 根据错误的定义得到错误Num号
     * @param string $errorValue
     * @return int
     */
    public function getErrorNum($errorValue){
        $error = require CONF_PATH.'error.php';
        $num = array_search($errorValue, $error['errorCode']);
        if($num === FALSE){
            return 0;
        }else{
            return $num;
        }
    }

    /**
     * 得到在线离线描述
     * @param int $onlineFlag
     * @return string
     */
    public function getOnlineDes($onlineFlag){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $des = $ptDes['ONLINEDES'][$onlineFlag];
        return $des;
    }

    /**
     * 得到正常或异常的描述
     * @param int $errorCode
     * @return string
     */
    public function getStatusDes($errorCode){
        $errorCode = intval($errorCode);
        if(0 == $errorCode){
            $des = Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'];
        }else{
            $des = Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'];
        }
        return $des;
    }

    /**
     * 根据用户UUID获取用户名
     * @param string $useruuid
     * @return string
     */
    public function getUsername($useruuid){
        $opHandler = Xphp::instance('OPHandler');
        $sql = "select user_name from bd_user where user_uuid = ?";
        $username = $opHandler->dbSelect($sql, array($useruuid));
        if($username){
            return $username[0]['user_name'];
        }else{
            $opHandler->writeLog('PF_USER_GET_USERNAME_ERROR', Xphp::$_config['LOG_INFO']['WARNING']);
            return 'unknown';
        }
    }

    /**
     * 格式化时分秒
     * 9:01:25补齐为09:01:25
     * @param string $dayTime
     */
    public function formartTime($dayTime){
        if(empty($dayTime)){
            return $dayTime;
        }
        $eachValue = explode(":", $dayTime);
        if(intval($eachValue[0]) < 10){
            $dayTime = "0".intval($eachValue[0]) . ":" . $eachValue[1] . ":" . $eachValue[2];
        }
        return $dayTime;
    }

    /**
     *      把秒数转换为时分秒的格式
     *      @param Int $times 时间，单位 秒
     *      @return String
     */
    public function secToTime($times){
        $result = '00:00:00';
        if ($times>0) {
            $hour = $this->formartTimeString(floor($times/3600));
            $minute = $this->formartTimeString(floor(($times-3600 * $hour)/60));
            $second = $this->formartTimeString(floor((($times-3600 * $hour) - 60 * $minute) % 60));
            $result = $hour . ':' . $minute . ':' . $second;
        }
        return $result;
    }

    /**
     *      把秒数转换为年天时分秒的格式
     *      @param Int $times 时间，单位 秒
     *      @return String
     */
    public function secToDayTime($times){
        $result = Xphp::$_config['TIMESPACE'];
        if ($times>0) {
            $dayValue = floor($times/3600/24);
            $day = $this->formartTimeString($dayValue);
//             $year = 0;
//             if($day >= 365){
//                 $year = intval($day/365);
//                 $day = $day % 365;
//             }

            $hour = $this->formartTimeString(floor($times/3600%24));
            $minute = $this->formartTimeString(floor($times/60%60));
            $second = $this->formartTimeString(floor($times%60));
//             if($year != 0){
//                 $result = $year .Xphp::$_lang['WEB_UTILS_YEAR']. $day . Xphp::$_lang['WEB_UTILS_DAY'].
//                 $hour .Xphp::$_lang['WEB_UTILS_HOUR']. $minute . Xphp::$_lang['WEB_UTILS_MINUTE'].$second .Xphp::$_lang['WEB_UTILS_SECOND'];
//             }else{
            if($dayValue != 0){
                $result = $day . Xphp::$_lang['WEB_UTILS_DAY'].$hour .":". $minute . ":" .$second;
            }else{
                $result = $hour .":". $minute . ":" .$second;
            }
//             }
        }
        return $result;
    }


    /**
     * 把时分秒格式转换为秒的格式
     * @param string $timeStr
     * @return number
     */
    public function timeToSec($timeStr){
        $timeArr = explode(":", $timeStr);
        return intval($timeArr[0]) * 3600 + intval($timeArr[1]) * 60 + intval($timeArr[2]);
    }

    /**
     * 转化时间格式 补齐  0-9 补齐00-09
     */
    public function formartTimeString($str){
        if($str < 10){
            return "0" . $str;
        }
        return $str;
    }


    /**
     * 获取客户端IP
     */
    public function getClientIP()
    {
        $ip = '';

        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
            // 使用普通匿名代理服务器, 这个值类似："203.98.182.163, 203.98.182.163, 203.129.72.215"
            $ip = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
        }else{
            $ip = "Unknow";
        }

        return $ip;
    }

    /**
     * 发送邮件(通过互联网发送,暂时支持有网络条件)
     * @param string $toEmail   发送到邮件的地址
     * @param string $subject   主题
     * @param string $body      内容
     * @return bool  发送成功true/发送失败false
     */
    public function sendEmail($toEmail, $subject, $body){
        $email = Xphp::instance('Email');
        $confEmail = Xphp::$_config['EMAIL'];
        $email->config($confEmail['smpt_host'], $confEmail['port'], $confEmail['authentication'],
                       $confEmail['from_email'], $confEmail['from_email_pass']);
        return $email->sendmail($toEmail, $confEmail['from_email'], $subject, $body, $confEmail['type']);
    }

    /**
     * 转换标志到bool
     * @param int $flag
     * @return boolean
     */
    public function parseFlagToBool($flag){
        $flag = intval($flag);
        if($flag == Xphp::$_config['FLAG']['SET']){
            return true;
        }
        return false;
    }

    /**
     * 转化bool到标志
     * @param boolean $boolValue
     * @return int
     */
    public function parseBoolToFlag($boolValue){
        return $boolValue ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'];
    }

    /**
     * 搜索指定的数组的某列,返回搜索到的数组
     * @param array $searchArray    需要搜索的数组(二维数组)
     * @param int $searchColumn     需要搜索的列
     * @param string $searchValue   需要搜索的字符串
     * @return array
     */
    public function arraySearch($searchArray, $searchColumn, $searchValue){
        if(empty($searchValue)) return $searchArray;
        $resultArray = array();
        foreach ($searchArray as $eachArray){
            if(stristr($eachArray[$searchColumn], $searchValue) !== FALSE) {
                $resultArray[] = $eachArray;
            }
        }
        return $resultArray;
    }

    /**
     * 给指定的数组进行排序,返回排序后指定长度的数组
     * @param array $sortArray  需要排序的数组(二维数组)
     * @param int $sortColumn   需要排序的列
     * @param string $sortType  排序类型    asc/desc
     * @param int $start        开始位置
     * @param int $length       返回长度
     * @return array
     */
    public function arraySort($sortArray, $sortColumn, $sortType, $start, $length){
        if('desc' == $sortType){
            $sortType = SORT_DESC;
        }else{
            $sortType = SORT_ASC;
        }
        if(-1 == $length){
            $length = null;     //显示全部
        }
        $column = array();
        foreach ($sortArray as $arr){
            $column[] = $arr[$sortColumn];
        }
        $column = array_map('strtolower', $column); //不区分大小写
        array_multisort($column, $sortType, $sortArray);
        $sortArray = array_slice($sortArray, $start, $length);
        return $sortArray;
    }

    /**
     * 二维数组去重
     * @param array $array  需要去重的二维数组
     * @param string $key   按什么字段去重
     * @return array        去重后的数组
     */
    public function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    /**
     * 加密字符串 php5.4
     * AES-256 CBC
     * @param string $plaintext
     * @return string base64字符串
     */
    public function encrype54($plaintext){
        # 密钥是 16 进制字符串格式
        $key = pack('H*', Xphp::$_config['SECRET_KEY']);
        # 显示 AES-128, 192, 256 对应的密钥长度：
        #16，24，32 字节。
        # 为 CBC 模式创建随机的初始向量
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        # 创建和 AES 兼容的密文（Rijndael 分组大小 = 256）
        # 仅适用于编码后的输入不是以 00h 结尾的
        # （因为默认是使用 0 来补齐数据）
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
        # 将初始向量附加在密文之后，以供解密时使用
        $ciphertext = $iv . $ciphertext;
        # 对密文进行 base64 编码
        $ciphertext_base64 = base64_encode($ciphertext);
        return $ciphertext_base64;
    }

    /**
     * 解密字符串 php5.4
     * AES-256 CBC
     * @param string $ciphertext
     * @return string
     */
    public function decrypt54($ciphertext){
        # 密钥是 16 进制字符串格式
        $key = pack('H*', Xphp::$_config['SECRET_KEY']);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        # --- 解密 ---
        $ciphertext_dec = base64_decode($ciphertext);
        # 初始向量大小，可以通过 mcrypt_get_iv_size() 来获得
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);
        # 获取除初始向量外的密文
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);
        # 可能需要从明文末尾移除 0
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
        return trim($plaintext_dec);
    }

    /**
     * 加密字符串
     * AES-256 CBC
     * @param string $plaintext
     * @return string base64字符串
     */
    public function encrype($plaintext){
        if (!(PHP_VERSION_ID >= 70000)) {
            return $this->encrype54($plaintext);
        }
        if(empty($plaintext)) return '';
        $key = pack('H*', Xphp::$_config['SECRET_KEY']);
        $cipher = "AES-256-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv1 = openssl_random_pseudo_bytes($ivlen);
        $iv2 = openssl_random_pseudo_bytes($ivlen);
        $iv = $iv1 . $iv2;  //凑齐32位,为了兼容之前的版本
        $cipher = new Crypt_Rijndael('cbc');
        $cipher->setBlockLength(256);
        // keys are null-padded to the closest valid size
        // longer than the longest key and it's truncated
        $cipher->setKeyLength(256);
        $cipher->setKey($key);
        // the IV defaults to all-NULLs if not explicitly defined
        $cipher->setIV($iv);
        $cipher->disablePadding();
        $length = strlen($plaintext);
        $pad = 32 - ($length % 32);
        $plaintext = str_pad($plaintext, $length + $pad, chr(0));
        return base64_encode($iv . $cipher->encrypt($plaintext));
    }

    /**
     * 解密字符串
     * AES-256 CBC
     * @param string $ciphertext
     * @return string
     */
    public function decrypt($ciphertext){
        if (!(PHP_VERSION_ID >= 70000)) {
            return $this->decrypt54($ciphertext);
        }
        if(empty($ciphertext)) return '';
        $key = pack('H*', Xphp::$_config['SECRET_KEY']);
        $ciphertext_dec = base64_decode($ciphertext);
        $ivlen = 32;
        # 初始向量大小，可以通过 mcrypt_get_iv_size() 来获得
        $iv_dec = substr($ciphertext_dec, 0, $ivlen);
        # 获取除初始向量外的密文
        $ciphertext_dec = substr($ciphertext_dec, $ivlen);
        $cipher = new Crypt_Rijndael('cbc'); // could use CRYPT_RIJNDAEL_MODE_CBC
        $cipher->setBlockLength(256);
        // keys are null-padded to the closest valid size
        // longer than the longest key and it's truncated
        $cipher->setKeyLength(256);
        $cipher->setKey($key);
        // the IV defaults to all-NULLs if not explicitly defined
        $cipher->setIV($iv_dec);
        $cipher->disablePadding();
        return trim($cipher->decrypt($ciphertext_dec));
    }

    /**
     * 解密平台加密
     * @param string $ciphertext
     */
    public function ptPassDecrypt($ciphertext){
        $key = base64_decode(Xphp::$_config['SECRET_KEY_PT']);
        $new_key = "";
        if (strlen($key) >= 32){
            $new_key = substr($key, 0, 32);
        }else{
            $remaind_len = 32 - strlen($key);
            $new_key = $key;
            for ($i = 0, $j = strlen($key); $j < 32; $i++, $j++){
                $new_key[$j] = ~$new_key[$i];
            }
        }
        $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
        $ciphertext = !empty($ciphertext)?$ciphertext:"";
        $plaintext_dec = openssl_decrypt(base64_decode($ciphertext), "AES-256-CBC", $new_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);

        //去掉补位
        $pad = ord($plaintext_dec[strlen($plaintext_dec)-1]);
        if ($pad > strlen($plaintext_dec)) return null;
        if (strspn($plaintext_dec, chr($pad), strlen($plaintext_dec) - $pad) != $pad) return null;
        if($pad){
            $plaintext_dec = substr($plaintext_dec, 0, -1 * $pad);
        }
        return trim($plaintext_dec);
    }

    /**
     * 平台加密字符串
     * AES-256 CBC
     * @param string $plaintext
     * @return string base64字符串
     */
    public function ptPassEncrypt($plaintext){
        $BLOCK_SIZE = 16;  //固定块大小byte
        $key = base64_decode(Xphp::$_config['SECRET_KEY_PT']);
        $new_key = "";
        if (strlen($key) >= 32){
            $new_key = substr($key, 0, 32);
        }else{
            $remaind_len = 32 - strlen($key);
            $new_key = $key;
            for ($i = 0, $j = strlen($key); $j < 32; $i++, $j++){
                $new_key[$j] = ~$new_key[$i];
            }
        }

        $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

        //计算传入字符串填充补齐块大小 --16byte
        if(strlen($plaintext) % $BLOCK_SIZE != 0){
            //不能被固定块大小整除
            $plaintSize = $BLOCK_SIZE - strlen($plaintext) % $BLOCK_SIZE;

        }else{
            //整除
            $plaintSize = $BLOCK_SIZE;
        }

        //填充追加补齐
        for($i = 0; $i < $plaintSize; $i++){
            $plaintext .= chr($plaintSize);

        }

        //加密
        $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $new_key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv);

        # 对密文进行 base64 编码
        $ciphertext_base64 = base64_encode($ciphertext);
        return $ciphertext_base64;
    }

    /**
     * 解密JS通过RSA算法加密的字符串
     * JS通过公钥加密，PHP端通过私钥解密
     * @param unknown $plaintext
     */
    public function decryptJsRsa($plaintext){
        $decrypted = "";
        $privateKey = file_get_contents(CONF_PATH . 'private_key.pem');
        openssl_private_decrypt(base64_decode($plaintext), $decrypted, $privateKey);
        return $decrypted;
    }

    //封装导出excel函数
    public function my_export($expTitle, $expCellName, $expTableData)
    {
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle . date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);

        include_once("../tools/PHPExcel.php");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle . '  Export time:' . date('Y-m-d H:i:s'));
        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }



    /**
     * 加密字符串(升级服务器)
     * AES-256 CBC
     * @param string $plaintext
     * @return string base64字符串
     */
    public function my_encrype($plaintext){
        $key = pack('H*', '0e55cf8bfba0fdf4a53353cd0d7e282e727a3c1e504145164ae97aa95542ca9a');
        //         $cipher = "AES-128-CBC";
        $cipher = "aes-256-cbc";

        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0);
        return $ciphertext;
    }



    /**
     * 解密字符串(升级服务器)
     * AES-256 CBC
     * @param string $ciphertext
     * @return string
     */
    public function my_decrypt($ciphertext){
        # 密钥是 16 进制字符串格式
        $key = pack('H*', '0e55cf8bfba0fdf4a53353cd0d7e282e727a3c1e504145164ae97aa95542ca9a');
        $cipher = "aes-256-cbc";

        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $original_plaintext = openssl_decrypt($ciphertext, $cipher, $key, $options=0);
        return $original_plaintext;
    }


    /**
     * sql查询部分通配符转义--- 主要是%以及_
     * @params 需要转义的字符串
     */
    public function escapeWildcard($strText){
        if(empty($strText)){
            return $strText;
        }
        $strText = addcslashes($strText,"\\");
        //转义百分号
        $strText = addcslashes($strText,"%");
        //转义_
        $strText = addcslashes($strText,"_");
        return $strText;
    }

    /**
     * 多维数组转换为二维数组
     * 取出最下面的一层数组
     * @param array $data 多维数组
     * @param string $field 数组以什么字段为条件
     * @param string $fields 返回数组的具体哪个字段
     * @return mixed
     */
    function multiToTwos(array $data = [], $field = 'child', $fields = 'function')
    {
        static $array = [];
        if (is_array($data)) {
            foreach ($data as $p) {
                if (isset($p[$field])) {
                    $this->multiToTwos($p[$field], $field, $fields);
                } else {
                    if (isset($p[$fields])) {
                        if (is_array($p[$fields])) {
                            foreach ($p[$fields] as $item) {
                                $array[] = $item;
                            }
                        } else {
                            $array[] = $p[$fields];
                        }
                    }
                }
            }
        }
        return array_filter($array);
    }

    /**
     * 多维数组转换为三维数组
     * 父类(name) => array( 类(name) => array( 'method1', 'method2'))
     * @param array $data 多维数组
     * @param string $keys 数组以什么为键
     * @param string $field 数组以什么字段为条件
     * @param string $fields 返回数组的具体哪个字段
     * @return array
     */

    function multiToTwo(array $data = [], $keys = 'name', $field = 'child', $fields = 'function'): array
    {
        $array = [];
        foreach ($data as $p) {
            if (!empty($p[$field])) {
                foreach ($this->selfMultiGet($p, $keys, $field, $fields) as $key=>$item) {
                    $array[$key] = $item;
                }
            }
        }

        return $array;
    }

    /**
    * 递归的调用本身
     */
    function selfMultiGet($p, $keys = 'name', $field = 'child', $fields = 'function'): array
    {
        $array = [];
        if (!empty($p[$field])) {
            foreach ($p[$field] as $p2) {
                if (intval($p2['level']) === 10) {
                    foreach ($p[$field] as $p3) {
                        $array[$p[$keys]][$p3[$keys]] = $p3[$fields];
                    }
                    break;
                } else {
                    $array = array_merge($array, $this->selfMultiGet($p2, $keys, $field, $fields));
                }
            }
        }

        return $array;
    }

    /**
     * 校验密码复杂度
     */

    function checkPassword($pwd)
    {
        $myType = 0;
        if ($pwd == null) {
            return false;
        }
        if (strlen(trim($pwd)) >= 6) {//必须大于6个字符
            $myType++;
        }

        // 有数字
        if (preg_match('/\d/', $pwd)) {
            $myType++;
        }

        //有大写字母
        //有小写写字母
        if (preg_match('/[A-Za-z]/', $pwd)) $myType++;

        $str2 = preg_replace('/[A-Za-z0-9]/', '', $pwd);
        if (strlen($str2) >= 1) { //必须含有特殊字符
            $myType++;
        }

        if ($myType > 3) {
            return true;
        }

        return false;
    }

    /**
     * @param string $value 转义后的值
     * @return string
     */
    public function removeEscape(string $value): string
    {
        $value = htmlspecialchars_decode($value, ENT_NOQUOTES);
        $value = str_replace("\'", "'", $value);
        $value = str_replace("\\\"", "\"", $value);
        return str_replace("\\\\", "\\", $value);
    }

    /**
     * 二维码生成类
     *
     * @param string $type
     */
    public function qrcode($url = '', $outfile = false) {

        include_once(XPHP_PATH . "libs/phpqrcode/phpqrcode.php");
        if ($outfile) {
            $path = ROOT_PATH . 'web_ng/api/data/qrcode/';
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            QRcode::png($url, $path . $outfile);
            return './web_ng/api/data/qrcode/' . $outfile;
        }
        return QRcode::png($url);
    }

    /**
     * 载入微信类
     *
     * @param string $type
     */
    public function wechat($type = '', $cache = []) {
        $options = array (
            'appid' => $cache ['appid'], // 填写高级调用功能的app id
            'appsecret' => $cache ['appsecret'], // 填写高级调用功能的密钥
        );

        include_once(XPHP_PATH . "libs/wechat-php-sdk/include.php");
        $wechat = &\Wechat\Loader::get_instance ( $type, $options );
        return $wechat;
    }

    /**
     * 发送模板消息
     *
     * @param array $openid 接收用户openid数组
     * @param string $templateid 模板id
     * @param array $info 模板信息
     * @param string $url 跳转url
     * @param array $config 微信配置
     * @param int $wechat_mode 发送模式 1 系统中转 2自定义
     * @return bool
     */
    public function send_wechat_template($openid = [], $templateid = '', $info = [], $url = '', $config = [], $wechat_mode = 1) {
        if(!$templateid || empty($openid)){
            return false;
        }
        if (empty($config)) {
            $config = Xphp::$_config['WECHAT_CONFIG'];
        }

        $return = false;
        $wechat = $this->wechat ( 'Receive' , $config);
        // 批量发送消息
        foreach ($openid as $item) {
            $data = [];
            $data ['touser'] = $item;
            $data ['template_id'] = $templateid;
            $data ['data'] = $info;
            $data ['url'] = $url;
            if ($wechat_mode == 1) {
                // 中转服务
                $return = $this->send_wechat_transfer($data, $config);
            } else {
                $return = $wechat->sendTemplateMessage ( $data );
            }
        }
        return $return;
    }

    /**
    * 中转服务
     * $data 发送的数据包
     * $config 微信配置
     */
    public function send_wechat_transfer($data, $config)
    {
        $datas = ['data'=>$data, 'config'=>$config];
        $url = Xphp::$_config['WECHAT_TRANSFER_URL'] . '/index.php?op=wechat';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datas));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 忽略 SSL 证书验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        curl_close($ch);

        $return = json_decode($response, true);

        if ($return['errcode'] == 0) {
            return true;
        }
        return false;
    }

    /**
    * 发送企业微信通知
     */
    public function send_wework_api($config, $message = [], $is_test = 0)
    {

        if (empty($config)) {
            $config = [
                // 企业的id，在管理端->"我的企业" 可以看到
                "CORP_ID"               => "ww37ae916bc52a725d",
                // 某个自建应用的id及secret, 在管理端 -> 企业应用 -> 自建应用, 点进相应应用可以看到
                "APP_ID"                => 1000002,
                "APP_SECRET"            => "T0cToNVS4SPQM4wKhnTEWOrriy9zLdXorS_WpuxdYoU",
            ];
        }

        $session_key = md5(json_encode($config));
        if (!empty($_SESSION[$session_key])) {
            $session_value = json_decode($_SESSION[$session_key], true);
        }
        if (!empty($session_value) && $session_value['expires_in'] + $session_value['cache_time'] > time() ) {
            // 存在缓存并且未过期
            $access_token = $_SESSION[$session_key];
        } else {
            // 获取 accesstoken
            $corpId = $config['CORP_ID'];  // 企业ID
            $corpSecret = $config['APP_SECRET'];  // 应用的凭证密钥

            $accessTokenUrl = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$corpId}&corpsecret={$corpSecret}";

            $response = file_get_contents($accessTokenUrl);
            $result = json_decode($response, true);
            if ($result['access_token']) {
                // 缓存下token
                $result['cache_time'] = time();
                $_SESSION[$session_key] = json_encode($result);

                $access_token = $result['access_token'];
            } else {
                return false;
                die('Failed to get access_token.');
            }
        }

        if ($is_test) {
            // 只是获取accesstoken 测试
            return true;
        }

        $agentId = $config['APP_ID'];  // 应用ID

        $sendMessageUrl = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$access_token}";

        $message = [
            'touser' => '@all',
            'msgtype' => 'textcard',
            'agentid' => $agentId,
            "textcard" => array(
                "title" => $message['title'] ?? Xphp::$_lang['WEB_UTILS_NOTICE'],
                "description" => $message['content'] ?? Xphp::$_lang['WEB_UTILS_CONTENT'],
                "url" => $message['url'] ?? 'https://www.vinchin.com',  // 在卡片底部显示的链接
                "btntxt" => Xphp::$_lang['WEB_UTILS_DETAILS'],  // 在卡片底部显示的按钮文字
            )
        ];

        $data = json_encode($message);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sendMessageUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        if ($result["errcode"] === 0) {
            return true;
            echo Xphp::$_lang['WEB_UTILS_SEND_INFO_SUCCESS'];
        } else {
            file_put_contents(ROOT_PATH . 'log.txt', Xphp::$_lang['WEB_UTILS_ENTERPRISE_WECHAT_SEND_INFO_ERROR'] . $result["errmsg"] . date('Y-m-d H:i') . PHP_EOL, FILE_APPEND);
            return false;
            echo Xphp::$_lang['WEB_UTILS_SEND_INFO_ERROR'] . $result["errmsg"];
        }
    }

    /**
     * 获取当前网址全路径
     */
    public function get_current_url() {
        $url = array ();
        $url [0] = $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $url [0] = 'https';
        $url [1] = isset ( $_SERVER ['HTTP_HOST'] ) ? $_SERVER ['HTTP_HOST'] : 'localhost';
        $url [2] = $_SERVER ['SERVER_PORT'];
        if ($url [2] == '80' || $url [2] == '443') {
            $u = $url [0] . '://' . $url [1];
        } else {
            $u = $url [0] . '://' . $url [1] . ':' . $url [2];
        }
        return $u;
    }

    /**
     * 系统加密方法
     *
     * @param string $data 要加密的字符串
     * @param string $key 加密密钥
     * @param int $expire 过期时间 单位 秒
     * @return string
     */
    public function xphp_encrpt($data, $key = 'vinchin', $expire = 0)
    {
        $key = md5 ($key);
        $data = base64_encode ( $data );
        $x = 0;
        $len = strlen ( $data );
        $l = strlen ( $key );
        $char = '';

        for($i = 0; $i < $len; $i ++) {
            if ($x == $l)
                $x = 0;
            $char .= substr ( $key, $x, 1 );
            $x ++;
        }

        $str = sprintf ( '%010d', $expire ? $expire + time () : 0 );

        for($i = 0; $i < $len; $i ++) {
            $str .= chr ( ord ( substr ( $data, $i, 1 ) ) + (ord ( substr ( $char, $i, 1 ) )) % 256 );
        }
        return str_replace ( array (
            '+',
            '/',
            '='
        ), array (
            '-',
            '_',
            ''
        ), base64_encode ( $str ) );
    }

    /**
     * 系统解密方法
     *
     * @param string $data要解密的字符串 （必须是aes_encrpt方法加密的字符串）
     * @param string $key 加密密钥
     * @return string
     */
    public function xphp_decrypt($data, $key = 'vinchin') {
        $key = md5 ($key);
        $data = str_replace ( array (
            '-',
            '_'
        ), array (
            '+',
            '/'
        ), $data );
        $mod4 = strlen ( $data ) % 4;
        if ($mod4) {
            $data .= substr ( '====', $mod4 );
        }
        $data = base64_decode ( $data );
        $expire = substr ( $data, 0, 10 );
        $data = substr ( $data, 10 );

        if ($expire > 0 && $expire < time ()) {
            return '';
        }
        $x = 0;
        $len = strlen ( $data );
        $l = strlen ( $key );
        $char = $str = '';

        for($i = 0; $i < $len; $i ++) {
            if ($x == $l)
                $x = 0;
            $char .= substr ( $key, $x, 1 );
            $x ++;
        }

        for($i = 0; $i < $len; $i ++) {
            if (ord ( substr ( $data, $i, 1 ) ) < ord ( substr ( $char, $i, 1 ) )) {
                $str .= chr ( (ord ( substr ( $data, $i, 1 ) ) + 256) - ord ( substr ( $char, $i, 1 ) ) );
            } else {
                $str .= chr ( ord ( substr ( $data, $i, 1 ) ) - ord ( substr ( $char, $i, 1 ) ) );
            }
        }
        return base64_decode ( $str );
    }

    /**
     * 系统加密方法
     *
     * @param string $data 要加密的字符串
     * @param string $key  加密密钥
     * @return string
     */
    function xphp_short_encrypt(string $data, $key = 'vinchin'): string
    {
        // 使用 SHA-256 代替 MD5，SHA-256 更安全
        $key = hash('sha256', $key, true);

        // 使用 XOR 加密
        $encrypted = '';
        $len = strlen($data);
        $keyLen = strlen($key);

        for ($i = 0; $i < $len; $i++) {
            $encrypted .= chr(ord($data[$i]) ^ ord($key[$i % $keyLen]));
        }

        // 使用 URL-safe 的 Base64 编码
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($encrypted));
    }

    /**
     * 系统解密方法
     *
     * @param string $encryptedData 加密后的字符串
     * @param string $key           加密密钥
     * @return string
     */
    function xphp_short_decrypt(string $encryptedData, $key = 'vinchin'): string
    {
        // 使用 SHA-256 代替 MD5，SHA-256 更安全
        $key = hash('sha256', $key, true);

        // 将 URL-safe 的 Base64 编码转换为标准 Base64 编码并解码
        $encryptedData = base64_decode(str_replace(['-', '_'], ['+', '/'], $encryptedData));

        // 使用 XOR 解密
        $decrypted = '';
        $len = strlen($encryptedData);
        $keyLen = strlen($key);

        for ($i = 0; $i < $len; $i++) {
            $decrypted .= chr(ord($encryptedData[$i]) ^ ord($key[$i % $keyLen]));
        }

        return $decrypted;
    }

    /**
     * 取出菜单配置里面的所有的name属性组合成一个一维数组
     * @param array $arr 数组
     * @return array
     */
    public function v1_get_all_name(array $arr = []): array
    {

        if (empty($arr)) {
            $arr = require CONF_PATH . 'page.php';
        }
        $return = [];
        foreach ($arr as $menu) {
            $return[] = $menu['name'];
            if (!empty($menu['child']) && $menu['level'] < 10) {
                $return = array_merge($return, $this->v1_get_all_name($menu['child']));
            }
        }
        return $return;
    }

    /**
     * 判断用户是否有操作关联用户的权限
     * @param string $useruuid 用户uuid
     * @param array $useruuidArr 被操作的用户uuid集合
     * @param array $authUser 管理权限的uuid集合
     * @return boolean
     */
    function xphp_check_operate(string $useruuid, array $useruuidArr = [], array $authUser = []): bool
    {
        // 超级管理员admin拥有所有权限
        if ($useruuid == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            return true;
        }
        // 1 先判断操作的数据是否有不属于自身的
        // 2 有自身的，在判断是否有关联用户的操作权限
        $userArr = array_unique(array_filter($useruuidArr));
        if (count($userArr) > 1 || (count($userArr) == 1 && $userArr[0] != $useruuid)) {
            // 表示有不属于自己的数据 那么需要判断是否越权
            // 获取拥有当前模块操作的所有用户集合
            $userArrs = array_merge($authUser ?? [], [$useruuid]);
            if (count(array_intersect($userArrs, $userArr)) < count($userArr)) {
                return false; // 没有权限
            }
        }
        return true;
    }

    /**
     *  获取 系统安全里面的数据安全的一些配置信息
     * @param int $type 某一项的配置  1 历史任务 2任务日志 3系统日志 4高可用日志 5任务告警 6系统告警
     * @return array 数组[类型, 数量] 类型1按个数 2按天数， 3永久保留
     */
    function xphp_get_system_data_safe(int $type = 1)
    {
        // 获取 bd_system_settings 表里面 settings_type 为 SYSTEM_CONFIG_RECORDS（12） 的配置内容
        $sql = "select settings_content from bd_system_settings where settings_type = 12";
        $opHandler = Xphp::instance('OPHandler');
        $data = $opHandler->dbSelect($sql);

        if (!empty($data)) {
            // 处理下数据格式，因为存储的是json数组
            $content = json_decode($data[0]['settings_content'], true);

            $model = $_SESSION['isThreePowers'] ? 'three' : 'normal';
            if (empty($content[$model])) {
                return [3, 0];
            }
            $data = $content[$model][$type] ?? [];
            if (
                empty($data) ||
                $data['strategy_status'] == 2 ||
                $data['strategy_type'] == 3
            ) {
                return [3, 0]; // 永久
            } else {
                // 类型和数量 1按个数 2按天数
                return [$data['strategy_type'], $data['number']];
            }
        }
        // 默认永久
        return [3, 0];
    }

    /**
     * 二维数组排序, 汉字按拼音排序, 英文忽略大小写
     * @param $array
     * @param $field
     * @param int $order
     */
    public function secondaryArraySort(&$array, $field, $order = SORT_ASC)
    {
        $sortColumns = array_map(function ($item) use ($field) {
            // 汉字排序，先转为GB18030, 再排序
            $value = iconv('UTF-8', 'GB18030//IGNORE', $item[$field]);
            // $value = mb_convert_encoding($item[$field], 'GB18030');
            // 忽略大小写排序
            return strtolower(trim($value));
        }, $array);
        array_multisort($sortColumns, $order, SORT_NATURAL, $array);
    }

    /**
     * 生成随机验证码
     * @param int $length 验证码长度
     * @return string
     */
    public function xphp_random_code($length = 4)
    {
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            // 生成一个随机字符
            $char = chr(mt_rand(48, 57)); // 数字0-9
            if (mt_rand(0, 1) === 1) {
                $char = chr(mt_rand(65, 90)); // 大写字母A-Z
            }
            $code .= $char;
        }
        return $code;
    }

    /**
     * 删除文件夹内所有文件及子文件夹，但不删除文件夹本身
     * @param string $path 文件夹路径
     * @return boolean
     */
    function xphp_delete_dir_file(string $path): bool
    {
        $result = false;
        if (is_dir($path)) {
            $handle = opendir($path);
            if ($handle) {
                while (false !== ($item = readdir($handle))) {
                    if (($item != '.') && ($item != '..')) {
                        $fullPath = "$path/$item";
                        if (is_dir($fullPath)) {
                            $this->xphp_delete_dir_file($fullPath); // 递归删除子文件夹内容
                            rmdir($fullPath); // 删除空子文件夹
                        } else {
                            unlink($fullPath); // 删除文件
                        }
                    }
                }
                closedir($handle);
                $result = true;
            }
        }
        return $result;
    }

    /*** --start 全局观察者* ***/
    /**
     * 获取当前用户是否是超级管理员/三权操作员
     * @return array
     */
    function v1_auth_is_admin(): array
    {
        $return = [
            'is_admin' => false, // 是否是超级管理员
            'global_observer' => false, // 是否是全局观察者
            'global_read' => false, // 是否是全局观察者只读
            'global_write' => false, // 是否是全局观察者查看并操作
            'is_three_operator' => false, // 是否是三权操作员
        ];
        // 1、首先判断用户的 user_level 是否是1 （超级管理员）
        $user = $_SESSION;

        if ($user['userLevel'] == 1) {
            $return['is_admin'] = true;
            return $return;
        }
        // 2、判断用户是否是全局观察者
        if (in_array('global_observer', $user['permission'])) {
            $return['global_observer'] = true;
            $return['global_read'] = in_array('global_read', $user['permission']);
            $return['global_write'] = in_array('global_write', $user['permission']);
            return $return;
        }

        // 是否是三权操作员
        if ($user['userLevel'] == 5) {
            $return['is_three_operator'] = true;
        }
        return $return;
    }

    /**
     * 获取当前用户关联管理的用户sql（包括关联管理者和用户组管理员(待定)）
     * @param string $auth 管理用户资源标识
     * @param string $back 返回格式 array或sql语句
     * @return array|string
     */
    function v1_auth_manage_user(string $auth, $back = 'array')
    {

        $user = $_SESSION;
        $uuid = $user['userUUID'];

        // 根据uuid和auth查询关联的用户uuid集合
        $sql = "select user_uuid from bd_user
                where (manager_uuid = '{$uuid}' and manager_auth like '%,{$auth},%')
                   or user_uuid = '{$uuid}'";
        if ($back == 'sql') {
            $return = '(' . $sql . ')';
        } else {
            $opHandler = Xphp::instance('OPHandler');
            $list = $opHandler->dbSelect($sql);
            $return = !empty($list) ? array_column($list, 'user_uuid') : [];
        }

        return $return;
    }

    /**
     * 判断当前用户是否需要关联权限-查看
     * @return bool
     */
    function v1_auth_need_check_look(): bool
    {
        $checkAuth = $this->v1_auth_is_admin();
        if (
            (empty($checkAuth['is_admin']) && empty($checkAuth['global_observer']))
            || !empty($checkAuth['is_three_operator'])
        ) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            return true;
        }
        return false;
    }

    /**
     * 判断当前用户是否需要关联权限-操作
     * @return bool
     */
    function v1_auth_need_operation(): bool
    {
        $checkAuth = $this->v1_auth_is_admin();
        if (
            (empty($checkAuth['is_admin']) && empty($checkAuth['global_write']))
            || !empty($checkAuth['is_three_operator'])
        ) {
            // 三权模式下的操作员只能管理分配的存储列表
            // 不是超级管理员也不是全局观察者-查看并操作，也只能管理分配的资源
            return true;
        }
        return false;
    }

    /**
     * 获取当前用户可拥有的资源sql(可分配的资源判断)
     * @param int    $souceType 分配的资源类型标识 详见resource的 RESOURCE_TYPE 枚举
     * @param string $field     如果带入了主表的主键，那么就会直接返回一个完整的where条件，不需要主表再拼一次
     * @param string $auth      权限标识，详见 user的 USER_AUTH 枚举
     * @param int    $type      类型 1查看 2操作
     * @return string
     */
    function v1_auth_get_source_by_type(int $souceType, string $field = '', string $auth = 'resmanagement', int $type = 1)
    {

        $source = $type == 1 ? '_look' : '_operate';
        // 关联管理用户判断 存储资源 - 查看?操作
        $authUser = $this->v1_auth_manage_user($auth . $source, 'sql');

        // 需要查询出用户所用户的资源
        $authsLogic = Xphp::instance('ResourceHandler');
        return ($authsLogic)->pGetUserAllResourceSql(
            $authUser,
            $souceType,
            'sql',
            $field
        );
    }

    /**
     * 操作存储权限判断
     * @param string $sourceUuid 资源uuid
     * @param string $auth       权限标识，详见 user的 USER_AUTH 枚举
     * @param int    $souceType  分配的资源类型标识 详见resource的 RESOURCE_TYPE 枚举
     * @return bool|string
     */
    function v1_auth_check_operate(string $sourceUuid, string $auth, int $souceType): bool
    {

        $resourceUuid = $this->v1_auth_get_source_by_type($souceType, '', $auth, 2);
        $idArr = explode(',', $sourceUuid);
        $idArr = array_filter($idArr);
        if (count($idArr) == 1) {
            $sql = "SELECT CASE WHEN '{$idArr[0]}' IN (
                        {$resourceUuid}
                    ) THEN 1 ELSE 0 END AS result";
        } else {
            // 多个
            $subSql = '';
            $i = 0;
            foreach ($idArr as $item) {
                if ($i == 0) {
                    $subSql = "SELECT '{$item}' AS uuid ";
                } else {
                    $subSql .= "UNION ALL SELECT '{$item}' ";
                }
                $i++;
            }
            $sql = "WITH target_users AS (
                            {$resourceUuid}
                        ),
                        check_list AS (
                            {$subSql}
                        )
                        SELECT 
                            CASE WHEN COUNT(t.uuid) = COUNT(*) THEN 1 ELSE 0 END AS result
                        FROM check_list cl
                        LEFT JOIN target_users t ON cl.uuid = t.uuid";
        }
        $opHandler = Xphp::instance('OPHandler');
        $check = $opHandler->dbSelect($sql);

        if (empty($check) || $check[0]['result'] != 1) {
            return false;
        }

        return true;
    }

    /**
     * 获取当前用户可管理的用户uuid sql
     * @param string $auth 权限标识，详见 user的 USER_AUTH 枚举
     * @param int    $type 类型 1查看 2操作
     * @return string
     */
    function v1_auth_get_users(string $auth, int $type = 1): string
    {

        $source = $type == 1 ? '_look' : '_operate';
        // 关联管理用户判断 存储资源 - 查看?操作
        return $this->v1_auth_manage_user($auth . $source, 'sql');
    }
    /*** --end 全局观察者* ***/
    /**
    * 统一获取license信息
     * @param string $field 查询字段，不带表示查询所有
     * @return array
     */
    function getLicenseInfo(string $field = '')
    {
        if (empty($field)) {
            $field = '*';
        }
        $opHandler = Xphp::instance('OPHandler');
        $data = $opHandler->dbSelect("select {$field} from bd_license");
        return !empty($data) ? $data : [];
    }
    /**
    * 获取授权是否有效
     */
    function getExpireDays()
    {
        $info = $this->getLicenseInfo('register_time,days');
        $return = [
            'expire_days' => -1, // 距离过期天数
            'expire_time' => Xphp::$_config['NULLSPACE'], // 过期日期
        ];
        if (empty($info)) {
            // 未授权
            return $return;
        }

        if ($info[0]['days'] == -1) {
            // 永久有效
            $return['expire_days'] = 99999;
            return $return;
        }

        $dayInterval = round((time() - strtotime($info[0]['register_time'])) / 3600 / 24);
        $expireDays = ($info[0]['days'] - $dayInterval) < 0 ? -1 : ($info[0]['days'] - $dayInterval);
        $dateformat = 'Y-m-d H:i:s';

        //到期天数等于注册时间+授权天数转换成时间
        $endTimeStamp = strtotime($info[0]['register_time']) + ($info[0]['days'] * 24 * 3600);
        $expireTime = date($dateformat, $endTimeStamp);
        $return['expire_time'] = $expireTime;
        $return['expire_days'] = $expireDays;

        return $return;
    }
}
