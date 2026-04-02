<?php

namespace app\v1\cluster\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\ClusterOpcode;
use app\v1\resources\v0\logic\Node;

class Cluster extends Base
{
    private $clusterOpcode;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->clusterOpcode = new ClusterOpcode();
    }

    /**
     * ping集群服务IP
     * @param string $configServiceIp 集群服务IP
     * @return array
     */
    private function pingConfigServiceIp(string $configServiceIp): array
    {
        if (v1_check_ip_exists($configServiceIp)) {  // IP存在了
            return $this->sendResult(xphp_get_lang('UI_CLUSTER_VIRTUAL_IP_EXISTS'), false, 0);
        }
        return $this->sendResult('');
    }

    /**
     * 配置集群
     * @param array $params 节点配置信息
     * @return array
     */
    public function setClusterConfig(array $params): array
    {
        $clusterName = htmlspecialchars_decode($params['cluster_name']);
        $configServiceIp = $params['config_service_ip'];
        $pingRet = $this->pingConfigServiceIp($configServiceIp);
        if (!$pingRet['success']) {
            return $pingRet;
        }
        $advertInt = $params['advert_int'];
        $nodeConfig = $params['node_config'];
        $priorityFlag = $params['priority_flag'];
        $opcodeName = 'CLUSTER_OP_SYSTEM_CONFIG';
        $allClusterConfigMode = xphp_get_config('cluster', 'CLUSTER_CONFIG_MODE', 'cluster');
        if ($allClusterConfigMode['INIT'] == $params['config_mode']) {  // 初始化配置
            $serviceResult = $this->service()->setClusterConfigService(
                $clusterName,
                $configServiceIp,
                $nodeConfig,
                $priorityFlag,
                $advertInt
            );
        } else {
            $opcodeName = 'BD_CLUSTER_SYSTEM_OP_CODE_ADD_CLUSTER_NODE';
            $serviceResult = $this->service()->addClusterNodeService($nodeConfig);
        }
        $operate = $this->clusterOpcode->getOpcodeDes($opcodeName);
        if (!$serviceResult['result']) {
            $this->muOpResult(false, $operate, $serviceResult['msg'], 0, $serviceResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_CLUSTER_SET_CONFIG_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_CLUSTER_SET_CONFIG_SUCCESS'));
    }

    /**
     * 获取集群的配置信息
     * @return array
     */
    public function getClusterConfig(): array
    {
        $sql = "SELECT bc.cluster_uuid, bc.virtual_ip, bc.cluster_status, bc.cluster_priority_strategy,
                    bc.cluster_name, bc.operation_progress, bc.advert_int,
                    bcn.node_uuid, bcn.node_priority, bcn.node_role,
                    bcnn.ip, bcnn.nic_name,
                    bn.host_name, bn.ip AS node_ip, bn.detail
                FROM bd_cluster bc
                    LEFT JOIN bd_cluster_node bcn ON bc.cluster_uuid = bcn.cluster_uuid
                    LEFT JOIN bd_cluster_node_network bcnn ON bcn.cluster_uuid = bcnn.cluster_uuid
                        AND bcn.node_uuid = bcnn.node_uuid
                    LEFT JOIN bd_node bn ON bn.node_uuid = bcnn.node_uuid
                ORDER BY bcn.node_role ";
        $clusterData = $this->dbSelect($sql);
        if (!is_array($clusterData)) {
            $clusterData = [];
        }

        $nodeHandler = new Node();
        $clusterConfigFlag = $this->getClusterIsConfig();
        $priorityFlag = v1_parse_flag_to_bool($clusterData[0]['cluster_priority_strategy']);
        $retData = [
            'cluster_name' => $clusterData[0]['cluster_name'],
            'config_flag' => $clusterConfigFlag,
            'config_service_ip' => !$clusterConfigFlag ? '' : $clusterData[0]['virtual_ip'],
            'advert_int' => !$clusterConfigFlag ? 60 : intval($clusterData[0]['advert_int']),
            'operation_progress' => $clusterData[0]['operation_progress'],
            'node_config' => [],
            'priority_flag' => !$clusterConfigFlag ? 0 : $priorityFlag,
            'cluster_status' => !$clusterConfigFlag ? 0 : $clusterData[0]['cluster_status'],
        ];

        $sortKeys = array_column($clusterData, 'node_priority');
        array_multisort($sortKeys, SORT_DESC, $clusterData);
        foreach ($clusterData as $clusterInfo) {
            $networkList = $nodeHandler->getNodeNetworkList(
                $clusterInfo['node_uuid'],
                ['offset' => 0, 'limit' => 10000]
            );
            if ($networkList['success']) {
                $networkList = $networkList['data']['rows'];
            } else {
                $networkList = [];
            }

            $nodeStatus = $nodeHandler->getNodeStatus($clusterInfo['node_uuid']);

            // 节点版本
            $nodeVersion = xphp_get_config('app', 'NULLSPACE');
            $detail = json_decode($clusterInfo['detail'], true);
            if ($detail) {
                $nodeVersion = $detail['version']['major_version']
                    . '.' . $detail['version']['minor_version']
                    . '.' . $detail['version']['build_number'];
            }

            $retData['node_config'][] = [
                'node_hostname' => $clusterInfo['host_name'],
                'node_uuid' => $clusterInfo['node_uuid'],
                'node_role' => (int) $clusterInfo['node_role'],
                'node_ip' => $clusterInfo['node_ip'],
                'network_ip' => $clusterInfo['ip'],
                'network_name' => $clusterInfo['nic_name'],
                'priority' => (int) $clusterInfo['node_priority'],
                'network_list' => $networkList,
                'online_flag' => $nodeStatus['online_flag'],
                'node_status' => $nodeStatus['status'],
                'node_offline_module' => $nodeStatus['offline_module'],
                'node_offline_module_des' => $nodeHandler->getOffLineModuleDes($nodeStatus['offline_module']),
                'node_version' => $nodeVersion,
            ];
        }
        // $retData['node_config'][] = [
        //     'node_hostname' => 'localhost.localdomain13123123123123213123131312',
        //     'node_uuid' => 'node_uuid',
        //     'node_role' => 2,
        //     'network_ip' => '192.168.24.36',
        //     'network_name' => 'nic_name13123123123131313123',
        //     'priority' => 2,
        //     'network_list' => [],
        //     'online_flag' => false,
        //     'node_status' => 1,
        //     'node_offline_module' => [1],
        //     'node_offline_module_des' => 'asdasd',
        //     'node_version' => 'xxxxxx',
        // ];
        return $this->sendResult('', true, 200, $retData);
    }

    /**
     * 获取正在运行的任务列表
     * @return array
     */
    private function getRunningJobList(): array
    {
        $runningJobList = [];
        $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');
        $runningJobStatusList = [
            $allTaskStatus['RUNNING'],   // 运行中
            $allTaskStatus['STOPPING'],  // 停止中
            $allTaskStatus['PREPARING'], // 准备中
            $allTaskStatus['PAUSING'],   // 暂停中
            $allTaskStatus['STARTING'],  // 启动中
            $allTaskStatus['PENDING'],   // 挂起
        ];
        $runningJobs = "'" . implode("','", $runningJobStatusList) . "'";
        $sql = "SELECT task_name, task_uuid, task_status FROM bd_task WHERE task_status IN ($runningJobs) ";
        $taskData = $this->dbSelect($sql);
        if (!is_array($taskData)) {
            $taskData = [];
        }
        foreach ($taskData as $taskInfo) {
            $runningJobList[] = [
                'job_name' => $taskInfo['task_name'],
                'job_uuid' => $taskInfo['task_uuid'],
                'job_status' => intval($taskInfo['task_status']),
            ];
        }
        return $runningJobList;
    }

    /**
     * 检查集群操作的任务状态
     * @description 任务处于运行中、停止中、准备中、暂停中、启动中、挂起状态时，不允许操作(启动、停止、切换)集群
     * @return array
     */
    private function checkClusterOperateTaskStatus(): array
    {
        $runningJobList = $this->getRunningJobList();
        if (count($runningJobList)) {
            $jobNameList = array_column($runningJobList, 'job_name');
            $message = sprintf(xphp_get_lang('WEB_CLUSTER_TASK_IN_RUNNING_REJECT_OPERATE'), implode('、', $jobNameList));
            return $this->sendResult($message, false, 0, $runningJobList);
        }

        return $this->sendResult('', true, 200);
    }

    /**
     * 检查集群启动状态
     * @description 集群节点至少拥有两个节点
     * @return array
     */
    private function checkClusterStartStatus(): array
    {
        $sql = "SELECT node_uuid, cluster_uuid FROM bd_cluster_node ";
        $nodeData = $this->dbSelect($sql);
        if (!is_array($nodeData)) {
            $nodeData = [];
        }
        if (count($nodeData) < 2) {
            return $this->sendResult(xphp_get_lang('WEB_CLUSTER_START_AT_LEAST_TWO_NODE'), false, 200);
        }
        return $this->sendResult('');
    }

    /**
     * 启动集群
     * @return array
     */
    public function startCluster(): array
    {
        $checkRet = $this->checkClusterOperateTaskStatus();
        if (!$checkRet['success']) {
            return $checkRet;
        }
        $checkRet = $this->checkClusterStartStatus();
        if (!$checkRet['success']) {
            return $checkRet;
        }
        $opcodeName = 'CLUSTER_OP_SYSTEM_START';
        $operate = $this->clusterOpcode->getOpcodeDes($opcodeName);
        $serviceResult = $this->service()->startClusterService();
        if (!$serviceResult['result']) {
            $this->muOpResult(false, $operate, $serviceResult['msg'], 0, $serviceResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_CLUSTER_START_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_CLUSTER_START_SUCCESS'));
    }

    /**
     * 停止集群
     * @return array
     */
    public function stopCluster(): array
    {
        $checkRet = $this->checkClusterOperateTaskStatus();
        if (!$checkRet['success']) {
            return $checkRet;
        }
        $opcodeName = 'CLUSTER_OP_SYSTEM_STOP';
        $operate = $this->clusterOpcode->getOpcodeDes($opcodeName);
        $serviceResult = $this->service()->stopClusterService();
        if (!$serviceResult['result']) {
            $this->muOpResult(false, $operate, $serviceResult['msg'], 0, $serviceResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_CLUSTER_STOP_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_CLUSTER_STOP_SUCCESS'));
    }

    /**
     * 停止集群
     * @param string $sourceNodeUuid 源主节点的uuid
     * @param string $targetNodeUuid 目标主节点的uuid
     * @return array
     */
    public function setClusterMasterNode(string $sourceNodeUuid, string $targetNodeUuid): array
    {
        $checkRet = $this->checkClusterOperateTaskStatus();
        if (!$checkRet['success']) {
            return $checkRet;
        }
        if ($sourceNodeUuid == $targetNodeUuid) {
            return $this->sendResult(xphp_get_lang('WEB_CLUSTER_SOURCE_NOT_SAME_TARGET'), false, 400);
        }
        // 判断源主节点是否可以被切换
        $checkResult = $this->checkSourceMasterNode($sourceNodeUuid);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        // 判断目标主节点是否可以设置为主节点
        $checkResult = $this->checkTargetMasterNode($targetNodeUuid);
        if (!$checkResult['success']) {
            return $checkResult;
        }

        $opcodeName = 'CLUSTER_OP_SYSTEM_SWITCH';
        $operate = $this->clusterOpcode->getOpcodeDes($opcodeName);
        $serviceResult = $this->service()->setClusterMasterNodeService($targetNodeUuid);
        if (!$serviceResult['result']) {
            $this->muOpResult(false, $operate, $serviceResult['msg'], 0, $serviceResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_CLUSTER_SET_MASTER_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_CLUSTER_SET_MASTER_SUCCESS'));
    }

    /**
     * 检查源主节点是否可以切换
     * @param string $sourceNodeUuid 源主节点uuid
     * @return array
     */
    private function checkSourceMasterNode(string $sourceNodeUuid): array
    {
        return $this->sendResult('');
    }

    /**
     * 检查目标节点是否可以设置为主节点
     * @param string $targetNodeUuid 目标主节点uuid
     * @return array
     */
    private function checkTargetMasterNode(string $targetNodeUuid): array
    {
        // 判断节点是否存在
        $sql = "SELECT * FROM bd_node WHERE node_uuid = ? ";
        $nodeData = $this->dbSelect($sql, [$targetNodeUuid]);
        if (!$nodeData || !is_array($nodeData)) {
            return $this->sendResult(xphp_get_lang('WEB_CLUSTER_TARGET_NODE_NOT_EXISTS'), false, 400);
        }
        if ($targetNodeUuid == $this->getCurrentMasterNode()) {
            return $this->sendResult(xphp_get_lang('WEB_CLUSTER_TARGET_ALREADY_MASTER'), false, 400);
        }
        return $this->sendResult('');
    }

    /**
     * 获取系统当前的主节点
     * @return string
     */
    private function getCurrentMasterNode(): string
    {
        $sql = "SELECT node_uuid FROM bd_cluster_node WHERE node_role = ? ";
        $data = $this->dbSelect($sql, [xphp_get_config('cluster', 'NODE_ROLE', 'cluster')['MASTER']]);
        if (!$data || !is_array($data)) {
            return '';
        }
        return $data[0]['node_uuid'];
    }

    /**
     * 获取集群是否已配置
     * @link Node::addNode
     * @return boolean
     */
    public function getClusterIsConfig(): bool
    {
        $data = $this->dbSelect("SELECT cluster_config_flag FROM bd_system ");
        if (!$data || !is_array($data)) {
            return false;
        }
        return v1_parse_flag_to_bool($data[0]['cluster_config_flag']);
    }

    /**
     * 获取集群的操作日志
     * @return array
     */
    public function getClusterOperateLog(): array
    {
        $sql = "SELECT op_user_name, error_code, error_detail, node_uuid, op_time,
                    description_key, description_param, log_level
                FROM bd_cluster_log WHERE running_flag = ? ORDER BY id DESC ";
        $clusterOperateLogData = $this->dbSelect($sql, [xphp_get_config('app', 'FLAG')['SET']]);
        $ret = [];
        foreach ($clusterOperateLogData as $clusterOperateLog) {
            $ret[] = [
                'op_time' => $clusterOperateLog['op_time'],
                'op_message' => $this->getClusterLogDescription(
                    $clusterOperateLog['description_key'],
                    $clusterOperateLog['description_param'],
                    $clusterOperateLog['error_code'] ?? '',
                    $clusterOperateLog['log_level'] ?? ''
                ),
                'log_level' => intval($clusterOperateLog['log_level']),
                'op_user' => $clusterOperateLog['op_user_name'],
                'node_uuid' => $clusterOperateLog['node_uuid'],
            ];
        }
        return $this->sendResult('', true, 200, $ret);
    }

    /**
     * 获取集群日志描述
     * @param string $descriptionKey   描述信息
     * @param string $descriptionParam 描述参数
     * @param string $errorCode        错误码
     * @param string $logLevel         日志级别
     * @return string
     */
    private function getClusterLogDescription(
        string $descriptionKey,
        string $descriptionParam,
        string $errorCode,
        string $logLevel
    ): string {
        $clusterLogDes = xphp_get_desc('Cluster', 'ClusterLog', 'cluster');
        $description = $clusterLogDes[$descriptionKey] ?? $descriptionKey;
        if ($errorCode) {
            $errorConf = xphp_get_config('error');
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $errorClass = $this->getLogLevelClass($logLevel);
            $description .= ',[' . '<span  class="' . $errorClass . '">#' . $errorCode . '</span>' . ']' . $errorStr;
        }
        if ($descriptionParam) {
            $pos = strpos($description, '%s');
            if ($pos === false) {
                return $description;
            }
            $descriptionParam = json_decode($descriptionParam, true);
            $descriptionArr = explode('%s', $description);
            // var_dump($descriptionArr);
            // var_dump($descriptionParam);
            $description = '';
            foreach ($descriptionArr as $k => $v) {
                // var_dump($k, $descriptionParam);
                $description .= $v . $this->getEachParamsDes($descriptionParam[$k] ?? '', $descriptionParam);
            }
        }
        return $description;
    }

    /**
     * 得到日志错误对应的类型
     * @param string $logLevel 日志级别
     * @return string
     */
    private function getLogLevelClass(string $logLevel): string
    {
        $logClass = 'font-green';   //一般日志
        $logLevelArr = xphp_get_config('log', 'LOGLEVEL');
        if ($logLevelArr['WARN'] == $logLevel) {
            $logClass = 'font-yellow-gold';
        } elseif ($logLevelArr['ERROR'] == $logLevel) {
            $logClass = 'font-red-thunderbird';
        }
        return $logClass;
    }

    /**
     * 获取一个参数的描述
     * @param string $eachParams       单个参数
     * @param array  $descriptionParam 描述参数
     * @return string
     */
    private function getEachParamsDes(string $eachParams, array $descriptionParam): string
    {
        if (!$eachParams) {
            return '';
        }

        $des = '';
        $eachArr = explode(':', $eachParams, 2);
        switch ($eachArr[0]) {
            case 'S':
                $des = $eachArr[1];
                break;
            case 'module_type':
                $des = $this->logModuleTypeDes(intval($eachArr[1]));
                break;
            case 'backup_mode':
                $des = $this->logBackupModeDes(intval($eachArr[1]), $descriptionParam);
                break;
            default:
                break;
        }
        return $des;
    }

    /**
     * 得到日志模块描述
     * @param int $moduleType 模块
     * @return string
     */
    private function logModuleTypeDes(int $moduleType): string
    {
        $moduleTypeDes = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        return $moduleTypeDes[$moduleType];
    }

    /**
     * 得到日志备份模式描述
     * @param int   $backupMode       备份模式
     * @param array $descriptionParam 描述参数
     * @return string
     */
    private function logBackupModeDes(int $backupMode, array $descriptionParam): string
    {
        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        $moduleTypeArr = explode(':', $descriptionParam[1], 2);
        if ($moduleTypeArr[0] == 'module_type' && $moduleTypeArr[1] == $allModuleType['VOL_CDP']) {
            return '';
        }
        $backupModeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        return $backupModeDes[$backupMode];
    }
}
