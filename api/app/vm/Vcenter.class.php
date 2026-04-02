<?php
/******************************************* 
** VCenter管理类
** 
** @author       xiezhuowei@vinchin.com
** @date         2015-1-13 下午11:20:41 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
require_once XPHP_PATH.'utils/OPHandler.class.php';

class Vcenter extends OPHandler{
    private $opcodeHandler;
    
    function __construct(){
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('VMOpcode');
    }
    /**
     * 注册
     * @param unknown $params
     */
    public function register($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_add");
        $vmtype = $params['vmtype'];
        $ip = $params['ip'];
        $username = $params['username'];
        $password = $params['password'];
        $rname = $params['rname'];
        $detail = $params['detail'];
        if(isset($detail['backup_time'])){
            $time = strtotime($detail['backup_time']);
            $detail['backup_time'] = date('H:i:s', $time);
        }
        
        //针对深信服6.8以后国际化版本处理增加系统语言参数 
        if($vmtype == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']){
            if(is_string($detail)){
                $detail = array();
            }
            $detail['language'] = Xphp::$_config['lang'];
        }
        $detailArr = $detail;
        if(!empty($detail)){
            $detail = json_encode($detail);
        }
        //检查参数
        if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $vmtype || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $vmtype) {
            $this->paramsCheck($vmtype, $ip);
            if (!($username && $password) && !($detailArr['access_key_id'] && $detailArr['access_key_secret'])) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['UI_VCENTER_ICS_ADD_TIPS'], 'warning'));
            }
        } else {
            $this->paramsCheck($vmtype, $ip, $username, $password);
        }
        //ICS AccessKey
        if (!$username || !$password) {
            $username = $detailArr['access_key_id'];
            $password = $detailArr['access_key_secret'];
        }
        //ipv6处理
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ip = '[' . $ip . ']';
        }
        //得到子模块号
        $submodule_type = intval($vmtype);
        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_ADD';
        //组合消息
        $msg = array(
            'hypervisor_type' => $submodule_type,
            'ip' => $ip,
            'username' => str_replace("\\\\", "\\", $username),
            'password' => $password,
            'nickname' => htmlspecialchars_decode($rname),
            'display_mode' => Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
            'detail' => $detail
        );
        $jsonmsg = json_encode($msg);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getvCenterUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, intval($submodule_type), $opcodeName, $jsonmsg);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
//         $descriptionParam = array($rname, $ip);
        //返回结果到UI
        if($result){
//             $this->systemLog('VM_SYSTEMLOG_DESC_KEY_ADD_VCENTER_SUCCESS', $descriptionParam);
        	if(in_array($submodule_type, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){
        		return $this->muOpResult($result, $operate, $msg);
        	}
        	//调用自动刷新虚拟化中心
            $this->refreshVcenterDefault($submodule_type, $ip);
            return $this->muOpResult($result, $operate, $msg);
        }else{
//             $this->systemLog('VM_SYSTEMLOG_DESC_KEY_ADD_VCENTER_SUCCESS', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        
    }
    
    /**
     * 添加虚拟化中心成功后自动刷新另外两种模式
     * @param int $submoduleType
     * @param string $vcenterip
     * @return boolean
     */
    private function refreshVcenterDefault($submoduleType, $vcenterip){
        $sql = "select vcenter_uuid from vm_vcenter where vcenter_ip = ?";
        $data = $this->dbSelect($sql, array($vcenterip));
        if(empty($data)) return true;
        $vcenteruuid = $data[0]['vcenter_uuid'];
        $this->refleshVcenter($submoduleType, $vcenteruuid, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_VM'], TRUE, TRUE);
        $this->refleshVcenter($submoduleType, $vcenteruuid, Xphp::$_config['VM_TREE_DISPLAY_MODE']['VM_AND_TEMPLATE'], TRUE, TRUE);
        return true;
    }
    
    /**
     * 修改
     * @param unknown $params
     */
    public function modify($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_edit");
        $uuid = $params['uuid'];
        $vmtype = intval($params['vmtype']);
        $ip = $params['ip'];
        $username = $params['username'];
        $password = $params['password'];
        $rname = $params['rname'];
        $detail = $params['detail'];
        if($detail['backup_time']){
            $time = strtotime($detail['backup_time']);
            $detail['backup_time'] = date('H:i:s', $time);
        }
        
        //针对深信服6.8以后国际化版本处理增加系统语言参数
        if($vmtype == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']){
            if(is_string($detail)){
                $detail = array();
            }
            $detail['language'] = Xphp::$_config['lang'];
        }

        $detailJson = '';
        if(!empty($detail)){
            $detailJson = json_encode($detail);
        }
        //检查参数
        if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $vmtype || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $vmtype) {
            $this->paramsCheck($uuid, $vmtype, $ip, $rname);
            if (!($username && $password) && !($detail['access_key_id'] && $detail['access_key_secret'])) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['UI_VCENTER_ICS_ADD_TIPS'], 'warning'));
            }
        } else {
            $this->paramsCheck($uuid, $vmtype, $ip, $username, $password, $rname);
        }
        
        //检查用户是否修改了密码,如果修改了用新的密码,如果没有修改用原来的密码(数据库里面的)
        $sql = "select user_uuid,password from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));

        // 关联管理用户判断 存储资源 - 操作
        $utils = Xphp::instance("Utils");
        $authUser = $_SESSION['authUser']['resmanagement_operate'] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], array_column($data, 'user_uuid'), $authUser);
        if (!$checkOperate) {
            // 没权限操作
            exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
        }

        $oldPass = base64_encode(md5($data[0]['password']));
        if($oldPass == $password){
            //没有修改密码
            $utils = Xphp::instance('Utils');
            $password = base64_encode($utils->ptPassDecrypt($data[0]['password']));
        }
        if ($detail['access_key_id'] && $detail['access_key_secret'] && !$username && !$params['password']) {
            $username = $detail['access_key_id'];
            $password = $detail['access_key_secret'];
        }

        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_MODIFY';
        //组合消息
        $msg = array(
            'vcenter_uuid' => $uuid,
            'hypervisor_type' => $vmtype,
            'ip' => $ip,
            'username' => str_replace("\\\\", "\\", $username),
            'password' => $password,
            'nickname' => htmlspecialchars_decode($rname),
            'display_mode' => Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_VM'],
            'detail' => $detailJson
        );
        $jsonmsg = json_encode($msg);
        return $this->unifyMsg($vmtype, $opcodeName, $jsonmsg);
    }
    
    /**
     * 获取要修改的虚拟化中心信息
     * @param unknown $params
     */
    public function getModifyVcenterInfo($params){
        $uuid = $params['uuid'];
        $this->paramsCheck($uuid);
        $sql = "select hypervisor_type, vcenter_ip, vcenter_uuid, username, password, nickname, detail from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $arr = array();
        if($data){
            $arr = array(
                'hypervisor' => $data[0]['hypervisor_type'],
                'ip' => $data[0]['vcenter_ip'],
                'username' => $data[0]['username'],
                'pass' => md5($data[0]['password']),
                'rname' => htmlspecialchars_decode($data[0]['nickname']),
                'uuid' => $data[0]['vcenter_uuid'],
            	'detail' => $data[0]['detail']
            );
        }
        return json_encode($arr);
    }
    
    /**
     * 删除
     * @param unknown $params
     */
    public function deleteVcenter($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_delete");
        $vcenter = $params['vcenter'];
        $this->paramsCheck($vcenter);
        $vcenterUUIDArray = array();
        foreach ($vcenter as $v){
            $vcenterUUIDArray[] = $v;
        }
        $this->deleteVcenterCheck($vcenterUUIDArray);
        $submodule_type = $this->getVcenterSubModule($vcenter[0]);
        $opName = 'VM_VCENTER_OP_DELETE';
        $msg = array('vcenter_uuid_list' => $vcenterUUIDArray);
        
        //取消备份节点关联关系
        $sql = "delete from mt_platform_node where platform_uuid = ? and type = ? ";
        $result = $this->dbExec($sql, array($vcenter[0], 1));
        
        
        return $this->unifyMsg($submodule_type, $opName, json_encode($msg));
    }
    
    /**
     * 删除虚拟化中心检查
     * @param array $vcenters   虚拟化中心ID数组
     */
    private function deleteVcenterCheck($vcenters){
        $vcenterStr = "";
        foreach ($vcenters as $vcenter){
            $vcenterStr .= $vcenter . " ,"; 
        }
        if($vcenterStr){
            $vcenterStr = substr($vcenterStr, 0, -1);
        }

        if (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
            // 超级管理查看所有的资源
        } elseif ($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['sysadmin']) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        } else {
            $sqlcheck = "select user_uuid from vm_vcenter where vcenter_uuid in ( ? )";
            $data = $this->dbSelect($sqlcheck, [$vcenterStr]);
            // 关联管理用户判断 存储资源 - 操作 resmanagement_operate
            $utils = Xphp::instance("Utils");
            $authUser = $_SESSION['authUser']['resmanagement_operate'] ?? [];
            $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], array_column($data, 'user_uuid'), $authUser);
            if (!$checkOperate) {
                // 没权限操作
                exit($this->muOpResult(false, Xphp::$_lang['UI_ROLE_PERMISSION'], Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'warning'));
            }
        }

        //检测备份和恢复任务
        $sql = "select vv.nickname, bt.task_name from vm_vcenter vv,vm_machine_list vml, bd_task bt  
                where vv.vcenter_uuid = vml.vcenter_uuid 
                and bt.delete_flag = ? 
                and vml.task_uuid = bt.task_uuid 
                and vml.vcenter_uuid in ( ? ) 
                group by vv.nickname ";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $vcenterStr);
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            //如果虚拟化中心有虚拟机存在于备份或恢复任务中,直接返回
            $msg = Xphp::$_lang['WEB_VM_VCENTER'] . "'" . $data[0]['nickname'] . "'" . 
                    Xphp::$_lang['WEB_VM_VCENTER_DELETE_TIPS'] . "'" . $data[0]['task_name'] . "'";
                    exit($this->muOpResult(false, Xphp::$_lang['WEB_VM_VCENTER_DELETE'], $msg, "warning"));
        }
        //检测瞬时恢复和迁移任务
        $sql = "select vv.nickname, bt.task_name from vm_vcenter vv,vm_instant vi, bd_task bt  
                where vv.vcenter_uuid = vi.target_vcenter_uuid 
                and bt.delete_flag = ? 
                and vi.task_uuid = bt.task_uuid 
                and vi.target_vcenter_uuid in ( ? ) 
                group by vv.nickname ";
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $vcenterStr);
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            //如果虚拟化中心有虚拟机存在于瞬时恢复或迁移任务中,直接返回
            $msg = Xphp::$_lang['WEB_VM_VCENTER'] . "'" . $data[0]['nickname'] . "'" .
                Xphp::$_lang['WEB_VM_VCENTER_DELETE_TIPS'] . "'" . $data[0]['task_name'] . "'";
            exit($this->muOpResult(false, Xphp::$_lang['WEB_VM_VCENTER_DELETE'], $msg, "warning"));
        }
        //检查是否有关联虚拟实验室
        $sql = "select svl.virtual_lab_name, vv.nickname from sr_virtual_lab svl, vm_vcenter vv where svl.vcenter_uuid = vv.vcenter_uuid and vv.vcenter_uuid in ( ? ) ";
        $data = $this->dbSelect($sql, array($vcenterStr));
        if(!empty($data)){
            //如果虚拟化中心有虚拟实验室存在,直接返回
            $msg = Xphp::$_lang['WEB_VM_VCENTER'] . "'" . $data[0]['nickname'] . "'" .
                Xphp::$_lang['WEB_VM_VCENTER_DELETE_VIRTUAL_LAB_EXIST_TIPS'] . "'" . $data[0]['virtual_lab_name'] . "'";
                exit($this->muOpResult(false, Xphp::$_lang['WEB_VM_VCENTER_DELETE'], $msg, "warning"));
        }
        return true;
    }
    
    /**
     * 得到备份树
     * @param unknown $params
     */
    public function getBackupTree($params){
        $hypervisor = $params['type'];
		$taskuuid = $params['taskuuid'];
        $vcenteruuid = $params['vcenteruuid'];
        $this->paramsCheck($hypervisor);
        
        $tree = array();
        //获取用户已分配的虚拟机
        $resourceHandler = Xphp::instance('ResourceHandler');
        $publicCloudFlag = in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']);
        $vmResource = $resourceHandler->pGetUserResourceVM(Xphp::$_user['useruuid'],"tree_id", "desc", $limit = 0, "all", [], $publicCloudFlag);
        $vcenterList = array();
        foreach ($vmResource['data'] as $vm){
            if(!in_array($vm['vcenter_uuid'], $vcenterList)){
                $vcenterList[] = $vm['vcenter_uuid'];
            }
        }
        $vcenteruuids = implode("','", $vcenterList);
        
        $sql = "select vv.vcenter_id, vv.vcenter_uuid, vv.vcenter_ip, vv.nickname, vv.vcenter_name, vv.hypervisor_type, vv.vcenter_flag, vv.detail, vv.username, vv.user_uuid, vh.online_flag
                from vm_vcenter vv, vm_host vh
                where vv.hypervisor_type = ?
                and vv.vcenter_uuid = vh.vcenter_uuid ";
        
        $sql .= "and (vv.user_uuid = ? or vv.vcenter_uuid in ('".$vcenteruuids."'))group by vv.vcenter_uuid ";
        $sqlParams = array($hypervisor, Xphp::$_user['useruuid']);
        
        $data = $this->dbSelect($sql, $sqlParams);
        $vmModifyData = $this->getTaskVmList($taskuuid);
        $vmModifyList = $vmModifyData['list'];
        $chkDisabled = $this->isNotAdmin();
        foreach ($data as $d){
            $checkedFlag = false; 
            $checked = false;
             if(in_array($d['vcenter_uuid'], $vmModifyList)){
                 $checked = true;
             }
            if($d['vcenter_uuid'] == $vcenteruuid ){
                //如果是在同一个虚拟化中心 则可以勾选
                $checkedFlag =  true;
            }
            $name = $this->getVcenterNameInTree($d['vcenter_uuid'], $hypervisor, $d['vcenter_flag'], $d['vcenter_ip'], $d['nickname'], $d['vcenter_name'], $d['detail'], $d['username']);
            $sql = "select vol.exclude_vm_uuid_list from vm_object_list vol where vol.object_uuid = ? and vol.task_uuid = ?";
            $exclude_vm_uuid_list = $this->dbSelect($sql, array($d['vcenter_uuid'], $params['taskuuid']))[0]['exclude_vm_uuid_list'];
            $exclude_vm_uuid_list = $exclude_vm_uuid_list ?? '';
            $exclude_vm_uuid_list = explode(',', trim($exclude_vm_uuid_list, ','));
            
            $node = array(
                "id" => $d['vcenter_uuid'],
                "pId" => 0,
                "name" => $name,
                "title" => $name,
                "open" => false,
                "isParent" => true,
                "uuid" => $d['vcenter_uuid'],
                "sid" => $d['vcenter_id'],
                "nocheck" => !$checkedFlag,
                "hypervisor" => $hypervisor,
                "eventtype" => "",
                "vcflag" => $d['vcenter_flag'],
                "iconSkin" => $this->getHypervisorIcon($hypervisor, $d['vcenter_flag']),
                "type" => 1,
                "vcenterFlag" => $d['vcenter_flag'],
                "path"=>$name,
                "checked" => $checked,
                "vmChecked" => $exclude_vm_uuid_list ?: '',
                'vcenteruuid' => $d['vcenter_uuid'],
                'nosnapshot' => true,
                'chkDisabled' => $chkDisabled,
                'userUuid' => $d['user_uuid']
            );
            $tree[] = $node;
        }
        
        return json_encode($tree);
    }

    /**
     * 得到快速创建备份任务的树
     * @param unknown $params
     */
    public function getBackupTreeSpeed($params){
        $showtype = $params['showtype'];
        $vmuuid = $params['vmuuid'];
        $vcenteruuid = $params['vcenteruuid'];
        $this->paramsCheck($showtype, $vmuuid, $vcenteruuid);
        //获取最顶层虚拟化中心,同备份第一步
        $topNode = $this->getBackupTree($params);
        $utils = Xphp::instance('Utils');
        $topNode = $utils->object_array(json_decode($topNode));
        $sql = "select vcenter_flag, hypervisor_type, user_uuid, vcenter_uuid from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        $syncParams = array(
            'id' => $vcenteruuid,
            'vcflag' => $data[0]['vcenter_flag'],
            'hypervisor' => $data[0]['hypervisor_type'],
            'open' => true,
            'showtype' => $showtype,
            'modifyflag' => false,
            'backupflag' => true,
        );
        $vmuuidArr = explode(",", $vmuuid);
        // 租户模式下，虚拟化中心不属于该用户，但属于分配的资源，备份树为三层：hypervisor - 已分配虚拟机 - vm
        if (!empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] != $data[0]['user_uuid']) {
            return $this->getTenantAllocatedVmTree($data[0]['hypervisor_type'], $data[0]['vcenter_uuid'], '', $vmuuidArr);
        }
        //获取对应vcenter的子树
        $syncVcenter = $this->getBackupSyncVcenter($syncParams);
        $syncVcenter = $utils->object_array(json_decode($syncVcenter));
        if($syncVcenter['re']){
            //获取成功
            $msg = $syncVcenter['msg'];
            foreach ($msg as $key => $each){
                //如果找到对应虚拟机,直接赋值选中
                if(in_array($each['id'], $vmuuidArr)){
                    $msg[$key]['checked'] = true;
                }
            }
            $childArr = $msg;
        }
        //展开虚拟化中心,如果虚拟化中心就是宿主机,修改虚拟化中心的节点ID为宿主机UUID
        foreach ($topNode as $key => $each){
            if($each['id'] == $vcenteruuid){
                //展开虚拟化中心
                $topNode[$key]['open'] = true;
                if($each['vcflag'] == Xphp::$_config['FLAG']['UNSET']){
                    //如果虚拟化中心就是宿主机,修改虚拟化中心的节点ID为宿主机UUID
                    $topNode[$key]['id'] = $this->getVcAndHostUUID($vcenteruuid, $topNode[$key]['hypervisor']);
                    break;
                }
                break;
            }
        }

        if(!empty($childArr)){
            $result = array_merge($topNode, $childArr);
        }

        return json_encode($result);
    }

    /**
     * 得到虚拟化中心的名字
     * 主要是区别XenServer和VMware
     * 以及有别名和没有别名
     * @param string $vcenteruuid
     * @param int $hypervisor
     * @param int $vcenterflag
     * @param string $vcenterip
     * @param string $nickname
     * @param string $vcentetname
     */
    public function getVcenterNameInTree($vcenteruuid, $hypervisor, $vcenterflag, $vcenterip, $nickname, $vcentetname, $detail, $username){
        $detail = json_decode($detail, true);
        if(in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['xenserver'])){
            //如果是XenServer,显示IP+(主节点IP)
            $name = $vcentetname . " (" . Xphp::$_lang['WEB_VM_VCENTER_XENSERVER_MASTER_NODE'] . ":" . $vcenterip . ")";
        }else if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
            $name = $vcentetname . '(' . $username . ')';
        } else if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV'] == $hypervisor && Xphp::$_config['FLAG']['UNSET'] == $vcenterflag) {
            //hyperv单机
//            $name = $vcenterip . "(" . $vcentetname . ")";
            $name = $nickname . "(" . $vcenterip . ")";
        }else{
            $name = $vcenterip == $nickname ? $vcenterip : $nickname . "(" . $vcenterip . ")";
        }
        return $this->getHostIsLisenced($vcenterflag, $vcenteruuid, $name, $hypervisor);
    }
    
    /**
     * 得到宿主机是否是授权状态,没有授权需要添加未授权标志
     * @param int $vcenterflag
     * @param string $vcenteruuid
     * @param string $name
     */
    private function getHostIsLisenced($vcenterflag, $vcenteruuid, $name, $hypervisor){
        if($vcenterflag == Xphp::$_config['FLAG']['SET']){
            return $name;
        }
        if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
            return $name;
        }
        //如果是宿主机,$vcenteruuid 其实就是hostuuid
        $sql = "select authorization_flag from vm_host where host_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        if($data[0]['authorization_flag'] != Xphp::$_config['FLAG']['SET']){
            $name = "(" . Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'] . ")" . $name;
        }
        return $name;
    }
    
    //     /**
    //      * 得到虚拟化中心或宿主机是否断开标志，断开要加上已断开
    //      * @param int $onlineflag
    //      * @param string $name
    //      *
    //      */
    //     private function getVcenterIsOnlined($onlineflag, $name){
    //     	if($onlineflag != Xphp::$_config['FLAG']['SET']){
    //     		$name = $name . '(已断开)';
    //     	}
    //     	return $name;
    //     }
    
    /**
     * 得到修改备份任务树
     * @param unknown $params
     */
    public function getBackupTreeOldInfo($params){
        //获取最顶层虚拟化中心,同备份第一步
        $topNode = $this->getBackupTree($params);
        $utils = Xphp::instance('Utils');
        $topNode = $utils->object_array(json_decode($topNode));
        //获取该任务备份对象
        $sql = "select vv.user_uuid, vol.object_uuid, vol.type, vol.vcenter_uuid, vv.vcenter_flag, vol.vm_config, vol.exclude_vm_uuid_list 
            from vm_vcenter vv, vm_object_list vol where vol.vcenter_uuid = vv.vcenter_uuid and vol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($params['taskuuid']));
        if (empty($data)) {
            //获取该任务所备份的虚拟机
            $sql = "select vv.user_uuid, vml.vm_name, vml.vm_uuid as object_uuid, 7 as type, vml.vcenter_uuid, vv.vcenter_flag , vml.vm_config, '' as exclude_vm_uuid_list 
                from vm_machine_list vml, vm_vcenter vv  where vml.vcenter_uuid = vv.vcenter_uuid and vml.task_uuid = ?";
            $data = $this->dbSelect($sql, array($params['taskuuid']));
        }
        $vcenterUUID = null;
        if(!empty($data)){
            // 租户模式下，虚拟化中心不属于该用户，但属于分配的资源，备份树为三层：hypervisor - 已分配虚拟机 - vm
            if (!empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] != $data[0]['user_uuid']) {
                $hypervisorType = $params['type'];
                return $this->getTenantAllocatedVmTree($hypervisorType, $data[0]['vcenter_uuid'], $params['taskuuid']);
            }
            //备份任务限制只有在一个虚拟化中心下,所以虚拟化中心只有一个
            $vcenterUUID = $data[0]['vcenter_uuid'];
            $showType = $params['displaymode'] ?: Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];
            //             if($params['type'] == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
            //             	$showType = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_VM'];
            //             }
            $syncParams = array(
                'id' => $vcenterUUID,
                'vcflag' => $data[0]['vcenter_flag'],
                'hypervisor' => $params['type'],
                'open' => true,
                'showtype' => $showType,
                'modifyflag' => true,
                'backupflag' => true,
                'taskuuid' => $params['taskuuid']
            );
            //得到所有备份对象数组
            $vmuuidArr = array();
            $diskVMList = array();
            $exVmList = array();
            foreach ($data as $d){
                if(empty($d['vm_config'])) continue;
                $vmuuidArr[] = $d['object_uuid'];
                $diskVMList[] = array(
                    'id' => $d['object_uuid'],
                    'vm_config' => json_decode($d['vm_config']),
                );
            }

            foreach ($data as $d){
                //获取创建任务后对象下新增的虚拟机，未开启自动备份则要排除，已开启自动备份无需处理会自动勾选
                $notInTaskVmUuidList = [];
                if (Xphp::$_config['VM_TREE_TYPE']['VM'] != $d['type'] && !$params['auto_join_flag']) {
                    $notInTaskVms = $this->getObjectVmsNotInTask($params['displaymode'], $d['vcenter_uuid'], $d['object_uuid'], $params['taskuuid']);
                    if ($notInTaskVms) {
                        $notInTaskVmUuidList = array_column($notInTaskVms, 'uuid');
                    }
                }
                //if(empty($d['exclude_vm_uuid_list'])) continue;
                $vmuuidList = explode(',', trim($d['exclude_vm_uuid_list'], ','));
                $vmuuidArr[] = $d['object_uuid'];
                $exVmList[] = array(
                    'id' => $d['object_uuid'],
                    'exclude_vm_uuid_list' => array_values(array_unique(array_merge($vmuuidList, $notInTaskVmUuidList))),
                );
            }

            //获取对应vcenter的子树
            $syncVcenter = $this->getBackupSyncVcenter($syncParams);
            $syncVcenter = $utils->object_array(json_decode($syncVcenter));
            if($syncVcenter['re']){
                //获取成功
                $msg = $syncVcenter['msg'];
                foreach ($msg as $key => $each){
                    //如果找到对应虚拟机,直接赋值选中
                    foreach ($diskVMList as $disk){
                        $diskVMuuid = array( $disk['id']);
                        if(in_array($each['id'], $diskVMuuid)){
                            $msg[$key]['checked'] = true;
                            $msg[$key]['chkDisabled'] = false;
                            $msg[$key]['diskChecked'] = $disk['vm_config'];
                        }
                    }

                    //如果找到对应对象,直接赋值选中
                    foreach ($exVmList as $vm){
                        $diskVMuuid = array( $vm['id']);
                        if(in_array($each['id'], $diskVMuuid)){
                            $msg[$key]['checked'] = true;
                            $msg[$key]['chkDisabled'] = false;
                            $msg[$key]['vmChecked'] = $vm['exclude_vm_uuid_list'];
                        }
                    }
                    //                     if(in_array($each['id'], $vmuuidArr)){
                    //                         $msg[$key]['checked'] = true;
                    //                         $msg[$key]['chkDisabled'] = false;
                    //                     }
                }
                $childArr = $msg;
            }
        }
        //展开虚拟化中心,如果虚拟化中心就是宿主机,修改虚拟化中心的节点ID为宿主机UUID
        foreach ($topNode as $key => $each){
            if (!empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] != $each['userUuid']) {
                // 不是租户添加的不显示
                unset($topNode[$key]);
                continue;
            }
            if($each['id'] == $vcenterUUID){
//                if($each['vcflag'] == Xphp::$_config['FLAG']['UNSET'] && $each['hypervisor'] ==2){
//                    unset($topNode[$key]);
//                    continue;
//                }
                //展开虚拟化中心
                $topNode[$key]['open'] = true;
                if($each['vcflag'] == Xphp::$_config['FLAG']['UNSET']){
                    //如果虚拟化中心就是宿主机,修改虚拟化中心的节点ID为宿主机UUID
                    $topNode[$key]['id'] = $this->getVcAndHostUUID($vcenterUUID, $topNode[$key]['hypervisor']);
                }
            }
        }
        if(!empty($childArr)){
            $result = array_merge($topNode, $childArr);
        }
        return json_encode($result);
    }

    /**
     * 获取对象下不在任务中的虚拟机
     * @param $display_mode
     * @param $vcenter_uuid
     * @param $object_uuid
     * @param $task_uuid
     * @return array
     */
    public function getObjectVmsNotInTask($display_mode, $vcenter_uuid, $object_uuid, $task_uuid): array
    {
        $vms = [];
        $sql = "select vt.name, vt.vcenter_uuid, vt.uuid, vt.type, vt.dir_path  
            from vm_tree vt where vt.display_mode = ? and vt.vcenter_uuid = ? and vt.parent_uuid = ? and vt.uuid != vt.parent_uuid 
            and vt.uuid not in (select vm_uuid from vm_machine_list where task_uuid = ?)";
        $data = $this->dbSelect($sql, [$display_mode, $vcenter_uuid, $object_uuid, $task_uuid]);
        foreach ($data as $item) {
            if (Xphp::$_config['VM_TREE_TYPE']['VM'] == $item['type']) {
                $vms[] = $item;
            } else {
                $vms = array_merge($vms, $this->getObjectVmsNotInTask($display_mode, $item['vcenter_uuid'], $item['uuid'], $task_uuid));
            }
        }
        return $vms;
    }
    
    /**
     * 得到直接作为vcenter的宿主机的mofer uuid
     * @param string $vcenteruuid
     * @param int    $hypervisor
     */
    private function getVcAndHostUUID($vcenteruuid, $hypervisor){
        if(in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])){
            $sql = "select uuid from vm_tree where parent_uuid = ?";
            $data = $this->dbSelect($sql, array($vcenteruuid));
            if($data){
                $uuid = $data[0]['uuid'];
            }
            return $uuid;
        }else{
            return $vcenteruuid;
        }
    }
    
    /**
     * 刷新虚拟化中心
     * @param int $submoduleType
     * @param string $vcenterUUID
     * @param int $displayMode
     * @param boolean $command  是否为命令模式,命令模式将直接返回
     * @param boolean $newInstance 是否重新实例化
     */
    private function refleshVcenter($submoduleType,$vcenterUUID, $displayMode, $command = FALSE, $newInstance = FALSE){
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getvCenterUUID();
        $opName = 'VM_VCENTER_OP_REFLASH';
        $msg = json_encode(array('vcenter_uuid'=>$vcenterUUID, 'display_mode' => intval($displayMode)));
        return $this->mbVMMsg($nodeuuid, $submoduleType, $opName, $msg, FALSE, $command, $newInstance);
    }
    
    /**
     * 同步单个虚拟化中心
     * @param unknown $params
     */
    public function syncVcenterOne($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_sync");
        $uuid = $params['id'];
        $ip = $params['ip'];//如果IP存在
        if(!empty($ip)){
            $uuid = $this->getVcenterUUIDbyIP($ip);
        }
        $sql = "select hypervisor_type, vcenter_ip from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $hypervisor = $data[0]['hypervisor_type'];
        $displayMode = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];
        $refleshResult = $this->refleshVcenter($hypervisor, $uuid, $displayMode);
        $operate = Xphp::$_lang['WEB_VM_VCENTER_SYNC'] . $data[0]['vcenter_ip'];
        //VMware同时刷新三种方式
        if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'] == $hypervisor && $refleshResult['result']) {
            $refleshResult2 = $this->refleshVcenter($hypervisor, $uuid, Xphp::$_config['VM_TREE_DISPLAY_MODE']['VM_AND_TEMPLATE']);
            if (!$refleshResult2['result']) {
                return $this->muOpResult(false, $operate);
            }
            $refleshResult3 = $this->refleshVcenter($hypervisor, $uuid, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_VM']);
            if (!$refleshResult3['result']) {
                return $this->muOpResult(false, $operate);
            }
        }
        return $this->muOpResult($refleshResult['result'], $operate);
    }
    
    /**
     * 同步多个虚拟化中心
     * @param unknown $params
     */
    public function syncVcenterMore($params){
        //TODO 暂时不支持同时刷新多个,后期再添加
        return;
        $uuids = $params['ids'];
    }
    
    /**
     * 备份树异步获取
     * 异步获取vcenter信息:先刷新,在从数据库获取
     * @param array $params
     * @return string
     */
    public function getBackupSyncVcenter($params){
        $uuid = $params['id'];            //vcenter uuid
        $vcflag = intval($params['vcflag']);    //vcenter 标志
        $hypervisor = intval($params['hypervisor']);
        $showtype = intval($params['showtype']);
        $openFlag = $params['open'];
        $refresh = $params['refresh'];          //是否刷新
        $modifyflag = $params['modifyflag'];    //修改标志
        $taskuuid = $params['taskuuid'];        //任务uuid
        $backupFlag= $params['backupflag'];
        $allocatedVmFlag = $params['allocatedVmFlag']; // 已分配虚拟机节点标志
        
        $this->paramsCheck($uuid, $hypervisor, $showtype);
        if (!$allocatedVmFlag) {
            $this->refreshVcenterCheck($refresh, $hypervisor, $uuid, $showtype);
        }
        
        return $this->getTreeFromVMTree($hypervisor, $vcflag, $uuid, $showtype, TRUE, $openFlag, $modifyflag, $backupFlag, $taskuuid, $allocatedVmFlag);
    }
    
    /**
     * 检测并刷新虚拟化中心
     * @param unknown $refresh      刷新标志
     * @param unknown $hypervisor
     * @param unknown $uuid
     * @param unknown $showtype
     * @return boolean|string
     */
    private function refreshVcenterCheck($refresh, $hypervisor, $uuid, $showtype){
        if(!$refresh) return true;
       // $opName = 'VM_VCENTER_OP_REFLASH';
        $mbResult = $this->refleshVcenter($hypervisor, $uuid, $showtype);
        
        $result = $mbResult['result'];
        if(!$result){
            //如果刷新失败,返回错误消息
            //因为改成了同步 提示信息就改成同步
            $operate  = Xphp::$_lang['WEB_VM_VCENTER_SYNC'];
            exit($this->muOpResult($result, $operate, '', '', $mbResult['errorCode']));
        }
    }
    
    /**
     * 得到系统所有授权宿主机的UUID
     */
    public function getAllLisenceHostUUID(){
        $sql = "select vcenter_uuid, host_uuid from vm_host where authorization_flag = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']));
        $hosts = array(
            'hostuuid' => array(),
            'vcenteruuid' => array()
        );
        foreach ($data as $d){
            $hosts['hostuuid'][] = $d['host_uuid'];
            $hosts['vcenteruuid'][] = $d['vcenter_uuid'];
        }
        return $hosts;
    }
    
    /**
     * 根据虚拟机所在宿主机授权情况得到虚拟机名字
     * @param array $lisenceHostArr     所有授权的宿主机 UUID数组array('vcenteruuid' => array(), 'hostuuid' => array())
     * @param string $vcenterUUID       虚拟机所在虚拟化中心UUID
     * @param string $vmHostUUID        虚拟机所在宿主机UUID
     * @param string $vmname            虚拟机的名字
     */
    public function getVMNameWithLisence($lisenceHostArr, $vcenterUUID, $vmHostUUID, $vmname, $type){
        if($type != Xphp::$_config['VM_TREE_TYPE']['VM'] &&
            $type != Xphp::$_config['VM_TREE_TYPE']['HOST']){
                return $vmname;
        }
        
        if(empty($vmHostUUID)){
            //TODO 这里因为虚拟机没有启动的时候可能拿不到虚拟机的宿主机,暂时放过
            return $vmname;
        }
        
        if(in_array($vmHostUUID, $lisenceHostArr['hostuuid'])){
            foreach ($lisenceHostArr['hostuuid'] as $key => $value){
                if($vmHostUUID == $value){
                    if($vcenterUUID == $lisenceHostArr['vcenteruuid'][$key]){
                        return $vmname;
                    }
                }
            }
        }
        
        
        return "(" . Xphp::$_lang['WEB_SYSTEM_LISENCE_UNAUTHORIZED'] . ")" . $vmname;
    }
    
    /**
     * 得到虚拟机(所在宿主机)是否授权
     * @param array $lisenceHostArr     所有授权的宿主机UUID数组
     * @param string $vmHostUUID        虚拟机所在宿主机UUID
     * @return boolean
     */
    public function getVMHostLisenced($lisenceHostArr, $vcenterUUID, $vmHostUUID, $inBackupFlag, $modifyFlag){
        if($modifyFlag && $inBackupFlag){
            //如果是修改备份任务,不可用
            return false;
        }
        if($inBackupFlag){
            //如果是在备份任务中,不可用
            return false;
        }
        if(in_array($vmHostUUID, $lisenceHostArr['hostuuid'])){
            foreach ($lisenceHostArr['hostuuid'] as $key => $value){
                if($vmHostUUID == $value){
                    if($vcenterUUID == $lisenceHostArr['vcenteruuid'][$key]){
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * 根据vcenterUUID 和展示方式得到虚拟机树
     * @param int $hypervisor   虚拟化类型
     * @param bool $vcenterFlag  虚拟化中心标志
     * @param string $vcenterUUID
     * @param int $showType
     * @param boolean $showVMFlag   是否显示虚拟机,用于虚拟化中心详情
     * @param boolean $openFlag     父节点是否展开标志
     * @param boolean $modifyFlag   是否是修改任务标志(修改任务的时候复选框可用)
     * @param boolean $allocatedVmFlag 是否是租户的已分配虚拟机节点
     * @return string
     */
    private function getTreeFromVMTree($hypervisor, $vcenterFlag, $vcenterUUID, $showType, $showVMFlag, $openFlag = FALSE, $modifyFlag = FALSE, $backupFlag = false, $taskuuid = '', $allocatedVmFlag = false){
        
        //获取用户已分配的虚拟机
        $resourceHandler = Xphp::instance('ResourceHandler');
        $publicCloudFlag = in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']);
        $vmResource = $resourceHandler->pGetUserResourceVM(Xphp::$_user['useruuid'],"tree_id", "desc", $limit = 0, "all", [], $publicCloudFlag);
        $hostList =array();
        $vmList = array();
        $vcenterList = array();
        foreach ($vmResource['data'] as $vm){
            if(!in_array($vm['vcenter_uuid'], $vcenterList)){
                $vcenterList[] = $vm['vcenter_uuid'];
            }
            if(!in_array($vm['host_uuid'], $hostList)){
                $hostList[] = $vm['host_uuid'];
            }
            if(!in_array($vm['uuid'], $vmList)){
                $vmList[] = $vm['uuid'];
            }
        }
       
        $vcenterFlag = $vcenterFlag == Xphp::$_config['FLAG']['SET'];
        if ($allocatedVmFlag) {
            // 租户获取已分配虚拟机时是所有虚拟化中心
            $sql = "select vv.user_uuid, vv.hypervisor_type, vt.tree_id, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.conn_state, vt.power_state,
                vt.host_uuid, vt.vcenter_uuid, vt.version
                from vm_tree vt join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                join mt_user_resource mur on vt.uuid = mur.vm_uuid and vt.vcenter_uuid = mur.vcenter_uuid 
                where vt.display_mode = ? ";
            $sqlParams = array($showType);
        } else {
            $sql = "select vv.user_uuid, vv.hypervisor_type, vt.tree_id, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.conn_state, vt.power_state,
                vt.host_uuid, vt.vcenter_uuid, vt.version
                from vm_tree vt join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                left join mt_user_resource mur on vt.uuid = mur.vm_uuid 
                where vt.vcenter_uuid = ? and vt.display_mode = ? ";
            $sqlParams = array($vcenterUUID, $showType);
        }
        $sql .= " order by vt.type";
        if(in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware'])){
            //VMware按照中文排序,保持和vcenter显示顺序一致
            $sql .= ", convert(vt.name USING gbk) COLLATE gbk_chinese_ci";
        }else{
            $sql .= ", vt.name";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();
        $vmInfo = $this->getTenantBackupVMInfo();
        $lisenceHosts = $this->getAllLisenceHostUUID();
        //检查Xenserver高版本8.0以上没有静默快照
        $noSnapshot = $this->getSnapshotFlag($vcenterUUID);
        if($modifyFlag){
            $vmModifyData = $this->getTaskVmList($taskuuid);
            $vmModifyList = $vmModifyData['list'];
            $taskVcenterUuid = $vmModifyData['vcenter_uuid'];
        }
        $disconnectHostUuids = array();//记录断开连接的主机uuid
        $parentuuids = [];
        $notAdminFlag = $this->isNotAdmin();
        foreach ($data as $d){
            //不是超级管理员或者虚拟机属于当前用户添加需要进行虚拟机检测
            if(Xphp::$_user['useruuid'] != $d['user_uuid'] && Xphp::$_user['usertype'] != Xphp::$_config['USERTYPE']['manager']){
                //如果是操作员，并且没未分配宿主机
                if(intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['HOST'] && !in_array($d['uuid'], $hostList) && !in_array($d['host_uuid'], $hostList)){
                    $parentuuids[] = $d['uuid'];
                    continue;
                }
                //如果是操作员，并且没未分配虚拟机
                if(intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['VM'] && !in_array($d['uuid'], $vmList)){
                    continue;
                }
                
                //添加父节点层
                if(intval($d['type']) != Xphp::$_config['VM_TREE_TYPE']['HOST'] && intval($d['type']) != Xphp::$_config['VM_TREE_TYPE']['VM']){
                    
                    if(in_array($d['parent_uuid'], $parentuuids)){
                        continue;
                    }
                }
                
            }
            
            if(!$vcenterFlag){
                if(intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['HOST'] && $hypervisor != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
                    //如果是宿主机,不再显示宿主机层
                    continue;
                }
            }
            
            //hyperv宿主机是虚拟化中心
            if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV'] == $hypervisor && $vcenterUUID == $d['uuid']) {
                continue;
            }

            $vmFlag = intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['VM'];
//            $hascheck = intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['VM'];
//            if(!$hascheck && $backupFlag){
//                switch($showType){
//                    case 1:
//                        $hascheck = intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['POOL'] && in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware']) || 
//                        intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['FOLDER'] && $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM'];
//                        break;
//                    case 2:
//                        $hascheck = intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['FOLDER'];
//                        break;
//                    case 3:
//                        $hascheck = intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['HOST'];
//                        break;
//                        
//                }
//            }
            $hascheck = true;
            if($vmFlag && !$showVMFlag) continue;   //如果是虚拟机,而且不显示虚拟机
            $name = $this->getVMTreeName($d['type'], $d['name'], $d['vcenter_uuid']);
            $name = $this->getOffLineStatusDes($d['conn_state'], $name);
            $name = $this->getVMNameWithLisence($lisenceHosts, $d['vcenter_uuid'], $d['host_uuid'], $name, $d['type']);
            //            	$detail = json_decode($d['detail']['annotation']);
            $title = $this->getBackupTreeNodeTitle($d['type'], $name, $d['uuid'], $d['vcenter_uuid'], $vmInfo, "");
            $inBackupFlag = $this->getVMInBackupFlag($d['type'], $d['uuid'], $d['vcenter_uuid'], $vmInfo);
            if(intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['VM']){
                $checkEnabled = $this->getVMHostLisenced($lisenceHosts, $d['vcenter_uuid'], $d['host_uuid'], $inBackupFlag, $modifyFlag);
            }else{
                $checkEnabled = $hascheck;
                //非管理员的用户无法勾选虚拟机上级
                if ($notAdminFlag) {
                    $checkEnabled = false;
                }
            }
            //判断修改任务
            $checkedFlag = false;
            if($modifyFlag && in_array($d['uuid'], $vmModifyList) && $d['vcenter_uuid'] == $taskVcenterUuid){
                $checkedFlag = true;
                $checkEnabled = true;
            }

            //若主机断开连接，则虚拟机不可选
            $connState = $this->getVMHostOnlineFlag($d['conn_state']);
            if (intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['HOST'] && !$connState) {
                $checkEnabled = false;
                $connState = false;
                $disconnectHostUuids[] = $d['uuid'];
            }
            if (intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['VM'] && in_array($d['host_uuid'], $disconnectHostUuids)) {
                $checkEnabled = false;
                $connState = false;
            };

            //主机和集群展示方式下屏蔽主机勾选
            if(in_array(intval($hypervisor), Xphp::$_config['VMHYPERVISORGROUP']['vmware']) && intval($d['type']) == Xphp::$_config['VM_TREE_TYPE']['HOST'] && $showType == Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']){
                $checkEnabled = false;
            }

            //模板不可选
            if (Xphp::$_config['VM_TREE_TYPE']['TEMPLATE'] == $d['type']) {
                $checkEnabled = false;
            }

            // 租户模式，获取不属于用户但是已分配的vm
            if ($allocatedVmFlag) {
                if (Xphp::$_user['useruuid'] != $d['user_uuid'] && Xphp::$_config['VM_TREE_TYPE']['VM'] == $d['type'] && $hypervisor == $d['hypervisor_type']) {
                    $node[] = array(
                        'id' => $d['uuid'],
                        'pid' => $hypervisor . '_allocated_vm',
//                        'pid' => $d['parent_uuid'],
                        'name' => htmlspecialchars_decode(rawurldecode($name)),
                        'hostuuid' => $d['host_uuid'],
                        'vcenteruuid' => $d['vcenter_uuid'],
                        'hypervisor' => $hypervisor,
                        'iconSkin' => $this->getTreeTypeIcon($hypervisor, $d['type'], $d['conn_state'], $d['power_state']),
                        "nocheck" => false,
                        "path" => htmlspecialchars_decode(rawurldecode($d['dir_path'])),
                        "path_show" => htmlspecialchars($d['dir_path']), // 显示使用
                        "eventtype" => 'vm',
                        "version" => $d['version'],
                        "inbackup" => $inBackupFlag,
                        "title" => $title,
                        "chkDisabled" => !$checkEnabled,

                        "online" => $connState,
                        "open" => $openFlag,
                        "clickshow" => $d['uuid'] == $d['vcenter_uuid'],    //虚拟化中心详情使用
                        "detail" => "",
                        "diskChecked" => "",
                        "vmChecked" => "",
                        "initDisk" => false,
                        "nosnapshot" => $noSnapshot,
                        "checked" => $checkedFlag,
                        "type" => $d['type']
                    );
                }
                continue;
            }

            $node[] = array(
                'id' => $d['uuid'],
                'pid' => $d['parent_uuid'],
                'name' => htmlspecialchars_decode(rawurldecode($name)),
                'hostuuid' => $d['host_uuid'],
                'vcenteruuid' => $vcenterUUID,
                'hypervisor' => $hypervisor,
                'iconSkin' => $this->getTreeTypeIcon($hypervisor,$d['type'], $d['conn_state'], $d['power_state']),
                "nocheck" => !$hascheck,
                "path" => htmlspecialchars_decode(rawurldecode($d['dir_path'])),
                "path_show" => htmlspecialchars($d['dir_path']), // 显示使用
                "eventtype" => $vmFlag ? 'vm' : '',
                "version" =>$d['version'],
                "inbackup" => $inBackupFlag,
                "title" => $title,
                "chkDisabled" => !$checkEnabled,
                
                "online" => $connState,
                "open" => $openFlag,
                "clickshow" => $d['uuid'] == $d['vcenter_uuid'],    //虚拟化中心详情使用
                "detail" => "",
                "diskChecked" => "",
                "vmChecked" => "",
                "initDisk" => false,
                "nosnapshot" => $noSnapshot,
                "checked" => $checkedFlag,
                "type" => $d['type']
            );
        }
        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);
    }
    
    /**
     * 根据节点类型得到名称,主要解决KVM的 8 9 10 类型的语言问题
     * @param int $type
     * @param string $name
     * @param string $hostuuid
     */
    public function getVMTreeName($type, $name, $vcenteruuid){
        $type = intval($type);
        $vmTreeType = Xphp::$_config['VM_TREE_TYPE'];
        if($type == $vmTreeType['OVIRT_FAKE_HOST_FOLDER']){
            $langKey = 'WEB_VM_TREE_OVIRT_FAKE_HOST_FOLDER';
            return Xphp::$_lang[$langKey];
        }elseif($type == $vmTreeType['OVIRT_FAKE_VM_FOLDER']){
            $langKey = 'WEB_VM_TREE_OVIRT_FAKE_VM_FOLDER';
            return Xphp::$_lang[$langKey];
        }elseif($type == $vmTreeType['OVIRT_CLUSTER_FOLDER']){
            $langKey = 'WEB_VM_TREE_OVIRT_CLUSTER_FOLDER';
            return Xphp::$_lang[$langKey];
        }else if($type == $vmTreeType['HOST']){  //针对于hyper-v单机宿主机
            return $this->getHyperVHostName($vcenteruuid, $name);
        }else{
            return $name;
        }
    }
    
    /**
     * 得到虚拟机备份树节点标题
     * @param int $type         vm_tree节点类型
     * @param string $name      节点名字
     * @param string $vmuuid
     * @param string $vcenteruuid
     * @param array $backupVMInfo   处于备份任务的虚拟机信息
     * @param string $detail        备注信息
     * @return string
     */
    public function getBackupTreeNodeTitle($type, $name, $vmuuid, $vcenteruuid, $backupVMInfo, $detail){
        //如果不是虚拟机,直接返回名字
        if($type != Xphp::$_config['VM_TREE_TYPE']['VM']) return $name;
        $backupFlag = $this->getVMInBackupFlag($type, $vmuuid, $vcenteruuid, $backupVMInfo);
        if(!$backupFlag){
            //虚拟机不在任务中,如果有备注,返回备注信息
            if(!empty($detail)){
                return $detail;
            }
            return $name;
        }
        //在任务中的虚拟机
        $key = array_search($vmuuid, $backupVMInfo['vmuuid']);
        $taskname = $backupVMInfo['taskname'][$key];
        return urldecode($name) . "('" . $taskname . "'". Xphp::$_lang['WEB_BACKUP_TASK_PROTECTED'] . ")";
    }
    
    
    /**
     * 得到处于备份任务的虚拟机信息
     * Vmhandler需要调用,所以public
     */
    public function getBackupVMInfo(){
        $sql = "select vml.vcenter_uuid, vml.vm_uuid, bt.task_name from vm_machine_list vml, bd_task bt
                where vml.task_uuid = bt.task_uuid and bt.task_type = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKTYPE']['BACKUP']));
        $info = array(
            'vmuuid' => array(),
            'vcenteruuid' => array(),
            'taskname' =>array()
        );
        foreach ($data as $d){
            $idStr = $d['vcenter_uuid'].'_'.$d['vm_uuid'];
            $info['vmuuid'][] = $d['vm_uuid'];
            $info['vcenteruuid'][] = $d['vcenter_uuid'];
            $info['taskname'][] = $d['task_name'];
            $info['tasklist'][$idStr] = $d['task_name'];
        }
        return $info;
    }
    
    /**
     * 得到虚拟机是否处于备份任务中
     * @param int $vmFlag       vm_tree中虚拟机类型
     * @param string $vmuuid       虚拟机uuid
     * @param string $vcenteruuid  vcenteruuid
     * @param array $backupVMInfo   处于备份任务的虚拟机信息
     * @return boolean
     * Vmhandler需要调用,所以public
     */
    public function getVMInBackupFlag($vmFlag, $vmuuid, $vcenteruuid, $backupVMInfo){
        $vcenterInfo = $this->pGetVcenterIPByUUID($vcenteruuid);
        //如果不是虚拟机,直接返回
        if($vmFlag != Xphp::$_config['VM_TREE_TYPE']['VM']) return false;
        //如果没有备份的虚拟机,直接返回
        if(empty($backupVMInfo['vmuuid'])) return false;
        //因为虚拟机uuid可能相同，所以这里需要对虚拟机进行遍历查找
        foreach ($backupVMInfo['vmuuid'] as $key => $eachUUID){
            
            if($vmuuid == $eachUUID){
                //如果是Openstack系列，租户概念，仅检测虚拟机uuid
                if(in_array($vcenterInfo['hypervisor'], Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){
                    return true;
                }
                //如果找到了虚拟机UUID对应的虚拟机,再找对应位置的vcenteruuid是否一样,一样的话这台虚拟机就是备份状态
                if($vcenteruuid == $backupVMInfo['vcenteruuid'][$key]){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * 检测虚拟机连接状态
     * @param unknown $connState
     */
    public function getVMHostOnlineFlag($connState){
        $connState = intval($connState);
        if($connState == Xphp::$_config['HOSTSTATUS']['DISCONNECT']){
            return false;
        }
        return true;
    }
    
    /**
     * 得到节点是否断开状态的描述,断开的时候增加已断开描述
     * @param int $status
     * @param string $name
     * @return string
     */
    public function getOffLineStatusDes($status, $name){
        if(intval($status) == Xphp::$_config['HOSTSTATUS']['DISCONNECT']){
            $name .= "  " . "(" . Xphp::$_lang['WEB_VM_HOST_DISCONNECTED'] . ")";
        }
        return $name;
    }
    
    /**
     * 根据宿主机信息得到虚拟机信息/树
     * @param int $hypervisor       虚拟化类型
     * @param string $vcenterUUID   vcener uuid
     * @param string $hostUUID      host uuid
     * @return array
     */
    private function getHostsMachine($hypervisor, $vcenterUUID, $hostUUID){
        $this->paramsCheck($hypervisor, $vcenterUUID, $hostUUID);
        $machine = array();
        $sql = "select machine_id, vm_uuid, vm_name, conn_state, power_state, version
                from vm_machine where host_uuid = ? and vcenter_uuid = ? order by vm_name";
        $data = $this->dbSelect($sql, array($hostUUID, $vcenterUUID));
        foreach ($data as $d){
            $machine[] = array(
                "id" => $d['vm_uuid'],
                "pid" => $hostUUID,
                "name" => $this->getOffLineStatusDes($d['conn_state'], $d['vm_name']),
                "hostuuid" => $hostUUID,
                "vcenteruuid" => $vcenterUUID,
                "version" => $d['version'],
                "type" => 3,
                "checked" => false,
                "eventtype" => 'vm',
                "hypervisor" => $hypervisor,
                "iconSkin" => $this->getVmStateIcon($d['conn_state'], $d['power_state'],$hypervisor),
            );
        }
        return $machine;
    }
    
    /**
     * 根据虚拟机状态获取状态图标  LS
     * @param int $status
     * @author luokai@vinchin.com
     * @author liushuai@vinchin.com
     */
    private function getVMStatusIcon($status,$hypervisor){
        $iconskin = Xphp::$_config['VIRTUALIZATIONICONCLASSNAME'][$hypervisor];
        $status = intval($status);
        $icon = $iconskin.'_vm';
        switch($status){
            case Xphp::$_config['MACHINESTATUS']['POWEREDOFF']:
                $icon = $iconskin.'_vm_poweroff';
                break;
            case Xphp::$_config['MACHINESTATUS']['POWEREDON']:
                $icon = $iconskin.'_vm_running';
                break;
            case Xphp::$_config['MACHINESTATUS']['SUSPENDED']:
            case Xphp::$_config['MACHINESTATUS']['PAUSE']:
                $icon = $iconskin.'_vm_suspend';
                break;
        }
        return $icon;
    }
    
    /**
     * 得到虚拟化中心信息
     * @param unknown $params
     */
    public function getVcenters($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $utils = Xphp::instance("Utils");
        $search = $params['search'];
        $sortArr = array('', '', 'vv.vcenter_ip', 'vv.nickname', 'vv.hypervisor_type', 'vv.version', 'vv.username', 'vv.refresh_time', 'vv.user_uuid','');

        $sql = "select distinct vv.vcenter_id, vv.vcenter_uuid, vv.vcenter_ip, vv.nickname, vv.hypervisor_type, vv.username, vv.detail,
                unix_timestamp(vv.register_time) register_time, unix_timestamp(vv.refresh_time) refresh_time, vv.user_uuid, vv.version 
                from vm_vcenter vv left join mt_user_resource mur on vv.vcenter_uuid = mur.vcenter_uuid, bd_user bu 
                where vv.user_uuid = bu.user_uuid and hypervisor_type not in (" . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ") ";
        $sqlCount = "select count(vv.vcenter_id) as total from vm_vcenter vv left join mt_user_resource mur on vv.vcenter_uuid = mur.vcenter_uuid, 
            bd_user bu where vv.user_uuid = bu.user_uuid and hypervisor_type not in (" . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ") ";
        
        //查询参数
        $sqlParams = array();
        $sqlCountParams = array();

        $accurateFlag = $params['accurateFlag'];
        
        if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
            // 超级管理查看所有的资源
        } elseif ($_SESSION['isThreePowers'] && ($_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['sysadmin']
                || $_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['safeadmin'])) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        }else{
            // 关联管理用户判断
            $authUser = $_SESSION['authUser']['vcenter_manager_look'] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= "and (bu.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
                $sqlCount .= "and (bu.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
            } else {
                $userUuid = Xphp::$_user['useruuid'];
                $sql .= " and (bu.user_uuid = ? or mur.user_uuid = ?) ";
                $sqlCount .= " and (bu.user_uuid = ? or mur.user_uuid = ?) ";
                $sqlParams = array_merge($sqlParams,array($userUuid, $userUuid));
                $sqlCountParams = array_merge($sqlCountParams,array($userUuid, $userUuid));
            }
        }

        //云平台的
        $cloudType = Xphp::$_config['VMHYPERVISORGROUP']['openstack'];
        $cloudTypeStr = implode(',', $cloudType);
        if ($params['cloudFlag']) {
            $sql .= " and vv.hypervisor_type in({$cloudTypeStr}) ";
            $sqlCount .= " and vv.hypervisor_type in({$cloudTypeStr}) ";
        } else {
            $sql .= " and vv.hypervisor_type not in({$cloudTypeStr}) ";
            $sqlCount .= " and vv.hypervisor_type not in({$cloudTypeStr}) ";
        }

        //精确搜索
        if(!$accurateFlag){
            $searchValue = $search['search'];
            $searchValue = $utils->escapeWildcard($searchValue);
            if($this->checkEmpty($searchValue)){
                $sql .= " and vv.nickname like ? ";
                $sqlCount .= " and vv.nickname like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$searchValue.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$searchValue.'%'));
            }
        }else{
            $search = $params['search'];
            $userName = $search['userName'];
            $hypervisor = intval($search['hypervisor']);
            $vcenterIp = $search['vcenterIp'];
            $nickName = $search['nickName'];
            
            
            //判断输入的用户名是否在当前用户的管理范围
            if($this->checkEmpty($userName)){
                $sql .= " and bu.user_name like ? ";
                $sqlCount .= " and bu.user_name like ? ";
                $sqlParams = array_merge($sqlParams,array('%' . $userName . '%'));
                $sqlCountParams = array_merge($sqlCountParams,array('%' . $userName . '%'));
            }
            
            //虚拟化类型
            if(!empty($hypervisor)){
                $sql .= " and vv.hypervisor_type = ? ";
                $sqlCount .= " and vv.hypervisor_type = ? ";
                $sqlParams = array_merge($sqlParams, array($hypervisor));
                $sqlCountParams = array_merge($sqlCountParams, array($hypervisor));
            }
            
            //别名
            if($this->checkEmpty($nickName)){
                $sql .= " and vv.nickname like ? ";
                $sqlCount .= " and vv.nickname like ? ";
                $sqlParams = array_merge($sqlParams,array('%'.$nickName.'%'));
                $sqlCountParams = array_merge($sqlCountParams,array('%'.$nickName.'%'));
            }
            //虚拟化中心IP
            if($this->checkEmpty($vcenterIp)){
                $sql .= " and vv.vcenter_ip like ? ";
                $sqlCount .= " and vv.vcenter_ip like ? ";
                $sqlParams = array_merge($sqlParams,array('%'.$vcenterIp.'%'));
                $sqlCountParams = array_merge($sqlCountParams,array('%'.$vcenterIp.'%'));
            }
            
        }
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams,array($start, $length));

        $datas = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);
        
        $hostPermission = $this->getLisenceHostPermission();
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        foreach ($datas as $data){
            if (filter_var(trim($data['vcenter_ip'], '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $data['vcenter_ip'] = trim($data['vcenter_ip'], '[]');
            }
            if (filter_var(trim($data['nickname'], '[]'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $data['nickname'] = trim($data['nickname'], '[]');
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $data['vcenter_uuid'] .'">',
                $id,
                $this->getVcenterIp($data['vcenter_ip'], $data['detail'], intval($data['hypervisor_type']), $data['username']),
                htmlspecialchars_decode($data['nickname']),
                Xphp::$_config['VMHYPERVISORDES'][intval($data['hypervisor_type'])],
                $this->getVcentversion($data['hypervisor_type'], $data['version']),
                $data['username'],
                $this->parseDate($data['refresh_time']),
                $utils->getUsername($data['user_uuid']),
                $this->getVcenterHostLisenceStatus($data['vcenter_uuid']),
                $data['vcenter_uuid'],
                $hostPermission
            );
            $id++;
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return  json_encode($records);
    }
    
    /**
     * 得到是否可以进行授权操作的权限
     * @return boolean
     */
    private function getLisenceHostPermission(){
        $systemHandler = Xphp::instance('SystemHandler');
        $lisenceInfo = json_decode($systemHandler->getSystemLisenceInfo(), true);
        if($lisenceInfo['status'] != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            //如果系统不是已授权状态
            return false;
        }
        $vmLisenceType = $lisenceInfo['vminfo']['type'];
        if($vmLisenceType == Xphp::$_config['LISENCE_INFO']['type']['host'] ||
            $vmLisenceType == Xphp::$_config['LISENCE_INFO']['type']['cpu']){
                //如果是宿主机授权或CPU授权,才可以进行授权操作
                return true;
        }
        return false;
    }
    
    /**
     * 得到vcenter下所有宿主机授权的状态
     * @param string $vcenteruuid
     */
    public function getVcenterHostLisenceStatus($vcenteruuid){
        $sql = "select authorization_flag from vm_host where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        $countHost = count($data);
        $authHost = 0;
        $unAuthHost = 0;
        foreach ($data as $d){
            if(intval($d['authorization_flag']) == Xphp::$_config['FLAG']['SET']){
                $authHost++;
            }else{
                $unAuthHost++;
            }
        }
        $vmDes = require APP_PATH . "/vm/VmDescription.php";
        $info = array();
        if($countHost == $authHost){
            //全部授权
            $info['status'] = Xphp::$_config['HOST_LISENCE']['ALL'];
        }elseif($countHost == $unAuthHost){
            //未授权
            $info['status'] = Xphp::$_config['HOST_LISENCE']['NONE'];
        }else{
            //部分授权
            $info['status'] = Xphp::$_config['HOST_LISENCE']['PART'];
        }
        $info['statusDes'] = $vmDes['VcenterHostAuthDes'][$info['status']];
        return $info;
    }
    
    /**
     * 根据hypervisor类型得到顶层树的描述
     * @param int $hypervisor
     * @return array
     */
    private function getHypervisorLevTree($hypervisor, $vcenter_flag){
        $hypervisor = intval($hypervisor);
        $name = Xphp::$_config['VMHYPERVISORDES'][$hypervisor];
        $node = array(
            "id" => $hypervisor,
            "pId" => 0,
            "name" => $name,
            "title" => $name,
            "open" => false,
            "nocheck" => true,
            "sid" => $hypervisor,
            "type" => 0,
            "iconSkin" => $this->getHypervisorIcon($hypervisor, $vcenter_flag),
        );
        return $node;
    }
    
    /**
     * 得到虚拟化中心详情的树
     * @param unknown $params
     */
    public function getVcenterDetailsTree($params){
        $archiveflag = $params['archiveflag'];  //归档标记
        $showType = intval($params['type']);    //显示方式
        $vcenteruuid = $params['vcuuid'];       //虚拟化中心UUID,从虚拟机管理点击进去才有
        $this->paramsCheck($showType);
        $selectVc = $this->getVCenterInfo($vcenteruuid, $showType);
        
        $sql = "select vcenter_id, vcenter_ip, vcenter_uuid, nickname, vcenter_name,  hypervisor_type, vcenter_flag, detail, user_uuid, username from vm_vcenter ";
        
        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();
        $hypervisor = array();
        
        
        //获取当前用户所有拥有虚拟机
        $vcenterList =array();
        $resourceHandler = Xphp::instance('ResourceHandler');
        $vmList = $resourceHandler->pGetUserResourceVM(Xphp::$_user['useruuid'],"tree_id", "desc", $limit = 0, "all");
        foreach ($vmList['data'] as $vm){
            if(!in_array($vm['vcenter_uuid'], $vcenterList)){
                $vcenterList[] = $vm['vcenter_uuid'];
            }
        }
        $chkDisabled = $this->isNotAdmin();
        // 标记哪些虚拟化有“已分配虚拟机”节点
        $allocatedVmHypervisors = [];
        foreach ($data as $d){
            $openFlag = false;
            $hypervisorType = intval($d['hypervisor_type']);
            //过滤公有云
            if (in_array($hypervisorType, Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])) {
                continue;
            }
            //过滤Emd
            if ($hypervisorType == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_EMD']) {
                continue;
            }
            //虚拟化中心不属于该用户,也不是分配的资源直接排除
            if(Xphp::$_user['useruuid'] != $d['user_uuid'] && !in_array($d['vcenter_uuid'], $vcenterList)) continue;
            
            if(!in_array($hypervisorType, Xphp::$_config['VMHYPERVISORGROUP']['vmware']) && !in_array($hypervisorType, Xphp::$_config['VMHYPERVISORGROUP']['huawei'])){
                if($showType != Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']){
                    //因为vmware多种形式显示
                    continue;
                }
            }
            
            if(in_array($hypervisorType, Xphp::$_config['VMHYPERVISORGROUP']['huawei'])){
                if($showType == Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_VM']){
                    //huawei暂时不支持主机和虚拟机显示
                    continue;
                }
            }
            if(!in_array($hypervisorType, $hypervisor)){
                //没有hypervisor层时,自动添加
                $hypervisorLevNode = $this->getHypervisorLevTree($hypervisorType, false);
                $hypervisor[] = $hypervisorType;
                
                //如果选择的vcenter虚拟化类型就是他
                if($selectVc['hypervisor_type'] == $d['hypervisor_type']){
                    $hypervisorLevNode['open'] = true;
                }
                $tree[] = $hypervisorLevNode;
            }
            $vcenterFlag = false;
            if($d['vcenter_flag'] == Xphp::$_config['FLAG']['SET']){
                //如果是vcenter
                $vcenterFlag = true;
            }
            
            //如果选中的就是他
            if($selectVc['vcenter_uuid'] == $d['vcenter_uuid']){
                $openFlag = true;
            }

            // 租户模式下，虚拟化中心不属于该用户，但属于分配的资源，第二层显示为“已分配虚拟机”
            if (!empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] != $d['user_uuid'] && !in_array($hypervisorType, $allocatedVmHypervisors)) {
                $allocatedVmHypervisors[] = $hypervisorType;
                $name = Xphp::$_lang['WEB_VM_TREE_ALLOCATED_VM'];
                $node = array(
                    "id" => $hypervisorType . '_allocated_vm', // 特殊处理
                    "pid" => $hypervisorType,
                    "name" => html_entity_decode($name),
                    "title" => $name,
                    "isParent" => true,
                    "nocheck" => true,
                    "type" => 1,
                    "vcenterFlag" => false,
                    "flush" => true,
                    "iconSkin" => $this->getHypervisorIcon($hypervisorType, 1),
                    "hypervisor" => $d['hypervisor_type'],
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "clickshow" => true,
                    "open" => false,
                    "eventtype" => '',
                    "path" => $name,
                    "path_show" => html_entity_decode($name),
                    "nosnapshot" => true,
                    'chkDisabled' => true,
                    'allocatedVmFlag' => true,
                );
                $tree[] = $node;
                continue;
            }

            if (!empty($_SESSION['tenantuuid']) && in_array($d['vcenter_uuid'], $vcenterList)) {
                // 平台是已分配的不再显示
                continue;
            }
            
            $name = $this->getVcenterNameInTree($d['vcenter_uuid'], $hypervisorType, $d['vcenter_flag'], $d['vcenter_ip'], $d['nickname'], $d['vcenter_name'], $d['detail'], $d['username']);
            //TODO
            $node = array(
                "id" => $d['vcenter_uuid'],
                "pid" => $hypervisorType,
                "name" => html_entity_decode($name),
                "title" => $name,
                "isParent" => true,
                "nocheck" => true,
                "type" => 1,
                "vcenterFlag" => $vcenterFlag,
                "flush" => true,
                "iconSkin" => $this->getHypervisorIcon($hypervisorType, $d['vcenter_flag']),
                "hypervisor" => $d['hypervisor_type'],
                "vcenteruuid" => $d['vcenter_uuid'],
                "clickshow" => true,
                "open" => $openFlag,
                "eventtype"=>'',
                "path"=>$name,
                "path_show" => html_entity_decode($name),
                "nosnapshot" => true,
                'chkDisabled' => $chkDisabled,
            );
            $tree[] = $node;
        }
        
        return json_encode($tree);
    }
    
    /**
     * 得到演练的宿主机树
     * @param unknown $params
     */
    public function getOrchProxyTree(){
        //得到所有已经部署了的代理,在部署代理选择的时候禁用选择
        $sql = "select host_uuid, vcenter_uuid from orch_proxy";
        $data = $this->dbSelect($sql);
        $proxyHosts = array();
        foreach ($data as $d){
            $proxyHosts[] = array(
                'hostuuid' => $d['host_uuid'],
                'vcenteruuid' => $d['vcenter_uuid']
            );
        }
        $hypervisorList = array(Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'], Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']);
        $type = implode("','", $hypervisorList);
        $sql = "select vcenter_id, vcenter_ip, vcenter_uuid, nickname, vcenter_name,  hypervisor_type, vcenter_flag, online_flag, detail, username from vm_vcenter ";
        $sql .= " where hypervisor_type in (' $type ') and user_uuid = ? ";
        $sqlParams = array(Xphp::$_user['useruuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();
        $hypervisor = array();
        foreach ($data as $d){
            $openFlag = false;
            $hypervisorType = intval($d['hypervisor_type']);
            if(!in_array($hypervisorType, $hypervisor)){
                //没有hypervisor层时,自动添加
                $hypervisorLevNode = $this->getHypervisorLevTree($hypervisorType, Xphp::$_config['FLAG']['SET']);
                $hypervisor[] = $hypervisorType;
                
                $tree[] = $hypervisorLevNode;
            }
            $vcenterFlag = false;
            if($d['vcenter_flag'] == Xphp::$_config['FLAG']['SET']){
                //如果是vcenter
                $vcenterFlag = true;
            }
            
            //TODO
            $node = array(
                "id" => $d['vcenter_uuid'],
                "pid" => $hypervisorType,
                "name" => $this->getVcenterNameInTree($d['vcenter_uuid'], $hypervisorType, $d['vcenter_flag'], $d['vcenter_ip'], $d['nickname'], $d['vcenter_name'], $d['detail'], $d['username']),
                "isParent" => $vcenterFlag,
                "nocheck" => $vcenterFlag,
                "type" => $vcenterFlag ? 1 : 2,
                "vcenterFlag" => $vcenterFlag,
                "iconSkin" => $this->getHypervisorIcon($hypervisorType, $d['vcenter_flag']),
                "hypervisor" => $d['hypervisor_type'],
                "vcenteruuid" => $d['vcenter_uuid'],
                "open" => $openFlag,
                //                 "chkDisabled" => $this->getHostChkDisabledFlag($proxyHosts, $d['vcenter_uuid'], $d['vcenter_uuid']),
            );
            $tree[] = $node;
            if($vcenterFlag){
                //如果是vcenter还需要得到宿主机
                $hostNode = $this->getVcenterHostInfo($hypervisorType, $d['vcenter_uuid'], $proxyHosts);
                if(!empty($hostNode)){
                    $tree = array_merge($tree, $hostNode);
                }
            }
        }
        
        return json_encode($tree);
    }
    
    /**
     * 得到某个vcenter下的所有宿主机
     * @param unknown $hypervisorType
     * @param unknown $vcuuid
     * @param unknown $chkDisabled      禁用宿主机选中框的宿主机列表(主要是部署代理的时候使用)
     * @return multitype:multitype:boolean NULL unknown string fetchAll()
     */
    private function getVcenterHostInfo($hypervisorType, $vcuuid, $chkDisabledHosts = array()){
        $info = array();
        $sql = "select host_uuid, host_ip, host_name from vm_host where online_flag = ? and vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET'], $vcuuid));
        foreach ($data as $d){
            $name = $d['host_ip'];
            //XenServer
            if(in_array($hypervisorType, Xphp::$_config['VMHYPERVISORGROUP']['xenserver'])){
                $name = $d['host_name'] . ": " . $name;
            }
            
            $node = array(
                "id" => $d['host_uuid'],
                "pid" => $vcuuid,
                "name" => $name,
                "isParent" => false,
                "nocheck" => false,
                "type" => Xphp::$_config['FLAG']['UNSET'],
                "vcenterFlag" => false,
                "iconSkin" => $this->getHypervisorIcon($hypervisorType, Xphp::$_config['FLAG']['UNSET']),
                "hypervisor" => $hypervisorType,
                "vcenteruuid" => $vcuuid,
                "open" => false,
                "chkDisabled" => $this->getHostChkDisabledFlag($chkDisabledHosts, $d['host_uuid'], $vcuuid),
            );
            $info[] = $node;
        }
        return $info;
    }
    
    /**
     * 获取是否禁用某个宿主机的选中框
     * @param array $chkDisabledHosts   禁用列表
     * @param string $hostuuid
     * @param string $vcenteruuid
     */
    private function getHostChkDisabledFlag($chkDisabledHosts, $hostuuid, $vcenteruuid){
        if(empty($chkDisabledHosts)) return false;
        foreach ($chkDisabledHosts as $host){
            if($host['hostuuid'] == $hostuuid && $host['vcenteruuid'] == $vcenteruuid){
                return true;
            }
        }
        return false;
    }
    
    /**
     * 得到vcenter的信息
     * @param string $vcuuid
     * @param int   $showType
     */
    private function getVCenterInfo($vcuuid, $showType){
        if($showType != Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']){
            //如果不是主机和集群模式,直接返回(其他模式树形不要展开)
            return array();
        }
        $sql = "select hypervisor_type, vcenter_flag from vm_vcenter where vcenter_uuid = ? ";
        $data = $this->dbSelect($sql, array($vcuuid));
        $info = array(
            'vcenter_uuid' => $vcuuid,
            'hypervisor_type' => $data[0]['hypervisor_type'],
            'vcenter_flag' => $data[0]['vcenter_flag'],
        );
        return $info;
    }
    
    /**
     * 得到UUID
     * @param unknown $vcenterFlag
     * @param unknown $vcenterUUID
     */
    private function getUUID($vcenterFlag, $vcenterUUID, $vcenterID){
        if($vcenterFlag){
            return $vcenterUUID;
        }
        $sql = "select host_uuid from vm_host where vcenter_id = ?";
        $data = $this->dbSelect($sql, array($vcenterID));
        return $data[0]['host_uuid'];
    }
    
    /**
     * 得到HOSTID号
     * @param bool $vcenterFlag  是否是vcenter
     * @param int $vcenterID    vcenterID号
     */
    private function getHostID($vcenterFlag, $vcenterID){
        if($vcenterFlag){
            return $vcenterID;
        }else{
            $sql = "select  host_id from vm_host where vcenter_id = ?";
            $data = $this->dbSelect($sql, array($vcenterID));
            return $data[0]['host_id'];
        }
    }
    
    /**
     * 虚拟化中心详情
     * 异步获取vcenter信息:先刷新,在从数据库获取
     * @param unknown $params
     * @return string
     */
    public function getSyncVcenter($params){
        $uuid = $params['id'];            //vcenter uuid
        $vcflag = intval($params['vcflag']);    //vcenter 标志
        $hypervisor = intval($params['hypervisor']);
        $showtype = intval($params['showtype']);
        $refresh = $params['refresh'];          //是否刷新
        $this->paramsCheck($uuid, $hypervisor, $showtype);
        $this->refreshVcenterCheck($refresh, $hypervisor, $uuid, $showtype);
        return $this->getTreeFromVMTree($hypervisor, $vcflag, $uuid, $showtype, FALSE, false, '');
        
    }
    
    /**
     * 获取恢复到的宿主机
     * 异步获取vcenter信息:先刷新,在从数据库获取
     * @param unknown $params
     * @return string
     */
    public function getSyncRecoveryVcenter($params){
        $hostuuid = $params['hostuuid'];    //获取虚拟实验室修改宿主机uuid
        $id = $params['id'];
        $pid = intval($params['pid']);
        $nocheck = $params['nocheck'];
        $hideOffline = $params['hideoffline']; //隐藏离线的
        $refresh = $params['refresh'];
        $this->paramsCheck($id, $pid);

        //说明一下,此处pid刚好是子模块号
        $submodule_type = $pid;

        if(in_array($submodule_type, Xphp::$_config['VMHYPERVISORGROUP']['vmware'])){
            //如果是VMware
            $showtype = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_VM'];
            //         }elseif($submodule_type == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER']){
        }else{
            //如果是XenServer
            $showtype = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];
        }

        if($refresh){
            //如果是刷新,刷新后再从数据库取
            // $opName = 'VM_VCENTER_OP_REFLASH';
            $mbResult = $this->refleshVcenter($submodule_type, $id, $showtype);

            $result = $mbResult['result'];
            if(!$result){
                //如果刷新失败,返回错误消息
                // $operate = $this->opcodeHandler->getOpcodeDes($opName);
                $operate = Xphp::$_lang['WEB_VM_VCENTER_SYNC'];
                return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
            }
        }

        $vmTreeTypes = [Xphp::$_config['VM_TREE_TYPE']['DATACENTER'], Xphp::$_config['VM_TREE_TYPE']['CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['HOST']];
        //获取虚拟化类型，hyperv/smartx需特殊处理
        $sql = "select hypervisor_type from vm_vcenter where vcenter_uuid = ?";
        $hypervisor = $this->dbSelect($sql, [$id])[0]['hypervisor_type'];
        if (Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV'] == $hypervisor
            || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SMARTX_KVM'] == $hypervisor) {
            $sql = "select vt.type, vt.name, vt.uuid, vt.parent_uuid, vh.host_name, vh.host_ip, vh.host_uuid, vh.host_id, vh.authorization_flag, vh.online_flag
                from vm_tree vt left join vm_host vh on vt.uuid = vh.host_uuid and vt.type = ? and vt.vcenter_uuid = vh.vcenter_uuid 
                where vt.vcenter_uuid = ? and vt.display_mode = 1 and vt.type in (" . implode(",", $vmTreeTypes) . ") order by vt.type";
            $data = $this->dbSelect($sql, [Xphp::$_config['VM_TREE_TYPE']['HOST'], $id]);
        } else {
            $sql = "select vt.type, vt.name, vt.uuid, vt.parent_uuid, vh.host_name, vh.host_ip, vh.host_uuid, vh.host_id, vh.authorization_flag, vh.online_flag
                from vm_tree vt left join vm_host vh on vt.name = vh.host_name and vt.type = ? and vt.vcenter_uuid = vh.vcenter_uuid 
                where vt.vcenter_uuid = ? and vt.host_uuid != vt.vcenter_uuid and vt.display_mode = 1 and vt.type in (" . implode(",", $vmTreeTypes) . ") order by vt.type";
            $data = $this->dbSelect($sql, [Xphp::$_config['VM_TREE_TYPE']['HOST'], $id]);

            // 树形结构没有主机层则清空不显示
            $hostFlag = false;
            foreach ($data as $item) {
                if (Xphp::$_config['VM_TREE_TYPE']['HOST'] == $item['type']) {
                    $hostFlag = true;
                    break;
                }
            }
            if (!$hostFlag) {
                $data = [];
            }

            $sql = "select 4 as type, host_name as name, host_uuid as uuid, vcenter_uuid as parent_uuid, 
            host_name, host_ip, host_uuid, host_id, authorization_flag, online_flag from vm_host where vcenter_uuid = ?";
            $dataHost = $this->dbSelect($sql, array($id));
            $hostUuids = array_column($data, 'host_uuid');
            foreach ($dataHost as $host) {
                if (!in_array($host['host_uuid'], $hostUuids)) {
                    // 不在树形结构中的主机单独显示
                    $data[] = $host;
                }
            }
        }

        $node = array();

        $tenantHost = "";
        //如果是租户内用户操作，检测是否已配置指定宿主机
        if(!empty($_SESSION['tenantuuid'])){
            $tenantHandler = Xphp::instance('TenantHandler');
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if($settings['recover'] && $settings['recover']['host']){
                $tenantHost = $settings['recover']['host'];
            }
        }


        foreach ($data as $d){
            //指定宿主机
            if(!empty($_SESSION['tenantuuid']) && !empty($tenantHost)){
                if($d['host_uuid'] != $tenantHost) continue;
            }
            if (!$d['host_uuid']) {
                $d['host_uuid'] = $d['uuid'];
            }

            $name = $d['host_ip'] ?: $d['name'];
            if (Xphp::$_config['VM_TREE_TYPE']['HOST'] == $d['type']) {
                //添加离线/在线,授权/未授权标志,先检查在线/离线状态,在线的时候再检查授权/未授权标志
                if($d['online_flag'] == Xphp::$_config['FLAG']['SET']){
                    //在线
                    if($d['authorization_flag'] != Xphp::$_config['FLAG']['SET']){
                        $name = $d['host_ip'] . "(" . Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'] . ")";
                    }
                    if($submodule_type == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
                        $name = $d['host_name'];
                        if($d['authorization_flag'] != Xphp::$_config['FLAG']['SET']){
                            $name = $name . "(" . Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'] . ")";
                        }
                    }
                }else{
                    //离线
                    $name = $d['host_ip'] . "(" . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ")";
                    //                 if($hideOffline) continue; //隐藏离线的
                    if($submodule_type == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM'] || $submodule_type == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']){
                        $name = $d['host_name'] . "(" . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ")";
                    }

                }
            }

            //判断修改虚拟实验室选中宿主机
            $checked = false;
            if($hostuuid == $d['host_uuid']){
                $checked = true;
            }
            $node[] = array(
                "id" => $d['host_uuid'] ?: $d['uuid'],
                "pid" => $d['parent_uuid'],
                "pId" => $d['parent_uuid'],
                "name" => $name,
                "isParent" => Xphp::$_config['VM_TREE_TYPE']['HOST'] != $d['type'],
                "sid" => $d['host_id'],
                "nocheck" => $nocheck || Xphp::$_config['VM_TREE_TYPE']['HOST'] != $d['type'],
                "type" => 2,
                "ip" => $d['host_ip'],
                "hypervisor" => $pid,
                "icon" => "./img/vm/host.png",
                "clickShow" => true,
                //用于点击宿主机的时候获取虚拟机信息后台刷新标志
                "refresh" => false,
                "vcuuid" => $id,
                "chkDisabled" => $this->getOffLine($d['online_flag']),
                "hypervisor_des" => Xphp::$_config['VMHYPERVISORDES'][intval($pid)],
                "checked" => $checked,
                "online_flag" => intval($d['online_flag'])
            );
        }
        array_multisort(array_column($node, 'name'), SORT_ASC, $node);
        $msg = array(
            're' => true,
            'msg' => $node,
        );

        return json_encode($msg);
    }

    /**
     * xhere获取恢复到的集群
     * @param array $params
     * @return string
     */
    public function getSyncRecoveryVcenterForXhere($params){
        $id = $params['id'];
        $pid = intval($params['pid']);
        $nocheck = $params['nocheck'];
        $refresh = $params['refresh'];
        $this->paramsCheck($id, $pid);

        //说明一下,此处pid刚好是子模块号
        $submodule_type = $pid;
        $showtype = Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'];

        if($refresh){
            //如果是刷新,刷新后再从数据库取
            $opName = 'VM_VCENTER_OP_REFLASH';
            $mbResult = $this->refleshVcenter($submodule_type, $id, $showtype);

            $result = $mbResult['result'];
            if(!$result){
                //如果刷新失败,返回错误消息
                $operate = $this->opcodeHandler->getOpcodeDes($opName);
                return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
            }
        }

        $sql = "select vt.type, vt.name, vt.uuid, vt.parent_uuid
                from vm_tree vt join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                where vt.vcenter_uuid = vv.vcenter_uuid and vt.vcenter_uuid = ? and vt.display_mode = ? and vt.type = ? order by vt.type";
        $sqlParams = [
            $id,
            Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'],
            Xphp::$_config['VM_TREE_TYPE']['CLUSTER'],
        ];
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        $checked = false;
        foreach ($data as $d){
            $node[] = array(
                "id" => $d['uuid'],
                "pid" => $d['parent_uuid'],
                "name" => $d['name'],
                "isParent" => false,
                "sid" => $d['host_id'],
                "nocheck" => Xphp::$_config['VM_TREE_TYPE']['CLUSTER'] == $d['type'] ? false : $nocheck,
                "type" => 2,
                "ip" => $d['host_ip'],
                "hypervisor" => $pid,
                "icon" => "./img/vm/cluster.png",
                "clickShow" => true,
                //用于点击宿主机的时候获取虚拟机信息后台刷新标志
                "refresh" => false,
                "vcuuid" => $id,
                "chkDisabled" => false,
                "hypervisor_des" => Xphp::$_config['VMHYPERVISORDES'][intval($pid)],
                "checked" => $checked
            );
        }
        array_multisort(array_column($node, 'name'), SORT_ASC, $node);
        $msg = array(
            're' => true,
            'msg' => $node,
        );

        return json_encode($msg);
    }
    
    /**
     * 得到vcenter添加的时候的用户名和密码
     * @param unknown $vcenteruuid  string
     */
    private function getVcenterUserAndPassword($vcenteruuid){
        $this->paramsCheck($vcenteruuid);
        $sql = "select username, password from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        $info = array(
            "username" => $data[0]['username'],
            "password" => $data[0]['password']
        );
        return $info;
    }
    
    /**
     * 得到某个虚拟化中心下的用户分组,现在用于:flexcloud,openstack
     * @param unknown $params
     */
    public function getSyncVcenterGroup($params){
        $vcenteruuid = $params['vcenteruuid'];
        $hypervisor = intval($params['hypervisor']);
        $refresh = boolval($params['refresh']);
        $iconskin = Xphp::$_config['VIRTUALIZATIONICONCLASSNAME'][$hypervisor];
        $this->paramsCheck($vcenteruuid, $hypervisor);
        
        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_QUERY_OPENSTACK_USER_GROUP';
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        //组合消息
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
        );
        
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $userInfo = $this->getVcenterUserAndPassword($vcenteruuid);

        if ($refresh) {
            $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg), true);
            $result = $mbResult['result'];
            if(!$result){
                return $this->muOpResult(false, $operate, '', '', $mbResult['errorCode']);
            }
            $groupList = $mbResult['msg']['user_group_list'];
        } else {
            $result = true;
            $groupList = array();
//            $sql = "select name, uuid from vm_tree where parent_uuid = ? order by name";
            $sql = "select host_name name, host_uuid uuid from vm_host where vcenter_uuid = ? order by name";
            $data = $this->dbSelect($sql, array($vcenteruuid));
            foreach ($data as $d) {
                $groupList[] = array(
                    'group_name' => $d['name'],
                    'group_uuid' => $d['uuid'],
                    'user_list' => array($userInfo['username'])
                );
            }
        }

        $userList = array();
        foreach ($groupList as $group){
            $username = '';
            $password = '';
            if(in_array($userInfo['username'], $group['user_list'])){
                //如果添加虚拟化中心的用户,在某个分组下面,用户名密码就用这个用户的,如果没有,就需要用户输入
                $username = $userInfo['username'];
                $password = $userInfo['password'];
            }
            $userList[] = array(
                "type" => 2,
                "isParent" => false,
                "vcuuid" => $vcenteruuid,
                "pid" => $vcenteruuid,
                "hypervisor" => $hypervisor,
                "name" => $group['group_name'],
                "groupuuid" => $group['group_uuid'],
                "groupusers" => $group['user_list'],
                "username" => $username,
                "password" => $password,
                "iconSkin" => $iconskin."_folder_vm",
                //                 "icon" => "./img/vm/host.png",
            );
        }
        $msg = array(
            're' => $result,
            'msg' => $userList,
        );
        
        return json_encode($msg);
    }
    
    /**
     * 验证vcenter下某个分组下的用户的用户名,密码是否正确
     * @param unknown $parmas
     */
    public function veryfyVcenterGroupUser($params){
        $vcenteruuid = $params['vcenteruuid'];
        $hypervisor = $params['hypervisor'];
        $groupname = $params['groupname'];
        $groupuuid = $params['groupuuid'];
        $username = $params['username'];
        $password = base64_decode($params['password']);
        $this->paramsCheck($vcenteruuid, $groupname, $username, $password);
        
        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_TEST_OPENSTACK_RECOVERY_USER_CONNECTION';
        //组合消息
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password
        );
        
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg));
        
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 得到某台宿主机下所有虚拟机
     * @param unknown $params
     */
    public function getVcenterDetailVms($params){
        $start = $params['start'];
        $length = $params['length'];
        $draw = $params['draw'];
        $vcenteruuid = $params['vcenteruuid'];  //虚拟化中心uuid
        $hypervisor = $params['hypervisor'];    //虚拟化类型
        $showtype = $params['showtype'];        //显示模式
        $nodeuuids = $params['node'];           //所有父节点
        $name = $params['name'];
        $vcenterFlag = $params['vcenterflag'];  //是否是vcenter
        $this->paramsCheck($vcenteruuid, $hypervisor, $showtype, $nodeuuids);
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'vt.name', 'vt.power_state');
        
        //         $uuidStr = implode("','", $nodeuuids);
        $uuidStr = $this->getParentUUIDNodeStr($vcenterFlag, $vcenteruuid, $name, $showtype, $nodeuuids);
        $sql = "select vt.name, vt.uuid, vt.conn_state, vt.power_state, vt.dir_path from vm_tree vt 
                join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                left join mt_user_resource mur on vt.uuid = mur.vm_uuid and mur.resource_type = 3 
                where vt.vcenter_uuid = ? and vt.display_mode = ? and vt.type = ?
                and vt.parent_uuid in ('" . $uuidStr . "')";
        $sqlCount = "select count(vt.name) as total from vm_tree vt 
                     join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                     left join mt_user_resource mur on vt.uuid = mur.vm_uuid and mur.resource_type = 3 
                     where vt.vcenter_uuid = ? and vt.display_mode = ? and vt.type = ?
                     and vt.parent_uuid in ('" . $uuidStr . "')";
        
        $sqlParams = array($vcenteruuid, $showtype, Xphp::$_config['VM_TREE_TYPE']['VM']);
        $sqlCountParams = array($vcenteruuid, $showtype, Xphp::$_config['VM_TREE_TYPE']['VM']);

        if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
            // 超级管理查看所有的资源
        } elseif ($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['sysadmin']) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        }else{
            // 关联管理用户判断 存储资源 - 查看
            $authUser = $_SESSION['authUser']['resmanagement_look'] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and (vv.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
                $sqlCount .= " and (vv.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
            } else {
                $userUuid = Xphp::$_user['useruuid'];
                $sql .= " and (vv.user_uuid = ? or mur.user_uuid = ?) ";
                $sqlCount .= " and (vv.user_uuid = ? or mur.user_uuid = ?) ";
                $sqlParams = array_merge($sqlParams,array($userUuid, $userUuid));
                $sqlCountParams = array_merge($sqlCountParams,array($userUuid, $userUuid));
            }
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ?";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $vmInfo = $this->getTenantBackupVMInfo();
        $vms = array();
        $i = 1;
        $vmDes = require APP_PATH . "/vm/VmDescription.php";
        foreach ($data as $d){
            $vmBackupFlag = $this->getVMInBackupFlag(Xphp::$_config['VM_TREE_TYPE']['VM'], $d['uuid'], $vcenteruuid, $vmInfo);
            $vms[] = array(
                '<input type="checkbox" name="id[]" value="' . $d['uuid'] . '">',
                $i,
                $d['name'],
                $vmDes['VmMachineStatus'][intval($d['power_state'])],
                $this->getVMOpcode($d['power_state'], $vmBackupFlag),
                array(
                    'vmuuid' => $d['uuid'],
                    'vcenterid' => $vcenteruuid,
                    'vmname' => $d['name'],
                    'backupflag' => $vmBackupFlag,
                    'vmstatus' => intval($d['power_state'])
                ),
            );
            $i++;
        }
        $records["data"] = $vms;
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到某个虚拟化中心的所有任务
     * @param unknown $params
     */
    public function getVcenterCurrentTask($params){
        $vcenteruuid = $params['vcenteruuid'];
        $sql = "select bt.task_uuid,bt.task_name from bd_task bt,vm_machine_list vml
            where bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = ? and bt.task_type = ? ";
        $sqlParams = array($vcenteruuid, Xphp::$_config['TASKTYPE']['BACKUP']);
        // 查看权限
        if ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9' != Xphp::$_user['useruuid']) {
            $authUser = $_SESSION['authUser']['vmprotect_look'] ?? [];
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bt.user_uuid in " . $useruuidArr;
        }

        $sql .= " group by bt.task_uuid order by bt.task_name";
        $data = $this->dbSelect($sql, $sqlParams);
        $taskInfo = array();
        foreach ($data as $d){
            $taskInfo[] = array(
                'uuid' => $d['task_uuid'],
                'name' => $d['task_name'],
            );
        }
        return json_encode($taskInfo);
    }
    
    /**
     * 添加虚拟机到备份任务
     * @param unknown $params
     */
    public function addVmToBackupTask($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vm_overview_vmoperate");
        $vmuuid = $params['vmuuid'];
        $vcenteruuid = $params['vcenteruuid'];
        $taskuuid = $params['taskuuid'];
        $displaymode = $params['displaymode'];
        $this->paramsCheck($vmuuid, $vcenteruuid, $taskuuid, $displaymode);
        $vmuuidArr = explode(',',$vmuuid);
        $vmuuidStr = implode("','", $vmuuidArr);
        //检测授权
        $operate = Xphp::$_lang['WEB_VM_VCENTER_ADD_VM_TO_TASK'];
        $vmHandler = Xphp::instance('Vmhandler');
        $vmHandler->checkTaskLegal($operate, array($vmuuid), null);
        //检测任务是否存在
        $sql = "select id, task_status from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        
        //检查任务是否存在
        if(empty($data)){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_VCENTER_ADD_VM_TO_TASK_NOTASK'], 'warning'));
        }
        //任务运行中不允许添加
        if(intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['RUNNING']){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_VCENTER_ADD_VM_TASK_RUNNING_ERROR'], 'warning'));
        }
        
        //检测虚拟机是否存在
        $sql = "select * from vm_tree where vcenter_uuid = ? and uuid in ('". $vmuuidStr ."') and display_mode = ?";
        $vmInfo = $this->dbSelect($sql, array($vcenteruuid, $displaymode));
        if(!$vmInfo){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_VCENTER_ADD_VM_TO_TASK_NOVM'], 'warning'));
        }
        //检测虚拟机是否已经在任务中
        $sql = "select machine_id from vm_machine_list where vcenter_uuid = ? and vm_uuid in ('". $vmuuidStr ."') and task_uuid = ?";
        $data = $this->dbQuery($sql, array($vcenteruuid, $taskuuid));
        if($data > 0){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_VM_VCENTER_ADD_VM_TO_TASK_INTASK'], 'warning'));
        }
        foreach($vmInfo as $info){
            //插入数据库
            $sql = "insert vm_machine_list (task_uuid, vcenter_uuid, vm_uuid, vm_name, version, host_uuid, dir_path, task_status)
            values (?, ?, ?, ?, ?, ?, ?, ?)";
            $sqlParams = array($taskuuid, $vcenteruuid, $info['uuid'], $info['name'], $info['version'],
                $info['host_uuid'], $info['dir_path'], Xphp::$_config['VmTaskStatus']['NEW_ADD']);
            $result = $this->dbQuery($sql, $sqlParams);
            //插入vm_object_list
            $sql2 = "insert vm_object_list (task_uuid, object_uuid, vcenter_uuid, type, exclude_vm_uuid_list, object_name, vm_config, dir_path) 
            values (?, ?, ?, ?, ?, ?, ?, ?)";
            $sql2Params = array($taskuuid, $info['uuid'], $vcenteruuid, Xphp::$_config['VM_TREE_TYPE']['VM'],
                '', $info['name'], json_encode(['disk_list' => []], JSON_UNESCAPED_UNICODE), $info['dir_path']);
            $result = $result && $this->dbQuery($sql2, $sql2Params);
        }
        return $this->muOpResult(!!$result, $operate);
    }
    
    /**
     * 得到父节点的uuid字符串
     * @param boolean $vcenterFlag
     * @param string  $vcenteruuid
     * @param array $nodes
     */
    private function getParentUUIDNodeStr($vcenterFlag, $vcenteruuid, $name, $showtype, $nodes){
        if(!$vcenterFlag){
            //如果是宿主机,查找宿主机的uuid,替换$nodes里面的vcenteruuid
            $sql = "select uuid from vm_tree where vcenter_uuid = ? and parent_uuid = ?  and display_mode = ?
                    and type != ?";
            $sqlParams = array($vcenteruuid, $vcenteruuid, $showtype, Xphp::$_config['VM_TREE_TYPE']['VM']);
            $data = $this->dbSelect($sql, $sqlParams);
            if($data){
                $hostuuid = $data[0]['uuid'];
                foreach ($nodes as $key => $value){
                    if($value == $vcenteruuid){
                        $nodes[$key] = $hostuuid;
                        break;
                    }
                }
            }
        }
        
        $uuidStr = implode("','", $nodes);
        return $uuidStr;
    }
    
    /**
     * 根据虚拟机状态得到虚拟机可以进行的操作
     * @param int $status   1关机、2启动、3暂停、4添加到备份任务
     * @return array
     */
    private function getVMOpcode($status, $vmBackupFlag){
        $status = intval($status);
        $opArr = array();
        switch($status){
            case Xphp::$_config['MACHINESTATUS']['POWEREDOFF']:
                $opArr = array(2);
                break;
            case Xphp::$_config['MACHINESTATUS']['POWEREDON']:
                $opArr = array(3, 1);
                break;
            case Xphp::$_config['MACHINESTATUS']['SUSPENDED']:
                $opArr = array(2);
                break;
            case Xphp::$_config['MACHINESTATUS']['PAUSE']:
                $opArr = array(2);
                break;
        }
        if(!$vmBackupFlag){
            $opArr[] = 4;
        }
        
        return $opArr;
    }
    
    /**
     * 根据vcenter ID 得到虚拟化子模块类型
     * @param unknown $vcenterID
     * @return string
     */
    private function getVcenterSubModule($vcenterID){
        $sql = "select hypervisor_type from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenterID));
        if($data){
            return $data[0]['hypervisor_type'];
        }
        $this->writeLog('VM_VCENTER_GET_SUB_MODULE_ERROR', Xphp::$_config['LOG_INFO']['WARNING']);
        return FALSE;
    }
    
    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param int $submodule_type
     * @param string $opName
     * @param json $msg
     * @return string
     */
    private function unifyMsg($submodule_type, $opName, $jsonMsg){
//         if($opName == "VM_VCENTER_OP_DELETE"){
//             $list = json_decode($jsonMsg, true);
//             $id = $list['vcenter_uuid_list'][0];
//             $sql = "select vcenter_ip, nickname from vm_vcenter where vcenter_uuid = ?";
//             $data = $this->dbSelect($sql, array($id));
//         }
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getvCenterUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, intval($submodule_type), $opName, $jsonMsg);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //后台已经记录日志
//         //写系统日志
//         if($opName == 'VM_VCENTER_OP_MODIFY'){
//             $this->writeModifyLog($result, json_decode($jsonMsg, true), $mbResult['errorCode']);
//         }else if ($opName == 'VM_VCENTER_OP_DELETE'){
//             $this->writeDeleteLog($result, $data, $mbResult['errorCode']);
//         }
        
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 启动虚拟机
     * @param unknown $params
     */
    public function startMachine($params){
        return $this->machinOpUnify($params, 'VM_MACHINE_OP_POWERON');
    }
    
    /**
     * 挂起虚拟机
     * @param unknown $params
     */
    public function pauseMachine($params){
        return $this->machinOpUnify($params, 'VM_MACHINE_OP_SUSPEND');
    }
    
    /**
     * 停止虚拟机
     * @param unknown $params
     */
    public function stopMachine($params){
        return $this->machinOpUnify($params, 'VM_MACHINE_OP_POWEROFF');
    }
    
    /**
     * 虚拟机统一操作
     * @param unknown $params
     * @param unknown $opName
     * @return string
     */
    private function machinOpUnify($params, $opName){
        $vmuuid = $params['vmuuid'];
        $vmname = $params['vmname'];
        $vcenterid = $params['vcenterid'];
        $hypervisor = $params['hypervisor'];
        $this->paramsCheck($vmuuid, $vmname, $vcenterid, $hypervisor);
        
        
        $msg = json_encode(array("vm_uuid" => $vmuuid, "vm_name" => $vmname, "vcenter_uuid" => $vcenterid));
        return $this->unifyMsg($hypervisor, $opName, $msg);
    }
    
    /**
     * 根据虚拟化类型得到树图标
     * @param int $hypervisor
     * @param mixed $vcenter_flag 是否是VCENTER
     * @param liushuai@vinchin.com
     */
    public function getHypervisorIcon($hypervisor, $vcenter_flag = true){
        $hypervisor = intval($hypervisor);
        $this->paramsCheck($hypervisor);
        $iconskin = Xphp::$_config['VIRTUALIZATIONICONCLASSNAME'][$hypervisor];
        if($vcenter_flag == Xphp::$_config['FLAG']['UNSET']){
            //如果不是vcenter
            return $iconskin."_host";
        }else if($vcenter_flag == Xphp::$_config['FLAG']['SET']){
            return $iconskin."_vcenter";
        }else if($vcenter_flag == 3){
            return $iconskin."_hostcluster";
        } else if ($vcenter_flag == 4) {
            return $iconskin."_folder_vm";
        }
        
        return $iconskin;
    }
    
      /**
     * 根据节点类型和状态得到节点的图标 vm_tree
     * @param int $type         节点类型
     * @param int $connState    连接状态
     * @param int $powerState   开机状态
     * @return string $icon
     * @author liushuai@vinchin.com
     */
    public function getTreeTypeIcon($hypervisor,$type, $connState, $powerState){
        $iconskin = Xphp::$_config['VIRTUALIZATIONICONCLASSNAME'][$hypervisor];
        $type = intval($type);
        $connState = intval($connState);
        $powerState = intval($powerState);
        $icon = '';
        switch ($type){
            case Xphp::$_config['VM_TREE_TYPE']['FOLDER']:
                $icon = $iconskin.'_folder_vm';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['DATACENTER']:
                $icon = $iconskin.'_datacenter';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['CLUSTER']:
                $icon = $iconskin.'_cluster';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['HOST']:
                $icon = $this->getHostStateIcon($connState,$hypervisor);
                break;
            case Xphp::$_config['VM_TREE_TYPE']['POOL']:
                $icon = $iconskin.'_pool';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['VAPP']:
                $icon = $iconskin.'_vapp';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['VM']:
                $icon = $this->getVmStateIcon($connState, $powerState,$hypervisor);
                break;
            case Xphp::$_config['VM_TREE_TYPE']['OVIRT_FAKE_HOST_FOLDER']:
                $icon = $iconskin.'_host';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['OVIRT_FAKE_VM_FOLDER']:
                $icon = $iconskin.'_vm';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['OVIRT_CLUSTER_FOLDER']:
                $icon = $iconskin.'_cluster';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['OVIRT_CLUSTER']:
                $icon = $iconskin.'_cluster';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['OVIRT_DATACENTER']:
                $icon = $iconskin.'_datacenter';
                break;
            case Xphp::$_config['VM_TREE_TYPE']['TEMPLATE']:
                $icon = $iconskin.'_template';
                break;
            default:
                break;
        }
        return $icon;
    }
    
    /**
     * 根据连接状态得到宿主机图标 vm_tree 
     * @param int $connState
     * @author liushuai@vinchin.com
     */
    private function getHostStateIcon($connState,$hypervisor){
        $iconskin = Xphp::$_config['VIRTUALIZATIONICONCLASSNAME'][$hypervisor];
        $connState = intval($connState);
        switch ($connState){
            case Xphp::$_config['HOSTSTATUS']['CONNECT']:
                $icon = $iconskin.'_host_on';
                break;
            case Xphp::$_config['HOSTSTATUS']['DISCONNECT']:
                $icon = $iconskin.'_host_off';
                break;
            default:
                $icon = $iconskin.'_host';
                break;
        }
        return $icon;
    }
    
    /**
     * 根据连接状态和虚拟机状态得到虚拟机图标 vm_tree  LS
     * @param int $connState
     * @param int $powerState
     */
    private function getVmStateIcon($connState, $powerState,$hypervisor){
        $iconskin = Xphp::$_config['VIRTUALIZATIONICONCLASSNAME'][$hypervisor];
        $connState = intval($connState);
        $powerState = intval($powerState);
        if($connState == Xphp::$_config['VmMachineConnectState']['DISCONNECTED'] ||
            $connState == Xphp::$_config['VmMachineConnectState']['COMPLETED']){
                //虚拟机处于离线状态,不可用
                return $iconskin.'_vm_disable';
        }
        return $this->getVMStatusIcon($powerState,$hypervisor);
    }
    
    /**
     * 得到授权状态描述
     * @param int $flag
     */
    private function getAuthorizationStatusDes($flag){
        $des = Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'];
        if($flag == Xphp::$_config['FLAG']['SET']){
            $des = Xphp::$_lang['WEB_SYSTEM_AUTHIORIZED'];
        }
        return $des;
    }
    
    /**
     * 得到宿主机状态描述
     * @param int $flag
     */
    private function getHostStatusDes($flag){
        $des = Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'];
        if($flag == Xphp::$_config['FLAG']['SET']){
            $des = Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'];
        }
        return $des;
    }
    /**
     * 得到某个vcenter下面所有宿主机的信息,主要是授权情况
     * @param unknown $params
     */
    public function getVcenterHostLisenceInfo($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $vcenteruuid = $params['vcenteruuid'];
        $sortArr = array('', 'vh.host_name', 'vh.host_ip', 'vv.vcenter_ip', 'vh.online_flag', 'cpu_count', 'vh.authorization_flag');
        $draw = $params['draw'];
        $sql = "select vh.host_name, vh.host_uuid, vh.host_ip, vh.authorization_flag, vh.cpu_count,vh.online_flag,
                vv.vcenter_ip, vv.hypervisor_type, vv.nickname, vv.vcenter_uuid
                from vm_host vh, vm_vcenter vv
                where vh.vcenter_uuid = vv.vcenter_uuid and vh.vcenter_uuid = ?";
        $sqlCount = "select count(vh.host_name) as total from vm_host vh, vm_vcenter vv
                where vh.vcenter_uuid = vv.vcenter_uuid and vh.vcenter_uuid = ?";
        $sqlParams = array($vcenteruuid, $start, $length);
        $sqlCountParams = array($vcenteruuid);
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array("data" => array());
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['host_uuid'] .'">',
                $d['host_name'],
                $d['host_ip'],
                $d['vcenter_ip'] == $d['nickname'] ? $d['vcenter_ip'] : $d['nickname'] . "(" . $d['vcenter_ip'] . ")",
                $this->getHostStatusDes($d['online_flag']),
                $d['cpu_count'],
                $this->getAuthorizationStatusDes($d['authorization_flag']),
                intval($d['authorization_flag']),
                intval($d['online_flag'])
            );
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $dataCount[0]['total'];
        $records["recordsFiltered"] = $dataCount[0]['total'];
        
        return  json_encode($records);
        
    }
    
    /**
     * 从配置文件得到系统支持的所有虚拟化类型
     * @param unknown $params
     */
    public function getAllHypervisorType($params){
        $tenantFlag = $params['tenantFlag'];   //租户内部
        $userFlag = $params['userFlag'];    //用户拥有
        $cloudFlag = $params['cloudFlag']; //是否是云平台
        $allTypeFlag = $params['allTypeFlag']; //获取虚拟化+云平台
        $systemHandler = Xphp::instance('SystemHandler');
        $extension = $systemHandler->getExtensionLicense();
        $list = array();
        if(!empty($extension)){
            $list = $extension['v'];
        }else{
            //未授权出厂虚拟化
            if((Xphp::$_config['RELEASE_HYPERVISOR'])){
                //如果存在指定选择需要的虚拟化 直接应用
                $list = Xphp::$_config['RELEASE_HYPERVISOR'];
            }
        }
        //$list[] = 52; //todo:新增虚拟化调试使用
        $vendor = include CONF_PATH . 'vendor.php';
        $hypervisor = $cloudFlag ? $vendor['CLOUDHYPERVISORTYPE'] : $vendor['VMHYPERVISORTYPE'];
        if(!empty($list)){
            foreach ($hypervisor as $key => $value){
                foreach ($list as $h){
                    if ($h == $key) {
                        $hypervisors[$key] = $value;
                    }
                }
            }
        }else{
            $hypervisors = $hypervisor;
        }
        if ($allTypeFlag) {
            $hypervisors = $hypervisors + $vendor['CLOUDHYPERVISORTYPE'];
        }
        $info = array();
        $vmHypervisor = $this->getTenantHypervisor($_SESSION['tenantuuid']);
        $userHypervisor = $this->getUserHypervisor(Xphp::$_user['useruuid']);
        foreach ($hypervisors as $key => $value){
            if($tenantFlag && !in_array($key, $vmHypervisor)) continue;
            if($userFlag && !in_array($key, $userHypervisor)) continue;
            $info[] = array(
                'text' => $value,
                'value' => $key
            );
        }
        return json_encode($info);
    }

    /**
     * 得到虚拟化中心版本号
     * @param string $hypervisor
     * @param string $version
     */
    public function getVcentversion($hypervisor, $version){
        if($version == null || Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XFUSION_KVM'] == $hypervisor){
            $version = Xphp::$_config['NULLSPACE'];
        }
        return $version;
    }
    
    /**
     * 如果主机离线禁用checkbox
     * @param int $online_flag
     */
    public function getOffline($online_flag){
        if($online_flag != Xphp::$_config['FLAG']['SET']){
            return true;
        }
        return false;
    }
    
    /**
     * openstack获取vcenter名字
     * @param string $ip
     * @param string $detail
     * @param int $hypervisor
     *
     */
    public function getVcenterIp($ip, $detail, $hypervisor, $username){
        $detail =json_decode($detail, true);
        if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])){
                $ip = $ip . '(' . $username . ')';
        }
        return $ip;
    }
    
    /**
     * 测试Openstack控制IP连接
     * @param array $params
     *
     */
    public function testControllerIP($params){
        $controllerIP = $params['controller_ip'];
        $hypervisor = $params['hypervisor'];
        $this->paramsCheck($controllerIP, $hypervisor);
        
        //测试连接操作码
        $opcodeName = 'VM_VCENTER_OP_TEST_CONTROLLER_IP';
        //组合消息
        $msg = array(
            'controller_ip' => $controllerIP,
        );
        
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    
    /**
     * 获取已添加虚拟化中心列表
     * @param unknow $params
     *
     */
    public function getVcenterList($params){
        $sql = 'select vcenter_uuid, vcenter_ip, vcenter_name from vm_vcenter where 
                    hypervisor_type not in (' . implode(',', Xphp::$_config['VMHYPERVISORGROUP']['publiccloud']) . ')';

        $data = $this->dbSelect($sql);
        $info = array();
        foreach($data as $d){
            $info[] = array(
                "vcenteruuid" => $d['vcenter_uuid'],
                "vcenterip" => $d['vcenter_ip']
            );
        }

        return json_encode($info);
    }
    
    /**
     * 获取某个虚拟机的磁盘列表
     * @param unknown $params
     */
    public function getVMDiskList($params){
        $vmuuid = $params['vmuuid'];
        $vcenteruuid = $params['vcenteruuid'];
        $hypervisor = $params['hypervisor'];
        //获取磁盘信息操作码
        $opcodeName = 'VM_VCENTER_OP_GET_VM_DISK_LIST';
        //组合消息
        $msg = array(
            'vm_uuid' => $vmuuid,
            'vcenter_uuid' => $vcenteruuid
        );
        
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg), TRUE);
        $info =array();
        if($mbResult['result']){
            $info = $mbResult['msg'];
        }
        return json_encode($info);
    }
    
    /**
     * 获取hyperv宿主机显示名字
     * @param string $vcenteruuid
     * @param string $name
     */
    private function getHyperVHostName($vcenteruuid, $name){
        $sql = "select vcenter_name, vcenter_flag from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        if($data[0]['vcenter_flag'] == Xphp::$_config['FLAG']['UNSET']){
            $name = $data[0]['vcenter_name'];
        }
        return $name;
    }
    
    /**
     * 平台备份测试连接虚拟化平台
     * @param unknown $params
     */
    public function testEngine($params){
        $username =$params['username'];
        $password = base64_decode($params['password']);
        $hypervisor = $params['hypervisor'];
        $ip = $params['ip'];
        //测试连接操作码
        $opcodeName = 'VM_VCENTER_OP_ENGINE_TEST';
        //组合消息
        $msg = array(
            'ip' => $ip,
            'port' => 22,
            'username' => $username,
            'password' => $password
            
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg), FALSE);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 获取平台备份数据列表
     * @param unknown $params
     * @return string
     */
    public function getEngineData($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'vpmb.vcenter_ip', 'vpmb.vcenter_nickname', 'vv.hypervisor_type', 'vpmb.backup_time', 'vpmb.size', 'vpmb.node_uuid', 'vpmb.storage_uuid', '');
        $sql = "select vv.hypervisor_type, vpmb.data_uuid, vpmb.vcenter_ip, vpmb.vcenter_uuid, vpmb.vcenter_nickname, unix_timestamp(vpmb.backup_time) backup_time, vpmb.size, vpmb.extra_info, vpmb.path,
        bn.ip as node_ip, bn.node_nickname, bn.host_name, bsr.storage_nickname from vm_platform_meta_backup_data vpmb, bd_node bn, bd_storage_resource bsr, vm_vcenter vv where vpmb.vcenter_uuid = vv.vcenter_uuid and vpmb.node_uuid = bn.node_uuid and vpmb.storage_uuid = bsr.storage_uuid ";
        $sqlCount = "select count(vpmb.data_uuid) as total from vm_platform_meta_backup_data vpmb, bd_node bn, bd_storage_resource bsr, vm_vcenter vv where vpmb.vcenter_uuid = vv.vcenter_uuid and vpmb.node_uuid = bn.node_uuid and vpmb.storage_uuid = bsr.storage_uuid ";
        
        //精确搜索
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $datas =$this->dbSelect($sql, array($start, $length));
        $count = $this->dbSelect($sqlCount, array());
        
        $utils = Xphp::instance("Utils");
        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $nodeHandler = Xphp::instance('NodeHandler');
        if(!empty($datas)){
            foreach ($datas as $data){
                $records["data"][] = array(
                    '<input type="checkbox" name="id[]" value="'. $data['data_uuid'] .'">',
                    $id,
                    $data['vcenter_ip'],
                    $data['vcenter_nickname'],
                    Xphp::$_config['VMHYPERVISORDES'][intval($data['hypervisor_type'])],
                    $this->parseDate($data['backup_time']),
                    $utils->calSize($data['size']),
                    $nodeHandler->getNodeGridName($data['node_ip'], $data['node_nickname'], $data['host_name']),
                    $data['storage_nickname'],
                    $data['path'],
                    intval($data['size']),
                    $data['data_uuid']
                );
                $id++;
            }
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return  json_encode($records);
    }
    
    /**
     * 得到平台备份数据管理的数据条数,用于是否展示虚拟化中心相对应的按钮
     */
    public function getEngineCount(){
        $sql = "select count(vpmb.data_uuid) as total from vm_platform_meta_backup_data vpmb, bd_node bn, bd_storage_resource bsr, vm_vcenter vv where vpmb.vcenter_uuid = vv.vcenter_uuid and vpmb.node_uuid = bn.node_uuid and vpmb.storage_uuid = bsr.storage_uuid";
        $result = $this->dbSelect($sql);
        return json_encode($result[0]['total']);
    }
    
    /**
     * 删除平台数据
     * @param unknown $params
     */
    public function deleteEngineData($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_backup");
        $uuids = $params['list'];
        //删除平台数据操作码
        $opcodeName = 'VM_PRIVATE_TASK_OP_PF_BAKCUP_DELETE_TIMEPOINT';
        //组合消息
        $msg = array(
            'data_uuid_list' => $uuids
            
        );
        
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_RHV_KVM'], $opcodeName, json_encode($msg), FALSE);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 下载平台备份文件
     * @param unknown $params
     */
    public function downloadEngineFile($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_backup");
        $filepath = $_GET['path'];
        $filesize = $_GET['size'];
        $filename = $_GET['name'];
        $filename = str_replace(" ", "_", $filename);
        $datauuid = $_GET['uuid'];
        
        $this->paramsCheck($filepath, $filesize, $filename);
        
        $sql = "select vv.hypervisor_type, vpmb.node_uuid from vm_vcenter vv, vm_platform_meta_backup_data vpmb where vpmb.vcenter_uuid = vv.vcenter_uuid and vpmb.data_uuid = ?";
        $data = $this->dbSelect($sql, array($datauuid));
        $hypervisor = $data[0]['hypervisor_type'];
        
        $nodeuuid = $data[0]['node_uuid'];
        $opName = 'VM_PRIVATE_TASK_OP_PF_BACKUP_DOWNLOAD_BLOCK_FILE_BY_OFFSET';
        
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $filesize);
        Header("Content-Disposition: attachment; filename= Engine_Backup_". $filename);
        $blockSize = Xphp::$_config['GRAIN_FILE_BLOCK_SIZE'];
        //如果大于分块大小,分块下载
        for($i=0; $i<$filesize; $i = $i + $blockSize){
            if($filesize - $i <= $blockSize){
                $readLen = $filesize - $i;
            }else{
                $readLen = $blockSize;
            }
            
            $msg = array(
                "data_uuid" => $datauuid,
                "offset" => $i,
                "len" => $readLen,
            );
            $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opName, json_encode($msg), true);
            echo  $mbResult;
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }
    }
    
    private function writeModifyLog($result, $msg, $erroCode){
        $descriptionParam = array($msg['nickname'], $msg['ip'], $msg['nickname']);
        if($result){
            $this->systemLog('VM_SYSTEMLOG_DESC_KEY_MODIFY_VCENTER_SUCCESS', $descriptionParam);
        }else{
            $this->systemLog('VM_SYSTEMLOG_DESC_KEY_MODIFY_VCENTER_SUCCESS', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $erroCode);
        }
    }
    
    private function writeDeleteLog($result, $data, $erroCode){
        $descriptionParam = array($data[0]['nickname'], $data[0]['vcenter_ip']);
        if($result){
            $this->systemLog('VM_SYSTEMLOG_DESC_KEY_DELETE_VCENTER_SUCCESS', $descriptionParam);
        }else{
            $this->systemLog('VM_SYSTEMLOG_DESC_KEY_DELETE_VCENTER_SUCCESS', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $erroCode);
        }
    }
    
    
    /**
     * 获取静默快照flag
     * @param string $vcenteruuid
     */
    public function getSnapshotFlag($vcenteruuid){
        $flag = false;
        $sql = "select hypervisor_type, version from vm_vcenter where vcenter_uuid = ? ";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        if(!empty($data)){
            $hypervisor = intval($data[0]['hypervisor_type']);
            $version = $data[0]['version'];
            if($hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XENSERVER'] || $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_XCP_NG']){
                $value = intval(substr($version,0,1));
                if($value >= 8){
                    $flag = true;
                }
            }
        }
        
        return $flag;
    }
    
    
    /**
     * 获取任务中的虚拟机列表
     * @param unknown $taskuuid
     */
    private function getTaskVmList($taskuuid){
        $sql2 ="select vol.object_uuid, vol.vcenter_uuid from vm_object_list vol where vol.task_uuid = ?";
        $data = $this->dbSelect($sql2, array($taskuuid));
        $list = array();
        $vcenter_uuid = '';
        if(!empty($data)){
            foreach ($data as $d){
                $list[] = $d['object_uuid'];
            }
            $vcenter_uuid = $data[0]['vcenter_uuid'];
        }

        return ['list' => $list, 'vcenter_uuid' => $vcenter_uuid];
    }
    
    /**
     * 通过IP获取虚拟化中心uuid
     * @param string $ip
     */
    private function getVcenterUUIDbyIP($ip){
        $sql  = "select vcenter_uuid from vm_vcenter where vcenter_ip = ?";
        $data = $this->dbSelect($sql, array($ip));
        $uuid = "";
        if(!empty($data)){
            $uuid = $data[0]['vcenter_uuid'];
        }
        
        return $uuid;
    }
    
    /**
     * 根据虚拟化中心唯一标识获取虚拟化中心IP和虚拟化类型
     * @param string $vcenteruuid
     * @return string
     */
    public function pGetVcenterIPByUUID($vcenteruuid){
        $sql = "select vcenter_ip, detail, hypervisor_type, username from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        $vcenterIp = "";
        $hypervisor = 1;    //默认vmware
        $info = array();
        if(!empty($data)){
            $vcenterIp = $this->getVcenterIp($data[0]['vcenter_ip'], $data[0]['detail'], intval($data[0]['hypervisor_type']), $data[0]['username']);
            $hypervisor = intval($data[0]['hypervisor_type']);
        } 
        $info = array(
            'vcenter_ip' => $vcenterIp,
            'hypervisor' => $hypervisor
        );
        
        return $info;
    }
    
    
    /**
     * 得到处于备份任务的虚拟机信息
     * Vmhandler需要调用,所以public
     */
    public function getTenantBackupVMInfo(){
        $sql = "select vml.vcenter_uuid, vml.vm_uuid, bt.task_name from vm_machine_list vml, bd_task bt
                where vml.task_uuid = bt.task_uuid and bt.task_type = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKTYPE']['BACKUP']));
        $info = array(
            'vmuuid' => array(),
            'vcenteruuid' => array(),
            'taskname' =>array()
        );
        foreach ($data as $d){
            $info['vmuuid'][] = $d['vm_uuid'];
            $info['vcenteruuid'][] = $d['vcenter_uuid'];
            $info['taskname'][] = $d['task_name'];
        }
        return $info;
    }
    
    /**
     * 获取分配的虚拟化类型
     * @param unknown $tenantuuid
     * @return array|fetchAll()[]
     */
    public function getTenantHypervisor($tenantuuid){
       $list = array();    
       if(empty($tenantuuid)){
           return $list;
       }
       //获取租户下所有用户
       $resourceHandler = Xphp::instance('ResourceHandler');
       $resourceList = $resourceHandler->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['VM']);
       $vcenteruuids = array();
       if(!empty($resourceList)){
           foreach ($resourceList as $resource){
               $vcenteruuids[] = $resource['vcenter_uuid'];
           }
           $vcenterDes = implode("','", $vcenteruuids);
           $sql = "select distinct hypervisor_type from vm_vcenter where vcenter_uuid in ('".$vcenterDes."')";
           $data = $this->dbSelect($sql);
           if(!empty($data)){
                foreach ($data as $d){
                    $list[] = $d['hypervisor_type'];
                }
           }
       }
       
       return $list;
       
    }
    
    /**
     * 获取用户所有虚拟化类型
     * @param unknown $useruuid
     * @return array|fetchAll()[]
     */
    public function getUserHypervisor($useruuid){
        $list = array();
        if(empty($useruuid)){
            return $list;
        }
        //获取租户下所有用户
        if(!empty($_SESSION['tenantuuid'])){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceList = $resourceHandler->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['VM']);
            $vcenteruuids = array();
            if(!empty($resourceList)){
                foreach ($resourceList as $resource){
                    $vcenteruuids[] = $resource['vcenter_uuid'];
                }
                $vcenterDes = implode("','", $vcenteruuids);
                $sql = "select distinct hypervisor_type from vm_vcenter where vcenter_uuid in ('".$vcenterDes."')";
                $data = $this->dbSelect($sql);
                if(!empty($data)){
                    foreach ($data as $d){
                        $list[] = $d['hypervisor_type'];
                    }
                }
            }
        }else{
            $sql = "select distinct hypervisor_type from vm_vcenter ";
            $where = '';
            $sqlparam = [];
            //租户外直接读取用户创建的虚拟化中心
            if(empty($_SESSION['tenantuuid']) && $useruuid == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
                // 超级管理查看所有的资源
            } elseif ($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['sysadmin']) {
                // 三权模式 并且是系统管理员才能看到所有的资源
            } else {
                // 关联管理用户判断 存储资源 - 查看   resmanagement_look
                $authUser = $_SESSION['authUser']['resmanagement_look'] ?? [];
                if (!empty($authUser)) {
                    // 表示有管理的用户
                    $useruuidArr = array_merge([$useruuid], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
                    $where = " where user_uuid in " . $useruuidArr;
                } else {
                    $where = " where user_uuid = ? ";
                    $sqlparam = [$useruuid];
                }
            }

            $data = $this->dbSelect($sql . $where, $sqlparam);
            if(!empty($data)){
                foreach ($data as $d){
                    $list[] = $d['hypervisor_type'];
                }
            }
        }
        
        return $list;
    }

    private function isNotAdmin()
    {
        $result = false;
        if ($_SESSION['isThreePowers']
            && $_SESSION['userLevel'] != Xphp::$_config['THREE_POWERS_USER']['sysadmin']
            && $_SESSION['userLevel'] != Xphp::$_config['THREE_POWERS_USER']['safeadmin']
        ) {
            $result = true;
        }
        if (!$_SESSION['isThreePowers'] && Xphp::$_user['useruuid'] != 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            $result = true;
        }
        return $result;
    }

    /**
     * 获取租户已分配的虚拟机树
     * @param int $hypervisorType
     * @param string $vcenterUuid
     * @param string $taskUuid
     * @param array $vmUuidArr 选中的虚拟机uuid，从概览添加使用
     * @return false|string
     */
    private function getTenantAllocatedVmTree(int $hypervisorType, string $vcenterUuid, string $taskUuid = '', $vmUuidArr = [])
    {
        // hypervisor层
        $hypervisorLevNode = $this->getHypervisorLevTree($hypervisorType, false);
        $hypervisorLevNode['open'] = true; // 展开
        $result[] = $hypervisorLevNode;
        // 已分配虚拟机
        $name = in_array($hypervisorType, Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])
            ? Xphp::$_lang['WEB_VM_TREE_ALLOCATED_INSTANCE'] : Xphp::$_lang['WEB_VM_TREE_ALLOCATED_VM'];
        $allocatedVmNode = [
            "checked" => false,
            "chkDisabled" => true,
            "eventtype" => '',
            "hypervisor" => $hypervisorType,
            "iconSkin" => $this->getHypervisorIcon($hypervisorType, 1),
            "id" => $hypervisorType . '_allocated_vm', // 特殊处理
            "isParent" => true,
            "name" => html_entity_decode($name),
            "nocheck" => true,
            "nosnapshot" => true,
            "open" => true,
            "pid" => $hypervisorType,
            "path" => $name,
            "sid" => 0, //vcenter_id
            "title" => $name,
            "type" => 1,
            "uuid" => '',
            "vcenterFlag" => false,
            "vcenteruuid" => $vcenterUuid,
            "vcflag" => false,
            "vmChecked" => [""]
        ];
        $result[] = $allocatedVmNode;
        // vm层
        $vmData = $this->getTreeFromVMTree(
            $hypervisorType,
            false,
            $allocatedVmNode['id'],
            1,
            true,
            true,
            true,
            true,
            $taskUuid,
            true
        );
        $data = json_decode($vmData, true)['msg'];
        // 处理选中
        foreach ($data as &$d) {
            if (in_array($d['id'], $vmUuidArr)) {
                $d['checked'] = true;
            }
        }
        $result = array_merge($result, $data);

        return json_encode($result);
    }
}
?>