<?php

namespace app\v1\cloud\v0\controller;

use app\v1\common\controller\AuthBase;
use app\v1\opcode\VmOpcode;

/**
 * Class CloudPlatform
 * @package app\v1\cloud\v0\controller
 */
class CloudPlatform extends AuthBase
{
    /**
     * 获取平台下的区域
     * @return string
     */
    public function getPlatformRegions(): string
    {
        $data = $this->logic()->getPlatformRegions($this->param['platform_uuid']);
        $this->success('', $data);
    }

    /**
     * 获取所有可用区
     * @return string
     */
    public function getAvailabilityZones(): string
    {
        $this->checkParams('getAz');
        $data = $this->logic()->getAvailabilityZones($this->param);
        $this->success('', $data);
    }

    /**
     * 获取全部云平台，恢复目标使用
     * @return string
     */
    public function getAllPlatforms(): string
    {
        $data = $this->logic()->getAllPlatforms($this->param);
        $this->success('', $data);
    }

    /**
     * 获取可用的云平台别名
     * @return string
     */
    public function getValidPlatformNickname(): string
    {
        $nickname = $this->logic()->getValidPlatformNickname($this->param['prefix']);
        $this->success('', ['nickname' => $nickname]);
    }

    /**
     * 云平台同步代理镜像
     * @return string
     */
    public function syncProxyImage(): string
    {
        $this->checkParams('syncImage');
        $re = $this->logic()->syncProxyImage($this->param['platform_uuid']);
        $opDes = VmOpcode::instance()->getOpcodeDes('VM_VCENTER_OP_UPDATE_PUBLIC_CLOUD_SHARED_IMAGE');
        return $this->muOpResult($re['result'], $opDes, '', 0, $re['errorCode']);
    }

    /**
     * 获取云平台某区域的公有IP列表
     * @return string
     */
    public function getPublicIpList(): string
    {
        $this->checkParams('getIpList');
        $data = $this->logic()->getPublicIpList($this->param);
        $this->success('', ['ip_list' => $data]);
    }

    /**
     * 删除共享镜像
     * @return string
     */
    public function deleteSharedImage(): string
    {
        $this->checkParams('syncImage');
        $re = $this->logic()->deleteSharedImage($this->param['platform_uuid']);
        $msg = xphp_get_lang('WEB_VM_VCENTER_OP_DELETE_PUBLIC_CLOUD_SHARED_IMAGE');
        return $this->outputMsg($re, $msg);
    }
}
