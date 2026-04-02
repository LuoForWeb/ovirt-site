<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/web_ng/api/public/load.php';

use League\OAuth2\Client\Provider\GenericProvider;

// 判断是否为 HTTPS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

// 获取 HTTP_HOST（包含域名和端口）
$host = $_SERVER['HTTP_HOST'];

// 拼接返回值
$baseUrl = "$protocol://$host";

$clientId = $_GET['clientId'];
$clientSecret = $_GET['clientSecret'];
$tenantId = $_GET['tenantId'];
if (empty($clientId) || empty($clientSecret)) {
    // select
    $sql = "select smtp_config from bd_email_notice where email_notice_type = 1";
    $data = dbSelect($sql);
    $smtpConfig = json_decode($data[0]['smtp_config'], true);
    $clientId = $smtpConfig['client_id'];
    $clientSecret = $smtpConfig['client_secret'];
    $tenantId = $smtpConfig['tenant_id'];
}
if (empty($tenantId)) {
    $tenantId = 'common';
}

$_SESSION['clientId'] = $clientId;
$_SESSION['clientSecret'] = $clientSecret;
$_SESSION['tenantId'] = $tenantId;

$provider = new GenericProvider([
    'clientId'                => $clientId,
    'clientSecret'            => $clientSecret,
    'redirectUri'             => $baseUrl . '/oauth2callback.php',
    'urlAuthorize'            => "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize",
    'urlAccessToken'          => "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token",
    'urlResourceOwnerDetails' => '',
    'scopes'                  => 'https://outlook.office365.com/Mail.Send offline_access'
]);

// 构造授权 URL
$authorizationUrl = $provider->getAuthorizationUrl();
$_SESSION['oauth2state'] = $provider->getState();

header('Location: ' . $authorizationUrl);
exit;