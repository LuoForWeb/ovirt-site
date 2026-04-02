<?php

namespace app\v1\db\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库 -- 之任务信息 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbJobInfo extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'jobs_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],  // 任务uuid，通过query发送的
            'job_uuid' => ['require'],  // 任务uuid
            'instance_name' => ['require'],
            'db_name' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'get_db_job_info' => ['jobs_uuid'],
            'generate_db_validate_report' => ['job_uuid', 'db_type', 'instance_name', 'db_name'],
            'get_db_validate_report' => ['job_uuid', 'db_type', 'instance_name', 'db_name'],
            'send_db_validate_report' => ['job_uuid', 'db_type', 'instance_name', 'db_name'],
            'get_db_job_association' => ['jobs_uuid'],
        ];
    }
}
