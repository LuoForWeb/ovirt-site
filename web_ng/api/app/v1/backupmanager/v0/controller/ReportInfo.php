<?php

namespace app\v1\backupmanager\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          报表数据获取 -- 集群管理平台
 * @author       jiangyongjie@vinchin.com
 * @date         2023/10/18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ReportInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取任务报表数据
     * @return void
     */
    public function getTaskReportData()
    {
        $info = $this->logic()->getTaskReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取存储报表数据
     * @return void
     */
    public function getStorageReportData()
    {
        $info = $this->logic()->getStorageReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取客户端报表数据
     * @return void
     */
    public function getClientReportData()
    {
        $info = $this->logic()->getClientReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取NAS模块报表数据
     * @return void
     */
    public function getNasReportData()
    {
        $info = $this->logic()->getNasReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取虚拟机模块报表数据
     * @return void
     */
    public function getVmReportData()
    {
        $info = $this->logic()->getVmReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取文件模块报表数据
     * @return void
     */
    public function getFileReportData()
    {
        $info = $this->logic()->getFileReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取数据库模块报表数据
     * @return void
     */
    public function getDbReportData()
    {
        $info = $this->logic()->getDbReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取操作系统报表数据
     * @return void
     */
    public function getOsReportData()
    {
        $info = $this->logic()->getOsReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }

    /**
     * 获取操作系统报表数据
     * @return void
     */
    public function getVolCdpReportData()
    {
        $info = $this->logic()->getVolCdpReportData($this->param);
        if (empty($info)) {
            $this->error();
        }
        $this->success('', $info);
    }
}
