<?php
namespace app\v2\s3\v0\service;

use app\v2\common\service\Base;
use app\v2\resources\v0\logic\Node;

/**
 * note          对象存储 服务通信 service
 * @auther       chengjiafu@vinchin.com
 * @date         2023/9/8 17:43
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

        return $this->mbOBSMsg($nodeuuid, $opName, json_encode($msg), $sync, $command, xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);
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

    /**
     * 统一发送平台消息到后台
     * @param string $opName  操作码
     * @param string $msg     消息json
     * @param string $operate 操作描述
     * @return array
     */
    public function opUnifyPfMsg(string $opName, string $msg, string $operate)
    {
        $mbResult = $this->mbPFMsg($opName, $msg);
        return $mbResult;
    }
}