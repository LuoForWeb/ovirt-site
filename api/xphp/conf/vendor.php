<?php
/**
 * 软件版本支持定义文件.
 * 1.通过不同配置,可以定义备份软件支持的虚拟化厂商
 * 2.通过不同配置,可以定义登录页面可以支持的备份代理下载
 */



return  array
(
		//本版本支持的虚拟化类型,不同OEM厂商支持版本不同, hypervisor => 描述
		'VMHYPERVISORTYPE' => array(
		    '1' => 'VMware vSphere',
		    '2' => 'Microsoft Hyper-V',
		    '3' => 'Citrix XenServer/Citrix Hypervisor',
		    '44' => 'XSKY XHERE',
// 		    '4' => 'KVM + Libvirt',
// 		    'XEN' => 5,
// 		    'ORACLEVM' => 6,

            ///////////////////////// OEM VMWARE //////////////////////////
// 		    'SUGON_CLOUD_VIEW_SRM' => 7,	// shu guang, sugon

            ///////////////////////// OEM KVM /////////////////////////////
            '24' =>	'InCloud Sphere / Rail',	// inspur Incloud shpere 5.X kvm
            '40' =>	'InCloud Sphere / Rail (VVDK)',	// Inspur VVDK
            '12' => 'Sangfor HCI',				// sangfor kvm
            '47' => 'Sangfor Cloud Platform',				// Sangfor Cloud Platform
            '11' => 'H3C UIS/CAS',					// h3c kvm
            '51' => 'H3C UIS/CAS CVD',					// h3c cas cvd
            '19' => 'Red Hat Virtualization(RHV)',              // redhat rhv
            '52' => 'oVirt',              // redhat ovirt
            '41' => 'zVirt',              // zVirt
            '45' => 'HOSTVM',           //HOSTVM kvm
            '49' => 'RED Virtualization',           //red virt kvm
            '50' => 'ROSA Virtualization',           //rosa virt kvm
            '29' =>  'Oracle Linux Virtualization Manager(OLVM)', // Oracle Linux Virtualization Manager(OLVM)
            '26' =>	 'ZStack Cloud',	    // ZStack
            '16' => 'Huawei FusionCompute KVM',// huawei fusion sphere kvm
            '32' => 'Winhong CNware WinStack',                 //winhong kvm
            '28' => 'XCP-ng',				// XCP-ng
            '33' => 'SmartX',                 //smartx kvm
            '42' => 'Proxmox VE',               // Proxmox
            '53' => 'Lenovo AIO',               // Lenovo AIO
            '43' => 'FusionOne Compute',           //Xfusion kvm
            '48' => 'CloudView',           //CloudView kvm

		    ///////////////////////// OEM XENSERVER ///////////////////////
		    '8' => 'InCloud Sphere Xen',	// lang chao, Inspur
            '9' => 'Halsign vGate',			// halsign
            '20' => 'D-Server',              // dongchen d server
            '18' => 'Winhong CNware',		//  Winhong WinServer, XenServer OEM
            '25' =>	'V-Server',	    // winhong diy

            ///////////////////////// OEM KVM /////////////////////////////
            '27' => 'Easted vServer',         //Easted vserser, based on ovirt
            '10' => 'NeoKylin',			// NeoKylin kvm

            ///////////////////////// OEM VMWARE //////////////////////////
            '21' => 'SVM CloudVirtual',	// cloud view svm, Vmware OEM
            '23' =>	'Os Easy V-server',			// wuhan os easy v-server
            '30' => 'XSKY XECCP',                  //XSKY

// 		    '13' => 'SDC OS(电科凌云)',				// dianke lingyun sdcos
		),

        //云平台类型
        'CLOUDHYPERVISORTYPE' => array(
            '15' => 'OpenStack',			// open stack kvm,
            '31' => 'InCloud OpenStack',                 //InCloud OpenStack
            '35' => 'Inspur Cloud Platform', // Inspur Cloud Platform
            '34' => 'Sugon CloudView',				// Sugon CloudView
            '36' => 'EasyStack',                  //XSKY
            '37' => 'Fiberhome Openstack',         //Fiberhome Openstack
            '38' => 'CTSI Openstack',				// CTSI Openstack
            '39' => 'AWCloud',
            '14' => 'FlexCloud',			// flex cloud kvm
            '22' => 'Flex HCS',          // flex hci kvm
            '54' => 'Huawei Cloud Stack',          // huawei cloud stack
        ),

        //公有云平台类型
        'PUBLICCLOUDHYPERVISORTYPE' => array(
            '100' => 'AWS', // AWS
            '101' => 'Huawei Cloud', // Huawei Cloud
        ),

        //代理类型
        'AGENT_TYPE' => array(
            'VM' => 0,      //虚拟机
//             'NODE' => 1,    //节点
            'CLIENT' => 2,  //客户端
            // 'DBTIMING'=> 3, //数据库定时
            'DBREALTIME'=> 4, //数据库实时
//             'DBPROTECT'=> 5, //数据库保护
        ),
        //代理厂商和安装包
       'AGENT_VENDOR' => array(
            array(
                'text' => 'Microsoft Hyper-V',       //厂商名称 Microsoft Hyper-V
                'version' => array(                 //软件版本
                    array(
                        'text' => '1.x',            //text显示
                        'value' => 'vinchin-agent',        //安装包查找关键字,通过关键字查找,找到对应的包后替换成具体的路径
                    ),
                ),
            ),
            array(
                'text' => 'Citrix XenServer/Citrix Hypervisor',       //厂商名称 Citrix XenServer
                'version' => array(                 //软件版本
    //                     array(
    //                         'text' => '6.2',            //text显示
    //                         'value' => 'xe.6.2',        //安装包查找关键字,通过关键字查找,找到对应的包后替换成具体的路径
    //                     ),
                    array(
                        'text' => '6.5/7.x/8.x',
                        'value' => 'xe.6.5',
                    ),
                )
            ),
            array(
                'text' => 'XCP-ng',       //厂商名称 XCP-ng
                'version' => array(                 //软件版本
                    array(
                        'text' => '7.x/8.x',
                        'value' => 'xe.6.5',
                    ),
                )
            ),
            /*
            array(
                'text' => 'KVM',                    //kvm公版
                'version' => array(
                    array(
                        'text' => 'Red Hat Enterprise Linux 6',
                        'value' => 'RHEL.6',
                    ),
                    array(
                        'text' => 'Red Hat Enterprise Linux 7',
                        'value' => 'RHEL.7',
                    ),
                    array(
                        'text' => 'Ubuntu Linux 12',
                        'value' => 'Ubuntu.12',
                    ),
                )
            ),*/
            array(
                'text' => 'Halsign vGate',          //红山世纪
                'version' => array(
                    array(
                        'text' => '5.2.0',
                        'value' => 'xe.6.2',
                    ),
                    array(
                        'text' => '5.2.1/6.0.0',
                        'value' => 'xe.6.5',
                    ),
                )
            ),
            array(
                'text' => 'H3C UIS/CAS',                //H3C
                'version' => array(
                    array(
                        'text' => 'CAS 5.X/UIS 6.0',
                        'value' => 'Ubuntu.12',
                    ),
                    array(
                        'text' => 'CAS 7.X/UIS (6.5/7.0/8.0)',
                        'value' => 'RHEL.7',
                    ),
                    array(
                        'text' => 'CAS 7.0(ARM)',
                        'value' => 'ARM-RHEL7',
                    ),
                )
            ),
            array(
                'text' => 'Sangfor HCI',           //深信服
                'version' => array(
                    array(
                        'text' => '5.x/6.x',
                        'value' => 'Debian.7',             //#表示暂时不支持
                    ),
                )
            ),
            array(
                'text' => 'NeoKylin',        //中标麒麟
                'version' => array(
    //                     array(
    //                         'text' => 'V6',
    //                         'value' => 'RHEL.6',
    //                     ),
                    array(
                        'text' => 'V7',
                        'value' => 'RHEL.7',
                    ),
                )
            ),
            /*
            array(
                'text' => 'SDC OS(电科凌云)',          //电科凌云
                'version' => array(
                    array(
                        'text' => 'V2.0',
                        'value' => 'RHEL.6',
                    ),
                )
            ),*/
            array(
                'text' => 'Winhong CNware',              //云宏
                'version' => array(
                    array(
                        'text' => '5.5.1',
                        'value' => 'xe.6.2',
                    ),
                    array(
                        'text' => '6.0',
                        'value' => 'xe.6.5',
                    ),
                    array(
                        'text' => '6.5/7.1',
                        'value' => 'whrelease',
                    ),
                    array(
                        'text' => '7.5',
                        'value' => 'release',
                    ),
                )
            ),
            array(

                'text' => 'InCloud Sphere / Rail',      //浪潮kvm
                'version' => array(                 //软件版本
                    array(
                        'text' => '5.x/6.x',
                        'value' => 'RHEL.7',
                    ),
                    array(
                        'text' => '5.8.1(ARM)',
                        'value' => 'ARM-RHEL8',
                    ),
                )
            ),
            array(
                'text' => 'InCloud Sphere Xen',              //浪潮
                'version' => array(
                    array(
                        'text' => '4.0/4.5',
                        'value' => 'xe.6.5',
                    ),
                )
            ),

            array(
                'text' => 'InCloud OpenStack',              //openstack
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),

            array(
                'text' => 'OpenStack',              //openstack
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'Inspur Cloud Platform',              //Inspur Cloud Platform
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'Sugon CloudView',              //Sugon CloudView
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'EasyStack',              //EasyStack
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'Fiberhome Openstack',              //Fiberhome Openstack
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'CTSI Openstack',              //CTSI Openstack
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'AWCloud',              //AWCloud
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'FlexCloud',              //FlexCloud
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'Flex HCS',              //Flex HCS
                'version' => array(
                    array(
                        'text' => 'Cloud(RHEL)',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => 'Cloud(UBUNTU)',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                    array(
                        'text' => 'Docker(RHEL)',
                        'value' => 'stack-docker.RHEL',
                    ),
                    array(
                        'text' => 'Docker(UBUNTU)',
                        'value' => 'stack-docker.Ubuntu',
                    ),
                )
            ),
           array(
               'text' => 'Huawei Cloud Stack',              //Huawei Cloud Stack
               'version' => array(
                   array(
                       'text' => 'Cloud(RHEL)',
                       'value' => 'stack-cloud.RHEL',
                   ),
                   array(
                       'text' => 'Cloud(UBUNTU)',
                       'value' => 'stack-cloud.Ubuntu',
                   ),
                   array(
                       'text' => 'Docker(RHEL)',
                       'value' => 'stack-docker.RHEL',
                   ),
                   array(
                       'text' => 'Docker(UBUNTU)',
                       'value' => 'stack-docker.Ubuntu',
                   ),
               )
           ),
            /*
            array(
                'text' => 'FlexCloud',              //赛特斯
                'version' => array(
                    array(
                        'text' => '4.2',
                        'value' => '#',
                    ),
                )
            ),*/
            array(
                'text' => 'Red Hat Virtualization(RHV)',              //红帽
                'version' => array(
                    array(
                        'text' => '4.0-4.3(RHEL7/CentOS7)',
                        'value' => 'el7',
                    ),
                    array(
                        'text' => '4.4-4.5(RHEL8/CentOS8)',
                        'value' => 'el8',
                    ),
                )
            ),
            array(
                'text' => 'Oracle Linux Virtualization Manager(OLVM)',              //Oracle
                'version' => array(
                    array(
                        'text' => '4.3(Oracle Linux7)',
                        'value' => 'el7',
                    ),
                    array(
                        'text' => '4.4-4.5(Oracle Linux8)',
                        'value' => 'el8',
                    )
                )
            ),
            array(
                'text' => 'D-Server',              //红帽
                'version' => array(                 //软件版本
                    array(
                        'text' => '2.x',
                        'value' => 'xe.6.5',
                    ),
                )
            ),
            array(
                'text' => 'Os Easy V-server',              //噢易
                'version' => array(                 //软件版本
                    array(
                        'text' => '4.x',
                        'value' => 'RHEL.6',
                    ),
                )
            ),
            array(
                'text' => 'V-Server',              //winhong diy
                'version' => array(
                    array(
                        'text' => '5.5.1',
                        'value' => 'xe.6.2',
                    ),
                    array(
                        'text' => '6.0',
                        'value' => 'xe.6.5',
                    ),
                    array(
                        'text' => '6.5/7.x',
                        'value' => 'whrelease',
                    ),
                )
            ),
            array(
                'text' => 'ZStack Cloud',              //ZStack
                'version' => array(
                    array(
                        'text' => '3.x/4.x/5.x',
                        'value' => 'stack-cloud.RHEL',
                    ),
                )
            ),
            array(
                'text' => 'Huawei FusionCompute KVM',       //Huawei FusionCompute KVM
                'version' => array(
                    array(
                        'text' => '3.x/4.x',
                        'value' => 'stack-cloud.RHEL',
                    ),
                ),
            ),
            array(
                'text' => 'CloudView',              //CloudView KVM
                'version' => array(
                    array(
                        'text' => '3.x/4.x',
                        'value' => 'stack-cloud.RHEL',
                    ),
                )
            ),
            array(
                'text' => 'XSKY XECCP',              //XSKY
                'version' => array(
                    array(
                        'text' => '3.9',
                        'value' => 'stack-cloud.RHEL',
                    ),
                )
            ),
            array(
                'text' => 'Easted vServer',              //易讯通VServer
                'version' => array(
                    array(
                        'text' => '4.x',
                        'value' => 'RHEL.7',
                    ),
                )
            ),
            array(
                'text' => 'Winhong CNware WinStack',              //易讯通VServer
                'version' => array(
                    array(
                        'text' => '8.x/9.x',
                        'value' => 'stack-cloud.RHEL',
                    ),
                    array(
                        'text' => '8.x(ARM)',
                        'value' => 'ARM-el7',
                    ),
                )
            ),
            array(
                'text' => 'zVirt',              //zVirt
                'version' => array(
                    array(
                        'text' => '3.1(CentOS Stream8)',
                        'value' => 'el8',
                    ),
                    array(
                        'text' => '3.3(CentOS Stream8)',
                        'value' => 'el8',
                    ),
                    array(
                        'text' => '4.1(CentOS Stream8)',
                        'value' => 'el8',
                    ),
                )
            ),
            array(
                'text' => 'Proxmox VE',              //Proxmox
                'version' => array(
                    array(
                        'text' => '7.x/8.x',
                        'value' => 'stack-cloud.Ubuntu',
                    ),
                )
            ),
            array(
                'text' => 'HOSTVM',              //HOSTVM
                'version' => array(
                    array(
                        'text' => '4.4(CentOS Stream8)',
                        'value' => 'el8',
                    ),
                )
            ),
            array(
                'text' => 'RED Virtualization',              //REDVIRT
                'version' => array(
                    array(
                        'text' => '7.3.0 (RED OS 7)',
                        'value' => 'el7',
                    ),
                )
            ),
            array(
                'text' => 'ROSA Virtualization',              //ROSAVIRT
                'version' => array(
                    array(
                        'text' => '2.1 (ROSA Linux 8)',
                        'value' => 'el8',
                    ),
                )
            ),
            array(
                'text' => 'oVirt',              //红帽ovirt
                'version' => array(
                    array(
                        'text' => '4.0-4.3(RHEL7/CentOS7)',
                        'value' => 'el7',
                    ),
                    array(
                        'text' => '4.4-4.5(RHEL8/CentOS8)',
                        'value' => 'el8',
                    ),
                )
            ),
        ),
        //数据库定时客户端下载
        "DBTIMING_CLIENT" => array(
            array(
                'text' => 'Windows X64',
                'value' => 'https://' . $_SERVER['SERVER_ADDR'] . ':8089' . '/vc/client/download?os=win&type=64'
            ),
            array(
                'text' => 'Windows X86',
                'value' => 'https://' . $_SERVER['SERVER_ADDR'] . ':8089' . '/vc/client/download?os=win&type=32'
            ),
            array(
                'text' => 'Linux 64',
                'value' => 'https://' . $_SERVER['SERVER_ADDR'] . ':8089' . '/vc/client/download?os=linux&type=64'
            ),
        ),
        "DBREALTIME_CLIENT" => array(
            array(
                'text' => 'Windows',
                'value' => 'dbcdp-agent.windows'
            ),
            array(
                'text' => 'Linux',
                'value' => 'dbcdp-agent.linux'
            )
        ),



    //操作系统
    "CLIENT_OS" => array(
        array(
            'text' => 'Windows',
            'value' => 'WINDOWS',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 1,  // 根据授权来显示客户端
            'version' => array(

            )
        ),
        array(
            'text' => 'RHEL/CentOS/CentOS Stream',
            'value' => 'RHEL/CentOS/CentOS STREAM',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 2,
            'version' => array(
                array(
                    'text' => 'RHEL 5/CentOS 5 x86_64',
                    'value' => 'RHEL5',
                    'licence_key' => 2.1,
                ),
                array(
                    'text' => 'RHEL 6/CentOS 6 x86_64',
                    'value' => 'RHEL6',
                    'licence_key' => 2.2,
                ),
                array(
                    'text' => 'RHEL 7/CentOS 7 x86_64',
                    'value' => 'RHEL7',
                    'licence_key' => 2.3,
                ),
                array(
                    'text' => 'RHEL 8/CentOS 8/CentOS Stream 8 x86_64',
                    'value' => 'RHEL8',
                    'licence_key' => 2.4,
                ),
                array(
                    'text' => 'RHEL 9/CentOS Stream 9 x86_64',
                    'value' => 'RHEL9',
                    'licence_key' => 2.5,
                ),
            )
        ),
        array(
            'text' => 'Ubuntu',
            'value' => 'UBUNTU',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 3,
            'version' => array(
                array(
                    'text' => 'Ubuntu x86_64',
                    'value' => 'UBUNTU',
                    'licence_key' => 3.1,
                ),
            )
        ),
        array(
            'text' => 'Debian',
            'value' => 'DEBIAN',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 4,
            'version' => array(
                array(
                    'text' => 'Debian x86_64',
                    'value' => 'DEBIAN',
                    'licence_key' => 4.1,
                ),
            )
        ),
        array(
            'text' => 'SUSE',
            'value' => 'SUSE',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 5,
            'version' => array(
                array(
                    'text' => 'SLES/openSUSE x86_64',
                    'value' => 'SUSE',
                    'licence_key' => 5.1,
                ),
            )
        ),
        array(
            'text' => 'Oracle Linux',
            'value' => 'ORACLE LINUX',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 6,
            'version' => array(
                array(
                    'text' => 'Oracle Linux 6 x86_64',
                    'value' => 'ORACLELINUX6',
                    'licence_key' => 6.1,
                ),
                array(
                    'text' => 'Oracle Linux 7 x86_64',
                    'value' => 'ORACLELINUX7',
                    'licence_key' => 6.2,
                ),
                array(
                    'text' => 'Oracle Linux 8 x86_64',
                    'value' => 'ORACLELINUX8',
                    'licence_key' => 6.3,
                ),
                array(
                    'text' => 'Oracle Linux 9 x86_64',
                    'value' => 'ORACLELINUX9',
                    'licence_key' => 6.4,
                ),
            )
        ),
        array(
            'text' => 'Rocky Linux',
            'value' => 'ROCKY LINUX',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 7,
            'version' => array(
                array(
                    'text' => 'Rocky Linux 8 x86_64',
                    'value' => 'ROCKYLINUX8',
                    'licence_key' => 7.1,
                ),
                array(
                    'text' => 'Rocky Linux 9 x86_64',
                    'value' => 'ROCKYLINUX9',
                    'licence_key' => 7.2,
                ),
            )
        ),
        array(
            'text' => 'Astra Linux',
            'value' => 'ASTRA LINUX',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 8,
            'version' => array(
                array(
                    'text' => 'Astra Linux 1.7.4 x86_64',
                    'value' => 'ASTRALINUX',
                    'licence_key' => 8.1,
                ),
            )
        ),
        array(
            'text' => 'RED OS',
            'value' => 'REDOS',
            'oversea_flag' => true,  // 海外是否显示
            'licence_key' => 9,
            'version' => array(
                array(
                    'text' => 'RED OS 7.1/7.2 x86_64',
                    'value' => 'REDOS',
                    'licence_key' => 9.1,
                ),
            )
        ),
        array(
            'text' => '麒麟kylin V10',
            'value' => 'KYLINV10',
            'oversea_flag' => false,  // 海外不显示国产操作系统
            'licence_key' => 10,
            'version' => array(
                array(
                    'text' => '麒麟kylin V10 x86_64',
                    'value' => 'KYLINX86',
                    'licence_key' => 10.1,
                ),
                array(
                    'text' => '麒麟kylin V10 aarch64',
                    'value' => 'KYLIN',
                    'licence_key' => 10.2,
                ),
            )
        ),
        array(
            'text' => '统信UOS 20',
            'value' => 'UOS20',
            'oversea_flag' => false,  // 海外不显示国产操作系统
            'licence_key' => 11,
            'version' => array(
                array(
                    'text' => '统信UOS 20 x86_64',
                    'value' => 'UNIONTECHX86',
                    'licence_key' => 11.1,
                ),
                array(
                    'text' => '统信UOS 20 aarch64',
                    'value' => 'UNIONTECH',
                    'licence_key' => 11.2,
                ),
            )
        ),
        array(
            'text' => '中科方德NFSChina',
            'value' => 'NFSCHINA',
            'oversea_flag' => false,  // 海外不显示国产操作系统
            'licence_key' => 12,
            'version' => array(
                array(
                    'text' => '中科方德NFSChina x86_64',
                    'value' => 'ZKFD-V4',
                    'licence_key' => 12.1,
                ),
            )
        ),
        array(
            'text' => '凝思Linx',
            'value' => 'NSLINX',
            'oversea_flag' => false,  // 海外不显示国产操作系统
            'licence_key' => 13,
            'version' => array(
                array(
                    'text' => '凝思Linx x86_64',
                    'value' => 'NSLINUX',
                    'licence_key' => 13.1,
                ),
                array(
                    'text' => '凝思Linx aarch64',
                    'value' => 'NSAARCH64',
                    'licence_key' => 13.2,
                ),
            )
        ),
        array(
            'text' => '欧拉openEuler',
            'value' => 'OPENEULER',
            'oversea_flag' => false,  // 海外不显示国产操作系统
            'licence_key' => 14,
            'version' => array(
                array(
                    'text' => '欧拉openEuler x86_64',
                    'value' => 'EulerX86',
                    'licence_key' => 14.1,
                ),
                array(
                    'text' => '欧拉openEuler aarch64',
                    'value' => 'EulerAARCH64',
                    'licence_key' => 14.2,
                ),
            )
        ),
        array(
            'text' => '龙蜥Anolis OS',
            'value' => 'ANOLISOS',
            'oversea_flag' => false,  // 海外不显示国产操作系统
            'licence_key' => 15,
            'version' => array(
                array(
                    'text' => '龙蜥Anolis OS 7/23.0 x86_64',
                    'value' => 'ANOLIS7OSX64',
                    'licence_key' => 15.1,
                ),
                array(
                    'text' => '龙蜥Anolis OS 8 x86_64',
                    'value' => 'ANOLIS8OSX64',
                    'licence_key' => 15.2,
                ),
            )
        ),
    ),

)
?>
