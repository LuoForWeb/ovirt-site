<?php

namespace app\v1\job\v0\service;

use app\v1\common\service\Base;

/**
 * note           服务通信 service
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
    /**
     * 平台发送消息
     * @param string $opName  操作码
     * @param array  $fsmsg   消息
     * @param bool   $sync    是否同步
     * @param bool   $command 是否命令模式
     * @return array
     */
    public function mbPFMsgs($opName, $fsmsg, $sync = false, $command = false)
    {
        // 这里面就是和后台通信的代码 一个模块的都放在index里面
        return $this->mbPFMsg($opName, json_encode($fsmsg), $sync, $command);
    }

    /**
     * 文件模块发送消息
     * @param string $nodeUuid 节点uuid
     * @param string $opName   操作码
     * @param array  $fsmsg    消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令模式
     * @return array
     */
    public function mbFSMsgs($nodeUuid, $opName, $fsmsg, $sync = false, $command = false)
    {
        // 这里面就是和后台通信的代码 一个模块的都放在index里面
        return $this->mbFSMsg($nodeUuid, $opName, json_encode($fsmsg), $sync, $command);
    }

    /**
     * TODO:文件复制模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbFCMsgs($nodeUuid, $opName, $fsmsg, $sync = false, $command = false)
    {
        return $this->mbFCMsg($nodeUuid, $opName, json_encode($fsmsg), $sync, $command);
    }

    /**
     * 数据库CDP系统模块发送消息
     * @param string $nodeUuid 节点uuid
     * @param string $opName   操作码
     * @param array  $fsmsg    消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令模式
     * @return array
     */
    public function mbDbCdpMsgs($nodeUuid, $opName, $fsmsg, $sync = false, $command = false)
    {
        // 这里面就是和后台通信的代码 一个模块的都放在index里面
        return $this->mbDbCdpMsg($nodeUuid, $opName, json_encode($fsmsg), $sync, $command);
    }

    /**
     * @description: 发送策略消息
     * @param string $nodeuuid 所属节点
     * @param string $opName   操作码
     * @param array  $list     消息
     * @return array
     */
    public function mbStrategyMsgs(string $nodeuuid, string $opName, array $list)
    {
        return $this->mbStrategyMsg($nodeuuid, $opName, json_encode($list));
    }
}
