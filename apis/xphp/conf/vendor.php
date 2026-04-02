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
// 		    '4' => 'KVM + Libvirt',
// 		    'XEN' => 5,
// 		    'ORACLEVM' => 6,
		    
		    ///////////////////////// OEM VMWARE //////////////////////////
// 		    'SUGON_CLOUD_VIEW_SRM' => 7,	// shu guang, sugon
		    
		    
		    
		    ///////////////////////// OEM KVM /////////////////////////////
		    '19' => 'Red Hat Virtualization(RHV)/oVirt',              // redhat rhv
            '41' => 'zVirt',              // zVirt
		    '17' => 'Huawei FusionCompute Xen',
		    '16' => 'Huawei FusionCompute KVM',// huawei fusion sphere kvm
		    ///////////////////////// OEM KVM /////////////////////////////
		    '24' =>	'InCloud Sphere KVM',	// inspur Incloud shpere 5.X kvm
		    ///////////////////////// OEM XENSERVER ///////////////////////
		    '8' => 'InCloud Sphere Xen',	// lang chao, Inspur
		    
		    ///////////////////////// OEM KVM /////////////////////////////
		    '12' => 'Sangfor HCI',				// sangfor kvm
		    '11' => 'H3C UIS/CAS',					// h3c kvm
		    '26' =>	'ZStack Cloud',	    // ZStack
		    '15' => 'OpenStack',			// open stack kvm
		    '18' => 'Winhong CNware',		//  Winhong WinServer, XenServer OEM
		    ///////////////////////// OEM VMWARE //////////////////////////
		    '21' => 'SVM CloudVirtual',	// cloud view svm, Vmware OEM
		    
		    '14' => 'FlexCloud',			// flex cloud kvm
		    '22' => 'Flex HCS',          // flex hci kvm
// 		    '13' => 'SDC OS(电科凌云)',				// dianke lingyun sdcos

		    ///////////////////////// OEM XENSERVER ///////////////////////
		    '9' => 'Halsign vGate',			// halsign
		    ///////////////////////// OEM KVM /////////////////////////////
		    '10' => 'NeoKylin',			// NeoKylin kvm
		    
		    '25' =>	Xphp::$_config['VMHYPERVISORDES'][25],	    // winhong diy
		    '23' =>	'Os Easy V-server',			// wuhan os easy v-server
		    '27' => 'Easted vServer',         //Easted vserser, based on ovirt
		    '20' => 'D-Server',              // dongchen d server
		    
		    '28' => 'XCP-ng',				// XCP-ng
		    '29' =>  'Oracle Linux Virtualization Manager(OLVM)', // Oracle Linux Virtualization Manager(OLVM)
		    '30' => 'XSKY XECCP',                  //XSKY,
		    '31' => 'InCloud OpenStack',                  //Inspur Cloud OpenStack
		),
    
        //代理类型
        'AGENT_TYPE' => array(
            'VM' => 0,      //虚拟机
//             'NODE' => 1,    //节点
            'FILE' => 2,    //文件
            'DBTIMING'=> 3, //数据库定时
            'DBREALTIME'=> 4, //数据库实时
            'DBPROTECT'=> 5, //数据库保护
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
                    array(
                        'text' => '6.2',            //text显示
                        'value' => 'xe.6.2',        //安装包查找关键字,通过关键字查找,找到对应的包后替换成具体的路径
                    ),
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
                        'text' => 'CAS 7.X/UIS 6.5/7.0',
                        'value' => 'RHEL.7',
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
                
                'text' => 'InCloud Sphere KVM',      //浪潮kvm
                'version' => array(                 //软件版本
                    array(
                        'text' => '5.x/6.0',
                        'value' => 'RHEL.7',
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
                'text' => 'Red Hat Virtualization(RHV)/oVirt',              //红帽
                'version' => array(
                    array(
                        'text' => '4.0-4.3(RHEL7/CentOS7)',
                        'value' => 'el7',
                    ),
                    array(
                        'text' => '4.4(RHEL8/CentOS8)',
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
                'text' => Xphp::$_config['VMHYPERVISORDES'][25],              //winhong diy
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
                'text' => 'zVirt',              //zVirt
                'version' => array(
                    array(
                        'text' => '4.0-4.3(RHEL7/CentOS7)',
                        'value' => 'el7',
                    ),
                    array(
                        'text' => '4.4(RHEL8/CentOS8)',
                        'value' => 'el8',
                    ),
                )
            ),
        ),
        "FILE_AGENT_VENDOR" => array(
            array(
                'text' => "Windows",
                "value" => "windows"
            ),
            array(
                'text' => "RHEL6/CentOS6 X64",
                "value" => "RHEL6"
            ),
            array(
                'text' => "RHEL7/CentOS7 X64",
                "value" => "RHEL7"
            ),
            array(
                'text' => "RHEL8/CentOS8 X64",
                "value" => "RHEL8"
            ),
            array(
                'text' => "Ubuntu X64",
                "value" => "UBUNTU"
            ),
            array(
                'text' => "Debian X64",
                "value" => "DEBIAN"
            ),
            array(
                'text' => "麒麟",
                "value" => "KYLIN"
            ),
            array(
                'text' => "统信",
                "value" => "UNIONTECH"
            )
        ),
        //数据库定时客户端下载
        "DBTIMING_CLIENT" => array(
            array(
                'text' => "Windows X64",
                'value' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/vc/client/download?os=win&type=64"
            ),
            array(
                'text' => "Windows X86",
                'value' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/vc/client/download?os=win&type=32"
            ),
            array(
                'text' => "Linux 64",
                'value' => "https://" . $_SERVER['SERVER_ADDR'] . ":8089" . "/vc/client/download?os=linux&type=64"
            ),
        ),
        "DBREALTIME_CLIENT" => array(
            array(
                'text' => "Windows",
                "value" => "dbcdp-agent.windows"
            ),
            array(
                'text' => "Linux",
                "value" => "dbcdp-agent.linux"
            )
        ),
    
        "DB_AGENT_VENDOR" => array(
            array(
                'text' => "Windows",
                "value" => "database-agent.windows"
            ),
            array(
                'text' => "RHEL6/CentOS6 X64",
                "value" => "DB.RHEL6"
            ),
            array(
                'text' => "RHEL7/CentOS7 X64",
                "value" => "DB.RHEL7"
            ),
            array(
                'text' => "RHEL8/CentOS8 X64",
                "value" => "DB.RHEL8"
            ),
            array(
                'text' => "Ubuntu X64",
                "value" => "DB.UBUNTU"
            ),
            array(
                'text' => "Debian X64",
                "value" => "DB.DEBIAN"
            ),
//             array(
//                 'text' => "统信",
//                 "value" => "DB.uos"
//             )
        ),

)
?>