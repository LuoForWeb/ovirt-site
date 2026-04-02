<?php

namespace app\v1\nas\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          NAS -- 之任务操作
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 9:57
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasJobController extends AuthBase
{
    /**
     * @var \app\v1\nas\v0\logic\NasJobController
     */
    private $logic;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->logic = new \app\v1\nas\v0\logic\NasJobController();
    }

    /**
     * 启动任务 demo
     * @param string $taskuuid 数据
     * @return string
     */
    public function startJob(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->startJob($taskuuid, $this->param['start_type']);
    }

    /**
     * 暂停任务 demo
     * @param string $taskuuid 数据
     * @return string
     */
    public function pauseTask(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->pauseTask($taskuuid);
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