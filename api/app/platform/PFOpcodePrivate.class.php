<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class PFOpcodePrivate extends OpcodeHandler{
    
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
            'PT_OP_TYPE_UNKNOWN',           //unknown opcode type
            'PT_OP_TYPE_LICENSE',           //license operation
            'PT_OP_TYPE_QUERY_AGENT_INFO',  //query agent info
            'PT_OP_TYPE_SYSTEM_BACKGROUND'  //system background operation
        ); 
    }
    
    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            'PT_OP_TYPE_UNKNOWN' => array(),     
                
            //opcode for license operation
            'PT_OP_TYPE_LICENSE' => array(
                'PT_LICENSE_OP_UNKNOWN',			//unknown opcode type
                'PT_LICENSE_OP_QUERY_THUMBPRINT',	//get machine thumbprint
                'PT_LICENSE_OP_ADD_LICENSE',	    //add license
                'PT_LICENSE_OP_MODIFY_LICENSE',		//modify license
                'PT_LICENSE_OP_DELETE_LICENSE',	    //delete license
                'PT_LICENSE_OP_ADD_VM_LICENSE',		//add license to vm host
                'PT_LICENSE_OP_DEL_VM_LICENSE',		//delete license to vm host
                'PT_LICENSE_OP_ADD_AGENT',			//add agent 
            	'PT_LICENSE_OP_MODIFY_AGENT',		//modify agent
            	'PT_LICENSE_OP_DEL_AGENT',			//delete agent
            	'PT_LICENSE_OP_ADD_AGENT_LICENSE',	//add license to agent
            	'PT_LICENSE_OP_MODIFY_AGENT_LICENSE',	//modify license to agent
            	'PT_LICENSE_OP_DEL_AGENT_LICENSE',	//delete license to agent 
                'PT_LICENSE_OP_MODIFY_NAS_LICENSE',	//nas 授权
                'PT_LICENSE_OP_MODIFY_HADOOP_CLUSTER_LICENSE', //hadoop授权
                'PT_LICENSE_OP_MODIFY_OBS_LICENSE', // obs授权
                'PT_LICENSE_OP_ADD_NODE_THUMBPRINT', //for cluster mode, add node thumbprint
                'PT_LICENSE_OP_SYNC_KEY_FILE', // sync key file to cluster
                'PT_LICENSE_OP_REGISTER_PROTECT_PATH', // register protect path
                'PT_LICENSE_OP_QUERY_MACHINE_SN' // query machine SN number
            ),
            
            'PT_OP_TYPE_QUERY_AGENT_INFO' => array(
                'PT_QUERY_AGENT_INFO_OP_UNKNOWN',                   // unknown opcode type
                'PT_QUERY_AGENT_INFO_OP_QUERY_DIR_LIST',            // query agent file system struct
                'PT_QUERY_AGENT_INFO_OP_QUERY_RECOVERY_DIR_LIST',   // query agent file system directory
            ),
            
            'PT_OP_TYPE_SYSTEM_BACKGROUND' => array(
                'PT_SYSTEM_BACKGROUND_OP_UNKNOWN',		            // unknown system background opcode
                'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND',			    // only return the command result success or not
                'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND_WITH_DETAIL',   // do command with detail output
            ),
        );
    }
    
    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'PT_LICENSE_OP_QUERY_THUMBPRINT' => Xphp::$_lang['WEB_PT_LICENSE_OP_QUERY_THUMBPRINT'],	
            'PT_LICENSE_OP_ADD_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_ADD_LICENSE'],	
            'PT_LICENSE_OP_MODIFY_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_MODIFY_LICENSE'],	
            'PT_LICENSE_OP_DELETE_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_DELETE_LICENSE'],
            'PT_LICENSE_OP_ADD_VM_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_ADD_VM_LICENSE'],
            'PT_LICENSE_OP_DEL_VM_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_DEL_VM_LICENSE'],
            'PT_LICENSE_OP_ADD_AGENT' => Xphp::$_lang['WEB_PT_LICENSE_OP_ADD_AGENT'],
            'PT_LICENSE_OP_MODIFY_AGENT' => Xphp::$_lang['WEB_PT_LICENSE_OP_MODIFY_AGENT'],
            'PT_LICENSE_OP_DEL_AGENT' => Xphp::$_lang['WEB_PT_LICENSE_OP_DEL_AGENT'],
            'PT_LICENSE_OP_ADD_AGENT_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_ADD_AGENT_LICENSE'],
            'PT_LICENSE_OP_MODIFY_AGENT_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_MODIFY_AGENT_LICENSE'],
            'PT_LICENSE_OP_DEL_AGENT_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_DEL_AGENT_LICENSE'],
            'PT_LICENSE_OP_MODIFY_NAS_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_MODIFY_NAS_LICENSE'],
            'PT_QUERY_AGENT_INFO_OP_QUERY_DIR_LIST' => Xphp::$_lang['WEB_PT_AGENT_OP_QUERY_DIR_LIST'],
            'PT_QUERY_AGENT_INFO_OP_QUERY_RECOVERY_DIR_LIST' => Xphp::$_lang['WEB_PT_AGENT_OP_QUERY_RECOVERY_DIR_LIST'],
            'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND' => Xphp::$_lang['WEB_PT_SYSTEM_BACKGROUND_OP_DO_COMMAND'],
            'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND_WITH_DETAIL' => Xphp::$_lang['WEB_PT_SYSTEM_BACKGROUND_OP_DO_COMMAND_WITH_DETAIL'],
            'PT_LICENSE_OP_MODIFY_HADOOP_CLUSTER_LICENSE' => Xphp::$_lang['WEB_PT_LICENSE_OP_MODIFY_HADOOP_CLUSTER_LICENSE'],
            'PT_LICENSE_OP_QUERY_MACHINE_SN' => Xphp::$_lang['WEB_PT_LICENSE_OP_QUERY_MACHINE_SN'],
        );
    }
}
?>