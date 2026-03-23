<?php

namespace app\v2\vm\v0\logic;

use app\v2\common\logic\Base;
use app\v2\resources\v0\logic\Index as ResourceHandler;

/**
 * note          虚拟机概览
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmOverview extends Base
{
    /**
     * 获取所有虚拟机个数（已弃用）
     * @param array $params 参数
     * @return array
     */
    public function getAllVms(array $params): array
    {
        $subModuleType = $params['sub_module_type'] ?? 1;
        //获取所有虚拟机
        $sql = "select vt.tree_id from vm_tree vt 
                join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid
                left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid 
                where  vt.display_mode = ?
                and vt.type = ? ";
        if (empty($_SESSION['tenantuuid'])) {
            // 不能看租户内部的资源
            $sql .= " and mut.tenant_uuid is null";
        }

        $publicTypes = implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']);
        $privateTypes = implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack']);
        if (xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'] == $subModuleType) {
            $sql .= " and vv.hypervisor_type in (" . $publicTypes . ")";
        } elseif (xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'] == $subModuleType) {
            $sql .= " and vv.hypervisor_type in (" . $privateTypes . ")";
        } else {
            $sql .= " and vv.hypervisor_type not in (" . $publicTypes . ") and vv.hypervisor_type not in (" . $privateTypes . ")";
        }

        $sqlParams = array(
            xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'],
            xphp_get_config('vm', 'VM_TREE_TYPE')['VM']
        );
        // 查看权限
        $userInfo = xphp_get_user_info();
        if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' != $userInfo['userUuid']) {
            $authUser = $_SESSION['authUser']['vmprotect_look'] ?? [];
            $useruuidArr = array_merge([$userInfo['userUuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and vv.user_uuid in " . $useruuidArr;
        }

        $data = $this->dbSelect($sql, $sqlParams);
        return array(
            'vm_length' => count($data)
        );
    }

    /**
     * 获取虚拟机概览列表
     * @param array $params 参数
     * @return array
     */
    public function getVMReport(array $params): array
    {
        $subModuleType = $params['sub_module_type'] ?? 1;
        $start = $params['offset'] ?? 0;
        $length = $params['limit'] ?? 10;
        $sortColumn = $params['sort'] ?? 'vm_name';
        $sortType = $params['order'] ?? 'asc';
        $searchValue = $params['search_value'];

        // 虚拟化中心详情新增参数
        $platformUuid = $params['platform_uuid'];  //虚拟化中心uuid
        if ($platformUuid) {
            $hypervisorType = $params['hypervisor_type'];    //虚拟化类型
            $displayMode = $params['display_mode'];        //显示模式
            $parentNodes = $params['new_node'] ?? $params['node'];
            $nodeUuids = json_decode($parentNodes, true);           //所有父节点
            $platformFlag = $params['platform_flag'] ?? false;  //是否是平台
            $uuidStr = VmPlatform::instance()->getParentUUIDNodeStr($platformFlag, $platformUuid, $displayMode, $nodeUuids);
        }

        $userInfo = xphp_get_user_info();
        //获取用户已分配的虚拟机
        $resourceHandler = ResourceHandler::instance();
        $vmResource = $resourceHandler->pGetUserResourceVM($userInfo['userUuid'], "tree_id", "desc", $limit = 0, "all", [], $subModuleType);
        $vmList = array();
        $vcenterList = array();
        foreach ($vmResource['data'] as $vm) {
            if (!in_array($vm['vcenter_uuid'], $vcenterList)) {
                $vcenterList[] = $vm['vcenter_uuid'];
            }
            if (!in_array($vm['uuid'], $vmList)) {
                $vmList[] = $vm['uuid'];
            }
        }

        //获取所有虚拟机
        $sql = "select vt.tree_id, vt.vcenter_uuid, vt.power_state, vv.user_uuid, vv.hypervisor_type, vv.vcenter_ip, vv.detail, 
                vv.username, vt.name, vt.uuid, vt.dir_path,vt.detail as vm_detail, bu.user_name, 
                mur.user_uuid as owner_uuid, bu2.user_name as owner_name,b_tt.tenant_name  
                from vm_tree vt join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                left join mt_user_tenant mut_v on vv.user_uuid = mut_v.user_uuid 
                join bd_user bu on vv.user_uuid = bu.user_uuid 
                left join mt_user_resource mur on vt.uuid = mur.vm_uuid
                left join bd_user bu2 on mur.user_uuid = bu2.user_uuid 
                left join mt_user_tenant mut on mur.user_uuid = mut.user_uuid
                left join bd_tenant b_tt on mut.tenant_uuid = b_tt.tenant_uuid
                where vt.display_mode = ? and vt.type = ? ";
        if ($platformUuid) {
            $sql .= " and vt.parent_uuid in ('" . $uuidStr . "') and vt.vcenter_uuid = '{$platformUuid}'";
        }
        if (empty($_SESSION['tenantuuid'])) {
            // 不能看租户内部的资源
            $sql .= " and mut_v.tenant_uuid is null";
        }

        if (!$platformUuid) {
            $publicTypes = implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']);
            $privateTypes = implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack']);
            if (xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'] == $subModuleType) {
                $sql .= " and vv.hypervisor_type in (" . $publicTypes . ")";
            } elseif (xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'] == $subModuleType) {
                $sql .= " and vv.hypervisor_type in (" . $privateTypes . ")";
            } else {
                $sql .= " and vv.hypervisor_type not in (" . $publicTypes . ") and vv.hypervisor_type not in (" . $privateTypes . ")";
            }
        }

        $vmTreeType = xphp_get_config('vm', 'VM_TREE_TYPE');
        $sqlParams = array(
            xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'],
            $vmTreeType['VM']
        );
        $accurateFlag = $params['accurate_flag'];
        if ($accurateFlag) {
            $hypervisor = intval($params['hypervisor_type']);
            $backupFlag = intval($params['backup_status']);
            $vcenteruuid = $params['platform_uuid'];
            $vmName = $params['vm_name'];
            $searchvmIP = $params['vm_ip'];
            $hostName = $params['host_name'];
            $vmCluster = $params['vm_cluster'];
            $taskName = $params['job_name'];
            $startTime = $params['start_time'];
            $endTime = $params['end_time'];

            //虚拟机名
            if ($this->checkEmpty($vmName)) {
                $sql .= " and vt.name like ? ";
                $sqlParams = array_merge($sqlParams, array('%' . $vmName . '%'));
            }
            //虚拟机路径
            if ($this->checkEmpty($vmCluster)) {
                $sql .= " and vt.parent_uuid in (
                    select distinct vt2.uuid from vm_tree vt2 where vt2.type in (?, ?) and vt2.name like ?  
                    union select distinct vt3.uuid from vm_tree vt3 join vm_tree vt4 on vt3.parent_uuid = vt4.uuid 
                        where vt4.type in (?, ?, ?) and vt4.name like ? 
                )";
                $sqlParams = array_merge($sqlParams, array(
                    $vmTreeType['CLUSTER'],
                    $vmTreeType['OVIRT_CLUSTER'],
                    '%' . $vmCluster . '%',
                    $vmTreeType['CLUSTER'],
                    $vmTreeType['OVIRT_CLUSTER'],
                    $vmTreeType['POOL'], // sangfor scp通过集群搜索资源池的虚拟机
                    '%' . $vmCluster . '%',
                ));
            }
            //虚拟化中心
            if (!empty($vcenteruuid)) {
                $sql .= " and vt.vcenter_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($vcenteruuid));
            }
            //虚拟化中心
            if (!empty($hypervisor)) {
                $sql .= " and vv.hypervisor_type = ? ";
                $sqlParams = array_merge($sqlParams, array($hypervisor));
            }
            //宿主机或租户
            if ($this->checkEmpty($hostName)) {
                $sql .= " and vt.host_uuid in (select distinct vh.host_uuid from vm_host vh where vh.host_name like ? or vh.host_ip like ?)";
                $sqlParams = array_merge($sqlParams, array('%' . $hostName . '%', '%' . $hostName . '%'));
            }

        } else if ($this->checkEmpty($searchValue)) {
            $searchValue = str_replace('\\', '\\\\', $searchValue); //like查询中的"\"需要转成"\\\\"才会生效
            $backupFlag = false;
            $sql .= " and (vt.name like ? or vt.dir_path like ?)";
            $sqlParams = array_merge($sqlParams, array('%' . $searchValue . '%', '%' . $searchValue . '%'));
        }
        $sql .= " order by vt.name ";
        $data = $this->dbSelect($sql, $sqlParams);
        //获取所有正在备份任务中的虚拟机
        $vcenter = VmPlatform::instance();
        $vmInbackupInfo = $vcenter->getBackupVMInfo();
        $vminfo = array();
        $i = 1;
        $searchCount = 0;
        if (!empty($data)) {
            $lastBackupInfoList = $this->getVMReportLastBackupInfo($data);
            $configUserType = xphp_get_config('user', 'USERTYPE');
            $configTimeSpace = xphp_get_config('app', 'TIMESPACE');
            $configNullSpace = xphp_get_config('app', 'NULLSPACE');
            $treeTypeConf = xphp_get_config('vm', 'VM_TREE_TYPE');
            $vmGroupConf = xphp_get_config('vm', 'VMHYPERVISORGROUP');
            foreach ($data as $d) {
                $tenantName = '';
                if (!empty($d['tenant_name']) && $userInfo['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
                    $tenantName = '(' . $d['tenant_name'] . ')';
                }
                //如果是不属于当前用户的虚拟机需要排除
                if (v2_auth_need_check_look() && $userInfo['userUuid'] != $d['user_uuid'] && $userInfo['userType'] != $configUserType['manager']) {
                    if (in_array($d['uuid'], $vmList) && in_array($d['vcenter_uuid'], $vcenterList)) {

                    } else {
                        continue;
                    }
                }

                // 操作权限
                $authUser = $_SESSION['authUser']['vmprotect_operate'] ?? [];
                $userUuids = $d['owner_uuid'] ? [$d['owner_uuid']] : [$d['user_uuid']];
                $operateFlag = xphp_check_operate($userInfo['userUuid'], $userUuids, $authUser) || !v2_auth_need_check_look();

                $searchCount++;
                $lastBackupInfo = array(
                    'lastbackuptime' => $configTimeSpace,
                    'backuptask' => $configNullSpace,
                    'backupcount' => $configNullSpace,
                    'backupstorage' => $configNullSpace,
                    'backupstorageSize' => 0
                );

                $vmBackupFlag = $vcenter->getVMInBackupFlag($vmTreeType['VM'], $d['uuid'], $d['vcenter_uuid'], $vmInbackupInfo, $d['hypervisor_type'], $treeTypeConf, $vmGroupConf);
                //虚拟机是否在任务中
                if (!empty($lastBackupInfoList)) {
                    foreach ($lastBackupInfoList as $list) {
                        if ($list['vm_uuid'] == $d['uuid'] && $list['vcenter_uuid'] == $d['vcenter_uuid']) {
                            $lastBackupInfo = $list;
                            if (!$vmBackupFlag) {
                                $tasks_arrays = $this->dbSelect('select task_name from bd_task');
                                $tasks_array = array();
                                foreach ($tasks_arrays as $t) {
                                    $tasks_array[] = $t['task_name'];
                                }
                                //任务是否还存在
                                if (in_array($lastBackupInfo['backuptask'], $tasks_array)) {
                                    $lastBackupInfo['backuptask'] = $configNullSpace;
                                } else {
                                    $lastBackupInfo['backuptask'] = $lastBackupInfo['backuptask'] . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
                                }
                            }
                        }
                    }
                }
                if ($vmBackupFlag) {
                    $idStr = $d['vcenter_uuid'] . '_' . $d['uuid'];
                    $lastBackupInfo['backuptask'] = $vmInbackupInfo['tasklist'][$idStr];
                }
                //匹配查询备份中任务名
                if ($accurateFlag && $this->checkEmpty($taskName) && stripos($lastBackupInfo['backuptask'], $taskName) === false) {
                    $searchCount--;
                    continue;
                }
                //匹配最后一次备份时间
                if ($accurateFlag && $startTime && $endTime) {
                    if (strtotime($startTime) < strtotime($lastBackupInfo['lastbackuptime']) && strtotime($endTime) > strtotime($lastBackupInfo['lastbackuptime'])) {

                    } else {
                        $searchCount--;
                        continue;
                    }
                }
                if ($backupFlag && $vmBackupFlag != v2_parse_flag_to_bool($backupFlag)) {
                    $searchCount--;
                    continue;
                }

                $vmIp = $configNullSpace;

                //获取虚拟机IP
                if (!empty($d['vm_detail'])) {
                    $detail = json_decode($d['vm_detail'], true);
                    if ($this->isHypervisorHaveIp($d['hypervisor_type'])) {
                        $network = $detail['network_list'];
                        $vmIp = '';
                        $ipList = array();
                        if (!empty($network)) {
                            foreach ($network as $net) {
                                $list = $net['ip_list'];
                                foreach ($list as $ip) {
                                    $ipList[] = $ip;
                                    $vmIp .= $ip . PHP_EOL;
                                }
                            }
                        }
                        if ($accurateFlag && !empty($searchvmIP)) {
                            //改为模糊搜索
                            $ipFlag = false;
                            foreach ($ipList as $ip) {
                                if (false !== strpos($ip, $searchvmIP)) {
                                    $ipFlag = true;
                                    break;
                                }
                            }
                            if (!$ipFlag) {
                                $searchCount--;
                                continue;
                            }
                        }

                    } else if ($accurateFlag && !empty($searchvmIP)) {
                        //通过IP搜索,没有虚拟机详情的一并排除
                        $searchCount--;
                        continue;
                    }
                } else if ($accurateFlag && !empty($searchvmIP)) {
                    //通过IP搜索,没有虚拟机详情的一并排除
                    $searchCount--;
                    continue;
                }
                $vminfo[] = array(
                    //                    '<input type="checkbox" name="id[]" value="' . $d['tree_id'] . '">',
                    'no' => 1,
                    'vm_uuid' => $d['uuid'],
                    'vm_name' => $d['name'],
                    'power_state' => intval($d['power_state']),
                    'vm_ip' => $vmIp,
                    'platform_uuid' => $d['vcenter_uuid'],
                    'platform_ip' => $vcenter->getVcenterIp($d['vcenter_ip'], intval($d['hypervisor_type']), $d['username']),
                    'backup_flag' => $vmBackupFlag,
                    'last_backup_time' => $lastBackupInfo['lastbackuptime'],
                    'backup_job' => $lastBackupInfo['backuptask'],
                    'backup_count' => $lastBackupInfo['backupcount'],
                    'backup_storage' => $lastBackupInfo['backupstorage'],
                    'user_name' => $d['owner_uuid'] ? $d['owner_name'] . $tenantName : $d['user_name'] . $tenantName, //被分配的虚拟机显示被分配的用户名
                    'user_uuid' => $d['owner_uuid'] ?: $d['user_uuid'],
                    'operations' => $operateFlag && !$vmBackupFlag ? array(4, 5) : array(),
                    'details' => array(
                        'tree_id' => $d['tree_id'],
                        'dir_path' => $d['dir_path'],
                        'platform_uuid' => $d['vcenter_uuid'],
                        'vm_uuid' => $d['uuid'],
                        'hypervisor_type' => $d['hypervisor_type'],
                        'display_mode' => xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER']
                    ),
                    'dir_path' => $d['dir_path'],
                    'backup_storage_size' => $lastBackupInfo['backupstorageSize'],
                );
            }
        }
        //存储空间排序取int字节排序
        if ($sortColumn == 'backup_storage') {
            $sortColumn = 'backup_storage_size';  //处理导出获取的是字节int类型
        }
        $vminfo = v2_array_sort($vminfo, $sortColumn, $sortType, $start, $length);  //排序

        //重新赋值编号
        foreach ($vminfo as &$vm) {
            $vm['no'] = $i++;
        }

        $records = array("rows" => $vminfo);
        $records["total"] = $searchCount;

        return $records;
    }

    /**
     * 得到虚拟机上次备份时间/备份任务/备份次数/当前备份所占用存储空间等信息
     * @param array $vmList uuid, vcenter_uuid
     * @return array
     */
    public function getVMReportLastBackupInfo(array $vmList): array
    {
        $vmlistDes = "";
        foreach ($vmList as $key => $vm) {
            if ($key == count($vmList) - 1) {
                $vmlistDes .= '("' . $vm['vcenter_uuid'] . '","' . $vm['uuid'] . '")';
            } else {
                $vmlistDes .= '("' . $vm['vcenter_uuid'] . '","' . $vm['uuid'] . '"),';
            }
        }

        $sql = "select max(unix_timestamp(bbt.timepoint)) as timepoint, vbt.vm_uuid, vbt.vcenter_uuid, bbt.task_name, 
            bbt.task_uuid, count(distinct bbt.timepoint) as count, sum(bbt.write_size) as real_size, 
            sum(bbt.total_size) as total_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt
            where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.available_flag = ?
            and (vbt.vcenter_uuid, vbt.vm_uuid) in (" . $vmlistDes . ") and bbt.task_type = ? and bbt.module_type = ? 
            group by vbt.vm_uuid, vbt.vcenter_uuid order by bbt.timepoint desc";
        $sqlParams = array(
            xphp_get_config('app', 'FLAG')['SET'],
            xphp_get_config('task', 'TASKTYPE')['BACKUP'],
            xphp_get_config('module', 'MODULE_TYPE')['VM']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $i = 0;
        $backupStorage = 0;

        $list = array();
        foreach ($data as $d) {
            //备份次数先用历史任务成功个数统计，未匹配到历史任务获取时间点个数
//             $count = $d['task_count'];
//             if(empty($count)){
            $count = $d['count'];
            //             }
            $info = array();
            $name = $d['task_name'];
            //上次备份时间
            $info['lastbackuptime'] = $this->parseDate($d['timepoint']);
            //备份任务
            $info['backuptask'] = $name;
            //备份次数
            $info['backupcount'] = $count;
            $info['timepointcount'] = $d['count'];
            //             $info['backupstorage'] = $utils->calSize(intval($d['real_size']));
            //获取虚拟机 备份写入大小和总大小
//             $sizeInfo = $this->getVmStorageSize($d['vm_uuid'], $d['vcenter_uuid']);
            //备份数据大小
            $info['backupstorage'] = v2_calsize($d['real_size'], true);
            $info['backupstorageSize'] = $d['real_size'];
            //虚拟机备份总大小
            $info['totalsizeDes'] = v2_calsize($d['total_size'], true);
            $info['totalsize'] = $d['total_size'];
            $info['vm_uuid'] = $d['vm_uuid'];
            $info['vcenter_uuid'] = $d['vcenter_uuid'];

            $list[] = $info;
        }
        return $list;
    }

    /**
     * 获取已添加虚拟化中心列表
     * @param array $params
     * @return array
     */
    public function getVcenterList(array $params)
    {
        $subModuleType = $params['sub_module_type'] ?? 1;
        $sql = 'select vcenter_uuid, vcenter_ip, vcenter_name, hypervisor_type from vm_vcenter ';

        $publicTypes = implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']);
        $privateTypes = implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack']);
        if ($params['all_type_flag']) {
            $sql .= " where hypervisor_type not in (" . $publicTypes . ")";
        } else {
            if (xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'] == $subModuleType) {
                $sql .= " where hypervisor_type in (" . $publicTypes . ")";
            } elseif (xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'] == $subModuleType) {
                $sql .= " where hypervisor_type in (" . $privateTypes . ")";
            } else {
                $sql .= " where hypervisor_type not in (" . $publicTypes . ") and hypervisor_type not in (" . $privateTypes . ")";
            }
        }
        // 获取非分配资源的权限
        if (v2_auth_need_check_look()) {
            $resourceUuidSql = v2_auth_get_users(VmPlatform::instance()->getUserAuthKeyBySubModule($subModuleType));
            $sql .= " and user_uuid in $resourceUuidSql";
        }

        $data = $this->dbSelect($sql);
        $info = array();
        $hypervisorDes = xphp_get_config('vm', 'VMHYPERVISORDES');
        foreach ($data as $d) {
            $info[] = array(
                "platform_uuid" => $d['vcenter_uuid'],
                "platform_ip" => $d['vcenter_ip'],
                "hypervisor_text" => $hypervisorDes[$d['hypervisor_type']]
            );
        }

        return ['rows' => $info];
    }

    /**
     * 虚拟化是否支持ip显示
     * @param $hypervisor
     * @return bool
     */
    public function isHypervisorHaveIp($hypervisor): bool
    {
        $vmHypervisorGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        $vmHypervisorType = xphp_get_config('vm', 'VMHYPERVISORTYPE');
        return in_array(intval($hypervisor), $vmHypervisorGroup['openstack'])
            || in_array(intval($hypervisor), [
                $vmHypervisorType['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_SANGFOR_KVM'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_VMWARE'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_HYPERV'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_INSPUR_VVDK'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_H3C_KVM'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_WINHONG_KVM'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_SMARTX_KVM'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_XFUSION_KVM'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_H3C_CAS_CVD'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_LENOVO_AIO'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_VOLC'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_KSPHERE'],
                $vmHypervisorType['VM_HYPERVISOR_TYPE_ARCFRA_KVM'],
            ]);
    }
}
