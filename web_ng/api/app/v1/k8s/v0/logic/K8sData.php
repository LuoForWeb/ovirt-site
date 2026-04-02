<?php

namespace app\v1\k8s\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Storage;
use app\v1\common\logic\JobInfo;
use app\v1\common\logic\JobInfo as JobInfos;
use app\v1\backupData\v0\logic\DataManage;

/**
 * note          容器 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sData extends Base
{
    /**
     * 获取备份数据或者恢复的树形结构
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getRestoreData($params)
    {
        //获取存储uuid
        $storage_uuid = $params['storage_uuid'];
        //获取来源
        //获取是否是恢复的树形结构 true为恢复的树形结构
        $recoverflag = boolval($params['recoverflag']);
        //获取是否是备份数据的树形结构 true为备份数据的树形结构
        $dataflag = boolval($params['dataflag']);
//        //搜索
//        $search = $params['search'];
//        //获取搜索条件, 是否开启搜索条件,true为开启搜索条件,false为关闭搜索条件
//        $search_flag = boolval($search['search_flag']);
//        //如果开启搜索条件则获取搜索值
//        if($search_flag){
//            $search_keyword = $search['search_keyword'];
//            $search_forever_flag = $search['search_forever_flag'];
//        }
        //获取所有时间点
        $sql = "select bbt.timepoint_uuid, bbt.timepoint, bbt.depend_point_uuid, bbt.storage_uuid, bbt.task_uuid, bbt.task_name, bbt.importance_flag, 
        kbt.cluster_uuid, kbt.cluster_name,kbt.by_type,bbt.deleted_flag  
        from bd_backup_timepoint bbt 
        left join kube_backup_timepoint kbt 
        on bbt.timepoint_uuid = kbt.timepoint_uuid 
        left join bd_task bt 
        on bbt.task_uuid = bt.task_uuid 
        left join kube_task kt 
        on bbt.task_uuid = kt.task_uuid 
        where bbt.available_flag = 1 and bbt.import_flag = 2 and bbt.module_type = ?";
        $sql_params = array(xphp_get_config('module','MODULE_TYPE')['KUBERNETES']);
        if(!empty($storage_uuid)){
            $sql .=" and bbt.storage_uuid = ?";
            $sql_params = array_merge($sql_params,array($storage_uuid));
        }

        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的资源数据
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源数据
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['k8s']
            );
            $sql .= " and bbt.user_uuid in ({$userUuidSql}) ";
        }
        $sql .=" order by bbt.timepoint desc";

        //获取数据
        $sql_result = $this->dbSelect($sql,$sql_params);

        //初始化返回数据
        $mbResult = array(
            'error_code' => 0,
            'result' => true,
            'message' => array(),
        );
        if(empty($sql_result)){
            return $mbResult;
        }
        //判断任务层是否有勾选框
        $noCheckflag = true;
        if(!$recoverflag && $dataflag){
            //如果是备份数据则有勾选框
            $noCheckflag = false;
        }

        //开始获取第一层第二层数据
        $task_uuid_list = array();
        $cluster_uuid_list = array();
        $info = array();
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        foreach ($sql_result as $each){
            $task_uuid = $each['task_uuid'];
            $cluster_uuid = $each['cluster_uuid'];
            $taskName = $each['task_name'];
           
            //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
            $taskAvailable = in_array($task_uuid, $currentTaskUUID);
            $name = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
            $each['task_name_str'] = $name;
            //第一层集群层
            if(!in_array($cluster_uuid,$cluster_uuid_list)){
                //如果task_uuid不存在与列表中,则表示还未加载这个节点
                $cluster_uuid_list[] = $cluster_uuid;
                $info[] = $this->getZtreeTimepointOne($params,$each);
            }
            //第二层任务层
            if(!in_array($task_uuid,$task_uuid_list)){
                //如果task_uuid不存在与列表中,则表示还未加载这个节点
                $task_uuid_list[] = $task_uuid;
                $info[] = $this->getZtreeTimepointTwo($params,$each);
            }
        }
        $mbResult['message'] = $info;
        return $mbResult;
    }









    /**
     * 获取时间点
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getRestoreTimepoint($params,$info=array())
    {
        //获取任务uuid
        $task_uuid = $params['task_uuid'];
        //获取存储uuid
        $storage_uuid = $params['storage_uuid'];
        //获取集群uuid
        $cluster_uuid = $params['cluster_uuid'];
        //获取来源
        //获取是否是恢复的树形结构 true为恢复的树形结构
        $recoverflag = $params['recoverflag'];
        //获取是否是备份数据的树形结构 true为备份数据的树形结构
        $dataflag = $params['dataflag'];
        //搜索
        $search = $params['search'];
        //获取搜索条件, 是否开启搜索条件,true为开启搜索条件,false为关闭搜索条件
        $search_flag = $search['search_flag'];
        //如果开启搜索条件则获取搜索值
        if($search_flag){
            $search_keyword = $search['search_keyword'];
            $search_forever_flag = $search['search_forever_flag'];
        }
        $sql = "select bbt.timepoint_uuid, bbt.timepoint, bbt.depend_point_uuid, bbt.storage_uuid, bbt.task_uuid, bbt.task_name,
                bbt.backup_mode,bbt.real_node_uuid, bbt.detail, bbt.integrity_check_flag,  
                bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status,
                kbt.cluster_uuid, kbt.cluster_name,kbt.meta, kbt.by_type 
                from bd_backup_timepoint bbt 
                left join kube_backup_timepoint kbt 
                on bbt.timepoint_uuid = kbt.timepoint_uuid 
                left join bd_task bt 
                on bbt.task_uuid = bt.task_uuid 
                left join kube_task kt 
                on bbt.task_uuid = kt.task_uuid 
                left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
                where bbt.task_uuid = ? and bbt.available_flag = 1 ";
        $sql_params = array($task_uuid);
        //如果开启了搜索条件
        if($search_flag){
            if(!empty($search_keyword)){
                $sql .=" and bbt.timepoint like '%".$search_keyword."%' ";
            }
            if(!empty($search_forever_flag)){
                $sql .=" and bbt.importance_flag = ?";
                $sql_params = array_merge($sql_params,array(v1_parse_bool_to_flag($search_forever_flag)));
            }
        }
        $sql .= " order by bbt.timepoint asc";
        $sql_result = $this->dbSelect($sql,$sql_params);
        //初始化返回数据
        $mbResult = array(
            'error_code' => 0,
            'result' => true,
            'message' => array("null"),
        );
        if(empty($sql_result)){
            //如果查询出来为空值则直接返回
            return $mbResult;
        }

         //筛选出每个增备点和差异点对应PID
         $fulluuidList = $this->findDepointTimepoint($sql_result);
        
        foreach ($sql_result as $each){
            $backup_mode = $each['backup_mode'];
            //完备点
            if($backup_mode == xphp_get_config('task')['BACKUP_MODE']['FULL']){
                $info[] = $this->getZtreeTimepointThree($params,$each);
            }

            //增量点
            if($backup_mode == xphp_get_config('task')['BACKUP_MODE']['INCREMENTAL']){
                $info[] = $this->getZtreeTimepointFour($params,$each,$fulluuidList);
            }
        }
        $mbResult['message'] = $info;
        return $mbResult;
    }

    /**
     * 获取完整的树结构依赖关系
     */
    public function findDepointTimepoint($sql_result){
         //筛选出每个增备点和差异点对应PID
         $fulluuidList = array();
         $unfullList = array();
         foreach ($sql_result as $point) {
             if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                 $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
             } else {
                 $unfullList[] = $point;
             }
         }
        
         while (!empty($unfullList)) {
             $unfullCount = count($unfullList);
             $unfullCountTmp = count($unfullList);
             foreach ($unfullList as $key1 => $unfull) {
                 $dependId = $unfull['depend_point_uuid'];
                 foreach ($fulluuidList as $key => $value) {
                     if ($dependId == $key) {
                         $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$key];
                         unset($unfullList[$key1]);
                         $unfullCountTmp--;
                     }
                     continue;
                 }
             }
 
             if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0) {
                 break;
             }
         }
        return $fulluuidList;
    }


    //获取完整树结构搜索使用
    // public function getRestoreZtree($params){
    //     //获取任务uuid
    //     $task_uuid = $params['task_uuid'];
    //     //获取存储uuid
    //     $storage_uuid = $params['storage_uuid'];
    //     //获取集群uuid
    //     $cluster_uuid = $params['cluster_uuid'];
    //     //获取来源
    //     //获取是否是恢复的树形结构 true为恢复的树形结构
    //     $recoverflag = $params['recoverflag'];
    //     //获取是否是备份数据的树形结构 true为备份数据的树形结构
    //     $dataflag = $params['dataflag'];
    //     //搜索
    //     $search = $params['search'];
    //     //获取搜索条件, 是否开启搜索条件,true为开启搜索条件,false为关闭搜索条件
    //     $search_flag = $params['search_flag'];
    //     //如果开启搜索条件则获取搜索值
    //     if($search_flag){
    //         $search_keyword = $search['search_keyword'];
    //         $search_forever_flag = $search['search_forever_flag'];
    //     }
    //     $sql = "select bbt.timepoint_uuid, bbt.timepoint, bbt.depend_point_uuid, bbt.storage_uuid, bbt.task_uuid, bbt.task_name,
    //             bbt.backup_mode,bbt.detail,
    //             kbt.cluster_uuid, kbt.cluster_name,kbt.meta, kbt.by_type  
    //             from bd_backup_timepoint bbt 
    //             left join kube_backup_timepoint kbt 
    //             on bbt.timepoint_uuid = kbt.timepoint_uuid 
    //             left join bd_task bt 
    //             on bbt.task_uuid = bt.task_uuid 
    //             left join kube_task kt 
    //             on bbt.task_uuid = kt.task_uuid 
    //             where bbt.user_uuid = ?";
    //     $sql_params = array(xphp_get_user_info()['userUuid']);

    //     //        //如果开启了搜索条件
    //     if($search_flag){
    //         if(!empty($search_keyword)){
    //             $sql .= " and bbt.task_name like '%".$search_keyword."%' ";
    //             $sql .= " or bbt.timepoint like '%".$search_keyword."%' ";
    //         }
    //         if(!empty($search_forever_flag)){
    //             $sql .= " and bbt.importance_flag = ?";
    //             $sql_params = array_merge($sql_params,array(v1_parse_bool_to_flag($search_forever_flag)));
    //         }
    //     }
    //     //获取数据
    //     $sql_result = $this->dbSelect($sql,$sql_params);
    //     //初始化返回数据
    //     $mbResult = array(
    //         'error_code' => 0,
    //         'result' => true,
    //         'message' => array(),
    //     );
    //     if(empty($sql_result)){
    //         return $mbResult;
    //     }

    //     $task_uuid_list = array();
    //     $cluster_uuid_list = array();
    //     $info = array();


    //      //筛选出每个增备点和差异点对应PID
    //      $fulluuidList = $this->findDepointTimepoint($sql_result);
    //     //得到当前任务所有uuid
    //     $currentTaskUUID = $this->getCurrentAllTaskUUID();
    //     foreach ($sql_result as $each){
    //         //-----------组合第1层-------集群
    //         $task_uuid = $each['task_uuid'];
    //         $cluster_uuid = $each['cluster_uuid'];
    //         if(!in_array($cluster_uuid,$cluster_uuid_list)){
    //             //如果task_uuid不存在与列表中,则表示还未加载这个节点
    //             $cluster_uuid_list[] = $cluster_uuid;
    //             $info[] = $this->getZtreeTimepointOne($params,$each);
    //         }
    //         //-----------组合第2层-------任务
    //         if(!in_array($task_uuid,$task_uuid_list)){
    //             //如果task_uuid不存在与列表中,则表示还未加载这个节点
    //             $task_uuid_list[] = $task_uuid;
    //             $taskName = $each['task_name'];
    //             //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
    //             $taskAvailable = in_array($task_uuid, $currentTaskUUID);
    //             $name = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
    //             $each['task_name_str'] = $name;
    //             $info[] = $this->getZtreeTimepointTwo($params,$each);
    //         }
    //         //-----------组合第3层-------完备
    //         $backup_mode = $each['backup_mode'];
    //         if($backup_mode == xphp_get_config('task')['BACKUP_MODE']['FULL']){
    //             $info[] = $this->getZtreeTimepointThree($params,$each);
    //         }
    //         //-----------组合第4层-------差异
    //         if($backup_mode == xphp_get_config('task')['BACKUP_MODE']['INCREMENTAL']){
    //             $info[] = $this->getZtreeTimepointFour($params,$each,$fulluuidList);
    //         }
    //     }
    //     //添加完备或增备点
    //     $info = $this->filterTimepoint($info);
    //     //去重
    //     $info = $this->multiArrayUniqueTimepoint($info);
    //     $mbResult['message'] = $info;
    //     return $mbResult;
    // }


    // //多元数组去重
    // public function multiArrayUniqueTimepoint($info){
    //     $uniqueArray = array();

    //     foreach ($info as $item) {
    //         $id = $item['id'];

    //         // 判断是否已经存在相同id的元素，如果不存在则添加到结果数组中
    //         if (!isset($uniqueArray[$id])) {
    //             $uniqueArray[$id] = $item;
    //         }
    //     }

    //     // 将结果数组中的值重新索引，并返回去重后的数组
    //     return array_values($uniqueArray);
    // }


    // //再过滤一遍
    // public function filterTimepoint($info){
    //     //循环处理所有节点
    //     foreach ($info as $key=>$each){
    //         //如果是完备点,还要再搜索一遍完备点是否有增备点并加入到里面去
    //         if($each['backup_mode'] == xphp_get_config('task')['BACKUP_MODE']['FULL']){
    //             $sql = "select bbt.timepoint_uuid, bbt.timepoint, bbt.depend_point_uuid, bbt.storage_uuid, bbt.task_uuid, bbt.task_name,
    //             bbt.backup_mode,
    //             kbt.cluster_uuid, kbt.cluster_name,kbt.meta,  
    //             kbt.by_type 
    //             from bd_backup_timepoint bbt 
    //             left join kube_backup_timepoint kbt 
    //             on bbt.timepoint_uuid = kbt.timepoint_uuid 
    //             left join bd_task bt 
    //             on bbt.task_uuid = bt.task_uuid 
    //             left join kube_task kt 
    //             on bbt.task_uuid = kt.task_uuid 
    //             where bbt.user_uuid = ? and bbt.depend_point_uuid = ?";
    //             $sql_params = array(xphp_get_user_info()['userUuid'],$each['timepoint_uuid']);
    //             //获取数据
    //             $sql_result = $this->dbSelect($sql,$sql_params);
    //             if(!empty($sql_result)){
    //                 //如果查询出来有数据则再往info里面加数据
    //                 $params = array(
    //                     "task_uuid" => $each['task_uuid'],
    //                     "cluster_uuid" => $each['cluster_uuid'],
    //                 );
    //                 foreach ($sql_result as $one){
    //                     $info[] = $this->getZtreeTimepointFour($params,$one);
    //                 }
    //             }
    //         }

    //         //如果是增备点,则要查找增备点的父级完备点,加入进去
    //         if($each['backup_mode'] == xphp_get_config('task')['BACKUP_MODE']['INCREMENTAL']){
    //             $sql = "select bbt.timepoint_uuid, bbt.timepoint, bbt.depend_point_uuid, bbt.storage_uuid, bbt.task_uuid, bbt.task_name,
    //             bbt.backup_mode,
    //             kbt.cluster_uuid, kbt.cluster_name,kbt.meta,  
    //             kbt.by_type 
    //             from bd_backup_timepoint bbt 
    //             left join kube_backup_timepoint kbt 
    //             on bbt.timepoint_uuid = kbt.timepoint_uuid 
    //             left join bd_task bt 
    //             on bbt.task_uuid = bt.task_uuid 
    //             left join kube_task kt 
    //             on bbt.task_uuid = kt.task_uuid 
    //             where bbt.user_uuid = ? and bbt.timepoint_uuid = ?";
    //             $sql_params = array(xphp_get_user_info()['userUuid'],$each['depend_point_uuid']);
    //             //获取数据
    //             $sql_result = $this->dbSelect($sql,$sql_params);
    //             if(!empty($sql_result)){
    //                 //如果查询出来有数据则再往info里面加数据
    //                 $params = array(
    //                     "task_uuid" => $each['task_uuid'],
    //                     "cluster_uuid" => $each['cluster_uuid'],
    //                     "search_flag" => true,
    //                 );
    //                 $info[] = $this->getZtreeTimepointThree($params,$sql_result[0]);
    //             }
    //         }




    //     }
    // return $info;
    // }






    //组装ztree第一层 集群层
    public function getZtreeTimepointOne($params,$each){
        $one = array(
            "id" => $each['cluster_uuid'],
            "pId" => 0,
            "name" => $each['cluster_name'],
            "open" => true,
            "nocheck" => true, //不显示勾选按钮
            "type" => 0,
            "iconSkin" => 'ztree_cluster',
            "title" => $each['cluster_name'],
            "isParent" => true,
            "cluster_uuid" => $each['cluster_uuid'],
            "cluster_name" => $each['cluster_name'],
        );
        return $one;
    }

    //组装ztree第二层 任务层
    public function getZtreeTimepointTwo($params,$each){
        $task_uuid = $each['task_uuid'];
        $cluster_uuid = $each['cluster_uuid'];
        //获取是否是恢复的树形结构 true为恢复的树形结构
        $recoverflag = boolval($params['recoverflag']);
        //获取是否是备份数据的树形结构 true为备份数据的树形结构
        $dataflag = boolval($params['dataflag']);

        //判断是否展开
        $openflag = false;
        //是否是搜索
        if(!empty($params['search_flag'])){
            $openflag = true;
        }

        //判断任务层是否有勾选框
        $noCheckflag = true;
        if(!$recoverflag && $dataflag){
            //如果是备份数据则有勾选框
            $noCheckflag = false;
        }

        //根据任务类型来显示哪种图标
        if($each['by_type'] == 1){
            //按应用备份
            $inconSkin = "ztree_app";

        }else{
            //按命名空间备份
            $inconSkin = "ztree_namespace";
        }
        

        $two = array(
            "id" => $each['task_uuid'],
            "pId" => $each['cluster_uuid'],
            "name" => $each['task_name_str'],
            "open" => $openflag,
            "nocheck" => $noCheckflag, //不显示勾选按钮
            "type" => 1,
            "iconSkin" => $inconSkin,
            "title" => $each['cluster_name'],
            "isParent" => true,
            "checked" => false,
            //---
            "task_uuid" => $task_uuid,
            "by_type" => $each['by_type'],
            "cluster_uuid" => $each['cluster_uuid'],
            "cluster_name" => $each['cluster_name'],
        );
        return $two;
    }

    //组装ztree第三层 完备点
    public function getZtreeTimepointThree($params,$each){
        $pointMixedStatus = $this->getTimePointStatus($each['merge_status'], $each['operation_status'], $each['virus_scan_status'], $each['integrity_check_status']);

        //判断是否有加密 ,如果有手动输入密码则显示小锁标识
        $name = $each['timepoint'];
        $details = json_decode($each['detail'], true);
        $encryptedflag = false;
        if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
            $name .= '<i class="viconfont vicon-a-Unlockjiesuo-011"></i>';
            $encryptedflag = true;
        } else {
            $encryptedflag = false;
        }

        //获取任务uuid
        $task_uuid = $params['task_uuid'];
        //获取集群uuid
        $cluster_uuid = $params['cluster_uuid'];
        //判断是否展开
        $openflag = false;
        //是否是搜索
        if(!empty($params['search_flag'])){
            $openflag = true;
        }
        $three = array(
            "id" => $each['timepoint_uuid'],
            "pId" => $each['task_uuid'],
            "name" => $name,
            'encrypted_flag' => $encryptedflag,
//                    "open" => true,
            "nocheck" => false, //不显示勾选按钮
            "open" => $openflag,
            "type" => 2,
            "iconSkin" => 'ztree_timepoint_full',
            "title" => $each['timepoint'],
            "isParent" => false,
            "checked" => false,
            "by_type" => $each['by_type'],
            "timepoint_uuid" => $each['timepoint_uuid'],
            "resource" => $this->getPVCAndNamespace($each['meta']),
            "task_uuid" => $task_uuid,
            "cluster_uuid" => $each['cluster_uuid'],
            "cluster_name" => $each['cluster_name'],
            "backup_mode" => $each['backup_mode'],
            "oldname" => $each['timepoint'],
            "real_node_uuid" => $each['real_node_uuid'],
            //备份是否开启完整性效验
            'integrity_check_flag' => $each['integrity_check_flag'],
            "point_status" => $pointMixedStatus['status'],
            "available_flag" => $pointMixedStatus['available_flag'],
        );
        return $three;
    }

    //组装ztree第四层 增量点
    public function getZtreeTimepointFour($params,$each,$fulluuidList){
        $pointMixedStatus = $this->getTimePointStatus($each['merge_status'], $each['operation_status'], $each['virus_scan_status'], $each['integrity_check_status']);
        //获取任务uuid
        $task_uuid = $params['task_uuid'];
        //获取集群uuid
        $cluster_uuid = $params['cluster_uuid'];
        //判断是否有加密 ,如果有手动输入密码则显示小锁标识
        $name = $each['timepoint'];
        $details = json_decode($each['detail'], true);
        $encryptedflag = false;
        if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
            $name .= '<i class="viconfont vicon-a-Unlockjiesuo-011"></i>';
            $encryptedflag = true;
        } else {
            $encryptedflag = false;
        }
        $four = array(
            "id" => $each['timepoint_uuid'],
            "pId" => $fulluuidList[$each['timepoint_uuid']],
            "name" => $name,
            'encrypted_flag' => $encryptedflag,
//                    "open" => true,
            "nocheck" => false, //不显示勾选按钮
            "type" => 3,
            "iconSkin" => 'ztree_timepoint_incr',
            "title" => $each['timepoint'],
            "isParent" => false,
            "checked" => false,
            "by_type" => $each['by_type'],
            "timepoint_uuid" => $each['timepoint_uuid'],
            "depend_point_uuid" => $each['depend_point_uuid'],
            "resource" => $this->getPVCAndNamespace($each['meta']),
            "task_uuid" => $task_uuid,
            "cluster_uuid" => $each['cluster_uuid'],
            "cluster_name" => $each['cluster_name'],
            "backup_mode" => $each['backup_mode'],
            "oldname" => $each['timepoint'],
            "real_node_uuid" => $each['real_node_uuid'],
            //备份是否开启完整性效验
            'integrity_check_flag' => $each['integrity_check_flag'],
            "point_status" => $pointMixedStatus['status'],
            "available_flag" => $pointMixedStatus['available_flag'],
        );
        return $four;
    }







    /**
     * 恢复时获取单个时间点相关数据
     * @params details 为一个json
     * @author liushuai@vinchin.com
     * @date 2023/6/9
     * @return unknown
     * 输入为([app] => 1
    *[namespace] => 2
    *[pvcs] => Array
    *(
    *[0] => 3
    *[1] => 5
    *)
    *)
    
     *
     */
    public function getPVCAndNamespace($details){
        $info = array(
            'app' => array(), //app资源
            'namespace' => array(), //命名空间资源
            'resource' => array(), //集群资源
            'pvcs' => array(), //pvc资源
            'cluster' => array(),

        );
        if(empty($details)){
            return $info;
        }
        //解析json
        $details = json_decode($details,true);
        //得到备份的资源数据
        $resources = $details['resources'];
        foreach ($resources as $each){
            //得到资源类型 资源类型一共有4种
            if($each['exec_status'] != 2){
                continue;
            }
            $type = intval($each['type']);
            $eachInfo = array();
            $eachInfo['app'] = $each['app'];
            $eachInfo['app_type'] = $each['app_type'];
            $eachInfo['group'] = $each['group'];
            $eachInfo['kind'] = $each['kind'];
            $eachInfo['name'] = $each['name'];
            $eachInfo['namespace'] = $each['namespace'];
            $eachInfo['type'] = $each['type'];
            $eachInfo['version'] = $each['version'];
            $eachInfo['exec_status'] = $each['exec_status'];
            //获取meta数据
            $meta = json_decode($each['meta'],true);
            //获取pvc_storage_class数据
            $pvc_storage_class = $meta['pvc_storage_class'] ?? "";

            //获取
            switch($type){
                case xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_NAMESPACE']:
                    $eachInfo['id'] = $each['namespace'];
                    $info['namespace'][] = $eachInfo;
                    break;
                case xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_APP']:
                    $eachInfo['id'] = $each['namespace']."_".$each['app']."_".$each['app_type'];
                    $info['app'][] = $eachInfo;
                    break;
                case xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_RESOURCE']:
                    $eachInfo['id'] = $each['namespace']."_".$each['app']."_".$each['app_type'];
                    $info['resource'][] = $eachInfo;
                    break;
                case xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_PVC']:
                    $eachInfo['id'] = $each['namespace']."_".$each['name'];
                    $eachInfo['app_list'] = $each['app_list'];
                    $eachInfo['size'] = v1_calsize($each['exec_pvc_size_total'], true);
                    $eachInfo['pvc_storage_class'] = $pvc_storage_class;
                    $info['pvcs'][] = $eachInfo;
                    break;
                case xphp_get_config('kubernetes','KUBE_OBJECT_TYPE')['KUBE_OBJECT_TYPE_CLUSTER']:
                    $eachInfo['id'] = $each['name'];
                    $info['cluster'][] = $eachInfo;
                    break;
                default:
                    break;
            }
           
        }

        return $info;


    }




    /**
     * 获取备份数据的表格数据
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getRestoreTimepointTable($params)
    {
        //获取任务uuid
        $task_uuid = $params['task_uuid'];
        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //获取开始时间和结束时间
        $startTime = $params['start_time'];
        $endTime = $params['end_time']; 
        $forever = $params['forever'];
        $timepointType = $params['timepointType']; //时间点类型

        $sql = "select bbt.id, bbt.timepoint_uuid, bbt.timepoint, bbt.depend_point_uuid, bbt.storage_uuid, bbt.task_uuid, bbt.task_name,
                bbt.backup_mode,bbt.detail, 
                bbt.total_size, bbt.write_size, bbt.remarks, bbt.importance_flag,
                kbt.cluster_uuid, kbt.cluster_name 
                from bd_backup_timepoint bbt 
                left join kube_backup_timepoint kbt 
                on bbt.timepoint_uuid = kbt.timepoint_uuid where bbt.user_uuid = ? and bbt.task_uuid = ?";
        $sql_count = "select count(bbt.id) as count from bd_backup_timepoint bbt where bbt.user_uuid = ? and bbt.task_uuid = ?";
        $sql_params = array(xphp_get_user_info()['userUuid'],$task_uuid);
        $sqlcount_params = array(xphp_get_user_info()['userUuid'],$task_uuid);
        //如果有搜索条件 那么搜索完找到count再来做分页 否则 直接做分页
        //开始时间和结束时间查询
        if(!empty($startTime) && !empty($endTime)){
            $sql .= " and bbt.timepoint between ? and ? ";
            $sql_count .= " and bbt.timepoint between ? and ? ";
            $sql_params = array_merge($sql_params, array($startTime,$endTime));
            $sqlcount_params = array_merge($sqlcount_params,  array($startTime,$endTime));
        }
        //添加类型
        if(!empty($timepointType)){
            $sql .= " and bbt.backup_mode = ? ";
            $sql_count .= " and bbt.backup_mode = ? ";
            $sql_params = array_merge($sql_params, array($timepointType));
            $sqlcount_params = array_merge($sqlcount_params, array($timepointType));
        }
        //永久标记点
        if(!empty($forever)){
            $foreverflag = v1_parse_bool_to_flag($forever);
            $sql .= " and bbt.importance_flag = ? ";
            $sql_count .= " and bbt.importance_flag = ? ";
            $sql_params = array_merge($sql_params, array($foreverflag));
            $sqlcount_params = array_merge($sqlcount_params, array($foreverflag));
        }


        $count = $this->dbSelect($sql_count,$sqlcount_params);
        
        //如果高级搜索不为空的话 且搜索出来的总条数小于等于当前请求的offset 从第一页显示 否则显示当前页
        
        //搜索条件为空的话  直接limit
        
        if(empty($startTime) && empty($endTime) && empty($timepointType) && empty($forever)){
            if(is_numeric($offset) && !empty($limit)){
                $sql .= " limit ?, ?"; 
                $sql_params =  array_merge($sql_params, array($offset, $limit));
            }


        }else{
            //有高级搜索
            if($count[0]['count'] <= $offset){
                //从第一页开始显示
                if(is_numeric($offset) && !empty($limit)){
                    $sql .= " limit ?, ?"; 
                    $sql_params =  array_merge($sql_params, array(0, $limit));
                }
    
            }else{
                //显示当前页
                if(is_numeric($offset) && !empty($limit)){
                    $sql .= " limit ?, ?"; 
                    $sql_params =  array_merge($sql_params, array($offset, $limit));
                }
            }
        }
        $sql_result = $this->dbSelect($sql,$sql_params);
        // $count = $this->dbSelect($sql_count,$sqlcount_params);
        $info = array(
            'rows' => array(),
            'total' => $count[0]['count'],

        );
        if(empty($sql_result)){
            return $info;
        }
        $storage = new Storage();
        $jobInfo = new JobInfo();
        $jobs = new JobInfos();
        foreach ($sql_result as $each){
            $name = $each['timepoint'];
            $details = json_decode($each['detail'], true);
            //判断是否有加密 ,如果有手动输入密码则显示小锁标识
            if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
                // $name .= '<i class="viconfont vicon-a-Unlockjiesuo-011"></i>';
                $encryptedflag = true;
            } else {
                $encryptedflag = false;
            }



            $mark =  $jobs->pGetTimepointMark(false, false, false,v1_parse_flag_to_bool($each['importance_flag']));
            $remark = '';   //备注
            //添加备注
            if(!empty($each['remarks'])){
                //根据标记是否显示固定备注显示高度
                // $top ="";
                // if(!empty($mark)){
                //     $top ="top:-4px;";
                // }
                $remark .= '<a class="popovers remarktips" data-container="body" data-trigger="hover" 
                data-placement="right" data-content="'.preg_replace('/\"/', "'", $each['remarks']).'">'.'<i class="viconfont vicon-remark-info"></i></a>';
            }
            // $timepointDiv = '<div>' . $name . '</div>' . '<div class="table-body__timepoint">' . $mark . $remark . '</div>';
            $info['rows'][] = array(
                //获取id
                'id' => $each['id'],
                //获取时间点
                'timepoint' => $name.$mark,
                //获取类型
                'backup_mode' => $jobInfo->getTimepointTypeDes($each['backup_mode']),
                //获取数据大小
                'total_size' => v1_calsize($each['total_size'], true),
                //获取写入大小
                'write_size' => v1_calsize($each['write_size'], true),
                //所在存储
                'storage' => $storage->getStorageName($each['storage_uuid']),
                //获取备注
                'remark' => $each['remarks'],
                //获取标记
                'mark' => "",
                //是否加密
                'encrypted_flag' => $encryptedflag,
                //时间点uuid
                'timepoint_uuid' => $each['timepoint_uuid'],
                //永久标记
                'importance_flag'  => v1_parse_flag_to_bool($each['importance_flag']),
                //备份模式
                'mode' => $each['backup_mode']
            );
        }

        return $info;
    }


    /**
     * 获取时间点app或命名空间下的详细资源列表
     * @author liushuai@vinchin.com
     * @date 2023/6/13
     * @return unknown
     *
     */
    public function getRestoreTimepointResource($params)
    {
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_SUCCESS'),
            'data' => array(),
        );
        $dataList = array();
       //获取时间点
       $dataList['timepoint_uuid'] = $params['timepoint_uuid'];
       //获取命名空间
       $dataList['namespace'] = $params['namespace'] ?? "";
       //获取集群uuid
       $dataList['cluster_uuid'] = $params['cluster_uuid'];
       //获取应用名称
       $dataList['app'] = $params['app'] ?? "";
       //获取应用类型
       $dataList['app_type'] = $params['app_type'] ?? 0;
       //获取密匙
       $dataList['decryption_key'] = null;
       //获取偏移量起始
       $dataList['list_offset'] =  intval($params['list_offset']);
       //获取偏移量数量
       $dataList['list_limit'] =  intval($params['list_limit']);


        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        // 获取时间点所在存储可用的节点uuid
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints([$dataList['timepoint_uuid']]);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }
        $mbResult = $this->service()->getRestoreTimepointResource($nodeuuid, $dataList);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_FAILURE');
            return $resultInfo;
        }
        //获取信息
        $message = $mbResult['message'];
        //获取资源列表
        $resourceList = $message['resources'];
        $info = array(
            'rows' => array(),
            'total' => 0,
            'extraParams' => array(
                'list_total' => $message['list_total'] ?? 0,  //总数量
                'list_offset' => $message['list_offset'] ?? 0,  //起始偏移量
                'list_size' => $message['list_size'] ?? 0, //偏移数量
                'list_remains' => $message['list_remains'] ?? 0, //剩余数量
            ),
        );
        if(empty($resourceList)){
            // $resultInfo['info'] = $info;
            $resultInfo['data'] = $info;
            return $resultInfo;
        }
        foreach ($resourceList as $each){
            $info['rows'][] = array(
                "id" => $each['namespace']."_".$each['group']."_".$each['version']."_".$each['kind']."_".$each['name'],
                //获取资源名称
                "name" => $each['name'],
                //获取资源命名空间'
                "namespace" => $each['namespace'],
                //获取资源版本
                "version" => $each['version'],
                //获取资源组
                "group" => $each['group'],
                //获取应用名称
                "app" => $each['app'],
                //获取应用类型
                "app_type" => $each['app_type'],
                //获取资源类型
                "kind" => $each['kind'],
                //获取大分组类型
                "category" => $each['category'],
                "checkbox" => true,
                //获取时间点
                "timepoint_uuid" => $params['timepoint_uuid'],
               //获取集群uuid
               "cluster_uuid" => $params['cluster_uuid'],
                //获取密匙???

            );
            $info['total']++;
        }

        $resultInfo['data'] = $info;
        return $resultInfo;


    }



    /**
     * 获取资源详情
     * @author liushuai@vinchin.com
     * @date 2023/6/13
     * @return unknown
     *
     */
    public function getRestoreTimepointResourcDetails($params)
    {
        $dataList = array();
        //获取时间点
        $dataList['timepoint_uuid'] = $params['timepoint_uuid'];
        //获取命名空间
        $dataList['namespace'] = $params['namespace'];
        //获取集群uuid
        $dataList['cluster_uuid'] = $params['cluster_uuid'];
        //获取资源分组
        $dataList['group'] = $params['group'];
        //获取资源版本
        $dataList['version'] = $params['version'];
        //获取资源类型
        $dataList['kind'] = $params['kind'];
        //获取资源名称
        $dataList['name'] = $params['name'];
        //获取密匙
        $dataList['decryption_key'] = null;
        //获取节点
        $nodeuuid =$params['real_node_uuid'];
        // 获取时间点所在存储可用的节点uuid
        $allStorageData = DataManage::instance()->getStorageInfoByTimepoints([$params['real_node_uuid']]);
        foreach ($allStorageData as $item) {
            if ($item['storage_online_flag']) {
                $nodeuuid = $item['node_uuid'];
                break;
            }
        }
        $mbResult = $this->service()->getRestoreTimepointResourcDetails($nodeuuid, $dataList);
        if(!$mbResult['result']){
            return $mbResult;
        }
        //获取信息
        $message = $mbResult['message'];
        //获取yaml信息
        $yaml  = $message['yaml'];
        $mbResult['info'] = $yaml;
        return $mbResult;
    }


    /**
     * 获取存储类
     * @author liushuai@vinchin.com
     * @date 2023/6/13
     * @return unknown
     *
     */
    public function getRestorePvc($params)
    {
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_DATA_GET_TARGET_STORAGE_SUCCESS'),
            'data' => array(
                'rows' => array(),
                'total' => 0,
            ),
        );
        $dataList = array();
        $dataList['cluster_uuid'] = $params['cluster_uuid'];
        //获取需要勾选的pvc数据
        $recoverPVCList = $params['recoverPVCList'];
        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->service()->getRestorePvc($nodeuuid, $dataList);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_DATA_GET_TARGET_STORAGE_FAILURE');
            return $resultInfo;
        }
        //获取信息
        $message = $mbResult['message'];
        //获取pvc列表
        $pvcs = $message['pvcs'];
        //获取存储类
        $storage_classes_list = array();
        foreach ($message['storage_classes'] as $one){
            $storage_classes_list[] = $one['name'];
        }
        if(empty($pvcs)){
            return $resultInfo;
        }
        //获取所有pvc名称
        $pvc_list = array();
        foreach ($pvcs as $each){
            //获取pvc的命名空间
            $pvc_namespace = $each['namespace'] ?? '';
            //获取pvc的名称
            $pvc_name = $each['name'];
            $pvc_list[] = $pvc_namespace."_".$pvc_name;
        }
        //这里应该是循环时间点的数据
        foreach($recoverPVCList as $each){
             //获取每一条数据
             $resultInfo['data']['rows'][] = array(
                //获取持久卷名称
                "pvc_name" => $each['name'],
                //获取新持久卷名称
                "pvc_name_new" => array(
                    'pvc_name'=> $each['name'],
                    'pvc_list' => $pvc_list,
                    //判断pvc_name是否存在于pvc_list中, true表示存在，false表示不存在
                    'pvc_exists' => in_array($each['ns_name']."_".$each['name'], $pvc_list) ? true : false,
                ),
                //获取存储类
                "storage_class" => array(
                    'storage_class_list' =>$storage_classes_list,
                    'storage_class' => $each['pvc_storage_class'],
                    //是否存在该存储类, true表示存在，false表示不存在
                    'storage_class_exists' => in_array($each['pvc_storage_class'], $storage_classes_list) ? true : false,
                ),
                // "old_sc" =>$each['storage_class_name'],
                //获取源所属命名空间
                "namespace" => $each['namespace'],
                //获取容量
                "size" => $each['size'],
                "checkbox" => true,
            );
            $resultInfo['data']['total']++;

        }



        // foreach ($pvcs as $each){
        //     //获取pvc的name
        //     $pvc_name = $each['name'];
        //     //获取命名空间
        //     $namespace = $each['namespace'];
        //     // 使用 array_filter 筛选数组
        //     // $filtered = array_filter($recoverPVCList, function($item) use ($pvc_name, $namespace) {
        //     //     return $item['name'] == $pvc_name && $item['namespace'] == $namespace;
        //     // });
        //     // if(empty($filtered)){
        //     //     continue;
        //     // }
           
        //     //获取每一条数据
        //     $resultInfo['data']['rows'][] = array(
        //         //获取持久卷名称
        //         "pvc_name" => $each['name'],
        //         "exec_status" => $each['exec_status'],
        //         //获取新持久卷名称
        //         "pvc_name_new" => array(
        //             'pvc_name'=> $each['name'],
        //             'pvc_list' => $pvc_list,
        //             //判断pvc_name是否存在于pvc_list中, true表示存在，false表示不存在
        //             'pvc_exists' => in_array($each['name'], $pvc_list) ? true : false,
        //         ),
        //         //获取存储类
        //         "storage_class" => array(
        //             'storage_class_list' =>$storage_classes_list,
        //             'storage_class' => $each['storage_class_name'],
        //             //是否存在该存储类, true表示存在，false表示不存在
        //             'storage_class_exists' => in_array($each['storage_class_name'], $storage_classes_list) ? true : false,
        //         ),
        //         "old_sc" =>$each['storage_class_name'],
        //         //获取源所属命名空间
        //         "namespace" => $each['namespace'],
        //         //获取容量
        //         "size" => $each['size'],
        //         "checkbox" => true,
        //     );
        //     $resultInfo['data']['total']++;
        // }
        return $resultInfo;
    }

    /**
     * 得到本地节点UUID
     */
    public function getLocalNodeUUID()
    {
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }

    /**
     * 给时间点添加备注
     * @author liushuai@vinchin.com
     * @date 2023/6/13
     * @return unknown
     *
     */
    public function remarkTimepoint($params){
        $remark = $params['remark'];
        $timepointuuid = $params['timepoint_uuid'];
        $sql = "update bd_backup_timepoint set remarks = ? where timepoint_uuid = ?";
        $result = $this->dbExec($sql,array($remark,$timepointuuid));
        return $result;  
    }

    
    /**
     * 给时间点添加星标
     * @author liushuai@vinchin.com
     * @date 2023/6/13
     * @return unknown
     *
     */
    public function addStar($params){
        $mbResult = $this->service()->addStar($params);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($result) {
            // return [$result, '添加星标', $msg];
            $this->muOpResult(true, xphp_get_lang('WEB_KUBE_DATA_SET_BOOKMARK'),$msg);
        } else {
            //返回错误信息
            $this->muOpResult(false, xphp_get_lang('WEB_KUBE_DATA_SET_BOOKMARK'), $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 给时间点删除星标
     * @author liushuai@vinchin.com
     * @date 2023/6/13
     * @return unknown
     *
     */
    public function deleteStar($params){
        $mbResult = $this->service()->deleteStar($params);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        // $pfOpcode = Xphp::instance('PFOpcode');
        // $operate = $pfOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if ($result) {
            // return [$result, '添加星标', $msg];
            $this->muOpResult(true, xphp_get_lang('WEB_KUBE_DATA_DELETE_BOOKMARK'),$msg);
        } else {
            //返回错误信息
            $this->muOpResult(false, xphp_get_lang('WEB_KUBE_DATA_DELETE_BOOKMARK'), $msg, '', $mbResult['errorCode']);
        }
    }












}



