<?php

namespace app\v1\k8s\v0\logic;
use app\v1\common\logic\Backup;

use app\v1\common\logic\Base;

use app\v1\common\logic\JobInfo;

use app\v1\common\logic\Unification;
use Mpdf\Tag\Em;

/**
 * note          容器 备份管理 logic
 * @author       liushuai@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sBackUp extends Backup
{
    /**
     * 创建备份任务 demo
     */
    public function createBackupJob($params){
        if(empty($params['task_uuid'])){
            $operate = xphp_get_lang('WEB_KUBE_CREATE_BACKUP_JOB');
        }else{
            $operate = xphp_get_lang('WEB_KUBE_EDIT_BACKUP_JOB');
        }
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        //
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CREATE_BACKUP_JOB_SUCCESS'),
            'data' => array(),
        );
        if(!empty($params['task_uuid'])){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_MODIFY_BACKUP_JOB_SUCCESS');
        };
        
        $pfMsg = array();
        //获取任务名称
        $taskname = htmlspecialchars_decode($params['task_name']);
        //获取模块类型
        $moduletype = xphp_get_config('module','MODULE_TYPE')['KUBERNETES'];
        //组合通用策略---------------------------
        //组合时间策略
        $timestrategylist = $this->newGroupBackupTimeList($params['strategyInfo']['time']);
        //组合存储策略
        $params['strategyInfo']['store']['storeInfo']['blocksize'] = intval($params['blocksize']) *1024;
        $storagestrategy = $this->groupStorageStrategy($params['strategyInfo']['store']['storeInfo']);
        //组合保留策略
        $reserverstrategy = $this->groupReserverStrategy($params['strategyInfo']['reserve']['reserveInfo']);
        //组合传输策略-----------------------------
        $transportstrategy = $this->groupTransportStrategy($params['transfer_strategy']);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['node_info']);
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $taskname,
            $moduletype,
            $timestrategylist,
            $reserverstrategy,
            $transportstrategy,
            $storagestrategy,
            $nodeInfo,
            $params['strategyInfo']['time']['type']
        );
        if(!empty($params['task_uuid'])){
            //修改含有任务uuid
            $pfMsg['task_uuid'] = $params['task_uuid'];
        }
        //获取任务类型
        $pfMsg['task_type'] = xphp_get_config('task','TASKTYPE')['KUBE_BACKUP'];
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['strategyInfo']['speedlimit']);
        //组合安全策略-----------------------------
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_config_strategy'],$params['node_info']['storage_uuid']);
        //组合重试策略
        $pfMsg['retry_strategy'] = $params['retry_strategy'];
        //过载保护
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['ignore_resource_limiting_flag']);
        
        //高级配置--------------
        $pfMsg['advance_setting'] = array();
        $pfMsg['advance_setting'] = $params['advanced_strategy'];
        $pfMsg['thread_num'] = $params['thread_num'];
        //其他非公共变量-----------------------------
        $pfMsg['backup_src_info'] = $params['backup_src_info'];

        $nodeuuid = $params['node_info']['node_uuid'];
        $mbResult = $this->service()->createBackupJob($nodeuuid,$pfMsg);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            if(empty($params['task_uuid'])){
                $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CREATE_BACKUP_JOB_FAILURE');
            }else{
                $resultInfo['message'] = xphp_get_lang('WEB_KUBE_MODIFY_BACKUP_JOB_FAILURE');
            }
           
            return $resultInfo;
        }
        return $resultInfo;
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

    /**
     * 得到主机任务名(备份)
     * @param unknown $params
     */
    public function getK8sBackupTaskName(){
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_BACKUP_GET_TASK_NAME'),
            'data' => array(),
        );
        $taskName = 'Kubernetes';
        $taskName .= xphp_get_lang('WEB_KUBE_BACKUP_BACKUP_TASK');
        $getName = new JobInfo();
        $resultInfo['data'] = $getName->getValidTaskName($taskName);
        return $resultInfo;
    }

    /**
     * 得到主机任务名(恢复)
     * @param unknown $params
     */
    public function getK8sRecoverTaskName(){
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_BACKUP_GET_TASK_NAME'),
            'data' => array(),
        );
        $taskName = 'Kubernetes';
        $taskName .= xphp_get_lang('WEB_KUBE_BACKUP_RECOVERY_TASK');
        $getName = new JobInfo();
        $resultInfo['data'] = $getName->getValidTaskName($taskName);
        return $resultInfo;
    }
    

    
    
    
    
    
    
    

}