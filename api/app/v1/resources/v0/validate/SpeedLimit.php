<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          SpeedLimit全局限速策略验证器
 * @author       wanggongxi@vinchin.com
 * @date         2023/9/11 14:45
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class SpeedLimit extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset'        => ['number', 'between' => '0,99999'],
            'limit'         => ['require', 'number', 'between' => '1,500'],
            'strategy_uuid' => ['require'],
            'global_speed_send_uuid' => ['require'],
            'name'          => ['require'],
            'speed_info'    => ['require', 'array'],
            'uuids'         => ['require', 'array'],
            'speed_type'    => ['require', 'number'],
            'task_priority'    => ['require', 'number'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'lists' => ['offset', 'limit'], // 列表
            'add' => ['name', 'speed_info', 'speed_type'], // 添加
            'edit' => ['name', 'speed_info', 'speed_type', 'strategy_uuid'], // 修改
            'view' => ['strategy_uuid'], // 详情
            'del' => ['uuids'], // 删除
            'send' => ['global_speed_send_uuid', 'uuids'], // 分发
            'jobs' => ['offset', 'limit'], // 获取可以分发的任务列表
        ];
    }
}
