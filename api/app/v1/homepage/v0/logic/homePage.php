<?php

namespace app\v1\homepage\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\Data;
use app\v1\log\v0\logic\Index;
use app\v1\resources\v0\logic\Index as LogicIndex;
use app\v1\resources\v0\logic\Node;
use app\v1\user\v0\logic\User;
use app\v1\log\v0\logic\Index as LogInfo;
use app\v1\homepage\v0\logic\homePageInfo;
use app\v1\resources\v0\logic\Index as ResourceIndex;

/**
 * note          首页
 * @author       liushuai@vinchin.com
 * @date         2023/11/23 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class homePage extends Base
{
    /**
     * 获取首页基本数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function getHomePageInfo($params)
    {
        //这里根据传进来的卡片名称来获取数据,如果为false表示不拿数据,如果为true则获取该数据
        //返回数据
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_HOMEPAGE_GET_DATA_SUCCESS'),
            'data' => array(),
        );

        $params['userval'] = $params['userval'] ? $params['userval'] : xphp_get_user_info()['userUuid'];
        $hostInfo = $this->getHostInfo($params['userval']);
        //概括-----card_basic_data
        $card_basic_data = boolval($params['card_basic_data']);
        if($card_basic_data){
            $info['data']['card_basic_data'] = $this->get_card_basic_data($params['userval']);
        }
        //概括-----card_basic_data
        $card_basic_2 = boolval($params['card_basic_2']);
        if($card_basic_2){
            $info['data']['card_basic_2'] = $this->get_card_basic_data($params['userval']);
        }
        //概括-----card_basic_data
        $card_basic_3 = boolval($params['card_basic_3']);
        if($card_basic_3){
            $info['data']['card_basic_3'] = $this->get_card_basic_data($params['userval']);
        }
        //当前任务-----card_current_job_1
        $card_current_job_1 = boolval($params['card_current_job_1']);
        if($card_current_job_1){
            $info['data']['card_current_job_1'] = $this->get_card_current_job_1($params['userval']);
        }
        //备份存储-----card_backup_storage_1
        $card_backup_storage_1 = boolval($params['card_backup_storage_1']);
        if($card_backup_storage_1){
            $info['data']['card_backup_storage_1'] = $this->get_card_backup_storage_1($params['userval']);
        }
        //副本存储-----card_copy_storage_pool_1
        $card_copy_storage_pool_1 = boolval($params['card_copy_storage_pool_1']);
        if($card_copy_storage_pool_1){
            $info['data']['card_copy_storage_pool_1'] = $this->get_card_copy_storage_pool_1($params['userval']);
        }
        //归档存储-----card_Archival_storage_1
        $card_Archival_storage_1 = boolval($params['card_Archival_storage_1']);
        if($card_Archival_storage_1){
            $info['data']['card_Archival_storage_1'] = $this->get_card_Archival_storage_1($params['userval']);
        }
        //磁盘IOPS-----card_iops_1
        $card_iops_1 = boolval($params['card_iops_1']);
        //网络流量-----card_network_1
        $card_network_1 = boolval($params['card_network_1']);
        //磁盘BPS-----card_bps_1
        $card_bps_1 = boolval($params['card_bps_1']);
        if($card_iops_1 || $card_network_1 || $card_bps_1){
            $systemMonitorData = $this->getSystemMonitorData();
            if($card_iops_1){
                $info['data']['card_iops_1'] = $systemMonitorData['card_iops_1'];
            }
            if($card_network_1){
                $info['data']['card_network_1'] = $systemMonitorData['card_network_1'];
            }
            if($card_bps_1){
                $info['data']['card_bps_1'] = $systemMonitorData['card_bps_1'];
            }
        }
        //日志统计-----card_log_1
        $card_log_1 = boolval($params['card_log_1']);
        if($card_log_1){
            $info['data']['card_log_1'] = $this->get_card_log_1($params['userval']);
        }
        //告警统计-----card_alarm_1
        $card_alarm_1 = boolval($params['card_alarm_1']);
        if($card_alarm_1){
            $info['data']['card_alarm_1'] = $this->get_card_alarm_1($params['userval']);
        }
        //告警统计-----card_alarm_1
        $card_alarm_2 = boolval($params['card_alarm_2']);
        if($card_alarm_2){
            $info['data']['card_alarm_2'] = $this->get_card_alarm_1($params['userval']);
        }

        //CPU-----card_basic_data
        $card_cpu_1 = boolval($params['card_cpu_1']);
        //内存-----card_memory_1
        $card_memory_1 = boolval($params['card_memory_1']);
        if($card_cpu_1 || $card_memory_1){
            $systemPercentage = $this->getCPURootLastTime();
            if($card_cpu_1){
                $info['data']['card_cpu_1'] = $systemPercentage['card_cpu_1'];
            }
            if($card_memory_1){
                $info['data']['card_memory_1'] = $systemPercentage['card_memory_1'];
            }
        }
        //文件-----card_file_1
        $card_file_1 = boolval($params['card_file_1']);
        if($card_file_1){
            $info['data']['card_file_1'] = $this->get_card_file_1($params['userval'],$hostInfo);
        }
        //数据库-----card_db_1
        $card_db_1 = boolval($params['card_db_1']);
        if($card_db_1){
            $info['data']['card_db_1'] = $this->get_card_db_1($params['userval'],$hostInfo);
        }
        //任务日志-----card_task_log_1
        $card_task_log_1 = boolval($params['card_task_log_1']);
        if($card_task_log_1){
            $info['data']['card_task_log_1'] = $this->get_card_task_log_1();
        }
        // //系统日志-----card_system_log_1
        $card_system_log_1 = boolval($params['card_system_log_1']);
        if($card_system_log_1){
            $info['data']['card_system_log_1'] = $this->get_card_system_log_1();
        }
        // //虚拟机-----card_vm_1
        $card_vm_1 = boolval($params['card_vm_1']);
        if($card_vm_1){
            $info['data']['card_vm_1'] = $this->get_card_vm_1($params['userval'],$hostInfo);
        }
        //操作系统-----card_os_1
        $card_os_1 = boolval($params['card_os_1']);
        if($card_os_1){
            $info['data']['card_os_1'] = $this->get_card_os_1($params['userval'],$hostInfo);
        }
        //系统告警-----card_system_alarm_1
        $card_system_alarm_1 = boolval($params['card_system_alarm_1']);
        if($card_system_alarm_1){
            $info['data']['card_system_alarm_1'] = $this->get_card_system_alarm_1();
        }
        //任务告警-----card_task_alarm_1
        $card_task_alarm_1 = boolval($params['card_task_alarm_1']);
        if($card_task_alarm_1){
            $info['data']['card_task_alarm_1'] = $this->get_card_task_alarm_1($params['userval']);
        }

        //近7天存储摘要-----card_storage_7days_1
        $card_storage_7days_1 = boolval($params['card_storage_7days_1']);
        if($card_storage_7days_1){
            $info['data']['card_storage_7days_1'] = $this->get_card_copy_archive_7days_1();
        }
        //数据库实时-----card_db_2
        $card_db_2 = boolval($params['card_db_2']);
        if($card_db_2){
            $info['data']['card_db_2'] = $this->get_card_db_2();
        }
        //365-----card_365_2
        $card_365_2 = boolval($params['card_365_2']);
        if($card_365_2){
            $info['data']['card_365_2'] = $this->get_card_365_2($params['userval']);
        }
        //近7天任务摘要-----card_task_7days_1
        $card_task_7days_1 = boolval($params['card_task_7days_1']);
        if($card_task_7days_1){
            $info['data']['card_task_7days_1'] = $this->get_card_task_7days_1();
        }
        //CDM-----card_CDM_1
        $card_CDM_1 = boolval($params['card_CDM_1']);
        if($card_CDM_1){
            $info['data']['card_CDM_1'] = $this->get_card_CDM_1($params['userval']);
        }
        //NAS-----card_nas_1
        $card_nas_1 = boolval($params['card_nas_1']);
        if($card_nas_1){
            $info['data']['card_nas_1'] = $this->get_card_nas_1($params['userval'],$hostInfo);
        }
        //实时容灾-----card_volcdp_1
        $card_volcdp_1 = boolval($params['card_volcdp_1']);
        if($card_volcdp_1){
            $info['data']['card_volcdp_1'] = $this->get_card_volcdp_1($params['userval'],$hostInfo);
        }
        //AWS-----card_aws_1
        $card_aws_1 = boolval($params['card_aws_1']);
        if($card_aws_1){
            $info['data']['card_aws_1'] = $this->get_card_aws_1($params['userval'],$hostInfo);
        }
        //HADOOP-----card_hadoop_1
        $card_hadoop_1 = boolval($params['card_hadoop_1']);
        if($card_hadoop_1){
            $info['data']['card_hadoop_1'] = $this->get_card_hadoop_1($params['userval'],$hostInfo);
        }
        //OBS-----card_obs_1
        $card_obs_1 = boolval($params['card_obs_1']);
        if($card_obs_1){
            $info['data']['card_obs_1'] = $this->get_card_obs_1($params['userval'],$hostInfo);
        }
        //2025年8月26号新增个人用户首页相关接口
        //获取存储和近7天的存储信息
        $card_storage_1 = boolval($params['card_storage_1']);
        if($card_storage_1){
            $info['data']['card_storage_1'] = $this->get_card_storage_1($params['userval'],$hostInfo);
        }
        //获取各个模块的信息汇总,受保护个数以及是否有授权等等
        $card_all_module_backup_data_1 = boolval($params['card_all_module_backup_data_1']);
        if($card_all_module_backup_data_1){
            $info['data']['card_all_module_backup_data_1'] = $this->get_card_all_module_backup_data_1($params['userval'],$hostInfo);
        }

        //-----------------------------------以下是项目版蓝色首页接口--------------------------
        //概览+任务
        $card_basic_task_data_1 = boolval($params['card_basic_task_data_1']);
        if($card_basic_task_data_1){
            $info['data']['card_basic_task_data_1'] = $this->get_card_basic_task_data_1($params['card_basic_task_data_1_params']);
        }
        //存储
        $card_storage_all_1 = boolval($params['card_storage_all_1']);
        if($card_storage_all_1){
            $info['data']['card_storage_all_1'] = $this->get_card_storage_all_1($params['card_storage_all_1_params'],$params['userval']);
        }
        //告警
        $card_alarm_all_1 = boolval($params['card_alarm_all_1']);
        if($card_alarm_all_1){
            $info['data']['card_alarm_all_1'] = $this->get_card_alarm_all_1($params['card_alarm_all_1_params']);
        }
        //数据保护
        $card_data_protect_all_1 = boolval($params['card_data_protect_all_1']);
        if($card_data_protect_all_1){
            $info['data']['card_data_protect_all_1'] = $this->get_card_data_protect_all_1($params['card_data_protect_all_1_params']);
        }
        //系统监控
        $card_system_alarm_all_1 = boolval($params['card_system_alarm_all_1']);
        if($card_system_alarm_all_1){
            $info['data']['card_system_alarm_all_1'] = $this->get_card_system_alarm_all_1($params['card_system_alarm_all_1_params']);
        }
        //-----------------------------------以上是项目版蓝色首页接口--------------------------

        return $info;
    }



    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2024/1/9
     * @return unknown
     */
    public function get_card_basic_task_data_1($params = ""){
        //统计累计保护总数
        $accumulateData = $this->getAccumulatedData("");
        //获取日志信息
        $valueLog = $this->get_card_log_1("");
        //获取任务数据
        $taskValue = $this->get_card_current_job_1("");
        $info = array(
            //统计累计运行天数
            "value_days"=> $this->getRunningDays(), //累计运行时间,单位天
            //统计累计保护数据
            "value_size_int"=> $accumulateData['value_int'], //单位B
            "value_size"=> $accumulateData['value'], //单位换算后保留小数点后两位//累计保护数据
            "value_size_unit"=> $accumulateData['unit'],//累计保护数据单位
            //统计操作日志数量
            'value_task_log_count' => $valueLog['value_task_log_count'],
            'value_system_log_count' => $valueLog['value_system_log_count'],
            //任务相关
            'task_info' =>$taskValue,
        );
        return $info;

    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2024/1/9
     * @return unknown
     */
    public function get_card_storage_all_1($params,$userVal){
        //存储概览信息
        $storageInfo = $this->getStorageInfo($userVal);
        //存储近期情况
        $storageRecentInfo = $this->getStorageRecentInfo($params['recent_days']);
        $info =  array(
            'storageInfo' => $storageInfo,
            'storageRecentInfo' => $storageRecentInfo,
        );
        return $info;
    }

    /**获取存储概览信息
     * @return void
     */
    private function getStorageInfo($userVal){
        //获取存储数据
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $sql = "select storage_uuid, status, total_size, free_size from bd_storage_resource where user_uuid = ?";
        $data = $this->dbSelect($sql, array($userVal));
        $online = 0;    //在线
        $offline = 0;   //离线
        $totalSize = 0; //存储总量
        $freeSize = 0;  //剩余容量
        foreach ($data as $d) {
            if(intval($d['status']) == xphp_get_config('app')['FLAG']['SET']){
                $online ++;
            }else{
                $offline ++;
            }
            $totalSize += intval($d['total_size']);
            $freeSize += intval($d['free_size']);
        }

        $usedSize = $totalSize - $freeSize; //已用容量
        $usedSizeInfo = v1_calsize_to_value_and_unit($usedSize);

        $totalSizeInfo = v1_calsize_to_value_and_unit($totalSize);
        $freeSizeInfo = v1_calsize_to_value_and_unit($freeSize);

        return array(
            'online_num' => $online,
            'offline_num' => $offline,
            'total_size_info' => array(
                'value' => $totalSizeInfo['value'],
                'unit' => $totalSizeInfo['unit'],
                'intval' => $totalSize
            ),
            'used_size_info' => array(
                'value' => $usedSizeInfo['value'],
                'unit' => $usedSizeInfo['unit'],
                'intval' => $usedSize
            ),
            'free_size_info' => array(
                'value' => $freeSizeInfo['value'],
                'unit' => $freeSizeInfo['unit'],
                'intval' => $freeSize
            )
        );
    }

    /**
     * 存储近期使用情况
     * @return void
     */
    public function getStorageRecentInfo($days){
        $sql = "select unix_timestamp(date) date,  backup_write_size, copy_write_size, 	archive_write_size from bd_storage_monitor 
                order by id desc limit ?, ?";
        $data = $this->dbSelect($sql, array(0, $days));
        $info = array();
        if(empty($data)){
            for ($i = $days - 1; $i >= 0; $i--) {
                $dateTime = strtotime("-$i day");
                $info[] = array(
                    'date' => date("m-d", $dateTime),
        
                    'backup_data' => 0,
                    'backup_data_des' => 0,
                    'backup_data_des_unit' => 'B',
        
                    'copy_data' => 0,
                    'copy_data_des' => 0,
                    'copy_data_des_unit' => 'B',
        
                    'archived_data' => 0,
                    'archived_data_des' => 0,
                    'archived_data_des_unit' => 'B',
                );
            }
            return $info;

        }
        foreach ($data as $d){
            $dateTime = intval($d['date']) - 3600*24;
            $backupValueAndUnit = v1_calsize_to_value_and_unit($d['backup_write_size']);
            $copyValueAndUnit = v1_calsize_to_value_and_unit($d['copy_write_size']);
            $archiveValueAndUnit = v1_calsize_to_value_and_unit($d['archive_write_size']);

            $info[] = array(
                'date' => date("m-d", $dateTime),

                'backup_data' => intval($d['backup_write_size']),
                'backup_data_des' => $backupValueAndUnit['value'],
                'backup_data_des_unit' => $backupValueAndUnit['unit'],

                'copy_data' => intval($d['copy_write_size']),
                'copy_data_des' => $copyValueAndUnit['value'],
                'copy_data_des_unit' => $copyValueAndUnit['unit'],

                'archived_data' => intval($d['archive_write_size']),
                'archived_data_des' => $archiveValueAndUnit['value'],
                'archived_data_des_unit' => $archiveValueAndUnit['unit'],
            );
        }
        $info = array_reverse($info);
        return $info;
    }

    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2024/1/9
     * @return unknown
     */
    public function get_card_alarm_all_1($params){
        $userVal = xphp_get_user_info()['userUuid'];
        $alarm_time_day = $params['alarm_time_day']; //获取到的数据为1,7,30,90,单位为天
        $sql_task = "SELECT COUNT(*) as total,SUM(CASE WHEN alarm_level = 2 THEN 1 ELSE 0 END) as count2,SUM(CASE WHEN alarm_level = 3 THEN 1 ELSE 0 END) as count3 FROM bd_task_alarm where alarm_level != 1 and user_uuid = ? AND alarm_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY);";
        $sql_system = "SELECT COUNT(*) as total,SUM(CASE WHEN alarm_level = 2 THEN 1 ELSE 0 END) as count2,SUM(CASE WHEN alarm_level = 3 THEN 1 ELSE 0 END) as count3 FROM bd_system_alarm WHERE alarm_time >= DATE_SUB(CURDATE(), INTERVAL ? DAY);";
        $result_task = $this->dbSelect($sql_task, array($userVal,$alarm_time_day));
        $result_system = $this->dbSelect($sql_system,array($alarm_time_day));
        $info = array(
            'value_task_alarm_count' => intval($result_task[0]['total']),
            'value_task_alarm_level_warning' =>intval($result_task[0]['count2']),
            'value_task_alarm_level_error' =>intval($result_task[0]['count3']),

            'value_system_alarm_count' => intval($result_system[0]['total']),
            'value_system_alarm_level_warning' =>intval($result_system[0]['count2']),
            'value_system_alarm_level_error' =>intval($result_system[0]['count3']),

        );
        return $info;



    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2024/1/9
     * @return unknown
     */
    public function get_card_data_protect_all_1($params){
        //获取授权信息
        $permission = xphp_get_user_info()['permission'];
        //虚拟机
        $vmsInfo = (in_array('vmprotect', $permission) && in_array('vcenter_manager', $permission))?$this->getVmInfo():array();
        //公有云
        $publicCloudInfo = (in_array('awsprotect', $permission) && in_array('cloud_platform', $permission))?$this->getPublicCloudInfo():array();
        //私有云
        $privateCloudInfo = (in_array('vmprotect', $permission) && in_array('cloud_platform_private', $permission))?$this->getPrivateCloudInfo():array();
        //NAS
        $nasInfo = (in_array('nas_protect', $permission) && in_array('nasmanager', $permission))?$this->getNasInfo():array();
        //实时容灾
        $cdpInfo = (in_array('vol_cdp_protect', $permission) && in_array('client', $permission))?$this->getCdpInfo():array();
        //客户端
        $clientInfo = (in_array('host_protect', $permission) && in_array('client', $permission))?$this->getClientInfo():array();
        // //虚拟实验室
        $labInfo = (in_array('data_verification', $permission))?$this->getLabInfo():array();
        //M365
        $m365Info = (in_array('office365_protect', $permission) && in_array('exchange_organization', $permission))?$this->getM365Info():array();
        //hadoop
        $hadoopInfo  = (in_array('hadoop_protect', $permission) && in_array('hadoop_cluster', $permission))?$this->getHadoopInfo():array();
        //对象存储
        $obsInfo = (in_array('obs_protect', $permission) && in_array('obsmanager', $permission))?$this->getObsInfo():array();
        $info = array(
            'vm_info' => $vmsInfo,
            'public_cloud_info' => $publicCloudInfo,
            'private_cloud_info' => $privateCloudInfo,
            'nas_info' => $nasInfo,
            'cdp_info' => $cdpInfo,
            'client_info' => $clientInfo,
            'lab_info' => $labInfo,
            'm365_info' => $m365Info,
            'hadoop_info' => $hadoopInfo,
            'obs_info' => $obsInfo
        );
        //过滤掉空值
        $info = array_filter($info, function($each) {
            if(empty($each)){
                return false;
            }else{
                return true;
            }
        });
        return $info;


    }

    //虚拟机
    private function getVmInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        $openstackDes = implode("','", xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack']);
        $cloudDes = implode("','", xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud']);
        //获取虚拟机总数
        $sql = "select count(distinct vt.tree_id) as total from vm_tree vt left join vm_vcenter vv on vv.vcenter_uuid = vt.vcenter_uuid 
        left join mt_user_resource mur on vt.uuid = mur.vm_uuid and mur.resource_type = 3
        where (mur.user_uuid = ? or vv.user_uuid = ? ) 
        and vt.type = ? and vt.display_mode = ? and vv.hypervisor_type not in ('".$openstackDes."') and vv.hypervisor_type not in ('".$cloudDes."')
        ";
        $data = $this->dbSelect($sql , array($userVal,$userVal,xphp_get_config('vm')['VM_TREE_TYPE']['VM'],xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']));
        $vmNum = intval($data[0]['total']);
        //受保护个数
        $sql = "select count(vml.machine_id) as protect from bd_task bt, vm_task vt, vm_machine_list vml where bt.task_uuid = vt.task_uuid and bt.task_uuid = vml.task_uuid and bt.user_uuid = ? and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ? and vt.hypervisor_type not in ('".$openstackDes."') and vt.hypervisor_type not in ('".$cloudDes."')";
        $data = $this->dbSelect($sql , array($userVal, xphp_get_config('app')['FLAG']['UNSET'],xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('task')['TASKTYPE']['BACKUP']));
        $protectNum = intval($data[0]['protect']);
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.user_uuid = ? and bbt.task_type = ? and bbt.copy_flag = ? and vbt.hypervisor_type not in ('".$openstackDes."') and vbt.hypervisor_type not in ('".$cloudDes."')";
        $data = $this->dbSelect($sql, array($userVal,xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize($backupSize,true);

        return array(
            'vm_num' => $vmNum,
            'protect_num' => $protectNum,
            'backup_size' => $backupSize,
            'backup_size_des' => $backupSizeDes
        );
    }

    //公有云
    private function getPublicCloudInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        $cloudDes = implode("','", xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud']);
        //获取实例总数
        $sql = "select count(distinct vt.tree_id) as total from vm_tree vt 
		left join vm_vcenter vv on vv.vcenter_uuid = vt.vcenter_uuid 
        left join mt_user_resource mur on vt.uuid = mur.vm_uuid and mur.resource_type = 3
        where (mur.user_uuid = ? or vv.user_uuid = ? ) 
        and vt.type = ? and vv.hypervisor_type in ('".$cloudDes."')";
        $data = $this->dbSelect($sql , array($userVal,$userVal, xphp_get_config('vm')['VM_TREE_TYPE']['VM']));
        $vmNum = intval($data[0]['total']);
        //受保护个数
        $sql = "select count(vml.machine_id) as protect from bd_task bt, vm_task vt, vm_machine_list vml where bt.task_uuid = vt.task_uuid and bt.task_uuid = vml.task_uuid and bt.user_uuid = ? and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ? and vt.hypervisor_type in ('".$cloudDes."')";
        $data = $this->dbSelect($sql , array($userVal, xphp_get_config('app')['FLAG']['UNSET'],xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('task')['TASKTYPE']['BACKUP']));
        $protectNum = intval($data[0]['protect']);
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.user_uuid = ? and bbt.task_type = ? and bbt.copy_flag = ? and vbt.hypervisor_type in ('".$cloudDes."')";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize($backupSize,true);

        return array(
            'vm_num' => $vmNum,
            'protect_num' => $protectNum,
            'backup_size' => $backupSize,
            'backup_size_des' => $backupSizeDes
        );
    }
    //私有云
    private function getPrivateCloudInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        $openstackDes = implode("','", xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack']);
        //获取实例总数
        $sql = "select count(distinct vt.tree_id) as total from vm_tree vt 
		left join vm_vcenter vv on vv.vcenter_uuid = vt.vcenter_uuid 
        left join mt_user_resource mur on vt.uuid = mur.vm_uuid and mur.resource_type = 3
        where (mur.user_uuid = ? or vv.user_uuid = ? ) 
        and vt.type = ? and vv.hypervisor_type in ('".$openstackDes."')";
        $data = $this->dbSelect($sql , array($userVal,$userVal, xphp_get_config('vm')['VM_TREE_TYPE']['VM']));
        $vmNum = intval($data[0]['total']);
        //受保护个数
        $sql = "select count(vml.machine_id) as protect from bd_task bt, vm_task vt, vm_machine_list vml where bt.task_uuid = vt.task_uuid and bt.task_uuid = vml.task_uuid and bt.user_uuid = ? and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ? and vt.hypervisor_type in ('".$openstackDes."')";
        $data = $this->dbSelect($sql , array($userVal, xphp_get_config('app')['FLAG']['UNSET'],xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('task')['TASKTYPE']['BACKUP']));
        $protectNum = intval($data[0]['protect']);
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.user_uuid = ? and bbt.task_type = ? and bbt.copy_flag = ? and vbt.hypervisor_type in ('".$openstackDes."')";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize($backupSize,true);

        return array(
            'vm_num' => $vmNum,
            'protect_num' => $protectNum,
            'backup_size' => $backupSize,
            'backup_size_des' => $backupSizeDes
        );
    }
    //NAS
    public function getNasInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        //获取设备总数
        $sql = "select count(id) as total from nas_storage_resource";
        $data = $this->dbSelect($sql);
        $nasNum = intval($data[0]['total']);
        //受保护个数
        $sql = "select count(distinct nt.nas_uuid) as total from bd_task bt, nas_task nt where bt.task_uuid = nt.task_uuid and bt.user_uuid = ?  and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        $data = $this->dbSelect($sql , array($userVal, xphp_get_config('app')['FLAG']['UNSET'], xphp_get_config('module')['MODULE_TYPE']['NAS'], xphp_get_config('task')['TASKTYPE']['BACKUP']));
        $protectNum = intval($data[0]['protect']);
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('module')['MODULE_TYPE']['NAS'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize($backupSize,true);

        return array(
            'nas_num' => $nasNum,
            'protect_num' => $protectNum,
            'backup_size' => $backupSize,
            'backup_size_des' => $backupSizeDes
        );
    }
    //实时容灾
    private function getCdpInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        
        //主机总数
        $sql = "select agent_uuid from bd_agent where agent_type not in (3, 4);";
        $data = $this->dbSelect($sql);
        $countAgentNum = 0;
        if(!empty($data)){
            foreach($data as $each){
                //获取真实user_uuid
                $user_uuid = $this->getClientUuid($each['agent_uuid'],$userVal);
                if($user_uuid == $userVal){
                    $countAgentNum++;
                    continue;
                }
            }
        }
        $agentNum = $countAgentNum;

        //受保护主机个数
        $sql = "SELECT count(task_uuid) as protect_num from bd_task where user_uuid = ? and delete_flag = ? and task_type = ? and module_type = ? ";
        $data = $this->dbSelect($sql, array($userVal,xphp_get_config('app')['FLAG']['UNSET'], xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'], xphp_get_config('task')['TASKTYPE']['VOL_CDP_BACKUP']));
        $protectNum = $data[0]['protect_num'];

        //累计实时容灾数据
        $sql = "select vol_cdp_data from bd_user_extension where user_uuid = ? ";
        $data = $this->dbSelect($sql,array($userVal));
        $totalBackupSize = intval($data[0]['vol_cdp_data']);
        $totoalBackupSizeDes = v1_calsize($totalBackupSize,true);
        //今日实时容灾累计保护数据
        $todayStart = date('Y-m-d') . " 00:00:00";
        $todayEnd = date('Y-m-d') . " 23:59:59";
        $sql = "SELECT SUM(cvbvs.backup_file_size + cvbvs.log_file_total_size) total 
        FROM cdp_vol_backup_vol_set cvbvs,cdp_vol_backup_agent cvba,bd_backup_timepoint bbt 
        WHERE cvbvs.backup_agent_id = cvba.id 
        AND cvba.timepoint_uuid = bbt.timepoint_uuid 
        AND bbt.user_uuid = ? 
        AND cvba.storage_location !=0 
        AND cvbvs.end_timestamp BETWEEN ? AND ?";
        $sqlParams = array($userVal,$todayStart, $todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $todayBackupSize = intval($data[0]['total']);
        $todayBackupSizeDes = v1_calsize($todayBackupSize,true);
        return array(
            'agent_num' => $agentNum,
            'protect_num' => $protectNum,
            'today_backup_size' => $todayBackupSize,
            'today_backup_size_des' => $todayBackupSizeDes,
            'total_backup_size' => $totalBackupSize,
            'total_backup_size_des' => $totoalBackupSizeDes
        );
    }

    //客户端
    private function getClientInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        //获取客户端总数
        $sql = "select agent_uuid from bd_agent where agent_type in (1,2)";
        $data = $this->dbSelect($sql);
        $countClientNum = 0;
        if(!empty($data)){
            foreach($data as $each){
                //获取真实user_uuid
                $user_uuid = $this->getClientUuid($each['agent_uuid'],$userVal);
                if($user_uuid == $userVal){
                    $countClientNum++;
                    continue;
                }
            }
        }
        $clientNum = $countClientNum;
        //文件备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $fsBackupSize = intval($data[0]['backup_size']);
        $fsBackupSizeDes = v1_calsize($fsBackupSize,true);

        //数据库备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, db_backup_timepoint dbt where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('module')['MODULE_TYPE']['DB'], xphp_get_config('task')['TASKTYPE']['DB_BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $dbBackupSize = intval($data[0]['backup_size']);
        $dbBackupSizeDes = v1_calsize($dbBackupSize,true);

        //操作系统备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, os_backup_timepoint obt where bbt.timepoint_uuid = obt.timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('module')['MODULE_TYPE']['OS'], xphp_get_config('task')['TASKTYPE']['OS_BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $osBackupSize = intval($data[0]['backup_size']);
        $osBackupSizeDes = v1_calsize($osBackupSize,true);

        return array(
            'client_num' => $clientNum,
            'fs_backup_size' => $fsBackupSize,
            'fs_ackup_size_des' => $fsBackupSizeDes,
            'db_backup_size' => $dbBackupSize,
            'db_ackup_size_des' => $dbBackupSizeDes,
            'os_backup_size' => $osBackupSize,
            'os_ackup_size_des' => $osBackupSizeDes,
        );
    }

    //虚拟实验室
    private function getLabInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        //获取虚拟实验室总数
        $sql = "select count(virtual_lab_uuid) as total from sr_virtual_lab where user_uuid = ?";
        $data = $this->dbSelect($sql,array($userVal));
        $labNum = intval($data[0]['total']);
        //验证次数
        $sql = "select id, error_code from bd_history_task where user_uuid = ? and module_type = ? and task_type = ? ";
        $data = $this->dbSelect($sql , array($userVal, xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('task')['TASKTYPE']['SURE_BACKUP']));
        $verifyNum = count($data);
        $faildNum = 0;
        foreach ($data as $d) {
            if($d['error_code'] != 0){
                $faildNum ++;
                continue;
            }
        }

        //验证中数量
        $sql = "select count(distinct ssb.virtual_lab_uuid) as run_num from bd_task bt, sr_sure_backup ssb where bt.task_uuid = ssb.task_uuid and bt.user_uuid = ? and bt.module_type = ? and bt.task_type = ? and bt.delete_flag = ? ";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('task')['TASKTYPE']['SURE_BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $runNum = intval($data[0]['run_num']);

        return array(
            'lab_num' => $labNum,
            'run_num' => $runNum,
            'verify_num' => $verifyNum,
            'faild_num' => $faildNum
        );
    }
    //M365
    private function getM365Info(){
        $userVal = xphp_get_user_info()['userUuid'];
        //获取组织总数
        $sql = "select count(id) as total from m365_organization";
        $data = $this->dbSelect($sql);
        $m365Num = intval($data[0]['total']);
        //受保护个数
        $sql = "select count(distinct mt.organization_uuid) as total from bd_task bt, m365_task mt where bt.task_uuid = mt.task_uuid and bt.user_uuid = ? and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        $data = $this->dbSelect($sql , array($userVal, xphp_get_config('app')['FLAG']['UNSET'], xphp_get_config('module')['MODULE_TYPE']['M365'], xphp_get_config('task')['TASKTYPE']['BACKUP']));
        $protectNum = intval($data[0]['protect']);
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, m365_backup_timepoint mbt where bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('module')['MODULE_TYPE']['M365'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize($backupSize,true);

        return array(
            'm365_num' => $m365Num,
            'protect_num' => $protectNum,
            'backup_size' => $backupSize,
            'backup_size_des' => $backupSizeDes
        );
    }
    //hadoop
    private function getHadoopInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        //获取集群总数
        $sql = "select count(id) as total from hadoop_cluster";
        $data = $this->dbSelect($sql);
        $hadoopNum = intval($data[0]['total']);
        //获取受保护的hadoop集群
        $sqlProtect ="select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.sub_module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $dataProtect = $this->dbSelect(
            $sqlProtect, array(
                xphp_get_config('app','FLAG')['UNSET'],
                xphp_get_config('module','MODULE_TYPE')['FS'],
                xphp_get_config('module','SUBMODULE_TYPE')['HADOOP'],
                xphp_get_config('task','TASKTYPE')['BACKUP'],
                $userVal
            )
        );
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.sub_module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module','SUBMODULE_TYPE')['HADOOP'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize($backupSize,true);
        return array(
            'hadoop_num' => $hadoopNum,
            'protect_num' =>  $dataProtect[0]['total'],
            'backup_size' => $backupSize,
            'backup_size_des' => $backupSizeDes
        );
    }
    //obs 
    private function getObsInfo(){
        $userVal = xphp_get_user_info()['userUuid'];
        //获对象存储总数
        $sql  = "select count(id) as total from obs_resource";
        $data = $this->dbSelect($sql);
        $obsNum = intval($data[0]['total']);
        //获取受保护的对象存储
        $sqlProtect ="select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.sub_module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $dataProtect = $this->dbSelect(
            $sqlProtect, array(
                xphp_get_config('app','FLAG')['UNSET'],
                xphp_get_config('module','MODULE_TYPE')['FS'],
                xphp_get_config('module','SUBMODULE_TYPE')['OBS'],
                xphp_get_config('task','TASKTYPE')['BACKUP'],
                $userVal
            )
        );
        //备份数据
        $sql = "select sum(bbt.write_size) as backup_size from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.user_uuid = ? and bbt.module_type = ? and bbt.sub_module_type = ? and bbt.task_type = ? and bbt.copy_flag = ? ";
        $data = $this->dbSelect($sql, array($userVal, xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module','SUBMODULE_TYPE')['OBS'], xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['UNSET']));
        $backupSize = intval($data[0]['backup_size']);
        $backupSizeDes = v1_calsize($backupSize,true);
        return array(
            'obs_num' => $obsNum,
            'protect_num' =>  $dataProtect[0]['total'],
            'backup_size' => $backupSize,
            'backup_size_des' => $backupSizeDes
        );
        
    }



    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2024/1/9
     * @return unknown
     */
    public function get_card_system_alarm_all_1($params){
        //获取node_uuid
        $node_uuid = $params['selectedNode'];
        $systemMonitorData = $this->getSystemMonitorData($node_uuid);
        $systemPercentage = $this->getCPURootLastTime($node_uuid);
        $info = array(
            'iops' => $systemPercentage['card_iops_1'],
            'network' => $systemMonitorData['card_network_1'],
            'bps' => $systemPercentage['card_bps_1'],
            'cpu' =>  $systemPercentage['card_cpu_1'],
            'memory' => $systemPercentage['card_memory_1'],
        );
        return $info;
    }









    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_basic_data($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        //当前任务个数
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu where  bt.delete_flag = ? and bt.user_uuid = bu.user_uuid and bu.user_uuid = ?";
        $sqlCountParams = array(xphp_get_config('app', 'FLAG')['UNSET'],$userVal);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        // 历史任务个数
        $sqlCount = "select count(distinct id) as total from bd_history_task where user_uuid = ?";
        $sqlCountParams = array($userVal);
        $hisCount = $this->dbSelect($sqlCount, $sqlCountParams);

        $accumulateData = $this->getAccumulatedData($userVal);
        $info = array(
            "value_days"=> $this->getRunningDays(), //累计运行时间,单位天
            "value_size_int"=> $accumulateData['value_int'], //单位B
            "value_size"=> $accumulateData['value'], //单位换算后保留小数点后两位//累计保护数据
            "value_size_unit"=> $accumulateData['unit'],//累计保护数据单位
            "value_current_count"=> $count[0]['total'],//当前任务总数
            "value_history_count"=> $hisCount[0]['total'],//历史任务总数
        );
        return $info;
    }

    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_current_job_1($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        //当前任务总计
        $sqlCurrent =  "select count(bt.id) as total from bd_task bt, bd_user bu where  
        bt.delete_flag = ? and bt.user_uuid = bu.user_uuid and bu.user_uuid = ?";
        $sqlCurrentParams = array(xphp_get_config('app', 'FLAG')['UNSET'], $userVal);
        $resultCurrent = $this->dbSelect($sqlCurrent,$sqlCurrentParams);
        //
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu where  
        bt.delete_flag = ? and bt.user_uuid = bu.user_uuid and bu.user_uuid = ? and bt.task_status = ?";
        // 运行中
        $sqlCountRun = array(xphp_get_config('app', 'FLAG')['UNSET'], $userVal, xphp_get_config('task','TASKSTATUS')['RUNNING']);
        $countRun = $this->dbSelect($sqlCount, $sqlCountRun);
        // 等待中
        $sqlCountWait = array(xphp_get_config('app', 'FLAG')['UNSET'], $userVal, xphp_get_config('task','TASKSTATUS')['WAITTING']);
        $countWait = $this->dbSelect($sqlCount, $sqlCountWait);
        // 成功 异常  失败 从bd_task_alarm中取
        $sqlAlarm = "select count(task_alarm_id) as total from bd_task_alarm where user_uuid = ? and alarm_level = ? and solved_flag = ?";
        //成功从bd_history_task中取
        $sqlhis = "select count(id) as total from bd_history_task where user_uuid = ? and error_code = ?";
        //成功
        $countSuccess = $this->dbSelect($sqlhis, array($userVal,0));
        //异常  level 2
        $countAbnormal = $this->dbSelect($sqlAlarm, array($userVal, 2,2));
        //失败  level 3
        $countFail = $this->dbSelect($sqlAlarm, array($userVal, 3,2));

        $count_all = intval($countRun[0]['total']) + intval($countWait[0]['total']) + intval($countSuccess[0]['total'])
            + intval($countAbnormal[0]['total']) + intval($countFail[0]['total']);

        $info = array(
            "running" => array(
                "value_count" => $countRun[0]['total'],
                "value_percent" => $count_all ? round(($countRun[0]['total'] / $count_all) * 100,1) : 0,
            ),
            "wait" => array(
                "value_count" => $countWait[0]['total'],
                "value_percent" => $count_all ? round(($countWait[0]['total'] / $count_all) * 100,1) : 0,
            ),
            "success" => array(
                "value_count" => $countSuccess[0]['total'],
                "value_percent" => $count_all ? round(($countSuccess[0]['total'] / $count_all) * 100,1) : 0,
            ),
            "abnormal" => array(
                "value_count" => $countAbnormal[0]['total'],
                "value_percent" => $count_all ? round(($countAbnormal[0]['total'] / $count_all) * 100,1) : 0,
            ),
            "fail" => array(
                "value_count" => $countFail[0]['total'],
                "value_percent" => $count_all ? round(($countFail[0]['total'] / $count_all) * 100,1) : $count_all,
            ),
            "all" => array(
                "value_count" => $count_all,
            ),
            "current_total" =>array(
                "value_count" => $resultCurrent[0]['total'],
            )
        );
        return $info;
    }

    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_backup_storage_1($userVal){
        $useMode = xphp_get_config('resource','BD_STORAGE_USE_MODE')['BACKUP'];
        $storageInfo = $this->pGetStorageInfo($useMode,$userVal);
        //获取总容量
        $bakStorageCapacity = $this->pGetIntToSizeInfo($storageInfo['total']);
        //获取已使用
        $usedCapacity = $this->pGetIntToSizeInfo($storageInfo['used']);
        //获取剩余
        $remainingCapacity = $this->pGetIntToSizeInfo($storageInfo['free']);
        $info = array(
            'value_storage_num' => $storageInfo['total_num'],
            'value_storage_size_all_int' => $bakStorageCapacity['size'],
            'value_storage_size_all' => $bakStorageCapacity['value'],
            'value_storage_size_all_unit' => $bakStorageCapacity['unit'],

            'value_storage_size_used_int' => $usedCapacity['size'],
            'value_storage_size_used' => $usedCapacity['value'],
            'value_storage_size_used_unit' => $usedCapacity['unit'],

            'value_storage_size_free_int' => $remainingCapacity['size'],
            'value_storage_size_free' => $remainingCapacity['value'],
            'value_storage_size_free_unit' => $remainingCapacity['unit'],

        );
        return $info;

    }

    /**
     * 获取数据  副本改成副本归档了
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_copy_storage_pool_1($userVal){
        $storageInfo = $this->getCopyAcrchivalSize($userVal);
        //获取总容量
        $bakStorageCapacity = $this->pGetIntToSizeInfo($storageInfo['total']);
        //获取已使用
        $usedCapacity = $this->pGetIntToSizeInfo($storageInfo['used']);
        //获取剩余
        $remainingCapacity = $this->pGetIntToSizeInfo($storageInfo['free']);
        $info = array(
            'value_storage_num' => $storageInfo['total_num'],
            'value_storage_size_all_int' => $bakStorageCapacity['size'],
            'value_storage_size_all' => $bakStorageCapacity['value'],
            'value_storage_size_all_unit' => $bakStorageCapacity['unit'],

            'value_storage_size_used_int' => $usedCapacity['size'],
            'value_storage_size_used' => $usedCapacity['value'],
            'value_storage_size_used_unit' => $usedCapacity['unit'],

            'value_storage_size_free_int' => $remainingCapacity['size'],
            'value_storage_size_free' => $remainingCapacity['value'],
            'value_storage_size_free_unit' => $remainingCapacity['unit'],
            'card_storage_7days_1' => $this->get_card_copy_archive_7days_1(),
        );
        return $info;

    }

    /**
     * 获取数据归档大小
     */
    public function getCopyAcrchivalSize($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $useMode1 = xphp_get_config('resource','BD_STORAGE_USE_MODE')['COPY'];
        $useMode2 = xphp_get_config('resource','BD_STORAGE_USE_MODE')['ARCHIVE'];
        $resourceHandler = new LogicIndex;
        $storage_list = $resourceHandler->pGetUserAllResource($userVal,8);
        //获取agent_uuid的集合
        $storage_list =array_column($storage_list, 'resource_uuid');
        $storage_uuids = implode(',', array_map(function($item) {return "'{$item}'";}, $storage_list));
        $sql = "select sum(total_size) as total_size, sum(free_size) as free_size, count(storage_uuid) as total_num from  bd_storage_resource where (use_mode = ? or use_mode = ?) ";
        if(!empty($storage_uuids)){
            $sql .= " and storage_uuid in (".$storage_uuids.")";
        }
        $data = $this->dbSelect($sql, array($useMode1,$useMode2));

        //返回已用容量
        $total = intval($data[0]['total_size']);
        $free = intval($data[0]['free_size']);
        $used = $total - $free;
        $info = array(
            'total' => $total,
            'free' => $free,
            'used' => $used,
            'total_num' => intval($data[0]['total_num'])
        );
        return $info;


    }


    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_Archival_storage_1($userVal){
        $useMode = xphp_get_config('resource','BD_STORAGE_USE_MODE')['ARCHIVE'];
        $storageInfo = $this->pGetStorageInfo($useMode,$userVal);
        //获取总容量
        $bakStorageCapacity = $this->pGetIntToSizeInfo($storageInfo['total']);
        //获取已使用
        $usedCapacity = $this->pGetIntToSizeInfo($storageInfo['used']);
        //获取剩余
        $remainingCapacity = $this->pGetIntToSizeInfo($storageInfo['free']);
        $info = array(
            'value_storage_num' => $storageInfo['total_num'],
            'value_storage_size_all_int' => $bakStorageCapacity['size'],
            'value_storage_size_all' => $bakStorageCapacity['value'],
            'value_storage_size_all_unit' => $bakStorageCapacity['unit'],

            'value_storage_size_used_int' => $usedCapacity['size'],
            'value_storage_size_used' => $usedCapacity['value'],
            'value_storage_size_used_unit' => $usedCapacity['unit'],

            'value_storage_size_free_int' => $remainingCapacity['size'],
            'value_storage_size_free' => $remainingCapacity['value'],
            'value_storage_size_free_unit' => $remainingCapacity['unit'],
            'card_storage_7days_1' => $this->get_card_storage_7days_1(),
        );
        return $info;

    }


    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_log_1($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        //获取任务日志总数
        $sql = "select count(id) as total from bd_task_log where running_flag = ? and user_uuid = ?";
        $sql_params = array(
            xphp_get_config('app', 'FLAG')['UNSET'],
            $userVal,
        );
        $sql_task_result = $this->dbSelect($sql,$sql_params);

        //获取系统日志总数
        $sql_system = "select count(id) as total from bd_system_log where user_uuid = ?";
        $sql_system_result = $this->dbSelect($sql_system,array($userVal,));
        return array(
            'value_task_log_count' => $sql_task_result[0]['total'],
            'value_system_log_count' => $sql_system_result[0]['total'],
        );
    }

    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_alarm_1($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $sql_task = "SELECT COUNT(*) as total,SUM(CASE WHEN alarm_level = 2 THEN 1 ELSE 0 END) as count2,SUM(CASE WHEN alarm_level = 3 THEN 1 ELSE 0 END) as count3 FROM bd_task_alarm where alarm_level != 1 and user_uuid = ?;";
        $sql_system = "SELECT COUNT(*) as total,SUM(CASE WHEN alarm_level = 2 THEN 1 ELSE 0 END) as count2,SUM(CASE WHEN alarm_level = 3 THEN 1 ELSE 0 END) as count3 FROM bd_system_alarm;";
        $result_task = $this->dbSelect($sql_task, array($userVal,));
        $result_system = $this->dbSelect($sql_system);
        $info = array(
            'value_task_alarm_count' => intval($result_task[0]['total']),
            'value_task_alarm_level_warning' =>intval($result_task[0]['count2']),
            'value_task_alarm_level_error' =>intval($result_task[0]['count3']),

            'value_system_alarm_count' => intval($result_system[0]['total']),
            'value_system_alarm_level_warning' =>intval($result_system[0]['count2']),
            'value_system_alarm_level_error' =>intval($result_system[0]['count3']),

        );
        return $info;
    }


    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_file_1($userVal,$hostInfo){
        //主机信息
        
        $module = xphp_get_config('module','MODULE_TYPE')['FS'];
        $taskType = xphp_get_config('task','TASKTYPE')['BACKUP'];
        $info = array(
            'totalNum' => $hostInfo['file']['module']['filebackup']['total'],
            'protectedNum' => $hostInfo['file']['module']['filebackup']['protected'],
            'backupData' => $this->pGetProtectData($module,$userVal,1),
        );
        return $info;
    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_db_1($userVal,$hostInfo){
        //主机信息
        $module = xphp_get_config('module','MODULE_TYPE')['DB'];
        $taskType = xphp_get_config('task','TASKTYPE')['DB_BACKUP'];
        $info = array(
            'totalNum' => $hostInfo['application']['module']['db_protect']['total'],
            'protectedNum' => $hostInfo['application']['module']['db_protect']['protected'],
            'backupData' => $this->pGetProtectData($module,$userVal),
        );
        return $info;
    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_vm_1($userVal,$hostInfo){
        $info = array(
            'totalNum' => $hostInfo['cloud']['module']['vmprotect']['total'],
            'protectedNum' => $hostInfo['cloud']['module']['vmprotect']['protected'],
            'backupData' => $this->pGetProtectData(xphp_get_config('module','MODULE_TYPE')['VM'],$userVal),
        );
        return $info;
    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_os_1($userVal,$hostInfo){
        //主机信息
        
        $module = xphp_get_config('module','MODULE_TYPE')['OS'];
        $taskType = xphp_get_config('task','TASKTYPE')['OS_BACKUP'];
        $info = array(
            'totalNum' => $hostInfo['machine']['module']['osbackup']['total'],
            'protectedNum' => $hostInfo['machine']['module']['osbackup']['protected'],
            'backupData' => $this->pGetProtectData($module,$userVal),
        );
        return $info;
    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_db_2(){
        $info = array(
            //生产主机
            'productHost' => $this->getDBCDPHost(xphp_get_config('db','DB_CDP_HOST_TYPE')['product']),
            //备份主机
            'standbyHost' => $this->getDBCDPHost(xphp_get_config('db','DB_CDP_HOST_TYPE')['standby']),
        );
        return $info;
    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_365_2($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];

        //获取该用户拥有的所有agent_uuid集合
        $resourceHandler = new LogicIndex;
        $agent_list = $resourceHandler->pGetUserAllResource($userVal,56);
        //获取agent_uuid的集合
        $agent_list =array_column($agent_list, 'resource_uuid');
        $agent_uuids = implode(',', array_map(function($item) {return "'{$item}'";}, $agent_list));
        $sql = "select m365_org.region,m365_org.online_flag 
        from m365_organization m365_org";
        if(!empty($agent_uuids)){
        $sql .= " where m365_org.organization_uuid in (".$agent_uuids.")";
        }
        $result = $this->dbSelect($sql);
        $info = array(
            'online' => array(
                'online' => 0,
                'offline' =>0,
            ),
            'server' => array(
                'online' => 0,
                'offline' =>0,
            ),
        );
        foreach ($result as $each){
            //region: server是100 
            if($each['region'] != 100){
                if($each['online_flag'] == 1){
                    $info['online']['online'] ++;
                }else{
                    $info['online']['offline'] ++;
                }
            }else{
                if($each['online_flag'] == 1){
                    $info['server']['online'] ++;
                }else{
                    $info['server']['offline'] ++;
                }
            }
        }

        return $info;

    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_CDM_1($userVal){
        $info = array(
            //验证数量
            'laboratoryNum' => $this->getLaboratoryNum($userVal),
            //实验室
            'verifyNum' => $this->getVerifyNum(false,$userVal),
            //验证中
            'verifyingNum' => $this->getVerifyNum(true,$userVal),
            //验证次数
            'verifyTimes' => $this->getVerifyTime(false,$userVal),
            //失败
            'falieTimes' => $this->getVerifyTime(true,$userVal),
        );

        return $info;
    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_task_log_1(){
        $sql = "select id, task_name, task_type, user_name, agent_name, module_type, submodule_type, 
                    error_code, op_time, description_key, description_param, log_level 
                from bd_task_log where running_flag = ? order by op_time desc limit 15";
        $result = $this->dbSelect($sql,array(xphp_get_config('app','FLAG')['UNSET']));
        $info = array();
        $LOGHandler = new Index();
        foreach ($result as $each){
            $info[] = array(
                'system_alarm_description' => $LOGHandler->getLogDesription(
                    xphp_get_config('log','LOGTYPE')['TASK'],
                    $each['error_code'],
                    $each['description_key'],
                    $each['description_param']
                ),
                'system_alarm_time' => $each['op_time'],
                'system_alarm_status_int' => $each['log_level'],
                "system_alarm_status_des" => xphp_get_desc('Pf','ALARM_LEVEL_DES')[$each['log_level']],
            );
        }
        return $info;

    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_system_log_1(){
        $sql = "select user_name, agent_name, error_code, op_time, description_key, description_param, log_level 
                from bd_system_log order by op_time desc limit 15";
        $result = $this->dbSelect($sql);
        $info = array();
        $LOGHandler = new Index();
        foreach ($result as $each){
            $info[] = array(
                'system_alarm_description' => $LOGHandler->getLogDesription(
                    xphp_get_config('log','LOGTYPE')['SYSTEM'],
                    $each['error_code'],
                    $each['description_key'],
                    $each['description_param']
                ),
                'system_alarm_time' => $each['op_time'],
                'system_alarm_status_int' => $each['log_level'],
                "system_alarm_status_des" => xphp_get_desc('Pf','ALARM_LEVEL_DES')[$each['log_level']],
            );
        }
        return $info;

    }

    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_system_alarm_1(){
        $sql = "select alarm_level, description_key, description_param, alarm_time, solved_flag, error_code 
                from bd_system_alarm where system_alarm_id is not null order by alarm_time desc limit 15";
        $result = $this->dbSelect($sql);
        $info = array();
        $LOGHandler = new Index();
        foreach ($result as $each){
            $info[] = array(
                'system_alarm_description' => $LOGHandler->getLogDesription(
                    xphp_get_config('log','LOGTYPE')['SYSTEM'],
                    $each['error_code'],
                    $each['description_key'],
                    $each['description_param']
                ),
                'system_alarm_time' => $each['alarm_time'],
                'system_alarm_status_int' => $each['alarm_level'],
                "system_alarm_status_des" => xphp_get_desc('Pf','ALARM_LEVEL_DES')[$each['alarm_level']],
                'system_alarm_response_status_int' => $each['solved_flag'],
                "system_alarm_response_status_des" => xphp_get_desc('Pf','ALARM_RESPOND_DES')[$each['solved_flag']],
            );
        }
        return $info;
    }
    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_task_alarm_1($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $sql = "select alarm_level, description_key, description_param, alarm_time, solved_flag, error_code 
                from bd_task_alarm where alarm_level != 1 and user_uuid = ? order by alarm_time desc limit 15";
        $result = $this->dbSelect($sql, $userVal);
        $info = array();
        $LOGHandler = new Index();
        foreach ($result as $each){
            $info[] = array(
                'system_alarm_description' => $LOGHandler->getLogDesription(
                    xphp_get_config('log','LOGTYPE')['TASK'],
                    $each['error_code'],
                    $each['description_key'],
                    $each['description_param']
                ),
                'system_alarm_time' => $each['alarm_time'],
                'system_alarm_status_int' => $each['alarm_level'],
                "system_alarm_status_des" => xphp_get_desc('Pf','ALARM_LEVEL_DES')[$each['alarm_level']],
                'system_alarm_response_status_int' => $each['solved_flag'],
                "system_alarm_response_status_des" => xphp_get_desc('Pf','ALARM_RESPOND_DES')[$each['solved_flag']],
            );
        }
        return $info;

    }



     /**
     * 获取副本+归档数据集合
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_copy_archive_7days_1(){
        $dates = $this->getPast7Days();
    
        $value_y_data = [
            'copy_size' => ['name' => xphp_get_lang('WEB_HOMEPAGE_COPY_ARCHIVE_STORAGE'), 'data' => array_fill(0, 7, 0)],
            'backup_size' => ['name' => xphp_get_lang('WEB_HOMEPAGE_BACKUP_STORAGE'), 'data' => array_fill(0, 7, 0)],
        ];
    
        // 使用getTimepointWriteSize获取最近7天的数据
        $timepointInfo = $this->getTimepointWriteSize(7);
    
        foreach ($timepointInfo as $entry) {
            // 将日期转换为Y-m-d格式
            $date = date('m-d', strtotime($entry['date']));
    
            $index = array_search($entry['date'], $dates); // 不需要转换日期格式

            if ($index !== false) {
                $value_y_data['backup_size']['data'][$index] = $entry['backup_data'];
                $value_y_data['copy_size']['data'][$index] = $entry['copy_data'];
            }
        }
    
        return [
            'value_x_data' => $dates,
            'value_y_data' => $value_y_data,
        ];
    }


    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */
    public function get_card_storage_7days_1(){
        $dates = $this->getPast7Days();

        $value_y_data = [
            'backup_size' => ['name' => xphp_get_lang('WEB_HOMEPAGE_BACKUP_STORAGE'), 'data' => array_fill(0, 7, 0)],
            'copy_size' => ['name' => xphp_get_lang('WEB_HOMEPAGE_COPY_ARCHIVE_STORAGE'), 'data' => array_fill(0, 7, 0)],
            'archive_size' => ['name' => xphp_get_lang('WEB_HOMEPAGE_ARCHIVE_STORAGE'), 'data' => array_fill(0, 7, 0)],
        ];
        $sql_size = "SELECT date, backup_write_size, copy_write_size, archive_write_size FROM bd_storage_monitor WHERE date >= ? AND date <= ?";
        $rows = $this->dbSelect($sql_size,array($dates[0],end($dates)));
        foreach ($rows as $row) {
            $index = array_search($row['date'], $dates);
            if ($index !== false) {
                $value_y_data['backup_size']['data'][$index] = $row['backup_write_size'];
                $value_y_data['copy_size']['data'][$index] = $row['copy_write_size'];
                $value_y_data['archive_size']['data'][$index] = $row['archive_write_size'];
            }
        }
        return [
            'value_x_data' => $dates,
            'value_y_data' => $value_y_data,
        ];
    }



    /**
     * 获取数据
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/11/23
     * @return unknown
     */

     public function get_card_task_7days_1(){
        $sql = "SELECT DATE(finish_time) AS date,error_code FROM bd_history_task WHERE finish_time >= DATE(NOW()) - INTERVAL 7 DAY";
        $result = $this->dbSelect($sql);
        $info = [
            'value_x_data' => [],
            'value_y_data' => [
                ['name' => xphp_get_lang('WEB_PLATFORM_DES_SUCCESSED'), 'data' => []],
                ['name' => xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL'), 'data' => []],
                ['name' => xphp_get_lang('WEB_PLATFORM_DES_DISCONTINUE'), 'data' => []],
                ['name' => xphp_get_lang('WEB_PUBLIC_FAILURE_HOMEPAGE'), 'data' => []],
            ],
        ];

        // Initialize the arrays
        for ($i = 6; $i >= 0; $i--) {
            $date = date("m-d", strtotime("-$i days"));
            $info['value_x_data'][] = $date;
            foreach ($info['value_y_data'] as &$value) {
                $value['data'][] = 0;
            }
        }

        // Count the tasks
        foreach ($result as $row) {
            $date = date("m-d", strtotime($row['date']));
            $index = array_search($date, $info['value_x_data']);
            if ($index !== false) {
                $status_value = $this->getTaskStatus($row['error_code']);
                switch ($status_value){
                    case 0: //成功
                        $info['value_y_data'][0]['data'][$index]++;
                        break;
                    case 1: //异常
                        $info['value_y_data'][1]['data'][$index]++;
                        break;
                    case 2: //中止
                        $info['value_y_data'][2]['data'][$index]++;
                        break;
                    case 3: //失败
                        $info['value_y_data'][3]['data'][$index]++;
                        break;
                }
            }
        }
return $info;

    }

    /**
     * 获取数据
     * @param array $params
     * @author ZHENGXIANGQIN@vinchin.com
     * @date 2023/12/8
     * @return unknown
     */
    public function get_card_nas_1($userVal,$hostInfo){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $module = xphp_get_config('module','MODULE_TYPE')['NAS'];
        $info = array(
                'totalNum' => $hostInfo['file']['module']['nas_protect']['total'],
                'protectedNum' => $hostInfo['file']['module']['nas_protect']['protected'],
                'backupData' => $this->pGetProtectData($module,$userVal,2),
        );
        return $info;

    }

    /**
     * 获取数据
     * @param array $params
     * @author ZHENGXIANGQIN@vinchin.com
     * @date 2023/12/8
     * @return unknown
     */
    public function get_card_volcdp_1($userVal,$hostInfo){
        //主机信息
        $module = xphp_get_config('module','MODULE_TYPE')['VOL_CDP'];
        $taskType = xphp_get_config('task','TASKTYPE')['BACKUP'];
        $info = array(
            'totalNum' => $hostInfo['vol_cdp_protect']['module']['vol_cdp_backup']['total'],
            // 受保护主机
            'protectedNum' => $hostInfo['vol_cdp_protect']['module']['vol_cdp_backup']['protected'],
            // 备份数据
            'backupData' => $this->getVolcdpProtectData(),
        );
        return $info;
    }

    /**
     * 获取数据
     * @param array $params
     * @author ZHENGXIANGQIN@vinchin.com
     * @date 2023/12/11
     * @return unknown
     */
    public function get_card_aws_1($userVal,$hostInfo){
        $info = array(
            'totalNum' => $hostInfo['cloud']['module']['awsprotect']['total'],
            'protectedNum' => $hostInfo['cloud']['module']['awsprotect']['protected'],
            'backupData' => $this->pGetProtectData(xphp_get_config('module','MODULE_TYPE')['PUBLIC_CLOUD'],$userVal),
        );
        return $info;
    }
    
    /**
     * 获取数据
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2024/9/11
     * @return unknown
     */
    public function get_card_hadoop_1($userVal,$hostInfo){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
       
        $module = xphp_get_config('module','MODULE_TYPE')['FS'];
        $subModule = xphp_get_config('module','SUBMODULE_TYPE')['HADOOP'];
        $sqlData = "select sum(write_size) as write_size from bd_backup_timepoint where module_type = ? and user_uuid = ? and sub_module_type =?";
        $sqldata = $this->dbSelect($sqlData, array($module,$userVal,$subModule));
        $info = array(
            'totalNum' => $hostInfo['file']['module']['hadoop_protect']['total'],
            'protectedNum' => $hostInfo['file']['module']['hadoop_protect']['protected'],
            'backupData' => $this->pGetIntToSizeInfo(intval($sqldata[0]['write_size']))
        );
        return $info;
    }

    /**
     * 获取数据 -- 后面要改的
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2024/9/11
     * @return unknown
     */
    public function get_card_obs_1($userVal,$hostInfo){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        
        $module = xphp_get_config('module','MODULE_TYPE')['FS'];
        $subModule = xphp_get_config('module','SUBMODULE_TYPE')['OBS'];
        $sqlData = "select sum(write_size) as write_size from bd_backup_timepoint where module_type = ? and user_uuid = ? and sub_module_type =?";
        $sqldata = $this->dbSelect($sqlData, array($module,$userVal,$subModule));
        $info = array(
            'totalNum' => $hostInfo['file']['module']['obs_protect']['total'],
            'protectedNum' => $hostInfo['file']['module']['obs_protect']['protected'],
            'backupData' => $this->pGetIntToSizeInfo(intval($sqldata[0]['write_size']))
        );
        return $info;
    }


        

    public function get_card_storage_1($userVal,$hostInfo){
        $storageInfo = $this->pGetALLStorageInfo($userVal);
        //获取总容量
        $bakStorageCapacity = $this->pGetIntToSizeInfo($storageInfo['total']);
        //获取已使用
        $usedCapacity = $this->pGetIntToSizeInfo($storageInfo['used']);
        //获取剩余
        $remainingCapacity = $this->pGetIntToSizeInfo($storageInfo['free']);

        //获取近7天存储总量
        $dates = $this->getPast7Days();
        //时间格式转换
        $startDate = date('Y') . '-' . $dates[0];   
        $end = date('Y') . '-' . end($dates);
        $value_y_data = [
            'all_size' => ['name' => xphp_get_lang('UI_TENANT_HOME_STORAGE_USED'), 'data' => array_fill(0, 7, 0)],
        ];
        $sql_size = "SELECT 
    DATE_FORMAT(timepoint, '%m-%d') as date,
    SUM(write_size) as daily_total_size
FROM bd_backup_timepoint
WHERE user_uuid = ? and DATE(timepoint) BETWEEN ? AND ?
GROUP BY DATE(timepoint)
ORDER BY date;";
        $rows = $this->dbSelect($sql_size,array($userVal,$startDate,$end));
        foreach ($rows as $row) {
            $index = array_search($row['date'], $dates);
            if ($index !== false) {
                $value_y_data['all_size']['data'][$index] = $row['daily_total_size'];
            }
        }
        $info = array(
            'value_storage_num' => $storageInfo['total_num'],
            'value_storage_size_all_int' => $bakStorageCapacity['size'],
            'value_storage_size_all' => $bakStorageCapacity['value'],
            'value_storage_size_all_unit' => $bakStorageCapacity['unit'],

            'value_storage_size_used_int' => $usedCapacity['size'],
            'value_storage_size_used' => $usedCapacity['value'],
            'value_storage_size_used_unit' => $usedCapacity['unit'],

            'value_storage_size_free_int' => $remainingCapacity['size'],
            'value_storage_size_free' => $remainingCapacity['value'],
            'value_storage_size_free_unit' => $remainingCapacity['unit'],
            'value_7_days_storage_size' => array(
                'value_x_data' => $dates,
                'value_y_data' => $value_y_data,
            ),
        );
        return $info;
        
    }

    public function get_card_all_module_backup_data_1($userVal,$hostInfo){
        $info = array(
            'protect_num' => $hostInfo, //受保护数量
            'protect_capacity' => (new homePageInfo())->getBackupData(array('user_uuid'=>$userVal))
        );
        return $info;
    }










//----------------其他--------------------------------------------------------------------------------------------------
    /**
     * 得到系统运行天数
     */
    private function getRunningDays(){
        $sql = "select system_run_time from bd_system";
        $data = $this->dbSelect($sql);
        $value = round($data[0]['system_run_time'], 1);
        $date = intval($value/24);
        $dateDes = $date;
//        if($date >= 365){
//            $year = intval($date/365);
//            $days = $date % 365;
//            $dateDes = $year .xphp_get_lang['WEB_UTILS_YEAR']. $days . xphp_get_lang['WEB_UTILS_DAY'];
//        }
        return $dateDes;
    }


    /**
     * 得到累计备份数据
     */
    public function getAccumulatedData($userVal = ""){
        $user = xphp_get_user_info();
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $sql = "select * from bd_user_extension ";
        $sqlParams = array();
        //如果是admin系统超级管理员或全局观察者
        if(in_array("global_observer", $user['permission']) || (xphp_get_user_info()['userUuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($user['tenantuuid']) && !$userVal )){

        }else{
            //操作员只显示自己的数据
            $sql .= "where user_uuid = ? ";
            $sqlParams = array($userVal);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $total = 0;
        foreach ($data as $d){
            $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] +
                $d['kvm_data'] + $d['hyperv_data'] + $d['fs_data'] +
                $d['db_data'] + $d['os_data'] + $d['copy_data'] +
                $d['archive_data'] + $d['vol_cdp_data'] + $d['nas_data']+
                $d['m365_data'] + $d['obs_data'] + $d['hadoop_data']+
                $d['kube_data'] + $d['db_cdp_data'] + $d['full_machine_data']+
                $d['fs_copy_data'] + $d['disk_cdp_data'] + $d['vol_cdp_copy_data'] + $d['disk_cdp_copy_data'];
        }

        $info = v1_calsize_to_value_and_unit($total, true);
        $info['value_int'] = $total;
        return $info;
    }


    /**
     * 公共函数
     * 获取存储总量/已用/剩余
     * @param int $useMode 备份/副本/归档
     * @param string $userVal 传入的用户uuid
     */
    private function pGetALLStorageInfo($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        //获取该用户拥有的所有storage_uuid集合
        $resourceHandler = new LogicIndex;
        $storage_list = $resourceHandler->pGetUserAllResource($userVal,8);
        //获取agent_uuid的集合
        $storage_list =array_column($storage_list, 'resource_uuid');
        $storage_uuids = implode(',', array_map(function($item) {return "'{$item}'";}, $storage_list));
        $sql = "select sum(total_size) as total_size, sum(free_size) as free_size, count(storage_uuid) as total_num from  bd_storage_resource ";
        if(!empty($storage_uuids)){
            $sql .= " where storage_uuid in (".$storage_uuids.")";
        }
        $data = $this->dbSelect($sql, array());
        //返回已用容量
        $total = intval($data[0]['total_size']);
        $free = intval($data[0]['free_size']);
        $used = $total - $free;
        $info = array(
            'total' => $total,
            'free' => $free,
            'used' => $used,
            'total_num' => intval($data[0]['total_num'])
        );
        return $info;
    }


    /**
     * 公共函数
     * 获取存储总量/已用/剩余
     * @param int $useMode 备份/副本/归档
     * @param string $userVal 传入的用户uuid
     */
    private function pGetStorageInfo($useMode,$userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        //获取该用户拥有的所有storage_uuid集合
        $resourceHandler = new LogicIndex;
        $storage_list = $resourceHandler->pGetUserAllResource($userVal,8);
        //获取agent_uuid的集合
        $storage_list =array_column($storage_list, 'resource_uuid');
        $storage_uuids = implode(',', array_map(function($item) {return "'{$item}'";}, $storage_list));
        $sql = "select sum(total_size) as total_size, sum(free_size) as free_size, count(storage_uuid) as total_num from  bd_storage_resource where use_mode = ? ";
        if(!empty($storage_uuids)){
            $sql .= " and storage_uuid in (".$storage_uuids.")";
        }
        $data = $this->dbSelect($sql, array($useMode));
        //返回已用容量
        $total = intval($data[0]['total_size']);
        $free = intval($data[0]['free_size']);
        $used = $total - $free;
        $info = array(
            'total' => $total,
            'free' => $free,
            'used' => $used,
            'total_num' => intval($data[0]['total_num'])
        );
        return $info;
    }

    /**
     * 获取大小转换成界面显示信息
     * @param unknown $size
     */
    private function pGetIntToSizeInfo($size){
        $writeSize = v1_calsize($size, true);
        $writeInfo = v1_calsize_to_value_and_unit($size, true);
        $info = array(
            'size' => $size,
            'value' => $writeInfo['value'],
            'unit' => $writeInfo['unit'],
            'des' => $writeSize
        );
        return $info;
    }


    /**
     * 获取近7天时间
     * @return array
     */
    public function getPast7Days() {
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = date("m-d", strtotime("-$i day"));
        }
        return array_reverse($dates); // 从早到晚排序
    }


    /**
     * 得到主节点的uuid
     */
    public function getHostNode(){
        $sql = "select node_uuid from bd_node where node_type = 1";
        $result = $this->dbSelect($sql);
        return $result[0]['node_uuid'];
    }


    //获取近3分钟的监控数据
    public function getSystemMonitorData($node_uuid = ""){
        //获取主节点
        $node_uuid = empty($node_uuid) ?  $this->getHostNode() : $node_uuid;
        $stmt = "SELECT monitor_time, iops_read, iops_write, network_in, network_out, bps_read, bps_write   
FROM bd_system_monitor   
WHERE node_uuid = ?   
ORDER BY monitor_time DESC  
LIMIT 90;";

        $stmt_params = array(
            $node_uuid,
        );
        $rows = $this->dbSelect($stmt,$stmt_params);
        $result = array(
            'card_iops_1' => [
                'value_x_data' => [],
                'value_y_data' => [
                    ['name' => xphp_get_lang('WEB_HOMEPAGE_DISK_WRITE'), 'data' => []],
                    ['name' => xphp_get_lang('WEB_HOMEPAGE_DISK_READ'), 'data' => []],
                ],
            ],
            'card_network_1' => [
                'value_x_data' => [],
                'value_y_data' => [
                    ['name' => xphp_get_lang('WEB_HOMEPAGE_NETWORK_INFLOW'), 'data' => []],
                    ['name' => xphp_get_lang('WEB_HOMEPAGE_NETWORK_OUTFLOW'), 'data' => []],
                ],
            ],
            'card_bps_1' => [
                'value_x_data' => [],
                'value_y_data' => [
                    ['name' => xphp_get_lang('WEB_HOMEPAGE_DISK_WRITE_SIZE_PER_SEC'), 'data' => []],
                    ['name' => xphp_get_lang('WEB_HOMEPAGE_DISK_READ_SIZE_PER_SEC'), 'data' => []],
                ],
            ],
        );

        // 反转数组，使得最旧的记录在前面
        if(empty($rows)){
            return $result;
        }
        $rows = array_reverse($rows);
        foreach ($rows as $row) {
            $result['card_iops_1']['value_x_data'][] = $row['monitor_time'];
            $result['card_iops_1']['value_y_data'][0]['data'][] = $row['iops_write'];
            $result['card_iops_1']['value_y_data'][1]['data'][] = $row['iops_read'];
            $result['card_network_1']['value_x_data'][] = $row['monitor_time'];
            $result['card_network_1']['value_y_data'][0]['data'][] = $row['network_in'];
            $result['card_network_1']['value_y_data'][1]['data'][] = $row['network_out'];
            $result['card_bps_1']['value_x_data'][] = $row['monitor_time'];
            $result['card_bps_1']['value_y_data'][0]['data'][] = $row['bps_write'];
            $result['card_bps_1']['value_y_data'][1]['data'][] = $row['bps_read'];
        }
        return $result;
    }



    //获取最后一次CPU和内存的数据
    public function getCPURootLastTime($node_uuid = ""){
        //获取主节点
        $node_uuid = empty($node_uuid) ?  $this->getHostNode() : $node_uuid;
        $stmt = "SELECT ram_total, ram_used, ram_percentage, cpu_total,cpu_used,cpu_percentage, root_total,root_used,root_percentage,iops_read,iops_write,bps_read,bps_write FROM bd_system_monitor WHERE node_uuid = ? ORDER BY monitor_time DESC limit 1";
        $stmt_params = array(
            $node_uuid,
        );
        $rows = $this->dbSelect($stmt,$stmt_params);
        $result = array(
            'card_cpu_1' => [
                'value_percent' => 0,
            ],
            'card_memory_1' => [
                'value_percent' => 0,
            ],
            'card_root_1' =>[
                'value_percent' => 0,
            ],
            'card_bps_1' =>[
                'value_read' => 0,
                'value_write' => 0,
            ],
            'card_iops_1' =>[
                'value_read' => 0,
                'value_write' => 0,
            ],
        );
        if(empty($rows)){
            return $result;
        }

        foreach ($rows as $row) {
            $result['card_cpu_1']['value_percent'] = $row['cpu_percentage'];
            $result['card_memory_1']['value_percent'] = $row['ram_percentage'];
            $result['card_root_1']['value_percent'] = $row['root_percentage'];
            $result['card_bps_1']['value_read'] = $row['bps_read'];
            $result['card_bps_1']['value_write'] = $row['bps_write'];
            $result['card_iops_1']['value_read'] = $row['iops_read'];
            $result['card_iops_1']['value_write'] = $row['iops_write'];
        }
        return $result;
    }


    /**
     * 公共函数
     * 获取虚拟化中心在线离线占比
     * @param string $vcenterFlag 虚拟化中心标记，false云平台
     * @return number[]
     */
    private function pGetVcenterInfo($vcenterFlag = false){
        $sql = "select online_flag from vm_vcenter where hypervisor_type ";
        //虚拟化中心
        if($vcenterFlag){
            $sql .= " not ";
        }
        $sql .= " in (15) and user_uuid = ?";

        $data = $this->dbSelect($sql,array(xphp_get_user_info()['userUuid'] ));
        $online = 0;
        $offline = 0;
        foreach ($data as $d){
            if(intval($d['online_flag']) == xphp_get_config('app', 'FLAG')['SET']){
                $online ++;
            }else{
                $offline ++;
            }
        }

        $info = array(
            'online' => $online,
            'offline' => $offline
        );
        return $info;
    }




    /**
     * 公共函数
     * 获取保护数据总量
     * @param int $moduleType  模块类型
     */
    private function pGetProtectData($moduleType,$userVal,$sub_module = 0){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $sql = "select sum(write_size) as write_size from  bd_backup_timepoint where module_type = ? and user_uuid = ?";
        $sql_params = array($moduleType,$userVal);
        if(!empty($sub_module)){
            $sql .= " and sub_module_type = ?";
            $sql_params[] = $sub_module;
        }
        $data = $this->dbSelect($sql, $sql_params);
        return $this->pGetIntToSizeInfo(intval($data[0]['write_size']));
    }





    /**
     * 公共方法
     * 获取受保护主机个数
     * @param int $module 模块类型
     * @return number
     */
    public function pGetProtectHost($module,$taskType,$userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $sql ="select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $data = $this->dbSelect($sql, array(
                xphp_get_config('app', 'FLAG')['UNSET'],
                $module,
                $taskType,
            $userVal,
        ));
        return intval($data[0]['total']);
    }

    /**
     * 获取主机信息
     * @return number[]
     */
    private function getHostInfo($userVal){
        $homePageInfo = new homePageInfo();
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $params_userVal = array('userUuid'=>$userVal);
        $authInfo = $homePageInfo -> getProtectedDevice($params_userVal);
        return $authInfo;
       
    }


    /**
     * 根据客户端 uuid 获取当前的使用者是谁 只查询分配的资源/组
     * @param string $agentUuid 客户端uuid
     * @return string
     */
    public function getClientUuid(string $agentUuid,string $user_uuid)
    {
        $sqlParms = [$agentUuid];
        // 先 再分配的资源和资源组去查询
        $sql2 = "select user_uuid from mt_user_resource where resource_type = 10 and resource_uuid = ? limit 1";
        $array = $this->dbSelect($sql2, $sqlParms);
        if (empty($array)) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 10";
            $array = $this->dbSelect($sql, $sqlParms);
        }
        return empty($array) ? $user_uuid : $array[0]['user_uuid'];
    }


    /**
     * 获取数据库实时主机在线/离线个数
     * @param unknown $type
     * @return number[]
     */
    public function getDBCDPHost($type){
        $sql = "select status from cdp_db_host where host_type = ? and user_uuid = ? ";
        $data = $this->dbSelect($sql, array($type,xphp_get_user_info()['userUuid']));
        $online = 0;
        $offline = 0;
        foreach ($data as $d){
            if(intval($d['status']) == xphp_get_config('db','DB_CDP_HOST_STATUS')['ONLINE']){
                $online ++;
            }else{
                $offline ++;
            }
        }
        $info = array(
            'num' => count($data),
            'online' => $online,
            'offline' => $offline
        );
        return $info;
    }


    /**
     * 虚拟机验证数量
     * @return number
     */
    private function getLaboratoryNum($userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        // proxy_status 7 已部署  2 使用中
        $sql = "select count(svl.virtual_lab_uuid) as total from sr_virtual_lab svl,vm_vcenter vv where svl.vcenter_uuid = vv.vcenter_uuid and vv.user_uuid = ? and (proxy_status = 7 or proxy_status = 2)";
        $data = $this->dbSelect($sql,array($userVal));
        return intval($data[0]['total']);
    }

    /**
     * 验证虚拟机数量
     * @param boolean $runFlag  验证中标志
     * @return number
     */
    private function getVerifyNum($runFlag = false, $userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        //验证数量
        $sql = "select count(ssbiv.vm_id) as total from sr_sure_backup_instant_vm ssbiv, bd_task bt where ssbiv.task_uuid = bt.task_uuid and bt.user_uuid = ?";
        $sqlParams = array($userVal);
        if($runFlag){
            //验证中
            $sql .= " and bt.task_status = ?";
            $sqlParams = array_merge($sqlParams,array(xphp_get_config('task','TASKSTATUS')['RUNNING']));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return intval($data[0]['total']);
    }


    /**
     * 验证数量
     * @param intval $errorFlag
     * @return number
     */
    private function getVerifyTime($errorFlag = false,$userVal){
        $userVal = $userVal ? $userVal : xphp_get_user_info()['userUuid'];
        $sql = "select count(id) as total from bd_history_task where task_type = ? and user_uuid = ?";
        $sqlParams = array(xphp_get_config('task','TASKTYPE')['SURE_BACKUP'],$userVal);
        if($errorFlag){
            $sql .= " and error_code = 46";
        }
        $data = $this->dbSelect($sql,$sqlParams);
        return intval($data[0]['total']);
    }


    /**
     * 统计任务状态标识
     */
    public function getTaskStatus($errorCode){
        //异常的错误
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        //中止的错误
        $discontinue = array(
            'BD_TASK_BE_CANCELLED_ERROR'
        );
        $errorCodeArr = xphp_get_config('error', 'errorCode');
        if (in_array($errorCodeArr[$errorCode], $abnormal)) {
            return 1; //异常
        }
        if (in_array($errorCodeArr[$errorCode], $discontinue)) {
            return 2; //中止
        }
        return $errorCode == 0 ? 0 : 3;  //0成功 3失败
    }


    /**
     * 实时容灾备份数据
     */
    public function getVolcdpProtectData(){
        $sql = "SELECT SUM(cvbvs.backup_file_size + cvbvs.log_file_total_size) total 
                FROM cdp_vol_backup_vol_set cvbvs,cdp_vol_backup_agent cvba,bd_backup_timepoint bbt 
                WHERE cvbvs.backup_agent_id = cvba.id and cvba.timepoint_uuid = bbt.timepoint_uuid and bbt.user_uuid = ? and cvba.storage_location !=0";
        $data = $this->dbSelect($sql,array(xphp_get_user_info()['userUuid']));
        $total = $data[0]['total'];
        return $this->pGetIntToSizeInfo(intval($total));
    }






//----------------------------------------------------------------------------------------------------
    /**
     * 统计任务状态标识
     * $grid_type=1 初始化配置
     */
    public function getHomePageGrid($params){
        $userVal = $params['userVal'];
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('UI_HOMEPAGE_DEFALT_GET_HOMEPAGE_DATA'),
            'data' => array(),
        );
        //个人用户首页
        $userInitCard = '[{"id":"card_basic_username","w":12,"content":"card_basic_username","x":0,"y":0,"h":3,"noMove":true,"noResize":true},{"content":"card_basic_data","x":0,"y":3,"w":6,"id":"card_basic_data","h":9,"noMove":true,"noResize":true},{"content":"card_current_job_1","x":6,"y":3,"w":6,"id":"card_current_job_1","h":9,"noMove":true,"noResize":true},{"content":"card_log_1","w":4,"x":0,"y":12,"id":"card_log_1","h":9,"noMove":true,"noResize":true},{"id":"card_storage_1","w":4,"h":19,"content":"card_storage_1","x":4,"y":12,"noMove":true,"noResize":true},{"id":"card_cpu_1","w":4,"h":9,"content":"card_cpu_1","x":8,"y":12,"noMove":true,"noResize":true},{"content":"card_alarm_1","w":4,"x":0,"y":21,"id":"card_alarm_1","h":10,"noMove":true,"noResize":true},{"id":"card_memory_1","w":4,"h":10,"content":"card_memory_1","x":8,"y":21,"noMove":true,"noResize":true},{"id":"card_all_module_backup_data_1","w":12,"h":16,"content":"card_all_module_backup_data_1","x":0,"y":31,"noMove":true,"noResize":true},{"id":"card_vol_cdp_protect_1","w":6,"h":15,"content":"card_vol_cdp_protect_1","x":0,"y":47,"noMove":true,"noResize":true},{"id":"card_copy_1","w":6,"h":15,"content":"card_copy_1","x":6,"y":47,"noMove":true,"noResize":true}]';


        //是否是项目版
        $checkProject = $this->checkProVersion();
        if(!$checkProject){
            //如果非项目版
            //三权并且为操作,非三权非admin用户:  个人首页
            if(($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == 5) || (!$_SESSION['isThreePowers'] && $_SESSION['userLevel'] != 1)){
                //从用户表中取数据
                $sql_user = "select permission from bd_user where user_uuid = ?";
                $user_uuid = empty($userVal) ? xphp_get_user_info()['userUuid'] : $userVal;
                $result_user = $this->dbSelect($sql_user,array($user_uuid));
                //如果没有数据
                if(empty($result_user) || empty($result_user[0]['permission'])){
                    //如果没有数据则使用初始模板
                    $content = $userInitCard;
                }else{
                    //如果有数据则使用用户自己已经编辑的首页
                    $content =  $result_user[0]['permission'];
                }
                $content = json_decode($content,true);
                //如果参数为空 那么从session里面取 否则进行查询
                $getsessionflag =  empty($params['userval']) ? false : true; 
                $authlist = $this->removeCard($content,$getsessionflag,$params['userval']);
                $info['data'] = $authlist;
                return $info;
            }
        }
        //如果是其他用户,则先读取bd_user里面有没自定义首页
        $sql_user_my = "select permission from bd_user where user_uuid = ?";
        $result_user_my = $this->dbSelect($sql_user_my,array(xphp_get_user_info()['userUuid']));
        if(empty($result_user_my) || empty($result_user_my[0]['permission'])){
            //如果没有自定义表格,则取原始的数据
            $sql = "select settings_content from bd_system_settings where settings_type = 20";
            $result = $this->dbSelect($sql);
            $content = $result[0]['settings_content'];
        }else{
            //如果有自定义首页,则取自定义首页的数据
            $result = $result_user_my;
            $content = $result[0]['permission'];
        }
        if(empty($result)){
            $info['success'] = true;
            $info['message'] = xphp_get_lang('WEB_HOMEPAGE_NOT_FIND_DATA_FROM_DATABASE');
            $content = $userInitCard;
            $content = json_decode($content,true);
            $info['data'] = $content;
            return $info;
        }
        $content = json_decode($content,true);
        if($this->checkProVersion()){
            //如果是项目版
            foreach ($content as $key => $value) {
                if($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == 2 && $value['id'] == 'card_data_protect_all_1'){
                    //如果是三权并且为syaadmin,则去掉数据保护模块
                    unset($content[$key]);
                    continue;
                }
                if($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == 5 && $value['id'] == 'card_system_alarm_all_1'){
                    //如果是三权并且为oprator,则去掉流量监控模块
                    unset($content[$key]);
                    continue;
                }
            }
            //重置数组顺序
            $content = array_values($content);
        }
        
        //去除未授权的卡片
        $authlist = $this->removeCard($content);
        $info['data'] = $authlist;
        return $info;
    }


    /**
     * 检查是否是项目版
     */
    public function checkProVersion(){
        $sql= "select settings_content from bd_system_settings where settings_type = 24";
        $result = $this->dbSelect($sql);
        if(empty($result) || $result[0]['settings_content'] != "project"){
            return false;
        }else{
            return true;
        }

    }


    /**
     *
     * 剔除未授权的卡片
     */
    public function removeCard($allcards,$getsessionflag = false,$useruuid = ''){  
        if($getsessionflag){
            //需要调用方法取查permisson
            $userHander = new User();
            //如果没有传值过来 就是当前用户
            $permission = $userHander->pGetUserAllPermission($useruuid, true);
        }else{
            $permission = $_SESSION['permission'];
        }
        $filelist = array(); //文件
        $dblist =  array(); //数据库
        $vmlist = array(); //虚拟机
        $oslist =  array();//操作系统
        $dbprotectlist =  array();//数据库实时
        $exchangelist =  array(); //365
        $CDMlist = array(); //CDM
        $naslist = array(); //nas
        $volcdplist = array(); //实时容灾
        $awslist =  array(); //aws
        $hadooplist = array(); //hadoop
        $obslist =  array(); //obs
        //将卡片分类
        foreach($allcards as $itemcard){
            switch ($itemcard['id']){
                //文件
                case 'card_file_1':
                    $filelist[] = $itemcard;
                    break;
                case 'card_db_1';
                    $dblist[] =  $itemcard;
                    break;
                case 'card_vm_1';
                    $vmlist[] =  $itemcard;
                    break;
                case 'card_os_1';
                    $oslist[] =  $itemcard;
                    break;
                case 'card_db_2';
                    $dbprotectlist[] =  $itemcard;
                    break; 
                case 'card_365_2';
                    $exchangelist[] =  $itemcard;
                    break; 
                case 'card_CDM_1';
                    $CDMlist[] =  $itemcard;
                    break; 
                case 'card_nas_1';
                    $naslist[] =  $itemcard;
                    break;
                case 'card_volcdp_1';
                    $volcdplist[] =  $itemcard;
                    break;
                case 'card_aws_1';
                    $awslist[] =  $itemcard;
                    break;
                case 'card_hadoop_1';
                    $hadooplist[] =  $itemcard;
                    break;
                case 'card_obs_1':
                    $obslist[] =  $itemcard;
                    break;
            }  
        }
      
        //根据权限 进行排除
        $authlist = $allcards;
        //如果文件不存在
        if(!in_array('fileprotect',$permission)){
            $authlist = $this->deleteitem($authlist,$filelist);
        }
        //数据库保护
        if(!in_array('db_protect',$permission)){
            // $authlist =  array_diff($authlist,$dblist);
            $authlist = $this->deleteitem($authlist,$dblist);
        }
        //虚拟机保护
        if(!in_array('vmprotect',$permission)){
            $authlist = $this->deleteitem($authlist,$vmlist);
        }
        //操作系统保护
        if(!in_array('os_protect',$permission)){
            $authlist = $this->deleteitem($authlist,$oslist);
        }
        //数据库实时
        if(!in_array('dbprotect',$permission)){
            $authlist = $this->deleteitem($authlist,$dbprotectlist);
        }
        //exchange
        if(!in_array('office365_protect',$permission)){
            $authlist = $this->deleteitem($authlist,$exchangelist);
        }
        //数据验证
        if(!in_array('data_verification',$permission)){
            $authlist = $this->deleteitem($authlist,$CDMlist);
        }
        //nas
        if(!in_array('nas_protect',$permission)){
            $authlist = $this->deleteitem($authlist,$naslist);
        }
        //实时容灾
        if(!in_array('vol_cdp_protect',$permission)){
            $authlist = $this->deleteitem($authlist,$volcdplist);
        }
        //aws
        if(!in_array('awsprotect',$permission)){
            $authlist = $this->deleteitem($authlist,$awslist);
        }
        //hadoop
        if(!in_array('hadoop_protect',$permission)){
            $authlist = $this->deleteitem($authlist,$hadooplist);
        }
        //obs
        if(!in_array('obs_protect',$permission)){
            $authlist = $this->deleteitem($authlist,$obslist);
        }
        return $authlist;
    }
    /**
     *
     * 从授权列表中剔除需要删除的列表
     */
    public function deleteitem($alllist,$deletelist) {
        foreach($deletelist as $deleteitem){
            $index =  array_search($deleteitem,$alllist);
            array_splice($alllist,$index,1);
        }
        return $alllist;
    }

    /**
     *
     * 获取主题配色
     */
    public function getThemeInfo(){
        //主题配色
        $skin = "";
        //主题布局
        $layout = "";
        //登录页布局
        $login_layout = "";
        //产品版本
        $login_product_type = "";

        //获取主题配色
        $sql_Skin = "select settings_content from bd_system_settings where settings_type = 21";
        $result_skin = $this->dbSelect($sql_Skin);
        if(empty($result_skin)){
            $skin = "";
        }else{
            $skin = $result_skin[0]['settings_content'];
        }

        //获取主题布局
        $sql_layout = "select settings_content from bd_system_settings where settings_type = 22";
        $result_layout = $this->dbSelect($sql_layout);
        if(empty($result_layout)){
            $layout = "";
        }else{
            $layout = $result_layout[0]['settings_content'];
        }

        //获取登录页布局
        $sql_login_layout = "select settings_content from bd_system_settings where settings_type = 23";
        $result_login_layout = $this->dbSelect($sql_login_layout);
        if(empty($result_login_layout)){
            $login_layout = "";
        }else{
            $login_layout = $result_login_layout[0]['settings_content'];
        }

        //获取版本信息
        $sql_product_type = "select settings_content from bd_system_settings where settings_type = 24";
        $result_product_type = $this->dbSelect($sql_product_type);
        if(empty($result_product_type)){
            $login_product_type = "";
        }else{
            $login_product_type = $result_product_type[0]['settings_content'];
        }

        setCaches([
            'theme_skin' => $skin,
            'theme_layout' => $layout,
            'theme_login_layout' => $login_layout,
            'product_type' => $login_product_type,
        ]);
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_HOMEPAGE_GET_THEME_SUCCESS'),
            'data' => array(
                'theme_skin' => $skin,
                'theme_layout' => $layout,
                'theme_login_layout' => $login_layout,
                'product_type' => $login_product_type,
            ),
        );
        return $info;
    }


    /**
     *
     * 保存首页配置
     */
    public function saveCardInfo($params){
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_HOMEPAGE_SAVE_HOMEPAGE_SUCCESS'),
            'data' => array(
            ),
        );
        $cardContent = $params['cardContent'];
            //获取用户uuid
            $user_uuid = xphp_get_user_info()['userUuid'];
            $sql_update_user = "update bd_user set permission = ? where user_uuid  = ?";
            $result_update_user = $this->dbExec($sql_update_user,array($cardContent,$user_uuid));
            if($result_update_user){
                return $info;
            }else{
                $info['success'] = false;
                $info['message'] = xphp_get_lang('WEB_HOMEPAGE_SAVE_HOMEPAGE_ERROR');
                return $info;
            }

    }



    /**
     *
     * 获取节点信息
     */
    public function getNodeList($params){
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_HOMEPAGE_GET_NODE_DATA_SUCCESS'),
            'data' => array(
            ),
        );

        $sql = "select node_uuid,node_type,ip,host_name from bd_node";
        $result = $this->dbSelect($sql);
        if(empty($result)){
            $info['success'] = false;
            $info['message'] = xphp_get_lang('WEB_HOMEPAGE_GET_NODE_DATA_ERROR');
            return $info;
        }

        $NodeHandler = new Node();
        foreach ($result as $each){
            //先获取节点状态,只需要节点状态在线的
            $nodeStatus = $NodeHandler->getNodeAllStatus($each['node_uuid'])['flag'];
            if(!$nodeStatus){continue;}
            //判断节点类型
            $info['data'][] = array(
                'node_uuid' => $each['node_uuid'],
                'ip' => $each['ip'],
                'host_name' => $each['host_name'],
                "ipDes"=> $each['node_type'] == 1 ? $each['ip']."(" . xphp_get_lang('UI_PALTFORM_MASTER_NODE') .")" : $each['ip'],
            );
        }
        return $info;
    }



    /**
     * 清除自定义布局
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function clearUserGrid($params)
    {
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_HOMEPAGE_CLEAR_CUSTOM_LAYOUT_SUCCESS'),
            'data' => array(
            ),
        );
        if(empty($params)){
            $info['success'] = false;
            $info['message'] = xphp_get_lang('WEB_HOMEPAGE_NO_DETECTABLE_USER');

        }
        //初始化需要进行清空布局的user_uuid集合
        $clear_user_uuid_list = array();
        foreach ($params as $each){
            //获取用户uuid
            $user_uuid = $each['user_uuid'];
            //获取是否检测权限,如果不检查权限则直接清空响应的
            $no_check = $each['no_check'];
            if($no_check){
                $clear_user_uuid_list[] = $user_uuid;
                continue;
            }
            //获取旧的权限
            $old_permission = $each['old_permission'];
            //获取新的权限
            $new_permission = $each['new_permission'];
            //对比新旧权限是否有改变,如果为true则改变了
            $check_permission = $this->checkPermission($old_permission,$new_permission);
            if($check_permission){
                $clear_user_uuid_list[] = $user_uuid;
            }
        }
        //开始对数组里面的所有user_uuid进行布局清空
        if(empty($clear_user_uuid_list)){
            return $info;
        }
        // 将数组中的每个元素用单引号包围，并通过逗号连接成一个字符串
        $uuid_list = "'" . implode("', '", $clear_user_uuid_list) . "'";
        // 构建完整的SQL语句
        $sql = "UPDATE bd_user SET permission = NULL WHERE user_uuid IN (".$uuid_list.")";
        $sql_result = $this->dbExec($sql);
        return $info;
    }



    /**
     * 清除自定义布局
     * @author       liushuai@vinchin.com
     * @date         2023/10/9
     */
    public function reoverUserGrid($params)
    {
        $info = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('UI_HOMEPAGE_DEFALT_RECOVERY_SUCCESS'),
            'data' => array(
            ),
        );
       //获取用户uuid
       $user_uuid = empty($params['user_uuid']) ? xphp_get_user_info()['userUuid'] : $params['user_uuid'];
       if(empty($user_uuid)){
           $info['success'] = false;
           $info['message'] = xphp_get_lang('UI_HOMEPAGE_DEFALT_RECOVERY_FAULT_TIPS');
           return $info;
       }
       $sql = "UPDATE bd_user SET permission = NULL WHERE user_uuid = ?";
       $sql_result = $this->dbExec($sql,array($user_uuid));
       if($sql_result){
            return $info;
       }else{
            $info['success'] = false;
            $info['message'] = xphp_get_lang('UI_HOMEPAGE_DEFALT_RECOVERY_FAULT');
            return $info;
       }
    }


    /**
     * 检查新旧权限是否有变化,只检查各个模块的授权
     * @param $old_permission
     * @param $new_permission
     * @authr  liushuai@vinchin.com
     * @date 2024年1月23日11:39:32
     *
     */
    public function checkPermission($old_permission,$new_permission){
        $old_list = $this->getPermissionByArray($old_permission);
        $new_list = $this->getPermissionByArray($new_permission);
        //对2个数组进行排序
        sort($old_list);
        sort($new_list);
        if ($old_list == $new_list) {
            return false; //没有变化
        } else {
            return true; //有变化
        }

    }

    private function getPermissionByArray($permission){
        $info = array();
        //公有云
        if (in_array("awsprotect", $permission)) {
            $info[] = "awsprotect";
        }
        //虚拟机
        if (in_array("vmprotect", $permission)) {
            $info[] = "vmprotect";
        }
        //文件
        if (in_array("fileprotect", $permission)) {
            $info[] = "fileprotect";
        }
        //数据库
        if (in_array("db_protect", $permission)) {
            $info[] = "db_protect";
        }
        //操作系统
        if (in_array("os_protect", $permission)) {
            $info[] = "os_protect";
        }
        //nas
        if (in_array("nas_protect", $permission)) {
            $info[] = "nas_protect";
        }
        //365
        if (in_array("application_protect", $permission)) {
            $info[] = "application_protect";
        }
        //k8s
        if (in_array("k8s_protect", $permission)) {
            $info[] = "k8s_protect";
        }
        //实时容灾
        if (in_array("vol_cdp_protect", $permission)) {
            $info[] = "vol_cdp_protect";
        }
        return $info;
    }



        /**
     * 获取近几天的时间点写入大小
     */
    public function getTimepointWriteSize($days){
        $days = intval($days);
        // 获取当前日期作为结束日期
        $endDate = date("Y-m-d");
        // 计算开始日期
        $startDate = date("Y-m-d", strtotime("-$days day"));

        // 初始化信息数组
        $info = [];

        // 逆序遍历，确保顺序正确
        for ($i = $days-1; $i >= 0; $i--) {
            // 计算日期
            $dateTime = strtotime("-$i day", strtotime($endDate));
            $info[] = array(
                'date' => date("m-d", $dateTime),
                'backup_data' => 0,
                'backup_data_des' => 0,
                'backup_data_des_unit' => 'B',

                'copy_data' => 0,
                'copy_data_des' => 0,
                'copy_data_des_unit' => 'B',
            );
        }

        // 获取当前用户uuid
        $user_uuid = xphp_get_user_info()['userUuid'];

        //获取副本归档的任务类型
        $copy_archive_list = array(
            xphp_get_config('task','TASKTYPE')['BACKUP_COPY'], //备份副本
            xphp_get_config('task','TASKTYPE')['ARCHIVE'],//归档副本
            xphp_get_config('task','TASKTYPE')['FILE_BACKUP_COPY'],//文件备份副本
            xphp_get_config('task','TASKTYPE')['DB_BACKUP_COPY'],//数据库备份副本
            xphp_get_config('task','TASKTYPE')['OS_BACKUP_COPY'],//操作系统备份副本
            xphp_get_config('task','TASKTYPE')['OS_BACKUP_ARCHIVE'],//操作系统备份归档副本
            xphp_get_config('task','TASKTYPE')['NAS_BACKUP_COPY'],//NAS备份副本
        );

        $copy_archive_list_str ="'". implode("','", $copy_archive_list)."'";



        // 构建SQL查询，确保范围正确
        $sql = "SELECT DATE(timepoint) AS date_day, SUM(write_size) AS total_write_size 
                FROM bd_backup_timepoint 
                WHERE DATE(timepoint) BETWEEN DATE(?) AND DATE(?)
                AND user_uuid = ?
                AND task_type not in ($copy_archive_list_str)
                GROUP BY DATE(timepoint)";
        $result = $this->dbSelect($sql, array($startDate, $endDate, $user_uuid));

        //获取只有副本归档的数据
        $sql_copy_archive = "SELECT DATE(timepoint) AS date_day, SUM(write_size) AS total_write_size 
                FROM bd_backup_timepoint 
                WHERE DATE(timepoint) BETWEEN DATE(?) AND DATE(?)
                AND user_uuid = ?
                AND task_type in ($copy_archive_list_str)
                GROUP BY DATE(timepoint)";
        $result_copy_archive = $this->dbSelect($sql_copy_archive, array($startDate, $endDate, $user_uuid));

        if(!empty($result)){
            // 更新$info数组
            foreach ($result as $row) {
                foreach ($info as &$entry) {
                    if ($entry['date'] === date("m-d", strtotime($row['date_day']))) {
                        $backupValueAndUnit = v1_calsize_to_value_and_unit($row['total_write_size']);
                        $entry['backup_data'] = intval($row['total_write_size']);
                        $entry['backup_data_des'] = $backupValueAndUnit['value'];
                        $entry['backup_data_des_unit'] = $backupValueAndUnit['unit'];
                        break;
                    }
                }
            }
        }
        if(!empty($result_copy_archive)){
            //更新$info数组
            foreach ($result_copy_archive as $row) {
                foreach ($info as &$entry) {
                    if ($entry['date'] === date("m-d", strtotime($row['date_day']))) {
                        $copyValueAndUnit = v1_calsize_to_value_and_unit($row['total_write_size']);
                        $entry['copy_data'] = intval($row['total_write_size']);
                        $entry['copy_data_des'] = $copyValueAndUnit['value'];
                        $entry['copy_data_des_unit'] = $copyValueAndUnit['unit'];
                        break;
                    }
                }
            }
        }
        
        return $info;
    }

    //--------------------标准版首页所需接口开始--------------------
    /* 获取首页日志信息 */
    public function getLogInfo(){
        return array(
            'job' => $this->getJobLogCount(),
            'system' => $this->getSystemLogCount()
        );
    }
    /* 获取任务日志数量 */
    public function getJobLogCount(){
        $FLAG = xphp_get_config('app', 'FLAG');
        $sqlCount = "SELECT count(btl.id) as total from bd_task_log btl left join bd_user bu on btl.user_uuid = bu.user_uuid
                        where btl.running_flag = {$FLAG['UNSET']} ";
        $sql = '';
        $userInfo = xphp_get_user_info();

        //获取当前用户所拥有的用户
        if (xphp_get_user_info()['isThreePowers']) {
            // 如果是三权模式
            if (xphp_get_user_info()['userLevel'] == 3) {
                //安全管理员
                $sql .= " and (bu.user_level = 5 or bu.user_level = 4) ";
            } else if (xphp_get_user_info()['userLevel'] == 4) {
                //安全审计员
                $sql .= " and (bu.user_level = 2 or bu.user_level = 3) ";
            } else {
                // 关联管理用户判断 日志 - 查看   log_look
                $authUser = xphp_get_user_info()['authUser']['log_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $userUuidArr = array_merge([$userInfo['userUuid']], $authUser);
                    $userUuidStr = "('" . implode("','", $userUuidArr) . "')";
                    // 下面是具体的业务逻辑 $userUuidStr 是最终的用户ID字符串
                    $sql .= "and btl.user_uuid in " . $userUuidStr;
                } else {
                    $sql .= " and btl.user_uuid = '{$userInfo['userUuid']}' ";
                }
            }
        } else {
            //不是全局观察者获取对应用户的任务
            if (!(in_array('global_read', $userInfo['permissionArr']) || in_array('global_write', $userInfo['permissionArr']))) {
                // 关联管理用户判断 日志 - 查看   log_look
                $authUser = xphp_get_user_info()['authUser']['log_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([$userInfo['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= "and btl.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " and btl.user_uuid = '{$userInfo['userUuid']}' ";
                }
            }
        }
        $count = $this->dbSelect($sqlCount . $sql, []);
        return $count[0]['total'];
    }
    /* 获取系统日志数量 */
    public function getSystemLogCount(){
        $sqlCount = "SELECT count(bsl.id) as total from bd_system_log bsl left join bd_user bu on bsl.user_uuid = bu.user_uuid ";
        $sql = '';
        //检查是否是租户管理员
        $userInfo = xphp_get_user_info();
        $tenantHandler = new \app\v1\tenant\v0\logic\Index();
        $userHandler = new User();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        $userList = $tenantHandler->getTenantAllUser(xphp_get_user_info()['tenantuuid']);
        $userListDes = implode("','", $userList);
        //Master用户
        if (in_array("global_observer", xphp_get_user_info()['permission']) || (empty(xphp_get_user_info()['tenantuuid']) && $userInfo['userUuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")) {
            $sql .= " where 1=1 ";
        } else if ($tenantMangerFlag) {
            $sql .= " where bsl.user_uuid in ('" . $userListDes . "')";
        } else {
            if (xphp_get_user_info()['isThreePowers']) {
                // 三权模式
                if (xphp_get_user_info()['userLevel'] == 3) {
                    //安全管理员
                    $sql .= " where (bu.user_level = 5 or bu.user_level = 4) ";
                } else if (xphp_get_user_info()['userLevel'] == 4) {
                    //安全审计员
                    $sql .= " where (bu.user_level = 2 or bu.user_level = 3) ";
                } else {
                    $sql .= " where bsl.user_uuid = '{$userInfo['userUuid']}' ";
                }
            } else {
                // 关联管理用户判断 存储资源 - 查看   log_look
                $authUser = xphp_get_user_info()['authUser']['log_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([$userInfo['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= " where bsl.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " where bsl.user_uuid = '{$userInfo['userUuid']}' ";
                }
            }
        }
        $count = $this->dbSelect($sqlCount . $sql, []);
        return $count[0]['total'];
    
    }

    /* 获取首页告警信息 */
    public function getAlarmInfo(){
        return array(
            'job' => $this->getJobAlarmCount(),
            'system' => $this->getSystemAlarmCount()
        );
    }

    /* 获取任务告警信息 */
    public function getJobAlarmCount(){
        $sqlCount = "SELECT count(bta.task_alarm_id) as total from bd_task_alarm bta LEFT JOIN bd_user bu on bta.user_uuid = bu.user_uuid where bta.alarm_level != 1 ";
        $sqlData = "SELECT bta.task_name, bta.alarm_level, unix_timestamp(bta.alarm_time) alarm_time, bta.error_code, bta.submodule_type, bta.description_key, bta.description_param
                FROM bd_task_alarm bta
                LEFT JOIN bd_user bu on bta.user_uuid = bu.user_uuid
                where bta.alarm_level != 1 ";
        $sql = '';
        $userInfo = xphp_get_user_info();
        if (xphp_get_user_info()['isThreePowers']) {
            // 三权模式
            if (xphp_get_user_info()['userLevel'] == 3) {
                //安全管理员
                $sql .= " and (bu.user_level = 5 or bu.user_level = 4) ";
            } else if (xphp_get_user_info()['userLevel'] == 4) {
                //安全审计员
                $sql .= " and (bu.user_level = 2 or bu.user_level = 3) ";
            } else {
                // 关联管理用户判断 告警 - 查看   alarm_look
                $authUser = xphp_get_user_info()['authUser']['alarm_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([$userInfo['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= "and bta.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " and bta.user_uuid = '{$userInfo['userUuid']}' ";
                }
            }
        } else {
            //不是全局观察者获取对应用户的任务
            if (!in_array("global_observer", xphp_get_user_info()['permission'])) {
                // 关联管理用户判断 告警 - 查看   log_look
                $authUser = xphp_get_user_info()['authUser']['alarm_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([$userInfo['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= "and bta.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " and bta.user_uuid = '{$userInfo['userUuid']}' ";
                }
            }
        }

        $count = $this->dbSelect($sqlCount . $sql, []) ?? [];
        $warnsql = $sql . " and bta.alarm_level = 2 ";
        $errorsql = $sql . " and bta.alarm_level = 3 ";
        $listsql = $sql . " order by bta.alarm_time desc limit 10";
        $warnCount = $this->dbSelect($sqlCount . $warnsql, []) ?? [];
        $errorCount = $this->dbSelect($sqlCount . $errorsql, []) ?? [];
        $listdata = $this->dbSelect($sqlData . $listsql, []) ?? []; //获取任务告警列表
        //重新组装listdata 让它变成前端所需要的结构
        $rows = [];
        $logInfo = new LogInfo();
        $logTaskType = xphp_get_config('log', 'LOGTYPE')['TASK'];
        foreach ($listdata as $eachdata) {
            $des = $logInfo->getLogDescription($logTaskType, $eachdata['error_code'], $eachdata['description_key'], $eachdata['description_param']);
            // 公有云替换描述中的"虚拟机"为"实例"
            if (in_array($eachdata['submodule_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
                $des = str_replace(xphp_get_lang('WEB_VM_TREE_OVIRT_FAKE_VM_FOLDER'), xphp_get_lang('WEB_PLATFORM_DES_INSTANCE'), $des);
            }
            $rows[] = array(
                'job_name' => $eachdata['task_name'],
                'alarm_time' => date('m-d H:i:s', intval($eachdata['alarm_time'])),
                'status' => intval($eachdata['alarm_level']),
                'description' => $des
            );
        }
        return array(
            'total' => $count[0]['total'],
            'warn' => $warnCount[0]['total'],
            'error' => $errorCount[0]['total'],
            'list' => $rows
        );
    }

    /* 获取系统告警信息 */
    public function getSystemAlarmCount(){
        $sqlCount = "SELECT count(system_alarm_id) as total from bd_system_alarm where system_alarm_id is not null";
        $count = $this->dbSelect($sqlCount) ?? [];
        $warnsql = $sqlCount . " and alarm_level = 2 ";
        $errorsql = $sqlCount . " and alarm_level = 3 ";
        $warnCount = $this->dbSelect($warnsql, []) ?? [];
        $errorCount = $this->dbSelect($errorsql, []) ?? [];
        return array(
            'total' => $count[0]['total'],
            'warn' => $warnCount[0]['total'],
            'error' => $errorCount[0]['total'],
        );
    }

    /* 获取数据保护 */
    public function getProtectData(){
        //定时模块
        $showFlagInfo = (new homePageInfo()) -> getPageAuth();
        $userUuid = xphp_get_user_info()['userUuid'];
        $vmSubModule = xphp_get_config('module', 'VM_SUB_MODULE');
        $fileSubModule = xphp_get_config('module', 'SUBMODULE_TYPE');
        $osSubModule = xphp_get_config('module', 'OS_SUBMODULE_TYPE');
        $module = xphp_get_config('module', 'MODULE_TYPE');
        $taskType =  xphp_get_config('task', 'TASKTYPE');
        // 定时模块获取数据
        $vmData = $this->getVMData($showFlagInfo['vmprotect'],$module['VM'],$vmSubModule['VM'],$userUuid);
        $privateCloudData = $this->getVMData($showFlagInfo['prcloud_protect'],$module['VM'],$vmSubModule['PRIVATE_CLOUD'],$userUuid);
        $publicCloudData = $this->getVMData($showFlagInfo['awsprotect'],$module['VM'],$vmSubModule['PUBLIC_CLOUD'],$userUuid);

        $fileData = $this->getFileData($showFlagInfo['filebackup'],$module['FS'],$fileSubModule['FS'],$taskType['BACKUP'],$userUuid);
        $nasData = $this->getFileData($showFlagInfo['nas_protect'],$module['NAS'],'',$taskType['BACKUP'],$userUuid);
        $hadoopData = $this->getFileData($showFlagInfo['hadoop_protect'],$module['FS'],$fileSubModule['HADOOP'],$taskType['BACKUP'],$userUuid);
        $obsData = $this->getFileData($showFlagInfo['obs_protect'],$module['FS'],$fileSubModule['OBS'],$taskType['BACKUP'],$userUuid);
        $k8sData = $this->getk8sData($showFlagInfo['k8s_protect'],$module['KUBERNETES'],$userUuid);
        
        $machineData = $this->getServerData($showFlagInfo['complete_machine'],$module['OS'],$osSubModule['MACHINE_OS'],1,$taskType['OS_BACKUP'],$userUuid);
        $volData = $this->getServerData($showFlagInfo['osbackup'],$module['OS'],0,0,$taskType['OS_BACKUP'],$userUuid);
        
        $dbData = $this->getFileData($showFlagInfo['db_protect'],$module['DB'],'',$taskType['DB_BACKUP'],$userUuid);
        $m365Data = $this->getM365Data($showFlagInfo['office365_protect'],$module['M365'],$userUuid);
        //实时模块获取数据
        $machineCdpData = $this->getCdpData( $showFlagInfo['complete_cdp_backup'],$module['VOL_CDP'],2,$taskType['VOL_CDP_BACKUP'],$userUuid);
        $volCdpData = $this->getCdpData($showFlagInfo['vol_cdp_backup'],$module['VOL_CDP'],1,$taskType['VOL_CDP_BACKUP'],$userUuid);
        //复制模块获取数据
        $machineCopyData = $this->getServerCopyData($showFlagInfo['machine_copy'],$module['VOL_CDP'],$osSubModule['MACHINE_OS'],2,$taskType['VOL_CDP_REPLICATION'],$userUuid);
        $volCopyData = $this->getServerCopyData($showFlagInfo['vol_cdp_copy'],$module['VOL_CDP'],$osSubModule['OS'],1,$taskType['VOL_CDP_REPLICATION'],$userUuid);
        $fileCopyData = $this->getFileCopyData($showFlagInfo['file_copy_protect'],$module['FILE_COPY'],$taskType['FILE_COPY'],$userUuid);
        $dbCopyData = $this->getDbCopyData($showFlagInfo['dbcdpcopy'],$module['DB_CDP'],$taskType['CDP_DB_BACKUP'],$userUuid);
        return array(
            // 定时模块
            'timemodule' => array(
                'vm' => $vmData,
                'private_cloud' => $privateCloudData,
                'public_cloud' => $publicCloudData,
                'file' => $fileData,
                'nas' => $nasData,
                'hadoop' => $hadoopData,
                'obs' => $obsData,
                'machine' => $machineData,
                'vol' => $volData,
                'k8s' => $k8sData,
                'db' => $dbData,
                'm365' => $m365Data
            ),
            // 实时模块
            'cdpmodule' => array(
                'machine' => $machineCdpData,
                'vol' => $volCdpData
            ),
            // 复制模块
            'copymodule' => array(
                'machine' => $machineCopyData,
                'vol' => $volCopyData,
                'file' => $fileCopyData,
                'db' => $dbCopyData
            )

        );
    }
    /* 获取虚拟机、公有云、私有云数据公共方法 */
    private function getVMData($showFlag,$module,$subModule,$userUuid){
        $vmprotect = (new homePageInfo()) -> getProtectVmInfo($showFlag,$subModule,$userUuid);
        $protectData = $this->getProtectDataDetail($module,$subModule,$userUuid);
        return array(
            ...$vmprotect,
            'protectData' => $protectData,
            
        );
    }
    /* 获取文件、nas、hadoop、对象存储公共方法 */
    private function getFileData($showFlag,$module,$subModule,$taskType,$userUuid){ 
        $fileprotect = (new homePageInfo()) -> pGetProtectHost($showFlag,$module,$taskType,$subModule,$userUuid);
        $protectData = $this->getProtectDataDetail($module,$subModule,$userUuid);
        return array(
            ...$fileprotect,
            'protectData' => $protectData,
        );
    }


    /* 获取整机、卷公共方法 */
    private function getServerData($showFlag,$module,$subModule,$devType,$taskType,$userUuid){ 
        $serverprotect = (new homePageInfo()) -> pGetProtectHost($showFlag,$module,$taskType,$subModule,$userUuid);
        $protectData = $this->getProtectDataDetail($module,$devType,$userUuid);
        return array(
            ...$serverprotect,
            'protectData' => $protectData,
        );
    }
    


    /* 获取k8s数据 */
    private function getk8sData($showFlag,$module,$userUuid){
        $k8sprotect = (new homePageInfo()) -> getK8sNums($showFlag,$module,$userUuid);
        $protectData = $this->getProtectDataDetail($module,'',$userUuid);
        return array(
            ...$k8sprotect,
            'protectData' => $protectData,
        );
    }

    /* 获取M365数据 */
    private function getM365Data($showFlag,$module,$userUuid){
        $m365protect = (new homePageInfo()) -> getOrganizationInfo($showFlag,$userUuid);
        $protectData = $this->getProtectDataDetail($module,'',$userUuid);
        return array(
            ...$m365protect,
            'protectData' => $protectData,
        );
    }

    /* 获取受保护数据大小 */
    private function getProtectDataDetail($moduleType, $submodule = null, $userUuid, $taskType = '', $dateFlag = false){
        $userInfo = xphp_get_user_info();
        $sql = "SELECT DATE_FORMAT(bht.finish_time, '%m-%d') AS day, SUM(bht.total_object_valid_size) AS write_size
                FROM bd_history_task bht left join mt_user_tenant mut on bht.user_uuid = mut.user_uuid ";
        $sqlLeft = "";
        $sqlAnd = "";
        $sqlWhere = "";
        $module = xphp_get_config('module')['MODULE_TYPE'];
        $vmModule = xphp_get_config('module')['VM_SUB_MODULE'];
        $taskTypeALL = xphp_get_config('task', 'TASKTYPE');
        $taskType = empty($taskType) ? $taskTypeALL['BACKUP'] : $taskType;
        switch ($moduleType) {
            case $module['DB']:
                $taskType = $taskTypeALL['DB_BACKUP'];
                break;
            case $module['OS']:
                $taskType = $taskTypeALL['OS_BACKUP'];
                $sqlAnd .= " and bht.submodule_type = {$submodule} ";
                break;
            case $module['FILE_COPY']://文件复制
                $taskType = $taskTypeALL['FILE_COPY'];
                break;
            case $module['VOL_CDP']://整机和卷复制只是dev_type不同
                $sqlLeft .= " left join cdp_vol_task cvt on bht.task_uuid = cvt.task_uuid";
                // dev_type 1卷  2整机
                $sqlAnd .= " and cvt.dev_type = {$submodule}";
                break;
            case $module['DB_CDP']://数据库复制
                $taskStatus = xphp_get_config('task', 'TASKSTATUS')['RUNNING'];
                $sqlLeft .= " left join bd_task bt on bht.task_uuid = bt.task_uuid";
                $sqlAnd .= " and bt.task_status = {$taskStatus}";
                break;
            case $module['KUBERNETES']:
                $taskType = $taskTypeALL['KUBE_BACKUP'];
                break;
            case $module['VM']:
                $hyperversionType = xphp_get_config('vm', 'VMHYPERVISORGROUP');
                $notIn = implode(',', array_merge($hyperversionType['openstack'], $hyperversionType['publiccloud']));
                $openstask = implode(',', $hyperversionType['openstack']);
                $publiccloud = implode(',', $hyperversionType['publiccloud']);
                switch ($submodule) {
                    case $vmModule['VM']:
                        $sqlAnd .= " and bht.submodule_type not in ({$notIn})";
                        break;
                    case $vmModule['PRIVATE_CLOUD']:
                        $sqlAnd .= " and bht.submodule_type in ({$openstask})";
                        break;
                    case $vmModule['PUBLIC_CLOUD']:
                        $sqlAnd .= " and bht.submodule_type in ({$publiccloud})";
                        break;
                }
                break;
        }
        $sqlWhere .= " where bht.module_type = ? and bht.user_uuid = ? and bht.task_type = ?";
        if($dateFlag){
            $sqlWhere .= " and bht.finish_time >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
        }
        $sqlParams = array($moduleType, $userUuid, $taskType);
        if (!empty($submodule) && $moduleType != $module['VOL_CDP']
        && $moduleType != $module['OS']
        && $moduleType != $module['VM']) {
            $sqlAnd .= " and bht.submodule_type = ? ";
            $sqlParams = array_merge($sqlParams, array($submodule));
        }
        //userLevel是1 2 3就全部显示
        if (!in_array($userInfo['userLevel'], [1, 2, 3])) {
            $sqlAnd .= " and bht.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlAnd .= " and mut.tenant_uuid IS NULL";
        }
        if($dateFlag){
            $sqlEnd = " GROUP BY DATE(bht.finish_time) ORDER BY day";
        }

        $data = $this->dbSelect($sql . $sqlLeft . $sqlWhere . $sqlAnd .$sqlEnd, $sqlParams);
        $datainfo = array();
        $sizeInfo = $this->pGetIntToSizeInfo(intval($data[0]['write_size']));
        $info = array();
        if(!$dateFlag){
            return $sizeInfo;
        }else{
            $existingDays = array_column($data, 'day');
            for ($i = 0; $i < 365; $i++) {
                $date = new \DateTime();
                $date->modify("-$i days");
                $dayitem = $date->format('m-d');
                $fulldayitem = $date->format('Y-m-d');
                $index = array_search($dayitem, $existingDays);
                if ($index !==  false) {
                    $datainfo[] = ["day" => $dayitem, "fullday" => $fulldayitem, "write_size" => $data[intval($index)]['write_size']];
                }else{
                   $datainfo[] = ["day" => $dayitem,"fullday" => $fulldayitem, "write_size" => "0"];
                }
            }
            foreach ($datainfo as $d) {
                
                $sizeInfo = $this->pGetIntToSizeInfo(intval($d['write_size']));
                $info[] = array(
                    'date' => $d['day'],
                    'fullDate' => $d['fullday'],
                    'size' => $sizeInfo['size'],
                    'value' => $sizeInfo['value'],
                    'unit' => $sizeInfo['unit'],
                    'des' => $sizeInfo['des']
                );
            }
            return $info;
        }
        
    }


    /* 获取整机实时模块数据 */
    private function getCdpData($showFlag,$module,$devType,$taskType,$userUuid){
        $cdpprotect = (new homePageInfo()) -> getCdpInfo($showFlag, $module, $devType, $taskType, $userUuid);
        $protectData = $this->getProtectDataDetail($module,$devType,$userUuid,$taskType);
        // 获取任务数
        $userInfo = xphp_get_user_info();
        //实时任务数
        $sql = "select count(bt.task_uuid) as total from bd_task bt,cdp_vol_task cvt, mt_user_tenant mut where bt.task_uuid = cvt.task_uuid and cvt.dev_type = ? and bt.module_type =? and bt.task_type = ? ";
        //userLevel是1 2 3就全部显示
        $sqlParams = array($devType, $module, $taskType);
        if (!in_array($userInfo['userLevel'], [1, 2, 3])) {
            $sql .= " and bt.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $tasknum =  $data[0]['total'];
        $onlinenum = $this->getHostOnlineOfflineNum(1, $userUuid);
        $backupsetnum  = $this->getBackupSetNum($userUuid);
        return array(
            ...$cdpprotect,
            'protectData' => $protectData,
            'taskNum' => $tasknum,
            'onlineNum' => $onlinenum,
            'offlineNum' => $cdpprotect['total'] - $onlinenum,
            'backupSet' => $backupsetnum
        );
    }

    /* 获取主机在线数量、离线数量 */
    private function getHostOnlineOfflineNum($onlineFlag,$userUuid) {
        $userInfo = xphp_get_user_info();
       
        $sql = "select count(distinct ba.agent_uuid) as total from bd_agent ba left join mt_user_tenant mut on ba.user_uuid = mut.user_uuid where ba.agent_type not in (3, 4, 5) and ba.online_flag = ? ";
        $sqlParams = array($onlineFlag);
        //userLevel是1 2 3就全部显示
        if (!in_array($_SESSION['userLevel'], [1, 2, 3])) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceIndex();
            $uuid = $resourceHandler->pGetUserAllResource($userUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $agentUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " and ba.agent_uuid in ($agentUuidsIn)";
            }
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return $data[0]['total'];
    }

   /*  获取备份集数量 */
   private function getBackupSetNum(){
       $sql = "select count(id) as total from cdp_vol_backup_agent";
       $data = $this->dbSelect($sql);
       return $data[0]['total'];
   }

    /* 获取整机、卷复制数据 */
    private function getServerCopyData($showFlag,$module,$subModule,$devType,$taskType,$userUuid){
        $copyprotect = (new homePageInfo()) -> getCdpInfo($showFlag, $module, $devType, $taskType, $userUuid);
        //获取累计保护数据
        $protectData = $this -> getProtectDataDetail($module,$devType,$userUuid,$taskType);
        return array(
            ...$copyprotect,
            'protectData' => $protectData,
        );
    }
    /* 获取文件复制数据 */
    private function getFileCopyData($showFlag,$module,$taskType,$userUuid){
        $copyprotect = (new homePageInfo()) -> getFilecopyInfo($showFlag,$module,$taskType,$userUuid);
        //获取累计保护数据
        $protectData = $this -> getTotalProtectData('file_copy', $userUuid);
        return array(
            ...$copyprotect,
            'protectData' => $protectData,
        );
    }
    /* 获取数据可以复制数据 */
    private function getDbCopyData($showFlag,$module,$taskType,$userUuid){
        $copyprotect = (new homePageInfo()) -> getDbCopyInfo($showFlag,$module,$taskType,null, $userUuid);
        $protectData = $this -> getProtectDataDetail($module,'',$userUuid,$taskType);
        return array(
            ...$copyprotect,
            'protectData' => $protectData,
        );
    }


    /**
     * 自定义的四个大模块分类 - 获取保护数据总量 - 从历史任务获取
     * @param int $moduleType  模块类型
     * @param string $userUuid 用户uuid
     */
    private function getTotalProtectData($moduleType = '', $userUuid, $dateFlag = false)
    {
        $info = array();
        $userInfo = xphp_get_user_info();
        $sql = "SELECT DATE_FORMAT(bht.finish_time, '%m-%d') AS day, SUM(bht.total_object_valid_size) AS write_size
                FROM bd_history_task bht left join mt_user_tenant mut on bht.user_uuid = mut.user_uuid where 1=1";
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        switch ($moduleType) {
            case 'cloud':
                $taskType = xphp_get_config('task', 'TASKTYPE')['KUBE_BACKUP'] . ',' . xphp_get_config('task', 'TASKTYPE')['BACKUP'];
                $moduleType = xphp_get_config('module')['MODULE_TYPE']['KUBERNETES'] . ',' . xphp_get_config('module')['MODULE_TYPE']['VM'];
                break;
            case 'file':
                $moduleType = xphp_get_config('module')['MODULE_TYPE']['FS'] . ',' . xphp_get_config('module')['MODULE_TYPE']['NAS'];
                break;
            case 'machine':
                $moduleType = xphp_get_config('module')['MODULE_TYPE']['OS'];
                $taskType = xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'];
                break;
            case 'application':
                $taskType = xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'] . ',' . xphp_get_config('task', 'TASKTYPE')['BACKUP'];
                $moduleType = xphp_get_config('module')['MODULE_TYPE']['DB'] . ',' . xphp_get_config('module')['MODULE_TYPE']['M365'];
                break;
            case 'file_copy':
                $moduleType = xphp_get_config('module')['MODULE_TYPE']['FILE_COPY'];
                $taskType = xphp_get_config('task', 'TASKTYPE')['FILE_COPY'];
                break;

        }
        $sqlParams = array();
        //userLevel是1 2 3就全部显示
        if (!in_array($userInfo['userLevel'], [1, 2, 3])) {
            $sql .= " and bht.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        if($moduleType){
            $sql .= " and bht.module_type in ({$moduleType})";
        }
        $sql .= " and bht.task_type in ({$taskType})";
        if($dateFlag){
            $sql .= "and bht.finish_time >= DATE_SUB(CURDATE(), INTERVAL 365 DAY) GROUP BY DATE(bht.finish_time) ORDER BY day";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $datainfo = array();
        $sizeInfo = $this->pGetIntToSizeInfo($data[0]['write_size']);
        if(!$dateFlag){
            return $sizeInfo;
        }else{
            $existingDays = array_column($data, 'day');
            for ($i = 0; $i < 365; $i++) {
                $date = new \DateTime();
                $date->modify("-$i days");
                $dayitem = $date->format('m-d');
                $fulldayitem = $date->format('Y-m-d');
                $index = array_search($dayitem, $existingDays);
                if ($index !==  false) {
                    $datainfo[] = ["day" => $dayitem, "fullday" => $fulldayitem, "write_size" => $data[intval($index)]['write_size']];
                }else{
                    $datainfo[] = ["day" => $dayitem, "fullday" => $fulldayitem, "write_size" => "0"];
                }
            }
            foreach ($datainfo as $d) {
                $sizeInfo = $this->pGetIntToSizeInfo($d['write_size']);
                $info[] = array(
                    'date' => $d['day'],
                    'fullDate' => $d['fullday'],
                    'size' => $sizeInfo['size'],
                    'value' => $sizeInfo['value'],
                    'unit' => $sizeInfo['unit'],
                    'des' => $sizeInfo['des']
                );
            }
            return $info;
        }
        
    }

    /* 获取受保护数据详情 */
    public function getProtectDataTrend(){
        $showFlagInfo = (new homePageInfo()) -> getPageAuth();
        $userUuid = $params['userUuid'] ?? xphp_get_user_info()['userUuid'];
        $vmSubModule = xphp_get_config('module', 'VM_SUB_MODULE');
        $fileSubModule = xphp_get_config('module', 'SUBMODULE_TYPE');
        $osSubModule = xphp_get_config('module', 'OS_SUBMODULE_TYPE');
        $module = xphp_get_config('module', 'MODULE_TYPE');
        $taskType =  xphp_get_config('task', 'TASKTYPE');
        //获取定时模块数据
        $vmData = $showFlagInfo['vmprotect'] ? $this->getProtectDataDetail($module['VM'],$vmSubModule['VM'],$userUuid,'',true) : [];
        $privateCloudData = $showFlagInfo['prcloud_protect'] ? $this->getProtectDataDetail($module['VM'],$vmSubModule['PRIVATE_CLOUD'],$userUuid,'',true) : [];
        $publicCloudData = $showFlagInfo['awsprotect'] ? $this->getProtectDataDetail($module['VM'],$vmSubModule['PUBLIC_CLOUD'],$userUuid,'',true) : [];
        $fileData = $showFlagInfo['filebackup'] ? $this->getProtectDataDetail($module['FS'],$fileSubModule['FS'],$userUuid,'',true): [];
        $nasData = $showFlagInfo['nas_protect'] ? $this->getProtectDataDetail($module['NAS'],'',$userUuid,'',true): [];
        $hadoopData = $showFlagInfo['hadoop_protect'] ? $this->getProtectDataDetail($module['FS'],$fileSubModule['HADOOP'],$userUuid,'',true): [];
        $obsData =  $showFlagInfo['obs_protect'] ? $this->getProtectDataDetail($module['FS'],$fileSubModule['OBS'],$userUuid,'',true): [];
        $k8sData =  $showFlagInfo['k8s_protect'] ? $this->getProtectDataDetail($module['KUBERNETES'],'',$userUuid,'',true): [];
        $machineData =  $showFlagInfo['complete_machine'] ? $this->getProtectDataDetail($module['OS'],1,$userUuid,$taskType['OS_BACKUP'],true): [];
        $volData = $showFlagInfo['osbackup'] ? $this->getProtectDataDetail($module['OS'],0,$userUuid,$taskType['OS_BACKUP'],true): [];
        $dbData = $showFlagInfo['db_protect'] ? $this->getProtectDataDetail($module['DB'],'',$userUuid,$taskType['DB_BACKUP'],true): [];
        $m365Data = $showFlagInfo['office365_protect'] ? $this->getProtectDataDetail($module['M365'],'',$userUuid,'',true): [];
        //获取实时模块数据
        $machineCdpData = $showFlagInfo['complete_cdp_backup'] ? $this->getProtectDataDetail($module['VOL_CDP'],2,$userUuid,$taskType['VOL_CDP_BACKUP'],true): [];
        $volCdpData = $showFlagInfo['vol_cdp_backup'] ? $this->getProtectDataDetail($module['VOL_CDP'],1,$userUuid,$taskType['VOL_CDP_BACKUP'],true): [];
        //获取复制模块数据
        $machineCopyData = $showFlagInfo['machine_copy'] ? $this->getProtectDataDetail($module['VOL_CDP'],2,$userUuid,$taskType['VOL_CDP_REPLICATION'],true): [];
        $volCopyData = $showFlagInfo['vol_cdp_copy'] ? $this->getProtectDataDetail($module['VOL_CDP'],1,$userUuid,$taskType['VOL_CDP_REPLICATION'],true): [];
        $fileCopyData = $showFlagInfo['file_copy_protect'] ? $this->getTotalProtectData('file_copy', $userUuid,true): [];
        $dbCopyData = $showFlagInfo['dbcdpcopy'] ? $this->getProtectDataDetail($module['DB_CDP'],'',$userUuid,$taskType['CDP_DB_BACKUP'],true): [];
        $totalProtectData = $this->getTotalProtectDataTrend($userUuid);
        $data =  array(
            'timemodule' => array(
                'vm' => array("showflag" => $showFlagInfo['vmprotect'], "data" => $vmData),
                'private_cloud' => array("showflag" => $showFlagInfo['prcloud_protect'], "data" => $privateCloudData),
                'public_cloud' => array("showflag" => $showFlagInfo['awsprotect'], "data" => $publicCloudData),
                'file' => array("showflag" => $showFlagInfo['filebackup'], "data" => $fileData),
                'nas' => array("showflag" => $showFlagInfo['nas_protect'], "data" => $nasData),
                'hadoop' => array("showflag" => $showFlagInfo['hadoop_protect'], "data" => $hadoopData),
                'obs' => array("showflag" => $showFlagInfo['obs_protect'], "data" => $obsData),
                'machine' => array("showflag" => $showFlagInfo['complete_machine'], "data" => $machineData),
                'vol' => array("showflag" => $showFlagInfo['osbackup'], "data" => $volData),
                'k8s' => array("showflag" => $showFlagInfo['k8s_protect'], "data" => $k8sData),
                'db' => array("showflag" => $showFlagInfo['db_protect'], "data" => $dbData),
                'm365' => array("showflag" => $showFlagInfo['office365_protect'], "data" => $m365Data),
            ),
            'cdpmodule' => array(
                'machine' => array("showflag" => $showFlagInfo['complete_cdp_backup'], "data" => $machineCdpData),
                'vol' => array("showflag" => $showFlagInfo['vol_cdp_backup'], "data" => $volCdpData)
            ),
            'copymodule' => array(
                'machine' => array("showflag" => $showFlagInfo['machine_copy'], "data" => $machineCopyData),
                'vol' => array("showflag" => $showFlagInfo['vol_cdp_copy'], "data" => $volCopyData),
                'file' => array("showflag" => $showFlagInfo['file_copy_protect'], "data" => $fileCopyData),
                'db' => array("showflag" => $showFlagInfo['dbcdpcopy'], "data" => $dbCopyData)
            ),
            'allmodule' => array(
                "data" => $totalProtectData['data'],
                "datades" => $totalProtectData['datades'],
            ) 
        );
        return $data;
        
    }
    private function getTotalProtectDataTrend($userUuid){
        $data = $this->getTotalProtectData('',$userUuid,true);
        $dataresult = array();
        $dataresultdes = array();
        foreach($data as $value){
            $des = $this->pGetIntToSizeInfo(intval($value['size']));
            $dataresult[] = intval($value['size']);
            $dataresultdes[] = $des['des'];
        }

        return array(
            "data" => $dataresult,
            "datades" => $dataresultdes
        ); 
    }
    /* 获取存储数据使用趋势 */
    public function getStorageDataTrend(){
        $userUuid = xphp_get_user_info()['userUuid'];
        $sql = "SELECT DATE_FORMAT(bht.finish_time, '%m-%d') AS day, SUM(bht.total_object_write_size) AS write_size
                FROM bd_history_task bht left join mt_user_tenant mut on bht.user_uuid = mut.user_uuid WHERE bht.finish_time >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
       
        $userInfo = xphp_get_user_info();
        $sqlParams = array();
        //userLevel是1 2 3就全部显示
        if (!in_array($userInfo['userLevel'], [1, 2, 3])) {
            $sql .= " and bht.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        $sql .= " GROUP BY DATE(bht.finish_time) ORDER BY day";
        $data = $this->dbSelect($sql, $sqlParams);
        $existingDays = array_column($data, 'day');
            for ($i = 0; $i < 365; $i++) {
                $date = new \DateTime();
                $date->modify("-$i days");
                $dayitem = $date->format('m-d');
                $fulldayitem = $date->format('Y-m-d');
                $index = array_search($dayitem, $existingDays);
                if ($index !==  false) {
                $datainfo[] = ["day" => $dayitem, "fullday" => $fulldayitem, "write_size" => $data[intval($index)]['write_size']];
                }else{
                   $datainfo[] = ["day" => $dayitem,"fullday" => $fulldayitem, "write_size" => "0"];
                }
            }
            foreach ($datainfo as $d) {
                $sizeInfo = $this->pGetIntToSizeInfo(intval($d['write_size']));
                $info[] = array(
                    'date' => $d['day'],
                    'fullDate' => $d['fullday'],
                    'size' => $sizeInfo['size'],
                    'value' => $sizeInfo['value'],
                    'unit' => $sizeInfo['unit'],
                    'des' => $sizeInfo['des']
                );
            }
            return $info;
    }



    //--------------------标准版首页所需接口结束--------------------
















}
