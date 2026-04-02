<?php

namespace app\v1\vm\v0\controller;

use app\v1\common\controller\AuthBase;
use app\v1\system\v0\logic\Index as SystemIndex;

/**
 * Class VmPlatform
 * @package app\v1\vm\v0\controller
 */
class VmPlatform extends AuthBase
{
    /**
     * VmPlatform constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 添加虚拟化平台
     * @return string
     */
    public function addVmPlatform(): string
    {
        $this->checkParams('add');
        return $this->logic()->addVmPlatform($this->param);
    }

    /**
     * 修改虚拟化平台
     * @return string
     */
    public function modifyVmPlatform(): string
    {
        $this->checkParams('modify');
        return $this->logic()->modifyVmPlatform($this->param);
    }

    /**
     * 删除虚拟化平台
     * @return string
     */
    public function deleteVmPlatforms(): string
    {
        $this->checkParams('delete');
        return $this->logic()->deleteVmPlatforms($this->param['platforms_uuids']);
    }

    /**
     * 获取虚拟化平台列表
     * @return string
     */
    public function getVmPlatforms(): string
    {
        $this->checkParams('getList');
        $data = $this->logic()->getVmPlatformsList($this->param);
        $this->success('', $data);
    }

    /**
     * 获取单个虚拟化平台详情
     * @return string
     */
    public function getVmPlatformDetail(): string
    {
        $this->checkParams('getDetail');
        return $this->logic()->getVmPlatformDetail($this->param['platform_uuid']);
    }

    /**
     * 获取虚拟化平台自动刷新配置
     * @return void
     */
    public function getRefreshVcenterTime(): void
    {
        $data = $this->logic()->getRefreshVcenterTime();
        $this->success('', $data);
    }

    /**
     * 虚拟化平台自动刷新配置
     * @return void
     */
    public function vmPlatformAutoRefresh(): void
    {
        $this->checkParams('autoRefresh');
        $re = $this->logic()->vmPlatformAutoRefresh($this->param);
        if (!$re) {
            $this->error();
        }
        $this->success();
    }

    /**
     * 获取虚拟化平台树
     * @return string
     */
    public function getVmPlatformsTree(): string
    {
        $this->checkParams('getTree');
        return $this->logic()->getVmPlatformsTree($this->param);
    }

    /**
     * 异步获取/刷新虚拟化平台的虚拟机树
     * @return string
     */
    public function getVmPlatformsVmTree(): string
    {
        $this->checkParams('getVmTree');
        $data = $this->logic()->getVmPlatformsVmTree($this->param);
        $this->success('', $data);
    }

    /**
     * 获取虚拟化平台下的虚拟机
     * @return string
     */
    public function getVmPlatformVms(): string
    {
        $this->checkParams('getVms');
        $data = $this->logic()->getVmPlatformVms($this->param);
        $this->success('', $data);
    }

    /**
     * 同步虚拟化平台信息
     * @return string
     */
    public function syncVmPlatform(): string
    {
        return $this->logic()->syncVmPlatform($this->param);
    }

    /**
     * 获取虚拟化平台主机列表
     * @return string
     */
    public function getVmPlatformHosts(): string
    {
        $this->checkParams('getHosts');
        $data = $this->logic()->getVmPlatformHosts($this->param);
        $this->success('', $data);
    }

    /**
     * 添加虚拟化平台主机授权
     * @return string
     */
    public function addHostAuth(): string
    {
        $this->checkParams('auth');
        return $this->logic('\\' . SystemIndex::class)->addHostAuth($this->param);
    }

    /**
     * 取消虚拟化平台主机授权
     * @return string
     */
    public function deleteHostAuth(): string
    {
        $this->checkParams('auth');
        return $this->logic('\\' . SystemIndex::class)->deleteHostAuth($this->param);
    }

    /**
     * 获取所有虚拟化类型
     * @return void
     */
    public function getAllHypervisors(): void
    {
        $this->checkParams('getAllHypervisors');
        $data = [];
        $data['hypervisors'] = $this->logic('VmJobInfo')->getAllHypervisorType($this->param);
        $msg = 'Get hypervisor types';
        $data['hypervisors'] ? $this->success($msg, $data) : $this->error($msg, $data);
    }

    /**
     * 获取平台当前所有任务
     * @return string
     */
    public function getPlatformCurrentJobs(): string
    {
        $this->checkParams('getCurrentJobs');
        $data = $this->logic()->getPlatformCurrentJobs($this->param['platform_uuid']);
        $this->success('', $data);
    }

    /**
     * 获取平台备份数据管理的数据条数
     * @return string
     */
    public function getEngineCount(): string
    {
        $data = $this->logic()->getEngineCount();
        $this->success('', $data);
    }

    /**
     * 平台备份测试连接虚拟化平台
     * @return string
     */
    public function testEngine(): string
    {
        $this->checkParams('testEngine');
        return $this->logic()->testEngine($this->param);
    }

    /**
     * 获取快速创建备份任务的树
     * @return string
     */
    public function getBackupTreeSpeed(): string
    {
        $this->checkParams('getBackupTreeSpeed');
        $data = $this->logic()->getBackupTreeSpeed($this->param);
        $this->success('', $data);
    }

    /**
     * 获取修改备份任务虚拟机树
     * @return string
     */
    public function getBackupTreeOldInfo(): string
    {
        $data = $this->logic()->getBackupTreeOldInfo($this->param);
        $this->success('', $data);
    }

    /**
     * 获取修改备份任务虚拟化平台树
     * @return string
     */
    public function getBackupTree(): string
    {
        $data = $this->logic()->getBackupTree($this->param);
        $this->success('', ['rows' => $data, 'total' => count($data)]);
    }

    /**
     * 验证虚拟化平台分组用户
     * @return string
     */
    public function verifyVcenterGroupUser(): string
    {
        $this->checkParams('groupUserVerify');
        return $this->logic()->verifyVcenterGroupUser($this->param);
    }

    /**
     * 获取虚拟化平台分组
     * @return string
     */
    public function getSyncVcenterGroup(): string
    {
        $this->checkParams('getGroups');
        $re = $this->logic()->getSyncVcenterGroup($this->param);
        if ($re['re']) {
            $this->success('', ['rows' => $re['data'], 'total' => count($re['data'])]);
        } else {
            $this->error();
        }
    }

    /**
     * 测试Openstack控制IP连接
     * @return string
     */
    public function testControllerIP(): string
    {
//        $this->checkParams('testControllerIP');
        return $this->logic()->testControllerIP($this->param);
    }

    /**
     * 获取虚拟化平台支持的配置
     * @return string
     */
    public function getSupportInfo(): string
    {
        $data = $this->logic()->getSupportInfo($this->param);
        $this->success('', $data);
    }

    /**
     * 获取zstack用户角色列表
     * @return void
     */
    public function getUserRoleList(): string
    {
        $this->checkParams('getUserRoleList');
        $data = $this->logic()->getUserRoleList($this->param);
        $this->success('', $data);
    }
}
