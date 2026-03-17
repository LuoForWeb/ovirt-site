<?php

namespace app\v1\system\v0\validate;

use app\v1\common\validate\Base;

/**
 * 系统配置 - 系统工具 validate
 */
class Tool extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['integer', 'min' => 0],
            'limit' => ['integer', 'min' => 1],
            'oprate' => ['integer', 'between' => [1,3]],
            'node_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'name' => ['require'],
            'ip' => ['require'],
            'tool_type' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取系统服务列表
            'getServices' => ['offset', 'limit', 'node_uuid'],
            // 系统服务管理
            'opearate_service' => ['name', 'node_uuid', 'oprate'],
            // 测试网络连接
            'test_connect' => ['ip', 'node_uuid', 'tool_type'],
        ];
    }
}
