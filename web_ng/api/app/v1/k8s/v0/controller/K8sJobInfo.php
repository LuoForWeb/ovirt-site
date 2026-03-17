<?php

namespace app\v1\k8s\v0\controller;

use app\v1\common\controller\AuthBase;


/**
 * note          容器 -- 之任务信息
 * @author       liushuai@vinchin.com
 * @date         2023/6/21 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sJobInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }



    /**
     * 获取容器任务基本信息
     */
    public function getk8sJobBasicInfo(){
        $info = $this->logic()->getk8sJobBasicInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取容器单个历史任务详情
     */
    public function getk8sJobHistoryInfo(){
        $info = $this->logic()->getk8sJobHistoryInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取容器单个任务的命名空间
     */
    public function getTaskNamespace(){
        $info = $this->logic()->getTaskNamespace($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
    /**
     * 获取容器单个任务的app
     */
    public function getTaskApp(){
        $info = $this->logic()->getTaskApp($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    public function getCurrentObject(){
        $info = $this->logic()->getCurrentObject($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }
    /**
     * 获取容器单个任务的pvc
     */
    public function getTaskPvc(){
        $info = $this->logic()->getTaskPvc($this->param);
        return $this->success(xphp_get_lang('WEB_KUBE_JOB_INFO_GET_PVC_SUCCESS'), $info);
    }
    /**
     * 获取容器单个任务的资源
     */
    public function getTaskResource(){
        $info = $this->logic()->getTaskResource($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取容器单个任务的备份信息(基本用于修改用)
     */
    public function getTaskBackupInfo(){
        $info = $this->logic()->getTaskBackupInfo($this->param);
        return $this->success(xphp_get_lang('WEB_KUBE_JOB_INFO_GET_BACKUP_INFO_SUCCESS'), $info);
    }




}
