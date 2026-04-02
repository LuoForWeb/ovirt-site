<?php
/**
 *虚拟机模块描述定义
 *TODO 每一个定义都关联语言文件
 */

return array(
    //虚拟机状态
    'VmMachineStatus' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_VM_POWER_OFF'],
        Xphp::$_lang['WEB_VM_POWER_ON'],
        Xphp::$_lang['WEB_VM_SUSPEND'],
        Xphp::$_lang['WEB_VM_PAUSE'],
    ),
    //任务状态
    'VmTaskStatus' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_VM_WAITING'],
        Xphp::$_lang['WEB_VM_RUNNING'],
        Xphp::$_lang['WEB_VM_COMPLETION'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],
    	Xphp::$_lang['WEB_VM_NEW_ADD'],
    	Xphp::$_lang['WEB_VM_PAUSE'],
    ),
    //传输模式VMware
    'VmTransportMode' => array(
        'file' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        'nbd' => Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD_VMWARE'],
        'nbdssl' => Xphp::$_lang['UI_BACKUP_TRANSPORT_NBDSSL_VMWARE'],
        'san' => Xphp::$_lang['UI_BACKUP_TRANSPORT_SAN'],
        'hotadd' => Xphp::$_lang['UI_BACKUP_TRANSPORT_HOTADD']
    ),
    //传输模式XenServer
    'VmTransportModeXenServer' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_SAN'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD_XENSERVER'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD_SAN_XENSERVER'],
    ),
    //增量模式
    'IncModeDes' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_BACKUP_INCMODE_GENNERAL'],
        Xphp::$_lang['UI_BACKUP_INCMODE_HIGH_SPEED'],
        Xphp::$_lang['UI_BACKUP_INCMODE_CBT'],
    ),
    //演练恢复类型
    'OrchRecoveryMode' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_RECOVERY'],
        Xphp::$_lang['WEB_PLATFORM_DES_INSTANT_RECOVERY']
    ),
    //vcenter的宿主机授权状态描述
    'VcenterHostAuthDes' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_SYSTEM_AUTHIORIZED_ALL'],
        Xphp::$_lang['WEB_SYSTEM_AUTHIORIZED_PART'],
        Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'],
    ),

    //演练使用时间点类型
    'OrchTimepointType' => array(
        Xphp::$_lang['WEB_DRILLS_NOT_CONFIG'],
        Xphp::$_lang['WEB_DRILLS_LATEST_ARBITRARILY_BACKUP_POINT'],
        Xphp::$_lang['WEB_DRILLS_LATEST_FULL_BACKUP_POINT'],
        Xphp::$_lang['WEB_DRILLS_LATEST_INCREASE_BACKUP_POINT'],
        Xphp::$_lang['WEB_DRILLS_LATEST_DIFF_BACKUP_POINT'],
    ),
	//副本任务状态
	'CopyTaskStatus' => array(
		Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
		Xphp::$_lang['WEB_PUBLIC_SUCCESS'],
		Xphp::$_lang['WEB_PUBLIC_FAILURE'],
		Xphp::$_lang['WEB_PLATFORM_DES_PAUSE'],
		Xphp::$_lang['WEB_ERROR_BD_ERRNO_ENETDOWN'],
		Xphp::$_lang['WEB_VM_WAITING'],
		Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'],

	),

	//传输模式Openstack
    'VmTransportModeOpenstack' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_SAN'],
        "proxy",
        Xphp::$_lang['UI_BACKUP_TRANSPORT_SAN'],
    ),
    //传输模式华为KVM
    'VmTransportModeHuaWeiKVM' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_SAN'],
        'APPLIANCE',
        'LANFREE APPLIANCE',
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD_XENSERVER'],
        'API LAN FREE',
    ),
    //传输模式Redhat
    'VmTransportModeRedHat' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_SAN'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD_XENSERVER'],
        Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD_SAN_XENSERVER'],
        'ImageIO',
    ),
    //数据验证任务虚拟机状态
    'VERIFY_VM_STATUS' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_WAITING'],
        Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'],
        Xphp::$_lang['WEB_PLATFORM_DES_SKIP'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],
        Xphp::$_lang['WEB_PLATFORM_DES_SUCCESSED'],
        Xphp::$_lang['WEB_PLATFORM_DES_FINISH'],
        Xphp::$_lang['WEB_PLATFORM_DES_WARNING']
    ),
    //数据验证功能状态描述
    'VERIFY_FUNC_STATUS' => array(
        Xphp::$_lang['WEB_PLATFORM_UNVERIFIED'],
        Xphp::$_lang['WEB_PLATFORM_DES_WAITING'],
        Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'],
        Xphp::$_lang['WEB_PLATFORM_DES_SKIP'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],
        Xphp::$_lang['WEB_PLATFORM_DES_SUCCESSED'],
        Xphp::$_lang['WEB_PLATFORM_DES_FINISH'],
        Xphp::$_lang['WEB_PLATFORM_DES_WARNING'],
    ),
    //虚拟实验室状态描述
    'VIRTUAL_LAB_STATUS' => array(
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['WEB_PLATFORM_DES_DEPLOY'],
        Xphp::$_lang['WEB_PLATFORM_DES_USE'],
        Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'],
        Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'],
        Xphp::$_lang['WEB_PLATFORM_DES_ERROR'],
        Xphp::$_lang['WEB_PLATFORM_DES_MODIFY'],
        Xphp::$_lang['WEB_PLATFORM_DES_DEPLOYED']
    ),
    //重置CBT级别
    'RESET_CBT_LEVEL' => [
        Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'],
        Xphp::$_lang['UI_BACKUP_CBT_NO_RESET'],
        Xphp::$_lang['UI_BACKUP_CBT_ERROR_RESET'],
        Xphp::$_lang['UI_BACKUP_CBT_FULL_RESET'],
    ]
);

?>