<?php

namespace app\v1\k8s\v0\service;

use app\v1\common\service\Base;

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
     * 手动添加集群
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function addClusterManual($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_ADD_CLUSTER';
        //想后台发消息
        //---测试结果----
        $info = array(
            'result' => true,
            'message' => $params,
            'error_code' => 0,
        );
        return $info;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 远程添加集群
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function addClusterRemote($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_REMOTE_DEPLOYMENT';
        //想后台发消息
        //---测试结果----
        $info = array(
            'result' => true,
            'message' => $params,
            'error_code' => 0,
        );
        return $info;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 删除集群
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function deleteCluster($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_DELETE_CLUSTERS';
        //想后台发消息
        //---测试结果----
        $info = array(
            'result' => true,
            'message' => $params,
            'error_code' => 0,
        );
        return $info;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 刷新集群
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/7/12
     */
    public function refreshCluster($nodeuuid)
    {
        $msg = json_encode(array());
        //得到操作码
        $opName = 'KUBE_CLUSTER_OP_CODE_REFRESH';
        //想后台发消息
        //---测试结果----
        $info = array(
            'result' => true,
            'message' => array(),
            'error_code' => 0,
        );
        return $info;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 按命名空间备份获取命名空间列表
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function getNameSpaceByNs($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_EXPLORER_CLUSTER_NAMESPACES_BY_NAMESPACE';
        //想后台发消息
        //---测试结果----
        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": {
    "namespaces": [
      {
        "name": "ns-pre-release",
        "pvcs": [
          {
            "name": "mysql-pvc-0",
            "namespace": "ns-pre-release",
            "size": "10Gi",
            "storage_class": "local-storage"
          },{
            "name": "mysql-pvc-1",
            "namespace": "ns-pre-release",
            "size": "10Gi",
            "storage_class": "local-storage"
          },{
            "name": "mysql-pvc-2",
            "namespace": "ns-pre-release",
            "size": "10Gi",
            "storage_class": "local-storage"
          }
        ]
      }
    ]
  }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 按应用备份获取命名空间列表
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function getNameSpaceByApp($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_EXPLORER_CLUSTER_NAMESPACES_BY_APP';
        //想后台发消息
        //---测试结果----
        $params_test = '{
                  "error_code": 0,
                  "result": true,
                  "message": {
                    "namespaces": [
                      {
                        "name": "ns-pre-release"
                      }
                    ]
                  }
                }';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 按应用备份获取应用
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/17
     */
    public function getApp($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_EXPLORER_CLUSTER_APPS';
        //想后台发消息
        //---测试结果----
        $params_test = '{
    "error_code":0,
    "result":true,
    "message":{
        "apps":[
            {
                "namespace":"ns-pre-release",
                "name":"app-pre-release1",
                "pvcs":[
                    {
                        "name":"mysql-pvc-01",
                        "namespace":"ns-pre-release",
                        "size":"10Gi",
                        "storage_class":"local-storage"
                    }
                ]
            },
            {
                "namespace":"ns-pre-release",
                "name":"app-pre-release2",
                "pvcs":[
                    {
                        "name":"mysql-pvc-02",
                        "namespace":"ns-pre-release",
                        "size":"10Gi",
                        "storage_class":"local-storage"
                    }
                ]
            },
            {
                "namespace":"ns-pre-release",
                "name":"app-pre-release3",
                "pvcs":[
                    {
                        "name":"mysql-pvc-03",
                        "namespace":"ns-pre-release",
                        "size":"10Gi",
                        "storage_class":"local-storage"
                    }
                ]
            }
        ]
    }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 按应用备份获取命名空间列表
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/18
     */
    public function getResourceGroup($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_EXPLORER_CLUSTER_RESOURCES_KINDS';
        //想后台发消息
        //---测试结果----
        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": {
    "kind": [
      {
        "name": "Deployment",
        "namespace": "ns-pre-release",
        "version": "v1",
        "group": "apps",
        "kind": "deployments"
      }
    ]
  }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----

        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 获取资源小分组下资源列表
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/18
     */
    public function getResource($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_EXPLORER_CLUSTER_RESOURCES_OF_KIND';
        //想后台发消息
        //---测试结果----
        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": {
    "resources": [
      {
        "name": "mysql-deployment-0",
        "namespace": "ns-pre-release",
        "group": "apps",
        "version": "v1",
        "kind": "deployments"
      }
    ]
  }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 从客户端获取资源详情
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/4/18
     */
    public function getResourceDetails($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_COMMON_OP_CODE_EXPLORER_CLUSTER_VIEW_RESOURCE_YAML';
        //想后台发消息
        //---测试结果----

        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": {
    "yaml": "house:family: { name: Doe, parents: [John, Jane], children: [Paul, Mark, Simone] }address: { number: 34, street: Main Street, city: Nowheretown, zipcode: 12345 }"
  }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }




    /**
     * 创建k8s备份
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/6/1
     */
    public function createBackupJob($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        //想后台发消息
        //---测试结果----

        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": "success",
  "message": {
    "op_id": "123"
  }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }


    /**
     * 获取应用下资源列表
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/6/13
     */
    public function getRestoreTimepointResource($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_RESTORE_OP_CODE_GET_BACKUP_TIMEPOINT_RESOURCES';
        //想后台发消息
        //---测试结果----

        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": {
    "resources": [
      {
        "name": "mysql-res-0",
        "namespace": "ns-pre-release",
        "group": "apps",
        "version": "v1",
        "kind_name": "Deployment",
        "kind": "deployments",
        "category": "WORKLOADS/SERVICES/CONF_STORAGE"
      },
      {
        "name": "mysql-res-1",
        "namespace": "ns-pre-release2",
        "group": "apps",
        "version": "v2",
        "kind_name": "Deployment",
        "kind": "deployments",
        "category": "WORKLOADS/SERVICES/CONF_STORAGE"
      },
     {
        "name": "mysql-res-2",
        "namespace": "ns-pre-release3",
        "group": "apps",
        "version": "v2",
        "kind_name": "Deployment1",
        "kind": "deployments1",
        "category": "WORKLOADS/SERVICES/CONF_STORAGE"
      }
    ]
  }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }


    /**
     * 获取应用下资源详情
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/6/13
     */
    public function getRestoreTimepointResourcDetails($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_RESTORE_OP_CODE_VIEW_BACKUP_TIMEPOINT_RESOURCE_YAML';
        //想后台发消息
        //---测试结果----

        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": {
    "yaml": "YAML文件文本"
  }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }


    /**
     * 获取pvc以及其存储类
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/6/13
     */
    public function getRestorePvc($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'KUBE_TASK_RESTORE_OP_CODE_EXPLORER_CLUSTER_PVC_AND_STORAGE_CLASSES';
        //想后台发消息
        //---测试结果----

        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": {
    "pvcs": [
      {
        "name": "mysql-pvc-0",
        "namespace": "ns-pre-release",
        "size": "10Gi",
        "storage_class": "local-storage-sc"
      },
     {
        "name": "mysql-pvc-1",
        "namespace": "ns-pre-release",
        "size": "10Gi",
        "storage_class": "local-storage-sc"
      }
    ],
    "storage_classes": [
      {
        "name": "csi-rbd-sc"
      },
     {
        "name": "local-storage-sc"
      }
    ]
  }
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
    }

    /**
     * 创建恢复任务
     * @param unknown $params
     * @author liushuai@vinchin.com
     * $date 2023/6/13
     */
    public function createRecoveryJob($nodeuuid, $params)
    {
        //获取参数
        $msg = json_encode($params);
        //得到操作码
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        //想后台发消息
        //---测试结果----

        $params_test = '{
  "error_code": 0,
  "result": true,
  "message": "success"
}';
        $mbResult = json_decode($params_test,true);
        return $mbResult;
        //---测试结果----
        $mbResult = $this->mbK8SMsg($nodeuuid, $opName, $msg);
        return $mbResult;
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
