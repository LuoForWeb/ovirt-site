<?php

namespace app\v1\complete_machine_volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

class CmRecover extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建恢复任务
     */
    public function createRecoverJob()
    {
        $data = $this->logic()->createRecoverJob($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取选中客户端可供恢复的时间区间
     */
    public function ClientBackupSetTimeRange()
    {
        $data = $this->logic()->ClientBackupSetTimeRange($this->param);
        return $this->success('',$data);
    }

    /**
     * 通过选择的客户端uuid获取其对应的备份集
     */
    public function getClientBackupSetNewTime()
    {
        $data = $this->logic()->getClientBackupSetNewTime($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端备份时间轴对应的数据
     */
    public function getAgentTimelineData()
    {
        $data = $this->logic()->getAgentTimelineData($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取恢复目标主机
     */
    public function getRecoveryTargetHost()
    {
        $data = $this->logic()->getRecoveryTargetHost($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端对应网卡信息（table）
     */
    public function getNetworkCardInfo()
    {
        $data = $this->logic()->getNetworkCardInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取获取整机恢复目标
     */
    public function getRecoveryTargetMachine()
    {
        $data = $this->logic()->getRecoveryTargetMachine();
        return $this->success('',$data);
    }

    public function getHardwareConfig()
    {
        $data = $this->logic()->getHardwareConfig($this->param);
        return $this->success('',$data);
    }

}