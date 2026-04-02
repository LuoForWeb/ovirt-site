<?php

namespace app\v1\hadoop\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          hadoop -- 之集群管理 validate
 * @author       lilingyu@vinchin.com
 * @date         2024/12/16 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopCluster extends Base
{

    function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'authflag' => ['gt' => 0, 'lt' => 3, 'integer','require'],
            'refresh' => ['gt' => 4, 'lt' => 10000, 'integer','require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'authflag_rule' => ['authflag'],
            'refreshtime_rule' => ['refresh'],
        ];

    }

}
