<?php

namespace app\v2\opcode;

use xphp\OpcodeHandler;

class DbcdpOpcode extends OpcodeHandler
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
        $this->opcodeType = array(
            1 => 'BD_OP_TYPE_TASK',  // private task opcode
            5 => 'BD_OP_TYPE_AGENT',
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'BD_OP_TYPE_TASK' => array(
                40 => 'DB_CDP_TASK_START_FAILBACK',
                41 => 'DB_CDP_TASK_FAILBACK_CONFIG',
                20 => 'DB_CDP_TASK_START_TAKEOVER',
                104 => 'DB_CDP_TASK_START_FAILBACK_SWITCH',
                111 => 'DB_CDP_TASK_ALLOW_AUTO_TAKEOVER',
                112 => 'DB_CDP_TASK_MODIFY_CACHE_CONFIG',
                23 => 'DB_CDP_TASK_STOP_TAKEOVER_NOT_STOP_SERVICES'
            ),
            'BD_OP_TYPE_AGENT' => array(
                97 => 'DB_CDP_TASK_SCANNING_SERVICE_INFO',
                84 => 'DB_CDP_TASK_SCANNING_APP_INFO',
            ),
        );
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = array(
            'DB_CDP_TASK_FAILBACK_CONFIG' => xphp_get_lang('UI_JOB_VIEW_MODIFY_FAILBACK'),
            'DB_CDP_TASK_START_FAILBACK' => xphp_get_lang('WEB_TASK_VOL_CDP_LOG_DESC_KEY_START_FAILBACK_REALTIME_SYNC'),
            'DB_CDP_TASK_SCANNING_SERVICE_INFO' => xphp_get_lang('WEB_DB_CDP_TASK_SCANNING_SERVICE_INFO'),
            'DB_CDP_TASK_START_TAKEOVER' => xphp_get_lang('UI_VOL_CDP_MANUAL_TAKEOVER_TASK'),
            'DB_CDP_TASK_START_FAILBACK_SWITCH' => xphp_get_lang('UI_DB_CDP_OP_CONFIRM_FAILBACK'),
            'DB_CDP_TASK_SCANNING_APP_INFO' => xphp_get_lang('UI_VOL_CDP_OPCODE_SCAN_APP_INFO')
        );
    }
}
