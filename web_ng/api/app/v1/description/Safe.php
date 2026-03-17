<?php
return [
    'STRAREGY_TYPE' => [ //政策类型
        0 => xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        1 => xphp_get_lang('UI_VERIFY_STRATEGY'),
        2 => xphp_get_lang('UI_SAFE_NO_SCAN_STRATEGY'),
        3 => xphp_get_lang('UI_SAFE_HEALTH_STRATEGY'),
        4 => xphp_get_lang('UI_SAFE_REFECT_STRATEGY'),
    ],
    "RECOVER_POLICY" => [ //恢复策略
        0 => xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        1 => xphp_get_lang('UI_PLATFORM_DIRECT_RECOVERY'),
        2 => xphp_get_lang('UI_PLATFORM_AFTER_KILLING_RECOVERY'),
        3 => xphp_get_lang('UI_PLATFORM_AGAIN_SCAN'),
        4 => xphp_get_lang('UI_SAFE_DECTION_TAKEOVER'),
    ],
    "INTERRUPT_POLICY" => [ //扫描中断策略
        0 => xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        1 => xphp_get_lang('UI_SAFE_VIRUS_STOP'),
        2 => xphp_get_lang('UI_SAFE_VIRUS_COVER_NO_INTER'),
        3 => xphp_get_lang('UI_PLATFORM_AFTER_KILLING_RECOVERY'),
        4 => xphp_get_lang('UI_SAFE_VIRUS_TAKEOVER_NO_INTER'),
        5 => xphp_get_lang('UI_SAFE_VIRUS_TAKEOVER'),
    ],
    "SCAN_STRATEGY" => [ //扫描策略
        0 => xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        1 => xphp_get_lang('UI_SAFE_VIRUS_ONLY_SCAN'),
        2 => xphp_get_lang('UI_SAFE_VIRUS_AND_DELETE'),
        3 => xphp_get_lang('UI_SAFE_SCAN_VIRUS_FAIL_DELETE'),
        4 => xphp_get_lang('UI_SAFE_SCAN_VIRUS_FAIL_SKIP'),
    ],
    "CHECK_STRATEGY" => [ //完整性检查策略
        0 => xphp_get_lang('WEB_SETTINGS_UPDATE_CHECK_PERIOD_WEEK'),
        1 => xphp_get_lang('WEB_SETTINGS_UPDATE_CHECK_PERIOD_DAY'),
        2 => xphp_get_lang('UI_BACKUP_EVERY_TIME'),
    ],
    "FULL_ERROR_STRATEGY" => [ // 备份完备点异常策略
        0 => xphp_get_lang('UI_BACKUP_POINT_ABNORMAL_HANDLE_TYPE2'),
        1 => xphp_get_lang('UI_BACKUP_POINT_ABNORMAL_HANDLE_TYPE1'),
    ],
    "INC_ERROR_STRATEGY" => [ // 备份完备点异常策略
        0 => xphp_get_lang('UI_BACKUP_OTHER_POINT_ABNORMAL_HANDLE_TYPE0'),
        1 => xphp_get_lang('UI_BACKUP_OTHER_POINT_ABNORMAL_HANDLE_TYPE1'),
        2 => xphp_get_lang('UI_BACKUP_OTHER_POINT_ABNORMAL_HANDLE_TYPE2'),
    ],
    "RECOVERY_ERROR_STRATEGY" => [ // 恢复异常策略S
        0 => xphp_get_lang('UI_PLATFORM_INTERRUPT_RECOVERY'),
        1 => xphp_get_lang('UI_PLATFORM_CONTINUE_RECOVERY'),
        2 => xphp_get_lang('UI_PLATFORM_NONET_RECOVERY'),
    ],
    'SCAN_ENTIRE_SYSTEM_FLAG' => [  // 扫描对象
        1 => xphp_get_lang('UI_SAFE_SCAN_ENTIRE_FULL_DISK'),
        2 => xphp_get_lang('UI_SAFE_SCAN_ENTIRE_SPECIFY_PATH'),
    ],
];