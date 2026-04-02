<?php

namespace app\v1\cluster\v0\controller;

use app\v1\common\controller\AuthBase;

class Cluster extends AuthBase
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 配置集群信息
     * @return void
     */
    public function setClusterConfig()
    {
        $this->checkParams('set_cluster_config');
        $return = $this->logic()->setClusterConfig($this->param);
        $this->outputHandle($return);
    }

    /**
     * 获取集群配置信息
     * @return void
     */
    public function getClusterConfig()
    {
        $return = $this->logic()->getClusterConfig();
        $this->outputHandle($return);
    }

    /**
     * 启动集群
     * @return void
     */
    public function startCluster()
    {
        $return = $this->logic()->startCluster();
        $this->outputHandle($return);
    }

    /**
     * 停止集群
     * @return void
     */
    public function stopCluster()
    {
        $return = $this->logic()->stopCluster();
        $this->outputHandle($return);
    }

    /**
     * 设置主节点
     * @return void
     */
    public function setClusterMasterNode()
    {
        $this->checkParams('set_cluster_master_node');
        $return = $this->logic()->setClusterMasterNode(
            $this->param['source_node_uuid'],
            $this->param['target_node_uuid']
        );
        $this->outputHandle($return);
    }

    /**
     * 获取集群的操作日志
     * @return void
     */
    public function getClusterOperateLog()
    {
        $return = $this->logic()->getClusterOperateLog();
        $this->outputHandle($return);
    }
}
