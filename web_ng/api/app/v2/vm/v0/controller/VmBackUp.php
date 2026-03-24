<?php

namespace app\v2\vm\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          虚拟机管理 -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmBackUp extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建备份任务
     * @return string
     */
    public function createBackupJob()
    {
        $this->checkParams('createJob');
        return $this->logic()->createBackupJob($this->param);
    }

    /**
     * 获取虚拟机备份任务名
     * @return void
     */
    public function getVMBackupTaskName()
    {
        $this->checkParams('getBackupJobName');
        $data = $this->logic()->getVMBackupTaskName($this->param);
        $this->success('', $data);
    }

    /**
     * 修改备份任务
     * @return string
     */
    public function editBackupJob()
    {
        $this->checkParams('editJob');
        return $this->logic()->editBackupJobV2($this->param);
    }

    /**
     * 获取虚拟机备份任务信息
     * @return string
     */
    public function getBackupTaskAllInfo()
    {
        $this->checkParams('getAllInfo');
        $data = $this->logic()->getBackupTaskAllInfo($this->param['job_uuid']);
        $this->success('', $data);
    }

    /**
     * 获取虚拟化的备份配置
     * @return string
     */
    public function getBackupConfigs()
    {
        $this->checkParams('getBackupConfigs');
        $data = $this->logic()->getBackupConfigs($this->param['hypervisor_type']);
        $this->success('', $data);
    }
}
