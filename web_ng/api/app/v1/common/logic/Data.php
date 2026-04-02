<?php

namespace app\v1\common\logic;

use app\v1\opcode\OsOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\VmOpcode;

/**
 * note          模块data的基类
 * @author       wanggongxi@vinchin.com
 * @date         2023/7/20 15:29
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Data extends Base
{
    /**
     * 获取任务appliance信息
     * @param string $taskuuid 任务uuid
     * @return string
     */
    protected function getJobAppliance(string $taskuuid)
    {
        $sql = "select ba.ip, ba.nickname from
                               bd_appliance ba, vm_task vt
                where vt.appliance_uuid = ba.appliance_uuid and vt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        return !empty($data) ?
            $data[0]['nickname'] . '(' . $data[0]['ip'] . ')' : xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
    }

    /**
     * 获取恢复全磁盘恢复开启/关闭
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    public function getRecoveryFullDisk(string $taskuuid)
    {
        $sql = "select level from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        return !empty($data) && $data[0]['level'] == xphp_get_config('vm', 'VM_RECOVERY_ZERO')['INCLOUD_ZERO'];
    }

    /**
     * 获取xen版本判断有无静默快照flag
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    public function getXenSnapshotFlag(string $taskuuid)
    {
        $sql = "select distinct vcenter_uuid from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (empty($data)) {
            return false;
        }

        $sql = "select hypervisor_type, version from vm_vcenter where vcenter_uuid = ? ";
        $data = $this->dbSelect($sql, array($data[0]['vcenter_uuid']));

        if (!empty($data)) {
            $vmhypervisonrtype = xphp_get_config('vm', 'VMHYPERVISORTYPE');
            if (
                in_array(
                    intval($data[0]['hypervisor_type']),
                    [
                        $vmhypervisonrtype['VM_HYPERVISOR_TYPE_XENSERVER'],
                        $vmhypervisonrtype['VM_HYPERVISOR_TYPE_XCP_NG']
                    ]
                ) && intval(substr($data[0]['version'], 0, 1)) >= 8
            ) {
                $flag = true;
            }
        }
        return $flag ?? false;
    }

    /**
     * 任务详情-概览: 文件是否配置通配符
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    protected function getWildCardInfo(string $taskuuid)
    {
        $sql = "select detail from bd_task_agent_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        if (empty($data)) {
            return false;
        }
        $count = 0;
        foreach ($data as $d) {
            $count += intval(json_decode($d['detail'], true)['wildcard_mode']);
        }

        //大于0则配置了通配符
        return $count > 0;
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid 任务uuid
     * @return boolean
     */
    protected function getNodeNetworkFlag(string $taskuuid)
    {
        $sql = "select ba.net_model, bts.network_uuid from
                                           bd_transport_strategy bts, bd_agent ba, bd_task_agent_list btal
                where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = bts.task_uuid and btal.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;  //定义传输网络标志
        foreach ($data as $d) {
            if (intval($d['net_model']) == 2 && !empty($d['network_uuid'])) {
                $flag = true;
            }
        }

        return $flag;
    }

    /**
     * 1、代理没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、代理已删除，显示时间点中的主机名+ip
     * @param string $agentuuid 时间点中的uuid
     * @param string $fbtname   时间点中的主机名
     * @param string $ip        时间点中的ip
     * @return string name(ip)
     */
    protected function getAgentNameStr(string $agentuuid, $fbtname, $ip)
    {
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql, array($agentuuid));
        if (!empty($result)) {
            return ($result[0]['agent_name'] == $ip ? $result[0]['hostname'] : $result[0]['agent_name']) .
                '(' . $ip . ')';
        } else {
            return $fbtname . '(' . $ip . ')';
        }
    }

    /**
     * 1、nas设备没删，如果有别名就显示别名,没有别名就显示share_path,如果别名和IP一样则显示share_path
     * 2、nas设备已删除，显示时间点中的主机名+ip
     * @param string $nasuuid $nasuuid
     * @param string $fbtname 时间点中的主机名
     * @param string $ip      时间点中的主机名
     * @return name(ip)
     */
    protected function getNasNameStr(string $nasuuid, $fbtname, $ip)
    {
        $sql = "select nas_nickname, share_path from nas_storage_resource where nas_uuid = ?";
        $result = $this->dbSelect($sql, array($nasuuid));
        if (!empty($result)) {
            return $ip . '(' .
                ($result[0]['nas_nickname'] == $ip ? $result[0]['share_path'] : $result[0]['nas_nickname']) .  ')';
        } else {
            return $ip . '(' . $fbtname . ')';
        }
    }

    /**
     * 根据任务状态计算进度
     * @param string $taskUUID    任务uuid
     * @param string $agentUUID   客户端uuid
     * @param int    $taskStatus  任务状态
     * @param int    $agentStatus 客户端状态
     * @return string
     */
    protected function getFsAgentProgress(string $taskUUID, string $agentUUID, $taskStatus, $agentStatus)
    {
        $sql = "select current_object_total_size, current_object_completed_size
                from fs_running_info where task_uuid = ? and agent_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID,$agentUUID));
        $taskStatusArr = xphp_get_config('task', 'TASKSTATUS');
        if (!in_array($taskStatus, [$taskStatusArr['RUNNING'], $taskStatusArr['PAUSED'], $taskStatusArr['ABNORMAL']])) {
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
        }

        //备份任务只有目录时，文件大小为0  导致进度计算出来一直是0
        if ($agentStatus == 4) {
            return '100.00%';
        }

        $speed = v1_calpercent($data[0]['current_object_total_size'], $data[0]['current_object_completed_size']);
        //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
        return sprintf('%.2f', substr($speed, 0, -1)) . '%';
    }

    /**
     * 获取操作名
     * @param string $opCode 操作码
     * @return string
     */
    protected function getUnifyOpcodeDes(string $opCode)
    {
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opCode);
        if ($operate == xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')) {
            $pfOpcode = new VmOpcode();
            $operate = $pfOpcode->getOpcodeDes($opCode);
        }
        if ($operate == xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')) {
            $pfOpcode = new OsOpcode();
            $operate = $pfOpcode->getOpcodeDes($opCode);
        }
        return $operate;
    }

    /**
     * 检查备份点的权限
     * @param $pointUUID
     * @return void
     */
    protected function checkTimePointPermission($pointUUID)
    {
        $sql = "SELECT bbt.module_type, bbt.user_uuid FROM bd_backup_timepoint bbt WHERE bbt.timepoint_uuid = ? ";
        $pointData = $this->dbSelect($sql, [$pointUUID]);
        if ($pointData) {
            // 做个模块映射
            $array = [
                xphp_get_config('module', 'MODULE_TYPE')['DB'] => 'db_protect_operate', // 数据库模块 - 主机保护
                xphp_get_config('module', 'MODULE_TYPE')['OS'] => 'os_protect_operate', // 操作系统模块 - 主机保护
                xphp_get_config('module', 'MODULE_TYPE')['VM'] => 'vmprotect_operate', // 虚拟机保护
                xphp_get_config('module', 'MODULE_TYPE')['NAS'] => 'fileprotect_operate', // 文件保护
                xphp_get_config('module', 'MODULE_TYPE')['FS'] => 'nas_protect_operate', // nas保护
            ];

            if (!empty($array[$pointData[0]['module_type']])) {
                $checkOperate = xphp_check_operate(xphp_get_user_info()['userUuid'], [$pointData[0]['user_uuid']]);
                if (!$checkOperate) {
                    // 没权限操作
                    exit($this->muOpResult(false, xphp_get_lang('UI_ROLE_PERMISSION'), xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), 'warning'));
                }
            }
        }
    }
}
