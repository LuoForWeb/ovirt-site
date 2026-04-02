<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Agent;
use app\v1\common\logic\Base;
use app\v1\opcode\DbProtectOpcode;
use app\v1\opcode\NodeOpcode;
use PhpOffice\PhpSpreadsheet\IOFactory;
use app\v1\db\v0\logic\Dbtiming;
use app\v1\db\v0\logic\DbCdp;
use app\v1\db\v0\logic\DbRpc;

/**
 * note          客户端管理处理类 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/29 15:21
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Client extends Base
{
    private $nodeOpcode;
    private $dbProtectOpcode;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->nodeOpcode = new NodeOpcode();
        $this->dbProtectOpcode = new DbProtectOpcode();
    }

    /**
     * 得到客户端管理客户端列表信息
     * @param array $params 数据
     * @return array
     */
    public function getClientList(array $params = []): array
    {
        $loginUser = xphp_get_user_info();
        // 查询客户端数据
        $clientInfo = $this->queryClientList($params);
        //应用配置
        $appList = $this->getApplicationList();
        // 查询任务信息
        $taskMap = $this->getAgentTaskMap($clientInfo['data']);

        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        $onlineStatusDes = xphp_get_desc('Pf', 'ONLINEDES');
        $deployStatusDes = xphp_get_desc('Pf', 'AGENT_DEPLOY_STATUS');
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $netModelDes = xphp_get_desc('client', 'AGENT_NET_MODEL_DES', 'resources');
        $clientPackages = $this->getClientPackages();
        $allAgentPackageTypeMap = xphp_get_desc('Agent', 'AGENT_PACKAGE_TYPE_MAP');
        // 操作
        $op = [1, 2, 3, 4, 5, 6];  //1 应用配置  2.修改授权 3 刷新客户端 4详情 5日志下载 6代理配置
        if (!in_array('p_agent_manager_application_config', $loginUser['permissionArr'])) {
            // 没有应用配置权限
            $op = array_values(array_diff($op, [1, 3]));
        }
        if (!in_array('p_agent_log_download', $loginUser['permissionArr'])) {
            // 没有日志下载权限
            $op = array_values(array_diff($op, [5]));
        }
        if (!in_array('p_agent_manager_agent_config', $loginUser['permissionArr'])) {
            // 代理配置权限
            $op = array_values(array_diff($op, [6]));
        }

        $rows = [];
        foreach ($clientInfo['data'] as $row) {
            $subOp = $op;
            $singleNetModelDes = $netModelDes[$row['net_model']];
            if ($allAgentType['APPLIANCE'] == $row['agent_type']) {
                // 传输代理没有应用配置
                $subOp = array_values(array_diff($op, [1]));
                $singleNetModelDes = xphp_get_lang('UI_CLIENT_SERVER_TO_PROXY');
            } else {
                // 非传输代理没有代理配置
                $subOp = array_values(array_diff($op, [6]));
            }
            // 应用配置描述
            $appDes = '';
            $clusterInfo = null;
            $singleAppList = [];
            foreach ($appList as $app) {
                if ($row['agent_uuid'] != $app['agent_uuid']) {
                    continue;
                }
                if (($params['app_type'] ?? 0) && $params['app_type'] != $app['app_type']) {
                    // 如果过滤了应用类别，其他类别应用不处理
                    continue;
                }
                $singleAppList[] = $app;
                $appDes .= $app['app_name']
                    . '(' . xphp_get_config('db', 'DB_TYPE_DES')[(int)$app['app_type']] . ')' . PHP_EOL;
                // 构建集群信息
                if ($app['cluster_flag'] && $app['cluster_uuid'] && !$clusterInfo) {
                    $clusterInfo = [
                        'cluster_name' => $app['cluster_name'],
                        'cluster_uuid' => $app['cluster_uuid'],
                        'cluster_service_ip' => $app['cluster_service_ip'],
                        'app_name' => $app['app_name'],
                        'app_uuid' => $app['app_uuid'],
                        'app_type' => $app['app_type'],
                        'agent_uuid' => $app['agent_uuid'],
                        'app_detail' => $app['app_detail'],
                        'app_auth_type' => $app['app_auth_type'],
                    ];
                }
            }

            // 拥有者
            $owner = $this->getClientUuidName($row['agent_uuid'], $row['user_name']);
            $ownerList = explode(',', $owner);
            if ($loginUser['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                foreach ($ownerList as $index => $name) {
                    if ($name == 'admin') {
                        $ownerList[$index] = 'sysadmin';
                    }
                }
                $owner = implode(',', $ownerList);
            }
            // 创建者
            $creator = $row['user_name'];
            if ('admin' == $creator && $loginUser['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                $creator = 'sysadmin';
            }
            //租户内判断创建者等不等于当前用户,等于当前用户才能操作
            $opFlag = true;
            if ($loginUser['tenantuuid'] && $row['user_uuid'] != $loginUser['userUuid']) {
                $opFlag = false;
                // bug#20208: 分配给租户的客户端可以应用配置、刷新、查看详情和下载日志
                // $subOp = [4];//创建者不是当前用户，只能查看详情
            }
            $detail = json_decode($row['detail'], true);
            $hardwareInfo = [];
            try {
                $hardwareInfo = json_decode($row['hardware_info'], true);
            } catch (\Exception $e) {
                $hardwareInfo = [];
            }

            // 升级判断
            $upgradeFlag = true;
            $upgradeVersion = '';
            if ($row['agent_type'] == $allAgentType['NORMAL']) {  // 客户端才有升级标识
                $packagePath = '';
                if (is_array($clientPackages[$allAgentPackageTypeMap[$row['agent_package_type']]])) {
                    $packagePath = $clientPackages[$allAgentPackageTypeMap[$row['agent_package_type']]]['fileName'];
                }
                if ($packagePath) {
                    if (preg_match('/.*backup-agent-(.*)-AGENT.*/', $packagePath, $matches)) {
                        $upgradeVersion = $matches[1];
                    } elseif (preg_match('/.*windows\.(.*)\.exe/', $packagePath, $matches)) {
                        $upgradeVersion = $matches[1];
                    }
                } else {
                    $upgradeFlag = false;
                }
                if (!$upgradeVersion) {
                    $upgradeFlag = false;
                    $upgradeVersion = '';
                } elseif ($upgradeVersion <= $row['plugin_version']) {
                    $upgradeFlag = false;
                    $upgradeVersion = '';
                }
            } else {
                $upgradeFlag = false;
            }
            //GMP需要新增几个字段
            if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] == xphp_get_config('app','VENDOR_LIST')['gmp']) {
                $backupTime = $row['backup_time'];
                $backupFlag = !empty($row['backup_time']);
                $verifyTime = $row['verify_time'];
                $verifyFlag = !empty($row['verify_time']);
            }

            $rows[] = [
                'agent_uuid' => $row['agent_uuid'],
                'agent_ip' => $row['ip'],
                'agent_port' => (int) $row['port'],
                'hostname' => $row['hostname'] ?: $nullSpace,
                'alias' => $row['agent_name'] ?: $nullSpace,
                'os_version' => $row['os_version'] ?: $nullSpace,
                'app_des' => $appDes ?: $nullSpace,
                'register_time' => $this->parseDate($row['register_time']),
                'online_status' => ($row['online_flag'] == 1),
                'online_status_des' => $onlineStatusDes[$row['online_flag']],
                'deploy_status' => (int) $row['plugin_deploy_status'],
                'deploy_status_des' => $deployStatusDes[$row['plugin_deploy_status']],
                'owner' => $owner,
                'app_list' => $singleAppList,
                'cluster_info' => $clusterInfo,
                'group_name' => $row['group_name'],
                'group_uuid' => $row['group_uuid'],
                'agent_version' => $row['plugin_version'] ?: $nullSpace,
                'net_model' => (int) $row['net_model'],
                'net_model_des' => $singleNetModelDes,
                'creator' => $creator,
                'op' => $subOp,
                'agent_type' => (int) $row['agent_type'],
                'agent_type_des' => $row['agent_type_des'],
                'os_type' => $row['os_type'],
                'network_list' => $this->getClientNetworkList($detail),
                'total' => count($clientInfo['data']),
                'domain_config' => $row['domain_config'],
                'agent_pool_name' => $this->getAgentPoolName($row['agent_uuid']),
                'add_ip' => $row['add_ip'],
                'auto_change_network_flag' => v1_parse_flag_to_bool($row['auto_change_network_flag']),
                'applied_vcenters' => $row['applied_vcenters']
                    ? json_decode($row['applied_vcenters'], true) : [],
                'op_flag' => $opFlag,//当前用户是否可以操作该资源
                'task_count' => $taskMap[$row['agent_uuid']]['task_count'],
                'all_task_name' => $taskMap[$row['agent_uuid']]['all_task_name'],
                'upgrade_flag' => $upgradeFlag,
                'upgrade_version' => $upgradeVersion,
                'time_zone_offset' => $row['time_zone_offset'],
                'driver_status' => $row['driver_status'],
                'hardware_info' => $hardwareInfo,
                'backup_time' => $backupTime ?? '',
                'backup_flag' => $backupFlag ?? false,
                'verify_time' => $verifyTime ?? '',
                'verify_flag' => $verifyFlag ?? false
            ];
        }

        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => $clientInfo['count']
        ]);
    }

    /**
     * 查询客户端列表
     * @param array $params 参数
     * @return array
     */
    private function queryClientList(array $params): array
    {
        $subSql = "SELECT agent_uuid, GROUP_CONCAT(app_name) AS app_name FROM bd_agent_app GROUP BY agent_uuid ";

        $agentTypeDes = xphp_get_desc('client', 'AGENT_TYPE_DES', 'resources');
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $appliance = $allAgentType['APPLIANCE'];
        $applianceDes = $agentTypeDes[$allAgentType['APPLIANCE']];
        $clientDes = $agentTypeDes[$allAgentType['NORMAL']];
        $agentTypeDesField = "IF (ba.agent_type = $appliance, '$applianceDes', '$clientDes')";

        $fields = '';
        //如果是GMP，则需要查询更多字段  需要重写前半段sql
        if (xphp_get_config('app', 'SYSTEM_INFO')['vendor'] == xphp_get_config('app','VENDOR_LIST')['gmp'] ) {
            $fields = ", (select bbt.timepoint from bd_backup_timepoint bbt where bbt.available_flag = 1 and (
                    exists (
                        select 1
                        from os_backup_timepoint obt
                        where obt.timepoint_uuid = bbt.timepoint_uuid
                        and obt.agent_uuid = ba.agent_uuid
                    )
                    or exists (
                        select 1
                        from fs_backup_timepoint fbt
                        where fbt.fs_timepoint_uuid = bbt.timepoint_uuid
                        and fbt.agent_uuid = ba.agent_uuid
                    )
                    or exists (
                        select 1
                        from db_backup_timepoint dbt
                        where dbt.timepoint_uuid = bbt.timepoint_uuid
                        and dbt.agent_uuid = ba.agent_uuid
                    )
                    or exists (
                        select 1
                        from cdp_vol_backup_agent cvba
                        where cvba.timepoint_uuid = bbt.timepoint_uuid
                        and cvba.master_agent_uuid = ba.agent_uuid
                    )
                ) order by bbt.timepoint desc limit 1) backup_time,
                    (select ir.create_time
                        from industry_report ir
                            where ba.agent_uuid = ir.agent_uuid and ir.status != 9
                            order by ir.create_time limit 1) verify_time ";
        }
            $sql = "SELECT ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_version,
                    ba.net_model, UNIX_TIMESTAMP(ba.register_time) AS register_time,ba.user_uuid,
                    ba.plugin_deploy_status, ba.online_flag, ba.plugin_version,
                    ba.agent_type, $agentTypeDesField AS agent_type_des, ba.os_type, ba.port, ba.detail,
                    ba.domain_config, ba.add_ip, ba.auto_change_network_flag, ba.applied_vcenters,
                    ba.agent_package_type, ba.time_zone_offset,
                    ba.domain_config, ba.add_ip, ba.auto_change_network_flag, ba.applied_vcenters, 
                    ba.driver_status, ba.hardware_info,
                    bu.user_name, bu.user_uuid,
                        bag.group_uuid, bag.group_name {$fields} FROM bd_agent ba
            INNER JOIN bd_user bu ON ba.user_uuid=bu.user_uuid
            LEFT JOIN bd_agent_group bag ON ba.group_uuid=bag.group_uuid
            LEFT JOIN ($subSql) baa ON ba.agent_uuid = baa.agent_uuid
            WHERE ba.agent_type != 3 and ba.agent_type != 5 ";

        // 没有bd_user和bd_agent_group的条件筛选，因此这里不用join了
        $sqlCount = "SELECT COUNT(ba.id) as total
                     FROM bd_agent ba
                        LEFT JOIN bd_user bu ON ba.user_uuid=bu.user_uuid
                        LEFT JOIN ($subSql) baa ON ba.agent_uuid=baa.agent_uuid
                     WHERE ba.agent_type != 3 and ba.agent_type != 5 ";
        $sqlParams = [];
        $sqlCountParams = [];
        // 客户端主机、ip、别名的模糊搜索
        if (isset($params['host_ip_alias'])) {
            $hostIpAlias = '%' . v1_escape_wildcard($params['host_ip_alias']) . '%';
            $sqlHostIpAlias = ' AND (ba.ip LIKE ? OR ba.hostname LIKE ? OR ba.agent_name LIKE ? OR ba.add_ip LIKE ?) ';
            $sql .= $sqlHostIpAlias;
            $sqlCount .= $sqlHostIpAlias;
            $sqlParams = [$hostIpAlias, $hostIpAlias, $hostIpAlias, $hostIpAlias];
            $sqlCountParams = [$hostIpAlias, $hostIpAlias, $hostIpAlias, $hostIpAlias];
        }

        // 客户端分组uuid筛选
        if (isset($params['agent_group_uuid'])) {
            $sqlAgentGroupUuid = " AND ba.group_uuid=? ";
            $sql .= $sqlAgentGroupUuid;
            $sqlCount .= $sqlAgentGroupUuid;
            $sqlParams[] = $params['agent_group_uuid'];
            $sqlCountParams[] = $params['agent_group_uuid'];
        }
        // 应用类别
        if (isset($params['app_type'])) {
            $appType = (int) $params['app_type'];
            $sqlAppType = "SELECT agent_uuid FROM bd_agent_app WHERE app_type = ? GROUP BY agent_uuid ";
            $agentAppData = $this->dbSelect($sqlAppType, [$appType]);
            $agentUuidList = [];
            if (is_array($agentAppData) && $agentAppData) {
                $agentUuidList = array_column($agentAppData, 'agent_uuid');
            }
            $agentUuids = "('" . implode("','", $agentUuidList) . "')";
            $sql .= " AND ba.agent_uuid in {$agentUuids} ";
            $sqlCount .= " AND ba.agent_uuid in {$agentUuids} ";
        }

        // 代理类型
        if (isset($params['agent_type'])) {
            $sqlAgentType = ' AND ba.agent_type = ? ';
            $sql .= $sqlAgentType;
            $sqlCount .= $sqlAgentType;
            $sqlParams[] = $params['agent_type'];
            $sqlCountParams[] = $params['agent_type'];
        }

        // 是否为传输代理
        if (isset($params['transfer_agent_flag'])) {
            $sqlTransferAgent = " AND ba.node_uuid = '' AND ba.agent_type = ? AND ba.os_type != 'Windows' ";
            $sql .= $sqlTransferAgent;
            $sqlCount .= $sqlTransferAgent;
            $sqlParams[] = $allAgentType['APPLIANCE'];
            $sqlCountParams[] = $allAgentType['APPLIANCE'];
        }

        // 主机名或IP搜索
        if (isset($params['host_ip'])) {
            $hostIp = '%' . v1_escape_wildcard($params['host_ip']) . '%';
            $sqlHostIp = ' AND (ba.ip LIKE ? OR ba.hostname LIKE ?) ';
            $sql .= $sqlHostIp;
            $sqlCount .= $sqlHostIp;
            $sqlParams = array_merge($sqlParams, [$hostIp, $hostIp]);
            $sqlCountParams = array_merge($sqlCountParams, [$hostIp, $hostIp]);
        }

        // 高级搜索
        $this->buildClientAdvancedSearch($params, $sql, $sqlCount, $sqlParams, $sqlCountParams);

        // 权限
        if (v1_auth_need_check_look()) {
            $allResourceType = xphp_get_config('resource', 'RESOURCE_TYPE');
            $clientUuidSql = v1_auth_get_source_by_type($allResourceType['CLIENT'], 'ba.agent_uuid');
            $agentUuidSql = v1_auth_get_source_by_type($allResourceType['APPLICE'], 'ba.agent_uuid');
            $sql .= " AND (($clientUuidSql) OR ($agentUuidSql))";
            $sqlCount .= " AND (($clientUuidSql) OR ($agentUuidSql))";
        }

        // 排序
        $sort = 'ba.register_time';
        $order = 'DESC';
        // 排序字段处理
        if (isset($params['sort'])) {
            $sortFields = [
                'agent_ip', 'hostname', 'os_version', 'agent_type_des', 'module',
                'app_des', 'agent_version', 'register_time', 'online_status', 'creator',
                'group_name', 'driver_status', 'alias',
            ];
            if (in_array($params['sort'], $sortFields, true)) {
                switch ($params['sort']) {
                    case 'creator':
                        $sort = 'bu.user_name';
                        break;
                    case 'agent_ip':
                        $sort = 'ba.ip';
                        break;
                    case 'agent_type_des':
                        $sort = 'agent_type_des';
                        break;
                    case 'app_des':
                        $sort = 'baa.app_name';
                        break;
                    case 'agent_version':
                        $sort = 'ba.plugin_version';
                        break;
                    case 'online_status':
                        $sort = 'ba.online_flag';
                        break;
                    case 'group_name':
                        $sort = 'baa.group_name';
                        break;
                    case 'backup_flag':
                        $sort = 'backup_time';
                        break;
                    case 'verify_flag':
                        $sort = 'verify_time';
                        break;
                    case 'agent_uuid':
                        $sort = 'ba.agent_uuid';
                        break;
                    case 'alias':
                        $sort = 'ba.agent_name';
                        break;
                    default:
                        $sort = 'ba.' . $params['sort'];
                }
            }
        }

        // 排序方式处理
        if (isset($params['order'])) {
            $order = in_array(strtolower($params['order']), ['asc', 'desc']) ? strtolower($params['order']) : $order;
        }
        // 排序这里采用预处理查询会导致查询卡顿
        $sqlSort = " GROUP BY ba.agent_uuid ORDER BY $sort $order ";
        $sql .= $sqlSort;

        // 分页
        if ($params['offset_flag'] ?? true) {
            $sqlOffset = ' LIMIT ?,? ';
            $sql .= $sqlOffset;
            $sqlParams[] = (int)$params['offset'];
            $sqlParams[] = (int)$params['limit'];
        }

        $dataSelect = $this->dbSelect($sql, $sqlParams);
        $countSelect = $this->dbSelect($sqlCount, $sqlCountParams);
        return ['data' => $dataSelect, 'count' => $countSelect[0]['total']];
    }

    /**
     * 构建高级搜索的sql
     * @param array  $params         参数
     * @param string $sql            sql
     * @param string $sqlCount       sqlCount
     * @param array  $sqlParams      sql的参数
     * @param array  $sqlCountParams sqlCount的参数
     * @return void
     */
    private function buildClientAdvancedSearch(
        array $params,
        string &$sql,
        string &$sqlCount,
        array &$sqlParams,
        array &$sqlCountParams
    ): void {
        // 添加时间
        if (isset($params['h_start_time']) && isset($params['h_end_time'])) {
            $timeSql = ' AND (UNIX_TIMESTAMP(ba.register_time) BETWEEN ? AND ? ) ';
            $sql .= $timeSql;
            $sqlCount .= $timeSql;
            $sqlParams[] = strtotime($params['h_start_time']);
            $sqlParams[] = strtotime($params['h_end_time']);
            $sqlCountParams[] = strtotime($params['h_start_time']);
            $sqlCountParams[] = strtotime($params['h_end_time']);
        }

        // IP地址
        if (isset($params['h_ip'])) {
            $hIp = '%' . v1_escape_wildcard($params['h_ip']) . '%';
            $ipSql = ' AND (ba.ip LIKE ? OR ba.add_ip LIKE ?) ';
            $sql .= $ipSql;
            $sqlCount .= $ipSql;
            $sqlParams[] = $hIp;
            $sqlParams[] = $hIp;
            $sqlCountParams[] = $hIp;
            $sqlCountParams[] = $hIp;
        }

        // 主机名
        if (isset($params['h_hostname'])) {
            $hHostname = '%' . v1_escape_wildcard($params['h_hostname']) . '%';
            $hostnameSql = ' AND ba.hostname LIKE ? ';
            $sql .= $hostnameSql;
            $sqlCount .= $hostnameSql;
            $sqlParams[] = $hHostname;
            $sqlCountParams[] = $hHostname;
        }

        // 别名
        if (isset($params['h_alias'])) {
            $hAlias = '%' . v1_escape_wildcard($params['h_alias']) . '%';
            $aliasSql = ' AND ba.agent_name LIKE ? ';
            $sql .= $aliasSql;
            $sqlCount .= $aliasSql;
            $sqlParams[] = $hAlias;
            $sqlCountParams[] = $hAlias;
        }

        // 操作系统
        if (isset($params['h_os_version'])) {
            $hOsVersion = '%' . v1_escape_wildcard($params['h_os_version']) . '%';
            $osVersionSql = ' AND ba.os_version LIKE ? ';
            $sql .= $osVersionSql;
            $sqlCount .= $osVersionSql;
            $sqlParams[] = $hOsVersion;
            $sqlCountParams[] = $hOsVersion;
        }

        // 在线状态
        if (isset($params['h_online_status'])) {
            $hOnlineStatus = (int) $params['h_online_status'];
            $onlineStatusSql = ' AND ba.online_flag = ? ';
            $sql .= $onlineStatusSql;
            $sqlCount .= $onlineStatusSql;
            $sqlParams[] = $hOnlineStatus;
            $sqlCountParams[] = $hOnlineStatus;
        }

        // 部署状态
        if (isset($params['h_deploy_status'])) {
            $hDeployStatus = (int) $params['h_deploy_status'];
            $deployStatusSql = ' AND ba.plugin_deploy_status = ? ';
            $sql .= $deployStatusSql;
            $sqlCount .= $deployStatusSql;
            $sqlParams[] = $hDeployStatus;
            $sqlCountParams[] = $hDeployStatus;
        }

        // 应用名
        if (isset($params['h_app_name'])) {
            $hAppName = '%' . v1_escape_wildcard($params['h_app_name']) . '%';
            $appNameSql = ' AND baa.app_name LIKE ? ';
            $sql .= $appNameSql;
            $sqlCount .= $appNameSql;
            $sqlParams[] = $hAppName;
            $sqlCountParams[] = $hAppName;
        }

        // 所有者
        if (isset($params['h_owner'])) {
            /**
             * 所有者搜索
             * 1. 所有者可以被资源分配和资源组分配
             * 2. 搜索所有者，需要先查询所有者的用户uuid，然后在查询这些用户拥有的代理资源
             * 3. 部分资源没有分配，此时要搜索创建者
             */
            $hOwner = '%' . v1_escape_wildcard($params['h_owner']) . '%';

            // 查询满足搜索条件的用户uuid
            $userData = dbSelect("SELECT user_uuid FROM bd_user WHERE user_name LIKE ? ", [$hOwner]);
            $userUuidList = array_column($userData, 'user_uuid');
            $userUuids = "'" . implode("', '", $userUuidList) . "'";
            $agentUuidList = [];
            if (is_array($userData) && $userData) {
                // 获取这些用户拥有的资源
                // 1. 查询资源
                $subSql = "SELECT resource_uuid FROM mt_user_resource WHERE user_uuid IN ($userUuids)";
                $agentData = $this->dbSelect($subSql);
                if (is_array($agentData) && $agentData) {
                    $agentUuidList = array_column($agentData, 'resource_uuid');
                }
                // 2. 查询资源组
                $subSql = "SELECT mrrg.resource_uuid FROM mt_resource_resource_group mrrg
                            INNER JOIN mt_user_resource_group murg
                                ON mrrg.resource_group_uuid = murg.resource_group_uuid
                        WHERE murg.user_uuid IN ($userUuids) ";
                $agentData = $this->dbSelect($subSql);
                if (is_array($agentData) && $agentData) {
                    $agentUuidList = array_merge($agentUuidList, array_column($agentData, 'resource_uuid'));
                }

                // 去重
                $agentUuidList = array_values(array_unique($agentUuidList));
            }
            // 查询未分配的客户端资源
            $unassignedAgentUuidList = $this->getUnassignedAgentUuidListByUserUuids($userUuids);
            $agentUuidList = array_merge($agentUuidList, $unassignedAgentUuidList);
            $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
            $ownerSql = " AND ba.agent_uuid IN ($agentUuids) ";
            $sql .= $ownerSql;
            $sqlCount .= $ownerSql;
        }
    }

    /**
     * 获取所有未分配的客户端资源uuid列表
     * @param string $userUuids 用户uuid列表
     * @return array
     */
    private function getUnassignedAgentUuidListByUserUuids(string $userUuids): array
    {
        $allResourceType = xphp_get_config('resource', 'RESOURCE_TYPE');
        $resourceTypes = "'" . $allResourceType['CLIENT'] . "', '" . $allResourceType['APPLICE'] . "'";
        // 资源sql
        $sql1 = "SELECT resource_uuid FROM mt_user_resource WHERE mt_user_resource.resource_type IN ($resourceTypes)";
        // 资源组sql
        $sql2 = "SELECT mrrg.resource_uuid
                 FROM mt_resource_resource_group mrrg
                    INNER JOIN mt_user_resource_group murg ON mrrg.resource_group_uuid = murg.resource_group_uuid
                WHERE mrrg.resource_type IN ($resourceTypes)";
        // 查询没有分配的客户端资源
        $sql = "SELECT agent_uuid
                FROM bd_agent
                WHERE agent_uuid NOT IN ($sql1) AND agent_uuid NOT IN ($sql2)
                    AND user_uuid IN ($userUuids) ";
        $agentData = $this->dbSelect($sql);
        if (!is_array($agentData)) {
            $agentData = [];
        }
        return array_column($agentData, 'agent_uuid');
    }

    /**
     * 根据客户端 uuid 获取当前的使用者是谁 只查询分配的资源/组
     * @param string $agentUuid 客户端uuid
     * @param string $userName  拥有着名称
     * @return string
     */
    private function getClientUuidName(string $agentUuid, string $userName = ''): string
    {
        $sqlParams = [$agentUuid];
        // 先 再分配的资源和资源组去查询
        // 适配租户，租户有租户管理员和租户，都要显示
        $sql2 = "select user_uuid from mt_user_resource where resource_type IN (2, 10) and resource_uuid = ?";
        $array = $this->dbSelect($sql2, $sqlParams);
        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type In (2, 10) ";
            $array = $this->dbSelect($sql, $sqlParams);
        }

        if (is_array($array) && $array) {
            $userUuids = "'" . implode("', '", array_column($array, 'user_uuid')) . "'";
            $sql = "select user_name from bd_user where user_uuid IN ($userUuids)";
            $data = $this->dbSelect($sql);
            if (!is_array($data)) {
                $data = [];
            }
            $userName = implode(',', array_column($data, 'user_name'));
        }

        return $userName;
    }

    /**
     * 获取客户端的网卡信息
     * @param array|null $detail 客户端detail字段(json反序列化了)
     * @return array
     */
    private function getClientNetworkList(?array $detail): array
    {
        if (!$detail) {
            return [];
        }
        $ipList = $detail['nic_list'];
        $networkList = array();
        foreach ($ipList as $i) {
            $list = $i['ip_set'];
            $ip = '';
            $netmask = '';
            foreach ($list as $net) {
                if ($net['ip_addr'] != '127.0.0.1') {
                    $ip .= $net['ip_addr'] . PHP_EOL;
                    $netmask .= $net['netmask'] . PHP_EOL;
                }
            }
            $networkList[] = array(
                'network_name' => $i['name'],
                'ip' => $ip,
                'mac' => $i['mac_address'],
                'netmask' => $netmask,
                'gateway' => $i['gateway_address']
            );
        }
        return $networkList;
    }

    /**
     * 获取代理的任务信息
     * @param array $agentList 代理列表
     * @return array
     */
    private function getAgentTaskMap(array $agentList): array
    {
        /**
         * 1. 备份任务关联: bd_task_agent_list.agent_uuid
         * 2. 数据库定时恢复任务的恢复目标: db_list.source_agent_uuid
         * 3. 虚拟化的传输代理: vm_task.agent_uuid
         * 4. Hadoop、对象存储的传输代理: fs_task.proxy_uuid
         * 5. 数据库实时: cdp_db_dr_task.source_agent_uuid或cdp_db_dr_task.target_agent_uuid
         * 6. 整机瞬时恢复: os_list.inst_recovery_agent_uuid
         */
        $agentUuidList = array_column($agentList, 'agent_uuid');
        $agentUuids = "'" . implode("', '", $agentUuidList) . "'";

        $agentTaskMap = [];
        foreach ($agentUuidList as $agentUuid) {
            $agentTaskMap[$agentUuid] = [
                'task_count' => 0,
                'task_list' => [],
                'all_task_name' => ''
            ];
        }

        // 备份任务关联
        $taskSql = "SELECT bt.task_name, bt.task_uuid, btal.agent_uuid
                    FROM bd_task bt
                        INNER JOIN bd_task_agent_list btal ON bt.task_uuid = btal.task_uuid
                    WHERE btal.agent_uuid IN ($agentUuids) AND bt.task_type != ? ";
        $taskData = $this->dbSelect($taskSql, [xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']]);
        if (is_array($taskData) && $taskData) {
            foreach ($taskData as $taskInfo) {
                $agentTaskMap[$taskInfo['agent_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
            }
        }

        // 代理是否存在数据库集群备份任务
        $clusterSql = "SELECT bt.task_name, bt.task_uuid, baa.cluster_uuid FROM bd_agent_app baa
        	            INNER JOIN db_list dl ON dl.instance_name = baa.app_name AND dl.agent_uuid = baa.agent_uuid
                        INNER JOIN bd_task bt ON dl.task_uuid = bt.task_uuid
                    WHERE baa.agent_uuid IN ($agentUuids)
                        AND baa.cluster_uuid IS NOT NULL AND baa.cluster_uuid != '' ";
        $clusterData = $this->dbSelect($clusterSql);
        if (is_array($clusterData) && $clusterData) {
            $taskClusterMap = [];
            foreach ($clusterData as $clusterInfo) {
                $taskClusterMap[$clusterInfo['cluster_uuid']][$clusterInfo['task_uuid']] = $clusterInfo['task_name'];
            }

            $clusterUuidList = array_column($clusterData, 'cluster_uuid');
            $clusterUuidList = array_values(array_unique($clusterUuidList));
            $clusterUuids = "'". implode("', '", $clusterUuidList). "'";
            $agentSql = "SELECT agent_uuid, cluster_name, cluster_uuid FROM bd_agent_app WHERE cluster_uuid IN ($clusterUuids) ";
            $agentData = $this->dbSelect($agentSql);

            foreach ($agentData as $agentInfo) {
                $taskList = $taskClusterMap[$agentInfo['cluster_uuid']];
                foreach ($taskList as $taskUuid => $taskName) {
                    $agentTaskMap[$agentInfo['agent_uuid']]['task_list'][$taskUuid] = $taskName;
                }
            }
        }
        // 代理是否存在数据库单机备份任务
        $taskSql = "SELECT bt.task_name, bt.task_uuid, btal.agent_uuid
                    FROM bd_task bt
                        INNER JOIN bd_task_agent_list btal ON bt.task_uuid = btal.task_uuid
                        INNER JOIN bd_agent_app baa ON baa.agent_uuid = btal.agent_uuid
                    WHERE btal.agent_uuid IN ($agentUuids) AND bt.task_type = ?
                        AND (baa.cluster_uuid IS NULL OR baa.cluster_uuid = '') ";
        $taskData = $this->dbSelect($taskSql, [xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']]);
        if (is_array($taskData) && $taskData) {
            foreach ($taskData as $taskInfo) {
                $agentTaskMap[$taskInfo['agent_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
            }
        }

        // 数据库定时恢复任务的恢复目标
        $taskSql = "SELECT bt.task_name, bt.task_uuid, dl.source_agent_uuid
                    FROM bd_task bt
                        INNER JOIN db_list dl ON bt.task_uuid = dl.task_uuid
                    WHERE dl.source_agent_uuid IN ($agentUuids) ";
        $taskData = $this->dbSelect($taskSql);
        if (is_array($taskData) && $taskData) {
            foreach ($taskData as $taskInfo) {
                $agentTaskMap[$taskInfo['source_agent_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
            }
        }

        // 虚拟化的传输代理
        $taskSql = "SELECT bt.task_name, bt.task_uuid, vt.agent_uuid
                FROM bd_task bt
                    INNER JOIN vm_task vt ON bt.task_uuid = vt.task_uuid
                WHERE vt.agent_uuid IN ($agentUuids) ";
        $taskData = $this->dbSelect($taskSql);
        if (is_array($taskData) && $taskData) {
            foreach ($taskData as $taskInfo) {
                $agentTaskMap[$taskInfo['agent_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
            }
        }

        // Hadoop、对象存储的传输代理
        $taskSql = "SELECT bt.task_name, bt.task_uuid, ft.proxy_uuid
                FROM bd_task bt
                    INNER JOIN fs_task ft ON bt.task_uuid = ft.task_uuid
                WHERE ft.proxy_uuid IN ($agentUuids) ";
        $taskData = $this->dbSelect($taskSql);
        if (is_array($taskData) && $taskData) {
            foreach ($taskData as $taskInfo) {
                $agentTaskMap[$taskInfo['proxy_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
            }
        }

        // 数据库实时
        $taskSql = "SELECT bt.task_name, bt.task_uuid, cddt.source_agent_uuid, cddt.target_agent_uuid, cddttfi.failback_target_agent_uuid FROM bd_task bt 
                        INNER JOIN cdp_db_dr_task cddt ON bt.task_uuid = cddt.task_uuid 
                        INNER JOIN bd_agent_app baa ON baa.agent_uuid = cddt.source_agent_uuid OR baa.agent_uuid = cddt.target_app_uuid
                        LEFT JOIN cdp_db_dr_task_takeover_failback_info cddttfi ON bt.task_uuid = cddttfi.task_uuid
                        WHERE cddt.source_agent_uuid IN ($agentUuids) 
                        OR cddt.target_agent_uuid in ($agentUuids) and bt.task_type = ? or bt.task_type = ?";
        $taskData = $this->dbSelect($taskSql,array(xphp_get_config('task', 'TASKTYPE')['CDP_DB_BACKUP'],xphp_get_config('task', 'TASKTYPE')['CDP_DB_RECOVERY']));
        if (is_array($taskData) && $taskData) {
            foreach ($taskData as $taskInfo) {
                $agentTaskMap[$taskInfo['source_agent_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
                $agentTaskMap[$taskInfo['target_agent_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
                $agentTaskMap[$taskInfo['failback_target_agent_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
            }
        }

        // 6. 整机瞬时恢复
        $taskSql = "SELECT bt.task_name, bt.task_uuid, ol.inst_recovery_agent_uuid
                FROM bd_task bt
                    INNER JOIN os_list ol ON bt.task_uuid = ol.task_uuid
                WHERE ol.inst_recovery_agent_uuid IN ($agentUuids) ";
        $taskData = $this->dbSelect($taskSql);
        if (is_array($taskData) && $taskData) {
            foreach ($taskData as $taskInfo) {
                $agentTaskMap[$taskInfo['inst_recovery_agent_uuid']]['task_list'][$taskInfo['task_uuid']] = $taskInfo['task_name'];
            }
        }

        foreach ($agentTaskMap as $agentUuid => $taskInfo) {
            $agentTaskMap[$agentUuid]['task_list'] = array_values($taskInfo['task_list']);
            $agentTaskMap[$agentUuid]['task_count'] = count($taskInfo['task_list']);
            $agentTaskMap[$agentUuid]['all_task_name'] = implode('<br>', $taskInfo['task_list']);
        }

        return $agentTaskMap;
    }

    /**
     * 获取传输代理资源池名称
     * @param string $agentUuid 代理uuid
     * @return array
     */
    private function getAgentPoolName(string $agentUuid): array
    {
        $sql = "SELECT bap.agent_pool_nickname
                FROM bd_agent_pool bap
                    INNER JOIN bd_agent_pool_list bapl ON bap.agent_pool_uuid = bapl.agent_pool_uuid
                WHERE bapl.agent_uuid = ? GROUP BY bap.agent_pool_uuid ";
        $data = $this->dbSelect($sql, [$agentUuid]);
        if (!is_array($data) || !$data) {
            $data = [];
        }
        return array_column($data, 'agent_pool_nickname');
    }

    /**
     * 获取客户端详情
     * @param array $params 数据
     * @return array
     */
    public function getClientDetail(array $params = []): array
    {

        $sql = "select ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_version, ba.detail, ba.process_type,
                        ba.agent_username, ba.agent_password, ba.net_model, ba.agent_os_type, ba.port,
                		ba.client_transport_port, unix_timestamp(ba.register_time) AS register_time,
                        ba.plugin_deploy_status, ba.online_flag, ba.authorization_module, ba.plugin_version,
                		bu.user_name, bu.user_uuid 
                from bd_agent ba 
                left join  bd_user bu 
                on ba.user_uuid = bu.user_uuid where ba.agent_type != 3 and agent_uuid = ?";

        $data = $this->dbSelect($sql, [$params['agents_uuid']]);

        if (empty($data[0])) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }
        // 组装返回信息
        $d = $data[0];

        //应用配置
        $appList = $this->getApplicationList();

        $agentUuid = $d['agent_uuid'];
        //应用配置描述
        $appDes = '';
        foreach ($appList as $app) {
            if ($agentUuid != $app['agent_uuid']) {
                continue;
            }
            $appDes .= $app['app_name'] .
                '(' . xphp_get_config('db', 'DB_TYPE_DES')[(int) $app['app_type']] . ')' . PHP_EOL;
        }

        $nullSpace = xphp_get_config('app', 'NULLSPACE');

        //未获取到应用配置
        if (empty($appDes)) {
            $appDes = $nullSpace;
        }

        return $this->sendResult('', true, 200, [
            'agent_ip' => $d['ip'],
            'hostname' => !empty($d['hostname']) ? $d['hostname'] : $nullSpace,
            'alias' => !empty($d['agent_name']) ? $d['agent_name'] : $nullSpace,
            'net_model' => (int)$d['net_model'],
            'agent_uuid' => $d['agent_uuid'],
            'module' => !empty($d['authorization_module']) ? $d['authorization_module'] : '',
            'online_status' => ($d['online_flag'] == 1),
            'deploy_status' => ($d['plugin_deploy_status'] == 1),
            'os_version' => !empty($d['os_version']) ? $d['os_version'] : $nullSpace,
            'client_version' => $d['plugin_version'] ?: $nullSpace,
            'register_time' => $this->parseDate($d['register_time']),
            'application_description' => $appDes,
        ]);
    }

    /**
     * 删除客户端
     * @param string $agentsUuid          客户端uuid
     * @param int    $uninstallPluginFlag 卸载插件标志【1卸载 2不卸载】
     * @return array
     */
    public function deleteClient(string $agentsUuid, int $uninstallPluginFlag): array
    {
        $opcodeName = 'NODE_AGENT_OP_DEL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        // 验证删除客户端
        $validateResult = $this->validateDeleteClient([$agentsUuid]);
        if ($validateResult['success'] === false) {
            return $validateResult;
        }
        $agentInfo = $validateResult['data'];

        // 权限验证
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        if ($agentInfo[0]['agent_type'] == $allAgentType['APPLIANCE']) {
            $this->checkAuthBySourceUuid($agentsUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['APPLICE']);
        } else {
            $this->checkAuthBySourceUuid($agentsUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);
        }

        // 检查代理是否处于资源池中
        $sql = "SELECT bapl.agent_uuid, bap.agent_pool_uuid, bap.agent_pool_nickname
                FROM bd_agent_pool bap
                    INNER JOIN bd_agent_pool_list bapl ON bap.agent_pool_uuid = bapl.agent_pool_uuid
                WHERE bapl.agent_uuid = ? ";
        $agentPoolData = $this->dbSelect($sql, [$agentsUuid]);
        if (is_array($agentPoolData) && $agentPoolData) {
            $agentPoolNameList = array_column($agentPoolData, 'agent_pool_nickname');
            return $this->sendResult(sprintf(xphp_get_lang('WEB_AGENT_DELETE_IN_POOL'), implode("、", $agentPoolNameList)), false, 0);
        }
        // 删除客户端
        $deleteResult = $this->service()->deleteClientService([$agentsUuid], $uninstallPluginFlag);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_DELETE_ERROR'), false, 0);
        }
        $detail = array_map(function ($agent) {
            return sprintf(
                xphp_get_lang('WEB_AGENT_DELETE_DETAIL'),
                $agent['hostname'] . '(' . $agent['ip'] . ')'
            );
        }, $agentInfo);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_DELETE_SUCCESS'), true, 200, $detail);
    }

    /**
     * 批量删除客户端
     * @param array $agentUuids          客户端列表
     * @param int   $uninstallPluginFlag 卸载插件标志【1卸载 2不卸载】
     * @return array
     */
    public function deleteClientList(array $agentUuids, int $uninstallPluginFlag): array
    {
        $opcodeName = 'NODE_AGENT_OP_DEL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        // 去重
        $agentUuids = array_values(array_unique($agentUuids));
        // 验证删除客户端
        $validateResult = $this->validateDeleteClient($agentUuids);
        if ($validateResult['success'] === false) {
            return $validateResult;
        }
        $agentInfo = $validateResult['data'];

        // 权限验证
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $agentList = array_filter($agentInfo, function($row) use ($allAgentType) {
            return $row['agent_type'] == $allAgentType['APPLIANCE'];
        });
        $clientList = array_filter($agentInfo, function($row) use ($allAgentType) {
            return $row['agent_type'] != $allAgentType['APPLIANCE'];
        });
        if ($agentList) {
            $this->checkAuthBySourceUuid(
                implode(',', array_column($agentList, 'agent_uuid')),
                xphp_get_config('resource', 'RESOURCE_TYPE')['APPLICE']
            );
        }
        if ($clientList) {
            $this->checkAuthBySourceUuid(
                implode(',', array_column($clientList, 'agent_uuid')),
                xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']
            );
        }

        // 检查代理是否处于资源池中
        $agentUuidStr = "'" . implode("', '", $agentUuids) . "'";
        $sql = "SELECT bapl.agent_uuid, bap.agent_pool_uuid, bap.agent_pool_nickname
                FROM bd_agent_pool bap
                    INNER JOIN bd_agent_pool_list bapl ON bap.agent_pool_uuid = bapl.agent_pool_uuid
                WHERE bapl.agent_uuid IN ($agentUuidStr) ";
        $agentPoolData = $this->dbSelect($sql);
        if (is_array($agentPoolData) && $agentPoolData) {
            $agentPoolNameList = array_column($agentPoolData, 'agent_pool_nickname');
            return $this->sendResult(sprintf(xphp_get_lang('WEB_AGENT_DELETE_IN_POOL'), implode("、", $agentPoolNameList)), false, 0);
        }
        // 删除客户端
        $deleteResult = $this->service()->deleteClientService($agentUuids, $uninstallPluginFlag);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_DELETE_LIST_ERROR'), false, 0);
        }
        $detail = array_map(function ($agent) {
            return sprintf(
                xphp_get_lang('WEB_AGENT_DELETE_DETAIL'),
                $agent['hostname'] . '(' . $agent['ip'] . ')'
            );
        }, $agentInfo);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_DELETE_LIST_SUCCESS'), true, 200, $detail);
    }

    /**
     * 检查删除的客户端是否关联有office 365组织
     * @param array $uuids 客户端uuid
     * @return array
     */
    private function checkClientOrganizationExist(array $uuids): array
    {
        $uuidDes = implode("','", $uuids);
        // 因为m365_organization存储的是json格式的客户端列表，因此这里要将所有的office 365组织查询出来
        $sql = "SELECT agent_uuid_list, organization_name FROM m365_organization";
        $organizationList = $this->dbSelect($sql);
        if (!$organizationList || !is_array($organizationList)) {
            return $this->sendResult('');
        }
        $sql = "SELECT ip, agent_uuid FROM bd_agent WHERE agent_uuid IN ('$uuidDes')";
        $agentList = $this->dbSelect($sql);
        foreach ($organizationList as $organization) {
            $agentUuidList = json_decode($organization['agent_uuid_list'], true);
            if (!$agentUuidList) {
                continue;
            }
            $intersectUuidList = array_intersect($agentUuidList, $uuids);
            if ($intersectUuidList) {  // 存在交集，说明某个客户端有关联组织
                $msg = sprintf(
                    xphp_get_lang('WEB_CLIENT_ASSOCIATED_ORGANIZATION'),
                    implode(',', array_map(function ($uuid) use ($agentList) {
                        foreach ($agentList as $agent) {
                            if ($agent['agent_uuid'] == $uuid) {
                                return $agent['ip'];
                            }
                        }
                        return '';
                    }, $intersectUuidList)),
                    $organization['organization_name']
                );
                return $this->sendResult($msg, false, 0);
            }
        }
        return $this->sendResult('');
    }

    /**
     * 验证删除客户端, 返回客户端信息
     * 1. 客户端必须存在
     * 2. 客户端不能有任务
     * @param array $agentUuids 客户端uuid列表
     * @return array
     */
    private function validateDeleteClient(array $agentUuids): array
    {
        // 获取客户端列表
        $in = "'" . implode("', '", $agentUuids) . "'";
        $sql = "SELECT * FROM bd_agent WHERE agent_uuid IN ($in) ";
        $agentInfo = $this->dbSelect($sql);
        if (!is_array($agentInfo) || count($agentInfo) != count($agentUuids)) {
            // 删除的客户端uuid列表里面有不存在的客户端
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }
        // 检查是否关联有组织(office 365)
        $checkResult = $this->checkClientOrganizationExist($agentUuids);
        if ($checkResult['success'] === false) {
            return $checkResult;
        }
        // 检查是否有生产资源关联(Hadoop、对象存储)
        $checkResult = $this->checkClientProductionExist($in);
        if ($checkResult['success'] === false) {
            return $checkResult;
        }
        // 检查是否存在任务
        $checkResult = $this->checkClientTaskExist($in);
        if ($checkResult['success'] === false) {
            return $checkResult;
        }
        return $this->sendResult('', true, 200, $agentInfo);
    }

    /**
     * 检查代理是否被生产资源关联
     * @param string $agentUuids 代理uuid列表
     * @return array
     */
    private function checkClientProductionExist(string $agentUuids): array
    {
        /**
         * 1、Hadoop集群
         * 2、对象存储
         */
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        // 判断传输代理是否被Hadoop集群关联
        $hadoopClusterSql = "SELECT hc.hadoop_cluster_name, ba.ip
                            FROM hadoop_cluster hc
                                INNER JOIN bd_agent ba ON hc.proxy_uuid = ba.agent_uuid
                            WHERE hc.proxy_uuid IN ($agentUuids) AND ba.agent_type = ? ";
        $data = $this->dbSelect($hadoopClusterSql, [$allAgentType['APPLIANCE']]);
        if (is_array($data) && $data) {
            $detailOne = sprintf(
                xphp_get_lang('WEB_AGENT_DELETE_CHECK_HADOOP'),
                $data[0]['hadoop_cluster_name'],
                $data[0]['ip']
            );
            return $this->sendResult($detailOne, false, 0);
        }

        // 判断传输代理是否被对象存储关联
        $obsSql = "SELECT or1.obs_nickname, ba.ip
                    FROM obs_resource or1
                        INNER JOIN bd_agent ba ON or1.proxy_uuid = ba.agent_uuid
                    WHERE or1.proxy_uuid IN ($agentUuids) AND ba.agent_type = ? ";
        $data = $data ?: $this->dbSelect($obsSql, [$allAgentType['APPLIANCE']]);
        if (is_array($data) && $data) {
            $detailOne = sprintf(
                xphp_get_lang('WEB_AGENT_DELETE_CHECK_OBS'),
                $data[0]['obs_nickname'],
                $data[0]['ip']
            );
            return $this->sendResult($detailOne, false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 检查客户端是否有任务存在
     * @param string $in 客户端uuid列表转为了字符串形式
     * @return array
     */
    private function checkClientTaskExist(string $in): array
    {
        // 任务类型
        $taskType = xphp_get_config('task', 'TASKTYPE');
        /**
         * 这里各个类型判断只做查询，在最后进行错误判断
         * 优先级从上往下递减
         * 1. 备份任务
         * 2. cdp备份任务
         * 3. cdp恢复目标主机
         * 4. cdp双机镜像备机
         * 5. cdp接管机
         * 6. cdp回切目标机
         * 7. 数据库定时恢复的目标机
         * 8. 生产资源（Hadoop、对象存储）备份、恢复任务是否选择了这个传输代理
         * 9. 虚拟化备份、恢复任务是否选择了这个传输代理
         *  10. 整机瞬时恢复: os_list.inst_recovery_agent_uuid
         */
        // 客户端是否存在任务(这里可以判断数据库单机任务)
        $sql = "SELECT bt.task_name, bt.task_uuid, ba.ip FROM bd_agent ba
                    INNER JOIN bd_task_agent_list btal ON btal.agent_uuid=ba.agent_uuid
                    INNER JOIN bd_task bt on btal.task_uuid= bt.task_uuid
        	    WHERE ba.agent_uuid IN ($in) ";
        $data = $this->dbSelect($sql);

        // 客户端是否存在数据库集群备份任务
        // 1.查询集群uuid
        $sql = "SELECT baa.cluster_uuid FROM bd_agent_app baa
                WHERE baa.agent_uuid IN ($in)
                AND baa.cluster_uuid IS NOT NULL AND baa.cluster_uuid != '' ";
        $clusterData = $this->dbSelect($sql);
        if (is_array($clusterData) && $clusterData) {
            // 2.查询集群备份任务
            $clusterUuidList = array_column($clusterData, 'cluster_uuid');
            $clusterUuidList = array_values(array_unique($clusterUuidList));
            $clusterUuids = "'" . implode("', '", $clusterUuidList) . "'";
            $clusterSql = "SELECT ba.ip, bt.task_uuid, bt.task_name FROM db_list dl
                            INNER JOIN bd_agent_app baa ON dl.agent_uuid = baa.agent_uuid
                                AND dl.instance_name = baa.app_name
                            INNER JOIN bd_task bt ON dl.task_uuid = bt.task_uuid
                            INNER JOIN bd_agent ba ON ba.agent_uuid = baa.agent_uuid
                           WHERE baa.cluster_uuid IN ($clusterUuids) ";
            $data = $data ?: $this->dbSelect($clusterSql);
        }

        // 判断客户端是否存在卷cdp备份任务
        $volCdpBackupSql = "SELECT cvt.master_agent_ip AS ip, bt.task_name, bt.task_uuid FROM cdp_vol_task cvt
                                INNER JOIN bd_task bt ON bt.task_uuid=cvt.task_uuid
                            WHERE cvt.master_agent_uuid IN ($in) AND bt.task_type = ?";
        // 采用条件判断操作避免多余的查询
        $data = $data ?: $this->dbSelect($volCdpBackupSql, [$taskType['VOL_CDP_BACKUP']]);

        // 判断客户端是否做为卷cdp恢复目标主机
        $volCdpRecoverySql = "SELECT bt.task_name, bt.task_uuid, ba.ip FROM bd_agent ba
                                INNER JOIN cdp_vol_task cvt ON cvt.recovery_target_agent_uuid=ba.agent_uuid
                                INNER JOIN bd_task bt ON bt.task_uuid=cvt.task_uuid
                              WHERE cvt.recovery_target_agent_uuid IN ($in) AND bt.task_type = ?";
        $data = $data ?: $this->dbSelect($volCdpRecoverySql, [$taskType['VOL_CDP_RECOVERY']]);

        // 判断客户端是否用作卷cdp双机镜像备机
        $volCdpDoubleMirrorSql = "SELECT bt.task_name, bt.task_uuid, ba.ip FROM bd_agent ba
                                    INNER JOIN cdp_vol_task cvt ON cvt.standby_agent_uuid=ba.agent_uuid
                                    INNER JOIN bd_task bt ON bt.task_uuid=cvt.task_uuid
                                  WHERE cvt.standby_agent_uuid IN ($in) AND bt.task_type = ?";
        $data = $data ?: $this->dbSelect($volCdpDoubleMirrorSql, [$taskType['VOL_CDP_BACKUP']]);

        // 判断客户端是否用作卷cdp接管备机
        $volCdpTakeoverSql = "SELECT bt.task_name, bt.task_uuid, ba.ip FROM bd_agent ba
                                INNER JOIN cdp_vol_task_takeover_info cvtti
                                    ON ba.agent_uuid=cvtti.takeover_standby_agent_uuid
                                INNER JOIN bd_task bt ON bt.task_uuid=cvtti.task_uuid
                              WHERE cvtti.takeover_standby_agent_uuid IN ($in)
                                AND (bt.task_type = ? OR bt.task_type = ?)";
        $data = $data ?: $this->dbSelect(
            $volCdpTakeoverSql,
            [$taskType['VOL_CDP_BACKUP'], $taskType['VOL_CDP_TAKEOVER']]
        );

        // 判断客户端是否用作卷cdp回切目标机
        $volCdpFailBackSql = "SELECT bt.task_name, bt.task_uuid, ba.ip FROM bd_agent ba
                                INNER JOIN cdp_vol_task_takeover_failback_info cvttfi
                                    ON ba.agent_uuid=cvttfi.failback_target_agent_uuid
                                INNER JOIN bd_task bt
                              WHERE cvttfi.failback_target_agent_uuid IN ($in)
                                AND (bt.task_type = ? OR bt.task_type = ?)";
        $data = $data ?: $this->dbSelect(
            $volCdpFailBackSql,
            [$taskType['VOL_CDP_BACKUP'], $taskType['VOL_CDP_TAKEOVER']]
        );

        // 判断客户端是否作为恢复最新点的目标机
        $recoveryNewestSql = "SELECT bt.task_uuid, bt.task_name, ba.ip
                            FROM bd_task bt
                                INNER JOIN db_list dl ON dl.task_uuid = bt.task_uuid
                                INNER JOIN bd_agent ba ON ba.agent_uuid = dl.source_agent_uuid
                            WHERE dl.source_agent_uuid IN ($in) ";
        $data = $data ?: $this->dbSelect($recoveryNewestSql);

        // 判断传输代理是否被生产资源备份、恢复任务关联
        $productionTaskSql = "SELECT bt.task_uuid, bt.task_name, ba.ip
                                FROM bd_task bt
                                    INNER JOIN fs_task ft ON ft.task_uuid = bt.task_uuid
                                    INNER JOIN bd_agent ba ON ba.agent_uuid = ft.proxy_uuid
                                WHERE ft.proxy_uuid IN ($in) ";
        $data = $data ?: $this->dbSelect($productionTaskSql);

        // 判断虚拟化备份、恢复任务是否选择了这个传输代理
        $vmTaskSql = "SELECT bt.task_uuid, bt.task_name, ba.ip
                                FROM bd_task bt
                                    INNER JOIN vm_task vt ON vt.task_uuid = bt.task_uuid
                                    INNER JOIN bd_agent ba ON ba.agent_uuid = vt.agent_uuid
                                WHERE vt.agent_uuid IN ($in) ";
        $data = $data ?: $this->dbSelect($vmTaskSql);

        // 判断整机瞬时恢复任务目标是否包含客户端
        $instRecoveryTaskSql = "SELECT bt.task_uuid, bt.task_name, ba.ip
                                FROM bd_task bt
                                    INNER JOIN os_list ol ON ol.task_uuid = bt.task_uuid
                                    INNER JOIN bd_agent ba ON ba.agent_uuid = ol.inst_recovery_agent_uuid
                                WHERE ol.inst_recovery_agent_uuid IN ($in) ";
        $data = $data ?: $this->dbSelect($instRecoveryTaskSql);

        // 客户端存在任务、作为恢复主机、作为双机镜像、作为接管主机、作为回切主机，不能删除
        if ($data) {
            $detailOne = sprintf(xphp_get_lang('WEB_AGENT_DELETE_CHECK_TASK'), $data[0]['ip'], $data[0]['task_name']);
            return $this->sendResult($detailOne, false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 修改客户端
     * @param array $params 参数
     * @return array
     */
    public function updateClient(array $params): array
    {
        $opcodeName = 'NODE_AGENT_OP_MODIFY';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        // 验证客户端是否存在
        $agentSql = "SELECT hostname, ip, agent_uuid, net_model, port, agent_name, agent_type FROM bd_agent WHERE agent_uuid = ?";
        $agentInfo = $this->dbSelect($agentSql, [$params['agents_uuid']]);
        if (!$agentInfo || !is_array($agentInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 权限验证
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        if ($agentInfo[0]['agent_type'] == $allAgentType['APPLIANCE']) {
            $this->checkAuthBySourceUuid($params['agents_uuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['APPLICE']);
        } else {
            $this->checkAuthBySourceUuid($params['agents_uuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);
        }

        if ($agentInfo[0]['agent_type'] == xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE']) {
            $detailOne = sprintf(
                xphp_get_lang('WEB_AGENT_UPDATE_PROXY_DETAIL'),
                $agentInfo[0]['hostname'] . '(' . $agentInfo[0]['ip'] . ')'
            );
        } else {
            $detailOne = sprintf(
                xphp_get_lang('WEB_AGENT_UPDATE_DETAIL'),
                $agentInfo[0]['hostname'] . '(' . $agentInfo[0]['ip'] . ')'
            );
        }

        // 格式化数据
        $params['net_model'] = intval($params['net_model'] ?? $agentInfo[0]['net_model']);
        $params['port'] = intval($params['port'] ?? $agentInfo[0]['port']);
        $params['nickname'] = isset($params['nickname']) ? v1_remove_escape($params['nickname']) : $agentInfo[0]['agent_name'];
        $params['ip'] = $params['ip'] ?? $agentInfo[0]['ip'];
        if ($params['net_model'] == xphp_get_config('client', 'AGENT_NET_MODEL', 'resources')['client_to_server']) {
            $params['auto_change_network_flag'] = false;
        } else {
            $params['auto_change_network_flag'] = $params['auto_change_network_flag'] ?? false;
        }


        $updateResult = $this->service()->updateClientService($params);
        if (!$updateResult['result']) {
            if ($agentInfo[0]['agent_type'] == xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE']) {
                $this->muOpResult(false, $operate, xphp_get_lang('WEB_AGENT_UPDATE_PROXY_ERROR'), 0, $updateResult['errorCode']);
                return $this->sendResult(xphp_get_lang('WEB_AGENT_UPDATE_PROXY_ERROR'), false, 0);
            } else {
                $this->muOpResult(false, $operate, xphp_get_lang('WEB_AGENT_UPDATE_ERROR'), 0, $updateResult['errorCode']);
                return $this->sendResult(xphp_get_lang('WEB_AGENT_UPDATE_ERROR'), false, 0);
            }
        }
        if ($agentInfo[0]['agent_type'] == xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE']) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_UPDATE_PROXY_SUCCESS'), true, 200, [$detailOne]);
        } else {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_UPDATE_SUCCESS'), true, 200, [$detailOne]);
        }
    }

    /**
     * 添加客户端
     * @param int    $addMode    添加模式[1手动添加 2远程部署]
     * @param ?array $manual     手动添加数据
     * @param ?array $deployment 远程部署数据
     * @return array
     */
    public function addClient(int $addMode, ?array $manual, ?array $deployment): array
    {
        $opcodeName = 'NODE_AGENT_OP_ADD';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        // 查询当前登录的用户信息
        $user = xphp_get_user_info();
        $userSql = "SELECT user_name AS username, password, user_uuid FROM bd_user
                        WHERE user_uuid = '{$user['userUuid']}'";
        $userInfo = $this->dbSelect($userSql);
        // 查询客户端默认分组的uuid
        $agentGroupType = xphp_get_config('client', 'AGENT_GROUP_TYPE', 'resources');
        $agentGroupSql = "SELECT group_uuid FROM bd_agent_group WHERE group_type = {$agentGroupType['system']} ";
        $agentGroupInfo = $this->dbSelect($agentGroupSql);

        // 构建添加的客户端信息
        $clientAddMode = xphp_get_config('client', 'AGENT_ADD_MODE', 'resources');
        $clientAddType = xphp_get_config('client', 'AGENT_ADD_TYPE', 'resources');
        $addType = $clientAddType['single'];
        $serverIp = '';
        $serverPort = 0;
        if ($addMode == $clientAddMode['manual']) {
            $agentInfo = $this->buildManualClient($manual, $userInfo[0], $agentGroupInfo[0]);
        } elseif ($addMode == $clientAddMode['deployment']) {
            $agentInfo = $this->buildDeploymentClient($deployment, $userInfo[0], $agentGroupInfo[0]);
            $addType = (int)$deployment['add_type'];
            $serverIp = $deployment['server_ip'];
            $serverPort = (int)$deployment['server_port'];
        } else {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_ADD_ERROR_NONSUPPORT'), false, 0);
        }

        // 验证客户端是否存在
        $agentType = $agentInfo[0]['agent_type'];
        $checkAddClient = $this->validateAddClient($agentInfo, $addType, $agentType);
        if ($checkAddClient['success'] === false) {
            return $checkAddClient;
        }

        // 添加客户端
        $newAgentInfo = $checkAddClient['data'];
        $detail = array_map(function ($agent) use ($newAgentInfo, $agentType) {
            foreach ($newAgentInfo as $newAgent) {
                if ($newAgent['ip'] == $agent['ip']) {
                    if ($agentType == xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE']) {
                        return sprintf(xphp_get_lang('WEB_AGENT_ADD_PROXY_DETAIL'), $agent['ip']);
                    } else {
                        return sprintf(xphp_get_lang('WEB_AGENT_ADD_DETAIL'), $agent['ip']);
                    }
                }
            }
            if ($agentType == xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE']) {
                return sprintf(xphp_get_lang('WEB_AGENT_ADD_PROXY_EXISTS_DETAIL'), $agent['ip']);
            } else {
                return sprintf(xphp_get_lang('WEB_AGENT_ADD_EXISTS_DETAIL'), $agent['ip']);
            }
        }, $agentInfo);
        if (!$newAgentInfo) {
            // 表示所有客户端都已经存在了，直接返回成功信息
            return $this->sendResult(xphp_get_lang('WEB_AGENT_ADD_SUCCESS_BUT_EXISTS'), true, 200, $detail);
        }

        $authCode = $this->getLoginUserAuthCode();
        // 调用后台接口, 添加客户端
        $addResult = $this->service()->addClientService($newAgentInfo, $serverPort, $addMode, $serverIp, $authCode);
        if ($addResult['result']) {
            // 添加成功
            $lang = 'WEB_AGENT_ADD_SUCCESS_TYPE1';
            if ($agentType == xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE']) {
                $lang = 'WEB_AGENT_ADD_SUCCESS_PROXY_TYPE1';
            }
            if ($addMode == $clientAddMode['deployment']) {
                $lang = 'WEB_AGENT_ADD_SUCCESS_TYPE2';
            }
            // 如果存在已添加过的客户端, 要更换提示语
            $message = count($agentInfo) == count($newAgentInfo) ?
                xphp_get_lang($lang) : xphp_get_lang('WEB_AGENT_ADD_SUCCESS_BUT_EXISTS');
            return $this->sendResult($message, true, 200, $detail);
        }
        if ($agentType == xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE']) {
            $this->muOpResult(false, $operate, xphp_get_lang('WEB_AGENT_ADD_PROXY_ERROR'), 0, $addResult['errorCode']);
        } else {
            $this->muOpResult(false, $operate, xphp_get_lang('WEB_AGENT_ADD_ERROR'), 0, $addResult['errorCode']);
        }
        $lang = 'WEB_AGENT_ADD_ERROR_TYPE1';
        if ($agentType == xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE']) {
            $lang = 'WEB_AGENT_ADD_ERROR_PROXY_TYPE1';
        }
        if ($addMode == $clientAddMode['deployment']) {
            $lang = 'WEB_AGENT_ADD_ERROR_TYPE2';
        }
        return $this->sendResult(xphp_get_lang($lang), false, 0);
    }

    /**
     * 获取当前登录用户的授权码
     * @return string
     */
    private function getLoginUserAuthCode(): string
    {
        $loginUser = xphp_get_user_info();
        $sql = "SELECT auth_code FROM bd_user WHERE user_uuid = ? ";
        $userAuthCode = $this->dbSelect($sql, [$loginUser['userUuid']]);
        return strval($userAuthCode[0]['auth_code']);
    }

    /**
     * 构建手动添加参数
     * @param array $manual         手动添加参数
     * @param array $user           用户数据
     * @param array $agentGroupInfo 客户端分组信息
     * @return array
     */
    private function buildManualClient(array $manual, array $user, array $agentGroupInfo): array
    {
        return [[
            'ip' => $manual['ip'],
            'os_type' => null,
            'port' => $manual['port'],
            'net_model' => xphp_get_config('client', 'AGENT_NET_MODEL', 'resources')['server_to_client'],
            'agent_username' => '',
            'agent_password' => v1_pt_pass_encrypt(base64_decode('')),
            'server_username' => $user['username'],
            'server_password' => $user['password'],
            'group_uuid' => $agentGroupInfo['group_uuid'],
            'user_uuid' => $user['user_uuid'],
            'plugin_path' => '',
            'nickname' => $manual['alias'],
            'agent_type' => $manual['agent_type'],
            'client_transport_port' => 23101,
            'auto_change_network_flag' => v1_parse_bool_to_flag($manual['auto_change_network_flag']),
            'applied_vcenters' => json_encode($manual['applied_vcenters']),
        ]];
    }

    /**
     * 构建远程部署参数
     * @param array $deployment     远程部署参数
     * @param array $user           用户数据
     * @param array $agentGroupInfo 客户端分组信息
     * @return array
     */
    private function buildDeploymentClient(array $deployment, array $user, array $agentGroupInfo): array
    {
        $clientAddType = xphp_get_config('client', 'AGENT_ADD_TYPE', 'resources');
        if ($deployment['add_type'] == $clientAddType['single']) {
            $data = [$deployment['single']];
        } elseif ($deployment['add_type'] == $clientAddType['multiple']) {
            $data = $deployment['multiple'];
        } else {
            return [];
        }

        $clientList = [];
        $agentOsType = xphp_get_config('client', 'AGENT_OS_TYPE', 'resources');
        $clientPackages = $this->getClientPackages();
        foreach ($data as $agent) {
            if (isset($clientList[$agent['ip']])) {
                // 表示多个添加里面有重复的IP
                continue;
            }
            $osType = $this->getOsType($agent['os_type']);
            $pluginPath = '';
            if (is_array($clientPackages[$osType])) {
                $pluginPath = $clientPackages[$osType]['fileName'];
                $pluginPath = dirname(API_PATH, 2) . $pluginPath;
            }
            $clientList[$agent['ip']] = [
                'ip' => $agent['ip'],
                'os_type' => $agentOsType[$osType],
                'port' => $agent['port'],
                'net_model' => (int)$agent['net_model'],
                'agent_username' => $agent['username'],
                'agent_password' => v1_pt_pass_encrypt(base64_decode($agent['password'])),
                'server_username' => $user['username'],
                'server_password' => $user['password'],
                'group_uuid' => $agentGroupInfo['group_uuid'],
                'user_uuid' => $user['user_uuid'],
                'plugin_path' => $pluginPath,
                'nickname' => $agent['alias'],
                'agent_type' => $agent['agent_type'],
                'client_transport_port' => $agent['client_transport_port'],
                'auto_change_network_flag' => v1_parse_bool_to_flag($agent['auto_change_network_flag']),
                'applied_vcenters' => json_encode([]), // 补全参数
            ];
        }
        return array_values($clientList);
    }

    /**
     * 获取操作系统类别
     * @param string $osType 操作系统
     * @return string
     */
    private function getOsType(string $osType): string
    {
        $clientVendor = xphp_get_config('vendor', 'CLIENT_VENDOR');
        foreach ($clientVendor as $eachVendor) {
            if ($osType == $eachVendor['text']) {
                return $eachVendor['value'];
            }
        }
        return 'WINDOWS';
    }

    /**
     * 检查客户端是否已经添加,单个添加直接返回错误提示，批量添加不再添加已存在的客户端
     * @param array $agentInfo 客户端列表
     * @param int   $addType   客户端添加类型【1单个添加 2多个添加】
     * @param int   $agentType 客户端类型
     * @return array
     */
    private function validateAddClient(array $agentInfo, int $addType, int $agentType): array
    {
        if (!$agentInfo) {
            // 没有客户端数据
            return $this->sendResult(xphp_get_lang('WEB_AGENT_ADD_NO_AGENT'), false, 0);
        }

        // 判断客户端
        $clientAddType = xphp_get_config('client', 'AGENT_ADD_TYPE', 'resources');
        $ipList = array_column($agentInfo, 'ip');
        $ipStr = "'" . implode("','", $ipList) . "'";
        $agentTypeList = [$agentType];
        if ($agentType == xphp_get_config('client', 'AGENT_TYPE','resources')['NORMAL']) {  // #24011: 添加客户端追加内存操作系统判断
            $agentTypeList[] = xphp_get_config('client', 'AGENT_TYPE','resources')['MEMORY_OS'];
        }
        $agentTypes = "'" . implode("','", $agentTypeList) . "'";
        $sql = "SELECT agent_uuid, ip FROM bd_agent WHERE ip IN ($ipStr) AND agent_type IN ($agentTypes) ";
        $data = $this->dbSelect($sql);
        if ($addType == $clientAddType['single'] && $data) {
            // 传输代理IP已添加，返回提示
            if (xphp_get_config('client', 'AGENT_TYPE', 'resources')['APPLIANCE'] == $agentType) {
                return $this->sendResult(xphp_get_lang('WEB_AGENT_ADD_APPLIANCE_EXISTS'), false, 0);
            }
            // 单个添加，如果存在就报错
            return $this->sendResult(xphp_get_lang('WEB_AGENT_ADD_AGENT_EXISTS'), false, 0);
        }

        // 多个添加只需添加不存在的客户端
        $newAgentInfo = [];
        foreach ($agentInfo as $agent) {
            $isExists = false;
            foreach ($data as $row) {
                if ($row['ip'] == $agent['ip']) {
                    $isExists = true;
                    break;
                }
            }
            if (!$isExists) {
                $newAgentInfo[] = $agent;
            }
        }
        return $this->sendResult('', true, 200, $newAgentInfo);
    }

    /**
     * 客户端授权/取消授权
     * @param bool  $authFlag  授权类型【true授权 false取消授权】
     * @param array $agentList 客户端授权列表
     * @return array
     */
    public function authClient(bool $authFlag, array $agentList): array
    {
        if ($authFlag) {
            // 授权
            $opName = 'PT_LICENSE_OP_ADD_AGENT_LICENSE';
        } else {
            // 取消授权
            $opName = 'PT_LICENSE_OP_MODIFY_AGENT_LICENSE';
        }
        $operate = $this->pfOpcode->getOpcodeDes($opName);
        // 验证信息
        $validateResult = $this->validateAuthClient($authFlag, $agentList);
        if ($validateResult['success'] === false) {
            return $validateResult;
        }
        $agentInfo = $validateResult['data'];

        // 获取登录用户的uuid
        $user = xphp_get_user_info();

        // 批量授权
        foreach ($agentList as $agent) {
            $agent['new_module'] = $agent['auth_module'];
            // 授权/取消授权文件处理
            $agent['auth_module']['file'] = $this->setAuthModule($authFlag, $agent, 'file');
            // 授权/取消授权数据库处理
            $agent['auth_module']['database'] = $this->setAuthModule($authFlag, $agent, 'database');
            // 授权/取消授权操作系统处理
            $agent['auth_module']['os'] = $this->setAuthModule($authFlag, $agent, 'os');
            // 授权/取消授权cdp处理
            $agent['auth_module']['cdp'] = $this->setAuthModule($authFlag, $agent, 'cdp');

            // 判断当前授权与之前的授权是否一致, 一致就不需要授权了
            if ($this->compareAuthModule($agent['auth_module'], $agent['old_module'])) {
                continue;
            }
            $agent['module'] = $this->getFullAuthModule($agent['auth_module']);

            // 添加资源与用户关联
            $resourcesList = [[
                'resourceuuid' => $agent['agent_uuid'],
                'vmuuid' => '',
                'vcenteruuid' => '',
                'resourceType' => xphp_get_config('resource', 'RESOURCE_TYPE')['FILE_HOST'],
            ]];
            (new \app\v1\user\v0\logic\User())->pAddUserResource($user['userUuid'], $resourcesList);

            // 授权
            $authResult = $this->service()->authClientServer($authFlag, $agent, $user['userUuid']);
            if (!$authResult['result']) {
                // 一个授权失败, 就不需要授权其他客户端了, 返回错误信息
                $this->muOpResult(false, $operate, $authResult['msg'], 'warning', $authResult['errorCode']);
                $message = xphp_get_lang($authFlag ? 'WEB_AGENT_AUTH_ERROR' : 'WEB_AGENT_CANCEL_AUTH_ERROR');
                return $this->sendResult($message, false, 0);
            }
        }

        // 授权/取消授权成功
        $message = xphp_get_lang($authFlag ? 'WEB_AGENT_AUTH_SUCCESS' : 'WEB_AGENT_CANCEL_AUTH_SUCCESS');
        $detail = array_map(function ($agent) use ($authFlag) {
            $format = xphp_get_lang($authFlag ? 'WEB_AGENT_AUTH_DETAIL' : 'WEB_AGENT_CANCEL_AUTH_DETAIL');
            return sprintf($format, $agent['hostname'] . '(' . $agent['ip'] . ')');
        }, $agentInfo);
        return $this->sendResult($message, true, 200, $detail);
    }

    /**
     * 验证授权客户端信息, 并返回客户端信息
     * @param bool  $authFlag  授权类型【true授权 false取消授权】
     * @param array $agentList 客户端授权列表
     * @return array
     */
    private function validateAuthClient(bool $authFlag, array $agentList): array
    {
        // 验证客户端是否都存在
        $agentUuids = array_column($agentList, 'agent_uuid');
        $in = "'" . implode("', '", $agentUuids) . "'";
        $sql = "SELECT ip, hostname FROM bd_agent WHERE agent_uuid in ($in)";
        $agentInfo = $this->dbSelect($sql);
        if (!is_array($agentInfo) || count($agentInfo) != count($agentUuids)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 授权验证
        if ($authFlag) {
            // 检查授权是否足够
            if (($checkAuth = $this->checkAuthEnough($agentList)) !== true) {
                return $this->sendResult($checkAuth, false, 0);
            }
            // 检查系统授权是否正常，不正常不允许执行授权操作
            $systemLicenseInfo = json_decode((new \app\v1\system\v0\logic\Index())->getSystemLisenceInfo(), true);
            if (!$systemLicenseInfo['status']) {
                return $this->sendResult($systemLicenseInfo['statusDes'], false, 0);
            }
        }
        return $this->sendResult('', true, 200, $agentInfo);
    }

    /**
     * 检查客户端授权是否足够
     * @param array $agentList 授权的客户端列表
     * @return bool|string
     */
    private function checkAuthEnough(array $agentList)
    {
        // 获取当前系统已授权信息
        $authInfo = $this->getClientAuthInfo();
        // 判断授权模式，如果是容量授权不需要检测个数
        if ($authInfo['license_type'] ==  xphp_get_config('auth', 'LISENCE_INFO')['type']['storage']) {
            return true;
        }

        // 计算本次授权的数量
        $authCount = [
            'file' => 0,
            'database' => 0,
            'os' => 0,
            'cdp' => 0,
        ];
        $authModule = [];
        foreach ($agentList as $agent) {
            $authModule = $agent['auth_module'];
            $oldModule = $agent['old_module'];

            // 文件授权数量
            if ($authModule['file'] && !$oldModule['file']) {
                // 之前没有授权, 现在要授权, 因此数量要加一, 下同
                $authCount['file']++;
            }

            // 数据库授权数量
            if ($authModule['database'] && !$oldModule['database']) {
                $authCount['database']++;
            }

            // 操作系统授权数量
            if ($authModule['os'] && !$oldModule['os']) {
                $authCount['os']++;
            }

            // cdp授权数量
            if ($authModule['cdp'] && !$oldModule['cdp']) {
                $authCount['cdp']++;
            }
        }

        // 是否足够授权文件
        if ($authModule['file'] && $authCount['file'] > $authInfo['file']['valid']) {
            return xphp_get_lang('WEB_CLIENT_FILE_AUTH_NOT_ENOUGH');
        }

        // 是否足够授权数据库
        if ($authModule['database'] && $authCount['database'] > $authInfo['database']['valid']) {
            return xphp_get_lang('WEB_CLIENT_DB_AUTH_NOT_ENOUGH');
        }

        // 是否足够授权操作系统
        if ($authModule['os'] && $authCount['os'] > $authInfo['os']['valid']) {
            return xphp_get_lang('WEB_CLIENT_OS_AUTH_NOT_ENOUGH');
        }

        // 是否足够授权cdp
        if ($authModule['cdp'] && $authCount['cdp'] > $authInfo['cdp']['valid']) {
            return xphp_get_lang('WEB_CLIENT_CDP_AUTH_NOT_ENOUGH');
        }
        return true;
    }

    /**
     * 设置授权
     * @param bool   $authFlag 授权方式【true授权 false取消授权】
     * @param array  $agent    当前授权模块
     * @param string $authKey  需要授权的键
     * @return bool
     */
    private function setAuthModule(bool $authFlag, array $agent, string $authKey): bool
    {
        // 授权/取消授权文件处理
        if ($agent['auth_module'][$authKey]) {
            // 这里通过if else分开赋值, 下面将合并
            if (!$authFlag) {
                // 取消授权
                return false;
            } else {
                // 授权
                return true;
            }
        }
        // 不做授权/取消授权处理, 使用之前的模块授权
        return (bool)$agent['old_module'][$authFlag];
    }

    /**
     * 比较新旧模块
     * @param array $newModule 新授权模块
     * @param array $oldModule 旧授权模块
     * @return bool
     */
    private function compareAuthModule(array $newModule, array $oldModule): bool
    {
        if ($newModule['file'] != $oldModule['file']) {
            return false;
        }

        if ($newModule['database'] != $oldModule['database']) {
            return false;
        }

        if ($newModule['os'] != $oldModule['os']) {
            return false;
        }

        if ($newModule['cdp'] != $oldModule['cdp']) {
            return false;
        }
        return true;
    }

    /**
     * 获取全部授权模块
     * @param array $authModule 授权的模块
     * @return array
     */
    private function getFullAuthModule(array $authModule): array
    {
        $fullAuthModule = [
            'file' => false, 'vm' => false, 'mysql' => false, 'oracle' => false,
            'sqlserver' => false, 'dm' => false, 'os' => false, 'cdp' => false,
            'desktop' => false, 'database' => false,
        ];
        foreach ($fullAuthModule as $key => $value) {
            if ($authModule[$key]) {
                $fullAuthModule[$key] = $authModule[$key];
            }
        }
        return $fullAuthModule;
    }

    /**
     * 客户端升级
     * @param array $upgradeData 参数
     * @return array
     */
    public function upgradeClient(array $upgradeData): array
    {
        $agentUuidList = array_values(array_unique(array_column($upgradeData, 'agent_uuid')));
        $agentUuids = "'" . implode("' ,'", $agentUuidList) . "'";
        // 判断客户端是否存在
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $sql = "SELECT agent_uuid, ip, hostname, agent_name
                FROM bd_agent
                WHERE agent_uuid IN ($agentUuids) AND agent_type != ? ";
        $agentList = $this->dbSelect($sql, [$allAgentType['APPLIANCE']]);
        if (!is_array($agentList) || count($agentList) != count($agentUuidList)) {
            return $this->sendResult(xphp_get_lang('WEB_ERROR_BD_AGENT_NOT_EXIST_ERROR'), false, 0);
        }

        // 权限验证
        $this->checkAuthBySourceUuid(implode(',', $agentUuidList), xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);

        $opcodeName = 'NODE_AGENT_OP_UPGRADE';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $upgradeResult = $this->service()->upgradeClientService($upgradeData);
        if (!$upgradeResult['result']) {
            $this->muOpResult(false, $operate, $upgradeResult['msg'], 'warning', $upgradeResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_UPGRADE_ERROR'), false, 0);
        }
        $detail = array_map(function ($row) {
            return sprintf(xphp_get_lang('WEB_AGENT_UPGRADE_DETAIL'), $row['hostname'] . '(' . $row['ip'] . ')');
        }, $agentList);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_UPGRADE_SUCCESS'), true, 200, $detail);
    }

    /**
     * 获取客户端插件
     * @return array
     */
    private function getClientPackages(): array
    {
        $clientVendor = xphp_get_config('vendor', 'CLIENT_VENDOR');
        $agentHandler = new Agent();
        $packageList = $agentHandler->getAllPackagesInfo();
        $ret = [];
        foreach ($clientVendor as $item) {
            $ret[$item['value']] = $packageList[$item['value']];
        }
        return $ret;
    }

    /**
     * 判断代理是否有正在运行的任务
     * @param string $agentUuid 客户端uuid
     * @return boolean
     */
    private function checkAgentIsRunningTask(string $agentUuid): bool
    {
        $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');
        // 查找任务
        $sql = "SELECT bt.task_status FROM bd_task_agent_list btal
                    LEFT JOIN bd_task bt ON btal.task_uuid = bt.task_uuid
                WHERE btal.agent_uuid = ? ";
        $data = $this->dbSelect($sql, [$agentUuid]);
        // 任务处于运行、临界值将不能升级
        if (!$data || !is_array($data)) {
            return true;
        }
        foreach ($data as $item) {
            if (
                $item['task_status'] == $allTaskStatus['RUNNING'] ||
                $item['task_status'] == $allTaskStatus['STOPPING'] ||
                $item['task_status'] == $allTaskStatus['STARTING']
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取客户端升级列表
     * @param array $agentUuidList 客户端uuid列表
     * @return array
     */
    public function getUpgradeClientList(array $agentUuidList): array
    {
        $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $sql = "SELECT ip, os_version, online_flag, agent_uuid, plugin_version,
                    agent_type, agent_package_type
                FROM bd_agent
                WHERE agent_uuid IN ($agentUuids) AND agent_type != ? ";
        $clientList = $this->dbSelect($sql, [$allAgentType['APPLIANCE']]);
        $clientPackages = $this->getClientPackages();
        $allAgentPackageTypeMap = xphp_get_desc('Agent', 'AGENT_PACKAGE_TYPE_MAP');
        if (!is_array($clientList) || !$clientList) {
            $clientList = [];
        }
        $ret = [
            'total' => count($clientList),
            'rows' => array_map(function ($row) use ($clientPackages, $allAgentPackageTypeMap) {
                $upgradeFlag = true;
                $packagePath = '';
                $packageShowName = '';
                $downloadUrl = '';
                if (is_array($clientPackages[$allAgentPackageTypeMap[$row['agent_package_type']]])) {
                    $packagePath = $clientPackages[$allAgentPackageTypeMap[$row['agent_package_type']]]['fileName'];
                    $packageShowName = $clientPackages[$allAgentPackageTypeMap[$row['agent_package_type']]]['showName'];
                }
                $packageVersion = '';
                if ($packagePath) {
                    if (preg_match('/.*backup-agent-(.*)-AGENT.*/', $packagePath, $matches)) {
                        $packageVersion = $matches[1];
                    } elseif (preg_match('/.*windows\.(.*)\.exe/', $packagePath, $matches)) {
                        $packageVersion = $matches[1];
                    }
                } else {
                    $upgradeFlag = false;
                }
                // 升级版本比较
                if ($packageVersion <= $row['plugin_version']) {
                    $upgradeFlag = false;
                }
                // 在线状态判断
                if (!v1_parse_flag_to_bool($row['online_flag'])) {
                    $upgradeFlag = false;
                }
                // 任务判断
                if (!$this->checkAgentIsRunningTask($row['agent_uuid'])) {
                    $upgradeFlag = false;
                }

                if ($upgradeFlag) {
                    $systemHandler = new \app\v1\system\v0\logic\Index();
                    $nodeHandler = new Node();
                    $filepath = dirname(API_PATH, 2) . $packagePath;
                    $downloadUrl = $systemHandler->groupUnifyDownloadUrl(
                        $nodeHandler->getLocalNodeUUID(),
                        $filepath,
                        false,
                        $packageShowName
                    );
                }

                return [
                    'agent_uuid' => $row['agent_uuid'],
                    'agent_ip' => $row['ip'],
                    'os_version' => $row['os_version'],
                    'agent_version' => $row['plugin_version'],
                    'agent_newest_version' => $packageVersion,
                    'upgrade_flag' => $upgradeFlag,
                    'online_flag' => v1_parse_flag_to_bool($row['online_flag']),
                    'package_path' => $packagePath,
                    'agent_package_type' => $row['agent_package_type'],
                    'download_url' => $downloadUrl,
                ];
            }, $clientList),
        ];
        return $this->sendResult('', true, 200, $ret);
    }

    /**
     * 获取客户端日志列表
     * @param string $agentsUuid 客户端uuid
     * @return array
     */
    public function getClientLogList(string $agentsUuid): array
    {
        if (!$this->dbSelect("SELECT * FROM bd_agent WHERE agent_uuid = ? ", [$agentsUuid])) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        $opcodeName = 'NODE_AGENT_OP_LIST_AGENT_LOG';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $logResult = $this->service()->getClientLogService($agentsUuid);
        if (!$logResult['result']) {
            $this->muOpResult(false, $operate, $logResult['msg'], 'warning', $logResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_LOG_GET_ERROR'), false, 0);
        }

        $logList = [];
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        foreach ($logResult['msg'] as $logItem) {
            $moduleType = (int) $logItem['module_type'];
            if (!isset($logItem['log_info']) || !is_array($logItem['log_info']) || !$logItem['log_info']) {
                continue;
            }

            foreach ($logItem['log_info'] as $logInfo) {
                $logList[] = [
                    'log_name' => $logInfo['name'] ?: $nullSpace,
                    'log_module' => $moduleType,
                    'sub_log_module' => 0,
                    'sub_module_name' => $nullSpace,
                    'log_time' => $logInfo['time'] ?: $nullSpace,
                    'log_path' => $logInfo['path'] ?: $nullSpace,
                ];
            }
            if (isset($logItem['sub_module']) && is_array($logItem['sub_module']) && $logItem['sub_module']) {
                foreach ($logItem['sub_module'] as $subLogItem) {
                    $subModuleType = (int) $subLogItem['type'];
                    if (
                        !isset($subLogItem['log_info']) ||
                        !is_array($subLogItem['log_info']) || !$subLogItem['log_info']
                    ) {
                        continue;
                    }

                    foreach ($subLogItem['log_info'] as $subLogInfo) {
                        $logList[] = [
                            'log_name' => $subLogInfo['name'] ?: $nullSpace,
                            'log_module' => $moduleType,
                            'sub_log_module' => $subModuleType,
                            'sub_module_name' => $subLogItem['name'] ?: $nullSpace,
                            'log_time' => $subLogInfo['time'] ?: $nullSpace,
                            'log_path' => $subLogInfo['path'] ?: $nullSpace,
                        ];
                    }
                }
            }
        }
        return $this->sendResult('', true, 200, [
            'rows' => $logList,
            'total' => count($logList),
        ]);
    }

    /**
     * 下载客户端日志
     * @param string $agentsUuid  客户端uuid
     * @param array  $logPathList 日志路径列表
     * @return array
     */
    public function downloadClientLog(string $agentsUuid, array $logPathList): array
    {
        // 验证客户端是否存在
        $agentSql = "SELECT hostname, ip, agent_uuid, net_model, port, agent_name, agent_type FROM bd_agent WHERE agent_uuid = ?";
        $agentInfo = $this->dbSelect($agentSql, [$agentsUuid]);
        if (!$agentInfo || !is_array($agentInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 权限验证
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        if ($agentInfo[0]['agent_type'] == $allAgentType['APPLIANCE']) {
            $this->checkAuthBySourceUuid($agentsUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['APPLICE']);
        } else {
            $this->checkAuthBySourceUuid($agentsUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);
        }

        $opcodeName = 'NODE_AGENT_OP_DOWNLOAD_AGENT_LOG';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $logResult = $this->service()->downloadClientLogService($agentsUuid, $logPathList);

        if (!$logResult['result']) {
            $this->muOpResult(false, $operate, $logResult['msg'], 'warning', $logResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_NODE_AGENT_OP_DOWNLOAD_AGENT_LOG'), false, 0);
        }

        $nodeHandler = new Node();
        $nodeUuid = $nodeHandler->getLocalNodeUUID();
        $systemHandler = new \app\v1\system\v0\logic\Index();
        $url = $systemHandler->groupUnifyDownloadUrl($nodeUuid, trim($logResult['msg']['file_path']), true);
        return $this->sendResult('', true, 200, ['file_path' => $url]);
    }

    /**
     * 获取租户授权信息
     * @param string $module     模块
     * @param array  $authConfig 授权配置
     * @param array  $agentMap   客户都信息
     * @return int[]
     */
    private function getTenantLicenseInfo(string $module, array $authConfig, array $agentMap = [])
    {
        $moduleInfo = [
            'total' => 0,
            'used' => 0,
            'valid' => 0,
        ];
        $modules = ['file', 'db', 'os'];
        if (!in_array($module, $modules)) {
            return $moduleInfo;
        }
        $moduleInfo['total'] = $authConfig[$module . '_num'];
        foreach ($agentMap as $authModule) {
            switch ($module) {
                case 'file':
                    if (isset($authModule['file']) && $authModule['file']) {
                        $moduleInfo['used']++;
                    }
                    break;
                case 'db':
                    if (isset($authModule['database']) && $authModule['database']) {
                        $moduleInfo['used']++;
                    }
                    break;
                case 'os':
                    if (isset($authModule['os']) && $authModule['os']) {
                        $moduleInfo['used']++;
                    }
                    break;
            }
        }
        $moduleInfo['valid'] = $moduleInfo['total'] - $moduleInfo['used'];
        return $moduleInfo;
    }

    /**
     * 获取客户端的授权信息
     * @return array
     */
    public function getClientAuthInfo(): array
    {
        $systemHandler = new \app\v1\system\v0\logic\Index();
        $fileInfo = $systemHandler->getOneModuleLisenceInfo('file');
        $dbInfo = $systemHandler->getOneModuleLisenceInfo('database');
        $osInfo = $systemHandler->getOneModuleLisenceInfo('os');
        $cdpInfo = $systemHandler->getOneModuleLisenceInfo('cdp');
        $licenseType = $systemHandler->getSystemLicenseType()['licensetype'];
        $loginUser = xphp_get_user_info();
        if ($loginUser['tenantuuid']) {  // 租户授权读取租户的授权信息
            $fileInfo = $this->getTenantLicenseInfo('', []);
            $dbInfo = $this->getTenantLicenseInfo('', []);
            $osInfo = $this->getTenantLicenseInfo('', []);
            $cdpInfo = $this->getTenantLicenseInfo('', []);
            $tenantUserData = $this->dbSelect("SELECT user_uuid FROM mt_user_tenant WHERE tenant_uuid = ? ", [$loginUser['tenantuuid']]);
            $tenantUserUuidList = array_column($tenantUserData, 'user_uuid');

            $agentUuidList = $this->getClientUuids('', 1, [10], $tenantUserUuidList);
            $agentUuidList = array_values(array_unique($agentUuidList));
            $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
            $sql = "SELECT authorization_module, agent_uuid FROM bd_agent WHERE agent_uuid IN ($agentUuids) ";
            $agentData = $this->dbSelect($sql);
            $agentMap = [];
            if (is_array($agentData) && $agentData) {
                foreach ($agentData as $item) {
                    try {
                        $authModule = json_decode($item['authorization_module'], true);
                        if (!is_array($authModule)) {
                            continue;
                        }
                        $agentMap[$item['agent_uuid']] = $authModule;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            $sql = "SELECT config FROM bd_tenant WHERE tenant_uuid = ? ";
            $tenantInfo = $this->dbSelect($sql, [$loginUser['tenantuuid']]);
            if (!is_array($tenantInfo) || !$tenantInfo) {
                $licenseType = 2;
            } else {
                $config = json_decode($tenantInfo[0]['config'], true);
                if (2 == $config['auth_way']) { // 数量授权
                    $fileInfo = $this->getTenantLicenseInfo('file', $config, $agentMap);
                    $dbInfo = $this->getTenantLicenseInfo('db', $config, $agentMap);
                    $osInfo = $this->getTenantLicenseInfo('os', $config, $agentMap);
                    $licenseType = 2;
                } else {
                    $licenseType = 3;
                }
            }
        }
        return [
            'file' => $fileInfo,
            'database' => $dbInfo,
            'os' => $osInfo,
            'cdp' => $cdpInfo,
            'license_type' => $licenseType,
        ];
    }

    /**
     * 获取集群应用列表
     * @param string $clusterUuid 集群uuid
     * @return array
     */
    private function getClusterAppList(string $clusterUuid): array
    {
        $clusterAppList = [];
        if (!$clusterUuid) {
            return $clusterAppList;
        }
        $sql = "SELECT ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.online_flag,
                    baa.app_uuid, baa.app_name, baa.app_type, baa.app_auth_type,
                    baa.cluster_uuid, baa.cluster_name, baa.cluster_service_ip,
                    baa.app_detail
                FROM bd_agent_app baa
                    INNER JOIN bd_agent ba ON baa.agent_uuid=ba.agent_uuid
                WHERE baa.cluster_uuid = ? ";
        $clusterAppData = $this->dbSelect($sql, [$clusterUuid]);
        foreach ($clusterAppData as $clusterAppInfo) {
            $clusterAppList[] = [
                'agent_uuid' => $clusterAppInfo['agent_uuid'],
                'agent_name' => $clusterAppInfo['agent_name'],
                'hostname' => $clusterAppInfo['hostname'],
                'ip' => $clusterAppInfo['ip'],
                'online_flag' => v1_parse_flag_to_bool($clusterAppInfo['online_flag']),
                'app_uuid' => $clusterAppInfo['app_uuid'],
                'app_name' => $clusterAppInfo['app_name'],
                'app_type' => $clusterAppInfo['app_type'],
                'app_auth_type' => $clusterAppInfo['app_auth_type'],
                'cluster_uuid' => $clusterAppInfo['cluster_uuid'],
                'cluster_name' => $clusterAppInfo['cluster_name'],
                'cluster_service_ip' => $clusterAppInfo['cluster_service_ip'],
                'app_detail' => $clusterAppInfo['app_detail'],
            ];
        }
        return $clusterAppList;
    }

    /**
     * 获取客户端应用列表
     * @param array $params 全部请求参数
     * @return array
     */
    public function getClientAppList(array $params): array
    {
        $agentsUuid = $params['agents_uuid'];
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sort = $params['sort'] ?? 'update_time';
        $order = $params['order'] ?? 'DESC';
        // 判断客户端是否存在
        $agentSql = "SELECT agent_uuid, agent_name, hostname, ip, os_type FROM bd_agent WHERE agent_uuid = ?";
        $agentInfo = $this->dbSelect($agentSql, [$agentsUuid]);
        if (!$agentInfo) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 查询数据
        $sql = "SELECT app_uuid, app_name, app_type, app_auth_type, app_version, app_username,
                    UNIX_TIMESTAMP(update_time) AS update_time, cluster_flag, app_detail,
                    cluster_uuid, cluster_name, cluster_service_ip
                FROM bd_agent_app WHERE agent_uuid = ? ";
        $sqlCount = "SELECT COUNT(id) AS total FROM bd_agent_app WHERE agent_uuid = ? ";
        $sqlParams = $sqlCountParams = [$agentsUuid];

        // 排序
        $sortFields = [
            'app_type', 'app_version', 'app_name', 'app_username',
            'app_auth_type', 'update_time',
        ];
        if (isset($params['app_type']) && $params['app_type']) {
            $sql .= ' AND app_type = ? ';
            $sqlCount .= ' AND app_type = ? ';
            $sqlParams = array_merge($sqlParams, [(int) $params['app_type']]);
            $sqlCountParams = array_merge($sqlCountParams, [(int) $params['app_type']]);
        }
        if (isset($params['order'])) {
            $order = strtoupper($params['order']) == 'ASC' ? 'ASC' : 'DESC';
        }
        if (isset($params['sort'])) {
            $sort = in_array($sort, $sortFields) ? $sort : 'update_time';
        }
        $sql .= " ORDER BY $sort $order ";

        // 分页
        $sql .= ' limit ? , ? ';

        $sqlParams = array_merge($sqlParams, [$offset, $limit]);

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        if (!is_array($data) || !is_array($count)) {
            return  $this->sendResult(xphp_get_lang('WEB_AGENT_FAIL_TO_OBTAIN_DATA'), false, 0);
        }

        $agentAppList = ['rows' => [], 'total' => (int)$count[0]['total']];

        $agentAppClusterFlag = xphp_get_config('app', 'FLAG');
        foreach ($data as $row) {
            $clusterFlag = $agentAppClusterFlag['SET'] == $row['cluster_flag'] && $row['cluster_uuid'];
            $agentAppList['rows'][] = [
                'app_uuid' => $row['app_uuid'],
                'app_type' => $row['app_type'],
                'app_version' => $row['app_version'],
                'app_name' => $row['app_name'],
                'app_username' => $row['app_username'],
                'app_auth_type' => $row['app_auth_type'],
                'register_time' => $this->parseDate($row['update_time']),
                'cluster_uuid' => $clusterFlag ? ($row['cluster_uuid'] ?? '') : '',
                'cluster_name' => $clusterFlag ? ($row['cluster_name'] ?? '') : '',
                'cluster_service_ip' => $clusterFlag ? ($row['cluster_service_ip'] ?? '') : '',
                'cluster_app_list' => $this->getClusterAppList($row['cluster_uuid']),
                'agent_uuid' => $agentInfo[0]['agent_uuid'],  // 客户端信息
                'agent_name' => $agentInfo[0]['agent_name'],
                'hostname' => $agentInfo[0]['hostname'],
                'ip' => $agentInfo[0]['ip'],
                'os_type' => $agentInfo[0]['os_type'],
                'app_detail' => $row['app_detail'],
            ];
        }

        return $this->sendResult('', true, 200, $agentAppList);
    }

    /**
     * 获取客户端应用详情
     * @param string $agentsUuid       客户端uuid
     * @param string $applicationsUuid 客户端应用uuid
     * @return array
     */
    public function getClientAppDetail(string $agentsUuid, string $applicationsUuid): array
    {
        // 判断客户端是否存在
        $agentSql = "SELECT agent_uuid, agent_name, hostname, ip, os_type FROM bd_agent WHERE agent_uuid = ?";
        $agentInfo = $this->dbSelect($agentSql, [$agentsUuid]);
        if (!$agentInfo) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 获取数据
        $sql = "SELECT app_uuid, app_name, app_type, app_auth_type, app_version,
                    app_username, UNIX_TIMESTAMP(update_time) AS update_time, app_detail,
                    cluster_flag, cluster_service_ip, cluster_name, cluster_uuid
                FROM bd_agent_app WHERE agent_uuid = ? AND app_uuid = ? ";
        $sqlParams = [$agentsUuid, $applicationsUuid];
        $data = $this->dbSelect($sql, $sqlParams);

        if (!$data || !is_array($data)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_FAIL_TO_OBTAIN_DATA'), false, 0);
        }

        $agentAppClusterFlag = xphp_get_config('app', 'FLAG');
        $clusterFlag = $agentAppClusterFlag['SET'] == $data[0]['cluster_flag'] && $data[0]['cluster_uuid'];
        $agentAppInfo = [
            'app_uuid' => $data[0]['app_uuid'],
            'app_type' => $data[0]['app_type'],
            'app_version' => $data[0]['app_version'],
            'app_name' => $data[0]['app_name'],
            'app_username' => $data[0]['app_username'],
            'app_auth_type' => $data[0]['app_auth_type'],
            'register_time' => $this->parseDate($data[0]['update_time']),
            'cluster_uuid' => ($clusterFlag ? $data[0]['cluster_uuid'] : null),
            'cluster_name' => ($clusterFlag ? $data[0]['cluster_name'] : null),
            'cluster_service_ip' => ($clusterFlag ? $data[0]['cluster_service_ip'] : null),
            'agent_uuid' => $agentInfo[0]['agent_uuid'],  // 客户端信息
            'agent_name' => $agentInfo[0]['agent_name'],
            'hostname' => $agentInfo[0]['hostname'],
            'ip' => $agentInfo[0]['ip'],
            'os_type' => $agentInfo[0]['os_type'],
            'app_detail' => $data[0]['app_detail'],
        ];

        return $this->sendResult('', true, 200, $agentAppInfo);
    }

    /**
     * 删除单个客户端应用
     * @param string $agentsUuid       客户端uuid
     * @param string $applicationsUuid 客户端应用uuid
     * @return array
     */
    public function deleteClientApp(string $agentsUuid, string $applicationsUuid): array
    {
        // 权限验证
        $this->checkAuthBySourceUuid($agentsUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);

        $opName = 'DB_INSTANCE_OP_DELETE_INSTANCE';
        $operate = $this->dbProtectOpcode->getOpcodeDes($opName);
        // 验证
        $appUuidList = $this->getAppClusterAllAppUuid([$applicationsUuid]);
        $validateResult = $this->validateDeleteClientApp($agentsUuid, $appUuidList);
        if ($validateResult['success'] === false) {
            return $validateResult;
        }
        $agentApplicationInfo = $validateResult['data'];

        // 删除客户端应用
        $deleteResult = $this->service()->deleteClientAppService($appUuidList);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 'warning', $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_APP_DELETE_ERROR'), false, 0);
        }
        $detail = array_map(function ($agentApp) {
            return sprintf(xphp_get_lang('WEB_AGENT_APP_DELETE_DETAIL'), $agentApp['app_name']);
        }, $agentApplicationInfo);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_APP_DELETE_SUCCESS'), true, 200, $detail);
    }

    /**
     * 批量删除客户端应用
     * @param string $agentsUuid  客户端uuid
     * @param array  $appUuidList 客户端应用uuid
     * @return array
     */
    public function deleteClientAppList(string $agentsUuid, array $appUuidList): array
    {
        // 权限验证
        $this->checkAuthBySourceUuid($agentsUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);

        $opName = 'DB_INSTANCE_OP_DELETE_INSTANCE';
        $operate = $this->dbProtectOpcode->getOpcodeDes($opName);
        // 去重
        $appUuidList = array_values(array_unique($appUuidList));
        $appUuidList = $this->getAppClusterAllAppUuid($appUuidList);
        // 验证
        $validateResult = $this->validateDeleteClientApp($agentsUuid, $appUuidList);
        if ($validateResult['success'] === false) {
            return $validateResult;
        }
        $agentApplicationInfo = $validateResult['data'];

        // 删除客户端应用
        $deleteResult = $this->service()->deleteClientAppService($appUuidList);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 'warning', $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_APP_DELETE_LIST_ERROR'), false, 0);
        }
        $detail = array_map(function ($agentApp) {
            return sprintf(xphp_get_lang('WEB_AGENT_APP_DELETE_DETAIL'), $agentApp['app_name']);
        }, $agentApplicationInfo);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_APP_DELETE_LIST_SUCCESS'), true, 200, $detail);
    }

    /**
     * 获取应用所处集群的所有应用uuid
     * @param array $appUuidList 应用uuid
     * @return array
     */
    private function getAppClusterAllAppUuid(array $appUuidList): array
    {
        $appUuids = "'" . implode("', '", $appUuidList) . "'";
        $allAppUuidList = $appUuidList;
        $sql = "SELECT baa1.app_uuid
                FROM bd_agent_app baa
                    LEFT JOIN bd_agent_app baa1 ON baa.cluster_uuid = baa1.cluster_uuid
                WHERE baa.app_uuid IN ($appUuids) AND baa.cluster_flag = ?
                    AND baa.cluster_uuid IS NOT NULL AND baa.cluster_uuid != '' ";
        $appData = $this->dbSelect($sql, [xphp_get_config('app', 'FLAG')['SET']]);
        if (!is_array($appData) || !$appData) {
            $appData = [];
        }
        foreach ($appData as $appInfo) {
            $allAppUuidList[] = $appInfo['app_uuid'];
        }
        return array_values(array_unique($allAppUuidList));
    }

    /**
     * 验证删除客户端应用, 返回应用信息
     * 1. 验证客户端是否存在
     * 2. 验证客户端应用是否存在
     * 3. 验证应用是否有任务
     * @param string $agentsUuid  客户端uuid
     * @param array  $appUuidList 客户端应用uuid列表
     * @return array
     */
    private function validateDeleteClientApp(string $agentsUuid, array $appUuidList): array
    {
        // 判断客户端是否存在
        if (!$this->dbSelect("SELECT agent_uuid FROM bd_agent WHERE agent_uuid = ?", [$agentsUuid])) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 判断客户端应用是否存在
        $in = "'" . implode("', '", $appUuidList) . "'";
        $sql = "SELECT app_name FROM bd_agent_app WHERE agent_uuid = ? AND app_uuid IN ($in) ";
        $agentApplicationInfo = $this->dbSelect($sql, [$agentsUuid]);
        if (!$agentApplicationInfo || !is_array($agentApplicationInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT_APP'), false, 0);
        }

        // 判断应用是否有任务
        $sql = "SELECT dl.task_uuid
                FROM db_list dl
                    INNER JOIN bd_agent_app baa ON dl.agent_uuid = baa.agent_uuid AND dl.instance_name = baa.app_name
                WHERE baa.app_uuid IN ($in) ";
        $taskData = $this->dbSelect($sql);
        if (is_array($taskData) && $taskData) {
            return $this->sendResult(xphp_get_lang('WEB_ERROR_BD_AGENT_APP_IN_USE_BY_TASK_ERROR'), false, 0);
        }
        // 判断集群任务
        // 1. 查找集群uuid
        $sql = "SELECT baa.cluster_uuid FROM bd_agent_app baa
                WHERE baa.app_uuid IN ($in) AND baa.cluster_flag = ?
                    AND baa.cluster_uuid IS NOT NULL AND baa.cluster_uuid != '' ";
        $clusterData = $this->dbSelect($sql, [v1_parse_bool_to_flag(true)]);
        if (is_array($clusterData) && $clusterData) {
            // 2. 查找集群客户端的数据库任务
            $clusterUuidList = array_column($clusterData, 'cluster_uuid');
            $clusterUuidList = array_values(array_unique($clusterUuidList));
            $clusterUuids = "'" . implode("', '", $clusterUuidList) . "'";
            $clusterSql = "SELECT * FROM db_list dl
                            INNER JOIN bd_agent_app baa ON dl.agent_uuid = baa.agent_uuid AND dl.instance_name = baa.app_name
                           WHERE baa.cluster_uuid IN ($clusterUuids) ";
            $clusterTaskData = $this->dbSelect($clusterSql);
            if (is_array($clusterTaskData) && $clusterTaskData) {
                return $this->sendResult(xphp_get_lang('WEB_ERROR_BD_AGENT_APP_IN_USE_BY_TASK_ERROR'), false, 0);
            }
        }
        return $this->sendResult('', true, 200, $agentApplicationInfo);
    }

    /**
     * 添加应用/实例认证
     * @param array $params 请求的参数
     * @return array
     */
    public function addClientApp(array $params): array
    {
        // 权限验证
        $this->checkAuthBySourceUuid($params['agents_uuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);

        $agentsUuid = $params['agents_uuid'];
        $dbType = (int) $params['db_type'];
        $instanceName = $params['instance_name'];
        $listenIp = $params['listen_ip'];
        $opcodeName = 'DB_INSTANCE_OP_VERIFY_AUTH';
        $operate = $this->dbProtectOpcode->getOpcodeDes($opcodeName);
        // 验证客户端是否存在
        $sql = "SELECT hostname, agent_name, ip FROM bd_agent WHERE agent_uuid = ? ";
        $agentInfo = $this->dbSelect($sql, [$agentsUuid]);
        if (!$agentInfo || !is_array($agentInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        switch ($dbType) {
            case $allDbType['ORACLE']:
                // 添加oracle
                $msg = $this->getOracleMsg($params['oracle'], $agentsUuid, $instanceName);
                if (!$msg['success']) {
                    return $msg;
                }
                $msg = $msg['data'];
                break;
            case $allDbType['SQLSERVER']:
                // 添加sql server
                $msg = $this->getSQLServerMsg($params['sql_server'], $listenIp, $agentsUuid, $instanceName);
                if (!$msg['success']) {
                    return $msg;
                }
                $msg = $msg['data'];
                break;
            case $allDbType['MYSQL']:
            case $allDbType['MARIA']:
                // 添加MySQL或MariaDB
                $msg = $this->getMySQLMsg($params['mysql']);
                break;
            case $allDbType['DM']:
                // 添加DM
                $msg = $this->getDmMsg($params['dm'], $agentsUuid, $instanceName);
                if (!$msg['success']) {
                    return $msg;
                }
                $msg = $msg['data'];
                break;
            case $allDbType['POSTGRE']:
            case $allDbType['KINGBASE']:
            case $allDbType['UXDB']:
            case $allDbType['HIGHGO']:
            case $allDbType['OPENGAUSS']:
            case $allDbType['VASTBASE']:
            case $allDbType['ANTDB']:
                // 添加postgres、KingBase、UXDB、HighGo、openGauss、Vasebase
                $msg = $this->getPostgresMsg($params['postgres'], $agentsUuid, $instanceName);
                if (!$msg['success']) {
                    return $msg;
                }
                $msg = $msg['data'];
                break;
            case $allDbType['MONGODB']:
                $msg = $this->getMongoDBMsg($params['mongodb'], $agentsUuid, $instanceName);
                if (!$msg['success']) {
                    return $msg;
                }
                $msg = $msg['data'];
                break;
            case $allDbType['TIDB']:
                $msg = $this->getTiDBMsg($params['tidb'], $agentsUuid, $instanceName);
                if (!$msg['success']) {
                    return $msg;
                }
                $msg = $msg['data'];
                break;
            case $allDbType['CACHE']:
            case $allDbType['IRIS']:
                $msg = $this->getCacheMsg($params['cache']);
                break;
            case $allDbType['SAPHANA']:
                $msg = $this->getSapHanaMsg($params['sap_hana'], $agentsUuid, $instanceName);
                if (!$msg['success']) {
                    return $msg;
                }
                $msg = $msg['data'];
                break;
            default:
                // 不支持的数据库类型
                return $this->sendResult(xphp_get_lang('WEB_AGENT_APP_ADD_NONSUPPORT_DB'), false, 0);
        }

        // 添加应用
        $msg['agent_uuid'] = $agentsUuid;
        $msg['db_type'] = $dbType;
        $msg['instance_name'] = $instanceName;
        $msg['listen_ip'] = $listenIp;
        $addResult = $this->service()->addClientAppService($msg);
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 'warning', $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_APP_ADD_ERROR'), false, 0);
        }

        // 添加系统日志
        $logParams = [
            ($agentInfo[0]['hostname'] ?: $agentInfo[0]['agent_name']) . '[' . $agentInfo[0]['ip'] . ']',
            xphp_get_config('db', 'DB_TYPE_DES')[$dbType],
            $instanceName
        ];
        $this->systemLog('SYSTEM_LOG_DB_AUTH_INSTANCE', $logParams);
        $oneDetail = sprintf(xphp_get_lang('WEB_AGENT_APP_ADD_DETAIL'), $instanceName);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_APP_ADD_SUCCESS'), true, 200, [$oneDetail]);
    }

    /**
     * 验证集群名称
     * @param array  $dbInfo     数据库信息
     * @param string $agentsUuid 客户端uuid
     * @param string $appName    应用名称
     * @return array
     */
    private function validateClusterName(array $dbInfo, string $agentsUuid, string $appName): array
    {
        // 集群别名不能重复
        if ($dbInfo['is_cluster'] && $dbInfo['cluster_name']) {
            $clusterAgentList = [[
                'agent_uuid' => $agentsUuid,
                'app_name' => $appName,
            ]];
            foreach ($dbInfo['cluster_info'] as $clusterInfo) {
                $clusterAgentList[] = [
                    'agent_uuid' => $clusterInfo['agent_uuid'],
                    'app_name' => $clusterInfo['instance_name'],
                ];
            }
            $sql = "SELECT agent_uuid, app_name FROM bd_agent_app WHERE cluster_name = ? ";
            $clusterData = $this->dbSelect($sql, [$dbInfo['cluster_name']]);
            $nameUsedFlag = false;
            if (is_array($clusterData) && $clusterData) {
                foreach ($clusterData as $clusterInfo) {
                    $tmpFlag = true;
                    foreach ($clusterAgentList as $clusterAgent) {
                        if (
                            $clusterInfo['agent_uuid'] == $clusterAgent['agent_uuid'] &&
                            $clusterInfo['app_name'] == $clusterAgent['app_name']
                        ) {
                            $tmpFlag = false;
                            break;
                        }
                    }
                    if ($tmpFlag) {
                        $nameUsedFlag = true;
                    }
                }
            }
            if ($nameUsedFlag) {
                return $this->sendResult(xphp_get_lang('WEB_AGENT_APP_ADD_CLUSTER_NAME_USED'), false, 0);
            }
        }
        return $this->sendResult('');
    }

    /**
     * 添加oracle
     * @param array  $oracle       oracle数据信息
     * @param string $agentsUuid   客户端uuid
     * @param string $instanceName 实例名
     * @return array
     */
    private function getOracleMsg(array $oracle, string $agentsUuid, string $instanceName): array
    {
        $validateResult = $this->validateClusterName($oracle, $agentsUuid, $instanceName);
        if (!$validateResult['success']) {
            return $validateResult;
        }
        return $this->sendResult('', true, 200, [
            'auth_type' => $oracle['auth_type'],
            'username' => $oracle['username'],
            'password' => $oracle['password'],
            'verify_detail' => '',
            'install_db_username' => $oracle['install_db_username'],
            'cluster_flag' => v1_parse_bool_to_flag($oracle['is_cluster']),
            'cluster_name' => $oracle['is_cluster'] ? $oracle['cluster_name'] : '',
            'cluster_service_ip' => '',
            'cluster_info' => $oracle['is_cluster'] ? $oracle['cluster_info'] : [],
            'listen_port' => 0,
        ]);
    }

    /**
     * 添加SQL Server
     * @param array  $sqlServer    SQL Server数据信息
     * @param string $listenIp     监听IP
     * @param string $agentsUuid   客户端uuid
     * @param string $instanceName 实例名
     * @return array
     */
    private function getSQLServerMsg(array $sqlServer, string &$listenIp, string $agentsUuid, string $instanceName): array
    {
        $validateResult = $this->validateClusterName($sqlServer, $agentsUuid, $instanceName);
        if (!$validateResult['success']) {
            return $validateResult;
        }
        if ($sqlServer['is_cluster']) {
            $listenIp = $sqlServer['cluster_ip'];
        }
        return $this->sendResult('', true, 200, [
            'auth_type' => $sqlServer['auth_type'],
            'username' => $sqlServer['user']['username'],
            'password' => $sqlServer['user']['password'],
            'verify_detail' => '',
            'install_db_username' => '',
            'cluster_flag' => v1_parse_bool_to_flag($sqlServer['is_cluster']),
            'cluster_name' => $sqlServer['is_cluster'] ? $sqlServer['cluster_name'] : '',
            'cluster_service_ip' => '',
            'cluster_info' => $sqlServer['is_cluster'] ? $sqlServer['cluster_info'] : [],
            'listen_port' => 0,
        ]);
    }

    /**
     * 添加MySQL
     * @param array $mysql MySQL和MariaDB数据信息
     * @return array
     */
    private function getMySQLMsg(array $mysql): array
    {
        $verifyDetail = [
            'cnf_path' => $mysql['config_path'],
            'port' => '',
            'sock_path' => '',
        ];
        if ($mysql['auth_type'] == xphp_get_config('client', 'MYSQL_AUTH_TYPE', 'resources')['tcp']) {
            $verifyDetail['port'] = $mysql['tcp']['port'];
            $verifyDetail['host'] = $mysql['tcp']['ip'];
        } else {
            $verifyDetail['host'] = $mysql['sock']['host'];
            $verifyDetail['sock_path'] = $mysql['sock']['sock_path'];
        }
        return [
            'auth_type' => $mysql['auth_type'],
            'username' => $mysql['username'],
            'password' => $mysql['password'],
            'verify_detail' => json_encode($verifyDetail),
            'install_db_username' => '',
            'cluster_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'cluster_name' => '',
            'cluster_service_ip' => '',
            'cluster_info' => [],
            'listen_port' => 0,
        ];
    }

    /**
     * 添加Postgres
     * @param array  $postgres     postgres数据信息
     * @param string $agentsUuid   客户端uuid
     * @param string $instanceName 实例名
     * @return array
     */
    private function getPostgresMsg(array $postgres, string $agentsUuid, string $instanceName): array
    {
        $validateResult = $this->validateClusterName($postgres, $agentsUuid, $instanceName);
        if (!$validateResult['success']) {
            return $validateResult;
        }
        $verifyDetail = [
            'database_name' => $postgres['db_name'],
            'install_db_path' => $postgres['db_bin_path']
        ];
        return $this->sendResult('', true, 200, [
            'auth_type' => 2,
            'username' => $postgres['username'],
            'password' => $postgres['password'],
            'verify_detail' => json_encode($verifyDetail),
            'install_db_username' => '',
            'cluster_flag' => v1_parse_bool_to_flag($postgres['is_cluster']),
            'cluster_name' => $postgres['is_cluster'] ? $postgres['cluster_name'] : '',
            'cluster_service_ip' => '',
            'cluster_info' => $postgres['is_cluster'] ? $postgres['cluster_info'] : [],
            'listen_port' => 0,
        ]);
    }

    /**
     * 添加DM
     * @param array  $dm           dm数据信息
     * @param string $agentsUuid   客户端uuid
     * @param string $instanceName 实例名
     * @return array
     */
    private function getDmMsg(array $dm, string $agentsUuid, string $instanceName): array
    {
        $validateResult = $this->validateClusterName($dm, $agentsUuid, $instanceName);
        if (!$validateResult['success']) {
            return $validateResult;
        }
        return $this->sendResult('', true, 200, [
            'auth_type' => 2,
            'username' => $dm['username'],
            'password' => $dm['password'],
            'verify_detail' => '',
            'install_db_username' => $dm['install_db_username'],
            'cluster_flag' => v1_parse_bool_to_flag($dm['is_cluster']),
            'cluster_name' => $dm['is_cluster'] ? $dm['cluster_name'] : '',
            'cluster_service_ip' => '',
            'cluster_info' => $dm['is_cluster'] ? $dm['cluster_info'] : [],
            'listen_port' => 0,
        ]);
    }

    /**
     * 添加MongoDB
     * @param array  $mongoDB      MongoDB数据信息
     * @param string $agentsUuid   客户端uuid
     * @param string $instanceName 实例名
     * @return array
     */
    private function getMongoDBMsg(array $mongoDB, string $agentsUuid, string $instanceName): array
    {
        $validateResult = $this->validateClusterName($mongoDB, $agentsUuid, $instanceName);
        if (!$validateResult['success']) {
            return $validateResult;
        }
        $verifyDetail = [
            'install_db_path' => $mongoDB['db_bin_path']
        ];
        return $this->sendResult('', true, 200, [
            'auth_type' => 2,
            'username' => $mongoDB['username'],
            'password' => $mongoDB['password'],
            'verify_detail' => json_encode($verifyDetail),
            'install_db_username' => '',
            'cluster_flag' => v1_parse_bool_to_flag($mongoDB['is_cluster']),
            'cluster_name' => $mongoDB['is_cluster'] ? $mongoDB['cluster_name'] : '',
            'cluster_service_ip' => '',
            'cluster_info' => $mongoDB['is_cluster'] ? $mongoDB['cluster_info'] : [],
            'listen_port' => 0,
        ]);
    }

    /**
     * 添加TiDB
     * @param array  $tidb         TiDB数据信息
     * @param string $agentsUuid   客户端uuid
     * @param string $instanceName 实例名
     * @return array
     */
    private function getTiDBMsg(array $tidb, string $agentsUuid, string $instanceName): array
    {
        $validateResult = $this->validateClusterName($tidb, $agentsUuid, $instanceName);
        if (!$validateResult['success']) {
            return $validateResult;
        }
        return $this->sendResult('', true, 200, [
            'auth_type' => 2,
            'username' => $tidb['username'],
            'password' => $tidb['password'],
            'verify_detail' => '',
            'install_db_username' => '',
            'cluster_flag' => v1_parse_bool_to_flag($tidb['is_cluster']),
            'cluster_name' => $tidb['is_cluster'] ? $tidb['cluster_name'] : '',
            'cluster_service_ip' => '',
            'cluster_info' => $tidb['is_cluster'] ? $tidb['cluster_info'] : [],
            'listen_port' => 0,
        ]);
    }

    /**
     * 添加Cache/IRIS
     * @param array $cache Cache/IRIS数据信息
     * @return array
     */
    private function getCacheMsg(array $cache): array
    {
        $verifyDetail = [
            'table_space' => $cache['table_space'],
        ];
        return [
            'auth_type' => 2,
            'username' => $cache['username'],
            'password' => $cache['password'],
            'verify_detail' => json_encode($verifyDetail),
            'install_db_username' => '',
            'cluster_flag' => v1_parse_bool_to_flag($cache['is_cluster']),
            'cluster_name' => $cache['is_cluster'] ? $cache['cluster_name'] : '',
            'cluster_service_ip' => '',
            'cluster_info' => $cache['is_cluster'] ? $cache['cluster_info'] : [],
            'listen_port' => 0,
        ];
    }

    /**
     * 添加SAP HANA
     * @param array  $sapHana      SAP HANA数据信息
     * @param string $agentsUuid   客户端uuid
     * @param string $instanceName 实例名
     * @return array
     */
    private function getSapHanaMsg(array $sapHana, string $agentsUuid, string $instanceName): array
    {
        $validateResult = $this->validateClusterName($sapHana, $agentsUuid, $instanceName);
        if (!$validateResult['success']) {
            return $validateResult;
        }
        return $this->sendResult('', true, 200, [
            'auth_type' => 2,
            'username' => $sapHana['username'],
            'password' => $sapHana['password'],
            'verify_detail' => '',
            'install_db_username' => '',
            'cluster_flag' => v1_parse_bool_to_flag($sapHana['is_cluster']),
            'cluster_name' => $sapHana['is_cluster'] ? $sapHana['cluster_name'] : '',
            'cluster_service_ip' => $sapHana['cluster_service_ip'],
            'cluster_info' => $sapHana['is_cluster'] ? $sapHana['cluster_info'] : [],
            'listen_port' => 0,
        ]);
    }

    /**
     * 根据前缀获取数据库集群别名
     * @param string $clusterNamePrefix 集群名前缀
     * @return string
     */
    private function getClientAppClusterNameByPrefix(string $clusterNamePrefix): string
    {
        $sequence = 1;
        $sql = "SELECT DISTINCT cluster_name FROM bd_agent_app WHERE cluster_uuid != '' AND cluster_uuid IS NOT NULL ";
        $clusterData = $this->dbSelect($sql);
        if (!is_array($clusterData)) {
            return $clusterNamePrefix . $sequence;
        }
        while (true) {
            $loopFlag = false;
            foreach ($clusterData as $clusterInfo) {
                if ($clusterInfo['cluster_name'] == $clusterNamePrefix . $sequence) {
                    $sequence++;
                    $loopFlag = true;
                }
            }
            if (!$loopFlag) {
                break;
            }
        }
        return $clusterNamePrefix . $sequence;
    }

    /**
     * 获取数据库集群别名
     * @param int $dbType 数据库类型
     * @return array
     */
    public function getClientAppClusterName(int $dbType): array
    {
        xphp_get_lang('WEB_PLATFORM_DES_CLUSTER');
        $allDbTypeDes = xphp_get_config('db', 'DB_TYPE_DES');
        $clusterNamePrefix = ($allDbTypeDes[$dbType] ?? '') . xphp_get_lang('WEB_PLATFORM_DES_CLUSTER');
        return $this->sendResult('', true, 200, [
            'cluster_name' => $this->getClientAppClusterNameByPrefix($clusterNamePrefix),
        ]);
    }

    /**
     *   获取客户端集群信息
     * @param array $params 数据
     * @return array
     */
    public function getAppCluster(array $params = []): array
    {

        // 1, 在 bd_agent_app 表根据 agent_uuid 查询出 cluster_flag 1是集群标记的 cluster_uuid 字段
        $setFlag = xphp_get_config('app', 'FLAG');
        $agentApp = $this->dbSelect(
            "SELECT cluster_uuid,cluster_name,cluster_service_ip 
                    FROM bd_agent_app
                    WHERE agent_uuid = ? AND cluster_flag = ?
                        AND cluster_uuid IS NOT NULL AND cluster_uuid != ''
                    LIMIT 1 ",
            [$params['agents_uuid'], $setFlag['SET']]
        );
        if (empty($agentApp[0])) {
            return ['rows' => [], 'total' => 0];
        }

        // 2, 根据 cluster_uuid 在 bd_agent_app 表里查询所有的 agent_uuid 集合
        // 3, 根据 agent_uuid 在 bd_agent 表里查询出相关信息列表
        $sql = "SELECT agent_uuid, agent_name, hostname, ip, os_type FROM bd_agent
                WHERE agent_uuid IN 
                                 (SELECT agent_uuid FROM bd_agent_app WHERE cluster_uuid = ? AND cluster_flag = 1 )
                ORDER BY id DESC LIMIT ?,?";
        $list = $this->dbSelect($sql, [$agentApp[0]['cluster_uuid'], $params['offset'], $params['limit']]);

        foreach ($list as &$item) {
            $item['agent_name'] = empty($item['agent_name']) ? $item['hostname'] : $item['agent_name'];
            $item['cluster_name'] = $agentApp[0]['cluster_name'];
            $item['cluster_service_ip'] = $agentApp[0]['cluster_service_ip'];
            unset($item['hostname']);
        }

        $total = $this->dbSelect(
            "SELECT count(id) num FROM bd_agent_app WHERE cluster_uuid = ? AND cluster_flag = 1 LIMIT 1",
            [$agentApp[0]['cluster_uuid']]
        );

        return [
            'rows' => $list,
            'total' => $total[0]['num']
        ];
    }

    /**
     * 获取客户端应用配置信息
     * @return array
     */
    private function getApplicationList(): array
    {
        $ret = [];
        // 查询集群信息
        $sql = "SELECT agent_uuid, app_name, app_type, cluster_flag, cluster_name,
                    cluster_uuid, cluster_service_ip, app_uuid, app_detail, app_auth_type,
                    install_app_username, app_service_name
                FROM bd_agent_app ";
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            $data = [];
        }
        foreach ($data as $row) {
            $ret[] = [
                'agent_uuid' => $row['agent_uuid'],
                'app_uuid' => $row['app_uuid'],
                'app_name' => $row['app_name'],
                'app_type' => $row['app_type'],
                'app_detail' => $row['app_detail'],
                'cluster_flag' => v1_parse_flag_to_bool($row['cluster_flag']) && $row['cluster_uuid'],
                'cluster_name' => $row['cluster_name'] ?: '',
                'cluster_uuid' => $row['cluster_uuid'] ?: '',
                'cluster_service_ip' => $row['cluster_service_ip'] ?: '',
                'app_auth_type' => intval($row['app_auth_type']),
                'install_app_username' => $row['install_app_username'] ?: '',
                'app_service_name' => $row['app_service_name'] ?: '',
            ];
        }
        return $ret;
    }

    /**
     * 获取磁盘的卷信息
     * @param string $diskUuid uuid
     * @return array
     */
    private function getClientVolInfo(string $diskUuid): array
    {
        $sql = "SELECT disk_uuid, vol_name, vol_uuid, capacity, free_space,
                    mount_point, display_name, is_boot, detail
                FROM bd_agent_vol WHERE disk_uuid = ?";
        $sqlParams = [$diskUuid];
        $selectData = $this->dbSelect($sql, $sqlParams);
        $volInfo = [];
        foreach ($selectData as $row) {
            $volInfo[] = [
                'disk_uuid' => $row['disk_uuid'],
                'vol_name' => $row['vol_name'],
                'vol_uuid' => $row['vol_uuid'],
                'capacity' => $row['capacity'],
                'free_space' => $row['free_space'],
                'mount_point' => $row['mount_point'],
                'display_name' => $row['display_name'],
                'is_boot' => $row['is_boot'] == 1,  // 1为true; 0、2为false。是否为引导分区(linux: /boot, windows: EFI)
                'detail' => $row['detail']
            ];
        }
        return $volInfo;
    }

    /**
     * 获取客户端的磁盘信息
     * @param string $agentsUuid uuid
     * @return array
     */
    public function getClientDiskInfo(string $agentsUuid): array
    {
        if (!$this->dbSelect("SELECT agent_uuid FROM bd_agent WHERE agent_uuid = ?", [$agentsUuid])) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        $sql = "SELECT agent_uuid, disk_name, disk_uuid, capacity, free_space, display_name
                FROM bd_agent_disk WHERE agent_uuid = ?";
        $selectData = $this->dbSelect($sql, [$agentsUuid]);

        $diskInfo = [];
        foreach ($selectData as $row) {
            $diskInfo[] = [
                'agent_uuid' => $row['agent_uuid'],
                'disk_name' => $row['disk_name'],
                'disk_uuid' => $row['disk_uuid'],
                'capacity' => $row['capacity'],
                'free_space' => $row['free_space'],
                'display_name' => $row['display_name'],
                'vol_info' => $this->getClientVolInfo($row['disk_uuid']),
            ];
        }
        return $this->sendResult('', true, 200, $diskInfo);
    }

    /**
     * 获取客户端网卡信息
     * @param array $params 数据
     * @return array|bool
     */
    public function getClientNetworkCard(array $params = [])
    {
        // 1获取客户端信息里面的 详情
        $sql = "SELECT detail FROM bd_agent WHERE agent_uuid = ? ";
        $data = $this->dbSelect($sql, [$params['agents_uuid']]);

        if (empty($data)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 网卡全部存放在这个字段里面
        $detail = json_decode($data[0]['detail'], true);
        $ipList = $detail['nic_list'];

        $newarr = array_slice($ipList, $params['offset'], $params['limit']);
        $list = [];
        if (!empty($newarr)) {
            $j = 0;
            foreach ($newarr as $i) {
                $j++;
                $list2 = $i['ip_set'];
                $ip = '';
                $netmask = '';
                foreach ($list2 as $net) {
                    if ($net['ip_addr'] != '127.0.0.1') {
                        $ip .= $net['ip_addr'] . PHP_EOL;
                        $netmask .= $net['netmask'] . PHP_EOL;
                    }
                }
                $list[] = array(
                    'num'   => $params['offset'] + $j,
                    'network_name' => $i['name'],
                    'ip' => $ip,
                    'mac' => $i['mac_address'],
                    'netmask' => $netmask,
                    'gateway' => $i['gateway_address']
                );
            }
        }

        $total = count($ipList);
        return $this->sendResult('', true, 200, [
            'rows' => $list,
            'total' => $total
        ]);
    }

    /**
     * 分配客户端的所有者用户
     * @param array  $agentUuids 客户端uuid
     * @param string $userUuid   用户uuid
     * @return array
     */
    public function allocateClientUser(array $agentUuids, string $userUuid): array
    {
        // 去重
        $agentUuids = array_values(array_unique($agentUuids));
        // 验证客户端是否存在
        $in = "'" . implode("', '", $agentUuids) . "'";
        $agentSql = "SELECT agent_uuid, ip, hostname FROM bd_agent WHERE agent_uuid IN ($in)";
        $agentInfo = $this->dbSelect($agentSql);
        if (!is_array($agentInfo) || count($agentUuids) != count($agentInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }
        // 验证用户是否存在
        $userSql = "SELECT user_uuid, user_name FROM bd_user WHERE user_uuid = ?";
        $userInfo = $this->dbSelect($userSql, [$userUuid]);
        if (!is_array($userInfo) || !$userInfo) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_USER'), false, 0);
        }

        $sql = "UPDATE bd_agent SET user_uuid = ? WHERE agent_uuid IN ($in)";
        $execResult = $this->dbExec($sql, [$userUuid]);

        // 返回值处理
        if (!$execResult) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_ALLOCATION_USER_ERROR'), false, 0);
        }
        $detail = array_map(function ($agent) use ($userInfo) {
            return sprintf(
                xphp_get_lang('WEB_AGENT_ALLOCATION_USER_DETAIL'),
                $agent['hostname'] . '(' . $agent['ip'] . ')',
                $userInfo[0]['user_name']
            );
        }, $agentInfo);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_ALLOCATION_USER_SUCCESS'), true, 200, $detail);
    }

    /**
     * 调整客户端分组
     * @param int    $adjustMode     调整模式【1移入 2移出】
     * @param array  $agentUuids     客户端uuid列表
     * @param string $agentGroupUuid 客户端分组uuid 【如果是移入操作，那么就是移入该分组 如果是移出操作，那么就是从该分组移出】
     * @return array
     */
    public function adjustClientGroup(int $adjustMode, array $agentUuids, string $agentGroupUuid): array
    {
        // 去重
        $agentUuids = array_values(array_unique($agentUuids));
        // 验证客户端是否存在
        $in = "'" . implode("', '", $agentUuids) . "'";
        $agentSql = "SELECT agent_uuid, ip, hostname, agent_type FROM bd_agent WHERE agent_uuid in ($in)";
        $agentInfo = $this->dbSelect($agentSql);
        if (!is_array($agentInfo) || count($agentUuids) != count($agentInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 权限验证
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $agentList = array_filter($agentInfo, function($row) use ($allAgentType) {
            return $row['agent_type'] == $allAgentType['APPLIANCE'];
        });
        $clientList = array_filter($agentInfo, function($row) use ($allAgentType) {
            return $row['agent_type'] != $allAgentType['APPLIANCE'];
        });
        if ($agentList) {
            $this->checkAuthBySourceUuid(
                implode(',', array_column($agentList, 'agent_uuid')),
                xphp_get_config('resource', 'RESOURCE_TYPE')['APPLICE']
            );
        }
        if ($clientList) {
            $this->checkAuthBySourceUuid(
                implode(',', array_column($clientList, 'agent_uuid')),
                xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']
            );
        }

        // 验证客户端分组是否存在
        $agentGroupSql = "SELECT group_uuid, group_name FROM bd_agent_group WHERE group_uuid = ?";
        $agentGroupInfo = $this->dbSelect($agentGroupSql, [$agentGroupUuid]);
        if (!is_array($agentGroupInfo) || !$agentGroupInfo) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT_GROUP'), false, 0);
        }

        //验证客户端是否在任务中
        $sql = "SELECT agent_uuid FROM bd_task_agent_list WHERE agent_uuid IN ($in) ";
        $agentTaskInfo = $this->dbSelect($sql);
        if (!empty($agentTaskInfo)) {
            // 客户端在任务中, 直接返回
            return $this->sendResult(xphp_get_lang('UI_AGENT_IN_TASK'), false, 0);
        }

        /*
         * 客户端与客户端分组的关联是多对一的，即一个客户端只能有一个客户端分组，一个客户端分组可能有多个客户端
         * 1. 移入，修改到指定的分组即可
         * 2. 移出，修改到默认分组
         */
        $lang = [
            'success' => xphp_get_lang('WEB_AGENT_MOVE_IN_GROUP_SUCCESS'),
            'error' => xphp_get_lang('WEB_AGENT_MOVE_IN_GROUP_ERROR'),
            'detail' => xphp_get_lang('WEB_AGENT_MOVE_IN_GROUP_DETAIL'),
        ];
        if ($adjustMode == 2) {
            // 移出操作需要客户端在客户端分组里面才进行移出
            $sql = "SELECT agent_uuid, agent_name, hostname FROM bd_agent WHERE agent_uuid in ($in) AND group_uuid=?";
            $agentInfoCheck = $this->dbSelect($sql, [$agentGroupUuid]);
            if (!is_array($agentInfoCheck) || count($agentInfoCheck) != count($agentUuids)) {
                return $this->sendResult(xphp_get_lang('WEB_AGENT_MOVE_OUT_NOT_IN_GROUP'), false, 0);
            }

            // 移出操作，将客户端分组uuid修改为默认分组即可
            $agentGroupSql = "SELECT group_uuid, group_name FROM bd_agent_group WHERE group_type=? LIMIT 1";
            $agentGroupType = xphp_get_config('client', 'AGENT_GROUP_TYPE', 'resources');
            $defaultAgentGroupInfo = $this->dbSelect($agentGroupSql, [$agentGroupType['system']]);
            if (!is_array($defaultAgentGroupInfo) || !$defaultAgentGroupInfo) {
                // 默认分组不存在
                return $this->sendResult(xphp_get_lang('WEB_AGENT_NOT_DEFAULT_GROUP'), false, 0);
            }
            if ($agentGroupUuid == $defaultAgentGroupInfo[0]['group_uuid']) {
                // 移出默认分组操作
                return $this->sendResult(xphp_get_lang('WEB_AGENT_MOVE_OUT_DEFAULT_GROUP'), false, 0);
            }
            $agentGroupUuid = $defaultAgentGroupInfo[0]['group_uuid'];
            $lang = [
                'success' => xphp_get_lang('WEB_AGENT_MOVE_OUT_GROUP_SUCCESS'),
                'error' => xphp_get_lang('WEB_AGENT_MOVE_OUT_GROUP_ERROR'),
                'detail' => xphp_get_lang('WEB_AGENT_MOVE_OUT_GROUP_DETAIL'),
            ];
        } else {
            // 检测是否有不同类型的代理移动至一个分组（默认分组除外）
            $sql = "SELECT ba.agent_type, bag.group_type
                    FROM bd_agent ba
                        INNER JOIN bd_agent_group bag on ba.group_uuid = bag.group_uuid 
                    WHERE ba.group_uuid = ? LIMIT 1 ";
            $data = $this->dbSelect($sql, [$agentGroupUuid]);
            $existsAgentType = null;
            if ($data && 1 != $data[0]['group_type']) {
                $existsAgentType = $data[0]['agent_type'];
            }

            $sql = "SELECT DISTINCT agent_type FROM bd_agent WHERE agent_uuid in ($in) ";
            $data = $this->dbSelect($sql);
            $agentTypes = [];
            foreach ($data as $row) {
                if ($existsAgentType != null && $row['agent_type'] != $existsAgentType) {
                    return $this->sendResult(xphp_get_lang('UI_CLIENT_GROUP_MOVE_CLIENT_MULTI_TYPE_TIPS'), false, 0);
                }
                $agentTypes[$row['agent_type']] = 1;
            }
            if (count($agentTypes) > 1) {
                return $this->sendResult(xphp_get_lang('UI_CLIENT_GROUP_MOVE_CLIENT_MULTI_TYPE_TIPS'), false, 0);
            }
        }

        // 移入 & 移出操作
        $sql = "UPDATE bd_agent SET group_uuid = ? WHERE agent_uuid in ($in)";
        $execResult = $this->dbExec($sql, [$agentGroupUuid]);
        if (!$execResult) {
            return $this->sendResult($lang['error'], false, 0);
        }
        $detail = array_map(function ($agent) use ($agentGroupInfo, $lang) {
            return sprintf(
                $lang['detail'],
                $agent['hostname'] . '(' . $agent['ip'] . ')',
                $agentGroupInfo[0]['group_name']
            );
        }, $agentInfo);
        return $this->sendResult($lang['success'], true, 200, $detail);
    }

    /**
     * 获取客户端分组信息
     * @return array
     */
    public function getClientGroupInfo(): array
    {
        $sql = "SELECT bag.group_uuid, bag.group_name, bag.remark, UNIX_TIMESTAMP(bag.create_time) AS create_time,
                    bag.group_type, bag.user_uuid
                FROM bd_agent_group bag
                    LEFT JOIN bd_agent ba ON ba.group_uuid = bag.group_uuid
                WHERE 1=1 ";
        $sqlParams = [];
        // 权限判断
        $loginUser = xphp_get_user_info();
        if (v1_auth_need_check_look()) {
            $sql .= " AND (bag.user_uuid = '' OR bag.user_uuid =?) ";  // 查询默认分组和当前用户拥有
            $sqlParams[] = $loginUser['userUuid'];
            $allResourceType = xphp_get_config('resource', 'RESOURCE_TYPE');
            $clientUuidSql = v1_auth_get_source_by_type($allResourceType['CLIENT'], 'ba.agent_uuid');
            $agentUuidSql = v1_auth_get_source_by_type($allResourceType['APPLICE'], 'ba.agent_uuid');
            $sql .= " OR ($clientUuidSql) ";
            $sql .= " OR ($agentUuidSql) ";
        }
        $sql .= " ORDER BY bag.group_type ASC, bag.create_time ASC ";  // 默认分组排序在最前面
        $dataSelect = $this->dbSelect($sql, $sqlParams);
        if (!is_array($dataSelect)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_FAIL_TO_OBTAIN_DATA'), false, 0);
        }
        $rows = [];
        foreach ($dataSelect as $row) {
            $rows[$row['group_uuid']] = [
                'group_uuid' => $row['group_uuid'],
                'group_name' => $row['group_name'],
                'remark' => $row['remark'],
                'create_time' => $this->parseDate($row['create_time']),
                'group_type' => $row['group_type'],
                'user_uuid' => $row['user_uuid'],
            ];
        }

        $rows = array_values($rows);

        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

    /**
     * 检查客户端分组名称
     * @param string $groupName 客户端分组名称
     * @param string $groupUuid 客户端分组UUID
     * @return array
     */
    private function checkClientGroupName(string $groupName, string $groupUuid = ''): array
    {
        $sql = "SELECT group_uuid FROM bd_agent_group WHERE group_name = ? ";
        $sqlParams = [$groupName];
        if ($groupUuid) {
            $sql .= " AND group_uuid <> ? ";
            $sqlParams[] = $groupUuid;
        }
        $data = $this->dbSelect($sql, $sqlParams);
        if (is_array($data) && $data) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_NAME_EXISTS'), false, 0);
        }
        return $this->sendResult('', true, 200);
    }

    /**
     * 添加客户端分组
     * @param string $groupName 客户端分组名称
     * @param string $remark    客户端分组备注
     * @return array
     */
    public function addClientGroup(string $groupName, string $remark): array
    {
        // 查询当前登录的用户信息
        $loginUser = xphp_get_user_info();

        $groupName = v1_remove_escape($groupName);

        $ret = $this->checkClientGroupName($groupName);
        if (!$ret['success']) {
            return $ret;
        }

        $this->dbBeginTransaction();
        // 添加到数据库
        $sql = "INSERT INTO bd_agent_group (group_uuid, group_name, remark, user_uuid, create_time, group_type, detail)
                    VALUES (?, ?, ?, ?, ?, ?, ?) ";
        $groupUuid = xphp_uuid();
        $createTime = date('Y-m-d H:i:s');
        $sqlParams = [
            $groupUuid,
            $groupName,
            $remark,
            $loginUser['userUuid'],
            $createTime,
            xphp_get_config('client', 'AGENT_GROUP_TYPE', 'resources')['custom'],
            ''
        ];
        $insertExec = $this->dbExec($sql, $sqlParams);
        if (!$insertExec) {
            $this->dbRollBack();
            return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_ADD_ERROR'), false, 0);
        }
        $this->dbCommit();
        $oneDetail = sprintf(xphp_get_lang('WEB_AGENT_GROUP_ADD_DETAIL'), $groupName);
        $addInfo = [
            'group_uuid' => $groupUuid,
            'group_name' => $groupName,
            'remark' => $remark,
            'create_time' => $createTime,
            'group_type' => xphp_get_config('client', 'AGENT_GROUP_TYPE', 'resources')['custom'],
            'add_text' => $oneDetail,
            'user_uuid' => $loginUser['userUuid'],
        ];
        return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_ADD_SUCCESS'), true, 200, $addInfo);
    }

    /**
     * 修改客户端分组
     * @param string $groupsUuid 客户端分组uuid
     * @param string $groupName  客户端分组名称
     * @param string $remark     客户端分组备注
     * @return array
     */
    public function updateClientGroup(string $groupsUuid, string $groupName, string $remark): array
    {
        $groupName = v1_remove_escape($groupName);
        $clientGroupData = $this->dbSelect("SELECT group_uuid, user_uuid FROM bd_agent_group WHERE group_uuid = ?", [$groupsUuid]);
        // 验证客户端分组是否存在
        if (!$clientGroupData) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT_GROUP'), false, 0);
        }

        // 资源拥有判断
        $this->checkAuthByUserUuid($clientGroupData[0]['user_uuid'], 'resmanagement');

        $ret = $this->checkClientGroupName($groupName, $groupsUuid);
        if (!$ret['success']) {
            return $ret;
        }

        // 更新数据
        $sql = "UPDATE bd_agent_group SET group_name = ?, remark = ?, create_time = ? WHERE group_uuid = ? ";
        $sqlParams = [$groupName, $remark, date('Y-m-d H:i:s'), $groupsUuid];
        $updateExec = $this->dbExec($sql, $sqlParams);
        if (!$updateExec) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_UPDATE_ERROR'), false, 0);
        }
        $oneDetail = sprintf(xphp_get_lang('WEB_AGENT_GROUP_UPDATE_DETAIL'), $groupName);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_UPDATE_SUCCESS'), true, 200, [$oneDetail]);
    }

    /**
     * 删除客户端分组
     * @param string $groupsUuid 客户端分组uuid
     * @return array
     */
    public function deleteClientGroup(string $groupsUuid): array
    {
        // 验证客户端分组
        $validateResult = $this->validateDeleteClientGroup([$groupsUuid]);
        if ($validateResult['success'] === false) {
            return $validateResult;
        }
        $agentGroupInfo = $validateResult['data'];

        // 资源拥有判断
        $this->checkAuthByUserUuid($agentGroupInfo[0]['user_uuid'], 'resmanagement');

        $this->dbBeginTransaction();
        // 删除客户端
        $sql = "DELETE FROM bd_agent_group WHERE group_uuid = ? ";
        $deleteExec = $this->dbExec($sql, [$groupsUuid]);

        // 移动到默认分组中
        $agentGroupType = xphp_get_config('client', 'AGENT_GROUP_TYPE', 'resources');
        $sql = "SELECT group_uuid FROM bd_agent_group WHERE group_type = ? ";
        $groupData = $this->dbSelect($sql, [$agentGroupType['system']]);
        $sql = "UPDATE bd_agent SET group_uuid = ? WHERE group_uuid = ? ";
        $deleteExec = $deleteExec && $this->dbExec($sql, [$groupData[0]['group_uuid'], $groupsUuid]);
        if (!$deleteExec) {
            $this->dbRollBack();
            return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_DELETE_ERROR'), false, 0);
        }
        $this->dbCommit();

        $oneDetail = sprintf(xphp_get_lang('WEB_AGENT_GROUP_DELETE_DETAIL'), $agentGroupInfo[0]['group_name']);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_DELETE_SUCCESS'), true, 200, [$oneDetail]);
    }

    /**
     * 批量删除客户端分组
     * @param array $agentGroupUuids 客户端分组uuid列表
     * @return array
     */
    public function deleteClientGroupList(array $agentGroupUuids): array
    {
        // 去重
        $agentGroupUuids = array_values(array_unique($agentGroupUuids));
        // 验证客户端分组
        $validateResult = $this->validateDeleteClientGroup($agentGroupUuids);
        if ($validateResult['success'] === false) {
            return $validateResult;
        }
        $agentGroupInfo = $validateResult['data'];

        // 资源拥有判断
        $userUuidList = array_column($agentGroupInfo, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $this->checkAuthByUserUuid(implode(',', $userUuidList), 'resmanagement');

        $this->dbBeginTransaction();
        // 删除客户端
        $in = "'" . implode("', '", $agentGroupUuids) . "'";
        $sql = "DELETE FROM bd_agent_group WHERE group_uuid in ($in) ";
        $deleteExec = $this->dbExec($sql);

        // 移动到默认分组中
        $agentGroupType = xphp_get_config('client', 'AGENT_GROUP_TYPE', 'resources');
        $sql = "SELECT group_uuid FROM bd_agent_group WHERE group_type = ? ";
        $groupData = $this->dbSelect($sql, [$agentGroupType['system']]);
        $sql = "UPDATE bd_agent SET group_uuid = ? WHERE group_uuid IN ($in) ";
        $deleteExec = $deleteExec && $this->dbExec($sql, [$groupData[0]['group_uuid']]);
        if (!$deleteExec) {
            $this->dbRollBack();
            return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_DELETE_ERROR'), false, 0);
        }
        $this->dbCommit();

        $detail = array_map(function ($group) {
            return sprintf(xphp_get_lang('WEB_AGENT_GROUP_DELETE_DETAIL'), $group['group_name']);
        }, $agentGroupInfo);
        return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_DELETE_LIST_SUCCESS'), true, 200, $detail);
    }

    /**
     * 验证删除客户端分组, 返回客户端分组信息
     * 1. 验证客户端分组是否存在
     * 2. 默认分组不能被删除
     * 3. 验证客户端分组关联的客户端是否在任务中
     * @param array $agentGroupUuids 客户端分组uuid列表
     * @return array
     */
    private function validateDeleteClientGroup(array $agentGroupUuids): array
    {
        // 验证客户端分组是否都存在
        $in = "'" . implode("', '", $agentGroupUuids) . "'";
        $sql = "SELECT * FROM bd_agent_group bag WHERE group_uuid IN ($in) ";
        $agentGroupInfo = $this->dbSelect($sql);
        if (!is_array($agentGroupInfo) || count($agentGroupUuids) != count($agentGroupInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT_GROUP'), false, 0);
        }

        // 不能删除默认分组
        $sql = "SELECT group_uuid FROM bd_agent_group WHERE group_type = ? ";
        $defaultAgentGroupInfo = $this->dbSelect(
            $sql,
            [xphp_get_config('client', 'AGENT_GROUP_TYPE', 'resources')['system']]
        );
        if (!$defaultAgentGroupInfo || !is_array($defaultAgentGroupInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NOT_DEFAULT_GROUP'), false, 0);
        }
        if (in_array($defaultAgentGroupInfo[0]['group_uuid'], $agentGroupUuids)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_DELETE_DEFAULT'), false, 0);
        }

        // 检查客户端分组是否在任务中，有任务不能删除
        // 查询客户端列表
        $sql = "SELECT agent_uuid FROM bd_agent WHERE group_uuid IN ($in) ";
        $agentInfo = $this->dbSelect($sql);
        if (!$agentInfo || !is_array($agentInfo)) {
            // 没有关联客户端, 直接返回
            return $this->sendResult('', true, 200, $agentGroupInfo);
        }
        // 查询任务列表
        $agentUuids = array_column($agentInfo, 'agent_uuid');
        $agentUuidsIn = "'" . implode("', '", $agentUuids) . "'";
        $sql = "SELECT task_uuid FROM bd_task WHERE agent_uuid IN ($agentUuidsIn) ";
        if ($this->dbSelect($sql)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_GROUP_CHECK_TASK'), false, 0);
        }
        return $this->sendResult('', true, 200, $agentGroupInfo);
    }

    /**
     * @param ?array $file 接收$_FILES里面的内容
     * @return array
     */
    public function resolveClientDeployFile(?array $file): array
    {
        if (is_null($file)) {
            // 表示没有上传文件
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_FILE'), false, 0);
        }
        $excelInfo = [];
        // 需要读取的列，这里采用固定位置读取
        $readColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $reader = IOFactory::load($file['tmp_name']);
        // 获取所有所有表格(Sheet)迭代器
        $objInfo = $reader->getWorksheetIterator();
        foreach ($objInfo as $sheet) {
            // 获取这个表格的所有行(Row)的迭代器
            $sheetInfo = $sheet->getRowIterator();
            foreach ($sheetInfo as $row) {
                // excel的行号是从1开始编码。第一行为表名，第二行为表头，第三行开始才是数据
                if ($row->getRowIndex() < 3) {
                    continue;
                }
                // 获取这一行的所有单元格(Cell)的迭代器
                $rowInfo = $row->getCellIterator();
                $readData = [];
                $flag = false;
                foreach ($rowInfo as $key => $cell) {
                    // 只读取指定列
                    if (!in_array($key, $readColumns)) {
                        continue;
                    }
                    // 这里需要去除两端的空白字符
                    $cellValue = trim($cell->getValue());
                    // 这里的意思是只要有一个单元格为空，就不处理这一行。保持之前逻辑
                    if (!$cellValue) {
                        $flag = true;
                        break;
                    }
                    $readData[] = $cellValue;
                }
                if ($flag) {
                    continue;
                }
                $excelInfo[] = [
                    'ip' => $readData[0],
                    'os_type' => $readData[1],
                    'username' => $readData[2],
                    'password' => $readData[3],
                    'nickname' => $readData[4],
                    'net_model' => (int)$readData[5],
                    'port' => (int)$readData[6],
                    'client_transport_port' => 23101, // 传输端口默认为23101
                    // 这里硬编码写死，部署模板的枚举值【切换、不切换、Switch、Do Not Switch】
                    'auto_change_network_flag' => $readData[7] == xphp_get_lang('UI_AGENT_RESOLVE_EXCEL_SWITCH1'),
                ];
            }
        }
        return $this->sendResult('', true, 200, [
            'rows' => $excelInfo,
            'total' => count($excelInfo)
        ]);
    }

    /**
     * 加载客户端实例
     * 业务逻辑
     * 1. 调用后台接口，获取实例列表
     * 2. 书库查询当前认证的实例列表
     * 3. 如果后台返回有实例列表，就以这个实例列表为主，将当前认证的实例列表修改到后台返回的实例列表上
     * 4. 如果后台没有实例列表，就当前认证的实例列表返返回
     * @param string $agentsUuid    客户端uuid
     * @param int    $dbType        数据库类型
     * @param array  $agentUuidList 集群关联的用户uuid
     * @return array
     */
    public function loadClientInstance(string $agentsUuid, int $dbType, array $agentUuidList): array
    {
        /**
         * 处理逻辑
         * 1. 查询当前已认证的实例
         * 2. 获取后台实例
         * 3. 如果后台实例不为空，则将当前实例合并到后台实例中；为空，不做处理
         */
        $opcodeName = 'DB_CLIENT_OP_GET_INSTANCE_LIST';
        $operate = $this->dbProtectOpcode->getOpcodeDes($opcodeName);
        if (!$this->dbSelect("SELECT agent_uuid FROM bd_agent WHERE agent_uuid = ?", [$agentsUuid])) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }
        $sql = "SELECT app_name, app_detail, app_version, app_auth_type,
                    app_username, app_password, UNIX_TIMESTAMP(update_time) AS update_time,
                    app_uuid, true AS auth_flag, cluster_flag, cluster_uuid, cluster_name, cluster_service_ip,
                    app_listen_ip AS listen_ip, install_app_username
                FROM bd_agent_app
                WHERE agent_uuid = ? AND app_type = ? ORDER BY update_time DESC ";

        // 获取当前认证的实例列表
        $agentAppData = $this->dbSelect($sql, [$agentsUuid, $dbType]);
        if (!is_array($agentAppData)) {
            $agentAppData = [];
        }
        $clusterUuidList = array_column($agentAppData, 'cluster_uuid');
        $clusterUuidList = array_values(array_filter(array_unique($clusterUuidList)));
        if ($clusterUuidList) {
            $clusterUuids = "'" . implode("', '", $clusterUuidList) . "'";
            $sql = "SELECT app_name, agent_uuid, cluster_uuid, app_listen_ip AS listen_ip
                    FROM bd_agent_app
                    WHERE cluster_uuid IN ($clusterUuids) AND app_type = ? ";
            $clusterAppData = $this->dbSelect($sql, [$dbType]);
            if (!is_array($clusterAppData)) {
                $clusterAppData = [];
            }
            $clusterAppMap = [];
            foreach ($clusterAppData as $clusterApp) {
                $clusterAppMap[$clusterApp['cluster_uuid']][] = $clusterApp;
            }
            foreach ($agentAppData as $index => $agentApp) {
                $agentAppData[$index]['cluster_app_info'] = [];
                if ($agentApp['cluster_uuid'] && isset($clusterAppMap[$agentApp['cluster_uuid']])) {
                    foreach ($clusterAppMap[$agentApp['cluster_uuid']] as $clusterApp) {
                        $agentAppData[$index]['cluster_app_info'][] = [
                            'agent_uuid' => $clusterApp['agent_uuid'],
                            'instance_name' => $clusterApp['app_name'],
                            'listen_ip' => $clusterApp['listen_ip'],
                        ];
                    }
                }
            }
        }

        $excludeLoadType = [
            xphp_get_config('db', 'DB_TYPE')['MYSQL'],
            xphp_get_config('db', 'DB_TYPE')['MARIA']
        ];
        $instanceList = $agentAppData;
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        if (!in_array($dbType, $excludeLoadType)) {
            // 调用后台接口，获取实例列表
            $serviceRet = $this->service()->getInstanceService($dbType, $agentUuidList, $agentsUuid);
            if (!$serviceRet['result']) {
                $instanceList = [];
                // 这里不直接报错，返回现有认证的实例
                $this->muOpResult(false, $operate, $serviceRet['msg'], 0, $serviceRet['errorCode']);
                return $this->sendResult('failed', false, 0);
            } else {
                $instanceList = $serviceRet['msg']['instance_list'];
            }
            if ($instanceList) {
                $tmpList = [];
                foreach ($instanceList as $instance) {
                    $instance['instance'] = trim($instance['instance']);
                    $isAuth = false;
                    foreach ($agentAppData as $agentApp) {
                        if ($agentApp['app_name'] == $instance['instance']) {
                            $tmpList[] = $agentApp;
                            $isAuth = true;
                            break;
                        }
                    }
                    if (!$isAuth) {
                        $tmpList[] = [
                            'app_name' => $instance['instance'],
                            'app_version' => $nullSpace,
                            'app_username' => $nullSpace,
                            'app_password' => '',
                            'app_auth_type' => 0,
                            'app_detail' => '{}',
                            'update_time' => null,
                            'app_uuid' => '',
                            'auth_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
                            'cluster_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
                            'cluster_uuid' => '',
                            'cluster_name' => '',
                            'cluster_service_ip' => '',
                            'cluster_app_info' => [],
                            'listen_ip' => '',
                            'install_app_username' => '',
                        ];
                    }
                }
                $instanceList = $tmpList;
            } else {
                // 如果后台获取到的实例为空，以当前认证的实例为主
                $instanceList = $agentAppData;
            }
        }
        $retInstanceList = ['rows' => [], 'total' => count($instanceList)];
        $taskData = $this->getAppTaskData($agentsUuid, $dbType);
        foreach ($instanceList as $instance) {
            $instanceClusterPath = '';
            $appDetail = json_decode($instance['app_detail'], true);
            if ($appDetail && isset($appDetail['cluster_path']) && $appDetail['cluster_path']) {
                $instanceClusterPath = $appDetail['cluster_path'];
            }
            $taskInfo = [];
            foreach ($taskData as $task) {
                if ($instance['app_name'] == $task['instance_name']) {
                    $taskInfo[] = [
                        'db_name' => $task['db_name'],
                        'instance_name' => $task['instance_name'],
                        'task_uuid' => $task['task_uuid'],
                        'task_name' => $task['task_name'],
                        'task_type' => $task['task_type'],
                        'task_status' => $task['task_status'],
                    ];
                }
            }
            $clusterFlag = v1_parse_flag_to_bool($instance['cluster_flag']) && $instance['cluster_uuid'];
            $password = $instance['app_password'] ? base64_encode(v1_pt_pass_decrypt($instance['app_password'])) : '';
            $retInstanceList['rows'][] = [
                'instance_name' => $instance['app_name'],
                'dbname' => $instance['app_name'],
                'db_type' => $dbType,
                'db_type_des' => xphp_get_config('db', 'DB_TYPE_DES')[$dbType],
                'version' => $instance['app_version'] ?: xphp_get_config('app', 'NULLSPACE'),
                'username' => $instance['app_username'],
                'password' => $password,
                'verify_type' => (int)$instance['app_auth_type'],
                'verify_time' => $this->parseDate($instance['update_time']),
                'instance_cluster_path' => $instanceClusterPath,  // 实例集群路径
                'app_uuid' => $instance['app_uuid'],
                'auth_flag' => v1_parse_flag_to_bool($instance['auth_flag']),
                'cluster_flag' => $clusterFlag,
                'cluster_uuid' => $instance['cluster_uuid'],
                'cluster_name' => $instance['cluster_name'],
                'cluster_service_ip' => $instance['cluster_service_ip'],
                'cluster_app_info' => $instance['cluster_app_info'],
                'listen_ip' => $instance['listen_ip'],
                'install_db_username' => $instance['install_app_username'],
                'detail' => $appDetail,
                'task_info' => $taskInfo,
            ];
        }
        return $this->sendResult('', true, 200, $retInstanceList);
    }

    /**
     * 获取任务信息
     * 1. 备份任务信息
     * 2. 恢复任务信息
     *  a. 恢复任务有“恢复最新备份点”
     * @param string $agentUuid 客户端uuid列表
     * @param int    $dbType    数据库类别
     * @return array
     */
    private function getAppTaskData(string $agentUuid, int $dbType): array
    {
        $agentUuidList = $this->getAppClusterAllAppUuid([$agentUuid]);
        $ret = [];
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $allowTaskTypeList = [$allTaskType['DB_BACKUP'], $allTaskType['DB_RECOVERY'], $allTaskType['DRILL']];
        $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
        $allowTaskTypes = "'" . implode("', '", $allowTaskTypeList) . "'";
        $sql = "SELECT bt.task_uuid, bt.task_name, bt.task_status, bt.task_type,
                    dl.instance_name, dl.db_name
                FROM bd_task bt
                    INNER JOIN bd_task_agent_list btal ON bt.task_uuid = btal.task_uuid
                    LEFT JOIN db_list dl ON bt.task_uuid = dl.task_uuid
                    INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                WHERE btal.agent_uuid IN ($agentUuids) AND bt.task_type IN ($allowTaskTypes)
                    AND dt.db_type = ? ";
        $data = $this->dbSelect($sql, [$dbType]);
        if (!is_array($data)) {
            $data = [];
        }
        foreach ($data as $row) {
            $ret[] = [
                'task_uuid' => $row['task_uuid'],
                'task_name' => $row['task_name'],
                'task_status' => $row['task_status'],
                'task_type' => $row['task_type'],
                'instance_name' => $row['instance_name'],
                'db_name' => $row['db_name'],
            ];
        }
        return $ret;
    }

    /**
     * 刷新客户端
     * @param string $agentsUuid 客户端uuid
     * @return array
     */
    public function refreshClient(string $agentsUuid): array
    {
        $opcodeName = 'NODE_AGENT_OP_REFRESH';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $agentInfo = $this->dbSelect("SELECT * FROM bd_agent WHERE agent_uuid = ?", [$agentsUuid]);
        if (!$agentInfo) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        // 权限验证
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        if ($agentInfo[0]['agent_type'] == $allAgentType['APPLIANCE']) {
            $this->checkAuthBySourceUuid($agentsUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['APPLICE']);
        } else {
            $this->checkAuthBySourceUuid($agentsUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);
        }

        $refreshResult = $this->service()->refreshClientService($agentsUuid);
        if (!$refreshResult['result']) {
            $this->muOpResult(false, $operate, $refreshResult['msg'], 0, $refreshResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_REFRESH_ERROR'), false, 0);
        }
        $detailOne = sprintf(
            xphp_get_lang('WEB_AGENT_REFRESH_DETAIL'),
            $agentInfo[0]['hostname'] . '(' . $agentInfo[0]['ip'] . ')'
        );
        return $this->sendResult(xphp_get_lang('WEB_AGENT_REFRESH_SUCCESS'), true, 200, [$detailOne]);
    }

    /**
     * 获取客户端的下载列表
     * FIXME: 等web_ui接口移至web_ng后再修改
     * @return array
     */
    public function getDownloadPackage(): array
    {
        // 获取所有的安装包
        $agentHandler = new Agent();
        $allPackages = $agentHandler->getAllPackagesInfo();
        // 获取客户端安装包
        $agentVendor = xphp_get_config('vendor', 'AGENT_VENDOR');
        // 客户端插件
        $clientVendor = xphp_get_config('vendor', 'CLIENT_VENDOR');
        $resultPackages = [
            'vm' => [],
            'host' => [],
        ];

        // 虚拟机插件
        foreach ($agentVendor as $eachVendor) {
            foreach ($eachVendor['version'] as $index => $version) {
                if ('#' != $version['value']) {
                    $eachVendor['version'][$index]['value'] = $allPackages[$version['value']];
                }
            }
            $resultPackages['vm'][] = [
                'name' => $eachVendor['text'],
                'versions' => array_map(function ($version) {
                    return ['name' => $version['text'], 'src' => $version['value']];
                }, $eachVendor['version']),
            ];
        }
        // 虚拟机过滤
        $releaseHypervisor = array();
        $extension = (new \app\v1\system\v0\logic\Index())->getExtensionLicense();
        if ($extension) {
            $releaseHypervisor = $extension['v'];
        } else {
            $configFile = xphp_get_config('app', 'SPECIAL_DIR') . xphp_get_config('app', 'SPECIAL_CONFIG');
            if (is_file($configFile)) {
                $content = file_get_contents($configFile);
                $config = json_decode($content, true);
                $releaseHypervisor = $config['RELEASE_HYPERVISOR'];
            }
        }

        // 根据需要虚拟化屏蔽相应虚拟化插件
        if ($releaseHypervisor) {
            $newAgentVendor = [];
            foreach ($resultPackages['vm'] as $eachVendor) {
                foreach ($releaseHypervisor as $hypervisor) {
                    if (xphp_get_config('vm', 'VMHYPERVISORDES')[(int)$hypervisor] == $eachVendor['name']) {
                        $newAgentVendor[] = $eachVendor;
                    }
                }
            }
            $resultPackages['vm'] = $newAgentVendor;
        }

        // 主机插件
        foreach ($clientVendor as $eachVendor) {
            if ('#' != $eachVendor['value']) {
                $eachVendor['value'] = $allPackages[$eachVendor['value']];
            }
            $resultPackages['host'][] = [
                'name' => $eachVendor['text'],
                'src' => $eachVendor['value'],
            ];
        }
        return $this->sendResult('', true, 200, $resultPackages);
    }

    /**
     * 获取传输代理域名解析配置
     * @param string $agentUuid 代理uuid
     * @return array
     */
    public function queryAgentDomainConfig(string $agentUuid): array
    {
        $nodeHandler = new Node();
        $nodeUuid = $nodeHandler->getLocalNodeUUID();
        $msg = array(
            'agent_uuid' => $agentUuid,
        );
        $opName = 'NODE_AGENT_OP_QUERY_DOMAIN_CONFIG';
        $mbResult = $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg), true);
        return $mbResult['result'] ? $this->sendResult('', true, 200, $mbResult['msg'])
            : $this->sendResult('', false, 0);
    }

    /**
     * 更新传输代理域名解析配置
     * @param array $params 参数
     * @return array
     */
    public function updateAgentDomainConfig(array $params): array
    {
        $nodeHandler = new Node();
        $nodeUuid = $nodeHandler->getLocalNodeUUID();
        $msg = array(
            'agent_uuid' => $params['agent_uuid'],
            'domain_config' => $params['domain_config'],
        );
        $opName = 'NODE_AGENT_OP_MODIFY_DOMAIN_CONFIG';
        $mbResult = $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg));
        $params['agents_uuid'] = $params['agent_uuid'];
        $mbResult2 = $this->service()->updateClientService($params);
        return $mbResult['result'] && $mbResult2['result'] ? $this->sendResult('', true)
            : $this->sendResult('', false, 0);
    }

    /**
     * 获取对应虚拟化插件版本
     * @param array $params 参数
     * @return array[]
     */
    public function getAgentVersions(array $params): array
    {
        $name = $params['name'];
        $agentVendor = xphp_get_config('vendor', 'AGENT_VENDOR');
        $agentHandler = new Agent();
        $agent = $agentHandler->getAllPackagesInfo();
        //替换厂商和版本配置中的安装包路径
        $info = array();
        foreach ($agentVendor as $k1 => $eachVendor) {
            if ($eachVendor['text'] == $name) {
                foreach ($eachVendor['version'] as $k2 => $version) {
                    $value = $version['value'];
                    if ('#' != $version['value']) {
                        //如果是"#"就不替换,如果指定了安装包名就替换
                        $agentItem = $agent[$version['value']];
                        $value = is_array($agentItem) ? $agentItem['fileName'] : $agentItem;
                    }
                    $info[] = array(
                        'text' => $version['text'],
                        'value' => $value
                    );
                }
            }
        }

        return ['versions' => $info];
    }

    /**
     * 扫描客户端磁盘文件
     * @param string $agentsUuid 客户端uuid
     * @param array  $params     参数
     * @return array
     */
    public function scanAgentDiskFiles(string $agentsUuid, array $params): array
    {
        $startIndex = intval($params['search_index']);
        $limitCount = intval($params['limit_count']);
        $dirPath = $params['dir_path'];
        $parentPath = $params['parent_path'];
        $searchFileName = $params['search_file_name'];
        $codeType = 2;
        if ($params['code_type']) {
            $codeType = $params['code_type'];
        }
        $msg = [
            'agent_uuid' => $agentsUuid,
            'search_index' => $startIndex,
            'limit_count' => $limitCount,
            'search_file_name' => $searchFileName,
            'dir_path' => $dirPath,
            'code_type' => $codeType,
        ];
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        if ($params['select_mode'] == xphp_get_config('client', 'FILE_TREE_SELECT_MODE', 'resources')['DIRECTORY']) {
            $opName = 'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST';
        }
        $operate = $this->nodeOpcode->getOpcodeDes($opName);
        $mbResult = $this->service()->scanClientDiskFilesService($msg, $opName);
        if (!$mbResult['result']) {
            $this->muOpResult(false, $operate, '', '', $mbResult['errorCode']);
            return $this->sendResult('', false, 0);
        }
        $rows = [];
        foreach ($mbResult['msg']['item_list'] as $item) {
            $rows[] = [
                'agent_uuid' => $agentsUuid,
                'filepath' => $item['item_path'],
                'filename' => $item['item_name'],
                'filetype' => intval($item['item_type']),
                'parent_path' => $parentPath,
                'checked' => false,
                'open' => false,
            ];
        }

        /**
         * 选中操作
         * 1. 用于初次扫描
         * 2. 解析选择路径
         * 3. 逐级遍历已解析的路径
         * 4. 找到即结束
         * 5. 遍历完成未找到，不勾选
         */
        $selectedPath = $params['selected_path'] ?? '';
        if ($selectedPath) {
            $loadItemList = [];
            $pathFragment = explode('/', $selectedPath);
            $matchPid = $rows[0]['filepath'];
            foreach ($pathFragment as $index => $path) {
                if ($index == 0) {  // 表示选中了顶级目录
                    if ($path . '/' == $selectedPath) {
                        $rows[0]['checked'] = true;
                        break;
                    }

                    $loadItemList = $this->loadAgentAllPathItem($opName, $codeType, $agentsUuid, $path . '/');
                    if (is_string($loadItemList)) {
                        return $this->sendResult($loadItemList, false, 0);
                    }
                    $rows[0]['open'] = true;
                    $rows[0]['checked'] = true;
                    continue;
                } elseif ($index == count($pathFragment) - 1) {
                    if (!$path) {  // 搜索的可能是路径
                        continue;
                    }
                }
                $matchItem = null;
                foreach ($loadItemList as $loadItem) {
                    $node = [
                        'agent_uuid' => $agentsUuid,
                        'filepath' => $loadItem['item_path'],
                        'filename' => $loadItem['item_name'],
                        'filetype' => intval($loadItem['item_type']),
                        'parent_path' => $matchPid,
                        'checked' => false,
                        'open' => false,
                    ];
                    if ($loadItem['item_name'] == $path) {
                        $matchItem = $loadItem;
                        $node['checked'] = true;
                        if (
                            !(
                                $loadItem['item_type'] == 1 ||
                                ($index == count($pathFragment) - 2 && !$pathFragment[$index + 1])
                            )
                        ) {
                            $node['open'] = true;
                        }
                    }
                    $rows[] = $node;
                }
                if (!$matchItem) {
                    break;
                }
                if ($matchItem['item_type'] == 1) {
                    continue;
                }
                $matchPid = $matchItem['item_path'];
                $loadItemList = $this->loadAgentAllPathItem($opName, $codeType, $agentsUuid, $matchItem['item_path']);
                if (is_string($loadItemList)) {
                    return $this->sendResult($loadItemList, false, 0);
                }
            }
        }

        // 排序
        return $this->sendResult('', true, 200, [
            'rows' => $this->sortAgentPath($rows),
            'total' => count($rows),
            'more_flag' => $mbResult['msg']['is_search_finish'] == xphp_get_config('app', 'FLAG')['UNSET'],
            'search_file_name' => $mbResult['msg']['search_file_name'],
            'current_next_index' => $mbResult['msg']['current_next_index'],
            'dir_path' => $dirPath,
        ]);
    }

    /**
     * 文件树排序
     * @param array $rows 文件树
     * @return array
     */
    private function sortAgentPath(array $rows): array
    {

        $folderList = [];
        $fileList = [];
        $moreList = [];
        foreach ($rows as $node) {
            if (1 == $node['filetype']) {  // 文件
                $fileList[] = $node;
            } elseif (2 == $node['filetype']) {  // 目录
                $folderList[] = $node;
            } elseif (3 == $node['filetype']) {  // 盘符
                $folderList[] = $node;
            }
        }

        // 排序
        v1_secondary_array_sort($folderList, 'title');
        v1_secondary_array_sort($fileList, 'title');

        return array_merge($folderList, $fileList, $moreList);
    }

    /**
     * 加载目录下所有的文件
     * @param string $opName    操作码
     * @param int    $codeType  code type
     * @param string $agentUuid 客户端uuid
     * @param string $searchDir 搜索的文件
     * @return array|string
     */
    private function loadAgentAllPathItem(string $opName, int $codeType, string $agentUuid, string $searchDir)
    {
        $ret = [];
        $msg = array(
            'agent_uuid' => $agentUuid,
            'search_index' => 0,
            'limit_count' => 20,
            'search_file_name' => '',
            'dir_path' => $searchDir,
            'code_type' => $codeType,
        );
        do {
            $mbResult = $this->service()->scanClientDiskFilesService($msg, $opName);
            if (!$mbResult['result']) {
                $operate = $this->nodeOpcode->getOpcodeDes($opName);
                return $this->muOpResult(false, $operate, '', '', $mbResult['errorCode']);
            }
            if (!isset($mbResult['msg']['item_list']) || !is_array($mbResult['msg']['item_list'])) {
                break;
            }
            $ret = array_merge($ret, $mbResult['msg']['item_list']);
            if ($mbResult['msg']['is_search_finish'] == xphp_get_config('app', 'FLAG')['SET']) {
                break;
            }
            $msg['search_index'] = $mbResult['msg']['current_next_index'];
            $msg['search_file_name'] = $mbResult['msg']['search_file_name'];
        } while ($mbResult['msg']['is_search_finish'] == xphp_get_config('app', 'FLAG')['UNSET']);
        return $ret;
    }

    /**
     * 在指定目录下搜索客户端的磁盘文件
     * @param string $agentsUuid 客户端uuid
     * @param array  $params     参数
     * @return array
     */
    public function searchAgentDiskFiles(string $agentsUuid, array $params): array
    {
        $searchValue = $params['search_value'];
        $searchRet = [];

        $ret = $this->scanAgentDiskFiles($agentsUuid, $params);
        if (!$ret['success']) {
            return $ret;
        }
        $allNodes = $ret['data']['rows'];

        foreach ($allNodes as $node) {
            if (strpos($node['filename'], $searchValue) !== false) {
                $searchRet[] = $node;
            }
        }
        $ret['data']['rows'] = $searchRet;
        $ret['data']['total'] = count($searchRet);

        return $this->sendResult('', true, 200, $ret['data']);
    }

    /**
     * 得到可下载的代理地址
     * @param array $params
     */
    public function getDoloadAgentName($params)
    {
        $pfDes = xphp_get_desc('Pf', 'AGENT_TYPE_DES');
        $vendor = xphp_get_config('vendor');
        $agent = (new Agent())->getAllPackagesInfo();
        $agentType = $vendor['AGENT_TYPE'];
        $agentVendor = $vendor['AGENT_VENDOR'];
        $dbcdpVendor = $vendor['DBREALTIME_CLIENT'];

        //客户端插件
        $clientOs = $vendor['CLIENT_OS'];
        $clientVendor = array();
        foreach ($clientOs as $os) {
            $temp = array();
            if (empty($os['version'])) {
                $temp['text'] = $os['text'];
                $temp['value'] = $os['value'];
                $clientVendor[] = $temp;
            } else {
                foreach ($os['version'] as $version) {
                    $temp['text'] = $version['text'];
                    $temp['value'] = $version['value'];
                    $clientVendor[] = $temp;
                }
            }
        }

        //获取是否授权
        $sql = "select authorized_flag from bd_system";
        $data = $this->dbSelect($sql);
        $authflag = intval($data[0]['authorized_flag']);
        // 授权控制操作系统
        $licenceOs = (new \app\v1\system\v0\logic\Index())->getExtensionLicense()['os'];  // 授权的操作系统
        if ($authflag != xphp_get_config('app', 'FLAG','',true)['UNSET'] && empty($licenceOs)) {
            // 系统授权其他状态(正常,异常,过期等),如果extendLicense['os']为空,显示所有操作系统
            foreach ($clientOs as $os) {
                $licenceOs[] = $os['licence_key'];
                if (!empty($os['version'])) {
                    foreach ($os['version'] as $version) {
                        $licenceOs[] = $version['licence_key'];
                    }
                }
            }
        }

        // 筛选出整数（操作系统）
        // 筛选出小数（版本架构）
        $osArr = array();
        $versionArr = array();
        foreach ($licenceOs as $os) {
            if (is_int($os)) {
                $osArr[] = $os;
            } elseif (is_float($os)) {
                $versionArr[] = $os;
            }
        }
        $arr = array();
        // 循环CLIENT_OS
        foreach ($clientOs as $client) {
            $temp = array();
            // 客户端
            if (in_array($client['licence_key'], $osArr)) {
                $temp['text'] = $client['text'];
                if ($client['text'] == 'Windows') {
                    $temp['version'] = $client['version'];
                }
                $temp['value'] = $client['value'];
                $temp['oversea_flag'] = $client['oversea_flag'];
                $temp['licence_key'] = $client['licence_key'];
                foreach ($client['version'] as $version) {
                    // 再循环version数组
                    if (in_array($version['licence_key'], $versionArr)) {
                        $temp['version'][] = $version;
                    }
                }
                $arr[] = $temp;
            }
        }
        $clientOs = $arr;

        // 初始化版本架构
        $clientVersion = $clientOs[0]['version'];
        $versionArr = array();
        if (!empty($clientVersion)) {
            foreach ($clientVersion as $version) {
                $temp['text'] = $version['text'];
                $temp['value'] = $version['value'];
                $temp['licence_key'] = $version['licence_key'];
                $versionArr[] = $temp;
            }
        }
        //构造新的操作系统数组
        $dbTimingHandler = new Dbtiming();
        $dbCDPHandler = new DbCdp();
        $rpc = new DbRpc();
        //得到类型的列表
        $agentTypeDes = array();
        //判断文件模块是否授权
        $extension = (new \app\v1\system\v0\logic\Index())->getExtensionLicense();
        foreach ($agentType as $type) {
//            if (
//                xphp_get_config('app', 'FLAG')['UNSET']
//                &&
//                $authflag == xphp_get_config('app', 'FLAG')['UNSET']
//            ) {
//                // 系统未授权,不显示任何下载的操作系统
//                continue;
//            }
            $eachAgent = array(
                'text' => $pfDes[$type],
                'value' => $type,
                'href' => '#'
            );
            $flag = true;
            switch ($type) {
                case $vendor['AGENT_TYPE']['NODE']:
                    //添加节点链接
                    $eachAgent['href'] = $agent['node'];
                    break;
                case $vendor['AGENT_TYPE']['VM']:
                    break;
                case $vendor['AGENT_TYPE']['CLIENT']:
                    if ($authflag == xphp_get_config('app', 'FLAG')['UNSET']) {
                        continue;
                    }
                    break;
                case $vendor['AGENT_TYPE']['DBTIMING']:
                    $cmdStr = 'ps aux|grep daserver';
                    exec($cmdStr, $cmdinfo);
                    if (count($cmdinfo) > 2) {
                        if (!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime) {
                            $dbTimingHandler->getDBAuth(); //获取datapp认证
                        }
                        $dbTimingInfo = $dbTimingHandler->getDBLisenceInfo(); //获取datapp授权信息
                        if ($dbTimingInfo['state'] == 0) {
                            continue; //主机保护未授权不显示插件下载
                        }
                    } else {
                        continue;
                    }
                    break;
                case $vendor['AGENT_TYPE']['DBREALTIME']:
                    $cmdStr = 'ps aux|grep lzbackupsys';
                    exec($cmdStr, $cmdinfo);
                    if (count($cmdinfo) > 2) {
                        //是否获取cdp授权信息
                        $hostip = $_SERVER['SERVER_ADDR'];
                        $result = $rpc->getLicenseCenterAuthorInfo($hostip, array());
                        if ($result['result']) {
                            $data = $dbCDPHandler->getLicenseCenterAuthorInfo();
                            if ($data[0]['liceselflag'] != xphp_get_config('app', 'FLAG')['SET']) {
                                //数据库CDP未授权不显示插件下载
                                $flag = false;
                            }
                        } else {
                            $flag = false;
                        }
                    } else {
                        $flag = false;
                    }
                    break;
            }
            if ($flag) {
                $agentTypeDes[] = $eachAgent;
            }
        }
        //根据需要虚拟化屏蔽相应虚拟化插件
        $releaseHypervisor = array();
        if (!empty($extension)) {
            $releaseHypervisor = $extension['v'];
        } else {
            $configFile = xphp_get_config('app', 'SPECIAL_DIR') . xphp_get_config('app', 'SPECIAL_CONFIG');
            if (file_exists($configFile)) {
                $content = file_get_contents($configFile);
                $config = json_decode($content, true);
                $releaseHypervisor = $config['RELEASE_HYPERVISOR'];
            }
        }
        $allHypervisorDes = xphp_get_config('vm', 'VMHYPERVISORDES');
        if (!empty($releaseHypervisor)) {
            $agentVendorUsed = array();
            foreach ($agentVendor as $key => $eachVendor) {
                foreach ($releaseHypervisor as $hypervisor) {
                    if ($allHypervisorDes[intval($hypervisor)] == $eachVendor['text']) {
                        //如果配置有这个虚拟化就展开相应插件
                        $agentVendorUsed[] = $eachVendor;
                    }
                    continue;
                }
            }
            //使用配置所有的虚拟化包含的插件
            $agentVendor = $agentVendorUsed;
        }
        //操作系统对应的架构版本
        $agentOsUsed = array();
        $lang = $_SESSION['language'] ?? xphp_get_config('app', 'lang');
        foreach ($clientOs as $key => $eachOS) {
            // if ($lang != 'zh-cn' && $lang != 'zh-tw') {
            //     if (!$eachOS['oversea_flag']) {  // 海外不显示的操作系统
            //         continue;
            //     }
            // }
            $agentOsUsed[] = $eachOS;
        }
        //使用配置所有的虚拟化包含的插件
        $clientOs = $agentOsUsed;
        //数据库实时安装包
        foreach ($dbcdpVendor as $k1 => $version) {
            if ('#' != $version['value']) {
                //如果是"#"就不替换,如果指定了安装包名就替换
                $dbcdpVendor[$k1]['value'] = $agent[$version['value']];
            }
        }
        //数据库定时客户端
        $dbtimingClient = $vendor['DBTIMING_CLIENT'];
        $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
        $info = array(
            'type' => $agentTypeDes,
            'vendor' => $agentVendor,
            'softwareType' => $softwareType,
            'client' => $versionArr,
            'clientOs' => $clientOs,
            'dbtiming' => $dbtimingClient,
            'dbcdp' => $dbcdpVendor,
        );
        return json_encode($info);
    }

    /**
     * 组合下载链接
     * @params array 参数
     * @retrun string 下载链接
     */
    public function getDownloadLink($params)
    {
        $vendor = xphp_get_config('vendor');
        $agent = (new Agent())->getAllPackagesInfo();
        if ($params['type'] == 0) {
            //虚拟机代理
            //替换厂商和版本配置中的安装包路径
            $agentVendor = $vendor['AGENT_VENDOR'];
            $linkStr = '';
            foreach ($agentVendor as $k1 => $eachVendor) {
                if ($params['selectedText'] == $eachVendor['text']) {
                    foreach ($eachVendor['version'] as $k2 => $version) {
                        $value = $version['value'];
                        $text = $version['text'];
                        if ($params['version'] == $text) {
                            //如果是"#"就不替换,如果指定了安装包名就替换
                            $fileName = preg_replace('~^/[^/]+/~', '', $agent[$value]);
                            $return = array(
                                'linkStr' => $agent[$value],
                                'path' => '/usr/share/nginx/' . xphp_get_config('app', 'SYSTEM_INFO')['vendor'],
                                'fileName' => $fileName,
                                'showName' => $fileName,
                            );
                        }
                    }
                }
            }
        } elseif ($params['type'] == 2) {
            //文件代理
            //客户端插件
            $clientOs = $vendor['CLIENT_OS'];
            $clientVendor = array();
            foreach ($clientOs as $os) {
                $temp = array();
                if (empty($os['version'])) {
                    $temp['text'] = $os['text'];
                    $temp['value'] = $os['value'];
                    $clientVendor[] = $temp;
                } else {
                    foreach ($os['version'] as $version) {
                        $temp['text'] = $version['text'];
                        $temp['value'] = $version['value'];
                        $clientVendor[] = $temp;
                    }
                }
            }
            foreach ($clientVendor as $k1 => $eachVendor) {
                if ($params['selectedText'] == $eachVendor['text']) {
                    if ('#' != $eachVendor['value']) {
                        //如果是"#"就不替换,如果指定了安装包名就替换
                        if ('#' === $agent[$eachVendor['value']]) {
                            $return = array(
                                'linkStr' => '#',
                                'path' => '',
                                'showName' => '',  //下载后显示的文件名
                            );
                        } else {
                            $showName = $agent[$eachVendor['value']]['showName'];
                            $return = array(
                                'linkStr' => $agent[$eachVendor['value']]['fileName'],
                                'path' => '/usr/share/nginx/' . xphp_get_config('app', 'SYSTEM_INFO')['vendor'],
                                'showName' => $showName,  //下载后显示的文件名
                            );
                        }
                    }
                }
            }
        } elseif ($params['type'] == 4) {
            //数据库实时插件
            $dbcdpVendor = $vendor['DBREALTIME_CLIENT'];
            foreach ($dbcdpVendor as $k1 => $eachVendor) {
                if ($params['selectedText'] == $eachVendor['text']) {
                    if ('#' != $eachVendor['value']) {
                        //如果是"#"就不替换,如果指定了安装包名就替换
                        $fileName = preg_replace('~^/[^/]+/~', '', $agent[$eachVendor['value']]);
                        $return = array(
                            'linkStr' => $agent[$eachVendor['value']],
                            'path' => '/usr/share/nginx/' . xphp_get_config('app', 'SYSTEM_INFO')['vendor'],
                            'fileName' => $fileName,
                            'showName' => $fileName,
                        );
                    }
                }
            }
        }
        return $return;
    }

    /**
     * 获取客户端应用类型
     * @return array
     */
    public function getAgentAppType(): array
    {
        $extension = (new \app\v1\system\v0\logic\Index())->getExtensionLicense();
        $showAllDbFlag = false;
        $dbLicense = [];
        if (!isset($extension['db']) || !$extension['db']) {
            $showAllDbFlag = true;
        } else {
            $dbLicense = $extension['db'];
        }
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allDbTypeDes = xphp_get_config('db', 'DB_TYPE_DES');
        $allowDbTypeList = [];
        foreach ($allDbType as $key => $dbType) {
            if ($dbType == 0) {
                continue;
            }
            if ($showAllDbFlag || in_array($dbType, $dbLicense)) {
                $allowDbTypeList[] = [
                    'app_type' => $dbType,
                    'app_type_name' => $allDbTypeDes[$dbType],
                ];
            }
        }
        return $this->sendResult('', true, 200, [
            'rows' => $allowDbTypeList,
            'total' => count($allowDbTypeList),
        ]);
    }
}
