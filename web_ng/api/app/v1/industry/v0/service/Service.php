<?php

namespace app\v1\industry\v0\service;

use app\v1\common\service\Base;

/**
 * note          服务通信 service
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/31 18:29
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Service extends Base
{
    /**
     * 统一发送消息到后台  -- 数据验证相关服务
     * @param string $opName  操作码
     * @param string $msg     消息
     * @param int    $subtype 子模块
     * @param bool   $sync    是否同步
     * @param bool   $command 是否命令
     * @return array
     */
    public function mbSRMsgs(string $opName, string $msg, $subtype = 0, $sync = false, $command = false)
    {

        $nodeuuid = $this->getMasterNodeUuid();

        return $this->mbSRMsg($nodeuuid, $opName, $msg, $subtype, $sync, $command);
    }

    /**
     * 发送消息到节点
     * @param string $opName  操作的名字定义
     * @param array  $msg     JSON消息
     * @param bool   $sync    是否同步获取消息，默认false
     * @param bool   $command 是否为命令模式(消息发送成功后立即返回),默认false
     * @return array
     */
    public function mbNodeMsgs(string $opName, $msg = [], $sync = false, $command = false)
    {
        $nodeuuid = $this->getMasterNodeUuid();
        return $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg, JSON_UNESCAPED_UNICODE), $sync, $command);
    }
}
