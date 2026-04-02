<?php

namespace app\v1\industry\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          报告 validate
 * @author       wanggongxi@vinchin.com
 * @date         2024/8/6 10:33
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Report extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'report_uuid'   => ['require'],
            'data'          => ['require', 'array'],
            'offset'        => ['number'],
            'limit'         => ['require', 'number'],
            'uuids'         => ['require', 'array'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'view' => ['report_uuid'],
            'save' => ['report_uuid', 'data'],
            'list' => ['offset', 'limit'],
            'lists' => ['report_uuid', 'offset', 'limit'],
            'uuids' => ['uuids'],
        ];
    }
}
