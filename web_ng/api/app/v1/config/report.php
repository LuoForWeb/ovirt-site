<?php
/**
 * 报表
 */

return [
    //报表模块定义
    'MODULE_REPORT' => [
        'UNKNOWN' => 0,
        'TASK' => 1,  //任务
        'STORAGE' => 2,  //存储
        'CLIENT' => 3,  //客户端
        'NAS' => 4,  //NAS设备
        'VM' => 5,  //虚拟机设备
        'FILE' => 6, //文件模块
        'DB' => 7,  // 数据库
        'OS' => 8,  //操作系统
        'VOL_CDP' => 9,  //卷CDP
        'PUBLIC_CLOUD' => 10, // 公有云
        'PRIVATE_CLOUD' => 11, // 私有云
        'OBS' => 12, // OBS
        'HADOOP' => 13, // HADOOP
        'FILECOPY' => 14, // 文件复制
        'M365' => 15, // M365
        'K8S' => 16, // K8S
    ],
    // 报表数据统计时间间隔范围类型定义
    'TIME_INTERVAL' => [
        'UNKNOWN' => 0,
        'LAST_DAY' => 1, //最近一天
        'LAST_THREE_DAYS' => 2, //最近三天
        'LAST_WEEK' => 3, //最近一周
        'LAST_MONTH' => 4, //最近一月
        'RECENT_FORTEEN_DAYS' => 5, // 最近十四天
    ],
    'CLIENT_RUNNING_BACKUP_COPY_TASK_TYPES' => [1, 28, 32, 35, 46, 62, 65], // 客户端上运行的备份或复制任务所有类型
];
