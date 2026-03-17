<?php

namespace app\v1\s3\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          对象存储 - 任务详情 validate
 * @auther       chengjiafu@vinchin.com
 * @date         2024/1/9 14:27
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsJobInfo extends Base {
    public function __construct() {
        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['min' => 0],
            'limit' => ['require','number', 'min' => 0],
            'backup_mode' => ['require', 'number'],
            'task_uuid' => ['require'],
            'obs_uuids' => ['require', 'array'],
            'path' => ['require'],
            'fsnodeuuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 启动备份任务
            'obs_backup_job_start' => ['backup_mode', 'task_uuid', 'obs_uuids'],
            // 获取历史任务信息
            'obs_job_history' => ['task_uuid', 'offset', 'limit'],
            // 下载跳过文件
            'files_download_pass'   => ['fsnodeuuid'],
        ];
    }
}