<?php

namespace app\v1\visualscreen\v0\controller;

use app\v1\common\controller\AuthBase;

class Visual extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取概览
     */
    public function getOverView()
    {
        $data = $this->logic()->getOverView($this->param);
        $this->success('', $data);
    }

    /**
     * 系统配置
     */
    public function getConfigure()
    {
        $data = $this->logic()->getConfigure($this->param);
        $this->success('', $data);
    }

    /**
     * 统计数据
     */
    public function getStatisticData()
    {
        $data = $this->logic()->getStatisticData($this->param);
        $this->success('', $data);
    }

    /**
     * 告警与任务
     */
    public function getCurrentTaskAndWarning()
    {
        $data = $this->logic()->getCurrentTaskAndWarning($this->param);
        $this->success('', $data);
    }

    /**
     * 授权信息
     */
    public function getSystemLisenceInfo()
    {
        $data = $this->logic()->getSystemLisenceInfo($this->param);
        $this->success('', $data);
    }

    /**
     * 任务列表
     */
    public function getCurrentTaskList()
    {
        $data = $this->logic()->getCurrentTaskList($this->param);
        $this->success('', $data);
    }

    /**
     * 除了概览以外的数据
     */
    public function getOtherView()
    {
        $data = $this->logic()->getOtherView($this->param);
        $this->success('', $data);
    }

    /**
     *
     */
    public function getNodeMonitor()
    {
        $data = $this->logic()->getNodeMonitor($this->param);
        $this->success('', $data);
    }

    /**
     * 系统时间
     */
    public function getDataSurvey()
    {
        $data = $this->logic()->getDataSurvey($this->param);
        $this->success('', $data);
    }

    /**
     * 大屏语言
     */
    public function getVisualLang()
    {
        $data = $this->logic()->getVisualLang($this->param);
        $this->success('', $data);
    }

    /**
     * 记录刷新日志
     */
    public function writeRefreshLog()
    {
        $data = $this->logic()->writeRefreshLog($this->param);
        $this->success('', $data);
    }

}
