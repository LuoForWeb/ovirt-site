<?php

namespace app\v2\system\v0\service;

use app\v2\common\service\Base;

/**
 * note           虚拟机 服务通信 service
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
    /**
     * 创建备份任务 demo
     * @param array $params 数据
     * @return void
     */
    public function createBackupJob($params = [])
    {

        // 这里面就是和后台通信的代码 一个模块的都放在index里面
        // $this->mbVMMsg($nodeuuid, $subModule, $opName, $msg, $sync, $command);
    }

    /**
     * 分块读取文件服务
     * @param string $filePath 文件名
     * @param int    $offset   偏移量
     * @param int    $length   读取大小
     * @param string $nodeUUid 节点uuid
     * @return mixed|string
     */
    public function pReadFileService(string $filePath, int $offset, int $length, string $nodeUUid)
    {
        $opName = 'NODE_SYS_OP_PREAD_FILE';
        $msg = [
            'file_path' => $filePath,
            'offset' => $offset,
            'length' => $length,
        ];
        return $this->mbNodeMsg($opName, $nodeUUid, json_encode($msg), true);
    }

    /**
     * 容灾主机信息
     * @param array   $msg      消息内容
     * @param string  $opName   操作码
     * @param string  $nodeUuid 节点
     * @param boolean $sync     是否同步
     * @return array
     */
    public function mbTempAgentMsgs(array $msg, string $opName, string $nodeUuid, $sync = false)
    {

        return $this->mbTempAgentMsg($nodeUuid, $opName, json_encode($msg), $sync);
    }

    /**
     * 发送节点消息
     * @param string  $opName   操作码
     * @param string  $nodeUuid 节点
     * @param array   $msg      消息内容
     * @param boolean $sync     是否同步
     * @param boolean $command  是否命令
     * @return array
     */
    public function mbNodeMsgs(string $opName, string $nodeUuid, array $msg, $sync = false, $command = false)
    {

        return $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg), $sync, $command);
    }

    /**
     * 策略模块发消息Strategy-server
     * @param string $opName 操作码
     * @param array  $msg    消息内容
     * @return array
     */
    public function mbStrategyMsgs(string $opName, array $msg)
    {

        return $this->mbStrategyMsg($this->getLocalNodeUuid(), $opName, json_encode($msg));
    }
    /**
     * 获取机器SN码
     * @return array
     */
    public function getMachinSNCode()
    {
        $opName = 'PT_LICENSE_OP_QUERY_MACHINE_SN';
        $msg = array();
        $newmsg = json_encode($msg);
        return $this->mbPFMsg($opName,$newmsg,true);
    }
}
