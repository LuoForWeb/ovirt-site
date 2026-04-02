<?php

namespace app\v1\opcode;

use xphp\OpcodeHandler;

class PfOpcode extends OpcodeHandler
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
        $this->opcodeLevel = xphp_get_config('app')['OPCODE_LEVEL']['PUBLIC'];
    }

    /**
     * 初始化opcode type
     * @return void
     */
    private function initOpcodeType()
    {
        $this->opcodeType = array(
            'BD_OP_TYPE_UNKNOWN',       //unknown opcode type
            'BD_OP_TYPE_TASK',          //task operation
            'BD_OP_TYPE_BACKUP_POINT',  //backup point operation
            'BD_OP_TYPE_LOG',           //log operation
            'BD_OP_TYPE_SYSTEM',        //system operation
            'BD_OP_TYPE_ELITE_RCVY' => 8, // elite rcvy
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'BD_OP_TYPE_UNKNOWN' => array(),

            //opcode for task management
            'BD_OP_TYPE_TASK' => array(
                'BD_TASK_OP_UNKNOWN',               //unknown task opcode
                'BD_TASK_OP_BACKUP_CREATE',     //create backup task
                'BD_TASK_OP_RECOVERY_CREATE',   //create recovery task
                'BD_TASK_OP_BACKUP_MODIFY',     //modify backup task
                'BD_TASK_OP_RECOVERY_MODIFY',   //modify recovery task
                'BD_TASK_OP_BACKUP_START',      //start backup task
                'BD_TASK_OP_RECOVERY_START',    //start recovery task
                'BD_TASK_OP_BACKUP_PAUSE',      //pause backup task
                'BD_TASK_OP_RECOVERY_PAUSE',    //pause recovery task
                'BD_TASK_OP_BACKUP_CONTINUE',   //continue backup task
                'BD_TASK_OP_RECOVERY_CONTINUE', //continue recovery task
                'BD_TASK_OP_BACKUP_STOP',       //stop backup task
                'BD_TASK_OP_RECOVERY_STOP',     //stop recovery task
                'BD_TASK_OP_BACKUP_DELETE',     //delete backup task
                'BD_TASK_OP_RECOVERY_DELETE',   //delete recovery task
                'BD_TASK_OP_HISTORY_TASK_DELETE',       //delete histtory task
                'BD_TASK_OP_CREATE_DATA_CONNECTION',    //create data transport connection这是后台用的，不需要语言
                'BD_TASK_OP_CLOSE_DATA_CONNECTION',     //(not used) close data transport connection这是后台用的，不需要语言
                'BD_TASK_OP_START_TIMESTRATEGY',        //start time strategy
                'BD_TASK_OP_BACKUP_EXPORT_CREATE',  //create backup export task
                'BD_TASK_OP_BACKUP_EXPORT_DELETE',  //delete backup export task
                'BD_TASK_OP_BACKUP_EXPORT_MODIFY',  //modify backup export task
                'BD_TASK_OP_BACKUP_EXPORT_START',       //start backup export task
                'BD_TASK_OP_BACKUP_EXPORT_STOP',        //stop backup export task

                'BD_TASK_OP_CDP_BACKUP_CREATE',     //create cdp backup task
                'BD_TASK_OP_CDP_BACKUP_DELETE',     //delete cdp backup task
                'BD_TASK_OP_CDP_BACKUP_MODIFY',     //modify cdp backup task
                'BD_TASK_OP_CDP_BACKUP_START',      //start cdp backup task
                'BD_TASK_OP_CDP_BACKUP_STOP',           //stop cdp backup task

                'BD_TASK_OP_CDP_RECOVERY_CREATE',       //create cdp recovery task
                'BD_TASK_OP_CDP_RECOVERY_DELETE',       //delete cdp recovery task
                'BD_TASK_OP_CDP_RECOVERY_MODIFY',       //modify cdp recovery task
                'BD_TASK_OP_CDP_RECOVERY_START',        //start cdp recovery task
                'BD_TASK_OP_CDP_RECOVERY_STOP',         //stop cdp recovery task


                'BD_TASK_OP_BACKUP_COPY_CREATE',        //create backup copy task
                'BD_TASK_OP_BACKUP_COPY_DELETE',        //delete backup copy task
                'BD_TASK_OP_BACKUP_COPY_MODIFY',        //modify backup copy task
                'BD_TASK_OP_BACKUP_COPY_START',         //start backup copy task
                'BD_TASK_OP_BACKUP_COPY_STOP',          //stop backup copy task
                'BD_TASK_OP_BACKUP_COPY_PAUSE',         //pause backup copy task
                'BD_TASK_OP_BACKUP_COPY_CONTINUE',      //continue backup copy task



                'BD_TASK_OP_BACKUP_COPY_FETCH_CREATE',  //create backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE',  //delete backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_MODIFY',  //modify backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_START',   //start backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_STOP',    //stop backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_PAUSE',   //pause backup copy fetch task
                'BD_TASK_OP_BACKUP_COPY_FETCH_CONTINUE',//continue backup copy fetch task

                'BD_TASK_OP_ARCHIVE_CREATE',            //create archive task
                'BD_TASK_OP_ARCHIVE_DELETE',            //delete archive task
                'BD_TASK_OP_ARCHIVE_MODIFY',            //modify archive task
                'BD_TASK_OP_ARCHIVE_START',             //start archive task
                'BD_TASK_OP_ARCHIVE_STOP',              //stop archive task
                'BD_TASK_OP_ARCHIVE_PAUSE',             //pause archive task
                'BD_TASK_OP_ARCHIVE_CONTINUE',          //continue archive task
                'BD_TASK_OP_ARCHIVE_FETCH_CREATE',      //create archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_DELETE',      //delete archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_START',       //start archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_STOP',        //stop archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_PAUSE',       //pause archive fetch task
                'BD_TASK_OP_ARCHIVE_FETCH_CONTINUE',    //continue archive fetch task

                'BD_TASK_OP_TAKEOVER_CREATE',       //create cdp takeover task 61
                'BD_TASK_OP_TAKEOVER_DELETE',       //delete cdp takeover task
                'BD_TASK_OP_TAKEOVER_MODIFY',       //modify cdp takeover task
                'BD_TASK_OP_TAKEOVER_START',        //start cdp takeover task
                'BD_TASK_OP_TAKEOVER_STOP',         //stop cdp takeover task 65
                'BD_TASK_OP_TAKEOVER_PAUSE',
                'BD_TASK_OP_TAKEOVER_CONTINUE',

                'SS_OP_ADD_TYPE_TASK_ORCHESTRATION_PLAN', //add task orchestration plan
                'SS_OP_MOD_TYPE_TASK_ORCHESTRATION_PLAN',//modify task orchestration plan
                'SS_OP_DEL_TYPE_TASK_ORCHESTRATION_PLAN',  //delete task orchestration plan
                'SS_OP_START_TYPE_TASK_ORCHESTRATION_PLAN', //start task orchestration plan
                'SS_OP_STOP_TYPE_TASK_ORCHESTRATION_PLAN', //stop task orchestration plan
                'SS_OP_ENABLE_TASK_ORCHESTRATION_PLAN', //enable task orchestration plan
                'SS_OP_DISABLE_TASK_ORCHESTRATION_PLAN',  //disable task orchestration plan

                75 => 'BD_TASK_OP_GRAIN_RECOVERY_CREATE', // 创建细粒度恢复任务
                'BD_TASK_OP_GRAIN_RECOVERY_START', // 启动细粒度恢复任务
                'BD_TASK_OP_GRAIN_RECOVERY_STOP', // 停止细粒度恢复任务
                'BD_TASK_OP_GRAIN_RECOVERY_DELETE', // 删除细粒度恢复任务
            ),

            //opcode for backup point management
            'BD_OP_TYPE_BACKUP_POINT' => array(
                'BD_BACKUP_POINT_OP_UNKNOWN',   //unknown backup point opcode
                'BD_BACKUP_POINT_OP_DELETE',    //delete backup point
                'BD_BACKUP_POINT_OP_SCAN',      //scan backup point
                'BD_BACKUP_POINT_OP_MAKR',      //mark timepoint *
                'BD_BACKUP_POINT_OP_UNMARK',    //unmark timepoint
                'BD_BACKUP_POINT_OP_COMMENT',   //add comment to a timepoint
                'BD_BACKUP_POINT_OP_BATCH_DELETE', //delete backup point in batch
                'BD_BACKUP_COPY_POINT_OP_DELETE',   //delete backup copy point
                'BD_BACKUP_COPY_POINT_OP_BATCH_DELETE', //delete backup copy point in batch
                'BD_ARCHIVE_POINT_OP_DELETE',               //delete archive point
                'BD_ARCHIVE_POINT_OP_BATCH_DELETE',         //delete archive point in batch
                // mark/unmark timepoint with gfs flag, add by sky huang, 2021/11/25
                'BD_BACKUP_POINT_OP_GFS_FLAG_OP',

            ),

            //opcode for log management
            'BD_OP_TYPE_LOG' => array(
                'BD_LOG_OP_UNKNOWN',            //unknown log opcode
                'BD_LOG_OP_TASK_RECORD',        //record task log
                'BD_LOG_OP_SYSTEM_RECORD',      //record system log
                'BD_LOG_OP_TASK_DELETE',        //delete task log
                'BD_LOG_OP_SYSTEM_DELETE',      //delete system log
                'BD_LOG_OP_TASK_EXPORT',        //export task log
                'BD_LOG_OP_SYSTEM_EXPORT',      //export system log
                'BD_ALARM_OP_TASK_DELETE',      //delete task alarm
                'BD_ALARM_OP_SYSTEM_DELETE',    //delete system alarm
            ),

            'BD_OP_TYPE_SYSTEM' => array(
                'BD_SYSTEM_OP_UNKNOWN',         //unknown system opcode
                'BD_SYSTEM_OP_SYNC_TIME',       //sync system time
                'BD_SYSTEM_OP_READ_FILE',       //read file content
                'BD_SYSTEM_OP_DELETE_FILE',     //delete file
                'BD_SYSTEM_OP_GET_IP_LIST',     //get all ip list
                'BD_SYSTEM_OP_LIST_DIR',        //list dir
                'BD_SYSTEM_OP_REBOOT_SYSTEM',   //reboot system
                'BD_SYSTEM_OP_POWEROFF_SYSTEM', //poweroff system
                'BD_SYSTEM_OP_PUSH_MQ_MESSAGE', //push mq message
                'BD_SYSTEM_OP_MQ_CONNCT_TEST',  //test connection to mq
            ),

            // elite_rcvy_server
            'BD_OP_TYPE_ELITE_RCVY' => [
                'ELITE_RCVY_WEB_OP_UNKNOWN',
                'ELITE_RCVY_WEB_OP_CREATE_GRAIN_RECOVERY_TASK',
                'ELITE_RCVY_WEB_OP_MODIFY_GRAIN_RECOVERY_TASK',
                'ELITE_RCVY_WEB_OP_START_GRAIN_RECOVERY_TASK',
                'ELITE_RCVY_WEB_OP_STOP_GRAIN_RECOVERY_TASK',
                'ELITE_RCVY_WEB_OP_DELETE_GRAIN_RECOVERY_TASK',
                'ELITE_RCVY_WEB_OP_GET_GRAIN_DIR_FILES',
                'ELITE_RCVY_WEB_OP_DOWNLOAD_GRAIN_FILE',
                'ELITE_RCVY_WEB_OP_START_TRANSPORT_GRAIN_FILE',
                'ELITE_RCVY_WEB_OP_STOP_TRANSPORT_GRAIN_FILE',
                'ELITE_RCVY_WEB_OP_GREATE_NETWORK_FILE_SYSTEM_MOUNT_POINT',
                'ELITE_RCVY_WEB_OP_DESTROY_NETWORK_FILE_SYSTEM_MOUNT_POINT',
                'ELITE_RCVY_WEB_OP_SEARCH_GRAIN_FILE',
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
            'BD_TASK_OP_BACKUP_CREATE' => 'WEB_PT_OP_BACKUP_CREATE',
            'BD_TASK_OP_RECOVERY_CREATE' => 'WEB_PT_OP_RECOVERY_CREATE',
            'BD_TASK_OP_BACKUP_MODIFY' => 'WEB_PT_OP_BACKUP_MODIFY',
            'BD_TASK_OP_RECOVERY_MODIFY' => 'WEB_PT_OP_RECOVERY_MODIFY',
            'BD_TASK_OP_BACKUP_START' => 'WEB_PT_OP_BACKUP_START',
            'BD_TASK_OP_RECOVERY_START' => 'WEB_PT_OP_RECOVERY_START',
            'BD_TASK_OP_BACKUP_PAUSE' => 'WEB_PT_OP_BACKUP_PAUSE',
            'BD_TASK_OP_RECOVERY_PAUSE' => 'WEB_PT_OP_RECOVERY_PAUSE',
            'BD_TASK_OP_BACKUP_CONTINUE' => 'WEB_PT_OP_BACKUP_CONTINUE',
            'BD_TASK_OP_RECOVERY_CONTINUE' => 'WEB_PT_OP_RECOVERY_CONTINUE',
            'BD_TASK_OP_BACKUP_STOP' => 'WEB_PT_OP_BACKUP_STOP',
            'BD_TASK_OP_RECOVERY_STOP' => 'WEB_PT_OP_RECOVERY_STOP',
            'BD_TASK_OP_BACKUP_DELETE' => 'WEB_PT_OP_BACKUP_DELETE',
            'BD_TASK_OP_RECOVERY_DELETE' => 'WEB_PT_OP_RECOVERY_DELETE',
            'BD_TASK_OP_HISTORY_TASK_DELETE' => 'WEB_PT_OP_HISTORY_TASK_DELETE',
            'BD_TASK_OP_START_TIMESTRATEGY' => 'WEB_PT_OP_START_TIMESTRATEGY',
            'BD_TASK_OP_BACKUP_EXPORT_CREATE' => 'WEB_PT_OP_BACKUP_EXPORT_CREATE',
            'BD_TASK_OP_BACKUP_EXPORT_DELETE' => 'WEB_PT_OP_BACKUP_EXPORT_DELETE',
            'BD_TASK_OP_BACKUP_EXPORT_MODIFY' => 'WEB_PT_OP_BACKUP_EXPORT_MODIFY',
            'BD_TASK_OP_BACKUP_EXPORT_START' => 'WEB_PT_OP_BACKUP_EXPORT_START',
            'BD_TASK_OP_BACKUP_EXPORT_STOP' => 'WEB_PT_OP_BACKUP_EXPORT_STOP',
            'BD_TASK_OP_CDP_BACKUP_CREATE' => "create cdp backup task",
            'BD_TASK_OP_CDP_BACKUP_DELETE' => "delete cdp backup task",
            'BD_TASK_OP_CDP_BACKUP_MODIFY' => 'modify cdp backup task',
            'BD_TASK_OP_CDP_BACKUP_START' => 'start cdp backup task',
            'BD_TASK_OP_CDP_BACKUP_STOP' => 'stop cdp backup task',
            'BD_TASK_OP_CDP_RECOVERY_CREATE' => "create cdp recovery task",
            'BD_TASK_OP_CDP_RECOVERY_DELETE' => "delete cdp recovery task",
            'BD_TASK_OP_CDP_RECOVERY_MODIFY' => 'modify cdp recovery task',
            'BD_TASK_OP_CDP_RECOVERY_START' => 'start cdp recovery task',
            'BD_TASK_OP_CDP_RECOVERY_STOP' => 'stop cdp recovery task',


            'BD_TASK_OP_BACKUP_COPY_CREATE' => 'WEB_PT_OP_COPY_CREATE',
            'BD_TASK_OP_BACKUP_COPY_DELETE' => 'WEB_PT_OP_COPY_DELETE',
            'BD_TASK_OP_BACKUP_COPY_MODIFY' => 'WEB_PT_OP_COPY_MODIFY',
            'BD_TASK_OP_BACKUP_COPY_START' => 'WEB_PT_OP_COPY_START',
            'BD_TASK_OP_BACKUP_COPY_STOP' => 'WEB_PT_OP_COPY_STOP',
            'BD_TASK_OP_BACKUP_COPY_PAUSE' => 'WEB_PT_OP_COPY_PAUSE',
            'BD_TASK_OP_BACKUP_COPY_CONTINUE' => 'WEB_PT_OP_COPY_CONTINUE',

            'BD_TASK_OP_BACKUP_COPY_FETCH_CREATE' => 'WEB_PT_OP_COPY_FETCH_CREATE',
            'BD_TASK_OP_BACKUP_COPY_FETCH_DELETE' => 'WEB_PT_OP_COPY_FETCH_DELETE',
            'BD_TASK_OP_BACKUP_COPY_FETCH_MODIFY' => 'WEB_PT_OP_COPY_FETCH_MODIFY',
            'BD_TASK_OP_BACKUP_COPY_FETCH_START' => 'WEB_PT_OP_COPY_FETCH_START',
            'BD_TASK_OP_BACKUP_COPY_FETCH_STOP' => 'WEB_PT_OP_COPY_FETCH_STOP',
            'BD_TASK_OP_BACKUP_COPY_FETCH_PAUSE' => 'WEB_PT_OP_COPY_FETCH_PAUSE',
            'BD_TASK_OP_BACKUP_COPY_FETCH_CONTINUE' => 'WEB_PT_OP_COPY_FETCH_CONTINUE',

            'BD_TASK_OP_ARCHIVE_CREATE' => 'WEB_PT_OP_ARCHIVE_CREATE',
            'BD_TASK_OP_ARCHIVE_DELETE' => 'WEB_PT_OP_ARCHIVE_DELETE',
            'BD_TASK_OP_ARCHIVE_MODIFY' => 'WEB_PT_OP_ARCHIVE_MODIFY',
            'BD_TASK_OP_ARCHIVE_START' => 'WEB_PT_OP_ARCHIVE_START',
            'BD_TASK_OP_ARCHIVE_STOP' => 'WEB_PT_OP_ARCHIVE_STOP',
            'BD_TASK_OP_ARCHIVE_PAUSE' => 'WEB_PT_OP_ARCHIVE_PAUSE',
            'BD_TASK_OP_ARCHIVE_CONTINUE' => 'WEB_PT_OP_ARCHIVE_CONTINUE',
            'BD_TASK_OP_ARCHIVE_FETCH_CREATE' => 'WEB_PT_OP_ARCHIVE_FETCH_CREATE',
            'BD_TASK_OP_ARCHIVE_FETCH_DELETE' => 'WEB_PT_OP_ARCHIVE_FETCH_DELETE',
            'BD_TASK_OP_ARCHIVE_FETCH_START' => 'WEB_PT_OP_ARCHIVE_FETCH_START',
            'BD_TASK_OP_ARCHIVE_FETCH_STOP' => 'WEB_PT_OP_ARCHIVE_FETCH_STOP',
            'BD_TASK_OP_ARCHIVE_FETCH_PAUSE' => 'WEB_PT_OP_ARCHIVE_FETCH_PAUSE',
            'BD_TASK_OP_ARCHIVE_FETCH_CONTINUE' => 'WEB_PT_OP_ARCHIVE_FETCH_CONTINUE',


            'BD_BACKUP_POINT_OP_DELETE' => 'WEB_PT_POINT_OP_DELETE',
            'BD_BACKUP_POINT_OP_SCAN' => 'WEB_PT_POINT_OP_SCAN',
            'BD_BACKUP_POINT_OP_MAKR' => 'WEB_PT_POINT_OP_MAKR',
            'BD_BACKUP_POINT_OP_UNMARK' => 'WEB_PT_POINT_OP_UNMARK',
            'BD_BACKUP_POINT_OP_COMMENT' => 'WEB_PT_POINT_OP_COMMENT',
            'BD_BACKUP_POINT_OP_BATCH_DELETE' => 'WEB_PT_POINT_OP_BATCH_DELETE',
            'BD_BACKUP_COPY_POINT_OP_DELETE' => 'WEB_PT_COPY_POINT_OP_DELETE',
            'BD_BACKUP_COPY_POINT_OP_BATCH_DELETE' => 'WEB_PT_COPY_POINT_OP_BATCH_DELETE',
            'BD_ARCHIVE_POINT_OP_DELETE' => 'WEB_PT_ARCHIVE_POINT_OP_DELETE',
            'BD_ARCHIVE_POINT_OP_BATCH_DELETE' => 'WEB_PT_ARCHIVE_POINT_OP_BATCH_DELETE',
            'BD_LOG_OP_TASK_RECORD' => 'WEB_PT_OP_TASK_RECORD',
            'BD_LOG_OP_SYSTEM_RECORD' => 'WEB_PT_OP_SYSTEM_RECORD',
            'BD_LOG_OP_TASK_DELETE' => 'WEB_PT_OP_TASK_DELETE',
            'BD_LOG_OP_SYSTEM_DELETE' => 'WEB_PT_OP_SYSTEM_DELETE',
            'BD_LOG_OP_TASK_EXPORT' => 'WEB_PT_OP_TASK_EXPORT',
            'BD_LOG_OP_SYSTEM_EXPORT' => 'WEB_PT_OP_SYSTEM_EXPORT',
            'BD_ALARM_OP_TASK_DELETE' => 'WEB_PT_OP_DELETE_TASK_ALARM',
            'BD_ALARM_OP_SYSTEM_DELETE' => 'WEB_PT_OP_DELETE_SYSTEM_ALARM',
            'BD_SYSTEM_OP_SYNC_TIME' => 'WEB_PT_OP_SYNC_NODE_TIME',
            'BD_SYSTEM_OP_READ_FILE' => 'WEB_PT_OP_READ_NODE_FILE',
            'BD_SYSTEM_OP_DELETE_FILE' => 'WEB_PT_OP_DELETE_NODE_FILE',
            'BD_SYSTEM_OP_GET_IP_LIST' => 'WEB_PT_OP_GET_IP_LIST',
            'BD_SYSTEM_OP_LIST_DIR' => 'WEB_PT_OP_READ_DIR',
            'BD_SYSTEM_OP_REBOOT_SYSTEM' => 'WEB_PT_OP_RESTART_NODE',
            'BD_SYSTEM_OP_POWEROFF_SYSTEM' => 'WEB_PT_OP_POWEROFF_NODE',
            'BD_SYSTEM_OP_PUSH_MQ_MESSAGE' => 'WEB_PT_OP_PUSH_MQ_MESSAGE',
            'BD_SYSTEM_OP_MQ_CONNCT_TEST' => 'WEB_PT_OP_MQ_CONNCT_TEST',
            'BD_BACKUP_POINT_OP_GFS_FLAG_OP' => 'WEB_PT_OP_GFS_FLAG',


            'BD_TASK_OP_TAKEOVER_CREATE' => 'WEB_BD_TASK_OP_TAKEOVER_CREATE',
            //delete cdp takeover task
            'BD_TASK_OP_TAKEOVER_DELETE' => 'WEB_BD_TASK_OP_TAKEOVER_DELETE',
            //modify cdp takeover task
            'BD_TASK_OP_TAKEOVER_MODIFY' => 'WEB_BD_TASK_OP_TAKEOVER_MODIFY',
            //start cdp takeover task
            'BD_TASK_OP_TAKEOVER_START' => 'WEB_BD_TASK_OP_TAKEOVER_START',
            'BD_TASK_OP_TAKEOVER_STOP' => 'WEB_BD_TASK_OP_TAKEOVER_STOP',        //stop cdp takeover task
            'BD_TASK_OP_TAKEOVER_PAUSE' => 'WEB_BD_TASK_OP_TAKEOVER_PAUSE',
            'BD_TASK_OP_TAKEOVER_CONTINUE' => 'WEB_BD_TASK_OP_TAKEOVER_CONTINUE',

            'SS_OP_ADD_TYPE_TASK_ORCHESTRATION_PLAN' => 'WEB_SS_OP_ADD_TYPE_TASK_ORCHESTRATION_PLAN',
            'SS_OP_MOD_TYPE_TASK_ORCHESTRATION_PLAN' => 'WEB_SS_OP_MOD_TYPE_TASK_ORCHESTRATION_PLAN',
            'SS_OP_DEL_TYPE_TASK_ORCHESTRATION_PLAN' => 'WEB_SS_OP_DEL_TYPE_TASK_ORCHESTRATION_PLAN',
            'SS_OP_START_TYPE_TASK_ORCHESTRATION_PLAN' => 'WEB_SS_OP_START_TYPE_TASK_ORCHESTRATION_PLAN',
            'SS_OP_STOP_TYPE_TASK_ORCHESTRATION_PLAN' => 'WEB_SS_OP_STOP_TYPE_TASK_ORCHESTRATION_PLAN',
            'SS_OP_ENABLE_TASK_ORCHESTRATION_PLAN' => 'WEB_SS_OP_ENABLE_TASK_ORCHESTRATION_PLAN',
            'SS_OP_DISABLE_TASK_ORCHESTRATION_PLAN' => 'WEB_SS_OP_DISABLE_TASK_ORCHESTRATION_PLAN',

            'VOL_CDP_TASK_SCANNING_CLIENT_INFO' => 'WEB_VOL_CDP_OPCODE_SCAN_HOST_INFO',
            'VOL_CDP_TASK_DELETE_CONSISTENCY_LABEL' => 'WEB_VOL_CDP_TASK_DELETE_CONSISTENCY_LABEL',

            'ELITE_RCVY_WEB_OP_CREATE_GRAIN_RECOVERY_TASK' => 'WEB_ELITE_RCVY_WEB_OP_CREATE_GRAIN_RECOVERY_TASK',
            'ELITE_RCVY_WEB_OP_MODIFY_GRAIN_RECOVERY_TASK' => 'WEB_ELITE_RCVY_WEB_OP_MODIFY_GRAIN_RECOVERY_TASK',
            'ELITE_RCVY_WEB_OP_START_GRAIN_RECOVERY_TASK' => 'WEB_ELITE_RCVY_WEB_OP_START_GRAIN_RECOVERY_TASK',
            'ELITE_RCVY_WEB_OP_STOP_GRAIN_RECOVERY_TASK' => 'WEB_ELITE_RCVY_WEB_OP_STOP_GRAIN_RECOVERY_TASK',
            'ELITE_RCVY_WEB_OP_DELETE_GRAIN_RECOVERY_TASK' => 'WEB_ELITE_RCVY_WEB_OP_DELETE_GRAIN_RECOVERY_TASK',
            'ELITE_RCVY_WEB_OP_GET_GRAIN_DIR_FILES' => 'WEB_ELITE_RCVY_WEB_OP_GET_GRAIN_DIR_FILES',
            'ELITE_RCVY_WEB_OP_DOWNLOAD_GRAIN_FILE' => 'WEB_ELITE_RCVY_WEB_OP_DOWNLOAD_GRAIN_FILE',
            'ELITE_RCVY_WEB_OP_START_TRANSPORT_GRAIN_FILE' => 'WEB_ELITE_RCVY_WEB_OP_START_TRANSPORT_GRAIN_FILE',
            'ELITE_RCVY_WEB_OP_STOP_TRANSPORT_GRAIN_FILE' => 'WEB_ELITE_RCVY_WEB_OP_STOP_TRANSPORT_GRAIN_FILE',
            'ELITE_RCVY_WEB_OP_GREATE_NETWORK_FILE_SYSTEM_MOUNT_POINT' =>
                'WEB_ELITE_RCVY_WEB_OP_GREATE_NETWORK_FILE_SYSTEM_MOUNT_POINT',
            'ELITE_RCVY_WEB_OP_DESTROY_NETWORK_FILE_SYSTEM_MOUNT_POINT' =>
                'WEB_ELITE_RCVY_WEB_OP_DESTROY_NETWORK_FILE_SYSTEM_MOUNT_POINT',
            'ELITE_RCVY_WEB_OP_SEARCH_GRAIN_FILE' => 'WEB_ELITE_RCVY_WEB_OP_SEARCH_GRAIN_FILE',
        );
    }
}
