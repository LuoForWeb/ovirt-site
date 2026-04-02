<?php
/******************************************* 
** 备份数据管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-06-03 下午15:16:44 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
class DataHandler extends OPHandler{
    /**
     * 给备份时间点添加星标
     * 暂时支持一个
     * @param unknown $params
     */
    public function addStar($params){
        //权限检查
//        $roleHandler = Xphp::instance('RoleHandler');
//        $roleHandler->pOperationPermissionCheckExit("p_vmdata_star");
        $pointUUID = $params['uuid'];
        $this->paramsCheck($pointUUID);
        $this->checkTimePointPermission($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_MAKR';
        $msg = json_encode(array('timepoint_uuids'=> $msg));
        return $this->opUnifyMsg($opName, $msg);
    }

    /**
     * 检查备份点的权限
     * @param $pointUUID
     * @return void
     */
    private function checkTimePointPermission($pointUUID)
    {
        $sql = "SELECT bbt.module_type, bbt.user_uuid FROM bd_backup_timepoint bbt WHERE bbt.timepoint_uuid = ? ";
        $pointData = $this->dbSelect($sql, [$pointUUID]);
        if ($pointData) {
            // 做个模块映射
            $array = [
                Xphp::$_config['MODULE_TYPE']['DB'] => 'db_protect_operate', // 数据库模块 - 主机保护
                Xphp::$_config['MODULE_TYPE']['OS'] => 'os_protect_operate', // 操作系统模块 - 主机保护
                Xphp::$_config['MODULE_TYPE']['VM'] => 'vmprotect_operate', // 虚拟机保护
                Xphp::$_config['MODULE_TYPE']['NAS'] => 'fileprotect_operate', // 文件保护
                Xphp::$_config['MODULE_TYPE']['FS'] => 'nas_protect_operate', // nas保护
            ];

            if (!empty($array[$pointData[0]['module_type']])) {
                $operate = $array[$pointData[0]['module_type']];
                $utils = Xphp::instance("Utils");
                $authUser = $_SESSION['authUser'][$operate] ?? [];
                $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], [$pointData[0]['user_uuid']], $authUser);
                if (!$checkOperate) {
                    // 没权限操作
                    exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
                }
            }
        }
    }
    
    /**
     * 备份时间点取消星标
     * 暂时支持一个
     * @param unknown $params
     */
    public function deleteStar($params){
        //权限检查
//        $roleHandler = Xphp::instance('RoleHandler');
//        $roleHandler->pOperationPermissionCheckExit("p_vmdata_star");
        
        $pointUUID = $params['uuid'];
        $this->paramsCheck($pointUUID);
        $this->checkTimePointPermission($pointUUID);
        $msg = array($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_UNMARK';
        $msg = json_encode(array('timepoint_uuids'=> $msg));
        return $this->opUnifyMsg($opName, $msg);
    }
    
    public function remarkTimepoint($params){
        $pointUUID = $params['uuid'];
        //权限检查
        $this->checkTimepointOperateAuth([$pointUUID]);

        $utils = Xphp::instance('Utils');
        $remark = $utils->removeEscape($params['remark']);
     	$this->paramsCheck($pointUUID);
        $this->checkTimePointPermission($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_COMMENT';
        
        $msg = json_encode(array('timepoint_uuid'=> $pointUUID, 'remarks' => $remark));
        return $this->opUnifyMsg($opName, $msg);
    }
    /**
     * 添加取消GFS标记
     * 支持一个时间点多个标记添加或取消
     * @param unknown $params
     * @return array
     */
    public function setGFSMark($params){
        $this->checkTimepointOperateAuth([$params['timepoint_uuid']]);
        $timepointUUID = $params['timepoint_uuid'];
        $item_list = $params['item_list'];
        $nodeuuid = $params['nodeuuid'];
        $submodule_type = $params['submode_type'];
        $info = array(
            'timepoint_uuid' => $timepointUUID,
            'item_list' => $item_list,
        );
        $opName = 'BD_BACKUP_POINT_OP_GFS_FLAG_OP';
        $msg = json_encode($info);
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $result = $mbResult['result'];
        $msg1 = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg1);
        }else{
            return $this->muOpResult($result, $operate, $msg1, '', $mbResult['errorCode']);
        }
    }
    
    
    
    
    /**
     * 统一处理消息
     * @param string $opName    操作名
     * @param json $msg         JSON消息
     * @return array
     */
    private function opUnifyMsg($opName, $msg){
        $mbResult = $this->mbPFMsg($opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    private function writePointLog($result, $data, $opName, $mbResult, $msg){
        $key = "";
        $params = array();
        if($opName == 'BD_BACKUP_POINT_OP_COMMENT'){
            $key = 'SYSTEM_LOG_TIMEPOINT_REMARK';
            $params = array($data[0]['timepoint'], $msg['remarks']);
        }else if($opName == 'BD_BACKUP_POINT_OP_MAKR'){
            $key = 'SYSTEM_LOG_TIMEPOINT_STAR';
            $params = array($data[0]['timepoint']);
        }else if($opName == 'BD_BACKUP_POINT_OP_UNMARK'){
            $key = 'SYSTEM_LOG_TIMEPOINT_UNSTAR';
            $params = array($data[0]['timepoint']);
        }
        
        if($result){
            $this->systemLog($key, $params);
        }else{
            $this->systemLog($key, $params,  Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
        }
    }

    public function checkTimepointOperateAuth(array $timepoint_uuids)
    {
        if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' == Xphp::$_user['useruuid']) return;
        $sql = "select user_uuid from bd_backup_timepoint where timepoint_uuid in ('" . implode("','", $timepoint_uuids) . "')";
        $chk_list = (array)$this->dbSelect($sql);
        // 操作权限
        $authUser = $_SESSION['authUser']['vmprotect_operate'] ?? [];
        $checkOperate = Xphp::instance('Utils')->xphp_check_operate(Xphp::$_user['useruuid'], array_column($chk_list, 'user_uuid'), $authUser);
        if (!$checkOperate) {
            // 没有操作权限
            exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
        }
    }
}
?>