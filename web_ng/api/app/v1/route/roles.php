<?php

/**
 *  ### 用户管理 用户角色模块
 */

return [

    // 用户角色模块
    'user' => [ // 模块名
        'Role' => [ // 类名
            'roles' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getRoleList',   // 获取用户角色列表
                'post'      => 'addRole',       // 新增用户角色
                'put'       => 'editRole',      // 编辑用户角色
                'delete'    => 'delRole',       // 删除用户角色
            ],
            'roles_unlock' => [
                'post' => 'unlockRole', // 解锁用户角色
            ],
            'roles_lock' => [
                'post' => 'lockRole', // 锁定用户角色
            ],
            'roles_user_allocation' => [
                'post' => 'addUserRoleAllocation', // 添加角色和用户关联确认
            ],
            'roles_usergroup' => [
                'post' => 'addUsergroupRoleAllocation', // 添加角色和用户组关联确认
            ],
            'roles_allocationList' => [
                'get' => 'initAllocationList', // 初始化角色关联用户和用户组列表
            ],
            'roles_userlist' => [
                'get' => 'getUserList',  // 初始化用户列表
            ],
            'roles_usergrouplist' => [
                'get' => 'getUsergroupList',  // 初始化用户组列表
            ],
            'roles_user' => [
                'get' => 'getRoleUser',
            ],
            'roles_roleusergroup' => [
                'get' => 'getRoleUserGroup',
            ],
            'roles_oldInfo' => [
                'get' => 'getRoleOldInfo'
            ],
            'roles_permissiontree' => [
                'get' => 'getRolePermissionTree'  // 获取角色权限树
            ],
        ],
    ],


    /*'role' => [
        'get' =>  ['module' => 'user', 'class' => 'Role', 'method' => 'getUserPermission'],  // 获取用户角色权限树
        'post' => ['module' => 'user', 'class' => 'Role', 'method' => 'getUserRoleList'],  // 获取用户角色信息列表为了其它调取而分配
    ],
    'editrole' => [
        'get'   => ['module' => 'user', 'class' => 'Role', 'method' => 'getRole'],  // 获取用户角色信息
        'put' => ['module' => 'user', 'class' => 'Role', 'method' => 'editRole'],  // 编辑用户角色
    ],

    'initrole' => [
        'post' => ['module' => 'user', 'class' => 'Role', 'method' => 'initAllocationList'],  // 初始化角色关联用户、用户组列表
    ],
    'inituserrole' => [
        'put' => ['module' => 'user', 'class' => 'Role', 'method' => 'addUserRoleAllocation'],  // 添加角色和用户关联
    ],
    'initgrouprole' => [
        'put' => ['module' => 'user', 'class' => 'Role', 'method' => 'addUsergroupRoleAllocation'],  // 添加角色和用户组关联
    ],*/


];
