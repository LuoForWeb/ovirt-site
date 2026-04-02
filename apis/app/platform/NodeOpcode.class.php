<?php
require_once XPHP_PATH.'utils/OpcodeHandler.class.php';
class NodeOpcode extends OpcodeHandler{

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
            'NODE_OP_TYPE_UNKNOWN',           //unknown opcode type
            'NODE_OP_TYPE_STORAGE',
            'NODE_OP_TYPE_SYSTEM',		      // system operation code type
            'NODE_OP_TYPE_APPLIANCE',		  // appliance operation type
            'NODE_OP_TYPE_AGENT',			// agent operation code type
            'NODE_OP_TYPE_NAS',
            'NODE_OP_TYPE_NETWORK',		// node network operation code type
            'NODE_OP_TYPE_STRATEGY',
            'NODE_OP_TYPE_HUAWEI_CBR'  // huawei cbr operation code type
        );
    }

    /**
     * 初始化opcode
     */
    private function initOpcode(){
        $this->opcode = array(
            'PT_OP_TYPE_UNKNOWN' => array(),


            // node server storage opcode
            'NODE_OP_TYPE_STORAGE' => array(
                'NODE_SR_OP_UNKNOWN',			    //unknow storage opcode
                'NODE_SR_OP_SCAN_LOCAL',			// scan lvm, disk and part
                'NODE_SR_OP_SCAN_FC',				// scan fc lun
                'NODE_SR_OP_SCAN_ISCSI',			// scan iscsi lun

                'NODE_SR_OP_GET_FC_HOST_WWN',		// get fc host wwn list
                'NODE_SR_OP_GET_ISCSI_INIT_IQN',	// get iscsi initiator iqn
                'NODE_SR_OP_GET_ISCSI_TARGET_IQN',  // get iscsi target iqn

                'NODE_SR_OP_MODIFY_DISK_SR',		// modify disk nickname
                'MODE_SR_OP_MODIFY_NFS_SR',         // modify storage opcode
                'NODE_SR_OP_MODIFY_CIFS_SR',		// modify cifs

                'NODE_SR_OP_TEST_DISK_PART_LVM',	// test disk, part, lvm validity
                'NODE_SR_OP_TEST_NFS',			    // test nfs validity
                'NODE_SR_OP_TEST_CIFS',			    // test cifs validity

                'NODE_SR_OP_IMPORT_DISK_PART_LVM',  // import disk, part, lvm
                'NODE_SR_OP_IMPORT_NFS',			// import nfs sr
                'NODE_SR_OP_IMPORT_CIFS',			// import cifs

                'NODE_SR_OP_ADD_DISK_PART_LVM',	    // add disk, part, lvm sr
                'NODE_SR_OP_ADD_NFS',				// add nfs sr
                'NODE_SR_OP_ADD_CIFS',			    // add cifs sr
                'NODE_SR_OP_ADD_ISCSI_DISK',		// add iscsi disk

                'NODE_SR_OP_DELETE',				// delete storage opcode
                'NODE_SR_OP_MOTION',				// motion storage content opcode

                'NODE_SR_OP_TEST_REMOTE_SYSTEM',	// network and authentication test
                'NODE_SR_OP_ADD_REMOTE_SYSTEM',	    // add remote system
                'NODE_SR_OP_SCAN_REMOTE_SYSTEM',	// scan storage resource of remote system
                'NODE_SR_OP_DELETE_REMOTE_SYSTEM',	// delete remote system opcode

                'NODE_SR_OP_GET_CLOUD_BUCKET',	    // get bucket of account
                'NODE_SR_OP_GET_CLOUD_FOLDER',	    // get folders of bucket
                'NODE_SR_OP_SCAN_CLOUD_STORAGE',	// scan cloud object storage
                'NODE_SR_OP_ADD_CLOUD_STORAGE',	    // add cloud object storage

                'NODE_SR_OP_ADD_DIR',				// add dir sr
                'NODE_SR_OP_TEST_DIR',			// test directory sr
                'NODE_SR_OP_TIMEPOINTS_IMPORT_SYNC',	// storage timepoints import sync

                34 => 'NODE_SR_OP_ADD_PARALLEL_FILESYSTEM', // add parallel filesystem
                35 => 'NODE_SR_OP_TEST_PARALLEL_FILESYSTEM', //test parallel filesystem


                39 => 'NODE_SR_OP_SCAN_ISCSI_WITH_CHAP', // iscis chap 认证
                40 => 'NODE_SR_OP_SCAN_ISCSI_WITH_DISCOVER_CHAP', // iscis discover chap 认证
                50 => 'NODE_SR_OP_MODIFY_REMOTE_SYSTEM_INFO', // 修改异地存储

            ),

            'NODE_OP_TYPE_SYSTEM' =>array(
                'NODE_SYS_OP_UNKNOWN',		        // unknow system opcode
                'NODE_SYS_OP_DO_CMD',				// do system command
                'NODE_SYS_OP_DO_CMD_WITH_DETAIL',	// do system command with detail
                // system update
                'NODE_SYS_OP_WRITE_FILE_BY_OFFSET',	// write system file content
                'NODE_SYS_OP_CHECK_PATCH_VALIDITY',	// check update patch is valid or not for node
                'NODE_SYS_OP_DO_MASTER_UPDATE',		// do master update
                'NODE_SYS_OP_DO_NODE_UPDATE',		// do node update
                'NODE_SYS_OP_DOWNLOAD',				// download the file

                /* file read/write operation */
                'NODE_SYS_OP_GET_FILE_SIZE',			// get file size
                'NODE_SYS_OP_PREAD_FILE',				// pread file
                'NODE_SYS_OP_PWRITE_FILE',			// pwrite file
                'NODE_SYS_OP_SYNC_INFO',            //sync info
                'NODE_SYS_OP_PATCH_UPLOAD_CHECK',   //patch upload check
                'NODE_SYS_OP_OPERATE_NODE_SERVER',  // add|modify|delete node_server
                'NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER',  //子节点操作
                'NODE_SYS_OP_CONFIG_SYSTEM_CACHE', // 设置节点缓存
            ),
            'NODE_OP_TYPE_APPLIANCE' => array(
                'NODE_APPLIANCE_OP_UNKNOWN',
                'NODE_APPLIANCE_OP_ADD',		//add new appliance
                'NODE_APPLIANCE_OP_DEL',		//delete appliance
                'NODE_APPLIANCE_OP_MODIFY',	//modify appliance info

            ),
            'NODE_OP_TYPE_AGENT' => array(
                'NODE_AGENT_OP_UNKNOWN',
                'NODE_AGENT_OP_ADD',							//add new agent(remote push+install+get host information)
                'NODE_AGENT_OP_REFRESH',                        //refresh agent
                'NODE_AGENT_OP_UPGRADE',						//upgrade agent(remote push+upgrade install)
                'NODE_AGENT_OP_DEL',							//delete the agent
                'NODE_AGENT_OP_MODIFY',						//modify the agent
                'NODE_AGENT_OP_QUERY_DIR_LIST',				//query dir list(include file, for backup)
                'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST',		//query dir list(exclude file, for recovery)
                // 'NODE_AGENT_OP_ALLOCATION',                     //allocation agent
                'NODE_AGENT_OP_LIST_AGENT_LOG',               //list agent log info
                'NODE_AGENT_OP_DOWNLOAD_AGENT_LOG',            //download agent log

            ),
            'NODE_OP_TYPE_NAS' => array(
                'NODE_NAS_OP_UNKNOWN',
                'NODE_OP_ADD_CIFS_NAS',  // add cifs nas
                'NODE_OP_ADD_NFS_NAS',  // add nfs nas
                'NODE_OP_DELETE_NAS',   // delete nas
                'NODE_OP_MODIFY_CIFS_NAS', // modify cifs nas
                'NODE_OP_MODIFY_NFS_NAS',  // modify nfs nas
                'NODE_OP_UMOUNT_NAS',
                'NODE_OP_MOUNT_NAS',
                'NODE_OP_MOUNT_NAS_AGAIN',
            ),
            'NODE_OP_TYPE_NETWORK' => array(
                'NODE_NETWORK_OP_UNKNONWN',
                'NODE_NETWORK_OP_ADD',						//add network(map ip and port)
                'NODE_NETWORK_OP_MODIFY',						//modify network
                'NODE_NETWORK_OP_ADJUST_ORDER',				//adjust network order
                'NODE_NETWORK_OP_DEL',						//delete network
            ),
            'NODE_OP_TYPE_HUAWEI_CBR' => array(
                'NODE_HUAWEI_CBR_OP_UNKOWN',              // unknow opcode
                'NODE_HUAWEI_CBR_OP_ADD',                 // add huawei cbr storage
                'NODE_HUAWEI_CBR_OP_REFERSH',             //refresh huawei cbr storage
                'NODE_HUAWEI_CBR_OP_MODIFY',              //modify huawei cbr storage
                'NODE_HUAWEI_CBR_OP_DELETE',              //delete huawei cbr storage
                'NODE_HUAWEI_CBR_OP_GET_REGIONS',         //get regions that huawei cbr storage
                'NODE_HUAWEI_CBR_OP_GET_VAULTS',          //get vaults that huawei cbr storage
                'NODE_HUAWEI_CBR_OP_GET_BACKUPS',          // get backups that huawei cbr storage
                'NODE_HUAWEI_CBR_OP_GET_VAULTS_BY_LIST',    //get edit huawei cbr vaults
                'NODE_HUAWEI_CBR_OP_GET_BACKUPS_BY_LIST',   //get edit huawei cbr backups
            )
        );
    }

    /**
     * 初始化操作描述
     */
    private function initOpcodeDes(){
        $this->opDes = array(
            'NODE_SR_OP_SCAN_LOCAL' => Xphp::$_lang['WEB_NODE_OP_SCAN_LOCAL'],
            'NODE_SR_OP_SCAN_FC' => Xphp::$_lang['WEB_NODE_OP_SCAN_FC'],
            'NODE_SR_OP_SCAN_ISCSI' => Xphp::$_lang['WEB_NODE_OP_SCAN_ISCSI'],
            'NODE_SR_OP_SCAN_ISCSI_WITH_CHAP' => Xphp::$_lang['WEB_NODE_SR_OP_SCAN_ISCSI_WITH_CHAP'],
            'NODE_SR_OP_SCAN_ISCSI_WITH_DISCOVER_CHAP' => Xphp::$_lang['WEB_NODE_SR_OP_SCAN_ISCSI_WITH_DISCOVER_CHAP'],
            'NODE_SR_OP_GET_FC_HOST_WWN' => Xphp::$_lang['WEB_NODE_OP_GET_FC_HOST_WWN'],
            'NODE_SR_OP_GET_ISCSI_INIT_IQN' => Xphp::$_lang['WEB_NODE_OP_GET_ISCSI_INIT_IQN'],
            'NODE_SR_OP_GET_ISCSI_TARGET_IQN' => Xphp::$_lang['WEB_NODE_OP_GET_ISCSI_TARGET_IQN'],
            'NODE_SR_OP_MODIFY_DISK_SR' => Xphp::$_lang['WEB_NODE_OP_MODIFY_DISK_SR'],
            'MODE_SR_OP_MODIFY_NFS_SR' => Xphp::$_lang['WEB_NODE_OP_MODIFY_NFS_SR'],
            'NODE_SR_OP_MODIFY_CIFS_SR' => Xphp::$_lang['WEB_NODE_OP_MODIFY_CIFS_SR'],
            'NODE_SR_OP_TEST_DISK_PART_LVM' => Xphp::$_lang['WEB_NODE_OP_TEST_DISK_PART_LVM'],
            'NODE_SR_OP_TEST_NFS' => Xphp::$_lang['WEB_NODE_OP_TEST_NFS'],
            'NODE_SR_OP_TEST_CIFS' => Xphp::$_lang['WEB_NODE_OP_TEST_CIFS'],
            'NODE_SR_OP_IMPORT_DISK_PART_LVM' => Xphp::$_lang['WEB_NODE_OP_IMPORT_DISK_PART_LVM'],
            'NODE_SR_OP_IMPORT_NFS' => Xphp::$_lang['WEB_NODE_OP_IMPORT_NFS'],
            'NODE_SR_OP_IMPORT_CIFS' => Xphp::$_lang['WEB_NODE_OP_IMPORT_CIFS'],
            'NODE_SR_OP_ADD_DISK_PART_LVM' => Xphp::$_lang['WEB_NODE_OP_ADD_DISK_PART_LVM'],
            'NODE_SR_OP_ADD_NFS' => Xphp::$_lang['WEB_NODE_OP_ADD_NFS'],
            'NODE_SR_OP_ADD_CIFS' => Xphp::$_lang['WEB_NODE_OP_ADD_CIFS'],
            'NODE_SR_OP_ADD_ISCSI_DISK' => Xphp::$_lang['WEB_NODE_OP_ADD_ISCSI'],
            'NODE_SR_OP_DELETE' => Xphp::$_lang['WEB_NODE_OP_DELETE'],
            'NODE_SR_OP_MOTION' => Xphp::$_lang['WEB_NODE_OP_MOTION'],

            'NODE_SR_OP_TEST_REMOTE_SYSTEM' => Xphp::$_lang['WEB_NODE_OP_TEST_REMOTE_SYSTEM'],
            'NODE_SR_OP_ADD_REMOTE_SYSTEM' => Xphp::$_lang['WEB_NODE_OP_ADD_REMOTE_SYSTEM'],
            'NODE_SR_OP_SCAN_REMOTE_SYSTEM' => Xphp::$_lang['WEB_NODE_OP_SCAN_REMOTE_SYSTEM'],
            'NODE_SR_OP_DELETE_REMOTE_SYSTEM' => Xphp::$_lang['WEB_NODE_OP_DELETE_REMOTE_SYSTEM'],

            'NODE_SR_OP_GET_CLOUD_BUCKET'  => Xphp::$_lang['WEB_NODE_OP_GET_CLOUD_BUCKET'],
            'NODE_SR_OP_GET_CLOUD_FOLDER'  => Xphp::$_lang['WEB_NODE_OP_GET_CLOUD_FOLDER'],
            'NODE_SR_OP_SCAN_CLOUD_STORAGE'  => Xphp::$_lang['WEB_NODE_OP_SCAN_CLOUD_STORAGE'],
            'NODE_SR_OP_ADD_CLOUD_STORAGE'  => Xphp::$_lang['WEB_NODE_OP_ADD_CLOUD_STORAGE'],
            'NODE_SR_OP_ADD_PARALLEL_FILESYSTEM' => Xphp::$_lang['WEB_NODE_SR_OP_ADD_PARALLEL_FILESYSTEM'],
            'NODE_SR_OP_TEST_PARALLEL_FILESYSTEM' => Xphp::$_lang['WEB_NODE_SR_OP_TEST_PARALLEL_FILESYSTEM'],

            'NODE_SR_OP_ADD_DIR' => Xphp::$_lang['WEB_NODE_OP_ADD_DIR'],
            'NODE_SR_OP_TEST_DIR' => Xphp::$_lang['WEB_NODE_OP_TEST_DIR'],
            'NODE_SR_OP_TIMEPOINTS_IMPORT_SYNC' =>  Xphp::$_lang['WEB_NODE_OP_TIMEPOINTS_IMPORT_SYNC'],

            'NODE_SYS_OP_WRITE_FILE_BY_OFFSET' => Xphp::$_lang['WEB_NODE_OP_WRITE_FILE_BY_OFFSET'],
            'NODE_SYS_OP_CHECK_PATCH_VALIDITY' => Xphp::$_lang['WEB_NODE_OP_CHECK_PATCH_VALIDITY'],
            'NODE_SYS_OP_DO_MASTER_UPDATE' => Xphp::$_lang['WEB_NODE_OP_DO_MASTER_UPDATE'],
            'NODE_SYS_OP_DO_NODE_UPDATE' => Xphp::$_lang['WEB_NODE_OP_DO_NODE_UPDATE'],
            'NODE_SYS_OP_DOWNLOAD' => Xphp::$_lang['WEB_NODE_OP_DOWNLOAD'],


            /* file read/write operation */
            'NODE_SYS_OP_GET_FILE_SIZE' => Xphp::$_lang['UI_PLATFORM_GET_FILESIZE'],
            'NODE_SYS_OP_PREAD_FILE' => Xphp::$_lang['UI_PLATFORM_READ_FILECONTENT'],
            'NODE_SYS_OP_PWRITE_FILE' => Xphp::$_lang['UI_PLATFORM_WRITE_FILECONTENT'],
            'NODE_SYS_OP_SYNC_INFO' => Xphp::$_lang['WEB_NODE_SYS_OP_SYNC_INFO'],     //sync info
            'NODE_SYS_OP_PATCH_UPLOAD_CHECK' => Xphp::$_lang['WEB_NODE_SYS_OP_PATCH_UPLOAD_CHECK'],   //patch upload check
            'NODE_SYS_OP_OPERATE_NODE_SERVER' => Xphp::$_lang['WEB_NODE_SYS_OP_OPERATE_NODE_SERVER'],
            'NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER' => Xphp::$_lang['WEB_NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER'],
            'NODE_SYS_OP_CONFIG_SYSTEM_CACHE' => Xphp::$_lang['WEB_NODE_OP_SET_CACHE'],

            'NODE_APPLIANCE_OP_ADD' => Xphp::$_lang['WEB_NODE_APPLIANCE_OP_ADD'],
            'NODE_APPLIANCE_OP_DEL' => Xphp::$_lang['WEB_NODE_APPLIANCE_OP_DEL'],
            'NODE_APPLIANCE_OP_MODIFY' => Xphp::$_lang['WEB_NODE_APPLIANCE_OP_MODIFY'],

            'NODE_AGENT_OP_UNKNOWN'=>Xphp::$_lang['WEB_NODE_AGENT_OP_UNKNOWN'],
            'NODE_AGENT_OP_ADD'=>Xphp::$_lang['WEB_NODE_AGENT_OP_ADD'],
            'NODE_AGENT_OP_REFRESH' => Xphp::$_lang['WEB_CLIENT_REFRESH'],
            'NODE_AGENT_OP_UPGRADE'=>Xphp::$_lang['WEB_NODE_AGENT_OP_UPGRADE'],
            'NODE_AGENT_OP_DEL'=>Xphp::$_lang['WEB_NODE_AGENT_OP_DEL'],
            'NODE_AGENT_OP_MODIFY'=>Xphp::$_lang['WEB_NODE_AGENT_OP_MODIFY'],
            'NODE_AGENT_OP_QUERY_DIR_LIST'=>Xphp::$_lang['WEB_NODE_AGENT_OP_QUERY_DIR_LIST'],
            'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST'=>Xphp::$_lang['WEB_NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST'],
            'NODE_AGENT_OP_ALLOCATION'=>Xphp::$_lang['WEB_CLIENT_ALLOCATION'],
            'NODE_OP_ADD_CIFS_NAS'=>Xphp::$_lang['WEB_NODE_OP_ADD_CIFS_NAS'],  // add cifs nas
            'NODE_OP_ADD_NFS_NAS'=>Xphp::$_lang['WEB_NODE_OP_ADD_NFS_NAS'],  // add nfs nas
            'NODE_OP_DELETE_NAS'=>Xphp::$_lang['WEB_NODE_OP_DELETE_NAS'],   // delete nas
            'NODE_OP_MODIFY_CIFS_NAS'=>Xphp::$_lang['WEB_NODE_OP_MODIFY_CIFS_NAS'], // modify cifs nas
            'NODE_OP_MODIFY_NFS_NAS'=>Xphp::$_lang['WEB_NODE_OP_MODIFY_NFS_NAS'],  // modify nfs nas
            'NODE_NAS_OP_UNKNOWN'=>Xphp::$_lang['WEB_NODE_NAS_OP_UNKNOWN'],
            'NODE_OP_UMOUNT_NAS'=>Xphp::$_lang['WEB_NODE_OP_UMOUNT_NAS'],
            'NODE_OP_MOUNT_NAS'=>Xphp::$_lang['WEB_NODE_OP_MOUNT_NAS'],
            'NODE_OP_MOUNT_NAS_AGAIN'=>Xphp::$_lang['WEB_NODE_OP_MOUNT_NAS_AGAIN'],
            'NODE_AGENT_OP_LIST_AGENT_LOG' => Xphp::$_lang['WEB_NODE_AGENT_OP_LIST_AGENT_LOG'],
            'NODE_AGENT_OP_DOWNLOAD_AGENT_LOG' => Xphp::$_lang['WEB_NODE_AGENT_OP_DOWNLOAD_AGENT_LOG'],


            'NODE_NETWORK_OP_UNKNONWN' => Xphp::$_lang['WEB_NODE_AGENT_OP_UNKNOWN'],
            'NODE_NETWORK_OP_ADD' => Xphp::$_lang['WEB_NODE_OP_ADD_MAP_NETWORK'],						//add network(map ip and port)
            'NODE_NETWORK_OP_MODIFY' => Xphp::$_lang['WEB_NODE_OP_MODIFY_MAP_NETWORK'],										//modify network
            'NODE_NETWORK_OP_ADJUST_ORDER' => Xphp::$_lang['WEB_NODE_OP_SORT_MAP_NETWORK'],							//adjust network order
            'NODE_NETWORK_OP_DEL' => Xphp::$_lang['WEB_NODE_OP_DELETE_MAP_NETWORK'],								//delete network


            'NODE_HUAWEI_CBR_OP_UNKOWN'=>Xphp::$_lang['WEB_NODE_AGENT_OP_UNKNOWN'],              // unknow opcode
            'NODE_HUAWEI_CBR_OP_ADD'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_ADD'],        // add huawei cbr storage
            'NODE_HUAWEI_CBR_OP_REFERSH'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_REFERSH'],       //refresh huawei cbr storage
            'NODE_HUAWEI_CBR_OP_MODIFY'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_MODIFY'],       //modify huawei cbr storage
            'NODE_HUAWEI_CBR_OP_DELETE'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_DELETE'],         //delete huawei cbr storage
            'NODE_HUAWEI_CBR_OP_GET_REGIONS'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_GET_REGIONS'],    //get regions that huawei cbr storage
            'NODE_HUAWEI_CBR_OP_GET_VAULTS'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_GET_VAULTS'],     //get vaults that huawei cbr storage
            'NODE_HUAWEI_CBR_OP_GET_BACKUPS'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_GET_BACKUPS'],    // get backups that huawei cbr storage
            'NODE_HUAWEI_CBR_OP_GET_VAULTS_BY_LIST'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_GET_VAULTS_BY_LIST'],//get edit huawei cbr vaults
            'NODE_HUAWEI_CBR_OP_GET_BACKUPS_BY_LIST'=>Xphp::$_lang['WEB_NODE_HUAWEI_CBR_OP_GET_BACKUPS_BY_LIST'],//get edit huawei cbr backups


        );
    }
}
?>