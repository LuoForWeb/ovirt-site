<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class VolCDPOpcode extends OpcodeHandler{
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
            'VOL_CDP_OP_TYPE_UNKNOWN',  //unknown opcode type
            'BD_OP_TYPE_TASK',  // private task opcode
            'BD_OP_TYPE_BACKUP_POINT',  //private bakcup point opcode
            5=>'BD_OP_TYPE_AGENT', //agent 相关控制码类型
            6=>'BD_OP_TYPE_TEMP_AGENT',        // temp agent operation

        ); 
    }
    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            'VM_VCENTER_OP_UNKNOWN' => array(),

            'BD_OP_TYPE_TASK' => array(
                'VOL_CDP_PRIVATE_TASK_OP_UNKNOWN',
                28 => 'VOL_CDP_TASK_OP_AUTO_TAKEOVER_SET', //自动接管可用状态设置
                29 => 'VOL_CDP_TASK_OP_TAKEOVER_CONFIG', //修改接管前业务IP映射
                30 => 'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_CONFIG',  //configure takeover failback parameters  
                31 => 'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_START',  //start takeover failback'
                150 => 'VOL_CDP_TASK_CREATE_CONSISTENCY_LABEL',
                151 => 'VOL_CDP_TASK_DELETE_CONSISTENCY_LABEL',  //删除标签点
            ),

            'BD_OP_TYPE_AGENT' => array(
                140 =>'VOL_CDP_TASK_SCANNING_APP_INFO',  //更新客户端应用
                141 =>'VOL_CDP_TASK_SCANNING_CLIENT_INFO',  //自动刷新客户端信息:卷和应用信息
                142 =>'VOL_CDP_TASK_SCANNING_DISK_INFO',  //更新客户端卷信息
                143 =>'VOL_CDP_TASK_SCANNING_NIC_INFO',  //更新客户端网卡信息
                
            ),

            'BD_OP_TYPE_TEMP_AGENT' => array(
                'TEMP_AGENT_OP_UNKNOWN',
                'TEMP_AGENT_OP_CREATE',
                'TEMP_AGENT_OP_EDIT',
                'TEMP_AGENT_OP_START',
                'TEMP_AGENT_OP_STOP',
                'TEMP_AGENT_OP_RESTART',
                'TEMP_AGENT_OP_DESTROY',
                'TEMP_AGENT_VM_OP_GET_STATUS',
                'TEMP_AGENT_VM_OP_GET_STATUS_ALL_EMD',
                100 => 'TEMP_AGENT_OP_ADD_EMBED_NETWORK_CONFIG',
                'TEMP_AGENT_OP_DEL_EMBED_NETWORK_CONFIG',
                'TEMP_AGENT_OP_ADD_EMBED_RESOURCE_CONFIG',
                'TEMP_AGENT_OP_DEL_EMBED_RESOURCE_CONFIG',
                'TEMP_AGENT_OP_HOST_USB_DEVICE_INFO'
            ),

        );
    }
    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'VOL_CDP_TASK_OP_AUTO_TAKEOVER_SET' => Xphp::$_lang['UI_VOL_CDP_TASK_OP_AUTO_TAKEOVER_SET'],
            'VOL_CDP_TASK_OP_TAKEOVER_CONFIG' => Xphp::$_lang['UI_VOL_CDP_TASK_MODIFY_NIC_INFO'],
            'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_CONFIG' => Xphp::$_lang['UI_VOL_CDP_OPCODE_CONFIGURE_BACK_PARAMETER'],
            'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_START' => Xphp::$_lang['WEB_TASK_VOL_CDP_LOG_DESC_KEY_START_FAILBACK'],
            'VOL_CDP_TASK_SCANNING_APP_INFO' => Xphp::$_lang['UI_VOL_CDP_OPCODE_SCAN_APP_INFO'],
            'VOL_CDP_TASK_SCANNING_CLIENT_INFO' => Xphp::$_lang['UI_VOL_CDP_OPCODE_SCAN_HOST_INFO'],
            'VOL_CDP_TASK_CREATE_CONSISTENCY_LABEL' => Xphp::$_lang['UI_VOL_CDP_OPCODE_CREATE_LABEL'],
            'VOL_CDP_TASK_DELETE_CONSISTENCY_LABEL' => Xphp::$_lang['UI_VOL_CDP_OPCODE_DELETE_LABEL'],
            'VOL_CDP_TASK_SCANNING_DISK_INFO' => Xphp::$_lang['UI_VOL_CDP_TASK_SCANNING_DISK_INFO'],
            'VOL_CDP_TASK_SCANNING_NIC_INFO' => Xphp::$_lang['UI_VOL_CDP_TASK_UPDATE_NIC_INFO'],

            'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_CONFIG' => Xphp::$_lang['UI_VOL_CDP_OPCODE_CONFIGURE_BACK_PARAMETER'],
            'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_START' => Xphp::$_lang['WEB_TASK_VOL_CDP_LOG_DESC_KEY_START_FAILBACK'],
            'VOL_CDP_TASK_SCANNING_APP_INFO' => Xphp::$_lang['UI_VOL_CDP_OPCODE_SCAN_APP_INFO'],
            'VOL_CDP_TASK_SCANNING_CLIENT_INFO' => Xphp::$_lang['UI_VOL_CDP_OPCODE_SCAN_HOST_INFO'],
            'VOL_CDP_TASK_CREATE_CONSISTENCY_LABEL' => Xphp::$_lang['UI_VOL_CDP_OPCODE_CREATE_LABEL'],
            'VOL_CDP_TASK_DELETE_CONSISTENCY_LABEL' => Xphp::$_lang['UI_VOL_CDP_OPCODE_DELETE_LABEL'],
            'VOL_CDP_TASK_SCANNING_DISK_INFO' => Xphp::$_lang['UI_VOL_CDP_TASK_SCANNING_DISK_INFO'],
        );
    }
}
?>