<?php

namespace app\v1\verification\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          数据验证CDM -- 之任务操作
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 9:57
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VerificationJobController extends AuthBase
{
    /**
     * @var \app\v1\verification\v0\logic\VerificationJobController
     */
    private $logic;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->logic = new \app\v1\verification\v0\logic\VerificationJobController();
    }

    /**
     * 启动任务
     * @param array $params 参数
     * @return json
     */
    public function startJob(string $taskUuid)
    {


        // 逻辑层转发
        return $this->logic->startJob($taskUuid, $this->param['start_type']);

    }

    /**
     * 启动任务
     * @param array $params 参数
     * @return json
     */
    public function startSelectJob()
    {


        // 逻辑层转发
        return $this->logic->startSelectJob($this->param);

    }

    /**
     * 停止任务
     * @param array $params 参数
     * @return json
     */
    public function stopJob(string $taskUuid)
    {


        // 逻辑层转发
        return $this->logic->stopJob($taskUuid);

    }

    /**
     * 删除任务
     * @param array $params 参数
     * @return json
     */
    public function delJob(string $taskUuid)
    {


        // 逻辑层转发
        return $this->logic->delJob($taskUuid);

    }
}
