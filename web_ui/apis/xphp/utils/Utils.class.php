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
        return $num.$type[$j];
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
     * 计算百分比
     * @param int $total   分母
     * @param int $value   分子
     * @return number|string
     */
    public function calPercent($total, $value){
        $total = floatval($total);
        $value = floatval($value);
        if(0 == $total) return 0 . "%";
        if($total <= $value) return '100%';
        $percent = round($value * 100 / $total, 2) . "%";
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
            mt_srand((double)microtime()*10000);
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
    public function getClientIP(){
        if (getenv("HTTP_CLIENT_IP")){
            $ip = getenv("HTTP_CLIENT_IP");
        }else if(getenv("HTTP_X_FORWARDED_FOR")){
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        }else if(getenv("REMOTE_ADDR")){
            $ip = getenv("REMOTE_ADDR");
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
     * @param int $sortColumn   需要培训的列
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
    
    /**
     * sql查询部分通配符转义--- 主要是%以及_
     * @params 需要转义的字符串
     */
    public function escapeWildcard($strText){
        if(empty($strText)){
            return $strText;
        }
        //转义百分号
        $strText = addcslashes($strText,"%");
        //转义_
        $strText = addcslashes($strText,"_");
        return $strText;
    }
    
}