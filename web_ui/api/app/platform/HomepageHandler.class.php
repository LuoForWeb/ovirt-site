<?php
use phpseclib3\Crypt\DH\Parameters;

/*******************************************
 ** 平台统一数据处理
 **
 ** @author       luokai@vinchin.com
 ** @date         2022-8-25 上午09:35:40
 ** @version      1.0.0
 ** @copyright    Copyright 2022 vinchin.com
 ********************************************/
class HomepageHandler extends OPHandler{
    /**
     * 数据概览
     * @param unknown $params
     */
    public function getDataCenterView($params){
        //当前任务个数
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu where  bt.delete_flag = ? and bt.user_uuid = bu.user_uuid and bu.user_uuid = ?";
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET'],Xphp::$_user['useruuid']);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        // 历史任务个数
        $sqlCount = "select count(distinct id) as total from bd_history_task where (user_uuid = ? or (user_uuid = '' and user_name = ?))";
        $sqlCountParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username']);
        $hisCount = $this->dbSelect($sqlCount, $sqlCountParams);

        $systemHandler = Xphp::instance('SystemHandler');
        $systemRunningTimeInfo = array(
            "runningTime" => date("Y-m-d H:i:s", $systemHandler->getSystemTime()),
            "runningDay" => $this->getRunningDays(),
        );
        $info = array(
            "systemRunningTimeInfo" => $systemRunningTimeInfo,
            "accumulateData" => $this-> getAccumulatedData(),
            "currentTaskNum" => $count[0]['total'],
            "historyTaskNum" => $hisCount[0]['total'],
        );
        return json_encode($info);
    }
    /**
     * 得到系统运行天数
     */
    private function getRunningDays(){
        $sql = "select system_run_time from bd_system";
        $data = $this->dbSelect($sql);
        $value = round($data[0]['system_run_time'], 1);
        $date = intval($value/24);
        $dateDes = $date.Xphp::$_lang['WEB_UTILS_DAY'];
        if($date >= 365){
            $year = intval($date/365);
            $days = $date % 365;
            $dateDes = $year .Xphp::$_lang['WEB_UTILS_YEAR']. $days . Xphp::$_lang['WEB_UTILS_DAY'];
        }
        return $dateDes;
    }
    /**
     * 得到累计备份数据
     */
    private function getAccumulatedData(){
        $sql = "select vmware_data, xs_data, xen_data, kvm_data, hyperv_data, fs_data, db_data, os_data, copy_data, 
       archive_data, vol_cdp_data, nas_data,m365_data, obs_data, hadoop_data, kube_data from bd_user_extension ";
        $sqlParams = array();
        //如果是admin系统超级管理员或全局观察者
        if(in_array("global_observer", $_SESSION['permission']) || (Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($_SESSION['tenantuuid']))){

        }else{
            //操作员只显示自己的数据
            $sql .= "where user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $total = 0;
        foreach ($data as $d){
            $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] +
                $d['kvm_data'] + $d['hyperv_data'] + $d['fs_data'] +
                $d['db_data'] + $d['os_data'] + $d['copy_data'] +
                $d['archive_data'] + $d['vol_cdp_data'] + $d['nas_data'] +
                $d['m365_data'] + $d['obs_data'] + $d['hadoop_data'] + $d['kube_data'];
        }

        $utils = Xphp::instance('Utils');
        $info = $utils->calSizeToValueAndUnit($total, true);
        return $info;
    }





    /**
     * 任务概览
     */
    public function getTaskStatusView(){
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu where  
        bt.delete_flag = ? and bt.user_uuid = bu.user_uuid and bu.user_uuid = ? and bt.task_status = ?";
        // 运行中
        $sqlCountRun = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_user['useruuid'], Xphp::$_config['TASKSTATUS']['RUNNING']);
        $countRun = $this->dbSelect($sqlCount, $sqlCountRun);
        // 等待中
        $sqlCountWait = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_user['useruuid'], Xphp::$_config['TASKSTATUS']['WAITTING']);
        $countWait = $this->dbSelect($sqlCount, $sqlCountWait);
        //停止
        $sqlCountStop = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_user['useruuid'], Xphp::$_config['TASKSTATUS']['STOPPED']);
        $countStop = $this->dbSelect($sqlCount, $sqlCountStop);
        //---------------------------------------------------------------------------------------------------------------------------
        // 成功 异常  失败 从bd_task_alarm中取
        $sqlAlarm = "select count(task_alarm_id) as total from bd_task_alarm where user_uuid = ? and alarm_level = ? and solved_flag = ?";
        //成功从bd_history_task中取
        $sqlhis = "select count(id) as total from bd_history_task where user_uuid = ? and error_code = ?";
        //成功(界面暂时去掉了显示)
        $sqlCountSuccess = array(Xphp::$_user['useruuid'], 0);
        $countSuccess = $this->dbSelect($sqlhis, $sqlCountSuccess);
        //异常  level 2
        $countAbnormal = $this->dbSelect($sqlAlarm, array(Xphp::$_user['useruuid'], 2,2));
        //失败  level 3
        $countFail = $this->dbSelect($sqlAlarm, array(Xphp::$_user['useruuid'], 3,2));
        $info = array(
            "runningNum" => $countRun[0]['total'],  //运行中任务个数
            "waitingNum" => $countWait[0]['total'],  //等待中任务个数
            "stopNum" => $countStop[0]['total'],  //停止任务个数
            "abnormalNum" => $countAbnormal[0]['total'],  //异常任务个数
            "failNum" => $countFail[0]['total'],  //失败任务个数
        );
        return json_encode($info);
    }
    /**
     * 模块授权
     */
    public function getModuleAuth(){
        $utils = Xphp::instance('Utils');
        $sql = "select extension from bd_license ";
        $data = $this->dbSelect($sql);
        $info = array();
        $extension = array();
        if (empty($data)) {
            $extension['p'] = [];
        } else {
            if(json_decode($utils->decrypt($data[0]['extension']), true) == null){
                $extension['p'] = [];
            }else{
                $extension = json_decode($utils->decrypt($data[0]['extension']), true);
            }
        }
        in_array('db_protect', $extension['p'])? $info['dbinfo']['showFlag'] = true: $info['dbinfo']['showFlag'] =false;
        in_array('fileprotect', $extension['p'])? $info['fileinfo']['showFlag'] = true: $info['fileinfo']['showFlag'] =false;
        in_array('vmprotect', $extension['p'])? $info['vminfo']['showFlag'] = true: $info['vminfo']['showFlag'] =false;
        in_array('cloud_platform', $extension['p'])? $info['cloudInfo']['showFlag'] = true: $info['cloudInfo']['showFlag'] =false;
        in_array('data_verification', $extension['p'])? $info['CDMInfo']['showFlag'] = true: $info['CDMInfo']['showFlag'] =false;
        in_array('os_protect', $extension['p'])? $info['osinfo']['showFlag'] = true: $info['osinfo']['showFlag'] =false;
        //屏蔽主机模块下的卷实时
        $info['cdpInfo']['showFlag'] =false;
        in_array('vol_cdp_protect', $extension['p'])? $info['volcdpInfo']['showFlag'] = true: $info['volcdpInfo']['showFlag'] =false;
        in_array('nas_protect', $extension['p'])? $info['nasInfo']['showFlag'] = true: $info['nasInfo']['showFlag'] =false;
        //永思数据库实时
        in_array('dbprotect', $extension['p'])? $info['dbcdpInfo']['showFlag'] = true: $info['dbcdpInfo']['showFlag'] =false;
        //公有云
        in_array('awsprotect', $extension['p']) ? $info['awsInfo']['showFlag'] = true : $info['awsInfo']['showFlag'] = false;
        //m365
        in_array('office365_protect', $extension['p']) ? $info['m365Info']['showFlag'] = true : $info['m365Info']['showFlag'] = false;
        //obs
        in_array('obs_protect', $extension['p']) ? $info['obsInfo']['showFlag'] = true : $info['obsInfo']['showFlag'] = false;
        //hadoop
        in_array('hadoop_protect', $extension['p']) ? $info['hadoopInfo']['showFlag'] = true : $info['hadoopInfo']['showFlag'] = false;
        //k8s
        in_array('k8s_protect', $extension['p']) ? $info['k8sInfo']['showFlag'] = true : $info['k8sInfo']['showFlag'] = false;
        //filecopy
        in_array('file_copy_protect', $extension['p']) ? $info['filecopyInfo']['showFlag'] = true : $info['filecopyInfo']['showFlag'] = false;
        //dbcopy
        in_array('dbcdpcopy', $extension['p']) ? $info['dbcopyInfo']['showFlag'] = true : $info['dbcopyInfo']['showFlag'] = false;
        return json_encode($info);
    }

    /**
     * 获取卷CDP模块保护数据
     */
    public function getVolcdpProtectData(){
        $utils = Xphp::instance('Utils');
        $sql = "SELECT SUM(cvbvs.backup_file_size + cvbvs.log_file_total_size) total 
                FROM cdp_vol_backup_vol_set cvbvs,cdp_vol_backup_agent cvba,bd_backup_timepoint bbt  
                WHERE cvbvs.backup_agent_id = cvba.id and cvba.timepoint_uuid = bbt.timepoint_uuid and bbt.user_uuid = ? and cvba.storage_location !=0";
        $data = $this->dbSelect($sql,array(Xphp::$_user['useruuid']));
        $total = $data[0]['total'];
        return $this->pGetIntToSizeInfo(intval($total), $utils);
    }

    /**
     * 数据保护
     * @param unknown $params
     */
    public function getProtectData($params){
        //TODO
        $utils = Xphp::instance('Utils');
        $info = array(
            'vmInfo' => $this->getProtectVMInfo($utils),
            'hostInfo' => $this->getProtectHostInfo($utils),
            'nasInfo' => $this->getProtectNasInfo($utils),
            'm365Info' => $this->getProtectM365Info($utils),
            'awsInfo' => $this->getProtectAwsInfo($utils),
            'obsInfo' => $this->getProtectObsInfo($utils),
            'hadoopInfo' => $this->getProtectHadoopInfo($utils),
            'k8sInfo' => $this->getProtectK8sInfo($utils),
            'filecopyInfo' => $this->getProtectedFilecopyInfo($utils),
            'dbcopyInfo' => $this->getProtectDbcopyInfo($utils),
        );

        return json_encode($info);
    }
    /**
     * 存储统计
     * @param unknown $params
     */
    public function getSystemStorageData(){
        //TODO
        $utils = Xphp::instance('Utils');
        $info = array(
            // 'backupInfo' => $this->getBackupStorageInfo($utils),
            'allStorageInfo' => $this->getAlltorageInfo($utils),
        );
        return json_encode($info);
    }

    /**
     * 获取备份 + 副本 + 归档存储总量/已用/剩余
     * @param array $utils 副本+归档
     */
    private function getAlltorageInfo($utils){
        $useModeStr = '('. Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'] . ',' . Xphp::$_config['BD_STORAGE_USE_MODE']['COPY'] . ',' . Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'] .')';
        $sql = "select sum(total_size) as total_size, sum(free_size) as free_size, count(storage_uuid) as total_num from  bd_storage_resource where use_mode in " . $useModeStr ." ";
        //userLevel是1 2 3就全部显示
        if (!in_array($_SESSION['userLevel'],[1,2,3])) {
            $resourceHandler =  Xphp::instance('ResourceHandler');
            $uuid = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                $nullSize = array(
                    'size' => 0,
                    'value' => 0,
                    'unit' => 'B',
                    'des' => '0B'
                );
                $info = array(
                    'storageNum' => 0,
                    'usedCapacity' => $nullSize,
                    'remainingCapacity' => $nullSize, 
                    'copyChartData' => $this->getStorageChartData(false),
                );
                return $info;
            } else {
                // $uuidArr 是一个一维数组
                $storageUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " and storage_uuid in ($storageUuidsIn)";
            }
        }
        $data = $this->dbSelect($sql);
        $total = intval($data[0]['total_size']);
        $free = intval($data[0]['free_size']);
        $used = $total - $free;
        $info = array(
            'storageNum' => intval($data[0]['total_num']),
            'totalCapacity' => $this->pGetIntToSizeInfo($total, $utils),
            'usedCapacity' => $this->pGetIntToSizeInfo($used, $utils),
            'remainingCapacity' => $this->pGetIntToSizeInfo($free, $utils),
            'copyChartData' => $this->getStorageChartData(),
        );
        return $info;
    }

    /**
     * 系统监控
     * @param unknown $params
     * @param unknown $node_uuid
     * @return array|number[]|unknown[]|unknown[][][]|unknown[][][][]|fetchAll()[]|NULL[]|fetchAll()[][][]
     */
    public function getNetworkChartData($params){
        $count = $params['count'];      //本次取的条数
        $node_uuid = $params['node_uuid'];
        $this->paramsCheck($count);
        $sql = "select node_uuid, unix_timestamp(monitor_time) as monitor_times, network_in, network_out
                from bd_system_monitor where node_uuid = ? 
                and monitor_time  < now() order by monitor_time desc limit 0,?";
        $result = $this->dbSelect($sql,array($node_uuid,$count));
        $utils = Xphp::instance('Utils');
        $info = array();
        foreach ($result as $each){
            $network_receive = $utils->calSizeToValueAndUnit($each['network_in'],true);
            $network_transmit = $utils->calSizeToValueAndUnit($each['network_out'],true);
            $info[] = array(
                'network' => array(
                    'receive' => $each['network_in'],
                    'transmit' => $each['network_out'],
                    'receive_des' => $network_receive['value'] . $network_receive['unit']."/s",
                    'receive_des_unit' => $network_receive['unit']."/s",
                    'transmit_des' => $network_transmit['value'] .$network_transmit['unit']."/s",
                    'transmit_des_unit' => $network_transmit['unit']."/s",
                ),
                'time'=> date("H:i:s", $each['monitor_times']),
            );
        };
        return json_encode($info);
    }
    /**
     * 得到主节点的uuid
     */
    public function getHostNode(){
        $sql = "select node_uuid from bd_node where node_type = 1";
        $result = $this->dbSelect($sql);
        return $result[0]['node_uuid'];
    }

    /**
     * 得到cpu、内存使用率、bps、iops读写速度
     */
    public function getSystemMonitorData($params){
        //获取主节点
        $node_uuid = $params['node_uuid'];
        $node_uuid = empty($node_uuid) ?  $this->getHostNode() : $node_uuid;
        $utils = Xphp::instance('Utils');
        $sql = "select node_uuid, monitor_time, cpu_percentage, ram_percentage, iops_read, iops_write, bps_read, bps_write
        from bd_system_monitor";
        $date_start= date ("Y-m-d H:i:s",strtotime ( "-10 minute" ));;
        $date_end= date("Y-m-d H:i:s");
        $sql .= " where monitor_time between ? and ? and node_uuid = ? order by monitor_time desc limit 0,1";
        $result = $this->dbSelect($sql,array($date_start, $date_end, $node_uuid));
        $iopsReadSpeed = $utils->calSizeToValueAndUnit($result[0]["iops_read"],true);
        $iopsWriteSpeed = $utils->calSizeToValueAndUnit($result[0]["iops_write"],true);
        $info = array(
            "cpuUsedRate" => $result[0]["cpu_percentage"],
            "memoryUsedRate" => $result[0]["ram_percentage"],
            "bpsReadSpeed" => $result[0]["bps_read"],
            "bpsWriteSpeed" => $result[0]["bps_write"],
            "iopsReadSpeed" => $iopsReadSpeed["value"],
            "iopsWriteSpeed" => $iopsWriteSpeed["value"],
            "iopsReadUnit" => Xphp::$_lang['UI_HOMEPAGE_COUNT_SECOND'],
            "iopsWriteUnit" => Xphp::$_lang['UI_HOMEPAGE_COUNT_SECOND'],
        );
        return json_encode($info);
    }

    /**
     * 虚拟机及CDM信息
     * @return HomepageHandler[]|number[]|unknown[]|NULL[]
     */
    private function getProtectVMInfo($utils){
        $vcenterInfo = $this->pGetVcenterInfo(true);
        $info = array(
            'totalNum' => $this->getVmNums(),
            'protectedNum' => $this->getProtectVmNums(Xphp::$_config['VM_SUB_MODULE']['VM']),
            'backupData' => $this->pGetProtectData(Xphp::$_config['MODULE_TYPE']['VM'], $utils, Xphp::$_config['VM_SUB_MODULE']['VM']),
            'onlineNum' => $vcenterInfo['online'],
            'offlineNum' => $vcenterInfo['offline'],
            'cloudInfo' => $this->getVmCloudInfo(),
            'CDMInfo' => $this->getCdmInfo(),
        );
        return $info;
    }

    /**
     * 主机信息
     * @return string[]
     */
    private function getProtectHostInfo($utils){
        //主机信息
        $hostInfo = $this->getHostInfo();
        $info = array(
            'hostTotalNum' => $hostInfo['total'],
            'hostOnlineNum' => $hostInfo['online'],
            'hostOfflineNum' => $hostInfo['offline'],
            'fileInfo' => $this->getFileInfo($utils),
            'DBInfo' => $this->getDBInfo($utils),
            'OSInfo' => $this->getOSInfo($utils),
            'CDPInfo' => $this->getCDPInfo($utils),
            'DBCDPInfo' => $this->getDBCDPInfo()
        );
        return $info;
    }


    /**
     * 获取NAS信息
     * @return string[]
     */
    private function getProtectNasInfo($utils){
        $module = Xphp::$_config['MODULE_TYPE']['NAS'];
        $submodule = Xphp::$_config['SUBMODULE_TYPE']['NAS'];
        //NAS设备信息
        $nasInfo = $this->getNasInfo();
        $info = array(
            'protectedNum' => $this->getProtectNas(),
            'backupDataSize' =>  $this->pGetProtectData(Xphp::$_config['MODULE_TYPE']['NAS'], $utils,$submodule),
            'taskNum' => $this->pGetTaskNum($module,$submodule),
            'backupPointNum' => $this->pGetBackuppointNum($module,$submodule),
            'onlineNum' => $nasInfo['online'],
            'offlineNum' => $nasInfo['offline'],
        );
        return $info;
    }


    /**
     * 虚拟机总数
     * @return number
     */
    private function getVmNums(){
        $sql = 'select count(vt.tree_id) as total from vm_tree vt, vm_vcenter vv where vv.hypervisor_type not in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ')'.'
        and vt.display_mode = ? and vt.type = ? and vt.vcenter_uuid = vv.vcenter_uuid and vv.user_uuid  =?';
        $data = $this->dbSelect($sql, array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM'],Xphp::$_user['useruuid']));
        return intval($data[0]['total']);
    }

    /**
     * 受保护虚拟机/公有云/私有云数量
     * @param unknown $sub_module_type 虚拟化的子模块类型
     * @return number
     */
    private function getProtectVmNums($sub_module_type = ''){
        $sql = "select count(vml.machine_id) as protect_vms from vm_machine_list vml, vm_vcenter vv, bd_task bt, vm_machine vm
                        where vml.vcenter_uuid = vm.vcenter_uuid and vml.vm_uuid = vm.vm_uuid and bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = vv.vcenter_uuid and bt.task_type = ? and vv.user_uuid  = ? and bt.module_type = ? ";
        $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP'],Xphp::$_user['useruuid'],Xphp::$_config['MODULE_TYPE']['VM']);
        if($sub_module_type == Xphp::$_config['VM_SUB_MODULE']['VM']){
            $sql .= " and bt.sub_module_type in (?,?) ";
            $sqlParams = array_merge($sqlParams,array(Xphp::$_config['VM_SUB_MODULE']['VM'], Xphp::$_config['VM_SUB_MODULE']['PRIVATE_CLOUD']));
        }else{
            $sql .= " and bt.sub_module_type = ? ";
             $sqlParams = array_merge($sqlParams,array($sub_module_type));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return intval($data[0]['protect_vms']);
    }

    /**
     * 获取云平台信息
     * @return unknown[]|number[]
     */
    private function getVmCloudInfo(){
        //获取云平台在线/离线
        $data = $this->pGetVcenterInfo(false);
        $info = array(
            'onlineNum' => $data['online'],
            'offlineNum' => $data['offline']
        );
        return $info;
    }

    /**
     * 获取cdm数据验证信息
     * @return string[]
     */
    private function getCdmInfo(){
        $info = array(
            'laboratoryNum' => $this->getLaboratoryNum(),
            'verifyNum' => $this->getVerifyNum(),
            'verifyingNum' => $this->getVerifyNum(true),
            'verifyTimes' => $this->getVerifyTime(),
            'falieTimes' => $this->getVerifyTime(true),
        );

        return $info;
    }

    /**
     * 虚拟机验证数量
     * @return number
     */
    private function getLaboratoryNum(){
        // proxy_status 7 已部署  2 使用中
        $sql = "select count(svl.virtual_lab_uuid) as total from sr_virtual_lab svl,vm_vcenter vv where svl.vcenter_uuid = vv.vcenter_uuid and vv.user_uuid = ? and (proxy_status = 7 or proxy_status = 2)";
        $data = $this->dbSelect($sql,array(Xphp::$_user['useruuid']));
        return intval($data[0]['total']);
    }

    /**
     * 验证虚拟机数量
     * @param boolean $runFlag  验证中标志
     * @return number
     */
    private function getVerifyNum($runFlag = false){
        //验证数量
        $sql = "select count(ssbiv.vm_id) as total from sr_sure_backup_instant_vm ssbiv, bd_task bt where ssbiv.task_uuid = bt.task_uuid and bt.user_uuid = ?";
        $sqlParams = array(Xphp::$_user['useruuid']);
        if($runFlag){
            //验证中
            $sql .= " and bt.task_status = ?";
            $sqlParams = array_merge($sqlParams,array(Xphp::$_config['TASKSTATUS']['RUNNING']));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return intval($data[0]['total']);
    }

    /**
     * 验证数量
     * @param intval $errorFlag
     * @return number
     */
    private function getVerifyTime($errorFlag = false){
        $sql = "select count(id) as total from bd_history_task where task_type = ? and user_uuid = ?";
        $sqlParams = array(Xphp::$_config['TASKTYPE']['SURE_BACKUP'],Xphp::$_user['useruuid']);
        if($errorFlag){
            $sql .= " and error_code = 46";
        }
        $data = $this->dbSelect($sql,$sqlParams);
        return intval($data[0]['total']);
    }

    /**
     * 获取NAS设备信息
     * @return []
     */
    private function getNasInfo(){
        $sql = "select nas_status from nas_storage_resource";
        if (!in_array($_SESSION['userLevel'],[1,2,3])) {//userLevel是1 2 3就全部显示
            $resourceHandler = Xphp::instance('ResourceHandler');
            $uuid = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], 57);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                $info = array(
                    'online' => 0,
                    'offline' => 0,
                    'total' => 0
                );
                return $info;
            } else {
                // $uuidArr 是一个一维数组
                $nasUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " where nas_uuid in ($nasUuidsIn)";
            }
        }
        $data = $this->dbSelect($sql);
        $online = 0;
        $offline = 0;
        $total = count($data);
        foreach ($data as $d){
            if(intval($d['nas_status']) == Xphp::$_config['FLAG']['SET']){
                $online++;
            }else{
                $offline++;
            }
        }

        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $total
        );
        return $info;
    }

    /**
     * 获取受保护的NAS设备
     * @return number
     */
    private function getProtectNas(){
        $sql = "select count(distinct nt.nas_uuid) as total from bd_task bt, nas_task nt where bt.task_uuid = nt.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.sub_module_type = ? and bt.task_type = ? and user_uuid = ?";
        $data = $this->dbSelect($sql, array(
                Xphp::$_config['FLAG']['UNSET'],
                Xphp::$_config['MODULE_TYPE']['NAS'],
                Xphp::$_config['SUBMODULE_TYPE']['NAS'],
                Xphp::$_config['TASKTYPE']['BACKUP'],
                Xphp::$_user['useruuid']
            )
        );
        return intval($data[0]['total']);
    }

    /**
     * 获取主机信息
     * @return []
     */
    private function getHostInfo(){
        $sql = "select online_flag from bd_agent where agent_type not in (3, 4, 5) and user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $online = 0;
        $offline = 0;
        $total = count($data);
        foreach ($data as $d){
            if(intval($d['online_flag']) == Xphp::$_config['FLAG']['SET']){
                $online++;
            }else{
                $offline++;
            }
        }

        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $total
        );
        return $info;
    }


    private function getFileInfo($utils){
        $module = Xphp::$_config['MODULE_TYPE']['FS'];
        $submodule = Xphp::$_config['SUBMODULE_TYPE']['FS'];
        $taskType = Xphp::$_config['TASKTYPE']['BACKUP'];
        $info = array(
            'protectedNum' => $this->pGetProtectHost($module,$taskType,$submodule),
            'backupDataSize' => $this->pGetProtectData($module,$utils,$submodule),
            'taskNum' => $this->pGetTaskNum($module,$submodule),
            'backupPointNum' => $this->pGetBackuppointNum($module,$submodule)
        );
        return $info;
    }

    private function getDBInfo($utils){
        $module = Xphp::$_config['MODULE_TYPE']['DB'];
        $taskType = Xphp::$_config['TASKTYPE']['DB_BACKUP'];
        $info = array(
            'protectedNum' => $this->pGetProtectHost($module,$taskType),
            'backupDataSize' => $this->pGetProtectData($module,$utils),
            'taskNum' => $this->pGetTaskNum($module),
            'backupPointNum' => $this->pGetBackuppointNum($module)
        );
        return $info;
    }

    private function getOSInfo($utils){
        $module = Xphp::$_config['MODULE_TYPE']['OS'];
        $taskType = Xphp::$_config['TASKTYPE']['OS_BACKUP'];
        $info = array(
            'protectedNum' => $this->pGetProtectHost($module,$taskType),
            'backupDataSize' => $this->pGetProtectData($module,$utils),
            'taskNum' => $this->pGetTaskNum($module),
            'backupPointNum' => $this->pGetBackuppointNum($module)
        );
        return $info;
    }
    /**
     * 卷CDP模块备份概要信息
     * @return number[]|NULL[]
     */
    private function getCDPInfo(){
        $module = Xphp::$_config['MODULE_TYPE']['VOL_CDP'];
        $info = array(
            //受保护主机数
            'protectedNum' => $this->getVolcdpProtectHost($module),
            //当前保护数据大小
            'backupDataSize' => $this->getVolcdpProtectData(),
            //任务数
            'taskNum' => $this->getVolcdpTaskNum($module),
            //备份集个数
            'backupPointNum' => $this->getVolcdpBackupSetNum($module),
            //运行中任务数
            // 'taskRunNum' => $this->pGetTaskRunNum($module),
        );
        return $info;
    }

    /**
     * 数据库实时容灾概要信息
     * @return number[]|unknown[][]
     */
    private function getDBCDPInfo(){
        $module = Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'];
        $info = array(
            //生产主机
            'productHost' => $this->getDBCDPHost(Xphp::$_config['DB_CDP_HOST_TYPE']['product']),
            //备份主机
            'standbyHost' => $this->getDBCDPHost(Xphp::$_config['DB_CDP_HOST_TYPE']['standby']),
            //受保护主机数
            'protectedNum' => $this->getDBCDPProtectHost(),
            //任务数
            'taskNum' => $this->pGetTaskNum($module),
            //任务运行数
            'taskRunNum' => $this->pGetTaskRunNum($module),
        );
        return $info;
    }


    /**
     * 备份存储信息
     * @return string[]
     */
    private function getBackupStorageInfo($utils){
        $useMode = Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'];
        $storageInfo = $this->pGetStorageInfo($useMode);
        $info = array(
            'bakStorageNum' => $storageInfo['total_num'],
            'bakStorageCapacity' => $this->pGetIntToSizeInfo($storageInfo['total'], $utils),
            'usedCapacity' => $this->pGetIntToSizeInfo($storageInfo['used'], $utils),
            'remainingCapacity' => $this->pGetIntToSizeInfo($storageInfo['free'], $utils)
        );
        return $info;
    }

    /**
     * 获取存储近七天数据图标统计
     * @params $permissionFlag 判断用户下有没有存储资源
     * @return string[][]|NULL[][]|number[][]|unknown[][]
     */
    private function getStorageChartData($permissionFlag = true){
        $days = 5; //一周
        $sql = "select distinct unix_timestamp(date) date, archive_write_size,copy_write_size from bd_storage_monitor where DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= date order by date asc";
        $data = $this->dbSelect($sql,array());
        $info = array();
        $utils = Xphp::instance('Utils');
        if (!$permissionFlag) {
            $data = [];
        }
        foreach ($data as $d){
            $dateTime = intval($d['date']) - 3600*24;
            $sizeInfo = $this->pGetIntToSizeInfo(intval($d['archive_write_size']) + intval($d['copy_write_size']), $utils);
            $info[] = array(
                'date' => date("m/d", $dateTime),
                'size' => $sizeInfo['size'],
                'value' => $sizeInfo['value'],
                'unit' => $sizeInfo['unit'],
                'des' => $sizeInfo['des']
            );
        }
        $info[] = $this->getTodayStorage($utils);
        return $info;
    }

    /**
     * 获取今天备份的数据量
     * @return string[]|NULL[]|number[]
     */
    private function getTodayStorage($utils){
        $today = date('Y-m-d');
        $sql = "select sum(total_object_write_size) as write_size from bd_history_task where finish_time like '%". $today . "%'  and module_type = ? ";
        //副本
        $sqlCopy = $sql . " and task_type in(17,26,30,38,44)";
        $dataCopy = $this->dbSelect($sqlCopy, array(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']));
        //归档
        $sqlArchive = $sql . " and task_type in(19)";
        $dataArchive = $this->dbSelect($sqlArchive, array(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']));
        $sizeInfo = $this->pGetIntToSizeInfo(intval($dataCopy[0]['write_size']) + intval($dataArchive[0]['write_size']), $utils);
        $info = array(
            'date' => date("m/d"),
            'size' => $sizeInfo['size'],
            'value' => $sizeInfo['value'],
            'unit' => $sizeInfo['unit'],
            'des' => $sizeInfo['des']
        );

        return $info;
    }


    /**
     * 公共函数
     * 获取保护数据总量
     * @param int $moduleType  模块类型
     */
    private function pGetProtectData($moduleType, $utils, $subModule = null){
        $sql = "select sum(write_size) as write_size from  bd_backup_timepoint where module_type = ? and user_uuid = ? and task_type = ? ";
        $taskType = Xphp::$_config['TASKTYPE']['BACKUP'];
        switch ($moduleType) {
            case Xphp::$_config['MODULE_TYPE']['DB']:
                $taskType = Xphp::$_config['TASKTYPE']['DB_BACKUP'];
                break;
            case Xphp::$_config['MODULE_TYPE']['OS']:
                $taskType = Xphp::$_config['TASKTYPE']['OS_BACKUP'];
                break;
            case Xphp::$_config['MODULE_TYPE']['KUBERNETES']://k8s
                $taskType = Xphp::$_config['TASKTYPE']['KUBE_BACKUP'];
                break;
        }
        $sqlParams = array($moduleType,Xphp::$_user['useruuid'],$taskType);
        // if($moduleType == Xphp::$_config['MODULE_TYPE']['VM'] && $subModule == Xphp::$_config['VM_SUB_MODULE']['VM']){
        //     $sql .= " and sub_module_type != ? ";
        //     $sqlParams = array_merge($sqlParams, array(Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']));
        // }else if(!empty($subModule)){
        //     $sql .= " and sub_module_type =? ";
        //     $sqlParams = array_merge($sqlParams, array($subModule));
        // }
        if(!empty($subModule)){
            $sql .= " and sub_module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($subModule));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return $this->pGetIntToSizeInfo(intval($data[0]['write_size']), $utils);
    }

    /**
     * 公共函数
     * 获取存储总量/已用/剩余
     * @param int $useMode 备份/副本/归档
     * @param string $storageUuid 存储uuid
     */
    private function pGetStorageInfo($useMode,$storageUuid = ''){
        $sql = "select sum(total_size) as total_size, sum(free_size) as free_size, count(storage_uuid) as total_num from  bd_storage_resource where use_mode = ? ";
        //userLevel是1 2 3就全部显示
        if (!in_array($_SESSION['userLevel'],[1,2,3])) {
            $resourceHandler =  Xphp::instance('ResourceHandler');
            $uuid = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                $info = array(
                    'total' => 0,
                    'free' => 0,
                    'used' => 0,
                    'total_num' => 0
                );
                return $info;
            } else {
                // $uuidArr 是一个一维数组
                $storageUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " and storage_uuid in ($storageUuidsIn)";
            }
        }
        if (!empty($storageUuid)) {
            $sql .= " and storage_uuid = '" . $storageUuid . "'";
        }
        $data = $this->dbSelect($sql, array($useMode));
        //返回已用容量
        if(!empty($data)) {
            $total = intval($data[0]['total_size']);
            $free = intval($data[0]['free_size']);
            $used = $total - $free;
            $info = array(
                'total' => $total,
                'free' => $free,
                'used' => $used,
                'total_num' => intval($data[0]['total_num'])
            );
        }else {
            $info = array(
                'total' => 0,
                'free' => 0,
                'used' => 0,
                'total_num' => 0
            );
        }
        return $info;
    }

    /**
     * 获取大小转换成界面显示信息
     * @param unknown $size
     */
    private function pGetIntToSizeInfo($size, $utils){
        $writeSize = $utils->calSize($size, true);
        $writeInfo = $utils->calSizeToValueAndUnit($size, true);
        $info = array(
            'size' => $size,
            'value' => $writeInfo['value'],
            'unit' => $writeInfo['unit'],
            'des' => $writeSize
        );
        return $info;
    }

    /**
     * 公共函数
     * 获取虚拟化中心在线离线占比
     * @param string $vcenterFlag 虚拟化中心标记，false云平台
     * @return number[]
     */
    private function pGetVcenterInfo($vcenterFlag = false){
        $sql = "select online_flag from vm_vcenter where hypervisor_type";
        // 将数组中的每个元素用单引号包围，并用逗号连接成字符串
        $hypervisorTypes = array_map(function($item) {
            return "'" . $item . "'";
        }, array_merge(Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'], Xphp::$_config['VMHYPERVISORGROUP']['openstack']));
        // 虚拟化中心
        if ($vcenterFlag) {
            $sql .= ' not  in (' . implode(',', $hypervisorTypes) . ') and user_uuid = ?';
        } else {
            //云平台
            $sql .= ' in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['openstack']) . ')' .' and user_uuid = ?';
        }
        $data = $this->dbSelect($sql,array(Xphp::$_user['useruuid'] ));
        $online = 0;
        $offline = 0;
        foreach ($data as $d){
            if(intval($d['online_flag']) == Xphp::$_config['FLAG']['SET']){
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
     * 获取卷CDP模块已配置的接管机器
     */
    public function getVolcdpTakeoverNum(){
        $task_type = Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'];
        $sql ='SELECT COUNT(*) total FROM bd_task BT, cdp_vol_task CVL 
            WHERE CVL.task_uuid = BT.task_uuid and BT.task_type = ? and (CVL.standby_agent_uuid <> "" or CVL.auto_takeover_flag = ?) and BT.user_uuid = ?';
        $data = $this->dbSelect($sql,array($task_type,Xphp::$_config['FLAG']['SET'],Xphp::$_user['useruuid']));
        return intval($data[0]['total']);
    }
    /**
     * 获取卷CDP模块的主机数
     * @param unknown $module
     */
    public function getVolcdpProtectHost($module){
        $task_type = Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'];
        $sql ="SELECT count(task_uuid) as total from bd_task where delete_flag = ? and  task_type = ? and module_type = ? and user_uuid = ?";
        $data = $this->dbSelect($sql, array(
                Xphp::$_config['FLAG']['UNSET'],
                $task_type,
                $module,
                Xphp::$_user['useruuid'])
        );
        return intval($data[0]['total']);
    }
    /**
     * 获取卷CDP模块任务个数
     * @param unknown $module
     */
    public function getVolcdpTaskNum($module){
        $sql = "select count(task_uuid) as total from bd_task where module_type =? and user_uuid = ? ";
        $data = $this->dbSelect($sql, array($module,Xphp::$_user['useruuid']));
        return intval($data[0]['total']);
    }

    /**
     *  获取卷cdp模块备份集个数
     * @param unknown $module
     * @return number
     */
    public function getVolcdpBackupSetNum($module){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $sql = "select count(cvbvs.id) as total from cdp_vol_backup_vol_set cvbvs,cdp_vol_backup_agent cvba,bd_backup_timepoint bbt where
         cvbvs.backup_agent_id = cvba.id and cvba.timepoint_uuid = bbt.timepoint_uuid and bbt.user_uuid = ? and cvba.storage_location!=? ";
        $data = $this->dbSelect($sql,array(Xphp::$_user['useruuid'],$VolCdpDes['DATA_SOURCE']['UNKNOWN']));
        return intval($data[0]['total']);
    }


    /**
     * 获取数据库实时主机在线/离线个数
     * @param unknown $type
     * @return number[]
     */
    public function getDBCDPHost($type){
        $sql = "select status from cdp_db_host where host_type = ? and user_uuid = ? ";
        $data = $this->dbSelect($sql, array($type,Xphp::$_user['useruuid']));
        $online = 0;
        $offline = 0;
        foreach ($data as $d){
            if(intval($d['status']) == Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE']){
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
     * 获取数据库实时受保护主机
     * @return number
     */
    public function getDBCDPProtectHost(){
        $sql = "select count(distinct cdh.host_uuid) as total from cdp_db_host cdh, cdp_db_task cdt where cdh.host_uuid = cdt.product_host_uuid and cdh.host_type = ? and cdh.user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['DB_CDP_HOST_TYPE']['product'],Xphp::$_user['useruuid']));
        return intval($data[0]['total']);
    }

    /**
     * 公共方法
     * 获取受保护主机个数
     * @param int $module 模块类型
     * @return number
     */
    private function pGetProtectHost($module,$taskType,$submodule = null){
        $sql ="select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $params = array(Xphp::$_config['FLAG']['UNSET'], $module, $taskType, Xphp::$_user['useruuid']);
        if (!empty($submodule)) {
            $sql .= ' and bt.sub_module_type = ?';
            $params = array_merge($params, array($submodule));
        }
        $data = $this->dbSelect($sql,$params);
        return intval($data[0]['total']);
    }

    /**
     * 公共方法
     * 获取模块任务个数
     * @param int $module 模块类型
     * @return number
     */
    private function pGetTaskNum($module, $subModule = null){
        $sql = "select count(task_uuid) as total from bd_task where module_type = ? and task_type = ? and user_uuid = ? ";
        $taskType = Xphp::$_config['TASKTYPE']['BACKUP'];
        switch ($module) {
            case Xphp::$_config['MODULE_TYPE']['FILE_COPY']://文件复制
                $taskType = Xphp::$_config['TASKTYPE']['FILE_COPY'];      
                break;
            case Xphp::$_config['MODULE_TYPE']['DB_CDP']://数据库复制    
                $taskType = Xphp::$_config['TASKTYPE']['DB_CDP_SYN'];   
                break;
            case Xphp::$_config['MODULE_TYPE']['VOL_CDP']: //整机复制 卷复制
                $taskType = Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION'];   
                break;
            case Xphp::$_config['MODULE_TYPE']['KUBERNETES']://k8s
             $taskType = Xphp::$_config['TASKTYPE']['KUBE_BACKUP'];   
                break;
        }
        $sqlParams = array($module, $taskType, Xphp::$_user['useruuid']);
        if(!empty($subModule)){
            $sql .= " and sub_module_type =? ";
            $sqlParams = array_merge($sqlParams, array($subModule));
        }
        
        $data = $this->dbSelect($sql, $sqlParams);
        return intval($data[0]['total']);
    }

    /**
     * 公共方法
     * 获取模块任务运行个数
     * @return number
     */
    private function pGetTaskRunNum($module){
        //获取任务总数和运行个数
        $sql = "select task_status from bd_task where module_type = ? and user_uuid = ? ";
        $data = $this->dbSelect($sql, array($module,Xphp::$_user['useruuid']));
        $runNum = 0;
        foreach ($data as $d){
            if(intval($d['task_status']) == Xphp::$_config['TASKSTATUS']['RUNNING']){
                $runNum ++;
            }
        }
        return $runNum;
    }

    /**
     * 公共方法
     * 获取模块时间点个数
     * @param int $module 模块类型
     * @return number
     */
    private function pGetBackuppointNum($module, $subModule = null){
        $sql = "select count(timepoint_uuid) as total from bd_backup_timepoint where module_type = ? and user_uuid = ? ";
        $sqlParams = array($module,Xphp::$_user['useruuid']);
        // if($subModule == Xphp::$_config['VM_SUB_MODULE']['VM']){
        //     $sql .= " and sub_module_type != ? ";
        //     $sqlParams = array_merge($sqlParams, array(Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']));
        // }else if(!empty($subModule)){
        //     $sql .= " and sub_module_type =? ";
        //     $sqlParams = array_merge($sqlParams, array($subModule));
        // }
        if(!empty($subModule)){
            $sql .= " and sub_module_type =? ";
            $sqlParams = array_merge($sqlParams, array($subModule));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return intval($data[0]['total']);
    }

    // 浪潮新增方法
    /**
     * 当前任务列表
     * @param unknown $params
     */
    public function getCurrentTaskList($params){
        $info = array();
        $sql = "SELECT 
                bt.task_uuid, bt.task_name, bt.module_type, bt.sub_module_type, bt.task_type, 
                unix_timestamp(bt.create_time) create_time, bt.task_status, bt.strategy_id, bu.user_name,
                bri.total_object_size, bri.total_object_completed_size,
                bri.total_object_valid_size, bri.total_object_completed_valid_size,
                cvt.dev_type, cvti.takeover_agent_role, cvt.standby_agent_uuid,
                (SELECT GROUP_CONCAT(sbt.module_type SEPARATOR ',') 
                 FROM sr_surebackup_item sbt WHERE sbt.sr_task_uuid = bt.task_uuid) AS sure_module_types,
                (SELECT GROUP_CONCAT(sbt.submodule_type SEPARATOR ',') 
                 FROM sr_surebackup_item sbt WHERE sbt.sr_task_uuid = bt.task_uuid) AS sure_sub_module_types,
                vml.vcenter_uuid, vc.hypervisor_type, vc.nickname,
                bbt.module_type AS backup_module_type, bbt.sub_module_type AS backup_sub_module_type
            FROM bd_running_info bri 
            JOIN bd_task bt ON bt.task_uuid = bri.task_uuid
            JOIN bd_user bu ON bt.user_uuid = bu.user_uuid
            LEFT JOIN sr_sure_backup ssb ON bt.task_uuid = ssb.task_uuid
            LEFT JOIN cdp_vol_task cvt ON bt.task_uuid = cvt.task_uuid
            LEFT JOIN cdp_vol_task_takeover_info cvti ON bt.task_uuid = cvti.task_uuid
            LEFT JOIN vm_machine_list vml ON bt.task_uuid = vml.task_uuid
            LEFT JOIN vm_vcenter vc ON vml.vcenter_uuid = vc.vcenter_uuid
            LEFT JOIN bd_backup_timepoint bbt ON (
                (bt.module_type = ? AND vml.timepoint_uuid = bbt.timepoint_uuid) OR
                (bt.module_type = ? AND EXISTS (
                    SELECT 1 FROM os_list ol 
                    WHERE ol.task_uuid = bt.task_uuid AND ol.timepoint_uuid = bbt.timepoint_uuid
                )) OR
                (bt.module_type = ? AND EXISTS (
                    SELECT 1 FROM cdp_vol_task_restore_info cvtri
                    WHERE cvtri.task_uuid = bt.task_uuid AND cvtri.recovery_datetime_uuid = bbt.timepoint_uuid
                ))
            )
            WHERE bt.delete_flag = ? 
            AND bt.task_type != ? 
            AND bt.task_type != ? ";
        $moduleType =  Xphp::$_config['MODULE_TYPE'];
        $taskTypeConfig =  Xphp::$_config['TASKTYPE'];
        $sqlParams = [
            $moduleType['VM'],    // 虚拟机模块类型
            $moduleType['OS'],    // 整机模块类型
            $moduleType['VOL_CDP'], // 卷实时模块类型
            Xphp::$_config['FLAG']['UNSET'],
            $taskTypeConfig['ORCH_TASK'], 
            $taskTypeConfig['BACKUP_EXPORT']
        ];

        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission']) && $_SESSION['userUuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9'){
             // 关联管理用户判断 当前任务 - 查看
             $authUser = $_SESSION['authUser']['current_job_look'] ?? [];
             if (!empty($authUser)) {
                 // 表示有管理的用户
                 $useruuid = array_merge([$_SESSION['userUuid']], $authUser);
                 $useruuid = "('" . implode("','", $useruuid) . "')";
                 $sql .= ' and bu.user_uuid in ' . $useruuid;
             } else {
                 $sql .= ' and bu.user_uuid = ? ';
                 $sqlParams = array_merge($sqlParams, array($_SESSION['userUuid']));
             }
        }
        $sql .= " order by bt.create_time desc limit 0,20 ";
        $data = $this->dbSelect($sql, $sqlParams);
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        foreach ($data as $d){
            // 完全复制 getJobList 中的对象类型处理逻辑
            $moduleTypeValue = !empty($d['sure_module_types']) ? $d['sure_module_types'] : $d['module_type'];
            $subModuleTypeValue = !empty($d['sure_sub_module_types']) ? $d['sure_sub_module_types'] : $d['sub_module_type'];
            // 处理跨平台恢复任务的对象类型
            if ($d['task_type'] == $taskTypeConfig['PLATFORM_RECOVERY']) {
                if ($d['module_type'] == $moduleType['VM']) {
                    $moduleTypeValue = $d['backup_module_type'];
                    $subModuleTypeValue = $d['backup_sub_module_type'];
                } elseif ($d['module_type'] == $moduleType['OS']) {
                    $moduleTypeValue = $d['backup_module_type'];
                    $subModuleTypeValue = $d['backup_sub_module_type'];
                } elseif ($d['module_type'] == $moduleType['VOL_CDP']) {
                    $moduleTypeValue = $d['backup_module_type'];
                }
            }

            // 获取对象类型名称
            $objectType = $this->getObjects(
                $moduleTypeValue,
                $subModuleTypeValue,
                $d['dev_type'] ?? ''
            );
            $taskType = intval($d['task_type']);
            $info[] = array(
                $d['module_type'], //前一个module_type作为类型显示对应图片
                $d['task_name'],
                $objectType['des'],
                $this->getTaskNameString($d['module_type'],$taskType),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $objectType['value']
            );
        }
        $list = array(
            'data' => $info
        );
        return json_encode($list);
    }

    /**
     * 历史任务列表
     * @param unknown $params
     */
    public function getHistoryTaskList($params){
        $info = array();
        $sql = "SELECT 
                bht.id, bht.task_name, bht.module_type, bht.submodule_type, bht.task_type, bht.error_code,
                unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time, 
                bht.task_uuid, bht.history_uuid,
                (SELECT GROUP_CONCAT(sbt.module_type SEPARATOR ',') 
                 FROM sr_surebackup_item sbt WHERE sbt.sr_task_uuid = bht.task_uuid) AS sure_module_types,
                (SELECT GROUP_CONCAT(sbt.submodule_type SEPARATOR ',') 
                 FROM sr_surebackup_item sbt WHERE sbt.sr_task_uuid = bht.task_uuid) AS sure_sub_module_types,
                cvt.dev_type, cvti.takeover_agent_role
            FROM bd_history_task bht
            LEFT JOIN cdp_vol_task cvt ON bht.task_uuid = cvt.task_uuid
            LEFT JOIN cdp_vol_task_takeover_info cvti ON bht.task_uuid = cvti.task_uuid";
        $sqlParams =  array();
        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission']) && $_SESSION['userUuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9'){
            // 关联管理用户判断 历史任务 - 查看
            $authUser = $_SESSION['authUser'];
            if (!empty($authUser['history_job_look'])) {
                // 表示有管理的用户
                $useruuid = array_merge([$_SESSION['userUuid']], $authUser['history_job_look']);
                $useruuid = "('" . implode("','", $useruuid) . "')";
                $sql .= ' where bht.user_uuid in ' . $useruuid;
            } else {
                $sql .= ' where bht.user_uuid = ? ';
                $sqlParams = array_merge($sqlParams, array($_SESSION['userUuid']));
            }
        }
        $sql .= " order by bht.finish_time desc limit 0,20 ";
        $data = $this->dbSelect($sql,$sqlParams);
        $moduleType =  Xphp::$_config['MODULE_TYPE'];
        // 准备虚拟化类型分组信息
        $vmHypervisorArr = array_diff(
            Xphp::$_config['VMHYPERVISORTYPE'],
            Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'],
            Xphp::$_config['VMHYPERVISORGROUP']['openstack']
        );
        $publicCloudArr = Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'];
        $privateCloudArr = Xphp::$_config['VMHYPERVISORGROUP']['openstack'];

        foreach ($data as $d){

            // 完全复制 getHistoryList 中的对象类型处理逻辑
            $moduleTypeValue = !empty($d['sure_module_types']) ? $d['sure_module_types'] : $d['module_type'];
            $subModuleTypeValue = !empty($d['sure_sub_module_types']) ? $d['sure_sub_module_types'] : $d['submodule_type'];
            // 处理虚拟机子模块类型
            if ($d['module_type'] == $moduleType['VM']) {
                if (in_array($d['submodule_type'], $privateCloudArr)) {
                    $subModuleTypeValue = 2; // 私有云
                } elseif (in_array($d['submodule_type'], $publicCloudArr)) {
                    $subModuleTypeValue = 3; // 公有云
                } elseif (in_array($d['submodule_type'], $vmHypervisorArr)) {
                    $subModuleTypeValue = 1; // 虚拟化
                }
            }
            // 获取对象类型名称
            $objectType = $this->getObjects(
                $moduleTypeValue,
                $subModuleTypeValue,
                $d['dev_type'] ?? ''
            );

            $taskType = intval($d['task_type']);
            $info[] = array(
                $d['module_type'], //前一个module_type作为类型显示对应图片
                $d['task_name'],
                $objectType['des'],
                $this->getTaskNameString($d['module_type'],$taskType),
                date("Y-m-d H:i:s",$d['finish_time']),
                $this->getHistoryJobResultDes($d['error_code']),
                $objectType['value']
            );
        }
        $list = array(
            'data' => $info
        );
        return json_encode($list);
    }

    /**
     * 根据 moduleType 和 subModuleType 获取对应的中文对象名称
     *
     * @param string|int|array $moduleType    模块类型（多个用逗号分隔）
     * @param string|int|array $subModuleType 模块类型（多个用逗号分隔）
     * @param int              $devType       设备类型（用于 VOL_CDP 类型区分卷/整机）
     * @return array 匹配的对象名称，多个用逗号连接
     */
    public function getObjects($moduleType, $subModuleType = '', $devType = 0)
    {
        $moduleArr = Xphp::$_config['MODULE_TYPE'];
        if ($moduleType == $moduleArr['VOL_CDP']) {
            // 是实时，那么根据 devType 来判断
            if($devType ==  1){
                return array(
                    'des'=> Xphp::$_lang['UI_PLATFORM_CDP_REEL_BACKUP'],
                    'value' => 10
                );
            }else{
                 return array(
                    'des'=> Xphp::$_lang['UI_PLATFORM_CDP_COMPLETE_BACKUP'],
                    'value' => 10
                );
            }
            
        } elseif ($moduleType == $moduleArr['DB']) {  // 数据库模块不需要判断sub_module_type
            return array(
                'des'=> Xphp::$_lang['UI_PLATFORM_DATABASE'],
                'value' => 4
            );
        }

        $moduleArrs = explode(',', trim($moduleType, ','));
        $subModuleArrs = explode(',', trim($subModuleType, ','));

        // 如果 moduleArrs 为空，直接返回空
        if (empty($moduleArrs)) {
            return array();
        }

        // 根据module_type和sub_module_type返回对应的对象信息
        $objectArr = [
            '2-0' => array('des'=>'UI_PLATFORM_VM_VIRTUAL', 'value'=>'2'), // 虚拟化
            '2-1' => array('des'=>'UI_PLATFORM_VM_VIRTUAL', 'value'=>'2'),  // 虚拟化
            '2-2' => array('des'=>'UI_PLATFORM_PRIVATE_CLOUD', 'value'=>'22'), // 私有云
            '2-3' => array('des'=>'UI_PLATFORM_PUBLIC_CLOUD', 'value'=>'17'), // 公有云
            '5-0' => array('des'=>'UI_PLATFORM_MACHINE_REEL_BACKUP', 'value'=>'5'), // 定时卷（数据库中submodule_type存的0）
            '5-1' => array('des'=>'UI_PLATFORM_CDP_COMPLETE_BACKUP', 'value'=>'10'), // 定时整机
            '5-2' => array('des'=>'UI_PLATFORM_MACHINE_REEL_BACKUP', 'value'=>'5'), // 定时卷（兼容前端过滤筛选）
            '11-2' => array('des'=>'UI_PLATFORM_NAS', 'value'=>'11'),// 文件系列 nas
            '3-1' => array('des'=>'UI_PLATFORM_FILES', 'value'=>'3'),// 文件系列 文件
            '3-3' => array('des'=>'UI_PLATFORM_HADOOP_HDFS', 'value'=>'998'),// 文件系列 HADOOP
            '3-4' => array('des'=>'UI_PLATFORM_OBS_STORAGE', 'value'=>'999'),// 文件系列 OBS
            '4-0' => array('des'=>'UI_PLATFORM_DATABASE', 'value'=>'4'), // 数据库
            '14-0' => array('des'=>'UI_PLATFORM_MICROSOFT365', 'value'=>'14'), // Microsoft 365
            '14-1' => array('des'=>'UI_PLATFORM_MICROSOFT365', 'value'=>'14'), // Microsoft 365
            '28-0' => array('des'=>'UI_PLATFORM_K8S', 'value'=>'28'), // 容器
            '26-0' => array('des'=>'UI_PLATFORM_FILES', 'value'=>'9'), // 文件复制 文件
            '12-0' => array('des'=>'UI_PLATFORM_DATABASE', 'value'=>'9'), // 数据库复制 数据库
            '10-0' => array('des'=>'UI_PLATFORM_CDP_COMPLETE_BACKUP', 'value'=>'10'), // 实时 需要根据 dev_type 区分，备份类型 1卷 2整机
            '10000-0' => array('des'=>'UI_PLATFORM_DATABASE', 'value'=>'10000'), // 友商数据库
        ];

        $result = [];
        $value = '';
        foreach ($moduleArrs as $key => $item) {
            $subKey = !isset($subModuleArrs[$key]) ? 0 : $subModuleArrs[$key];
            $items = $item . '-' . $subKey;
            if (isset($objectArr[$items])) {
                $result[] =  Xphp::$_lang[$objectArr[$items]['des']];
                $value = $objectArr[$items]['value'];
            }
        }

        // 去重 + 合并成字符串
        $result = array_unique($result);
        return array(
            'des' => implode(',', $result),
            'value' => $value
        );
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
//         $error = include CONF_PATH . "error.php";
        if(in_array(Xphp::$_error['errorCode'][$errorCode], $abnormal)){
            return Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'];
        }
        if(in_array(Xphp::$_error['errorCode'][$errorCode], $discontinue)){
            return Xphp::$_lang['WEB_PLATFORM_DES_DISCONTINUE'];
        }
        return $errorCode == 0 ? Xphp::$_lang['WEB_PUBLIC_SUCCESS'] : Xphp::$_lang['WEB_PUBLIC_FAILURE'];
    }

    /**
     * 得到浪潮cpu、内存使用率、bps、iops读写速度
     */
    public function getcpuMemoryData($params){
        $count = $params['count'];      //本次取的条数
        $node_uuid = $params['node_uuid'];
        $sql = "select node_uuid, monitor_time, cpu_percentage, ram_percentage
        from bd_system_monitor where node_uuid = ? 
        order by monitor_time desc limit 0,?";
        $result = $this->dbSelect($sql,array($node_uuid,$count));
        $info =  array();
        foreach ($result as $each){
            $info[] = array(
                "cpumemorydata"=>array(
                    "cpuUsedRate" => floatval($each["cpu_percentage"]),
                    "memoryUsedRate" => floatval($each["ram_percentage"]),
                ),
                'time'=>date("H:i:s",strtotime($each['monitor_time'])) ,
            );
        }
        return $info;

    }

    /**
     * 浪潮数据概览
     * @param unknown $params
     */
    public function systemDataCenter(){
        //当前任务个数
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu where  bt.delete_flag = ? and bt.user_uuid = bu.user_uuid and bu.user_uuid = ?";
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET'],Xphp::$_user['useruuid']);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        // 历史任务个数
        $sqlCount = "select count(distinct id) as total from bd_history_task where (user_uuid = ? or (user_uuid = '' and user_name = ?))";
        $sqlCountParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username']);
        $hisCount = $this->dbSelect($sqlCount, $sqlCountParams);

        $systemHandler = Xphp::instance('SystemHandler');
        $systemRunningTimeInfo = array(
            "runningTime" => date("Y-m-d H:i:s", $systemHandler->getSystemTime()),
            "runningDay" => $this->getSpurRunningDays(),
        );
        $info = array(
            "systemRunningTimeInfo" => $systemRunningTimeInfo,
            "accumulateData" => $this-> getAccumulatedData(),
            "currentTaskNum" => $count[0]['total'],
            "historyTaskNum" => $hisCount[0]['total'],
        );
        return $info;
    }
    /**
     * 得到浪潮系统运行天数
     */
    private function getSpurRunningDays(){
        $sql = "select system_run_time from bd_system";
        $data = $this->dbSelect($sql);
        $value = round($data[0]['system_run_time'], 1);
        $date = intval($value/24);
        // $dateDes = $date.Xphp::$_lang['WEB_UTILS_DAY'];
        $runday = array(
            "day" =>  $date
        );
        if($date >= 365){
            $year = intval($date/365);
            $days = $date % 365;
            //$dateDes = $year .Xphp::$_lang['WEB_UTILS_YEAR']. $days . Xphp::$_lang['WEB_UTILS_DAY'];
            $runday =  array(
                "year"=> $year,
                "day" => $days
            );
        }
        return $runday;
    }
    //获取浪潮最近七日备份数据
    /**
     * 数据统计，存储统计，存储详情
     * @param unknown $params
     */
    public function getStatisticData($params){
        $dataDetails = $this->getStatisticDataDetails();

        $info = array(
            'data_statistics' => $dataDetails,
        );
        return $info;
    }
    /**
     * 获取数据统计信息
     */
    private function getStatisticDataDetails(){
        $dataDetials = array();
        //每日数据统计列表
        $dataDetials['list'] = $this->getStatisticDataDetailsList();

        return $dataDetials;
    }

    /**
     * 获取数据统计的每日备份，副本，归档情况列表
     * @return array    $info
     */
    private function getStatisticDataDetailsList(){
        $info = array();
        //当天的数据从时间点中获取
        $todayInfo = array(
            'date' => date("m/d"),
        );
        //计算备份、副本、归档总共的用量大小
        $valueAndUnit=  $this->getTodaySomeTaskTypeTimepointWriteSize();
        $todayInfo['total_data'] = $valueAndUnit['size'];          //归档数据真实量
        $todayInfo['total_data_des'] = $valueAndUnit['value'];     //归档数据总量
        $todayInfo['total_data_des_unit'] = $valueAndUnit['unit']; //归档数据总量的单位
        $info[] = $todayInfo;
        $today = date('Y-m-d');
        $dateTime1 = strtotime($today);
        $staticdate =  array();
        $utils = Xphp::instance('Utils');
        for ($i = 0 ; $i < 6 ; $i++) {
            $vartime =  date("Y-m-d", $dateTime1);
            array_push($staticdate,$vartime);
            $dateTime1 -= 3600*24;
        }
        for ($i = 0 ; $i < 6 ; $i++) {
            $date1 =  date("m/d", strtotime($staticdate[$i])-3600*24);
            $dateyear =  date("Y-m-d", strtotime($staticdate[$i])-3600*24);
            // 2023-02-27
            //-1到另外一张表取数据
            $sql1 ="select sum(total_object_write_size) as write_size from bd_history_task where finish_time like '%". $dateyear . "%' ";
            $data = $this->dbSelect($sql1);
            $sizeInfo = $this->pGetIntToSizeInfo(intval($data[0]['write_size']), $utils);
            $info[] = array(
                'date' => $date1,
                'total_data'=> $sizeInfo['size'],
                'total_data_des'=> $sizeInfo['value'],
                'total_data_des_unit'=>$sizeInfo['unit']
            );
        }
        //按时间顺序排列
        $info = array_reverse($info);
        return $info;
    }
    /**
     * 获取指定类型任务的当天写入数据总大小
     * @param array $taskTypeArr    任务类型
     * @return array $valueAndUnit  大小和单位和真实值
     */
    private function getTodaySomeTaskTypeTimepointWriteSize(){
        $today = date('Y-m-d');
        $sql ="select sum(total_object_write_size) as write_size from bd_history_task where finish_time like '%". $today . "%' ";

        $data = $this->dbSelect($sql);

        $utils = Xphp::instance('Utils');
        $sizeInfo = $this->pGetIntToSizeInfo(intval($data[0]['write_size']), $utils);
        $info = array(
            'date' => date("m/d"),
            'size' => $sizeInfo['size'],
            'value' => $sizeInfo['value'],
            'unit' => $sizeInfo['unit'],
            'des' => $sizeInfo['des']
        );
        return $info;
    }
    /**
     * 存储统计
     * @param unknown $params
     */
    public function getSystemStorageTotalData(){
        //TODO
        $utils = Xphp::instance('Utils');
        $info = array(
            'storageInfo' => $this->getStorageTotalInfo($utils),
        );
        return $info;
    }
    public function getStorageTotalInfo($utils){
        $storageInfo = $this->pGetStorageTotalInfo();
        $info = array(
            'bakStorageNum' => $storageInfo['total_num'],
            'bakStorageCapacity' => $this->pGetIntToSizeInfo($storageInfo['total'], $utils),
            'usedCapacity' => $this->pGetIntToSizeInfo($storageInfo['used'], $utils),
            'remainingCapacity' => $this->pGetIntToSizeInfo($storageInfo['free'], $utils)
        );
        return $info;
    }

    /**
     * 公共函数
     * 获取存储总量/已用/剩余
     * @param int $useMode 备份/副本/归档
     */
    private function pGetStorageTotalInfo(){
        $sql = "select sum(total_size) as total_size, sum(free_size) as free_size, count(storage_uuid) as total_num from  bd_storage_resource ";
        $data = $this->dbSelect($sql);
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

    //获取浪潮系统实时数据
    public function getSystemData($params){
        $data = array(
            "cpumemory" =>  $this->getcpuMemoryData($params),
            "systemViewData" => $this->systemDataCenter(),
            "systemStorageData"=>$this->getSystemStorageTotalData($params),
            "netWorkData"=>$this->getNetworkChartData($params),
            "staticData" => $this->getStatisticData($params)
        );
        return json_encode($data);
    }
    /**
     * 当前任务中的任务类型修改部分描述,在任务中的当前任务虚拟机  文件  数据库中用
     * @param unknown $module_type  模块类型
     * @param unknown $task_type  任务类型
     * @author liushuai@vinchin.com
     * @return string
     */
    public function getTaskNameString($module_type,$task_type){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $moduleType = Xphp::$_config['MODULE_TYPE'];
        $taskType = Xphp::$_config['TASKTYPE'];
        $moduleTypeArr = [
            $moduleType['DB'] => [ // 如果为数据库
                $taskType['DB_BACKUP'] => ('WEB_PLATFORM_DES_BACKUP'),
                $taskType['DB_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OEM_DBCDP'] => [ // 如果为数据库实时
                $taskType['DB_CDP_BACKUP'] => 'CDP',
                $taskType['DB_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OEM_FSCDP'] => [
                $taskType['FILE_CDP_BACKUP'] => ('WEB_PLATFORM_DES_REAL_TIME_SYN'),
                $taskType['DB_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OS'] => [
                $taskType['OS_BACKUP'] => ('WEB_PLATFORM_DES_BACKUP'),
                $taskType['OS_INSTANT_RECOVERY'] => ('WEB_PLATFORM_DES_INSTANT_RECOVERY'),
                $taskType['OS_INSTANT_RECOVERY_MOTION'] => ('WEB_PLATFORM_DES_MOTION'),
            ],
             $moduleType['DB_CDP'] => [
                $taskType['CDP_DB_BACKUP'] => ('WEB_PLATFORM_REPLICATION'),
                $taskType['CDP_DB_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['VOL_CDP'] => [
                $taskType['VOL_CDP_BACKUP'] => ('UI_CLIENT_CDP_BACKUP'),
                $taskType['VOL_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
                $taskType['VOL_CDP_TAKEOVER'] => ('WEB_PLATFORM_DES_TAKEOVER'),
                $taskType['VOL_CDP_REPLICATION'] => ('WEB_PLATFORM_REPLICATION'),
            ]
        ];
        return !empty($moduleTypeArr[$module_type][$task_type])
        ? Xphp::$_lang[$moduleTypeArr[$module_type][$task_type]] :
        $ptDes['TASKTYPEDES'][$task_type] ?? '';
    }

    /*------------------------------------------------------------------------------------相孚---------------------------------------------------------------*/
    //相孚新增方法
    /**
     * 历史任务列表
     * @param unknown $params
     */
    public function getXfHistoryTaskList(){
        $info = array();
        $sql = "select id, task_name, module_type, task_type, error_code,
        unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time, task_uuid from bd_history_task order by finish_time desc";
        $data = $this->dbSelect($sql);
        foreach ($data as $d){
            $info[] = array(
                $d['module_type'], //前一个module_type作为类型显示对应图片
                $d['task_name'],
                $d['module_type'],
                $d['task_type'],
                date("Y-m-d H:i:s",$d['finish_time']),
                $this->getHistoryJobResultDes($d['error_code']),
            );
        }
        $list = array(
            'data' => $info
        );
        return $list;
    }
    public function getXfCurrentTaskList(){
        $info = array();
        $sql = "select ssb.task_progress, bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                     bt.task_status, bt.strategy_id, bu.user_name,
                     bri.total_object_size, bri.total_object_completed_size,
                     bri.total_object_valid_size,bri.total_object_completed_valid_size
             from bd_running_info bri, bd_user bu, bd_task bt
                  left join sr_sure_backup ssb on bt.task_uuid = ssb.task_uuid
             where bt.task_uuid = bri.task_uuid and
                  bt.user_uuid = bu.user_uuid and
                  bt.delete_flag = ? and bt.task_type != ? and bt.task_type != ? 
             order by bt.create_time desc";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'],Xphp::$_config['TASKTYPE']['ORCH_TASK'], Xphp::$_config['TASKTYPE']['BACKUP_EXPORT']);
        $data = $this->dbSelect($sql, $sqlParams);
        foreach ($data as $d){
            $info[] = array(
                $d['module_type'], //前一个module_type作为类型显示对应图片
                $d['task_name'],
                $d['module_type'],
                $d['task_type'],
                $d['task_status'],
            );
        }
        $list = array(
            'data' => $info
        );
        return $list;
    }

    /**
     * 相孚数据概览
     * @param unknown $params
     */
    public function systemXfDataCenter(){
        //当前任务个数
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu where  bt.delete_flag = ? and bt.user_uuid = bu.user_uuid and bu.user_uuid = ?";
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET'],Xphp::$_user['useruuid']);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        // 历史任务个数
        $sqlCount = "select count(distinct id) as total from bd_history_task where (user_uuid = ? or (user_uuid = '' and user_name = ?))";
        $sqlCountParams = array(Xphp::$_user['useruuid'], Xphp::$_user['username']);
        $hisCount = $this->dbSelect($sqlCount, $sqlCountParams);

        $systemHandler = Xphp::instance('SystemHandler');
        $systemRunningTimeInfo = array(
            "runningTime" => date("Y-m-d H:i:s", $systemHandler->getSystemTime()),
            "runningDay" => $this->getXfRunningDays(),
        );
        $info = array(
            "systemRunningTimeInfo" => $systemRunningTimeInfo,
            "accumulateData" => $this-> getAccumulatedData(),
            "currentTaskNum" => $count[0]['total'],
            "historyTaskNum" => $hisCount[0]['total'],
            "seventotal"  => $this->getProtectDataWeekTotal(),
        );
        return $info;
    }
    /**
     * 得到相孚系统运行天数
     */
    private function getXfRunningDays(){
        $sql = "select system_run_time from bd_system";
        $data = $this->dbSelect($sql);
        $value = round($data[0]['system_run_time'], 1);
        $date = intval($value/24);
        $hour =  intval($value % 24);
        $runday = array(
            "day" =>  $date,
            "hour" => $hour,
        );
        if($date >= 365){
            $year = intval($date/365);
            $days = $date % 365;
            $runday =  array(
                "year"=> $year,
                "day" => $days,
                "hour"=> $hour,
            );
        }
        return $runday;
    }
    //获取相孚最近七日备份数据
    // 获取最近一周数据统计保护
    private function getProtectDataWeekTotal(){
        $today = date('Y-m-d H:i:s');
        $weekday = date('Y-m-d H:i:s',strtotime("-7 day"));
        $sql = "select sum(total_size) as total_size from bd_backup_timepoint where timepoint between ? and ? order by timepoint desc";
        $sqlCountParams = array($weekday,$today);
        $utils = Xphp::instance('Utils');
        $totalsize =  $this->dbSelect($sql,$sqlCountParams);
        $totalUnit = $utils->calSizeToValueAndUnit($totalsize[0]['total_size'],true);
        $totalinfo = array(
            'size'=>$totalsize,
            'data'=>$totalUnit,
        );
        return $totalinfo;
    }
    //获取相孚系统实时数据
    public function getXfSystemData($params){
        $data = array(
            "cpumemory" =>  $this->getcpuMemoryData($params),
            "systemViewData" => $this->systemXfDataCenter(),
            "systemStorageData"=>$this->getSystemStorageTotalData($params),
            "netWorkData"=>$this->getNetworkChartData($params),
            "staticData" => $this->getStatisticData($params),
            "historytask" =>$this->getXfHistoryTaskList(),
            "currenttask"=>$this->getXfCurrentTaskList(),
        );
        return json_encode($data);
    }
    //获取系统时间
    public function getSystemTime(){
        $systemHandler = Xphp::instance('SystemHandler');
        $data =  array(
            "systime" => date("Y-m-d H:i:s", $systemHandler->getSystemTime()),
        );
        return json_encode($data);
    }

    /**
     * 获取M365信息
     * @return string[]
     */
    private function getProtectM365Info($utils)
    {
        $module = Xphp::$_config['MODULE_TYPE']['M365'];
        //组织信息
        $organizationInfo = $this->getOrganizationInfo();
        $info = array(
            'protectedNum' => $organizationInfo['total'],
            'backupDataSize' =>  $this->pGetProtectData(Xphp::$_config['MODULE_TYPE']['M365'], $utils),
            'taskNum' => $this->pGetTaskNum($module),
            'backupPointNum' => $this->pGetBackuppointNum($module),
            'onlineNum' => $organizationInfo['online'],
            'offlineNum' => $organizationInfo['offline'],
        );
        return $info;
    }
    /**
     * 获取对象存储信息
     * @return string[]
     */
    private function getProtectObsInfo($utils)
    {
        $module = Xphp::$_config['MODULE_TYPE']['FS'];
        $subModule = Xphp::$_config['SUBMODULE_TYPE']['OBS'];
        //对象存储
        $obsInfo = $this->getObsInfo();
        $info = array(
            'protectedNum' => $obsInfo['total'],
            'backupDataSize' =>  $this->pGetProtectData($module, $utils, $subModule),
            'taskNum' => $this->pGetTaskNum($module, $subModule),
            'backupPointNum' => $this->pGetBackuppointNum($module, $subModule),
            'onlineNum' => $obsInfo['online'],
            'offlineNum' => $obsInfo['offline'],
        );
        return $info;
    }
    /**
     * 获取Hadoop信息
     * @return string[]
     */
    private function getProtectHadoopInfo($utils)
    {
        $module = Xphp::$_config['MODULE_TYPE']['FS'];
        $subModule = Xphp::$_config['SUBMODULE_TYPE']['HADOOP'];
        //hadoop集群
        $hadoopInfo = $this->getHadoopInfo();
        $info = array(
            'protectedNum' => $hadoopInfo['total'],
            'backupDataSize' =>  $this->pGetProtectData($module, $utils, $subModule),
            'taskNum' => $this->pGetTaskNum($module, $subModule),
            'backupPointNum' => $this->pGetBackuppointNum($module, $subModule),
            'onlineNum' => $hadoopInfo['online'],
            'offlineNum' => $hadoopInfo['offline'],
        );
        return $info;
    }

    /**
     * 获取组织信息
     * @return number[]
     */
    private function getOrganizationInfo()
    {
        $sql = "select online_flag from m365_organization";
        $data = $this->dbSelect($sql);
        //受保护的组织
        $sqlProtect = "select count(distinct mol.organization_uuid) as total from m365_object_list mol, bd_task bt where bt.task_uuid = mol.task_uuid and bt.task_type = ?";
        $dataProtect = $this->dbSelect($sqlProtect,array(Xphp::$_config['TASKTYPE']['BACKUP']));
        $online = 0;
        $offline = 0;
        foreach ($data as $d) {
            if (intval($d['online_flag']) == Xphp::$_config['FLAG']['SET']) {
                $online++;
            } else {
                $offline++;
            }
        }

        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $dataProtect[0]['total']
        );
        return $info;
    }
    /**
     * 获取对象存储信息
     * @return []
     */
    private function getObsInfo()
    {
        $sql = "select status from obs_resource";
        $data = $this->dbSelect($sql);
        //受保护的hadoop集群
        $sqlProtect ="select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.sub_module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $dataProtect = $this->dbSelect($sqlProtect, array(
                Xphp::$_config['FLAG']['UNSET'],
                Xphp::$_config['MODULE_TYPE']['FS'],
                Xphp::$_config['SUBMODULE_TYPE']['OBS'],
                Xphp::$_config['TASKTYPE']['BACKUP'],
                Xphp::$_user['useruuid'])
        );
        $online = 0;
        $offline = 0;
        foreach ($data as $d) {
            if (intval($d['status']) == Xphp::$_config['FLAG']['SET']) {
                $online++;
            } else {
                $offline++;
            }
        }
        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $dataProtect[0]['total']
        );
        return $info;
    }
    /**
     * 获取hadoop集群信息
     * @return []
     */
    private function getHadoopInfo()
    {
        $sql = "select status from hadoop_cluster";
        $data = $this->dbSelect($sql);
        //受保护的hadoop集群
        $sqlProtect ="select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.sub_module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $dataProtect = $this->dbSelect($sqlProtect, array(
                Xphp::$_config['FLAG']['UNSET'],
                Xphp::$_config['MODULE_TYPE']['FS'],
                Xphp::$_config['SUBMODULE_TYPE']['HADOOP'],
                Xphp::$_config['TASKTYPE']['BACKUP'],
                Xphp::$_user['useruuid'])
        );
        $online = 0;
        $offline = 0;
        foreach ($data as $d) {
            if (intval($d['status']) == Xphp::$_config['FLAG']['SET']) {
                $online++;
            } else {
                $offline++;
            }
        }
        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $dataProtect[0]['total']
        );
        return $info;
    }

    /**
     * 获取aws信息
     * @return string[]
     */
    private function getProtectAwsInfo($utils)
    {
        $module = Xphp::$_config['MODULE_TYPE']['VM'];
        //云平台信息
        $cloudInfo = $this->getCloudInfo();
        //实例信息
        $exampleInfo = $this->getExampleInfo();
        $info = array(
            'backupDataSize' =>  $this->pGetProtectData($module, $utils, Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']),
            'taskNum' => $this->pGetTaskNum($module, Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']),
            'backupPointNum' => $this->pGetBackuppointNum($module, Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD']),
            'cloudInfo' => $cloudInfo,
            'exampleInfo' => $exampleInfo,
        );
        return $info;
    }

    /**
     * 云平台信息
     * @return number
     */
    private function getCloudInfo()
    {
        $sql = 'select count(vcenter_id) as total from vm_vcenter where hypervisor_type in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ')' .' and user_uuid  =?';
        $count = 'select online_flag from vm_vcenter where hypervisor_type in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ')'.' and user_uuid  =? ';
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $dataCount = $this->dbSelect($count, array(Xphp::$_user['useruuid']));
        $online = 0;
        $offline = 0;
        $total = $data[0]['total'];
        foreach ($dataCount as $d) {
            if (intval($d['online_flag']) == Xphp::$_config['FLAG']['SET']) {
                $online++;
            } else {
                $offline++;
            }
        }
        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $total
        );
        return $info;
    }

    /**
     * 实例信息
     * @return number
     */
    private function getExampleInfo()
    {
        $sql = 'select count(vt.tree_id) as total from vm_tree vt, vm_vcenter vv where vt.type = ? and vt.vcenter_uuid = vv.vcenter_uuid and vv.hypervisor_type in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ')'.'  and vv.user_uuid  =?';
        $data = $this->dbSelect($sql, array(Xphp::$_config['VM_TREE_TYPE']['VM'],Xphp::$_user['useruuid']));
        $protectsql = 'select count(distinct vml.machine_id) as protectNum from bd_task bt,vm_machine_list vml, vm_vcenter vv, vm_tree vt where bt.task_uuid = vml.task_uuid and  vml.vcenter_uuid = vv.vcenter_uuid and vml.vm_uuid = vt.uuid and vml.vcenter_uuid = vt.vcenter_uuid and bt.task_type =? and bt.module_type = ? and bt.sub_module_type = ? and vv.user_uuid  = ?;';
        $protectCount = $this->dbSelect($protectsql, array(Xphp::$_config['TASKTYPE']['BACKUP'],Xphp::$_config['MODULE_TYPE']['VM'],Xphp::$_config['VM_SUB_MODULE']['PUBLIC_CLOUD'],Xphp::$_user['useruuid']));
        $info = array(
            'protectNum' => $protectCount[0]['protectNum'],
            'totalNum' => $data[0]['total']
        );
        return $info;
    }

    // -----------------------------------深信服首页新增接口开始---------------------------
    /**
     * 备份计划--从当前任务中取的
     * @param unknown $params
     */
    public function getBackupPlan($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $params['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $jobHandler = Xphp::instance('JobHandler');
        //任务名	模块类型	任务类型	创建时间	状态	速度	创建者	操作
        $sql = "select ssb.task_progress, bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time, 
                        bt.task_status, bt.strategy_id, bu.user_name,
                		bri.total_object_size, bri.total_object_completed_size, bri.speed, bri.speed_time,
                        bri.total_object_valid_size,bri.total_object_completed_valid_size
                from bd_running_info bri, bd_user bu, bd_task bt 
                     left join sr_sure_backup ssb on bt.task_uuid = ssb.task_uuid 
                where bt.task_uuid = bri.task_uuid and 
                	 bt.user_uuid = bu.user_uuid and 
                     bt.delete_flag = ? ";
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu 
                    where  bt.delete_flag = ? and bt.user_uuid = bu.user_uuid ";
        
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET']);
        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            $sql .= "and bu.user_uuid = ? ";
            $sqlCount .= "and bu.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        }
        $search = $params['search'];
        // $taskType = intval($search['taskType']);
        $taskType = $search['taskType'] ? explode(',', trim($search['taskType'])) : ''; // 任务类型 支持以英文逗号隔开的多个\
        $taskStatus = $search['taskStatus'] ? explode(',', trim($search['taskStatus'])) : ''; // 任务状态 支持以英文逗号隔开的多个
        //任务类型
        if(!empty($taskType)){
            $taskTypeListDes = implode(',', $taskType);
            $sql .= " and bt.task_type in (".$taskTypeListDes.") ";
            $sqlCount .= " and bt.task_type in (".$taskTypeListDes.") ";
        }
        //任务状态
        if(!empty($taskStatus)){
            $taskStatusListDes = implode(',', $taskStatus);
            $sql .= " and bt.task_status =  (".$taskStatusListDes.") ";
            $sqlCount .= " and bt.task_status =  (".$taskStatusListDes.") ";
        }
        $sql .= " order by bt.create_time desc limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        
        if(empty($data)){
            $data = [];
        }
        $records["data"] = array();
        foreach ($data as $d){
            $taskType = intval($d['task_type']);
            $progress = $jobHandler->getCurrentTaskProgress($d);
            $records["data"][] = array(
                $d['task_name'],
                $d['task_status'],
                $progress,
                $jobHandler->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $this->getTaskNameString($d['module_type'],$taskType),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
            );
        }
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return  json_encode($records);
    }

    /**
     * 获取资源池名字
     */
    public function getStorageName(){
        $name = '';
        $sql = "select use_mode,storage_uuid, storage_nickname from  bd_storage_resource where use_mode in 
        (". Xphp::$_config['BD_STORAGE_USE_MODE']['UNKNOWN'] . "," . Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'] . ",". Xphp::$_config['BD_STORAGE_USE_MODE']['COPY'] . "," . Xphp::$_config['BD_STORAGE_USE_MODE']['ARCHIVE'] . ") 
        and storage_type not in (8) and status = ? and mount_flag = ? 
        and error_code = ? and lan_free_flag = ?";
        $data = $this->dbSelect($sql,array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET']));
        if(!empty($data)) {
            foreach($data as $d) {
                $name .= '<option mode="'. $d["use_mode"] .'" value="'. $d["storage_uuid"] .'">'. $d["storage_nickname"] .'</option>';
            }
        } else {
            //深信服单独使用此方法 不用提语言包
            $name = '<option value="" use_mode="">'. Xphp::$_lang['WEB_HOMEPAGE_NO_DATA'] .'</option>'; 
        }
        return json_encode($name);
    }

     /**
     * 存储资源池
     * @param unknown $params
     */
    public function getStorageInfo($params){
        //TODO
        $storageuuid = $params["storageuuid"];
        $useMode = $params["use_mode"];
        $utils = Xphp::instance('Utils');
        $storageInfo = $this->pGetStorageInfo($useMode, $storageuuid);
        $info = array(
            'totalCapacity' => $this->pGetIntToSizeInfo($storageInfo['total'], $utils),
            'usedCapacity' => $this->pGetIntToSizeInfo($storageInfo['used'], $utils),
            'freeCapacity'=> $this->pGetIntToSizeInfo($storageInfo['free'], $utils),
        );
        return json_encode($info);
    }
    
    /**
     * 告警统计
     * @param unknown $params
     */
    public function getAlarmData(){
        //任务告警
        $sqlTaskAlarm = "select count(task_alarm_id) as total from bd_task_alarm where user_uuid = ? and alarm_level = ? and solved_flag = 2";
        //警告  level 2
        $countTaskAbnormal = $this->dbSelect($sqlTaskAlarm, array(Xphp::$_user['useruuid'], 2));
        //错误  level 3
        $countTaskFail = $this->dbSelect($sqlTaskAlarm, array(Xphp::$_user['useruuid'], 3));
        //系统告警
        $sqlSysAlarm = "select count(system_alarm_id) as total from bd_system_alarm where system_alarm_id is not null and alarm_level = ? and solved_flag = 2";
        //警告  level 2
        $countSysAbnormal = $this->dbSelect($sqlSysAlarm, array(2));
        //错误  level 3
        $countSysFail = $this->dbSelect($sqlSysAlarm, array(3));
        $info = array(
            "warnNum" => $countTaskAbnormal[0]['total'] + $countSysAbnormal[0]['total'],  //异常个数
            "errorNum" => $countTaskFail[0]['total'] + $countSysFail[0]['total'],  //失败个数
        );
        
        return json_encode($info);
    }

    /**
     * 初始化数据
     * @param unknown $params
     * node_uuid
		time_range
		range_start_time
		range_end_time
		cpu_alarm
		ram_alarm 
		root_alarm
     */
    public function initDataFunc($params){
        //获取节点信息
        $node_uuid = $params['node_uuid'];
        //初始化数据
        $info = array(
            'cpuMsg' => array(
                //每条线的名称
                'name' => array(),
                //x轴时间
                'x_time' => array(),
                //y轴百分比
                'y_percent' => array(),
            ),
            'ramMsg' => array(
                //内存名字
                'name' => array(Xphp::$_lang['UI_SYSTEM_MONITOR_RAM_PERCENTAGE']),
                //x轴时间
                'x_time' => array(),
                //y轴百分比
                'y_percent' => array(),
            ),
            'loadMsg' => array(
                //每条线的名称
                'name' => array('Load 1min','Load 5min','Load 15min'),
                //x轴时间
                'x_time' => array(),
                //y轴百分比
                'y_val' => array(
                    'Load 1min' => array(),
                    'Load 5min' => array(),
                    'Load 15min' => array(),
                ),
            ),
            'netWorkMsg' => array(
                'name' => array(),
                'x_time' => array(),
                'y_val' => array(),
            ),
            'bpsMsg' => array(
                'name' => array(),
                'x_time' => array(),
                'y_val' => array(),
            ),
            'iopsMsg' => array(
                'name' => array(),
                'x_time' => array(),
                'y_val' => array(),
            ),
        );
        
        //动态刷新
        $result = $this->getDataOfMove($params);
        
        //名字只存一次
        $cpu_name_flag = false;
        $network_name_flag = false;
        $bps_name_flag = false;
        $iops_name_flag = false;
        
        foreach ($result as $each){
            //获取CPU,BPS,IOPS,NETWORK详情
            $details = json_decode($each['details'],true);
            //-------------处理CPU相关数据-------
            //             $info['cpuMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['cpuMsg']['x_time'][] = date("H:i:s", strtotime($each['monitor_time']));
            
            //得到CPU详情
            $cpuDetails = $details['cpuMsg'];
            foreach ($cpuDetails as $cpuEach){
                $name_cpu = $cpuEach['name'].Xphp::$_lang['UI_SYSTEM_MONITOR_PERCENTAGE_SYSTEM_CHART'];
                if(!$cpu_name_flag){
                    $info['cpuMsg']['name'][] = $name_cpu;
                }
                $info['cpuMsg']['y_percent'][$name_cpu][] = $cpuEach['usedPercent'];
            }
            $cpu_name_flag = true;
            //-------------处理内存相关数据-------
            //             $info['ramMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['ramMsg']['x_time'][] = date("H:i:s", strtotime($each['monitor_time']));
            $info['ramMsg']['y_percent'][] = $each['ram_percentage'];
            //-------------处理系统负载相关数据-------
            //             $info['loadMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['loadMsg']['x_time'][] = date("H:i:s", strtotime($each['monitor_time']));
            $info['loadMsg']['y_val']['Load 1min'][] = $each['system_load_1'];
            $info['loadMsg']['y_val']['Load 5min'][] = $each['system_load_5'];
            $info['loadMsg']['y_val']['Load 15min'][] = $each['system_load_15'];
            //-------------处理网络流量相关数据-------
            //             $info['netWorkMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['netWorkMsg']['x_time'][] = date("H:i:s", strtotime($each['monitor_time']));
            //得到CPU详情
            $networkDetails = $details['networkMsg'];
            foreach ($networkDetails as $networkEach){
                $name_in = $networkEach['name']." in";
                $name_out = $networkEach['name']." out";
                if(!$network_name_flag){
                    $info['netWorkMsg']['name'][] = $name_in;
                    $info['netWorkMsg']['name'][] = $name_out;
                }
                $info['netWorkMsg']['y_val'][$name_in][] = $networkEach['receive_avg'];
                $info['netWorkMsg']['y_val'][$name_out][] = $networkEach['transmit_avg'];
            }
            $network_name_flag = true;
            //-------------处理BPS相关数据-------
            //             $info['bpsMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['bpsMsg']['x_time'][] = date("H:i:s", strtotime($each['monitor_time']));
            //得到BPS详情
            $bpsDetails = $details['bpsMsg'];
            foreach ($bpsDetails as $bpsEach){
                //判断是否以dm开头的存储 如果是则跳过
                $namebps = $bpsEach['name'];
                if(preg_match("/^dm*/", $namebps)){
                    continue;
                };
                $name_write = $bpsEach['name']." write";
                $name_read = $bpsEach['name']." read";
                if(!$bps_name_flag){
                    $info['bpsMsg']['name'][] = $name_write;
                    $info['bpsMsg']['name'][] = $name_read;
                }
                $info['bpsMsg']['y_val'][$name_write][] = $bpsEach['write_avg'];
                $info['bpsMsg']['y_val'][$name_read][] = $bpsEach['read_avg'];
            }
            $bps_name_flag = true;
            //-------------处理IOPS相关数据-------
            //             $info['iopsMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['iopsMsg']['x_time'][] = date("H:i:s", strtotime($each['monitor_time']));
            //得到BPS详情
            $iopsDetails = $details['iopsMsg'];
            foreach ($iopsDetails as $iopsEach){
                //判断是否以dm开头的存储 如果是则跳过
                $nameiops = $iopsEach['name'];
                if(preg_match("/^dm*/", $nameiops)){
                    continue;
                };
                $name_write = $iopsEach['name']." write";
                $name_read = $iopsEach['name']." read";
                if(!$iops_name_flag){
                    $info['iopsMsg']['name'][] = $name_write;
                    $info['iopsMsg']['name'][] = $name_read;
                }
                $info['iopsMsg']['y_val'][$name_write][] = $iopsEach['write_avg'];
                $info['iopsMsg']['y_val'][$name_read][] = $iopsEach['read_avg'];
            }
            $iops_name_flag = true;
            
        }
        $info = $this->checkData(reset($result), end($result), $info);
        return json_encode($info);
    }
        /**
     * 验证返回的数据的正确性 一般只有少数情况下需要对数据进行处理
     * @param unknown $startInfo 取的所有数据的第一个元素
     * @param unknown $endInfo 取的所有数据的最后一个元素
     * @param unknown $info 最后组装成的数据
     */
    private function checkData($startInfo,$endInfo,$info){
        $startInfo = json_decode($startInfo['details'],true);
        $endInfo = json_decode($endInfo['details'],true);
        $networkFlag = false;
        $bpsFlag = false;
        $iopsFlag = false;
        //---------判断各自数组个数是否与y轴个数相同------
        //** 网络流量 **
        //判断网络流量
        $netWorkMsg = $info['netWorkMsg'];
        //获取x轴长度
        $xNetWorkCount = count($netWorkMsg['x_time']);
        //获取y轴长度
        foreach ($netWorkMsg['y_val'] as $each){
            if($xNetWorkCount != count($each)){
                $networkFlag = true;
            }
        }
        //** 读写bps **
        //bps
        $bpsMsg = $info['bpsMsg'];
        //获取x轴长度
        $xBpsCount = count($bpsMsg['x_time']);
        //获取y轴长度
        foreach ($bpsMsg['y_val'] as $each){
            if($xBpsCount != count($each)){
                $bpsFlag = true;
            }
        }
        //** 读写iops **
        //iops
        $iopsMsg = $info['iopsMsg'];
        //获取x轴长度
        $xIopsCount = count($iopsMsg['x_time']);
        //获取y轴长度
        foreach ($iopsMsg['y_val'] as $each){
            if($xIopsCount != count($each)){
                $iopsFlag = true;
            }
        }
        
        //判断是否一致
        if(!$networkFlag && !$bpsFlag && !$iopsFlag){
            return $info;
        }
        //如果数据长度不一致 则判断是在前面加0 还是后面加0
        if($networkFlag){
            //开始数目
            $networkStart = $startInfo['networkMsg'];
            $countStartNetwork = count($networkStart) * 2;
            //最后一个数目
            $networkEnd = $endInfo['networkMsg'];
            $countEndNetwork = count($networkEnd) * 2;
            //如果开始大于结尾的则在末尾填0
            if($countStartNetwork > $countEndNetwork){
                foreach ($netWorkMsg['y_val'] as $key=>$value){
                    if($xNetWorkCount != count($value)){
                        $info['netWorkMsg']['y_val'][$key] = array_pad($value,$xNetWorkCount,0);
                        if(!in_array($key, $info['netWorkMsg']['name'])){
                            $info['netWorkMsg']['name'][] = $key;
                        }
                    }
                }
            }else if($countStartNetwork < $countEndNetwork){ //在前面+0
                foreach ($netWorkMsg['y_val'] as $key=>$value){
                    if($xNetWorkCount != count($value)){
                        $info['netWorkMsg']['y_val'][$key] = array_pad($value,$xNetWorkCount*-1,0);
                        if(!in_array($key, $info['netWorkMsg']['name'])){
                            $info['netWorkMsg']['name'][] = $key;
                        }
                    }
                }
            }
        }
        //---
        if($bpsFlag){
            //开始数目
            $bpsStart = $startInfo['bpsMsg'];
            $countStartBps = count($bpsStart) * 2;
            //最后一个数目
            $bpsEnd = $endInfo['bpsMsg'];
            $countEndBps = count($bpsEnd) * 2;
            //如果开始大于结尾的则在末尾填0
            if($countStartBps > $countEndBps){
                foreach ($bpsMsg['y_val'] as $key=>$value){
                    if($xBpsCount != count($value)){
                        $info['bpsMsg']['y_val'][$key] = array_pad($value,$xBpsCount,0);
                        if(!in_array($key, $info['bpsMsg']['name'])){
                            $info['bpsMsg']['name'][] = $key;
                        }
                    }
                }
            }else if($countStartBps < $countEndBps){ //在前面+0
                foreach ($bpsMsg['y_val'] as $key=>$value){
                    if($xBpsCount != count($value)){
                        $info['bpsMsg']['y_val'][$key] = array_pad($value,$xBpsCount*-1,0);
                        if(!in_array($key, $info['bpsMsg']['name'])){
                            $info['bpsMsg']['name'][] = $key;
                        }
                    }
                }
            }
        }
        //---
        if($iopsFlag){
            //开始数目
            $iopsStart = $startInfo['iopsMsg'];
            $countStartIops = count($iopsStart) * 2;
            //最后一个数目
            $iopsEnd = $endInfo['iopsMsg'];
            $countEndIops = count($iopsEnd) * 2;
            //如果开始大于结尾的则在末尾填0
            if($countStartIops > $countEndIops){
                foreach ($iopsMsg['y_val'] as $key=>$value){
                    if($xIopsCount != count($value)){
                        $info['iopsMsg']['y_val'][$key] = array_pad($value,$xIopsCount,0);
                        if(!in_array($key, $info['iopsMsg']['name'])){
                            $info['iopsMsg']['name'][] = $key;
                        }
                    }
                }
            }else if($countStartIops < $countEndIops){ //在前面+0
                foreach ($iopsMsg['y_val'] as $key=>$value){
                    if($xIopsCount != count($value)){
                        $info['iopsMsg']['y_val'][$key] = array_pad($value,$xIopsCount*-1,0);
                        if(!in_array($key, $info['iopsMsg']['name'])){
                            $info['iopsMsg']['name'][] = $key;
                        }
                    }
                }
            }
        }
        return $info;
    }
    public function getDataOfMove($params){
        //获取节点信息
        $node_uuid = $params['node_uuid'];
        $sql = "select monitor_time, cpu_percentage, ram_percentage, system_load_1, system_load_5, system_load_15, details
                from bd_system_monitor";
        //获取时间固定为最近半小时的量 每张表理论上平均900条
        $date_start= date ("Y-m-d H:i:s",strtotime ( "-10 minute" ));;
        $date_end= date("Y-m-d H:i:s");
        $count = $params['count'];
        $sql .= " where monitor_time between ? and ? and node_uuid = ? order by monitor_time desc limit 0,?";
        $result = $this->dbSelect($sql,array($date_start,$date_end,$node_uuid,$count));
        return $result;
    }
    // -----------------------------------深信服首页新增接口结束---------------------------

    /**
     * 获取K8s信息
     * @return string[]
     */
    private function getProtectK8sInfo($utils)
    {
        $module = Xphp::$_config['MODULE_TYPE']['KUBERNETES'];
        //hadoop集群
        $K8sInfo = $this->getK8sInfo($module);
        $info = array(
            'protectedNum' => $K8sInfo['total'],
            'backupDataSize' =>  $this->pGetProtectData($module, $utils),
            'taskNum' => $this->pGetTaskNum($module),
            'backupPointNum' => $this->pGetBackuppointNum($module),
            'onlineNum' => $K8sInfo['online'],
            'offlineNum' => $K8sInfo['offline'],
        );
        return $info;
    }

    /**
     * 获取k8s集群信息
     * @param $module 模块类型
     * @return number[]
     */
    private function getK8sInfo($module)
    {
        $sql = "select online_flag from kube_cluster where user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        // 受保护的k8s集群
        $sqlProtect ="select count(distinct kt.cluster_uuid) as total from bd_task bt,kube_task kt where bt.task_uuid = kt.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $dataProtect = $this->dbSelect($sqlProtect, array(
                Xphp::$_config['FLAG']['UNSET'],
                $module,
                Xphp::$_config['TASKTYPE']['KUBE_BACKUP'],
                Xphp::$_user['useruuid'])
        );
        $online = 0;
        $offline = 0;
        foreach ($data as $d) {
            if (intval($d['online_flag']) == Xphp::$_config['FLAG']['SET']) {
                $online++;
            } else {
                $offline++;
            }
        }
        $info = array(
            'online' => $online,
            'offline' => $offline,
            'total' => $dataProtect[0]['total']
        );
        return $info;
    }

    /**
     * 获取数据库复制信息
     * @return string[]
     */
    private function getProtectDbcopyInfo()
    {
        $module = Xphp::$_config['MODULE_TYPE']['DB_CDP'];
        //数据库复制主机信息
        $dbcopyInfo = $this->getDbcopyInfo($module);
        $info = array(
            'protectedNum' => $dbcopyInfo['protectedNum'],
            'taskNum' => $this->pGetTaskNum($module),
            'taskRunNum' => $this->pGetTaskRunNum($module),
            'hostOnline' => $dbcopyInfo['hostOnline'],
            'hostOffline' => $dbcopyInfo['hostOffline'],
            'bakOnline' => $dbcopyInfo['bakOnline'],
            'bakOffline' => $dbcopyInfo['bakOffline'],
        );
        return $info;
    }

    /**
     * 获取数据库复制信息
     * @param $module 模块类型
     * @return number[]
     */
    private function getDbcopyInfo($module)
    {
        //主机在线离线数
        $sql = "SELECT
                    online_flag
                FROM
                    bd_agent ba 
                WHERE
                    ba.user_uuid = ? and ba.agent_uuid IN ( SELECT DISTINCT baa.agent_uuid FROM bd_agent_app baa ) ";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $hostOnline = 0;
        $hostOffline = 0;
        foreach ($data as $d) {
            if (intval($d['online_flag']) == Xphp::$_config['FLAG']['SET']) {
                $hostOnline++;
            } else {
                $hostOffline++;
            }
        }
        // 备份主机在线离线数
        $sqlProtect = "SELECT
                            COALESCE(SUM(count), 0) AS count 
                        FROM
                            (
                            SELECT
                                CDDT.target_agent_uuid,
                            CASE
                                    WHEN BAA.cluster_flag = 1 THEN
                                    ( SELECT COUNT(*) FROM bd_agent_app BAA2 WHERE BAA2.cluster_uuid = BAA.cluster_uuid ) 
                                    WHEN BAA.cluster_flag IS NOT NULL 
                                    AND BAA.cluster_flag != 1 THEN
                                        1 ELSE 0 
                                        END AS count 
                                FROM
                                    cdp_db_dr_task CDDT
                                    INNER JOIN bd_agent_app BAA ON CDDT.target_agent_uuid = BAA.agent_uuid
                                    INNER JOIN bd_agent BA ON CDDT.target_agent_uuid = BA.agent_uuid 
                                WHERE
                                BA.online_flag = ? 
                            ) subquery;";
        $bakOnline = $this->dbSelect($sqlProtect, array(Xphp::$_config['FLAG']['UNSET']));
        $bakOffline = $this->dbSelect($sqlProtect, array(Xphp::$_config['FLAG']['SET']));
        // 受保护主机：正在运行中的数据库复制任务源端客户端个数（需统计集群下所有节点）
        $sqlProtectedNum = "SELECT
                                COALESCE(SUM(count), 0) AS count 
                            FROM
                                (
                                SELECT
                                    BT.agent_uuid,
                                CASE
                                        WHEN BAA.cluster_flag = 1 THEN
                                        ( SELECT COUNT(*) FROM bd_agent_app WHERE cluster_uuid = BAA.cluster_uuid ) 
                                        WHEN BAA.cluster_flag != 1 THEN
                                        1 ELSE 0 
                                    END AS count 
                                FROM
                                    bd_task BT
                                    INNER JOIN bd_agent_app BAA ON BT.agent_uuid = BAA.agent_uuid 
                                WHERE
                                    BT.module_type = ? 
                                    AND BT.task_type = ? 
                                AND BT.task_status = ?
                                ) AS subquery;";
        $protectedNum = $this->dbSelect($sqlProtectedNum, array(
            $module,
            Xphp::$_config['TASKTYPE']['DB_CDP_SYN'],
            Xphp::$_config['TASKSTATUS']['RUNNING']
        ));
        $info = array(
            'hostOnline' => $hostOnline,
            'hostOffline' => $hostOffline,
            'bakOnline' => $bakOnline[0]['count'],
            'bakOffline' => $bakOffline[0]['count'],
            'protectedNum' => $protectedNum[0]['sqlProtect']
        );
        return $info;
    }

    /**
     * 获取文件复制信息
     * @return string[]
     */
    private function getProtectedFilecopyInfo($utils)
    {
        $module = Xphp::$_config['MODULE_TYPE']['FILE_COPY'];
        $filecopyInfo = $this->getFilecopyInfo($module);
        $info = array(
            'protectedNum' => $filecopyInfo['protectedNum'],
            'taskNum' => $this->pGetTaskNum($module),
            'taskRunNum' => $this->pGetTaskRunNum($module),
            'onlineNum' => $filecopyInfo['online'],
            'offlineNum' => $filecopyInfo['offline'],
        );
        return $info;
    }

    /**
     * 文件复制信息
     * @param $module 模块类型
     * @return number[]
     */
    private function getFilecopyInfo($module)
    {
        //主机
        $hostInfo = $this->getHostInfo();
        //nas设备
        $nasInfo = $this->getNasInfo();
        //对象存储
        // $obsInfo = $this->getObsInfo();
        // //hadoop集群
        // $hadoopInfo = $this->getHadoopInfo();
        $online = $hostInfo['online'] + $nasInfo['online'];
        $offline = $hostInfo['offline'] + $nasInfo['offline'];
        // 受保护的文件复制对象数量
        $sqlProtect ="select count(distinct stpl.source_uuid) as total from bd_task bt,sync_task_path_list stpl where bt.task_uuid = stpl.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $dataProtect = $this->dbSelect($sqlProtect, array(
                Xphp::$_config['FLAG']['UNSET'],
                $module,
                Xphp::$_config['TASKTYPE']['FILE_COPY'],
                Xphp::$_user['useruuid'])
        );
       
        $info = array(
            'online' => $online,
            'offline' => $offline,
            'protectedNum' => $dataProtect[0]['total']
        );
        return $info;
    }
}

?>