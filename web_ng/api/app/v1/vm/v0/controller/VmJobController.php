<?php

namespace app\v1\vm\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          虚拟机管理 -- 之任务操作
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 9:57
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmJobController extends AuthBase
{
    /**
     * @var \app\v1\vm\v0\logic\VmJobController
     */
    private $logic;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->logic = new \app\v1\vm\v0\logic\VmJobController();
    }

    /**
     * 启动虚拟机
     * @return string
     */
    public function startVm(): string
    {
        $this->checkParams('vmOperate');
        return $this->logic()->startVm($this->param);
    }

    /**
     * 关闭虚拟机
     * @return string
     */
    public function stopVm(): string
    {
        $this->checkParams('vmOperate');
        return $this->logic()->stopVm($this->param);
    }

    /**
     * 挂起虚拟机
     * @return string
     */
    public function pauseVm(): string
    {
        $this->checkParams('vmOperate');
        return $this->logic()->pauseVm($this->param);
    }

    /**
     * 重启虚拟机
     * @return string
     */
    public function restartVm(): string
    {
        $this->checkParams('vmOperate');
        return $this->logic()->restartVm($this->param);
    }

    /**
     * 终止虚拟机
     * @return string
     */
    public function terminateVm(): string
    {
        $this->checkParams('vmOperate');
        return $this->logic()->terminateVm($this->param);
    }

    /**
     * 获取虚拟机磁盘列表
     * @return string
     */
    public function getVmDiskList(): string
    {
        $this->checkParams('vmOperate');
        $data = $this->logic()->getVmDiskList($this->param);
        $this->success('Get vm disk list', $data);
    }

    /**
     * 添加虚拟机到备份任务
     * @return string
     */
    public function addVmToBackupTask(): string
    {
        $this->checkParams('addToBackupJob');
        return $this->logic()->addVmToBackupTask($this->param);
    }

    /**
     * 启动任务 demo
     * @param string $taskuuid 数据
     * @return array
     */
    public function startJob(string $taskuuid = '')
    {
        if ($this->param['job_uuid']) {
            $taskuuid = $this->param['job_uuid'];
        }
        // 逻辑层转发
        return $this->logic->startJob($taskuuid, $this->param['start_type']);
    }

    /**
     * 停止任务 demo
     * @param string $taskuuid 数据
     * @return array
     */
    public function stopJob(string $taskuuid = '')
    {
        if ($this->param['job_uuid']) {
            $taskuuid = $this->param['job_uuid'];
        }
        // 逻辑层转发
        return $this->logic->stopJob($taskuuid);
    }

    /**
     * 删除任务 demo
     * @param string $taskuuid 数据
     * @return array
     */
    public function delJob(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->delJob($taskuuid);
    }

    /**
     * 选择虚拟机启动备份任务
     * @return string
     */
    public function startVMJob()
    {
        return $this->logic->startVMJob($this->param);
    }

    /**
     * 选择虚拟机从备份任务标记删除
     * @return string
     */
    public function deleteVMFromJob()
    {
        $this->checkParams('deleteVMFromJob');
        return $this->logic->deleteVMFromJob($this->param);
    }
}
