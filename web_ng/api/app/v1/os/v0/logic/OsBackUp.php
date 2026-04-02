<?php

namespace app\v1\os\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\OsOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;
use xphp\BLLHandler;

/**
 * note          操作系统 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsBackUp extends Backup
{
    /**
     * 得到备份端代理树
     */
    public function getOsBackupTree($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>  xphp_get_lang('WEB_OS_GET_BACKUP_AGENT_TREE'),
            'data' => array(),
        );
        $agent_uuids = $params['agent_uuids'];

       
        //从数据库获取所有已添加的代理以及分组信息
        $sql = "select ba.agent_uuid,ba.os_type,ba.agent_name,ba.hostname,ba.ip,ba.group_uuid,ba.online_flag,ba.authorization_module, ba.net_model,
        bag.group_name, bag.group_type from bd_agent ba,bd_agent_group bag 
        where ba.group_uuid = bag.group_uuid and ba.plugin_deploy_status = 2 and ba.agent_type not in (3, 4, 5)";
        //获取当前用户所拥有的集群列表
        if(v1_auth_need_check_look()){
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql= v1_auth_get_source_by_type(
                xphp_get_config('resource','RESOURCE_TYPE')['OS'],
				'ba.agent_uuid'
            );
            $sql .= " and ({$resourceUuidSql})";
        };
        
        //得到所有数据
        $result = $this->dbSelect($sql);
		$info = array();
        //判断是否在任务中
        $task_agent_uuid_list = $this->getTaskAgentuuidList();

        $group_uuid_list = array();
        foreach ($result as $op){
            //先判断是否已经生成1级树
            if(!in_array($op['group_uuid'],$group_uuid_list)){
                $group_uuid_list[] = $op['group_uuid'];
                $open_flag = false;
                //第一层分组信息层
                $info[] = array(
                    "id" => $op['group_uuid'],
                    "pId" => 0,
                    "name" => $op['group_name'],
                    "title" => $op['group_name'],
                    "isParent" => true,
                    "uuid" => $op['group_uuid'],
                    "nocheck" => false,
                    "type" => 0,
                    "icon" => "./img/platform/flag.png",
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" => $open_flag,
                    "iconSkin" => 'Windowslogo',
                );
            }
            //判断是否有被勾选 主要是修改的时候需要用到
            $checkflag = false;
            if(!empty($agent_uuids) && in_array($op['agent_uuid'],$agent_uuids)){
                $checkflag = true;
                //这里需要把其父级也设置为勾选状态
                for($i = 0;$i<count($info);$i++){
                    if($op['group_uuid'] == $info[$i]['uuid']){
                        $info[$i]['checked'] = true;
                    }
                }

            }


            //是否授权
            $authorization_module = $op['authorization_module'];
            if(empty($authorization_module)){
                $auth_flag = false;
            }else{
                $authorization_module = json_decode($authorization_module,true);
                $auth_flag = $authorization_module['os'] ? true:false;
            }
            //是否在线
            $online_flag = $op['online_flag']== 1 ? true:false;

            //处理名字
            $titleDes = $this->getAgentName($op['hostname'], $op['agent_name'], $op['ip']);
            if($auth_flag == false){

                $titleDes = "(".xphp_get_lang('WEB_SYSTEM_UNAUTHIORIZED').")".$titleDes;
            }
            if($online_flag == false){

                $titleDes = $titleDes.xphp_get_lang('WEB_OS_OFFLINE');
            }

            //是否在任务中
            if(in_array($op['agent_uuid'],$task_agent_uuid_list)){
                $agentuuidInTask = true;
            }else{
                $agentuuidInTask = false;
            }


            //这里判断有4个条件
            //1 是否在任务中 2是否是修改 3是否授权 4是否离线
            //是否是在任务中
            if(in_array($op['agent_uuid'],$task_agent_uuid_list)){
                //是否是修改
                if(!empty($agent_uuids) && in_array($op['agent_uuid'],$agent_uuids)){

                }

            }

            //先判断是否授权和离线
            if(!$auth_flag || !$online_flag){
                //如果未授权或者离线 统一禁用
                $chkDisabledflag = true;
            }else{
                //是否在任务中
                if(in_array($op['agent_uuid'],$task_agent_uuid_list)){
                    //查看是否是修改
                    if(!empty($agent_uuids) && in_array($op['agent_uuid'],$agent_uuids)){
                        //是修改而且在任务中
                        $chkDisabledflag = false;
                    }else{
                        //不是修改 但是在任务中
                        $chkDisabledflag = true;//禁用
                    }
                }else{
                    $chkDisabledflag = false;
                }

            }


            //开始组合第二层
            $info[] = array(
                "id" => $op['agent_uuid'],
                "pId" => $op['group_uuid'],
                "name" => $titleDes,
                "title" => $titleDes,
                "isParent" => false,
                "uuid" => $op['agent_uuid'],
                "nocheck" => false,
                "type" => 1,
                "icon" => "./img/os/".$op['os_type'].".png",
                "clickshow" => false,
                "checked" => $checkflag,
                "chkDisabled" => $chkDisabledflag,
                "iconSkin" => $op['os_type'].'logo',
                //是否是在任务中
                'agentuuidInTask' => $agentuuidInTask,
                //是否授权操作系统
                'auth_flag' =>$auth_flag, //false 未授权
                //是否离线
                'online_flag' =>$online_flag, //false 离线
                //传输网络
                "net_model" => $op['net_model'],
            );
        }
        $resultInfo['data'] = $info;
        return $resultInfo;

    }



     /*
     * 得到在任务中的主机备份的agentuuid集合
     */
    public function getTaskAgentuuidList(){
        $info = array();
        $sql = "select ol.agent_uuid from os_list ol, bd_task bt where ol.task_uuid = bt.task_uuid and bt.task_type = ?";
        $result = $this->dbSelect($sql,array(xphp_get_config('task','TASKTYPE')['OS_BACKUP']));
        if(empty($result)){
            return $info;
        }
        foreach ($result as $each){
            $info[] = $each['agent_uuid'];
        }
        return $info;
    }


     /**
     * 如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * @param unknown $host_name 主机名
     * @param unknown $agent_name 别名
     * @return name(ip)
     */
    public function getAgentName($host_name,$agent_name,$ip){
        $name_ip = "(".$ip.")";
        if(empty($host_name) && !empty($agent_name)){
            return $agent_name.$name_ip;
        }else if(!empty($host_name) && empty($agent_name)){
            return $host_name.$name_ip;
        }else{
            if($agent_name == $ip){
                return $host_name.$name_ip;
            }else{
                return $agent_name.$name_ip;
            }
        }
    }




     /**
     * 获取代理端的磁盘信息
     * @param unknown $params
     * @$sourceflag 为true时返回不处理的数据  为false返回处理后的数据 默认false
     * @return void|unknown
     */
    public function getOSBackupAgentInfo($params,$sourceflag = false){
         //返回数据
         $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_DISK_AGENT_INFO'),
            'data' => array(),
        );
        //得到agent_uuid
        $agentuuid = $params['uuid'];
        //得到主机类型
        $osinfo = $this->getOSType($agentuuid);
        $ostype = $osinfo['os_type'];
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $agentuuid;
        $msg = json_encode($pfMSg);
        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true);
        $result = $mbResult['result'];
        $pfOpcode  = new OsOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $data = $mbResult['msg'];
        //返回结果到UI
        if(!$result){//如果返回失败
            $resultInfo['succcess'] = $result;
            $resultInfo['message'] = $operate;
            $resultInfo['code'] = $mbResult['errorCode'];
           return $resultInfo;
        }
        $disk_list =$data['disk_list'];
        if($sourceflag){
            $resultInfo['data'] = $disk_list;
            return $resultInfo;
        }
        //得到所有的volume_set数据
        $infoVolume = array(); //所有磁盘分区信息集合
        $infoDisk = array(); //所有磁盘信息集合
        foreach ($disk_list as $op){
            //判断是否展示这个卷
            if($op['show_flag'] != 2){
                $infoDisk[] = array(
                    "display_name" => $op['display_name'],
                    "type" => $op['type'],
                    "vendor" => $op['vendor'],
                    "model" => $op['model'],
                    "part_table_type" => $op['part_table_type'],
                    "name" => $op['name'],
                    "path" => $op['path'],
                    "serial_number" => $op['serial_number'],
                    "disk_uuid" => $op['disk_uuid'],
                    "dev_id" => $op['dev_id'],
                    "total_size" => $op['total_size'],
                    "total_size_str" => v1_calsize($op['total_size'], true),
                    "allocated_size" => $op['allocated_size'],
                    "logic_sector" => $op['logic_sector'],
                    "physical_sector" => $op['physical_sector'],
                    "mount_point" => $op['mount_point'],
                    "is_slave" => $op['is_slave'],
                    "system_flag" => $op['system_flag'],
                );
            }
            $volume_set = $op['volume_set'];//获取分区信息
            //如果没有分区的信息 则继续下个
            if(empty($volume_set)){ 
                continue;
            }
            foreach ($volume_set as $one){
                //判断是否展示这个卷
                if($one['show_flag'] != 1){
                    continue;
                }
                //判断类型后根据不同类型展示不同类型
                $infoVolume[] = array(
                    "display_name" => $one['display_name'],
                    "show_flag" => $one['show_flag'],
                    "rd_flag" => $one['rd_flag'],
                    "is_bitLocker" => $one['is_bitLocker'] ? $one['is_bitLocker'] : 2,
                    "system_flag" => $one['system_flag'],
                    "dev_id"=> $one['dev_id'],
                    "type"=> $one['type'],
                    "name"=> $one['name'],
                    "path"=> $one['path'],
                    "vol_uuid"=> $one['vol_uuid'],
                    "mount_point"=> $one['mount_point'],
                    "fs_type"=> $one['fs_type'],
                    "gpt_type"=> $one['gpt_type'],
                    "gpt_id"=> $one['gpt_id'],
                    "gpt_name"=> $one['gpt_name'],
                    "gpt_attribute"=> $one['gpt_attribute'],
                    "total_size"=> $one['total_size'],
                    "total_size_str" => v1_calsize($one['total_size'], true),
                    "used_size"=> $one['used_size'],
                    "used_size_str" => v1_calsize($one['used_size'], true),
                    "usable_size" =>v1_calsize($one['total_size'] - $one['used_size'], true),
                    "start_offset"=> $one['start_offset'],
                    "sector_start"=> $one['sector_start'],
                    "sector_count"=> $one['sector_count'],
                    "hidden_sector"=> $one['hidden_sector'],
                    "is_boot"=> $one['is_boot'],
                    "is_holder"=> $one['is_holder'],
                    "is_slave"=> $one['is_slave'],
                    "iconSkin" => $this->getIconOfData($ostype, $one['system_flag']),
                    "removable_flag" => v1_parse_flag_to_bool($one['removable_flag']),
                );
            }
        }
        $info = array(
            'infoDisk' => $infoDisk,
            'infoVolume' => $infoVolume,
        );
        $resultInfo['data'] = $info;
        return  $resultInfo;

    }


  /**
     * 根据agent_uuid得到系统类型 目前就window和linux
     * 数据库中存的字段为str类型 为Windows和Linux   注意大小写
     * @param unknown $agent_uuid
     * @return string
     */
    public function getOSType($agent_uuid){
        $sql = "select os_type,agent_name,hostname,ip,net_model from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql,array($agent_uuid));
        //得到系统类型
        $os_type = $result[0]['os_type'];
        return array(
            'os_type' => $result[0]['os_type'],
            'agent_name' => $result[0]['agent_name'],
            'hostname' => $result[0]['hostname'],
            'ip' => $result[0]['ip'],
            'net_model' => $result[0]['net_model'],
        );
    }




      /**
     * 得到本地节点UUID
     */
    public function getLocalNodeUUID(){
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app','NODETYPE')['MASTER']));
        return $data[0]['node_uuid'];
    }


     /**
     * 得到是否是系统盘
     * @param unknown $ostype 操作系统类型
     * @param unknown $system_flag 1是系统盘 2是数据盘     是否是有系统的盘
     * return icon的名字,这个和css里面的class相对应
     */
    public function getIconOfData($ostype,$system_flag){
        $iconName = "datalogo";
        //先判断操作类型
        if($ostype == 1 || $ostype == "Windows" || $ostype == "windows"){
            if($system_flag == 1){
                $iconName = "oslogo";
            }else{
                $iconName = "datalogo";
            }
        }else{
            if($system_flag == 1){
                $iconName = "linuxlogo";
            }else{
                $iconName = "datalogo";
            }
        }
        return $iconName;
    }


    /**
     * 得到主机任务名(备份)
     * @param unknown $params
     */
    public function getOSBackupTaskName(){
         //返回数据
         $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_OS_GET_BACKUP_TASK_NAME'),
            'data' => array(),
        );
        $taskName = xphp_get_lang('WEB_OS_HOST_TASK');
        $taskName .= xphp_get_lang('UI_VM_REPORT_BACKUP_TASK');
        $resultInfo['data'] = $this->getValidTaskName($taskName);
        return $resultInfo;
    }


    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    public function getValidTaskName($taskName){
        $oldTaskName = $taskName;
        for($i=1; $i<1000; $i++){
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if(empty($data) && empty($data1)){
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }



     /**
     * 创建主机备份任务 
     * @param unknown $params
     */
    public function createOSBackupJob($params){
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $this->paramsCheck($task_name);
        $module_type = xphp_get_config('module','MODULE_TYPE')['OS'];
       
        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['strategygroupuuid'];
        //组合备份方式完备差备等
        $time_strategy_list = $this->groupBackupTimeList($params['backup_info'], $strategygroupuuid);
        //组合保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['high_info']['reserve']);
        //组合传输策略
        $transport_strategy =$this->groupTransportStrategy($params['high_info']['transfer']);
        //组合存储策略
        $storage_strategy = $this->groupStorageStrategy($params['high_info']['store']);
        //组合节点信息
        $nodeInfo =$this->groupBackupNodeInfo($params['high_info']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage($task_name, $module_type,
            $time_strategy_list, $reserver_strategy, $transport_strategy, $storage_strategy, $nodeInfo);
        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task','TASKTYPE')['OS_BACKUP'];
        
        //得到线程数
        $pfMsg['thread_num'] = $params['thread_num'];
        //得到限速策略
        $pfMsg['speed_limit_strategy_list'] = [];
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speed_info']);
        
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        //得到备份节点ip (没有 传空值)
        $pfMsg['backup_server_ip'] = '';
        //得到指定网段 (没有 传空值)
        $pfMsg['transport_ip_segment'] = '';
        //-------以下为主机私有------
        //得到有效数据
        $pfMsg['valid_data_flag'] = v1_parse_bool_to_flag($params['src_info']['valid_data_flag']);
        //得到静默快照
        $pfMsg['silent_snapshot_flag'] = v1_parse_bool_to_flag($params['src_info']['silent_snapshot_flag']);
        //得到CBT
        $pfMsg['cbt_flag'] =v1_parse_bool_to_flag($params['src_info']['cbt_flag']);
        //得到快照
        $pfMsg['snapshot_flag'] = v1_parse_bool_to_flag($params['src_info']['snapshot_flag']);
        //得到传输模式  
        $pfMsg['transport_priority'] = $params['src_info']['transport_priority'];
        //得到备份选择的虚拟机以及排除等信息(独有结构体)
        $pfMsg['backup_oss_info'] = $params['src_info']['backup_oss_info'];
        $msg = json_encode($pfMsg);
        //发送消息
        //得到备份创建操作码
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $mbResult = $this->mbOSMsg($nodeInfo['node_uuid'],$opName,$msg);
        $result = $mbResult['result'];
        $pfOpcode  = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if(!$result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建主机备份任务    我这备注简直赏心悦目 优美  不需要描述了 自己看
     * @param unknown $params
     */
    public function editOSBackupJob($params)
    {
        //public params
        $task_name = htmlspecialchars_decode($params['taskName']);
        $this->paramsCheck($task_name);
        $module_type = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        //全局策略uuid  (没有传空值)
        $strategygroupuuid = '';
        //组合节点信息
        $nodeInfo = (new BLLHandler())->groupBackupNodeInfo($params['highInfo']['node']);
        //获取传输网络
        $tranferInfo  = $params['highInfo']['transfer'];
        $tranferInfo['network'] = (new Node())->pGetDiffNodeNetwork($params['taskuuid'], $nodeInfo['node_uuid'], $params['highInfo']['transfer']['network']);
        // //全局策略uuid  (没有为空值)
        // $strategygroupuuid = $params['strategygroupuuid'];
        //组合备份方式完备差备等
        $time_strategy_list = $this->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        //组合保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['highInfo']['reserve'],$strategygroupuuid);
        //组合传输策略
        $transport_strategy = $this->groupTransportStrategy($tranferInfo,$strategygroupuuid);
        //组合存储策略
        $storage_strategy = $this->groupStorageStrategy($params['highInfo']['store'],$strategygroupuuid,$params['taskuuid']);
        //时间策略备份方式
        $backup_type = $params['backupInfo']['type'];
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo,
            $backup_type
        );
        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'];

        //得到线程数
        $pfMsg['thread_num'] = $params['thread_num'];
        //得到限速策略
        //得到限速策略
        $pfMsg['speed_limit_strategy_list'] = $this->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        //得到备份节点ip (没有 传空值)
        $pfMsg['backup_server_ip'] = '';
        //得到指定网段 (没有 传空值)
        $pfMsg['transport_ip_segment'] = '';
        //-------以下为主机私有------
        //得到有效数据
        $pfMsg['valid_data_flag'] = v1_parse_bool_to_flag($params['srcInfo']['valid_data_flag']);
        //得到静默快照
        $pfMsg['silent_snapshot_flag'] = v1_parse_bool_to_flag($params['srcInfo']['silent_snapshot_flag']);
        //得到CBT
        $pfMsg['cbt_flag'] = v1_parse_bool_to_flag($params['srcInfo']['cbt_flag']);
        //得到快照
        $pfMsg['snapshot_flag'] = v1_parse_bool_to_flag($params['srcInfo']['snapshot_flag']);
        //得到传输模式  
        $pfMsg['transport_priority'] = $params['srcInfo']['transport_priority'];
        //得到备份选择的虚拟机以及排除等信息(独有结构体)
        $pfMsg['backup_oss_info'] = $params['srcInfo']['backup_oss_info'];
        $pfMsg['task_uuid'] = $params['taskuuid'];
        $msg = json_encode($pfMsg);
        //发送消息
        //得到备份创建操作码
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $mbResult = $this->mbOSMsg($nodeInfo['node_uuid'], $opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            $logParams = array(
                'task_uuid' => $params['taskuuid'],
                'task_name' => $task_name,
                'task_type' => xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'],
                'module_type' => xphp_get_config('module', 'MODULE_TYPE')['OS'],
                'submodule_type' => 0,
            );
            $this->taskLog($logParams, 'WEB_TASKLOG_DESC_KEY_MODIFY_TASK_SUCCESS', array($task_name));
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }


    
















































}