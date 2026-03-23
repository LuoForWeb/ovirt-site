<?php

namespace app\v1\user\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          角色验证
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/8 15:39
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Role extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['require', 'number', 'min' => 0],
            'limit' => ['require','number', 'min' => 1],
            'rolename' => ['require', 'min' => 3, 'max' => 64],
            'permission' => ['require'],
            'roleuuid' => ['require'],
            'roles_uuid' => ['require'],
            'permissionuuid' => ['require'],
            'rolelist' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'list' => ['offset', 'limit'],
            'add' => ['rolename', 'permission'],
            'edit' => ['roleuuid', 'rolename', 'permissionuuid', 'permission'],
            'get' => ['roles_uuid'],
            'lock' => ['rolelist'],
            'init' => ['roleuuid'],
        ];
    }
}
