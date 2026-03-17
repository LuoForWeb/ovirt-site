<?php

namespace app\v1\recovery\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          高级恢复：恢复、瞬时恢复、迁移、细粒度恢复操作的验证
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:45
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class RecoveryJobController extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'start_uuid' => ['require'],
            'point_info' => ['require', 'array'],
            'timepoint_uuid' => ['require'],
            'job_uuid' => ['require'],
            'safe_config_strategy' => ['require', 'array'],
            'mount_point_config' => ['require', 'array'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 启动任务
            'start' => ['start_uuid'],
            // 创建细粒度恢复任务
            'graininess_job' => ['point_info', 'safe_config_strategy'],
            // 创建瞬时恢复任务
            'instance' => ['point_info', 'timepoint_uuid', 'mount_point_config'],
            // 创建迁移任务
            'migrates_less' => ['task_uuid'],
            // 创建恢复任务
            'agentlessis_job' => ['point_info'],
            'agent_job' => ['point_info'],
            'stop_instant_operation' => ['job_uuid'],
        ];
    }
}
