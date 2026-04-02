<?php

namespace app\v1\volcdp\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          卷实时 -- 之数据管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpData extends Base
{

    function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'start' => ['require', 'number', 'min'=>0],
            'length' => ['require','number', 'min' => 1],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'list' => ['start', 'length'],
        ];

    }

}
