<?php

namespace app\v1\os\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          操作系统 -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsRecover extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }



    /**
     * 获取恢复任务名称
     */
    public function getOSRecoverTaskName(){
        $info = $this->logic()->getOSRecoverTaskName($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取恢复目的地IP集合
     */
    public function getOptionIP(){
        $info = $this->logic()->getOptionIP($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

      /**
     * 获取恢复目的地IP集合
     */
    public function linkTest(){
        $info = $this->logic()->linkTest($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

     /**
     * 创建操作系统恢复任务
     */
    public function createOSRecoverJob(){
        $info = $this->logic()->createOSRecoverJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

}
