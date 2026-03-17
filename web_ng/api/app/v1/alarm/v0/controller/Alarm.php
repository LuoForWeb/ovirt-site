<?php

namespace app\v1\alarm\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          系统告警
 * @author       liushuai@vinchin.com
 * @date         2023/10/24 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Alarm extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取任务告警
     * @return void
     */
    public function getJobAlarm()
    {
        if (!empty($this->param['alarm_id'])) {
            // 单个任务告警详情
            $return = $this->logic()->getJobAlarmDetail($this->param['alarm_id']);  // 逻辑层转发
        } else {
            //任务告警列表
            $return = $this->logic()->getJobAlarmList($this->param);  // 逻辑层转发
        }

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取任务告警
     * @return void
     */
    public function getJobAlarmSync()
    {
        //任务告警同步信息全部获取
        $return = $this->logic()->getJobAlarmSync($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 导出任务告警列表
     * @return void
     */
    public function exportJobAlarm()
    {
        //导出任务告警
        $return = $this->logic()->exportJobAlarm($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 导出系统告警列表
     * @return void
     */
    public function exportSystemAlarm()
    {
        //导出任务告警
        $return = $this->logic()->exportSystemAlarm($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 获取任务告警后台服务日志
     * @return void
     */
    public function getJobAlarmServerLog()
    {

        // 逻辑层转发
        $return = $this->logic()->getJobAlarmServerLog($this->param['job_uuid']);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 下载任务告警管理后台日志文件
     * @return void
     */
    public function downloadServerLog()
    {
        // 逻辑层转发
        $return = $this->logic()->downloadServerLog($this->param['id']);

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 得到系统告警
     */
    public function getSystemAlarm()
    {
        //获取接受到的参数
        if (!empty($this->param['system_uuid'])) {
            // 单个任务告警详情
            $info = $this->logic()->getSystemAlarmDetail($this->param['system_uuid']);  // 逻辑层转发
        } else {
            //获取脚本列表
            $info = $this->logic()->getSystemAlarmList($this->param);
        }
        
        if(!$info){
            return $this->error("", $info);
        }
        return $this->success("", $info);
    }

    /**
     * 删除系统告警
     */
    public function deleteSystemAlarm(){
        $return = $this->logic()->deleteSystemAlarm($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }

    /**
     * 删除任务告警
     */
    public function deleteTaskAlarm(){
        $return = $this->logic()->deleteTaskAlarm($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
    public function getJobAlarmModuleItem(){
        $return = $this->logic()->getJobAlarmModuleItem($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
    public function setTaskAlarmStatus(){
        $return = $this->logic()->setTaskAlarmStatus($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
    public function getSystemAlarmDetails(){
        $return = $this->logic()->getSystemAlarmDetails($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
    public function setSystemAlarmStatus(){
        $return = $this->logic()->setSystemAlarmStatus($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
    public function downLoadTaskLog(){
        $return = $this->logic()->downLoadTaskLog($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
    public function sendAlarmEmail(){
        $return = $this->logic()->sendAlarmEmail($this->param);  // 逻辑层转发

        if ($return) {
            $this->success('', $return);
        }
        $this->error();
    }
}
