<?php

namespace app\v1\os\v0\logic;
use app\v1\common\logic\Recover;
use app\v1\resources\v0\logic\Index;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;
use app\v1\user\v0\logic\User;
use app\v1\opcode\PfOpcode;
use app\v1\os\v0\logic\OsRecover;
use app\v1\os\v0\logic\OsBackUp;
use app\v1\common\logic\Backup;
use app\v1\opcode\OsOpcode;

/**
 * note          操作系统 之瞬时恢复 logic
 * @author       lilingyu@vinchin.com
 * @date         2024/6/5  16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsInstantRecover extends  Recover
{

    /**
     * 获取瞬时恢复目标主机IP集合
     */
    public function getOptionIP($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_IP_LIST'),
            'data' => array(),
        );
        $task_uuid = $params['taskuuid'];
        $instantflag = $params['instantflag'];
        //如果是瞬时恢复 直接获取参数
        if($instantflag){
            $os_type =  $params['os_type'];
            $agent_uuid =  $params['agent_uuid'];
        }else{
            //先获取操作系统类型
            $sql  = "select obt.os_type, obt.agent_uuid from os_list ol
            left join os_backup_timepoint obt on ol.timepoint_uuid  = obt.timepoint_uuid
            where ol.task_uuid  = ?";
            $result =  $this->dbSelect($sql,array($task_uuid));
            $os_type =  $result[0]['os_type'];
            $agent_uuid =  $result[0]['agent_uuid'];
        }
        $sql1 = "select agent_name, hostname, agent_uuid, os_type, ip,  online_flag, authorization_module, net_model from bd_agent ";
        $resourceHandler = new Index;
        if (!in_array($_SESSION['userLevel'],[1,2,3])) {
            $agent_list = $resourceHandler->pGetUserAllResource(xphp_get_user_info()['userUuid'],10);
            $agent_list =array_column($agent_list, 'resource_uuid');
            if(empty($agent_list)){
                return json_encode(array());
            }else{
                $agent_uuids = implode(',', array_map(function($item) {return "'{$item}'";}, $agent_list));
                $sql1 .= " where agent_uuid in ($agent_uuids) ";
                //如果是瞬时恢复 那么win->win linux->linux 限制不能为livecd
                if($instantflag){
                    if($os_type == 2){
                        $sql1 .= " and os_type = 'Linux' ";
                    }else{
                        $sql1 .= " and os_type = 'Windows' ";
                    }
                    //瞬时恢复还要屏蔽 livecd/winpe
                    $sql1 .= " and agent_type not in (2,3,4) ";
                }else{
                    //目前只能 win->win/linux, linux->linux
                    //排除掉nas的agent agent_type = 3为nas的
                    if($os_type == 2){
                        $sql1 .= " and os_type = 'Linux' and agent_type not in (3, 4)";
                    }else{
                        $sql1 .= " and agent_type not in (3, 4)";
                    }
                }
            }
        }else{
            //如果是瞬时恢复 那么win->win linux->linux 限制不能为livecd
            if($instantflag){
                if($os_type == 2){
                    $sql1 .= " where os_type = 'Linux' ";
                }else{
                    $sql1 .= " where os_type = 'Windows' ";
                }
                //瞬时恢复还要屏蔽 livecd/winpe
                $sql1 .= " and agent_type not in (2,3,4) ";
            }else{
                //目前只能 win->win/linux, linux->linux
                //排除掉nas的agent agent_type = 3为nas的
                if($os_type == 2){
                    $sql1 .= " where os_type = 'Linux' and agent_type not in (3, 4)";
                }else{
                    $sql1 .= " where agent_type not in (3, 4)";
                }
            }
        }
        $result = $this->dbSelect($sql1);
        $info = array();
        foreach($result as $op){
            //处理名字
            $titleDes = (new OsBackUp())->getAgentName($op['hostname'], $op['agent_name'], $op['ip']);
            if($agent_uuid == $op['agent_uuid']){
                $titleDes .= xphp_get_lang('WEB_OS_ORIGINAL_HOST');
            }
            if($op['online_flag'] != 1){
                $titleDes .= xphp_get_lang('WEB_OS_OFFLINE');
            }
            //如果为空 则是未添加授权的新虚拟机
            if(empty($op['authorization_module'])){
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' =>0,//0为未授权主机的新机子
                    'net_model' => $op['net_model'],
                );
                continue;
            }
            $authorization = json_decode($op['authorization_module'],true);
            //如果都是false 那么也是未授权的
            if($authorization['file'] == false && $authorization['vm'] == false && $authorization['mysql'] == false && $authorization['oracle'] == false &&
                $authorization['sqlserver'] == false && $authorization['dm'] == false && $authorization['os'] == false && $authorization['cdp']
                == false && $authorization['desktop'] == false && $authorization['database'] == false){
                    $info[] = array(
                        'os_type' => $op['os_type'],
                        'agent_name' => $titleDes,
                        'agent_uuid' => $op['agent_uuid'],
                        'ip' => $op['ip'],
                        'type' =>1,//1为一个未授权的机子
                        'net_model' => $op['net_model'],
                    );
                    continue;
            }
            if($authorization['os'] == true){
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' =>2,//1为授权有主机的机子
                    'net_model' => $op['net_model'],
                );
            }
        }
        $resultInfo['data'] = $info;
        return $resultInfo;
    }
    
    /**
     * 获取瞬时恢复缓存存储列表
     */
    public function getCatchStorage($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_IP_LIST'),
            'data' => array(),
        );
        $nodeuuid = $params['nodeuuid'];
        $sql = "select mount_flag, node_uuid, storage_nickname, storage_uuid, storage_type, total_size, free_size, status, error_code 
        from bd_storage_resource where status = ? and mount_flag = ? 
        and error_code = ? and lan_free_flag = ?  and use_mode = ? and storage_type not in (8,9,10) and node_uuid = ?";
        $sqlParams = array(xphp_get_config('resource', 'STORAGE_STATUS')['ONLINE'],
        xphp_get_config('app', 'FLAG')['SET'], 0, xphp_get_config('app', 'FLAG')['UNSET'],xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['BACKUP'], $nodeuuid);  
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $nodeHandler = new Node();
        $storageHandler = new Storage();
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        $user = xphp_get_user_info();
        $resourceType = xphp_get_config('resource', 'RESOURCE_TYPE');
        if (!empty($_SESSION['tenantuuid'])){
            $resourceInfo = (new \app\v1\resources\v0\logic\Index())->pGetUserAllResource($user['useruuid'], $resourceType['STORAGE']);
            if(!empty($resourceInfo)){
                foreach ($resourceInfo as $r){
                    $resourceList[] = $r['resource_uuid'];
                }
            }
        }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            if(!empty($_SESSION['tenantuuid']) && !in_array($d['storage_uuid'], $resourceList)) continue;
        	$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
        	$storageStatus = $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
        	if($storageStatus != xphp_get_config('resource', 'STORAGE_STATUS')['ONLINE']) continue;
        	$storageDes = $d['storage_nickname'];
        	if(empty($_SESSION['tenantuuid'])){
        	    $storageDes .= "(" . $storageHandler->getStorageTypeDes($d['storage_type']) . ", ".xphp_get_lang('UI_STORAGE_TOTAL_SIZE') . ":" . v1_calsize($d['total_size']) .
        	    ", " . xphp_get_lang('WEB_PLATFORM_DC_AVAILABLE_SPACE') . ":" . v1_calsize($d['free_size']) . ")";
        	}else{
        	    $quotaInfo = (new User())->getUserQuotaInfo();
        	    if(!empty($quotaInfo['des'])){
        	        $storageDes .= "(" . $storageHandler->getStorageTypeDes($d['storage_type']) . ", " . $quotaInfo['des'] . ")";
        	    }
        	}
            $info[] = array(
                'uuid' => $d['storage_uuid'],
                'text' => $storageDes,
            	'name' => $d['storage_nickname'],
            	'type' => intval($d['storage_type']),
            	'total_size' => intval($d['total_size']),
            	'free_size' => intval($d['free_size'])
            );
        }
        array_multisort(array_column($info, 'free_size') ,SORT_DESC, $info);
        $resultInfo['data'] = $info;
        return $resultInfo;
    }


    /**
     * 获取瞬时恢复缓存存储列表
     */
    public function getTaskName(){
         //返回数据
         $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_INSTANT_RECOVERY_TASK_NAME'),
            'data' => array(),
        );
        $taskName = xphp_get_lang('WEB_OS_INSTANT_RECOVERY_TASK');
        $logicOsBackup = new OsBackUp;
        $resultInfo['data'] = $logicOsBackup->getValidTaskName($taskName);
        return $resultInfo;
    }

    /**
     * 获取瞬时恢复缓存存储列表
     */
    public function createInstantOSRecoverJob($params)
    {
        $task_name = $params['task_name'];
        $cache_target = $params['cache_target'];
        $nodeuuid  =$params['node_uuid'];
        $recovery_timepoint_info =  $params['recovery_timepoint_info'];
        $transport_ip  = $params['transport_ip'];
        $pfMsg['task_name'] = $task_name;
        $pfMsg['cache_target'] = $cache_target;
        $pfMsg['recovery_timepoint_info']  = $recovery_timepoint_info;
        $pfMsg['transport_ip'] = $transport_ip;
        $opName = "OS_PRIVATE_TASK_OP_CODE_CREATE_INSTANTANEOUS_RECOVERY";
        $msg = json_encode($pfMsg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = new OsOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 迁移时连接测试（获取迁移目标主机信息）
     */
    public function osInstantlinkTest($params){
        //首先需要通过任务id获得时间点id 再调用linkTest方法
        $task_uuid =  $params['taskuuid'];
        $agentList = $params['agentList'];
        //通过任务id获取备份时间点id
        $sql = "select timepoint_uuid from os_list where task_uuid = ?";
        $result =  $this->dbSelect($sql,array($task_uuid));
        $timepointist = array();
        foreach ($result as $each){
            $timepointist[] = $each['timepoint_uuid'];
        }
        $getInfo =  array(
            "agentList"=>$agentList,
            "timepointList"=>$timepointist
        );
        $resultInfo = (new OsRecover())->linkTest($getInfo,true);
        return $resultInfo;
    }

    /**
     * 获取迁移时传输网络列表
     */
    public function getNetList($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => '',
            'data' => array(),
        );
        //直接通过任务id在bd_task中去找到node_uuid 然后直接使用调用你node的getNodeNetworkList方法
        $task_uuid = $params['taskuuid'];
        $sql = "select node_uuid from bd_task where task_uuid  = ?";
        $result =  $this->dbSelect($sql,array($task_uuid));
        $getparams = array(
            "nodeuuid" => $result[0]['node_uuid']
        );
        $resultInfo['data']  = $this->getNodeNetworkList($getparams);
        return $resultInfo;
    }

    /**
     * 创建操作系统迁移任务
     */
    public function createOSMotionJob($params){
        //得到任务uuid
        $task_uuid = $params['taskuuid'];
        //得到传输线程
        $thread_num = $params['highInfo']['threadnum'];
        //得到全局策略组uuid
        $strategygroupuuid = $params['strategygroupuuid'];
        $backupHandler = new Backup;
        //组合传输策略
        $transport_strategy = $backupHandler->groupTransportStrategy($params['highInfo']['transfer']);
        //得到限速策略
        $speed_limit_strategy_list = $backupHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $speed_limit_strategy = $backupHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到操作系统恢复信息
        $recovery_oss_info = $params['recoverInfo'];
        //组装数据
        $pfMsg['task_uuid'] = $task_uuid;
        $pfMsg['thread_num'] = $thread_num;
        $pfMsg['transport_strategy']=  $transport_strategy;
        $pfMsg['speed_limit_strategy_list'] = $speed_limit_strategy_list;
        $pfMsg['speed_limit_strategy'] = $speed_limit_strategy;
        $pfMsg['recovery_oss_info'] = $recovery_oss_info;
        //因为发送消息需要到节点nodeuuid  所以需要再获取一次节点uuid
        $sql = "select node_uuid from bd_task where task_uuid  = ?";
        $result =  $this->dbSelect($sql,array($task_uuid));
        $nodeuuid =   $result[0]['node_uuid'];
        //发送消息
        //得到恢复操作码
        $opName = "OS_PRIVATE_TASK_OP_CODE_INSTANTANEOUS_RECOVERY_START_MIGRATION";
        $msg = json_encode($pfMsg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg);

        $result = $mbResult['result'];
        $pfOpcode = new OsOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 获取节点网络列表
     * @param unknown $params
     * @return string
     */
    public function getNodeNetworkList($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $sql = "select ip, port, alias_name, network_order, network_uuid from bd_node_network where ip != '' and node_uuid = ?  order by network_order asc";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $list = array();
        foreach ($data as $d){
            $list[] = array(
                'ip' => $d['ip'],
                'port' => $d['port'],
                'alias_name' => $d['alias_name'],
                'network_order' => $d['network_order'],
                'network_uuid' => $d['network_uuid'],
            );

        }
        return $list;
    }
}

