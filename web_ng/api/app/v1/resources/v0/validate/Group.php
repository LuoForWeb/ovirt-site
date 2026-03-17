<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          资源验证器
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/30 18:07
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
            'offset'           => ['require', 'number'],
            'limit'            => ['require', 'number'],
            'group_uuid'       => ['require', 'regex' => '/^[\w|\d]\w+/'],
            'name'             => ['require'],
            'source_type'      => ['require', 'number'],
            'groupDetail_uuid' => ['require', 'regex' => '/^[\w|\d]\w+/'],


        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 获取资源组
            'get-recourse' => [ 'offset', 'limit'],
            // 删除资源组
            'recourse_delete' => ['group_uuid'],
            // 添加资源组 source_type 为 3（虚拟机）、2（虚拟机传输代理）、7（节点）、8（存储资源）、10（客户端）
            'recourse_add' => ['name', 'source_type'],
            // 修改资源组
            'recourse_edit' => ['group_uuid', 'name', 'source_type'],
            // 获取资源组 资源
            'recourse_detail' => ['groupDetail_uuid'],
            // 添加存储设备 type默认0是资源 1表示资源组
            'recourse_all' => ['offset', 'limit', 'source_type'],
        ];
    }
}
