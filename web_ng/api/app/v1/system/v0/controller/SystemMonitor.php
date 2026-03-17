<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          系统监控获取
 * @author       liushuai@vinchin.com
 * @date         2023/10/13 14:17
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class SystemMonitor extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取系统基本信息
     * 获取内容为：基本信息，CPU内存信息，磁盘信息，网络信息等等
     * @return json
     */
    public function getSystemBasicInfo()
    {
        $info = $this->logic()->getBasicInfo($this->param);

        $this->success($info['message'], $info['data']);
    }

    /**
     * 获取系统基本信息
     * 获取系统监控信息
     * @return json
     */
    public function getSystemChart()
    {
        $info = $this->logic()->getSystemChart($this->param);

        $this->success($info['message'], $info['data']);
    }

    
    /**
     * 获取告警信息
     * @return json
     */
    public function getAlarmVal()
    {
        $info = $this->logic()->getAlarmVal($this->param);

        $this->success($info['message'], $info['data']);
    }

     /**
     * 获取告警信息
     * @return json
     */
    public function setAlarmVal()
    {
        $info = $this->logic()->setAlarmVal($this->param);

        $this->success($info['message'], $info['data']);
    }






}
