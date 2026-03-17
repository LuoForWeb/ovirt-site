<?php

// 驱动管理

return [
    'resources' => [
        'Driver' => [
            'drivers' => [
                /**
                 * 获取驱动列表 & 获取驱动详情
                 * @link \app\v1\resources\v0\controller\Driver::getDrivers()
                 */
                'get' => 'getDrivers',
                /**
                 * 批量删除驱动
                 * @link \app\v1\resources\v0\controller\Driver::deleteDrivers()
                 */
                'delete' => 'deleteDrivers',
                /**
                 * 添加驱动
                 * @link \app\v1\resources\v0\controller\Driver::addDriver()
                 */
                'post' => 'addDriver',
            ],
            'drivers_file' => [
                /**
                 * 上传驱动
                 * @link \app\v1\resources\v0\controller\Driver::uploadDriver()
                 */
                'post' => 'uploadDriver',
            ],
            'drivers_agent_install' => [
                /**
                 * 获取代理驱动安装列表
                 * @link \app\v1\resources\v0\controller\Driver::getAgentInstallDriverList()
                 */
                'get' => 'getAgentInstallDriverList',
                /**
                 * 安装代理驱动
                 * @link \app\v1\resources\v0\controller\Driver::installAgentDriver()
                 */
                'post' => 'installAgentDriver',

            ],
            'drivers_check' => [
                /**
                 * 批量检查操作系统驱动
                 * @link \app\v1\resources\v0\controller\Driver::checkOsDrivers()
                 */
                'post' => 'checkOsDrivers',
            ],
        ],
    ],
];
