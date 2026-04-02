<?php

/**
 * 任务相关的路由定义
 */

return [

    //集中管理平台相关
    'homepage' => [//目录
        'homePage' => [//文件类名
            'homepage_info' => [
                //获取首页基本信息
                'post' => 'getHomePageInfo',
            ],
            'homepage_grid' =>[
                //获取首页布局信息
                'get' => 'getHomePageGrid',
            ],
            'homepage_theme' =>[
                //获取首页主题信息
                'get' => 'getThemeInfo',
            ],
            'homepage_save_card' =>[
                //获取首页布局信息
                'post' => 'saveCardInfo',
            ],
            'homepage_nodes' =>[
                //获取首页布局信息
                'get' => 'getNodeList',
            ],
            'homepage_recover_grid' =>[
                //恢复默认
                'post' => 'reoverUserGrid',
            ],
             'homepage_log' =>[
                //获取标准版首页日志信息
                'get' => 'getLogInfo',
            ],
            'homepage_alarm' =>[
                //获取标准版首页告警信息
                'get' => 'getAlarmInfo',
            ],
            'homepage_pretected_data' =>[
                //获取标准版受保护数据
                'get' => 'getProtectData',
            ],
            'homepage_pretected_data_trend' => [
                //获取标准版受保护数据详情
                'get' => 'getProtectDataTrend',
            ],
            'homepage_storage_data_trend' => [
                //获取标准版存储使用趋势
                'get' => 'getStorageDataTrend',
            ]
        ],
        //新首页接口
        'homePageInfo' => [
            'homepage_datacenter_view' => [
                //获取数据概览
                'get' => 'getDataCenterView',
            ],
            'homepage_job_status_view' => [
                //获取任务各个状态个数概览
                'get' => 'getTaskStatusView',
            ],
            'homepage_protected_device' => [
                //获取受保护设备 
                'get' => 'getProtectedDevice',
            ],
            'homepage_backup_data' => [
                //备份数据增长趋势
                'get' => 'getBackupData',
            ],
            'homepage_cdp_data' => [
                //连续保护数据增长趋势
                'get' => 'getCdpData',
            ],
            'homepage_copy_data' => [
                //复制数据增长趋势
                'get' => 'getCopyData',
            ],
            'homepage_auth_status' => [
                //获取系统授权状态
                'get' => 'getSystemAuthStatus',
            ],
            'homepage_storage_info' => [
                //获取存储信息
                'get' => 'getSystemStorageData',
            ],
        ],
    ],







];
