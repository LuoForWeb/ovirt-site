<?php

namespace app\v1\cloud\v0\validate;

use app\v1\common\validate\Base;

/**
 * Class CloudRecover
 * @package app\v1\cloud\v0\validate
 */
class CloudRecover extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        $timeReg = '/^((20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d)$/'; //验证时间格式
        $dateTimeReg = '/^[1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s
            ((20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d)$/'; //验证日期时间格式

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'job_name' => ['require'],

            //备份点信息
            'point_info' => ['require'],
            'point_info.points' => ['require', 'array'],
            'point_info.points.*.instance_uuid' => ['require'],
            'point_info.points.*.timepoint_uuid' => ['require'],
            'point_info.points.*.platform_uuid' => ['require'],
            'point_info.points.*.hypervisor_type' => ['require', 'integer', 'min' => 0],
            'point_info.points.*.node_uuid' => ['require'],
            'point_info.points.*.storage_uuid' => ['require'],

            //恢复目标信息
            'recover_info' => ['require'],
            'recover_info.platform_uuid' => ['require'],
            'recover_info.recover_type' => ['require', 'integer', 'in' => [1, 2]], //恢复类型：1实例，2卷
            'recover_info.hypervisor_type' => ['require', 'integer'],

            'recover_info.instance_configs' => ['require', 'array'],
            'recover_info.instance_configs.*.instance_name' => ['require'],
            'recover_info.instance_configs.*.instance_type_uuid' => ['require'],
            'recover_info.instance_configs.*.region_uuid' => ['require'],
            'recover_info.instance_configs.*.vpc_uuid' => ['require'],
            'recover_info.instance_configs.*.public_ip_flag' => ['require', 'boolean'],
            'recover_info.instance_configs.*.timepoint_uuid' => ['require'],

            'recover_info.instance_names' => ['array'],

            'recover_info.volume_configs' => ['require', 'array'],
            'recover_info.volume_configs.*.volume_uuid' => ['require'],
            'recover_info.volume_configs.*.available_zone' => ['require'],

            //时间策略
            'time_strategy' => ['require'],
            'time_strategy.time_type' => ['require', 'integer', 'in' => [1, 2]], //恢复方式：1立即，2定时
            'time_strategy.timing_time' => ['dateFormat' => $dateTimeReg],

            //限速策略
            'speed_strategy.*.mode' => ['require', 'integer', 'in' => [1, 2]], //限速方式：1按策略，2永久
            'speed_strategy.*.speed_type' => ['require', 'integer', 'in' => [1, 2, 3]], //时间策略：1每天，2每周，3每月
            'speed_strategy.*.value' => ['integer'],
            'speed_strategy.*.start_time' => ['dateFormat' => $timeReg],
            'speed_strategy.*.end_time' => ['dateFormat' => $timeReg],
            'speed_strategy.*.days' => ['array'],
            'speed_strategy.*.days.*' => ['integer', 'in' => [0, 1]], //是否执行：0否，1是
            'speed_strategy.*.des' => ['require'],

            //高级策略
            'advanced_strategy' => ['require'],
            'advanced_strategy.thread_num' => ['require', 'integer', 'between' => [1, 8]],
            'advanced_strategy.priority_snapshot_flag' => ['require', 'boolean'],

            //传输策略
            'transport_strategy' => ['require'],
            'transport_strategy.mode' => ['require', 'in' => ['nbd', 'nbdssl']],
            'transport_strategy.appliance_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'createJob' => ['platform_uuid'],
        ];
    }
}
