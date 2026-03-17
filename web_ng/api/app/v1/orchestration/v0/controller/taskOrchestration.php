<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 任务编排管理控制
 * @Date: 2023-12-19 15:44:30
 * @LastEditTime: 2024-04-24 17:31:19
 * @Version: 2.0
 * @copyright: Copyright 2023 vinchin.com
 */

namespace app\v1\orchestration\v0\controller;

use app\v1\common\controller\AuthBase;

class taskOrchestration extends AuthBase
{
    /**
     * @description: 获取编排计划列表
     * @return {*}
     */
    public function getOrchestrationList()
    {
        $info = $this->logic()->getOrchestrationList($this->param);
        return $this->success('', $info);
    }
    /**
     * @description: 获取默认名称
     * @return {*}
     */
    public function getPlanName()
    {
        $info = $this->logic()->getPlanName($this->param);
        return $this->success('', $info);
    }
    /**
     * @description: 获取任务列表
     * @return {*}
     */
    public function getTaskList()
    {
        $info = $this->logic()->getTaskList($this->param);
        return $this->success('', $info);
    }
    /**
     * @description: 添加任务编排计划
     * @return {*}
     */
    public function addOrchestrationList()
    {
        $info = $this->logic()->addOrchestrationList($this->param);
        return $this->success('', $info);
    }
    /**
     * @description: 删除编排计划
     * @return {*}
     */
    public function deleteOrchestrationList()
    {
        $info = $this->logic()->deleteOrchestrationList($this->param);
        return $this->success('', $info);
    }
    /**
     * @description: 任务编排计划操作 1.启动策略 2.启动 3.停止
     * @return {*}
     */
    public function operateOrchestration()
    {
        $info = $this->logic()->operateOrchestration($this->param);
        return $this->success('', $info);
    }
    /**
     * @description: 获取详细信息
     * @return {*}
     */
    public function getPlanDetails()
    {
        $info = $this->logic()->getPlanDetails($this->param);
        return $this->success('', $info);
    }
    public function getHistoryList()
    {
        $info = $this->logic()->getHistoryList($this->param);
        return $this->success('', $info);
    }
}
