<?php

namespace app\v2\vm\v0\logic;

use app\v2\backupData\v0\logic\DataManage;
use app\v2\common\logic\Backup;
use app\v2\common\logic\Recover;
use app\v2\job\v0\logic\JobController;
use app\v2\opcode\PfOpcode;
use app\v2\opcode\VmOpcode;
use app\v2\recovery\v0\logic\RecoveryJobController;
use app\v2\recovery\v0\service\Service as RecoveryService;
use app\v2\resources\v0\logic\Index as ResourceHandler;
use app\v2\resources\v0\logic\Node;
use app\v2\system\v0\logic\Index as SystemHandler;
use app\v2\tenant\v0\logic\Tenant;
use xphp\BLLHandler;
use app\v2\complete_machine_os\v0\service\Service as CMService;

/**
 * note          虚拟机 之恢复管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmRecover extends Recover
{
    // 恢复方式：1-新建虚拟机，2-指定虚拟机
    const VM_RECOVERY_WAY_NEW = 1;
    const VM_RECOVERY_WAY_SPECIFIC = 2;

    /**
     * 获取恢复目标虚拟化中心
     * @param array $params
     * @return array
     */
    public function getRecoverVcenter(array $params): array
    {
        $subModuleType = $params['sub_module_type'];
        $tenantFlag = $params['tenant_flag'];    //租户处
        $hypersior = $params['hypervisor_type'];   //虚拟化类型，多个以","分隔
        $instantFlag = $params['instant_flag'];  //瞬时恢复标志
        $motionFlag = $params['motion_flag'];  //迁移标志
        $oneHypersiorFlag = $params['one_hypervisor_flag'];    //是否只显示一个虚拟化,原平台恢复和迁移的时候用
        $targetHypervisor = $params['target_hypervisor_type'] ?? []; // 目标虚拟化类型
        $excludeHypervisor = $params['exclude_hypervisor_type'] ?? []; // 排除的目标虚拟化类型

        if ($motionFlag) {
            // 迁移时排除scp/hyperv
            $excludeHypervisor[] = xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'];
            $excludeHypervisor[] = xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV'];
        }

        // 是否显示容灾演练平台
        $emdFlag = $params['emd_flag'];
        $emdHypervisorType = xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_EMD'];

        $sql = "select vv.user_uuid, vcenter_uuid, vcenter_ip, nickname, vcenter_flag, 
                hypervisor_type, vcenter_name, detail, username, mut.tenant_uuid from vm_vcenter vv 
                LEFT JOIN mt_user_tenant mut on vv.user_uuid = mut.user_uuid where online_flag = ? ";
        $sqlParams = array(xphp_get_config('app', 'FLAG')['SET']);
        if (empty($_SESSION['tenantuuid'])) {
            // 不能看租户内部的资源
            $sql .= " and mut.tenant_uuid IS NULL ";
        }

        $privateTypes = implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud']);
        $publicTypes = implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']);
        if ($subModuleType) {
            if (xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'] == $subModuleType) {
                $sql .= " and hypervisor_type in ({$privateTypes}) ";
            } elseif (xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'] == $subModuleType) {
                $sql .= " and hypervisor_type in ({$publicTypes}) ";
            } else {
                $sql .= " and hypervisor_type not in ({$privateTypes}) and hypervisor_type not in ({$publicTypes})";
            }
        }
        if ($oneHypersiorFlag) {
            $sql .= " and hypervisor_type = ? ";
            $sqlParams = array(xphp_get_config('app', 'FLAG')['SET'], $hypersior);
        }
        if ($targetHypervisor) {
            $targetHypervisor = implode(',', $targetHypervisor);
            $sql .= " and hypervisor_type in ({$targetHypervisor}) ";
        }
        if ($excludeHypervisor) {
            $excludeHypervisor = implode(',', $excludeHypervisor);
            $sql .= " and hypervisor_type not in ({$excludeHypervisor}) ";
        }

        // 获取分配资源的权限-operate
        if (v2_auth_checks()) {
            // not admin or global-operator
            $resourceUuidSql = v2_auth_get_source_by_type(VmPlatform::instance()->getResourceTypeBySubModule($subModuleType), 'uuid');
            $sqlNew = " EXISTS (select vcenter_uuid from 
                                        (select DISTINCT vcenter_uuid
                                            from vm_tree
                                             where {$resourceUuidSql}
                                        ) v2_auth_all2
                                 where v2_auth_all2.vcenter_uuid = vv.vcenter_uuid) ";
            $sql .= " and {$sqlNew}";
        }

        $sql .= " order by hypervisor_type ";
        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();

        $tenantVcenter = [];
        //        $allVcenterFlag = false;  //租户内是否运行恢复到全部宿主机
        //如果是租户内用户操作，检测是否已配置指定虚拟化中心
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['recover']) {
                $tenantVcenter = $settings['recover']['vcenter'];
                //                $allVcenterFlag = $settings['recover']['hosttype'] == "0";
                if (is_string($tenantVcenter)) {
                    $tenantVcenter = [$tenantVcenter];
                }
            }
        }
        // 获取可恢复的目标类型
        $recoveryHypervisorArr = v2_license_get_v2v(explode(',', $hypersior));
        if (!in_array(1000, $recoveryHypervisorArr)) {
            $emdFlag = false;
        }

        foreach ($data as $d) {
            if (!empty($_SESSION['tenantuuid'])) {
                // 获取已分配的或租户自有的
                if (!in_array($d['vcenter_uuid'], $tenantVcenter) && $_SESSION['tenantuuid'] != $d['tenant_uuid'])
                    continue;
            }
            //如果是瞬时恢复排除其他虚拟化恢复到smartx/hyper-v/hcs/xhere
            if (
                $instantFlag && (
                    intval($d['hypervisor_type']) == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SMARTX_KVM']
                    || intval($d['hypervisor_type']) == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV']
                    || intval($d['hypervisor_type']) == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK']
                    || intval($d['hypervisor_type']) == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XHERE']
                    || intval($d['hypervisor_type']) == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_ARCFRA_KVM']
                )
            )
                continue;

            if (!in_array($d['hypervisor_type'], $recoveryHypervisorArr) && !$tenantFlag) {
                //如果目标虚拟化未授权,不能恢复到这个虚拟化,跳过
                continue;
            }

            if (
                in_array($d['hypervisor_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])
                && ($instantFlag || $motionFlag)
            ) {
                // 瞬时恢复和迁移排除公有云平台
                continue;
            }

            $node = array(
                "id" => $d['vcenter_uuid'],
                "pId" => 0,
                "name" => $this->getRecoverHostName($d['hypervisor_type'], $d),
                "open" => false,
                "isParent" => true,
                "nocheck" => true,
                "hypervisor" => intval($d['hypervisor_type']),
                "type" => 1,
                "iconSkin" => VmPlatform::instance()->getHypervisorIcon($d['hypervisor_type'], false)
            );
            $tree[] = $node;
        }

        if ($emdFlag) {
            // 包含容灾演练平台
            $tree[] = [
                "id" => 'emd',
                "pId" => 0,
                "name" => xphp_get_lang('UI_VM_MACHINE_MANAGER'),
                "open" => false,
                "isParent" => true,
                "nocheck" => true,
                "hypervisor" => $emdHypervisorType,
                "type" => 1,
                "iconSkin" => ''
            ];
        }

        return ['rows' => $tree, 'count' => count($tree)];
    }

    /**
     * 异步获取恢复目标虚拟化平台下的主机/集群
     * @param array $params
     * @return array|string
     */
    public function getSyncRecoveryVcenter(array $params)
    {
        $hostuuid = $params['host_uuid'];    //获取虚拟实验室修改宿主机uuid
        $id = $params['platform_uuid'];
        $pid = intval($params['pid']);
        $nocheck = $params['nocheck_flag'] ?? false;
        $refresh = $params['refresh_flag'] ?? false;
        $this->paramsCheck($id, $pid);

        //说明一下,此处pid刚好是子模块号
        $hypervisor = $pid;

        if (in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['vmware'])) {
            //如果是VMware
            $showtype = xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_VM'];
        } else {
            //如果是XenServer
            $showtype = xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];
        }

        if ($refresh) {
            //如果是刷新,刷新后再从数据库取
            // $opName = 'VM_VCENTER_OP_REFLASH';
            $mbResult = VmPlatform::instance()->refreshVmPlatform($hypervisor, $id, $showtype);

            $result = $mbResult['result'];
            if (!$result) {
                //如果刷新失败,返回错误消息
                // $operate = $this->opcodeHandler->getOpcodeDes($opName);
                $operate = xphp_get_lang('WEB_VM_VCENTER_SYNC');
                return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
            }
        }

        $vmTreeTypes = [
            xphp_get_config('vm')['VM_TREE_TYPE']['DATACENTER'],
            xphp_get_config('vm')['VM_TREE_TYPE']['CLUSTER'],
            xphp_get_config('vm')['VM_TREE_TYPE']['HOST']
        ];
        if (xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_EMD'] == $hypervisor) {
            // 获取容灾演练节点
            $node = [];
            $sql = "select bn.node_uuid, bn.ip  
                from bd_emd be join bd_node bn on be.node_uuid = bn.node_uuid";
            $data = $this->dbSelect($sql);
            foreach ($data as $d) {
                $node[] = array(
                    "id" => $d['node_uuid'],
                    "pid" => 'emd',
                    "pId" => 'emd',
                    "name" => $d['ip'],
                    "isParent" => false,
                    "sid" => $d['node_uuid'],
                    "nocheck" => false,
                    "type" => 2,
                    "ip" => '',
                    "hypervisor" => $pid,
                    "icon" => "./img/vm/host.png",
                    "clickShow" => true,
                    //用于点击宿主机的时候获取虚拟机信息后台刷新标志
                    "refresh" => false,
                    "vcuuid" => $id,
                    "chkDisabled" => false,
                    "hypervisor_des" => xphp_get_config('vm')['VMHYPERVISORDES'][intval($pid)],
                    "checked" => false,
                    "online_flag" => true
                );
            }
            array_multisort(array_column($node, 'name'), SORT_ASC, $node);
            return ['rows' => $node, 'total' => count($node)];
        }

        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
            // 获取公有云平台
            $platforms = [];
            $sql = "select vcenter_ip, vcenter_uuid, nickname from vm_vcenter where vcenter_uuid = ? limit 1";
            $data = $this->dbSelect($sql, [$id]);
            foreach ($data as $d) {
                $platforms[] = array(
                    "id" => $d['vcenter_uuid'],
                    "pid" => $id,
                    "pId" => $id,
                    "name" => $d['nickname'],
                    "isParent" => false,
                    "sid" => $d['vcenter_uuid'],
                    "nocheck" => false,
                    "type" => 2,
                    "ip" => '',
                    "hypervisor" => $pid,
                    "icon" => "./img/vm/vcenter.png",
                    "clickShow" => true,
                    //用于点击宿主机的时候获取虚拟机信息后台刷新标志
                    "refresh" => false,
                    "vcuuid" => $id,
                    "chkDisabled" => false,
                    "hypervisor_des" => xphp_get_config('vm')['VMHYPERVISORDES'][intval($pid)],
                    "checked" => false,
                    "online_flag" => true
                );
            }
            return ['rows' => $platforms, 'total' => count($platforms)];
        }

        if (
            xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV'] == $hypervisor
            || xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SMARTX_KVM'] == $hypervisor
            || xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_ARCFRA_KVM'] == $hypervisor
        ) {
            // hyperv/smartx需特殊处理
            $sql = "select vt.type, vt.name, vt.uuid, vt.parent_uuid, vh.host_name, vh.host_ip, vh.host_uuid, vh.host_id, vh.authorization_flag, vh.online_flag
                from vm_tree vt left join vm_host vh on vt.uuid = vh.host_uuid and vt.type = ? and vt.vcenter_uuid = vh.vcenter_uuid 
                where vt.vcenter_uuid = ? and vt.display_mode = 1 and vt.type in (" . implode(",", $vmTreeTypes) . ") order by vt.type";
            $data = $this->dbSelect($sql, [xphp_get_config('vm')['VM_TREE_TYPE']['HOST'], $id]);
        } elseif (xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XHERE'] == $hypervisor) {
            // xhere仅获取集群
            $sql = "select vt.type, vt.name, vt.uuid, vt.parent_uuid
                from vm_tree vt join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                where vt.vcenter_uuid = vv.vcenter_uuid and vt.vcenter_uuid = ? and vt.display_mode = 1 and vt.type = ? order by vt.type";
            $data = (array) $this->dbSelect($sql, [$id, xphp_get_config('vm', 'VM_TREE_TYPE')['CLUSTER']]);
            // 单独处理后返回
            return $this->getSyncRecoveryVcenterForXhere($params, $data);
        } else {
            $sql = "select vt.type, vt.name, vt.uuid, vt.parent_uuid, vh.host_name, vh.host_ip, vh.host_uuid, vh.host_id, vh.authorization_flag, vh.online_flag
                from vm_tree vt left join vm_host vh on vt.name = vh.host_name and vt.type = ? and vt.vcenter_uuid = vh.vcenter_uuid 
                where vt.vcenter_uuid = ? and vt.host_uuid != vt.vcenter_uuid and vt.display_mode = 1 and vt.type in (" . implode(",", $vmTreeTypes) . ") order by vt.type";
            $data = $this->dbSelect($sql, [xphp_get_config('vm')['VM_TREE_TYPE']['HOST'], $id]);

            // 树形结构没有主机层则清空不显示
            $hostFlag = false;
            foreach ($data as $item) {
                if (xphp_get_config('vm')['VM_TREE_TYPE']['HOST'] == $item['type']) {
                    $hostFlag = true;
                    break;
                }
            }
            if (!$hostFlag) {
                $data = [];
            }

            $sql = "select 4 as type, host_name as name, host_uuid as uuid, vcenter_uuid as parent_uuid, 
            host_name, host_ip, host_uuid, host_id, authorization_flag, online_flag from vm_host where vcenter_uuid = ?";
            $dataHost = $this->dbSelect($sql, array($id));
            $hostUuids = array_column($data, 'host_uuid');
            foreach ($dataHost as $host) {
                if (!in_array($host['host_uuid'], $hostUuids)) {
                    // 不在树形结构中的主机单独显示
                    $data[] = $host;
                }
            }
        }

        $node = array();

        $sql = "select mut.tenant_uuid from vm_vcenter vv left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid 
            where vv.vcenter_uuid = ?";
        $vcenterTenantUuid = $this->dbSelect($sql, [$id])[0]['tenant_uuid'];
        $tenantHost = [];
        //如果是租户内用户操作，检测是否已配置指定宿主机
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['host_list']) {
                $tenantHost = $settings['host_list'];
            }
        }

        // 私有云标志
        $privateCloudFlag = in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'])
            && xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_ZSTACK'] != $hypervisor
            && xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_NEXAVM_NCSSV'] != $hypervisor;

        foreach ($data as $d) {
            //指定宿主机
            if (!empty($_SESSION['tenantuuid'])) {
                // 获取已分配的和租户自有的
                if (!in_array($d['host_uuid'], $tenantHost) && $_SESSION['tenantuuid'] != $vcenterTenantUuid)
                    continue;
            }
            if (!$d['host_uuid']) {
                $d['host_uuid'] = $d['uuid'];
            }

            $name = $d['host_ip'] ?: $d['name'];
            if (xphp_get_config('vm')['VM_TREE_TYPE']['HOST'] == $d['type']) {
                //添加离线/在线,授权/未授权标志,先检查在线/离线状态,在线的时候再检查授权/未授权标志
                // 私有云的分组不需要检查在线/离线状态
                if ($d['online_flag'] == xphp_get_config('app')['FLAG']['SET'] || $privateCloudFlag) {
                    if ($privateCloudFlag) {
                        $name = $d['name'] ?: $d['host_ip']; // 优先使用分组名
                    } else {
                        //在线
                        if ($hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']) {
                            $name = $d['host_name'];
                        }
                        // 未授权
                        if ($this->getUnauthorized($d['authorization_flag'])) {
                            $name .= "(" . xphp_get_lang('WEB_SYSTEM_LISENCE_UNAUTHORIZED') . ")";
                        }
                    }
                } else {
                    //离线
                    $name = $d['host_ip'] . "(" . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ")";
                    //                 if($hideOffline) continue; //隐藏离线的
                    if (
                        $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']
                        || $hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']
                    ) {
                        $name = $d['host_name'] . "(" . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ")";
                    }

                }
            }

            //判断修改虚拟实验室选中宿主机
            $checked = false;
            if ($hostuuid == $d['host_uuid']) {
                $checked = true;
            }
            $node[] = array(
                "id" => $d['host_uuid'] ?: $d['uuid'],
                "pid" => $d['parent_uuid'],
                "pId" => $d['parent_uuid'],
                "name" => $name,
                "isParent" => xphp_get_config('vm')['VM_TREE_TYPE']['HOST'] != $d['type'],
                "sid" => $d['host_id'],
                "nocheck" => $nocheck || xphp_get_config('vm', 'VM_TREE_TYPE')['HOST'] != $d['type'],
                "type" => 2,
                "ip" => $d['host_ip'],
                "hypervisor" => $pid,
                "icon" => $privateCloudFlag ? "./img/vm/folder_vm.png" : "./img/vm/host.png",
                "clickShow" => true,
                //用于点击宿主机的时候获取虚拟机信息后台刷新标志
                "refresh" => false,
                "vcuuid" => $id,
                "chkDisabled" => !$privateCloudFlag && $this->getOffLine($d['online_flag']),
                "hypervisor_des" => xphp_get_config('vm')['VMHYPERVISORDES'][intval($pid)],
                "checked" => $checked,
                "online_flag" => intval($d['online_flag'])
            );
        }
        array_multisort(array_column($node, 'name'), SORT_ASC, $node);

        return ['rows' => $node, 'total' => count($node)];
    }

    /**
     * xhere获取恢复到的集群
     * @param array $params 参数
     * @param array $data sql查询数据
     * @return array
     */
    public function getSyncRecoveryVcenterForXhere(array $params, array $data): array
    {
        $id = $params['platform_uuid'];
        $pid = intval($params['pid']);
        $nocheck = $params['nocheck_flag'] ?? false;
        $this->paramsCheck($id, $pid);

        $node = array();

        $sql = "select mut.tenant_uuid from vm_vcenter vv left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid 
            where vv.vcenter_uuid = ?";
        $vcenterTenantUuid = $this->dbSelect($sql, [$id])[0]['tenant_uuid'];
        $tenantCluster = [];
        //如果是租户内用户操作，检测是否已配置指定集群
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['host_list']) {
                $tenantCluster = $settings['host_list'];
            }
        }

        foreach ($data as $d) {
            if (!empty($_SESSION['tenantuuid'])) {
                // 获取已分配的和租户自有的
                if (!in_array($d['uuid'], $tenantCluster) && $_SESSION['tenantuuid'] != $vcenterTenantUuid)
                    continue;
            }
            $node[] = array(
                "id" => $d['uuid'],
                "pid" => $d['parent_uuid'],
                "name" => $d['name'],
                "isParent" => false,
                "sid" => $d['host_id'],
                "nocheck" => xphp_get_config('vm', 'VM_TREE_TYPE')['CLUSTER'] == $d['type'] ? false : $nocheck,
                "type" => 2,
                "ip" => $d['host_ip'],
                "hypervisor" => $pid,
                "icon" => "./img/vm/cluster.png",
                "clickShow" => true,
                //用于点击宿主机的时候获取虚拟机信息后台刷新标志
                "refresh" => false,
                "vcuuid" => $id,
                "chkDisabled" => false,
                "hypervisor_des" => xphp_get_config('vm', 'VMHYPERVISORDES')[$pid],
                "checked" => false
            );
        }
        array_multisort(array_column($node, 'name'), SORT_ASC, $node);

        return ['rows' => $node, 'total' => count($node)];
    }

    /**
     * 获取xhere块存储策略
     * @param array $params
     * @return array
     */
    public function getXhereVolumePolicyList(array $params): array
    {
        $vcenteruuid = $params['platform_uuid'];
        $msg = array(
            'vcenter_uuid' => $vcenteruuid
        );
        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $msg = json_encode($msg);

        $opName = 'VM_VCENTER_OP_QUERY_VOLUME_POLICY_LIST';
        $hypervisorType = xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XHERE'];
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisorType, $opName, $msg, true);
        $this->writeLog(json_encode($mbResult));
        $info = [];
        if ($mbResult['result']) {
            $info = $mbResult['msg']['volume_policy_list'];
        }
        return ['rows' => $info, 'total' => count($info)];
    }

    /**
     * 得到网络和存储(适用于openstack,得到某个项目的网络和存储)
     * @param array $params
     * @return array
     */
    public function getOpenStackNetworkAndStorage(array $params): array
    {
        $vcenteruuid = $params['platform_uuid'];
        $hypervisor = $params['hypervisor_type'];
        $groupname = $params['group_name'];
        $groupuuid = $params['group_uuid'];
        $username = $params['username'];
        $password = $params['password'];
        $zonename = $params['zonename'];
        $region = $params['region'];
        $this->paramsCheck($vcenteruuid, $groupname, $username, $password);

        $network = array();
        $storage = array();
        $domain = array();
        $instance = array();


        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        //获取存储信息
        $opName = 'VM_VCENTER_OP_QUERY_OPENSTACK_STORAGE_LIST';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
            'zone_name' => $zonename,
            'region' => $region,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $storageList = $mbResult['msg']['storage_resource_list'];
            foreach ($storageList as $list) {
                $storage[] = array(
                    'uuid' => $list['storage_uuid'],
                    'text' => $this->getHostStorageNameText($hypervisor, $list),
                    'totalsize' => $list['total_size'],
                    'freesize' => $list['free_size'],
                );
            }
        }
        $storage = v2_array_sort($storage, 'freesize', 'desc', 0, -1);

        //获取网络信息
        $opName = 'VM_VCENTER_OP_QUERY_USER_GROUP_PHYSICAL_NETWORK';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
            'zone_name' => $zonename,
            'region' => $region,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $networkList = $mbResult['msg']['network_list'];
            foreach ($networkList as $list) {
                $network[] = array(
                    'uuid' => $list['network_uuid'],
                    'text' => $list['network_name'],
                );
            }
        }


        //获取可用域
        $opName = 'VM_VCENTER_OP_QUERY_AVAILABILITY_ZONE';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
            'region' => $region,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $domainList = $mbResult['msg']['availability_zone_list'];
            foreach ($domainList as $list) {
                $domain[] = array(
                    'uuid' => $list['zone_name'],
                    'text' => $list['zone_name'],
                );
            }
        }

        //获取实例类型
        $opName = 'VM_VCENTER_OP_QUERY_OPENSTACK_FLAVORS_LIST';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
            'zone_name' => $zonename,
            'region' => $region,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $instanceList = $mbResult['msg']['flavors_list'];
            foreach ($instanceList as $list) {
                //只保留根磁盘大小和原来一样的
                //$rootSizeUnit = $rootSize/1024/1024/1024;//转换成GB
                //if($list['root_disk_size'] != $rootSizeUnit && Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM'] != $hypervisor) continue;
                $text = $list['flavor_name'] . "(" . $list['vcpu_num'] . "/" . round($list['ram_size'] / 1024, 1) . "GB " . "/" . $list['root_disk_size'] . "GB)";
                $uuid = $list['vcpu_num'] . "_" . round($list['ram_size'] / 1024, 1) . "_" . $list['root_disk_size'] . "_" . $list['flavor_id'];
                $instance[] = array(
                    'text' => $text,
                    'uuid' => $uuid,
                    'flavor_id' => $list['flavor_id']
                );
            }
        }
        return array(
            'network' => $network,
            'storage' => $storage,
            'domain' => $domain,
            'instance' => $instance
        );
    }

    /**
     * 获取私有云可用域
     * @param $params
     * @return array
     */
    public function getOpenStackAvailableDomain($params): array
    {
        $vcenteruuid = $params['platform_uuid'];
        $hypervisor = $params['hypervisor_type'];
        $groupname = $params['group_name'];
        $groupuuid = $params['group_uuid'];
        $username = $params['username'];
        $password = $params['password'];
        $timepointuuid = $params['timepoint_uuid'];
        $this->paramsCheck($vcenteruuid, $groupname, $username, $password);

        $domain = array();
        $domain[] = array(
            'uuid' => '0',
            'text' => xphp_get_lang('UI_PUBLIC_SELECT'),
        );

        //获取可用域
        $opName = 'VM_VCENTER_OP_QUERY_AVAILABILITY_ZONE';
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password,
        );
        $msg = json_encode($msg);
        $nodeuuid = Node::instance()->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg, true);
        if ($mbResult['result']) {
            $domainList = $mbResult['msg']['availability_zone_list'];
            foreach ($domainList as $list) {
                $domain[] = array(
                    'uuid' => $list['zone_name'],
                    'text' => $list['zone_name'],
                );
            }
        }
        // 获取时间点的可用域
        $sql = "select vm_config from vm_backup_timepoint where timepoint_uuid = ?";
        $data = $this->dbSelect($sql, [$timepointuuid]);
        $vmConfig = json_decode($data[0]['vm_config'], true);
        $thisDomain = $vmConfig['availability_zone'] ?? '';
        return [
            'domain' => $domain,
            'thisDomain' => $thisDomain
        ];
    }

    /**
     * 得到网络和存储(某台宿主机)
     * @param array $params
     * @return array
     */
    public function getNetworkAndStorage(array $params): array
    {
        $hypervisor = $params['hypervisor_type'];
        $vcenteruuid = $params['platform_uuid'];
        $hostuuid = $params['host_uuid'];
        $this->paramsCheck($hypervisor, $vcenteruuid, $hostuuid);
        $submodule_type = $hypervisor;
        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        if (xphp_get_config('vm', "VMHYPERVISORTYPE")['VM_HYPERVISOR_TYPE_EMD'] == $hypervisor) {
            // 恢复到内嵌时vcenter_uuid取节点uuid
            $vcenteruuid = $hostuuid;
        }

        $network = array();
        $storage = array();

        //获取存储信息
        $opName = 'VM_VCENTER_OP_QUERY_STORAGE';
        $msg = json_encode(array('vcenter_uuid' => $vcenteruuid, 'host_uuid' => $hostuuid));
        //        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, true);
        $mbResult = $this->service()->unifyVmPlatformService($nodeuuid, $submodule_type, $opName, $msg, true);

        if ($mbResult['result']) {
            $storageList = $mbResult['msg']['storage_resource_list'];
            foreach ($storageList as $list) {
                if (xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM'] == $hypervisor && 'netfs' == $list['driver_type']) {
                    continue;
                }
                $storage[] = array(
                    'uuid' => $list['storage_uuid'],
                    'text' => $this->getHostStorageNameText($hypervisor, $list),
                    'totalsize' => $list['total_size'],
                    'freesize' => $list['free_size'],
                    'driver_type' => $list['driver_type']
                );
            }
        }
        $storage = v2_array_sort($storage, 'freesize', 'desc', 0, -1);

        //获取网络信息
        $opName = 'VM_VCENTER_OP_QUERY_HOST_NETWORK_LIST';
        $msg = json_encode(array('vcenter_uuid' => $vcenteruuid, 'host_uuid' => $hostuuid));
        //        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, true);
        $mbResult = $this->service()->unifyVmPlatformService($nodeuuid, $submodule_type, $opName, $msg, true);
        if ($mbResult['result']) {
            $networkList = $mbResult['msg']['network_list'];

            foreach ($networkList as $list) {
                $network[] = array(
                    'uuid' => $list['network_uuid'],
                    'text' => $this->getHostNetworkNameText($list)
                );
            }
        }
        return array(
            'network' => $network,
            'storage' => $storage,
            'vcenter_type' => $this->getVcenterType($vcenteruuid)
        );
    }

    /**
     * 得到备份的虚拟机配置信息
     * @param array $params
     * @return array
     */
    public function getVMConfigInfoV2(array $params): array
    {
        $hypervisor = $params['hypervisor_type'];
        $vcenteruuid = $params['platform_uuid'];
        $hostuuid = $params['host_uuid'];
        $points = $params['points'];
        $pointsDetail = $params['points_detail'];
        $this->paramsCheck($hypervisor, $points);

        $nodeuuid = $pointsDetail[0]['nodeuuid'];   //从时间点获取节点uuid
        // 获取时间点所在存储可用的节点uuid
        $timepointUuidList = array_column($pointsDetail, 'timepointuuid');
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints($timepointUuidList);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }

        $oldHypervisor = $pointsDetail[0]['hypervisor'];
        //         var_dump($hypervisor, $points);

        if (!$oldHypervisor) {
            // 跨平台恢复，即操作系统的时间点
            $groupMsg = [
                'target_vm_list' => [],
                'timepoint_uuid_list' => $points
            ];
            foreach ($pointsDetail as $k => $pointItem) {
                // 获取磁盘信息
                $nodeuuid = Node::instance()->getLocalNodeUUID();
                $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
                $pfMSg['agent_uuid'] = $pointItem['vmuuid']; // 客户端uuid
                $msg = json_encode($pfMSg);
                $submodule_type = 1;
                $mbResultDisk = (new CMService())->getOSBackupAgentInfo($nodeuuid, $opName, $msg, true, $submodule_type);
                // 获取时间点转换配置
                $mbResultMsg = RecoveryJobController::instance()->convertPoints($pointItem['timepointuuid']);
                if (!$mbResultMsg || empty($mbResultMsg['disk_list'])) {
                    //获取后台消息失败
                    return ["flag" => false, "errorCode" => 1, "errorMsg" => ''];
                }
                // 获取系统盘标志赋值给is_bootable
                if ($mbResultDisk['result']) {
                    $diskList = array_column($mbResultDisk['msg']['disk_list'], null, 'dev_uuid');
                    foreach ($mbResultMsg['disk_list'] as &$item) {
                        $item['is_bootable'] = v2_parse_flag_to_bool($diskList[$item['disk_key']]['is_system_disk_flag']);
                    }
                }
                // 整机返回的is_bootable为"1"和"0"时转true/false
                foreach ($mbResultMsg['disk_list'] as &$item) {
                    $item['is_bootable'] = is_string($item['is_bootable']) ? boolval(intval($item['is_bootable'])) : $item['is_bootable'];
                }
                $groupMsg['target_vm_list'][$k] = $mbResultMsg;
            }
            $info = $this->groupVMRecoveryPluginMsg($hypervisor, $vcenteruuid, $hostuuid, $pointsDetail, $groupMsg, true, $params['instant_flag']);
        } else {
            $opName = 'VM_VCENTER_OP_GET_ADVANCE_RECOVERY_CONF';
            $mbMsg = array(
                'target_hypervisor_type' => $hypervisor,
                'recovery_timepoint_uuid_list' => $points,
            );
            $mbMsg = json_encode($mbMsg);
            $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $mbMsg, true);
            //         var_dump($mbResult);
            if (!$mbResult['result']) {
                //获取后台消息失败
                $info = array(
                    "flag" => false,
                    "errorCode" => 1,
                    "errorMsg" => '',
                );
                if (50 == $mbResult['errorCode'] || 95 == $mbResult['errorCode']) {
                    //时间点不存在
                    $info['errorCode'] = 50;
                    $info['errorMsg'] = xphp_get_lang('WEB_ERROR_BD_TIMEPOINT_NOT_EXIST_ERROR');
                }
                if (14025 == $mbResult['errorCode']) {
                    //时间点不存在
                    $info['errorCode'] = 14025;
                    $info['errorMsg'] = xphp_get_lang('WEB_ERROR_BD_TAPE_OCCUPIED_CANT_READ_TIMEPOINT_DATA_ERROR');
                }
                return $info;
            }

            $info = $this->groupVMRecoveryPluginMsg($hypervisor, $vcenteruuid, $hostuuid, $pointsDetail, $mbResult['msg'], false, $params['instant_flag'], $params['motion_flag']);
        }

        $info['flag'] = true;
        $info['instantFlag'] = $params['instant_flag'];  //瞬时恢复标记,瞬时恢复的时候不选择存储
        $info['vmotionFlag'] = false;                   //迁移标记,插件统一处理的,这里补齐这个字段


        if ($params['instant_flag']) {
            //如果是瞬时恢复,不显示磁盘置备模式
            $info['control']['disk_setting_mode'] = false;
        }

        return $info;
    }

    /**
     * 组合虚拟机恢复返回配置
     * @param int $hypervisor 虚拟化类型
     * @param string $vcenteruuid 虚拟化中心uuid
     * @param string $hostuuid 主机uuid
     * @param array $pointsDetail 时间点详情数组
     * @param array $mbMsg 后台返回消息体msg部分
     * @param bool $pFlag 是否有代理的时间点
     * @param bool $instantFlag 瞬时恢复标志
     * @param bool|null $motionFlag 迁移标志
     * @return array
     */
    public function groupVMRecoveryPluginMsg(
        int $hypervisor,
        string $vcenteruuid,
        string $hostuuid,
        array $pointsDetail,
        array $mbMsg,
        bool $pFlag = false,
        bool $instantFlag = false,
        ?bool $motionFlag = false
    ): array {
        $source_vm_list = $mbMsg['source_vm_list'];
        $target_vm_list = $mbMsg['target_vm_list'];
        $timepoint_uuid_list = $mbMsg['timepoint_uuid_list'];

        $vmConfig = xphp_get_config('vm_config');

        //时间点的配置
        $timepoint_uuids = implode("','", $timepoint_uuid_list);
        $sql = "select vbt.timepoint_uuid, vbt.vm_config, bdtsi.virus_scan_status, bbt.timepoint from vm_backup_timepoint vbt
            join bd_backup_timepoint bbt on vbt.timepoint_uuid = bbt.timepoint_uuid 
            left join bd_backup_timepoint_safe_info bdtsi on vbt.timepoint_uuid = bdtsi.timepoint_uuid 
            where vbt.timepoint_uuid in ('" . $timepoint_uuids . "')";
        $timepoint_info = (array) $this->dbSelect($sql);
        $timepoint_info = array_column($timepoint_info, null, 'timepoint_uuid');

        $vcenter_detail = [];
        if ($vcenteruuid) {
            //虚拟化中心版本
            $sql = "select version, detail from vm_vcenter where vcenter_uuid = ?";
            $vcenter_version = $this->dbSelect($sql, [$vcenteruuid])[0]['version'];
            $vcenter_detail = $this->dbSelect($sql, [$vcenteruuid])[0]['detail'];
            $vcenter_detail = json_decode($vcenter_detail, true);
        }

        // 是否包含操作系统是windows的，vmware使用
        $windowsFlag = false;

        foreach ($target_vm_list as $key => $value) {
            $timepoint_uuid = $timepoint_uuid_list[$key];
            $vm_config = json_decode($timepoint_info[$timepoint_uuid]['vm_config'], true);
            $target_vm_list[$key]['timepoint'] = $timepoint_info[$timepoint_uuid]['timepoint'];

            // -----适配有代理转换时间点配置返回的字段-----
            $target_vm_list[$key]['cores'] = $value['cpu_core'] ?? $source_vm_list[$key]['cores'];
            $target_vm_list[$key]['memory'] = $value['memory_size'] ?? $source_vm_list[$key]['memory'];
            $target_vm_list[$key]['sockets'] = $value['cpu_socket'] ?? $source_vm_list[$key]['sockets'];

            if (!$value['vm_uuid']) {
                $target_vm_list[$key]['vm_uuid'] = $pointsDetail[$key]['vmuuid'];
            }
            //设置目标虚拟化类型
            $target_vm_list[$key]['target_hypervisor_type'] = intval($hypervisor);
            //设置原虚拟化类型
            $target_vm_list[$key]['source_hypervisor_type'] = intval($pointsDetail[$key]['hypervisor']);
            //设置恢复后的虚拟机名称
            $target_vm_list[$key]['new_vm_name'] = $pointsDetail[$key]['vmname'];
            if ($instantFlag) {
                $target_vm_list[$key]['new_vm_name'] .= '_Instant_Restore'; // 瞬时恢复的名称后缀
            }
            if ($motionFlag) {
                $target_vm_list[$key]['new_vm_name'] .= '_Instant_Restore_Migration'; // 迁移的名称后缀
            }
            //设置原本的虚拟机名称
            $target_vm_list[$key]['vm_name'] = $pointsDetail[$key]['oldname'];
            // 内存为0则默认4G
            if (0 == $target_vm_list[$key]['memory']) {
                $target_vm_list[$key]['memory'] = strval(4294967296);
            }
            //设置内存按单位显示的格式
            $target_vm_list[$key]['memory_array'] = v2_calsize_to_value_and_unit($target_vm_list[$key]['memory']);
            $target_vm_list[$key]['memory_array']['num'] = $target_vm_list[$key]['memory_array']['value'];

            //设置数据加密密码校验
            $target_vm_list[$key]['data_encrypt'] = $this->getVMDataEncrypt($pointsDetail[$key]['config'] ?? []);
            //数据加密密码
            $target_vm_list[$key]['timepointuuid'] = $pointsDetail[$key]['timepointuuid'];

            //设置磁盘大小和控制器类型
            $diskList = $target_vm_list[$key]['disk_list'];

            //启动方式 1 卷启动 2镜像启动
//             $target_vm_list[$key]['openstack_start_mode'] = $value['openstack_start_mode'];

            // 获取时间点的磁盘配置
            $vm_config_disk_list = [];
            if ($vm_config['disk_list']) {
                $vm_config_disk_list = array_column($vm_config['disk_list'], null, 'disk_name');
            }
            $virtioFlag = false;
            $controller_type_virtio = array_keys($vmConfig['VmMiddleControllerType'], 'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO')[0];
            if (
                xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV'] == $pointsDetail[$key]['hypervisor']
                && (xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM'] == $hypervisor
                    || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XFUSION_KVM'] == $hypervisor)
            ) {
                // hyperv恢复到华为KVM，磁盘总线virtio优先级最高
                $virtioFlag = true;
            }
            // 整机瞬时恢复到内嵌，磁盘总线类型默认为virtio
            if ($pFlag && $instantFlag && xphp_get_config('vm', "VMHYPERVISORTYPE")['VM_HYPERVISOR_TYPE_EMD'] == $hypervisor) {
                $virtioFlag = true;
            }
            foreach ($diskList as $dkey => $dvalue) {
                $target_vm_list[$key]['disk_list'][$dkey]['virtual_size_unit'] = v2_calsize($dvalue['virtual_size'], true);
                $controller_type = $virtioFlag ? $controller_type_virtio : intval($source_vm_list[$key]['disk_list'][$dkey]['controller_type']);
                $target_vm_list[$key]['disk_list'][$dkey]['controller_type'] = $controller_type;
                $target_vm_list[$key]['disk_list'][$dkey]['controller_des'] = $vmConfig['VmMiddleControllerTypeDes'][$controller_type];
                $target_vm_list[$key]['disk_list'][$dkey]['disk_key_base64'] = base64_encode($dvalue['disk_key']);
                $target_vm_list[$key]['disk_list'][$dkey]['disk_type'] = $source_vm_list[$key]['disk_list'][$dkey]['disk_type'];

                if (
                    xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_VMWARE'] == $pointsDetail[$key]['hypervisor']
                    && xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_VMWARE'] != $hypervisor
                ) {
                    //从vmware恢复到其他虚拟化类型时只保留磁盘名，去除路径和后缀
                    $target_vm_list[$key]['disk_list'][$dkey]['disk_name'] = pathinfo($dvalue['disk_name'])['filename'];
                }

                // 适配有存储资源池的
                $disk_name = $target_vm_list[$key]['disk_list'][$dkey]['disk_name'];
                $target_vm_list[$key]['disk_list'][$dkey]['storage_pool'] = $vm_config_disk_list[$disk_name]['storage_pool'] ?? [];
            }


            //设置网卡控制器类型
            if (!empty($value['network_list'])) {
                $target_vm_list[$key]['net_list'] = $value['network_list'];
            } elseif (!empty($value['net_list'])) {
                $target_vm_list[$key]['net_list'] = $value['net_list'];
            } else {
                $target_vm_list[$key]['net_list'] = [
                    [
                        'network_name' => '',
                        'mac_address' => '',
                        'subnet_id' => '',
                        'controller_type' => 0,
                        'network_type' => 0,
                        'ip_list' => [''],
                    ]
                ];
            }

            $net_list = $target_vm_list[$key]['net_list'];
            foreach ($net_list as $nkey => $nvalue) {
                $controller_type = intval($nvalue['controller_type']);
                $target_vm_list[$key]['net_list'][$nkey]['controller_des'] = $vmConfig['VmMiddleControllerTypeDes'][$controller_type];

                //增加网络uuid字段
                $network_uuid = "";
                if (!empty($nvalue['subnet_id'])) {
                    $network_uuid = $nvalue['subnet_id'];
                }
                if ($source_vm_list && !empty($source_vm_list[$key]['net_list'][$nkey]['src_network_uuid'])) {
                    // 原配置的src_network_uuid
                    $network_uuid = $source_vm_list[$key]['net_list'][$nkey]['src_network_uuid'];
                }
                $target_vm_list[$key]['net_list'][$nkey]['network_uuid'] = $network_uuid;
            }

            //虚拟机版本是否可选
            $target_vm_list[$key]['vm_version_enable'] = $this->getVmVersionSelectEnable($hypervisor);
            //ics/ics-vvdk恢复版本
            $target_vm_list[$key]['vm_new_version'] = $target_vm_list[$key]['vm_version'];
            if (
                xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisor
                || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisor
                || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_KSPHERE'] == $hypervisor
            ) {
                if ($instantFlag) {
                    //瞬时恢复
                    if (version_compare($vcenter_version, '6.10.0', '>=')) {
                        //如果icenter版本大于等于6.10.0，则虚拟机仅能恢复为2.0版本虚拟机
                        $target_vm_list[$key]['vm_new_version'] = 'V2';
                    } else {
                        //如果icenter版本小于6.10.0，则虚拟机仅能恢复为1.0版本虚拟机
                        $target_vm_list[$key]['vm_new_version'] = 'V1';
                    }
                } else {
                    if (version_compare($vcenter_version, '6.10.0', '>=')) {
                        //如果icenter版本大于等于6.10.0，则虚拟机仅能恢复为2.0版本虚拟机
                        $target_vm_list[$key]['vm_new_version'] = 'V2';
                    } elseif (version_compare($vcenter_version, '6.0.0', '<=')) {
                        //如果icenter版本小于等于6.0.0，则虚拟机仅能恢复为1.0版本虚拟机
                        $target_vm_list[$key]['vm_new_version'] = 'V1';
                    } else {
                        //如果icenter版本大于6.0.0且小于6.10.0，按原版本恢复且可选
                        $target_vm_list[$key]['vm_new_version'] = $target_vm_list[$key]['vm_version'];
                        $target_vm_list[$key]['vm_version_enable'] = true;
                    }
                }
            }

            //openstack删除实例删除卷
            $target_vm_list[$key]['delete_on_termination'] = $vm_config['delete_on_termination'] ?? false;
            //镜像元数据
            $target_vm_list[$key]['image_metadata'] = json_decode($vm_config['image_metadata'], true);


            //sangfor scp 其他配置 - HA
            $target_vm_list[$key]['is_ha'] = $vm_config['is_ha_enabled'] ?? false;
            //sangfor scp 其他配置 - tools
            $target_vm_list[$key]['is_installed_tools'] = $vm_config['is_installed_tools'] ?? false;

            //存储类型
            $target_vm_list[$key]['storage_type'] = $this->getTimepointStorageType($pointsDetail[$key]['timepointuuid']);

            // 原机操作系统类型为windows时提示
            if (1 == $source_vm_list[$key]['win_hotadd_warn']) {
                $windowsFlag = true;
            }

            // 病毒扫描状态
            $target_vm_list[$key]['virus_scan_status'] = $timepoint_info[$timepoint_uuid]['virus_scan_status'] ?? 0;

            // CPU工作模式
            $target_vm_list[$key]['cpu_mode'] = $vm_config['cpu_mode'] ? intval($vm_config['cpu_mode']) : xphp_get_config('vm_config', 'BdMiddleCpuModeType')['BD_MIDDLE_CPU_MODE_TYPE_DEFAULT'];

            unset($target_vm_list[$key]['memory_size'], $target_vm_list[$key]['network_list']);
        }

        //得到虚拟机名称限制条件
        $vmNameLimit = $vmConfig['VmNameCheck'][$hypervisor] ?? array(  //emd
            'len' => '256',
            'limit' => '',
            'msg' => '',
        );


        $res = array(
            'hypervisor' => intval($hypervisor),
            'vcenter_detail' => $vcenter_detail,
            'old_hypervisor' => intval($pointsDetail[0]['hypervisor']),
            'source_config' => $source_vm_list,
            'config' => $target_vm_list,
            'vmNameLimit' => $vmNameLimit,
            'windows_flag' => $windowsFlag,
        );
        if ($vcenteruuid && $hostuuid) {
            $controlContent = $this->getVMRecoveryControlContent($hypervisor, $vcenteruuid, $hostuuid);
            $res = array_merge($res, [
                'control' => $controlContent['control'],
                'disk_bus' => $controlContent['disk_bus'],
                'disk_bus_hyperv' => $controlContent['disk_bus_hyperv'],
                'network_bus' => $controlContent['network_bus'],
                'available_domain' => $controlContent['available_domain'],
                'des_dir' => $controlContent['des_dir'],
                'mirror_image' => $controlContent['mirror_image'],
                'maintain_model' => $controlContent['maintain_model'],
            ]);
        }
        return $res;
    }

    /**
     * 得到虚拟机恢复任务名
     * @param array $params
     * @return string
     */
    public function getVMRecoverTaskName(array $params): string
    {
        $type = intval($params['hypervisor_type']);
        $this->paramsCheck($type);
        $taskName = xphp_get_config('vm', 'VMHYPERVISORDES')[$type];
        if ($params['instant_flag']) {
            $taskName .= xphp_get_lang('WEB_VM_INSTANT_RECOVERY');
        } elseif ($params['granular_flag']) {
            $taskName .= xphp_get_lang('WEB_VM_GRAIN_RECOVERY');
        } else {
            $taskName .= xphp_get_lang('WEB_PLATFORM_DES_RECOVERY');
        }
        return VmBackUp::instance()->getValidTaskName($taskName);
    }

    /**
     * 创建恢复任务
     * @param array $params 参数
     * @return string
     */
    public function createRecoverJob(array $params): string
    {
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $strategygroupuuid = $params['strategygroupuuid'];
        $hypervisor = intval($params['recoverInfo']['hypervisor']);
        $this->paramsCheck($hypervisor);

        //任务一般检查
        if (self::VM_RECOVERY_WAY_NEW == $params['recoverInfo']['target_way']) {
            $this->createRecoverJobCheck($hypervisor, $params['recoverInfo']['vcenteruuid'], $params['recoverInfo']['names']);
        }
        //跨平台恢复检查
        $this->crossHypervisorRecoveryCheck($params['pointInfo']['type'], $params['recoverInfo']['hypervisor']);
        $module_type = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $recovery_position = 2; // 固定为异机恢复
        $recovery_time_type = intval($params['typeInfo']['type']);

        //组合时间策略
        $recoveryTimeType = intval($params['time_strategy']['time_type']);
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
        //组合传输策略
        $transport_strategy = $this->groupTransportStrategy($params['typeInfo']['high']['trasfer'], $strategygroupuuid);
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $timeStrategyList,
            $transport_strategy
        );
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['RECOVERY'];
        $pfMSg['ignore_resource_limiting_flag'] = v2_parse_bool_to_flag($params['highInfo']['ignore_resource_limiting_flag']);
        // 安全策略
        $pfMSg['safe_config_strategy'] = Backup::instance()->groupSafeConfigStrategy($params['safe_strategy']);

        //private params
        //disk or file
        $pfMSg['recovery_level'] = xphp_get_config('vm', 'VmTaskLevel')['VM'];
        $pfMSg['recovery_vm_uuids'] = $this->groupRebuildVMInfo(
            $recovery_position,
            $params['pointInfo']['points'],
            $params['recoverInfo']
        );
        $pfMSg['transport_priority'] = VmBackUp::instance()->getTransportMode($params['typeInfo']['high']['trasfer']['mode'], $hypervisor);
        $pfMSg['speed_limit_strategy_list'] = Backup::instance()->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = Backup::instance()->groupTaskSpeedGlobalList($params['speedLimit']);
        $pfMSg['strategy_group_uuid'] = $strategygroupuuid;
        //appliance
        $pfMSg['appliance_uuid'] = $params['agent_uuid'];
        $pfMSg['agent_uuid'] = $params['agent_uuid'];
        $pfMSg['agent_pool_uuid'] = $params['agent_pool_uuid'];
        //openstack跨平台恢复所有
        $pfMSg['cross_platform_appliance_uuid'] = $params['cross_platform_appliance_uuid'];
        //线程数量
        $pfMSg['thread_num'] = intval($params['highInfo']['threadnum']);
        //备份系统节点IP
        $pfMSg['backup_server_ip'] = $params['backup_server_ip'];
        $pfMSg['transport_ip_segment'] = $params['transport_ip_segment'];
        //新增存储媒介 存储路径 校验列表
        $pfMSg['storage_media'] = $params['pointInfo']['storage_media'];
        $pfMSg['storage_path'] = $params['pointInfo']['storagepath'];
        //时间点存储介质的uuid
        $pfMSg['storage_uuid'] = $params['pointInfo']['storageuuid'];
        $pfMSg['calibreate_strategy_list'] = array(
            "int_create_calibration_algorithm" => v2_parse_bool_to_flag($params['verifyInfo']['is_integrity_check']), //是否生成数据校验
            "integrity_error_handle" => v2_parse_bool_to_flag($params['verifyInfo']['integrity_error_handle']), //校验出错后是否继续
        );
        // 传输压缩
        $pfMSg['source_compression'] = $params['typeInfo']['high']['trasfer']['transfer_compress'];
        // 异步传输
        $pfMSg['async_rw_flag'] = v2_parse_bool_to_flag($params['typeInfo']['high']['trasfer']['async_transfer'] ?? false);
        // 重试策略
        $pfMSg['retry_strategy'] = Backup::instance()->groupRetryStrategy($params['retry_strategy']);
        // 并行传输
        $pfMSg['parallel_transfer_vm_count'] = 1;
        $pfMSg['single_vm_parallel_disk_transfer_count'] = $params['typeInfo']['high']['trasfer']['single_vm_parallel_disk_transfer_count'];
        $pfMSg['vm_single_disk_parallel_transfer_count'] = $params['typeInfo']['high']['trasfer']['vm_single_disk_parallel_transfer_count'];
        // 恢复失败保留卷数据
        $pfMSg['keep_recovery_volumes'] = v2_parse_bool_to_flag($params['keep_recovery_volumes'] ?? false);

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['pointInfo']['points'][0]['timepointuuid']);
        // 获取时间点所在存储可用的节点uuid
        $timepointUuidList = array_column($params['pointInfo']['points'], 'timepointuuid');
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints($timepointUuidList);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }

        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        //        dump($msg);
        //如果是租户内部的用户操作，检查设置速度是否超出租户配置限制速度
//         if(!empty($_SESSION['tenantuuid'])){
//             $this->pCheckSpeedLimit($params['speedInfo'], $operate);
//         }


        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['typeInfo']['type'];
            if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] == $recoveryType) {
                $startResult = $this->startRecoverJob($task_name);
            } else {
                $startResult = true;
            }
            if ($startResult) {
                //启动任务成功,直接返回创建任务成功
                return $this->muOpResult($result, $operate, $msg);
            } else {
                //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
                return $this->muOpResult($result, $operate, $msg . 'Start job failed');
            }
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建瞬时恢复任务
     * @param array $params 参数
     * @return string
     */
    public function createInstantRecoverJob($params)
    {
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $submodule_type = intval($params['recoverInfo']['hypervisor']);
        $this->paramsCheck($submodule_type);
        $this->createRecoverJobCheck($submodule_type, $params['recoverInfo']['vcenteruuid'], array($params['recoverInfo']['newname']));
        $module_type = xphp_get_config('module', 'MODULE_TYPE')['VM'];
        $recovery_position = intval($params['recoverInfo']['recover2']);
        $recovery_time_type = xphp_get_config('app', 'FLAG')['UNSET'];  //补齐,无用
        $time_strategy_list = array();
        $transport_strategy = $this->groupTransportStrategy(array(), '');
        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $time_strategy_list,
            $transport_strategy
        );
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['VM_INSTANT_RECOVERY'];

        //private params
        //disk or file
        $pfMSg['recovery_level'] = xphp_get_config('vm', 'VmTaskLevel')['INSTANT_RECOVERY'];
        $pfMSg['hypervisor_type'] = $submodule_type;
        $pfMSg['instant_vm_info'] = $this->groupInstantVMInfo($params['pointInfo']['points'], $params['recoverInfo']);
        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['pointInfo']['points']['uuid']);
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_INSTANT_RECOVERY_TASK';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $vmOpcode = VmOpcode::instance();
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取迁移虚拟机的配置信息
     * @param array $params
     * @return array
     */
    public function getMotionVMConfigs(array $params): array
    {
        $hypervisor = intval($params['hypervisor_type']);
        $taskuuid = $params['job_uuid'];
        $vcenteruuid = $params['platform_uuid'];
        $hostuuid = $params['host_uuid'];
        $this->paramsCheck($hypervisor, $taskuuid, $vcenteruuid);
        $sql = "select 
            birt.instant_machine_name as new_vm_name, 
            birt.timepoint_uuid, 
            vbt.hypervisor_type 
            from bd_instant_recovery_task birt left join vm_backup_timepoint vbt 
            on birt.timepoint_uuid = vbt.timepoint_uuid where birt.task_uuid = ?";
        $sqlParams = array($taskuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $timepointUuid = $data[0]['timepoint_uuid'];
        $pointsDetail = array(
            array(
                'vmname' => $data[0]['new_vm_name'] . "_Migration",
                'oldname' => $data[0]['new_vm_name'],
                'config' => array(
                    'password' => "",
                    'password_auto_flag' => 1
                ),
                'hypervisor' => $data[0]['hypervisor_type'] ?? 0,
                'timepointuuid' => $timepointUuid
            )
        );


        //获取中间统一结构消息
        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUuid);
        $opName = 'VM_VCENTER_OP_GET_ADVANCE_MIGRATION_CONF';

        $mbMsg = array(
            'target_hypervisor_type' => $hypervisor,
            'task_uuid' => $taskuuid,                               //瞬时恢复任务uuid
            'orig_vm_uuids' => array($params['points_detail'][0]['vmuuid']), //瞬时恢复虚拟机列表,为后续支持多个做准备,这里是一个列表,目前是一个
        );

        $mbMsg = json_encode($mbMsg);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $mbMsg, true);
        if (!$mbResult['result']) {
            //获取后台消息失败
            return array(
                "flag" => false
            );
        }
        //找到原本的hypervisor
        $sql1 = "select hypervisor_type from vm_backup_timepoint where timepoint_uuid = ? ";
        $sqlParams1 = array($data[0]['timepoint_uuid']);
        $data1 = $this->dbSelect($sql1, $sqlParams1);
        $original_hypervisor = $data1[0]['hypervisor_type'];
        $info = $this->groupVMRecoveryPluginMsg($hypervisor, $vcenteruuid, $hostuuid, $pointsDetail, $mbResult['msg']);
        $info['original_hypervisor'] = $original_hypervisor;
        $info['flag'] = true;
        $info['instantFlag'] = false;  //瞬时恢复标记,这里补齐这个字段
        $info['vmotionFlag'] = true;   //迁移标记,迁移的时候通过这个判断是迁移
        return $info;
    }

    /**
     * 得到恢复目的用户组，适用于flexcloud and openstack
     * @param array $params
     * @return array
     */
    public function getRecoverUserGroup(array $params): array
    {
        $hypervisorType = intval($params['hypervisor_type']);
        $this->paramsCheck($hypervisorType);
        $openstackGroup = implode("','", xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack']);

        $sql = "select vcenter_uuid, vcenter_ip, nickname, vcenter_flag, hypervisor_type, vcenter_name, detail, username from vm_vcenter
                where hypervisor_type in ('" . $openstackGroup . "')";
        $data = $this->dbSelect($sql, array());
        $tree = array();
        $vcenter = VmPlatform::instance();
        foreach ($data as $d) {
            $name = $this->getRecoverHostName($d['hypervisor_type'], $d);
            $status = $vcenter->getVcenterHostLisenceStatus($d['vcenter_uuid']);
            if (!empty($status['statusDes'])) {
                $name = $name . '(' . $status['statusDes'] . ')';
            }
            $node = array(
                "id" => $d['vcenter_uuid'],
                "pId" => 0,
                //                "name" => $d['vcenter_ip'] == $d['nickname'] ? $d['vcenter_ip'] : $d['nickname'] . "(" . $d['vcenter_ip'] . ")",
                "name" => $name,
                "open" => false,
                "isParent" => true,
                "nocheck" => true,
                "hypervisor" => $hypervisorType,
                "type" => 1,
                "iconSkin" => VmPlatform::instance()->getHypervisorIcon($d['hypervisor_type'], false),
            );
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * 创建虚拟机迁移任务
     * @param array $params
     * @return string
     */
    public function createMotionJob(array $params): string
    {
        $hypervisor = intval($params['hostInfo']['hypervisor']);            //虚拟化类型
        $this->createRecoverJobCheck($hypervisor, $params['hostInfo']['vcenteruuid'], array($params['vmName']));
        $hostInfo = $params['hostInfo'];                //宿主机信息
        $storeType = 1;      //存储类型 1 自动 /2 手动
        $storeName = '';                                //存储名字
        $vmNewName = $params['vmName'];                 //新虚拟机名字
        $startVMFlag = $params['startVMFlag'];          //迁移完成启动虚拟机标志
        $instantTaskUUID = $params['instantTaskUUID'];  //瞬时恢复任务uuid
        $threadNum = intval($params['threadNum']);      //线程数量
        $transportmode = $params['transportmode'];      //传输模式
        //appliance
        $applianceuuid = $params['applianceuuid'];
        // 加密传输
        $encryptFlag = $params['encrypt_flag'];
        // 加密传输算法
        $encryptMethod = $params['encrypt_method'];


        //获取原始虚拟机的信息
        $sql = "select orig_vm_uuid, timepoint_uuid from vm_instant where task_uuid = ?";
        $data = $this->dbSelect($sql, array($instantTaskUUID));
        if (!empty($data)) {
            $vmuuid = $data[0]['orig_vm_uuid'];
            $timepointUUID = $data[0]['timepoint_uuid'];
        } else {
            return $this->muOpResult(FALSE, xphp_get_lang('WEB_VM_GET_OLD_VM_INFO'));
        }

        //更新线程数量
        $sql = "update bd_task set thread_num = ? where task_uuid = ?";
        $result = $this->dbQuery($sql, array($threadNum, $instantTaskUUID));

        $hostuuid = $hostInfo['hostuuid'];
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $hostuuid = $hostInfo['groupuuid'];
        }
        //备份系统节点IP
        $backup_server_ip = $params['backup_server_ip'];
        //数据传输网段
        $transport_ip_segment = $params['transport_ip_segment'];
        $rebuildVMInfo = array(
            'vm_uuid' => $vmuuid,
            'new_name' => $vmNewName,
            'timepoint_uuid' => $timepointUUID,
            'destination_host_uuid' => $hostuuid,
            'destination_vcenter_uuid' => $hostInfo['vcenteruuid'],
            'auto_datastore_flag' => $storeType,
            'destination_datastore_name' => $storeName,
            'start_vm_flag' => v2_parse_bool_to_flag($startVMFlag),
            'vm_config' => $this->groupVMConfig($params['vmconfigs'][0]),
            'extension_info' => $this->groupExtensionInfo($hostInfo)    //扩展消息
        );
        $pfMSg = array(
            'task_uuid' => $instantTaskUUID,
            'rebuild_vm' => $rebuildVMInfo,
            'transport_priority' => VmBackUp::instance()->getTransportMode($transportmode),
            'appliance_uuid' => $applianceuuid,
            'agent_uuid' => $applianceuuid,
            'backup_server_ip' => $backup_server_ip,
            'transport_ip_segment' => $transport_ip_segment,
            'encrypt_flag' => v2_parse_bool_to_flag($encryptFlag),
            'encrypt_method' => $encryptMethod,
            'source_compression' => $params['transfer_compress'], // 传输压缩
        );
        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_ONLINE_MOTION_TASK';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $vmOpcode = VmOpcode::instance();
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建细粒度恢复任务
     * @param array $params 参数
     * @return string
     */
    public function createGrainRecoverJob(array $params): string
    {
        //public params
        $taskName = htmlspecialchars_decode($params['job_name']);
        $hypervisor = intval($params['point_info']['hypervisor_type']);
        $point = $params['point_info']['points'];
        $this->paramsCheck($hypervisor);

        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['VM'];

        $recoveryPosition = 2;
        $recoveryTimeType = 2;
        $timeStrategyList = array();
        $transportStrategy = $this->groupTransportStrategy(array(), '');

        $pfMSg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $taskName,
            $moduleType,
            $recoveryPosition,
            $recoveryTimeType,
            $timeStrategyList,
            $transportStrategy
        );
        $pfMSg['task_type'] = xphp_get_config('task', 'TASKTYPE')['VM_FILE_RECOVERY'];
        $pfMSg['recovery_level'] = xphp_get_config('vm', 'VmTaskLevel')['FILE'];
        $pfMSg['recovery_vm_uuids'] = array();
        $pfMSg['transport_priority'] = '';

        //上面是恢复的参数,下面是细粒度参数
        $pfMSg['vm_uuid'] = $point['vm_uuid'];
        $pfMSg['vm_name'] = $point['vm_name'];
        $pfMSg['timepoint_uuid'] = $point['timepoint_uuid'];

        $nodeUuid = Node::instance()->getNodeUUIDWithTimepointUUID($point['timepoint_uuid']);
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_GRAIN_RECOVERY_TASK';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->service()->unifyVmPlatformService($nodeUuid, $hypervisor, $opName, $msg);
        $result = $mbResult['result'];
        $operate = (new VmOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 校验数据加密密码正确性
     * @param array $params 参数
     * @return string
     */
    public function checkVMEncryptPass(array $params): string
    {
        $vms = $params['vms'];
        foreach ($vms as $vm) {
            //校验输入密码
            if ($vm['pass_flag']) {
                $sql = "select bbt.detail, vbt.vm_name from bd_backup_timepoint bbt, vm_backup_timepoint vbt 
                    where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint_uuid = ? ";
                $data = $this->dbSelect($sql, array($vm['timepoint_uuid']));
                if (!empty($data)) {
                    $detail = json_decode($data[0]['detail'], true);
                    $oldPassword = $detail['password'];
                    $password = v2_pt_pass_encrypt(base64_decode($vm['encrypt_pass']));
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
        return $this->muOpResult(true, '');
    }

    /**
     * 获取主机信息
     * @param array $params
     * @return string
     */
    public function getHostCommonInfo(array $params): string
    {
        $opName = 'VM_VCENTER_OP_QUERY_HOST_COMMON_CONF';
        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $msg = json_encode(['vcenter_uuid' => $params['platform_uuid'], 'host_uuid' => $params['host_uuid'], 'region' => $params['region']]);
        //        $mbResult = $this->mbVMMsg($nodeUuid, $params['hypervisor_type'], $opName, $msg, true);
        $mbResult = $this->service()->unifyVmPlatformService($nodeUuid, $params['hypervisor_type'], $opName, $msg, true);
        $result = $mbResult['result'];
        $vmOpcode = VmOpcode::instance();
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取恢复目标虚拟机配置
     * @param array $params
     * @return array
     */
    public function getRecoverTargetVMConfigs(array $params): array
    {
        $hypervisor = $params['hypervisor_type'];
        $platformUuid = $params['platform_uuid'];
        $hostUuid = $params['host_uuid'];
        $region = $params['region'];
        $vmUuid = $params['vm_uuid'];
        $pointsDetail = $params['points_detail'];
        $this->paramsCheck($hypervisor, $vmUuid);
        $nodeUuid = Node::instance()->getMasterNodeUuid();
        $opName = 'VM_VCENTER_OP_QUERY_TARGET_VM_CONFIG';
        $mbMsg = array(
            'vcenter_uuid' => $platformUuid,
            'host_uuid' => $hostUuid,
            'vm_uuid' => $vmUuid,
            'region' => $region,
        );
        $mbMsg = json_encode($mbMsg);
        $mbResult = $this->mbVMMsg($nodeUuid, $hypervisor, $opName, $mbMsg, true);
        if (!$mbResult['result']) {
            return [
                "flag" => false,
                "errorCode" => 1,
                "errorMsg" => '',
            ];
        }
        $data = $mbResult['msg'];
        $vmConfig = xphp_get_config('vm_config');
        foreach ($data['disk_list'] as &$disk) {
            // 总线类型转为名称
            $disk['controller_type_des'] = $vmConfig['VmMiddleControllerTypeDes'][$disk['controller_type']];
        }
        return $data;
        //        return $this->groupVMRecoveryPluginMsg($hypervisor, $platformUuid, $hostUuid, $pointsDetail, $mbResult['msg']);
    }

    //todo:public方法

    /**
     * 组合传输策略
     * @param array  $transport         传输策略信息
     * @param string $strategyGroupUuid 策略组UUID
     * @return array
     */
    public function groupTransportStrategy(array $transport, string $strategyGroupUuid = ''): array
    {
        $set = xphp_get_config('app', 'FLAG')['SET'];
        $unset = xphp_get_config('app', 'FLAG')['UNSET'];
        return array(
            'encrypt_flag' => $transport['encrypt'] ? $set : $unset,
            'encrypt_method' => intval($transport['encrypt_method']),
            'compress_flag' => $transport['compress'] ? $set : $unset,
            'speed_limit_flag' => $transport['speedFlag'] ? $set : $unset,
            'max_speed' => intval($transport['speed']),
            'network_uuid' => !empty($transport['network']) ? $transport['network'] : '', //传输网络
            'strategy_group_uuid' => $strategyGroupUuid,
            'compress_method' => 0,
            'reconnect_times' => intval($transport['reconnect_times']),
            'reconnect_interval' => intval($transport['reconnect_interval']),
        );
    }

    /**
     * 如果主机离线禁用checkbox
     * @param int $online_flag
     * @return bool
     */
    private function getOffline($online_flag): bool
    {
        return $online_flag != xphp_get_config('app', 'FLAG')['SET'];
    }

    /**
     * 如果主机未授权禁用checkbox
     * @param int $authorized_flag
     * @return bool
     */
    private function getUnauthorized($authorized_flag): bool
    {
        return $authorized_flag != xphp_get_config('app', 'FLAG')['SET'];
    }

    /**
     * 得到虚拟机恢复/瞬时恢复/迁移等可以到的目标虚拟化类型
     * @param int $hypervisor 源虚拟化类型
     * @return array
     */
    public function getVMRecoveryHypervisorArr(int $hypervisor): array
    {
        //return xphp_get_config('vm', 'VMHYPERVISORTYPE'); //todo:新增虚拟化调试使用
        $extension = SystemHandler::instance()->getExtensionLicense();

        $licFunction = $extension['f'];
        $crossHypervisorRecovery = $extension['crossHypervisorRecovery'];

        $ics_types = [
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'],
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_VVDK']
        ];
        if (in_array($hypervisor, $ics_types)) {
            //先检查是否选择了跨平台恢复功能,如果没有选择,只能恢复到ics/ics-vvdk
//            if (!$licFunction['crossHypervisorRecovery'] || !$crossHypervisorRecovery[$hypervisor]) {
//                return $ics_types;
//            }

            //如果选择了跨平台恢复,返回跨平台恢复授权中定义的内容+ics+ics-vvdk
            return array_merge($crossHypervisorRecovery[$hypervisor], $ics_types);
        }

        // hci/scp无需v2v授权即可相互恢复
        $sangfor_types = [
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SANGFOR_KVM'],
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']
        ];
        if (in_array($hypervisor, $sangfor_types)) {
            //先检查是否选择了跨平台恢复功能,如果没有选择,只能恢复到hci和scp
//            if (!$licFunction['crossHypervisorRecovery'] || !$crossHypervisorRecovery[$hypervisor]) {
//                return $sangfor_types;
//            }
            //如果选择了跨平台恢复,返回跨平台恢复授权中定义的内容+hci和scp
            return array_merge($crossHypervisorRecovery[$hypervisor], $sangfor_types);
        }

        //先检查是否选择了跨平台恢复功能,如果没有选择,只能恢复到本平台
//        if (!$licFunction['crossHypervisorRecovery']) {
//            return [$hypervisor];
//        }

        //如果选择了跨平台恢复,返回跨平台恢复授权中定义的内容
        return $crossHypervisorRecovery[$hypervisor] ?: [$hypervisor];
    }

    /**
     * 根据虚拟化类型得到虚拟化中心名称显示
     * @param int $hypervisor 虚拟化类型
     * @param array $vcenterArr 虚拟化中心数据库信息
     * @return string
     */
    public function getRecoverHostName(int $hypervisor, array $vcenterArr): string
    {
        $name = $vcenterArr['vcenter_ip'] == $vcenterArr['nickname'] ? $vcenterArr['vcenter_ip']
            : $vcenterArr['nickname'] . "(" . $vcenterArr['vcenter_ip'] . ")";
        if (in_array(intval($hypervisor), xphp_get_config('vm', 'VMHYPERVISORGROUP')['xenserver'])) {
            //如果是XenServer
            if ($vcenterArr['vcenter_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                //如果是vcenter
                $name = $vcenterArr['vcenter_name'] . " (" . xphp_get_lang('WEB_VM_VCENTER_XENSERVER_MASTER_NODE')
                    . ":" . $vcenterArr['vcenter_ip'] . ")";
            } else {
                $name = $vcenterArr['vcenter_name'] . " (" . $vcenterArr['vcenter_ip'] . ")";
            }
        } else if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $name = $name . '(' . $vcenterArr['username'] . ')';
        }
        return htmlspecialchars_decode($name);
    }

    /**
     * 根据虚拟化类型得到存储显示的名字
     * XenServer只有驱动器类型
     * VMware只有文件系统类型
     * @param int $hypervisor
     * @param array $storage
     * @return string
     */
    private function getHostStorageNameText(int $hypervisor, array $storage): string
    {
        if (
            in_array(intval($hypervisor), xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware'])
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV']
        ) {
            //如果是VMware
            $typeStr = 'filesystem_type';
        } elseif (
            in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])
            && !in_array($hypervisor, [26, 30])
        ) {
            //如果是openstack就不显示存储类型，排除zstack cloud/xeccp
            return $storage['storage_uuid'];
        } else {
            //如果是XenServer,KVM
            $typeStr = 'driver_type';
        }
        return $storage['storage_name'] . "(" . $storage[$typeStr] .
            ", " . xphp_get_lang('UI_STORAGE_TOTAL_SIZE') . ":" . v2_calsize($storage['total_size'], true) .
            ", " . xphp_get_lang('WEB_PLATFORM_DC_AVAILABLE_SPACE') . ":" . v2_calsize($storage['free_size'], true) . ")";
    }

    /**
     * 得到宿主机网卡的名字显示
     * @param array $network
     * @return string
     */
    private function getHostNetworkNameText(array $network): string
    {
        $text = $network['network_name'];
        if (!empty($network['ip_address'])) {
            $text .= "(" . $network['ip_address'] . ")";
        }
        return $text;
    }

    /**
     * 获取vcenter类型：1虚拟化平台，2单机
     * @param string $vcenteruuid
     * @return int
     */
    public function getVcenterType(string $vcenteruuid): int
    {
        $sql = "select vcenter_flag from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        return intval($data[0]['vcenter_flag']);
    }

    /**
     * 获取时间点是否加密密码
     * @param array $config
     * @return bool
     */
    private function getVMDataEncrypt(array $config): bool
    {
        if (empty($config))
            return false;
        if (!empty($config['password']) && $config['password_auto_flag'] == 2) {
            return true;
        }
        return false;
    }

    /**
     * 获取虚拟机版本是否可选
     * @param int $hypervisor
     * @return bool
     */
    public function getVmVersionSelectEnable(int $hypervisor): bool
    {
        // #29732全部放开
        return true;
        //        $enable = true;
//        if (xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisor
//            || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisor
//            || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_KSPHERE'] == $hypervisor
//        ) {
//            $enable = false;
//        }
//        return $enable;
    }

    /**
     * 获取时间点的存储类型
     * @param string $timepointUuid
     * @return int
     */
    public function getTimepointStorageType(string $timepointUuid): int
    {
        $sql = "select bsr.storage_type from bd_backup_timepoint bbt 
            join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid
            where bbt.timepoint_uuid = ?";
        $data = $this->dbSelect($sql, [$timepointUuid]);
        return $data[0]['storage_type'] ?? 0;
    }

    /**
     *
     * 得到虚拟机恢复控制内容,主要是控制恢复插件内容显示
     * @param int $hypervisor
     * @param string $vcenteruuid
     * @param string $hostuuid
     * @return array
     */
    public function getVMRecoveryControlContent(int $hypervisor, string $vcenteruuid, string $hostuuid): array
    {
        $this->paramsCheck($hypervisor);
        $hypervisor = intval($hypervisor);
        $hypervisorConf = xphp_get_config('vm', 'VMHYPERVISORTYPE');

        //可用域
        $available_domain = array();

        //目标目录
        $des_dir = array();

        //镜像
        $mirror_image = array();

        //置备模式 默认3个
        $maintain_model = array(
            '',
            xphp_get_lang('UI_PLATFORM_FINE_EQUIP'),
            xphp_get_lang('UI_PLATFORM_THICK_DELAY_0'),
            xphp_get_lang('UI_PLATFORM_THICK_0'),
        );

        $disk_bus_hyperv = [];

        switch ($hypervisor) {
            //vmware系列
            case $hypervisorConf['VM_HYPERVISOR_TYPE_VMWARE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_AWS']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项, 如果引导模式,可用域,root密码设置,目标资源池/目录,镜像,ha,虚拟化类型全部是false,这里就是false,表示界面不显示其他
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => true,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,           //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,       //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,      //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择

                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_PARA_VIRTUAL_SCSI' => xphp_get_lang('UI_PLATFORM_VM_PARAVIRTU_SCSI'),
                    'VM_MIDDLE_CONTROLLER_TYPE_LSI_LOGIC_SAS' => 'LSI Logic SAS',
                    'VM_MIDDLE_CONTROLLER_TYPE_LSI_LOGIC' => 'LSI Logic',
                    'VM_MIDDLE_CONTROLLER_TYPE_BUS_LOGIC' => xphp_get_lang('UI_PLATFORM_BUS_PARALLEL'),
                    'VM_MIDDLE_CONTROLLER_TYPE_AHCI' => 'AHCI SATA',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_NVME' => 'NVME',
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000E' => 'E1000E',
                    'VM_MIDDLE_CONTROLLER_TYPE_SRIOV' => xphp_get_lang('UI_PLATFORM_SRIOV_PASSTHROUGH'),
                    'VM_MIDDLE_CONTROLLER_TYPE_PCNET32' => xphp_get_lang('UI_PLATFORM_VARIABLE'),
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET' => 'VMXNET',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET2' => 'VMXNET 2',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3' => 'VMXNET 3',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3_VRDMA' => 'PVRDMA',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //目标目录
                $des_dir = $this->getDesDir($hostuuid);

                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_FINE_EQUIP'),
                    xphp_get_lang('UI_PLATFORM_THICK_DELAY_0'),
                    xphp_get_lang('UI_PLATFORM_THICK_QUICK_0'),
                );
                break;

            //XenServer/XCP-NG/vGate等
            case $hypervisorConf['VM_HYPERVISOR_TYPE_XENSERVER']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_XCP_NG']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => true,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_XEN' => 'xen',
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_XEN' => 'xen',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:

                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => true,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array();

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();

                break;

            //云宏Xen
            case $hypervisorConf['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array();

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                break;

            //Proxmox
            case $hypervisorConf['VM_HYPERVISOR_TYPE_PROXMOX']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3' => 'vmxnet3',
                );

                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;
            case $hypervisorConf['VM_HYPERVISOR_TYPE_LENOVO_AIO']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_VMXNET3' => 'vmxnet3',
                );

                $network_bus = $this->getControllerTypeDesArr($network_bus);
                break;

            //华为FusionCompute(Xen)
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array();

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                break;

            //华为FusionCompute(KVM)
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_XFUSION_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                );

                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //置备模式
                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_THIN'),
                    xphp_get_lang('UI_PLATFORM_NORMAL_DELAY_0'),
                    xphp_get_lang('UI_PLATFORM_NORMAL'),
                );
                break;

            //H3C CAS/UIS
            case $hypervisorConf['VM_HYPERVISOR_TYPE_H3C_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => xphp_get_lang('UI_PLATFORM_HSPEED_HARDDISK'),
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => xphp_get_lang('UI_PLATFORM_SCSI_HARDDISK'),
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => xphp_get_lang('UI_PLATFORM_IDE_HARDDISK'),
                    'VM_MIDDLE_CONTROLLER_TYPE_VSCSI' => xphp_get_lang('UI_PLATFORM_HSPEED_SCSI_HARDDISK'),
                    'VM_MIDDLE_CONTROLLER_TYPE_USB' => xphp_get_lang('UI_PLATFORM_USB_HARDDISK'),
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => xphp_get_lang('UI_PLATFORM_IE_CARD'),
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => xphp_get_lang('UI_PLATFORM_HSPEED_CARD'),
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => xphp_get_lang('UI_PLATFORM_NORMAL_CARD'),
                );

                $network_bus = $this->getControllerTypeDesArr($network_bus);

                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_THIN'),
                    xphp_get_lang('UI_PLATFORM_DELAY_0'),
                    xphp_get_lang('UI_PLATFORM_SET_0'),
                );
                break;

            //RHV/Ovirt/OLVM/易讯通/zVirt
            case $hypervisorConf['VM_HYPERVISOR_TYPE_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_RHV_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_OVIRT_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_EASTED_VSERVER']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_OLVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HOSTVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_RED_VIRT']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_ROSA_VIRT']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = $this->getRhvOvirtDiskBus($vcenteruuid);
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);
                //置备模式
                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_THIN_EQUIP'),
                    '',
                    xphp_get_lang('UI_PLATFORM_PRE_ALLOCATE'),
                );
                break;

            //Winhong CNware
            case $hypervisorConf['VM_HYPERVISOR_TYPE_WINHONG_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => true,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = $this->getRhvOvirtDiskBus($vcenteruuid);
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139_VIRTIO' => xphp_get_lang('UI_PLATFORM_DUALMODE_RV'),
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_FINE_EQUIP'),
                    xphp_get_lang('UI_PLATFORM_THICK_DELAY_0'),
                    xphp_get_lang('UI_PLATFORM_THICK_EQUIP'),
                );
                break;

            //SmartX
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SMARTX_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_ARCFRA_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    'disk_policy_smartx' => true,          //磁盘存储策略，smartx特殊显示

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'scsi',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'ide',
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_SRIOV' => 'sriov'
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_FINE_EQUIP'),
                    '',
                    xphp_get_lang('UI_PLATFORM_THICK_EQUIP'),
                );
                break;

            //Inspur ICS
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_KSPHERE']:
            // 火山云
            case $hypervisorConf['VM_HYPERVISOR_TYPE_VOLC']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => true,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_FINE_EQUIP'),
                    '',
                    xphp_get_lang('UI_PLATFORM_NOT_THIN_EQUIP'),
                );
                break;

            //OpenStack
            case $hypervisorConf['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SUGON_CLOOUD_OPENSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_INSPUR_CLOUD_PLATFORM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_EASYSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_FIBERHOME_OPENSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_CTSI_OPENSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_AW_CLOUD']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => true,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => false,              //cpu,插槽0和核心显示
                    'cpu_openstack' => true,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置

                    'os_type' => true,               //操作系统类型
                    'image_metadata' => true,          //镜像元数据，OpenStack特殊显示
                );
                if ($hypervisorConf['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK'] == $hypervisor) {
                    // 华为云stack在插件中隐藏可用域
                    $control['available_domain'] = false;
                }

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'ide',
                    'VM_MIDDLE_CONTROLLER_TYPE_UML' => 'uml',
                    'VM_MIDDLE_CONTROLLER_TYPE_XEN' => 'xen',
                    'VM_MIDDLE_CONTROLLER_TYPE_USB' => 'USB',
                    'VM_MIDDLE_CONTROLLER_TYPE_FDC' => 'fdc',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'sata',
                    'VM_MIDDLE_CONTROLLER_TYPE_LXC' => 'lxc',
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'virtio',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000E' => 'e1000e',
                    'VM_MIDDLE_CONTROLLER_TYPE_NE2K_PCI' => 'ne2k_pci',
                    'VM_MIDDLE_CONTROLLER_TYPE_NETFRONT' => 'netfront',
                    'VM_MIDDLE_CONTROLLER_TYPE_PCNET' => 'pcnet',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'etl8139',
                    'VM_MIDDLE_CONTROLLER_TYPE_SPAPR_VLAN' => 'spapr_vlan',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //可用域
                $available_domain = array();


                break;

            //噢易
            case $hypervisorConf['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => false,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array();

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                break;

            //Zstack,CloudView KVM
            case $hypervisorConf['VM_HYPERVISOR_TYPE_ZSTACK']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_ZSTACK_ZSPHERE']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_NEXAVM_NSSV']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_NEXAVM_NCSSV']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_CLOUDVIEW_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => false,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => true,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => xphp_get_lang('UI_PLATFORM_VIR_DISK'),
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => xphp_get_lang('UI_PLATFORM_NORMAL_CLOUD_DISK'),
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139'
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //可用域
                $available_domain = array();

                //目标目录
                $des_dir = $this->getDesDir($hostuuid);

                //置备模式
                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_THIN_EQUIP'),
                    '',
                    xphp_get_lang('UI_PLATFORM_THICK_EQUIP'),
                );
                break;

            case $hypervisorConf['VM_HYPERVISOR_TYPE_XSKY']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => false,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => true,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => false,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => xphp_get_lang('UI_PLATFORM_VIR_DISK'),
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => xphp_get_lang('UI_PLATFORM_NORMAL_CLOUD_DISK'),
                );

                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array();
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //可用域
                $available_domain = array();

                //目标目录
                $des_dir = $this->getDesDir($hostuuid);

                break;

            //深信服HCI/VVDK
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
            case $hypervisorConf['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => true,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => xphp_get_lang('UI_PLATFORM_VIR_DISK'),
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => xphp_get_lang('UI_PLATFORM_NORMAL_DISK'),
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'Virtio',            // virtio
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'e1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'rtl8139',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //目标目录
                $des_dir = array();

                //置备模式
                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_THIN_ALLOCATE'),
                    xphp_get_lang('UI_PLATFORM_DYNAMIC_ALLOCATE'),
                    xphp_get_lang('UI_PLATFORM_PRE_ALLOCATE'),
                );
                break;

            //Hyper-v
            case $hypervisorConf['VM_HYPERVISOR_TYPE_HYPERV']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型
                    'cpu_general' => false,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,              //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu
                    'cpu_hyperv' => true,           //cpu,hyperv特殊显示,显示hypervcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => false,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,        //磁盘分块大小
                    'disk_type_hyperv' => true,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,       //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );
                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'BD_MIDDLE_CONTROLLER_TYPE_HYPERV_SCSI' => 'hyper-v scsi',
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);
                //针对hyper-v特殊处理 因为还使用的老版本的数据 所以数据格式按照老版本来,把磁盘类型的值存在磁盘总线类型里
                $disk_bus_hyperv = array(
                    array(
                        'key' => 0,
                        'value' => xphp_get_lang('UI_PLATFORM_ORIGINNAL_CONF'),
                    ),
                    array(
                        'key' => 1,
                        'value' => xphp_get_lang('UI_PLATFORM_VHD_FIXED'),
                    ),
                    array(
                        'key' => 2,
                        'value' => xphp_get_lang('UI_PLATFORM_VHD_DYNAMIC'),
                    ),
                    array(
                        'key' => 3,
                        'value' => xphp_get_lang('UI_PLATFORM_VHDX_FIXED'),
                    ),
                    array(
                        'key' => 4,
                        'value' => xphp_get_lang('UI_PLATFORM_VHDX_DYNAMIC'),
                    ),
                );

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'BD_MIDDLE_CONTROLLER_TYPE_HYPERV_NET' => 'hyper-v network adapter',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                $maintain_model = array(
                    '',
                    xphp_get_lang('UI_PLATFORM_DYNAMIC_EXTEND'),
                    '',
                    xphp_get_lang('UI_PLATFORM_FIXED_SIZE'),
                );
                break;
            case $hypervisorConf['VM_HYPERVISOR_TYPE_XHERE']:
                //显示控制
                $control = array(
                    'other_nav' => false,                //其他,横选项, 如果引导模式,可用域,root密码设置,目标资源池/目录,镜像,ha,虚拟化类型全部是false,这里就是false,表示界面不显示其他
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,           //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => false,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,       //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv
                    'disk_type_xhere' => true,          //磁盘块存储策略，xhere特殊显示

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,      //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择
                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'Virtio',            // virtio
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',                // SCSI
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',                // IDE
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'Virtio',            // virtio
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',            // e1000
                    'VM_MIDDLE_CONTROLLER_TYPE_SRIOV' => 'VF',            // sriov
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //目标目录
                $des_dir = $this->getDesDir($hostuuid);
                break;
            case $hypervisorConf['VM_HYPERVISOR_TYPE_EMD']:
                //显示控制
                $control = array(
                    'other_nav' => true,                //其他,横选项, 如果引导模式,可用域,root密码设置,目标资源池/目录,镜像,ha,虚拟化类型全部是false,这里就是false,表示界面不显示其他
                    'vm_version' => false,               //虚拟机版本
                    'boot_type' => false,               //引导模式
                    'available_domain' => false,        //可用域
                    'root_pass' => false,               //root密码设置
                    'des_dir' => false,                  //目标资源池/目录
                    'mirror_image' => false,            //镜像
                    'HA' => false,                      //ha
                    'virtual_type' => false,            //虚拟化类型

                    'cpu_general' => true,              //cpu,插槽和核心显示
                    'cpu_openstack' => false,           //cpu,OpenStack特殊显示,显示vcpu
                    'cpu_xsky_zstack' => false,         //cpu,zstack和xsky特殊显示,显示xzcpu

                    'disk_high_config' => true,         //磁盘是否显示高级配置
                    'disk_name_modify_flag' => true,   //磁盘名称是否可以修改
                    'disk_setting_mode' => true,        //磁盘置备模式
                    'disk_bus_type' => true,            //磁盘总线类型
                    'disk_cluster_size' => false,       //磁盘分块大小
                    'disk_type_hyperv' => false,         //磁盘类型，hyperv特殊显示，显示disktypehyperv

                    'network_high_config' => true,      //网络是否显示高级配置
                    'network_bus_type' => true,         //网卡类型
                    'network_ip_setting' => false,      //网卡IP设置
                    'os_type' => true,                    //跨平台恢复是否支持操作系统选择

                );

                //磁盘控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $disk_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                    'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                    'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
                );
                $disk_bus = $this->getControllerTypeDesArr($disk_bus);

                //网卡控制器类型,!!!!如果这个虚拟化平台有特别的类型称呼,才配置下面的值部分,不然就是用配置文件的名称,只要键名,不要值
                $network_bus = array(
                    'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VIRTIO',
                    'VM_MIDDLE_CONTROLLER_TYPE_E1000' => 'E1000',
                    'VM_MIDDLE_CONTROLLER_TYPE_RTL8139' => 'RTL8139',
                );
                $network_bus = $this->getControllerTypeDesArr($network_bus);

                //目标目录
                $des_dir = $this->getDesDir($hostuuid);
                break;
        }
        $control['data_encrypt'] = false;           //数据加密校验
        return array(
            'control' => $control,
            'disk_bus' => $disk_bus,
            'network_bus' => $network_bus,
            'available_domain' => $available_domain,
            'des_dir' => $des_dir,
            'mirror_image' => $mirror_image,
            'maintain_model' => $maintain_model,  //置备模式
            'disk_bus_hyperv' => $disk_bus_hyperv, // hyperv磁盘类型
        );

    }

    /**
     * 得到控制器描述数组
     * @param array $typeArr
     * @param bool $emdFlag 是否内嵌获取
     * @param string $emdType 内嵌获取类型：disk/net
     * @return array
     */
    private function getControllerTypeDesArr(array $typeArr, bool $emdFlag = false, string $emdType = ''): array
    {
        $vmConfig = xphp_get_config('vm_config');

        $vmMiddleControllerType = $vmConfig['VmMiddleControllerType'];
        $vmMiddleControllerTypeDes = $vmConfig['VmMiddleControllerTypeDes'];
        if ($emdFlag && 'disk' == $emdType) {
            $vmMiddleControllerType = $vmConfig['EmdVmTargetBus'];
            $vmMiddleControllerTypeDes = $vmConfig['EmdVmTargetBusDes'];
        }
        if ($emdFlag && 'net' == $emdType) {
            $vmMiddleControllerType = $vmConfig['EmdVmInterfaceMode'];
            $vmMiddleControllerTypeDes = $vmConfig['EmdVmInterfaceModeDes'];
        }

        $info = array();

        foreach ($typeArr as $key => $value) {
            $confNum = array_search($key, $vmMiddleControllerType);
            if (empty($value)) {
                //如果没有键值,就使用配置文件默认配置的名称
                $typeDes = $vmMiddleControllerTypeDes[$confNum];
            } else {
                //如果有键值,就是用这个键值
                $typeDes = $value;
            }
            $info[] = array(
                'key' => $confNum,
                'value' => $typeDes
            );
        }
        return $info;
    }

    /**
     * 得到内嵌虚拟化网卡控制器描述数组
     * @param array $typeArr
     * @return array
     */
    private function getEmdNetworkTypeDesArr(array $typeArr): array
    {
        $vmConfig = xphp_get_config('vm_config');

        $vmMiddleControllerType = $vmConfig['EmdVmInterfaceMode'];
        $vmMiddleControllerTypeDes = $vmConfig['EmdVmInterfaceModeDes'];
        $info = array();

        foreach ($typeArr as $key => $value) {
            $confNum = array_search($key, $vmMiddleControllerType);
            if (empty($value)) {
                //如果没有键值,就使用配置文件默认配置的名称
                $typeDes = $vmMiddleControllerTypeDes[$confNum];
            } else {
                //如果有键值,就是用这个键值
                $typeDes = $value;
            }
            $info[] = array(
                'key' => $confNum,
                'value' => $typeDes
            );
        }
        return $info;
    }

    /**
     * 得到宿主机下的所有目录(排除虚拟机)
     * @param string $hostuuid
     * @return array
     */
    private function getDesDir(string $hostuuid): array
    {
        //先获取宿主机信息,主要是取到dir_path,然后通过dir_path去查找,只查找主机和集群模式
        $sql = "select dir_path from vm_tree where host_uuid = ? and type = ? and display_mode = ?";
        $sqlParams = array(
            $hostuuid,
            xphp_get_config('vm', 'VM_TREE_TYPE')['HOST'],
            xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $dirPath = $data[0]['dir_path'];

        //获取所有宿主机下的目录
        $sql = "select uuid, dir_path from vm_tree where dir_path like '%" . $dirPath . "%' and display_mode = ? and type != ?";
        $sqlParams = array(
            xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'],
            xphp_get_config('vm', 'VM_TREE_TYPE')['VM']
        );
        $data = $this->dbSelect($sql, $sqlParams);

        $dirArr = array();
        foreach ($data as $d) {
            $dirArr[] = array(
                'key' => $d['uuid'],
                'value' => $d['dir_path']
            );
        }
        return $dirArr;
    }

    /**
     * 得到RHV/Ovirt/OLVM/易讯通等的磁盘控制器类型,
     * <4.4：IDE/VIRTIO/VIRTIO-SCSI
     * >=4.4：SATA/VIRTIO/VIRTIO-SCSI
     * @param string $vcenteruuid
     * @return array
     */
    private function getRhvOvirtDiskBus(string $vcenteruuid): array
    {
        $sql = "select version from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));

        $version = $data[0]['version'];
        if (substr($version, 0, 3) < "4.4") {
            $bus_type = array(
                'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                'VM_MIDDLE_CONTROLLER_TYPE_VSCSI' => 'VirtIO-SCSI',
                'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
            );
        } else {
            $bus_type = array(
                'VM_MIDDLE_CONTROLLER_TYPE_VIRTIO' => 'VirtIO',
                'VM_MIDDLE_CONTROLLER_TYPE_VSCSI' => 'VirtIO-SCSI',
                'VM_MIDDLE_CONTROLLER_TYPE_SCSI' => 'SCSI',
                'VM_MIDDLE_CONTROLLER_TYPE_IDE' => 'IDE',
                'VM_MIDDLE_CONTROLLER_TYPE_SATA' => 'SATA',
            );
        }
        return $bus_type;
    }

    /**
     * 创建虚拟机恢复/瞬时恢复/迁移任务检测目的虚拟化中心是否有同名的虚拟机存在
     * @param int $hypervisor  虚拟化类型
     * @param string $vcenteruuid  目的VCENTERUUID
     * @param array $vmnames       新的虚拟机列表
     * @return bool
     */
    private function createRecoverJobCheck(int $hypervisor, string $vcenteruuid, array $vmnames): bool
    {
        //ics/ics-vvdk支持虚拟机同名
        if (
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisor
            || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisor
            || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_KSPHERE'] == $hypervisor
        ) {
            return true;
        }
        //检查target虚拟化中心是否有相同名字的虚拟机存在，如果存在则对其进行提示
        $nameStr = '';
        foreach ($vmnames as $name) {
            $nameStr .= "'" . $name . "',";
        }
        $nameStr = substr($nameStr, 0, -1);
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware'])) {
            //如果是VMware
            $displayMode = xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_VM'];
            //         }elseif($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']){
        } else {
            //如果是XenServer
            $displayMode = xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'];
        }
        $sql = "select name from vm_tree where vcenter_uuid = ? and display_mode = ? and name in (" . $nameStr . ") ";
        $data = (array) $this->dbSelect($sql, array($vcenteruuid, $displayMode));
        if (!empty($data)) {
            //检查如果有相同名字的虚拟机,程序中断返回提示信息
            $msg = xphp_get_lang('WEB_JOB_CREATE_VM_RECOVERY_TIPS');
            foreach ($data as $key => $d) {
                if ($key == (count($data) - 1)) {
                    $msg .= "'" . $d['name'];
                } else {
                    $msg .= "'" . $d['name'] . "',";
                }
            }
            $msg .= "'";
            exit($this->muOpResult(false, xphp_get_lang('UI_RECOVERY_VM_DESCRIPTION'), $msg, 'warning'));
        }
        return true;
    }

    /**
     * 跨平台恢复检查
     * @param int $oldHypervisor    原虚拟化平台
     * @param int $newHypervisor    新虚拟化平台
     * @return bool
     */
    private function crossHypervisorRecoveryCheck(int $oldHypervisor, int $newHypervisor): bool
    {
        //如果是本平台恢复,不做检查
        if ($oldHypervisor == $newHypervisor) {
            return true;
        }

        $icsTypes = [
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'],
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_INSPUR_VVDK']
        ];
        $sangforTypes = [
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SANGFOR_KVM'],
            xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']
        ];
        // 同组之间恢复不做检查
        if (
            in_array($oldHypervisor, $icsTypes) && in_array($newHypervisor, $icsTypes)
            || in_array($oldHypervisor, $sangforTypes) && in_array($newHypervisor, $sangforTypes)
        ) {
            return true;
        }

        //获取系统的授权
        $systemHandler = SystemHandler::instance();
        $license = $systemHandler->getSystemLisenceInfo();
        $license = json_decode($license, true);

        //如果跨平台恢复未授权,不能恢复
        if (true !== $license['authfun']['crossHypervisorRecovery']) {
            $msg = xphp_get_lang('UI_RECOVERY_CHECK_CROSS_HYPERVISOR_LICENSE_TIPS1');
            exit($this->muOpResult(false, xphp_get_lang('UI_RECOVERY_VM_DESCRIPTION'), $msg, 'warning'));
        }
        //如果跨平台恢复授权数量不足,不能恢复
        if ($license['v2v']['valid'] <= 0 && $license['v2v']['total'] != -1) {
            $msg = xphp_get_lang('UI_RECOVERY_CHECK_CROSS_HYPERVISOR_LICENSE_TIPS2');
            exit($this->muOpResult(false, xphp_get_lang('UI_RECOVERY_VM_DESCRIPTION'), $msg, 'warning'));
        }

        return true;
    }

    /**
     * 组合恢复虚拟机和时间点,新名字信息
     * @param int $recoveryPos 原机/异机
     * @param array $points 时间点信息
     * @param array $names 名字信息
     * @return array
     */
    private function groupRebuildVMInfo($recoveryPos, $points, $recoverInfo)
    {
        $drivers = array_column($recoverInfo['drivers'], null, 'timepoint_uuid');
        $pointInfo = array();
        $i = 0;
        foreach ($points as $point) {
            $info = array(
                'vm_uuid' => $point['vmuuid'],
                'vm_name' => $point['vmname'],
                'new_name' => $recoverInfo['vmconfigs'][$i]['vmname'],
                'timepoint_uuid' => $point['timepointuuid'],
                'destination_host_uuid' => self::VM_RECOVERY_WAY_NEW == $recoverInfo['target_way'] ? $recoverInfo['hostuuid'] : $recoverInfo['vmconfigs'][$i]['target_host_uuid'],
                'destination_vcenter_uuid' => self::VM_RECOVERY_WAY_NEW == $recoverInfo['target_way'] ? $recoverInfo['vcenteruuid'] : $recoverInfo['vmconfigs'][$i]['target_platform_uuid'],
                'auto_datastore_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
                'destination_datastore_name' => '',
                'start_vm_flag' => v2_parse_bool_to_flag($recoverInfo['vmconfigs'][$i]['power'] ?? false),
                'vm_config' => $this->groupVMConfig($recoverInfo['vmconfigs'][$i], $point),
                'extension_info' => $this->groupExtensionInfo($recoverInfo, $recoverInfo['vmconfigs'][$i]),
                'project_uuid' => $point['projectid'],
                'before_task_script' => $this->groupScriptConfig($recoverInfo['vmconfigs'][$i]['other']['script_before_data'] ?? []),
                'after_task_script' => $this->groupScriptConfig($recoverInfo['vmconfigs'][$i]['other']['script_data'] ?? []),
            );
            // extension_info添加驱动替换的参数
            $extensionInfo = json_decode($info['extension_info'], true);
            $driverInfo = $drivers[$point['timepointuuid']];
            // 如果不支持添加驱动，传给后台的消息中驱动映射map为空字符串，是否替换驱动为false
            if ($driverInfo && $driverInfo['driver_check_status'] != 8) {
                $extensionInfo['driver_replace_flag'] = v2_parse_bool_to_flag($driverInfo['platform_type'] == 2); // 仅异构需替换驱动
                $extensionInfo['driver_hw_id_map'] = $driverInfo['driver_hw_id_map'];
            } else {
                $extensionInfo['driver_replace_flag'] = v2_parse_bool_to_flag(false);
                $extensionInfo['driver_hw_id_map'] = '';
            }
            $info['extension_info'] = json_encode($extensionInfo, JSON_UNESCAPED_UNICODE);
            $pointInfo[] = $info;
            $i++;
        }
        return $pointInfo;
    }

    /**
     * 组合虚拟机配置 revovery处会用到
     * @param array $vmConfig
     * @param array $point
     * @param string $cdpDatetime
     * @param bool $motionFlag
     * @return string
     */
    public function groupVMConfig(array $vmConfig, array $point = [], string $cdpDatetime = '', bool $motionFlag = false): string
    {
        $other = $vmConfig['other'];
        $info = array(
            'orig_vm_uuid' => $point['vmuuid'] ?? '',
            'orig_vm_name' => $point['vmname'] ?? '',
            'new_vm_name' => $vmConfig['vmname'],
            'cpu_socket' => intval($vmConfig['cpu_socket']),
            'cores_per_socket' => intval($vmConfig['cpu_core']),
            'cpu_type' => intval($vmConfig['cpu_type']),
            'cpu_mode' => $vmConfig['cpu_mode'] ? intval($vmConfig['cpu_mode']) : xphp_get_config('vm_config', 'BdMiddleCpuModeType')['BD_MIDDLE_CPU_MODE_TYPE_DEFAULT'],
            'vm_memory' => $this->calUnitToSize($vmConfig['vm_memory'], $vmConfig['vm_memory_unit']),
            'auto_conf_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'boot_mode' => intval($other['boot_type_select']),  //引导模式
            'vm_version' => $other['vm_version'],
            'vm_network_list' => $this->processRecoveryVmNetwork($vmConfig['network'], $motionFlag),    //网络
            'vm_disk_list' => $this->processRecoveryVmDisk($vmConfig['storage']),          //磁盘
            'zone_config' => $this->processRecoveryVmZoneConfig($other['available_domain_select'] ?? ''),  //虚拟机可用域
            'disk_zone_config' => $this->processRecoveryVmZoneConfig($other['disk_domain_select'] ?? ''), //磁盘可用域
            'video_device' => array('video_type' => 0, 'vram_memory' => 0),                              //显卡,暂时没有用,保留字段
            'other_config' => $this->processRecoveryVmOtherConfig($other),                 //其他配置
            'openstack_start_mode' => intval($vmConfig['openstack_start_mode']),
            'root_disk_size' => $this->calUnitToSize(intval($vmConfig['root_disk_size']), "GB"),
            'os_type' => $vmConfig['os_type'],
            'os_version' => $vmConfig['os_version'],
            'delete_on_termination' => $vmConfig['openstack_del_vm_del_disk_mode'] ?? false,
            'flavor_id' => $vmConfig['flavor_id'],//openstack实例类id
            'original_recovery' => $other['original_recovery'],
            'image_metadata' => json_encode($other['image_metadata']),
            'reset_hostname_flag' => v2_parse_bool_to_flag($other['reset_hostname']),
            'new_hostname' => trim($other['new_hostname']),
            'reset_bios_uuid_flag' => v2_parse_bool_to_flag($other['reset_biosuuid']),
            'cdp_datetime' => $cdpDatetime,
            'new_vm_uuid' => $vmConfig['target_vm_uuid'] ?? '',
        );
        return json_encode($info, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 转化数字和单位组合形式为数字
     * @param int $num
     * @param string $unit
     * @return mixed|string
     */
    public function calUnitToSize($num, $unit)
    {
        $type = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB");
        $size = 0;
        foreach ($type as $key => $value) {
            if ($value == $unit) {
                $size = $num * pow(1024, $key);
                break;
            }
        }
        return $size;
    }

    /**
     * 组合处理恢复虚拟机的网卡数据,适应后台数据结构
     * @param array $networkList
     * @param bool $motionFlag
     * @return array
     */
    public function processRecoveryVmNetwork(array $networkList, bool $motionFlag = false): array
    {
        foreach ($networkList as $key => &$value) {
            $networkList[$key]['bus_type'] = intval($value['bus_type']) ?? 0;
            if ($motionFlag) {
                $networkList[$key]['ipv4_list'] = [];
                $networkList[$key]['ipv6_list'] = [];
                $networkList[$key]['reset_nic_list'] = [];
                continue;
            }
            $networkList[$key]['ipv4_list'] = $value['ipv4_set']['ip_set'] ? array_column($value['ipv4_set']['ip_set'], 'ip') : [];
            $networkList[$key]['ipv6_list'] = $value['ipv6_set']['ip_set'] ? array_column($value['ipv6_set']['ip_set'], 'ip') : [];
            $networkList[$key]['reset_nic_list'] = [
                [
                    'reset_network_flag' => v2_parse_bool_to_flag($value['modify_network_flag']),
                    'ip_protocol' => 'ipv4',
                    'gateway' => $value['ipv4_set']['gateway'],
                    'method' => $value['ipv4_set']['config_type'], // 1-自动，2-手动
                    'set_ip_list' => [],
                    'dns_list' => $value['ipv4_set']['dns2'] ? [$value['ipv4_set']['dns1'], $value['ipv4_set']['dns2']] : [$value['ipv4_set']['dns1']]
                ],
                [
                    'reset_network_flag' => v2_parse_bool_to_flag($value['modify_network_flag']),
                    'ip_protocol' => 'ipv6',
                    'gateway' => $value['ipv6_set']['gateway'],
                    'method' => $value['ipv6_set']['config_type'], // 1-自动，2-手动
                    'set_ip_list' => [],
                    'dns_list' => $value['ipv6_set']['dns2'] ? [$value['ipv6_set']['dns1'], $value['ipv6_set']['dns2']] : [$value['ipv6_set']['dns1']]
                ],
            ];
            foreach ($value['ipv4_set']['ip_set'] as $item) {
                if (filter_var($item['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    // 添加到ipv4的set_ip_list
                    $networkList[$key]['reset_nic_list'][0]['set_ip_list'][] = [
                        'ip_addr' => $item['ip'],
                        'netmask' => $item['netmask'],
                    ];
                }
            }
            foreach ($value['ipv6_set']['ip_set'] as $item) {
                if (filter_var($item['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    // 添加到ipv6的set_ip_list
                    $networkList[$key]['reset_nic_list'][1]['set_ip_list'][] = [
                        'ip_addr' => $item['ip'],
                        'netmask' => $item['netmask'],
                    ];
                    ;
                }
            }
            // 去掉后台用不到的字段
            unset($value['ipv4_set'], $value['ipv6_set']);
        }
        return $networkList;
    }

    /**
     * 组合处理恢复虚拟机的磁盘数据,适应后台数据结构
     * @param array $diskList
     * @return array
     */
    public function processRecoveryVmDisk(array $diskList): array
    {
        foreach ($diskList as $key => $value) {
            $diskList[$key]['src_disk_uuid'] = base64_decode($value['src_disk_uuid']);
            $diskList[$key]['disk_size'] = $value['size'];
            $diskList[$key]['cache_type'] = 0;  //缓存类型,保留字段
            $diskList[$key]['preallocated_type'] = 0;  // 预分配，针对vmware等,保留字段
            $diskList[$key]['policy_id'] = $value['policy_id'] ?: ''; //xhere块存储策略id
            $diskList[$key]['bus_type'] = intval($value['bus_type']) ?? 0;
            $diskList[$key]['disk_type'] = intval($value['disk_type']) ?? 0;
            $diskList[$key]['target_disk_uuid'] = $value['target_disk_uuid'] ?? '';

            // 处理hyper-v路径和uuid带"\"
            $diskList[$key]['disk_name'] = $value['disk_name'] ? str_replace('\\\\', '\\', $value['disk_name']) : '';
            $diskList[$key]['target_storage_uuid'] = $value['target_storage_uuid'] ? str_replace('\\\\', '\\', $value['target_storage_uuid']) : '';
        }
        return $diskList;
    }

    /**
     * 组合处理恢复虚拟机的可用域,适应后台数据结构
     * @param string $domain
     * @return array
     */
    public function processRecoveryVmZoneConfig(string $domain): array
    {
        if (empty($domain)) {
            $zoneConfig = array(
                'zone_name' => '',
                'auto_conf_flag' => xphp_get_config('app', 'FLAG')['SET'],
            );
        } else {
            $zoneConfig = array(
                'zone_name' => $domain,
                'auto_conf_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            );
        }
        return $zoneConfig;
    }

    /**
     * 组合处理恢复虚拟机的其他配置,适应后台数据结构
     * @param array $other
     * @return array
     */
    public function processRecoveryVmOtherConfig(array $other): array
    {
        return array(
            'root_password' => $other['root_pass_input'],               //root密码
            'target_resource_pool_or_dir' => $other['des_dir_select'],  //恢复目标池或者目录
            'image_uuid' => $other['mirror_image_select'],              //镜像UUID
            'is_ha' => $other['ha'],                                    //是否设置HA开关，针对ovirt、深信服等
            'is_cluster' => false,                                      //集群开关，预留
            'cpu_type' => "",                                           //cpu类型，针对ovirt，预留
            'emulator_type' => "",                                      //仿真机类型，针对ovirt，预留
            'vm_flavor_id' => "",                                       //虚拟机flavor，预留
            'virtualization_type' => $other['virtual_type_select'],      //虚拟化类型,XenServer/XCP-NG/vGate使用,HVM/PV,
        );
    }

    /**
     * 组合extensionInfo
     * @param array $recoverInfo
     * @param array $vmConfig
     * @return string
     */
    public function groupExtensionInfo(array $recoverInfo, array $vmConfig = []): string
    {
        if (self::VM_RECOVERY_WAY_SPECIFIC == $recoverInfo['target_way']) {
            $info = array(
                'group_name' => $vmConfig['target_host_name'],
                'group_uuid' => $vmConfig['target_host_uuid'],
                'user_name' => $vmConfig['target_username'],
                'password' => v2_pt_pass_decrypt($vmConfig['target_password']),
                'controller_ip' => $recoverInfo['controllerip'],
                'region' => $vmConfig['target_region']
            );
        } else {
            $info = array(
                'group_name' => $recoverInfo['groupname'] ?: $recoverInfo['node']['groupname'],
                'group_uuid' => $recoverInfo['groupuuid'] ?: $recoverInfo['node']['groupuuid'],
                'user_name' => $recoverInfo['username'] ?: $recoverInfo['node']['username'],
                'password' => $recoverInfo['password'] ? v2_pt_pass_decrypt($recoverInfo['password']) : v2_pt_pass_decrypt($recoverInfo['node']['password']),
                'controller_ip' => $recoverInfo['controllerip'],
                'region' => $recoverInfo['region'] ?: $recoverInfo['node']['name']
            );
        }

        return json_encode($info, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 组合脚本配置
     * @param array $scriptData
     * @return string
     */
    public function groupScriptConfig(array $scriptData = []): string
    {
        return json_encode($scriptData, JSON_UNESCAPED_UNICODE);
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
                and bt.module_type = ? and bt.task_type = ?
                order by bt.id desc";
        $data = $this->dbSelect($sql, array($taskName, xphp_get_config('module', 'MODULE_TYPE')['VM'], xphp_get_config('task', 'TASKTYPE')['RECOVERY']));
        if (!$data)
            return false;

        $result = VmJobController::instance()->startJob($data[0]['task_uuid'], 1);
        //这里直接返回成功或失败 bool
        return $result[0] ?? false;
    }

    /**
     * 组合瞬时恢复数据
     * @param array $pointInfo
     * @param array $recoverInfo
     * @return array
     */
    private function groupInstantVMInfo(array $pointInfo, array $recoverInfo): array
    {
        $hostuuid = $recoverInfo['hostuuid'];
        $hypervisor = intval($recoverInfo['hypervisor']);
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $hostuuid = $recoverInfo['groupuuid'];
        }
        return array(
            'orig_vm_uuid' => $pointInfo['vmuuid'],      //虚拟机uuid
            'orig_vm_name' => $pointInfo['vmname'],      //虚拟机名字
            'orig_vm_version' => $pointInfo['version'],   //虚拟机版本
            'new_vm_name' => $recoverInfo['newname'],         //虚拟机新名字
            'timepoint_uuid' => $pointInfo['uuid'],              //时间点uuid
            'target_vcenter_uuid' => $recoverInfo['vcenteruuid'], //恢复到vcenter uuid
            'target_host_uuid' => $hostuuid,   //恢复到数组机 uuid
            'nfs_server_ip' => $recoverInfo['addr'],          //挂载点IP
            'start_vm_flag' => v2_parse_bool_to_flag($recoverInfo['startvm']),     //启动标志
            'advance_vm_config' => $this->groupVMConfig($recoverInfo['vmconfigs'][0]),       //虚拟机配置
            'extension_info' => $this->groupExtensionInfo($recoverInfo),    //扩展消息
        );
    }

    /**
     * 将子网掩码转换为整数
     * @param string $mask
     * @return int
     */
    private function maskToPrefixLength(string $mask): int
    {
        // 将子网掩码转换为整数
        $mask = ip2long($mask);
        // 计算子网掩码的主机位数
        $prefixLength = 0;
        for ($i = 0; $i < 32; $i++) {
            if (($mask & (1 << $i)) != 0) {
                $prefixLength++;
            }
        }
        return $prefixLength;
    }
}