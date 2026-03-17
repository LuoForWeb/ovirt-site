<?php

/**
 * 虚拟机的一些配置信息
 */

return [
    //虚拟机模块定义
    'VMHYPERVISORTYPE' => array(
        'VM_HYPERVISOR_TYPE_UNKNOWN' => 0,
        'VM_HYPERVISOR_TYPE_VMWARE' => 1, //VMware vSphere
        'VM_HYPERVISOR_TYPE_HYPERV' => 2, //Microsoft Hyper-V
        'VM_HYPERVISOR_TYPE_XENSERVER' => 3, //Citrix XenServer/Citrix Hypervisor
        'VM_HYPERVISOR_TYPE_KVM' => 4,
        'VM_HYPERVISOR_TYPE_XEN' => 5,      //不支持20210304
        'VM_HYPERVISOR_TYPE_ORACLEVM' => 6, //不支持20210304

        ///////////////////////// OEM VMWARE //////////////////////////
        'VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM' => 7, // shu guang, sugon

        ///////////////////////// OEM XENSERVER ///////////////////////
        'VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE' => 8,    // lang chao, Inspur
        'VM_HYPERVISOR_TYPE_HALSIGN_VGATE' => 9,            // halsign

        ///////////////////////// OEM KVM /////////////////////////////
        'VM_HYPERVISOR_TYPE_NEOKYLIN_KVM' => 10,            // NeoKylin kvm
        'VM_HYPERVISOR_TYPE_H3C_KVM' => 11,                 // h3c kvm
        'VM_HYPERVISOR_TYPE_SANGFOR_KVM' => 12,             // sangfor kvm
        'VM_HYPERVISOR_TYPE_SDC_OS_KVM' => 13,              // dianke lingyun sdcos ,不支持20210304
        'VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM' => 14,          // flex cloud kvm
        'VM_HYPERVISOR_TYPE_OPENSTACK_KVM' => 15,           // open stack kvm

        'VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM' => 16,// huawei fusion sphere kvm
        'VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN' => 17,// huawei fusion sphere xen
        'VM_HYPERVISOR_TYPE_WINHONG_WINSERVER' => 18,       //  Winhong WinServer, XenServer OEM
        'VM_HYPERVISOR_TYPE_RHV_KVM' => 19,              // redhat rhv
        ///////////////////////// OEM XENSERVER //////////////////////////
        'VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER' => 20,        // dongchen d server

        ///////////////////////// OEM VMWARE //////////////////////////
        'VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE' => 21,   // cloud view svm, Vmware OEM

        'VM_HYPERVISOR_TYPE_FLEX_HCS_KVM' => 22,            // flex hci kvm

        'VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER' => 23,                // wuhan os easy v-server
        'VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM' => 24,       // inspur Incloud shpere 5.X kvm
        'VM_HYPERVISOR_TYPE_DIY_WINGHONG' => 25,                    // DIY winhong
        'VM_HYPERVISOR_TYPE_ZSTACK' => 26,                  // zstack
        'VM_HYPERVISOR_TYPE_EASTED_VSERVER' => 27,          // Easted vserser, based on ovirt
        'VM_HYPERVISOR_TYPE_XCP_NG' => 28,                  // XCP-ng
        'VM_HYPERVISOR_TYPE_OLVM' => 29,                    // Oracle Linux Virtualization Manager(OLVM)
        'VM_HYPERVISOR_TYPE_XSKY' => 30,                  //XSKY
        'VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK' => 31,    // lang chao, Inspur openstack
        'VM_HYPERVISOR_TYPE_WINHONG_KVM' => 32,                  //winhong KVM
        'VM_HYPERVISOR_TYPE_SMARTX_KVM' => 33,                  //smartx KVM
        'VM_HYPERVISOR_TYPE_SUGON_CLOOUD_OPENSTACK' => 34,                  //Sugon CloudView
        'VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM' => 35,                  //Inspur Cloud Platform
        'VM_HYPERVISOR_TYPE_EASYSTACK' => 36,                  //EasyStack
        'VM_HYPERVISOR_TYPE_FIBERHOME_OPENSTACK' => 37,                  //Fiberhome Openstack
        'VM_HYPERVISOR_TYPE_CTSI_OPENSTACK' => 38,                  //CTSI Openstack
        'VM_HYPERVISOR_TYPE_AW_CLOUD' => 39,                  //AWCloud
        'VM_HYPERVISOR_TYPE_INSPUR_VVDK' => 40,     // Inspur VVDK
        'VM_HYPERVISOR_TYPE_KVM_ZVIRT' => 41,              // zVirt, based on ovirt
        'VM_HYPERVISOR_TYPE_PROXMOX' => 42,             //Proxmox
        'VM_HYPERVISOR_TYPE_XFUSION_KVM' => 43,         //Xfusion KVM
        'VM_HYPERVISOR_TYPE_XHERE' => 44,       //xhere
        'VM_HYPERVISOR_TYPE_HOSTVM' => 45,      // hostvm, ovirt-engine
        'VM_HYPERVISOR_TYPE_HUAWEI_CBR' => 46,   //hauwei CBR
        'VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM' => 47,        // sangfor vvdk
        'VM_HYPERVISOR_TYPE_CLOUDVIEW_KVM' => 48,        // CloudView KVM
        'VM_HYPERVISOR_TYPE_RED_VIRT' => 49, 		// red virtualization, based on ovirt
        'VM_HYPERVISOR_TYPE_ROSA_VIRT' => 50, 		// rosa virtualization, based on ovirt
        'VM_HYPERVISOR_TYPE_H3C_CAS_CVD' => 51, 		// H3C CAS CVD, based on H3C KVM
        'VM_HYPERVISOR_TYPE_OVIRT_KVM' => 52,              // redhat ovirt
        'VM_HYPERVISOR_TYPE_LENOVO_AIO' => 53,              // Lenovo AIO KVM
        'VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK' => 54,              // huawei cloud stack, private cloud
        'VM_HYPERVISOR_TYPE_VOLC' => 55,              // volcano cloud
        'VM_HYPERVISOR_TYPE_ZSTACK_ZSPHERE' => 56,              // zstack zsphere
        'VM_HYPERVISOR_TYPE_KSPHERE' => 57,              // ksphere, based on ics vvdk
        'VM_HYPERVISOR_TYPE_ARCFRA_KVM' => 58,              // arcfra, based on smartx
        'VM_HYPERVISOR_TYPE_NEXAVM_NSSV' => 59,              // nexavm nssv, based on zstack zsphere
        'VM_HYPERVISOR_TYPE_NEXAVM_NCSSV' => 60,              // nexavm ncssv, based on zstack cloud
        'VM_HYPERVISOR_TYPE_NUTANIX_AHV' => 61,              // nutanix ahv
        'VM_HYPERVISOR_TYPE_AWS' => 100, //AWS
        'VM_HYPERVISOR_TYPE_HUAWEI_CLOUD' => 101, //huawei cloud
        'VM_HYPERVISOR_TYPE_EMD' => 108, // 虚拟演练室
    ),

    //虚拟化分组,同一个分组可以相互恢复
    'VMHYPERVISORGROUP' => array(
        //VMware分组
        'vmware' => array(
            1,
            7,
            21
        ),
        //XenServer分组
        'xenserver' => array(
            3,
            8,
            9,
            18,
            20,
            25,
            28
        ),
        //KVM分组
//             'kvm' => array(
//               4, 10, 11, 12, 13, 14, 15, 19, 29
//             ),

        //huawei
        'huawei' => array(
            17
        ),
        'openstack' => array(
            14,
            15,
            22,
            26,
            30,
            31,
            34,
            35,
            36,
            37,
            38,
            39,
            54,
            60
        ),
        // 私有云，包含zstack cloud/xeccp
        'privatecloud' => array(
            14,
            15,
            22,
            26,
            30,
            31,
            34,
            35,
            36,
            37,
            38,
            39,
            54,
            60
        ),
        'redhat' => array(
            19,
            29,
            41,
            45,
            49,
            50,
            52
        ),
        'publiccloud' => array(
            100,
            101
        ),
    ),

    //虚拟机模块描述
    'VMHYPERVISORDES' => array(
        'Unknown',
        'VMware vSphere',
        'Microsoft Hyper-V',
        'Citrix XenServer/Citrix Hypervisor',
        'KVM',
        'Xen',
        'Oracle VM',
        'Cloudview SVM',
        'InCloud Sphere Xen',
        'Halsign vGate',
        'NeoKylin',
        'H3C UIS/CAS',
        'Sangfor HCI',
        'SDC OS',
        'FlexCloud',
        'OpenStack',
        'Huawei FusionCompute KVM',
        'Huawei FusionCompute Xen',
        'Winhong CNware',
        'Red Hat Virtualization(RHV)',
        'D-Server',
        'SVM CloudVirtual',
        'Flex HCS',
        'Os Easy V-server',
        'InCloud Sphere / Rail',
        'V-Server',
        'ZStack Cloud',
        'Easted vServer',
        'XCP-ng',
        'Oracle Linux Virtualization Manager(OLVM)',
        'XSKY XECCP',
        'InCloud OpenStack',
        'Winhong CNware WinStack',
        'SmartX',
        'Sugon CloudView',
        'Inspur Cloud Platform',
        'EasyStack',
        'Fiberhome Openstack',
        'CTSI Openstack',
        'AWCloud',
        'InCloud Sphere / Rail (VVDK)',
        'zVirt',
        'Proxmox VE',
        'FusionOne Compute',
        'XSKY XHERE',
        'HostVM',
        'HUAWEI CBR',
        'Sangfor Cloud Platform',
        'CloudView',
        'RED Virtualization',
        'ROSA Virtualization',
        'H3C UIS/CAS CVD',
        'oVirt',
        'Lenovo AIO',
        'Huawei Cloud Stack',
        'Volcano Cloud',
        'ZStack ZSphere',
        'KayGrid / KSRail',
        'Arcfra',
        'NexaVM nSSV',
        'NexaVM nCSSV',
        'Nutanix AHV',
        100 => 'AWS',
        101 => 'Huawei Cloud',
        108 => xphp_get_lang('UI_VM_MACHINE_MANAGER'), // 容灾演练平台

    ),

    //针对OEM出厂未授权显示虚拟化,授权后读取授权文件的虚拟化，如果为空不处理
    'RELEASE_HYPERVISOR' => array(),

    //vm task level
    'VmTaskLevel' => array(
        'UNKNOWN' => 0,             //unknown level
        'VM' => 1,                  //backup or recover entire vm
        'FILE' => 2,                //backup or recovery file in vm
        'INSTANT_RECOVERY' => 3,    //instant recovery task
        'DATA_DISK' => 4,           //backup or recovery disk
    ),

    //vm tree display mode
    'VM_TREE_DISPLAY_MODE' => array(
        'UNKNOWN' => 0,
        'HOST_AND_CLUSTER' => 1,
        'VM_AND_TEMPLATE' => 2,
        'HOST_AND_VM' => 3,
        'ALL' => 4,
    ),

    //vm tree node type
    'VM_TREE_TYPE' => array(
        'UNKNOWN' => 0,
        'FOLDER' => 1,      //文件
        'DATACENTER' => 2,  //数据中心
        'CLUSTER' => 3,     //集群
        'HOST' => 4,        //主机
        'POOL' => 5,        //资源池
        'VAPP' => 6,        //vApp
        'VM' => 7,          //虚拟机
        'OVIRT_FAKE_HOST_FOLDER' => 8, //红帽ovirt用基础设施类型
        'OVIRT_FAKE_VM_FOLDER' => 9,
        'OVIRT_CLUSTER_FOLDER' => 10,
        'OVIRT_CLUSTER' => 11,
        'OVIRT_DATACENTER' => 12,
        'TEMPLATE' => 15,
    ),

    //虚拟机连接状态
    'VmMachineConnectState' => array(
        'UNKNOWN' => 0,
        'CONNECTED' => 1,
        'DISCONNECTED' => 2,
        'COMPLETED' => 3,
    ),

    //宿主机状态
    'HOSTSTATUS' => array(
        'UNKNOWN' => 0,
        'CONNECT' => 1,
        'DISCONNECT' => 2,
    ),

    //虚拟机状态VmMachineStatus
    'MACHINESTATUS' => array(
        'UNKNOWN' => 0,
        'POWEREDOFF' => 1,
        'POWEREDON' => 2,
        'SUSPENDED' => 3,
        'PAUSE' => 4,
    ),

    //虚拟机数据存储状态
    'VMDATASTORESTATUS' => array(
        'UNKNOWN' => 0,
        'CONNECT' => 1,
        'DISCONNECT' => 2,
    ),

    //虚拟机任务状态
    'VmTaskStatus' => array(
        'UNKNOWN' => 0,
        'WAITTING' => 1,
        'RUNNING' => 2,
        'FINISH' => 3,
        'ERROR' => 4,
        'NEW_ADD' => 5,
        'PAUSED' => 6
    ),
    'VM_RECOVERY_ZERO' => array(
        'UNKNOWN' => 0,
        'NOT_ZERO' => 1,
        'INCLOUD_ZERO' => 2
    ),

    //虚拟化类型树形结构，用于生成前端对应的class，以虚拟化名称来命名class,和上面VMHYPERVISORTYPE相对应
    'VIRTUALIZATIONICONCLASSNAME' => array(
        'vm_unknow',
        'vm_vmware',
        'vm_hyperv',
        'vm_xenserver',
        'vm_kvm',
        'vm_xen',       //···5
        'vm_oraclevm',

        ///////////////////////// OEM VMWARE //////////////////////////
        'vm_sugon_cloud_view_srm',  // shu guang, sugon

        ///////////////////////// OEM XENSERVER ///////////////////////
        'vm_inspur_incloud_sphere', // lang chao, Inspur
        'vm_halsign_vgate',         // halsign

        ///////////////////////// OEM KVM /////////////////////////////
        'vm_neokylin_kvm',          // NeoKylin kvm ···10
        'vm_h3c_kvm',                   // h3c kvm
        'vm_sangfor_kvm',               // sangfor kvm
        'vm_sdc_os_kvm',                // dianke lingyun sdcos
        'vm_flex_cloud_kvm',            // flex cloud kvm
        'vm_openstack_kvm',         // open stack kvm ···15

        'vm_huawei_fusion_sphere_kvm',// huawei fusion sphere kvm
        'vm_huawei_fusion_sphere_xen',// huawei fusion sphere xen
        'vm_winhong_winserver',     //  Winhong WinServer, XenServer OEM
        'vm_rhv_kvm',              // redhat rhv
        ///////////////////////// OEM XENSERVER //////////////////////////
        'vm_dongchen_dserver',      // dongchen d server ···20

        ///////////////////////// OEM VMWARE //////////////////////////
        'vm_cloud_view_svm_vmware', // cloud view svm, Vmware OEM

        'vm_flex_hcs_kvm',          // flex hci kvm

        'vm_os_easy_v_server',              // wuhan os easy v-server
        'vm_inspur_incloud_sphere_kvm',     // inspur Incloud shpere 5.X kvm
        'vm_diy_winghong',                  // DIY winhong ···25
        'vm_zstack',                    // zstack
        'vm_easted_vserver',            // Easted vserser, based on ovirt
        'vm_xcp_ng',                    // XCP-ng
        'vm_olvm',                  // Oracle Linux Virtualization Manager(OLVM)
        'vm_xsky',                  //XSKY ···30
        'vm_inspur_incloud_sphere', //incloud openstack ···31
        'vm_winhong_kvm',                  //winhong_kvm ···32
        'vm_smartx',                  //smartx_kvm ···33
        'vm_sugon_cloud_openstack',                  //cloud openstack ···34
        'vm_inspur_cloud_platform',                  //inspur cloud platform ···35
        'vm_easystack',                  //easystack ···36
        'vm_fiberhome_openstack',                  //fiberhome openstack ···37
        'vm_ctsi_openstack',                  //ctsi openstack ···38
        'vm_aw_cloud',                  //AWCloud ···39
        'vm_inspur_vvdk',                  //Inspur VVDK ···40
        'vm_kvm_zvirt',              // zVirt ···41
        'vm_proxmox',                       // Proxmox ···42
        'vm_xfusion',                       // Xfusion KVM ···43
        'vm_xhere',                     // xhere ···44
        'vm_hostvm',                        // hostvm, ovirt-engine ···45
        'vm_huawei_cbr',                //huwwei cbr ···46
        'vm_sangfor_vvdk_kvm',                      // sangfro vvdk ···47
        'vm_cloudview_kvm',                      // CloudView KVM ···48
        'vm_kvm_redvirt',				// red virt ···49
        'vm_kvm_rosavirt',				// rosa virt ···50
        'vm_h3c_cas_cvd',				// h3c cas cvd ···51
        'vm_ovirt_kvm',              // redhat ovirt ···52
        'vm_lenovo_aio',              // Lenovo AIO KVM ···53
        'vm_huawei_cloud_stack',              // huawei cloud stack ···54
        'vm_volc',              // volcano cloud ···55
        'vm_zstack_zsphere',              // zstack zsphere ···56
        'vm_ksphere',              // ksphere ···57
        'vm_arcfra',              // arcfra ···58
        'vm_nexavm_nssv',              // nexavm_nssv ···59
        'vm_nexavm_ncssv',              // nexavm_ncssv ···60
        'vm_nutanix_ahv',              // nutanix ahv ···61
        100 => 'vm_aws',                        // AWS, public cloud platform ···100
        101 => 'vm_huawei_cloud',                        // huawei cloud, public cloud platform ···101
    ),

    //宿主机授权状态
    'HOST_LISENCE' => array(
        'UNKNOWN' => 0,
        'ALL' => 1,     //全部授权
        'PART' => 2,    //部分授权
        'NONE' => 3     //未授权
    ),

    //备份时间点展示方式
    'POINTSHOWTYPE' => array(
        'UNKNOWN' => 0,
        'VMGROUP' => 1,
        'TIMEGROUP' => 2,
    ),

    //虚拟机子模块类型
    "VM_SUB_MODULE" => array(
        'UNKNOWN' => 0,
        'VM' => 1,
        'PRIVATE_CLOUD' => 2,
        'PUBLIC_CLOUD' => 3
    ),

    //细粒度展示模式
    "GUEST_DISPLAY_MODE" => array(
        'UNKNOWN' => 0,
        'SYSTEM' => 1,          //系统目录结构
        'NORMAL_DEVICE' => 2,   //物理磁盘设备
        'LOGICAL_DEVICE' => 3,  //逻辑卷
    ),

    //细粒度文件类型
    "GUEST_FILE_ITEM_TYPE" => array(
        'UNKNOWN' => 0,                     // unknown type
        'FILE' => 1,                        // file
        'DIR' => 2,                         // dir
        'SLINK' => 3,                       // symbolic link 不点
        'SLINK_TARGET_UNREACHABLE' => 4,    // symbolic link, but target unreachable 当成文件，不点进去
    ),

    //细粒度恢复文件块大小 8MB
    'GRAIN_FILE_BLOCK_SIZE' => 8388608,

    // 传输模式
    'TRANS_MODE' => [
        "NBD" => 'nbd',
        "NBDSSL" => 'nbdssl',
        "SAN" => 'san',
        "HOTADD" => 'hotadd',
        "NBD_XEN" => 1, // 网络传输
        "SAN_XEN" => 2, // SAN
        "PROXY" => 3, // 传输代理
        "OPENSTACK_SAN" => 4, // OpenStack SAN
        "ADAPTIVE" => 5, // 自适应传输，oVirt系为ImageIO
    ],

    // 快照模式
    'SNAPSHOT_MODE' => [
        'serial' => 1, // 并行
        'parallel' => 2, // 串行
    ],

    // 虚拟机重置CBT级别
    'RESET_CBT_LEVEL' => [
        'NONE' => 1, // 不重置
        'ERROR' => 2, // 错误时重置
        'FULL_BACKUP' => 3, // 完备时重置
    ],

    // 内嵌机器授权标识
    'VM_EMD_TYPE' => 1000,
    // 整机授权标识
    'MACHINE_VM_TYPE' => 1001,
];
