<?php

namespace app\v1\opcode;

use xphp\OpcodeHandler;

class ClusterOpcode extends OpcodeHandler
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        $this->initOpcodeLevel();
        $this->initOpcodeType();
        $this->initOpcode();
        $this->initOpcodeDes();
    }

    /**
     * 初始化opcode level
     * @return void
     */
    private function initOpcodeLevel()
    {
        $this->opcodeLevel = xphp_get_config('app')['OPCODE_LEVEL']['PRIVATE'];
    }

    /**
     * 初始化opcode type
     * @return void
     */
    private function initOpcodeType()
    {
        $this->opcodeType = [
            'CLUSTER_OP_TYPE_UNKNOWN',      // unknown opcode type
            'CLUSTER_OP_TYPE_SYSTEM',       // web request
            'CLUSTER_OP_TYPE_MASTER_NODE',  // backup request master
            'CLUSTER_OP_TYPE_BACKUP_NODE',  // master request backup
            9 => 'BD_CLUSTER_OP_TYPE_FAULT',
        ];
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = [
            'CLUSTER_OP_TYPE_UNKNOWN' => [],

            'CLUSTER_OP_TYPE_SYSTEM' => [
                'CLUSTER_OP_SYSTEM_UNKNOWN',  // unknown system opcode
                'CLUSTER_OP_SYSTEM_CONFIG',   // 配置集群
                'CLUSTER_OP_SYSTEM_START',    // 启动集群
                'CLUSTER_OP_SYSTEM_STOP',     // 停止集群
                'CLUSTER_OP_SYSTEM_SWITCH',   // 切换集群
                'BD_CLUSTER_SYSTEM_OP_CODE_DELETE_CLUSTER_HA_LOG',   // 删除集群高可用日志
                'BD_CLUSTER_SYSTEM_OP_CODE_ADD_CLUSTER_NODE',   // 添加集群节点
            ],

            'CLUSTER_OP_TYPE_MASTER_NODE' => [],

            'CLUSTER_OP_TYPE_BACKUP_NODE' => [],
            'BD_CLUSTER_OP_TYPE_FAULT'=> [
                '',
                'BD_CLUSTER_FAULT_OP_TYPE_SHARED_STORAGE_NODE_FAULT', // 共享存储节点故障,获取其他可用节点
            ],
        ];
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = [
            'CLUSTER_OP_SYSTEM_CONFIG' => 'WEB_CLUSTER_OP_SYSTEM_CONFIG',   // 配置集群
            'CLUSTER_OP_SYSTEM_START' => 'WEB_CLUSTER_OP_SYSTEM_START',     // 启动集群
            'CLUSTER_OP_SYSTEM_STOP' => 'WEB_CLUSTER_OP_SYSTEM_STOP',       // 停止集群
            'CLUSTER_OP_SYSTEM_SWITCH' => 'WEB_CLUSTER_OP_SYSTEM_SWITCH',   // 切换集群
            'BD_CLUSTER_SYSTEM_OP_CODE_DELETE_CLUSTER_HA_LOG' => 'WEB_BD_CLUSTER_SYSTEM_OP_CODE_DELETE_CLUSTER_HA_LOG',    // 删除集群高可用日志
            'BD_CLUSTER_SYSTEM_OP_CODE_ADD_CLUSTER_NODE' => 'WEB_BD_CLUSTER_SYSTEM_OP_CODE_ADD_CLUSTER_NODE',    // 添加集群节点
        ];
    }
}
