<?php
return  array
(
		
		'lang' => '@language@',		//语言文件
		'ext' => '.php',		//文件后缀
		
		/*系统信息*/
		'SYSTEM_INFO' => array(
		    'system_name' => 'Vinchin Disaster Recovery',
		    'company' => 'Vinchin ',
		    'copyright' => 'Copyright',
		    'years' => '2021',
		    'vendor' => '@VENDOR@',                            //软件内左下角标注
			'arch_type' => '@ARCHTYPE@',                      //底层架构 x86/arm
		    'os_type' => '@OSTYPE@',                          //操作系统 centos7/kylin10
		    'version' => '@version@',                         //软件版本号
		    'enterprise' => '@enterprise@',                   //软件版本    
		   	'recommend' => '推荐使用Chrome 39+、Firefox 19+及其以上版本的浏览器。推荐分辨率为1440*900或者更高', //推荐浏览器和分辨率提示 
		    //授权处使用
		    'company_name' => '成都云祺科技有限公司',               
		    'company_tel' => '+86 400-9955-698',
		    'company_email' => 'support@vinchin.com',
		),
    
        //远程地址
        'REMOTE' => array(
            'website' => "https://www.vinchin.com",
            'api_url' => "https://www.vinchin.com:5786",         //api地址
//             'api_url' => "https://www.vinchin.com:5786/",         //api地址
            'api_user' => "product",                   //api用户
            'api_pass' => "yunqi123456789",            //api密码
            'ueiplan' => "http://www.vinchin.com/ueiplan.php",  //用户体验改善计划
            'privacy' => "http://www.vinchin.com/privacy.php",  //隐私声明
            'release' => "http://www.vinchin.com/release.php",  //版本发布记录
        ),
    
        //软件版本
        'ENTERPRISE' => array(
            /**
             * 这里是打包的版本
             * 英文版本会有1个包,通过授权文件来区分不同版本
             * 中文版会有2个包,一个标准版,一个企业版.
             */
            'standard' => 'vinchin_standard',
            'enterprise' => 'vinchin_enterprise',
            'enterprise_en' => 'vinchin_enterprise_en',
        ),
        
        //api模块号,对应云端模块号
        'API_MODULE' => array(
            'Xemail' => 0,          //Xemail接口
            'Feedback' => 1,       //提交反馈接口
            'Sms' => 2,            //短信接口
            'Ueiplan' => 3,        //用户体验改善计划
            'Upgrade' => 4,        //系统升级
        ),
    
		/*数据库信息*/
        'DB_INFO' => array(
            'dbtype' => 'mysql',
            'host' => 'localhost',
            'user' => 'vinchin',
            'pass' => 'Database@2015',
            'port' => '3306',
            'dbname' => 'vinchin_db',
        ),
    
        'EMAIL' => array(
            'smpt_host' => 'smtp.exmail.qq.com',
            'from_email' => 'product@vinchin.com',
            'from_email_pass' => 'vinchin168',
            'port' => 465,
            'authentication' => true,
            'type' => 'HTML',
        ),
		
		/*socket info*/
        'SOCKET_INFO' => array(
            'IP' => 'localhost',
            'Port' => 22710,
            'recvByte' => 272,
            'recTimeout' => 900,
            'sendTimeout' => 900,
            'agentTimeout' => 30,
            
            'BdHeader' => array(
                'magic_number' => 0xfafbfcfd,
                'sequence_number' => 0,
            ),
            
            'BdRouterHeader' => array(
                'source_type' => 1,
                'message_level1_type' => 3,
                'message_level2_type' => 0,
            ),
            
            'BdLogRouterHeader' => array(
                'message_level1_type' => 4,
                'task_log' => 1,
                'system_log' => 2,
            ),
            
            'BdTargetConnLostRouterHeader' => array(
                'message_level1_type' => 1,
                'message_level2_type' => 3,
            ),
            
            'BdMessagePostion' => array(
                'module_client' => 5,
            ),
        ),
    
        /**日志信息**/
        'LOG_INFO' => array(
            //是否开启日志,开启日志后不写入INFO,NOTICE,WARNING日志,ERROR日志会照常写入
            'DEBUG' => false,
            //日志路径
            'PATH' => '/var/log/@VENDOR@/web/log',
            //日志等级  
            'INFO' => 1,
            'NOTICE' => 2,
            'WARNING' => 3,
            'ERROR' => 4,
        ),
        
        /*autoload path*/
        'APP_AUTOLOAD_PATH' => array(
            'xphp/libs/',
            'xphp/utils/',
            'app/db/',          //数据库实时
            'app/dbprotect/',   //数据库定时
            'app/file/',
            'app/platform/',
            'app/vm/',
			'app/api/',
            'amq/',
        	'monitor/',
            'dbcdp_monitor/',
            'billing_monitor/',
            'systembak_monitor/',
        ),
    
        /*api 方法*/
        'API' => array(
            'access_token' => 'APIAccessTokenHandler',  //认证token
            'jobs' => 'APIJobsHandler',                 //任务
            'vcenters' => 'APIVcentersHandler',         //虚拟化中心
            'logs' => 'APILogsHandler',                 //日志
            'alarms' => 'APIAlarmsHandler',             //告警
            'storages' => 'APIStoragesHandler',         //存储
            'settings' => 'APISettingsHandler',         //系统配置
            'users' => 'APIUsersHandler',               //用户
            'nodes' => 'APINodesHandler',               //节点
        	'points' => 'APIBackupPointHandler',        //时间点
			'copy' => 'APICopyHandler',                 //副本
            'tenant' => 'APITenantHandler',             //租户
            'usergroups' => 'APIUserGroupsHandler',     //用户组
            'roles' => 'APIRolesHandler',               //角色
            'domainserver' => 'APIDomainServerHandler', //域服务器
            'resource' => 'APIResourceHandler',         //资源
            'billing' => 'APIBillingHandler',           //计费
        ),
    
        'API_CONFIG' => array(
            'uriHeader' => 'api',
            'acceptHeader' => 'application/vnd.vinchin',
            'acceptDataType' => 'json',
            'charset' => 'charset=utf-8',
            'timeout' => 7200
        ),
    
        /*路由信息:和JS路由列表对应，错误即拒绝访问*/
        'ROUTE' => array(
            'PlatformHandler',  //平台                0
            'Vmhandler',        //虚拟机
            'FileHandler',      //文件
            'DbHandler',        //数据库
            'UsersHandler',     //用户管理
            'JobHandler',       //任务监控          5
            'DataHandler',      //备份数据管理    
            'LogHandler',       //日志管理
            'SystemHandler',    //系统配置
            'AgentHandler',     //代理端管理
            'StorageHandler',   //存储                    10
            'AlarmHandler',     //告警
            'ArchiveHandler',   //归档
            'ReportHandler',    //报表
            'ManoeuvreHandler', //灾难演练
            'Vcenter',          //虚拟化中心         15
            'NodeHandler',      //节点管理
            'CopyHandler',      //副本管理
            'VisualHandler',    //可视化大屏
            'DbCDPHandler',     //数据库CDP   19
            'DBTimingHandler',    //数存接口类     20
            'TenantHandler',        //租户管理      21     
            'RoleHandler',          //角色管理      22
            'ResourceHandler',      //资源管理      23
            'DomainServerHandler',  //域管理          24
            'BillingHandler',       //计费管理      25
            'FileCDPHandler',       //文件CDP    26 
			'DBProtectHandler',     //数据库保护 27
			'SystemBackupHandler',   //系统备份    28
			'SettingsHandler',       //系统配置    29
        ),
        //lisence info
        'LISENCE_INFO' => array(
            'thumbprintFileName' => 'thumbprint.txt',
            'uploadfile' => array(
                'name' => 'files',
                'suffixes' => 'key',
                'size' => 204800,
                'type' => 'application/octet-stream'
            ),
            'authflag' => array(
                'authorized' => 1,      //已授权
                'unauthorized' => 2,    //未授权
                'expire' => 3,          //授权过期
                'invalid' => 4,         //授权异常
            ),
            //授权方式
            'type' => array(
                'host' => 1,
                'cpu' => 2,
                'storage' => 3,
                'vm' => 4,
            ),
            //license文件类型
            'filetype' => array(
                'license' => 1,
                'service' => 2,
            ),
            //服务授权文件路径
            'serviceFilePath' => "/opt/@VENDOR@/vinsc.service",  //这里取名service,有混淆的意思.
            //服务类型
            'servertype' => array(
                '---',
                '软件标准服务',
                '软件白金服务',
            )
        ),
        //LOGO info
        'LOGO_INFO' => array(
            'uploadfile' => array(
                'name' => 'files',
                'suffixes' => 'jpeg|jpg|gif|png',
                'size' => 1048576,
            ),
        ),
        //导入数据库文件
        'IMPORT_INFO' => array(
            'uploadfile' => array(
                'name' => 'files',
                'suffixes' => 'zip',
                'size' => 1048576000,
            ),
        ),
        //节点软件包
        'NODE_SOFT_INFO' => array(
            'uploadfile' => array(
                'name' => 'files',
                'suffixes' => 'tar|gz',
                'size' => 104857600,
            ),
        ),
        
        //节点类型定义
        'NODETYPE' => array(
            'UNKNOWN' => 0,
            'MASTER' => 1,      //主节点
            'BACKUP' => 2       //备份节点
        ),
    
        //导出文件密码
        'ZIP_PASS' => 'Backup@4R',
        
        //网卡信息
        'NETWORKCARD' => array(
            'path' => '/etc/sysconfig/network-scripts/',
            'prefix' => 'ifcfg-',
        ),
    
        //tmp path
        'TMP_PATH' => '/usr/share/nginx/@VENDOR@/tmp/',
    
    
        'TMP_PATH_LOGO' => '/usr/share/nginx/@VENDOR@/tmp/logo.png',
    
        //tmp path 相对
        'TMP_PATH_RE' => '/tmp/',
        'TMP_PATH_RE_LOG' => '/tmp/logo.png',
    
        //logo path 
        'LOGO_PATH' => '/usr/share/nginx/@VENDOR@/img/platform/logo.png',
    
        //node soft
//         'NODE_SOFT' => '/usr/share/nginx/vinchin/tmp/node_soft/', 
        'NODE_SOFT' => '/etc/@VENDOR@/web/node_soft/',
        'NODE_SOFT_RE' => '/tmp/node_soft/',
        
        //agent path
        'AGENT_PATH' => '/usr/share/nginx/@VENDOR@/agent',
        //upload path
        'UPLOAD_PATH' => '/usr/share/nginx/@VENDOR@/tmp/upgrade/',
		'UPLOADTMP_PATH' => '/usr/share/nginx/@VENDOR@/tmp/upgradetmp/',
		
        
        
        //系统名称
        'SYSTEM_NAME_FILE' => '/etc/@VENDOR@/web/systemName.ini',
        
        //灾备中心自定义标志 true允许自定义,反之
        'DATACENTER_DIY' => true,
        
        //用户类型定义
        'USERTYPE' => array(
            "operator" => 1,
            "auditor" => 2,
            "manager" => 3,
            "administrator" => 4,
        ),
		//用户类型定义
		'USERTYPEINT' => array(
			1 => "operator",
			2 => "auditor",
			3 => "manager",
			4 => "administrator",
		),
        'FLAG' => array('SET' => 1, 'UNSET' => 2),
        
        //备份模式
        'BACKUP_MODE' => array('UNKNOWN'=>0, 'FULL'=>1, 'INCREMENTAL'=>2, 'DIFFERENTIAL'=>3, 'LOG'=>4, 'COPY'=>5, 'ARCHIVE'=>6),
        //策略类型
        'STRATEGY_TYPE' => array('UNKNOWN'=>0, 'EVERY_DAY'=>1, 'EVERY_WEEK'=>2, 'EVERY_MONTH'=>3, 'ONCE'=>4),
        //滚动类型
        'STRATEGY_ROLL_TYPE' => array('ON'=>1, 'OFF'=>2),
        //任务类型
        'TASKTYPE' => array(
            'BACKUP' => 1, 
            'RECOVERY' => 2,
            'DISK_BACKUP' => 3,
            'DISK_RECOVERY' => 4,
            'VM_FILE_BACKUP' => 5,
            'VM_FILE_RECOVERY' => 6,
            'VM_INSTANT_RECOVERY' => 7,
            'VM_INSTANT_RECOVERY_MOTION' => 8,
            'VM_REPLICATION' => 9,
            'SYNC' => 10,
            'ORCH_TASK' => 11,
            'BACKUP_EXPORT' => 12,
            'VM_CDP_BACKUP' => 13,
            'VM_CDP_RECOVERY' => 14,
            'VM_CDP_INSTANT_RECOVERY' => 15,
            'VM_CDP_INSTANT_RECOVERY_MOTION' => 16,
			'BACKUP_COPY' => 17,           //虚拟机副本
			'BACKUP_COPY_FETCH' => 18,      //虚拟机副本回传
			'ARCHIVE'=> 19,                 //虚拟机归档
			'ARCHIVE_FETCH' => 20,          //虚拟机归档回传
			'DB_CDP_BACKUP' => 21,          //数据库CDP备份
			'DB_CDP_RECOVERY' => 22,        //数据库CDP恢复
			'DB_CDP_TAKEOVER' => 23,        //数据库CDP接管
			'FILE_CDP_BACKUP' => 24,         //文件CDP备份
			'FILE_CDP_RECOVERY' => 25,      //文件CDP恢复
			'FILE_BACKUP_COPY' => 26,       //文件副本
			'FILE_BACKUP_COPY_FETCH'=> 27,  //文件副本回传
			'DB_BACKUP' => 28,              //数据库备份
			'DB_RECOVERY' => 29,            //数据库恢复
			'DB_BACKUP_COPY' => 30,         //数据库副本
			'DB_BACKUP_COPY_FETCH' => 31,   //数据库副本回传
			'VOL_CDP_BACKUP' => 32, 		//卷CDP备份
			'VOL_CDP_RECOVERY' => 33, 		//卷CDP恢复
			'VOL_CDP_TAKEOVER' => 34, 		//卷CDP接管
			'OS_BACKUP' => 35,  //OS备份
			'OS_RECOVERY' => 36,  //OS恢复
			'SURE_BACKUP' => 37,  //虚拟机数据验证
			'OS_BACKUP_COPY' => 38,  //OS副本
			'OS_BACKUP_COPY_FETCH' => 39,  //OS副本回传
			'OS_BACKUP_ARCHIVE' => 40,  //OS归档
			'OS_BACKUP_ARCHIVE_FETCH' => 41,  //OS归档回传
			'NAS_BACKUP' => 42,  //NAS备份（暂时没用）
			'NAS_RECOVERY' => 43,  //NAS恢复（暂时没用）
			'NAS_BACKUP_COPY' => 44,  //NAS副本
			'NAS_BACKUP_COPY_FETCH' => 45,  //NAS副本回传
		),
        //任务状态
        'TASKSTATUS' => array(
            'UNKNOWN' => 0,         //unknown task status
            'WAITTING' => 1,        //task is waitting for running
            'RUNNING' => 2,         //task is running
            'PAUSED' => 3,          //task is paused
            'STOPPED' => 4,         //task is stopped
            'STOPPING' => 5,        
            'NETWORK_FAULT' => 6,   //network fault
            'ABNORMAL' => 7,        //task compeleted but abnormal
            'ERROR' => 8,           //task is error
            'SYNC' => 9,			// task is sync
            'PREPARING' => 10,		// preparing
            'PAUSING' => 11,	    // pausing
            'STARTING' => 12,       //starting
            'FINISHED' => 13,       //finished
            'TAKEOVER' => 14,       //takeover
            'TAKEOVER_STARTING' => 15,       //takeover starting
            'TAKEOVER_STOPPING' => 16,       //takeover stopping
        ),
    
        //恢复位置
        'RECOVERY_POSITION' => array('ORIGINAL'=>1, 'OTHER'=>2, 'FILEDIR' => 3, 'REDIRECT' => 4, 'PDB' => 5),
        //恢复方式   立即恢复/按时间策略恢复
        'RECOVERY_TIME_TYPE' => array('IMMEDIATELY'=>1, 'STRATEGY'=>2),
        
        //模块定义
        'MODULE_TYPE' => array(
            'UNKNOWN' => 0,
            'PF' => 1,
            'VM' => 2,
            'FS' => 3,
            'DB' => 4,
            'OS' => 5,
            'VDDT_SERVER' => 6,
            'VDDT_CLIENT' => 7,
            'BACKUP_COPY_SERVER' => 8,
            'BACKUP_COPY_CLIENT' => 9,
            'PFPRIVATE' =>100,
            'NODE' => 1000,
            'OEM_DBCDP' => 10000,
            'OEM_FSCDP' => 10001,
        ),
        //虚拟机模块定义
        'VMHYPERVISORTYPE' => array(
            'VM_HYPERVISOR_TYPE_UNKNOWN' => 0,
            'VM_HYPERVISOR_TYPE_VMWARE' => 1,
            'VM_HYPERVISOR_TYPE_HYPERV' => 2,
            'VM_HYPERVISOR_TYPE_XENSERVER' => 3,
            'VM_HYPERVISOR_TYPE_KVM' => 4,
            'VM_HYPERVISOR_TYPE_XEN' => 5,      //不支持20210304
            'VM_HYPERVISOR_TYPE_ORACLEVM' => 6, //不支持20210304
            
            ///////////////////////// OEM VMWARE //////////////////////////
            'VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM' => 7,	// shu guang, sugon
            
            ///////////////////////// OEM XENSERVER ///////////////////////
            'VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE' => 8,	// lang chao, Inspur
            'VM_HYPERVISOR_TYPE_HALSIGN_VGATE' => 9,			// halsign
            
            ///////////////////////// OEM KVM /////////////////////////////
            'VM_HYPERVISOR_TYPE_NEOKYLIN_KVM' => 10,			// NeoKylin kvm
            'VM_HYPERVISOR_TYPE_H3C_KVM' => 11,					// h3c kvm
            'VM_HYPERVISOR_TYPE_SANGFOR_KVM' => 12,				// sangfor kvm
            'VM_HYPERVISOR_TYPE_SDC_OS_KVM' => 13,				// dianke lingyun sdcos ,不支持20210304
            'VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM' => 14,			// flex cloud kvm
            'VM_HYPERVISOR_TYPE_OPENSTACK_KVM' => 15,			// open stack kvm
            
            'VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM' => 16,// huawei fusion sphere kvm
            'VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN' => 17,// huawei fusion sphere xen
            'VM_HYPERVISOR_TYPE_WINHONG_WINSERVER' => 18,		//  Winhong WinServer, XenServer OEM
            'VM_HYPERVISOR_TYPE_RHV_KVM' => 19,              // redhat rhv
            ///////////////////////// OEM XENSERVER //////////////////////////
            'VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER' => 20,		// dongchen d server
            
        	///////////////////////// OEM VMWARE //////////////////////////
        	'VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE' => 21,	// cloud view svm, Vmware OEM
        	
        	'VM_HYPERVISOR_TYPE_FLEX_HCS_KVM' => 22,			// flex hci kvm
        	
        	'VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER' => 23,				// wuhan os easy v-server
        	'VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM' => 24,		// inspur Incloud shpere 5.X kvm
            'VM_HYPERVISOR_TYPE_DIY_WINGHONG' => 25,			        // DIY winhong
            'VM_HYPERVISOR_TYPE_ZSTACK' => 26,					// zstack
            'VM_HYPERVISOR_TYPE_EASTED_VSERVER' => 27,			// Easted vserser, based on ovirt
            'VM_HYPERVISOR_TYPE_XCP_NG' => 28,					// XCP-ng
            'VM_HYPERVISOR_TYPE_OLVM' => 29,					// Oracle Linux Virtualization Manager(OLVM)
            'VM_HYPERVISOR_TYPE_XSKY' => 30,                  //XSKY
            'VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK' => 31,	// lang chao, Inspur openstack
			'VM_HYPERVISOR_TYPE_INSPUR_VVDK' => 40,	// Inspur VVDK
			'VM_HYPERVISOR_TYPE_KVM_ZVIRT' => 41,              // zVirt, based on ovirt
        ),
        //虚拟化分组,同一个分组可以相互恢复
        'VMHYPERVISORGROUP' => array(
			//VMware分组
			'vmware' => array(
				1, 7, 21
			),
			//XenServer分组
			'xenserver' => array(
				3, 8, 9, 18, 20, 25, 28
			),
			//KVM分组
//             'kvm' => array(
//               4, 10, 11, 12, 13, 14, 15, 19, 29
//             ),

			//huawei
			'huawei' => array(
				17
			),
			//私有云
			'openstack'=> array(
				14, 15, 22, 31, 34, 35, 36, 37, 38, 39
			),
			'redhat' => array(
				19, 29, 41, 45, 49, 50
			),
			//公有云
			'publiccloud' => array(
				100, 101
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
            'Red Hat Virtualization(RHV)/oVirt',
            'D-Server',
        	'SVM CloudVirtual',
        	'Flex HCS',
        	'Os Easy V-server',
        	'InCloud Sphere KVM',
            'V-Server',
            'ZStack Cloud',
            'Easted vServer',
            'XCP-ng',
            'Oracle Linux Virtualization Manager(OLVM)',
            'XSKY XECCP',
            'InCloud OpenStack',
        ),
        //针对OEM出厂未授权显示虚拟化,授权后读取授权文件的虚拟化，如果为空不处理
        'RELEASE_HYPERVISOR' => array(),
        //vm task level
        'VmTaskLevel' => array(
            'UNKNOWN' => 0,		        //unknown level
        	'VM' => 1,				    //backup or recover entire vm
        	'FILE' => 2,			    //backup or recovery file in vm
        	'INSTANT_RECOVERY' => 3,	//instant recovery task
        	'DATA_DISK' => 4,		    //backup or recovery disk
        ),
        //模块操作码文件定义
        'MODULE_OPCODE_TYPE' => array(
            'UNKNOWN',
            'PFOpcode',
            'VMOpcode',
            'FSOpcode',
            'DBProtectOpcode',
            //             'DBOpcode',
            'OSOpcode',
            'VMOpcode',
            'VMOpcode',
            'VMOpcode',
            'VMOpcode',
            100 => 'PFOpcodePrivate',
            1000 => 'NodeOpcode',
        ),
        //vm tree display mode
        'VM_TREE_DISPLAY_MODE' => array(
            'UNKNOWN' => 0,
            'HOST_AND_CLUSTER' => 1,
            'VM_AND_TEMPLATE' => 2,
            'HOST_AND_VM' => 3,
        ),
        //vm tree node type
        'VM_TREE_TYPE' => array(
            'UNKNOWN' => 0,
            'FOLDER' => 1,
            'DATACENTER' => 2,
            'CLUSTER' => 3,
            'HOST' => 4,
            'POOL' => 5,
            'VAPP' => 6,
            'VM' => 7,
            'OVIRT_FAKE_HOST_FOLDER' => 8,
            'OVIRT_FAKE_VM_FOLDER' => 9,
            'OVIRT_CLUSTER_FOLDER' => 10,
            'OVIRT_CLUSTER' => 11,
            'OVIRT_DATACENTER' => 12,
        ),
        //存储类型
        'BD_STORAGE_TYPE' => array(
            'UNKNOWN' => 0,
            'DISK' => 1,        //local disk
            'LVM' => 2,         //lvm
            'PARTITION' => 3,   //local partition
            'FC' => 4,          //storage area network FC
            'ISCSI' => 5,       //storage area network iSCSI
            'NFS' => 6,         //network attached storage NFS
            'CIFS' => 7,        //network attached storage CIFS
            'REMOTE' => 8,		//storage type remote system
            'CLOUD' => 9,		//cloud type storage
            'TAPE' => 10,		//tape storage
            'LOCALDIR' => 11,   //Local dir
            
        ),
        //存储用途
        'BD_STORAGE_USE_MODE' => array(
            'UNKNOWN' => 0,
            'BACKUP' => 1,
            'COPY' => 2,
            'ARCHIVE' => 3,
        ),
        //分区类型
        'BD_PARTITION_TYPE' => array(
            'UNKNOWN' => 0,
            'DISK' => 1,
            'FC' => 2,
            'ISCSI' => 3,
        ),
        //存储状态
        'STORAGE_STATUS' => array(
            'UNKNOWN' => 0,
            'ONLINE' => 1,
            'CREATING' => 2,
            'OFFLINE' => 3,
            'UNMOUNT' => 4,
        	'WARNING' => 5
        ),
        //消息模式  同步/异步
        'MSG_MODE_TYPE' => array(
            'UNKNOWN' => 0,
            'SYNC' => 1,
            'RSYNC' => 2,
        ),
        //操作类型  
        "OPCODE_LEVEL" => array(
            'UNKNOWN' => 0,
            'PUBLIC' => 1,
            'PRIVATE' => 2,
        ),
        //操作状态
        "OP_STATUS" => array(
            'UNKNOWN' => 0,
            'RUNNING' => 1,
            'SUCCEED' => 2,
            'FAILURE' => 3,
        ),
        //宿主机状态
        'HOSTSTATUS' => array(
            'UNKNOWN' => 0,
            'CONNECT' => 1,
            'DISCONNECT' => 2,
        ),
        //虚拟机连接状态
        'VmMachineConnectState' => array(
            'UNKNOWN' => 0,
            'CONNECTED' => 1,
            'DISCONNECTED' => 2,
            'COMPLETED' => 3,
        ),
        //虚拟机状态VmMachineStatus
        'MACHINESTATUS' => array(
            'UNKNOWN' => 0,
            'POWEREDOFF' => 1,
            'POWEREDON' => 2,
            'SUSPENDED' => 3,
            'PAUSE' => 4,
        ),
        'VMDATASTORESTATUS' => array(
            'UNKNOWN' => 0,
            'CONNECT' => 1,
            'DISCONNECT' => 2,
        ),
        //日志类型
        'LOGTYPE' => array(
            'UNKNOWN' => 0,
            'TASK' => 1,
            'SYSTEM' => 2,
        ),
        //日志等级
        'LOGLEVEL' => array(
            'UNKNOWN' => 0,
            'NORMAL' => 1,
            'WARN' => 2,
            'ERROR' => 3,
        ),
    
        //通知方式
        'NOTICE_MODE' => array(
            'UNKNOWN' => 0,
            'EMIAL' => 1,       //邮件通知
            'SMS' => 2,         //短信通知
        ),
        //通知类型
        'NOTICE_TYPE' => array(
            'UNKNOWN' => 0,
            'TASK' => 1,        //任务通知
            'SYSTEM' => 2,      //系统通知
        ),
        //短信配置
        'SMS_CONFIG' => array(
            //发送类型
            'SEND_TYPE' => array(
                'UNKNOWN' => 0,
                'INTERNET' => 1,   //短信平台发送
                'MODEM' => 2,      //短信猫发送
            ),
            //短信猫签名
            'SIGNATURE' => '云祺科技',
            //发送结果查询睡眠时间,插入数据库后要不停检查是否发送成功,单位 S
            'SLEEPTIME' => 5,
            //发送结果查询次数
            'CHECKTIME' => 10,
        ),
        
        //接口魔术
        'API_MAGIC' => '6e24cc40bfdb6963c04a4f1983c8af71',
    
        //加密密钥
        'SECRET_KEY' => '0e55cf8bfba0fdf4a53353cd0d7e282e727a3c1e504145164ae97aa95542ca9a',
    
        //平台加密密钥
        'SECRET_KEY_PT' => 'dmluY2hpbjEyMzQ1Njc4c2t5',
        
        //时间占位符
        'TIMESPACE' => "----",
        //空占位符
        'NULLSPACE' => "--", 
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
		//副本历史任务状态
		'CopyTaskStatus' => array(
			'UNKNOWN' => 0,
			'SUCCESS' => 1,
			'ERROR' => 2,
			'PAUSE' => 3,
			'WAITING' => 4,
		),
        'XENSERVER_BACKUP_LEVEL' => array(
            'UNKNOWN' => 0,         // XenServer backup level unknown
            'NORMAL' => 1,          // XenServer backup level normal
            'KEEP_SNAPSHOT' => 2,   // XenServer backup level KEEP_SNAPSHOT
        ),
        //代理端状态
        'AGENTSTATUS' => array(
            'UNKNOWN' => 0,
            'ONLINEREGISTER' => 1,      //在线 注册
            'ONLINEUNREGISTERED' => 2,  //在线 未注册
            'OFFLINEREGISTER' => 3,     //离线 注册
            'OFFLINEUNREGISTERED' => 4, //离线 未注册
        ),
        //文件类型
        'FILETYPE' => array(
            'UNKNOWN' => 0,     // unknown type
            'FILE' => 1,        // file type
            'DIRECTORY' => 2,   // directory type
            'FIX_DRIVER' => 3,  // drive type, flash drive or hard disk
            'REMOVABLE' => 4,   // removabal drive, usb key and so on
            'REMOTE' => 5,      // remote drive
        ),
        //告警类型
        'ALARM' => array(
            'notice' => 1,  //提示错误
            'general' => 2, //一般错误
            'serious' => 3, //严重错误
            'fatal' => 4,   //致命错误
        ),
        //备份时间点展示方式
        'POINTSHOWTYPE' => array(
            'UNKNOWN' => 0,
            'VMGROUP' => 1,
            'TIMEGROUP' => 2,
        ),
        //数据库类型
        'DBTYPE' => array(
            'UNKNOWN' => 0,		    // sub module type unknown
            'MSSQL' => 1,			// ms sql server
            'ORACLE' => 2,			// oracle
            'MYSQL' => 3,			// mysql
            'SYBASE' => 4,			// sybase
        ),
        //mssql类型
        'MSSQLTYPE' => array(
            'UNKNOWN' => 0,	        // version type unknown
            '2000' => 1,			// sql server 2000
            '2005' => 2,			// sql server 2005
            '2008' => 3,			// sql server 2008
            '2008_R2' => 4,		    // sql server 2008 R2
            '2012' => 5,			// sql server 2012
            '2014' => 6,			// sql server 2014
            '2016' => 7,			// sql server 2016
        ),
        //oracle类型
        'ORACLETYPE' => array(
            'UNKNOWN' => 0,	        // oralce version type unknown
            '9I' => 1,			    // oracle 9i
            '10G' => 2,			    // oracle 10g
            '10G_R2' => 3,		    // oracle 10g R2
            '11G' => 4,			    // oracle 11g
            '11G_R2' => 5,		    // oracle 11g R2
            '12C' => 6,			    // oracle 12c
        ),
        //预案类型
        'PLANTYPE' => array(
            'PLAN' => 1,            //总预案
            'GROUP' => 2,           //分组预案
            'CHILD' => 3,           //子预案
        ),
        //NTP服务器
        'NTP_SERVERS_CONF' => '/etc/@VENDOR@/web/ntpConf.ini',
        'NTP_SERVERS_FILE' => '/etc/ntp.conf',
        //预案恢复类型
        'RECOVERY_MODE' => array(
            'RECOVERY' => 1,        //恢复
            'INSTANT' =>2           //瞬时恢复
        ),
        //演练代理状态
        'PROXY_STATUS' => array(
            'UNKNOWN' => 0,
            'DEPLOYING' => 1,   //部署中
            'ONLINE' => 2,      //正常
            'OFFLINE' => 3,     //离线
            'ABNORMAL' => 4,    //异常,正常出问题的时候
            'ERROR' => 5,       //错误,只在部署中出错展示
        ),
        //演练类型
        'VERIFY_TYPE' => array(
            'UNKNOWN' => 0,
            'IP' => 1,     
            'WEB' => 2,
        ),
        //宿主机授权状态
        'HOST_LISENCE' => array(
            'UNKNOWN' => 0,
            'ALL' => 1,     //全部授权
            'PART' => 2,    //部分授权
            'NONE' => 3     //未授权
        ),
        // data center state type
        'VmDataCenterStateType' => array(
            'UNKNOWN' => 0,
            'UP' => 1,          //在线
            'DOWN' => 2,        //离线
            'UNINIT' => 3       //未初始化
        ),
        //后台系统日志
        'LOG_PATH' => array(
            'fs_server' => '/var/log/@VENDOR@/fs_server',
            'node_server' => '/var/log/@VENDOR@/node_server',
            'pt_server' => '/var/log/@VENDOR@/pt_server',
            'rt_server' => '/var/log/@VENDOR@/rt_server',
            'backup_server' => '/var/log/@VENDOR@/backup_server',
            'fc_server' => '/var/log/@VENDOR@/fc_server',
            'vinfs' => '/var/log/@VENDOR@/vinfs',
            'vm_server' => '/var/log/@VENDOR@/vm_server',
            'vxefs' => '/var/log/@VENDOR@/vxefs',
            'fcvinfs' => '/var/log/@VENDOR@/fcvinfs',
            'kvinfs' => '/var/log/@VENDOR@/kvinfs',
            'osvinfs' => '/var/log/@VENDOR@/osvinfs',
            'web' => '/var/log/@VENDOR@/web',
            'backup_copy_server' => '/var/log/@VENDOR@/backup_copy_server',
            'backup_copy_client' => '/var/log/@VENDOR@/backup_copy_client',
            'rdm_server' => '/var/log/@VENDOR@/rdm_server',
            'appliance_server' => '/var/log/@VENDOR@/appliance_server',
            'backup_client' => '/var/log/@VENDOR@/backup_client',
            'log_server' => '/var/log/@VENDOR@/log_server',
            'progress_server' => '/var/log/@VENDOR@/progress_server',
            'vmware_server' => '/var/log/@VENDOR@/vmware_server',
            'db_server' => '/var/log/@VENDOR@/db_server',
            's3_server' => '/var/log/@VENDOR@/s3_server',
            'blob_server' => '/var/log/@VENDOR@/blob_server',
            'cos_server' => '/var/log/@VENDOR@/cos_server',
            'oss_server' => '/var/log/@VENDOR@/oss_server',
            'obs_server' => '/var/log/@VENDOR@/obs_server',
            'ceph_s3_server' => '/var/log/@VENDOR@/ceph_s3_server'
        ),
        //lisence类型
        'TRIAL_TYPE' => array(
            'UNKNOWN' => 0,
            'TRIAL' => 1,           //试用授权
            'NORMAL' => 2,          //正式授权
        ),
        //软件版本定义,bd_license, software_type字段
        'SOFTWARE_VERSION' => array(
            'UNKNOWN' => 0,
            'STANDARD' => 1,                //标准版*************
            'ENTERPRISE' => 2,              //企业版*************
            'ADVANCE_ENTERPRISE' => 3,      //企业增强版-
            'EN_FREE_EDITION' => 4,         //免费版(英文)*********
            'ESSENTIAL' => 5,               //基础版-
            'STANDARD_EN' => 6,             //标准版(英文)*********
            'ENTERPRISE_EN' => 7,           //企业版(英文)*********
            'ADVANCE_ENTERPRISE_EN' => 8,   //企业增强版(英文)-
            'ESSENTIAL_EN' => 9,            //基础版(英文)*********
            'FREE_EDITION' => 10,            //免费版-
            'ENTERPRISE_EN_LR' => 11,           //企业版(英文)促销*********
        ),
		
		//存储块大小数组
		'BLOCK_SIZE' => array(64, 128, 256, 512, 1024, 2048),
		
		//传输模式
		'TRANSPORT_MODE' => array(
			"VMWARE_NBD" => 1,
			"VMWARE_SAN" => 2,
			"XEN_NBD" => 3,
			"XEN_SAN" => 4
		),
    
        //华为kvm传输模式
        'HUAWEI_KVM_MODE' => array(
            'UNKNOWN' => 0,
            'TCP_RPC' => 1,					// kvm transport mode rpc
            'TCP_LANFREE' => 2,				// kvm transport mode lanfree + TCP RPC(lanfree failed will change to TCP RPC), current version only supported ovirt based
            'APPLIANCE' => 3,				// kvm transport node appliance
            'LANFREE_APPLIANCE' => 4,		// kvm transport node appliance
            'API_LAN' => 5,					// kvm transport mode API LAN/NBD
            'API_LAN_FREE' => 6,			// kvm transport mode API LAN
        ),
    
		
		//高速模式
		'HIGH_MODE' => array(
			"NORMAL" => 1,
			"HIGH" => 2,
			"CBT" => 3,
		),
    
        //中间件
        'MIDDLEWARE' => array(
            'UNKNOWN' => 0,
            'ACTIVEMQ' => 1,
        ),

        //ActiveMQ消息推送模式
        'ACTIVEMQ_MODE' => array(
            'queue' => 1,
            'topic' => 2
        ),
    
        //推送消息前缀
        'MQ_MSG_NAME' => array(
            'job' => array(
                'backup' => "Vinchin.Job.Backup.",
                'recovery' => "Vinchin.Job.Recovery.",
                'instant' => "Vinchin.Job.Instant.",
                'motion' => "Vinchin.Job.Motion.",
            ),
            'alarm' => array(
                'job' => "Vinchin.Alarm.Job",
                'system' => "Vinchin.Alarm.System"
            )
        ),
		
		//推送参数值
		'MQ_VALUE' => array(
			'PUSHSTOP' => 30,
			'JOBSTOP' => 3,
			'ALARMSTOP' => 10,
			'LISTNUM' => 100
		),

		//推送协议
		'MQPROTOCOL' => array(
			'UNKNOWN' => 0,
            'STOMP' => 1,
            'OPENWIRE' => 2
		),

		//推送类型
        'MESSAGETYPE' => array(
            'JOB.BACKUP' => 1,
            'JOB.RECOVERY' => 2,
            'JOB.INSTANT' => 3,
            'JOB.MOTION' => 4,
            'ALARM.JOB' => 5,
            'ALARM.SYSTEM' => 6,
        ),
    
        //推送消息统一名字,提供两种模式的消息
        'MESSAGENAME' => 'VINCHIN.EVENTSERVICE.EVENT',
        //推送消息统一文件名标志
        'MESSAGEUMFLAG' => 'UMFLAG', 

        //存储告警类型
        'STORAGEWARNINGTYPE' => array(
            'UNKNOWN' => 0,
            'PERCENT' => 1,
            'SIZE' => 2
        ),
        //邮件加密类型
        'EMAIL_ENCRYPTION_TYPE' => array(
            '',
            'ssl',
            'tls'
        ),
        //操作系统类型
        'OS_TYPE' => array(
            'UNKNOWN' => 0,
            'Linux' => 1,
            'Windows' => 2,
            'FreeBSD' => 3,
            'NetBSD' => 4,
            'OpenBSD' => 5,
            'Hurd' => 6,
            'Dos' => 7,
            'Minix' => 8,
        ),
    
        //文件系统类型
        'FILE_SYSTEM_TYPE' => array(
            'UNKNOWN' => 0,
            'XFS' => 1,
            'EXT2' => 2,
            'EXT3' => 3,
            'EXT4' => 4,
            'NTFS' => 5,
            'HFS' => 6,
            'ZFS' => 7,
            'SWAP' => 8,
            'FAT32' => 9
        ),
    
        //存储告警类型
        'STORAGEWARNINGTYPE' => array(
            'UNKNOWN' => 0,
            'PERCENT' => 1,
            'SIZE' => 2
        ),
        //邮件加密类型
        'EMAIL_ENCRYPTION_TYPE' => array(
            '',
            'ssl',
            'tls'
        ),
        
        //查询备份时间点参数
        'SEARCH_TIMEPOINT' => array(
            'VM_UUID' => 1,
            'VM_NAME' => 2,
            'TASK_UUID' => 3,
            'TASK_NAME' => 4,
            'VCENTER_UUID' => 5
        ),
        //查询当前任务列表参数
        'SEARCH_CURRENT_JOBS' => array(
            'TASK_NAME' => 1,
            'TASK_TYPE' => 2,
            'TASK_STATUS' => 3,
            'VM_NAME' => 4,
            'VM_UUID' => 5,
            'INSTANT_VM_NAME' => 6,
            'INSTANT_VM_UUID' => 7
        ),
        //查询历史任务列表参数
        'SEARCH_HISTORY_JOBS' => array(
            'TASK_NAME' => 1,
            'TASK_TYPE' => 2,
            'VM_NAME' => 3,
            'VM_UUID' => 4,
            'START_TIME' => 5,
            'FINISH_TIME' => 6
        ),
        //删除时间点类型
        'DELETETIMEPOINT' => array(
            'VM_ALL_TIMEPOINT' => 1,
            'TIMEPOINT_LIST' => 2
        ),
        //删除历史任务类型
        'DELETE_HISTORY' => array(
            'HISTORY_ID' => 1,
            'HISTORY_TASK_UUID' => 2,
            'HISTORY_TASK_NAME' => 3
        ),
        
        //瞬时恢复虚拟机创建/删除标志
        'INSTANT_VM_FLAG' => array(
            'UNKNOWN' => 0,
            'CREATE' => 1,
            'DELETE' => 2,
        ),
    
        //特殊配置文件地址，主要用作第三方产品配置信息
        'SPECIAL_DIR' => '/usr/share/nginx/@VENDOR@/special/',
        'SPECIAL_CONFIG' => 'config.txt',
    	
        //异地副本时间点状态
        'COPY_POINT_STATUS' => array(
        	'UNKNOWN' => 0,
        	'AVAILABLE' => 1,
        	'NOTEXIST' => 2,
        	'MERGING' => 3,
        	'INUSE' => 4,
        	'UNAVAILABLE' => 5,
        ),
        //细粒度恢复文件块大小 8MB
        'GRAIN_FILE_BLOCK_SIZE' => 8388608, 
		
		//版本划分虚拟化
		"NO_SUPPORT_HYPERVISOR" => array(
			'STANDARD' => array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23, 24,25,26,41),    //标准版*************
			'ENTERPRISE' =>  array(),              													//企业版*************
			'EN_FREE_EDITION' =>  array(8, 28, 14, 15, 16, 17, 24),         							//免费版(英文)*********
			'STANDARD_EN' =>  array(8, 14, 15, 16, 17, 24),             							//标准版(英文)*********
			'ENTERPRISE_EN' =>  array() ,           												//企业版(英文)*********
			'ESSENTIAL_EN' =>  array(3, 8, 12, 14, 15, 16, 17, 19, 24, 41),            				//基础版(英文)*********
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
		
		'VISUAL_CONF' => '/etc/@VENDOR@/web/visualConf.ini',
    
        /***↓数据库CDP配置***/
        
        //数据库类型
        'DB_CDP_VENDOR' => array(
            'SQLSERVER' => 0,
            'DB2' => 1,
            'ORACLE' => 2,
            'SYBASE' => 3,
            'HOTFILES' => 4,
            'MYSQL' => 5,
            'INTERBASE' => 6,
            'INFORMIX' => 7,
            'RENDAJINCANG' => 8,
            'SHENTONG' => 9,
            'DAMENG' => 10,
            'EXCHANGE' => 11,
            'LOTUSDOMINO' => 12,
        ),
        
        //消息定义
        'DB_CDP_SYSCODE' => array(
            'producthost' => 257,   //主机消息
            'standbyhost' => 258,   //备机消息
            'takeover' => 1025,     //接管消息
            'fileproduct' => 513,   //文件主站
            'filestandby' => 514,   //文件从站
        ),
    
        //主机类型
        'DB_CDP_HOST_TYPE' => array(
            'product' => 1,         //业务主机
            'standby' => 2          //备份主机
        ),
        
        //主机在线状态
        'DB_CDP_HOST_STATUS' => array(
            'ONLINE' => 1,      //在线
            'OFFLINE' => 2      //离线
        ),
        
        //备份/恢复 /接管系统启动状态
        'DB_CDP_HOST_START_STATUS' => array(
            'PREPARE' => 0,     //就绪
            'STARTED' => 1,     //启动
            'STARTING' => 2,    //启动中(停止无)
            'ERROR' => 3,       //启动错误(停止无)
            'WAITING' => 255,   //系统等待初始化
        ),
    
        //操作系统服务状态
        'DB_CDP_SERVICE_STATUS' => array(
            'STOPPED',          //停止
            'START_PENDING',    //启动中
            'STOP_PENDING',     //停止中
            'RUNNING',          //运行中
            'CONTINUE_PENDING', //继续中
            'PAUSE_PENDING',    //暂停中
            'PAUSED',           //暂停
        ),
    
        //操作系统类型
        'DB_CDP_OS_TYPE' => array(
            'UNKNOWN' => 0,
            'WINDOWS' => 1,
            'LINUX' => 2,
            'UNIX' => 3,
            'AIX' => 4,
            'HPUX' => 5,
            'SUNOS' => 6,
        ),
    
        //数据库备份类型
        'DB_CDP_BACKUP_TYPE' => array(
            'REALTIME_BACKUP' => 0,         //实时备份
            'TAKEOVER' => 2,                //业务接管
            'REALTIME_AND_TAKEOVER' => 3    //实时备份+业务接管
        ),
    
        //备份单个数据库状态
        'DB_CDP_BACKUP_DB_STATUS' => array(
            'WAITING' => 0,                 //等待
            'CHECKING' => 1,                //检查
            'PREPARATION' => 2,             //准备
            'SYNCING' => 3,                 //同步
            'SYNCOVER' => 4,                //同步完成
            'MONITOR' => 5,                 //监控
            'STOPPED' => 6,                 //停止
            'WAITNEXT' => 7,                //等待下次连接中
            'CONNING' => 8,                 //下次备份连接中
        ),
    
        //恢复workid,判断恢复状态 01342
        'DB_CDP_RECOVERY_WORDID' => array(
            'NOTSTARTED' => 0,              //未开始
            'CHECKING' => 1,                //检查中
            'FINISHED' => 2,                //完成
            'TRANSMITTING' => 3,            //传输
            'UNWRITE' => 4,                 //写入
        ),
    
        //接管状态 启动状态
        'DB_CDP_TAKEOVER_START_STATUS' => array(
//             'UNWORK' => 0,                  //没有开始工作
            'S_PREPARAM' => 1,                //正在准备参数
            'S_PREPARAM_AUTO' => 2,           //正在准备自动接管参数
            'S_WAITCHKIF' => 3,               //正在等待和检查自动接管条件
            'S_STARTING' => 4,                //正在启动
            'S_STOPHOSTNAME' => 5,            //停止接管主机名
            'S_STOPHOSTIP' => 6,             //停止接管主站IP
            'S_STOPCFGTASK' => 7,            //停止接管配置的任务(服务)
            'S_HOSTNAME' => 8,                //启动接管主机名
            'S_HOSTIP' => 9,                  //启动接管主站IP
            'S_HNAMEBINDIP' => 10,             //启动接管主机名BINDIP
            'S_CFGTASK' => 11,                 //启动接管配置的任务(服务)
            'S_TASKOK' => 12,                 //启动接管流程完成
            'S_TASKEND' => 13,                //停止接管流程完成
        ),
    
        //接管状态 停止状态
        'DB_CDP_TAKEOVER_STOP_STATUS' => array(
//             'UNWORK' => 0,                  //没有开始工作
            'P_PREPARAM' => 101,                //正在准备参数
            'P_PREPARAM_AUTO' => 102,           //正在准备自动接管参数
            'P_WAITCHKIF' => 103,               //正在等待和检查自动接管条件
            'P_STARTING' => 104,                //正在启动
            'P_STOPHOSTNAME' => 105,            //停止接管主机名
            'P_STOPHOSTIP' => 106,             //停止接管主站IP
            'P_STOPCFGTASK' => 107,            //停止接管配置的任务(服务)
            'P_HOSTNAME' => 108,                //启动接管主机名
            'P_HOSTIP' => 109,                  //启动接管主站IP
            'P_HNAMEBINDIP' => 110,             //启动接管主机名BINDIP
            'P_CFGTASK' => 111,                 //启动接管配置的任务(服务)
            'P_TASKOK' => 112,                 //启动接管流程完成
            'P_TASKEND' => 113,                //停止接管流程完成
        ),
    
        //主机监控睡眠时间
        'DB_CDP_HOST_MONITOR_SLEEP' => 10,
        //任务监控睡眠时间
        'DB_CDP_JOB_MONITOR_SLEEP' => 1,
        //数据库CDP授权ip地址 使用浏览器IP
        'DB_CDP_LICENSE_IP' => $_SERVER['SERVER_ADDR'],
        
        //日志大小管理方式:日志数据记录模式
        'DB_CDP_LOG_MODE' => array(
            'STEP' => 0,        //按步数
            'DISKSIZE' => 1,    //按磁盘空间大小
            'AUTODISK' => 2,    //自动按分配的磁盘自适应,磁盘有多大,就用多大
            'HOUR' => 3,        //按时间,单位小时
            'DISKHOUR' => 4,    //按指定小时和空间
        ),
    
    
        /***↑数据库CDP配置***/
    
        /***↓文件CDP配置***/
    
        //时间过滤类型
        'FILE_CDP_TIME_FILTER_TYPE' => array(
            'DAY' => 1,    //按天
            'MONTH' => 2,    //按月
            'YEAR' => 3,        //按年
        ),
        
        //时间策略类型
        'FILE_CDP_TIME_STRATEGY_TYPE' => array(
            'ALLDAY' => 0,    //按天
            'DIYTIME' => 1,    //按月
        ),
        
        //文件CDP备份任务状态，表示是不是正在传输数据
        'FILE_CDP_BACKUP_STATUS' => array(
            'IDLE' => 0,    //闲
            'BUSY' => 1,    //忙
        ),
    
        //文件下载块大小 8MB
        'FILE_CDP_DOWNLOAD_BLOCK_SIZE' => 8388608, 
    
        //恢复workid,判断恢复状态 01342
        'FILE_CDP_RECOVERY_WORDID' => array(
            'NOTSTARTED' => 0,              //未开始
            'CHECKING' => 1,                //恢复中
            'FINISHED' => 2,                //完成
        ),
        //文件CDP下载文件块大小 8MB
        'FILE_CDP_FILE_BLOCK_SIZE' => 8388608,
    
        //文件CDP备份类型
        'FILE_CDP_BACKUP_TYPE' => array(
            'REALTIME_BACKUP' => 0,         //实时备份
            'REALTIME_AND_HISTORY' => 1    //实时备份+数据回退
        ),
    
        /***↑文件CDP配置***/
    
        //数据库类型
        "DB_TYPE" => array(
            "UNKNOW" => 0,
            "SQLSERVER" => 1,
            "ORACLE" => 2,
            "MYSQL" => 3,
            "DM" => 4,
            "POSTGRE" => 5,
            "KINGBASE" => 6,
        ),
        //数据库类型描述
        "DB_TYPE_DES" => array(
            "Unknown",
            "SQL Server",
            "Oracle",
            "MySQL",
            "DM",
            "PostgreSQL",   //PostgreSQL
            "KingBase", //人大金仓
        ),
        "DB_TYPE_INDEX" => array(
            1,  //sqlserver
            2,  //oracle
            3,  //mysql
            4,   //DM
            5,  //PostgreSQL
            6,  //Kingbase
        ),
        //代理类型
        "AGENT_TYPE" => array(
            "FILE" => 0,
            "DB" => 1
        ),
        
        //服务状态
        "SERVICE_STATUS" => array(
            "" => 0,
            "enabled" => 1,
            "disabled" => 2,
            "static" => 3,
        ),
        //API接口存放第三方token目录
        "API_TOKEN_DIR" => '/etc/@VENDOR@/web/api_tokens/',
        'VM_RECOVERY_ZERO' => array(
            'UNKNOWN' => 0,
            'NOT_ZERO' => 1,
            'INCLOUD_ZERO' => 2 
        ),
    
        //文件锁文件路径
        'FILE_LOCK' => array(
            'FILE_LICENSE' => "/usr/share/nginx/@VENDOR@/file_license.lock",
            'VM_LICENSE' => "/usr/share/nginx/@VENDOR@/vm_license.lock"
        ),
    
        //资源类型
        'RESOURCE_TYPE' => array(
            'UNKNOWN' => 0,         
            'FILE_HOST' => 1,       //文件备份主机
            'APPLIANCE_VM' => 2,    //Appliance
            'VM' => 3,              //虚拟机
            'CDP_HOST' => 4,        //CDP主机
            'ORCH_HOST' => 5,       //演练主机
            'STRATEGY' => 6,        //策略组
            'NODE' => 7,            //节点
            'STORAGE' => 8,         //存储
            'DB_HOST' => 9,         //数据库定时
            'RESOURCE_GROUP' => 10,         //资源组
        ),
        //虚拟化类型树形结构，用于生成前端对应的class，以虚拟化名称来命名class,和上面VMHYPERVISORTYPE相对应
    'VIRTUALIZATIONICONCLASSNAME' =>array(
        'vm_unknow',
        'vm_vmware',
        'vm_hyperv',
        'vm_xenserver',
        'vm_kvm',
        'vm_xen',       //···5
        'vm_oraclevm',
        
        ///////////////////////// OEM VMWARE //////////////////////////
        'vm_sugon_cloud_view_srm',	// shu guang, sugon
        
        ///////////////////////// OEM XENSERVER ///////////////////////
        'vm_inspur_incloud_sphere',	// lang chao, Inspur
        'vm_halsign_vgate',			// halsign
        
        ///////////////////////// OEM KVM /////////////////////////////
        'vm_neokylin_kvm',			// NeoKylin kvm ···10
        'vm_h3c_kvm',					// h3c kvm
        'vm_sangfor_kvm',				// sangfor kvm
        'vm_sdc_os_kvm',				// dianke lingyun sdcos
        'vm_flex_cloud_kvm',			// flex cloud kvm
        'vm_openstack_kvm',			// open stack kvm ···15
        
        'vm_huawei_fusion_sphere_kvm',// huawei fusion sphere kvm
        'vm_huawei_fusion_sphere_xen',// huawei fusion sphere xen
        'vm_winhong_winserver',		//  Winhong WinServer, XenServer OEM
        'vm_rhv_kvm',              // redhat rhv
        ///////////////////////// OEM XENSERVER //////////////////////////
        'vm_dongchen_dserver',		// dongchen d server ···20
        
        ///////////////////////// OEM VMWARE //////////////////////////
        'vm_cloud_view_svm_vmware',	// cloud view svm, Vmware OEM
        
        'vm_flex_hcs_kvm',			// flex hci kvm
        
        'vm_os_easy_v_server',				// wuhan os easy v-server
        'vm_inspur_incloud_sphere_kvm',		// inspur Incloud shpere 5.X kvm
        'vm_diy_winghong',			        // DIY winhong ···25
        'vm_zstack',					// zstack
        'vm_easted_vserver',			// Easted vserser, based on ovirt
        'vm_xcp_ng',					// XCP-ng
        'vm_olvm',					// Oracle Linux Virtualization Manager(OLVM)
        'vm_xsky',                  //XSKY ···30
        'vm_inspur_incloud_sphere', //incloud openstack ···31
    ),
    
    'RESOURCE_FROM' => array(
        'UNKNOWN' => 0,
        'RESOURCE_GROUP' => 1,
        'USER' => 2,
        'USER_GROUP' => 3
    ),
    
    //副本归档submodule_type
    'COPY_ARCHIVE_SUB_TYPE' => array(
        'BACKUP_COPY_TYPE_UNKNOWN' => 0,
        'BACKUP_COPY_TYPE_VM_BACKUP_COPY' => 1,					//vm backup copy
        'BACKUP_COPY_TYPE_VM_ARCHIVE' => 2,						//vm archive
        'BACKUP_COPY_TYPE_FS_BACKUP_COPY' => 3,					//fs backup copy
        'BACKUP_COPY_TYPE_DB_BACKUP_COPY' => 4,					//db backup copy
        'BACKUP_COPY_TYPE_SYSTEM_BACKUP_COPY' => 5,				//system backup copy
        
    ),
    
    //远程控制路径配置
    'UPLOAD_DIR' => '/usr/share/nginx/@VENDOR@/tmp/file',
    'TARGET_DIR' => '/usr/share/nginx/@VENDOR@/tmp/filetmp',
    
    
    //网卡聚合配置信息存放文件
    "NIC_INFO_DIR" => '/etc/@VENDOR@/web/nicConf.ini',
	//代理分组类型
    'AGENT_GROUP_TYPE' => array(
        'UNKNOWN' => 0,
        'FILE' => 1,
        'DB' => 2
    ),
    //存放删除租户日志信息
    "DELETE_TENANT_PATH" => '/var/log/@VENDOR@/web/delete_tenant.log',
    //用户组类型
    "USER_GROUP_TYPE" => array(
        'UNKNOWN' => 0,
        'DEFAULT' => 1,
        'GLOBAL' => 2,
        'TENANT' => 3,
    ),
    
    //系统配置数据存储定义类型
    "SETTINGS_CONF" => array(
        'UNKNOWN' => 0, //未知
        'NTP' => 1,     //NTP配置
        'VISUAL' => 2,  //大屏配置
        'NIC' => 3,     //网卡聚合配置
        'SERVICE' => 4  //服务授权
    ),
)
?>
