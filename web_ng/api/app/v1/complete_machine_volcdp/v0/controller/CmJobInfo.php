<?php

namespace app\v1\complete_machine_volcdp\v0\controller;
use app\v1\common\controller\AuthBase;

class CmJobInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 获取当前任务的某个任务的任务流量
     * @return 返回任务基本信息
     */
    public function getTaskSpeed()
    {
        $return = $this->logic()->getCmCdpTaskSpeed($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }
    /**
     * 获取任务配置信息及当前任务基本信息
     */
    public function getBasicInfo()
    {
        $return = $this->logic()->getBasicInfo($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }
    /**
     * 获取当前任务所有设备列表供回切配置使用
     * @return void
     */
    public function getFailbackDevicesList()
    {
        $data = $this->logic()->getFailbackDevicesList($this->param);
        return $this->success('',$data);
    }
    /**
     * 获取当前任务监控设备信息
     * @return void
     */
    public function  getTaskMonitorDeviceInfo(){
        $data = $this->logic()->getTaskMonitorDeviceInfo($this->param);
        return $this->success('',$data);
    }

    public function getCmCdpTaskHostConf(){
        $data = $this->logic()->getCmCdpTaskHostConf($this->param);
        return $this->success('',$data);
    }
    /**
     * 获取当前任务接管时间点
     * Summary of getTakeoverTaskTimePoint
     * @return void
     */
    public function getTakeoverTaskTimePoint(){
        $data = $this->logic()->getTakeoverTaskTimePoint($this->param);
        return $this->success('',$data);
    }
    /**
     * 提交回切配置相关信息
     * Summary of submitFailbackTask
     * @return void
     */
    public function submitFailbackTask(){
        $data = $this->logic()->submitFailbackTask($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取任务详情机器的状态
     */
    public function getCmCdpAgentStatus(){
        $data = $this->logic()->getCmCdpAgentStatus($this->param);
        return $this->success('',$data);
    }
    /**
     * 获取监控设备详情
     */
    public function getMonitorDeviceDetails(){
        $data = $this->logic()->getMonitorDeviceDetails($this->param);
        return $this->success('',$data);
    }
    /**
     * @param $params 获取启动任务启动对象详细信息
     */
    public function getStartTaskObjectInfo()
    {
        $data = $this->logic()->getStartTaskObjectInfo($this->param);
        return $this->success('', $data);
    }
    /**
     * Summary of getTaskNetworkConfInfo
     */
    public function getTaskNetworkConfInfo(){
        $data = $this->logic()->getTaskNetworkConfInfo($this->param);
        return $this->success('',$data);
    }
    /**
     * 修改任务网络配置信息
     */
    public function modifyTaskTakeOverNetwork(){
        $data = $this->logic()->modifyTaskTakeOverNetwork($this->param);
        return $this->success('',$data);
    }

    /**
     *  获取整机实时内嵌虚拟机配置
     */
    public function getTakeoverVmConfig(){
        $data = $this->logic()->getTakeoverVmConfig($this->param);
        return $this->success('',$data);
    }

    /**
     *  获取任务信息回填修改
     * @return void
     */
    public function getJobInfo()
    {
        $data = $this->logic()->getJobInfos($this->param);
        if (empty($data)) {
            return $this->error(xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN'));
        }
        return $this->success('', $data);
    }
}

?>