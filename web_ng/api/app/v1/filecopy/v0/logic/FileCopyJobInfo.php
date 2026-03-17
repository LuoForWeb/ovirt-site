<?php

namespace app\v1\filecopy\v0\logic;
use app\v1\common\logic\Backup;
use \app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\NodeOpcode;
use app\v1\user\v0\logic\User;

/**
 * note          文件同步 -- 任务信息 logic
 * @author       wuxian@vinchin.com
 * @date         2024/7/17 16:15
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class FileCopyJobInfo extends JobInfo
{
    /**
     * 获取文件同步任务所有信息（用于修改）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getFileCopyBasicInfo($params = [])
    {
        $taskUUID = $params['job_uuid'];
        $info = JobInfo::instance()->getJobInfo($taskUUID);
        $basicInfo = array();
        if (!$info) {
            return array('flag' => false);
        }
        //查询自己模块独有的数据
        $sql = "SELECT
                    bt.task_name,
                    bt.user_uuid,
                    stpl.source_uuid,
                    stpl.target_uuid,
                    stpl.source_type,
                    stpl.target_type,
                    st.detail,
                    st.scan_thread_number,
                    st.scan_file_speed,
                    st.skip_file_alarm_flag,
                    st.skip_file_alarm_min_num,
                    st.skip_file_alarm_min_ratio,
                    st.snap_shot_flag,
                    st.permission_operate_flag,
                    st.same_file_strategy
                FROM
                    sync_task_path_list stpl
                    LEFT JOIN bd_task bt ON bt.task_uuid = stpl.task_uuid
                    LEFT JOIN sync_task st ON st.task_uuid = stpl.task_uuid
                WHERE
                    stpl.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        switch ($info['job_status']) {
            case xphp_get_config('task', 'TASKSTATUS')['RUNNING']:
                $current_size = $info['complate_size'];
                $total_size = $info['total_size'];
                break;
            default:
                $current_size = xphp_get_config('app', 'NULLSPACE');
                $total_size = xphp_get_config('app', 'NULLSPACE');
                break;
        }
        $basicInfo = array(
            'job_name' => $info['job_name'],
            'job_type' => $info['job_type'],
            'job_type_des' => $info['job_type_des'],
            'submodle_type' => $info['submodle_type'],
            'job_status'    => $info['job_status'],
            'module_type' => $info['module_type'],
            'module_type_des' => $info['module_type_des'],
            'total_size' => $total_size,
            'current_size' => $current_size,
            'create_time' => $info['create_time'],
            'start_time' => $info['start_time'],
            'interval_time' => $info['interval_time'],
            'job_stage' => $info['job_stage'],
            'speed' => $info['speed'],
            'speed_time' => $info['speed_time'],
            'thread_num' => $info['thread_num'],
            'timestamp' => $info['timestamp'],
            'progress' => $info['progress'],
            'next_time' => $info['next_time'],
            'speed_limit'  => $info['speed_limit'], // 限速策略
            'time_strategy'  => $info['time_strategy'], // 时间策略
            'reserve_strategy'  => $info['reserve_strategy'], // 保留策略
            'transport_strategy'  => $info['transport_strategy'], // 传输策略
            'retry_strategy' => $info['retry_strategy'],
            'source_uuid' => $data[0]['source_uuid'],
            'target_uuid' => $data[0]['target_uuid'],
            'source_type' => $data[0]['source_type'],
            'target_type' => $data[0]['target_type'],
            'src_os_type' => $this->getOsType($data[0]['source_type'],$data[0]['source_uuid']),
            'des_os_type' => $this->getOsType($data[0]['target_type'],$data[0]['target_uuid']),
            'encrypt_flag' => v1_parse_flag_to_bool($data[0]['encrypt_flag'],true),
            'snap_shot_flag' => v1_parse_flag_to_bool($data[0]['snap_shot_flag'],true),
            'skip_file_alarm_flag' => v1_parse_flag_to_bool($data[0]['skip_file_alarm_flag'],true),
            'skip_file_alarm_min_num' => $data[0]['skip_file_alarm_min_num'],
            'skip_file_alarm_min_ratio' => $data[0]['skip_file_alarm_min_ratio'],
            'scan_thread_number' => $data[0]['scan_thread_number'],
            'scan_file_speed' => $data[0]['scan_file_speed'],
            'permission_operate_flag' => v1_parse_flag_to_bool($data[0]['permission_operate_flag'],true),
            'same_file_strategy' => $data[0]['same_file_strategy'],
            'filter_info' => json_decode($data[0]['detail'],true),
            'file_list' => $this->getFileList($taskUUID),
            'speed_info' => $this->getSpeedStrategy($taskUUID),//修改任务用
            'current_stage' => $info['current_stage'], // 任务阶段
            'stage_percent' => $info['stage_percent'], // 任务百分比
            'current_stage_value' => $info['current_stage_value'], // 任务阶段+任务百分比
            'storage_info' => $info['storage_info'],
            'ignore_resource_limiting_flag' => $info['ignore_resource_limiting_flag'],
            'task_user_uuid' => $data[0]['user_uuid'],
        );
        return $basicInfo;
    }

    /**
     * 获取操作系统类型
     * @param int $type 类型
     * @param int $type uuid
     * @return string 操作系统类型
     */
    public function getOsType($type,$uuid)
    { 
        $os = '';
        switch ($type) {
            case 1:   //fs
                $os = 'agent';
                $data = $this->dbSelect("SELECT os_type FROM bd_agent WHERE agent_uuid = ?", array($uuid));
                if(!empty($data)) {
                    $os = $data[0]['os_type'];
                }
                break;
            case 2:   //nas
                $os = 'nas';
                break;
            case 3:   //hadoop
                $os = 'hadoop';
                break;
            case 4:   //obs
                $os = 'obs';
                break;
        }
        return $os;
    }

    /**
     * 获取复制列表信息（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getFileCopyList($params = [])
    {
        $taskUuid = $params['job_uuid'];
        $sql = "SELECT
                    bt.task_type,
                    bt.task_status,
                    bri.speed,
                    bri.total_object_size,
                    bri.total_object_completed_size,
                    sri.object_uuid,
                    sri.current_fs_count,
                    sri.total_fs_count,
                    sri.current_object_total_size,
                    sri.current_object_completed_size,
                    sri.current_sync_file_list,
                    stpl.source_uuid,
                    stpl.target_uuid,
                    stpl.source_type,
                    stpl.target_type,
                    st.detail  
                FROM
                    sync_running_info sri
                    LEFT JOIN bd_task bt ON sri.task_uuid = bt.task_uuid
                    LEFT JOIN sync_task_path_list stpl ON stpl.task_uuid = bt.task_uuid 
                    LEFT JOIN bd_running_info bri ON bri.task_uuid = bt.task_uuid 
                    LEFT JOIN sync_task st ON st.task_uuid = bt.task_uuid 
                WHERE
                    bt.task_uuid = ? GROUP BY stpl.target_uuid";
        $data = $this->dbSelect($sql, array($taskUuid));
        $info = array(
            'rows' => array(),
            'total' => count($data),
        );
        if (!empty($data)) {
            foreach($data as $d) {
                $wildcardInfo = json_decode($d['detail'],true);
                $fileCount = $this->getFileCount($taskUuid);
                switch ($d['task_status']) {
                    case xphp_get_config('task', 'TASKSTATUS')['RUNNING']:
                        $task_type_des = xphp_get_desc('Pf', 'TASKTYPEDES')[$d['task_type']];
                        $current_fs_count = $fileCount['current_count'];
                        $total_fs_count = $fileCount['total_count'];
                        $copy_speed = v1_calspeed($d['speed']);
                        $copy_percent = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $d['total_object_completed_size'], true, $d['task_type']);
                        $task_status_des = xphp_get_desc('Pf', 'TASKSTATUSDES')[$d['task_status']];
                        break;
                    default:
                        $task_type_des = xphp_get_config('app', 'NULLSPACE');
                        $current_fs_count = xphp_get_config('app', 'NULLSPACE');
                        $total_fs_count = xphp_get_config('app', 'NULLSPACE');
                        $copy_speed = xphp_get_config('app', 'NULLSPACE');
                        $copy_percent = xphp_get_config('app', 'NULLSPACE');
                        $task_status_des = xphp_get_config('app', 'NULLSPACE');
                        break;
                }
                $info['rows'][] = array(
                    'source_name' => $this->getSubAgentName($d['source_type'],$d['source_uuid']),
                    'target_name' => $this->getSubAgentName($d['target_type'],$d['target_uuid']),
                    'source_type' => $d['source_type'],
                    'target_type' => $d['target_type'],
                    'task_type' => $d['task_type'],
                    'task_type_des' => $task_type_des,
                    'current_fs_count' => $current_fs_count,
                    'total_fs_count' => $total_fs_count,
                    'file_list' => $this->getFileList($taskUuid),
                    'copy_speed' => $copy_speed,
                    'copy_percent' => $copy_percent,
                    'task_status' => $d['task_status'],
                    'task_status_des' => $task_status_des,
                    'filter_info' => json_decode($d['detail'],true),
                );
            }
        }
        return $info;
    }

    /**
     * 获取文件复制列表文件列表信息
     * @param string $uuid 任务uuid
     */
    private function getFileList($uuid) {
        $info = array();
        $sql = "SELECT
                    path_name,
                    path_type,
                    target_path_name,
                    target_path_code_type,
                    path_code_type 
                FROM
                    sync_task_path_list 
                WHERE
                    task_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        if(!empty($data)) {
            foreach($data as $d) {
                $info[] = array(
                    'target_path_name' => $d['target_path_name'],
                    'path_name' => $d['path_name'],
                    'path_type' => $d['path_type'],
                    'target_path_code_type' => $d['target_path_code_type'],
                    'path_code_type' => $d['path_code_type'],
                );
            }
        }
       return $info;
    }

    /**
     * 获取文件复制列表文件个数信息
     * @param string $uuid 任务uuid
     */
    private function getFileCount($uuid) {
        $info = array(
            'current_count' => 0,
            'total_count' => 0,
        );
        $sql = "SELECT sum(current_fs_count) AS current_count, sum(total_fs_count) AS total_count FROM sync_running_info WHERE task_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        if(!empty($data)) {
            foreach($data as $d) {
                $info = array(
                    'current_count' => $d['current_count'],
                    'total_count' => $d['total_count'],
                );
            }
        }
       
       return $info;
    }

    /**
     * 根据子模块类型和agentuuid获取源端/目标端名字
     * @param int $type 子模块类型
     * @param string $uuid agentuuid
     * @return string 返回结果
     */
    public function getSubAgentName($type, $uuid) {
        $des = xphp_get_config('app', 'NULLSPACE');
        switch (intval($type)) {
            case 1:   //fs
                $sql = "SELECT agent_name, hostname, ip FROM bd_agent WHERE agent_uuid = ?";
                break;
            case 2:   //nas
                $sql = "SELECT nas_nickname, share_path, ip FROM nas_storage_resource WHERE nas_uuid = ?";
                break;
            case 3:   //hadoop
                $sql = "SELECT hadoop_cluster_name FROM hadoop_cluster WHERE hadoop_cluster_uuid = ?";
                break;
            case 4:   //obs
                $sql = "SELECT obs_nickname, access_key_id, endpoint_override FROM obs_resource WHERE obs_uuid = ?";
                break;
        }
        if (empty($sql)) {
            return '--';
        }
        $result = $this->dbSelect($sql, array($uuid));
        if (!empty($result)) {
            $des = $this-> groupAgentName($type,$result[0]);
        }
        return $des;
    }

    /**
     * 组装源端/目标端名字
     * @param int $type 子模块类型
     * @param string $data 名称信息
     * @return string 返回结果
     */
    public function groupAgentName($type, $data) {
        $des = xphp_get_config('app', 'NULLSPACE');
        switch (intval($type)) {
            case 1:   //fs
                if(!empty($data['agent_name']) && $data['agent_name'] != $data['ip']) {
                    $des = $data['agent_name'] . '(' . $data['ip'] . ')';
                } else {
                    $des = $data['hostname'] . '(' . $data['ip'] . ')';
                }
                break;
            case 2:   //nas
                if(!empty($data['nas_nickname']) && $data['nas_nickname'] != $data['ip']) {
                    $des = $data['ip'] . '(' . $data['nas_nickname'] . ')';
                } else {
                    $des = $data['ip'] . '(' . $data['share_path'] . ')';
                }
                break;
            case 3:   //hadoop
                $des = strval($data['hadoop_cluster_name']);
                break;
            case 4:   //obs
                $des = $data['obs_nickname'] . '(' . $data['access_key_id'] . '@' . $data['endpoint_override'] . ')';
                break;
        }
        return $des;
    }

    /**
     * 获取历史任务列表信息（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getFileCopyHistoryList($params = [])
    {
        $taskUuid = $params['job_uuid'];
        $data = \app\v1\job\v0\logic\JobInfo::instance()->getJobHistory($params);
        $info = array(
            'rows' => array(),
            'count' => 0,
        );
        if (!empty($data)) {
            $info['count'] = count($data);
            $info['rows'] = $data;
        }
        return $info;
    }

    /**
     * 获取比对任务列表信息（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getCompareList($params = [])
    {
        $info = array(
            'rows' => array(),
            'total' => 1,
        );
        $taskUuid = $params['job_uuid'];
        $sql = "SELECT
                    SUM( dif_object_count ) AS dif_object_count,
                    SUM( same_object_count ) AS same_object_count,
                    SUM( abnormal_object_count ) AS abnormal_object_count 
                FROM
                    sync_fs_compare_result 
                WHERE
                    task_uuid = ? 
                    AND available_flag = ?";
        $data = $this->dbSelect($sql, array($taskUuid,xphp_get_config('app', 'FLAG')['SET']));
        foreach ($data as $each) {
            if ($each['dif_object_count'] != null && $each['same_object_count'] != null && $each['abnormal_object_count'] != null) {
                $info['rows'][] = array(
                    'dif_object_count' => $each['dif_object_count'], //差异文件数
                    'same_object_count' => $each['same_object_count'], //相同文件数
                    'abnormal_object_count' => $each['abnormal_object_count'], //异常文件数
                );
            } else {
                $info['total'] = 0;
            }
        }
        return $info;
    }

    /**
     * 获取比对任务列表信息（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getCompareDetail($params = [])
    {
        $info = array();
        $taskUuid = $params['job_uuid'];
        $sql = "SELECT
                start_time,
                end_time,
                object_uuid,
                same_object_count,
                dif_object_count,
                abnormal_object_count,
                detail
            FROM
                sync_fs_compare_result
            WHERE
                task_uuid = ? 
                AND available_flag = ?";
        $data = $this->dbSelect($sql, array($taskUuid,xphp_get_config('app', 'FLAG')['SET']));
        if (!empty($data)) {
            foreach ($data as $each) {
                $detail = json_decode($each['detail'],true);
                $srcPath = empty($detail['source_prefix']) ? $detail['source_select_dir'] : '/' . str_replace($detail['source_prefix'], '', $detail['source_select_dir']);
                $desPath = empty($detail['target_prefix']) ? $detail['slave_select_dir'] : '/' . str_replace($detail['target_prefix'], '', $detail['slave_select_dir']);
                $info[] = array(
                    'path' => $srcPath . '->' . $desPath,
                    'start_time' => $each['start_time'],//对比开始时间
                    'end_time' => $each['end_time'], //对比结束时间
                    'dif_object_count' => $each['dif_object_count'], //差异对象数
                    'abnormal_object_count' => $each['abnormal_object_count'], //异常文件数
                    'same_object_count' => $each['same_object_count'],//相同文件数
                    'node_uuid' => $detail['node_uuid'],
                );
            }
        }
        return $info;
    }

    /**
     * 获取比对结果（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getCompareResult($params = []) {
        $result = $this->service()->getCompareResult($params);
        if($result['result']) {
            $data =  $result['msg'];
            $info["error_code"] = $data["error_code"];
            $info["error_msg"] = $data["error_msg"];
            $info["finish_flag"] = $data["finish_flag"];
            $info["current_item_load_number"] = $data["current_item_load_number"];
            $info["total_diff"] = 0;
            $pId = $params['pId'] ?? 0;
            foreach($data['path_list'] as $each) {
                $name = $each['source_path']['file_name'];
                if ($params['info']['first_load_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                    //第一次请求才算这个
                    $info["total_diff"] = intval($each['source_path']["abnormal_file_count"]) + intval($each['source_path']["diff_file_count"]) + $info["total_diff"];
                    //第一次请求才拼上目标端名字
                    $name .= '（' . xphp_get_lang('UI_FILE_COPY_DES_PATH') . '：' . $each['target_path']['file_name'] . '）';
                }
                $info['fileNode'][] = array(
                    "name" => $name,
                    "id" => $each['source_path']["file_path"],
                    "pId" => $pId,
                    "job_uuid" => $params['info']['task_uuid'],
                    "path_uuid" => $each['source_path']["path_uuid"],
                    "isParent" => $this->getBoolType($each['source_path']["file_type"]),
                    "abnormal_file_count" => $each['source_path']["abnormal_file_count"],
                    "aim_id" => $each['source_path']["aim_id"],
                    "aim_site" => $each['source_path']["aim_site"],
                    "code_type" => $each['source_path']["code_type"],
                    "diff_file_count" => $each['source_path']["diff_file_count"],
                    "file_name" => $each['source_path']["file_name"],
                    "file_path" => $each['source_path']["file_path"],
                    "file_size" => $each['source_path']["file_size"],
                    "file_type" => $each['source_path']["file_type"],
                    "level_flag" => $each['source_path']["level_flag"],
                    "m_time" => $each['source_path']["m_time"],
                    "result_string" => $each['source_path']["result_string"],
                    "same_file_count" => $each['source_path']["same_file_count"],
                );
            }
            if (intval($data['finish_flag']) == xphp_get_config('app', 'FLAG')['UNSET']) {
                //如果还没有显示完全,添加显示更多项
                $more = array(
                    'id' => $each['source_path']["file_path"] . '/more',
                    'pid' => $pId,
                    'name' => xphp_get_lang('WEB_FILE_MORE'),
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    'nocheck' => true,
                    'more' => true,
                    'next_index' => $data["next_item_load_offset"],       //从哪个位置开始加载
                    "job_uuid" => $params['info']['task_uuid'],
                    "path_uuid" => $each['source_path']["path_uuid"],
                );
                array_push($info['fileNode'], $more);
            }
        } else { 
            $info = $result;
        }
        return $info;
    }

    /**
     * 获取比对结果（任务详情使用）
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function copyCompareResult($params = []) {
        return $this->service()->copyCompareResult($params);
    }

     /**
     * 根据文件类型判断是否是父节点
     * @param int $type 文件类型
     * @return boolean
     */
    public function getBoolType($type)
    {
        $bool = '';
        switch (intval($type)) {
            case 0:
            case 1:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
                $bool = false;
                break;
            case 2:
            case 3:
                $bool = true;
                break;
        }
        return $bool;
    }

     /**
     * 获取告警信息
     * @param array $params 告警id
     * @return string
     */
    public function getFileCopyAlarm($params)
    {
        $alarmId = $params['alarm_id'];
        $sql = "select bht.details,bht.task_type from bd_history_task bht, bd_task_alarm bta 
                where bht.history_uuid = bta.history_uuid and bta.task_alarm_id  = ? ";
        $sqlParams = array($alarmId);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        if (!empty($data)) {
            $detail = json_decode($data[0]['details'], true);
            $info = array(
                'source_name' => $this->getSubAgentName($detail['source_type'],$detail['source_uuid']),
                'target_name' => $this->getSubAgentName($detail['target_type'],$detail['target_uuid']),
                'copy_list' => $detail['copy_list'],
            );
        }
        return $info;
    }



    /**
     * 下载跳过数据
     * @param array $params 参数
     * @return string
     */
    public function downLoadPassData(array $params)
    {
        $nodeUuid =  $params['node_uuid'];
        $path = $params['path'];
        $filename = 'passfilelist';
        $filesize = $params['pass_file_size'];
        Header('Content-type: application/octet-stream');
        Header('Accept-Ranges: bytes');
        Header('Content-Disposition: attachment; filename=' . $filename . '.txt');
        $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        //如果大于分块大小,分块下载
        for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
            if ($filesize - $i <= $blockSize) {
                $readLen = $filesize - $i;
            } else {
                $readLen = $blockSize;
            }
            $msg = array(
                "path" => $path,
                "read_offset" => $i,
                "read_size" => $readLen,
            );
            echo $this->service()->downLoadPassData($nodeUuid, $msg);
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }
    }
}
