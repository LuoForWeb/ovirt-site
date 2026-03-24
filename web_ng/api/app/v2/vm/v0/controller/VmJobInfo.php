<?php

namespace app\v2\vm\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          虚拟机管理 -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmJobInfo extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取任务详情基本信息
     * @return string
     */
    public function getBasicInfo(): string
    {
        $this->checkParams('getBasicInfo');
        $data = $this->logic()->getBasicInfo($this->param);
        $this->success('', $data);
    }

    /**
     * 获取虚拟机任务监控运行日志
     * @return string
     */
    public function getVMRunningJobLog(): string
    {
        $this->checkParams('getBasicInfo');
        $data = $this->logic()->getVMRunningJobLog($this->param);
        $this->success('', $data);
    }

    /**
     * 获取虚拟机任务监控运行日志
     * @return string
     */
    public function getDetailsVM(): string
    {
        $this->checkParams('getBasicInfo');
        $data = $this->logic()->getJobVms($this->param);
        $this->success('', $data);
    }

    /**
     * 获取瞬时恢复任务的基本信息
     * @return string
     */
    public function getInstantBaseInfo(): string
    {
        $this->checkParams('getBasicInfo');
        $data = $this->logic()->getInstantBaseInfo($this->param);
        $this->success('', $data);
    }

    /**
     * 获取迁移任务的基本信息
     * @return string
     */
    public function getMotionBaseInfo(): string
    {
        $this->checkParams('getBasicInfo');
        $data = $this->logic()->getMotionBaseInfo($this->param);
        $this->success('', $data);
    }

    /**
     * 获取细粒度恢复任务详情
     * @return string
     */
    public function getVMGrainJobDetails(): string
    {
        $this->checkParams('getBasicInfo');
        $data = $this->logic()->getVMGrainJobDetails($this->param);
        $this->success('', $data);
    }

    /**
     * 获取细粒度恢复文件列表/搜索/加载更多
     * @return string
     */
    public function getVmGrainRecoveryFileDir(): string
    {
        $this->checkParams('getGranularFiles');
        $data = $this->logic()->getVmGrainRecoveryFileDir($this->param);
        $this->success('', $data);
    }

    /**
     * 检查细粒度恢复文件是否可以下载
     * @return string
     */
    public function downloadGrainRecoveryFileCheck(): string
    {
        $this->checkParams('download');
        return $this->logic()->downloadGrainRecoveryFileCheck($this->param);
    }

    /**
     * 下载细粒度恢复文件
     * @return string
     */
    public function downloadGrainRecoveryFile(): string
    {
        $this->checkParams('download');
        $this->logic()->downloadGrainRecoveryFile($this->param);
    }

    /**
     * 检查细粒度恢复目录是否可以下载
     * @return string
     */
    public function downloadGrainRecoveryDirCheck(): string
    {
        $this->checkParams('download');
        return $this->logic()->downloadGrainRecoveryDirCheck($this->param);
    }

    /**
     * 下载细粒度恢复目录
     * @return string
     */
    public function downloadGrainRecoveryDir(): string
    {
        $this->logic()->downloadGrainRecoveryDir();
    }

    /**
     * 获取细粒度恢复任务展示模式
     * @return void
     */
    public function getVmGrainRecoveryShowType(): string
    {
        $this->checkParams('getBasicInfo');
        $data = $this->logic()->getVmGrainRecoveryShowType($this->param);
        $this->success('', $data);
    }

    /**
     * 获取任务流量信息
     * @return string
     */
    public function getTaskSpeed(): string
    {
        $data = $this->logic()->getTaskSpeed($this->param);
        $this->success('', $data);
    }
}
