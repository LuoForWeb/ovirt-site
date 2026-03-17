<?php

namespace app\v1\k8s\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          容器 -- 之数据管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sData extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建备份任务 demo
     */
    public function getRestoreData(){
        $info = $this->logic()->getRestoreData($this->param);
        return $this->outputMsg($info['result'], "", $info['message'], $info['error_code']);
    }

    /**
     * 异步获取时间点
     */
    public function getRestoreTimepoint(){
        $info = $this->logic()->getRestoreTimepoint($this->param);
        return $this->outputMsg($info['result'], "", $info['message'], $info['error_code']);
    }
    /**
     * 获取整个时间点树
     */
    public function getRestoreZtree(){
        $info = $this->logic()->getRestoreZtree($this->param);
        return $this->outputMsg($info['result'], "", $info['message'], $info['error_code']);
    }


    /**
     * 获取备份数据表格
     */
    public function getRestoreTimepointTable(){
        $info = $this->logic()->getRestoreTimepointTable($this->param);
        return $this->success(xphp_get_lang('WEB_KUBE_DATA_GET_TIMEPOINT_DETAIL_SUCCESS'), $info);
    }

    /**
     * 获取备份数据表格
     */
    public function getRestoreTimepointData(){
        $info = $this->logic()->getRestoreTimepointData($this->param);
        return $this->success(xphp_get_lang('WEB_KUBE_DATA_GET_TIMEPOINT_DETAIL_SUCCESS'), $info);
    }

    /**
     * 获取时间点app或命名空间下的详细资源列表
     */
    public function getRestoreTimepointResource(){
        $info = $this->logic()->getRestoreTimepointResource($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取时间点app或命名空间下的详细资源列表
     */
    public function getRestoreTimepointResourcDetails(){
        $info = $this->logic()->getRestoreTimepointResourcDetails($this->param);
        if ($info['result']) {
            return $this->success(xphp_get_lang('WEB_KUBE_DATA_GET_RESOURCE_DETAIL_SUCCESS'), $info['info'], $info['error_code']);
        } else {
            return $this->error(xphp_get_lang('WEB_KUBE_DATA_GET_RESOURCE_DETAIL_FAILURE'), $info['message'], $info['error_code']);
        }
    }

    /**
     * 获取pvc以及其存储类
     */
    public function getRestorePvc(){
        $info = $this->logic()->getRestorePvc($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }


    /**
     * 给时间点设置备注
     */
    public function remarkTimepoint(){
        $info = $this->logic()->remarkTimepoint($this->param);
        if($info){
            return $this->success(xphp_get_lang('WEB_KUBE_DATA_ADD_REMARK_SUCCESS'));
        }else{
            return $this->error(xphp_get_lang('WEB_KUBE_DATA_ADD_REMARK_FAILURE'));
        }
    }

     /**
     * 添加星标
     */
    public function addStar(){
      return $this->logic()->addStar($this->param);
    }
    
     /**
     * 添加星标
     */
    public function deleteStar(){
       return $this->logic()->deleteStar($this->param);
    }



    

    












}
