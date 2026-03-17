<?php

namespace app\v1\os\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\exchange\v0\logic\ExchangeRecover;
use app\v1\job\v0\logic\JobInfo;
use app\v1\vm\v0\logic\VmBackUp;

/**
 * note          操作系统 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsData extends Base
{

    /**
     * 获取时间点树
     * @param unknown $params node 节点uuid
     * @return string
     */
    public function getTimepointTree($params)
    {
        //-----------------------------------------------------------------
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>  xphp_get_lang('WEB_OS_GET_TIMEPOINT_TREE'),
            'data' => array(),
        );
        $storageuuid = $params['storageuuid'];
        //来源
        $recoverflag = $params['recoverflag']; //恢复的树形结构
        $dataflag = $params['dataflag'];//备份数据的树形结构
        $instantflag = $params['instantflag'] ? true : false; //是否来自瞬时恢复
        //如果来自恢复则为true  来自备份数据为false
        $nocheckflag = $recoverflag && !$dataflag ? true : false;

        //获取所有时间点信息
        $sql = "select bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,
    			bbt.task_uuid, obt.os_name, obt.dir_path, bbt.real_node_uuid, bsr.node_uuid, obt.os_type,obt.agent_ip,obt.agent_uuid   
                from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and bbt.import_flag = 2 and bbt.data_local_flag = ? ";
        $sqlParams = array(xphp_get_config('app', 'FLAG')['SET']);
        $flag = xphp_get_config('app', 'FLAG');
        //备份数据不展示副本相关内容
        if ($dataflag) {
            $sql .= " and bbt.copy_flag = ? and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], xphp_get_config('module', 'MODULE_TYPE')['OS']));
        } else {
            $sql .= " and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array(xphp_get_config('module', 'MODULE_TYPE')['OS']));
        }


        // if (!empty($nodeuuid)) {
        //     $sql .= " and (bbt.real_node_uuid = ? or bsr.node_uuid = ?)";
        //     $sqlParams = array_merge($sqlParams, array($nodeuuid, $nodeuuid));
        // }
        if(!empty($storageuuid)){
            $sql .=" and bsr.storage_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($storageuuid));
        }

        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            //权限重构
            $authUser = $_SESSION['authUser']['os_protect_look'] ?? [];
            if (!empty($authUser)) {
                $userUuidArr = array_merge([$user = xphp_get_user_info()['userUuid']], $authUser);
                $userUuids = "('" . implode("','", $userUuidArr) . "')";
                $sql .= " and bbt.user_uuid in $userUuids ";
            } else {
                $sql .= " and bbt.user_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array(xphp_get_user_info()['userUuid']));
            }
        }

        //如果是瞬时恢复 需要屏蔽云存储的时间点 还需要屏蔽磁带的时间点
        if ($instantflag) {
            $sql .= " and bsr.storage_type != ? and bsr.storage_type != ? ";
            array_push($sqlParams, xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD'], xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE']);
        }

        $sql .= " order by bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);


        //所有任务的集合
        $task = array();
        $info = array();
        $os = array();
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();
        $agentList = $this->getAllAgentList();
        foreach ($data as $op) {
            $task_uuid = $op['task_uuid'];
            $taskName = $op['task_name'];
            if (!in_array($task_uuid, $task)) {
                $task[] = $task_uuid;
                //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
                $taskAvailable = in_array($task_uuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
                //如果是恢复的树  副本归档不显示删除 只显示副本数据和归档数据
                if ($recoverflag) {
                    // 副本数据
                    if ($op['task_type'] == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] || $op['task_type'] == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY_FETCH']) {
                        $name = $taskName . "(" . xphp_get_lang('UI_COPY_DATA') . ")";
                    }
                    // 归档数据
                    if ($op['task_type'] == xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] || $op['task_type'] == xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH']) {
                        $name = $taskName . "(" . xphp_get_lang('UI_ARCHIVE_DATA') . ")";
                    }
                }


                //第一层 任务名称
                $info[] = array(
                    "id" => $op['task_uuid'],
                    "pId" => 0,
                    "name" => $name,
                    "open" => false,
                    "nocheck" => $nocheckflag,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': ' . date('Y-m-d H:i:s', $op['task_create_time']),
                    "isParent" => true,
                );
            }

            if (!in_array($op['task_uuid'] . $op['agent_uuid'], $os)) {
                $os[] = $op['task_uuid'] . $op['agent_uuid'];
                //获取系统类型
                $os_type = $op['os_type'];
                $osTypeName = $this->getOSTypeStr($os_type);
                //第二层 任务系统类型
                $info[] = array(
                    "id" => $op['task_uuid'] . $op['agent_uuid'],
                    "pId" => $op['task_uuid'],
                    'name' => $this->getAgentNameByList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),
                    "open" => false,
                    "nocheck" => $nocheckflag,
                    "type" => 1,
                    "icon" => './img/os/' . $osTypeName . '.png',
                    "iconSkin" => $osTypeName . 'logo',
                    "title" => xphp_get_lang('WEB_OS_SOURCE_PATH') . ': ' . $op['dir_path'],
                    "taskuuid" => $op['task_uuid'],
                    "agentuuid" => $op['agent_uuid'],
                    "nodeuuid" => $op['node_uuid'],
                    "createtime" => $this->parseDate($op['task_create_time']),
                    "osType" => $op['os_type'],
                    "isParent" => true,
                    "clickshow" => true
                );
            }


        }
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

    /**
     * 获取主机类型
     * @param unknown $os_type 主机类型
     * return 主机文字描述
     */
    public function getOSTypeStr($os_type)
    {
        $TypeStr = '';
        switch ($os_type) {
            case 1:
                $TypeStr = 'Windows';
                break;
            case 2:
                $TypeStr = 'Linux';
                break;
            default:
                $TypeStr = "unknown";
                break;
        }
        ;
        return $TypeStr;
    }



    /**
     * 根据agent_uuid 查询出agent的信息并拼接成名字
     * @param unknown $list
     * @param unknown $agent_uuid
     * @return name(ip)
     */
    public function getAgentNameByList($list, $agent_uuid, $os_name, $os_agent_ip)
    {
        $name = "--";
        $logicOsBackup = new OsBackUp;
        //如果没获取到主机则直接返回空
        if (empty($list)) {
            if (empty($os_name)) {
                return $name;
            } else {
                return $logicOsBackup->getAgentName($os_name, "", $os_agent_ip);
            }
        }
        //开始循环获取主机
        foreach ($list as $each) {
            if ($each['agent_uuid'] == $agent_uuid) {
                return $logicOsBackup->getAgentName($each['hostname'], $each['agent_name'], $each['ip']);
            }
        }
        return $logicOsBackup->getAgentName($os_name, "", $os_agent_ip);
    }


    /**
     * 异步获取时间点数
     * @param unknown $params
     * taskuuid agentuuid nodeuuid  任务uuid 代理机uuid 节点uuid   3者确定一条数据
     * $recoverflag boolean
     * $dataflag  boolean
     */
    public function getSyncTimepoint($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_TIMEPOINT_ASYNC'),
            'data' => array(),
        );
        //----------------------------
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        $storageuuid = $params['storageuuid'];
        $oschecked = $params['oschecked']; //父级是否被勾选上
        //来源
        $recoverflag = $params['recoverflag']; //恢复的树形结构
        $dataflag = $params['dataflag'];//备份数据的树形结构
        $instantflag = $params['instantflag'] ? true : false; //是否来自瞬时恢复
        $agentList = $this->getAgentuuidList(); //得到在任务中的一些代理机uuid


        //如果来自恢复则为true  来自备份数据为false
        $nocheckflag = $recoverflag && !$dataflag ? true : false;

        $sql = "select bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,bbt.backup_mode,
    			     bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.detail,bbt.importance_flag, bbt.remarks,bbt.archive_flag,bbt.deleted_flag, 
                     bbt.task_uuid, obt.os_timepoint_id, obt.os_name, obt.dir_path, bbt.real_node_uuid, bsr.node_uuid, obt.os_type,obt.agent_ip ,obt.agent_uuid , bsr.status, bsr.storage_type   
                from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and
                      bbt.import_flag = 2 and   
                      bbt.module_type in (" . xphp_get_config('module', 'MODULE_TYPE')['OS'] . "," . xphp_get_config('module', 'MODULE_TYPE')['BACKUP_COPY_CLIENT'] . ") and
                      bbt.data_local_flag = ? and bbt.task_uuid = ? and 
                      obt.agent_uuid = ?";
        $sqlparams = array(xphp_get_config('app', 'FLAG')['SET'], $taskuuid, $agentuuid);
        if(!empty($storageuuid)){
            $sql .=" and bsr.storage_uuid = ? ";
            $sqlparams = array_merge($sqlparams, array($storageuuid));
        }
        //如果是瞬时恢复 需要屏蔽云存储的时间点 还需要屏蔽磁带上的时间点
        if ($instantflag) {
            $sql .= " and bsr.storage_type != ? and bsr.storage_type != ? ";
            $sqlparams = array_merge($sqlparams, array(xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD'],xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE']));
        }
        $sql .= " order by timepoint asc";
        $pointData = $this->dbSelect($sql, $sqlparams);

        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        foreach ($pointData as $point) {
            if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $unfullList[] = $point;

            }
        }
        while (!empty($unfullList)) {
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key1 => $unfull) {
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $key => $value) {
                    if ($dependId == $key) {
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$key];
                        $unfullList = array_splice($unfullList, $key1, 1);
                        $unfullCountTmp--;
                    }
                    continue;
                }
            }

            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0)
                break;

        }

        //判断是否可被勾选,先判断该代理主机是否存在恢复任务中,如果在任务中就看是否在运行状态  如果在就不让选中

        //默认为可勾选
        $chkDisabledflag = false;
        //         if($recoverflag){//如果是恢复
//             if(in_array($agentuuid, $agentList)){
//                 $chkDisabledflag = true;
//             }
//         }





        //得到所有数据后开始获取此机器备份的所有时间点
        $timepoint = array();
        $agentList = $this->getAllAgentList();


        foreach ($pointData as $op) {
            //----判断是否时间点是否可用 true表示禁用
            $availableFlag = true;

            //如果时间点正在合并且不可用
            if ($op['archive_flag'] == xphp_get_config('app', 'FLAG')['SET'] || $op['status'] != xphp_get_config('storage', 'STORAGE_STATUS')['ONLINE']) {
                $availableFlag = false;
            }


            $osTypeName = $this->getOSTypeStr($op['os_type']);

            //处理名字显示 比如添加有密码的小锁表示 GFS 备注 永久标记点等等
            $details = json_decode($op['detail'], true);
            $name = $this->parseDate($op['timepoint']) . "(" . $this->getTimepointTypeDes($op['backup_mode']) . ")";

            //如果在合并中
            if ($op['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                $name .= xphp_get_lang('WEB_OS_MERGE');
            } else if ($op['archive_flag'] == xphp_get_config('app', 'FLAG')['UNSET'] && $op['deleted_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                $name .= "(" . xphp_get_lang('UI_PUBLIC_MERGE_ERROR') . ")";
                //如果是在恢复页面不可用
                if ($recoverflag) {
                    $availableFlag = false;
                }
            }

            //判断是否有加密 ,如果有手动输入密码则显示小锁标识
            if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }

            //是否是数据备份
            $mark = "";
            if ($dataflag) {
                //添加GFS标识
                $mark .= $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($op['importance_flag']));

            }

            //其父级是否被勾选
            if (!empty($oschecked)) {
                $oschecked = true;
            } else {
                $oschecked = false;
            }





            if ($op['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                if (!in_array($op['timepoint_uuid'], $timepoint)) {
                    $timepoint[] = $op['timepoint_uuid'];
                    $info[] = array(
                        "agentuuid" => $agentuuid,
                        "checked" => $oschecked,
                        "createtime" => $this->parseDate($op['task_create_time']),
                        "osname" => $this->getNameByAgentList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),
                        "ostype" => $op['os_type'],
                        "icon" => "./img/platform/timepoint-f.png",
                        "id" => $op['timepoint_uuid'],
                        "name" => $name . $mark,
                        "oldname" => $name,
                        "nodeuuid" => $op['node_uuid'],
                        "real_node_uuid" => $op['real_node_uuid'],
                        "pId" => $op['task_uuid'] . $op['agent_uuid'],
                        "path" => $op['dir_path'],
                        "pointname" => $this->parseDate($op['task_create_time']),
                        "taskuuid" => $taskuuid,
                        "timepointuuid" => $op['timepoint_uuid'],
                        "title" => xphp_get_lang('WEB_OS_SOURCE_PATH') . "=> " . $op['dir_path'],
                        "type" => 2,
                        "iconSkin" => $osTypeName . 'logo',
                        "chkDisabled" => !$availableFlag,
                        //是否是在任务中
                        'agentuuidInTask' => $chkDisabledflag,
                        "password_auto_flag" => intval($details['password_auto_flag']) == 1 ? true : false,
                        "encrypted_flag" => $encrypted_flag,
                        "storage_type"=> $op['storage_type']
                    );
                    continue;
                }
            }


            //第四层 增备差异点
            $info[] = array(
                "agentuuid" => $agentuuid,
                "checked" => $oschecked,
                "createtime" => $this->parseDate($op['task_create_time']),
                "osname" => $this->getNameByAgentList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),
                "ostype" => $op['os_type'],
                "icon" => $this->getTimepointIcon($op['backup_mode']),
                "id" => $op['timepoint_uuid'],
                "name" => $name . $mark,
                "oldname" => $name,
                "nodeuuid" => $op['node_uuid'],
                "real_node_uuid" => $op['real_node_uuid'],
                "pId" => $fulluuidList[$op['timepoint_uuid']],
                "path" => $op['dir_path'],
                "pointname" => $this->parseDate($op['task_create_time']),
                "taskuuid" => $taskuuid,
                "timepointuuid" => $op['timepoint_uuid'],
                "title" => xphp_get_lang('WEB_OS_SOURCE_PATH') . "=> " . $op['dir_path'],
                "type" => 3,
                //                 "nocheck" => !$nocheckflag,
                "nocheck" => false,
                "iconSkin" => $osTypeName . 'logo',
                "chkDisabled" => $dataflag ? true : !$availableFlag,
                //是否是在任务中
                'agentuuidInTask' => $chkDisabledflag,
                "password_auto_flag" => intval($details['password_auto_flag']) == 1 ? true : false,
                "encrypted_flag" => $encrypted_flag,
                "storage_type"=> $op['storage_type']
            );

        }


        $resultInfo['data'] = $info;
        return $resultInfo;


    }



    /**
     * 得到所有在任务中的agentuuid集合 ,此主机在任务中并且是为在运行状态的
     *
     */
    public function getAgentuuidList()
    {
        $info = array();
        $sql = "select ol.agent_uuid from os_list ol, bd_task bt where ol.task_uuid = bt.task_uuid and ((bt.task_type = ? and bt.task_status = ?) or bt.task_type = ?)";
        $result = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'], xphp_get_config('task', 'TASKSTATUS')['RUNNING'], xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY']));
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $info[] = $each['agent_uuid'];
        }
        ;
        return $info;
    }


    /**
     * 得到备份时间点类型描述
     * @param unknown $backup_mode
     * return
     */
    public function getTimepointTypeDes($backup_mode)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = '';
        $des = $pfDes['BACKUP_MODE_DES'][$backup_mode];
        $des .= xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
        return $des;
    }

    /**
     * 根据agent_uuid 查询出名称
     * @param unknown $list
     * @param unknown $agent_uuid
     * @return name
     */
    public function getNameByAgentList($list, $agent_uuid, $os_name, $os_agent_ip)
    {
        $name = "--";
        //如果没获取到主机则直接返回空
        if (empty($list)) {
            if (empty($os_name)) {
                return $name;
            } else {
                return $os_name;
            }
        }
        //开始循环获取主机
        foreach ($list as $each) {
            if ($each['agent_uuid'] == $agent_uuid) {
                if (empty($each['hostname']) && !empty($each['agent_name'])) {
                    return $each['agent_name'];
                } else if (!empty($host_name) && empty($agent_name)) {
                    return $each['hostname'];
                } else {
                    if ($each['agent_name'] == $each['ip']) {
                        return $each['hostname'];
                    } else {
                        return $each['agent_name'];
                    }
                }
            }
        }
        return $os_name;
    }




    /*
     * 得到主机保护-备份数据-任务列表
     * taskuuid agentuuid nodeuuid
     * 编号 时间点 类型 数据大小 写入大小 所在存储 分区信息 备注 操作 星标
     * return
     */
    public function getOsTimepointGrid($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_TIMEPOINT_LIST'),
            'data' => array(),
        );
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        $copyFlag = $params['copyFlag'];    //副本标记
        $accurateFlag = $params['accurateFlag'];  //操作系统备份数据页面标记
        $search = $params['search'];
        $storageuuid = $params['storageuuid'];
        $offset = $params['offset'];
        $limit = $params['limit'];
        $order = $params['order'];
        $sort = $params['sort'];
        $localflag = intval($params['localflag']); //本地标志
        $sql = "select unix_timestamp(bbt.timepoint) timepoint, bbt.remarks, bbt.importance_flag, bbt.total_size, bbt.write_size, bbt.storage_uuid, bbt.task_uuid, bbt.timepoint_uuid,
                        bbt.backup_mode,bbt.user_uuid,
                        obt.os_type, obt.os_config, obt.agent_uuid, bsr.storage_type
                from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid and
        			  bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and
                      bbt.import_flag = 2 and   
                      bbt.task_uuid = ? and obt.agent_uuid = ? ";
        $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                     where bbt.timepoint_uuid = obt.timepoint_uuid and
            			   bbt.storage_uuid = bsr.storage_uuid and
                           bbt.available_flag = 1 and
                           bbt.import_flag = 2 and  
                           bbt.task_uuid = ? and obt.agent_uuid = ? ";

        $sqlSortParams = array('', 'bbt.timepoint', 'obt.os_type', 'bbt.total_size', 'bbt.write_size', '', '', '', '');
        $sqlParams = array($taskuuid, $agentuuid);
        $sqlCountParams = array($taskuuid, $agentuuid);

        // if (!$copyFlag && !empty($nodeuuid)) {
        //     $sql .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ?) ";
        //     $sqlCount .= " and (bsr.node_uuid = ? or bbt.real_node_uuid = ?) ";
        //     $sqlParams = array_merge($sqlParams, array($nodeuuid, $nodeuuid));
        //     $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid, $nodeuuid));
        // }
        //新增sql 为了得到最新的增量备份点
        $sqlincre = $sql . " and bbt.backup_mode = ? order by bbt.timepoint desc";
        $sqlincreParams = array_merge($sqlParams, array(xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL']));
        //如果带有搜索条件
        if (!empty($search)) {
            //如果开始时间和结束时间都有 则添加时间查询
            if (!empty($search['startTime']) && !empty($search['endTime'])) {
                $sql .= " and bbt.timepoint between ? and ? ";
                $sqlCount .= " and bbt.timepoint between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($search['startTime'], $search['endTime']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['startTime'], $search['endTime']));
            }
            //如果有类型 则添加类型
            if (!empty($search['timepointType'])) {
                $sql .= " and bbt.backup_mode = ? ";
                $sqlCount .= " and bbt.backup_mode = ? ";
                $sqlParams = array_merge($sqlParams, array($search['timepointType']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['timepointType']));
            }
            //如果有永久标记点 则添加永久标记的搜索
            if (!empty($search['forever']) && $search['forever'] != 2) {
                $sql .= " and bbt.importance_flag = ? ";
                $sqlCount .= " and bbt.importance_flag = ? ";
                $sqlParams = array_merge($sqlParams, array($search['forever']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['forever']));
            }

        }

        //区分异地还是本地
        if (!empty($localflag)) {
            $sql .= " and bbt.data_local_flag = ? ";
            $sqlParams = array_merge($sqlParams, array($localflag));
        }
        // if ($copyFlag) {

        //     if ($storageuuid && !empty($storageuuid)) {
        //         $sql .= " and bsr.storage_uuid = ? ";
        //         $sqlCount .= " and bsr.storage_uuid = ? ";
        //         $sqlParams = array_merge($sqlParams, array($storageuuid));
        //         $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
        //     }
        // }
        if($storageuuid && !empty($storageuuid)){
            $sql .=" and bsr.storage_uuid = ? ";
            $sqlCount .= " and bsr.storage_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($storageuuid));
            $sqlCountParams = array_merge($sqlCountParams, array($storageuuid));
        }

        $sql .= " order by $sort $order limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($offset, $limit));
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $dataincre = $this->dbSelect($sqlincre, $sqlincreParams);
        //得到最新增量备份时间点id
        if (!empty($dataincre)) {
            $latestincretpuuid = $dataincre[0]['timepoint_uuid'];
        }
        $exchangeHandler = new ExchangeRecover;
        $records = array("data" => array());
        $i = 1;

        $userUuidList = array_column($data, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $userUuids = "'" . implode("', '", $userUuidList) . "'";
        $sql = "select user_name, user_uuid from bd_user where user_uuid in ($userUuids)";
        $userData = $this->dbSelect($sql);
        $userMapping = [];
        foreach ($userData as $userInfo) {
            $userMapping[$userInfo['user_uuid']] = $userInfo['user_name'];
        }

        foreach ($data as $d) {
            $mark = $this->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($d['importance_flag']));
            $remark = "";   //备注
            $op = array(1, 2); //1 备注 2 删除 3设置标记
            //时间点在合并中，不让删除
            //如果是最新增量备份时间点不能让他删除
            if ($d['archive_flag'] == xphp_get_config('app', 'FLAG')['SET'] || $copyFlag || $d['timepoint_uuid'] == $latestincretpuuid) {
                $op = array(1);
            }
            //如果是存储在云存储上的和磁带上的，不允许删除
            if ($d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD'] || $d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE']) {
                $op = array(1);
            }

            //不是异地存储并且不是磁带可以标记永久增量
            if (intval($d['storage_type']) != xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE'] && intval($d['storage_type']) != xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE']) {
                $op = array_merge($op, array(3));
            }

            //添加备注
            if (!empty($d['remarks'])) {
                //根据标记是否显示固定备注显示高度
                $top = "";
                if (!empty($mark)) {
                    $top = "top:-4px;";
                }
                $remark .= '<a class="popovers remarktips" data-container="body" data-trigger="hover"
                            data-placement="right" data-content="' . preg_replace('/\"/', "'", $d['remarks']) . '"><i class="viconfont vicon-remark-info"></i></a>';
            }
            $resultInfo['data']['row'][] = array(
                //id
                'id' => $d['timepoint_uuid'],
                //时间点
                'timepoint_uuid' => $d['timepoint_uuid'],
                //时间点名称
                'timepoint_uuid_name' => $this->parseDate($d['timepoint']) . $mark . $remark,
                //类型
                'backup_mode' => $this->getTimepointTypeDes($d['backup_mode']),
                //数据大小
                'total_size' => v1_calsize($d['total_size'], true),
                //写入大小
                'write_size' => v1_calsize($d['write_size'], true),
                //所在存储
                'storage_uuid_name' => $exchangeHandler->getStorageName($d['storage_uuid'], $d['timepoint_uuid']),
                //分区信息
                'os_config' => $this->getVolStr(json_decode($d['os_config'], true)),
                //所有者
                'user_uuid_name' => $userMapping[$d['user_uuid']],
                //其他
                'extra' => array(
                    'uuid' => $d['timepoint_uuid'],
                    'timepointuuid' => $d['timepoint_uuid'],
                    'ostype' => $d['os_type'],
                    'agentuuid' => $agentuuid,
                    'taskuuid' => $taskuuid,
                    'backupmode' => $d['backup_mode'],
                    'remark' => $d['remarks'],
                    'weekly_flag' => false,
                    'monthly_flag' => false,
                    'yearly_flag' => false,
                    'importance_flag' => v1_parse_flag_to_bool($d['importance_flag']),
                    'operate' => $op
                ),
            );
        }
        $resultInfo['data']['total'] = $dataCount[0]['total'];
        return $resultInfo;
    }



    /**
     * 得到分区描述
     * @param unknown $list
     */
    private function getVolStr($list)
    {
        $list = $list['volumes'];
        $info = array();
        if (empty($list)) {
            return $info;
        }
        foreach ($list as $each) {
            $str = $each['display_name'];
            //             if(!empty($each['mount_point'])){
//                 $str .= "(".$each['mount_point'].")";
//             }
            $info[] = $str;
        }
        return $info;

    }


    /**
     * 搜索操作系统时间点
     * @param unknown $params
     */
    public function searchTimepoint($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_OS_GET_GET_SEARCH'),
            'data' => array(),
        );
        $search = $params['search'];
        $week = $params['week'];
        $month = $params['month'];
        $year = $params['year'];
        $forever = $params['forever'];
        $storage = $params['storage'];
        $sql = "select bbt.timepoint_uuid,bbt.storage_uuid, bbt.task_name, bbt.task_type, 
                bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,bbt.task_uuid,
                bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                bbt.detail,bbt.importance_flag, bbt.remarks,bbt.archive_flag,
                obt.os_name, obt.dir_path, obt.os_type,obt.agent_ip,obt.agent_uuid,
                bbt.real_node_uuid,
                bsr.node_uuid          
                from bd_backup_timepoint bbt, os_backup_timepoint obt, bd_storage_resource bsr
                where bbt.timepoint_uuid = obt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                      bbt.available_flag = 1 and 
                      bbt.import_flag = 2 and
                      bbt.module_type = ? and bbt.task_type = ? ";
        $sqldiff = $sql;
        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        if ($week) {
            $sql .= " and bbt.weekly_flag = 1";
        }
        if ($month) {
            $sql .= " and bbt.monthly_flag = 1";
        }
        if ($year) {
            $sql .= " and bbt.yearly_flag = 1";
        }
        if ($forever) {
            $sql .= " and bbt.importance_flag = 1";
        }
        if (!empty($storage)) {
            $sql .= " and bsr.storage_uuid = '".$storage."'";
        }
        if (!empty($search)) {
            $search = '%' . $search . '%';
            $sql .= " and (bbt.timepoint like '" . $search . "' or bbt.task_name like '" . $search . "' or obt.os_name like '" . $search . "' or obt.agent_ip like '" . $search . "')";
        }
        $sql .= ' order by bbt.timepoint';

        $sqlParams = array(xphp_get_config('module', 'MODULE_TYPE')['OS'], xphp_get_config('task', 'TASKTYPE')['OS_BACKUP']);
        $pointData = $this->dbSelect($sql, $sqlParams);
        //所有任务的集合
        $task = array();
        $info = array();
        $os = array();
        //得到当前任务所有uuid
        $currentTaskUUID = $this->getCurrentAllTaskUUID();

        $jobHandler = new JobInfo;

        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $vmHandler = new Backup;
        foreach ($pointData as $key => $point) {
            if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                if ($point['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['DIFFERENTIAL']) {//差异
                    $sqlfull = $sqldiff . " and bbt.timepoint_uuid = ? order by bbt.timepoint";
                    $data1 = $this->dbSelect($sqlfull, array_merge($sqlParams, array($point['depend_point_uuid'])));
                    if (!in_array($data1[0], $pointData)) {
                        $pointData[] = $data1[0];
                    }
                } else {//增量
                    $fullpoint = $this->getFulllPoint($point['depend_point_uuid']);
                    $pointData[] = $fullpoint;
                    $pointData[$key]['depend_point_uuid'] = $fullpoint['timepoint_uuid'];
                }
            }
        }

        //判断是否可被勾选,先判断该代理主机是否存在恢复任务中,如果在任务中就看是否在运行状态  如果在就不让选中

        //默认为可勾选
        $chkDisabledflag = false;
        $timepoint = array();
        $agentList = $this->getAllAgentList();

        foreach ($pointData as $op) {
            $task_uuid = $op['task_uuid'];
            if (!in_array($task_uuid, $task)) {
                $task[] = $task_uuid;
                $taskName = $jobHandler->getTimepointTaskname($op['task_uuid'], $op['task_name']);
                //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
                $taskAvailable = in_array($task_uuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
                //第一层 任务名称
                $info[] = array(
                    "id" => $op['task_uuid'],
                    "pId" => 0,
                    "name" => $name,
                    "open" => false,
                    "nocheck" => false,
                    "type" => 0,
                    "icon" => './img/platform/flag.png',
                    "title" => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': ' . date('Y-m-d H:i:s', $op['task_create_time']),
                    "isParent" => true,
                );
            }

            if (!in_array($op['task_uuid'] . $op['agent_uuid'], $os)) {
                $os[] = $op['task_uuid'] . $op['agent_uuid'];
                //获取系统类型
                $os_type = $op['os_type'];
                $osTypeName = $this->getOSTypeStr($os_type);
                //第二层 任务系统类型
                $info[] = array(
                    "id" => $op['task_uuid'] . $op['agent_uuid'],
                    "pId" => $op['task_uuid'],
                    'name' => $this->getAgentNameByList($agentList, $op['agent_uuid'], $op['os_name'], $op['agent_ip']),
                    "open" => false,
                    "nocheck" => false,
                    "type" => 1,
                    "icon" => './img/os/' . $osTypeName . '.png',
                    "iconSkin" => $osTypeName . 'logo',
                    "title" => xphp_get_lang('WEB_OS_SOURCE_PATH') . ': ' . $op['dir_path'],
                    "taskuuid" => $op['task_uuid'],
                    "agentuuid" => $op['agent_uuid'],
                    "nodeuuid" => $op['node_uuid'],
                    "real_node_uuid" => $op['real_node_uuid'],
                    "createtime" => $this->parseDate($op['task_create_time']),
                    "osType" => $op['os_type'],
                    "isParent" => true,
                    "clickshow" => true
                );
            }


            $osTypeName = $this->getOSTypeStr($op['os_type']);

            //处理名字显示 比如添加有密码的小锁表示 GFS 备注 永久标记点等等
            $details = json_decode($op['detail'], true);
            $name = $this->parseDate($op['timepoint']) . "(" . $this->getTimepointTypeDes($op['backup_mode']) . ")";

            //如果在合并中
            if ($op['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                $name .= xphp_get_lang('WEB_OS_MERGE');
            }

            //判断是否有加密 ,如果有手动输入密码则显示小锁标识
            if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
                $name .= '<i class="fa fa-lock"></i>';
            }

            //是否是数据备份
            $mark = "";
            //添加GFS标识
            $mark .= $vmHandler->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($op['importance_flag']));
            //其父级是否被勾选
            if (!empty($oschecked)) {
                $oschecked = true;
            } else {
                $oschecked = false;
            }

            if ($op['backup_mode'] == xphp_get_config('task', 'BACKUP_MODE')['FULL']) {
                if (!in_array($op['timepoint_uuid'], $timepoint)) {
                    $fullTimepoint = $op['timepoint_uuid'];
                    $timepoint[] = $op['timepoint_uuid'];
                    $info[] = array(
                        "agentuuid" => $op['agent_uuid'],
                        "checked" => $oschecked,
                        "createtime" => $this->parseDate($op['task_create_time']),
                        "osname" => $op['os_name'],
                        "ostype" => $op['os_type'],
                        "icon" => "./img/platform/timepoint-f.png",
                        "id" => $op['timepoint_uuid'],
                        "name" => $name . $mark,
                        "oldname" => $name,
                        "nodeuuid" => $op['node_uuid'],
                        "real_node_uuid" => $op['real_node_uuid'],
                        "pId" => $op['task_uuid'] . $op['agent_uuid'],
                        "path" => $op['dir_path'],
                        "pointname" => $this->parseDate($op['task_create_time']),
                        "taskuuid" => $op['task_uuid'],
                        "timepointuuid" => $op['timepoint_uuid'],
                        "title" => xphp_get_lang('WEB_OS_SOURCE_PATH') . "=> " . $op['dir_path'],
                        "type" => 2,
                        "iconSkin" => $osTypeName . 'logo',
                        "chkDisabled" => $chkDisabledflag,
                        //是否是在任务中
                        'agentuuidInTask' => $chkDisabledflag,
                    );

                    $incList = $this->getSearchIncTimepoint($op['timepoint_uuid']);
                    if (!empty($incList)) {
                        foreach ($incList as $op) {
                            if ($op['timepoint_uuid'] == $fullTimepoint) {
                                continue;
                            }

                            $osTypeName = $this->getOSTypeStr($op['os_type']);

                            //处理名字显示 比如添加有密码的小锁表示 GFS 备注 永久标记点等等
                            $details = json_decode($op['detail'], true);
                            $name = $this->parseDate($op['timepoint']) . "(" . $this->getTimepointTypeDes($op['backup_mode']) . ")";

                            //如果在合并中
                            if ($op['archive_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                                $name .= xphp_get_lang('WEB_OS_MERGE');
                            }

                            //判断是否有加密 ,如果有手动输入密码则显示小锁标识
                            if (!empty($details) && $details['password_auto_flag'] == 2 && !empty($details['password'])) {
                                $name .= '<i class="fa fa-lock"></i>';
                            }
                            //是否是数据备份
                            $mark = "";
                            //添加GFS标识
                            $mark .= $vmHandler->pGetTimepointMark(false, false, false, v1_parse_flag_to_bool($op['importance_flag']));
                            //其父级是否被勾选
                            if (!empty($oschecked)) {
                                $oschecked = true;
                            } else {
                                $oschecked = false;
                            }
                            //第四层 增备差异点
                            $info[] = array(
                                "agentuuid" => $op['agent_uuid'],
                                "checked" => $oschecked,
                                "createtime" => $this->parseDate($op['task_create_time']),
                                "osname" => $op['os_name'],
                                "ostype" => $op['os_type'],
                                "icon" => $this->getTimepointIcon($op['backup_mode']),
                                "id" => $op['timepoint_uuid'],
                                "name" => $name . $mark,
                                "oldname" => $name,
                                "nodeuuid" => $op['node_uuid'],
                                "real_node_uuid" => $op['real_node_uuid'],
                                "pId" => $fullTimepoint,
                                "path" => $op['dir_path'],
                                "pointname" => $this->parseDate($op['task_create_time']),
                                "taskuuid" => $op['task_uuid'],
                                "timepointuuid" => $op['timepoint_uuid'],
                                "title" => xphp_get_lang('WEB_OS_SOURCE_PATH') . "=> " . $op['dir_path'],
                                "type" => 3,
                                //                 "nocheck" => !$nocheckflag,
                                "nocheck" => false,
                                "iconSkin" => $osTypeName . 'logo',
                                "chkDisabled" => true,
                                //是否是在任务中
                                'agentuuidInTask' => $chkDisabledflag,
                            );

                        }

                    }
                    continue;
                }
            }
        }
        $resultInfo['data'] = $info;
        return $resultInfo;
    }

    /**
     * 搜索时获取增量点
     */
    public function getSearchIncTimepoint($depend_point_uuid)
    {
        $sql = "WITH RECURSIVE cte AS (
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, 
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,
                   obt.os_name, obt.dir_path, obt.os_type, obt.agent_ip, obt.agent_uuid,
                   bbt.real_node_uuid,
                   bsr.node_uuid
            FROM bd_backup_timepoint bbt
            JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid
            JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            WHERE bbt.timepoint_uuid = ? 
                  AND bbt.available_flag = 1 
                  AND bbt.import_flag = 2
        
            UNION ALL
        
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, 
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,
                   obt.os_name, obt.dir_path, obt.os_type, obt.agent_ip, obt.agent_uuid,
                   bbt.real_node_uuid,
                   bsr.node_uuid
            FROM bd_backup_timepoint bbt
            JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid
            JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            JOIN cte ON bbt.depend_point_uuid = cte.timepoint_uuid
            WHERE bbt.available_flag = 1 
                  AND bbt.import_flag = 2
        )
        SELECT * FROM cte ORDER BY timepoint;
        ";
        $result = $this->dbSelect($sql, array($depend_point_uuid));
        if (empty($result)) {
            return array();
        }
        return $result;
    }




     /**
     * 获取主机任务信息(修改任务用)
     */
    public function getBackupTaskAllInfo($params){
         //返回数据
         $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('UI_OS_GET_BASIC_INFO'),
            'data' => array(),
        );
        $vmHandler = new VmBackUp();
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.thread_num, bt.strategy_group_uuid, bt.backup_server_ip, bt.transport_ip_segment, 
                       ot.serial_snapshot_flag, ot.cbt_flag,ot.transport_priority, ot.valid_data_flag,ot.silent_snapshot_flag,
                       ol.agent_uuid, ol.dir_path, ol.exclude_partition_list,
                	   brs.strategy_type, brs.number, brs.strategy_mode,
                	   bts.encrypt_flag, bts.compress_flag,bts.network_uuid, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method,
                	   bss.deduplication_flag, bss.block_size, bss.compressed_flag, bss.compress_method, bss.encrypted_flag, bss.password_auto_flag, bss.password, bss.encrypt_method,
                       bsr.storage_type
                from os_task ot, os_list ol, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss, bd_task bt
                left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid
                where bt.task_uuid = ot.task_uuid
                and ot.task_uuid = ol.task_uuid 
                and bt.strategy_id = brs.strategy_id
                and bt.strategy_id = bts.strategy_id
                and bt.strategy_id = bss.strategy_id
                and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        
        $info = array();
        if($data){
            $info = array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //策略uuid
                "strategyuuid" => $data[0]['strategy_group_uuid'],
                //任务名
                'taskname' => $data[0]['task_name'],
//                 //虚拟化类型
//                 'hypervisor' => $data[0]['hypervisor_type'],
//                 //appliance
//                 'applianceuuid' => $data[0]['appliance_uuid'],
                //保留策略
                'brs' => array(
                    'type' => intval($data[0]['strategy_type']),
                    'strategyMode' => intval($data[0]['strategy_mode']),
                    'number' => intval($data[0]['number']),
                ),
                //节点
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'storageuuid' => $data[0]['storage_uuid'],
                    'storage_type' => $data[0]['storage_type']
                ),
                //传输策略
                'bts' => array(
                    'encrypt' =>  v1_parse_flag_to_bool($data[0]['encrypt_flag']),
                    'compress' => v1_parse_flag_to_bool($data[0]['compress_flag']),
//                     'mode' => $this->getTransportModeToArr($data[0]['transport_priority'],$data[0]['hypervisor_type']),
                    'network' => $data[0]['network_uuid'],
                    'reconnect_times' => intval($data[0]['reconnect_times']),
                    'reconnect_interval' => intval($data[0]['reconnect_interval']),
                    'encrypt_method' => intval($data[0]['transport_method']),
                ),
                //存储策略
                'bss' => array(
                    'deduplication' => v1_parse_flag_to_bool($data[0]['deduplication_flag']),
                    'blocksize' => intval($data[0]['block_size'])/1024,
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    'password' =>  base64_encode(v1_pt_pass_decrypt($data[0]['password'])),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method']),
                ),
                //备份模式
                'mode' => array(
                    'threadnum' => intval($data[0]['thread_num']),      //线程数量
                    //传输模式
                    'transport_priority' => intval($data[0]['transport_priority']),
                    //快照
                    'serial_snapshot_flag' => v1_parse_flag_to_bool($data[0]['serial_snapshot_flag']),
                    //cbt
                    'cbt_flag' => v1_parse_flag_to_bool($data[0]['cbt_flag']),
                    //获取有效数据
                    'valid_data_flag' => v1_parse_flag_to_bool($data[0]['valid_data_flag']),
                    //静默快照
                    'silent_snapshot_flag' => v1_parse_flag_to_bool($data[0]['silent_snapshot_flag']),

                ),
//                 //虚拟机信息
//                 'vm_info' => $this->getVMEditInfo($taskUUID),
                //时间策略
                'timestrategy' =>  $vmHandler->getTimeStrategyInfo($data[0]['strategy_id']),
                //备份方式
                'timeStrategyBackupType' => $vmHandler->getTimeStrategyBackupType($data[0]['strategy_id']),
                //限速策略
                'speedInfo' => $vmHandler->getSpeedGlobalStrategyInfo($taskUUID),
//                 'nosnapshot' => $this->getXenSnapshotFlag($taskUUID),
//                 'backup_server_ip' => $data[0]['backup_server_ip'],
//                 'transport_ip_segment' => $data[0]['transport_ip_segment']
                'backup_oss_info' => $this->getBackupOssInfo($taskUUID),
            );
        }
        $resultInfo['data'] = $info;
        return $resultInfo;
    }
    


     /**
     * 得到主机排出分区信息
     * @param unknown $taskUUID
     */
    public function getBackupOssInfo($taskUUID){
        $info = array();
        $sql = "select agent_uuid, os_name, agent_ip, dir_path, exclude_partition_list from os_list where task_uuid = ?";
        $result = $this->dbSelect($sql,array($taskUUID));
        $agentList = $this->getAllAgentList();
        foreach ($result as $each){
            $disk_value = json_decode($each['exclude_partition_list'],true);
            $disk_list = array();
            $disk_des = array();
            foreach ($disk_value as $one){
                $disk_list[] = $one['volume_uuid'];
                $disk_des[] = $one['volume_des'];
            }
            $info[] = array(
                'agent_group_uuid' => $this->agentuuidGetgroupuuid($each['agent_uuid']),
                'agent_uuid' => $each['agent_uuid'],
                'agent_name' => $this->getAgentNameByList($agentList, $each['agent_uuid'], $each['os_name'], $each['agent_ip']),
                'os_name' => $each['os_name'],
                'agent_ip' => $each['agent_ip'],
                'os_config' => array(
                    'disk_list' => $disk_list,
                    'disk_des' => $disk_des,
                    'disk_value' => $disk_value,
                ),
                'dir_path' => $each['dir_path'],
                
            );
        }
        return $info;
    }
    


     /**
     * 通过agentuuid获取到groupuuid
     * @param unknown $agentuuid
     */
    private function agentuuidGetgroupuuid($agentuuid){
        $sql = "select group_uuid from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql,array($agentuuid));
        $group_uuid = $result[0]['group_uuid'];
        return $group_uuid;
    }



















}
