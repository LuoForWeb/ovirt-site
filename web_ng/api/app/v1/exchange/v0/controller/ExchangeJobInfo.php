<?php

namespace app\v1\exchange\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          office365（exchange） -- 之任务信息
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:12
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeJobInfo extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取当前任务的某个任务的基本信息
     * @return 返回任务基本信息
     */
    public function getBasicInfo()
    {
        $return = $this->logic()->getBasicInfo($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }

    /**
     * 获取当前任务的某个任务的任务流量
     * @return 返回任务基本信息
     */
    public function getTaskSpeed()
    {
        $return = $this->logic()->getTaskSpeed($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }

    /**
     * 获取当前任务的某个任务的任务流量
     * @return 返回任务基本信息
     */
    public function getExchangeDetail()
    {
        $return = $this->logic()->getExchangeDetail($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }

    /**
     * 获取当前任务的某个任务的任务流量
     * @return 返回任务基本信息
     */
    public function getExchangeHistory()
    {
        $return = $this->logic()->getExchangeHistory($this->param);  // 逻辑层转发
        if ($return) {
            return $this->success('', $return);
        }
        return $this->error('', $return);
    }

    /**
     * 下载跳过数据
     * @return array 下载跳过数据
     */
    public function downLoadPassData()
    {
        $param = $this->param;
        $data = $this->logic()->downLoadPassData($param);
        if (!empty($param['checkFile'])) {
            if ($data) {
                return $this->success(xphp_get_lang('WEB_M365_SERVER_DOWNLOAD_SKIP_DATA_SUCCESS'), $data);
            } else {
                return $this->error(xphp_get_lang('WEB_M365_SERVER_DOWNLOAD_SKIP_DATA_ERROR'), $data);
            }
        } else {
            return $data;
        }
    }
}
