<?php

namespace app\v2\s3\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          对象存储 -- 之备份管理
 * @auther       chengjiafu@vinchin.com
 * @date         2023/9/8 17:35
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsBackup extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取备份源对象树
     * @return void
     */
    public function getObsBackupTree()
    {
        // 1 参数验证

        // 2 请求转发到logic去处理
        $return = $this->logic()->getObsBackupTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 获取对象存储对应的目录树
     * @return void
     */
    public function getFileDirTree()
    {

        // 1 参数验证
        $this->checkParams('file_dir_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getFileDirTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 获取对象存储对应的目录树
     * @return void
     */
    public function getFileDirSonTree()
    {
        // 1 参数验证
        $this->checkParams('file_dir_tree');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getFileDirSonTree($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    /**
     * 创建对象存储备份任务
     * @return void
     */
    public function createBackupJob()
    {
        // 1 参数验证
        $this->checkParams('obs_backup_job_add');

        // 2 请求转发到logic去处理
        $return = $this->logic()->createBackupJob($this->param);

        // 3 返回结果到用户
        $this->success('', $return);
    }

    public function editBackupJob()
    {
        // 1 参数验证
        $this->checkParams('obs_backup_job_edit');

        // 2 请求转发到logic去处理
        $return = $this->logic()->editBackupJob($this->param);

        // 3 返回结果到用户
        if ($return[0]) {
            $this->success('', $return[1]);
        }
        $this->error($return[1]);
    }

    /**
     * 获取备份任务信息
     * @return void
     */
    public function getBackupTaskInfo()
    {

        // 1 参数验证
        $this->checkParams('files_info');

        // 2 请求转发到logic去处理
        $return = $this->logic()->getBackupTaskInfo($this->param['info_uuid']);

        // 3 返回结果到用户
        $this->success('', $return);
    }
}