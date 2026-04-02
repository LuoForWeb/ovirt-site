<?php

namespace app\v1\user\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          用户登录相关的验证器
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:25
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Login extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'username' => ['require', 'max' => 172],
            'password' => ['require', 'max' => 172],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 登录
            'login' => ['username', 'password'],
        ];
    }
}
