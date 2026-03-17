<?php

namespace app\v1\k8s\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;

use app\v1\job\v0\logic\JobInfo;

use app\v1\common\logic\JobInfo as Jobinfos;

/**
 * note          容器 之任务信息 logic
 * @author       liushuai@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sJobInfo extends Base
{
    /**
     * 获取容器基本信息
     * @param $params
     * @return array
     */
    public function getk8sJobBasicInfo($params){
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_JOB_INFO_GET_TASK_BASIC_INFO_SUCCESS'),
            'data' => array(),
        );
        //获取任务uuid
        $task_uuid = $params['task_uuid'];
        //实例化任务
        $JobClass = new JobInfo();
        //获取基本信息
        $info = $JobClass->getJobInfo($task_uuid);
        $resultInfo['data'] = $info;
        //获取额外信息
        $sql = "select kt.by_type, kt.keep_snapshots, kt.snapshot_pvc, kt.upload_pvc_data, kt.ignore_exception, 
        kt.unset_affinity, kt.skip_exists, kt.hook_type, kt.local_snapshot_first, kt.ns_rename, 
        kc.cluster_name, kc.host, kc.hostname 
        from kube_task kt
        left join kube_cluster kc on kt.cluster_uuid = kc.cluster_uuid
        where kt.task_uuid = ?";
        $result_sql = $this->dbSelect($sql,array($task_uuid));
        if(empty($result_sql)){
            return $resultInfo;
        }
        $k8s_info = array();
        foreach ($result_sql as $each){
            //获取忽略快照异常
            $ignore_exception = json_decode($each['ignore_exception'], true);

            //获取额外信息
            $k8s_info = array(
                //获取备份类型
                "by_type" => $each['by_type'],
                //获取保留集群数
                "keep_snapshots" => $each['keep_snapshots'],
                //获取启用PVC快照
                "snapshot_pvc" => $each['snapshot_pvc'],
                //获取上传PVC快照
                "upload_pvc_data" => $each['upload_pvc_data'],
                //获取忽略异常
                "ignore_exception" => $ignore_exception['ignore_snapshot_exception'],
                //获取亲和性
                "unset_affinity" => json_decode($each['unset_affinity'],true),
                //获取跳过已存在方式
                "skip_exists" => $each['skip_exists'],
                //获取钩子类型
                "hook_type" => $each['hook_type'],
                //优先使用集群内快照
                "local_snapshot_first" => $each['local_snapshot_first'],
                //命名空间重定向
                "ns_rename" => json_decode($each['ns_rename'],true),
                //集群别名
                "cluster_name" => $each['cluster_name'],
                //集群地址
                "host" => $each['host'],
                //集群名称
                "hostname" => $each['hostname'],
            );
        }
        $info = array_merge($info,$k8s_info);
        //初始化返回数据
        $resultInfo['data'] = $info;
        return $resultInfo;
    }


    /**
     * 获取单个历史任务详情
     * @param $params
     * @return array
     */
    public function getk8sJobHistoryInfo($params){
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_JOB_INFO_GET_HISTORY_DETAILS_SUCCESS'),
            'data' => array(),
        );
        //获取任务uuid
        $task_uuid = $params['task_uuid'];
        //获取历史任务uuid
        $history_uuid = $params['history_uuid'];
        //获取details数据
        $sql = "select details, module_type,task_type from bd_history_task where history_uuid = ?";
        $result = $this->dbSelect($sql,array($history_uuid));
        if(empty($result)){
            return $resultInfo;
        }
        //获取json
        $details = json_decode($result[0]['details'],true);
        $details_list = $this->getDetailsData($details);
        $resultInfo['data'] = $details_list;
        return $resultInfo;
    }


    /**
     * 获取容器的details数据
     * @param $details
     * @return array|array[]
     */
    public function getDetailsData($details){
        $info = array(
            'pvc_list' => array(),
            'resource_list' => array(),
        );
        $details = $details['resources'];
        //开始循环处理details数据
        foreach ($details as $each){
            $meta = json_decode($each['meta'],true);
            //如果是PVC
            if($each['type'] == xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_PVC']){
                $info['pvc_list'][] = array(
                    'pvc_name' => $each['name'],
                    'pvc_namespace' => $each['namespace'],
                    'volume_mode' => $meta['pvc_volume_mode'] ?? "--",
                    'pvc_size' => $each['exec_pvc_size_total'],
                    'pvc_size_des' => v1_calsize($each['exec_pvc_size_total'],true),
                    'status_int' => $each['exec_status'],
                    'status' => xphp_get_desc('Kubernetes','EXEC_STATUS_DES')[$each['exec_status']], //状态
                    'status_desc' => $each['exec_status'] == 2 ? "" : "#".$each['exec_error_code'].":".$each['exec_error_message'],
                );
            }else{
                //这儿的name需要判断下
                $name = $each['name'];
                if($each['type'] == xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_APP'] ){
                    //如果是app则取app的名称
                    $name = $each['app'];
                }
                $info['resource_list'][] = array(
                    'resource_name' => $name,
                    'resource_namespace' => $each['namespace'] ?? "--",
                    'resource_kind' => $each['kind'] ?? "--",
                    'resource_version' => $meta['version'] ?? "--",
                    'resource_group' => $each['group'] ?? "--",
                    'resource_object_type' => xphp_get_desc('Kubernetes','KUBE_OBJECT_TYPE_DES')[$each['type']],
                    'status_int' => $each['exec_status'],
                    'status' => xphp_get_desc('Kubernetes','EXEC_STATUS_DES')[$each['exec_status']], //状态
                    'status_desc' => $each['exec_status'] == 2 ? "" : "#".$each['exec_error_code'].":".$each['exec_error_message'],
                );
            }

            

        }
        return $info;
    }


    /**
     * //获取任务的命名空间列表
     * @param $params
     */
    public function getTaskResource($params){
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_JOB_INFO_GET_RESOURCE_LIST'),
            'data' => array(
                'rows' => array(),
                'total' => 0,
            ),
        );
        //获取任务uuid
        $task_uuid = $params['jobs_uuid'];
        //一个数组
        $resource_type = $params['resource_type'];
        //将数组转换为字符串
        $resource_type = implode(",",$resource_type);
        if(empty($resource_type)){
            return $resultInfo;
        }

        $app = $params['app'];
        $app_type = $params['app_type'];
        $namespace = $params['namespace'];
        $start = $params['offset'] ?? 0;
        $length = $params['limit'] ?? 10;
        $sort = $params['sort'];
        $order = $params['order'];
        $sql = "select * from kube_object_list where task_uuid = ? and type in ($resource_type) ";
        $sql_count = "select count(*) as total from kube_object_list where task_uuid = ? and type in ($resource_type) ";
        $sql_params = array($task_uuid);
        $sql_count_params = array($task_uuid);
        if(!empty($app)){
            $sql .= " and app = ? ";
            $sql_count .= " and app = ? ";
            $sql_params = array_merge($sql_params,array($app));
            $sql_count_params = array_merge($sql_count_params,array($app));
        }
        if(!empty($app_type)){
            $sql .= " and app_type = ? ";
            $sql_count .= " and app_type = ? ";
            $sql_params = array_merge($sql_params,array($app_type));
            $sql_count_params = array_merge($sql_count_params,array($app_type));
        }
        if(!empty($namespace)){
            $sql .= " and namespace = ? ";
            $sql_count .= " and namespace = ? ";
            $sql_params = array_merge($sql_params,array($namespace));
            $sql_count_params = array_merge($sql_count_params,array($namespace));
        }
        $sql .= " limit $start,$length";

        $result = $this->dbSelect($sql,$sql_params);
        $result_count = $this->dbSelect($sql_count,$sql_count_params);
        if(empty($result)){
            return $resultInfo;
        }
       
        foreach ($result as $each){
            $meta = json_decode($each['meta'],true);
            $info['rows'][] = array(
                'id' => $each['id'],
                'task_uuid' => $task_uuid,
                //获取命名空间
                'name' => $each['name'],
                //获取命名空间
                'namespace' => $each['namespace'],
                //获取应用
                'app' => $each['app'],
                'app_type' => $each['app_type'],
                'group' => $each['group'],
                'version' => $each['version'],
                'kind' => $each['kind'],
                //获取当前对象执行状态
                'exec_status' => $each['exec_status'],
                //获取当前对象执行错误码
                'exec_error_code' => $each['exec_error_code'],
                //获取当前对象执行错误信息
                'exec_error_message' => $each['exec_error_message'],
                //获取当前对象资源总个数
                'exec_resources_count_total' => $each['exec_resources_count_total'],
                //当前选中对象下的资源当前处理个数
                'exec_resources_count_current' => $each['exec_resources_count_current'],
                //当前选中对象下的资源总存储容量大小
                'exec_resources_size_total' => $each['exec_resources_size_total'],
                'exec_resources_size_total_des' => v1_calsize($each['exec_resources_size_total'],true),
                //当前选中对象下的资源当前传输存储容量大小
                'exec_resources_size_current' => $each['exec_resources_size_current'],
                'exec_resources_size_current_des' => v1_calsize($each['exec_resources_size_current'],true),
                //当前选中对象下的PVC总个数
                'exec_pvc_count_total' => $each['exec_pvc_count_total'],
                //当前选中对象下的PVC当前处理个数
                'exec_pvc_count_current' => $each['exec_pvc_count_current'],
                //当前选中对象下的PVC总占用存储容量总大小
                'exec_pvc_size_total' => $each['exec_pvc_size_total'],
                'exec_pvc_size_total_des' => v1_calsize($each['exec_pvc_size_total'],true),
                //当前选中对象下的PVC当前传输存储容量大小
                'exec_pvc_size_current' => $each['exec_pvc_size_current'],
                'exec_pvc_size_current_des' => v1_calsize($each['exec_pvc_size_current'],true),
                'meta' => $meta,
                'volume_mode' => $meta['pvc_volume_mode'] ?? '',
                'type' => $each['type'],
            );
            
        }
        $info['total'] = $result_count[0]['total'];
        $resultInfo['data'] = $info;
        return $resultInfo;
    }



    //得到备份选择的数据(主要用于修改用)
    public function getTaskBackupInfo($params){
        $task_uuid = $params['jobs_uuid'];
        $JOBINFO = new Jobinfos();
        // $BACKUP = new Backup();
        //获取基本信息
        $info = $JOBINFO->getJobBackupInfo($task_uuid);
        //这里查询因为是一对多的情况,并且另一张表的条数可能更多,这里建议两张表分开查询,不链表查询效率更高
        //获取任务表
        $sql_task = "select by_type, cluster_uuid, keep_snapshots, ignore_exception, hook_type,hook_run_in_pods,
        hook_env, hook_script 
        from kube_task
        where task_uuid = ?";
        $result_task = $this->dbSelect($sql_task,array($task_uuid));
        //获取资源表
        $sql_resource = "select type, namespace, app, name, meta, exec_pvc_size_total 
        from kube_object_list 
        where task_uuid = ?";
        $result_resource = $this->dbSelect($sql_resource,array($task_uuid));
        //--------组装数据
        //初始化数据
        $info['other'] = array(
            'namespace_list' => array(),
            'app_list' => array(),
            'pvc_list' => array(),
        );
        //获取备份类型
        $info['other']['by_type'] = $result_task[0]['by_type'];
        //获取集群uuid
        $info['other']['cluster_uuid'] = $result_task[0]['cluster_uuid'];
        //获取本地集群保留个数
        $info['other']['keep_snapshots'] = $result_task[0]['keep_snapshots'];
        //获取快照异常
        $info['other']['ignore_exception'] = json_decode($result_task[0]['ignore_exception'], true)['ignore_snapshot_exception'];
        //获取脚本类型
        $info['other']['hook_type'] = $result_task[0]['hook_type'];
        //获取脚本环境
        $info['other']['hook_env'] = $result_task[0]['hook_env'];
        //执行环境　　  
        $info['other']['hook_run_in_pods'] = json_decode($result_task[0]['hook_run_in_pods'], true);
        //获取脚本内容
        $info['other']['hook_script'] = json_decode($result_task[0]['hook_script'], true);
        if(empty($result_resource)){
            return $info;
        }
        foreach ($result_resource as $each){
            $resource_type = intval($each['type']);
            switch ($resource_type){
                case  xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_NAMESPACE']://namespace
                    if(!in_array($each['namespace'],$info['other']['namespace_list'])){
                        $info['other']['namespace_list'][] = $each['namespace'];
                    }
                    break;
                case xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_APP']://app
                    //判断是否已经在列表中加过命名空间了,不重复添加
                    if(!in_array($each['namespace'],$info['other']['namespace_list'])){
                        $info['other']['namespace_list'][] = $each['namespace'];
                    }
                    $info['other']['app_list'][] = array(
                        'namespace' => $each['namespace'],
                        'app' => $each['app'],
                    );
                    break;
                case xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_PVC']://pvc
                    $meta = json_decode($each['meta'],true);
                    $info['other']['pvc_list'][] = array(
                        'pvc' => $each['name'],
                        'namespace' => $each['namespace'],
                        'storage_class' =>$meta['storage_class'],
                        'size' => $each['exec_pvc_size_total'],
                    );
                    break;
                case xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_CLUSTER']://resource
                    if($each['name'] == 'CLUSTER'){
                        $info['other']['resource_list'] = 'CLUSTER';
                    }
                    break;
                   
            }
        }
        return $info;
    }




    /**
     * 获取正在备份的资源
     */
    public function getCurrentObject($params){
        //获取task_uuid
        $task_uuid = $params['job_uuid'];
        $sql = "select current_object from bd_running_info where task_uuid = ?";
        $result = $this->dbSelect($sql,array($task_uuid));
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_JOB_INFO_GET_BACKUP_ING'),
            'data' => array(
                'current_object' => ($result[0]['current_object'] != null && $result[0]['current_object'] != "") ? $result[0]['current_object'] : '--',
            ),
        );
        return $resultInfo;

    }









}
