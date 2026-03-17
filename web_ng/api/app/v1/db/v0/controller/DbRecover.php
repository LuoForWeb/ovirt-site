<?php

namespace app\v1\db\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库 -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbRecover extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建数据库恢复任务
     * @return void
     */
    public function createDbRecoveryJob()
    {
        $this->checkParams('create_db_recovery_job');
        $result = $this->logic()->createDbRecoveryJob($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取数据库恢复任务
     * @return void
     */
    public function getDbRecoveryJob()
    {
        $this->checkParams('get_db_recovery_job');
        $result = $this->logic()->getDbRecoveryJob($this->param['jobs_uuid']);
        $this->outputHandle($result);
    }

    /**
     * 修改数据库恢复任务
     * @return void
     */
    public function editDbRecoveryJob()
    {
        $this->checkParams('edit_db_recovery_job');
        $result = $this->logic()->editDbRecoveryJob($this->param);
        $this->outputHandle($result);
    }

    /**
     * 根据恢复时间获取Oracle恢复所使用的备份链
     * @return void
     */
    public function getOracleTimepointChainRecoveryTime()
    {
        $this->checkParams('get_oracle_timepoint_chain_recovery_time');
        $result = $this->logic()->getOracleTimepointChainRecoveryTime($this->param);
        $this->outputHandle($result);
    }

    /**
     * 获取SQL Server集群中活动节点的客户端信息
     * @return void
     */
    public function getSqlserverClusterActiveNodeAgentInfo()
    {
        $this->checkParams('get_sqlserver_cluster_active_node_agent_info');
        $result = $this->logic()->getSqlserverClusterActiveNodeAgentInfo($this->param['cluster_uuid']);
        $this->outputHandle($result);
    }
}
