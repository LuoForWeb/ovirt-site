<?php

namespace app\v1\volcdp\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          卷实时 -- 之备份管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpBackUp extends AuthBase
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建备份任务 demo
     */
    public function createBackupJob()
    {
        $return = $this->logic()->createBackupJob($this->param);
        $this->success('',$return);

    }

    /**
     * 获取客户端tree
     */
    public function getVolCdpBackupAgentTree()
    {
        $data = $this->logic()->getVolCdpBackupAgentTree();
        return $this->success('',$data);
    }

    /**
     * 获取授权信息
     */
    public function getSysAuthFunc()
    {
        $data = $this->logic()->getSysAuthFunc();
        return $this->success('',$data);
    }

    /**
     * 更新客户端信息
     */
    public function updateAgentInfo()
    {
        $data = $this->logic()->updateAgentInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端应用信息
     */
    public function getVolCdpAppTree()
    {
        $data = $this->logic()->getVolCdpAppTree($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取指定客户端对应的卷信息
     */
    public function getHostVolinfo()
    {
        $data = $this->logic()->getHostVolinfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取选中设备网络信息
     */
    public function getHostNetworkInfo()
    {
        $data = $this->logic()->getHostNetworkInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端默认缓存路径
     */
    public function getAgentCachePath()
    {
        $data = $this->logic()->getAgentCachePath($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取接管授权情况（获取剩余可用的卷CDP接管授权个数）
     */
    public function verifyVolCdpTakeoverAuthNum()
    {
        $data = $this->logic()->verifyVolCdpTakeoverAuthNum($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取客户端备机
     */
    public function getStandbyHostInfo()
    {
        $data = $this->logic()->getStandbyHostInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 校验模块授权数是否充足，是择执行更新操作，否则提示错误信息
     */
    public function verifyingVolCdpAuthNum()
    {
        $data = $this->logic()->verifyingVolCdpAuthNum($this->param);
        return $this->success('', $data);
    }

    /**
     * 更新客户端下的所有应用信息/更新当个应用信息
     */
    public function updateAppInfo()
    {
        $data = $this->logic()->updateAppInfo($this->param);
        return $this->success('',$data);
    }

    /**
     * 监测当前输入IP在网络环境是否存在
     */
    public function checkIpExists()
    {
        $data = $this->logic()->checkIpExists($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取主机已配置的应用，封装成tree
     */
    public function getHostConfiguredAppTree()
    {
        $data = $this->logic()->getHostConfiguredAppTree($this->param);
        return $this->success('',$data);
    }

    /**
     * 获取创建备份任务名
     */
    public function getVolCdpBackupTaskName()
    {
        $data = $this->logic()->getVolCdpBackupTaskName($this->param);
        return $this->success('',$data);
    }

}
