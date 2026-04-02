<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class PFOpcode extends OpcodeHandler{
    
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
        $this->opcodeLevel = Xphp::$_config['OPCODE_LEVEL']['PUBLIC'];
    }
    
    /**
     * 初始化opcode type
     */
    private function initOpcodeType(){
        $this->opcodeType = array(
            'BD_OP_TYPE_UNKNOWN',       //unknown opcode type
            'BD_OP_TYPE_TASK',          //task operation
            'BD_OP_TYPE_BACKUP_POINT',  //backup point operation
            'BD_OP_TYPE_LOG',           //log operation
            'BD_OP_TYPE_SYSTEM',		//system operation
        ); 
    }
    
    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            'BD_OP_TYPE_UNKNOWN' => array(),     
                
            //opcode for task management
            'BD_OP_TYPE_TASK' => array(
                'BD_TASK_OP_UNKNOWN',			    //unknown task opcode
                'BD_TASK_OP_BACKUP_CREATE',		//create backup task
                'BD_TASK_OP_RECOVERY_CREATE',	//create recovery task
                'BD_TASK_OP_BACKUP_MODIFY',		//modify backup task
                'BD_TASK_OP_RECOVERY_MODIFY',	//modify recovery task
                'BD_TASK_OP_BACKUP_START',		//start backup task
                'BD_TASK_OP_RECOVERY_START',	//start recovery task
                'BD_TASK_OP_BACKUP_PAUSE',		//pause backup task
                'BD_TASK_OP_RECOVERY_PAUSE',	//pause recovery task
                'BD_TASK_OP_BACKUP_CONTINUE',	//continue backup task
                'BD_TASK_OP_RECOVERY_CONTINUE',	//continue recovery task
                'BD_TASK_OP_BACKUP_STOP',		//stop backup task
                'BD_TASK_OP_RECOVERY_STOP',		//stop recovery task
                'BD_TASK_OP_BACKUP_DELETE',		//delete backup task
                'BD_TASK_OP_RECOVERY_DELETE',	//delete recovery task
                'BD_TASK_OP_HISTORY_TASK_DELETE',	    //delete histtory task
                'BD_TASK_OP_CREATE_DATA_CONNECTION',	//create data transport connection这是后台用的，不需要语言
                'BD_TASK_OP_CLOSE_DATA_CONNECTION',	    //(not used) close data transport connection这是后台用的，不需要语言
                'BD_TASK_OP_START_TIMESTRATEGY',		//start time strategy
                'BD_TASK_OP_BACKUP_EXPORT_CREATE',	//create backup export task
                'BD_TASK_OP_BACKUP_EXPORT_DELETE',	//delete backup export task
                'BD_TASK_OP_BACKUP_EXPORT_MODIFY',	//modify backup export task
                'BD_TASK_OP_BACKUP_EXPORT_START',		//start backup export task
                'BD_TASK_OP_BACKUP_EXPORT_STOP',		//stop backup export task
                
                'BD_TASK_OP_CDP_BACKUP_CREATE',		//create cdp backup task
                'BD_TASK_OP_CDP_BACKUP_DELETE',		//delete cdp backup task
                'BD_TASK_OP_CDP_BACKUP_MODIFY',		//modify cdp backup task
                'BD_TASK_OP_CDP_BACKUP_START',		//start cdp backup task
                'BD_TASK_OP_CDP_BACKUP_STOP',			//stop cdp backup task
                
                'BD_TASK_OP_CDP_RECOVERY_CREATE',		//create cdp recovery task
                'BD_TASK_OP_CDP_RECOVERY_DELETE',		//delete cdp recovery task
                'BD_TASK_OP_CDP_RECOVERY_MODIFY',		//modify cdp recovery task
                'BD_TASK_OP_CDP_RECOVERY_START',		//start cdp recovery task
                'BD_TASK_OP_CDP_RECOVERY_STOP',		    //stop cdp recovery task
                
                
                'BD_TASK_OP_BACKUP_COPY_CREATE',		//create backup copy task
                'BD_TASK_OP_BACKUP_COPY_DELETE',		//delete backup copy task
                'BD_TASK_OP_BACKUP_COPY_MODIFY',		//modify backup copy task
                'BD_TASK_OP_BACKUP_COPY_START',		    //start backup copy task
                'BD_TASK_OP_BACKUP_COPY_STOP',		    //stop backup copy task
            	'BD_TASK_OP_BACKUP_COPY_PAUSE',			//pause backup copy task
            	'BD_TASK_OP_BACKUP_COPY_CONTINUE',		//continue backup copy task
            		
            		
                
                'BD_TASK_OP_BACKUP_COPY_FETCH_CREATE',  //create backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE',  //delete backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_MODIFY',  //modify backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_START',	//start backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_STOP',	//stop backup copy fetch task
            	'BD_TASK_OP_BACKUP_COPY_FETCH_PAUSE',	//pause backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_CONTINUE',//continue backup copy fetch task
                
                'BD_TASK_OP_ARCHIVE_CREATE',			//create archive task
                'BD_TASK_OP_ARCHIVE_DELETE',			//delete archive task
                'BD_TASK_OP_ARCHIVE_MODIFY',			//modify archive task
                'BD_TASK_OP_ARCHIVE_START',			    //start archive task
                'BD_TASK_OP_ARCHIVE_STOP',			    //stop archive task
                'BD_TASK_OP_ARCHIVE_PAUSE',			    //pause archive task
                'BD_TASK_OP_ARCHIVE_CONTINUE',          //continue archive task
                'BD_TASK_OP_ARCHIVE_FETCH_CREATE',	    //create archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_DELETE',	    //delete archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_START',		//start archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_STOP',		//stop archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_PAUSE',       //pause archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_CONTINUE',    //continue archive fetch task 
                
                'BD_TASK_OP_TAKEOVER_CREATE',		//create cdp takeover task 61
                'BD_TASK_OP_TAKEOVER_DELETE',		//delete cdp takeover task
                'BD_TASK_OP_TAKEOVER_MODIFY',		//modify cdp takeover task
                'BD_TASK_OP_TAKEOVER_START',		//start cdp takeover task
                'BD_TASK_OP_TAKEOVER_STOP',		    //stop cdp takeover task 65
                'BD_TASK_OP_TAKEOVER_PAUSE',
                'BD_TASK_OP_TAKEOVER_CONTINUE',

                'BD_TASK_OP_NAS_BACKUP_CREATE' ,		// create nas backup task
                'BD_TASK_OP_NAS_BACKUP_DELETE',		// delete nas backup task
                'BD_TASK_OP_NAS_BACKUP_MODIFY',		// modify nas backup task
                'BD_TASK_OP_NAS_BACKUP_START',		// start nas backup task
                'BD_TASK_OP_NAS_BACKUP_STOP',			// stop nas backup task

                'BD_TASK_OP_HUAWEI_CBR_SYNC_CREATE',
                'BD_TASK_OP_HUAWEI_CBR SYNC_MODIFY'


            ),
            
            //opcode for backup point management
            'BD_OP_TYPE_BACKUP_POINT' => array(
                'BD_BACKUP_POINT_OP_UNKNOWN',   //unknown backup point opcode
                'BD_BACKUP_POINT_OP_DELETE',    //delete backup point
                'BD_BACKUP_POINT_OP_SCAN',      //scan backup point
                'BD_BACKUP_POINT_OP_MAKR',		//mark timepoint *
                'BD_BACKUP_POINT_OP_UNMARK',	//unmark timepoint
                'BD_BACKUP_POINT_OP_COMMENT',	//add comment to a timepoint
                'BD_BACKUP_POINT_OP_BATCH_DELETE', //delete backup point in batch
            	'BD_BACKUP_COPY_POINT_OP_DELETE',	//delete backup copy point
                'BD_BACKUP_COPY_POINT_OP_BATCH_DELETE',	//delete backup copy point in batch
                'BD_ARCHIVE_POINT_OP_DELETE',				//delete archive point
	            'BD_ARCHIVE_POINT_OP_BATCH_DELETE',		    //delete archive point in batch
                'BD_BACKUP_POINT_OP_GFS_FLAG_OP',			// mark/unmark timepoint with gfs flag, add by sky huang, 2021/11/25
            		
            ),
            
            //opcode for log management
            'BD_OP_TYPE_LOG' => array(
                'BD_LOG_OP_UNKNOWN',			//unknown log opcode
                'BD_LOG_OP_TASK_RECORD',		//record task log
                'BD_LOG_OP_SYSTEM_RECORD',		//record system log
                'BD_LOG_OP_TASK_DELETE',		//delete task log
                'BD_LOG_OP_SYSTEM_DELETE',		//delete system log
                'BD_LOG_OP_TASK_EXPORT',		//export task log
                'BD_LOG_OP_SYSTEM_EXPORT',		//export system log
                'BD_ALARM_OP_TASK_DELETE',		//delete task alarm
                'BD_ALARM_OP_SYSTEM_DELETE',	//delete system alarm
            ),
            
            'BD_OP_TYPE_SYSTEM' => array(
                'BD_SYSTEM_OP_UNKNOWN',		    //unknown system opcode
            	'BD_SYSTEM_OP_SYNC_TIME',		//sync system time
            	'BD_SYSTEM_OP_READ_FILE',		//read file content 
            	'BD_SYSTEM_OP_DELETE_FILE',		//delete file
                'BD_SYSTEM_OP_GET_IP_LIST',		//get all ip list
                'BD_SYSTEM_OP_LIST_DIR',		//list dir
                'BD_SYSTEM_OP_REBOOT_SYSTEM',	//reboot system
                'BD_SYSTEM_OP_POWEROFF_SYSTEM',	//poweroff system
                'BD_SYSTEM_OP_PUSH_MQ_MESSAGE',	//push mq message
                'BD_SYSTEM_OP_MQ_CONNCT_TEST',	//test connection to mq
            )
        );
    }
    
    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'BD_TASK_OP_BACKUP_CREATE' => Xphp::$_lang['WEB_PT_OP_BACKUP_CREATE'],	
            'BD_TASK_OP_RECOVERY_CREATE' => Xphp::$_lang['WEB_PT_OP_RECOVERY_CREATE'],	
            'BD_TASK_OP_BACKUP_MODIFY' => Xphp::$_lang['WEB_PT_OP_BACKUP_MODIFY'],	
            'BD_TASK_OP_RECOVERY_MODIFY' => Xphp::$_lang['WEB_PT_OP_RECOVERY_MODIFY'],
            'BD_TASK_OP_BACKUP_START' => Xphp::$_lang['WEB_PT_OP_BACKUP_START'],	
            'BD_TASK_OP_RECOVERY_START' => Xphp::$_lang['WEB_PT_OP_RECOVERY_START'],		
            'BD_TASK_OP_BACKUP_PAUSE' => Xphp::$_lang['WEB_PT_OP_BACKUP_PAUSE'],
            'BD_TASK_OP_RECOVERY_PAUSE' => Xphp::$_lang['WEB_PT_OP_RECOVERY_PAUSE'],
            'BD_TASK_OP_BACKUP_CONTINUE' => Xphp::$_lang['WEB_PT_OP_BACKUP_CONTINUE'],
            'BD_TASK_OP_RECOVERY_CONTINUE' => Xphp::$_lang['WEB_PT_OP_RECOVERY_CONTINUE'],
            'BD_TASK_OP_BACKUP_STOP' => Xphp::$_lang['WEB_PT_OP_BACKUP_STOP'],
            'BD_TASK_OP_RECOVERY_STOP' => Xphp::$_lang['WEB_PT_OP_RECOVERY_STOP'],
            'BD_TASK_OP_BACKUP_DELETE' => Xphp::$_lang['WEB_PT_OP_BACKUP_DELETE'],
            'BD_TASK_OP_RECOVERY_DELETE' => Xphp::$_lang['WEB_PT_OP_RECOVERY_DELETE'],
            'BD_TASK_OP_HISTORY_TASK_DELETE' => Xphp::$_lang['WEB_PT_OP_HISTORY_TASK_DELETE'],
            'BD_TASK_OP_START_TIMESTRATEGY' => Xphp::$_lang['WEB_PT_OP_START_TIMESTRATEGY'],
            'BD_TASK_OP_BACKUP_EXPORT_CREATE' => Xphp::$_lang['WEB_PT_OP_BACKUP_EXPORT_CREATE'],
            'BD_TASK_OP_BACKUP_EXPORT_DELETE' => Xphp::$_lang['WEB_PT_OP_BACKUP_EXPORT_DELETE'],
            'BD_TASK_OP_BACKUP_EXPORT_MODIFY' => Xphp::$_lang['WEB_PT_OP_BACKUP_EXPORT_MODIFY'],
            'BD_TASK_OP_BACKUP_EXPORT_START' => Xphp::$_lang['WEB_PT_OP_BACKUP_EXPORT_START'],
            'BD_TASK_OP_BACKUP_EXPORT_STOP' => Xphp::$_lang['WEB_PT_OP_BACKUP_EXPORT_STOP'],
            'BD_TASK_OP_CDP_BACKUP_CREATE' => "create cdp backup task",
            'BD_TASK_OP_CDP_BACKUP_DELETE' => "delete cdp backup task",
            'BD_TASK_OP_CDP_BACKUP_MODIFY' => "modify cdp backup task",
            'BD_TASK_OP_CDP_BACKUP_START' => "start cdp backup task",
            'BD_TASK_OP_CDP_BACKUP_STOP' => "stop cdp backup task",
            'BD_TASK_OP_CDP_RECOVERY_CREATE' => "create cdp recovery task",
            'BD_TASK_OP_CDP_RECOVERY_DELETE' => "delete cdp recovery task",
            'BD_TASK_OP_CDP_RECOVERY_MODIFY' => "modify cdp recovery task",
            'BD_TASK_OP_CDP_RECOVERY_START' => "start cdp recovery task",
            'BD_TASK_OP_CDP_RECOVERY_STOP' => "stop cdp recovery task",
            
            
            'BD_TASK_OP_BACKUP_COPY_CREATE' => Xphp::$_lang['WEB_PT_OP_COPY_CREATE'],
            'BD_TASK_OP_BACKUP_COPY_DELETE' => Xphp::$_lang['WEB_PT_OP_COPY_DELETE'],
            'BD_TASK_OP_BACKUP_COPY_MODIFY' => Xphp::$_lang['WEB_PT_OP_COPY_MODIFY'],
            'BD_TASK_OP_BACKUP_COPY_START' => Xphp::$_lang['WEB_PT_OP_COPY_START'],
            'BD_TASK_OP_BACKUP_COPY_STOP' => Xphp::$_lang['WEB_PT_OP_COPY_STOP'],
        	'BD_TASK_OP_BACKUP_COPY_PAUSE' => Xphp::$_lang['WEB_PT_OP_COPY_PAUSE'],			
        	'BD_TASK_OP_BACKUP_COPY_CONTINUE' => Xphp::$_lang['WEB_PT_OP_COPY_CONTINUE'],
            
            'BD_TASK_OP_BACKUP_COPY_FETCH_CREATE' => Xphp::$_lang['WEB_PT_OP_COPY_FETCH_CREATE'],
            'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE' => Xphp::$_lang['WEB_PT_OP_COPY_FETCH_DELETE'],
            'BD_TASK_OP_BACKUP_COPY_FETCH_MODIFY' => Xphp::$_lang['WEB_PT_OP_COPY_FETCH_MODIFY'],
            'BD_TASK_OP_BACKUP_COPY_FETCH_START' => Xphp::$_lang['WEB_PT_OP_COPY_FETCH_START'],
            'BD_TASK_OP_BACKUP_COPY_FETCH_STOP' => Xphp::$_lang['WEB_PT_OP_COPY_FETCH_STOP'],
        	'BD_TASK_OP_BACKUP_COPY_FETCH_PAUSE' => Xphp::$_lang['WEB_PT_OP_COPY_FETCH_PAUSE'],	
            'BD_TASK_OP_BACKUP_COPY_FETCH_CONTINUE' => Xphp::$_lang['WEB_PT_OP_COPY_FETCH_CONTINUE'],
            
            'BD_TASK_OP_ARCHIVE_CREATE' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_CREATE'],			
            'BD_TASK_OP_ARCHIVE_DELETE' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_DELETE'],			
            'BD_TASK_OP_ARCHIVE_MODIFY' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_MODIFY'],			
            'BD_TASK_OP_ARCHIVE_START' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_START'],			   
            'BD_TASK_OP_ARCHIVE_STOP' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_STOP'],			   
            'BD_TASK_OP_ARCHIVE_PAUSE' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_PAUSE'],			   
            'BD_TASK_OP_ARCHIVE_CONTINUE' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_CONTINUE'],
            'BD_TASK_OP_ARCHIVE_FETCH_CREATE' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_FETCH_CREATE'],	    
            'BD_TASK_OP_ARCHIVE_FETCH_DELETE' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_FETCH_DELETE'],	   
            'BD_TASK_OP_ARCHIVE_FETCH_START' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_FETCH_START'],		
            'BD_TASK_OP_ARCHIVE_FETCH_STOP' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_FETCH_STOP'],		
            'BD_TASK_OP_ARCHIVE_FETCH_PAUSE' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_FETCH_PAUSE'],       
            'BD_TASK_OP_ARCHIVE_FETCH_CONTINUE' => Xphp::$_lang['WEB_PT_OP_ARCHIVE_FETCH_CONTINUE'],    
        		
        		
            'BD_BACKUP_POINT_OP_DELETE' => Xphp::$_lang['WEB_PT_POINT_OP_DELETE'],
            'BD_BACKUP_POINT_OP_SCAN' => Xphp::$_lang['WEB_PT_POINT_OP_SCAN'],
            'BD_BACKUP_POINT_OP_MAKR' => Xphp::$_lang['WEB_PT_POINT_OP_MAKR'],
            'BD_BACKUP_POINT_OP_UNMARK' => Xphp::$_lang['WEB_PT_POINT_OP_UNMARK'],
            'BD_BACKUP_POINT_OP_COMMENT' => Xphp::$_lang['WEB_PT_POINT_OP_COMMENT'],
            'BD_BACKUP_POINT_OP_BATCH_DELETE' => Xphp::$_lang['WEB_PT_POINT_OP_BATCH_DELETE'],
        	'BD_BACKUP_COPY_POINT_OP_DELETE' => Xphp::$_lang['WEB_PT_COPY_POINT_OP_DELETE'],	
            'BD_BACKUP_COPY_POINT_OP_BATCH_DELETE' => Xphp::$_lang['WEB_PT_COPY_POINT_OP_BATCH_DELETE'],	
            'BD_ARCHIVE_POINT_OP_DELETE' => Xphp::$_lang['WEB_PT_ARCHIVE_POINT_OP_DELETE'],				
	        'BD_ARCHIVE_POINT_OP_BATCH_DELETE' => Xphp::$_lang['WEB_PT_ARCHIVE_POINT_OP_BATCH_DELETE'],		    
            'BD_LOG_OP_TASK_RECORD' => Xphp::$_lang['WEB_PT_OP_TASK_RECORD'],
            'BD_LOG_OP_SYSTEM_RECORD' => Xphp::$_lang['WEB_PT_OP_SYSTEM_RECORD'],
            'BD_LOG_OP_TASK_DELETE' => Xphp::$_lang['WEB_PT_OP_TASK_DELETE'],
            'BD_LOG_OP_SYSTEM_DELETE' => Xphp::$_lang['WEB_PT_OP_SYSTEM_DELETE'],
            'BD_LOG_OP_TASK_EXPORT' => Xphp::$_lang['WEB_PT_OP_TASK_EXPORT'],
            'BD_LOG_OP_SYSTEM_EXPORT' => Xphp::$_lang['WEB_PT_OP_SYSTEM_EXPORT'],
            'BD_ALARM_OP_TASK_DELETE' => Xphp::$_lang['WEB_PT_OP_DELETE_TASK_ALARM'],
            'BD_ALARM_OP_SYSTEM_DELETE' => Xphp::$_lang['WEB_PT_OP_DELETE_SYSTEM_ALARM'],
            'BD_SYSTEM_OP_SYNC_TIME' => Xphp::$_lang['WEB_PT_OP_SYNC_NODE_TIME'],
            'BD_SYSTEM_OP_READ_FILE' => Xphp::$_lang['WEB_PT_OP_READ_NODE_FILE'],
            'BD_SYSTEM_OP_DELETE_FILE' => Xphp::$_lang['WEB_PT_OP_DELETE_NODE_FILE'],
            'BD_SYSTEM_OP_GET_IP_LIST' => Xphp::$_lang['WEB_PT_OP_GET_IP_LIST'],
            'BD_SYSTEM_OP_LIST_DIR' => Xphp::$_lang['WEB_PT_OP_READ_DIR'],
            'BD_SYSTEM_OP_REBOOT_SYSTEM' => Xphp::$_lang['WEB_PT_OP_RESTART_NODE'],
            'BD_SYSTEM_OP_POWEROFF_SYSTEM' => Xphp::$_lang['WEB_PT_OP_POWEROFF_NODE'],
            'BD_SYSTEM_OP_PUSH_MQ_MESSAGE' => Xphp::$_lang['WEB_PT_OP_PUSH_MQ_MESSAGE'],
            'BD_SYSTEM_OP_MQ_CONNCT_TEST' => Xphp::$_lang['WEB_PT_OP_MQ_CONNCT_TEST'],
            'BD_BACKUP_POINT_OP_GFS_FLAG_OP' => Xphp::$_lang['WEB_PT_OP_GFS_FLAG'],
            
            
            'BD_TASK_OP_TAKEOVER_CREATE' => Xphp::$_lang['WEB_BD_TASK_OP_TAKEOVER_CREATE'],
            'BD_TASK_OP_TAKEOVER_DELETE' => Xphp::$_lang['WEB_BD_TASK_OP_TAKEOVER_DELETE'],		//delete cdp takeover task
            'BD_TASK_OP_TAKEOVER_MODIFY' => Xphp::$_lang['WEB_BD_TASK_OP_TAKEOVER_MODIFY'],		//modify cdp takeover task
            'BD_TASK_OP_TAKEOVER_START' => Xphp::$_lang['WEB_BD_TASK_OP_TAKEOVER_START'],		//start cdp takeover task
            'BD_TASK_OP_TAKEOVER_STOP' => Xphp::$_lang['WEB_BD_TASK_OP_TAKEOVER_STOP'],		//stop cdp takeover task
            'BD_TASK_OP_TAKEOVER_PAUSE' => Xphp::$_lang['WEB_BD_TASK_OP_TAKEOVER_PAUSE'],
            'BD_TASK_OP_TAKEOVER_CONTINUE' => Xphp::$_lang['WEB_BD_TASK_OP_TAKEOVER_CONTINUE'],


            'BD_TASK_OP_HUAWEI_CBR_SYNC_CREATE' => Xphp::$_lang['WEB_BD_TASK_OP_HUAWEI_CBR_SYNC_CREATE'],
        	'BD_TASK_OP_HUAWEI_CBR_SYNC_MODIFY'	=> Xphp::$_lang['WEB_BD_TASK_OP_HUAWEI_CBR_SYNC_MODIFY'],
        	
            //文件复制
            'BD_TASK_OP_SYNC_FS_COPY_TASK_START' => Xphp::$_lang['WEB_BD_TASK_OP_SYNC_FS_COPY_TASK_START'],
        	'BD_TASK_OP_SYNC_FS_COMPARE_TASK_START'	=> Xphp::$_lang['WEB_BD_TASK_OP_SYNC_FS_COMPARE_TASK_START'],
        );
    }
}
?>