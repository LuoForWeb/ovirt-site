<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;

/**
 * note          系统安全配置 logic
 * @author       zhengxiangqin@vinchin.com
 * @date         2024/3/18 11:06
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */

class Message extends Base
{
    /**
     * 获取推送列表
     */
    public function getMessagePushList($params = [])
    {
        $sql = "SELECT 
                baps.alarm_message_structure,baps.alarm_type,baps.platform_uuid,baps.push_template_type,baps.push_time_type,
                baps.push_strategy_uuid,baps.push_strategy_name,baps.task_uuid,btmp.platform_name,baps.last_push_time,baps.start_push_time,baps.enable_flag,
                baps.auto_push_flag,baps.push_response_type,baps.all_task_flag from bd_alarm_push_strategy baps LEFT JOIN
                bd_third_monitor_platform btmp 
                on baps.platform_uuid = btmp.platform_uuid";
        $sqlCount = "select count(distinct baps.id) as total from bd_alarm_push_strategy baps";  // 总条数
        // 有搜索
        $sqlParams = array();
        if(!empty($params['search'])){
            $sql .= " where baps.push_strategy_name like ?";
            $sqlCount .= " where baps.push_strategy_name like ?";
            $sqlParams = array_merge($sqlParams,array('%' . $params['search'] . '%'));
        }
        $data = $this->dbSelect($sql,$sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlParams);  // 搜索后的条数
        $num = intval($count[0]['total']);
        $rows = [];
        if($num){
            foreach ($data as $d){
                // 关联任务
                $taskList = json_decode($d['task_uuid']);
                $taskNameArr = array();
                foreach ($taskList as $t){
                    $taskSql = "select task_name from bd_task where task_uuid = ?";
                    $taskData = $this->dbSelect($taskSql,array($t));
                    $taskNameArr[] = $taskData[0]['task_name'];
                }

                $rows[] = array(
                    'push_strategy_uuid' => $d['push_strategy_uuid'],
                    'push_strategy_name' => $d['push_strategy_name'],
                    'platform_name' => $d['platform_name'],
                    'platform_uuid' => $d['platform_uuid'],
                    'start_push_time' => $d['start_push_time'],
                    'last_push_time' => $d['last_push_time'],
                    'status' => $d['enable_flag'],
                    'push_time_type' => $data[0]['push_time_type'],
                    'alarm_type' => $d['alarm_type'],
                    'auto_push_flag' => $d['auto_push_flag'],
                    'push_response_type' => $d['push_response_type'],
                    'task_names' => empty($taskNameArr) ? '--' : $taskNameArr,
                    'push_template_type' => $data[0]['push_template_type'],
                    'all_task_flag' => $d['all_task_flag'],
                    'alarm_message_structure' => $d['alarm_message_structure'],
                );

            }
        }
        return [
            'rows' => $rows,
            'total' => $num
        ];
    }

    /**
     * 获取推送平台详情
     */
    public function getMessagePush($params = [])
    {
        $sql = "select btmp.platform_uuid, btmp.platform_name, baps.push_strategy_uuid,baps.push_strategy_name, baps.push_template_type,baps.alarm_type, baps.auto_push_flag, 
                baps.push_response_type, baps.push_time_type, baps.start_push_time,baps.last_push_time, baps.all_task_flag, 
                baps.task_uuid, baps.alarm_message_structure from bd_alarm_push_strategy baps 
                inner join bd_third_monitor_platform btmp on baps.platform_uuid = btmp.platform_uuid where baps.push_strategy_uuid = ?";
        $data = $this->dbSelect($sql,array($params['message_uuid']));
        $info = array();
        if(!empty($data)){
            $info = array(
                'platform_uuid' => $data[0]['platform_uuid'],
                'platform_name' => $data[0]['platform_name'],
                'push_strategy_uuid' => $data[0]['push_strategy_uuid'],
                'push_strategy_name' => $data[0]['push_strategy_name'],
                'alarm_type' => $data[0]['alarm_type'],
                'auto_push_flag' => v1_parse_flag_to_bool($data[0]['auto_push_flag']),
                'push_time_type' => $data[0]['push_time_type'],
                'push_response_type' => $data[0]['push_response_type'] == xphp_get_config('app','FLAG')['UNSET']?true:false,
                'last_push_time' => $data[0]['last_push_time'],
                'start_push_time' => $data[0]['start_push_time'],
                'all_task_flag' => $data[0]['all_task_flag'],
                'task_uuid' => $data[0]['task_uuid'],
                'push_template_type' => $data[0]['push_template_type'],
                'alarm_message_structure' => $data[0]['alarm_message_structure'],
            );
        }
        return $info;
    }

    /**
     * 添加消息推送
     * @return json
     */
    public function addMessagePush($params = [])
    {
        // 推送策略
        $strategyUuid = "";
        $strategyName = $params['nickname'];  // 推送别名
        $alarmType = $params['alarm_type'];
        $monitorPlatformUuid = $params['monitor_platform_uuid'];
        $syncTimeType = $params['sync_time_type'];
        $lastPushTime = $params['last_push_time'];
        $startPushTime = $params['start_push_time'];
        $allTaskFlag = v1_parse_bool_to_flag($params['all_task_flag']);
        $taskUuids = $params['task_uuid'];
        $alarmMessageStructure = $params['alarm_message_structure'];
        $autoPushFlag = v1_parse_bool_to_flag($params['auto_push_flag']);
        $pushResponseType =$params['push_response_type']?xphp_get_config('system','PUSH_RESPONSE_TYPE')['AUTO']:xphp_get_config('system','PUSH_RESPONSE_TYPE')['MANUAL'];
        $pushTemplateType = $params['push_template_type'];
        $enableFlag = xphp_get_config('app', 'FLAG')['SET'];  // 默认开启
        $opName = 'NODE_SYS_OP_ADD_ALARM_PUSH_STRATEGY';  //控制码 私有
        $nodeUuid = $this->getLocalNodeUUID();
        $msg = array();
        $msg['alarm_push_strategy'] = array(
            'monitor_platform_uuid' => $monitorPlatformUuid,
            'strategy_uuid' => $strategyUuid,
            'strategy_name' => $strategyName,
            'enable_flag' => $enableFlag,
            'alarm_type' => $alarmType,
            'auto_push_flag' => $autoPushFlag,
            'push_response_type' => $pushResponseType,
            'push_time_type' => $syncTimeType,
            'last_push_time' => $lastPushTime,
            'start_push_time' => $startPushTime,
            'all_task_flag' => $allTaskFlag,
            'task_uuid' => $taskUuids,
            'push_template_type' => $pushTemplateType,
            'alarm_message_structure' => $alarmMessageStructure,
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg);
        if($mbResult['result']){
            return xphp_get_lang('WEB_ALARM_PUSH_STRATEGY_ADD') . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        }
        return false;
    }

    /**
     * 修改消息推送
     * @return json
     */
    public function editMessagePush($params = [])
    {
        // 推送
        $strategyUuid = $params['strategy_uuid'];
        $strategyName = $params['nickname'];  // 推送别名
        $alarmType = $params['alarm_type'];
        $syncTimeType = $params['sync_time_type'];
        $lastPushTime = $params['last_push_time'];
        $startPushTime = $params['start_push_time'];
        $allTaskFlag = $params['all_task_flag'];
        $taskUuids = $params['task_uuid'];
        $alarmMessageStructure = $params['alarm_message_structure'];
        $autoPushFlag = v1_parse_bool_to_flag($params['auto_push_flag']);
        $pushResponseType =$params['push_response_type']?xphp_get_config('system','PUSH_RESPONSE_TYPE')['AUTO']:xphp_get_config('system','PUSH_RESPONSE_TYPE')['MANUAL'];
        $pushTemplateType = $params['push_template_type'];
        // 告警平台
        $monitorPlatformUuid = $params['monitor_platform_uuid'];
        // task_uuid和alarm_message_structure都为json格式
        $enableFlag = xphp_get_config('app', 'FLAG')['SET'];  // 默认开启
        $nodeUuid = $this->getLocalNodeUUID();
        $msg = array();
        $msg['alarm_push_strategy'] = array(
            'monitor_platform_uuid' => $monitorPlatformUuid,
            'strategy_uuid' => $strategyUuid,
            'strategy_name' => $strategyName,
            'enable_flag' => $enableFlag,
            'alarm_type' => $alarmType,
            'auto_push_flag' => $autoPushFlag,
            'push_response_type' => $pushResponseType,
            'push_time_type' => $syncTimeType,
            'last_push_time' => $lastPushTime,
            'start_push_time' => $startPushTime,
            'all_task_flag' => v1_parse_bool_to_flag($allTaskFlag),
            'task_uuid' => $taskUuids,
            'push_template_type' => $pushTemplateType,
            'alarm_message_structure' => $alarmMessageStructure,
        );
        $opName = 'NODE_SYS_OP_MODIFY_ALARM_PUSH_STRATEGY';
        $msg = json_encode($msg);
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg);
        if($mbResult['result']){
            return xphp_get_lang('WEB_ALARM_PUSH_STRATEGY_EDIT')  . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        }
        return false;

    }

    /**
     * 删除消息推送
     * @return json
     */
    public function deleteMessagePush($params = [])
    {
        $strategys = $params['strategy_uuid'];
        $msg = array();
        $msg['strategy_uuid']= $strategys;
        $opName = 'NODE_SYS_OP_DELETE_ALARM_PUSH_STRATEGY';
        $nodeUuid = $this->getLocalNodeUUID();
        $msg = json_encode($msg);
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg);
        if($mbResult['result']){
            return xphp_get_lang('WEB_ALARM_PUSH_STRATEGY_DELETE')  . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        }
        return false;

    }




    /**
     * 启用消息推送
     * @return json
     */
    public function unlcokMessagePush($params = [])
    {
        $strategyUuids = $params['strategy_uuid'];  // array type
        $msg = array();
        $msg['alarm_push_strategy'] = array(
            'enable_flag' => xphp_get_config('app')['FLAG']['SET'],
            'strategy_uuid' => $strategyUuids,
        );
        $nodeUuid = $this->getLocalNodeUUID();
        $opName = 'NODE_SYS_OP_ALTER_ALARM_PUSH_STRATEGY_STATUS';
        $operate = (new NodeOpcode())->getOpcodeDes($opName);
        $msg = json_encode($msg);
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg);
        if($mbResult['result']){
            return $this->muOpResult($mbResult['result'], $operate, '');
        }else{
            return $this->muOpResult($mbResult['result'], $operate, '');
        }
    }


    /**
     * 检测推送平台是否重名
     */
    public function checkPushStrategyName($params = [])
    {
        $name = trim($params['strategy_name']);
        $editName = trim($params['edit_strategy_name']);
        if(!empty($params['edit_strategy_name'])){
            $sql = "select push_strategy_uuid from bd_alarm_push_strategy where push_strategy_name = ? and push_strategy_name != ?";
            $data = $this->dbSelect($sql,array($name,$editName));
        }else{
            $sql = "select push_strategy_uuid from bd_alarm_push_strategy where push_strategy_name = ?";
            $data = $this->dbSelect($sql,array($name));
        }
        return array(
            'result' => empty($data[0]['push_strategy_uuid'])
        );
    }

    /**
     * 禁用消息推送
     * @return json
     */
    public function lcokMessagePush($params = [])
    {
        $strategyUuids = $params['strategy_uuid'];  // array type
        $msg = array();
        $msg['alarm_push_strategy'] = array(
            'enable_flag' => xphp_get_config('app')['FLAG']['UNSET'],
            'strategy_uuid' => $strategyUuids,
        );
        $nodeUuid = $this->getLocalNodeUUID();
        $msg = json_encode($msg);
        $opName = 'NODE_SYS_OP_ALTER_ALARM_PUSH_STRATEGY_STATUS';
        $operate = (new NodeOpcode())->getOpcodeDes($opName);
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg);
        if($mbResult['result']){
            return $this->muOpResult($mbResult['result'], $operate, '');
        }else{
            return $this->muOpResult($mbResult['result'], $operate, '');
        }

    }

    /**
     * 获取关联任务
     */
    public function getAssociatedTask()
    {
        $sql = "select task_name, task_uuid from bd_task where delete_flag != ?";
        $data = $this->dbSelect($sql,array(xphp_get_config('app')['FLAG']['SET']));
        $rows = array();
        $allTasks = array();
        foreach ($data as $d){
            $rows[] = array(
               'task_uuid' => $d['task_uuid'],
               'task_name' => $d['task_name']
            );
        }
        return [
            'rows' => $rows,
        ];
    }

    /**
     * 获取监控平台列表
     * @return json
     */
    public function getMessageMonitorPlatformList($params = [])
    {
        $sql = "SELECT platform_uuid,platform_name,ip,port,protocol_type,http_api_url,syslog_protocol_type FROM bd_third_monitor_platform btmp ";

        $sqlCount = "select count(distinct btmp.id) as total from bd_third_monitor_platform btmp";  // 总条数
        // 有搜索
        $sqlParams = array();
        if(!empty($params['search'])){
            $sql .= " where btmp.platform_name like ?";
            $sqlCount .= " where btmp.platform_name like ?";
            $sqlParams = array_merge($sqlParams,array('%' . $params['search'] . '%'));
        }
        $data = $this->dbSelect($sql,$sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlParams);  // 搜索后的条数
        $num = intval($count[0]['total']);
        $rows = [];
        if($num){
            foreach ($data as $d){
                $rows[] = array(
                    'platform_uuid' => $d['platform_uuid'],
                    'platform_name' => $d['platform_name'],
                    'ip' => $d['ip'] == '' ? '--' : $d['ip'],
                    'port' => $d['port'] == 0 ? '--' : $d['port'],
                    'protocol_type' => $d['protocol_type'] == xphp_get_config('app')['FLAG']['SET'] ? 'syslog':'http',
                    'http_api_url' => $d['http_api_url'] == '' ? '--' : $d['http_api_url'],
                    'syslog_protocol_type' => $d['syslog_protocol_type']
                );
            }
        }
        return [
            'rows' => $rows,
            'total' => $num
        ];
    }

    /**
     * 获取单个监控平台详情
     */
    public function getMessageMonitorPlatform($params = [])
    {
        $monitorPlatformUuid = $params['monitor_platform_uuid'];
        $sql = "select platform_name,ip,port,protocol_type,http_api_url,syslog_protocol_type,encryption_type from bd_third_monitor_platform where platform_uuid = ?";
        $data = $this->dbSelect($sql,array($monitorPlatformUuid));
        $info = array();
        if(!empty($data)){
              $info = array(
                  'platform_name' => $data[0]['platform_name'],
                  'ip' => $data[0]['ip'],
                  'port' => $data[0]['port'],
                  'protocol_type' => $data[0]['protocol_type'],
                  'http_api_url' => $data[0]['http_api_url'],
                  'syslog_protocol_type' => $data[0]['syslog_protocol_type'],
                  'encryption_type_flag' => v1_parse_flag_to_bool($data[0]['encryption_type'])
              );
        }
        return $info;
    }

    /**
     * 添加监控平台
     * @return json
     */
    public function addMessageMonitorPlatform($params = [])
    {
        $monitorPlatformUuid = "";
        $nickName = $params['nickname'];
        $protocolType = $params['protocol_type'];
        $syslogProtocolType = $params['syslog_protocol_type'];
        $ip = $params['ip'];
        $port = $params['port'];
        $url = $params['url'];
        $encryptionType = $params['encryption_type'];
        $nodeUuid = $this->getLocalNodeUUID();
        $msg = array();
        $msg['monitor_platform'] = array(
            'monitor_platform_uuid' => $monitorPlatformUuid,
            'monitor_platform_name' => $nickName,
            'ip' => $ip,
            'port' => $port,
            'protocol_type' => $protocolType,
            'syslog_protocol_type' => $syslogProtocolType,
            'http_api_url' => $url,
            'encryption_type' => $encryptionType,
        );
        $opName = 'NODE_SYS_OP_ADD_THIRD_MONITOR_PLATFROM';
        $msg = json_encode($msg);
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg);
        if($mbResult['result']){
            return xphp_get_lang('WEB_THIRD_MONITOR_PLATFROM_ADD')  . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        }
        return false;
    }

    /**
     * 修改监控平台
     * @return json
     */
    public function editMessageMonitorPlatform($params = [])
    {
        $monitorPlatformUuid = $params['monitor_platform_uuid'];
        $nickName = $params['nickname'];
        $protocolType = $params['protocol_type'];
        $syslogProtocolType = $params['syslog_protocol_type'];
        $ip = $params['ip'];
        $port = $params['port'];
        $url = $params['url'];
        $encryptionType = $params['encryption_type'];
        $nodeUuid = $this->getLocalNodeUUID();
        $msg = array();
        $msg['monitor_platform'] = array(
            'monitor_platform_uuid' => $monitorPlatformUuid,
            'monitor_platform_name' => $nickName,
            'ip' => $ip,
            'port' => $port,
            'protocol_type' => $protocolType,
            'syslog_protocol_type' => $syslogProtocolType,
            'http_api_url' => $url,
            'encryption_type' => $encryptionType
        );
        $opName = 'NODE_SYS_OP_MODIFY_THIRD_MONITOR_PLATFROM';
        $msg = json_encode($msg);
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg);
        if($mbResult['result']){
            return xphp_get_lang('WEB_THIRD_MONITOR_PLATFROM_EDIT')  . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        }
        return false;

    }

    /**
     * 删除监控平台
     * @return json
     */
    public function deleteMessageMonitorPlatform($params = [])
    {
         $monitorPlatfromUuids = $params['monitor_platform_uuid'];  // array type
         $msg = array();
         $msg['monitor_platform_uuid']= $monitorPlatfromUuids;
         $nodeUuid = $this->getLocalNodeUUID();
         $msg = json_encode($msg);
         $opName = 'NODE_SYS_OP_DELETE_THIRD_MONITOR_PLATFROM';
         $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg);
         if($mbResult['result']){
             return xphp_get_lang('WEB_THIRD_MONITOR_PLATFROM_DELETE')  . xphp_get_lang('WEB_PUBLIC_SUCCESS');
         }
         return false;
    }

    /**
     * 检测监控平台是否重名
     */
    public function checkMonitorPlatformName($params = [])
    {
        $name = trim($params['monitor_platform_name']);
        $editName = trim($params['edit_monitor_platform_name']);
        if(!empty($editName)){
            $sql = "select platform_uuid from bd_third_monitor_platform where platform_name = ? and platform_name != ?";
            $data = $this->dbSelect($sql,array($name,$editName));
        }else{
            $sql = "select platform_uuid from bd_third_monitor_platform where platform_name = ?";
            $data = $this->dbSelect($sql,array($name));
        }
        return array(
            'result' => empty($data[0]['platform_uuid'])
        );
    }

    /**
     * 测试IP是否能ping通
     */
    public function testIp($params = [])
    {
        $type = $params['type'];
        $ip = $params['ip'];
        $port = $params['port'];
        //socket连接
        if ($type == 'telnet') {
            $this->SOCKET = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        } else {
            $this->SOCKET = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
        }

        if ($this->SOCKET === false) {
            return $this->muOpResult(false, xphp_get_lang('UI_SETTINGS_TOOL_TEST_SOCKET_ERROR'), "warning");
        }else{
            socket_set_block($this->SOCKET);
            socket_set_option($this->SOCKET,SOL_SOCKET, SO_RCVTIMEO,array("sec"=>1, "usec"=>0 ) );
            socket_set_option($this->SOCKET,SOL_SOCKET,SO_SNDTIMEO,array("sec"=>3, "usec"=>0 ) );
        }
        $result = socket_connect($this->SOCKET, $ip, $port);
        socket_close($this->SOCKET);
        $opName = $type." ".$ip." : ". $port;
        $opName .= xphp_get_lang('UI_VCENTER_ENGINE_BACKUP_TEST_CONNECT');
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName, "", "warning");
        }
    }


    /**
     * 手动推送告警信息
     * @return json
     */
    public function pushMessageAlarm($params = [])
    {
        $alarmId = intval($params['alarm_id']);
        $alarmType = intval($params['alarm_type']);
        $alarmLevel = intval($params['alarm_level']);
        $taskUuid = $params['task_uuid'];
        $alarmTime = $params['alarm_time'];
        $alarmContent = $params['alarm_content'];
        $msg = array();
        $msg['push_alarm_message'] = array(
            'alarm_id' => $alarmId,
            'alarm_type' => $alarmType,
            'alarm_level' => $alarmLevel,
            'task_uuid' => $taskUuid,
            'alarm_time' => $alarmTime,
            'alarm_content' => $alarmContent,
        );
        $nodeUuid = $this->getLocalNodeUUID();
        $msg = json_encode($msg);
        $opName = 'NODE_SYS_OP_MANUAL_PUSH_ALARM';
        $operate = (new NodeOpcode())->getOpcodeDes($opName);
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg,true);
        if($mbResult['result']){
            return $this->muOpResult($mbResult['result'], $operate, '');
        }else{
            return $this->muOpResult($mbResult['result'], $operate, '', '', $mbResult['errorCode']);
        }
    }

    /**
     * 手动推送响应信息
     * @return json
     */
    public function pushMessageResponse($params = [])
    {
        $alarmId = intval($params['alarm_id']);
        $alarmType = intval($params['alarm_type']);
        $alarmLevel = intval($params['alarm_level']);
        $alarmTime = $params['alarm_time'];
        $alarmContent = $params['alarm_content'];
        $taskUuid = $params['task_uuid'];
        $msg = array();
        $msg['push_alarm_message'] = array(
            'alarm_id' => $alarmId,
            'alarm_type' => $alarmType,
            'alarm_level' => $alarmLevel,
            'alarm_time' => $alarmTime,
            'alarm_content' => $alarmContent,
            'task_uuid' => $taskUuid,
        );
        $nodeUuid = $this->getLocalNodeUUID();
        $msg = json_encode($msg);
        $opName = 'NODE_SYS_OP_MANUAL_PUSH_ALARM_RESPONSE';
        $mbResult = $this->mbNodeMsg($opName,$nodeUuid,$msg,true);
        $operate = (new NodeOpcode())->getOpcodeDes($opName);
        if($mbResult['result']){
            return $this->muOpResult($mbResult['result'], $operate, '');
        }else{
            return $this->muOpResult($mbResult['result'], $operate, '', '', $mbResult['errorCode']);
        }
    }

    public function getMessageDefaultName($params = [])
    {
        $messageName = $params['name'];
        $oldmessageName = $messageName;
        for ($i = 1; $i < 1000; $i++) {
            $messageName .= $i;
            $sql = "select id from bd_alarm_push_strategy where push_strategy_name = ?";
            $data = $this->dbSelect($sql, array($messageName));
            if (empty($data)) {
                return [
                    'name' => $messageName
                ];
            }
            $messageName = $oldmessageName;
        }
        return [
            'name' => $oldmessageName
        ];
    }

    public function getMonitorDefaultName($params = [])
    {
        $monitorName = $params['name'];
        $oldmessageName = $monitorName;
        for ($i = 1; $i < 1000; $i++) {
            $monitorName .= $i;
            $sql = "select id from bd_third_monitor_platform where platform_name = ?";
            $data = $this->dbSelect($sql, array($monitorName));
            if (empty($data)) {
               return [
                    'name' => $monitorName
                ];
            }
            $monitorName = $oldmessageName;
        }
        return [
            'name' => $oldmessageName
        ];
    }

    public function getLocalNodeUUID()
    {
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }

    public function isSameIP($params)
    {
        $sql = "select count(ip) as count from bd_third_monitor_platform where ip = ? and ip != ''";
        $data = $this->dbSelect($sql, array($params['ip']));
        if(!empty($params['edit_ip'])){
            $sql = "select count(ip) as count from bd_third_monitor_platform where ip = ? and ip != ?";
            $data = $this->dbSelect($sql, array($params['ip'],$params['edit_ip']));
        }
        if($data[0]['count'] > 0){
            return array(
                'result' => false
            );
        }
        return array(
            'result' => true
        );
    }

    public function isSameUrl($params)
    {
        $sql = "select count(http_api_url) as count from bd_third_monitor_platform where http_api_url = ? and http_api_url != ''";
        $data = $this->dbSelect($sql, array($params['url']));
        if(!empty($params['edit_url'])){
            $sql = "select count(http_api_url) as count from bd_third_monitor_platform where http_api_url = ? and http_api_url != ?";
            $data = $this->dbSelect($sql, array($params['url'],$params['edit_url']));
        }
        if($data[0]['count'] > 0){
            return array(
                'result' => false
            );
        }
        return array(
            'result' => true
        );
    }

}