<?php

namespace app\v1\dbcdp\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note           数据库实时 服务通信 service
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
        return $this->mbDbCdpMsg($nodeuuid, $opName, json_encode($msg), $sync, $command);
    }

    /**
     * 创建备份任务 demo
     * @param $nodeuuid 管理节点uuid
     * @param $opName   控制码
     * @param $msg      创建任务消息结构
     * @return \xphp\db\Ambigous
     */
    public function createBackupJob($nodeuuid, $opName, $msg)
    {
        // 这里面就是和后台通信的代码 一个模块的都放在index里面
        return $this->mbDbCdpMsg($nodeuuid, $opName, $msg);
    }

    /**
     * 创建恢复任务
     * @param $nodeuuid 管理节点uuid
     * @param $opName   控制码
     * @param $msg      创建任务消息结构
     * @return \xphp\db\Ambigous
     */
    public function createRecoverJob($nodeuuid, $opName, $msg)
    {

        return $this->mbDbCdpMsg($nodeuuid, $opName, $msg);
    }

    /**
     * 配置或修改同步任务回切配置
     * @param $nodeuuid 管理节点uuid
     * @param $opName   控制码
     * @param $msg      创建任务消息结构
     * @return \xphp\db\Ambigous
     */
    public function editFailBackConf($nodeuuid, $opName, $msg)
    {
        return $this->mbDbCdpMsg($nodeuuid, $opName, $msg);
    }

    /**
     * 扫描ip
     * @param $nodeuuid 管理节点uuid
     * @param $opName   控制码
     * @param $msg      创建任务消息结构
     * @return array
     */
    public function scanIp($nodeuuid, $opName, $msg)
    {
        return $this->mbDbCdpMsg($nodeuuid, $opName, $msg, true);
    }

    /**
     * 加载数据库应用实例的数据库列表的服务
     * @param array $msg 数据库数据
     * @return array
     */
    public function loadInstanceDatabaseService(array $msg): array
    {
        $opName = 'DB_CDP_TASK_SCANNING_APP_INFO';
        return $this->mbDbCdpMsg($this->getLocalNodeUuid(), $opName, json_encode($msg), true);
    }
}
