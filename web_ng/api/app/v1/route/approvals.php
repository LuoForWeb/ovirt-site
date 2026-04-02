<?php

/**
 * 审批流相关的路由定义
 */

return [
    'approval' => [ // 模块名
        'Approval' => [ // 类名
            'approvals_list'    => [
                'get' => 'getApprovalList',  // 获取审批流列表
                'post' => 'addApproval',  // 添加审批流
                'delete' => 'delApproval', //删除审批流
                'put' => 'modifyApproval' //修改审批流
            ],
            'approvals_unlock'    => [
                'put' => 'enableOrDisableApproval' //启用/禁用审批流
            ],
            'approvals_lock'    => [
                'put' => 'enableOrDisableApproval' //启用/禁用审批流
            ],
            'approvals_classify'    => [
                'get' => 'getClassifyList',  // 获取审批流分类列表
                'post' => 'addClassify',  // 添加审批流分类
                'delete' => 'delApprovalClassify', //删除审批流分类
                'put' => 'modifyClassify' //修改审批流分类
            ],
            'approvals_classify_unlock'    => [
                'put' => 'enableOrDisableClassify' //启用审批流分类
            ],
            'approvals_classify_lock'    => [
                'put' => 'enableOrDisableClassify' //禁用审批流分类
            ],
        ],
    ],
];
