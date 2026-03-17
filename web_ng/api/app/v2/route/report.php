<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2024-11-05 16:59:32
 * @Description: 报表路由
 * @version: 1.0
 */

/**
 * 备份节点 
 */

return [

    ### 备份节点 
    'report' => [ // 模块名
        ### 备份节点
        'Report' => [ // 类名
            'report' => [
                'get' => 'getReportList', // 获取报表列表
                'post' => 'createReport', // 复制新建报表
                'put' => 'updateReport', // 更新报表
                'delete' => 'deleteReport', // 删除报表
            ],
            'report_detail' => [
                'get' => 'getReportDetail', // 获取报表详情
            ],
            'report_tree' => [
                'get' => 'getReportTree', // 获取报表树
            ],
            'report_path_tree' => [
                'get' => 'getCustomReportFolderTree', // 获取自定义报表文件夹树
            ],
            'report_group' => [
                'post' => 'createReportGroup', // 创建报表组
                'put' => 'updateReportGroup', // 更新报表组
                'delete' => 'deleteReportGroup', // 删除报表组
            ],
            'report_storage_overview' => [
                'get' => 'getStorageOverview' // 获取存储概览数据
            ],
            'report_storage_usage_tendency' => [
                'get' => 'getStorageUsageTendency', //获取存储使用趋势
            ],
            'report_storage_avaliable_forecast' => [
                'get' => 'getStorageAvailabilityForecast', //获取存储容量可用性预测
            ],
            'report_storage_list' => [
                'get' => 'getStorageList', // 获取存储列表
            ],
            'report_tape_overview' => [
                'get' => 'getTapeOverview', // 获取磁带概览数据
            ],
            'report_tape_details' => [
                'get' => 'getTapeReportList', // 获取磁带备份数据明细
            ],
            'report_node_overview' => [
                'get' => 'getNodeOverview', // 获取节点概览数据
            ],
            'report_node_load_tendency' => [
                'get' => 'getNodeLoadTendency', // 获取节点负载趋势
            ],
            'report_node_details' => [
                'get' => 'getNodeReportList', // 获取节点备份数据明细
            ],


            'report_vm_backup_data' => [ // 获取虚拟机历史备份数据量大小
                'get' => 'getReportVmBackupdata'
            ],
            'report_vm_storage_usage' => [ // 获取虚拟机有效占用备份存储空间大小
                'get' => 'getReportVmStorageUsage'
            ],
        ],
    ]
];
