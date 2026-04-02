<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';
$CONF = xphp_get_config('app','','',true);

$system = (new \app\v1\system\v0\logic\Index());
if (empty($system->getNodeType())) {
    echo 'ACCESS DENIED';die;
}
$softwareType = $system->getSoftwareType();

//特殊处理自动登录的情况，适用于文件客户端不用登录可以直接通过特定的链接进入系统首页，跳过WEB系统的登录。
function clientLoginIndex($agentuuid, $conf){
    if(!empty($agentuuid)){
        //为了备份系统安全，直接跳转到登录页面，不做免密登录
        header('Location: ./login.php');
        die;
    }
}

function callCurl($url) {
    // 初始化 cURL
    $ch = curl_init();

    // 设置 cURL 选项
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


    // 忽略 SSL 证书验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    // 执行 cURL 请求并获取响应
    $response = curl_exec($ch);
    // 检查是否有错误发生
    if(curl_errno($ch)){
        $error_message = curl_error($ch);
        curl_close($ch);
        return "Curl error: " . $error_message;
    }


    // 关闭 cURL 资源
    curl_close($ch);

    return $response;
}

// 取access_token

//特殊处理单点登录
function ssoLogin(){
    $tokenid = $_GET['access_token'];
    if(!empty($tokenid)){
        $access_token = urldecode($tokenid);
        $access_token = str_replace(' ', '+', $access_token);
        $csrf_token = urldecode($_GET['csrf_token']);
        $username = $_GET['username'];
        $password = $_GET['password'];

        // 验证成功 JS 结束标记前面不能用空格
        $jsCode = <<<JS
                <script>
                window.localStorage.setItem('csrf_token', '{$csrf_token}');
                window.localStorage.setItem('access_token', '{$access_token}');
                window.localStorage.setItem('username', '{$username}');
                window.localStorage.setItem('password', '{$password}');
                </script>
JS;
        echo $jsCode;
    }
}

//根据后台get到的agentuuid得到对应的用户登录到web控制台首页
$agentuuid = $_GET['agentuuid'];
clientLoginIndex($agentuuid, $CONF['API_MAGIC']);

ssoLogin();

//登录验证
$user = xphp_get_user_info();

if(empty($user['userName']) || empty($user['userUuid']) || empty($user['userType']) || empty($user['permissionFunctions'])){
    header('Location: ./login.php');
    die;
}

$LANG = require $_SERVER['DOCUMENT_ROOT'] . '/lang/' . $user['language'] . $CONF['ext'];

$mouleType = xphp_get_config('module', 'MODULE_TYPE');
$CONF['MODULE_TYPE'] = $mouleType;

$tskeType = xphp_get_config('task', 'TASKTYPE');
$CONF['TASK_TYPE'] = $tskeType;
$arr = [];
$arr['ROOTPATH'] = getcwd() . "/";
// 存session给页面用
$arr['LANG'] = $LANG;
$arr['CONF'] = $CONF;
setCaches($arr);

require_once './tpl/header.php';
require_once './tpl/top.php';
require_once './tpl/sidebar.php';
require_once './tpl/footer.php';
