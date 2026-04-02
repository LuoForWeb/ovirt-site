<?php

namespace app\v1\exchange\v0\controller;

use app\v1\common\controller\AuthBase;
use mysql_xdevapi\Result;

/**
 * note          office365（exchange） -- 之任务操作
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 9:57
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeJobController extends AuthBase
{
    /**
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 启动任务
     * @param string $taskuuid 数据
     * @return string
     */
    public function startJob(string $taskuuid)
    {
        // 逻辑层转发
        return (new \app\v1\exchange\v0\logic\ExchangeJobController())->startJob($taskuuid, $this->param['start_type']);
    }

    /**
     * 停止任务
     * @param string $taskuuid 数据
     * @return string
     */
    public function stopJob(string $taskuuid)
    {
        // 逻辑层转发
        return (new \app\v1\exchange\v0\logic\ExchangeJobController())->stopJob($taskuuid);
    }

    /**
     * 删除任务
     * @param string $taskuuid 数据
     * @return string
     */
    public function delJob(string $taskuuid)
    {

        // 逻辑层转发
        return (new \app\v1\exchange\v0\logic\ExchangeJobController())->delJob($taskuuid);
    }
}
