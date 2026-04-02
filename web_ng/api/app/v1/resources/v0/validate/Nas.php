<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          nas 验证器
 * @author       wanggongxi@vinchin.com
 * @date         2023/5/22 17:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Nas extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset'        => ['require', 'number', 'between' => '0,99999'],
            'limit'         => ['require', 'number', 'between' => '1,500'],
            'nas_type'      => ['require', 'number', 'in' => [6,7]],
            'ip'            => ['require', 'ip'],
            'nas_uuid'      => ['require', 'length' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'check_uuid'      => ['require', 'length' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'share_path'    => ['require', 'regex' => '/^[\w|\d]\w+/'],
            'version'       => ['require', 'regex' => '/^[\w|\d]\w+/'],
            'nickname'      => ['require', 'regex' => '/^[\w|\d]\w+/'],
            'permission_flag'  => ['require', 'number', 'in' => [1,2]],
            'node_mount_list'  => ['require', 'array'],
            'nas_ip_list'  => ['require', 'array'],
            'nas_auth_flag'  => ['require', 'number', 'in' => [1,2]],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'lists' => ['offset', 'limit'], // 存储列表
            'add' => ['nas_type', 'ip', 'share_path',
                'permission_flag', 'version', 'node_mount_list'], // 添加nas设备
            'edit'  => ['nas_uuid', 'nickname', 'permission_flag'], // 编辑nas设备
            'check'  => ['check_uuid', 'nickname', 'permission_flag'], // 编辑nas设备检测
            'del'  => ['nas_uuid'], // 删除nas设备
            'lisence'  => ['nas_auth_flag', 'nas_ip_list'], // nas授权
        ];
    }
}
