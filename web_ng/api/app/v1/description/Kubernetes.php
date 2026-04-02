<?php

/**
 *虚拟机模块描述定义
 *TODO 每一个定义都关联语言文件
 */

return [
    'ONLINE_STATUS' => [
        0=> xphp_get_lang('WEB_KUBE_CLUSTER_DEPLOYING'),
        1=> xphp_get_lang('WEB_KUBE_CLUSTER_DEPLOYMENT_COMPLETED'),
        2=> xphp_get_lang('WEB_KUBE_CLUSTER_DEPLOYMENT_FAILED'),
        3=> xphp_get_lang('WEB_KUBE_CLUSTER_ONLINE'),
        4=> xphp_get_lang('WEB_KUBE_CLUSTER_OFFLINE'),
    ],
    //集群object对象类型
    'KUBE_OBJECT_TYPE_DES' =>[
        0 => xphp_get_lang('WEB_KUBE_CLUSTER_UNKNOWN'),
        1 => xphp_get_lang('WEB_KUBE_RECOVERY_NAMESPACE'),
        2 => xphp_get_lang('WEB_KUBE_APP'),
        3 => 'PVC',
        4 => xphp_get_lang('WEB_KUBE_RESOURCE'),
        5 => xphp_get_lang('WEB_KUBE_CLUSTER_CLUSTER_RESOURCES'),
    ],
    'EXEC_STATUS_DES' =>[
        0 => xphp_get_lang('WEB_KUBE_CLUSTER_UNKNOWN'),
        1 => xphp_get_lang('WEB_PLATFORM_DES_RUNNING'),
        2 => xphp_get_lang('WEB_PLATFORM_DES_SUCCESSED'),
        3 => xphp_get_lang('WEB_PUBLIC_FAILURE_HOMEPAGE'),
    ]
   
];
