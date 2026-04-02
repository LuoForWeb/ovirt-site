<?php

/**
 * 磁带相关路由定义
 */

return [
    'tape' => [ //模块名
        'TapeController' => [ //类名
            'tapes_scan' => [
                'post'  => 'scanTapeLib', //扫描磁带库
            ],
            'tapes_group' => [
                'post'  => 'addTapeGroup', //添加磁带组
                'put'  => 'modifyTapeGroup', //修改磁带组
                'delete'  => 'delTapeGroup', //删除磁带组
            ],
            'tapes_group_import' => [
                'post'  => 'importTapeGroup', //导入磁带组信息
            ],
            'tapes_backup_set' => [
                'post'  => 'opTapeBackupSet', //冻结/解冻 磁带
                'delete'  => 'delTapeBackupSet', //删除备份集
            ],
            'tapes_import' => [
                'post'  => 'importTape', //导入磁带
            ],
            'tapes_export' => [
                'post'  => 'exportTape', //导出磁带
            ],
            'tapes_retrieval' => [
                'post'  =>  'retrievalTape' // 数据检索
            ],
            'tapes_increase' => [
                'post'  =>  'increasePriority' // 提高优先级
            ],
            'tapes_decrease' => [
                'post'  =>  'decreasePriority' // 降低优先级
            ],
        ],
        'TapeInfo' => [ //类名
            'tapes' => [
                'get'  => 'getTapeLibInfo', //获取磁带库信息
            ],
            'tapes_carriage_info' => [
                'get'  => 'getTapeCarriageInfo', //获取磁带信息
                'put'  => 'modifyTapeCarriageInfo', //修改磁带信息
            ],
            'tapes_driver_info' => [
                'get'  => 'getTapeDriverInfo', //获取当前磁带库下所有驱动器
            ],
            'tapes_job' => [
                'get'  => 'getTapeJobInfo', //获取当前磁带相关任务
            ],
            'tapes_group' => [
                'get'  => 'getTapeGroupInfo', //获取磁带组信息
            ],
            'tapes_group_backup_set' => [
                'get'  => 'getBackupSetInfo', //获取备份集信息
                'put'  => 'modifyTapeBackupSet', //修改备份集信息
            ],
            'tapes_group_strategy' => [
                'get' => 'getTapeGroupStrategy', //获取磁带组策略信息
            ],
            'tapes_monitor' => [
                'get' => 'getTapeMonitorInfo', //获取磁带监控信息
            ],
            'tapes_timepoint' => [
                'get' => 'getTimePoint', // 获取磁带备份集树
            ],
            'tapes_modules' => [
                'get' => 'getBackupModule', // 获取磁带备份集模块
            ],
            'tapes_modules_tree' => [
                'get' => 'getBackupTree', // 获取磁带备份集模块树
            ],
            'tapes_timepoints_list' => [
                'get' => 'getBackupTimepoint', // 获取磁带备份对象下的时间点列表
            ],
        ]
    ],
];
