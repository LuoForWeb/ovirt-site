<?php

namespace app\v1\cloud\v0\logic;

use app\v1\backupData\v0\logic\DataManage;
use app\v1\common\logic\Backup as Backup;
use app\v1\common\logic\Recover;
use app\v1\opcode\PfOpcode;
use app\v1\vm\v0\logic\VmJobController as VmJobHandler;
use xphp\BLLHandler;
use app\v1\common\logic\Unification;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Index as ResourceHandler;
use app\v1\user\v0\logic\User as UserHandler;
use app\v1\resources\v0\logic\Storage as StorageHandler;

/**
 * Class CloudRecover
 * @package app\v1\cloud\v0\logic
 */
class CloudRecover extends Recover
{
    /**
     * 获取存储列表
     * @return array
     */
    public function getStorageList(): array
    {
        $sql = "select distinct bsr.mount_flag, bsr.node_uuid, bsr.storage_nickname, bsr.storage_uuid, bsr.storage_type, 
                bsr.total_size, bsr .free_size, bsr.status, bsr.error_code 
                from bd_storage_resource bsr 
                left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ? 
                where bsr.status = 1 and bsr.mount_flag = 1 
                and bsr.error_code = 0 and bsr.lan_free_flag = 2 and bsr.use_mode = ? and bsr.storage_type not in (8) ";
        $sqlParams = array(
            xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'],
            xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['BACKUP']
        );
        // 获取分配资源的权限
        if (v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'], 'bsr.storage_uuid');
            $sql .= " and $resourceUuidSql";
        }
        $data = $this->dbSelect($sql, $sqlParams);

        $info = array();
        $nodeHandler = Node::instance();
        $userHandler = UserHandler::instance();
        $storageHandler = StorageHandler::instance();
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty(xphp_get_user_info()['tenantuuid'])) {
            $resourceHandler = ResourceHandler::instance();
            $resourceInfo = $resourceHandler->pGetUserAllResource(
                xphp_get_user_info()['userUuid'],
                xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']
            );
            if (!empty($resourceInfo)) {
                foreach ($resourceInfo as $r) {
                    $resourceList[] = $r['resource_uuid'];
                }
            }

            //获取用户配额
        }
        foreach ($data as $d) {
            //如果是租户内部检查是否有该资源
            if (!empty(xphp_get_user_info()['tenantuuid']) && !in_array($d['storage_uuid'], $resourceList)) {
                continue;
            }

            $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
            $storageStatus = $storageHandler->getStorageStatus(
                $nodeAllStatus,
                intval($d['status']),
                intval($d['mount_flag'])
            );
            if ($storageStatus != xphp_get_config('resource', 'STORAGE_STATUS')['ONLINE']) {
                continue;
            }
            $name = '';
            $storageDes = $d['storage_nickname'];
            if (empty(xphp_get_user_info()['tenantuuid'])) {
                $storageDes .= '(' . $storageHandler->getStorageTypeDes($d['storage_type']) . ', '
                    . xphp_get_lang('UI_STORAGE_TOTAL_SIZE') . ':' . v1_calsize($d['total_size'], true) .
                    ', ' . xphp_get_lang('WEB_PLATFORM_DC_AVAILABLE_SPACE') . ':' . v1_calsize($d['free_size'], true) . ')';
            } else {
                $quotaInfo = $userHandler->getUserQuotaInfo();
                if (!empty($quotaInfo['des'])) {
                    $storageDes .= '(' . $storageHandler->getStorageTypeDes($d['storage_type']) . ', '
                        . $quotaInfo['des'] . ')';
                }
            }
            $storageDes .= $name;

            $info[] = array(
                'uuid' => $d['storage_uuid'],
                'text' => $storageDes,
                'name' => $d['storage_nickname'],
                'type' => intval($d['storage_type']),
                'total_size' => intval($d['total_size']),
                'free_size' => intval($d['free_size'])
            );
        }

        return ['rows' => $info, 'total' => count($info)];
    }

    /**
     * 创建恢复任务
     * @param array $params 参数
     * @return string
     */
    public function createRecoverJob(array $params): string
    {
        //public params

        $taskName = htmlspecialchars_decode($params['job_name']);
        $strategyGroupUuid = $params['strategy_group_uuid'];
        $hypervisor = intval($params['recover_info']['hypervisor_type']);
        $this->paramsCheck($hypervisor);

        // 检查数据加密密码
        $this->checkCloudEncryptPass($params['recover_info']['instance_configs']);

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $recoveryPosition = xphp_get_config('task', 'RECOVERY_POSITION')['ORIGINAL'];
        $recoveryTimeType = intval($params['time_strategy']['time_type']);
        //组合传输策略（AWS不使用，给默认值）
        $transportStrategy = [
            'encrypt_flag' => v1_parse_bool_to_flag($params['transport_strategy']['encrypt']), // 加密传输
            'encrypt_method' => intval($params['transport_strategy']['encrypt_method']),
            'compress_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'speed_limit_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'max_speed' => 0,
            'network_uuid' => '', //传输网络
            'block_size' => 0,
            'strategy_group_uuid' => $strategyGroupUuid,
            'compress_method' => 0,
            'reconnect_times' => intval($params['transport_strategy']['reconnect_times']), // 重连次数
            'reconnect_interval' => intval($params['transport_strategy']['reconnect_interval']), // 重连间隔时间
        ];
        //组合时间策略
        $timeStrategyList = [];
        if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] != $recoveryTimeType) {
            //定时恢复，除strategy_type,start_time外都为默认值
            $timeStrategyList[] = [
                'strategy_type' => xphp_get_config('task', 'STRATEGY_TYPE')['ONCE'],
                'mode' => 1,
                'days' => '0000000',
                'start_time' => $params['time_strategy']['timing_time'],
                'roll_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
                'roll_interval' => 0,
                'end_time' => '',
                'global_id' => 0,
                'strategy_group_uuid' => ''
            ];
        }

        $bLLHandler = new BLLHandler();
        $pfMSg = $bLLHandler->pfCreateRecoveryTaskMessage(
            $taskName,
            $moduleType,
            $recoveryPosition,
            $recoveryTimeType,
            $timeStrategyList,
            $transportStrategy
        );
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['RECOVERY'];
        $pfMSg['transport_strategy']['encrypt_method'] = $params['transport_strategy']['encrypt']
            ? intval($params['transport_strategy']['encrypt_method']) : 0; // 加密传输算法
        $pfMSg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['ignore_resource_limiting_flag']);
        // 安全策略
        $pfMSg['safe_config_strategy'] = Backup::instance()->groupSafeConfigStrategy($params['safe_strategy']);

        //private params
        $notUse = 'notuse'; //AWS不使用的参数传'notuse'

        //操作码
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        //appliance
        $pfMSg['appliance_uuid'] = $params['transport_strategy']['appliance_uuid'];
        //跨平台恢复
        $pfMSg['cross_platform_appliance_uuid'] = $notUse;
        //恢复类型：1实例，2卷
        $pfMSg['recovery_level'] = $params['recover_info']['recover_type'];
        //传输模式
        $pfMSg['transport_priority'] = CloudBackup::instance()->getTransportMode($params['transport_strategy']['mode']);
        //优先快照恢复
        $prioritySnapshot = v1_parse_bool_to_flag($params['advanced_strategy']['priority_snapshot_flag']);
        //实例恢复信息
        $pfMSg['recovery_vm_uuids'] = $this->groupRebuildVMInfo($params);
        //卷恢复信息
//        $pfMSg['volume'] = $this->groupVolConfig($params['recover_info']['volume_configs']);
        //策略组UUID
        $pfMSg['strategy_group_uuid'] = $strategyGroupUuid;
        //限速策略
        $pfMSg['speed_limit_strategy'] = Backup::instance()->groupTaskSpeedGlobalList($params['speed_strategy']);
        //线程数量
        $pfMSg['thread_num'] = intval($params['advanced_strategy']['thread_num']);
        //备份系统节点IP
        $pfMSg['backup_server_ip'] = '';
        $pfMSg['transport_ip_segment'] = '';
        //任务详情
        $pfMSg['detail'] = json_encode([
            'snapshot_priority' => $prioritySnapshot,
            //传输代理公有ip配置
            'agent_public_ip' => $params['transport_strategy']['agent_public_ip'],
            // 传输代理网络配置
            'agent_network' => $params['transport_strategy']['agent_network'],
        ]);
        // 重试策略
        $pfMSg['retry_strategy'] = Backup::instance()->groupRetryStrategy($params['retry_strategy']);

        $nodeUuid = Node::instance()
            ->getNodeUUIDWithTimepointUUID($params['point_info']['points'][0]['timepoint_uuid']);
        // 获取时间点所在存储可用的节点uuid
        $timepointUuidList = array_column($params['point_info']['points'], 'timepoint_uuid');
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints($timepointUuidList);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeUuid = $item['node_uuid'];
                break;
            }
        }

        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbVMMsg($nodeUuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] == $recoveryTimeType) {
                $this->startRecoverJob($taskName);
            }
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    //todo:public方法

    //todo:protected方法

    //todo:private方法

    /**
     * 组合恢复实例和时间点,新名字信息 因为跨平台恢复也会使用
     * @param array $params 全部参数
     * @return array
     */
    public function groupRebuildVMInfo(array $params): array
    {
        $recoverInfo = $params['recover_info'];
        //获取卷所在实例uuid
        $volInstanceUuids = array_unique(array_column($recoverInfo['volume_configs'], 'instance_uuid'));
        $pointInfo = array();
        $i = 0;
        foreach ($recoverInfo['instance_configs'] as $instanceConfig) {
            //卷恢复时若未勾选该实例下的卷则不传该实例配置
            if (2 == $recoverInfo['recover_type'] && !in_array($instanceConfig['instance_uuid'], $volInstanceUuids)) {
                continue;
            }
            $pointInfo[] = array(
                'vm_uuid' => $instanceConfig['instance_uuid'],
                'vm_name' => $instanceConfig['vm_name'],
                'new_name' => $instanceConfig['instance_name'],
                'timepoint_uuid' => $instanceConfig['timepoint_uuid'],
                'destination_host_uuid' => $instanceConfig['region_uuid'],
                'destination_vcenter_uuid' => $recoverInfo['platform_uuid'],
                'auto_datastore_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
                'destination_datastore_name' => '',
                'start_vm_flag' => v1_parse_bool_to_flag($instanceConfig['start_flag']),
                'vm_config' => $this->groupVMConfig($params, $instanceConfig),
                'extension_info' => '',
                'dir_path' => !empty($params['points'][$instanceConfig['timepoint_uuid']]) ?
                    $params['points'][$instanceConfig['timepoint_uuid']] : ''
            );
            $i++;
        }
        return $pointInfo;
    }

    /**
     * 组合实例配置
     * @param array $params         全部参数
     * @param array $instanceConfig 单个实例配置
     * @return string
     */
    private function groupVMConfig(array $params, array $instanceConfig): string
    {
        //组合卷恢复配置
        //[{'src_disk_uuid':'', 'available_zone':'', 'auto_conf_flag':1, 'target_storage_uuid':'', 'disk_type':1 }]
        $diskList = [];
        $volConfigs = $params['recover_info']['volume_configs'];
        foreach ($volConfigs as $v) {
            if ($v['instance_uuid'] == $instanceConfig['instance_uuid']) {
                $diskList[] = [
                    'src_disk_uuid' => $v['volume_uuid'],
                    'disk_delete_on_termination' => $v['disk_delete_on_termination'],
                    'region' => $v['region'],
                    'available_zone' => $v['available_zone'] ?: $instanceConfig['available_zone'],
                    'auto_conf_flag' => 1,
                    'target_storage_uuid' => '',
                    'disk_type' => $v['volume_disk_type'],
                    'iops' => $v['disk_iops'],
                    'new_volume_name' => $v['vol_new_name'],
                    'disk_encrypt_flag' => $v['disk_encrypt_flag'],
                    'disk_encrypt_key' => $v['disk_encrypt_key'],
                    'disk_size' => intval($v['totalSize'])
                ];
            }
        }

        //恢复区域
        if (1 == $params['recover_info']['recover_type']) {
            $recoveryRegion = $instanceConfig['region'];
        } else {
            $recoveryRegion = $diskList[0]['region'];
        }
        // 恢复位置标志
        $recoveryPositionFlag = true;
        $hypervisorTypes = xphp_get_config('vm', 'VMHYPERVISORTYPE');
        switch ($params['recover_info']['hypervisor_type']) {
            case $hypervisorTypes['VM_HYPERVISOR_TYPE_AWS']:
                // AWS根据区域
                $recoveryPositionFlag = $recoveryRegion == $instanceConfig['ori_region'];
                break;
            case $hypervisorTypes['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD']:
                // 华为云根据可用区
                $recoveryPositionFlag = $instanceConfig['available_zone'] == $instanceConfig['ori_available_zone'];
                break;
        }

        $info = [
            //恢复位置，原区域或跨区域
            'recovery_position' => $recoveryPositionFlag
                ? xphp_get_config('task', 'RECOVERY_POSITION')['ORIGINAL']
                : xphp_get_config('task', 'RECOVERY_POSITION')['OTHER'],
            //恢复类型：1实例，2卷
            'recovery_level' => $params['recover_info']['recover_type'],
            //是否分配公有ip
            'is_public_ip' => $instanceConfig['is_public_ip'],
            //是否快照优先
            'snapshot_priority' => $params['advanced_strategy']['priority_snapshot_flag'],
            //恢复完成是否自动启动
            'start_vm_flag' => $instanceConfig['start_flag'],
            //传输代理实例类型
            'agent_type' => $params['transport_strategy']['appliance_type'],
            //恢复实例类型
            'instance_type' => $instanceConfig['instance_type'],
            //恢复目标区域
            'recovery_region' => $recoveryRegion,
            //恢复目标可用区
            'available_zone' => $instanceConfig['available_zone'],
            //恢复目标平台
            'recovery_vcenter' => $params['recover_info']['platform_uuid'],
            //AMI，原机时传原AMI，异机时传空
            'ami' => $instanceConfig['region'] == $instanceConfig['ori_region'] ? $instanceConfig['ami_id'] : '',
            //网络信息
            'network' => [
                'vpc_uuid' => $instanceConfig['vpc_uuid'],
                'subnet_uuid' => $instanceConfig['subnet_uuid'],
                'security' => $instanceConfig['security'],
            ],
            //卷信息
            'vm_disk_list' => $diskList,
            // cpu
            'cpu_arch' => $instanceConfig['cpu_arch'],
            //操作系统
            'os_type' => $instanceConfig['os_type'],
            'os_version' => $instanceConfig['os_version'],
            //公有ip计费信息
            'public_ip' => [
                'chagemode' => $instanceConfig['billing_type'],
                'size' => intval($instanceConfig['billing_value']),
            ],
            // 企业项目id
            'enterprise_project_id' => $instanceConfig['enterprise_project_id'],
            // 引导模式
            'boot_mode' => intval($instanceConfig['boot_mode']),
        ];
        return json_encode($info, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 组合卷配置
     * @param array $volConfig 所有卷配置
     * @return array
     */
    private function groupVolConfig(array $volConfig): array
    {
        $info = [];
        foreach ($volConfig as $item) {
            $info[] = [
                'volume_id' => $item['volume_uuid'],
                'available_zone' => $item['available_zone']
            ];
        }
        return $info;
    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName 任务名
     * @return bool
     */
    private function startRecoverJob(string $taskName): bool
    {
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type, vt.hypervisor_type
                from bd_task bt, vm_task vt where bt.task_uuid = vt.task_uuid and bt.task_name = ? 
                and bt.module_type = ? and bt.task_type = ? order by bt.id desc";
        $data = $this->dbSelect($sql, array(
            $taskName,
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('task', 'TASKTYPE')['RECOVERY']
        )
        );
        if (!$data) {
            return false;
        }

        //调用系统统一启动任务接口.不重新写
        $result = VmJobHandler::instance()->startJob($data[0]['task_uuid'], 1);
        //这里直接返回成功或失败 bool
        return $result[0];
    }

    /**
     * 校验数据加密密码正确性
     * @param array $vms 参数
     * @return string
     */
    private function checkCloudEncryptPass(array $vms): string
    {
        foreach ($vms as $vm) {
            //校验输入密码
            if ($vm['encrypt_flag']) {
                $sql = "select bbt.detail, vbt.vm_name from bd_backup_timepoint bbt, vm_backup_timepoint vbt 
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint_uuid = ? ";
                $data = $this->dbSelect($sql, array($vm['timepoint_uuid']));
                if (!empty($data)) {
                    $detail = json_decode($data[0]['detail'], true);
                    $oldPassword = $detail['password'];
                    $password = v1_pt_pass_encrypt(base64_decode($vm['encrypt_password']));
                    if ($oldPassword != $password) {
                        return $this->muOpResult(
                            false,
                            xphp_get_lang('UI_VM_DATABASE_ENCR_PWD'),
                            "'" . $data[0]['vm_name'] . "'" . xphp_get_lang('UI_PLATFORM_VERIFI_FAIL_REENTER'),
                            'warning'
                        );
                    }
                }
            }
        }
        return json_encode(['success' => true]);
    }
}