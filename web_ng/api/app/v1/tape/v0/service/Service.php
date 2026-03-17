<?php

namespace app\v1\tape\v0\service;

use app\v1\common\service\Base;

/**
 * note     磁带 服务通信 service
 */
class Service extends Base
{
    /**
     * 扫描磁带库
     * @return string 操作
     */
    public function scanTapeLibService( string $tapeLibName, $node) 
    {
        $opName = 'NODE_TAPE_OP_SCAN_TAPE_LIB';
        $msg = [
            'tape_lib_name' => $tapeLibName
        ];
        return $this->mbNodeMsg($opName, $node, json_encode($msg), true);
    }

    // /**
    //  * 自定义发送磁带消息
    //  * @return mixed
    //  */
    // private function mbTapeMsg(string $opName, string $nodeuuid, $msg, $sync = true, $command = false, $submodule_type = 0)
    // {
    //     $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg), $sync, $command, $submodule_type);
    // }

    /**
     * 添加磁带组
     */
    public function addTapeGroupService(
        array $tapeGroupProperties,
        array $tapeSerialNumberSet,
        string $useMode,
        array $warningSettings,
        string $nodeUuid,
        $delayDays =0,
        $delaySpaceThreshold =0

    ) {
        $opName = 'NODE_TAPE_OP_CREATE_TAPE_GROUP';
        $tapeGroupProperties['delay_days'] = $delayDays;
        $tapeGroupProperties['delay_space_threshold'] = $delaySpaceThreshold;

        $msg = [
            'tape_group_properties' => $tapeGroupProperties,
            'tape_serial_number_set' => $tapeSerialNumberSet,
            'use_mode' => intval($useMode),
            'warning_flag'=> v1_parse_bool_to_flag($warningSettings['power']),
            'warning_type'=> intval($warningSettings['type']),
            'delay_days'=>$delayDays,
            'delay_space_threshold'=>$delaySpaceThreshold,

        ];
        if(xphp_get_config('resource', 'STORAGEWARNINGTYPE')['SIZE'] == $warningSettings['warning_type']){
            //如果是按照大小来告警
            $msg['warning_value'] = intval($warningSettings['value']) * 1024 * 1024 * 1024;
        }else{
            $msg['warning_value'] = intval($warningSettings['value']);
        }

        return $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg));
    }

    /**
     * 修改磁带组
     */
    public function modifyTapeGroupService(
        array $tapeGroupProperties,
        array $addTapeSerialNumberSet,
        array $removeTapeSerialNumberSet,
        string $useMode,
        array $warningSettings,
        $delayDays =0,
        $delaySpaceThreshold =0


    ) {
        $opName = 'NODE_TAPE_OP_MODIFY_TAPE_GROUP';
        $tapeGroupProperties['delay_days'] = $delayDays;
        $tapeGroupProperties['delay_space_threshold'] = $delaySpaceThreshold;

        $msg = [
            'tape_group_properties' => $tapeGroupProperties,
            'add_tape_serial_number_set' => $addTapeSerialNumberSet,
            'remove_tape_serial_number_set' => $removeTapeSerialNumberSet,
            'use_mode' => intval($useMode),
            'warning_flag'=> v1_parse_bool_to_flag($warningSettings['power']),
            'warning_type'=> intval($warningSettings['type']),
            'delay_days'=>$delayDays,
            'delay_space_threshold'=>$delaySpaceThreshold,

        ];
        if(xphp_get_config('resource', 'STORAGEWARNINGTYPE')['SIZE'] == $warningSettings['warning_type']){
            //如果是按照大小来告警
            $msg['warning_value'] = intval($warningSettings['value']) * 1024 * 1024 * 1024;
        }else{
            $msg['warning_value'] = intval($warningSettings['value']);
        }
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 删除磁带组
     */
    public function delTapeGroupService(
        string $groupUuid
    ) {
        $opName = 'NODE_TAPE_OP_DELETE_TAPE_GROUP';
        $msg = [
            'group_uuid' => $groupUuid,
        ];
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     * 删除备份集
     */
    public function delBackupSetService(array $backupSetUuidList): array
    {
        $opName = 'NODE_TAPE_OP_DELETE_BACKUP_SET';
        $msg = [
            'backupset_uuid_list' => $backupSetUuidList['backupset_uuid_list'],
        ];
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     *  导入磁带
     */
    public function importTapeService(string $tapeLibName, string $tapeSerialNumber)
    {
        $opName = 'NODE_TAPE_OP_IMPORT_TAPE_CARRIAGE';
        $opType = 1;
        $msg = [
            'op_type' => $opType,
            'tape_lib_name' => $tapeLibName,
            'tape_serial_number' => $tapeSerialNumber
        ];
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     *  弹出磁带
     */
    public function exportTapeService(string $tapeLibName, string $tapeSerialNumber)
    {
        $opName = 'NODE_TAPE_OP_EXPORT_TAPE_CARRIAGE';
        $opType = 2;
        $msg = [
            'op_type' => $opType,
            'tape_lib_name' => $tapeLibName,
            'tape_serial_number' => $tapeSerialNumber
        ];
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     *  导入磁带组
     */
    public function importTapeGroupService(array $tapeGroupProperties, string $useMode, array $warningSettings, $node)
    {
        $opName = 'NODE_TAPE_OP_IMPORT_TAPE_GROUP';
        $msg = [
            'tape_group_properties' => $tapeGroupProperties,
            'use_mode' => intval($useMode),
            'warning_flag'=> v1_parse_bool_to_flag($warningSettings['power']),
            'warning_type'=> intval($warningSettings['type']),
        ];
        if(xphp_get_config('resource', 'STORAGEWARNINGTYPE')['SIZE'] == $warningSettings['warning_type']){
            //如果是按照大小来告警
            $msg['warning_value'] = intval($warningSettings['value']) * 1024 * 1024 * 1024;
        }else{
            $msg['warning_value'] = intval($warningSettings['value']);
        }
        return $this->mbNodeMsg($opName, $node, json_encode($msg));
    }
    
    /**
     *  检索磁带
     */
    public function retrievalTapeService(string $tapeLibName, string $tapeSerialNumber)
    {
        $opName = 'NODE_TAPE_OP_RETRIEVE_TAPE_DATA';
        $msg = [
            'tape_lib_name' => $tapeLibName,
            'tape_serial_number' => $tapeSerialNumber
        ];
        return $this->mbNodeMsg($opName, $this->getLocalNodeUuid(), json_encode($msg));
    }

    /**
     *  冻结/解冻 磁带
     */
    public function operateTapeService(string $opName, string $msg, string $nodeUuid = '')
    {

        if (empty($nodeUuid)) {
            $nodeUuid = $this->getLocalNodeUuid();
        }
        return $this->mbNodeMsg($opName, $nodeUuid, $msg);
    }
    // /**
    //  * 统一发送消息到后台
    //  * @param string $tape_lib_name  磁带库name
    //  * @param int    $subModule 子模块编号
    //  * @param string $opName    操作码
    //  * @param array  $msg       消息
    //  * @param bool   $sync      是否同步
    //  * @param bool   $command   是否命令
    //  * @return array
    //  */
    // public function opUnifyMsg(
    //     string $tape_lib_name,
    //     string $opName,
    //     array $msg,
    //     $sync = false,
    //     $command = false
    // ) {
    //     return $this->mbTapeMsg($this->getLocalNodeUuid(), $opName, json_encode($msg), $sync, $command);
    // }
}