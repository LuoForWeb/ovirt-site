<?php

namespace app\v1\hadoop\v0\controller;

use app\v1\common\controller\AuthBase;
/**
 * note          hadoop -- 集群管理
 * @author       lilingyu@vinchin.com
 * @date         2023/9/14
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopCluster extends AuthBase
{
    /**
     * HadoopCluster constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取集群列表
     * 获取单个集群
     * @return unknown
     *
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     */
    public function getCluster(){
        //获取接收到的参数
        $data =  $this->param;
        if(!empty($data['cluster_uuid'])){
            $info = $this->logic()->getClusterInfo($this->param);
            return $this->success(xphp_get_lang('WEB_HADOOP_SERVER_GET_CLUSTER_SUCCESS'),$info);
        }else{
            //获取集群列表
            $info = $this->logic()->getCluster($this->param);
            return $this->success(xphp_get_lang('WEB_HADOOP_SERVER_GET_CLUSTER_LIST_SUCCESS'),$info);
        }
    }
    /**
     * 添加集群
     */
    public function addCluster(){
        //获取集群列表
        $info = $this->logic()->addCluster($this->param);
        $this->success('', $info);
    }
    
    /**
     * 修改集群
     */
    public function editCluster(){
        //获取集群列表
        $info = $this->logic()->editCluster($this->param);
        $this->success('', $info);
    }

    /**
     * 删除集群
     */
    public function deleteCluster(){
        //删除单个脚本或批量集群
        $info = $this->logic()->deleteCluster($this->param);
        $this->success('', $info);
    }

    /**
     * 更新集群自动配置时间
     */
    public function updateTime(){
        //删除单个脚本或批量集群
        $this->checkParams('refreshtime_rule');
        $info = $this->logic()->updateTime($this->param);
        return $this->success(xphp_get_lang('WEB_HADOOP_SERVER_UPDATE_ORAGNIZATION_AUTO_REFRESH_TIME'), $info);
    }
    
    /**
     * 获取集群自动配置时间
     */
    public function getTime(){
        //删除单个脚本或批量集群
        $info = $this->logic()->getTime($this->param);
        return $this->success(xphp_get_lang('WEB_HADOOP_SERVER_GET_ORAGNIZATION_AUTO_REFRESH_TIME'), $info);
    }
    /**
     * 刷新集群
     */
    public function refreshCluster(){
        //删除单个脚本或批量集群
        $info = $this->logic()->refreshCluster($this->param);
        $this->success('', $info);
    }
    /**
     * 获取集群名称
     */
    public function getClusterName(){
        //删除单个脚本或批量集群
        $info = $this->logic()->getClusterName();
        return $this->success(xphp_get_lang('WEB_HADOOP_SERVER_GET_CLUSTER_SUCCESS'),$info);
    }
    /**
     * 授权集群
     */
    public function authCluster(){
        //授权集群或者取消授权
        $this->checkParams('authflag_rule');
        $info = $this->logic()->authCluster($this->param);
        return $this->outputMsg($info['result'], $info['msg'], '', $info['errorCode']);
    }
}
