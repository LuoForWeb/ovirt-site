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
        'name' => "setting_manager",
        'path' => "settingManager.html",
        'class' => "viconfont vicon-setting_manager",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_SET",
        'level' => 1,
        'child' => [
            // 网络配置
            [
                'name' => "system_network",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_NETWORK_SETTING",
                'class' => "iconfont icon-ipaddress",
                'path' => "javascript:;",
                'level' => 2,
                'child' => [
                    // IP地址
                    [
                        'name' => "set_ip",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_IP_ADDRESS",
                        'class' => "iconfont icon-ipaddr",
                        'path' => "network.html#ip",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_set_ip_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    'get-system_network_info',
                                    'get-system_network_list',
                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_setting_manager_ip",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                    'post-system_network_info',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 域名解析
                    [
                        'name' => "system_dns",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_DNS",
                        'class' => "viconfont vicon-pt_setting_dns",
                        'path' => "network.html#dns",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_system_dns_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    'get-system_network_host',
                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_system_dns_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                    'post-system_network_host',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 网卡聚合
                    [
                        'name' => "nic_teaming",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_NIC_TEAMING",
                        'class' => "iconfont icon-wangkajihe",
                        'path' => "network.html#teaming",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_nic_teaming_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    'get-system_network_nic',
                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_nic_teaming_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                    'post-system_network_nic',
                                    'delete-system_network_nic',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 网卡桥接
                    [
                        'name' => "card_bridge",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_CARD_BRIDEG",
                        'class' => "iconfont icon-card_bridge",
                        'path' => "network.html#bridge",
                        'level' => 3,
                        'child' => [
                            // 查看
                            [
                                'name' => "p_card_bridge_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    'get-system_network_bridge'
                                ],
                                'level' => 10,
                            ],
                            // 添加
                            [
                                'name' => "p_card_bridge_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADDNEW",
                                'function' => [
                                    'post-system_network_bridge'
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_card_bridge_delte",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                    'delete-system_network_bridge'
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 隔离网段
                            [
                                'name' => "p_card_bridge_isolate_network",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_CARD_BRIDEG_ISOLATE",
                                'function' => [
                                    'get-system_network_isolate',
                                    'post-system_network_isolate'
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ]
                ]
            ],
            // 时间配置
            [
                'name' => "set_time",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SET_TIME",
                'class' => "iconfont icon-shezhishijian",
                'level' => 2,
                'path' => "times.html",
                'child' => [
                    // 查看
                    [
                        'name' => "p_setting_manager_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-system_times',
                            'get-system_times_info',
                        ],
                        'level' => 10,
                    ],
                    // 操作
                    [
                        'name' => "p_setting_manager_time",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                            'post-system_times_info',
                            'post-system_times_ntp',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 系统通知
            [
                'name' => "system_notice",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_NOTICE",
                'class' => "iconfont icon-xitongtongzhi",
                'path' => "javascript:;",
                'level' => 2,
                'child' => [
                    // 邮件通知
                    [
                        'name' => "emial_notice",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_NOTICE_EMAIL",
                        'class' => "icon-envelope-open",
                        'level' => 3,
                        'path' => "notice.html#email",
                        'child' => [
                            // 查看
                            [
                                'name' => "emial_notice_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_emial_notice",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 短信通知
                    [
                        'name' => "sms_notice",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_NOTICE_SMS",
                        'class' => "viconfont vicon-pt_setting_short_note",
                        'level' => 3,
                        'path' => "notice.html#sms",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_sms_notice_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_sms_notice",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 微信通知
                    [
                        'name' => "wechat_notice",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_NOTICE_WECHAT",
                        'class' => "viconfont vicon-pt_setting_wechat_notice",
                        'level' => 3,
                        'path' => "notice.html#wechat",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_wechat_notice_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_wechat_notice",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 企业微信通知
                    [
                        'name' => "enterprise_wechat_notice",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_NOTICE_WECHAT_INTERNET2",
                        'class' => "viconfont vicon-pt_setting_wechat2_notice",
                        'level' => 3,
                        'path' => "notice.html#enterprise",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_enterprise_wechat_notice_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_enterprise_wechat_notice",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                ]
            ],
            // 安全配置
            [
                'name' => "system_safe",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SAFE_SETTING",
                'class' => "iconfont icon-anquanpeizhi",
                'level' => 2,
                'path' => "javascript:;",
                'child' => [
                    // 账户安全
                    [
                        'name' => "account_safe",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ACCOUNT_SAFE",
                        'class' => "iconfont icon-accountsafe",
                        'level' => 3,
                        'path' => "safe.html#account",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_account_safe_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_account_safe",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 存储安全
                    [
                        'name' => "storage_safe",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_STORAGE_SAFE",
                        'class' => "iconfont icon-storagesafe",
                        'level' => 3,
                        'path' => "safe.html#storage",
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_storage_safe",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 系统安全
                    [
                        'name' => "os_safe",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_OS_SAFE",
                        'class' => "iconfont icon-ossafe",
                        'level' => 3,
                        'path' => "safe.html#ossafe",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_os_safe_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_os_safe",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 数据安全
                    [
                        'name' => 'data_safe',
                        'title' => 'WEB_PAGE_COMMON_MENU_PLATFORM_DATA_SAFE',
                        'class' => 'iconfont icon-ossafe',
                        'level' => 3,
                        'path' => "safe.html#data",
                        'child' => [
                            // 查看
                            [
                                'name' => 'p_data_safe_look',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_LOOK',
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => 'p_data_safe_operate',
                                'title' => 'WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION',
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                ]
            ],
            // 关机/重启
            [
                'name' => "system_poweroff",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_POWER",
                'class' => "iconfont icon-offrestart",
                'level' => 2,
                'path' => "powerOff.html",
                'is_operate' => 1,
                'child' => [
                    // 操作
                    [
                        'name' => "p_setting_manager_power",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 系统升级
            [
                'name' => "system_upgrade",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_UPDATE",
                'class' => "iconfont icon-upgrade",
                'level' => 2,
                'path' => "javascript:;",
                'child' => [
                    // 升级包管理
                    [
                        'name' => "upgrade_manage",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_UPDATE_MANAGE",
                        'class' => "viconfont vicon-pt_setting_package",
                        'level' => 3,
                        'path' => "upgrade.html#manage",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_upgrade_manage_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            // 上传升级包
                            [
                                'name' => "p_setting_manager_upload",
                                'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_UPLOAD_UPGRADE_PATCH",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除升级包
                            [
                                'name' => "p_setting_manager_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_DELETE_UPGRADE_PATCH",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 系统升级
                            [
                                'name' => "p_setting_manager_upgrade",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_UPDATE",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 升级历史
                    [
                        'name' => "upgrade_history",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_UPDATE_HISTORY",
                        'class' => "viconfont vicon-pt_setting_history",
                        'level' => 3,
                        'path' => "upgrade.html#history",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_upgrade_history_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 删除失败日志
                            [
                                'name' => "p_upgrade_history_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_UPDATE_DELETE_ERROR_HISTORY",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 检测更新
                    /*[
                        'name' => "upgrade_check",
                        'title' => "WEB_SETTINGS_UPDATE_CHECK_ONLINE",
                        'class' => "icon-speech",
                        'path' => "upgrade.html#check",
                        'level' => 3,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_upgrade_check_setting",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'level' => 10,
                            ],
                        ]
                    ],*/                            // 检测更新 先隐藏
                ]
            ],
            // 消息推送
            [
                'name' => "message_push",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MESSAGE_PUSH",
                'class' => "iconfont icon-xiaoxituisong",
                'level' => 2,
                'path' => "javascript:;",
                'child' => [
                    // 第三方推送
                    [
                        'name' => "message_push_third",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_THIRD_MESSAGE_PUSH",
                        'class' => "viconfont vicon-disanfangxiaoxituisong",
                        'level' => 3,
                        'path' => "message.html#third",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_message_push_third_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            // 新建
                            [
                                'name' => "p_message_push_third_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_message_push_third_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_message_push_third_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 启用
                            [
                                'name' => "p_message_push_third_enable",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ENABLE",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 禁用
                            [
                                'name' => "p_message_push_third_disable",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DISABLE",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 监控平台配置
                            [
                                'name' => "p_message_push_third_config",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_THIRD_MONITOR_CONFIG",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 消息推送
                    [
                        'name' => "message_push_old",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MESSAGE_PUSH",
                        'class' => "viconfont vicon-xiaoxituisong",
                        'level' => 3,
                        'path' => "message.html#push",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_message_push_old_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                            [
                                'name' => "p_message_push_old_operate",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                ]
            ],
            // 可视化配置
            [
                'name' => "visual_config",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_VISUAL_CONFIG",
                'class' => "iconfont icon-visual",
                'level' => 2,
                'path' => "visual.html",
                'child' => [
                    // 查看
                    [
                        'name' => "p_setting_manager_visualization_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [

                        ],
                        'level' => 10,
                    ],
                    // 操作
                    [
                        'name' => "p_setting_manager_visualization",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 系统工具
            [
                'name' => "system_service",
                'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_SYSTEM_TOOL",
                'class' => "iconfont icon-xitonggongju",
                'path' => "javascript:;",
                'level' => 2,
                'child' => [
                    // 服务管理
                    [
                        'name' => "service_manage",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_SERVICE_MANAGE",
                        'class' => "viconfont vicon-pt_setting_service_management",
                        'level' => 3,
                        'path' => "systemTool.html#service",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_setting_manager_tools",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                ],
                                'level' => 10,
                            ],
                            // 操作
                            [
                                'name' => "p_setting_manager_operate",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 网络工具
                    [
                        'name' => "network_tool",
                        'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_NETWORK_TOOL",
                        'class' => "viconfont vicon-pt_setting_service_network",
                        'level' => 3,
                        'path' => "systemTool.html#network",
                        'child' => [
                            // 测试
                            [
                                'name' => "p_network_tool_test",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_NETWORK_TOOL_TEST",
                                'function' => [

                                ],
                                'level' => 10,
                            ],
                        ]
                    ],
                    // 远程控制
                    [
                        'name' => "remote_control",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_REMOTE_CONTROL",
                        'class' => "iconfont icon-yuanchengkongzhi",
                        'level' => 3,
                        'path' => "systemTool.html#control",
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_remote_control_list",
                                'class' => "",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                ]
            ],
            // 系统备份/恢复
            [
                'name' => "system_br",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SYSTEM_BAK_REC",
                'class' => "iconfont icon-sysbakrec",
                'level' => 2,
                'path' => "javascript:;",
                'child' => [
                    // 手动备份
                    [
                        'name' => "rc_oncebak",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RC_ONCEBAK",
                        'class' => "iconfont icon-opbak",
                        'level' => 3,
                        'path' => "onceBak.html",
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_rc_oncebak",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [

                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 自动备份
                    [
                        'name' => "rc_autobak",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RC_AUTOBAK",
                        'class' => "iconfont icon-autobak",
                        'path' => "autoBak.html",
                        'level' => 3,
                        'child' => [
                            // 备份设置
                            [
                                'name' => "rc_autobak_setting",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RC_AUTOBAK_SETTING",
                                'class' => "iconfont icon-baksetting",
                                'level' => 4,
                                'path' => "onceBak.html#setting",
                                'is_operate' => 1,
                                'child' => [
                                    // 操作
                                    [
                                        'name' => "p_rc_autobak_setting",
                                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                        'function' => [

                                        ],
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ],
                                ]
                            ],
                            // 备份点管理
                            [
                                'name' => "rc_autobak_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RC_AUTOBAK_LIST",
                                'class' => "iconfont icon-sysbakpoint",
                                'level' => 4,
                                'path' => "onceBak.html#list",
                                'child' => [
                                    // 查看
                                    [
                                        'name' => "p_rc_autobak_list_list",
                                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                        'function' => [

                                        ],
                                        'level' => 10,
                                    ],
                                    // 下载备份点
                                    [
                                        'name' => "p_rc_autobak_list_download",
                                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RC_AUTOBAK_POINT_DOWNLAOD",
                                        'function' => [

                                        ],
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ],
                                    // 删除
                                    [
                                        'name' => "p_rc_autobak_list_delete",
                                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                        'function' => [

                                        ],
                                        'level' => 10,
                                        'is_operate' => 1,
                                    ],
                                ]
                            ],
                        ]
                    ],
                    // 系统恢复
                    [
                        'name' => "rc_recovery",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_RC_RECOVERY",
                        'class' => "iconfont icon-sysrecovery",
                        'level' => 3,
                        'path' => "rcRecovery.html",
                        'is_operate' => 1,
                        'child' => [
                            // 操作
                            [
                                'name' => "p_rc_recovery",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                                'function' => [
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                ]
            ],
            // 容灾演练平台
            [
                'name' => "exercise_platform",
                'title' => "WEB_PAGE_COMMON_MENU_EXERCISE_PLATFORM",
                'class' => "viconfont vicon-ge_disaster_recovery",
                'level' => 2,
                'path' => "exercisePlatform.html",
                'child' => [
                    // 操作
                    [
                        'name' => "p_exercise_platform_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                ]
            ],
            // 黑白名单
            [
                'name' => "black_white_list",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_BLACKLIST_WHITELIST",
                'class' => "viconfont vicon-heibaimingdan",
                'level' => 2,
                'path' => "blackWhite.html",
                'child' => [
                    // 黑名单
                    [
                        'name' => "black_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_BLACKLIST",
                        'class' => "viconfont vicon-a-Wrong-usercuowuyonghu",
                        'level' => 3,
                        'path' => "blackWhite.html#black",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_black_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    'get-system_wblist',
                                    'get-system_wblist_compare',
                                ],
                                'level' => 10,
                            ],
                            // 新建
                            [
                                'name' => "p_black_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                                'function' => [
                                    'post-system_wblist',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_black_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [
                                    'put-system_wblist',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_black_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                    'delete-system_wblist',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 启用
                            [
                                'name' => "p_black_enable",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ENABLE",
                                'function' => [
                                    'put-system_wblist_lock',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 禁用
                            [
                                'name' => "p_black_disable",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DISABLE",
                                'function' => [
                                    'put-system_wblist_unlock',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                    // 白名单
                    [
                        'name' => "white_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_WHITELIST",
                        'class' => "viconfont vicon-a-Right-userzhengqueyonghu",
                        'level' => 3,
                        'path' => "blackWhite.html#white",
                        'child' => [
                            // 查看
                            [
                                'name' => "p_white_list",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                                'function' => [
                                    'get-system_wblist',
                                    'get-system_wblist_compare',
                                ],
                                'level' => 10,
                            ],
                            // 新建
                            [
                                'name' => "p_white_add",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                                'function' => [
                                    'post-system_wblist',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 修改
                            [
                                'name' => "p_white_edit",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                                'function' => [
                                    'put-system_wblist',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 删除
                            [
                                'name' => "p_white_delete",
                                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                                'function' => [
                                    'delete-system_wblist',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 启用
                            [
                                'name' => "p_white_enable",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ENABLE",
                                'function' => [
                                    'put-system_wblist_lock',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                            // 禁用
                            [
                                'name' => "p_white_disable",
                                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DISABLE",
                                'function' => [
                                    'put-system_wblist_unlock',
                                ],
                                'level' => 10,
                                'is_operate' => 1,
                            ],
                        ]
                    ],
                ]
            ],
            //apikey
            [
                'name' => "api_key",
                'title' => "WEB_PAGE_COMMON_MENU_API_KEY",
                'class' => "viconfont vicon-apikey",
                'path' => "apiKey.html",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_api_key_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [

                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_api_key_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_api_key_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 启用
                    [
                        'name' => "p_api_key_enable",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ENABLE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 禁用
                    [
                        'name' => "p_api_key_disable",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DISABLE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            //能耗监控平台
            [
                'name' => "carbon_monitor_platform",
                'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_CARBON_MONITOR_PLATFORM",
                'class' => "viconfont vicon-carbon-platform",
                'level' => 2,
                'path' => "carbonMonitor.html",
                'child' => [
                    // 查看
                    [
                        'name' => "p_carbon_monitor_platform_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 操作
                    [
                        'name' => "p_carbon_monitor_platform_operate",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_OPERATION",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
        ]
    ],
    // 用户管理
    [
        'name' => "safety",
        'path' => "safetyManager.html",
        'class' => "viconfont vicon-safety",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SAFETY",
        'level' => 1,
        'child' => [
            // 用户
            [
                'name' => "safety_user",
                'path' => "users.html",
                'class' => "viconfont vicon-pt_setting_user",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SAFETY_USER",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_safety_user_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                            'get-users',
                            'get-users_roles',
                            'get-users_auth',
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_safety_user_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                            'post-users',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_safety_user_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                            'put-users',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_safety_user_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [
                            'delete-users',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 启用
                    [
                        'name' => "p_safety_user_enable",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ENABLE",
                        'function' => [
                            'post-users_unlock',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 禁用
                    [
                        'name' => "p_safety_user_disable",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DISABLE",
                        'function' => [
                            'post-users_lock',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 资源分配
                    [
                        'name' => "p_safety_user_allocation_resource",
                        'title' => "WEB_PAGE_COMMON_MENU_RESOURCE_GROUP_ALLOCATION_RESOURCE",
                        'function' => [
                            'post-users_allocation',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 解绑资源
                    [
                        'name' => "p_safety_user_unbind_allocation_resource",
                        'title' => "WEB_PAGE_COMMON_MENU_RESOURCE_GROUP_UNBIND_ALLOCATION_RESOURCE",
                        'function' => [
                            'delete-users_allocation',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 资源转移
                    [
                        'name' => "p_safety_user_storage_transfer",
                        'title' => "WEB_PAGE_COMMON_MENU_STORAGE_TRANSFER",
                        'function' => [
                            'post-users_transfer',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 分配管理用户
                    [
                        'name' => "p_safety_user_manager_allocation",
                        'title' => "WEB_PAGE_COMMON_MENU_USER_MANAGER_ALLOCATION",
                        'function' => [
                            'get-users_manager',
                            'post-users_manager',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 分配角色
                    [
                        'name' => "p_safety_user_role_allot",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SAFETY_ROLE_ALLOT",
                        'function' => [
                            'post-users_roles',
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 用户组
            [
                'name' => "safety_usergroup",
                'path' => "usersGroup.html",
                'class' => "iconfont icon-usergroup",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SAFETY_USER_GROUP",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_safety_usergroup_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_safety_usergroup_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_safety_usergroup_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_safety_usergroup_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 启用
                    [
                        'name' => "p_safety_usergroup_enable",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ENABLE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 禁用
                    [
                        'name' => "p_safety_usergroup_disable",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DISABLE",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
            // 角色
            [
                'name' => "safety_role",
                'path' => "role.html",
                'class' => "iconfont icon-jiaose",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SAFETY_ROLE",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_safety_role_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [
                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_safety_role_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_safety_role_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [
                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_safety_role_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 启用
                    [
                        'name' => "p_safety_role_enable",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ENABLE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 禁用
                    [
                        'name' => "p_safety_role_disable",
                        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DISABLE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 分配角色
                    [
                        'name' => "p_safety_role_allot",
                        'title' => "WEB_PAGE_COMMON_MENU_ROLE_ALLOC_USER",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ]
                ]
            ],
            // 域服务器
            [
                'name' => "safety_domain",
                'path' => "domainServer.html",
                'class' => "iconfont icon-domain",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_SAFETY_DOMAIN",
                'level' => 2,
                'child' => [
                    // 查看
                    [
                        'name' => "p_safety_domain_list",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                        'function' => [

                        ],
                        'level' => 10,
                    ],
                    // 新建
                    [
                        'name' => "p_safety_domain_add",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 修改
                    [
                        'name' => "p_safety_domain_edit",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                    // 删除
                    [
                        'name' => "p_safety_domain_delete",
                        'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                        'function' => [

                        ],
                        'level' => 10,
                        'is_operate' => 1,
                    ],
                ]
            ],
        ]
    ],
    //租户管理
    [
        'name' => "tenant_manager",
        'path' => "tenantManager.html",
        'class' => "viconfont vicon-tenant",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MULTI_TENANT",
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => "p_tenant_manager_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                'function' => [

                ],
                'level' => 10,
            ],
            // 新建
            [
                'name' => "p_tenant_manager_add",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_ADD",
                'function' => [

                ],
                'level' => 10,
                'is_operate' => 1,
            ],
            // 修改
            [
                'name' => "p_tenant_manager_edit",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_MODIFY",
                'function' => [

                ],
                'level' => 10,
                'is_operate' => 1,
            ],
            // 删除
            [
                'name' => "p_tenant_manager_delete",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_DELETE",
                'function' => [

                ],
                'level' => 10,
                'is_operate' => 1,
            ],
            // 启用
            [
                'name' => "p_tenant_manager_enable",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_ENABLE",
                'function' => [

                ],
                'level' => 10,
                'is_operate' => 1,
            ],
            // 禁用
            [
                'name' => "p_tenant_manager_disable",
                'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_DISABLE",
                'function' => [

                ],
                'level' => 10,
                'is_operate' => 1,
            ],
        ]
    ],
    // 系统授权
    [
        'name' => "authorization_module",
        'path' => "authorization.html",
        'class' => "viconfont vicon-authorization_module",
        'title' => "WEB_PAGE_COMMON_MENU_PLATFORM_MODULE_AUTH",
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => "p_authorization_module_list",
                'title' => "WEB_PAGE_COMMON_MENU_PUBLIC_LOOK",
                'function' => [

                ],
                'level' => 10,
            ],
            // 下载指纹文件
            [
                'name' => "p_authorization_module_download",
                'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_DOWNLOAD_FILE",
                'function' => [

                ],
                'level' => 10,
                'is_operate' => 1,
            ],
            // 上传授权文件
            [
                'name' => "p_authorization_module_upload",
                'title' => "WEB_PAGE_COMMON_MENU_SETTINGS_UPLOAD_FILE",
                'function' => [

                ],
                'level' => 10,
                'is_operate' => 1,
            ],
        ]
    ],
];
