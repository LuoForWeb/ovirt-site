<?php

namespace app\v1\log\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          日志管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/6/5 16:16
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends AuthBase
{
    /**
     * 获取任务运行日志
     * @return string
     */
    public function getRunningJobLog()
    {
        $this->checkParams('running_log');
        if ($this->param['history_flag']) {
            $record = $this->logic()->getHistoryRunningJobLog($this->param);
        } else {
            $record = $this->logic()->getRunningJobLog($this->param);
        }
        $this->success('', $record);
    }

    /**
     * 获取历史任务运行日志
     * @return string
     */
    public function getHistoryRunningJobLog()
    {
        $record = $this->logic()->getHistoryRunningJobLog($this->param);

        $this->success('', $record);
    }

    /**
     * 获取任务日志
     * @return string
     */
    public function getJobLog()
    {
        // $this->checkParams('running_log');

        $record = $this->logic()->getJobLogList($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 删除任务日志
     * @return string
     */
    public function deleteJobLog()
    {
        // $this->checkParams('running_log');

        $record = $this->logic()->deleteJobLog($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 获取系统日志
     * @return string
     */
    public function getSystemLog()
    {
        // $this->checkParams('running_log');

        $record = $this->logic()->getSystemLogList($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 删除系统日志
     * @return void
     */
    public function deleteSystemLog()
    {
        // $this->checkParams('running_log');

        $record = $this->logic()->deleteSystemLog($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 导出任务日志
     * @return mixed
     */
    public function exportJobLog()
    {
        $record = $this->logic()->exportJobLog($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 导出系统日志
     * @return mixed
     */
    public function exportSystemLog()
    {
        $record = $this->logic()->exportSystemLog($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 获取高可用日志
     * @return void
     */
    public function getHaLogList()
    {
        $return = $this->logic()->getHaLogList($this->param);
        $this->outputHandle($return);
    }

    /**
     * 删除高可用日志
     * @return void
     */
    public function deleteHaLog()
    {
        $return = $this->logic()->deleteHaLog($this->param);
        $this->outputHandle($return);
    }

    /**
     * 导出全部高可用日志
     * @return void
     */
    public function exportAllHaLog()
    {
        $this->logic()->exportAllHaLog($this->param);
    }

    /**
     * 获取单个运行日志错误详情
     * @auther chenchao@vinchin.com
     * @return void
     */
    public function getRunningLogErrorDetail()
    {
        $return = $this->logic()->getRunningLogErrorDetail($this->param['running_logs_uuid']);
        $this->outputHandle($return);
    }

    /**
     * 获取单个运行日志脚本详情
     * @auther chenchao@vinchin.com
     * @return void
     */
    public function getRunningLogScriptDetail()
    {
        $return = $this->logic()->getRunningLogScriptDetail($this->param['running_logs_uuid']);
        $this->outputHandle($return);
    }

    /**
     * 获取节点的系统日志列表
     * @return void
     */
    public function getNodeSystemLogList()
    {
        $record = $this->logic()->getNodeSystemLogList($this->param);
        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }

    /**
     * 下载系统日志
     * @return void
     */
    public function downloadSystemLog()
    {
        $record = $this->logic()->downloadSystemLog($this->param);

        if ($record) {
            $this->success('', $record);
        }
        $this->error();
    }
}
