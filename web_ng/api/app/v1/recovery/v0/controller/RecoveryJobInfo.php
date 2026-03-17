<?php

namespace app\v1\recovery\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          高级恢复：恢复、瞬时恢复、迁移、细粒度恢复 信息展示
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:37
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class RecoveryJobInfo extends AuthBase
{
    /**
     * 细粒度恢复任务详情
     * @return json
     */
    public function graininessJobDetail()
    {
        // 参数验证
        $this->checkParams('job_detail');

        // 逻辑层转发
        $return = $this->logic()->graininessJobDetail($this->param);

        return $this->success('', $return);
    }

    /**
     * 跨平台恢复任务详情
     * @return json
     */
    public function jobDetail()
    {
        // 参数验证
        $this->checkParams('platform_detail');

        // 逻辑层转发
        $return = $this->logic()->jobDetail($this->param);

        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }

        return $this->error($return['msg']);
    }

    /**
     * 瞬时恢复任务详情
     * @return json
     */
    public function instanceJobDetail()
    {
        // 参数验证
        $this->checkParams('job_detail');

        // 逻辑层转发
        $return = $this->logic()->instanceJobDetail($this->param);

        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }

        return $this->error($return['msg']);
    }

    /**
     * 迁移任务详情
     * @return json
     */
    public function migratesJobDetail()
    {
        // 参数验证
        $this->checkParams('job_detail');

        // 逻辑层转发
        $return = $this->logic()->migratesJobDetail($this->param);

        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }

        return $this->error($return['msg']);
    }

    /**
     * 获取客户端网络列表
     * @return json
     */
    public function agentNetworkList()
    {
        // 参数验证
       // $this->checkParams('recovery_agent_network');

        // 逻辑层转发
        $return = $this->logic()->agentNetworkList($this->param['agent_uuid']);

        return $this->success('', $return);
    }

    /**
     * 获取跨平台、瞬时恢复和迁移任务详情的对象列表（包括vm和整机的）
     * @return json
     */
    public function getJobObject()
    {
        // 参数验证
        // $this->checkParams('recovery_agent_network');

        // 逻辑层转发
        $return = $this->logic()->getJobObject($this->param);

        return $this->success('', $return);
    }

    /**
     * 获取跨平台、瞬时恢复和迁移任务详情的脚本详情（包括vm和整机的）
     * @return json
     */
    public function getJobScript()
    {
        // 参数验证
        // $this->checkParams('recovery_agent_network');

        // 逻辑层转发
        $return = $this->logic()->getJobScript($this->param);

        if ($return['code'] == -1) {
            return $this->error($return['msg']);
        }

        return $this->success('', $return['msg']);
    }

    /**
     * 获取虚拟化平台对应的磁盘总线类型和网络类型
     * @return json
     */
    public function diskNetworkList()
    {
        // 参数验证
        $this->checkParams('recovery_vm_disk_network');

        // 逻辑层转发
        $return = $this->logic()->diskNetworkList($this->param);

        return $this->success('', $return);
    }
}
