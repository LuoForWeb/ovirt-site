<?php

/**
 * 全局策略相关的路由定义
 */

return [
    'resources' => [ //模块名
        'Strategies' => [ //类名
            'strategies' => [ //路由
                //获取策略组列表
                'get' => 'getStrategyList',
                // 删除策略组
                'delete' => 'deleteStrategy',
                // 添加策略组
                'post' => 'addStrategy',
                // 修改策略
                'put' => 'editStrategy',
            ],
            'strategies_jobs' => [
                //获取策略组可分发的任务列表
                'get' => 'getTaskList',
                'post' => 'getTaskList',
            ],
            'strategies_jobs_occupation' => [
                //获取使用策略组的任务
                'get' => 'checkTaskStrategy',
            ],
            'strategies_name' => [
                //获取策略名称
                'get' => 'getStrategyName',
            ],
            'strategies_details' => [
                // 获取修改前策略信息
                'get' => 'getOldStrategyInfo',
            ],
            'strategies_check' => [
                // 检查策略是否被使用
                'get' => 'editStrategyCheck',
            ],
            'strategies_distribution' => [
                // 分发策略到备份任务
                'put' => 'dispenseStrategy',
            ],
            'strategies_select' => [
                //获取模块相关的策略组列表
                'get' => 'getStrategySelect',
            ],
        ],
    ],
];
