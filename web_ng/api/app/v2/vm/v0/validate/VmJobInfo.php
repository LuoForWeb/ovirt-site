<?php

namespace app\v2\vm\v0\validate;

use app\v2\common\validate\Base;

/**
 * note          虚拟机管理 -- 之任务信息 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmJobInfo extends Base
{
    /**
     * 构造方法
     */
    function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'offset' => ['require', 'number', 'min' => 0],
            'limit' => ['require','number', 'min' => 1],
            'job_uuid' => ['require'],
            'root_flag' => ['require', 'boolean'],
            'showtype' => ['require', 'integer', 'in' => xphp_get_config('vm', 'GUEST_DISPLAY_MODE')],
            'path' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'getBasicInfo' => ['job_uuid'],
            'getGranularFiles' => ['job_uuid', 'root_flag', 'offset', 'limit', 'showtype'],
            'download' => ['job_uuid', 'showtype', 'path']
        ];
    }
}
