<?php

/**
*  ### 用户管理 用户组模块
 */
return [

    // 用户组模块
    'user' => [ // 模块名
        'Group' => [ // 类名
            'usergroups' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getGroupList',   // 获取用户组列表
                'post'      => 'addGroup',       // 新增用户组
                'put'       => 'editGroup',      // 编辑用户组
                'delete'    => 'delGroup',       // 删除用户组
            ],
            'usergroups_unlock' => [
                'post' => 'unlockGroup', // 解锁用户组
            ],
            'usergroups_lock' => [
                'post' => 'lockGroup', // 锁定用户组
            ],
            'usergroups_user' => [
                'get' => 'getUserGroupUser', // 加载关联用户
            ],
            'usergroups_role' => [
                'get' => 'getUserGroupRole', // 加载关联角色
            ],
            'usergroups_resource' => [
                'get' => 'initResourcegroup',  // 加载关联资源组
            ],
            'usergroups_usergroup_permissiontree' => [
                'get' => 'getUserGroupPermissionTree',  // 获取用户组权限树
            ],
            'usergroups_data' => [
                'get' => 'getOneUserGroupData'  // 获取单个用户组所有信息
            ]
        ],
    ],

];
