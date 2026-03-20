<?php

namespace app\v2\file\v0\validate;

use app\v2\common\validate\Base;

/**
 * note          文件 -- 之备份管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileBackUp extends Base
{
    /**
    * 构造方法
     */
    public function __construct()
    {
        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['gt' => -1],
            'limit' => ['require','number', 'gt' => 0],
            'src_info.file_info' => ['require','array'],
            'src_info.agent_list' => ['require','array'],
            'src_info.group_list' => ['require','array'],
            'backup_info' => ['require','array'],
            'speedLimit' => ['require','array'],
            'job_name' => ['require'],
            'filename' => ['require'],
            'dir' => ['require'],
            'pid' => ['require'],
            'group_uuid' => ['require'],
            'agent_uuid' => ['require'],
            'job_uuid' => ['require'],
            'info_uuid' => ['require'],
            'agent_list' => ['require', 'array'],
            'group_list' => ['require', 'array'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 创建备份任务
            'files_backup_job_add' => ['fs_path_list', 'host_list', 'strategy_info', 'job_name'],
            // 修改备份任务
            'files_backup_job_edit' => ['fs_path_list', 'host_list', 'strategy_info', 'job_name', 'job_uuid'],
            // 代理端文件树
            'files_agent_tree' => ['offset', 'limit', 'pid', 'agent_uuid'],
            // 获取任务基本信息
            'files_info'    => ['info_uuid'],
            // 得到修改文件备份任务客户端树
            'files_backup_old_tree' => ['agent_list', 'group_list'],
			// 文件客户端对应的目录树
            'file_dir_tree' => ['offset', 'limit', 'agent_uuid']
        ];
    }
}
