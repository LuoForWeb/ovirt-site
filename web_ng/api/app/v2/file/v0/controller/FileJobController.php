<?php

namespace app\v2\file\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          文件 -- 之任务操作
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 9:57
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class FileJobController extends AuthBase
{
  
    private $logic;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->logic = new \app\v2\file\v0\logic\FileJobController();
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
     * 操作按钮-启动单个对象备份任务
     * @return null
     */
    public function startBackupJob()
    {
        $return = $this->logic()->startBackupJob($this->param);

        if ($return) {
            return $this->success('', $return);
        }

        return $this->error('', $return);
    }
}
