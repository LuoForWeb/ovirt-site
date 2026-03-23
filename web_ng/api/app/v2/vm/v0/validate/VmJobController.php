<?php

namespace app\v2\vm\v0\validate;

use app\v2\common\validate\Base;

/**
 * note          虚拟机管理 -- 之任务操作 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmJobController extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'platform_uuid' => ['require'],
            'vm_uuid' => ['require'],
            'jobs_uuid' => ['require'],
            'display_mode' => ['require', 'integer', 'in' => [1, 2, 3]],
            'start_type' => ['require', 'number', 'in' => [0, 1, 2, 3]],
            'hypervisor_type' => ['require', 'number'],
            'job_uuid' => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'vm_uuids' => ['require', 'array']
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'vmOperate' => ['platform_uuid', 'vm_uuid'],
            'addToBackupJob' => ['jobs_uuid', 'platform_uuid', 'vm_uuid', 'display_mode'],
            // 启动任务
            'start' => ['start_type'],
            'deleteVMFromJob' => ['hypervisor_type', 'job_uuid', 'vm_uuids']
        ];
    }
}
