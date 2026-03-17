<?php
/*
 * @note: 备份数据管理操作码管理
 * @author: chenyunfeng@vinchin.com
 * @Description: 备份数据管理操作码管理
 * @Date: 2024-10-30 17:29:46
 * @LastEditTime: 2025-08-29 14:26:13
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

namespace app\v2\opcode;

use xphp\OpcodeHandler;

class BackupDataOpcode extends OpcodeHandler
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
            'TIMEPOINT_WEB_OP_UNKNOWN',
            '',
            'BD_OP_TYPE_BACKUP_POINT',
        );
    }

    /**
     * 初始化opcode
     * @return void
     */
    private function initOpcode()
    {
        $this->opcode = array(
            'TIMEPOINT_WEB_OP_UNKNOWN' => array(),
            array(),
            'BD_OP_TYPE_BACKUP_POINT' => array(
                'TIMEPOINT_WEB_OP_UNKNOWN',
                'TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT', // 删除单个时间点
                'TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT_IN_BATCH', // 批量删除时间点
                'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_REMARKS',  // 添加备注
                'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_IMPORTANCE_FLAG', // 设置永久标记
                'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_GFS_FLAG', // 设置GFS标记
                'TIMEPOINT_WEB_OP_SET_WORM_OF_BACKUP_POINT', // 设置worm保护期限
                'TIMEPOINT_WEB_OP_EXTEND_WORM_FOR_BACKUP_CHAIN', // 延长worm保护期限
                'TIMEPOINT_WEB_OP_CONVERT_TIMEPOINT_CONFIG_TO_MIDDLE_CONFIG', // 转换时间点配置
                'TIMEPOINT_WEB_OP_FORCE_DELETE_BACKUP_POINT', // 删除异常时间点
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
            'TIMEPOINT_WEB_OP_UNKNOWN'=>'WEB_TIMEPOINT_WEB_OP_UNKNOWN',
            'TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT' => 'WEB_TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT',
            'TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT_IN_BATCH' => 'WEB_TIMEPOINT_WEB_OP_DELETE_BACKUP_POINT_IN_BATCH',
            'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_REMARKS' => 'WEB_TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_REMARKS',
            'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_IMPORTANCE_FLAG' => 'WEB_TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_IMPORTANCE_FLAG',
            'TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_GFS_FLAG' => 'WEB_TIMEPOINT_WEB_OP_UPDATE_BACKUP_POINT_GFS_FLAG',
            'TIMEPOINT_WEB_OP_SET_WORM_OF_BACKUP_POINT' => 'WEB_TIMEPOINT_WEB_OP_SET_WORM_OF_BACKUP_POINT',
            'TIMEPOINT_WEB_OP_EXTEND_WORM_FOR_BACKUP_CHAIN' => 'WEB_TIMEPOINT_WEB_OP_EXTEND_WORM_FOR_BACKUP_CHAIN',
            'TIMEPOINT_WEB_OP_CONVERT_TIMEPOINT_CONFIG_TO_MIDDLE_CONFIG' => 'WEB_TIMEPOINT_WEB_OP_CONVERT_TIMEPOINT_CONFIG_TO_MIDDLE_CONFIG',
            'TIMEPOINT_WEB_OP_FORCE_DELETE_BACKUP_POINT' => 'WEB_TIMEPOINT_WEB_OP_FORCE_DELETE_BACKUP_POINT',
        );
    }
}
