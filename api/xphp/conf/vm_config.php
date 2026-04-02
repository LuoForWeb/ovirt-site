<?php
/**
 * 虚拟机高级恢复配置
 */
return  array
(
    /* middle disk controller type */
    'VmMiddleControllerType' => array(
        'VM_MIDDLE_CONTROLLER_TYPE_UNKNOWN',
        'VM_MIDDLE_CONTROLLER_TYPE_IDE',				// IDE
        'VM_MIDDLE_CONTROLLER_TYPE_SCSI',				// SCSI
        'VM_MIDDLE_CONTROLLER_TYPE_VSCSI',			// virtio SCSI
        'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO',			// virtio
        'VM_MIDDLE_CONTROLLER_TYPE_SATA',				// sata, for ovirt
        'VM_MIDDLE_CONTROLLER_TYPE_E1000',			// e1000, for network
        'VM_MIDDLE_CONTROLLER_TYPE_RTL8139',			// rtl8139, for network
        
        //add for vmware
        'VM_MIDDLE_CONTROLLER_TYPE_LSI_LOGIC',		// scsi: lsi logic
        'VM_MIDDLE_CONTROLLER_TYPE_LSI_LOGIC_SAS',	// scsi: lsi logic sas
        'VM_MIDDLE_CONTROLLER_TYPE_BUS_LOGIC',		// scsi: bus logic
        'VM_MIDDLE_CONTROLLER_TYPE_PARA_VIRTUAL_SCSI',// scsi: para virtual scsi
        'VM_MIDDLE_CONTROLLER_TYPE_AHCI',				// sata
        'VM_MIDDLE_CONTROLLER_TYPE_USBXAHCI',			// usbxhci
        'VM_MIDDLE_CONTROLLER_TYPE_USB',				// usb
        'VM_MIDDLE_CONTROLLER_TYPE_NVME',				// nvme
        
        'VM_MIDDLE_CONTROLLER_TYPE_PCI',				// pci
        'VM_MIDDLE_CONTROLLER_TYPE_SIO',				// sio
        
        //VM_MIDDLE_CONTROLLER_TYPE_E1000',			// e1000
        'VM_MIDDLE_CONTROLLER_TYPE_E1000E',			// e1000e
        'VM_MIDDLE_CONTROLLER_TYPE_PCNET32',			// pcnet32
        'VM_MIDDLE_CONTROLLER_TYPE_SRIOV',			// sriov
        'VM_MIDDLE_CONTROLLER_TYPE_VMXNET',			// vmxnet
        'VM_MIDDLE_CONTROLLER_TYPE_VMXNET2',			// vmxnet2
        'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3',			// vmxnet3
        'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3_VRDMA',        //VRDMA
        
        //add for openstack
        'VM_MIDDLE_CONTROLLER_TYPE_UML',				// uml
        'VM_MIDDLE_CONTROLLER_TYPE_XEN',				// xen
        'VM_MIDDLE_CONTROLLER_TYPE_FDC',				// fdc
        'VM_MIDDLE_CONTROLLER_TYPE_LXC',				// lxc
        'VM_MIDDLE_CONTROLLER_TYPE_NE2K_PCI',			// ne2k_pci
        'VM_MIDDLE_CONTROLLER_TYPE_PCNET',			// pcnet
        'VM_MIDDLE_CONTROLLER_TYPE_NETFRONT',			// netfront
        'VM_MIDDLE_CONTROLLER_TYPE_SPAPR_VLAN',		// spapr_vlan
        
        // add for ovirt rtl8139_virtio
        Xphp::$_lang['UI_RECOVERY_VM_MIDDLE_CONTROLLER_TYPE_RTL8139_VIRTIO'],	// rtl8139_virtio
    ),
    
    
    
    'VmMiddleControllerTypeDes' => array(
        'UNKNOWN',
        'ide',      //1
        'scsi',
        'virtio scsi',
        'virtio',
        'sata',     //5
        'e1000',
        'rtl8139',
        
        //add for vmware
        'lsi logic',
        'lsi logic sas',
        'bus logic',    //10
        'para virtual scsi',
        'sata',
        'usbxhci',
        'usb',
        'nvme', //15
        'pci',
        'sio',
        
        
        'e1000e',
        'pcnet32',
        'sriov',    //20
        'vmxnet',
        'vmxnet2',
        'vmxnet3',
        
        //add for openstack
        'uml',
        'xen',
        'fdc',
        'lxc',
        'ne2k_pci',
        'pcnet',
        'netfront',
        'spapr_vlan',
        
        // add for ovirt rtl8139_virtio
        Xphp::$_lang['UI_RECOVERY_VM_MIDDLE_CONTROLLER_TYPE_RTL8139_VIRTIO'],	// rtl8139_virtio
    ),
    
    // middle boot mode type
    'VmMiddleBootModeType' => array(
        'UNKNOWN',
        'BIOS',
        'UEFI',
    ),
    
    //用于获取虚拟机类型的时候其相关的名称限制
    //     array(  //虚拟机类型
    //         'len' => '80',//字符限制 中文2字符 英文1字符
    //         'limit' => '', //用于js端的正则表达式
    //         'msg' =>'UI_NAME_MSG_80', //用于js端的语言包名称
    //     ),
    'VmNameCheck' => array(
        0 => 'Unknown',
        1 => array(  //VMware vSphere
            'len' => '80',
            'limit' => '',
            'msg' => Xphp::$_lang['UI_NAME_MSG_80'],
            'vmware_flag' => true,
        ),
        2 => array(  //Microsoft Hyper-V
            'len' => '100',
            'limit' => '^[^\\\\:*?"<>|/]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_HYPERV'],
        ),
        3 => array(  //Citrix XenServer
            'len' => '32767',
            'limit' => '[^\s]+',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_XENSERVER'],
            'disk_len' => '32767',
            'disk_limit' => '[^\s]+',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_XENSERVER']
        ),
        8 => array(  //InCloud Sphere Xen
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        9 => array(  //Halsign vGate
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        10 => array(  //NeoKylin
            'len' => '',
            'limit' => '',
            'msg' =>'',
        ),
        11 => array(  //H3C
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _/./-]+[\u4e00-\u9fa5a-zA-Z0-9 _/-]$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_64_H'],
            'disk_len' => '100',
            'disk_limit' => '',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_100']
        ),
        12 => array(  //SANGFOR HCI
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _@+().（）【】\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_SANGFOR'],
        ),
        14 => array(  //FlexCloud
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        15 => array(  //OpenStack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        16 => array(  //Huawei FusionSphere Kvm
            'len' => '256',
            'limit' => '[^\s]+',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_HUAWEIKVM'],
            'disk_len' => '256',
            'disk_limit' => '[^\s]+',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_HUAWEIKVM']
        ),
        17 => array(  //Huawei FusionCompute
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        18 => array(  //Winhong CNware
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_WINHONG'],
            'disk_len' => '80',
            'disk_limit' => '^[a-zA-Z_][a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_WINHONG_DISK'],
        ),
        19 => array(  //Redhat RHV
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        20 => array(  //D-Server
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        21 => array(  //SVM CloudVirtual
            'len' => '80',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_80'],
            'vmware_flag' => true,
        ),
        22 => array(  //Flex HCS
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        23 => array(  //Os Easy V-server
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        24 => array(  //InCloud Sphere KVM
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
            'disk_len' => '128',
            'disk_limit' => '',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        25 => array(  //V-Server
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        26 => array(  //ZStack
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
            'disk_len' => '128',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _:+().\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_ZSTACK'],
        ),
        27 => array(  //Easted vServer
            'len' => '64',
            'limit' => '^[a-zA-Z0-9_/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_64_L'],
        ),
        28 => array(  //XCP-ng
            'len' => '128',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        29 => array(  //Oracle Linux Virtualization Manager(OLVM)
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        30 => array(  //XSKY XECCP
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_@:/+/(/)/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128_L'],
        ),
        31 => array(  //Inspur Openstack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        32 => array(  //Winhong KVM
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_WINHONG'],
            'disk_len' => '80',
            'disk_limit' => '^[a-zA-Z_][a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_WINHONG_DISK'],
        ),

        33 => array(  //SmartX
            'len' => '255',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 ^()_+\-=\[\]{},.！￥……（）——【】、；‘’：“”，。、《》]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_SMARTX'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 ^()_+\-=\[\]{},.！￥……（）——【】、；‘’：“”，。、《》]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_SMARTX'],
        ),
        34 => array(  //Sugon CloudView
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        35 => array(  //Inspur Cloud Platform
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        36 => array(  //EasyStack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        37 => array(  //Fiberhome Openstack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        38 => array(  //CTSI Openstack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        39 => array(  //AWCloud
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),
        40 => array(  //Inspur VVDK
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
            'disk_len' => '128',
            'disk_limit' => '',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_128'],
        ),
        41 => array(  //zVirt
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        42 => array(  //Proxmox
            'len' => '128',
            'limit' => '^[a-zA-Z0-9][a-zA-Z0-9_-]*[a-zA-Z0-9]$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_PROXMOX'],
        ),
        43 => array(  //Xfusion Kvm
            'len' => '256',
            'limit' => '[^\s]+',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_HUAWEIKVM'],
            'disk_len' => '256',
            'disk_limit' => '[^\s]+',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_HUAWEIKVM']
        ),
        44 => array(  //xhere
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        45 => array(  //hostvm
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        46 => array(  //cbr
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        47 => array(  //SANGFOR VVDK
            'len' => '70',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _+().（）【】\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_SCP'],
            'scp_flag' => true,
        ),
        48 => array(  //CloudView KVM
            'len' => '128',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_:/+/(/)/./-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_128_L'],
        ),
        49 => array(  //red virt
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        50 => array(  //rosa virt
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        51 => array(  //H3C CAS CVD
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9 _/./-]+[\u4e00-\u9fa5a-zA-Z0-9 _/-]$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_64_H'],
            'disk_len' => '100',
            'disk_limit' => '',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_100']
        ),
        52 => array(  //oVirt
            'len' => '64',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT'],
            'disk_len' => '255',
            'disk_limit' => '^[\u4e00-\u9fa5a-zA-Z0-9_.\-]+$',
            'disk_msg' =>Xphp::$_lang['UI_NAME_MSG_OVIRT_DISK'],
        ),
        53 => array(  //Lenovo AIO
            'len' => '85',
            'limit' => '^[\u4e00-\u9fa5a-zA-Z0-9][\u4e00-\u9fa5a-zA-Z0-9_.:+\-]*$',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_LENOVO_AIO'],
        ),
        54 => array(  //Huawei Cloud Stack
            'len' => '255',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_255'],
        ),

        100 => array(  //aws
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
        108 => array(  //emd
            'len' => '256',
            'limit' => '',
            'msg' =>Xphp::$_lang['UI_NAME_MSG_256'],
        ),
    ),
);

?>