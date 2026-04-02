<?php

/**
* kubernetes的配置信息
 */

return [
    //集群状态
    'CLUSTER_ONLINE_STATUS' => [
        'UNKNOWN' => 0,
        'ONLINE' => 1,// 集群在线
        'OFFLINE' => 2,// 集群离线
        'DEPLOYING' => 3,// 远程部署开始部署，设置值为部署中
        'DEPLOYED' => 4,// 远程部署完成，设置值为已部署完成
        'DEPLOY_FAILED' => 5,// 远程部署失败，设置值为部署失败
       
    ],
    //节点状态
    'NODE_ONLINE_STATUS' => [
        'UNKNOWN' => 0, //未知
        'ONLINE' => 1,// 节点在线
        'OFFONLINE' => 2,// 节点离线
    ],
    //节点类型
    'NODE_IN_MASTER' => [
        'UNKNOWN' => 0,
        'IN_MASTER' => 1,
        'NOT_IN_MASTER' => 2,
    ],

    //任务备份类型
    'TASK_TYPE' => [
        'UNKNOWN' => 0,
        'NAMESPACE' => 1,
        'APPLICATION' => 2,
    ],
    //任务跳过方式
    'TASK_SKIP_EXISTS' =>[
        'UNKNOWN' => 0,
        'TASK_SKIP_EXISTS' => 1,
        'TASK_SKIP_NEWER_EXISTS' => 2,
        'TASK_OVERWIRTE_EXISTS' => 3,
    ],
    //资源策略
    'RESOURCE_CATEGORY' => [
        'OTHERS' => 0, // or UNKNOWN
        'WORKLOADS' => 1,// 工作负载
        'SERVICES_AND_LOAD_BALANCING' => 2, // 服务和负载均衡
        'CONFIG_AND_STORAGE' => 3, // 配置和存储
        'ACCESS_CONTROL_AND_PERMISSIONS' => 4, // 访问控制和权限
        'NETWORK_POLICIES' => 5, // 网络策略
        'CRD' => 6, // 自定义资源的定义，自定义资源定义分为命名空间下的和集群级别的，但是只显示在集群资源下
        'CRD_INSTANCES' => 7, // 自定义资源实例，可以为命名空间下的，也可以为集群下的
        'PVC' => 8, // PVC
        'CLUSTER' => 9,
    ],
    // 应用资源类型
    'APP_TYPE' => [
        'KUBE_APP_TYPE_UNKNOWN' => 0,
        'KUBE_APP_TYPE_DEPLOYMENT' => 1,
        'KUBE_APP_TYPE_STATEFUL_SET' => 2,
        'KUBE_APP_TYPE_DAEMON_SET' => 3,
        'KUBE_APP_TYPE_JOB' => 4,
        'KUBE_APP_TYPE_CRON_JOB' => 5,
        'KUBE_APP_TYPE_PURE_POD' => 6, // 纯Pod应用
        'KUBE_APP_TYPE_HELM' => 7,
        'KUBE_APP_TYPE_NAMESPACE' => 8,
    ],
    //集群添加方式
    'CLUSTER_ADD_METHOD' => [
        'ADD_METHOD_UNKNOWN' => 0,
        'ADD_METHOD_AGENT_AUTO_TO_SERVER' => 1, // 客户端自动注册到服务器，定时扫描方式插入kube_cluster和kube_node数据
        'ADD_METHOD_ADD_BY_SERVER' => 2, // 服务端手动输入连接信息添加
        'ADD_METHOD_DEPLOY_BY_SSH' => 3, // 服务端通过SSH方式部署
        'ADD_METHOD_DEPLOY_BY_KUBE_CONFIG' => 4, // 服务端通过KubeConfig方式部署
    ],

    //集群object对象类型
    'KUBE_OBJECT_TYPE' =>[
        'KUBE_OBJECT_TYPE_UNKNOWN' => 0,
        'KUBE_OBJECT_TYPE_NAMESPACE' => 1,
        'KUBE_OBJECT_TYPE_APP' => 2,
        'KUBE_OBJECT_TYPE_PVC' => 3,
        'KUBE_OBJECT_TYPE_RESOURCE' => 4,
        'KUBE_OBJECT_TYPE_CLUSTER' => 5,
    ],
     


  
];
