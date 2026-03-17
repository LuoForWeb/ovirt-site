<?php
// phpcs:ignoreFile -- 框架类
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

namespace xphp\db;

use xphp\db\builder\Vpdo;
use xphp\Sockets;

class Op
{
    /************************************************************************************
     * 数据库 =>start
     */
    public $_basedb;
    /**
     * @var Sockets
     */
    public $_socket;

    public function __construct()
    {
        $this->_basedb = new Vpdo();

        $this->_socket = new Sockets();

    }

    /**
     * 数据库带参数查询，适用于select
     * @param string $sql SQL语句,如"select a from b where c = ?"
     * @param array $param SQL条件参数[1,2],每一项按位置对应SQL语句中的"?"，无参数不传
     * @param int $fetch_style 控制结果集中数据的返回方式:PDO::FETCH_ASSOC 关联数组形式, PDO::FETCH_NUM 数字索引数组形式, PDO::FETCH_BOTH 两者数组形式都有，这是默认的
     * @return fetchAll()
     */
    public function dbSelect(string $sql, $param = array(), $fetch_style = \PDO::FETCH_BOTH)
    {

        return $this->_basedb->sqlQuery($sql, $param, true, $fetch_style);
    }

    /**
     * 数据库带参数，适用于insert,delete
     * @param string $sql SQL语句,如"select a from b where c = ?"
     * @param array $param SQL条件参数[1,2],每一项按位置对应SQL语句中的"?"，无参数不传
     * @return int 受影响的行数
     */
    public function dbQuery(string $sql, $param = array())
    {

        return $this->_basedb->newExec($sql, $param, false);
    }

    /**
     * 查询sql执行结果,成功或失败,适用于insert,update或delete,有的地方会有updates受影响为0行,但是结果是成功的,方便处理
     * @param string $sql
     * @param array $param
     * @return boolean 返回成功和失败
     */
    public function dbExec(string $sql, $param = array())
    {
        $result = $this->dbQuery($sql, $param);

        if (false == $result && $result != 0) {
            //这是执行出错  0也会别识别为false
            return false;
        }
        //这是执行成功  返回受影响的行数为0或更多都任务成功
        return true;
    }

    /**
     * 得到最后一次插入的id,last_insert_id()
     */
    public function dbLastInsertId()
    {
        $sql = "select last_insert_id()";
        $data = $this->dbSelect($sql);
        return $data[0][0];
    }

    /**
     * 开始事务
     */
    public function dbBeginTransaction()
    {

        $this->_basedb->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function dbCommit()
    {

        $this->_basedb->commit();
    }

    /**
     * 回滚
     */
    public function dbRollBack()
    {

        $this->_basedb->rollBack();
    }
    /**
     * 数据库 =>end
     *************************************************************************************/


    /*************************************************************************************
     * 消息 =>start
     */
    /**
     * 返回消息到UI统一接口
     * @param boolean $result 操作结果   true/false
     * @param string $operate 操作描述
     * @param string $msg 消息描述/具体消息
     * @param string $level 提示等级   success/info/warning/error
     * @param int $errorCode 错误码
     * @param array $extInfo 附加信息
     * @return string           json
     */
    public function muOpResult(bool $result, string $operate, $msg = '', $level = '', $errorCode = 0, $extInfo = '')
    {
        $oldMsg = $msg;
        $this->writeLog("errorCode: " . $errorCode);

        if ($result && !$msg) {
            $msg = $operate . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        }

        if (!$msg) {
            $msg = $operate . xphp_get_lang('WEB_PUBLIC_FAILURE');
        }

        if ($errorCode > 0) {
            $error = xphp_get_config('error');
            $errorKey = $error['errorCode'][$errorCode];
            $msg .= "," . xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ": #" . $errorCode . ",";
            //特殊处理错误码为20,Socket连接错误
            if ($errorCode == 20 && $operate == xphp_get_lang('WEB_NODE_APPLIANCE_OP_ADD')) {
                $msg .= xphp_get_lang('WEB_NETWORK_CONNECT_ERROR');
            } else {
                $msg .= xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ": " . $error['errorCodeDes'][$errorKey];
            }
        }

        if ($errorCode < 0 && $errorCode != -1) {
            //处理数据库CDP的情况
            $pMsg = $msg;
            $msg = $operate . xphp_get_lang('WEB_PUBLIC_FAILURE');
            $errorCode = abs($errorCode);
            $errorCodes = xphp_get_desc('DbcdpError', 'errorCode');
            $errorCodeDes = xphp_get_desc('DbcdpError', 'errorCodeDes');
            $errorKey = $errorCodes[$errorCode];
            $errorDes = $errorCodeDes[$errorKey];
            if (empty($errorDes)) {
                //如果没有对应的错误描述,使用后台返回的错误.
                $errorDes = $pMsg;
            }
            $msg .= "," . xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ": #-" . $errorCode . "," .
                xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ": " . $errorDes;

            if (!empty($oldMsg)) {
                $msg .= "," . $oldMsg;
            }
        }

        return [
            'success' => $result,
            'code' => $errorCode == 0 ? 200 : $errorCode,
            'message' => $msg,
            'data' => ['info' => $extInfo],
            'title' => $operate
        ];

    }

    /**
     * 统一调用SOCKET发送消息
     * @param string $opName 操作名
     * @param JSON $msg JSON消息
     * @param int $module 模块号
     * @param int $submodule_type 子模块号
     * @param bool $sync 同步方式    同步/true 异步/false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     * @param string $nodeuuid 节点UUID,不带此参数表示不发送到节点
     * @param bool $newInstance 是否重新实例化类,默认false
     * @param int $timeout 自定义超时时间
     * @return Ambigous <boolean, string>
     */
    public function mbMsg(string $opName, string $msg, int $module, bool $sync, bool $command, $submodule_type = 0, string $nodeuuid = '', $newInstance = false, $timeout = 0)
    {
        return $this->_socket->opMsg($opName, $msg, $module, $submodule_type, $sync, $command, $nodeuuid, $timeout);
    }

    /**
     * 平台发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbPFMsg(string $opName, $msg, $sync = false, $command = false)
    {
        $result = $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['PF'], $sync, $command);
        if ("PT_SYSTEM_BACKGROUND_OP_DO_COMMAND" == $opName) {
            //发送系统命令执行结果判断过滤
            //发送系统命令执行结果判断过滤,两种不同消息，返回不一样（同步和非同步）
            if (array_key_exists('errorCode', $result)) {
                if (0 !== $result['errorCode']) {
                    $result['result'] = false;
                }
            }
            if (array_key_exists('msg', $result)) {
                if (0 !== $result['msg']['command_result']) {
                    $result['result'] = false;
                }
            }
        }
        return $result;
    }

    /**
     * 发送消息到节点
     * @param string $opName 操作的名字定义
     * @param string $nodeuuid 节点UUID
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     * @return array
     */
    public function mbNodeMsg(string $opName, string $nodeuuid, $msg, $sync = false, $command = false, $submodule_type = 0)
    {
        $result = $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['NODE'], $sync, $command, $submodule_type, $nodeuuid);
        if ("PT_SYSTEM_BACKGROUND_OP_DO_COMMAND" == $opName) {
            //发送系统命令执行结果判断过滤,两种不同消息，返回不一样（同步和非同步）
            if (array_key_exists('errorCode', $result)) {
                if (0 !== $result['errorCode']) {
                    $result['result'] = false;
                }
            }
            if (array_key_exists('msg', $result)) {
                if (0 !== $result['msg']['command_result']) {
                    $result['result'] = false;
                }
            }
        }
        return $result;
    }

    /**
     * 发送消息到代理端
     * @param string $opName 操作的名字定义
     * @param string $agentUUID 代理端UUID
     * @param json $msg JSON消息
     * @param int $timeout 请求超时时间,如果不设置,采用默认超时
     */
    public function mbAgentMsg(string $opName, string $agentUUID, $msg, $timeout = false)
    {
        if ($timeout === false) {
            //如果未设置超时时间,使用默认超时
            $timeout = xphp_get_config('socket')['agentTimeout'];
        }
        $timeout = intval($timeout);
        //组合第四层包头
        $allMsg = array(
            'tmp_store_uuid' => $agentUUID,
            'tmp_store_type' => xphp_get_config('socket')['BdMessagePostion']['module_client'],
            'response_timeout' => $timeout,
            'error_code' => 0,
            'json_data_string' => $msg,
        );
        $allMsg = json_encode($allMsg);
        return $this->mbPFMsg($opName, $allMsg, true);
    }

    /**
     * 发送消息到数据库代理端
     * @param int $submoduleType 子模块号
     * @param string $opName 操作的名字定义
     * @param string $agentUUID 代理端UUID
     * @param json $msg JSON消息
     * @param int $timeout 请求超时时间,如果不设置,采用默认超时
     */
    public function mbDBAgentMsg(int $submoduleType, string $opName, string $agentUUID, $msg, $timeout = false)
    {
        if ($timeout === false) {
            //如果未设置超时时间,使用默认超时
            $timeout = xphp_get_config('socket')['agentTimeout'];
        }
        $timeout = intval($timeout);
        //组合第四层包头
        $allMsg = array(
            'tmp_store_uuid' => $agentUUID,
            'tmp_store_type' => xphp_get_config('socket')['BdMessagePostion']['module_client'],
            'response_timeout' => $timeout,
            'error_code' => 0,
            'json_data_string' => $msg,
        );
        $allMsg = json_encode($allMsg);
        return $this->mbDBMsg($submoduleType, $opName, $allMsg, true);
    }

    /**
     * TODO:虚拟机模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param int $submodule_type 子模块号
     * @param string $opName 操作的名字定义
     * @param string $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     * @param bool $newInstance 是否重新实例化类,默认false
     */
    public function mbVMMsg(string $nodeuuid, int $submodule_type, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        //添加虚拟机模块的sub_module_type
        $msgArr = json_decode($msg, true);
        $msgArr['sub_module_type'] = intval($this->getVmSubModuleType($submodule_type));
        $msg = json_encode($msgArr);
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['VM'], $sync, $command, $submodule_type, $nodeuuid, $newInstance);
    }

    /**
     * 获取虚拟机模块的子模块类型
     * @param $hypervisor
     * @return mixed
     */
    private function getVmSubModuleType($hypervisor)
    {
        $vmGroup = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        if (!in_array($hypervisor, $vmGroup['privatecloud']) && !in_array($hypervisor, $vmGroup['publiccloud'])) {
            return xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
        }
        if (in_array($hypervisor, $vmGroup['privatecloud'])) {
            return xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        }
        if (in_array($hypervisor, $vmGroup['publiccloud'])) {
            return xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
        }
        return xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
    }

    /**
     * TODO:虚拟机CDP模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param int $submodule_type 子模块号
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     * @param bool $newInstance 是否重新实例化类,默认false
     */
    public function mbCDPMsg(string $nodeuuid, int $submodule_type, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['VDDT_SERVER'], $sync, $command, $submodule_type, $nodeuuid, $newInstance);
    }

    /**
     * TODO:虚拟机副本模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param int $submodule_type 子模块号
     * @param string $opName 操作的名字定义
     * @param string $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     * @param bool $newInstance 是否重新实例化类,默认false
     */
    public function mbCopyMsg(string $nodeuuid, int $submodule_type, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['COPY'], $sync, $command, $submodule_type, $nodeuuid, $newInstance);
    }

    /**
     * TODO:文件模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbFSMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false,$submodule_type = 0)
    {
        // 文件单独处理后台进程无返回，获取结果超时的报错，在socket里面写setRecvTimeout方法覆盖原先的900s超时
        if ($opName == 'FS_OP_TYPE_SEARCH_RESULT_GET' || $opName == 'FS_OP_TYPE_SEARCH_THREAD_CREATE') {
            $timeout = 30;
        } else {
            $timeout = 0;
        }
        return $this->mbMsg($opName, $msg, xphp_get_config('module', 'MODULE_TYPE')['FS'], $sync, $command, $submodule_type, $nodeuuid, false, $timeout);
    }

    /**
     * TODO:文件复制模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbFCMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['FILE_COPY'], $sync, $command, 0, $nodeuuid);
    }

    /**
     * TODO:Nas模块发送消息 暂时取文件的，nas的后台不支持
     * @param string $nodeuuid 节点UUID
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbNASMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module', 'MODULE_TYPE')['FS'], $sync, $command, 0, $nodeuuid);
    }

    /**
     * TODO:对象存储模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbOBSMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $submodule_type = 0)
    {

        return $this->mbMsg($opName, $msg, xphp_get_config('module', 'MODULE_TYPE')['FS'], $sync, $command, $submodule_type, $nodeuuid);
    }
    /**
     * TODO:hadoop模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbHADOOPMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $submodule_type = 0)
    {

        return $this->mbMsg($opName, $msg, xphp_get_config('module', 'MODULE_TYPE')['FS'], $sync, $command, $submodule_type, $nodeuuid);
    }

    /**
     * TODO:数据库模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbDBMsg(string $nodeuuid, $submodule_type, string $opName, $msg, $sync = false, $command = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['DB'], $sync, $command, $submodule_type, $nodeuuid);
    }

    /**
     * TODO:操作系统模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbOSMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $submodule_type = 0)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['OS'], $sync, $command, $submodule_type, $nodeuuid);
    }

    /**
     * @function 卷CDP系统模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbVolCdpMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'], $sync, $command, 0, $nodeuuid, $newInstance);
    }

    /**
     * TODO:M365模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认FALSE
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认FALSE
     */
    public function mbM365Msg(string $nodeuuid, $submodule_type, string $opName, $msg, $sync = FALSE, $command = FALSE)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['M365'], $sync, $command, $submodule_type, $nodeuuid);
    }


    /**
     * TODO:容器模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbK8SMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['KUBERNETES'], $sync, $command, 0, $nodeuuid);
    }


    /**
     * @function 数据库CDP系统模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbDbCdpMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['DB_CDP'], $sync, $command, 0, $nodeuuid, $newInstance);
    }

    /**
     * @function 容灾主机模块发送消息
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbTempAgentMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['TEMP_AGENT'], $sync, $command, 0, $nodeuuid, $newInstance);
    }

    /**
     * @description: 策略模块发消息Strategy-server
     * @param string $nodeuuid
     * @param string $opName
     * @param {*} $msg
     * @param bool $sync
     * @param bool $command
     * @param bool $newInstance
     * @return {*}
     */
    public function mbStrategyMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['STRATEGY'], $sync, $command, 0, $nodeuuid, $newInstance);
    }

 	/**
     * @description: 集群消息cluster_server
     * @param string $nodeuuid
     * @param string $opName
     * @param mixed $msg
     * @param bool $sync
     * @param bool $command
     * @param bool $newInstance
     * @return array|string
     */
    public function mbClusterMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['CLUSTER'], $sync, $command, 0, $nodeuuid, $newInstance);
    }

    /**
     * @description: 工具消息tool_server
     * @param string $nodeuuid
     * @param string $opName
     * @param mixed $msg
     * @param bool $sync
     * @param bool $command
     * @param bool $newInstance
     * @return array|string
     */
    public function mbToolMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module')['MODULE_TYPE']['TOOL'], $sync, $command, 0, $nodeuuid, $newInstance);
    }
    /**
     * TODO:数据验证模块发送消息
     * @param string $nodeuuid 节点UUID
     * @param string $opName 操作的名字定义
     * @param json $msg JSON消息
     * @param bool $sync 是否同步获取消息，默认false
     * @param bool $command 是否为命令模式(消息发送成功后立即返回),默认false
     */
    public function mbSRMsg(string $nodeuuid, string $opName, $msg, $sub_type = 0, $sync = false, $command = false)
    {

        return $this->mbMsg($opName, $msg, xphp_get_config('module', 'MODULE_TYPE')['SUREBACKUP'], $sync, $command, $sub_type, $nodeuuid, false);
    }


    /**
     * @description: 时间点消息
     */
    public function mbPointMsg(string $nodeuuid, string $opName, $msg, $sub_type = 0, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module', 'MODULE_TYPE')['BACKUP_DATA'], $sync, $command, $sub_type, $nodeuuid, $newInstance);
    }

    /**
     * @description: elite_rcvy_server 消息
     */
    public function mbGrainMsg(string $nodeuuid, string $opName, $msg, $sync = false, $command = false, $newInstance = false)
    {
        return $this->mbMsg($opName, $msg, xphp_get_config('module', 'MODULE_TYPE')['GRAIN'], $sync, $command, 0, $nodeuuid, $newInstance);
    }


    /**************************************************************************************
     * 写系统日志到数据库'NORMAL' =>　1,
     * 'WARN' => 2,
     * 'ERROR' => 3,
     */

    /**
     * 写系统日志到数据库
     * @param string $descriptionKey 日志KEY
     * @param array $descriptionParam 日志参数
     * @param int $logLevel 日志等级  NORMAL 1/ WARN 2/ ERROR 3
     * @param int $errorCode 错误码
     */
    public function systemLog(string $descriptionKey, $descriptionParam = array(), $logLevel = 1, $errorCode = 0)
    {
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $msg = array(
            'op_user_uuid' => xphp_get_user_info()['userUuid'],
            'op_user_name' => xphp_get_user_info()['userName'],
            'agent_uuid' => '',
            'agent_name' => '',
            'op_time' => date($dateformat),
            'description_key' => $descriptionKey,
            'description_param' => $this->groupSystemLogParams($descriptionParam),
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );

        $msg = json_encode($msg);

        $logType = xphp_get_config('socket')['BdLogRouterHeader']['system_log'];
        return $this->_socket->logMsg($msg, $logType);
    }

    /**
     * 写任务日志到数据库
     * @param array $params 任务日志参数
     * @param string $descriptionKey 日志KEY
     * @param array $descriptionParam 日志参数
     * @param int $logLevel 日志等级  NORMAL 1/ WARN 2/ ERROR 3
     * @param int $errorCode 错误码
     */
    public function taskLog(array $params, string $descriptionKey, $descriptionParam = array(), $logLevel = 1, $errorCode = 0)
    {
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $msg = array(
            'op_user_uuid' => xphp_get_user_info()['userUuid'],
            'op_user_name' => xphp_get_user_info()['userName'],
            'task_uuid' => $params['task_uuid'],
            'task_name' => $params['task_name'],
            'task_type' => $params['task_type'],
            'module_type' => $params['module_type'],
            'submodule_type' => $params['submodule_type'],
            'running_flag' => 2,
            'write_to_notice_flag' => 2,
            'op_time' => date($dateformat),
            'description_key' => $descriptionKey,
            'description_param' => $this->groupSystemLogParams($descriptionParam),
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );

        $msg = json_encode($msg);

        $logType = xphp_get_config('socket')['BdLogRouterHeader']['task_log'];
        return $this->_socket->logMsg($msg, $logType);
    }


    /**
     * 组合系统日志参数消息
     * @param array $descriptionParam array(string|array(type => module_type|backup_mode, value => ?))
     */
    private function groupSystemLogParams(array $descriptionParam)
    {
        $info = array();
        foreach ($descriptionParam as $des) {
            if (is_array($des)) {
                //如果是数组
                $info[] = $des["type"] . ":" . $des["value"];
            } else {
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
     * @param int $logLevel
     * @param int $errorCode
     * @return bool
     */
    public function loginLog(string $useruuid, string $username, string $descriptionKey, $descriptionParam = array(), $logLevel = 1, $errorCode = 0)
    {
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $msg = array(
            'op_user_uuid' => $useruuid,
            'op_user_name' => $username,
            'agent_uuid' => '',
            'agent_name' => '',
            'op_time' => date($dateformat),
            'description_key' => $descriptionKey,
            'description_param' => $descriptionParam,
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );
        $msg = json_encode($msg);

        $logType = xphp_get_config('socket')['BdLogRouterHeader']['system_log'];

        return $this->_socket->logMsg($msg, $logType);
    }


    /**
     * 专门用于后台进程写日志,其他地方使用systemLog
     * @param string $descriptionKey
     * @param array $descriptionParam
     * @param int $logLevel
     * @param int $errorCode
     * @return bool
     */
    public function daemonLog(string $descriptionKey, $descriptionParam = array(), $logLevel = 1, $errorCode = 0)
    {
        $sql = "select user_uuid, user_name from bd_user limit 0, 1";
        $data = $this->dbSelect($sql, array());
        $useruuid = $data[0]['user_uuid'];
        $username = $data[0]['user_name'];
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $msg = array(
            'op_user_uuid' => $useruuid,
            'op_user_name' => $username,
            'agent_uuid' => '',
            'agent_name' => '',
            'op_time' => date($dateformat),
            'description_key' => $descriptionKey,
            'description_param' => $this->groupSystemLogParams($descriptionParam),
            'log_level' => intval($logLevel),
            'error_code' => intval($errorCode),
        );
        $msg = json_encode($msg);

        $logType = xphp_get_config('socket')['BdLogRouterHeader']['system_log'];
        return $this->_socket->logMsg($msg, $logType);
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
     * @return string
     */
    public function groupMsg(string $msg, int $type)
    {
        $logMsg = '';
        $debugInfo = debug_backtrace();
        $logLevel = array_search($type, xphp_get_config('log')['LOG_INFO'], true);
        $i = 0;
        foreach ($debugInfo as $value) {
            if ("writeLog" == $value['function']) {
                break;
            }
            $i++;
        }
        if ($type == xphp_get_config('log')['LOG_INFO']['ERROR']) {
            $msg = v1_get_error_des($msg);
        }
        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';
        $logMsg .= date($dateformat) . " [" . $logLevel . "] ";
        $logMsg .= $msg . " : file: " . $debugInfo[$i]['file'] . " on line " . $debugInfo[$i]['line'] . ", ";
        $logMsg .= "class: " . $debugInfo[$i + 1]['class'] . ", function: " . $debugInfo[$i + 1]['function'] . "." . PHP_EOL;
        return $logMsg;
    }

    /**
     * 写日志,只用于WEB写文件,调试用
     * @param string $msg 日志消息,可以是自定义消息,|error日志可以是错误定义名字或错误号
     * @param int $type 消息类型           info/notice/warning/error       1/2/3/4
     */
    public function writeLog(string $msg, $type = 1)
    {
        $file = xphp_get_config('log')['LOG_INFO']['PATH'] . "_" . date("Y-m-d");
        if (!xphp_get_config('log')['LOG_INFO']['DEBUG']) {
            //如果不是调试,直接返回
            return true;
        }
        $msg = $this->groupMsg($msg, $type);
        $count = file_put_contents($file, $msg, FILE_APPEND | LOCK_EX);
        if ($count > 0) {
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
    protected function paramsCheck()
    {
        $nums = func_num_args();
        $args = func_get_args();
        $flag = false;
        for ($i = 0; $i < $nums; $i++) {
            $flag = $flag || empty($args[$i]);
        }
        if ($flag) {
            $this->writeLog("params empty check error.", 3);
            exit($this->muOpResult(false, xphp_get_lang('WEB_OPHANDLER_PARAMS_CHECK'), xphp_get_lang('WEB_OPHANDLER_PARAMS_NULL'), 'warning'));
        }
    }

    /**
     * 输入框参数检测
     * 特别用于检查输入框传值是否为空
     */
    protected function checkEmpty($str)
    {
        if (strlen(trim($str)) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 转化时间戳为2012-12-12 12:12:12格式
     * @param unknown $timestamp
     * @return string
     */
    protected function parseDate($timestamp)
    {
        if (empty($timestamp)) {
            return xphp_get_config('app')['TIMESPACE'];
        }

        $dateformat = xphp_get_config('special', 'dateformat') ?? 'Y-m-d H:i:s';

        return date($dateformat, $timestamp);
    }

    /**
     * 秒数转化成时:分：秒的形式
     * @param int $seconds
     * @return string
     */
    protected function secondsToHMS($seconds)
    {
        if (empty($seconds)) {
            return $seconds;
        }

        $timestamp = mktime(0, 0, $seconds);

        return date('H:i:s', $timestamp);;
    }

    /**
     * 辅助函数 =>end
     *************************************************************************************/
}