<?php

namespace app\v1\volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          卷实时 -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpRecover extends AuthBase
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
     * 获取生产有备份集的客户端
     */
    public function getDataSourceHostInfo()
    {
        $data = $this->logic()->getDataSourceHostInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取选择客户端及卷对应的标签点信息
     */
    public function getVolTagPointInfo()
    {
        $data = $this->logic()->getVolTagPointInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取选择客户端生成的事件信息
     */
    public function getAgentEventInfo()
    {
        $data = $this->logic()->getAgentEventInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 将选中的客户端对应的卷信息进行组装
     */
    public function getDataSourceVolInfo()
    {
        $data = $this->logic()->getDataSourceVolInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 取最新时间点
     */
    public function getClientBackupSetNewTime()
    {
        $data = $this->logic()->getClientBackupSetNewTime($this->param);
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
     * 获取选中客户端可供恢复的时间区间
     */
    public function ClientBackupSetTimeRange()
    {
        $data = $this->logic()->ClientBackupSetTimeRange($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取恢复目标客户端磁盘、卷及挂载点等信息
     */
    public function getRecoveryTargetHost()
    {
        $data = $this->logic()->getRecoveryTargetHost($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取指定时间区间对应的备份集信息
     */
    public function getAgentBkTimelineData()
    {
        $data = $this->logic()->getAgentBkTimelineData($this->param);
        return $this->success('',$data);
    }

    /**
     *  校验输入时间是否有效
     */
    public function verifyTimepointisValid()
    {
        $data = $this->logic()->verifyTimepointisValid($this->param);
        return $this->success('',$data);
    }

    /**
     * 创建恢复任务名
     */
    public function getVolCdpRecoverTaskName()
    {
        $data = $this->logic()->getVolCdpRecoverTaskName($this->param);
        return $this->success('',$data);
    }
}
