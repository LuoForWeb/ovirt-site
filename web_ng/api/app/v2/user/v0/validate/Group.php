<?php

namespace app\v1\user\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          用户组相关的验证
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/7 17:26
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Group extends Base
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
            'user_group_name' => ['require', 'min' => 3, 'max' => 64],
            'usergroups_uuid' => ['require'],
            'user_group_uuid' => ['require'],
            'data_list' => ['require'],
            'groups' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'list' => ['offset', 'limit'],
            'add' => ['user_group_name'],
            'get' => ['usergroups_uuid'],
            'edit' => ['user_group_uuid', 'user_group_name'],
            'lock' => ['groups'],
            'del' => ['usergroups_uuid'],
            'dels' => ['groups'],
        ];
    }
}
