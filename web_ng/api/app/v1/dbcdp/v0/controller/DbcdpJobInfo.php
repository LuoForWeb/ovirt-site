<?php

namespace app\v1\dbcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库实时 -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpJobInfo extends AuthBase
{
    /**
     * DbcdpJobInfo constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * note 获取任务各个阶段的进度信息
     * @return void $taskProgress 进度信息
     */
    public function getJobsProgress()
    {
        // 参数验证
        $this->checkParams('get_progress_info');

        // 逻辑层转发
        $taskProgress = $this->logic()->getJobsProgress($this->param['jobs_uuid']);

        if ($taskProgress) {
            $this->success('', $taskProgress);
        }
        $this->error();
    }

    /**
     * note 获取任务监控数据
     * @return $taskMonitor
     */
    public function getJobsMonitorData()
    {
        $this->checkParams('get_monitor_data');

        $taskMonitorData = $this->logic()->getJobsMonitorData($this->param);

        if ($taskMonitorData) {
            $this->success('', $taskMonitorData);
        }
        $this->error();
    }

    /**
     * note 获取回切配置信息
     * @return $return
     */
    public function getFailBackInfo()
    {
        // 参数验证
        $this->checkParams('get_fail_back_info');

        // 逻辑层转发
        $return = $this->logic()->getFailBackInfo($this->param);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * note 配置或修改同步任务回切配置
     * @return $return
     */
    public function editFailBackConf()
    {
        $this->checkParams('edit_fail_back_conf');

        $return = $this->logic()->editFailBackConf($this->param);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * note 获取任务详情公共接口没有的信息
     * @return $return
     */
    public function getBasicInfo()
    {
        $this->checkParams('get_basic_info');

        $return = $this->logic()->getBasicInfo($this->param);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * note 获取任务数据流向源/目标机的信息
     * @return $return
     */
    public function getMachineInfo()
    {
        $this->checkParams('get_machine_info');
        $return = $this->logic()->getMachineInfo($this->param);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * note 获取任务详情历史任务的details
     * @return $return
     */
    public function getHistoryDetails()
    {
        $return = $this->logic()->getHistoryDetails($this->param);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * note 获取回切源为集群时
     * @return $return
     */
    public function getFailbackSourceCluster()
    {
        $return = $this->logic()->getFailbackSourceCluster($this->param);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取手动接管信息
     * @return string
     */
    public function getTakeoverInfo()
    {
        $return = $this->logic()->getTakeoverInfo($this->param);
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取数据库实时任务的流量
     */
    public function getDbcdpJobFlow()
    {
        $return = $this->logic()->getDbcdpJobFlow($this->param['jobs_uuid']);  // 逻辑层转发
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 导入/导出详情
     */
    public function getImportExportDetails()
    {
        $return = $this->logic()->getImportExportDetails($this->param);  // 逻辑层转发
        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
}
