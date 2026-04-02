<?php

namespace app\v1\dbcdp\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库实时 -- 之恢复管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpRecover extends Base
{
    /**
     * DbcdpRecover constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'job_name' => ['require'],
            'restore_data_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'events_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'job_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            //创建恢复任务
            'create_recover_job' => ['job_name'],
            //获取事件详情
            'events_details' => ['events_uuid'],
            //获取事件信息
            'events_info' => ['restore_data_uuid'],
            //获取可恢复时间范围
            'restore_data_time_range' => ['restore_data_uuid'],
            //修改恢复任务
            'modify_sync_task' => ['job_name','job_uuid'],
        ];
    }
}
