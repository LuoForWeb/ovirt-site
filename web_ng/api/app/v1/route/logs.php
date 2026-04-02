<?php

/**
* 日志相关的路由定义
 */

return [
    'log' => [ // 模块名
        'Index' => [ // 类名
            'logs_job'    => [
                'get'       => 'getJobLog',  // 获取任务日志列表
                'post'      => 'exportJobLog', //导出全部任务日志
                'delete'    => 'deleteJobLog', //删除任务日志
            ],
            'logs_system'    => [
                'get'       => 'getSystemLog',  // 获取系统日志列表
                'post'      => 'exportSystemLog', //导出全部系统日志
                'delete'    => 'deleteSystemLog', //删除系统日志
            ],
            'logs_system_get_download_system_log'    => [
                'post'      => 'getNodeSystemLogList', //获取各个节点系统日志列表
            ],
            'logs_system_download_system_log'    => [
                'post'      => 'downloadSystemLog', //下载系统日志
            ],
            'logs_jobs_running_logs'    => [
                'get'       => 'getRunningJobLog',  // 获取任务运行日志
            ],
            'logs_history_jobs_running_logs'    => [
                'get'       => 'getHistoryRunningJobLog',  // 获取历史任务运行日志
            ],
            'logs_ha_logs' => [
                'get'       => 'getHaLogList',  // 获取高可用日志列表
                'post'      => 'exportAllHaLog',  // 导出全部高可用日志
                'delete'    => 'deleteHaLog', // 删除高可用日志
            ],
        ],
    ],

];
