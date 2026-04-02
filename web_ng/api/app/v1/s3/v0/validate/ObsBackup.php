<?php

namespace app\v1\s3\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          对象存储 -- 备份管理 validate
 * @auther       chengjiafu@vinchin.com
 * @date         2023/9/8 17:47
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsBackup extends Base
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
            'src_info.file_info' => ['require', 'array'],
            'src_info.agent_list' => ['require', 'array'],
            'src_info.group_list' => ['require', 'array'],
            'backup_info' => ['require', 'array'],
            'speedInfo' => ['require', 'array'],
            'job_name' => ['require'],
            'filename' => ['require'],
            'dir' => ['require'],
            'pid' => ['require'],
            'obs_uuid' => ['require'],
            //            'group_uuid' => ['require'],
//            'agent_uuid' => ['require'],
            'taskuuid' => ['require'],
            'info_uuid' => ['require'],
            'agent_list' => ['require', 'array'],
            'group_list' => ['require', 'array'],

            'target_uuid' => ['require']
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 修改备份任务
            'files_backup_job_edit' => ['file_info', 'backup_info', 'speed_info', 'job_name', 'job_uuid'],

            // 获取任务基本信息
            'files_info' => ['info_uuid'],

            // 得到修改文件备份任务客户端树
            'files_backup_old_tree' => ['agent_list', 'group_list'],

            // 对象存储对应的目录树
            'file_dir_tree' => ['offset', 'limit', 'obs_uuid'],

            // 创建备份任务
            'obs_backup_job_add' => ['backupInfo', 'job_name'],

            // 修改备份任务
            'obs_backup_job_edit' => ['backupInfo', 'job_name', 'taskuuid'],
        ];
    }
}