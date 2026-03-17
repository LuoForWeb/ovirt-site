<?php

namespace app\v1\industry\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          行业合规报告相关控制器
 * @author       wuyihang@vinchin.com
 * @date         2024/11/4 10:31
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Index extends AuthBase
{
    /**
     * 客户端验证次数
     * @return json
     */
    public function clientReportInfo()
    {
        // 参数验证
        // $this->checkParams('view');
        $return = $this->logic()->clientReportInfo($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 报告统计
     * @return json
     */
    public function reportInfo()
    {
        // 参数验证
        // $this->checkParams('view');
        if ($this->param['count'] == true) {
            $return = $this->logic()->reportSummary($this->param);
        } else {
            $return = $this->logic()->getReportCountSummary($this->param);
        }

        if ($return['code'] == 0) {
            return $this->success('', $return);
        }

        return $this->error($return['msg']);
    }

    /**
     * 设备统计
     * @return json
     */
    public function getDeviceSummary()
    {
        // 参数验证
        // $this->checkParams('view');
        $return = $this->logic()->getDeviceSummary($this->param['report_uuid']);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }
        return $this->error($return['msg']);
    }

    /**
     * 消息统计
     * @return json
     */
    public function getMessageCount()
    {
        // 参数验证
        // $this->checkParams('view');
        $return = $this->logic()->getMessageCount($this->param);
        if ($return['code'] == 0) {
            return $this->success('', $return);
        }
        return $this->error($return['msg']);
    }
}
