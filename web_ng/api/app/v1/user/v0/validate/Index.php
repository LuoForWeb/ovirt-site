<?php

namespace app\v1\user\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          用户相关的验证
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/3 15:47
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['number', 'min' => 0],
            'limit' => ['require','number', 'min' => 1],
            'username' => ['require', 'min' => 2, 'max' => 64],
            'password' => ['require', 'min' => 6, 'max' => 172],
            'usertype' => ['require', 'number'],
            'quota' => ['require'],
            'email' => ['email'],
            'users_uuid' => ['require', 'max' => 36],
            'useruuid' => ['require', 'max' => 36],
            'users' => ['require'],
            'source_list' => ['require', 'array'],
            'manage_uuid' => ['require', 'max' => 36],
            'role_uuid' => ['require', 'max' => 36],
            'user_name' => ['require', 'min' => 2],
            'user_type' => ['require', 'max' => 1],
            'editflag' => ['require'],
            'usergroupuuid' => ['require', 'max' => 36],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 用户列表
            'list' => ['offset', 'limit'],
            'add' => ['username', 'password', 'usertype', 'quota', 'email'],
            'edit' => ['users_uuid', 'username', 'password', 'quota', 'email'],
            'get' => ['users_uuid'],
            'lock' => ['users'],
            'del' => ['users'],
            'users_resource' => ['users_uuid', 'offset', 'limit'],
            'users_allocation_post' => ['users_uuid', 'type', 'source_type', 'source_list'],
            'users_allocation_del' => ['users_uuid', 'type', 'source_list'],
            'users_transfer' => ['users_uuid', 'manage_uuid', 'source_list'],
            'users_roles' => ['users_uuid'],
            'users_manager_get' => ['users_uuid'],
            'users_manager_post' => ['users_uuid'],
            'users_check' => ['user_name'],
            'users_roles_post' => ['users_uuid', 'role_uuid', 'source_list'],
            'user_name_check' => ['useruuid','username','user_type'],
            'user_role' => ['editflag','usergroupuuid'],
            'user_group' => ['editflag','useruuid'],
        ];
    }
}
