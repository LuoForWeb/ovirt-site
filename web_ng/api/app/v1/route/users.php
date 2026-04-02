<?php

/**
*  ### 用户管理 用户
 */

return [

    // 用户模块
    'user' => [ // 模块名
        'Index' => [ // 类名
            'users' => [ // 路由名
                // 请求方式   => 方法名,
                'get'       => 'getUser',   // 获取用户 列表/详情
                'post'      => 'addUser',   // 新增用户
                'put'       => 'editUser',  // 编辑用户
                'delete'    => 'delUser',   // 删除用户
            ],
            'users_unlock' => [
                'post' => 'unlockUser', // 解锁用户
            ],
            'users_lock' => [
                'post' => 'lockUser', // 锁定用户
            ],
            'users_check' => [
                'get' => 'checkUser', // 验证用户名是否存在
            ],
            'users_resource' => [
                'get' => 'getResource', // 获取用户资源/组列表
            ],
            'users_roles' => [
                'get' => 'getRoles', // 获取用户权限列表
                'post' => 'doUserRole', // 分配用户角色权限
            ],
            'users_allocation' => [
                'post' => 'doAllocation', // 用户资源分配
                'delete' => 'delAllocation', // 用户资源/组删除
            ],
            'users_transfer' => [
                'post' => 'doTransfer', // 用户资源转移
            ],
            'users_manager' => [
                'get' => 'getManager', // 查看分配管理用户
                'post' => 'doManager', // 分配管理用户
            ],
            'users_auth' => [
                'get' => 'getAuth', // 获取权限资源列表
            ],
            'users_password' => [
                'get' => 'getUserPassword', // 获取用户密码
            ],
            'users_history' => [
                'get' => 'getLoginHistorys', // 获取当前登录用户的历史登录信息
            ],
            'users_auth_lists' => [
                'get' => 'getAuthLists', // 获取当前登录用户的关联管理用户列表
            ],
            'users_auth_func' => [
                'get' => 'getAuthFunc', // 获取授权的细节功能
            ],
            'users_verify_email' => [
                'get' => 'userInfoVerify',  // 验证用户名和邮箱
            ],
            'users_reset_password' => [
                'post' => 'resetPassWord',  // 重置密码
            ],
            'users_pass_configInfo' => [
                'get' => 'getPassComplexity'  // 获取密码复杂度
            ],
            'users_oldpass' => [
                'get' => 'oldpassAvailable'  // 获取旧密码
            ],
            'users_edit_password' => [
                'post' => 'editPassword',  // 修改密码
            ],
            'users_roles_list' => [
                'get' => 'getRoleList'  // 分配角色列表
            ],
            'users_permission_role' => [
                'post' => 'getUserPermissionRole'  // 根据传递来的permission数组组装成树返回
            ],
            'users_group_list' => [
                'get' => 'getUserGroupList',
            ],
            'users_self_info' => [
                'put' => 'editSelfInfo',
                'get' => 'getSelfInfo'
            ],
            'users_check_username' => [
                'get' => 'usernameExist'
            ],
            'users_usergroup_filehost' => [
                'post' => 'addUserGroupFileHost'
            ],
            'users_permission' => [
                'get' => 'getUserPermission'
            ],
            'users_check_password' => [
                'post' => 'checkUserPassword', // 验证用户密码
            ],
            'users_check_operation' => [
                'post' => 'checkUserAuth', // 验证用户操作权限
            ],
            'users_oldcustomepass' => [
                'get' => 'oldCustomePassAvailable'  // 获取原独立密码
            ],
            'users_edit_customepass' => [
                'put' => 'editCustomepass'  // 修改独立密码
            ]
        ],

        'Login' => [ // 类名
            'users_third_login' => [ // 路由名
                // 请求方式   => 方法名,
                'get' => 'thirdLogin',   //第三方登录

            ],

        ],

    ],



];
