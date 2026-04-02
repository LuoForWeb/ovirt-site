<?php

namespace app\v1\complete_machine_os\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\JobInfo;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\job\v0\logic\JobInfo as LogicJobInfo;
use app\v1\opcode\OsOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\recovery\v0\logic\RecoveryJobController;
use app\v1\system\v0\logic\Auth;
use app\v1\vm\v0\logic\VmJobInfo;
use Mpdf\Tag\Em;

/**
 * note          操作系统 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class MachineOsBackup extends Backup
{
    /**
     * 得到备份端代理树
     */
    public function getMachineOsBackupTree($params){
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
        if(v1_auth_need_operation()){
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql= v1_auth_get_source_by_type(
                xphp_get_config('resource','RESOURCE_TYPE')['CLIENT'],
				'ba.agent_uuid'
            );
            $sql .= " and ({$resourceUuidSql})";
        };

        //得到所有数据
        $result = $this->dbSelect($sql);
        $info = array();


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
                    // "clickshow" => false,
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

            $auth_flag = true;
            //是否授权
            // $authorization_module = $op['authorization_module'];
            // if(empty($authorization_module)){
            //     $auth_flag = false;
            // }else{
            //     $authorization_module = json_decode($authorization_module,true);
            //     $auth_flag = $authorization_module['os'] ? true:false;
            // }
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

            //判断是否在任务中
            $task_agent_uuid_exist_list = $this->getTaskAgentuuidList($op['agent_uuid']);

            //是否在任务中
            if($task_agent_uuid_exist_list['flag']){
                $agentuuidInTask = true;
            }else{
                $agentuuidInTask = false;
            }
            //如果在任务中名字加粗
            $name = $titleDes;
            if($agentuuidInTask){
                $name = '<span style="font-weight: bold;color:#008000;">'.$titleDes.'</span>';
            }


            //这里判断有4个条件
            //1 是否在任务中 2是否是修改 3是否授权 4是否离线
            //是否是在任务中
            if($task_agent_uuid_exist_list['flag']){
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
                if($task_agent_uuid_exist_list['flag']){
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
                "name" => $name,
                "title" => $titleDes,
                "isParent" => false,
                "uuid" => $op['agent_uuid'],
                "nocheck" => false,
                "type" => 1,
                "icon" => "./img/os/".$op['os_type'].".png",
                // "clickshow" => false,
                "checked" => $checkflag,
                "chkDisabled" => $chkDisabledflag,
                "iconSkin" => $op['os_type'].'logo',
                //操作系统类型
                "os_type" => $op['os_type'],
                //是否是在任务中
                'agentuuidInTask' => $agentuuidInTask, // true 在任务中
                'agentuuidInTaskList' => $task_agent_uuid_exist_list,
                'ip' => $op['ip'],
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
    public function getTaskAgentuuidList($agentUuids){
        $sql = "select task.task_name,task.task_status from bd_task as task 
	            left JOIN cdp_vol_task vol_task on vol_task.task_uuid = task.task_uuid 
	            left JOIN os_list on os_list.task_uuid = task.task_uuid 
                where (task.task_type = ? or task.task_type =? or task.task_type=? or task.task_type = ?) 
                and (vol_task.master_agent_uuid =? or os_list.agent_uuid = ? ) and task.delete_flag = ?";
        $backupTaskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $replactionTaskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'];
        $osBackupTaskType = xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'];
        $osRecoverTaskType = xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY'];
        $delFlag = xphp_get_config('app','FLAG')['UNSET'];
        $data = $this->dbSelect($sql, array($backupTaskType,$replactionTaskType,$osBackupTaskType,$osRecoverTaskType,$agentUuids, $agentUuids,$delFlag));
        $taskList = array(
            'flag' => false,
        );
        if (!empty($data)) {
            $taskList = array(
                'flag' => true,
                'task_name' => $data[0]['task_name'],
            );
        }
        return $taskList;
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
     * @return void|unknown
     */
    public function getDiskTree($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_DISK_AGENT_INFO'),
            'data' => array(),
        );
        //得到agent_uuid
        $agentuuid = $params['agent_uuid'];
        //先查询数据库当前agent的状态,判断当前agent是否在线
        $sql_agent = "select online_flag,ip from bd_agent where agent_uuid = ?";
        $result_agent = $this->dbSelect($sql_agent,array($agentuuid));
        if($result_agent[0]["online_flag"] == 2){
            //如果主机离线
            $resultInfo['success'] = false;
            $resultInfo['data'] = array(
                'ip' => $result_agent[0]["ip"],
                'online_flag' => $result_agent[0]["online_flag"],
            );
            $resultInfo['message'] = xphp_get_lang('UI_JOB_BACKUP_HOST')."'".$result_agent[0]["ip"]."'".xphp_get_lang('WEB_MACHINE_OS_NO_ONLINE');
            return $resultInfo;
        }

        $original_flag = $params['original_flag'] ?? false;
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $agentuuid;
        $msg = json_encode($pfMSg);
        $submodule_type = 1;
        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true,$submodule_type);
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
        $disk_list = $data['disk_list'];

        if($original_flag){
            $resultInfo['data'] = $data;
            return $resultInfo;
        }else{
            //判断disk_list是否是array
            if(!is_array($disk_list)){
                $resultInfo['succcess'] = false;
                $resultInfo['message'] = $operate;
                $resultInfo['code'] = $mbResult['errorCode'];
                return $resultInfo;
            }
            $resultInfo['data'] =$this->processDevices($disk_list);
            return  $resultInfo;
        }
    }



    // 递归函数
    function processDevices(array $data, $pid = 0) {
        $info = array();
        foreach ($data as $device) {
            //默认可以使用勾选框
            $chkDisabled = false;
            $nocheck = false;//默认有勾选框
            $checked = true; //默认被勾选


            //获取挂载路径
            $mount_point = $device['mount_point'];
            $mount_pointStr = "(".$mount_point.")";
            //获取总大小
            $total_size = intval($device['total_size']);
            //判断是磁盘还是非磁盘,只要part_table_type存在,不管是否为空都判定为磁盘
            if(isset($device['part_table_type'])){
                //如果为磁盘
                $nameStr = "(". xphp_get_lang('UI_STORAGE_TOTAL_SIZE').":".v1_calsize($total_size, true).")";

            }else{
                //如果不是磁盘
                //获取已使用大小
                $used_size = intval($device['used_size']);
                //获取可用空间大小
                $usable_size = $total_size - $used_size;
                $nameStr = "(".xphp_get_lang('UI_STORAGE_TOTAL_SIZE').":".v1_calsize($total_size, true) ." , ".xphp_get_lang('WEB_MACHINE_OS_AVAILABLE').":".v1_calsize($usable_size, true).")";
            }




            $end_node = 2;
            if(!isset($device['sub_devices']) || count($device['sub_devices']) == 0){
                //如果为0表示是最后一层级
                $end_node = 1;
            }
            //获取最终的name显示
            if(empty($mount_point)){
                $name = $device['display_name'].'<span class="backupSource_tree_size"> '.$nameStr.'</span>';
            }else{
                $name = $device['display_name'].'<span class="backupSource_tree_mount"> '.$mount_pointStr.'</span>'.'<span class="backupSource_tree_size"> '.$nameStr.'</span>';
            }

             //如果是可移动设备则增加字样
             if($device['removable_flag'] == 1){
                //如果是可移动磁盘则默认不选中
                // $nocheck = true;//不选中
                $checked = false; //不选中
                $name .= '<i title='.xphp_get_lang('WEB_OS_REMOVE_DEVICE').' class="viconfont vicon-yidongyingpan"></i>';
                // $nameStr .= "(".xphp_get_lang('WEB_OS_REMOVE_DEVICE').")";
            }

            //如果是加密磁盘或分区,则不勾选置灰且无法选中
            if($device['is_bitLocker'] == 1){
                $nocheck = true;//不选中
                $chkDisabled = true; //不可使用勾选框
                //给name添加一个锁的标签
                $name .= '<i title='.xphp_get_lang('WEB_MACHINE_OS_ENCRYPTED').' class="viconfont vicon-ge_lock"></i>';
            }

            //这里需要判断是否是逻辑分区还是扩展分区
            //是否是逻辑分区
            //与type做 按位与运算
            $logic_flag =xphp_get_config('machine_os_config','VOLUME_TYPE')['BD_LOGIC_VOLUME'] == (xphp_get_config('machine_os_config','VOLUME_TYPE')['BD_LOGIC_VOLUME'] & intval($device['type']));
            if($logic_flag){
                $name .= '<i title='.xphp_get_lang('WEB_MACHINE_LOGIC_VOL').' class="viconfont vicon-luojifenqu"></i>';
            }
            //判断扩展分区
            $extend_flag =xphp_get_config('machine_os_config','VOLUME_TYPE')['BD_EXTEND_VOLUME'] == (xphp_get_config('machine_os_config','VOLUME_TYPE')['BD_EXTEND_VOLUME'] & intval($device['type']));
            if($extend_flag){
                $name .= '<i title='.xphp_get_lang('WEB_MACHINE_EXTEND_VOL').' class="viconfont vicon-kuozhanfenqu"></i>';
                //如果是扩展分区则不显示勾选框
                $nocheck = true;
            }
            //如果是磁盘层并且is_boot为1
            if($device['is_boot'] == 1 && isset($device['part_table_type'])){
                //给name添加一个引导磁盘的的标签
                $name .= '<i title='.xphp_get_lang('WEB_MACHINE_OS_BOOT').' class="viconfont vicon-boot"></i>';
            }
            //获取磁盘阵列
            //如果磁盘阵列不为空
            $disk_array = "";
            if(!empty($device['disk_extra_info']) && !empty($device['disk_extra_info']['display_info'])){
                $disk_array = $device['disk_extra_info']['display_info'];
            }
            if(!empty($disk_array)){
                $name .= '<i title="'.$disk_array.'" class="viconfont vicon-cipanzhenlie"></i>';
            }

            //处理逻辑
            $info[] = array(
                //获取唯一uuid
                'id' => $device['dev_node_uuid'],
                'dev_node_uuid' => $device['dev_node_uuid'],
                //获取磁盘uuid
                'dev_uuid' => $device['dev_uuid'],
                //挂载点
                'mount_point' => $device['mount_point'],
                //path路径
                'path' => $device['path'],
                //fstype
                'fs_type' => $device['fs_type'],
                //获取父级id
                'pId' => $pid,
                //获取节点名称
                'name' => $name,
                //获取title
                'title' => $name,
                //获取磁盘名称
                'dev_name' => $device['display_name'],
                //是否展开
                'open' => true,
                //是否是系统盘
                'is_boot' => $device['is_boot'],
                //是否有勾选框
                'nocheck' => $nocheck,
                //类型,如果为1表示磁盘,2表示非磁盘
                'type' => isset($device['part_table_type']) ? 1 : 2,
                //结构类型
                'struct_type' => $device['struct_type'],
                //获取操作系统磁盘类型
                'dev_type' => $device['type'],
                //额外的name信息
                'extra_name' => $device['disk_extra_info'] ? $device['disk_extra_info']['display_info'] : '',
                //是否是磁盘类型,如果为1表示磁盘,2表示非磁盘
                'first_node' => isset($device['part_table_type']) ? 1 : 2,
                //图标
                'iconSkin' => isset($device['part_table_type']) ? 'tree_disk' : 'tree_volume' ,
                'iconClass' => isset($device['part_table_type']) ? 'vicon-cipan1 ztree_icon_color' : 'vicon-fenqu ztree_icon_color' ,
                //true表示是磁盘
                'is_disk_flag' => isset($device['part_table_type']) ? true : false,
                //是否是最后一个节点,1表示是最后一个节点,2表示不是最后一个节点
                'end_node' => $end_node,
                //true表示是卷或分区
                'is_volume_flag' => $end_node == 1 ? true : false,
                //是否是可移动磁盘
                'removable_flag' => $device['removable_flag'],
                //获取总大小
                'total_size' => intval($device['total_size']),
                //获取已使用大小
                'used_size' => isset($device['used_size']) ? intval($device['used_size']) : 0,
                //是否可以使用勾选框
                'chkDisabled' => $chkDisabled,
                'is_system_disk_flag' => $device['is_system_disk_flag'],
                'checked' => $checked,
                //磁盘阵列
                'disk_array' => $disk_array,
            );

            // 如果当前设备有子设备并且子设备数组非空，则递归处理子设备
            if (isset($device['sub_devices']) && count($device['sub_devices']) > 0) {
                // 当递归调用时，增加层级，并传递当前设备的id作为子设备的父id
                $sub_info = $this->processDevices($device['sub_devices'], $device['dev_node_uuid']);
                $info = array_merge($info, $sub_info);
            }
        }
        return $info;
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
     * 得到主机任务名(备份)
     * @param unknown $params
     */
    public function getMachineOsTaskName(){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_MACHINE_OS_GET_BACKUP_JOB_NAME'),
            'data' => array(),
        );
        $taskName = xphp_get_lang('WEB_MACHINE_OS_BACKUP_JOB_NAME');
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
     * 创建整机定时备份任务
     */
    public function createBackupJob($params){
        //判断是否过期
        if(empty($params['task_uuid'])){
            //task_uuid为空，创建任务
            $opName = 'BD_TASK_OP_BACKUP_CREATE';
        }else{
            //修改任务
            $pfMsg['task_uuid']  = $params['task_uuid'];
            $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        }
        $pfOpcode  = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        //
        $auth = $this->checkAuthNumOs($params);
        if(!$auth){
            return $this->muOpResult(false, xphp_get_lang('WEB_MACHINE_OS_LICENSE_CHECK'), xphp_get_lang('WEB_MACHINE_OS_LICENSE_SHORTAGE'));
        }
        $pfMsg = array();
        //获取任务名称
        $taskname = htmlspecialchars_decode($params['task_name']);
        //获取模块类型
        $moduletype = xphp_get_config('module','MODULE_TYPE')['OS'];
        //获取策略组uuid
        $strategygroupuuid = 0;
        //组合通用策略---------------------------
        //组合时间策略
        $timestrategylist = $this->newGroupBackupTimeList($params['strategyInfo']['time']);
        //组合存储策略
        //给定默认块大小
        $params['strategyInfo']['store']['storeInfo']['blocksize'] = 1048576;
        $storagestrategy = $this->groupStorageStrategy($params['strategyInfo']['store']['storeInfo']);
        //组合保留策略
        $reserverstrategy = $this->groupReserverStrategy($params['strategyInfo']['reserve']['reserveInfo']);
        //组合传输策略-----------------------------
        $transportstrategy = $this->groupTransportStrategy($params['transfer_strategy']);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['node_info']);
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $taskname,
            $moduletype,
            $timestrategylist,
            $reserverstrategy,
            $transportstrategy,
            $storagestrategy,
            $nodeInfo,
            $params['strategyInfo']['time']['type']
        );
        //获取任务类型
        $pfMsg['task_type'] = xphp_get_config('task','TASKTYPE')['OS_BACKUP'];
        //组合安全策略-----------------------------
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_config_strategy'],$params['node_info']['storage_uuid']);
        //组合限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['strategyInfo']['speedlimit']);
        //组合高级策略-----------------------------
        $pfMsg['advance_setting'] = array();
        $pfMsg['advance_setting']['full_backup'] = v1_parse_bool_to_flag($params['advanced_strategy']['full_backup']);
        $pfMsg['advance_setting']['silent_snapshot_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['silent_snapshot_flag']);
        $pfMsg['advance_setting']['cbt_flag'] = v1_parse_bool_to_flag($params['advanced_strategy']['cbt_flag']);
        $pfMsg['advance_setting']['skip_bad_block'] = v1_parse_bool_to_flag($params['advanced_strategy']['skip_bad_block']);
        //合并模式
        $pfMsg['merge_mode'] = intval($params['advanced_strategy']['combin_method']);
        //组合重试策略
        $pfMsg['retry_strategy'] = $params['retry_strategy'];
        //组合备份源信息-----------------------------
        $pfMsg['backup_oss_info'] = $params['backup_oss_info'];
        //其他
        //组合传输线程
        $pfMsg['thread_num'] = intval($params['thread_num']);
        //过载保护
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);

        // $pfMsg['storage_uuid'] =$params['node_info']['storage_uuid'];

        //是否自动寻找存储资源
        $pfMsg['auto_find_sr_flag'] = 2;
        //备份服务器IP
        $pfMsg['backup_server_ip'] = "";
        //传输网段
        $pfMsg['transport_ip_segment'] = "";


        //其他页面没有的传默认值
        $pfMsg['transport_priority'] = 1;
        $pfMsg['serial_snapshot_flag'] = 2;
        //发送消息
        //得到备份创建操作码
        if(empty($params['task_uuid'])){
            //task_uuid为空，创建任务
            $opName = 'BD_TASK_OP_BACKUP_CREATE';
        }else{
            //修改任务
            $pfMsg['task_uuid']  = $params['task_uuid'];
            $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        }

        $msg = json_encode($pfMsg);
        $submodule_type = 1; //0和2表示以前老版本的操作系统备份   1表示定时整机的
        // dump($msg);
        $mbResult = $this->mbOSMsg($params['node_info']['node_uuid'],$opName,$msg,false, false, $submodule_type);
        $result = $mbResult['result'];

        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }



    }

    //检查授权数量, 这个函数值检查整机定时和操作系统的
    public function checkAuthNumOs($params){
        //获取授权
        $authHandler = new Auth;
        $auth = $authHandler->getLicenceInfo(array('type'=>'a','module' => 'os'));
        $AUTH_SUCCESS = false;
        if($auth["auth_type"] == 1){
            //如果数量授权
            //得到剩余数量
            $remaining_quantity = intval($auth["total"]) - intval($auth["used"]);
            if(!empty($params['task_uuid'])){
                //修改的话授权数量+1
                $remaining_quantity =  $remaining_quantity + 1;
            }
            if($remaining_quantity > 0){
                $AUTH_SUCCESS = true;
            }
        }else{
            //容量授权则不判断
            $AUTH_SUCCESS = true;
        }
        return $AUTH_SUCCESS;
    }





    /**
     * 获取整机定时备份任务数据,修改使用
     */
    public function getJobInfo($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_MACHINE_OS_GET_BACKUP_JOB_INFO'),
            'data' => array(),
        );
        //获取备份源信息
        $task_uuid = $params['job_uuid'];
        $sql = "select 
        bt.node_uuid,bt.task_name, bt.storage_uuid, bt.strategy_id, bt.thread_num, bt.worm_flag, bt.virus_scan_flag,bt.integrity_check_flag, bt.ignore_resource_limiting_flag, 
        bt.node_pool_uuid, bt.storage_pool_uuid,  
        bsr.storage_type ,
        bss.compressed_flag, bss.encrypted_flag, bss.password_auto_flag, bss.password, bss.encrypt_method as bss_encrypt_method, bss.compress_method, bss.deduplication_flag,bss.redundant_data_proportiont, bss.data_container_size, 
        brs.strategy_type, brs.number, brs.strategy_mode,
        bts.encrypt_flag, bts.encrypt_method as bts_encrypt_method,bts.network_pool_uuid, bts.network_uuid, bts.reconnect_times,bts.compress_flag as bts_compress_flag, bts.compress_method as bts_compress_method,
        btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,btsc.backup_integrity_check_inc_error_policy, 
        ot.skip_bad_track_flag, ot.cbt_flag, ot.full_backup , ot.original_compress_flag, ot.original_compress_method , ot.silent_snapshot_flag 
        from bd_task bt 
        left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid
        left join bd_storage_strategy bss on bt.strategy_id = bss.strategy_id
        left join bd_reserved_strategy brs on bt.strategy_id = brs.strategy_id
        left join bd_transport_strategy bts on bt.strategy_id = bts.strategy_id
        left join bd_task_safe_config btsc on bt.task_uuid = btsc.task_uuid
        left join os_task ot on bt.task_uuid = ot.task_uuid
        where bt.task_uuid = ?
        ";
        $data = $this->dbSelect($sql,array($task_uuid));
        $ExchangeJobInfo = new ExchangeJobInfo;


        $info = array();
        if($data){
            $info = array(
                //任务UUID
                'taskuuid' => $task_uuid,
                'task_name' => $data[0]['task_name'],
                //获取备份源信息
                'backup_oss_info' => $this->getBackupOssInfo($task_uuid),
                //获取目标节点目标存储
                'node' => array(
                    //获取节点uuid
                    'node_uuid' => $data[0]['node_uuid'],
                    //获取节点资源池
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    //获取存储uuid
                    'storage_uuid' => $data[0]['storage_uuid'],
                    //获取存储资源池
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    //获取存储类型
                    'storage_pool_type' => $data[0]['storage_type']
                ),
                //获取时间策略
                'time_strategy' => $this->getTimeStrategyInfo($data[0]['strategy_id']),
                //获取限速策略
                'speed_strategy' => $this->getSpeedStrategy($task_uuid),
                //获取存储策略
                'storage_strategy' => array(
                    //获取压缩存储
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    //获取数据加密
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    //获取自动生成密码
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    //获取密码
                    'password' => base64_encode(v1_pt_pass_decrypt($data[0]['password'])),
                    //获取压缩等级
                    'compress_method' => intval($data[0]['compress_method']),
                    //获取加密算法
                    'encrypt_method' => intval($data[0]['bss_encrypt_method']),
                    //重复数据删除
                    'deduplication' => v1_parse_flag_to_bool(intval($data[0]['deduplication_flag'])),
                    //数据分片大小
                    'data_container_size' => intval($data[0]['data_container_size']),
                    //合并冗余数据比例
                    'redundant_data_proportiont' => intval($data[0]['redundant_data_proportiont']),
                ),
                //获取保留策略
                'reserve_strategy' => array(
                    'type' => intval($data[0]['strategy_type']),
                    'strategy_mode' => intval($data[0]['strategy_mode']),
                    'number' => intval($data[0]['number']),
                ),
                //获取传输策略
                'transfer_strategy' => array(
                    //加密传输
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypt_flag']),
                    //加密算法
                    'encrypt_method' => intval($data[0]['bts_encrypt_method']),
                    //传输网络
                    'network' => $data[0]['network_uuid'],
                    //网络资源池
                    'network_pool_uuid' =>$data[0]['network_pool_uuid'],
                    //传输线程
                    'thread_num' => intval($data[0]['thread_num']),
                    //重连次数
                    'reconnect_times' => intval($data[0]['reconnect_times']),
                    //重连间隔
                    'reconnect_interval' => intval($data[0]['reconnect_interval']),
                    //源端压缩
                    'original_compress_flag' => v1_parse_flag_to_bool($data[0]['bts_compress_flag']),
                    //源端压缩等级
                    'original_compress_method' => intval($data[0]['bts_compress_method']),

                ),
                //获取安全策略
                'safe_strategy' => array(
                    //获取worm开关
                    'worm_flag' => v1_parse_flag_to_bool($data[0]['worm_flag']),
                    //获取worm保护期限
                    'worm_protection_time' => intval($data[0]['worm_protection_time']),
                    //获取病毒是否开关
                    'virus_scan_flag' => v1_parse_flag_to_bool($data[0]['virus_scan_flag']),
                    //获取病毒检测配置
                    'virus_scan_config_list' => json_decode($data[0]['virus_scan_config_list'], true),
                    //获取完整性效验开关
                    'integrity_check_flag' => v1_parse_flag_to_bool($data[0]['integrity_check_flag']),
                    //获取完整性校验数据
                    'integrity_info' => array(
                        //获取效验周期
                        'integrity_check_strategy' => intval($data[0]['integrity_check_strategy']),
                        //获取完全备份点异常
                        'backup_integrity_check_full_error_policy' => intval($data[0]['backup_integrity_check_full_error_policy']),
                        //获取其他备份点异常
                        'backup_integrity_check_inc_error_policy' => intval($data[0]['backup_integrity_check_inc_error_policy']),
                    ),
                ),
                //获取高级配置
                'high_config' =>array(
                    //获取逐扇区备份
                    'full_backup' => v1_parse_flag_to_bool($data[0]['full_backup']),
                    //获取静默快照
                    'silent_snapshot_flag' => v1_parse_flag_to_bool($data[0]['silent_snapshot_flag']),
                    //获取CBT
                    'cbt_flag' => v1_parse_flag_to_bool($data[0]['cbt_flag']),
                    //获取跳过坏快备份
                    'skip_bad_track_flag' => v1_parse_flag_to_bool($data[0]['skip_bad_track_flag']),
                    //获取合并模式
                ),
                //重试策略
                'retry_strategy' => $ExchangeJobInfo->getRetryStrategy($task_uuid),
                //忽略节点限制
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag']),
                //合并模式
                'merge_mode' =>"",

            );
        }


        $resultInfo['data'] = $info;
        return $resultInfo;
    }







    /**
     * 获取整个任务备份源信息
     */
    public function getBackupOssInfo($task_uuid){
        $resultInfo = array();
        //获取备份源信息
        $sql = "select ol.exclude_devices_list,ol.agent_uuid,ol.backup_mode ,ol.disk_struct_backup_mode,
        ba.agent_name , ol.before_task_script, ol.after_task_script 
        from os_list ol
        left join bd_agent ba on ol.agent_uuid = ba.agent_uuid 
        where ol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        if(!empty($data)){
            foreach ($data as $each) {
                //获取主机uuid
                $exclude_devices_list = "";
                //获取备份信息
                if(!empty($each['exclude_devices_list'])){
                    $exclude_devices_list = json_decode($each['exclude_devices_list'],true);
                }
                $resultInfo[] = array(
                    'agent_uuid' => $each['agent_uuid'],
                    'agent_name' => $each['agent_name'] ?? 'Unknown',
                    'disk_struct_backup_mode' => v1_parse_flag_to_bool($each['disk_struct_backup_mode']),
                    'exclude_devices_list' => $exclude_devices_list,
                    'before_task_script' => json_decode($each['before_task_script'],true) ?? [],
                    'after_task_script' => json_decode($each['after_task_script'],true) ?? [],
                );
            }
        }
        return $resultInfo;
    }







    /**
     * 获取恢复目标卷配置,瞬时恢复,跨平台恢复都在用
     */
    public function getRecoverTargetInfo($params){
        // $params['origin_timepoint_uuid'] = "cff2632e-295d-4686-a991-409064cb5818";
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_MACHINE_OS_GET_RECOVERT_HOST_INFO'),
            'data' => array(),
        );
        // //假数据
        // $resultInfo['data'] = $this->fakeData();
        // return $resultInfo;

        //获取类型
        $type = intval($params['type']);
        //获取时间点信息
        $origin_timepoint_uuid = $params['origin_timepoint_uuid'];
        //获取目标主机uuid
        $target_agent_uuid = $params['target_agent_uuid'];

        //根据类型累判断调哪个接口以及返回的数据
        switch ($type) {
            case 1:
                //为定时操作系统恢复
                $resultInfo['data'] = $this->getRecoverTargetOfOS($params);
                break;
            case 2:
                //为定时虚拟机恢复
                $resultInfo['data'] = $this->getRecoverTargetOfVM($params);
                break;
            case 3:
                //为实时备份,实时的origin_data是从前端获取的,所以这个值为空
                $resultInfo['data'] = $this->getBackupTargetOfCmCDP($params);
                break;
            case 4:
                //复制备机配置origin_data是从前端获取的,所以这个值为空
                $resultInfo['data'] = $this->getCopyTargetOfCmCDP($params);
                break;

            case 5:
                //整机接管
            case 6:
                //为实时恢复, 整机,数据卷恢复/整机挂载接管
                //整机、数据卷恢复/整机挂载接管
                $resultInfo['data'] = $this->getRecoverTargetOfCmCDP($params);
                break;
            case 7:
                //实时回切,需要根据已经有的磁盘来排除其他数据
                $resultInfo['data'] = $this->getRealtimeSwitchbackCDP($params);
                break;
            default:
                $resultInfo['success'] = false;
                $resultInfo['message'] = xphp_get_lang('WEB_MACHINE_OS_PLUGIN_TYPE_ERROR');
                break;
        };


        return $resultInfo;
    }



    public function getRealtimeSwitchbackCDP($params){
        //获取时间点信息
        $origin_timepoint_uuid = $params['origin_timepoint_uuid'];
        //获取目标主机uuid
        $target_agent_uuid = $params['target_agent_uuid'];
        //需要匹配数据集
        $match_dev_uuid_list = $params['match_dev_uuid_list'];
        $resultInfo = array(
            "origin_data" => array(),
            "target_data" => array()
        );
        //获取origin_data-------------
        //未处理的数据
        $sql = "select master_agent_detail from cdp_vol_backup_agent where timepoint_uuid = ?";
        $data_origin = $this->dbSelect($sql, array($origin_timepoint_uuid));
        $cdp_config = json_decode($data_origin[0]['master_agent_detail'],true);
        //获取到未处理的数据格式后需要把特定磁盘以及磁盘下面的分区筛选出来
        //先获取所有数据
        $all_disk_list = $cdp_config['all_disk_list'];
        //该数据为一个数组,需要遍历数组删除掉对应的元素
        foreach ($all_disk_list as $key => $value) {
            //判断是否匹配
            if(!in_array($value['dev_uuid'],$match_dev_uuid_list)){
                //匹配成功,删除该元素
                unset($all_disk_list[$key]);
            }
        };
        $cdp_config['all_disk_list'] = $all_disk_list;
        //这里如果exclude_device_list没有值需要传空数组
        //  $cdp_config['exclude_device_list'] = array();
        $resultInfo['origin_data'] = $this->getBackupDevices($cdp_config,$params);
        //获取target_data-------------
        //获取恢复目标是否要显示,如果不显示就不调用目标端的数据了
        if(!$params['target_visible_flag']){
            return $resultInfo;
        }
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $target_agent_uuid;
        $submodule_type = 1; //0和2表示以前老版本的操作系统备份   1表示定时整机的
        $msg = json_encode($pfMSg);
        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true,$submodule_type);
        $result = $mbResult['result'];
        $data = $mbResult['msg'];
        $resultInfo['target_data'] = $this->diskTransitionList($data['disk_list']);
        if($result){
            return $resultInfo;
        }else{
            exit($this->muOpResult($result, $opName, $msg = '', 'warning', $mbResult['errorCode']));
        }


    }






    /**
     * 获取实时恢复数据
     */
    public function getRecoverTargetOfCmCDP($params){
        //获取时间点信息
        $origin_timepoint_uuid = $params['origin_timepoint_uuid'];
        //获取目标主机uuid
        $target_agent_uuid = $params['target_agent_uuid'];
        $resultInfo = array(
            "origin_data" => array(),
            "target_data" => array(),
        );
        //获取origin_data-------------
        $sql = "select master_agent_detail from cdp_vol_backup_agent where timepoint_uuid = ?";
        $data_origin = $this->dbSelect($sql, array($origin_timepoint_uuid));
        $cdp_config = json_decode($data_origin[0]['master_agent_detail'],true);
        $resultInfo['origin_data'] = $this->getBackupDevices($cdp_config,$params);
        //获取target_data-------------
        //获取恢复目标是否要显示,如果不显示就不调用目标端的数据了
        if(!$params['target_visible_flag']){
            return $resultInfo;
        }
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $target_agent_uuid;
        $submodule_type = 1; //0和2表示以前老版本的操作系统备份   1表示定时整机的
        $msg = json_encode($pfMSg);
        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true,$submodule_type);
        $result = $mbResult['result'];
        $data = $mbResult['msg'];
        // 增加来源，因为迁移不能到客户端的系统盘，需要屏蔽返回
        $resultInfo['target_data'] = $this->diskTransitionList(
            $data['disk_list'],
            0,
            [],
            !empty($params['motion_flag'])
        );
        if($result){
            return $resultInfo;
        }else{
            exit($this->muOpResult(false, xphp_get_lang('WEB_MACHINE_OS_GET_DISK_DATA'), $data, 'warning'));
        }


    }


    /**
     * 获取实时备份数据
     */
    public function getBackupTargetOfCmCDP($params){
        //获取目标主机uuid
        $target_agent_uuid = $params['target_agent_uuid'];
        $resultInfo = array(
            "origin_data" => array(),
            "target_data" => array(),
            'mount_point_list' => array()
        );
        //获取target_data-------------
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $target_agent_uuid;
        $msg = json_encode($pfMSg);
        $submodule_type = 1; //0和2表示以前老版本的操作系统备份   1表示定时整机的
        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true,$submodule_type);
        $result = $mbResult['result'];
        $data = $mbResult['msg'];
        $resultInfo['target_data'] = $this->diskTransitionList($data['disk_list']);
        $resultInfo['backup_target_data'] = $resultInfo['target_data'];
        $resultInfo['mount_point_list'] = $this->getMountPointList($resultInfo['target_data']);
        //这里需要隐藏下拉框的所有选项
        $resultInfo['target_data'] = array();
        if($result){
            return $resultInfo;
        }else{
            exit($this->muOpResult(false,xphp_get_lang('WEB_MACHINE_OS_GET_DISK_DATA'), $data, 'warning'));
        }

    }

    /**
     * 获取实时备份数据
     */
    public function getCopyTargetOfCmCDP($params){
        //获取目标主机uuid
        $target_agent_uuid = $params['target_agent_uuid'];
        $resultInfo = array(
            "origin_data" => array(),
            "target_data" => array(),
        );
        //获取target_data-------------
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $target_agent_uuid;
        $msg = json_encode($pfMSg);
        $submodule_type = 1; //0和2表示以前老版本的操作系统备份   1表示定时整机的
        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true,$submodule_type);
        $result = $mbResult['result'];
        $data = $mbResult['msg'];
        $resultInfo['target_data'] = $this->diskTransitionList($data['disk_list']);
        if($result){
            return $resultInfo;
        }else{
            exit($this->muOpResult(false,xphp_get_lang('WEB_MACHINE_OS_GET_DISK_DATA'), $data, 'warning'));
        }

    }






    public function getMountPointList($params){
        $result_list = array();
        if(empty($params)){
            return array();
        }
        foreach ($params as $key => $value) {
            if($value['is_volume_flag'] && $value['mount_point'] != ""){
                //获取分区每一个的mount_point
                $result_list[] = $value['mount_point'];
            }
        }
        return $result_list;
    }



    /**
     * 获取操作系统恢复数据
     */
    public function getRecoverTargetOfOS($params){
        //获取时间点信息
        $origin_timepoint_uuid = $params['origin_timepoint_uuid'];
        //获取目标主机uuid
        $target_agent_uuid = $params['target_agent_uuid'];
        $resultInfo = array(
            "origin_data" => array(),
            "target_data" => array()
        );
        //获取origin_data-------------
        $sql = "select os_config,disk_struct_backup_mode from os_backup_timepoint where timepoint_uuid = ?";
        $data_origin = $this->dbSelect($sql, array($origin_timepoint_uuid));
        $os_config = json_decode($data_origin[0]['os_config'],true);
        if (!empty($params['task_uuid'])) {
            // 如果有携带任务uuid，那么就是迁移任务，需要去瞬时恢复任务查询选择的磁盘uuid，然后过滤真是的迁移机器需要的磁盘uuid信息
            $extension = $this->dbSelect(
                'select extension_info from bd_instant_recovery_task where task_uuid = ?',
                [$params['task_uuid']]
            );
            $devInfo = json_decode($extension[0]['extension_info'], true);
            if (!empty($devInfo[0]['inst_rev_devices'])) {
                $newDisk = [];
                foreach ($os_config['all_disk_list'] as $item) {
                    if (in_array($item['dev_uuid'], $devInfo[0]['inst_rev_devices'])) {
                        $newDisk[] = $item;
                    }
                }
                $os_config['all_disk_list'] = $newDisk;
            }
        }
        $resultInfo['origin_data'] = $this->getBackupDevices($os_config,$params, $data_origin[0]['disk_struct_backup_mode']);
        //获取target_data-------------
        //获取恢复目标是否要显示,如果不显示就不调用目标端的数据了
        if(!$params['target_visible_flag']){
            return $resultInfo;
        }
        // 如果是整机到整机的瞬时恢复
        if (!empty($params['is_instant_machine'])) {
            $resultInfo['target_data'] = [];
            return $resultInfo;
        }
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $target_agent_uuid;
        $msg = json_encode($pfMSg);
        $submodule_type = 1;
        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true,$submodule_type);
        $result = $mbResult['result'];
        $data = $mbResult['msg'];
        // 增加来源，因为迁移不能到客户端的系统盘，需要屏蔽返回
        $resultInfo['target_data'] = $this->diskTransitionList(
            $data['disk_list'],
            0,
            [],
            !empty($params['motion_flag'])
        );
        if($result){
            return $resultInfo;
        }else{
            exit($this->muOpResult(false,xphp_get_lang('WEB_MACHINE_OS_GET_DISK_DATA'), $data, 'warning'));
        }
    }


    /**
     * 获取虚拟机恢复数据
     */
    public function getRecoverTargetOfVM($params){
        //获取时间点信息
        $origin_timepoint_uuid = $params['origin_timepoint_uuid'];
        //获取目标主机uuid
        $target_agent_uuid = $params['target_agent_uuid'];
        $resultInfo = array(
            "origin_data" => array(),
            "target_data" => array()
        );
        //获取origin_data-------------
        $resultInfo['origin_data'] = $this->getBackupVM($origin_timepoint_uuid, $params);
        //获取target_data-------------
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_SCAN_DISK';
        $pfMSg['agent_uuid'] = $target_agent_uuid;
        $msg = json_encode($pfMSg);
        $submodule_type = 1;
        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true,$submodule_type);
        $result = $mbResult['result'];
        $data = $mbResult['msg'];
        // 增加来源，因为迁移不能到客户端的系统盘，需要屏蔽返回
        $resultInfo['target_data'] = $this->diskTransitionList(
            $data['disk_list'],
            0,
            [],
            !empty($params['motion_flag'])
        );
        if($result){
            return $resultInfo;
        }else{
            exit($this->muOpResult(false, xphp_get_lang('WEB_MACHINE_OS_GET_DISK_DATA'), $data, 'warning'));
        }
    }


    /**
     * 将磁盘的数据转换成列表,数据源为已经备份的数据os_config的数据
     * $os_config=array{'all_disk_list':'xxx','exclude_device_list':'xxxx'}
     * $params = array{'nocheck':'xxx',  //是否显示勾选框,true表示不显示
     * 'recover_type':'true'  //是否展开 true表示不展开
     * },
     */
    public function getBackupDevices($os_config,$params = array(),$disk_struct_backup_mode = 0){
        //获取该系统的所有磁盘信息
        $all_disk_list = $os_config['all_disk_list'];
        //获取排除的磁盘信息
        $exclude_devices_list = $os_config['exclude_device_list'];

        //获取磁盘列表
        $all_devices_list = $this->diskTransitionList($all_disk_list,0,$params);

        //排除掉$exclude_devices_list

        // 提取需要排除的 ID
        $exclude_ids = [];
        foreach ($exclude_devices_list as $item) {
            $exclude_ids[] = $item['dev_node_uuid'];
        }
        // 初始化一个新的数组来存储过滤后的磁盘列表
        $filtered_devices_list = [];
        // 遍历 $all_devices_list
        foreach ($all_devices_list as $each_device) {
            // 检查 $device_id 是否存在于 $exclude_devices_list 中
            if (!in_array($each_device['dev_node_uuid'], $exclude_ids)) {
                // 如果不存在，则添加到 filtered_devices_list 中
                $each_device['disk_struct_backup_mode'] = $disk_struct_backup_mode;
                // 如果是整机到整机的瞬时恢复
                if (!empty($params['is_instant_machine']) && strpos('[SWAP]', $each_device['mount_point']) !== false) {
                    $each_device['end_node'] = 2;
                }
                $filtered_devices_list[] = $each_device;
            }
        }
        return $filtered_devices_list;
    }

    /**
     * 将接口获取的原始数据转换为列表数据
     * 递归函数
     * $motionFlag 标记是否迁移到在线客户端
     */
    public function diskTransitionList($data, $pid = 0, $params = array(), $motionFlag = false){
        $info = array();
        foreach ($data as $device) {
            //是否有勾选框
            $nocheck = false;
            $chkDisabled = false; //默认表示可以使用
            $open = true; //默认打开
            $checked = true;
            $end_node = 2;
            if(!isset($device['sub_devices']) || count($device['sub_devices']) == 0){
                //如果为0表示是最后一层级
                $end_node = 1;
            }
            $showMount = false;
            if (!empty($params)) {
                //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
                //获取前端的是否不要勾选框
                $nocheck = empty($params['nocheck']) ? false : $params['nocheck'];
                //获取前端的是否要禁用
                $chkDisabled = empty($params['chkDisabled']) ? false : $params['chkDisabled'];
                if (!empty($params['instant_flag'])) {
                    if ($pid != 0) {
                        // 瞬时恢复，只有磁盘有勾选框
                        $nocheck = true;
                    } else {
                        // 给每个磁盘的所有线给一个main相同的id
                        $params['main_pid'] = $device['dev_uuid'];
                    }
                    // linux无论是磁盘还是它的子设备，只要它fs_type不为0且不是swap并且fs_uuid不为空且它没有子设备了就显示挂载框框,
                    if (!empty($device['fs_type']) && $device['fs_type'] != 14 && !empty($device['fs_uuid']) && $end_node == 1) {
                        $showMount = true;
                    }
                }
            }

            $first_node = isset($device['part_table_type']) ? 1 : 2;

            if($first_node == 1 && !empty($params)){
                //获取前端是否是卷备份, 如果是只展示在磁盘层 则默认为收拢状态
                $recover_type = $params['recover_type'];
                if($recover_type){
                    $open = false;
                    //如果整机恢复的时候磁盘的is_boot为1就强制勾选
                    if($device['is_boot'] == 1){
                        $chkDisabled = true;
                    }
                }
            }

            //获取该磁盘是否是系统磁盘
            //该字段只有磁盘有,判断磁盘是否是系统磁盘(磁盘下面的分区是否有系统分区)
            $is_system_disk_flag = empty($device['is_system_disk_flag']) ? 2 : intval($device['is_system_disk_flag']);


            if (!empty($motionFlag) && $is_system_disk_flag == 1) {
                // 迁移到在线客户端不能选择系统盘
                continue;
            }

            //获取挂载路径
            $mount_point = $device['mount_point'];
            $mount_pointStr = "(".$mount_point.")";
            //如果是可移动设备则增加字样
            if($device['removable_flag'] == 1){
                $mount_pointStr .= "(".xphp_get_lang('WEB_MACHINE_OS_REMOVABLE_DEVICE').")";
            }
            if($first_node ==1 && $is_system_disk_flag ==1){
                //如果是磁盘层并且是系统磁盘则给出提示文字
                $mount_pointStr .= "(".xphp_get_lang('WEB_MACHINE_OS_SYSTEM_DISK').")";
            }
            //获取最终的name显示
            if(!empty($mount_point)){
                $name = $device['display_name'].'<span class="backupSource_tree_mount"> '.$mount_pointStr.'</span>';
            }else{
                $name = $device['display_name'];
                //如果是可移动设备则增加字样
                if($device['removable_flag'] == 1){
                    $name .= '<i title='.xphp_get_lang('WEB_MACHINE_OS_REMOVABLE_DEVICE').' class="viconfont vicon-yidongyingpan"></i>';
                    // $name .= "(".xphp_get_lang('WEB_MACHINE_OS_REMOVABLE_DEVICE').")";
                }
                if($first_node ==1 && $is_system_disk_flag ==1){
                    //如果是磁盘层并且是系统磁盘则给出提示文字
                    $name .= '<i title='.xphp_get_lang('WEB_MACHINE_OS_SYSTEM_DISK').' class="viconfont vicon-xitong"></i>';
                    // $name .= "(".xphp_get_lang('WEB_MACHINE_OS_SYSTEM_DISK').")";
                }
            }

            //这里需要判断是否是逻辑分区还是扩展分区
            //是否是逻辑分区
            //与type做 按位与运算
            $logic_flag =xphp_get_config('machine_os_config','VOLUME_TYPE')['BD_LOGIC_VOLUME'] == (xphp_get_config('machine_os_config','VOLUME_TYPE')['BD_LOGIC_VOLUME'] & intval($device['type']));
            if($logic_flag){
                $name .= '<i title='.xphp_get_lang('WEB_MACHINE_LOGIC_VOL').' class="viconfont vicon-luojifenqu"></i>';
            }
            //判断扩展分区
            $extend_flag =xphp_get_config('machine_os_config','VOLUME_TYPE')['BD_EXTEND_VOLUME'] == (xphp_get_config('machine_os_config','VOLUME_TYPE')['BD_EXTEND_VOLUME'] & intval($device['type']));
            if($extend_flag){
                //如果是扩展分区直接不显示
                continue;
            }
            //满足源是windows且没有挂载点且是分区时 (排除了整机接管)
            if(!empty($params) && !empty($params['origin_os_type']) && $params['origin_os_type'] == "Windows" && empty($mount_point) && $end_node == 1 && $params['type'] != 5){
                continue;
            }

             //如果是磁盘层并且is_boot为1
             if($device['is_boot'] == 1 && isset($device['part_table_type'])){
                //给name添加一个引导磁盘的的标签
                $name .= '<i title='.xphp_get_lang('WEB_MACHINE_OS_BOOT').' class="viconfont vicon-boot"></i>';
            }

             //如果磁盘阵列不为空
             $disk_array = "";
             if(!empty($device['disk_extra_info']) && !empty($device['disk_extra_info']['display_info'])){
                 $disk_array = $device['disk_extra_info']['display_info'];
             }
             if(!empty($disk_array)){
                $name .= '<i title="'.$disk_array.'" class="viconfont vicon-cipanzhenlie"></i>';
             }



            //处理逻辑
            $info[] = array(
                //获取唯一uuid
                'id' => $device['dev_node_uuid'],
                'main_pid' => $params['main_pid'] ?: $device['dev_uuid'],
                'dev_node_uuid' => $device['dev_node_uuid'],
                //获取磁盘uuid
                'dev_uuid' => $device['dev_uuid'],
                'fs_uuid' => $device['fs_uuid'],
                //获取操作系统磁盘类型
                'dev_type' => $device['type'],
                'show_amount' => $showMount, // 是否显示挂载路径
                //结构类型
                'struct_type' => $device['struct_type'],
                //获取挂载路径
                'mount_point' => $device['mount_point'],
                //获取父级id
                'pId' => $pid,
                //获取节点名称
                'dev_name' => $device['display_name'],
                'name' => $name,
                //额外的name信息
                'extra_name' => $device['disk_extra_info'] ? $device['disk_extra_info']['display_info'] : '',
                //是否是磁盘类型,如果为1表示磁盘,2表示非磁盘
                'first_node' => $first_node,
                //true表示是磁盘
                'is_disk_flag' => isset($device['part_table_type']) ? true : false,
                //是否是最后一个节点,1表示是最后一个节点,2表示不是最后一个节点
                'end_node' => $end_node,
                //true表示是卷或分区
                'is_volume_flag' => $end_node == 1 ? true : false,
                'removable_flag' =>  isset($device['removable_flag']) ? $device['removable_flag'] : 2,
                //获取总大小
                'total_size' => intval($device['total_size']),
                //获取已使用大小
                'used_size' => isset($device['used_size']) ? intval($device['used_size']) : 0,
                //图标
                'iconSkin' => isset($device['part_table_type']) ? 'tree_disk' : 'tree_volume' ,
                'iconClass' => isset($device['part_table_type']) ? 'vicon-cipan1 ztree_icon_color' : 'vicon-fenqu ztree_icon_color' ,
                "open" => $open,
                "checked" => true,
                "nocheck" => $nocheck,
                'chkDisabled' => $chkDisabled,
                "recovery_show_flag" => intval($device['recovery_show_flag']),
                //是否是系统磁盘
                "is_system_disk_flag" => $is_system_disk_flag,
                 //磁盘阵列
                 'disk_array' => $disk_array,
            );

            // 如果当前设备有子设备并且子设备数组非空，则递归处理子设备
            if (isset($device['sub_devices']) && count($device['sub_devices']) > 0) {
                // 当递归调用时，增加层级，并传递当前设备的id作为子设备的父id
                $sub_info = $this->diskTransitionList($device['sub_devices'], $device['dev_node_uuid'],$params);
                $info = array_merge($info, $sub_info);
            }
        }
        return $info;
    }




    /**
     *将虚拟机的备份数据转换成操作系统的数据格式
     * @param string $timepointUuid 时间点uuid
     * @param array  $params        请求参数
     * @return array
     */
    public function getBackupVM(string $timepointUuid, array $params = [])
    {
        $info = array();
        //获取虚拟机的备份磁盘数据
        $diskList = (new RecoveryJobController())->convertPoints($timepointUuid);
        //是否有勾选框
        $nocheck = false;
        if (!empty($params)) {
            //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管
            //获取前端的是否不要勾选框
            $nocheck = $params['nocheck'];
        }
        foreach ($diskList['disk_list'] as $device) {
            //这里获取的是所有数据,需要判断下is_backup_or_recovery的值, 1为备份了的,其他值都视为没备份
            if($device['is_backup_or_recovery'] != 1){
                continue;
            }
            //处理逻辑
            $info[] = array(
                //获取唯一uuid
                'id' => $device['disk_key'],
                'dev_node_uuid' => $device['disk_key'], //唯一识别码对应虚拟机的uuid
                //获取磁盘uuid
                'dev_uuid' => $device['disk_key'],//唯一识别码对应虚拟机的uuid
                //获取磁盘类型
                'dev_type' => $device['disk_type'], //取的虚拟机type值
                //结构类型
                'struct_type' => '',
                //获取父级id
                'pId' => 0, //虚拟机没有层级 都显示为一个层级只有父级
                //获取节点名称
                'dev_name' => $device['disk_name'],
                'name' => $device['disk_name'],
                //获取备份模式
                'disk_struct_backup_mode' => 2, //虚拟机没有备份模式,默认给2
                //额外的name信息
                'extra_name' => '', //额外的名称信息,可以在前端拼接
                //是否是磁盘类型,如果为1表示磁盘,2表示非磁盘
                'first_node' => 1, //虚拟机所有盘都当作磁盘
                //是否是最后一个节点,1表示是最后一个节点,2表示不是最后一个节点
                'end_node' => 1, //虚拟机所有都表示最后一个层级
                //获取总大小
                'total_size' => intval($device['virtual_size']),
                //获取已使用大小
                'used_size' => intval($device['virtual_size']),
                'open' => true,
                'checked' => true,
                'nocheck' => $nocheck,
            );
        }
        return $info;
    }

    /**
     *将虚拟机的备份数据转换成操作系统的数据格式
     */
    public function getBasicInfo($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_MACHINE_OS_GET_JOB_INFO'),
            'data' => array(),
        );
        //获取任务uuid
        $job_uuid = $params['job_uuid'];
        $JobInfoHandler = new LogicJobInfo;
        //获取基本信息任务信息
        $info = $JobInfoHandler->getJobInfo($job_uuid);
        if(!$info){
            $resultInfo['data'] = array(
                'flag' => false,
            );
            return $resultInfo;
        }
        $sql = "select cbt_flag, full_backup, valid_data_flag,silent_snapshot_flag,skip_bad_track_flag,original_compress_flag,original_compress_method,cache_target from os_task where task_uuid = ?";
        $result = $this->dbSelect($sql,array($job_uuid));
        $info['os_config'] = array();
        $info['os_config']['cbt_flag'] = $result[0]['cbt_flag'];
        $info['os_config']['full_backup'] = $result[0]['full_backup'];
        $info['os_config']['valid_data_flag'] = $result[0]['valid_data_flag'];
        $info['os_config']['silent_snapshot_flag'] = $result[0]['silent_snapshot_flag'];
        $info['os_config']['skip_bad_track_flag'] = $result[0]['skip_bad_track_flag'];
        $info['os_config']['original_compress_flag'] = $result[0]['original_compress_flag'];
        $info['os_config']['original_compress_method'] = $result[0]['original_compress_method'];
        //获取恢复的安全策略
        $jobHandler = new JobInfo;
        $info['safe_recover_info'] = $jobHandler->getSafeConfig($job_uuid);
        $resultInfo['data'] = $info;
        return $resultInfo;


    }


    /**
     * 获得所有agent的name和ip信息
     */
    public function getAllAgentList()
    {
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent";
        $result = $this->dbSelect($sql);
        $info = array();
        foreach ($result as $each) {
            $info[] = array(
                'agent_uuid' => $each['agent_uuid'],
                'agent_name' => $each['agent_name'],
                'hostname' => $each['hostname'],
                'ip' => $each['ip'],
            );
        }
        return $info;
    }


    public function getDetailsHostList($params){


    }



    /**
     * 根据任务状态和单个主机状态一起判断是否显示数据还是显示--
     * @param unknown $bd_task_status  任务状态
     * @param unknown $os_task_status  主机状态
     * @param unknown $Data 数据
     */
    public function getOSDisplayStr($bd_task_status,$os_task_status,$Data){
        return $Data;
        if($bd_task_status == xphp_get_config('task','TASKSTATUS')['RUNNING'] ||
            $bd_task_status == xphp_get_config('task','TASKSTATUS')['ABNORMAL']){
            //主机在任务中
            if($os_task_status == xphp_get_config('task','TASKSTATUS')['RUNNING'] ){
                //虚拟机是运行状态,显示bd_running_info的完成大小
                return $Data;
            }else{
                //虚拟机不在运行状态,显示vm_machine_list的完成大小
                return xphp_get_config('app','NULLSPACE');
            }
        }else{
            return xphp_get_config('app','NULLSPACE');
        }

    }


    /**
     * 获取详请主机列表
     */

    public function getMachineOsHostList($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_MACHINE_OS_GET_HOST_LIST'),
            'data' => array(
                'rows' => [],
                'total' => 0,
            ),
        );
        $taskUUID = $params['jobs_uuid'];
        $sql = "select ol.os_name, ol.total_size as ol_total_size, ol.task_status, ol.transport_size as ol_transport_size, 
        ol.backup_mode,ol.agent_ip, ol.os_config, ol.timepoint_uuid, ol.write_size as ol_write_size,
        ol.valid_size as ol_valid_size,ol.agent_uuid,ol.before_task_script, ol.after_task_script, 
        ol.exclude_devices_list , ol.recovery_strategy,ba.os_type,    
                bri.total_object_transport_size, bri.current_object_write_size, bri.speed, bri.speed_time, 
                bri.current_object_transport_size, bri.current_object_total_size, bri.current_object_valid_size,
                bt.task_status as bd_task_status, bt.task_type
                from os_list ol
                left join bd_running_info bri
                on ol.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = ol.task_uuid
                left join os_task ot 
                on ol.task_uuid = ot.task_uuid
                left join bd_agent ba
                on ba.agent_uuid = ol.agent_uuid 
                where ol.task_uuid = ?";
        if(!empty($params['search_val'])){
            $sql .=" and (ol.os_name like '%".$params['search_val']."%' or ol.agent_ip like '%".$params['search_val']."%') ";
        }
        //倒叙排序
        $sql .=" order by ol.id asc";
        $data = $this->dbSelect($sql, array($taskUUID));
        $i = 1;
        $agentList = $this->getAllAgentList();
        $ptDes = include APP_PATH . 'v1/description/Pf.php';
        $i = 1;
        $MachineOsHanlder = new MachineOsRecover;
        $vmHandler = new VmJobInfo;
        $resultInfo['data']['total'] = count($data);
        foreach ($data as $d){
            //这里要根据备份和恢复来分别获取
            //获取任务类型
            $task_type = $d['task_type'];
            $script_list = array();
            //获取时间点
            $timepoint_uuid = $d['timepoint_uuid'];

            //传输网络
            //传输网络
            $network_list = $this->get_network_str($taskUUID,$task_type, $d['agent_uuid']);


            //获取备份前脚本
            $before_task_script = json_decode($d['before_task_script'] ,true);
            //获取备份后脚本
            $after_task_script = json_decode($d['after_task_script'] ,true);
            $script_list = array(
                'before_task_script' => array(),
                'after_task_script' => array(),
            );

            foreach ($before_task_script as $value) {
                //获取脚本类型
                $script_type = intval($value['script_type']);
                //获取脚本名称
                $script_name = $value['script_name'];
                $script_list['before_task_script'][] = $script_name . ".".xphp_get_desc('Script','SCRIPT_TYPE')[$script_type];
            }
            foreach ($after_task_script as $value) {
                //获取脚本类型
                $script_type = intval($value['script_type']);
                //获取脚本名称
                $script_name = $value['script_name'];
                $script_list['after_task_script'][] = $script_name . ".".xphp_get_desc('Script','SCRIPT_TYPE')[$script_type];
            }


            $backup_info_list = array();
            $recover_info_list = array();
            $backup_timepoint_name = "";
            if($task_type == xphp_get_config('task', 'TASKTYPE')['OS_BACKUP']){
                //如果是备份
                //备份从bd_agent中查询
                //获取os_config
                $os_config = json_decode($d['os_config'],true);
                $exclude_devices_list = json_decode($d['exclude_devices_list'],true);
                //获取备份磁盘信息
                $backup_info_list_params1 = array(
                    'all_disk_list' => $os_config['all_disk_list'],
                    'exclude_device_list' => $exclude_devices_list,
                );
                $backup_info_list_params2 = array(
                    'nocheck' => true,
                    'recover_type' => true,
                    'origin_os_type' =>  $d['os_type'],
                );
                $backup_info_list =$this->getBackupDevices($backup_info_list_params1,$backup_info_list_params2);



            }else if($task_type == xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY']){
                //如果是恢复
                 //通过时间点查询当前时间点的备份数据
                $sql_recover = "select os_config,os_type from os_backup_timepoint where timepoint_uuid = ?";
                $sql_result = $this->dbSelect($sql_recover, array($timepoint_uuid));

                //获取os_config
                $os_config = json_decode($sql_result[0]['os_config'],true);

                //还要获取恢复os_list里面排除的设备列表
                //获取当前任务的uuid
                $exclude_devices_list = json_decode($d['exclude_devices_list'],true);
                $exclude_devices_list_os_config = $os_config['exclude_device_list'];
                if($exclude_devices_list_os_config == null || $exclude_devices_list_os_config == ""){
                    $exclude_devices_list_os_config = array();
                }
                //把两个数组组合在一起
                $exclude_devices_list1 = array_merge($exclude_devices_list,$exclude_devices_list_os_config);

                //获取备份磁盘信息
                $recover_info_list_params1 = array(
                    'all_disk_list' => $os_config['all_disk_list'],
                    'exclude_device_list' => $exclude_devices_list1,
                );

                $recover_info_list_params2 = array(
                    'nocheck' => true,
                    'recover_type' => true,
                    'origin_os_type' =>  $sql_result[0]['os_type'] == 1 ? 'Windows' :  'Linux',
                );
                $recover_info_list =$this->getBackupDevices($recover_info_list_params1,$recover_info_list_params2);
                //获取恢复的时间点数据以后,再遍历一遍,找到对应的名字进行修改
                //获取对应关系
                $recovery_strategy = json_decode($d['recovery_strategy'], true);

                // 构建关联数组
                $recover_info_map = [];
                foreach ($recover_info_list as $key => $each_ztree) {
                    $recover_info_map[$each_ztree['dev_node_uuid']] = &$recover_info_list[$key];
                }

                // 更新关联数组
                foreach ($recovery_strategy as $each) {
                    $destination_dev_node_uuid = $each['destination_dev_node_uuid'];
                    $destination_name = $each['destination_name'];
                    $source_dev_node_uuid = $each['source_dev_node_uuid'];

                    // 检查 recover_info_map 中是否存在对应的 dev_node_uuid
                    if (isset($recover_info_map[$source_dev_node_uuid])) {
                        // 更新名称和目标设备 UUID
                        $recover_info_map[$source_dev_node_uuid]['name'] .= "->" . $destination_name;
                        $recover_info_map[$source_dev_node_uuid]['destination_dev_node_uuid'] = $destination_dev_node_uuid;
                    }
                }


                //获取时间点名称
                //备份类型
                $sql_backup_timepoint = "select timepoint,backup_mode from bd_backup_timepoint where timepoint_uuid = ?";
                $sql_backup_timepoint_result = $this->dbSelect($sql_backup_timepoint, array($timepoint_uuid));
                $backup_mode_name =xphp_get_desc('Pf','BACKUP_MODE_DES')[$sql_backup_timepoint_result[0]['backup_mode']].xphp_get_lang('WEB_MACHINE_OS_BACKUP_POINT');
                $sql_backup_timepoint_name = $sql_backup_timepoint_result[0]['timepoint']."(".$backup_mode_name.")";
            }
            $backup_task_type_str = $vmHandler->getCurrentVMListTaskType($d['task_type'], $d['backup_mode'], $d['bd_task_status']);
            //当前任务是运行状态
            if($d['bd_task_status'] == xphp_get_config('task','TASKSTATUS')['RUNNING']){
                //主机列表是运行状态
                if($d['task_status'] == xphp_get_config('task','TASKSTATUS')['RUNNING']){
                    //显示实时数据
                    $resultInfo['data']['rows'][] = array(
                        //获取编号
                        'id_num' => $i++,
                        'id' => $d['agent_uuid'],
                        'agent_uuid' => $d['agent_uuid'],
                        //获取系统名称
                        'name' => $MachineOsHanlder->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'],$d['agent_ip']),
                        //备份类型
                        'task_type_str' => $backup_task_type_str,
                        //主机大小
                        'total_size' => v1_calsize($d['current_object_total_size'],true),
                        //有效数据大小
                        'current_object_valid_size' =>  v1_calsize($d['current_object_valid_size'],true),
                        //传输大小
                        'current_object_transport_size' =>  v1_calsize($d['current_object_transport_size'],true),
                        //写入大小
                        'current_object_write_size' =>  v1_calsize($d['current_object_write_size'],true),
                        //传输速度
                        'speed' =>  $vmHandler->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                        //传输进度
                        'Transmission_progress' =>  $this->getOSPercent($d['current_object_valid_size'], $d['current_object_transport_size']),
                        //状态
                        'status' =>  xphp_get_lang($ptDes['TASKSTATUSDES'][$d['task_status']]),
                        'status_value' =>  $d['task_status'],
                        //其他详情
                        // 'details' => $this->getOSDetailsInfo($d, $taskUUID),
                        //脚本获取
                        'script_list' => $script_list,
                        //获取磁盘信息
                        'disk_info_list' =>  $task_type == xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'] ? $backup_info_list:$recover_info_list,
                        'timepoint_name' => $sql_backup_timepoint_name,
                        'transport_network' => $network_list,
                    );
                }else if($d['task_status'] == xphp_get_config('task','TASKSTATUS')['UNKNOWN']){
                    //如果主机是未知, 则不显示内容
                    //当前任务其他状态,主机列表显示--
                    $resultInfo['data']['rows'][] = array(
                        //获取编号
                        'id_num' => $i++,
                        'id' => $d['agent_uuid'],
                        'agent_uuid' => $d['agent_uuid'],
                        //获取系统名称
                        'name' => $MachineOsHanlder->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'],$d['agent_ip']),
                        //备份类型
                        'task_type_str' => "--",
                        //主机大小
                        'total_size' => "--",
                        //有效数据大小
                        'current_object_valid_size' =>  "--",
                        //传输大小
                        'current_object_transport_size' =>  "--",
                        //写入大小
                        'current_object_write_size' =>  "--",
                        //传输速度
                        'speed' =>  "--",
                        //传输进度
                        'Transmission_progress' =>  "--",
                        //状态
                        'status' => "--",
                        'status_value' =>  $d['task_status'],
                        //其他详情
                        // 'details' => $this->getOSDetailsInfo($d, $taskUUID),
                        //脚本获取
                        'script_list' => $script_list,
                        //获取磁盘信息
                        'disk_info_list' =>  $task_type == xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'] ? $backup_info_list:$recover_info_list,
                        'timepoint_name' => $sql_backup_timepoint_name,
                        'transport_network' => $network_list,
                    );

                }else{
                    //主机列表是其他状态的时候显示ol.list的数据
                    //取os_list里面的数据
                    $resultInfo['data']['rows'][] = array(
                        //获取编号
                        'id_num' => $i++,
                        'id' => $d['agent_uuid'],
                        'agent_uuid' => $d['agent_uuid'],
                        //获取系统名称
                        'name' => $MachineOsHanlder->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'],$d['agent_ip']),
                        //备份类型
                        'task_type_str' => $backup_task_type_str,
                        //主机大小
                        'total_size' => v1_calsize($d['ol_total_size'],true),
                        //有效数据大小
                        'current_object_valid_size' =>  v1_calsize($d['ol_valid_size'],true),
                        //传输大小
                        'current_object_transport_size' =>  v1_calsize($d['ol_transport_size'],true),
                        //写入大小
                        'current_object_write_size' =>  v1_calsize($d['ol_write_size'],true),
                        //传输速度
                        'speed' =>  "--",
                        //传输进度
                        'Transmission_progress' =>  $this->getOSPercent($d['ol_valid_size'], $d['ol_transport_size']),
                        //状态
                        'status' =>  xphp_get_lang($ptDes['TASKSTATUSDES'][$d['task_status']]),
                        'status_value' =>  $d['task_status'],
                        //其他详情
                        // 'details' => $this->getOSDetailsInfo($d, $taskUUID),
                        //脚本获取
                        'script_list' => $script_list,
                        //获取磁盘信息
                        'disk_info_list' =>  $task_type == xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'] ? $backup_info_list:$recover_info_list,
                        'timepoint_name' => $sql_backup_timepoint_name,
                        'transport_network' => $network_list,
                    );
                }

            }else{
                //当前任务其他状态,主机列表显示--
                $resultInfo['data']['rows'][] = array(
                    //获取编号
                    'id_num' => $i++,
                    'id' => $d['agent_uuid'],
                    'agent_uuid' => $d['agent_uuid'],
                    //获取系统名称
                    'name' => $MachineOsHanlder->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'],$d['agent_ip']),
                    //备份类型
                    'task_type_str' => "--",
                    //主机大小
                    'total_size' => "--",
                    //有效数据大小
                    'current_object_valid_size' =>  "--",
                    //传输大小
                    'current_object_transport_size' =>  "--",
                    //写入大小
                    'current_object_write_size' =>  "--",
                    //传输速度
                    'speed' =>  "--",
                    //传输进度
                    'Transmission_progress' =>  "--",
                    //状态
                    'status' => "--",
                    'status_value' =>  $d['task_status'],
                    //其他详情
                    // 'details' => $this->getOSDetailsInfo($d, $taskUUID),
                    //脚本获取
                    'script_list' => $script_list,
                    //获取磁盘信息
                    'disk_info_list' =>  $task_type == xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'] ? $backup_info_list:$recover_info_list,
                    'timepoint_name' => $sql_backup_timepoint_name,
                    'transport_network' => $network_list,
                );
            }

        }

        return $resultInfo;

    }


    public function get_network_str($task_uuid, $task_type, $agent_uuid = ""){
        $network_info = array();
        if($task_type == xphp_get_config('task', 'TASKTYPE')['OS_BACKUP']){
             //获取传输网络
            $network_str = "";
            $sql_transport = "select bts.network_uuid, bts.network_pool_uuid, bn.port, bn.ip
            from bd_transport_strategy bts 
            left join bd_node_network bn on bts.network_uuid = bn.network_uuid where bts.task_uuid = ?";
            $transport_info = $this->dbSelect($sql_transport,array($task_uuid));
            //获取到该主机的network信息
            if(empty($transport_info[0]['network_uuid'])){
                $network_str = xphp_get_lang('WEB_MACHINE_OS_AUTO_NETWORK');
            }else{
                $network_str = $transport_info[0]['ip'].":".$transport_info[0]['port'];
            }
            $network_info =  array(
                "network_str"=>$network_str,
                "network_uuid"=>$transport_info[0]['network_uuid'],
                "network_pool_uuid"=>$transport_info[0]['network_pool_uuid'],
            );
        }else if($task_type == xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY']){
            $network_str = "";
            $sql_transport = "select ol.network_uuid, bn.port, bn.ip
            from os_list ol 
            left join bd_node_network bn on ol.network_uuid = bn.network_uuid 
            where ol.task_uuid = ? and ol.agent_uuid = ?";
            $transport_info = $this->dbSelect($sql_transport,array($task_uuid, $agent_uuid));
            if(empty($transport_info[0]['network_uuid'])){
                $network_str = xphp_get_lang('WEB_MACHINE_OS_AUTO_NETWORK');
            }else{
                $network_str = $transport_info[0]['ip'].":".$transport_info[0]['port'];
            }
            $network_info =  array(
                "network_str"=>$network_str,
                "network_uuid"=>$transport_info[0]['network_uuid'],
                "network_pool_uuid"=>"",
            );
        }
        return $network_info;
    }






    /**
     * 获取传输百分比
     * @param unknown $valid_size 分母
     * @param unknown $transport_size 分子
     */
    public function getOSPercent($valid_size, $transport_size){
        $valid_size = intval($valid_size);
        $transport_size = intval($transport_size);
        if($valid_size == $transport_size){
            //如果相等
            if(empty($valid_size)){
                return '0%';
            }else{
                return '100%';
            }

        }
        if($transport_size > $valid_size){
            //如果分子比分母大
            return xphp_get_config('app', 'NULLSPACE');
        }
        return v1_calpercent($valid_size, $transport_size);
    }



















    //  /**
    //  * 得到os任务详情
    //  * @param array  $d        虚拟机信息
    //  * @param string $taskUuid 任务uuid
    //  */
    // protected function getOSDetailsInfo(array $d, string $taskUuid): array
    // {
    //     if ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['OS_BACKUP']) {
    //         //操作系统备份
    //         return $this->getOSBackupDetailsInfo($d, $taskUuid);
    //     } elseif ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY']) {
    //         //操作系统恢复
    //         return $this->getOSRecoveryDetailsInfo($d, $taskUuid);
    //     } elseif ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['OS_INSTANT_RECOVERY_MOTION']) {
    //         // //操作系统恢复
    //         // return $this->getOSMotionDetailsInfo($d, $taskUuid);
    //     }else{
    //         return array();
    //     }
    // }




    // /**
    //  * 得到备份的详情信息
    //  * @param unknown $d taskuuid
    //  *
    //  */
    // public function getOSBackupDetailsInfo($d, $taskUUID){
    //     //处理主机备份的分区信息
    //     $os_config = $d['os_config'];
    //     $volList = json_decode($os_config,true);
    //     $volList = $volList['backup_info_list'];
    //     $infoList  = array();
    //     foreach ($volList as $each){
    //         $infoList[] = array(
    //             'name' => empty($each['name']) ? '--' : $each['name'],
    //             'mount_path' => empty($each['mount_path'])? '--' : $each['mount_path'],
    //             'total_size' => v1_calsize($each['total_size'],true),
    //         );
    //     }
    //     array_multisort($infoList);
    //     $info = array(
    //         "task_type_num" => intval($d['task_type']),
    //         "msgVol" => $infoList,
    //     );
    //     return $info;
    // }

    //  /**
    //  * 得到恢复的详情信息
    //  * @param unknown $d
    //  * @param unknown $taskUUID
    //  */
    // public function getOSRecoveryDetailsInfo($d, $taskUUID){
    //     //只有回复才有时间点信息
    //     $vmInfo = new VmJobInfo();
    //     $timepointInfo = $vmInfo->getTimepointInfo($d['timepoint_uuid']);
    //     $os_config = $d['os_config'];
    //     $volList = json_decode($os_config,true);
    //     $volList = $volList['recovery_info_list'];
    //     $infoList  = array();
    //     foreach ($volList as $each){
    //         $infoList[] = array(
    //             "timepoint" => $timepointInfo['timepoint'],
    //             'source_mount_path' => empty($each['source_mount_path']) ? '--' : $each['source_mount_path'],
    //             'source_name' => empty($each['source_name'])? '--' : $each['source_name'],
    //             'transfer_size' => v1_calsize($each['transfer_size'],true),
    //         );
    //     }
    //     array_multisort($infoList);
    //     $info = array(
    //         "task_type_num" => intval($d['task_type']),
    //         "msgVol" => $infoList,
    //         "timepoint" => $timepointInfo['timepoint'],
    //     );
    //     return $info;
    // }




    /**
     * 得到任务流量
     * @param unknown $params 参数
     * @return 流量
     */
    public function getTaskSpeed($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_MACHINE_OS_GET_JOB_TRAFFIC'),
            'data' => array(
            ),
        );
        $taskUUID = $params['jobs_uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.module_type, bri.speed, bri.speed_time, bt.task_status from bd_running_info bri, bd_task bt 
                where bri.task_uuid = bt.task_uuid and bri.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $speed = 0;
        if ($data) {
            if (
                $data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
                $data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
            ) {
                $speedCount = intval($data[0]['speed']);
                if ($speedCount < 0) {
                    $speedCount = 0;
                }
                $speed = round($speedCount / 1024, 2);
            }
        }
        // if (time() - $data[0]['speed_time'] > 12) {
        //     //如果长时间没有更新速度,处理速度为0
        //     $speed = 0;
        // }
        $resultInfo['data'] = array(
            'speed' => $speed,
            'test' => $data[0]['speed_time'],
            't' => time(),
            'nowTime' => date('H:i:s')
        );
        return $resultInfo;
    }




    /**
     * 获取详请主机列表
     */

    public function getScriptContent($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_MACHINE_OS_GET_SCRIPT_SUCCESS'),
            'data' => array(),
        );

        //获取脚本agent_uuid
        $value_agent_uuid = $params['value_agent_uuid'];
        //获取脚本名称
        //把名称.后面的去掉
        $value_script_name = substr($params['value_script_name'],0,strpos($params['value_script_name'],'.'));
        //获取脚本位置
        $value_script_position = intval($params['value_script_position']);
        //获取任务uuid
        $value_task_uuid = $params['value_task_uuid'];
        //获取脚本内容
        $sql = "select before_task_script, after_task_script from os_list where task_uuid = ? and agent_uuid = ?";
        $data = $this->dbSelect($sql, array($value_task_uuid,$value_agent_uuid));
        if (empty($data)) {
            return $resultInfo;
        }
        //获取备份前脚本
        $before_task_script = json_decode($data[0]['before_task_script'], true);
        //获取备份后脚本
        $after_task_script = json_decode($data[0]['after_task_script'], true);
        $scriptInfo = array();
        if($value_script_position == 1){
            //获取脚本前的内容
            foreach($before_task_script as $each){
                //获取每一个脚本名字
                $script_name = $each['script_name'];
                if($script_name == $value_script_name){
                    $scriptInfo = $each;
                    break;
                }
            }
        }else{
            //获取脚本前的内容
            foreach($after_task_script as $each){
                //获取每一个脚本名字
                $script_name = $each['script_name'];
                if($script_name == $value_script_name){
                    $scriptInfo = $each;
                    break;
                }
            }
        }
        $resultInfo['data'] = $scriptInfo;
        return $resultInfo;
    }


    /**
     * 操作系统任务详情-操作系统列表-对单个主机进行操作相关
     * @param unknown $params
     */
    public function startOSJob($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_MACHINE_OS_START_JOB_SUCCESS'),
            'data' => array(),
        );
        //获取备份模式
        $backup_mode = $params['backup_mode'];
        //获取任务uuid
        $task_uuid = $params['taskuuid'];
        //获取主机列表
        $os_uuid_list = $params['os_uuids'];
        //以下参数置位空
        $time_strategy_id = 0;
        $auto_start_flag = 2;
        //定义操作码
        $opName = 'BD_TASK_OP_BACKUP_START';
        $msg = array(
            'task_uuid' => $task_uuid,
            'backup_mode' => $backup_mode,
            'time_strategy_id' => $time_strategy_id,
            'auto_start_flag' => $auto_start_flag,
            "os_uuid_list" => $os_uuid_list,
        );
        $msg = json_encode($msg);
        $sync = FALSE;
        $command = TRUE;
        $nodeuuid = $this->getLocalNodeUUID();
        $sub_module_type = 1;
        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, $sync, $command, $sub_module_type);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $resultInfo;
        }else{
            $resultInfo['success'] = false;
            $resultInfo['message'] = xphp_get_lang('WEB_MACHINE_OS_START_JOB_FAIL');
            return $resultInfo;
        }
    }

    /**
     * 获取客户端信息
     */

    public function getClientInfo($params){
        $nodeuuid = $this->getLocalNodeUUID();
        //得到操作码
        $opName = 'OS_MACHINE_OP_CODE_GET_CLIENT_INFO';
        $pfMSg['agent_uuid'] = $params['agent_uuid'];
        $submodule_type = 1; //0和2表示以前老版本的操作系统备份   1表示定时整机的
        $msg = json_encode($pfMSg);

        //同步数据
        $mbResult = $this->service()->getOSBackupAgentInfo($nodeuuid,$opName,$msg,true,$submodule_type);
        $info =  $mbResult['msg'];
        $memoryInfo = v1_calsize_to_value_and_unit(intval($info['mem_size']), true);

        $info['memory_size_int'] = round(intval($memoryInfo['value']));
        $info['memory_size_unit'] = $memoryInfo['unit'];
        $pfOpcode  = new OsOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);

        $resultInfo = array(
            'succcess' => $mbResult['result'],
            'data' => $info,
            'message' => $operate,
            'code' => $mbResult['errorCode']

        );
        return  $resultInfo;

    }






}