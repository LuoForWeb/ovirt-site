<?php

namespace app\v1\opcode;

use xphp\OpcodeHandler;

class FsOpcode extends OpcodeHandler
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
            'FS_OP_TYPE_SCAN_BACKUP_PATH', // file backup scan backup path operation
            'FS_OP_TYPE_DETECT_AGENT_ENV', // file backup or recovery detect agent enviroment
            'FS_OP_TYPE_PRIVATE_HISTORY_OPERATION',//用于跳过文件下载
            'FS_OP_TYPE_PRIVATE_MANAGER_OPERATION'
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
            ),
            'FS_OP_TYPE_SCAN_BACKUP_PATH' => array(),
            'FS_OP_TYPE_DETECT_AGENT_ENV' => array(),
            'FS_OP_TYPE_PRIVATE_HISTORY_OPERATION' => array(
                'FS_PRIVATE_OPERATION_CODE_UNKNOW',
                'FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET',   // 获取异常文件列表
                'OBS_OP_TYPE_SEARCH_THREAD_CREATE'
            ),
            'FS_OP_TYPE_PRIVATE_MANAGER_OPERATION' => array(
                'FS_PRIVATE_OPERATION_CODE_TARGET_UNKNOWN',
                'FS_PRIVATE_OPERATION_CODE_TARGET_ADD',
                'FS_PRIVATE_OPERATION_CODE_TARGET_MODIFY',
                'FS_PRIVATE_OPERATION_CODE_TARGET_DELETE',
                'FS_PRIVATE_OPERATION_CODE_TARGET_REFRESH',
                'FS_PRIVATE_OPERATION_CODE_TARGET_DIR_QUERY',
                'FS_PRIVATE_OPERATION_CODE_TARGET_RECOVERY_DIR_QUERY',
                1000 => 'FS_PRIVATE_OPERATION_CODE_TARGET_AUTHORIZE',
                'FS_PRIVATE_OPERATION_CODE_TARGET_REMOVE_AUTHORIZATION'
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
            'FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET' => xphp_get_lang('WEB_FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET'),
            'OBS_OP_TYPE_SEARCH_THREAD_CREATE' => xphp_get_lang('WEB_NODE_OBS_CREATE_SEARCH_MESSAGE'),
            // OBS
            'FS_PRIVATE_OPERATION_CODE_TARGET_UNKNOWN' => 'WEB_NODE_AGENT_OP_UNKNOWN',
            'FS_PRIVATE_OPERATION_CODE_TARGET_ADD' => xphp_get_lang('WEB_NODE_OBS_OP_ADD'),
            'FS_PRIVATE_OPERATION_CODE_TARGET_MODIFY' => xphp_get_lang('WEB_NODE_OBS_OP_MODIFY'),
            'FS_PRIVATE_OPERATION_CODE_TARGET_DELETE' => xphp_get_lang('WEB_NODE_OBS_OP_DEL'),
            'FS_PRIVATE_OPERATION_CODE_TARGET_REFRESH' => xphp_get_lang('WEB_NODE_OBS_OP_REFRESH'),
            'FS_PRIVATE_OPERATION_CODE_TARGET_DIR_QUERY' => xphp_get_lang('WEB_NODE_OBS_OP_QUERY_DIR_LIST'),
            'FS_PRIVATE_OPERATION_CODE_TARGET_RECOVERY_DIR_QUERY' => xphp_get_lang('WEB_NODE_OBS_OP_QUERY_RECOVERY_DIR_LIST'),
            'FS_PRIVATE_OPERATION_CODE_TARGET_AUTHORIZE' => xphp_get_lang('WEB_FS_PRIVATE_OPERATION_CODE_TARGET_AUTHORIZE'),
            'FS_PRIVATE_OPERATION_CODE_TARGET_REMOVE_AUTHORIZATION' => xphp_get_lang('WEB_FS_PRIVATE_OPERATION_CODE_TARGET_REMOVE_AUTHORIZATION')
        );
    }
}