<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/api/load.php';

$filePath = ROOT_PATH . 'assets/global/css/v10/cJZKeOuBrn4kERxqtaUH3T8E0i7KZn-EPnyo3HZu7kw.woff';
$captchaParams = array(
    'font' => $filePath,
    'size' => 18,
    'width' => 120,
    'height' => 34,
    'length' => 4
);

//调验证码
$captchaUtils = Xphp::instance('Captcha');


//验证码初始化-返回图片
$captcha = $captchaUtils->get($captchaParams);

session_start();
$_SESSION['very_code'] = $captcha;
