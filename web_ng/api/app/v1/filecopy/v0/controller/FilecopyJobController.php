<?php

namespace app\v1\filecopy\v0\controller;

use app\v1\common\controller\AuthBase;
use mysql_xdevapi\Result;

/**
 * note          文件同步 -- 任务操作
 * @author       wuxian@vinchin.com
 * @date         2024/7/17 16:15
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class FilecopyJobController extends AuthBase
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
       return (new \app\v1\filecopy\v0\logic\FilecopyJobController())->startJob($taskuuid, $this->param['start_type']);
    }

    /**
     * 停止任务
     * @param string $taskuuid 数据
     * @return string
     */
    public function stopJob(string $taskuuid)
    {
        // 逻辑层转发
        return (new \app\v1\filecopy\v0\logic\FilecopyJobController())->stopJob($taskuuid);
    }

    /**
     * 删除任务
     * @param string $taskuuid 数据
     * @return string
     */
    public function delJob(string $taskuuid)
    {

        // 逻辑层转发
        return (new \app\v1\filecopy\v0\logic\FilecopyJobController())->delJob($taskuuid);
    }
}
