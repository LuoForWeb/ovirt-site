<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 备份数据时间点状态
 * @Date: 2024-08-22 09:59:22
 * @LastEditTime: 2025-08-25 15:45:46
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

return [
    // 操作状态
    'OPERATION_STATUS' => [
        'NO_OPERATION' => 0,  // 未操作
        'MERGING' => 1,  // 合并中
        'DELETING' => 2,  // 删除中
        'SCANNING' => 4,  // 扫描中
        'VERIFY' => 8,  // 校验中
    ],
    'VIRUS_STATUS' => [
        'NO_SCAN' => 0, // 未扫描
        'SCANNING' => 1, // 扫描中
        'SAFE' => 2, // 健康
        'INFECTED' => 3, // 感染
        'INFECTED_PARTLY_SCAN' => 4, // 已经感染但未扫描完成，不能进行杀毒后恢复
    ],
    'INTEGRITY_STATUS' => [
        'NORMAL' => 0, // 正常
        'BROKEN' => 1, // 损坏
        'NO_CHECK' => 2, // 未检查'
    ],
    'MERGE_STATUS' => [
        'NORMAL' => 0, // 正常
        'WAITING' => 1, // 待合并
        'MERGING' => 2, // 合并中
        'FAILED' => 3, // 失败
        'DEPEND_MERGE_FAILED' => 4, // 依赖点合并失败
        'ROLL_BACK' => 5, // 回滚中
    ],
    'CHAIN_STATUS' => [
        'UNKNOWN' => 0, // 未知
        'NORMAL' => 1, // 正常
        'MISS_DEPEND' => 2, // 缺失依赖点
        'DISORDER' => 3, // 乱序
    ],
    'OPERATE_DES' => [
		0 => xphp_get_lang('UI_BACKUP_DATA_STATUS_NO_OPERATION'),
		1 => xphp_get_lang('UI_BACKUP_DATA_STATUS_MERGING'),
		2 => xphp_get_lang('UI_BACKUP_DATA_STATUS_DELETING'),
		4 => xphp_get_lang('UI_BACKUP_DATA_STATUS_SCANNING'),
		8 => xphp_get_lang('UI_BACKUP_DATA_STATUS_CHECKING'),
	],
	"VIRUS_STATUS_DES" => [
		xphp_get_lang('UI_BACKUP_DATA_STATUS_VIRUS_NOT_SCAN'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_SCANNING'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_VIRUS_NORMAL'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_VIRUS_ABNORMAL'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_VIRUS_ABNORMAL'),
	],
	"INTEGRITY_STATUS_DES" => [
		xphp_get_lang('UI_BACKUP_DATA_STATUS_INTEGRITY_NORMAL'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_INTEGRITY_BROKEN'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_INTEGRITY_NOT_CHECK'),
	],
	"MERGE_STATUS_DES" => [
		xphp_get_lang('UI_BACKUP_DATA_STATUS_INTEGRITY_NORMAL'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_WAIRRING_MERGING'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_MERGING'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_MERGE_FAIL'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_DEPEND_POINT_MERGE_FAIL'),
		xphp_get_lang('UI_BACKUP_DATA_STATUS_ROLL_BACKUP'),
	],
    'CHAIN_STATUS_DES' => [
        xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'), // 未知
        xphp_get_lang('WEB_PLATFORM_DES_NORMAL'), // 正常
        xphp_get_lang('WEB_TIMEPOINT_WEB_MISS_DEPEND_TIMEPOINT'), // 缺失依赖点
        xphp_get_lang('WEB_TIMEPOINT_WEB_DISORDER_TIMEPOINT'), // 乱序
    ],
];
