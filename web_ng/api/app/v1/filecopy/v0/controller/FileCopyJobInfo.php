<?php

namespace app\v1\filecopy\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          文件同步 -- 任务信息
 * @author       wuxian@vinchin.com
 * @date         2024/7/17 16:15
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class FileCopyJobInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取文件同步任务所有信息（用于详情和修改）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getFileCopyBasicInfo()
    {
        $result = $this->logic()->getFileCopyBasicInfo($this->param);
        if ($result) {
            $this->success(xphp_get_lang('UI_FILE_COPY_GET_TASK_INFO'), $result);
        }
        $this->error(xphp_get_lang('UI_FILE_COPY_GET_TASK_INFO'), $result);
    }

    /**
     * 获取复制列表信息（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getFileCopyList($params = [])
    {
        $result = $this->logic()->getFileCopyList($this->param);
        if ($result) {
            $this->success(xphp_get_lang('UI_FILE_COPY_GET_LIST_INFO'), $result);
        }
        $this->error(xphp_get_lang('UI_FILE_COPY_GET_LIST_INFO'), $result);
    }

    /**
     * 获取历史任务列表信息（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getHistoryList($params = [])
    {
        $result = $this->logic()->getHistoryList($this->param);
        if ($result) {
            $this->success(xphp_get_lang('UI_FILE_COPY_GET_HIS_LIST_INFO'), $result);
        }
        $this->error(xphp_get_lang('UI_FILE_COPY_GET_HIS_LIST_INFO'), $result);
    }

    /**
     * 获取比对任务列表信息（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getCompareList($params = [])
    {
        $param = $this->param;
        if ($param['detail_flag']) {
            $result = $this->logic()->getCompareDetail($param);
        } else {
            $result = $this->logic()->getCompareList($param);
        }
        if ($result) {
            $this->success(xphp_get_lang('UI_FILE_COPY_GET_COMPARE_LIST_INFO'), $result);
        }
        $this->error(xphp_get_lang('UI_FILE_COPY_GET_COMPARE_LIST_INFO'), $result);
    }

    /**
     * 获取对比结果（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getCompareResult($params = [])
    {
        $result = $this->logic()->getCompareResult($this->param);
        if ($result["errorCode"] == 0) {
            $this->success(xphp_get_lang('UI_FILE_COPY_COMPARE_RESULT_TIPS'), $result);
        }
        $this->error(xphp_get_lang('UI_FILE_COPY_COMPARE_RESULT_TIPS') . xphp_get_lang('WEB_PUBLIC_FAILURE'), $result, $result["errorCode"]);
    }

    /**
     * 复制对比结果（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function copyCompareResult($params = [])
    {
        $result = $this->logic()->copyCompareResult($this->param);
        if ($result) {
            $this->success(xphp_get_lang('WEB_SYNC_FS_OP_CODE_SYNC_COMPARE_RESULT'), $result);
        }
        $this->error(xphp_get_lang('WEB_SYNC_FS_OP_CODE_SYNC_COMPARE_RESULT'), $result);
    }

    /**
     * 获取文件复制告警信息
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getFileCopyAlarm($params = [])
    {
        $result = $this->logic()->getFileCopyAlarm($this->param);
        if ($result) {
            $this->success(xphp_get_lang('WEB_SYSTEM_MONITER_GET_ALARM_MESSAGE'), $result);
        }
        $this->error(xphp_get_lang('WEB_SYSTEM_MONITER_GET_ALARM_MESSAGE'), $result);
    }

    /**
     * 下载跳过文件
     * @return array 下载跳过文件
     */
    public function downLoadPassData()
    {
        $this->logic()->downLoadPassData($this->param);
    }
}
