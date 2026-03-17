<?php

namespace app\v1\opcode;

use xphp\OpcodeHandler;

class M365Opcode extends OpcodeHandler
{
    private static $instance = null;

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
     * 获取单例实例
     * @return VmOpcode|null
     */
    public static function instance(): ?VmOpcode
    {
        if (null == static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
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
            'M365_OP_TYPE_UNKNOWN',
            'M365_OP_TYPE_COMMON',        //Microsoft365组织相关的操作类型
            'M365_OP_TYPE_EXCH',          //Exchange Online&Exchnage Server操作类型
            'M365_OP_TYPE_SHAREPOINT',
            'M365_OP_TYPE_ONE_DRIVE',
            'M365_OP_TYPE_TEAMS',
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'M365_OP_UNKNOWN' => array(),

            //opcode
            'M365_OP_TYPE_COMMON' => array(
                'M365_OP_UNKNOWN',   //unknown opcode
                'M365_COMMON_OP_CODE_LOGIN_AZURE_AD',
                'M365_COMMON_OP_CODE_ADD_ORGANIZATION',
                'M365_ON_PREMISES_OP_CODE_ADD_ORGANIZATION',
                'M365_COMMON_OP_CODE_EDIT_ORGANIZATION',
                'M365_ON_PREMISES_COMMON_OP_CODE_EDIT_ORGANIZATION',
                'M365_COMMON_OP_CODE_DELETE_ORGANIZATION',
                'M365_COMMON_OP_CODE_REFRESH_ORGANIZATION',
                'M365_COMMON_OP_CODE_SYNC_META_FILE',
                'M365_COMMON_OP_CODE_READ_DATA_FROM_STORAGE',
            ),

            //opcode for backup point management
            'M365_OP_TYPE_EXCH' => array(
                'M365_EXCHANGE_OP_UNKNOWN',   //unknown opcode type
                'EXCH_BACKUP_POINT_OP_CODE_SCAN_USER',
                'EXCH_BACKUP_POINT_OP_CODE_SCAN_ROOT_FOLDER',
                'EXCH_BACKUP_POINT_OP_CODE_SCAN_CHILD_FOLDER',
                'EXCH_BACKUP_POINT_OP_CODE_SCAN_ITEM',
                'EXCH_BACKUP_POINT_OP_CODE_GET_ITEM_DETAIL',
                'EXCH_BACKUP_POINT_OP_CODE_SEARCH',
                'EXCH_BACKUP_POINT_OP_CODE_ADVANCED_SEARCH',
                'EXCH_BACKUP_POINT_OP_CODE_EXPORT_ITEM',
                'EXCH_BACKUP_POINT_OP_CODE_READ_EXPORT_ITEM_DATA',
                'EXCH_BACKUP_POINT_OP_CODE_SEND_EMAIL',

            ),
        );
    }

    /**
     * 初始化操作描述
     * @return void
     */
    private function initOpcodeDes()
    {
        $this->opDes = array(
            'M365_COMMON_OP_CODE_LOGIN_AZURE_AD' => 'WEB_M365_COMMON_OP_CODE_LOGIN_AZURE_AD',
            'M365_COMMON_OP_CODE_ADD_ORGANIZATION' => 'WEB_M365_COMMON_OP_CODE_ADD_ORGANIZATION',
            'M365_ON_PREMISES_OP_CODE_ADD_ORGANIZATION' => 'WEB_M365_ON_PREMISES_OP_CODE_ADD_ORGANIZATION',
            'M365_COMMON_OP_CODE_EDIT_ORGANIZATION' => 'WEB_M365_COMMON_OP_CODE_EDIT_ORGANIZATION',
            'M365_ON_PREMISES_COMMON_OP_CODE_EDIT_ORGANIZATION' => 'WEB_M365_ON_PREMISES_COMMON_OP_CODE_EDIT_ORGANIZATION',
            'M365_COMMON_OP_CODE_DELETE_ORGANIZATION' => 'WEB_M365_COMMON_OP_CODE_DELETE_ORGANIZATION',
            'M365_COMMON_OP_CODE_REFRESH_ORGANIZATION' => 'WEB_M365_COMMON_OP_CODE_REFRESH_ORGANIZATION',
            'M365_COMMON_OP_CODE_SYNC_META_FILE' => 'WEB_M365_COMMON_OP_CODE_SYNC_META_FILE',
            'M365_COMMON_OP_CODE_READ_DATA_FROM_STORAGE' => 'WEB_M365_COMMON_OP_CODE_READ_DATA_FROM_STORAGE',
            'EXCH_BACKUP_POINT_OP_CODE_SCAN_USER' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_SCAN_USER',
            'EXCH_BACKUP_POINT_OP_CODE_SCAN_ROOT_FOLDER' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_SCAN_ROOT_FOLDER',
            'EXCH_BACKUP_POINT_OP_CODE_SCAN_CHILD_FOLDER' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_SCAN_CHILD_FOLDER',
            'EXCH_BACKUP_POINT_OP_CODE_SCAN_ITEM' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_SCAN_ITEM',
            'EXCH_BACKUP_POINT_OP_CODE_GET_ITEM_DETAIL' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_GET_ITEM_DETAIL',
            'EXCH_BACKUP_POINT_OP_CODE_SEARCH' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_SEARCH',
            'EXCH_BACKUP_POINT_OP_CODE_ADVANCED_SEARCH' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_ADVANCED_SEARCH',
            'EXCH_BACKUP_POINT_OP_CODE_EXPORT_ITEM' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_EXPORT_ITEM',
            'EXCH_BACKUP_POINT_OP_CODE_READ_EXPORT_ITEM_DATA' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_READ_EXPORT_ITEM_DATA',
            'EXCH_BACKUP_POINT_OP_CODE_SEND_EMAIL' => 'WEB_EXCH_BACKUP_POINT_OP_CODE_SEND_EMAIL'

        );
    }
}
