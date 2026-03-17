<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\ToolOpcode;

class Driver extends Base
{
    private $toolOpcode;

    public function __construct()
    {
        parent::__construct();
        $this->toolOpcode = new ToolOpcode();
    }

    /**
     * 获取驱动列表
     * @param array $params 请求参数
     * @return array
     */
    public function getDriverList(array $params): array
    {
        $sql = "SELECT driver_uuid, hardware_type, provider, driver_name, version,
                    os_type, create_time, remark,
                    IF(date REGEXP '^(0?[0-9]|1[0-2])-[0-9]{2}-[0-9]{4}$', STR_TO_DATE(date, '%m-%d-%Y'), date) AS formatted_date
                FROM bd_driver WHERE 1=1 ";
        $sqlCount = "SELECT COUNT(*) AS total FROM bd_driver WHERE 1=1 ";
        $sqlParams = [];
        $sqlCountParams = [];
        if (isset($params['name']) && $params['name']) {
            $name = '%' . v1_escape_wildcard($params['name']) . '%';
            $sql .= ' AND driver_name LIKE ? ';
            $sqlCount .= ' AND driver_name LIKE ? ';
            $sqlParams[] = $name;
            $sqlCountParams[] = $name;
        }

        // 排序
        $sortFields = [
            'driver_name' => 'driver_name',
            'hardware_type' => 'hardware_type',
            'provider' => 'provider',
            'date' => 'formatted_date',
            'version' => 'version',
            'create_time' => 'create_time',
            'os_type' => 'os_type',
        ];
        $sort = $sortFields[$params['sort']] ?? 'create_time';
        $order = strtoupper($params['order']) === 'DESC' ? 'DESC' : 'ASC';
        $sql .= " ORDER BY $sort $order LIMIT {$params['offset']}, {$params['limit']}";

        $countData = $this->dbSelect($sqlCount, $sqlCountParams);
        if (!is_array($countData)) {
            $countData = [['total' => 0]];
        }
        $driveData = dbSelect($sql, $sqlParams);
        if (!is_array($driveData)) {
            $driveData = [];
        }
        $ret = [
            'rows' => [],
            'total' => $countData[0]['total'],
        ];

        foreach ($driveData as $row) {
            $ret['rows'][] = [
                'driver_uuid' => $row['driver_uuid'],
                'driver_name' => $row['driver_name'],
                'hardware_type' => $row['hardware_type'],
                'provider' => $row['provider'],
                'date' => $row['formatted_date'],
                'version' => $row['version'],
                'create_time' => $row['create_time'],
                'os_type' => $row['os_type'],
                'remark' => $row['remark'],
            ];
        }

        return $this->sendResult('', true, 200, $ret);
    }

    /**
     * 获取驱动详情
     * @param string $driversUuid 驱动uuid
     * @return array
     */
    public function getDriverDetail(string $driversUuid): array
    {
        $checkResult = $this->checkDrivers([$driversUuid]);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $driverInfo = $checkResult['data'][0];

        $ret = [
            'driver_uuid' => $driverInfo['driver_uuid'],
            'driver_name' => $driverInfo['driver_name'],
            'hardware_type' => $driverInfo['hardware_type'],
            'provider' => $driverInfo['provider'],
            'date' => $driverInfo['date'],
            'version' => $driverInfo['version'],
            'create_time' => $driverInfo['create_time'],
            'os_type' => $driverInfo['os_type'],
            'remark' => $driverInfo['remark'],
            'details' => [],
        ];

        $sql = "SELECT driver_uuid, description, arch, min_ver, max_ver, specified_vers, hw_or_com_id
                FROM bd_driver_detail WHERE driver_uuid = ? ";
        $detailList = $this->dbSelect($sql, [$driversUuid]);
        if (!is_array($detailList) || !$detailList) {
            $detailList = [];
        }
        foreach ($detailList as $detailRow) {
            try {
                $specifiedVers = json_decode($detailRow['specified_vers'], true);
            } catch (\Exception $e) {
                $specifiedVers = [];
            }
            $ret['details'][] = [
                'driver_uuid' => $detailRow['driver_uuid'],
                'description' => $detailRow['description'],
                'arch' => $detailRow['arch'],
                'min_ver' => $detailRow['min_ver'],
                'max_ver' => $detailRow['max_ver'],
                'specified_vers' => $specifiedVers,
                'hw_or_com_id' => $detailRow['hw_or_com_id'],
            ];
        }
        return $this->sendResult('', true, 200, $ret);
    }

    /**
     * 验证驱动
     * @param array $driverUuidList 驱动uuid列表
     * @return array
     */
    private function checkDrivers(array $driverUuidList): array
    {
        $driverUuidList = array_values(array_unique($driverUuidList));
        $deriverUuids = "'" . implode("','", $driverUuidList) . "'";
        $sql = "SELECT driver_uuid, hardware_type, provider, driver_name, date, version,
                    os_type, create_time, remark
                FROM bd_driver WHERE driver_uuid IN ($deriverUuids) ";
        $driverList = $this->dbSelect($sql);
        if (!is_array($driverList) || !$driverList) {
            return $this->sendResult(xphp_get_lang('WEB_DRIVER_NOT_EXISTS'), false, 0);
        }

        if (count($driverList) !== count($driverUuidList)) {
            return $this->sendResult(xphp_get_lang('WEB_DRIVER_SOME_NOT_EXISTS'), false, 0);
        }

        return $this->sendResult('', true, 200, $driverList);
    }

    /**
     * 批量删除驱动
     * @param array $driverUuidList 驱动uuid列表
     * @return array
     */
    public function deleteDrivers(array $driverUuidList): array
    {
        $opcodeName = 'TOOL_DR_OP_DELETE';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);
        $checkResult = $this->checkDrivers($driverUuidList);
        if (!$checkResult['success']) {
            return $checkResult;
        }
        $driverList = $checkResult['data'];

        $deleteResult = $this->service()->deleteDriversService($driverUuidList);
        if (!$deleteResult['result']) {
            $this->muOpResult(false, $operate, $deleteResult['msg'], 0, $deleteResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_DRIVER_DELETE_ERROR'), false, 0);
        }
        $detailList = array_map(function ($driverRow) {
            return sprintf(xphp_get_lang('WEB_DRIVER_DELETE_DETAIL'), $driverRow['driver_name']);
        }, $driverList);
        return $this->sendResult(xphp_get_lang('WEB_DRIVER_DELETE_SUCCESS'), true, 200, $detailList);
    }

    /**
     * 上传驱动
     * @param array|null $file   驱动文件
     * @param int        $osType 操作系统类型
     * @return array
     */
    public function uploadDriver(?array $file, int $osType): array
    {
        if (is_null($file)) {
            // 表示没有上传文件
            return $this->sendResult(xphp_get_lang('WEB_DRIVER_NO_DRIVER_FILE'), false, 0);
        }

        // 上传文件到tmp路径下
        $tmpPath = xphp_get_config('app', 'DRIVER_UPLOAD_DIR');
        $this->deleteDir($tmpPath);  // 清除之前的目录，保证每次只有一个上传
        mkdir($tmpPath, 0777, true);  // umask 022, 因此创建的目录是755
        chmod($tmpPath, 0777);
        move_uploaded_file($file['tmp_name'], $tmpPath . '/' . $file['name']);

        // 执行linux命令解压
        $fileInfo = pathinfo($file['name']);
        $postfix = date('YmdH');
        $uploadPath = $tmpPath . '/' . $fileInfo['filename'] . '_' . $postfix;
        $cmd = "unzip \"$tmpPath/{$file['name']}\" -d \"$uploadPath\"";
        exec($cmd, $output, $returnVar);
        // 删除压缩包
        unlink($tmpPath . '/' . $file['name']);

        // 解析驱动信息
        $opcodeName = 'TOOL_DR_OP_PARSE';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);
        $parseResult = $this->service()->parseDriverService($uploadPath, $osType);
        if (!$parseResult['result']) {
            $this->muOpResult(false, $operate, $parseResult['msg'], 0, $parseResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_DRIVER_UPLOAD_ERROR'), false, 0);
        }

        $parseData = $parseResult['msg'];
        return $this->sendResult('', true, 200, [
            'driver_name' => $parseData['name'],
            'version' => $parseData['version'],
            'date' => $parseData['date'],
            'hardware_type' => $parseData['class'],
            'provider' => $parseData['provider'],
            'upload_path' => $uploadPath,
        ]);
    }

    /**
     * 删除目录
     * @param string $dirPath 目录
     * @return void
     */
    private function deleteDir(string $dirPath)
    {
        if (!is_dir($dirPath)) {
            return;
        }
        $dirHandle = opendir($dirPath);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file != '.' && $file != '..') {
                $filePath = $dirPath . '/' . $file;
                if (is_dir($filePath)) {
                    $this->deleteDir($filePath);
                } else {
                    unlink($filePath);
                }
            }
        }
        closedir($dirHandle);
        rmdir($dirPath);
    }

    /**
     * 添加驱动
     * @param array $params 参数
     * @return array
     */
    public function addDriver(array $params): array
    {
        if (!is_dir($params['upload_path'])) {
            return $this->sendResult(xphp_get_lang('WEB_DRIVER_UPLOAD_PATH_EXPIRED'), false, 0);
        }

        $opcodeName = 'TOOL_DR_OP_PARSE_AND_STORE';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);
        // 添加驱动
        $addResult = $this->service()->addDriverService(
            $params['upload_path'],
            intval($params['os_type']),
            $params['remark'] ?? ''
        );
        if (!$addResult['result']) {
            $this->muOpResult(false, $operate, $addResult['msg'], 0, $addResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_DRIVER_ADD_ERROR'), false, 0);
        }

        return $this->sendResult(xphp_get_lang('WEB_DRIVER_ADD_SUCCESS'));
    }

    /**
     * 获取代理驱动安装列表
     * @param array $agentUuidList 代理uuid列表
     * @return array
     */
    public function getAgentInstallDriverList(array $agentUuidList): array
    {
        $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $sql = "SELECT ip, os_version, online_flag, agent_uuid, os_arch, os_current_version,
                    driver_status, hardware_info
                FROM bd_agent
                WHERE agent_uuid IN ($agentUuids) AND agent_type != ? ";
        $clientList = $this->dbSelect($sql, [$allAgentType['APPLIANCE']]);
        if (!is_array($clientList)) {
            $clientList = [];
        }
        return $this->sendResult('', true, 200, [
            'total' => count($clientList),
            'rows' => array_map(function ($row) {
                return [
                    'agent_uuid' => $row['agent_uuid'],
                    'agent_ip' => $row['ip'],
                    'os_version' => $row['os_version'],
                    // 'os_arch' => $row['os_arch'],
                    // 'os_current_version' => $row['os_current_version'],
                    'online_flag' => v1_parse_flag_to_bool($row['online_flag']),
                    'driver_status' => $row['driver_status'],
                    'hardware_info' => $row['hardware_info'],
                ];
            }, $clientList),
        ]);
    }

    /**
     * 安装代理驱动
     * @param array $agentUuidList 代理uuid列表
     * @return array
     */
    public function installAgentDriver(array $agentUuidList): array
    {
        $agentUuidList = array_values(array_unique($agentUuidList));
        $agentUuids = "'" . implode("' ,'", $agentUuidList) . "'";
        // 判断客户端是否存在
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        $sql = "SELECT agent_uuid, ip, hostname, agent_name
                FROM bd_agent
                WHERE agent_uuid IN ($agentUuids) AND agent_type != ? ";
        $agentList = $this->dbSelect($sql, [$allAgentType['APPLIANCE']]);
        if (!is_array($agentList) || count($agentList) != count($agentUuidList)) {
            return $this->sendResult(xphp_get_lang('WEB_ERROR_BD_AGENT_NOT_EXIST_ERROR'), false, 0);
        }

        // 权限验证
        $this->checkAuthBySourceUuid(implode(',', $agentUuidList), xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT']);

        $opcodeName = 'NODE_AGENT_OP_INSTALL_DRIVER_FOR_AGENT';
        $nodeOpcode = new \app\v1\opcode\NodeOpcode();
        $operate = $nodeOpcode->getOpcodeDes($opcodeName);
        $installResult = $this->service()->installAgentDriverService($agentUuidList);
        if (!$installResult['result']) {
            $this->muOpResult(false, $operate, $installResult['msg'], 0, $installResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_INSTALL_DRIVERS_COMMAND_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_AGENT_INSTALL_DRIVERS_COMMAND_SUCCESS'));
    }

    /**
     * 批量检查操作系统驱动
     * @param array $checkOsList 操作系统检查列表
     * @param int   $driverUsage 驱动检测用途
     * @return array
     */
    public function checkOsDrivers(array $checkOsList, int $driverUsage): array
    {
        /**
         * 异构
         * 1. 非异构 => 直接通过
         *
         *
         * p => p（只选择Windows，禁用选择）
         * p => v
         * 1. os_backup_timepoint缺失hardware_info，“备份点缺失硬件信息，请再做一次增量备份或完全备份”
         * 2. 针对Windows，os_backup_timepoint缺失os_arch、os_version，“未检测到系统信息”；Linux缺失，选择架构
         *
         * v => p
         * v => v
         * 1. 直接选择，Linux操作系统和架构
         * 2.
         *
         * 创建恢复任务需要携带“driver_hw_id_map” : "\"{}\""(JSON字符串)
         */

        // 获取恢复源信息
        $driverCheckSource = $this->getDriverCheckSourceMap($checkOsList, $driverUsage);
        $sourceTimepointMap = $driverCheckSource['timepoint_map'];
        $sourceAgentMap = $driverCheckSource['agent_map'];
        $sourceVmMap = $driverCheckSource['vm_map'];
        // 获取目标信息
        $targetAgentMap = $this->getDriverCheckTargetAgentMap($checkOsList);

        $opcodeName = 'WEB_TOOL_DR_OP_CHECK_DIFF_PLAT_AND_DRIVER_EXIST';
        $operate = $this->toolOpcode->getOpcodeDes($opcodeName);
        $ret = [
            'check_result' => [],
        ];
        $allDriverCheckUsage = xphp_get_config('driver', 'DRIVER_CHECK_USAGE', 'resources');
        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        $allDriverTargetType = xphp_get_config('driver', 'DRIVER_TARGET_TYPE', 'resources');
        $allDriverCheckResult = xphp_get_config('driver', 'DRIVER_CHECK_RESULT', 'resources');
        $allOsTypeMap = xphp_get_config('driver', 'OS_TYPE_MAP', 'resources');
        $allDriverPlatformType = xphp_get_config('driver', 'DRIVER_PLATFORM_TYPE','resources');
        $findNoOsFlag = false;
        foreach ($checkOsList as $checkOs) {
            if ($findNoOsFlag) {
                $ret['check_result'][] = [
                    'platform_type' => $allDriverPlatformType['UNKNOWN'],
                    'driver_check_status' => $allDriverCheckResult['NO_HW'],
                    'lack_driver_info' => '',
                    'driver_hw_id_map' => '',
                ];
                continue;
            }

            // 检测源信息
            $sourceModuleType = 0;
            $sourceHypervisorType = 0;
            $sourceHWInfo = '';
            $osType = 'Windows';
            $osVersion = '';
            $osArch = '';
            $sourceHypervisorDiskBusList = [];
            $sourceHypervisorNetBusList = [];
            if ($driverUsage == $allDriverCheckUsage['BACKUP']) { // 备份
                if (!isset($sourceAgentMap[$checkOs['agent_uuid']])) {
                    $ret['check_result'][] = [
                        'platform_type' => $allDriverPlatformType['UNKNOWN'],
                        'driver_check_status' => $allDriverCheckResult['NO_CLIENT'],
                        'lack_driver_info' => '',
                        'driver_hw_id_map' => '',
                    ];
                    continue;
                }
                $sourceAgentInfo = $sourceAgentMap[$checkOs['agent_uuid']];

                $sourceModuleType = $allModuleType['OS'];
                $sourceHWInfo = $sourceAgentInfo['hardware_info'];
                $osType = $sourceAgentInfo['os_type'];
                $osVersion = $sourceAgentInfo['os_current_version'];
                $osArch = $sourceAgentInfo['os_arch'];

                if (!$sourceHWInfo) {  // 提示: 客户端不存在
                    $ret['check_result'][] = [
                        'platform_type' => $allDriverPlatformType['UNKNOWN'],
                        'driver_check_status' => $allDriverCheckResult['CLIENT_LACK_HW'],
                        'lack_driver_info' => '',
                        'driver_hw_id_map' => '',
                    ];
                    continue;
                }
                if ($checkOs['manual_config_flag']) {  // 手动选择优先级最高
                    $osVersion = $checkOs['os_version'];
                    $osArch = $checkOs['os_arch'];
                } else {
                    if (!$osVersion || !$osArch) {  // 不存在版本或架构信息
                        if ($osType === 'Windows') {  // 提示: 未检测到系统信息
                            $ret['check_result'][] = [
                                'platform_type' => $allDriverPlatformType['UNKNOWN'],
                                'driver_check_status' => $allDriverCheckResult['NO_OS'],
                                'lack_driver_info' => '',
                                'driver_hw_id_map' => '',
                            ];
                            $findNoOsFlag = true;
                            continue;
                        } else {  // Linux缺失架构需要选择架构信息
                            $osVersion = '';
                            if (!$osArch)  {
                                $ret['check_result'][] = [
                                    'platform_type' => $allDriverPlatformType['UNKNOWN'],
                                    'driver_check_status' => $allDriverCheckResult['NO_OS'],
                                    'lack_driver_info' => '',
                                    'driver_hw_id_map' => '',
                                ];
                                $findNoOsFlag = true;
                                continue;
                            }
                            $osVersion = '';
                        }
                    }
                }
            } elseif (
                $driverUsage == $allDriverCheckUsage['TAKEOVER'] ||
                ($checkOs['module_type'] == $allModuleType['VOL_CDP'] && $driverUsage == $allDriverCheckUsage['RECOVERY'])  // 整机实时恢复
            ) {
                if ($checkOs['check_agent_flag']) {
                    if (!isset($sourceAgentMap[$checkOs['agent_uuid']])) {
                        $ret['check_result'][] = [
                            'platform_type' => $allDriverPlatformType['UNKNOWN'],
                            'driver_check_status' => $allDriverCheckResult['NO_CLIENT'],
                            'lack_driver_info' => '',
                            'driver_hw_id_map' => '',
                        ];
                        continue;
                    }
                    $sourceAgentInfo = $sourceAgentMap[$checkOs['agent_uuid']];

                    $sourceModuleType = $allModuleType['VOL_CDP'];
                    $sourceHWInfo = $sourceAgentInfo['hardware_info'];
                    $osType = $sourceAgentInfo['os_type'];
                    $osVersion = $sourceAgentInfo['os_current_version'];
                    $osArch = $sourceAgentInfo['os_arch'];
                } elseif ($checkOs['check_vm_flag']) {
                    if (!isset($sourceVmMap[$checkOs['vcenter_uuid'] . '_' . $checkOs['vm_uuid']])) {
                        $ret['check_result'][] = [
                            'platform_type' => $allDriverPlatformType['UNKNOWN'],
                            'driver_check_status' => $allDriverCheckResult['NO_CLIENT'],
                            'lack_driver_info' => '',
                            'driver_hw_id_map' => '',
                        ];
                        continue;
                    }
                    $sourceVmInfo = $sourceVmMap[$checkOs['vcenter_uuid'] . '_' . $checkOs['vm_uuid']];

                    $sourceModuleType = $allModuleType['VM'];
                    $sourceHWInfo = '';
                    $osType = $checkOs['os_type'];
                    $sourceHypervisorType = intval($sourceVmInfo['hypervisor_type']);
                    $osVersion = strval($checkOs['os_version']);
                    $osArch = strval($checkOs['os_arch']);
                    $sourceHypervisorDiskBusList = $checkOs['source_hypervisor_disk_bus_list'];
                    $sourceHypervisorNetBusList = $checkOs['source_hypervisor_net_bus_list'];
                } else {
                    if (!isset($sourceTimepointMap[$checkOs['timepoint_uuid']])) {
                        $ret['check_result'][] = [
                            'platform_type' => $allDriverPlatformType['UNKNOWN'],
                            'driver_check_status' => $allDriverCheckResult['NO_TIMEPOINT'],
                            'lack_driver_info' => '',
                            'driver_hw_id_map' => '',
                        ];
                        continue;
                    }
                    $sourceTimepointInfo = $sourceTimepointMap[$checkOs['timepoint_uuid']];
                    $sourceHWInfo = $sourceTimepointInfo['hardware_info'];
                    $osType = $sourceTimepointInfo['os_type'];
                    $osVersion = $sourceTimepointInfo['os_version'];
                    $osArch = $sourceTimepointInfo['os_arch'];
                    $sourceModuleType = $allModuleType['VOL_CDP'];
                }
                if ($checkOs['manual_config_flag']) {  // 手动选择优先级最高
                    $osType = $checkOs['os_type'];
                    $osVersion = $checkOs['os_version'];
                    $osArch = $checkOs['os_arch'];
                }
            } else { // 恢复
                if ($checkOs['check_agent_flag']) {
                    if (!isset($sourceAgentMap[$checkOs['agent_uuid']])) {
                        $ret['check_result'][] = [
                            'platform_type' => $allDriverPlatformType['UNKNOWN'],
                            'driver_check_status' => $allDriverCheckResult['NO_CLIENT'],
                            'lack_driver_info' => '',
                            'driver_hw_id_map' => '',
                        ];
                        continue;
                    }
                    $sourceAgentInfo = $sourceAgentMap[$checkOs['agent_uuid']];

                    $sourceModuleType = $allModuleType['OS'];
                    $sourceHWInfo = $sourceAgentInfo['hardware_info'];
                    $osType = $sourceAgentInfo['os_type'];
                    $osVersion = $sourceAgentInfo['os_current_version'];
                    $osArch = $sourceAgentInfo['os_arch'];
                    if ($checkOs['manual_config_flag']) {
                        $osType = $checkOs['os_type'];
                        $osVersion = $checkOs['os_version'];
                        $osArch = $checkOs['os_arch'];
                    }
                } elseif ($checkOs['check_vm_flag']) {
                    if (!isset($sourceVmMap[$checkOs['vcenter_uuid'] . '_' . $checkOs['vm_uuid']])) {
                        $ret['check_result'][] = [
                            'platform_type' => $allDriverPlatformType['UNKNOWN'],
                            'driver_check_status' => $allDriverCheckResult['NO_CLIENT'],
                            'lack_driver_info' => '',
                            'driver_hw_id_map' => '',
                        ];
                        continue;
                    }
                    $sourceVmInfo = $sourceVmMap[$checkOs['vcenter_uuid'] . '_' . $checkOs['vm_uuid']];

                    $sourceModuleType = $allModuleType['VM'];
                    $sourceHWInfo = '';
                    $osType = $checkOs['os_type'];
                    $sourceHypervisorType = intval($sourceVmInfo['hypervisor_type']);
                    $osVersion = strval($checkOs['os_version']);
                    $osArch = strval($checkOs['os_arch']);
                    $sourceHypervisorDiskBusList = $checkOs['source_hypervisor_disk_bus_list'];
                    $sourceHypervisorNetBusList = $checkOs['source_hypervisor_net_bus_list'];
                } else {
                    if (!isset($sourceTimepointMap[$checkOs['timepoint_uuid']])) {
                        $ret['check_result'][] = [
                            'platform_type' => $allDriverPlatformType['UNKNOWN'],
                            'driver_check_status' => $allDriverCheckResult['NO_TIMEPOINT'],
                            'lack_driver_info' => '',
                            'driver_hw_id_map' => '',
                        ];
                        continue;
                    }
                    $sourceTimepointInfo = $sourceTimepointMap[$checkOs['timepoint_uuid']];

                    $sourceModuleType = intval($sourceTimepointInfo['module_type']);
                    $sourceHypervisorType = intval($sourceTimepointInfo['hypervisor_type']);
                    $sourceHWInfo = '';
                    $osType = $allOsTypeMap[$sourceTimepointInfo['os_type']] ?? 'Windows';
                    $osVersion = strval($checkOs['os_version']);
                    $osArch = strval($checkOs['os_arch']);
                    if ($sourceModuleType == $allModuleType['OS']) {  // 操作系统
                        $sourceHypervisorType = 0;
                        $osConfig = json_decode($sourceTimepointInfo['os_config'], true);
                        if (!isset($osConfig['hardware_info'])) {  // 提示: 备份点缺失硬件信息，请再做一次增量备份或完全备份
                            $ret['check_result'][] = [
                                'platform_type' => $allDriverPlatformType['UNKNOWN'],
                                'driver_check_status' => $allDriverCheckResult['NO_HW'],
                                'lack_driver_info' => '',
                                'driver_hw_id_map' => '',
                            ];
                            $findNoOsFlag = true;
                            continue;
                        }
                        $sourceHWInfo = $osConfig['hardware_info'];
                        if ($checkOs['manual_config_flag']) {  // 手动选择优先级最高
                            $osVersion = $checkOs['os_version'];
                            $osArch = $checkOs['os_arch'];
                        } else {
                            if (
                                isset($osConfig['os_current_version']) &&
                                isset($osConfig['os_arch']) &&
                                $osConfig['os_current_version'] &&
                                $osConfig['os_arch']
                            ) {  // 有存版本和架构信息
                                $osVersion = $osConfig['os_current_version'];
                                $osArch = $osConfig['os_arch'];
                            } else {
                                if ($osType === 'Windows') {  // 提示: 未检测到系统信息
                                    $ret['check_result'][] = [
                                        'platform_type' => $allDriverPlatformType['UNKNOWN'],
                                        'driver_check_status' => $allDriverCheckResult['NO_OS'],
                                        'lack_driver_info' => '',
                                        'driver_hw_id_map' => '',
                                    ];
                                    $findNoOsFlag = true;
                                    continue;
                                } else {  // Linux缺失架构需要选择架构信息
                                    if (isset($osConfig['os_arch']) && $osConfig['os_arch']) {
                                        $osArch = $osConfig['os_arch'];
                                    } else {
                                        $ret['check_result'][] = [
                                            'platform_type' => $allDriverPlatformType['UNKNOWN'],
                                            'driver_check_status' => $allDriverCheckResult['NO_OS'],
                                            'lack_driver_info' => '',
                                            'driver_hw_id_map' => '',
                                        ];
                                        $findNoOsFlag = true;
                                        continue;
                                    }
                                    $osVersion = '';
                                }
                            }
                        }
                    } else {  // 虚拟化
                        if (!$checkOs['manual_config_flag']) {
                            $ret['check_result'][] = [
                                'platform_type' => $allDriverPlatformType['UNKNOWN'],
                                'driver_check_status' => $allDriverCheckResult['NO_OS'],
                                'lack_driver_info' => '',
                                'driver_hw_id_map' => '',
                            ];
                            $findNoOsFlag = true;
                            continue;
                        }
                        $osType = strval($checkOs['os_type']);
                        $sourceHypervisorDiskBusList = $checkOs['source_hypervisor_disk_bus_list'];
                        $sourceHypervisorNetBusList = $checkOs['source_hypervisor_net_bus_list'];
                    }
                }
            }

            // $samePlatformFlag = true;
            $targetModuleType =  $allModuleType['OS'];
            $targetHypervisorType = 0;
            $targetHWInfo = '';
            if ($driverUsage != $allDriverCheckUsage['TAKEOVER']) {
                // 检测目标信息
                if ($allDriverTargetType['CLIENT'] == $checkOs['target_type']) {  // 操作系统
                    $targetHWInfo = $targetAgentMap[$checkOs['client']['agent_uuid']]['hardware_info'];
                    // if ($targetAgentMap[$checkOs['client']['agent_uuid']]['os_arch'] != $osArch) {
                    //     $samePlatformFlag = false;
                    // }
                    // 比较源与目标的架构
                } else {  // 虚拟化
                    $targetModuleType = $allModuleType['VM'];
                    $targetHypervisorType = intval($checkOs['vm']['vm_type']);
                }
            } else {
                $targetModuleType = $allModuleType['VOL_CDP'];
            }
            // if (!$samePlatformFlag) {
            //     $ret['check_result'][] = [
            //         'platform_type' => $allDriverPlatformType['UNKNOWN'],
            //         'driver_check_status' => $allDriverCheckResult['PASS'],
            //         'lack_driver_info' => '',
            //         'driver_hw_id_map' => '',
            //     ];
            //     continue;
            // }
            $sourceHypervisorDiskBusList = array_values(array_unique($sourceHypervisorDiskBusList));
            $sourceHypervisorNetBusList = array_values(array_unique($sourceHypervisorNetBusList));
            $diskBusList = array_values(array_unique($checkOs['target_hypervisor_disk_bus_list']));
            $netBusList = array_values(array_unique($checkOs['target_hypervisor_net_bus_list']));
            $msg = [
                'source_module_type' => $sourceModuleType,
                'source_hypervisor_type' => $sourceHypervisorType,
                'source_hardware_info' => $sourceHWInfo,
                'source_hypervisor_disk_bus_list' => $sourceHypervisorDiskBusList,
                'source_hypervisor_net_bus_list' => $sourceHypervisorNetBusList,
                'os_type' => $osType,
                'os_version' => $osVersion,
                'os_arch' => $osArch,
                'target_module_type' => $targetModuleType,
                'target_hypervisor_type' => $targetHypervisorType,
                'target_hardware_info' => $targetHWInfo,
                'target_hypervisor_disk_bus_list' => $diskBusList,
                'target_hypervisor_net_bus_list' => $netBusList,
            ];
            $checkResult = $this->service()->checkOsDriverService($msg);
            if (!$checkResult['result']) {
                if (
                    $checkResult['errorCode'] == 25009 ||  // 不支持的控制器类型
                    $checkResult['errorCode'] == 25010  // 未知的的控制器类型
                ) {
                    $this->muOpResult(false, $operate, $checkResult['errorMsg']['controller_description'], 0, $checkResult['errorCode']);
                } elseif ($checkResult['errorCode'] == 25005) {  // 操作系统不支持安装驱动
                    $ret['check_result'][] = [
                        'platform_type' => $allDriverPlatformType['DIFF'],
                        'driver_check_status' => $allDriverCheckResult['NONSUPPORT_OS_INSTALL_DRIVER'],
                        'lack_driver_info' => '',
                        'driver_hw_id_map' => '',
                    ];
                    continue;
                } else {
                    $this->muOpResult(false, $operate, $checkResult['errorMsg'], 0, $checkResult['errorCode']);
                }
                return $this->sendResult('', false, 0);
            }
            $msg = $checkResult['msg'];
            $driverCheckStatus = $allDriverCheckResult['PASS'];  // 这里只有检测通过和缺失驱动
            $lackDriverList = [];
            if ($msg['driver_lack_flag']) {
                $driverCheckStatus = $allDriverCheckResult['NO_DRIVER'];
            }
            if (is_array($msg['unknown_hw_id_map'])) {
                foreach ($msg['unknown_hw_id_map'] as $hwId => $des) {
                    $lackDriverList[] = $hwId . '/' . $des;
                }
            }
            if ($msg['diff_plat_flag']) {
                $ret['check_result'][] = [
                    'platform_type' => $allDriverPlatformType['DIFF'],
                    'driver_check_status' => $driverCheckStatus,
                    'lack_driver_info' => implode(',', $lackDriverList),
                    'driver_hw_id_map' => isset($msg['driver_hw_id_map']) ? json_encode($msg['driver_hw_id_map']) : '',
                ];
            } else {
                $ret['check_result'][] = [
                    'platform_type' => $allDriverPlatformType['SAME'],
                    'driver_check_status' => $driverCheckStatus,
                    'lack_driver_info' => implode(',', $lackDriverList),
                    'driver_hw_id_map' => isset($msg['driver_hw_id_map']) ? json_encode($msg['driver_hw_id_map']) : '',
                ];
            }
        }

        return $this->sendResult('', true, 200, $ret);
    }

    /**
     * 获取驱动检测源信息
     * @param array $checkOsList 操作系统检查列表
     * @param int   $driverUsage 驱动检测用途
     * @return array
     */
    private function getDriverCheckSourceMap(array $checkOsList, int $driverUsage): array
    {
        $allDriverCheckUsage = xphp_get_config('driver', 'DRIVER_CHECK_USAGE', 'resources');
        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        // 获取恢复源信息
        $sourceTimepointMap = [];
        $sourceAgentList = [];
        $sourceAgentMap = [];
        $sourceVmMap = [];
        // 源客户端信息
        $agentUuidList = array_values(array_unique(array_column($checkOsList, 'agent_uuid')));
        if (count($agentUuidList)) {
            $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
            // 查询客户端信息
            $sql = "SELECT agent_uuid, os_type, os_arch, os_current_version, hardware_info,
                        {$allModuleType['OS']} AS module_type, 0 As hypervisor_type
                    FROM bd_agent
                    WHERE agent_uuid IN ($agentUuids)";
            $sourceAgentList = $this->dbSelect($sql);
            foreach ($sourceAgentList as $sourceAgentInfo) {
                $sourceAgentMap[$sourceAgentInfo['agent_uuid']] = $sourceAgentInfo;
            }
        }
        // 源虚拟机信息
        $sql = "SELECT vt.vcenter_uuid, vt.uuid, vt.name, vt.detail, vc.hypervisor_type
                FROM vm_tree vt INNER JOIN vm_vcenter vc ON vt.vcenter_uuid = vc.vcenter_uuid
                WHERE vt.type = 7 ";
        $vmSubSqlList = [];
        foreach ($checkOsList as $checkOs) {
            if ($checkOs['check_vm_flag']) {
                $vmSubSqlList[] = " (vt.vcenter_uuid = '{$checkOs['vcenter_uuid']}' AND vt.uuid = '{$checkOs['vm_uuid']}') ";
            }
        }
        if (count($vmSubSqlList)) {
            $sql .= " AND ( ". implode(" OR ", $vmSubSqlList) . ") ";
            $sourceVmList = $this->dbSelect($sql);
            foreach ($sourceVmList as $sourceVmInfo) {
                $sourceVmMap[$sourceVmInfo['vcenter_uuid'] . '_' . $sourceVmInfo['uuid']] = $sourceVmInfo;
            }
        }
        $moduleType = 0;
        if ($checkOsList) {
            $moduleType = $checkOsList[0]['module_type'];
        }
        if (
            $driverUsage == $allDriverCheckUsage['TAKEOVER'] ||  // 接管到内嵌虚拟化, 查询timepoint
            ($moduleType == $allModuleType['VOL_CDP'] && $driverUsage == $allDriverCheckUsage['RECOVERY'])  // 整机实时恢复
        ) {
            $timepointUuidList = array_values(array_unique(array_column($checkOsList, 'timepoint_uuid')));
            $timepointUuids = "'" . implode("', '", $timepointUuidList) . "'";
            $sql = "SELECT timepoint_uuid, master_agent_detail FROM cdp_vol_backup_agent WHERE timepoint_uuid IN ($timepointUuids)";
            $sourceTimepointList = $this->dbSelect($sql);
            foreach ($sourceTimepointList as $sourceTimepointInfo) {
                $masterAgentDetail = json_decode($sourceTimepointInfo['master_agent_detail'], true);
                $sourceTimepointMap[$sourceTimepointInfo['timepoint_uuid']] = [
                    'os_type' => $masterAgentDetail['os_type'],
                    'os_version' => $masterAgentDetail['os_current_version'],
                    'os_arch' => $masterAgentDetail['os_arch'],
                    'hardware_info' => $masterAgentDetail['hardware_info'],
                ];
            }
        } else {  // 恢复查询timepoint
            $timepointUuidList = array_values(array_unique(array_column($checkOsList, 'timepoint_uuid')));
            $timepointUuids = "'" . implode("', '", $timepointUuidList) . "'";
            // 查询备份点信息
            $sql = "SELECT bbt.timepoint_uuid, bbt.module_type, bbt.sub_module_type, bbt.task_type,
                        obt.os_type, obt.os_config,
                        vbt.vcenter_uuid, vbt.vm_uuid, vbt.hypervisor_type, vbt.vm_config
                    FROM bd_backup_timepoint bbt
                        LEFT JOIN os_backup_timepoint obt ON bbt.timepoint_uuid = obt.timepoint_uuid
                        LEFT JOIN vm_backup_timepoint vbt ON bbt.timepoint_uuid = vbt.timepoint_uuid
                    WHERE bbt.timepoint_uuid IN ($timepointUuids) ";
            $sourceTimepointList = $this->dbSelect($sql);
            foreach ($sourceTimepointList as $sourceTimepointInfo) {
                $sourceTimepointMap[$sourceTimepointInfo['timepoint_uuid']] = $sourceTimepointInfo;
            }
        }
        return [
            'timepoint_map' => $sourceTimepointMap,
            'agent_map' => $sourceAgentMap,
            'vm_map' => $sourceVmMap,
        ];
    }

    /**
     * 获取驱动检测目标客户端信息
     * @param array $checkOsList 检测列表
     * @return array
     */
    private function getDriverCheckTargetAgentMap(array $checkOsList): array
    {
        $ret = [];
        $allDriverTargetType = xphp_get_config('driver', 'DRIVER_TARGET_TYPE', 'resources');
        $agentUuidList = [];
        foreach ($checkOsList as $checkOs) {
            if ($allDriverTargetType['CLIENT'] == $checkOs['target_type']) {
                $agentUuidList[] = $checkOs['client']['agent_uuid'];
            }
        }
        $agentUuidList = array_values(array_unique($agentUuidList));
        $agentUuids = "'" . implode("', '", $agentUuidList) . "'";
        $sql = "SELECT agent_uuid, hardware_info, ip, hostname, os_arch, os_current_version, os_type
                FROM bd_agent
                WHERE agent_uuid IN ($agentUuids) ";
        $agentList  = $this->dbSelect($sql);
        foreach ($agentList as $agentInfo) {
            $ret[$agentInfo['agent_uuid']] = $agentInfo;
        }
        return $ret;
    }
}
