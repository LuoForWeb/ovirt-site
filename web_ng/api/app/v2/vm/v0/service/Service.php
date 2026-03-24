<?php

namespace app\v2\vm\v0\service;

use app\v2\common\service\Base;
use app\v2\opcode\PfOpcode;
use app\v2\resources\v0\logic\Node;

/**
 * 虚拟机模块发后台消息
 * Class Service
 * @package app\v2\vm\v0\service
 */
class Service extends Base
{
    /**
     * 虚拟化平台统一操作：添加、修改、删除、获取
     * @param string $nodeUuid       节点UUID
     * @param int    $hypervisorType 虚拟化类型
     * @param string $opName         操作码
     * @param string $msg            参数json
     * @param bool   $sync            是否同步消息
     * @return array|\xphp\db\Ambigous
     */
    public function unifyVmPlatformService(string $nodeUuid, int $hypervisorType, string $opName, string $msg, bool $sync = false): array
    {
        return $this->mbVMMsg($nodeUuid, $hypervisorType, $opName, $msg, $sync);
    }

    /**
     * 刷新虚拟化平台
     * @param string $nodeUuid      节点UUID
     * @param int    $submoduleType 虚拟化类型
     * @param string $platformUuid  虚拟化平台UUID
     * @param int    $displayMode   显示模式
     * @param bool   $command       是否命令模式
     * @param bool   $newInstance   是否重新实例化
     * @return array|\xphp\db\Ambigous
     */
    public function refreshVmPlatformService(
        string $nodeUuid,
        int $submoduleType,
        string $platformUuid,
        int $displayMode,
        bool $command,
        bool $newInstance
    ): array {
        $msg = json_encode(array('vcenter_uuid' => $platformUuid, 'display_mode' => $displayMode));
        return $this->mbVMMsg($nodeUuid, $submoduleType, 'VM_VCENTER_OP_REFLASH', $msg, false, $command, $newInstance);
    }

    /**
     * 获取虚拟机磁盘列表
     * @param string $nodeUuid     节点UUID
     * @param int    $hypervisor   虚拟化类型
     * @param string $platformUuid 虚拟化平台UUID
     * @param string $vmUuid       虚拟机（实例）UUID
     * @return array|\xphp\db\Ambigous
     */
    public function getVmDiskListService(string $nodeUuid, int $hypervisor, string $platformUuid, string $vmUuid): array
    {
        $msg = json_encode(array('vcenter_uuid' => $platformUuid, 'vm_uuid' => $vmUuid));
        return $this->mbVMMsg($nodeUuid, $hypervisor, 'VM_VCENTER_OP_GET_VM_DISK_LIST', $msg, true);
    }

    /**
     * 统一发送消息到后台  -- 任务相关
     * @param string $taskUuid  任务uuid
     * @param int    $subModule 子模块编号
     * @param string $opName    操作码
     * @param array  $msg       消息
     * @param bool   $sync      是否同步
     * @param bool   $command   是否命令
     * @return array|\xphp\db\Ambigous
     */
    public function opUnifyMsg(
        string $taskUuid,
        int $subModule,
        string $opName,
        array $msg,
        $sync = false,
        $command = false
    ): array {

        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid

        return $this->mbVMMsg($nodeuuid, $subModule, $opName, json_encode($msg), $sync, $command);
    }

    /**
     * 统一发送平台消息到后台
     * @param string $opName  操作码
     * @param string $msg     消息json
     * @return array
     */
    public function opUnifyPfMsg(string $opName, string $msg): array
    {
        $mbResult = $this->mbPFMsg($opName, $msg);
        $operate = (new PFOpcode())->getOpcodeDes($opName);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
}
