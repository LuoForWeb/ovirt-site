<?php

namespace app\v2\opcode;

use xphp\OpcodeHandler;

class DbProtectOpcode extends OpcodeHandler
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
     * @return class
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
            'DB_OP_TYPE_UNKNOWN',                       // database backup operation type unknown
            'DB_OP_TYPE_CLIENT',                            // database backup client operation type
            'DB_OP_TYPE_INSTANCE',                      // database backup instance operation type
            'DB_OP_TYPE_DB',                                // database backup db operation
            'DB_OP_TYPE_CLIENT_SELECT',                 // database backup client select operation type
            'DB_OP_TYPE_RECOVERY_TIMEPOINT',               // database recovery timepoint operation type
        ];
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = [
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
                'DB_INSTANCE_OP_DELETE_INSTANCE',
                'DB_INSTANCE_OP_GET_TABLE_SPACE_DATA_FILE',  // 获取表空间数据文件
                'DB_INSTANCE_OP_PARSE_AND_CONVERT_SPFILE',   // 解析并转换spfile
            ),
            'DB_OP_TYPE_DB' => array(

            ),
            'DB_OP_TYPE_CLIENT_SELECT' => array(
            ),
            'DB_OP_TYPE_RECOVERY_TIMEPOINT' => array(
                'DB_RECOVERY_TIMEPOINT_OP_UNKNOWN',
                'DB_RECOVERY_TIMEPOINT_OP_GET_RECOVERY_TIMEPOINT_CHAIN_BY_TIME',        // get recovery timepoint chain
                'DB_RECOVERY_TIMEPOINT_OP_GET_FULL_RECOVERY_TIMEPOINT_CHAIN',         // get full recovery timepoint chain
            ),
        ];
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = [
            'DB_CLIENT_OP_ADD' => 'WEB_DB_ADD_CLIENT',
            'DB_CLIENT_OP_DELETE' => 'WEB_DB_DELETE_CLIENT',
            'DB_CLIENT_OP_GET_INSTANCE_LIST' => 'WEB_DB_GET_INSTANCE_LIST',
            'DB_CLIENT_OP_MODIFY' => 'UI_DB_MODIFY_DB_AGENT',

            'DB_INSTANCE_OP_VERIFY_AUTH' => 'WEB_DB_INSTANCE_VERIFY_AUTH',
            'DB_INSTANCE_OP_SCAN_DB' => 'WEB_DB_INSTANCE_SCAN_DB',
            'DB_INSTANCE_OP_SCAN_TABLE_SPACE' => 'WEB_DB_INSTANCE_OP_SCAN_TABLE_SPACE',
            'DB_INSTANCE_OP_DELETE_INSTANCE' => 'WEB_DB_INSTANCE_OP_DELETE_INSTANCE',
            'DB_INSTANCE_OP_GET_TABLE_SPACE_DATA_FILE' => 'WEB_DB_INSTANCE_OP_GET_TABLE_SPACE_DATA_FILE',
            'DB_INSTANCE_OP_PARSE_AND_CONVERT_SPFILE' => 'WEB_DB_INSTANCE_OP_PARSE_AND_CONVERT_SPFILE',

            'DB_RECOVERY_TIMEPOINT_OP_GET_RECOVERY_TIMEPOINT_CHAIN_BY_TIME' => 'WEB_DB_RECOVERY_TIMEPOINT_OP_GET_RECOVERY_TIMEPOINT_CHAIN_BY_TIME',
            'DB_RECOVERY_TIMEPOINT_OP_GET_FULL_RECOVERY_TIMEPOINT_CHAIN' => 'WEB_DB_RECOVERY_TIMEPOINT_OP_GET_FULL_RECOVERY_TIMEPOINT_CHAIN',
        ];
    }
}
