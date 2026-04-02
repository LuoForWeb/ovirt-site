<?php

namespace app\v1\system\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          desc
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 17:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Network extends AuthBase
{
    /**
     * 获取某块网卡的信息
     * @return json
     */
    public function getNetworkCardInfo()
    {
        // 参数验证
        $this->checkParams('init');

        $records = $this->logic()->getNetworkCardInfo($this->param);

        if (empty($records)) {
            $this->error();
        }

        $this->success('', $records);
    }

    /**
     * 获取所有网卡名字
     * @return json
     */
    public function getNetworkCardList()
    {
        // 参数验证
        $this->checkParams('list');

        $records = $this->logic()->getNetworkCardList($this->param);

        $this->success('', $records);
    }

    /**
     * 设置网卡信息
     * @return json
     */
    public function setNetworkCardInfo()
    {
        // 参数验证
        $this->checkParams('ip');

        $records = $this->logic()->setNetworkCardInfo($this->param);

        $this->success('', $records);
    }

    /**
     * 获取节点的hosts文件信息
     * @return json
     */
    public function getNodeDnsHosts()
    {
        // 参数验证
        $this->checkParams('host');

        $records = $this->logic()->getNodeDnsHosts($this->param);

        $this->success('', $records);
    }

    /**
     * 配置节点hosts文件信息
     * @return json
     */
    public function setNodeDnsHosts()
    {
        // 参数验证
        $this->checkParams('host');

        $result = $this->logic()->setNodeDnsHosts($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
     * 获取网卡聚合配置信息
     * @return json
     */
    public function getNicOldInfo()
    {
        // 参数验证
        $this->checkParams('nic');

        $result = $this->logic()->getNicOldInfo($this->param);

        $this->success('', $result);
    }

    /**
     * 清除网卡聚合信息
     * @return json
     */
    public function cleanNicInfo()
    {
        // 参数验证
        $this->checkParams('nic');

        $result = $this->logic()->cleanNicInfo($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
     * 添加网卡聚合
     * @return json
     */
    public function addNicTeaming()
    {
        // 参数验证
        $this->checkParams('nic');

        $result = $this->logic()->addNicTeaming($this->param);

        $this->muOpResult($result[0], $result[1], $result[2] ?? '', $result[3] ?? '');
    }

    /**
     * 获取网卡桥接信息
     * @return json
     */
    public function getBridgeInfo()
    {

        $result = $this->logic()->getBridgeInfo($this->param);

        $this->success('', $result);
    }

    /**
     * 清除网卡桥接信息
     * @return json
     */
    public function cleanBridge()
    {
        // 参数验证
        $this->checkParams('clear');

        $this->logic()->cleanBridge($this->param);

        $this->success(xphp_get_lang('UI_PLATFORM_CARD_BRIDEG_SUCCESS'));
    }

    /**
     * 添加网卡桥接
     * @return json
     */
    public function addBridge()
    {
        // 参数验证
        $this->checkParams('add');

        $return = $this->logic()->addBridge($this->param);
        if ($return['code'] == 0) {
            $this->success($return['msg']);
        }
        $this->error($return['msg']);
    }

    /**
     * 获取隔离网段配置
     * @return json
     */
    public function getIsolate()
    {

        $result = $this->logic()->getIsolate($this->param);

        $this->success('', $result);
    }

    /**
     * 保存隔离网段配置
     * @return json
     */
    public function saveIsolate()
    {
        // 参数验证
        $this->checkParams('isolate');

        $this->logic()->saveIsolate($this->param);

        $this->success(xphp_get_lang('UI_PLATFORM_CARD_BRIDEG_ISOLATE_SAVE'));
    }
}
