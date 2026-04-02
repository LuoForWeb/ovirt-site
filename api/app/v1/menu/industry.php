<?php

/**
* 这是行业合规的子栏目
 */

return [
    // 模板管理
    [
        'name' => 'industry_template',
        'path' => './content/platform/industry/template_detail.php',
        'class' => 'viconfont vicon-shenpi',
        'title' => 'WEB_INDUSTRY_TEMPLATE',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_industry_template_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 添加
            [
                'name' => 'p_industry_template_add',
                'title' => 'UI_PUBLIC_ADDNEW',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_industry_template_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 删除
            [
                'name' => "p_industry_template_delete",
                'title' => "UI_PUBLIC_DELETE",
                'function' => [
                    'get-url',
                ],
                'level' => 10,
            ],
            // 启用
            [
                'name' => 'p_industry_template_enable',
                'title' => 'WEB_PLATFORM_ENABLE',
                'function' => [
                    'post-users_unlock',
                ],
                'level' => 10,
            ],
            // 禁用
            [
                'name' => 'p_industry_template_disable',
                'title' => 'WEB_PLATFORM_DISABLE',
                'function' => [
                    'post-users_lock',
                ],
                'level' => 10,
            ],
        ]
    ],
    // 报告管理
    [
        'name' => 'industry_report',
        'path' => './content/platform/industry/report.php',
        'class' => 'viconfont vicon-baogaoliebiao',
        'title' => 'WEB_INDUSTRY_REPORT',
        'level' => 1,
        'child' => [
            // 查看
            [
                'name' => 'p_industry_report_list',
                'title' => 'UI_PUBLIC_LOOK',
                'function' => [
                ],
                'level' => 10,
            ],
            // 修改
            [
                'name' => 'p_industry_report_edit',
                'title' => 'UI_PUBLIC_MODIFY',
                'function' => [
                ],
                'level' => 10,
            ],
            // 审批
            [
                'name' => "p_industry_report_delete",
                'title' => 'WEB_INDUSTRY_APPROVE',
                'function' => [
                ],
                'level' => 10,
            ],
            // 撤销
            [
                'name' => 'p_industry_report_enable',
                'title' => 'WEB_INDUSTRY_REVOKE',
                'function' => [
                ],
                'level' => 10,
            ],
            // 审批流
            [
                'name' => 'p_industry_report_disable',
                'title' => 'WEB_INDUSTRY_APPROVAL',
                'function' => [
                ],
                'level' => 10,
            ],
            // 下载
            [
                'name' => 'p_industry_report_download',
                'title' => 'UI_PUBLIC_DOWNLOAD',
                'function' => [],
                'level' => 10,
            ],
        ]
    ]
];
