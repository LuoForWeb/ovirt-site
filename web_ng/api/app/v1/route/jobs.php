<?php

/**
 * 任务相关的路由定义
 */

return [
    'job' => [ // 模块名
        'JobController' => [ // 类名
            'jobs_start' => [
                'post' => 'startJob',  // 启动任务
            ],
            'jobs_stop' => [
                'post' => 'stopJob', // 停止任务
            ],
            'jobs' => [
                'delete' => 'delJob', // 删除任务
            ],
            'jobs_start_takeover' => [
                'post' => 'startTakeover', // 启动接管任务
            ],
            'jobs_stop_takeover' => [
                'post' => 'stopTakeover', // 停止接管任务
            ],
            'jobs_switch_autotakeover' => [
                'post' => 'switchTakeoverConfig', // 切换自动接管
            ],
            'jobs_start_failback' => [
                'post' => 'startFailback', // 启动回切任务
            ],
            'jobs_pause' => [
                'post' => 'pauseTask', // 暂停任务
            ],
            'jobs_continue' => [
                'post' => 'continueTask', // 继续任务
            ],
            'jobs_history' => [
                'delete' => 'delHistory', // 删除历史任务
            ],
            'jobs_log' => [
                'get' => 'downloadHistoryCheck', // 下载历史任务日志检查
            ],
            'jobs_log_down' => [
                'get' => 'downLoadTaskLog', // 下载任务告警日志
            ],
            'jobs_pending' => [
                'put' => 'updatePendingSort', // 更新优先级
            ],
        ],
        'JobInfo' => [ // 类名
            'jobs' => [
                'get' => 'getJob', // 获取当前任务
            ],
            'jobs_export' => [
                'post' => 'exportAllCurrentJobs', // 导出全部当前任务
            ],
            'jobs_info' => [
                'get' => 'getJobInfo', // 获取当前任务的某个任务的基本信息
            ],
            'jobs_flow' => [
                'get' => 'getJobFlow', // 获取单个任务的流量
            ],
            'jobs_history_export' => [
                'post' => 'exportAllHistoryJobs' // 导出全部历史任务
            ],
            'jobs_history' => [
                'get' => 'getHistory', // 获取历史任务
            ],
            'jobs_modules' => [
                'get' => 'getModule', // 获取模块列表
            ],
            'jobs_name' => [
                'get' => 'getValidName', // 根据任务名或语言包获取新的任务名
            ],
            'jobs_time_crow_list' => [
                'get' => 'getTimeCrowdList', // 获取每个小时任务占用个数列表
            ],
            'jobs_strategy_list' => [
                'get' => 'getStrategySelect', // 初始化策略选择下拉框
            ],
            'jobs_password_check' => [
                'get' => 'checkEncryptPass', // 初始化策略选择下拉框
            ],
            'jobs_pending' => [
                'get' => 'getPending', // 获取挂起任务列表
            ],
            'jobs_history_detail' => [
                'get' => 'getHistoryDetailHeader', // 获取历史任务抽屉的基本信息
            ],
        ]
    ]
];
