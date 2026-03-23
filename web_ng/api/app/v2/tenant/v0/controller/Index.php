<?php

namespace app\v2\tenant\v0\controller;

use app\v2\common\controller\AuthBase;

/**
 * note          租户管理
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/2 16:20
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends AuthBase
{
    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 公共方法
     * 获取租户对应的高级配置信息
     * @param string $tenantuuid uuid
     * @return array|mixed
     * @author luokai@vinchin.com
     */
    public function pGetTenantSettings(string $tenantuuid)
    {
        return $this->logic()->pGetTenantSettings($tenantuuid);
    }

    /**
     * 获取租户所有用户组
     * @param string $tenantUuid 租户uuid
     * @return array
     */
    public function getTenantAllUser(string $tenantUuid): array
    {
        return $this->logic()->getTenantAllUser($tenantUuid);
    }
}
