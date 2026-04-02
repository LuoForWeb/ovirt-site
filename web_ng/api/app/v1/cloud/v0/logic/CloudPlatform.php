<?php

namespace app\v1\cloud\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Node;
use app\v1\cloud\v0\service\Service as CloudService;
use app\v1\tenant\v0\logic\Tenant;
use app\v1\vm\v0\logic\VmPlatform;

/**
 * Class CloudPlatform
 * @package app\v1\cloud\v0\logic
 */
class CloudPlatform extends Base
{
    /**
     * 获取平台下的区域
     * @param string $platformUuid 平台UUID
     * @return array
     */
    public function getPlatformRegions(string $platformUuid): array
    {
        $sql = "select vh.host_uuid, vh.host_name, vh.detail, mut.tenant_uuid from vm_host vh
                join vm_vcenter vv on vh.vcenter_uuid = vv.vcenter_uuid 
                left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid 
                where vh.vcenter_uuid = ?";
        $rows = $this->dbSelect($sql, [$platformUuid]);
        //如果是租户内用户操作，检测是否已配置指定区域
        $tenantRegions = [];
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['host_list']) {
                $tenantRegions = $settings['host_list'];
            }
        }

        $data = [];
        $userLang = xphp_get_user_info()['language'];
        foreach ($rows as $row) {
            if (!empty($_SESSION['tenantuuid'])) {
                // 获取已分配的或租户自有的
                if (!in_array($row['host_uuid'], $tenantRegions) && $_SESSION['tenantuuid'] != $row['tenant_uuid']) continue;
            }
            $detail = $row['detail'] ? json_decode($row['detail'], true) : [];
            $regionCnName = '';
            $regionEnName = '';
            if (('zh-cn' == $userLang || 'zh-tw' == $userLang) && $detail['cn_name']) {
                $regionCnName = $detail['cn_name'];
            }
            if($detail['en_name']){
                $regionEnName = $detail['en_name'];
            }
            $data[] = [
                'region_uuid' => $row['host_uuid'],
                'region_name' => $row['host_name'],
                'region_cn_name' => $regionCnName,
                'region_en_name' => $regionEnName,
                'available_zone' => $detail['available']
            ];
        }
        return ['rows' => $data, 'total' => count($data)];
    }

    /**
     * 获取所有可用区
     * @param array $params 参数：platformUuid, region
     * @return array
     */
    public function getAvailabilityZones(array $params): array
    {
        $sql = "select detail from vm_host where vcenter_uuid = ? and host_name = ?";
        $rows = $this->dbSelect($sql, [$params['platform_uuid'], $params['region']]);
        $data = [];
        foreach ($rows as $row) {
            $detail = $row['detail'] ? json_decode($row['detail'], true) : [];
            foreach ($detail['available'] as $az) {
                $data[] = $az['zone'];
            }
        }
        return ['rows' => $data];
    }

    /**
     * 获取全部云平台
     * @param array $params 参数
     * @return array
     */
    public function getAllPlatforms(array $params): array
    {
        $publicTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $sql = "select vcenter_uuid, nickname, hypervisor_type, vv.detail, mut.tenant_uuid from vm_vcenter vv 
            left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid 
            where hypervisor_type in ('" . implode("','", $publicTypes) . "') 
            and vcenter_flag = 1 and online_flag = 1";
        if ($params['hypervisor_type']) {
            $hypervisorType = intval($params['hypervisor_type']);
            $sql .= " and hypervisor_type = {$hypervisorType}";
        }
        if (empty($_SESSION['tenantuuid'])) {
            // 不能看租户内部的资源
            $sql .= " and mut.tenant_uuid IS NULL ";
        }
        // 获取非分配资源的权限
        if (v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_users('awsprotect');
            $sql .= " and vv.user_uuid in $resourceUuidSql";
        }
        $data = $this->dbSelect($sql, []);
        //如果是租户内用户操作，检测是否已配置指定云平台
        $tenantVcenter = [];
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['recover']) {
                $tenantVcenter = $settings['recover']['vcenter'];
                if (is_string($tenantVcenter)) {
                    $tenantVcenter = [$tenantVcenter];
                }
            }
        }

        $info = [];
        foreach ($data as $d) {
            if (!empty($_SESSION['tenantuuid'])) {
                // 获取已分配的或租户自有的
                if (!in_array($d['vcenter_uuid'], $tenantVcenter) && $_SESSION['tenantuuid'] != $d['tenant_uuid']) continue;
            }
            $itemInfo = [
                'platform_uuid' => $d['vcenter_uuid'],
                'nickname' => $d['nickname'],
                'hypervisor_type' => intval($d['hypervisor_type'])
            ];
            // 华为云添加企业项目列表
            if (xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD'] == $d['hypervisor_type']) {
                $itemInfo['enterprise_projects'] = json_decode($d['detail'], true)['enterprise_projects'] ?? [];
            }
            $info[$d['vcenter_uuid']] = $itemInfo;
        }
        return ['rows' => $info];
    }

    /**
     * 获取可用的云平台别名
     * @param string $prefix 别名前缀，即平台类型
     * @return string
     */
    public function getValidPlatformNickname(string $prefix): string
    {
        for ($i = 1; $i < 100; $i++) {
            $nickname = $prefix . '-' . $i;
            $sql = "select vcenter_id from vm_vcenter where nickname = ?";
            $data = $this->dbSelect($sql, array($nickname));
            if (empty($data)) {
                return $nickname;
            }
        }
        // 默认平台类型的名称
        return $prefix;
    }

    /**
     * 云平台同步代理镜像，暂时华为云使用，异步命令模式
     * @param string $platformUuid
     * @return array
     */
    public function syncProxyImage(string $platformUuid): array
    {
        $operate = 'VM_VCENTER_OP_UPDATE_PUBLIC_CLOUD_SHARED_IMAGE';
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $msg = json_encode(['vcenter_uuid' => $platformUuid]);

        $cloudService = new CloudService();
        $cloudService->unifyCloudService(
            $nodeUuid,
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD'],
            $operate,
            $msg,
            false,
            true
        );
        return array('result' => true, 'errorCode' => 0);
    }

    /**
     * 获取云平台某区域的公有IP列表
     * @param array $params 参数
     * @return array
     */
    public function getPublicIpList(array $params): array
    {
        $operate = 'VM_VCENTER_OP_GET_PUBLIC_CLOUD_PUBLIC_IP_LIST';
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $msg = json_encode(['vcenter_uuid' => $params['platform_uuid'], 'host_uuid' => $params['region_uuid']]);

        $mbResult = $this->service()->unifyCloudService(
            $nodeUuid,
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD'],
            $operate,
            $msg,
            true
        );
        return $mbResult['result'] ? $mbResult['msg']['ip_list'] : [];
    }

    /**
     * 云平台删除共享镜像，暂时华为云使用
     * @param string $platformUuid
     * @return bool
     */
    public function deleteSharedImage(string $platformUuid): bool
    {
        $operate = 'VM_VCENTER_OP_DELETE_PUBLIC_CLOUD_SHARED_IMAGE';
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $msg = json_encode(['vcenter_uuid' => $platformUuid]);

        $mbResult = (new CloudService())->unifyCloudService(
            $nodeUuid,
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD'],
            $operate,
            $msg,
            false,
            true
        );
        return $mbResult['result'];
    }

    /**
     * 获取区域的多语言名称
     * @param string $name
     * @param string $detailJson
     * @return string
     */
    public function getRegionNameMultiLang(string $name, string $detailJson): string
    {
        $detail = json_decode($detailJson, true);
        $userLang = xphp_get_user_info()['language'];
        if (('zh-cn' == $userLang || 'zh-tw' == $userLang) && $detail['cn_name']) {
            $name .= "({$detail['cn_name']})";
        }
        if ('en-us' == $userLang && $detail['en_name']) {
            $name .= "({$detail['en_name']})";
        }
        return $name;
    }
}
