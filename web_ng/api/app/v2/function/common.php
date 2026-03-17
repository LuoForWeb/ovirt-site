<?php

/**
 * v2 版本 公共函数文件
 * 以  v2_开头，单词之间用 _ 下划线隔开，单词用小写
 */

use phpseclib3\Crypt\Rijndael as Crypt_Rijndael;
use xphp\db\Op;
use xphp\Email;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * 方法库-字节转换-转换成MB格式等
 * @param int|string|null $num       数值
 * @param bool            $valueFlag 是否一定要获取数值,默认为否,为TRUE的时候会返回如0B这样的数据
 * @return string
 */
function v2_calsize($num = 0, bool $valueFlag = false): string
{
    $num = floatval($num);
    if (0 == $num && !$valueFlag) {
        return xphp_get_config('app')['NULLSPACE'];
    }
    $type = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'BB', 'NB', 'DB');
    $index = 0;
    while ($num >= 1024) {
        if ($index >= 11) {
            return $num . ($valueFlag ? $type[$index] : '');
        }
        $num = $num / 1024;
        $index++;
    }
    $num = round($num, 2);
    return $num . ($valueFlag ? (' ' . $type[$index]) : '');
}

/**
 * 方法库-字节转换-转换成MB格式等 按1000换算
 * @param int  $num       数值,
 * @param bool $valueFlag 是否一定要获取数值,默认为否,为TRUE的时候会返回如0B这样的数据
 * @return string
 */
function v2_calsize1000($num = 0, $valueFlag = false)
{
    $num = floatval($num);
    if (0 == $num && !$valueFlag) {
        return xphp_get_config('app')['NULLSPACE'];
    }
    $type = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'BB', 'NB', 'DB');
    $j = 0;
    while ($num >= 1000) {
        if ($j >= 11) {
            return $num . $type[$j];
        }
        $num = $num / 1000;
        $j++;
    }
    $num = round($num, 2);
    return $num . ' ' . $type[$j];
}

/**
 * 方法库-字节转换-转换成MB格式等
 * @param int $num       数值
 * @param $valueFlag 是否一定要获取数值,默认为否,为TRUE的时候会返回如0B这样的数据
 * @return array
 */
function v2_calsize_to_value_and_unit($num = 0, $valueFlag = false)
{
    $num = intval($num);
    $info = array(
        'value' => xphp_get_config('app')['NULLSPACE'],
        'unit' => '',
    );
    $num = floatval($num);
    if (0 == $num && !$valueFlag) {
        return $info;
    }
    $type = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'BB', 'NB', 'DB');
    $j = 0;
    while ($num >= 1024) {
        if ($j >= 11) {
            return $num . $type[$j];
        }
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
 * @param int $num 数值
 * @return string
 */
function v2_calspeed($num = 0)
{
    $speed = v2_calsize($num, true);
    if (0 == $speed) {
        return $speed;
    }
    return $speed . '/s';
}

/**
 * 计算百分比 返回百分比 23.45%
 * @param int $total 分母
 * @param int $value 分子
 * @return number|string
 */
function v2_calpercent($total = 0, $value = 0)
{
    $total = floatval($total);
    $value = floatval($value);
    if ($total == $value) {
        return '100%';
    }

    if (0 == $total) {
        return 0 . '%';
    }
    if ($total <= $value) {
        return '100%';
    }
    return floor(($value * 100 / $total) * 100) / 100 . '%';
}

/**
 * 计算百分比 返回数值23.45
 * @param int $total 分母
 * @param int $value 分子
 * @return number|string
 */
function v2_calpercent_value($total = 0, $value = 0)
{
    $total = floatval($total);
    $value = floatval($value);
    if (0 == $total) {
        return 0;
    }
    if ($total <= $value) {
        return '100';
    }

    return floor(($value * 100 / $total) * 100) / 100;
}

/**
 * object to array
 * @param object $array 对象
 * @return array
 */
function v2_object_array(object $array)
{
    if (is_object($array)) {
        $array = (array) $array;
    }

    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = v2_object_array($value);
        }
    }
    return $array;
}

/**
 * 得到用户类型的描述 操作员/审计员/管理员/超级管理员
 * @param int $usertype 用户类型
 * @return string
 */
function v2_get_user_type_des(int $usertype)
{
    $user = xphp_get_config('user')['USERTYPE'];
    switch ($usertype) {
        case $user['operator']:
            $typeDes = xphp_get_lang('WEB_UTILS_USERTYPE_OPERATOR');
            break;
        case $user['auditor']:
            $typeDes = xphp_get_lang('WEB_UTILS_USERTYPE_AUDITOR');
            break;
        case $user['manager']:
            $typeDes = xphp_get_lang('WEB_UTILS_USERTYPE_MANAGER');
            break;
        case $user['administrator']:
            $typeDes = xphp_get_lang('WEB_UTILS_USERTYPE_ADMINISTRATOR');
            break;
        default:
            $typeDes = xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN');
    }
    return $typeDes;
}

/**
 * 转换时间戳为 "3小时前/后"这种格式
 * @param $timestamp 时间戳
 * @return string
 */
function v2_format_date($timestamp)
{
    if ($timestamp <= 0) {
        return ' ';
    }
    $t = time() - $timestamp;
    $des = xphp_get_lang('WEB_UTILS_AGO');
    if ($t < 0) {
        $des = xphp_get_lang('WEB_UTILS_AFTER');
    }
    $t = abs($t);
    $f = array(
        '31536000' => xphp_get_lang('WEB_UTILS_YEAR'),
        '2592000' => xphp_get_lang('WEB_UTILS_MONTH'),
        '604800' => xphp_get_lang('WEB_UTILS_WEEK'),
        '86400' => xphp_get_lang('WEB_UTILS_DAY'),
        '3600' => xphp_get_lang('WEB_UTILS_HOUR'),
        '60' => xphp_get_lang('WEB_UTILS_MINUTE'),
        '1' => xphp_get_lang('WEB_UTILS_SECOND')
    );
    foreach ($f as $k => $v) {
        if (0 != $c = floor($t / (int) $k)) {
            return $c . $v . $des;
        }
    }
}

/**
 * 根据错误码,得到错误描述信息
 * @param int $errorInfo 描述
 * @return string
 */
function v2_get_error_des(int $errorInfo)
{

    $error = xphp_get_config('error');
    if (is_numeric($errorInfo)) {
        $errorCodeName = $error['errorCode'][$errorInfo];
        $errorCodeDes = $error['errorCodeDes'][$errorCodeName];
    } else {
        $errorCodeDes = $error['errorCodeDes'][$errorInfo];
    }
    //如果获取为空,直接返回错误参数
    return empty($errorCodeDes) ? $errorInfo : $errorCodeDes;
}

/**
 * 根据错误的定义得到错误Num号
 * @param string $errorValue 错误
 * @return int
 */
function v2_get_error_num(string $errorValue)
{
    $error = xphp_get_config('error');
    $num = array_search($errorValue, $error['errorCode']);

    return $num === false ? 0 : $num;
}

/**
 * 得到在线离线描述
 * @param int $onlineFlag 状态
 * @return string
 */
function v2_get_online_des(int $onlineFlag)
{

    $ptDes = xphp_get_desc('Pf', 'ONLINEDES');
    return $ptDes[$onlineFlag];
}

/**
 * 得到正常或异常的描述
 * @param int $errorCode 状态码
 * @return string
 */
function v2_get_status_des(int $errorCode)
{

    return $errorCode == 0 ? xphp_get_lang('WEB_PLATFORM_DES_NORMAL') : xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL');
}

/**
 * 根据用户UUID获取用户名
 * @param string $useruuid 用户id
 * @return string
 */
function v2_get_username(string $useruuid)
{
    $opHandler = new Op();
    $sql = "select user_name from bd_user where user_uuid = ?";
    $username = $opHandler->dbSelect($sql, array($useruuid));
    if ($username) {
        return $username[0]['user_name'];
    } else {
        $opHandler->writeLog('PF_USER_GET_USERNAME_ERROR', xphp_get_config('log')['LOG_INFO']['WARNING']);
        return 'unknown';
    }
}

/**
 * 格式化时分秒
 * 9:01:25补齐为09:01:25
 * @param string $dayTime 时间
 * @return string
 */
function v2_formart_time($dayTime)
{
    if (empty($dayTime)) {
        return $dayTime;
    }
    $eachValue = explode(':', $dayTime);
    if (intval($eachValue[0]) < 10) {
        $dayTime = '0' . intval($eachValue[0]) . ':' . $eachValue[1] . ':' . $eachValue[2];
    }
    return $dayTime;
}

/**
 *      把秒数转换为时分秒的格式
 * @param Int $times 时间，单位 秒
 * @return String
 */
function v2_sec_to_time(int $times)
{
    $result = '00:00:00';
    if ($times > 0) {
        $hour = v2_formart_time_string(floor($times / 3600));
        $minute = v2_formart_time_string(floor(($times - 3600 * $hour) / 60));
        $second = v2_formart_time_string(floor((($times - 3600 * $hour) - 60 * $minute) % 60));
        $result = $hour . ':' . $minute . ':' . $second;
    }
    return $result;
}

/**
 *      把秒数转换为年天时分秒的格式
 * @param Int $times 时间，单位 秒
 * @return String
 */
function v2_sec_to_day_time(int $times)
{
    $result = xphp_get_config('app')['TIMESPACE'];
    if ($times > 0) {
        $dayValue = floor($times / 3600 / 24);
        $day = v2_formart_time_string($dayValue);

        $hour = v2_formart_time_string(floor($times / 3600 % 24));
        $minute = v2_formart_time_string(floor($times / 60 % 60));
        $second = v2_formart_time_string(floor($times % 60));

        $result = $hour . xphp_get_lang('WEB_UTILS_HOUR') .
            $minute . xphp_get_lang('WEB_UTILS_MINUTE')
            . $second . xphp_get_lang('WEB_UTILS_SECOND');

        if ($dayValue != 0) {
            $result = $day . xphp_get_lang('WEB_UTILS_DAY') . $result;
        }
    }
    return $result;
}

/**
 * 把时分秒格式转换为秒的格式
 * @param string $timeStr 时间
 * @return number
 */
function v2_time_to_sec($timeStr)
{
    $timeArr = explode(':', $timeStr);
    return intval($timeArr[0]) * 3600 + intval($timeArr[1]) * 60 + intval($timeArr[2]);
}

/**
 * 转化时间格式 补齐  0-9 补齐00-09
 * @param $str 字符串
 * @return string
 */
function v2_formart_time_string($str)
{
    if ($str < 10) {
        return '0' . $str;
    }
    return $str;
}

/**
 * 获取客户端IP
 * @return string
 */
function v2_get_client_iP()
{

    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
        // 使用普通匿名代理服务器, 这个值类似："203.98.182.163, 203.98.182.163, 203.129.72.215"
        $ip = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
    } elseif (getenv('REMOTE_ADDR')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = 'Unknow';
    }

    return $ip;
}

/**
 * 发送邮件(通过互联网发送,暂时支持有网络条件)
 * 这个内置方法不对，不要用
 * @param string $toEmail 发送到邮件的地址
 * @param string $subject 主题
 * @param string $body    内容
 * @return bool
 */
function v2_send_email(string $toEmail, string $subject, string $body): bool
{
    $email = new Email();
    $confEmail = xphp_get_config('email')['EMAIL'];
    $email->config(
        $confEmail['smpt_host'],
        $confEmail['port'],
        $confEmail['authentication'],
        $confEmail['from_email'],
        $confEmail['from_email_pass']
    );
    return $email->sendmail($toEmail, $confEmail['from_email'], $subject, $body, $confEmail['type']);
}

/**
 * 转换标志到bool
 * @param int $flag flag
 * @return boolean
 */
function v2_parse_flag_to_bool($flag)
{
    $flag = intval($flag);
    if ($flag == xphp_get_config('app')['FLAG']['SET']) {
        return true;
    }
    return false;
}

/**
 * 转化bool到标志
 * @param boolean $boolValue bool
 * @return int
 */
function v2_parse_bool_to_flag($boolValue)
{
    return $boolValue ? xphp_get_config('app')['FLAG']['SET'] : xphp_get_config('app')['FLAG']['UNSET'];
}

/**
 * 搜索指定的数组的某列,返回搜索到的数组
 * @param array  $searchArray  需要搜索的数组(二维数组)
 * @param int    $searchColumn 需要搜索的列
 * @param string $searchValue  需要搜索的字符串
 * @return array
 */
function v2_array_search(array $searchArray, int $searchColumn, string $searchValue)
{
    if (empty($searchValue)) {
        return $searchArray;
    }
    $resultArray = array();
    foreach ($searchArray as $eachArray) {
        if (stristr($eachArray[$searchColumn], $searchValue) !== false) {
            $resultArray[] = $eachArray;
        }
    }
    return $resultArray;
}

/**
 * 给指定的数组进行排序,返回排序后指定长度的数组
 * @param array  $sortArray  需要排序的数组(二维数组)
 * @param string $sortColumn 需要排序的列
 * @param string $sortType   排序类型
 *                           asc/desc
 * @param int    $start      开始位置
 * @param int    $length     返回长度
 * @return array
 */
function v2_array_sort(array $sortArray, string $sortColumn, string $sortType, int $start, int $length)
{
    if ('desc' == $sortType) {
        $sortType = SORT_DESC;
    } else {
        $sortType = SORT_ASC;
    }
    if (-1 == $length) {
        $length = null;     //显示全部
    }
    $column = array();
    foreach ($sortArray as $arr) {
        $column[] = $arr[$sortColumn];
    }
    $column = array_map('strtolower', $column); //不区分大小写
    array_multisort($column, $sortType, $sortArray);
    return array_slice($sortArray, $start, $length);
}

/**
 * 二维数组去重
 * @param array  $array 需要去重的二维数组
 * @param string $key   按什么字段去重
 * @return array        去重后的数组
 */
function v2_unique_multidim_array(array $array, string $key)
{
    $temparray = array();
    $i = 0;
    $keyarray = array();

    foreach ($array as $val) {
        if (!in_array($val[$key], $keyarray)) {
            $keyarray[$i] = $val[$key];
            $temparray[$i] = $val;
        }
        $i++;
    }
    return $temparray;
}

/**
 * 加密字符串 php5.4
 * AES-256 CBC
 * @param string $plaintext 字符
 * @return string base64字符串
 */
function v2_encrype54(string $plaintext)
{
    # 密钥是 16 进制字符串格式
    $key = pack('H*', xphp_get_config('app')['SECRET_KEY']);
    # 显示 AES-128, 192, 256 对应的密钥长度：
    #16，24，32 字节。
    # 为 CBC 模式创建随机的初始向量
    $ivsize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($ivsize, MCRYPT_RAND);
    # 创建和 AES 兼容的密文（Rijndael 分组大小 = 256）
    # 仅适用于编码后的输入不是以 00h 结尾的
    # （因为默认是使用 0 来补齐数据）
    $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
    # 将初始向量附加在密文之后，以供解密时使用
    $ciphertext = $iv . $ciphertext;
    # 对密文进行 base64 编码
    return base64_encode($ciphertext);
}

/**
 * 解密字符串 php5.4
 * AES-256 CBC
 * @param string $ciphertext 字符
 * @return string
 */
function v2_decrypt54(string $ciphertext)
{
    # 密钥是 16 进制字符串格式
    $key = pack('H*', xphp_get_config('app')['SECRET_KEY']);
    $ivsize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    # --- 解密 ---
    $ciphertextdec = base64_decode($ciphertext);
    # 初始向量大小，可以通过 mcrypt_get_iv_size() 来获得
    $ivdec = substr($ciphertextdec, 0, $ivsize);
    # 获取除初始向量外的密文
    $ciphertextdec = substr($ciphertextdec, $ivsize);
    # 可能需要从明文末尾移除 0
    $plaintextdec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $ciphertextdec, MCRYPT_MODE_CBC, $ivdec);

    return trim($plaintextdec);
}

/**
 * 加密字符串
 * AES-256 CBC
 * @param string $plaintext 字符
 * @return string base64字符串
 */
function v2_encrype($plaintext = '')
{
    if (!(PHP_VERSION_ID >= 70000)) {
        return v2_encrype54($plaintext);
    }
    if (empty($plaintext)) {
        return '';
    }
    $key = pack('H*', xphp_get_config('app')['SECRET_KEY']);
    $cipher = 'AES-256-CBC';
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv2 = openssl_random_pseudo_bytes($ivlen);
    $iv2 = openssl_random_pseudo_bytes($ivlen);
    $iv = $iv2 . $iv2;  //凑齐32位,为了兼容之前的版本
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
 * @param string $ciphertext 字符
 * @return string
 */
function v2_decrypt($ciphertext = '')
{
    if (!(PHP_VERSION_ID >= 70000)) {
        return v2_decrypt54($ciphertext);
    }
    if (empty($ciphertext)) {
        return '';
    }
    $key = pack('H*', xphp_get_config('app')['SECRET_KEY']);
    $ciphertextdec = base64_decode($ciphertext);
    $ivlen = 32;
    # 初始向量大小，可以通过 mcrypt_get_iv_size() 来获得
    $ivdec = substr($ciphertextdec, 0, $ivlen);
    # 获取除初始向量外的密文
    $ciphertextdec = substr($ciphertextdec, $ivlen);
    $cipher = new Crypt_Rijndael('cbc'); // could use CRYPT_RIJNDAEL_MODE_CBC
    $cipher->setBlockLength(256);
    // keys are null-padded to the closest valid size
    // longer than the longest key and it's truncated
    $cipher->setKeyLength(256);
    $cipher->setKey($key);
    // the IV defaults to all-NULLs if not explicitly defined
    $cipher->setIV($ivdec);
    $cipher->disablePadding();

    return trim($cipher->decrypt($ciphertextdec));
}

/**
 * 解密平台加密
 * @param string $ciphertext 字符
 * @return string
 */
function v2_pt_pass_decrypt($ciphertext = '')
{
    $ciphertext = strval($ciphertext);
    $key = base64_decode(xphp_get_config('app')['SECRET_KEY_PT']);
    $newkey = '';
    if (strlen($key) >= 32) {
        $newkey = substr($key, 0, 32);
    } else {
        $remaindlen = 32 - strlen($key);
        $newkey = $key;
        for ($i = 0, $j = strlen($key); $j < 32; $i++, $j++) {
            $newkey[$j] = ~$newkey[$i];
        }
    }
    $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
    $plaintextdec = openssl_decrypt(
        base64_decode($ciphertext),
        'AES-256-CBC',
        $newkey,
        OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
        $iv
    );

    //去掉补位
    $pad = ord($plaintextdec[strlen($plaintextdec) - 1]);
    if ($pad > strlen($plaintextdec)) {
        return null;
    }
    if (strspn($plaintextdec, chr($pad), strlen($plaintextdec) - $pad) != $pad) {
        return null;
    }
    if ($pad) {
        $plaintextdec = substr($plaintextdec, 0, -1 * $pad);
    }
    return trim($plaintextdec);
}

/**
 * 平台加密字符串
 * AES-256 CBC
 * @param string $plaintext 字符
 * @return string base64字符串
 */
function v2_pt_pass_encrypt($plaintext = '')
{
    $bLOCKSIZE = 16;  //固定块大小byte
    $key = base64_decode(xphp_get_config('app')['SECRET_KEY_PT']);

    if (strlen($key) >= 32) {
        $newkey = substr($key, 0, 32);
    } else {
        $remaindlen = 32 - strlen($key);
        $newkey = $key;
        for ($i = 0, $j = strlen($key); $j < 32; $i++, $j++) {
            $newkey[$j] = ~$newkey[$i];
        }
    }

    $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    //计算传入字符串填充补齐块大小 --16byte
    if (strlen($plaintext) % $bLOCKSIZE != 0) {
        //不能被固定块大小整除
        $plaintSize = $bLOCKSIZE - strlen($plaintext) % $bLOCKSIZE;
    } else {
        //整除
        $plaintSize = $bLOCKSIZE;
    }

    //填充追加补齐
    $plaintext .= str_repeat(chr($plaintSize), $plaintSize);

    //加密
    $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $newkey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

    # 对密文进行 base64 编码
    return base64_encode($ciphertext);
}

/**
 * 解密JS通过RSA算法加密的字符串
 * JS通过公钥加密，PHP端通过私钥解密
 * @param array $plaintext 加密字符
 * @return string
 */
function v2_decrypt_js_rsa($plaintext = '')
{
    $decrypted = '';
    $privateKey = file_get_contents(DATA_PATH . 'private_key.pem');
    openssl_private_decrypt(base64_decode($plaintext), $decrypted, $privateKey);

    return $decrypted;
}

/**
 * 加密php通过RSA算法加密的字符串
 * php通过公钥加密，PHP端通过私钥解密
 * @param array $plaintext 加密字符
 * @return string
 */
function v2_encrypt_js_rsa($plaintext = '')
{
    $encrypted = '';
    $publicKey = file_get_contents(DATA_PATH . 'public_key.pem');

    // 加密前，通常需要对明文进行填充，以符合RSA加密块大小的要求
    openssl_public_encrypt($plaintext, $encrypted, $publicKey, OPENSSL_PKCS1_PADDING);

    // 加密后的数据通常是二进制数据，需要将其转换为base64编码以便传输或存储
    return base64_encode($encrypted);
}

/**
 * 封装导出excel函数
 * @param $expTitle     名称
 * @param $expCellName  标题
 * @param $expTableData 内容
 * @return void
 */
function v2_my_export($expTitle, $expCellName, $expTableData)
{
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
    $fileName = $expTitle . date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
    $cellNum = count($expCellName);
    $dataNum = count($expTableData);

    $objPHPExcel = new Spreadsheet();
    $cellName = array(
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ'
    );

    $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格
    $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle . '  Export time:' . date($dateformat));
    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
    }
    // Miscellaneous glyphs, UTF-8
    for ($i = 0; $i < $dataNum; $i++) {
        for ($j = 0; $j < $cellNum; $j++) {
            $objPHPExcel->getActiveSheet(0)->setCellValue(
                $cellName[$j] . ($i + 3),
                $expTableData[$i][$expCellName[$j][0]]
            );
        }
    }

    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
    $objWriter = IOFactory::createWriter($objPHPExcel, 'Xls');
    $objWriter->save('php://output');
    exit;
}

/**
 * 导出数据
 * @param string $title    表格的名称
 * @param array  $header   表头,[name => 姓名, ip => IP地址, create_time => 创建时间]
 * @param array  $data     表的数据[[name => JackC, ip => 192.168.1.1, create_time => 2023-08-23 11:23:01]]
 * @param array  $relation 列与数据的关系，即各个列的位置和宽度[name => [col_name => A, width => 20]]
 * @return void
 * @throws Exception
 */
function v2_base_export(string $title, array $header, array $data, array $relation)
{
    $objPHPExcel = new Spreadsheet();

    // 设置表格的属性
    $objPHPExcel->getProperties()
        ->setCreator('vinchin')
        ->setLastModifiedBy('vinchin')
        ->setTitle('Office 2007 XLSX Document')
        ->setSubject('Office 2007 XLSX Document')
        ->setDescription('Office 2007 XLSX')
        ->setKeywords('office 2007')
        ->setCategory('');

    // 设置活跃的表格，设为第一个
    $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
    // 表格的文件名
    $objPHPExcel->getActiveSheet()->setTitle($title);
    // 设置表头
    foreach ($relation as $key => $colInfo) {
        $activeSheet->setCellValue($colInfo['col_name'] . '1', $header[$key]);
        // 表头加粗
        $activeSheet->getStyle($colInfo['col_name'] . '1')->getFont()->setBold(true);
        // 设置列宽度
        $activeSheet->getColumnDimension($colInfo['col_name'])->setWidth($colInfo['width']);
    }

    // 添加内容
    foreach ($data as $index => $row) {
        $rowIndex = $index + 2;
        foreach ($relation as $key => $colInfo) {
            $activeSheet->setCellValue($colInfo['col_name'] . $rowIndex, $row[$key]);
        }
    }

    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $title . '.xlsx"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    // If you're serving to IE over SSL, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
    $objWriter->save('php://output');
    exit; // 立即终止脚本执行，防止任何额外输
}

/**
 * 加密字符串(升级服务器)
 * AES-256 CBC
 * @param string $plaintext 字符
 * @return string base64字符串
 */
function v2_my_encrype($plaintext = '')
{
    $key = pack('H*', '0e55cf8bfba0fdf4a53353cd0d7e282e727a3c1e504145164ae97aa95542ca9a');
    //         $cipher = "AES-128-CBC";
    $cipher = 'aes-256-cbc';

    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);

    return openssl_encrypt($plaintext, $cipher, $key, $options = 0);
}

/**
 * 解密字符串(升级服务器)
 * AES-256 CBC
 * @param string $ciphertext 字符
 * @return string
 */
function v2_my_decrypt($ciphertext = '')
{
    # 密钥是 16 进制字符串格式
    $key = pack('H*', '0e55cf8bfba0fdf4a53353cd0d7e282e727a3c1e504145164ae97aa95542ca9a');
    $cipher = 'aes-256-cbc';

    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);

    return openssl_decrypt($ciphertext, $cipher, $key, $options = 0);
}

/**
 * sql查询部分通配符转义--- 主要是%以及_
 * @param string $strText 需要转义的字符串
 * @return string
 */
function v2_escape_wildcard($strText = '')
{
    if (empty($strText)) {
        return $strText;
    }
    $strText = addcslashes($strText, '\\');
    //转义百分号
    return addcslashes($strText, '%');
    //转义_
    // return addcslashes($strText, '_');
}

/**
 * 多维数组转换为二维数组
 * 取出最下面的一层数组
 * @param array  $data   多维数组
 * @param string $field  数组以什么字段为条件
 * @param string $fields 返回数组的具体哪个字段
 * @return mixed
 */
function v2_multi_to_twos(array $data = [], $field = 'child', $fields = 'function')
{
    $array = [];
    if (is_array($data)) {
        foreach ($data as $p) {
            if (isset($p[$field])) {
                $array = array_merge($array, v2_multi_to_twos($p[$field], $field, $fields));
            } else {
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
    return array_filter($array);
}

/**
 * 多维数组转换为三维数组
 * 父类(name) => array( 类(name) => array( 'method1', 'method2'))
 * @param array  $data   多维数组
 * @param string $keys   数组以什么为键
 * @param string $field  数组以什么字段为条件
 * @param string $fields 返回数组的具体哪个字段
 * @return array
 */
function v2_multi_to_two(array $data = [], $keys = 'name', $field = 'child', $fields = 'function'): array
{
    $array = [];
    foreach ($data as $p) {
        if (!empty($p[$field])) {
            foreach (v2_self_multi_get($p, $keys, $field, $fields) as $key => $item) {
                $array[$key] = $item;
            }
        }
    }

    return $array;
}

/**
 * 递归的调用本身
 * @param $p      内容
 * @param string $keys   keys
 * @param string $field  字段
 * @param string $fields 字段2
 * @return array
 */
function v2_self_multi_get($p, $keys = 'name', $field = 'child', $fields = 'function'): array
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
                $array = array_merge($array, v2_self_multi_get($p2, $keys, $field, $fields));
            }
        }
    }

    return $array;
}

/**
 * 校验密码复杂度
 * @param $pwd 密码
 * @return bool
 */
function v2_check_password($pwd)
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
    if (preg_match('/[A-Z]/', $pwd)) {
        $myType++;
    }

    //有小写写字母
    if (preg_match('/[a-z]/', $pwd)) {
        $myType++;
    }

    $str2 = preg_replace('/[A-Za-z0-9]/', '', $pwd);
    if (strlen($str2) >= 1) { //必须含有特殊字符
        $myType++;
    }

    if ($myType > 4) {
        return true;
    }

    return false;
}

/**
 * @param string $value 转义后的值
 * @return string
 */
function v2_remove_escape(string $value): string
{
    $value = htmlspecialchars_decode($value, ENT_NOQUOTES);
    $value = str_replace("\'", "'", $value);
    $value = str_replace('\\"', '"', $value);
    return str_replace('\\\\', '\\', $value);
}

/**
 * 取出菜单配置里面的所有的name属性组合成一个一维数组
 * @param array $arr 数组
 * @return array
 */
function v2_get_all_name(array $arr = []): array
{

    $arr = empty($arr) ? xphp_get_menu() : $arr;
    $return = [];

    foreach ($arr as $menu) {
        $return[] = $menu['name'];
        if (!empty($menu['child']) && $menu['level'] < 10) {
            $return = array_merge($return, v2_get_all_name($menu['child']));
        }
    }

    return $return;
}

/**
 * 二维数组排序, 汉字按拼音排序, 英文忽略大小写
 * @param array  $array 排序的数组，传引用，会修改原数组
 * @param string $field 排序的字段
 * @param int    $order 排序的方式
 * @return void
 */
function v2_secondary_array_sort(array &$array, string $field, int $order = SORT_ASC)
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
 *  根据某2个键的值对二维数组去重
 * @param array  $array 某一项的配置
 * @param string $key1  第一个key
 * @param string $key2  第二个key
 * @return array
 */
function v2_unique_by_two_keys_manual(array $array, string $key1, string $key2)
{
    $seen = [];
    $result = [];

    foreach ($array as $item) {
        // 使用两个键值作为复合键
        $compositeKey = $item[$key1] . '-' . $item[$key2];

        if (!isset($seen[$compositeKey])) {
            $seen[$compositeKey] = true;
            $result[] = $item;
        }
    }

    // 使用 array_filter 去除空数组元素
    $filteredData = array_filter($result, function ($item) {
        return !empty($item); // 保留非空数组
    });

    // 重新索引数组
    return array_values($filteredData);
}

/**
 * 组装磁带获取时间点uuid的sql
 * @param string $where 额外条件
 * @return string
 */
function v2_tape_timepoint_sql($where = '')
{
    return "SELECT DISTINCT 
                SUBSTRING_INDEX(SUBSTRING_INDEX(btb.file_path, '/', 2), '/', -1) AS timepoint_uuid
            FROM
                bd_tape_backup_file btb
                JOIN bd_tape_carriage btc ON btc.serial_number = btb.tape_serial_number
                JOIN bd_tape_backup_set btbs ON btc.backup_set_uuid = btbs.backup_set_uuid
            WHERE 
                btb.file_path IS NOT NULL
                {$where}
            HAVING LENGTH(timepoint_uuid) = 36";
}
