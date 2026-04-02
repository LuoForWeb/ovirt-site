<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class FSOpcode extends OpcodeHandler{
    
    function __construct(){
        $this->initOpcodeLevel();
        $this->initOpcodeType();
        $this->initOpcode();
        $this->initOpcodeDes();
    }
    
    /**
     * 初始化opcode level
     */
    private function initOpcodeLevel(){
        $this->opcodeLevel = Xphp::$_config['OPCODE_LEVEL']['PRIVATE'];
    }
    
    /**
     * 初始化opcode type
     */
    private function initOpcodeType(){
        $this->opcodeType = array(
            'BD_OP_TYPE_UNKNOWN',       //unknown opcode type
            'FS_OP_TYPE_RECOVERY_FILE_SEARCH',   // recovery file search
            'FS_OP_TYPE_SCAN_BACKUP_PATH', // file backup scan backup path operation
            'FS_OP_TYPE_DETECT_AGENT_ENV', // file backup or recovery detect agent enviroment
            'FS_OP_TYPE_PRIVATE_OPERATION',//用于跳过文件下载
        );
    }

    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            'FS_OP_TYPE_UNKNOWN' => array(),     
            'FS_OP_TYPE_RECOVERY_FILE_SEARCH' => array(
                'FS_OP_TYPE_SEARCH_UNKOWN',
                'FS_OP_TYPE_SEARCH_THREAD_CREATE',   // file recovery find file
                'FS_OP_TYPE_SEARCH_RESULT_GET',    // file search result get
                'FS_OP_TYPE_SEARCH_STOP',    //  file search stop
            ),
            'FS_OP_TYPE_SCAN_BACKUP_PATH' => array(),
            'FS_OP_TYPE_DETECT_AGENT_ENV' => array(),
            'FS_OP_TYPE_PRIVATE_OPERATION' => array(
                'FS_PRIVATE_OPERATION_CODE_UNKNOW',
                'FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET',   // 获取异常文件列表
            )
        );
    }
    
    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'FS_OP_TYPE_SEARCH_THREAD_CREATE' => Xphp::$_lang['WEB_FS_OP_TYPE_SEARCH_THREAD_CREATE'],
            'FS_OP_TYPE_SEARCH_RESULT_GET' => Xphp::$_lang['WEB_FS_OP_TYPE_SEARCH_RESULT_GET'],
            'FS_OP_TYPE_SEARCH_STOP' => Xphp::$_lang['WEB_FS_OP_TYPE_SEARCH_STOP'],
            'FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET' => Xphp::$_lang['WEB_FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET'],
        );
    }
}
?>