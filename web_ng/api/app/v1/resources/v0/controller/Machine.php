<?php

namespace app\v1\resources\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          虚拟机管理控制器
 * @author       wanggongxi@vinchin.com
 * @date         2024/3/5 18:21
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Machine extends AuthBase
{
    /**
    *  获取主机列表、信息
     * @return json
     */
    public function getMachines()
    {

        if (!empty($this->param['virtual_uuid'])) {
            // 获取主机信息
            $return = $this->logic()->getMachineInfo($this->param['virtual_uuid']);
            return $return['code'] == 0 ? $this->success('', $return['msg']) : $this->error($return['msg']);
        }

        // 参数校验
        $this->checkParams('lists');
        // 获取列表
        $return = $this->logic()->getMachineLists($this->param);

        return $this->success('', $return);
    }

    /**
     *  修改主机信息
     * @return json
     */
    public function editMachine()
    {

        // 参数校验
        $this->checkParams('editMachine');
        // 逻辑处理
        $this->logic()->editMachine($this->param);

        return $this->success(xphp_get_lang('UI_PUBLIC_MODIFY') . xphp_get_lang('WEB_ERROR_BD_GENERIC_SUCCESS'));
    }

    /**
     *  删除主机
     * @return json
     */
    public function delMachine()
    {

        // 参数校验
        $this->checkParams('delMachine');
        // 逻辑处理
        $return = $this->logic()->delMachine($this->param['id_list']);

        return $return['code'] == 0 ? $this->success($return['msg']) : $this->error($return['msg']);
    }

    /**
     *  操作主机
     * @return json
     */
    public function operateMachines()
    {

        // 参数校验
        $this->checkParams('operateMachines');
        // 操作对应操作码
        $real = [
            'look' => 'EMD_VM_SYNC_ALL_VM_VNC_TOKEN',
            'open' => 'TEMP_AGENT_OP_START',
            'stop' => 'TEMP_AGENT_OP_STOP',
            'force' => 'TEMP_AGENT_OP_STOP',
            'restart' => 'TEMP_AGENT_OP_RESTART',
        ];
        $uuid = $this->param['operate_uuid'];
        $opName = $real[$this->param['type']] ?? '';
        if (empty($opName)) {
            return $this->error(xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN'));
        }
        // 逻辑处理
        $return = $this->logic()->operateMachines($opName, [$uuid]);

        return $return['code'] == 0 ? $this->success($return['msg']) : $this->error($return['msg']);
    }

    /**
     *  获取资源隔离
     * @return json
     */
    public function getResources()
    {

        // 参数校验
        $this->checkParams('getResources');
        // 逻辑处理
        $return = $this->logic()->getResources($this->param['node_uuid']);

        return $this->success('', $return);
    }

    /**
     *  保存资源隔离
     * @return json
     */
    public function editResources()
    {

        // 参数校验
        $this->checkParams('editResources');
        // 逻辑处理
        $return = $this->logic()->editResources($this->param);

        return $return['code'] == 0 ? $this->success($return['msg']) : $this->error($return['msg']);
    }

    /**
     *  获取备份系统节点资源隔离列表
     * @return json
     */
    public function getNodeSource()
    {

        // 逻辑处理
        $return = $this->logic()->getNodeSource($this->param);

        $this->success('', $return);
    }

    /**
     *  更改资源列表的是否使用VT
     * @return json
     */
    public function setNodeSourceVt()
    {
        $this->checkParams('nodeVt');

        // 逻辑处理
        $return = $this->logic()->setNodeSourceVt($this->param);

        if ($return) {
            $this->success();
        }
        $this->error();
    }

    /**
     *  获取虚拟机和计算资源的一些统计信息
     * @return json
     */
    public function getStatistInfo()
    {

        // 逻辑处理
        $return = $this->logic()->getStatistInfo($this->param);

        $this->success('', $return);
    }

    /**
     *  网络管理列表、信息
     * @return json
     */
    public function getNetworks()
    {

        if (!empty($this->param['network_uuid'])) {
            // 获取网络信息
            $return = $this->logic()->getNetworkInfo($this->param['network_uuid']);
            return $return['code'] == 0 ? $this->success('', $return['msg']) : $this->error($return['msg']);
        }

        // 参数校验
        $this->checkParams('lists');
        // 获取列表
        $return = $this->logic()->getNetworkLists($this->param);

        return $this->success('', $return);
    }

    /**
     *  添加、修改网络
     * @return json
     */
    public function editNetworks()
    {

        // 参数校验
        $this->checkParams('editNetworks');
        // 逻辑处理
        $return = $this->logic()->editNetworks($this->param);

        return $return['code'] == 0 ? $this->success($return['msg']) : $this->error($return['msg']);
    }

    /**
     *  获取虚拟网络添加时的所有桥接网卡列表
     * @return json
     */
    public function getNetCard()
    {

        // 参数校验
        $this->checkParams('getResources');
        // 逻辑处理
        $return = $this->logic()->getNetCard($this->param);

        return $this->success('', $return);
    }

    /**
     *  删除网络
     * @return json
     */
    public function delNetworks()
    {

        // 参数校验
        $this->checkParams('delNetworks');
        // 逻辑处理
        $return = $this->logic()->delNetworks($this->param['id_list']);

        return $return['code'] == 0 ? $this->success($return['msg']) : $this->error($return['msg']);
    }

    /**
     *  获取操作日志
     * @return json
     */
    public function getLogs()
    {

        // 参数校验
        $this->checkParams('lists');
        // 获取列表
        $return = $this->logic()->getLogs($this->param);

        return $this->success('', $return);
    }

    /**
     *  删除日志
     * @return json
     */
    public function delLogs()
    {

        // 参数校验
        $this->checkParams('delLogs');
        // 逻辑处理
        $return = $this->logic()->delLogs($this->param['id_list']);

        return $return['code'] == 0 ? $this->success($return['msg']) : $this->error($return['msg']);
    }

    /**
     *  获取备份系统iso/光盘列表
     * @return json
     */
    public function getDiskLists()
    {

        // 参数校验
        $this->checkParams('lists');
        // 获取列表
        $return = $this->logic()->getDiskLists($this->param);

        return $this->success('', $return);
    }

    /**
     *  获取新的Uuid集合
     * @return json
     */
    public function getUuid()
    {

        $return = $this->logic()->getUuid($this->param);

        return $this->success('', $return);
    }

    /**
     *  获取创建虚拟机默认的一些信息
     * @return json
     */
    public function getDefault()
    {

        $return = $this->logic()->getDefault($this->param);

        return $this->success('', $return);
    }

    /**
     *  代理网关列表
     * @return json
     */
    public function getProxyList()
    {

        // 参数校验
        $this->checkParams('lists');
        // 逻辑处理
        $return = $this->logic()->getProxyList($this->param);

        $this->success('', $return);
    }
}
