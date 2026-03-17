<?php

/**
 *数据验证描述定义
 *TODO 每一个定义都关联语言文件
 */

return array(
    //验证方式
    'VERIFY_MODE_DES' =>  array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        ('UI_VERIFY_MODE_BACKUP_DATA_SCAN'),
        ('UI_VERIFY_MODE_FULL_RECOVER'),
        ('UI_VERIFY_MODE_GMP_VERIFITED'),
        ('UI_VERIFY_MODE_AFTER_BACKUP'),
    ),

    //验证类型
    'VERIFY_TYPE_DES' => array(
        ('UI_VERIFY_AUTOMATIC'),
        ('UI_VERIFY_MANUAL'),
    ),

    //磁盘驱动器类型
    'DSIK_TARGET_BUS_DES' =>array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        'IDE',
        'VIRTIO',
        'SATA',
        'SCSI',
        'FDC',
        'PCI',
        'USB'
    ),

    //网络适配器类型
    'NETCARD_TARGET_BUS_DES' =>array(
        ('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        'VIRTIO',
        'E1000',
        'RTL8139',
        'NE2K_PCI',
        'VIRTIO_NEW_PCI',
        'PCNET',
        'VMNET3'
    ),
);
