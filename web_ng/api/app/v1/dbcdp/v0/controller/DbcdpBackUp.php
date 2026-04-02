<?php

namespace app\v1\dbcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库实时 -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpBackUp extends AuthBase
{
    /**
     * DbcdpBackUp constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建同步任务
     * @return $createMsg
     */
    public function createBackupJob()
    {
        //参数验证
        $this->checkParams('create_sync_job');

        //业务逻辑
        $createMsg = $this->logic()->createBackupJob($this->param);
        if ($createMsg) {
            $this->success(xphp_get_lang('UI_DB_CDP_BACKUP_CREATE_REPLICATION_TASK_SUCCESS'), $createMsg);
        }
        $this->error();
    }

    /**
     * 获取客户端的网卡信息
     * @return $return
     */
    public function getHostNetworkInfo()
    {
        // 参数验证
        $this->checkParams('network');

        //业务逻辑
        $res = $this->logic()->getHostNetworkInfo($this->param);
        if (!$res) {
            $this->error(xphp_get_lang('UI_DB_CDP_BACKUP_FAILED_TO_QUERY_NETWORK_INTERFACE_INFORMATION'), $res);
        }
        $this->success(xphp_get_lang('UI_DB_CDP_BACKUP_SUCCESSFULLY_QUERIED_NETWORK_INTERFACE_INFORMATION'), $res);
    }

    /**
     * 扫描ip(源端为集群时)
     * @return $return
     */
    public function scanIp()
    {

        // 业务逻辑
        $res = $this->logic()->scanIp($this->param);
        if (!$res) {
            $this->error(xphp_get_lang('UI_DB_CDP_BACKUP_SUCCESSFULLY_SCANNED_IP_ADDRESSES'), $res);
        }
        $this->success(xphp_get_lang('UI_DB_CDP_BACKUP_FAILED_TO_SCAN_IP_ADDRESSES'), $res);
    }

    /**
     * 源机为集群时的网卡信息
     */
    public function getClusterNetworkInfo()
    {
        // 业务逻辑
        $res = $this->logic()->getClusterNetworkInfo($this->param);
        if (!$res) {
            $this->error(xphp_get_lang('UI_DB_CDP_BACKUP_FAILED_TO_QUERY_NETWORK_INTERFACE_INFORMATION'), $res);
        }
        $this->success(xphp_get_lang('UI_DB_CDP_BACKUP_SUCCESSFULLY_QUERIED_NETWORK_INTERFACE_INFORMATION'), $res);
    }

    /**
     *
     * 修改同步任务
     * @return $msg : 执行修改操作结果
     */
    public function editBackupJob()
    {
        //业务逻辑
        $editMsg = $this->logic()->editBackupJob($this->param);

        $opName = xphp_get_lang('WEB_DB_CDP_MODIFY_SYNC_TASK');
        if ($editMsg) {
            $this->success($opName, $editMsg);
        }
        $this->error();
    }

    /**
     * 客户端是否有任务运行
     *
     */
    public function taskExist()
    {
        $data = $this->logic()->taskExist($this->param);
        //
        if (!$data) {
            $this->error(xphp_get_lang('UI_DB_CDP_BACKUP_NO_TASKS_ARE_RUNNING_ON_THIS_CLIENT'));
        }
        $this->success(xphp_get_lang('UI_DB_CDP_BACKUP_THERE_ARE_TASKS_RUNNING_ON_THIS_CLIENT'),$data);
    }

    /**
     * 修改同步任务
     * @return $msg : 执行修改操作结果
     */
    public function editBackupJobConf()
    {
        //业务逻辑
        $editMsg = $this->logic()->editBackupJobConf($this->param);

        $opName = xphp_get_lang('UI_DB_CDP_CREATE_EDIT_JOB');
        if ($editMsg) {
            $this->success($opName, $editMsg);
        }
        $this->error();
    }

    public function loadInstanceDatabase()
    {
        $ret = $this->logic()->loadInstanceDatabase($this->param);
        $this->outputHandle($ret);
    }

    /**
     * 获取剩余授权是否足够
     */
    public function getAuthRemainEnough()
    {
        $data = $this->logic()->getAuthRemainEnough($this->param);
        if (!$data) {
            $this->error(xphp_get_lang('UI_DB_CDP_AUTH_INSUFFICIENT_TIPS'));
        }
        $this->success('',$data);
    }
}
