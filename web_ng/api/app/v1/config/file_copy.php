<?php

/**
 * 文件复制配置
 */

return [
    //文件对比异常等级
    'COMPARE_RESULT_LEVEL' => array(
        "FILE_COMPARE_RESULT_LEVEL_UNKNOW" => 0,
        "FILE_COMPARE_RESULT_LEVEL_NORMAL" => 1,//正常
        "FILE_COMPARE_RESULT_LEVEL_DIFF_ONLY" => 2,//差异
        "FILE_COMPARE_RESULT_LEVEL_ABNORMAL_ONLY" => 4,//异常
        "FILE_COMPARE_RESULT_LEVEL_DIFF_ABNORMAL" => 5,//前面两个都有
        "FILE_COMPARE_RESULT_LEVEL_ERROR" => 6,//本身错误
    ),

    //启动任务发送的sync_mode
    'SYNC_MODE' => array(
        "SYNC_FS_TASK_MODE_UNKNOWN" => 0,
        "SYNC_FS_TASK_MODE_COMPARE" => 1,//文件对比
        "SYNC_FS_TASK_MODE_OTHER_COMPARE" => 2,//其他过来调用的
        "SYNC_FS_TASK_MODE_SYNC_TO_OTHER" => 3,//文件同步到其他端(文件复制发这个)
        "SYNC_FS_TASK_MODE_SYNC_TO_SELF" => 4,//文件同步到自己
        "SYNC_FS_TASK_MODE_SYNC_BOTH" => 5,//双方互相同步
        "SYNC_FS_TASK_MODE_SYNC_MINIOR" => 6,//镜像同步
        "SYNC_FS_TASK_MODE_WAIT_FILE_SYNC" => 7,//外部等待状态模式，这个设置后，需要后续调用进行取消或者改动（对比结果复制发这个）
    ),
];
