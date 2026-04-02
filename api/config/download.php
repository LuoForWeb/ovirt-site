<?php

/**
 * 这里下整个系统涉及到下载相关的路由配置
 * 后台的接口也暂时放在这里
 */

return [
    'v1/jobs/log_down', // 下载任务日志
    'v1/exchange/restore/export_data',//exchange导出功能
    'v1/users/third_login',
    'v1/alarm/job/download_log',//下载日志文件
    'v1/exchange/jobs/download',//exchange跳过数据下载
    'v1/s3/download_pass',
    'v1/system/license', // 获取license的extension解密后的数据
    'v1/login/auth', // 单点登录
    'v1/login/sso_login', // 单点登录
    'v1/system/download',  // 下载文件
    'v1/system/generate/download',  // 生成下载文件的url
    'v1/system/get_time',  //获取系统时间
    'v1/agents/file',  // 解析上传的客户端部署文件
    'v1/system/tools/uploads',  // 系统工具上传文件
    'v1/hadoop/download_pass', // hadoop跳过文件下载
    'v1/recovery/graininess/download', // 细粒度下载
    'v1/system/auth/get/thumbprint/file', //授权页面下载指纹文件
    'v1/users/pass/configInfo',   // 密码配置信息
    'v1/users/edit/password', // 修改密码
    'v1/users/oldpass', //旧密码信息
    'v1/users/reset/password', //修改密码
    'v1/industry/report/download', //下载报告
    'v1/report/overview/download', //下载报告
    'v1/agents/download/plugins',  //下载插件

    'v1/jobs/export',  //导出全部当前任务
    'v1/jobs/history_export', // 导出全部历史任务
    'v1/alarm/job',  //导出全部任务告警
    'v1/alarm/system',  //导出全部系统告警
    'v1/alarm/dispatch', // 发送告警邮件
    'v1/logs/job',  //导出全部任务日志
    'v1/logs/system',  //导出全部系统日志
    'v1/logs/ha_logs',  //导出全部高可用日志

    'v1/report/template/export_app', // 导出全部app应用报表
    'v1/report/template/export_storage', // 导出全部存储报表
    'v1/report/template/export_task', // 导出全部任务报表
    'v1/report/template/export_vm', // 导出全部虚拟机报表
    'v1/report/template/export_client', // 导出全部主机报表
    'v1/report/template/export_vol', // 导出全部实时容灾报表
    'v1/report/template/export_ob', // 导出全部对象存储报表
    'v1/report/template/export_public', // 导出全部公有云报表
    'v1/report/template/export_private', // 导出全部私有云报表
    'v1/report/template/export_hadoop', // 导出全部hadoop报表
    'v1/system/auth/upload_license', //上传授权文件
    'v1/system/setting', //GMP自定义logo
    'v1/drivers/file',  // 上传驱动
    'v1/virus/file',  // 上传病毒库

    'v1/nodes/timepoints/node',  // 根据时间点获取节点信息

    'v1/filecopy/download_pass',//文件复制跳过文件下载
    'v1/system/backup_upload_file', // 上传系统手动备份文件

    'v1/system/messages',  // 发送消息(后台调用)

    'v1/system/upgrade/upload', // 上传升级包
    'v1/system/times/info', //获取系统时间
    'v1/system/notice/send_email', // 发送邮件通知，给web_ui调用
    'v1/users/verify/email',  // 忘记密码-发送邮件
    'v1/backup_data/jobs/list/export', // 导出备份数据任务列表
    'v1/backup_data/items/list/export', // 导出备份数据对象列表
    'v1/backup_data/remote/list/export', // 导出备份数据异地任务列表
    'v1/backup_data/points/export', // 导出备份数据时间点列表
    'v1/tapes/timepoints/list', // 导出磁带数据时间点
];
