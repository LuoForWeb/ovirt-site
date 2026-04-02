<?php

namespace app\v1\system\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          系统配置之系统通知的验证
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/14 14:19
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Apikey extends Base
{
    /**
    * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'host' => ['require'],
            'port' => ['require'],
            'user' => ['require'],
            'recEmail' => ['require'],
            'ip' => ['require'],
            'database' => ['require'],
            'pass' => ['require'],
            'recPhone' => ['require'],
            'phone' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'testemail' => ['host', 'port', 'user', 'recEmail'],
            'smtp' => ['host', 'port', 'user'],
            'sms' => ['ip', 'database', 'port', 'user', 'pass', 'recPhone'],
            'sendsms' => ['phone'],
        ];
    }
}
