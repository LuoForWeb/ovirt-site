<?php

namespace app\v1\industry\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          行业合规模板 validate
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/31 18:29
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Template extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'templates_uuid' => ['require'],
            'name' => ['require'],
            'virtus_num' => ['require', 'number'],
            'document_num' => ['require', 'number'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'update' => ['name', 'document_num'],
            'view' => ['templates_uuid'],
        ];
    }
}
