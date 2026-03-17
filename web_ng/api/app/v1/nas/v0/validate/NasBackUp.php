<?php

namespace app\v1\nas\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          NAS -- 之备份管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasBackUp extends Base
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
            'nas_uuid' => ['require'],
            'agent_uuid' => ['require'],
            'dir' => ['require'],
            'pid' => ['require'],
            'src_info.file_info' => ['require', 'array'],
            'src_info.agent_list' => ['require', 'array'],
            'src_info.group_list' => ['require', 'array'],
            'backup_info' => ['require', 'array'],
            'speed_info' => ['require', 'array'],
            'job_name' => ['require'],
            'job_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 得到所有挂载成功节点
            'nas_mount_node' => ['nas_uuid'],
            //  代理端文件子树
            'nas_agent_sontree' => ['nas_uuid', 'agent_uuid', 'offset', 'limit', 'dir', 'pid'],
            // 得到备份任务的所有信息
            'nas_get_backup_info' => ['job_uuid'],
            // 得到修改nas备份任务nas设备树
            'nas_backup_old_tree' => ['nas_uuid'],
            // 创建备份任务
            'nas_backup_job_add' => ['fileInfo', 'backupInfo', 'speedInfo', 'job_name'],
            // 修改备份任务
            'nas_backup_job_edit' => ['fileInfo', 'backupInfo', 'speedInfo', 'job_name', 'task_uuid'],
        ];
    }
}