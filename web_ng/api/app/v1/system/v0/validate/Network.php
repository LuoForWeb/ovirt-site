<?php

namespace app\v1\system\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          网络设置 相关的验证
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 17:19
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Network extends Base
{
    /**
    * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'node_uuid' => ['require'],
            'card_name' => ['require'],
            'card_hwaddr' => ['require'],
            'NAME' => ['require'],
            'uuids' => ['require', 'array'],
            'name' => ['require'],
            'card' => ['require'],
            'ip_start' => ['require', 'ip'],
            'ip_end' => ['require', 'ip'],
            'ip_mask' => ['require', 'ip'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'init' => ['node_uuid', 'card_name'],
            'list' => ['node_uuid'],
            'ip' => ['node_uuid', 'NAME'],
            'host' => ['node_uuid'],
            'nic' => ['node_uuid'],
            'clear' => ['uuids'],
            'add' => ['node_uuid', 'name'],
            'isolate' => ['ip_start', 'ip_end', 'ip_mask'],
        ];
    }
}
