<?php

namespace app\v1\complete_machine_os\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note           操作系统 服务通信 service
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
     * @param array  $msg      消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令
     * @return array
     */
    public function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false, $submodule_type)
    {

        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid

        return $this->mbOSMsg($nodeuuid, $opName, json_encode($msg), $sync, $command,$submodule_type);
    }







   /**
     * 获取操作系统磁盘信息
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2024/3/29
     */
    public function getOSBackupAgentInfo($nodeuuid,$opName,$msg,$flag = true,$submodule_type = 0)
    {
        $mbResult = $this->mbOSMsg($nodeuuid,$opName,$msg,$flag,false, $submodule_type);
        return $mbResult;
    }

}
