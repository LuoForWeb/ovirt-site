<?php

namespace app\v1\common\logic;

use app\v1\opcode\PfOpcode;

/**
 * note          日志管理处理类 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/7 15:36
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Log extends Base
{
    /**
     * 删除任务日志
     * @param array $params 数据
     * @return string
     */
    public function deleteTaskLog(array $params)
    {
        $id = $params['id'];
        $tenantFlag = $params['tenantFlag']; //删除租户标志
        if (!$tenantFlag) {
            $this->paramsCheck($id);
        }
        $idArray = array_map('intval', $id);

        $this->checkTaskLogTime($idArray, $tenantFlag); //检查只能删除半年以前的日志
        $opName = 'BD_LOG_OP_TASK_DELETE';
        $msg = array('id_list' => $idArray);
        return $this->unifyMsg($opName, json_encode($msg));
    }

    /**
     * 删除系统日志
     * @param array $params 数据
     * @return string
     */
    public function deleteSystemLog(array $params)
    {
        $id = $params['id'];
        $tenantFlag = $params['tenantFlag']; //删除租户标志
        if (!$tenantFlag) {
            $this->paramsCheck($id);
        }
        $idArray = array_map('intval', $id);

        $this->checkSystemLogTime($idArray, $tenantFlag); //检查只能删除半年以前的日志
        $opName = 'BD_LOG_OP_SYSTEM_DELETE';
        $msg = array('id_list' => $idArray);
        return $this->unifyMsg($opName, json_encode($msg));
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param string $opName  opname
     * @param  $jsonMsg msg
     * @return string
     */
    private function unifyMsg(string $opName, $jsonMsg)
    {
        $mbResult = $this->mbPFMsg($opName, $jsonMsg);
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        $this->writelogLog($opName, json_decode($jsonMsg, true), $mbResult);
        //返回结果到UI
        if ($result) {
            return [$result, $operate, $msg];
        } else {
            return [$result, $operate, $msg, '', $mbResult['errorCode']];
        }
    }

    /**
     * 写日志
     * @param $opName   opname
     * @param $msg      msg
     * @param $mbResult result
     * @return void
     */
    private function writelogLog($opName, $msg, $mbResult)
    {
        $key = '';
        $params = array(count($msg['id_list']));
        if ($opName == "BD_LOG_OP_TASK_DELETE") {
            $key = "SYSTEM_LOG_DELETE_TASK_LOG";
        } elseif ($opName == "BD_LOG_OP_SYSTEM_DELETE") {
            $key = "SYSTEM_LOG_DELETE_SYSTEM_LOG";
        }

        if ($mbResult['result']) {
            $this->systemLog($key, $params);
        } else {
            $this->systemLog($key, $params, xphp_get_config('log', 'LOGLEVEL')['ERROR'], $mbResult['errorCode']);
        }
    }

    /**
     * 删除日志检查是否为半年前的日志
     * @param array $ids        ids
     * @param $tenantFlag flag
     * @return void|json
     */
    private function checkTaskLogTime(array $ids, $tenantFlag)
    {
        //删除租户时不检查
        if ($tenantFlag) {
            return;
        }
        $idStr = implode("','", $ids);
        $sql = "select unix_timestamp(op_time) op_time from bd_task_log where id in ('" . $idStr . "')";
        $data = $this->dbSelect($sql, array());
        foreach ($data as $d) {
            $halfyear = 365 / 2 * 24 * 3600;
            $time = time() - intval($d['op_time']);
            if ($time < $halfyear) {
                $this->muOpResult(
                    false,
                    xphp_get_lang('WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'),
                    xphp_get_lang('WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS'),
                    'warning'
                );
            }
        }
    }

    /**
     * 删除日志检查是否为半年前的日志
     * @param array $ids        ids
     * @param bool  $tenantFlag flag
     * @return void|json
     */
    private function checkSystemLogTime(array $ids, $tenantFlag)
    {
        //删除租户时不检查
        if ($tenantFlag) {
            return;
        }
        $idStr = implode("','", $ids);
        $sql = "select unix_timestamp(op_time) op_time from bd_system_log where id in ('" . $idStr . "')";
        $data = $this->dbSelect($sql, array());
        foreach ($data as $d) {
            $halfyear = 365 / 2 * 24 * 3600;
            $time = time() - intval($d['op_time']);
            if ($time < $halfyear) {
                $this->muOpResult(
                    false,
                    xphp_get_lang('WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR'),
                    xphp_get_lang('WEB_LOG_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS'),
                    'warning'
                );
            }
        }
    }
}
