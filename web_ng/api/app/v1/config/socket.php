<?php

/**
* socket的配置信息
 */

return [
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

    //消息模式  同步/异步
    'MSG_MODE_TYPE' => array(
        'UNKNOWN' => 0,
        'SYNC' => 1,
        'RSYNC' => 2,
    ),

    //ActiveMQ消息推送模式
    'ACTIVEMQ_MODE' => array(
        'queue' => 1,
        'topic' => 2
    ),

    //推送消息前缀
    'MQ_MSG_NAME' => array(
        'job' => array(
            'backup' => 'Vinchin.Job.Backup.',
            'recovery' => 'Vinchin.Job.Recovery.',
            'instant' => 'Vinchin.Job.Instant.',
            'motion' => 'Vinchin.Job.Motion.',
        ),
        'alarm' => array(
            'job' => 'Vinchin.Alarm.Job',
            'system' => 'Vinchin.Alarm.System'
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


];
