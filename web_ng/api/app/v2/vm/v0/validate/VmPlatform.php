<?php

namespace app\v2\vm\v0\validate;

use app\v2\common\validate\Base;

/**
 * Class VmPlatform
 * @package app\v2\vm\v0\validate
 */
class VmPlatform extends Base
{
    /**
     * VmPlatform constructor.
     */
    public function __construct()
    {

        parent::__construct();

        $this->lang = array_merge($this->lang, [
            'egt' => ' must greater than or equal :rule',
        ]);

        // 这里存放的所有的字段要验证的规则集合
        $this->rule = [
            'platform_uuid' => ['require'],
            'hypervisor_type' => ['require', 'integer', 'min' => 0],
            'username' => ['require'],
            'password' => ['require'],
            'rname' => ['require'],
            'detail.type' => ['integer', 'min' => 1, 'max' => 3],
            'detail.reserved_num' => ['integer', 'min' => 1],
            'sort' => ['require'],
            'order' => ['require', 'in' => ['asc', 'desc']],
            'offset' => ['require', 'integer', 'min' => 0],
            'limit' => ['require', 'integer', 'in' => [10, 20, 50, 100, 150, 200]],
            'accurate_flag' => ['boolean'],
            'cloud_flag' => ['boolean'],
            'display_mode' => ['integer', 'in' => [1, 2, 3]],
            'platforms_uuids' => ['require', 'array'],
            'node' => ['require'], //数组json
            'platform_flag' => ['require', 'boolean'],
            'refresh' => ['require', 'integer', 'egt' => 5],
            'platform_type' => ['require', 'in' => ['vcenter', 'public', 'private']],
            'host_uuids' => ['require', 'array'],
            'tenant_flag' => ['boolean'],
            'user_flag' => ['boolean'],
            'alltype_flag' => ['boolean'],
            'archive_flag' => ['boolean'],
            'aws_flag' => ['boolean'],
            'ip' => ['require'],
            'vm_uuid' => ['require'],
            'group_uuid' => ['require'],
            'group_name' => ['require'],
            'refresh_flag' => ['boolean'],
        ];

        // 这里是自定义不满足要求的返回信息
        $this->message = $this->make_message($this->rule);

        // 这里是自定义校验的场景
        $this->scene = [
            'add' => ['hypervisor_type', 'rname'],
            'modify' => ['platform_uuid', 'rname'],
            'getList' => ['sort', 'order', 'offset', 'limit', 'accurate_flag', 'cloud_flag'],
            'getDetail' => ['platform_uuid'],
            'getTree' => ['display_mode', 'cloud_flag', 'archive_flag', 'aws_flag'],
            'getVmTree' => ['hypervisor_type', 'display_mode', 'cloud_flag', 'archive_flag', 'aws_flag'],
            'delete' => ['platforms_uuids'],
            'getVms' => ['platform_uuid', 'sort', 'order', 'offset', 'limit',
                'hypervisor_type', 'display_mode', 'node', 'platform_flag'],
            'autoRefresh' => ['refresh', 'platform_type'],
            'getHosts' => ['platform_uuid', 'sort', 'order', 'offset', 'limit'],
            'auth' => ['platform_uuid', 'host_uuids'],
            'getAllHypervisors' => ['tenant_flag', 'user_flag', 'cloud_flag', 'alltype_flag'],
            'getCurrentJobs' => ['platform_uuid'],
            'testEngine' => ['username', 'password', 'hypervisor_type', 'ip'],
            'getBackupTreeSpeed' => ['display_mode', 'hypervisor_type', 'platform_uuid', 'vm_uuid'],
            'groupUserVerify' => ['platform_uuid', 'hypervisor_type', 'group_uuid', 'group_name', 'username', 'password'],
            'getGroups' => ['platform_uuid', 'hypervisor_type', 'refresh_flag'],
            'getUserRoleList' => ['hypervisor_type', 'username', 'password', 'ip']
        ];
    }
}
