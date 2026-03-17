<?php

namespace app\v1\alarm\v0\logic;

use app\v1\common\logic\Base;
use app\v1\system\v0\logic\Time;

/**
 * note          desc
 * @author       wanggongxi@vinchin.com
 * @date         2025/7/24 11:35
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class Index extends Base
{
    /**
     * 获取系统告警 得到首页通知信息、左侧菜单提示、顶部消息通知
     * @param array $params 请求参数
     * @return array
     */
    public function getSurveyNoticeInfo(array $params = [])
    {

        $flag = xphp_get_config('app', 'FLAG');
        $logLevel = xphp_get_config('log', 'LOGLEVEL');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        //系统告警
        $noSureBackupFlag = $params['no_surebackup_flag'] ?? false; // 是否不查询验证任务信息，true不查询，GMP适配
        $sql = "select count(description_key) as total from bd_system_alarm where solved_flag = ?
            and alarm_level = ? ";
        $warnParams = array($flag['UNSET'], $logLevel['WARN']);
        $errorParams = array($flag['UNSET'], $logLevel['ERROR']);
        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $systemWarn = intval($dataWarn[0]['total']);
        $systemError = intval($dataError[0]['total']);

        $isglobal = false;
        $user = xphp_get_user_info();
        // 如果是有全局观察者权限，那么显示所有的数据
        if (
            (in_array('global_read', $user['permissionArr']) || in_array('global_write', $user['permissionArr']))
            && $user['userLevel'] != 1
        ) {
            // 有全局观察者权限并且不是超级管理
            $isglobal = true;
        }

        //任务告警 有全局权限就看所有的，不然就看自己的
        $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
        and alarm_level = ?";
        if (!$isglobal && $user['userLevel'] != 1) {
            $sql .= ' and user_uuid = ?';
            $warnParams = array_merge($warnParams, [$user['userUuid']]);
            $errorParams = array_merge($errorParams, [$user['userUuid']]);
        }

        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $taskWarn = intval($dataWarn[0]['total']);
        $taskError = intval($dataError[0]['total']);

        //当前任务
        $sql = "select count(task_uuid) as total from bd_task where delete_flag = ? and task_type != ? ";
        $sqlParam = [$flag['UNSET'], $taskType['ORCH_TASK']];
        if (!$isglobal && $user['userLevel'] != 1) {
            $sql .= ' and user_uuid = ?';
            $sqlParam = array_merge($sqlParam, [$user['userUuid']]);
        }

        if ($noSureBackupFlag) {
            $sql .= ' and task_type != ? ';
            $sqlParam = array_merge($sqlParam, [$taskType['SURE_BACKUP']]);
        }
        $data = $this->dbSelect($sql, $sqlParam);

        $currentTask = intval($data[0]['total']);

        //历史任务
        $sql = "select count(task_uuid) as total from bd_history_task where 1 = 1 ";
        $sqlParam = [];
        if ($noSureBackupFlag) {
            $sql .= ' and task_type != ? ';
            $sqlParam = array_merge($sqlParam, [$taskType['SURE_BACKUP']]);
        }

        if (!$isglobal && $user['userLevel'] != 1) {
            $sql .= ' and user_uuid = ?';
            $sqlParam = [$user['userUuid']];
        }

        $data = $this->dbSelect($sql, $sqlParam);
        $historyTask = intval($data[0]['total']);

        //获取当前验证任务
        $sql = "select count(task_uuid) as total from bd_task where delete_flag = ? and task_type = ? ";
        $sqlParam = [$flag['UNSET'], $taskType['SURE_BACKUP']];
        if (!$isglobal && $user['userLevel'] != 1) {
            $sql .= ' and user_uuid = ?';
            $sqlParam = array_merge($sqlParam, [$user['userUuid']]);
        }
        $data = $this->dbSelect($sql, $sqlParam);
        $verifyTask = intval($data[0]['total']);

        //获取历史验证任务
        $sql = "select count(task_uuid) as total from bd_history_task where task_type = ? ";
        $sqlParam = [$taskType['SURE_BACKUP']];
        if (!$isglobal && $user['userLevel'] != 1) {
            $sql .= ' and user_uuid = ?';
            $sqlParam = array_merge($sqlParam, [$user['userUuid']]);
        }

        $data = $this->dbSelect($sql, $sqlParam);
        $verifyHistoryTask = intval($data[0]['total']);

        $info = array(
            'alarm' => array(
                'system' => array(
                    'error' => $systemError,
                    'warn' => $systemWarn,
                ),
                'task' => array(
                    'error' => $taskError,
                    'warn' => $taskWarn,
                )
            ),
            'task' => array(
                'current' => $currentTask,
                'history' => $historyTask,
                'verifycurrent' => $verifyTask,
                'verifyhistory' => $verifyHistoryTask,
            ),
            'vcenter' => 0,
            'storage' => 0,
            'lisence' => 0,
        );

        //检测系统是否授权
        $systemHandler = new \app\v1\system\v0\logic\Index();
        $status = $systemHandler->getSystemAuthorizationStatus();
        if ($status != xphp_get_config('app', 'LISENCE_INFO')['authflag']['authorized']) {
            $info['lisence'] = 1;
        } else {
            //如果授权了判断是否授权虚拟化
            $vmInfo = v1_license_get_auth('vm');
            if ($vmInfo['total'] == 0) {
                $info['vcenter'] = 0;
            }
        }
        //检测是否添加了存储设备
        $sql = "select storage_id from bd_storage_resource";
        $count = $this->dbQuery($sql);
        $info['storage'] = $count > 0 ? 0 : 1;
        //检测是否添加了vcenter
        $sql = "select vcenter_id from vm_vcenter";
        $count = $this->dbQuery($sql);
        $info['vcenter'] = $count > 0 ? 0 : 1;
        $info['systime'] = (new Time())->getSystemTime();

        return $this->checkUserPemission($info);
    }

    /**
     * 检查用户权限
     * 主要是检查用户是否有权限查看告警(任务告警/系统告警),任务(当前任务/历史任务)
     * @param array $info 请求参数
     * @return array
     */
    private function checkUserPemission(array $info): array
    {
        $user = xphp_get_user_info();
        $permission = $user['permission'];
        //检查任务,当前任务和历史任务
        if (!in_array('current_job', $permission)) {
            $info['task']['current'] = 0;
        }
        if (!in_array('history_job', $permission)) {
            $info['task']['history'] = 0;
        }

        //检查告警,任务告警和系统告警
        if (!in_array('task_alarm', $permission)) {
            $info['alarm']['task']['error'] = 0;
            $info['alarm']['task']['warn'] = 0;
        }
        if (!in_array('system_alarm', $permission)) {
            $info['alarm']['system']['error'] = 0;
            $info['alarm']['system']['warn'] = 0;
        }

        return $info;
    }
}
