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
class MachineOsRecover extends AuthBase
{
    /**
    * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

     /**
     * 获取恢复树
     */
    public function getTimepointTree(){
        $info = $this->logic()->getTimepointTree($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
    
     /**
     * 异步获取恢复树
     */
    public function getSyncTimepoint(){
        $info = $this->logic()->getSyncTimepoint($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取所有可用恢复目标主机
     */
    public function getOptionIP(){
        $info = $this->logic()->getOptionIP($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }



    
    /**
     * 获取定时的恢复任务
     */
    public function createRecoverJob(){
        $info = $this->logic()->createRecoverJob($this->param);
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
     * 获取定时的主机网络信息
     */
    public function getNetworkofAgent(){
        $info = $this->logic()->getNetworkofAgent($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
    












}
