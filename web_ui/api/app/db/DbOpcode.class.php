<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class DbOpcode extends OpcodeHandler{
    
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
            'DB_OP_TYPE_UNKNOWN',						// database operation type unknown
        	'DB_OP_TYPE_COMMON',						// database common op type
        	'DB_OP_TYPE_MSSQL',							// mssql op type
        	'DB_OP_TYPE_ORACLE',						// oracle op type
        	'DB_OP_TYPE_MYSQL',							// mysql op type
        	'DB_OP_TYPE_SYBASE',						// sybase op type
        );
    }
    
    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            'DB_OP_TYPE_UNKNOWN' => array(),   
            'DB_OP_TYPE_COMMON' => array(
                'DB_COMMON_OP_UNKNOWN',					// common database operation unknown
            ),
            'DB_OP_TYPE_MSSQL' => array(
                'DB_MSSQL_OP_UNKNOWN',					// mssql database operation unknown
                'DB_MSSQL_OP_GET_INSTANCE_INFO',		// get database instance info
            ),
            'DB_OP_TYPE_ORACLE' => array(
                'DB_ORACLE_OP_UNKNOWN',					// oracle database operation unknown
            ),
            'DB_OP_TYPE_MYSQL' => array(
                'DB_MYSQL_OP_UNKNOWN',					// mysql database operation unknown
                'DB_MYSQL_OP_GET_INSTANCE_INFO',		// get database instance info
            ),
            'DB_OP_TYPE_SYBASE' => array(
                'DB_SYBASE_OP_UNKNOWN',					// sybase database operation unknown
            ),
        );
    }
    
    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'DB_MSSQL_OP_GET_INSTANCE_INFO' => '得到SQL Server实例信息',
            'DB_MYSQL_OP_GET_INSTANCE_INFO' => '得到MySQL实例信息',
        );
    }
}
?>