<?php

namespace app\v1\system\v0\validate;

use app\v1\common\validate\Base;
/**
 * note          系统安全配置 controller
 * @author       zhengxiangqin@vinchin.com
 * @date         2024/3/18 11:06
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Message extends Base
{

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'nickname' => ['require', 'string'],
            'alarm_type' => ['require', 'number'],
            'strategy_uuid' => ['require', 'string'],
            'strategy_name' => ['require', 'string'],
            'sync_time_type' => ['require', 'number'],
            'last_sync_time' => ['require', 'string'],
            'all_task_flag' => ['require', 'number'],
            'task_uuid' => ['require', 'array'],
            'alarm_message_structure' => ['require', 'array'],
            'auto_push_flag' => ['require', 'boolean'],
            'push_response_type' => ['require', 'number'],
            'protocol_type' => ['require', 'string'],
            'ip' => ['require', 'string'],
            'port' => ['require','number'],
            'url' => ['require','string'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 保存数据安排配置
            'add_message_push' => ['nickname', 'alarm_type', 'strategy_uuid', 'strategy_name', 'sync_time_type','last_sync_time','all_task_flag','task_uuid','alarm_message_structure','auto_push_flag','push_response_type'],
            'add_message_monitor_platform' => ['nickname','protocol_type','ip','port','url'],
        ];

    }
}