<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2024-07-19 11:51:54
 * @LastEditTime: 2025-09-18 17:46:42
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */

return [
    'backupData' => [ // 模块名
        'DataManage'=> [ // 类名
            "backup_data_jobs_list"=>[ // 路由
                "post"=>"getTaskGrid" // 获取任务列表
            ],
            "backup_data_jobs_list_export"=>[ // 路由
                "post"=>"exportAllTaskList" // 导出任务列表
            ],
            "backup_data_jobs_list_sub"=>[ // 路由
                "get"=>"getSubTaskList" // 获取任务列表
            ],
            "backup_data_items_list"=>[
                "post"=>"getItemGrid", // 获取对象列表
            ],
            "backup_data_items_list_export"=>[
                "post"=>"exportAllItemList", // 导出对象列表
            ],
            "backup_data_vol_list"=>[
                "get"=>"getVolTagPointGrid", // 获取整机实时标签点
                "delete"=>"deleteTagPoint", // 删除整机实时标签点
            ],
            "backup_data_vol_remark"=>[
                "get"=>"remarkTagPoint", // 整机标签点备注
            ],
            "backup_data_remote_list"=>[
                "get"=>"getRemoteGrid", // 获取异地副本数据列表
            ],
            "backup_data_remote_list_export"=>[
                "post"=>"exportAllRemoteTaskList", // 导出异地副本数据列表
            ],
            "backup_data_remote_tree"=>[
                "get"=>"getRemoteItem", // 获取异地副本数据树
            ],
            "backup_data_points"=>[
                "post"=>"getAllPoints", // 获取备份点
                "delete"=>"deletePoint", // 删除备份点
            ],
            "backup_data_points_export"=>[
                "post"=>"exportAllPointList", // 导出备份点
            ],
            "backup_data_task_tree"=>[
                "post"=>"getTaskTree", // 获取备份点
            ],
            "backup_data_points_details"=>[
                "get"=>"getPointDetailsInfo", // 获取时间点备份的虚拟机路径、文件列表等
            ],
            "backup_data_points_remark"=>[
                "put"=>"updateRemarks", // 设置备份点备注
            ],
            "backup_data_points_gfs_mark"=>[
                "post"=>"setGfsMark", // 设置备份点gfs标记
            ],
            "backup_data_points_mark"=>[
                "get"=>"addStar", // 设置永久标记
                "delete"=>"deleteStar", // 取消永久标记
            ],
            "backup_data_points_worm"=>[
                "get"=>"setWormTime", // 配置worm时间
            ],
            "backup_data_points_virus_list"=>[
                "get"=>"getVirusList", // 获取病毒感染文件列表
            ],
            "backup_data_virus_history"=>[
                "get"=>"getVirusHistoryList", // 获取病毒扫描历史
            ],
            "backup_data_verify_history"=>[
                "get"=>"getDataVerifyHistory", // 获取验证历史记录
            ],
        ]
    ]
];