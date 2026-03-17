<?php

namespace app\v1\verification\v0\logic;

use app\v1\common\logic\Recover;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\VerifyOpcode;
use app\v1\system\v0\logic\Auth;

/**
 * note          数据验证CDM 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Verification extends Recover
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
     * 创建数据验证任务
     * @param array $params 参数
     * @return string
     */
    public function createVerifyJob($params = [])
    {
        $opName = 'SR_OP_CODE_CREATE_SUREBACKUP';
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }

        //检查授权数量
        $auth = $this->checkAuthNum($params);
        if(!$auth){
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_MACHINE_OS_LICENSE_SHORTAGE'));
        }


        //验证的备份任务名
        $pfMsg['task_name'] = $params['task_name'];

        //模块类型
        $pfMsg['module_type'] = xphp_get_config('module')['MODULE_TYPE']['SUREBACKUP'];

        //任务类型
        $pfMsg['task_type'] = xphp_get_config('task')['TASKTYPE']['SURE_BACKUP'];
        //验证模式
        $pfMsg['surebackup_task_type'] = intval($params['basic_info']['verify_mode']);
        if(xphp_get_config('app', 'SYSTEM_INFO')['vendor'] ==   xphp_get_config('app','VENDOR_LIST')['gmp']){
            $pfMsg['surebackup_task_type'] = 3;
        }
        //验证方式
        $pfMsg['automatic_verification_flag'] = intval($params['basic_info']['automatic_verifitied_flag']);
        //应用组
        $pfMsg['appgroup_uuid'] = $params['basic_info']['appgroup_uuid'];
        //虚拟演练室
        $pfMsg['virtual_lab_uuid'] = $params['basic_info']['virtual_lab_uuid'];

        $pfMsg['hypervisor_type'] = $this->getLabHypervisor($params['basic_info']['virtual_lab_uuid']);

        //虚拟机信息列表
        $pfMsg['sr_items'] = AppGroup::instance()->groupAppItems($params['item_list'], $params['high_strategy']['doc_compare_thread']);

        //同时启动虚拟机个数
        $pfMsg['limit_boot_vm_num'] = intval($params['high_strategy']['limit_boot_vm_num']);
        //挂载协议
        $pfMsg['mount_protocol'] = intval($params['high_strategy']['mount_protocol']);
        //存储挂载点IP地址或域名
        $pfMsg['backup_server_ip'] = $params['high_strategy']['backup_server_ip'];

        //存储挂载点IP地址或域名
        $pfMsg['nfs_server_ip'] = $params['high_strategy']['nfs_server_ip'];

        //忽略节点限制
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_strategy']['ignore_resource_limiting_flag']);


        //安全策略
        $pfMsg['safe_config_strategy'] = array(
            'worm_flag' => 0,
            'worm_protection_time' => 0,
            'virus_scan_flag' => 0,
            'integrity_check_flag' => 0,
        );


        //转换时间策略参数名适配,默认关闭滚动标志和间隔时间
        $timeStrategy = array(
            'type' => $params['time_strategy']['type'],
            'strategy' => array(
                'mode' => $params['time_strategy']['strategy']['mode'],
                'type' => $params['time_strategy']['strategy']['type'],
                'days' => $params['time_strategy']['strategy']['days'],
                'start_time' => $params['time_strategy']['strategy']['startTime'],
                'roll_flag' => false,
                'roll_interval' => 0,
                'end_time' => $params['time_strategy']['strategy']['endTime'],
                'frequency' => "",

            )
        );
        $pfMsg['time_strategy_list'] =  $this->groupRecoverTimeList($timeStrategy);

        //限速策略
        $pfMsg['speed_limit_strategy'] = array();

        //传输策略
        $pfMsg['transport_strategy'] = array(
            'encrypt_flag' => 0,
            'compress_flag' => 0,
            'speed_limit_flag' => 0,
            'max_speed' => 0,
            'block_size' => 0,
            'network_uuid' => 0,
            'compress_method' => 0,
            'reconnect_times' => 0,
            'reconnect_interval' => 0,
            'encrypt_method' => 0,
            'strategy_group_uuid' => ""

        );

        //全局策略uuid（未使用）
        $pfMsg['strategy_group_uuid'] = "";

        //参数补全
        $pfMsg['sub_module_type'] = 1;
        $pfMsg['recovery_position'] = 0;
        $pfMsg['thread_num'] = 1;
        $pfMsg['transport_ip_segment'] = "";
        $pfMsg['recovery_type'] = 0;

        //任务所在节点
        $pfMsg['node_uuid'] = $params['node_uuid'];
        $pfMsg['storage_uuid'] = $params['storage_uuid'];
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbSRMsgs($opName, $params['node_uuid'], $msg , 0, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            //立即启动数据验证任务
//            if($params['time_strategy']['type'] == 1){
//                $startResult = $this->startVerifyJob($params['task_name']);
//            }else{
//                $startResult = true;
//            }
            return $this->muOpResult($result, $operate, $msg, '', 0, $msg);
//            if($startResult){
//                //启动任务成功,直接返回创建任务成功
//                return $this->muOpResult($result, $operate, $msg, '', 0, $msg);
//            }else {
//                //启动任务失败,返回创建任务成功加上启动任务失败提示, 暂时没加提示
//                return $this->muOpResult($result, $operate, $msg, '', 0, $msg);
//            }
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改数据验证任务
     * @param array $params 参数
     * @return string
     */
    public function editVerifyJob($params = [])
    {
        $opName = 'SR_OP_CODE_EDIT_SUREBACKUP';
        $operate = $this->verifyOpcode->getOpcodeDes($opName);
        //检查授权是否过期
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        //检查授权数量
        $auth = $this->checkAuthNum($params);
        if(!$auth){
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_MACHINE_OS_LICENSE_SHORTAGE'));
        }
        //任务uuid
        $pfMsg['task_uuid'] = $params['task_uuid'];

        //验证的备份任务名
        $pfMsg['task_name'] = $params['task_name'];

        //模块类型
        $pfMsg['module_type'] = xphp_get_config('module')['MODULE_TYPE']['SUREBACKUP'];

        //任务类型
        $pfMsg['task_type'] = xphp_get_config('task')['TASKTYPE']['SURE_BACKUP'];
        //验证模式
        $pfMsg['surebackup_task_type'] = intval($params['basic_info']['verify_mode']);
        if(xphp_get_config('app', 'SYSTEM_INFO')['vendor'] ==   xphp_get_config('app','VENDOR_LIST')['gmp'] ){
            $pfMsg['surebackup_task_type'] = 3;
        }
        //验证方式
        $pfMsg['automatic_verification_flag'] = intval($params['basic_info']['automatic_verifitied_flag']);
        //应用组
        $pfMsg['appgroup_uuid'] = $params['basic_info']['appgroup_uuid'];
        //虚拟演练室
        $pfMsg['virtual_lab_uuid'] = $params['basic_info']['virtual_lab_uuid'];
        $pfMsg['hypervisor_type'] = $this->getLabHypervisor($params['basic_info']['virtual_lab_uuid']);

        //虚拟机信息列表
        $pfMsg['sr_items'] = AppGroup::instance()->groupAppItems($params['item_list'], $params['high_strategy']['doc_compare_thread']);

        //同时启动虚拟机个数
        $pfMsg['limit_boot_vm_num'] = intval($params['high_strategy']['limit_boot_vm_num']);
        //挂载协议
        $pfMsg['mount_protocol'] = intval($params['high_strategy']['mount_protocol']);
        //存储挂载点IP地址或域名
        $pfMsg['backup_server_ip'] = $params['high_strategy']['backup_server_ip'];

        //存储挂载点IP地址或域名
        $pfMsg['nfs_server_ip'] = $params['high_strategy']['nfs_server_ip'];

        //忽略节点限制
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_strategy']['ignore_resource_limiting_flag']);


//        //病毒查杀标记
//        $pfMsg['vir_det_kill_flag'] = intval($params['high_strategy']['vir_det_kill_flag']);
//        //数据完整性校验标记
//        $pfMsg['integrity_check_flag'] = intval($params['high_strategy']['integrity_check_flag']);

        //安全策略
        $pfMsg['safe_config_strategy'] = array(
            'worm_flag' => 0,
            'worm_protection_time' => 0,
            'virus_scan_flag' => 0,
            'integrity_check_flag' => 0,
        );


        //转换时间策略参数名适配
        $timeStrategy = array(
            'type' => $params['time_strategy']['type'],
            'strategy' => array(
                'mode' => $params['time_strategy']['strategy']['mode'],
                'type' => $params['time_strategy']['strategy']['type'],
                'days' => $params['time_strategy']['strategy']['days'],
                'start_time' => $params['time_strategy']['strategy']['startTime'],
                'roll_flag' => false,
                'roll_interval' => 0,
                'end_time' => $params['time_strategy']['strategy']['endTime'],
                'frequency' => "",

            )
        );
        $pfMsg['time_strategy_list'] =  $this->groupRecoverTimeList($timeStrategy);

        //限速策略
        $pfMsg['speed_limit_strategy'] = array();

        //传输策略
        $pfMsg['transport_strategy'] = array(
            'encrypt_flag' => 0,
            'compress_flag' => 0,
            'speed_limit_flag' => 0,
            'max_speed' => 0,
            'block_size' => 0,
            'network_uuid' => 0,
            'compress_method' => 0,
            'reconnect_times' => 0,
            'reconnect_interval' => 0,
            'encrypt_method' => 0,
            'strategy_group_uuid' => ""

        );



        //全局策略uuid（未使用）
        $pfMsg['strategy_group_uuid'] = "";

        //参数补全
        $pfMsg['sub_module_type'] = 1;
        $pfMsg['recovery_position'] = 0;
        $pfMsg['thread_num'] = 1;
        $pfMsg['transport_ip_segment'] = "";
        $pfMsg['recovery_type'] = 0;

        //任务所在节点
        $pfMsg['node_uuid'] = $params['node_uuid'];
        $pfMsg['storage_uuid'] = $params['storage_uuid'];
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbSRMsgs($opName, $params['node_uuid'], $msg);

        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            //立即启动数据验证任务
            if($params['time_strategy']['type'] == 1){
                $startResult = $this->startVerifyJob($params['task_name']);
            }else{
                $startResult = true;
            }
            if($startResult){
                //启动任务成功,直接返回创建任务成功
                return $this->muOpResult($result, $operate, $msg);
            }else {
                //启动任务失败,返回创建任务成功加上启动任务失败提示, 暂时没加提示
                return $this->muOpResult($result, $operate, $msg);
            }
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取选择模块的数据验证对象列表
     * @return json
     */
    public function getVerifySelectObjectList($params = [])
    {
        $moduleList = $params['module_list'];
        $editFlag = $params['edit_flag'];   //是否从修改获取
        $taskuuid= $params['task_uuid'];    //用于修改数据验证任务
        $appgroupuuid = $params['appgroup_uuid'];   //用于修改应用组
        $storageuuid = $params['storage_uuid']; //存储uuid
        $objectList = array();
        $taskTypeList = array(1, 28, 35 , 17,18, 19, 20, 56);
        $cdpCopyFlag = false;   //复制标记
        $cdpBackupFlag = false;   //实时备份标记
        if($editFlag && $taskuuid){
            //获取在任务中的对象
            $sqlObject = "select ori_uuid, backup_task_uuid from sr_surebackup_item where sr_task_uuid = ? ";
            $dataObject = $this->dbSelect($sqlObject, array($taskuuid));
            foreach ($dataObject as $d){
                $objectList[] = array(
                    'item_uuid' => $d['ori_uuid'],
                    'task_uuid' => $d['backup_task_uuid']
                );
            }
        }

        if($editFlag && $appgroupuuid){
            //获取在应用组中的对象
            $sqlObject = "select item_uuid, backup_task_uuid from sr_application_item where appgroup_uuid = ? ";
            $dataObject = $this->dbSelect($sqlObject, array($appgroupuuid));
            foreach ($dataObject as $d){
                $objectList[] = array(
                    'item_uuid' => $d['item_uuid'],
                    'task_uuid' => $d['backup_task_uuid']
                );
            }
        }


        //拆分模块类型
        $moduleUse = array();
        foreach($moduleList as $d){

            $info = explode("_", $d);
            if(count($info) == 1){
                $info = explode("-", $d);
            }
            $moduleUse[] = array(
                'module_type'=> $info[0],
                'sub_module_type' => $info[1]
            );
            if(!empty($info[2]) && $info[2] == 65){
                $cdpCopyFlag = true;
            }

            if(!empty($info[2]) && $info[2] == 32){
                $cdpBackupFlag = true;
            }
        }

        //处理整机复制筛选
        if($cdpCopyFlag == true){
            $taskTypeList = array_merge($taskTypeList, array(65));
        }

        //处理实时备份筛选
        if($cdpBackupFlag == true){
            $taskTypeList = array_merge($taskTypeList, array(32));
        }

        $taskTypeDes = implode(',', $taskTypeList);
        if (count($moduleUse) != 0){
            $moduleSql = " and (";
        }

        foreach ($moduleUse as $key=>$m){
            if($key == 0){
                $moduleSql .= "";
            }else{
                $moduleSql .= " or";
            }
            $moduleSql .= " (bt.module_type = ".$m['module_type'] ." and bt.sub_module_type = ".$m['sub_module_type']." ) ";
        }

        if (count($moduleUse) != 0){
            $moduleSql .= " ) ";
        }

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
        $sqlparams = array();
        $sqlCountparams = array();
        //获取数据
        $sql = "select
                bt.module_type, bt.sub_module_type, bt.task_type, bt.task_uuid, bt.task_name, bt.integrity_check_flag,bt.task_status, 
                vml.vm_name, vml.vm_uuid, vml.dir_path, vml.vcenter_uuid,vml.host_uuid,
                btal.agent_uuid,
                dl.db_name, dl.instance_name, dl.db_uuid,
                ol.agent_uuid as os_agent_uuid,
                nt.nas_uuid,
                mol.organization_uuid,
                kt.cluster_uuid,
                cvt.master_agent_uuid as vol_agent_uuid,
                cl.item_uuid,cl.item_name, cl.parent_uuid
                from bd_task bt
                left join vm_machine_list vml on bt.task_uuid = vml.task_uuid
                left join bd_task_agent_list btal on bt.task_uuid = btal.task_uuid
                left join db_list dl on bt.task_uuid = dl.task_uuid
                left join os_list ol on bt.task_uuid = ol.task_uuid
                left join nas_task nt on bt.task_uuid = nt.task_uuid
                left join m365_object_list mol on bt.task_uuid = mol.task_uuid
                left join kube_task kt on bt.task_uuid = kt.task_uuid
                left join cdp_vol_task cvt on bt.task_uuid = cvt.task_uuid and cvt.dev_type = 2
                left join copy_list cl on bt.task_uuid = cl.task_uuid
                left join bd_user bu on bt.user_uuid = bu.user_uuid
                where bt.task_type in (".$taskTypeDes.") ";
        if(!empty($storageuuid)){
            $sql .= " and bt.storage_uuid = ? ";
            $sqlparams = array($storageuuid);
        }

        $sql .= $moduleSql;


        $sqlcount = "select count(bt.task_uuid) as count
                from bd_task bt
                left join vm_machine_list vml on bt.task_uuid = vml.task_uuid
                left join bd_task_agent_list btal on bt.task_uuid = btal.task_uuid
                left join db_list dl on bt.task_uuid = dl.task_uuid
                left join os_list ol on bt.task_uuid = ol.task_uuid
                left join nas_task nt on bt.task_uuid = nt.task_uuid
                left join m365_object_list mol on bt.task_uuid = mol.task_uuid
                left join kube_task kt on bt.task_uuid = kt.task_uuid
                left join cdp_vol_task cvt on bt.task_uuid = cvt.task_uuid and cvt.dev_type = 2
                left join copy_list cl on bt.task_uuid = cl.task_uuid
                left join bd_user bu on bt.user_uuid = bu.user_uuid
                where bt.task_type in (".$taskTypeDes.") ";
        if(!empty($storageuuid)){
            $sqlcount .= " and bt.storage_uuid = ? ";
            $sqlCountparams = array($storageuuid);
        }
        $sqlcount .= $moduleSql;

        $user = xphp_get_user_info();
        //不是全局观察者获取对应用户的任务
        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源

            // 关联管理用户判断 当前任务 - 查看
            $authUser = $user['authUser']['current_job_look'] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuid = array_merge([$user['userUuid']], $authUser);
                $useruuid = "('" . implode("','", $useruuid) . "')";
                $sql .= ' and bu.user_uuid in ' . $useruuid;
                $sqlcount .= ' and bu.user_uuid in ' . $useruuid;
            } else {
                $sql .= ' and bu.user_uuid = ? ';
                $sqlcount .= ' and bu.user_uuid = ? ';
                $sqlparams = array_merge($sqlparams, array($user['userUuid']));
                $sqlCountparams = array_merge($sqlCountparams, array($user['userUuid']));
            }
        }

        // 创建者
        if (!empty($userName)) {
            $sql .= ' and bu.user_name like ? ';
            $sqlcount .= ' and bu.user_name like ? ';
            $sqlparams = array_merge($sqlparams, array('%' . $userName . '%'));
            $sqlCountparams = array_merge($sqlCountparams, array('%' . $userName . '%'));
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
            'total' => intval($count[0]['count']),
        );


        //根据查询信息二次获取附加信息
        $vm = array();
        $privatecloud = array();
        $publicCloud = array();
        $fs = array();
        $nas = array();
        $db = array();
        $os = array();
        $hadoop = array();
        $obs =  array();
        $organization = array();
        $k8s = array();
        $volcdp = array();
        $module = xphp_get_config('module')['MODULE_TYPE'];
        $subModule = xphp_get_config('module')['SUBMODULE_TYPE'];
        $modeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $taskTypeIndex = xphp_get_config('task')['TASKTYPE'];
        foreach ($result as $k => $each) {
            $subtype = intval($each['sub_module_type']);
            switch (intval($each['module_type'])){
                case $module['VM']:
                    $objectuuid = !empty($each['vm_uuid'])? $each['vm_uuid']:$each['item_uuid'];
                    if($subtype == 1){
                        $vm[] = $objectuuid;
                    }else  if ($subtype == 2){
                        $privatecloud[] = $objectuuid;
                    }else if ($subtype == 3){
                        $publicCloud[] = $objectuuid;
                    }

                  break;
                case $module['FS']:
                    $objectuuid = !empty($each['agent_uuid'])? $each['agent_uuid']:$each['item_uuid'];
                    if($subtype == $subModule['FS']){
                        $fs[] = $objectuuid;
                    }else if ($subtype == $subModule['HADOOP']){
                        $hadoop[] = $objectuuid;
                    }else if ($subtype == $subModule['OBS']){
                        $obs[] = $objectuuid;
                    }

                    break;
                case $module['NAS']:
                    $objectuuid = !empty($each['nas_uuid'])? $each['nas_uuid']:$each['item_uuid'];
                    $nas[] = $objectuuid;

                    break;
                case $module['DB']:
                    $objectuuid = !empty($each['db_uuid'])? $each['db_uuid']:$each['item_uuid'];
                    $db[] = $objectuuid;

                    break;
                case $module['OS']:
                    $objectuuid = !empty($each['agent_uuid'])? $each['agent_uuid']:$each['item_uuid'];
                    $os[] = $objectuuid;

                    break;
                case $module['M365']:
                    $objectuuid = !empty($each['organization_uuid'])? $each['organization_uuid']:$each['item_uuid'];
                    $organization[] = $objectuuid;

                    break;
                case $module['KUBERNETES']:
                    $objectuuid = !empty($each['cluster_uuid'])? $each['cluster_uuid']:$each['item_uuid'];
                    $k8s[] = $objectuuid;

                    break;
                case $module['VOL_CDP']:
                    $volcdp[] = $each['vol_agent_uuid'];
                    break;
            }
        }

        //获取每个模块额外信息
        $vmList = array();
        $privatecloudList = array();
        $publicCloudList = array();
        $fsList = array();
        $nasList = array();
        $dbList = array();
        $osList = array();
        $hadoopList = array();
        $obsList =  array();
        $organizationList = array();
        $k8sList = array();
        $volList = array();

        //文件
        $fsDes = implode("','", $fs);
        $sqlfs = "select agent_uuid, agent_name, ip, hostname from bd_agent where agent_uuid in ('".$fsDes."')";
        $datafs = $this->dbSelect($sqlfs);
        foreach ($datafs as $d) {
            $fsList[$d['agent_uuid']] = $this->getAgentNameStr($d['hostname'], $d['agent_name'], $d['ip']);
        }

        //操作系统
        $osDes = implode("','", $os);
        $sqlos = "select agent_uuid, agent_name, ip, hostname from bd_agent where agent_uuid in ('".$osDes."')";
        $dataos = $this->dbSelect($sqlos);
        foreach ($dataos as $d) {
            $osList[$d['agent_uuid']] = $this->getAgentNameStr($d['hostname'], $d['agent_name'], $d['ip']);
        }

        //Nas
        $nasDes = implode("','", $nas);
        $sqlnas = "select nas_name, nas_uuid, ip, nas_nickname, share_path from nas_storage_resource where nas_uuid in ('".$nasDes."')";
        $datanas = $this->dbSelect($sqlnas);
        foreach ($datanas as $d) {
            $nasList[$d['nas_uuid']] = $this->getAgentNameStr($d['nas_nickname'], $d['nas_nickname'], $d['share_path']);
        }
        //hadoop
        $hadoopDes = implode("','", $hadoop);
        $sqlhadoop = "select hadoop_cluster_uuid, hadoop_cluster_name from hadoop_cluster where hadoop_cluster_uuid in ('".$hadoopDes."')";
        $datahadoop = $this->dbSelect($sqlhadoop);
        foreach ($datahadoop as $d) {
            $hadoopList[$d['hadoop_cluster_uuid']] = $d['hadoop_cluster_name'];
        }

        //对象存储
        $obsDes = implode("','", $obs);
        $sqlobs = "select obs_uuid, obs_nickname, access_key_id, endpoint_override from obs_resource where obs_uuid in ('".$obsDes."')";
        $dataobs = $this->dbSelect($sqlobs);
        foreach ($dataobs as $d) {
            $obsList[$d['obs_uuid']] = $d['obs_nickname'] . '(' . $d['access_key_id'] . '@' . $d['endpoint_override'] . ')';
        }
        //m365组织
        $m365Des = implode("','", $organization);
        $sqlm365 = "select organization_uuid, organization_name from m365_organization where organization_uuid in ('".$m365Des."')";
        $datam365 = $this->dbSelect($sqlm365);
        foreach ($datam365 as $d) {
            $organizationList[$d['organization_uuid']] = $d['organization_name'];
        }

        //k8s容器
        $kubeDes = implode("','", $k8s);
        $sqlkube = "select cluster_uuid, cluster_name from kube_cluster where cluster_uuid in ('".$kubeDes."')";
        $datakube = $this->dbSelect($sqlkube);
        foreach ($datakube as $d) {
            $k8sList[$d['cluster_uuid']] = $d['cluster_name'];
        }

        //整机实时
        $volDes = implode("','", $volcdp);
        $sqlvol = "select agent_uuid, agent_name, ip, hostname from bd_agent where agent_uuid in ('".$volDes."')";
        $datavol = $this->dbSelect($sqlvol);
        foreach ($datavol as $d) {
            $volList[$d['agent_uuid']] = $this->getAgentNameStr($d['hostname'], $d['agent_name'], $d['ip']);
        }

        $objectTemp = array();
        //获取列表信息
        foreach ($result as $each) {
            $subtype = intval($each['sub_module_type']);
            $taskType = intval($each['task_type']);
            $taskuuid = $each['task_uuid'];
            $objectuuid = "";
            $objectname = "";
            $title = "";
            $parentuuid = "";
            $hostuuid = "";
            switch (intval($each['module_type'])){
                case $module['VM']:
                    if($subtype == 1){
                        $objectuuid = $each['vm_uuid'];
                        $objectname = $each['vm_name'];
                    }else  if ($subtype == 2){
                        $objectuuid = $each['vm_uuid'];
                        $objectname = $each['vm_name'];
                    }else if ($subtype == 3){
                        $objectuuid = $each['vm_uuid'];
                        $objectname = $each['vm_name'];
                    }
                    $title = $each['dir_path'];
                    $parentuuid = $each['vcenter_uuid'];
                    $hostuuid =  $each['host_uuid'];
                    break;
                case $module['FS']:
                    if($subtype == $subModule['FS']){
                        $objectuuid = $each['agent_uuid'];
                        $objectname = $fsList[$objectuuid];
                    }else if ($subtype == $subModule['HADOOP']){
                        $objectuuid = $each['agent_uuid'];
                        $objectname = $hadoopList[$objectuuid];
                    }else if ($subtype == $subModule['OBS']){
                        $objectuuid = $each['agent_uuid'];
                        $objectname = $obsList[$objectuuid];
                    }
                    $title = $objectname;
                    break;
                case $module['NAS']:
                    $objectuuid = $each['nas_uuid'];
                    $objectname = $nasList[$objectuuid];
                    $title = $objectname;
                    break;
                case $module['DB']:
                    $objectuuid = $each['db_uuid'];
                    $objectname = $each['db_name'];
                    $title = $objectname;

                    break;
                case $module['OS']:
                    $objectuuid = $each['agent_uuid'];
                    $objectname = $osList[$objectuuid];
                    $title = $objectname;
                    break;
                case $module['M365']:
                    $objectuuid = $each['organization_uuid'];
                    $objectname = $organizationList[$objectuuid];
                    $title = $objectname;
                    break;
                case $module['KUBERNETES']:
                    $objectuuid = $each['cluster_uuid'];
                    $objectname = $k8sList[$objectuuid];
                    $title = $objectname;
                    break;

                case $module['VOL_CDP']:
                    $objectuuid = $each['vol_agent_uuid'];
                    $objectname = $volList[$objectuuid];
                    $title = $objectname;
                    break;

            }
            if ($taskType  == $taskTypeIndex['BACKUP_COPY'] || $taskType  == $taskTypeIndex['ARCHIVE']){
                $objectuuid = $each['item_uuid'];
                $objectname = $each['item_name'];
                $parentuuid = $each['parent_uuid'];
                $title = $objectname;
            }


            if(!inArray($taskuuid.'_'.$objectuuid, $objectTemp) && !empty($objectuuid)){
                $info['rows'][] = array(
                    'object_uuid' => $objectuuid,
                    'object_name' => $objectname,
                    'parent_uuid' => $parentuuid,
                    'host_uuid' => $hostuuid,
                    'module_type' => intval($each['module_type']),
                    'sub_module_type' => intval($each['sub_module_type']),
                    'task_uuid' => $each['task_uuid'],
                    'task_name' => $each['task_name'],
                    'title' => $title,
                    'integrity_check_flag' => $each['integrity_check_flag'],
                    'task_type' => intval($each['task_type'])
                );
                $objectTemp[] = $taskuuid.'_'.$objectuuid;
            }
        }
        //附加被删除任务的时间点选择
        $list = $this->getDeleteTaskObjectInfo($params);
        $info['rows'] = array_merge($info['rows'], $list);
        $info['total'] = count($info['rows']);
        return $info;
    }

    /**
     * 获取已删除任务的所有时间点
     * @param $params
     * @return array
     */
    public function getDeleteTaskObjectInfo($params){
        $moduleList = $params['module_list'];
        $editFlag = $params['edit_flag'];   //是否从修改获取
        $taskuuid= $params['task_uuid'];    //用于修改数据验证任务
        $appgroupuuid = $params['appgroup_uuid'];   //用于修改应用组
        $storageuuid = $params['storage_uuid']; //存储uuid
        $search = $params['search'];
        $taskTypeList = array(1, 28, 35 , 17, 18, 19, 20, 56);
        $cdpCopyFlag = false;   //复制标记
        $cdpBackupFlag = false;   //实时备份标记
        //获取所有存在的任务
        $sqlTask = "select task_uuid from bd_task where storage_uuid = ? ";
        $dataTask = $this->dbSelect($sqlTask, array($storageuuid));
        $taskuuidList = array();
        foreach ($dataTask as $d) {
            $taskuuidList[] = $d['task_uuid'];
        }

        //拆分模块类型
        $moduleUse = array();
        foreach($moduleList as $d){
            $info = explode("_", $d);
            if(count($info) == 1){
                $info = explode("-", $d);
            }
            $moduleUse[] = array(
                'module_type'=> $info[0],
                'sub_module_type' => $info[1]
            );
            if(!empty($info[2]) && $info[2] == 65){
                $cdpCopyFlag = true;
            }

            if(!empty($info[2]) && $info[2] == 32){
                $cdpBackupFlag = true;
            }
        }

        //处理整机复制筛选
        if($cdpCopyFlag == true){
            $taskTypeList = array_merge($taskTypeList, array(65));
        }

        //处理实时备份筛选
        if($cdpBackupFlag == true){
            $taskTypeList = array_merge($taskTypeList, array(32));
        }

        $taskTypeDes = implode(',', $taskTypeList);
        if (count($moduleUse) != 0){
            $moduleSql = " and (";
        }

        foreach ($moduleUse as $key=>$m){
            if($key == 0){
                $moduleSql .= "";
            }else{
                $moduleSql .= " or";
            }
            $moduleSql .= " (bbt.module_type = ".$m['module_type'] ." and bbt.sub_module_type = ".$m['sub_module_type']." ) ";
        }

        if (count($moduleUse) != 0){
            $moduleSql .= " ) ";
        }

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

        $taskuuidDes = implode("','", $taskuuidList);
        $sql = "select  
                bbt.timepoint_uuid, bbt.backup_mode, bbt.timepoint, bbt.module_type, bbt.sub_module_type, bbt.task_type, bbt.task_uuid, bbt.task_name, bbt.integrity_check_flag,
                vbt.vm_name, vbt.vm_uuid, vbt.dir_path, vbt.vcenter_uuid, 
                fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                dbt.db_name, dbt.instance_name, dbt.db_uuid, dbt.agent_uuid as db_agent_uuid,
                obt.agent_uuid as os_agent_uuid, obt.agent_ip as os_agent_ip, obt.os_name,
                mbt.organization_uuid, mbt.organization_name,
                kbt.cluster_name, kbt.cluster_uuid,
                agent.master_agent_uuid as vol_agent_uuid, agent.master_agent_detail
                from bd_backup_timepoint bbt left join vm_backup_timepoint vbt on bbt.timepoint_uuid = vbt.timepoint_uuid 
                left join db_backup_timepoint dbt on bbt.timepoint_uuid = dbt.timepoint_uuid
                left join fs_backup_timepoint fbt on bbt.timepoint_uuid = fbt.fs_timepoint_uuid 
                left join os_backup_timepoint obt on bbt.timepoint_uuid = obt.timepoint_uuid
                left join m365_backup_timepoint mbt on bbt.timepoint_uuid = mbt.m365_timepoint_uuid 
                left join kube_backup_timepoint kbt on bbt.timepoint_uuid = kbt.timepoint_uuid
                left join bd_storage_resource bsr on bbt.storage_uuid = bsr.storage_uuid 
                left join cdp_vol_backup_agent agent ON bbt.timepoint_uuid = agent.timepoint_uuid and agent.dev_type = 2  and agent.storage_location in (1,3)
                left join cdp_vol_backup_vol_set vol ON agent.id = vol.backup_agent_id
                left join bd_user bu on bbt.user_uuid = bu.user_uuid
                where bbt.task_type in (".$taskTypeDes.") and bbt.task_uuid not in ('".$taskuuidDes."') and bbt.operation_status not in (1, 2) and bbt.available_flag = 1 and bbt.import_flag = 2 ";

        $sqlparams = array();
        if(!empty($storageuuid)){
            $sql .= " and bbt.storage_uuid = ? ";
            $sqlparams = array($storageuuid);
        }
        $user = xphp_get_user_info();
        //不是全局观察者获取对应用户的任务
        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            // 关联管理用户判断 当前任务 - 查看
            $authUser = $user['authUser']['current_job_look'] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuid = array_merge([$user['userUuid']], $authUser);
                $useruuid = "('" . implode("','", $useruuid) . "')";
                $sql .= ' and bu.user_uuid in ' . $useruuid;
            } else {
                $sql .= ' and bu.user_uuid = ? ';
                $sqlparams = array_merge($sqlparams, array($user['userUuid']));
            }
        }

        // 创建者
        if (!empty($userName)) {
            $sql .= ' and bu.user_name like ? ';
            $sqlparams = array_merge($sqlparams, array('%' . $userName . '%'));
        }

        $sql .= $moduleSql;
        $sql .= " order by bbt.id desc";
        $result = $this->dbSelect($sql, $sqlparams);
        $module = xphp_get_config('module')['MODULE_TYPE'];
        $subModule = xphp_get_config('module')['SUBMODULE_TYPE'];

        $sql .= $moduleSql;
        $sql .= " order by bbt.id desc";
        $result = $this->dbSelect($sql, $sqlparams);
        $module = xphp_get_config('module')['MODULE_TYPE'];
        $subModule = xphp_get_config('module')['SUBMODULE_TYPE'];

        //获取列表信息
        $list = array();
        $liIDList = array();
        foreach ($result as $each) {
            $subtype = intval($each['sub_module_type']);
            $objectuuid = "";
            $objectname = "";
            $title = "";
            $parentuuid = "";
            $hostuuid = "";

            switch (intval($each['module_type'])){
                case $module['VM']:
                    if($subtype == 1){
                        $objectuuid = $each['vm_uuid'];
                        $objectname = $each['vm_name'];
                    }else  if ($subtype == 2){
                        $objectuuid = $each['vm_uuid'];
                        $objectname = $each['vm_name'];
                    }else if ($subtype == 3){
                        $objectuuid = $each['vm_uuid'];
                        $objectname = $each['vm_name'];
                    }
                    $title = $each['dir_path'];
                    $parentuuid = $each['vcenter_uuid'];
                    break;
                case $module['FS']:
                    if($subtype == $subModule['FS']){
                        $objectuuid = $each['agent_uuid'];
                        $objectname = $this->getAgentNameStr("", $each['agent_name'], $each['agent_ip']);
                    }else if ($subtype == $subModule['HADOOP']){
                        $objectuuid = $each['agent_uuid'];
                        $objectname =$this->getAgentNameStr("", $each['agent_name'], $each['agent_ip']);
                    }else if ($subtype == $subModule['OBS']){
                        $objectuuid = $each['agent_uuid'];
                        $objectname = $this->getAgentNameStr("", $each['agent_name'], $each['agent_ip']);

                    }
                    $title = $objectname;
                    break;
                case $module['NAS']:
                    $liID = $each['task_uuid']."_".$each['agent_uuid'];
                    $objectuuid = $each['agent_uuid'];
                    $objectname = $this->getAgentNameStr("", $each['agent_ip'], $each['agent_name']);
                    $title = $objectname;
                    break;
                case $module['DB']:
                    $objectuuid = $each['db_uuid'];
                    $objectname = $each['db_name'];
                    $title = $objectname;
                    break;
                case $module['OS']:
                    $objectuuid = $each['os_agent_uuid'];
                    $objectname = $this->getAgentNameStr("", $each['os_name'], $each['os_agent_ip']);
                    $title = $objectname;
                    break;
                case $module['M365']:
                    $objectuuid = $each['organization_uuid'];
                    $objectname = $each['organization_name'];
                    $title = $objectname;
                    break;
                case $module['KUBERNETES']:
                    $objectuuid = $each['cluster_uuid'];
                    $objectname = $each['cluster_name'];
                    $title = $objectname;
                    break;
                case $module['VOL_CDP']:
                    $objectuuid = $each['vol_agent_uuid'];
                    $cdpInfo = json_decode($each['master_agent_detail'], true);
                    $objectname =$this->getAgentNameStr("", $cdpInfo['agent_name'], $cdpInfo['ip']);
                    $title = $objectname;
                    break;

            }
            if(!inArray($each['task_uuid']."_".$objectuuid, $liIDList) && !empty($objectuuid)){
                $taskName =  $each['task_name'];
                if (!in_array($each['task_uuid'], $taskuuidList)){
                    $taskName =  $each['task_name']."(".xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED').")";
                }
                $list[] = array(
                    'object_uuid' => $objectuuid,
                    'object_name' => $objectname,
                    'parent_uuid' => $parentuuid,
                    'host_uuid' => $hostuuid,
                    'module_type' => intval($each['module_type']),
                    'sub_module_type' => intval($each['sub_module_type']),
                    'task_uuid' => $each['task_uuid'],
                    'task_name' => $taskName,
                    'title' => $title,
                    'integrity_check_flag' => $each['integrity_check_flag'],
                    'task_type' => intval($each['task_type'])
                );
                $liIDList[] = $each['task_uuid']."_".$objectuuid;
            }
        }
        return $list;
    }

    /**
     * 获取选择模块的数据验证对象列表
     * @return json
     */
    public function getVerifyTaskName($params = [])
    {
        $taskName  ="";
        if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
            $taskName = xphp_get_lang('WEB_PLATFORM_DES_SURE_BACKUP').xphp_get_lang('UI_VIRTURL_LAB_JOB');
        }else{
            $taskName = xphp_get_lang('WEB_PLATFORM_DES_SURE_BACKUP')." ".xphp_get_lang('UI_VIRTURL_LAB_JOB');
        }

        $info = array(
            'task_name' => $this->getValidTaskName($taskName)
        );
        return $info;
    }

    /**
     * 获取对象最新的时间点信息
     * @param $params
     */
    public function getNewestTimepointInfo($params){
        $module = xphp_get_config('module')['MODULE_TYPE'];
        $subModule = xphp_get_config('module')['SUBMODULE_TYPE'];
        $modeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $subtype = intval($params['sub_module_type']);
        $moduleType = intval($params['module_type']);
        $objectuuid = $params['object_uuid'];
        $taskuuid = $params['task_uuid'];
        $timepointInfo = array();
        // 获取对象对应模块的最新的时间点信息
        switch ($moduleType){
            case $module['VM']:
                $timepointSql = "select vbt.timepoint_uuid, bbt.timepoint, bbt.backup_mode, bbt.integrity_check_flag from vm_backup_timepoint vbt join bd_backup_timepoint bbt
                    on vbt.timepoint_uuid = bbt.timepoint_uuid
                    where bbt.task_uuid = ? and vbt.vm_uuid = ? and bbt.operation_status not in (1, 2) and bbt.available_flag = 1 and bbt.import_flag = 2 
                     order by bbt.id desc limit 0, 1";
                $data = (array)$this->dbSelect($timepointSql, array($taskuuid, $objectuuid));
                break;
            case $module['FS']:
                $timepointSql = "select fbt.fs_timepoint_uuid as timepoint_uuid, bbt.timepoint, bbt.backup_mode, bbt.integrity_check_flag from fs_backup_timepoint fbt join bd_backup_timepoint bbt
                    on fbt.fs_timepoint_uuid = bbt.timepoint_uuid 
                    where bbt.task_uuid = ? and fbt.agent_uuid = ? and bbt.operation_status not in (1, 2) and bbt.available_flag = 1 and bbt.import_flag = 2 
                     order by bbt.id desc limit 0, 1";
                $data = (array)$this->dbSelect($timepointSql, array($taskuuid, $objectuuid));

                break;
            case $module['NAS']:
                $timepointSql = "select fbt.fs_timepoint_uuid as timepoint_uuid, bbt.timepoint, bbt.backup_mode, bbt.integrity_check_flag from fs_backup_timepoint fbt join bd_backup_timepoint bbt
                    on fbt.fs_timepoint_uuid = bbt.timepoint_uuid
                    where bbt.task_uuid = ? and fbt.agent_uuid = ? and bbt.operation_status not in (1, 2) and bbt.available_flag = 1 and bbt.import_flag = 2 
                   order by bbt.id desc limit 0, 1";
                $data = (array)$this->dbSelect($timepointSql, array($taskuuid, $objectuuid));
                break;
            case $module['DB']:

                $timepointSql = "select dbt.timepoint_uuid, bbt.timepoint, bbt.backup_mode, bbt.integrity_check_flag from db_backup_timepoint dbt join bd_backup_timepoint bbt
                    on dbt.timepoint_uuid = bbt.timepoint_uuid
                    where bbt.task_uuid = ? and dbt.db_uuid = ? and bbt.operation_status not in (1, 2) and bbt.available_flag = 1 and bbt.import_flag = 2 
                    order by bbt.id desc limit 0, 1";
                $data = (array)$this->dbSelect($timepointSql, array($taskuuid, $objectuuid));
                break;
            case $module['OS']:
                $timepointSql = "select obt.timepoint_uuid, bbt.timepoint, bbt.backup_mode, bbt.integrity_check_flag from os_backup_timepoint obt join bd_backup_timepoint bbt
                    on obt.timepoint_uuid = bbt.timepoint_uuid
                    where bbt.task_uuid = ? and obt.agent_uuid = ? and bbt.operation_status not in (1, 2) and bbt.available_flag = 1 and bbt.import_flag = 2 
                   order by bbt.id desc limit 0, 1";
                $data = (array)$this->dbSelect($timepointSql, array($taskuuid, $objectuuid));
                break;
            case $module['M365']:

                $timepointSql = "select mbt.m365_timepoint_uuid, bbt.timepoint, bbt.backup_mode, bbt.integrity_check_flag from m365_backup_timepoint mbt join bd_backup_timepoint bbt
                    on mbt.m365_timepoint_uuid = bbt.timepoint_uuid
                    where bbt.task_uuid = ? and mbt.organization_uuid = ? and bbt.operation_status not in (1, 2) and bbt.available_flag = 1 and bbt.import_flag = 2 
                    order by bbt.id desc limit 0, 1";
                $data = (array)$this->dbSelect($timepointSql, array($taskuuid, $objectuuid));
                break;
            case $module['KUBERNETES']:
                $timepointSql = "select kbt.timepoint_uuid, bbt.timepoint, bbt.backup_mode, bbt.integrity_check_flag from kube_backup_timepoint kbt join bd_backup_timepoint bbt
                    on kbt.timepoint_uuid = bbt.timepoint_uuid
                    where bbt.task_uuid = ? and kbt.cluster_uuid = ? and bbt.operation_status not in (1, 2) and bbt.available_flag = 1 and bbt.import_flag = 2 
                    order by bbt.id desc limit 0, 1";
                $data = (array)$this->dbSelect($timepointSql, array($taskuuid, $objectuuid));
                break;
            case $module['VOL_CDP']:

                $timepointSql = "select vol.end_timestamp, bbt.timepoint_uuid, agent.master_agent_detail, bbt.integrity_check_flag 
                                from cdp_vol_backup_vol_set vol,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                                where bbt.timepoint_uuid = agent.timepoint_uuid and agent.id = vol.backup_agent_id and bbt.available_flag = 1 and agent.storage_location in (1,3) and bbt.task_uuid = ?  and agent.master_agent_uuid = ? order by vol.id limit 0, 1";
                $data = (array)$this->dbSelect($timepointSql, array($taskuuid, $objectuuid));
                break;
        }
        if(!empty($data)){
            $timepointInfo = array(
                'timepoint_uuid' => $data[0]['timepoint_uuid'],
                'timepoint' => !empty($data[0]['timepoint'])? $data[0]['timepoint']:"",
                'backup_mode' => intval($data[0]['backup_mode']),
                'backup_mode_des' => $modeDes[$data[0]['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT'),
                'integrity_check_flag' => v1_parse_flag_to_bool(intval($data[0]['integrity_check_flag']))

            );
            if($moduleType == $module['VOL_CDP']){
                $detail = json_decode($data[0]['master_agent_detail'], true);
                $allDiskList = $detail['all_disk_list'];
                $excludeDiskList = $detail['exclude_device_list'];
                $excludeList = array();
                foreach ($excludeDiskList as $disk) {
                    $excludeList[] = $disk['dev_uuid'];
                }
                $systemFlag = false;
                foreach ($allDiskList as $disk) {
                    //判断是否备份了系统盘
                    if(!in_array($disk['dev_uuid'], $excludeList) && $disk['is_system_disk_flag'] == 1){
                        $systemFlag = true;
                    }
                }
                $timepointInfo = array(
                    'timepoint_uuid' => $data[0]['timepoint_uuid'],
                    'timepoint' => !empty($data[0]['end_timestamp'])? $data[0]['end_timestamp']:"",
                    'backup_mode' => intval($data[0]['backup_mode']),
                    'backup_mode_des' => $modeDes[$data[0]['backup_mode']] . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT'),
                    'new_timepoint_uuid' => $data[0]['timepoint_uuid'],
                    'is_system_disk_flag' => $systemFlag,
                    'integrity_check_flag' => v1_parse_flag_to_bool(intval($data[0]['integrity_check_flag']))
                );
            }
        }
        return $timepointInfo;

    }

    /**
     * 1、代理没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、代理已删除，显示时间点中的主机名+ip
     * @return string name(ip)
     */
    protected function getAgentNameStr($host_name, $agent_name, $ip)
    {
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
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    protected function getValidTaskName($taskName){
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
     * 获取演练室虚拟化类型
     * @param $labuuid
     */
    protected function getLabHypervisor($labuuid){
        if(empty($labuuid)){
            //直接返回内嵌虚拟化
            return xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_EMD'];
        }
        $sql = "select hypervisor_type from sr_virtual_lab where virtual_lab_uuid = ?";
        $data = $this->dbSelect($sql, array($labuuid));
        return !empty($data[0]['hypervisor_type'])?intval($data[0]['hypervisor_type']):xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_EMD'];
    }

    /**
     * 立即启动数据验证任务
     * @param unknown $taskName
     * @return boolean|mixed
     */
    protected function startVerifyJob($taskName){
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type, ssb.hypervisor_type
                from bd_task bt, sr_surebackup ssb where bt.task_uuid = ssb.sr_task_uuid and bt.task_name = ?  and bt.module_type = ? and bt.task_type = ?
                order by bt.id desc";

        $data = $this->dbSelect($sql, array($taskName, xphp_get_config('module')['MODULE_TYPE']['SUREBACKUP'], xphp_get_config('task')['TASKTYPE']['SURE_BACKUP']));
        if(!$data) return false;
        //模块类型
        //验证模式
        //调用系统统一启动任务接口.不重新写
       VerificationJobController::instance()->startJob($data[0]['task_uuid'], 1);

    }

    //检查授权数量
    public function checkAuthNum($params){
        //获取授权
        $auth = Auth::instance()->getLicenceInfo(array('type'=>'a','module' => 'verify'));
        $AUTH_SUCCESS = false;
        //得到剩余数量
        if ($auth['total'] == -1){
            $AUTH_SUCCESS = true;
            return $AUTH_SUCCESS;
        }
        $remaining_quantity = intval($auth["total"]) - intval($auth["used"]) - count($params['item_list']);
        if(!($remaining_quantity < 0 )){
            $AUTH_SUCCESS = true;
        }

        return $AUTH_SUCCESS;
    }



}
