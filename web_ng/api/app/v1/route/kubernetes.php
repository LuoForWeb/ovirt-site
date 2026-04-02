<?php

/**
* 任务相关的路由定义
 */

return [
    
    //脚本管理
    'k8s' => [
        'K8sScript' => [
            'kubernetes_scripts' => [
                //获取脚本列表
                //获取单个脚本信息
                'get' => 'getScript',
                //添加脚本
                'post' => 'addScript',
                //删除脚本
                'delete' => 'deleteScript',
            ],
        ],
        //集群管理
        'K8sCluster' => [
            'kubernetes_cluster' => [
                //获取集群列表
                //获取单个集群
                'get' => 'getCluster',
                //修改单个集群
                'patch' => 'editCluster',
                //添加集群
                'post' => 'addCluster',
                //删除集群
                'delete' => 'deleteCluster',
            ],
            'kubernetes_cluster_refresh' => [
                //刷新列表
                'get' => 'refreshCluster',

            ],
            'kubernetes_cluster_nodeport' =>[
                //获取主节点所有地址
                'get' => 'getNetworkInfo',
            ],
            'kubernetes_cluster_ztree' => [
                //获取集群
                'get' => 'getClusterZtree',
            ],
            'kubernetes_cluster_namespaces' => [
                //获取集群下命名空间
                'get' => 'getNameSpace',
            ],
            'kubernetes_cluster_applications' => [
                //获取命名空间下应用
                'get' => 'getApp'
            ],
            'kubernetes_cluster_resource_group' => [
                //获取命名空间或应用下小分组
                'get' => 'getResourceGroup'
            ],
            'kubernetes_cluster_resource_big_group' =>[
                 //获取大的资源分类
                'get' => 'getResourceBigGroup'
            ],
            'kubernetes_cluster_resource' => [
                //获取小分组下资源
                'get' => 'getResource'
            ],
            'kubernetes_cluster_resource_details' => [
                //获取资源详情
                'get' => 'getResourceDetails'
            ],
        ],
        //备份
        'K8sBackUp' => [
            'kubernetes_jobs_backup' => [
                //创建kubernetes备份任务
                'post' => 'createBackupJob',
            ],
            'kubernetes_jobs_backup_name' => [
                //备份任务名
                'get' => 'getK8sBackupTaskName',
            ],
            'kubernetes_jobs_recover_name' => [
                //备份任务名
                'get' => 'getK8sRecoverTaskName',
            ],
        ],
        //备份数据
        'K8sData' => [
            'kubernetes_restore_data' => [
                //得到恢复或者备份数据的树形结构
                'get' => 'getRestoreData',
            ],
            'kubernetes_restore_data_task' => [
                //获取时间点
                'get' => 'getRestoreTimepoint',
            ],
            'kubernetes_restore_data_search' => [
                //获取时间点
                'get' => 'getRestoreZtree',
            ],
            'kubernetes_restore_data_table_task' => [
                //获取时间点
                'get' => 'getRestoreTimepointTable',
            ],
            'kubernetes_restore_data_timepoint' => [
                //获取时间点
                'get' => 'getRestoreTimepointData',
            ],
            'kubernetes_restore_data_resource' => [
                //获取时间点资源
                'get' => 'getRestoreTimepointResource',
            ],
            'kubernetes_restore_data_resource_details' => [
                //获取资源详情
                'get' => 'getRestoreTimepointResourcDetails',
            ],
            'kubernetes_restore_data_pvc' => [
                //获取资源详情
                'post' => 'getRestorePvc',
            ],
            'kubernetes_restore_data_remark_timepoint' => [
                //给时间点添加备注
                'post' => 'remarkTimepoint',
            ],
            'kubernetes_restore_data_addstar' => [
                //给时间点添加星标
                'get' => 'addStar'
            ],
            'kubernetes_restore_data_deletestar' => [
                //删除时间点星标
                'get' => 'deleteStar'
            ]
        ],


        //恢复
        'K8sRecover' => [
            'kubernetes_recovery' => [
                //创建kubernetes恢复任务
                'post' => 'createRecoveryJob',
            ],
        ],


        //任务相关
        'K8sJobInfo' => [
            'kubernetes_jobs_task_basicinfo' => [
                //获取任务基本信息
                'get' => 'getk8sJobBasicInfo',
            ],
            'kubernetes_jobs_history_details' => [
                //获取历史任务基本信息
                'get' => 'getk8sJobHistoryInfo',
            ],
            'kubernetes_jobs_namespace' => [
                //获取任务命名空间列表
                'get' => 'getTaskNamespace',
            ],
            'kubernetes_jobs_app' => [
                //获取任务应用列表
                'get' => 'getTaskApp',
            ],
            'kubernetes_jobs_pvc' => [
                //获取任务持久卷列表
                'get' => 'getTaskPvc',
            ],
            'kubernetes_jobs_resource' => [
                //获取任务资源列表
                'get' => 'getTaskResource',
            ],
            'kubernetes_jobs_backupinfo' => [
                //获取任务资源列表
                'get' => 'getTaskBackupInfo',
            ],
            'kubernetes_current_object' =>[
                //获取正在备份的资源
                'get' => 'getCurrentObject'
            ],
        ],

        
        
    ],
    
    
    
    
    
    

];
