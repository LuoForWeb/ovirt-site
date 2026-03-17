<?php

namespace app\v1\hadoop\v0\service;
use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note          hadoop service
 * @author       lilingyu@vinchin.com
 * @date         2023/10/16 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{

    /**
     * 添加hadoop集群
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/16
     */
    public function addCluster($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_ADD';
        // var_dump("msg",$msg);
        // return;
        // $mbResult = $this->mbNodeMsg($opName, $nodeuuid, $msg);
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg, false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 修改hadoop集群
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/16
     */
    public function editCluster($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_MODIFY';
        // $mbResult = $this->mbNodeMsg($opName,$nodeuuid,  $msg);
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg, false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 删除hadoop集群
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function deleteCluster($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_DELETE';
        // $mbResult = $this->mbNodeMsg($opName,$nodeuuid, $msg);
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg, false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 获取备份文件树
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function getBackupFileZtree($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        // $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_DIR_QUERY';
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg , true, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        // $mbResult = $this->mbNodeMsg($opName, $nodeuuid, $msg,true);
        return $mbResult;
    }
    
    /**
     * 创建备份任务
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function createBackupJob($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg, false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 获取备份文件列表
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function getFileZtree($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'BD_BACKUP_POINT_OP_SCAN';
        //---TEST END----
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg , true, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 创建搜索消息
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function createSearchJob($nodeuuid,  $params){
        
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'FS_OP_TYPE_SEARCH_THREAD_CREATE';
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg , true, true, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 停止搜索消息
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function stopSearchJob($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'FS_OP_TYPE_SEARCH_STOP';
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg , false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 获取搜索结果
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function getSearchInfo($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'FS_OP_TYPE_SEARCH_RESULT_GET';
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg , true, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }


    /**
     * 创建恢复任务
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function createRecoverJob($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg , false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

     /**
     * 给时间点添加星标
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function addStar($params){
        $pointUUID = $params['uuid'];
        $this->paramsCheck($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_MAKR';
        $msg = json_encode(array('timepoint_uuids'=> $msg));
        $mbResult = $this->mbPFMsg($opName, $msg);
        return $mbResult;
    }

    /**
     * 删除时间点星标
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function deleteStar($params){
        $pointUUID = $params['uuid'];
        $this->paramsCheck($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_UNMARK';
        $msg = json_encode(array('timepoint_uuids'=> $msg));
        $mbResult = $this->mbPFMsg($opName, $msg);
        return $mbResult;
    }


    /**
     * 删除时间点
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function deleteTimepoint($nodeuuid,$params){
        //得到操作码
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $params);
        return $mbResult;
    }


     /**
     * 统一发送消息到后台  -- 任务相关
     * @param string $taskUuid 任务uuid
     * @param string $opName   操作码
     * @param  array  $msg      消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令
     * @return void
     */
    public function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false)
    {
        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, json_encode($msg) , $sync, $command, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }


    /**
     * mbFSMsg
     * @param $nodeUuid 节点uuid
     * @param $opName   操作码
     * @param $msg      内容
     * @param bool $sync     异步
     * @return array
     */
    public function mbNodeMsgs($nodeUuid, $opName, $msg, $sync = false)
    {
        return $this->mbNodeMsg($nodeUuid, $opName, $msg, $sync);
    }



    /**
     * 修改备份任务
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function editBackupJob($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg , false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 刷新hadoop集群
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function refreshCluster($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_REFRESH';
        // $mbResult = $this->mbNodeMsg($opName,$nodeuuid, $msg);
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg, false, false, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        return $mbResult;
    }

    /**
     * 获取恢复文件树
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function getRecoveryFileZtree($nodeuuid,  $params){
        //获取参数
        $msg =  json_encode($params);
        //得到操作码
        $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_RECOVERY_DIR_QUERY';
        $mbResult = $this->mbHADOOPMsg($nodeuuid, $opName, $msg , true, true, xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']);
        // $mbResult = $this->mbNodeMsg($opName, $nodeuuid, $msg,true);
        return $mbResult;
    }

    /**
     * 集群授权
     * @param unknown $params
     * @author lilingyu@vinchin.com
     * $date 2023/10/18
     */
    public function authCluster($params){
        $opName = 'PT_LICENSE_OP_MODIFY_HADOOP_CLUSTER_LICENSE';
        $msg = json_encode($params);
        $mbResult = $this->mbPFMsg($opName, $msg);
        return $mbResult;
    }

    /**
     * 统一发送平台消息到后台
     * @param string $opName  操作码
     * @param string $msg     消息json
     * @param string $operate 操作描述
     * @return array
     */
    public function opUnifyPfMsg(string $opName, string $msg, string $operate)
    {
        $mbResult = $this->mbPFMsg($opName, $msg);
        return $mbResult;
    }
    
}