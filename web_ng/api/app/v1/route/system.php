<?php

return [

    ### 系统管理
    'system' => [ // 模块名
        # 网络配置
        'Network' => [ // 类名
            'system_network_info' => [ // 路由名
                // 请求方式   => 方法名,
                'get' => 'getNetworkCardInfo',   // 获取某块网卡的信息
                'post' => 'setNetworkCardInfo',   // 设置网卡信息
            ],
            'system_network_list' => [
                'get' => 'getNetworkCardList', // 获取所有网卡名字
            ],
            'system_network_host' => [
                'get' => 'getNodeDnsHosts', // 获取节点的hosts文件信息
                'post' => 'setNodeDnsHosts', // 配置节点hosts文件信息
            ],
            'system_network_nic' => [
                'get' => 'getNicOldInfo', // 获取网卡聚合配置信息
                'delete' => 'cleanNicInfo', // 清除网卡聚合信息
                'post' => 'addNicTeaming', // 添加网卡聚合
            ],
            'system_network_bridge' => [
                'get' => 'getBridgeInfo', // 获取网卡桥接信息
                'delete' => 'cleanBridge', // 清除网卡桥接信息
                'post' => 'addBridge', // 添加网卡桥接
            ],
            'system_network_isolate' => [
                'get' => 'getIsolate', // 获取隔离网段配置
                'post' => 'saveIsolate', // 保存隔离网段配置
            ],
        ],
        # 时间配置
        'Time' => [
            'system_times' => [
                'get' => 'getAllTimezone', // 获取所有时区配置文件
            ],
            'system_times_info' => [
                'get' => 'getDefaultTimeInfo', // 获取系统默认的时区和时间
                'post' => 'setTimeInfo', // 设置时间
            ],
            'system_times_ntp' => [
                'post' => 'syncNtpTime', // 立即同步ntp时间
            ],
            'system_get_time' => [
                'get' => 'getSystemTime', // 获取系统时间
            ],
        ],
        'SystemMonitor' => [
            'system_basic_info' => [
                'get' => 'getSystemBasicInfo', //获取系统基本信息
            ],
            'system_system_chart' => [
                'get' => 'getSystemChart', //获取系统基本信息
            ],
            'system_alarm_info' => [
                'get' => 'getAlarmVal',
                'put' => 'setAlarmVal',
            ],
        ],
        'Index' => [
            'system_thumbprint_file' => [
                'get' => 'getThumbprint', //下载指纹文件
            ],
            'system_upload_license' => [
                'post' => 'uploadLisence', //远程授权
            ],
            'system_license' => [
                'get' => 'getDecryptLisence', //获取license的extension解密后的数据
            ],
            'system_download' => [
                'get' => 'downloadFile', // 下载文件
            ],
            'system_generate_download' => [
                'get' => 'generateDownloadFilepath',  // 生成下载文件的url
            ],
            'system_vm_license' => [
                'get' => 'getVMLicenseInfo', //获取虚拟机授权的基本信息
            ],
            'system_config_base_info' => [
                'get' => 'getConfig', //获取系统的基本信息
            ],
            'system_config_recover_permission' => [
                'get' => 'getRecoverPermission' // 获取恢复权限配置
            ],
            'system_ip_reachable_batch_check' => [
                'post' => 'batchCheckIpReachable' // 批量检查IP是否可达
            ],
            'system_messages' => [
                'post' => 'sendMessages', // 发送消息(后台调用)
            ],
        ],
        # apikey
        'Apikey' => [ // 类名
            'system_apikey' => [ // 路由名
                // 请求方式   => 方法名,
                'post' => 'createApikey',   // 生成apikey
                'get' => 'getApikeyList',   // 获取apikey列表
                'delete' => 'deleteApikey',   // 删除apikey
            ],
            'system_apikey_lock' => [ // 路由名
                // 请求方式   => 方法名,
                'put' => 'lockApikey',   // 禁用apikey
            ],
            'system_apikey_unlock' => [ // 路由名
                // 请求方式   => 方法名,
                'put' => 'unlockApikey',   // 启用apikey
            ],

        ],
        # 黑白名单 whiteblacklist
        'WBList' => [  // 类名
            'system_wblist' => [  // 路由名
                // 请求方式   => 方法名,
                'post' => 'addWBList',  //添加黑/白名单
                'put' => 'editWBList',  //修改名单
                'delete' => 'deleteWBList',  //删除黑/白名单
                'get' => 'getWBList',  //获取名单列表
            ],
            'system_wblist_lock' => [
                'put' => 'lockWBList',  // 锁定名单
            ],
            'system_wblist_unlock' => [
                'put' => 'unlockWBList',    //启用名单
            ],
            'system_wblist_compare' => [
                'get' => 'compareList'  //添加IP时候确保没重复
            ]
        ],
        # 系统安全
        'Safe' => [
            'system_safe_data' => [
                'get' => 'getDatas', // 获取配置
                'post' => 'setDatas', // 保存配置
            ], // 数据安全
            'system_safe_account' => [
                'get' => 'getAccounts', // 获取配置
                'post' => 'setAccounts', // 保存配置
            ], // 账户安全
            'system_safe_storage' => [
                'get' => 'getStorages', // 获取配置
                'post' => 'setStorages', // 保存配置
            ], // 存储安全
            'system_safe_os' => [
                'get' => 'getOsSafe', // 获取配置
                'post' => 'setOsSafe', // 保存配置
            ], // 系统安全
            'system_power_up_down' => [
                'post' => 'powerSubmit', // 关机/重启
            ],
        ],
        # 系统通知
        'Notice' => [
            // 邮件
            'system_notice_email' => [
                'get' => 'getEmailConf', // 获取邮件通知配置
                'post' => 'setEmailConf', // 配置邮件通知
            ],
            'system_notice_test_email' => [
                'post' => 'sendEmailTest', // 测试发送邮件通知
            ],
            'system_notice_smtp' => [
                'post' => 'setEmailSmtp', // 保存邮件SMTP配置信息
            ],
            // 短信
            'system_notice_sms' => [
                'get' => 'getSmsConf', // 获取短信通知配置
                'post' => 'setSmsConf', // 配置短信通知
            ],
            'system_notice_test_sms' => [
                'post' => 'sendSmsTest', // 测试互联网短信发送
            ],
            'system_notice_cat_sms' => [
                'post' => 'sendCatSmsTest', // 测试短信猫发送
            ],
            // 微信
            'system_notice_wechat' => [
                'get' => 'getWechatConf', // 获取微信通知配置
                'post' => 'setWechatConf', // 配置微信通知
            ],
            'system_notice_wechat_qrcode' => [
                'post' => 'getWechatQrcode', // 获取微信通知的二维码
            ],
            'system_notice_test_wechat' => [
                'post' => 'sendWechatTest', // 发送测试微信通知
            ],
            'system_notice_wechat_conf' => [
                'post' => 'updateWechatConf', // 保存微信通知配置
            ],
            'system_notice_wechat_user' => [
                'get' => 'getWechatUser', // 获取微信通知用户列表
                'delete' => 'delWechatUser', // 删除微信授权用户
            ],
            // 企业微信
            'system_notice_wecom' => [
                'get' => 'getWecomConf', // 获取企业微信通知配置
                'post' => 'setWecomConf', // 配置企业微信通知
            ],
            'system_notice_test_wecom' => [
                'post' => 'sendWecomTest', // 发送企业微信测试通知
            ],
            'system_notice_wecom_conf' => [
                'post' => 'updateWecomConf', // 保存企业微信通知配置
            ],
            // 消息发送 - 公开
            'system_notice_send_email' => [
                'post' => 'sendEmail', // 发送邮件通知
            ],
            'system_notice_send_sms' => [
                'post' => 'sendSms', // 发送短信通知
            ],
            'system_notice_send_wechat' => [
                'post' => 'sendWechat', // 发送微信通知
            ],
            'system_notice_send_wecom' => [
                'post' => 'sendWecom', // 发送企业微信通知
            ],
        ],
        #系统升级
        'Upgrade' => [
            'system_upgrade_patches' => [
                'get' => 'getPatches', // 获取升级包列表
                'delete' => 'deletePatches', // 删除升级包
                'post'  => 'updatePatchList'    //更新升级包记录（上传成功后回调）
            ],
            'system_upgrade_history' => [
                'get' => 'getPatchHistory', // 获取升级历史列表
                'delete' => 'deletePatchHistory', // 删除失败的升级历史
            ],
            'system_upgrade_history_download' => [
                'get' => 'downloadPatchHistory', //下载升级历史日志
            ],
            'system_upgrade_upload' => [
                'get' => 'checkSystemSpaceEnough', // 上传升级包前检查空间是否够用
                'post' => 'uploadPatches', // 上传升级包
            ],
            'system_upgrade_patch' => [
                'get' => 'getSelectPatch', // 获取选中的升级包的信息
            ],
            'system_upgrade_nodes' => [
                'get' => 'getUpdateNodeList', // 得到升级可用的备份节点列表
            ],
            'system_upgrade_check_master' => [
                'get' => 'checkMasterUpdate', // 检查主节点是否已经升级
            ],
            'system_upgrade_check' => [
                'get' => 'checkIsUpgrading', // 检测是否有升级中的升级包
                'post' => 'upgradeCheck', // 升级前检查
            ],
            'system_upgrade' => [
                'post' => 'upgradeStart', // 立即升级
                'get' => 'getUpgradeInfo' //获取升级信息
            ],
            'system_upgrade_child' => [
                'post' => 'upgradeChildNode' //升级子节点?
            ],
            'system_upgrade_log' => [
                'get' => 'getUpgradeLog', // 获取升级日志
            ],
            'system_packet_status' => [
                'get' => 'getPacketStatus', // 获取升级包状态
            ]
        ],
        #系统工具
        'Tool' => [
            'system_services' => [
                'get' => 'getServiceList', // 获取系统服务列表
            ],
            'system_tools_uploads' => [
                'post' => 'uploadToSystem', // 上传文件到指定目录
                'delete' => 'delTempFile', // 删除临时文件
            ],
            'system_tools_service' => [
                'post' => 'operateService', // 系统服务管理
            ],
            'system_tools_connect' => [
                'post' => 'testConnectTool', // 测试网络连接
            ],
        ],
        # 消息推送
        'Message' => [
            'system_message' => [
                'get' => 'getMessagePush',  //获取推送列表
                'post' => 'addMessagePush', //添加推送
                'put' => 'editMessagePush',  //修改推送
                'delete' => 'deleteMessagePush',  //删除推送
            ],
            'system_message_unlock' => [
                'put' => 'unlcokMessagePush', //启用推送
            ],
            'system_message_lock' => [
                'put' => 'lcokMessagePush', //禁用推送
            ],
            'system_message_check' => [
                'get' => 'checkPushStrategyName',
            ],
            'system_message_monitor_platform' => [
                'get' => 'getMessageMonitorPlatform', //获取监控平台列表
                'post' => 'addMessageMonitorPlatform', //添加监控平台
                'put' => 'editMessageMonitorPlatform',  //修改监控平台
                'delete' => 'deleteMessageMonitorPlatform',  //删除监控平台
            ],
            'system_message_monitor_platform_unlock' => [
                'put' => 'unlcokMessageMonitorPlatform', //启用监控平台
            ],
            'system_message_monitor_platform_lock' => [
                'put' => 'lcokMessageMonitorPlatform', //禁用监控平台
            ],
            'system_message_push_alarm' => [
                'post' => 'pushMessageAlarm', //手动推送告警信息
            ],
            'system_message_push_response' => [
                'post' => 'pushMessageResponse',  //手动推送响应信息
            ],
            'system_message_monitor_platform_check' => [
                'get' => 'checkMonitorPlatformName',
            ],
            'system_message_associated_task' => [
                'get' => 'getAssociatedTask',
            ],
            'system_message_name' => [
                'get' => 'getMessageDefaultName',
            ],
            'system_message_monitor_name' => [
                'get' => 'getMonitorDefaultName',
            ],
            'system_message_same_ip' => [
                'get' => 'isSameIP'
            ],
            'system_message_same_url' => [
                'get' => 'isSameUrl'
            ]
        ],
        # 系统授权
        'Auth' => [
            'system_auth_info' => [
                'get' => 'getSystemLisenceInfo', //获取系统授权的基本信息
            ],
            'system_auth_get_thumbprint' => [
                'get' => 'getThumbprint', //获取系统指纹信息
            ],
            'system_auth_get_thumbprint_file' => [
                'get' => 'getThumbprintFile', //获取系统指纹文件
            ],
            'system_auth_upload_license' => [
                'post' => 'uploadLicense', //上传授权文件
            ],
            'system_auth_basic_info' => [
                'get' => 'getSystemAuthInfo', //获取系统授权的基本信息(new)
            ],
            'system_auth_base_info' => [
                'get' => 'getLicenceInfo', //获取授权的一些基本信息
            ]
        ],
        'Files' => [ // 文件上传
            'system_upload' => [ // 路由名
                // 请求方式   => 方法名,
                'post' => 'upload',   // 上传文件
            ],
        ],
        'Settings' => [
            'system_setting' => [
                'get' => 'getSysSetting', // 获取上传系统配置
                'post' => 'uploadSysSetting', // 上传系统配置
            ],
            'system_visual_setting' => [
                'get' => 'getDefaultVisualInfo',
                'put' => 'setVisualInfo'
            ]
        ],
        'SystemBackup' => [
            'system_backup_tree' => [ // 获取备份树
                'get' => 'getSystemBackupTree',
            ],
            'system_backup_manual' => [ // 手动备份
                'post' => 'systemBackupMaual',
            ],
            'system_backup_auto' => [ // 自动备份配置
                'post' => 'systemBackupAutoConfig',
            ],
            'system_backup_list' => [
                'get' => 'getSystemBackupList', // 获取备份列表
                'delete' => 'deleteSystemBackupAutoPoint', // 删除自动备份点
            ],
            'system_backup_upload_file' => [ // 上传恢复源文件
                'post' => 'uploadRecoverySrc',
            ],
            'system_backup_auto_config' => [ // 获取自动备份配置
                'get' => 'getAutoBakConfig',
            ],
            'system_backup_recovery_tree' => [ // 获取上传文件恢复源树
                'get' => 'getUploadSrcTree',
            ],
            'system_backup_recovery_check' => [ // 检测恢复源文件
                'post' => 'checkRecData',
            ],
            'system_backup_recovery' => [ // 恢复
                'post' => 'doSystemRecovery',
            ],
            'system_backup_recovery_progress' => [ // 恢复进度
                'post' => 'getRecoveryProgress',
            ],
            'system_backup_recovery_auto_tree' => [ // 自动恢复源
                'get' => 'getAutoSourceTree',
            ],
            'system_backup_clean_dir' => [ // 暂存文件路径
                'get' => 'cleanSystemTempDir',
            ],
            'system_backup_list_download' => [ // 下载文件
                'get' => 'downloadBackupList',
            ],
        ],
    ],

];
