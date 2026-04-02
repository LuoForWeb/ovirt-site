<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class DBprotectOpcode extends OpcodeHandler{
    
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
            'DB_OP_TYPE_UNKNOWN',						// database backup operation type unknown
            'DB_OP_TYPE_CLIENT',							// database backup client operation type
            'DB_OP_TYPE_INSTANCE',						// database backup instance operation type
            'DB_OP_TYPE_DB',								// database backup db operation
            
        );
    }
    
    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            'DB_OP_TYPE_UNKNOWN' => array(),   
            'DB_OP_TYPE_CLIENT' => array(
                'DB_CLIENT_OP_UNKNOWN',
                'DB_CLIENT_OP_ADD',
                'DB_CLIENT_OP_DELETE',
                'DB_CLIENT_OP_GET_INSTANCE_LIST',
                'DB_CLIENT_OP_MODIFY'
                
            ),
            'DB_OP_TYPE_INSTANCE' => array(
                'DB_INSTANCE_OP_UNKONWN',
                'DB_INSTANCE_OP_VERIFY_AUTH',
                'DB_INSTANCE_OP_SCAN_DB',
                'DB_INSTANCE_OP_SCAN_TABLE_SPACE',
                'DB_INSTANCE_OP_DELETE_INSTANCE'
            ),
            'DB_OP_TYPE_DB' => array(
                
            ),
        );
    }
    
    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'DB_CLIENT_OP_ADD' => Xphp::$_lang['WEB_DB_ADD_CLIENT'],
            'DB_CLIENT_OP_DELETE' => Xphp::$_lang['WEB_DB_DELETE_CLIENT'],
            'DB_CLIENT_OP_GET_INSTANCE_LIST' => Xphp::$_lang['WEB_DB_GET_INSTANCE_LIST'],
            'DB_CLIENT_OP_MODIFY' => Xphp::$_lang['UI_DB_MODIFY_DB_AGENT'],
            
            'DB_INSTANCE_OP_VERIFY_AUTH' => Xphp::$_lang['WEB_DB_INSTANCE_VERIFY_AUTH'],
            'DB_INSTANCE_OP_SCAN_DB' => Xphp::$_lang['WEB_DB_INSTANCE_SCAN_DB'],
            'DB_INSTANCE_OP_SCAN_TABLE_SPACE' => Xphp::$_lang['WEB_DB_INSTANCE_OP_SCAN_TABLE_SPACE'],
            'DB_INSTANCE_OP_DELETE_INSTANCE' => Xphp::$_lang['WEB_DB_INSTANCE_OP_DELETE_INSTANCE']
        );
    }
}
?>