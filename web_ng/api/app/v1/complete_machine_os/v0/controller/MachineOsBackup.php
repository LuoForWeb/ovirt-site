<?php

namespace app\v1\complete_machine_os\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          操作系统 -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class MachineOsBackup extends AuthBase
{
    /**
    * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

     /**
     * 获取备份树
     */
    public function getMachineOsBackupTree(){
        $info = $this->logic()->getMachineOsBackupTree($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

     /**
     * 获取定时或者实时的树形结构
     */
    public function getDiskTree(){
        $info = $this->logic()->getDiskTree($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取定时的备份任务名称
     */
    public function getMachineOsTaskName(){
        $info = $this->logic()->getMachineOsTaskName($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


    /**
     * 创建备份任务
     */
    public function createBackupJob(){
        $info = $this->logic()->createBackupJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }



    /**
     * 获取任务信息
     */
    public function getJobInfo(){
        $info = $this->logic()->getJobInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取整机定时恢复的目标信息
     */
    public function getRecoverTargetInfo(){
        $info = $this->logic()->getRecoverTargetInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


    /**
     * 获取整机定时恢复的目标信息
     */
    public function getBasicInfo(){
        $info = $this->logic()->getBasicInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取整机定时恢复的目标信息
     */
    public function getDetailsHostList(){
        $info = $this->logic()->getDetailsHostList($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

     /**
     * 获取整机定时详请主机列表
     */
    public function getMachineOsHostList(){
        $info = $this->logic()->getMachineOsHostList($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取整机定时任务流量图
     */
    public function getTaskSpeed(){
        $info = $this->logic()->getTaskSpeed($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取脚本内容
     */
    public function getScriptContent(){
        $info = $this->logic()->getScriptContent($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 启动各种策略任务
     */
    public function startOSJob(){
        $info = $this->logic()->startOSJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取客户端信息
     */
    public function getClientInfo(){
        $info = $this->logic()->getClientInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
















}
