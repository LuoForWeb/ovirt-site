<?php

/**
 * 存储设备
 */

return [

    ### 存储设备
    'resources' => [ // 模块名
        'Storage' => [ // 类名
            'storages' => [ // 路由名
                // 请求方式   => 方法名,
                'get' => 'getStorageInfo',   // 获取存储设备列表 / 详情
                'patch' => 'editStorageInfo',   // 修改存储设备
                'delete' => 'checkStorageInfo',   // 删除存储设备检测
            ],
            'storages_confirm' => [
                'delete' => 'delStorageInfo', // 删除存储设备确定
            ],
            'storages_autotime' => [
                'get' => 'getImportRefreshTime', // 得到存储设备自动导入时间点时间
                'post' => 'setImportRefreshTime', // ；设置存储设备自动导入时间点时间
            ],
            'storages_cbr' => [
                'get' => 'getCBRArea', // 获取CBR区域
            ],
            'storages_name' => [
                'get' => 'getNewStorageName', // 根据存储类型获取新的存储名称
            ],
            'storages_data' => [
                'get' => 'getImportData', // 导入数据管理列表 / 获取备份数据信息详情
            ],
            'storages_distribute' => [
                'post' => 'distributeDataToUser', // 分配导入数据到其他用户
            ],
            'storages_table' => [
                'get' => 'getAddStorageTable', // 获取添加存储的表格数据
            ],
            'storages_wwn' => [
                'get' => 'getWwnNum', // 获取存储WWN信息
            ],
            'storages_iscsi' => [
                'get' => 'getIscsiName', // 获取iscsi名称
            ],
            'storages_lun' => [
                'get' => 'getLunStorageList', //获取生产存储列表
                'post' => 'getIscsiLunTable', // 获取IQN对应的LUN信息
                'put' => 'editLunStorage', //修改生产存储
            ],
            'storages_lun_list' => [
                'get' => 'getLunList', // 获取LUN生成存储列表
            ],
            'storages_lun_snap' => [
                'get' => 'getLunSnap', // 获取LUN快照
            ],
            'storages_lun_add' => [
                'post' => 'addLunStorage', // 添加生成存储
            ],
            'storages_check_initiator' => [
                'post' => 'checkInitiator',
            ],
            'storages_sync' => [
                'post' => 'syncLunStorage', // 同步生产存储
            ],
            'storages_vendor' => [
                'get' => 'getVendor', // 获取云存储的云服务商列表
            ],
            'storages_bucket' => [
                'get' => 'getBucketFolder', // 获取云存储bucket folder列表
            ],
            'storages_region' => [
                'get' => 'getCloudRegion', // 获取云存储region
            ],
            'storages_remote' => [
                'get' => 'getRemoteStorages', //获取异地备份系统存储列表
                'post' => 'addCopyStorage', //添加存储设备之异地备份8
            ],
            'storages_chap_auth' => [
                'get' => 'getIscsiChapList1', //进行chap认证信息填写后再次扫描-第一次认证
            ],
            'storages_chap_auth_again' => [
                'get' => 'getIscsiChapList', //进行chap门户认证信息填写后再次扫描-第二次认证
            ],
            'storages_max_resource' => [
                'get' => 'getMaxStorage', //得到当前所有节点中存储最大的那个节点存储返回
            ],
            'storages_lanfree' => [
                'post' => 'addLanfreeStorage', //添加lan-free存储
            ],
            'storages_common' => [
                'post' => 'addNewStorage', //添加存储设备1,2,3,4,5,6,7
            ],
            'storages_nas' => [
                'post' => 'addNasStorage', //添加存储设备之nas系列11,101,102,dddb-16
            ],
            'storages_hwcbr' => [
                'post' => 'addHuaweiCBRStorage', //添加存储设备之华为cbr12
            ],
            'storages_cloud' => [
                'post' => 'addCloudStorage', //添加存储设备之云存储9
            ],
            'storages_parallel' => [
                'post' => 'addParallelStorage', //添加存储设备之并行文件13
            ],
            'storages_type' => [
                'get' => 'getStorageRecoveryList' // 获取存储列表（恢复时间点筛选）
            ],
            'storages_hand_sync' => [
                'post' => 'syncStorageInfo', // 手动同步
            ]
        ],
        'Node' => [
            'storages_backup' => [
                'get' => 'getBackupStorageList', // 得到某个节点下的可用存储
            ],
        ],
        'SpeedLimit' => [ // 全局限速策略
            'storages_global_speed' => [
                'get' => 'getList', // 获得列表
            ],
            'storages_global_speed_add' => [
                'post' => 'addSpeed', // 添加
            ],
            'storages_global_speed_detail' => [
                'get' => 'viewSpeed', // 获取详情
            ],
            'storages_global_speed_edit' => [
                'post' => 'editSpeed', // 修改
            ],
            'storages_global_speed_del' => [
                'delete' => 'delSpeed', // 删除
            ],
            'storages_global_speed_send' => [
                'post' => 'sendSpeed', // 分发
            ],
            'storages_global_speed_job' => [
                'get' => 'getSpeedJob', // 获取可分发的任务列表
            ],
            'storages_global_speed_name' => [
                'get' => 'getGlobalSpeedName',
            ],
        ]
    ],

];
