<?php

/**
* 这是 系统管理对应的子类
 * level 为10 表示最底层的执行方法
 * function 存放权限数组路由 如 get-getConfigs 表示 get是请求的方法method，getConfigs是请求的url， get-* 其中*表示所有的get请求都可以访问（不建议这样给）
 * 注意，所有的模块都必须至少存在一个level为10的数组，不然权限校验匹配不到
 */

return [
    // 系统设置
    [
        'name' => 'setting_manager',
        'path' => './content/platform/settings/setting_manager.php',
        'class' => 'viconfont vicon-setting_manager',
        'title' => 'UI_PLATFORM_SYSTEM_SET',
        'level' => 1,
        'child' => [
            // 网络配置
            [
                'name' => 'system_network',
                'title' => 'UI_PLATFORM_NETWORK_SETTING',
                'class' => 'iconfont icon-ipaddress',
                'level' => 2,
                'child' => [
                    // IP地址
                    [
                        'name' => 'set_ip',
                        'title' => 'UI_PUBLIC_IP_ADDRESS',
                        'class' => 'iconfont icon-ipaddr',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_setting_manager_ip',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-system_network_info',
                                    'get-system_network_list',
                                    'post-system_network_info',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 域名解析
                    [
                        'name' => 'system_dns',
                        'title' => 'UI_PLATFORM_SYSTEM_DNS',
                        'class' => 'viconfont vicon-pt_setting_dns',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_system_dns_edit',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-system_network_host',
                                    'post-system_network_host',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 网卡聚合
                    [
                        'name' => "nic_teaming",
                        'title' => "UI_PLATFORM_NIC_TEAMING",
                        'class' => 'iconfont icon-wangkajihe',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_nic_teaming_edit",
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-system_network_nic',
                                    'post-system_network_nic',
                                    'delete-system_network_nic',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 网卡桥接
                    [
                        'name' => 'card_bridge',
                        'title' => 'UI_PLATFORM_CARD_BRIDEG',
                        'class' => 'iconfont icon-card_bridge',
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => 'p_card_bridge_list',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => [
                                    'get-system_network_bridge'
                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => 'p_card_bridge_add',
                                'title' => 'UI_PUBLIC_ADDNEW',
                                'function' => [
                                    'post-system_network_bridge'
                                ],
                                'level' => 10,
                            ],
                            // 删除
                            [
                                'name' => 'p_card_bridge_delte',
                                'title' => "UI_PUBLIC_DELETE",
                                'function' => [
                                    'delete-system_network_bridge'
                                ],
                                'level' => 10,
                            ],
                            // 隔离网段
                            [
                                'name' => 'p_card_bridge_isolate_network',
                                'title' => 'UI_PLATFORM_CARD_BRIDEG_ISOLATE',
                                'function' => [
                                    'get-system_network_isolate',
                                    'post-system_network_isolate'
                                ],
                                'level' => 10,
                            ],
                        ]
                    ]
                ]
            ],
            // 时间配置
            [
                'name' => 'set_time',
                'title' => 'UI_PLATFORM_SET_TIME',
                'class' => 'iconfont icon-shezhishijian',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_setting_manager_time',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-system_times',
                            'get-system_times_info',
                            'post-system_times_info',
                            'post-system_times_ntp',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 系统通知
            [
                'name' => 'system_notice',
                'title' => 'UI_PLATFORM_SYSTEM_NOTICE',
                'class' => 'iconfont icon-xitongtongzhi',
                'level' => 2,
                'child' => [
                    // 邮件通知
                    [
                        'name' => 'emial_notice',
                        'title' => 'UI_SETTINGS_NOTICE_EMAIL',
                        'class' => 'icon-envelope-open',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_emial_notice',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 短信通知
                    [
                        'name' => 'sms_notice',
                        'title' => 'UI_SETTINGS_NOTICE_SMS',
                        'class' => 'viconfont vicon-pt_setting_short_note',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_sms_notice',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 微信通知
                    [
                        'name' => 'wechat_notice',
                        'title' => 'UI_SETTINGS_NOTICE_WECHAT',
                        'class' => 'viconfont vicon-pt_setting_wechat_notice',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_wechat_notice',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 企业微信通知
                    [
                        'name' => 'enterprise_wechat_notice',
                        'title' => 'UI_SETTINGS_NOTICE_WECHAT_INTERNET2',
                        'class' => 'viconfont vicon-pt_setting_wechat2_notice',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_enterprise_wechat_notice',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [],
                                'level' => 10,
                            ],
                        ]
                    ],
                ]
            ],
            // 安全配置
            [
                'name' => 'system_safe',
                'title' => 'UI_PLATFORM_SAFE_SETTING',
                'class' => 'iconfont icon-anquanpeizhi',
                'level' => 2,
                'child' => [
                    // 账户安全
                    [
                        'name' => "account_safe",
                        'title' => "UI_PLATFORM_ACCOUNT_SAFE",
                        'class' => "iconfont icon-accountsafe",
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_account_safe",
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 存储安全
                    [
                        'name' => 'storage_safe',
                        'title' => 'UI_PLATFORM_STORAGE_SAFE',
                        'class' => 'iconfont icon-storagesafe',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_storage_safe',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 系统安全
                    [
                        'name' => 'os_safe',
                        'title' => 'UI_PLATFORM_OS_SAFE',
                        'class' => 'iconfont icon-ossafe',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_os_safe',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 数据安全
                    [
                        'name' => 'data_safe',
                        'title' => 'UI_PLATFORM_DATA_SAFE',
                        'class' => 'iconfont icon-ossafe',
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => 'p_data_safe_look',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => [
                                    'get-system_safe_data',
                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => 'p_data_safe_operate',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'post-system_safe_data',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                ]
            ],
            // 关机/重启
            [
                'name' => 'system_poweroff',
                'title' => 'UI_PLATFORM_POWER',
                'class' => 'iconfont icon-offrestart',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_setting_manager_power',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 系统升级
            [
                'name' => 'system_upgrade',
                'title' => "UI_PLATFORM_SYSTEM_UPDATE",
                'class' => 'iconfont icon-upgrade',
                'level' => 2,
                'child' => [
                    // 升级包管理
                    [
                        'name' => 'upgrade_manage',
                        'title' => "UI_SETTINGS_UPDATE_MANAGE",
                        'class' => 'viconfont vicon-pt_setting_package',
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => 'p_upgrade_manage_list',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                            // 上传升级包
                            [
                                'name' => 'p_setting_manager_upload',
                                'title' => 'UI_SETTINGS_UPLOAD_UPGRADE_PATCH',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                            // 删除升级包
                            [
                                'name' => "p_setting_manager_delete",
                                'title' => "UI_SETTINGS_DELETE_UPGRADE_PATCH",
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                            // 系统升级
                            [
                                'name' => 'p_setting_manager_upgrade',
                                'title' => "UI_PLATFORM_SYSTEM_UPDATE",
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 升级历史
                    [
                        'name' => 'upgrade_history',
                        'title' => "UI_SETTINGS_UPDATE_HISTORY",
                        'class' => 'viconfont vicon-pt_setting_history',
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => 'p_upgrade_history_list',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                            // 删除失败日志
                            [
                                'name' => "p_upgrade_history_delete",
                                'title' => "UI_SETTINGS_UPDATE_DELETE_ERROR_HISTORY",
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 检测更新
                    /*[
                        'name' => "upgrade_check",
                        'title' => "WEB_SETTINGS_UPDATE_CHECK_ONLINE",
                        'class' => "icon-speech",
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_upgrade_check_setting",
                                'title' => "UI_PUBLIC_OPERATION",
                                'level' => 10,
                            ],
                        ]
                    ],*/   // 检测更新 先隐藏
                ]
            ],
            // 消息推送
            [
                'name' => 'message_push',
                'title' => 'UI_PLATFORM_MESSAGE_PUSH',
                'class' => 'iconfont icon-xiaoxituisong',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_setting_manager_push',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 可视化配置
            [
                'name' => 'visual_config',
                'title' => 'UI_PUBLIC_VISUAL_CONFIG',
                'class' => 'iconfont icon-visual',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_setting_manager_visualization',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 系统工具
            [
                'name' => 'system_service',
                'title' => 'UI_SETTINGS_SYSTEM_TOOL',
                'class' => 'iconfont icon-xitonggongju',
                'level' => 2,
                'child' => [
                    // 服务管理
                    [
                        'name' => 'service_manage',
                        'title' => 'UI_SETTINGS_SERVICE_MANAGE',
                        'class' => 'viconfont vicon-pt_setting_service_management',
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => 'p_setting_manager_tools',
                                'class' => '',
                                'title' => 'UI_PUBLIC_LOOK',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 网络工具
                    [
                        'name' => 'network_tool',
                        'title' => 'UI_SETTINGS_NETWORK_TOOL',
                        'class' => 'viconfont vicon-pt_setting_service_network',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_network_tool_test',
                                'class' => '',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 远程控制
                    [
                        'name' => 'remote_control',
                        'title' => 'UI_PLATFORM_REMOTE_CONTROL',
                        'class' => 'iconfont icon-yuanchengkongzhi',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_remote_control_list',
                                'class' => '',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                ]
            ],
            // 系统备份/恢复
            [
                'name' => 'system_br',
                'title' => 'UI_PLATFORM_SYSTEM_BAK_REC',
                'class' => 'iconfont icon-sysbakrec',
                'level' => 2,
                'child' => [
                    // 手动备份
                    [
                        'name' => 'rc_oncebak',
                        'title' => 'UI_PLATFORM_RC_ONCEBAK',
                        'class' => 'iconfont icon-opbak',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_rc_oncebak',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 自动备份
                    [
                        'name' => 'rc_autobak',
                        'title' => 'UI_PLATFORM_RC_AUTOBAK',
                        'class' => 'iconfont icon-autobak',
                        'level' => 3,
                        'child' => [
                            // 备份设置
                            [
                                'name' => 'rc_autobak_setting',
                                'title' => 'UI_PLATFORM_RC_AUTOBAK_SETTING',
                                'class' => 'iconfont icon-baksetting',
                                'level' => 4,
                                'child' => [
                                    // 操作
                                    [
                                        'name' => 'p_rc_autobak_setting',
                                        'title' => 'UI_PUBLIC_OPERATION',
                                        'function' => [
                                            'get-url',
                                        ],
                                        'level' => 10,
                                    ],
                                ]
                            ],
                            // 备份点管理
                            [
                                'name' => 'rc_autobak_list',
                                'title' => 'UI_PLATFORM_RC_AUTOBAK_LIST',
                                'class' => 'iconfont icon-sysbakpoint',
                                'level' => 4,
                                'child' => [
                                    // 查看
                                    [
                                        'name' => 'p_rc_autobak_list_list',
                                        'title' => 'UI_PUBLIC_LOOK',
                                        'function' => [
                                            'get-url',
                                        ],
                                        'level' => 10,
                                    ],
                                    // 下载备份点
                                    [
                                        'name' => 'p_rc_autobak_list_download',
                                        'title' => 'UI_PLATFORM_RC_AUTOBAK_POINT_DOWNLAOD',
                                        'function' => [
                                            'get-url',
                                        ],
                                        'level' => 10,
                                    ],
                                    // 删除
                                    [
                                        'name' => "p_rc_autobak_list_delete",
                                        'title' => "UI_PUBLIC_DELETE",
                                        'function' => [
                                            'get-url',
                                        ],
                                        'level' => 10,
                                    ],
                                ]
                            ],
                        ]
                    ],
                    // 系统恢复
                    [
                        'name' => 'rc_recovery',
                        'title' => 'UI_PLATFORM_RC_RECOVERY',
                        'class' => 'iconfont icon-sysrecovery',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_rc_recovery',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-url',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                ]
            ],
            /*// 容灾演练平台
            [
                'name' => 'exercise_platform',
                'title' => 'UI_EXERCISE_PLATFORM',
                'class' => 'viconfont vicon-ge_disaster_recovery',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_exercise_platform_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],*/
            [
                'name' => 'carbon_monitor_platform',
                'title' => 'UI_SETTINGS_CARBON_MONITOR_PLATFORM',
                'class' => 'viconfont vicon-carbon-platform',
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'p_carbon_monitor_platform_list',
                        'title' => 'UI_PUBLIC_OPERATION',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 黑白名单
            [
                'name' => 'black_white_list',
                'title' => 'UI_PLATFORM_BLACKLIST_WHITELIST',
                'class' => "viconfont vicon-heibaimingdan",
                'level' => 2,
                'child' => [
                    // 操作
                    [
                        'name' => 'black_list',
                        'title' => 'UI_PLATFORM_BLACKLIST',
                        'class' => 'viconfont vicon-a-Wrong-usercuowuyonghu',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_black_list',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-system_wblist',
                                    'post-system_wblist',
                                    'put-system_wblist',
                                    'delete-system_wblist',
                                    'put-system_wblist_lock',
                                    'put-system_wblist_unlock',
                                    'get-system_wblist_compare',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    [
                        'name' => 'white_list',
                        'title' => 'UI_PLATFORM_WHITELIST',
                        'class' => 'viconfont vicon-a-Right-userzhengqueyonghu',
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => 'p_white_list',
                                'title' => 'UI_PUBLIC_OPERATION',
                                'function' => [
                                    'get-system_wblist',
                                    'post-system_wblist',
                                    'put-system_wblist',
                                    'delete-system_wblist',
                                    'put-system_wblist_lock',
                                    'put-system_wblist_unlock',
                                    'get-system_wblist_compare',
                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                ]
            ],
        ]
    ],
    // 用户管理
    [
        'name' => 'safety',
        'path' => './content/platform/users/safety_manager.php',
        'class' => 'viconfont vicon-safety',
        'title' => 'UI_PLATFORM_SAFETY',
        'level' => 1,
        'child' => [
            // 用户
            [
                'name' => 'safety_user',
                'path' => './content/platform/users/users.php',
                'class' => 'viconfont vicon-pt_setting_user',
                'title' => 'UI_PLATFORM_SAFETY_USER',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_safety_user_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-users',
                            'get-users_roles',
                            'get-users_auth',
                            'get-users_password',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => 'p_safety_user_add',
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => [
                            'post-users',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_safety_user_edit',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'put-users',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_safety_user_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'delete-users',
                        ],
                        'level' => 10,
                    ],
                    // 启用
                    [
                        'name' => 'p_safety_user_enable',
                        'title' => 'WEB_PLATFORM_ENABLE',
                        'function' => [
                            'post-users_unlock',
                        ],
                        'level' => 10,
                    ],
                    // 禁用
                    [
                        'name' => 'p_safety_user_disable',
                        'title' => 'WEB_PLATFORM_DISABLE',
                        'function' => [
                            'post-users_lock',
                        ],
                        'level' => 10,
                    ],
                    // 资源分配
                    [
                        'name' => 'p_safety_user_allocation_resource',
                        'title' => "UI_RESOURCE_GROUP_ALLOCATION_RESOURCE",
                        'function' => [
                            'post-users_allocation',
                        ],
                        'level' => 10,
                    ],
                    // 解绑资源
                    [
                        'name' => 'p_safety_user_unbind_allocation_resource',
                        'title' => "UI_RESOURCE_GROUP_UNBIND_ALLOCATION_RESOURCE",
                        'function' => [
                            'delete-users_allocation',
                        ],
                        'level' => 10,
                    ],
                    // 资源转移
                    [
                        'name' => 'p_safety_user_storage_transfer',
                        'title' => 'UI_STORAGE_TRANSFER',
                        'function' => [
                            'post-users_transfer',
                        ],
                        'level' => 10,
                    ],
                    // 分配管理用户
                    [
                        'name' => 'p_safety_user_manager_allocation',
                        'title' => 'UI_USER_MANAGER_ALLOCATION',
                        'function' => [
                            'get-users_manager',
                            'post-users_manager',
                        ],
                        'level' => 10,
                    ],
                    // 分配角色
                    [
                        'name' => 'p_safety_user_role_allot',
                        'title' => 'UI_PLATFORM_SAFETY_ROLE_ALLOT',
                        'function' => [
                            'post-users_roles',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 用户组
            [
                'name' => "safety_usergroup",
                'path' => "./content/platform/users/users_group.php",
                'class' => "iconfont icon-usergroup",
                'title' => "UI_PLATFORM_SAFETY_USER_GROUP",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_safety_usergroup_list",
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_safety_usergroup_add",
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => "p_safety_usergroup_edit",
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_safety_usergroup_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 启用
                    [
                        'name' => "p_safety_usergroup_enable",
                        'title' => 'WEB_PLATFORM_ENABLE',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 禁用
                    [
                        'name' => "p_safety_usergroup_disable",
                        'title' => 'WEB_PLATFORM_DISABLE',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 角色
            [
                'name' => 'safety_role',
                'path' => './content/platform/users/role.php',
                'class' => 'iconfont icon-jiaose',
                'title' => 'UI_PLATFORM_SAFETY_ROLE',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_safety_role_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => 'p_safety_role_add',
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_safety_role_edit',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_safety_role_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 启用
                    [
                        'name' => 'p_safety_role_enable',
                        'title' => 'WEB_PLATFORM_ENABLE',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 禁用
                    [
                        'name' => 'p_safety_role_disable',
                        'title' => 'WEB_PLATFORM_DISABLE',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 分配角色
                    [
                        'name' => 'p_safety_role_allot',
                        'title' => 'UI_PLATFORM_SAFETY_ROLE_ALLOT',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ]
                ]
            ],
            // 域服务器
            [
                'name' => 'safety_domain',
                'path' => './content/platform/users/domain_server.php',
                'class' => 'iconfont icon-domain',
                'title' => 'UI_PLATFORM_SAFETY_DOMAIN',
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => 'p_safety_domain_list',
                        'title' => 'UI_PUBLIC_LOOK',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => 'p_safety_domain_add',
                        'title' => 'UI_PUBLIC_ADD',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 修改
                    [
                        'name' => 'p_safety_domain_edit',
                        'title' => 'UI_PUBLIC_MODIFY',
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                    // 删除
                    [
                        'name' => "p_safety_domain_delete",
                        'title' => "UI_PUBLIC_DELETE",
                        'function' => [
                            'get-url',
                        ],
                        'level' => 10,
                    ],
                ]
            ],
        ]
    ],
    //租户管理
    array(
        'name' => 'tenant_manager',
        'path' => './content/platform/tenant/tenant_manager.php',
        'class' => 'viconfont vicon-tenant',
        'title' => 'UI_PLATFORM_MULTI_TENANT',
        'level' => 1,
        'child' => array(
            // 新建
            array(
                'name' => 'p_tenant_manager_add',
                'title' => 'UI_PUBLIC_ADD',
                'function' => array(
                ),
                'level' => 10,
            ),
            // 修改
            array(
                'name' => 'p_tenant_manager_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => array(
                ),
                'level' => 10,
            ),
            // 删除
            array(
                'name' => "p_tenant_manager_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => array(
                ),
                'level' => 10,
            ),
            // 启用
            array(
                'name' => 'p_tenant_manager_enable',
                'title' => 'WEB_PLATFORM_ENABLE',
                'function' => array(
                ),
                'level' => 10,
            ),
            // 禁用
            array(
                'name' => 'p_tenant_manager_disable',
                'title' => 'WEB_PLATFORM_DISABLE',
                'function' => array(
                ),
                'level' => 10,
            ),
        )
    ),
    // 系统授权
    [
        'name' => 'authorization_module',
        'path' => './content/platform/settings/authorization_module.php',
        'class' => 'viconfont vicon-authorization_module',
        'title' => 'UI_PLATFORM_MODULE_AUTH',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_authorization_module_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 下载指纹文件
            [
                'name' => 'p_authorization_module_download',
                'title' => 'UI_SETTINGS_DOWNLOAD_FILE',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 上传授权文件
            [
                'name' => 'p_authorization_module_upload',
                'title' => 'UI_SETTINGS_UPLOAD_FILE',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
        ]
    ],
];
