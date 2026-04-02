<?php

namespace app\v1\dbcdp\v0\logic;

use app\v1\common\logic\Base;
use app\v1\db\v0\logic\DbBackUp;
use app\v1\resources\v0\logic\Client;
use xphp\Email;

/**
 * note          数据库实时 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpData extends Base
{
    /**
     * @param array $params 获取恢复数据列表入参
     * @return array|false
     */
    public function getRestoreDataList(array $params = [])
    {
        $sql = "select distinct cddbi.source_agent_uuid,cddbi.target_agent_uuid, cddbi.delay_time,cddbi.last_redo_replay_time,
                ba.group_uuid, bag.group_name, bag.group_uuid, ba.online_flag,
                cddbi.target_cluster_flag, ba.online_flag from cdp_db_dr_backup_info cddbi inner join
                bd_agent ba on cddbi.target_agent_uuid = ba.agent_uuid inner join bd_agent_group bag
                on ba.group_uuid = bag.group_uuid inner join bd_backup_timepoint bbt on
                bbt.timepoint_uuid = cddbi.timepoint_uuid where bbt.user_uuid in (?) group by cddbi.id";
        $data = $this->dbSelect($sql, array(xphp_get_user_info()['userUuid']));
        if (empty($data)) {
            return false;
        }

        foreach ($data as $d) {
            if ($d['last_redo_replay_time'] == '0000-00-00 00:00:00') {
                continue;
            }
            $targetAgentInfo = $this->getHostInfo($d['target_agent_uuid']);  //同步的目标机
            if (!$targetAgentInfo) {
                continue;
            }
            $targetClusterInfo = $this->getClusterInfo($d['target_agent_uuid']);

            $dataTargetAgentInfo = array(
                'group_name' => $d['group_name'],
                'group_uuid' => $d['group_uuid'],
                'target_agent_name' => $targetAgentInfo['agent_name'],
                'target_agent_ip' => $targetAgentInfo['ip'],
                'target_host_name' => $targetAgentInfo['host_name'],
                'target_agent_uuid' => $d['target_agent_uuid'],
                'source_agent_uuid' => $d['source_agent_uuid'],
                'target_agent_is_cluster' => v1_parse_flag_to_bool($d['target_cluster_flag']),
                'target_agent_is_online' => v1_parse_flag_to_bool($d['online_flag']),
                'online_status' => v1_parse_flag_to_bool($d['online_flag']),
                'in_task' => v1_parse_flag_to_bool(Client::instance()->inTask(
                    $d['target_agent_uuid'],
                    xphp_get_config('module')['MODULE_TYPE']['DB_CDP'],
                    xphp_get_config('task')['TASKTYPE']['CDP_DB_RECOVERY']
                )),
                'delay_time_flag' => $d['delay_time'] ? v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['SET']) : v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['UNSET']),
            );
            $dataTargetAgentInfo['cluster_info'] = array(
                'target_agent_cluster_uuid' => $targetClusterInfo['cluster_uuid'],
                'target_agent_cluster_service_ip' => $targetClusterInfo['cluster_service_ip'],
                'target_agent_cluster_name' => $targetClusterInfo['cluster_name']
                    ? $targetClusterInfo['cluster_name'] : '',
            );

            $list[] = array(
                'target_agent_info' => $dataTargetAgentInfo,
            );
        }
        return $list;
    }

    /**
     * 获取可恢复的数据库实例
     * @param array $params 参数
     * @return array
     */
    public function getRestoreInstances(array $params = [])
    {
        $sql = "SELECT 
                    distinct cddbi.timepoint_uuid,cddbi.source_agent_uuid,cddbi.target_agent_uuid, cddbi.delay_time,
                             cddbi.last_redo_replay_time,cddbi.target_cluster_flag,
                    ba.group_uuid, ba.online_flag,ba.agent_name,ba.hostname,ba.ip,ba.os_type,ba.net_model,
                    baa.app_name,baa.app_type,baa.app_uuid,baa.app_auth_type,
                    baa.app_listen_ip,baa.app_version,baa.app_detail,baa.cluster_uuid,
                    baa.cluster_name,baa.cluster_service_ip,baa.app_service_name,
                    cddbi.last_redo_replay_scn,cddbi.latest_recv_transaction_scn,cddbi.select_copy_users,cddbi.timepoint_type
				FROM cdp_db_dr_backup_info cddbi 
				INNER JOIN
                    bd_agent ba 
				ON cddbi.target_agent_uuid = ba.agent_uuid  
				INNER JOIN bd_backup_timepoint bbt ON
                    bbt.timepoint_uuid = cddbi.timepoint_uuid
				INNER JOIN bd_agent_app baa on ba.agent_uuid = baa.agent_uuid";
        // 权限判断
        if(v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'ba.agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
            $resourceUuidSql = v1_auth_get_users('dbcdpcopy');
            $sql .= " AND bbt.user_uuid IN ($resourceUuidSql) ";
        }
        $sql .= ' group by cddbi.id';
        $data = $this->dbSelect($sql);
        $rows = [];
        foreach ($data as $row) {
            $dbcdpRecoverInfo = $this->getDbcdpRecoverInfo($row['target_agent_uuid']);
            $dbcdpRecoverTask = $this->getRecoveryTask($row['target_agent_uuid']);
            $rows[] = [
                'db_type' => intval($row['app_type']),
                'instance_name' => $row['app_name'],
                'app_name' => $row['app_name'],
                'app_uuid' => $row['app_uuid'],
                'app_auth_type' => intval($row['app_auth_type']),
                'app_listen_ip' => $params['app_listen_ip'] ?: '',
                'app_detail' => $row['app_detail'],
                'app_type' => $row['app_type'],
                'app_version' => $row['app_version'],
                'source_agent_uuid' => $row['source_agent_uuid'],  // 同步任务源机的agent_uuid
                'cluster_info' => [
                    'cluster_flag' => v1_parse_flag_to_bool($row['target_cluster_flag']),
                    'cluster_uuid' => $row['cluster_uuid'],
                    'cluster_name' => $row['cluster_name'],
                    'cluster_service_ip' => $row['cluster_service_ip'],
                    'app_service_name' => $row['app_service_name'],
                    'app_name' => $row['app_name'],
                    'app_type' => $row['app_type'],
                    'app_version' => $row['app_version'],
                    'db_cdp_recover_info' => $dbcdpRecoverInfo,  // 有值才能作为恢复源 没值不能作为恢复源
                    'db_cdp_recover_task' => $dbcdpRecoverTask,
                    'timepoint_uuid' => $row['timepoint_uuid'],
                    'select_copy_users' => json_decode($row['select_copy_users']),
                    'timepoint_type' => $row['timepoint_type'],
                    'last_redo_replay_scn' => $row['last_redo_replay_scn'],
                    'latest_recv_transaction_scn' => $row['latest_recv_transaction_scn']
                ],
                'agent_info' => [
                    'app_name' => $row['app_name'],
                    'agent_uuid' => $row['target_agent_uuid'],
                    'agent_name' => $row['agent_name'],
                    'hostname' => $row['hostname'],
                    'ip' => $row['ip'],
                    'online_flag' => v1_parse_flag_to_bool($row['online_flag']),
                    'os_type' => $row['os_type'],
                    'net_model' => intval($row['net_model']),
                    'authorization_module' => [
                        'dbcdp' => true,
                    ],
                ],
                'db_cdp_recover_info' => $dbcdpRecoverInfo,  // 有值才能作为恢复源 没值不能作为恢复源
                'db_cdp_recover_task' => $dbcdpRecoverTask,
                'delay_time_flag' => $row['delay_time'] !== 0 ? true : false,
                'timepoint_uuid' => $row['timepoint_uuid'],
                'last_redo_replay_scn' => $row['last_redo_replay_scn'],
                'latest_recv_transaction_scn' => $row['latest_recv_transaction_scn'],
                'select_copy_users' => json_decode($row['select_copy_users']),
                'timepoint_type' => $row['timepoint_type'],
            ];
        }
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

    /**
     * @params $agentuuid
     * 是否可以作为恢复源机使用
     * 有值才能作为恢复源 没值不能作为恢复源
     * @return void
     */
    private function getDbcdpRecoverInfo($agentuuid)
    {
        $sql = "SELECT timepoint_uuid, backup_info_status
                FROM cdp_db_dr_backup_info CDDBI 
                WHERE CDDBI.target_agent_uuid = ?";
        $data = $this->dbSelect($sql,array($agentuuid));
        $info = array(
            'backup_info_status' => $data[0]['backup_info_status']
        );
        return $info;
    }

    /**
     * @params $agentuuid
     * 机器是否在恢复任务中
     */
    private function getRecoveryTask($agentuuid){
        $sql = "select bt.task_name,cddt.task_uuid from cdp_db_dr_task cddt 
				inner join bd_task bt on cddt.task_uuid = bt.task_uuid
				where cddt.source_agent_uuid = ?
				or cddt.target_agent_uuid = ?
				and bt.task_type = ?";
        $data = $this->dbSelect($sql,array($agentuuid,$agentuuid,xphp_get_config('task','TASKTYPE')['CDP_DB_RECOVERY']));
        $info = array(
            'task_name' => ''
        );
        if(!empty($data)){
            $info['task_name'] = $data[0]['task_name'];
        }
        return $info;
    }

    /**
     * @param $agentuuid :  客户端uuid
     * @return array|string[]
     */
    private function getHostInfo($agentuuid): array
    {
        $sql = "select agent_name,hostname,ip from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        if (empty($data)) {
            return $data;
        }

        if (!empty($data)) {
            $listInfo = array(
                'agent_name' => $data[0]['agent_name'],
                'host_name' => $data[0]['hostname'],
                'ip' => $data[0]['ip']
            );
        }
        return $listInfo;
    }

    /**
     * @param array $params :
     * @return array|false
     */
    public function getStandbyRestoreData($params = [])
    {
        $sql = "select cddbi.timepoint_uuid,cddbi.source_app_uuid,cddbi.source_agent_uuid,cddbi.target_agent_uuid,
                cddbi.target_app_uuid,cddbi.delay_time,
                cddbi.last_redo_replay_time,cddbi.latest_recv_transaction_time,
                cddbi.source_cluster_flag,cddtpi.completed_size
                from cdp_db_dr_backup_info cddbi
                inner join bd_backup_timepoint bbt on cddbi.timepoint_uuid = bbt.timepoint_uuid 
                inner join cdp_db_dr_task_progress_info cddtpi on
                cddtpi.task_uuid = bbt.task_uuid
                where cddbi.target_agent_uuid = ?";
        $data = $this->dbSelect($sql, array($params['restore_data_uuid']));
        if (empty($data)) {
            return false;
        }
        $list = array();
        $appInfo = $this->getHostAppInfo($data[0]['source_app_uuid']);
        $list[] = array(
            'timepoint_uuid' => $data[0]['timepoint_uuid'],
            'source_app_uuid' => $data[0]['source_app_uuid'],
            'source_agent_uuid' => $data[0]['source_agent_uuid'],
            'target_agent_uuid' => $data[0]['target_agent_uuid'],
            'target_app_uuid' => $data[0]['target_app_uuid'],
            'delay_time' => $data[0]['delay_time'],
            'last_redo_replay_time' => $data[0]['last_redo_replay_time'],
            'last_recv_transaction_time' => $data[0]['last_recv_transaction_time'],
            'completed_size' => $data[0]['completed_size'],
            'source_cluster_flag' => $data[0]['source_cluster_flag'],
            'app_info' => $appInfo,
        );
        return $list;
    }

    /**
     * 获取备机对应的应用信息
     * @param $appuuid : 应用UUID
     * @return array
     */
    private function getHostAppInfo($appuuid): array
    {
        $sql = "select app_name from bd_agent_app where app_uuid = ?";
        $data = $this->dbSelect($sql, array($appuuid));
        if (empty($data)) {
            return [];
        }
        $appInfo = array(
            'app_name' => $data[0]['app_name'],
        );
        return $appInfo;
    }

    /**
     * 获取集群
     * @param $agentuuid :  客户端uuid
     * @return array|string[]
     */
    private function getClusterInfo($agentuuid): array
    {
        $sql = 'select cluster_uuid,cluster_name,cluster_service_ip from bd_agent_app where agent_uuid = ?';
        $data = $this->dbSelect($sql, array($agentuuid));
        if (!$data) {
            return [];
        }
        $listInfo = array(
            'cluster_uuid' => $data[0]['cluster_uuid'],
            'cluster_name' => $data[0]['cluster_name'],
            'cluster_service_ip' => $data[0]['cluster_service_ip']
        );
        return $listInfo;
    }

    /**
     * 恢复源对应的同步主机信息
     * @param string $restoreDataUuid 数据源uuid
     * @return array
     */
    public function getRestoreDataAgentInfo(string $restoreDataUuid)
    {
        $sql = 'select ba.agent_uuid,ba.agent_name,ba.hostname,ba.ip,baa.cluster_uuid from bd_agent ba 
                inner join cdp_db_dr_backup_info cddbi on ba.agent_uuid = cddbi.source_agent_uuid 
				inner join bd_agent_app baa on baa.agent_uuid = cddbi.source_agent_uuid 
                where cddbi.target_agent_uuid = ?';
        $data = $this->dbSelect($sql, array($restoreDataUuid));
        $agentInfo = array();
        if (!empty($data)) {
            $agentInfo = array(
                'agent_uuid' => $data[0]['agent_uuid'],
                'agent_name' => $data[0]['agent_name'],
                'agent_ip' => $data[0]['ip'],
                'agent_hostname' => $data[0]['hostname'],
                'cluster_uuid' => $data[0]['cluster_uuid']
            );
        }
        return $agentInfo;
    }

    /**
     * 备份数据源相关联的主机信息
     * @param string $restoreDataUuid 数据源uuid
     * @return array
     */
    public function getRestoreDataSourceInfo(string $restoreDataUuid)
    {
        $sql = 'select distinct cddt.source_agent_uuid, ba.agent_name, ba.hostname,ba.ip from cdp_db_dr_task cddt 
                inner join cdp_db_dr_backup_info cddbi 
                on cddbi.source_agent_uuid = cddt.source_agent_uuid
                inner join bd_agent ba on cddt.source_agent_uuid = ba.agent_uuid where cddbi.target_agent_uuid = ?';
        $data = $this->dbSelect($sql, array($restoreDataUuid));
        if (!$data) {
            return false;
        }
        $agentInfo = array(
            'agent_uuid' => $data[0]['source_agent_uuid'],
        );
        return $agentInfo;
    }
}
