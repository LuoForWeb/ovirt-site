<?php

namespace app\v1\cloud\v0\logic;

use app\v1\backupData\v0\logic\DataManage;
use app\v1\common\logic\Base;
use app\v1\recovery\v0\logic\RecoveryJobController;
use app\v1\resources\v0\logic\Node;

/**
 * Class Instance
 * @package app\v1\cloud\v0\logic
 */
class Instance extends Base
{
    /**
     * 获取所有传输代理实例类型，同步消息
     * @param array $params 参数
     * @return array
     */
    public function getInstanceTypes(array $params): array
    {
        $platformUuid = $params['platform_uuid'];
        $regionUuid = $params['region_uuid'];
        $availableZone = $params['available_zone'] ?? '';
        $operate = 'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_AGENT_INSTANCE_TYPE';
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $awsHypervisor = xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_AWS'];
        $msg = json_encode([
            'vcenter_uuid' => $platformUuid,
            'host_uuid' => $regionUuid,
            'available_zone' => $availableZone
        ]);

        $mbResult = $this->service()->unifyCloudService(
            $nodeUuid,
            $awsHypervisor,
            $operate,
            $msg,
            true
        );
        return $mbResult['result'] && $mbResult['msg'] ? $mbResult['msg']['instance_type_info'] : [];
    }

    /**
     * 获取实例配置
     * @param string $instanceUuid 实例UUID
     * @return array
     */
    public function getInstanceConfigs(string $instanceUuid): array
    {
        $sql = "select vv.vcenter_uuid, vv.nickname, vm.vm_uuid, vm.vm_name, vh.host_uuid, 
            vh.host_name, vt.dir_path, vt.detail as vt_detail, vm.detail as vm_detail from vm_machine vm 
            join vm_vcenter vv on vm.vcenter_uuid = vv.vcenter_uuid 
            join vm_tree vt on vm.vm_uuid = vt.uuid and vt.display_mode = ?
            join vm_host vh on vm.host_uuid = vh.host_uuid
            where vm.vm_uuid = ?";
        $sqlParams = [xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'], $instanceUuid];
        $row = $this->dbSelect($sql, $sqlParams)[0];
        $vtConfig = $row['vt_detail'] ? json_decode($row['vt_detail'], true) : [];
        $vmConfig = $row['vm_detail'] ? ['available_zone' => $row['vm_detail']] : [];
        return [
            'platform_uuid' => $row['vcenter_uuid'],
            'platform_name' => $row['nickname'] ?: '',
            'instance_uuid' => $row['vm_uuid'],
            'instance_name' => $row['vm_name'],
            'region_uuid' => $row['host_uuid'], //使用vm_host保存区域信息
            'region_name' => $row['host_name'],
            'dir_path' => $row['dir_path'], //"/云平台/区域/可用区/实例id"
            'config' => array_merge($vtConfig, $vmConfig)
        ];
    }

    /**
     * 获取实例所有配置项，发同步消息
     * @param array $params 参数
     * @return array
     */
    public function getInstanceAllConfigs(array $params): array
    {
        $platformUuid = $params['platform_uuid'];
        $regionUuid = $params['region_uuid'];
        $availableZone = $params['available_zone'] ?? '';
        $architecture = $params['architecture'] ?? '';
        $osType = $params['os_type'] ?? '';
        $crossFlag = $params['cross_flag'];
        $typeParams = [
            'platform_uuid' => $platformUuid,
            'region_uuid' => $regionUuid,
            'available_zone' => $availableZone,
            'architecture' => $architecture,
            'os_type' => $osType,
            'cross_flag' => $crossFlag
        ];
        $networkParams = [
            'platform_uuid' => $platformUuid,
            'region_uuid' => $regionUuid,
            'instance_type' => ''
        ];

        return [
            'instance_types' => $this->getInstanceTypeConfigs($typeParams)['instance_types'],
            'network_list' => $this->getInstanceNetworkConfigs($networkParams)['network_list']
        ];
    }

    /**
     * 获取实例类型，发同步消息
     * @param array $params 参数
     * @return array
     */
    public function getInstanceTypeConfigs(array $params): array
    {
        $platformUuid = $params['platform_uuid'];
        $regionUuid = $params['region_uuid'];
        $availableZone = $params['available_zone'] ?? '';
        $architecture = $params['architecture'] ?? '';
        $osType = $params['os_type'] ?? '';
        if ($params['cross_flag']) {
            // 跨平台调用
            $operate = 'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_CROSS_PLATFORM_INSTANCE_TYPE';
        } else {
            $operate = 'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_INSTANCE_TYPE';
        }
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $hypervisorType = $this->getHypervisorTypeByPlatform($platformUuid);
        $msg = json_encode([
            'vcenter_uuid' => $platformUuid,
            'host_uuid' => $regionUuid,
            'available_zone' => $availableZone,
            'architecture' => $architecture,
            'os_type' => $osType,
        ]);

        $mbResult = $this->service()->unifyCloudService(
            $nodeUuid,
            $hypervisorType,
            $operate,
            $msg,
            true
        );
        $instanceTypes = $mbResult['result'] ? $mbResult['msg']['instance_type_info'] : [];

        return ['instance_types' => $instanceTypes];
    }

    /**
     * 获取实例网络配置，发同步消息
     * @param array $params 参数
     * @return array
     */
    public function getInstanceNetworkConfigs(array $params): array
    {
        $platformUuid = $params['platform_uuid'];
        $regionUuid = $params['region_uuid'];
        $instanceType = $params['instance_type'];
        // 获取hypervisor_type后发不同消息
        $hypervisorType = $this->getHypervisorTypeByPlatform($platformUuid);
        switch ($hypervisorType) {
            case xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_AWS']:
                $operate = 'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_NETWORK_WITH_INSTANCE_TYPE';
                break;
            case xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD']:
                $operate = 'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_NETWORK';
                break;
            default:
                $operate = '';
        }
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $msg = json_encode([
            'vcenter_uuid' => $platformUuid,
            'host_uuid' => $regionUuid,
            'instance_type_name' => $instanceType
        ]);

        $mbResult = $this->service()->unifyCloudService($nodeUuid, $hypervisorType, $operate, $msg, true);
        $networkList = $mbResult['result'] ? $mbResult['msg']['network_list'] : [];
        return ['network_list' => $networkList];
    }

    /**
     * 获取kms密钥信息，发同步消息
     * @param string $platformUuid 平台uuid
     * @param string $regionUuid   区域uuid
     * @return array
     */
    public function getKmsInfo(string $platformUuid, string $regionUuid): array
    {
        $operate = 'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_KMS_ENCRYPT_INFO';
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $awsHypervisor = xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_AWS'];
        $msg = json_encode(['vcenter_uuid' => $platformUuid, 'host_uuid' => $regionUuid]);

        $mbResult = $this->service()->unifyCloudService(
            $nodeUuid,
            $awsHypervisor,
            $operate,
            $msg,
            true
        );
        $kmsInfo = $mbResult['result'] ? $mbResult['msg']['kms_info'] : [];

        return ['kms_info' => $kmsInfo];
    }

    /**
     * 通过备份时间点获取实例/卷配置
     * @param int   $sourceHypervisor   源虚拟化类型
     * @param int   $targetHypervisor   目标虚拟化类型
     * @param int   $configType         类型：1实例，2卷
     * @param array $timepointUuids     时间点uuid
     * @return array
     */
    public function getTimepointsConfig(int $sourceHypervisor, int $targetHypervisor, int $configType, array $timepointUuids = []): array
    {
        if (!$timepointUuids) {
            //初始化使用
            return ['rows' => [], 'total' => 0];
        }

        if (!$sourceHypervisor) {
            // 整机的时间点，区分定时和实时
            return $this->getTimepointsConfigForCM($configType, $timepointUuids);
        }

        // 从后台获取公有云所需转换后的配置
        $hypervisor = $sourceHypervisor;

        //从时间点获取节点uuid
        $sql = "select real_node_uuid from bd_backup_timepoint where timepoint_uuid = ?";
        $nodeUuid = $this->dbSelect($sql, [$timepointUuids[0]])[0]['real_node_uuid'];
        // 获取时间点所在存储可用的节点uuid
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints($timepointUuids);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeUuid = $item['node_uuid'];
                break;
            }
        }
        $nodeUuid = $nodeUuid ?: Node::instance()->getLocalNodeUUID();

        $opName = 'VM_VCENTER_OP_GET_ADVANCE_RECOVERY_CONF';
        $mbMsg = array(
            'target_hypervisor_type' => $targetHypervisor,
            'recovery_timepoint_uuid_list' => $timepointUuids,
        );
        $mbMsg = json_encode($mbMsg);
        $mbResult = $this->service()->unifyCloudService($nodeUuid, $targetHypervisor, $opName, $mbMsg, true);
        $targetVmList = $mbResult['msg']['target_vm_list'] ?? [];
        $targetVmList = array_column($targetVmList, null, 'vm_uuid');

        $timepointUuidsStr = implode("','", $timepointUuids);
        $sql = "select distinct vbt.timepoint_uuid, vbt.vcenter_uuid, vbt.vm_config, vbt.vm_name, vbt.vm_uuid, bbt.timepoint,
            bbt.encrypted_flag, vbt.hypervisor_type, bbt.detail, bdtsi.virus_scan_status  
            from vm_backup_timepoint vbt 
            join bd_backup_timepoint bbt on vbt.timepoint_uuid = bbt.timepoint_uuid 
            left join  bd_backup_timepoint_safe_info bdtsi on vbt.timepoint_uuid = bdtsi.timepoint_uuid 
            where vbt.timepoint_uuid in ('" . $timepointUuidsStr . "') 
            order by bbt.timepoint desc";
        $data = $this->dbSelect($sql);
        $rows = [];
        foreach ($data as $d) {
            $vmConfig = json_decode($d['vm_config'], true);
            $bbtDetail = json_decode($d['detail'], true);
            if (is_array($vmConfig['networks'][0])) {
                $vmConfig['networks'] = $vmConfig['networks'][0];
            }
            $vmConfig['timepoint_uuid'] = $d['timepoint_uuid'];
            $vmConfig['platform_uuid'] = $d['vcenter_uuid'];
            // 从vm_host获取region_uuid
            $sqlRegion = "select host_uuid, detail from vm_host where vcenter_uuid = ? and host_name = ?";
            $dataRegion = $this->dbSelect($sqlRegion, [$d['vcenter_uuid'], $vmConfig['region']]);
            $vmConfig['region_uuid'] = $dataRegion[0]['host_uuid'];
            $hostDetail = json_decode($dataRegion[0]['detail'], true);

            $vmConfig['timepoint'] = $d['timepoint'];

            $vmConfig['vm_name'] = $d['vm_name'];
            // vm_name是否带有（实例ID）,有则去除
            $search = strstr($vmConfig['vm_name'], '(' . $vmConfig['instance_uuid'] . ')', true);
            if (false !== $search) {
                $vmConfig['vm_name'] = $search;
            }
            // 是否数据加密
            $vmConfig['encrypted_flag'] = $this->getVMDataEncrypt($bbtDetail);

            // 适配虚拟化恢复到aws
            $vmConfig['ami_id'] = $vmConfig['ami_id'] ?? '';
            $vmConfig['available_zone'] = $vmConfig['available_zone'] ?? '';
            $vmConfig['instance_type'] = $vmConfig['instance_type'] ?? '';
            $vmConfig['instance_uuid'] = $d['vm_uuid'];
            $vmConfig['networks'] = $vmConfig['networks'] ?? [
                'security' => [['security_name' => '', 'security_uuid' => '']],
                'subnet_uuid' => '',
                'subnet_ip_number' => '',
                'vpc_uuid' => '',
                'vpc_name' => '',
            ];
            $vmConfig['region'] = $vmConfig['region'] ?? '';
            // 区域、可用区中文名
            $userLang = xphp_get_user_info()['language'];
            $vmConfig['region_cn_name'] = '';
            $vmConfig['zone_cn_name'] = '';
            if ('zh-cn' == $userLang || 'zh-tw' == $userLang) {
                if ($hostDetail['cn_name']) {
                    $vmConfig['region_cn_name'] = $hostDetail['cn_name'];
                }
            }else{
                //英文版
                if ($hostDetail['en_name']) {
                    $vmConfig['region_cn_name'] = $hostDetail['en_name'];
                }
            }   
            foreach ($hostDetail['available'] as $az) {
                if ($az['zone'] == $vmConfig['available_zone'] && $az['zone_cn_name']) {
                    $vmConfig['zone_cn_name'] = $az['zone_cn_name'];
                    break;
                }
            }
            $vmConfig['region_uuid'] = $vmConfig['region_uuid'] ?? '';
            $vmConfig['hypervisor_type'] = $d['hypervisor_type'];
            $vmConfig['os_type'] = $vmConfig['os_type'] ?: $vmConfig['ami_platfrom'];
            $crossFlag = !in_array($d['hypervisor_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']);
            $vmConfig['cross_flag'] = $crossFlag;
            // 病毒扫描状态
            $vmConfig['virus_scan_status'] = $d['virus_scan_status'] ?? 0;

            $targetDiskList = $targetVmList ? $targetVmList[$vmConfig['vm_uuid']]['disk_list'] : [];
            foreach ($vmConfig['disk_list'] as $i => &$disk) {
                if (isset($disk['is_backup_or_recovery']) && !$disk['is_backup_or_recovery']) {
                    // 跳过未备份的磁盘
                    unset($vmConfig['disk_list'][$i]);
                }
                // 跨平台时获取磁盘id，根据虚拟化类型取值会有不同
                $vmDiskUuid = $disk['uuid'] ?: '';
                $vmDiskUuid = $disk['disk_uuid'] ?: $vmDiskUuid;
                $vmDiskUuid = $disk['volume_id'] ?: $vmDiskUuid;
                $targetDisk = $targetDiskList ? $targetDiskList[$i] : [];
                $disk['timepoint_uuid'] = $vmConfig['timepoint_uuid'];
                $disk['instance_uuid'] = $vmConfig['instance_uuid'];
                $disk['instance_name'] = $vmConfig['vm_name'];
                $diskSize = $targetDisk['virtual_size'] ?? $disk['totalSize'];
                $rootDiskFlag = $targetDisk['is_bootable'] ?? ($disk['root_disk_flag'] ?? $disk['is_bootable']);
                // 恢复到华为云，若根卷小于40GB则转为40GB大小，若数据卷小于10GB则转为10GB大小
                if (xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD'] == $targetHypervisor) {
                    if ($rootDiskFlag && $diskSize < 42949672960) {
                        $diskSize = 42949672960;
                    }
                    if (!$rootDiskFlag && $diskSize < 10737418240) {
                        $diskSize = 10737418240;
                    }
                }
                $disk['totalSize'] = $diskSize;
                $disk['totalSizeDes'] = v1_calsize(intval($diskSize), true);
                $disk['region'] = $vmConfig['region'];
                $disk['region_cn_name'] = $vmConfig['region_cn_name'];
                $disk['region_uuid'] = $vmConfig['region_uuid'] ?? '';
                $disk['available_zone'] = $vmConfig['available_zone'];
                $disk['zone_cn_name'] = $vmConfig['zone_cn_name'];
                $disk['vol_new_name'] = $vmConfig['vm_name'] . date('YmdHis', strtotime($vmConfig['timepoint'])) . '_' . $i;
                $disk['uuid'] = $targetDisk['disk_key'] ?? $vmDiskUuid; // 适配虚拟化恢复到aws
                $disk['cross_flag'] = $crossFlag;
                $disk['encrypted_flag'] = $vmConfig['encrypted_flag'];
                $disk['root_disk_flag'] = $rootDiskFlag;
                $disk['disk_delete_on_termination'] = intval($vmConfig['disk_delete_on_termination']) ?? 0;
                if (in_array($sourceHypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware']) && !$targetDisk) {
                    // vmware未获取到target_vm_list时取第一个为根磁盘
                    $disk['root_disk_flag'] = $i == 0;
                }
            }

            if (1 == $configType) {
                $rows[] = $vmConfig;
            } else {
                $rows = array_merge($rows, $vmConfig['disk_list']);
            }
        }
        return ['rows' => $rows, 'total' => count($rows)];
    }

    /**
     * 通过整机备份时间点获取实例/卷配置
     * @param int   $configType         类型：1实例，2卷
     * @param array $timepointUuids     时间点uuid
     * @return array
     */
    public function getTimepointsConfigForCM(int $configType, array $timepointUuids = []): array
    {
        // 整机的时间点，区分定时和实时
        $sql = "select module_type from bd_backup_timepoint where timepoint_uuid = ? limit 1";
        $data = $this->dbSelect($sql, [$timepointUuids[0]])[0];
        $cdpFlag = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'] == $data['module_type'];

        $targetVmList = [];
        foreach ($timepointUuids as $k => $timepointUuid) {
            $mbResultMsg = RecoveryJobController::instance()->convertPoints($timepointUuid);
            if (!$mbResultMsg || empty($mbResultMsg['disk_list'])) {
                //获取后台消息失败
                return ["flag" => false, "errorCode" => 1, "errorMsg" => ''];
            }
            $targetVmList[$k] = $mbResultMsg;
            $targetVmList[$k]['timepoint_uuid'] = $timepointUuid;
        }
        $targetVmList = array_column($targetVmList, null, 'timepoint_uuid');

        $timepointUuidsStr = implode("','", $timepointUuids);
        if ($cdpFlag) {
            $sql = "select distinct cvba.timepoint_uuid, cvba.master_agent_detail, ba.ip as agent_ip, ba.hostname as os_name, ba.agent_uuid, ba.os_type,
                bbt.timepoint, bbt.encrypted_flag, bbt.detail, bdtsi.virus_scan_status  
                from cdp_vol_backup_agent cvba 
                join bd_agent ba on cvba.master_agent_uuid = ba.agent_uuid 
                join bd_backup_timepoint bbt on cvba.timepoint_uuid = bbt.timepoint_uuid 
                left join  bd_backup_timepoint_safe_info bdtsi on cvba.timepoint_uuid = bdtsi.timepoint_uuid 
                where cvba.timepoint_uuid in ('" . $timepointUuidsStr . "') 
                order by bbt.timepoint desc";
        } else {
            $sql = "select distinct obt.timepoint_uuid, obt.os_config, obt.agent_ip, obt.os_name, obt.agent_uuid, obt.os_type,
                bbt.timepoint, bbt.encrypted_flag, bbt.detail, bdtsi.virus_scan_status  
                from os_backup_timepoint obt 
                join bd_backup_timepoint bbt on obt.timepoint_uuid = bbt.timepoint_uuid 
                left join  bd_backup_timepoint_safe_info bdtsi on obt.timepoint_uuid = bdtsi.timepoint_uuid 
                where obt.timepoint_uuid in ('" . $timepointUuidsStr . "') 
                order by bbt.timepoint desc";
        }
        $data = $this->dbSelect($sql);
        $rows = [];
        $osTypeDes = xphp_get_config('vm_config', 'BdMiddleOsTypeV2Des');
        foreach ($data as $d) {
            $vmConfig = [];
            $bbtDetail = json_decode($d['detail'], true);
            $timepointConfig = $cdpFlag ? json_decode($d['master_agent_detail'], true) : json_decode($d['os_config'], true);
            $vmConfig['boot_mode'] = intval($timepointConfig['system_boot_type']);
            $vmConfig['timepoint_uuid'] = $d['timepoint_uuid'];
            $vmConfig['platform_uuid'] = '';
            $vmConfig['region_uuid'] = '';
            $vmConfig['timepoint'] = $d['timepoint'];
            $vmConfig['vm_name'] = $d['os_name'] . "({$d['agent_ip']})";
            $vmConfig['encrypted_flag'] = $this->getVMDataEncrypt($bbtDetail);
            $vmConfig['ami_id'] = '';
            $vmConfig['available_zone'] = '';
            $vmConfig['instance_type'] = '';
            $vmConfig['instance_uuid'] = $d['timepoint_uuid']; // 这里不能为空，用时间点uuid代替
            $vmConfig['networks'] = [
                'security' => [['security_name' => '', 'security_uuid' => '']],
                'subnet_uuid' => '',
                'subnet_ip_number' => '',
                'vpc_uuid' => '',
                'vpc_name' => '',
            ];
            $vmConfig['region'] = '';
            // 区域、可用区中文名
            $vmConfig['region_cn_name'] = '';
            $vmConfig['zone_cn_name'] = '';
            $vmConfig['hypervisor_type'] = 0;
            $osType = $d['os_type'];
            if (is_numeric($osType)) {
                // 如果是枚举值转为字符串
                if ($osType == 0) {
                    $osType = null;
                } else {
                    $osType = $osTypeDes[$osType];
                }
            }
            $vmConfig['os_type'] = $osType;
            $vmConfig['cross_flag'] = true;
            // 病毒扫描状态
            $vmConfig['virus_scan_status'] = $d['virus_scan_status'] ?? 0;
            $vmConfig['cpu_arch'] = $targetVmList ? $targetVmList[$vmConfig['timepoint_uuid']]['cpu_arch'] : 0;
            $vmConfig['os_version'] = $targetVmList ? $targetVmList[$vmConfig['timepoint_uuid']]['os_version'] : 0;

            $targetDiskList = $targetVmList ? $targetVmList[$vmConfig['timepoint_uuid']]['disk_list'] : [];
            $vmConfig['disk_list'] = $targetDiskList;
            foreach ($vmConfig['disk_list'] as $i => &$disk) {
                if (isset($disk['is_backup_or_recovery']) && !$disk['is_backup_or_recovery']) {
                    // 跳过未备份的磁盘
                    unset($vmConfig['disk_list'][$i]);
                }
                $disk['totalSize'] = $disk['virtual_size'];
                $disk['timepoint_uuid'] = $vmConfig['timepoint_uuid'];
                $disk['instance_uuid'] = $vmConfig['instance_uuid'];
                $disk['instance_name'] = $vmConfig['vm_name'];
                $disk['totalSizeDes'] = v1_calsize(intval($disk['virtual_size']), true);
                $disk['region'] = $vmConfig['region'];
                $disk['region_cn_name'] = $vmConfig['region_cn_name'];
                $disk['region_uuid'] = $vmConfig['region_uuid'];
                $disk['available_zone'] = $vmConfig['available_zone'];
                $disk['zone_cn_name'] = $vmConfig['zone_cn_name'];
                $disk['vol_new_name'] = $vmConfig['vm_name'] . date('YmdHis', strtotime($vmConfig['timepoint'])) . '_' . $i;
                $disk['uuid'] = $disk['disk_key'];
                $disk['cross_flag'] = true;
                $disk['encrypted_flag'] = $vmConfig['encrypted_flag'];
                $disk['root_disk_flag'] = $disk['is_bootable'];
                $disk['disk_delete_on_termination'] = intval($vmConfig['disk_delete_on_termination']) ?? 0;
            }

            if (1 == $configType) {
                $rows[] = $vmConfig;
            } else {
                $rows = array_merge($rows, $vmConfig['disk_list']);
            }
        }
        return ['rows' => $rows, 'total' => count($rows)];
    }

    /**
     * 获取某个实例的磁盘列表
     * @param array $params 参数
     * @return array
     */
    public function getInstanceDiskList(array $params): array
    {
        $instanceUuid = $params['instance_uuid'];
        $platformUuid = $params['platform_uuid'];
        $sql = "select detail from vm_tree where vcenter_uuid = ? and uuid = ?";
        $data = $this->dbSelect($sql, array($platformUuid, $instanceUuid));
        $info = ['annotation' => '', 'disk_list' => []];
        if (!$data) {
            return $info;
        }
        $detail = json_decode($data[0]['detail'], true);
        foreach ($detail['disk_list'] as $v) {
            $info['disk_list'][] = [
                'disk_datastore_uuid' => '',
                'disk_name' => $v['dev_name'],
                'disk_uuid' => $v['volume_id']
            ];
        }
        return $info;
    }

    /**
     * 获取区域下的卷类型列表
     * @param array $params 参数
     * @return array
     */
    public function getVolumeTypeList(array $params): array
    {
        $operate = 'VM_VCENTER_OP_QUERY_PUBLIC_CLOUD_AVAILABE_VOLUME_TYPE';
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $msg = json_encode([
            'region' => $params['region'],
            'root_disk_size' => $params['root_disk_size'],
            'vcenter_uuid' => $params['platform_uuid'] ?? '',
            'host_uuid' => $params['region_uuid'] ?? '',
            'available_zone' => $params['available_zone'] ?? ''
        ]);
        $mbResult = $this->service()->unifyCloudService(
            $nodeUuid,
            intval($params['hypervisor_type']),
            $operate,
            $msg,
            true
        );
        $rows = $mbResult['result'] ? $mbResult['msg']['volume_type'] : [];
        return ['volume_type_list' => $rows];
    }

    /**
     * 获取时间点是否加密密码
     * @param array $config 时间点详情
     * @return bool
     */
    private function getVMDataEncrypt(array $config): bool
    {
        if (empty($config)) {
            return false;
        }
        if (!empty($config['password']) && $config['password_auto_flag'] == 2) {
            return true;
        }

        return false;
    }

    /**
     * 获取平台的虚拟化类型
     * @param string $platformUuid 平台uuid
     * @return int
     */
    private function getHypervisorTypeByPlatform(string $platformUuid): int
    {
        $sql = 'select hypervisor_type from vm_vcenter where vcenter_uuid = ?';
        $data = $this->dbSelect($sql, [$platformUuid]);
        return intval($data[0]['hypervisor_type']);
    }
}
