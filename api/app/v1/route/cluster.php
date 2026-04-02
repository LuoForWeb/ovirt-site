<?php

return [
    'cluster' => [
        'Cluster' => [
            'cluster_config' => [
                // 配置集群信息
                'put' => 'setClusterConfig',
                // 获取集群的配置信息
                'get' => 'getClusterConfig',
            ],
            'cluster_start' => [
                // 启动集群
                'post' => 'startCluster',
            ],
            'cluster_stop' => [
                // 停止集群
                'post' => 'stopCluster',
            ],
            'cluster_set_master' => [
                // 设置主节点
                'post' => 'setClusterMasterNode',
            ],
            'cluster_log' => [
                // 获取集群操作日志
                'get' => 'getClusterOperateLog',
            ],
        ],
    ],
];
