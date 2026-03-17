<?php

namespace app\v1\vm\v0\logic;

use app\v1\common\logic\Data;
use app\v1\opcode\PfOpcode;
use app\v1\recovery\v0\logic\InstantaneousData;
use app\v1\resources\v0\logic\Node;
use app\v1\user\v0\logic\User as UserHandler;
use app\v1\tenant\v0\logic\Index as TenantHandler;
use app\v1\job\v0\logic\JobInfo as JobInfoHandler;
use app\v1\resources\v0\logic\Storage as StorageHandler;

/**
 * note          虚拟机 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmData extends Data
{
    /**
     * 获取备份数据的虚拟机树
     * @param array $params 参数
     * @return string|array
     */
    public function getRestoreDataVmTree(array $params)
    {
        //配置
        $moduleTypes = xphp_get_config('module', 'MODULE_TYPE');
        $vmHypervisorTypes = xphp_get_config('vm', 'VMHYPERVISORTYPE');
        $taskTypes = xphp_get_config('task', 'TASKTYPE');

        $showType = intval($params['show_type']);
        $nodeUuid = $params['node_uuid'] ?? '';                   //节点UUID,为空的时候显示所有节点数据
        $instantFlag = $params['instant_flag'] ?? false;             //瞬时恢复标志,瞬时恢复的时候在按时间点分组展示方式的时候,时间点没有选择框
        $manageFlag = $params['manage_flag'];               //备份数据管理标志,备份数据管理的树带有checkbox
        $exportFlag = $params['export_flag'];               //备份数据导出的标志,暂时只支持VMware备份数据导出
        $dataFlag = $params['data_flag'];                    //备份数据标志
        $subModuleType = $params['sub_module_type'];        // 子模块类型
        $storageUuid = $params['storage_uuid'];                    //存储UUID
        $grainFlag = $params['grain_flag'];                 //细粒度恢复标志

        // 加载更多的参数
        $offset = $params['loadmore_offset'];
        $limit = $params['loadmore_limit'];
        $jobUuid = $params['job_uuid'];
        $_rows = $params['_rows']; // 以rows&total方式获取

        $keyword = trim($params['keyword']);
        if ($keyword) {
            // 按关键词搜索时一次性加载
            $offset = null;
            $limit = null;
            $jobUuid = '';
        }

        if ($showType == xphp_get_config('vm', 'POINTSHOWTYPE')['TIMEGROUP']) {
            //如果是按时间点方式显示
            $node = $this->getTimepointTimeGroup($nodeUuid, $instantFlag);
            return $this->muOpResult(true, '', 'Get vm restore data', '', 0, $node);
        }
        $this->paramsCheck($showType);
        // vm_config只有openstack需要
        $privateCloudFields = $subModuleType == xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'] ? 'vbt.vm_config, ' : '';
        $sql = 'select distinct bbt.storage_uuid, bbt.task_type, bbt.module_type, bbt.task_create_time, 
    			bbt.task_uuid, bbt.user_uuid, bbt.user_name, vbt.vcenter_uuid, vbt.vm_uuid, ' . $privateCloudFields . ' vbt.vm_name, 
                vbt.dir_path, vbt.hypervisor_type, bsr.node_uuid, bsr.storage_type, ifnull(bt.task_name, bbt.task_name) as task_name  
                from vm_backup_timepoint vbt 
                    join bd_backup_timepoint bbt on bbt.timepoint_uuid = vbt.timepoint_uuid 
                    join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid 
                    left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ? 
                    left join bd_task bt on bbt.task_uuid = bt.task_uuid 
                where bbt.available_flag = ?
	              and bbt.import_flag = ? 
                  and bbt.module_type in (' . $moduleTypes['VM'] . ', ' . $moduleTypes['BACKUP_COPY_CLIENT'] . ')
                  and bbt.data_local_flag = ? ';
        $flag = xphp_get_config('app')['FLAG'];
        $sqlParams = array(xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'], $flag['SET'], $flag['UNSET'], $flag['SET']);

        //租户管理员特殊处理数据显示
        $tenantHandler = TenantHandler::instance();
        $userHandler = UserHandler::instance();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        $allManageFlag = false;
        if (!empty(xphp_get_user_info()['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings(xphp_get_user_info()['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }

        //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
        if ($dataFlag && $tenantMangerFlag && $allManageFlag) {
            $userList = $tenantHandler->getTenantAllUser(xphp_get_user_info()['tenantuuid']);
            $userListDes = implode("','", $userList);
            $sql .= " and bbt.user_uuid in ('" . $userListDes . "')";
        }
        $userInfo = xphp_get_user_info();
        if (empty($userInfo['tenantuuid'])) {
            // 不能看租户内部的资源
            $tenantUuids = (array)$this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        }
        // 获取非分配资源的权限
        if (v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_users(VmPlatform::instance()->getUserAuthKeyBySubModule($subModuleType));
            $sql .= " and bbt.user_uuid in $resourceUuidSql";
        }

        // 排除云存储的数据
        if (!empty($params['notcloud'])) {
            $sql .= ' and bsr.storage_type != ? ';
            $sqlParams = array_merge($sqlParams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['CLOUD']]);
        }

        // 排除磁带的数据
        if (!empty($params['nottape'])) {
            $sql .= ' and bsr.storage_type != ? ';
            $sqlParams = array_merge($sqlParams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['TAPE']]);
        }

        //备份数据页面
        if ($dataFlag) {
            $sql .= ' and bbt.copy_flag = ? and bbt.module_type = ? ';
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], $moduleTypes['VM']));
        }

        if ($nodeUuid) {
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= ' and bsr.node_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($nodeUuid));
        }

        $cloudTypesTmp = $cloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $privateTypesTmp = $privateTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud'];
        $hypervisorTypesTmp = $hypervisorTypes = array_diff(xphp_get_config('vm', 'VMHYPERVISORTYPE'), array_merge($cloudTypes, $privateTypes), [0]);
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
            $sql .= ' and bbt.storage_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        } else {
            //如果是瞬时恢复或者细粒度恢复 所有存储还要排除云存储/磁带
            if ($instantFlag || $grainFlag) {
                $sql .= " and bsr.storage_type not in (" . xphp_get_config('resource', 'BD_STORAGE_TYPE')['HUAWEI_CBR'] . "," . xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD'] . "," . xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'] . ") ";
            }
        }

        if ($jobUuid) {
            $sql .= ' and bbt.task_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($jobUuid));
        }

        // 按关键词搜索
        if ($keyword) {
            $sql .= " and (ifnull(bt.task_name, bbt.task_name) like '%{$keyword}%' or vbt.vm_name like '%{$keyword}%' 
            or bbt.task_uuid in (select task_uuid from bd_history_task where task_name like '%{$keyword}%') 
            or bbt.timepoint like '%{$keyword}%' and bbt.backup_mode = 1) ";
        }

        $sql .= " group by vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_name, bbt.task_uuid, task_name 
            order by task_name, bbt.timepoint desc, vbt.vm_name  ";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();

        //定义task vm timepoint
        $hypervisor = array();
        $task = array();
        $vm = array();
        $vmPlatformLogic = VmPlatform::instance();
        $vmCount = []; // 统计每个任务下已添加的虚拟机节点的个数
        $total = []; // 统计每个任务下虚拟机总条数
        foreach ($data as $d) {
            if ($exportFlag) {
                //如果是备份数据导出任务创建,这里只显示VMware的,其他的虚拟化类型暂时过滤,以后支持了之后再添加
                if (!in_array(intval($d['hypervisor_type']), xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware'])) {
                    continue;
                }
            }

            $taskUuid = $d['task_uuid'];
            $taskCreateTime = $d['task_create_time'];
            //检查并添加虚拟化类型
            if (!in_array($d['hypervisor_type'], $hypervisor) && ($_rows && !$jobUuid || !$_rows)) {
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
            //检查并添加task
            if (!in_array($hypervisorType . $taskUuid, $task) && ($_rows && !$jobUuid || !$_rows)) {
                // 同一个任务根据task_create_time排倒序找到最新的任务名
                $thisTaskData = array_filter((array)$data, function ($item) use ($taskUuid) {
                    return $item['task_uuid'] == $taskUuid;
                });
                $thisTaskData = v1_array_sort($thisTaskData, 'task_create_time', 'desc', 0, -1);
                $taskName = $thisTaskData[0]['task_name'];

                $task[] = $hypervisorType . $taskUuid;
                $taskAvailable = in_array($taskUuid, $currentTaskUUID);
                $name = $taskAvailable
                    ? $taskName
                    : $taskName . '(' . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ')';
                if ($d['module_type'] == $moduleTypes['BACKUP_COPY_CLIENT']) {
                    if (
                        intval($d['task_type']) == $taskTypes['BACKUP_COPY']
                        || intval($d['task_type']) == $taskTypes['BACKUP_COPY_FETCH']
                    ) {
                        $name = $taskName . '(' . xphp_get_lang('UI_COPY_DATA') . ')';
                    } elseif (
                        intval($d['task_type']) == $taskTypes['ARCHIVE']
                        || intval($d['task_type']) == $taskTypes['ARCHIVE_FETCH']
                    ) {
                        $name = $taskName . '(' . xphp_get_lang('UI_ARCHIVE_DATA') . ')';
                    }
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if (
                    $dataFlag && $tenantMangerFlag && $allManageFlag
                    && $d['user_uuid'] != xphp_get_user_info()['userUuid']
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
                    'task_uuid' => $taskUuid
                );
            }
            $vmUuid = $d['vm_uuid'];
            //检查并添加vm
            if (!in_array($hypervisorType . $vmUuid . $taskUuid, $vm)) {
                if ($_rows && !$jobUuid && isset($offset) && isset($limit) && $vmCount[$taskUuid] >= $limit) {
                    // 初次获取时单个任务下超过加载更多的条数就不再获取，任务下的总数标记为limit+1用于显示【加载更多】
                    $total[$taskUuid] = $limit + 1;
                    continue;
                }
                $total[$taskUuid] = intval($limit);
                $vm[] = $hypervisorType . $vmUuid . $taskUuid;
                $vmName = $d['vm_name'];
                if (in_array($hypervisorType, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
                    $vmConfig = json_decode($d['vm_config'], true);
                    $ipList = $vmConfig['network_list'][0]['ip_list'];
                    if (!empty($ipList)) {
                        $vmName .= '(' . $ipList[0]['ipaddr'] . ')';
                    }
                }
                // 区分虚拟机和实例图标
                $iconSkin = $cloudTimepointFlag ? 'vm_aws_vm' : 'vm';
                $icon = $cloudTimepointFlag ? './img/vm/AWS/aws-instance.png' : './img/vm/vm.png';
                // 虚拟机源路径 或 实例源路径
                $langPath = in_array($hypervisorType, array_merge($cloudTypesTmp, $privateTypesTmp))
                    ? xphp_get_lang('WEB_PLATFORM_DC_INSTANCE_SRC_DIR_PATH') : xphp_get_lang('WEB_PLATFORM_DC_VM_SRC_DIR_PATH');
                $node[] = array(
                    'id' => $hypervisorType . $vmUuid . $taskUuid,
                    'pId' => $hypervisorType . $taskUuid,
                    'name' => rawurldecode($vmName),
                    'open' => false,
                    'nocheck' => !$manageFlag,
                    'type' => 1,
                    'icon' => $icon,
                    'iconSkin' => $iconSkin,
                    'title' => $langPath . ': ' . rawurldecode($d['dir_path']),
                    'platform_uuid' => $d['vcenter_uuid'],
                    'vm_uuid' => $d['vm_uuid'],
                    'task_uuid' => $taskUuid,
                    'node_uuid' => $d['node_uuid'],
                    "create_time" => $taskCreateTime,
                    'hypervisor_type' => $d['hypervisor_type'],
                    'isParent' => true,
                    'clickshow' => true
                );
                ++$vmCount[$taskUuid];
            }
        }
        // 初次获取时每个任务下添加【加载更多】节点
        foreach ($node as $item) {
            if ($_rows && isset($offset) && isset($limit) && 0 == $item['type'] && $limit < $total[$item['task_uuid']]) {
                $node[] = [
                    'id' => $item['hypervisor_type'] . $item['task_uuid'] . '_loadMore',
                    'pId' => $item['hypervisor_type'] . $item['task_uuid'],
                    'name' => '<span class="loadMore font-green-seagreen">' . xphp_get_lang('WEB_FILE_MORE') . '</span>',
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    'nocheck' => true,
                    'isParent' => false,
                    'nextOffset' => $limit,
                    'moreType' => 1, // 代表加载更多虚拟机
                    'task_uuid' => $item['task_uuid'],
                    'icon' => $icon,
                    'iconSkin' => $iconSkin,
                ];
            }
        }

        // 这里需要判断下，如果携带了 instant_flag = true则表示还需要返回瞬时恢复的时间点
        if (!empty($params['instant_flag'])) {
            $node1 = (new InstantaneousData())->instantanceTree($params);
            $node = array_merge($node, $node1);
            $node = v1_unique_by_two_keys_manual($node, 'id', 'pId');
        }
        // 通过数组分页实现加载更多
        if (isset($offset) && isset($limit) && isset($jobUuid)) {
            $total[$jobUuid] = count($node);
            $node = array_slice($node, $offset, $limit);
            // 继续添加【加载更多】节点
            if ($node && $offset + $limit < $total[$jobUuid]) {
                $node[] = [
                    'id' => $node[0]['hypervisor_type'] . $node[0]['task_uuid'] . '_loadMore',
                    'pId' => $node[0]['hypervisor_type'] . $node[0]['task_uuid'],
                    'name' => '<span class="loadMore font-green-seagreen">' . xphp_get_lang('WEB_FILE_MORE') . '</span>',
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    'nocheck' => true,
                    'isParent' => false,
                    'nextOffset' => $offset + $limit,
                    'moreType' => 1, // 代表加载更多虚拟机
                    'task_uuid' => $node[0]['task_uuid'],
                    'icon' => $icon,
                    'iconSkin' => $iconSkin,
                ];
            }
        }
        if ($_rows) {
            return ['rows' => $node, 'total' => $total];
        }
        return $this->muOpResult(true, '', '', '', 0, $node);
    }

    /**
     * 获取/筛选备份时间点
     * @param array $params 参数
     * @return array
     */
    public function getRestorePoints(array $params): array
    {
        //异步获取使用
        $taskUuid = $params['task_uuid'];
        $hypervisor = $params['hypervisor_type'];
        $vmUuid = $params['vm_uuid'];
        $disabledFlag = $params['disabled_flag'];    //备份数据禁用勾选增量差异标志
        $manageFlag = $params['manage_flag'];       //备份数据管理标志,备份数据管理的树带有checkbox,异步获取和筛选都为true
//        $vmCheck = $params['vm_check'];
        $nodeUuid = $params['node_uuid'];
        $grainFlag = $params['grain_flag'];

        // 加载更多的参数
        $offset = $params['loadmore_offset'];
        $limit = $params['loadmore_limit'];
        $parentUuid = $params['parent_uuid']; // 获取完备点时传vm_uuid，获取非完备点时传完备点的timepoint_uuid

        //筛选条件使用
        $keyword = trim($params['keyword']);
        if ($keyword) {
            // 按关键词搜索时一次性加载
            $offset = null;
            $limit = null;
        }
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];
        $backupMode = $params['backup_mode']; //通过备份模式筛选：1完备，2增量，3差异，9永久增量
        $accurateFlag = $params['accurate_flag']; //高级搜索标志

        //表格使用
        $start = $params['offset'];
        $length = $params['limit'];
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $localFlag = $params['local_flag'];
        $platformUuid = $params['platform_uuid'];
        $storageUuid = $params['storage_uuid'];
        $week = $params['week_flag'];
        $month = $params['month_flag'];
        $year = $params['year_flag'];
        $forever = $params['forever_flag'];
        $sortArr = array(
            'timepoint' => 'bbt.timepoint',
            'backup_mode' => 'bbt.backup_mode',
            'data_size' => 'bbt.total_size',
            'write_size' => 'bbt.write_size',
            'storage' => 'bsr.storage_nickname',
            'user_name' => 'bu.user_name'
        );

        $node = array();
        $sqlPoint = "select distinct bbt.detail, bbt.deleted_flag, bbt.storage_uuid, bbt.timepoint_uuid, 
            unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid,
            unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks, bbt.archive_flag,
            bbt.weekly_flag,bbt.monthly_flag,bbt.yearly_flag,bbt.importance_flag,
            bbt.total_size, bbt.write_size, bbt.data_local_flag, bbt.real_node_uuid, 
            bbt.merge_status, bbt.operation_status, bbt.integrity_check_flag, bdtsi.virus_scan_status, bdtsi.integrity_check_status, 
            vbt.vm_uuid, 
            (SELECT vbt2.vm_name FROM vm_backup_timepoint vbt2 JOIN bd_backup_timepoint bbt2 ON vbt2.timepoint_uuid = bbt2.timepoint_uuid 
                 WHERE bbt2.task_uuid = '{$taskUuid}' AND vbt2.vcenter_uuid = '{$platformUuid}' AND vbt2.vm_uuid = vbt.vm_uuid ORDER BY vbt2.vm_timepoint_id DESC LIMIT 1) as current_vm_name, 
            vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, 
            bsr.node_uuid, bsr.storage_uuid, bsr.status, bsr.storage_type, bu.user_name 
                from bd_backup_timepoint bbt left join bd_user bu on bbt.user_uuid = bu.user_uuid 
                     left join bd_backup_timepoint_safe_info bdtsi on bbt.timepoint_uuid = bdtsi.timepoint_uuid, 
                     vm_backup_timepoint vbt, 
                     bd_storage_resource bsr 
                     left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ?   
                where bbt.timepoint_uuid = vbt.timepoint_uuid 
                       and bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 
	                   and bbt.import_flag = 2  and bbt.module_type in (2, 9) ";
        $sqlCount = "select count(distinct bbt.id) as total from bd_backup_timepoint bbt, vm_backup_timepoint vbt, 
                    bd_storage_resource bsr 
                    left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ? 
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid 
                    and bbt.import_flag = 2 and
                    bbt.available_flag = 1 and bbt.module_type in (2, 9) ";
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
        $cloudFlag = false;
        if ($hypervisor) {
            $sqlPoint .= ' and vbt.hypervisor_type = ?';
            $sqlCount .= ' and vbt.hypervisor_type = ?';
            $sqlParams = array_merge($sqlParams, [$hypervisor]);
            $cloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
            $privateTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud'];
            $cloudFlag = in_array($hypervisor, array_merge($cloudTypes, $privateTypes));
        }
        if (!empty($keyword)) {
            $keyword = '%' . $keyword . '%';
            $sqlPoint .= " and (bbt.timepoint like '" . $keyword .
                "' or bbt.task_name like '" . $keyword . "' or vbt.vm_name like '" . $keyword .
                "' or bbt.task_uuid in (select task_uuid from bd_history_task where task_name like '" . $keyword . "'))";
            $sqlCount .= " and (bbt.timepoint like '" . $keyword .
                "' or bbt.task_name like '" . $keyword . "' or vbt.vm_name like '" . $keyword .
                "' or bbt.task_uuid in (select task_uuid from bd_history_task where task_name like '" . $keyword . "'))";
        }
        if ($localFlag) {
            $sqlPoint .= ' and bbt.data_local_flag = ?';
            $sqlCount .= ' and bbt.data_local_flag = ?';
            $sqlParams = array_merge($sqlParams, [$localFlag]);
        }
        if ($storageUuid) {
            $sqlPoint .= ' and bsr.storage_uuid = ?';
            $sqlCount .= ' and bsr.storage_uuid = ?';
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        if ($accurateFlag) {
            if (!empty($backupMode)) {
                $sqlPoint .= ' and bbt.backup_mode = ?';
                $sqlCount .= ' and bbt.backup_mode = ?';
                $sqlParams = array_merge($sqlParams, array($backupMode));
            }

            //如果填了开始时间范围查询
            if ($startTime && $endTime) {
                $sqlPoint .= " and bbt.timepoint between '" . $startTime . "' and '" . $endTime . "' ";
                $sqlCount .= " and bbt.timepoint between '" . $startTime . "' and '" . $endTime . "' ";
            }

            //按标记筛选
            if ($week == 1) {
                $sqlPoint .= ' and bbt.weekly_flag = 1';
                $sqlCount .= ' and bbt.weekly_flag = 1';
            }
            if ($month == 1) {
                $sqlPoint .= ' and bbt.monthly_flag = 1';
                $sqlCount .= ' and bbt.monthly_flag = 1';
            }
            if ($year == 1) {
                $sqlPoint .= ' and bbt.yearly_flag = 1';
                $sqlCount .= ' and bbt.yearly_flag = 1';
            }
            if ($forever == 1) {
                $sqlPoint .= ' and bbt.importance_flag = 1';
                $sqlCount .= ' and bbt.importance_flag = 1';
            }
        }
        //公有云细粒度恢复屏蔽云存储的数据
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $publicCloudFlag = $hypervisor ? in_array($hypervisor, $publicCloudTypes) :
            xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'] == $params['sub_moduletype'];
        if ($publicCloudFlag && $grainFlag) {
            $sqlPoint .= ' and bsr.storage_type != ?';
            $sqlCount .= ' and bsr.storage_type != ?';
            $sqlParams = array_merge($sqlParams, array(xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD']));
        }

        // 备份数据处只获取自己模块且是备份任务的
        if ($manageFlag) {
            $sqlPoint .= " and bbt.task_type = ? ";
            $sqlCount .= " and bbt.task_type = ? ";
            $sqlParams = array_merge($sqlParams, [xphp_get_config('task', 'TASKTYPE')['BACKUP']]);
            if ($publicCloudFlag) {
                $sqlPoint .= " and vbt.hypervisor_type in (" . implode(",", $publicCloudTypes) . ")";
                $sqlCount .= " and vbt.hypervisor_type in (" . implode(",", $publicCloudTypes) . ")";
            } else {
                $sqlPoint .= " and vbt.hypervisor_type not in (" . implode(",", $publicCloudTypes) . ")";
                $sqlCount .= " and vbt.hypervisor_type not in (" . implode(",", $publicCloudTypes) . ")";
            }
        }

        // 查看权限
        $userInfo = xphp_get_user_info();
        //租户管理员特殊处理数据显示
        $tenantHandler = TenantHandler::instance();
        $userHandler = UserHandler::instance();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        $allManageFlag = false;
        if (!empty(xphp_get_user_info()['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings(xphp_get_user_info()['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }
        // 获取非分配资源的权限
        if (v1_auth_need_check_look()) {
            $subModule = VmPlatform::instance()->hypervisorTypeToSubModule($params['hypervisor_type']);
            $resourceUuidSql = v1_auth_get_users(VmPlatform::instance()->getUserAuthKeyBySubModule($subModule));
            $sqlPoint .= " and bbt.user_uuid in $resourceUuidSql";
        }
        //具有管理所有备份数据的租户管理员查询租户内所有用户数据,其余用户只能获取到自己的备份数据
        if ($manageFlag && $tenantMangerFlag && $allManageFlag) {
            $userList = $tenantHandler->getTenantAllUser(xphp_get_user_info()['tenantuuid']);
            $userListDes = implode("','", $userList);
            $sqlPoint .= " and bbt.user_uuid in ('" . $userListDes . "')";
            $sqlCount .= " and bbt.user_uuid in ('" . $userListDes . "')";
        }
        if (empty($userInfo['tenantuuid'])) {
            // 不能看租户内部的资源
            $tenantUuids = (array)$this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sqlPoint .= " and bbt.user_uuid not in $tenantUuids ";
            $sqlCount .= " and bbt.user_uuid not in $tenantUuids ";
        }

        // 排除云存储的数据
        if (!empty($params['notcloud'])) {
            $sqlPoint .= ' and bsr.storage_type != ? ';
            $sqlCount .= ' and bsr.storage_type != ? ';
            $sqlParams = array_merge($sqlParams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['CLOUD']]);
        }

        // 排除磁带的数据
        if (!empty($params['nottape'])) {
            $sqlPoint .= ' and bsr.storage_type != ? ';
            $sqlCount .= ' and bsr.storage_type != ? ';
            $sqlParams = array_merge($sqlParams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['TAPE']]);
        }

        $tableFlag = false; //是否表格查询
        $sqlCountParams = $sqlParams;
        if (isset($sortColumn) && isset($sortType) && isset($start) && isset($length)) {
            $tableFlag = true;
            $sqlPoint .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
            $sqlParams = array_merge($sqlParams, array($start, $length));
        } else {
            //时间点树
            $sqlPoint .= " order by vbt.vm_uuid, bbt.timepoint, bbt.task_name";
        }
        $pointData = $this->dbSelect($sqlPoint, $sqlParams);
        $jobInfoHandler = JobInfoHandler::instance();
        $storageHandler = StorageHandler::instance();

        if ($tableFlag) {
            $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
            $records = ['rows' => [], 'total' => 0];
            //表格获取分页数据
            $i = 1;
            foreach ($pointData as $d) {
                $mark = $this->pGetTimepointMark(
                    v1_parse_flag_to_bool($d['weekly_flag']),
                    v1_parse_flag_to_bool($d['monthly_flag']),
                    v1_parse_flag_to_bool($d['yearly_flag']),
                    v1_parse_flag_to_bool($d['importance_flag'])
                );
                $remark = '';   //备注
                $op = array(1, 2, 3);
                //公有云的时间点在云存储无法删除
                if (
                    in_array($d['hypervisor_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])
                    && $d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD']
                ) {
                    $op = array(1, 3);
                }
                //时间点在合并中，不让删除
                if ($d['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                    $op = array(1);
                }
                //时间点在磁带存储，不让删除
                if ($d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE']) {
                    $op = array(1);
                }

                //添加备注
                if ($this->checkEmpty($d['remarks'])) {
                    $remark .= '<a class="popovers remarktips" data-container="body" data-trigger="hover" data-toggle="popover" 
                        data-placement="right" data-content="'
                        . preg_replace('/\"/', "'", $d['remarks']) . '"><i class="viconfont vicon-remark-info"></i></a>';
                }
                if (!isset($mark) && !isset($d['remarks'])) {
                    $timepointDiv = '<span>' . $this->parseDate($d['timepoint']) . '</span>' .
                        '<span class="table-body__i">' . $mark . $remark . '</span>';
                } else {
                    $timepointDiv = '<div>' . $this->parseDate($d['timepoint']) . '</div>' .
                        '<div class="table-body__timepoint">' . $mark . $remark . '</div>';
                }
                //                $noHtml = '<span tid="' . $d['timepoint_uuid'] . '" sid="' . $d['storage_uuid'] . '">'
//                    . $i++ . '</span>';
                $records['rows'][] = array(
                    'hypervisor_type' => $d['hypervisor_type'],
                    'platform_uuid' => $platformUuid,
                    'timepoint_uuid' => $d['timepoint_uuid'],
                    'storage_uuid' => $d['storage_uuid'],
                    'storage_type' => $d['storage_type'],
                    'no_html' => $i++,
                    'timepoint_html' => $timepointDiv,
                    'backup_mode_des' => $jobInfoHandler->getTimepointTypeDes($d['backup_mode']),
                    'data_size' => v1_calsize($d['total_size'], true),
                    'write_size' => v1_calsize($d['write_size'], true),
                    'storage_des' => $storageHandler->getStorageName($d['storage_uuid']),
                    'user_name' => $d['user_name'] ?: '--',
                    'operations' => $op,
                    'detail' => array(
                        'backup_mode' => $d['backup_mode'],
                        'vm_uuid' => $vmUuid,
                        'task_uuid' => $taskUuid,
                        'remote_flag' => !v1_parse_flag_to_bool(intval($d['data_local_flag'])),
                        'weekly_flag' => v1_parse_flag_to_bool($d['weekly_flag']),
                        'monthly_flag' => v1_parse_flag_to_bool($d['monthly_flag']),
                        'yearly_flag' => v1_parse_flag_to_bool($d['yearly_flag']),
                        'importance_flag' => v1_parse_flag_to_bool($d['importance_flag']),
                        'remark' => $d['remarks'],
                        'node_uuid' => $d['real_node_uuid'] ?: $d['node_uuid'],
                        'timepoint_uuid' => $d['timepoint_uuid'],
                        'hypervisor_type' => $d['hypervisor_type']
                    ),
                );
            }
            $records['total'] = $dataCount[0]['total'];
            return $records;
        }

        $pid = '';
        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        $backupModes = xphp_get_config('task', 'BACKUP_MODE');
        foreach ($pointData as $key => $point) {
            if ($point['backup_mode'] == $backupModes['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = [
                    'timepoint' => $point['timepoint'],
                    'timepoint_uuid' => $point['timepoint_uuid']
                ];
            } else {
                if (in_array($point['depend_point_uuid'], array_keys($fulluuidList)) || in_array($point['depend_point_uuid'], array_column($unfullList, 'timepoint_uuid'))) {
                    // 如果非完备点依赖的点存在时才添加，防止获取断头链
                    $unfullList[] = $point;
                } else {
                    // 断头链的情况则去掉
                    unset($pointData[$key]);
                }
            }
        }
        // 记录依赖点的uuid
        $dependUuidList = array_column($unfullList, 'depend_point_uuid');
        while (!empty($unfullList)) {
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key => $unfull) {
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $k => $value) {
                    if ($dependId == $k) {
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$k];
                        $unfullList = array_splice($unfullList, $key, 1);

                        $unfullCountTmp--;
                    }
                    continue;
                }
            }

            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0)
                break;

        }

        $timepoint = array();
        foreach ($pointData as $point) {
            if (!$point) {
                continue;
            }
            $availableFlag = true;

            //如果时间点正在合并且不可用
            if (
                $point['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']
                || $point['status'] != xphp_get_config('resource', 'STORAGE_STATUS')['ONLINE']
            ) {
                $availableFlag = false;
            }


            //判断是合并中还是离线状态
            $archiveStatusStr = true;
            $vmStatusStr = true;

            //删除中
            $deleteStatusStr = false;

            //删除中
            if (
                $point['archive_flag'] == xphp_get_config('app', 'FLAG')['UNSET']
                && $point['deleted_flag'] == xphp_get_config('app', 'FLAG')['SET']
            ) {
                //如果是在恢复页面不可用
//                if (!$manageFlag) {
//                    $availableFlag = false;
//                }
                $deleteStatusStr = true;
            }

            //如果是合并中
            if ($point['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                $archiveStatusStr = false;
            }
            //如果不是在线状态
            if ($point['status'] != xphp_get_config('resource', 'STORAGE_STATUS')['ONLINE']) {
                $vmStatusStr = false;
            }

            $taskUuid = $point['task_uuid'];
            $vmUuid = $point['vm_uuid'];
            $timepointUuid = $point['timepoint_uuid'];
            $taskCreateTimeIn = $this->parseDate($point['task_create_time']);
            $config = json_decode($point['detail'], true);
            if ($manageFlag) {
                $mark = $this->pGetTimepointMark(
                    v1_parse_flag_to_bool($point['weekly_flag']),
                    v1_parse_flag_to_bool($point['monthly_flag']),
                    v1_parse_flag_to_bool($point['yearly_flag']),
                    v1_parse_flag_to_bool($point['importance_flag'])
                );
            } else {
                $mark = '';
            }

            //标记列表
            $markList = array(
                'weekly_flag' => v1_parse_flag_to_bool($point['weekly_flag']),
                'monthly_flag' => v1_parse_flag_to_bool($point['monthly_flag']),
                'yearly_flag' => v1_parse_flag_to_bool($point['yearly_flag']),
                'importance_flag' => v1_parse_flag_to_bool($point['importance_flag']),
            );

            $name = $this->parseDate($point['timepoint'])
                . ' (' . $jobInfoHandler->getTimepointTypeDes($point['backup_mode']) . ')';
            //获取时间点是否可用
            $statusFlag = true;
            $timepointStatus = $this->getTimePointStatus($point['merge_status'], $point['operation_status'], $point['virus_scan_status'], $point['integrity_check_status']);
            if (!$timepointStatus['avaliable_flag']) {
                $statusFlag = false;
//                if ($timepointStatus['status'] == 1) {
//                    // 操作中
//                    $name .= $timepointStatus['icon_label'];
//                } else {
//                    // 异常
//                    $name = '<span style="color: #f1416c">' . $name . '</span>' . $timepointStatus['icon_label'];
//                }
            }
            // 存储不在线则无法勾选
            $storageBatchStatusList = StorageHandler::instance()->batchGetStorageStatus([$point['storage_uuid']]);
            $storageBatchStatus = $storageBatchStatusList[0]['storage_online_flag'];
            if (!$storageBatchStatus) {
                $statusFlag = false;
            }

            //检查并添加完备点
            if ($point['backup_mode'] == $backupModes['FULL']) {
                if (!in_array($timepointUuid, $timepoint)) {
                    //根据合并和是否在线展示不同文字信息
                    if (!$archiveStatusStr) {
                        $name .= '(' . xphp_get_lang('UI_PUBLIC_IN_MERGE') . ')';
                    } elseif ($deleteStatusStr) {
                        $name .= '(' . xphp_get_lang('UI_PUBLIC_MERGE_ERROR') . ')';
                    }
                    if (!$storageBatchStatus) {
                        $name .= '(' . xphp_get_lang('UI_PUBLIC_STORAGE_OFF') . ')';
                    }

                    if (!empty($config) && $config['password_auto_flag'] == 2 && !empty($config['password'])) {
                        $name .= '<i class="fa fa-lock"></i>';
                    }
                    $timepoint[] = $timepointUuid;
                    $vmName = $point['current_vm_name'] ?: $point['vm_name'];
                    $node[] = array(
                        'id' => $timepointUuid,
                        'pId' => $point['hypervisor_type'] . $vmUuid . $taskUuid,
                        'name' => $manageFlag ? $name . $mark : $name,
                        'old_name' => $name,
                        //                        'checked' => $vmCheck && $availableFlag,
                        'type' => 3,
                        'vm_uuid' => $point['vm_uuid'],
                        'vm_name' => rawurldecode($vmName),
                        'point_name' => $this->parseDate($point['timepoint']),
                        'platform_uuid' => $point['vcenter_uuid'],
                        'timepoint_uuid' => $timepointUuid,
                        "create_time" => $taskCreateTimeIn,
                        'hypervisor_type' => $point['hypervisor_type'],
                        'node_uuid' => $point['real_node_uuid'] ?: $point['node_uuid'],
                        'storage_uuid' => $point['storage_uuid'],
                        'storage_type' => $point['storage_type'],
                        'task_uuid' => $taskUuid,
                        'path' => htmlspecialchars($point['dir_path']),
                        'version' => $point['version'],
                        'icon' => $this->getTimepointIcon($point['backup_mode']),
                        'title' => htmlspecialchars(rawurldecode($this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks'], $cloudFlag))),
                        'chkDisabled' => !$statusFlag,
                        'config' => $config,
                        'mark_list' => $markList,
                        'remark' => $point['remarks'],
                        'backup_mode' => intval($point['backup_mode']),
                        'integrity_check_flag' => v1_parse_flag_to_bool($point['integrity_check_flag']),
                        'virus_scan_status' => intval($point['virus_scan_status']),
                        'timepoint_status' => $timepointStatus,
                        'isParent' => in_array($timepointUuid, $dependUuidList),
                    );
                    //添加了完全备份时间点继续下一次
                    $pid = $timepointUuid;
                    continue;
                }
            }

            //非完备点
            //根据合并和是否在线展示不同文字信息
            if (!$archiveStatusStr) {
                $name .= '(' . xphp_get_lang('UI_PUBLIC_IN_MERGE') . ')';
            } elseif ($deleteStatusStr) {
                $name .= '(' . xphp_get_lang('UI_PUBLIC_MERGE_ERROR') . ')';
            }
            $vmName = $point['current_vm_name'] ?: $point['vm_name'];
            $node[] = array(
                'id' => $timepointUuid,
                'pId' => $pid,
                'name' => $manageFlag ? $name . ' ' . $mark : $name,
                'old_name' => $name,
                //                    'checked' => $vmCheck && $availableFlag,
                'type' => 4,
                'vm_uuid' => $point['vm_uuid'],
                'vm_name' => rawurldecode($vmName),
                'point_name' => $this->parseDate($point['timepoint']),
                'platform_uuid' => $point['vcenter_uuid'],
                'node_uuid' => $point['real_node_uuid'] ?: $point['node_uuid'],
                'storage_uuid' => $point['storage_uuid'],
                'storage_type' => $point['storage_type'],
                'timepoint_uuid' => $timepointUuid,
                'hypervisor_type' => $point['hypervisor_type'],
                'path' => htmlspecialchars($point['dir_path']),
                'version' => $point['version'],
                'icon' => $this->getTimepointIcon($point['backup_mode']),
                'title' => htmlspecialchars(rawurldecode($this->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks'], $cloudFlag))),
                'chkDisabled' => $disabledFlag || !$availableFlag || $statusFlag,
                'config' => $config,
                'mark_list' => $markList,
                'remark' => $point['remarks'],
                'backup_mode' => intval($point['backup_mode']),
                'integrity_check_flag' => v1_parse_flag_to_bool($point['integrity_check_flag']),
                'virus_scan_status' => intval($point['virus_scan_status']),
                'timepoint_status' => $timepointStatus
            );
        }
        // 这里需要判断下，如果携带了 instant_flag = true则表示还需要返回瞬时恢复的时间点
        if (!empty($params['instant_flag'])) {
            $info1 = (new InstantaneousData())->instantancePoints($params);
            $node = array_merge($node, $info1);
            // 还要去重
            $node = v1_unique_by_two_keys_manual($node, 'id', 'pId');
        }
        // 通过数组分页实现加载更多
        if (isset($offset) && isset($limit)) {
            $node = array_filter($node, function ($item) use ($parentUuid, $vmUuid) {
                if ($parentUuid == $vmUuid) {
                    // 只取该虚拟机下的完备点
                    return $item['type'] == 3 && $item['vm_uuid'] == $vmUuid;
                } else {
                    // 只取该完备点下的非完备点
                    return $item['type'] == 4 && $item['pId'] == $parentUuid;
                }
            });
            $total = count($node);
            $node = array_slice($node, $offset, $limit);
            // 继续添加【加载更多】节点
            if ($node && $offset + $limit < $total) {
                $node[] = [
                    'id' => $node[0]['pId'] . '_loadMore',
                    'pId' => $node[0]['pId'],
                    'name' => '<span class="loadMore font-green-seagreen">' . xphp_get_lang('WEB_FILE_MORE') . '</span>',
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    'nocheck' => true,
                    'isParent' => false,
                    'nextOffset' => $offset + $limit,
                    'moreType' => $node[0]['type'], // 加载更多对应类型的时间点
                    'task_uuid' => $node[0]['task_uuid'],
                    'icon' => $node[0]['icon'],
                    'timepoint_status' => $timepointStatus,
                    'vm_uuid' => $node[0]['vm_uuid'],
                ];
            }
        }

        return ['rows' => $node, 'total' => count($node)];
    }

    /**
     * 删除备份时间点数据
     * @param array $params 参数
     * @return string
     */
    public function deleteRestoreData(array $params): string
    {
        $timepointList = $params['timepoint_list'];
        $vmList = $params['vm_list'];
        if (!empty($vmList)) {
            foreach ($vmList as $vm) {
                $selectTimepoints = $this->getTimepointByVM($vm);
                $timepointList = array_merge($timepointList, $selectTimepoints);
            }
        }
        $timepointList = v1_array_sort($timepointList, 'node_uuid', '', 0, -1);
        $hypervisor_type = $timepointList[0]['hypervisor_type'];

        $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);

        $info = array();
        $nodeUuids = array();
        $timepointUuids = array();
        $timepointUuid = array();
        // 日志信息
        $details = '';
        $i = 0;
        foreach ($timepointList as $d) {
            $i++;
            // 有磁带下的时间点无法删除
            if (xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'] == $d['storage_type']) {
                return $this->muOpResult(false, $operate, xphp_get_lang('UI_TAPE_DELETE_BACKUP_POINT_TIPS'));
            }

            if (!in_array($d['node_uuid'], $nodeUuids)) {
                $nodeUuids[] = $d['node_uuid'];
                $info[] = array(
                    'nodeuuid' => $d['node_uuid'],
                    'hypervisor' => $d['hypervisor_type']
                );
                if (!empty($timepointUuid)) {
                    $timepointUuids[] = $timepointUuid;
                    $timepointUuid = array();
                }

                $timepointUuid[] = $d['timepoint_uuid'];
            } else {
                $timepointUuid[] = $d['timepoint_uuid'];
            }
            // 时间点信息
            $details .= $this->getPointDetails($i,$d['timepoint_uuid']);
        }

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (
            !in_array('global_write', xphp_get_user_info()['permissionArr'])
            && in_array('global_read', xphp_get_user_info()['permissionArr'])
        ) {
            // 根据 $timepointUuid 数组获取所有的用户ID，判读是否是都是自己的
            if (!$this->checkTimepointOperateAuth($operate, $timepointUuid)) {
                return $this->muOpResult(false, $operate, xphp_get_lang('WEB_VM_DATA_NO_OPERATE_PERMISSION'));
            }
        }


        $timepointUuids[] = $timepointUuid;
        $nodeUuidsCount = count($nodeUuids);   //计算nodeuuids数组的长度用于分组发送消息
        $this->paramsCheck($timepointUuids[0][0], $nodeUuids[0]);
        //检测是否在任务中在任务就直接返回
        $uuidList = array();
        foreach ($timepointUuids as $d) {
            $uuidList = array_merge($uuidList, $d);
        }

        //检查时间点是否有任务存在
        $this->taskExist($uuidList, $operate);
        $this->grainTaskExist($uuidList, $operate);
        $this->copyTaskExist($uuidList, $operate);
        $countPoint = 0;
        for ($i = 0; $i < $nodeUuidsCount; $i++) {
            $countPoint += count($timepointUuids[$i]);
            $msg = array(
                'timepoint_uuids' => $timepointUuids[$i]
            );
            $msg = json_encode($msg);
            $mbResult = $this->mbVMMsg($nodeUuids[$i], $info[$i]['hypervisor'], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $vmDesc = in_array($hypervisor_type, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])
            ? xphp_get_lang('WEB_PLATFORM_DES_INSTANCE') : xphp_get_lang('WEB_PLATFORM_DES_VM');
        $descriptionParam = array($details, $countPoint, $vmDesc);
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam);
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        } else {
            $this->systemLog(
                'SYSTEM_LOG_DELETE_BATCH_TIMEPOINT',
                $descriptionParam,
                xphp_get_config('log', 'LOGLEVEL')['ERROR'],
                $mbResult['errorCode']
            );
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    private function getPointDetails($index, $pointUUID)
    {
        $pfDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, vbt.vm_name from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = ? and vbt.timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = $pfDes[intval($data[0]['backup_mode'])] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT') . '：' . $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . xphp_get_lang('UI_PUBLIC_TASK_RNAME') . "：" . $data[0]['task_name'] . "，" . xphp_get_lang('UI_PUBLIC_MODULE_TYPE') . "：" . xphp_get_lang('WEB_PLATFORM_DES_VM') . "，" . $timepointDes . "，" . xphp_get_lang('UI_VCENTER_MACHINE_NAME') . "：" . $data[0]['vm_name'];
        } else {
            $details = xphp_get_lang('UI_PUBLIC_TASK_RNAME') . "：" . $data[0]['task_name'] . "，" . xphp_get_lang('UI_PUBLIC_MODULE_TYPE') . "：" . xphp_get_lang('WEB_PLATFORM_DES_VM') . "，" . $timepointDes . "，" . xphp_get_lang('UI_VCENTER_MACHINE_NAME') . "：" . $data[0]['vm_name'];
        }
        return $details;
    }

    /**
     * 删除单个备份点数据
     * @param array $params 参数
     * @return string
     */
    public function deleteRestoreDataOne(array $params): string
    {
        $timepointUuid = $params['timepoint_uuid'];
        $this->paramsCheck($timepointUuid);
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, vbt.vm_name, 
            bbt.task_uuid, vbt.vcenter_uuid, vbt.vm_uuid, vbt.hypervisor_type
            from bd_backup_timepoint bbt, vm_backup_timepoint vbt 
            where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, [$timepointUuid]);

        $vmUuid = $data[0]['vm_uuid'];
        $platformUuid = $data[0]['vcenter_uuid'];
        $taskUuid = $data[0]['task_uuid'];
        $hypervisor = $data[0]['hypervisor_type'];

        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        //检测是否在任务中在任务就直接返回
        $this->taskExist(array($timepointUuid), $operate);
        $this->grainTaskExist(array($timepointUuid), $operate);
        $this->copyTaskExist(array($timepointUuid), $operate);

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

        $msg = json_encode(['timepoint_uuids' => [$timepointUuid]]);
        $nodeUuid = Node::instance()->getNodeUUIDWithTimepointUUID($timepointUuid);
        $mbResult = $this->mbVMMsg($nodeUuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $pfDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $timepointDes = $data[0]['timepoint'] .
            "(" . $pfDes[intval($data[0]['backup_mode'])] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT') . ")";
        $descriptionParam = array($timepointDes, $data[0]['task_name'], $data[0]['vm_name']);
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_TIMEPOINT', $descriptionParam);
            $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, vm_backup_timepoint vbt
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.deleted_flag = ? and
                    bbt.available_flag = ? and vbt.vm_uuid = ? and vbt.vcenter_uuid = ? and bbt.task_uuid = ? and bbt.timepoint_uuid = ?
                    ";
            $flag = xphp_get_config('app', 'FLAG');
            $dataCount = $this->dbSelect($sqlCount, array($flag['UNSET'], $flag['SET'], $vmUuid, $platformUuid, $taskUuid, $timepointUuid));
            $count = $dataCount[0]['total'];
            return $this->muOpResult($result, $operate, $msg, '', 0, array("count" => intval($count), "id" => $timepointUuid));
        } else {
            $this->systemLog('SYSTEM_LOG_DELETE_ONE_TIMEPOINT', $descriptionParam, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 备份时间点添加备注
     * @param array $params 参数
     * @return array
     */
    public function addRestorePointsRemark(array $params): array
    {
        $pointUuid = $params['timepoint_uuid'];
        $remark = v1_remove_escape($params['remark']);
        $this->paramsCheck($pointUuid);
        $opName = 'BD_BACKUP_POINT_OP_COMMENT';
        $operate = (new PFOpcode())->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (
            !in_array('global_write', xphp_get_user_info()['permissionArr'])
            && in_array('global_read', xphp_get_user_info()['permissionArr'])
        ) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            if (!$this->checkTimepointOperateAuth($operate, [$pointUuid])) {
                return $this->muOpResult(false, $operate, xphp_get_lang('WEB_VM_DATA_NO_OPERATE_PERMISSION'));
            }
        }

        $msg = json_encode(array('timepoint_uuid' => $pointUuid, 'remarks' => $remark));
        return $this->service()->opUnifyPfMsg($opName, $msg);
    }

    /**
     * 备份时间点设置保留标记
     * @param array $params 参数
     * @return string
     */
    public function setRestorePointsGfsMark(array $params): string
    {
        // 非完备点无法设置
        $sql = "select backup_mode from bd_backup_timepoint where timepoint_uuid = ?";
        $backupMode = $this->dbSelect($sql, [$params['timepoint_uuid']])[0]['backup_mode'];
        if ($backupMode != xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
            return $this->muOpResult(false, '', xphp_get_lang('WEB_VM_GFS_TABLE_TIPS4'));
        }

        $info = array(
            'timepoint_uuid' => $params['timepoint_uuid'],
            'item_list' => $params['item_list'],
        );
        $opName = 'BD_BACKUP_POINT_OP_GFS_FLAG_OP';
        $operate = (new PFOpcode())->getOpcodeDes($opName);
        // 操作权限
        if (!$this->checkTimepointOperateAuth($operate, [$params['timepoint_uuid']])) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_VM_DATA_NO_OPERATE_PERMISSION'));
        }

        $msg = json_encode($info);
        $data = $this->service()->mbVMMsg($params['node_uuid'], $params['hypervisor_type'], $opName, $msg);
        return $this->muOpResult($data['result'], '', $data['msg'], '', $data['errorCode']);
    }

    /**
     * 给备份时间点添加星标
     * 暂时支持一个
     * @param array $params
     * @return array
     */
    public function addStar(array $params): array
    {
        $pointUUID = $params['timepoint_uuid'];
        $this->paramsCheck($pointUUID);
        $this->checkTimePointPermission($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_MAKR';
        $msg = json_encode(array('timepoint_uuids' => $msg));
        return $this->service()->opUnifyPfMsg($opName, $msg);
    }

    /**
     * 备份时间点取消星标
     * 暂时支持一个
     * @param array $params
     * @return array
     */
    public function deleteStar(array $params): array
    {
        $pointUUID = $params['timepoint_uuid'];
        $this->paramsCheck($pointUUID);
        $this->checkTimePointPermission($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_UNMARK';
        $msg = json_encode(array('timepoint_uuids' => $msg));
        return $this->service()->opUnifyPfMsg($opName, $msg);
    }

    //todo:public方法

    /**
     * 获取删除的虚拟机对应时间点信息
     * @param array $vmInfo 虚拟机信息[vm_uuid, node_uuid, hypervisor_type, task_uuid]
     * @return array
     */
    public function getTimepointByVM(array $vmInfo): array
    {
        $info = array();
        $sql = "select bbt.timepoint_uuid, bsr.node_uuid, bsr.storage_type 
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr 
                where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = vbt.timepoint_uuid and
    			bbt.task_uuid = ? and vbt.vm_uuid = ? ";
        $sqlParams = array($vmInfo['task_uuid'], $vmInfo['vm_uuid']);
        if (!empty($vmInfo['node_uuid'])) {
            $sql .= ' and bsr.node_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($vmInfo['node_uuid']));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d) {
            $info[] = array(
                'timepoint_uuid' => $d['timepoint_uuid'],
                'node_uuid' => $d['node_uuid'],
                'hypervisor_type' => $vmInfo['hypervisor_type'],
                'storage_type' => $d['storage_type']
            );
        }
        return $info;
    }

    /**
     * 检查需要删除的完备点是否在恢复、瞬时恢复和迁移任务中
     * @param array  $timepointUuids 时间点uuid
     * @param string $operate        操作
     * @return null
     */
    public function taskExist(array $timepointUuids, string $operate)
    {
        //判断完备点是否在瞬时恢复任务中
        $timepointUuids = implode("','", $timepointUuids);
        $sql = "select vi.task_uuid from vm_instant vi join bd_task bt on vi.task_uuid = bt.task_uuid 
            where vi.timepoint_uuid in ('$timepointUuids') and bt.task_status != ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKSTATUS')['STOPPED']));
        if (!empty($data)) {
            exit($this->muOpResult(
                false,
                $operate,
                xphp_get_lang('WEB_VM_DELETE_TIMEPOINT_INSTANT_WARNING'),
                'warning'
            )
            );
        }

        //判断是否在恢复或迁移任务中
        $sqlmore = "select vml.task_uuid from vm_machine_list vml join bd_task bt on vml.task_uuid = bt.task_uuid 
            where vml.timepoint_uuid in ('$timepointUuids') and bt.task_status != ?";
        $datamore = $this->dbSelect($sqlmore, array(xphp_get_config('task', 'TASKSTATUS')['STOPPED']));
        if (!empty($datamore)) {
            exit($this->muOpResult(
                false,
                $operate,
                xphp_get_lang('WEB_VM_DELETE_TIMEPOINT_RECOVER_MOTION_WARNING'),
                'warning'
            )
            );
        }
    }

    /**
     * 得到虚拟机备份时间点的备注信息,
     * 如果有备注添加到虚拟机源路径后面就是
     * @param string $dirPath 路径
     * @param string $remarks 备注
     * @param bool $cloudFlag 是否云平台
     * @return string
     */
    public function getBackupTimepointTreeTitle($dirPath, $remarks, $cloudFlag = false): string
    {
        // 虚拟机源路径 或 实例源路径
        $langPath = $cloudFlag ? xphp_get_lang('WEB_PLATFORM_DC_INSTANCE_SRC_DIR_PATH') : xphp_get_lang('WEB_PLATFORM_DC_VM_SRC_DIR_PATH');
        $title = $langPath . ': ' . $dirPath;
        if (!empty($remarks)) {
            $title .= '  ' . xphp_get_lang('UI_PUBLIC_REMARK') . ': ' . $remarks;
        }
        return urldecode($title);
    }

    /**
     * 找增量点的完备点
     * @param string $timepointuuid uuid
     * @return array
     */
    protected function getFulllPoint($timepointuuid)
    {
        $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname, bsr.node_uuid, bbt.task_uuid, bbt.id,
        bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode,
        bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks, bbt.real_node_uuid,
        vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type
        from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
        where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid
                        and bbt.available_flag = 1 and bbt.import_flag = 2 and bbt.module_type in (2, 9)
                        and data_local_flag = 1 and bbt.timepoint_uuid = ?
        order by vbt.vm_uuid, bbt.timepoint";
        $data = $this->dbSelect($sqlfull, array($timepointuuid));
        if (!empty($data[0]['depend_point_uuid'])) {//不是完备点继续找
            return $this->getFulllPoint($data[0]['depend_point_uuid']);
        } else {
            return $data[0];
        }
    }

    //todo:private方法

    /**
     * 得到恢复按时间点分组显示方式树
     * @param string  $nodeUuid    节点UUID
     * @param boolean $instantFlag 瞬时恢复标志
     * @return array
     */
    private function getTimepointTimeGroup(string $nodeUuid, bool $instantFlag): array
    {
        $sql = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_type, 
                    bbt.module_type, bbt.task_name, bbt.depend_point_uuid, 
                    unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                    vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.hypervisor_type, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                   bbt.module_type in (2,9) and
                       bbt.user_uuid = ? and data_local_flag = ? ";
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array(
            $flag['UNSET'],
            $flag['SET'],
            $flag['UNSET'],
            xphp_get_user_info()['userUuid'],
            $flag['SET']
        );
        if ($nodeUuid) {
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= ' and bsr.node_uuid = ?';
            array_push($sqlParams, $nodeUuid);
        }
        $sql .= " order by  vbt.vm_uuid,  vbt.vm_timepoint_id desc  ";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();

        //定义task vm timepoint
        $hypervisor = array();
        $task = array();
        $vmPlatformLogic = VmPlatform::instance();
        $vmHypervisorTypes = xphp_get_config('vm', 'VMHYPERVISORTYPE');
        $vmHypervisorDes = xphp_get_config('vm', 'VMHYPERVISORDES');
        foreach ($data as $d) {
            if ($instantFlag) {
                //如果是瞬时恢复，hyper-v不支持
                if (intval($d['hypervisor_type']) == $vmHypervisorTypes['VM_HYPERVISOR_TYPE_HYPERV']) {
                    continue;
                }
            }
            $taskUuid = $d['task_uuid'];
            //检查并添加虚拟化类型
            if (!in_array($d['hypervisor_type'], $hypervisor)) {
                $hypervisorType = intval($d['hypervisor_type']);
                $name = $vmHypervisorDes[$hypervisorType];
                $node[] = array(
                    'id' => $d['hypervisor_type'],
                    'pId' => 0,
                    'name' => $name,
                    'title' => $name,
                    'open' => true,
                    'nocheck' => true,
                    'type' => -1,
                    'iconSkin' => $vmPlatformLogic->getHypervisorIcon($hypervisorType, false),
                );
                $hypervisor[] = $hypervisorType;
            }
            //检查并添加task
            if (!in_array($d['hypervisor_type'] . $taskUuid, $task)) {
                $task[] = $d['hypervisor_type'] . $taskUuid;
                $taskAvailable = in_array($taskUuid, $currentTaskUUID);
                $name = $taskAvailable
                    ? $d['task_name']
                    : $d['task_name'] . '(' . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ')';
                if ($d['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT']) {
                    $name = $d['task_name'] . '(' . xphp_get_lang('UI_COPY_DATA') . ')';
                }
                $node[] = array(
                    'id' => $d['hypervisor_type'] . $taskUuid,
                    'pId' => $d['hypervisor_type'],
                    'name' => $name,
                    'open' => false,
                    'nocheck' => true,
                    'type' => 0,
                    'icon' => './img/platform/flag.png',
                    'title' => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': '
                        . date('Y-m-d H:i:s', $d['task_create_time']),
                    'vcenteruuid' => $d['vcenter_uuid'],
                    'taskuuid' => $d['task_uuid'],
                    'hypervisor' => $d['hypervisor_type'],
                    'isParent' => true,
                    'clickshow' => true
                );
            }
        }
        return $node;
    }

    /**
     * 根据任务uuid获取最新的任务修改时间
     * 主要用于时间点树通过group筛选后，任务创建修改时间没有到最新的情况，这里只能从bd_backup_timepoint中获取，因为任务可能已经删除
     * @param string $taskUuid 任务uuid
     * @return string
     */
    private function getTaskNewModifyTimeFromBdbackupTimepoint(string $taskUuid): string
    {
        $sql = "select task_create_time from bd_backup_timepoint where task_uuid = ? order by id desc limit 0, 1";
        $data = $this->dbSelect($sql, array($taskUuid));
        return $data[0]['task_create_time'];
    }

    /**
     * 检查时间点是否有细粒度任务存在
     * @param array  $timepointUuids 时间点uuid
     * @param string $operate        操作
     * @return null
     */
    public function grainTaskExist(array $timepointUuids, string $operate)
    {
        $points = implode("','", $timepointUuids);
        $sql = "select vm_grain_info_id from vm_grain_info vgi join bd_task bt on vgi.task_uuid = bt.task_uuid 
            where timepoint_uuid in ('$points') and bt.task_status in (?, ?)";
        $data = $this->dbSelect($sql, [
            xphp_get_config('db', 'DB_TASK_STATUS')['WAITING'],
            xphp_get_config('db', 'DB_TASK_STATUS')['RUNNING']
        ]);
        if (!empty($data)) {
            exit($this->muOpResult(
                false,
                $operate,
                xphp_get_lang('WEB_VM_DELETE_TIMEPOINT_GRAIN_WARNING'),
                'warning'
            )
            );
        }
    }

    /**
     * 检查时间点所在任务是否是副本源
     * 副本任务等待中且备份任务未被副本/副本任务运行中 无法删除
     * @param array $timepointUuids 时间点uuid
     * @param string $operate 操作
     * @return null
     */
    private function copyTaskExist(array $timepointUuids, string $operate)
    {
        $points = implode("','", $timepointUuids);
        $sql = "select bbt.task_uuid from bd_backup_timepoint bbt 
            join copy_list cl on bbt.task_uuid = cl.source_task_uuid
            join bd_task bt on cl.task_uuid = bt.task_uuid 
            where bbt.timepoint_uuid in ('$points') 
              and (bt.task_status = 1 and isnull(cl.handled_timepoint_list) or bt.task_status = 2)";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(
                false,
                $operate,
                xphp_get_lang('WEB_VM_DELETE_TIMEPOINT_BACKUP_TASK_COPY_WARNING'),
                'warning'
            )
            );
        }
    }

    /**
     * 时间点操作鉴权
     * @param string $operate
     * @param array $timepointUuids
     * @return bool
     */
    private function checkTimepointOperateAuth(string $operate, array $timepointUuids): bool
    {
        $sqlAuth = "select user_uuid from bd_backup_timepoint where timepoint_uuid in ('"
            . implode("','", $timepointUuids) . "')";
        $data = (array) $this->dbSelect($sqlAuth);
        $userArr = array_unique(array_column($data, 'user_uuid'));
        return xphp_check_operate('vmprotect_operate', $userArr);
    }
}
