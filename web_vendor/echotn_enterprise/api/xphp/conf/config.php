<?php
return  array
(
		
		'lang' => '@language@',		//语言文件
		'ext' => '.php',		//文件后缀
		
		/*系统信息*/
		'SYSTEM_INFO' => array(
		    'system_name' => 'ECHOTN云应用管理平台',
		    'company' => '南京天顺迪诚科技有限公司',
		    'copyright' => 'Copyright',
		    'years' => '2019',
		    'version' => '@version@',                         //软件版本号
		    'enterprise' => '@enterprise@',                   //软件版本     
		    
		    //授权处使用
		    'company_name' => '南京天顺迪诚科技有限公司',               
		    'company_tel' => '400-900-8028',
		    'company_email' => 'market@echotn.com',
		),
    
        //远程地址
        'REMOTE' => array(
            'website' => "http://www.echotn.com/",
            'api_url' => "https://www.vinchin.com:5786",         //api地址
//             'api_url' => "https://www.vinchin.com:5786/",         //api地址
            'api_user' => "product",                   //aip用户
            'api_pass' => "yunqi123456789",            //aip密码
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
            'pass' => 'yunqi123456',
            'port' => '3306',
            'dbname' => 'vinchin_db',
        ),
    
        'EMAIL' => array(
            'smpt_host' => 'smtp.exmail.qq.com',
            'from_email' => 'product@vinchin.com',
            'from_email_pass' => 'vinchin168',
            'port' => 25,
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
            'app/db/',
            'app/file/',
            'app/platform/',
            'app/vm/',
			'app/api/',
            'amq/',
        ),
    
        /*api 方法*/
        'API' => array(
            'access_token' => 'APIAccessTokenHandler',
            'jobs' => 'APIJobsHandler',
            'vcenters' => 'APIVcentersHandler',
            'logs' => 'APILogsHandler',
            'alarms' => 'APIAlarmsHandler',
            'storages' => 'APIStoragesHandler',
            'settings' => 'APISettingsHandler',
            'users' => 'APIUsersHandler',
            'nodes' => 'APINodesHandler',
        	'points' => 'APIBackupPointHandler'
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
            'PlatformHandler',  //平台
            'Vmhandler',        //虚拟机
            'FileHandler',      //文件
            'DbHandler',        //数据库
            'UsersHandler',     //用户管理
            'JobHandler',       //任务监控
            'DataHandler',      //备份数据管理
            'LogHandler',       //日志管理
            'SystemHandler',    //系统配置
            'AgentHandler',     //代理端管理
            'StorageHandler',   //存储
            'AlarmHandler',     //告警
            'ArchiveHandler',   //归档
            'ReportHandler',    //报表
            'ManoeuvreHandler', //灾难演练
            'Vcenter',          //虚拟化中心
            'NodeHandler',      //节点管理
        ),
        //lisence info
        'LISENCE_INFO' => array(
            'thumbprintFileName' => 'thumbprint.txt',
            'uploadfile' => array(
                'name' => 'files',
                'suffixes' => 'key',
                'size' => 2048,
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
                'size' => 104857600,
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
        'ZIP_PASS' => '!!vinchin123456',
        
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
        'UPLOAD_PATH' => '/usr/share/nginx/upgrade/',
        
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
        'BACKUP_MODE' => array('UNKNOWN'=>0, 'FULL'=>1, 'INCREMENTAL'=>2, 'DIFFERENTIAL'=>3, 'LOG'=>4, 'TAG'=>5),
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
            'FILE_RECOVERY' => 6,
            'VM_INSTANT_RECOVERY' => 7,
            'VM_INSTANT_RECOVERY_MOTION' => 8,
            'VM_REPLICATION' => 9,
            'SYNC' => 10,
            'ORCH_TASK' => 11,
            'BACKUP_EXPORT' => 12,
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
        ),
    
        //恢复位置
        'RECOVERY_POSITION' => array('ORIGINAL'=>1, 'OTHER'=>2),
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
            'PFPRIVATE' =>100,
            'NODE' => 1000,
        ),
        //虚拟机模块定义
        'VMHYPERVISORTYPE' => array(
            'VM_HYPERVISOR_TYPE_UNKNOWN' => 0,
            'VM_HYPERVISOR_TYPE_VMWARE' => 1,
            'VM_HYPERVISOR_TYPE_HYPERV' => 2,
            'VM_HYPERVISOR_TYPE_XENSERVER' => 3,
            'VM_HYPERVISOR_TYPE_KVM' => 4,
            'VM_HYPERVISOR_TYPE_XEN' => 5,
            'VM_HYPERVISOR_TYPE_ORACLEVM' => 6,
            
            ///////////////////////// OEM VMWARE //////////////////////////
            'VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM' => 7,	// shu guang, sugon
            
            ///////////////////////// OEM XENSERVER ///////////////////////
            'VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE' => 8,	// lang chao, Inspur
            'VM_HYPERVISOR_TYPE_HALSIGN_VGATE' => 9,			// halsign
            
            ///////////////////////// OEM KVM /////////////////////////////
            'VM_HYPERVISOR_TYPE_NEOKYLIN_KVM' => 10,			// NeoKylin kvm
            'VM_HYPERVISOR_TYPE_H3C_KVM' => 11,					// h3c kvm
            'VM_HYPERVISOR_TYPE_SANGFOR_KVM' => 12,				// sangfor kvm
            'VM_HYPERVISOR_TYPE_SDC_OS_KVM' => 13,				// dianke lingyun sdcos
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
        		
        ),
        //虚拟化分组,同一个分组可以相互恢复
        'VMHYPERVISORGROUP' => array(
            //VMware分组
            'vmware' => array(
                1, 2, 7, 21
            ),
            //XenServer分组
            'xenserver' => array(
                3, 8, 9, 18, 20, 25
            ),
            //KVM分组
//             'kvm' => array(
//               4, 10, 11, 12, 13, 14, 15, 19 
//             ),
            
            //huawei
        	'huawei' => array(
        		17
        	),
        	'openstack'=> array(
        		14, 15, 22
        	)
            
        ),
    
        //虚拟机模块描述
        'VMHYPERVISORDES' => array(
            'Unknown',
            'VMware vSphere',
            'Microsoft Hyper-V',
            'Citrix XenServer',
            'KVM',
            'Xen',
            'Oracle VM',
            'Cloudview SVM',
            'InCloud Sphere',
            'Halsign vGate',
            'NeoKylin',
            'H3C CAS',
            'SANGFOR HCI',
            'SDC OS',
            'FlexCloud',
            'OpenStack',
            'FusionSphere(kvm)',
            'Huawei Fusion Compute',
            'Winhong CNware',
            'Redhat RHV/Ovirt',
            'D-Server',
        	'Cloudview SVM',
        	'Flex HCS',
        	'Os Easy V-server',
        	'InCloud Sphere Kvm',
            'V-Server'
        ),
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
            'DBOpcode',
            'OSOpcode',
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
            'SYSTEM' =>　2,
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
            'SIGNATURE' => '天顺迪诚',
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
    
        //特殊配置文件地址，主要用作第三方产品配置信息
        'SPECIAL_DIR' => '/usr/share/nginx/@VENDOR@/special/',
        'SPECIAL_CONFIG' => 'config.txt',
    
        "NO_SUPPORT_HYPERVISOR" => array(
        	'STANDARD' => array(2,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23, 24,25),                //标准版*************
        	'ENTERPRISE' =>  array(),              //企业版*************
        	'EN_FREE_EDITION' =>  array(2, 8, 14, 15, 16, 17, 24),         //免费版(英文)*********
        	'STANDARD_EN' =>  array(2, 8, 14, 15, 16, 17, 24),             //标准版(英文)*********
        	'ENTERPRISE_EN' =>  array() ,           //企业版(英文)*********
        	'ESSENTIAL_EN' =>  array(2, 3, 8, 12, 14, 15, 16, 17, 19, 24),            //基础版(英文)*********
        )

)
?>