<?php

namespace app\v1\dbcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据库实时 -- 之任务操作
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 9:57
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpJobController extends AuthBase
{
    private $logic;

    /**
     * DbcdpJobController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->logic = new \app\v1\dbcdp\v0\logic\DbcdpJobController();
    }

    /**
     * 启动任务 demo
     * @param string $taskuuid 任务UUID
     * @return $return
     */
    public function startJob(string $taskuuid)
    {
        // 逻辑层转发
        return $this->logic->startJob($taskuuid, $this->param['start_type']);
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

    /**
     * node 修改回切配置
     * @param array $params 任务UUID
     * @return $return
     */
    public function editFailbackConfig($params = [])
    {
        // 参数验证
        $this->checkParams('start');

        // 逻辑层转发
        $return = $this->logic->startJob($params);

        if ($return) {
            $this->success();
        }
        $this->error();
    }

    /**
     * 停止任务
     * @param string $taskuuid 数据
     * @return string
     */
    public function stopJob(string $taskuuid)
    {
        // 逻辑层转发
        return $this->logic->stopJob($taskuuid);
    }

    /**
     * 暂停任务
     * @param string $taskuuid 数据
     * @return string
     */
    public function pauseTask(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->pauseTask($taskuuid);
    }

    /**
     * 启动接管
     * @return string
     */
    public function startTakeOver()
    {
        // 逻辑层转发jobs/start_takeover
        return $this->logic->startTakeOverJob($this->param);
    }

    /**
     * 停止接管
     * @return string
     */
    public function stopTakeOver()
    {
        return $this->logic->stopTakeOverJob($this->param);
    }

    /**
     * 启动回切
     * @return string
     */
    public function startFailback()
    {
        return $this->logic->startFailback($this->param['job_uuids']);
    }

    /**
     * 启用/禁用自动接管
     * @return string
     */
    public function switchAutoTakeover()
    {
        $return = $this->logic->switchAutoTakeover($this->param);
        if ($return) {
            $this->success();
        }
        $this->error();
    }

    /**
     * 暂停状态修改延时重放时间和文件缓存大小等配置
     * @return string
     */
    public function editHighConfig()
    {
        $return = $this->logic->editHighConfig($this->param);
        if ($return) {
            $this->success();
        }
        $this->error();
    }
}
