<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class OSOpcode extends OpcodeHandler{
    
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
            'OS_OP_TYPE_UNKNOWN',       //unknown opcode type
            'OS_OP_TYPE_OS_PRIVATE_TASK',       //os private task, such as grain recovery
            'OS_OP_TYPE_OS_CLIENT',        //os client operation
            'OS_OP_TYPE_OS_MACHINE',   //os machine operation
        ); 
    }
    
    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            
            'OS_OP_TYPE_OS_PRIVATE_TASK' => array(
                'OS_PRIVATE_TASK_OP_CODE_UNKNOWN',
            //--------------------------------grain recovery----------------------------------
                'OS_PRIVATE_TASK_OP_CODE_CREATE_GRAIN_RECOVERY',//os grain recovery create
                'OS_PRIVATE_TASK_OP_CODE_START_GRAIN_RECOVERY',//os grain recovery start
                'OS_PRIVATE_TASK_OP_CODE_STOP_GRAIN_RECOVERY',//os grain recovery stop
                'OS_PRIVATE_TASK_OP_CODE_DELETE_GRAIN_RECOVERY', //os grain recovery delete
            
                //---------------------------- grain recovery operation --------------------------
                'OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR',
                'OS_PRIVATE_TASK_OP_CODE_GRAIN_READ_FILE_BLOCK',
                'OS_PRIVATE_TASK_OP_CODE_GRAIN_SEARCH_FILES',
                'OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR_NAMES',
                'OS_PRIVATE_TASK_OP_CODE_GRAIN_GET_ITEMS_STATS',
                // download dir
                'OS_PRIVATE_TASK_OP_CODE_GRAIN_START_DOWNLOAD_DIR',
                'OS_PRIVATE_TASK_OP_CODE_GRAIN_PROCESS_DOWNLOAD_DIR',
                'OS_PRIVATE_TASK_OP_CODE_GRAIN_STOP_DOWNLOAD_DIR',
                //---------------------------- delete os list operation ---------------------------
                'OS_PRIVATE_TASK_OP_CODE_DELETE_OS_LIST',
                //---------------------------- instant recovery operation ---------------------------
                'OS_PRIVATE_TASK_OP_CODE_CREATE_INSTANTANEOUS_RECOVERY',//os instant recovery create
                'OS_PRIVATE_TASK_OP_CODE_START_INSTANTANEOUS_RECOVERY', //os instant recovery start
                'OS_PRIVATE_TASK_OP_CODE_STOP_INSTANTANEOUS_RECOVERY', //os instant recovery stop
                'OS_PRIVATE_TASK_OP_CODE_STOP_MIGRATION',//os migration  stop
                'OS_PRIVATE_TASK_OP_CODE_DELETE_INSTANTANEOUS_RECOVERY', //os instant recovery delete
                'OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION' //os migration start 
            ),
            
            'OS_OP_TYPE_OS_CLIENT' => array(
                ' OS_CLIENT_OP_CODE_UNKNOWN'
            ),
            
            //opcode for backup point management
            'OS_OP_TYPE_OS_MACHINE' => array(
                'OS_MACHINE_OP_CODE_UNKNOWN',
                'OS_MACHINE_OP_CODE_SCAN_DISK',  //get all disk and partition from machine
            ),
            
        );
    }
    
    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'OS_MACHINE_OP_CODE_UNKNOWN'=> Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
            'OS_MACHINE_OP_CODE_SCAN_DISK'=> Xphp::$_lang['WEB_OS_MACHINE_OP_CODE_SCAN_DISK'],  //get all disk and partition from machine
            
            
            
            'OS_PRIVATE_TASK_OP_CODE_UNKNOWN'=> Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
            //--------------------------------grain recovery----------------------------------
            'OS_PRIVATE_TASK_OP_CODE_CREATE_GRAIN_RECOVERY'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_CREATE_GRAIN_RECOVERY'],//os grain recovery create
            'OS_PRIVATE_TASK_OP_CODE_START_GRAIN_RECOVERY'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_START_GRAIN_RECOVERY'],//os grain recovery start
            'OS_PRIVATE_TASK_OP_CODE_STOP_GRAIN_RECOVERY'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_STOP_GRAIN_RECOVERY'],//os grain recovery stop
            'OS_PRIVATE_TASK_OP_CODE_DELETE_GRAIN_RECOVERY'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_DELETE_GRAIN_RECOVERY'], //os grain recovery delete
            
            //---------------------------- grain recovery operation --------------------------
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR'],
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_READ_FILE_BLOCK'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_READ_FILE_BLOCK'],
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_SEARCH_FILES'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_SEARCH_FILES'],
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR_NAMES'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR_NAMES'],
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_GET_ITEMS_STATS'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_GET_ITEMS_STATS'],
            // download dir
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_START_DOWNLOAD_DIR'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_START_DOWNLOAD_DIR'],
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_PROCESS_DOWNLOAD_DIR'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_PROCESS_DOWNLOAD_DIR'],
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_STOP_DOWNLOAD_DIR'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_STOP_DOWNLOAD_DIR'],
            //---------------------------- delete os list operation ---------------------------
            'OS_PRIVATE_TASK_OP_CODE_DELETE_OS_LIST' => Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_DELETE_HOST'],

            //---------------------------- instant recovery operation --------------------------
            'OS_PRIVATE_TASK_OP_CODE_CREATE_INSTANTANEOUS_RECOVERY'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_CREATE_INSTANTANEOUS_RECOVERY'],
            'OS_PRIVATE_TASK_OP_CODE_START_INSTANTANEOUS_RECOVERY'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_START_INSTANTANEOUS_RECOVERY'],
            'OS_PRIVATE_TASK_OP_CODE_STOP_INSTANTANEOUS_RECOVERY'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_STOP_INSTANTANEOUS_RECOVERY'],
            'OS_PRIVATE_TASK_OP_CODE_STOP_MIGRATION'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_STOP_MIGRATION'],
            'OS_PRIVATE_TASK_OP_CODE_DELETE_INSTANTANEOUS_RECOVERY'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_DELETE_INSTANTANEOUS_RECOVERY'],
            'OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION'=> Xphp::$_lang['WEB_OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION'],

            

            ' OS_CLIENT_OP_CODE_UNKNOWN' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        );
    }
}
?>