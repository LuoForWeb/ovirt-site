<?php

namespace app\v1\job\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          虚拟机管理 -- 之任务操作 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class JobController extends Base
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
            'stop_uuid' => ['require'],
            'jobs_uuid' => ['require'],
            'job_uuids' => ['require', 'array'], // 批量多个
            'history_uuid' => ['require'],
//            'start_type' => ['number', 'in' => [0,1,2,3,4]],
            'start_type' => ['number'],
            'log_uuid' => ['require'],
            'log_down_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'start' => ['start_uuid', 'start_type'],              // 启动任务
            'startBatch' => ['job_uuids', 'start_type'],         // 批量启动任务
            'stop' => ['stop_uuid'],                            // 停止任务
            'stopBatch' => ['job_uuids'],                       // 批量停止任务
            'del' => ['jobs_uuid'],                             // 删除任务
            'delBatch' => ['job_uuids'],                       // 批量删除任务
            'pause_task' => ['job_uuids'],                       // 暂停任务
            'del_history' => ['history_uuid'],      // 删除历史任务
            'batchdel_history' => ['job_uuids'],      // 批量删除历史任务
            'jobs_log' => ['log_uuid'],      // 下载任务日志校验
            'jobs_down_log' => ['log_down_uuid'],      // 下载任务日志校验
        ];
    }
}
