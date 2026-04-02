<?php

/**
 * 备份节点
 */

return [

    ### 备份节点
    'resources' => [ // 模块名
        ### 备份节点
        'Node' => [ // 类名
            'nodes' => [ // 路由名
                // 请求方式   => 方法名,
                /**
                 * 获取节点列表 & 获取单个节点详情
                 * @link \app\v1\resources\v0\controller\Node::getNodes()
                 */
                'get'  => 'getNodes',
                /**
                 * 添加节点
                 * @link \app\v1\resources\v0\controller\Node::addNode()
                 */
                'post' => 'addNode',
                /**
                 * 修改节点 & 修改节点名称(主节点)
                 * @link \app\v1\resources\v0\controller\Node::editNode()
                 */
                'patch' => 'editNode',
                /**
                 * 删除节点
                 * @link \app\v1\resources\v0\controller\Node::deleteNode()
                 */
                'delete' => 'deleteNode',
            ],
            'nodes_edit_name' => [
                'post'   => 'editNodeHostName', // 修改节点名字
            ],
            'nodes_network_card' => [
                'get'   => 'getNodesNetworkCard', // 获取某个节点的网络信息
            ],
            'nodes_select' => [
                'get' => 'getAddStorageNodeSelect', // 得到添加存储的时候的可用节点列表,同时用户备份选择存储节点
            ],
            'nodes_get_timepoint' => [
                'get' => 'getTimepointAllNode', // 得到恢复的时候所有有虚拟机|数据库备份数据的节点列表
            ],
            'nodes_resources_limit' => [
                // 设置节点的资源限制
                'put' => 'setNodeResourcesLimit',
                // 获取节点的资源限制
                'get' => 'getNodeResourcesLimit',
            ],
            'nodes_resources_limit_batch' => [
                // 批量获取节点的资源限制
                'get' => 'getNodeResourcesLimitByNodeUuidList',
            ],
            // 节点网卡
            'nodes_network' => [
                'get' => 'getNodeNetwork',  // 获取网卡列表和网卡详情
                'post' => 'addNodeNetwork',  // 添加网卡
                'patch' => 'editNodeNetwork',  // 编辑网卡
                'delete' => 'deleteNodeNetwork',  // 删除网卡信息
            ],
            'nodes_network_sort' => [
                'post' => 'sortNodeNetwork',  // 排序节点网络
            ],
            'nodes_allocation' => [
                'get' => 'getNodeAllocationList', // 获取节点分配列表
                'post' => 'allocationNodePlatform', // 分配备份节点到虚拟化平台
            ],
            'nodes_appliance' => [
                'get' => 'getApplianceSelect', // 获取传输代理列表
            ],
            'nodes_ip' => [
                /**
                 * 扫描本地节点的IP列表
                 * @link \app\v1\resources\v0\controller\Node::scanLocalNodeIPList()
                 */
                'get' => 'scanLocalNodeIPList',
            ],
            'nodes_cache' => [
                /**
                 * 获取节点缓存
                 * @link \app\v1\resources\v0\controller\Node::getNodeCache()
                 */
                'get' => 'getNodeCache',
                /**
                 * 设置节点缓存
                 * @link \app\v1\resources\v0\controller\Node::setNodeCache()
                 */
                'patch' => 'setNodeCache',
            ],
            'nodes_all_ip' => [
                'get' => 'getBackupServerIp', // 获取备份系统节点IP
            ],
            'nodes_resource_pool_name' => [
                'get' => 'getResourcePoolName', // 获取资源池名称
            ],
            'nodes_timepoints_node' => [
                'get' => 'getNodeUUIDWithTimepointUUID',  // 根据时间点UUID获取节点UUID
            ],
            'nodes_master_network' => [
                'get' => 'getNodesMasterNetwork', // 获取主节点网络信息
            ],
            'nodes_storage' => [
                'get' => 'getNodesByStorageUUID',   //根据存储UUI获取节点集合
            ]
        ],
    ]
];
