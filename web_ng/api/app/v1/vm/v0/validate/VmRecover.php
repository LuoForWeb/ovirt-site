<?php

namespace app\v1\vm\v0\validate;

use app\v1\common\validate\Base;

/**
 * note          虚拟机管理 -- 之恢复管理 validate
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:08
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmRecover extends Base
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
            'pid' => ['require'],
            'hypervisor_type' => ['require'],
            'tenant_flag' => ['boolean'],
            'instant_flag' => ['boolean'],
            'one_hypervisor_flag' => ['boolean'],
            'group_name' => ['require'],
            'group_uuid' => ['require'],
            'username' => ['require'],
            'password' => ['require'],
            'host_uud' => ['require'],

            //细粒度恢复
            'job_name' => ['require'],
            'point_info' => ['require'],
            'point_info.hypervisor_type' => ['require', 'integer', 'min' => 0],
            'point_info.points' => ['require'],
            'point_info.points.vm_uuid' => ['require'],
            'point_info.points.vm_name' => ['require'],
            'point_info.points.timepoint_uuid' => ['require'],
            'vms' => ['array'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'getRecoverVcenter' => ['hypervisor_type', 'tenant_flag', 'instant_flag', 'one_hypervisor_flag'],
            'asyncGetVcenter' => ['platform_uuid', 'pid'],
            'getXhereVolumePolicy' => ['platform_uuid'],
            'getOpenStackConfig' => ['platform_uuid', 'hypervisor_type', 'group_name', 'group_uuid', 'username', 'password'],
            'getHostConfig' => ['platform_uuid', 'hypervisor_type', 'host_uuid'],
            'getRecoverJobName' => ['hypervisor_type'],
            'grainJob' => $this->getGrainJobRuleKeys(),
            'checkEncryptPassword' => ['vms'],
            'getHostCommonInfo' => ['hypervisor_type', 'platform_uuid', 'host_uuid'],
        ];
    }

    /**
     * 获取细粒度恢复任务验证规则
     * @return array
     */
    private function getGrainJobRuleKeys(): array
    {
        return ['job_name', 'point_info', 'point_info.hypervisor_type', 'point_info.points',
            'point_info.points.vm_uuid', 'point_info.points.vm_name', 'point_info.points.timepoint_uuid'];
    }
}
