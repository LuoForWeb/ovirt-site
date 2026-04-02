<?php

namespace app\v1\recovery\v0\service;

use app\v1\common\service\Base;

/**
 * note          高级恢复和后台通信的
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:41
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Service extends Base
{
    /**
     * 虚拟机发送消息
     * @param string  $nodeuuid   节点uuid
     * @param int     $hypervisor 虚拟化类型
     * @param string  $opName     操作码
     * @param array   $msg        消息
     * @param boolean $sync       是否同步
     * @param boolean $command    是否命令模式
     * @return void
     */
    public function mbVMMsgs(string $nodeuuid, int $hypervisor, string $opName, $msg, $sync = false, $command = false)
    {

        return $this->mbVMMsg(
            $nodeuuid,
            $hypervisor,
            $opName,
            json_encode($msg, JSON_UNESCAPED_UNICODE),
            $sync,
            $command
        );
    }

    /**
     * TODO:操作系统模块发送消息
     * @param string  $nodeuuid  节点uuid
     * @param string  $opName    操作码
     * @param array   $msg       消息
     * @param int     $submodule 0和2表示以前老版本的操作系统备份
     *                           1表示定时整机的
     * @param boolean $sync      是否同步
     * @param boolean $command   是否命令模式
     * @return void
     */
    public function mbOSMsgs(
        string $nodeuuid,
        string $opName,
        array $msg,
        $submodule = 1,
        $sync = false,
        $command = false
    ) {
        return $this->mbOSMsg(
            $nodeuuid,
            $opName,
            json_encode($msg, JSON_UNESCAPED_UNICODE),
            $sync,
            $command,
            $submodule
        );
    }

    /**
     * TODO:卷CDP系统模块发送消息
     * @param string  $nodeuuid 节点uuid
     * @param string  $opName   操作码
     * @param array   $msg      消息
     * @param boolean $sync     是否同步
     * @param boolean $command  是否命令模式
     * @return void
     */
    public function mbVolCdpMsgs(string $nodeuuid, string $opName, array $msg, $sync = false, $command = false)
    {
        return $this->mbVolCdpMsg(
            $nodeuuid,
            $opName,
            json_encode($msg, JSON_UNESCAPED_UNICODE),
            $sync,
            $command
        );
    }

    /**
     * 平台发送消息
     * @param string $opName  操作的名字定义
     * @param array  $msg     JSON消息
     * @param bool   $sync    是否同步获取消息，默认false
     * @param bool   $command 是否为命令模式(消息发送成功后立即返回),默认false
     * @return array
     */
    public function mbPFMsgs(string $opName, $msg = [], $sync = false, $command = false)
    {

        return $this->mbPFMsg($opName, json_encode($msg, JSON_UNESCAPED_UNICODE), $sync, $command);
    }

    /**
     * 平台发送消息
     * @param string $opName   操作的名字定义
     * @param array  $msg      JSON消息
     * @param string $nodeuuid 节点uuid
     * @param bool   $sync     是否同步获取消息，默认false
     * @param bool   $command  是否为命令模式(消息发送成功后立即返回),默认false
     * @return array
     */
    public function mbGrainMsgs(string $opName, $msg = [], $nodeuuid = '', $sync = false, $command = false)
    {

        if (empty($nodeuuid)) {
            $nodeuuid = $this->getMasterNodeUuid();
        }

        return $this->mbGrainMsg($nodeuuid, $opName, json_encode($msg, JSON_UNESCAPED_UNICODE), $sync, $command);
    }

    /**
     * 时间点消息
     * @param string $opName  操作码
     * @param array  $msg     消息
     * @param bool   $sync    是否同步获取消息，默认false
     * @param bool   $command 是否为命令模式(消息发送成功后立即返回),默认false
     * @return array
     */
    public function mbPointMsgs(string $opName, array $msg, $sync = false, $command = false)
    {
        $nodeuuid = $this->getMasterNodeUuid();
        return $this->mbPointMsg($nodeuuid, $opName, json_encode($msg, JSON_UNESCAPED_UNICODE), 0, $sync, $command);
    }

    /**
     * 发送消息到节点
     * @param string $opName   操作的名字定义
     * @param array  $msg      JSON消息
     * @param string $nodeUuid 所属节点
     * @param bool   $sync     是否同步获取消息，默认false
     * @param bool   $command  是否为命令模式(消息发送成功后立即返回),默认false
     * @return array
     */
    public function mbNodeMsgs(string $opName, $msg = [], $nodeUuid = '', $sync = false, $command = false)
    {
        if (empty($nodeUuid)) {
            $nodeUuid = $this->getMasterNodeUuid();
        }
        return $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg, JSON_UNESCAPED_UNICODE), $sync, $command);
    }
}
