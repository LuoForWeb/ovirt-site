<?php

namespace app\v1\cloud\v0\validate;

use app\v1\common\validate\Base;

/**
 * Class Instance
 * @package app\v1\cloud\v0\validate
 */
class Instance extends Base
{
    /**
     * 构造方法
     */
    public function __construct()
    {

        parent::__construct();

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'instance_uuid' => ['require'],
            'region_uuid' => ['require'],
            'timepoint_uuids' => ['array'],
            'config_type' => ['require', 'integer', 'in' => [1, 2]],
            'platform_uuid' => ['require'],
            'instance_type' => ['require'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'getConfig' => ['instance_uuid'],
            'getAllConfig' => ['region_uuid', 'platform_uuid'],
            'getNetworkConfig' => ['region_uuid', 'platform_uuid', 'instance_type'],
            'getConfigByTimepoint' => ['timepoint_uuids', 'config_type'],
            'getDiskList' => ['platform_uuid', 'instance_uuid'],
            'getVolumeTypes' => ['hypervisor_type', 'region', 'root_disk_size'],
        ];
    }
}
