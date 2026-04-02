<?php

/***
 * 模块相关的一些配置定义
 */

return [

    //api模块号,对应云端模块号
    'API_MODULE' => array(
        'Xemail' => 0,          //Xemail接口
        'Feedback' => 1,       //提交反馈接口
        'Sms' => 2,            //短信接口
        'Ueiplan' => 3,        //用户体验改善计划
        'Upgrade' => 4,        //系统升级
    ),

    //模块操作码文件定义
    'MODULE_OPCODE_TYPE' => [
        'UNKNOWN',
        '\app\v1\opcode\PfOpcode', //平台
        '\app\v1\opcode\VmOpcode', //虚拟机
        '\app\v1\opcode\FsOpcode', //文件
        '\app\v1\opcode\DbProtectOpcode', //数据库
        '\app\v1\opcode\OsOpcode', //操作系统
        '\app\v1\opcode\VmOpcode',
        '\app\v1\opcode\VmOpcode',
        '\app\v1\opcode\VmOpcode',
        '\app\v1\opcode\VmOpcode',
        '\app\v1\opcode\VolcdpOpcode', //卷CDP
        11 => '\app\v1\opcode\NasOpcode', //NAS
        12 => '\app\v1\opcode\DbcdpOpcode',  //DBCDP
        14 => '\app\v1\opcode\M365Opcode', //M365
        16 => '\app\v1\opcode\CopyOpcode', //副本
        18 => '\app\v1\opcode\VolcdpOpcode', //容灾主机
        19 => '\app\v1\opcode\OrchestrationOpcode', //策略
        21 => '\app\v1\opcode\ClusterOpcode',  //集群
        22 => '\app\v1\opcode\PfOpcode', //平台
        23 => '\app\v1\opcode\BackupDataOpcode',  //备份数据
        24 => '\app\v1\opcode\ToolOpcode',  //工具
        26 => '\app\v1\opcode\FcOpcode', //文件复制
        28 => '\app\v1\opcode\KubeOpcode',  //Kubernetes
        30 => '\app\v1\opcode\VerifyOpcode', //数据验证
        100 => '\app\v1\opcode\PfOpcodePrivate',//平台私有
        999 => '\app\v1\opcode\FsOpcode', //文件
        1000 => '\app\v1\opcode\NodeOpcode', //节点

    ],

    //模块定义
    'MODULE_TYPE' => [
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
        'VOL_CDP' => 10,             //与后台协商暂时定义为
        'NAS' => 11,  //nas
        'DB_CDP' => 12, //数据库实时
        'M365' => 14, //exchange
        'COPY' => 16, //副本
        'PUBLIC_CLOUD' => 17, //公有云
        'TEMP_AGENT' => 18, //容灾主机
        'STRATEGY' => 19, // 策略
        'CLUSTER' => 21,  // 集群
        'GRAIN' => 22, // 细粒度恢复
        'BACKUP_DATA' => 23, // 备份数据管理
        'TOOL' => 24, // 工具
        'FILE_COPY' =>26, //文件复制
        'KUBERNETES' => 28,
        'SUREBACKUP' => 30, //数据验证
        'PFPRIVATE' => 100,          //平台私有
        'NODE' => 1000,             //节点
        'OEM_DBCDP' => 10000,       //友商数据库CDP
        'OEM_FSCDP' => 10001,       //友商文件CDP
        'OBS' => 999,               // 对象存储
        'HADOOP' => 998,             // 对象存储
    ],
    // 模块文件定义
    'MODULE_NAME' => [
        // 模块标识（MODULE_TYPE里面的值） => 模块文件名
        2 => 'vm',          // 虚拟机 2
        4 => 'db',          // 数据库  4
        3 => 'file',        // 文件   3
        5 => 'os',          // 操作系统 5
        10 => 'volcdp',      // 卷实时 10
        11 => 'nas',         // NAS  11
        9 => 'copy',        // 副本   9
        16 => 'copy', // 重构副本
        999999999 => 'archive',     // 归档 9 副本和归档是一个模块

        // 以下是新模块
        12 => 'dbcdp',
        14 => 'exchange',   // office365（exchange）
        17 => 'vm',         //公有云指向vm
        23 => 'backupData', // 备份数据管理
        24 => 'tool',  /// 工具
        26 => 'filecopy',   //文件复制
        28 => 'k8s',        // 容器 28
        999 => 's3'         // 对象存储
    ],
    // web端搜索用到的模块列表定义
    'MODULE_WEB' => [
        // 虚拟机
        2 => [
            'auth' => 'vmprotect', // 授权名称
            'name' => xphp_get_lang('WEB_PLATFORM_DES_VM'), // 名称
            'child' => ['task_type', 'vm_type'], // 子节点包括虚拟化类型和任务类型
        ],
        // 文件
        3 => [
            'auth' => 'fileprotect', // 授权名称
            'name' => xphp_get_lang('WEB_PLATFORM_DES_FS'), // 名称
            'child' => ['task_type'], // 子节点包括任务类型
        ],
        // 数据库
        4 => [
            'auth' => 'db_protect', // 授权名称
            'name' => xphp_get_lang('WEB_PLATFORM_DES_DB'), // 名称
            'child' => ['task_type', 'db_type'], // 子节点包括任务类型和数据库类型
        ],
        // 操作系统
        5 => [
            'auth' => 'os_protect', // 授权名称
            'name' => xphp_get_lang('WEB_PLATFORM_DES_OS'), // 名称
            'child' => ['task_type'], // 子节点包括任务类型
        ],
        // cdp/卷实时/实时容灾保护
        10 => [
            'auth' => 'vol_cdp_protect', // 授权名称
            'name' => xphp_get_lang('UI_VISUAL_VOL_CDP'), // 名称
            'child' => ['task_type'], // 子节点包括任务类型
        ],
        // nas
        11 => [
            'auth' => 'nas_protect', // 授权名称
            'name' => xphp_get_lang('WEB_PLATFORM_DES_NAS'), // 名称
            'child' => ['task_type'], // 子节点包括任务类型
        ],

    ],

    // 子模块定义
    'SUBMODULE_TYPE' => [
        'UNKNOWN' => 0,
        'FS' => 1,
        'NAS' => 2,
        'HADOOP' => 3,
        'OBS' => 4
    ],

    // 子模块定义
    'OS_SUBMODULE_TYPE' => [
        'UNKNOWN' => 0,
        'MACHINE_OS' => 1, //定时整机
        'OS' => 2, //以前老的
    ],

    // 子模块描述
    'SUBMODULE_TYPE_DES' => [
        1 => xphp_get_lang('WEB_PLATFORM_DES_FS'),
        2 => 'NAS',
        3 => 'Hadoop',
        4 => xphp_get_lang('WEB_PLATFORM_DES_OBS')
    ],
    //虚拟机子模块类型
    "VM_SUB_MODULE" => array(
        'UNKNOWN' => 0,
        'VM' => 1,
        'PRIVATE_CLOUD' => 2,
        'PUBLIC_CLOUD' => 3,
    ),

];