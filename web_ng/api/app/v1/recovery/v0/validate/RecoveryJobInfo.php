<?php

namespace app\v1\recovery\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          高级恢复：恢复、瞬时恢复、迁移、细粒度恢复 信息展示的验证
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:45
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class RecoveryJobInfo extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'start'         => ['require', 'number', 'min' => 0],
            'length'        => ['require','number', 'min' => 1],
            'detail_uuid' => ['require'],
            'recovery_uuid' => ['require'],
            'hypervisor' => ['require'],
            'vcenter_uuid' => ['require'],
            'host_uuid' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            // 列表
            'list' => ['start', 'length'],
            'platform_detail' => ['detail_uuid'],
            'job_detail' => ['recovery_uuid'],
            'recovery_vm_disk_network' => ['hypervisor', 'vcenter_uuid', 'host_uuid'],
        ];
    }
}
