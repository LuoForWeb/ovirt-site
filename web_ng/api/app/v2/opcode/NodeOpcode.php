<?php

namespace app\v2\opcode;

use xphp\OpcodeHandler;

class NodeOpcode extends OpcodeHandler
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

        $this->opcodeLevel = xphp_get_config('app')['OPCODE_LEVEL']['PRIVATE'];
    }

    /**
     * 初始化opcode type
     * @return void
     */
    private function initOpcodeType()
    {

        $this->opcodeType = array(
            'NODE_OP_TYPE_UNKNOWN',           //unknown opcode type
            'NODE_OP_TYPE_STORAGE',
            'NODE_OP_TYPE_SYSTEM',            // system operation code type
            'NODE_OP_TYPE_APPLIANCE',         // appliance operation type
            'NODE_OP_TYPE_AGENT',           // agent operation code type
            'NODE_OP_TYPE_NAS',
            'NODE_OP_TYPE_NETWORK',     // node network operation code type
            7 => 'NODE_OP_TYPE_STRATEGY',    // node storategy
            'NODE_OP_TYPE_HUAWEI_CBR',  // huawei cbr operation code type
            'NODE_OP_TYPE_TAPE',   //tape operation code type
            'NODE_OP_TYPE_LUN',  //lun storage
            12 => 'NODE_OP_TYPE_TASK',
            999 => 'NODE_OP_TYPE_OBS'
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {

        $this->opcode = array(
            'PT_OP_TYPE_UNKNOWN' => array(),


            // node server storage opcode
            'NODE_OP_TYPE_STORAGE' => array(
                'NODE_SR_OP_UNKNOWN',               //unknow storage opcode
                'NODE_SR_OP_SCAN_LOCAL',            // scan lvm, disk and part
                'NODE_SR_OP_SCAN_FC',               // scan fc lun
                'NODE_SR_OP_SCAN_ISCSI',            // scan iscsi lun

                'NODE_SR_OP_GET_FC_HOST_WWN',       // get fc host wwn list
                'NODE_SR_OP_GET_ISCSI_INIT_IQN',    // get iscsi initiator iqn
                'NODE_SR_OP_GET_ISCSI_TARGET_IQN',  // get iscsi target iqn

                'NODE_SR_OP_MODIFY_DISK_SR',        // modify disk nickname
                'MODE_SR_OP_MODIFY_NFS_SR',         // modify storage opcode
                'NODE_SR_OP_MODIFY_CIFS_SR',        // modify cifs

                10 => 'NODE_SR_OP_TEST_DISK_PART_LVM',    // test disk, part, lvm validity
                'NODE_SR_OP_TEST_NFS',              // test nfs validity
                'NODE_SR_OP_TEST_CIFS',             // test cifs validity

                'NODE_SR_OP_IMPORT_DISK_PART_LVM',  // import disk, part, lvm
                'NODE_SR_OP_IMPORT_NFS',            // import nfs sr
                'NODE_SR_OP_IMPORT_CIFS',           // import cifs

                'NODE_SR_OP_ADD_DISK_PART_LVM',     // add disk, part, lvm sr
                'NODE_SR_OP_ADD_NFS',               // add nfs sr
                'NODE_SR_OP_ADD_CIFS',              // add cifs sr
                'NODE_SR_OP_ADD_ISCSI_DISK',        // add iscsi disk

                20 => 'NODE_SR_OP_DELETE',                // delete storage opcode
                'NODE_SR_OP_MOTION',                // motion storage content opcode

                'NODE_SR_OP_TEST_REMOTE_SYSTEM',    // network and authentication test
                'NODE_SR_OP_ADD_REMOTE_SYSTEM',     // add remote system
                'NODE_SR_OP_SCAN_REMOTE_SYSTEM',    // scan storage resource of remote system
                'NODE_SR_OP_DELETE_REMOTE_SYSTEM',  // delete remote system opcode

                'NODE_SR_OP_GET_CLOUD_BUCKET',      // get bucket of account
                'NODE_SR_OP_GET_CLOUD_FOLDER',      // get folders of bucket
                'NODE_SR_OP_SCAN_CLOUD_STORAGE',    // scan cloud object storage
                'NODE_SR_OP_ADD_CLOUD_STORAGE',     // add cloud object storage

                30 => 'NODE_SR_OP_ADD_DIR',               // add dir sr
                'NODE_SR_OP_TEST_DIR',          // test directory sr
                'NODE_SR_OP_TIMEPOINTS_IMPORT_SYNC',    // storage timepoints import sync
                'NODE_SR_OP_TIMEPOINTS_IMPORT_MANUAL_SYNC',    // storage Manual Synchronization Import

                34 => 'NODE_SR_OP_ADD_PARALLEL_FILESYSTEM', // add parallel filesystem
                35 => 'NODE_SR_OP_TEST_PARALLEL_FILESYSTEM', //test parallel filesystem
                36 => 'NODE_SR_OP_ADD_POOL',      //添加存储资源池
                'NODE_SR_OP_DELETE_POOL',   //删除存储资源池
                'NODE_SR_OP_MODIFY_POOL',   //修改存储资源池


                39 => 'NODE_SR_OP_SCAN_ISCSI_WITH_CHAP', // iscis chap 认证
                40 => 'NODE_SR_OP_SCAN_ISCSI_WITH_DISCOVER_CHAP', // iscis discover chap 认证
                50 => 'NODE_SR_OP_MODIFY_REMOTE_SYSTEM_INFO', // 修改异地存储
                'NODE_SR_OP_MODIFY_NFS_STORAGE_NODE', // 修改NFS存储挂载节点
                'NODE_SR_OP_MODIFY_CIFS_STORAGE_NODE', // 修改CIFS存储挂载节点
                'NODE_SR_OP_MODIFY_CLOUD_STORAGE_NODE', // 修改云存储挂载节点
                'NODE_SR_OP_TEST_DDBOOST', // test Dell DDBoost，值为54
                'NODE_SR_OP_ADD_DDBOOST', // add Dell DDBoost，值为55
                'NODE_SR_OP_MODIFY_DDBOOST_SR', // modify DDBoost resource ，值为56
            ),

            'NODE_OP_TYPE_SYSTEM' => array(
                'NODE_SYS_OP_UNKNOWN',              // unknow system opcode
                'NODE_SYS_OP_DO_CMD',               // do system command
                'NODE_SYS_OP_DO_CMD_WITH_DETAIL',   // do system command with detail

                // system update
                'NODE_SYS_OP_WRITE_FILE_BY_OFFSET', // write system file content
                'NODE_SYS_OP_CHECK_PATCH_VALIDITY', // check update patch is valid or not for node
                'NODE_SYS_OP_DO_MASTER_UPDATE',     // do master update
                'NODE_SYS_OP_DO_NODE_UPDATE',       // do node update
                'NODE_SYS_OP_DOWNLOAD',             // download the file

                /* file read/write operation */
                'NODE_SYS_OP_GET_FILE_SIZE',            // get file size
                'NODE_SYS_OP_PREAD_FILE',               // pread file
                'NODE_SYS_OP_PWRITE_FILE',          // pwrite file
                'NODE_SYS_OP_SYNC_INFO',            //sync info
                'NODE_SYS_OP_PATCH_UPLOAD_CHECK',   //patch upload check
                'NODE_SYS_OP_OPERATE_NODE_SERVER',  // add|modify|delete node_server
                14 => 'NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER', // add|modify|delete remote node server
                15 => 'NODE_SYS_OP_CONFIG_SYSTEM_CACHE', // 设置节点缓存
                'NODE_SYS_OP_ADD_POOL',      //添加节点池
                'NODE_SYS_OP_DELETE_POOL',   //删除节点池
                'NODE_SYS_OP_MODIFY_POOL',   //修改节点池

                'NODE_SYS_OP_CONFIG_SYSTEM_CACHE',  // 设置节点缓存
                19 => 'NODE_SYS_OP_TRANSFER_USER_RESOURCE', // transfer user resource
                /* third monitor platform */
                20 => 'NODE_SYS_OP_ADD_THIRD_MONITOR_PLATFROM',  // add third monitor platform
                21 => 'NODE_SYS_OP_MODIFY_THIRD_MONITOR_PLATFROM',  // modify third monitor platform
                22 => 'NODE_SYS_OP_DELETE_THIRD_MONITOR_PLATFROM',  // delete third monitor platform
                23 => 'NODE_SYS_OP_ADD_ALARM_PUSH_STRATEGY',  // add alarm push strategy
                24 => 'NODE_SYS_OP_MODIFY_ALARM_PUSH_STRATEGY', // modify alarm push strategy
                25 => 'NODE_SYS_OP_DELETE_ALARM_PUSH_STRATEGY', // delete alarm push strategy
                26 => 'NODE_SYS_OP_ALTER_ALARM_PUSH_STRATEGY_STATUS',  // alter alarm push strategy status
                27 => 'NODE_SYS_OP_MANUAL_PUSH_ALARM',  // manual push alarm
                28 => 'NODE_SYS_OP_MANUAL_PUSH_ALARM_RESPONSE',  // manual push alarm response
                29 => 'NODE_SYS_OP_OPERTER_RANSOM_PROTECT', // 1-开启勒索防护，其他关闭

                41 => 'NODE_SYS_OP_GET_SYSTEM_LOG_LIST', // get node system log list
                42 => 'NODE_SYS_OP_DOWNLOAD_SYSTEM_LOG', // download systemctl log
                50 => 'NODE_SYS_OP_MODIFY_MASTER_NODE_INFO', // 主节点操作
            ),
            'NODE_OP_TYPE_APPLIANCE' => array(
                'NODE_APPLIANCE_OP_UNKNOWN',
                'NODE_APPLIANCE_OP_ADD',        //add new appliance
                'NODE_APPLIANCE_OP_DEL',        //delete appliance
                'NODE_APPLIANCE_OP_MODIFY', //modify appliance info
                'NODE_APPLIANCE_OP_ADD_POOL',     //添加传输代理池
                'NODE_APPLIANCE_OP_DELETE_POOL',  //删除传输代理池
                'NODE_APPLIANCE_OP_MODIFY_POOL',  //修改传输代理池

            ),
            'NODE_OP_TYPE_AGENT' => array(
                'NODE_AGENT_OP_UNKNOWN',
                // add new agent(remote push+install+get host information)
                'NODE_AGENT_OP_ADD',
                'NODE_AGENT_OP_REFRESH',                        //refresh agent
                'NODE_AGENT_OP_UPGRADE',                        //upgrade agent(remote push+upgrade install)
                'NODE_AGENT_OP_DEL',                            //delete the agent
                'NODE_AGENT_OP_MODIFY',                     //modify the agent
                'NODE_AGENT_OP_QUERY_DIR_LIST',             //query dir list(include file, for backup)
                'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST',        //query dir list(exclude file, for recovery)
                'NODE_AGENT_OP_LIST_AGENT_LOG',               //list agent log info
                'NODE_AGENT_OP_DOWNLOAD_AGENT_LOG',            //download agent log
                10 => 'NODE_AGENT_OP_MODIFY_DOMAIN_CONFIG', // 传输代理域名解析
                11 => 'NODE_AGENT_OP_QUERY_DOMAIN_CONFIG', // 传输代理域名刷新后获取
                12 => 'NODE_AGENT_OP_INSTALL_DRIVER_FOR_AGENT', // 安装客户端驱动

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
                'NODE_NETWORK_OP_ADD',                      //add network(map ip and port)
                'NODE_NETWORK_OP_MODIFY',                       //modify network
                'NODE_NETWORK_OP_ADJUST_ORDER',             //adjust network order
                'NODE_NETWORK_OP_DEL',                      //delete network
                'NODE_NETWORK_OP_ADD_POOL',                   //添加节点网络池
                'NODE_NETWORK_OP_DELETE_POOL',              //删除节点网络池
                'NODE_NETWORK_OP_MODIFY_POOL',             //修改节点网络池
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
                'NODE_HUAWEI_CBR_OP_GET_BACKUPS_BY_LIST'   //get edit huawei cbr backups
            ),
            'NODE_OP_TYPE_STRATEGY' => array(
                'NODE_OP_STRATEGY_UNKNOWN', // unknown
                'NODE_OP_ADD_SPEED_LIMIT_STRATEGY',     // add
                'NODE_OP_DEL_SPEED_LIMIT_STRATEGY',     // del
                'NODE_OP_MOD_SPEED_LIMIT_STRATEGY',     // edit
            ),
            'NODE_OP_TYPE_OBS' => array(
                'NODE_OBS_OP_UNKNOWN',
                'NODE_OBS_OP_ADD',
                'NODE_OBS_OP_REFRESH',
                'NODE_OBS_OP_DEL',
                'NODE_OBS_OP_MODIFY',
                'NODE_OBS_OP_QUERY_DIR_LIST',
                'NODE_OBS_OP_QUERY_RECOVERY_DIR_LIST'
            ),
            'NODE_OP_TYPE_TAPE' => array(
                'NODE_TAPE_OP_UNKOWN',
                'NODE_TAPE_OP_SCAN_TAPE_LIB',         // scan one or all tape library
                'NODE_TAPE_OP_SCAN_TAPE_DRIVER',      // scan one or all tape driver
                'NODE_TAPE_OP_RETRIEVE_TAPE_DATA',    // retrieve tape or tape lib stored data
                'NODE_TAPE_OP_EXPORT_TAPE_CARRIAGE',  // export tape carriage
                'NODE_TAPE_OP_IMPORT_TAPE_CARRIAGE',  // import tape carriage

                'NODE_TAPE_OP_CREATE_TAPE_GROUP',     // create tape group
                'NODE_TAPE_OP_MODIFY_TAPE_GROUP',     // modify tape group info
                'NODE_TAPE_OP_DELETE_TAPE_GROUP',     // delete tape group

                'NODE_TAPE_OP_DELETE_BACKUP_SET',     // delete backup sets
                'NODE_TAPE_OP_IMPORT_TAPE_GROUP',    // import tape group

                'NODE_TAPE_OP_FREEZE_BACKUP_SET', // 冻结磁带 11
                'NODE_TAPE_OP_THAW_BACKUP_SET', // 解冻磁带 12
            ),
            'NODE_OP_TYPE_LUN' => array(
                'NODE_OP_LUN_UNKNOWN',
                'NODE_OP_LUN_ADD',
                'NODE_OP_LUN_SYNC',
                'NODE_OP_LUN_MODIFY',
                'NODE_OP_LUN_DELETE',
                'NODE_STORAGE_INFRASTRUCTURE_OP_CHECK_INITIATOR',
                'NODE_STORAGE_INFRASTRUCTURE_OP_CHECK_IS_MAPPED', //检查节点下的存储状态
            ),
            'NODE_OP_TYPE_TASK' => [
                'NODE_OP_TASK_UNKNOWN',
                'NODE_TASK_OP_GET_TASK_HISTORY_IO_SUMMARY', // 最近5min的IO流量信息
            ],
        );
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {

        $this->opDes = array(

            'NODE_SR_OP_SCAN_LOCAL' => 'WEB_NODE_OP_SCAN_LOCAL',
            'NODE_SR_OP_SCAN_FC' => 'WEB_NODE_OP_SCAN_FC',
            'NODE_SR_OP_SCAN_ISCSI' => 'WEB_NODE_OP_SCAN_ISCSI',
            'NODE_SR_OP_SCAN_ISCSI_WITH_CHAP' => 'WEB_NODE_SR_OP_SCAN_ISCSI_WITH_CHAP',
            'NODE_SR_OP_SCAN_ISCSI_WITH_DISCOVER_CHAP' => 'WEB_NODE_SR_OP_SCAN_ISCSI_WITH_DISCOVER_CHAP',
            'NODE_SR_OP_GET_FC_HOST_WWN' => 'WEB_NODE_OP_GET_FC_HOST_WWN',
            'NODE_SR_OP_GET_ISCSI_INIT_IQN' => 'WEB_NODE_OP_GET_ISCSI_INIT_IQN',
            'NODE_SR_OP_GET_ISCSI_TARGET_IQN' => 'WEB_NODE_OP_GET_ISCSI_TARGET_IQN',
            'NODE_SR_OP_MODIFY_DISK_SR' => 'WEB_NODE_OP_MODIFY_DISK_SR',
            'MODE_SR_OP_MODIFY_NFS_SR' => 'WEB_NODE_OP_MODIFY_NFS_SR',
            'NODE_SR_OP_MODIFY_CIFS_SR' => 'WEB_NODE_OP_MODIFY_CIFS_SR',
            'NODE_SR_OP_TEST_DISK_PART_LVM' => 'WEB_NODE_OP_TEST_DISK_PART_LVM',
            'NODE_SR_OP_TEST_NFS' => 'WEB_NODE_OP_TEST_NFS',
            'NODE_SR_OP_TEST_CIFS' => 'WEB_NODE_OP_TEST_CIFS',
            'NODE_SR_OP_IMPORT_DISK_PART_LVM' => 'WEB_NODE_OP_IMPORT_DISK_PART_LVM',
            'NODE_SR_OP_IMPORT_NFS' => 'WEB_NODE_OP_IMPORT_NFS',
            'NODE_SR_OP_IMPORT_CIFS' => 'WEB_NODE_OP_IMPORT_CIFS',
            'NODE_SR_OP_ADD_DISK_PART_LVM' => 'WEB_NODE_OP_ADD_DISK_PART_LVM',
            'NODE_SR_OP_ADD_NFS' => 'WEB_NODE_OP_ADD_NFS',
            'NODE_SR_OP_ADD_CIFS' => 'WEB_NODE_OP_ADD_CIFS',
            'NODE_SR_OP_ADD_ISCSI_DISK' => 'WEB_NODE_OP_ADD_ISCSI',
            'NODE_SR_OP_DELETE' => 'WEB_NODE_OP_DELETE',
            'NODE_SR_OP_MOTION' => 'WEB_NODE_OP_MOTION',
            'NODE_SR_OP_MODIFY_REMOTE_SYSTEM_INFO' => 'WEB_NODE_SR_OP_MODIFY_REMOTE_SYSTEM_INFO',
            'NODE_SR_OP_MODIFY_NFS_STORAGE_NODE' => 'WEB_NODE_SR_OP_MODIFY_NFS_STORAGE_NODE',
            'NODE_SR_OP_MODIFY_CIFS_STORAGE_NODE' => 'WEB_NODE_SR_OP_MODIFY_CIFS_STORAGE_NODE',
            'NODE_SR_OP_MODIFY_CLOUD_STORAGE_NODE' => 'WEB_NODE_SR_OP_MODIFY_CLOUD_STORAGE_NODE',
            'NODE_SR_OP_TEST_DDBOOST' => 'WEB_NODE_SR_OP_TEST_DDBOOST',
            'NODE_SR_OP_ADD_DDBOOST' => 'WEB_NODE_SR_OP_ADD_DDBOOST',
            'NODE_SR_OP_MODIFY_DDBOOST_SR' => 'WEB_NODE_SR_OP_MODIFY_DDBOOST_SR',

            'NODE_SR_OP_TEST_REMOTE_SYSTEM' => 'WEB_NODE_OP_TEST_REMOTE_SYSTEM',
            'NODE_SR_OP_ADD_REMOTE_SYSTEM' => 'WEB_NODE_OP_ADD_REMOTE_SYSTEM',
            'NODE_SR_OP_SCAN_REMOTE_SYSTEM' => 'WEB_NODE_OP_SCAN_REMOTE_SYSTEM',
            'NODE_SR_OP_DELETE_REMOTE_SYSTEM' => 'WEB_NODE_OP_DELETE_REMOTE_SYSTEM',

            'NODE_SR_OP_GET_CLOUD_BUCKET' => 'WEB_NODE_OP_GET_CLOUD_BUCKET',
            'NODE_SR_OP_GET_CLOUD_FOLDER' => 'WEB_NODE_OP_GET_CLOUD_FOLDER',
            'NODE_SR_OP_SCAN_CLOUD_STORAGE' => 'WEB_NODE_OP_SCAN_CLOUD_STORAGE',
            'NODE_SR_OP_ADD_CLOUD_STORAGE' => 'WEB_NODE_OP_ADD_CLOUD_STORAGE',
            'NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER' => 'WEB_NODE_SYS_OP_OPERATE_REMOTE_NODE_SERVER',
            'NODE_SYS_OP_CONFIG_SYSTEM_CACHE' => 'WEB_NODE_OP_SET_CACHE',

            'NODE_SR_OP_ADD_DIR' => 'WEB_NODE_OP_ADD_DIR',
            'NODE_SR_OP_TEST_DIR' => 'WEB_NODE_OP_TEST_DIR',
            //storage timepoints import sync
            'NODE_SR_OP_TIMEPOINTS_IMPORT_SYNC' => 'WEB_NODE_OP_TIMEPOINTS_IMPORT_SYNC',
            'NODE_SR_OP_TIMEPOINTS_IMPORT_MANUAL_SYNC' => 'WEB_NODE_SR_OP_TIMEPOINTS_IMPORT_MANUAL_SYNC',
            'NODE_SR_OP_ADD_POOL' => 'WEB_NODE_SR_OP_ADD_POOL', //添加存储资源池
            'NODE_SR_OP_DELETE_POOL' => 'WEB_NODE_SR_OP_DELETE_POOL', //删除存储资源池
            'NODE_SR_OP_MODIFY_POOL' => 'WEB_NODE_SR_OP_MODIFY_POOL', //修改存储资源池

            'NODE_SYS_OP_WRITE_FILE_BY_OFFSET' => 'WEB_NODE_OP_WRITE_FILE_BY_OFFSET',
            'NODE_SYS_OP_CHECK_PATCH_VALIDITY' => 'WEB_NODE_OP_CHECK_PATCH_VALIDITY',
            'NODE_SYS_OP_DO_MASTER_UPDATE' => 'WEB_NODE_OP_DO_MASTER_UPDATE',
            'NODE_SYS_OP_DO_NODE_UPDATE' => 'WEB_NODE_OP_DO_NODE_UPDATE',
            'NODE_SYS_OP_DOWNLOAD' => 'WEB_NODE_OP_DOWNLOAD',

            /* file read/write operation */
            'NODE_SYS_OP_GET_FILE_SIZE' => 'UI_PLATFORM_GET_FILESIZE',
            'NODE_SYS_OP_PREAD_FILE' => 'UI_PLATFORM_READ_FILECONTENT',
            'NODE_SYS_OP_PWRITE_FILE' => 'UI_PLATFORM_WRITE_FILECONTENT',
            'NODE_SYS_OP_SYNC_INFO' => 'WEB_NODE_SYS_OP_SYNC_INFO',     //sync info
            'NODE_SYS_OP_PATCH_UPLOAD_CHECK' => 'WEB_NODE_SYS_OP_PATCH_UPLOAD_CHECK',   //patch upload check
            'NODE_SYS_OP_OPERATE_NODE_SERVER' => 'WEB_NODE_SYS_OP_OPERATE_NODE_SERVER',
            'NODE_SYS_OP_ADD_POOL' => 'WEB_NODE_SYS_OP_ADD_POOL',      //添加节点池
            'NODE_SYS_OP_DELETE_POOL' => 'WEB_NODE_SYS_OP_DELETE_POOL',   //删除节点池
            'NODE_SYS_OP_MODIFY_POOL' => 'WEB_NODE_SYS_OP_MODIFY_POOL',   //修改节点池

            'NODE_SYS_OP_MODIFY_MASTER_NODE_INFO' => 'WEB_NODE_SYS_OP_MODIFY_MASTER_NODE_INFO', // 主节点操作

            'NODE_APPLIANCE_OP_ADD' => 'WEB_NODE_APPLIANCE_OP_ADD',
            'NODE_APPLIANCE_OP_DEL' => 'WEB_NODE_APPLIANCE_OP_DEL',
            'NODE_APPLIANCE_OP_MODIFY' => 'WEB_NODE_APPLIANCE_OP_MODIFY',
            'NODE_APPLIANCE_OP_ADD_POOL' => 'WEB_NODE_APPLIANCE_OP_ADD_POOL',     //添加传输代理池
            'NODE_APPLIANCE_OP_DELETE_POOL' => 'WEB_NODE_APPLIANCE_OP_DELETE_POOL',  //删除传输代理池
            'NODE_APPLIANCE_OP_MODIFY_POOL' => 'WEB_NODE_APPLIANCE_OP_MODIFY_POOL',  //修改传输代理池

            'NODE_AGENT_OP_UNKNOWN' => 'WEB_NODE_AGENT_OP_UNKNOWN',
            'NODE_AGENT_OP_ADD' => 'WEB_NODE_AGENT_OP_ADD',
            'NODE_AGENT_OP_REFRESH' => 'WEB_CLIENT_REFRESH',
            'NODE_AGENT_OP_UPGRADE' => 'WEB_NODE_AGENT_OP_UPGRADE',
            'NODE_AGENT_OP_DEL' => 'WEB_NODE_AGENT_OP_DEL',
            'NODE_AGENT_OP_MODIFY' => 'WEB_NODE_AGENT_OP_MODIFY',
            'NODE_AGENT_OP_QUERY_DIR_LIST' => 'WEB_NODE_AGENT_OP_QUERY_DIR_LIST',
            'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST' => 'WEB_NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST',
            'NODE_OP_ADD_CIFS_NAS' => 'WEB_NODE_OP_ADD_CIFS_NAS',  // add cifs nas
            'NODE_OP_ADD_NFS_NAS' => 'WEB_NODE_OP_ADD_NFS_NAS',  // add nfs nas
            'NODE_OP_DELETE_NAS' => 'WEB_NODE_OP_DELETE_NAS',   // delete nas
            'NODE_OP_MODIFY_CIFS_NAS' => 'WEB_NODE_OP_MODIFY_CIFS_NAS', // modify cifs nas
            'NODE_OP_MODIFY_NFS_NAS' => 'WEB_NODE_OP_MODIFY_NFS_NAS',  // modify nfs nas
            'NODE_NAS_OP_UNKNOWN' => 'WEB_NODE_NAS_OP_UNKNOWN',
            'NODE_OP_UMOUNT_NAS' => 'WEB_NODE_OP_UMOUNT_NAS',
            'NODE_OP_MOUNT_NAS' => 'WEB_NODE_OP_MOUNT_NAS',
            'NODE_OP_MOUNT_NAS_AGAIN' => 'WEB_NODE_OP_MOUNT_NAS_AGAIN',
            'NODE_AGENT_OP_LIST_AGENT_LOG' => 'WEB_NODE_AGENT_OP_LIST_AGENT_LOG',
            'NODE_AGENT_OP_DOWNLOAD_AGENT_LOG' => 'WEB_NODE_AGENT_OP_DOWNLOAD_AGENT_LOG',


            'NODE_NETWORK_OP_UNKNONWN' => 'WEB_NODE_AGENT_OP_UNKNOWN',
            // add network(map ip and port)
            'NODE_NETWORK_OP_ADD' => 'WEB_NODE_OP_ADD_MAP_NETWORK',
            // modify network
            'NODE_NETWORK_OP_MODIFY' => 'WEB_NODE_OP_MODIFY_MAP_NETWORK',
            //adjust network order
            'NODE_NETWORK_OP_ADJUST_ORDER' => 'WEB_NODE_OP_SORT_MAP_NETWORK',
            //delete network
            'NODE_NETWORK_OP_DEL' => 'WEB_NODE_OP_DELETE_MAP_NETWORK',
            'NODE_NETWORK_OP_ADD_POOL' => 'WEB_NODE_NETWORK_OP_ADD_POOL',                   //添加节点网络池
            'NODE_NETWORK_OP_DELETE_POOL' => 'WEB_NODE_NETWORK_OP_DELETE_POOL',              //删除节点网络池
            'NODE_NETWORK_OP_MODIFY_POOL' => 'WEB_NODE_NETWORK_OP_MODIFY_POOL',             //修改节点网络池

            'NODE_HUAWEI_CBR_OP_UNKOWN' => 'WEB_NODE_AGENT_OP_UNKNOWN',              // unknow opcode
            'NODE_HUAWEI_CBR_OP_ADD' => 'WEB_NODE_HUAWEI_CBR_OP_ADD',            // add huawei cbr storage
            'NODE_HUAWEI_CBR_OP_REFERSH' => 'WEB_NODE_HUAWEI_CBR_OP_REFERSH',        //refresh huawei cbr storage
            'NODE_HUAWEI_CBR_OP_MODIFY' => 'WEB_NODE_HUAWEI_CBR_OP_MODIFY',         //modify huawei cbr storage
            'NODE_HUAWEI_CBR_OP_DELETE' => 'WEB_NODE_HUAWEI_CBR_OP_DELETE',         //delete huawei cbr storage
            //get regions that huawei cbr storage
            'NODE_HUAWEI_CBR_OP_GET_REGIONS' => 'WEB_NODE_HUAWEI_CBR_OP_GET_REGIONS',
            //get vaults that huawei cbr storage
            'NODE_HUAWEI_CBR_OP_GET_VAULTS' => 'WEB_NODE_HUAWEI_CBR_OP_GET_VAULTS',
            // get backups that huawei cbr storage
            'NODE_HUAWEI_CBR_OP_GET_BACKUPS' => 'WEB_NODE_HUAWEI_CBR_OP_GET_BACKUPS',
            //get edit huawei cbr vaults
            'NODE_HUAWEI_CBR_OP_GET_VAULTS_BY_LIST' => 'WEB_NODE_HUAWEI_CBR_OP_GET_VAULTS_BY_LIST',
            //get edit huawei cbr backups
            'NODE_HUAWEI_CBR_OP_GET_BACKUPS_BY_LIST' => 'WEB_NODE_HUAWEI_CBR_OP_GET_BACKUPS_BY_LIST',
            'NODE_OP_ADD_SPEED_LIMIT_STRATEGY' => 'WEB_NODE_OP_ADD_SPEED_LIMIT_STRATEGY',     // add
            'NODE_OP_DEL_SPEED_LIMIT_STRATEGY' => 'WEB_NODE_OP_DEL_SPEED_LIMIT_STRATEGY',     // del
            'NODE_OP_MOD_SPEED_LIMIT_STRATEGY' => 'WEB_NODE_OP_MOD_SPEED_LIMIT_STRATEGY',     // edit

            'NODE_SYS_OP_TRANSFER_USER_RESOURCE' => 'UI_STORAGE_TRANSFER', // transfer user resource

            'NODE_TAPE_OP_UNKOWN' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_UNKOWN',
            // scan one or all tape library
            'NODE_TAPE_OP_SCAN_TAPE_LIB' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_SCAN_TAPE_LIB',
            // scan one or all tape driver
            'NODE_TAPE_OP_SCAN_TAPE_DRIVER' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_SCAN_TAPE_DRIVER',
            // retrieve tape or tape lib stored data
            'NODE_TAPE_OP_RETRIEVE_TAPE_DATA' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_RETRIEVE_TAPE_DATA',
            // export tape carriage
            'NODE_TAPE_OP_EXPORT_TAPE_CARRIAGE' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_EXPORT_TAPE_CARRIAGE',
            // import tape carriage
            'NODE_TAPE_OP_IMPORT_TAPE_CARRIAGE' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_IMPORT_TAPE_CARRIAGE',
            // create tape group
            'NODE_TAPE_OP_CREATE_TAPE_GROUP' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_CREATE_TAPE_GROUP',
            // modify tape group info
            'NODE_TAPE_OP_MODIFY_TAPE_GROUP' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_MODIFY_TAPE_GROUP',
            // delete tape group
            'NODE_TAPE_OP_DELETE_TAPE_GROUP' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_DELETE_TAPE_GROUP',
            // delete backup sets
            'NODE_TAPE_OP_DELETE_BACKUP_SET' => 'WEB_TAPE_PRIVATE_TAPE_OP_CODE_BACKUP_SET',
            'NODE_TAPE_OP_IMPORT_TAPE_GROUP' => 'WEB_TAPE_PRIVATE_NODE_TAPE_OP_IMPORT_TAPE_GROUP', // import tape group
            'NODE_TAPE_OP_FREEZE_BACKUP_SET' => 'WEB_TAPE_PRIVATE_NODE_TAPE_OP_FREEZE_BACKUP_SET',     // 冻结磁带
            'NODE_TAPE_OP_THAW_BACKUP_SET' => 'WEB_TAPE_PRIVATE_NODE_TAPE_OP_THAW_BACKUP_SET',     // 解冻磁带
            // OBS
            'NODE_OBS_OP_UNKNOWN' => 'WEB_NODE_AGENT_OP_UNKNOWN',
            'NODE_OBS_OP_ADD' => 'WEB_NODE_OBS_OP_ADD',
            'NODE_OBS_OP_REFRESH' => 'WEB_NODE_OBS_OP_REFRESH',
            'NODE_OBS_OP_DEL' => 'WEB_NODE_OBS_OP_DEL',
            'NODE_OBS_OP_MODIFY' => 'WEB_NODE_OBS_OP_MODIFY',
            'NODE_OBS_OP_QUERY_DIR_LIST' => 'WEB_NODE_OBS_OP_QUERY_DIR_LIST',
            'NODE_OBS_OP_QUERY_RECOVERY_DIR_LIST' => 'WEB_NODE_OBS_OP_QUERY_RECOVERY_DIR_LIST',

            // third monitor platform
            'NODE_SYS_OP_ADD_THIRD_MONITOR_PLATFROM' => 'WEB_THIRD_MONITOR_PLATFROM_ADD',
            'NODE_SYS_OP_MODIFY_THIRD_MONITOR_PLATFROM' => 'WEB_THIRD_MONITOR_PLATFROM_EDIT',
            'NODE_SYS_OP_DELETE_THIRD_MONITOR_PLATFROM' => 'WEB_THIRD_MONITOR_PLATFROM_DELETE',
            'NODE_SYS_OP_ADD_ALARM_PUSH_STRATEGY' => 'WEB_ALARM_PUSH_STRATEGY_ADD',
            'NODE_SYS_OP_MODIFY_ALARM_PUSH_STRATEGY' => 'WEB_ALARM_PUSH_STRATEGY_EDIT',
            'NODE_SYS_OP_DELETE_ALARM_PUSH_STRATEGY' => 'WEB_ALARM_PUSH_STRATEGY_DELETE',
            'NODE_SYS_OP_ALTER_ALARM_PUSH_STRATEGY_STATUS' => 'WEB_NODE_SYS_OP_ALTER_ALARM_PUSH_STRATEGY_STATUS',
            'NODE_SYS_OP_MANUAL_PUSH_ALARM' => 'WEB_MANUAL_PUSH_ALARM',
            'NODE_SYS_OP_MANUAL_PUSH_ALARM_RESPONSE' => 'WEB_MANUAL_PUSH_ALARM_RESPONSE',
            // 任务
            'NODE_TASK_OP_GET_TASK_HISTORY_IO_SUMMARY' => 'WEB_NODE_TASK_OP_GET_TASK_HISTORY_IO_SUMMARY',
        );
    }
}
