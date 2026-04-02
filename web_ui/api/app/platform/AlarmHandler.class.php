<?php
/*******************************************
** 告警管理处理类
**
** @author       xiezhuowei@vinchin.com
** @date         2016-06-25 下午14:43:12
** @version      1.0.0
** @copyright    Copyright 2015-2016 vinchin.com
********************************************/
class AlarmHandler extends OPHandler{

    /**
     * 获取系统告警表格信息
     * @param unknown $params
     */
    public function getSystemAlarms($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $search = $params['search'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $level = $search['level'];
        $sortArr = array('', '', 'alarm_level', 'alarm_time', '', 'solved_flag', '');

        $sql = "select system_alarm_id, alarm_level, description_key, description_param, unix_timestamp(alarm_time) alarm_time, solved_flag, error_code 
                from bd_system_alarm where system_alarm_id is not null";
        $sqlCount = "select count(system_alarm_id) as total from bd_system_alarm where system_alarm_id is not null";

        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array();
        $sqlCountParams = array();
        if(!empty($level) && !$accurateFlag ){
            $sql .= " and alarm_level = ? ";
            $sqlCount .= " and alarm_level = ? ";
            $sqlParams = array_merge($sqlParams, array($level));
            $sqlCountParams = array_merge($sqlCountParams, array($level));
        }
        if($accurateFlag){
        	$search = $params['search'];
        	$alarmLevel = intval($search['alarmLevel']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];

        	//告警等级
        	if(!empty($alarmLevel)){
        	    $sql .= " and alarm_level = ? ";
        	    $sqlCount .= " and alarm_level = ? ";
        	    $sqlParams = array_merge($sqlParams, array($alarmLevel));
        	    $sqlCountParams = array_merge($sqlCountParams, array($alarmLevel));
        	}

        	//如果填了开始时间范围查询
        	if(!empty($startTime) && !empty($endTime)){
        	    $sql .= " and alarm_time between ? and ? ";
        	    $sqlCount .= " and alarm_time between ? and ? ";
        	    $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
        	    $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
        	}
        }


        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
       	$data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $records = array("data" => array());
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $logHandler = Xphp::instance('LogHandler');
        $utils = Xphp::instance('Utils');
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['system_alarm_id'] . '">',
                ++$start,
                intval($d['alarm_level']),
                $this->parseDate($d['alarm_time']),
                $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'],
                    $d['error_code'], $d['description_key'], $d['description_param']),
                $utils->parseFlagToBool($d['solved_flag']),
                array(
                    'id' => intval($d['system_alarm_id']),
                    'leveldes' => $ptDes['ALARM_LEVEL_DES'][intval($d['alarm_level'])],
                    'solveddes' => $ptDes['ALARM_RESPOND_DES'][intval($d['solved_flag'])],
                ),
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 得到任务告警信息
     * @param unknown $params
     */
    public function getTaskAlarms($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $utils = Xphp::instance('Utils');
        $search = $params['search'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'bta.task_name', 'bta.user_name', 'bta.task_type', 'bta.module_type', 'bta.alarm_level', 'bta.alarm_time', '', 'bta.solved_flag', '');
        $level = $search['level'];
        $name = $search['name'];
        $accurateFlag = $params['accurateFlag'];
        if($accurateFlag){
        	$name = $params['search']['taskName'];
        }
        $name = $utils->escapeWildcard($name);
        $sql = "select bu.user_level,bta.task_alarm_id, bta.task_name,bta.user_name, bta.task_type, bta.module_type, bta.alarm_level, bta.description_key, 
                bta.description_param, unix_timestamp(bta.alarm_time) alarm_time, bta.solved_flag, bta.error_code, bta.submodule_type 
                from bd_task_alarm bta left join bd_user bu on bta.user_uuid = bu.user_uuid  where bta.alarm_level != 1 ";
        $sqlCount = "select count(bta.task_alarm_id) as total from bd_task_alarm bta left join bd_user bu on bta.user_uuid = bu.user_uuid where bta.alarm_level != 1 ";

        $sqlParams = array();
        $sqlCountParams = array();

        if ($_SESSION['isThreePowers']) {
            // 三权模式
            if($_SESSION['userLevel'] == 3){
                //安全管理员
                $sql .= " and (bu.user_level = 5 or bu.user_level = 4) ";
                $sqlCount .= " and (bu.user_level = 5 or bu.user_level = 4) ";
            }else if($_SESSION['userLevel'] == 4){
                //安全审计员
                $sql .= " and (bu.user_level = 2 or bu.user_level = 3) ";
                $sqlCount .= " and (bu.user_level = 2 or bu.user_level = 3) ";
            } else {
                // 关联管理用户判断 告警 - 查看   alarm_look
                $authUser = $_SESSION['authUser']['alarm_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= "and bta.user_uuid in " . $useruuidArr;
                    $sqlCount .= "and bta.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " and bta.user_uuid = ? ";
                    $sqlCount .= " and bta.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
                    $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
                }
            }
        } else {
            //不是全局观察者获取对应用户的任务
            if(!in_array("global_observer", $_SESSION['permission'])){
                // 关联管理用户判断 告警 - 查看   log_look
                $authUser = $_SESSION['authUser']['alarm_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $sql .= "and bta.user_uuid in " . $useruuidArr;
                    $sqlCount .= "and bta.user_uuid in " . $useruuidArr;
                } else {
                    $sql .= " and bta.user_uuid = ? ";
                    $sqlCount .= " and bta.user_uuid = ? ";
                    $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
                    $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
                }
            }
        }

        if($this->checkEmpty($name)){
            $sql .= " and bta.task_name like ? ";
            $sqlCount .= " and bta.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$name.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$name.'%'));
        }

        if(!empty($level) && !$accurateFlag){
            $sql .= " and bta.alarm_level = ? ";
            $sqlCount .= " and bta.alarm_level = ? ";
            $sqlParams = array_merge($sqlParams, array($level));
            $sqlCountParams = array_merge($sqlCountParams, array($level));
        }

        if($accurateFlag){
        	$search = $params['search'];
        	$hypervisor = intval($search['hypervisor']);
        	$dbType = intval($search['dbtype']);
        	$taskType = intval($search['taskType']);
        	$moduleType = intval($search['moduleType']);
            $subModuleType = intval($search['sub_module_type']);
        	$taskName = $search['taskName'];
        	$alarmLevel = intval($search['alarmLevel']);
        	$startTime = $search['startTime'];
        	$endTime = $search['endTime'];
        	$nodeuuid = $search['nodeValue'];
            //虚拟化类型
        	if($moduleType == Xphp::$_config['MODULE_TYPE']['VM'] && !empty($hypervisor)){
        		$sql .= " and bta.submodule_type = ? ";
        		$sqlCount .= " and bta.submodule_type = ? ";
        		$sqlParams = array_merge($sqlParams, array($hypervisor));
        		$sqlCountParams = array_merge($sqlCountParams, array($hypervisor));
         	}
         	//数据库类型
         	if($moduleType == Xphp::$_config['MODULE_TYPE']['DB'] && !empty($dbType)){
         	    $sql .= " and bta.submodule_type = ? ";
         	    $sqlCount .= " and bta.submodule_type = ? ";
         	    $sqlParams = array_merge($sqlParams, array($dbType));
         	    $sqlCountParams = array_merge($sqlCountParams, array($dbType));
         	}

         	//模块类型
         	if(!empty($moduleType)){
                if ($subModuleType == 3) { //公有云
                    $publicCloud =  "(" . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ")";
                    $sql.= " and bta.module_type = ? and bta.submodule_type in  " . $publicCloud . " ";
                    $sqlCount .= " and bta.module_type = ? and bta.submodule_type in ( " . $publicCloud . " ) ";
                    $sqlParams = array_merge($sqlParams, array($moduleType));
         	        $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
                }else if($subModuleType == 1){ // 虚拟机
                    $publicCloud =  "(" . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ")";
                    $vmHypervisor = "(" . implode(',', Xphp::$_config['VMHYPERVISORTYPE']) . ")";
                    $sql .= " and bta.module_type = ? and bta.submodule_type in  " . $vmHypervisor . " and bta.submodule_type not in " . $publicCloud . " ";
                    $sqlCount .= " and bta.module_type = ? and bta.submodule_type in  " . $vmHypervisor . " and bta.submodule_type not in " . $publicCloud . " ";
                    $sqlParams = array_merge($sqlParams, array($moduleType));
                    $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
                }  else {
                    $sql .= " and bta.module_type = ? ";
                    $sqlCount .= " and bta.module_type = ? ";
                    $sqlParams = array_merge($sqlParams, array($moduleType));
                    $sqlCountParams = array_merge($sqlCountParams, array($moduleType));
                }
         	}
            if($taskType == Xphp::$_config['TASKTYPE']['BACKUP']){
                $tasktypeList = array(
                    Xphp::$_config['TASKTYPE']['BACKUP'],
                    Xphp::$_config['TASKTYPE']['DB_BACKUP'],
                    Xphp::$_config['TASKTYPE']['OS_BACKUP'],
                    Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']
                );
            }else if($taskType == Xphp::$_config['TASKTYPE']['RECOVERY']){
                $tasktypeList = array(
                    Xphp::$_config['TASKTYPE']['RECOVERY'],
                    Xphp::$_config['TASKTYPE']['DB_RECOVERY'],
                    Xphp::$_config['TASKTYPE']['OS_RECOVERY'],
                    Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']
                );
            }else if($taskType == Xphp::$_config['TASKTYPE']['BACKUP_COPY']){
                $tasktypeList = array(Xphp::$_config['TASKTYPE']['BACKUP_COPY']);
            }else if($taskType == Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC']){
                $tasktypeList = array(Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC']);
            }else {
                $tasktypeList = array($taskType);
            }
            $tasktypeListDes = implode(',', $tasktypeList);
         	//任务类型
         	if(!empty($taskType)){
                $sql .= " and bta.task_type in (".$tasktypeListDes.") ";
                $sqlCount .= " and bta.task_type in (".$tasktypeListDes.") ";
         	}

         	//告警等级
         	if(!empty($alarmLevel)){
         	    $sql .= " and bta.alarm_level = ? ";
         	    $sqlCount .= " and bta.alarm_level = ? ";
         	    $sqlParams = array_merge($sqlParams, array($alarmLevel));
         	    $sqlCountParams = array_merge($sqlCountParams, array($alarmLevel));
         	}

         	//节点唯一标识
         	if(!empty($nodeuuid)){
         	    $sql .= " and bta.node_uuid = ? ";
         	    $sqlCount .= " and bta.node_uuid = ? ";
         	    $sqlParams = array_merge($sqlParams, array($nodeuuid));
         	    $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
        	}

        	//如果填了开始时间范围查询
        	if(!empty($startTime) && !empty($endTime)){
        	    $sql .= " and bta.alarm_time between ? and ? ";
        	    $sqlCount .= " and bta.alarm_time between ? and ? ";
        	    $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
        	    $sqlCountParams = array_merge($sqlCountParams, array($startTime, $endTime));
        	}
        }


        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $records = array("data" => array());
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $logHandler = Xphp::instance('LogHandler');

        $total = intval($count[0]['total']);
        foreach ($data as $d){
            $des =  $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'],
            $d['error_code'], $d['description_key'], $d['description_param']);
             // 公有云替换描述中的"虚拟机"为"实例"
             if (in_array($d['submodule_type'], Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])) {
                $des = str_replace(Xphp::$_lang['WEB_VM_TREE_OVIRT_FAKE_VM_FOLDER'], Xphp::$_lang['WEB_PLATFORM_DES_INSTANCE'], $des);
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['task_alarm_id'] . '">',
                ++$start,
                $d['task_name'],
                $d['user_name'],
                $this->getModuleTypeDes($d['module_type'], $d['submodule_type'], $d['task_type']),
                $this->getTaskTypeString($d['module_type'], $d['task_type']),
                intval($d['alarm_level']),
                $this->parseDate($d['alarm_time']),
                $des,
                $utils->parseFlagToBool($d['solved_flag']),
                array(
                    'id' => intval($d['task_alarm_id']),
                    'leveldes' => $ptDes['ALARM_LEVEL_DES'][intval($d['alarm_level'])],
                    'solveddes' => $ptDes['ALARM_RESPOND_DES'][intval($d['solved_flag'])],
                ),
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;

        return  json_encode($records);
    }

    /**
     * 得到任务日志模块类型信息
     * @param int $module       模块号
     * @param int $subModule    子模块号
     * @param int $tasktype    任务类型
     * 告警/存储要调用
     */
    public function getModuleTypeDes($module, $subModule = null, $tasktype = null){
        $desConf = Xphp::$_pfdes;
        $des = $desConf['MODULE_TYPE_DES'][$module];
        if($module == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
            if($tasktype){
                if($tasktype == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $tasktype == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']){
                    $des = Xphp::$_lang['UI_PLATFORM_ARCHIVE'];
                }
            }
        }
        if($subModule){
            //添加子模块,暂时添加虚拟机子模块,后期TODO涉及到数据库子模块
            if($module == Xphp::$_config['MODULE_TYPE']['VM']){
//                 $des .= "[" . Xphp::$_config['VMHYPERVISORDES'][$subModule] . "]";
                $vmGroup = Xphp::$_config['VMHYPERVISORGROUP'];
                if (in_array($subModule, $vmGroup['openstack'])) {
                    // 副本没有私有云
                    if($tasktype == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $tasktype == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'] || $tasktype == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $tasktype == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']){
                        $des = Xphp::$_lang['WEB_PLATFORM_DES_VM'];
                    }else{
                        $des = Xphp::$_lang['WEB_PLATFORM_DES_PRIVATE_CLOUD'];
                    }
                } elseif (in_array($subModule, $vmGroup['publiccloud'])) {
                    $des = Xphp::$_lang['WEB_PLATFORM_DES_PUBLIC_CLOUD'];
                } else {
                    $des = Xphp::$_lang['WEB_PLATFORM_DES_VM'];
                }
            }
            //还需添加文件子模块
            if($module == Xphp::$_config['MODULE_TYPE']['FS']){
                if($subModule == Xphp::$_config['SUBMODULE_TYPE']['HADOOP']){
                    $des = "Hadoop";
                }elseif ($subModule == Xphp::$_config['SUBMODULE_TYPE']['OBS']){
                    $des = Xphp::$_lang['UI_PLATFORM_OBS'];
                }
            }
        }
        return $des;
    }

    /**
     * 获取虚拟机子模块
     * @param $module
     * @param $subModule
     * @return int
     */
    public function getSubModuleTypeValue($module, $subModule = null): int
    {
        if ($module == Xphp::$_config['MODULE_TYPE']['VM']) {
            $vmGroup = Xphp::$_config['VMHYPERVISORGROUP'];
            if (in_array($subModule, $vmGroup['openstack'])) {
                return 2;
            } elseif (in_array($subModule, $vmGroup['publiccloud'])) {
                return 3;
            } else {
                return 1;
            }
        }
        return 0;
    }

    /**
     * 得到系统告警详情
     * @param unknown $params
     * @return string
     */
    public function getSystemAlarmDetails($params){
        $id = $params['id'];
        //获取系统告警响应标记
        $solvedFlag = $this->getSystemAlarmSolvedFlag($id);
        if($solvedFlag == Xphp::$_config['FLAG']['UNSET']){
            //打开直接标记为已经响应
            $nowDate = date('Y-m-d H:i:s');
            $sql = "update bd_system_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
            where system_alarm_id = ?";
            $sqlParams = array(Xphp::$_config['FLAG']['SET'], $nowDate, Xphp::$_user['username'], $id);
            $result = $this->dbExec($sql, $sqlParams);
        }
        //获取数据
        $sql = "select system_alarm_id, alarm_level, description_key, description_param, alarm_time, 
                solved_flag, solved_time, solved_username, email_send_flag, sms_send_flag, wechat_send_flag,enterprise_wechat_send_flag as wechat2_send_flag, error_code 
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        $info = array();
        if(!empty($data)){
            $utils = Xphp::instance('Utils');
            $logHandler = Xphp::instance('LogHandler');
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            // 手动推送消息-系统
            $pushParams = [];
            $pushParams['logType'] = Xphp::$_config['LOGTYPE']['SYSTEM'];
            $pushParams['errorCode'] = $data[0]['error_code'];
            $pushParams['desription'] = $data[0]['description_key'];
            $pushParams['descriptionParam'] = $data[0]['description_param'];
            $info = array(
                'alarmid' => $id,
                'alarmlevel' => $ptDes['ALARM_LEVEL_DES'][intval($data[0]['alarm_level'])],
                'alarmlevelflag' => intval($data[0]['alarm_level']),
                'alarmtime' => $data[0]['alarm_time'],
                'alarmcontent' => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['SYSTEM'],
                    $data[0]['error_code'], $data[0]['description_key'], $data[0]['description_param']),
                'systemAlarmContent' => $this->getSystemAlarmContent($pushParams),
                'alarmsolvedflag' => $utils->parseFlagToBool($data[0]['solved_flag']),
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][intval($data[0]['solved_flag'])],
                'alarmsolveduser' => $data[0]['solved_username'],
                'alarmsolvedtime' => $data[0]['solved_time'],
                'alarmemailflag' => $utils->parseFlagToBool($data[0]['email_send_flag']),
                'alarmsmsflag' => $utils->parseFlagToBool($data[0]['sms_send_flag']),
                'alarmwechatflag' => $utils->parseFlagToBool($data[0]['wechat_send_flag']),
                'alarmwechat2flag' => $utils->parseFlagToBool($data[0]['wechat2_send_flag']),
                'alarmemail' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['email_send_flag'])],
                'alarmsms' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['sms_send_flag'])],
                'alarmwechat' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['wechat_send_flag'])],
                'alarmwechat2' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['wechat2_send_flag'])],
                'alarmlevel_push' => $data[0]['alarm_level'],
            );

            $arr = [];
            // 追加处理只返回哪些已经发送了的通知方式
            if ($info['alarmemailflag']) {
                $arr[] = xphp::$_lang['UI_SETTINGS_NOTICE_EMAIL'];
            }
            if ($info['alarmsmsflag']) {
                $arr[] = xphp::$_lang['UI_SETTINGS_NOTICE_SMS'];
            }
            if ($info['alarmwechatflag']) {
                $arr[] = xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'];
            }
            if ($info['alarmwechat2flag']) {
                $arr[] = xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'];
            }

            $info['sendtypes'] = implode(',', $arr);

        }
        return json_encode($info);
    }

    /**
     * 得到任务告警详情
     * @param unknown $params
     */
    public function getTaskAlarmDetails($params){
        $id = $params['id'];
        //获取任务告警是否已响应标记
        $solvedFlag = $this->getTaskAlarmSolvedFlag($id);
        if($solvedFlag == Xphp::$_config['FLAG']['UNSET']){
            //如果是未标记的才标记成已标记，打开详情直接标记为已响应
            $nowDate = date('Y-m-d H:i:s');
            $sql = "update bd_task_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
        where task_alarm_id = ? ";
            $sqlParams = array(Xphp::$_config['FLAG']['SET'], $nowDate, Xphp::$_user['username'], $id);
            $result = $this->dbExec($sql, $sqlParams);
        }
        //获取数据
        $sql = "select task_alarm_id, alarm_level, description_key, description_param, alarm_time, task_uuid,
                task_name, task_type, module_type, submodule_type, node_uuid, node_name, storage_name, 
                solved_flag, solved_time, solved_username, email_send_flag, sms_send_flag,wechat_send_flag,enterprise_wechat_send_flag as wechat2_send_flag, error_code, task_log_path 
                from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        // 是否有任务告警推送  前提:策略自动推送关闭
        $pushSql = "select task_uuid, alarm_type, push_response_type from bd_alarm_push_strategy where auto_push_flag != ? and alarm_type = ?";   //再循环查找，二维数组
        $pushSqlData = $this->dbSelect($pushSql,array(Xphp::$_config['FLAG']['SET'],Xphp::$_config['FLAG']['SET']));
        $info = array();
        if(!empty($data)){
            $utils = Xphp::instance('Utils');
            $logHandler = Xphp::instance('LogHandler');
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            // 手动推送消息-任务
            $pushParams = [];
            $pushParams['logType'] = Xphp::$_config['LOGTYPE']['TASK'];
            $pushParams['errorCode'] = $data[0]['error_code'];
            $pushParams['desription'] = $data[0]['description_key'];
            $pushParams['descriptionParam'] = $data[0]['description_param'];
            $pushParams['logLevel'] = null;
            $pushParams['logId'] = null;
            $pushParams['errorDetail'] = null;
            $pushParams['moduleType'] = $data[0]['module_type'];
            $pushParams['taskName'] = $data[0]['task_name'];

            $des =  $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'],
            $data[0]['error_code'], $data[0]['description_key'], $data[0]['description_param']);
             // 公有云替换描述中的"虚拟机"为"实例"
             if (in_array($data[0]['submodule_type'], Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])) {
                $des = str_replace(Xphp::$_lang['WEB_VM_TREE_OVIRT_FAKE_VM_FOLDER'], Xphp::$_lang['WEB_PLATFORM_DES_INSTANCE'], $des);
            }
            $info = array(
                'alarmid' => $id,
                'alarmlevel' => $ptDes['ALARM_LEVEL_DES'][intval($data[0]['alarm_level'])],
                'alarmlevelflag' => intval($data[0]['alarm_level']),
                'alarmtime' => $data[0]['alarm_time'],
                'alarmcontent' => $des,
                'taskAlarmContent' => $this->getTaskAlarmContent($pushParams),
                'alarmsolvedflag' => $utils->parseFlagToBool($data[0]['solved_flag']),
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][intval($data[0]['solved_flag'])],
                'alarmsolveduser' => $data[0]['solved_username'],
                'alarmsolvedtime' => $data[0]['solved_time'],
                'alarmemailflag' => $utils->parseFlagToBool($data[0]['email_send_flag']),
                'alarmsmsflag' => $utils->parseFlagToBool($data[0]['sms_send_flag']),
                'alarmwechatflag' => $utils->parseFlagToBool($data[0]['wechat_send_flag']),
                'alarmwechat2flag' => $utils->parseFlagToBool($data[0]['wechat2_send_flag']),
                'alarmemail' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['email_send_flag'])],
                'alarmsms' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['sms_send_flag'])],
                'alarmwechat' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['wechat_send_flag'])],
                'alarmwechat2' => $ptDes['ALARM_NOTICE_DES'][intval($data[0]['wechat2_send_flag'])],

                'taskname' => $data[0]['task_name'],
                'taskmodule' => $logHandler->getModuleTypeDes($data[0]['module_type'], $data[0]['submodule_type'], $data[0]['task_type']),
                'tasktype' =>  $this->getTaskTypeString($data[0]['module_type'], $data[0]['task_type']),
                'tasknode' => $data[0]['node_name'],
                'taskstorage' => empty($data[0]['storage_name']) ? Xphp::$_config['NULLSPACE'] : $data[0]['storage_name'],
                'taskmoduletype' => intval($data[0]['module_type']),
                'task_submodule_type' => $this->getModuleTypeDes($data[0]['module_type'], $data[0]['submodule_type'], $data[0]['task_type']),
                'task_submodule_type_value' => $this->getSubModuleTypeValue($data[0]['module_type'], $data[0]['submodule_type']),
                'taskuuid' => $data[0]['task_uuid'],
                'tasktypeindex' => intval($data[0]['task_type']),
                'alarmlevel_push' => $data[0]['alarm_level'],
            );

            $arr = [];
            // 追加处理只返回哪些已经发送了的通知方式
            if ($info['alarmemailflag']) {
                $arr[] = xphp::$_lang['UI_SETTINGS_NOTICE_EMAIL'];
            }
            if ($info['alarmsmsflag']) {
                $arr[] = xphp::$_lang['UI_SETTINGS_NOTICE_SMS'];
            }
            if ($info['alarmwechatflag']) {
                $arr[] = xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'];
            }
            if ($info['alarmwechat2flag']) {
                $arr[] = xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'];
            }

            $info['sendtypes'] = implode(',', $arr);
        }
        return json_encode($info);
    }

    /**
     * 得到任务日志信息
     * @param unknown $params
     */
    public function getTaskAlarmDetailsLogs($params){
        $id = $params['id'];
        $sql = "select node_uuid, task_log_path, alarm_level from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        $info = array();
        if($data){
            $info = array(
                'tasklog' => $this->getTaskLogInfo($data[0]['node_uuid'], $data[0]['task_log_path'], $data[0]['alarm_level'], $params['type'] ?? 0),
            );
        }
        return $this->muOpResult(true, '', '', '', '', $info);
    }

    /**
     * 得到告警日志的信息
     * @param string $nodeuuid
     * @param string $path
     * @param int    $logLeve
     * @param int    $type
     * @return string
     */
    private function getTaskLogInfo($nodeuuid, $path, $logLeve, $type = 0){
        if(empty($path) || empty($nodeuuid) || empty($logLeve)) return '';
        if(intval($logLeve) == Xphp::$_config['LOGLEVEL']['NORMAL']){
            //如果是一般告警,没有日志
            return '';
        }
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $fileContent = '';
        if($msg['result']){
//             $fileContent = $msg['msg']['file_content'];
            $fileContent = str_replace("\n", "\n", $msg['msg']['file_content']);
//             $fileContent = str_replace(" ", "", $fileContent);
        }else{
            if (!$type) {
                $errorMsg = $this->muOpResult(false, Xphp::$_lang['WEB_ALARM_READ_LOG_INFO'], '', 'error', $msg['errorCode']);
                exit($errorMsg);
            } else {
              // 内部调用，不能使用exit
                $errorCode = $msg['errorCode'];
                $msgs = Xphp::$_lang['WEB_ALARM_READ_LOG_INFO'] . Xphp::$_lang['WEB_PUBLIC_FAILURE'];
                if($errorCode > 0){
                    $error = include CONF_PATH . 'error.php';
                    $errorKey = $error['errorCode'][$errorCode];
                    $msgs .= "," . Xphp::$_lang['WEB_OPHANDLER_ERROR_CODE'] . ": #" . $errorCode . ",";
                    $msgs .= Xphp::$_lang['WEB_OPHANDLER_ERROR_DES'] . ": " . $error['errorCodeDes'][$errorKey];
                }
                return $msgs;
            }

        }
        return $fileContent;
    }

    /**
     * 下载任务告警日志
     * @param unknown $params
     */
    public function downLoadTaskLog($params){
        $id = $params['id'];
        if(empty($id)) return false;
        $sql = "select node_uuid, task_log_path, task_name, alarm_time from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        if(empty($data)) return false;
        $path = $data[0]['task_log_path'];
        $nodeuuid = $data[0]['node_uuid'];
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $fileContent = '';
        if($msg['result']){
            $fileContent = str_replace("\n", "\r\n", $msg['msg']['file_content']);
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($fileContent));
            Header("Content-Disposition: attachment; filename=". $data[0]['task_name'] . "_" . $data[0]['alarm_time'] . ".txt");
            return $fileContent;
        }else{
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($fileContent));
            Header("Content-Disposition: attachment; filename=". $data[0]['task_name'] . "_" . $data[0]['alarm_time'] . ".txt");
            return $fileContent;
        }

    }

    /**
     * 删除系统告警
     * @param unknown $params
     */
    public function deleteSystemAlarm($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_system_alarm_delete");
        $id = $params['id'];
        $this->paramsCheck($id);
        $idArray = array();
        foreach ($id as $v){
            $idArray[] = intval($v);
        }

        // 判断下是否在最低删除期限内
        $alamId = "(" . implode(',', $idArray) . ")";
        $data = $this->dbSelect("select unix_timestamp(alarm_time) alarm_time from bd_system_alarm where system_alarm_id in {$alamId}");
        $utils = Xphp::instance("Utils");
        $leastDays = $utils->xphp_get_system_data_safe(6);
        $daysArr = array_column($data, 'alarm_time');
        // 判断下是否符合保留策略
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column($data, 'alarm_time');
        if ($reserveType == 3) {
            // 永久保留，那么不允许删除
            $msg = Xphp::$_lang['WEB_ALARM_DELETE_CAN_NOT_DELETE_TIPS'];
            return $this->muOpResult(
                false,
                Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                $msg
            );
        } else if ($reserveType == 2) {
            // 按时间保留
            if (time() - min($daysArr) < $reserveNum * 24 * 3600) {
                // 那么不允许删除
                $msg = Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS'];
                $msg = str_replace('%S%', $reserveNum, $msg);
                return $this->muOpResult(
                    false,
                    Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                    $msg
                );
            }
        } else if ($reserveType == 1) {
            // 按数量保留
            $countSql = "select *  from bd_system_alarm ORDER BY alarm_time desc limit 0, {$reserveNum}";
            $taskCount = $this->dbSelect($countSql); //删除前数量
            $alarmIdArr = array_column($taskCount, 'system_alarm_id');
            $taskCount = $taskCount[0]['total'];
            foreach($params['id'] as $v){
                if (in_array($v, $alarmIdArr)) {
                    // 那么不允许删除
                    $msg = Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_RESERVE_COUNT_ERROR_TIPS'];
                    $msg = str_replace('%S%', $reserveNum, $msg);
                    return $this->muOpResult(
                        false,
                        Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                        $msg
                    );
                }
            }
        }

        $opName = 'BD_ALARM_OP_SYSTEM_DELETE';
        $msg = array('id_list' => $idArray);
        return $this->unifyMsg($opName, json_encode($msg));
    }

    /**
     * 删除任务告警
     */
    public function deleteTaskAlarm($params){
        $id = $params['id'];
        $tenantFlag = $params['tenantFlag']; //删除租户标志
        //权限检查
        if(!$tenantFlag){
            $roleHandler = Xphp::instance('RoleHandler');
            $roleHandler->pOperationPermissionCheckExit("p_task_alarm_delete");
            $this->paramsCheck($id);
        }
        $idArray = array();
        foreach ($id as $v){
            $idArray[] = intval($v);
        }

        // 关联管理用户判断 存储资源 - 操作 alarm_operate
        $alamId = "(" . implode(',', $idArray) . ")";
        $data = $this->dbSelect("select user_uuid,unix_timestamp(alarm_time) alarm_time from bd_task_alarm where task_alarm_id in {$alamId}");
        $utils = Xphp::instance("Utils");
        $authUser = $_SESSION['authUser']['alarm_operate'] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], array_column($data, 'user_uuid'), $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
        }

        // 判断下是否在最低删除期限内
        $leastDays = $utils->xphp_get_system_data_safe(5);
        $daysArr = array_column($data, 'alarm_time');
        // 判断下是否符合保留策略
        $reserveType = $leastDays[0]; // 保留类型
        $reserveNum = $leastDays[1]; // 保留数
        $daysArr = array_column($data, 'alarm_time');
        if ($reserveType == 3) {
            // 永久保留，那么不允许删除
            $msg = Xphp::$_lang['WEB_ALARM_DELETE_CAN_NOT_DELETE_TIPS'];
            return $this->muOpResult(
                false,
                Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                $msg
            );
        } else if ($reserveType == 2) {
            // 按时间保留
            if (time() - min($daysArr) < $reserveNum * 24 * 3600) {
                // 那么不允许删除
                $msg = Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR_TIPS'];
                $msg = str_replace('%S%', $reserveNum, $msg);
                return $this->muOpResult(
                    false,
                    Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                    $msg
                );
            }
        } else if ($reserveType == 1) {
            // 按数量保留
            $countSql = "select *  from bd_task_alarm ORDER BY alarm_time desc limit 0, {$reserveNum}";
            $taskCount = $this->dbSelect($countSql); //删除前有多少任务告警
            $alarmIdArr = array_column($taskCount, 'task_alarm_id');
            $taskCount = $taskCount[0]['total'];
            foreach($params['id'] as $v){
                if (in_array($v, $alarmIdArr)) {
                    // 那么不允许删除
                    $msg = Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_RESERVE_COUNT_ERROR_TIPS'];
                    $msg = str_replace('%S%', $reserveNum, $msg);
                    return $this->muOpResult(
                        false,
                        Xphp::$_lang['WEB_ALARM_DELETE_LESS_THAN_HALFYEAR_ERROR'],
                        $msg
                    );
                }
            }
        }

        $opName = 'BD_ALARM_OP_TASK_DELETE';
        $msg = array('id_list' => $idArray);
        return $this->unifyMsg($opName, json_encode($msg));
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param string $opName
     * @param json $msg
     * @return string
     */
    private function unifyMsg($opName, $jsonMsg){
        $mbResult = $this->mbPFMsg($opName, $jsonMsg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        $this->writeAlarmLog($opName, json_decode($jsonMsg, true), $mbResult);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    private function writeAlarmLog($opName, $msg, $mbResult){
       $key = "";
       $params = array(count($msg['id_list']));
       if($opName == "BD_ALARM_OP_TASK_DELETE"){
           $key = "SYSTEM_LOG_DELETE_TASK_ALARM";
       }else if($opName == "BD_ALARM_OP_SYSTEM_DELETE"){
           $key = "SYSTEM_LOG_DELETE_SYSTEM_ALARM";
       }

       if($mbResult['result']){
           $this->systemLog($key, $params);
       }else{
           $this->systemLog($key, $params,  Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
       }
    }

    /**
     * 处理系统告警,标记为响应
     * @param unknown $params
     */
    public function solvedSystemAlarm($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_system_alarm_response");
        $ids = implode(',', $params['id']);
        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_system_alarm set solved_flag = ?, solved_time = ?, solved_username = ? 
                where system_alarm_id in ($ids)";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], $nowDate, Xphp::$_user['username']);
        $result = $this->dbExec($sql, $sqlParams);
        $operate = Xphp::$_lang['WEB_ALARM_MARK_RESPONSE'];
        if($result){
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $ext = array(
                'alarmsolvedflag' => true,
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][Xphp::$_config['FLAG']['SET']],
                'alarmsolveduser' => Xphp::$_user['username'],
                'alarmsolvedtime' => $nowDate
            );
        }
        return $this->muOpResult($result, $operate, '', '', '', $ext);
    }

    /**
     * 处理系统告警,标记为未响应
     * @param unknown $params
     */
    public function notSolvedSystemAlarm($params){
        $ids = implode(',', $params['id']);
        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_system_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
        where system_alarm_id in ($ids)";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $nowDate, Xphp::$_user['username']);
        $result = $this->dbExec($sql, $sqlParams);
        $operate = Xphp::$_lang['WEB_ALARM_MARK_NOT_RESPONSE'];
        if($result){
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $ext = array(
                'alarmsolvedflag' => false,
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][Xphp::$_config['FLAG']['UNSET']],
            );
        }
        return $this->muOpResult($result, $operate, '', '', '', $ext);
    }

    /**
     * 处理任务告警,标记为已响应
     * @param unknown $parmas
     */
    public function solvedTaskAlarm($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_task_alarm_response");
        $ids = implode(',', $params['id']);

        if (!in_array('global_write', $_SESSION['permissionArr'])) {
            // 没全局观察者操作权限
            // 关联管理用户判断 存储资源 - 操作 alarm_operate
            $data = $this->dbSelect("select user_uuid from bd_task_alarm where task_alarm_id in ($ids)");
            $utils = Xphp::instance("Utils");
            $authUser = $_SESSION['authUser']['alarm_operate'] ?? [];
            $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], array_column($data, 'user_uuid'), $authUser);
            if (!$checkOperate) {
                // 没权限操作
                exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
            }
        }

        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_task_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
                where task_alarm_id in ($ids)";
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], $nowDate, Xphp::$_user['username']);
        $result = $this->dbExec($sql, $sqlParams);
        $operate = Xphp::$_lang['WEB_ALARM_MARK_RESPONSE'];
        if($result){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $ext = array(
                'alarmsolvedflag' => true,
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][Xphp::$_config['FLAG']['SET']],
                'alarmsolveduser' => Xphp::$_user['username'],
                'alarmsolvedtime' => $nowDate
            );
        }
        return $this->muOpResult($result, $operate, '', '', 0, $ext);
    }

    /**
     * 处理任务告警,标记为未响应
     * @param unknown $parmas
     */
    public function notSolvedTaskAlarm($params){
        $ids = implode(',', $params['id']);
        $nowDate = date('Y-m-d H:i:s');
        $sql = "update bd_task_alarm set solved_flag = ?, solved_time = ?, solved_username = ?
        where task_alarm_id in ($ids)";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $nowDate, Xphp::$_user['username']);
        $result = $this->dbExec($sql, $sqlParams);
        $operate = Xphp::$_lang['WEB_ALARM_MARK_NOT_RESPONSE'];
        if($result){
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $ext = array(
                'alarmsolvedflag' => false,
                'alarmsolveddes' => $ptDes['ALARM_RESPOND_DES'][Xphp::$_config['FLAG']['UNSET']],
            );
        }
        return $this->muOpResult($result, $operate, '', '', '', $ext);
    }

    /**
     * 得到灾备中心告警信息4.0 暂时保留
     * TODO
     * @param unknown $params
     */
    public function getSurveyAlarmInfo($params){
        $info = array(
            "task" => array(
                "warn" => 0,
                "error" => 0
            ),
            "system" => array(
                "warn" => 0,
                "error" => 0
            )
        );
        //系统告警
        $sql = "select count(description_key) as total from bd_system_alarm where solved_flag = ?
            and alarm_level = ? ";
        $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN']);
        $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR']);
        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $info['system']['warn'] = intval($dataWarn[0]['total']);
        $info['system']['error'] = intval($dataError[0]['total']);

        //任务告警
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
            //如果是操作员,获取自己的任务告警
            $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
            and alarm_level = ? and user_uuid = ?";
            $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN'], Xphp::$_user['useruuid']);
            $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR'], Xphp::$_user['useruuid']);
            //如果是操作员 不获取系统告警
            $info['system']['warn'] = 0;
            $info['system']['error'] = 0;
        }else{
            //其他类型用户,获取系统所有的任务告警
            $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
            and alarm_level = ? ";
            $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN']);
            $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR']);
        }
        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $info['task']['warn'] = intval($dataWarn[0]['total']);
        $info['task']['error'] = intval($dataError[0]['total']);

        $info['vcenter'] = 0;
        $info['storage'] = 0;
        $info['lisence'] = 0;
        //检测系统是否授权
        $systemHandler = Xphp::instance('SystemHandler');
        $status = $systemHandler->getSystemAuthorizationStatus();
        if($status != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            $info['lisence'] = 1;
        }
        //检测是否添加了存储设备
        $sql = "select storage_id from bd_storage_resource";
        $count = $this->dbQuery($sql);
        $info['storage'] = $count > 0 ? 0 : 1;
        //检测是否添加了vcenter
        $sql = "select vcenter_id from vm_vcenter";
        $count = $this->dbQuery($sql);
        $info['vcenter'] =$count > 0 ? 0 : 1;

        return json_encode($info);
    }


    /**
     * 得到首页通知信息
     * 左侧菜单提示
     * 顶部消息通知
     * @param unknown $params
     */
    public function getSurveyNoticeInfo($params){
        //系统告警
        $noSureBackupFlag = $params['no_surebackup_flag'] ?? false; // 是否不查询验证任务信息，true不查询，GMP适配
        $sql = "select count(description_key) as total from bd_system_alarm where solved_flag = ?
            and alarm_level = ? ";
        $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN']);
        $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR']);
        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $systemWarn = intval($dataWarn[0]['total']);
        $systemError = intval($dataError[0]['total']);

        $is_global = false;
        // 如果是有全局观察者权限，那么显示所有的数据
        if ((in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 有全局观察者权限
            $is_global = true;
        }
        // 如果是超级管理员 admin 那么不给全局观察权限
        if (Xphp::$_user['useruuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            $is_global = false;
        }

        //任务告警 有全局权限就看所有的，不然就看自己的
        $sql = "select count(description_key) as total from bd_task_alarm where solved_flag = ?
        and alarm_level = ?";
        !$is_global && $sql .= " and user_uuid = ?";

        $warnParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['WARN']);
        $errorParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['LOGLEVEL']['ERROR']);

        !$is_global && $warnParams = array_merge($warnParams, [Xphp::$_user['useruuid']]);
        !$is_global && $errorParams = array_merge($errorParams, [Xphp::$_user['useruuid']]);

        $dataWarn = $this->dbSelect($sql, $warnParams);
        $dataError = $this->dbSelect($sql, $errorParams);
        $taskWarn = intval($dataWarn[0]['total']);
        $taskError = intval($dataError[0]['total']);

        //当前任务
        $sql = "select count(task_uuid) as total from bd_task where delete_flag = ? and task_type != ? ";
        $sqlParam = [Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['TASKTYPE']['ORCH_TASK']];
        !$is_global && $sql .= " and user_uuid = ?";
        !$is_global && $sqlParam = array_merge($sqlParam, [Xphp::$_user['useruuid']]);
        if ($noSureBackupFlag) {
            $sql .= ' and task_type != ? ';
            $sqlParam = array_merge($sqlParam, [Xphp::$_config['TASKTYPE']['SURE_BACKUP']]);
        }
        $data = $this->dbSelect($sql, $sqlParam);

        $currentTask = intval($data[0]['total']);

        //历史任务
        $sql = "select count(task_uuid) as total from bd_history_task ";
        !$is_global && $sql .= " where user_uuid = ?";
        $sqlParam = [];
        !$is_global && $sqlParam = [Xphp::$_user['useruuid']];
        if ($noSureBackupFlag) {
            if(!$is_global){
                $sql .= ' and task_type != ? ';
            }else{
                $sql .= ' where task_type != ? ';
            }

            $sqlParam = array_merge($sqlParam, [Xphp::$_config['TASKTYPE']['SURE_BACKUP']]);
        }
        $data = $this->dbSelect($sql, $sqlParam);
        $historyTask = intval($data[0]['total']);

        //获取当前验证任务
        $sql = "select count(task_uuid) as total from bd_task where delete_flag = ? and task_type = ? ";
        !$is_global && $sql .= " and user_uuid = ?";
        $sqlParam = [Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['TASKTYPE']['SURE_BACKUP']];
        !$is_global && $sqlParam = array_merge($sqlParam, [Xphp::$_user['useruuid']]);
        $data = $this->dbSelect($sql, $sqlParam);
        $verifyTask = intval($data[0]['total']);

        //获取历史验证任务
        $sql = "select count(task_uuid) as total from bd_history_task where task_type = ? ";
        !$is_global && $sql .= " and user_uuid = ?";
        $sqlParam = [Xphp::$_config['TASKTYPE']['SURE_BACKUP']];
        !$is_global && $sqlParam = array_merge($sqlParam, [Xphp::$_user['useruuid']]);
        $data = $this->dbSelect($sql, $sqlParam);
        $verifyHistoryTask = intval($data[0]['total']);


        $info = array(
            "alarm" => array(
                "system" => array(
                    "error" => $systemError,
                    "warn" => $systemWarn,
                ),
                "task" => array(
                    "error" => $taskError,
                    "warn" => $taskWarn,
                )
            ),
            "task" => array(
                "current" => $currentTask,
                "history" => $historyTask,
                "verifycurrent" => $verifyTask,
                "verifyhistory" => $verifyHistoryTask,
            ),
            "vcenter" => 0,
            "storage" => 0,
            "lisence" => 0,
        );

        //检测系统是否授权
        $systemHandler = Xphp::instance('SystemHandler');
        $sql = "SELECT * FROM bd_license";
        $data =  $this->dbSelect($sql);
        if(!empty($data[0])){
            //有数据
            $extension = [];
            $utils = Xphp::instance('Utils');
            $extension = $utils->decrypt($data[0]['extension']);
            $extension = json_decode($extension, true);
            $status = $systemHandler->getSystemAuthorizationStatus();
            if($status != Xphp::$_config['LISENCE_INFO']['authflag']['authorized'] || !in_array('backup_manager', $extension['p'] )){
                $info['lisence'] = 1;
            }
        }else{
            //未授权
            $info['lisence'] = 1;
        }

        //检测是否添加了存储设备
        $sql = "select storage_id from bd_storage_resource";
        $count = $this->dbQuery($sql);
        $info['storage'] = $count > 0 ? 0 : 1;
        //检测是否添加了vcenter
        $sql = "select vcenter_id from vm_vcenter";
        $count = $this->dbQuery($sql);
        $info['vcenter'] =$count > 0 ? 0 : 1;
        //获取系统授权状态
        $systemHandler = Xphp::instance('SystemHandler');
        $authflag = $systemHandler->getSystemAuthorizationStatus();
        $authInfo = Xphp::$_config['LISENCE_INFO']['authflag'];
        if($authflag == $authInfo['authorized']){
            $sql = "select license_type from bd_license";
            $data = $this->dbSelect($sql);
            $licenseType = intval($data[0]['licenseType']);
            //如果授权了判断是否授权虚拟化
            $vmTypeName = array('', 'host', 'cpu', 'storage', 'vm');
            $vmInfo = $systemHandler->getOneModuleLisenceInfo($vmTypeName[$licenseType]);
            if($vmInfo['total'] <= 0){
                $info['vcenter'] = 0;
            }
        }
        $info['systime'] = $this->getSystemTime();

//         $info['system'] = $info['lisence'];
//         $info['resource'] = $info['vcenter'] + $info['storage'];
        //资源管理只控制存储
//         $info['resource'] = $info['storage'];

        $info = $this->checkUserPemission($info);
        return json_encode($info);
    }

    /**
     * 获取系统时间
     * @param unknown $params
     */
    public function getSystemTime(){
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        return strtotime($data[0]);
    }

    /**
     * 得到任务告警的任务类型  现在任务类型数据库和文件用的一样的task_type  这里通过模块类型区分虚拟机和文件
     * @param unknown $module_type
     * @param unknown $task_type
     * @author liushuai@vinchin.com
     * @return string 任务类型
     */
    private function getTaskTypeString($module_type,$task_type){
        $pfDes = Xphp::$_pfdes;
        $task_type_string = "";
        if($module_type == Xphp::$_config['MODULE_TYPE']['VM'] || $module_type == Xphp::$_config['MODULE_TYPE']['FS'] || $module_type == Xphp::$_config['MODULE_TYPE']['NAS'] || $module_type == Xphp::$_config['MODULE_TYPE']['KUBERNETES']){
            if($task_type == Xphp::$_config['TASKTYPE']['BACKUP'] && $module_type == Xphp::$_config['MODULE_TYPE']['VM']){
                $task_type_string = Xphp::$_lang['UI_PLATFORM_BACKUP'];
            }else if($task_type == Xphp::$_config['TASKTYPE']['RECOVERY'] && $module_type == Xphp::$_config['MODULE_TYPE']['VM']){
                $task_type_string = Xphp::$_lang['UI_PLATFORM_RECOVER'];
            }else if($task_type == Xphp::$_config['TASKTYPE']['BACKUP'] && $module_type == Xphp::$_config['MODULE_TYPE']['FS']){
                $task_type_string = Xphp::$_lang['UI_PLATFORM_BACKUP'];
            }else if($task_type == Xphp::$_config['TASKTYPE']['RECOVERY'] && $module_type == Xphp::$_config['MODULE_TYPE']['FS']){
                $task_type_string = Xphp::$_lang['UI_PLATFORM_RECOVER'];
            }else if($task_type == Xphp::$_config['TASKTYPE']['BACKUP'] && $module_type == Xphp::$_config['MODULE_TYPE']['NAS']){
                $task_type_string = Xphp::$_lang['UI_PLATFORM_BACKUP'];
            }else if($task_type == Xphp::$_config['TASKTYPE']['RECOVERY'] && $module_type == Xphp::$_config['MODULE_TYPE']['NAS']){
                $task_type_string = Xphp::$_lang['UI_PLATFORM_RECOVER'];
            } else if ($task_type == Xphp::$_config['TASKTYPE']['KUBE_BACKUP'] && $module_type == Xphp::$_config['MODULE_TYPE']['KUBERNETES']) {
                $task_type_string = Xphp::$_lang['UI_PLATFORM_BACKUP'];
            } else if($task_type == Xphp::$_config['TASKTYPE']['KUBE_RECOVERY'] && $module_type == Xphp::$_config['MODULE_TYPE']['KUBERNETES']){
                $task_type_string = Xphp::$_lang['UI_PLATFORM_RECOVER'];
            } else{
                $task_type_string = $pfDes['TASKTYPEDES'][intval($task_type)];
            }
        }else{
            $task_type_string = $pfDes['TASKTYPEDES'][intval($task_type)];
        }
        return $task_type_string;
    }


    /**
     * 检查用户权限
     * 主要是检查用户是否有权限查看告警(任务告警/系统告警),任务(当前任务/历史任务)
     * @param array $info
     */
    private function checkUserPemission($info){
        $permission = $_SESSION['permission'];
        //检查任务,当前任务和历史任务
        if(!in_array('current_job', $permission)){
            $info['task']['current'] = 0;
        }
        if(!in_array('history_job', $permission)){
            $info['task']['history'] = 0;
        }

        //检查告警,任务告警和系统告警
        if(!in_array('task_alarm', $permission)){
            $info['alarm']['task']['error'] = 0;
            $info['alarm']['task']['warn'] = 0;
        }
        if(!in_array('system_alarm', $permission)){
            $info['alarm']['system']['error'] = 0;
            $info['alarm']['system']['warn'] = 0;
        }

        return $info;
    }




    /****************************************************************************
     * 下面代码是短信和邮件通知接口
     ****************************************************************************/
    /**
     * 发送短信和邮件接口(提供给后台调用)
     * url:https://域名/api/?m=11&f=sendNotice&k=6e24cc40bfdb6963c04a4f1983c8af71&p={"email":"1","sms":"2","type":"1","id":"2771"}
     * @param array $params
     *  mode:1.邮件,2.短信
     *  type:1.任务,2.系统
     *  id:告警id
     */
    public function sendNotice($params){
        $emailFlag = $params['email'];
        $smsFlag = $params['sms'];
        $type = intval($params['type']);
        $id = intval($params['id']);
        if($type == Xphp::$_config['NOTICE_TYPE']['SYSTEM']){
            //发送系统通知
            return $this->sendSystemNotice($emailFlag, $smsFlag, $id);
        }elseif ($type == Xphp::$_config['NOTICE_TYPE']['TASK']){
            //发送任务通知
            return $this->sendTaskNotice($emailFlag, $smsFlag, $id);
        }
    }

    /*
     *从外部调用接口时用post取参-目前主要是监控告警中用到
     *
     */
    public function sendNoticeFromAPI(){
        $params = $_POST;
        if(empty($params)){
            return;
        }
        $emailFlag = $params['email'];
        $smsFlag = $params['sms'];
        $type = intval($params['type']);
        $id = intval($params['id']);
        $this->paramsCheck($emailFlag,$smsFlag,$type,$id);
        $info = array(
            'email' => $emailFlag,
            'sms' => $smsFlag,
            'type' => $type,
            'id' => $id,
        );
        $this->sendNotice($info);
        return;
    }

    /**
     * 发送系统通知
     * @param int $mode
     * @param int $systemAlarmID
     */
    private function sendSystemNotice($emailFlag, $smsFlag, $systemAlarmID){
        $usersHandler = Xphp::instance('UsersHandler');
        $checkResult = $this->checkNoticeSetting($emailFlag, $smsFlag, Xphp::$_config['NOTICE_TYPE']['SYSTEM'], $systemAlarmID);
        $emailFlag = $checkResult['email'];
        $smsFlag = $checkResult['sms'];
        $wechatFlag = $checkResult['wechat'];
        $wechat2Flag = $checkResult['wechat2'];
        $type = Xphp::$_config['NOTICE_TYPE']['SYSTEM'];

        if ($wechatFlag == Xphp::$_config['FLAG']['SET']) {
            // 微信通知发送
            $wechatInfo = $this->getSystemWechatParams($systemAlarmID);
            $wechatResult = $usersHandler->sendTemplate($wechatInfo, $systemAlarmID, 1);
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['WECHAT'], $wechatResult);
        }

        if ($wechat2Flag == Xphp::$_config['FLAG']['SET']) {
            // 企业微信通知发送
            $wechatInfo = $this->getSystemWechatParams($systemAlarmID);
            $wechatResult = $usersHandler->sendTemplate2($wechatInfo, $systemAlarmID, 1);
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['WECHAT2'], $wechatResult);
        }

        if($emailFlag == Xphp::$_config['FLAG']['SET'] && $smsFlag == Xphp::$_config['FLAG']['SET']){
            //邮件短信一起发
            $EmailInfo = $this->getSystemEmailParams($systemAlarmID);
            $SmsInfo = $this->getSystemSmsParams($systemAlarmID);
            $emailResult = $usersHandler->sendEmail($EmailInfo);
            $smsResult = $usersHandler->sendSms($SmsInfo);

            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['EMIAL'], $emailResult);
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['SMS'], $smsResult);

            $emailResult = json_decode($emailResult, true);
            $smsResult = json_decode($smsResult, true);
            $result = false;
            if ($emailResult['re'] && $smsResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_NOTICE_SUCCESS'];
                $result = true;
            }elseif ($emailResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_EMAIL_SUCCESS'];
            }elseif ($smsResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_SMS_SUCCESS'];
            }else{
                $msg = Xphp::$_lang['WEB_ALARM_SEND_NOTICE_FAILURE'];
            }
            return $this->muOpResult($result, Xphp::$_lang['WEB_ALARM_SEND_NOTICE'], $msg, 'error');
        }
        if($emailFlag == Xphp::$_config['FLAG']['SET']){
            //邮件通知
            $EmailInfo = $this->getSystemEmailParams($systemAlarmID);
            $emailResult = $usersHandler->sendEmail($EmailInfo);
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['EMIAL'], $emailResult);
            return $emailResult;
        }
        if($smsFlag == Xphp::$_config['FLAG']['SET']){
            //短信通知
            $SmsInfo = $this->getSystemSmsParams($systemAlarmID);
            $smsResult = $usersHandler->sendSms($SmsInfo);
            $this->updateNoticeResult($systemAlarmID, $type, Xphp::$_config['NOTICE_MODE']['SMS'], $smsResult);
            return $smsResult;
        }
    }

    /**
     * 得到所有管理员的邮件和电话
     * @return array(email=>array, telephone=>array)
     */
    public function getManagerEmailAndTelephone(){
        $sql = "select email, telephone from bd_user where user_type = ? or user_level = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['USERTYPE']['manager'], Xphp::$_config['THREE_POWERS_USER']['sysadmin']));
        $email = array();
        $telephone = array();
        foreach ($data as $d){
            $email[] = $d['email'];
            $telephone[] = $d['telephone'];
        }
        $info = array(
            'email' => $email,
            'telephone' => $telephone
        );
        return $info;
    }

    /**
     * 得到所有的通知邮件,
     * @param array $email 要通知的邮件
     * 在要通知的邮件基础上添加系统配置处的邮件
     */
    public function getAllEmail($email){
        $sql = "select receive_email from bd_email_notice where email_notice_type = 1  ";
        $data = $this->dbSelect($sql, array());
        $setEmail = json_decode($data[0]['receive_email'], TRUE);
        $resultEmail = array_unique(array_merge($email, $setEmail));
        return $resultEmail;
    }

    /**
     * 得到系统邮件通知参数
     * @param int $systemAlarmID
     */
    private function getSystemEmailParams($systemAlarmID){
        $sql = "select alarm_level, description_key, description_param, alarm_time, error_code, system_log_path
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($systemAlarmID));
        $this->checkNoticeData($data);
        $logHandler = Xphp::instance('LogHandler');
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['SYSTEM'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param'], "");
        $managerInfo = $this->getManagerEmailAndTelephone();
        $params = array(
            'email' => $this->getAllEmail($managerInfo['email']),
            'title' => $desription,
            'info' => $this->getSystemEmailBody($data[0]),
            'attachment' => array($data[0]['system_log_path']),
        );
        return $params;
    }

    private function getSystemEmailBody($data){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $logConfig = include CONF_PATH . 'log_system.php';
        $title = $logConfig[$data['description_key']];
        $subTitle = '';
        if ($data['error_code']) {
            $errorConf = include CONF_PATH . 'error.php';
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$data['error_code']]];
            $subTitle = "[#" . $data['error_code'] . "]" . $errorStr;
        }
        if ($data['description_param']) {
            $logHandler = Xphp::instance('LogHandler');
            $param = json_decode($data['description_param'], true);
            $desArr = explode("%s", $title);
            $title = "";
            foreach ($desArr as $k => $v){
                $title .= $v . $logHandler->getEachParamsDes($param[$k],$param, false);
            }
        }

        //获取邮件模板内容并替换
        if(!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], Xphp::$_config['ENTERPRISE'])){
            $info = file_get_contents(ROOT_PATH.'/email/email-system-alarm-oem.html');
        }else if(Xphp::$_config['lang'] == "en-us"){
            $info = file_get_contents(ROOT_PATH.'/email/email-system-alarm-en.html');
        }else{
            $info = file_get_contents(ROOT_PATH.'/email/email-system-alarm.html');
        }
        $reportTime = date('Y-m-d H:i:s');
        $alarmLevelDes = $ptDes['ALARM_LEVEL_DES'][2]; //告警等级为警告
        $info = str_replace('title', $title, $info);
        $info = str_replace('reportTime', $reportTime, $info);
        $info = str_replace('subTitle', $subTitle, $info);
        $info = str_replace('alarmTime', $data['alarm_time'], $info);
        $info = str_replace('alarmLevel', $alarmLevelDes, $info);

        $info = str_replace('backupServerHost', Xphp::instance('SystemHandler')->getMasterNodeIpLink(), $info);
        $info = str_replace('supportEmailHref', 'mailto: '.Xphp::$_config['SYSTEM_INFO']['company_email'], $info);
        $info = str_replace('supportEmail', Xphp::$_config['SYSTEM_INFO']['company_email'], $info);
        return $info;
    }

    /**
     * 得到系统短信通知参数
     * @param int $systemAlarmID
     */
    private function getSystemSmsParams($systemAlarmID){
        $sql = "select alarm_level, description_key, description_param, alarm_time, error_code, system_log_path
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($systemAlarmID));
        $this->checkNoticeData($data);

        $logHandler = Xphp::instance('LogHandler');
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['SYSTEM'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param'], "");
        $managerInfo = $this->getManagerEmailAndTelephone();
        $telephone = implode(',', $managerInfo['telephone']);
        $params = array(
            'tels' => $telephone,
            'msg' => $desription . ". " . date("m-d H:i:s", strtotime($data[0]['alarm_time'])),
        );
        return $params;
    }

    /**
     * 得到系统微信通知参数
     * @param int $systemAlarmID
     */
    private function getSystemWechatParams($systemAlarmID){
        $sql = "select alarm_level, description_key, description_param, alarm_time, error_code, system_log_path
                from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($systemAlarmID));
        $this->checkNoticeData($data);

        $logHandler = Xphp::instance('LogHandler');
        $confDes = $logHandler->getLogConf(Xphp::$_config['LOGTYPE']['SYSTEM']);
        $desStr = $confDes[$data[0]['description_key']];
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['SYSTEM'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param'], "");

        if($data[0]['error_code']){
            $errorConf = include CONF_PATH . 'error.php';
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$data[0]['error_code']]];
            $desStrs = "[#" . $data[0]['error_code'] . "]" . $errorStr;
        }

        if ($data[0]['description_param']) {
            $desStrs = str_replace(["%s", "'", "[", "]"], ['', '', '', ''], $desStr);
            $desStr = $desStrs;
        }

        return array(
            'title' => Xphp::$_lang['UI_PLATFORM_SYSTEM_NOTICE'],
            'desc' => $desStr,
            'task_name' => $desStrs ?? '',
            'content' => $desription,
        );
    }
    /**
     * 发送任务通知
     * @param int $mode
     * @param int $TaskAlarmID
     */
    private function sendTaskNotice($emailFlag, $smsFlag, $taskAlarmID){
        $usersHandler = Xphp::instance('UsersHandler');
        $checkResult = $this->checkNoticeSetting($emailFlag, $smsFlag, Xphp::$_config['NOTICE_TYPE']['TASK'], $taskAlarmID);
        $emailFlag = $checkResult['email'];
        $smsFlag = $checkResult['sms'];
        $wechatFlag = $checkResult['wechat'];
        $wechat2Flag = $checkResult['wechat2'];
        $type = Xphp::$_config['NOTICE_TYPE']['TASK'];

        if ($wechatFlag) {
            // 微信公众号通知发送
            $wechatInfo = $this->getTaskWechatParams($taskAlarmID, 1);
            $wechatResult = $usersHandler->sendTemplate($wechatInfo, $taskAlarmID, 2);
            //file_put_contents(ROOT_PATH . 'log.txt', '发送结果：'.print_r($wechatResult, true), FILE_APPEND);
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['WECHAT'], $wechatResult);
        }

        if ($wechat2Flag) {
            // 企业微信通知发送
            $wechatInfo = $this->getTaskWechatParams($taskAlarmID, 1);
            $wechatResult = $usersHandler->sendTemplate2($wechatInfo, $taskAlarmID, 2);
            //file_put_contents(ROOT_PATH . 'log.txt', '发送结果：'.print_r($wechatResult, true), FILE_APPEND);
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['WECHAT2'], $wechatResult);
        }

        if($emailFlag && $smsFlag){
            //邮件短信一起发
            $EmailInfo = $this->getTaskEmailParams($taskAlarmID);
            $SmsInfo = $this->getTaskSmsParams($taskAlarmID);

            $emailResult = $usersHandler->sendEmail($EmailInfo);
            $smsResult = $usersHandler->sendSms($SmsInfo);

            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['EMIAL'], $emailResult);
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['SMS'], $smsResult);

            $emailResult = json_decode($emailResult, true);
            $smsResult = json_decode($smsResult, true);

            $result = false;
            if ($emailResult['re'] && $smsResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_NOTICE_SUCCESS'];
                $result = true;
            }elseif ($emailResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_EMAIL_SUCCESS'];
            }elseif ($smsResult['re']){
                $msg = Xphp::$_lang['WEB_ALARM_SEND_SMS_SUCCESS'];
            }else{
                $msg = Xphp::$_lang['WEB_ALARM_SEND_NOTICE_FAILURE'];
            }
            return $this->muOpResult($result, Xphp::$_lang['WEB_ALARM_SEND_NOTICE'], $msg, 'error');
        }
        if($emailFlag){
            //邮件通知
            $EmailInfo = $this->getTaskEmailParams($taskAlarmID);
            $emailResult = $usersHandler->sendEmail($EmailInfo);
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['EMIAL'], $emailResult);
            return $emailResult;
        }
        if($smsFlag){
            //短信通知
            $SmsInfo = $this->getTaskSmsParams($taskAlarmID);
            $smsResult = $usersHandler->sendSms($SmsInfo);
            $this->updateNoticeResult($taskAlarmID, $type, Xphp::$_config['NOTICE_MODE']['SMS'], $smsResult);
            return $smsResult;
        }
    }

    /**
     * 得到任务邮件通知参数
     * @param int $taskAlarmID
     */
    private function getTaskEmailParams($taskAlarmID){
        $sql = "select bht.history_uuid, bht.id as history_id, bta.alarm_level, bta.description_key, bta.description_param, bta.alarm_time,
                bta.task_uuid, bta.task_name, bta.task_type, bta.module_type, bta.submodule_type, bta.node_name,
                bta.storage_name, bta.user_name, bta.error_code, bta.task_log_path,
                bu.email, bu.telephone
                from bd_user bu, bd_task_alarm bta left join bd_history_task bht on bht.history_uuid = bta.history_uuid
                where bta.user_uuid = bu.user_uuid
                and bta.task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($taskAlarmID));
        $this->checkNoticeData($data);
        $logHandler = Xphp::instance('LogHandler');
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['TASK'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param'], $data[0]['task_name']);
        //开启防篡改，php没权限访问存储下的文件，调用接口获取文件内容重写到指定文件夹
        $attachment = $this->getRedirectAttach($data[0]['task_log_path'], $data[0]['node_uuid']);
        $params = array(
            'email' => $this->getAllEmail(array($data[0]['email'], $data[2]['email'])),
            'title' => $desription,
            'info' => $this->getTaskEmailBody($data[0]),
            'attachment' => array($attachment),
        );
        return $params;
    }

    /**
     * 得到任务邮件通知email内容
     * @param array $data
     */
    private function getTaskEmailBody($data){
        $info = '';
        //数据验证任务如果是成功或者异常的走获取验证报告接口
//         if($data['task_type'] == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
//             //0 成功 47 异常
//             if(intval($data['error_code']) == 0 || intval($data['error_code']) == 47){
//                 $params = array(
//                     'history_uuid' => $data['history_uuid'],
//                     'taskuuid' => $data['task_uuid']
//                 );
//                 //验证报告邮件通知
//                 $manoeuvreHandler = Xphp::instance('ManoeuvreHandler');
//                 $verifyInfo = $manoeuvreHandler->getVerifyJobReport($params);
//                 $verifyInfo = json_decode($verifyInfo, true);
//                 return $verifyInfo['report'];
//             }
//         }
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        //模块类型
        $moduleDes = $ptDes['MODULE_TYPE_DES'][$data['module_type']];
        if(intval($data['module_type']) == Xphp::$_config['MODULE_TYPE']['VM'] &&
            (intval($data['task_type']) != Xphp::$_config['TASKTYPE']['BACKUP_COPY'] && intval($data['task_type']) != Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH'] &&
                intval($data['task_type']) != Xphp::$_config['TASKTYPE']['ARCHIVE'] && intval($data['task_type']) != Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'])
        ){
            $publicCloudTypes = Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'];
            if (in_array($data['submodule_type'], $publicCloudTypes)) {
                //公有云
                $moduleDes = $ptDes['MODULE_TYPE_DES'][17];
            }
            $moduleDes .= "[" . Xphp::$_config['VMHYPERVISORDES'][$data['submodule_type']] . "]";
        }
        if (intval($data['module_type']) ==  Xphp::$_config['MODULE_TYPE']['FS']) {
            if($data['submodule_type'] == Xphp::$_config['SUBMODULE_TYPE']['HADOOP']){
                $moduleDes = "Hadoop";
            }
            if($data['submodule_type'] == Xphp::$_config['SUBMODULE_TYPE']['OBS']){
                $moduleDes = Xphp::$_lang['WEB_PLATFORM_DES_OBS'];
            }
        }
        if(intval($data['module_type']) ==  Xphp::$_config['MODULE_TYPE']['DB']){
            $moduleDes .= "[" .Xphp::$_config['DB_TYPE_DES'][$data['submodule_type']] . "]";
        }
        if(intval($data['module_type']) ==  Xphp::$_config['MODULE_TYPE']['OS']){
            $moduleDes = Xphp::$_lang['WEB_PLATFORM_DES_VOL'];
            if($data['submodule_type'] == 1){
                $moduleDes = Xphp::$_lang['WEB_MACHINE_OS_MACHINE_TIME'];
            }
        }
        //备份任务才有存储名称字段
        $storage_name = empty($data['storage_name']) ? Xphp::$_config['NULLSPACE'] : $data['storage_name'];

        //获取邮件模板内容并替换
        if(!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], Xphp::$_config['ENTERPRISE'])){
            $info = file_get_contents(ROOT_PATH.'/email/email-task-alarm-oem.html');
        }else if(Xphp::$_config['lang'] == "en-us"){
            $info = file_get_contents(ROOT_PATH.'/email/email-task-alarm-en.html');
        }else{
            $info = file_get_contents(ROOT_PATH.'/email/email-task-alarm.html');
        }
        $reportTime = date('Y-m-d H:i:s');
        $logHandler = Xphp::instance('LogHandler');
        if (Xphp::$_config['ALARM']['notice'] == $data['alarm_level']) {
            if ('BD_TASKLOG_DESC_KEY_TASK_STOPPED' == $data['description_key']) {
                // 任务停止时告警等级为警告
                $taskStatusDes = Xphp::$_lang['WEB_LOG_TASK_TASK_STOPPED'];
                $alarmLevelDes = $ptDes['ALARM_LEVEL_DES'][2]; //告警等级为警告
                $info = str_replace('font-style', 'font-warning', $info);
                $info = str_replace('bg-style', 'bg-warning', $info);
                $info = str_replace('alarm-level-style', 'font-warning', $info);
            } else {
                $taskStatusDes = Xphp::$_lang['WEB_LOG_TASK_TASK_SUCCESS']; // 副标题
                $alarmLevelDes = $ptDes['ALARM_LEVEL_DES'][1]; //告警等级为一般
                //成功状态的字体和背景颜色
                $info = str_replace('font-style', 'font-success', $info);
                $info = str_replace('bg-style', 'bg-success', $info);
                $info = str_replace('alarm-level-style', 'font-normal', $info);
            }
        } elseif (Xphp::$_config['ALARM']['general'] == $data['alarm_level']) {
            $taskStatusDes = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['TASK'], $data['error_code'],
                $data['description_key'], $data['description_param'], '');
            $alarmLevelDes = $ptDes['ALARM_LEVEL_DES'][2]; //告警等级为警告

            $info = str_replace('font-style', 'font-warning', $info);
            $info = str_replace('bg-style', 'bg-warning', $info);
            $info = str_replace('alarm-level-style', 'font-warning', $info);
        } else {
            $errorConf = include CONF_PATH . 'error.php';
            $taskStatusDes = 'BD_TASKLOG_DESC_KEY_TASK_ABNORMAL' == $data['description_key'] ?
                Xphp::$_lang['WEB_LOG_TASK_TASK_ABNORMAL'] :
                Xphp::$_lang['WEB_LOG_TASK_TASK_FAILURE'];
            if ($data['error_code']) {
                $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$data['error_code']]];
                $taskStatusDes .= ' | [#' . $data['error_code'] . ']' . $errorStr;
            }
            $alarmLevelDes = $ptDes['ALARM_LEVEL_DES'][intval($data['alarm_level'])];

            $info = str_replace('font-style', 'font-error', $info);
            $info = str_replace('bg-style', 'bg-error', $info);
            $info = str_replace('alarm-level-style', 'font-error', $info);
        }
        $info = str_replace('title', '['.$data['task_name'].']', $info);
        $info = str_replace('reportTime', $reportTime, $info);

        if (in_array($data['description_key'], [
            'VM_TASK_DESC_KEY_PREPARE_BACKUP_TASK_FOR_VM_DETAIL_ERROR',
            'VM_TASK_DESC_KEY_CREATE_SNANPSHOT_FOR_VM_DETAIL_ERROR',
            'VM_TASK_DESC_KEY_CAL_VM_SIZE_FOR_VM_DETAIL_ERROR',
            'VM_TASK_DESC_KEY_TRANSFER_BACKUP_DATA_FOR_VM_DETAIL_ERROR',
            'VM_TASK_DESC_KEY_CREATE_TIMEPOINT_AND_CLEANUP_ERROR',
        ])) {
            // 单个虚拟机备份的告警
            $subTitle = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['TASK'], $data['error_code'],
                $data['description_key'], $data['description_param'], '');
            $info = str_replace('subTitle', $subTitle, $info);
            $singleVmAlarm = true;
        } else {
            $info = str_replace('subTitle', $taskStatusDes, $info);
            $singleVmAlarm = false;
        }

        $info = str_replace('taskName', $data['task_name'], $info);
        $info = str_replace('taskType', $ptDes['TASKTYPEDES'][$data['task_type']], $info);
        $info = str_replace('moduleType', $moduleDes, $info);
        $info = str_replace('nodeName', $data['node_name'] ?: Xphp::$_config['NULLSPACE'], $info);
        $info = str_replace('storageName', $storage_name, $info);
        $info = str_replace('errorCode', $data['error_code'], $info);
        $info = str_replace('alarmTime', $data['alarm_time'], $info);
        $info = str_replace('alarmLevel', $alarmLevelDes, $info);
        $info = str_replace('backupServerHost', Xphp::instance('SystemHandler')->getMasterNodeIpLink(), $info);
        $info = str_replace('supportEmailHref', 'mailto: '.Xphp::$_config['SYSTEM_INFO']['company_email'], $info);
        $info = str_replace('supportEmail', Xphp::$_config['SYSTEM_INFO']['company_email'], $info);
        //获取列表数据
        if(!empty($data['history_uuid'])){
            $list = $this->getTaskEmailBodyList($data['task_type'], $data['submodule_type'], $data['module_type'], $data['task_uuid'], $data['history_uuid']);
            if ($list['td'] && !$singleVmAlarm) {
                //有列表数据则显示表格
                $info = str_replace('display-hide', 'display-show', $info);
                $info = str_replace('<tr><th>tableTh</th></tr>', $list['th'], $info);
                $info = str_replace('<tr><td>tableTd</td></tr>', $list['td'], $info);
            }
        }

        return $info;
    }

    /**
     * 得到邮件中任务详情列表,文件列表或虚拟机列表，组装为html
     * @param int $taskType
     * @param int $moduleType
     * @param string $taskuuid
     * @return array
     */
    private function getTaskEmailBodyList($taskType, $sub_module_type, $moduleType, $taskuuid, $historyUuid)
    {
        // 每个模块对应的对象名称中文
        $item_name = [
            "2-1" => Xphp::$_lang['UI_BACKUP_REPORT_NAME'],
            "2-2" => Xphp::$_lang['UI_BACKUP_REPORT_NAME'],
            "2-3" => Xphp::$_lang['UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME'],
            "3-1" => Xphp::$_lang['UI_AGENT_HOST_NAME'],
            "3-2" => Xphp::$_lang['UI_PLATFORM_NAS_MANAGER'],
            "3-3" => Xphp::$_lang['WEB_HADOOP_CLUSTER_NAME_DES'],
            "3-4" => Xphp::$_lang['UI_PLATFORM_OBS_NAME'],
            "4-0" => Xphp::$_lang['UI_DB_DATABASE_NAME'] . '/' . Xphp::$_lang['UI_OVERVIEW_PUBLIC_CLOUD_INSTANCE_NAME'],
            "5-0" => Xphp::$_lang['UI_AGENT_HOST_NAME'],
            "5-1" => Xphp::$_lang['UI_AGENT_HOST_NAME'],
            "10-0" => Xphp::$_lang['UI_AGENT_HOST_NAME'],
            "10-1" => Xphp::$_lang['UI_AGENT_HOST_NAME'],
            "10-2" => Xphp::$_lang['UI_AGENT_HOST_NAME'],
            "11-2" => Xphp::$_lang['UI_PLATFORM_NAS_MANAGER'],
            "14-1" => Xphp::$_lang['UI_ORGAN_ORGANIZATION_NAME'],
            "18-0" => Xphp::$_lang['WEB_HADOOP_CLUSTER_NAME_DES'],
        ];
        $TASK_TYPE = Xphp::$_config['TASKTYPE'];
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $copy_module = [$TASK_TYPE['BACKUP_COPY'], $TASK_TYPE['BACKUP_COPY_FETCH'], $TASK_TYPE['ARCHIVE'], $TASK_TYPE['ARCHIVE_FETCH']];
        $info = [
            'th' => '',
            'td' => '',
        ];
        $item_key = $moduleType . '-' . $sub_module_type;
        // 副本对象列表显示
        if (in_array($taskType, $copy_module)) {
            //虚拟机
            $info['th'] = '<tr><th width="10%">' . Xphp::$_lang['UI_PUBLIC_NUMBER'] . '</th><th width="30%">' . $item_name[$item_key] . '</th><th width="10%">' . Xphp::$_lang['UI_PUBLIC_STATUS'] . '</th></tr>';
            $list = $this->getCopyItemList($historyUuid);
            foreach ($list as $l) {
                $class = $this->getClassByItemStatus($l['error_code']);
                $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td><span class='" . $class . "'>" . $this->geCopyResultDes($l['error_code']) . "</span></td></tr>";
            }
            return $info;
        } else {
            switch ($moduleType) {
                //虚拟机
                case Xphp::$_config['MODULE_TYPE']['VM']:
                case Xphp::$_config['MODULE_TYPE']['DB']:
                    $info['th'] = '<tr><th>' . Xphp::$_lang['UI_PUBLIC_NUMBER'] . '</th><th>' . $item_name[$item_key] . '</th><th>' . Xphp::$_lang['UI_PUBLIC_STATUS'] . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    foreach ($list as $l) {
                        $class = $this->getClassByTaskStatus($l['task_status']);
                        $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td><span class='" . $class . "'>" . $vmDes['VmTaskStatus'][$l['task_status']] . "</span></td></tr>";
                    }
                    break;
                case Xphp::$_config['MODULE_TYPE']['FS']:
                case Xphp::$_config['MODULE_TYPE']['NAS']:
                    $info['th'] = '<tr><th>' . Xphp::$_lang['UI_PUBLIC_NUMBER'] . '</th><th>' . $item_name[$item_key] . '</th><th>' . Xphp::$_lang['UI_BACKUP_FILE_LIST'] . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    foreach ($list as $l) {
                        $class = $this->getClassByTaskStatus($l['task_status']);
                        $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td>{$l['source_list']}</td></tr>";
                    }
                    break;
                case Xphp::$_config['MODULE_TYPE']['OS']:
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    if ($sub_module_type == 0) {
                        $info['th'] = '<tr><th>' . Xphp::$_lang['UI_PUBLIC_NUMBER'] . '</th><th>' . $item_name[$item_key] . '</th><th>' . Xphp::$_lang['UI_PUBLIC_STATUS'] . '</th></tr>';
                        foreach ($list as $l) {
                            $class = $this->getStatusClass($l['task_status']);
                            $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td><span class='" . $class . "'>" . $ptDes['TASKSTATUSDES'][$l['task_status']] . "</span></td></tr>";
                        }
                    } else {
                        $info['th'] = '<tr><th>' . Xphp::$_lang['UI_PUBLIC_NUMBER'] . '</th><th>' . $item_name[$item_key] . '</th><th>' . Xphp::$_lang['UI_PUBLIC_STATUS'] . '</th></tr>';
                        foreach ($list as $l) {
                            $class = $this->getStatusClass($l['task_status']);
                            $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td><span class='" . $class . "'>" . $ptDes['TASKSTATUSDES'][$l['task_status']] . "</span></td></tr>";
                        }
                    }
                    break;
                case Xphp::$_config['MODULE_TYPE']['VOL_CDP']:
                    $info['th'] = '<tr><th>' . Xphp::$_lang['UI_PUBLIC_NUMBER'] . '</th><th>' . $item_name[$item_key] . '</th><th>' . Xphp::$_lang['UI_VOL_CDP_STANDBY'] . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    foreach ($list as $l) {
                        $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td>{$l['target_name']}</td></tr>";
                    }
                    break;
                case Xphp::$_config['MODULE_TYPE']['M365']:
                    $info['th'] = '<tr><th>' . Xphp::$_lang['UI_PUBLIC_NUMBER'] . '</th><th>' . $item_name[$item_key] . '</th><th>' . Xphp::$_lang['WEB_KUBE_BACKUP_RESOURCE_LIST'] . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    foreach ($list as $l) {
                        $info['td'] .= "<tr><td>{$l['index']}</td><td>{$l['item_name']}</td><td>{$l['source_list']}</td></tr>";
                    }
                    break;
                case Xphp::$_config['MODULE_TYPE']['FILE_COPY']: 
                    $info['th'] = '<tr><th>' . Xphp::$_lang['UI_FILE_COPY_SRC'] . '</th><th>' . Xphp::$_lang['UI_FILE_COPY_DES'] . '</th><th>' . Xphp::$_lang['WEB_KUBE_BACKUP_RESOURCE_LIST'] . '</th></tr>';
                    $list = $this->getTaskItemList($historyUuid, $moduleType);
                    foreach ($list as $l) {
                        $info['td'] .= "<tr><td>{$l['source_agent_name']}</td><td>{$l['des_agent_name']}</td><td>{$l['source_list']}</td></tr>";
                    }
                    break;
            }
        }
        return $info;
    }
    /**
     * 根据任务状态获取样式
     * @param mixed $status
     * @return string
     */
    private function getStatusClass($status){
        $class = "label label-sm label-info";
        switch ($status) {
            case 13:
            case 17:
                $class = "vm-status-success";
                break;
            case 6:
            case 7:
            case 8:
                $class = "vm-status-error";
                break;
            default:
                $class = "vm-status-info";
                break;
        }
        return $class;
    }
    /**
     *
     * 获取副本对象列表
     * @param mixed $task_uuid
     * @return array{error_code: mixed, index: float|int, item_name: mixed, item_status: mixed[]}
     */
    private function getCopyItemList($history_uuid)
    {
        $sql = "select details,error_code from bd_history_task where history_uuid = '{$history_uuid}' and details != '' order by id desc limit 1";
        $data = $this->dbSelect($sql, []);
        if (!$data) {
            return [];
        }
        $details = $data[0]['details'];
        $data = json_decode($details, true);
        $list = [];
        foreach ($data as $k => $d) {
            $list[] = [
                'index' => $k + 1,
                'item_name' => $d['item_name'],
                'item_status' => $d['item_status'],
                'error_code' => $d['error_code'],
            ];
        }
        return $list;
    }
    /**
     * 获取副本对象状态
     * @param mixed $errorCode
     */
    private function geCopyResultDes($errorCode)
    {
        //异常的错误
        $abnormal = ['BD_TASK_ANBNORMAL_ERROR'];
        //中止的错误
        $discontinue = ['BD_TASK_BE_CANCELLED_ERROR'];
        $errorCodeArr = Xphp::$_error['errorCode'];
        if (in_array($errorCodeArr[$errorCode], $abnormal)) {
            return Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'];
        }
        if (in_array($errorCodeArr[$errorCode], $discontinue)) {
            return Xphp::$_lang['WEB_PLATFORM_DES_DISCONTINUE'];
        }
        return $errorCode == 0 ? Xphp::$_lang['WEB_PLATFORM_DES_SUCCESSED'] : Xphp::$_lang['WEB_PUBLIC_FAILURE_HOMEPAGE'];
    }
    /**
     * 获取副本对象状态对应的class名
     * @param mixed $errorCode
     * @return string
     */
    private function getClassByItemStatus($errorCode)
    {
        //异常的错误
        $abnormal = ['BD_TASK_ANBNORMAL_ERROR'];
        //中止的错误
        $discontinue = ['BD_TASK_BE_CANCELLED_ERROR'];
        $errorCodeArr = Xphp::$_error['errorCode'];
        if (in_array($errorCodeArr[$errorCode], $abnormal)) {
            return 'vm-status-error';
        }
        if (in_array($errorCodeArr[$errorCode], $discontinue)) {
            return 'vm-status-info';
        }
        return $errorCode == 0 ? 'vm-status-success' : 'vm-status-error';
    }

    /**
     * 根据任务状态获取显示的css类名
     * @param $taskType
     * @param $taskStatus
     * @return string
     */
    private function getClassByTaskStatus($taskStatus): string
    {
        $class = '';
        switch ($taskStatus) {
            case 3: //成功
                $class = "vm-status-success";
                break;
            case 4: //失败
                $class = "vm-status-error";
                break;
            default:
                $class = "vm-status-info";
                break;
        }
        return $class;
    }
    private function getTaskItemList($history_uuid, $module_type)
    {
        $list = [];
        $sql = "select details,module_type,submodule_type,error_code from bd_history_task where history_uuid = ? and details != '' order by id desc limit 1";
        $data = $this->dbSelect($sql, [$history_uuid]);
        if (!$data) {
            return $list;
        }
        $sub_module_type = $data[0]['submodule_type'];
        $details = $data[0]['details'];
        $data = json_decode($details, true);
        switch ($module_type) {
            case Xphp::$_config['MODULE_TYPE']['VM']:
                $vms_details = $data['vms_details'] ?? $data;
                foreach ($vms_details as $k => $d) {
                    $list[] = [
                        'index' => $k + 1,
                        'item_name' => $d['dir_path'] ?: $d['vm_name'],
                        'task_status' => $d['task_status'],
                        'error_code' => $d['error_code'],
                    ];
                }
                break;
            case Xphp::$_config['MODULE_TYPE']['FS']:
            case Xphp::$_config['MODULE_TYPE']['NAS']:
                foreach ($data['agent_info_list'] as $k => $d) {
                    $list[] = [
                        'index' => ++$k,
                        'item_name' => $d['src_agent_name'] . "(" . $d['src_agent_ip'] . ")",
                        'source_list' => implode('<br>', $d['file_list']),
                        'task_status' => $d['task_status'],
                        'error_code' => $d['error_code'],
                    ];
                }
                break;
            case Xphp::$_config['MODULE_TYPE']['DB']:
                foreach ($data as $k => $d) {
                    $list[] = [
                        'index' => ++$k,
                        'item_name' => $d['dir_path'],
                        'dir_path' => $d['dir_path'],
                        'task_status' => $d['task_status'],
                        'error_code' => $d['error_code'],
                    ];
                }
                break;
            case Xphp::$_config['MODULE_TYPE']['OS']:
                foreach ($data as $k => $d) {
                    if ($sub_module_type == 0) {
                        $list[] = [
                            'index' => ++$k,
                            'item_name' => $d['os_name'] . "(" . $d['agent_ip'] . ")",
                            'task_status' => $d['task_status'],
                            'error_code' => $d['error_code'],
                        ];
                    } else {
                        $list[] = [
                            'index' => ++$k,
                            'item_name' => $d['os_name'] . "(" . $d['agent_ip'] . ")",
                            'task_status' => $d['task_status'],
                            'error_code' => $d['error_code'],
                        ];
                    }
                }
                break;
            case Xphp::$_config['MODULE_TYPE']['VOL_CDP']:
                foreach ($data as $k => $d) {
                    $list[] = [
                        'index' => ++$k,
                        'item_name' => $d['hostname'] . "(" . $d['agent_ip'] . ")",
                        'target_name' => !empty($d['standby_agent_name']) ? $d['standby_agent_name'] . "(" . $d['standby_agent_ip'] . ")" : '---',
                        'task_status' => $d['task_status'],
                        'error_code' => $d['error_code'],
                    ];
                }
                break;
            case Xphp::$_config['MODULE_TYPE']['M365']:
                $list[] = [
                    'index' => 1,
                    'item_name' => $data['organization_name'],
                    'source_list' => implode('<br>', $data['backup_m365_object_info_list']),
                    'task_status' => $data['task_status'],
                    'error_code' => $data['error_code'],
                ];
                break;
            case Xphp::$_config['MODULE_TYPE']['KUBERNETS']:
                // foreach($data['resources'] as $k => $d){
                //     $list[] = [
                //         'index' => ++$k,
                //         'item_name' => $d['name'],
                //         'source_list' => implode('<br>', $d['file_list']),
                //         'task_status' => $d['task_status'],
                //     ];
                // }
                break;
            case Xphp::$_config['MODULE_TYPE']['FILE_COPY']: 
                $copy_list = $data['copy_list'];
                $source_list = [];
                foreach ($copy_list as $k => $d) {
                    $source_list[] =  $d['source'] . "->" . $d['target'];
                }
                $source_name = $data['source_agent_nickname'] . "(" . $data['source_agent_ip'] . ")";
                // Nas端显示ip(路径)
                if($data['source_type'] == 2){
                    $source_name = $data['source_agent_ip'] . "(" . $data['source_agent_name'] . ")";
                }
                $target_name = $data['des_agent_nickname'] . "(" . $data['des_agent_ip'] . ")";
                // Nas端显示ip(路径)
                if($data['target_type'] == 2){
                    $target_name = $data['des_agent_ip'] . "(" . $data['des_agent_name'] . ")";
                }
                $list[] = [
                    'index' => 1,
                    'source_agent_name' => $source_name,
                    'des_agent_name' => $target_name,
                    'source_list' => implode('<br>', $source_list),
                ];
                break;
            default:
                break;
        }
        return $list;
    }

    /**
     * 根据任务UUID得到文件列表
     * @param string $historyUuid
     */
    private function getTaskFileList($historyUuid){
        $sql = "select details from bd_history_task where task_uuid = ? and details != ''";
        $data = $this->dbSelect($sql, array($historyUuid));
        if (!$data) {
            return array();
        }
        $details = $data[0]['details'];
        $data = json_decode($details, true);
        $list = array();
        foreach($data['agent_info_list'] as $k => $d){
            $list[] = [
                'no' => ++$k,
                'src_agent_name' => $d['src_agent_name'],
                'src_agent_ip' => $d['src_agent_ip'],
                'file_list' => implode('<br>', $d['file_list'])
            ];
        }
        return $list;
    }

    /**
     * 得到任务短信通知参数
     * @param int $taskAlarmID
     */
    private function getTaskSmsParams($taskAlarmID){
        $sql = "select bta.alarm_level, bta.description_key, bta.description_param, bta.alarm_time,
                bta.task_name, bta.task_type, bta.module_type, bta.submodule_type, bta.node_name,
                bta.storage_name, bta.user_name, bta.error_code, bta.task_log_path,
                bu.email, bu.telephone
                from bd_task_alarm bta, bd_user bu
                where bta.user_uuid = bu.user_uuid
                and bta.task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($taskAlarmID));
        $this->checkNoticeData($data);
        $logHandler = Xphp::instance('LogHandler');
        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['TASK'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param'], $data[0]['task_name']);
        $params = array(
            'tels' =>  $data[0]['telephone'],
            'msg' => $desription . ". " . date("m-d H:i:s", strtotime($data[0]['alarm_time'])),
        );
        return $params;
    }

    /**
     * 得到任务微信通知参数
     * @param int $taskAlarmID
     * @param int $is_wechat
     */
    private function getTaskWechatParams($taskAlarmID){
        $sql = "select bta.alarm_level, bta.description_key, bta.description_param, bta.alarm_time,
                bta.task_name, bta.task_type, bta.module_type, bta.submodule_type, bta.node_name,
                bta.storage_name, bta.user_name, bta.error_code, bta.task_log_path,
                bu.email, bu.telephone
                from bd_task_alarm bta, bd_user bu
                where bta.user_uuid = bu.user_uuid
                and bta.task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($taskAlarmID));
        $this->checkNoticeData($data);
        $logHandler = Xphp::instance('LogHandler');

        $confDes = $logHandler->getLogConf(Xphp::$_config['LOGTYPE']['TASK']);
        $desStr = $confDes[$data[0]['description_key']];

        if($data[0]['error_code']){
            $errorConf = include CONF_PATH . 'error.php';
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$data[0]['error_code']]];
            $desStrs = "[#" . $data[0]['error_code'] . "]" . $errorStr;
        }

        $desription = $logHandler->getLogDesriptionNotice(Xphp::$_config['LOGTYPE']['TASK'], $data[0]['error_code'],
            $data[0]['description_key'], $data[0]['description_param'], $data[0]['task_name']);

        return array(
            'title' =>  xphp::$_lang['UI_SETTINGS_NOTICE_TASK'],
            'task_name' => $data[0]['task_name'],
            'desc' => $desStrs ?? $desStr,
            'content' => $desription,
        );
    }

    /**
     * 检测是否获取到通知
     * @param array $data
     */
    private function checkNoticeData($data){
        if(empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ALARM_GET_NOTICE_INFO']));
        }
    }

    /**
     * 检测通知参数
     * @param int $type     通知类型
     * @param int $id       告警ID
     */
    private function checkNoticeParams($emailFlag, $smsFlag, $type, $id){
        $wechatFlag = false;
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sqlupdate);
        if(!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            if ($wechatContent['openid'] && $wechatContent['appid'] && $wechatContent['appsecret']) {
                $wechatFlag = true;
            }
        }

        if($emailFlag == Xphp::$_config['FLAG']['UNSET'] && $smsFlag == Xphp::$_config['FLAG']['UNSET'] && !$wechatFlag){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ALARM_GET_NOTICE_SETTING']));
        }
        if($type != Xphp::$_config['NOTICE_TYPE']['TASK'] && $type != Xphp::$_config['NOTICE_TYPE']['SYSTEM']){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ALARM_CHECK_NOTICE_TYPE']));
        }
        if($id <= 0){
            exit($this->muOpResult(false, Xphp::$_lang['WEB_ALARM_CHECK_NOTICE_NUM']));
        }
    }

    /**
     * 根据配置得到本次发送邮件和短信的标志
     * @param boolean $emailFlag    调用者发送邮件的标志
     * @param boolean $smsFlag      调用者发送短信的标志
     * @param int $type             系统通知还是任务通知
     * @param int $id               通知ID号
     * @return array
     */
    private function checkNoticeSetting($emailFlag, $smsFlag, $type, $id){
        $emailFlag = $this->checkEmailNoticeSetting($emailFlag, $type, $id);
        $smsFlag = $this->checkSmsmNoticeSetting($smsFlag, $type, $id);
        $wechatFlag = $this->checkWechatNoticeSetting($type, $id);
        $wechat2Flag = $this->checkWechat2NoticeSetting($type, $id);
        $flag = array(
            'email' => $emailFlag,
            'sms' => $smsFlag,
            'wechat' => $wechatFlag,
            'wechat2' => $wechat2Flag,
        );
        return $flag;
    }

    /**
     * 根据配置得到本次发送邮件的标志
     * @param boolean $emailFlag    调用者发送邮件的标志
     * @param int $type             系统通知还是任务通知
     * @param int $id               通知ID号
     * @return boolean
     */
    public function checkEmailNoticeSetting($emailFlag, $type, $id){
        if(!$emailFlag) return false;
        //得到邮件配置
        $sql = "select email_notice_flag, system_notice_flag, system_notice_level, 
                task_notice_flag, task_notice_level, verify_report_flag, verify_report_level from bd_email_notice where email_notice_type = 1  ";
        $data = $this->dbSelect($sql);
        //如果未设置总开关,直接返回false
        if($data[0]['email_notice_flag'] == Xphp::$_config['FLAG']['UNSET']) return false;

        return $this->unifyCheckSetting($data[0], $type, $id);
    }

    /**
     * 根据配置得到本次发送短信的标志
     * @param boolean $emailFlag    调用者发送短信的标志
     * @param int $type             系统通知还是任务通知
     * @param int $id               通知ID号
     * @return boolean
     */
    private function checkSmsmNoticeSetting($smsFlag, $type, $id){
        if(!$smsFlag) return false;
        //得到邮件配置
        $sql = "select sms_notice_flag, system_notice_flag, system_notice_level,
                task_notice_flag, task_notice_level from bd_sms_notice";
        $data = $this->dbSelect($sql);
        //如果未设置总开关,直接返回false
        if($data[0]['sms_notice_flag'] == Xphp::$_config['FLAG']['UNSET']) return false;

        return $this->unifyCheckSetting($data[0], $type, $id);
    }
    /**
     * 根据配置得到本次发送微信模板消息的标志
     * @param int $type             系统通知还是任务通知
     * @param int $id               通知ID号
     * @return boolean
     */
    private function checkWechatNoticeSetting($type, $id){
        //得到配置
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sqlupdate);
        //如果未设置总开关,直接返回false
        if(!empty($wechats)){
            $wechatContent = json_decode($wechats[0]['settings_content'],true);
            if (!empty($wechatContent['openid']) && $wechatContent['wechatFlag'] != Xphp::$_config['FLAG']['UNSET']) {
                $datas = [
                    'system_notice_flag' => $wechatContent['systemFlag'],
                    'task_notice_flag' => $wechatContent['taskFlag'],
                    'system_notice_level' => $wechatContent['systemLevel'],
                    'task_notice_level' => $wechatContent['taskLevel'],
                ];
                return $this->unifyCheckSetting($datas, $type, $id);
            }
        }
        return false;
    }

    /**
     * 根据配置得到本次发送企业微信消息的标志
     * @param int $type             系统通知还是任务通知
     * @param int $id               通知ID号
     * @return boolean
     */
    private function checkWechat2NoticeSetting($type, $id){
        //得到配置
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 11";
        $wechats = $this->dbSelect($sqlupdate);
        //如果未设置总开关,直接返回false
        if(!empty($wechats)){
            $wechatContent = json_decode($wechats[0]['settings_content'],true);
            if ($wechatContent['wechatFlag'] != Xphp::$_config['FLAG']['UNSET']) {
                $datas = [
                    'system_notice_flag' => $wechatContent['systemFlag'],
                    'task_notice_flag' => $wechatContent['taskFlag'],
                    'system_notice_level' => $wechatContent['systemLevel'],
                    'task_notice_level' => $wechatContent['taskLevel'],
                ];
                return $this->unifyCheckSetting($datas, $type, $id);
            }
        }
        return false;
    }

    /**
     *
     * @param array $setting    配置信息
     * @param int $type         系统通知还是任务通知
     * @param int $id           通知ID号
     * @return boolean
     */
    private function unifyCheckSetting($setting, $type, $id){
        $sql = "select task_type from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        if($type == Xphp::$_config['NOTICE_TYPE']['SYSTEM']){
            //系统通知
            if($setting['system_notice_flag'] == Xphp::$_config['FLAG']['UNSET'])   return false;

            $alarmID = "system_alarm_id";
            $tableName = "bd_system_alarm";
            $tableLevel = "system_notice_level";
        }elseif ($type == Xphp::$_config['NOTICE_TYPE']['TASK']){
            //任务通知
            if($setting['task_notice_flag'] == Xphp::$_config['FLAG']['UNSET'])   return false;
            $alarmID = "task_alarm_id";
            $tableName = "bd_task_alarm";
            $tableLevel = "task_notice_level";

        }elseif ($type == Xphp::$_config['NOTICE_TYPE']['VERIFY']){
            //数据验证任务单独判断
            if(intval($data[0]['task_type']) == Xphp::$_config['TASKTYPE']['SURE_BACKUP']){
                if($setting['verify_report_flag'] == Xphp::$_config['FLAG']['UNSET'])   return false;
                $alarmID = "task_alarm_id";
                $tableName = "bd_task_alarm";
                $tableLevel = "verify_report_level";
            }
        }
        //得到本次告警等级
        $sql = "select alarm_level from $tableName where $alarmID = ?";
        $alarmInfo = $this->dbSelect($sql, array($id));
        $level = $alarmInfo[0]['alarm_level'];

        $settingLeve = explode(',', $setting[$tableLevel]);

        if(in_array($level, $settingLeve)){
            return true;
        }
        return false;
    }

    /**
     * 更新通知结果
     * @param int $alarmID  告警ID号
     * @param int $type     系统告警/任务告警
     * @param int $mode     邮件/短信
     * @param json $result  发送结果
     */
    private function updateNoticeResult($alarmID, $type, $mode, $result){

        if (in_array($mode, [Xphp::$_config['NOTICE_MODE']['WECHAT'], Xphp::$_config['NOTICE_MODE']['WECHAT2']])){
            if (empty($result)) {
                return true;     //发送失败,不更新
            }
        } else{
            $result = json_decode($result, true);
            if ((!$result['re'])){
                return true;     //发送失败,不更新
            }
        }

        if($type == Xphp::$_config['NOTICE_TYPE']['SYSTEM']){
            $tableName = "bd_system_alarm";     //系统告警
            $tableID = "system_alarm_id";
        }elseif ($type == Xphp::$_config['NOTICE_TYPE']['TASK']){
            $tableName = "bd_task_alarm";       //任务告警
            $tableID = "task_alarm_id";
        }

        if($mode == Xphp::$_config['NOTICE_MODE']['EMIAL']){
            $sendFlagName = "email_send_flag";      //邮件通知
        }elseif ($mode == Xphp::$_config['NOTICE_MODE']['SMS']){
            $sendFlagName = "sms_send_flag";        //告警通知
        }elseif ($mode == Xphp::$_config['NOTICE_MODE']['WECHAT']) {
            $sendFlagName = "wechat_send_flag";        //微信通知
        }elseif ($mode == Xphp::$_config['NOTICE_MODE']['WECHAT2']) {
            $sendFlagName = "enterprise_wechat_send_flag";        //企业微信通知
        }

        if(empty($tableName) || empty($sendFlagName)) return true;
        $sql = "update $tableName set $sendFlagName = ? where $tableID = ?";
        return $this->dbExec($sql, array(Xphp::$_config['FLAG']['SET'], $alarmID));
    }

    /**
     * 获取任务告警解决标记
     * @param string $id
     */
    public function getTaskAlarmSolvedFlag($id){
        $sql = "select solved_flag from bd_task_alarm where task_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        $solvedFlag = Xphp::$_config['FLAG']['UNSET'];
        if(!empty($data)){
            $solvedFlag = $data[0]['solved_flag'];
        }

        return $solvedFlag;
    }

    /**
     * 获取系统告警解决标记
     * @param string $id
     */
    public function getSystemAlarmSolvedFlag($id){
        $sql = "select solved_flag from bd_system_alarm where system_alarm_id = ?";
        $data = $this->dbSelect($sql, array($id));
        $solvedFlag = Xphp::$_config['FLAG']['UNSET'];
        if(!empty($data)){
            $solvedFlag = $data[0]['solved_flag'];
        }

        return $solvedFlag;
    }


    /**
     * 获取告警发送的邮件日志附件可用路径
     * @param string $path  附件原路径
     * @param string $nodeuuid  当前日志文件所在节点唯一标识
     * @return unknown|string
     */
    private function getRedirectAttach($path, $nodeuuid){
        if(empty($path)) return $path;
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $fileContent = '';
        if($msg['result']){
            //检查一个月定期清理日志文件;
            $cmd = "find ".Xphp::$_config['TMP_PATH']." logdir/ "." -type f -mtime +30 -exec rm {} \;";
            exec($cmd);
            //日志文件另存为php有权限访问的路径
            $path = Xphp::$_config['TMP_PATH']."logdir/"."log_attachment_".date("Y_m_d_H_i_s");
            file_put_contents($path, $msg['msg']['file_content']);

        }else{
            //出错设置路径为空
            $path = "";
        }

        return $path;
    }

    /**
     * 获取任务告警内容
     */
    public function getTaskAlarmContent($params){
        $msg = array(
            'success' => true,
            'code' => $params['errorCode'],
            'message' => array(
                'alarm_content' => ''
            ),
            'date' => '',
            'alarm_type' => Xphp::$_config['LOGTYPE']['TASK'],
        );

        $logHandler = Xphp::instance('LogHandler');
        $confDes = $logHandler->getLogConf($params['logType']);
        $desStr = '';
        if(!empty($params['taskName'])){
            $desStr .= Xphp::$_lang['UI_PUBLIC_TASK_RNAME'].":".$params['taskName']." ";
        }
        if(!empty($params['moduleType'])){
            $desStr .= Xphp::$_lang['UI_PUBLIC_MODULE_TYPE'].":".$this->getModuleTypeDes($params['moduleType'])." ";
            $desStr .= Xphp::$_lang['UI_ALARM_CONTENT'].":";
        }
        $desStr .= $confDes[$params['desription']];

        // 填充参数
        if ($params['descriptionParam']) {
            $descriptionParam = json_decode($params['descriptionParam'], true);
            $descriptionArr = explode('%s', $desStr);
            $desStr = '';
            foreach ($descriptionArr as $k => $v) {
                $desStr .= $v . $this->getEachParamsDes($descriptionParam[$k], $descriptionParam);
            }
        }

        if($params['errorCode']){
            $errorConf = Xphp::$_error;
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$params['errorCode']]];
            $desStr .= "," . $errorStr;
        }
        $msg['message']['alarm_content'] = $desStr;
        $desStr ? $msg['success'] = true:$msg['success']= false;
        $msg['data'] = date('Y-m-d H:i:s');
        $msg = json_encode($msg);
        return $msg;
    }

    /**
     * 获取系统告警内容
     */
    public function getSystemAlarmContent($params)
    {
        $msg = array(
            'success' => true,
            'code' => $params['errorCode'],
            'message' => array(
                'alarm_content' => ''
            ),
            'date' => '',
            'alarm_type' => Xphp::$_config['LOGTYPE']['SYSTEM'],
        );

        $logHandler = Xphp::instance('LogHandler');
        $confDes = $logHandler->getLogConf($params['logType']);
        $desStr = '';
        $desStr .= $confDes[$params['desription']];
        if($params['errorCode']){
            $errorConf = Xphp::$_error;
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$params['errorCode']]];
            $desStr .= "," . $errorStr;
        }
        $batchDelete = array('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', 'SYSTEM_LOG_DELETE_ARCHIVE_BATCH_TIMEPOINT', 'SYSTEM_LOG_DELETE_COPY_BATCH_TIMEPOINT', 'BD_SYSTEMLOG_DESC_KEY_DEL_TASK_ORCHESTRATION_PLAN');
        if ($params['descriptionParam']) {
            $param = json_decode($params['descriptionParam'], true);
            $desArr = explode("%s",
                $desStr
            );
            $desStr = "";
            $i = 0;
            foreach ($desArr as $k => $v) {
                $i++;
                if ($i == 1 && in_array($params['desription'], $batchDelete)) {
                    $desStr .= $v . $this->getEachParamsDes($param[$k], $param, false);
                } else {
                    $desStr .= $v . $this->getEachParamsDes($param[$k], $param);
                }
            }
            if( $param && count($param)>2 && in_array($params['desription'], $batchDelete)) {
                for ($i = 2; $i <= count($param); $i++) {
                    $parts = explode(':', $param[$i]);
                    if (count($parts) > 1) {
                        $value = $parts[1];
                        $desStr .= $value;
                    }
                }
            }
        }
        $msg['message']['alarm_content'] = $desStr;
        $desStr ? $msg['success'] = true:$msg['success']= false;
        $msg['data'] = date('Y-m-d H:i:s');
        $msg = json_encode($msg);
        return $msg;
    }

    /**
     * 得到每一项参数的描述
     * @param string $eachParams
     * @param boolean $classShowFlag  是否显示颜色,页面显示的时候要显示颜色类,发送短信和邮件的时候不显示
     * @return string
     */
    public function getEachParamsDes($eachParams,$descriptionParam,$classShowFlag = true){
        if(empty($eachParams)){
            return "";
        }
        $des = "";
        $arr = explode(":", $eachParams, 2);
        switch($arr[0]){
            case "S":
                $des = $arr[1];
                break;
            case "module_type":
                $des = $this->logModuleTypeDes($arr[1]);
                break;
            case "backup_mode":
                $des = $this->logBackupModeDes($arr[1],$descriptionParam);
                break;
            default:
                break;
        }
        return $des;
    }

    /**
     * 得到日志模块描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logModuleTypeDes($index){
        $desConf = Xphp::$_pfdes;
        return $desConf['MODULE_TYPE_DES'][intval($index)];
    }

    /**
     * 得到日志备份模式描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logBackupModeDes($index,$descriptionParam){
        $desConf = Xphp::$_pfdes;
        $moduleType = explode(":", $descriptionParam[1],2);
        if($moduleType[0]=='module_type' && $moduleType[1] == Xphp::$_config['MODULE_TYPE']['VOL_CDP']){
            return "";
        }else{
            return $desConf['BACKUP_MODE_DES'][intval($index)];
        }
    }
}
?>