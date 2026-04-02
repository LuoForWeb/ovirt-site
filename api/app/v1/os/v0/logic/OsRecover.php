<?php

namespace app\v1\os\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Recover;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Index;
use app\v1\resources\v0\logic\Node;
use app\v1\os\v0\logic\OsBackUp;
use xphp\BLLHandler;

/**
 * note          操作系统 之恢复管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class OsRecover extends Recover
{
    /**
     * 得到主机任务名(恢复)
     * @param unknown $params
     */
    public function getOSRecoverTaskName()
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_OS_GET_RECOVERY_TASK_NAME'),
            'data' => array(),
        );
        $taskName = xphp_get_lang('WEB_OS_HOST_TASK');
        $taskName .= xphp_get_lang('WEB_OS_RECOVERY_TASK');
        $logicOsBackup = new OsBackUp;
        $resultInfo['data'] = $logicOsBackup->getValidTaskName($taskName);
        return $resultInfo;
    }


    /**
     * 得到所有IP地址用于组装option
     * return
     */
    public function getOptionIP($params)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_GET_RECOVERY_TASK_NAME'),
            'data' => array(),
        );
        $timepoint_uuid = $params['timepoint_uuid'];
        $os_type = intval($params['os_type']);//1表示Windows 2表示Linux
        $agent_uuid = $params['agent_uuid'];
        //先获取该用户分配了哪些主机
        //获取该用户拥有的所有agent_uuid集合
        $resourceHandler = new Index;
        //userLevel是1 2 3就全部显示
        $sql = "select agent_name, hostname, agent_uuid, os_type, ip, online_flag, authorization_module from bd_agent ";
        if (!in_array($_SESSION['userLevel'], [1, 2, 3])) {
            $agent_list = $resourceHandler->pGetUserAllResource(xphp_get_user_info()['userUuid'], 10);
            $agent_list = array_column($agent_list, 'resource_uuid');
            if (empty($agent_list)) {
                return json_encode(array());
            } else {
                $agent_uuids = implode(',', array_map(function ($item) {
                    return "'{$item}'"; }, $agent_list));
                $sql .= " where agent_uuid in ($agent_uuids) ";
                //目前只能 win->win/linux, linux->linux
                //排除掉nas的agent agent_type = 3为nas的
                if ($os_type == 2) {
                    $sql .= " and os_type = 'Linux' and agent_type != 3";
                } else {
                    $sql .= " and agent_type != 3";
                }
            }
        } else {
            //目前只能 win->win/linux, linux->linux
            //排除掉nas的agent agent_type = 3为nas的
            if ($os_type == 2) {
                $sql .= " where os_type = 'Linux' and agent_type != 3";
            } else {
                $sql .= " where agent_type != 3";
            }
        }
        $result = $this->dbSelect($sql, array());
        $info = array();
        foreach ($result as $op) {
            //处理名字
            $titleDes = (new OsBackUp())->getAgentName($op['hostname'], $op['agent_name'], $op['ip']);
            if ($agent_uuid == $op['agent_uuid']) {
                $titleDes .= xphp_get_lang('WEB_OS_ORIGINAL_HOST');
            }
            if ($op['online_flag'] != 1) {
                $titleDes .= xphp_get_lang('WEB_OS_OFFLINE');
            }
            //如果为空 则是未添加授权的新虚拟机
            if (empty($op['authorization_module'])) {
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' => 0,//0为未授权主机的新机子
                );
                continue;
            }
            $authorization = json_decode($op['authorization_module'], true);
            //             如果都是false 那么也是未授权的
            if (
                $authorization['file'] == false && $authorization['vm'] == false && $authorization['mysql'] == false && $authorization['oracle'] == false &&
                $authorization['sqlserver'] == false && $authorization['dm'] == false && $authorization['os'] == false && $authorization['cdp']
                == false && $authorization['desktop'] == false && $authorization['database'] == false
            ) {
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' => 1,//1为一个未授权的机子
                );
                continue;
            }
            if ($authorization['os'] == true) {
                $info[] = array(
                    'os_type' => $op['os_type'],
                    'agent_name' => $titleDes,
                    'agent_uuid' => $op['agent_uuid'],
                    'ip' => $op['ip'],
                    'type' => 2,//2为授权有主机的机子
                );
            }
        }
        $resultInfo['data'] = $info;
        return $resultInfo;
    }


    /**
     * 连接测试
     * @param $params IPList数组
     * return {re:true/false 成功或失败 msg:信息}
     *
     */
    public function linkTest($params, $osmotionflag = false)
    {
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('WEB_OS_LINK_TEST'),
            'data' => array(),
        );
        //时间点uuid集合
        $timepoint_uuid_list = $params['timepointList'];
        //代理主机uuid集合
        $agent_uuid_list = $params['agentList'];
        //检测是否在任务中
        $this->getAgentUsed($agent_uuid_list, $osmotionflag);
        //获取到时间点的数据
        $old_list = $this->getOSBackupDiskInfo($timepoint_uuid_list);
        //获取代理主机数据的方式有两种 一种是调用接口实时获取 一种是数据库获取
        //先写一种实时获取数据的方式 后面有需要可以写实时没获取到就从数据库获取
        $newList = $this->getNewDiskInfo($agent_uuid_list);

        $result = array(
            'oldList' => $old_list,
            'newList' => $newList,
            'encrypt_flag' => $this->getEncryFlag($timepoint_uuid_list),
        );
        $resultInfo['data'] = $result;
        return $resultInfo;
    }



    /**
     * 得到该代理主机是否在以下2种情况中
     * 1备份任务中 任务状态正在进行中
     * 2恢复任务中 任务状态任意
     * 2种情况都不让进入下一步
     */
    private function getAgentUsed($agent_uuid_list, $osmotionflag)
    {
        if (empty($agent_uuid_list)) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_OS_GET_TIMEPOINT_INFO'), '', 'warning'));
        }
        foreach ($agent_uuid_list as $eachuuid) {
            //查找瞬时恢复任务id
            $sql1 = "select ol.task_uuid from os_list as ol, bd_task as bt where ol.task_uuid = bt.task_uuid and bt.task_type in (49,50) and ol.inst_recovery_agent_uuid = ?";
            $result1 = $this->dbSelect($sql1, array($eachuuid));
            //查找迁移任务id
            $sql2 = "select ol.task_uuid from os_list as ol, bd_task as bt where ol.task_uuid = bt.task_uuid and bt.task_type = 50 and ol.agent_uuid = ?";
            $result2 = $this->dbSelect($sql2, array($eachuuid));
            //查找其他任务id
            $sql3 = "select ol.task_uuid from os_list as ol, bd_task as bt where ol.task_uuid = bt.task_uuid and bt.task_type not in (49,50) and ol.agent_uuid = ?";
            $result3 = $this->dbSelect($sql3, array($eachuuid));


            //融合三个sql语句的结合
            $resultTaskSql = array_merge($result1, $result2, $result3);
            //判断是为空 为空则直接退出
            if (empty($resultTaskSql)) {
                return;
            }
            //得到每一个task信息
            foreach ($resultTaskSql as $eachTask) {
                //查询每一个task信息
                $taskSql = "select task_name,task_type,task_status from bd_task where task_uuid = ? and module_type = 5";
                $resultSql = $this->dbSelect($taskSql, array($eachTask['task_uuid']));
                if (empty($resultSql)) {
                    return;
                }
                foreach ($resultSql as $each) {

                    //如果是操作系统迁移  不能在备份/迁移/恢复任务中  可以在瞬时恢复任务的主机中

                    //如果是在备份中并且任务正在进行中
                    if ($each['task_type'] == 35 && $each['task_status'] == 2) {
                        exit($this->muOpResult(false, xphp_get_lang('WEB_OS_HOST_IP'), xphp_get_lang('WEB_OS_IS_TASK') . $each['task_name'] . xphp_get_lang('WEB_OS_WAIT_BACKUP_RETRY'), 'warning'));
                    }

                    //如果是在恢复中
                    if ($each['task_type'] == 36) {
                        exit($this->muOpResult(false, xphp_get_lang('WEB_OS_HOST_IP'), xphp_get_lang('WEB_OS_IS_TASK') . $each['task_name'] . xphp_get_lang('WEB_OS_RECOVERY_TASK_RETRY'), 'warning'));
                    }

                    //如果是瞬时恢复任务中
                    if ($each['task_type'] == 49) {
                        if (!$osmotionflag) {
                            exit($this->muOpResult(false, xphp_get_lang('WEB_OS_HOST_IP'), xphp_get_lang('WEB_OS_IS_TASK') . $each['task_name'] . xphp_get_lang('WEB_OS_INST_RECOVERY_TASK_RETRY'), 'warning'));
                        }
                    }

                    //如果是迁移任务中
                    if ($each['task_type'] == 50) {
                        exit($this->muOpResult(false, xphp_get_lang('WEB_OS_HOST_IP'), xphp_get_lang('WEB_OS_IS_TASK') . $each['task_name'] . xphp_get_lang('WEB_OS_MOTION_TASK_RETRY'), 'warning'));
                    }
                }
            }
        }

    }



    /**
     * 根据时间点得到此时间点备份的分区或磁盘信息
     * $params 一个timepointuuid的数组 适应后面多对多
     * $params [timepointuuid,timepointuuid,timepointuuid]
     * return 返回一个数组 分区磁盘信息
     */
    public function getOSBackupDiskInfo($params)
    {
        //         $timepointuuid_list = $params['pointList'];
        $timepointuuid_list = $params;
        $this->paramsCheck($timepointuuid_list);
        $paramsList = implode("','", $timepointuuid_list);
        $sql = "select obt.timepoint_uuid, obt.agent_uuid, obt.os_name, obt.agent_ip, obt.os_type, obt.os_config, 
                        unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode   
                        from os_backup_timepoint obt, bd_backup_timepoint bbt  
                        where obt.timepoint_uuid = bbt.timepoint_uuid 
                        and obt.timepoint_uuid in ('" . $paramsList . "')";
        $result = $this->dbSelect($sql, array());
        if (empty($result)) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_OS_GET_TIMEPOINT_INFO'), '', 'warning'));
        }
        $oldList = array();
        $logicOsBackup = new OsBackUp;
        foreach ($result as $d) {
            $agent_uuid = $d['agent_uuid'];
            $osinfo = $logicOsBackup->getOSType($agent_uuid);
            $ostype = $osinfo['os_type'];
            $os_config = json_decode($d['os_config'], true);
            //得到分区信息
            $diskInfo = array();
            foreach ($os_config['disk_list'] as $oneDisk) {
                $volume_set = $oneDisk['volume_set'];
                foreach ($volume_set as $oneVolume) {
                    if ($oneVolume['show_flag'] != 1) {
                        continue;
                    }
                    $diskInfo[] = array(
                        $oneVolume['vol_uuid'],
                        $oneVolume['display_name'],
                        $oneVolume['total_size'],
                        $oneVolume['system_flag'],
                        v1_calsize($oneVolume['total_size'], true),
                        $ostype,
                        $oneVolume['mount_point'],
                    );
                }
            }
            //组合数据
            $oldList[] = array(
                'os_uuid' => $d['timepoint_uuid'],
                'os_name' => $d['agent_ip'],
                'os_str' => $this->parseDate($d['timepoint']) . "(" . $this->getTimepointTypeDes($d['backup_mode']) . ")",
                'diskInfo' => $diskInfo,
                'agent_uuid' => $d['agent_uuid'],
                'os_type' => $d['os_type'],
            );
        }
        //         return json_encode($oldList);
        return $oldList;
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
     * 实时获取物理主机上的数据
     * @param unknown $agentList 代理主机列表
     * diskinfo中[uuid,名称,容量,是否是系统盘]
     * return array
     */
    public function getNewDiskInfo($agentList)
    {
        $getdata = array();
        $logicOsBackup = new OsBackUp;
        foreach ($agentList as $oneAgentList) {
            $info = array(
                'uuid' => $oneAgentList,
            );
            $getdata[$oneAgentList] = $logicOsBackup->getOSBackupAgentInfo($info, true)['data'];
        }
        ;
        $newList = array();
        foreach ($getdata as $agentuuid => $data) {
            //得到主机类型
            $osinfo = $logicOsBackup->getOSType($agentuuid);
            $ostype = $osinfo['os_type'];
            //得到分区信息
            $diskInfo = array();
            //得到磁盘信息
            $diskInfo2 = array();
            foreach ($data as $oneDisk) {
                //如果为磁盘为空则跳过
                if (empty($oneDisk)) {
                    continue;
                }
                $volume_set = $oneDisk['volume_set'];
                foreach ($volume_set as $oneVolume) {
                    if ($oneVolume['show_flag'] != 1) {
                        continue;
                    }
                    //这里通过与值计算看是否是U盘分区,如果是可移动设备则不显示
                    //获取分区的type值
                    $volume_type = $oneVolume['type'];
                    //后端给的一个检测是否是移动设备的值(应该是一个移动设备的type)
                    $num_type = 0x8000;
                    //计算与运算
                    $result_type = $volume_type & $num_type;
                    // if ($result_type != 0) {
                    //     continue;
                    // }
                    $removabledes = "";
                    if($oneVolume['removable_flag'] == 1){
                        $removabledes = "(". xphp_get_lang('WEB_OS_REMOVE_DEVICE') .")";
                    }
                    $diskInfo[] = array(
                        $oneVolume['vol_uuid'],
                        $oneVolume['display_name'].$removabledes,
                        $oneVolume['total_size'],
                        $oneVolume['system_flag'],
                        v1_calsize($oneVolume['total_size'], true)

                    );
                }
                //有可能分区显示 但是磁盘不显示的情况  这种 在最下面处理
                if ($oneDisk['show_flag'] != 1) {
                    continue;
                }
                //这里通过和后端对接 后端type枚举19为可移动设备,这里要排除掉可移动设备
                // if ($oneDisk['type'] == 19) {
                //     continue;
                // }
                $removabledes2 = "";
                if($oneDisk['type'] == 19){
                    $removabledes2 =  "(". xphp_get_lang('WEB_OS_REMOVE_DEVICE') .")";
                }
                $diskInfo2[] = array(
                    $oneDisk['disk_uuid'],
                    $oneDisk['display_name'].$removabledes2,
                    $oneDisk['total_size'],
                    $oneDisk['system_flag'],
                    v1_calsize($oneDisk['total_size'], true)

                );
            }
            //得到的数组看卷是否为空 如果为空 则开启重建分区
            $builtFlag = false;
            if (empty($diskInfo)) {
                $builtFlag = true;
            }
            //组合数据
            $newList[] = array(
                'net_model' => $osinfo['net_model'],
                'agent_uuid' => $agentuuid,
                'group_uuid' => $this->agentuuidGetgroupuuid($agentuuid),
                'agent_name' => $logicOsBackup->getAgentName($osinfo['hostname'], $osinfo['agent_name'], $osinfo['ip']),
                'builtFlag' => $builtFlag,
                'diskInfo' => $diskInfo,
                'diskInfo2' => $diskInfo2,
            );
        }
        return $newList;
    }



    /**
     * 通过agentuuid获取到groupuuid
     * @param unknown $agentuuid
     */
    private function agentuuidGetgroupuuid($agentuuid)
    {
        $sql = "select group_uuid from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql, array($agentuuid));
        $group_uuid = $result[0]['group_uuid'];
        return $group_uuid;
    }



    /**
     * 获取是否有加密的flag,用于前端显示,适用于1个 ,后面做成多个再改结构
     * @param unknown $timepoint_uuid_list
     */
    private function getEncryFlag($timepoint_uuid_list)
    {
        $flag = 2;
        //由于目前只有一个时间点 就按照一个来处理
        $timepoint_uuid = $timepoint_uuid_list[0];
        if (empty($timepoint_uuid)) {
            return $flag;
        }
        $sql = "select detail, encrypted_flag from bd_backup_timepoint where timepoint_uuid = ?";
        $result = $this->dbSelect($sql, array($timepoint_uuid));
        $detail = $result[0]['detail'];
        $detail = json_decode($detail, true);
        $encrypted_flag = $result[0]['encrypted_flag'];
        //开始判断是否有设置密码
        if ($encrypted_flag == 1 && $detail['password_auto_flag'] == 2) {
            $flag = 1;
        }
        return $flag;
    }


    /**
     * 创建主机恢复任务
     * @param unknown $params
     */
    public function createOSRecoverJob($params)
    {
        //public params
        //得到名称
        $task_name = htmlspecialchars_decode($params['taskName']);
        //得到全局策略组uuid
        $strategygroupuuid = $params['strategygroupuuid'];
        //得到模块编号
        $module_type = xphp_get_config('module', 'MODULE_TYPE')['OS'];
        //得到恢复方式
        $recovery_time_type = intval($params['time_info']['type']);
        //得到恢复位置  默认传1
        $recovery_position = 1;
        $backupHandler = new Backup;
        $bllHandler = new BLLHandler;
        //组合传输策略
        $transport_strategy = $backupHandler->groupTransportStrategy($params['high_info']['transfer']);
        //组合时间策略
        $time_strategy_list = $this->groupRecoverTimeList($params['time_info']);
        //组合--返回task_name,module_type,recovery_position,recovery_time_type,time_strategy_list,transport_strategy
        $pfMsg = $bllHandler->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $time_strategy_list,
            $transport_strategy
        );
        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY'];
        //得到限速策略
        $pfMsg['speed_limit_strategy_list'] = "";
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $backupHandler->groupTaskSpeedGlobalList($params['speed_info']);

        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        //得到备份节点ip (没有 传空值)
        $pfMsg['backup_server_ip'] = '';
        //得到指定网段 (没有 传空值)
        $pfMsg['transport_ip_segment'] = '';
        //得到线程数
        $pfMsg['thread_num'] = $params['high_info']['threadnum'];
        //根据时间点得到node_uuid  因为时间点只能选择同一节点下的  所以数据里所有时间点的节点是同一时间点  随便传一个时间点即可
        $nodeHandler = new Node;
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['recover_info'][0]['recovery_timepoint_uuid']);
        $pfMsg['node_uuid'] = $nodeuuid;
        //---------以下主机恢复私有-------------
        $pfMsg['recovery_oss_info'] = $params['recover_info'];
        //目前单个恢复 则把密码信息放在外面 后面有需求做成多个的时候再改结构
        $pfMsg['timepoint_password'] = base64_decode($params['timepoint_password']);

        //发送消息
        //得到恢复操作码
        $opName = "BD_TASK_OP_RECOVERY_CREATE";
        $msg = json_encode($pfMsg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg);

        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode;
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['time_info']['type'];
            if (xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY'] == $recoveryType) {
                $startResult = $this->startRecoverJob($task_name);
            } else {
                $startResult = true;
            }
            if ($startResult) {
                //启动任务成功,直接返回创建任务成功
                return $this->muOpResult($result, $operate, $msg);
            } else {
                //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
                return $this->muOpResult($result, $operate, $msg);
            }
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }


    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName  任务名
     */
    private function startRecoverJob($taskName)
    {
        $sql = "select task_uuid from bd_task where task_name = ? order by id desc";
        $data = $this->dbSelect($sql, array($taskName));
        if (!$data)
            return false;

        //任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
        $params = array(
            'task_uuid' => $data[0]['task_uuid'],
            'backup_mode' => 1,
            'time_strategy_id' => 0,
            'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
        );
        //调用系统统一启动任务接口.不重新写
        $nodeHandler = new Node;
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($data[0]['task_uuid']);   //任务UUID对应的存储节点uuid
        $msg = json_encode($params);
        $sync = FALSE;
        $command = TRUE;
        $opName = 'BD_TASK_OP_RECOVERY_START';
        $mbResult = $this->mbOSMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];

        //这里直接返回成功或失败 bool
        return $result;
    }















}