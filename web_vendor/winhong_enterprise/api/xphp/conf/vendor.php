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
// 		    '2' => 'Microsoft Hyper-V',
		    '3' => 'Citrix XenServer',
// 		    '4' => 'KVM + Libvirt',
// 		    'XEN' => 5,
// 		    'ORACLEVM' => 6,
		    
		    ///////////////////////// OEM VMWARE //////////////////////////
// 		    'SUGON_CLOUD_VIEW_SRM' => 7,	// shu guang, sugon
		    
		    ///////////////////////// OEM XENSERVER ///////////////////////
		    '8' => 'InCloud Sphere',	// lang chao, Inspur
		    '9' => 'Halsign vGate',			// halsign
		    
		    ///////////////////////// OEM KVM /////////////////////////////
		    '10' => 'NeoKylin',			// NeoKylin kvm
		    '11' => 'H3C CAS',					// h3c kvm
		    '12' => 'SANGFOR HCI',				// sangfor kvm
// 		    '13' => 'SDC OS(电科凌云)',				// dianke lingyun sdcos
		    '14' => 'FlexCloud',			// flex cloud kvm
		    '15' => 'OpenStack',			// open stack kvm
		    
// 		    'HUAWEI_FUSION_SPHERE_KVM' => 16,// huawei fusion sphere kvm
		    '17' => 'Huawei Fusion Compute',
		    '18' => 'Winhong CNware',		//  Winhong WinServer, XenServer OEM
		    '19' => 'Redhat RHV/Ovirt',              // redhat rhv
		    '20' => 'D-Server',              // dongchen d server
			'21' => 'Cloudview SVM',	// cloud view svm, Vmware OEM
			'22' => 'Flex HCS',          // flex hci kvm
			'23' =>	'Os Easy V-server',			// wuhan os easy v-server
			'24' =>	'InCloud Sphere Kvm',	// inspur Incloud shpere 5.X kvm
		    '25' =>	Xphp::$_config['VMHYPERVISORDES'][25],	    // winhong diy
		),
    
        //代理类型
        'AGENT_TYPE' => array(
            'VM' => 0,      //虚拟机
            'NODE' => 1,    //节点
//             	'FILE' => 2,    //文件
        ),
        //代理厂商和安装包
        'AGENT_VENDOR' => array(
            array(
                'text' => 'Citrix XenServer',       //厂商名称 Citrix XenServer
                'version' => array(                 //软件版本
                    array(
                        'text' => '6.2',            //text显示
                        'value' => 'xe.6.2',        //安装包查找关键字,通过关键字查找,找到对应的包后替换成具体的路径
                    ),
                    array(
                        'text' => '6.5/7.x',
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
                'text' => 'H3C CAS',                //H3C
                'version' => array(
                    array(
                        'text' => 'E02.x',
                        'value' => 'Ubuntu.12',
                    ),
                	array(
                		'text' => 'E03.x',
                		'value' => 'Ubuntu.12',
                	),
                	array(
                		'text' => 'E05.x',
                		'value' => 'Ubuntu.12',
                	),
                )
            ),
            array(
                'text' => 'SANGFOR HCI',           //深信服
                'version' => array(
                    array(
                        'text' => '5.x',
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
                		'text' => '6.5/7.x',
                		'value' => 'whrelease',
                	),
                )
            ),
            array(
                'text' => 'InCloud Sphere',              //浪潮
                'version' => array(
                    array(
                        'text' => '4.0/4.5',
                        'value' => 'xe.6.5',
                    ),
                )
            ),
        		
        	array(
        		 'text' => 'OpenStack Compute',              //openstack
        		 'version' => array(
        		 	array(
        		 		'text' => 'mitaka',
        		 		'value' => 'openstack-compute',
        		 	),
        		 )
        	),
        	array(
        		'text' => 'OpenStack Controller',              //openstack
        		'version' => array(
        			array(
        				'text' => 'mitaka',
        				'value' => 'openstack-controller',
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
                'text' => 'Redhat RHV/Ovirt',              //红帽
                'version' => array(
                    array(
                        'text' => '4.x',
                        'value' => 'RHEL.7',
                    ),
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
        			
        		'text' => 'InCloud Sphere Kvm',      //浪潮kvm
        		'version' => array(                 //软件版本
        			array(
        				'text' => '5.x',
                        'value' => 'RHEL.7',
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
        	
        ),
)
?>