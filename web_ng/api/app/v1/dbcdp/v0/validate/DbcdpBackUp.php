<?php

namespace app\v1\dbcdp\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库实时 -- 之备份管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpBackUp extends Base
{
    /**
     * DbcdpBackUp constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'job_name' => ['require'],
            'agent_uuid' => ['require'],
//            'transfer_thread_num' => ['require','number'],
//            'transport_strategy.network_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'], //传输网络非空
        ];
        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'create_sync_job' => ['job_name'],
            'modify_sync_task' => ['job_name'],
            'network' => ['agent_uuid'],
        ];
    }
}
