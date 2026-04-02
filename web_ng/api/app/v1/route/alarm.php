<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-01-22 11:09:52
 * @LastEditTime: 2026-02-28 17:56:42
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */

/**
 * 告警相关的路由定义
 */

return [
    'alarm' => [ // 模块名
        'Alarm' => [ // 类名
            'alarm_job'    => [
                'get' => 'getJobAlarm',  // 获取任务告警列表/详情信息
                'post' => 'exportJobAlarm', //导出任务告警
                'delete' => 'deleteTaskAlarm' //删除任务告警
            ],
            'alarm_job_item'    => [
                'get' => 'getJobAlarmModuleItem',  // 返回各个模块的对象信息
            ],
            'alarm_job_response'    => [
                'post' => 'setTaskAlarmStatus',  // 设置响应状态
            ],
            'alarm_job_sync'    => [
                'get' => 'getJobAlarmSync',  // 同步任务告警到集中管理平台
            ],
            'alarm_job_server_log' => [
                'get' => 'getJobAlarmServerLog'  //获取任务告警关联的后台日志信息
            ],
            'alarm_job_download_server_log' => [
                'get' => 'downloadServerLog'  //下载后台日志文件
            ],
            'alarm_job_download_log' => [
                'get' => 'downLoadTaskLog'  //下载后台日志文件
            ],
            'alarm_system'    => [
                'get' => 'getSystemAlarm',  // 获取系统告警列表/详情
                'post' => 'exportSystemAlarm', //导出系统告警
                'delete' => 'deleteSystemAlarm' //删除系统告警
            ],
            'alarm_system_response'    => [
                'post' => 'setSystemAlarmStatus',  // 设置响应状态
            ],
            'alarm_dispatch' => [ // 发送告警邮件
                'get' => 'sendAlarmEmail',
                'post' => 'sendAlarmEmail',
            ],
        ],
    ],

];
