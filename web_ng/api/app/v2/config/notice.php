<?php

/**
 * 微信通知的一些公共必须加载的配置信息
 */

return [
    // 微信公众号配置
    'WECHAT_CONFIG' => [
        'gh_id' => 'gh_2b928b00b109', // 原始id
        'appid' => 'wxadd515c8c7890d9c', // 填写高级调用功能的app id
        'appsecret' => '3209e7034725a123a53fe7f022c10cdb', // 填写高级调用功能的密钥
        'template_id' => 'RXYWI_4e3uaenFbClw01MInlfF-Thk3_EtexCXeFnQw', // 模板id
        'template_param1' => 'thing1', // 模板参数1
        'template_param2' => 'thing5', // 模板参数2
        'wechat_mode' => 1, // 模式 默认 1是系统中转 2是自定义
    ],
    // 微信公众号出厂中转服务器地址
    'WECHAT_TRANSFER_URL' => 'http://app.vinchin.com',

];
