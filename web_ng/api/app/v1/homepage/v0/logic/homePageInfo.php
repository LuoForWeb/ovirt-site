<?php

namespace app\v1\homepage\v0\logic;
use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Index as ResourceHandler;
use app\v1\system\v0\logic\Time;
use app\v1\system\v0\logic\Settings;


/**
 * note          首页
 * @author       wuxian@vinchin.com
 * @date         2025/04/27 11:26
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class homePageInfo extends Base
{
    /**
     * 数据概览
     * @param unknown $params
     */
    public function getDataCenterView()
    {
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        $userUuid = $userInfo['userUuid'];
        $sqlCountParams = array($flag['UNSET']);
        //当前任务个数---------------------------------------------------------------------------------------
        $sqlCount = "select count(bt.id) as total from bd_task bt, bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid where  bt.delete_flag = ? and bt.user_uuid = bu.user_uuid";
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sqlCount .= " and bu.user_uuid = ?";
            $sqlCountParams = array_merge($sqlCountParams, array($userUuid));
        }
        
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlCount .= " and mut.tenant_uuid IS NULL";
        }
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        // 历史任务个数----------------------------------------------------------------------------------------
        $sqlCount = "select count(distinct bht.id) as total from bd_history_task bht left join mt_user_tenant mut on bht.user_uuid = mut.user_uuid";
        $sqlCountParams = array();
        $sqlContact = ' where';
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sqlCount .= $sqlContact . " bht.user_uuid = ?";
            $sqlCountParams = array_merge($sqlCountParams, array($userUuid));
            $sqlContact = ' and';
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlCount .= $sqlContact . " mut.tenant_uuid IS NULL";
        }
        $hisCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $systemRunningTimeInfo = array(
            "running_time" => date('Y/m/d H:i:s', (new Time())->getSystemTime()),
            "running_day" => $this->getRunningDays(),
        );
        $info = array(
            "system_running_time_info" => $systemRunningTimeInfo,
            "accumulate_data" => $this->getAccumulatedData(),
            "current_task_num" => $count[0]['total'],
            "history_task_num" => $hisCount[0]['total'],
        );
        return $info;
    }

     /**
     * 获取任务各个状态个数概览
     */
    public function getTaskStatusView(){
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $sqlCount = "select count(bt.id) as total from bd_task bt where  
        bt.delete_flag = ? and bt.task_status = ?";
        // 运行中
        $sqlCountRun = array($flag['UNSET'], $taskStatus['RUNNING']);
        // 等待中
        $sqlCountWait = array($flag['UNSET'], $taskStatus['WAITTING']);
        //停止
        $sqlCountStop = array($flag['UNSET'], $taskStatus['STOPPED']);
        //不是admin或者全局观察者 
        if ($_SESSION['userLevel'] != 1 && !in_array('global_observer', $_SESSION['permission'])) {
            $sqlCount .= " and bt.user_uuid = ?";
            $sqlCountRun = array_merge($sqlCountRun, array($userInfo['userUuid']));
            $sqlCountWait = array_merge($sqlCountWait, array($userInfo['userUuid']));
            $sqlCountStop = array_merge($sqlCountStop, array($userInfo['userUuid']));
        }
        $countRun = $this->dbSelect($sqlCount, $sqlCountRun);
        $countWait = $this->dbSelect($sqlCount, $sqlCountWait);
        $countStop = $this->dbSelect($sqlCount, $sqlCountStop);
        //---------------------------------------------------------------------------------------------------------------------------
        // 成功 异常  失败 从bd_task_alarm中取
        $sqlAlarm = "select count(task_alarm_id) as total from bd_task_alarm where alarm_level = ? and solved_flag = ?";
        //成功从bd_history_task中取
        $sqlhis = "select count(id) as total from bd_history_task where error_code = ?";
        //成功(界面暂时去掉了显示)
        $sqlCountSuccess = array($userInfo['userUuid'], 0);
        // $countSuccess = $this->dbSelect($sqlhis, $sqlCountSuccess);
        //不是admin或者全局观察者
        if ($_SESSION['userLevel'] != 1 && !in_array('global_observer', $_SESSION['permission'])) {
            $sqlAlarm .= " and user_uuid = ?"; 
            //异常  level 2
            $countAbnormal = $this->dbSelect($sqlAlarm, array($userInfo['userUuid'], 2,2));
            //失败  level 3
            $countFail = $this->dbSelect($sqlAlarm, array($userInfo['userUuid'], 3,2));
        } else {
            //异常  level 2
            $countAbnormal = $this->dbSelect($sqlAlarm, array(2,2));
            //失败  level 3
            $countFail = $this->dbSelect($sqlAlarm, array(3,2));
        }
        $info = array(
            "running_num" => $countRun[0]['total'],  //运行中任务个数
            "waiting_num" => $countWait[0]['total'],  //等待中任务个数
            "stop_num" => $countStop[0]['total'],  //停止任务个数
            "abnormal_num" => $countAbnormal[0]['total'],  //异常任务个数
            "fail_num" => $countFail[0]['total'],  //失败任务个数
        );
        return $info;
    }

    /**
     * 存储统计
     * @param unknown $params
     */
    public function getSystemStorageData(){
        $userInfo = xphp_get_user_info();
        $useModeStr = '('. xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['BACKUP'] . ',' . xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['COPY'] . ',' . xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['BACKUP'] .')';
        $sql = "select sum(total_size) as total_size, sum(free_size) as free_size, count(storage_uuid) as total_num from  bd_storage_resource where use_mode in " . $useModeStr ." ";
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $resourceHandler = new ResourceHandler();
            $uuid = $resourceHandler->pGetUserAllResource($userInfo['userUuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']);
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
                    'storage_num' => 0,
                    'total_capacity' => $nullSize,
                    'used_capacity' => $nullSize,
                    'remaining_capacity' => $nullSize, 
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
            'storage_num' => intval($data[0]['total_num']),
            'total_capacity' => $this->getIntToSizeInfo($total),
            'used_capacity' => $this->getIntToSizeInfo($used),
            'remaining_capacity' => $this->getIntToSizeInfo($free),
        );
        // $result['allStorageInfo'] = $info;
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
        if (!$permissionFlag) {
            $data = [];
        }
        foreach ($data as $d){
            $dateTime = intval($d['date']) - 3600*24;
            $sizeInfo = $this->getIntToSizeInfo(intval($d['archive_write_size']) + intval($d['copy_write_size']));
            $info[] = array(
                'date' => date("m/d", $dateTime),
                'size' => $sizeInfo['size'],
                'value' => $sizeInfo['value'],
                'unit' => $sizeInfo['unit'],
                'des' => $sizeInfo['des']
            );
        }
        $info[] = $this->getTodayStorage();
        return $info;
    }

    /**
     * 获取今天备份的数据量
     * @return string[]|NULL[]|number[]
     */
    private function getTodayStorage(){
        $today = date('Y-m-d');
        $sql = "select sum(total_object_write_size) as write_size from bd_history_task where finish_time like '%". $today . "%'  and module_type = ? ";
        //副本
        $sqlCopy = $sql . " and task_type in(17,26,30,38,44)";
        $dataCopy = $this->dbSelect($sqlCopy, array(xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT']));
        //归档
        $sqlArchive = $sql . " and task_type in(19)";
        $dataArchive = $this->dbSelect($sqlArchive, array(xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT']));
        $sizeInfo = $this->getIntToSizeInfo(intval($dataCopy[0]['write_size']) + intval($dataArchive[0]['write_size']));
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
     * 获取系统授权状态
     * @param unknown $params
     */
    public function getSystemAuthStatus()
    {
        //系统授权情况
        $status = (new Settings())->getSystemAuthorizationStatus();//1系统已授权  2系统未授权
        return $status;
    }

    /**
     * 得到系统运行天数
     */
    private function getRunningDays()
    {
        $sql = "select system_run_time from bd_system";
        $data = $this->dbSelect($sql);
        $value = round($data[0]['system_run_time'], 1);
        $date = intval($value / 24);
        $dateDes = $date . xphp_get_lang('WEB_UTILS_DAY');
        if ($date >= 365) {
            $year = intval($date / 365);
            $days = $date % 365;
            $dateDes = $year . xphp_get_lang('WEB_UTILS_YEAR') . $days . xphp_get_lang('WEB_UTILS_DAY');
        }
        return $dateDes;
    }

    /**
     * 得到累计备份数据
     */
    private function getAccumulatedData()
    {
        $userUuid = xphp_get_user_info()['userUuid'];
        $sql = "select vmware_data, xs_data, xen_data, kvm_data, hyperv_data, fs_data, db_data, os_data, copy_data, 
       archive_data, vol_cdp_data, nas_data,m365_data, obs_data, hadoop_data, kube_data,db_cdp_data,full_machine_data,
       fs_copy_data, disk_cdp_data, vol_cdp_copy_data, disk_cdp_copy_data from bd_user_extension ";
        $sqlParams = array();
        //如果是admin系统超级管理员或全局观察者
        if (v1_auth_need_check_look()){
            //操作员只显示自己的数据
            $sql .= "where user_uuid = ? ";
            $sqlParams = array($userUuid);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $total = 0;
        foreach ($data as $d) {
            $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] +
                $d['kvm_data'] + $d['hyperv_data'] + $d['fs_data'] +
                $d['db_data'] + $d['os_data'] + $d['copy_data'] +
                $d['archive_data'] + $d['vol_cdp_data'] + $d['nas_data'] +
                $d['m365_data'] + $d['obs_data'] + $d['hadoop_data'] + $d['kube_data'] +
                $d['db_cdp_data'] + $d['full_machine_data'] + $d['fs_copy_data'] + $d['disk_cdp_data'] + $d['vol_cdp_copy_data'] + $d['disk_cdp_copy_data'];
        }

        $info = v1_calsize_to_value_and_unit($total, true);
        return $info;
    }

    /**
     * 获取页面授权
     * @return number[]
     */
    public function getPageAuth()
    {
        $info = array();
        $sql = "SELECT extension FROM bd_license";
        $data = dbSelect($sql);
        $extension = [];
        if (json_decode(v1_decrypt($data[0]['extension']), true) != null) {
            $extension = json_decode(v1_decrypt($data[0]['extension']), true);
        }
        $extension['p'] = $extension['p'] ?? [];
        $info = array(
            //备份
            'vmprotect' => in_array('vmprotect', $extension['p']) ? true : false,//虚拟机
            'prcloud_protect' => in_array('prcloud_protect', $extension['p']) ? true : false,//私有云
            'awsprotect' => in_array('awsprotect', $extension['p']) ? true : false,//公有云
            'complete_machine' => in_array('complete_machine', $extension['p']) ? true : false,//定时整机
            'osbackup' => in_array('osbackup', $extension['p']) ? true : false,//定时卷
            'filebackup' => in_array('filebackup', $extension['p']) ? true : false,//文件
            'nas_protect' => in_array('nas_protect', $extension['p']) ? true : false,//nas
            'obs_protect' => in_array('obs_protect', $extension['p']) ? true : false,//obs
            'hadoop_protect' => in_array('hadoop_protect', $extension['p']) ? true : false,//hadoop
            'db_protect' => in_array('db_protect', $extension['p']) ? true : false,//数据库
            'office365_protect' => in_array('office365_protect', $extension['p']) ? true : false,//Microsoft 365
            'k8s_protect' => in_array('k8s_protect', $extension['p']) ? true : false,//容器
            'cbrbackup' => in_array('cbrbackup', $extension['p']) ? true : false,//云存储同步
            //实时保护
            'complete_cdp_backup' => in_array('complete_cdp_backup', $extension['p']) ? true : false,//整机实时
            'vol_cdp_backup' => in_array('vol_cdp_backup', $extension['p']) ? true : false,//卷实时
            //复制
            'machine_copy' => in_array('machine_copy', $extension['p']) ? true : false, //整机复制
            'vol_cdp_copy' => in_array('vol_cdp_copy', $extension['p']) ? true : false, //卷复制
            'file_copy_protect' => in_array('file_copy_protect', $extension['p']) ? true : false, //文件复制
            'dbcdpcopy' => in_array('dbcdpcopy', $extension['p']) ? true : false, //数据库复制
            //--------------------用于测试和调试各种授权场合-----------------------------------------------
            // 'prcloud_protect' => false,
            // 'awsprotect' => false,
            // 'complete_machine' => false,
            // 'osbackup' => false,
            // 'filebackup' => false,
            // 'nas_protect' => false,
            // 'obs_protect' => false,
            // 'hadoop_protect' => false,
            // 'db_protect' => false,
            // 'office365_protect' => false,
            // 'k8s_protect' => false,
            // 'cbrbackup' => false,
            // 'complete_cdp_backup' => false,
            // 'vol_cdp_backup' => false,
            // 'machine_copy' => false,
            // 'vol_cdp_copy' => false,
            // 'file_copy_protect' => false,
            // 'dbcdpcopy' => false,
        );
        // $_SESSION['system_auth'] = 2;
        return $info;
    }
    /**
     * 受保护设备个数
     * @param $module 模块类型
     * @return number[]
     */
    public function getProtectedDevice($params)
    {
        $userUuid = $params['userUuid'] ?? xphp_get_user_info()['userUuid'];
        $showFlagInfo = $this->getPageAuth();
        $info = array(
            'cloud' => array(
                'show' => !$showFlagInfo['vmprotect'] && !$showFlagInfo['prcloud_protect'] && !$showFlagInfo['awsprotect'] && !$showFlagInfo['k8s_protect']
                    ? false : true,//几个小模块都不显示，cloud大模块才返回false
                'module' => array(
                    'vmprotect' => $this->getProtectVmInfo(
                        $showFlagInfo['vmprotect'],
                        xphp_get_config('module', 'VM_SUB_MODULE')['VM'],
                        $userUuid
                    ),
                    'prcloud_protect' => $this->getProtectVmInfo(
                        $showFlagInfo['prcloud_protect'],
                        xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD'],
                        $userUuid
                    ),
                    'awsprotect' => $this->getProtectVmInfo(
                        $showFlagInfo['awsprotect'],
                        xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD'],
                        $userUuid
                    ),
                    'k8s_protect' => $this->getK8sNums(
                        $showFlagInfo['k8s_protect'],
                        xphp_get_config('module', 'MODULE_TYPE')['KUBERNETES'],
                        $userUuid
                    ),
                ),
            ),
            //---------------------------------------------------------------------
            'file' => array(
                'show' => !$showFlagInfo['filebackup'] && !$showFlagInfo['nas_protect'] && !$showFlagInfo['obs_protect'] && !$showFlagInfo['hadoop_protect']
                    ? false : true,//几个小模块都不显示，file大模块才返回false
                'module' => array(
                    'filebackup' => $this->pGetProtectHost(
                        $showFlagInfo['filebackup'],
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                        xphp_get_config('module', 'SUBMODULE_TYPE')['FS'],
                        $userUuid
                    ),
                    'nas_protect' => $this->pGetProtectHost(
                        $showFlagInfo['nas_protect'],
                        xphp_get_config('module', 'MODULE_TYPE')['NAS'],
                        xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                        '',
                        $userUuid
                    ),
                    'obs_protect' => $this->pGetProtectHost(
                        $showFlagInfo['obs_protect'],
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                        xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'],
                        $userUuid
                    ),
                    'hadoop_protect' => $this->pGetProtectHost(
                        $showFlagInfo['hadoop_protect'],
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                        xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'],
                        $userUuid
                    ),
                ),
            ),
            //----------------------------------------------------------------------
            'machine' => array(
                'show' => !$showFlagInfo['complete_machine'] && !$showFlagInfo['osbackup']
                    ? false : true,//几个小模块都不显示，machine大模块才返回false
                'module' => array(
                    'complete_machine' => $this->pGetProtectHost(//定时整机
                        $showFlagInfo['complete_machine'],
                        xphp_get_config('module', 'MODULE_TYPE')['OS'],
                        xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'],
                        xphp_get_config('module', 'OS_SUBMODULE_TYPE')['MACHINE_OS'],
                        $userUuid
                    ),
                    'osbackup' => $this->pGetProtectHost(//旧操作系统(定时卷)
                        $showFlagInfo['osbackup'],
                        xphp_get_config('module', 'MODULE_TYPE')['OS'],
                        xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'],
                        0,// xphp_get_config('module', 'OS_SUBMODULE_TYPE')['OS'],卷的子模块类型是0
                        $userUuid
                    ),
                ),
            ),
            //----------------------------------------------------------------------
            'application' => array(//应用
                'show' => !$showFlagInfo['db_protect'] && !$showFlagInfo['office365_protect']
                    ? false : true,//几个小模块都不显示，application大模块才返回false
                'module' => array(
                    'db_protect' => $this->pGetProtectHost(
                        $showFlagInfo['db_protect'],
                        xphp_get_config('module', 'MODULE_TYPE')['DB'],
                        xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'],
                        '',
                        $userUuid
                    ),
                    'office365_protect' => $this->getOrganizationInfo(
                        $showFlagInfo['office365_protect'],
                        $userUuid
                    ),
                ),
            ),
            //----------------------------------------------------------------------
            'vol_cdp_protect' => array(//实时保护
                'show' => !$showFlagInfo['complete_cdp_backup'] && !$showFlagInfo['vol_cdp_backup']
                    ? false : true,//几个小模块都不显示，vol_cdp_protect大模块才返回false
                'module' => array(
                    'complete_cdp_backup' => $this->getCdpInfo(
                        $showFlagInfo['complete_cdp_backup'],
                        xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'],
                        2,//dev_type
                        xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'],
                        $userUuid
                    ),
                    'vol_cdp_backup' => $this->getCdpInfo(
                        $showFlagInfo['vol_cdp_backup'],
                        xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'],
                        1,//dev_type
                        xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'],
                        $userUuid
                    ),
                ),
            ),
            //----------------------------------------------------------------------
            'copy' => array(//复制
                'show' => !$showFlagInfo['machine_copy'] && !$showFlagInfo['vol_cdp_copy'] && !$showFlagInfo['file_copy_protect'] && !$showFlagInfo['dbcdpcopy']
                    ? false : true,//几个小模块都不显示，copy大模块才返回false
                'module' => array(
                    'machine_copy' => $this->getCdpInfo(//整机复制
                        $showFlagInfo['machine_copy'],
                        xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'],
                        2,//dev_type
                        xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'],
                        $userUuid
                    ),
                    'vol_cdp_copy' => $this->getCdpInfo(
                        $showFlagInfo['vol_cdp_copy'],
                        xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'],
                        1,//dev_type
                        xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'],
                        $userUuid
                    ),
                    'file_copy' => $this->getFilecopyInfo(//文件复制
                        $showFlagInfo['file_copy_protect'],
                        xphp_get_config('module', 'MODULE_TYPE')['FILE_COPY'],
                        xphp_get_config('task', 'TASKTYPE')['FILE_COPY'],
                        $userUuid
                    ),
                    'dbcdpcopy' => $this->getDbCopyInfo(//数据库复制
                        $showFlagInfo['dbcdpcopy'],
                        xphp_get_config('module', 'MODULE_TYPE')['DB_CDP'],
                        xphp_get_config('task', 'TASKTYPE')['CDP_DB_BACKUP'],
                        xphp_get_config('task', 'TASKSTATUS')['RUNNING'],
                        $userUuid
                    ),
                ),
            ),

        );
        return $info;
    }

    /**
     * 受保护虚拟机/公有云/私有云信息
     * @param boolean $showFlag 模块是否授权显示
     * @param unknown $sub_module_type 虚拟化的子模块类型
     * @param $userUuid 用户uuid
     * @return array
     */
    public function getProtectVmInfo($showFlag, $sub_module_type = '', $userUuid)
    {
        $info = array();
        $userInfo = xphp_get_user_info();
        $sqlParams = array(xphp_get_config('task', 'TASKTYPE')['BACKUP'], $sub_module_type);
        $sql = "select count(vml.machine_id) as protect_vms from vm_machine_list vml, bd_task bt, vm_vcenter vv left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid 
                        where bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = vv.vcenter_uuid and bt.task_type = ? and bt.sub_module_type in (?)
                        and vml.vm_uuid in (select vm_uuid from vm_machine)";
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sql .= " and vv.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array(
            'show' => $showFlag,
            'protected' => intval($data[0]['protect_vms']),
            'total' => $this->getVmNums($sub_module_type, $userUuid),
        );
        return $info;
    }

    /**
     * 虚拟机/公有云/私有云总数
     * @return number
     */
    private function getVmNums($sub_module_type = '', $userUuid)
    {
        $userInfo = xphp_get_user_info();
        $hyperversionType = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        $notIn = implode(',', array_merge($hyperversionType['openstack'], $hyperversionType['publiccloud']));
        $openstask = implode(',', $hyperversionType['openstack']);
        $publiccloud = implode(',', $hyperversionType['publiccloud']);
        $sqlParams = array(xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'], xphp_get_config('vm')['VM_TREE_TYPE']['VM']);
        $sql = 'select count(vt.tree_id) as total from vm_tree vt, vm_vcenter vv left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid  where
        vt.display_mode = ? and vt.type = ? and vt.vcenter_uuid = vv.vcenter_uuid';
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sql .= " and vv.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        switch (intval($sub_module_type)) {
            case xphp_get_config('module', 'VM_SUB_MODULE')['VM']:
                $sql .= " and vv.hypervisor_type not in ({$notIn})";
                break;
            case xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD']:
                $sql .= " and vv.hypervisor_type in ({$openstask})";
                break;
            case xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']:
                $sql .= " and vv.hypervisor_type in ({$publiccloud})";
                break;
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return intval($data[0]['total']);
    }

    /**
     * 获取受保护k8s集群个数
     * @param boolean $showFlag 模块是否授权显示
     * @param $module 模块类型
     * @param $userUuid 用户uuid
     * @return number[]
     */
    public function getK8sNums($showFlag, $module, $userUuid)
    {
        $info = array();
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        $sqlParams = array($flag['UNSET'], $module, xphp_get_config('task')['TASKTYPE']['KUBE_BACKUP']);
        //受保护集群
        $sqlProtect = "select count(distinct kt.cluster_uuid) as total from kube_task kt, bd_task bt left join mt_user_tenant mut on bt.user_uuid = mut.user_uuid where bt.task_uuid = kt.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sqlProtect .= " and bt.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlProtect .= " and mut.tenant_uuid IS NULL";
        }
        $dataProtect = $this->dbSelect($sqlProtect, $sqlParams);
        //集群总数
        $sql = "select count(kc.cluster_uuid) as total from kube_cluster kc left join mt_user_tenant mut on kc.user_uuid = mut.user_uuid ";
        $sqlParams = array();
        $sqlContact = " where ";
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sql .= $sqlContact . " kc.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
            $sqlContact = " and";
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= $sqlContact . " mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array(
            'show' => $showFlag,
            'protected' => $dataProtect[0]['total'],
            'total' => $data[0]['total'],
        );
        return $info;
    }

    /**
     * 获取受保护主机信息
     * @param boolean $showFlag 模块是否授权显示
     * @param int $module 模块类型
     * @param int $taskType 任务类型
     * @param int $submodule 子模块类型
     * @param $userUuid 用户uuid
     * @return array
     */
    public function pGetProtectHost($showFlag, $module, $taskType, $submodule = null, $userUuid)
    {
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        $sqlProtect = "select count(distinct btal.agent_uuid) as total from bd_task_agent_list btal, bd_task bt left join mt_user_tenant mut on bt.user_uuid = mut.user_uuid 
        where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        $paramsProtect = array($flag['UNSET'], $module, $taskType);
        if (!empty($submodule) || (xphp_get_config('module', 'MODULE_TYPE')['OS'] && $submodule == 0)) {
            $sqlProtect .= ' and bt.sub_module_type = ?';
            $paramsProtect = array_merge($paramsProtect, array($submodule));
        }
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sqlProtect .= " and bt.user_uuid = ?";
            $paramsProtect = array_merge($paramsProtect, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlProtect .= " and mut.tenant_uuid IS NULL";
        }
        $dataProtect = $this->dbSelect($sqlProtect, $paramsProtect);
        $protected = intval($dataProtect[0]['total']);
        $total = $this->getHostNums($userUuid);
        switch ($module) {
            case xphp_get_config('module')['MODULE_TYPE']['FS']:
                switch ($submodule) {
                    case xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']:
                        $total = $this->getObsNums($userUuid);
                        break;
                    case xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']:
                        $total = $this->getHadoopNums($userUuid);
                        break;
                }
                break;
            case xphp_get_config('module')['MODULE_TYPE']['NAS']:
                $total = $this->getNasNums($userUuid);
                $protected = $this->getProtectNas();
                break;

        }
        $info = array(
            'show' => $showFlag,
            'protected' => $protected,
            'total' => $total,
        );
        return $info;
    }

    /**
     * 获取受保护的NAS设备
     * @return number
     */
    private function getProtectNas()
    {
        $userInfo = xphp_get_user_info();
        $sqlParams = array(xphp_get_config('app')['FLAG']['UNSET'], xphp_get_config('module')['MODULE_TYPE']['NAS'], xphp_get_config('module', 'SUBMODULE_TYPE')['NAS'], xphp_get_config('task')['TASKTYPE']['BACKUP']);
        $sql = "select count(distinct nt.nas_uuid) as total from nas_task nt, bd_task bt left join mt_user_tenant mut on bt.user_uuid = mut.user_uuid
        where bt.task_uuid = nt.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.sub_module_type = ? and bt.task_type = ?";
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sql .= " and bt.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userInfo['userUuid']));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return intval($data[0]['total']);
    }

    /**
     * 获取主机总数
     * @return []
     */
    private function getHostNums($userUuid)
    {
        $userInfo = xphp_get_user_info();
        $sqlParams = array();
        $sql = "select count(distinct ba.agent_uuid) as total from bd_agent ba left join mt_user_tenant mut on ba.user_uuid = mut.user_uuid where ba.agent_type not in (3, 4, 5)";
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceHandler();
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

    /**
     * 获取NAS设备总数
     * @return []
     */
    private function getNasNums($userUuid)
    {
        $userInfo = xphp_get_user_info();
        $sql = "select nsr.nas_uuid from nas_storage_resource nsr left join mt_user_tenant mut on nsr.user_uuid = mut.user_uuid ";
        $sqlContact = " where ";
        if (v1_auth_need_check_look()) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceHandler();
            $uuid = $resourceHandler->pGetUserAllResource($userUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['NAS']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $nasUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= $sqlContact . " nsr.nas_uuid in ($nasUuidsIn)";
                $sqlContact = " and";
            }
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= $sqlContact . " mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql);
        $total = count($data);
        return $total;
    }

    /**
     * 获取hadoop集群总数
     * @return []
     */
    private function getHadoopNums($userUuid)
    {
        $userInfo = xphp_get_user_info();
        $sql = "select count(distinct hc.hadoop_cluster_uuid) as total from hadoop_cluster hc left join mt_user_tenant mut on hc.user_uuid = mut.user_uuid";
        $sqlContact = " where ";
        if (v1_auth_need_check_look()) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceHandler();
            $uuid = $resourceHandler->pGetUserAllResource($userUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['HADOOP']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $hadoopUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= $sqlContact . " hc.hadoop_cluster_uuid in ($hadoopUuidsIn)";
                $sqlContact = " and";
            }
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= $sqlContact . " mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql);
        return $data[0]['total'];
    }

    /**
     * 获取对象存储总数
     * @return []
     */
    private function getObsNums($userUuid)
    {
        $userInfo = xphp_get_user_info();
        $sql = "select count(distinct obs.obs_uuid) as total from obs_resource obs left join mt_user_tenant mut on obs.user_uuid = mut.user_uuid ";
        $sqlContact = " where ";
        if (v1_auth_need_check_look()) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceHandler();
            $uuid = $resourceHandler->pGetUserAllResource($userUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['OBS']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $obsUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= $sqlContact . " obs.obs_uuid in ($obsUuidsIn)";
                $sqlContact = " and";
            }
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= $sqlContact . " mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql);
        return $data[0]['total'];
    }

    /**
     * 获取组织信息
     * @return []
     */
    public function getOrganizationInfo($showFlag, $userUuid)
    {
        $userInfo = xphp_get_user_info();
        $sql = "select count(distinct mo.organization_uuid) as total from m365_organization mo left join mt_user_tenant mut on mo.user_uuid = mut.user_uuid";
        $sqlContact = " where ";
        if (v1_auth_need_check_look()) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceHandler();
            $uuid = $resourceHandler->pGetUserAllResource($userUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['M365_EXCHANGE']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                 // 没得 返回空
                 $info = array(
                    'show' => $showFlag,
                    'protected' => 0,
                    'total' => 0,
                );
                return $info;
            } else {
                // $uuidArr 是一个一维数组
                $organizationUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= $sqlContact . "mo.organization_uuid in ($organizationUuidsIn)";
                $sqlContact = " and";
            }
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= $sqlContact . " mut.tenant_uuid IS NULL";
        }
        $data = $this->dbSelect($sql);
        //受保护的组织
        $sqlProtect = "select count(distinct mol.organization_uuid) as total from m365_object_list mol, bd_task bt left join mt_user_tenant mut on bt.user_uuid = mut.user_uuid where bt.task_uuid = mol.task_uuid and bt.task_type = ?";
        if (v1_auth_need_check_look()) {//userLevel是1 2 3就全部显示
            $resourceHandler = new ResourceHandler();
            $uuid = $resourceHandler->pGetUserAllResource($userUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['M365_EXCHANGE']);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                $info = array(
                    'show' => $showFlag,
                    'protected' => 0,
                    'total' => 0,
                );
                return $info;
            } else {
                // $uuidArr 是一个一维数组
                $organizationUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sqlProtect .= " and mol.organization_uuid in ($organizationUuidsIn)";
            }
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlProtect .= " and mut.tenant_uuid IS NULL";
        }
        $dataProtect = $this->dbSelect($sqlProtect, array(xphp_get_config('task', 'TASKTYPE')['BACKUP']));
        $info = array(
            'show' => $showFlag,
            'protected' => intval($dataProtect[0]['total']),
            'total' => $data[0]['total'],
        );
        return $info;
    }

    /**
     * 获取卷实时信息
     * @param boolean $showFlag 模块是否授权显示
     * @param int $module 模块类型
     * @param int $devType 1卷  2整机
     * @param int $taskType 任务类型
     * @param $userUuid 用户uuid
     * @return array
     */
    public function getCdpInfo($showFlag, $module, $devType, $taskType, $userUuid)
    {
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        $sqlProtect = "SELECT count(bt.task_uuid) as total from bd_task bt left join cdp_vol_task cvt
        on bt.task_uuid = cvt.task_uuid left join mt_user_tenant mut on bt.user_uuid = mut.user_uuid where cvt.dev_type = ? and bt.delete_flag = ? and bt.task_type = ? and bt.module_type = ?";
        $sqlParams = array($devType, $flag['UNSET'], $taskType, $module);
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sqlProtect .= " and bt.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlProtect .= " and mut.tenant_uuid IS NULL";
        }
        $dataProtect = $this->dbSelect($sqlProtect, $sqlParams);
        $info = array(
            'show' => $showFlag,
            'protected' => intval($dataProtect[0]['total']),
            'total' => $this->getHostNums($userUuid),
        );
        return $info;
    }

    /**
     * 文件复制信息
     *@param boolean $showFlag 模块是否授权显示
     * @param int $taskType 任务类型
     * @param $userUuid 用户uuid
     * @return []
     */
    public function getFilecopyInfo($showFlag, $module, $taskType, $userUuid)
    {
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        $sqlProtect2 = '';
        $sqlParams = array();
        // userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sqlProtect2 .= " AND bt.user_uuid = ?";
            $sqlParams = array($userUuid, $userUuid);
        }
        // 不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlProtect2 .= " AND mut.tenant_uuid IS NULL ";
        }
        // 受保护的文件复制对象数量
        $sqlProtectSrc = "SELECT
                            COUNT( DISTINCT uuid ) AS total 
                        FROM
                            (
                            SELECT
                                source_uuid AS uuid 
                            FROM
                                sync_task_path_list stpl
                                JOIN bd_task bt ON bt.task_uuid = stpl.task_uuid
                                LEFT JOIN mt_user_tenant mut ON bt.user_uuid = mut.user_uuid 
                            WHERE
                                bt.delete_flag = {$flag['UNSET']} 
                                AND bt.module_type = {$module} 
                                AND bt.task_type = {$taskType} 
                                AND stpl.source_type != 2 " .  $sqlProtect2 ;
        $sqlProtectNassrc = "UNION
                            SELECT DISTINCT ip AS uuid FROM nas_storage_resource WHERE nas_uuid IN 
                            (SELECT source_uuid AS nas_uuid FROM sync_task_path_list stpl JOIN bd_task bt ON bt.task_uuid = stpl.task_uuid
                            LEFT JOIN mt_user_tenant mut ON bt.user_uuid = mut.user_uuid WHERE stpl.source_type = 2 " .  $sqlProtect2.") 
                            ) AS combined_uuids;";
        $dataProtect = $this->dbSelect($sqlProtectSrc . $sqlProtectNassrc, $sqlParams);
        $info = array(
            'show' => $showFlag,
            'protected' => intval($dataProtect[0]['total']),
            'total' => $this->getHostNums($userUuid) + $this->getNasNums($userUuid),
            // + $this->getObsNums($userUuid) + $this->getHadoopNums($userUuid)暂时不算obs和hadoop
        );
        return $info;
    }

    /**
     * 数据库复制信息
     *@param boolean $showFlag 模块是否授权显示
     * @param int $taskType 任务类型
     * @param $userUuid 用户uuid
     * @return []
     */
    public function getDbCopyInfo($showFlag, $module, $taskType, $taskStatus, $userUuid)
    {
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        // 受保护主机：正在运行中的数据库复制任务源端客户端个数（需统计集群下所有节点）
        $sqlProtect = "SELECT COALESCE(SUM(
                            CASE
                                WHEN BAA.cluster_uuid = '' THEN 1 
                                WHEN BAA.cluster_flag = 1 THEN
                                    (SELECT COUNT(*) FROM bd_agent_app WHERE cluster_uuid = BAA.cluster_uuid) 
                                ELSE 0 
                            END), 0) AS count 
                        FROM
                            bd_task BT
                            INNER JOIN bd_agent_app BAA ON BT.agent_uuid = BAA.agent_uuid 
                            LEFT JOIN mt_user_tenant MUT ON BT.user_uuid = MUT.user_uuid 
                        WHERE
                            BT.module_type = ? 
                            AND BT.task_type = ? ";
        $sqlParams = array($module, $taskType);
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sqlProtect .= " AND BT.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlProtect .= " and MUT.tenant_uuid IS NULL";
        }
        $dataProtect = $this->dbSelect($sqlProtect, $sqlParams);
        $info = array(
            'show' => $showFlag,
            'protected' => intval($dataProtect[0]['count']),
            'total' => $this->getHostNums($userUuid),
        );
        return $info;
    }

    /**
     * 获取保护数据总量 - 从历史任务获取
     * @param int $moduleType  模块类型
     * @param int $submodule 子模块类型
     * @param string $userUuid 用户uuid
     */
    private function getProtectData($moduleType, $submodule = null, $userUuid, $taskType = '')
    {
        $info = array();
        $flag = xphp_get_config('app')['FLAG'];
        $userInfo = xphp_get_user_info();
        $sql = "SELECT  DATE_FORMAT(bht.finish_time, '%m-%d') AS day, SUM(bht.total_object_valid_size) AS write_size
                FROM bd_history_task bht left join mt_user_tenant mut on bht.user_uuid = mut.user_uuid ";
        $sqlLeft = "";
        $sqlAnd = "";
        $sqlWhere = "";

        $taskType = empty($taskType) ? xphp_get_config('task', 'TASKTYPE')['BACKUP'] : $taskType;
        switch ($moduleType) {
            case xphp_get_config('module')['MODULE_TYPE']['DB']:
                $taskType = xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'];
                break;
            case xphp_get_config('module')['MODULE_TYPE']['OS']:
                $taskType = xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'];
                $sqlAnd .= " and bht.submodule_type = {$submodule} ";
                break;
            case xphp_get_config('module')['MODULE_TYPE']['FILE_COPY']://文件复制
                $taskType = xphp_get_config('task', 'TASKTYPE')['FILE_COPY'];
                break;
            case xphp_get_config('module')['MODULE_TYPE']['VOL_CDP']://整机和卷复制只是dev_type不同
                $sqlLeft .= " left join cdp_vol_task cvt on bht.task_uuid = cvt.task_uuid";
                // dev_type 1卷  2整机
                $sqlAnd .= " and cvt.dev_type = {$submodule}";
                break;
            case xphp_get_config('module')['MODULE_TYPE']['DB_CDP']://数据库复制
                $taskStatus = xphp_get_config('task', 'TASKSTATUS')['RUNNING'];
                $sqlLeft .= " left join bd_task bt on bht.task_uuid = bt.task_uuid";
                $sqlAnd .= " and bt.task_status = {$taskStatus}";
                break;
            case xphp_get_config('module')['MODULE_TYPE']['KUBERNETES']:
                $taskType = xphp_get_config('task', 'TASKTYPE')['KUBE_BACKUP'];
                break;
            case xphp_get_config('module')['MODULE_TYPE']['VM']:
                $hyperversionType = xphp_get_config('vm', 'VMHYPERVISORGROUP');
                $notIn = implode(',', array_merge($hyperversionType['openstack'], $hyperversionType['publiccloud']));
                $openstask = implode(',', $hyperversionType['openstack']);
                $publiccloud = implode(',', $hyperversionType['publiccloud']);
                switch ($submodule) {
                    case xphp_get_config('module', 'VM_SUB_MODULE')['VM']:
                        $sqlAnd .= " and bht.submodule_type not in ({$notIn})";
                        break;
                    case xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD']:
                        $sqlAnd .= " and bht.submodule_type in ({$openstask})";
                        break;
                    case xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD']:
                        $sqlAnd .= " and bht.submodule_type in ({$publiccloud})";
                        break;
                }
                break;
        }
        $sqlWhere .= " where bht.finish_time >= DATE_SUB(CURDATE(), INTERVAL 10 DAY) and bht.module_type = ? and bht.task_type = ?";
        $sqlParams = array($moduleType, $taskType);
        if (!empty($submodule) && $moduleType != xphp_get_config('module')['MODULE_TYPE']['VOL_CDP']
        && $moduleType != xphp_get_config('module')['MODULE_TYPE']['OS']
        && $moduleType != xphp_get_config('module')['MODULE_TYPE']['VM']) {
            $sqlAnd .= " and bht.submodule_type =? ";
            $sqlParams = array_merge($sqlParams, array($submodule));
        }
        //userLevel是1 2 3就全部显示
        if (v1_auth_need_check_look()) {
            $sqlAnd .= " and bht.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sqlAnd .= " and mut.tenant_uuid IS NULL";
        }
        $sqlEnd = " GROUP BY DATE(bht.finish_time) ORDER BY day";
        $data = $this->dbSelect($sql . $sqlLeft . $sqlWhere . $sqlAnd . $sqlEnd, $sqlParams);
        // 获取最近10天的日期（月-日格式）
        $last10Days = [];
        for ($i = 0; $i < 10; $i++) {
            $date = new \DateTime();
            $date->modify("-$i days");
            $last10Days[] = $date->format('m-d');
        }
        // 提取现有日期
        $existingDays = array_column($data, 'day');

        // 补全缺失的日期
        foreach ($last10Days as $day) {
            if (!in_array($day, $existingDays)) {
                $data[] = ["day" => $day, "write_size" => "0"];
            }
        }
        foreach ($data as $d) {
            $sizeInfo = $this->getIntToSizeInfo($d['write_size']);
            $info[] = array(
                'date' => $d['day'],
                'size' => $sizeInfo['size'],
                'value' => $sizeInfo['value'],
                'unit' => $sizeInfo['unit'],
                'des' => $sizeInfo['des']
            );

        }
        return $info;
    }

    /**
     * 自定义的四个大模块分类 - 获取保护数据总量 - 从历史任务获取
     * @param int $moduleType  模块类型
     * @param string $userUuid 用户uuid
     */
    private function getTotalProtectData($moduleType, $userUuid, $moduleShow = '')
    {
        $info = array();
        $userInfo = xphp_get_user_info();
        $sql = "SELECT  DATE_FORMAT(bht.finish_time, '%m-%d') AS day, SUM(bht.total_object_valid_size) AS write_size
                FROM bd_history_task bht left join mt_user_tenant mut on bht.user_uuid = mut.user_uuid where bht.finish_time >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)";
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
        if (v1_auth_need_check_look()) {
            $sql .= " and bht.user_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($userUuid));
        }
        //不是租户，不显示租户数据
        if (empty($userInfo['tenantuuid'])) {
            $sql .= " and mut.tenant_uuid IS NULL";
        }
        $sql .= " and bht.module_type in ({$moduleType}) and bht.task_type in ({$taskType})
                GROUP BY DATE(bht.finish_time) ORDER BY day";
        $data = $this->dbSelect($sql, $sqlParams);
        // 获取最近10天的日期（月-日格式）
        $last10Days = [];
        for ($i = 0; $i < 10; $i++) {
            $date = new \DateTime();
            $date->modify("-$i days");
            $last10Days[] = $date->format('m-d');
        }
        // 提取现有日期
        $existingDays = array_column($data, 'day');

        // 补全缺失的日期
        foreach ($last10Days as $day) {
            if (!in_array($day, $existingDays)) {
                $data[] = ["day" => $day, "write_size" => "0"];
            }
        }
        foreach ($data as $d) {
            $sizeInfo = $this->getIntToSizeInfo($d['write_size']);
            $info[] = array(
                'date' => $d['day'],
                'size' => $sizeInfo['size'],
                'value' => $sizeInfo['value'],
                'unit' => $sizeInfo['unit'],
                'des' => $sizeInfo['des'],
            );

        }
        return $info;
    }

    /**
     * 获取大小转换成界面显示信息
     * @param unknown $size
     */
    private function getIntToSizeInfo($size)
    {
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
     * 备份数据增长趋势
     * @param unknown $size
     */
    public function getBackupData($params = array())
    {
        $user_uuid = $params['user_uuid'];
        $userUuid = !empty($user_uuid) ? $user_uuid : xphp_get_user_info()['userUuid'];
        //虚拟机数据
        $vmData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('module', 'VM_SUB_MODULE')['VM'], $userUuid);
        //私有云数据
        $privateData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD'], $userUuid);
        //公有云数据
        $publicData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('module', 'VM_SUB_MODULE')['PUBLIC_CLOUD'], $userUuid);
        //k8s数据
        $k8sData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['KUBERNETES'], '', $userUuid);
        //文件数据
        $fileData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module', 'SUBMODULE_TYPE')['FS'], $userUuid);
        //nas数据
        $nasData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['NAS'], '', $userUuid);
        //对象存储数据
        $obsData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'], $userUuid);
        //hadoop数据
        $hadoopData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'], $userUuid);
        //整机数据
        $machineData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['OS'], 1, $userUuid);
        //卷数据
        $volData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['OS'], 0, $userUuid);
        //数据库数据
        $dbData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['DB'], '', $userUuid);
        //exchange数据
        $exchangeData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['M365'], '', $userUuid);
        //整机实时
        $complete_cdp_backup_data = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'], 2, $userUuid, xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']);
        //卷实时
        $vol_cdp_backup_data = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'], 1, $userUuid, xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']);
        //整机复制
        $machine_copy_data = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'], 2, $userUuid, xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION']);
        //卷复制
        $vol_cdp_copy_data = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'], 1, $userUuid, xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION']);
        //文件复制
        $file_copy_data = $this->getTotalProtectData('file_copy', $userUuid);
        //数据库复制
        $dbcdpcopy_data = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['DB_CDP'], '', $userUuid, xphp_get_config('task', 'TASKTYPE')['CDP_DB_BACKUP']);
        $info = array(
            'vmData' => $vmData,
            'privateData' => $privateData,
            'publicData' => $publicData,
            'k8sData' => $k8sData,
            'fileData' => $fileData,
            'nasData' => $nasData,
            'obsData' => $obsData,
            'hadoopData' => $hadoopData,
            'machineData' => $machineData,
            'volData' => $volData,
            'dbData' => $dbData,
            'exchangeData' => $exchangeData,
            //实时
            'complete_cdp_backup_data' => $complete_cdp_backup_data,
            'vol_cdp_backup_data' => $vol_cdp_backup_data,
            //复制
            'machine_copy_data' => $machine_copy_data,
            'vol_cdp_copy_data' => $vol_cdp_copy_data,
            'file_copy_data' => $file_copy_data,
            'dbcdpcopy_data' => $dbcdpcopy_data,
            //备份四个大模块
            'cloud' => $this->getTotalProtectData('cloud', $userUuid),
            'file' => $this->getTotalProtectData('file', $userUuid),
            'machine' => $this->getTotalProtectData('machine', $userUuid),
            'application' => $this->getTotalProtectData('application', $userUuid),
        );
        return $info;
    }
}