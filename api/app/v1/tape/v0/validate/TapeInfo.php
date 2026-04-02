<?php

namespace app\v1\tape\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          磁带信息 validate
 * @author       wuyihang@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class TapeInfo extends Base
{
    /**
    * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'name'  => ['max' => 25, 'regex' => '/^[\w|\d]\w+/'],
            'new_name'  => ['max' => 25],
            'offset' => ['require', 'number'],
            'limit' => ['number'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'get_tape_lib_info'       => ['offset', 'limit'],       // 获取带库信息
            'modify_info'             => ['new_name'],
            'driver'                  => ['offset', 'limit'],
            'group'                   => ['offset', 'limit'],
            'tapes_modules'           => ['offset', 'limit'], // 获取磁带备份集模块
        ];
    }
}
