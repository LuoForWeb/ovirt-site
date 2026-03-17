<?php

namespace app\v1\dbcdp\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库实时 -- 之任务信息 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpJobInfo extends Base
{
    /**
     * DbcdpJobInfo constructor.
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'jobs_uuid' => ['require', 'min' => 36, 'regex' => '/^[\w|\d]\w+/'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'get_progress_info' => ['jobs_uuid'],
            'get_monitor_data' => ['job_uuid'],
            'get_fail_back_info' => ['jobs_uuid'],
            'edit_fail_back_conf' => ['jobs_uuid'],
//            'get_basic_info' => ['jobs_uuid'],
            'get_history_details' => ['jobs_uuid']
        ];
    }
}
