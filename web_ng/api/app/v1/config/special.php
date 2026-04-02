<?php
/*
 * @Author: ChengJiaFu
 * @Date: 2025-02-10 10:57:00
 * @Description: 
 * @version: 1.0
 */

/**
 * 一些账号的独特的配置信息
 */

return [

    // token有效时间
    'access_token_timeout' => 0, // 0表示永久有效 单位为秒
    // 接口请求qps限制
    'maxQequest' => 100, // 最大请求量
    'unitQequest' => 1, // 计算时间默认1秒内
    'token_available_times' => 50, // 安全token校验有效次数
    'token_available_num' => 15, // 安全token同时存在的个数

    // 国际化的时间和数字输出格式
    'dateformat' => 'Y-m-d H:i:s', // 日期输出格式
    // 不进行参数过滤的路由
    'SPECIAL_ROUTE' => [
        'v1/industry/report', // 生成报告
        'v1/verification', // 创建、修改验证任务
        'v1/report/overview/download',
        'v1/s3/backup_job', // 创建/修改对象存储备份
        'v1/s3/recover_job', // 创建对象存储恢复
        'v1/s3/file_dir_son_tree', // 备份获取存储桶中的对象
        'v1/s3/recover_path_tree', // 恢复获取存储桶中的对象
        'v1/industry/job/client', // 切换设备保存报告的某些信息
        'v1/system/notice/send_email', // 发送邮件通知，给web_ui调用
        'v1/filecopy/job/backup',//创建、修改文件复制任务
        'v1/nas/backup_son_tree',//nas扫描文件子目录，文件复制处用(源端)
        'v1/files/agent_sontree',//文件扫描文件子目录，文件复制处用(源端)
        'v1/nas/recover_path_tree',//nas扫描文件子目录，文件复制处用（目标端）
        'v1/files/recover_path_tree',//文件扫描文件子目录，文件复制处用（目标端）
        'v1/industry/plan', //创建、修改和复制方案
        'v1/files/recover_job',//文件恢复任务
        'v1/nas/recover_job'//nass恢复任务
    ]
];
