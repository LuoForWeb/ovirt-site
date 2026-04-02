<?php

namespace app\v1\tape\v0\logic;

use app\v1\opcode\NodeOpcode;
use app\v1\common\logic\Base;

/**
 * note         磁带设备操作 logic
 */
class TapeController extends Base
{
    /**
     * 获取操作名
     * @param $opCode opccode
     * @return string
     */
    public function getUnifyOpcodeDes($opCode)
    {
        $nodeOpcode = new NodeOpcode();
        return $nodeOpcode->getOpcodeDes($opCode);
    }

    /**
     * 扫描磁带库 单个/批量
     * @param string $params 带库名称
     * @return json
     */
    public function scanTapeLib($params)
    {
        //扫描磁带库的逻辑,tape_lib_name为空扫描所有磁带库
        $tapeLibName = $params['tape_lib_name'];
        $node = $params['node_uuid'];
        if ($tapeLibName != '') {
            //不为空验证是否存在单个磁带库
            $sql = "select id from bd_tape_library where name = ? ";
            $libInfo = $this->dbSelect($sql, [$tapeLibName]);
            if (!$libInfo) {
                return $this->sendResult(xphp_get_lang('WEB_NODE_NO_TAPE_LIB'), false, 400);
            }
        } else {
            $sql = "select id from bd_tape_library ";
            
            $data = $this->dbSelect($sql);
        }
        if (!empty($node)) {
            $result = $this->service()->scanTapeLibService($tapeLibName, $node);
        } else {
            $nodeSql = "select node_uuid from bd_node";
            $nodes = $this->dbSelect($nodeSql);
            foreach ($nodes as $n) {
                $result = $this->service()->scanTapeLibService($tapeLibName, $n['node_uuid']);
            }
        }
        if ($tapeLibName == '') {
            //扫描全部时，分段处理，返回操作的状态和开始时间
            $opUuid = $result['msg']['op_uuid'];
            $statusSql = "select status, start_time from bd_tape_operation where op_uuid = ? ";
            $statusSqlParam = [$opUuid];
            $data = $this->dbSelect($statusSql, $statusSqlParam);
            $result['status'] = $data[0]['status'];
            $result['start_time'] = $data[0]['start_time'];
        }
        return $result;
    }

    /**
     * 添加磁带组
     * @return mixed
     */
    public function addTapeGroup($params)
    {
        $opName = 'WEB_NODE_TAPE_OP_CREATE_TAPE_GROUP';

        $sql = "select count(name) as count from bd_tape_group where name = ?";
        $sqlParams = array($params['tape_group_properties']['name']);

        $nodeSql = "select node_uuid from bd_tape_library where name = ?";
        $nodeSqlParams = array($params['lib_name']);
        $node = $this->dbSelect($nodeSql, $nodeSqlParams);
        $nodeUuid = $node[0]['node_uuid'];
        $count = $this->dbSelect($sql, $sqlParams);
        if ($count[0]['count'] > 0) {
            exit($this->muOpResult(
                false,
                xphp_get_lang('WEB_TAPE_GROUP_ADD_FAILURE'),
                xphp_get_lang('WEB_TAPE_GROUP_ADD_FAILURE_TIP')
            ));
        }
        //添加磁带库的逻辑
        $result = $this->service()->addTapeGroupService(
            $params['tape_group_properties'], 
            $params['tape_serial_number_set'],
            $params['use_mode'],
            $params['warning_settings'],
            $nodeUuid
        );

        return $result;
    }

    /**
     * 修改磁带组
     * @return json
     */
    public function modifyTapeGroup($params)
    {
        $opName = 'WEB_NODE_TAPE_OP_MODIFY_TAPE_GROUP';
        $operate = $this->getUnifyOpcodeDes($opName);

        $sql = "select count(name) as count from bd_tape_group where name = ?";
        $sqlParams = array($params['tape_group_properties']['name']);
        $groupUuid = $params['tape_group_properties']['group_uuid']; //修改的磁带组uuid
        $oldSql = "select name from bd_tape_group where group_uuid = ?";
        $oldSqlParams = array($groupUuid);
        $data = $this->dbSelect($oldSql, $oldSqlParams);
        $oldName = $data[0]['name']; //磁带组旧的名称
        $count = $this->dbSelect($sql, $sqlParams);
        if ($count[0]['count'] > 0 && $oldName != $params['tape_group_properties']['name']) {
            exit($this->muOpResult(
                false,
                xphp_get_lang('WEB_TAPE_GROUP_EDIT_FAILURE'),
                xphp_get_lang('WEB_TAPE_GROUP_ADD_FAILURE_TIP')
            ));
        }
        //修改磁带组的逻辑
        $result = $this->service()->modifyTapeGroupService(
            $params['tape_group_properties'],
            $params['add_tape_serial_number_set'],
            $params['remove_tape_serial_number_set'],
            $params['use_mode'],
            $params['warning_settings']
        );

        return $result;
    }

    /**
     * 删除磁带组
     * @return json
     */
    public function delTapeGroup($group_uuid)
    {
        $opName = 'WEB_NODE_TAPE_OP_DELETE_TAPE_GROUP';
        $this->checkStorageInRunningTask($group_uuid);

        $result = $this->service()->delTapeGroupService($group_uuid);
        
        return $result;
    }

    /**
     * 检测磁带组是否在正在运行的任务中,如果在的话,直接退出程序,给予提示
     * @param string $groupUuid 磁带组uuid
     * @return json
     */
    private function checkStorageInRunningTask($groupUuid = '')
    {
        $sql = "select task_name from bd_task where task_status = ? and storage_uuid = ?";
        $sqlParams = array(xphp_get_config('task', 'TASKSTATUS')['RUNNING'], $groupUuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $tasks = array_column($data, 'task_name');

        $backupSetSql = "select backup_set_uuid from bd_tape_backup_set where group_uuid = ?";
        $backupSetData = $this->dbSelect($backupSetSql, [$groupUuid]);
        if (count($tasks) > 0 || count($backupSetData) > 0) {
            $tasksStr = implode(',', $tasks);
            exit($this->muOpResult(
                false,
                xphp_get_lang('WEB_TAPE_GROUP_DELETE'),
                $tasksStr . xphp_get_lang('WEB_TAPE_DELETE_GROUP_TIP')
            )
            );
        }
    }

    /**
     * 删除备份集
     * @param array $params 请求参数
     * @return array|void
     */
    public function delTapeBackupSet(array $params)
    {

        $uuidList = "'" . implode("','", $params['backupset_uuid_list']) . "'";
        $operate = xphp_get_lang('WEB_TAPE_PRIVATE_TAPE_OP_CODE_BACKUP_SET');
        // 1 冻结的备份集也不能删除
        $check = $this->dbSelect(
            "select count(*) num from bd_tape_backup_set where backup_set_uuid in ({$uuidList}) and freeze_flag != 0"
        );
        if (!empty($check) && $check[0]['num'] > 0) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TAPE_DELETE_BACKUP_SET_TIP4'), 'warning');
        }


        // 2 备份任务处于运行、异常状态，该备份任务的全部链都不能删除
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');
        //所有的不可删除状态
        $allAbnormalStatus = [
            $allTaskStatus['RUNNING'],
            $allTaskStatus['NETWORK_FAULT'],
            $allTaskStatus['ABNORMAL'],
            $allTaskStatus['ERROR'],
            $allTaskStatus['STOPPING'],
            $allTaskStatus['STARTING'],
        ];
        $allAbnormalStatus = implode(',', $allAbnormalStatus);

        //所有的备份任务状态
        $allAbnormalType = [
            $allTaskType['BACKUP'],
            $allTaskType['DB_CDP_BACKUP'],
            $allTaskType['FILE_CDP_BACKUP'],
            $allTaskType['DB_BACKUP'],
            $allTaskType['VOL_CDP_BACKUP'],
            $allTaskType['OS_BACKUP'],
            $allTaskType['KUBE_BACKUP'],
        ];
        $allAbnormalType = implode(',', $allAbnormalType);
        $subSql = v1_tape_timepoint_sql(" and btbs.backup_set_uuid IN ({$uuidList})");
        $taskSql = "SELECT count(btt.id) num 
                    FROM bd_backup_timepoint btt
                        JOIN bd_task bt ON bt.task_uuid = btt.task_uuid 
                        inner join ({$subSql}) AS valid_timepoints
                                        ON btt.timepoint_uuid = valid_timepoints.timepoint_uuid
                    WHERE
                        bt.task_status in ({$allAbnormalStatus}) and bt.task_type in ({$allAbnormalType})";

        $check = $this->dbSelect($taskSql);
        if (!empty($check) && $check[0]['num'] > 0) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TAPE_DELETE_BACKUP_SET_TIP2'), 'warning');
        }

        // 关联的时间点处于不可用的也不能删除,同时任务还存在
        $taskSql = "SELECT count(btt.id) num 
                    FROM bd_backup_timepoint btt
                        JOIN bd_task bt ON bt.task_uuid = btt.task_uuid 
                        inner join ({$subSql}) AS valid_timepoints
                                        ON btt.timepoint_uuid = valid_timepoints.timepoint_uuid
                    WHERE
                         btt.available_flag = 2";
        $check = $this->dbSelect($taskSql);
        if (!empty($check) && $check[0]['num'] > 0) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TAPE_DELETE_BACKUP_SET_TIP2'), 'warning');
        }

        // 3 备份任务处于等待，最近一个备份集不能删除
        // 通过 bd_tape_backup_set 表的 group_uuid 关联 bd_backup_timepoint 表的 storage_uuid 查询出对应的任务 bd_task 的状态为等待，
        $taskSql = "SELECT backup_set_uuid
                        FROM (
                            SELECT 
                                btbs2.backup_set_uuid,
                                ROW_NUMBER() OVER (PARTITION BY btbs2.group_uuid ORDER BY btbs2.last_write_time DESC) AS rn
                            FROM bd_tape_backup_set btbs2
                            WHERE btbs2.group_uuid IN (
                                SELECT btbs.group_uuid
                                FROM bd_tape_backup_set btbs
                                JOIN bd_backup_timepoint bbt ON btbs.group_uuid = bbt.storage_uuid
                                JOIN bd_task bt ON bt.task_uuid = bbt.task_uuid
                                where btbs.backup_set_uuid in ( {$uuidList} )
                                and bt.task_status = ({$allTaskStatus['WAITTING']})
                            )
                        ) t
                        WHERE rn = 1";

        $check = $this->dbSelect($taskSql);
        if (
            !empty($check) &&
            !empty(array_intersect(array_column($check, 'backup_set_uuid'), $params['backupset_uuid_list']))
        ) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TAPE_DELETE_BACKUP_SET_TIP3'), 'warning');
        }
        // 4 恢复任务处于使用状态，使用的备份集都不能删除
        //所有的恢复任务状态
        $allAbnormalType = [
            $allTaskType['RECOVERY'],
            $allTaskType['VM_FILE_RECOVERY'],
            $allTaskType['VM_INSTANT_RECOVERY'],
            $allTaskType['VM_INSTANT_RECOVERY_MOTION'],
            $allTaskType['VM_CDP_RECOVERY'],
            $allTaskType['VM_CDP_INSTANT_RECOVERY'],
            $allTaskType['VM_CDP_INSTANT_RECOVERY_MOTION'],
            $allTaskType['DB_CDP_RECOVERY'],
            $allTaskType['FILE_CDP_RECOVERY'],
            $allTaskType['DB_RECOVERY'],
            $allTaskType['VOL_CDP_RECOVERY'],
            $allTaskType['OS_RECOVERY'],
            $allTaskType['NAS_RECOVERY'],
            $allTaskType['CDP_DB_RECOVERY'],
            $allTaskType['OS_INSTANT_RECOVERY'],
            $allTaskType['OS_INSTANT_RECOVERY_MOTION'],
            $allTaskType['PLATFORM_RECOVERY'],
            $allTaskType['INSTANT_RECOVERY'],
            $allTaskType['INSTANT_RECOVERY_MOTION'],
            $allTaskType['GRAIN_RECOVERY'],
            $allTaskType['KUBE_RECOVERY'],
        ];
        $allAbnormalType = implode(',', $allAbnormalType);
        // 组装恢复的sql
        $recoverSql = [];
        // 虚拟化
        $recoverSql[] = "SELECT vml.timepoint_uuid
                            FROM bd_task bt
                            INNER JOIN vm_machine_list vml ON vml.task_uuid = bt.task_uuid
                            WHERE bt.task_type in ({$allAbnormalType})
                              AND vml.timepoint_uuid IS NOT NULL";
        $recoverSql[] = "SELECT vml.timepoint_uuid
                            FROM bd_task bt
                            INNER JOIN bd_grain_recovery_task vml ON vml.task_uuid = bt.task_uuid
                            WHERE bt.task_type in ({$allAbnormalType})
                              AND vml.timepoint_uuid IS NOT NULL";
        // 容器
        $recoverSql[] = "SELECT vml.src_timepoint_uuid timepoint_uuid
                            FROM bd_task bt
                            INNER JOIN kube_task vml ON vml.task_uuid = bt.task_uuid
                            WHERE bt.task_type in ({$allAbnormalType})
                              AND vml.src_timepoint_uuid IS NOT NULL";
        // 文件（fs/nas/haddop/obs）
        $recoverSql[] = "SELECT vml.recovery_timepoint_uuid timepoint_uuid
                            FROM bd_task bt
                            INNER JOIN fs_path_list vml ON vml.task_uuid = bt.task_uuid
                            WHERE bt.task_type in ({$allAbnormalType})
                              AND vml.recovery_timepoint_uuid IS NOT NULL";
        // 数据库
        $recoverSql[] = "SELECT vml.timepoint_uuid
                            FROM bd_task bt
                            INNER JOIN db_list vml ON vml.task_uuid = bt.task_uuid
                            WHERE bt.task_type in ({$allAbnormalType})
                              AND vml.timepoint_uuid IS NOT NULL";
        // m365
        $recoverSql[] = "SELECT vml.recovery_timepoint_uuid timepoint_uuid
                            FROM bd_task bt
                            INNER JOIN m365_task vml ON vml.task_uuid = bt.task_uuid
                            WHERE bt.task_type in ({$allAbnormalType})
                              AND vml.recovery_timepoint_uuid IS NOT NULL";
        // 定时整机/卷
        $recoverSql[] = "SELECT vml.timepoint_uuid
                            FROM bd_task bt
                            INNER JOIN os_list vml ON vml.task_uuid = bt.task_uuid
                            WHERE bt.task_type in ({$allAbnormalType})
                              AND vml.timepoint_uuid IS NOT NULL";
        // 副本任务
        $recoverSql[] = "SELECT 
                            bbt.timepoint_uuid
                        FROM bd_task bt
                        JOIN copy_list cl ON cl.task_uuid = bt.task_uuid
                        JOIN bd_backup_timepoint bbt ON bbt.task_uuid = cl.source_task_uuid
                        WHERE bt.task_type in ({$allAbnormalType})";

        // 第一部分：任务相关的 timepoint_uuid（去重）
        $first = implode(" union ", $recoverSql);
        $finalSql = "WITH task_timepoints AS (
                        {$first}
                    ),";
        // -- 第二部分：tape 路径中提取的 timepoint_uuid
        $tapeTimepont = v1_tape_timepoint_sql(" and btbs.backup_set_uuid in ({$uuidList})");
        $finalSql .= "tape_timepoints AS (
                    {$tapeTimepont}
                )";
        // -- 取两者的交集
        $finalSql .= "SELECT count(t1.timepoint_uuid) num
                        FROM task_timepoints t1
                        INNER JOIN tape_timepoints t2
                            ON t1.timepoint_uuid = t2.timepoint_uuid";
        $check = $this->dbSelect($finalSql);
        if (!empty($check) && $check[0]['num'] > 0) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_TAPE_DELETE_BACKUP_SET_TIP1'), 'warning');
        }

        // 执行删除
        return $this->service()->delBackupSetService($params);
    }

    /**
     * 冻结/解冻 磁带备份集
     * @param array $params param
     * @return string
     */
    public function opTapeBackupSet(array $params)
    {
        if ($params['type'] == 'defrost') {
            // 冻结
            $opName = 'NODE_TAPE_OP_FREEZE_BACKUP_SET';
            $opCode = 1;
        } else {
            // 解冻
            $opName = 'NODE_TAPE_OP_THAW_BACKUP_SET';
            $opCode = 2;
        }

        $msg = [
            'op_code' => $opCode,
            'backupset_uuid' => $params['backup_set_uuid'], //目标备份集UUID列表
        ];

        $mbResult = $this->service()->operateTapeService($opName, json_encode($msg));

        $operate = (new NodeOpcode())->getOpcodeDes($opName);
        return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
    }

    /**
     *  导入磁带
     */
    public function importTape($param)
    {
        $tapeLibName = $param['lib_name'];
        $tapeSerialNumber = $param['serial_number'];

        $opName = 'NODE_TAPE_OP_IMPORT_TAPE_CARRIAGE';

        $result = $this->service()->importTapeService($tapeLibName, $tapeSerialNumber);
        return $result;
    }

    /**
     *  弹出磁带
     */
    public function exportTape($param)
    {
        $tapeLibName = $param['lib_name'];
        $tapeSerialNumber = $param['serial_number'];
        $opName = 'NODE_TAPE_OP_EXPORT_TAPE_CARRIAGE';

        $result = $this->service()->exportTapeService($tapeLibName, $tapeSerialNumber);
        return $result;
    }

    /**
     *  导入磁带组
     */
    public function importTapeGroup($param)
    {
        $properties = $param['tape_group_properties'];
        $sql = "select btl.name, btl.node_uuid from bd_tape_carriage btc
                left join bd_tape_library btl on btl.name = btc.lib_name where btc.group_uuid = ? limit 1";
        $sqlParam = [$properties['group_uuid']];
        $data = $this->dbSelect($sql, $sqlParam);
        $node = $data[0]['node_uuid'];
        $result = $this->service()->importTapeGroupService($properties, $param['use_mode'], $param['warning_settings'], $node);
        return $result;
    }

    /**
     *  检索磁带
     */
    public function retrievalTape($param)
    {
        $tapeLibName = $param['tape_lib_name'];
        $tapeSerialNumber = $param['tape_serial_number'];
        $opName = 'NODE_TAPE_OP_RETRIEVE_TAPE_DATA';
        if ($tapeSerialNumber == '') {
            // 为空时检索整个带库，做限制(当磁带库中的磁带处于启动中/运行任务中，不可检索磁带库)
            $sql = "select distinct group_uuid from bd_tape_carriage where lib_name = ? ";
            $sqlParams = array($tapeLibName);
            $groups = $this->dbSelect($sql, $sqlParams);
            $groups = array_filter(array_column($groups, 'group_uuid'));
            $groupsList = "('" . implode("','", $groups) . "')";
            
            // 当前带库上的磁带备份集的所有备份点（恢复任务用）
            $timepointSql = "SELECT DISTINCT btt.timepoint_uuid, btt.task_uuid 
            FROM bd_backup_timepoint btt 
            JOIN bd_tape_backup_file btb ON INSTR(btb.file_path, btt.timepoint_uuid) > 0 
            JOIN bd_tape_carriage ON bd_tape_carriage.serial_number = btb.tape_serial_number 
            JOIN bd_tape_backup_set ON bd_tape_carriage.backup_set_uuid = bd_tape_backup_set.backup_set_uuid 
            WHERE bd_tape_carriage.lib_name = ?";
            $timepointSqlParam = array($tapeLibName);
            $timePoints = $this->dbSelect($timepointSql, $timepointSqlParam);
            $timePoints = "('" . implode("','", array_column($timePoints, 'timepoint_uuid')) . "')";

            $taskSql = "SELECT distinct bt.task_uuid, bt.task_status from bd_task bt
                LEFT JOIN m365_task ON bt.task_uuid = m365_task.task_uuid AND m365_task.recovery_timepoint_uuid in {$timePoints} 
                LEFT JOIN db_list ON bt.task_uuid = db_list.task_uuid AND db_list.timepoint_uuid in {$timePoints} 
                LEFT JOIN fs_path_list ON bt.task_uuid = fs_path_list.task_uuid AND fs_path_list.recovery_timepoint_uuid in {$timePoints} 
                LEFT JOIN vm_machine_list ON bt.task_uuid = vm_machine_list.task_uuid AND vm_machine_list.timepoint_uuid in {$timePoints} 
                LEFT JOIN os_list ON bt.task_uuid = os_list.task_uuid AND os_list.timepoint_uuid in {$timePoints} 
                LEFT JOIN copy_list ON bt.task_uuid = copy_list.task_uuid 
                where bt.delete_flag=? and ( ( bt.storage_uuid in {$groupsList} AND bt.storage_uuid != '' ) OR 
                (m365_task.recovery_timepoint_uuid != ''
                OR db_list.timepoint_uuid != ''
                OR fs_path_list.recovery_timepoint_uuid != ''
                OR vm_machine_list.timepoint_uuid != ''
                OR os_list.timepoint_uuid != ''
                OR copy_list.source_storage_uuid in {$groupsList} ) )";
            $flag = xphp_get_config('app', 'FLAG');
            $taskSqlParams = [$flag['UNSET']];
            $tasks = $this->dbSelect($taskSql, $taskSqlParams);
            $status = array_column($tasks, 'task_status');
            $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');

            if (in_array($allTaskStatus['RUNNING'], $status) || in_array($allTaskStatus['STARTING'], $status)) {
                return $this->muOpResult(false, $opName, xphp_get_lang('WEB_TAPE_RETRIEVAL_LIB_TIP'), 'warning');
            }
        }
        
        $result = $this->service()->retrievalTapeService($tapeLibName, $tapeSerialNumber);
        return $result;
    }

    /**
     *  提高优先级
     */
    public function increasePriority($param)
    {
        $taskUuid = $param['task_uuid'];
        $priority = $param['priority'];
        $groupUuid = $param['group_uuid'];
        if ($priority == 2) {//优先级为2的设置数要小于等于驱动器数
            $libSql = "select lib_name from bd_tape_carriage where group_uuid = ? limit 1";
            $libSqlParam = [$groupUuid];
            $libName = $this->dbSelect($libSql, $libSqlParam);
            $driveSql = "select count(*) from bd_tape_driver where lib_name = ?";
            $driveSqlParam = [$libName[0]['lib_name']];
            $driverCount = $this->dbSelect($driveSql, $driveSqlParam);
            $pendingTaskCount = $this->dbSelect('select count(*) from bd_pending_task where priority = 2');
            // if ($driverCount <= $pendingTaskCount) {
            //     return $this->muOpResult(false, '变更优先级', '可用驱动器数目不足',);
            // }
        }
        $sql = "update bd_pending_task set priority = ? where task_uuid = ?";
        $sqlParams = [$taskUuid, $priority];
        $result = $this->dbExec($sql, $sqlParams);
        return $result;
    }

    /**
     *  降低优先级
     */
    public function decreasePriority($param)
    {
        $tapeLibName = $param['tape_lib_name'];
        $tapeSerialNumber = $param['tape_serial_number'];
        $opName = 'NODE_TAPE_OP_RETRIEVE_TAPE_DATA';

        $result = $this->service()->retrievalTapeService($tapeLibName, $tapeSerialNumber);
        return $result;
    }

}