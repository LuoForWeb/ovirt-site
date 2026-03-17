<?php

/**
 * 任务相关的配置信息
 */

return [
    //策略备份模式
    'BACKUP_MODE' => array(
        'UNKNOWN' => 0,
        'FULL' => 1,
        'INCREMENTAL' => 2,
        'DIFFERENTIAL' => 3,
        'LOG' => 4,
        'COPY' => 5,
        'ARCHIVE' => 6,
        'TAG' => 7,
        'VERIFY' => 8,
        'PINCREMENTAL' => 9
    ),
    //策略类型
    'STRATEGY_TYPE' => array('UNKNOWN' => 0, 'EVERY_DAY' => 1, 'EVERY_WEEK' => 2, 'EVERY_MONTH' => 3, 'ONCE' => 4),
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
    'STRATEGY_ROLL_TYPE' => array('ON' => 1, 'OFF' => 2),
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
        'ARCHIVE' => 19,                 //虚拟机归档
        'ARCHIVE_FETCH' => 20,          //虚拟机归档回传
        'DB_CDP_BACKUP' => 21,          //数据库CDP备份
        'DB_CDP_RECOVERY' => 22,        //数据库CDP恢复
        'DB_CDP_TAKEOVER' => 23,        //数据库CDP接管
        'FILE_CDP_BACKUP' => 24,         //文件CDP备份
        'FILE_CDP_RECOVERY' => 25,      //文件CDP恢复
        'FILE_BACKUP_COPY' => 26,       //文件副本
        'FILE_BACKUP_COPY_FETCH' => 27,  //文件副本回传
        'DB_BACKUP' => 28,              //数据库备份
        'DB_RECOVERY' => 29,            //数据库恢复
        'DB_BACKUP_COPY' => 30,         //数据库副本
        'DB_BACKUP_COPY_FETCH' => 31,   //数据库副本回传
        'VOL_CDP_BACKUP' => 32,         //卷CDP备份
        'VOL_CDP_RECOVERY' => 33,       //卷CDP恢复
        'VOL_CDP_TAKEOVER' => 34,       //卷CDP接管
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
        'CDP_DB_BACKUP' => 46,  //数据库实时同步
        'CDP_DB_RECOVERY' => 47,  //数据库实时恢复
        'OS_INSTANT_RECOVERY' => 49, //操作系统瞬时恢复
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
        'VOL_CDP_REPLICATION' => 65 // 整机复制
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
        'SYNC' => 9,            // task is sync
        'PREPARING' => 10,      // preparing
        'PAUSING' => 11,        // pausing
        'STARTING' => 12,       //starting
        'FINISHED' => 13,       //finished
        'TAKEOVER' => 14,       //takeover
        'TAKEOVER_STARTING' => 15,       //takeover starting
        'TAKEOVER_STOPPING' => 16,       //takeover stopping
        'SUCCESSED' => 17, //任务成功
        'CREATING' => 18, // task is being created
        'PENDING' => 19, // task is pending
        'DELETING' => 20, // task is deleting
        'CLEANING' => 21, // task is cleaning
    ),
    // 任务状态描述
    'TASKSTATUSDES' => array(
        0 => 'WEB_PLATFORM_PUBLIC_UNKNOWN',
        1 => 'WEB_PLATFORM_DES_WAITING',
        2 => 'WEB_PLATFORM_DES_RUNNING',
        3 => 'WEB_PLATFORM_DES_PAUSE',
        4 => 'WEB_PLATFORM_DES_STOP',
        5 => 'WEB_PLATFORM_DES_STOPPING',
        6 => 'WEB_PLATFORM_DES_NETWORK_ERROR',
        7 => 'WEB_PLATFORM_DES_ABNORMAL',
        8 => 'WEB_PLATFORM_DES_ERROR',
        9 => 'WEB_PLATFORM_DES_SYNC',
        10 => 'WEB_PLATFORM_DES_PREPARE',
        11 => 'WEB_PLATFORM_DES_PAUSEING',
        12 => 'WEB_PLATFORM_DES_STARTING',
        13 => 'WEB_PLATFORM_DES_FINISH',
        14 => 'WEB_PLATFORM_DES_IN_TAKEOVER',
        15 => 'WEB_PLATFORM_DES_TAKEOVER_STARTING',
        16 => 'WEB_PLATFORM_DES_TAKEOVER_STOPPING',
        17 => 'WEB_PLATFORM_DES_SUCCESSED',
        18 => 'WEB_PLATFORM_DES_CREATING',
        19 => 'WEB_PLATFORM_DES_PENDING',
        20 => 'WEB_PLATFORM_DES_DELETING',
        21 => 'WEB_PLATFORM_DES_CLEANING',
    ),
    /**
     * 任务控制项
     * 启动1/停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切(新增)/15 停止回切(新增)/16继续备份(新增)
     */
    'TASK_CONTROL' => array(
        'UNKNOWN' => 0,         //unknown task control
        'START' => 1,           //启动任务
        'STOP' => 2,            //停止任务
        'MODIFY' => 3,          //修改任务
        'DELETE' => 4,          //删除任务
        'PAUSE' => 5,            //暂停任务
        'START_DIFF' => 6,      //启动差异
        'START_INCR' => 7,      //启动增量
        'START_STRATEGY' => 8,  //启动策略
        'MIGRATION' => 9,       //迁移
        'START_FULL' => 10,     //启动完备
        'START_TAKEOVER' => 11, //启动接管
        'STOP_TAKEOVER' => 12,  //停止接管
        'LOG_BACKUP' => 13,     //日志备份
        'START_FAILBACK' => 14, //启动回切
        'STOP_FAILBACK' => 15,  //停止回切
        'CONTINUE' => 16,       //继续备份
        'AUTO_TAKEOVER' => 17,  //自动接管
        'START_COPY' => 18,  //启动复制
        'START_COMPARE' => 19,  //启动对比
        'FORCE_DELETE' => 20,  //强制删除
        'FORCE_STOP' => 21, //强制停止
        'STOP_MOTION' => 22, // 停止迁移
        'FINISH_MOTION' => 23, // 完成迁移
    ),

    //恢复位置
    'RECOVERY_POSITION' => ['ORIGINAL' => 1, 'OTHER' => 2, 'FILEDIR' => 3, 'REDIRECT' => 4, 'EXPORT' => 5, 'PDB' => 6],
    //恢复方式   立即恢复/按时间策略恢复
    'RECOVERY_TIME_TYPE' => array('IMMEDIATELY' => 1, 'STRATEGY' => 2, 'ONCETIME' => 4),

    //副本归档submodule_type
    'COPY_ARCHIVE_SUB_TYPE' => array(
        'BACKUP_COPY_TYPE_UNKNOWN' => 0,
        'BACKUP_COPY_TYPE_VM_BACKUP_COPY' => 1,                 //vm backup copy
        'BACKUP_COPY_TYPE_VM_ARCHIVE' => 2,                     //vm archive
        'BACKUP_COPY_TYPE_FS_BACKUP_COPY' => 3,                 //fs backup copy
        'BACKUP_COPY_TYPE_DB_BACKUP_COPY' => 4,                 //db backup copy
        'BACKUP_COPY_TYPE_OS_BACKUP_COPY' => 5,             //os system backup copy
        'BACKUP_COPY_TYPE_OS_ARCHIVE' => 6,                     //operator system archive
        'BACKUP_COPY_TYPE_NAS_BACKUP_COPY' => 7,                //nas backup copy

    ),
    //细粒度恢复文件块大小 8MB
    'GRAIN_FILE_BLOCK_SIZE' => 8388608,
    //任务阶段
    'TASK_STAGE' => array(
        'UNKNOWN',
        'WAIT_EXEC',
        'DICT_EXPORT',
        'DICT_IMPORT',
        'FULL_SYNC',
        'IN_TAKEOVER_STARTING',
        'IN_TAKEOVER',
        'FAILBACK_DICT_EXPORT',
        'FAILBACK_DICT_IMPORT',
        'FAILBACK_FULL_SYNC',
        'FAILBACK_LOG_SYNC',
        'RESTORE_IN_RUNNING',
    ),
    // 迁移任务阶段
    'TASK_STAGE_MOTION' => [
        'WEB_PLATFORM_PUBLIC_UNKNOWN',
        'UI_MOTION_STAGE1', // 时间点数据传输阶段
        'UI_MOTION_STAGE2', // 缓存数据同步阶段
    ],
    // 迁移任务是否显示停止瞬时恢复
    'TASK_STATUS_MOTION' => [
        'SHOW_STOP_MIGRATE_BUTTON' => 1, // 显示停止瞬时恢复读写按钮 1
        'USER_STOP_MIGRATE' => 2, // 用户手动点击停止后的状态 2
    ],
    // 实时保护任务阶段
    'REAL_PROTECT_STAGE' => [
        1 => 'WEB_REAL_WAIT_EXEC', // 等待执行 不可创建标签点，不可开始接管
        20 => 'WEB_REAL_BACKUP_IN_INIT_SYNC', // 初始化同步 可创建标签点，可开始接管(若配置有接管)
        21 => 'WEB_REAL_BACKUP_IN_REALTIME_SYNC', // 备份实时同步 不可创建标签点，不可开始接管
        22 => 'WEB_REAL_BACKUP_WAIT_CONVERT_TO_CDP', // 等待恢复实时保护 不可创建标签点，不可开始接管
        40 => 'WEB_REAL_BACKUP_IN_SERVER_DATA_CONSISTENCY_CHECK', // 数据一执性校验 不可创建标签点，不可开始接管
        41 => 'WEB_REAL_BACKUP_IN_STANDBY_DATA_CONSISTENCY_CHECK', // 备机数据一致性校验
        42 => 'WEB_REAL_BACKUP_IN_SERVER_REALTIME_CONSISTENCY_CHECK', // 实时数据一致性校验 该阶段可创建标签点，不可开始接管
        60 => 'WEB_REAL_IN_TAKEOVER', // 接管中 可开始回切(若配置有回切)
        61 => 'WEB_REAL_IN_TAKEOVER_STARTING', // 接管启动中 不可开始回切
        80 => 'WEB_REAL_FAILBACK_IN_INIT_SYNC', // 回切初始化同步 不可创建标签点，不可开始接管
        81 => 'WEB_REAL_FAILBACK_IN_REALTIME_SYNC', // 回切实时同步 可创建标签点，不可开始接管
        82 => 'WEB_REAL_FAILBACK_IN_STARTING', // 回切准备中 不可创建标签点，不可开始接管
        100 => 'WEB_VOL_CDP_TASK_RECOVERY_STARTING', // 恢复准备中
        101 => 'WEB_VOL_CDP_TASK_RECOVERY_WAITING_DATA_TRANSFER', // 恢复数据传输中
        102 => 'WEB_VOL_CDP_TASK_RECOVERY_WAITING_AGENT_HANDLE', // 代理预处理
        103 => 'WEB_VOL_CDP_TASK_RECOVERY_FINISH', // 恢复完成
        104 => 'WEB_VOL_CDP_TASK_RECOVERY_FAILED', // 恢复中止
    ],
    // 数据库实时阶段
    'DB_CDP_PROTECT_STAGE' => [
        1 => 'WEB_DB_CDP_WAIT_EXEC', // 等待
        10 => 'WEB_DB_CDP_DICT_EXPORT', // 数据字典导出 初始同步
        11 => 'WEB_DB_CDP_DICT_IMPORT', // 数据字典导入 初始同步
        20 => 'WEB_DB_CDP_FULL_SYNC', // 全量数据同步 初始同步
        21 => 'WEB_DB_CDP_LOG_SYNC', // 日志数据同步 实时同步
        30 => 'WEB_DB_CDP_IN_TAKEOVER_STARTING', // 接管启动中
        31 => 'WEB_DB_CDP_IN_TAKEOVER', // 接管中
        40 => 'WEB_DB_CDP_FAILBACK_DICT_EXPORT', // 回切数据字典导出 逆向初始同步
        41 => 'WEB_DB_CDP_FAILBACK_DICT_IMPORT', // 回切数据字典导入 逆向实时同步
        50 => 'WEB_DB_CDP_FAILBACK_FULL_SYNC', // 回切全量数据同步 逆向初始同步
        51 => 'WEB_DB_CDP_FAILBACK_LOG_SYNC', // 回切日志数据同步 逆向实时同步
        52 => 'WEB_DB_CDP_SERVICE_FAILBACK', // 回切生产业务 回切生产业务
        53 => 'WEB_DB_CDP_FAILBACK_SUCCESSED', // 回切完成
        60 => 'WEB_DB_CDP_RESTORE_IN_RUNNING', // 恢复运行中
        61 => 'WEB_DB_CDP_TASK_RESTORE_FINISH', // 恢复完成
        22 => 'WEB_DB_CDP_TASK_IN_CONSTRAINT_IMPORT', // 约束导入
        54 => 'WEB_DB_CDP_TASK_IN_FAILBACK_CONSTRAINT_IMPORT', // 回切约束导入
    ],
    // 普通任务任务阶段
    'COMMON_STAGE' => [
        // 公共
        1 => 'WEB_PLATFORM_TYPE_PREPARE', // 任务准备 同【运行中】状态，无特殊限制
        2 => 'WEB_PLATFORM_TYPE_DATA_TRANSPORT', // 数据传输 同【运行中】状态，无特殊限制
        3 => 'WEB_PLATFORM_TYPE_INTEGRITY_CHECK', // 完整性校验 同【运行中】状态，无特殊限制
        4 => 'WEB_PLATFORM_TYPE_VIRUS_CHECK', // 病毒查杀 同【运行中】状态，无特殊限制
        // 虚拟机
        100 => 'WEB_PLATFORM_REMOVE_RESIDUAL_RESOURCES', // 清除残留资源
        101 => 'WEB_PLATFORM_CREATE_SNAPSHOT', // 创建快照
        102 => 'WEB_PLATFORM_DEL_SNAPSHOT', // 删除快照
        103 => 'WEB_PLATFORM_CALCULCATE_EFFECTIVE_DATA', // 计算有效数据
        104 => 'WEB_PLATFORM_PREPARE_BACKUP_RESOURCES', // 准备备份资源
        105 => 'WEB_PLATFORM_DESTROY_BACKUP_RESOURCES', // 销毁备份资源
        106 => 'WEB_PLATFORM_PREPARE_RECOVERY_RESOURCES', // 准备恢复资源
        107 => 'WEB_PLATFORM_DESTROY_RECOVERY_RESOURCES', // 销毁恢复资源
        108 => 'WEB_PLATFORM_VOLUME_MOUNTING', // 卷挂载
        109 => 'WEB_PLATFORM_VOLUME_UNINSTALLATION', // 卷卸载
        110 => 'WEB_PLATFORM_GENERATE_IMAGE', // 生成镜像
        111 => 'WEB_PLATFORM_CONVERT_IMAGE_FORMAT', // 转换镜像格式
        112 => 'WEB_PLATFORM_UPLOAD_IMAGE', // 上传镜像
        113 => 'WEB_PLATFORM_GENERATE_TIME_POINT', // 生成时间点
        114 => 'WEB_PLATFORM_EXECUTE_RETENTION_POLICY', // 执行保留策略
        115 => 'WEB_PLATFORM_CREATE_STORAGE', // 创建存储
        116 => 'WEB_PLATFORM_DESTROY_STORAGE', // 销毁存储
        117 => 'WEB_PLATFORM_CREATE_VIRTUAL_MACHINE', // 创建虚拟机
        118 => 'WEB_PLATFORM_DESTROY_VIRTUAL_MACHINE', // 销毁虚拟机
        119 => 'WEB_PLATFORM_ADVANCED_RECOVERY_OPERATIONS', // 高级恢复操作
        120 => 'WEB_PLATFORM_GENERATE_DIRECTORY_TREE', // 生成目录树结构
        121 => 'WEB_PLATFORM_CBT_PROCESSING', // cbt处理
        122 => 'WEB_PLATFORM_TIMEPOINT_DATA_TRANSMISSION', // 时间点数据传输
        123 => 'WEB_PLATFORM_TRANSMISSION_OF_METADATA', // 元数据信息传输
        124 => 'WEB_PLATFORM_CACHE_DATA_TRANSMISSION', // 缓存数据传输
        125 => 'WEB_PLATFORM_VM_TASK_STAGE_CREATE_AGENT', // 创建代理
        126 => 'WEB_PLATFORM_VM_TASK_STAGE_POWEROFF_VM', // 关闭恢复虚拟机
        127 => 'WEB_PLATFORM_VM_TASK_STAGE_POWERON_VM', // 开启恢复虚拟机
        128 => 'WEB_PLATFORM_VM_TASK_STAGE_CREATE_VOLUME', // 创建卷
        129 => 'WEB_PLATFORM_VM_TASK_STAGE_DESTORY_VOLUME', // 删除卷
        // 文件
        200 => 'WEB_PLATFORM_LOAD_TARGET_OBJECT', // 加载目标对象信息
        201 => 'WEB_PLATFORM_INITIALIZE_TARGET_OBJECT', // 初始化目标对象上下文
        202 => 'WEB_PLATFORM_START_SCAN', // 启动扫描
        203 => 'WEB_PLATFORM_SYNCHRONIZE_DATA_POOL', // 同步数据池
        204 => 'WEB_PLATFORM_GENERATE_TIME_POINT', // 生成时间点
        205 => 'WEB_PLATFORM_EXECUTE_RETENTION_POLICY', // 执行保留策略
        206 => 'WEB_PLATFORM_CLEAN_RESOURCES', // 清理资源
        207 => 'WEB_PLATFORM_EXECUTE_ARCHIVING', // 执行归档
        208 => 'WEB_PLATFORM_GENERATE_HISTORY_TASKS', // 生成历史任务
        //文件复制
        250 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_LOAD_OBJECT_INFO', // 加载目标对象信息
        251 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_CREATE_OBJECT_PROGRESS', // 创建目标进程
        252 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_SEND_TASK_START_INFO', // 发送任务启动信息
        253 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_TASK_RUNNING', // 任务运行
        254 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_CACHE_CLEAN', // 资源清理
        255 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_TASK_RUNNING_INFO_COLLECT', // 任务运行结果统计
        256 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_HISTORY_CREATE', // 历史任务创建
        257 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_TASK_FINISH', // 任务退出
        258 => 'WEB_PLATFORM_SYNC_FS_TASK_STARGE_TASK_RUNNING_PREPARE', // 任务运行前置准备
        // 操作系统
        301 => 'WEB_PLATFORM_CLEAN_CLIENT_RESOURCES', // 清理客户端资源
        302 => 'WEB_PLATFORM_CREATE_SNAPSHOT', // 创建快照
        303 => 'WEB_PLATFORM_CALCULATE_EFFECTIVE_DATA', // 计算有效数据
        304 => 'WEB_PLATFORM_GENERATE_TIME_POINT', // 生成时间点
        305 => 'WEB_PLATFORM_DEL_SNAPSHOT', // 删除快照
        306 => 'WEB_PLATFORM_EXECUTE_RETENTION_POLICY', // 执行保留策略
        307 => 'WEB_PLATFORM_GET_AGENT_OF_RECOVERY', // 获取需要恢复的主机信息
        308 => 'WEB_PLATFORM_ADVANCED_RECOVERY_OPERATIONS', // 高级恢复操作
        309 => 'WEB_PLATFORM_CREATE_STORAGE', // 创建存储
        310 => 'WEB_PLATFORM_DESTROY_STORAGE', // 销毁存储
        311 => 'WEB_PLATFORM_CACHE_DATA_TRANSMISSION', // 缓存数据传输
        312 => 'WEB_PLATFORM_STAGE_DEPLOY_RAMOS', // RAMOS部署
        // 数据库
        400 => 'WEB_PLATFORM_CHECK_STATUS_OF_DATABASE', // 检查数据库运行状态
        401 => 'WEB_PLATFORM_CHECK_STATUS_OF_LOG', // 检查日志状态
        402 => 'WEB_PLATFORM_CREATE_SNAPSHOT', // 创建快照
        403 => 'WEB_PLATFORM_DEL_SNAPSHOT', // 删除快照
        404 => 'WEB_PLATFORM_EXECUTE_BACKUP_COMMAND_OF_DATABASE', // 执行备份数据库命令
        405 => 'WEB_PLATFORM_EXECUTE_RECOVERY_COMMAND_OF_DATABASE', // 执行恢复数据库命令
        406 => 'WEB_PLATFORM_CLEAN_CLIENT_RESOURCES', // 清理客户端资源
        407 => 'WEB_PLATFORM_GENERATE_TIME_POINT', // 生成时间点
        408 => 'WEB_PLATFORM_EXECUTE_RETENTION_POLICY', // 执行保留策略
        409 => 'WEB_PLATFORM_CREATE_DATABASE', // 创建数据库/实例
        410 => 'WEB_PLATFORM_DEL_DATABASE', // 删除数据库/实例
        411 => 'WEB_PLATFORM_BACKUP_CONFIG_OF_DATABASE', // 备份数据库配置文件
        412 => 'WEB_PLATFORM_RECOVERY_CONFIG_OF_DATABASE', // 恢复数据库配置文件
        413 => 'WEB_PLATFORM_BACKUP_FILE_OF_DATABASE', // 备份数据库数据文件
        414 => 'WEB_PLATFORM_RECOVERY_FILE_OF_DATABASE', // 恢复数据库数据文件
        415 => 'WEB_PLATFORM_BACKUP_LOG_OF_DATABASE', // 备份数据库日志文件
        416 => 'WEB_PLATFORM_RECOVERY_LOG_OF_DATABASE', // 恢复数据库日志文件
        417 => 'WEB_PLATFORM_DB_TASK_STAGE_START_DATABASE', // 启动数据库实例
        418 => 'WEB_PLATFORM_DB_TASK_STAGE_STOP_DATABASE', // 停止数据库实例
        419 => 'WEB_PLATFORM_DB_TASK_STAGE_SYNCHRONIZE_CLUSTER', // 同步集群数据
        420 => 'WEB_PLATFORM_DB_TASK_STAGE_CHECK_DATABASE', // 备份前检查数据库
        421 => 'WEB_PLATFORM_DB_TASK_STAGE_CROSSCHECK_ARCHIVE_LOG', // 校验归档日志
        // 应用
        500 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_SAVE_TIMEPOINT', // 保存时间点
        501 => 'WEB_PLATFORM_EXECUTE_RETENTION_POLICY', // 执行保留策略
        502 => 'WEB_PLATFORM_GENERATE_TIME_POINT', // 生成时间点
        520 => 'WEB_PLATFORM_CLEAN_CLIENT_RESOURCES', // 清理客户端资源
        521 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_CALCULATE_RECOVERY_OBJECTS', // 计算需要恢复的对象
        522 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_GET_AVAILABLE_CLIENT', // 获取可用客户端
        580 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_EXEC_PRE_HOOK', // 执行任务前置HOOK脚本
        581 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_EXEC_POST_HOOK', // 执行任务后置HOOK脚本
        582 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_EXEC_POST_FAILED_HOOK', // 执行任务失败后置HOOK脚本
        583 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_BACKUP_RESOURCES', // 资源备份
        584 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_RECOVER_RESOURCES', // 资源恢复
        585 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_CREATE_PVC_SNAPSHOT', // 创建PVC快照
        586 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_PREPARE_PVC_INSTANCES', // 准备PVC实例
        587 => 'WEB_PLATFORM_APP_TASK_STAGE_TYPE_CLEAN_TMP_RESOURCES', // 清理临时资源
        // 副本 （600-699）
        601 => 'WEB_PLATFORM_COPY_TASK_STAGE_LOAD_COPY_TASK_INFO', // 加载副本任务信息
        'WEB_PLATFORM_COPY_TASK_STAGE_LOAD_COPY_ITEM_INFO', // 获取副本各对象信息
        'WEB_PLATFORM_COPY_TASK_STAGE_CAL_COPY_ITEM_SIZE', // 计算副本任务量
        'WEB_PLATFORM_COPY_TASK_STAGE_START_ITEM_COPY', // 副本对象数据
        'WEB_PLATFORM_COPY_TASK_STAGE_UPDATE_AND_CHECK_COPY_TIMEPOINT', // 更新并检查数据位图文件
        'WEB_PLATFORM_COPY_TASK_STAGE_CACHE_BITMAP_FILE', // 缓存数据位图文件
        'WEB_PLATFORM_COPY_TASK_STAGE_DO_RESERVED_STRATEGY', // 执行保留策略
        // 数据验证 (700-799)
        700 => 'WEB_PLATFORM_SR_TASK_STAGE_TYPE_PREPARE_VIRTUAL_LAB', // 准备虚拟演练室
        'WEB_PLATFORM_SR_TASK_STAGE_TYPE_PREPARE_APPLICATION_GROUP', // 准备应用程序组
        'WEB_PLATFORM_SR_TASK_STAGE_TYPE_NETWORK_VERIFYING', // 网络验证
        'WEB_PLATFORM_SR_TASK_STAGE_TYPE_HEART_BEAT_VERIFYING', // 心跳验证
        'WEB_PLATFORM_SR_TASK_STAGE_TYPE_SCREEN_SHOT_VERIFING', // 截屏验证
        'WEB_PLATFORM_SR_TASK_STAGE_TYPE_DOCUMENT_VERIFYING', // 文件对比验证

    ],
    // 实时保护任务阶段枚举定义
    'REAL_PROTECT_STAGE_LIST' => [
        'WAIT_EXEC' => 1, // 等待执行 不可创建标签点，不可开始接管
        'BACKUP_IN_INIT_SYNC' => 20, // 初始化同步 可创建标签点，可开始接管(若配置有接管)
        'BACKUP_IN_REALTIME_SYNC' => 21, // 备份实时同步 不可创建标签点，不可开始接管
        'BACKUP_WAIT_CONVERT_TO_CDP' => 22, // 等待恢复实时保护 不可创建标签点，不可开始接管
        'BACKUP_IN_SERVER_DATA_CONSISTENCY_CHECK' => 40, // 数据一执性校验 不可创建标签点，不可开始接管
        'BACKUP_IN_STANDBY_DATA_CONSISTENCY_CHECK' => 41, // 备机数据一致性校验
        'BACKUP_IN_SERVER_REALTIME_CONSISTENCY_CHECK' => 42, // 实时数据一致性校验 该阶段可创建标签点，不可开始接管
        'IN_TAKEOVER' => 60, // 接管中 可开始回切(若配置有回切)
        'IN_TAKEOVER_STARTING' => 61, // 接管准备中 不可开始回切
        'FAILBACK_IN_INIT_SYNC' => 80, // 回切初始化同步 不可创建标签点，不可开始接管
        'FAILBACK_IN_REALTIME_SYNC' => 81, // 回切实时同步 可创建标签点，不可开始接管
        'FAILBACK_IN_STARTING' => 82, // 回切准备中 不可创建标签点，不可开始接管
        'VOL_CDP_TASK_RECOVERY_STARTING' => 100, // 恢复准备中
        'VOL_CDP_TASK_RECOVERY_WAITING_DATA_TRANSFER' => 101, // 恢复数据传输中
        'VOL_CDP_TASK_RECOVERY_WAITING_AGENT_HANDLE' => 102, // 代理预处理
        'VOL_CDP_TASK_RECOVERY_FINISH' => 103, // 恢复完成
        'VOL_CDP_TASK_RECOVERY_FAILED' => 104, // 恢复中止
    ],
    // 数据库实时阶段枚举定义
    'DB_CDP_PROTECT_STAGE_LIST' => [
        'WAIT_EXEC' => 1, // 等待
        'DICT_EXPORT' => 10, // 数据字典导出
        'DICT_IMPORT' => 11, // 数据字典导入
        'FULL_SYNC' => 20, // 全量数据同步
        'LOG_SYNC' => 21, // 日志数据同步
        'DB_CDP_TASK_IN_CONSTRAINT_IMPORT' => 22, // 复制/恢复任务导入约束
        'IN_TAKEOVER_STARTING' => 30, // 接管启动中
        'IN_TAKEOVER' => 31, // 接管中
        'FAILBACK_DICT_EXPORT' => 40, // 回切数据字典导出
        'FAILBACK_DICT_IMPORT' => 41, // 回切数据字典导入
        'FAILBACK_FULL_SYNC' => 50, // 回切全量数据同步
        'FAILBACK_LOG_SYNC' => 51, // 回切日志数据同步
        'SERVICE_FAILBACK' => 52, // 回切日志数据同步
        'FAILBACK_SUCCESSED' => 53, // 回切完成
        'DB_CDP_TASK_IN_FAILBACK_CONSTRAINT_IMPORT' => 54, // 接管回切导入约束
        'RESTORE_IN_RUNNING' => 60, // 恢复运行中
        'TASK_RESTORE_FINISH' => 61, // 恢复完成
        'IN_CONSTRAINT_IMPORT' => 22, // 约束导入
        'IN_FAILBACK_CONSTRAINT_IMPORT' => 54, // 回切约束导入
        'DB_CDP_TASK_RESTORE_FINISH' => 61, // 恢复任务完成
    ],
    // 普通任务任务阶段枚举定义
    'COMMON_STAGE_LIST' => [
        'TYPE_PREPARE' => 1, // 任务准备 同【运行中】状态，无特殊限制
        'TYPE_DATA_TRANSPORT' => 2, // 数据传输 同【运行中】状态，无特殊限制
        'TYPE_INTEGRITY_CHECK' => 3, // 完整性校验 同【运行中】状态，无特殊限制
        'TYPE_VIRUS_CHECK' => 4, // 病毒查杀 同【运行中】状态，无特殊限制
        // 虚拟机 无特殊限制
        'REMOVE_RESIDUAL_RESOURCES' => 100, // 清除残留资源
        'VM_CREATE_SNAPSHOT' => 101, // 创建快照
        'VM_DEL_SNAPSHOT' => 102, // 删除快照
        'CALCULCATE_EFFECTIVE_DATA' => 103, // 计算有效数据
        'PREPARE_BACKUP_RESOURCES' => 104, // 准备备份资源
        'DESTROY_BACKUP_RESOURCES' => 105, // 销毁备份资源
        'PREPARE_RECOVERY_RESOURCES' => 106, // 准备恢复资源
        'DESTROY_RECOVERY_RESOURCES' => 107, // 销毁恢复资源
        'VOLUME_MOUNTING' => 108, // 卷挂载
        'VOLUME_UNINSTALLATION' => 109, // 卷卸载
        'GENERATE_IMAGE' => 110, // 生成镜像
        'CONVERT_IMAGE_FORMAT' => 111, // 转换镜像格式
        'UPLOAD_IMAGE' => 112, // 上传镜像
        'VM_GENERATE_TIME_POINT' => 113, // 生成时间点
        'VM_EXECUTE_RETENTION_POLICY' => 114, // 执行保留策略
        'VM_CREATE_STORAGE' => 115, // 创建存储
        'VM_DESTROY_STORAGE' => 116, // 销毁存储
        'CREATE_VIRTUAL_MACHINE' => 117, // 创建虚拟机
        'DESTROY_VIRTUAL_MACHINE' => 118, // 销毁虚拟机
        'VM_ADVANCED_RECOVERY_OPERATIONS' => 119, // 高级恢复操作
        'GENERATE_DIRECTORY_TREE' => 120, // 生成目录树结构
        'CBT_PROCESSING' => 121, // cbt处理
        'TIMEPOINT_DATA_TRANSMISSION' => 122, // 时间点数据传输
        'TRANSMISSION_OF_METADATA' => 123, // 元数据信息传输
        'VM_CACHE_DATA_TRANSMISSION' => 124, // 缓存数据传输
        'VM_TASK_STAGE_CREATE_AGENT' => 125, // 创建代理
        'VM_TASK_STAGE_POWEROFF_VM' => 126, // 关闭恢复虚拟机
        'VM_TASK_STAGE_POWERON_VM' => 127, // 开启恢复虚拟机
        'VM_TASK_STAGE_CREATE_VOLUME' => 128, // 创建卷
        'VM_TASK_STAGE_DESTORY_VOLUME' => 129, // 删除卷
        // 文件
        'LOAD_TARGET_OBJECT' => 200, // 加载目标对象信息
        'INITIALIZE_TARGET_OBJECT' => 201, // 初始化目标对象上下文
        'START_SCAN' => 202, // 启动扫描
        'SYNCHRONIZE_DATA_POOL' => 203, // 同步数据池
        'FILE_GENERATE_TIME_POINT' => 204, // 生成时间点
        'FILE_EXECUTE_RETENTION_POLICY' => 205, // 执行保留策略
        'CLEAN_RESOURCES' => 206, // 清理资源
        'EXECUTE_ARCHIVING' => 207, // 执行归档
        'GENERATE_HISTORY_TASKS' => 208, // 生成历史任务
        //文件复制
        'SYNC_FS_TASK_STARGE_LOAD_OBJECT_INFO' => 250, // 加载目标对象信息
        'SYNC_FS_TASK_STARGE_CREATE_OBJECT_PROGRESS' => 251, // 创建目标进程
        'SYNC_FS_TASK_STARGE_SEND_TASK_START_INFO' => 252, // 发送任务启动信息
        'SYNC_FS_TASK_STARGE_TASK_RUNNING' => 253, // 任务运行
        'SYNC_FS_TASK_STARGE_CACHE_CLEAN' => 254, // 资源清理
        'SYNC_FS_TASK_STARGE_TASK_RUNNING_INFO_COLLECT' => 255, // 任务运行结果统计
        'SYNC_FS_TASK_STARGE_HISTORY_CREATE' => 256, // 历史任务创建
        'SYNC_FS_TASK_STARGE_TASK_FINISH' => 257, // 任务退出
        'SYNC_FS_TASK_STARGE_TASK_RUNNING_PREPARE' => 258, //任务运行前置准备
        // 操作系统
        'OS_CLEAN_CLIENT_RESOURCES' => 301, // 清理客户端资源
        'OS_CREATE_SNAPSHOT' => 302, // 创建快照
        'CALCULATE_EFFECTIVE_DATA' => 303, // 计算有效数据
        'OS_GENERATE_TIME_POINT' => 304, // 生成时间点
        'OS_DEL_SNAPSHOT' => 305, // 删除快照
        'OS_EXECUTE_RETENTION_POLICY' => 306, // 执行保留策略
        'GET_AGENT_OF_RECOVERY' => 307, // 获取需要恢复的主机信息
        'ADVANCED_RECOVERY_OPERATIONS' => 308, // 高级恢复操作
        'CREATE_STORAGE' => 309, // 创建存储
        'DESTROY_STORAGE' => 310, // 销毁存储
        'CACHE_DATA_TRANSMISSION' => 311, // 缓存数据传输
        'STAGE_DEPLOY_RAMOS' => 312, // RAMOS部署
        // 数据库
        'CHECK_STATUS_OF_DATABASE' => 400, // 检查数据库运行状态
        'CHECK_STATUS_OF_LOG' => 401, // 检查日志状态
        'CREATE_SNAPSHOT' => 402, // 创建快照
        'DEL_SNAPSHOT' => 403, // 删除快照
        'EXECUTE_BACKUP_COMMAND_OF_DATABASE' => 404, // 执行备份数据库命令
        'EXECUTE_RECOVERY_COMMAND_OF_DATABASE' => 405, // 执行恢复数据库命令
        'CLEAN_CLIENT_RESOURCES' => 406, // 清理客户端资源
        'GENERATE_TIME_POINT' => 407, // 生成时间点
        'EXECUTE_RETENTION_POLICY' => 408, // 执行保留策略
        'CREATE_DATABASE' => 409, // 创建数据库/实例
        'DEL_DATABASE' => 410, // 删除数据库/实例
        'BACKUP_CONFIG_OF_DATABASE' => 411, //备份数据库配置文件
        'RECOVERY_CONFIG_OF_DATABASE' => 412, //恢复数据库配置文件
        'BACKUP_FILE_OF_DATABASE' => 413, //备份数据库数据文件
        'RECOVERY_FILE_OF_DATABASE' => 414, //恢复数据库数据文件
        'BACKUP_LOG_OF_DATABASE' => 415, //备份数据库日志文件
        'RECOVERY_LOG_OF_DATABASE' => 416, //备份数据库日志文件
        'DB_TASK_STAGE_START_DATABASE' => 417, //启动数据库实例
        'DB_TASK_STAGE_STOP_DATABASE' => 418, //停止数据库实例
        'DB_TASK_STAGE_SYNCHRONIZE_CLUSTER' => 419, //同步集群数据
        // 应用
        'APP_TASK_STAGE_TYPE_SAVE_TIMEPOINT' => 500,// 保存时间点
        'APP_TASK_STAGE_TYPE_DO_TIMEPOINT_RESERVED_STRATEGY' => 501,// 执行保留策略
        'APP_TASK_STAGE_TYPE_CREATE_TIMEPOINT' => 502,// 生成时间点
        'APP_TASK_STAGE_TYPE_CLEAN_CLIENT_RESOURCE' => 520,// 清理客户端资源
        'APP_TASK_STAGE_TYPE_CALCULATE_RECOVERY_OBJECTS' => 521,// 计算需要恢复的对象
        'APP_TASK_STAGE_TYPE_GET_AVAILABLE_CLIENT' => 522,// 获取可用客户端
        'APP_TASK_STAGE_TYPE_EXEC_PRE_HOOK' => 580,// 执行任务前置HOOK脚本
        'APP_TASK_STAGE_TYPE_EXEC_POST_HOOK' => 581,// 执行任务后置HOOK脚本
        'APP_TASK_STAGE_TYPE_EXEC_POST_FAILED_HOOK' => 582,// 执行任务失败后置HOOK脚本
        'APP_TASK_STAGE_TYPE_BACKUP_RESOURCES' => 583,// 资源备份
        'APP_TASK_STAGE_TYPE_RECOVER_RESOURCES' => 584,// 资源恢复
        'APP_TASK_STAGE_TYPE_CREATE_PVC_SNAPSHOT' => 585,// 创建PVC快照
        'APP_TASK_STAGE_TYPE_PREPARE_PVC_INSTANCES' => 586,// 准备PVC实例
        'APP_TASK_STAGE_TYPE_CLEAN_TMP_RESOURCES' => 587,// 清理临时资源
        // 副本
        'COPY_TASK_STAGE_LOAD_COPY_TASK_INFO' => 601, // 加载副本任务信息
        'COPY_TASK_STAGE_LOAD_COPY_ITEM_INFO' => 602, // 获取副本各对象信息
        'COPY_TASK_STAGE_CAL_COPY_ITEM_SIZE' => 603, // 计算副本任务量
        'COPY_TASK_STAGE_START_ITEM_COPY' => 604, // 副本对象数据
        'COPY_TASK_STAGE_UPDATE_AND_CHECK_COPY_TIMEPOINT' => 605, // 更新并检查数据位图文件
        'COPY_TASK_STAGE_CACHE_BITMAP_FILE' => 606, // 缓存数据位图文件
        'COPY_TASK_STAGE_DO_RESERVED_STRATEGY' => 607, // 执行保留策略
        // 数据验证
        'SR_TASK_STAGE_TYPE_PREPARE_VIRTUAL_LAB' => 700, // 准备虚拟演练室
        'SR_TASK_STAGE_TYPE_PREPARE_APPLICATION_GROUP' => 701, // 准备虚拟演练室
        'SR_TASK_STAGE_TYPE_NETWORK_VERIFYING' => 702, // 网络验证
        'SR_TASK_STAGE_TYPE_HEART_BEAT_VERIFYING' => 703, // 心跳验证
        'SR_TASK_STAGE_TYPE_SCREEN_SHOT_VERIFING' => 704, // 截屏验证
        'SR_TASK_STAGE_TYPE_DOCUMENT_VERIFYING' => 705, // 文件对比验证
    ],
    // 数据库复制任务阶段
    'DBCDP_TASK_RUNNING_STAGE' => [
        0 => xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'),
        1 => xphp_get_lang('WEB_PLATFORM_DES_WAITING'),
        10 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_DATA_DICTIONARY_EXPORT'),
        11 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_DATA_DICTIONARY_IMPORT'),
        20 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_FULL_SYNC'),
        21 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_LOG_SYNC'),
        22 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_CONSTRAINT_IMPORT'),
        30 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_IN_TAKEOVER_STARTING'),
        31 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_IN_TAKEOVERING'),
        40 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_FAILBACK_DICT_EXPORT'),
        41 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_FAILBACK_DICT_IMPORT'),
        50 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_FAILBACK_FULL_SYNC'),
        51 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_FAILBACK_LOG_SYNC'),
        52 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_SERVICE_FAILBACK'),
        53 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_FAILBACK_SUCCESSED'),
        54 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_FAILBACK_CONSTRAINT_IMPORT'),
        60 => xphp_get_lang('WEB_DB_CDP_DETAILS_STAGE_RESTORE_IN_RUNNING'),
        61 => xphp_get_lang('WEB_DB_CDP_TASK_RESTORE_FINISH'),
    ],
    // 业务类型定义
    'BUSINESS_TYPE' => [
        'unknown' => [
            'value' => 0,
            'text' => xphp_get_config('app', 'NULLSPACE')
        ],
        'backup' => [
            'value' => 1,
            'text' => xphp_get_lang('UI_PLATFORM_DATA_BACKUP')
        ],
        'vol' => [
            'value' => 2,
            'text' => xphp_get_lang('UI_PLATFORM_CDP_PROTECT')
        ],
        'copy' => [
            'value' => 3,
            'text' => xphp_get_lang('UI_PLATFORM_DATA_COPY')
        ],
    ],
    // 挂起原因枚举定义
    'PENDING_TYPE' => [
        1 => 'WEB_TASK_PENDING_TIP1', // 无可用驱动器
        2 => 'WEB_TASK_PENDING_TIP2', // 无可用磁带
        3 => 'WEB_TASK_PENDING_TIP3', // 磁带已被使用
        4 => 'WEB_TASK_PENDING_TIP4', // 内嵌资源不足
    ],
];
