<?php

namespace app\v1\os\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          操作系统 -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsBackUp extends AuthBase
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
    public function getOsBackupTree(){
        $info = $this->logic()->getOsBackupTree($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取备份中心磁盘分区信息
     */
    public function getOSBackupAgentInfo(){
        $info = $this->logic()->getOSBackupAgentInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

      /**
     * 获取创建备份任务的任务名称
     */
    public function getOSBackupTaskName(){
        $info = $this->logic()->getOSBackupTaskName($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取创建备份任务的任务名称
     */
    public function createOSBackupJob(){
        $info = $this->logic()->createOSBackupJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
    
     /**
     * 修改备份任务
     */
    public function editOSBackupJob(){
        $info = $this->logic()->editOSBackupJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
    
    

















}
