<?php

namespace app\v1\os\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          操作系统 -- 瞬时恢复
 * @author       lilingyu@vinchin.com
 * @date         2024/6/5 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsInstantRecover extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 获取瞬时恢复目标主机IP集合
     */
    public function getOptionIP(){
        $info = $this->logic()->getOptionIP($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取瞬时恢复缓存存储列表
     */
    public function getCatchStorage(){
        $info = $this->logic()->getCatchStorage($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取瞬时恢复任务名
     */
    public function getTaskName(){
        $info = $this->logic()->getTaskName($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 创建瞬时恢复任务
     */
    public function createInstantOSRecoverJob(){
        $info = $this->logic()->createInstantOSRecoverJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 迁移时连接测试（获取迁移目标主机信息）
     */
    public function osInstantlinkTest(){
        $info = $this->logic()->osInstantlinkTest($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取迁移时传输网络列表
     */
    public function getNetList(){
        $info = $this->logic()->getNetList($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 创建操作系统迁移任务
     */
    public function createOSMotionJob(){
        $info = $this->logic()->createOSMotionJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

}