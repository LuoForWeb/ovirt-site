<?php

namespace app\v1\os\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\os\v0\logic\OsData;
use app\v1\vm\v0\logic\VmJobInfo;


/**
 * note          操作系统 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsJobInfo extends Base
{

    /**
     * 获取瞬时恢复任务详细信息
     */
    public function getInstantOSRecoverJobInfo($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_INSTANT_RECOVERY_TASK_DETAILS'),
            'data' => array(),
        );
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $sql  = "select  ol.inst_recovery_agent_uuid  as host_ip, ba.online_flag, bt.task_status, bt.task_type, ot.inst_iscsi_network_uuid
        from bd_task bt
        left join os_list ol on bt.task_uuid = ol.task_uuid 
        left join os_backup_timepoint obt on obt.timepoint_uuid = ol.timepoint_uuid
        left join bd_agent ba on obt.agent_uuid  = ba.agent_uuid
        left join os_task ot on  bt.task_uuid = ot.task_uuid 
        where bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $targetinfo = (new JobInfo())->getOSHostInfo($data[0]['host_ip']);
        $serverip = $this->getServerIp($data[0]['inst_iscsi_network_uuid']);
        $resultInfo['data'] =  array(
            "server_ip"=>$serverip,
            "host_ip"=>$targetinfo['ip'],
            "online_flag"=> v1_parse_flag_to_bool($targetinfo['online_flag']),
            "task_status"=>$data[0]['task_status'],
            "task_type"=>$data[0]['task_type']
        );
        return $resultInfo;
    }

    /**
     * 获取迁移任务详细信息
     */
    public function getOSMotionJobInfo($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_INSTANT_RECOVERY_TASK_DETAILS'),
            'data' => array(),
        );
        $taskUUID = $params['uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
        bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time,
        bt.node_uuid, bt.storage_uuid, unix_timestamp(bs.next_start_time) next_start_time,
        bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time, 
        ol.reparted_flag, ol.agent_uuid as targetid, ol.os_config,
        ba.ip  as host_ip, ba.online_flag,
        obt.agent_uuid as sourceid, obt.os_type,
        ot.inst_iscsi_network_uuid 
        from bd_task bt 
        left join bd_running_info bri on bt.task_uuid = bri.task_uuid 
        left join bd_strategy bs on bt.strategy_id = bs.strategy_id 
        left join os_list ol on bt.task_uuid = ol.task_uuid 
        left join bd_agent ba on ba.agent_uuid  = ol.agent_uuid
        left join os_backup_timepoint obt on obt.timepoint_uuid = ol.timepoint_uuid
        left join os_task ot on  bt.task_uuid = ot.task_uuid 
        where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        if(!$data){
            $resultInfo['data'] = array('flag' => false);
            return $resultInfo;
        }
        $jobInfo = new JobInfo();
        // $ptDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $ptDes = include APP_PATH . 'v1/description/Pf.php';
        foreach ($data as $d){
            $list = json_decode($d['os_config'],true);
            $sourceinfo = $jobInfo->getOSHostInfo($d['sourceid']);
            $targetinfo = $jobInfo->getOSHostInfo($d['targetid']); //目标主机只取第一个原主机下的
            $sourcehost =  $jobInfo->getAgentNamebyName($sourceinfo["hostname"], $sourceinfo["agent_name"], $sourceinfo["ip"]) . "(" . $sourceinfo['ip'] . ")";
            $targethost = $jobInfo->getAgentNamebyName($targetinfo["hostname"], $targetinfo["agent_name"], $targetinfo["ip"]) . "(" . $targetinfo['ip'] . ")";
            $serverip = $this->getServerIp($d['inst_iscsi_network_uuid']);
            $basicInfo = array(
                //任务名
                'taskName' => $d['task_name'],
                //任务状态
                'status' =>  xphp_get_lang($ptDes['TASKSTATUSDES'][$d['task_status']]),
                //任务总容量
                'totalSize' => v1_calsize($d['total_object_size']),
                //已处理容量
                'currentSize' =>  v1_calsize($d['total_object_completed_size']),
                //开始时间
                'startTime' => $jobInfo->getStartTIme($d['start_time'], $d['task_status']),
                //持续时间
                'intervalTime' => $jobInfo->getTimeInterval($d['start_time'], $d['task_status']),
                //限速信息
                'speed_limit' => $jobInfo->getSpeedlimitDes($d['task_uuid']),
                //创建/修改时间
                'createTime' => $this->parseDate($d['create_time']),
                //下次开始时间
                'nextTime' => $jobInfo->getNextStartTime($d['next_start_time'], $d['task_status']),
                //传输策略
                'transportStrategy' => $this->gettransportOSinfo($d['strategy_id']),
                //任务状态int
                'status_num' => $d['task_status'],
                //任务进度
                'progress' => $jobInfo->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], false, $d['task_type']),
                //任务进度百分比
                'totalprogress' => $jobInfo->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']),
                //网络传输
                'networkFlag' => $this->getNodeNetworkFlag($taskUUID),//检查显示传输网络标志
                //重建分区
                'reparted_flag' => v1_parse_flag_to_bool($d['reparted_flag']),  //重建分区(只有恢复有)
                'repair_linux_flag' => v1_parse_flag_to_bool($list['repair_linux_flag']), //引导恢复(只有恢复有)
                'osType' => $d['os_type'],              //操作系统类型
                //操作系统迁移用 新增
                'speed' => $this->getJobSpeed($d['task_status'], $d['speed']),
                'taskType' => $this->getBasicInfoTaskTypeDes(xphp_get_lang($ptDes['TASKTYPEDES'][$d['task_type']]),
                $d['module_type'], $d['task_uuid']),
                'endTime' => $this->getCalEndTime($d['total_object_size'], $d['total_object_completed_size'], $d['task_status'], $d['speed']),
                'thread_num' => intval($d['thread_num']),
                "server_ip"=> $serverip,
                "host_ip"=> $d['host_ip'],
                "online_flag"=> v1_parse_flag_to_bool($d['online_flag']),
                "task_status"=> $d['task_status'],
                "recoverhostname"=>$sourcehost,
                "motionhostname"=>$targethost,
                "tasytype"=>intval($d['task_type']),//用于判断是否跳转到瞬时恢复页面
            );
        }
        $resultInfo['data'] = $basicInfo;
        return $resultInfo;
    }

    /**
     * 获取迁移任务详细信息
     */
    public function getOSDetailList($params){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_INSTANT_RECOVERY_TASK_DETAILS'),
            'data' => array(),
        );
        $taskUUID = $params['uuid'];
        $sql = "select ol.os_name, ol.total_size as ol_total_size, ol.task_status, ol.transport_size as ol_transport_size, ol.backup_mode,ol.agent_ip, ol.os_config, ol.timepoint_uuid, ol.write_size as ol_write_size, ol.valid_size as ol_valid_size,ol.agent_uuid,     
                bri.total_object_transport_size, bri.current_object_write_size, bri.speed, bri.speed_time, bri.current_object_transport_size, bri.current_object_total_size, bri.current_object_valid_size,
                bt.task_status as bd_task_status, bt.task_type
                from os_list ol
                left join bd_running_info bri
                on ol.task_uuid = bri.task_uuid
                left join bd_task bt
                on bt.task_uuid = ol.task_uuid
                left join os_task ot 
                on ol.task_uuid = ot.task_uuid where
                ol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        // $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $records["data"] = array();
        $osHandler =  new OsData();
        $jobInfo = new JobInfo();
        $agentList = $osHandler->getAllAgentList();
        $vmInfo = new VmJobInfo();
        $ptDes = include APP_PATH . 'v1/description/Pf.php';
        foreach ($data as $d){
            //这里显示要根据任务状态显示,状态为已完成或停止等状态 显示ol_list里面的数据大小
            //状态为等待状态时大小统一显示--
            //状态为正在运行状态时显示bd_running_info里面的数据大小
            //获取名字
            $name = $osHandler->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'],$d['agent_ip']);

            //获取ol_list中的状态
            $os_task_status = $d['task_status']; //主机状态
            $bd_task_status = $d['bd_task_status']; //任务状态
            if($d['task_type'] ==  xphp_get_config('task', 'TASKTYPE')['OS_INSTANT_RECOVERY_MOTION']){
                $records["data"][] = array(
                    //编号
                    $i++,
                    //系统名称
                    $name,
                    //备份类型
                    $vmInfo->getCurrentVMListTaskType($d['task_type'], $d['backup_mode'], $d['bd_task_status']),
                    //主机大小
                    v1_calsize($d['current_object_total_size']),
                    //有效数据大小
                    v1_calsize($d['current_object_valid_size']),
                    //传输大小
                    v1_calsize($d['current_object_transport_size']),
                    //写入大小
                    v1_calsize($d['current_object_write_size']),
                    //传输速度
                    $vmInfo->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                    //传输进度
                    $this->getOSPercent($d['current_object_valid_size'], $d['current_object_transport_size']),
                    //状态
                    xphp_get_lang($ptDes['TASKSTATUSDES'][$d['task_status']]),
                    //其他详情
                    $this->getOSDetailsInfo($d, $taskUUID),
                );
            }else{
                if($bd_task_status ==  xphp_get_config('task', 'TASKSTATUS')['RUNNING'] || $bd_task_status ==  xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']){
                    if($os_task_status ==  xphp_get_config('task', 'TASKSTATUS')['RUNNING']){
                        $records["data"][] = array(
                            //勾选框
                            '<input type="checkbox" name="id'. $d['agent_uuid'] .'" value="'. $d['agent_uuid'] .'">',
                            //编号
                            $i++,
                            //系统名称
                            $name,
                            //备份类型
                            $vmInfo->getCurrentVMListTaskType($d['task_type'], $d['backup_mode'], $d['bd_task_status']),
                            //主机大小
                            v1_calsize($d['current_object_total_size']),
                            //有效数据大小
                            v1_calsize($d['current_object_valid_size']),
                            //传输大小
                            v1_calsize($d['current_object_transport_size']),
                            //写入大小
                            v1_calsize($d['current_object_write_size']),
                            //传输速度
                            $vmInfo->getVMSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                            //传输进度
                            $this->getOSPercent($d['current_object_valid_size'], $d['current_object_transport_size']),
                            //状态
                            xphp_get_lang($ptDes['TASKSTATUSDES'][$d['task_status']]),
                            //其他详情
                            $this->getOSDetailsInfo($d, $taskUUID),
                    );
                }else if($os_task_status == xphp_get_config('task', 'TASKSTATUS')['UNKNOWN']){
                        $records["data"][] = array(
                            //勾选框
                            '<input type="checkbox" name="id'. $d['agent_uuid'] .'" value="'. $d['agent_uuid'] .'">',
                            //编号
                            $i++,
                            //系统名称
                            $name,
                            //备份类型
                            xphp_get_config('app')['NULLSPACE'],
                            //主机大小
                            xphp_get_config('app')['NULLSPACE'],
                            //有效数据大小
                            xphp_get_config('app')['NULLSPACE'],
                            //传输大小
                            xphp_get_config('app')['NULLSPACE'],
                            //写入大小
                            xphp_get_config('app')['NULLSPACE'],
                            //传输速度
                            xphp_get_config('app')['NULLSPACE'],
                            //传输进度
                            xphp_get_config('app')['NULLSPACE'],
                            //状态
                            xphp_get_config('app')['NULLSPACE'],
                            //其他详情
                            $this->getOSDetailsInfo($d, $taskUUID),
                        );
                    }else{
                            $records["data"][] = array(
                                //勾选框
                                '<input type="checkbox" name="id'. $d['agent_uuid'] .'" value="'. $d['agent_uuid'] .'">',
                                //编号
                                $i++,
                                //系统名称
                                $name,
                                //备份类型
                                $vmInfo->getCurrentVMListTaskType($d['task_type'], $d['backup_mode'], $d['bd_task_status']),
                                //主机大小
                                v1_calsize($d['ol_total_size']),
                                //有效数据大小
                                v1_calsize($d['ol_valid_size']),
                                //传输大小
                                v1_calsize($d['ol_transport_size']),
                                //写入大小
                                v1_calsize($d['ol_write_size']),
                                //传输速度
                                xphp_get_config('app')['NULLSPACE'],
                                //传输进度
                                $this->getOSPercent($d['ol_valid_size'], $d['ol_transport_size']),
                                //状态
                                xphp_get_lang($ptDes['TASKSTATUSDES'][$d['task_status']]),
                                //其他详情
                                $this->getOSDetailsInfo($d, $taskUUID),
                            );
                    }
                }else{
                    $records["data"][] = array(
                        //勾选框
                        '<input type="checkbox" name="id'. $d['agent_uuid'] .'" value="'. $d['agent_uuid'] .'">',
                        //编号
                        $i++,
                        //系统名称
                        $name,
                        //备份类型
                        xphp_get_config('app')['NULLSPACE'],
                        //主机大小
                        xphp_get_config('app')['NULLSPACE'],
                        //有效数据大小
                        xphp_get_config('app')['NULLSPACE'],
                        //传输大小
                        xphp_get_config('app')['NULLSPACE'],
                        //写入大小
                        xphp_get_config('app')['NULLSPACE'],
                        //传输速度
                        xphp_get_config('app')['NULLSPACE'],
                        //传输进度
                        xphp_get_config('app')['NULLSPACE'],
                        //状态
                        xphp_get_config('app')['NULLSPACE'],
                        //其他详情
                        $this->getOSDetailsInfo($d, $taskUUID),
                    );
                }

            }
        }
        $resultInfo['data'] = $records;
        return $resultInfo;
    }

    //获取服务器IP地址
    public function getServerIp($networkuuid){
        $sql = "select ip from bd_node_network where network_uuid  = ? ";
        $data = $this->dbSelect($sql,array($networkuuid));
        return $data[0]['ip'];
    }

    /**
     * 得到主机传输策略
     * @return NULL[]
     */
    private function gettransportOSinfo($strategyid){
        $info = array();
        $sql = "select bts.encrypt_flag, bts.compress_flag, bnn.ip, bnn.port, bnn.alias_name, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method from bd_transport_strategy bts left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $info['encrypt'] = v1_parse_flag_to_bool($data[0]['encrypt_flag']);
        $info['compress'] = v1_parse_flag_to_bool($data[0]['compress_flag']);
        //获取传输网络描述
        $name = "";
        if(!empty($data[0]['ip'])){
            $name = $data[0]['ip'].":".$data[0]['port'];
            if(!empty($data[0]['alias_name'])){
                $name .= "(".$data[0]['alias_name'].")";
            }
        }
        $info['network'] = $name;
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        $info['encrypt_method'] = $data[0]['encrypt_method'];
        return $info;
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid
     * @return boolean
     */
    public function getNodeNetworkFlag($taskuuid){
        $sql = "select ba.net_model, bts.network_uuid from bd_transport_strategy bts, bd_agent ba, bd_task_agent_list btal where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = bts.task_uuid and btal.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;  //定义传输网络标志
        foreach ($data as $d){
            if(intval($d['net_model']) == 2 && !empty($d['network_uuid'])){
                $flag = true;
            }
        }

        return $flag;
    }

    /**
     * 根据任务状态计算速度
     * @param unknown $taskStatus 任务状态
     * @param  int $speed      速度
     * @return   速度
     */
    public function getJobSpeed($taskStatus, $speed)
    {
        if ($taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
            ;
        }
        return v1_calspeed($speed);
    }

    /**
     * 得到任务监控界面的任务类型 ,主要是获取虚拟机的子模块
     * @param string $taskTypeDes
     * @param int    $moduleType
     * @param string $taskuuid
     */
    protected function getBasicInfoTaskTypeDes($taskTypeDes, $moduleType, $taskuuid)
    {
        if ($moduleType == xphp_get_config('module')['MODULE_TYPE']['VM']) {
            $sql = "select hypervisor_type from vm_task where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            $hypervisor = intval($data[0]['hypervisor_type']);
            $taskTypeDes .= '[' . xphp_get_config('vm')['VMHYPERVISORDES'][$hypervisor] . ']';
        }
        return $taskTypeDes;
    }

    /**
     * 根据任务运行情况估算完成时间
     * @param int $totalSize   总大小
     * @param int $currentSize 当前大小
     * @param int $taskStatus  任务状态
     * @param int $speed       速度
     * @return 时间
     */
    public function getCalEndTime($totalSize, $currentSize, $taskStatus, $speed)
    {
        if (
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //任务没有运行
            return xphp_get_config('app', 'TIMESPACE');
        }
        if (0 == intval($speed)) {
            return xphp_get_config('app', 'TIMESPACE');
        }
        $needTime = ($totalSize - $currentSize) / $speed ;
        return date('Y-m-d H:i:s', time() + intval($needTime));
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

    /**
     * 得到os任务详情
     * @param array  $d        虚拟机信息
     * @param string $taskUuid 任务uuid
     */
    protected function getOSDetailsInfo(array $d, string $taskUuid): array
    {
        if ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['OS_BACKUP']) {
            //操作系统备份
            return $this->getOSBackupDetailsInfo($d, $taskUuid);
        } elseif ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY']) {
            //操作系统恢复
            return $this->getOSRecoveryDetailsInfo($d, $taskUuid);
        } elseif ($d['task_type'] == xphp_get_config('task', 'TASKTYPE')['OS_INSTANT_RECOVERY_MOTION']) {
            //操作系统恢复
            return $this->getOSMotionDetailsInfo($d, $taskUuid);
        }else{
            return array();
        }
    }

    /**
     * 得到备份的详情信息
     * @param unknown $d taskuuid
     *
     */
    public function getOSBackupDetailsInfo($d, $taskUUID){
        //处理主机备份的分区信息
        $os_config = $d['os_config'];
        $volList = json_decode($os_config,true);
        $volList = $volList['backup_info_list'];
        $infoList  = array();
        foreach ($volList as $each){
            $infoList[] = array(
                'name' => empty($each['name']) ? '--' : $each['name'],
                'mount_path' => empty($each['mount_path'])? '--' : $each['mount_path'],
                'total_size' => v1_calsize($each['total_size'],true),
            );
        }
        array_multisort($infoList);
        $info = array(
            "task_type_num" => intval($d['task_type']),
            "msgVol" => $infoList,
        );
        return $info;
    }

     /**
     * 得到恢复的详情信息
     * @param unknown $d
     * @param unknown $taskUUID
     */
    public function getOSRecoveryDetailsInfo($d, $taskUUID){
        //只有回复才有时间点信息
        $vmInfo = new VmJobInfo();
        $timepointInfo = $vmInfo->getTimepointInfo($d['timepoint_uuid']);
        $os_config = $d['os_config'];
        $volList = json_decode($os_config,true);
        $volList = $volList['recovery_info_list'];
        $infoList  = array();
        foreach ($volList as $each){
            $infoList[] = array(
                "timepoint" => $timepointInfo['timepoint'],
                'source_mount_path' => empty($each['source_mount_path']) ? '--' : $each['source_mount_path'],
                'source_name' => empty($each['source_name'])? '--' : $each['source_name'],
                'transfer_size' => v1_calsize($each['transfer_size'],true),
            );
        }
        array_multisort($infoList);
        $info = array(
            "task_type_num" => intval($d['task_type']),
            "msgVol" => $infoList,
            "timepoint" => $timepointInfo['timepoint'],
        );
        return $info;
    }
    /**
     * 得到操作系统迁移的详情信息
     * @param unknown $d
     * @param unknown $taskUUID
     */
    public function getOSMotionDetailsInfo($d,$taskuuid){
        //获取迁移源主机和迁移目标主机
        $sql = "select obt.agent_uuid as sourceid, ol.agent_uuid as targetid from os_list ol, os_backup_timepoint obt where
        ol.timepoint_uuid =  obt.timepoint_uuid and
        ol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $jobInfo = new JobInfo();
        $vmInfo = new VmJobInfo();
        $sourceinfo = $jobInfo->getOSHostInfo($data[0]['sourceid']);
        $targetinfo = $jobInfo->getOSHostInfo($data[0]['targetid']); //目标主机只取第一个原主机下的
        $sourcehost =  $jobInfo->getAgentNamebyName($sourceinfo["hostname"], $sourceinfo["agent_name"], $sourceinfo["ip"]) . "(" . $sourceinfo['ip'] . ")";
        $targethost = $jobInfo->getAgentNamebyName($targetinfo["hostname"], $targetinfo["agent_name"], $targetinfo["ip"]) . "(" . $targetinfo['ip'] . ")";
        //时间点 迁移源主机 迁移目标主机
        $timepointInfo = $vmInfo->getTimepointInfo($d['timepoint_uuid']);
        $info = array(
            "taskname" => $timepointInfo['taskname'],
            "timepoint" => $timepointInfo['timepoint'],
            "source_name" => $sourcehost,
            "target_name" => $targethost
        );
        return  $info;
    }





}
