<?php

session_start();
include_once $_SERVER['DOCUMENT_ROOT'].'/api/load.php';

$opHandler = Xphp::instance('OPHandler');
$utils = Xphp::instance('utils');
$clientIP = $utils->getClientIP();

$opHandler->loginOutLog(
    $_SESSION['userUuid'],
    $_SESSION['userName'],
    'SYSTEM_USER_LOGINOUT_SUCCESS',
    array("S:". $clientIP)
);

$permissionVisualScreen = false;
// 退出这里判断下是否拥有大屏的权限
if (!empty($_SESSION['permissionVisualScreen']) && $_SESSION['permissionVisualScreen'] == 100) {
    $permissionVisualScreen = true;
}
//这里保留语言的Session
$language = $_SESSION['language'];
// 清除所有sesson 完成退出
session_destroy();
//setcookie("Token", "", -1, '/', '', true, true);

// 保留大屏权限
$permissionVisualScreen && session_start() && $_SESSION['permissionVisualScreen'] = 100 && $_SESSION['language'] = $language;

// 清除web_ng的缓存目录
$utils->xphp_delete_dir_file(ROOT_PATH . 'web_ng/api/data/runtime/cache');

header('Location: ./login.php');
