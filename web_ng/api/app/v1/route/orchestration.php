<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 任务编排计划相关路由定义
 * @Date: 2024-03-20 14:35:35
 * @LastEditTime: 2025-12-01 11:44:01
 * @Version: 1.0
 * @copyright: Copyright 2024 vinchin.com
 */

return [
    'orchestration' => [ // 模块名
        'taskOrchestration' =>[ // 类名
            'orchestration' => [ //路径
                'get' => 'getOrchestrationList', // 获取编排列表
                'delete' => 'deleteOrchestrationList', // 删除编排计划
                'post' => 'addOrchestrationList', // 新建编排计划
                'put' => 'modifyOrchestrationList', // 新建编排计划
            ],
            'orchestration_operate' => [
                'get' => 'operateOrchestration', // 获取编排列表
            ],
            'orchestration_plan' => [
                'get' => 'operatePlan', // 编排计划操作
                'delete' => 'deleteOrchestrationList', // 删除计划
            ],
            'orchestration_plan_name' => [
                'get' => 'getPlanName', //获取默认名称
            ],
            'orchestration_task' => [
                'post' => 'getTaskList', //获取任务列表
            ],
            'orchestration_details' => [
                'get' => 'getPlanDetails', //获取编排计划详细信息
            ],
            'orchestration_history' => [
                'get' => 'getHistoryList', //获取历史任务列表
            ],
        ]
    ],

];
