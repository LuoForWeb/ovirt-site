<?php

namespace app\v1\opcode;

use xphp\OpcodeHandler;

class ToolOpcode extends OpcodeHandler
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
            'TOOL_OP_TYPE_UNKNOWN',      // unknown opcode type
            'TOOL_OP_TYPE_DRIVER',       // web request
            'TOOL_OP_TYPE_ISO',
            'TOOL_OP_TYPE_VIRUS',
        ];
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = [
            'TOOL_OP_TYPE_UNKNOWN' => [],

            'TOOL_OP_TYPE_DRIVER' => [
                'TOOL_DR_OP_UNKNOWN',  // unknown system opcode
                'TOOL_DR_OP_PARSE',   // 解析驱动
                'TOOL_DR_OP_PARSE_AND_STORE',    // 解析并存储驱动
                'TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST',  // 驱动检测
                'TOOL_DR_OP_DELETE',     // 删除驱动
            ],
            'TOOL_OP_TYPE_ISO' => [
                'TOOL_ISO_OP_UNKNOWN',
                'TOOL_ISO_OP_CREATE_ISO',
                'TOOL_ISO_OP_DELETE_ISO',
                'TOOL_ISO_OP_GET_ISO_DIR_AVAILABLE_SIZE',
            ],
            'TOOL_OP_TYPE_VIRUS' => [
                'TOOL_ISO_OP_UNKNOWN',                          // unknow virus opcode
                'TOOL_VIRUS_OP_OFFLINE_UPDATE_VIRUS_LIB',       // offline update virus lib opcode
                'TOOL_VIRUS_OP_MANAGE_VIRUS_LIB',               // manage virus lib opcode
                'TOOL_VIRUS_OP_GET_VIRUS_SCAN_RESULT',          // get virus scan result opcode
                'TOOL_VIRUS_OP_UPLOAD_AUTHORIZATION_FILE',			// upload authorization file opcode
            ]

        ];
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = [
            'TOOL_DR_OP_PARSE' => 'WEB_TOOL_DR_OP_PARSE',   // 解析驱动
            'TOOL_DR_OP_PARSE_AND_STORE' => 'WEB_TOOL_DR_OP_PARSE_AND_STORE',     // 解析并存储驱动
            'TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST' => 'WEB_TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST',  // 驱动检测
            'TOOL_DR_OP_DELETE' => 'WEB_TOOL_DR_OP_DELETE',       // 删除驱动
            'TOOL_ISO_OP_CREATE_ISO' => 'WEB_TOOL_ISO_OP_CREATE',  //添加镜像
            'TOOL_ISO_OP_DELETE_ISO' => 'WEB_TOOL_ISO_OP_DELETE',
            'TOOL_ISO_OP_GET_ISO_DIR_AVAILABLE_SIZE' => 'WEB_TOOL_ISO_OP_GET_ISO_DIR_AVAILABLE_SIZE',
            'TOOL_VIRUS_OP_OFFLINE_UPDATE_VIRUS_LIB' => 'WEB_TOOL_VIRUS_OP_OFFLINE_UPDATE_VIRUS_LIB',
            'TOOL_VIRUS_OP_MANAGE_VIRUS_LIB' => 'WEB_TOOL_VIRUS_OP_MANAGE_VIRUS_LIB',
            'TOOL_VIRUS_OP_GET_VIRUS_SCAN_RESULT' => 'WEB_TOOL_VIRUS_OP_GET_VIRUS_SCAN_RESULT',
            'TOOL_VIRUS_OP_UPLOAD_AUTHORIZATION_FILE' => 'WEB_TOOL_VIRUS_OP_UPLOAD_AUTHORIZATION_FILE',
        ];
    }
}
