<?php

namespace app\v1\s3\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          对象存储 - 恢复管理 validate
 * @auther       chengjiafu@vinchin.com
 * @date         2023/10/12 11:13
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsRecover extends Base
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
            'search_mode' => ['require', 'number', 'between' => [1, 2]],
            'md5_list' => ['require', 'array'],
            'path_name' => ['require'],
            'timepoint_uuid' => ['require'],
            'thread_uuid' => ['require'],
            'passwd' => ['require'],
            'pid' => ['require'],
            'sclass' => ['require'],
            'job_uuid' => ['require'],
            'agent_uuid' => ['require'],
            'id' => ['require'],
            'req_total_num' => ['require', 'number', 'min' => 0],
            'module_type' => ['require', 'number', 'min' => 0],
            'limit' => ['require', 'number', 'min' => 0],
            'root_flag' => ['require', 'integer'],
            'distinct_flag' => ['require', 'number'],
            'thread_num' => ['require'],
            'job_name' => ['require'],
            'speedInfo' => ['require', 'array'],
            'pointInfo' => ['require', 'array'],
            'typeInfo' => ['require', 'array'],
            'recoverInfo' => ['require', 'array'],
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
            // 校验文件数据加密密码正确性
            'files_check_encrypt' => ['timepoint_uuid', 'passwd'],
            // 得到恢复文件列表
            'files_get_backup_dir' => [
                'timepoint_uuid',
                'root_flag',
                'limit',
                'sclass'
            ],
            // 异步获取文件时间点
            'files_async_timepoint' => ['job_uuid', 'agent_uuid', 'id'],
            // 创建恢复任务
            'files_recover_job' => [
                'job_name',
                'recoverInfo',
                'typeInfo',
                'pointInfo',
                'speedInfo',
                'thread_num',
                'distinct_flag'
            ],
            'obs_recover_path_tree' => ['offset', 'limit', 'agent_uuid']
        ];
    }
}