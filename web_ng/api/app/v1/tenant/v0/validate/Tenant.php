<?php

namespace app\v1\vm\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          validate
 * @author      wuxian@vinchin.com
 * @date         2024/4/16 16:20
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Tenant extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'start' => ['require', 'number', 'min' => 0],
            'length' => ['require','number', 'min' => 1],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'list' => ['start', 'length'],
        ];
    }
}
