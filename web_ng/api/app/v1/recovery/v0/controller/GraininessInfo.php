<?php

namespace app\v1\recovery\v0\controller;

use app\v1\common\controller\AuthBase;

/**
 * note          细粒度恢复任务详情右边的文件等
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 16:55
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class GraininessInfo extends AuthBase
{
    /**
     * 细粒度恢复任务详情资源树
     * @return json
     */
    public function graininessTree()
    {
        // 参数验证
        //$this->checkParams('graininess_tree');

        // 逻辑层转发
        $return = $this->logic()->graininessJobTree($this->param);

        return $this->success('', $return);
    }

    /**
     * 按文件名细粒度恢复任务详情资源树
     * @return json
     */
    public function searchGraininessTree()
    {
        // 参数验证
        //$this->checkParams('graininess_tree');

        // 逻辑层转发
        $return = $this->logic()->searchGraininessTree($this->param);

        return $this->success('', $return);
    }

    /**
     * 任务策略配置
     * @return array
     */
    public function taskStrategy()
    {

        // 逻辑层转发
        $return = $this->logic()->taskStrategy($this->param);

        return $this->success('', $return);
    }

    /**
     * 客户端传输配置
     * @return json
     */
    public function clientConfig()
    {
        // 参数验证
//        $this->checkParams('client_config');

        // 逻辑层转发
        $return = $this->logic()->clientConfig($this->param);
        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 网络共享配置
     * @return json
     */
    public function networkConfig()
    {
        // 参数验证
//        $this->checkParams('network_config');

        // 逻辑层转发
        $return = $this->logic()->networkConfig($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 查看共享任务的访问密码
     * @return json
     */
    public function graininessNetworkPwd()
    {
        // 参数验证
        $this->checkParams('look_pwd');

        // 逻辑层转发
        $return = $this->logic()->graininessNetworkPwd($this->param);

        if ($return['code'] == 0) {
            return $this->success('', $return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 网页下载验证
     * @return json
     */
    public function downloadFile()
    {
        // 参数验证
//        $this->checkParams('download');

        // 逻辑层转发
        $return = $this->logic()->downloadFile($this->param);

        if ($return['code'] == 0) {
            return $this->success('', ['value' => $return['value']]);
        }
        return $this->error($return['msg']);
    }

    /**
     * 客户端传输列表 / 客户端传输文件列表
     * @return json
     */
    public function graininessClients()
    {
        // 参数验证
//        $this->checkParams('graininess_client');

        // 逻辑层转发
        if (!empty($this->param['clients_uuid'])) {
            // 逻辑层转发 客户端传输文件列表
            $return = $this->logic()->graininessClientFiles($this->param);
        } else {
            // 客户端传输列表
            $return = $this->logic()->graininessClients($this->param);
        }

        return $this->success('', $return);
    }

    /**
     * 网络共享列表
     * @return json
     */
    public function graininessNetwork()
    {
        // 参数验证
        // $this->checkParams('graininess_network');

        // 逻辑层转发
        if (!empty($this->param['network_uuid'])) {
            // 逻辑层转发 网络共享文件列表
            $return = $this->logic()->graininessNetworkFiles($this->param);
        } else {
            // 网络共享列表
            $return = $this->logic()->graininessNetwork($this->param);
        }

        return $this->success('', $return);
    }

    /**
     * 客户端停止操作
     * @return json
     */
    public function graininessClientStop()
    {
        // 参数验证
//        $this->checkParams('graininess_client_operate');

        // 逻辑层转发
        $return = $this->logic()->graininessClientStop($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 删除客户端传输
     * @return json
     */
    public function graininessClientDel()
    {
        // 参数验证
        //$this->checkParams('graininess_client_del');

        // 逻辑层转发
        $return = $this->logic()->graininessClientDel($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 网络共享的操作
     * @return json
     */
    public function graininessNetworkOperate()
    {
        // 参数验证
        // $this->checkParams('graininess_network_operate');

        // 逻辑层转发
        $return = $this->logic()->graininessNetworkOperate($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 删除网络共享
     * @return json
     */
    public function graininessNetworkDel()
    {
        // 参数验证
//        $this->checkParams('graininess_network_del');

        // 逻辑层转发
        $return = $this->logic()->graininessNetworkDel($this->param);

        if ($return['code'] == 0) {
            return $this->success($return['msg']);
        }
        return $this->error($return['msg']);
    }

    /**
     * 客户端传输错误，下载跳过文件(子节点)
     * @return json
     */
    public function getTransferLog()
    {

        // 逻辑层转发
        $return = $this->logic()->getTransferLog($this->param);

        if ($return['code'] == 0) {
            return $this->success('', ['url' => $return['url']]);
        }
        return $this->error($return['msg']);
    }

    /**
     * 细粒度文件下载完后删除对应的资源文件
     * @return json
     */
    public function grainSourceClear()
    {

        // 逻辑层转发
        $this->logic()->grainSourceClear($this->param);

        return $this->success();
    }

    /**
     * 根据任务名或语言包获取新的任务名
     * @return json
     */
    public function getValidName()
    {
        // 参数验证
        $this->checkParams('get_name');
        // 逻辑转发
        $record = $this->logic()->getValidName($this->param);
        // 返回数据
        $this->success('', $record);
    }
}
