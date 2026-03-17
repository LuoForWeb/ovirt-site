<?php

namespace app\v1\dbcdp\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          数据库实时 -- 之任务操作 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpJobController extends Base
{
    /**
     * DbcdpJobController constructor.
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'start_uuid' => ['require'],
            'delete_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 启动任务
            'start' => ['start_uuid'],
            'delete' => ['delete_uuid'],
        ];
    }
}
