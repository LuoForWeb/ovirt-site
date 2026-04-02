<?php

namespace app\v1\resources\v0\validate;

use app\v1\common\validate\Base;

class Driver extends Base
{
    public function __construct()
    {
        parent::__construct();

        $applicableOsType = xphp_get_config('driver', 'APPLICABLE_OS_TYPE', 'resources');
        $allDriverCheckUsage = array_values(xphp_get_config('driver', 'DRIVER_CHECK_USAGE', 'resources'));
        $this->rule = [
            'offset' => ['require', 'number', 'between:0,99999'],
            'limit' => ['require', 'number', 'between:0,5000'],
            'sort' => ['require'],
            'order' => ['require', 'in:asc,desc,ASC,DESC'],
            'drivers_uuid'   => ['require', 'max' => 36, 'regex' => '/^[\w|\d]\w+/'],
            'driver_uuid_list' => ['require', 'array'],
            'os_type' => ['require', 'in:' . implode(',', $applicableOsType)],
            'upload_path' => ['require'],
            'agent_uuid_list' => ['require', 'array'],
            'check_os_list' => ['require', 'array'],
            'driver_usage' => ['require', 'number', 'in:' . implode(',', $allDriverCheckUsage)],
        ];

        $this->message = $this->make_message($this->rule);

        $this->scene = [
            // 获取驱动列表
            'get_driver_list' => [
                'offset', 'limit', 'sort', 'order',
            ],
            // 获取驱动详情
            'get_driver_detail' => [
                'drivers_uuid',
            ],
            // 批量删除驱动
            'delete_drivers' => [
                'driver_uuid_list'
            ],
            // 添加驱动
            'add_driver' => [
                'os_type', 'remark', 'upload_path',
            ],
            // 获取代理驱动安装列表
            'get_agent_install_driver_list' => [
                'agent_uuid_list',
            ],
            // 安装代理驱动
            'install_agent_driver' => [
                'agent_uuid_list',
            ],
            // 批量检查操作系统驱动
            'check_os_drivers' => [
                'check_os_list',
                'driver_usage',
            ],
        ];
    }
}
