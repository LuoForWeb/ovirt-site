<?php

return [
    'db' => [
        'DbBackUp' => [
            'db_type' => [
                // 获取数据库类别列表
                'get' => 'getDbType',
            ],
            'db_databases' => [
                'get' => 'loadInstanceDatabase',  // 加载数据库应用（实例）的数据库信息
            ],
            'db_data_file' => [
                'get' => 'loadDbDataFile',  // 加载数据库数据文件
            ],
            'db_jobs_backup' => [
                'post' => 'createDbBackupJob',  // 创建数据库备份任务
                'put' => 'editDbBackupJob',  // 修改数据库备份任务
                'get' => 'getDbBackupJob',  // 获取数据库备份任务
            ],
            'db_instances' => [
                'get' => 'getAllInstance',  // 获取所有的数据库实例
            ],
        ],
        'DbRecover' => [
            'db_jobs_recovery' => [
                'post' => 'createDbRecoveryJob',  // 创建数据库恢复任务
                'get' => 'getDbRecoveryJob',  // 创建数据库恢复任务
                'put' => 'editDbRecoveryJob',  // 修改数据库恢复任务
            ],
            'db_oracle_timepoint_chain' => [
                'get' => 'getOracleTimepointChainRecoveryTime',  // 根据恢复时间获取Oracle恢复所使用的备份链
            ],
            'db_sqlserver_active_agent' => [
                'get' => 'getSqlserverClusterActiveNodeAgentInfo',  // 获取SQL Server集群中活动节点的客户端信息
            ],
        ],
        'DbData' => [
            'db_jobs_backup_data' => [
                'get' => 'getDbBackupData',  // 获取数据库备份数据
            ],
            'db_jobs_backup_time_point' => [
                'get' => 'getDbBackupTimePoint',  // 获取数据库实例或单个数据库的备份点信息
                'delete' => 'deleteDbBackupTimePoint',  // 删除数据库备份时间点
            ],
            'db_jobs_backup_time_point_list' => [
                'get' => 'getDbBackupTimePointList',  // 批量获取数据库实例的备份点信息
            ],
            'db_jobs_backup_password' => [
                'post' => 'verifyDbBackupPassword',  // 校验数据加密密码是否正确
            ],
            'db_config_file_content' => [
                'get' => 'getConfigFileContent',  // 获取配置文件内容
            ],
            'db_config_file_parse' => [
                'get' => 'parseConfigFileContent',  // 解析并转换spfile文件
            ],
            'db_jobs_backup_storages' => [
                'get' => 'getDbBackupStorageList',  // 获取数据库备份存储列表
            ],
        ],
        'DbJobInfo' => [
            'db_jobs_info' => [
                'get' => 'getDbJobInfo',  // 获取数据库备份/恢复/演练任务的运行信息
            ],
            'db_jobs_object_info' => [
                'get' => 'getDbJobObjectInfo',  // 获取数据库备份/恢复/演练任务的对象运行信息
            ],
            'db_jobs_validate_report' => [
                'post' => 'generateDbValidateReport',  // 生成数据库验证报告
                'get' => 'getDbValidateReport',  // 获取数据库验证报告
            ],
            'db_jobs_validate_report_email' => [
                'post' => 'sendDbValidateReport',  // 发送数据库验证报告
            ],
            'db_jobs_association' => [
                'get' => 'getDbJobAssociation',  // 获取数据库备份任务的关联任务
            ],
        ],
        'DbJobController' => [
            'db_jobs_backup_start' => [
                'post' => 'startBackupJob',  // 启动数据库备份任务
            ],
        ],
    ],
];
