<?php

namespace app\v1\visualscreen\v0\logic;

use app\v1\common\logic\Base;
use app\v1\homepage\v0\logic\homePage;
use app\v1\job\v0\logic\JobController;
use app\v1\job\v0\logic\JobInfo;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage as StorageHandler;
use app\v1\system\v0\logic\Index;
use app\v1\system\v0\logic\Settings;
use app\v1\system\v0\logic\SystemMonitor;
use app\v1\system\v0\logic\Time;

class Visual extends Base
{
    /**
     * 获取概览
     */
    public function getOverView()
    {
        $info = array();
        //累计运行时长和单位
        $runningInfo = $this->getRunningTimeInfo();
        $info['running_time'] = $runningInfo['running_time'];
        $info['running_time_unit'] = $runningInfo['running_time_unit'];

        //累计保护数据和单位
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['UNKNOWN']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        //今日累计保护数据
        //备份数据:文件(1)，数据库，操作系统，虚拟机(1)，NAS(1)，实时容灾
        //副本数据:虚拟机，文件，操作系统，数据库
        //归档数据:虚拟机
        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        $taskTypeArr = array(
            $taskTypeConf['BACKUP'], $taskTypeConf['DB_BACKUP'], $taskTypeConf['OS_BACKUP'],
            $taskTypeConf['VOL_CDP_BACKUP'], $taskTypeConf['BACKUP_COPY'],
            $taskTypeConf['FILE_BACKUP_COPY'], $taskTypeConf['DB_BACKUP_COPY'], $taskTypeConf['OS_BACKUP_COPY'],
            $taskTypeConf['VOL_CDP_TAKEOVER'], $taskTypeConf['ARCHIVE']
        );
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        //节点状态
        $nodeAndStorageInfo = $this->getNodeAndStorageInfo();
        $info = array_merge($info, $nodeAndStorageInfo);

        //获取显示哪些模块图标
        $moduels = $this->getLicenseModuleInfo();
        $returnMoudles = array(
            'vm' => $moduels['vm'],
            'file' => $moduels['fs'],
            "database" => $moduels['db'],
            'nas' => $moduels['nas'],
            'os' => $moduels['os'],
            'cdp' => $moduels['volcdp'],
            'dbcdp' => $moduels['dbcdp'],
            'm365' => $moduels['office365'],
            'hadoop' => $moduels['hadoop'],
            'obs' => $moduels['obs']
        );
        $info['module_show'] = $returnMoudles;

        //模块状态,通过当前任务来判断
        $moduleTaskStatus = $this->getModuleTaskStatus($moduels);
        $info = array_merge($info, $moduleTaskStatus);
        return $info;
    }

    /**
     * 获取累计运行时间，单位为天
     */
    private function getRunningTimeInfo()
    {
        $info = array();
        $sql = "select system_run_time from bd_system";
        $data = $this->dbSelect($sql);
        $value = round($data[0]['system_run_time'], 1);
        $runTime = intval($value / 24);
        //累计运行时长和单位
        $info['running_time'] = $runTime;
        $info['running_time_unit'] = xphp_get_lang('WEB_UTILS_DAY');

        return $info;
    }

    /**
     * 获取累计保护数据，后期可能会增加类型
     * @param int $moduleType 0的时候获取所有数据，其他获取指定模块的数据
     * @return array('protect_data'=>"",'protect_data_unit'=>"")
     */
    private function getProtectDataInfo($moduleType,$subModuleType = null,$taskType = null )
    {
        $info = array();
        $sql = "select 
                    vmware_data, xs_data, xen_data, kvm_data, hyperv_data, 
                    fs_data, db_data, os_data, full_machine_data, copy_data, archive_data ,nas_data ,vol_cdp_data, m365_data, hadoop_data, obs_data,kube_data, fs_copy_data, db_cdp_data,disk_cdp_data,
                    vol_cdp_copy_data, disk_cdp_copy_data
                from 
                    bd_user_extension ";
        $data = $this->dbSelect($sql);
        $total = 0;
        $moduleTypeConf = xphp_get_config('module', 'MODULE_TYPE');
        foreach ($data as $d) {
            if ($moduleType == 0) {
                $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] + $d['kvm_data'] + $d['hyperv_data'] +
                    $d['fs_data'] + $d['db_data'] + $d['os_data'] + $d['copy_data'] + $d['archive_data'] +
                    $d['nas_data'] + $d['vol_cdp_data'] + $d['m365_data'] + $d['obs_data'] + $d['hadoop_data'];
            } elseif ($moduleTypeConf['VM'] == $moduleType) {
                //虚拟机
                $total += $d['vmware_data'] + $d['xs_data'] + $d['xen_data'] + $d['kvm_data'] + $d['hyperv_data'];
            } elseif ($moduleTypeConf['FS'] == $moduleType) {
                //文件
                $total += $d['fs_data'];
            } elseif ($moduleTypeConf['DB'] == $moduleType) {
                //数据库
                $total += $d['db_data'];
            } elseif ($moduleTypeConf['OS'] == $moduleType) {
                //操作系统
                if($subModuleType == xphp_get_config('module', 'OS_SUBMODULE_TYPE')['MACHINE_OS']){
                    $total += $d['full_machine_data'];
                }else if($subModuleType == 0){
                    $total += $d['os_data'];
                }

            } elseif ($moduleTypeConf['VOL_CDP'] == $moduleType) {
                if($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']){
                    if($subModuleType == xphp_get_config('module', 'OS_SUBMODULE_TYPE')['OS']){
                        //卷实时备份
                        $total += $d['vol_cdp_data'];
                    }else if($subModuleType == xphp_get_config('module', 'OS_SUBMODULE_TYPE')['MACHINE_OS']){
                        //整机实时备份
                        $total += $d['disk_cdp_data']; 
                    }
                }else if($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION']){
                    if($subModuleType == xphp_get_config('module', 'OS_SUBMODULE_TYPE')['OS']){
                        //卷复制
                        $total += $d['vol_cdp_copy_data'];
                    }else if($subModuleType == xphp_get_config('module', 'OS_SUBMODULE_TYPE')['MACHINE_OS']){
                        //整机复制
                        $total += $d['disk_cdp_copy_data']; 
                    }
                }
            } elseif ($moduleTypeConf['NAS'] == $moduleType) {
                //NAS
                $total += $d['nas_data'];
            } elseif ($moduleTypeConf['BACKUP_COPY_CLIENT'] == $moduleType) {
                //副本归档客户端
                $total += $d['copy_data'];
            } elseif ($moduleTypeConf['M365'] == $moduleType) {
                //m365
                $total += $d['m365_data'];
            } elseif($moduleTypeConf['HADOOP'] == $moduleType) {
                //hadoop
                $total += $d['hadoop_data'];
            } elseif($moduleTypeConf['OBS'] == $moduleType) {
                //obs
                $total += $d['obs_data'];
            } elseif($moduleTypeConf['KUBERNETES'] == $moduleType) {
                //k8s
                $total += $d['kube_data'];
            } else if($moduleTypeConf['FILE_COPY'] == $moduleType){
                //文件复制 先暂时用这个字段
                $total += $d['fs_copy_data'];
            } else if($moduleTypeConf['DB_CDP'] == $moduleType){
                //数据库复制
                 $total += $d['db_cdp_data'];
            }
        }
        $valueAndUnit = v1_calsize_to_value_and_unit($total, true);
        //累计保护数据和单位
        $info['protect_data'] = $valueAndUnit['value'];
        $info['protect_data_unit'] = $valueAndUnit['unit'];
        return $info;
    }

    /**
     * 获取指定类型任务的当天写入数据总大小
     * @param array $taskTypeArr 任务类型
     * @return array $valueAndUnit  大小和单位和真实值
     */
    private function getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr)
    {
        $today = date('Y-m-d');
        $sql = "select 
                    sum(total_object_write_size) as write_size 
                from 
                    bd_history_task 
                where 
                    finish_time like '%" . $today . "%'  and task_type in (?) ";
        $sqlParams = array_map(function ($value) {
            $value = sprintf('%d', $value);
            return $value;
        }, $taskTypeArr);
        $dataWriteSize = 0;
        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        foreach ($sqlParams as $d) {
            if (intval($d) == $taskTypeConf['VOL_CDP_BACKUP']) {
                $todayStart = date('Y-m-d') . ' 00:00:00';
                $todayEnd = date('Y-m-d') . ' 23:59:59';
                $sqlCdp = "select 
                                sum(data_flow) as size 
                           from 
                               cdp_vol_backup_agent_minute_level_data_flow 
                           where 
                               backup_start_timestamp >= ? and backup_end_timestamp < ?";
                $sqlParams = array($todayStart, $todayEnd);
                $data = $this->dbSelect($sqlCdp, $sqlParams);
                if (empty($data[0]['size'])) {
                    $data[0]['size'] = 0;
                }
                $dataWriteSize += $data[0]['size'];
            } else {
                $data = $this->dbSelect($sql, array($d));
                $dataWriteSize += $data[0]['write_size'];
            }
        }
        $sizeInfo = $this->pGetIntToSizeInfo(intval($dataWriteSize));
        $info = array(
            'date' => date('m/d'),
            'size' => $sizeInfo['size'],
            'value' => $sizeInfo['value'],
            'unit' => $sizeInfo['unit'],
            'des' => $sizeInfo['des']
        );
        return $info;
    }

    /**
     * 获取大小转换成界面显示信息
     * @param int $size
     */
    private function pGetIntToSizeInfo($size)
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
     * 获取节点和存储状态
     */
    private function getNodeAndStorageInfo()
    {
        $info = array();
        //获取节点状态：1为部分有问题为黄色，2为全部有问题为红色，0为正常
        //因为这里只判断是否是部分或全部有问题，所以使用分组来看
        // 节点的个数
        $sql = "select node_uuid from bd_node";
        $sqlData = $this->dbSelect($sql);
        $sqlCount = count($sqlData);

        $sql = "select 
                    bms.online_flag 
                from 
                    bd_module_server bms, bd_node bn 
                where 
                    bn.node_uuid = bms.node_uuid 
                and 
                    bms.online_flag = ? 
                group by bms.node_uuid;";
        $data = $this->dbSelect($sql, array(xphp_get_config('FLAG')['UNSET']));
        $abnormalStatus = count($data);

        if ($sqlCount == $abnormalStatus) {
            // 全部异常
            $nodeStatus = xphp_get_config('app')['THREE_STATUS']['all_abnormail'];
        } elseif ($sqlCount > $abnormalStatus && $abnormalStatus !== 0) {
            // 部分异常
            $nodeStatus = xphp_get_config('app')['THREE_STATUS']['some_abnormal'];
        } elseif ($abnormalStatus == 0) {
            // 无异常
            $nodeStatus = xphp_get_config('app')['THREE_STATUS']['normal'];
        }

        //获取存储状态，同时获取类型
        $sql = "select storage_type, status, mount_flag from bd_storage_resource where lan_free_flag = ?";
        $sqlParams = array(xphp_get_config('FLAG')['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $cloudStorageNum = 0;      //云存储个数
        $remoteStorageNum = 0;     //异地备份系统个数
        $allStatus = count($data);
        $online = 0;
        $offline = 0;
        foreach ($data as $d) {
            if (xphp_get_config('app')['BD_STORAGE_TYPE']['CLOUD'] == $d['storage_type']) {
                $cloudStorageNum++;
            }
            if (xphp_get_config('app')['BD_STORAGE_TYPE']['REMOTE'] == $d['storage_type']) {
                $remoteStorageNum++;
            }
            if (xphp_get_config('FLAG')['SET'] == $d['status']) {
                $online++;
            } else {
                $offline++;
            }
        }
        $storageStatus = $this->getThreeStatus($allStatus, $online, $offline);

        /**
         * 根据节点状态生成存储状态
         * 节点全部有问题2，存储和节点状态一致2
         * 节点部分有问题或全部正常，1或0， 存储状态取存储的状态
         */
        $threeConf = xphp_get_config('app')['THREE_STATUS'];
        if ($nodeStatus == $threeConf['all_abnormail']) {
            $storageStatus = $threeConf['all_abnormail'];
        }

        $info['node_status'] = $nodeStatus;
        $info['save_status'] = $storageStatus;
        $info['cloud_show'] = $cloudStorageNum > 0 ? true : false;
        $info['offsite_show'] = $remoteStorageNum > 0 ? true : false;

        return $info;
    }

    /**
     * 得到三态状态
     * @param int $totalNum     总计个数
     * @param int $normalNum    正常个数
     * @param int $abnormailNum 异常个数
     */
    private function getThreeStatus($totalNum, $normalNum, $abnormailNum)
    {
        $threeConf = xphp_get_config('app')['THREE_STATUS'];
        if (0 == $totalNum) {
            //总数为0，直接返回全部正常
            return $threeConf['normal'];
        }
        if ($totalNum == $normalNum) {
            //总数和正常个数一样，返回全部正常
            return $threeConf['normal'];
        }
        if ($totalNum == $abnormailNum) {
            //总数和异常个数一样，返回全部异常
            return $threeConf['all_abnormail'];
        }

        //其他情况，返回部分异常
        return $threeConf['some_abnormal'];
    }

    /**
     * 获取系统授权的模块
     * @return module['vm','fs','db','os','volcdp','nas','copy',]
     */
    private function getLicenseModuleInfo()
    {
        $sql = "select extension from bd_license ";
        $data = $this->dbSelect($sql);
        $extension = array();
        if (!empty($data)) {
            $extension = json_decode(v1_decrypt($data[0]['extension']), true);
        }
        $pagesArr = $extension['p'];
        $moduleFlagArr = array(
            'vm' => false,
            'fs' => false,
            'db' => false,
            'os' => false,
            'volcdp' => false,
            'nas' => false,
            'copy' => false,
            'dbcdp' => false,
            'office365' => false,
            'publicCloud' => false,
            'hadoop' => false,
            'obs' => false,
            'privateCloud' => false,
            'machine_os' => false,
            'cdp_machine_os' => false,
            'cdp_os' => false,
            'replicate_machine_os' => false,
            'replicate_os' => false,
            'replicate_file' => false,
            'replicate_db' => false,
        );
        if (in_array('vmprotect', $pagesArr)) {
            $moduleFlagArr['vm'] = true;
        }
        if (in_array('fileprotect', $pagesArr)) {
            $moduleFlagArr['fs'] = true;
        }
        if (in_array('db_protect', $pagesArr)) {
            $moduleFlagArr['db'] = true;
        }
        if (in_array('os_protect', $pagesArr)) {
            $moduleFlagArr['os'] = true;
        }
        if (in_array('vol_cdp_protect', $pagesArr)) {
            $moduleFlagArr['volcdp'] = true;
        }
        if (in_array('nas_protect', $pagesArr)) {
            $moduleFlagArr['nas'] = true;
        }
        if (in_array('dbprotect', $pagesArr)) {
            $moduleFlagArr['dbcdp'] = true;
        }
        if (in_array('copy', $extension['f'])) {
            $moduleFlagArr['copy'] = true;
        }
        if (in_array('office365_protect', $pagesArr)) {
            $moduleFlagArr['office365'] = true;
        }
        if (in_array('awsprotect', $pagesArr)) {
            $moduleFlagArr['publicCloud'] = true;
        }
        if (in_array('hadoop_protect', $pagesArr)){
            $moduleFlagArr['hadoop'] = true;
        }
        if (in_array('obs_protect', $pagesArr)){
            $moduleFlagArr['obs'] = true;
        }
        if (in_array('prcloud_protect', $pagesArr)){
            $moduleFlagArr['privateCloud'] = true;
        }
        if (in_array('os_protect', $pagesArr)) {
            $moduleFlagArr['machine_os'] = true;
        }
        // 连续数据保护
        if (in_array('os_protect', $pagesArr)) {
            $moduleFlagArr['cdp_machine_os'] = true;
        }
        if (in_array('os_protect', $pagesArr)) {
            $moduleFlagArr['cdp_os'] = true;
        }
        //数据复制 暂时都设置为true
        $moduleFlagArr['replicate_machine_os'] = true;
        $moduleFlagArr['replicate_os'] = true;
        $moduleFlagArr['replicate_file'] = true;
        $moduleFlagArr['replicate_db'] = true;
        return $moduleFlagArr;
    }

    /**
     * 获取每个模块任务的状态
     * @param unknown $modules 系统授权的模块
     */
    private function getModuleTaskStatus($modules)
    {
        $moduleTypeConf = xphp_get_config('module', 'MODULE_TYPE');
        $info = array();
        $info['vm_status'] = $this->getSomeModuleTaskStatus($modules['vm'], $moduleTypeConf['VM']);
        $info['file_status'] = $this->getSomeModuleTaskStatus($modules['fs'], $moduleTypeConf['FS']);
        $info['database_status'] = $this->getSomeModuleTaskStatus($modules['db'], $moduleTypeConf['DB']);
        $info['os_status'] = $this->getSomeModuleTaskStatus($modules['os'], $moduleTypeConf['OS']);
        $info['cdp_status'] = $this->getSomeModuleTaskStatus($modules['volcdp'], $moduleTypeConf['VOL_CDP']);
        $info['nas_status'] = $this->getSomeModuleTaskStatus($modules['nas'], $moduleTypeConf['NAS']);
        $info['hadoop_status'] = $this->getSomeModuleTaskStatus($modules['hadoop'], $moduleTypeConf['HADOOP']);
        $info['obs_status'] = $this->getSomeModuleTaskStatus($modules['obs'], $moduleTypeConf['OBS']);
        return $info;
    }

    /**
     * 获取指定模块的任务状态
     * @param boolean $moduleFlag 是否有这个模块
     * @param int     $moduleType 模块类型
     */
    private function getSomeModuleTaskStatus($moduleFlag, $moduleType)
    {
        $threeStatus = xphp_get_config('app')['THREE_STATUS'];
        if (!$moduleFlag) {
            //如果没有这个模块，直接返回正常
            return $threeStatus['normal'];
        }
        $sql = "select task_status from bd_task where module_type = ?";
        $data = $this->dbSelect($sql, array($moduleType));

        $total = 0;
        $normal = 0;
        $abnormal = 0;
        $taskStatusConf = xphp_get_config('task')['TASKSTATUS'];
        $abnormalTaskStatus = array(
            $taskStatusConf['NETWORK_FAULT'],   //network fault
            $taskStatusConf['ABNORMAL'],        //task compeleted but abnormal
            $taskStatusConf['ERROR'],           //task is error
        );
        foreach ($data as $d) {
            $total++;
            //如果任务状态是异常的：在异常任务状态列表中
            if (in_array($d['task_status'], $abnormalTaskStatus)) {
                $abnormal++;
            } else {
                $normal++;
            }
        }
        $status = $this->getThreeStatus($total, $normal, $abnormal);
        return $status;
    }

    /**
     * 系统配置
     */
    public function getConfigure()
    {
        $settingsHandler = new Settings();
        $visualConf = $settingsHandler->getSettingsInfos(xphp_get_config('app')['SETTINGS_CONF']['VISUAL']);
        $visualConf = json_decode($visualConf[0]['settings_content'], true);

        $config = $visualConf['config'];
        //专门判断是否是空是为了处理兼容性，如果是老版本升级上来的可能只有title,没有其他配置
        $info = array(
            'system_name' => empty($config['title']) ? '' : $config['title'],
            'cloud_name' => empty($config['cloudName']) ? '' : $config['cloudName'],
            'local_name' => empty($config['localName']) ? '' : $config['localName'],
            'offsite_name' => empty($config['offsiteName']) ? '' : $config['offsiteName'],
            'warning' => array(
                'task_show' => empty($config['taskAlertCheck']) ? false : $config['taskAlertCheck'],
                'system_show' => empty($config['systemAlertCheck']) ? false : $config['systemAlertCheck'],
            )
        );
        return $info;
    }

    /**
     * 数据统计，存储统计，存储详情
     */
    public function getStatisticData()
    {
        // $dataDetails = $this->getStatisticDataDetails();
        $dataDetails = $this->getStatisticDataDetail();
        $storageList = $this->getStorageList();
        $storageData = $this->getStorageData();

        $info = array(
            'data_statistics' => $dataDetails,
            'storage_details' => array(
                'list' => $storageList
            ),
            'storing_stastical' => $storageData,
        );
        return $info;
    }
    
    /**
     * 获取备份、实时、复制统计数据
     */
    private function getStatisticDataDetail(){
        $dataDetails = array();
        $dataDetails['backup_data'] = $this->getBackupData(); //总备份数据
        $dataDetails['cdp_data'] = $this->getCDPData(); //总实时数据
        $dataDetails['copy_data'] = $this->getCopyData(); //总复制数据
        
        //每日数据统计列表
        $dataDetails['list'] = $this->getStatisticDataList();

        return $dataDetails;
    }
    //从历史任务里面取 备份数据
    private function getBackupData($time = null){
        $lastday = $time;
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        
        //获取虚拟机 私有云 公有云 整机 卷 文件 nas obs hadoop m365 k8s 数据库
        $vmData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('module', 'VM_SUB_MODULE')['VM'],$taskType,$lastday);
        $privateData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATE_CLOUD'],$taskType,$lastday);
        $publicData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VM'], xphp_get_config('module', 'VM_SUB_MODULE')['PRIVATEPUBLIC_CLOUD_CLOUD'],$taskType,$lastday);
        //整机数据
        $machineData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['OS'], 2,xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'],$lastday);
        //卷数据
        $volData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['OS'], 1,xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'],$lastday);
        //文件数据
        $fileData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module', 'SUBMODULE_TYPE')['FS'],$taskType,$lastday);
        //nas数据
        $nasData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['NAS'], '',$taskType,$lastday);
        //对象存储数据
        $obsData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'],$taskType,$lastday);
        //hadoop数据
        $hadoopData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['FS'], xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'],$taskType,$lastday);
        //exchange数据
        $exchangeData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['M365'], '',$taskType,$lastday);
        //k8s数据
        $k8sData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['KUBERNETES'], '',xphp_get_config('task', 'TASKTYPE')['KUBE_BACKUP'],$lastday);
        //数据库数据
        $dbData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['DB'], '',xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'],$lastday);
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
        );
        return $this->handleStaticData($info,$lastday);
    }
    //从历史任务里面取 实时数据
    private function getCDPData($time = null){
        //整机实时数据
        $lastday = $time;
        //整机卷一起的实时数据
        $machineVolCDPData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'], '',xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'],$lastday);
        $info = array(
            'machineVolCDPData' => $machineVolCDPData,
        );
        return $this->handleStaticData($info,$lastday);
    }
    //从历史任务里面取 复制数据
    private function getCopyData($time = null){
        $lastday = $time;
        //整机卷一起的复制数据
        $machinVolCopyData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['VOL_CDP'], '',xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'],$lastday);
        //文件复制数据
        $fileCopyData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['FILE_COPY'], '',xphp_get_config('task', 'TASKTYPE')['FILE_COPY'],$lastday);
        //数据库复制数据
        $dbCopyData = $this->getProtectData(xphp_get_config('module')['MODULE_TYPE']['DB_CDP'], '',xphp_get_config('task', 'TASKTYPE')['CDP_DB_BACKUP'],$lastday);
        $info = array(
            'machinVolCopyData' => $machinVolCopyData,
            'fileCopyData' => $fileCopyData,
            'dbCopyData' => $dbCopyData,
        );
        return $this->handleStaticData($info,$lastday);
    }
    private function handleStaticData($info,$time = null){
        if(empty($time)){
            //统计所有数据
            $total = 0;
            foreach($info as $key => $value){
                if(is_array($value)){
                    foreach($value as $dayindex => $dayvalue){
                        $total += $dayvalue['size'];
                    }
                }
            }
            $returninfo = $this->pGetIntToSizeInfo(intval($total));
        }else{
            // 按日期存储 size 总和
            $daySize = [];
            $dateWiseSize = [];
            //统计指定时间的数据
            foreach($info as $key => $value){
                if(is_array($value)){
                    foreach($value as $dayindex => $dayvalue){
                        $date = $dayvalue['date'];
                        $size = $dayvalue['size'];
                        $daySize[$date] += $size;
                    }
                }
            }
            //设置没有数据的日期为0
            $dates = [];
            for ($i = $time-1; $i >= 0; $i--) {
                $dates[] = date('m-d', strtotime("-" . $i . " days"));
            }
            foreach($dates as $date){
                if(!isset($daySize[$date])){
                    $dateWiseSize[$date] = 0;
                }else{

                    $dateWiseSize[$date] = $daySize[$date];
                }
            }
            foreach($dateWiseSize as $date => $size){
                
                $dateWiseSize[$date] = $this->pGetIntToSizeInfo(intval($size));
            }
            $returninfo = $dateWiseSize;
        }
        return $returninfo;
    }

    private function getProtectData($moduleType, $submodule, $taskType, $lastday = null){
        $user = xphp_get_user_info();
        $userVal = $user['userUuid'];
        $sql = "SELECT  DATE_FORMAT(bht.finish_time, '%m-%d') AS day, SUM(bht.total_object_write_size) AS write_size,  GROUP_CONCAT(details SEPARATOR ';') AS details
                FROM bd_history_task bht ";
        $sqlLeft = "";
        $sqlAnd = "";
        $sqlWhere = "";
        $sqlEnd = "";
        $module = xphp_get_config('module')['MODULE_TYPE'];
        switch ($moduleType) {
            // 注释掉这段 因为历史任务的值不能和cdp_vol_task这个表联查 当任务一旦删除 就获取不到total_object_write_size
            // case $module['VOL_CDP']:
            //     $sqlLeft .= " left join cdp_vol_task cvt on bht.task_uuid = cvt.task_uuid";
            //     // dev_type 1卷  2整机
            //     $sqlAnd .= " and cvt.dev_type = {$submodule}";
            //     break;
            case $module['DB_CDP']://数据库复制
                $sqlLeft .= " left join bd_task bt on bht.task_uuid = bt.task_uuid";
                $sqlAnd .= " and bt.task_status = {$taskType}";
                break;
        }
        if(!empty($lastday)){
            $sqlAnd .= ' and bht.finish_time >= DATE_SUB(CURDATE(), INTERVAL '.$lastday.' DAY) ';
        }
        $sqlEnd = " GROUP BY DATE(bht.finish_time) ORDER BY day ";
        $sqlWhere .= " where bht.module_type = ? and bht.task_type = ?";
        $sqlParams = array($moduleType,$taskType);
        //如果是admin系统超级管理员或全局观察者
        if (v1_auth_need_check_look()){
            //操作员只显示自己的数据
            $sqlWhere .= " and bht.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($userVal));
        }
        //因为如果moduleType传的是php_get_config('module')['MODULE_TYPE']['VOL_CDP'] 那么不用判断submodule_type
        if(!empty($submodule)){
            $sqlAnd .= " and bht.submodule_type =? ";
            $sqlParams = array_merge($sqlParams, array($submodule));
        }
        $data = $this->dbSelect($sql . $sqlLeft. $sqlWhere . $sqlAnd . $sqlEnd, $sqlParams);
        foreach ($data as $d){
            //如果是整机和卷复制/实时 那么就取details里面real_complete_size进行累加 details是一个数组 里面有几个主机就有几个磁盘
            if($moduleType == $module['VOL_CDP']){
                $size = 0;
                $detailJsonList = explode(';', $d['details']); // 拆分成单个 JSON 字符串
                foreach ($detailJsonList as $jsonStr) {
                    if (empty(trim($jsonStr))) continue;
                    $items = json_decode($jsonStr, true);
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (isset($item['real_complete_size'])) {
                                $size += $item['real_complete_size'];
                            }
                        }
                    }
                }
                $sizeInfo = $this->pGetIntToSizeInfo($size);
            }else{
                $sizeInfo = $this->pGetIntToSizeInfo($d['write_size']);
            }
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
    // 获取统计数据列表信息
    private function getStatisticDataList(){
        
        $backupList= $this->getBackupData(7); //总备份数据
        $cdpList = $this->getCDPData(7); //总实时数据
        $copyList = $this->getCopyData(7); //总复制数据
        $dataDetails = array(
            'dateArr' => array(),
            'backupinfo' => array(),
            'backuppoint' => array(),
            'cdpinfo' => array(),
            'cdppoint' => array(),
            'copyinfo' => array(),
            'copypoint' => array()
        );

        foreach ($backupList as $date => $value){
            $dataDetails['dateArr'][] = $date;
            $dataDetails['backupinfo'][] = array(
                'value' => $value['size'],
                'unit' => $value['unit'],
                'des' => $value['value'],
                'date' => $date
            );
            $dataDetails['backuppoint'][] = $value['size'];
        }
        foreach ($cdpList as $date => $value){
            $dataDetails['cdpinfo'][] = array(
                'value' => $value['size'],
                'unit' => $value['unit'],
                'des' => $value['value'],
                'date' => $date
            );
            $dataDetails['cdppoint'][] = $value['size'];

        }
        foreach ($copyList as $date => $value){
            $dataDetails['copyinfo'][] = array(
                'value' => $value['size'],
                'unit' => $value['unit'],
                'des' => $value['value'],
                'date' => $date
            );
            $dataDetails['copypoint'][] = $value['size'];

        }
        return $dataDetails;
    }

    /**
     * 获取数据统计信息
     */
    private function getStatisticDataDetails()
    {
        $dataDetials = array();
        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');

        //备份数据:文件(1)，数据库，操作系统，虚拟机(1)，NAS(1)，实时容灾
        $taskTypeArr = array($taskTypeConf['BACKUP'], $taskTypeConf['DB_BACKUP'],
            $taskTypeConf['OS_BACKUP'], $taskTypeConf['VOL_CDP_BACKUP']);

        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        $dataDetials['total_backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $dataDetials['total_backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

        //副本数据:虚拟机，文件，操作系统，数据库
        $taskTypeArr = array($taskTypeConf['BACKUP_COPY'], $taskTypeConf['FILE_BACKUP_COPY'],
            $taskTypeConf['DB_BACKUP_COPY'], $taskTypeConf['OS_BACKUP_COPY']);
        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        $dataDetials['total_copy_data'] = $valueAndUnit['value'];     //副本数据总量
        $dataDetials['total_copy_data_unit'] = $valueAndUnit['unit']; //副本数据总量的单位

        //归档数据:虚拟机
        $taskTypeArr = array($taskTypeConf['ARCHIVE']);
        $valueAndUnit = $this->getSomeTaskTypeTimepointWriteSize($taskTypeArr);
        $dataDetials['total_archived_data'] = $valueAndUnit['value'];     //归档数据总量
        $dataDetials['total_archived_data_unit'] = $valueAndUnit['unit']; //归档数据总量的单位

        //每日数据统计列表
        $dataDetials['list'] = $this->getStatisticDataDetailsList();
        return $dataDetials;
    }

    /**
     * 获取指定类型任务的写入数据总大小
     * @param array $taskTypeArr 任务类型
     * @return array $valueAndUnit  大小(value)和单位(unit)和数量(num)
     */
    private function getSomeTaskTypeTimepointWriteSize($taskTypeArr)
    {
        $sql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where task_type in (?)";
        // 实时容灾
        $sqlVolCdp = "select 
                        SUM(cvbvs.backup_file_size + cvbvs.log_file_total_size) as size ,count(bbt.id) as num 
                      FROM 
                        cdp_vol_backup_vol_set cvbvs,cdp_vol_backup_agent cvba,bd_backup_timepoint bbt 
                      where
                      cvbvs.backup_agent_id = cvba.id and cvba.copy_flag = 0 and cvba.timepoint_uuid = bbt.timepoint_uuid  ";
        // 副本任务个数
        $taskNumSql = "select count(task_uuid) as task_num from bd_task where task_type = ?";
        $sqlParams = array_map(function ($value) {
            $value = sprintf('%d', $value);
            return $value;
        }, $taskTypeArr);
        $dataSize = 0;
        $dataNum = 0;
        foreach ($sqlParams as $d) {
            if ($d == sprintf('%d', xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'])) { //volcdp的数据量
                $data = $this->dbSelect($sqlVolCdp);
            } else {
                $data = $this->dbSelect($sql, array($d));
            }
            $taskNumSqlData = $this->dbSelect($taskNumSql, array($d));
            $dataSize += $data[0]['size'];
            $dataNum += $data[0]['num'];
            $taskNum += $taskNumSqlData[0]['task_num'];
        }
        $valueAndUnit = v1_calsize_to_value_and_unit($dataSize, true);
        $valueAndUnit['num'] = $dataNum;     //时间点个数
        $valueAndUnit['task_num'] = $taskNum;  //副本任务个数
        return $valueAndUnit;
    }

    /**
     * 各模块副本数据
     */
    private function getModuleCopyData($moduleType,$submoduleType = null)
    {
        $info = array();
        //按照任务类型来统计副本数据，按照模块类型返回数据
        $backupCopy = xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'];
        switch ($moduleType){
            case xphp_get_config('module', 'MODULE_TYPE')['VM']:
                //公有云
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect($sql, array(
                    xphp_get_config('module', 'MODULE_TYPE')['VM'],
                    $backupCopy,
                    xphp_get_config('vm', 'VM_SUB_MODULE')['VM']));
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                                   where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['VM'],
                        $backupCopy,
                        xphp_get_config('vm', 'VM_SUB_MODULE')['VM']
                    )
                );
                break;
            case xphp_get_config('module', 'MODULE_TYPE')['PUBLIC_CLOUD']:
                //公有云
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect($sql, array(
                    xphp_get_config('module', 'MODULE_TYPE')['VM'],
                    $backupCopy,
                    xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']));
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                                   where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['VM'],
                        $backupCopy,
                        xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']
                    )
                );
                break;
            case xphp_get_config('module', 'MODULE_TYPE')['FS']:
                // fs
                $sql = "select sum(write_size) as size, count(id) as num 
                            from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect(
                    $sql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $backupCopy,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['FS'])
                );
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $backupCopy,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['FS'])
                );
                break;
            case xphp_get_config('module', 'MODULE_TYPE')['HADOOP']:
                // hadoop
                $sql = "select sum(write_size) as size, count(id) as num 
                            from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect(
                    $sql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $backupCopy,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'])
                );
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $backupCopy,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'])
                );
                break;
            case xphp_get_config('module', 'MODULE_TYPE')['OBS']:
                //obs
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect(
                    $sql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $backupCopy,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'])
                );
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $backupCopy,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'])
                );
                break;
            default:
                $sql = "select sum(write_size) as size, count(id) as num 
                            from bd_backup_timepoint where module_type = ? and task_type in (?)";
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                            where module_type = ? and task_type = ?";
                $params = array($moduleType,$backupCopy);
                if(!empty($submoduleType) || (xphp_get_config('module', 'MODULE_TYPE')['OS']  == $moduleType && $submoduleType == 0)){
                    $sql .= " and sub_module_type = ?";
                    $taskNumSql .= " and sub_module_type = ?";
                    $params = array_merge($params, array($submoduleType));
                }
                $data = $this->dbSelect(
                    $sql,
                    $params
                );
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    $params
                );
                break;
        }

        $dataSize = $data[0]['size'];
        $dataNum = $data[0]['num'];
        $taskNum = $taskNumSqlData[0]['task_num'];
        $valueAndUnit = v1_calsize_to_value_and_unit($dataSize, true);
        $valueAndUnit['num'] = $dataNum;     //时间点个数
        $valueAndUnit['task_num'] = $taskNum;  //副本任务个数
        $info = array(
            'type' => $moduleType,                             //对应模块类型
            'copy_data' => $valueAndUnit['value'],      //数据总量
            'copy_data_unit' => $valueAndUnit['unit'],  //数据总量的单位
            'copy_point' => $valueAndUnit['num'],       //点个数
            'copy_task_num' => $valueAndUnit['task_num'],    //副本任务个数
        );
        return $info;
    }

    /**
     * 获取存储信息：包括存储统计和存储详情
     */
    private function getStorageList()
    {
        $sql = "select 
                    storage_nickname, storage_type, total_size, free_size, status, mount_flag, node_uuid 
                from 
                    bd_storage_resource 
                where 
                    lan_free_flag = ? and source_type = ?";
        $sqlParams = array(xphp_get_config('app', 'FLAG')['UNSET'],xphp_get_config('app', 'FLAG')['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $list = array();
        $storageHandler = StorageHandler::instance();
        $nodeHandler = new Node();
        $allStorageTypeDes = xphp_get_desc('Pf', 'STORAGETYPE');
        foreach ($data as $d) {
            $usedSize = $d['total_size'] - $d['free_size'];
            //存储状态
            $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
            $status = $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
            //转换存储状态0在线 1离线
            $status = $status == xphp_get_config('storage', 'STORAGE_STATUS')['ONLINE'] ? 0 : 1;
            $list[] = array(
                'store_name' => $d['storage_nickname'],
                'store_type' => $d['storage_type'],
                'use_space' => v1_calpercent_value($d['total_size'], $usedSize),
                'use_space_des' => v1_calsize($usedSize, true) . '/' . v1_calsize($d['total_size'], true),
                'store_status' => $status,
                'store_type_des' => $allStorageTypeDes[$d['storage_type']],
            );
        }
        return $list;
    }

    /**
     *  获取存储统计
     */
    private function getStorageData()
    {
        $sql = "select 
                 count(bsr.storage_uuid) 
                as 
                storage_count, sum(bsr.total_size) 
                as total_size, sum(bsr.free_size) 
                as free_size from bd_storage_resource bsr, bd_node bn 
                where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? and bsr.source_type = ?";
        $sqlParams = array(xphp_get_config('app', 'FLAG')['UNSET'],xphp_get_config('app', 'FLAG')['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        if (!$data) {
            $totalSize = 0;
            $freeSize = 0;
        } else {
            $totalSize = intval($data[0]['total_size']);
            $freeSize = intval($data[0]['free_size']);
        }

        if ($totalSize > 0) {
            $percent = round(($freeSize / $totalSize) * 100, 1);
        } else {
            $percent = 0;
        }
        $useSize = $totalSize - $freeSize;

        $useSizeValueAndUnit = v1_calsize_to_value_and_unit($useSize);
        $freeSizeValueAndUnit = v1_calsize_to_value_and_unit($freeSize);

        $info = array(
            'storage_num' => intval($data[0]['storage_count']),
            'total_storage_data' => v1_calsize($totalSize,true),

            'use_size' => $useSize,
            'free_size' => $freeSize,

            'use_size_des' => $useSizeValueAndUnit['value'],
            'use_size_unit' => $useSizeValueAndUnit['unit'],

            'free_size_des' => $freeSizeValueAndUnit['value'],
            'free_size_unit' => $freeSizeValueAndUnit['unit'],
        );

        return $info;
    }

    /**
     * 获取数据统计的每日备份，副本，归档情况列表
     * @return array    $info
     */
    private function getStatisticDataDetailsList()
    {
        $info = array();
        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        //当天的数据从时间点中获取
        $todayInfo = array(
            'date' => date('m/d'),
        );

        //备份数据:文件(1)，数据库，操作系统，虚拟机(1)，NAS(1)，实时容灾
        $taskTypeArr = array($taskTypeConf['BACKUP'], $taskTypeConf['DB_BACKUP'],
            $taskTypeConf['OS_BACKUP'], $taskTypeConf['VOL_CDP_BACKUP'], $taskTypeConf['VOL_CDP_TAKEOVER']);

        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量
        $todayInfo['backup_data_des'] = $valueAndUnit['value'];     //备份数据总量
        $todayInfo['backup_data_des_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

        //副本数据:虚拟机，文件，操作系统，数据库
        $taskTypeArr = array($taskTypeConf['BACKUP_COPY'], $taskTypeConf['FILE_BACKUP_COPY'],
            $taskTypeConf['DB_BACKUP_COPY'], $taskTypeConf['OS_BACKUP_COPY']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['copy_data'] = $valueAndUnit['size'];            //副本数据真实量
        $todayInfo['copy_data_des'] = $valueAndUnit['value'];     //副本数据总量
        $todayInfo['copy_data_des_unit'] = $valueAndUnit['unit'];   //副本数据总量的单位

        //归档数据:虚拟机
        $taskTypeArr = array($taskTypeConf['ARCHIVE']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['archived_data'] = $valueAndUnit['size'];          //归档数据真实量
        $todayInfo['archived_data_des'] = $valueAndUnit['value'];     //归档数据总量
        $todayInfo['archived_data_des_unit'] = $valueAndUnit['unit']; //归档数据总量的单位

        $info[] = $todayInfo;

        //从bd_storage_monitor表取最新6条，每一条都是存储的上一天的数据。
        $sql = "select 
                    unix_timestamp(date) date,  backup_write_size, copy_write_size, 	archive_write_size 
                from bd_storage_monitor 
                order by id desc limit 0, 6";
        $data = $this->dbSelect($sql);

        foreach ($data as $d) {
            $dateTime = intval($d['date']) - 3600 * 24;
            $backupValueAndUnit = v1_calsize_to_value_and_unit($d['backup_write_size'], true);
            $copyValueAndUnit = v1_calsize_to_value_and_unit($d['copy_write_size'], true);
            $archiveValueAndUnit = v1_calsize_to_value_and_unit($d['archive_write_size'], true);

            $info[] = array(
                'date' => date('m/d', $dateTime),

                'backup_data' => $d['backup_write_size'],
                'backup_data_des' => $backupValueAndUnit['value'],
                'backup_data_des_unit' => $backupValueAndUnit['unit'],

                'copy_data' => $d['copy_write_size'],
                'copy_data_des' => $copyValueAndUnit['value'],
                'copy_data_des_unit' => $copyValueAndUnit['unit'],

                'archived_data' => $d['archive_write_size'],
                'archived_data_des' => $archiveValueAndUnit['value'],
                'archived_data_des_unit' => $archiveValueAndUnit['unit'],
            );
        }

        //按时间顺序排列
        $info = array_reverse($info);
        return $info;
    }

    /**
     * 告警与任务
     */
    public function getCurrentTaskAndWarning($params)
    {
        $info = array();
        $sql = "select module_type, sub_module_type, task_type, task_status from bd_task where delete_flag = ?";
        $sqlParams = array(xphp_get_config('app', 'FLAG')['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);

        $taskNum = 0;       //任务总数
        $waittingNum = 0;   //等待任务数：任务在等待运行状态
        $runningNum = 0; //运行任务数
        $stopNum = 0;   //停止任务数
        $finishNum = $this->getTodayHistoryJobSuccessNum();     //完成任务数：在历史任务去取，规则为今日所有成功的历史任务
        $moduleAndTasktypeArr = array();     //模块和任务类型数组，按模块统计使用,这里需要通过module_type和task_type区分模块
        foreach ($data as $d) {
            $taskNum++;
            $moduleAndTasktypeArr[] = array(
                'module_type' => intval($d['module_type']),
                'sub_module_type' => intval($d['sub_module_type']),
                'task_type' => intval($d['task_type'])
            );
            if (xphp_get_config('task')['TASKSTATUS']['WAITTING'] == $d['task_status']) {
                //处理等待任务数
                $waittingNum++;
            }
            if(xphp_get_config('task')['TASKSTATUS']['RUNNING'] == $d['task_status']){
                $runningNum++;
            }
            if(xphp_get_config('task')['TASKSTATUS']['STOPPED'] == $d['task_status'] || xphp_get_config('task')['TASKSTATUS']['STOPPING'] == $d['task_status']){
                $stopNum++;
            }
        }

        $taskModuleNum = $this->getTaskModultNum($moduleAndTasktypeArr);

        $currentTask = array(
            'total_task_num' => $taskNum,
            'wait_num' => $waittingNum,
            'complete_num' => $finishNum,
            'running_num' => $runningNum,
            'stop_num' => $stopNum
        );

        $currentTask = array_merge($currentTask, $taskModuleNum);
        $alarmNum = $this->getAlarmInfo();

        $info = array(
            'current_task' => $currentTask,
            'warning' => $alarmNum
        );
        return $info;
    }

    /**
     * 获取当日任务，成功的
     */
    private function getTodayHistoryJobSuccessNum()
    {
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sql = "SELECT 
                COUNT(id) AS num 
                FROM bd_history_task 
                WHERE (error_code = 0 OR error_code = 47)
                AND (finish_time >= ? AND finish_time < ?)";

        $data = $this->dbSelect($sql, array($todayStart, $todayEnd));
        $num = $data[0]['num'];

        return $num;
    }

    /**
     * 根据模块和任务类型统计各个模块任务数量
     * @param unknown $moduleAndTasktypeArr 模块和任务类型数组
     * @return array
     */
    private function getTaskModultNum($moduleAndTasktypeArr)
    {
        $taskNum = array(
            'vm' => array('num' => 0,'des'=>xphp_get_lang('UI_PLATFORM_VM_VIRTUAL')),
            'publicCloud' => array('num' => 0,'des'=>xphp_get_lang('WEB_PLATFORM_DES_PUBLIC_CLOUD')),
            'privateCloud' => array('num' => 0,'des'=>xphp_get_lang('WEB_PLATFORM_DES_PRIVATE_CLOUD')),
            'completeMachine' => array('num' => 0,'des'=>xphp_get_lang('UI_COMPLETE_MACHINE')),
            'os' => array('num' => 0,'des'=>xphp_get_lang('WEB_PLATFORM_DES_VOL')),
            'file' => array('num' => 0,'des'=>xphp_get_lang('WEB_PLATFORM_DES_FS')),
            'nas' => array('num' => 0,'des'=>xphp_get_lang('WEB_PLATFORM_DES_NAS')),
            'obs' => array('num' => 0,'des'=>xphp_get_lang('UI_PLATFORM_OBS')),
            'hadoop' => array('num' => 0,'des'=>xphp_get_lang('UI_PLATFORM_HADOOP')),
            'office365' => array('num' => 0,'des'=>'Microsoft365'),
            'k8s' => array('num' => 0,'des'=> xphp_get_lang(('UI_PLATFORM_K8S'))),
            'db' => array('num' => 0,'des'=>xphp_get_lang('WEB_PLATFORM_DES_DB')),

            // 'cdp' => array('num' => 0,'des'=>xphp_get_lang('UI_HOMEPAGE_SERVER_CDP')),
           
            // 'dbcdp' => array('num' => 0),

        );
        $moduleTypeConf = xphp_get_config('module', 'MODULE_TYPE');
        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        $jobHandler = new JobInfo();
        $vmflag =  false;
        $publicflag =  false;
        $privateflag =  false;
        foreach ($moduleAndTasktypeArr as $type) {
            $module = $type['module_type'];
            $subModuleType = $type['sub_module_type'];
            $tasktype = $type['task_type'];
            $moduleTypedes = $this->getModuleTypeDes($module,$subModuleType);
            switch ($module) {
                case $moduleTypeConf['VM']:
                    $params = array(
                        'module_type' => $module,
                        'sub_module_type' => $subModuleType,
                    );
                    if($subModuleType  == xphp_get_config('vm', 'VM_SUB_MODULE')['VM'] && $vmflag == false){
                        $taskNum['vm']['num'] = $jobHandler -> getJobList($params,true)['total'];
                        $vmflag = true;
                    }else if($subModuleType  == xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'] && $publicflag == false){
                        $taskNum['publicCloud']['num'] = $jobHandler -> getJobList($params,true)['total'];
                        $publicflag =  true;
                    }else if($subModuleType  == xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'] && $privateflag == false){
                         $taskNum['privateCloud']['num'] = $jobHandler -> getJobList($params,true)['total'];
                         $privateflag = true;
                    }

                    break;
                case $moduleTypeConf['OS']:
                    //操作系统
                    //这里要区分整机定时和之前旧的
                    if($subModuleType == 0){
                        $taskNum['os']['num'] += 1;
                    }else if($subModuleType == xphp_get_config('module','OS_SUBMODULE_TYPE')['MACHINE_OS']) {
                        $taskNum['completeMachine']['num'] += 1;
                    }
                    break;
                case $moduleTypeConf['FS']:
                    //文件
                    // 包括hadoop、obs、fs 根据sub_module_type区分
                    if($subModuleType == xphp_get_config('module','SUBMODULE_TYPE')['FS']){
                        $taskNum['file']['num'] += 1;
                    }elseif ($subModuleType == xphp_get_config('module','SUBMODULE_TYPE')['HADOOP']){
                        $taskNum['hadoop']['num'] += 1;
                    }elseif ($subModuleType == xphp_get_config('module','SUBMODULE_TYPE')['OBS']){
                        $taskNum['obs']['num'] += 1;
                    }
                    break;
                case $moduleTypeConf['NAS']:
                    //NAS
                    $taskNum['nas']['num'] += 1;
                    break;
                case $moduleTypeConf['M365']:
                    //m365
                    $taskNum['office365']['num'] += 1;
                    break;
                case $moduleTypeConf['DB']:
                    //数据库
                    $taskNum['db']['num'] += 1;
                    break;
                    
                case $moduleTypeConf['KUBERNETES']:
                     //k8s
                     $taskNum['k8s']['num'] += 1;
                     break;
                case $moduleTypeConf['PUBLIC_CLOUD']:
                    //公有云
                    $taskNum['publicCloud']['num'] += 1;
                    break;
                case $moduleTypeConf['HADOOP']:
                    //hadoop
                    $taskNum['hadoop']['num'] += 1;
                    break;
                case $moduleTypeConf['OBS']:
                    //obs
                    $taskNum['obs']['num'] += 1;
                    break;
                // case $moduleTypeConf['VOL_CDP']:
                //     //实时容灾
                //     $taskNum['cdp']['num'] += 1;
                //     break;

                // case $moduleTypeConf['BACKUP_COPY_CLIENT']:
                //     //副本，这里特殊处理，因为副本和归档都是这个模块类型 ，需要再判断任务类型
                //     $acrhiveTaskTypeArr = array($taskTypeConf['ARCHIVE'], $taskTypeConf['ARCHIVE_FETCH']);
                //     if (!in_array($tasktype, $acrhiveTaskTypeArr)) {
                //         $taskNum['copy']['num'] += 1;
                //     }
                //     break;
                // case $moduleTypeConf['OEM_DBCDP']:
                //     //实时同步
                //     $taskNum['dbcdp']['num'] += 1;
                //     $taskNum['dbcdp']['des'] = $moduleTypedes;
                //     break;
                
            }
        }
        //获取最多模块的任务数量信息
        // $taskNum['max_num'] = max($taskNum);
        $maxValue = max($taskNum);
        $maxKey = array_search($maxValue, $taskNum);
        $taskNum['max_num'] = array("module" => $maxKey, "num" => $maxValue['num'], "des" => $maxValue['des']);
        return $taskNum;
    }

    /**
     * 得到告警信息
     */
    private function getAlarmInfo()
    {
        //任务告警
        $sql = "select count(task_alarm_id) as num from bd_task_alarm where alarm_level != ? and solved_flag = ?";
        $sqlParams = array(xphp_get_config('alarm', 'ALARM')['notice'], xphp_get_config('app', 'FLAG')['UNSET']);

        $data = $this->dbSelect($sql, $sqlParams);
        $taskAlarmNum = $data[0]['num'];

        //系统告警
        $sql = "select count(system_alarm_id) as num from bd_system_alarm where alarm_level != ? and solved_flag = ?";
        $sqlParams = array(xphp_get_config('alarm', 'ALARM')['notice'], xphp_get_config('app', 'FLAG')['UNSET']);

        $data = $this->dbSelect($sql, $sqlParams);
        $systemAlarmNum = $data[0]['num'];

        $alarm = array(
            'task_num' => $taskAlarmNum,
            'system_num' => $systemAlarmNum,
        );

        return $alarm;
    }

    /**
     * @return false|string
     */
    public function getSystemLisenceInfo()
    {
        $sql = "select extension from bd_license ";
        $data = $this->dbSelect($sql);
        $extension = json_decode(v1_decrypt($data[0]['extension']), true);
        //授权功能
        in_array(
            'vmprotect',
            $extension['p']
        ) ?
            $info['vm'] = true : $info['vm'] = false;
        //公有云
        in_array(
            'awsprotect',
            $extension['p']
        ) ?
            $info['publicCloud'] = true : $info['publicCloud'] = false;  //公有云
        //私有云
        in_array(
            'prcloud_protect',
            $extension['p']
        ) ?
            $info['privateCloud'] = true : $info['privateCloud'] = false;  //私有云
        //整机
        in_array(
            'complete_machine',
            $extension['p']
        ) ? $info['completeMachine'] = true : $info['completeMachine'] = false;
        //卷
        in_array(
            'osbackup',
            $extension['p']
        ) ? $info['os'] = true : $info['os'] = false;
        // 文件
        in_array(
            'fileprotect',
            $extension['p']
        ) ?
            $info['file'] = true : $info['file'] = false;
        //nas
        in_array(
            'nas_protect',
            $extension['p']
        ) ?
            $info['nas'] = true : $info['nas'] = false;

        in_array(
            'obs_protect',
            $extension['p']
        ) ?
            $info['obs'] = true : $info['obs'] = false; //obs

        in_array(
            'hadoop_protect',
            $extension['p']
        ) ?
            $info['hadoop'] = true : $info['hadoop'] = false; //hadoop

        in_array(
            'office365_protect',
            $extension['p']
        ) ?
            $info['office365'] = true : $info['office365'] = false; // office365

            
        in_array(
            'k8s_protect',
            $extension['p']
        ) ?
            $info['k8s'] = true : $info['k8s'] = false; // k8s


        in_array(
            'db_protect',
            $extension['p']
        ) ? $info['db'] = true : $info['db'] = false;


        // 连续数据保护
        //整机
        in_array(
            'complete_cdp_backup',
            $extension['p']
        ) ? $info['cdpCompleteMachine'] = true : $info['cdpCompleteMachine'] = false;

        in_array(
            'vol_cdp_backup',
            $extension['p']
        ) ? $info['cdpOs'] = true : $info['cdpOs'] = false;
        
        //数据复制
        //整机
        in_array(
            'machine_copy',
            $extension['p']
        ) ? $info['reCompleteMachine'] = true : $info['reCompleteMachine'] = false;

        in_array(
            'vol_cdp_copy',
            $extension['p']
        ) ? $info['reOs'] = true : $info['reOs'] = false;

        in_array(
            'file_copy_protect',
            $extension['p']
        ) ? $info['refile'] = true : $info['refile'] = false;

        in_array(
            'dbcdpcopy',
            $extension['p']
        ) ? $info['redb'] = true : $info['redb'] = false;

        if (
            $extension['f']['copy'] ?
                $info['copy'] = true : $info['copy'] = false
        ) {
            //副本通过f中的copy进行判断
        }

        if (
            $extension['f']['archive'] ?
                $info['archive'] = true : $info['archive'] = false
        ) {
            //归档通过f中的archive进行判断
        }

        return $info;
    }

    /**
     * 任务列表
     */
    public function getCurrentTaskList($params)
    {
        $info = array();
        $sql = "SELECT 
    ssb.task_progress, 
    bt.task_uuid, 
    bt.task_name, 
    bt.module_type, 
    bt.task_type, 
    UNIX_TIMESTAMP(bt.create_time) AS create_time,
    bt.task_status, 
    bt.strategy_id, 
    bu.user_name,
    bri.total_object_size, 
    bri.total_object_completed_size, 
    bri.speed, 
    bri.speed_time,
    bri.total_object_valid_size,
    bri.total_object_completed_valid_size,
    cvti.takeover_agent_role
FROM 
    bd_task bt
LEFT JOIN 
    sr_sure_backup ssb ON bt.task_uuid = ssb.task_uuid
LEFT JOIN 
    cdp_vol_task_takeover_info cvti ON bt.task_uuid = cvti.task_uuid
JOIN 
    bd_running_info bri ON bt.task_uuid = bri.task_uuid
JOIN 
    bd_user bu ON bt.user_uuid = bu.user_uuid
WHERE 
    bt.delete_flag = ?
    AND bt.task_type != ?
    AND bt.task_type != ?
    ORDER BY bt.create_time DESC";
        $sqlParams = array(
            xphp_get_config('app', 'FLAG')['UNSET'],
            xphp_get_config('task', 'TASKTYPE')['ORCH_TASK'],
            xphp_get_config('task', 'TASKTYPE')['BACKUP_EXPORT']);
        $data = $this->dbSelect($sql, $sqlParams);
        $tasktypeDes = xphp_get_desc('Pf', 'TASKTYPEDES');
        $taskstatusDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $jobHandler = new JobInfo();
        foreach ($data as $d) {
            $info[] = array(
                'task_name' => $d['task_name'],
                'task_speed' => $jobHandler->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time'], $d['task_type']),
                'task_status' => $d['task_status'],
                'task_progress' => $jobHandler->getCurrentTaskProgress($d),
                'task_module' => $d['module_type'],
                'task_type' => $d['task_type'],
                'takeover_agent_role' => $d['takeover_agent_role'],
                'task_type_des' =>  $tasktypeDes[$d['task_type']],
                'task_status_des' =>  $taskstatusDes[$d['task_status']],
            );
        }

        $list = array(
            'list' => $info
        );
        return $list;
    }

    /**
     * 获取虚拟机模块统计信息
     * @param array $modules 模块信息
     */
    private function getVmViewInfo()
    {
        $info = array('show' => true);
        // if (false === $modules['vm']) {
        //     //没有这个模块的授权，直接返回
        //     $info['show'] = false;
        //     return $info;
        // }

        //有哪些虚拟机类型
        $sql = "select hypervisor_type from vm_vcenter where hypervisor_type not in (100, 101) group by hypervisor_type ";
        $data = $this->dbSelect($sql);
        $hypervisorArr = array();
        foreach ($data as $d) {
            $hypervisorArr[] = $d['hypervisor_type'];
        }
        $info['vm_type'] = $hypervisorArr;

        //虚拟机总数
        $sql = 'select count(vt.tree_id) as total 
                from vm_tree vt, vm_vcenter vv where vv.hypervisor_type 
                not in (' . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']) . ')' . '
                and vt.display_mode = ? and vt.type = ? and vt.vcenter_uuid = vv.vcenter_uuid';
        $sqlParams = array(
            xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'],
            xphp_get_config('vm')['VM_TREE_TYPE']['VM']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $info['total_vm_num'] = $data[0]['total'];

        //受保护虚拟机个数
        $vmSubModule = xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
        $privateCloudSubModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        $sql = "select count(vml.machine_id) 
                as protect_vms from vm_machine_list vml, vm_vcenter vv, bd_task bt, vm_machine vm
                where vml.vcenter_uuid = vm.vcenter_uuid and vml.vm_uuid = vm.vm_uuid 
                and bt.task_uuid = vml.task_uuid 
                and vml.vcenter_uuid = vv.vcenter_uuid and bt.task_type = ? 
                and bt.sub_module_type in ({$vmSubModule},{$privateCloudSubModule})";
        $sqlParams = array(
            xphp_get_config('task', 'TASKTYPE')['BACKUP']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $info['protected_vm_num'] = $data[0]['protect_vms'];

        //虚拟机备份数据
        $valueAndUnit = $this->pGetProtectData(
            xphp_get_config('module')['MODULE_TYPE']['VM'],
            xphp_get_config('vm', 'VM_SUB_MODULE')['VM']
        );
        $info['backup_data'] = v1_calsize_to_value_and_unit($valueAndUnit['size'], true)['value'];     //备份数据总量

        $info['backup_data_unit'] = v1_calsize_to_value_and_unit($valueAndUnit['size'], true)['unit'];  //备份数据总量的单位

        //虚拟机累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module')['MODULE_TYPE']['VM']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];


        //当日备份数据:虚拟机,按模块获取
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sql = 'select sum(total_object_valid_size) as size, GROUP_CONCAT(details SEPARATOR ";") AS details from bd_history_task 
                where module_type in (?) and submodule_type 
                not in (' . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']) . ')' .
            ' and task_type in (?) and  start_time >= ? and finish_time < ?';
        $data = $this->dbSelect(
            $sql,
            array(
                xphp_get_config('module')['MODULE_TYPE']['VM'],
                xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                $todayStart,
                $todayEnd)
        );
        // $backupSize = $data[0]['size'];  ////备份数据真实量
        $detailJsonList = explode(";", $data[0]['details']);
        $backupSize = 0;
        foreach ($detailJsonList as $jsonStr) {
            if (empty(trim($jsonStr))) continue;
            $items = json_decode($jsonStr, true);
             if (is_array($items['vms_details'])) {
                foreach ($items['vms_details'] as $item) {
                    if (isset($item['vm_valid_size']) && $item['error_code'] == 0) {
                        $backupSize += (int)$item['vm_valid_size'];
                    }
                }
             }
        }

        //当日副本数据:虚拟机
        $sql = 'select sum(total_object_valid_size) as size, GROUP_CONCAT(details SEPARATOR ";") AS details from bd_history_task 
                where module_type in (?) and submodule_type in (?) 
                and task_type in (?) and  start_time >= ? and finish_time < ?';
        $data = $this->dbSelect(
            $sql,
            array(
                xphp_get_config('module')['MODULE_TYPE']['VM'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['VM'],
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],
                $todayStart,
                $todayEnd
            )
        );
        // $copySize = $data[0]['size'];  //副本数据真实量
        $detailJsonList = explode(";", $data[0]['details']);
        $copySize = 0;
        foreach ($detailJsonList as $jsonStr) {
            if (empty(trim($jsonStr))) continue;
            $items = json_decode($jsonStr, true);
            if (is_array($items['vms_details'])) {
                foreach ($items['vms_details'] as $item) {
                    if (isset($item['vm_valid_size']) && $item['error_code'] == 0) {
                        $copySize += (int)$item['vm_valid_size'];
                    }
                }
             }
        }

        //当日归档数据:虚拟机
        $sql = 'select sum(total_object_valid_size) as size, GROUP_CONCAT(details SEPARATOR ";") AS details from bd_history_task 
                where module_type in (?) and submodule_type in (?) and task_type in (?) 
                and  start_time >= ? and finish_time < ?';
        $data = $this->dbSelect(
            $sql,
            array(
                xphp_get_config('module')['MODULE_TYPE']['VM'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['VM'],
                xphp_get_config('task', 'TASKTYPE')['ARCHIVE'],$todayStart,$todayEnd
            )
        );
        // $archiveSize = $data[0]['size']; //归档数据真实量
        $detailJsonList = explode(";", $data[0]['details']);
        $archiveSize = 0;
        foreach ($detailJsonList as $jsonStr) {
            if (empty(trim($jsonStr))) continue;
            $items = json_decode($jsonStr, true);
            if (is_array($items['vms_details'])) {
                foreach ($items['vms_details'] as $item) {
                    if (isset($item['vm_valid_size']) && $item['error_code'] == 0) {
                        $archiveSize += (int)$item['vm_valid_size'];
                    }
                }
             }
        }

        $todayProtectData = $backupSize + $copySize + $archiveSize;
        $valueAndUnit = v1_calsize_to_value_and_unit($todayProtectData, true);

        //虚拟机今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        //虚拟机副本数据
        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module')['MODULE_TYPE']['VM']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //虚拟机副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //虚拟机副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //虚拟机副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //虚拟机副本任务数
            );
        }

        //虚拟机归档数据
        $sqlVm = "select sum(write_size) as size, count(id) as num 
                  from bd_backup_timepoint where task_type in (?) and sub_module_type != ? and module_type = ?";
        $data = $this->dbSelect(
            $sqlVm,
            array(
                xphp_get_config('task', 'TASKTYPE')['ARCHIVE'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'],
                xphp_get_config('module')['MODULE_TYPE']['VM']
            )
        );
        $valueAndUnit = v1_calsize_to_value_and_unit($data[0]['size'], true);
        //如果没有归档,archive就没有
        if ($data[0]['num']) {
            //有时间点，给copy赋值
            $info['archive'] = array(
                'archive_data' => $valueAndUnit['value'],      //归档数据
                'archive_data_unit' => $valueAndUnit['unit'],  //归档数据单位
                'archive_point' => $data[0]['num'],       //归档点
            );
        }
        return $info;
    }
     /**
     * 公共函数
     * 获取整机实时和卷实时主机备份数据
     * @param int $devType 设备类型
     */
    private function pGetCDPProtectData($devType = null){
        $sql  = "SELECT sum(bbt.write_size) as write_size FROM bd_backup_timepoint bbt INNER JOIN cdp_vol_backup_agent cvba 
        ON bbt.timepoint_uuid = cvba.timepoint_uuid 
        where cvba.dev_type = ? and cvba.copy_flag = 0 ";
        // copy_flag 为0是普通备份数据 1是复制数据
        $sqlParams = array($devType);
        $data = $this->dbSelect($sql, $sqlParams);
        return $this->pGetIntToSizeInfo(intval($data[0]['write_size']));
    }

    /**
     * 公共函数
     * 获取保护数据总量
     * @param int $moduleType 模块类型
     */
    private function pGetProtectData($moduleType, $subModule = null)
    {
        $sql = "select 
                sum(write_size) as write_size from  bd_backup_timepoint where module_type = ? and task_type = ?";
        
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        if($moduleType == xphp_get_config('module', 'MODULE_TYPE')['KUBERNETES']){
             $taskType = xphp_get_config('task', 'TASKTYPE')['KUBE_BACKUP'];
        }
        $sqlParams = array($moduleType, $taskType);
        if(!empty($subModule)){
            $sql .= ' and sub_module_type = ?';
            $sqlParams = array_merge($sqlParams, array($subModule));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return $this->pGetIntToSizeInfo(intval($data[0]['write_size']));
    }

    /**
     * 获取指定类型模块的写入数据总大小
     * @param array $moduleTypeArr 模块类型
     * @return array $valueAndUnit      大小(value)和单位(unit)和数量(num)
     */
    private function getSomeModuleTypeTimepointWriteSize($moduleType,$subModule = null)
    {
        $sql = "select sum(write_size) as write_size from  bd_backup_timepoint where module_type = ? and task_type = ? ";
        $taskType = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        switch ($moduleType) {
            case xphp_get_config('module')['MODULE_TYPE']['DB']:
                $taskType = xphp_get_config('task','TASKTYPE')['DB_BACKUP'];
                break;
            case xphp_get_config('module')['MODULE_TYPE']['OS']:
                $taskType = xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'];
                break;
        }
        $sqlParams = array($moduleType,$taskType);
        if($moduleType == xphp_get_config('module')['MODULE_TYPE']['VM'] && $subModule == xphp_get_config('vm','VM_SUB_MODULE')['VM']){
            $sql .= " and sub_module_type != ? ";
            $sqlParams = array_merge($sqlParams, array(xphp_get_config('vm','VM_SUB_MODULE')['PUBLIC_CLOUD']));
        }else if(!empty($subModule) || ($moduleType == xphp_get_config('module')['MODULE_TYPE']['OS'] && $subModule == 0)){
            $sql .= " and sub_module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($subModule));
        }

        $data = $this->dbSelect($sql, $sqlParams);
        return $this->pGetIntToSizeInfo(intval($data[0]['write_size']));
    }

    /**
     * 获取指定类型模块的当天写入数据总大小
     * @param array $moduleTypeArr 模块类型
     * @return array $valueAndUnit      大小和单位和真实值
     */
    private function getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, $taskType1, $taskType2, $taskType3, $submoduleType)
    {
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        if(($submoduleType != 0 ) || (array(xphp_get_config('module', 'MODULE_TYPE')['OS'])  == $moduleTypeArr && $submoduleType == 0)){
            $sql = "select 
                sum(total_object_valid_size) as size from bd_history_task 
                where module_type in (?) and task_type not in (?,?,?) and submodule_type = ? and start_time >= ? and finish_time < ?";
            $sqlParams = array(implode(',', $moduleTypeArr), $taskType1, $taskType2, $taskType3, $submoduleType, $todayStart, $todayEnd);
        }else{
            $sql = "select 
                sum(total_object_valid_size) as size from bd_history_task 
                where module_type in (?) and task_type not in (?,?,?) and start_time >= ? and finish_time < ?";
            $sqlParams = array(implode(',', $moduleTypeArr), $taskType1, $taskType2, $taskType3, $todayStart, $todayEnd);
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $size = $data[0]['size'];
        $valueAndUnit = v1_calsize_to_value_and_unit($size, true);
        if (empty($size)) {
            $size = 0;
        }
        $valueAndUnit['size'] = $size;

        return $valueAndUnit;
    }

    /**
     * 获取受保护的客户端主机个数
     * @param unknown $moduleType
     * @param unknown $taskType
     * @return number
     */
    public function pGetProtectClient($moduleType, $taskType,$submodule = null)
    {
        //受保护主机个数
        $sql = "select count(distinct btal.agent_uuid) as total 
                from bd_task bt,bd_task_agent_list btal 
                where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        $sqlParams = array(
            xphp_get_config('app', 'FLAG')['UNSET'],
            $moduleType,
            $taskType,
        );
        if (!empty($submodule) || (xphp_get_config('module', 'MODULE_TYPE')['OS'] == $moduleType && $submodule == 0)) {
            $sql .= ' and bt.sub_module_type = ?';
            $sqlParams = array_merge($sqlParams, array($submodule));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        return !empty($data[0]['total']) ? intval($data[0]['total']) : 0;
    }

    /**
     * 获取数据库模块统计信息
     * @param unknown $modules
     * agent_type == 3 是nas和其它主机去区分，查找主机要排除agent_type == 3
     */
    private function getDbViewInfo()
    {
        $info = array('show' => true);
        //有哪些数据库类型
        $sql = "select app_type from bd_agent_app group by app_type";
        $data = $this->dbSelect($sql);
        $databaseTypeArr = array();
        foreach ($data as $d) {
            $databaseTypeArr[] = $d['app_type'];
        }
        $info['database_type'] = $databaseTypeArr;

        //主机总数
        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4, 5);";
        $data = $this->dbSelect($sql);
        $info['total_database_num'] = $data[0]['num'];

        //受保护主机个数
        $info['protected_database_num'] = $this->pGetProtectClient(
            xphp_get_config('module', 'MODULE_TYPE')['DB'],
            xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']
        );

        //数据库备份数据
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['DB'];
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleType);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

        //数据库累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['DB']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        $todayInfo = array();
        //当日备份数据:数据库,按模块获取
        $moduleTypeArr = array(xphp_get_config('module', 'MODULE_TYPE')['DB']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 0, 17, 29, 0);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量

        //当日副本数据:数据库
        $taskTypeArr = array($taskTypeConf['DB_BACKUP_COPY']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['copy_data'] = $valueAndUnit['size'];            //副本数据真实量

        //当日归档数据:数据库暂无归档

        $todayProtectData = $todayInfo['backup_data'] + $todayInfo['copy_data'];
        $valueAndUnit = v1_calsize_to_value_and_unit($todayProtectData, true);

        //数据库今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        //数据库副本数据
        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['DB']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }

        //数据库暂无归档

        return $info;
    }

    /**
     * 获取文件模块统计信息
     * @param array $modules 模块信息
     */
    private function getFsViewInfo()
    {
        $info = array('show' => true);
        //主机总数
        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4, 5);";
        $data = $this->dbSelect($sql);
        $info['total_file_num'] = $data[0]['num'];

        $submodule = xphp_get_config('module', 'SUBMODULE_TYPE')['FS'];

        //受保护主机个数
        $info['protected_file_num'] = $this->pGetProtectClient(
            xphp_get_config('module', 'MODULE_TYPE')['FS'],
            xphp_get_config('task', 'TASKTYPE')['BACKUP'],
            $submodule
        );

        //文件备份数据
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        $submodule = xphp_get_config('module', 'SUBMODULE_TYPE')['FS'];
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleType,$submodule);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

        //文件累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['FS']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        $todayInfo = array();
        //当日备份数据:文件,按模块获取
        $moduleTypeArr = array(xphp_get_config('module', 'MODULE_TYPE')['FS']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 2, 0, 0, 0);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量

        //当日副本数据:文件
        $taskTypeArr = array($taskTypeConf['FILE_BACKUP_COPY']);
        $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        $todayInfo['copy_data'] = $valueAndUnit['size'];            //副本数据真实量

        //当日归档数据:文件暂无归档

        $todayProtectData = $todayInfo['backup_data'];
        $valueAndUnit = v1_calsize_to_value_and_unit($todayProtectData, true);

        //文件今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        //文件副本数据
        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['FS']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }

        //文件暂无归档

        return $info;
    }

    /**
     * 获取nas模块统计信息
     * @param array $modules 模块信息
     */
    private function getNasViewInfo()
    {
        $info = array('show' => true);
        //NAS设备总数
        $sql = "select count(id) as num from nas_storage_resource;";
        $data = $this->dbSelect($sql);
        $info['total_nas_num'] = $data[0]['num'];

        //受保护主机个数
        $sql = "select count(distinct nt.nas_uuid) as total 
                from bd_task bt, nas_task nt where bt.task_uuid = nt.task_uuid  
                and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ?";
        $sqlParams = array(
            xphp_get_config('app', 'FLAG')['UNSET'],
            xphp_get_config('module', 'MODULE_TYPE')['NAS'],
            xphp_get_config('task', 'TASKTYPE')['BACKUP'],
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $info['protected_nas_num'] = $data[0]['total'];

        //NAS备份数据
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['NAS'];
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleType);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

        //NAS累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['NAS']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        $todayInfo = array();
        //当日备份数据:NAS,按模块获取
        $moduleTypeArr = array(xphp_get_config('module', 'MODULE_TYPE')['NAS']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 2, 0, 0, 0);
        $todayInfo['backup_data'] = $valueAndUnit['size'];          //备份数据真实量

        //当日副本数据:NAS 这里区分不了，暂无

        //当日归档数据:NAS暂无归档

        $todayProtectData = $todayInfo['backup_data'];
        $valueAndUnit = v1_calsize_to_value_and_unit($todayProtectData, true);

        //NAS今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        //NAS副本数据
        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['NAS']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }
        //NAS暂无归档
        return $info;
    }
    /**
     * 获取整机统计信息
     * @param array $modules 模块信息
     */
    private function getCompleteOSViewInfo()
    {
        $info = array('show' => true);

        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4, 5);";
        $data = $this->dbSelect($sql);
        //主机总数
        $info['total_os_num'] = $data[0]['num'];
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        $submoduleType = xphp_get_config('module', 'OS_SUBMODULE_TYPE')['MACHINE_OS'];
        //受保护主机个数
        $info['protected_os_num'] = $this->pGetProtectClient(
            $moduleType,
            xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'],
            $submoduleType
        );
        //主机备份数据
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleType,$submoduleType);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

         //整机累计保护数据 
        $protectDataInfo = $this->getProtectDataInfo($moduleType,$submoduleType);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        //当日备份数据:按模块获取
        $moduleTypeArr = array($moduleType);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 36, 49, 50, $submoduleType);
        $todayProtectData = $valueAndUnit['size'];          //备份数据真实量
        $valueAndUnit = v1_calsize_to_value_and_unit($todayProtectData, true);

        //操作系统今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        //整机副本数据
        $valueAndUnit = $this->getModuleCopyData($moduleType, $submoduleType);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }

        //整机归档数据
        $sqlOs = "select sum(write_size) as size, count(id) as num 
                from bd_backup_timepoint where task_type in (?) and sub_module_type = ? and module_type = ?";
        $data = $this->dbSelect(
            $sqlOs,
            array(
                xphp_get_config('task', 'TASKTYPE')['ARCHIVE'],
                $submoduleType,
                $moduleType
            )
        );
        $valueAndUnit = v1_calsize_to_value_and_unit($data[0]['size'], true);
        //如果没有归档,archive就没有
        if ($data[0]['num']) {
            //有时间点，给copy赋值
            $info['archive'] = array(
                'archive_data' => $valueAndUnit['value'],      //归档数据
                'archive_data_unit' => $valueAndUnit['unit'],  //归档数据单位
                'archive_point' => $data[0]['num'],       //归档点
            );
        }
        return $info;
    }
    

    /**
     * 获取操作系统模块统计信息
     * @param array $modules 模块信息
     */
    private function getOsViewInfo()
    {
        $info = array('show' => true);
        //主机总数
        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4, 5);";
        $data = $this->dbSelect($sql);
        $info['total_os_num'] = $data[0]['num'];
        //操作系统备份数据
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        $submoduleType = 0;
        //受保护主机个数
        $info['protected_os_num'] = $this->pGetProtectClient(
            $moduleType,
            xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'],
            $submoduleType
        );
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleType,$submoduleType);
        $info['backup_data'] = $valueAndUnit['value'];     //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

        //操作系统累计保护数据
        $protectDataInfo = $this->getProtectDataInfo($moduleType,$submoduleType);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        // $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        // $todayInfo = array();
        //当日备份数据:操作系统,按模块获取
        $moduleTypeArr = array($moduleType);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 36, 49, 50, $submoduleType);
        $todayProtectData = $valueAndUnit['size'];          //备份数据真实量

        // $taskTypeArr = array($taskTypeConf['OS_BACKUP_COPY']);
        // $valueAndUnit = $this->getTodaySomeTaskTypeTimepointWriteSize($taskTypeArr);
        // $todayInfo['copy_data'] = $valueAndUnit['size'];            //副本数据真实量

        $valueAndUnit = v1_calsize_to_value_and_unit($todayProtectData, true);

        //操作系统今日累计保护数据
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        //操作系统副本数据
        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['OS'],$submoduleType);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }

        //操作系统归档数据
        $sqlOs = "select sum(write_size) as size, count(id) as num 
                from bd_backup_timepoint where task_type in (?) and sub_module_type = ? and module_type = ?";
        $data = $this->dbSelect(
            $sqlOs,
            array(
                xphp_get_config('task', 'TASKTYPE')['ARCHIVE'],
                $submoduleType,
                xphp_get_config('module')['MODULE_TYPE']['OS']
            )
        );
        $valueAndUnit = v1_calsize_to_value_and_unit($data[0]['size'], true);
        //如果没有归档,archive就没有
        if ($data[0]['num']) {
            //有时间点，给copy赋值
            $info['archive'] = array(
                'archive_data' => $valueAndUnit['value'],      //归档数据
                'archive_data_unit' => $valueAndUnit['unit'],  //归档数据单位
                'archive_point' => $data[0]['num'],       //归档点
            );
        }
        return $info;
    }

    /**
     * 获取卷cdp模块统计信息
     * @param array $modules 模块信息
     */
    private function getVolcdpViewInfo()
    {
        $module = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $subModule = xphp_get_config('module', 'OS_SUBMODULE_TYPE')['OS'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        //主机总数
        $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4, 5);";
        $data = $this->dbSelect($sql);
        $info['total_cdp_num'] = $data[0]['num'];

        //受保护主机个数
        $info['protected_cdp_num'] = $this->getCDPProtectHostNum($module,1,$taskType);

        //主机备份数据
        $hostBackupData = $this->pGetCDPProtectData(1);
        $info['backup_data'] = v1_calsize_to_value_and_unit($hostBackupData['size'], true)['value'];    //备份数据总量
        $info['backup_data_unit'] = v1_calsize_to_value_and_unit($hostBackupData['size'], true)['unit'];  //备份数据总量的单位

        //累计实时容灾数据
        $protectDataInfo = $this->getProtectDataInfo($module,$subModule,$taskType);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        //今日实时容灾累计保护数据
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sql = "select sum(cvbamldf.data_flow) as size from cdp_vol_backup_agent_minute_level_data_flow  cvbamldf, cdp_vol_backup_agent cvba
                where cvbamldf.backup_agent_id  = cvba.id and cvba.dev_type = 1 and cvba.copy_flag = 0 and cvbamldf.backup_start_timestamp >= ? and cvbamldf.backup_end_timestamp < ?";
        //copy_flag = 0 表示是备份数据 1是复制数据
        $sqlParams = array($todayStart, $todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $size = $data[0]['size'];
        $valueAndUnit = v1_calsize_to_value_and_unit($size, true);
        if (empty($size)) {
            $size = 0;
        }
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        return $info;
    }

    //获取实时受保护主机个数 --整机实时、整机卷、整机复制、卷复制
    private function getCDPProtectHostNum($module,$devType,$taskType){
        $sql = "SELECT count(bt.task_uuid) as total from bd_task bt, cdp_vol_task cvt
                where bt.task_uuid = cvt.task_uuid and cvt.dev_type = ? and bt.delete_flag = ? and bt.task_type = ? and bt.module_type = ? ";
        $sqlParams = array(
            $devType,
            xphp_get_config('app', 'FLAG')['UNSET'],
            $taskType,
            $module,
        );
        $data = $this->dbSelect($sql, $sqlParams);
        return $data[0]['total'];
    }
    /**
     * 获取整机实时统计信息
     * @param array $modules 模块信息
     */
    private function getCDPCompleteMachineViewInfo(){

        $module = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $subModule = xphp_get_config('module', 'OS_SUBMODULE_TYPE')['MACHINE_OS'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
         //主机总数
         $sql = "select count(id) as num from bd_agent where agent_type not in (3, 4, 5);";
         $data = $this->dbSelect($sql);
         $info['total_cdp_num'] = $data[0]['num'];
         //受保护主机数 联合cdp_vol_task 来区分
         
        //受保护主机个数
        $info['protected_cdp_num'] = $this->getCDPProtectHostNum($module,2,$taskType);

        //主机备份数据
        $hostBackupData = $this->pGetCDPProtectData(2);
        $info['backup_data'] = v1_calsize_to_value_and_unit($hostBackupData['size'], true)['value'];    //备份数据总量
        $info['backup_data_unit'] = v1_calsize_to_value_and_unit($hostBackupData['size'], true)['unit'];  //备份数据总量的单位
        //累计保护数据 
        $protectDataInfo = $this->getProtectDataInfo($module,$subModule,$taskType);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        //今日备份数据
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sql = "select sum(cvbamldf.data_flow) as size from cdp_vol_backup_agent_minute_level_data_flow  cvbamldf, cdp_vol_backup_agent cvba
                where cvbamldf.backup_agent_id  = cvba.id and cvba.dev_type = 2 and cvba.copy_flag = 0 and cvbamldf.backup_start_timestamp >= ? and cvbamldf.backup_end_timestamp < ?";
        $sqlParams = array($todayStart, $todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $size = $data[0]['size'];
        $valueAndUnit = v1_calsize_to_value_and_unit($size, true);
        if (empty($size)) {
            $size = 0;
        }
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        return $info;
    }
    /**
     * 获取整机复制统计信息
     * @param array $modules 模块信息
     */
    private function getRepCompleteOsViewInfo(){
        $info = array();
        $module = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $subModule = xphp_get_config('module', 'OS_SUBMODULE_TYPE')['MACHINE_OS'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'];

        //获取受保护对象个数
        $info['protected_host_num'] = $this->getCDPProtectHostNum($module,2,$taskType);
        //复制任务数
        $sql = "select count(bt.task_uuid) as total from bd_task bt,cdp_vol_task cvt where bt.task_uuid = cvt.task_uuid and cvt.dev_type = 2 and bt.module_type =? and bt.task_type = ? and bt.user_uuid = ? ";
        $data = $this->dbSelect($sql, array(
            $module,
            $taskType,
            xphp_get_user_info()['userUuid'])
        );
        $info['task_num'] = $data[0]['total'];

        //累计保护数据 
        $protectDataInfo = $this->getProtectDataInfo($module,$subModule,$taskType);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        //今日备份数据
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sqlToday = "select 
                sum(bht.total_object_valid_size) as size from bd_history_task bht left join cdp_vol_task cvt on bht.task_uuid = cvt.task_uuid
                where cvt.dev_type = 2 and bht.module_type = ? and bht.task_type = ? and bht.start_time >= ? and bht.finish_time < ?";
        $sqlTodayParams = array($module, $taskType , $todayStart, $todayEnd);
        $dataToday = $this->dbSelect($sqlToday, $sqlTodayParams);
        $size = $dataToday[0]['size'];
        $valueAndUnit = v1_calsize_to_value_and_unit($size, true);
        if (empty($size)) {
            $size = 0;
        }
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        return $info;

    }

    /**
     * 获取卷复制统计信息
     * @param array $modules 模块信息
     */
    private function getRepOsViewInfo(){
        $info = array();
        $module = xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP'];
        $subModule = xphp_get_config('module', 'OS_SUBMODULE_TYPE')['OS'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'];
        //获取受保护对象个数
        $info['protected_host_num'] = $this->getCDPProtectHostNum($module,1,$taskType);
        //复制任务数
        $sql = "select count(bt.task_uuid) as total from bd_task bt,cdp_vol_task cvt where bt.task_uuid = cvt.task_uuid and cvt.dev_type = 1 and bt.module_type =? and bt.task_type = ? and bt.user_uuid = ? ";
        $data = $this->dbSelect($sql, array(
            $module,
            $taskType,
            xphp_get_user_info()['userUuid'])
        );
        $info['task_num'] = $data[0]['total'];

        //累计保护数据 先写假数据
        $protectDataInfo = $this->getProtectDataInfo($module,$subModule,$taskType);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        //今日备份数据
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sqlToday = "select 
                sum(bht.total_object_valid_size) as size from bd_history_task bht left join cdp_vol_task cvt on bht.task_uuid = cvt.task_uuid
                where cvt.dev_type = 1 and bht.module_type = ? and bht.task_type = ? and bht.start_time >= ? and bht.finish_time < ?";
        $sqlTodayParams = array($module, $taskType , $todayStart, $todayEnd);
        $dataToday = $this->dbSelect($sqlToday, $sqlTodayParams);
        $size = $dataToday[0]['size'];
        $valueAndUnit = v1_calsize_to_value_and_unit($size, true);
        if (empty($size)) {
            $size = 0;
        }
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        return $info;

    }
    /**
     * 获取文件复制统计信息
     * @param array $modules 模块信息
     */
    private function getRepFsViewInfo(){
        //获取收保护对象个数
        $module =  xphp_get_config('module', 'MODULE_TYPE')['FILE_COPY'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['FILE_COPY'];
        // 受保护的文件复制对象数量
        $flag = xphp_get_config('app')['FLAG'];
        $sqlProtectSrc = "SELECT
                            COUNT( DISTINCT uuid ) AS total 
                        FROM
                            (
                            SELECT
                                source_uuid AS uuid 
                            FROM
                                sync_task_path_list stpl
                                JOIN bd_task bt ON bt.task_uuid = stpl.task_uuid
                            WHERE
                                bt.delete_flag = {$flag['UNSET']} 
                                AND bt.module_type = {$module} 
                                AND bt.task_type = {$taskType} 
                                AND stpl.source_type != 2 " ;
        $sqlProtectNassrc = "UNION
                            SELECT DISTINCT ip AS uuid FROM nas_storage_resource WHERE nas_uuid IN 
                            (SELECT source_uuid AS nas_uuid FROM sync_task_path_list stpl JOIN bd_task bt ON bt.task_uuid = stpl.task_uuid
                            WHERE stpl.source_type = 2 ) 
                            ) AS combined_uuids;";

        $dataProtect = $this->dbSelect($sqlProtectSrc . $sqlProtectNassrc );
        $info['protect_object_num'] = $dataProtect[0]['total'];
        //复制任务数
        $sql = "select count(task_uuid) as total from bd_task where module_type =? and task_type = ? and user_uuid = ? ";
        $data = $this->dbSelect($sql, array(
            $module,
            $taskType,
            xphp_get_user_info()['userUuid'])
        );
        $info['task_num'] = $data[0]['total'];
        // 累计复制对象数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['FILE_COPY']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        // 今日复制对象数据
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sqlToday = "select 
                sum(total_object_valid_size) as size from bd_history_task 
                where module_type = ? and task_type = ? and start_time >= ? and finish_time < ?";
        $sqlTodayParams = array($module, $taskType , $todayStart, $todayEnd);
        $dataToday = $this->dbSelect($sqlToday, $sqlTodayParams);
        $size = $dataToday[0]['size'];
        $valueAndUnit = v1_calsize_to_value_and_unit($size, true);
        if (empty($size)) {
            $size = 0;
        }
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];
        return $info;
    }
    /**
     * 获取数据库复制统计信息
     * @param array $modules 模块信息
     */
    private function getRepDbViewInfo(){
        //获取收保护主机个数
        $module =  xphp_get_config('module', 'MODULE_TYPE')['DB_CDP'];
        $taskType = xphp_get_config('task', 'TASKTYPE')['CDP_DB_BACKUP'];
        $taskStatus = xphp_get_config('task', 'TASKSTATUS')['RUNNING'];
        //这里方法跟首页一样
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
                        WHERE
                            BT.module_type = ? 
                            AND BT.task_type = ? ";
        $dataProtect = $this->dbSelect($sqlProtect, array(
            $module,
            $taskType,
        ));
        $info['protect_host_num'] = $dataProtect[0]['count'];
        // 复制任务数
        $sql = "select count(task_uuid) as total from bd_task where module_type =? and task_type = ? and user_uuid = ? ";
        $data = $this->dbSelect($sql, array(
            $module,
            $taskType,
            xphp_get_user_info()['userUuid'])
        );
        $info['task_num'] = $data[0]['total'];
        //累计复制数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['DB_CDP']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] =  $protectDataInfo['protect_data_unit'];

        //今日备份数据
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sqlToday = "select 
                sum(bht.total_object_valid_size) as size from bd_history_task bht left join bd_task bt on bht.task_uuid = bt.task_uuid
                where bt.task_status = ? and bht.module_type = ? and bht.task_type = ? and bht.start_time >= ? and bht.finish_time < ?";
        $sqlTodayParams = array($taskStatus, $module, $taskType , $todayStart, $todayEnd);
        $dataToday = $this->dbSelect($sqlToday, $sqlTodayParams);
        $size = $dataToday[0]['size'];
        $valueAndUnit = v1_calsize_to_value_and_unit($size, true);
        if (empty($size)) {
            $size = 0;
        }
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        return $info;

    }
    
    

    /**
     * 获取副本分模块详情信息
     * 虚拟机，文件，操作系统，数据库
     */
    private function getAllModuleCopyData()
    {
        $info = array();

        //按照任务类型来统计副本数据，按照模块类型返回数据
        $copyDataTaskTypes = array(
            xphp_get_config('module', 'MODULE_TYPE')['VM'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],          //虚拟机副本 公有云副本 通过sub_module_type区分
            xphp_get_config('module', 'MODULE_TYPE')['FS'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],     //文件副本
            xphp_get_config('module', 'MODULE_TYPE')['OS'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],       //操作系统副本
            xphp_get_config('module', 'MODULE_TYPE')['DB'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],       //数据库副本
            xphp_get_config('module', 'MODULE_TYPE')['NAS'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],       //NAS副本
            xphp_get_config('module', 'MODULE_TYPE')['PUBLIC_CLOUD'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],       //公有云副本
            xphp_get_config('module', 'MODULE_TYPE')['M365'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],       //m365
            xphp_get_config('module', 'MODULE_TYPE')['HADOOP'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],       //hadoop
            xphp_get_config('module', 'MODULE_TYPE')['OBS'] =>
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],       //obs
        );

        foreach ($copyDataTaskTypes as $key => $value) {
            if ($key == xphp_get_config('module', 'MODULE_TYPE')['VM']) {
                // 虚拟机
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect($sql, array(
                    xphp_get_config('module', 'MODULE_TYPE')['VM'],
                    $value,
                    xphp_get_config('vm', 'VM_SUB_MODULE')['VM']));
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['VM'],
                        $value,
                        xphp_get_config('vm', 'VM_SUB_MODULE')['VM']
                    )
                );
            }else if ($key == xphp_get_config('module', 'MODULE_TYPE')['FS']){
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect(
                    $sql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $value,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['FS'])
                );
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $value,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['FS'])
                );

            }else if ($key == xphp_get_config('module', 'MODULE_TYPE')['PUBLIC_CLOUD']) {
                // 公有云
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect($sql, array(
                    xphp_get_config('module', 'MODULE_TYPE')['VM'],
                    $value,
                    xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']));
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['VM'],
                        $value,
                        xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']
                    )
                );
            }elseif ($key == xphp_get_config('module', 'MODULE_TYPE')['HADOOP']){
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect(
                    $sql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $value,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'])
                );
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $value,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'])
                );

            }elseif ($key == xphp_get_config('module', 'MODULE_TYPE')['OBS']){
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?) and sub_module_type = ?";
                $data = $this->dbSelect(
                    $sql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $value,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'])
                );
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ? and sub_module_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array(
                        xphp_get_config('module', 'MODULE_TYPE')['FS'],
                        $value,
                        xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'])
                );
            } else {
                $sql = "select sum(write_size) as size, count(id) as num 
                        from bd_backup_timepoint where module_type = ? and task_type in (?)";
                $data = $this->dbSelect(
                    $sql,
                    array($key,$value)
                );
                $taskNumSql = "select count(task_uuid) as task_num from bd_task 
                               where module_type = ? and task_type = ?";
                $taskNumSqlData = $this->dbSelect(
                    $taskNumSql,
                    array($key,$value)
                );
            }
            $dataSize = $data[0]['size'];
            $dataNum = $data[0]['num'];
            $taskNum = $taskNumSqlData[0]['task_num'];
            $valueAndUnit = v1_calsize_to_value_and_unit($dataSize, true);
            $valueAndUnit['num'] = $dataNum;     //时间点个数
            $valueAndUnit['task_num'] = $taskNum;  //副本任务个数
            $info[] = array(
                'type' => $key,                             //对应模块类型
                'copy_data' => $valueAndUnit['value'],      //数据总量
                'copy_data_unit' => $valueAndUnit['unit'],  //数据总量的单位
                'copy_point' => $valueAndUnit['num'],       //点个数
                'copy_task_num' => $valueAndUnit['task_num'],    //副本任务个数
            );
        }
        return $info;
    }

    /**
     * 获取副本模块统计信息
     * @param array $modules 模块信息
     */
    private function getCopyViewInfo()
    {
        $info = array('show' => true);
        //副本数据
        $sql = "select sum(write_size) as size, count(id) as num from bd_backup_timepoint where task_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY']));
        $valueAndUnit = v1_calsize_to_value_and_unit($data[0]['size'], true);
        $info['copy_data'] = $valueAndUnit['value'];     //数据总量
        $info['copy_data_unit'] = $valueAndUnit['unit']; //数据总量的单位
        $info['copy_point_num'] = $data[0]['num'];  //点个数

        //累计副本数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        //今日副本累计保护数据
        $taskType1 = xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'];
        $taskType2 = xphp_get_config('task', 'TASKTYPE')['FILE_BACKUP_COPY'];
        $taskType3 = xphp_get_config('task', 'TASKTYPE')['DB_BACKUP_COPY'];
        $taskType4 = xphp_get_config('task', 'TASKTYPE')['OS_BACKUP_COPY'];
        $taskType5 = xphp_get_config('task', 'TASKTYPE')['NAS_BACKUP_COPY'];

        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sql = "select sum(total_object_write_size) as size 
                from bd_history_task where task_type in (?,?,?,?,?) 
                and start_time >= ? and finish_time < ?;";
        $data = $this->dbSelect(
            $sql,
            array($taskType1,$taskType2,$taskType3,$taskType4,$taskType5,$todayStart,$todayEnd)
        );
        $size = $data[0]['size'];
        $valueAndUnit = v1_calsize_to_value_and_unit($size, true);
        $info['protect_data_today'] = $valueAndUnit['value'];          //数据真实量
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];          //数据真实量

        //各模块副本详情，虚拟机，文件，操作系统，数据库
        $info['copy_details'] = $this->getAllModuleCopyData();

        return $info;
    }

    /**
     * 获取数据库cdp模块统计信息
     * @param array $modules 模块信息
     * @return boolean[]|boolean[]|number[]
     */
    private function getDBCDPViewInfo()
    {
        $info = array('show' => true);
        //获取生产主机和备份主机个数
        $sql = "select host_type from cdp_db_host";
        $data = $this->dbSelect($sql);
        $product = 0;   //生产主机
        $stantby = 0;   //备份主机
        foreach ($data as $d) {
            if (intval($d['host_type']) == xphp_get_config('db', 'DB_CDP_HOST_TYPE')['product']) {
                $product++;
            } else {
                $stantby++;
            }
        }
        //获取任务总数和运行个数
        $sql = "select task_status from bd_task where module_type = ? ";
        $data = $this->dbSelect($sql, array(xphp_get_config('module', 'MODULE_TYPE')['OEM_DBCDP']));
        $runNum = 0;
        $taskNum = count($data);
        foreach ($data as $d) {
            if (intval($d['task_status']) == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
                $runNum++;
            }
        }
        //生产主机个数
        $info['product_host'] = $product;
        //备份主机个数
        $info['standby_host'] = $stantby;
        //任务个数
        $info['task_num'] = $taskNum;
        //任务运行个数
        $info['task_run_num'] = $runNum;

        return $info;
    }

    /**
     * 获取office365模块统计信息
     * @param array $modules
     */
    private function getOffice365ViewInfo()
    {
        $info = array('show' => true);
        // m365的类型
        $sql = "select m365_type from m365_backup_timepoint group by m365_type";
        $data = $this->dbSelect($sql, array());
        $m365TypeArr = array();
        foreach ($data as $d) {
            $m365TypeArr[] = $d['m365_type'];
        }
        // m365的类型个数
        $info['office365_type'] = $m365TypeArr;

        // 受保护用户
        $userNum = 0;
        //获取m365所有备份任务
        $sql = "select task_uuid from m365_task where m365_type = 1";
        $taskList =  $this->dbSelect($sql);
        if (!empty($taskList)) {
            foreach ($taskList as $task) {
                //获取每个任务最新的时间点,解析出每个任务的最新时间点的授权用户数，然后相加
                $userNum += (new Index())->getNewestTimepoit($task['task_uuid']);
            }
        }
        //受保护用户
        $info['protected_user_num'] = $userNum;

        //m365备份数据
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['M365'];
        $valueAndUnit = $this->getSomeModuleTypeTimepointWriteSize($moduleType);
        $info['backup_data'] = $valueAndUnit['value'];  //备份数据总量
        $info['backup_data_unit'] = $valueAndUnit['unit']; //备份数据总量的单位

        //m365累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['M365']);
        $info['protect_data'] = $protectDataInfo['protect_data'];
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];

        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        //当日备份数据:M365,按模块获取
        $moduleTypeArr = array(xphp_get_config('module', 'MODULE_TYPE')['M365']);
        $valueAndUnit = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 0, 0, 0,0);
        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        // 副本
        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['M365']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }
        return $info;
    }
    /**
     * 获取k8s模块统计信息
     * @param array $modules
     */
    private function getK8sViewInfo(){
        // 集群总个数
        $module = xphp_get_config('module','MODULE_TYPE')['KUBERNETES'];
        $info = array('show' => true);
        $sql = "select count(id) as total from kube_cluster";
        $data = $this->dbSelect($sql);
        $total = $data[0]['total'];
        //受保护的集群个数
        // 受保护的k8s集群
        $sqlProtect ="select count(distinct kt.cluster_uuid) as total from bd_task bt,kube_task kt where bt.task_uuid = kt.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.task_type = ? and bt.user_uuid = ?";
        $dataProtect = $this->dbSelect($sqlProtect, array(
                xphp_get_config('app', 'FLAG')['UNSET'],
                $module,
                xphp_get_config('task', 'TASKTYPE')['KUBE_BACKUP'],
                xphp_get_user_info()['userUuid'])
        );
        //累计保护数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['KUBERNETES']);
         // 今日保护数据
        $moduleTypeArr = array($module);
        $todayProtectedData = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 57, 0, 0,0);

        $info['totalNum'] = $total;  // 集群总个数
        $info['protectNum'] = $dataProtect[0]['total']; //受保护的集群个数
        $info['backupDataSize'] = $this->pGetProtectData($module);  // 备份数据
        $info['protect_data'] = $protectDataInfo['protect_data'];  // 累计保护
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        $info['protect_data_today'] = $todayProtectedData['value'];  // 今日保护
        $info['protect_data_today_unit'] = $todayProtectedData['unit'];


        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['KUBERNETES']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }
        return $info;
    }

    /**
     * 获取公有云模块统计信息
     * @params unknow $modules
     */
    private function getpublicCloudViewInfo()
    {
        $info = array('show' => true);
        // 公有云的类型
        $publiccloud = "('" . implode("','", xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']) . "')";
        $sql = "SELECT hypervisor_type FROM vm_vcenter WHERE hypervisor_type in {$publiccloud} GROUP BY hypervisor_type";
        $data = $this->dbSelect($sql);
        $publicCloudTypeArr = array();
        foreach ($data as $d) {
            $publicCloudTypeArr[] = $d['hypervisor_type'];
        }
        // publicCloud的类型
        $info['publicCloud_type'] = $publicCloudTypeArr;
        // 实例总个数
        $sql = 'select count(vt.tree_id) as total 
                from vm_tree vt, vm_vcenter vv 
                where vt.type = ? and vt.vcenter_uuid = vv.vcenter_uuid and vv.hypervisor_type 
                in (' . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']) . ')';
        $dataTotal = $this->dbSelect(
            $sql,
            array(xphp_get_config('vm')['VM_TREE_TYPE']['VM'])
        );
        // 受保护实例
        $protectsql = 'select count(distinct vml.machine_id) as protectNum 
                       from bd_task bt,vm_machine_list vml, vm_vcenter vv, vm_tree vt 
                       where bt.task_uuid = vml.task_uuid and  vml.vcenter_uuid = vv.vcenter_uuid 
                       and vml.vm_uuid = vt.uuid 
                       and vml.vcenter_uuid = vt.vcenter_uuid and bt.task_type =? and bt.module_type = ? 
                       and bt.sub_module_type = ?';
        $protectCount = $this->dbSelect(
            $protectsql,
            array(
                xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                xphp_get_config('module', 'MODULE_TYPE')['VM'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']
            )
        );
        // 副本数据
        $copyArchiveSql = "select sum(write_size) as size, count(id) as num 
                           from bd_backup_timepoint where task_type in (?) and sub_module_type = ? and module_type = ?";
        $copyData = $this->dbSelect(
            $copyArchiveSql,
            array(
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'],
                xphp_get_config('module', 'MODULE_TYPE')['VM']
            )
        );
        $copydataSize = $copyData[0]['size'];
        $copydataNum = $copyData[0]['num'];
        $info['copy_data'] = v1_calsize_to_value_and_unit($copydataSize, true);
        $info['copy_num'] = $copydataNum;     //副本时间点个数
        // 归档
        $archiveData = $this->dbSelect(
            $copyArchiveSql,
            array(
                xphp_get_config('task', 'TASKTYPE')['ARCHIVE'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'],
                xphp_get_config('module', 'MODULE_TYPE')['VM']
            )
        );
        $archivedataSize = $archiveData[0]['size'];
        $archivedataNum = $archiveData[0]['num'];
        $info['archive_data'] = v1_calsize_to_value_and_unit($archivedataSize, true);
        $info['archive_num'] = $archivedataNum;     //归档时间点个数
        // 今日保护实例数据
        //当日备份数据:公有云,按模块获取
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sql = 'select sum(total_object_write_size) as size 
                from bd_history_task where module_type in (?) 
                and submodule_type in (' . implode(
                ',',
                xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud']
            ) . ')' . ' 
                and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('task', 'TASKTYPE')['BACKUP'],
            $todayStart,$todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $backupSize = $data[0]['size'];   //备份数据真实量

        //当日副本数据:公有云
        $sql = 'select sum(total_object_write_size) as size 
                from bd_history_task where module_type in (?) 
                and submodule_type in (?) and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'],
            xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],
            $todayStart,$todayEnd
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $copySize = $data[0]['size'];  //副本数据真实量

        //当日归档数据:公有云
        $sql = 'select sum(total_object_write_size) as size 
                from bd_history_task where module_type in (?) 
                and submodule_type in 
                (' . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']) . ')' . ' 
                and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('task', 'TASKTYPE')['ARCHIVE'],
            $todayStart,$todayEnd
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $archiveSize = $data[0]['size'];  //归档数据真实量
        $todayProtectData = $backupSize + $copySize + $archiveSize;
        $valueAndUnit = v1_calsize_to_value_and_unit($todayProtectData, true);

        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        $info['total_publicCloud_num'] = $dataTotal[0]['total'];
        $info['protected_publicCloud_num'] = $protectCount[0]['protectNum'];
        $info['backupDataSize'] = $this->pGetProtectData(
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']
        ); // 实例备份数据

        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['PUBLIC_CLOUD']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }
        return $info;
    }

    /**
     * 获取Hadoop模块统计信息
     */
    private function getHadoopViewInfo()
    {
        $module = xphp_get_config('module','MODULE_TYPE')['FS'];
        $subModule = xphp_get_config('module','SUBMODULE_TYPE')['HADOOP'];
        $info = array('show' => true);

        // 集群总个数
        $sql = "select count(id) as total from hadoop_cluster";
        $data = $this->dbSelect($sql);
        $total = $data[0]['total'];
        //受保护的hadoop集群
        $sqlProtect ="select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.sub_module_type = ? and bt.task_type = ?";
        $dataProtect = $this->dbSelect(
            $sqlProtect, array(
                xphp_get_config('app','FLAG')['UNSET'],
                xphp_get_config('module','MODULE_TYPE')['FS'],
                xphp_get_config('module','SUBMODULE_TYPE')['HADOOP'],
                xphp_get_config('task','TASKTYPE')['BACKUP']
            )
        );
        // 累计保护hadoop数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['HADOOP']);
        // 今日保护hadoop数据
        $moduleTypeArr = array(xphp_get_config('module', 'MODULE_TYPE')['FS']);
        $todayProtectedData = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 2, 0, 0,3);
        // 副本
        $taskTypeConf = xphp_get_config('task', 'TASKTYPE');
        $sql = "select sum(write_size) as size, count(id) as num 
        from bd_backup_timepoint where task_type in (?) and module_type = ? and sub_module_type = ?";
        $data = $this->dbSelect(
            $sql,
            array($taskTypeConf['BACKUP_COPY'],
                xphp_get_config('module', 'MODULE_TYPE')['FS'],
                xphp_get_config('module','SUBMODULE_TYPE')['HADOOP'])
        );

        $info['total'] = $total;  // 集群总个数
        $info['protectedNum'] = $dataProtect[0]['total'];  // 受保护集群
        $info['backupDataSize'] = $this->pGetProtectData($module, $subModule);  // 备份数据
        $info['protect_data'] = $protectDataInfo['protect_data'];  // 累计保护
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        $info['protect_data_today'] = $todayProtectedData['value'];  // 今日保护
        $info['protect_data_today_unit'] = $todayProtectedData['unit'];

        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['HADOOP']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }
        return $info;
    }

    /**
     * 获取Obs模块统计信息
     */
    private function getObsViewInfo()
    {
        $module = xphp_get_config('module','MODULE_TYPE')['FS'];
        $subModule = xphp_get_config('module','SUBMODULE_TYPE')['OBS'];
        $info = array('show' => true);
        // 对象存储数量
        $sql = "select count(id) as total from obs_resource";
        $data = $this->dbSelect($sql);
        $total = $data[0]['total'];
        // 受保护
        $sqlProtect ="select count(distinct btal.agent_uuid) as total from bd_task bt,bd_task_agent_list btal where bt.task_uuid = btal.task_uuid and bt.delete_flag = ? and bt.module_type = ? and bt.sub_module_type = ? and bt.task_type = ?";
        // 累计保护obs数据
        $protectDataInfo = $this->getProtectDataInfo(xphp_get_config('module', 'MODULE_TYPE')['OBS']);
        $dataProtect = $this->dbSelect($sqlProtect, array(
                xphp_get_config('app','FLAG')['UNSET'],
                xphp_get_config('module','MODULE_TYPE')['FS'],
                xphp_get_config('module','SUBMODULE_TYPE')['OBS'],
                xphp_get_config('task','TASKTYPE')['BACKUP']
            )
        );
        // 今日保护obs数据
        $moduleTypeArr = array(xphp_get_config('module', 'MODULE_TYPE')['FS']);
        $todayProtectedData = $this->getTodaySomeModuleTypeTimepointWriteSize($moduleTypeArr, 2, 0, 0, 4);
        $info['total'] = $total;  // 对象存储数量
        $info['protectedNum'] = $dataProtect[0]['total'];  // 受保护
        $info['backupDataSize'] = $this->pGetProtectData($module, $subModule);  // 备份数据
        $info['protect_data'] = $protectDataInfo['protect_data'];  // 累计保护
        $info['protect_data_unit'] = $protectDataInfo['protect_data_unit'];
        $info['protect_data_today'] = $todayProtectedData['value'];  // 今日保护
        $info['protect_data_today_unit'] = $todayProtectedData['unit'];

        // 服务商
        $sql = "select distinct vendor from obs_resource";
        $data = $this->dbSelect($sql);

        $info['vendor'] = array();
        foreach ($data as $d){
            $info['vendor'][] = $d['vendor'];
        }

        //副本
        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['OBS']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }
        return $info;
    }
    
    private function getPrivateCloudViewInfo(){
        $info = array('show' => true);
        //私有云类型
        $privatecloud = "('" . implode("','", xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud']) . "')";
        
        $sql = "SELECT hypervisor_type FROM vm_vcenter WHERE hypervisor_type in {$privatecloud} GROUP BY hypervisor_type";
       
        $data = $this->dbSelect($sql);
        $publicCloudTypeArr = array();
        foreach ($data as $d) {
            $publicCloudTypeArr[] = $d['hypervisor_type'];
        }
        // privateCloud的类型
        $info['privateCloud_type'] = $publicCloudTypeArr;

        //主机总个数 
        $sql = 'select count(vt.tree_id) as total 
            from vm_tree vt, vm_vcenter vv 
            where vt.type = ? and vt.vcenter_uuid = vv.vcenter_uuid and vv.hypervisor_type 
            in (' . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud']) . ')';
        $dataTotal = $this->dbSelect(
            $sql,
            array(xphp_get_config('vm')['VM_TREE_TYPE']['VM'])
        );
        //受保护主机
        $protectsql = 'select count(distinct vml.machine_id) as protectNum 
                from bd_task bt,vm_machine_list vml, vm_vcenter vv, vm_tree vt 
                where bt.task_uuid = vml.task_uuid and  vml.vcenter_uuid = vv.vcenter_uuid 
                and vml.vm_uuid = vt.uuid 
                and vml.vcenter_uuid = vt.vcenter_uuid and bt.task_type =? and bt.module_type = ? 
                and bt.sub_module_type = ?';
        $protectCount = $this->dbSelect(
            $protectsql,
            array(
                xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                xphp_get_config('module', 'MODULE_TYPE')['VM'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD']
            )
        );
        // 副本数据
        $copyArchiveSql = "select sum(write_size) as size, count(id) as num 
                            from bd_backup_timepoint where task_type in (?) and sub_module_type = ? and module_type = ?";
        $copyData = $this->dbSelect(
            $copyArchiveSql,
                array(
                xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'],
                xphp_get_config('module', 'MODULE_TYPE')['VM']
            )
        );
        $copydataSize = $copyData[0]['size'];
        $copydataNum = $copyData[0]['num'];
        $info['copy_data'] = v1_calsize_to_value_and_unit($copydataSize, true);
        $info['copy_num'] = $copydataNum;     //副本时间点个数
        // 归档
        $archiveData = $this->dbSelect(
            $copyArchiveSql,
            array(
                xphp_get_config('task', 'TASKTYPE')['ARCHIVE'],
                xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'],
                xphp_get_config('module', 'MODULE_TYPE')['VM']
            )
        );
        $archivedataSize = $archiveData[0]['size'];
        $archivedataNum = $archiveData[0]['num'];
        $info['archive_data'] = v1_calsize_to_value_and_unit($archivedataSize, true);
        $info['archive_num'] = $archivedataNum;     //归档时间点个数
        // 今日保护主机数据
        //当日备份数据:私有云,按模块获取
        $todayStart = date('Y-m-d') . ' 00:00:00';
        $todayEnd = date('Y-m-d') . ' 23:59:59';
        $sql = 'select sum(total_object_write_size) as size 
                from bd_history_task where module_type in (?) 
                and submodule_type in (' . implode(
                ',',
                xphp_get_config('vm')['VMHYPERVISORGROUP']['privatecloud']
            ) . ')' . ' 
                and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('task', 'TASKTYPE')['BACKUP'],
            $todayStart,$todayEnd);
        $data = $this->dbSelect($sql, $sqlParams);
        $backupSize = $data[0]['size'];   //备份数据真实量
        //当日副本数据:私有云
        $sql = 'select sum(total_object_write_size) as size 
                from bd_history_task where module_type in (?) 
                and submodule_type in (?) and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'],
            xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'],
            $todayStart,$todayEnd
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $copySize = $data[0]['size'];  //副本数据真实量
        //当日归档数据:私有云
        $sql = 'select sum(total_object_write_size) as size 
                from bd_history_task where module_type in (?) 
                and submodule_type in 
                (' . implode(',', xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud']) . ')' . ' 
                and task_type in (?) and  start_time >= ? and finish_time < ?';
        $sqlParams = array(
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('task', 'TASKTYPE')['ARCHIVE'],
            $todayStart,$todayEnd
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $archiveSize = $data[0]['size'];  //归档数据真实量
        $todayProtectData = $backupSize + $copySize + $archiveSize;
        $valueAndUnit = v1_calsize_to_value_and_unit($todayProtectData, true);

        $info['protect_data_today'] = $valueAndUnit['value'];
        $info['protect_data_today_unit'] = $valueAndUnit['unit'];

        $info['total_privateCloud_num'] = $dataTotal[0]['total'];
        $info['protected_privateCloud_num'] = $protectCount[0]['protectNum'];
        $info['backupDataSize'] = $this->pGetProtectData(
            xphp_get_config('module', 'MODULE_TYPE')['VM'],
            xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD']
        ); // 实例备份数据
        $valueAndUnit = $this->getModuleCopyData(xphp_get_config('module','MODULE_TYPE')['PRIVATE_CLOUD']);
        //如果没有副本copy就没有
        if ($valueAndUnit['copy_point'] > 0) {
            //有时间点，给copy赋值
            $info['copy'] = array(
                'copy_data' => $valueAndUnit['copy_data'],              //副本数据
                'copy_data_unit' => $valueAndUnit['copy_data_unit'],    //副本数据单位
                'copy_point' => $valueAndUnit['copy_point'],            //副本点
                'copy_task_num' => $valueAndUnit['copy_task_num']       //副本任务个数
            );
        }

        return $info;
    }

    /**
     * 得到节点名以及状态
     */
    private function getNodeMsg($nodeuuid)
    {
        $sql = "select host_name,node_nickname,ip from bd_node where node_uuid = ?";
        $result = $this->dbSelect($sql, array($nodeuuid));
        $nodeHandler = Node::instance();
        $info = array(
            'node_name' => $result[0]['node_nickname'] ? $result[0]['node_nickname'] : $result[0]['host_name'],
            'ip' => $result[0]['ip'],
            'status' => $nodeHandler->getNodeAllStatus($nodeuuid),
        );
        return $info;
    }

    /**
     * 根据节点获取数据
     * @param unknown $params
     * @param unknown $node_uuid
     * @return array|number[]|unknown[]|unknown[][][]|unknown[][][][]|fetchAll()[]|NULL[]|fetchAll()[][][]
     */
    public function getNodeData($params, $nodeuuid)
    {
        $count = $params['count'];      //本次取的条数
        $this->paramsCheck($count);
        $sql = "select node_uuid, monitor_time, cpu_percentage, ram_percentage, system_load_1, system_load_5,
                system_load_15, iops_read, iops_write, bps_read, bps_write, network_in, network_out, details
                from bd_system_monitor where node_uuid = ?
                order by monitor_time desc
                limit 0,?";

        $result = $this->dbSelect($sql, array($nodeuuid,$count));
        $info = array();
        if (empty($result)) {
            return $info;
        }

        $netData = array(
            'receive' => array(),
            'transmit' => array(),
            'receive_des' => array(),
            'receive_des_unit' => array(),
            'transmit_des' => array(),
            'transmit_des_unit' => array(),
            'time'   => array()
        );
        $bpsData = array(
            'read' => array(),
            'write' => array(),
            'read_des' => array(),
            'read_des_unit' => array(),
            'write_des' => array(),
            'write_des_unit' => array(),
            'time'   => array()
        );
        $iopsData = array(
            'read' => array(),
            'write' => array(),
            'read_des' => array(),
            'read_des_unit' => xphp_get_lang('UI_HOMEPAGE_COUNT_SECOND'),
            'read_des_unit_en' => '/s',
            'write_des' => array(),
            'write_des_unit' => xphp_get_lang('UI_HOMEPAGE_COUNT_SECOND'),
            'write_des_unit_en' => '/s',
            'time'   => array(),
        );
        $systemLoadData = array(
            'load_1' => array(),
            'load_5' => array(),
            'load_15' => array(),
            'time'   => array()
        );
        foreach ($result as $each) {
            $bpsread = v1_calsize_to_value_and_unit($each['bps_read'] * 1024, true);
            $bpswrite = v1_calsize_to_value_and_unit($each['bps_write'] * 1024, true);



            // $bpsData[] = array(
            //     'bpswork' => array(
            //         'read' => $each['bps_read'],
            //         'write' => $each['bps_write'],
            //         'read_des' => $bpsread['value'],
            //         'read_des_unit' => $bpsread['unit'] . '/s',
            //         'write_des' => $bpswrite['value'],
            //         'write_des' => $bpswrite['unit'] . '/s',
            //     ),
            //     'time' => $each['monitor_time'],
            // );

            $bpsData['read'][] = $each['bps_read'];
            $bpsData['write'][] = $each['bps_write'];
            $bpsData['read_des'][] =  $bpsread['value'];
            $bpsData['read_des_unit'][] =  $bpsread['unit'] . '/s';
            $bpsData['write_des'][] =   $bpswrite['value'];
            $bpsData['write_des_unit'][] = $bpswrite['unit'] . '/s';
            $bpsData['time'][] = $each['monitor_time'];

            // $iopsData[] = array(
            //     'iopswork' => array(
            //         'read' => $each['iops_read'],
            //         'write' => $each['iops_write'],
            //         'read_des' => $each['iops_read'],
            //         'read_des_unit' => xphp_get_lang('UI_HOMEPAGE_COUNT_SECOND'),
            //         'read_des_unit_en' => '/s',
            //         'write_des' => $each['iops_write'],
            //         'write_des_unit' => xphp_get_lang('UI_HOMEPAGE_COUNT_SECOND'),
            //         'write_des_unit_en' => '/s',
            //     ),
            //     'time' => $each['monitor_time'],
            // );

            $iopsData['read'][] = $each['iops_read'];
            $iopsData['write'][] = $each['iops_write'];
            $iopsData['read_des'][] =  $each['iops_read'];
            $iopsData['write_des'][] =   $each['iops_write'];
            $iopsData['time'][] = $each['monitor_time'];


            $systemLoadData['load_1'][] =  $each['system_load_1'];
            $systemLoadData['load_5'][] = $each['system_load_5'];
            $systemLoadData['load_15'][] =  $each['system_load_15'];
            $systemLoadData['time'][] = $each['monitor_time'];

            // $systemLoadData[] = array(
            //     'systemloadwork' => array(
            //         'load_1' => $each['system_load_1'],
            //         'load_5' => $each['system_load_5'],
            //         'load_15' => $each['system_load_15'],

            //     ),
            //     'time' => $each['monitor_time'],
            // );
        };

        $nodeInfo = $this->getNodeMsg($result[0]['node_uuid']);
        $info = array(
            'node_uuid' => $result[0]['node_uuid'],
            'node_name' => $nodeInfo['node_name'],
            'node_status' => $nodeInfo['status']['flag'] ? 0 : 1,
            'ip' => $nodeInfo['ip'],
            'cpu_use' => intval($result[0]['cpu_percentage']),
            'memory_use' => intval($result[0]['ram_percentage']),
            // 'netData' => $netData,
            'bpsData' => $bpsData,
            'iopsData' => $iopsData,
            'SystemLoadData' => $systemLoadData,
        );

        return $info;
    }

    /**
     * 节点统计
     * @param array $params
     */
    public function getNodeMonitor($params)
    {
        //得到所有节点
        $nodeListJson = (new SystemMonitor())->getNodeUUid();
        $nodeList = json_decode($nodeListJson, true);
        $info = array();
        foreach ($nodeList as $eachNode) {
            $eachnodeuuid = $eachNode['node_uuid'];
            $data = $this->getNodeData($params, $eachnodeuuid);
            $info['list'][] = $data;
        }

        return $info;
    }

    /**
     * 除了概览以外的数据
     */
    public function getOtherView($params)
    {
        $info = array();
        //先获取系统授权的模块
        // $modules = $this->getLicenseModuleInfo();
        $info['vm_data'] = $this->getVmViewInfo();
        $info['publicCloud_data'] = $this->getpublicCloudViewInfo();
        $info['privateCloud_data'] = $this->getPrivateCloudViewInfo();
        //整机
        $info['completeMachine_data'] = $this->getCompleteOSViewInfo();
        //卷
        $info['os_data'] = $this->getOsViewInfo();
        $info['file_data'] = $this->getFsViewInfo();
        $info['nas_data'] = $this->getNasViewInfo();
        $info['obs_data'] = $this->getObsViewInfo();
        $info['hadoop_data'] = $this->getHadoopViewInfo();
        $info['office365_data'] = $this->getOffice365ViewInfo();
        //k8s
        $info['k8s_data'] = $this->getK8sViewInfo();
        $info['database_data'] = $this->getDbViewInfo();

        //连续数据保护
        $info['cdp_completeMachine_data'] = $this->getCDPCompleteMachineViewInfo();
        $info['cdp_data'] = $this->getVolcdpViewInfo();
        //数据复制
        $info['replication_completeMachine_data'] = $this->getRepCompleteOsViewInfo();
        $info['replication_os_data'] = $this->getRepOsViewInfo();
        $info['replication_fs_data'] = $this->getRepFsViewInfo();
        $info['replication_db_data'] = $this->getRepDbViewInfo();


        // $info['copy_data'] = $this->getCopyViewInfo();
        // $info['dbcdp_data'] = $this->getDBCDPViewInfo();
        return $info;
    }

    /**
     * 获取虚拟化中心列表
     */
    private function getVcenters()
    {
        $sql = "select distinct hypervisor_type from vm_vcenter";
        $data = $this->dbSelect($sql);
        $info = array();
        foreach ($data as $d) {
            $info[] = array(
                'hypervisor' => intval($d['hypervisor_type']),
                'text' => xphp_get_config('vm', 'VMHYPERVISORDES')[intval($d['hypervisor_type'])]
            );
        }

        return $info;
    }

    /**
     * 获取存储列表
     */
    private function getStorages()
    {
        $sql = "select bsr.storage_nickname, bsr.storage_uuid from bd_storage_resource bsr, bd_node bn 
                where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? ";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'FLAG')['UNSET']));
        $info = array();
        foreach ($data as $d) {
            $info[] = array(
                'storage_uuid' => $d['storage_uuid'],
                'storgae_name' => $d['storage_nickname']
            );
        }
        return $info;
    }

    /**
     * 获取节点列表
     */
    private function getNodes()
    {
        $sql = "select ip, node_uuid, node_type from bd_node order by node_type asc";
        $data = $this->dbSelect($sql);
        $info = array();
        $nodeHandler = Node::instance();
        foreach ($data as $d) {
            $nodeInfo = $nodeHandler->getNodeAllStatus($d['node_uuid']);
            if (!$nodeInfo['flag']) {
                continue;
            }
            $name = $d['ip'];
            if ($d['node_type'] == 1) {
                $name = xphp_get_lang('UI_PALTFORM_MASTER_NODE') . '(' . $d['ip'] . ')';
            }

            $info[] = array(
                'name' => $name,
                'node_uuid' => $d['node_uuid']
            );
        }

        return $info;
    }

    /**
     * 得到灾备中心概况/运行时间&备份数据&功能选项&虚拟机使用统计&备份存储使用统计
     */
    public function getDataSurvey()
    {
        $info = array();
        $survey = array(
            'systemTime' => date('Y/m/d H:i:s', (new Time())->getSystemTime()),
            'licenseInfo' => (new Index())->getSystemLisenceInfo(),
            'totalBackupData' => (new homePage())->getAccumulatedData(''),
        );

        $info = array(
            'survery' => $survey,
            'vcenter_select' => $this->getVcenters(),
            'storage_select' => $this->getStorages(),
            'node_select' => $this->getNodes(),
        );

        return $info;
    }

    /**
     * 获得大屏可视化的语言
     */
    public function getVisualLang()
    {
        $sql = "select language from bd_user";
        $result = $this-> dbSelect($sql);
        return array(
            'lang' => $result[0]['language']
        );
    }

    /**
     * 记录刷新日志
     */
    public function writeRefreshLog()
    {
        // 构建文件路径
        $filepath = xphp_get_config('log', 'LOG_INFO')['PATH'];
        $filename = $filepath . 'refresh_time-' . date('Y-m-d') . '.log';
        $date = date('Y-m-d H:i:s');
        if (!file_exists($filename)) {
            $addFile = 'touch ' . $filename;
            exec($addFile);
            // 删除日志，保证只有15个，按照时间顺序排序
            $cmd = 'ls -tr ' . $filepath . 'refresh_time-*';
            exec($cmd, $output);
            $count = count($output);
            // 保留90天，一天一个文件，保留90个
            if ($count > 90) {
                $row = $count - 90;
                // 删除90个之前的日志文件
                for ($i = 0; $i < $row; $i++) {
                    if (file_exists($output[$i])) {
                        // 这里采用unlink，不采用rm -rf， 避免风险
                        unlink($output[$i]);
                    }
                }
            }
        }
        $count = file_put_contents($filename, $date . "\n", FILE_APPEND | LOCK_EX);
        if ($count > 0) {
            return array(
                'refreshFlag' => true
            );
        }
        return array(
            'refreshFlag' => false
        );
    }
}
