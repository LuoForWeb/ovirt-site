<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-03-17 10:12:09
 * @LastEditTime: 2026-01-20 17:23:08
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */

return [
    'copy' => [ // 模块名
        'CopyBackUp' => [ // 类名
            'copy_resources' => [ // 路由
                // 获取副本源树
                'post' => 'getCopySrcTask',
            ],
            'copy_resources_item' => [ // 路由
                // 获取副本源树
                'post' => 'getCopySrcItem',
            ],
            'copy_resources_remote_task' => [ // 
                // 获取异地副本数据树---任务
                'get' => 'getRemoteTreeTask',
            ],
            'copy_resources_remote_host' => [ //
                // 获取异地副本数据树---主机
                'get' => 'getRemoteTreeHost',
            ],
            'copy_resources_remote_timepoint' => [ //
                // 获取异地副本数据树---时间点
                'get' => 'getRemoteTreeTimePOint',
            ],
            'copy_resources_task' => [ // 
                // 获取修改前副本任务信息
                'get' => 'getCopyTaskInfo',
            ],
            'copy_resources_data' => [ //
                // 异步获取时间点
                'post' => 'getSyncCopyTimePoint',
            ],
            'copy_resources_type' => [ //
                // 获取源任务的数据类型
                'get' => 'getSourceDataType',
            ],
            'copy_jobs_name' => [ //
                // 获取副本任务名
                'get' => 'getCopyTaskName',
            ],
            'copy_nodes_net_remote' => [ //
                'get' => 'getRemoteNetInfo',
            ],
            'copy' => [ //
                'post' => 'createCopyJob',
                'put' => 'editCopyJob',
            ],
            'copy_back' =>[
                'post' => 'createCopyBackJob',
            ],
            'copy_resources_search' => [ //
                'post' => 'searchCopySource',
            ],
        ],
        'CopyData' => [ //
            'copy_data_remark' => [
                'put' => 'remarkTimePoint',
            ],
            'copy_data_mark' => [ //
                'post' => 'addStar',
                'delete' => 'deleteStar',
            ],
            'copy_data_search' => [ //
                'get' => 'searchCopyTimePoint',
            ],
            'copy' => [ //
                'delete' => 'deleteBatchCopyPoint',
            ],
        ],
        'CopyJobInfo' => [ //
            'copy_job_details' => [
                'post' => 'getCopyDetails',
            ],
            'copy_job_details_high' => [
                'get' => 'getHighInfo',
            ],
            'copy_job_details_transfer' => [
                'get' => 'getCopyTransferInfo',
            ],
            'copy_job_history' => [
                'get' => 'getCopyHistory',
            ],
            'copy_job_speed' => [
                'get' => 'getTaskSpeed',
            ],
            'copy_job' => [ //
                'get' => 'getCopyBasicInfo',
            ],
        ],
    ]
];