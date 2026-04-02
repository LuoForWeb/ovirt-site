<?php

namespace app\v1\complete_machine_volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

class CmTakeover extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建接管任务
     */
    public function createTakeoverJob()
    {
        $data = $this->logic()->createTakeoverJob($this->param);

        return $this->success('',$data);
    }

    /**
     * 获取接管备机
     */
    public function getTakeoverTargetHost()
    {
        $data = $this->logic()->getTakeoverTargetHost($this->param);

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
     *
     */
    public function getDataSourceVolInfo()
    {
        $data = $this->logic()->getDataSourceVolInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端备份集信息
     */
    public function getClientBackupSetVolInfo()
    {
        $data = $this->logic()->getClientBackupSetVolInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取主机网卡信息
     */
    public function loadAgentNetworkInfo()
    {
        $data = $this->logic()->loadAgentNetworkInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取备机网卡信息
     */
    public function loadStandbyNetworkInfo()
    {
        $data = $this->logic()->loadStandbyNetworkInfo($this->param);
        return $this->success('',$data);
    }

}