<?php

namespace app\v1\cloud\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * Class Instance
 * @package app\v1\cloud\v0\controller
 */
class Instance extends AuthBase
{
    /**
     * 获取所有传输代理实例类型
     * @return string
     */
    public function getInstanceTypes(): string
    {
        $this->checkParams('getAllConfig');
        $data = $this->logic()->getInstanceTypes($this->param);
        $this->success('', $data);
    }

    /**
     * 获取实例配置
     * @return string
     */
    public function getInstanceConfigs(): string
    {
        $this->checkParams('getConfig');
        $data = $this->logic()->getInstanceConfigs($this->param['instance_uuid']);
        $msg = 'Get Public Cloud instance configs';
        $data ? $this->success($msg, $data) : $this->error($msg, $data);
    }

    /**
     * 获取实例所有配置项
     * @return string
     */
    public function getInstanceAllConfigs(): string
    {
        $this->checkParams('getAllConfig');
        $data = $this->logic()->getInstanceAllConfigs($this->param);
        $this->success('', $data);
    }

    /**
     * 获取实例恢复的实例类型
     * @return string
     */
    public function getInstanceTypeConfigs(): string
    {
        $this->checkParams('getAllConfig');
        $data = $this->logic()->getInstanceTypeConfigs($this->param);
        $this->success('', $data);
    }

    /**
     * 获取实例恢复的网络配置
     * @return string
     */
    public function getInstanceNetworkConfigs(): string
    {
        $this->checkParams('getNetworkConfig');
        $data = $this->logic()->getInstanceNetworkConfigs($this->param);
        $this->success('', $data);
    }

    /**
     * 获取kms密钥信息
     * @return string
     */
    public function getKmsInfo(): string
    {
        $this->checkParams('getAllConfig');
        $data = $this->logic()->getKmsInfo($this->param['platform_uuid'], $this->param['region_uuid']);
        $this->success('', $data);
    }

    /**
     * 通过备份时间点获取实例配置
     * @return string
     */
    public function getTimepointsInstanceConfig(): string
    {
        $this->checkParams('getConfigByTimepoint');
        $timepointUuids = $this->param['timepoint_uuids'] ?? [];
        $data = $this->logic()->getTimepointsConfig(
            $this->param['source_hypervisor'],
            $this->param['target_hypervisor'],
            $this->param['config_type'],
            $timepointUuids
        );
        $this->success('', $data);
    }

    /**
     * 获取实例磁盘列表
     * @return string
     */
    public function getInstanceDiskList(): string
    {
        $this->checkParams('getDiskList');
        $data = $this->logic()->getInstanceDiskList($this->param);
        $this->success('', $data);
    }

    /**
     * 获取卷类型列表
     * @return string
     */
    public function getVolumeTypeList(): string
    {
        $this->checkParams('getVolumeTypes');
        $data = $this->logic()->getVolumeTypeList($this->param);
        $this->success('', $data);
    }
}
