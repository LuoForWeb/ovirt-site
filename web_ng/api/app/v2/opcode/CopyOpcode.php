<?php

namespace app\v2\opcode;

use xphp\OpcodeHandler;

class CopyOpcode extends OpcodeHandler
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
            'COPY_SERVER_OP_TYPE_UNKNOWN',  
            'COPY_SERVER_OP_TYPE_BASIC',
            'COPY_SERVER_OP_TYPE_TIMEPOINT',
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'COPY_SERVER_OP_TYPE_UNKNOWN' => array(),
            'COPY_SERVER_OP_TYPE_BASIC' => array(
                'COPY_SERVER_BASIC_OP_CODE_UNKNOWN',
                'COPY_SERVER_BASIC_OP_CODE_START_COPY_CLIENT',
                'COPY_SERVER_BASIC_OP_CODE_STOP_COPY_CLIENT_FORCE',
                'COPY_SERVER_BASIC_OP_CODE_TEST_SPEED',
                'COPY_SERVER_BASIC_OP_CODE_GET_NETWORK_INFO',
            ),
            'COPY_SERVER_OP_TYPE_TIMEPOINT' => array(
                'COPY_SERVER_TIMEPOINT_OP_CODE_UNKNOWN',
                'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TREE_BY_TIMEPOINT', //获取异地数据树
                'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TIMEPOINT',
                'COPY_SERVER_TIMEPOINT_OP_CODE_DELETE_TIMEPOINTS',
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
            'COPY_SERVER_BASIC_OP_CODE_START_COPY_CLIENT' => 'WEB_COPY_SERVER_BASIC_OP_CODE_START_COPY_CLIENT',
            'COPY_SERVER_BASIC_OP_CODE_STOP_COPY_CLIENT_FORCE' => 'WEB_COPY_SERVER_BASIC_OP_CODE_STOP_COPY_CLIENT_FORCE',
            'COPY_SERVER_BASIC_OP_CODE_TEST_SPEED' => 'WEB_COPY_SERVER_BASIC_OP_CODE_TEST_SPEED',
            'COPY_SERVER_BASIC_OP_CODE_GET_NETWORK_INFO' => 'WEB_COPY_SERVER_BASIC_OP_CODE_GET_NETWORK_INFO',
            'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TREE_BY_TIMEPOINT' => 'WEB_COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TREE_BY_TIMEPOINT',
            'COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TIMEPOINT' => 'WEB_COPY_SERVER_TIMEPOINT_OP_CODE_LIST_TIMEPOINT',
            'COPY_SERVER_TIMEPOINT_OP_CODE_DELETE_TIMEPOINTS' => 'WEB_COPY_SERVER_TIMEPOINT_OP_CODE_DELETE_TIMEPOINTS',
        );
    }
}
