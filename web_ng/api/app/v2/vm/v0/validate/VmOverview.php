<?php

namespace app\v2\vm\v0\validate;

use app\v2\common\validate\Base;

/**
 * note          虚拟机概览 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmOverview extends Base
{
    /**
     * 构造方法
     */
    function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'sub_module_type' => ['integer', 'between' => '1, 3'],
            'offset' => ['integer', 'min' => 0],
            'limit' => ['integer', 'min' => 1],
            'order' => ['in' => ['asc', 'desc']],
            'accurate_flag' => ['boolean'],
            'start_time' => ['date'],
            'end_time' => ['date'],
            'hypervisor_type' => ['integer', 'min' => 0],
            'backup_status' => ['integer', 'between' => '0, 2'],
            'platform_uuid' => ['regex' => '/^[\w|\d]\w+/'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'count' => ['sub_module_type'],
            // 列表
            'list' => ['sub_module_type', 'offset', 'limit', 'order', 'accurate_flag', 'start_time', 'end_time',
                'hypervisor_type', 'backup_status', 'vm_ip', 'platform_uuid'],
        ];
    }
}
