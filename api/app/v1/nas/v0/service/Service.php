<?php

namespace app\v1\nas\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note           NAS 服务通信 service
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
    /**
     * 统一发送消息到后台  -- 任务相关
     * @param string $taskUuid 任务uuid
     * @param string $opName   操作码
     * @param  array  $msg      消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令
     * @return void
     */
    public function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false)
    {

        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid

        return $this->mbNASMsg($nodeuuid, $opName, json_encode($msg), $sync, $command);
    }

    /**
     * 统一发送消息到后台  -- 任务相关
     * @param string $opName   操作码
     * @param string $nodeuuid 节点uuid
     * @param string $msg      消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令
     * @return void
     */
    public function mbNodeMsgs(string $opName, string $nodeuuid, string $msg, $sync = false, $command = false)
    {

        return $this->mbNodeMsg($opName, $nodeuuid, $msg, $sync, $command);
    }

    /**
     * 统一发送消息到后台  -- 备份任务相关
     * @param string $opName   操作码
     * @param string $nodeuuid 节点uuid
     * @param string $msg      消息
     * @param bool   $sync     是否同步
     * @return void
     */
    public function mbFSMsgs(string $opName, string $nodeuuid, string $msg, $sync = false)
    {

        return $this->mbFSMsg($nodeuuid, $opName, $msg, $sync);
    }
}