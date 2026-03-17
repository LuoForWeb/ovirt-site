<?php

namespace app\v1\exchange\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;

/**
 * note          office365（exchange） 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeData extends Base
{
    /**
     * 删除备份数据
     * @param unkown $params 参数
     * @return unknown
     *
     */
    public function deleteTimePoint($params)
    {
        $pointlist = $params['point_list'];
        $tasklist = $params['task_list'];
        $selectTimepoints = array();
        if (!empty($tasklist)) {
            foreach ($tasklist as $task) {
                $selectTimepoints = $this->getTimepointByTask($task, $params['storage_uuid']);
                $pointlist =  array_merge($pointlist, $selectTimepoints);
            }
        }
        $pointList = v1_array_sort($pointlist, 'nodeuuid', '', 0, -1);
        $info = array();
        $nodeuuids = array();
        $timepointuuids = array();
        $timepointuuid = array();
        foreach ($pointList as $d) {
            if (!in_array($d['node_uuid'], $nodeuuids)) {
                $nodeuuids[] = $d['node_uuid'];
                $info[] =  array(
                    'node_uuid' => $d['node_uuid'],
                );
                if (!empty($timepointuuid)) {
                    $timepointuuids[] = $timepointuuid;
                    $timepointuuid = array();
                }
                $timepointuuid[] = $d['timepoint_uuid'];
            } else {
                $timepointuuid[] = $d['timepoint_uuid'];
            }
        }
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes('BD_BACKUP_POINT_OP_BATCH_DELETE');
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chklist = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $timepointuuid) . "')", [xphp_get_user_info()['userUuid']]);
            if (!empty($chklist)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chklist, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'error'));
            }
        }
        $timepointuuids[] = $timepointuuid;
        //检测时间点是否在磁带上
        $this->checkTimepointStorage($timepointuuids[0]);
        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
        $uuidList = array();
        foreach ($timepointuuids as $d) {
            $uuidList = array_merge($uuidList, $d);
        }
        
        // 日志信息
        $details = '';
        $i = 0;
        foreach($uuidList as $u) {
            // 时间点信息
            $i ++;
            $details .= $this->getPointDetails($i,$u);

        }
        // $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
        //检查时间点是否有任务存在
        $countPoint = 0;
        for ($i = 0; $i < $nodeuuidsCount; $i++) {
            $countPoint += count($timepointuuids[$i]);
            $msg = array(
                'timepoint_uuids' => $timepointuuids[$i]
            );
            if (empty($nodeuuids[$i])) {
                $nodeuuids[$i] = (new Node())->getNodeUUIDWithTimepointUUID($timepointuuids[$i][0]);
            }
            //检查时间点是否有恢复任务正在使用
//            $this->checkTaskExist($nodeuuids[$i], $timepointuuids[$i]);
            $mbResult = $this->service()->deleteTimePoint($nodeuuids[$i], $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, 'M365');
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam);
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        } else {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * @description: 获取时间点信息
     * @param {*} $index
     * @param {*} $pointUUID
     * @return {*}
     */
    private function getPointDetails($index, $pointUUID)
    {
        $taskNameDes = xphp_get_lang('UI_PUBLIC_TASK_RNAME');
        $moduleDes = xphp_get_lang('UI_PUBLIC_MODULE_TYPE');
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, bbt.module_type, bbt.task_type, mbt.organization_info from bd_backup_timepoint bbt, m365_backup_timepoint mbt where bbt.timepoint_uuid = '" . $pointUUID . "' and mbt.m365_timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array());
        $d = $data[0];
        $info = json_decode($d['organization_info'], true);
        $timepointDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES')[intval($data[0]['backup_mode'])] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT') . '：' . $d['timepoint'];
        if ($index > 1) {
            $details = "\n" . $taskNameDes . "：" . $d['task_name'] . "，" . $moduleDes . "：" . xphp_get_lang('WEB_PLATFORM_DES_M365') . "，" . $timepointDes . "，" . xphp_get_lang('UI_ORGAN_ORGANIZATION_NAME') . "：" . $info['organization_name'];
        } else {
            $details = $taskNameDes . "：" . $d['task_name'] . "，" . $moduleDes . "：" . xphp_get_lang('WEB_PLATFORM_DES_M365') . "，" . $timepointDes . "，" . xphp_get_lang('UI_ORGAN_ORGANIZATION_NAME') . "：" . $info['organization_name'];
        }
        return $details;
    }

    /**
     * 检测恢复任务正在使用的备份点不能被删除
     * @param $nodeuuid  节点
     * @param $pointList 时间点
     * @return null
     */
    public function checkRecoveryPoint($nodeuuid, $pointList)
    {
        $sql =  "select bt.id from bd_task bt, m365_task mt where mt.recovery_timepoint_uuid = ? and mt.task_uuid = bt.task_uuid and bt.task_status = 2 and bt.node_uuid = ?";
        foreach ($pointList as $pointuuid) {
            $params = array($pointuuid,$nodeuuid);
            $data  = $this->dbSelect($sql, $params);
            if (!empty($data)) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_ERROR_BACKUP_COPY_DELETE_TIMEPOINT_ERROR'), xphp_get_lang('WEB_FS_DELETE_TIMEPOINT_TIPS'), 'warning'));
            }
        }
    }

    /**
     * 检查该时间点是否有恢复任务
     * @param string $nodeuuid      节点
     * @param $timepointuuid 时间点uuid
     * @return boolean 是否有任务
     */
    private function checkTaskExist($nodeuuid, $timepointuuid)
    {
        $sql =  "select bt.id from bd_task bt, m365_task mt where mt.recovery_timepoint_uuid = ? and mt.task_uuid = bt.task_uuid and bt.task_status = 2 and bt.node_uuid = ?";
        foreach ($timepointuuid as $timepoint) {
            $params = array($timepoint,$nodeuuid);
            $data  = $this->dbSelect($sql, $params);
            if (!empty($data)) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_M365_COMMON_OP_CODE_DELETE_ORGANIZATION'), xphp_get_lang('WEB_M365_COMMON_OP_CODE_DELETE_ORGANIZATION_TIPS') . $data[0]['task_name'], 'warning'));
            }
        }
    }

    /**
     * 获取任务下的时间点
     * @param $task     任务uuid
     * @param string $nodeuuid 节点
     * @return array 时间点
     */
    private function getTimepointByTask($task, $storageUuid)
    {
        $info = array();
        $params = array($task['job_uuid']);
        $sql = 'select distinct bsr.node_uuid, bbt.timepoint_uuid,bbt.real_node_uuid from bd_backup_timepoint bbt,bd_storage_resource bsr, m365_backup_timepoint mbt where bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.task_uuid = ? and available_flag = 1';
        if (!empty($storageUuid)) {
            $sql .=  ' and bsr.storage_uuid = ?';
            $params = array_merge($params, array($storageUuid));
        }
        $data  = $this->dbSelect($sql, $params);
        foreach ($data as $d) {
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $info[] = array(
                'timepoint_uuid' => $d['timepoint_uuid'],
                'node_uuid' => $nodeUuid,
            );
        }
        return $info;
    }

    /**
     * 添加备注
     * @param $params 备注
     * @return string 添加备注结果
     */
    public function remarkTimepoint($params)
    {
        $remark = v1_remove_escape($params['remark']);
        $timepointuuid = $params['time_point_uuid'];
        $opName = 'BD_BACKUP_POINT_OP_COMMENT';
        $operate = (new PFOpcode())->getOpcodeDes($opName);
        $msg = json_encode(array('timepoint_uuid' => $timepointuuid, 'remarks' => $remark));
        return $this->service()->opUnifyPfMsg($opName, $msg, $operate);
    }

    /**
     * 云存储上的完备点索引数据同步
     * @param $params 完备点
     * @return object同步结果
     */
    public function syncPointData($params)
    {
        $info = array();
        if (empty($params['op_id'])) {//op_id为空就去获取
            $opId = $this->service()->syncPointData($params);
            $info = array(
                'op_id' => $opId,
                'op_status' => 1,//运行中
            );
        } else {
            $sql = "select op_status, op_error_code, max_wait_time, detail from bd_operation where op_id = ?";
            $data = $this->dbSelect($sql, array($params['op_id']));
            if ($data[0]['op_status'] == 3) {//报错
                return $this->muOpResult(false, xphp_get_lang('WEB_M365_COMMON_OP_CODE_SYNC_META_FILE'), '', 'info', $data[0]['op_error_code']);
            } elseif ($data[0]['op_status'] == 1 || $data[0]['op_status'] == 2) {//请求中1 成功2\
                if (!empty($data[0]['detail'])) {
                    $detail = json_decode($data[0]['detail'], true);
                    $detail = array(
                        'progress' => $detail['progress'] * 100,
                        'total_size' => v1_calSize($detail['total_size']),
                    );
                    $info = array(
                        'op_status' => $data[0]['op_status'],
                        'detail' => $detail,
                    );
                } else {
                    $info = array(
                        'op_status' => $data[0]['op_status'],
                        'detail' => array(
                            'progress' => 0,
                            'total_size' => 0,
                        ),
                    );
                }
            }
        }
        return $info;
    }

    /**
     * 设置永久标记
     * @param $params
     * @return
     */
    public function addStar($params) {
        $pointUUID = $params['time_point_uuid'];
        $opName = 'BD_BACKUP_POINT_OP_MAKR';
        $mbResult = $this->mbPFMsg($opName, json_encode(array('timepoint_uuids'=> array($pointUUID))));
        return $mbResult;
    }

    /**
     * 取消永久标记
     * @param $params
     * @return
     */
    public function deleteStar($params) {
        $time_point_uuid = $params['time_point_uuid'];
        $opName = 'BD_BACKUP_POINT_OP_UNMARK';
        $mbResult = $this->mbPFMsg($opName, json_encode(array('timepoint_uuids' => array($time_point_uuid))));
        return $mbResult;
    }

    /**
     * 检测时间点是否在磁带上,如果在磁带上则退出不让其删除并给出提示
     */
    public function checkTimepointStorage($timepointList)
    {
        $timeDes = "'" . implode("','", $timepointList) . "'";
        $storage_type = xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'];  //磁带
        $sql = "SELECT bbt.timepoint_uuid, bsr.storage_uuid, bsr.storage_type FROM bd_backup_timepoint bbt JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid  
        WHERE bbt.timepoint_uuid IN ($timeDes) AND bsr.storage_type = " . $storage_type;
        $result = $this->dbSelect($sql);
        if (count($result) > 0) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_M365_SERVER_DELETE_TIME_POINT'), xphp_get_lang('UI_TAPE_DELETE_BACKUP_POINT_TIPS'), 'warning'));
        }
        return;
    }
}
