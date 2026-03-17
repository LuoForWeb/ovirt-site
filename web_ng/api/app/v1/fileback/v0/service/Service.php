<?php

namespace app\v1\fileback\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note           文件 服务通信 service
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

        return $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), $sync, $command);
    }

    /**
     * mbFSMsg
     * @param $nodeUuid 节点uuid
     * @param $opName   操作码
     * @param $msg      内容
     * @param bool $sync     异步
     * @param bool $command  命令
     * @return array
     */
    public function mbFSMsgs($nodeUuid, $opName, $msg, $sync = false, $command = false)
    {
        return $this->mbFSMsg($nodeUuid, $opName, $msg, $sync, $command);
    }

    /**
     * mbFSMsg
     * @param $nodeUuid 节点uuid
     * @param $opName   操作码
     * @param $msg      内容
     * @param bool $sync     异步
     * @return array
     */
    public function mbNodeMsgs($nodeUuid, $opName, $msg, $sync = false)
    {
        return $this->mbNodeMsg($nodeUuid, $opName, $msg, $sync);
    }
}
