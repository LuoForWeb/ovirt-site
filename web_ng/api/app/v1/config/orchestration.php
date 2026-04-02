<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 任务编排状态相关定义
 * @Date: 2024-03-18 14:16:11
 * @LastEditTime: 2024-08-21 11:03:05
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

 return [
    // 编排计划启动方式
    'ORCHESTRATION_PLAN_START_TYPE' => array(
        'UNKNOWN' => 0,
        'TIME',
        'EVENT',
        'BOTH'

    ),
     
    // 编排计划阶段状态
     "ORCHESTRATION_PLAN_SECTION_STATUS" => array(
        'UNKNOWN' => 0,
        'WAITING',
        'RUNNING',
        'FINISHED',
        'STOPPED',

     ),

    // 阶段计划启动类型
    "ORCHESTRATION_PLAN_EVENT_TYPE" => array(
        'UNKNOWN' => 0,
        'ALL_TASK_SUCCESS',
        'ANY_TASK_SUCCESS',
        'SOME_TASK_SUCCESS',
        'ALL_TASK_FAILURE',
        'ANY_TASK_FAILURE',
        'SOME_TASK_FAILURE'
    ),
    // 任务编排计划状态
	'ORCHESTRATION_PLAN_STATUS' => array(
		'UNKNOWN' => 0,
		'WAITING' => 1,
		'RUNNING' => 2,
		'PAUSED' => 3,
		'STOPPED' => 4,
		'ERROR' => 5,
    ),

];
