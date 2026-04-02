<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          资源 全局策略 logic
 * @author       chenyunfeng@vinchin.com
 * @date         2023/8/9
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */

class Strategies extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * @description: 获取策略组列表
     */
    public function getStrategyList()
    {
        $info = $this->logic()->getStrategyList($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 获取策略组先关任务列表
     */
    public function getTaskList()
    {
        $info = $this->logic()->getTaskList($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 删除时检查策略组关联任务并返回树，否则删除策略
     */
    public function checkTaskStrategy()
    {
        $info = $this->logic()->checkTaskStrategy($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 删除策略组
     */
    public function deleteStrategy()
    {
        $info = $this->logic()->deleteStrategy($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 得到策略组名称
     */
    public function getStrategyName()
    {
        $info = $this->logic()->getStrategyName($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 添加策略组
     */
    public function addStrategy()
    {
        $info = $this->logic()->addStrategy($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 修改策略-获取旧的策略信息
     */
    public function getOldStrategyInfo()
    {
        $info = $this->logic()->getOldStrategyInfo($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 检查策略是否被使用
     */
    public function editStrategyCheck()
    {
        $info = $this->logic()->editStrategyCheck($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 修改策略组
     */
    public function editStrategy()
    {
        $info = $this->logic()->editStrategy($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 获取策略组差异信息
     */
    public function getStrategyDiff()
    {
        $info = $this->logic()->getStrategyDiff($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 分发策略到备份任务
     */
    public function dispenseStrategy()
    {
        $info = $this->logic()->dispenseStrategy($this->param);
        return $this->success('', $info);
    }

    /**
     * @description: 获取模块相关的策略组列表
     * @return {*}
     */    
    public function getStrategySelect()
    {
        $info = $this->logic()->getStrategySelect($this->param);
        return $this->success('', $info);
    }
}
