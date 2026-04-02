<?php

namespace app\v1\k8s\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\common\logic\Recover;
use xphp\BLLHandler as XphpBLLHandler;
use app\v1\backupData\v0\logic\DataManage;

/**
 * note          容器 之恢复管理 logic
 * @author       liushuai@vinchin.com
 * @date         2023/6/20 18:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sRecover extends Recover
{
    /**
     * 创建备份任务 demo
     */
    public function createRecoveryJob($params)
    {
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CREATE_RECOVERY_JOB_SUCCESS'),
            'data' => array(),
        );
        //获取参数
        $pfMsg = array();

        $bllhandler = new XphpBLLHandler;
        $backupCommonHanlder = new Backup;
        //组合时间策略
        $timestrategylist = $this->groupRecoverTimeList($params['type_info']);
        //组合传输策略
        $task_name = htmlspecialchars_decode($params['task_name']);
        $module_type = xphp_get_config('module','MODULE_TYPE')['KUBERNETES'];
        $recoverytimetype = 0;
        $recovery_position = 2;
        $transportstrategy =  $backupCommonHanlder->groupTransportStrategy($params['transfer_strategy']);
        $pfMsg = $bllhandler->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recoverytimetype,
            $timestrategylist,
            $transportstrategy
        );
         //获取任务类型
         $pfMsg['task_type'] = xphp_get_config('task','TASKTYPE')['KUBE_BACKUP'];
         //获取策略组uuid
         $pfMsg['strategy_group_uuid'] = "";
         //??
         $pfMsg['backup_server_ip'] = "";
         //??
         $pfMsg['transport_ip_segment'] = "";
        //组合限速策略
        $pfMsg['speed_limit_strategy'] = $backupCommonHanlder->groupTaskSpeedGlobalList($params['strategyInfo']['speedlimit']);
        //获取线程数量
        $pfMsg['thread_num'] = $params['thread_num'];
        //组合安全策略-----------------------------
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_config_strategy']);
        //组合重试策略
        $pfMsg['retry_strategy'] = $params['retry_strategy'];
        //过载保护
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);
    
        //获取恢复信息
        $pfMsg['recovery_info'] = $params['recovery_info'];
        //获取高级信息
        $pfMsg['advance_setting'] = $params['advanced_strategy'];

        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
         // 获取时间点所在存储可用的节点uuid
         $allStorageData = DataManage::instance()->getStorageInfoByTimepoints([$params['recovery_info']['src_timepoint_uuid']]);
         foreach ($allStorageData as $item) {
             if ($item['storage_online_flag']) {
                 $nodeuuid = $item['node_uuid'];
                 break;
             }
         }
        $mbResult = $this->service()->createRecoveryJob($nodeuuid, $pfMsg);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CREATE_RECOVERY_JOB_FAILURE');
            return $resultInfo;
        }
        //如果是立即恢复,需要创建完成后启动任务
        if($params['type_info']['type'] == xphp_get_config('task', 'RECOVERY_TIME_TYPE')['IMMEDIATELY']) {
            $this->startRecoverJob($params['task_name']);
        }
        return $resultInfo;

    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName 任务名
     * @return boolean
     */
    private function startRecoverJob(string $taskName)
    {
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type
                from bd_task bt where bt.task_name = ? and bt.module_type = ? and bt.task_type = ?";
        $data = $this->dbSelect($sql, array($taskName, xphp_get_config('module', 'MODULE_TYPE')['KUBERNETES'], xphp_get_config('task', 'TASKTYPE')['KUBE_RECOVERY']));
        if (!$data) {
            return false;
        }
        //调用系统统一启动任务接口.不重新写
        $jobInfoKube = new K8sJobController;
        $jobInfoKube->startJob($data[0]['task_uuid'], xphp_get_config('task', 'BACKUP_MODE')['FULL']);
    }

    /**
     * 得到本地节点UUID
     */
    public function getLocalNodeUUID()
    {
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }

}