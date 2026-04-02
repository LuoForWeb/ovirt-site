<?php

namespace app\v1\mirror\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

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

    /**
     * 更新客户端
     * @param array $params 参数信息
     * @return array
     */
    public function addMirrorService(array $params): array
    {
        $opcodeName = 'TOOL_ISO_OP_CREATE_ISO';
        $msg = [
            'os_type' => $params['os_type'],
            'os_arch' => $params['os_arch'],
            'purpose' => intval($params['purpose']),
            'name' =>  $params['name'],
            'retention_time' =>  $params['retention_time'] ?: 0,
            'net_model' =>  $params['net_model'] ?: 0,
            'server_port' =>  $params['server_port'] ?: 0,
            'client_port' => $params['client_port'] ?: 0,
		    'listen_ip' =>  $params['listen_ip'] ?: 0,
		    'ipv4_set' =>  $params['ipv4_set'] ?: [
                'method' => 0,
            ],
		    'ipv6_set' =>  $params['ipv6_set'] ?: [
                'method' => 0,
            ],
        ];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), false, true);
    }

    public function deleteMirrorService(array $params): array
    {
        $opcodeName = 'TOOL_ISO_OP_DELETE_ISO';
        $msg = [
            'delete_iso_info_list' => $params['mirror_list'],
        ];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), true);
    }


    public function sizeMirrorService(array $params): array
    {
        $opcodeName = 'TOOL_ISO_OP_GET_ISO_DIR_AVAILABLE_SIZE';
        $msg = [];
        return $this->mbToolMsg($this->getLocalNodeUuid(), $opcodeName, json_encode($msg), true);
    }
}