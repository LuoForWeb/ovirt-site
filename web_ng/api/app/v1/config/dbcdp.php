<?php

/**
 * 数据库实时相关的配置信息
 */

return [
    // 任务类型
    'DBCDP_TASK_TYPE' => array(
        'DB_CDP_TASK_TYPE_BACKUP' => 46,
        'DB_CDP_TASK_TYPE_RECOVER' => 47,
    ),
    //数据库实时字典导入/导出
    'DBCDP_CURRENT_DICT_TYPE' => array(
        'DB_CDP_TASK_IN_DICT_EXPORT' => 10,                 // 数据字典导出
        'DB_CDP_TASK_IN_DICT_IMPORT' => 11,                 // 数据字典导入
        'DB_CDP_TASK_IN_FULL_SYNC' => 20,                   //全量数据同步
        'DB_CDP_TASK_IN_CONSTRAINT_IMPORT' => 22,           //约束导入
        'DB_CDP_TASK_IN_FAILBACK_DICT_EXPORT' => 40,        //回切数据字典导出
        'DB_CDP_TASK_IN_FAILBACK_DICT_IMPORT' => 41,        //回切数据字典导入
        'DB_CDP_TASK_IN_FAILBACK_FULL_SYNC' => 50,          //回切全量数据同步
        'DB_CDP_TASK_IN_FAILBACK_CONSTRAINT_IMPORT' => 54   //回切约束导入
    ),
    //数据库实时任务状态
    'DBcdpTaskStatus' => array(
        'UNKNOWN' => 0,
        'WAITING' => 1,
        'STARTING' => 2,
        'RUNNING' => 3,  //任务运行中
        'PAUSED' => 4,  //任务暂停
        'PAUSING' => 5,  //任务暂停中
        'STOPPED' => 6,  //任务停止
        'STOPPING' => 7,  //任务停止中
        'NETWORK_FAULT' => 8,  //网络错误
        'SUCCESS' => 9,  //任务成功
        'FAIL' => 10,  //任务失败
        'ABNORMAL' => 11,  //任务异常
        'ERROR' => 12,  //任务错误
        'PREPARING' => 13,  //任务准备中
    ),
];
