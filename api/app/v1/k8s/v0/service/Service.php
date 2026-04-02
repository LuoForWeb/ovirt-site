<?php

namespace app\v1\k8s\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;
use Mpdf\Tag\Em;

/**
 * note           容器 服务通信 service
 * @author       liushuai@vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{


  
    /**
     * 统一发送消息到后台  -- 任务相关
     * @param string $taskUuid 任务uuid
     * @param string $opName   操作码
     * @param array  $msg      消息
     * @param bool   $sync     是否同步
     * @param bool   $command  是否命令
     * @return array
     */
    public function opUnifyMsg(string $taskUuid, string $opName, array $msg, $sync = false, $command = false)
    {

        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid

        return $this->mbK8SMsg($nodeuuid, $opName, json_encode($msg), $sync, $command);
    }






    /**
     * 集群管理-添加集群-手动添加（服务端连接客户端模式）-确定
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     */
    public function addClusterManual(string $nodeuuid, array $params): array
    {
        $msg = json_encode([
            'host' => $params['host'] ?? '',
            'port' => $params['port'] ?? 0,
            'name' => $params['name'] ?? '',
        ]);
        $opName = 'KUBE_CLUSTER_OP_CODE_ADD_CLUSTER';
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, false);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }

    /**
     * 集群管理-添加集群-远程部署-确定
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function addClusterRemote(string $nodeuuid, array $params): array
    {
        //获取参数
        $msg = json_encode([
            'type' => $params['type'] ?? 'BY_CONFIG',
            'ssh_host' => $params['ssh_host'] ?? '',
            'ssh_port' => $params['ssh_port'] ?? 22,
            'ssh_user' => $params['ssh_user'] ?? '',
            'ssh_password' => $params['ssh_password'] ?? '',
            'kube_config' => $params['kube_config'] ?? '',
            'limit_cpu' => $params['limit_cpu'] ?? 0,
            'limit_memory' => $params['limit_memory'] ?? 0,
            'net_mode' => $params['net_mode'] ?? 1,
            'net_mode_host' => $params['net_mode_host'] ?? '',
            'net_mode_port' => $params['net_mode_port'] ?? 0,
        ]);
        //得到操作码
        $opName = 'KUBE_CLUSTER_OP_CODE_REMOTE_DEPLOYMENT';
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }

    /**
     * 集群管理-集群列表-勾选要删除的集群-删除
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function deleteCluster(string $nodeuuid, array $params): array
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_CLUSTER_OP_CODE_DELETE_CLUSTERS';
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }

    /**
     * 刷新集群
     * @param string $nodeuuid
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/7/12
     */
    public function refreshCluster(string $nodeuuid): array
    {
        $msg = json_encode([]);
        //得到操作码
        $opName = 'KUBE_CLUSTER_OP_CODE_REFRESH_ALL_CLUSTERS';
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }

    /**
     * 备份任务-按命名空间或者APP备份-展开集群-加载命名空间列表
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function getNameSpace(string $nodeuuid, array $params): array
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_NAMESPACES';
       
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '', // 如何result=true则message是数据JSON
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }



    /**
     * 获取资源分类
     */
    public function getResourceBigGroup(string $nodeuuid, array $params)
    {

      //获取参数
      $msg = json_encode($params);
      //得到操作码
      $opName = 'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCE_CATEGORIES';
      $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
      return [
        'opcode_name' => $opName,
        'result' => $mbResult['result'] ?? false,
        'message' => $mbResult['msg'] ?? '', // 如何result=true则message是数据JSON
        'error_code' => $mbResult['errorCode'] ?? 0,
      ];
    }




    /**
     * 备份任务-按应用备份-加载应用列表
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function getApp(string $nodeuuid, array $params): array
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_APPS';
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }

    /**
     * 备份任务-按命名空间或者APP备份-获取命名空间或者APP下展开后的资源大分类
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/4/18
     */
    public function getResourceCategory(string $nodeuuid, array $params): array
    {
        //获取参数
        //{
        //  "cluster_uuid": "00000000-0000-0000-0000-000000000000",
        //  "namespace": "vinchin",
        //  "app": "可选的APP",
        //  "app_type": 1
        //}
        $msg = json_encode([
            'cluster_uuid' => $params['cluster_uuid'] ?? '',
            'namespace' => $params['namespace'] ?? '',
            'app' => $params['app'] ?? '',
            'app_type' => $params['app_type'] ?? '',
        ]);
        //得到操作码
        $opName = 'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCE_CATEGORIES';

        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }


    /**
     * 获取资源Kind列表
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/4/18
     */
    public function getResourceGroup(string $nodeuuid, array $params): array
    {
       
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCES_KINDS';
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }

    /**
     * 获取资源小分组下资源列表
     * 备份任务-按应用/命名空间备份-树形列表-展开资源-展开子资源分类-获取子分类列表下更小的资源分类-获取小分类下的资源列表
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/4/18
     */
    public function getResource(string $nodeuuid, array $params): array
    {
        //获取数据
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_RESOURCES_OF_KIND';

        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }

    /**
     * 从客户端获取资源详情
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/4/18
     */
    public function getResourceDetails(string $nodeuuid, array $params): array
    {
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_BACKUP_OP_CODE_EXPLORER_CLUSTER_VIEW_RESOURCE';
        //想后台发消息
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }




    /**
     * 创建k8s备份
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/6/1
     */
    public function createBackupJob(string $nodeuuid, array $params): array
    {
        //得到操作码
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        if(!empty($params['task_uuid'])){
            $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        }
        $msg = json_encode($params);
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }


    /**
     * 获取应用下资源列表
     * 恢复-选中时间点-展开备份的命名空间或者应用-加载备份的资源（同展开集群级别资源）
     * 前端web界面直接从数据库加载备份的命名空间或者APP列表后，若用户展开了命名空间或者APP下具体的资源列表，则发送该请求
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/6/13
     */
    public function getRestoreTimepointResource(string $nodeuuid, array $params): array
    {

        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_RESTORE_OP_CODE_GET_BACKUP_TIMEPOINT_RESOURCES';
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }


    /**
     * 获取应用下资源详情
     * 恢复-选中时间点-展开备份的命名空间或者应用-加载备份的资源-预览资源Yaml文件
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/6/13
     */
    public function getRestoreTimepointResourcDetails(string $nodeuuid, array $params): array
    {
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_RESTORE_OP_CODE_VIEW_BACKUP_TIMEPOINT_RESOURCE_YAML';

        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }


    /**
     * 恢复-恢复目的地-加载目的集群的PVC和存储类列表
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/6/13
     */
    public function getRestorePvc(string $nodeuuid, array $params): array
    {
        //获取参数
        //{
        //  "cluster_uuid": "00000000-0000-0000-0000-000000000000"
        //}
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_RESTORE_OP_CODE_EXPLORER_CLUSTER_PVC_AND_STORAGE_CLASSES';
        //想后台发消息
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg, true);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }

    /**
     * 创建恢复任务
     * @param string $nodeuuid
     * @param array $params
     * @return array
     * @author liushuai@vinchin.com
     * $date 2023/6/13
     */
    public function createRecoveryJob(string $nodeuuid, array $params): array
    {
        $msg = json_encode($params);
        //得到操作码
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        //想后台发消息
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return [
            'opcode_name' => $opName,
            'result' => $mbResult['result'] ?? false,
            'message' => $mbResult['msg'] ?? '',
            'error_code' => $mbResult['errorCode'] ?? 0,
        ];
    }


  /**
   * 给时间点添加星标
   */
  public function addStar($params){
    $pointUUID = $params['uuid'];
    $this->paramsCheck($pointUUID);
    $msg = array($pointUUID);
    $opName = 'BD_BACKUP_POINT_OP_MAKR';
    $msg = json_encode(array('timepoint_uuids'=> $msg));
    // return $this->opUnifyMsg($opName, $msg);
    //给后台发消息
      //---测试结果----
    $mbResult = $this->mbPFMsg($opName, $msg);
    return $mbResult;
  }

    /**
   * 删除时间点星标
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



}
