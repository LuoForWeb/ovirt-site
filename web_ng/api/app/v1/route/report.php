<?php

/**
 * 备份节点 
 */

return [

    ### 备份节点 
    'report' => [ // 模块名
        ### 备份节点
        'Report' => [ // 类名
            'report_template_list' => [
                'get' => 'getTemplateList', //获取全部资源
            ],
            'report_template_detail' => [
                'get' => 'getTemplateDetail', //获取全部资源
            ],
            'report_template_agent_data_details' => [
                'get' => 'getAgenteDetails', //获取客户端资源
            ],
            'report_template_vm_data_details' => [
                'get' => 'getVmReportList', //获取客户端资源
            ],
            'report_template_storage_data_details' => [
                'get' => 'getStorageReportList', //获取存储资源
            ],
            'report_template_task_data_details' => [
                'get' => 'getTaskReportList', //获取任务资源
            ],
            'report_template_cdp_data_details' => [
                'get' => 'getVolReportList', //获取实时容灾资源
            ],
            'report_template_nas_data_details' => [
                'get' => 'getNasReportList', //获取nas资源
            ],
            'report_template_app_data_details' => [
                'get' => 'getAppReportList', //获取app资源
            ],
            'report_template_hadoop_data_details' => [
                'get' => 'getHadoopReportList', //获取hadoop备份数据明细
            ],
            'report_template_ob_data_details' => [
                'get' => 'getObReporList', //获取对象存储数据明细
            ],
            'report_template_public_data_details' => [
                'get' => 'getPublicReportList', //获取公有云备份数据明细
            ],
            'report_template_private_data_details' => [
                'get' => 'getPrivateReportList', //获取私有云备份数据明细
            ],
            'report_template_filecopy_data_details' => [
                'get' => 'getFilecopyReportList' // 获取文件复制数据明细
            ],
            'report_template_k8s_data_details' => [
                'get' => 'getK8sReportList' // 获取文件复制数据明细
            ],
            'report_template' => [
                'post' => 'addOrModifyTemplate',//创建报表模板
                'put' => 'addOrModifyTemplate', // 修改报表模版
                'delete' => 'deleteTemplate', //删除报表模版
            ],
            'report_template_overview' => [
                'get' => 'getReportTemplateOverview' //创建指定的模板概览 
            ],
            'report_template_tendency' => [
                'get' => 'getStragyTendency' //创建指定的模板概览
            ],
            'report_template_agentOverview' => [
                'get' => 'getAgentOverview' //创建指定的模板概览
            ],
            'report_template_vmOverview' => [
                'get' => 'getVmOverview' //创建指定的模板概览
            ],
            'report_template_cloudOverview' => [
                'get' => 'getPublicCloud' //创建指定的模板概览
            ],
            'report_template_nasOverview' => [
                'get' => 'getNasOverview' //创建指定的模板概览
            ],
            'report_template_cdpOverview' => [
                'get' => 'getCdpOverview' //创建指定的模板概览
            ],
            'report_template_appOverview' => [
                'get' => 'getAppOverview' //创建指定的模板概览
            ],
            'report_template_nodeOverview' => [
                'get' => 'getNodeInfo' //创建指定的模板概览
            ],
            'report_template_storageOverview' => [
                'get' => 'getStorageOverview' //创建指定的模板概览
            ],
            'report_template_taskTendencyOverview' => [
                'get' => 'getTaskOverviewTendency' //创建指定的模板概览
            ],
            'report_template_alarmOverview' => [
                'get' => 'getAlarmNumber' //创建指定的模板概览
            ],
            'report_template_get_email' => [
                'get' => 'getEmail' //创建指定的模板概览
            ],
            'report_template_get_addressInfo' => [
                'get' => 'getAddressInfo' //创建指定的模板概览
            ],
            'report_template_get_notice' => [
                'get' => 'getReportNoticeInfo' //创建指定的模板概览
            ],
            'report_overview_download' => [
                'post' => 'reportDownLoadUrl', //导出概览模板
                'get' => 'reportDownLoad'
            ],
            'report_vm_backup_data' => [ // 获取虚拟机历史备份数据量大小
                'get' => 'getReportVmBackupdata'
            ],
            'report_vm_storage_usage' => [ // 获取虚拟机有效占用备份存储空间大小
                'get' => 'getReportVmStorageUsage'
            ],
            'report_client_export' => [
                'post' => 'exportAllClientReportData' // 导出全部客户端报表数据
            ]
        ],
    ]
];
