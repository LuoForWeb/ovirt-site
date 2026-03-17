<?php

/**
* 副本
 */

return [
    //副本历史任务状态
    'COPY_TASK_STATUS' => array(
        'COPY_ITEM_STATUS_UNKNOWN' => 0,
        'COPY_ITEM_STATUS_SUCCESS' => 1,
        'COPY_ITEM_STATUS_FAILED' => 2,
        'COPY_ITEM_STATUS_RUNNING' => 3,
        'COPY_ITEM_STATUS_WAITTING' => 4,
        'COPY_ITEM_STATUS_ABNORMAL' => 5,
    ),
     
    //副本源类型
     "COPY_SRC_TYPE" => array(
        "BACKUP_TASK" => 1,//备份任务
        "BACKUP_DATA" => 2,//备份数据
        "COPY_TASK" => 3,//副本任务
        "COPY_DATA" => 4,//副本数据
     ),

    //副本类型
    "COPY_MODE" => array(
        "MIRROR" => 1,//镜像副本
        "COMBINED" => 2,//合并副本
    ),

];
