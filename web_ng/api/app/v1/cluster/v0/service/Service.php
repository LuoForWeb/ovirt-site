<?php

namespace app\v1\cluster\v0\service;

use app\v1\common\service\Base;

class Service extends Base
{
    /**
     * 获取当前系统的集群uuid
     * @return string
     */
    private function getSystemClusterUuid(): string
    {
        $sql = "SELECT cluster_uuid FROM bd_cluster ";
        $data = $this->dbSelect($sql);
        if (!$data) {
            return '';
        }
        return $data[0]['cluster_uuid'];
    }

    /**
     * 配置集群信息的服务
     * @param string  $clusterName     集群名称
     * @param string  $configServiceIp 集群服务IP
     * @param array   $nodeConfig      节点配置信息
     * @param boolean $priorityFlag    是否开启优先级
     * @param int     $advertInt       心跳间隔
     * @return array
     */
    public function setClusterConfigService(
        string $clusterName,
        string $configServiceIp,
        array $nodeConfig,
        bool $priorityFlag,
        int $advertInt
    ): array {
        $opcodeName = 'CLUSTER_OP_SYSTEM_CONFIG';
        $msg = [
            'cluster_info' => [
                'cluster_name' => $clusterName,
                'cluster_uuid' => '',
                'node_info_set' => array_map(function ($item) use ($priorityFlag) {
                    return [
                        'ip' => $item['network_ip'],
                        'nic_name' => $item['network_name'],
                        'node_role' => (int) $item['node_role'],
                        'node_uuid' => $item['node_uuid'],
                        // 'priority' => $priorityFlag ? (int) $item['priority'] : 0,
                        'priority' => 100,
                    ];
                }, $nodeConfig),
                'priority_strategy' => v1_parse_bool_to_flag($priorityFlag),
                'virtual_ip' => $configServiceIp,
                'advert_int' => $advertInt,
            ],
        ];
        // 集群不论配置与否，都将消息发给主节点
        return $this->mbClusterMsg($this->getMasterNodeUuid(), $opcodeName, json_encode($msg));
    }

    /**
     * 添加集群节点的服务
     * @param array $nodeConfig 节点配置信息
     * @return array
     */
    public function addClusterNodeService(array $nodeConfig): array
    {
        $opcodeName = 'BD_CLUSTER_SYSTEM_OP_CODE_ADD_CLUSTER_NODE';
        $msg = [
            'node_info_set' => array_map(function ($item) {
                return [
                    'ip' => $item['network_ip'],
                    'nic_name' => $item['network_name'],
                    'node_role' => (int) $item['node_role'],
                    'node_uuid' => $item['node_uuid'],
                    'priority' => 100,
                ];
            }, $nodeConfig),
        ];
        return $this->mbClusterMsg($this->getMasterNodeUuid(), $opcodeName, json_encode($msg), false, true);
    }

    /**
     * 启动集群的消息
     * @return array
     */
    public function startClusterService(): array
    {
        $opcodeName = 'CLUSTER_OP_SYSTEM_START';
        $msg = [
            'cluster_uuid' => $this->getSystemClusterUuid(),
        ];
        return $this->mbClusterMsg($this->getMasterNodeUuid(), $opcodeName, json_encode($msg), false, true);
    }

    /**
     * 停止集群的消息
     * @return array
     */
    public function stopClusterService(): array
    {
        $opcodeName = 'CLUSTER_OP_SYSTEM_STOP';
        $msg = [
            'cluster_uuid' => $this->getSystemClusterUuid(),
        ];
        return $this->mbClusterMsg($this->getMasterNodeUuid(), $opcodeName, json_encode($msg), false, true);
    }

    /**
     * 设置集群主节点
     * @param string $nodeUuid 节点的uuid
     * @return array
     */
    public function setClusterMasterNodeService(string $nodeUuid): array
    {
        $opcodeName = 'CLUSTER_OP_SYSTEM_SWITCH';
        $msg = [
            'switch_cluster' => [
                'cluster_uuid' => $this->getSystemClusterUuid(),
                'target_node_uuid' => $nodeUuid
            ],
        ];
        return $this->mbClusterMsg($this->getMasterNodeUuid(), $opcodeName, json_encode($msg), false, true);
    }

    /**
     * 删除集群高可用日志服务
     * @param array $haLogIdList 高可用日志id列表
     * @return array
     */
    public function deleteClusterHaLogService(array $haLogIdList): array
    {
        $opcodeName = 'BD_CLUSTER_SYSTEM_OP_CODE_DELETE_CLUSTER_HA_LOG';
        $msg = [
            'log_id' => $haLogIdList,
        ];
        return $this->mbClusterMsg($this->getMasterNodeUuid(), $opcodeName, json_encode($msg), false);
    }
}
