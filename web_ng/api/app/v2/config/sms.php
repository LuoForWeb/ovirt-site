<?php

/**
* 短信配置
 */

return [
    //短信配置
    'SMS_CONFIG' => array(
        //发送类型
        'SEND_TYPE' => array(
            'UNKNOWN' => 0,
            'INTERNET' => 1,   //短信平台发送
            'MODEM' => 2,      //短信猫发送
        ),
        //短信猫签名
        'SIGNATURE' => '云祺科技',
        //发送结果查询睡眠时间,插入数据库后要不停检查是否发送成功,单位 S
        'SLEEPTIME' => 5,
        //发送结果查询次数
        'CHECKTIME' => 10,
    ),
    'REMOTE' => [
        'API_URL' => 'http://app.vinchin.com/api/v2/system/sms_transfer?x-api-version=1.0-rev0', // 远程url
        'API_USER' => 'product', // api用户
        'API_PASS' => 'yunqi123456789', // api密码
    ],

];
