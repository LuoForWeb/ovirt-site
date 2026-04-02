<?php

/**
* 客户端
 */

return [

    ### 客户端
    'resources' => [
        'Client' => [
            'agents' => [
                // 获取客户端列表 / 详情
                'get' => 'getClientInfo',
                // 删除客户端 / 批量删除客户端
                'delete' => 'deleteClient',
                // 修改客户端
                'patch' => 'updateClient',
                // 添加客户端
                'post' => 'addClient',
            ],
            'agents_applications' => [
                // 获取客户端应用列表 / 详情
                'get' => 'getClientAppInfo',
                // 删除客户端应用 / 批量删除客户端应用
                'delete' => 'deleteClientApp',
                // 添加应用/实例认证
                'post' => 'addClientApp',
            ],
            'agents_applications_cluster_name' => [
                /**
                 * 获取数据库集群别名
                 * @link \app\v1\resources\v0\controller\Client::getClientAppClusterName()
                 */
                'get' => 'getClientAppClusterName',
            ],
            'agents_cluster' => [
                // 获取客户端集群信息
                'get' => 'getAppCluster',
            ],
            'agents_disks' => [
                // 获取客户端的磁盘信息
                'get' => 'getClientDiskInfo',
            ],
            'agents_network_card' => [
                // 获取客户端网卡信息
                'get' => 'getClientNetworkCard',
            ],
            'agents_users_allocate' => [
                // 分配客户端的所有者用户
                'patch' => 'allocateClientUser',
            ],
            'agents_groups_adjust' => [
                // 调整客户端所处分组
                'patch' => 'adjustClientGroup',
            ],
            'agents_file' => [
                // 解析上传的客户端部署文件
                'post' => 'resolveClientDeployFile'
            ],
            'agents_instances' => [
                // 加载客户端实例
                'get' => 'loadClientInstance'
            ],
            'agents_refresh' => [
                // 刷新客户端
                'post' => 'refreshClient',
            ],
            'agents_auths' => [
                // 客户端授权/取消授权
                'post' => 'authClient',
                // 获取客户端授权信息
                'get' => 'getClientAuthInfo',
            ],
            'agents_packages' => [
                // 获取客户端下载列表
                'get' => 'getClientDownloadPackage',
            ],
            'agents_groups' => [
                // 获取客户端分组
                'get' => 'getClientGroupInfo',
                // 添加客户端分组
                'post' => 'addClientGroup',
                // 修改客户端分组
                'patch' => 'updateClientGroup',
                // 删除客户端分组
                'delete' => 'deleteClientGroup',
            ],
            'agents_domain_config' => [
                // 获取传输代理域名解析配置
                'get' => 'queryAgentDomainConfig',
                // 更新传输代理域名解析配置
                'put' => 'updateAgentDomainConfig',
            ],
            'agents_upgrade' => [
                // 获取客户端升级的列表
                'get' => 'getUpgradeClientList',
                // 客户端升级
                'post' => 'upgradeClient',
            ],
            'agents_log' => [
                // 获取客户端日志
                'get' => 'getClientLogList',
                // 下载客户端日志
                'post' => 'downloadClientLog',
            ],
            'agents_versions' => [
                // 获取虚拟化插件版本
                'get' => 'getAgentVersions'
            ],
            'agents_disk_files' => [
                /**
                 * 扫描客户端磁盘文件
                 * @link \app\v1\resources\v0\controller\Client::scanAgentDiskFiles()
                 */
                'get' => 'scanAgentDiskFiles',
            ],
            'agents_disk_files_search' => [
                /**
                 * 在指定目录下搜索客户端的磁盘文件
                 * @link \app\v1\resources\v0\controller\Client::searchAgentDiskFiles()
                 */
                'get' => 'searchAgentDiskFiles',
            ],
            'agents_download_plugins' => [
                'get' => 'getDownloadLink'
            ],
            'agents_download_name' => [
                'get' => 'getDoloadAgentName'
            ],
            'agents_app_type' => [
                'get' => 'getAgentAppType',  // 获取客户端应用类型
            ],
        ]
    ],
];
