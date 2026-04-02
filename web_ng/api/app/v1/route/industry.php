<?php

/**
 * 行业相关的路由定义
 */

return [
    'industry' => [ // 模块名
        'Template' => [ // 类名
            'industry_templates'    => [
                'get'      => 'getTemplateList', // 模板列表/详情
                'post'     => 'updateTemplate', // 新建、编辑模板
                'delete'      => 'delTemplate', // 删除模板
            ],
            'industry_templates_enable'    => [
                'post'     => 'unLockTemplate', // 启用模板列表
            ],
            'industry_templates_disable'    => [
                'post'     => 'lockTemplate', // 禁用模板列表
            ],
        ],
        'Report' => [ // 类名
            'industry_report'       => [
                'get'      => 'viewReport', // 报告详情
                'post'     => 'makeReport',// 生成报告
                'put'      => 'saveReport',// 保存报告
            ],
            'industry_screenshot'   => [
                'post'     => 'allScrren', // 一键截屏
            ],
            'industry_screenshot_produce'  => [
                'post'     => 'produceScrren', // 生产截屏
            ],
            'industry_screenshot_verify'   => [
                'post'     => 'verifyScrren', // 验证截屏
            ],
            'industry_report_virtus'   => [
                'get'     => 'virtusLists', // 病毒查杀列表
            ],
            'industry_report_document'   => [
                'get'     => 'documentLists', // 文件比对列表
            ],
            'industry_report_download_file'   => [
                'post'    => 'downLoadFileUrl', // 报告下载的url
            ],
            'industry_report_download'   => [
                'get'     => 'downLoad', // 报告下载
                'post'    => 'downLoadUrl', // 报告下载的url
            ],
            'industry_report_send'   => [
                'post'    => 'sendEmail', // 发送报告到邮箱
            ],
            'industry_report_approve'   => [
                'post'    => 'approveReport', // 审批报告
            ],
            'industry_report_change_approval'   => [
                'post'    => 'changeApproval', // 更改审批流
            ],
            'industry_report_change_user'   => [
                'post'    => 'changeApproveUser', // 更改审批人
            ],
            'industry_report_cancel'   => [
                'post'    => 'cancelApproval', // 撤销审批流
            ],
            'industry_report_delete'   => [
                'delete'    => 'delReport', // 删除审批报告
            ],
            'industry_report_share'   => [
                'post'    => 'shareReport', // 分享报告
            ],
            'industry_report_remark'   => [
                'get'      => 'getReportCommentList', // 获取评论列表
                'post'    => 'remarkReport', // 评论报告
            ],
            'industry_report_custom_password'   => [
                'get'      => 'getCustomPwd', // 获取用户独立密码
            ],
            'industry_report_share_users'   => [
                'get'      => 'getShareUsers', // 报告审批时获取所有的用户或者用户组
            ],
        ],
        'Index' => [ // 类名
            'industry_client_summary'    => [
                'get'      => 'clientReportInfo', // 获取客户端统计信息
            ],
            'industry_report_summary'    => [
                'get'      => 'reportInfo', // 获取报告统计信息
            ],
            'industry_device_summary'    => [
                'get'      => 'getDeviceSummary', // 获取设备统计信息
            ],
            'industry_message_summary'    => [
                'get'      => 'getMessageCount', // 获取消息统计信息
            ],
        ],
        'Job' => [
            'industry_job'           => [
                'get'      => 'getVerifyJobDetail', // 获取数据验证获取任务详情
            ],
            'industry_job_object'    => [
                'get'      => 'getVerifyObjectList', // 获取数据验证对象列表
            ],
            'industry_job_client'    => [
                'get'      => 'getClientInfos', // 切换设备获取右边的详细信息
                'post'      => 'updateClientInfos', // 切换设备保存报告的某些信息
            ],
        ],
        'Plan' => [
            'industry_plan'         => [
                'get'      => 'index', // 方案列表、详情
                'post'     => 'submitPlan', // 新建、修改方案
                'put'      => 'copyPlan', // 复制方案
            ],
        ],
    ],

];
