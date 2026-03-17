<?php

namespace app\v2\opcode;

use xphp\OpcodeHandler;

class FcOpcode extends OpcodeHandler
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
            'BD_OP_LEVEL_PUBLIC',  
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
            'BD_OP_LEVEL_PUBLIC' => array(
                'FS_OP_TYPE_SEARCH_UNKOWN = 0',
                'SYNC_FS_OP_CODE_COPY',   
                'SYNC_FS_OP_CODE_COMPARE',
                'SYNC_FS_OP_CODE_CREATE_TASK',
                'SYNC_FS_OP_CODE_DELETE_TASK',
                'SYNC_FS_OP_CODE_MODIFY_TASK',
                'SYNC_FS_OP_CODE_STOP_TASK',
                'SYNC_FS_OP_CODE_LIST_COMPARE_RESULT', //请求对比结果
                'SYNC_FS_OP_CODE_SYNC_COMPARE_RESULT', //复制对比结果
                'SYNC_FS_OP_CODE_FILE_DOWNLOAD', //文件下载
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
            'SYNC_FS_OP_CODE_COPY' => 'WEB_SYNC_FS_OP_CODE_COPY',   
            'SYNC_FS_OP_CODE_COMPARE' => 'WEB_SYNC_FS_OP_CODE_COMPARE',
            'SYNC_FS_OP_CODE_CREATE_TASK' => 'WEB_SYNC_FS_OP_CODE_CREATE_TASK',
            'SYNC_FS_OP_CODE_DELETE_TASK' => 'WEB_SYNC_FS_OP_CODE_DELETE_TASK',
            'SYNC_FS_OP_CODE_MODIFY_TASK' => 'WEB_SYNC_FS_OP_CODE_MODIFY_TASK',
            'SYNC_FS_OP_CODE_STOP_TASK' => 'WEB_SYNC_FS_OP_CODE_STOP_TASK',
            'SYNC_FS_OP_CODE_LIST_COMPARE_RESULT' => 'WEB_SYNC_FS_OP_CODE_LIST_COMPARE_RESULT',
            'SYNC_FS_OP_CODE_SYNC_COMPARE_RESULT' => 'WEB_SYNC_FS_OP_CODE_SYNC_COMPARE_RESULT',
            'SYNC_FS_OP_CODE_FILE_DOWNLOAD' => 'WEB_SYNC_FS_OP_CODE_FILE_DOWNLOAD',
        );
    }
}