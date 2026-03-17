<?php

/**
 * hadoop相关的路由定义
 */

return [
    'hadoop' => [ //模块名
        //集群管理
        'HadoopCluster' => [ //类名
            'hadoop_cluster' => [ //路由名
                //获取集群列表及相关节点信息
                'get' => 'getCluster',
                //添加集群
                'post' => 'addCluster',
                //修改集群
                'put' => 'editCluster',
                //删除集群
                'delete' => 'deleteCluster',
            ],
            'hadoop_update_time' => [
                'put' => 'updateTime'    // 更新集群自动刷新时间
            ],
            'hadoop_get_time' => [
                'get' => 'getTime'    //获取集群自动刷新时间
            ],
            'hadoop_refresh_cluster' => [
                'post' => 'refreshCluster'    // 更新集群自动刷新时间
            ],
            'hadoop_cluster_name' => [
                'get' => 'getClusterName'    //获取默认集群名称
            ],
            'hadoop_cluster_auth' => [
                'post' => 'authCluster'    //授权集群
            ],

        ],
        //集群备份
        'HadoopBackUp' => [ //类名
            'hadoop_backup_cluster_ztree' => [ //路由名
                //获取集群树
                'get' => 'getBackupZtree',
            ],
            'hadoop_backup_cluster_file_ztree' => [ //路由名
                //获取文件树
                'get' => 'getBackupFileZtree',
            ],
            'hadoop_backup_taskname' => [
                //获取备份任务名
                'get' => 'getBackupTaskName',
            ],
            'hadoop_jobs_backup' => [
                'post' => 'createBackupJob',    // 创建备份任务
                'put' => 'editBackupJob',    // 修改备份任务
            ],
            'hadoop_jobs_backup_info' => [
                'get' => 'getBackupTaskInfo',    //得到备份任务的所有信息（用于修改备份任务）
            ]
        ],
        //集群恢复
        'HadoopRecovery' => [ //类名
            'hadoop_recovery_cluster_list' => [
                'get' => 'getClusterList' //获取所有集群列表  
            ],
            'hadoop_recovery_cluster_ztree' => [
                'get' => 'getClusterZtree' //获取集群列表树
            ],
            'hadoop_recovery_timepoint_ztree' => [
                'get' => 'getTimepointZtree' //获取时间点树
            ],
            'hadoop_recovery_file_ztree' => [
                'get' => 'getFileZtree' //获取备份的文件列表
            ],
            'hadoop_recovery_create_search' => [
                'post' => 'createSearchJob' //创建搜索消息
            ],
            'hadoop_recovery_stop_search' => [
                'post' => 'stopSearchJob' //停止搜索消息            ]
            ],
            'hadoop_recovery_get_search' => [
                'post' => 'getSearchInfo' //得到恢复搜索结果       
            ],
            'hadoop_recovery_taskname' => [
                'get' => 'getRecoveryTaskName', //获取恢复任务名
            ],
            'hadoop_jobs_restore' => [
                'post' => 'createRecoverJob',    // 创建恢复任务
            ],
            'hadoop_recover_path_tree' => [
                'get' => 'getRecoverPathTree',    // 获取恢复路径树
            ]
        ],
        'HadoopData' => [
            'hadoop_restore_data' => [
                'get' => 'getRestoreTimepointTable', //获取时间点表格数据
            ],
            'hadoop_restore_data_remark_timepoint' => [
                'post' => 'remarkTimepoint', //给时间点添加备注
            ],
            'hadoop_restore_data_addstar' => [
                //给时间点添加星标
                'post' => 'addStar'
            ],
            'hadoop_restore_data_deletestar' => [
                //删除时间点星标
                'delete' => 'deleteStar'
            ],
            'hadoop_restore_data_timepoint' => [
                //删除时间点
                'delete' => 'deleteTimepoint'
            ],
            'hadoop_restore_data_search' => [
                //搜索时间点
                'get' => 'searchTimepoint'
            ],
        ],
        //任务信息
        'HadoopJobInfo' => [
            'hadoop_jobs' => [
                //获取hadoop当前任务的某个任务的基本信息
                'get' => 'getBasicInfo',
            ],
            'hadoop_jobs_speed' => [
                //获取当前任务的某个任务的任务流量
                'get' => 'getTaskSpeed',
            ],
            'hadoop_jobs_detail' => [
                //获取任务详情表格信息
                'get' => 'getHadoopDetail',
            ],
            'hadoop_jobs_start' => [
                // 启动任务
                'post' => 'startHadoopBackupJob',
            ],
            'hadoop_download_pass' => [
                 //  下载跳过文件
                'get' => 'downLoadPassFile'
            ]

        ]
    ]
];