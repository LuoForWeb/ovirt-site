<?php

namespace app\v1\vm\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          虚拟机管理 -- 之备份管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmBackUp extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        $timeReg = '/^((20|21|22|23|[0-1]?\d):[0-5]?\d:[0-5]?\d)$/'; //验证时间格式

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'hypervisor_type' => ['require', 'integer', 'in' => xphp_get_config('vm', 'VMHYPERVISORTYPE')],

            'job_uuid' => ['require'], //修改任务使用
            'job_name' => ['require'],

            //备份源
            'src_info' => ['require'],
            //时间策略
            'time_strategy' => ['require'],
            //存储策略
            'storage_strategy' => ['require'],
            //保留策略
            'reserved_strategy' => ['require'],
            //高级策略
            'advanced_strategy' => ['require'],
            //传输策略
            'transport_strategy' => ['require'],
            //限速策略
            'speed_strategy' => ['require'],

            'emptyCachePath' => ['require', 'boolean']
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'getBackupJobName' => ['hypervisor_type'],
            'createJob' => array_diff(array_keys($this->rule),
                ['hypervisor_type', 'job_uuid', 'emptyCachePath']),
            'editJob' => array_diff(array_keys($this->rule), ['hypervisor_type']),
            'getAllInfo' => ['job_uuid'],
            'getBackupConfigs' => ['hypervisor_type']
        ];
    }
}
