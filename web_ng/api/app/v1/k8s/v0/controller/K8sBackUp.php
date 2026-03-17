<?php

namespace app\v1\k8s\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          容器 -- 之备份管理
 * @author       liushuai@vinchin.com
 * @date         2023/4/18 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sBackUp extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建备份任务 demo
     */
    public function createBackupJob(){
        $info = $this->logic()->createBackupJob($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取备份任务名
     */
    public function getK8sBackupTaskName(){
        $info = $this->logic()->getK8sBackupTaskName($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


     /**
     * 获取恢复任务名
     */
    public function getK8sRecoverTaskName(){
        $info = $this->logic()->getK8sRecoverTaskName($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

}
