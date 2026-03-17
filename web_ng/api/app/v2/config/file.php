<?php

/**
* 文件CDP配置
 */

return [
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
    //文件类型
    'FILETYPE' => array(
        'UNKNOWN' => 0,     // unknown type
        'FILE' => 1,        // file type
        'DIRECTORY' => 2,   // directory type
        'FIX_DRIVER' => 3,  // drive type, flash drive or hard disk
        'REMOVABLE' => 4,   // removabal drive, usb key and so on
        'REMOTE' => 5,      // remote drive
    ),
];
