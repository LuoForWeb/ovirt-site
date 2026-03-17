<?php

namespace app\v1\volcdp\v0\controller;

use app\v1\common\controller\AuthBase;
class VolcdpTakeover extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }
    public function getBackupAgentNetworkInfo()
    {
        $data = $this->logic()->getBackupAgentNetworkInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取接管目标客户端
     */
    public function getTakeoverTargetHost()
    {
        $data = $this->logic()->getTakeoverTargetHost($this->param);
        return $this->success('',$data);
    }

    /**
     *  判断数据对应主机ip是否在线,
     */
    public function checkIpIsOnline()
    {
        $data = $this->logic()->checkIpIsOnline($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取选中客户端应用信息
     */
    public function getClientTakeoverAppInfo()
    {
        $data = $this->logic()->getClientTakeoverAppInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 创建手动接管作业
     */
    public function createTakeoverJob()
    {
        $data = $this->logic()->createTakeoverJob($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取指定客户端选择卷对应的备份数据集
     */
    public function getBackupSetGrid()
    {
        $data = $this->logic()->getBackupSetGrid($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取指定客户端对应的备份标签点信息
     */
    public function getBackupTagPointGrid()
    {
        $data = $this->logic()->getBackupTagPointGrid($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端备份集信息,取最新时间点
     */
    public function getClientBackupSetInfo()
    {
        $data = $this->logic()->getClientBackupSetInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 接管任务名
     */
    public function getVolCdpTakeoverTaskName()
    {
        $data = $this->logic()->getVolCdpTakeoverTaskName($this->param);
        return $this->success('',$data);
    }

}