<?php

namespace app\v1\tenant\v0\logic;

use app\v1\common\logic\Base;

/**
 * note          租户管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/6 14:32
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Index extends Base
{
    /**
     * 公共方法
     * 获取租户对应的高级配置信息
     * @param string $tenantuuid uuid
     * @return array|mixed
     */
    public function pGetTenantSettings(string $tenantuuid)
    {
        $sql = "select config from bd_tenant where tenant_uuid = ?";
        $data = $this->dbSelect($sql, array($tenantuuid));

        return !empty($data) ? json_decode($data[0]['config'], true) : [];
    }

    /**
     * 获取租户所有用户组
     * @param string $tenantuuid uuid
     * @return unknown[]
     */
    public function getTenantAllUser(string $tenantuuid)
    {

        $sql = "select bu.user_uuid, bu.user_name from bd_user bu, bd_tenant bt 
        where (bu.create_user_uuid = bt.admin_uuid or bu.user_uuid = bt.admin_uuid) and bt.tenant_uuid = ? ";
        $userInfo = $this->dbSelect($sql, array($tenantuuid));

        return !empty($userInfo) ? array_column($userInfo, 'user_uuid') : [];
    }

    /**
     * 公共方法
     * 获取租户的所有用户
     * @param string $tenantuuid 租户uuid
     * @author xiezhuowei@vinchin.com
     * @return array  用户列表,包含用户部分字段
     */
    public function pGetTenantAllUser($tenantuuid)
    {
        $this->paramsCheck($tenantuuid);

        //用户-租户
        $sql = "select distinct bu.user_uuid, bu.user_name from 
                bd_user bu, mt_user_tenant mut
                where bu.user_uuid = mut.user_uuid and mut.tenant_uuid = ? ";
        $data1 = $this->dbSelect($sql, array($tenantuuid));

        //用户-用户组-租户
        $sql = "select distinct bu.user_uuid, bu.user_name from
                bd_user bu, mt_user_user_group muug, mt_user_group_tenant mugt 
                where bu.user_uuid = muug.user_uuid 
                  and muug.user_group_uuid = mugt.user_group_uuid and mugt.tenant_uuid = ? ";
        $data2 = $this->dbSelect($sql, array($tenantuuid));

        //合并数据
        $data = array_merge($data1, $data2);

        return v1_unique_multidim_array($data, 'user_uuid');
    }
}
