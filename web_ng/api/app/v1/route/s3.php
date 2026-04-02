<?php

/**
 * s3路由
 */

return [

    ### 对象存储
    'resources' => [
        // 模块名
        'S3' => [
            // 类名
            's3' => [
                // 路由名
                // 请求方式   => 方法名,
                'get' => 'getOBStroage',
                // 获取对象存储列表
                'post' => 'addOBStorage',
                // 添加对象存储
                'patch' => 'editOBStorage',
                // 修改对象存储
                'delete' => 'delOBStorage',
                // 删除对象存储
            ],
            's3_auth' => [
                'get' => 'getObsAuthList',
                'post' => 'addAuth',
                'delete' => 'removeAuth'
            ],
            's3_obs_refresh' => [
                'post' => 'refreshOBStorage' // 刷新对象存储
            ],
            's3_auto_refresh_interval' => [
                'get' => 'getAutoRefreshInterval',
                'post' => 'editAutoRefreshInterval'
            ],
            's3_default' => [
                'get' => 'getObsDefaultName'
            ],
            's3_lisence_info' => [
                'get' => 'getObsLisenceInfo'
            ]
        ],
    ],
    ### 模块
    's3' => [
        ### 备份
        'ObsBackup' => [
            's3_backup_source_tree' => [
                'get' => 'getObsBackupTree' // 获取对象存储树 
            ],
            's3_file_dir_tree' => [
                'get' => 'getFileDirTree' // 获取对象存储对应的目录树
            ],
            's3_file_dir_son_tree' => [
                'get' => 'getFileDirSonTree' // 获取对象存储对应的目录树展开的子树
            ],
            's3_backup_job' => [
                'post' => 'createBackupJob',
                // 创建备份任务
                'put' => 'editBackupJob',
                // 修改备份任务
            ],
            's3_backup_info' => [
                'get' => 'getBackupTaskInfo',
                // 获取备份任务信息
            ]
        ],
        ### 恢复
        'ObsRecover' => [
            's3_recover_data_tree' => [
                'get' => 'getObsDataTree' // 获取对象存储恢复时间点树
            ],
            's3_sync_backup_timepoint' => [
                'get' => 'getSyncBackupTimePoint' //获取对象备份时间点
            ],
            's3_stop_search' => [
                'post' => 'stopSearchJob' // 停止搜索消息
            ],
            's3_check_encrypt' => [
                'post' => 'checkObsEncryptPass' // 检验对象数据加密密码正确性
            ],
            's3_recover_dir' => [
                'get' => 'getRecoverDir' // 得到恢复文件列表
            ],
            's3_recover_path_tree' => [
                'get' => 'getRecoverPathTree' // 获取恢复路径树
            ],
            's3_create_search_job' => [
                'post' => 'createSearchJob' // 创建搜索消息
            ],
            's3_search_info' => [
                'get' => 'getSearchInfo' // 获取搜索结果
            ],
            's3_recover_obs_tree' => [
                'get' => 'getRecoverObsTree' // 获取恢复对象存储树
            ],
            's3_recover_job' => [
                'post' => 'createRecoverJob', // 创建恢复任务
            ],
            's3_default_name' => [
                'get' => 'getDefaultRecoverTaskName'
            ]
        ],
        ### 对象存储数据管理
        'ObsData' => [
            's3_timepoint_grid' => [
                'get' => 'getObsTimePointGrid' // 获取对象时间点列表信息
            ],
            's3_search_timepoint' => [
                'get' => 'searchObsTimePoint' // 搜索对象时间点
            ],
            's3_batch_timepoint' => [
                'delete' => 'deleteSelectedTimePoints' // 删除批量备份时间点
            ],
            's3_manage_data' => [
                'delete' => 'deleteTimePoint',
                'put' => 'remarkTimePoint'
            ],
            's3_star' => [
                'post' => 'addStar',
                'delete' => 'deleteStar'
            ]
        ],
        ### 对象存储任务详情
        'ObsJobInfo' => [
            's3_jobs_basic' => [
                //获取对象存储当前任务的某个任务的基本信息
                'get' => 'getBasicInfo',
            ],
            's3_jobs_speed' => [
                //获取当前任务的某个任务的任务流量
                'get' => 'getTaskSpeed',
            ],
            's3_jobs_detail' => [
                //获取任务详情表格信息
                'get' => 'getTaskDetail',
            ],
            's3_jobs_history' => [
                //获取任务详情历史表格信息
                'get' => 'getObsHistory',
            ],
            's3_jobs_start' => [
                // 启动任务
                'post' => 'startObsBackupJob',
            ],
            's3_download_pass' => [
                'get' => 'downLoadPassFile', //  下载跳过文件
            ]
        ]
    ]
];