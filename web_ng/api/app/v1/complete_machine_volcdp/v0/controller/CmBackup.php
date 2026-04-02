<?php

namespace app\v1\complete_machine_volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

class CmBackup extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取客户端tree
     */
    public function getCompleteMachineVolcdpAgentTree()
    {
        $data = $this->logic()->getCompleteMachineVolcdpAgentTree();
        return $this->success('',$data);
    }

    /**
     * 获取模块授权信息
     */
    public function verifyingCompleteMachineVolcdpAuthInfo()
    {
        $data = $this->logic()->verifyingCompleteMachineVolcdpAuthInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取应急接管授权信息
     */
    public function verfiyEmergencyTakeoverAuthInfo()
    {
        $data = $this->logic()->verfiyEmergencyTakeoverAuthInfo($this->param);
        return $this->success('',$data);
    }
    /**
     * 更新客户端信息
     * @param array agent_uuid
     */
    public function updateAgentInfo()
    {
        $data = $this->logic()->updateAgentInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取选择客户端对应的应用tree
     * @param array agent_uuid agent_name agent_ip
     */
    public function getCompleteMachineVolcdpAppTree()
    {
        $data = $this->logic()->getCompleteMachineVolcdpAppTree($this->param);
        return $this->success('',$data);
    }

    /**
     * 更新客户端的下的所有/单个应用信息
     */
    public function updateAppInfo()
    {
        $data = $this->logic()->updateAppInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端网卡信息
     */
    public function getHostNetworkInfo()
    {
        $data = $this->logic()->getHostNetworkInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端缓存文件路径
     */
    public function getAgentCachePath()
    {
        $data = $this->logic()->getAgentCachePath($this->param);
        return $this->success('',$data);
    }
    /**
     * Summary of getNodeNetworkList
     */
    public function getNodeNetworkList(){
        $data = $this->logic()->getNodeNetworkList($this->param);
        return $this->success('',$data);
    }
    /**
     * 校验备机应用是否匹配
     * @return void
     */
    public function checkStandbyApp(){
        $data = $this->logic()->checkStandbyApp($this->param);
        return $this->success('',$data);
    }
    /**
     * 获取模块默认任务名
     */
    public function getDefaultTaskName()
    {
        $data = $this->logic()->getDefaultTaskName($this->param);
        return $this->success('',$data);
    }

    public function createBackupJob(){
        $data = $this->logic()->createBackupJob($this->param);
        return $this->success('',$data);
    }

}