<?php

/**
 * 任务相关的路由定义
 */

return [

    //集中管理平台相关
    'backupmanager' => [//目录
        'backupCenter' => [//文件类名
            'backupmanager' => [
                //获取备份中心基本数据(集中管理平台添加备份中心使用)
                'get' => 'getBasicInfo',
            ],
            'backupmanager_syncdata' => [ //route文件名+url名字
                //获取备份中心所有数据(集中管理平台同步备份中心使用)
                'get' => 'getAllBasicInfo',
            ],
        ],

        'ReportInfo' => [ //文件类名
            'backupmanager_report_task' => [ //获取任务报表明细数据
                'get' => 'getTaskReportData'
            ],
            'backupmanager_report_storage' => [ //获取存储报表明细数据
                'get' => 'getStorageReportData'
            ],
            'backupmanager_report_nas' => [ //获取NAS报表明细数据
                'get' => 'getNasReportData'
            ],
            'backupmanager_report_vm' => [ //获取虚拟机报表明细数据
                'get' => 'getVmReportData'
            ],
            'backupmanager_report_file' => [ //获取文件报表明细数据
                'get' => 'getFileReportData'
            ],
            'backupmanager_report_db' => [ //获取数据库报表明细数据
                'get' => 'getDbReportData'
            ],
            'backupmanager_report_client' => [ //获取客户端系统报表明细数据
                'get' => 'getClientReportData'
            ],
            'backupmanager_report_os' => [ //获取操作系统报表明细数据
                'get' => 'getOsReportData'
            ],
            'backupmanager_report_vol_cdp' => [ //获取卷CDP模块报表明细数据
                'get' => 'getVolCdpReportData'
            ],
        ],
        'ReportOverView' => [
            'backupmanager_report_overview_data' => [ //获取概览数据
                'get' => 'getReportOverviewData'
            ]
        ],
        'ReportRunningData' => [
            'backupmanager_report_running_data' => [ //获取模块运行数据
                'get' => 'getReportRunningData'
            ]
        ],
    ],







];
