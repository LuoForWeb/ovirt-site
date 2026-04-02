<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2024-08-22 10:16:44
 * @LastEditTime: 2025-05-15 11:47:55
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

return [
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
];