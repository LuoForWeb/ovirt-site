<?php

namespace app\v1\filecopy\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\file\v0\logic\FileBackUp;
use app\v1\hadoop\v0\logic\HadoopBackUp;
use app\v1\nas\v0\logic\NasBackUp;
use app\v1\opcode\FcOpcode;
use app\v1\resources\v0\logic\Index;
use app\v1\resources\v0\logic\Node;
use app\v1\s3\v0\logic\ObsBackup;
use app\v1\tenant\v0\logic\Tenant;
use xphp\db\Op;

/**
 * note          文件同步 -- 备份管理 logic
 * @author       wuxian@vinchin.com
 * @date         2024/7/17 16:15
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class FileCopy extends Backup
{
    /**
     * 获取客户端、nas设备、hadoop集群、对象存储信息
     * @param unkown $params 参数
     * @return array 返回结果
     */
    public function getResourceInfo($params)
    {
        //获取客户端
        $agent = FileBackUp::instance()->getAgentGroupBackupTree();
        //获取nas设备
        $nas = NasBackUp::instance()->getNasBackupTree();
        //获取hadoop集群
        // $hadoop = HadoopBackUp::instance()->getBackupZtree([]);
        $hadoop = [];
        //获取对象存储
        // $obs = ObsBackup::instance()->getObsBackupTree();
        $obs = [];
        return array_merge($agent, $nas, $hadoop, $obs);
    }

    /**
     * 获取修改同步任务选中的源和目标信息
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function editResourceInfo($params = [])
    {
        $info = array();
        $taskUuid = $params['job_uuid'];
        //组装修改展示的数据,一个目标端对应多个源端
        $sql = "SELECT target_path_name, GROUP_CONCAT(path_name ORDER BY path_name SEPARATOR ', ') AS path_names, 
       source_uuid, destination_uuid FROM sync_task_path_list WHERE task_uuid = ? GROUP BY target_path_name;";
        $data = $this->dbSelect($sql, array($taskUuid));
        if (!empty($data)) {
            foreach ($data as $each) {
                $info['show_table'][] = array(
                    'path_names' => $each['path_names'],//所有源端
                    'target_path_name' => $each['target_path_name'],//目标端
                );
            }
        }
        //查出所有数据的相关字段
        $sqlAll = "select path_uuid,path_name,path_type,target_path_name,source_uuid,destination_uuid,code_type from 
        sync_task_path_list where task_uuid = ?";
        $allData = $this->dbSelect($sqlAll, array($taskUuid));
        if (!empty($allData)) {
            foreach ($allData as $each) {
               $info[] = array(
                   'path_uuid' => $each['path_uuid'],
                   'path_name' => $each['path_name'],//需要同步的文件路径
                   'path_type' => $each['path_type'],//文件的类型
                   'target_path_name' => $each['target_path_name'],//同步目的地的路径
                   'code_type' => $each['code_type'], //路径的编码类型
               );
            }
            $info['source_uuid'] = $data[0]['source_uuid'];//源端的uuid
            $info['destination_uuid'] = $data[0]['destination_uuid'];//目的端的uuid
        }
        return $info;
    }

    /**
     * 创建文件同步任务
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function createFileCopyJob($params = [])
    {
        if (empty($params['job_uuid'])) {//创建
            $opName = 'SYNC_FS_OP_CODE_CREATE_TASK';
             //判断首次复制启动时间是否大于系统时间(只创建判断，修改不判断，避免每次必须修改首次启动时间才能修改任务)
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            if ($systemTime >= strtotime($params['copy_info']['first_copy_time'])) {
                return $this->muOpResult(false, xphp_get_lang('UI_FILE_COPY_FIRST_START_TIME'), xphp_get_lang('UI_FILE_COPY_TIME_TIPS'), 'warning');
            }
        } else {//修改
            $opName = 'SYNC_FS_OP_CODE_MODIFY_TASK';
            $pfMsg['task_uuid'] = $params['job_uuid'];
            //源端或者目标端是nas的时候，判断nodeuuid是否是nas挂载过的
            $this->checkNasMounted($params);
        }
        $fcOpcode = new FcOpcode();
        $operate = $fcOpcode->getOpcodeDes($opName);
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        $pfMsg['task_name'] = htmlspecialchars_decode($params['job_name']);
        $pfMsg['module_type'] = xphp_get_config('module', 'MODULE_TYPE')['FILE_COPY'];
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        //租户内检测授权个数
        // if (xphp_get_user_info()['tenantuuid']) {
        //     (new Tenant())->checkTenantAuth($moduletype, $params['src_info']['path_list'], '');
        // }
        //源端uuid
        $pfMsg['source_uuid'] = $params['copy_info']['source_uuid'];
        //源端子模块类型
        $pfMsg['source_type'] = $params['copy_info']['source_type'];
        //目标端uuid
        $pfMsg['target_uuid'] = $params['copy_info']['target_uuid'];
        //目标端子模块类型
        $pfMsg['target_type'] = $params['copy_info']['target_type'];
        //勾选的对象列表,包含源端文件列表和目标端路径
        $pfMsg['path_list'] = $params['copy_info']['path_list'];
        //复制间隔
        $pfMsg['copy_interval'] = $params['copy_info']['copy_interval'];
        //首次复制启动时间
        $pfMsg['first_copy_time'] = $params['copy_info']['first_copy_time'];
        $transport_strategy = array(
            //压缩传输
            'compress_flag' => v1_parse_bool_to_flag($params['transfer_info']['compress_transport_flag']),
            //压缩传输算法
            'compress_method' => $params['transfer_info']['compress_method'],
            //加密传输
            'encrypt_flag' => v1_parse_bool_to_flag($params['transfer_info']['transport_encrypt_flag']),
            //加密传输算法
            'encrypt_method' => $params['transfer_info']['transport_encrypt_method'],
            //暂时没用
            'max_speed' => 0,
            //暂时没用
            'speed_limit_flag' => false
        );
        //传输策略
        $pfMsg['transport_strategy'] = $transport_strategy;
        //快照
        $pfMsg['snap_shot_flag'] = v1_parse_bool_to_flag($params['high_info']['snapshot']); 
        //文件权限复制
        $pfMsg['permission_operate_flag'] = v1_parse_bool_to_flag($params['high_info']['copy_file_permission']); 
        //传输线程
        $pfMsg['backup_thread_num'] = $params['high_info']['backup_thread_num'];
        //扫描线程
        $pfMsg['scan_thread_num'] = $params['high_info']['scan_thread_num'];
        //扫描文件速度
        $pfMsg['scan_file_num'] = $params['high_info']['scan_file_num'];
        //跳过文件告警智能判断
        $pfMsg['skip_file_alarm_flag'] = v1_parse_bool_to_flag($params['high_info']['skip_file_alarm_flag']);
        //跳过文件告警个数
        $pfMsg['skip_file_alarm_min_num'] = $params['high_info']['skip_file_alarm_min_num'];
        //跳过文件告警比例
        $pfMsg['skip_file_alarm_min_ratio'] = $params['high_info']['skip_file_alarm_min_ratio'];
        //同名文件处理
        $pfMsg['same_file_strategy'] = $params['high_info']['same_file_strategy'];
        //校验方式
        $pfMsg['check_mode'] = $params['high_info']['check_mode'];
        //校验算法
        $pfMsg['sync_rule'] = $params['high_info']['sync_rule'];
        //覆盖规则
        $pfMsg['file_verification_algorithm'] = $params['high_info']['file_verification_algorithm'];
        //base64解码通配符
        foreach($params['high_info']['wildcard_list']['wildcard'] as $key => $wildcard) {
            $params['high_info']['wildcard_list']['wildcard'][$key]['wildcard'] = xphp_decrypt_js($wildcard['wildcard']);
        }
        $pfMsg['wildcard_list'] = $params['high_info']['wildcard_list'];
        $pfMsg['time_range_list'] = $params['high_info']['time_range_list'];
        //限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $this->groupRetryStrategy($params['retry_strategy']);
        //得到全局策略uuid (没有传空)
        $pfMsg['strategy_group_uuid'] = $params['strategygroupuuid'] ?? '';
        $pfMsg['node_uuid'] = $params['node_info']['node_uuid'];
        $pfMsg['node_pool_uuid'] = $params['node_info']['node_pool_uuid'] ?? '';
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_info']['ignore_resource_limiting_flag']);
        //复制勾选目录
        $pfMsg['sync_self_flag'] = v1_parse_bool_to_flag($params['high_info']['sync_self_flag']);
        if ($pfMsg['node_uuid']) {
            $nodeuuid = $pfMsg['node_uuid'];
        } else {
            $nodeuuid = (new Node)->getMasterNodeUuid();
        }
        $mbResult = $this->service()->createFileCopyJob($nodeuuid, $pfMsg, $opName);
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg']);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }

    /**
     * 源端或者目标端是nas的时候，判断nodeuuid是否是nas挂载过的
     * @param unkown $params 参数
     * @return string 返回结果
     */
    private function checkNasMounted($params)
    {
        $param_node_arr = [];//任务配置的node_uuid
        if ($params["node_info"]['node_uuid'] != "" && $params["node_info"]['node_pool_uuid'] == "") {
            $param_node_arr = [$params["node_info"]['node_uuid']];
        } else if ($params["node_info"]['node_pool_uuid'] != "") {
            //选择的计算资源池
            $sql = "SELECT node_uuid FROM bd_node_pool_list WHERE node_pool_uuid = ?";
            $data = $this->dbSelect($sql,[$params["node_info"]["node_pool_uuid"]]);
            $param_node_arr = array_column($data, 'node_uuid');
        }
        if ($params["copy_info"]["source_type"] == 2) {
            $sql = "SELECT node_uuid FROM nas_mount_list WHERE nas_uuid = ?";
            $data = $this->dbSelect($sql,[$params["copy_info"]["source_uuid"]]);
            $node_arr = array_column($data, 'node_uuid');
            //判断nas挂载的节点和任务配置的节点有没有交集，没有则返回错误
            if(empty(array_intersect($param_node_arr, $node_arr))){
                exit($this->muOpResult(false, xphp_get_lang('UI_BACKUP_EDIT_TASK'), xphp_get_lang('UI_FILE_COPY_NODE_NOT_AVAILABLE') , "warning"));
            }
        }
        if ($params["copy_info"]["target_type"] == 2) {
            $sql = "SELECT node_uuid FROM nas_mount_list WHERE nas_uuid = ?";
            $data = $this->dbSelect($sql,[$params["copy_info"]["target_uuid"]]);
            $node_arr = array_column($data, 'node_uuid');
            //判断nas挂载的节点和任务配置的节点有没有交集，没有则返回错误
            if(empty(array_intersect($param_node_arr, $node_arr))){
                exit($this->muOpResult(false, xphp_get_lang('UI_BACKUP_EDIT_TASK'), xphp_get_lang('UI_FILE_COPY_NODE_NOT_AVAILABLE') , "warning"));
            }
        }
    }
       

    /**
     * 组合同步任务时间策略
     * @param unkown $params 参数
     * @return string 返回结果
     */
    private function groupSyncTimeList($params)
    {
        $timeInfo = array();
        $timeInfo['task_pending_open_flag'] = v1_parse_bool_to_flag($params['task_pending_open_flag']);//任务暂停时间是否配置
        foreach ($params['task_pending_time_list'] as $each) {
            $timeInfo['task_pending_time_list'][] = array( //任务暂停时间配置的列表
                'time_start' => $each['time_start'],
                'time_end' => $each['time_end'],
            );
        }
        return $timeInfo;
    }

    /**
     * 绝对路径访问
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function accessByAbsolutePath($params = [])
    {
        $info = array();
        $result = $this->service()->accessByAbsolutePath($params);
        if ($result['item_list']) {
            foreach ($result['item_list'] as $each) {
                $info[] = array(
                    "file_exist_flag" => $each['file_exist_flag'],
                    "item_name" => $each['item_name'], //文件名
                    "item_path" => $each['item_path'],  //文件路径
                    "item_type" => $each['item_type'], //文件类型
                    "code_type" => $each['code_type'], //编码类型
                    "modify_time" => $each['modify_time'], //修改时间
                );
            }
        }
        return $info;
    }
}