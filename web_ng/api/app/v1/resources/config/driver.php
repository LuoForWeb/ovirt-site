<?php

// 驱动库配置

return [
    // 适用操作系统
    'APPLICABLE_OS_TYPE' => [
        'WINDOWS' => 1,
        'LINUX' => 2,
    ],
    // 操作系统检查类别
    'DRIVER_TARGET_TYPE' => [
        'VM' => 1,   // 虚拟化
        'CLIENT' => 2,  // 客户端
    ],
    // 驱动是否异构平台
    'DRIVER_PLATFORM_TYPE' => [
        'UNKNOWN' => 0, // 未知
        'SAME' => 1,    // 同平台
        'DIFF' => 2,    // 异构平台
    ],
    // 检测结果
    'DRIVER_CHECK_RESULT' => [
        'PASS' => 1,  // 检测通过
        'NO_OS' => 2,  // 未检测到操作系统
        'NO_DRIVER' => 3,  // 驱动缺失
        'NO_TIMEPOINT' => 4, // 备份点不存在
        'NO_HW' => 5,  // 备份点缺少硬件信息
        'NO_CLIENT' => 6, // 未找到客户端
        'CLIENT_LACK_HW' => 7, // 未找到客户端的驱动信息
        'NONSUPPORT_OS_INSTALL_DRIVER' => 8, // 该操作系统版本不支持添加驱动
    ],
    // 操作系统类型转化
    'OS_TYPE_MAP' => [
        1 => 'Windows',
        2 => 'Linux',
    ],
    // Windows操作系统版本
    'WINDOWS_OS_VERSION' => [
        'Windows 11' => '10.0',
        'Windows Server 2022' => '10.0',
        'Windows Server 2019' => '10.0',
        'Windows Server 2016' => '10.0',
        'Windows 10' => '10.0',
        'Windows Server 2012 R2' => '6.3',
        'Windows 8.1' => '6.3',
        'Windows Server 2012' => '6.2',
        'Windows 8' => '6.2',
        'Windows Server 2008 R2' => '6.1',
        'Windows 7' => '6.1',
        'Windows Server 2008' => '6.0',
        'Windows Vista' => '6.0',
        'Windows Server 2003 R2' => '5.2',
        'Windows Server 2003' => '5.2',
        'Windows XP' => '5.1',
    ],
    // Windows操作系统架构
    'WINDOWS_OS_ARCH' => [
        'amd64' => 'amd64',
        'x86' => 'x86',
        'arm' => 'arm',
        'arm64' => 'arm64',
    ],
    // 检测用途
    'DRIVER_CHECK_USAGE' => [
        'BACKUP' => 1,    // 备份
        'RECOVERY' => 2,  // 恢复
        'TAKEOVER' => 3,  // 接管到内嵌虚拟化
    ],
];
