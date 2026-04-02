<?php

/**
 * 高级恢复相关的路由定义
 */

return [
    'recovery' => [ // 模块名
        'RecoveryJobController' => [ // 高级恢复：恢复、瞬时恢复、迁移、细粒度恢复操作
            'recovery_graininess_agentlessis'    => [
                'post' => 'createGraininessJob', //创建细粒度恢复任务
            ],
            'recovery_agentlessis'    => [
                'post' => 'createAgentlessisJob', //创建无代理跨平台恢复任务
            ],
            'recovery_agent'    => [
                'post' => 'createAgentJob', //创建有代理跨平台恢复任务
            ],
            'recovery_instantaneous_agentlessis'    => [
                'post' => 'createInstanceLessJob', //创建无代理瞬时恢复任务
            ],
            'recovery_instantaneous_agent'    => [
                'post' => 'createInstanceJob', //创建有代理瞬时恢复任务
            ],
            'recovery_migrates_agentlessis'    => [
                'post' => 'createMigratesLessJob', //创建无代理迁移任务
            ],
            'recovery_migrates_agent'    => [
                'post' => 'createMigratesJob', //创建有代理迁移任务
            ],
            'recovery_job_motion'    => [
                'post' => 'stopInstanceJobOperate', // 停止瞬时恢复读写
            ],
            'recovery_timepoint_config'    => [
                'get' => 'convertPoints', // 获取时间点配置信息
            ],
        ],
        'RecoveryJobInfo' => [ // 高级恢复：恢复、瞬时恢复、迁移、细粒度恢复 信息展示
            'recovery_graininess'    => [
                'get' => 'graininessJobDetail', //细粒度恢复任务详情
            ],
            'recovery_detail'    => [
                'get' => 'jobDetail', //跨平台恢复任务详情
            ],
            'recovery_instantaneous'    => [
                'get' => 'instanceJobDetail', //瞬时恢复任务详情
            ],
            'recovery_migrates'    => [
                'get' => 'migratesJobDetail', //迁移任务详情
            ],
            'recovery_agent_network'    => [
                'get' => 'agentNetworkList', //获取客户端网络列表
            ],
            'recovery_vm_disk_network'    => [
                'get' => 'diskNetworkList', //获取虚拟化平台对应的磁盘总线类型和网络类型
            ],
            'recovery_job_object' => [
                'get' => 'getJobObject', // 获取跨平台、瞬时恢复和迁移任务详情的对象列表（包括vm和整机的）
            ],
            'recovery_job_script' => [
                'get' => 'getJobScript', // 获取跨平台、瞬时恢复和迁移任务详情的脚本详情（包括vm和整机的）
            ],
        ],
        'InstantaneousData' => [ // 瞬时恢复快照点管理的逻辑
            'recovery_instantaneous_points_tree'    => [
                'get' => 'instantanceTree', //瞬时恢复快照点之左边的树结构
                'delete' => 'delInstantanceTree', //删除备份时间点数据
            ],
            'recovery_instantaneous_points'    => [
                'get' => 'instantancePoints', //瞬时恢复快照点之具体的时间点列表
                'post' => 'createInstancePonit', //创建瞬时恢复快照点
                'delete' => 'instantancePointsDel', //瞬时恢复快照点之删除时间点
            ],
            'recovery_job_points' => [
                'get' => 'instantancePoint', // 获取瞬时恢复任务关联的时间点 迁移用
            ],
        ],
        'GraininessInfo' => [ // 细粒度恢复任务详情右边的文件等
            'recovery_graininess_tree'    => [
                'get' => 'graininessTree', //细粒度恢复任务详情资源树
            ],
            'recovery_search_graininess_tree'    => [
                'get' => 'searchGraininessTree', //细粒度恢复任务详情资源树
            ],
            'recovery_task_strategy'    => [
                'get' => 'taskStrategy', //
            ],
            'recovery_graininess_clients'    => [
                'get' => 'graininessClients', //客户端传输列表 / 客户端传输文件列表
                'post' => 'clientConfig', //客户端传输配置
            ],
            'recovery_graininess_network'    => [
                'get' => 'graininessNetwork', //网络共享列表 / 网络共享文件列表
                'post' => 'networkConfig', //网络共享配置
            ],
            'recovery_graininess_download'    => [
                'get' => 'downloadFiles', //网页下载连接
                'post' => 'downloadFile', //网页下载
            ],
            'recovery_graininess_network_look'    => [
                'get' => 'graininessNetworkPwd', //查看共享任务的访问密码
            ],
            'recovery_graininess_stop_clients'    => [
                'post' => 'graininessClientStop', //客户端传输的操作
            ],
            'recovery_graininess_operation_network'    => [
                'post' => 'graininessNetworkOperate', //网络共享的操作
                'delete' => 'graininessNetworkDel', //删除网络共享
            ],
            'recovery_graininess_file'    => [
                'delete' => 'grainSourceClear', // 细粒度文件下载完后删除对应的资源文件
            ],
            'recovery_graininess_job_name'    => [
                'get' => 'getValidName', // 根据任务名或语言包获取新的任务名
            ],
            'recovery_grains_log'    => [
                'get' => 'getTransferLog', // 客户端传输错误，下载跳过文件(子节点)
            ],
        ],
    ],

];
