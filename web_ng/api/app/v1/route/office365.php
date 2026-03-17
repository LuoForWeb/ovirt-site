<?php

return [
    'exchange' => [
        //组织管理
        'ExchangeOrganization' => [
            'office365_organization' => [
                //获取组织信息
                'get' => 'getOrganizationInfo',
                //添加组织
                'post' => 'addOrganization',
                //修改组织
                'put' => 'editOrganization',
                //删除组织
                'delete' => 'deleteOrganization',
            ],
            'office365_organization_auth_code' => [
                //获取Microsoft365身份验证码和身份验证结果
                'get' => 'getVertifyCode',
            ],
            'office365_organization_refresh' => [
                //获取Microsoft365组织自动刷新间隔时间
                'get' => 'getRefreshTime',
                //更新Microsoft365组织自动刷新间隔时间
                'put' => 'editRefreshTime',
            ],
            'office365_organization_agent' => [
                //获取exchange server用于客户端关联的客户端
                'get' => 'getServerAgent',
            ],
            'office365_organization_sync' => [
                //同步按钮（手动刷新组织接口）
                'post' => 'syncOrganization',
            ],
        ],
    ],
];
