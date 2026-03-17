<?php

namespace app\v1\verification\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\VerifyOpcode;
use app\v1\resources\v0\logic\Node;

/**
 * note          数据验证CDM 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class AppGroup extends Base
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
     * 添加应用组
     * @return json
     */
    public function addAppgroup($params = [])
    {

        $pfMsg['appgroup_name'] = $params['appgroup_name'];
        $pfMsg['node_uuid'] = $params['node_uuid'];
        $pfMsg['storage_uuid'] = $params['storage_uuid'];
        $pfMsg['description'] = $params['description'];
        $pfMsg['app_items'] = $this->groupAppItems($params['object_list'], 1);

        $opName = 'SR_OP_CODE_CREATE_APP_GROUP';
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbSRMsgs($opName, $params['node_uuid'], $msg);

        $result = $mbResult['result'];
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改应用组
     * @return json
     */
    public function editAppgroup($params = [])
    {
        $pfMsg['appgroup_uuid'] = $params['appgroup_uuid'];
        $pfMsg['appgroup_name'] = $params['appgroup_name'];
        $pfMsg['node_uuid'] = $params['node_uuid'];
        $pfMsg['storage_uuid'] = $params['storage_uuid'];
        $pfMsg['description'] = $params['description'];
        $pfMsg['app_items'] = $this->groupAppItems($params['object_list'], 1);

        $opName = 'SR_OP_CODE_EDIT_APP_GROUP';
        $msg = json_encode($pfMsg);

        $mbResult = $this->service()->mbSRMsgs($opName, $params['node_uuid'], $msg);

        $result = $mbResult['result'];
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除应用组
     * @return json
     */
    public function deleteAppgroup($params = [])
    {
        $pfMsg['appgroup_uuids'] = $params['appgroup_list'];
        $pfMsg['node_uuid'] = $params['node_uuid'];

        $opName = 'SR_OP_CODE_DELETE_APP_GROUP';
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbSRMsgs($opName, $params['node_uuid'], $msg);

        $result = $mbResult['result'];
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取应用组列表
     * @return json
     */
    public function getAppGroupList($params = [])
    {
        $info = array();
//        $info['rows'][] = array(
//            'appgroup_uuid' => "bf133bf2-99aa-4e40-9ad6-d2a35a2f7b20",
//            'appgroup_name' => "应用组1",
//            'description' => "这是测试代码",
//            'object_num' => 2,
//            'create_time' => "2024-8-1 12:00:00",
//        );
//        $info['total'] = 1;
//
//        return $info;
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
        $sql = "select sag.node_uuid,sag.appgroup_uuid, sag.appgroup_name, count(sa.item_uuid) as object_num, unix_timestamp(sag.create_time) as create_time, sag.description
                from sr_application_group sag left join sr_application_item sa on sag.appgroup_uuid = sa.appgroup_uuid ";
        $sqlcount = "select count(distinct sag.appgroup_uuid) as count from sr_application_group sag left join sr_application_item sa on sag.appgroup_uuid = sa.appgroup_uuid ";
        $sqlparams = array();
        //按应用组名搜索
        if (!empty($keyword)) {
            $sql .= " where sag.appgroup_name like '%" . $keyword . "%' or sag.description like '%" . $keyword . "%'";
            $sqlcount .= " where sag.appgroup_name like '%" . $keyword . "%' or sag.description like '%" . $keyword . "%'";
        }

        $sql .= " group by sa.appgroup_uuid ";

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
        $count = $this->dbSelect($sqlcount);
        $info = array(
            'rows' => array(),
            'total' => intval($count[0]['count']),
        );
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $info['rows'][] = array(
                'appgroup_uuid' => $each['appgroup_uuid'],
                'appgroup_name' => $each['appgroup_name'],
                'description' => $each['description'],
                'object_num' => intval($each['object_num']),
                'create_time' => date('Y-m-d H:i:s', intval($each['create_time'])),
                'node_uuid' => $each['node_uuid']

            );
        }


        return $info;
    }

    /**
     * 删除应用组
     * @return json
     */
    public function getAppGroupDetail($appgroupuuid)
    {

        //获取数据
        $sql = "select sag.appgroup_uuid, sag.storage_uuid, sag.appgroup_name, sag.description, unix_timestamp(sag.create_time) as create_time, sag.node_uuid, bn.ip, bn.host_name, bn.node_nickname 
                from sr_application_group sag  left join bd_node bn on bn.node_uuid = sag.node_uuid where sag.appgroup_uuid = ? ";
        $sqlparams = array($appgroupuuid);
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $info = array();
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $info = array(
                'appgroup_uuid' => $each['appgroup_uuid'],
                'appgroup_name' => $each['appgroup_name'],
                'node_uuid' => $each['node_uuid'],
                'storage_uuid' => $each['storage_uuid'],
                'node_name' => Node::instance()->getNodeShowName($each['ip'], $each['node_nickname'], $each['host_name']),
                'module_type_list' => $this->getModuleList($each['appgroup_uuid']),
                'description' => $each['description'],
                'create_time' => date('Y-m-d H:i:s', intval($each['create_time'])),
                'object_list' => $this->getOldItemList($each['appgroup_uuid']),
            );
        }

        return $info;
    }

    /**
     * 获取应用组名称
     * @return array
     */
    public function getAppgroupName($params = []){
        $name = xphp_get_lang('UI_PLATFORM_LAB_APP_GROUP');
        $oldName = $name;
        for ($i = 1; $i < 1000; $i++) {
            $name .= $i;
            $sql = "select appgroup_id from sr_application_group where appgroup_name = ?";
            $data = $this->dbSelect($sql, array($name));
            if (empty($data)) break;  //找到合适的名称提前退出循环
            $name = $oldName;
        }

        return array(
            'name' => $name
        );

    }



    /**
     * 组合应用组对象列表
     * @param $list
     * @return array
     */
    public function groupAppItems($list, $fileThread = 1){
        $info = array();
        foreach ($list as $d) {

            $timepoint_uuids = array();
            $fileList = array();
            foreach ($d['file_compare_config']['doc_list'] as $i) {
                $fileList[] = array(
                    'doc_path' => $i['doc_path'],
                    'encode' => $i['encode'],
                    'path_name' => $i['path_name'],
                    'path_type' => $i['path_type']
                );
            }
            foreach ($d['verify_config']['timepoint_uuid_list']['timepoint_uuids'] as $i) {
                $timepoint_uuids[] = array(
                    'timepoint_uuid' => $i['timepoint_uuid'],
                    'timepoint' => $i['timepoint'],
                    'backup_mode' => $i['backup_mode'],
                );
            }
            $driverConfig = $d['driver_config'];
            $extensionInfo = [
                'group_name' => '',
                'group_uuid' => '',
                'username' => '',
                'password' => '',
                'controller_ip' => '',
                'driver_replace_flag' => $driverConfig['driver_check_status'] == 3 ?
                    xphp_get_config('app', 'FLAG')['SET'] : xphp_get_config('app', 'FLAG')['UNSET'],
                'driver_hw_id_map' => $driverConfig['driver_hw_id_map']
            ];
            $info[] = array(
                'order' => intval($d['sort_num']),
                'start_vm_flag' => v1_parse_bool_to_flag($d['general_config']['start_vm_flag']),
                'module_type' => intval($d['general_config']['module_type']),
                'submodule_type' => intval($d['general_config']['submodule_type']),
                'item_name' => $d['general_config']['item_name'],
                'item_uuid' => $d['general_config']['item_uuid'],
                'backup_task_uuid' => $d['general_config']['backup_task_uuid'],
                'max_boot_time' =>  intval(explode(':',$d['verify_config']['max_boot_time'])[0]) * 3600 +  intval(explode(':',$d['verify_config']['max_boot_time'])[1]) * 60 + intval(explode(':',$d['verify_config']['max_boot_time'])[2]),
                'timepoint_uuids' => json_encode(array(
                    'timepoint_uuids' => $timepoint_uuids,
                    'timepoint_range' => $d['verify_config']['timepoint_range'],
                    'max_timepoint_verify' => intval($d['verify_config']['max_timepoint_verify']),
                    'vol_cdp_datetime' => !empty($d['verify_config']['vol_cdp_datetime'])?$d['verify_config']['vol_cdp_datetime']:""
                )),
                'role' => intval($d['general_config']['role']),
                'extension_info' => "",
                'doc_list' => !empty($fileList)?json_encode(array("doc_list"=>$fileList)):"",
                'advanced_config' => json_encode(array(
                    'auto_conf_flag' => 1,
                    'cpu_mode' => intval($d['general_config']['cpu_mode']),
                    'cpu_arch' => intval($d['general_config']['cpu_arch']),
                    'cpu_socket' => intval($d['general_config']['cpu_num']),
                    'cores_per_socket' => intval($d['general_config']['core_num']),
                    'vm_memory' => intval($d['general_config']['memory_size']),
                    'os_type' => $d['general_config']['os_type'],
                    'os_version' => $d['general_config']['os_version'],
                    'os_version_name' => $d['general_config']['os_version_name'],
                    'disk_target_bus' => $d['general_config']['disk_target_bus'],
                    'netcard_target_bus' => $d['general_config']['netcard_target_bus'],
                    'ping_flag' => v1_parse_bool_to_flag($d['verify_config']['ping_test_flag']),
                    'screen_flag' => v1_parse_bool_to_flag($d['verify_config']['print_screen_flag']),
                    'heartbeat_flag' => v1_parse_bool_to_flag($d['verify_config']['heartbeat_flag']),
                    'doc_consistency_flag' => empty($fileList) ? 2:intval($d['file_compare_config']['doc_consistency_flag']),
                    'keep_mac_flag' => 1,
                    'screen_compare_flag' => 1,
                    'start_vm_flag' => v1_parse_bool_to_flag($d['general_config']['start_vm_flag']),
                    'vir_det_kill_flag'=> isset($d['safe_config']['vir_det_kill_flag']) ? intval($d['safe_config']['vir_det_kill_flag']) : intval($d['verify_config']['vir_det_kill_flag']),
                    'virus_det_info' => $d['safe_config']['virus_scan_config_list'],
                    'first_vir_stop_flag' => 2,
                    'integrity_check_flag'=> isset($d['safe_config']['integrity_check_flag']) ? v1_parse_bool_to_flag($d['safe_config']['integrity_check_flag']) : v1_parse_bool_to_flag($d['verify_config']['integrity_check_flag']),
                    'doc_compare_thread' => intval($fileThread),
                    'max_ping_wait_time' =>  intval(explode(':',$d['verify_config']['max_ping_wait_time'])[0]) * 3600 +  intval(explode(':',$d['verify_config']['max_ping_wait_time'])[1]) * 60 + intval(explode(':',$d['verify_config']['max_ping_wait_time'])[2]),
                )),
                'netcards_info' => $this->getObjectNetworkInfo($d['network_config']),
                'extension_info' => json_encode($extensionInfo),
            );

        }
        return $info;
    }


    /**
     * 获取模块类型数组
     * @param $appgroupuuid
     */
    private function getModuleList($appgroupuuid){
        $sql = "select module_type, submodule_type from sr_application_item where appgroup_uuid = ?";
        $data = $this->dbSelect($sql, array($appgroupuuid));
        $list = array();
        foreach ($data as $d) {
            $list[] = $d['module_type'].'_'.$d['submodule_type'];
        }

        return $list;
    }

    /**
     * 获取对象列表信息
     * @param $appgroupuuid
     */
    private function getOldItemList($appgroupuuid){
        $sql = "select item_uuid, item_name, backup_task_uuid, timepoint_uuids_json, advanced_config, ori_netcards_info, max_boot_time, module_type, submodule_type from sr_application_item where appgroup_uuid = ? ";
        $data = $this->dbSelect($sql, array($appgroupuuid));
        $list = array();
        $modeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        foreach ($data as $d) {
            $doc_list = json_decode($d['doc_list_json'],true)['doc_list'];
            $timepoint = json_decode($d['timepoint_uuids_json'], true);
            $newesttimepoint = array();
            if(empty($timepoint['timepoint_uuids'])){
                //获取最新的时间点
                $sqlTimepoint = "select bbt.timepoint_uuid, bbt.timepoint, bbt.backup_mode from bd_backup_timepoint bbt 
                                left join vm_backup_timepoint vbt on bbt.timepoint_uuid = vbt.timepoint_uuid 
                                left join db_backup_timepoint dbt on bbt.timepoint_uuid = dbt.timepoint_uuid
                                left join fs_backup_timepoint fbt on bbt.timepoint_uuid = fbt.fs_timepoint_uuid 
                                left join os_backup_timepoint obt on bbt.timepoint_uuid = obt.timepoint_uuid
                                left join m365_backup_timepoint mbt on bbt.timepoint_uuid = mbt.m365_timepoint_uuid 
                                left join kube_backup_timepoint kbt on bbt.timepoint_uuid = kbt.timepoint_uuid
                                left join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid 
                                left join cdp_vol_backup_agent agent ON bbt.timepoint_uuid = agent.timepoint_uuid
                                left join cdp_vol_backup_vol_set vol ON agent.id = vol.backup_agent_id 
                                where bbt.task_uuid = ? 
                                and (vbt.vm_uuid = '".$d['item_uuid']."' or fbt.agent_uuid = '".$d['item_uuid']."' or dbt.db_uuid = '".$d['item_uuid']."' or obt.agent_uuid = '".$d['item_uuid']."' or mbt.organization_uuid = '".$d['item_uuid']."' or kbt.cluster_uuid = '".$d['item_uuid']."' or agent.master_agent_uuid = '".$d['item_uuid']."') 
                                order by bbt.timepoint desc";
                $dataTimepoint = $this->dbSelect($sqlTimepoint, array($d['backup_task_uuid']));
                $newesttimepoint = array(
                    'timepoint_uuid' => $dataTimepoint[0]['timepoint_uuid'],
                    'timepoint' => $dataTimepoint[0]['timepoint'],
                    'backup_mode' => intval($dataTimepoint[0]['backup_mode']),
                    'backup_mode_des' => $modeDes[$dataTimepoint[0]['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT')
                );
            }else{
                $sqlTimepoint = "select timepoint_uuid, timepoint, backup_mode from bd_backup_timepoint where timepoint_uuid = ? ";
                $dataTimepoint = $this->dbSelect($sqlTimepoint, array($timepoint['timepoint_uuids'][0]));
                $newesttimepoint = array(
                    'timepoint_uuid' => $dataTimepoint[0]['timepoint_uuid'],
                    'timepoint' => $dataTimepoint[0]['timepoint'],
                    'backup_mode' => intval($dataTimepoint[0]['backup_mode']),
                    'backup_mode_des' => $modeDes[$dataTimepoint[0]['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT')
                );
            }
            $advance_config = json_decode($d['advanced_config'], true);
            $networkList = json_decode($d['ori_netcards_info'], true)['netcard_list'];
            $memorySize = $advance_config['vm_memory'] * 1024 * 1024 * 1024;
            $memoryInfo = v1_calsize_to_value_and_unit($memorySize);
            $general_config = array(
                'task_uuid' => $d['backup_task_uuid'],
                'item_uuid' => $d['item_uuid'],
                'item_name' => $d['item_name'],
                'cpu_num' => $advance_config['cpu_socket'],
                'core_num' => $advance_config['cores_per_socket'],
                'memory_size' => $memorySize,
                'memory_size_int' => $memoryInfo['value'],
                'memory_size_unit' => $memoryInfo['unit'],
                'os_type' => $advance_config['os_type'],
                'os_version' => $advance_config['os_version'],
                'os_version_name' => !empty($advance_config['os_version_name'])?$advance_config['os_version_name']:xphp_get_config('app','NULLSPACE'),
                'cpu_mode' => $advance_config['cpu_mode'],
                'cpu_arch' => $advance_config['cpu_arch'],
                'start_vm_flag' => $advance_config['start_vm_flag'],
                'module_type' => intval($d['module_type']),
                'sub_module_type' => intval($d['submodule_type']),
                'disk_target_bus' => intval($advance_config['disk_target_bus']),
                'netcard_target_bus' => intval($advance_config['netcard_target_bus']),
                'disk_target_bus_des' => xphp_get_desc('Verification','DSIK_TARGET_BUS_DES')[intval($advance_config['disk_target_bus'])],
                'netcard_target_bus_des' => xphp_get_desc('Verification','NETCARD_TARGET_BUS_DES')[intval($advance_config['netcard_target_bus'])],
            );

            $verify_config = array(
                'timepoint_uuid_list' => $timepoint,
                'newest_timepoint' => $newesttimepoint,
                'ping_test_flag' => v1_parse_flag_to_bool(intval($advance_config['ping_flag'])),
                'heartbeat_flag' => v1_parse_flag_to_bool(intval($advance_config['heartbeat_flag'])),
                'print_screen_flag' => v1_parse_flag_to_bool(intval($advance_config['screen_flag'])),
                'integrity_check_flag' => v1_parse_flag_to_bool(intval($advance_config['integrity_check_flag'])),
                'vir_det_kill_flag' => intval($advance_config['vir_det_kill_flag']),
                'virus_scan_config_list' => json_decode($advance_config['virus_det_info'], true) ?: [
                    [
                        'strategy_type' => 0,
                        'recover_policy' => 0,
                        'interrupt_policy' => 0,
                        'scan_strategy' => 0,
                        'all_timepoints_flag' => 0,
                        'skip_application_group_flag' => 0
                    ]
                ], // 兼容配置为空的
                'doc_consistency_flag' => v1_parse_flag_to_bool(intval($advance_config['doc_consistency_flag'])),
                'max_boot_time' => gmdate("H:i:s", intval($d['max_boot_time'])),
                'max_ping_wait_time' => gmdate("H:i:s", intval($advance_config['max_ping_wait_time'])),
                'doc_list' => $doc_list
            );

            $network_config = array();
            foreach ($networkList as $i) {
                $network_config[] = array(
                    'network_name' => $i['network_name'],
                    'ip' => $i['ip_address'],
                    'netmask'=> $i['netmask'],
                    'gateway' => $i['gateway']
                );
            }

            $list[] = array(
                'general_config' => $general_config,
                'verify_config' => $verify_config,
                'network_config' => $network_config
            );

        }

        return $list;
    }


    /**
     * 组合对象网卡信息
     * @param $list
     * @return false|string
     */
    protected function getObjectNetworkInfo($list){
        $network = array();
        foreach ($list as $d) {
            $network[] = array(
                'dns_server'=> "",
                'gateway'=> $d['gateway'],
                'ip_address' => $d['ip'],
                'ip_segment' => "",
                'mac_address' => !empty($d['mac_address'])? $d['mac_address']:"",
                'netmask' => $d['netmask'],
                'network_name' => $d['network_name'],
                'network_uuid' => "",
                'spilt_segment_num' => 0,
            );
        }

        if (empty($list)){
            return "";
        }
        $info = array(
            'netcard_list' => $network
        );
        return json_encode($info);

    }

}
