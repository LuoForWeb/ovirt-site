<?php

namespace app\v1\industry\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          方案 validate
 * @author       wanggongxi@vinchin.com
 * @date         2025/9/29 10:51
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class Plan extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'plan_uuid'   => ['require'],
            'name'          => ['require'],
            'content'       => ['require'],
            'approval_uuid' => ['require'],
            'offset'        => ['number'],
            'limit'         => ['require', 'number'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'view' => ['plan_uuid'],
            'save' => ['name', 'approval_uuid'],
            'list' => ['offset'],
            'make' => ['plan_uuid', 'name', 'approval_uuid'],
        ];
    }
}
