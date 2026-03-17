<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2026-02-10 17:47:36
 * @Description: 
 * @version: 1.0
 */

namespace app\v2\alarm\v0\service;

use app\v2\common\service\Base;
use app\v2\resources\v0\logic\Node;

/**
 * note           通信 service
 * @author       @vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
    /**
     * wu
     * @param string $taskUuid  任务uuid
     * @param int    $subModule 子模块编号
     * @param string $opName    操作码
     * @param array  $msg       消息
     * @param bool   $sync      是否同步
     * @param bool   $command   是否命令
     * @return void
     */
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
        return $this->mbNodeMsg($opName, $nodeUuid, $msg, $sync);
    }
}
