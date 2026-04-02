<?php

/** 大屏 */

return [
    'visualscreen' => [  // 模块名
        'Visual' => [ // 类名
            'visual_overview' => [  // 路由名
                'get' => 'getOverView',
            ],
            'visual_system_config' => [
                'get' => 'getConfigure'
            ],
            'visual_statistic_data' => [
                'get' => 'getStatisticData'
            ],
            'visual_current_task_warn' => [
                'get' => 'getCurrentTaskAndWarning'
            ],
            'visual_system_lisence_info' => [
                'get' => 'getSystemLisenceInfo'
            ],
            'visual_current_task_list' => [
                'get' => 'getCurrentTaskList'
            ],
            'visual_other_view' => [
                'get' => 'getOtherView'
            ],
            'visual_node_monitor' => [
                'get' => 'getNodeMonitor'
            ],
            'visual_data_survey' => [
                'get' => 'getDataSurvey'
            ],
            'visual_lang' => [
                'get' => 'getVisualLang'
            ],
            'visual_write_refresh_log' => [
                'post' => 'writeRefreshLog'
            ]
        ]
    ]
];
