<?php

/**
 *虚拟机模块描述定义
 *TODO 每一个定义都关联语言文件
 */

return [
    //虚拟机状态
    'VmMachineStatus' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_VM_POWER_OFF'),
        ('WEB_VM_POWER_ON'),
        ('WEB_VM_SUSPEND'),
        ('WEB_VM_PAUSE'),
    ),

    //任务状态
    'VmTaskStatus' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_VM_WAITING'),
        ('WEB_VM_RUNNING'),
        ('WEB_VM_COMPLETION'),
        ('WEB_PLATFORM_DES_ERROR'),
        ('WEB_VM_NEW_ADD'),
        ('WEB_VM_PAUSE'),
    ),

    //传输模式VMware
    'VmTransportMode' => array(
        'file' => ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        'nbd' => ('UI_BACKUP_TRANSPORT_NBD_VMWARE'),
        'nbdssl' => ('UI_BACKUP_TRANSPORT_NBDSSL_VMWARE'),
        'san' => ('UI_BACKUP_TRANSPORT_SAN'),
        'hotadd' => ('UI_BACKUP_TRANSPORT_HOTADD')
    ),

    //传输模式XenServer
    'VmTransportModeXenServer' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_BACKUP_TRANSPORT_NBD'),
        ('UI_BACKUP_TRANSPORT_SAN'),
        ('UI_BACKUP_TRANSPORT_NBD_XENSERVER'),
        ('UI_BACKUP_TRANSPORT_NBD_SAN_XENSERVER'),
    ),

    //增量模式
    'IncModeDes' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_BACKUP_INCMODE_GENNERAL'),
        ('UI_BACKUP_INCMODE_HIGH_SPEED'),
        ('UI_BACKUP_INCMODE_CBT'),
    ),

    //演练恢复类型
    'OrchRecoveryMode' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_RECOVERY'),
        ('WEB_PLATFORM_DES_INSTANT_RECOVERY')
    ),

    //vcenter的宿主机授权状态描述
    'VcenterHostAuthDes' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_SYSTEM_AUTHIORIZED_ALL'),
        ('WEB_SYSTEM_AUTHIORIZED_PART'),
        ('WEB_SYSTEM_UNAUTHIORIZED'),
    ),

    //演练使用时间点类型
    'OrchTimepointType' => array(
        ('WEB_DRILLS_NOT_CONFIG'),
        ('WEB_DRILLS_LATEST_ARBITRARILY_BACKUP_POINT'),
        ('WEB_DRILLS_LATEST_FULL_BACKUP_POINT'),
        ('WEB_DRILLS_LATEST_INCREASE_BACKUP_POINT'),
        ('WEB_DRILLS_LATEST_DIFF_BACKUP_POINT'),
    ),

    //副本任务状态
    'CopyTaskStatus' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PUBLIC_SUCCESS'),
        ('WEB_PUBLIC_FAILURE'),
        ('WEB_PLATFORM_DES_RUNNING'),
        ('WEB_VM_WAITING'),
        ('WEB_PLATFORM_DES_ABNORMAL'),

    ),

    //传输模式Openstack
    'VmTransportModeOpenstack' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_BACKUP_TRANSPORT_NBD'),
        ('UI_BACKUP_TRANSPORT_SAN'),
        'UI_APPLIANCE_NAME',
        ('UI_BACKUP_TRANSPORT_SAN'),
    ),

    //传输模式华为KVM
    'VmTransportModeHuaWeiKVM' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_BACKUP_TRANSPORT_NBD'),
        ('UI_BACKUP_TRANSPORT_SAN'),
        'APPLIANCE',
        'LANFREE APPLIANCE',
        ('UI_BACKUP_TRANSPORT_NBD_XENSERVER'),
        'API LAN FREE',
    ),

    //传输模式Redhat
    'VmTransportModeRedHat' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_BACKUP_TRANSPORT_NBD'),
        ('UI_BACKUP_TRANSPORT_SAN'),
        ('UI_BACKUP_TRANSPORT_NBD_XENSERVER'),
        ('UI_BACKUP_TRANSPORT_NBD_SAN_XENSERVER'),
        'ImageIO',
    ),

    //传输模式KVM
    'VmTransportModeKvm' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_BACKUP_TRANSPORT_NBD'),
        ('UI_BACKUP_TRANSPORT_SAN'),
        ('UI_APPLIANCE_NAME'),
    ),

    //数据验证任务虚拟机状态
    'VERIFY_VM_STATUS' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_WAITING'),
        ('WEB_PLATFORM_DES_RUNNING'),
        ('WEB_PLATFORM_DES_SKIP'),
        ('WEB_PLATFORM_DES_ERROR'),
        ('WEB_PLATFORM_DES_SUCCESSED'),
        ('WEB_PLATFORM_DES_FINISH'),
        ('WEB_PLATFORM_DES_WARNING'),
    ),

    //数据验证功能状态描述
    'VERIFY_FUNC_STATUS' => array(
        ('WEB_PLATFORM_UNVERIFIED'),
        ('WEB_PLATFORM_DES_WAITING'),
        ('WEB_PLATFORM_DES_RUNNING'),
        ('WEB_PLATFORM_DES_SKIP'),
        ('WEB_PLATFORM_DES_ERROR'),
        ('WEB_PLATFORM_DES_SUCCESSED'),
        ('WEB_PLATFORM_DES_FINISH'),
        ('WEB_PLATFORM_DES_WARNING'),
    ),

    //虚拟实验室状态描述
    'VIRTUAL_LAB_STATUS' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('WEB_PLATFORM_DES_DEPLOY'),
        ('WEB_PLATFORM_DES_USE'),
        ('WEB_AGENT_STATUS_OFFLINE'),
        ('WEB_PLATFORM_DES_ABNORMAL'),
        ('WEB_PLATFORM_DES_ERROR'),
        ('WEB_PLATFORM_DES_MODIFY'),
        ('WEB_PLATFORM_DES_DEPLOYED')
    ),

    // 虚拟机重置CBT级别
    'RESET_CBT_LEVEL' => array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_BACKUP_CBT_NO_RESET'),
        ('UI_BACKUP_CBT_ERROR_RESET'),
        ('UI_BACKUP_CBT_FULL_RESET'),
    )
];
