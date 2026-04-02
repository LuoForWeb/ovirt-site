<?php
/**
 * 整机配置
 */
return  array
(
    'HostNameCheck' => array(
        'Linux' => array(  //windows
            'len' => '63',
            'limit' => '^(?![0-9]+$)[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?$',
            'msg' => Xphp::$_lang['UI_MACHINE_OS_HOSTNAME_MSG_63'],
        ),
        'Windows' => array(  //linux
            'len' => '15',
            'limit' => '^(?![0-9]+$)(?!(CON|AUX|COM[1-9]|LPT[1-9]|PRN|NUL)$)[A-Za-z0-9](?:[A-Za-z0-9-]{0,13}[A-Za-z0-9])?$',
            'msg' => Xphp::$_lang['UI_MACHINE_OS_HOSTNAME_MSG_15'],
        ),
    ),

    'VOLUME_TYPE' => array(
        "BD_VOLUME_TYPE_UNKNOWN" => 0x0000,
        "BD_BASIC_PARTITION" => 0x0001,		// 数据分区
        "BD_GPT_PARTITION" => 0x0002,			// GPT 分区
        'BD_DYNAMIC_VOLUME' => 0x0004,			// 动态卷
        "BD_LVM_VOLUME" => 0x0008,				// LVM 逻辑卷
        "BD_EFI_VOLUME" => 0x0010,             //EFI system partition.	 EFI分区
        "BD_BOOT_VOLUME" => 0x0020,            //Bios boot partition.
        "BD_SYSTEM_VOLUME" => 0x0040,          //Partitions for operating system load system data 	系统分区
        "BD_WIN_RESERVE_VOLUME" => 0x0080,     //Windows	Windows保留分区
        "BD_WIN_RECOVERY_VOLUME" => 0x0100,    //Windows	Windows恢复分区
        "BD_HIDDEN_VOLUME" => 0x0200,          //隐藏分区
        "BD_PV_VOLUME" => 0x0400,       		// LVM PV
        "BD_DMRAID_VOLUME" => 0x0800,   		// 软raid卷
        "BD_MPATH_VOLUME" => 0x1000,			// 多路径卷
        "BD_EXTEND_VOLUME"=> 0x2000,           //MBR extend partition. 扩展分区
        "BD_LOGIC_VOLUME"=> 0x4000,            //MBR logic partition.	逻辑分区
        "BD_REMOVABLE_VOLUME" => 0x8000,       //removable device		
        "BD_WIN_FIRMWARE_BOOT_VOLUME" => 0x10000, //current windows real boot volume	Windows引导分区
        "BD_SWAP_VOLUME" => 0x20000,            //Swap partition	SWAP分区
    ),




);