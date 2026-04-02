<?php 
/**
 * API返回码,考虑到不和系统错误码冲突,从80000开始
 */
return array(
    'codes' => array(
        /*********一般返回**********/
        80000 => 'API_CODE_SUCCESS',        //成功
        'API_CODE_ERROR',                   //错误
        'API_CODE_PARAMS_ERROR',            //参数错误
        'API_CODE_USER_ERROR',              //API用户错误,请使用admin用户
        'API_CODE_SYSTEM_SERVICE_ERROR',    //后台程序未启动或异常退出
        'API_CODE_HEADER_ERROR_401',        //未授权，access_token认证失败
    	'API_CODE_HEADER_ERROR_404',        //url路径错误
    	'API_CODE_HEADER_ERROR_405',        //方法内部错误
    	'API_CODE_HEADER_ERROR_406',        //accept错误
        
        /*********Access Token**********/
        81001 => 'API_CODE_ACCESS_TOKEN_LOGIN_ERROR',   //登录失败,账号或密码错误
        'API_CODE_ACCESS_TOKEN_NOT_AVAILABLE',          //Aceess Token不可用
        'API_CODE_ACCESS_TOKEN_TIMEOUT',                //Aceess Token超时
        'API_CODE_ACCESS_TOKEN_NOT_AVAILABLE_ERROR',    //Aceess Token不可用
        'API_CODE_ACCESS_TOKEN_REQUEST_IP_NOT_SUPPORT',         //不支持的客户端ip
        'API_CODE_ACCESS_TOKEN_TARGET_IP_NOT_SUPPORT',         //不支持的服务端ip
        
        /*********Alarms**********/
        82001 => 'API_CODE_ALARMS_DELETE_SYSTEM_ALARM', //删除系统告警
        'API_CODE_ALARMS_DELETE_JOB_ALARM',           //删除任务告警
        'API_CODE_ALARMS_DELETE_SYSTEM_ALARM_ERROR',    //系统告警id未找到
        'API_CODE_ALARMS_DELETE_JOB_ALARM_ERROR',      //任务告警id未找到
        'API_CODE_ALARMS_RESPONSE_JOB_ALARM',			//响应任务告警
    	'API_CODE_ALARMS_RESPONSE_SYSTEM_ALARM',        //响应系统告警
		'API_CODE_ALARMS_GET_JOB_ALARM_LIST',          //获取任务告警列表
		'API_CODE_ALARMS_GET_SYSTEM_ALARM_LIST',        //获取系统告警列表
        'API_CODE_ALARMS_GET_UNSOLVED_ALARM_NOTICE',         //获取未响应的告警数量统计
    		
        /*********Jobs**********/
        83001 => 'API_CODE_JOBS_NOT_FIND_VM',           //没有找到对应的虚拟机
        'API_CODE_JOBS_CREATE_BACKUP',                  //创建备份任务
        'API_CODE_JOBS_CREATE_RECOVERY',                //创建恢复任务
        'API_CODE_JOBS_CREATE_INSTANT',                 //创建瞬时恢复任务
        'API_CODE_JOBS_CREATE_MOTION',                  //创建迁移任务
        'API_CODE_JOBS_EDIT_BACKUP',                    //修改备份任务
        'API_CODE_JOBS_START_FULL_BACKUP',              //启动完全备份任务
        'API_CODE_JOBS_START_INCR_BACKUP',              //启动增量备份任务
        'API_CODE_JOBS_START_DIFF_BACKUP',              //启动差异备份任务
        'API_CODE_JOBS_START_RECOVERY',                 //启动恢复任务
    	'API_CODE_JOBS_START_INSTANT_RECOVERY',         //启动瞬时恢复任务
        'API_CODE_JOBS_STOP',                           //停止任务
        'API_CODE_JOBS_DELETE',                         //删除任务
        'API_CODE_JOBS_NOT_FIND_JOB',                   //没有找到对应的任务
        'API_CODE_JOBS_JOB_NAME_EXISTS',                //任务名已经存在
        'API_CODE_JOBS_GET_UUID',                       //获取任务uuid
        'API_CODE_JOBS_TIME_STRATEGY_ERROR',             //增量差异只能选一个
        'API_CODE_JOBS_CREATE_MOTION_ERROR',             //创建迁移任务失败
        'API_CODE_JOBS_EDIT_ERROR',						//修改备份任务不匹配
        'API_CODE_JOBS_EDIT_BACKUP_ERROR',              //修改备份任务更新数据库错误
        'API_CODE_JOBS_JOB_UUID_NOT_EXIST',            //修改时传入的任务uuid不存在
        'API_CODE_JOBS_STORAGE_BLOCK_SIZE_ERROR',       //备份存储块大小值不对
        'API_CODE_JOBS_VM_NAME_NOT_AVAILABLE',          //虚拟机名不可用
    	'API_CODE_JOBS_CREATE_TIME_FORMAT_ERROR',       //时间格式不正确	
    	'API_CODE_JOBS_CREATE_TIME_NOT_CORRECT',        //时间不正确	
    	'API_CODE_JOBS_TIMEPOINT_UUID_NOT_EXIST',       //检查备份时间点是否存在	
    	'API_CODE_JOBS_WEEK_DAYS_LENGTH_ERROR',         //检查时间策略每周选择日子长度错误
		'API_CODE_JOBS_MONTH_DAYS_LENGTH_ERROR',        //检查时间策略每月选择日子长度错误
    	'API_CODE_JOBS_TRANSPOT_PARAMS_ERROR',	        //传输模式参数错误传递
    	'API_CODE_JOBS_BACKUP_MODE_PARAM_ERROR',		//备份模式对应虚拟化参数错误
    	
    	'API_CODE_JOBS_GET_CURRENT_JOBS_LIST',          //获取当前任务列表
    	'API_CODE_JOBS_GET_CURRENT_JOB_DETAILS',          //获取当前任务详情
    	'API_CODE_JOBS_GET_HISTORY_JOBS_LIST',          //获取历史任务列表
    	'API_CODE_JOBS_GET_HISTORY_JOB_DETAILS',          //获取历史任务详情
    	
    	'API_CODE_JOBS_DELETE_JOB_CHECK_STATUS',         //删除任务检测任务状态
    	'API_CODE_JOBS_JOB_NAME_NOT_EXIST',              //任务名字不存在	
		'API_CODE_JOBS_GET_CURRENT_JOB_UUID',			//通过vcenteruuid和vmuuid得到当前任务uuid
		'API_CODE_JOBS_DELETE_HISTORY_JOB',             //删除历史任务
    	'API_CODE_JOBS_DELETE_HISTORY_JOB_ERROR',       //想要删除的历史任务ID不存在	
		'API_CODE_JOBS_GET_PROTECTED_VM',               //获取受保护的虚拟机列表
    	'API_CODE_JOBS_START_STRATEGY',                 //启动时间策略	
    	'API_CODE_JOBS_DELETE_JOB_UUID_NOT_EXIST',      //传入的任务uuid不存在	
    	'API_CODE_JOBS_VM_NAME_EXIST',                  //虚拟机名已经存在
    	'API_CODE_JOBS_START_COPY_BACKUP',				//启动副本备份任务
    	'API_CODE_JOBS_START_COPY_BACK',				//启动副本回传任务
    	'API_CODE_JOBS_PAUSE',							//暂停任务
    	
        'API_CODE_JOBS_THREAD_NUM_OVER_ERROR',       //线程数量未设置成1-32
        
        'API_CODE_JOBS_GET_TASK_UUID_BY_VM_ID',      //通过虚拟机id获取任务uuid
        'API_CODE_JOBS_START_SELECT_VM_BACKUP',      //启动选择虚拟机的备份任务
        'API_CODE_JOBS_VM_NOT_IN_TASK',             //虚拟机不在任务中
        'API_CODE_JOBS_SELECT_ONE_TASK',            //选择的虚拟机不在同一个任务中
        'API_CODE_JOBS_SELECT_ONE_HYPERVISOR',      //选择的虚拟机不是同一个虚拟化类型
        'API_CODE_SELECT_VM_JOB_RUNNING_EXIST',      //选择的虚拟机所在任务正在运行中
        
        'API_CODE_JOBS_GET_CURRENT_TASK_VM_INFO',      //获取指定虚拟机的当前任务详情信息
        
        'API_CODE_JOBS_START_RUNNING_JOB_ERROR',      //启动任务检查任务是否正在运行
        
        'API_CODE_JOBS_START_STRATEGY_JOB_STATUS_ERROR',      //启动时间策略状态不为停止
        
        'API_CODE_JOBS_TIMEPOINT_REPEAT_ERROR',      //重复的时间点
    		
        /*********Logs**********/
        84001 => 'API_CODE_LOGS_DELETE_SYSTEMLOG',      //删除系统日志
        'API_CODE_LOGS_DELETE_JOBLOG',                 //删除任务日志
    	'API_CODE_LOGS_DELETE_SYSTEMLOG_ERROR',			//系统日志ID未找到
    	'API_CODE_LOGS_DELETE_TASKLOG_ERROR',			//任务日志ID未找到
    	'API_CODE_LOGS_GET_JOB_LOG_LIST',          //获取任务日志列表
		'API_CODE_LOGS_GET_SYSTEM_LOG_LIST',        //获取系统日志列表
        
        /*********Settings**********/
        85001 => 'API_CODE_SETTINGS_LICENSE',   			//系统授权
        'API_CODE_SETTINGS_GET_FINGERPRINT',            //获取指纹信息
        'API_CODE_SETTINGS_GET_AUTHORIZED_INFO',        //得到已授权的系统信息
        'API_CODE_SETTINGS_GET_VM_DISTRIBUTION',        //得到虚拟机使用情况
        'API_CODE_SETTINGS_GET_STORAGE_USAGE',          //得到存储使用情况
        'API_CODE_SETTINGS_MESSAGE_PUSH',               //配置消息推送
    	'API_CODE_SETTINGS_GET_MESSAGE_PUSH_INFO',      //获取消息推送信息
        'API_CODE_SETTINGS_GET_SYSTEM_SURVEY',          //获取系统运行信息
        'API_CODE_SETTINGS_MESSAGE_PUSH_CONNECT_TEST_ERROR',   //测试连接失败
        'API_CODE_SETTINGS_LICENSE_ERROR',              //授权文件传入异常
    		
        /*********Storages**********/
        86001 => 'API_CODE_STORAGES_ADD_STORAGE',       //添加存储
        'API_CODE_STORAGES_NICKNAME_EXISTS',            //存储重名检查
    	'API_CODE_STORAGES_EDIT_STORAGE_NICKNAME',		//修改存储别名
        'API_CODE_STORAGES_DELETE_STORAGE',             //删除存储
    	'API_CODE_STORAGES_DELETE_STORAGE_OFF_LINE',    //删除存储失败，存储离线
        'API_CODE_STORAGES_DELETE_STORAGE_JOB_USED',   //删除存储失败，有任务正在使用存储
		'API_CODE_STORAGES_GET_ISCSI_NAME_FAILED',      //获取ISCSI名字失败
		'API_CODE_STORAGES_GET_STORAGE_INFO',           //通过存储uuid获取存储信息
		'API_CODE_STORAGES_GET_STORAGE_LIST',           //获取存储列表
    	'API_CODE_STORAGES_GET_DISK_LIST',              //获取磁盘列表	
    	'API_CODE_STORAGES_GET_WWN_INFO',               //获取wwn信息
		'API_CODE_STORAGES_GET_ISCSI_NAME',             //获取ISCSI名字
		'API_CODE_STORAGES_NO_STORAGE_EXIST',           //没有可用存储
		'API_CODE_STORAGES_NO_WWN_EXIST',               //没有wwn存在

    	'API_CODE_STORAGES_ADD_LANFREE',                //添加lanfree
    	'API_CODE_STORAGES_EDIT_LANFREE_NICKNAME',      //修改lanfree名字
    	'API_CODE_STORAGES_DELETE_LANFREE',				//删除lanfree
		'API_CODE_STORAGES_GET_ISCSI_LUN',             //获取ISCSI映射轮
    	'API_CODE_STORAGES_GET_LANFREE_LIST',           //获取lanfree列表
		'API_CODE_ADD_STORAGE_WARNING_VALUE_ERROR',     //如果阈值是百分比 不能超过100
		'API_CODE_STORAGE_DELETE_UUI_NOT_EXIST',        //传入的存储uuid不存在
		'API_CODE_STORAGE_NOT_SUPPORT_IMPORT_ERROR',    //只有分区才支持导入数据功能
		'API_CODE_STORAGES_GET_IMPORT_DATA_LIST', 		//获取导入数据任务列表
		'API_CODE_STORAGES_ASSIGN_DATA', 				//分配导入数据给用户
		'API_CODE_STORAGES_DELETE_IMPORT_DATA', 		//删除导入数据
		'API_CODE_STORAGES_ADD_COPY_STORAEG',			//添加副本存储
    		
    	/*********Users**********/
        87001 => 'API_CODE_USERS_ADD_USER',            //添加用户
        'API_CODE_USERS_EDIT_USER',                    //修改用户    
        'API_CODE_USERS_DELETE_USER',                  //删除用户
        'API_CODE_USERS_UNLOCK_USER',                   //启用用户
        'API_CODE_USERS_LOCK_USER',                     //禁用用户
        'API_CODE_USERS_GET_USER_LIST',                //获取用户列表
        'API_CODE_USERS_GET_USER_INFO',                //通过用户uuid获取用户信息
        'API_CODE_USERS_DELETE_USER_UUID_NOT_EXIST',    //用户uuid不存在
        'API_CODE_USERS_DELETE_USER_EXIST_JOB',        //删除用户失败，该用户有任务存在
		'API_CODE_USERS_DELETE_USER_CHECK_PERMISSION', //检查用户权限
		'API_CODE_USERS_DELETE_ADMIN_ERROR',           //不能删除admin用户
		'API_CODE_USERS_USER_NAME_EXISTS',             //检查用户名是否存在
        'API_CODE_USERS_DELETE_USER_EXIST_USER',        //删除用户失败，该用户有子用户
        'API_CODE_USERS_DELETE_USER_EXIST_PARAM',       //删除用户失败，参数错误
        'API_CODE_USERS_DELETE_USER_EXIST_TIMEPOINT',        //删除用户失败，该用户有时间点存在
        'API_CODE_USERS_DELETE_USER_EXIST_AGENT',        //删除用户失败，该用户有代理客户端存在
        'API_CODE_USERS_DELETE_USER_EXIST_VCENTER',        //删除用户失败，该用户有虚拟化中心存在
        
    		
        /*********Vcenters**********/
        88001 => 'API_CODE_VCENTERS_ADD',               //添加虚拟化中心
        'API_CODE_VCENTERS_EDIT',                       //修改虚拟化中心
        'API_CODE_VCENTERS_DELETE',                     //删除虚拟化中心
        'API_CODE_VCENTERS_DELETE_CHECK_BAK_REC_JOB',   //删除检查有备份或恢复任务
        'API_CODE_VCENTERS_DELETE_CHECK_INS_MOT_JOB',   //删除检查有瞬时恢复或迁移任务
        'API_CODE_VCENTERS_HOST_LICENSE',               //授权虚拟化中心下的宿主机
        'API_CODE_VCENTERS_HOST_UNLICENSE',             //取消授权虚拟化中心下的宿主机
        'API_CODE_VCENTERS_REFRESH',                    //刷新虚拟化中心
        'API_CODE_VCENTERS_GET_UUID',                   //获取虚拟化中心uuid
       	'API_CODE_VCENTERS_GET_VCENTER_INFO',           //获取虚拟化中心信息
        'API_CODE_VCENTERS_GET_VCENTER_LIST',           //获取虚拟化中心列表
       	'API_CODE_VCENTERS_GET_HOST_LIST',              //获取宿主机列表
       	'API_CODE_VCENTERS_GET_DESTINATION_LIST',       //获取目的虚拟化中心列表
        'API_CODE_VCENTERS_GET_VM_DISK_LIST',           //获取虚拟机磁盘列表
        'API_CODE_VCENTERS_GET_HOST_CONFIG',            //获取宿主机配置信息
    	'API_CODE_VCENTERS_GET_OPENSTACK_CONFIG',      	//获取Openstack配置信息
    	'API_CODE_VCENTERS_DELETE_UUID_NOT_EXIST',      //传入的虚拟化中心uuid不存在
    	'API_CODE_VCENTERS_GET_VM_DISKS',               //获取对应虚拟机的磁盘列表信息
    	'API_CODE_VCENTERS_GET_PROTECT_BACKUP_SIZE',    //获取Openstack租户下虚拟机备份总量
    	'API_CODE_VCENTERS_OPENSTACK_PROTECT_NOT_EXIST',//查询的租户不存在
    	'API_CODE_VCENTERS_GET_VM_CONFIG_BY_IP',       //根据虚拟机IP获取虚拟机信息
    	
    	/*********Nodes**********/
    	89001 => 'API_CODE_NODES_EDIT_NODE_NAME',       //修改节点名字
        'API_CODE_NODES_DELETE_NODE',                   //删除节点
        'API_CODE_NODES_DELETE_LOCAL_ERROR',            //不支持删除本地节点
        'API_CODE_NODES_DELETE_EXIST_STORAGE_ERROR',    //存储设备未全部删除
        'API_CODE_NODES_DELETE_NODE_USED_ERROR',        //节点正在使用
       	'API_CODE_NODES_GET_NODE_LIST',                 //获取节点列表
    	'API_CODE_NODES_GET_NODE_UUID',                 //获取节点uuid
    	'API_CODE_NODES_GET_NODE_INFO',                 //通过uuid获取节点信息
    	'API_CODE_NODES_DELETE_NOT_EXIST',             //传入的节点uuid不存在
    	'API_CODE_NODES_NO_BACKUP_NODE',               //没有找到可用的备份节点
    	'API_CODE_NODES_NO_FIND_STORAGE',              //没有找到可用存储
    		
    	/*********Points**********/
    	90001 => 'API_CODE_POINTS_GET_VM_CONFIG',       //获取虚拟机存储和网络
    	'API_CODE_POINTS_GET_BACKUP_VM_LIST',           //获取已备份的虚拟机列表
    	'API_CODE_POINTS_GET_VM_POINTS_LIST',           //获取虚拟机备份时间点列表
    	'API_CODE_POINTS_DELETE_TIME_POINT',            //删除备份时间点
    	'API_CODE_POINTS_TIME_POINT_NOT_EXIST',         //备份时间点不存在
    	'API_CODE_POINTS_GET_SELECT_POINTS_LIST',       //根据查询虚拟机名和uuid获取时间点
    	'API_CODE_POINTS_GET_STORAGE_NODE_INFO',        //获取存储uuid对应的系统节点信息
    	'API_CODE_POINTS_GET_TASKUUID_POINTS_LIST',     //根据多个任务uuid获取备份点列表
        'API_CODE_POINTS_GET_VM_STORAGE_SIZE_LIST',     //获取虚拟机存储容量信息
    	
    	/*********Copy**********/
    	91001 => 'API_CODE_COPY_CREATE_COPY_JOB',		//创建副本备份任务
    	'API_CODE_COPY_EDIT_COPY_JOB',					//修改副本备份任务
    	'API_CODE_COPY_GET_ALL_TASK_INFO',				//获取副本任务的所有配置信息
    	'API_CODE_COPY_CREATE_COPY_BACK_JOB',			//创建副本回传任务
    	'API_CODE_COPY_GET_VM_LIST',					//获取副本任务的虚拟机列表
    	'API_CODE_COPY_GET_COPY_DATA',					//获取副本时间点数据
    	'API_CODE_COPY_GET_BASIC_DETAILS', 				//获取副本任务详情信息
    	'API_CODE_COPY_SOURCE_NODE_UUID_NOT_SAME',  	//副本任务源节点uuid不相同
    	
    	/*********tenant**********/
        92001 => 'API_CODE_TENANT_ADD',	                //创建租户
        'API_CODE_TENANT_EDIT',                         //修改租户
        'API_CODE_TENANT_DELETE',                       //删除租户
        'API_CODE_TENANT_UNLOCK',                       //启用租户
        'API_CODE_TENANT_LOCK',                         //禁用租户
        'API_CODE_TENANT_GET_LIST',                     //获取租户列表
        'API_CODE_TENANT_GET_INFO',                     //获取单个租户详细信息
        'API_CODE_TENANT_GET_USER_LIST',                //获取租户下用户列表
        'API_CODE_TENANT_GET_USER_GROUP_LIST',          //获取租户下用户组列表
        'API_CODE_TENANT_GET_RESOURCE_GROUP_LIST',      //获取租户下资源组列表
        'API_CODE_TENANT_SET_AUTH',                     //配置租户授权
        'API_CODE_TENANT_SET_VM_HOST',                  //配置租户虚拟机恢复目标宿主机
        'API_CODE_TENANT_EXIST_NAME_ERROR',             //租户名已存在
        'API_CODE_TENANT_STOP_JOB_ERROR',               //停止租户内所有任务失败
        'API_CODE_TENANT_SPACE_NOT_ENOUGH_BY_CAPACITY',  //租户按容量授权剩余空间不足
        'API_CODE_TENANT_VIRTU_MACHINE_NOT_ENOUGH_BY_VM', //租户按数量可分配虚拟机个数不足
        'API_CODE_TENANT_FSAGENT_NOT_ENOUGH',             //租户可分配文件代理不足
        'API_CODE_TENANT_DBAGENT_NOT_ENOUGH',             //租户可分配数据库代理不足
        'API_CODE_TENANT_VM_HOST_NOT_EXIST',             //选择的宿主机不存在
        'API_CODE_TENANT_TIMEPOINT_EXIST_ERROR',        //租户存在备份数据需要删除
        'API_CODE_TENANT_INPUT_STANDARD_ERROR',        //校验租户名是否合规
        
        /*********usergroups**********/
        93001 => 'API_CODE_USER_GROUP_ADD',             //创建用户组
        'API_CODE_USER_GROUP_EDIT',                     //修改用户组
        'API_CODE_USER_GROUP_DELETE',                   //删除用户组
        'API_CODE_USER_GROUP_UNLOCK',                   //启用用户组 
        'API_CODE_USER_GROUP_LOCK',                     //禁用用户组
        'API_CODE_USER_GROUP_GET_LIST',                 //获取用户组列表
        'API_CODE_USER_GROUP_GET_INFO',                 //获取单个用户组详细信息
        
        /*********roles**********/
        94001 => 'API_CODE_ROLE_ADD',                   //创建角色
        'API_CODE_ROLE_EDIT',                           //修改角色
        'API_CODE_ROLE_DELETE',                         //删除角色
        'API_CODE_ROLE_UNLOCK',                         //启用角色
        'API_CODE_ROLE_LOCK',                           //禁用角色
        'API_CODE_ROLE_GET_LIST',                       //获取角色列表
        'API_CODE_ROLE_GET_INFO',                       //获取单个角色详细信息
        'API_CODE_ROLE_GET_USER_PERMISSION',            //获取当前可用权限集合
        'API_CODE_ROLE_GET_ALL_PERMISSION',             //获取系统所有权限
        'API_CODE_ROLE_ALLOCATE_USER_LIST',             //分配角色到选择用户集合
        'API_CODE_ROLE_ALLOCATE_USER_GROUP_LIST',       //分配角色到选择用户组集合
        
        /*********domainserver**********/
        95001 => 'API_CODE_DOMAIN_SERVER_ADD',             //创建域服务器
        'API_CODE_DOMAIN_SERVER_EDIT',                     //修改域服务器
        'API_CODE_DOMAIN_SERVER_DELETE',                   //删除域服务器
        'API_CODE_DOMAIN_SERVER_GET_LIST',                 //获取域服务器列表
        'API_CODE_DOMAIN_SERVER_GET_INFO',                 //获取单个域服务器详细信息
        'API_CODE_DOMAIN_SERVER_USER_PASSWORD_ERROR',      //连接域服务器账户密码错误
        'API_CODE_DOMAIN_SERVER_NOT_CONNECT_ERROR',        //无法连接到AD域服务器
        'API_CODE_DOMAIN_SERVER_INCLUDE_USER_DEL_FIRST',   //统创建了包含该域的用户，请先删除对应用户
        
        /*********resource**********/
        96001 => 'API_CODE_RESOURCE_ADD_GROUP',		       //创建资源组
        'API_CODE_RESOURCE_EDIT_GROUP',                    //修改资源组
        'API_CODE_RESOURCE_DELETE_GROUP',		           //删除资源组
        'API_CODE_RESOURCE_GET_GROUP_LIST',		           //获取资源组列表
        'API_CODE_RESOURCE_GET_USER_ASSIGNABLE_RESOURCE',  //获取当前用户可分配资源
        'API_CODE_RESOURCE_ALLOCATE_TO_USER',		       //分配资源到用户
        'API_CODE_RESOURCE_ALLOCATE_TO_USER_GROUP',		   //分配资源到用户组
        'API_CODE_RESOURCE_ALLOCATE_TO_RESOURCE_GROUP',	   //分配资源到资源组	
        'API_CODE_RESOURCE_GET_RESOURCE_OF_TENANT',		   //获取指定租户下的资源
        'API_CODE_RESOURCE_GET_RESOURCE_OF_USER',		   //获取指定用户下的资源
        'API_CODE_RESOURCE_GET_RESOURCE_OF_USER_GROUP',	   //获取指定用户组下的资源	
        'API_CODE_RESOURCE_GET_RESOURCE_OF_RESOURCE_GROUP',//获取指定资源组下的资源		
        'API_CODE_RESOURCE_DELETE_RESOURCE_USER',		   //取消资源和用户关联
        'API_CODE_RESOURCE_DELETE_RESOURCE_USER_GROUP',	   //取消资源和用户组关联
        'API_CODE_RESOURCE_DELETE_RESOURCE_RESOURCE_GROUP',//取消资源和资源组关联
        'API_CODE_RESOURCE_SELECT_USING_ERROR',            //任务正在使用对应资源
        'API_CODE_RESOURCE_GET_FAILED_ERROR',            //未获取指定类型的资源
        'API_CODE_RESOURCE_EXIST_ERROR',                    //所选资源已被关联
        'API_CODE_RESOURCE_RESOURCE_GROUP_EXIST_ERROR',  //所选资源组已被关联
        
        /*********billing**********/
        97001 => 'API_CODE_BILLING_ADD_STRATEGY',	      //创建计费策略
        'API_CODE_BILLING_EDIT_STRATEGY',                 //修改计费策略
        'API_CODE_BILLING_DELETE_STRATEGY',               //删除计费策略
        'API_CODE_BILLING_UNLOCK_STRATEGY',               //启用计费策略
        'API_CODE_BILLING_LOCK_STRATEGY',                 //禁用计费策略
        'API_CODE_BILLING_GET_STRATEGY_LIST',             //获取计费策略列表
        'API_CODE_BILLING_GET_STRATEGY_INFO',             //获取单个计费策略详细信息
        'API_CODE_BILLING_GET_MONEY_UNIT_LIST',           //获取计费货币单位集合
        'API_CODE_BILLING_ALLOCATE_STRATEGY_TENANT',      //分配计费策略到租户集合
        'API_CODE_BILLING_GET_STRATEGY_TENANT_LIST',      //获取计费策略下的租户列表
        'API_CODE_BILLING_TENANT_RENEW',                  //租户使用续费
        'API_CODE_BILLING_DELETE_STRATEGY_TENANT',        //取消指定计费策略与租户关联
        'API_CODE_BILLING_GET_TENANT_BILL_DETAIL',        //获取租户使用费用详情
        'API_CODE_BILLING_GET_TENANT_UNALLOCATED',        //获取未分配到费用策略的所有租户
    ),
    'description' => array(
        /*********一般返回**********/
        'API_CODE_SUCCESS' => Xphp::$_lang['API_CODE_SUCCESS'],
        'API_CODE_ERROR' => Xphp::$_lang['API_CODE_ERROR'],
        'API_CODE_PARAMS_ERROR' => Xphp::$_lang['API_CODE_PARAMS_ERROR'],
        'API_CODE_USER_ERROR' => Xphp::$_lang['API_CODE_USER_ERROR'],
        'API_CODE_SYSTEM_SERVICE_ERROR' => Xphp::$_lang['API_CODE_SYSTEM_SERVICE_ERROR'],
    	'API_CODE_HEADER_ERROR_401' => "HTTP/1.1 401 Unauthorized",        
    	'API_CODE_HEADER_ERROR_404' => "HTTP/1.1 404 Not Found",        
    	'API_CODE_HEADER_ERROR_405' => "HTTP/1.1 405 Method Not Allowed",        
    	'API_CODE_HEADER_ERROR_406' => "HTTP/1.1 406 Not Acceptable",     
    		
        /*********Access Token**********/
        'API_CODE_ACCESS_TOKEN_LOGIN_ERROR' => Xphp::$_lang['API_CODE_ACCESS_TOKEN_LOGIN_ERROR'],
        'API_CODE_ACCESS_TOKEN_NOT_AVAILABLE' => Xphp::$_lang['API_CODE_ACCESS_TOKEN_NOT_AVAILABLE'],
        'API_CODE_ACCESS_TOKEN_TIMEOUT' => Xphp::$_lang['API_CODE_ACCESS_TOKEN_TIMEOUT'],
    	'API_CODE_ACCESS_TOKEN_NOT_AVAILABLE_ERROR' => "Access token is not available",
        'API_CODE_ACCESS_TOKEN_REQUEST_IP_NOT_SUPPORT' => "Unsupported request iP",
        'API_CODE_ACCESS_TOKEN_TARGET_IP_NOT_SUPPORT' => "Unsupported target iP",
        
        
        /*********Alarms**********/
        'API_CODE_ALARMS_DELETE_SYSTEM_ALARM' => Xphp::$_lang['API_CODE_ALARMS_DELETE_SYSTEM_ALARM'], 
        'API_CODE_ALARMS_DELETE_JOB_ALARM' => Xphp::$_lang['API_CODE_ALARMS_DELETE_JOB_ALARM'],          
        'API_CODE_ALARMS_DELETE_SYSTEM_ALARM_ERROR' => Xphp::$_lang['API_CODE_ALARMS_DELETE_SYSTEM_ALARM_ERROR'],    
        'API_CODE_ALARMS_DELETE_JOB_ALARM_ERROR' => Xphp::$_lang['API_CODE_ALARMS_DELETE_JOB_ALARM_ERROR'],      
        'API_CODE_ALARMS_RESPONSE_JOB_ALARM' => Xphp::$_lang['API_CODE_ALARMS_RESPONSE_JOB_ALARM'],
    	'API_CODE_ALARMS_RESPONSE_SYSTEM_ALARM' => Xphp::$_lang['API_CODE_ALARMS_RESPONSE_SYSTEM_ALARM'],
    	'API_CODE_ALARMS_GET_JOB_ALARM_LIST' => Xphp::$_lang['API_CODE_ALARMS_GET_JOB_ALARM_LIST'],	
    	'API_CODE_ALARMS_GET_SYSTEM_ALARM_LIST' => Xphp::$_lang['API_CODE_ALARMS_GET_SYSTEM_ALARM_LIST'],
        'API_CODE_ALARMS_GET_UNSOLVED_ALARM_NOTICE' => "get the unsolved  alarm survey info",

        /*********Jobs**********/
        'API_CODE_JOBS_NOT_FIND_VM' => Xphp::$_lang['API_CODE_JOBS_NOT_FIND_VM'],
        'API_CODE_JOBS_CREATE_BACKUP' => Xphp::$_lang['API_CODE_JOBS_CREATE_BACKUP'],
        'API_CODE_JOBS_CREATE_RECOVERY' => Xphp::$_lang['API_CODE_JOBS_CREATE_RECOVERY'],
        'API_CODE_JOBS_CREATE_INSTANT' => Xphp::$_lang['API_CODE_JOBS_CREATE_INSTANT'],
        'API_CODE_JOBS_CREATE_MOTION' => Xphp::$_lang['API_CODE_JOBS_CREATE_MOTION'],
        'API_CODE_JOBS_EDIT_BACKUP' => Xphp::$_lang['API_CODE_JOBS_EDIT_BACKUP'],
        'API_CODE_JOBS_START_FULL_BACKUP' => Xphp::$_lang['API_CODE_JOBS_START_FULL_BACKUP'],
        'API_CODE_JOBS_START_INCR_BACKUP' => Xphp::$_lang['API_CODE_JOBS_START_INCR_BACKUP'],
        'API_CODE_JOBS_START_DIFF_BACKUP' => Xphp::$_lang['API_CODE_JOBS_START_DIFF_BACKUP'],
    	'API_CODE_JOBS_START_RECOVERY' => Xphp::$_lang['API_CODE_JOBS_START_RECOVERY'],
    	'API_CODE_JOBS_START_INSTANT_RECOVERY' => Xphp::$_lang['API_CODE_JOBS_START_INSTANT_RECOVERY'],
        'API_CODE_JOBS_STOP' => Xphp::$_lang['API_CODE_JOBS_STOP'],
        'API_CODE_JOBS_DELETE' => Xphp::$_lang['API_CODE_JOBS_DELETE'],
        'API_CODE_JOBS_NOT_FIND_JOB' => Xphp::$_lang['API_CODE_JOBS_NOT_FIND_JOB'],
        'API_CODE_JOBS_JOB_NAME_EXISTS' => Xphp::$_lang['API_CODE_JOBS_JOB_NAME_EXISTS'],
        'API_CODE_JOBS_GET_UUID' => Xphp::$_lang['API_CODE_JOBS_GET_UUID'],
        'API_CODE_JOBS_TIME_STRATEGY_ERROR' => Xphp::$_lang['API_CODE_JOBS_TIME_STRATEGY_ERROR'],
		'API_CODE_JOBS_CREATE_MOTION_ERROR' => Xphp::$_lang['API_CODE_JOBS_CREATE_MOTION_ERROR'],
    	'API_CODE_JOBS_EDIT_ERROR' => Xphp::$_lang['API_CODE_JOBS_EDIT_ERROR'],	
    	'API_CODE_JOBS_EDIT_BACKUP_ERROR' => Xphp::$_lang['API_CODE_JOBS_EDIT_BACKUP_ERROR'],	
        'API_CODE_JOBS_JOB_UUID_NOT_EXIST' => Xphp::$_lang['API_CODE_JOBS_JOB_UUID_NOT_EXIST'],
		'API_CODE_JOBS_STORAGE_BLOCK_SIZE_ERROR' => Xphp::$_lang['API_CODE_JOBS_STORAGE_BLOCK_SIZE_ERROR'],
		'API_CODE_JOBS_VM_NAME_NOT_AVAILABLE' => Xphp::$_lang['API_CODE_JOBS_VM_NAME_NOT_AVAILABLE'],
    	'API_CODE_JOBS_CREATE_TIME_FORMAT_ERROR' => Xphp::$_lang['API_CODE_JOBS_CREATE_TIME_FORMAT_ERROR'],	
		'API_CODE_JOBS_CREATE_TIME_NOT_CORRECT' => Xphp::$_lang['API_CODE_JOBS_CREATE_TIME_NOT_CORRECT'],
    	'API_CODE_JOBS_TIMEPOINT_UUID_NOT_EXIST' => Xphp::$_lang['API_CODE_JOBS_TIMEPOINT_UUID_NOT_EXIST'],	
		'API_CODE_JOBS_WEEK_DAYS_LENGTH_ERROR' => Xphp::$_lang['API_CODE_JOBS_WEEK_DAYS_LENGTH_ERROR'],
    	'API_CODE_JOBS_MONTH_DAYS_LENGTH_ERROR' => Xphp::$_lang['API_CODE_JOBS_MONTH_DAYS_LENGTH_ERROR'],
    	'API_CODE_JOBS_TRANSPOT_PARAMS_ERROR' => Xphp::$_lang['API_CODE_JOBS_TRANSPOT_PARAMS_ERROR'],	
    	'API_CODE_JOBS_BACKUP_MODE_PARAM_ERROR' => Xphp::$_lang['API_CODE_JOBS_BACKUP_MODE_PARAM_ERROR'],
    		
		'API_CODE_JOBS_GET_CURRENT_JOBS_LIST' => Xphp::$_lang['API_CODE_JOBS_GET_CURRENT_JOBS_LIST'],
    	'API_CODE_JOBS_GET_CURRENT_JOB_DETAILS' => Xphp::$_lang['API_CODE_JOBS_GET_CURRENT_JOB_DETAILS'],
		'API_CODE_JOBS_GET_HISTORY_JOBS_LIST' => Xphp::$_lang['API_CODE_JOBS_GET_HISTORY_JOBS_LIST'],
    	'API_CODE_JOBS_GET_HISTORY_JOB_DETAILS' => Xphp::$_lang['API_CODE_JOBS_GET_HISTORY_JOB_DETAILS'],
    	'API_CODE_JOBS_DELETE_JOB_CHECK_STATUS' => Xphp::$_lang['API_CODE_JOBS_DELETE_JOB_CHECK_STATUS'],
    	'API_CODE_JOBS_JOB_NAME_NOT_EXIST' => Xphp::$_lang['API_CODE_JOBS_JOB_NAME_NOT_EXIST'],
    	'API_CODE_JOBS_GET_CURRENT_JOB_UUID' => Xphp::$_lang['API_CODE_JOBS_GET_CURRENT_JOB_UUID'],	
    	'API_CODE_JOBS_DELETE_HISTORY_JOB' => Xphp::$_lang['API_CODE_JOBS_DELETE_HISTORY_JOB'],	
		'API_CODE_JOBS_DELETE_HISTORY_JOB_ERROR' => Xphp::$_lang['API_CODE_JOBS_DELETE_HISTORY_JOB_ERROR'],
    	'API_CODE_JOBS_GET_PROTECTED_VM' => Xphp::$_lang['API_CODE_JOBS_GET_PROTECTED_VM'],
    	'API_CODE_JOBS_START_STRATEGY' => Xphp::$_lang['API_CODE_JOBS_START_STRATEGY'],
    	'API_CODE_JOBS_DELETE_JOB_UUID_NOT_EXIST' => Xphp::$_lang['API_CODE_JOBS_DELETE_JOB_UUID_NOT_EXIST'],
    	'API_CODE_JOBS_VM_NAME_EXIST' => Xphp::$_lang['API_CODE_JOBS_VM_NAME_EXIST'],	
    	'API_CODE_JOBS_START_COPY_BACKUP' => Xphp::$_lang['API_CODE_JOBS_START_COPY_BACKUP'],				
    	'API_CODE_JOBS_START_COPY_BACK' => Xphp::$_lang['API_CODE_JOBS_START_COPY_BACK'],				
    	'API_CODE_JOBS_PAUSE' => Xphp::$_lang['API_CODE_JOBS_PAUSE'],	
        'API_CODE_JOBS_THREAD_NUM_OVER_ERROR' => "线程数量最少设置为1个，最多设置为32个。",
        
        'API_CODE_JOBS_GET_TASK_UUID_BY_VM_ID' => "get task_uuid by vm id",      //通过虚拟机id获取任务uuid
        'API_CODE_JOBS_START_SELECT_VM_BACKUP' => "start backup job by vmuuid(s)",      //启动选择虚拟机的备份任务
        'API_CODE_JOBS_VM_NOT_IN_TASK' => "the vm is not in the task",             //虚拟机不在任务中
        'API_CODE_JOBS_SELECT_ONE_TASK' => "the vms are not in the same task",            //选择的虚拟机不在同一个任务中
        'API_CODE_JOBS_SELECT_ONE_HYPERVISOR' => "the vms are not in the same virtualization type",      //选择的虚拟机不是同一个虚拟化类型
        'API_CODE_SELECT_VM_JOB_RUNNING_EXIST' => "The task in which the vm resides is running",      //选择的虚拟机所在任务正在运行中
        'API_CODE_JOBS_GET_CURRENT_TASK_VM_INFO' => "Get current task vm info",
        'API_CODE_JOBS_START_RUNNING_JOB_ERROR' => "The task is already running",      //启动任务检查任务是否正在运行
        'API_CODE_JOBS_START_STRATEGY_JOB_STATUS_ERROR' => "The task status must be stopped",      //启动时间策略状态不为停止
        
        'API_CODE_JOBS_TIMEPOINT_REPEAT_ERROR' => "The timepoint_uuid repeat, please select the different vm(s)",      //重复的时间点
        
        /*********Logs**********/
        'API_CODE_LOGS_DELETE_SYSTEMLOG' => Xphp::$_lang['API_CODE_LOGS_DELETE_SYSTEMLOG'],
    	'API_CODE_LOGS_DELETE_JOBLOG' => Xphp::$_lang['API_CODE_LOGS_DELETE_JOBLOG'],
    	'API_CODE_LOGS_DELETE_SYSTEMLOG_ERROR' => Xphp::$_lang['API_CODE_LOGS_DELETE_SYSTEMLOG_ERROR'],
        'API_CODE_LOGS_DELETE_TASKLOG_ERROR' => Xphp::$_lang['API_CODE_LOGS_DELETE_TASKLOG_ERROR'],
        'API_CODE_LOGS_GET_JOB_LOG_LIST' => Xphp::$_lang['API_CODE_LOGS_GET_JOB_LOG_LIST'],
    	'API_CODE_LOGS_GET_SYSTEM_LOG_LIST' => Xphp::$_lang['API_CODE_LOGS_GET_SYSTEM_LOG_LIST'],
    		
    	
        /*********Settings**********/
        'API_CODE_SETTINGS_LICENSE' => Xphp::$_lang['API_CODE_SETTINGS_LICENSE'],
    	'API_CODE_SETTINGS_GET_FINGERPRINT' => Xphp::$_lang['API_CODE_SETTINGS_GET_FINGERPRINT'],
    	'API_CODE_SETTINGS_GET_AUTHORIZED_INFO' => Xphp::$_lang['API_CODE_SETTINGS_GET_AUTHORIZED_INFO'],
    	'API_CODE_SETTINGS_GET_VM_DISTRIBUTION' => Xphp::$_lang['API_CODE_SETTINGS_GET_VM_DISTRIBUTION'],
    	'API_CODE_SETTINGS_GET_STORAGE_USAGE' => Xphp::$_lang['API_CODE_SETTINGS_GET_STORAGE_USAGE'],
    	'API_CODE_SETTINGS_MESSAGE_PUSH' => Xphp::$_lang['API_CODE_SETTINGS_MESSAGE_PUSH'],
    	'API_CODE_SETTINGS_GET_MESSAGE_PUSH_INFO' => Xphp::$_lang['API_CODE_SETTINGS_GET_MESSAGE_PUSH_INFO'],
		'API_CODE_SETTINGS_GET_SYSTEM_SURVEY' => "Get backup system survey",
        'API_CODE_SETTINGS_MESSAGE_PUSH_CONNECT_TEST_ERROR' => Xphp::$_lang['API_CODE_SETTINGS_MESSAGE_PUSH_CONNECT_TEST_ERROR'],
    	'API_CODE_SETTINGS_LICENSE_ERROR' => Xphp::$_lang['API_CODE_SETTINGS_LICENSE_ERROR'],	
    		
        /*********Storages**********/
        'API_CODE_STORAGES_ADD_STORAGE' => Xphp::$_lang['API_CODE_STORAGES_ADD_STORAGE'],
        'API_CODE_STORAGES_NICKNAME_EXISTS' => Xphp::$_lang['API_CODE_STORAGES_NICKNAME_EXISTS'],
    	'API_CODE_STORAGES_EDIT_STORAGE_NICKNAME' => Xphp::$_lang['API_CODE_STORAGES_EDIT_STORAGE_NICKNAME'],		
    	'API_CODE_STORAGES_DELETE_STORAGE' => Xphp::$_lang['API_CODE_STORAGES_DELETE_STORAGE'], 
    	'API_CODE_STORAGES_DELETE_STORAGE_OFF_LINE' => Xphp::$_lang['API_CODE_STORAGES_DELETE_STORAGE_OFF_LINE'],           
        'API_CODE_STORAGES_DELETE_STORAGE_JOB_USED' => Xphp::$_lang['API_CODE_STORAGES_DELETE_STORAGE_JOB_USED'],
    	'API_CODE_STORAGES_GET_ISCSI_NAME_FAILED' => Xphp::$_lang['API_CODE_STORAGES_GET_ISCSI_NAME_FAILED'],
		'API_CODE_STORAGES_GET_STORAGE_INFO' => Xphp::$_lang['API_CODE_STORAGES_GET_STORAGE_INFO'],
    	'API_CODE_STORAGES_GET_STORAGE_LIST' => Xphp::$_lang['API_CODE_STORAGES_GET_STORAGE_LIST'],	
    	'API_CODE_STORAGES_GET_DISK_LIST' => Xphp::$_lang['API_CODE_STORAGES_GET_DISK_LIST'],
    	'API_CODE_STORAGES_GET_WWN_INFO' => Xphp::$_lang['API_CODE_STORAGES_GET_WWN_INFO'],
    	'API_CODE_STORAGES_GET_ISCSI_NAME' => Xphp::$_lang['API_CODE_STORAGES_GET_ISCSI_NAME'],
    	'API_CODE_STORAGES_NO_STORAGE_EXIST' => Xphp::$_lang['API_CODE_STORAGES_NO_STORAGE_EXIST'],
    	'API_CODE_STORAGES_NO_WWN_EXIST' => Xphp::$_lang['API_CODE_STORAGES_NO_WWN_EXIST'],	

    	'API_CODE_STORAGES_ADD_LANFREE' => Xphp::$_lang['API_CODE_STORAGES_ADD_LANFREE'],
		'API_CODE_STORAGES_EDIT_LANFREE_NICKNAME' => Xphp::$_lang['API_CODE_STORAGES_EDIT_LANFREE_NICKNAME'],
    	'API_CODE_STORAGES_DELETE_LANFREE' => Xphp::$_lang['API_CODE_STORAGES_DELETE_LANFREE'],	
    	'API_CODE_STORAGES_GET_ISCSI_LUN' => Xphp::$_lang['API_CODE_STORAGES_GET_ISCSI_LUN'],
    	'API_CODE_STORAGES_GET_LANFREE_LIST' => Xphp::$_lang['API_CODE_STORAGES_GET_LANFREE_LIST'],
		'API_CODE_ADD_STORAGE_WARNING_VALUE_ERROR' => Xphp::$_lang['API_CODE_ADD_STORAGE_WARNING_VALUE_ERROR'],
		'API_CODE_STORAGE_DELETE_UUI_NOT_EXIST' => Xphp::$_lang['API_CODE_STORAGE_DELETE_UUI_NOT_EXIST'],
    	'API_CODE_STORAGE_NOT_SUPPORT_IMPORT_ERROR' => Xphp::$_lang['API_CODE_STORAGE_NOT_SUPPORT_IMPORT_ERROR'],
		'API_CODE_STORAGES_GET_IMPORT_DATA_LIST' => Xphp::$_lang['API_CODE_STORAGES_GET_IMPORT_DATA_LIST'],
		'API_CODE_STORAGES_ASSIGN_DATA' => Xphp::$_lang['API_CODE_STORAGES_ASSIGN_DATA'],
    	'API_CODE_STORAGES_DELETE_IMPORT_DATA' => Xphp::$_lang['API_CODE_STORAGES_DELETE_IMPORT_DATA'],
    	'API_CODE_STORAGES_ADD_COPY_STORAEG' => Xphp::$_lang['API_CODE_STORAGES_ADD_COPY_STORAEG'],
    		
        /*********Users**********/
        'API_CODE_USERS_ADD_USER' => Xphp::$_lang['API_CODE_USERS_ADD_USER'],
        'API_CODE_USERS_EDIT_USER' => Xphp::$_lang['API_CODE_USERS_EDIT_USER'],
    	'API_CODE_USERS_DELETE_USER' => Xphp::$_lang['API_CODE_USERS_DELETE_USER'],
        'API_CODE_USERS_UNLOCK_USER' => "Unlock user",
        'API_CODE_USERS_LOCK_USER' => "Lock user",   
        'API_CODE_USERS_GET_USER_LIST' => Xphp::$_lang['API_CODE_USERS_GET_USER_LIST'],
        'API_CODE_USERS_GET_USER_INFO' => "Get user's information through user_uuid",
    	'API_CODE_USERS_DELETE_USER_UUID_NOT_EXIST' => Xphp::$_lang['API_CODE_USERS_DELETE_USER_UUID_NOT_EXIST'],
    	'API_CODE_USERS_DELETE_USER_EXIST_JOB' => Xphp::$_lang['API_CODE_USERS_DELETE_USER_EXIST_JOB'],
		'API_CODE_USERS_DELETE_USER_CHECK_PERMISSION' => Xphp::$_lang['API_CODE_USERS_DELETE_USER_CHECK_PERMISSION'],
    	'API_CODE_USERS_DELETE_ADMIN_ERROR' => Xphp::$_lang['API_CODE_USERS_DELETE_ADMIN_ERROR'],	
    	'API_CODE_USERS_USER_NAME_EXISTS' => Xphp::$_lang['API_CODE_USERS_USER_NAME_EXISTS'],
        'API_CODE_USERS_DELETE_USER_EXIST_USER' => "Description Failed to delete the user because the user has sub-users",
        'API_CODE_USERS_DELETE_USER_EXIST_PARAM' =>"The Parameter type error",
        'API_CODE_USERS_DELETE_USER_EXIST_TIMEPOINT' => "Description Failed to delete a user because the user existed at a Timepoint",
        'API_CODE_USERS_DELETE_USER_EXIST_AGENT' => "Description Failed to delete a user because the user existed at a Agent",
        'API_CODE_USERS_DELETE_USER_EXIST_VCENTER' => "Description Failed to delete a user because the user existed at a Vcenter",

    	/*********Vcenters**********/
        'API_CODE_VCENTERS_ADD' => Xphp::$_lang['API_CODE_VCENTERS_ADD'],
        'API_CODE_VCENTERS_EDIT' => Xphp::$_lang['API_CODE_VCENTERS_EDIT'],
        'API_CODE_VCENTERS_DELETE' => Xphp::$_lang['API_CODE_VCENTERS_DELETE'],
        'API_CODE_VCENTERS_DELETE_CHECK_BAK_REC_JOB' => Xphp::$_lang['API_CODE_VCENTERS_DELETE_CHECK_BAK_REC_JOB'],
        'API_CODE_VCENTERS_DELETE_CHECK_INS_MOT_JOB' => Xphp::$_lang['API_CODE_VCENTERS_DELETE_CHECK_INS_MOT_JOB'],
        'API_CODE_VCENTERS_HOST_LICENSE' => Xphp::$_lang['API_CODE_VCENTERS_HOST_LICENSE'],
        'API_CODE_VCENTERS_HOST_UNLICENSE' => Xphp::$_lang['API_CODE_VCENTERS_HOST_UNLICENSE'],
        'API_CODE_VCENTERS_REFRESH' => Xphp::$_lang['API_CODE_VCENTERS_REFRESH'],
        'API_CODE_VCENTERS_GET_UUID' => Xphp::$_lang['API_CODE_VCENTERS_GET_UUID'],
    	'API_CODE_VCENTERS_GET_VCENTER_INFO' => Xphp::$_lang['API_CODE_VCENTERS_GET_VCENTER_INFO'],
    	'API_CODE_VCENTERS_GET_VCENTER_LIST' => Xphp::$_lang['API_CODE_VCENTERS_GET_VCENTER_LIST'],
    	'API_CODE_VCENTERS_GET_HOST_LIST' => Xphp::$_lang['API_CODE_VCENTERS_GET_HOST_LIST'],
    	'API_CODE_VCENTERS_GET_DESTINATION_LIST' => Xphp::$_lang['API_CODE_VCENTERS_GET_DESTINATION_LIST'],
    	'API_CODE_VCENTERS_GET_VM_DISK_LIST' => Xphp::$_lang['API_CODE_VCENTERS_GET_VM_DISK_LIST'],
		'API_CODE_VCENTERS_GET_HOST_CONFIG' => Xphp::$_lang['API_CODE_VCENTERS_GET_HOST_CONFIG'],
    	'API_CODE_VCENTERS_GET_OPENSTACK_CONFIG' => Xphp::$_lang['API_CODE_VCENTERS_GET_OPENSTACK_CONFIG'],
    	'API_CODE_VCENTERS_DELETE_UUID_NOT_EXIST' => Xphp::$_lang['API_CODE_VCENTERS_DELETE_UUID_NOT_EXIST'],
    	'API_CODE_VCENTERS_GET_VM_DISKS' => Xphp::$_lang['API_CODE_VCENTERS_GET_VM_DISKS'],
        'API_CODE_VCENTERS_GET_PROTECT_BACKUP_SIZE' => "获取Openstack租户下虚拟机备份总量",    //获取Openstack租户下虚拟机备份总量
        'API_CODE_VCENTERS_OPENSTACK_PROTECT_NOT_EXIST' => "查询的租户不存在", //查询的租户不存在
        'API_CODE_VCENTERS_GET_VM_CONFIG_BY_IP' => "get vm config by ip",       //根据虚拟机IP获取虚拟机信息
        
    	/*********Nodes**********/
    	'API_CODE_NODES_EDIT_NODE_NAME' => Xphp::$_lang['API_CODE_NODES_EDIT_NODE_NAME'],
    	'API_CODE_NODES_DELETE_NODE' => Xphp::$_lang['API_CODE_NODES_DELETE_NODE'],
    	'API_CODE_NODES_DELETE_LOCAL_ERROR' => Xphp::$_lang['API_CODE_NODES_DELETE_LOCAL_ERROR'],
    	'API_CODE_NODES_DELETE_EXIST_STORAGE_ERROR' => Xphp::$_lang['API_CODE_NODES_DELETE_EXIST_STORAGE_ERROR'],
		'API_CODE_NODES_DELETE_NODE_USED_ERROR' => Xphp::$_lang['API_CODE_NODES_DELETE_NODE_USED_ERROR'],
    	'API_CODE_NODES_GET_NODE_LIST' => Xphp::$_lang['API_CODE_NODES_GET_NODE_LIST'],
    	'API_CODE_NODES_GET_NODE_UUID' => Xphp::$_lang['API_CODE_NODES_GET_NODE_UUID'],
    	'API_CODE_NODES_GET_NODE_INFO' => Xphp::$_lang['API_CODE_NODES_GET_NODE_INFO'],
    	'API_CODE_NODES_DELETE_NOT_EXIST' => Xphp::$_lang['API_CODE_NODES_DELETE_NOT_EXIST'],
    	'API_CODE_NODES_NO_BACKUP_NODE' => Xphp::$_lang['API_CODE_NODES_NO_BACKUP_NODE'],	
    	'API_CODE_NODES_NO_FIND_STORAGE' => Xphp::$_lang['API_CODE_NODES_NO_FIND_STORAGE'],
    		
    	/*********Points**********/
    	'API_CODE_POINTS_GET_VM_CONFIG' => Xphp::$_lang['API_CODE_POINTS_GET_VM_CONFIG'],
    	'API_CODE_POINTS_GET_BACKUP_VM_LIST' => Xphp::$_lang['API_CODE_POINTS_GET_BACKUP_VM_LIST'],
    	'API_CODE_POINTS_GET_VM_POINTS_LIST' => Xphp::$_lang['API_CODE_POINTS_GET_VM_POINTS_LIST'],
    	'API_CODE_POINTS_DELETE_TIME_POINT' => Xphp::$_lang['API_CODE_POINTS_DELETE_TIME_POINT'],
    	'API_CODE_POINTS_TIME_POINT_NOT_EXIST' => Xphp::$_lang['API_CODE_POINTS_TIME_POINT_NOT_EXIST'],
    	'API_CODE_POINTS_GET_SELECT_POINTS_LIST' => Xphp::$_lang['API_CODE_POINTS_GET_SELECT_POINTS_LIST'],
    	'API_CODE_POINTS_GET_STORAGE_NODE_INFO' => Xphp::$_lang['API_CODE_POINTS_GET_STORAGE_NODE_INFO'],
    	'API_CODE_POINTS_GET_TASKUUID_POINTS_LIST' => Xphp::$_lang['API_CODE_POINTS_GET_TASKUUID_POINTS_LIST'],
    	'API_CODE_POINTS_GET_VM_STORAGE_SIZE_LIST' => "获取虚拟机备份容量列表",
        
    	/*********Copy**********/
    	'API_CODE_COPY_CREATE_COPY_JOB' => Xphp::$_lang['API_CODE_COPY_CREATE_COPY_JOB'],
    	'API_CODE_COPY_EDIT_COPY_JOB' => Xphp::$_lang['API_CODE_COPY_EDIT_COPY_JOB'],
    	'API_CODE_COPY_GET_ALL_TASK_INFO' => Xphp::$_lang['API_CODE_COPY_GET_ALL_TASK_INFO'],
    	'API_CODE_COPY_CREATE_COPY_BACK_JOB' => Xphp::$_lang['API_CODE_COPY_CREATE_COPY_BACK_JOB'],
    	'API_CODE_COPY_GET_VM_LIST' => Xphp::$_lang['API_CODE_COPY_GET_VM_LIST'],			
    	'API_CODE_COPY_GET_COPY_DATA' => Xphp::$_lang['API_CODE_COPY_GET_COPY_DATA'],					
    	'API_CODE_COPY_GET_BASIC_DETAILS' => Xphp::$_lang['API_CODE_COPY_GET_BASIC_DETAILS'], 				
    	'API_CODE_COPY_SOURCE_NODE_UUID_NOT_SAME' => Xphp::$_lang['API_CODE_COPY_SOURCE_NODE_UUID_NOT_SAME'],  	
    
    	/*********tenant**********/
        'API_CODE_TENANT_ADD' => "Add tenant",	                
        'API_CODE_TENANT_EDIT' => "Edit tenant",                         
        'API_CODE_TENANT_DELETE' => "Delete tenant",                       
        'API_CODE_TENANT_UNLOCK' => "Unlock tenant",                      
        'API_CODE_TENANT_LOCK' => "Lock tenant",                        
        'API_CODE_TENANT_GET_LIST' => "Get tenant list",                     
        'API_CODE_TENANT_GET_INFO' => "Get tenant info",                     
        'API_CODE_TENANT_GET_USER_LIST' => "Get user list of tenant",                
        'API_CODE_TENANT_GET_USER_GROUP_LIST' => "Get user group list of tenant",          
        'API_CODE_TENANT_GET_RESOURCE_GROUP_LIST' => "Get resource group list of tenant",      
        'API_CODE_TENANT_SET_AUTH' => "Set tenant authorization",                   
        'API_CODE_TENANT_SET_VM_HOST' => "Set tenant vm host",                  
        'API_CODE_TENANT_EXIST_NAME_ERROR' => "The tenant name already exist",
        'API_CODE_TENANT_STOP_JOB_ERROR' => "Stop job(s) of tenant error",
        'API_CODE_TENANT_SPACE_NOT_ENOUGH_BY_CAPACITY' => "The remaining capacity is not enough, please reallocate or expand the capacity",
        'API_CODE_TENANT_VIRTU_MACHINE_NOT_ENOUGH_BY_VM' => "The remaining number of available VMs is not enough, please reallocate or expand the capacity",
        'API_CODE_TENANT_FSAGENT_NOT_ENOUGH' => "he remaining number of available file agents is not enough, please reallocate or expand the capacity",
        'API_CODE_TENANT_DBAGENT_NOT_ENOUGH' => "The remaining number of available database agents is not enough, please reallocate or expand the capacity",
        'API_CODE_TENANT_VM_HOST_NOT_EXIST' => "The select host is not exist",
        'API_CODE_TENANT_TIMEPOINT_EXIST_ERROR' => "The tenant has backup data, please notify the tenant admin to delete all backup data before deleting",
        'API_CODE_TENANT_INPUT_STANDARD_ERROR' => "please enter a name for the specification",

        /*********usergroups**********/
        'API_CODE_USER_GROUP_ADD' => "Add user group",
        'API_CODE_USER_GROUP_EDIT' => "Edit user group",
        'API_CODE_USER_GROUP_DELETE' => "Delete user group",
        'API_CODE_USER_GROUP_UNLOCK' => "Unlock user group",
        'API_CODE_USER_GROUP_LOCK' => "Lock user group",
        'API_CODE_USER_GROUP_GET_LIST' => "Get user group list",
        'API_CODE_USER_GROUP_GET_INFO' => "Get user group info",
        
        /*********roles**********/
        'API_CODE_ROLE_ADD' => "Add role",                   
        'API_CODE_ROLE_EDIT' => "Edit role",                           
        'API_CODE_ROLE_DELETE' => "Delete role",                         
        'API_CODE_ROLE_UNLOCK' => "Unlock role",                         
        'API_CODE_ROLE_LOCK' => "Lock role",                           
        'API_CODE_ROLE_GET_LIST' => "get role list",                       
        'API_CODE_ROLE_GET_INFO' => "get role info",                       
        'API_CODE_ROLE_GET_USER_PERMISSION' => "get current user permission",
        'API_CODE_ROLE_GET_ALL_PERMISSION' => "get system all permission",
        'API_CODE_ROLE_ALLOCATE_USER_LIST' => "Allocate role to user list",            
        'API_CODE_ROLE_ALLOCATE_USER_GROUP_LIST' => "Allocate role to user group list",
        
        /*********domainserver**********/
        'API_CODE_DOMAIN_SERVER_ADD' => "Add domain server",                
        'API_CODE_DOMAIN_SERVER_EDIT' => "Edit domain server",                    
        'API_CODE_DOMAIN_SERVER_DELETE' => "Delete domain server",                   
        'API_CODE_DOMAIN_SERVER_GET_LIST' => "Get domain server list",                 
        'API_CODE_DOMAIN_SERVER_GET_INFO' => "Get domain server info",  
        'API_CODE_DOMAIN_SERVER_USER_PASSWORD_ERROR' => "User name or password error(Please also check if local DNS lookup is configured)",      
        'API_CODE_DOMAIN_SERVER_NOT_CONNECT_ERROR' => "Cannot connect to Active Directory server",        
        'API_CODE_DOMAIN_SERVER_INCLUDE_USER_DEL_FIRST' => "The system has created a user that contains the domain, delete the user first",
        
        /*********resource**********/
        'API_CODE_RESOURCE_ADD_GROUP' => "Add resource group",		       
        'API_CODE_RESOURCE_EDIT_GROUP' => "Edit resource group",                    
        'API_CODE_RESOURCE_DELETE_GROUP' => "Delete resource group",		           
        'API_CODE_RESOURCE_GET_GROUP_LIST' => "Get resource group list",		           
        'API_CODE_RESOURCE_GET_USER_ASSIGNABLE_RESOURCE' => "get current user assignable resource",  
        'API_CODE_RESOURCE_ALLOCATE_TO_USER' => "Allocate resource to user",		       
        'API_CODE_RESOURCE_ALLOCATE_TO_USER_GROUP' => "Allocate resource to user group",		  
        'API_CODE_RESOURCE_ALLOCATE_TO_RESOURCE_GROUP' => "Allocate resource to resource group",	   
        'API_CODE_RESOURCE_GET_RESOURCE_OF_TENANT' => "Get resource of tenant",		   
        'API_CODE_RESOURCE_GET_RESOURCE_OF_USER' => "Get resource of user",		   
        'API_CODE_RESOURCE_GET_RESOURCE_OF_USER_GROUP' => "Get resource of user group",	   
        'API_CODE_RESOURCE_GET_RESOURCE_OF_RESOURCE_GROUP' => "Get resource of resource group",
        'API_CODE_RESOURCE_DELETE_RESOURCE_USER' => "Cancel the association between resource and user",		   
        'API_CODE_RESOURCE_DELETE_RESOURCE_USER_GROUP' => "Cancel the association between resource and user group",	   
        'API_CODE_RESOURCE_DELETE_RESOURCE_RESOURCE_GROUP' => "Cancel the association between resource and resource group",
        'API_CODE_RESOURCE_SELECT_USING_ERROR' => "The selected resource is being used by the job",
        'API_CODE_RESOURCE_GET_FAILED_ERROR' => "Get the specified resource failed",
        'API_CODE_RESOURCE_EXIST_ERROR' => "The selected resource group already exists",
        'API_CODE_RESOURCE_RESOURCE_GROUP_EXIST_ERROR' => "The selected resource already exists",
        
        /*********billing**********/
        'API_CODE_BILLING_ADD_STRATEGY' => "Add billing strategy",	      
        'API_CODE_BILLING_EDIT_STRATEGY' => "Edit billing strategy",                 
        'API_CODE_BILLING_DELETE_STRATEGY' => "Delete billing strategy",               
        'API_CODE_BILLING_UNLOCK_STRATEGY' => "Unlock billing strategy",              
        'API_CODE_BILLING_LOCK_STRATEGY' => "Lock billing strategy",                 
        'API_CODE_BILLING_GET_STRATEGY_LIST' => "Get billing strategy list",             
        'API_CODE_BILLING_GET_STRATEGY_INFO' => "Get billing strategy info",             
        'API_CODE_BILLING_GET_MONEY_UNIT_LIST' => "Get billing money list",          
        'API_CODE_BILLING_ALLOCATE_STRATEGY_TENANT' => "Allocate billing strategy to tenant",      
        'API_CODE_BILLING_GET_STRATEGY_TENANT_LIST' => "Get tenant list of billing strategy",      
        'API_CODE_BILLING_TENANT_RENEW' => "Renew for tenant",                  
        'API_CODE_BILLING_DELETE_STRATEGY_TENANT' => "Cancel the association between billing and tenant", 
        'API_CODE_BILLING_GET_TENANT_BILL_DETAIL' => "Get tenant billing detail", 
        'API_CODE_BILLING_GET_TENANT_UNALLOCATED' => "Get unassigned tenants"
    ),
);


?>