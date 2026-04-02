<?php

namespace app\v1\volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          卷实时 -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpJobInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 获取卷cdp模块启动任务对象详细信息
     * @author       liushuai@vinchin.com
     * @date         2023/11/10
     */
    public function getStartTaskObjectDetail()
    {
        //获取接受到的参数
        $data = $this->param;
        $info = $this->logic()->getStartTaskObjectDetail($this->param);
        if(!$info){
            return $this->error(xphp_get_lang('WEB_TASK_VOL_CDP_GET_TASK_OBJECT_FAIL'), $info);
        }
        return $this->success(xphp_get_lang('WEB_TASK_VOL_CDP_GET_TASK_OBJECT_SUCCESS'), $info);
    }

    /**
     * 获取任务历史网络配置
     */
    public function getTaskNetworkHistoryConf()
    {
        $data = $this->logic()->getTaskNetworkHistoryConf($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取卷CDP任务数据流向状态
     */
    public function getVolCdpTaskMapInfo()
    {
        $data = $this->logic()->getVolCdpTaskMapInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 任务详情: 得到卷CDP模块任务基本信息
     */
    public function getVolCdpBasicInfo()
    {
        $data = $this->logic()->getVolCdpBasicInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取接管脚本配置信息
     */
    public function getTakeoveScriptConf()
    {
        $data = $this->logic()->getTakeoveScriptConf($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取任务配置的接管网络信息
     */
    public function getTaskNetworkConfInfo()
    {
        $data = $this->logic()->getTaskNetworkConfInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 修改任务回切网络映射
     */
    public function modifyTaskTakeOverNetwork()
    {
        $data = $this->logic()->modifyTaskTakeOverNetwork($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取任务接管回切配置
     */
    public function getVolCdpTaskHostConf()
    {
        $data = $this->logic()->getVolCdpTaskHostConf($this->param);
        return $this->success('',$data);
    }

    /**
     * 得到添加存储的时候的可用节点列表,同时用户备份选择存储节点(新建备份任务,修改备份任务,系统配置等等)
     */
    public function getStorageNodeSelect()
    {
        $data = $this->logic()->getStorageNodeSelect($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取回切目标主机对应的卷信息
     */
    public function getAgentVolInfo()
    {
        $data = $this->logic()->getAgentVolInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取回切目标主机对应的磁盘信息
     */
    public function getAgentDiskInfo()
    {
        $data = $this->logic()->getAgentDiskInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 接管回切配置
     */
    public function takeOverCutBack()
    {
        $data = $this->logic()->takeOverCutBack($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取当前任务是否有进行接管回切配置
     */
    public function getTaskFailbackInfo()
    {
        $data = (new \app\v1\volcdp\v0\logic\VolcdpJobController())->getTaskFailbackInfo($this->param['task_uuid']);
        return $this->success('',$data);
    }

    /**
     * 创建手动标签并添加描述
     */
    public function createLablePoint()
    {
        $data = $this->logic()->createLablePoint($this->param);
        return $this->success('',$data);
    }

    /**
     * /**
     * @param $params 获取启动任务启动对象详细信息
     */
    public function getStartTaskObjectInfo()
    {
        $data = $this->logic()->getStartTaskObjectInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取卷/应用信息详情
     */
    public function getDetailsVolInfo()
    {
        $data = $this->logic()->getDetailsVolInfo($this->param);
        return $this->success('',$data);
    }
     /**
     * 获取任务配置的内嵌虚拟主机信息
     */
    public function getTaskVmTemplateConfig() {
        $data = $this->logic()->getTaskVmTemplateConfig($this->param);
        return $this->success('',$data);
     }
    
}
