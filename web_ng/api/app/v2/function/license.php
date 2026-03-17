<?php

/**
 * v2 版本 获取授权系统函数文件
 * 以  v2_license_ 开头，单词之间用 _ 下划线隔开，单词用小写
 *
// 枚举定义
$modules = [
'vm',  // 虚拟机
'file', // 文件
'oracle', // 数据库(不包含sap_hana)
'oracle_hana', // sap_hana数据库
'os', // 操作系统
'nas', // nas
'hadoop', // hadoop
'obs', // ob对象存储
'cdp', // cdp
'exchange', // exchange(server)
'exchange_online', // exchange_online
'k8s', // k8s
'private_cloud', // 私有云
'cloud', // 公有云
'vol_cdp', // 实时接管
'emergency_takeover', // 应急接管（内嵌）
'v2v', // 跨平台恢复
verify, // 数据验证
copy_machine, // 整机复制
copy_db, // 数据库复制
copy_fs, // 本地文件复制
copy_nas, // nas复制
copy_hadoop, // hadoop复制
copy_obs, // obs复制
 */

/**
 * 获取已经使用的容量
 * @param int    $type   默认全部容量
 *                       1定时容量
 *                       2实时容量
 * @param string $subsql 用户uuid子查询语句
 * @return int
 */
function v2_license_get_capacity_used($type = 0, $subsql = ''): int
{

    $sql1 = "SELECT sum(BBT.write_size) total FROM
                                bd_backup_timepoint BBT
                    WHERE (BBT.task_type='1' OR BBT.task_type='13'
                       OR BBT.task_type='28' OR BBT.task_type='35' OR BBT.task_type='56')";

    $where = '';
    if (!empty($subsql)) {
        $where = " and BBT.user_uuid in $subsql";
        $sql1 .= $where;
    }
    // 如果排除了复制容量，那么就只计算 时间点里面的任务类型为 32的，否则需要加上 65
    $exclude = v2_license_info('exclude_client_replication_capacity_flag');
    if ($exclude[0]['exclude_client_replication_capacity_flag'] == 1) {
        $taskType = xphp_get_config('task', 'TASKTYPE');
        // 排除复制容量
        $where .= " and BBT.task_type = {$taskType['VOL_CDP_BACKUP']} ";
    }

    $sql2 = "SELECT (CASE WHEN a.total_size IS NULL THEN 0 ELSE a.total_size END) AS total FROM 
                        (
                            SELECT (SUM(CVBVS.backup_file_size) + SUM(CVBVS.log_file_total_size)) AS total_size
                                FROM cdp_vol_backup_vol_set CVBVS 
                                INNER JOIN cdp_vol_backup_agent CVBA ON CVBVS.backup_agent_id = CVBA.id 
                                INNER JOIN bd_backup_timepoint BBT ON CVBA.timepoint_uuid = BBT.timepoint_uuid 
                                WHERE BBT.storage_uuid IN (SELECT BSR.storage_uuid FROM bd_storage_resource BSR)
                                 {$where}
                        )a";

    if ($type == 1) {
        $data = dbSelect($sql1);
        $num = intval($data[0]['total']) ?? 0;
    } elseif ($type == 2) {
        $data = dbSelect($sql2);
        $num = intval($data[0]['total']) ?? 0;
    } else {
        $data = dbSelect($sql1);
        $num = intval($data[0]['total']) ?? 0;
        $data = dbSelect($sql2);
        $num += intval($data[0]['total']) ?? 0;
    }

    return $num;
}

/**
 * 获取已经使用的数量(虚拟机)
 * @param int    $licenseType 虚拟机的授权类型有 1 host个数
 *                            2cpu个数 3,604之前才存在 4vm个数
 * @param string $subsql      用户信息子查询
 * @return int
 */
function v2_license_get_vm_used(int $licenseType, $subsql = ''): int
{

    $licenseInfo = xphp_get_config('auth', 'LISENCE_INFO')['type'];
    $flag = xphp_get_config('app', 'FLAG');
    $moduleType = xphp_get_config('module', 'MODULE_TYPE');
    $taskType = xphp_get_config('task', 'TASKTYPE');
    $hyperversionType = xphp_get_config('vm', 'VMHYPERVISORGROUP');
    $notIn = implode(',', array_merge($hyperversionType['openstack'], $hyperversionType['publiccloud']));
    switch ($licenseType) {
        case $licenseInfo['host']:
            $sql = "select count(vh.host_id) as total from vm_host vh,vm_vcenter vv
                    where vh.vcenter_uuid = vv.vcenter_uuid and
                          vv.hypervisor_type not in ({$notIn}) and vh.authorization_flag = " . $flag['SET'];
            if (!empty($subsql)) {
                $sql .= " and vv.user_uuid in $subsql ";
            }
            break;
        case $licenseInfo['cpu']:
            $sql = "select sum(vh.cpu_count) as total from vm_host vh,vm_vcenter vv
                    where vh.vcenter_uuid = vv.vcenter_uuid and
                          vv.hypervisor_type not in ({$notIn}) and vh.authorization_flag = " . $flag['SET'];
            if (!empty($subsql)) {
                $sql .= " and vv.user_uuid in $subsql ";
            }
            break;
        case $licenseInfo['storage']:
            $sql = "select sum(bbt.write_size) as total from bd_backup_timepoint bbt,vm_backup_timepoint vbt
                    where vbt.hypervisor_type not in ({$notIn}) and vbt.timepoint_uuid = bbt.timepoint_uuid ";
            if (!empty($subsql)) {
                $sql .= " and bbt.user_uuid in $subsql ";
            }
            break;
        case $licenseInfo['vm']:
            $sql = "select count(vml.vm_uuid) as total from vm_machine_list vml, bd_task bt, vm_vcenter vv 
                        where vml.task_uuid = bt.task_uuid and vml.vcenter_uuid = vv.vcenter_uuid
                          and vv.hypervisor_type not in ({$notIn})
                          and bt.task_type = " . $taskType['BACKUP'];
            // 更改为
            $subModuleType = xphp_get_config('module', 'VM_SUB_MODULE');
            $sql = 'select count(vblr.vm_uuid) as total
                            from vm_backup_license_records vblr,vm_vcenter vv 
                        where vblr.vcenter_uuid = vv.vcenter_uuid and vblr.mode = ' . $subModuleType['VM'];
            if (!empty($subsql)) {
                $sql .= " and vv.user_uuid in $subsql ";
            }
            break;
    }

    if (!empty($sql)) {
        $data = dbSelect($sql);
        return $data[0]['total'] ?? 0;
    }

    return 0;
}

/**
 * 获取已经使用的个数--虚拟机、私有云和公有云
 * @param int $mode 类型 1虚拟机、2私有有、3公有云
 * @return int
 */
function v2_license_get_virtualization_used_num(int $mode = 1): int
{
    $sql = 'select count(vm_uuid) as total from vm_backup_license_records where mode = ?';
    $data = dbSelect($sql, [$mode]);
    return $data[0]['total'] ?? 0;
}

/**
 * 获取已经使用的(cdp)
 * @param int    $authType cdp的类型有
 *                         1个数 2容量
 * @param string $subsql   用户信息子查询
 * @return int
 */
function v2_license_get_cdp_used(int $authType, $subsql = ''): int
{
    if ($authType == 2) {
        return v2_license_get_capacity_used(2, $subsql);
    }
    $taskType = xphp_get_config('task', 'TASKTYPE');
    $sql = 'SELECT COUNT(*) total FROM bd_task WHERE task_type = ?';
    if (!empty($subsql)) {
        $sql .= " and user_uuid in $subsql ";
    }

    $data = dbSelect($sql, [$taskType['VOL_CDP_BACKUP']]);

    return intval($data[0]['total']);
}

/**
 * 获取已经使用的数量（主机系列)
 * 主机保护类型的，暂时有6个模块， file oracle os nas hadoop obs
 * exchange exchange_online private_cloud cloud k8s vol_cdp emergency_takeover v2v verify
 * copy_machine copy_db copy_fs copy_nas copy_hadoop copy_obs
 * @param string|array $module   模块标识
 *                               数组表示会查询多个的合集
 * @param array        $uuids    当前任务正在使用的主机uuid集合，用于创建任务处判断授权
 * @param string       $taskuuid 任务uuid,用于修改任务处判断授权
 * @param string       $subsql   用户信息子查询
 * @return int
 */
function v2_license_get_num_used($module, $uuids = [], $taskuuid = '', $subsql = ''): int
{
    $user = xphp_get_user_info();
    $num = 0;
    $flag = xphp_get_config('app', 'FLAG');

    if (!is_array($module)) {
        $module = explode(',', $module);
    }
    if (in_array('nas', $module)) {
        // nas
        $sql = "SELECT DISTINCT
                    fpl.agent_uuid AS nas_uuid 
                FROM
                    fs_path_list fpl,
                    bd_task bt 
                WHERE
                    bt.task_uuid = fpl.task_uuid 
                    AND bt.task_type = ? 
                    AND bt.sub_module_type = ?";
        //用于创建任务处判断授权
        if (!empty($uuids)) {
            $uuids2 = "'" . implode("','", $uuids) . "'";
            $sql .= " AND fpl.agent_uuid NOT IN ($uuids2)";
        }
        //修改任务检查
        if (!empty($taskuuid)) {
            $sql .= ' and bt.task_uuid !=  "' . $taskuuid . '"' ;
        }

        if (!empty($subsql)) {
            $sql .= " and bt.user_uuid in {$subsql} ";
        }
        $dataNas = dbSelect(
            $sql,
            array(xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('module')['SUBMODULE_TYPE']['NAS'])
        );
        if (!empty($dataNas)) {
            //nas是根据ip判断授权数量
            $nasUuids = array_column($dataNas, 'nas_uuid');
            $nasDes = implode("','", $nasUuids);
            $sqlIp = "SELECT DISTINCT ip FROM nas_storage_resource WHERE nas_uuid IN ('" . $nasDes . "')";
            $num = count(dbSelect($sqlIp));
        } else {
            $num = 0;
        }
    }
    if (in_array('exchange', $module) || in_array('exchange_online', $module)) {
        // exchange
        if (in_array('exchange', $module)) {
            // server
            $where = ' and mo.region = 100 ';
        } else {
            $where = ' and mo.region != 100 ';
        }
        if (!empty($subsql)) {
            $where .= " and bbt.user_uuid in $subsql ";
        }
        if (!empty($taskuuid)) {
            $where .= " and bbt.task_uuid != '{$taskuuid}' ";
        }
        $sql = 'WITH latest_timepoints AS (
            SELECT mbt.organization_info,bbt.timepoint,bbt.task_uuid,
                ROW_NUMBER() OVER ( PARTITION BY bbt.task_uuid ORDER BY bbt.timepoint DESC ) AS rn 
            FROM m365_task mt,bd_backup_timepoint bbt,m365_backup_timepoint mbt,m365_organization mo
            WHERE
                bbt.available_flag = 1  AND mt.m365_type = 1 AND bbt.task_uuid = mt.task_uuid 
                AND bbt.timepoint_uuid = mbt.m365_timepoint_uuid and mo.organization_uuid = mbt.organization_uuid '
            . $where . '
            )
	        SELECT organization_info FROM latest_timepoints WHERE rn = 1 ORDER BY timepoint DESC';

        $data = dbSelect($sql);
        foreach ($data as $item) {
            $organizationInfo = json_decode($item['organization_info'], true);
            $num += intval($organizationInfo['current_reserverd_user_num']);
        }
    }

    // file oracle os 是在当前任务列表根据模块关联
    $machineFlag = false; // 标记是否是整体 file oracle os
    $machineFlag2 = false; // 标记是否是整体 oracle os
    $machineList = [];
    // 如果是 file oracle os 整体授权，那么就得所有的客户端去重在算和
    if (
        in_array('file', $module) && in_array('oracle', $module)
        && in_array('os', $module) && count($module) == 3
    ) {
        $machineFlag = true;
    }

    // 如果是 oracle os 整体授权，那么就得所有的客户端去重在算和
    if (
        in_array('oracle', $module) && in_array('os', $module) && count($module) == 2
    ) {
        $machineFlag2 = true;
    }
    $machineList = [];
    // bd_task里面的module_type 和 task_type 关联 bd_task_agent_list 表的task_uuid
    $sql = "select distinct btal.agent_uuid from bd_task bt,bd_task_agent_list btal
                where bt.module_type = ? and bt.task_uuid = btal.task_uuid";

    $moduleArr = xphp_get_config('module', 'MODULE_TYPE');
    $taskType = xphp_get_config('task', 'TASKTYPE');
    $subModuleArr = xphp_get_config('module', 'SUBMODULE_TYPE');
    if (in_array('file', $module)) {
        // file 的是 module_type = 3 and sub_module_type = 1
        $sqls = $sql . ' and bt.sub_module_type = ? and bt.task_type = ?';
        //用于创建任务处判断授权
        if (!empty($uuids)) {
            $uuids2 = "'" . implode("','", $uuids) . "'";
            $sqls .= " and btal.agent_uuid not in ($uuids2)";
        }
        //修改任务检查
        if (!empty($taskuuid)) {
            $sqls .= ' and bt.task_uuid !=  "' . $taskuuid . '"' ;
        }
        if (!empty($subsql)) {
            $sqls .= " and bt.user_uuid in $subsql ";
        }
        $sqlParam = [$moduleArr['FS'], $subModuleArr['FS'],$taskType['BACKUP']];
        $list = dbSelect($sqls, $sqlParam);
        if (!$machineFlag) {
            $num += count($list);
        } else {
            $machineList = array_column($list, 'agent_uuid');
        }
    }

    $sqlOracle = "select distinct btal.agent_uuid from bd_task bt,bd_task_agent_list btal,db_task dt
                where bt.module_type = ? and bt.task_uuid = btal.task_uuid and bt.task_uuid = dt.task_uuid";
    if (in_array('oracle', $module)) {
        // oracle 的是 module_type = 4
        $sql2 = $sqlOracle . " and bt.task_type = 28 and dt.db_type != 13
                                and (btal.cluster_uuid is null or btal.cluster_uuid = '')";
        $sqlParam = [$moduleArr['DB']];
        if (!empty($subsql)) {
            $sql2 .= " and bt.user_uuid in $subsql ";
        }
        //修改任务检查
        if (!empty($taskuuid)) {
            $sql2 .= ' and bt.task_uuid !=  "' . $taskuuid . '"' ;
        }

        $list = dbSelect($sql2, $sqlParam);

        // 要考虑集群的情况 需要去 bd_agent_app 表根据 cluster_uuid 查询所有的 agent_uuid ，然后再去重
        $sqlss = " and btal.cluster_uuid is not null and btal.cluster_uuid != ''";
        //修改任务检查
        if (!empty($taskuuid)) {
            $sqlss .= ' and bt.task_uuid !=  "' . $taskuuid . '"' ;
        }
        $sqls = "select distinct agent_uuid 
                        from bd_agent_app
                        where cluster_uuid in 
                              (select btal.cluster_uuid from bd_task bt,bd_task_agent_list btal,db_task dt
                where bt.module_type = ? and bt.task_uuid = btal.task_uuid and bt.task_uuid = dt.task_uuid
                  and dt.db_type != 13 {$sqlss})";

        $list2 = dbSelect($sqls, $sqlParam);

        $lists = array_filter(array_unique(
            array_merge(
                array_column($list, 'agent_uuid'),
                array_column($list2, 'agent_uuid')
            )
        ));
        if (!empty($uuids)) {
            $lists = array_values(array_diff($lists, $uuids));
        }
        if (!$machineFlag && !$machineFlag2) {
            $num += count($lists);
        } else {
            $machineList = array_merge($machineList, $lists);
        }
    }

    if (in_array('oracle_hana', $module)) {
        // oracle 的是 module_type = 4 查询hala数据库的
        $sql2 = $sqlOracle . " and bt.task_type = 28 and (btal.cluster_uuid is null or btal.cluster_uuid = '')
         and dt.db_type = 13";
        $sqlParam = [$moduleArr['DB']];
        if (!empty($subsql)) {
            $sql2 .= " and bt.user_uuid in $subsql ";
        }
        //修改任务检查
        if (!empty($taskuuid)) {
            $sql2 .= ' and bt.task_uuid !=  "' . $taskuuid . '"' ;
        }
        $list = dbSelect($sql2, $sqlParam);

        // 要考虑集群的情况 需要去 bd_agent_app 表根据 cluster_uuid 查询所有的 agent_uuid ，然后再去重
        $sqlss = " and btal.cluster_uuid is not null and btal.cluster_uuid != ''";
        //修改任务检查
        if (!empty($taskuuid)) {
            $sqlss .= ' and bt.task_uuid !=  "' . $taskuuid . '"' ;
        }
        $sqls = "select distinct agent_uuid 
                        from bd_agent_app
                        where cluster_uuid in 
                              (select btal.cluster_uuid from bd_task bt,bd_task_agent_list btal,db_task dt
                where bt.module_type = ? and bt.task_uuid = btal.task_uuid and bt.task_uuid = dt.task_uuid
                  and dt.db_type = 13 {$sqlss})";

        $list2 = dbSelect($sqls, $sqlParam);

        $lists = array_filter(array_unique(
            array_merge(
                array_column($list, 'agent_uuid'),
                array_column($list2, 'agent_uuid')
            )
        ));
        if (!empty($uuids)) {
            $lists = array_values(array_diff($lists, $uuids));
        }
        $num += count($lists);
    }

    if (in_array('os', $module)) {
        // os 的是 module_type = 5
        $sqlParam = [$moduleArr['OS']];
        $sql2 = $sql . ' and bt.task_type = 35 ';
        if (!empty($subsql)) {
            $sql2 .= " and bt.user_uuid in $subsql ";
        }
        $list = dbSelect($sql2, $sqlParam);
        if (!$machineFlag && !$machineFlag2) {
            $num += count($list);
        } else {
            $lists = array_column($list, 'agent_uuid');
            $machineList = array_merge($machineList, $lists);
        }
    }

    if ($machineFlag || $machineFlag2) {
        $num += count(array_filter(array_unique($machineList)));
    }

    if (in_array('hadoop', $module)) {
        // hadoop bd_task_agent_list 子表查询具体的 uuid
        // hadoop 的是 module_type = 3 and sub_module_type = 3
        $sqls = $sql . ' and bt.sub_module_type = ? and bt.task_type = ?';
        //用于创建任务处判断授权
        if (!empty($uuids)) {
            $uuids2 = "'" . implode("','", $uuids) . "'";
            $sqls .= " and btal.agent_uuid not in ($uuids2)";
        }
        //修改任务检查
        if (!empty($taskuuid)) {
            $sqls .= ' and bt.task_uuid !=  "' . $taskuuid . '"' ;
        }
        if (!empty($subsql)) {
            $sqls .= " and bt.user_uuid in $subsql ";
        }
        $sqlParam = [$moduleArr['FS'], $subModuleArr['HADOOP'], $taskType['BACKUP']];
        $list = dbSelect($sqls, $sqlParam);
        $num += count($list);
    }

    if (in_array('obs', $module)) {
        // obs 的是 module_type = 3 and sub_module_type = 4
        $sqls = $sql . ' and bt.sub_module_type = ? and bt.task_type = ?';
        //用于创建任务处判断授权
        if (!empty($uuids)) {
            $uuids2 = "'" . implode("','", $uuids) . "'";
            $sqls .= " and btal.agent_uuid not in ($uuids2)";
        }
        //修改任务检查
        if (!empty($taskuuid)) {
            $sqls .= ' and bt.task_uuid !=  "' . $taskuuid . '"' ;
        }
        if (!empty($subsql)) {
            $sqls .= " and bt.user_uuid in $subsql ";
        }
        $sqlParam = [$moduleArr['FS'], $subModuleArr['OBS'], $taskType['BACKUP']];
        $list = dbSelect($sqls, $sqlParam);
        $num += count($list);
    }

    if (in_array('k8s', $module)) {
        // k8s 是没计算具体的节点信息的，只存了集群关联的节点数量，-只计算工作节点（kube_node表的 in_master != 1 ）
        // 比如之前已经创建了2个任务，每个任务使用的不同集群，集群分别有 2 和 3个节点，那么就使用了 5个节点授权数量，如果运行过程中，集群数量有增加或减少，
        // 对应的使用授权数量也会变化，此时新建任务的时候，如果使用的之前的集群，则不消耗授权；修改任务的时候，需要不查询之前的任务的，但是要带上本次使用的集群信息cluster_uuid
        $sqls = 'SELECT KC1.cluster_uuid
                FROM bd_task AS BT
	            INNER JOIN kube_task AS KT ON BT.task_uuid = KT.task_uuid
	            INNER JOIN kube_cluster AS KC1 ON KC1.cluster_uuid = KT.cluster_uuid 
                WHERE BT.module_type = ? AND BT.task_type = ?';
        $sqlparams = [$moduleArr['KUBERNETES'], $taskType['KUBE_BACKUP']];
        if (!empty($taskuuid)) {
            $sqls .= ' and BT.task_uuid != ?';
            $sqlparams = array_merge($sqlparams, [$taskuuid]);
        }
        //用于创建任务处判断授权
        if (!empty($uuids)) {
            $uuids2 = "'" . implode("','", $uuids) . "'";
            $sqls .= " and KC.cluster_uuid not in ($uuids2)";
        }
        if (!empty($subsql)) {
            $sqls .= " and BT.user_uuid in $subsql ";
        }
        $sqls = "SELECT KC.cluster_uuid, COUNT(*) AS nodes
                    FROM kube_node AS KN
                        INNER JOIN kube_cluster AS KC ON KC.cluster_uuid = KN.cluster_uuid
                    WHERE KN.in_master != 1 and KC.cluster_uuid in ($sqls) GROUP BY KC.cluster_uuid";
        $datas = dbSelect($sqls, $sqlparams);
        if (!empty($datas)) {
            // 需要去重 cluster_uuid
            $nums = 0;
            $unique = [];
            foreach ($datas as $item) {
                if (!isset($unique[$item['cluster_uuid']])) {
                    $unique[$item['cluster_uuid']] = $item;
                    $nums += intval($item['nodes']);
                }
            }
            $num += $nums;
        }
    }

    if (in_array('vol_cdp', $module)) {
        // vol_cdp 是根据任务类型判断的
        $sql = 'SELECT COUNT(*) total FROM bd_task BT, cdp_vol_task_takeover_info CVTTI, cdp_vol_task CVL 
            WHERE CVL.task_uuid = BT.task_uuid and BT.task_type = ? and CVTTI.takeover_vm_hypervisor = 100
                and CVL.auto_takeover_flag = ? ';
        if (!empty($subsql)) {
            $sql .= " and BT.user_uuid in $subsql ";
        } else {
            $sql .= " and BT.user_uuid = '" . $user['userUuid'] . "'";
        }
        $data = dbSelect($sql, array($taskType['VOL_CDP_TAKEOVER'], $flag['SET']));
        $num += intval($data[0]['total']);
    }

    if (in_array('private_cloud', $module) || in_array('cloud', $module)) {
        $subModuleType = xphp_get_config('module', 'VM_SUB_MODULE');
        $mode = in_array('private_cloud', $module) ? $subModuleType['PRIVATE_CLOUD'] : $subModuleType['PUBLIC_CLOUD'];
        $num += v2_license_get_virtualization_used_num(intval($mode));
    }

    if (in_array('v2v', $module)) {
        $sql = "select vc_lic_total_num from bd_system";
        $data = dbSelect($sql);
        $total = !empty($data) ? intval($data[0]['vc_lic_total_num']) : 0;
        $num += $total;
    }

    if (in_array('emergency_takeover', $module)) {
        $sql = "select count(*) total from cdp_vol_task_takeover_info where takeover_vm_hypervisor = 108";
        $data = dbSelect($sql);
        $num += intval($data[0]['total']);
    }

    if (in_array('verify', $module)) {
        $vendorList = xphp_get_config('app', 'VENDOR_LIST');
        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] == $vendorList['gmp']) {
            // gmp 验证数量是根据备份数据或已生成验证报告来取并集（客户端去重）
            $sql = "SELECT COUNT(DISTINCT agent_uuid) as total
                        FROM (
                            SELECT agent_uuid FROM industry_report WHERE status != 9
                            UNION
                            SELECT agent_uuid FROM os_backup_timepoint obt
                            WHERE EXISTS (
                                SELECT 1 FROM bd_backup_timepoint bbt
                                WHERE bbt.timepoint_uuid = obt.timepoint_uuid
                                  AND bbt.module_type = 5 
                                  AND bbt.available_flag = 1 
                                  AND bbt.sub_module_type = 1
                            )
                        ) combined_data";
        } else {
            // 取 bd_system 表的 sr_used_num 字段的值就行
            $sql = "select sr_used_num as total from bd_system";
        }        $data = dbSelect($sql);
        $num += intval($data[0]['total']);
    }

    if (in_array('copy_machine', $module)) {
        $sql = "select count(bt.task_uuid) as total from bd_task bt,cdp_vol_task cvt
                                         where bt.task_type = ? and bt.module_type = ? and 
                                         bt.task_uuid = cvt.task_uuid and cvt.standby_agent_uuid != ''";
        if (!empty($subsql)) {
            $sql .= " and bt.user_uuid in $subsql ";
        }
        $data = dbSelect($sql, [$taskType['VOL_CDP_REPLICATION'], $moduleArr['VOL_CDP']]);
        $num += intval($data[0]['total']);
    }

    if (in_array('copy_db', $module)) {
        $where = '';
        if (!empty($subsql)) {
            $where = " and BT.user_uuid in $subsql ";
        }
        $sql = "SELECT COALESCE(SUM(count), 0) AS count FROM (SELECT BT.agent_uuid, 
CASE WHEN BAA.cluster_uuid = '' THEN 1 WHEN BAA.cluster_flag = 1 THEN 
(SELECT COUNT(*) FROM bd_agent_app WHERE cluster_uuid = BAA.cluster_uuid) 
ELSE 0 END AS count FROM bd_task BT INNER JOIN 
bd_agent_app BAA ON BT.agent_uuid = BAA.agent_uuid WHERE BT.module_type = ? AND BT.task_type = ? {$where}) AS subquery";

        $data = dbSelect($sql, [$moduleArr['DB_CDP'], $taskType['CDP_DB_BACKUP']]);
        $num += intval($data[0]['count']);
    }

    // 文件复制的一系列
    if (in_array('copy_fs', $module)) {
        $join = '';
        if (!empty($subsql)) {
            $join = " and stpl.task_uuid in (SELECT DISTINCT stpl2.task_uuid 
                        FROM sync_task_path_list stpl2, bd_task bt
                        WHERE stpl2.source_type = {$subModuleArr['FS']}
                         and bt.task_uuid = stpl2.task_uuid and bt.user_uuid in $subsql )";
        }
        $sql = "SELECT DISTINCT uuid FROM (
                    SELECT stpl.source_uuid AS uuid FROM sync_task_path_list stpl WHERE stpl.source_type = ? {$join}
                   /* UNION
                    SELECT target_uuid AS uuid FROM sync_task_path_list WHERE target_type = ?*/
                ) AS tmp";

        if (!empty($uuids)) {
            $placeholders = "'" . implode("','", $uuids) . "'";
            $sql .= " WHERE uuid NOT IN ($placeholders)";
        }
        $params = [$subModuleArr['FS']/*, $subModuleArr['FS']*/];

        $data = dbSelect($sql, $params);
        $num += count($data);
    }

    if (in_array('copy_nas', $module)) {
        $join = '';
        if (!empty($subsql)) {
            $join = " and stpl.task_uuid in (SELECT DISTINCT stpl2.task_uuid 
                        FROM sync_task_path_list stpl2, bd_task bt
                        WHERE stpl2.source_type = {$subModuleArr['NAS']}
                         and bt.task_uuid = stpl2.task_uuid and bt.user_uuid in $subsql )";
        }
        // nas的要稍微特殊点，需要按照绑定的ip来计算
        $sql = "SELECT DISTINCT ip
                    FROM nas_storage_resource
                    WHERE nas_uuid IN (
                        SELECT stpl.source_uuid AS nas_uuid FROM sync_task_path_list stpl WHERE stpl.source_type = ? {$join}
                       /* UNION
                        SELECT target_uuid AS nas_uuid FROM sync_task_path_list WHERE target_type = ?*/
                    )";

        if (!empty($uuids)) {
            $placeholders = "'" . implode("','", $uuids) . "'";
            $sql .= " and nas_uuid NOT IN ($placeholders)";
        }
        $params = [$subModuleArr['NAS']/*, $subModuleArr['NAS']*/];

        $data = dbSelect($sql, $params);
        $num += count($data);
    }

    if (in_array('copy_hadoop', $module)) {
        $join = '';
        if (!empty($subsql)) {
            $join = " and stpl.task_uuid in (SELECT DISTINCT stpl2.task_uuid 
                        FROM sync_task_path_list stpl2, bd_task bt
                        WHERE stpl2.source_type = {$subModuleArr['HADOOP']}
                         and bt.task_uuid = stpl2.task_uuid and bt.user_uuid in $subsql )";
        }
        $sql = "SELECT DISTINCT uuid FROM (
                    SELECT stpl.source_uuid AS uuid FROM sync_task_path_list stpl WHERE stpl.source_type = ? {$join}
                    /*UNION
                    SELECT target_uuid AS uuid FROM sync_task_path_list WHERE target_type = ?*/
                ) AS tmp";

        if (!empty($uuids)) {
            $placeholders = "'" . implode("','", $uuids) . "'";
            $sql .= " WHERE uuid NOT IN ($placeholders)";
        }
        $params = [$subModuleArr['HADOOP']/*, $subModuleArr['HADOOP']*/];

        $data = dbSelect($sql, $params);
        $num += count($data);
    }

    if (in_array('copy_obs', $module)) {
        $join = '';
        if (!empty($subsql)) {
            $join = " and stpl.task_uuid in (SELECT DISTINCT stpl2.task_uuid 
                        FROM sync_task_path_list stpl2, bd_task bt
                        WHERE stpl2.source_type = {$subModuleArr['OBS']}
                         and bt.task_uuid = stpl2.task_uuid and bt.user_uuid in $subsql )";
        }
        $sql = "SELECT DISTINCT uuid FROM (
                    SELECT stpl.source_uuid AS uuid FROM sync_task_path_list stpl WHERE stpl.source_type = ? {$join}
                    /*UNION
                    SELECT target_uuid AS uuid FROM sync_task_path_list WHERE target_type = ?*/
                ) AS tmp";

        if (!empty($uuids)) {
            $placeholders = "'" . implode("','", $uuids) . "'";
            $sql .= " WHERE uuid NOT IN ($placeholders)";
        }
        $params = [$subModuleArr['OBS']/*, $subModuleArr['OBS']*/];

        $data = dbSelect($sql, $params);
        $num += count($data);
    }
    return $num;
}

/**
 * 文件系列生产容量（文件、nas、hadoop、对象存储）
 * @param array  $module   模块标识
 *                         暂时固定4个
 * @param array  $uuids    资源uuid
 * @param string $taskUuid 任务uuid
 * @param string $subsql   子查询用户信息
 * @return int
 */
function v2_license_get_files_capacity_used(array $module, $uuids = [], $taskUuid = '', $subsql = ''): int
{
    $moduleType = xphp_get_config('module', 'MODULE_TYPE');
    $sql = "select sum(client_capacity) as total from bd_task where task_type = 1 and ";

    $sqlArr = $sqlParam = [];
    $subModuleArr = xphp_get_config('module', 'SUBMODULE_TYPE');
    if (in_array('file', $module)) {
        $sqlArr[] = ' (module_type = ? and sub_module_type = ?)';
        $sqlParam = array_merge($sqlParam, [$moduleType['FS'], $subModuleArr['FS']]);
    }

    if (in_array('nas', $module)) {
        $sqlArr[] = ' (module_type = ? and sub_module_type = ?)';
        $sqlParam = array_merge($sqlParam, [$moduleType['NAS'], $subModuleArr['NAS']]);
    }

    if (in_array('hadoop', $module)) {
        $sqlArr[] = ' (module_type = ? and sub_module_type = ?)';
        $sqlParam = array_merge($sqlParam, [$moduleType['FS'], $subModuleArr['HADOOP']]);
    }

    if (in_array('obs', $module)) {
        $sqlArr[] = ' (module_type = ? and sub_module_type = ?)';
        $sqlParam = array_merge($sqlParam, [$moduleType['FS'], $subModuleArr['OBS']]);
    }

    if (!empty($sqlArr)) {
        $sql .= '(' . implode(' or ', $sqlArr) . ')';
        if (!empty($subsql)) {
            $sql .= " and user_uuid in $subsql ";
        }
        $data = dbSelect($sql, $sqlParam);
        return intval($data[0]['total']) ?? 0;
    }

    return 0;
}

/**
 * 获取已经使用的(根据组合类型)
 * @param string $module      模块标识
 * @param int    $authType    授权类型1个数
 * @param int    $licenseType 0非主机保护模块 1独立授权
 *                            2整机数量(文件、数据库、操作系统)
 *                            3整机数量(数据库、操作系统)
 *                            4文件系列生产容量
 * @param array  $uuids       用于部分模块创建任务时计算授权个数
 * @param string $taskuuid    任务uuid,用于修改任务处判断授权
 * @param string $subsql      用户uuid子查询
 * @return int
 */
function v2_license_get_used_storages(
    string $module,
    $authType = 1,
    $licenseType = 0,
    $uuids = [],
    $taskuuid = '',
    $subsql = ''
) {
    // 根据不同的值查询不同的
    switch ($module) {
        case 'vm':
            // 虚拟机的$licenseType授权类型有 1 host个数 2cpu个数 3vm个数
            $num = v2_license_get_vm_used($licenseType, $subsql);
            break;
        case 'cdp':
            // cdp的$authType类型有  1个数 2容量
            $num = v2_license_get_cdp_used($authType, $subsql);
            break;
        case 'exchange':    // exchange 按照个数
        case 'exchange_online':    // exchange_online 按照个数
        case 'k8s':     // k8s 按照个数
        case 'private_cloud':   // private_cloud 按照个数
        case 'cloud':   // cloud 按照个数
        case 'vol_cdp': // vol_cdp 实时接管按照个数
        case 'emergency_takeover':    // emergency_takeover 应急接管按照个数
        case 'v2v': // v2v 跨平台恢复按照个数
        case 'verify': // 数据验证按照个数
        case 'copy_machine': // 整机复制按照个数
        case 'copy_db': // 数据库复制按照个数
        case 'copy_fs': // 本地文件复制按照个数
        case 'copy_nas': // nas复制按照个数
        case 'copy_hadoop': // hadoop复制按照个数
        case 'copy_obs': // obs对象存储复制按照个数
        case 'oracle_hana': // hala数据库单独计算个数
            $num = v2_license_get_num_used($module, $uuids, $taskuuid, $subsql);
            break;
        default:
            // 剩下的就是 主机保护类型的，暂时有6个模块， file oracle os nas hadoop obs
            // 1 独立授权
            // 2  整机数量（文件、数据库、操作系统）
            // 3  整机数量（数据库、操作系统）
            // 4  文件系列生产容量（文件、nas、hadoop、对象存储）
            $methodArr = [
                1 => [
                    'function' => 'v2_license_get_num_used', 'param' => $module,
                    'uuids' => $uuids, 'task_uuid' => $taskuuid
                ],
                2 => [
                    'function' => 'v2_license_get_num_used', 'param' => ['file', 'oracle', 'os'],
                    'uuids' => $uuids, 'task_uuid' => $taskuuid
                ],
                3 => [
                    'function' => 'v2_license_get_num_used', 'param' => ['oracle', 'os'],
                    'uuids' => $uuids, 'task_uuid' => $taskuuid
                ],
                4 => [
                    'function' => 'v2_license_get_files_capacity_used', 'param' => ['file', 'nas', 'hadoop', 'obs'],
                    'uuids' => $uuids, 'task_uuid' => $taskuuid
                ],
            ];
            if (!empty($methodArr[$licenseType])) {
                $method = $methodArr[$licenseType];
                if (in_array($module, ['file', 'nas', 'hadoop', 'obs'])) {
                    $num = $method['function']($method['param'], $method['uuids'], $method['task_uuid'], $subsql);
                } else {
                    $num = $method['function']($method['param'], $method['uuids'], $method['task_uuid'], $subsql);
                }
            }
    }
    return $num ?? 0;
}

/** 获取授权信息
 * @param string $module     模块标识
 * @param array  $uuids      用于部分模块创建任务时计算授权个数
 * @param string $taskuuid   任务uuid,用于修改任务处判断授权
 * @param string $tenantuuid 租户uuid
 * @return array
 */
function v2_license_get_auth($module = '', $uuids = [], $taskuuid = '', $tenantuuid = ''): array
{

    $data = v2_license_info();
    if (empty($data)) {
        // 未查询到授权信息
        if (!empty($module)) {
            return [
                'auth_type' => 2,
                'license_type' => 0,
                'total' => 0,
                'used' => 0,
                'license_flag' => false,
            ];
        } else {
            return [
                'capacity' => [
                    'auth_type' => 2,
                    'license_type' => 0,
                    'total' => 0,
                    'used' => 0,
                ],
                'license_flag' => false,
            ];
        }
    }

    // 过期时间
    $expireDate = v2_license_get_expire_days();
    $expireDays = $expireDate['expire_days'] >= 0;

    $subsql = '';
    if (!empty($tenantuuid)) {
        // 获取租户下的所有用户uuid
        $subsql = "(select bu.user_uuid from bd_user bu, bd_tenant bt
                    where (bu.create_user_uuid = bt.admin_uuid or bu.user_uuid = bt.admin_uuid)
                      and bt.tenant_uuid = '{$tenantuuid}')";
    }
    $auth = $data[0];
    if ($auth['root_type'] == 1) {
        // 存储容量
        $return['capacity'] = [
            'auth_type' => 2,
            'license_type' => 0,
            'total' => $auth['mixed_capacity_max'],
            'used' => v2_license_get_capacity_used(0, $subsql),
        ];
    } elseif ($auth['root_type'] == 2) {
        // 定时和实时容量
        $return['storage'] = [
            'auth_type' => 2,
            'license_type' => 0,
            'total' => $auth['storage_count'],
            'used' => v2_license_get_capacity_used(1, $subsql),
        ];
        $return['vol_capacity'] = [
            'auth_type' => 2,
            'license_type' => 0,
            'total' => $auth['cdp_capacity_max'],
            'used' => v2_license_get_capacity_used(2, $subsql),
        ];
    } else {
        $return = [];
        // 非整体容量授权
        // 虚拟化授权方式 license_type
        $vm = [
            1 => 'vm_max_num', // host 个数
            2 => 'cpu_count', // cpu个数
            // 3 => 'storage_count', // 604 之前才存在
            4 => 'vm_max_num', // vm个数
        ];
        $return['vm'] = [
            'auth_type' => 1, // 授权方式为个数
            'license_type' => $auth['license_type'], // 授权类型
            'total' => $auth[$vm[$auth['license_type']]] ?? 0, // 总数
            'used' => 0, // 已使用 待查询
        ];
        // 主机保护 client_type
        /*  文件： 类型是个数和容量，授权类型（1、独立授权；2、整机数量；4、文件系列生产容量），授权数量和已使用数量
            数据库：类型是个数和容量，授权类型（1、独立授权；2、整机数量（含文件）；3整机数量），授权数量和已使用数量
            操作系统：类型是个数和容量，授权类型（1、独立授权；2、整机数量（含文件）；3整机数量）），授权数量和已使用数量
            nas：类型是个数和容量，授权类型（1、独立授权；4、文件系列生产容量），授权数量和已使用数量
            hadoop：类型是个数和容量，授权类型（1、独立授权；4、文件系列生产容量），授权数量和已使用数量
            对象存储：类型是个数和容量，授权类型（1、独立授权；4、文件系列生产容量），授权数量和已使用数量
        */
        if ($auth['client_type'] == 2) {
            // 2整机数量（文件、数据库、操作系统）、nas数量、hadoop数量、对象存储数量
            //文件
            $return['file'] = ['auth_type' => 1, 'license_type' => 2, 'total' => $auth['client_max'], 'used' => 0];
            //数据库
            $return['oracle'] = ['auth_type' => 1, 'license_type' => 2, 'total' => $auth['client_max'], 'used' => 0];
            $return['oracle_hana'] =
                ['auth_type' => 1, 'license_type' => 2, 'total' => $auth['sap_hana_max_num'], 'used' => 0];
            // 操作系统
            $return['os'] = ['auth_type' => 1, 'license_type' => 2, 'total' => $auth['client_max'], 'used' => 0];
            // nas
            $return['nas'] = ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['nas_max_num'], 'used' => 0];
            // hadoop
            $return['hadoop'] =
                ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['hadoop_cluster_max_num'], 'used' => 0];
            // obs
            $return['obs'] = ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['obs_max_num'], 'used' => 0];
        } elseif ($auth['client_type'] == 3) {
            // 4文件系列生产容量（文件、nas、hadoop、对象存储）、数据库数量、操作系统数量
            $return['file'] =
                ['auth_type' => 2, 'license_type' => 4, 'total' => $auth['client_capacity_max'], 'used' => 0];
            $return['nas'] =
                ['auth_type' => 2, 'license_type' => 4, 'total' => $auth['client_capacity_max'], 'used' => 0];
            $return['hadoop'] =
                ['auth_type' => 2, 'license_type' => 4, 'total' => $auth['client_capacity_max'], 'used' => 0];
            $return['obs'] =
                ['auth_type' => 2, 'license_type' => 4, 'total' => $auth['client_capacity_max'], 'used' => 0];
            $return['oracle'] =
                ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['oracle_max_num'], 'used' => 0];
            $return['oracle_hana'] =
                ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['sap_hana_max_num'], 'used' => 0];
            $return['os'] = ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['os_max_num'], 'used' => 0];
        } elseif ($auth['client_type'] == 4) {
            // 4文件系列生产容量（文件、nas、hadoop、对象存储）、整机数量（数据库、操作系统）
            $return['file'] =
                ['auth_type' => 2, 'license_type' => 4, 'total' => $auth['client_capacity_max'], 'used' => 0];
            $return['nas'] =
                ['auth_type' => 2, 'license_type' => 4, 'total' => $auth['client_capacity_max'], 'used' => 0];
            $return['hadoop'] =
                ['auth_type' => 2, 'license_type' => 4, 'total' => $auth['client_capacity_max'], 'used' => 0];
            $return['obs'] =
                ['auth_type' => 2, 'license_type' => 4, 'total' => $auth['client_capacity_max'], 'used' => 0];

            $return['oracle'] = ['auth_type' => 1, 'license_type' => 3, 'total' => $auth['client_max'], 'used' => 0];
            $return['oracle_hana'] =
                ['auth_type' => 1, 'license_type' => 3, 'total' => $auth['sap_hana_max_num'], 'used' => 0];
            $return['os'] = ['auth_type' => 1, 'license_type' => 3, 'total' => $auth['client_max'], 'used' => 0];
        } else {
            // 独立授权
            $return['file'] = ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['file_max_num'], 'used' => 0];
            $return['oracle'] =
                ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['oracle_max_num'], 'used' => 0];
            $return['oracle_hana'] =
                ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['sap_hana_max_num'], 'used' => 0];
            $return['os'] = ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['os_max_num'], 'used' => 0];
            $return['nas'] = ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['nas_max_num'], 'used' => 0];
            $return['hadoop'] =
                ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['hadoop_cluster_max_num'], 'used' => 0];
            $return['obs'] = ['auth_type' => 1, 'license_type' => 1, 'total' => $auth['obs_max_num'], 'used' => 0];
        }

        // 实时保护 cdp_license_type
        // 类型是个数和容量，授权数量和已使用数量
        $cdp = [
            1 => 'cdp_max', // 主机任务数
            2 => 'cdp_capacity_max',  // cdp容量
        ];
        $return['cdp'] = [
            'auth_type' => $auth['cdp_license_type'],
            'license_type' => 0,
            'total' => $auth[$cdp[$auth['cdp_license_type']]] ?? 0,
            'used' => 0,
        ];
        // 类型是个数，返回授权个数和已经使用的个数
        $return['exchange'] = [
            'auth_type' => 1,
            'license_type' => 0,
            'total' => $auth['exchange_user_max_num'],
            'used' => 0,
        ];
        $return['exchange_online'] = [
            'auth_type' => 1,
            'license_type' => 0,
            'total' => $auth['exchange_online_user_max_num'],
            'used' => 0,
        ];
        // 类型是个数，返回授权个数和已经使用的个数
        $return['k8s'] = [
            'auth_type' => 1,
            'license_type' => 0,
            'total' => $auth['k8s_max_num'],
            'used' => 0,
        ];
        // 类型是个数，返回授权个数和已经使用的个数
        $return['private_cloud'] = [
            'auth_type' => 1,
            'license_type' => 0,
            'total' => $auth['private_cloud_instance_max_num'],
            'used' => 0,
        ];
        // 类型是个数，返回授权个数和已经使用的个数
        $return['cloud'] = [
            'auth_type' => 1,
            'license_type' => 0,
            'total' => $auth['public_cloud_instance_max_num'],
            'used' => 0,
        ];
    }

    // cdp实时
    $return['vol_cdp'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['cdp_takeover_max_num'],
        'used' => 0,
    ];
    // 应急接管-内嵌
    $return['emergency_takeover'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['internal_cdp_takeover_max_num'],
        'used' => 0,
    ];
    // v2v
    $return['v2v'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['desktop_max'],
        'used' => 0,
    ];
    // 验证数量
    $return['verify'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['sr_max_num'],
        'used' => 0,
    ];
    // 整机复制
    $return['copy_machine'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['client_replication_max_num'],
        'used' => 0,
    ];
    // 数据库复制
    $return['copy_db'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['db_replication_max_num'],
        'used' => 0,
    ];
    // 本地文件复制
    $return['copy_fs'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['file_replication_max_num'],
        'used' => 0,
    ];
    // NAS复制
    $return['copy_nas'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['nas_replication_max_num'],
        'used' => 0,
    ];
    // Hadoop复制
    $return['copy_hadoop'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['hadoop_replication_max_num'],
        'used' => 0,
    ];
    // 对象存储复制
    $return['copy_obs'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => $auth['obs_replication_max_num'],
        'used' => 0,
    ];
    // 永思数据库实时
    $return['third_db_cdp'] = [
        'auth_type' => 1,
        'license_type' => 0,
        'total' => 0,
        'used' => 0,
        'valid' => false,
    ];

    if (!empty($module)) {
        // 获取单独某一个模块的配置信息
        if (empty($return[$module])) {
            // 不在列表里 那么可能就是1和2
            if ($auth['root_type'] == 1) {
                $return['capacity']['license_flag'] = $expireDays;
                return $return['capacity'];
            }
            // 需要判断下是定时还是实时模块
            if ($module == 'cdp') {
                // 实时模块
                $return['vol_capacity']['license_flag'] = $expireDays;
                return $return['vol_capacity'];
            }
            $return['storage']['license_flag'] = $expireDays;
            return $return['storage'];
        }
        $returns = $return[$module];
        if ($module == 'third_db_cdp') {
            // 是第三方的，那么单独计算
            $thirdInfo = v2_get_db_cdp_num();
            $returns['used'] = $thirdInfo['used'];
            $returns['total'] = $thirdInfo['total'];
            $returns['valid'] = $thirdInfo['valid'];
        } else {
            // 计算已经使用了的
            $returns['used'] = v2_license_get_used_storages(
                $module,
                $returns['auth_type'],
                $returns['license_type'],
                $uuids,
                $taskuuid,
                $subsql
            );
        }

        $returns['used_des'] = $returns['used'];
        $returns['total_des'] = $returns['total'];
        $returns['avail_des'] = $returns['total'] - $returns['used'];
        if ($returns['auth_type'] == 2) {
            $returns['used_des'] = v2_calsize($returns['used']);
            $returns['total_des'] = v2_calsize($returns['total']);
            $returns['avail_des'] = v2_calsize($returns['avail_des']);
        }
        $returns['license_flag'] = $expireDays;
        return $returns;
    }
    // 循环的获取 used 为 0的真实的值
    // 预先定义一个已经获取了的
    foreach ($return as $key => &$value) {
        if ($value['used'] != 0) {
            $value['used_des'] = $value['used'];
            $value['total_des'] = $value['total'];
            $value['avail_des'] = $value['total'] - $value['used'];
            if ($value['auth_type'] == 2) {
                $value['used_des'] = v2_calsize($value['used'], true);
                $value['total_des'] = v2_calsize($value['total'], true);
                $value['avail_des'] = v2_calsize($value['avail_des'], true);
            }
            continue;
        }
        if ($key == 'third_db_cdp') {
            // 是第三方的，那么单独计算
            $thirdInfo = v2_get_db_cdp_num();
            $value['used'] = $thirdInfo['used'];
            $value['total'] = $thirdInfo['total'];
            $value['valid'] = $thirdInfo['valid'];
            $value['used_des'] = $value['used'];
            $value['total_des'] = $value['total'];
            $value['avail_des'] = $value['total'] - $value['used'];
            continue;
        }
        if ($key == 'vm' || in_array($value['license_type'], [0, 1])) {
            // 虚拟化的 license_type不一样；license_type为0的表示不是主机保护里面的块块
            $value['used'] = v2_license_get_used_storages(
                $key,
                $value['auth_type'],
                $value['license_type'],
                $uuids,
                $taskuuid,
                $subsql
            );
            $value['used_des'] = $value['used'];
            $value['total_des'] = $value['total'];
            $value['avail_des'] = $value['total'] - $value['used'];
            if ($value['auth_type'] == 2) {
                $value['used_des'] = v2_calsize($value['used'], true);
                $value['total_des'] = v2_calsize($value['total'], true);
                $value['avail_des'] = v2_calsize($value['avail_des'], true);
            }
            continue;
        }
        $used = v2_license_get_used_storages(
            $key,
            $value['auth_type'],
            $value['license_type'],
            $uuids,
            $taskuuid,
            $subsql
        );
        $value['used'] = $used;
        $value['used_des'] = $value['used'];
        $value['total_des'] = $value['total'];
        $value['avail_des'] = $value['total'] - $value['used'];
        if ($value['auth_type'] == 2) {
            $value['used_des'] = v2_calsize($value['used'], true);
            $value['total_des'] = v2_calsize($value['total'], true);
            $value['avail_des'] = v2_calsize($value['avail_des'], true);
        }
    }
    $return['license_flag'] = $expireDays;
    return $return;
}

/**
 * 获取所有的功能授权信息
 * in_array 就表示存在
 * @param string $type 类型 p（页面）默认或f（功能）
 * @param int    $mode 返回方式
 * @return array
 */
function v2_license_get_func(string $type = 'f', int $mode = 0): array
{

    $data = v2_license_info('extension');
    $return = [];
    if (!empty($data)) {
        $data = json_decode(v2_decrypt($data[0]['extension']), true);
        if ($type == 'f') {
            foreach ($data['f'] as $key => $val) {
                if ($mode == 1) {
                    if ($val == 1) {
                        $return[] = $key;
                    }
                } else {
                    $return[$key] = $val == 1;
                }
            }
        } elseif ($type == 'v2v') {
            $return = $data['crossHypervisorRecovery'];
        } else {
            $return = $data['p'];
        }
    }
    return $return;
}

/**
 * 获取所有的虚拟化类型功能授权信息
 * @param int $type 类型 1返回所有支持备份、恢复的虚拟化类型,2返回可以添加的虚拟化类型
 * @return array
 */
function v2_license_get_v(int $type = 1): array
{

    $data = v2_license_info('extension');
    $return = [];
    if (!empty($data)) {
        $data = json_decode(v2_decrypt($data[0]['extension']), true);
        foreach ($data['crossHypervisorRecovery'] as $key => $item) {
            $return[] = intval($key);
            if ($type == 2) {
                // 还要合并所有的子数组
                $return = array_merge($return, $item);
            }
        }
    }
    // 去重并重新给健排序
    return array_values(array_unique($return));
}

/**
 * 根据传递的虚拟化类型，判断可以跨平台、瞬时恢复和迁移的虚拟化类型（内嵌是 1000，整机是1001）
 * @param array $array 源端虚拟化类型数组
 * @return array
 */
function v2_license_get_v2v(array $array = []): array
{

    $data = v2_license_info('extension');
    $return = [];
    if (!empty($data)) {
        $data = json_decode(v2_decrypt($data[0]['extension']), true);
        foreach ($data['crossHypervisorRecovery'] as $key => $item) {
            if (in_array(108, $array)) {
                // 内嵌可以到授权的所有的虚拟化类型
                $return = array_merge($return, $item);
            } elseif (in_array($key, $array)) {
                $return[$key] = $item;
            }
        }

        if (in_array(108, $array)) {
            // 内嵌可以到授权的所有的虚拟化类型
            return array_unique($return);
        }
        // 根据 return 中元素数量决定如何处理
        $count = count($return);
        if ($count === 0) {
            return [];
        } elseif ($count === 1) {
            return array_values($return)[0]; // 返回唯一一组值
        } else {
            // 安全调用 array_intersect
            $result = call_user_func_array('array_intersect', $return);
            return array_values($result); // 重排索引
        }
    }
    // 去重并重新给健排序
    return [];
}

/**
 * 根据传递的类型，计算当前的模式是否需要消耗授权 （整机是1001）
 * @param array $array   源端虚拟化类型数组
 * @param int   $goal    目的端虚拟化类型（跨平台恢复或迁移目的地）
 * @param int   $jobType 任务类型，默认1跨平台 2瞬时恢复 3迁移
 * @param int   $instant 瞬时恢复端虚拟化类型
 * @return bool
 */
function v2_license_v2v(array $array = [], int $goal = 0, int $jobType = 1, int $instant = 0): bool
{
    $need = false; // 默认不需要消耗授权
    $emd = xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_EMD'];
    if ($jobType == 1) {
        // 跨平台2种情况需要消耗，1 整机到虚拟化；2 虚拟化到异构或整机
        // 因为不能跨到内嵌，整机也算是一种虚拟化，所以只需判断，是否 in 即可
        $need = !in_array($goal, $array);
    } elseif ($jobType == 2) {
        // 瞬时恢复2种情况需要消耗，1 整机到虚拟化平台（不包含内嵌）；2 虚拟化到异构(不包含内嵌)
        if (!in_array($instant, $array) && $instant != $emd) {
            $need = true;
        }
    } else {
        // 迁移， 1 虚拟化瞬时恢复到内嵌或原平台迁移到整机；2 整机瞬时恢复到内嵌迁移到虚拟化平台
        $machine = xphp_get_config('vm', 'MACHINE_VM_TYPE');
        if (
            (((in_array($instant, $array) || $instant == $emd) && !in_array($machine, $array) ) && $goal == $machine) ||
            (in_array($machine, $array) && $instant == $emd && $goal != $machine)
        ) {
            $need = true;
        }
    }

    if ($need) {
        // 计算已经使用了的跨平台授权，判读是否还有剩余
        // 获取系统的授权
        $license = v2_license_get_auth('v2v');
        if ($license['total'] == -1) {
            // -1 表示无限制
            return true;
        }

        if ($license['used'] + count($array) > $license['total']) {
            // 授权数不足
            return false;
        }
    }
    return true;
}

/**
 * 获取永思数据库的授权使用数量
 * @return array
 */
function v2_get_db_cdp_num(): array
{
    $return = [
        'used' => 0,
        'total' => 0,
        'valid' => false,
    ];

    $data = v2_license_info('extension');
    if (empty($data)) {
        return $return;
    }
    //授权模块以及自定义版本信息
    if (json_decode(v2_decrypt($data[0]['extension']), true) == null) {
        return $return;
    }
    $extension = json_decode(v2_decrypt($data[0]['extension']), true);
    if ($extension['dbcdplic']) {
        $hostip = xphp_get_config('db', 'DB_CDP_LICENSE_IP');
        $rpc = new app\v2\db\v0\logic\DbRpc();
        $data = array();
        //是否获取cdp授权信息
        $result = $rpc->getLicenseCenterAuthorInfo($hostip, $data);
        if (empty($result['result'])) {
            return $return;
        }

        $dbCDPHandler = new app\v2\db\v0\logic\DbCdp();
        $result = $dbCDPHandler->getLicenseCenterAuthorInfo();
        if (empty($result)) {
            return $return;
        }
        $totalArr = $result[0]['units'];
        $usedArr = $result[0]['alloced'];
        $dbcdpsyscode = xphp_get_config('db', 'DB_CDP_SYSCODE');
        $totalNum = 0;
        $usedNum = 0;
        $endTime = false;
        foreach ($totalArr as $total) {
            if (
                in_array(
                    $total['syscode'],
                    [$dbcdpsyscode['producthost'], $dbcdpsyscode['standbyhost'], $dbcdpsyscode['takeover']]
                )
            ) {
                $totalNum = max(intval($total['authors']), $totalNum);
            }
            /*if ($total['syscode'] == $dbcdpsyscode['fileproduct']) {
                $info['total']['fsproducthost'] = $total['authors'];
            }
            if ($total['syscode'] == $dbcdpsyscode['filestandby']) {
                $info['total']['fsstandbyhost'] = $total['authors'];
            }*/
            $endTime = $total['etime'];
        }
        foreach ($usedArr as $used) {
            if (
                in_array(
                    $used['syscode'],
                    [$dbcdpsyscode['producthost'], $dbcdpsyscode['standbyhost'], $dbcdpsyscode['takeover']]
                )
            ) {
                $usedNum += 1;
            }
            /*if ($used['syscode'] == $dbcdpsyscode['fileproduct']) {
                $info['used']['fsproducthost'] = $info['used']['fsproducthost'] + 1;
            }
            if ($used['syscode'] == $dbcdpsyscode['filestandby']) {
                $info['used']['fsstandbyhost'] = $info['used']['fsstandbyhost'] + 1;
            }*/
        }

        $return['total'] = $totalNum;
        $return['used'] = $usedNum;
        $return['valid'] = !empty($endTime);
        return $return;
    }
    return $return;
}

/**
 * 统一获取license信息
 * @param string $field 查询字段，不带表示查询所有
 * @return array
 */
function v2_license_info(string $field = ''): array
{
    if (empty($field)) {
        $field = '*';
    }
    $data = dbSelect("select {$field} from bd_license");
    return !empty($data) ? $data : [];
}

/**
 * 获取授权是否有效
 */
function v2_license_get_expire_days(): array
{
    $return = [
        'expire_days' => -1, // 距离过期天数
        'expire_time' => xphp_get_config('app', 'NULLSPACE'), // 过期日期
    ];

    // 先判断授权是否正常
    $sql = "select authorized_flag from bd_system ";
    $authInfo = dbSelect($sql);
    if (empty($authInfo) || $authInfo[0]['authorized_flag'] != 1) {
        // 异常
        return $return;
    }

    $info = v2_license_info('register_time,days');
    if (empty($info)) {
        // 未授权
        return $return;
    }

    if ($info[0]['days'] == -1) {
        // 永久有效
        $return['expire_days'] = 99999;
        return $return;
    }

    $dayInterval = ceil((time() - strtotime($info[0]['register_time'])) / 3600 / 24);
    $expireDays = ($info[0]['days'] - $dayInterval) < 0 ? -1 : ($info[0]['days'] - $dayInterval);
    $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';

    //到期天数等于注册时间+授权天数转换成时间
    $endTimeStamp = strtotime($info[0]['register_time']) + ($info[0]['days'] * 24 * 3600);
    $expireTime = date($dateformat, $endTimeStamp);
    $return['expire_time'] = $expireTime;
    // 到期天数精确到秒
    if ($endTimeStamp <= time()) {
        // 如果到期时间小于等于当前时间，那么就表示到期
        $expireDays = -1;
    }
    $return['expire_days'] = $expireDays;

    return $return;
}
