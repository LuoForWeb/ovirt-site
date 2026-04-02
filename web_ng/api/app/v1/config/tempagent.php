<?php

/***
* 容灾主机相关的一些配置定义
 */

return [

    # 日志管理
    // 操作来源
    'EmdOperationUser' => [
        0 => 'WEB_VM_SOURCE_BY_USER', // 用户手动操作
        1 => 'WEB_VM_SOURCE_BY_TASK', // 来自于任务
    ],
    // 操作名
    'EmdOperation' => [
        0 => 'WEB_OPERATION_CREATE_VM', // 创建容灾演练主机
        1 => 'WEB_OPERATION_DELETE_VM', // 删除容灾演练主机
        2 => 'WEB_OPERATION_RECONFIG_VM', // 重新配置容灾演练主机
        3 => 'WEB_OPERATION_START_VM', // 启动容灾演练主机
        4 => 'WEB_OPERATION_GET_VM_STATUS', // 获取容灾演练主机状态
        5 => 'WEB_OPERATION_GET_VM_CONFIG', // 获取容灾演练主机配置
        6 => 'WEB_OPERATION_ADD_DEVICE_FOR_VM', // 为容灾演练主机添加设备
        7 => 'WEB_OPERATION_DEL_DEVICE_FOR_VM', // 删除容灾演练主机设备
        8 => 'WEB_OPERATION_TAKE_SCREEN_SHOT_FOR_VM', // 创建屏幕快照
        9 => 'WEB_OPERATION_CREATE_NETWORK', // 创建网络
        10 => 'WEB_OPERATION_DELETE_NETWORK', // 删除网络
        11 => 'WEB_OPERATION_ACTIVE_NETWORK', // 启用网络
        12 => 'WEB_OPERATION_SHUTDOWN_NETWORK', // 关闭网络
        13 => 'WEB_OPERATION_STOP_VM', // 关闭容灾演练主机
    ],

    # 虚拟机管理
    // 状态
    'STATUS' => [
        'unknown' => 0,             // 未知
        'on' => 1,                  // 运行中
        'off' => 2,                 // 关闭
        'stop' => 3,               // 暂停
    ],
    // 状态对应的语言包
    'STATUS_MSG' => [
        0 => 'WEB_PLATFORM_PUBLIC_UNKNOWN',
        1 => 'WEB_VM_POWER_ON',
        3 => 'WEB_VM_PAUSE',
        5 => 'WEB_VM_POWER_OFF',
    ],
    // 内嵌机器对应的状态
    'PREFIX_STATUS' => [
        'STATUS_NOSTATE' => 0,
        'STATUS_DOING' => 1,
        'STATUS_ERROR' => 2,
        'STATUS_VIR_DIT' => 3,
        'STATUS_DEIVER_REPLACE' => 4,
        'STATUS_NETCARD_FIX' => 5,
        'STATUS_FSTABLE_FIX' => 6,
        'STATUS_GRAB_FIX' => 7,
        'STATUS_SUCCESS' => 8,
    ],
    // 代理类型
    'AGENT_ROLE' => [
        'WEB_PLATFORM_PUBLIC_UNKNOWN',
        99 => 'UI_VOL_CDP_STANDBY_USE_VERIFY', // 验证
        100 => 'UI_VM_MACHINE_AGENT_ROLE1', // 接管
        'UI_VM_MACHINE_AGENT_ROLE2', // 虚拟实验室
        'UI_VM_MACHINE_AGENT_ROLE3', // 手动验证
        'UI_VM_MACHINE_AGENT_ROLE4', // 自动验证
        'UI_VM_MACHINE_AGENT_ROLE5',// 高级恢复
        'UI_VM_MACHINE_AGENT_ROLE6',// 高级恢复-后台未定义
    ],
    // 网络管理标记
    'NETWORK_TAGS' => [
        0 => 'WEB_PLATFORM_PUBLIC_UNKNOWN',
        1 => 'UI_PLATFORM_SYSTEM',
        2 => 'UI_PUBLIC_USER'
    ],
    // 磁盘类型
    'DISK_TYPE' => [
        'unknown',
        'raw',
        'qcow',
        'vmdk'
    ],
    // 总线接入类型
    'DISK_TARGET_BUS' => [
        0 => 'WEB_PLATFORM_PUBLIC_UNKNOWN',
        1 => 'IDE',
        2 => 'VIRTIO',
        3 => 'SATA',
        4 => 'SCSI',
    ],
    // ip类型
    'IP_TYPE' => [
        'NET_IP_TYPE_UNKNOWN' => 0,
        'NET_IP_TYPE_V4' => 1,
        'NET_IP_TYPE_V6' => 2,
    ],
    // 网络接口枚举
    'NET_MODEL_TYPE' => [
        1 => 'virtio',
        2 => 'e1000',
        3 => 'rtl8139',
        4 => 'ne2k_pci',
        5 => 'virtio_net_pci',
        6 => 'pcnet',
        7 => 'vmxnet3',
    ],
    // 桥接网卡类型
    'DEVICE_TYPE_NIC' => [
        'NIC_DEVICE_TYPE_UNKNOWN' => 0,
        'NIC_DEVICE_TYPE_COMMON' => 1,             // 普通网卡
        'NIC_DEVICE_TYPE_LOOP' => 2,               // 回环网卡
        'NIC_DEVICE_TYPE_BRIDGE' => 3,             // 桥接网卡
        'NIC_DEVICE_TYPE_BOND' => 4,               // 聚合网卡
        'NIC_DEVICE_TYPE_BRIDGE_SLAVE' => 5,       // 桥接网卡接口
        'NIC_DEVICE_TYPE_BOND_SLAVE' => 6,         // 聚合网卡接口
    ],
    // 桥接网卡的隔离网段标识
    'ISOLATE_NETWORK_USED' => [
        'EMD_ISOLATE_NETWORK_USED_FOR_UNKNOWN' => 0,
        'EMD_ISOLATE_NETWORK_USED_FOR_RANGE_START' => 1 ,
        'EMD_ISOLATE_NETWORK_USED_FOR_RANGE_END' => 2,
        'EMD_ISOLATE_NETWORK_USED_FOR_NODE' => 3,
        'EMD_ISOLATE_NETWORK_USED_FOR_EMD_VM' => 4
    ],
    // 系统类型配置
    'OS_TYPE' => [
        'unknown',
        'win_common',
        'win_xp',
        'win_vista',
        'win_7',
        'win_8',
        'win_8-1',
        'win_10',
        'win_11',
        'win_server_2003',
        'win_server_2003_r2',
        'win_server_2008',
        'win_server_2008_r2',
        'win_server_2012',
        'win_server_2012_r2',
        'win_server_2016',
        'win_server_2019',
        'win_server_2022',
        100 => 'linux_common',
        'redhat6',
        'redhat7',
        'redhat8',
        'redhat9',
        150 => 'centos6',
        'centos7',
        'centos8',
        'centos8_stream',
        'centos9_stream',
        200 => 'kylin16',
        'kylin17',
        'kylin18',
        'kylin19',
        'kylin20',
        'kylin21',
        'kylin22',
        250 => 'ubuntu12',
        'ubuntu14',
        'ubuntu16',
        'ubuntu18',
        'ubuntu19',
        'ubuntu20',
        'ubuntu22',
        300 => 'debian6',
        'debian7',
        'debian8',
        'debian9',
        'debian10',
        'debian11',
        350 => 'rocky8',
        'rocky9',
        400 => 'orancle6',
        'orancle7',
        'orancle8',
        'orancle9',
        450 => 'open_enler20',
        'open_enler21',
        'open_enler22',
        'open_enler23',
        500 => 'anolis7',
        'anolis8',
        'anolis23',
        550 => 'suse11',
        'suse12',
        'suse15',
        600 => 'fedora23',
        'fedora24',
        'fedora25',
        'fedora26',
        'fedora27',
        'fedora28',
        'fedora29',
        'fedora33',
        'fedora34',
        'fedora35',
        650 => 'euleros2_2_0',
        'euleros2_3_0',
        'euleros2_5_0',
    ],
];
