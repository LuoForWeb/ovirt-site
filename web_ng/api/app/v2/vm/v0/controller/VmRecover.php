<?php

namespace app\v2\vm\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          虚拟机管理 -- 之恢复管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:15
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmRecover extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取恢复目标虚拟化平台，老方法getRecoverHost
     * @return string
     */
    public function getRecoverVcenter(): string
    {
        $this->checkParams('getRecoverVcenter');
        $data = $this->logic()->getRecoverVcenter($this->param);
        $this->success('', $data);
    }

    /**
     * 异步获取恢复目标虚拟化平台下的主机/集群
     * @return string
     */
    public function getSyncRecoveryVcenter(): string
    {
        $this->checkParams('asyncGetVcenter');
        $data = $this->logic()->getSyncRecoveryVcenter($this->param);
        $this->success('', $data);
    }

    /**
     * 获取xhere块存储策略
     * @return string
     */
    public function getXhereVolumePolicyList(): string
    {
        $this->checkParams('getXhereVolumePolicy');
        $data = $this->logic()->getXhereVolumePolicyList($this->param);
        $this->success('', $data);
    }

    /**
     * 获取openstack的网络和存储配置
     * @return string
     */
    public function getOpenStackNetworkAndStorage(): string
    {
        $this->checkParams('getOpenStackConfig');
        $data = $this->logic()->getOpenStackNetworkAndStorage($this->param);
        $this->success('', $data);
    }

    /**
     * 获取私有云可用域
     * @return string
     */
    public function getOpenStackAvailableDomain(): string
    {
        $this->checkParams('getOpenStackConfig');
        $data = $this->logic()->getOpenStackAvailableDomain($this->param);
        $this->success('', $data);
    }

    /**
     * 获取宿主机网络和存储配置
     * @return string
     */
    public function getNetworkAndStorage(): string
    {
        $this->checkParams('getHostConfig');
        $data = $this->logic()->getNetworkAndStorage($this->param);
        $this->success('', $data);
    }

    /**
     * 获取虚拟机备份时间点配置
     * @return string
     */
    public function getVMConfigInfo(): string
    {
        //        $this->checkParams('getVMConfigInfo');
        $data = $this->logic()->getVMConfigInfoV2($this->param);
        $this->success('', $data);
    }

    /**
     * 获取可用的恢复任务名
     * @return string
     */
    public function getVMRecoverTaskName(): string
    {
        $this->checkParams('getRecoverJobName');
        $data['info'] = $this->logic()->getVMRecoverTaskName($this->param);
        $this->success('', $data);
    }

    /**
     * 创建恢复任务
     * @return string
     */
    public function createRecoverJob(): string
    {
        //        $this->checkParams('createRecoverJob');
        return $this->logic()->createRecoverJob($this->param);
    }

    /**
     * 创建瞬时恢复任务
     * @return string
     */
    public function createInstantRecoverJob(): string
    {
        //        $this->checkParams('createInstantRecover');
        return $this->logic()->createInstantRecoverJob($this->param);
    }

    /**
     * 获取迁移虚拟机的配置信息
     * @return string
     */
    public function getMotionVMConfigs(): string
    {
        //        $this->checkParams('getMotionConfig');
        $data = $this->logic()->getMotionVMConfigs($this->param);
        $this->success('', $data);
    }

    /**
     * Openstack获取用户组的树
     * @return string
     */
    public function getRecoverUserGroup(): string
    {
        //        $this->checkParams('getRecoverUserGroup');
        $data = $this->logic()->getRecoverUserGroup($this->param);
        $this->success('', $data);
    }

    /**
     * 创建迁移任务
     * @return string
     */
    public function createMotionJob(): string
    {
        //        $this->checkParams('createMotionJob');
        return $this->logic()->createMotionJob($this->param);
    }

    /**
     * 创建细粒度恢复任务
     * @return string
     */
    public function createGrainRecoverJob(): string
    {
        $this->checkParams('grainJob');
        return $this->logic()->createGrainRecoverJob($this->param);
    }

    /**
     * 校验数据加密密码正确性
     * @return string
     */
    public function checkEncryptPassword(): string
    {
        $this->checkParams('checkEncryptPassword');
        return $this->logic()->checkVMEncryptPass($this->param);
    }

    /**
     * 获取主机信息
     * @return string
     */
    public function getHostCommonInfo(): string
    {
        $this->checkParams('getHostCommonInfo');
        return $this->logic()->getHostCommonInfo($this->param);
    }

    /**
     * 获取恢复目标虚拟机配置
     * @return string
     */
    public function getRecoverTargetVMConfigs(): string
    {
        //        $this->checkParams('getRecoverTargetVMConfigs');
        $data = $this->logic()->getRecoverTargetVMConfigs($this->param);
        $this->success('', $data);
    }
}
