<?php

namespace app\v1\job\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class JobInfo extends AuthBase
{
    /**
     * 获取模块列表
     * @return json
     */
    public function getModule()
    {
        // 参数验证
        $this->checkParams('get_module');
        // 逻辑转发
        $record = $this->logic()->getModule($this->param);
        // 返回数据
        $this->success('', $record);
    }

    /**
     * 根据任务名或语言包获取新的任务名
     * @return json
     */
    public function getValidName()
    {
        // 参数验证
        $this->checkParams('get_name');
        // 逻辑转发
        $record = $this->logic()->getValidName($this->param['job_name']);
        // 返回数据
        $this->success('', $record);
    }

    /**
     * 导出全部当前任务
     * @return mixed
     */
    public function exportAllCurrentJobs()
    {
        $record = $this->logic()->exportAllCurrentJobs($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 导出全部历史任务
     * @return void
     */
    public function exportAllHistoryJobs()
    {
        $record = $this->logic()->exportAllHistoryJobs($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 获取当前任务 列表/详情
     * @return json
     */
    public function getJob()
    {

        if (!empty($this->param['jobs_uuid'])) {
            // 单个
            $this->checkParams('get_job'); // 参数验证

            $return = $this->logic()->getJob($this->param['jobs_uuid']);  // 逻辑层转发
        } else {
            $this->checkParams('get_job_list'); // 参数验证
            $return = $this->logic()->getJobList($this->param);  // 逻辑层转发
        }

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取当前任务的某个任务的基本信息
     * @return json
     */
    public function getJobInfo()
    {

        $this->checkParams('get_job'); // 参数验证

        $return = $this->logic()->getJobInfo($this->param['jobs_uuid']);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取单个任务的流量
     * @return json
     */
    public function getJobFlow()
    {

        $this->checkParams('get_job'); // 参数验证

        $return = $this->logic()->getJobFlow($this->param['jobs_uuid']);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取历史任务 列表/详情
     * 获取单个任务的历史任务列表
     * @return json
     */
    public function getHistory()
    {

        if (!empty($this->param['history_uuid'])) {
            // 单个历史任务详情
            $this->checkParams('history'); // 参数验证

            $return = $this->logic()->getHistory($this->param); // 逻辑层转发
        } elseif (!empty($this->param['jobs_uuid'])) {
            // 获取单个任务的历史任务列表
            $this->checkParams('job_history'); // 参数验证
            $return = $this->logic()->getJobHistory($this->param); // 逻辑层转发
        } else {
            // 获取历史任务列表
            $this->checkParams('get_history_list'); // 参数验证
            // 逻辑层转发
            $return = $this->logic()->getHistoryList($this->param);
        }

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取每个小时任务占用个数列表
     * @return json
     */
    public function getTimeCrowdList()
    {
        $return = $this->logic()->getTimeCrowdList($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 初始化策略选择下拉框
     * @return json
     */
    public function getStrategySelect()
    {
        $this->checkParams('jobs_strategy_list'); // 参数验证
        $return = $this->logic()->getStrategySelect($this->param['type']);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 时间点密码验证
     * @return json
     */
    public function checkEncryptPass()
    {
        $info = $this->logic()->checkEncryptPass($this->param);
        return $this->outputMsg($info['success'], $info['message'], $info['data'], $info['code']);
    }

    /**
     * 获取挂起任务列表
     * @return json
     */
    public function getPending()
    {

        // 逻辑转发
        $record = $this->logic()->getPending($this->param);
        // 返回数据
        $this->success('', $record);
    }
}
