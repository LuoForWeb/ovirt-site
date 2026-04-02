<?php

/**
*  备份数据验证
 */

return [

   ### 备份数据验证
    'verification' => [ // 模块名
        'Verification' => [ // 数据验证任务
            ### 数据验证
            'verification' => [ // 路由名
                // 请求方式   => 方法名,
                'post'      => 'createVerifyJob',       // 添加验证任务
                'put'      => 'editVerifyJob',       // 修改验证任务
            ],
            'verification_select_object_list' => [
                'get' => 'getVerifySelectObjectList'    //获取数据验证源对象列表
            ],
            'verification_task_name' => [
                'get' => 'getVerifyTaskName'    //获取数据验证可使用任务名
            ],
            'verification_get_timepoint' => [
                'get' => 'getNewestTimepointInfo'    //获取对象最新的时间点信息
            ]
        ],
        'VerificationJobController' => [ // 数据验证任务

            'verification_jobs_start_select' => [
                'post' => 'startSelectJob' //选对象启动数据验证任务
            ],
            'verification_jobs_stop_select' => [
                'post' => 'stopSelectJob' //选对象停止数据验证任务
            ]
        ],
        'VerificationJobInfo' => [ // 数据验证任务
            ### 数据验证
            'verification_job' => [ // 路由名
                // 请求方式   => 方法名,
                'get'      => 'getVerifyJobDetails',       // 获取数据验证任务详情信息
            ],
            'verification_job_object_list' => [
                // 请求方式   => 方法名,
                'get'      => 'getVerifyObjectList',       // 获取数据验证对象列表
            ],
            'verification_object_info' => [
                // 请求方式   => 方法名,
                'get'      => 'getVerifyObjectInfo',       // 获取数据验证任务单个对象详情信息
            ],
            'verification_job_report' => [
                // 请求方式   => 方法名,
                'get'      => 'getVerifyJobReport',       // 获取历史任务验证报告
                'post'      => 'sendVerifyEmail',       // 发送验证报告邮件
            ]
        ],
        'AppGroup' => [ // 应用组
            ### 数据验证
            'verification_app_group' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getAppgroup',       // 获取应用组信息
                'post'      => 'addAppgroup',       // 添加应用组
                'put'       => 'editAppgroup',       // 修改应用组
                'delete'    => 'deleteAppgroup'        //删除应用组
            ],
            'verification_app_group_name' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getAppgroupName',       // 获取应用组名称
            ],
        ],
        'VirtualLab' => [ // 虚拟演练室
            ### 数据验证
            'verification_lab' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getVirtualLab',       // 获取虚拟演练室信息
                'post'      => 'addVirtualLab',       // 添加虚拟演练室
                'put'       => 'editVirtualLab',       // 修改虚拟演练室
                'delete'    => 'deleteVirtualLab'        //删除虚拟演练室
            ],
            'verification_lab_isolation_network' => [
                // 请求方式   => 方法名,
                'post'      => 'createIsolationNetwork',       // 自动生成隔离网络
            ],
            'verification_lab_refresh' => [
                // 请求方式   => 方法名,
                'post'      => 'refreshVirtualLab',       // 刷新虚拟演练室
            ],
            'verification_lab_get_name' => [
                // 请求方式   => 方法名,
                'get'      => 'getCreateLabName',       // 获取可使用演练室名称
            ],
            'verification_lab_name_check' => [
                // 请求方式   => 方法名,
                'post'      => 'labnameAvailable',       // 检验虚拟演练室名称是否可用
            ],
            'verification_lab_get_host' => [
                // 请求方式   => 方法名,
                'get'      => 'getVirtualLabHost',       // 获取创建虚拟演练室所选择宿主机
            ],
        ],

    ]
];
