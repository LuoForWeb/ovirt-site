var CONF = {
	
	// <----------------------------------------------- BEGIN PUBLIC CONFIG ----------------------------------------------->

	M : {
		PLATFORM : 0,
		VM: 1,
		FILE: 2,
		DB: 3,
		USER: 4,
		JOB: 5,
		DATA: 6,
		LOG: 7,
		SYSTEM: 8,
		AGENT: 9,
		STORAGE: 10,
		ALARM: 11,
		ARCHIVE: 12,
		REPORT: 13,
		MANOEUVRE: 14,
		VCENTER: 15,
		NODE: 16,
		COPY: 17,
		VISUAL: 18,
		DBCDP: 19,
		DBTIMING: 20,
		TENANT: 21,			//租户
		ROLE: 22,			//角色
		RESOURCE: 23,		//资源
		DOMAINSERVER: 24,	//域服务器
		BILLING: 25,		//付费功能
		FILECDP: 26,
		DBPROTECT: 27,
		SYSBAK:	28,
		SETTINGSHANDLER:29, //系统配置
		//卷cdp模块添加，暂时定义下面的值，后根据实际情况调整
		VOLCDPBACKUP:30,   //备份
		VOLCDPRECOVER:31,  //恢复
		VOLCDPTAKEOVER:32,  //接管
		VOLCDPBACKUPSET:33, //备份集管理
		OS: 34,	//操作系统
		CLIENT: 35,
		NAS: 36, //nas备份
		SYSTEMMONITOR: 37, //监控中心
		VISUALSCREEN: 38, //可视化大屏V2
		HOMEPAGE: 39,	//首页
		COPYNEW: 40,	//副本容灾new
		ARCHIVENEW: 41, //数据归档new
		EXCHANGE:42 //exchange备份
	},
	
	STRATEGY_TYPE : {
		DAY: 1,
		WEEK: 2,
		MONTH: 3,
		GLOBAL: 4
	},
	
	RESERVE_TYPE : {
		NUM: 1,
		DAY: 2,
		PERMANENT: 3,//永久
	},

	// 保留策略数据保留类型
	RESERVE_STRATEGY_MODE : {
		POINT: 1, // 按备份点保留
		CHIAN: 2, // 按备份链保留
	},

	//模块类型
	MODULE_TYPE : {
		VM: 2,
        FS: 3,
        DB: 4,
        OS:5,
        VDDT_SERVER:6,
        VDDT_CLIENT:7,
        BACKUP_COPY_SERVER:8,
        COPY: 16,
        VOL_CDP:10,
		NAS:11,
		DB_CDP: 12,  //数据库实时
		M365:14,
		AWS: 17,
		FILE_COPY: 26,
		KUBERNETES: 28,
		OBS: 999,                // 对象存储
		HADOOP: 998,               // hadoop
	},

	TIMING_MODULE_TYPE_ARR: [2, 3, 5, 11, 14, 28] , // 定时备份模块

	MODULE_TYPE_DES: {},
	
	VM_TYPE:{},
	VM_DES:[],
	SYSTEMNAME:"",
	SOFTWARE: 2,
	AJAXPATH : "./api/",
	AJAXMETHOD: 'POST',
	IDLETIMEOUT: 900,
	LANG_CONF : {
		"zh-cn" : "zh_CN",
		"zh-tw" : "zh_TW",
		"en-us" : "en",
		"cs" : "cs",
		"sk" : "sk",
		"de" : "de"
	},
	WEEK : ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday",
	        "Saturday", "Sunday"],
	TENANTUUID : "",		//判断登录账户归宿
	// 任务状态
	TASK_STATUS :{
        UNKNOWN:0,         		//未知的任务状态
        WAITTING:1,       		//任务等待运行
        RUNNING:2,         		//任务正在运行
        PAUSED:3,          		//任务暂停
        STOPPED:4,         		//任务停止
        STOPPING:5,        		//任务停止中
        NETWORK_FAULT:6,   		//网络故障
        ABNORMAL:7,        		//任务已完成但异常
        ERROR:8,           		//错误
        SYNC:9,					//任务同步
        PREPARING:10,			//准备中
        PAUSING:11,	    		//任务暂停中
        STARTING:12,        	//启动中
        FINISHED:13,       		//已完成
        TAKEOVER:14,       		//接管
        TAKEOVER_STARTING:15,   //启动接管
        TAKEOVER_STOPPING:16,   //停止接管
        SUCCESSED:17,			//任务成功
        CREATING:18,			//任务创建中
		PENDING:19,				//任务挂起
		DELETING:20,			//删除中
		CLEANING:21, 			//清理中
	},

	TASK_STATUS_DES: [],

	//任务类型 
    TASK_TYPE :{
    	BACKUP:1,
    	RECOVERY:2,
    	DISK_BACKUP:3,
    	DISK_RECOVERY:4,
    	VM_FILE_BACKUP:5,
    	VM_FILE_RECOVERY:6,
    	VM_INSTANT_RECOVERY:7,
    	VM_INSTANT_RECOVERY_MOTION:8,
    	VM_REPLICATION:9,
    	SYNC:10,
    	ORCH_TASK:11,
    	BACKUP_EXPORT:12,
    	VM_CDP_BACKUP:13,
    	VM_CDP_RECOVERY:14,
    	VM_CDP_INSTANT_RECOVERY:15,
    	VM_CDP_INSTANT_RECOVERY_MOTION:16,
    	BACKUP_COPY:17,
    	BACKUP_COPY_FETCH:18,
    	ARCHIVE:19,
    	ARCHIVE_FETCH:20,
    	DB_CDP_BACKUP:21,
    	DB_CDP_RECOVERY:22,
    	DB_CDP_TAKEOVER:23,
    	FILE_CDP_BACKUP:24,
    	FILE_CDP_RECOVERY:25,
    	FILE_BACKUP_COPY:26,
    	FILE_BACKUP_COPY_FETCH:27,
    	DB_BACKUP:28,
    	DB_RECOVERY:29,
    	DB_BACKUP_COPY:30,
    	DB_BACKUP_COPY_FETCH:31,
    	VOL_CDP_BACKUP:32,	//卷CDP备份
    	VOL_CDP_RECOVERY:33,	//卷CDP恢复    
    	VOL_CDP_TAKEOVER:34,	//卷CDP接管
    	OS_BACKUP:35,	//OS备份
    	OS_RECOVERY:36,	//OS恢复 
    	SURE_BACKUP:37,	//虚拟机数据验证
    	OS_BACKUP_COPY:38,		//OS副本
    	OS_BACKUP_COPY_FETCH:39,	//OS副本回传
		OS_BACKUP_ARCHIVE: 40,  //OS归档
		OS_BACKUP_ARCHIVE_FETCH: 41,  //OS归档回传
		NAS_BACKUP: 42,  //NAS备份（暂时没用）
		NAS_RECOVERY: 43,  //NAS恢复（暂时没用）
		NAS_BACKUP_COPY: 44,  //NAS副本
		NAS_BACKUP_COPY_FETCH: 45,  //NAS副本回传
		DB_CDP_SYN: 46,  //数据库实时同步
		DB_CDP_RECOVER: 47,  //数据库实时恢复
		OS_INSTANT_RECOVERY:49,  //操作系统瞬时恢复 
		OS_INSTANT_RECOVERY_MOTION:50, //操作系统在线迁移
		VM_HUAWEI_CBR_SYNC: 51, //华为CBR同步任务
		PLATFORM_RECOVERY: 52, // 跨平台恢复 52
		INSTANT_RECOVERY: 53, // 瞬时恢复 53
		INSTANT_RECOVERY_MOTION: 54, // 迁移 54
		GRAIN_RECOVERY: 55, // 细粒度恢复 55
		KUBE_BACKUP: 56, // K8s备份
		KUBE_RECOVERY: 57, // k8s恢复
		FILE_COPY: 62, //文件复制
		FILE_COMPARE: 63, //文件对比
		DRILL: 64, //演练
		VOL_CDP_REPLICATION: 65, //整机复制
    },

	TASK_TYPE_DES: [],

	// 任务控制项
	TASK_CONTROL: {
		START: 1, // 启动任务
		STOP: 2, // 停止任务
		MODIFY: 3, // 修改任务
		DELETE: 4, // 删除任务
		PAUSE: 5, // 暂停任务
		START_DIFF: 6, // 启动差异
		START_INCR: 7, // 启动增量
		START_STRATEGY: 8, // 启动策略
		MIGRATION: 9, // 迁移
		START_FULL: 10, // 启动完备
		START_TAKEOVER: 11, // 启动接管
		STOP_TAKEOVER: 12, // 停止接管
		LOG_BACKUP: 13, // 日志备份
		START_FAILBACK: 14, // 启动回切
		STOP_FAILBACK: 15, // 停止回切
		CONTINUE: 16, // 继续备份
		AUTO_TAKEOVER: 17, // 自动接管
		START_COPY: 18, // 启动复制
		START_COMPARE: 19, // 启动对比
		FORCE_DELETE: 20, // 强制删除
		FORCE_STOP: 21, // 强制停止
		STOP_MOTION: 22, // 停止迁移
		FINISH_MOTION: 23 // 完成迁移
	},

	FLAG:{
    	UNKNOW:0,
    	SET:1,
    	UNSET:2
    },

	//加密公钥
	PUBLIC_KEY : '-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCq/O2gNw8H3RNTCfJlXYWTPX3JmTeKoPr6lCaHpEBgBg9FTt7Ftu+zYVaPtBLtPVYpxXzHdnjrkFThCuI30TZnh23RbU92Ap67IV6V0DqL3gQRPVsEgRCeH7Uwsvt5/YFZTgUT/V+hW5+Hq6fjMf8ghaAFDro/vHElH2FAevLy0wIDAQAB-----END PUBLIC KEY-----',
	//当前语言
	LANGUAGE: "",
    //软件版本定义
    SOFTWARE_VERSION:{
    	UNKNOWN: 0,
        STANDARD: 1,                //标准版*************
        ENTERPRISE: 2,              //企业版*************
        ADVANCE_ENTERPRISE: 3,      //企业增强版-
        EN_FREE_EDITION: 4,         //免费版(英文)*********
        ESSENTIAL: 5,               //基础版-
        STANDARD_EN: 6,             //标准版(英文)*********
        ENTERPRISE_EN: 7,           //企业版(英文)*********
        ADVANCE_ENTERPRISE_EN: 8,   //企业增强版(英文)-
        ESSENTIAL_EN: 9,            //基础版(英文)*********
        FREE_EDITION: 10,            //免费版-
        ENTERPRISE_EN_LR: 11,        //企业版(英文)促销*********
        DIY_VERSION: 100            //自定义版本
    },
	 //策略组类型备份
	POLICY_TYPE_BACKUP :{
		VIRTUAL_MACHINE : 1,
    	FILE : 2,
        DATABASE : 3,
        OPERATING_SYSTEM : 4
	},
	//副本子模块
	BACKUP_COPY_MODULE :{
		UNKNOWN: 0,
		VM: 1,	//虚拟机
		FS: 2,	//文件
		DB: 3,	//数据库
		OS: 4,	//操作系统	
		NAS: 5	//NAS
	},
	//用户权限
	PERMISSION: [],
	// 用户权限树（包括操作的）
	PERMISSION_ARR: [],

	//访问接口主机IP地址
	HOST: "",

	//存储类型
	BD_STORAGE_TYPE:{
		UNKNOWN : 0,
		DISK : 1,        //local disk
		LVM : 2,         //lvm
		PARTITION : 3,   //local partition
		FC : 4,          //storage area network FC
		ISCSI : 5,       //storage area network iSCSI
		NFS : 6,         //network attached storage NFS
		CIFS : 7,        //network attached storage CIFS
		REMOTE : 8,		//storage type remote system
		CLOUD : 9,		//cloud type storage
		TAPE : 10,		//tape storage
		LOCALDIR : 11,   //Local dir
		HUAWEICBR:12,      //华为CBR
		DDDB:16,      //dddb
	},
	// 存储类型描述
	STORAGE_TYPE_DES: {},
	// 存储设备状态
	STORAGE_STATUS: {
		ONLINE: 1,  // 在线
		CREATING: 2,  // 创建中
		OFFLINE: 3,  // 离线
		UNMOUNT: 4,  // 未挂载
		WARNING: 5,  // 警告
	},

	// 备份方式
	TIME_STRATEGY_MODE: {
		FULL: 1, //完全备份
		INCR: 2, //增量备份
		DIFF: 3, //差异备份
		DB_LOG: 4, // 日志备份
		PER_INCR: 9, //永久增量
	},

	// 任务编排计划状态
	ORCHESTRATION_PLAN_STATUS : {
		UNKNOWN: 0,
		WAITING: 1,
		RUNNING: 2,
		PAUSED: 3,
		STOPPED : 4,
		ERROR : 5,
	},
	// 任务编排阶段状态
	ORCHESTRATION_SECTION_STATUS : {
		UNKNOWN: 0,
		WAITING: 1,
		RUNNING: 2,
		FINISHED: 3,
		STOPPED : 4 ,
	},

	STORE_ENCRYPT_METHOD: {
		AES: 1,
		SM4: 2,
	},
	TASK_LEVEL: {
		NORMAL: 1,
		FIRST: 2,
		HIGH: 3,
	},
	COMPRESS_METHOD: {
		FASTER: 1,
		NORMAL: 2,
		BETTER: 3,
		BEST: 4,
	},
	RECOVER_POLICY: {
		DIRECT_COVER: 1, //直接恢复
		VIRUS: 2, //杀毒后恢复
		SCAN: 3, //执行扫描
		DIRECT_TAKE: 4,//直接接管
	},
	INTERRUPT: {
		STOP: 1,//扫出病毒后停止(验证任务停止检测，恢复任务停止恢复)
		NO_INTER: 2,//扫出病毒后恢复到无网络环境
        KILL_COVER: 3,//扫出病毒后杀除并恢复
		TAKE_ONINTER: 4,//扫出病毒后接管到无网络环境
		KILL_TAKE: 5,//杀除后接管
	},
	CHECK_TIME: {
		DAY: 1,//每天一次
		WEEK: 0, //每周一次
		EVERY: 2,//每次备份
	},
	FULL_ABNORAL: {//完全备份点异常
		REFULL: 1,//将整链标记为损坏，并重做完备
		STOPBACKUP: 0,//中止备份
	},
	OTHER_ABNORAL: {
		REFULL: 1,//重做完备（将对应差异点/增量点标记为损坏）
		REINC: 2,//重做增量（删除无效差异点/增量点，并基于最新有效点做目标端增量）
		STOPBACKUP: 0,//中止备份
	},
	OPERATION_STATUS: {
        NO_OPERATION: 0,  // 未操作
        MERGING: 1,  // 合并中
        SCANNING: 2,  // 扫描中
        DELETING: 4,  // 删除中
        CHECKING: 8,  // 校验中
	},
    VIRUS_STATUS: {
        NO_SCAN: 0, // 未扫描
        SCANNING: 1, // 扫描中
        SAFE: 2, // 健康
        INFECTED: 3, // 感染
        INFECTED_PARTLY_SCAN: 4, // 感染但未扫描完成
	},
    INTEGRITY_STATUS: {
        NORMAL: 0, // 正常
        BROKEN: 1, // 损坏
        NO_CHECK: 2, // 未检查'
	},
    MERGE_STATUS: {
        NORMAL: 0, // 正常
        WAITING: 1, // 待合并
        MERGING: 2, // 合并中
        FAILED: 3, // 失败
        DEPEND_MERGE_FAILED: 4, // 依赖点合并失败
        ROLL_BACK: 5, // 回滚中
	},
	// 时间点状态
	POINT_STATUS : {
		UNKNOWN: 0, // 未操作、未知
		OPERATING: 1, // 操作中
		ABNORMAL: 2, // 异常
		NORMAL: 3, // 正常
	},
	//存储用途
	BD_STORAGE_USE_MODE: {
		UNKNOWN: 0,
		BACKUP: 1,  //备份
		COPY: 2,    //副本
		ARCHIVE: 3, //归档
		NAS: 4, //NAS
		READ_ONLY: 5, //只读
	},

	// 存储位置类型（用于区分整机/卷是持续数据保护还是数据复制）
	STORAGE_LOCATION_TYPE: {
		BACKUP: 1, // 备份（数据在备份服务器）
		COPY: 3 // 复制（数据在备份服务器和备机都存在）
	},

	// 系统层级关系 - 第一层：业务类型（1：数据备份 2：连续数据保护 3：数据复制）
	SYSTEM_BUSINESS_TYPE: {
		DATA_BACKUP: 1,
		CONTINUOUS_DATA_PROTECT: 2,
		DATA_COPY: 3
	},

	// 第二层：模块类型对应值map（用于任务过滤器和任务类型的级联）
	SYSTEM_MODULE_TYPE_TO_VALUE_MAP: {
		DATA_BACKUP_ALL: '1-0',
		VM: '2-1',
		PRIVATE_CLOUD: '2-2',
		PUBLIC_CLOUD: '2-3',
		COMPLETE_MACHINE_DISK: '5-1',
		COMPLETE_MACHINE_VOLUME: '5-0',
		FILE: '3-1',
		NAS: '11-2',
		HADOOP: '3-3',
		OBS: '3-4',
		DB: '4-0',
		M365: '14-0',
		REAL_TIME_DATA_PROTECT_ALL: '2-0',
		REAL_TIME_COMPLETE_MACHINE_DISK: '10-1-2',
		REAL_TIME_COMPLETE_MACHINE_VOLUME: '10-1-1',
		DATA_COPY_ALL: '3-0',
		DATA_COPY_COMPLETE_MACHINE_DISK: '10-0-4',
		DATA_COPY_COMPLETE_MACHINE_VOLUME: '10-0-3',
		DATA_COPY_DB: '12-0',
		DATA_COPY_FILE: '26-0',
		KUBERNETES: '28-0'
	},

	// 第三层：任务类型对应的map值（只用于过滤器）
	SYSTEM_MODULE_TASK_TYPE_TO_VALUE_MAP: {
		BACKUP: '1,28,32,35', // 备份、数据库备份、卷CDP备份、OS备份
		RECOVER: '2,29,33,36,47', // 恢复、数据库恢复、卷CDP恢复、OS恢复、数据库实时恢复
		COPY: '17,30,38', // 副本、数据库副本、OS副本
		ARCHIVE: '19,40', // 归档、OS归档
		TAKEOVER: '34', // 接管
		TRANSFER: '54', // 迁移
		INSTANCE_RECOVER: '7,49,53', // 虚拟机瞬时恢复、操作系统瞬时恢复、卷瞬时恢复
		GRAIN_RECOVER: '55', // 细粒度恢复
		CROSS_PLATFORM_RECOVER: '52', // 跨平台恢复
		DATA_VERIFY: '37', // 数据验证
		DATA_COPY: '46,62,65', // 数据库复制、文件复制、整机复制
		COMPARE: '63', // 文件对比
		DRILL: '64', // 数据库演练
	},

	// 历史任务状态枚举(该枚举只用于历史任务过滤传参使用)
	HISTORY_TASK_STATUS: {
		SUCCESSED: 1, // errorCode 0
		SUSPEND: 2, // errorCode 45
		ABNORMAL: 3, // errorCode 47
		ERROR: 4 // errorCode not in [0,45,47]
	},

	// 任务类型对应值map（用于当前任务和历史任务过滤器）
	SYSTEM_TASK_TYPE_TO_VALUE_MAP: {
		BACKUP: '1,28,32,35,56',
		RECOVER: '2,29,33,36,47,57',
		COPY: '17,30,38',
		ARCHIVE: '19,40',
		TAKEOVER: '34',
		MOTION: '54',
		INSTANT_RECOVER: '7,49,53',
		GRAIN_RECOVER: '55',
		CROSS_PLATFORM_RECOVER: '52',
		DATA_VERIFY: '37',
		DATA_COPY: '46,62,65',
		COMPARE: '63',
		DB_DRILL: '64'
	},

	// <----------------------------------------------- END PUBLIC CONFIG ----------------------------------------------->


	// <----------------------------------------------- BEGIN VM CONFIG ------------------------------------------------->

	VMTYPE_GROUP : {
		VMWARE : [1, 7, 21],
		XENSERVER : [3, 8, 9, 18, 20, 25, 28],
		KVM : [4, 10, 11, 12, 13, 14, 15, 19, 22],
		OPENSTACK : [14, 15, 22, 31, 34, 35, 36, 37, 38, 39, 54],
		HUAWEIKVM : [16, 43],
		REDHAT: [19, 29, 41, 45, 49, 50, 52],
		PUBLICCLOUD : [100, 101],
		PRIVATECLOUD: [14, 15, 22, 26, 30, 31, 34, 35, 36, 37, 38, 39, 54],
	},

	// 虚拟机子模块定义
	VM_SUB_MODULE: {
		UNKNOWN: 0,
		VM: 1,
		PRIVATE_CLOUD: 2,
		PUBLIC_CLOUD: 3,
	},

	// 支持高速模式的虚拟化
	HIGH_MODE_HYPERVISORS: [
		2, 3, 8, 9, 10, 11, 12, 14, 15, 18, 19, 20, 22, 23, 24, 25, 26, 27, 28, 29, 30,
		31, 32, 33, 34, 35, 36, 37, 38, 39, 41, 42, 44, 45, 48, 49, 50, 52, 54
	],
	// 支持iscsi的瞬时恢复的虚拟化类型
	// 华三(kvm) ：11 (平台支持，后台暂时不支持，屏蔽掉)
	// 华三cvd(kvm)：51 (平台支持，后台暂时不支持，屏蔽掉)
	// 浪潮vddk(kvm)：40(平台支持，后台暂时不支持，屏蔽掉)
	// 联想(kvm)：53(平台支持，后台暂时不支持，屏蔽掉)
	// 云宏(kvm)：32(平台支持，后台暂时不支持，屏蔽掉)
	// ovirt系(kvm)、ovirt系imageio(kvm)：19, 29, 41, 45, 49, 50, 52(平台支持，后台暂时不支持，屏蔽掉)
	// vserver(kvm)：27(平台支持，后台暂时不支持，屏蔽掉)
	// proxmox(kvm)：42
	// 内嵌：108
	SUPPORT_ISCSI_INSTANT: [
		42, 108
	],

	// 虚拟机状态
	VM_STATUS_TYPE: {
		OFF: 1,
		ON: 2,
		SUSPEND: 3,
		PAUSE: 4
	},

	// <----------------------------------------------- END VM CONFIG --------------------------------------------------->

	// <----------------------------------------------- BEGIN FS CONFIG ------------------------------------------------->

	// 文件子模块定义
    SUBMODULE_TYPE: {
        UNKNOWN: 0,
        FS: 1,
        NAS: 2,
        HADOOP: 3,
        OBS: 4
	},

	// <----------------------------------------------- END FS CONFIG ------------------------------------------------->

	// <----------------------------------------------- BEGIN DB CONFIG ------------------------------------------------->

	// 数据库类型 NOTE: 这里定义只是js显示，取值不等于实际值，实际值是api/xphp/conf/config.php的DB_TYPE
	DB_TYPE: {
		UNKNOW : 0,
		SQLSERVER : 1,
		ORACLE : 2,
		MYSQL : 3,
		DM : 4,
		POSTGRE : 5,
		KINGBASE : 6,
		UXDB : 7,
		HIGHGO : 8,
		MARIA : 9,
		OPENGAUSS : 10,
		VASTBASE : 11,
		ANTDB : 12,
		SAPHANA : 13,
		TIDB : 14,
		MONGODB : 15,
	},

	DB_TYPE_MAP: {
		1: 'SQL Server',
		2: 'Oracle',
		3: 'MySQL',
		4: 'DM',
		5: 'PostgreSQL',
		6: 'KingbaseES',
		7: 'UXDB',
		8: 'Highgo DB',
		9: 'MariaDB',
		10: 'openGauss',
		11: 'Vastbase',
		12: 'AntDB',
		13: 'SAP HANA',
		14: 'TiDB',
		15: 'MongoDB'
	},

	DB_DES : [],

	// <----------------------------------------------- END DB CONFIG --------------------------------------------------->


	// <----------------------------------------------- BEGIN REPORT CONFIG ---------------------------------------------->

	// 报表对应模块类型
	REPORT_TYPE: {
		TASK: 1,
		STORAGE: 2,
		CLIENT: 3,
		NAS: 4,
		VM: 5,
		FILE: 6,
		DB: 7,
		OS: 8,
		VOL_CDP: 9,
		PUBLIC_CLOUD: 10,
		PRIVATE_CLOUD: 11,
		OBS: 12,
		HADOOP: 13,
		FILECOPY: 14,
		M365: 15,
		K8S: 16
	},

	// 报表任务趋势运行时间选择类型
	RUNNING_TIME_TYPE: {
		LAST_DAY: 1,
		LAST_THREE_DAYS: 2,
		LAST_WEEK: 3,
		LAST_MONTH: 4
	},

	// 虚拟化类型
    VM_HYPERVISOR_TYPE: {
        VMWARE: 1,
        MICROSOFT_HYPER_V: 2,
        INCLOUD_SPHERE_KVM: 24,
        VVDK: 40
    },

	// 备份状态类型
    BACKUP_STATUS_TYPE: {
        PROTECTED: 1,
        UNPROTECTED: 0
    },

	OS_TYPE: {
		WINDOWS: 'Windows',
		LINUX: 'Linux',
	},

	// 客户端状态类型，第一个数字代表在线状态 1：在线；2：离线；第二个数字代表plugin_deploy_status 1：部署中；2：部署成功；3：部署失败；4：升级中；5：升级成功；6：升级失败；9：AccessKey失效
	CLIENT_STATUS_TYPE: {
		ONLINE_DEPLOYING: '11', // 在线部署中
		OFFLINE_DEPLOYING: '21', // 离线部署中
		ONLINE_DEPLOY_SUCCESS: '12', // 在线部署成功
		OFFLINE_DEPLOY_SUCCESS: '22', // 离线部署成功
		OFFLINE_DEPLOY_FAILED: '23', // 离线部署失败
		ONLINE_UPGRADING: '14', // 在线升级中
		OFFLINE_UPGRADING: '24', // 离线升级中
		ONLINE_UPGRADE_SUCCESS: '15', // 在线升级成功
		OFFLINE_UPGRADE_SUCCESS: '25', // 离线升级成功
		ONLINE_UPGRADE_FAILED: '16', // 在线升级失败
		OFFLINE_UPGRADE_FAILED: '26', // 离线升级失败
		ONLINE_ACCESSKEY_INVALID: '17', // 在线AccessKey无效
		OFFLINE_ACCESSKEY_INVALID: '27', // 离线AccessKey无效
	},

	REPORT_NODE_STATUS_TYPE: {
		NORMAL: 1,
		ABNORMAL: 2
	},

	NAS_STATUS_TYPE: {
		ONLINE: 1,
		ABNORMAL: 2,
		OFFLINE: 3
	},

	OBS_STATUS_TYPE: {
		ONLINE: 1,
		OFFLINE: 0
	},

	// <----------------------------------------------- END REPORT CONFIG ------------------------------------------------>


	// <----------------------------------------------- BEGIN RESOURCE CONFIG -------------------------------------------->

	SOURCE_FROM : {
		RESOURCE_GROUP: 1,
		USER: 2,
		USER_GROUP: 3
	},

	// <----------------------------------------------- END RESOURCE CONFIG ---------------------------------------------->


	// <----------------------------------------------- BEGIN TIMMING CONPLETE MACHINE CONFIG ------------------------------------->

	//卷CDP 运行阶段
	CDP_TASK_RUNNING_STAGE:{
		UNKNOWN:0, 						//未知状态
		WAIT_EXEC:1, 					//等待
		INIT_SYNC:20, 					//初始化同步
		REALTIME_SYNC:21, 				//备份实时同步
		WAIT_CONVERT_TO_REALTIME_SYNC:22, //等待任务切换到实时同步
		SERVER_CONS_CHECK:40, 			//服务端的数据一致性校验
		STANDBY_CONS_CHECK:41, 			//备机的数据一致性校验
		SERVER_REEALTIME_CONS_CHECK:42, //服务端实时数据校验
		IN_TAKEOVER:60, 				//接管中
		IN_TAKEOVER_STARTING:61,		//接管启动中
		FAILBACK_INIT_SYNC:80, 			//逆向初始同步
		FAILBACK_REALTIME_SYNC:81, 		//逆向实时同步
		FAILBACK_IN_STARTING:82,		//回切启动中
	},

	//CDP备机类型
	CDP_STANDBY_HOST_MODE:{
		UNKNOWN:0, 						//未知模式
		PROXY_CLIENT:1,					//代理客户端
		OTHER_VM_MACHINE:2,				//第三方虚拟化
		// REALTIME_SYNC_OR_COPY:3,		//实时同步+实时复制
		DISASTER_RECOVERY_DRILL_PLAT:3,	//容灾演练平台
	},

	//代理类型
	BD_AGENT_TYPE:{
		BD_AGENT_TYPE_UNKNOWN:0,// unknown agent type
		BD_AGENT_TYPE_NORMAL:1,  // normal
		BD_AGENT_TYPE_MEMORY_OS:2, // memory operating system(livecd/winpe)
		BD_AGENT_TYPE_NAS:3,// nas
		BD_AGENT_TYPE_APPLIANCE:4,// appliance
		BD_AGENT_TYPE_TEMP_AGENT:5,
	},

	//模板代理角色
	TEMP_AGENT_ROLE:{
		TEMP_AGENT_ROLE_UNKNOW: 0,
		TEMP_AGENT_ROLE_DRILL:1,
		TEMP_AGENT_ROLE_TAKEOVER:100,
		TEMP_AGENT_ROLE_VERIFY:101,
	},

	//内嵌应用场景
	EMD_VM_ROLE:{
		EMD_VM_ROLE_UNKNOW: 0,
		EMD_VM_ROLE_DRILL: 99,  //实时验证验证
		EMD_VM_ROLE_TAKEOVER: 100,  //实时接管
		EMD_VM_ROLE_VIRTUAL_LAB:101,
		EMD_VM_ROLE_MANUAL_VERIFY: 102,
		EMD_VM_ROLE_AUTOMATIC_VERIFY:103,
	},

	// <----------------------------------------------- END TIMING CONPLETE MACHINE CONFIG --------------------------------------->



	// <----------------------------------------------- BEGIN REAL TIME CONPLETE MACHINE CONFIG ------------------------------------->

	//数据库实时 运行阶段
	DBCDP_TASK_RUNNING_STAGE:{
		UNKNOWN:0, 						//未知状态 --
		WAIT_EXEC:1, 					//等待
		DICT_EXPORT:10, 				//数据字典导出 初始同步
		DICT_IMPORT:11, 				//数据字典导入 初始同步
		FULL_SYNC:20, 			        //全量数据同步 初始同步
		LOG_SYNC:21, 			        //日志数据同步 实时同步
		CONSTRAINT_IMPORT:22, 			//日志数据同步 约束导入
		IN_TAKEOVER_STARTING:30, 		//接管启动中
		IN_TAKEOVER:31,		            //接管中
		FAILBACK_DICT_EXPORT:40, 	    //回切数据字典导出 逆向初始同步
		FAILBACK_DICT_IMPORT:41, 		//回切数据字典导入 逆向实时同步
		FAILBACK_FULL_SYNC:50,		    //回切全量数据同步 逆向初始同步
		FAILBACK_LOG_SYNC:51,           //回切日志数据同步 逆向实时同步
		SERVICE_FAILBACK:52,			//回切生产业务
		FAILBACK_SUCCESSED:53,			//回切完成
		FAILBACK_CONSTRAINT_IMPORT:54, 	//回切约束导入
		RESTORE_IN_RUNNING:60,          //恢复运行中
	},

	//数据库实时 任务状态
	DBCDP_TASK_STATUS:{
		UNKNOWN:0,         		//未知的任务状态
		WAITTING:1,       		//任务等待运行
		RUNNING:2,         		//任务正在运行
		PAUSED:3,          		//任务暂停
		STOPPED:4,         		//任务停止
		STOPPING:5,        		//任务停止中
		NETWORK_FAULT:6,   		//网络错误
		ABNORMAL:7,        	    //任务已完成但异常
		ERROR:8,           		//任务失败
		SYNC:9,					//任务同步
		PREPARING:10,			//准备中
		PAUSING:11,	    		//任务暂停中
		STARTING:12,        	//启动中
		FINISHED_THIRD:13,		//三方
		TAKEOVER_THIRD:14,		//接管三方
		TAKEOVER_STARTING_THIRD:15, //启动接管三方
		TAKEOVER_STOPPING_THIRD:16, //停止接管三方
		SUCCESSED:17,			    //任务成功
	},

	//卷实时备份模式
	CDP_BACKUP_MODE:{
		UNKNOWN:0, 						//未知模式
		REALTIME_SYNC:1,				//实时同步.
		REALTIME_COPY:2,				//实时复制
		// DISASTER_RECOVERY_DRILL_PLAT:3,	//实时同步+实时复制
		REALTIME_SYNC_OR_COPY:3,		//实时同步+实时复制
	},
	
	//磁盘和卷设备类型定义
	VOLUME_TYPE :{
        BD_VOLUME_TYPE_UNKNOWN:0x0000,
        BD_BASIC_PARTITION:0x0001,
        BD_GPT_PARTITION:0x0002,
        BD_DYNAMIC_VOLUME: 0x0004,
        BD_LVM_VOLUME : 0x0008,
        BD_EFI_VOLUME : 0x0010,			    //EFI system partition.
        BD_BOOT_VOLUME : 0x0020,			//Bios boot partition.
        BD_SYSTEM_VOLUME : 0x0040,			//Partitions for operating system load system data
        BD_WIN_RESERVE_VOLUME : 0x0080,		//Windows
        BD_WIN_RECOVERY_VOLUME : 0x0100,	//Windows
        BD_HIDDEN_VOLUME : 0x0200,			//
        BD_PV_VOLUME : 0x0400,
        BD_DMRAID_VOLUME : 0x0800,
        BD_MPATH_VOLUME : 0x1000,
        BD_EXTEND_VOLUME : 0x2000,           //MBR extend partition.
        BD_LOGIC_VOLUME : 0x4000,            //MBR logic partition.
        BD_REMOVABLE_VOLUME : 0x8000,       //removable device
        BD_WIN_FIRMWARE_BOOT_VOLUME : 0x10000,  //current windows real boot volume
        BD_SWAP_VOLUME : 0x20000,    //Swap partition
    },
	// <----------------------------------------------- END REAL TIME CONPLETE MACHINE CONFIG ------------------------------------->


	// <----------------------------------------------- BEGIN SYSTEM CONFIG ------------------------------------->

	UPDATE_PATCH_STATUS: {
        'BD_PATCH_STATUS_UNKNOWN': 0,
        'BD_PATCH_STATUS_UPLOAD': 1, // 已上传, 等待升级中
        'BD_PATCH_STATUS_PATCH_FAILED': 2, // 补丁包已同步到子节点，但安装失败
        'BD_PATCH_STATUS_UPLOAD_FAILD': 3, // 补丁包上传失败
        'BD_PATCH_STATUS_PATCH_DONE': 4, // 补丁包已安装
        'BD_PATCH_STATUS_SYNC_TO_NODE': 5, // 正在上传补丁包到节点
        'BD_PATCH_STATUS_UPDATE_WAITTING': 6, // 补丁包等待升级中
        'BD_PATCH_STATUS_PATCH_UPDATING': 7, // 补丁包升级中
        'BD_PATCH_STATUS_PATCH_INVALID': 8, // 补丁包无效
        'BD_PATCH_STATUS_PATCH_CHECKING': 9, // 正在检测补丁包
        'BD_PATCH_STATUS_PATCH_VERIFIED': 10, // 补丁包已验证通过
        'BD_PATCH_STATUS_SPACE_NOT_ENOUGH_TO_UNCOMPRESS': 11, // 系统空间不足以完成补丁包解压
        'BD_PATCH_STATUS_SPACE_NOT_ENOUGH_TO_DOWNLOAD': 12, // 系统空间不足以下载补丁包
		'BD_PATCH_STATUS_DOWNLOAD_PATCH_FAILED': 13 // 下载补丁包失败
    },

	FUNCTIONS: {}, // 授权功能

	// <----------------------------------------------- END SYSTEM CONFIG --------------------------------------->

	CPU_MODE: {
		2: 'host-model',
		3: 'host-passthrough',
		4: 'EPYC',
	},
	CPU_MODE_EMD: {
		3: 'host-model',
		4: 'host-passthrough',
		8: 'EPYC',
	},
	VIRUS_HISTORY_STATUS:{
		'DETECT_STATUS_NONE': 0,
		'DETECT_STATUS_RUNNING': 1,         // 扫描中 | 暂未使用
		'DETECT_STATUS_INTERRUPTED': 2,   // 扫描中止 | 检测病毒立即停止或者检测任务被停止会使用该字段
		'DETECT_STATUS_COMPLETED': 3,     // 扫描完成 | 正常完成扫描
		'DETECT_STATUS_FAILED': 4,       // 扫描失败 | 因某些原因扫描出错，具体原因可解析error_code
	},

	// 虚拟机任务状态
	VM_TASK_STATUS: {
		UNKNOWN: 0,
		WAITTING: 1,
		RUNNING: 2,
		FINISH: 3,
		ERROR: 4,
		NEW_ADD: 5,
		PAUSED: 6,
	},

	// 全局观察者权限类型
	GLOBAL_OBSERVER_AUTH_TYPE: {
		NON_ASSIGN_PERMISSION: 1, // 非分配权限
		ASSIGN_PERMISSION: 2, // 分配权限
	}
};

var LANG = {};