<?php

namespace app\v1\cloud\v0\validate;

use app\v1\common\validate\Base;

/**
 * Class CloudJobInfo
 * @package app\v1\cloud\v0\validate
 */
class CloudJobInfo extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'job_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'getJobBasicInfo' => ['job_uuid'],
        ];
    }
}
