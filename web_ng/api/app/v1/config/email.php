<?php

/**
* email 的配置信息
 */

return [
    // 运行警告通知
    'host' => 'smtp.qq.com', // SMTP服务器
    'username' => '1099748104@qq.com', // SMTP 用户名  即邮箱的用户名
    'password' => 'urezycnauqrkidib', // SMTP 密码  部分邮箱是授权码(例如163邮箱)
    'port' => 465, // 服务器端口 25 或者465 具体要看邮箱服务器支持
    'sendname' => 'vinchin_web', // 发件人
    'recive' => [
        [
            'recivename' => 'wanggongxi',  // 接收人 名称
            'reciveaddress' => 'wanggongxi@vinchin.com'  // 接收人 邮件地址
        ]
    ],
    //邮件加密类型
    'EMAIL_ENCRYPTION_TYPE' => array(
        '',
        'ssl',
        'tls'
    ),

    //系统配置-默认邮件服务器信息
    'EMAIL' => array(
        'smpt_host' => 'smtp.exmail.qq.com',
        'from_email' => 'product@vinchin.com',
        'from_email_pass' => 'vinchin168',
        'port' => 465,
        'authentication' => true,
        'type' => 'HTML',
        'sendTimeOut' => 300,  //邮件链接有效时间  单位s
    ),


];
