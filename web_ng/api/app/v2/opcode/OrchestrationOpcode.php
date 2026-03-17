<?php

namespace app\v2\opcode;

use xphp\OpcodeHandler;

class OrchestrationOpcode extends OpcodeHandler
{
    private static $instance = null;

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
        $this->opcodeType = array(
            'SS_OP_TYPE_UNKNOWN',
            'SS_OP_TYPE_TASK_ORCHESTRATION_PLAN',
            'SS_OP_TYPE_SPEED_LIMITING_STRATEGY',       // 限速策略
            'SS_OP_TYPE_SYSTEM_RESOURCE_LIMITING',  //系统资源限制策略
            'SS_OP_TYPE_SYSTEM_RESERVED_STRATEGY',  //系统保留策略
            6 => 'SS_OP_TYPE_TASK_PENDING_STRATEGY', // 挂起任务更改优先级
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'SS_OP_TYPE_UNKNOWN' => array(),
            'SS_OP_TYPE_TASK_ORCHESTRATION_PLAN' => array(
                'SS_OP_TYPE_TASK_ORCHESTRATION_PLAN_UNKNOWN',
                'SS_OP_ADD_TYPE_TASK_ORCHESTRATION_PLAN',
                'SS_OP_MOD_TYPE_TASK_ORCHESTRATION_PLAN',
                'SS_OP_DEL_TYPE_TASK_ORCHESTRATION_PLAN',  //delete task orchestration plan
                'SS_OP_START_TYPE_TASK_ORCHESTRATION_PLAN', //start task orchestration plan
                'SS_OP_STOP_TYPE_TASK_ORCHESTRATION_PLAN', //stop task orchestration plan
                'SS_OP_ENABLE_TASK_ORCHESTRATION_PLAN', //enable task orchestration plan
                'SS_OP_DISABLE_TASK_ORCHESTRATION_PLAN',//disable task orchestration plan
            ),
            'SS_OP_TYPE_SPEED_LIMITING_STRATEGY' => [
                'SS_OP_SPEED_LIMITING_STRATEGY_UNKNOWN',
                'SS_OP_ADD_SPEED_LIMITING_STRATEGY', // 添加限速策略
                'SS_OP_MOD_SPEED_LIMITING_STRATEGY', // 修改限速策略
                'SS_OP_DEL_SPEED_LIMITING_STRATEGY' // 删除限速策略
            ],
            'SS_OP_TYPE_SYSTEM_RESOURCE_LIMITING' => [
                'SS_OP_RESOURCE_LIMITING_UNKNOWN',
                'SS_OP_CONFIG_RESOURCE_LIMITING',  // 配置节点资源限制
            ],
            'SS_OP_TYPE_SYSTEM_RESERVED_STRATEGY' => [
                'SS_OP_SYSTEM_RESERVED_STRATEGY_UNKNOWN',
                'SS_OP_UPDATE_SYSTEM_RESERVED_STRATEGY',    //更新系统预留策略
            ],
            'SS_OP_TYPE_TASK_PENDING_STRATEGY' => [
                1 => 'SS_OP_UPDATE_TASK_PENDING_STRATEGY', // 挂起任务更改优先级
            ],
        );
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = [
            'SS_OP_ADD_SPEED_LIMITING_STRATEGY' => 'WEB_NODE_OP_ADD_SPEED_LIMIT_STRATEGY',     // add
            'NODE_OP_DEL_SPEED_LIMIT_STRATEGY' => 'WEB_NODE_OP_DEL_SPEED_LIMIT_STRATEGY',     // del
            'NODE_OP_MOD_SPEED_LIMIT_STRATEGY' => 'WEB_NODE_OP_MOD_SPEED_LIMIT_STRATEGY',     // edit
            'SS_OP_CONFIG_RESOURCE_LIMITING' => 'WEB_SS_OP_CONFIG_RESOURCE_LIMITING',     // 配置节点资源限制
            'SS_OP_UPDATE_TASK_PENDING_STRATEGY' => 'WEB_SS_OP_UPDATE_TASK_PENDING_STRATEGY',     // 更改优先级
        ];
    }
}
