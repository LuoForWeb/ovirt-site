<?php

namespace app\v1\common\logic;

use app\v1\opcode\PfOpcode;

/**
 * note          告警管理处理类 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/7 16:11
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Alarm extends Base
{
    /**
     * 删除任务告警
     * @param array $params 数据
     * @return void|string
     */
    public function deleteTaskAlarm(array $params)
    {
        $id = $params['id'];
        $tenantFlag = $params['tenantFlag']; //删除租户标志
        if (!$tenantFlag) {
            $this->paramsCheck($id);
        }

        $opName = 'BD_ALARM_OP_TASK_DELETE';
        $msg = array('id_list' => array_map('intval', $id));

        return $this->unifyMsg($opName, json_encode($msg));
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param string $opName  opname
     * @param $jsonMsg json
     * @return array
     */
    private function unifyMsg(string $opName, $jsonMsg)
    {
        $mbResult = $this->mbPFMsg($opName, $jsonMsg);
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();

        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        $this->writeAlarmLog($opName, json_decode($jsonMsg, true), $mbResult);
        //返回结果到UI
        if ($result) {
            return [$result, $operate, $msg];
        } else {
            return [$result, $operate, $msg, '', $mbResult['errorCode']];
        }
    }

    /**
     * @param $opName   opname
     * @param $msg      msg
     * @param $mbResult 结果
     * @return void
     */
    private function writeAlarmLog($opName, $msg, $mbResult)
    {
        $key = '';
        $params = array(count($msg['id_list']));
        if ($opName == "BD_ALARM_OP_TASK_DELETE") {
            $key = "SYSTEM_LOG_DELETE_TASK_ALARM";
        } elseif ($opName == "BD_ALARM_OP_SYSTEM_DELETE") {
            $key = "SYSTEM_LOG_DELETE_SYSTEM_ALARM";
        }

        if ($mbResult['result']) {
            $this->systemLog($key, $params);
        } else {
            $this->systemLog($key, $params, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
        }
    }
}
