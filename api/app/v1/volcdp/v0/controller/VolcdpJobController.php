<?php

namespace app\v1\volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          卷实时 -- 之任务操作
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 9:57
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpJobController extends AuthBase
{
    /**
     * @var \app\v1\volcdp\v0\logic\VolcdpJobController
     */
    private $logic;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->logic = new \app\v1\volcdp\v0\logic\VolcdpJobController();
    }

    /**
     * 启动任务 demo
     * @param string $taskuuid 数据
     * @return string
     */
    public function startJob(string $taskuuid)
    {

        // 逻辑层转发 返回到入口去统一处理
        return $this->logic->startJob($taskuuid, $this->param['start_type']);
    }

    /**
     * 启动接管任务
     * @param string $taskuuid 数据
     * @return string
     */
    public function startTakeover(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->startTakeover($taskuuid);
    }

    /**
     * 停止任务 demo
     * @param string $taskuuid 数据
     * @return string
     */
    public function stopJob(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->stopJob($taskuuid);
    }

    /**
     * 停止接管任务 demo
     * @param string $taskuuid 数据
     * @return string
     */
    public function stopTakeover(string $taskuuid)
    {
        // 逻辑层转发
        return $this->logic->stopTakeoverJob($taskuuid);
    }

    /**
     * 切换自动接管配置 demo
     * @param array $params 数据
     * @return string
     */
    public function autoTakeoverOperation($params)
    {
        $params = [];
        $params['job_uuid'] = $this->param['job_uuid'][0];
        $params['enable_flag'] =$this->param['enable_flag'];
        // 逻辑层转发
        return $this->logic->autoTakeoverOperation($params);
    }

    /**
     * 启动回切任务 demo
     * @param string $taskuuid 数据
     * @return string
     */
    public function startFailback(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->startFailback($taskuuid);
    }

    /**
     * 删除任务 demo
     * @param string $taskuuid 数据
     * @return string
     */
    public function delJob(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->delJob($taskuuid);
    }
}
