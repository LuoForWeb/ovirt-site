<?php

namespace app\v2\opcode;

use xphp\OpcodeHandler;

class NasOpcode extends OpcodeHandler
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
        $this->opcodeLevel = xphp_get_config('app', 'OPCODE_LEVEL')['PRIVATE'];
    }

    /**
     * 初始化opcode type
     * @return void
     */
    private function initOpcodeType()
    {
        $this->opcodeType = array(
            'BD_OP_TYPE_UNKNOWN',       //unknown opcode type
            'FS_OP_TYPE_RECOVERY_FILE_SEARCH',   // recovery file search
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'FS_OP_TYPE_UNKNOWN' => array(),
            'FS_OP_TYPE_RECOVERY_FILE_SEARCH' => array(
                'FS_OP_TYPE_SEARCH_UNKOWN = 0',
                'FS_OP_TYPE_SEARCH_THREAD_CREATE',   // file recovery find file
                'FS_OP_TYPE_SEARCH_RESULT_GET',    // file search result get
                'FS_OP_TYPE_SEARCH_STOP',    //  file search stop
            )
        );
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = array(
            'FS_OP_TYPE_SEARCH_THREAD_CREATE' => xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_THREAD_CREATE'),
            'FS_OP_TYPE_SEARCH_RESULT_GET' => xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_RESULT_GET'),
            'FS_OP_TYPE_SEARCH_STOP' => xphp_get_lang('WEB_FS_OP_TYPE_SEARCH_STOP'),
        );
    }
}
