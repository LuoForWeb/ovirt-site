<?php

namespace app\v1\k8s\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          容器 -- 集群管理
 * @author       liushuai@vinchin.com
 * @date         2023/4/14
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sCluster extends AuthBase
{
    /**
     * K8sCluster constructor.
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
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     */
    public function getCluster()
    {
        //获取接受到的参数
        $data = $this->param;
        if (!empty($data['cluster_uuid'])) {
            //获取单个集群详情
            $info = $this->logic()->getClusterThis($this->param);
            return $this->success(xphp_get_lang('WEB_KUBE_CLUSTER_GET_SINGLE_CLUSTER_DETAIL_SUCCESS'), $info);
        }

        $info = $this->logic()->getCluster($this->param);
        return $this->success(xphp_get_lang('WEB_KUBE_CLUSTER_GET_CLUSTER_LIST_SUCCESS'), $info);
    }

    /**
     * 添加集群三种方式
     * MANUAL(手动方式)  BY_SSH方式  BY_CONFIG方式
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     */
    public function addCluster()
    {
        //获取接受到的参数
        $data = $this->param;
        //获取添加类型
        $type = $data['type'];
        if ($type == 'BY_MANUAL') {
            //如果类型为手动连接方式
            $info = $this->logic()->addClusterManual($this->param);
            return $this->outputMsg($info['result'], $info['message'], $info['data'], $info['error_code']);
        } else {
            //如果是远程方式
            $info = $this->logic()->addClusterRemote($this->param);
            return $this->outputMsg($info['result'], $info['message'], $info['data'], $info['error_code']);
        }
    }

    /**
     * 删除集群
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     */
    public function deleteCluster()
    {
        $info = $this->logic()->deleteCluster($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 刷新集群
     * @author liushuai@vinchin.com
     * @date 2023/7/12
     */
    public function refreshCluster()
    {
        $info = $this->logic()->refreshCluster();
        if ($info['result']) {
            return $this->success(xphp_get_lang('WEB_KUBE_CLUSTER_REFRESH_CLUSTER_SUCCESS'));
        } else {
            return $this->error(xphp_get_lang('WEB_KUBE_CLUSTER_REFRESH_CLUSTER_FAILURE'));
        }
    }

    /**
     * 修改集群
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     */
    public function editCluster()
    {
        $info = $this->logic()->editCluster($this->param);
        if ($info) {
            return $this->success(xphp_get_lang('WEB_KUBE_CLUSTER_MODIFY_CLUSTER_SUCCESS'));
        } else {
            return $this->error(xphp_get_lang('WEB_KUBE_CLUSTER_MODIFY_CLUSTER_FAILURE'));
        }
    }

    /**
     * 获取集群下命名空间
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     */
    public function getNameSpace()
    {
        $info = $this->logic()->getNameSpace($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    public function getResourceBigGroup(){
        $info = $this->logic()->getResourceBigGroup($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取命名空间下应用
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     */
    public function getApp()
    {

        $info = $this->logic()->getApp($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取命名空间或应用下资源小分组
     * @author liushuai@vinchin.com
     * @date 2023/4/18
     */
    public function getResourceGroup()
    {

        $info = $this->logic()->getResourceGroup($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取资源小分组下资源列表
     * @author liushuai@vinchin.com
     * @date 2023/4/18
     */
    public function getResource()
    {

        $info = $this->logic()->getResource($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 从客户端获取资源详情
     * @author liushuai@vinchin.com
     * @date 2023/4/18
     */
    public function getResourceDetails()
    {

        $info = $this->logic()->getResourceDetails($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 从客户端获取资源详情
     * @author liushuai@vinchin.com
     * @date 2023/5/17
     */
    public function getNetworkInfo()
    {
        $info = $this->logic()->getNetworkInfo($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 从客户端获取资源详情
     * @author liushuai@vinchin.com
     * @date 2023/5/18
     */
    public function getClusterZtree()
    {
        $info = $this->logic()->getClusterZtree($this->param);
        return $this->success(xphp_get_lang('WEB_KUBE_CLUSTER_GET_CLUSTER_TREE_SUCCESS'), $info);
//            return $this->error('获取主节点端口信息失败', $info);
    }















}


