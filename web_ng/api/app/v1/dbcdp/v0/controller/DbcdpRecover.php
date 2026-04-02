<?php

namespace app\v1\dbcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库实时 -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpRecover extends AuthBase
{
    /**
     * DbcdpRecover constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建恢复任务
     * @return $return
     */
    public function createRestoreJob()
    {
        // 参数验证
        $this->checkParams('create_recover_job');

        // 业务逻辑
        $return = $this->logic()->createRecoverJob($this->param);

        //创建恢复任务控制码，确定键位后通过配置文件读取
        $opName = xphp_get_lang('WEB_DB_CDP_CREATE_RECOVER_TASK');

        if ($return) {
            $this->success($opName, $return);
        }
        $this->error();
    }

    /**
     * 获取事件信息
     * 获取选定事件详情
     * @return $return
     */
    public function getTransactionInfo()
    {
        $return = $this->logic()->getTransactionInfo($this->param);
        if ($return) {
            $this->success('success', $return);
        }
        $this->error();
    }

    /**
     * 获取事件详情
     * @return $return
     */
    public function getEventDetail()
    {
        $return = $this->logic()->getEventDetail($this->param);
        if ($return) {
            $this->success('success', $return);
        }
        $this->error();
    }


    /**
     *获取可恢复时间范围
     * @return $return
     */
    public function getRestoreTimeRange()
    {
        $this->checkParams('restore_data_time_range');

        $return = $this->logic()->getRestoreTimeRange($this->param);

        if ($return) {
            $this->success('success', $return);
        }
        $this->error();
    }

    /**
     * 获取指定时间区间的数据流量信息
     * @return string $return
     */
    public function getAgentBkTimelineData()
    {
//        $this->checkParams('restore_data_uuid');
        $return = $this->logic()->getAgentBkTimelineData($this->param['restore_data_uuid'], $this->param['time_unit'], $this->param['task_uuid']);

        if ($return) {
            $this->success('success', $return);
        }
        $this->error();
    }
}
