<?php

namespace app\v1\opcode;

use xphp\OpcodeHandler;

class OsOpcode extends OpcodeHandler
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

        $this->opcodeType = [
            'OS_OP_TYPE_UNKNOWN',       //unknown opcode type
            'OS_OP_TYPE_OS_PRIVATE_TASK',       //os private task, such as grain recovery
            'OS_OP_TYPE_OS_CLIENT',        //os client operation
            'OS_OP_TYPE_OS_MACHINE',   //os machine operation

        ];
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = [

            'OS_OP_TYPE_OS_PRIVATE_TASK' => [
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
                'OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION', //os migration start
                'OS_PRIVATE_TASK_OP_CODE_MODIFY_INSTANTANEOUS_RECOVERY', // 修改瞬时恢复任务
            ],

            'OS_OP_TYPE_OS_CLIENT' => [
                ' OS_CLIENT_OP_CODE_UNKNOWN'
            ],

            //opcode for backup point management
            'OS_OP_TYPE_OS_MACHINE' => [
                'OS_MACHINE_OP_CODE_UNKNOWN',
                'OS_MACHINE_OP_CODE_SCAN_DISK',  //get all disk and partition from machine
                'OS_MACHINE_OP_CODE_GET_CLIENT_INFO'
            ]

        ];
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {

        $this->opDes = [
            'OS_MACHINE_OP_CODE_UNKNOWN' => 'WEB_PLATFORM_PUBLIC_UNKNOWN',
            //get all disk and partition from machine
            'OS_MACHINE_OP_CODE_SCAN_DISK' => 'WEB_OS_MACHINE_OP_CODE_SCAN_DISK',
            'OS_MACHINE_OP_CODE_GET_CLIENT_INFO' => 'WEB_OS_MACHINE_OP_CODE_GET_CLIENT_INFO',


            'OS_PRIVATE_TASK_OP_CODE_UNKNOWN' => 'WEB_PLATFORM_PUBLIC_UNKNOWN',

            //--------------------------------grain recovery----------------------------------
            //os grain recovery create
            'OS_PRIVATE_TASK_OP_CODE_CREATE_GRAIN_RECOVERY' => 'WEB_OS_PRIVATE_TASK_OP_CODE_CREATE_GRAIN_RECOVERY',
            //os grain recovery start
            'OS_PRIVATE_TASK_OP_CODE_START_GRAIN_RECOVERY' => 'WEB_OS_PRIVATE_TASK_OP_CODE_START_GRAIN_RECOVERY',
            //os grain recovery stop
            'OS_PRIVATE_TASK_OP_CODE_STOP_GRAIN_RECOVERY' => 'WEB_OS_PRIVATE_TASK_OP_CODE_STOP_GRAIN_RECOVERY',
            //os grain recovery delete
            'OS_PRIVATE_TASK_OP_CODE_DELETE_GRAIN_RECOVERY' => 'WEB_OS_PRIVATE_TASK_OP_CODE_DELETE_GRAIN_RECOVERY',

            //---------------------------- grain recovery operation --------------------------
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR' => 'WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR',
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_READ_FILE_BLOCK' => 'WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_READ_FILE_BLOCK',
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_SEARCH_FILES' => 'WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_SEARCH_FILES',
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR_NAMES' =>
                'WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_LIST_CHILD_DIR_NAMES',
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_GET_ITEMS_STATS' => 'WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_GET_ITEMS_STATS',

            // download dir
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_START_DOWNLOAD_DIR' =>
                'WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_START_DOWNLOAD_DIR',
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_PROCESS_DOWNLOAD_DIR' =>
                'WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_PROCESS_DOWNLOAD_DIR',
            'OS_PRIVATE_TASK_OP_CODE_GRAIN_STOP_DOWNLOAD_DIR' => 'WEB_OS_PRIVATE_TASK_OP_CODE_GRAIN_STOP_DOWNLOAD_DIR',

            //---------------------------- delete os list operation ---------------------------
            'OS_PRIVATE_TASK_OP_CODE_DELETE_OS_LIST' => 'WEB_OS_PRIVATE_TASK_OP_CODE_DELETE_HOST',

             //---------------------------- instant recovery operation --------------------------
             'OS_PRIVATE_TASK_OP_CODE_CREATE_INSTANTANEOUS_RECOVERY' => 'WEB_OS_PRIVATE_TASK_OP_CODE_CREATE_INSTANTANEOUS_RECOVERY',
             'OS_PRIVATE_TASK_OP_CODE_START_INSTANTANEOUS_RECOVERY' => 'WEB_OS_PRIVATE_TASK_OP_CODE_START_INSTANTANEOUS_RECOVERY',
             'OS_PRIVATE_TASK_OP_CODE_STOP_INSTANTANEOUS_RECOVERY' => 'WEB_OS_PRIVATE_TASK_OP_CODE_STOP_INSTANTANEOUS_RECOVERY',
             'OS_PRIVATE_TASK_OP_CODE_STOP_MIGRATION' => 'WEB_OS_PRIVATE_TASK_OP_CODE_STOP_MIGRATION',
             'OS_PRIVATE_TASK_OP_CODE_DELETE_INSTANTANEOUS_RECOVERY' => 'WEB_OS_PRIVATE_TASK_OP_CODE_DELETE_INSTANTANEOUS_RECOVERY',
             'OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION' => 'WEB_OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION',

            ' OS_CLIENT_OP_CODE_UNKNOWN' => 'WEB_PLATFORM_PUBLIC_UNKNOWN',

        ];
    }
}
