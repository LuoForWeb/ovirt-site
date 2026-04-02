<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class VMOpcode extends OpcodeHandler{
    
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
            'VM_OP_TYPE_UNKNOWN',       //unknown opcode type
            'VM_OP_TYPE_VCENTER',       //opcode of vm vcenter
            'VM_OP_TYPE_MACHINE',       //opcode of vm machine
            'VM_OP_TYPE_PRIVATE_TASK',  // private task opcode
        ); 
    }
    
    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            'VM_VCENTER_OP_UNKNOWN' => array(),     
                
            //opcode for vcenter
            'VM_OP_TYPE_VCENTER' => array(
                'VM_VCENTER_OP_UNKNOWN',   //unknown vcenter opcode
                'VM_VCENTER_OP_ADD',       //add vcenter
                'VM_VCENTER_OP_REFLASH',   //reflash vcenter
                'VM_VCENTER_OP_DELETE',    //delete vcenter
                'VM_VCENTER_OP_MODIFY',    //modify vcenter
                'VM_VCENTER_OP_QUERY_STORAGE',              //query storage for vcenter
                'VM_VCENTER_OP_QUERY_HOST_NETWORK_LIST',	// query network list information
                'VM_VCENTER_OP_QUERY_HOST_COMMON_CONF',	    // query host common configuration information, include cpu and memory
                'VM_VCENTER_OP_DEPLOY_PROXY_VM',			// deploy proxy vm
                'VM_VCENTER_OP_UNDEPLOY_PROXY_VM',		    // undeploy proxy vm
                /* below operation for openstack */
                'VM_VCENTER_OP_QUERY_OPENSTACK_USER_GROUP',	                // query openstack user group
                'VM_VCENTER_OP_TEST_OPENSTACK_RECOVERY_USER_CONNECTION',	// test user connection
                'VM_VCENTER_OP_QUERY_USER_GROUP_STORAGE_SIZE',	            // query storage free space for recovery user group
                'VM_VCENTER_OP_QUERY_USER_GROUP_PHYSICAL_NETWORK',	        // query physical network list for recovery user group
            	'VM_VCENTER_OP_TEST_CONTROLLER_IP',		                    // test controller ip connection, for instant recovery function
            	'VM_VCENTER_OP_GET_VM_DISK_LIST', 							// get vm disk list, for backup disk
            	'VM_VCENTER_OP_QUERY_AVAILABILITY_ZONE',                    // get usable domain
                'VM_VCENTER_OP_ENGINE_TEST',				                // engine user and passwd test
                /* for high safe version */
                'VM_VCENTER_OP_WINCENTER_TEST',			                    // winhong wincenter test
                /* 6.0 add by sky huang, advance recovery */
                'VM_VCENTER_OP_GET_ADVANCE_RECOVERY_CONF',	                // get advanced recovery vm config
                'VM_VCENTER_OP_GET_ADVANCE_MIGRATION_CONF',	                // get advanced migration vm config
                
                //sure backup
                //virtual lab
                'VM_VCENTER_OP_DEPLOY_VIRTUAL_LAB_PROXY_VM',			// 21 deploy proxy vm
                'VM_VCENTER_OP_UNDEPLOY_VIRTUAL_LAB_PROXY_VM',		// 22 undeploy proxy vm
                'VM_VCENTER_OP_MODIFY_VIRTUAL_LAB_PROXY_VM',			// 23
                
                'VM_VCENTER_OP_AUTOMATIC_GET_ISOLATED_NETWORK',     // 24
                'VM_VCENTER_OP_QUERY_VCENTER_NETWORK_LIST',     // 25 query vcenter network list
                'VM_VCENTER_OP_QUERY_OPENSTACK_FLAVORS_LIST',        //26
                'VM_VCENTER_OP_REFRESH_VIRTUAL_LAB',                 //27
                'VM_VCENTER_OP_QUERY_VOLUME_POLICY_LIST',           //28
                'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_NETWORK',         //29
                'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_INSTANCE_TYPE',         //30
                'VM_CHECK_AND_BUILD_HUAWEI_CBR_TIMEPOINT',                          //31
                'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_AGENT_INSTANCE_TYPE',    // 32 query agent cloud use instance type
                'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_KMS_ENCRYPT_INFO',    // 33 query aws kms key id info
                'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_NETWORK_WITH_INSTANCE_TYPE',    // 34 query aws network with out instance type
                'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_REGION_INFO',  //35 query public region and az info
                'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_AVAILABE_VOLUME_TYPE',   //36 query available volume type in current region
                'VM_VCENTER_OP_UPDATE_PUBLIC_CLOUD_SHARED_IMAGE', // 37
                'VM_VCENTER_OP_GET_PUBLIC_CLOUD_PUBLIC_IP_LIST', // 38
                'VM_VCENTER_OP_DELETE_PUBLIC_CLOUD_SHARED_IMAGE', // 39
                'VM_VCENTER_OP_QUERY_OPENSTACK_STORAGE_LIST', // 40
                'VM_VCENTER_OP_QUERY_USER_ROLE_LIST', // 41
            ),
            
            //opcode for backup point management
            'VM_OP_TYPE_MACHINE' => array(
                'VM_MACHINE_OP_UNKNOWN',   //unknown opcode type
                'VM_MACHINE_OP_POWEROFF',  //power on vm
                'VM_MACHINE_OP_POWERON',   //power off vm
                'VM_MACHINE_OP_SUSPEND',   //suspend vm
            ),
            
            'VM_OP_TYPE_PRIVATE_TASK' => array(
                'VM_PRIVATE_TASK_OP_UNKNOWN',						    //unknown opcode type
                //--------------------------------instant recovery---------------------------------
                'VM_PRIVATE_TASK_OP_CREATE_INSTANT_RECOVERY_TASK',	    //create instant recovery task
                'VM_PRIVATE_TASK_OP_START_INSTANT_RECOVERY_TASK',		//start instant vm recovery
                'VM_PRIVATE_TASK_OP_STOP_INSTANT_RECOVERY_TASK',		//stop instant vm recovery
                'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK',	    //delete instant recovery task
                'VM_PRIVATE_TASK_OP_CREATE_ONLINE_MOTION_TASK',		    //create online motion task
                //--------------------------------cdp instant recovery---------------------------------
                'VM_PRIVATE_TASK_OP_CREATE_CDP_INSTANT_RECOVERY_TASK',	//create instant recovery task
                'VM_PRIVATE_TASK_OP_START_CDP_INSTANT_RECOVERY_TASK',	//start instant vm recovery
                'VM_PRIVATE_TASK_OP_STOP_CDP_INSTANT_RECOVERY_TASK',	//stop instant vm recovery
                'VM_PRIVATE_TASK_OP_DELETE_CDP_INSTANT_RECOVERY_TASK',	//delete instant recovery task
                'VM_PRIVATE_TASK_OP_CREATE_CDP_ONLINE_MOTION_TASK',		//create online motion task
                //--------------------------------grain recovery----------------------------------
                'VM_PRIVATE_TASK_OP_CREATE_GRAIN_RECOVERY_TASK',		//create grain recovery task
                'VM_PRIVATE_TASK_OP_START_GRAIN_RECOVERY_TASK',		    //start grain recovery task
                'VM_PRIVATE_TASK_OP_STOP_GRAIN_RECOVERY_TASK',		    //stop grain recovery task
                'VM_PRIVATE_TASK_OP_DELETE_GRAIN_RECOVERY_TASK',		//delete grain recovery task
                
                'VM_PRIVATE_TASK_OP_START_PACK_GRAIN_RECOVERY_FILES_TASK',      // start packing grain recovery files task
                'VM_PRIVATE_TASK_OP_STOP_PACK_GRAIN_RECOVERY_FILES_TASK',	    // stop pack grain recovery files
                'VM_PRIVATE_TASK_OP_DELETE_PACK_GRAIN_RECOVERY_FILES_TASK',     // delete pack grain recovery files task, and package
                //--------------------------------orchestration recovery----------------------------------
                'VM_PRIVATE_TASK_OP_CREATE_ORCH_TASK',				    //create orch recovery task
                'VM_PRIVATE_TASK_OP_START_ORCH_TASK',					//start orch recovery task
                'VM_PRIVATE_TASK_OP_STOP_ORCH_TASK',					//stop orch recovery task
                'VM_PRIVATE_TASK_OP_DELETE_ORCH_TASK',				    //delete orch recovery task
                //---------------------------- new grain recovery operation ---------------------------------
                'VM_PRIVATE_TASK_OP_GRAIN_LIST_CHILD_DIR',	            // list children directory
                'VM_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK',			    // read guest file block by offset and start length
                'VM_PRIVATE_TASK_OP_GRAIN_SEARCH_FILES',				// search current dir to find the files
                'VM_PRIVATE_TASK_OP_GRAIN_LIST_CHILD_DIR_NAMES',		// list children directory, but only list the item's name
                'VM_PRIVATE_TASK_OP_GRAIN_GET_ITEMS_STATS',			    // get the files' stats
                
                // download dir
                'VM_PRIVATE_TASK_OP_GRAIN_START_DOWNLOAD_DIR',		    // start download dir
                'VM_PRIVATE_TASK_OP_GRAIN_PROCESS_DOWNLOAD_DIR',		// process download dir
                'VM_PRIVATE_TASK_OP_GRAIN_STOP_DOWNLOAD_DIR',			// stop download dir
            
                /*---------------------------- platform backup ---------------------------------------------*/
                'VM_PRIVATE_TASK_OP_PF_BAKCUP_DELETE_TIMEPOINT',				// delete platform backup timepoint
                'VM_PRIVATE_TASK_OP_PF_BACKUP_DOWNLOAD_BLOCK_FILE_BY_OFFSET',	// download file by offset
              
                //--------------------------new add for sure backup task
                'VM_PRIVATE_TASK_OP_CREATE_SURE_BACKUP_TASK',
                'VM_PRIVATE_TASK_OP_START_SURE_BACKUP_TASK',
                'VM_PRIVATE_TASK_OP_STOP_SURE_BACKUP_TASK',
                'VM_PRIVATE_TASK_OP_DELETE_SURE_BACKUP_TASK',

                39 => 'VM_PRIVATE_TASK_OP_EXCLUDE_SELECTED_VMS',
            ),
            
        );
    }
    
    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'VM_VCENTER_OP_ADD' => Xphp::$_lang['WEB_VM_VCENTER_ADD'],
            'VM_VCENTER_OP_REFLASH' => Xphp::$_lang['WEB_VM_VCENTER_REFRESH'],
            'VM_VCENTER_OP_DELETE' => Xphp::$_lang['WEB_VM_VCENTER_DELETE'],
            'VM_VCENTER_OP_MODIFY' => Xphp::$_lang['WEB_VM_VCENTER_MODIFY'],
            
            'VM_VCENTER_OP_QUERY_STORAGE' => Xphp::$_lang['WEB_VCENTER_QUERY_STORAGE'],              //query storage for vcenter
            'VM_VCENTER_OP_QUERY_HOST_NETWORK_LIST' => Xphp::$_lang['WEB_VCENTER_QUERY_HOST_NETWORK_LIST'],	// query network list information
            'VM_VCENTER_OP_QUERY_HOST_COMMON_CONF' => Xphp::$_lang['WEB_VCENTER_QUERY_HOST_COMMON_CONF'],	    // query host common configuration information, include cpu and memory
            'VM_VCENTER_OP_DEPLOY_PROXY_VM' => Xphp::$_lang['WEB_VCENTER_DEPLOY_PROXY_VM'],			     // deploy proxy vm
            'VM_VCENTER_OP_UNDEPLOY_PROXY_VM' => Xphp::$_lang['WEB_VCENTER_UNDEPLOY_PROXY_VM'],		    // undeploy proxy vm
            'VM_VCENTER_OP_QUERY_OPENSTACK_USER_GROUP' => Xphp::$_lang['WEB_VCENTER_QUERY_OPENSTACK_USER_GROUP'],
            'VM_VCENTER_OP_TEST_OPENSTACK_RECOVERY_USER_CONNECTION' => Xphp::$_lang['WEB_VCENTER_TEST_OPENSTACK_RECOVERY_USER_CONNECTION'],
            'VM_VCENTER_OP_QUERY_USER_GROUP_STORAGE_SIZE' => Xphp::$_lang['WEB_VCENTER_QUERY_USER_GROUP_STORAGE_SIZE'],
            'VM_VCENTER_OP_QUERY_USER_GROUP_PHYSICAL_NETWORK' => Xphp::$_lang['WEB_VCENTER_QUERY_USER_GROUP_PHYSICAL_NETWORK'],
        	'VM_VCENTER_OP_TEST_CONTROLLER_IP' => Xphp::$_lang['WEB_VCENTER_OP_TEST_CONTROLLER_IP'],    
        	'VM_VCENTER_OP_GET_VM_DISK_LIST' => Xphp::$_lang['WEB_VCENTER_OP_GET_VM_DISK_LIST'],
        	'VM_VCENTER_OP_QUERY_AVAILABILITY_ZONE' => "get usable domain",
            'VM_VCENTER_OP_ENGINE_TEST' => Xphp::$_lang['WEB_VM_VCENTER_OP_ENGINE_TEST'],
            'VM_VCENTER_OP_WINCENTER_TEST' => Xphp::$_lang['WEB_VM_VCENTER_OP_WINCENTER_TEST'],
            'VM_VCENTER_OP_GET_ADVANCE_RECOVERY_CONF' => Xphp::$_lang['WEB_VM_VCENTER_OP_GET_ADVANCE_RECOVERY_CONF'],
            'VM_VCENTER_OP_GET_ADVANCE_MIGRATION_CONF' => Xphp::$_lang['VM_VCENTER_OP_GET_ADVANCE_MIGRATION_CONF'],
            
            'VM_VCENTER_OP_DEPLOY_VIRTUAL_LAB_PROXY_VM' => Xphp::$_lang['WEB_VM_VCENTER_OP_DEPLOY_VIRTUAL_LAB_PROXY'],			// 21 deploy proxy vm
            'VM_VCENTER_OP_UNDEPLOY_VIRTUAL_LAB_PROXY_VM' => Xphp::$_lang['WEB_VM_VCENTER_OP_UNDEPLOY_VIRTUAL_LAB_PROXY'],		// 22 undeploy proxy vm
            'VM_VCENTER_OP_MODIFY_VIRTUAL_LAB_PROXY_VM' => Xphp::$_lang['WEB_VM_VCENTER_OP_MODIFY_VIRTUAL_LAB_PROXY'],			// 23
            'VM_VCENTER_OP_AUTOMATIC_GET_ISOLATED_NETWORK' => Xphp::$_lang['WEB_VM_VCENTER_OP_AUTOMATIC_GET_ISOLATED_NETWORK'],
            'VM_VCENTER_OP_QUERY_VCENTER_NETWORK_LIST' => Xphp::$_lang['WEB_VM_VCENTER_OP_GET_NETWORK_LIST'],
            'VM_VCENTER_OP_QUERY_OPENSTACK_FLAVORS_LIST' => Xphp::$_lang['WEB_VM_VCENTER_OP_GET_EXAMPLE_CONFIG_LIST'],        //26
            'VM_VCENTER_OP_REFRESH_VIRTUAL_LAB' => Xphp::$_lang['WEB_VM_VCENTER_OP_UPDATE_VIRTUAL_LAB'],                 //27
            'VM_VCENTER_OP_QUERY_VOLUME_POLICY_LIST' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_VOLUME_POLICY_LIST'],                 //28
            'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_NETWORK' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_NETWORK'],           //29
            'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_INSTANCE_TYPE' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_INSTANCE_TYPE'],           //30
            'VM_CHECK_AND_BUILD_HUAWEI_CBR_TIMEPOINT' => Xphp::$_lang['WEB_VM_CHECK_AND_BUILD_HUAWEI_CBR_TIMEPOINT'],                 //31
            'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_AGENT_INSTANCE_TYPE' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_AGENT_INSTANCE_TYPE'],                 //32
            'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_KMS_ENCRYPT_INFO' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_KMS_ENCRYPT_INFO'],                 //33
            'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_NETWORK_WITH_INSTANCE_TYPE' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_NETWORK_WITH_INSTANCE_TYPE'],                 //34
            'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_REGION_INFO' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_REGION_INFO'],                 //35
            'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_AVAILABE_VOLUME_TYPE' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_AVAILABE_VOLUME_TYPE'],                 //36
            'VM_VCENTER_OP_UPDATE_PUBLIC_CLOUD_SHARED_IMAGE' => Xphp::$_lang['WEB_VM_VCENTER_OP_UPDATE_PUBLIC_CLOUD_SHARED_IMAGE'], // 37
            'VM_VCENTER_OP_GET_PUBLIC_CLOUD_PUBLIC_IP_LIST' => Xphp::$_lang['WEB_VM_VCENTER_OP_GET_PUBLIC_CLOUD_PUBLIC_IP_LIST'], // 38
            'VM_VCENTER_OP_DELETE_PUBLIC_CLOUD_SHARED_IMAGE' => Xphp::$_lang['WEB_VM_VCENTER_OP_DELETE_PUBLIC_CLOUD_SHARED_IMAGE'], // 39
            'VM_VCENTER_OP_QUERY_OPENSTACK_STORAGE_LIST' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_OPENSTACK_STORAGE_LIST'], // 40
            'VM_VCENTER_OP_QUERY_USER_ROLE_LIST' => Xphp::$_lang['WEB_VM_VCENTER_OP_QUERY_USER_ROLE_LIST'], // 41

            'VM_MACHINE_OP_POWEROFF' => Xphp::$_lang['WEB_VM_POWER_OFF'],
            'VM_MACHINE_OP_POWERON' => Xphp::$_lang['WEB_VM_POWER_ON'],
            'VM_MACHINE_OP_SUSPEND' => Xphp::$_lang['WEB_VM_SUSPEND'],
            'VM_PRIVATE_TASK_OP_CREATE_INSTANT_RECOVERY_TASK' => Xphp::$_lang['WEB_VM_CREATE_INSTANT_JOB'],
            'VM_PRIVATE_TASK_OP_START_INSTANT_RECOVERY_TASK' => Xphp::$_lang['WEB_VM_START_INSTANT_JOB'],
            'VM_PRIVATE_TASK_OP_STOP_INSTANT_RECOVERY_TASK' => Xphp::$_lang['WEB_VM_STOP_INSTANT_JOB'],
            'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK' => Xphp::$_lang['WEB_VM_DELETE_INSTANT_JOB'],
            'VM_PRIVATE_TASK_OP_CREATE_ONLINE_MOTION_TASK' => Xphp::$_lang['WEB_VM_CREATE_MOTION_JOB'],
            'VM_PRIVATE_TASK_OP_CREATE_CDP_INSTANT_RECOVERY_TASK' => 'create instant recovery task',
            'VM_PRIVATE_TASK_OP_START_CDP_INSTANT_RECOVERY_TASK' => 'start instant vm recovery',
            'VM_PRIVATE_TASK_OP_STOP_CDP_INSTANT_RECOVERY_TASK' => 'stop instant vm recovery',
            'VM_PRIVATE_TASK_OP_DELETE_CDP_INSTANT_RECOVERY_TASK' => 'delete instant recovery task',
            'VM_PRIVATE_TASK_OP_CREATE_CDP_ONLINE_MOTION_TASK' => 'create online motion task',
            //--------------------------------grain recovery----------------------------------
            'VM_PRIVATE_TASK_OP_CREATE_GRAIN_RECOVERY_TASK' => Xphp::$_lang['WEB_GRAIN_RECOVERY_CREATE_TASK'],
            'VM_PRIVATE_TASK_OP_START_GRAIN_RECOVERY_TASK' => Xphp::$_lang['WEB_GRAIN_RECOVERY_START_TASK'],
            'VM_PRIVATE_TASK_OP_STOP_GRAIN_RECOVERY_TASK' => Xphp::$_lang['WEB_GRAIN_RECOVERY_STOP_TASK'],
            'VM_PRIVATE_TASK_OP_DELETE_GRAIN_RECOVERY_TASK' => Xphp::$_lang['WEB_GRAIN_RECOVERY_DELETE_TASK'],
            
            'VM_PRIVATE_TASK_OP_START_PACK_GRAIN_RECOVERY_FILES_TASK' => Xphp::$_lang['WEB_GRAIN_RECOVERY_START_PACK_FILE_TASK'],
            'VM_PRIVATE_TASK_OP_STOP_PACK_GRAIN_RECOVERY_FILES_TASK' => Xphp::$_lang['WEB_GRAIN_RECOVERY_STOP_PACK_FILE_TASK'],
            'VM_PRIVATE_TASK_OP_DELETE_PACK_GRAIN_RECOVERY_FILES_TASK' => Xphp::$_lang['WEB_GRAIN_RECOVERY_DELETE_PACK_FILE_TASK'],
            //--------------------------------orchestration recovery----------------------------------
            'VM_PRIVATE_TASK_OP_CREATE_ORCH_TASK' => Xphp::$_lang['WEB_DRILLS_EMERGENCY_RECOVERY_CREATE_TASK'],
            'VM_PRIVATE_TASK_OP_START_ORCH_TASK' => Xphp::$_lang['WEB_DRILLS_EMERGENCY_RECOVERY_START_TASK'],
            'VM_PRIVATE_TASK_OP_STOP_ORCH_TASK' => Xphp::$_lang['WEB_DRILLS_EMERGENCY_RECOVERY_STOP_TASK'],
            'VM_PRIVATE_TASK_OP_DELETE_ORCH_TASK' => Xphp::$_lang['WEB_DRILLS_EMERGENCY_RECOVERY_DELETE_TASK'],
            //---------------------------- new grain recovery operation ---------------------------------
            'VM_PRIVATE_TASK_OP_GRAIN_LIST_CHILD_DIR' => Xphp::$_lang['WEB_PRIVATE_TASK_OP_GRAIN_LIST_CHILD_DIR'],
            'VM_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK' => Xphp::$_lang['WEB_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK'],
            'VM_PRIVATE_TASK_OP_GRAIN_SEARCH_FILES' => Xphp::$_lang['WEB_PRIVATE_TASK_OP_GRAIN_SEARCH_FILES'],
            
            'VM_PRIVATE_TASK_OP_GRAIN_LIST_CHILD_DIR_NAMES' => Xphp::$_lang['WEB_PRIVATE_TASK_OP_GRAIN_LIST_CHILD_DIR_NAMES'],
            'VM_PRIVATE_TASK_OP_GRAIN_GET_ITEMS_STATS' => Xphp::$_lang['WEB_PRIVATE_TASK_OP_GRAIN_GET_ITEMS_STATS'],
            'VM_PRIVATE_TASK_OP_GRAIN_START_DOWNLOAD_DIR' => Xphp::$_lang['WEB_PRIVATE_TASK_OP_GRAIN_START_DOWNLOAD_DIR'],
            'VM_PRIVATE_TASK_OP_GRAIN_PROCESS_DOWNLOAD_DIR' => Xphp::$_lang['WEB_PRIVATE_TASK_OP_GRAIN_PROCESS_DOWNLOAD_DIR'],
            'VM_PRIVATE_TASK_OP_GRAIN_STOP_DOWNLOAD_DIR' => Xphp::$_lang['WEB_PRIVATE_TASK_OP_GRAIN_STOP_DOWNLOAD_DIR'],
            
            'VM_PRIVATE_TASK_OP_PF_BAKCUP_DELETE_TIMEPOINT' => Xphp::$_lang['WEB_VM_PRIVATE_TASK_OP_PF_BAKCUP_DELETE_TIMEPOINT'],
            'VM_PRIVATE_TASK_OP_PF_BACKUP_DOWNLOAD_BLOCK_FILE_BY_OFFSET' => Xphp::$_lang['WEB_VM_PRIVATE_TASK_OP_PF_BACKUP_DOWNLOAD_BLOCK_FILE_BY_OFFSET'],
            
            //--------------------------new add for sure backup task
            'VM_PRIVATE_TASK_OP_CREATE_SURE_BACKUP_TASK'=> Xphp::$_lang['WEB_VM_PRIVATE_TASK_OP_CREATE_SURE_BACKUP_TASK'],
            'VM_PRIVATE_TASK_OP_START_SURE_BACKUP_TASK' => Xphp::$_lang['WEB_VM_PRIVATE_TASK_OP_START_SURE_BACKUP_TASK'],
            'VM_PRIVATE_TASK_OP_STOP_SURE_BACKUP_TASK' => Xphp::$_lang['WEB_VM_PRIVATE_TASK_OP_STOP_SURE_BACKUP_TASK'],
            'VM_PRIVATE_TASK_OP_DELETE_SURE_BACKUP_TASK' => Xphp::$_lang['WEB_VM_PRIVATE_TASK_OP_DELETE_SURE_BACKUP_TASK'],

            'VM_PRIVATE_TASK_OP_EXCLUDE_SELECTED_VMS' => Xphp::$_lang['WEB_VM_PRIVATE_TASK_OP_EXCLUDE_SELECTED_VMS'],
        );
    }
}
?>