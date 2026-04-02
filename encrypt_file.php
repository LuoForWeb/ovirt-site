<?php

if (!function_exists('encryptArrayValues')) {
    function encryptArrayValues($array) {
        $key = createKeyIv(0);
        $iv = createKeyIv(1);
        foreach ($array as $k => $value) {
            if (is_array($value)) {
                $array[$k] = encryptArrayValues($value);
            } else {
                // 这里需要把类型一起加密存，解密时候才能完全还原
                // 判断数据类型
                $dataType = gettype($value);
                // 对布尔值进行特殊处理，将其转换为字符串 "true" 或 "false"
                if ($dataType === 'boolean') {
                    $value = $value ? 'true' : 'false';
                }
                // 将数据类型转换为字符串，以便可以安全地存储和传输
                $dataTypeString = serialize($dataType);
                $newValue = $dataTypeString . '!'.md5($iv).'!' . $value;
                $array[$k] = openssl_encrypt($newValue, 'AES-256-CBC', $key, 0, $iv);
            }
        }
        return $array;
    }
}

if (!function_exists('createKeyIv')) {
    function createKeyIv($type = 0) {
        if ($type == 0) {
            // key
            $binary = array (48,101,53,53,99,102,56,98,102,98,97,48,102,100,102,52,97,53,51,51,53,51,99,100,48,100,55,101,50,56,50,101,55,50,55,97,51,99,49,101,53,48,52,49,52,53,49,54,52,97,101,57,55,97,97,57,53,53,52,50,99,97,57,97 );
            $len = 32;
        } else {
            // iv
            $binary = array (100,109,108,117,89,50,104,112,98,106,69,121,77,122,81,49,78,106,99,52,99,50,116,53);
            $len = 16;
        }

        $string = '';
        foreach ($binary as $byte) {
            $string .= chr($byte);
        }
        return substr(md5($string), 0, $len);
    }
}
if (!function_exists('getEnvs')) {
    function getEnvs() {
        return;
    }
}
error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('memory_limit', '512M');

$baseUrl = getcwd();

$config = [
    $baseUrl . '/api/xphp/conf/config.php',
    $baseUrl . '/apis/xphp/conf/config.php',
    $baseUrl . '/web_ng/api/app/v1/config/app.php',
    $baseUrl . '/web_ng/api/app/v1/config/database.php',
    $baseUrl . '/web_ng/api/app/v1/config/email.php',
    $baseUrl . '/web_ng/api/app/v1/config/socket.php',
    $baseUrl . '/web_ng/api/app/v1/config/three_powers.php',
];


$array = require $baseUrl . '/api/xphp/conf/config.php';
if ($array['ext'] != '.php') {
    echo 'again encrypt!';
    exit(1);
}

$num = 0;
$encry_files = [];
// encry config
foreach ($config as $file) {
    if (file_exists($file)) {
        $value = require $file;
        if (is_array($value)) {
            $encryptedArray = encryptArrayValues($value);
            $encryptedArrayString = "<?php\n\nreturn " . var_export($encryptedArray, true) . ";\n\n";
            $encry_files[] = $file;
            file_put_contents($file, $encryptedArrayString);
            echo $file . "<br>";
            $num++;
        } else {
            echo "Invalid data type in file: " . $file . "<br>";
        }
    }
}
echo 'Total openssl PHP Files:' . $num . "<br>";

exit(0);