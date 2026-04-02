<?php

namespace app\v1\recovery\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          瞬时恢复快照点管理的验证
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:45
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class InstantaneousData extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'start' => ['require', 'number', 'min' => 0],
            'length' => ['require','number', 'min' => 1],
            'recovery_uuid' => ['require'],
            'job_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'list' => ['start', 'length'],
            'instance_points' => ['recovery_uuid'],
            'job_points' => ['job_uuid'],
        ];
    }
}
