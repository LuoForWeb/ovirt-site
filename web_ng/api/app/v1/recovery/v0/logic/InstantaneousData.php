<?php

namespace app\v1\recovery\v0\logic;

use app\v1\common\logic\Base;
use app\v1\complete_machine_os\v0\logic\MachineOsRecover;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Storage as StorageHandler;
use app\v1\tenant\v0\logic\Index as TenantHandler;
use app\v1\user\v0\logic\User as UserHandler;
use app\v1\vm\v0\logic\VmPlatform;

/**
 * note          瞬时恢复快照点管理的逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 15:44
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class InstantaneousData extends Base
{
    /**
     * 创建瞬时恢复快照点
     * @param array $params 请求参数
     * @return array
     */
    public function createInstancePonit(array $params)
    {

        $msg = ['task_uuid' => $params['recovery_uuid']];
        $opName = 'ELITE_RECOVERY_INSTANT_OP_CREATE_SNAPSHOT_POINT'; // 1
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $msg
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 瞬时恢复快照点之左边的树结构 无代理
     * @param array $params 请求参数
     * @return array
     */
    public function instantanceTree(array $params)
    {
        if (!empty($params['instant_flag'])) {
            // 如果是选择恢复源的，需要合并对应的模块的一起返回，所以返回数组即可
            return [];
        }

        if ($params['point_type'] == 'agent') {
            // 整机的
            return $this->instantanceAgentTree($params);
        }
        //配置
        $moduleTypes = xphp_get_config('module', 'MODULE_TYPE');

        $nodeUuid = $params['node_uuid'];                   //节点UUID,为空的时候显示所有节点数据
        $manageFlag = $params['manage_flag'];               //备份数据管理标志,备份数据管理的树带有checkbox
        $dataFlag = $params['data_flag'];                    //备份数据标志
        $subModuleType = $params['sub_module_type'];        // 子模块类型
        $storageUuid = $params['storage_uuid'];                    //存储UUID
        $subtype = $params['subtype'] ?? 1;                    //虚拟化类型，1虚拟机2私有云3公有云
        $sql = 'select distinct birsp.storage_uuid, birsp.task_uuid, birsp.module_type,
    			birsp.user_uuid, vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_config, vbt.vm_name, 
                vbt.dir_path, vbt.hypervisor_type, bsr.node_uuid, bsr.storage_type,bu.user_name,
                (select task_name from bd_task where task_uuid = birsp.task_uuid) as task_name,
	            (select task_name from bd_history_task where task_uuid = birsp.task_uuid limit 1) as task_name2
                from bd_instant_recovery_snapshot_point birsp, 
                    vm_backup_timepoint vbt, 
                    bd_user bu,
                    bd_storage_resource bsr
                    left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ? 
                where birsp.depend_point_uuid = vbt.timepoint_uuid and
                      birsp.storage_uuid = bsr.storage_uuid and 
                      birsp.user_uuid = bu.user_uuid 
                  and birsp.module_type = ' . $moduleTypes['VM'];

        $vmTypeArr = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        if ($subtype == 3) {
            // 公有云
            $arr = implode(',', $vmTypeArr['publiccloud']);
            $sql .= " and birsp.hypervisor_type in ({$arr})";
        } elseif ($subtype == 2) {
            // 私有云
            $arr = implode(',', $vmTypeArr['openstack']);
            $sql .= " and birsp.hypervisor_type in ({$arr})";
        } else {
            // 虚拟机 not in openstack 和 publiccloud
            $arr = implode(',', array_merge($vmTypeArr['openstack'], $vmTypeArr['publiccloud']));
            $sql .= " and birsp.hypervisor_type not in ({$arr})";
        }
        $sqlParams = array(xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']);

        //租户管理员特殊处理数据显示
        $tenantHandler = (new TenantHandler());
        $userHandler = new UserHandler();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        $user = xphp_get_user_info();
        //获取租户管理员是否可以控制所有备份数据标志
        $allManageFlag = false;
        if (!empty($user['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($user['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        // 如果是有全局观察者权限，那么显示所有的数据
        if (
        !(in_array('global_read', $user['permissionArr'])
            || in_array('global_write', $user['permissionArr']))
        ) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($dataFlag && $tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser($user['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and birsp.user_uuid in ('" . $userListDes . "')";
            } else {
                // 查看权限
                if (!$this->isAdmin()) {
                    $authUser = $user['authUser']['awsprotect_look'] ?? [];
                    $useruuid = array_merge([$user['userUuid']], $authUser);
                    $useruuid = "('" . implode("','", $useruuid) . "')";
                    $sql .= ' and birsp.user_uuid in ' . $useruuid;
                }
            }
        }
        if (empty($user['tenantuuid'])) {
            // 不能看租户内部的资源
            $tenantUuids = (array)$this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids, 'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and birsp.user_uuid not in $tenantUuids ";
        } elseif (xphp_three_powers() && $this->isAdmin()) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        } else {
            // 关联管理用户判断 存储资源 - 查看
            $authKey = 'storage_manager_look';
            $authUser = $userInfo['authUser'][$authKey] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and (bsr.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
            } else {
                $userUuid = xphp_get_user_info()['userUuid'];
                $sql .= ' and (bsr.user_uuid = ? or mur.user_uuid = ?) ';
                $sqlParams = array_merge($sqlParams, array($userUuid, $userUuid));
            }
        }

        if ($nodeUuid) {
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= ' and bsr.node_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($nodeUuid));
        }

        $cloudTypesTmp = $cloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $privateTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'];

        // 备份数据获取
        $cloudTypes = "'" . implode("','", $cloudTypes) . "'";
        $privateTypes = "'" . implode("','", $privateTypes) . "'";
        if (xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'] == $subModuleType) {
            $sql .= ' and vbt.hypervisor_type in (' . $cloudTypes . ')';
        } elseif (xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'] == $subModuleType) {
            $sql .= ' and vbt.hypervisor_type in (' . $privateTypes . ')';
        } else {
            $sql .= ' and vbt.hypervisor_type not in (' . $cloudTypes . ')';
            $sql .= ' and vbt.hypervisor_type not in (' . $privateTypes . ')';
        }

        //按存储筛选
        if ($storageUuid) {
            $sql .= ' and birsp.storage_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }

        $sql .= " group by vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_name, birsp.task_uuid
            order by birsp.task_uuid, birsp.create_time desc, vbt.vm_name  ";

        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();

        //定义task vm timepoint
        $hypervisor = array();
        $task = array();
        $vm = array();
        $vmPlatformLogic = VmPlatform::instance();
        foreach ($data as $d) {
            $vmConfig = json_decode($d['vm_config'], true);

            $taskUuid = $d['task_uuid'];
            $taskCreateTime = '';
            //检查并添加虚拟化类型
            if (!in_array($d['hypervisor_type'], $hypervisor)) {
                $hypervisorType = intval($d['hypervisor_type']);
                $name = xphp_get_config('vm', 'VMHYPERVISORDES')[$hypervisorType];
                $node[] = array(
                    'id' => $d['hypervisor_type'],
                    'pId' => 0,
                    'name' => $name,
                    'title' => $name,
                    'open' => true,
                    'nocheck' => !$manageFlag,
                    'type' => -1,
                    'iconSkin' => $vmPlatformLogic->getHypervisorIcon($hypervisorType, false),
                    'platform_uuid' => $d['vcenter_uuid'],
                    'hypervisor_type' => $d['hypervisor_type'],
                );
                $hypervisor[] = $hypervisorType;
            }
            $hypervisorType = $d['hypervisor_type'];
            $cloudTimepointFlag = in_array($hypervisorType, $cloudTypesTmp); // 是否为公有云备份时间点
            $taskName = $d['task_name'] ? $d['task_name'] : $d['task_name2'];
            //检查并添加task
            if (!in_array($hypervisorType . $taskUuid, $task)) {
                $task[] = $hypervisorType . $taskUuid;
                $taskAvailable = in_array($taskUuid, $currentTaskUUID);
                $name = $taskAvailable
                    ? $taskName
                    : $taskName . '(' . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ')';

                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if (
                    $dataFlag && $tenantMangerFlag && $allManageFlag
                    && $d['user_uuid'] != $user['userUuid']
                ) {
                    $name .= '(' . $d['user_name'] . ')';
                }
                // 区分虚拟机和公有云的任务图标
                $icon = $cloudTimepointFlag ? './img/vm/AWS/aws-job.png' : './img/platform/flag.png';
                $node[] = array(
                    'id' => $hypervisorType . $taskUuid,
                    'pId' => $d['hypervisor_type'],
                    'name' => $name,
                    'open' => false,
                    'nocheck' => !$manageFlag,
                    'type' => 0,
                    'icon' => $icon,
                    'title' => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': ' . $taskCreateTime,
                    'platform_uuid' => $d['vcenter_uuid'],
                    'hypervisor_type' => $d['hypervisor_type'],
                    'isParent' => true,
                );
            }
            $vmUuid = $d['vm_uuid'];
            //检查并添加vm
            if (!in_array($hypervisorType . $vmUuid . $taskUuid, $vm)) {
                $vm[] = $hypervisorType . $vmUuid . $taskUuid;
                $vmName = $d['vm_name'];
                if (in_array($hypervisorType, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
                    $ipList = $vmConfig['network_list'][0]['ip_list'];
                    if (!empty($ipList)) {
                        $vmName .= '(' . $ipList[0]['ipaddr'] . ')';
                    }
                }
                // 区分虚拟机和实例图标
                $iconSkin = $cloudTimepointFlag ? 'vm_aws_vm' : 'vm';
                $icon = $cloudTimepointFlag ? './img/vm/AWS/aws-instance.png' : './img/vm/vm.png';
                $node[] = array(
                    'id' => $hypervisorType . $vmUuid . $taskUuid,
                    'pId' => $hypervisorType . $taskUuid,
                    'name' => rawurldecode($vmName),
                    'open' => false,
                    'nocheck' => !$manageFlag,
                    'type' => 1,
                    'icon' => $icon,
                    'iconSkin' => $iconSkin,
                    'title' => xphp_get_lang('WEB_PLATFORM_DC_VM_SRC_DIR_PATH') . ': ' . rawurldecode($d['dir_path']),
                    'platform_uuid' => $d['vcenter_uuid'],
                    'vm_uuid' => $d['vm_uuid'],
                    'task_uuid' => $taskUuid,
                    'node_uuid' => $d['node_uuid'],
                    "create_time" => $taskCreateTime,
                    'hypervisor_type' => $d['hypervisor_type'],
                    'isParent' => true,
                    'clickshow' => true,
                );
            }
        }

        if (!empty($params['instant_flag'])) {
            // 如果是选择恢复源的，需要合并对应的模块的一起返回，所以返回数组即可
            return $node;
        }
        return $this->muOpResult(true, '', '', '', 0, $node);
    }

    /**
     * 瞬时恢复快照点之左边的树结构 有代理
     * @param array $params 请求参数
     * @return array
     */
    private function instantanceAgentTree($params)
    {
        if (!empty($params['instant_flag'])) {
            // 如果是选择恢复源的，需要合并对应的模块的一起返回，所以返回数组即可
            return [];
        }
        //配置
        $moduleTypes = xphp_get_config('module', 'MODULE_TYPE');

        $nodeUuid = $params['node_uuid'];                   //节点UUID,为空的时候显示所有节点数据
        $manageFlag = $params['manage_flag'];               //备份数据管理标志,备份数据管理的树带有checkbox
        $dataFlag = $params['data_flag'];                    //备份数据标志
        $storageUuid = $params['storage_uuid'];                    //存储UUID
        $sql = 'select distinct birsp.storage_uuid, birsp.task_uuid, birsp.module_type,birsp.create_time,
    			birsp.user_uuid, vbt.agent_uuid, vbt.agent_ip, vbt.os_name, vbt.os_type, 
                vbt.dir_path, vbt.os_config, bsr.node_uuid, bsr.storage_type,bu.user_name,
                (select task_name from bd_task where task_uuid = birsp.task_uuid) as task_name,
	            (select task_name from bd_history_task where task_uuid = birsp.task_uuid limit 1) as task_name2
                from bd_instant_recovery_snapshot_point birsp, 
                    os_backup_timepoint vbt, 
                    bd_user bu,
                    bd_storage_resource bsr
                    left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ? 
                where birsp.depend_point_uuid = vbt.timepoint_uuid and
                      birsp.storage_uuid = bsr.storage_uuid and 
                      birsp.user_uuid = bu.user_uuid 
                  and birsp.module_type = ' . $moduleTypes['OS'];

        $sqlParams = array(xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']);

        //租户管理员特殊处理数据显示
        $tenantHandler = (new TenantHandler());
        $userHandler = new UserHandler();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        $user = xphp_get_user_info();
        //获取租户管理员是否可以控制所有备份数据标志
        $allManageFlag = false;
        if (!empty($user['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($user['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        // 如果是有全局观察者权限，那么显示所有的数据
        if (
        !(in_array('global_read', $user['permissionArr'])
            || in_array('global_write', $user['permissionArr']))
        ) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($dataFlag && $tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser($user['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sql .= " and birsp.user_uuid in ('" . $userListDes . "')";
            } else {
                // 查看权限
                if (!$this->isAdmin()) {
                    $authUser = $user['authUser']['awsprotect_look'] ?? [];
                    $useruuid = array_merge([$user['userUuid']], $authUser);
                    $useruuid = "('" . implode("','", $useruuid) . "')";
                    $sql .= ' and birsp.user_uuid in ' . $useruuid;
                }
            }
        }
        if (empty($user['tenantuuid'])) {
            // 不能看租户内部的资源
            $tenantUuids = (array)$this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids, 'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and birsp.user_uuid not in $tenantUuids ";
        } elseif (xphp_three_powers() && $this->isAdmin()) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        } else {
            // 关联管理用户判断 存储资源 - 查看
            $authKey = 'storage_manager_look';
            $authUser = $userInfo['authUser'][$authKey] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and (bsr.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
            } else {
                $userUuid = xphp_get_user_info()['userUuid'];
                $sql .= ' and (bsr.user_uuid = ? or mur.user_uuid = ?) ';
                $sqlParams = array_merge($sqlParams, array($userUuid, $userUuid));
            }
        }

        if ($nodeUuid) {
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= ' and bsr.node_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($nodeUuid));
        }

        //按存储筛选
        if ($storageUuid) {
            $sql .= ' and birsp.storage_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }

        $sql .= " group by vbt.agent_uuid, birsp.task_uuid
            order by birsp.task_uuid, birsp.create_time desc, vbt.os_name  ";

        $data = $this->dbSelect($sql, $sqlParams);

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $agentList = $this->getAllAgentList();

        //定义task vm timepoint
        $task = [];
        $info = [];
        $os = array();
        foreach ($data as $d) {
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'] ?: $d['task_name2'];

            if (!in_array($taskuuid, $task)) {
                $task[] = $taskuuid;
                //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskName .
                    ($taskAvailable ? '' : '(' . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ')');
                //第一层 任务名称
                $info[] = array(
                    'id' => $d['task_uuid'],
                    'pId' => 0,
                    'name' => $name,
                    'open' => false,
                    'nocheck' => !$manageFlag,
                    'type' => 0,
                    'icon' => './img/platform/flag.png',
                    'title' => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': ' . $d['create_time'],
                    'isParent' => true,
                );
            }
            if (!in_array($d['task_uuid'] . $d['agent_uuid'], $os)) {
                $os[] = $d['task_uuid'] . $d['agent_uuid'];
                //获取系统类型
                $ostype = $d['os_type'];
                $osTypeName = $ostype == 1 ? 'Windows' : 'Linux';
                //第二层 任务系统类型
                $info[] = array(
                    'id' => $d['task_uuid'] . $d['agent_uuid'],
                    'pId' => $d['task_uuid'],
                    'name' => (new MachineOsRecover())->getAgentNameByList(
                        $agentList,
                        $d['agent_uuid'],
                        $d['os_name'],
                        $d['agent_ip']
                    ),
                    'open' => false,
                    'nocheck' => !$manageFlag,
                    'type' => 1,
                    'icon' => './img/os/' . $osTypeName . '.png',
                    'iconSkin' => $osTypeName . 'logo',
                    'title' => xphp_get_lang('WEB_PLATFORM_DC_VM_SRC_DIR_PATH') . ': ' . $d['dir_path'],
                    'taskuuid' => $d['task_uuid'],
                    'agentuuid' => $d['agent_uuid'],
                    'nodeuuid' => $d['node_uuid'],
                    "createtime" => $d['create_time'],
                    'osType' => $d['os_type'],
                    'isParent' => true,
                    'clickshow' => true,
                );
            }
        }
        if (!empty($params['instant_flag'])) {
            // 如果是选择恢复源的，需要合并对应的模块的一起返回，所以返回数组即可
            return $info;
        }
        return $this->muOpResult(true, '', '', '', 0, $info);
    }

    /**
     * 瞬时恢复快照点之具体的时间点列表 无代理
     * @param array $params 请求参数
     * @return array
     */
    public function instantancePoints(array $params)
    {

        if (!empty($params['instant_flag'])) {
            // 如果是选择恢复源的，需要合并对应的模块的一起返回，所以返回数组即可
            return [];
        }

        if ($params['point_type'] == 'agent') {
            // 整机的
            return $this->instantanceAgentPoints($params);
        }
        //异步获取使用
        $taskUuid = $params['task_uuid'];
        $hypervisor = $params['hypervisor_type'];
        $vmUuid = $params['vm_uuid'];
        $manageFlag = $params['manage_flag'];       //备份数据管理标志,备份数据管理的树带有checkbox,异步获取和筛选都为true
        $nodeUuid = $params['node_uuid'];

        //筛选条件使用
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $accurateFlag = $params['accurate_flag']; //高级搜索标志

        //表格使用
        $start = intval($params['offset']);
        $length = intval($params['limit']);
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $platformUuid = $params['platform_uuid'];
        $storageUuid = $params['storage_uuid'];
        $sortArr = array(
            'timepoint' => 'bbt.create_time',
            'data_size' => 'bbt.total_size',
            'write_size' => 'bbt.actual_size',
            'storage' => 'bsr.storage_nickname',
            'user_name' => 'bu.user_name'
        );

        $node = array();
        $sqlPoint = "select distinct bbt.machine_config detail, bbt.storage_uuid,
                bbt.snapshot_point_uuid timepoint_uuid,bbt.create_time timepoint, bbt.depend_point_uuid,
            bbt.task_uuid,bbt.total_size, bbt.actual_size write_size,vbt.vm_uuid, 
            (SELECT vbt2.vm_name FROM vm_backup_timepoint vbt2
                JOIN bd_instant_recovery_snapshot_point bbt2 ON vbt2.timepoint_uuid = bbt2.snapshot_point_uuid 
                 WHERE bbt2.task_uuid = '{$taskUuid}' AND vbt2.vcenter_uuid = '{$platformUuid}'
                   AND vbt2.vm_uuid = vbt.vm_uuid
            ORDER BY vbt2.vm_timepoint_id DESC LIMIT 1) as current_vm_name, 
            vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.hypervisor_type, 
            bsr.node_uuid, bsr.storage_uuid, bsr.status, bsr.storage_type, bu.user_name 
                from bd_instant_recovery_snapshot_point bbt left join bd_user bu on bbt.user_uuid = bu.user_uuid, 
                     vm_backup_timepoint vbt, 
                     bd_storage_resource bsr 
                     left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ?   
                where bbt.depend_point_uuid = vbt.timepoint_uuid 
                       and bbt.storage_uuid = bsr.storage_uuid 
	                   and bbt.module_type = 2 ";
        $sqlCount = "select count(distinct bbt.id) as total
                    from bd_instant_recovery_snapshot_point bbt, vm_backup_timepoint vbt,
                    bd_storage_resource bsr 
                    left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ? 
                    where bbt.depend_point_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid 
                    and bbt.module_type = 2 ";
        $sqlParams = [
            xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'],
        ];

        if (!empty($nodeUuid)) {
            $sqlPoint .= ' and bsr.node_uuid = ?';
            $sqlCount .= ' and bsr.node_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($nodeUuid));
        }
        if ($taskUuid) {
            $sqlPoint .= ' and bbt.task_uuid = ?';
            $sqlCount .= ' and bbt.task_uuid = ?';
            $sqlParams = array_merge($sqlParams, [$taskUuid]);
        }
        if ($platformUuid) {
            $sqlPoint .= ' and vbt.vcenter_uuid = ?';
            $sqlCount .= ' and vbt.vcenter_uuid = ?';
            $sqlParams = array_merge($sqlParams, [$platformUuid]);
        }
        if ($vmUuid) {
            $sqlPoint .= ' and vbt.vm_uuid = ?';
            $sqlCount .= ' and vbt.vm_uuid = ?';
            $sqlParams = array_merge($sqlParams, [$vmUuid]);
        }
        if ($hypervisor) {
            $sqlPoint .= ' and bbt.hypervisor_type = ?';
            $sqlCount .= ' and bbt.hypervisor_type = ?';
            $sqlParams = array_merge($sqlParams, [$hypervisor]);
        }

        if ($storageUuid) {
            $sqlPoint .= ' and bsr.storage_uuid = ?';
            $sqlCount .= ' and bsr.storage_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        if ($accurateFlag) {
            //如果填了开始时间范围查询
            if ($startTime && $endTime) {
                $sqlPoint .= " and bbt.create_time between '" . $startTime . "' and '" . $endTime . "' ";
                $sqlCount .= " and bbt.create_time between '" . $startTime . "' and '" . $endTime . "' ";
            }
        }
        //公有云细粒度恢复屏蔽云存储的数据
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $publicCloudFlag = $hypervisor ? in_array($hypervisor, $publicCloudTypes) :
            xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'] == $params['sub_moduletype'];

        // 备份数据处只获取自己模块且是备份任务的
        if ($publicCloudFlag) {
            $sqlPoint .= ' and vbt.hypervisor_type in (' . implode(',', $publicCloudTypes) . ')';
            $sqlCount .= ' and vbt.hypervisor_type in (' . implode(',', $publicCloudTypes) . ')';
        }

        // 查看权限
        $userInfo = xphp_get_user_info();
        //租户管理员特殊处理数据显示
        $tenantHandler = new TenantHandler();
        $userHandler = new UserHandler();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        $allManageFlag = false;
        if (!empty($userInfo['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($userInfo['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }
        // 如果是有全局观察者权限，那么显示所有的数据
        if (
        !(in_array('global_read', $userInfo['permissionArr'])
            || in_array('global_write', $userInfo['permissionArr']))
        ) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($manageFlag && $tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser($userInfo['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sqlPoint .= " and bbt.user_uuid in ('" . $userListDes . "')";
                $sqlCount .= " and bbt.user_uuid in ('" . $userListDes . "')";
            } else {
                // 查看权限
                if (!$this->isAdmin()) {
                    $authUser = $userInfo['authUser']['awsprotect_look'] ?? [];
                    $useruuid = array_merge([$userInfo['userUuid']], $authUser);
                    $useruuid = "('" . implode("','", $useruuid) . "')";
                    $sqlPoint .= ' and bbt.user_uuid in ' . $useruuid;
                    $sqlCount .= ' and bbt.user_uuid in ' . $useruuid;
                }
            }
        }
        if (empty($userInfo['tenantuuid'])) {
            // 不能看租户内部的资源
            $tenantUuids = (array)$this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids, 'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sqlPoint .= " and bbt.user_uuid not in $tenantUuids ";
            $sqlCount .= " and bbt.user_uuid not in $tenantUuids ";
        } elseif (xphp_three_powers() && $this->isAdmin()) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        } else {
            // 关联管理用户判断 存储资源 - 查看
            $authKey = 'storage_manager_look';
            $authUser = $userInfo['authUser'][$authKey] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuidArr = array_merge([$userInfo['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sqlPoint .= " and (bsr.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
                $sqlCount .= " and (bsr.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
            } else {
                $userUuid = $userInfo['userUuid'];
                $sqlPoint .= ' and (bsr.user_uuid = ? or mur.user_uuid = ?) ';
                $sqlCount .= ' and (bsr.user_uuid = ? or mur.user_uuid = ?) ';
                $sqlParams = array_merge($sqlParams, array($userUuid, $userUuid));
            }
        }

        // 查看权限
        if ($userInfo['userType'] != 3) {
            $authUser = $user['authUser']['awsprotect_look'] ?? [];
            $useruuid = array_merge([$userInfo['userUuid']], $authUser);
            $useruuid = "('" . implode("',", $useruuid) . "')";
            $sqlPoint .= ' and bbt.user_uuid in ' . $useruuid;
            $sqlCount .= ' and bbt.user_uuid in ' . $useruuid;
        }

        $tableFlag = false; //是否表格查询
        $sqlCountParams = $sqlParams;
        if (
            isset($sortColumn) && isset($sortType) && isset($start) && isset($length) && empty($params['instant_flag'])
        ) {
            $tableFlag = true;
            $sqlPoint .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
            $sqlParams = array_merge($sqlParams, array($start, $length));
        } else {
            //时间点树
            $sqlPoint .= " order by bbt.task_uuid, vbt.vm_uuid, bbt.create_time";
        }

        $pointData = $this->dbSelect($sqlPoint, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $storageHandler = new StorageHandler();

        if ($tableFlag) {
            $records = ['rows' => [], 'total' => 0];
            //表格获取分页数据
            $i = 1;
            foreach ($pointData as $d) {
                $remark = '';   //备注
                $timepointDiv = '<span>' . ($d['timepoint']) . '</span>' .
                    '<span class="table-body__i">' . $remark . '</span>';
                $records['rows'][] = array(
                    'backup_mode' => 1,
                    'hypervisor_type' => $d['hypervisor_type'],
                    'platform_uuid' => $platformUuid,
                    'timepoint_uuid' => $d['timepoint_uuid'],
                    'storage_uuid' => $d['storage_uuid'],
                    'storage_type' => $d['storage_type'],
                    'no_html' => $i++,
                    'timepoint_html' => $timepointDiv,
                    'data_size' => v1_calsize($d['total_size'], true),
                    'write_size' => v1_calsize($d['write_size'], true),
                    'storage_des' => $storageHandler->getStorageName($d['storage_uuid']),
                    'user_name' => $d['user_name'] ?: '--',
                    'detail' => array(
                        'vm_uuid' => $vmUuid,
                        'task_uuid' => $taskUuid,
                        'hypervisor_type' => $d['hypervisor_type']
                    ),
                );
            }
            $records['total'] = $dataCount[0]['total'];
            return $records;
        }
        $timepoint = array();
        foreach ($pointData as $point) {
            if (!$point) {
                continue;
            }

            $taskUuid = $point['task_uuid'];
            $vmUuid = $point['vm_uuid'];
            $timepointUuid = $point['timepoint_uuid'];
            $taskCreateTimeIn = $this->parseDate($point['task_create_time']);

            //检查并添加完备点
            if (!in_array($timepointUuid, $timepoint)) {
                $name = ($point['timepoint']) . ' (' . xphp_get_lang('UI_INSTANT_POINTS') . ')';
                $timepoint[] = $timepointUuid;
                $vmName = $point['current_vm_name'] ?: $point['vm_name'];
                $config = json_decode($point['detail'], true);
                $node[] = array(
                    'id' => $timepointUuid,
                    'pId' => $point['hypervisor_type'] . $vmUuid . $taskUuid,
                    'name' => $name,
                    'old_name' => $name,
                    'vm_uuid' => $point['vm_uuid'],
                    'vm_name' => rawurldecode($vmName),
                    'point_name' => $point['timepoint'],
                    'platform_uuid' => $point['vcenter_uuid'],
                    'timepoint_uuid' => $timepointUuid,
                    "create_time" => $taskCreateTimeIn,
                    'hypervisor_type' => $point['hypervisor_type'],
                    'node_uuid' => $point['real_node_uuid'] ?: $point['node_uuid'],
                    'storage_uuid' => $point['storage_uuid'],
                    'storage_type' => $point['storage_type'],
                    'task_uuid' => $taskUuid,
                    'path' => htmlspecialchars($point['dir_path']),
                    'icon' => $this->getTimepointIcon(1),
                    'title' => htmlspecialchars(rawurldecode(
                        xphp_get_lang('WEB_PLATFORM_DC_VM_SRC_DIR_PATH') . ': ' . $point['dir_path']
                    )),
                    'config' => $config,
                    'chkDisabled' => false,
                    'virus_scan_status' => 0, // 备份点健康状态 0 未扫描 1扫描中 2健康 3感染
                );
            }
        }
        if (!empty($params['instant_flag'])) {
            // 如果是选择恢复源的，需要合并对应的模块的一起返回，所以返回数组即可
            return $node;
        }
        return ['rows' => $node, 'total' => $dataCount[0]['total']];
    }

    /**
     * 瞬时恢复快照点之具体的时间点列表 有代理
     * @param array $params 请求参数
     * @return array
     */
    public function instantanceAgentPoints(array $params)
    {
        if (!empty($params['instant_flag'])) {
            // 如果是选择恢复源的，需要合并对应的模块的一起返回，所以返回数组即可
            return [];
        }

        //异步获取使用
        $taskUuid = $params['task_uuid'];
        $vmUuid = $params['agent_uuid'];
        $manageFlag = $params['manage_flag'];       //备份数据管理标志,备份数据管理的树带有checkbox,异步获取和筛选都为true
        $nodeUuid = $params['node_uuid'];

        //筛选条件使用
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $accurateFlag = $params['accurate_flag']; //高级搜索标志

        //表格使用
        $start = intval($params['offset']);
        $length = intval($params['limit']);
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $storageUuid = $params['storage_uuid'];
        $sortArr = array(
            'timepoint' => 'bbt.create_time',
            'data_size' => 'bbt.total_size',
            'write_size' => 'bbt.actual_size',
            'storage' => 'bsr.storage_nickname',
            'user_name' => 'bu.user_name'
        );

        $node = array();
        $sqlPoint = "select distinct bbt.machine_config detail, bbt.storage_uuid,
                bbt.snapshot_point_uuid timepoint_uuid,bbt.create_time timepoint, bbt.depend_point_uuid,
            bbt.task_uuid,bbt.total_size, bbt.actual_size write_size,vbt.agent_uuid,
            vbt.os_name, vbt.dir_path,vbt.os_type, vbt.agent_ip,vbt.os_config,
            bsr.node_uuid, bsr.storage_uuid, bsr.status, bsr.storage_type, bu.user_name 
                from bd_instant_recovery_snapshot_point bbt left join bd_user bu on bbt.user_uuid = bu.user_uuid, 
                     os_backup_timepoint vbt, 
                     bd_storage_resource bsr 
                     left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ?   
                where bbt.depend_point_uuid = vbt.timepoint_uuid 
                       and bbt.storage_uuid = bsr.storage_uuid 
	                   and bbt.module_type = 5 ";
        $sqlCount = "select count(distinct bbt.id) as total
                    from bd_instant_recovery_snapshot_point bbt, os_backup_timepoint vbt,
                    bd_storage_resource bsr 
                    left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ? 
                    where bbt.depend_point_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid 
                    and bbt.module_type = 5 ";
        $sqlParams = [
            xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'],
        ];

        if (!empty($nodeUuid)) {
            $sqlPoint .= ' and bsr.node_uuid = ?';
            $sqlCount .= ' and bsr.node_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($nodeUuid));
        }
        if ($taskUuid) {
            $sqlPoint .= ' and bbt.task_uuid = ?';
            $sqlCount .= ' and bbt.task_uuid = ?';
            $sqlParams = array_merge($sqlParams, [$taskUuid]);
        }

        if ($vmUuid) {
            $sqlPoint .= ' and vbt.agent_uuid = ?';
            $sqlCount .= ' and vbt.agent_uuid = ?';
            $sqlParams = array_merge($sqlParams, [$vmUuid]);
        }

        if ($storageUuid) {
            $sqlPoint .= ' and bsr.storage_uuid = ?';
            $sqlCount .= ' and bsr.storage_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        if ($accurateFlag) {
            //如果填了开始时间范围查询
            if ($startTime && $endTime) {
                $sqlPoint .= " and bbt.create_time between '" . $startTime . "' and '" . $endTime . "' ";
                $sqlCount .= " and bbt.create_time between '" . $startTime . "' and '" . $endTime . "' ";
            }
        }

        // 查看权限
        $userInfo = xphp_get_user_info();
        //租户管理员特殊处理数据显示
        $tenantHandler = new TenantHandler();
        $userHandler = new UserHandler();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        $allManageFlag = false;
        if (!empty($userInfo['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($userInfo['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }
        // 如果是有全局观察者权限，那么显示所有的数据
        if (
        !(in_array('global_read', $userInfo['permissionArr'])
            || in_array('global_write', $userInfo['permissionArr']))
        ) {
            // 没有全局观察者权限
            //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
            if ($manageFlag && $tenantMangerFlag && $allManageFlag) {
                $userList = $tenantHandler->getTenantAllUser($userInfo['tenantuuid']);
                $userListDes = implode("','", $userList);
                $sqlPoint .= " and bbt.user_uuid in ('" . $userListDes . "')";
                $sqlCount .= " and bbt.user_uuid in ('" . $userListDes . "')";
            } else {
                // 查看权限
                if (!$this->isAdmin()) {
                    $authUser = $userInfo['authUser']['awsprotect_look'] ?? [];
                    $useruuid = array_merge([$userInfo['userUuid']], $authUser);
                    $useruuid = "('" . implode("','", $useruuid) . "')";
                    $sqlPoint .= ' and bbt.user_uuid in ' . $useruuid;
                    $sqlCount .= ' and bbt.user_uuid in ' . $useruuid;
                }
            }
        }
        if (empty($userInfo['tenantuuid'])) {
            // 不能看租户内部的资源
            $tenantUuids = (array)$this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids, 'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sqlPoint .= " and bbt.user_uuid not in $tenantUuids ";
            $sqlCount .= " and bbt.user_uuid not in $tenantUuids ";
        } elseif (xphp_three_powers() && $this->isAdmin()) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        } else {
            // 关联管理用户判断 存储资源 - 查看
            $authKey = 'storage_manager_look';
            $authUser = $userInfo['authUser'][$authKey] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuidArr = array_merge([$userInfo['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sqlPoint .= " and (bsr.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
                $sqlCount .= " and (bsr.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
            } else {
                $userUuid = $userInfo['userUuid'];
                $sqlPoint .= ' and (bsr.user_uuid = ? or mur.user_uuid = ?) ';
                $sqlCount .= ' and (bsr.user_uuid = ? or mur.user_uuid = ?) ';
                $sqlParams = array_merge($sqlParams, array($userUuid, $userUuid));
            }
        }

        // 查看权限
        if ($userInfo['userType'] != 3) {
            $authUser = $user['authUser']['awsprotect_look'] ?? [];
            $useruuid = array_merge([$userInfo['userUuid']], $authUser);
            $useruuid = "('" . implode("',", $useruuid) . "')";
            $sqlPoint .= ' and bbt.user_uuid in ' . $useruuid;
            $sqlCount .= ' and bbt.user_uuid in ' . $useruuid;
        }

        $tableFlag = false; //是否表格查询
        $sqlCountParams = $sqlParams;
        if (
            isset($sortColumn) && isset($sortType) && isset($start) && isset($length) && empty($params['instant_flag'])
        ) {
            $tableFlag = true;
            $sqlPoint .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
            $sqlParams = array_merge($sqlParams, array($start, $length));
        } else {
            //时间点树
            $sqlPoint .= " order by bbt.task_uuid, vbt.agent_uuid, bbt.create_time";
        }

        $pointData = $this->dbSelect($sqlPoint, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $storageHandler = new StorageHandler();

        if ($tableFlag) {
            $records = ['rows' => [], 'total' => 0];
            //表格获取分页数据
            $i = 1;
            foreach ($pointData as $d) {
                $remark = '';   //备注
                $timepointDiv = '<span>' . ($d['timepoint']) . '</span>' .
                    '<span class="table-body__i">' . $remark . '</span>';
                $records['rows'][] = array(
                    'timepoint_uuid' => $d['timepoint_uuid'],
                    'storage_uuid' => $d['storage_uuid'],
                    'storage_type' => $d['storage_type'],
                    'no_html' => $i++,
                    'timepoint_html' => $timepointDiv,
                    'data_size' => v1_calsize($d['total_size'], true),
                    'write_size' => v1_calsize($d['write_size'], true),
                    'storage_des' => $storageHandler->getStorageName($d['storage_uuid']),
                    'user_name' => $d['user_name'] ?: '--',
                    'detail' => array(
                        'agent_uuid' => $vmUuid,
                        'task_uuid' => $taskUuid
                    ),
                );
            }
            $records['total'] = $dataCount[0]['total'];
            return $records;
        }
        $timepoint = array();
        $agentList = $this->getAllAgentList();
        foreach ($pointData as $point) {
            if (!$point) {
                continue;
            }

            $taskUuid = $point['task_uuid'];
            $vmUuid = $point['agent_uuid'];
            $timepointUuid = $point['timepoint_uuid'];
            // 处理下 启动方式的值
            $osconfig = json_decode($point['os_config'], true);
            //检查并添加完备点
            if (!in_array($timepointUuid, $timepoint)) {
                $osTypeName = $point['os_type'] == 1 ? 'Windows' : 'Linux';
                $name = ($point['timepoint']) . ' (' . xphp_get_lang('UI_INSTANT_POINTS') . ')';
                $timepoint[] = $timepointUuid;
                $vmName = $point['current_vm_name'] ?: $point['vm_name'];
                $config = json_decode($point['detail'], true);
                $node[] = array(
                    'id' => $timepointUuid,
                    'pId' => $taskUuid . $vmUuid,
                    'name' => $name,
                    'osname' => (new MachineOsRecover())->getAgentNameByList(
                        $agentList,
                        $point['agent_uuid'],
                        $point['os_name'],
                        $point['agent_ip']
                    ),
                    'old_name' => $name,
                    'oldname' => $name,
                    'system_boot_type' => intval($osconfig['system_boot_type']),
                    'agentuuid' => $vmUuid,
                    'vm_name' => rawurldecode($vmName),
                    'point_name' => $point['timepoint'],
                    'timepoint_uuid' => $timepointUuid,
                    'timepointuuid' => $timepointUuid,
                    "create_time" => $point['timepoint'],
                    "createtime" => $point['timepoint'],
                    'pointname' => $point['timepoint'],
                    'node_uuid' => $point['real_node_uuid'] ?: $point['node_uuid'],
                    'nodeuuid' => $point['real_node_uuid'] ?: $point['node_uuid'],
                    'real_node_uuid' => $point['real_node_uuid'] ?: $point['node_uuid'],
                    'storage_uuid' => $point['storage_uuid'],
                    'storage_type' => $point['storage_type'],
                    'ostype' => $point['os_type'],
                    'task_uuid' => $taskUuid,
                    'taskuuid' => $taskUuid,
                    'type' => 2,
                    'path' => htmlspecialchars($point['dir_path']),
                    'icon' => './img/platform/timepoint-f.png',
                    'title' => htmlspecialchars(rawurldecode(
                        xphp_get_lang('WEB_OS_SOURCE_PATH') . ': ' . $point['dir_path']
                    )),
                    'iconSkin' => $osTypeName . 'logo',
                    'config' => $config,
                    'chkDisabled' => false,
                    'encrypted_flag' => false,
                    'virus_scan_status' => 0, // 备份点健康状态 0 未扫描 1扫描中 2健康 3感染
                );
            }
        }
        if (!empty($params['instant_flag'])) {
            // 如果是选择恢复源的，需要合并对应的模块的一起返回，所以返回数组即可
            return $node;
        }
        return ['rows' => $node, 'total' => $dataCount[0]['total']];
    }

    /**
     * 瞬时恢复快照点之删除时间点
     * @param array $params 请求参数
     * @return array
     */
    public function instantancePointsDel(array $params)
    {

        $result = $this->delPoints($params['timepoint_uuid']);
        return $this->muOpResult($result[0], $result[1], $result[2]);
    }

    /**
     * 内部封装删除时间点
     * @param string $timepointUuid 时间点
     * @return array|json
     */
    private function delPoints(string $timepointUuid)
    {

        $sql = "select bbt.create_time timepoint,
                (select task_name from bd_task where task_uuid = bbt.task_uuid) as task_name,
	            (select task_name from bd_history_task where task_uuid = bbt.task_uuid limit 1) as task_name2,
	            (select vm_name from vm_backup_timepoint where timepoint_uuid = bbt.depend_point_uuid) as vm_name,
	            (select os_name from os_backup_timepoint where timepoint_uuid = bbt.depend_point_uuid) as os_name
            from bd_instant_recovery_snapshot_point bbt
            where bbt.snapshot_point_uuid = ? ";
        $data = $this->dbSelect($sql, [$timepointUuid]);

        $opName = 'ELITE_RECOVERY_INSTANT_OP_DELETE_SNAPSHOT_POINT';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        $user = xphp_get_user_info();
        if (
            !in_array('global_write', $user['permissionArr'])
            && in_array('global_read', $user['permissionArr'])
        ) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            if (!$this->checkTimepointOperateAuth($operate, [$timepointUuid])) {
                return $this->muOpResult(false, $operate, xphp_get_lang('WEB_VM_DATA_NO_OPERATE_PERMISSION'));
            }
        }

        $msg = ['point_id' => $timepointUuid];
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $msg
        );
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $taskName = $data[0]['task_name'] ?: $data[0]['task_name2'];
        $agentName = $data[0]['vm_name'] ?: $data[0]['os_name'];
        $timepointDes = $data[0]['timepoint'] . '(' . xphp_get_lang('UI_INSTANT_POINTS') . ')';
        $descriptionParam = array($timepointDes, $taskName, $agentName);
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_TIMEPOINT', $descriptionParam);
            return [$result, $operate, $msg];
        } else {
            $this->systemLog(
                'SYSTEM_LOG_DELETE_ONE_TIMEPOINT',
                $descriptionParam,
                xphp_get_config('log', 'LOGLEVEL')['ERROR'],
                $mbResult['errorCode']
            );
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除备份时间点数据 循环删除单个时间点，感觉不太合理
     * @param array $params 参数
     * @return string
     */
    public function delInstantanceTree(array $params): string
    {
        $timepointList = $params['tree_list'];
        $result = [];
        // 循环调用
        foreach ($timepointList as $item) {
            $result = $this->delPoints($item);
        }
        return $this->muOpResult($result[0], $result[1], $result[2]);
    }

    /**
     * 获取瞬时恢复任务对应的时间点。迁移需要用
     * @param array $params 请求参数
     * @return array
     */
    public function instantancePoint(array $params): array
    {

        $jobUuid = $params['job_uuid'];
        // 先判断是什么类型的瞬时恢复
        $sql = "select bbt.module_type module_types,bt.module_type,bbt.backup_mode
                        from bd_backup_timepoint bbt,bd_task bt,bd_instant_recovery_task birt
                        where bbt.timepoint_uuid = birt.timepoint_uuid and bt.task_uuid = birt.task_uuid
                          and birt.task_uuid = ?";
        $type = $this->dbSelect($sql, [$jobUuid]);
        $moduleArr = xphp_get_config('module', 'MODULE_TYPE');
        $des = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        if ($type[0]['module_types'] == $moduleArr['VM']) {
            // 虚拟化的时间点瞬时恢复
            // 需要查询出时间点的虚拟化类型，以及瞬时恢复任务的虚拟化类型
            $sql = "select distinct bbt.detail, bbt.deleted_flag, bbt.storage_uuid, bbt.timepoint_uuid, 
            unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid,
            unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks, bbt.archive_flag,
            bbt.weekly_flag,bbt.monthly_flag,bbt.yearly_flag,bbt.importance_flag,
            bbt.total_size, bbt.write_size, bbt.data_local_flag, bbt.real_node_uuid, 
            vbt.vm_uuid, 
            vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, 
            bsr.node_uuid, bsr.status, bsr.storage_type,birt.instant_target_info
                from bd_backup_timepoint bbt, 
                     vm_backup_timepoint vbt, 
                     bd_storage_resource bsr,
					 bd_instant_recovery_task birt	
                where bbt.timepoint_uuid = vbt.timepoint_uuid 
                      and bbt.storage_uuid = bsr.storage_uuid 
                      and birt.timepoint_uuid = bbt.timepoint_uuid 
	                   and birt.task_uuid = ?";

            $info = $this->dbSelect($sql, [$jobUuid]);
            $node = [];
            foreach ($info as $item) {
                $mode = $des[$item['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
                $point = $this->parseDate($item['timepoint']);
                $name = $point . '(' . $mode . ')';
                //判断是否有加密 ,如果有手动输入密码则显示小锁标识
                $encryptedflag = false;
                if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
                    $encryptedflag = true;
                }
                // 处理下瞬时恢复的目标配置信息
                $instantTargetInfo = json_decode($item['instant_target_info'], true);
                $node[] = [
                    'type' => 1,
                    'storageuuid' => $item['storage_uuid'],
                    'node_uuid' => $item['real_node_uuid'],
                    'point_info' => [
                        [
                            'uuid' => $item['vm_uuid'],
                            'timepoint_uuid' => $item['timepoint_uuid'],
                            'vcenter_uuid' =>  $item['vcenter_uuid'],
                            'hypervisor' => $item['hypervisor_type'],
                            'hypervisor_new' => $instantTargetInfo['hypervisor_type'],
                            'encrypted_flag' => $encryptedflag,
                            'node_uuid' => $item['real_node_uuid'],
                            'config' => json_decode($item['detail'], true),
                            'host_name' => $item['vm_name'],
                            'name' => $point,
                            'target_uuid' => $instantTargetInfo['target_uuid'],
                            'host_uuid' => $instantTargetInfo['host_uuid'],
                        ]
                    ],
                    'vm_old_name' => [
                        [
                            $item['vm_name'],
                            $point
                        ]
                    ],
                    'show_str' => [
                        $item['dir_path'] . '(' . $name . ')',
                    ]
                ];
            }
        } else {
            // 有代理的时间点瞬时恢复
            $sql = "select distinct bbt.detail, bbt.deleted_flag, bbt.storage_uuid, bbt.timepoint_uuid, 
            unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid,
            unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks, bbt.archive_flag,
            bbt.weekly_flag,bbt.monthly_flag,bbt.yearly_flag,bbt.importance_flag,
            bbt.total_size, bbt.write_size, bbt.data_local_flag, bbt.real_node_uuid, 
            obt.agent_uuid, obt.os_name, obt.dir_path, obt.os_type,obt.os_config,birt.extension_info,
            bsr.node_uuid, bsr.status, bsr.storage_type,birt.instant_target_info,obt.agent_ip
                from bd_backup_timepoint bbt, 
					 bd_storage_resource bsr,
					 bd_instant_recovery_task birt,	
					 os_backup_timepoint obt
                where bbt.timepoint_uuid = obt.timepoint_uuid 
                      and bbt.storage_uuid = bsr.storage_uuid 
                      and birt.timepoint_uuid = bbt.timepoint_uuid 
	                   and birt.task_uuid = ?";

            $info = $this->dbSelect($sql, [$jobUuid]);
            $node = [];
            $agentName = xphp_get_lang('UI_PLATFORM_AGENT');
            foreach ($info as $item) {
                $mode = $des[$item['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
                $point = $this->parseDate($item['timepoint']);
                $name = $point . '(' . $mode . ')';
                $details = json_decode($item['detail'], true);
                //判断是否有加密 ,如果有手动输入密码则显示小锁标识
                $encryptedflag = false;
                if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
                    $encryptedflag = true;
                }
                // 处理下瞬时恢复的目标配置信息
                $instantTargetInfo = json_decode($item['instant_target_info'], true);
                // 判断下是否有系统分区
                $ostInfo = json_decode($item['os_config'], true);
                $instantInfo = json_decode($item['extension_info'], true);
                $sysDevUuid = '';
                foreach ($ostInfo['all_disk_list'] as $items) {
                    if ($items['is_system_disk_flag'] == 1) {
                        $sysDevUuid = $items['dev_uuid'];
                        break;
                    }
                }
                $isSys = true;
                if (
                    !empty($sysDevUuid) && !empty($instantInfo[0]['inst_rev_devices']) &&
                    !in_array($sysDevUuid, $instantInfo[0]['inst_rev_devices'])
                ) {
                    $isSys = false;
                }

                $node[] = [
                    'type' => 2,
                    'storageuuid' => $item['storage_uuid'],
                    'node_uuid' => $item['real_node_uuid'],
                    'point_info' => [
                        [
                            'uuid' => $item['agent_uuid'],
                            'timepoint_uuid' => $item['timepoint_uuid'],
                            'os_type' => $item['os_type'],
                            'is_sys' => $isSys,
                            'encrypted_flag' => $encryptedflag,
                            'vcenter_uuid' => 0,
                            'hypervisor' => 0,
                            'hypervisor_new' => $instantTargetInfo['hypervisor_type'] ?? 0,
                            'node_uuid' => $item['real_node_uuid'],
                            'config' => json_decode($item['detail'], true),
                            'host_name' => $agentName . '：' . $item['os_name'] . '(' . $item['agent_ip'] . ')',
                            'name' => $point,
                            'target_uuid' => $instantTargetInfo['target_uuid'],
                            'host_uuid' => $instantTargetInfo['host_uuid'],
                        ]
                    ],
                    'vm_old_name' => [
                        [
                            $item['os_name'],
                            $point
                        ]
                    ],
                    'show_str' => [
                        $item['dir_path'] . '(' . $name . ')',
                    ]
                ];
            }
        }

        return [
            'rows' => $node,
            'total' => count($node)
        ];
    }

    /**
     * 获得所有agent的name和ip信息
     * @return array
     */
    public function getAllAgentList()
    {
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent";
        $result = $this->dbSelect($sql);
        $info = array();
        foreach ($result as $each) {
            $info[] = array(
                'agent_uuid' => $each['agent_uuid'],
                'agent_name' => $each['agent_name'],
                'hostname' => $each['hostname'],
                'ip' => $each['ip'],
            );
        }
        return $info;
    }

    /**
     * 时间点操作鉴权
     * @param array $timepointUuids 时间点uuid
     * @return bool
     */
    private function checkTimepointOperateAuth(array $timepointUuids): bool
    {
        $sqlAuth = "select user_uuid from bd_instant_recovery_snapshot_point where snapshot_point_uuid in ('"
            . implode("','", $timepointUuids) . "')";
        $data = (array) $this->dbSelect($sqlAuth);
        $userArr = array_unique(array_column($data, 'user_uuid'));
        return xphp_check_operate('data_manager', $userArr);
    }
}
