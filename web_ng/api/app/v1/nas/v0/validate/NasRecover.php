<?php

namespace app\v1\nas\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          NAS -- 之恢复管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasRecover extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['min' => 0],
            'limit' => ['require', 'number', 'min' => 0],
            'timepoint_uuid' => ['require'],
            'sclass' => ['require'],
            'job_uuid' => ['require'],
            'agent_uuid' => ['require'],
            'id' => ['require'],
            'job_name' => ['require'],
            'speedInfo' => ['require', 'array'],
            'pointInfo' => ['require', 'array'],
            'typeInfo' => ['require', 'array'],
            'recoverInfo' => ['require', 'array'],
            'distinct_flag' => ['require', 'number'],
            'thread_num' => ['require'],
            'search_mode' => ['require', 'number', 'between' => [1, 2]],
            'path_name' => ['require'],
            'req_total_num' => ['require', 'number', 'min' => 0],
            'module_type' => ['require', 'number', 'min' => 0],
            'thread_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 文件恢复搜索 创建搜索消息
            'files_create_search' => [
                'search_mode',
                'path_name',
                'timepoint_uuid',
                'req_total_num',
                'module_type'
            ],
            // 文件恢复搜索---停止搜索
            'files_stop_search' => ['timepoint_uuid', 'thread_uuid'],
            // 文件恢复搜索---获取搜索结果
            'files_get_search' => ['timepoint_uuid', 'thread_uuid', 'limit', 'offset'],
            // 得到nas时间点树
            // 'nas_get_data_tree' => [],
            // 得到nas恢复文件树
            'nas_get_recovery_dir' => ['offset', 'limit', 'timepoint_uuid', 'sclass'],
            // 异步获取nas时间点
            'nas_async_timepoint' => ['job_uuid', 'agent_uuid', 'id'],
            // 得到nas设备下的恢复文件目录树
            'nas_recover_path_tree' => ['offset', 'limit', 'agent_uuid'],
            // 创建恢复任务
            'nas_recover_job' => [
                'job_name',
                'recoverInfo',
                'typeInfo',
                'pointInfo',
                'speedInfo',
                'thread_num',
                'distinct_flag'
            ],
        ];
    }
}