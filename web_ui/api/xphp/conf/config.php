<?php
return  array
(

	'lang' => '@language@',		//语言文件
	'ext' => '.php',		//文件后缀

	/*系统信息*/
	'SYSTEM_INFO' => array(
		'system_name' => '云祺容灾备份系统',     //备份系统名称
		'company' => 'Vinchin ',                          //厂商名
		'copyright' => 'Copyright',                       //版权
		'years' => '2025',                                //年份
		'vendor' => '@VENDOR@',                            //厂商
		'arch_type' => '@ARCHTYPE@',                      //底层架构 x86/arm
		'os_type' => '@OSTYPE@',                          //操作系统 centos7/kylin10
		'version' => '@version@',                         //软件版本号
		'enterprise' => '@enterprise@',                   //软件版本
		'recommend' => '', //推荐浏览器和分辨率提示
		//授权处使用
		'company_name' => '成都云祺科技有限公司',              //系统所属公司
		'company_tel' => '+86 400-9955-698',              //联系电话
		'company_email' => 'support@vinchin.com',         //支持邮箱
		'copyright_name' => '成都云祺科技有限公司'
	),
	//远程地址
	'REMOTE' => array(
		'website' => "https://www.vinchin.cn",              //公司官网
		'api_url' => "https://www.vinchin.com:5786",         //api地址
//             'api_url' => "https://www.vinchin.com:5786/",         //api地址
		'api_user' => "product",                   //api用户
		'api_pass' => "yunqi123456789",            //api密码
		'ueiplan' => "http://www.vinchin.cn/ueiplan.php",  //用户体验改善计划
		'privacy' => "http://www.vinchin.cn/privacy.php",  //隐私声明
		'release' => "http://www.vinchin.cn/release.php",  //版本发布记录
	),

	//软件版本
	'ENTERPRISE' => array(
		/**
		 * 这里是打包的版本
		 * 英文版本会有1个包,通过授权文件来区分不同版本
		 * 中文版会有2个包,一个标准版,一个企业版.
		 */
		'standard' => 'vinchin_standard',           //中文标准版
		'enterprise' => 'vinchin_enterprise',       //中文企业版
		'enterprise_en' => 'vinchin_enterprise_en', //英文企业版
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

	//系统配置-默认邮件服务器信息
	'EMAIL' => array(
		'smpt_host' => 'smtp.exmail.qq.com',
		'from_email' => 'product@vinchin.com',
		'from_email_pass' => 'vinchin168',
		'port' => 465,
		'authentication' => true,
		'type' => 'HTML',
		'sendTimeOut' =>300,  //邮件链接有效时间  单位s
	),
	/*socket info*/
	'SOCKET_INFO' => array(
		'IP' => 'localhost', //访问本地
		'Port' => 22710,     //访问端口
		'recvByte' => 272,  //最大接收长度
		'recTimeout' => 900,//接收超时时间
		'sendTimeout' => 900,//发送超时时间
		'agentTimeout' => 30,
		//第一层消息
		'BdHeader' => array(
			'magic_number' => 0xfafbfcfd,  //规定二进制魔术数字
			'sequence_number' => 0, //规定序列号
		),
		//第二层消息
		'BdRouterHeader' => array(
			'source_type' => 1,
			'message_level1_type' => 3,
			'message_level2_type' => 0,
		),
		//第二部分日志消息
		'BdLogRouterHeader' => array(
			'message_level1_type' => 4,
			'task_log' => 1,
			'system_log' => 2,
		),
		//检查消息
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
		'xphp/libs/',       //php扩展类
		'xphp/utils/',      //工具类
		'app/db/',          //数据库实时
		'app/dbprotect/',   //数据库定时
		'app/file/',        //文件
		'app/platform/',    //平台
		'app/vm/',          //虚拟化
		'app/api/',         //对外接口
		'amq/',             //ActiveMQ
		'monitor/',         //存储统计和报表邮件监控进程
		'dbcdp_monitor/',   //数据库实时(外)
		'billing_monitor/', //计费监控进程
		'systembak_monitor/',//备份系统备份监控进程
		'app/volcdp/',      //卷CDP
		'app/os/',          //操作系统
		'app/client/',      //客户端
		'update_monitor/',  //检测更新监控
		'app/nas/',         //nas备份
		'app/visualscreen/',         //可视化大屏
		'app/copy/',        //副本容灾
		'app/archive/',        //数据归档
		'app/exchange/',         //exchange备份
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
		'points' => 'APIBackupPointHandler',
	),

	//api头消息配置
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
		'ManoeuvreHandler', //灾难演练             14
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

		//卷cdp模块添加，暂时定义下面的值，后根据实际情况调整
		'VolCDPBackupHandler',     //卷CDP备份30,
		'VolCDPRecoverHandler',    //卷CDP恢复31,
		'VolCDPTakeoverHandler',   //卷CDP应用及数据接管32
		'VolCDPBackupSetHandler',   //备份集管理33

		//OS
		'OsHandler',             //操作系统备份34

		//客户端
		'ClientHandler',        //客户端管理类35
		'NasHandler',           //nas备份   36
		'SystemMonitorHandler', //监控中心,系统监控 37
		'VisualScreenHandler', //可视化大屏V2, 38
		'HomepageHandler',  //首页  39

		'CopyNewHandler',      //副本容灾 40
		'ArchiveNewHandler',   //数据归档 41
		'ExchangeHandler',    //exchange备份 42
	),
	//lisence info
	'LISENCE_INFO' => array(
		'thumbprintFileName' => 'thumbprint.txt',  //指纹文件名
		//下载文件方式
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
			'host' => 1,   //按主机授权
			'cpu' => 2,    //按CPU
			'storage' => 3,//按容量
			'vm' => 4,     //按虚拟机个数
		),
		//license文件类型
		'filetype' => array(
			'license' => 1, //系统授权文件后缀
			'service' => 2, //服务授权文件后缀
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
//         'NODE_SOFT' => '/usr/share/nginx/@VENDOR@/tmp/node_soft/',
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
	// 三员用户类型定义
	'THREE_POWERS_USER' => [
		'admin'		=> 1,
		'sysadmin'	=> 2,
		'safeadmin'	=> 3,
		'auditor'	=> 4,
		'operator'	=> 5,
	],
	// 用户type定义
	'USER_TYPES' => [
		'USER_LOCATION' => 1, // 本地全局/普通用户
		'USER_EXTERNAL' => 2, // ad域用户
		'USER_ADMIN' 	=> 3, // 本地/管理员
	],

	//公共标记 1true 2false
	'FLAG' => array('SET' => 1, 'UNSET' => 2),

	//策略备份模式
	'BACKUP_MODE' => array('UNKNOWN'=>0, 'FULL'=>1, 'INCREMENTAL'=>2, 'DIFFERENTIAL'=>3, 'LOG'=>4, 'COPY'=>5, 'ARCHIVE'=>6, 'TAG'=> 7, 'VERIFY'=> 8, 'PINCREMENTAL' => 9),
	//策略类型
	'STRATEGY_TYPE' => array('UNKNOWN'=>0, 'EVERY_DAY'=>1, 'EVERY_WEEK'=>2, 'EVERY_MONTH'=>3, 'ONCE'=>4),
	// 时间策略备份方式
	'TIME_STRATEGY_BACKUP_TYPE' => [
		'unknown' => 0,
		'strategy' => 1,  // 按策略备份
		'oncetime' => 2,  // 一次性备份
		'manual' => 3,    // 无策略备份
	],
	'TIME_STRATEGY_BACKUP_TYPE_MAP' => [
		1 => 'strategy',  // 按策略备份
		2 => 'oncetime',  // 一次性备份
		3 => 'manual',    // 无策略备份
	],
	//滚动类型
	'STRATEGY_ROLL_TYPE' => array('ON'=>1, 'OFF'=>2),
	//任务类型
	'TASKTYPE' => array(
		'BACKUP' => 1,              //备份
		'RECOVERY' => 2,            //恢复
		'DISK_BACKUP' => 3,                 //磁盘备份(未支持)
		'DISK_RECOVERY' => 4,               //磁盘恢复(未支持)
		'VM_FILE_BACKUP' => 5,              //(未支持)
		'VM_FILE_RECOVERY' => 6,            //细粒度恢复
		'VM_INSTANT_RECOVERY' => 7,         //虚拟机瞬时恢复
		'VM_INSTANT_RECOVERY_MOTION' => 8,  //在线迁移
		'VM_REPLICATION' => 9,              //复制(未支持)
		'SYNC' => 10,                       //同步(未支持)
		'ORCH_TASK' => 11,                  //灾难演练
		'BACKUP_EXPORT' => 12,                      //备份数据导出
		'VM_CDP_BACKUP' => 13,                      //虚拟机CDP备份
		'VM_CDP_RECOVERY' => 14,                    //虚拟机CDP恢复
		'VM_CDP_INSTANT_RECOVERY' => 15,            //虚拟机CDP瞬时恢复
		'VM_CDP_INSTANT_RECOVERY_MOTION' => 16,     //虚拟机CDP在线迁移
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
        'DB_CDP_SYN' => 46,  //数据库实时同步
        'DB_CDP_RECOVER' => 47,  //数据库实时恢复
		'OS_INSTANT_RECOVERY' => 49 ,//操作系统瞬时恢复
		'OS_INSTANT_RECOVERY_MOTION' => 50,  //操作系统在线迁移
		'VM_HUAWEI_CBR_SYNC' => 51, //华为CBR同步任务
		'PLATFORM_RECOVERY' => 52, // 跨平台恢复 52
		'INSTANT_RECOVERY' => 53, // 瞬时恢复 53
		'INSTANT_RECOVERY_MOTION' => 54, // 迁移 54
		'GRAIN_RECOVERY' => 55, // 细粒度恢复 55
		'KUBE_BACKUP' => 56, // K8s备份
		'KUBE_RECOVERY' => 57, // k8s恢复
		'FILE_COPY' => 62, //文件复制
		'FILE_COMPARE' => 63, //文件对比
		'DRILL' => 64, //演练
		'VOL_CDP_REPLICATION' => 65, //CDP复制
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
		'SUCCESSED' => 17,//任务成功
		'CREATING' => 18, // task is being created
        'PENDING' => 19, // task is pending
	),
	/**
	 * 任务控制项
	 * 启动1/停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切(新增)/15 停止回切(新增)/16继续备份(新增)
	 */
	'TASK_CONTROL' => array(
		'UNKNOWN' => 0,         //unknown task control
		'START' => 1, 			//启动任务
		'STOP' => 2, 			//停止任务
		'MODIFY' => 3, 			//修改任务
		'DELETE' => 4, 			//删除任务
		'PAUSE' =>5, 			//暂停任务
		'START_DIFF' => 6, 		//启动差异
		'START_INCR' => 7, 		//启动增量
		'START_STRATEGY' => 8, 	//启动策略
		'MIGRATION' => 9, 		//迁移
		'START_FULL' => 10, 	//启动完备
		'START_TAKEOVER' => 11, //启动接管
		'STOP_TAKEOVER' => 12, 	//停止接管
		'LOG_BACKUP' => 13, 	//日志备份
		'START_FAILBACK' => 14, //启动回切
		'STOP_FAILBACK' => 15,  //停止回切
		'CONTINUE' => 16,       //继续备份
		'START_COPY_MIRROR' => 17, //启动镜像副本
		'START_COPY_FULL' => 18, //启动完整副本
		'START_COPY_INCREASE' => 19 //启动增量副本
	),

	//恢复位置
	'RECOVERY_POSITION' => array('ORIGINAL'=>1, 'OTHER'=>2, 'FILEDIR' => 3, 'REDIRECT' => 4, 'EXPORT' => 5,'PDB' => 6),
	//恢复方式   立即恢复/按时间策略恢复
	'RECOVERY_TIME_TYPE' => array('IMMEDIATELY'=>1, 'STRATEGY'=>2, 'ONCETIME' => 4),
    //恢复方式
    'BD_RECOVERY_TYPE' => [
        'UNKNOWN' => 0,
        'SPECIFIC_TIMEPOINT' => 1,  // 指定时间点恢复
        'LATEST_TIMEPOINT' => 2,  // 恢复最新时间点
    ],

	//模块定义
	'MODULE_TYPE' => array(
		'UNKNOWN' => 0,
		'PF' => 1,  //平台
		'VM' => 2,  //虚拟机
		'FS' => 3,  //文件
		'DB' => 4,  //数据库
		'OS' => 5,  //操作系统
		'VDDT_SERVER' => 6,
		'VDDT_CLIENT' => 7,
		'BACKUP_COPY_SERVER' => 8,  //副本归档服务server
		'BACKUP_COPY_CLIENT' => 9,  //副本归档服务客户端（web接发消息用）
		'VOL_CDP' =>10, 			//与后台协商暂时定义为
		'NAS' => 11,  //nas
		'DB_CDP' => 12,
		'STORAGE_SERVER' => 13,
		'M365' => 14,  //M365
		'NODE_MANAGER' => 15,
		'COPY' => 16,
		'PUBLIC_CLOUD' => 17,	//公有云
		'TEMP_AGENT'=> 18,	//模板虚拟机
        'STRATEGY' => 19, // 策略
        'CLUSTER' => 21,  // 集群
		'PRIVATE_CLOUD' => 22, // 私有云
		'BACKUP_DATA' => 23, // 备份数据管理
        'TOOL' => 24, // 工具
		'FILE_COPY' => 26, //文件复制
        'KUBERNETES' => 28,
		'PFPRIVATE' =>100,          //平台私有
		'NODE' => 1000,             //节点
		'OEM_DBCDP' => 10000,       //友商数据库CDP
		'OEM_FSCDP' => 10001,       //友商文件CDP
		'OBS' => 999,                // 对象存储
		'HADOOP' => 998,               // hadoop

	),
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
		'VM_HYPERVISOR_TYPE_WINHONG_KVM' => 32,                  //winhong KVM
		'VM_HYPERVISOR_TYPE_SMARTX_KVM' => 33,                  //smartx KVM
		'VM_HYPERVISOR_TYPE_SUGON_CLOOUD_OPENSTACK' => 34,                  //Sugon CloudView
		'VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM' => 35,                  //Inspur Cloud Platform
		'VM_HYPERVISOR_TYPE_EASYSTACK' => 36,                  //EasyStack
		'VM_HYPERVISOR_TYPE_FIBERHOME_OPENSTACK' => 37,                  //Fiberhome Openstack
		'VM_HYPERVISOR_TYPE_CTSI_OPENSTACK' => 38,                  //CTSI Openstack
		'VM_HYPERVISOR_TYPE_AW_CLOUD' => 39,                  //AWCloud
		'VM_HYPERVISOR_TYPE_INSPUR_VVDK' => 40,		// Inspur VVDK
		'VM_HYPERVISOR_TYPE_KVM_ZVIRT' => 41,              // zVirt, based on ovirt
		'VM_HYPERVISOR_TYPE_PROXMOX' => 42, 			//Proxmox
		'VM_HYPERVISOR_TYPE_XFUSION_KVM' => 43, 		//Xfusion KVM
		'VM_HYPERVISOR_TYPE_XHERE' => 44, 		//xhere
		'VM_HYPERVISOR_TYPE_HOSTVM' => 45, 		// hostvm, ovirt-engine
		'VM_HYPERVISOR_TYPE_HUAWEI_CBR' => 46,	 //hauwei CBR
		'VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM' => 47, 		// sangfor vvdk
		'VM_HYPERVISOR_TYPE_CLOUDVIEW_KVM' => 48, 		// CloudView KVM
		'VM_HYPERVISOR_TYPE_RED_VIRT' => 49, 		// red virtualization, based on ovirt
		'VM_HYPERVISOR_TYPE_ROSA_VIRT' => 50, 		// rosa virtualization, based on ovirt
		'VM_HYPERVISOR_TYPE_H3C_CAS_CVD' => 51, 		// H3C CAS CVD, based on H3C KVM
		'VM_HYPERVISOR_TYPE_OVIRT_KVM' => 52,              // redhat ovirt
		'VM_HYPERVISOR_TYPE_LENOVO_AIO' => 53,              // Lenovo AIO KVM
		'VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK' => 54,              // huawei cloud stack, private cloud

		'VM_HYPERVISOR_TYPE_AWS' => 100, //AWS
		'VM_HYPERVISOR_TYPE_HUAWEI_CLOUD' => 101, //huawei cloud

		'VM_HYPERVISOR_TYPE_EMD' => 108, // 虚拟演练室
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
			14, 15, 22, 31, 34, 35, 36, 37, 38, 39, 54
		),
		'redhat' => array(
			19, 29, 41, 45, 49, 50, 52
		),
		//公有云
		'publiccloud' => array(
			100, 101
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
			54
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
		'InCloud Sphere Sphere / Rail (VVDK)',
		'zVirt',
		'Proxmox VE',
		'FusionOne Compute',
		'XSKY XHERE',
		'HOSTVM',
		'HUAWEI CBR',
		'Sangfor Cloud Platform',
		'CloudView',
		'RED Virtualization',
		'ROSA Virtualization',
		'H3C UIS/CAS CVD',
		'oVirt',
		'Lenovo AIO',
		'Huawei Cloud Stack',
		100 => 'AWS',
		101 => 'Huawei Cloud',
		108 => 'Emd',
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
		'PFOpcode', //平台
		'VMOpcode', //虚拟机
		'FSOpcode', //文件
		'DBProtectOpcode', //数据库
		//             'DBOpcode',
		'OSOpcode', //操作系统
		'VMOpcode',
		'VMOpcode',
		'VMOpcode',
		'VMOpcode',
		'VolCDPOpcode', //卷CDP
		18 => 'VolCDPOpcode',
		26 => 'FCOpcode', //文件复制
		100 => 'PFOpcodePrivate',//平台私有
		1000 => 'NodeOpcode', //节点
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
		'HUAWEI_CBR'=>12,
		'FILE_SYSTEM' => 13,   //并行文件系统

	),
	//存储用途
	'BD_STORAGE_USE_MODE' => array(
		'UNKNOWN' => 0,
		'BACKUP' => 1,  //备份
		'COPY' => 2,    //副本
		'ARCHIVE' => 3, //归档
		'NAS' => 4, //NAS
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
		'ONLINE' => 1,  //在线
		'CREATING' => 2,//创建
		'OFFLINE' => 3, //离线
		'UNMOUNT' => 4, //未挂载
		'WARNING' => 5  //告警
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
	//虚拟机数据存储状态
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
		'WECHAT' => 3,         //微信公众号通知
		'WECHAT2' => 4,         //企业微信通知
	),
	//通知类型
	'NOTICE_TYPE' => array(
		'UNKNOWN' => 0,
		'TASK' => 1,        //任务通知
		'SYSTEM' => 2,      //系统通知
		'VERIFY' =>3 ,
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

	// 微信公众号配置
	'WECHAT_CONFIG' => [
		'gh_id' => 'gh_2b928b00b109', // 原始id
		'appid' => 'wxadd515c8c7890d9c', // 填写高级调用功能的app id
		'appsecret' => '3209e7034725a123a53fe7f022c10cdb', // 填写高级调用功能的密钥
		'template_id' => 'RXYWI_4e3uaenFbClw01MInlfF-Thk3_EtexCXeFnQw', // 模板id
		'template_param1' => 'thing1', // 模板参数1
		'template_param2' => 'thing5', // 模板参数2
		'wechat_mode' => 1, // 模式 默认 1是系统中转 2是自定义
	],
	// 微信公众号出厂中转服务器地址
	'WECHAT_TRANSFER_URL' => 'https://update.vinchin.com/wechat',

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
		'NETWORKERROR' => 4,
		'WAITING' => 5,
		'RUNNING' => 6,
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
		'TEMPORARY' => 6,     // temporary file(not support backup)
		'DIR_LINK' => 7,     // link directory
		'FILE_LINK' => 8,     // link file
		'SPARSE_FILE' => 9,
		'SHORTCUT' => 10,
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
	//后台系统日志路径
	'LOG_PATH' => '/var/log/@VENDOR@',
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
		'DIY_VERSION' => 100            //自定义版本
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
		'STANDARD' => array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23, 24,25,26,41,45,47),    //标准版*************
		'ENTERPRISE' =>  array(),              													//企业版*************
		'EN_FREE_EDITION' =>  array(8, 28, 14, 15, 16, 17, 24),         							//免费版(英文)*********
		'STANDARD_EN' =>  array(8, 14, 15, 16, 17, 24),             							//标准版(英文)*********
		'ENTERPRISE_EN' =>  array() ,           												//企业版(英文)*********
		'ESSENTIAL_EN' =>  array(3, 8, 12, 14, 15, 16, 17, 19, 24, 41, 45, 47),            				//基础版(英文)*********
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
		"UNKNOWN" => 0,
		"ORACLE" => 2,
		"SQLSERVER" => 1,
		"MYSQL" => 3,
		"DM" => 4,
		"POSTGRE" => 5,
		"KINGBASE" => 6,
		"UXDB" => 7,
		"HIGHGO" => 8,
		"MARIA" => 9,
		"OPENGAUSS" => 10,
		"VASTBASE" => 11,
		"ANTDB" => 12,
		"SAPHANA" => 13,
		"TIDB" => 14,
		"MONGODB" => 15,
		// "CACHE" => 15,
		// "IRIS" => 16,
	),
	//数据库类型描述
	"DB_TYPE_DES" => array(
		"Unknown",
		2 => "Oracle",
		1 => "SQL Server",
		3 => "MySQL",
		"DM",
		"PostgreSQL",   //PostgreSQL
		"KingbaseES", //人大金仓
		"UXDB",     //优炫
		"Highgo DB", //翰高
		"MariaDB", //
		"openGauss",
		"Vastbase",
		"AntDB",
		"SAP HANA",
		"TiDB",
		"MongoDB",
		// "InterSystems Caché", // InterSystems Caché
		// "InterSystems IRIS",  // InterSystems IRIS
		1000 => "Exchange Server"
	),
	"DB_TYPE_INDEX" => array(
		2 => 2,  //oracle
		1 => 1,  //sqlserver
		3 => 3,  //mysql
		4,  //DM
		5,  //PostgreSQL
		6,  //Kingbase
		7,  //UXDB
		8,  //Highgo DB
		9,  //MariaDB
		10, //openGauss
		11, //Vastbase
		12, //AntDB
		13,  // SAP HANA
		14, // TiDB
		15, // MongoDB
		// 15, // Caché
		// 16, // IRIS
		1000,//Exchange
	),
	//代理类型
	"AGENT_TYPE" => array(
		"UNKNOW" => 0,
		"NORMAL" => 1,  //normal
		"MEMORY_OS" => 2,  //memory operating system(livecd/winpe)
		"NAS" =>3, //nas
		"APPLIANCE" => 4, // vm transport agent
		"TEMP_AGENT" =>5,// vm tempAgent
	),

	//服务状态
	"SERVICE_STATUS" => array(
		"" => 0,
		"enabled" => 1,
		"disabled" => 2,
		"static" => 3,
	),
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
		'vm_proxmox',						// Proxmox ···42
		'vm_xfusion',						// Xfusion KVM ···43
		'vm_xhere',						// xhere ···44
		'vm_hostvm',						// hostvm, ovirt-engine ···45
		'vm_huawei_cbr',                //huwwei cbr ···46
		'vm_sangfor_vvdk_kvm',						// sangfor vvdk ···47
		'vm_cloudview_kvm',						// CloudView KVM ···48
		'vm_kvm_redvirt',				// red virt ···49
		'vm_kvm_rosavirt',				// rosa virt ···50
		'vm_h3c_cas_cvd',				// h3c cas cvd ···51
		'vm_ovirt_kvm',              // redhat ovirt ···52
		'vm_lenovo_aio',              // Lenovo AIO KVM ···53
		'vm_huawei_cloud_stack',              // huawei cloud stack ···54

		100 => 'vm_aws',						// AWS ···100
		101 => 'vm_huawei_cloud',                        // huawei cloud, public cloud platform ···101
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
		'BACKUP_COPY_TYPE_OS_BACKUP_COPY' => 5,				//os system backup copy
		'BACKUP_COPY_TYPE_OS_ARCHIVE' => 6,						//operator system archive
		'BACKUP_COPY_TYPE_NAS_BACKUP_COPY' => 7,				//nas backup copy

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
		'SERVICE' => 4,  //服务授权
		'UPDATE' => 5,  //系统升级
		'SYSTEM_MONITOR_PROGRESS' =>6, //系统监控报警规则配置
		'SYSTEM_MONITOR_PROGRESS_TIME' =>7,//系统监控报警规则配置检测时间和通道沉默周期
		'SYSTEM_UPDATE_FLAG' =>8,//系统升级更新sql的flag版本信息
		'PLATFORM_SYSTEM_RECOVERY' =>9,//容灾演练平台配置信息
		'SYSTEM_WECHAT_OPENID' =>10,// 微信公众号的配置
		'SYSTEM_ENTERPRISE_WECHAT' =>11,// 企业微信的配置
		//20-29先暂时为页面设计相关占用
		'HOMEPAGE' => 20, //页面首页定制
		'THEME' => 21, //页面主题配色
		'LAYOUT' => 22, //页面布局
		'LOGINPAGE' => 23, //页面登录布局,
		'CUSTOM_VERSION' => 24, 	//定制版本
		'PLATFORM_SYSTEM_CARBON_MONITOR' => 25, //能耗监控平台配置信息
	),
	//虚拟实验室状态
	"VIRTUAL_LAB_STATUS" => array(
		'UNKNOWN' => 0, //未知
		'DEPLOYMENT' => 1,     //部署中
		'ONLINE' => 2,  //在线
		'OFFLINE' => 3,     //离线
		'ABNORMAL' => 4,  //异常
		'ERROR' => 5,  //错误
		'MODIFY' => 6,  //修改中
		'DEPLOY' => 7  //已部署
	),
	//虚拟实验室状态
	"VERIFY_STATUS" => array(
		'WAIT' => 0,    //等待
		'VERIFY' => 1,  //验证中
		'FINISH' => 2,  //完成
		'ERROR' => 3,   //错误
		'SKIP' => 4,    //跳过
	),
	//数据库任务运行中状态
	"DB_TASK_STATUS" => array(
		'UNKNOWN' => 0, //未知
		'WAITING' => 1, //等待
		'RUNNING' => 2, //运行
		'FINISH' => 3, //完成
		'ERROR' => 4, //错误
	),
	//客户端操作系统类型
	"AGENT_OS_TYPE" => array(
		'UNKONWN' => 0,
		'WINDOWS' => 1,
		'RHEL6' => 2,
		'RHEL7' => 3,
		'RHEL8' => 4,
		'UBUNTU' => 5,
		'DEBIAN' => 6,
		'KYLIN' => 7,
		'UNIONTECH' => 8,
		'KYLINX86' => 9,
		'ZKFD-V4' => 10,
		'UNIONTECHX86' => 11,
		'ANOLISOSX64' => 12,
	),
	//操作系统类型对应键值用于获取插件
	"AGENT_OS_TYPE_DES" => array(
		'UNKONWN',
		'WINDOWS',
		'RHEL6',
		'RHEL7',
		'RHEL8',
		'UBUNTU',
		'DEBIAN',
		'KYLIN',
		'UNIONTECH',
		'KYLINX86',
		'ZKFD-V4',
		'UNIONTECHX86',
		'ANOLISOSX64',
	),
	"AGENT_DEPLOY_STATUS" => array(
		"UNKONWN" => 0,
		"DEPLOY_WAITING" => 1,
		"DEPLOY_SUCCESS" => 2,
		"DEPLOY_FAILED" => 3,
		"UPGRADE_WAITING" => 4,  // 升级中
		"UPGRADE_SUCCESS" => 5,
		"UPGRADE_FAILED" => 6,
	),
	//nas挂载状态
	"NAS_MOUNT_STATUS" => array(
		"NS_NAS_MOUNT_UNKWON" => 0,
		"NS_NAS_MOUNT_NORMAL" => 1,
		"NS_NAS_MOUNT_ABNORMAL" => 2,
		"NS_NAS_MOUNT_ERROR" => 3,
	),
	//--------------
	//检测是否能联网的URL地址
	"UPDATE_CHECK_URL" => "https://update.vinchin.com/",
	//在线升级请求的服务器
	"UPDATE_REQUEST_URL" => "https://update.vinchin.com/api/",
	//在线升级下载安装包目的地,文件夹路径
	"UPDATE_DOWNLOAD_FILE_PATH" => "/usr/share/nginx/@VENDOR@/tmp/upgrade/",
	//测试人员如果想获取测试包的内容 则需要新建此文件,内容为json,目前可选为test_ip字段
	"UPDATE_TEST_FLAG_PATH_FILE" => "/usr/share/nginx/@VENDOR@/tmp/upgrade/testInfo.json",
	//临时日志存放目的地
	"UPDATE_TMP_LOG_FILE" => "/usr/share/nginx/@VENDOR@/tmp/upgrade/updatelog.json",

	"UPDATE_PATCH_STATUS" => array(
		"UNKNOWN" => 0,
		"UPDATE_ALREADY_UPLOAD" => 1,   //已上传, 等待升级中
		"UPDATE_FAILED" => 2,           //升级失败
		"UPDATE_UPLOAD_FAILED" => 3,    //上传失败
		"UPDATE_SUCCESS" => 4,          //升级成功
		"UPDATE_UPLOAD_TO_NODE" => 5,   //升级包正在上传到节点
		"UPDATE_WAITING" => 6,          //等待升级中
		"UPDATING" => 7,                //升级中
		"PATCH_INVALID" => 8,           //升级包无效
		"PATCH_INVALID_ING" => 9,       //升级包检查中
		"PATCH_INVALID_COMPLETE" => 10, //升级包检查完成
	),

	//客户端环境
	"AGENT_ENVIRONMENT" => array(
		//底层架构
		'arch_type' => array(
			'X86',
			'ARM'
		),
		//操作系统类型
		'os_type' => array(
			'WINDOWS',
			'RHEL6',
			'RHEL7',
			'RHEL8',
			'RHEL9',
			'UBUNTU',
			'DEBIAN',
			'KYLIN',
			'UNIONTECH',
			'WINPE'
		),
	),

	//策略组类型备份
	"POLICY_TYPE_BACKUP" => array(
		"VIRTUAL_MACHINE" => 1,
		"FILE" => 2,
		"DATABASE" => 3,
		"OPERATING_SYSTEM" => 4
	),
	//数据库恢复方式
	"DB_RECOVERY_TYPE" => array(
		"UNKNOWN" => 0,
		"COVER" => 1,   //原机覆盖恢复
		"CREATE" => 2,  //新建恢复
		"SPECIFY_FOLDER" => 3, //指定文件夹恢复
		"REDIRECT_DIR" => 4,   //重定向目录恢复
		"EXPORT" => 5,  //导出恢复
		"PDB" => 6,  //PDB恢复
		"RESTORE_ARCHIVELOG" => 7,// 还原归档日志
		'FULL' => 8,  // 完全恢复
		'INCOMPLETE' => 9,  // 不完全恢复
	),
	// Oracle spfile.file路径
	'ORACLE_SPFILE_PATH' => '/backup_storage/%s/db/%s/spfile.file',
	'ORACLE_LISTENER_PATH' => '/backup_storage/%s/db/%s/listener.ora',
	'ORACLE_TNSNAMES_PATH' => '/backup_storage/%s/db/%s/tnsnames.ora',
	'ORACLE_SQLNET_PATH' => '/backup_storage/%s/db/%s/sqlnet.ora',
	"THREE_STATUS" => array(
		"normal" => 0,          //正常
		"some_abnormal" => 1,   //部分有问题
		"all_abnormail" => 2,   //全部有问题
	),

	//6.0授权调整后的授权容量/数量类型
	"LICENSE_BIG_TYPE" => array(
		"STORAGE" => 1,     //容量授权
		"NUM" => 2,         //数量授权
		"MIX_STORAGE" => 3  //混合存储空间授权
	),

	//卷CDP授权
	"VOLCDP_LISENCE_TYPE" => array(
		"NUM" => 1,         //数量授权
		"STORAGE" => 2,     //容量授权
	),
	//新增华为CBR同步细粒度类型
	"HUAWEI_CBR_GRAIN_TYPE"=> array(
		"UNKNOWN" => 0,
		"VAULT" => 1, //按存储库同步
		"RESOURCE" => 2,//按虚拟机同步
		"TIMEPOINT"=>3 //时间点同步
	),
	//访问接口主机名，默认访问本地
	"HOSTNAME" => "",

	//全局策略类型
	'GLOBAL_STRATEGY_TYPE' => array(
		'VIRTUAL_MACHINE' => 1,
		'FILE' => 2,
		'DATABASE' => 3,
		'OPERATING_SYSTEM' => 4,
		'NAS' => 5,
		'MICROSOFT' => 6,
		'KUBERNETES' => 7,
		'AWS' => 8,
	),
	//登录页路径
	"LOGIN_INFO" => array(
		'login_url' => '@loginpage@',
		'login_layout' => '@loginlayout@'
	),
	// 文件子模块定义
	'SUBMODULE_TYPE' => array(
		'UNKNOWN' => 0,
		'FS' => 1,
		'NAS' => 2,
		'HADOOP' => 3,
		'OBS' => 4
	),
	//虚拟机子模块类型
	"VM_SUB_MODULE" => array(
		'UNKNOWN' => 0,
		'VM' => 1,
		'PRIVATE_CLOUD' => 2,
		'PUBLIC_CLOUD' => 3,
	),
	// 任务编排计划状态
	'ORCHESTRATION_PLAN_STATUS' => ARRAY(
		'UNKNOWN' => 0,
		'WAITING' => 1,
		'RUNNING' => 2,
		'PAUSED' => 3,
		'STOPPED' => 4,
		'ERROR' => 5,
	),
	"CUSTOM_VERSION" => array(
		'unknown' => "",
		'professional' => "", //专业版
		'basic' => "vinchin_enterprise_sr",    //基础版
		'special' => "",   //白牌版
		'project' => "vinchin_enterprise_pr"   //项目版
	),
	"TRANSPORT_ENCRYPT_METHOD" => array(
		1 => "RSA",
		2 => "SM2",
	),
	"STORE_ENCRYPT_METHOD" => array(
		1 => "AES-256",
		2 => "SM4",
	),
	// 任务编排阶段状态
	'ORCHESTRATION_SECTION_STATUS' => array(
		'UNKNOWN'=> 0,
		'WAITING',
		'RUNNING',
		'FINISHED',
		'STOPPED',
	),
	// 资源分配类型
	'RESOURCE_DIS_TYPE' => [
		// key => 语言包键名
		2 => 'UI_PLATFORM_VM_APPLIANCE', // 传输代理
		3 => 'UI_VISUAL_VM', // 虚拟机
		7 => 'UI_PALTFORM_NODE', // 备份节点
		8 => 'UI_PLATFORM_STORAGE_RESOURCE', // 存储资源
		10 => 'UI_JOB_CLIENT', // 客户端
		56 => 'UI_PLATFORM_OFFICE365_ORGANIZATION', // 组织管理
		57 => 'UI_NAS_DEVICE_NAME', // nas设备
		58 => 'UI_PLATFORM_VM_CLOUD_PLATFORM', // 公有云平台
		59 => 'UI_PLATFORM_OBS', // 对象存储
		60 => 'WEB_HADOOP_CLUSTER', // Hadoop集群
		61 => 'UI_PLATFORM_VM_PRIVATE_CLOUD_PLATFORM', // 私有云
		62 => 'WEB_K8S_CLUSTER_PROTECT', // kubernetes集群
	],
	// 虚拟机重置CBT级别
	'RESET_CBT_LEVEL' => [
		'NONE' => 1, // 不重置
		'ERROR' => 2, // 错误时重置
		'FULL_BACKUP' => 3, // 完备时重置
	],
    // 操作状态
    'OPERATION_STATUS' => [
        'NO_OPERATION' => 0,  // 未操作
        'MERGING' => 1,  // 合并中
        'DELETING' => 2,  // 删除中
        'SCANNING' => 4,  // 扫描中
        'VERIFY' => 8,  // 校验中
    ],
	// 病毒查杀状态
    'VIRUS_STATUS' => [
        'NO_SCAN' => 0, // 未扫描
        'SCANNING' => 1, // 扫描中
        'SAFE' => 2, // 健康
        'INFECTED' => 3, // 感染
        'INFECTED_PARTLY_SCAN' => 4, // 感染但未扫描完成
    ],
	// 完整性状态
    'INTEGRITY_STATUS' => [
        'NORMAL' => 0, // 正常
        'BROKEN' => 1, // 损坏
        'NO_CHECK' => 2, // 未检查'
    ],
	// 时间点合并状态
    'MERGE_STATUS' => [
        'NORMAL' => 0, // 正常
        'WAITTING' => 1, // 待合并
        'FAILED' => 2, // 失败
        'UN_MERGE' => 3, // 不可进行合并
	],
	// 时间点状态
	'POINT_STATUS' => [
		'UNKNOWN' => 0, // 未操作、未知
		'OPERATING' => 1, // 操作中
		'ABNORMAL' => 2, // 异常
		'NORMAL' => 3, // 正常
	],
	"VIRUS_HISTORY_STATUS" => [
		'DETECT_STATUS_NONE' => 0,
		'DETECT_STATUS_RUNNING',         // 扫描中 | 暂未使用
		'DETECT_STATUS_INTERRUPTED',   // 扫描中止 | 检测病毒立即停止或者检测任务被停止会使用该字段
		'DETECT_STATUS_COMPLETED',     // 扫描完成 | 正常完成扫描
		'DETECT_STATUS_FAILED',       // 扫描失败 | 因某些原因扫描出错，具体原因可解析error_code
	]
)
?>
