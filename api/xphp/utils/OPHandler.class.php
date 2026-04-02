<?php
/*******************************************
 ** 操作控制器，统一调用，如果方法不全，请通知管理员添加~！！
 ** 包含数据库操作 [db]打头
 ** 消息发送处理 [mb]打头到后台 [mu]打头到前台
 ** 日志处理 [log]打头
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2015-4-16 10:18:40
 ** @version      1.0.0
 ** @copyright    Copyright 2015 vinchin.com
 ********************************************/
class OPHandler{
    /************************************************************************************
     * 数据库 =>start
     */

    /**
     * 数据库带参数查询，适用于select
     * @param string $sql  SQL语句,如"select a from b where c = ?"
     * @param array $param SQL条件参数[1,2],每一项按位置对应SQL语句中的"?"，无参数不传
     * @param int $fetch_style 控制结果集中数据的返回方式:PDO::FETCH_ASSOC 关联数组形式, PDO::FETCH_NUM 数字索引数组形式, PDO::FETCH_BOTH 两者数组形式都有，这是默认的
     * @return fetchAll()
     */
    public function dbSelect($sql, $param = array(), $fetch_style = PDO::FETCH_BOTH) {
        $VPDO = Xphp::instance('VPDO');
        return $VPDO->sqlQuery($sql, $param, true, $fetch_style);
    }

    /**
     * 数据库带参数，适用于insert,delete
     * @param string $sql  SQL语句,如"select a from b where c = ?"
     * @param array $param SQL条件参数[1,2],每一项按位置对应SQL语句中的"?"，无参数不传
     * @return int 受影响的行数
     */
    public function dbQuery($sql, $param = array()){
        $VPDO = Xphp::instance('VPDO');
        return $VPDO->newExec($sql, $param, false);
    }

    /**
     * 查询sql执行结果,成功或失败,适用于insert,update或delete,有的地方会有updates受影响为0行,但是结果是成功的,方便处理
     * @param string $sql
     * @return boolean 返回成功和失败
     */
    public function dbExec($sql, $param = array()){
        $result = $this->dbQuery($sql, $param);
        if(false === $result){
            //这是执行出错
            return false;
        }
        //这是执行成功  返回受影响的行数为0或更多都任务成功
        return true;
    }

    /**
     * 得到最后一次插入的id,last_insert_id()
     */
    public function dbLastInsertId(){
        $sql = "select last_insert_id()";
        $data = $this->dbSelect($sql);
        return $data[0][0];
    }

    /**
     * 开始事务
     */
    public function dbBeginTransaction(){
        $VPDO = Xphp::instance('VPDO');
        $VPDO->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function dbCommit(){
        $VPDO = Xphp::instance('VPDO');
        $VPDO->commit();
    }

    /**
     * 回滚
     */
    public function dbRollBack(){
        $VPDO = Xphp::instance('VPDO');
        $VPDO->rollBack();
    }
    /**
     * 数据库 =>end
     *************************************************************************************/



    /*************************************************************************************
     * 消息 =>start
     */
    /**
     * 返回消息到UI统一接口
     * @param boolean $result   操作结果   true/false
     * @param string $operate  操作描述
     * @param string $msg       消息描述/具体消息
     * @param string $level     提示等级   success/info/warning/error
     * @param int $errorCode    错误码
     * @param array $extInfo    附加信息
     * @return string           json
     */
    public function muOpResult($result, $operate, $msg = '', $level = '', $errorCode = '', $extInfo = ''){
        $errorCode = empty($errorCode) ? 0 : $errorCode; // php版本兼容问题
        $oldMsg = $msg;
        $this->writeLog("errorCode: ".$errorCode);
        if($result){
            if(!$msg){
                $msg = $operate . Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
            }
            $lev = "success";
        }else{
            $lev = "info";
        }
        if(!$msg){
            $msg = $operate . Xphp::$_lang['WEB_PUBLIC_FAILURE'];
        }
        if($errorCode > 0){
            $error = include CONF_PATH . 'error.php';
            $errorKey = $error['errorCode'][$errorCode];
            $msg .= "," . Xphp::$_lang['WEB_OPHANDLER_ERROR_CODE'] . ": #" . $errorCode . ",";
            //特殊处理错误码为20,Socket连接错误
            if($errorCode == 20 && $operate == Xphp::$_lang['WEB_NODE_APPLIANCE_OP_ADD']){
                $msg .= Xphp::$_lang['WEB_NETWORK_CONNECT_ERROR'];
            }else{
                $msg .= Xphp::$_lang['WEB_OPHANDLER_ERROR_DES'] . ": " . $error['errorCodeDes'][$errorKey];
            }
            $level = "warning";
        }

        if($errorCode < 0){
            //处理数据库CDP的情况 
            $pMsg = $msg;
            $msg = $operate . Xphp::$_lang['WEB_PUBLIC_FAILURE'];
            $errorCode = abs($errorCode);
            $error = include CONF_PATH . 'dbcdp_error.php';
            $errorKey = $error['errorCode'][$errorCode];
            $errorDes = $error['errorCodeDes'][$errorKey];
            if(empty($errorDes)){
                //如果没有对应的错误描述,使用后台返回的错误.
                $errorDes = $pMsg;
            }
            $msg .= "," . Xphp::$_lang['WEB_OPHANDLER_ERROR_CODE'] . ": #-" . $errorCode . "," .
                Xphp::$_lang['WEB_OPHANDLER_ERROR_DES'] . ": " . $errorDes;

            if(!empty($oldMsg)){
                $msg .= "," . $oldMsg;
            }

            $level = "warning";
        }
        if($level){
            $lev = $level;
        }
        $opMsg = array(
            're' => $result,
            'lev' => $lev,
            'msg' => $msg,
            'title' => $operate,
        );
        if(!empty($extInfo)){
            $opMsg['ext'] = $extInfo;
        }
        return json_encode($opMsg);
    }

    /**
     * 统一调用SOCKET发送消息
     * @param string $opName        操作名
     * @param JSON $msg             JSON消息
     * @param int $module           模块号
     * @param int $submodule_type   子模块号
     * @param bool $sync            同步方式    同步/true 异步/false
     * @param bool $command         是否为命令模式(消息发送成功后立即返回),默认FALSE
     * @param string $nodeuuid      节点UUID,不带此参数表示不发送到节点
     * @param bool $newInstance     是否重新实例化类,默认false
     * @return Ambigous <boolean, string>
     */
    private function mbMsg($opName, $msg, $module, $sync, $command, $submodule_type = 0, $nodeuuid = null, $newInstance = false){
        $socket = Xphp::instance('Sockets', '', '', $newInstance);
        return $socket->opMsg($opName, $msg, $module, $submodule_type, $sync, $command, $nodeuuid);
    }

    /**
     * 平台发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg     JSON消息
     * @param bool $sync    是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     */
    protected function mbPFMsg($opName, $msg, $sync = FALSE, $command = FALSE){
        $result = $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['PF'], $sync, $command);
        if("PT_SYSTEM_BACKGROUND_OP_DO_COMMAND" == $opName){
            //发送系统命令执行结果判断过滤
            //发送系统命令执行结果判断过滤,两种不同消息，返回不一样（同步和非同步）
            if(array_key_exists('errorCode', $result)){
                if(0 !== $result['errorCode']){
                    $result['result'] = false;
                }
            }
            if(array_key_exists('msg', $result)){
                if(0 !== $result['msg']['command_result']){
                    $result['result'] = false;
                }
            }
        }
        return $result;
    }

    /**
     * 发送消息到节点
     * @param string $opName    操作的名字定义
     * @param string $nodeuuid  节点UUID
     * @param json $msg         JSON消息
     * @param bool $sync        是否同步获取消息，默认FALSE
     * @param bool $command     是否为命令模式(消息发送成功后立即返回),默认FALSE
     * @return array
     */
    protected function mbNodeMsg($opName, $nodeuuid, $msg, $sync = FALSE, $command = FALSE, $submodule_type = 0){
        $result = $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['NODE'], $sync, $command, $submodule_type, $nodeuuid);
        if("PT_SYSTEM_BACKGROUND_OP_DO_COMMAND" == $opName){
            //发送系统命令执行结果判断过滤,两种不同消息，返回不一样（同步和非同步）
            if(array_key_exists('errorCode', $result)){
                if(0 !== $result['errorCode']){
                    $result['result'] = false;
                }
            }
            if(array_key_exists('msg', $result)){
                if(0 !== $result['msg']['command_result']){
                    $result['result'] = false;
                }
            }
        }
        return $result;
    }

    /**
     * 发送消息到代理端
     * @param string $opName    操作的名字定义
     * @param string $agentUUID 代理端UUID
     * @param json $msg         JSON消息
     * @param int $timeout      请求超时时间,如果不设置,采用默认超时
     */
    protected function mbAgentMsg($opName, $agentUUID, $msg, $timeout = FALSE){
        if($timeout === FALSE){
            //如果未设置超时时间,使用默认超时
            $timeout = Xphp::$_config['SOCKET_INFO']['agentTimeout'];
        }
        $timeout = intval($timeout);
        //组合第四层包头
        $allMsg = array(
            'tmp_store_uuid' => $agentUUID,
            'tmp_store_type' => Xphp::$_config['SOCKET_INFO']['BdMessagePostion']['module_client'],
            'response_timeout' => $timeout,
            'error_code' => 0,
            'json_data_string' => $msg,
        );
        $allMsg = json_encode($allMsg);
        return $this->mbPFMsg($opName, $allMsg, true);
    }

    /**
     * 发送消息到数据库代理端
     * @param int   $submoduleType  子模块号
     * @param string $opName    操作的名字定义
     * @param string $agentUUID 代理端UUID
     * @param json $msg         JSON消息
     * @param int $timeout      请求超时时间,如果不设置,采用默认超时
     */
    protected function mbDBAgentMsg($submoduleType, $opName, $agentUUID, $msg, $timeout = FALSE){
        if($timeout === FALSE){
            //如果未设置超时时间,使用默认超时
            $timeout = Xphp::$_config['SOCKET_INFO']['agentTimeout'];
        }
        $timeout = intval($timeout);
        //组合第四层包头
        $allMsg = array(
            'tmp_store_uuid' => $agentUUID,
            'tmp_store_type' => Xphp::$_config['SOCKET_INFO']['BdMessagePostion']['module_client'],
            'response_timeout' => $timeout,
            'error_code' => 0,
            'json_data_string' => $msg,
        );
        $allMsg = json_encode($allMsg);
        return $this->mbDBMsg($submoduleType, $opName, $allMsg, true);
    }

    /**
     * TODO:虚拟机模块发送消息
     * @param string $nodeuuid      节点UUID
     * @param int $submodule_type   子模块号
     * @param string $opName 操作的名字定义
     * @param json $msg     JSON消息
     * @param bool $sync    是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     * @param bool $newInstance 是否重新实例化类,默认FALSE
     */
    protected function mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, $sync = FALSE, $command = FALSE, $newInstance = FALSE){
        //添加虚拟机模块的sub_module_type
        $msgArr = json_decode($msg, true);
        $msgArr['sub_module_type'] = intval($this->getVmSubModuleType($submodule_type));
        $msg = json_encode($msgArr);
        return $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['VM'], $sync, $command, $submodule_type, $nodeuuid, $newInstance);
    }

    /**
     * 获取虚拟机模块的子模块类型
     * @param $hypervisor
     * @return mixed
     */
    private function getVmSubModuleType($hypervisor)
    {
        $vmGroup = Xphp::$_config['VMHYPERVISORGROUP'];
        if (!in_array($hypervisor, $vmGroup['openstack']) && !in_array($hypervisor, $vmGroup['publiccloud'])) {
            return Xphp::$_config['VM_SUB_MODULE']['VM'];
        }
        if (in_array($hypervisor, $vmGroup['openstack'])) {
            return Xphp::$_config['VM_SUB_MODULE']['PRIVATE_CLOUD'];
        }
        if (in_array($hypervisor, $vmGroup['publiccloud'])) {
            return Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'];
        }
        return Xphp::$_config['VM_SUB_MODULE']['VM'];
    }

    /**
     * TODO:虚拟机CDP模块发送消息
     * @param string $nodeuuid      节点UUID
     * @param int $submodule_type   子模块号
     * @param string $opName 操作的名字定义
     * @param json $msg     JSON消息
     * @param bool $sync    是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     * @param bool $newInstance 是否重新实例化类,默认FALSE
     */
    protected function mbCDPMsg($nodeuuid, $submodule_type, $opName, $msg, $sync = FALSE, $command = FALSE, $newInstance = FALSE){
        return $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['VDDT_SERVER'], $sync, $command, $submodule_type, $nodeuuid, $newInstance);
    }

    /**
     * TODO:虚拟机副本模块发送消息
     * @param string $nodeuuid      节点UUID
     * @param int $submodule_type   子模块号
     * @param string $opName 操作的名字定义
     * @param json $msg     JSON消息
     * @param bool $sync    是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     * @param bool $newInstance 是否重新实例化类,默认FALSE
     */
    protected function mbCopyMsg($nodeuuid, $submodule_type, $opName, $msg, $sync = FALSE, $command = FALSE, $newInstance = FALSE){
        return $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'], $sync, $command, $submodule_type, $nodeuuid, $newInstance);
    }

    /**
     * TODO:文件模块发送消息
     * @param string $nodeuuid      节点UUID
     * @param string $opName 操作的名字定义
     * @param json $msg     JSON消息
     * @param bool $sync    是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     */
    protected function mbFSMsg($nodeuuid, $opName, $msg, $sync = FALSE, $command = FALSE){
        //文件单独处理后台进程无返回，获取结果超时的报错，在socket里面写setRecvTimeout方法覆盖原先的900s超时
        if($opName == 'FS_OP_TYPE_SEARCH_RESULT_GET' || $opName == 'FS_OP_TYPE_SEARCH_THREAD_CREATE') {
            $socket = Xphp::instance('Sockets', '', '', false);
            $socket->setRecvTimeout(30);
            return $socket->opMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['FS'], '', $sync, $command, $nodeuuid);
        }
        return $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['FS'], $sync, $command, 0, $nodeuuid);
    }

    /**
     * TODO:数据库模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg     JSON消息
     * @param bool $sync    是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     */
    protected function mbDBMsg($nodeuuid, $submodule_type, $opName, $msg, $sync = FALSE, $command = FALSE){
        return $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['DB'], $sync, $command, $submodule_type, $nodeuuid);
    }

    /**
     * TODO:操作系统模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg     JSON消息
     * @param bool $sync    是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     */
    protected function mbOSMsg($nodeuuid, $opName, $msg, $sync = FALSE, $command = FALSE){
        return $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['OS'], $sync, $command,0,$nodeuuid);
    }
    /**
     * @function 卷CDP系统模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg     JSON消息
     * @param bool $sync    是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     */
    protected function mbVolCdpMsg($nodeuuid,$opName, $msg, $sync = FALSE, $command = FALSE,$newInstance = FALSE){
        return $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['VOL_CDP'], $sync, $command,0,$nodeuuid,$newInstance);
    }
    /**
     * @function 容灾主机模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbTempAgentMsg($nodeuuid, $opName, $msg, $sync = FALSE, $command = FALSE, $newInstance = FALSE)
    {
        return $this->mbMsg($opName, $msg, Xphp::$_config['MODULE_TYPE']['TEMP_AGENT'], $sync, $command, 0, $nodeuuid, $newInstance);
    }

    /**************************************************************************************
     * 写系统日志到数据库'NORMAL' =>　1,
    'WARN' => 2,
    'ERROR' => 3,
     */

    /**
     * 写系统日志到数据库
     * @param string $descriptionKey        日志KEY
     * @param array $descriptionParam       日志参数
     * @param int $logLevel                 日志等级  NORMAL 1/ WARN 2/ ERROR 3
     * @param int $errorCode                错误码
     */
    public function systemLog($descriptionKey, $descriptionParam = array(), $logLevel = 1, $errorCode = 0){
        $msg = array(
            'op_user_uuid' => Xphp::$_user['useruuid'],
            'op_user_name' => Xphp::$_user['username'],
            'agent_uuid' => '',
            'agent_name' => '',
            'op_time' => date("Y-m-d H:i:s"),
            'description_key' => $descriptionKey,
            'description_param' => $this->groupSystemLogParams($descriptionParam),
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );

        $msg = json_encode($msg);
        $socket = Xphp::instance('Sockets');
        $logType = Xphp::$_config['SOCKET_INFO']['BdLogRouterHeader']['system_log'];
        return $socket->logMsg($msg, $logType);
    }

    /**
     * 写任务日志到数据库
     * @param array  $params                       任务日志参数
     * @param string $descriptionKey        日志KEY
     * @param array $descriptionParam       日志参数
     * @param int $logLevel                 日志等级  NORMAL 1/ WARN 2/ ERROR 3
     * @param int $errorCode                错误码
     */
    public function taskLog($params, $descriptionKey, $descriptionParam = array(), $logLevel = 1, $errorCode = 0){
        $msg = array(
            'op_user_uuid' => Xphp::$_user['useruuid'],
            'op_user_name' => Xphp::$_user['username'],
            'task_uuid' => $params['task_uuid'],
            'task_name' => $params['task_name'],
            'task_type' => $params['task_type'],
            'module_type' => $params['module_type'],
            'submodule_type' => $params['submodule_type'],
            'running_flag' => 2,
            'write_to_notice_flag' => 2,
            'op_time' => date("Y-m-d H:i:s"),
            'description_key' => $descriptionKey,
            'description_param' => $this->groupSystemLogParams($descriptionParam),
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );

        $msg = json_encode($msg);
        $socket = Xphp::instance('Sockets');
        $logType = Xphp::$_config['SOCKET_INFO']['BdLogRouterHeader']['task_log'];
        return $socket->logMsg($msg, $logType);
    }


    /**
     * 组合系统日志参数消息
     * @param array $descriptionParam array(string|array(type => module_type|backup_mode, value => ?))
     */
    private function groupSystemLogParams($descriptionParam){
        $info = array();
        foreach ($descriptionParam as $des){
            if(is_array($des)){
                //如果是数组
                $info[] = $des["type"] . ":" . $des["value"];
            }else{
                $info[] = "S:" . $des;
            }
        }
        return $info;
    }

    /**
     * 专门用于登录系统写日志,其他地方使用systemLog
     * @param string $useruuid
     * @param string $username
     * @param string $descriptionKey
     * @param array $descriptionParam
     * @param number $logLevel
     * @param number $errorCode
     */
    protected function loginLog($useruuid, $username, $descriptionKey, $descriptionParam = array(),
                                $logLevel = 1, $errorCode = 0){
        $msg = array(
            'op_user_uuid' => $useruuid,
            'op_user_name' => $username,
            'agent_uuid' => '',
            'agent_name' => '',
            'op_time' => date("Y-m-d H:i:s"),
            'description_key' => $descriptionKey,
            'description_param' => $descriptionParam,
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );
        $msg = json_encode($msg);
        $socket = Xphp::instance('Sockets');
        $logType = Xphp::$_config['SOCKET_INFO']['BdLogRouterHeader']['system_log'];
        return $socket->logMsg($msg, $logType);
    }

    /**
     * 专门用于退出登录系统写日志,其他地方使用systemLog
     * @param string $useruuid
     * @param string $username
     * @param string $descriptionKey
     * @param array $descriptionParam
     * @param number $logLevel
     * @param number $errorCode
     */
    public function loginOutLog($useruuid, $username, $descriptionKey, $descriptionParam = array(),
                                $logLevel = 1, $errorCode = 0){
        $msg = array(
            'op_user_uuid' => $useruuid,
            'op_user_name' => $username,
            'agent_uuid' => '',
            'agent_name' => '',
            'op_time' => date("Y-m-d H:i:s"),
            'description_key' => $descriptionKey,
            'description_param' => $descriptionParam,
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );
        $msg = json_encode($msg);
        $socket = Xphp::instance('Sockets');
        $logType = Xphp::$_config['SOCKET_INFO']['BdLogRouterHeader']['system_log'];
        return $socket->logMsg($msg, $logType);
    }




    /**
     * 专门用于后台进程写日志,其他地方使用systemLog
     * @param string $useruuid
     * @param string $username
     * @param string $descriptionKey
     * @param array $descriptionParam
     * @param number $logLevel
     * @param number $errorCode
     */
    protected function daemonLog($descriptionKey, $descriptionParam = array(), $logLevel = 1, $errorCode = 0){
        $sql = "select user_uuid, user_name from bd_user limit 0, 1";
        $data = $this->dbSelect($sql, array());
        $useruuid = $data[0]['user_uuid'];
        $username = $data[0]['user_name'];
        $msg = array(
            'op_user_uuid' => $useruuid,
            'op_user_name' => $username,
            'agent_uuid' => '',
            'agent_name' => '',
            'op_time' => date("Y-m-d H:i:s"),
            'description_key' => $descriptionKey,
            'description_param' => $this->groupSystemLogParams($descriptionParam),
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );
        $msg = json_encode($msg);
        $socket = Xphp::instance('Sockets');
        $logType = Xphp::$_config['SOCKET_INFO']['BdLogRouterHeader']['system_log'];
        return $socket->logMsg($msg, $logType);
    }

    /*************************************************************************************/


    /**
     * 消息 =>end
     *************************************************************************************/


    /*************************************************************************************
     * 日志 =>start
     */

    /**
     * 组合消息
     * @param string $msg
     * @param int $type
     */
    private function groupMsg($msg, $type){
        $logMsg = '';
        $debugInfo = debug_backtrace();
        $logLevel = array_search($type, Xphp::$_config['LOG_INFO'], true);
        $i = 0;
        foreach ($debugInfo as $value){
            if("writeLog" == $value['function']){
                break;
            }
            $i++;
        }
        if($type == Xphp::$_config['LOG_INFO']['ERROR']){
            $utils = Xphp::instance('Utils');
            $msg = $utils->getErrorDes($msg);
        }
        $logMsg .= date("Y-m-d H:i:s")." [" . $logLevel . "] ";
        $logMsg .= $msg." : file: " . $debugInfo[$i]['file'] . " on line ". $debugInfo[$i]['line'] . ", ";
        $logMsg .=  "class: " . $debugInfo[$i+1]['class'] . ", function: ". $debugInfo[$i+1]['function'] . "." . PHP_EOL;
        return $logMsg;
    }

    /**
     * 写日志,只用于WEB写文件,调试用
     * @param string $msg   日志消息,可以是自定义消息,|error日志可以是错误定义名字或错误号
     * @param int $type  消息类型           info/notice/warning/error       1/2/3/4
     */
    public function writeLog($msg, $type = 1){
        $file = Xphp::$_config['LOG_INFO']['PATH']. "-" .date("Y-m-d") . '.log';
        if(!Xphp::$_config['LOG_INFO']['DEBUG']){
            //如果不是调试,直接返回
            return true;
        }
        $msg = $this->groupMsg($msg, $type);
        $count = file_put_contents($file, $msg, FILE_APPEND | LOCK_EX);
        if($count > 0){
            return true;
        }
        return false;
    }

    /**
     * 日志 =>end
     *************************************************************************************/


    /*************************************************************************************
     * 辅助函数 =>start
     */

    /**
     * 参数检测
     * 要求检测的参数不能是空,如果检测的参数有空则直接返回参数错误,程序强制退出
     */
    protected function paramsCheck(){
        $nums = func_num_args();
        $args = func_get_args();
        $flag = false;
        for($i=0; $i<$nums; $i++){
            if (is_string($args[$i])) {
                $flag = $flag || !$this->checkEmpty($args[$i]);
            } else {
                $flag = $flag || empty($args[$i]);
            }
        }
        if($flag){
            $this->writeLog("params empty check error.", 3);
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_OPHANDLER_PARAMS_NULL'], 'warning'));
        }
    }

    /**
     * 输入框参数检测
     * 特别用于检查输入框传值是否为空
     */
    protected function checkEmpty($str){
        if($str) {
            $newtrim = trim($str)??'';
        } else {
            $newtrim = $str??'';
        }

        if(strlen($newtrim) > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 转化时间戳为2012-12-12 12:12:12格式
     * @param unknown $timestamp
     * @return string
     */
    protected function parseDate($timestamp){
        if(empty($timestamp)){
            return  Xphp::$_config['TIMESPACE'];
        }
        return date("Y-m-d H:i:s", $timestamp);
    }

    /**
     * 辅助函数 =>end
     *************************************************************************************/
}

?>