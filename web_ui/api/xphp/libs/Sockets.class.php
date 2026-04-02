<?php
class Sockets {
    protected $SOCKET;
    protected $SOCKET_STATUS = FALSE;
    protected $IP;
    protected $PORT;
    protected $recvByte;
    protected $recTimeout;
    protected $sendTimeout;
    
    protected $magic_number;
    protected $sequence_number;
    protected $source_type;
    protected $message_level1_type;
    protected $message_level1_log_type;
    protected $message_level2_type;
    
    protected $opcode_level;
    protected $opcode_type;
    protected $opcode;
    
    protected $opHandler;
    
    
    function __construct() {
        //初始化SOCKET信息
        $this->IP = Xphp::$_config['SOCKET_INFO']['IP'];
        $this->PORT = Xphp::$_config['SOCKET_INFO']['Port'];
        $this->recvByte = Xphp::$_config['SOCKET_INFO']['recvByte'];
        $this->recTimeout = Xphp::$_config['SOCKET_INFO']['recTimeout'];
        $this->sendTimeout = Xphp::$_config['SOCKET_INFO']['sendTimeout'];
        //初始化包头信息
        $this->magic_number = Xphp::$_config['SOCKET_INFO']['BdHeader']['magic_number'];
        $this->sequence_number = Xphp::$_config['SOCKET_INFO']['BdHeader']['sequence_number'];
        $this->source_type = Xphp::$_config['SOCKET_INFO']['BdRouterHeader']['source_type'];
        $this->message_level1_type = Xphp::$_config['SOCKET_INFO']['BdRouterHeader']['message_level1_type'];
        $this->message_level1_log_type = Xphp::$_config['SOCKET_INFO']['BdLogRouterHeader']['message_level1_type'];
        $this->message_level2_type = Xphp::$_config['SOCKET_INFO']['BdRouterHeader']['message_level2_type'];
        //初始化操作信息
        $this->opcode_level = $this->opcode_type = $this->opcode = 0;
        
    }
    
    function __destruct(){
        $this->socketClose();
    }
    
    /**
     * 连接socket
     */
    private function socketConnect(){
        //初始化操作类
        $this->opHandler = Xphp::instance('OPHandler');
        
        $this->SOCKET = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        
        if ($this->SOCKET === false) {
            //             echo "socket_create() failed.\n reason: " . socket_strerror(socket_last_error()) . "\n";
            $this->opHandler->writeLog('PF_SOCKET_CREATE_ERROR', Xphp::$_config['LOG_INFO']['ERROR']);
            $this->SOCKET_STATUS = $this->getErrorCode('PF_SOCKET_CREATE_ERROR');
        }else{
            socket_set_block($this->SOCKET);
            socket_set_option($this->SOCKET,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>$this->recTimeout, "usec"=>0 ) );
            socket_set_option($this->SOCKET,SOL_SOCKET,SO_SNDTIMEO,array("sec"=>$this->sendTimeout, "usec"=>0 ) );
        }
        $result = socket_connect($this->SOCKET, $this->IP, $this->PORT);
        if($result === false) {
//             echo "socket_connect() failed.\n reason: ($result) " . socket_strerror(socket_last_error($this->SOCKET)) . "\n";
            $this->opHandler->writeLog('PF_SOCKET_CONNECT_ERROR', Xphp::$_config['LOG_INFO']['ERROR']);
            $this->SOCKET_STATUS = $this->getErrorCode('PF_SOCKET_CONNECT_ERROR');
        }
    }
    
    /**
     * 关闭socket
     */
    private function socketClose(){
        if(is_resource($this->SOCKET)){
            //防止关闭了又来关闭,找不到资源
            socket_close($this->SOCKET);
        }
    }

    /**
     * 用于文件模块，后台一直无返回时，覆盖原先的900s超时
     */
    public function setRecvTimeout(int $timeout){
        $this->recTimeout = $timeout;
    }

    
    /**
     * 发送消息
     * @param string $msg
     * @return boolean
     */
    private function sendMsg($msg){
        $length = strlen($msg);
        while (true) {
            $sent = socket_write($this->SOCKET, $msg, $length);
            if ($sent === false) {
                $this->opHandler->writeLog('PF_SOCKET_SEND_ERROR', Xphp::$_config['LOG_INFO']['ERROR']);
                return false;
            }
            // Check if the entire message has been sented
            if ($sent < $length) {
                // If not sent the entire message.
                // Get the part of the message that has not yet been sented as message
                $msg = substr($msg, $sent);
                // Get the length of the not sented part
                $length -= $sent;
            } else {
                break;
            }
        }
        return true;
    }
    
    /**
     * 接收消息
     * @return string
     */
    private function recvMsg($length){
        $opHandler = Xphp::instance('OPHandler');
        $data = "";
        $buf = socket_recv($this->SOCKET, $data, $length, MSG_WAITALL);
        if ($buf === false) {
            $this->opHandler->writeLog('PF_SOCKET_RECV_ERROR', Xphp::$_config['LOG_INFO']['ERROR']);
            return false;
        }
        return $data;
    }
    
    /**
     * 组装消息头 第一部分BdHeader
     * @param string $msg 从第二部分开始的所有消息
     * @return string
     */
    private function bdHeader($msg){
        $packStr = pack("I4", $this->magic_number, strlen($msg), $this->sequence_number, $this->sequence_number);
        $msg = $packStr . $msg;
        return $msg;
    }
    
    /**
     * 组装消息头 第二部分BdRouterHeader
     * @param unknown $length
     * @return string
     */
    private function bdRouterHeader($msg, $module, $nodeuuid){
        $utils = Xphp::instance("Utils");
        $source_uuid = $utils->uuid();
        $length = strlen($msg);
        $destType = $this->getDestType($module);
        // 从数据库取得模块UUID
        $destination_uuid = $this->getModuleUUID($module, $nodeuuid);
        $packStr = pack("a40a40I6", $source_uuid, $destination_uuid, $this->source_type, $destType, 
                        $module, $length, $this->message_level1_type, $this->message_level2_type);
        $msg = $packStr . $msg;
        return $msg;
    }
    
    /**
     * 组装消息头 日志消息第二部分BdRouterHeader
     * @param unknown $length
     * @return string
     */
    private function bdLogRouterHeader($msg, $module, $logType){
        $utils = Xphp::instance("Utils");
        $source_uuid = $utils->uuid();
        $length = strlen($msg);
        $destType = $this->getDestType($module);
        // 从数据库取得模块UUID
        $destination_uuid = $this->getModuleUUID($module, null);
        $packStr = pack("a40a40I6", $source_uuid, $destination_uuid, $this->source_type, $destType,
            $module, $length, $this->message_level1_log_type, $logType);
        $msg = $packStr . $msg;
        return $msg;
    }
    
    /**
     * 获取第三部分的用户信息
     */
    private function getModuleHeaderUser(){
        $user_uuid = Xphp::$_user['useruuid'];
        if(empty($user_uuid)){
            //如果UUID是空,直接找到数据库第一条记录的用户UUID,如果没有用户UUID,消息会被后台丢弃
            $opHandler = Xphp::instance('OPHandler');
            $sql = "select user_uuid from bd_user limit 0, 1";
            $data = $opHandler->dbSelect($sql, array());
            $user_uuid = $data[0]['user_uuid'];
        }
        return $user_uuid;
    }
    
    /**
     * 组装消息头 第三部分 BdModuleHeader
     * @param unknown $msg
     * @param unknown $module
     * @return string
     */
    private function bdModuleHeader($msg, $module, $submodule_type, $sync, $opName){
        $utils = Xphp::instance("Utils");
        $user_uuid = $this->getModuleHeaderUser();
        $source_thread_uuid = '';
        $destination_thread_uuid = '';
        $message_mode_type = $sync;
        $module_type = $module;
        $this->setOpCodeInfo($module, $opName);
        $msg = !empty($msg)?$msg:"";
        $body_len = strlen($msg);
        $packStr = pack("a40a40a40I8", $user_uuid, $source_thread_uuid, $destination_thread_uuid, $message_mode_type, 
                        $module_type, $submodule_type, $this->opcode_level, $this->opcode_type, $this->opcode, 0, $body_len);
        $msg = $packStr . $msg;
        return $msg;
    }
    
    /**
     * 根据操作名得到操作信息
     * @param int $module
     * @param string $opName
     */
    private function setOpCodeInfo($module, $opName){
        //按模块实例化opcode对象
        $opcodeHandler = Xphp::instance(Xphp::$_config['MODULE_OPCODE_TYPE'][$module]);
        $opcodeInfo = $opcodeHandler->getOpcodeInfo($opName);
        if(empty($opcodeInfo)){
            //如果在特定模块下没有找到,使用平台PUBLIC
            $module = Xphp::$_config['MODULE_TYPE']['PF'];
            $opcodeHandler = Xphp::instance(Xphp::$_config['MODULE_OPCODE_TYPE'][$module]);
            $opcodeInfo = $opcodeHandler->getOpcodeInfo($opName);
            if(empty($opcodeInfo)){
                //如果在平台PUBLIC没有找到,使用平台PRIVATE
                $module = Xphp::$_config['MODULE_TYPE']['PFPRIVATE'];
                $opcodeHandler = Xphp::instance(Xphp::$_config['MODULE_OPCODE_TYPE'][$module]);
                $opcodeInfo = $opcodeHandler->getOpcodeInfo($opName);
            }
        }
        $this->opcode_level = $opcodeInfo['level'];
        $this->opcode_type = $opcodeInfo['type'];
        $this->opcode = $opcodeInfo['code'];
    }
    
    /**
     * 根据模块号得到模块UUID
     * @param unknown $module
     */
    private function getModuleUUID($module, $nodeuuid){
        $opHandler = Xphp::instance('OPHandler');
        if($nodeuuid){
            //如果有节点参数,通过模块类型和节点UUID找到对应模块UUID
            $sql = "select module_uuid from bd_module_server where module_type = ? and node_uuid = ?";
            $uuid = $opHandler->dbSelect($sql, array($module, $nodeuuid));
        }else{
            $sql = "select module_uuid from bd_module_server where module_type = ?";
            $uuid = $opHandler->dbSelect($sql, array($module));
        }
        if($uuid){
            return $uuid[0]['module_uuid'];
        }
    }
    
    /**
     * 根据模块类型得到destination_type
     * @param unknown $module
     * @return number
     */
    private function getDestType($module){
        return Xphp::$_config['MODULE_TYPE']['PF'] == $module ? 6 : 4;
    }
    
    /**
     * 组合消息包
     * @param JSON $msg             消息
     * @param int $module           模块类型
     * @param int $submodule_type   子模块类型
     * @param bool $sync            同步模式
     * @param string  $opName       操作名
     * @return string
     */
    private function groupMsg($msg, $module, $submodule_type, $sync, $opName, $nodeuuid){
        $bdModuleHeader = $this->bdModuleHeader($msg, $module, $submodule_type, $sync, $opName);
        $bdRouterHeader = $this->bdRouterHeader($bdModuleHeader, $module, $nodeuuid);
        $bdHeader = $this->bdHeader($bdRouterHeader);
        return $bdHeader;
    }
    
    /**
     * 发送和接收消息统一流程
     * @param unknown $msg
     * @param unknown $module
     * @param unknown $submodule_type
     * @param unknown $sync
     * @param unknown $opName
     * @return boolean|Ambigous <string, boolean>   成功返回JSON消息
     */
    private function sendAndGetMsg($msg, $module, $submodule_type, $sync, $opName, $nodeuuid){
        //连接socket
        $this->socketConnect();
        $msg = $this->groupMsg($msg, $module, $submodule_type, $sync, $opName, $nodeuuid);
        
        $sendFlag = $this->sendMsg($msg);
        if(!$sendFlag){
            return FALSE;
        }
        $rece = $this->recvMsg($this->recvByte);
        //检查后台程序状态
        $this->checkRecvMsg($rece);
        $moduleHeader = unpack("I8", substr($rece, 240));
        $msg = $this->recvMsg($moduleHeader[8]);
        //关闭socket
        $this->socketClose();
        return $msg;
    }
    
    /**
     * 检测收到的包是否是出错(没有目的地)
     * @param unknown $rece
     */
    private function checkRecvMsg($rece){
        //解析第二层包头,检查最后两个字段
        $moduleHeader = unpack("I6", substr($rece, 96, 120));
        $bdTargetConnLostRouterHeader = Xphp::$_config['SOCKET_INFO']['BdTargetConnLostRouterHeader'];
        if($moduleHeader[5] == $bdTargetConnLostRouterHeader['message_level1_type'] && 
            $moduleHeader[6] == $bdTargetConnLostRouterHeader['message_level2_type']){
            exit($this->opHandler->muOpResult(false, Xphp::$_lang['WEB_TIPS_SYSTEM_SERVICE_ERROR'], 
                Xphp::$_lang['WEB_TIPS_SYSTEM_SERVICE_ERROR_TIP'], 'warning', $this->getErrorCode('PF_SOCKET_SYSTEM_SERVICE_ERROR')));
        }
    }
    
    /**
     * 同步消息
     * @param unknown $msg
     * @param unknown $module
     * @return string 直接返回JSON字符串
     */
    private function syncMsg($msg, $module, $submodule_type, $sync, $command, $opName, $nodeuuid){
        $revMsg = $this->sendAndGetMsg($msg, $module, $submodule_type, $sync, $opName, $nodeuuid);
        if(!$revMsg){
            //接收消息失败
            $this->opHandler->writeLog('PF_SOCKET_RECV_ERROR', Xphp::$_config['LOG_INFO']['ERROR']);
            return array('result' => false, 'errorCode' => $this->getErrorCode('PF_SOCKET_RECV_ERROR'));
        }
        
        if($opName == "VM_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK"){
            //单独特殊处理读取细粒度文件
//             var_dump($revMsg);
            $msgHead = unpack("I2", substr($revMsg, 0, 8));
//             var_dump($msgHead);
            if(0 !== $msgHead[1]){
                //操作失败
                $returnArray = array(
                    'result' => false,
                    'errorCode' => $msgHead[1],
                    'errorMsg' => "Read File Failure!"
                );
                return $returnArray;
            }
            return substr($revMsg, 8);
        }
        
        if($opName == "VM_PRIVATE_TASK_OP_GRAIN_PROCESS_DOWNLOAD_DIR"){
            //单独特殊处理读取细粒度目录
            //             var_dump($revMsg);
            /*
             * $msgHead 注意下标从1开始的
             * uint32    reserved   保留字段
             * uint32    is_eof     是否读完   1读完 0未读完
             * unit32    error_code 错误码，正确为0
             * uint32    size       本次读取大小
             * */
            $msgHead = unpack("I4", substr($revMsg, 0, 16));
            $readMsg = array(
                'head' => $msgHead,
                'msg' => substr($revMsg, 16)
            );
            return $readMsg;
        }
        
        if($opName == "VM_PRIVATE_TASK_OP_PF_BACKUP_DOWNLOAD_BLOCK_FILE_BY_OFFSET"){
                //单独特殊处理读取平台备份数据
                $msgHead = unpack("I4", substr($revMsg, 0, 16));
                if(0 !== $msgHead[1]){
                    //操作失败
                    $returnArray = array(
                        'result' => false,
                        'errorCode' => $msgHead[1],
                        'errorMsg' => "Read File Failure!"
                    );
                    return $returnArray;
                }
                return substr($revMsg, 16);
        }
        
        if($opName == "NODE_SYS_OP_PREAD_FILE"){
            //单独特殊处理读取节点指定文件
            //             var_dump($revMsg);
            $msgHead = unpack("I2", substr($revMsg, 0, 8));
            //             var_dump($msgHead);
            if(0 !== $msgHead[1]){
                //操作失败
                $returnArray = array(
                    'result' => false,
                    'errorCode' => $msgHead[1],
                    'errorMsg' => "Read File Failure!"
                );
                return $returnArray;
            }
            return substr($revMsg, 8);
        }
        
        $revMsg = json_decode($revMsg, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
        if(0 !== $revMsg['error_code']){
            //操作失败
            $returnArray = array(
                'result' => false, 
                'errorCode' => $revMsg['error_code'],
                'errorMsg' => $revMsg['error_msg']
            );
            return $returnArray;
        }
        if(!array_key_exists('json_data_string', $revMsg)){
            //3层消息接收
            return array('result' => true, 'msg' => $revMsg['message']);
        }
        //4层消息接收,处理发送到代理的情况
        $jsonData2Arr = json_decode($revMsg['json_data_string'], true);
        return array('result' => true, 'msg' => $jsonData2Arr);;
    }
    
    /**
     * 异步消息
     * @param unknown $msg
     * @param unknown $module
     */
    private function asyncMsg($msg, $module, $submodule_type, $sync, $command, $opName, $nodeuuid){
        $revMsg = $this->sendAndGetMsg($msg, $module, $submodule_type, $sync, $opName, $nodeuuid);
        if(!$revMsg){
            return array('result' => false, 'errorCode' => $this->getErrorCode('PF_SOCKET_RECV_ERROR'));
        }
        if($command){
            //命令模式,直接返回
            return array('result'=>true, 'errorCode'=>0);
        }
        $revMsgArr = json_decode($revMsg, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
        $opID = $revMsgArr['op_id'];
        
        return $this->getOpStatus(intval($opID));
    }
    
    /**
     * 异步消息从数据库获取操作状态
     * @param int $opID
     * @return array $result
     */
    private function getOpStatus($opID){
        $result = array(
            'result' => false,
            'errorCode' => 0,
        );
        $sql = "select op_status,max_wait_time,op_error_code from bd_operation where op_id = ?";
        $startTime = time();
        do{
            $opInfo = $this->opHandler->dbSelect($sql, array($opID));
            if($opInfo){
                $status = intval($opInfo[0]['op_status']);
                $waitTime = intval($opInfo[0]['max_wait_time']);
                $errorCode = intval($opInfo[0]['op_error_code']);
                if(Xphp::$_config['OP_STATUS']['SUCCEED'] == $status){
                    //操作成功
                    $result['result'] = TRUE;
                    return $result;
                }elseif(Xphp::$_config['OP_STATUS']['FAILURE'] == $status){
                    $this->opHandler->writeLog('PF_SOCKET_OP_STATUS_ERROR: '. $errorCode, Xphp::$_config['LOG_INFO']['ERROR']);
                    $result['errorCode'] = $errorCode;
                    return $result;
                    //操作失败
                }elseif(Xphp::$_config['OP_STATUS']['RUNNING'] == $status){
                    //正在运行
                    sleep(1);
                    continue;
                }
            }else{
                $this->opHandler->writeLog('PF_SOCKET_GET_OP_STATUS_ERROR', Xphp::$_config['LOG_INFO']['ERROR']);
                $result['errorCode'] = $this->getErrorCode('PF_SOCKET_GET_OP_STATUS_ERROR');
                break;
            }
        }while((time() - $startTime) < $waitTime);
        $result['errorCode'] = $this->getErrorCode('PF_SOCKET_GET_OP_TIMEOUT');
        $this->opHandler->writeLog('PF_SOCKET_GET_OP_TIMEOUT', Xphp::$_config['LOG_INFO']['ERROR']);
        //没有查找到或查询超时返回
        return $result;
    }
    
    /**
     * 消息统一处理接口
     * @param string $opName        操作名
     * @param JSON $msg             JSON消息
     * @param int $module           模块号
     * @param int $submodule_type   子模块号
     * @param bool $sync            同步方式    同步/true 异步/false
     * @param bool $command          是否为命令模式(消息发送成功后立即返回)
     * @param string $nodeuuid      节点UUID
     * @return Ambigous <boolean, string>
     */
    public function opMsg($opName, $msg, $module, $submodule_type, $sync, $command, $nodeuuid){
        if($this->SOCKET_STATUS){
            //SOCKET初始化失败
            return array('result' => false, 'errorCode' => $this->SOCKET_STATUS);
        }
        if($sync){
            $sync = Xphp::$_config['MSG_MODE_TYPE']['SYNC'];
            return $this->syncMsg($msg, $module, $submodule_type, $sync, $command, $opName, $nodeuuid);
        }else{
            $sync = Xphp::$_config['MSG_MODE_TYPE']['RSYNC'];
            return $this->asyncMsg($msg, $module, $submodule_type, $sync, $command, $opName, $nodeuuid);
        }
    }
    
    /**
     * 平台日志统一处理接口
     * @param json $msg   日志内容
     * @param int $logType 日志类型    任务日志 1/系统日志2
     */
    public function logMsg($msg, $logType){
        $module = Xphp::$_config['MODULE_TYPE']['PF'];
        $content = $this->bdLogRouterHeader($msg, $module, $logType);
        $content = $this->bdHeader($content);
        $this->socketConnect();
        $msg = $this->sendMsg($content);
        $this->socketClose();
        return $msg;
    }
    
    /**
     * 通过错误名获取错误号
     * @param string $errorName
     * @return int 
     */
    private function getErrorCode($errorName){
        $error = include CONF_PATH . 'error.php';
        return array_search($errorName, $error['errorCode']);
    }
    
}

?>