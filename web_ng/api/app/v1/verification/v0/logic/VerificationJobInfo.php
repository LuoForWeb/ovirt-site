<?php

namespace app\v1\verification\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\VerifyOpcode;
use app\v1\system\v0\logic\Notice;
use app\v1\system\v0\logic\Time;
use app\v1\vm\v0\logic\VmJobInfo;
use xphp\Email;

/**
 * note          数据验证CDM 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VerificationJobInfo extends Base
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->nodeOpcode = new NodeOpcode();
        $this->verifyOpcode = new VerifyOpcode();
    }

    /**
     * 获取数据验证任务详情信息
     * @return json
     */
    public function getVerifyJobDetails($params = [])
    {
        //任务uuid
        $taskuuid = $params['job_uuid'];
        //获取数据
        $sql = "select bt.user_uuid, bt.strategy_id, bt.ignore_resource_limiting_flag,bt.task_uuid, bt.node_uuid, ssb.storage_uuid, bt.task_name, bt.task_type, bt.task_status, bt.current_stage, bt.stage_percent, unix_timestamp(bt.create_time) as create_time, bs.time_strategy_backup_type, unix_timestamp(bs.next_start_time) as next_start_time, unix_timestamp(bri.start_time) as start_time, bts.start_time as strategy_start_time, ssb.surebackup_task_type, ssb.automatic_verifitied_flag, ssb.limit_boot_vm_num, ssb.mount_protocol, ssb.nfs_server_ip, ssb.backup_server_ip, ssb.appgroup_uuid, ssb.virtual_lab_uuid,
                bts.strategy_type, bts.mode, bts.days, bts.roll_interval, unix_timestamp(bts.roll_end_time) as roll_end_time, ir.temp_uuid, ir.approval_uuid
                from bd_task bt left join sr_surebackup ssb on bt.task_uuid = ssb.sr_task_uuid left join bd_running_info bri on bt.task_uuid = bri.task_uuid  left join bd_time_strategy bts on bt.strategy_id = bts.strategy_id left join bd_strategy bs on bt.strategy_id = bs.strategy_id left join industry_report ir on bt.task_uuid = ir.task_uuid
                where bt.task_uuid = ? ";
        $sqlparams = array($taskuuid);
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $info = array();
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $basicInfo = $this->groupBasicInfo($each);
            $strategyInfo = $this->groupStrategyInfo($each);
            $itemInfo = $this->getOldItemList($each['task_uuid']);
            $each['doc_compare_thread'] = $itemInfo['doc_compare_thread'];
            $highInfo = $this->groupHighInfo($each);
            $info = array(
                'item_list' => $itemInfo['item_list'],
                'basic_info' => $basicInfo,
                'time_strategy' => $strategyInfo['time_strategy'],
                'high_strategy' => $highInfo,
                'user_uuid' => $each['user_uuid']
            );
        }
        return $info;
    }

    /**
     * 获取数据验证对象列表
     * @return json
     */
    public function getVerifyObjectList($params = [])
    {
        $jobuuid = $params['job_uuid'];
        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //排序字段
        $sort = $params['sort'];
        //排序方式
        $order = $params['order'];
        //搜索参数
        $keyword = $params['search'];
        //获取数据
        $sql = "select ssi.current_timepoint, ssi.max_boot_time, ssi.ping_status, ssi.heartbeat_status, ssi.screen_status, ssi.item_status, ssi.vir_det_kill_status, ssi.integrity_check_status, ssi.module_type, ssi.submodule_type, ssi.ori_uuid, ssi.ori_name, ssi.new_uuid, ssi.new_name, ssi.timepoint_uuids_json, ve.console_url from sr_surebackup_item ssi left join vm_emd ve on ve.uuid = ssi.new_uuid where ssi.sr_task_uuid = ? ";
        $sqlcount = "select count(ssi.item_id) as total from sr_surebackup_item ssi left join vm_emd ve on ve.uuid = ssi.new_uuid where ssi.sr_task_uuid = ? ";
        $sqlparams = array($jobuuid);
        $sqlCountparams = array($jobuuid);
        //按对象名称搜索
        if (!empty($keyword)) {
            $sql .= " and ssi.ori_name like '%" . $keyword . "%' ";
            $sqlcount .= " and ssi.ori_name like '%" . $keyword . "%' ";
        }
        //如果还有排序参数,则进行排序
        if (!empty($sort) && !empty($order)) {
            $sql .= " order by " . $sort . " " . $order;
        }
        if (!empty($offset) && !empty($limit)) {
            $sql .= " limit ?, ?";
            $sqlparams = array_merge($sqlparams, array($offset, $limit));
        }
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount, $sqlCountparams);
        $info = array(
            'rows' => array(),
            'total' => intval($count[0]['total']),
        );
        //如果数据为空
        if (empty($result)) {
            return $info;
        }

        // 根据module_type和sub_module_type返回对应的对象信息
        $objectArr = [
            '2_0' => ('UI_PLATFORM_VM_VIRTUAL'), // 虚拟化
            '2_1' => ('UI_PLATFORM_VM_VIRTUAL'), // 虚拟化
            '2_2' => ('UI_PLATFORM_PRIVATE_CLOUD'), // 私有云
            '2_3' => ('UI_PLATFORM_PUBLIC_CLOUD'), // 公有云
            '5_0' => ('UI_COMPLETE_MACHINE'), // 定时整机
            '5_1' => ('UI_PLATFORM_CDP_COMPLETE_BACKUP'), // 定时整机
            '5_2' => ('UI_PLATFORM_MACHINE_REEL_BACKUP'), // 定时卷
            '11_2' => ('UI_PLATFORM_NAS'),// 文件系列 nas
            '3_1' => ('UI_PLATFORM_FILES'),// 文件系列 文件
            '3_3' => ('UI_PLATFORM_HADOOP_HDFS'),// 文件系列 HADOOP
            '3_4' => ('UI_PLATFORM_OBS_STORAGE'),// 文件系列 OBS
            '4_0' => ('UI_PLATFORM_DATABASE'), // 数据库
            '14_0' => ('UI_PLATFORM_MICROSOFT365'), // Microsoft 365
            '14_1' => ('UI_PLATFORM_MICROSOFT365'), // Microsoft 365
            '28_0' => ('UI_PLATFORM_K8S'), // 容器
            '26_0' => ('UI_PLATFORM_FILES'), // 文件复制 文件
            '12_0' => ('UI_PLATFORM_DATABASE'), // 数据库复制 数据库
            '10_0' => ('UI_PLATFORM_CDP_COMPLETE_BACKUP'), // 实时 需要根据 dev_type 区分，备份类型 1卷 2整机
        ];

        foreach ($result as $each) {
            //获取内嵌控制台
            if (!empty($each['console_url'])) {
                $newconsoleurl = str_replace('0.0.0.0:6080', $_SERVER['HTTP_HOST'] . '/web_console', $each['console_url']);
                $newconsoleurl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($newconsoleurl);
            } else {
                $newconsoleurl = '';
            }
            $items = $each['module_type'] . '_' . $each['submodule_type'];
            if (isset($objectArr[$items])) {
                $moduleTypeDes = xphp_get_lang($objectArr[$items]);
            }

            $info['rows'][] = array(
                'object_uuid' => $each['ori_uuid'],
                'object_name' => $each['ori_name'],
                'module_type' => intval($each['module_type']),
                'submodule_type' => intval($each['submodule_type']),
                'module_type_des' => $moduleTypeDes,
                'status' => intval($each['item_status']),
                'status_des' => xphp_get_desc('Vm', 'VERIFY_VM_STATUS')[intval($each['item_status'])],
                'ping_flag' => intval($each['ping_status']),
                'ping_flag_des' => xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($each['ping_status'])],
                'heartbeat_flag' => intval($each['heartbeat_status']),
                'heartbeat_flag_des' => xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($each['heartbeat_status'])],
                'screen_flag' => intval($each['screen_status']),
                'screen_flag_des' => xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($each['screen_status'])],
                'vir_det_kill_flag' => intval($each['vir_det_kill_status']),
                'vir_det_kill_flag_des' => xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($each['vir_det_kill_status'])],
                'integrity_check_flag' => intval($each['integrity_check_status']),
                'integrity_check_flag_des' => xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($each['integrity_check_status'])],
                'max_boot_time' => gmdate("H:i:s", intval($each['max_boot_time'])),
                'console_url' => $newconsoleurl,
                'new_uuid' => $each['new_uuid'],
                'current_timepoint' => !empty($each['current_timepoint'])?$each['current_timepoint']:xphp_get_config('app','TIMESPACE'),
            );
        }

        return $info;
    }


    /**
     * 获取数据验证任务单个对象详情信息
     * @return json
     */
    public function getVerifyObjectInfo($params = [])
    {

        //对象uuid
        $objectuuid = $params['object_uuid'];
        $taskuuid = $params['task_uuid'];
        //获取数据
        $sql = "select ssi.backup_task_uuid, ssi.item_status, ssi.module_type, ssi.ori_uuid, ssi.ori_name, ssi.new_uuid, ssi.new_name, ssi.advance_config_json, ssi.netcards_info_json, ssi.timepoint_uuids_json, ssi.current_timepoint from bd_task bt left join sr_surebackup_item ssi on bt.task_uuid = ssi.sr_task_uuid where bt.task_uuid = ? and ssi.ori_uuid = ?";
        $sqlparams = array($taskuuid, $objectuuid);
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $info = array();
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $advanced_config = json_decode($each['advance_config_json'], true);
            $original_netcard_info = json_decode($each['netcards_info_json'], true);
            $timepointInfo= json_decode($each['timepoint_uuids_json'], true);
            //获取时间点信息
            if(intval($timepointInfo['timepoint_range']) == 1){
                $timepointDes = xphp_get_lang('UI_VERIFY_APP_GROUP_ALL_POINT');

            }else if(intval($timepointInfo['timepoint_range']) == 2){
                //最新时间点
                $timepointDes = xphp_get_lang('UI_VERIFY_APP_GROUP_NEWEST_POINT').$this->getTimepointDes("", $each);
            }else{
                $timepointDes = xphp_get_lang('UI_VERIFY_APP_GROUP_ASSIGN_POINT');
            }
            $timepointList = array();
            foreach ($timepointInfo['timepoint_uuids'] as $d) {
                //整机实时数据直接获取存入的时间戳显示
                if (intval($each['module_type']) == xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP']){
                    $timepointList[] = $d['timepoint'];
                }else{
                    $timepointList[] = $d['timepoint']."(".xphp_get_desc('Pf','BACKUP_MODE_DES')[intval($d['backup_mode'])].xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT').")";
                }
            }

            $general_config = array(
                'cpu_mode' => $advanced_config['cpu_mode'],
                'cpu_num' => $advanced_config['cpu_socket'],
                'core_num' => $advanced_config['cores_per_socket'],
                'memory_size' => $advanced_config['vm_memory'],
                'os_type' => $advanced_config['os_type'],
                'os_version_name' => $advanced_config['os_version_name'],
                'disk_target_bus' => xphp_get_desc('Verification','DSIK_TARGET_BUS_DES')[intval($advanced_config['disk_target_bus'])],
                'netcard_target_bus' => xphp_get_desc('Verification','NETCARD_TARGET_BUS_DES')[intval($advanced_config['netcard_target_bus'])],
            );
            $verify_config = array(
                'timepoint_des' => $timepointDes,
                'timepoint_list' => $timepointList,
                'timepoint_range' => intval($timepointInfo['timepoint_range']),
                'max_timepoint_verify' => $timepointInfo['max_timepoint_verify'],
                'vol_cdp_datetime' => $timepointInfo['vol_cdp_datetime'],
                'vir_det_kill_flag' => intval($advanced_config['vir_det_kill_flag']),
                'max_ping_wait_time' =>  gmdate("H:i:s", intval($advanced_config['max_ping_wait_time'])),
                'virus_scan_config_list' => !empty($advanced_config['virus_det_info']) ? json_decode($advanced_config['virus_det_info'], true) : [
                    [
                        'strategy_type' => 0,
                        'recover_policy' => 0,
                        'interrupt_policy' => 0,
                        'scan_strategy' => 0,
                        'all_timepoints_flag' => 0,
                        'skip_application_group_flag' => 0
                    ]
                ], // 兼容配置为空的
            );

            $network_config = $original_netcard_info;
            $info = array(
                'object_name' => $each['ori_name'],
                'new_name' => $each['new_name'],
                'general_config' => $general_config,
                'verify_config' => $verify_config,
                'network_config' => $network_config,
            );
        }

        return $info;
    }


    /**
     * 组合基本信息
     * @param $d
     * @return array
     */
    private  function groupBasicInfo($d){
        // 任务阶段
        $stageArr = xphp_get_config('task', 'COMMON_STAGE');
        $currentstage = !empty($stageArr[$d['current_stage']]) ?
            xphp_get_lang($stageArr[$d['current_stage']]) : xphp_get_config('app', 'NULLSPACE');
        //如果是病毒查杀替换为病毒扫描
        if(intval($d['current_stage']) == 4 ){
            $currentstage = xphp_get_lang('UI_PLATFORM_RECOVERY_JOB_VIRUS_SCAN');
        }
        if ($d['stage_percent'] != -1) {
            $currentstage .= '(' . $d['stage_percent'] . '%)';
        }
        $info = array(
            'task_name' => $d['task_name'],
            'task_type' => intval($d['task_type']),
            'task_type_des' => xphp_get_desc('Pf', 'TASKTYPEDES')[intval($d['task_type'])],
            'task_status' => intval($d['task_status']),
            'task_status_des' => xphp_get_desc('Pf', 'TASKSTATUSDES')[intval($d['task_status'])],
            'start_time' => VmJobInfo::instance()->getStartTIme($d['start_time'], $d['task_status']),
            'run_time' => VmJobInfo::instance()->getTimeInterval($d['start_time'], $d['task_status']),
            'verify_mode' => intval($d['surebackup_task_type']),
            'verify_mode_des' => xphp_get_desc('Verification', 'VERIFY_MODE_DES')[ intval($d['surebackup_task_type'])],
            'automatic_verifitied_flag' => intval($d['automatic_verifitied_flag']),
            'verify_type_des' => xphp_get_desc('Verification', 'VERIFY_TYPE_DES')[ intval($d['automatic_verifitied_flag'])],
            'create_time' => date('Y-m-d H:i:s', intval($d['create_time'])),
            'node_uuid' => $d['node_uuid'],
            'storage_uuid' => $d['storage_uuid'],
            'module_list' => $this->getJobModuleList($d['task_uuid']),
            'temp_uuid' => $d['temp_uuid'],
            'approval_uuid' => $d['approval_uuid'],
            'taskStage' => $currentstage
        );

        return $info;
    }

    /**
     * 组合策略信息
     * @param $d
     * @return array
     */
    private  function groupStrategyInfo($d){

        //时间策略
        $timeStrategy = array(
            'next_time' => ($d['automatic_verifitied_flag'] == 1 || $d['time_strategy_backup_type'] == 3) ? xphp_get_config('app','TIMESPACE'):date('Y-m-d H:i:s', intval($d['next_start_time'])),
            'type' => !empty($d['strategy_type'])? 2:1,
            'info' => array(
                'strategy_type' => intval($d['strategy_type']),
                'mode' => intval($d['mode']),
                'days' => $this->getDaysArr($d['days']),
                'start_time' => $d['strategy_start_time'],
                'roll_flag' => intval($d['roll_flag']),
                'roll_interval' => date('H:i:s', intval($d['roll_interval'])),
                'roll_end_time' => date('H:i:s', intval($d['roll_end_time'])),
                'frequency' => ""
            )
        );

        $info = array(
            'time_strategy' => $timeStrategy
        );

        return $info;
    }

    /**
     * 组合高级配置
     * @param $d
     * @return array
     */
    private  function groupHighInfo($d){
        if(!empty($d['appgroup_uuid'])){
            $sql1 = "select appgroup_name from sr_application_group where appgroup_uuid = ? ";
            $data1 = $this->dbSelect($sql1, array($d['appgroup_uuid']));
        }

        if(!empty($d['virtual_lab_uuid'])){
            $sql2 = "select virtual_lab_name, hypervisor_type from sr_virtual_lab where virtual_lab_uuid = ? ";
            $data2 = $this->dbSelect($sql2, array($d['virtual_lab_uuid']));
        }


        $info = array(
            'limit_boot_vm_num' => intval($d['limit_boot_vm_num']),
            'mount_protocol' => intval($d['mount_protocol']),
            'nfs_server_ip' => $d['nfs_server_ip'],
            'backup_server_ip' => $d['backup_server_ip'],
            'appgroup_uuid' => $d['appgroup_uuid'],
            'appgroup_name' => !empty($d['appgroup_uuid'])?$data1[0]['appgroup_name']:xphp_get_config('app')['NULLSPACE'],
            'virtual_lab_uuid' => $d['virtual_lab_uuid'],
            'virtual_lab_name' => !empty($d['virtual_lab_uuid'])?$data2[0]['virtual_lab_name']:xphp_get_config('app')['NULLSPACE'],
            'hypervisor_type' => !empty($d['virtual_lab_uuid'])?intval($data2[0]['hypervisor_type']): 108,
            'doc_compare_thread' => intval($d['doc_compare_thread']),
            'ignore_resource_limiting_flag' =>  v1_parse_flag_to_bool(intval($d['ignore_resource_limiting_flag'])),
        );

        return $info;
    }

    /**
     * 得到天的数组
     * @param unknown $days
     */
    private function getDaysArr($days){
        $count = strlen($days);
        $daysArr = array();
        for ($i =0; $i<$count; $i++){
            if("s" == substr($days, $i, 1)) break;
            $daysArr[] = intval(substr($days, $i, 1));
        }
        return $daysArr;
    }


    /**
     * 过滤开始时间
     * @param unknown $startTime
     * @return string
     */
    protected function getStartTIme($startTime, $status)
    {
        if (
            $status == xphp_get_config('task')['TASKSTATUS']['RUNNING'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['NETWORK_FAULT'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['ABNORMAL'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['SUCCESSED']
        ) {
            return $this->parseDate($startTime);
        }
        return xphp_get_config('app')['TIMESPACE'];
    }

    /**
     * 得到任务运行的持续时间
     * @param timestamp $startTime
     */
    public function getTimeInterval($startTime, $status)
    {
        if (
            $status == xphp_get_config('task')['TASKSTATUS']['RUNNING'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['NETWORK_FAULT'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['ABNORMAL'] ||
            $status == xphp_get_config('task')['TASKSTATUS']['SUCCESSED']
        ) {
            $nowTime = Time::instance()->getSystemTime();
            if (!$startTime || $startTime == 0) {
                return xphp_get_config('app')['TIMESPACE'];
            }
            $intval = $nowTime - $startTime;
            return v1_sec_to_time($intval);
        }
        return xphp_get_config('app')['TIMESPACE'];
    }

    /**
     * 获取模块类型数组
     * @param $taskuuid
     */
    private function getJobModuleList($taskuuid){
        $sql = "select module_type, submodule_type from sr_surebackup_item where sr_task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $list = array();
        foreach ($data as $d) {
            $list[] = $d['module_type'].'_'.$d['submodule_type'];
        }

        return $list;
    }

    /**
     * 获取对象列表信息
     * @param $taskuuid
     */
    private function getOldItemList($taskuuid){
        $sql = "select ori_uuid, ori_name, backup_task_uuid, role, timepoint_uuids_json, advance_config_json, netcards_info_json, doc_list_json, max_boot_time, module_type, submodule_type,extension_info from sr_surebackup_item where sr_task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $list = array();
        $docThread = 3;
        foreach ($data as $d) {
            $doc_list = json_decode($d['doc_list_json'],true);
            $timepoint = json_decode($d['timepoint_uuids_json'], true);
            $advance_config = json_decode($d['advance_config_json'], true);
            $extension_info = json_decode($d['extension_info'], true);
            $networkList = json_decode($d['netcards_info_json'], true)['netcard_list'];
            $memorySize = $advance_config['vm_memory'] * 1024 * 1024 * 1024;
            $memoryInfo = v1_calsize_to_value_and_unit($memorySize);
            $general_config = array(
                'task_uuid' => $d['backup_task_uuid'],
                'item_uuid' => $d['ori_uuid'],
                'item_name' => $d['ori_name'],
                'cpu_num' => $advance_config['cpu_socket'],
                'core_num' => $advance_config['cores_per_socket'],
                'memory_size' => $memorySize,
                'memory_size_int' => $memoryInfo['value'],
                'memory_size_unit' => $memoryInfo['unit'],
                'os_type' => $advance_config['os_type'],
                'os_version' => $advance_config['os_version'],
                'os_version_name' => $advance_config['os_version_name'],
                'cpu_mode' => $advance_config['cpu_mode'],
                'cpu_arch' => $advance_config['cpu_arch'],
                'start_vm_flag' => $advance_config['start_vm_flag'],
                'module_type' => $d['module_type'],
                'submodule_type' => $d['submodule_type'],
                'disk_target_bus' => intval($advance_config['disk_target_bus']),
                'netcard_target_bus' => intval($advance_config['netcard_target_bus'])
            );
            $verify_config = array(
                'timepoint_uuid_list' => $timepoint,
                'ping_test_flag' => v1_parse_flag_to_bool(intval($advance_config['ping_flag'])),
                'heartbeat_flag' => v1_parse_flag_to_bool(intval($advance_config['heartbeat_flag'])),
                'print_screen_flag' => v1_parse_flag_to_bool(intval($advance_config['screen_flag'])),
                'integrity_check_flag' => v1_parse_flag_to_bool(intval($advance_config['integrity_check_flag'])),
                'vir_det_kill_flag' => intval($advance_config['vir_det_kill_flag']),
                'virus_scan_config_list' => !empty($advance_config['virus_det_info']) ? json_decode($advance_config['virus_det_info'], true) : [
                    [
                        'strategy_type' => 0,
                        'recover_policy' => 0,
                        'interrupt_policy' => 0,
                        'scan_strategy' => 0,
                        'all_timepoints_flag' => 0,
                        'skip_application_group_flag' => 0
                    ]
                ], // 兼容配置为空的
                'doc_consistency_flag' => intval($advance_config['doc_consistency_flag']),
                'max_boot_time' => gmdate("H:i:s", intval($d['max_boot_time'])),
                'max_ping_wait_time' => gmdate("H:i:s", intval($advance_config['max_ping_wait_time'])),
                'doc_list' => !empty($doc_list)? $doc_list['doc_list']:[],
                'role' => intval($d['role']),
                'driver_check_status' => $extension_info['driver_replace_flag'] == 1 ? 3 : 1,
                'driver_hw_id_map' => $extension_info['driver_hw_id_map'],
            );

            $network_config = array();
            foreach ($networkList as $i) {
                $network_config[] = array(
                    'network_name' => $i['network_name'],
                    'ip' => $i['ip_address'],
                    'netmask'=> $i['netmask'],
                    'gateway' => $i['gateway'],
                    'mac_address' => $i['mac_address'],
                );
            }

            $list[] = array(
                'general_config' => $general_config,
                'verify_config' => $verify_config,
                'network_config' => $network_config
            );
            $docThread = $advance_config['doc_compare_thread'];

        }
        $info = array(
            'item_list' => $list,
            'doc_compare_thread' => $docThread
        );
        return $info;
    }

    /**
     * 获取时间点描述信息
     * @param string $timepointuuid
     * @param $taskuuid
     * @return string
     */
    protected function getTimepointDes(string $timepointuuid, $info){
        $taskuuid = $info['backup_task_uuid'];
        $objectuuid = $info['ori_uuid'];
        $sql = "select bbt.timepoint, bbt.backup_mode,bbt.module_type, vol.end_timestamp from bd_backup_timepoint bbt 
                left join vm_backup_timepoint vbt on bbt.timepoint_uuid = vbt.timepoint_uuid 
                left join db_backup_timepoint dbt on bbt.timepoint_uuid = dbt.timepoint_uuid
                left join fs_backup_timepoint fbt on bbt.timepoint_uuid = fbt.fs_timepoint_uuid 
                left join os_backup_timepoint obt on bbt.timepoint_uuid = obt.timepoint_uuid
                left join m365_backup_timepoint mbt on bbt.timepoint_uuid = mbt.m365_timepoint_uuid 
                left join kube_backup_timepoint kbt on bbt.timepoint_uuid = kbt.timepoint_uuid
                left join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid 
                left join cdp_vol_backup_agent agent ON bbt.timepoint_uuid = agent.timepoint_uuid
                left join cdp_vol_backup_vol_set vol ON agent.id = vol.backup_agent_id
                where bbt.task_uuid = ?  and (vbt.vm_uuid = '".$objectuuid."' or fbt.agent_uuid = '".$objectuuid."' or dbt.db_uuid = '".$objectuuid."' or obt.agent_uuid = '".$objectuuid."' or mbt.organization_uuid = '".$objectuuid."' or kbt.cluster_uuid = '".$objectuuid."' or agent.master_agent_uuid = '".$objectuuid."') ";
        $sqlParams = array($taskuuid);
        if (!empty($timepointuuid)){
            $sql .= " and bbt.timepoint_uuid = ? ";
            $sqlParams = array($taskuuid, $timepointuuid);
        }else{
            $sql .= " order by bbt.timepoint desc";
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $des = "";
        if (!empty($data)){
            if ($data[0]['module_type'] == xphp_get_config('module','MODULE_TYPE')['VOL_CDP']){
                if (!empty($info['current_timepoint'])){
                    $des = $info['current_timepoint'];
                }else{
                    $des = $data[0]['end_timestamp'];
                }
            }else{
                $des = $data[0]['timepoint']."(".xphp_get_desc('Pf','BACKUP_MODE_DES')[intval($data[0]['backup_mode'])].xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT').")";
            }
        }
        return $des;
    }

    /**
     * 获取历史任务验证报告
     * @return json
     */
    public function getVerifyJobReport($params){
        $historyuuid = $params['history_uuid']; //历史任务uuid
        $id = $params['history_id'];    //历史任务id
        $sendFlag = $params['send_flag'];  //发送邮件标志
        if(!in_array(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'], xphp_get_config('app', 'ENTERPRISE'))){
            $content = file_get_contents(DATA_PATH . 'email/email-verify-data-report-oem.html');
        }else if(xphp_get_config('app', 'lang') == "en-us"){
            $content = file_get_contents(DATA_PATH . 'email/email-verify-data-report-en.html');
        }else{
            $content = file_get_contents(DATA_PATH . 'email/email-verify-data-report.html');
        }
        //组合报告
        $sql = "select bht.history_uuid, bht.task_name, bht.task_type, bht.error_code, unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time, bht.details from bd_history_task bht ";
        $sqlParams = array();
        if(!empty($historyuuid)){
            $sql .= " where bht.id = ? ";
            $sqlParams = array($historyuuid);
        }else{
            $sql .= " where bht.id = ? ";
            $sqlParams = array($id);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $taskTypeDes = xphp_get_desc('Pf', 'TASKTYPEDES');
//        $utils = Xphp::instance('Utils');

        $content = str_replace('reportTime', date('Y-m-d H:i:s'), $content);
        //任务名
        $taskName =  $data[0]['task_name']."[".$taskTypeDes[intval($data[0]['task_type'])]."]";
        $content = str_replace('taskName', $taskName, $content);


        //历史任务状态
        $taskStatus = $this->getHistoryJobResultDes($data[0]['error_code']);
        $fontStyle = $data[0]['error_code'] == 0 ? 'font-success' : 'font-error';
        $content = str_replace('font-style', $fontStyle, $content);
        $content = str_replace('taskStatus', $taskStatus, $content);
        //开始时间
        $startTime = $this->parseDate($data[0]['start_time']);
        $content = str_replace('startTime', $startTime, $content);
        //结束时间
        $finishTime = $this->parseDate($data[0]['finish_time']);
        $content = str_replace('endTime', $finishTime, $content);
        //持续时间
        $intval = intval($data[0]['finish_time']) - intval($data[0]['start_time']);
        $intvalTime = $this->secToTime($intval);
        $content = str_replace('runTime', $intvalTime, $content);

        $historyuuid = $data[0]['history_uuid'];
        $sqlVm = "select distinct ssr.report_id, ssr.timestamp, ssr.screen_shot_path, ssr.item_error_code, ssr.item_name, unix_timestamp(ssr.start_time) start_time, unix_timestamp(ssr.end_time) end_time,  ssr.extension_info,bht.module_type, bht.submodule_type
                  from sr_surebackup_report ssr, bd_history_task bht where ssr.history_uuid = bht.history_uuid and ssr.history_uuid = ? ";
        $dataVm = $this->dbSelect($sqlVm, array($historyuuid));
        //获取报告中的虚拟机
        $successNum = 0;
        $vmInfo = array();
        foreach ($dataVm as $d){
            $extensionInfo = json_decode($d['extension_info'],true);
            $info = array(
                'name' => $d['item_name'],//对象名
                'status' => xphp_get_desc('Vm', 'VERIFY_VM_STATUS')[intval($d['item_error_code'])],//状态
                'start_time' => $this->parseDate($d['start_time']), //开始时间
                'end_time' =>$this->parseDate($d['end_time']),//结束时间
                'integrity_check_status' => intval($extensionInfo['integrity_check_status']),//完整性校验
                'vir_det_kill_status' =>  intval($extensionInfo['vir_det_kill_status']),//病毒查杀
                'doc_consistency_status' =>  intval($extensionInfo['doc_consistency_status']),//文件对比
                'screen_compare_status' =>  intval($extensionInfo['screen_compare_status']),//截屏对比
                'item_status' =>  intval($extensionInfo['item_status']),//对象状态
                'ping_test_status' =>  intval($extensionInfo['ping_test_status']),//ping
                'heartbeat_status' =>  intval($extensionInfo['heartbeat_status']),//心跳测试
                'print_screen_status' =>  intval($extensionInfo['print_screen_status']),//截屏
                'item_status_des' => xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($extensionInfo['item_status'])],//对象状态描述
                'integrity_check_status_des' =>  xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($extensionInfo['integrity_check_status'])],//完整性校验
                'vir_det_kill_status_des' =>  xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($extensionInfo['vir_det_kill_status'])],//病毒查杀
                'doc_consistency_status_des' =>  xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($extensionInfo['doc_consistency_status'])],//文件对比
                'screen_compare_status_des' =>  xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($extensionInfo['screen_compare_status'])],//截屏对比
                'ping_test_status_des' =>  xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($extensionInfo['ping_test_status'])],//ping
                'heartbeat_status_des' =>  xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($extensionInfo['heartbeat_status'])],//心跳测试
                'print_screen_status_des' =>  xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS')[intval($extensionInfo['print_screen_status'])],//截屏
                'screen_src' => $d['screen_shot_path'],
                'report_id' => $d['report_id'],
                'timepoint' => $d['timestamp'],
                'total_file_num' => intval($extensionInfo['total_file_num']),//总文件数量
                'scan_file_num' => intval($extensionInfo['scan_file_num']),//扫描文件数量
                'infected_file_num' => intval($extensionInfo['infected_file_num']),//病毒文件数量
            );
            if($extensionInfo['item_status'] == 5){
                //成功
                $successNum++;
            }
            $vmInfo[] = $info;
        }

        //虚拟机总个数
        $content = str_replace('vmNum', count($vmInfo), $content);
        //成功虚拟机个数
        $content = str_replace('runNum', $successNum, $content);
        //虚拟机列表显示
        $vmtable = "";
        $imgContent = "";
        foreach ($vmInfo as $d){
            //组合验证报告虚拟机列表信息显示
            $color = "color: #313344;";
            if($d['infected_file_num'] > 0){
                $color = "color:#f1416c;";
            }
            $info = ' <li>'.
                '<div style="margin-bottom:30px;">'.$d['name'].'</div>'.
                '<div class="content-list" >'.
                '<p ><span>'.xphp_get_lang('UI_PUBLIC_STATUS').': </span><span class="'.$this->getVerifyClass($d['item_status']).'">'.$d['item_status_des'].'</span></p>'.
                '<p ><span>'.xphp_get_lang('UI_SAFE_STRATEGY_INTEGRITY_CHECK').': </span><span class="'.$this->getVerifyClass($d['integrity_check_status']).'">'.$d['integrity_check_status_des'].'</span></p>'.
                '<p ><span>'.xphp_get_lang('UI_PLATFORM_RECOVERY_JOB_VIRUS_SCAN').': </span><span class="'.$this->getVerifyClass($d['vir_det_kill_status']).'">'.$d['vir_det_kill_status_des'].'</span></p>'.
                '</div>'.
                '<div class="content-list">'.
                '<p ><span>'.xphp_get_lang('UI_DRILLS_DETAIL_BACKUP_TIMEPOINT').': </span><span>'.$d['timepoint'].'</span></p>'.
                '<p ><span>'.xphp_get_lang('UI_VERIFY_SCREEN').': </span><span class="'.$this->getVerifyClass($d['print_screen_status']).'">'.$d['print_screen_status_des'].'</span></p>'.
                '<p ><span>'.xphp_get_lang('UI_VERIFY_VIRUS_TOTAL_FILE_NUM').': </span><span style="text-align: left;">'.$d['total_file_num'].'</span></p>'.
                '</div>'.
                '<div class="content-list">'.
                '<p ><span>'.xphp_get_lang('UI_JOB_START_TIME').': </span><span>'.$d['start_time'].'</span></p>'.
                '<p ><span>'.xphp_get_lang('UI_VERIFY_PING_TEST').': </span><span class="'.$this->getVerifyClass($d['ping_test_status']).'">'.$d['ping_test_status_des'].'</span></p>'.
                '<p ><span>'.xphp_get_lang('UI_VERIFY_VIRUS_SCAN_FILE_NUM').': </span><span style="text-align: left;">'.$d['scan_file_num'].'</span></p>'.
                '</div>'.
                '<div class="content-list">'.
                '<p ><span>'.xphp_get_lang('UI_JOB_OVER_TIME').': </span><span>'.$d['end_time'].'</span></p>'.
                '<p ><span>'.xphp_get_lang('UI_VERIFY_HEARTBEAT').': </span><span class="'.$this->getVerifyClass($d['heartbeat_status']).'">'.$d['heartbeat_status_des'].'</span></p>'.
                '<p ><span>'.xphp_get_lang('UI_VERIFY_VIRUS_INFECTED_FILE_NUM').': </span><span style="text-align: left;'.$color.'">'.$d['infected_file_num'].'</span></p>'.
                '</div>'.
                '</li>';
            $vmtable .= $info;
            if(!empty($d['screen_src'])){
                $imgSrc = "";
                if($sendFlag){
                    //发送邮件使用内嵌附件图片展示
                    $imgSrc .= '<img width="100%" style="margin-bottom:20px;" src="cid:'.$d['report_id'].'" >';
                }else {
                    $filepath = $d['screen_src'];
                    $img = file_get_contents($filepath);
                    //获取图片信息
                    $imgbase64 = chunk_split(base64_encode($img));
                    //输出base64图片
                    if (!empty(trim($imgbase64))) {
                        $imgSrc .= '<img width="100%" style="margin-bottom:20px;" src="data:image/jpg/png/gif;base64,' . $imgbase64 . '" >';
                    }
                }
                $imgContent = '<span class="screenshot-title">'.xphp_get_lang('WEB_PLATFORM_GMP_JOB_SCREENS_VERIFY').'</span>'.
                    '<hr style="display: inline-block;width: 851px;height: 1px;background: #EFF2F5;border: none;margin-bottom: 5px">'.
                    '<div style="margin-top: 10px">'.$imgSrc.'</div>';
            }
        }
        $content = str_replace('objectContent', $vmtable, $content);
        //截屏信息显示
        $content = str_replace('imgContent', $imgContent, $content);
        $ip = $_SERVER['SERVER_ADDR'];
        $serverIpArr = explode(' ', $ip);
        $host = 'https://' . $serverIpArr[0];
        $ipInfo = '<a class="font-success" style="text-decoration: none" href="' . $host . '">' . $host . '</a>  ';
        $content = str_replace('backupServerHost', $ipInfo, $content);
        $content = str_replace('supportEmailHref', 'mailto: '.xphp_get_config('app', 'SYSTEM_INFO')['company_email'], $content);
        $content = str_replace('supportEmail', xphp_get_config('app','SYSTEM_INFO')['company_email'], $content);

        $info = array(
            'report' => $content
        );
        return $info;
    }

    /**
     * 发送数据验证报告到邮箱
     * @param unknown $params
     * @return string
     */
    public function sendVerifyEmail($params){
        $historyId= $params['history_id'];
        $queryParams = array(
            'history_uuid' => '',
            'taskuuid' => "",
            'history_id' => $historyId,
            'send_flag' => true
        );
        $report = $this->getVerifyJobReport($queryParams);
        $content = $report['report'];
        $userInfo = xphp_get_user_info();
        $sql = "select email from bd_user where user_uuid = ? ";
        $data = $this->dbSelect($sql, array($userInfo['userUuid']));
        $email = $data[0]['email'];
        //未配置邮箱返回失败提醒
        if(empty($email)){
            exit($this->muOpResult(false, xphp_get_lang('UI_VERIFY_REPORT_SEND_TO_EMAIL_DETAIL'), xphp_get_lang('UI_VERIFY_REPORT_SEND_TO_EMAIL_DETAIL_TIPS'), "warning"));
        }
        //获取报告附件图片
        $sql = "select ssbr.report_id, ssbr.screen_shot_path
                  from sr_surebackup_report ssbr, bd_history_task bht where ssbr.history_uuid = bht.history_uuid and bht.id = ?";
        $data = $this->dbSelect($sql, array($historyId));
        $attachment = array();
        $innerContent = array();
        //添加附件
        foreach ($data as $d) {
            $innerContent[] = array(
                'img_src' => $d['screen_shot_path'],
                'id' =>$d['report_id']
            );
            $attachment[] = $d['screen_shot_path'];
        }
        //发送报告
        $title = xphp_get_lang('UI_VERIFY_REPORT');
        //判断此次传入邮箱信息是否和之前一样
        //从数据库获取邮件信息
        $sql="select smtp_config, receive_email from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql);
        $setEmail = json_decode($data[0]['receive_email'], TRUE);
        if (!empty($setEmail)){
            $resultEmail = array_unique(array_merge(array($email), $setEmail));
        }else{
            $resultEmail = array_unique(array($email));
        }

        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        $pass = v1_decrypt($smtpConfig['pass']);
        $encryption = intval($smtpConfig['encryption']);
        $encryption = xphp_get_config('email','EMAIL_ENCRYPTION_TYPE')[$encryption];
        //直接调用发送邮件接口
        $emailUtil = new Email();
        $emailConfig = xphp_get_config('email','EMAIL');

        $emailUtil->config($smtpConfig['host'], $smtpConfig['port'], $emailConfig['authentication'],
            $smtpConfig['email'], $pass, $encryption);
        $result = $emailUtil->sendmail($resultEmail, $title, $content, $attachment, [], $innerContent, false);

        $operate = xphp_get_lang('UI_VERIFY_REPORT_SEND_TO_EMAIL_DETAIL').": " . $email;
        if($result){
            return $this->muOpResult(true, $operate);
        }else {
            return $this->muOpResult(false, $operate);
        }

    }

    /**
     * 得到历史任务结果描述
     * @param int $errorCode
     */
    public function getHistoryJobResultDes($errorCode){
        //异常的错误
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        //中止的错误
        $discontinue = array(
            'BD_TASK_BE_CANCELLED_ERROR'
        );
        if(in_array(xphp_get_config('error', 'errorCode')[$errorCode], $abnormal)){
            return xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL');
        }
        if(in_array(xphp_get_config('error', 'errorCode')[$errorCode], $discontinue)){
            return xphp_get_lang('WEB_PLATFORM_DES_DISCONTINUE');
        }
        return $errorCode == 0 ? xphp_get_lang('WEB_PUBLIC_SUCCESS') : xphp_get_lang('WEB_PUBLIC_FAILURE');
    }

    /**
     *      把秒数转换为时分秒的格式
     *      @param Int $times 时间，单位 秒
     *      @return String
     */
    public function secToTime($times){
        $result = '00:00:00';
        if ($times>0) {
            $hour = $this->formartTimeString(floor($times/3600));
            $minute = $this->formartTimeString(floor(($times-3600 * $hour)/60));
            $second = $this->formartTimeString(floor((($times-3600 * $hour) - 60 * $minute) % 60));
            $result = $hour . ':' . $minute . ':' . $second;
        }
        return $result;
    }

    /**
     * 转化时间格式 补齐  0-9 补齐00-09
     */
    public function formartTimeString($str){
        if($str < 10){
            return "0" . $str;
        }
        return $str;
    }

    public function getVerifyClass($status){
        $labelClass = "vm-status-waiting";
        switch(intval($status)){
            case 0:	//未知
                $labelClass = "vm-status-waiting";
                break;
            case 1:	//等待
                $labelClass = "vm-status-waiting";
                break;
            case 2:	//运行
                $labelClass = "vm-status-success";
                break;
            case 3:	//跳过
                $labelClass = "vm-status-waiting";
                break;
            case 4:	//错误
                $labelClass = "vm-status-error";
                break;
            case 5:	//成功
                $labelClass = "vm-status-success";
                break;
            case 6:	//完成
                $labelClass = "vm-status-success";
                break;
            case 7:	//异常
                $labelClass = "vm-status-waiting";
                break;
        }
        return $labelClass;
    }

}
