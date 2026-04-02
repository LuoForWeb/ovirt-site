<?php

namespace app\v1\recovery\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          高级恢复：恢复、瞬时恢复、迁移、细粒度恢复操作
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:35
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class RecoveryJobController extends AuthBase
{
    /**
     * @var \app\v1\recovery\v0\logic\RecoveryJobController
     */
    private $logic;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->logic = new \app\v1\recovery\v0\logic\RecoveryJobController();
    }

    /**
     * 启动任务
     * @param string $taskuuid 任务uuid
     * @return json
     */
    public function startJob(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->startJob($taskuuid, $this->param['start_type']);
    }

    /**
     * 停止任务
     * @param string $taskuuid 任务uuid
     * @return json
     */
    public function stopJob(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->stopJob($taskuuid);
    }

    /**
     * 删除任务
     * @param string $taskuuid 任务uuid
     * @return json
     */
    public function delJob(string $taskuuid)
    {

        // 逻辑层转发
        return $this->logic->delJob($taskuuid);
    }

    /**
     * 停止瞬时恢复读写
     * @return json
     */
    public function stopInstanceJobOperate()
    {
        // 参数验证
        $this->checkParams('stop_instant_operation');

        // 逻辑层转发
        $return = $this->logic->stopInstanceJobOperate($this->param['job_uuid']);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 创建细粒度恢复任务
     * @return json
     */
    public function createGraininessJob()
    {
        // 参数验证
        $this->checkParams('graininess_job');

        // 逻辑层转发
        $return = $this->logic->createGraininessJob($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 创建无代理跨平台恢复任务
     * @return json
     */
    public function createAgentlessisJob()
    {
        // 参数验证
        $this->checkParams('agentlessis_job');

        // 逻辑层转发
        $return = $this->logic->createAgentlessisJob($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 创建有代理跨平台恢复任务
     * @return json
     */
    public function createAgentJob()
    {
        // 参数验证
        $this->checkParams('agent_job');

        // 逻辑层转发
        $return = $this->logic->createAgentJob($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 创建无代理瞬时恢复任务
     * @return json
     */
    public function createInstanceLessJob()
    {
        // 参数验证
        $this->checkParams('instance');

        // 逻辑层转发
        $return = $this->logic->createInstanceLessJob($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 创建有代理瞬时恢复任务
     * @return json
     */
    public function createInstanceJob()
    {
        // 参数验证
        $this->checkParams('instance');

        // 逻辑层转发
        $return = $this->logic->createInstanceJob($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 创建无代理迁移任务
     * @return json
     */
    public function createMigratesLessJob()
    {
        // 参数验证
        $this->checkParams('migrates_less');

        // 逻辑层转发
        $return = $this->logic->createMigratesLessJob($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 创建有代理迁移任务
     * @return json
     */
    public function createMigratesJob()
    {
        // 参数验证
        $this->checkParams('migrates_less');

        // 逻辑层转发
        $return = $this->logic->createMigratesJob($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 跨平台恢复任务详情
     * @return json
     */
    public function convertPoints()
    {


        // 逻辑层转发
        $return = $this->logic()->convertPointsVerify($this->param['agent_uuid'], $this->param['timepoint_uuid']);

        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }

        return $this->error($return['msg']);
    }
}
