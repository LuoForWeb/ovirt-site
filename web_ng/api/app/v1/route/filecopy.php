<?php

return [
    'filecopy' => [
        //同步页面
        'FileCopy' => [
            'filecopy_resource' => [
                'get' => 'getResourceInfo',//获取客户端、nas设备、hadoop集群、对象存储信息
                'put' => 'editResourceInfo',//获取修改同步任务选中的源和目标信息--废弃
            ],
            'filecopy_job_backup' => [
                'post' => 'createFileCopyJob',//创建文件同步任务
            ],

        ],
        //任务信息
        'FileCopyJobInfo' => [
            'filecopy_job_info' => [
                'get' => 'getFileCopyBasicInfo',//获取文件同步任务所有信息（用于修改）
            ],
            'filecopy_job_list' => [
                'get' => 'getFileCopyList',//获取复制列表
            ],
            'filecopy_job_history_list' => [
                'get' => 'getFileCopyHistoryList',//获取历史任务列表
            ],
            'filecopy_job_compare_list' => [
                'get' => 'getCompareList',//获取对比列表
            ],
            'filecopy_job_compare_result' => [
                'post' => 'getCompareResult',//获取对比结果
            ],
            'filecopy_job_copy_compare_result' => [
                'post' => 'copyCompareResult',//复制对比结果
            ],
            'filecopy_job_alarm' => [
                'get' => 'getFileCopyAlarm',//获取文件复制告警信息
            ],
            'filecopy_download_pass' => [
                'get' => 'downLoadPassData',//下载跳过文件
            ],
        ],
        //回收站
        'FileCopyRecycleBin' => [
            'filecopy_collection_task' => [
                'get' => 'getCollectionTask',//获取回收站任务和同步对象
            ],
            'filecopy_collection_path' => [
                'get' => 'getCollectionPath',//获取回收站文件目录
                'delete' => 'deleteCollectionData',//删除回收站数据
            ],
            
            
        ],
    ],
];
