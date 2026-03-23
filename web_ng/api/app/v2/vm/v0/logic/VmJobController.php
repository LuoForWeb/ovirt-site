<?php

namespace app\v2\vm\v0\logic;

use app\v2\common\logic\JobController;
use app\v2\opcode\PfOpcode;
use app\v2\opcode\VmOpcode;
use app\v2\resources\v0\logic\Node;
use app\v2\system\v0\logic\Index as SystemHandler;
use app\v2\tenant\v0\logic\Tenant;
use app\v2\vm\v0\service\Service;

/**
 * note          虚拟机 之任务操作 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VmJobController extends JobController
{
    /**
     * 启动虚拟机
     * @param array $params 参数
     * @return string
     */
    public function startVm(array $params): string
    {
        return $this->machineOpUnify($params, 'VM_MACHINE_OP_POWERON');
    }

    /**
     * 关闭虚拟机
     * @param array $params 参数
     * @return string
     */
    public function stopVm(array $params): string
    {
        return $this->machineOpUnify($params, 'VM_MACHINE_OP_POWEROFF');
    }

    /**
     * 挂起虚拟机
     * @param array $params 参数
     * @return string
     */
    public function pauseVm(array $params): string
    {
        return $this->machineOpUnify($params, 'VM_MACHINE_OP_SUSPEND');
    }

    /**
     * 重启虚拟机
     * @param array $params 参数
     * @return string
     */
    public function restartVm(array $params): string
    {
        return $this->machineOpUnify($params, 'VM_MACHINE_OP_RESUME');
    }

    /**
     * 终止虚拟机
     * @param array $params 参数
     * @return string
     */
    public function terminateVm(array $params): string
    {
        return $this->machineOpUnify($params, 'VM_MACHINE_OP_TERMINATE');
    }

    /**
     * 获取某个虚拟机的磁盘列表
     * @param array $params 参数
     * @return array
     */
    public function getVMDiskList(array $params): array
    {
        $vmUuid = $params['vm_uuid'];
        $platformUuid = $params['platform_uuid'];
        $sql = "select vv.hypervisor_type from vm_vcenter vv where vv.vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($platformUuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeUuid = Node::instance()->getLocalNodeUUID();
        $mbResult = $this->service()->getVmDiskListService($nodeUuid, $hypervisor, $platformUuid, $vmUuid);
        $info = array();
        if ($mbResult['result']) {
            $info = $mbResult['msg'];
        }
        return $info;
    }

    /**
     * 添加虚拟机到备份任务
     * @param array $params 参数
     * @return string
     */
    public function addVmToBackupTask(array $params): string
    {
        $vmUuid = $params['vm_uuid'];
        $platformUuid = $params['platform_uuid'];
        $taskUuid = $params['jobs_uuid'];
        $displayMode = $params['display_mode'];
        $this->paramsCheck($vmUuid, $platformUuid, $taskUuid, $displayMode);
        $vmUuidArr = explode(',', $vmUuid);
        $vmUuidStr = implode("','", $vmUuidArr);
        $operate = xphp_get_lang('WEB_VM_VCENTER_ADD_VM_TO_TASK');

        //虚拟机检测授权
        $sql = "select hypervisor_type from vm_vcenter where vcenter_uuid = ?";
        $hypervisor = $this->dbSelect($sql, [$platformUuid])[0]['hypervisor'];
        if (!in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
            //租户内检查可用数量是否超过授权个数
            if (!empty($_SESSION['tenantuuid'])) {
                $tenantHandler = Tenant::instance();
                $tenantHandler->checkTenantAuth(xphp_get_config('module', 'MODULE_TYPE')['VM'], $vmUuidArr, '');
            } else {
                $this->checkTaskLegal($operate, $vmUuidArr, '');
            }
        }

        //检测任务是否存在
        $sql = "select id, task_status from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        if (empty($data)) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('WEB_VM_VCENTER_ADD_VM_TO_TASK_NOTASK'), 'warning'));
        }

        //任务运行中不允许添加
        if (intval($data[0]['task_status']) == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            exit($this->muOpResult(
                false,
                $operate,
                xphp_get_lang('WEB_VM_VCENTER_ADD_VM_TASK_RUNNING_ERROR'),
                'warning'
            ));
        }

        //检测虚拟机是否存在
        $sql = "select * from vm_tree where vcenter_uuid = ? and uuid in ('" . $vmUuidStr . "') and display_mode = ?";
        $vmInfo = $this->dbSelect($sql, array($platformUuid, $displayMode));
        if (!$vmInfo) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('WEB_VM_VCENTER_ADD_VM_TO_TASK_NOVM'), 'warning'));
        }
        //检测虚拟机是否已经在任务中
        $sql = "select machine_id from vm_machine_list where vcenter_uuid = ? and vm_uuid in ('" . $vmUuidStr . "') 
            and task_uuid = ?";
        $data = $this->dbQuery($sql, array($platformUuid, $taskUuid));
        if ($data > 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('WEB_VM_VCENTER_ADD_VM_TO_TASK_INTASK'), 'warning'));
        }
        $result = 0;
        // 检测是否有GFS策略，并获取level1_type
        $sql = "select level1_type from bd_gfs_entity_waiting_map where task_uuid = ?";
        $gfsData = (array) $this->dbSelect($sql, [$taskUuid]);
        $level1Types = array_unique(array_column($gfsData, 'level1_type'));
        $gfsFlag = !empty($gfsData);
        //开始事务
        $this->dbBeginTransaction();
        foreach ($vmInfo as $info) {
            //插入数据库
            $sql = "insert vm_machine_list (task_uuid, vcenter_uuid, vm_uuid, vm_name, version, host_uuid, 
                dir_path, task_status) values (?, ?, ?, ?, ?, ?, ?, ?)";
            $sqlParams = array(
                $taskUuid,
                $platformUuid,
                $info['uuid'],
                $info['name'],
                $info['version'],
                $info['host_uuid'],
                $info['dir_path'],
                xphp_get_config('vm', 'VmTaskStatus')['NEW_ADD']
            );
            $result = $this->dbQuery($sql, $sqlParams);

            //插入vm_object_list
            $sql2 = "insert vm_object_list (task_uuid, object_uuid, vcenter_uuid, type, exclude_vm_uuid_list, object_name, vm_config, dir_path) 
            values (?, ?, ?, ?, ?, ?, ?, ?)";
            $sql2Params = array(
                $taskUuid,
                $info['uuid'],
                $platformUuid,
                xphp_get_config('vm', 'VM_TREE_TYPE')['VM'],
                '',
                $info['name'],
                json_encode(['disk_list' => []], JSON_UNESCAPED_UNICODE),
                $info['dir_path']
            );
            $result = $result && $this->dbQuery($sql2, $sql2Params);

            // 有GFS策略，则插入bd_gfs_entity_waiting_map
            if ($gfsFlag) {
                foreach ($level1Types as $level1type) {
                    $sqlAdd = "insert into bd_gfs_entity_waiting_map(task_uuid, entity_uuid, level1_type, waiting_flag) 
                        values(?,?,?,?)";
                    $sqlAddParams = array($taskUuid, $info['uuid'], $level1type, 2);
                    $result = $result && $this->dbQuery($sqlAdd, $sqlAddParams);
                }
            }
        }
        if (!!$result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }
        return $this->muOpResult(!!$result, $operate);
    }

    //todo:public方法

    /**
     * 创建和修改备份任务做检测
     * @param string $operate  操作描述
     * @param array  $vmInfo   本次备份的虚拟机
     * @param string $taskUuid 任务UUID(修改的时候用)
     * @return bool
     */
    public function checkTaskLegal(string $operate, array $vmInfo, string $taskUuid): bool
    {
        $vmCount = $this->getAllTaskCount($vmInfo, $taskUuid);
        //这里暂时只检测在按虚拟机授权的时候虚拟机个数是否足够,不足够直接退出并提示
        $systemLisenceInfo = SystemHandler::instance()->getSystemLisenceInfo();
        $systemLisenceInfo = json_decode($systemLisenceInfo, true);

        if ($systemLisenceInfo['status'] == xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            //已授权
            //如果是按照虚拟机授权,检测虚拟机个数是否超出限制
            if ($systemLisenceInfo['vminfo']['type'] == xphp_get_config('auth', 'LISENCE_INFO')['type']['vm']) {
                $totalCount = $systemLisenceInfo['vminfo']['total'];
                $validCount = $systemLisenceInfo['vminfo']['valid'];
                if ($totalCount > 0 && $vmCount > $validCount) {
                    exit($this->muOpResult(false, $operate, xphp_get_lang('WEB_VM_LISENCE_USED_ALL'), 'error'));
                }
            }
        } else {
            //未授权
            exit($this->muOpResult(false, $operate, $systemLisenceInfo['statusDes'], 'error'));
        }
        return true;
    }

    /**
     * 启动任务
     * @param string $taskUuid  任务uuid
     * @param int    $startType 启动类型
     * @return array
     */
    public function startJob(string $taskUuid, int $startType)
    {
        // 进行启动任务的逻辑
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);

        // 有2种启动任务类型 备份：启动任务（完备）、增量、差异、策略，恢复：启动任务
        if ($startType == 0) {
            // 启动策略
            $opName = 'BD_TASK_OP_START_TIMESTRATEGY';
            $msg = ['task_uuid' => $taskUuid];
        } else {
            $taskType = xphp_get_config('task', 'TASKTYPE');
            if ($task['task_type'] == $taskType['BACKUP']) {
                // 启动备份任务
                $opName = 'BD_TASK_OP_BACKUP_START';
            } elseif ($taskType['VM_INSTANT_RECOVERY'] == $task['task_type']) {
                //启动瞬时恢复任务
                $opName = 'VM_PRIVATE_TASK_OP_START_INSTANT_RECOVERY_TASK';
            } elseif ($taskType['VM_FILE_RECOVERY'] == $task['task_type']) {
                //启动细粒度任务
                $opName = 'VM_PRIVATE_TASK_OP_START_GRAIN_RECOVERY_TASK';
            } elseif ($taskType['VM_HUAWEI_CBR_SYNC'] == $task['task_type']) {
                //启动华为CBR下云同步任务（任务类型暂定为49）
                $opName = 'BD_TASK_OP_BACKUP_START';
            } elseif ($taskType['SURE_BACKUP'] == $task['task_type']) {
                //启动数据验证任务
                $opName = 'VM_PRIVATE_TASK_OP_START_SURE_BACKUP_TASK';
            } else {
                // 启动恢复任务
                $opName = 'BD_TASK_OP_RECOVERY_START';
            }
            $msg = [
                'task_uuid' => $taskUuid,
                'backup_mode' => $startType,
                'time_strategy_id' => 0,
                'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            ];
        }
        $subModule = $this->getSubModule($task['module_type'], $task['task_type'], $taskUuid);
        return $this->opUnifyMsg($taskUuid, $subModule, $opName, $msg, false, true);
    }

    /**
     * 停止任务
     * @param string $taskUuid 任务uuid
     * @return array
     */
    public function stopJob(string $taskUuid)
    {
        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);

        $taskType = xphp_get_config('task', 'TASKTYPE');
        if ($task['task_type'] == $taskType['BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        } elseif ($taskType['VM_INSTANT_RECOVERY'] == $task['task_type']) {
            //瞬时恢复任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_INSTANT_RECOVERY_TASK';
        } elseif ($taskType['VM_FILE_RECOVERY'] == $task['task_type']) {
            //细粒度任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_GRAIN_RECOVERY_TASK';
        } elseif ($taskType['VM_HUAWEI_CBR_SYNC'] == $task['task_type']) {
            //停止下云同步任务
            $opName = 'BD_TASK_OP_BACKUP_STOP';
        } elseif ($taskType['SURE_BACKUP'] == $task['task_type']) {
            //停止数据验证任务
            $opName = 'VM_PRIVATE_TASK_OP_STOP_SURE_BACKUP_TASK';
        } else {
            // 恢复任务
            $opName = 'BD_TASK_OP_RECOVERY_STOP';
        }

        $msg = [
            'task_uuid' => $taskUuid
        ];
        $subModule = $this->getSubModule($task['module_type'], $task['task_type'], $taskUuid);
        return $this->opUnifyMsg($taskUuid, $subModule, $opName, $msg, false, true);
    }

    /**
     * 删除任务
     * @param string $taskUuid 任务uuid
     * @return array
     */
    public function delJob(string $taskUuid)
    {

        // 查询出任务的一些信息
        $task = $this->getTask($taskUuid);

        // 检查任务是否在运行中
        if ($task['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            return $this->muOpResult(
                false,
                xphp_get_lang('WEB_VM_BACKUP_ALREADY_RUNNING'),
                xphp_get_lang('WEB_VM_BACKUP_ALREADY_RUNNING_TIPS'),
                'warning'
            );
        }

        $taskType = xphp_get_config('task', 'TASKTYPE');

        if ($task['task_type'] == $taskType['BACKUP']) {
            // 备份任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        } elseif ($taskType['VM_INSTANT_RECOVERY'] == $task['task_type']) {
            //瞬时恢复任务
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_INSTANT_RECOVERY_TASK';
        } elseif ($taskType['VM_FILE_RECOVERY'] == $task['task_type']) {
            //细粒度任务
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_GRAIN_RECOVERY_TASK';
        } elseif ($taskType['VM_HUAWEI_CBR_SYNC'] == $task['task_type']) {
            //删除下云同步任务
            $opName = 'BD_TASK_OP_BACKUP_DELETE';
        } elseif ($taskType['SURE_BACKUP'] == $task['task_type']) {
            //删除数据验证任务
            $opName = 'VM_PRIVATE_TASK_OP_DELETE_SURE_BACKUP_TASK';
        } else {
            // 恢复任务
            $opName = 'BD_TASK_OP_RECOVERY_DELETE';
        }

        $msg = [
            'task_uuid_list' => [$taskUuid]
        ];
        $subModule = $this->getSubModule($task['module_type'], $task['task_type'], $taskUuid);
        return $this->opUnifyMsg($taskUuid, $subModule, $opName, $msg, false, true);
    }

    /**
     * 选择虚拟机启动备份任务
     * @param array $params
     * @return string
     */
    public function startVMJob(array $params): string
    {
        $mode = $params['mode'];
        $subModule = intval($params['hypervisor_type']);
        $taskuuid = $params['job_uuid'];
        $uuidList = $params['vm_uuids'];
        $subModuleType = VmPlatform::instance()->hypervisorTypeToSubModule($subModule);
        $resourceType = VmPlatform::instance()->getResourceTypeBySubModule($subModuleType);
        $this->checkAuthBySourceUuid(implode(',', $uuidList), $resourceType);
        $this->checkTaskRun($taskuuid);
        $vcenteruuidList = $params['platform_uuids'];
        $vmuuids = array();
        $vcenteruuids = array();
        foreach ($vcenteruuidList as $vcenteruuid) {
            $vcenteruuids[] = array(
                'vcenter_uuid' => $vcenteruuid
            );
        }
        foreach ($uuidList as $uuid) {
            $vmuuids[] = array(
                "vm_uuid" => $uuid
            );
        }
        $opName = 'BD_TASK_OP_BACKUP_START';
        $msg = array(
            'task_uuid' => $taskuuid,
            'backup_mode' => $mode,
            'time_strategy_id' => 0,
            'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            "vm_uuid_list" => $vmuuids,
            "vcenter_uuid_list" => $vcenteruuids
        );
        return $this->opUnifyMsg($taskuuid, $subModule, $opName, $msg, false, true, false);
    }

    /**
     * 选择虚拟机从备份任务标记删除
     * @param array $params
     * @return string
     */
    public function deleteVMFromJob(array $params): string
    {
        $subModule = intval($params['hypervisor_type']);
        $taskuuid = $params['job_uuid'];
        $uuidList = $params['vm_uuids'];
        $subModuleType = VmPlatform::instance()->hypervisorTypeToSubModule($subModule);
        $resourceType = VmPlatform::instance()->getResourceTypeBySubModule($subModuleType);
        $this->checkAuthBySourceUuid(implode(',', $uuidList), $resourceType);
        // 有状态是运行中的虚拟机不允许删除
        $vmUuidStr = "'" . implode("','", $uuidList) . "'";
        $sql = "select count(1) as count from vm_machine_list where task_uuid = ? and vm_uuid in ($vmUuidStr) and task_status = ?";
        $data = $this->dbSelect($sql, [$taskuuid, xphp_get_config('vm', 'VmTaskStatus')['RUNNING']]);
        if ($data[0]['count'] > 0) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_VM_PRIVATE_TASK_OP_EXCLUDE_SELECTED_VMS'), xphp_get_lang('WEB_VM_PRIVATE_TASK_OP_EXCLUDE_SELECTED_VMS_RUNNING_ERROR'), 'warning'));
        }
        $opName = 'VM_PRIVATE_TASK_OP_EXCLUDE_SELECTED_VMS';
        $msg = array(
            'task_uuid' => $taskuuid,
            "vm_uuid_list" => $uuidList,
        );
        return $this->opUnifyMsg($taskuuid, $subModule, $opName, $msg, false, true, false, true);
    }

    //todo:private方法

    /**
     * 虚拟机统一操作
     * @param array  $params 参数
     * @param string $opName 操作码
     * @return string
     */
    private function machineOpUnify(array $params, string $opName): string
    {
        $vmUuid = $params['vm_uuid'];
        $platformUuid = $params['platform_uuid'];
        $sql = "select vm.vm_name, vv.hypervisor_type from vm_machine vm join vm_vcenter vv 
            on vm.vcenter_uuid = vv.vcenter_uuid where vm.vm_uuid = ? and vv.vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vmUuid, $platformUuid));
        $vmName = $data[0]['vm_name'];
        $hypervisor = $data[0]['hypervisor_type'];
        $this->paramsCheck($vmUuid, $vmName, $platformUuid, $hypervisor);

        $msg = json_encode(array('vm_uuid' => $vmUuid, 'vm_name' => $vmName, 'vcenter_uuid' => $platformUuid));
        return (new VmPlatform())->unifyMsg($hypervisor, $opName, $msg);
    }

    /**
     * 得到除当前任务的所有虚拟机个数
     * @param array  $vmInfo   本次备份的虚拟机
     * @param string $taskUuid 任务UUID
     * @return int
     */
    private function getAllTaskCount(array $vmInfo, string $taskUuid): int
    {
        $vmNum = count($vmInfo);
        if (empty($taskUuid)) {
            //如果是新建任务,直接返回本次虚拟机个数
            return $vmNum;
        }
        //如果是修改任务,需要减掉修改前的虚拟个数,才是新增的虚拟机个数
        $sql = "select count(machine_id) as vm_num from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $oldNum = $data[0]['vm_num'];
        return $vmNum - $oldNum;
    }

    /**
     * 统一发送消息到后台
     * @param string $taskUuid  任务uuid
     * @param int    $subModule 子模块编号
     * @param string $opName    操作码
     * @param array  $msg       消息
     * @param bool   $sync      是否同步
     * @param bool   $command   是否命令
     * @param bool   $arrayReturnFlag   是否以数组形式返回
     * @param bool   $vmOpFlag   是否虚拟机模块的操作
     * @return string | array
     */
    private function opUnifyMsg(
        string $taskUuid,
        int $subModule,
        string $opName,
        array $msg,
        bool $sync = false,
        bool $command = false,
        bool $arrayReturnFlag = true,
        bool $vmOpFlag = false
    ) {
        $mbResult = (new Service())->opUnifyMsg($taskUuid, $subModule, $opName, $msg, $sync, $command);
        //返回结果到UI
        if ($arrayReturnFlag) {
            return $this->outMsg($mbResult, $opName);
        } else {
            $result = $mbResult['result'];
            $msg = $mbResult['msg'];
            $operate = $vmOpFlag ? (new VmOpcode())->getOpcodeDes($opName) : (new PfOpcode())->getOpcodeDes($opName);
            if ($result) {
                return $this->muOpResult($result, $operate, $msg);
            } else {
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            }
        }
    }

    /**
     * 检查任务是否在运行状态
     * @param string $taskuuid
     */
    private function checkTaskRun(string $taskuuid)
    {
        $sql = "select task_status from bd_task where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        if ($data[0]['task_status'] == xphp_get_config('task')['TASKSTATUS']['RUNNING']) {
            exit($this->muOpResult(false, xphp_get_lang('WEB_VM_BACKUP_ALREADY_RUNNING'), xphp_get_lang('WEB_VM_BACKUP_ALREADY_RUNNING_TIPS'), 'warning'));
        }
    }
}
