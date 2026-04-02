<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';

use League\OAuth2\Client\Provider\GenericProvider;

if (!isset($_GET['code']) || !isset($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    echo backHtml('/img/platform/alarm/icon_error.svg', Xphp::$_lang['WEB_PUBLIC_FAILURE'], 'Invalid state');
    die;
}

// 判断是否为 HTTPS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

// 获取 HTTP_HOST（包含域名和端口）
$host = $_SERVER['HTTP_HOST'];

// 拼接返回值
$baseUrl = "$protocol://$host";

$clientId = $_SESSION['clientId'];
$clientSecret = $_SESSION['clientSecret'];
$tenantId = $_SESSION['tenantId'];
if (empty($clientId) || empty($clientSecret)) {
    // select
    $sql = "select smtp_config from bd_email_notice where email_notice_type = 1";
    $data = dbSelect($sql);
    $smtpConfig = json_decode($data[0]['smtp_config'], true);
    $clientId = $smtpConfig['client_id'];
    $tenantId = $smtpConfig['tenant_id'];
}

if (empty($tenantId)) {
    $tenantId = 'common';
}

$provider = new GenericProvider([
    'clientId'                => $clientId,
    'clientSecret'            => $clientSecret,
    'redirectUri'             => $baseUrl . '/oauth2callback.php',
    'urlAuthorize'            => "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize",
    'urlAccessToken'          => "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
    'urlResourceOwnerDetails' => '',
]);

try {
    // 获取 token
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    $baseDir = DATA_PATH;
    $tokenUrl = $baseDir . '/email/refresh_token.txt';
    // 保存 refresh_token 到文件或数据库
    file_put_contents($tokenUrl, $token->getRefreshToken());
    // echo $token->getRefreshToken();
    echo backHtml('/img/platform/alarm/icon_success.svg', Xphp::$_lang['WEB_PUBLIC_SUCCESS'], "Token is get，now you can send test email and save the email config.");
    die;

} catch (\Exception $e) {
    echo backHtml('/img/platform/alarm/icon_error.svg', Xphp::$_lang['WEB_PUBLIC_FAILURE'], "get Token error：" . $e->getMessage());
    die;
}

function backHtml($icon, $title, $content) {
    return '<div>
        <img src="'.$icon.'" style="
    display: block;
    width: 30%;
    margin-left: 35%;
">
        <div style="
    text-align: center;
    height: 22px;
    font-size: 50px;
    font-weight: bold;
    color: #333333;
    line-height: 22px;
    margin-top: 46px;
">'.$title.'</div>
        <div style="
    width: 59%;
    height: 118px;
    font-size: 40px;
    font-weight: 400;
    color: #999999;
    line-height: 48px;
    margin: 68px auto;
    text-align: center;
">'.$content.'</div>
    </div>';
}
