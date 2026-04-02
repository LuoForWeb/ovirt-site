<?php

namespace app\v1\dbcdp\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库实时 -- 之数据管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpData extends Base
{
    /**
     * DbcdpData constructor.
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'node_uuid' => ['min' => 36, 'regex' => '/^[\w|\d]\w+/'],  //节点UUID校验
            'restore_data_uuid' => ['require','min' => 36,'regex' => '/^[\w|\d]\w+/'],
        ];
        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'restore_data' => ['node_uuid'],
            'standby_restore_data' => ['restore_data_uuid']
        ];
    }
}
