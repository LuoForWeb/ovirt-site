<?php
/*******************************************
** 恢复应急演练处理类
**
** @author       xiezhuowei@vinchin.com
** @date         2016-11-23 下午15:03:11
** @version      1.0.0
** @copyright    Copyright 2015-2016 vinchin.com
********************************************/
class ManoeuvreHandler extends BLLHandler{
    private $opcodeHandler;

    function __construct(){
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('VMOpcode');
    }

    /**
     * 得到预案管理树
     * @param unknown $parmas
     */
    public function getPlanTree($parmas){
        $node = array();
        $sql = "select plan_uuid, plan_name, unix_timestamp(create_time) create_time, remark from orch_plan where user_uuid = ?";
        $plan = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        foreach ($plan as $p){
            $node[] = array(
                "id" => $p['plan_uuid'],
                "name" => $p['plan_name'],
                "title" => Xphp::$_lang['WEB_DRILLS_CREATE_TIME'] . $this->parseDate($p['create_time']),
                "type" => 1,
                "icon" => './img/platform/orch/plan.png',
                "pid" => 0,
                "remark" => $p['remark'],
                "open" => true,
            );
            $group = $this->getGroupTree($p['plan_uuid']);
            $node = array_merge($node, $group);
        }

        return json_encode($node);
    }

    /**
     * 得到某个总预案的分组
     * @param string $planuuid  总预案uuid
     */
    private function getGroupTree($planuuid){
        $node = array();
        $sql = "select group_uuid, group_name, unix_timestamp(create_time) create_time, remark from orch_plan_group where plan_uuid = ?";
        $group = $this->dbSelect($sql, array($planuuid));
        foreach ($group as $g){
            $node[] = array(
                "id" => $g['group_uuid'],
                "name" => $g['group_name'],
                "title" => Xphp::$_lang['WEB_DRILLS_CREATE_TIME'] . $this->parseDate($g['create_time']),
                "type" => 2,
                "icon" => './img/platform/orch/group.png',
                "pId" => $planuuid,
                "remark" => $g['remark'],
                "open" => true,
            );
            $child = $this->getChildTree($g['group_uuid']);
            $node = array_merge($node, $child);

        }
        return $node;
    }

    /**
     * 得到某个分组的子预案
     * @param string $groupuuid 分组预案uuid
     */
    private function getChildTree($groupuuid){
        $node = array();
        $sql = "select child_uuid, group_uuid, plan_uuid, child_name, unix_timestamp(create_time) create_time, remark from orch_plan_child where group_uuid = ?";
        $child = $this->dbSelect($sql, array($groupuuid));
        foreach ($child as $c){
            $node[] = array(
                "id" => $c['child_uuid'],
                "name" => $c['child_name'],
                "title" => Xphp::$_lang['WEB_DRILLS_CREATE_TIME'] . $this->parseDate($c['create_time']),
                "type" => 3,
                "icon" => './img/platform/orch/child.png',
                "pId" => $groupuuid,
                "groupuuid" => $c['group_uuid'],
                "planuuid" => $c['plan_uuid'],
                "remark" => $c['remark'],
                "open" => true,
            );
        }
        return $node;
    }


    /**
     * 得到所有总预案列表
     * @param unknown $params
     */
    public function getPlanList($params){
        $plan = array();
        $sql = "select plan_uuid, plan_name from orch_plan where user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        foreach ($data as $d){
            $plan[] = array(
                "uuid" => $d['plan_uuid'],
                "name" => $d['plan_name']
            );
        }
        return json_encode($plan);
    }

    /**
     * 得到某个总预案的分组列表
     * @param unknown $params
     */
    public function getGroupList($params){
        $planuuid = $params['planuuid'];
        $group = array();
        $sql = "select group_uuid, group_name from orch_plan_group where plan_uuid = ?";
        $data = $this->dbSelect($sql, array($planuuid));
        foreach ($data as $d){
            $group[] = array(
                "uuid" => $d['group_uuid'],
                "name" => $d['group_name']
            );
        }
        return json_encode($group);
    }

    /**
     * 检测预案名称是否存在
     * @param string $operate   操作名
     * @param string $name      预案名
     */
    private function checkPlansName($operate, $name){
        $sql = "select plan_uuid from orch_plan where plan_name = ?";
        $countPlan = $this->dbQuery($sql, array($name));
        $sql = "select group_uuid from orch_plan_group where group_name = ?";
        $countGroup = $this->dbQuery($sql, array($name));
        $sql = "select child_uuid from orch_plan_child where child_name = ?";
        $countChild = $this->dbQuery($sql, array($name));
        if($countPlan > 0 || $countGroup > 0 || $countChild > 0){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_DRILLS_PLAN_NAME'] . $name . Xphp::$_lang['WEB_DRILLS_PLAN_EXIST_RENAME'], "warning"));
        }
    }

    /**
     * 添加预案:总预案,分组预案,子预案
     * @param unknown $params
     * @return string
     */
    public function addPlans($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_plan_add");
        $orchtype = intval($params['orchtype']);
        $name = $params['name'];
        $remark = $params['remark'];
        $planuuid = $params['planuuid'];
        $groupuuid = $params['groupuuid'];
        $this->paramsCheck($orchtype, $name);
        $utils = Xphp::instance('Utils');
        $uuid = $utils->uuid();
        $useruuid = Xphp::$_user['useruuid'];
        $createTime = date("Y-m-d H:i:s", time());
        $planType = Xphp::$_config['PLANTYPE'];

        $operate = Xphp::$_lang['WEB_DRILLS_ADD_EMERGENCY_PLAN'];
        $this->checkPlansName($operate, $name);

        if($planType['PLAN'] == $orchtype){
            //总预案
            $sql = "insert into orch_plan (plan_uuid, plan_name, user_uuid, create_time, remark) 
                    values (?, ?, ?, ?, ?)";
            $sqlParams = array($uuid, $name, $useruuid, $createTime, $remark);
            $pId = 0;
            $icon = './img/platform/orch/plan.png';
        }elseif ($planType['GROUP'] == $orchtype){
            //分组预案
            $sql = "insert into orch_plan_group (group_uuid, group_name, plan_uuid, create_time, remark) 
                    values (?, ?, ?, ?, ?)";
            $sqlParams = array($uuid, $name, $planuuid, $createTime, $remark);
            $pId = $planuuid;
            $icon = './img/platform/orch/group.png';
        }elseif ($planType['CHILD'] == $orchtype){
            //子预案
            $sql = "insert into orch_plan_child (child_uuid, child_name, plan_uuid, group_uuid, create_time, remark) 
                    values (?, ?, ?, ?, ?, ?)";
            $sqlParams = array($uuid, $name, $planuuid, $groupuuid, $createTime, $remark);
            $pId = $groupuuid;
            $icon = './img/platform/orch/child.png';
        }
        $result = $this->dbExec($sql, $sqlParams);

        $newNode = array(
            "id" => $uuid,
            "name" => $name,
            "title" => Xphp::$_lang['WEB_DRILLS_CREATE_TIME'] . $createTime,
            "type" => $orchtype,
            "icon" => $icon,
            "pId" => $pId,
            "groupuuid" => $groupuuid,
            "planuuid" => $planuuid,
            "open" => true,
        );
        return $this->muOpResult($result, $operate, null, null, 0, $newNode);
    }

    /**
     * 修改预案
     * @param unknown $params
     * @return string
     */
    public function editPlans($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_plan_edit");
        $uuid = $params['uuid'];
        $name = $params['name'];
        $remark = $params['remark'];
        $orchtype = intval($params['orchtype']);
        $this->paramsCheck($uuid, $name, $orchtype);
        $planType = Xphp::$_config['PLANTYPE'];
        if($planType['PLAN'] == $orchtype){
            $sql = "update orch_plan set plan_name = ?, remark = ? where plan_uuid = ?";
        }elseif ($planType['GROUP'] == $orchtype){
            $sql = "update orch_plan_group set group_name = ?, remark = ? where group_uuid = ?";
        }elseif ($planType['CHILD'] == $orchtype){
            $sql = "update orch_plan_child set child_name = ?, remark = ? where child_uuid = ?";
        }
        $result = $this->dbExec($sql, array($name, $remark, $uuid));
        $operate = Xphp::$_lang['WEB_DRILLS_EDIT_EMERGENCY_PLAN'];
        return $this->muOpResult($result, $operate);
    }

    /**
     * 删除预案
     * @param unknown $params
     * @return string
     */
    public function deletePlans($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_plan_delete");
        $uuid = $params['uuid'];
        $orchtype = intval($params['orchtype']);
        $this->paramsCheck($uuid, $orchtype);
        $planType = Xphp::$_config['PLANTYPE'];
        $this->dbBeginTransaction();
        $sqlParams = array($uuid);
        $result = true;
        if($planType['PLAN'] == $orchtype){
            //删除虚拟机预案
            $sql = "delete from orch_plan_vm where plan_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);
            //删除子预案
            $sql = "delete from orch_plan_child where plan_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);
            //删除分组预案
            $sql = "delete from orch_plan_group where plan_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);
            //删除总预案
            $sql = "delete from orch_plan where plan_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);

        }elseif ($planType['GROUP'] == $orchtype){
            //删除虚拟机预案
            $sql = "delete from orch_plan_vm where group_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);
            //删除分组包含的子预案
            $sql = "delete from orch_plan_child where group_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);
            //删除分组
            $sql = "delete from orch_plan_group where group_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);
        }elseif ($planType['CHILD'] == $orchtype){
            //删除虚拟机预案
            $sql = "delete from orch_plan_vm where child_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);
            //删除子预案
            $sql = "delete from orch_plan_child where child_uuid = ?";
            $result = $result && $this->dbExec($sql, $sqlParams);
        }
        if($result){
            $this->dbCommit();
        }else{
            $this->dbRollBack();
        }

        $operate = Xphp::$_lang['WEB_DRILLS_DELETE_EMERGENCY_PLAN'];
        return $this->muOpResult($result, $operate);
    }

    /**
     * 删除预案前获取预案信息,如果没有孩子就直接删除,如果有孩子,就返回信息,提示后再删除
     * @param unknown $params
     */
    public function getDeletePlanInfo($params){
        $uuid = $params['uuid'];
        $orchtype = intval($params['orchtype']);
        $planname = $params['name'];
        $this->paramsCheck($uuid, $orchtype, $planname);
        $planType = Xphp::$_config['PLANTYPE'];
        $info = array(
            "deleteflag" => false,  //直接删除标志,如果是true,界面就直接返回,不再提示
            "groupnum" => 0,
            "childnum" => 0,
            "vmnum" => 0,

            "uuid" => $uuid,
            "name" => $planname,
            "orchtype" => $orchtype
        );
        $sqlParams = array($uuid);
        if($planType['PLAN'] == $orchtype){
            //分组预案个数
            $sql = "select count(group_id) from orch_plan_group where plan_uuid = ?";
            $data = $this->dbSelect($sql, $sqlParams);
            $info['groupnum'] = intval($data[0][0]);
            //子预案个数
            $sql = "select count(child_id) from orch_plan_child where plan_uuid = ?";
            $data = $this->dbSelect($sql, $sqlParams);
            $info['childnum'] = intval($data[0][0]);
            //虚拟机预案个数
            $sql = "select count(vm_id) from orch_plan_vm where plan_uuid = ?";
            $data = $this->dbSelect($sql, $sqlParams);
            $info['vmnum'] = intval($data[0][0]);

            if(0 == $info['groupnum'] && 0 == $info['childnum'] && 0 == $info['vmnum']){
                return $this->deletePlans($params);
            }

        }elseif ($planType['GROUP'] == $orchtype){
            //子预案个数
            $sql = "select count(child_id) from orch_plan_child where group_uuid = ?";
            $data = $this->dbSelect($sql, $sqlParams);
            $info['childnum'] = intval($data[0][0]);
            //虚拟机预案个数
            $sql = "select count(vm_id) from orch_plan_vm where group_uuid = ?";
            $data = $this->dbSelect($sql, $sqlParams);
            $info['vmnum'] = intval($data[0][0]);

            if(0 == $info['childnum'] && 0 == $info['vmnum']){
                return $this->deletePlans($params);
            }
        }elseif ($planType['CHILD'] == $orchtype){
            //虚拟机预案个数
            $sql = "select count(vm_id) from orch_plan_vm where child_uuid = ?";
            $data = $this->dbSelect($sql, $sqlParams);
            $info['vmnum'] = intval($data[0][0]);

            if(0 == $info['vmnum']){
                return $this->deletePlans($params);
            }
        }

        return json_encode($info);
    }

    /**
     * 添加虚拟机预案到子预案
     * @param unknown $params
     */
    public function addPlanVM($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_plan_addvm");
        $selectvm = $params['selectvm'];
        $vmconfig = $params['vmconfig'];
        $timepointtype = $params['timepointtype'];
        $host = $params['host'];
        $plan = $params['plan'];
        $this->paramsCheck($selectvm, $vmconfig);

        if(empty($host['proxyuuid'])){
            $hostuuid = '';
            $vcenteruuid = '';
        }else{
            $sql = "select host_uuid, vcenter_uuid from orch_proxy where proxy_uuid = ?";
            $data = $this->dbSelect($sql, array($host['proxyuuid']));
            $hostuuid = $data[0]['host_uuid'];
            $vcenteruuid = $data[0]['vcenter_uuid'];
        }


        $result = true;
        $this->dbBeginTransaction();
        foreach ($selectvm as $k => $vm){
            $sql = "insert into orch_plan_vm (vm_name, vm_uuid, vcenter_uuid, dir_path, vm_config, recovery_mode, 
                    timepoint_type, new_host_uuid, new_vcenter_uuid, plan_uuid, group_uuid, child_uuid) values 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $sqlParams = array(
                $vmconfig[$k]['vmname'], $vm['vmuuid'], $vm['vcenteruuid'], $vm['dirpath'],
                json_encode($vmconfig[$k]), Xphp::$_config['RECOVERY_MODE']['INSTANT'], $timepointtype,
                $hostuuid, $vcenteruuid,
                $plan['planuuid'], $plan['groupuuid'], $plan['childuuid']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        }
        if($result){
            $this->dbCommit();
        }else{
            $this->dbRollBack();
        }

        return $this->muOpResult($result, Xphp::$_lang['WEB_DRILLS_ADD_VM_TO_CHILD_PLAN']);
    }

    /**
     * 修改子预案的虚拟机
     * @param unknown $params
     */
    public function editPlanVM($params){
        //TODO
    }

    /**
     * 删除子预案的虚拟机
     * @param unknown $params
     */
    public function deletePlanVM($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_plan_deletevm");
        $id = $params['id'];
        $this->paramsCheck($id);
        $id = implode("','", $id);
        $id = "'" . $id . "'";
        $sql = "delete from orch_plan_vm where vm_id in (" . $id . ")";
        $result = $this->dbExec($sql, array());
        return $this->muOpResult($result, Xphp::$_lang['WEB_DRILLS_DELETE_PLAN_VM']);
    }

    /**
     * 得到预案的虚拟机列表
     * @param unknown $params
     */
    public function getPlanVMGrid($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $type = intval($params['type']);
        $planuuid = $params['planuuid'];
        $groupuuid = $params['groupuuid'];
        $childuuid = $params['childuuid'];
        //总预案
        $sql = "select vm_id, vm_name, dir_path, vm_config, recovery_mode, timepoint_type, 
                new_host_uuid, new_vcenter_uuid from orch_plan_vm where plan_uuid = ? ";
        $sqlCount = "select count(vm_id) as total from orch_plan_vm  where plan_uuid = ? ";

        $sqlParams = array($planuuid);
        $sqlCountParams = array($planuuid);
        if(Xphp::$_config['PLANTYPE']['GROUP'] == $type){
            //分组预案
            $sql .= " and group_uuid = ? ";
            $sqlCount .= " and group_uuid = ? ";
            $sqlParams[] = $groupuuid;
            $sqlCountParams[] = $groupuuid;
        }elseif(Xphp::$_config['PLANTYPE']['CHILD'] == $type){
            //子预案
            $sql .= " and group_uuid = ? and child_uuid = ?";
            $sqlCount .= " and group_uuid = ? and child_uuid = ?";
            $sqlParams[] = $groupuuid;
            $sqlCountParams[] = $groupuuid;
            $sqlParams[] = $childuuid;
            $sqlCountParams[] = $childuuid;
        }
        $sql .= " limit ? , ? ";

        $sqlParams[] = $start;
        $sqlParams[] = $length;

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $vmDescription = include APP_PATH . "vm/VmDescription.php";
        $records = array("data" => array());
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['vm_id'] . '">',
                $d['vm_name'],
                '',
                $vmDescription['OrchRecoveryMode'][intval($d['recovery_mode'])],
                $vmDescription['OrchTimepointType'][intval($d['timepoint_type'])],
                $this->getPlanVMDetail($d)
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);

    }

    /**
     * 得到预案虚拟机的详细信息,表格展示用
     * @param unknown $d
     */
    private function getPlanVMDetail($d){
        $info = array();
        $config = json_decode($d['vm_config'], true);
        $info['cpunum'] = $config['cpu']['cpunum'];
        $info['cpucore'] = $config['cpu']['cpucore'];
        $info['memory'] = $config['memory'];
        $info['power'] = $config['power'];
        $info['dirpath'] = $d['dir_path'];
        $info['hostname'] = $this->getPlanVMDetailDesHostInfo($d['new_host_uuid'], $d['new_vcenter_uuid']);

        return $info;
    }

    /**
     * 得到演练目的宿主机名称
     * @param string $hostuuid
     * @param string $vcenteruuid
     */
    private function getPlanVMDetailDesHostInfo($hostuuid, $vcenteruuid){
        $hostName = Xphp::$_lang['WEB_SYSTEM_SETTING_NO_SET'];
        if(empty($hostuuid) || empty($vcenteruuid)) return $hostName;

        $sql = "select host_ip, host_name from vm_host where host_uuid = ? and vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($hostuuid, $vcenteruuid));
        if($data[0]){
            $hostName = $data[0]['host_name'] . " (" . $data[0]['host_ip'] . ")";
        }
        return $hostName;
    }

    /**
     * 得到演练代理列表
     * @param unknown $params
     */
    public function getProxyInfo($params){
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $sql = "select op.proxy_uuid, op.network, op.vm_name, op.primary_flag, op.primary_uuid, 
                op.vm_ip, op.vm_netmask, op.vm_gateway, op.network_map, op.datastore, 
                op.server_ip, op.server_netmask, op.server_gateway, op.server_netcard_name, error_code,   
                proxy_status, vh.host_name, vh.host_ip, vc.hypervisor_type  
                from orch_proxy op, vm_host vh, vm_vcenter vc 
                where op.host_uuid = vh.host_uuid and op.vcenter_uuid = vh.vcenter_uuid 
                and vh.vcenter_uuid = vc.vcenter_uuid and op.user_uuid = ? limit ? , ?";
        $sqlCount = "select count(op.proxy_id) as total from orch_proxy op, vm_host vh , vm_vcenter vc  
                where op.host_uuid = vh.host_uuid and op.vcenter_uuid = vh.vcenter_uuid 
                and vh.vcenter_uuid = vc.vcenter_uuid and op.user_uuid = ?  ";

        $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
        $sqlCountParams = array(Xphp::$_user['useruuid']);

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $records = array("data" => array());
        foreach ($data as $d){
            $vmType = Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])];
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['proxy_uuid'] . '">',
                $d['host_name'],
                $d['host_ip'],
                $vmType,
                $d['network'],
                $d['vm_name'],
                $d['primary_flag'] == Xphp::$_config['FLAG']['SET'] ? Xphp::$_lang['WEB_DRILLS_YES'] : Xphp::$_lang['WEB_DRILLS_NO'],
                intval($d['proxy_status']),
                $this->getProxyInfoDetails($d)
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);
    }

    /**
     * 得到代理的其他信息
     * @param unknown $params
     */
    private function getProxyInfoDetails($params){
        $info = array();
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $status = intval($params['proxy_status']);
        $info['statusdes'] = $pfDes['PROXY_STATUS_DES'][$status];
        $info['popover'] = $this->getProxyStatusShowPopover($params['error_code']);
        $info['vm'] = array(
            'name' => $params['vm_name'],
            'ip' => $params['vm_ip'],
            'netmask' => $params['vm_netmask'],
            'gateway' => $params['vm_gateway'],
            'datastore' => $params['datastore'],
            'primaryhost' => "",
            'servernetcard' => $params['server_netcard_name'],
            'serverip' => $params['server_ip'],
            'servernetmask' => $params['server_netmask'],
            'servergateway' => $params['server_gateway'],
        );
        $network = json_decode($params['network_map'], true);
        $info['network'] = array();
        $info['network'] = $network['map_list'];
        //如果是从代理,获取主代理的隔离网络配置和主服务器名字
        if($params['primary_flag'] == Xphp::$_config['FLAG']['UNSET']){
            $sql = "select vh.host_name, op.network_map from orch_proxy op, vm_host vh 
                    where op.host_uuid = vh.host_uuid and op.vcenter_uuid = vh.vcenter_uuid 
                    and proxy_uuid = ?";
            $data = $this->dbSelect($sql, array($params['primary_uuid']));
            $networkMap = json_decode($data[0]['network_map'], true);
            $info['network'] = $networkMap['map_list'];
            $info['vm']['primaryhost'] = $data[0]['host_name'];
        }
        return $info;
    }


    /**
     * 根据代理状态得到显示的popover提示信息
     * @param int $errorCode
     */
    private function getProxyStatusShowPopover($errorCode){
        $error = include CONF_PATH . "error.php";
        if(0 != $errorCode){
            $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];
            $des .= "," . Xphp::$_lang['WEB_OPHANDLER_ERROR_CODE'] . ": #". $errorCode;
        }else{
            $des = "";
        }
        return $des;
    }

    /**
     * 得到所有主服务器列表
     * @param unknown $params
     */
    public function getOrchPrimaryHostSelect($params){
        $sql = "select op.proxy_uuid, op.server_ip, op.server_netmask, op.server_gateway, vh.host_name, vh.host_ip, vc.hypervisor_type  
                from orch_proxy op, vm_host vh, vm_vcenter vc 
                where op.host_uuid = vh.host_uuid and op.vcenter_uuid = vh.vcenter_uuid 
                and vh.vcenter_uuid = vc.vcenter_uuid 
                and op.primary_flag = ? and op.user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET'], Xphp::$_user['useruuid']));
        $info = array();
        foreach ($data as $d){
            $info[] = array(
                'uuid' => $d['proxy_uuid'],
                'text' => $d['host_name'] . "(" . $d['host_ip'] . ")",
                'serverip' => $d['server_ip'],
                'servernetmask' => $d['server_netmask'],
                'servergateway' => $d['server_gateway']
            );
        }

        return json_encode($info);
    }

    /**
     * 得到所有代理服务器列表
     * @param unknown $params
     */
    public function getOrchProxyHostSelect($params){
        $sql = "select op.proxy_uuid, vh.host_name, vh.host_ip, vc.hypervisor_type, op.primary_flag 
                from orch_proxy op, vm_host vh, vm_vcenter vc
                where op.host_uuid = vh.host_uuid and op.vcenter_uuid = vh.vcenter_uuid
                and vh.vcenter_uuid = vc.vcenter_uuid
                and op.user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $info = array();
        foreach ($data as $d){
            $primaryFlag = $d['primary_flag'] == Xphp::$_config['FLAG']['SET'];
            if($primaryFlag){
                $pName = Xphp::$_lang['WEB_DRILLS_HOST_SERVER'];
            }else{
                $pName = Xphp::$_lang['WEB_DRILLS_CLIENT_SERVER'];
            }

            $info[] = array(
                'uuid' => $d['proxy_uuid'],
                'text' => $pName . $d['host_name'] . "(" . $d['host_ip'] . ")"
            );
        }

        return json_encode($info);
    }

    /**
     * 得到创建代理的时候某台宿主机上面的验证虚拟机的默认信息
     * @param unknown $params
     */
    public function getOrchProxyVMConfig($params){
        $hypervisor = $params['hypervisor'];
        $vcuuid = $params['vcenteruuid'];
        $hostuuid = $params['hostuuid'];
        $this->paramsCheck($hypervisor, $vcuuid, $hostuuid);
        //得到存储和网络
        $info = Xphp::instance('Vmhandler', 'getNetworkAndStorage', $params);
        $info = json_decode($info, true);
        $sql = "select count(proxy_id) as total from orch_proxy";
        $data = $this->dbSelect($sql);

        //得到虚拟机名称
        $info['vmname'] = 'proxy gateway' . ($data[0]['total'] + 1);
        //虚拟机IP配置
        $info['vmip'] = '';
        $info['vmnetmask'] = '';
        $info['vmgateway'] = '';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $jsonparams = array(
            'nodeuuid' => $nodeuuid
        );
        //备份服务器网卡
        $networkCard = Xphp::instance('SystemHandler', 'getNetworkCardList',$jsonparams);
        $networkCard = json_decode($networkCard, true);
        $info['networkcard'] = $networkCard;

        return json_encode($info);
    }

    /**
     * 添加演练代理
     * @param unknown $params
     */
    public function addOrchProxy($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_environment_add");
        $hosttype = $params['hosttype'];
        $primaryhost = $params['primaryhost'];
        $host = $params['host'];
        $vmConfig = $params['vmConfig'];
        $segment = $params['segment'];
        $submodule_type = intval($host['hypervisor']);
        $this->paramsCheck($hosttype, $host, $vmConfig);
//         var_dump($params);
        // 发送到后台处理
        $pfMSg = array(
            "host_uuid" => $host['hostuuid'],
            "vcenter_uuid" => $host['vcenteruuid'],
            "primary_flag" => intval($hosttype),
            "primary_uuid" => $primaryhost,
            "hypervisor_type" => $submodule_type,

            "network" => $vmConfig['network'],
            "datastore" => $vmConfig['storage'],
            "vm_name" => $vmConfig['name'],
            "vm_ip" => $vmConfig['ip'],
            "vm_netmask" => $vmConfig['netmask'],
            "vm_gateway" => $vmConfig['gateway'],
            "server_netcard_name" => $vmConfig['networkcard'],
            "server_ip" => $vmConfig['serverip'],
            "server_netmask" => $vmConfig['servernetmask'],
            "server_gateway" => $vmConfig['servergateway'],

            "map_list" => $this->groupSegmentMapList($segment),
            "user_uuid" => Xphp::$_user['useruuid'],
        );

        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $opName = 'VM_VCENTER_OP_DEPLOY_PROXY_VM';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, false, true);
        //TODO暂时睡一会
        sleep(5);
        $result = $mbResult['result'];
        $vmOpcode = Xphp::instance('VMOpcode');
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除演练宿主机
     * @param unknown $params
     */
    public function deleteOrchProxy($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_environment_delete");
        $proxyuuid = $params['uuid'];
        $this->paramsCheck($proxyuuid);
        $pfMSg = array(
            "proxy_uuid_list" => array($proxyuuid)
        );
        $sql = "select hypervisor_type from orch_proxy where proxy_uuid = ?";
        $data = $this->dbSelect($sql, array($proxyuuid));
        $submodule_type = intval($data[0]['hypervisor_type']);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $opName = 'VM_VCENTER_OP_UNDEPLOY_PROXY_VM';

        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);

//         var_dump($mbResult);
//         return;

        $result = $mbResult['result'];
        $vmOpcode = Xphp::instance('VMOpcode');
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 得到部署演练环境的网段列表
     * @param unknown $segment
     */
    private function groupSegmentMapList($segment){
        $list = array();
        foreach ($segment as $s){
            $list[] = array(
                "old_segment" => $s[0],
                "verify_segment" => $s[1],
                "old_netmask" => $s[2],
                "verify_netmask" => $s[3],
                "old_gateway" => $s[4],
                "verify_gateway" => $s[5],
            );
        }

        return $list;
    }

    /**
     * 得到任务监控信息
     * @param unknown $params
     */
    public function getTaskMonitor($params){
        $taskuuid = $params['taskuuid'];
        //用户选中的虚拟机对应的虚拟机uuid和宿主机uuid
        $vmuuid = $params['vmuuid'];
        $hostuuid = $params['hostuuid'];
        $this->paramsCheck($taskuuid);
        $info = array();

        $sql = "select  bri.task_uuid, bri.total_object_size, bri.total_object_completed_size, bri.current_object_total_size, bri.current_object_completed_size,
                		ot.hypervisor_type, ot.recovery_mode, unix_timestamp(bbt.timepoint) timepoint,
                		vh.host_ip, vh.host_name, 
                		oti.global_uuid, oti.proxy_uuid, oti.new_vm_name, oti.new_dir_path, oti.new_vm_config, oti.start_vm_flag, oti.old_dir_path,
                		oti.new_host_uuid, oti.new_vcenter_uuid, oti.vm_status, oti.task_status, oti.nfs_datastore_state,
                		oti.instant_host_state, oti.error_code
                from 	bd_running_info bri, orch_task ot, orch_task_instant oti, bd_backup_timepoint bbt,
                		vm_host vh
                where 	bri.task_uuid = ot.task_uuid and
                		ot.task_uuid = oti.task_uuid and
                		oti.timepoint_uuid = bbt.timepoint_uuid and
                		oti.new_host_uuid = vh.host_uuid and
                		oti.new_vcenter_uuid = vh.vcenter_uuid and
                		oti.task_uuid = ?";


        $data = $this->dbSelect($sql, array($taskuuid));
        $vmuuids = array();
        foreach ($data as $d){
            $vmuuids[] = $d['global_uuid'];
        }

        $sql = "select 	oti.global_uuid, oti.proxy_uuid, op.network, op.vm_name, op.primary_flag, 
                        op.primary_uuid, op.network_map, op.primary_flag, op.primary_uuid, 
                		vhrs.cpu_utilization, vhrs.memory_utilization, vhrs.network_flow_in, vhrs.network_flow_out,
                        vh.host_ip, vh.host_name 
                from orch_task_instant oti, orch_proxy op, vm_host_running_state vhrs, vm_host vh 
                where 	oti.proxy_uuid = op.proxy_uuid and 
                     op.proxy_uuid =  vhrs.proxy_uuid and 
                     op.host_uuid = vh.host_uuid and op.vcenter_uuid = vh.vcenter_uuid and 
                		oti.task_uuid = ?
                group by oti.proxy_uuid";

        $hosts = $this->dbSelect($sql, array($taskuuid));
        $proxyuuids = array();
        foreach ($hosts as $host){
            $proxyuuids[] = $host['proxy_uuid'];
        }

        //演练总进度
        $info['totalprogress'] = round($data[0]['total_object_completed_size'] / $data[0]['total_object_size'] * 100, 2);
        //饼图状态统计
        $info['vmsuccess'] = $this->getTaskMonitorPie($data);
        //虚拟机信息
        $info['vms'] = $this->getTaskMonitorAllVMInfo($data, $proxyuuids);
        //宿主机信息
        $info['hosts'] = $this->getTaskMonitorHostInfo($hosts);
//         $info['hosts'] = array();
        //当前正在执行的信息
        $info['current'] = $this->getTaskMonitorCurrentInfo($data, $vmuuids, $proxyuuids, $vmuuid, $hostuuid);
        $info['timestamp'] = time() * 1000;


        return json_encode($info);
    }

    /**
     * 得到任务总统计(饼图)
     * @param unknown $vms
     */
    private function getTaskMonitorPie($vms){
        $pie = array();
        $taskConf = Xphp::$_config['VmTaskStatus'];     //虚拟机任务状态
        $success = 0;
        $failure = 0;
        $waiting = 0;
        foreach ($vms as $vm){
            $taskStatus = intval($vm['task_status']);
            switch ($taskStatus){
                case $taskConf['FINISH']:
                    $success++;
                    break;
                case $taskConf['ERROR']:
                    $failure++;
                    break;
                default:
                    $waiting++;
                    break;
            }
        }
        $pie = array(
            'success' => $success,
            'failure' => $failure,
            'waiting' => $waiting,
        );
        return $pie;
    }

    /**
     * 得到当前需要显示的虚拟机信息
     * @param array $vms
     * @param array $vmuuids
     * @param array $proxyuuids
     * @param string $vmuuid
     * @param string $hostuuid
     */
    private function getTaskMonitorCurrentInfo($vms, $vmuuids, $proxyuuids, $vmuuid, $hostuuid){
        //如果用户选择了一个,$vmuuid和hostuuid就有值,就需要返回对应用户选择的虚拟机的信息
        //如果没有选择,返回真正程序正在执行的那台虚拟机的信息
        if(!empty($vmuuid) && !empty($hostuuid)){
            //用户选择
            $info = array(
                'vmuuid' => $vmuuid,
                'vmindex' => array_search($vmuuid, $vmuuids),
                'hostuuid' => $hostuuid,
                'hostindex' => array_search($hostuuid, $proxyuuids),
            );
        }else{
            //程序当前正在执行
            $currentvmuuid = $vms[0]['current_vm_uuid'];
            $sql = "select proxy_uuid from orch_task_instant where global_uuid = ?";
            $data = $this->dbSelect($sql, array($currentvmuuid));
            $currentproxyuuid = $data[0]['proxy_uuid'];

            $info = array(
                'vmuuid' => '',
                'vmindex' => array_search($currentvmuuid, $vmuuids),
                'hostuuid' => '',
                'hostindex' => array_search($currentproxyuuid, $proxyuuids),
            );
        }
        return $info;
    }

    /**
     * 得到每个虚拟机的进度
     * @param unknown $taskStatus       瞬时恢复状态
     * @param unknown $completedSize    完成进度
     * @param unknown $totalSize        总进度
     */
    private function getEachVMProgress($taskStatus, $completedSize, $totalSize){
        $taskStatus = intval($taskStatus);
        $progress = 0;
        if($taskStatus == Xphp::$_config['VmTaskStatus']['FINISH']){
            $progress = 100;
        }elseif ($taskStatus == Xphp::$_config['VmTaskStatus']['RUNNING']){
            $progress = round($completedSize / $totalSize * 100, 2);
        }
        return $progress;
    }

    /**
     * 得到演练任务的所有虚拟机信息
     * @param unknown $data
     */
    private function getTaskMonitorAllVMInfo($data, $proxyuuids){
        $vms = array();
        foreach ($data as $k => $d){
            $vmconfig = json_decode($d['new_vm_config'], true);
            $vms[] = array(
                'name' => $d['new_vm_name'],
                'dirpath' => $d['new_dir_path'],
                'cpunum' => $vmconfig['cpu_socket'],
                'cpucore' => $vmconfig['cores_per_socket'],
                'memory' => $vmconfig['vm_memory'] >= 1024 ? $vmconfig['vm_memory']/1024 . "GB" : $vmconfig['vm_memory'] . "MB",
                'timepoint' => $this->parseDate($d['timepoint']),
                'host' => $d['host_name'] . "(" . $d['host_ip'] . ")",
                'instant' => intval($d['task_status']),    //瞬时恢复状态
                'start' => intval($d['vm_status']),      //启动状态
                'progress' => $this->getEachVMProgress($d['task_status'], $d['current_object_completed_size'], $d['current_object_total_size']), //进度
                'vmuuid' => $d['global_uuid'],
                'hostuuid' => $d['new_host_uuid'],
                'vmindex' => $k,
                'hostindex' => array_search($d['proxy_uuid'], $proxyuuids),
                'logs' => $this->getTaskMonitorEachVMLogs($d['task_uuid'], $d['global_uuid']),
            );
        }
        return $vms;
    }

    /**
     * 得到每台虚拟机的日志信息
     */
    private function getTaskMonitorEachVMLogs($taskuuid, $globaluuid){
        $sql = "select error_code, unix_timestamp(op_time) op_time, description_key, description_param, log_level 
                from vm_task_log where task_uuid = ? and vm_global_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid, $globaluuid));
        $logs = array();
        $logHandler = Xphp::instance('LogHandler');
        foreach ($data as $d){
            $logs[] = array(
                'desc' => $logHandler->getLogDesription(Xphp::$_config['LOGTYPE']['TASK'], $d['error_code'],
                                        $d['description_key'], $d['description_param']),
                'level' => intval($d['log_level'])
            );
        }
        return $logs;
    }

    /**
     * 得到任务监控宿主机信息
     * @param array $hosts
     */
    private function getTaskMonitorHostInfo($hosts){
        $hostInfo = array();
        foreach ($hosts as $host){
            $eachHost = array(
                'hostuuid' => $host['proxy_uuid'],   //这里使用代理uuid
                'name' => $host['host_name'],
                'ip' => $host['host_ip'],
                'verifyvm' => $host['vm_name'],
                'cpu' => array(),
                'memory' => array(),
                'cpuAndMemoryX' => array(),
                'network' => array(),
                'networkX' => array(),
                'networkmap' => array(),
            );
            //networkmap
            //如果是从代理,获取主代理的隔离网络配置
            if($host['primary_flag'] == Xphp::$_config['FLAG']['UNSET']){
                $sql = "select network_map from orch_proxy where proxy_uuid = ?";
                $data = $this->dbSelect($sql, array($host['primary_uuid']));
                $networkMap = json_decode($data[0]['network_map'], true);
                $eachHost['networkmap'] = $networkMap['map_list'];
            }else{
                $networkMap = json_decode($host['network_map'], true);
                $eachHost['networkmap'] = $networkMap['map_list'];
            }
            //cpu and memory
            $cpuUtilization = json_decode($host['cpu_utilization'], true);
            $cpuUtilization = $cpuUtilization['usage'];
            $memoryUtilization = json_decode($host['memory_utilization'], true);
            $memoryUtilization = $memoryUtilization['usage'];
            $cpu = array();
            $memory = array();
            $cpuAndMemoryX = array();
            foreach ($cpuUtilization as $key => $value){
                $cpu[] = $cpuUtilization[$key]['value'] / 100;
                $memory[] = $memoryUtilization[$key]['value'] / 100;
                $cpuAndMemoryX[] = date("i:s", strtotime($cpuUtilization[$key]['datetime']));
            }
            //network,这里用的数据库的network_flow_in字段
            $networkFlow = json_decode($host['network_flow_in'], true);
            $networkFlow = $networkFlow['usage'];
            $network = array();
            $networkX = array();
            foreach ($networkFlow as $key => $value){
                $network[] = $value['value'];
                $networkX[] = date("i:s", strtotime($value[$key]['datetime']));
            }
            $eachHost['cpu'] = $cpu;
            $eachHost['memory'] = $memory;
            $eachHost['cpuAndMemoryX'] = $cpuAndMemoryX;
            $eachHost['network'] = $network;
            $eachHost['networkX'] = $networkX;
            $hostInfo[] = $eachHost;
        }

        return $hostInfo;
    }

    /**
     * 得到创建演练任务的预案,虚拟机,时间点树
     * @param unknown $params
     */
    public function getPlanVMTimepointTree($params){
        $nodeuuid = $params['node'];                //节点UUID,为空的时候显示所有节点数据
        $node = array();
        $sql = "select op.plan_name, op.create_time as pt, opg.group_name, opg.create_time as pgt, 
                opc.child_name, opc.create_time as pct, 
                opv.vm_id, opv.vm_name, opv.vm_uuid, opv.vcenter_uuid, opv.dir_path, opv.timepoint_type, 
                opv.timepoint_type, opv.plan_uuid, opv.group_uuid, opv.child_uuid  
                from orch_plan op, orch_plan_group opg, orch_plan_child opc, orch_plan_vm opv 
                where op.user_uuid = ? 
                and opv.plan_uuid = op.plan_uuid 
                and opv.group_uuid = opg.group_uuid 
                and opv.child_uuid = opc.child_uuid ";

        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $plan = array();
        $group = array();
        $child = array();
        foreach ($data as $d){
            //检查并插入plan
            if(!in_array($d['plan_uuid'], $plan)){
                $plan[] = $d['plan_uuid'];
                $node[] = array(
                    "id" => $d['plan_uuid'],
                    "name" => $d['plan_name'],
                    "title" => Xphp::$_lang['WEB_DRILLS_CREATE_TIME'] . $d['pt'],
                    "type" => 1,
                    "icon" => './img/platform/orch/plan.png',
                    "pid" => 0,
                );
            }

            //检查并插入group
            if(!in_array($d['group_uuid'], $group)){
                $group[] = $d['group_uuid'];
                $node[] = array(
                    "id" => $d['group_uuid'],
                    "name" => $d['group_name'],
                    "title" => Xphp::$_lang['WEB_DRILLS_CREATE_TIME'] . $d['pgt'],
                    "type" => 2,
                    "icon" => './img/platform/orch/group.png',
                    "pId" => $d['plan_uuid'],
                );
            }

            //检查并插入child
            if(!in_array($d['child_uuid'], $child)){
                $child[] = $d['child_uuid'];
                $node[] = array(
                    "id" => $d['child_uuid'],
                    "name" => $d['child_name'],
                    "title" => Xphp::$_lang['WEB_DRILLS_CREATE_TIME'] . $d['pct'],
                    "type" => 3,
                    "icon" => './img/platform/orch/child.png',
                    "pId" => $d['group_uuid'],
                );
            }
            //插入vm
            $node[] = array(
                "id" => $d['vm_id'] . $d['child_uuid'] . $d['vm_uuid'] . $d['vcenter_uuid'],
                "name" => $d['vm_name'],
                "title" => $d['dir_path'],
                "type" => 4,
                "icon" => './img/vm/vm.png',
                "pId" => $d['child_uuid'],
                "timepoint" => 1,//timepoint_type,选择的时候是否默认选择
            );
            //插入时间点
            $node = array_merge($node, $this->getRecoveryVMTimepoint($d['vm_id'], $nodeuuid, $d['child_uuid'], $d['vcenter_uuid'], $d['vm_uuid'], $d['plan_uuid']));
        }

        return json_encode($node);
    }

    /**
     * 得到某个虚拟机的时间点节点
     * @param string $vmuuid
     */
    private function getRecoveryVMTimepoint($vmID, $nodeuuid, $childuuid, $vcenteruuid, $vmuuid, $planuuid){
        $node = array();
        $sql = "select bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid, bbt.task_create_time, bbt.task_uuid, bbt.module_type, bbt.task_type, bbt.copy_flag,
		               vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                       bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and
                       vbt.vcenter_uuid = ?  and
                       vbt.vm_uuid = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'],
            $vcenteruuid,  $vmuuid
        );

        if ($nodeuuid){
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.node_uuid = ?";
            array_push($sqlParams, $nodeuuid);
        }
        $sql .= " order by bbt.module_type, bbt.task_type, vbt.vm_uuid,  bbt.timepoint";

        $pointData = $this->dbSelect($sql, $sqlParams);
        $vmHandler = Xphp::instance('Vmhandler');
        $timepoint = array();
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        foreach ($pointData as $point){
            $taskuuid = $point['task_uuid'];
            $vmuuid = $point['vm_uuid'];
            $timepointuuid = $point['timepoint_uuid'];
            $taskCreateTimeIn = $point['task_create_time'];
            if($point['module_type'] == Xphp::$_config['MODULE_TYPE']['VM']){
                $pointType = $pfDes['BACKUP_MODE_DES'][$point['backup_mode']]. Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
            }else if($point['task_type'] == 19 || $point['task_type'] == 20){
                $pointType = $pfDes['BACKUP_MODE_DES'][$point['backup_mode']]. Xphp::$_lang['UI_ARCHIVE_TIMEPOINT'];
            }else if($point['copy_flag'] == Xphp::$_config['FLAG']['SET']){
                $pointType = $pfDes['BACKUP_MODE_DES'][$point['backup_mode']].Xphp::$_lang['UI_COPY_DATA_POINT'];
            }
            //检查并添加完备点
            if($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                if(!in_array($timepointuuid, $timepoint)){
                    $timepoint[] =  $timepointuuid;
                    $node[] = array(
                        "id" =>  $childuuid . $timepointuuid,
                        "pId" =>  $vmID . $childuuid . $vmuuid . $vcenteruuid,
                        "name" => $this->parseDate($point['timepoint']) . "(" . $pointType . ")",
                        "checked" => false,
                        "type" => 5,
                        "vmuuid" => $point['vm_uuid'],
                        "vmname" => $point['vm_name'],
                        "pointname" => $this->parseDate($point['timepoint']),
                        "vcenteruuid" => $point['vcenter_uuid'],
                        "timepointuuid" => $timepointuuid,
                        "nodeuuid" => $point['node_uuid'],
                        "path" => $point['dir_path'],
                        "version" => $point['version'],
                        "icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
                        "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $point['dir_path'],
                        "childuuid" => $childuuid,
                        "hypervisor" => intval($point['hypervisor_type']),
                        "planuuid" => $planuuid
                    );
                    //添加了完全备份时间点继续下一次
                    $pid = $childuuid . $timepointuuid;
                    continue;
                }
            }
            $node[] = array(
                "id" => $childuuid . $timepointuuid,
                "pId" => $pid,
                "name" => $this->parseDate($point['timepoint']) . "(" . $pointType . ")",
                "checked" => false,
                "type" => 6,
                "vmuuid" => $point['vm_uuid'],
                "vmname" => $point['vm_name'],
                "pointname" => $this->parseDate($point['timepoint']),
                "vcenteruuid" => $point['vcenter_uuid'],
                "nodeuuid" => $point['node_uuid'],
                "timepointuuid" => $timepointuuid,
                "path" => $point['dir_path'],
                "version" => $point['version'],
                "icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
                "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $point['dir_path'],
                "childuuid" => $childuuid,
                "hypervisor" => intval($point['hypervisor_type']),
                "planuuid" => $planuuid
            );
        }

        return $node;
    }

    /**
     * 得到演练的宿主机树
     * @param unknown $params
     */
    public function getProxyHost($params){
        //TODO,这里后面要根据虚拟化类型来查找
        $sql = "select op.host_uuid, op.vcenter_uuid, op.proxy_uuid, op.primary_flag, op.primary_uuid, 
                vh.host_ip, vh.host_name, op.hypervisor_type 
                from orch_proxy op, vm_host vh 
                where op.host_uuid = vh.host_uuid 
                and op.vcenter_uuid = vh.vcenter_uuid 
                and op.user_uuid = ? order by op.primary_flag ";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $tree = array();
        $hyperviosr = array();
        $vcenter = Xphp::instance('Vcenter');
        foreach ($data as $d){
            if(!in_array($d['hypervisor_type'], $hyperviosr)){
                //如果没有hypervisor,先插入hypervisor
                $hyperviosr[] = $d['hypervisor_type'];
                $tree[] = array(
                    "id" => $d['hypervisor_type'],
                    "pId" => 0,
                    "name" => Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor_type']],
                    "open" => true,
                    "nocheck" => true,
                    "type" => 0,
                    "iconSkin" => $vcenter->getHypervisorIcon($d['hypervisor_type'], false),
                );
            }

            $primaryFlag = $d['primary_flag'] == Xphp::$_config['FLAG']['SET'];
            if($primaryFlag){
                $pName = Xphp::$_lang['WEB_DRILLS_HOST_SERVER'];
            }else{
                $pName = Xphp::$_lang['WEB_DRILLS_CLIENT_SERVER'];
            }
            $tree[] = array(
                "id" => $d['proxy_uuid'],
                "pId" => $d['hypervisor_type'],
                "name" => $pName . $d['host_name'] . ": " . $d['host_ip'],
                "open" => false,
                "isParent" => false,
                "nocheck" => false,
//                 "checked" => true,
//TODO这里要把预案对应的演练宿主机默认选择
                "hypervisor" => $d['hypervisor_type'],
                "type" => 1,
                "iconSkin" => $vcenter->getHypervisorIcon($d['hypervisor_type'], true),
                "proxyuuid" => $d['proxy_uuid'],
                "primaryflag" => $primaryFlag,
                "primaryuuid" => $d['primary_uuid'],
                "hostuuid" => $d['host_uuid'],
                "vcenteruuid" => $d['vcenter_uuid'],
            );
        }

        return json_encode($tree);
    }

    /**
     * 创建演练任务检查
     */
    private function checkOrchTaskInstant(){
        //TODO
        //检查同一个虚拟化中心是否有相同名称的虚拟机存在.
    }

    /**
     * 创建瞬时恢复演练任务
     * @param unknown $params
     */
    public function createOrchTaskInstant($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_orch_task_add");
//         var_dump($params);
        //public params
        $task_name = $params['taskName'];
        $submodule_type = intval($params['pointInfo']['type']);
        $this->paramsCheck($task_name, $submodule_type);
        $this->checkOrchTaskInstant();

        $vmHandler = Xphp::instance('Vmhandler');
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        $recovery_position = Xphp::$_config['RECOVERY_POSITION']['OTHER'];
        $recovery_time_type = Xphp::$_config['FLAG']['UNSET'];  //补齐,无用
        $time_strategy_list = $vmHandler->groupRecoverTimeList($params['typeInfo']);
        $transport_strategy = $vmHandler->groupTransportStrategy($params['highInfo']['transfer']);
        $pfMSg = $this->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position,
            $recovery_time_type, $time_strategy_list, $transport_strategy);

        $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['ORCH_TASK'];
        $pfMSg['hypervisor_type'] = $submodule_type;
        $pfMSg['auto_report_flag'] = Xphp::$_config['FLAG']['SET'];             //自动生成报告
        $pfMSg['plan_uuid'] = $params['pointInfo']['planuuid'];                 //总预案uuid
        $pfMSg['recovery_mode'] = Xphp::$_config['RECOVERY_MODE']['INSTANT'];   //恢复模式
        $pfMSg['verify_script'] = '';   //验证脚本
        $pfMSg['vm_list'] = $this->groupOrchVMList($params['pointInfo']['points'], $params['recoverInfo']);  //虚拟机列表

//         var_dump($pfMSg);
//         return;

        $nodeuuid = $params['pointInfo']['nodeuuid'];
        $opName = 'VM_PRIVATE_TASK_OP_CREATE_ORCH_TASK';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);


//         var_dump($nodeuuid, $submodule_type, $opName, $msg);
//         return;
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);

//         var_dump($mbResult);
//         return;

        $result = $mbResult['result'];
        $vmOpcode = Xphp::instance('VMOpcode');
        $operate = $vmOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 组合虚拟机配置列表
     * @param array $points     时间点列表
     * @param array $recoverInfo      恢复目标
     */
    private function groupOrchVMList($points, $recoverInfo){
        $vmList = array();
        $hosts = $recoverInfo['hosts'];
        $nfsAddr = $recoverInfo['addr'];
        $vmconfigs = $recoverInfo['vmconfigs'];

        $utils = Xphp::instance('Utils');
        foreach ($vmconfigs as $vm){
            //虚拟机配置
            $vmInfo = array(
                'old_vm_uuid' => $vm['vmuuid'],
                'old_vcenter_uuid' => $vm['vcenteruuid'],
                'new_vm_name' => $vm['vmname'],
                'new_vm_config' => $this->groupVMConfig($vm),
                'start_vm_flag' => $utils->parseBoolToFlag($vm['power']),
                'child_uuid' => $vm['childuuid'],
                'nfs_server_ip' => $nfsAddr,
            );
            //时间点信息
            $pointInfo = $this->getVMPointInfo($points, $vm);

            //代理信息
            $proxyInfo = $this->getVMProxyInfo($vm);

            //合并所有信息
            $vmList[] = array_merge($vmInfo, $pointInfo, $proxyInfo);
        }

        return $vmList;
    }

    /**
     * 得到虚拟机的时间点信息
     * @param array $points
     * @param array $vm
     */
    private function getVMPointInfo($points, $vm){
        //因为时间点的顺序和虚拟机的顺序不一样(界面上虚拟机可以调整顺序)
        //所以这里需要通过虚拟机的vmuuid, vcenteruuid, childuuid来查找到对应的虚拟机
        //每个子预案能有相同的虚拟机,包括名称和uuid
        $pointInfo = array();
        foreach ($points as $p){
            if($p['vmuuid'] == $vm['vmuuid'] && $p['vcenteruuid'] == $vm['vcenteruuid'] && $p['childuuid'] == $vm['childuuid']){
                $pointInfo = array(
                    'timepoint_uuid' => $p['timepointuuid'],
                    'old_dir_path' => $p['dirpath'],
                );
                return $pointInfo;
            }
        }
        $this->paramsCheck($pointInfo); //检测下,如果没有获取到,直接退出报错
    }

    /**
     * 得到虚拟机代理信息
     * @param array $vm
     */
    private function getVMProxyInfo($vm){
        //注意这里的hostuuid和vcenteruuid是通过代理的uuid查找的,不是$vm之前的
        $proxyInfo = array();
        //如果这个虚拟机选择了目标代理,直接使用目标代理
        $proxyuuid = $vm['host']['value'];
        //如果没有选择,随机从目标代理选择一个
        $sql = "select host_uuid, vcenter_uuid from orch_proxy where proxy_uuid = ?";
        $data = $this->dbSelect($sql, array($proxyuuid));

        $this->paramsCheck($data);
        $proxyInfo = array(
            'new_host_uuid' => $data[0]['host_uuid'],
            'new_vcenter_uuid' => $data[0]['vcenter_uuid'],
            'group_uuid' => '',
            'proxy_uuid' => $proxyuuid,
        );
        $sql = "select group_uuid from orch_plan_child where child_uuid = ?";
        $data = $this->dbSelect($sql, array($vm['childuuid']));
        $this->paramsCheck($data);
        $proxyInfo['group_uuid'] = $data[0]['group_uuid'];

        return $proxyInfo;
    }

    /**
     * 组合虚拟机配置
     * @param unknown $vmConfig
     */
    private function groupVMConfig($vmConfig){
        $info = array(
            'cpu_socket' => intval($vmConfig['cpu']['cpunum']),
            'cores_per_socket' => intval($vmConfig['cpu']['cpucore']),
            'vm_memory' => intval($vmConfig['memory']),
            'auto_conf_flag' => Xphp::$_config['FLAG']['SET'],
            'vm_network_list' => $vmConfig['network'],
            'vm_disk_list' => $vmConfig['storage'],
        );

        return json_encode($info, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 得到瞬时恢复演练任务的虚拟机列表
     * @param unknown $params
     */
    public function getOrchInstanJobVMList($params){
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('oti.vm_id', 'oti.new_vm_name', 'vh.host_name', 'opc.child_name',
            'vm_backup_timepoint', 'vm_backup_timepoint', 'oti.vm_status');
        $sql = "select oti.new_vm_name, oti.vm_status, oti.task_status, oti.nfs_datastore_state, oti.instant_host_state, oti.error_code, 
                	   vh.host_ip, vh.host_name, op.plan_name, opg.group_name, opc.child_name, vbt.vm_config, bbt.timepoint 
                from orch_task_instant oti, vm_host vh, vm_backup_timepoint vbt, bd_backup_timepoint bbt, 
                	 orch_plan op, orch_plan_group opg, orch_plan_child opc 
                where oti.new_host_uuid = vh.host_uuid and 
                	  oti.timepoint_uuid = vbt.timepoint_uuid and 
                	  oti.timepoint_uuid = bbt.timepoint_uuid and 
                	  oti.group_uuid = opg.group_uuid and 
                	  oti.child_uuid = opc.child_uuid and 
                	  opg.plan_uuid = op.plan_uuid and 
                	  oti.task_uuid = ? 
                group by oti.global_uuid 
                order by  $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $sqlCount = "select count(vm_id) as total from orch_task_instant where task_uuid = ?";

        $data = $this->dbSelect($sql, array($taskuuid, $start, $length));
        $count = $this->dbSelect($sqlCount, array($taskuuid));

        $records = array();
        $records["data"] = array();

        foreach ($data as $d){
            $details = json_decode($d['details'], true);
            $records["data"][] = array(
                ++$start,
                $d['new_vm_name'],
                $d['host_name'] . "(" . $d['host_ip'] . ")",
                $d['plan_name'] . ">" . $d['group_name'] . ">" . $d['child_name'],
                $d['timepoint'],
                $this->getVMDiskTotalSize($d['vm_config']),
                $d['task_status'],
            );
        }
        $records["draw"] = $draw;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return  json_encode($records);

    }

    /**
     * 得到虚拟机所有磁盘大小
     * @param unknown $vmconfig
     */
    private function getVMDiskTotalSize($vmconfig){
        $vmconfig = json_decode($vmconfig, true);
        $diskList = $vmconfig['disk_list'];
        $size = 0;
        $utils = Xphp::instance('Utils');
        foreach ($diskList as $disk){
            $size += $disk['totalSize'];
        }
        return $utils->calSize($size, true);
    }

    /**
     * 得到某个演练任务的报告
     * @param unknown $params
     */
    public function getOrchReportDetails($params){
        $reportuuid = $params['uuid'];
        $this->paramsCheck($reportuuid);

        $sql = "select task_name, unix_timestamp(start_time) start_time, unix_timestamp(finish_time) finish_time, network_map, error_code from orch_report where report_uuid = ?";
        $orchr = $this->dbSelect($sql, array($reportuuid));

        $sql = "select vm_name, hypervisor_type, dir_path, vm_config, recovery_mode, unix_timestamp(timepoint) timepoint, backup_task_name, 
                        backup_mode, proxy_uuid, new_host_ip, new_host_name, new_vcenter_name, total_size, 
                        vm_status, error_code, plan_name, plan_uuid,  
                        group_name, group_uuid, child_name, child_uuid from orch_report_vm where report_uuid = ? 
                        order by group_uuid, child_uuid";
        $orchrv = $this->dbSelect($sql, array($reportuuid));

        $jobHandler = Xphp::instance('JobHandler');
        //统计信息
        $statistics = array(
            'result' => $jobHandler->getHistoryJobResultDes($orchr[0]['error_code']),
            'hosts' => '',
            'vms' => count($orchrv),
            'times' => $orchr[0]['finish_time'] - $orchr[0]['start_time'],
        );


        $networkMap = json_decode($orchr[0]['network_map'], true);

        $utils = Xphp::instance('Utils');

        $hostName = array();    //宿主机名称,用于计算宿主机个数
        $hostList = array();    //宿主机列表
        $proxyList = array();   //代理列表
        $charts = array();      //图标信息
        $pieSeriesdataSuccess = 0;      //成功个数
        $pieSeriesdataFailure = 0;      //失败个数
        $groupUUIDList = array();       //分组预案uuid列表
        $groupList = array();           //分组预案
        $childUUIDList = array();       //子预案uuid列表
        $childList = array();           //子预案
        $planAndVM = array();           //预案和虚拟机
        $i = 1;
        //雷达图信息
        $legenddata = array();          //虚拟机的名称列表
        $radarindicatormax = array(0, 0, 0, 0, 0);   //每个项目的最大刻度//耗时(秒), 内存(MB), 网络(网卡个数), 存储(GB), CPU(个数)
        $seriesdata = array();          //每个虚拟机的信息
        foreach ($orchrv as $vm){
            $hostName[] = $vm['proxy_uuid'];
            //成功失败统计
            0 == $vm['error_code'] ? $pieSeriesdataSuccess++ : $pieSeriesdataFailure++;

            //宿主机信息
            if(!in_array($vm['proxy_uuid'], $proxyList)){
                $hostList[] = array(
                    "id" => $i++,
                    "name" => $vm['new_host_name'],
                    "ip" => $vm['new_host_ip'],
                    "segment" => $networkMap['map_list']
                );
                $proxyList[] = $vm['proxy_uuid'];
            }


            $vmConfig = json_decode($vm['vm_config'], true);

            $vmValue = array(
                $vmConfig['total_time'],
                $vmConfig['vm_memory'],
                $vmConfig['network_num'],
                $vm['total_size'] / 1024 / 1024,
                $vmConfig['cpu_socket'],
            );

            //更新最大值
            foreach ($vmValue as $key => $value){
                if($value > $radarindicatormax[$key]){
                    $radarindicatormax[$key] = $value;
                }
            }

            //雷达图信息
            $legenddata[] = $vm['vm_name'];
            $seriesdata[] = array(
                "name" => $vm['vm_name'],
                "value" => $vmValue,
            );

            //总预案名称
            $planName = $vm['plan_name'];

            //添加分组预案
            if(!in_array($vm['group_uuid'], $groupUUIDList)){

                //添加某个分组预案的预案
                $groupChilds = array();
                foreach ($orchrv as $c){
                    if(!in_array($vm['child_uuid'], $childUUIDList)){
                        //添加某个子预案的虚拟机
                        $childVms = array();
                        foreach ($orchrv as $v){
                            $vConfig = json_decode($v['vm_config'], true);
                            //添加虚拟机
                            $childVms[] = array(
                                "name" => $v['vm_name'],
                                "cpu" => $vConfig['cpu_socket'] . "/" . $vConfig['cores_per_socket'],
                                "memory" => $vConfig['vm_memory'] . "MB",
                                "size" => $utils->calSize($v['total_size']),
                                "timepoint" => $this->parseDate($v['timepoint']),
                                "host" => $v['new_host_ip'] == $v['new_host_name'] ? $v['new_host_ip'] :
                                    $v['new_host_name'] . "[" . $v['new_host_ip'] . "]",
                                "result" => $v['error_code'] == 0,
                                'des' => $jobHandler->getVMErrorCodeDes($v['vm_status'], $v['error_code']),
                            );
                        }
                        $groupChilds[] = array(
                            "name" => $c['child_name'],
                            "vmnum" => count($childVms),
                            "vm" => $childVms
                        );


                        $childUUIDList[] = $vm['child_uuid'];
                    }
                }
                $groupInfo = array(
                    "name" => $vm['group_name'],
                    "child" => $groupChilds,
                );

                $groupList[] = $groupInfo;
                $groupUUIDList[] = $vm['group_uuid'];


            }

        }

        $hostName = array_unique($hostName);
        $statistics['hosts'] = count($hostName);

        $charts = array(
            'radar' => array(
                'legenddata' => $legenddata,
                'radarindicatormax' => $radarindicatormax,
                'seriesdata' => $seriesdata
            ),
            'pie' => array(
                'seriesdata' => array(
                    "success" => $pieSeriesdataSuccess,
                    "failure" => $pieSeriesdataFailure
                )
            )
        );

        $planAndVM = array(
            "name" => $planName,
            "groupnum" => count($groupUUIDList),
            "childnum" => count($childUUIDList),
            "vmnum" => count($orchrv),
            "group" => $groupList
        );

        $report = array(
            'statistics' => $statistics,    //统计信息
            'charts' => $charts,            //图表信息
            'hostList' => $hostList,        //宿主机列表
            'planAndVM' => $planAndVM,      //预案和虚拟机
        );

        return json_encode($report);

    }

    /**
     * 得到用户验证结果
     * @param unknown $params
     */
    public function getVerifydiyResult($params){
        $type = intval($params['type']);
        $value = $params['value'];
        $this->paramsCheck($type, $value);

        $verifyResult = false;
        $verifyType = Xphp::$_config['VERIFY_TYPE'];
        switch ($type){
            case $verifyType['IP']:
                if (!filter_var($value, FILTER_VALIDATE_IP)) {
                   // "The IP address is not valid.";
                    return $this->muOpResult(false, Xphp::$_lang['WEB_PUBLIC_FAILURE'], Xphp::$_lang['UI_PUBLIC_IP_ADDRESS'] . Xphp::$_lang['WEB_SETTINGS_UPDATE_PARAMS_ERROR'], "warning");
                }
                $verifyResult = $this->verifyPing($value);
                break;
            case $verifyType['WEB']:
                $verifyResult = $this->verifyWeb($value);
                break;
        }
        $result = array(
            "result" => $verifyResult
        );
        return json_encode($result);
    }

    /**
     * ping验证
     * @param string $ip
     * @return boolean
     */
    private function verifyPing($ip){
        exec("ping -c 1 $ip", $outcome, $status);
        $result = 0 == $status ? true : false;
        return $result;
    }

    /**
     * web验证
     * @param string $ip
     * @return boolean
     */
    private function verifyWeb($ip){
        $httpUrl = "http://" . $ip;
        $httpsUrl = "https://" . $ip;
        $httpUrlCode = $this->getHttpCode($httpUrl);
        //这里只验证web服务是否存在,只要是返回的是web服务器的任意转态码就表示web服务存在
        $httpCode = array(
            100, 101,
            200, 201, 202, 203, 204, 205, 206,
            300, 301, 302, 303, 304, 305, 306, 307,
            400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417,
            500, 501, 502, 503, 504, 505
        );
        if(0 == $httpUrlCode){
            $httpsUrlCode = $this->getHttpCode($httpsUrl);
            if(0 == $httpsUrlCode){
                return false;
            }
        }
        if(in_array($httpUrlCode, $httpCode) || in_array($httpsUrlCode, $httpCode)){
            return true;
        }

        return false;
    }

    /**
     * 得到url http code
     * @param unknown $url
     * @return mixed
     */
    private function getHttpCode($url){
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_NOBODY,1);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl,CURLOPT_TIMEOUT,5);
        curl_exec($curl);
        $re = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        curl_close($curl);
        return  $re;
    }

    /**
     * 得到当前演练任务的预案统计信息
     * @param unknown $params
     */
    public function getVMRunningJobPlanInfo($params){
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);

        $sql = "select task_name from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $taskName = $data[0]['task_name'];

        $sql = "select group_uuid from orch_task_instant where task_uuid = ? group by group_uuid";
        $data = $this->dbSelect($sql, array($taskuuid));
        $groupCount = count($data);

        $sql = "select child_uuid from orch_task_instant where task_uuid = ? group by child_uuid";
        $data = $this->dbSelect($sql, array($taskuuid));
        $childCount = count($data);

        $info = array(
            'taskname' => $taskName,
            'group' => $groupCount,
            'child' => $childCount
        );
        return json_encode($info);
    }


   /*数据自动验证代码开始*/

    /**
     * 获取虚拟实验室列表
     * @param unknown $params
     * @return string
     */
    public function getVirtualLabInfo($params){
        $start = $params['start'];
        $length = $params['length'];
        $search = $params['search'];
        $draw = $params['draw'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'svl.virtual_lab_name', 'vv.vcenter_uuid', 'vh.host_uuid',
            'svl.proxy_name', 'svl.create_time', 'svl.proxy_status'
        );

        $sql = "select distinct svl.virtual_lab_uuid, svl.virtual_lab_name, svl.proxy_name, svl.proxy_ip, svl.proxy_netmask, svl.proxy_gateway, svl.storage_uuid,
                svl.proxy_network_name, svl.proxy_status, svl.task_progress, unix_timestamp(svl.create_time) create_time, svl.hypervisor_type, svl.network_map,
                svl.error_code, vh.host_ip, vh.host_name,vv.vcenter_uuid, vv.vcenter_flag, vv.detail, vv.vcenter_ip, vv.nickname, vv.vcenter_name, vv.username,
                bn.host_name,bn.node_type,bsr.storage_nickname
                from sr_virtual_lab svl
                    left join vm_host vh on svl.host_uuid = vh.host_uuid
                    left join vm_vcenter vv on svl.vcenter_uuid = vv.vcenter_uuid
                    left join bd_node bn on svl.node_uuid = bn.node_uuid
                    left join bd_storage_resource bsr on svl.storage_uuid = bsr.storage_uuid
                where virtual_lab_uuid is not null ";
        $sqlCount = "select count(svl.virtual_lab_uuid) as total from sr_virtual_lab svl left join vm_host vh on svl.host_uuid = vh.host_uuid left join vm_vcenter vv on svl.vcenter_uuid = vv.vcenter_uuid where virtual_lab_uuid is not null ";
        $sqlParams = array();
        $sqlCountParams = array();

        // 关联管理用户判断 存储资源 - 查看   resmanagement_look
        $authUser = $_SESSION['authUser']['data_manager_look'] ?? [];
        if (!empty($authUser)) {
            // 表示有管理的用户
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
            $sql .= "and svl.user_uuid in " . $useruuidArr;
        } else {
            // 这个是之前的业务逻辑
        }


        $name = $search['name'];
        //根据名称搜索
        if($this->checkEmpty($name)){
            $sql .= "and svl.virtual_lab_name like ? ";
            $sqlCount .= "and svl.virtual_lab_name like ? ";
            $sqlParams = array_merge($sqlParams,array('%'.$name.'%'));
            $sqlCountParams = array_merge($sqlCountParams,array('%'.$name.'%'));
        }

        $sql .= " order by  $sortArr[$sortColumn]  $sortType limit ? , ? ";

        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records['data'] = array();

        $vcenter = Xphp::instance('Vcenter');
        $jobHandler = Xphp::instance('JobHandler');
        $vmDes = include APP_PATH . 'vm/VmDescription.php';
        $labList= array();
        foreach ($data as $d){
            //防止虚拟实验室重复显示
            if(in_array($d['virtual_lab_uuid'], $labList)) continue;
            $labList[] = $d['virtual_lab_uuid'];
            //主机名暂时显示主机IP
            $hostname = $d['host_ip'];
            $proxyInfo = array(
                'name' => $d['proxy_name'],
                'ip' => $d['proxy_ip'],
                'netmask' => $d['proxy_netmask'],
                'gateway' => $d['proxy_gateway'],
                'network' => $d['proxy_network_name'],
                'datastore' => $d['storage_nickname'] ?? $d['storage_uuid']
            );
            $statusDes = $vmDes['VIRTUAL_LAB_STATUS'][intval($d['proxy_status'])];
            if(intval($d['proxy_status']) == Xphp::$_config['VIRTUAL_LAB_STATUS']['DEPLOYMENT'] ||
                intval($d['proxy_status']) == Xphp::$_config['VIRTUAL_LAB_STATUS']['MODIFY']){
                $statusDes .= "(".$d['task_progress']."%".")";
            }
            if($d['error_code'] != 0){
                $popover = $jobHandler->getJobStatusShowPopover($d['error_code']);
            }else{
                $popover = $statusDes;
            }
            // 获取下容灾演练室的虚拟化中心为节点信息
            if ($d['hypervisor_type'] == 108) {
                // 容灾
                $nodetype = $d['node_type'] == 1 ? Xphp::$_lang['UI_STORAGE_REMOTE_NODE_MASTER'] : Xphp::$_lang['UI_STORAGE_REMOTE_NODE_SUB'];
                $hostname =$d['host_name'] . '(' . $nodetype . ')';
                $center = Xphp::$_lang['UI_VM_MACHINE_MANAGER'];
            } else {
                $center = $vcenter->getVcenterNameInTree($d['vcenter_uuid'], $d['hypervisor_type'], $d['vcenter_flag'], $d['vcenter_ip'], $d['nickname'], $d['vcenter_name'], $d['detail'], $d['username']);
            }
            $records['data'][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['virtual_lab_uuid'] . '">',
                $d['virtual_lab_name'],
                $center,
                $hostname,
                $d['proxy_name']."(".$d['proxy_ip'].")",
                $this->parseDate($d['create_time']),
                $statusDes,
                $d['proxy_status'],
                $d['virtual_lab_uuid'],
                array(
                    'proxyInfo' => $proxyInfo,
                    'networkInfo' => json_decode($d['network_map'], true),
                    'level'=>$jobHandler->getJobStatusShowLevel($d['error_code']),
                    'popover' => $popover
                )

            );
        }
        $records["draw"] = $draw;
        $records["recordsTotal"] = intval($count[0]['total']);
        $records["recordsFiltered"] = intval($count[0]['total']);

        return  json_encode($records);
    }

    /**
     * 获取创建虚拟实验室初始化名称
     * @return string|number|string
     */
    public function getCreateLabName(){
        $labName = "Verification_Lab";

        // 这里判断下存在就在编号前加上1
        $sqlParams = [$labName . '%'];
        $sql = "select virtual_lab_name from sr_virtual_lab where virtual_lab_name like ?";
        $data = $this->dbSelect($sql, $sqlParams);

        if (empty($data)) {
            return $labName . '1';
        }

        // 取出任务名后面的编号并降序
        $array = str_replace($labName, '', array_column($data, 'virtual_lab_name'));

        $key = array_map('intval', $array);

        $key = !empty($key) ? max($key) + 1 : 1;

        return $labName . $key;
    }

    /**
     * 创建虚拟实验室
     * @param unknown $params
     */
    public function createVirtualLab($params){
        //public params

        $opName = 'VM_VCENTER_OP_DEPLOY_VIRTUAL_LAB_PROXY_VM';

        $submodule_type = $params['hostInfo']['hypervisor'];
        $vcenter_uuid = $params['hostInfo']['vcenter_uuid'];
        $host_uuid = $params['hostInfo']['host_uuid'];
        $nodeuuid = $params['hostInfo']['nodeuuid'] ?? Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        if ($params['hostInfo']['source_type'] == 2) {
            // 内嵌
            $submodule_type = 108;
            // 从 bd_emd 表 根据 node_uuid 获取 emd_uuid 替换
            $emdUuid = $this->dbSelect("select emd_uuid from bd_emd where node_uuid = ? limit 1", [$nodeuuid]);
            if (!empty($emdUuid)) {
                $vcenter_uuid = $emdUuid[0]['emd_uuid'];
                $host_uuid = $emdUuid[0]['emd_uuid'];
            }
        }

        //实验室
        $pfMsg['virtual_lab_name'] = $params['labInfo']['virtual_lab_name'];
        //代理网关
        $pfMsg['proxy_name'] = $params['labInfo']['proxy_name'];
        //文件夹
        $pfMsg['folder_name'] = $params['labInfo']['folder_name'];
        //资源池
        $pfMsg['resource_pool_name'] = $params['labInfo']['resource_pool_name'];
        //虚拟交换机
        $pfMsg['isolated_vswitch_name'] = $params['labInfo']['isolated_vswitch_name'];
        $pfMsg['vcenter_uuid'] = $vcenter_uuid;
        $pfMsg['host_uuid'] = $host_uuid;
        $pfMsg['hypervisor_type'] = $submodule_type;
        $pfMsg['storage_uuid'] = $params['hostInfo']['storage_uuid'];
        $pfMsg['proxy_product_network_Info'] = $this->groupProxyNetworkInfo($params['hostInfo']['proxy_info']);
        $pfMsg['isolated_network_info'] = $this->groupIsolatedNetworkInfo($params['networkInfo']);

        $msg = json_encode($pfMsg);

        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);

        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改虚拟实验室
     * @param unknown $params
     */
    public function editVirtualLab($params){
        //public params

        $opName = 'VM_VCENTER_OP_MODIFY_VIRTUAL_LAB_PROXY_VM';
        $operate = $this->opcodeHandler->getOpcodeDes($opName);

        //比较检测是否信息进行了修改
        $oldSettings = json_encode($params['oldSettings']);
        $newSettings = json_encode(array(
            'virtual_lab_uuid' => $params['virtual_lab_uuid'],
            'labInfo' => $params['labInfo'],
            'hostInfo' => $params['hostInfo'],
            'networkInfo' => $params['networkInfo'],
        ));
        if($oldSettings == $newSettings){
            //未做修改直接返回成功
            return $this->muOpResult(true, $operate);
        }

        $submodule_type = $params['hostInfo']['hypervisor'];
        $vcenter_uuid = $params['hostInfo']['vcenter_uuid'];
        $host_uuid = $params['hostInfo']['host_uuid'];
        $nodeuuid = $params['hostInfo']['nodeuuid'] ?? Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        if ($params['hostInfo']['source_type'] == 2) {
            // 内嵌
            $submodule_type = 108;
            // 从 bd_emd 表 根据 node_uuid 获取 emd_uuid 替换
            $emdUuid = $this->dbSelect("select emd_uuid from bd_emd where node_uuid = ? limit 1", [$nodeuuid]);
            if (!empty($emdUuid)) {
                $vcenter_uuid = $emdUuid[0]['emd_uuid'];
                $host_uuid = $emdUuid[0]['emd_uuid'];
            }
        }

        //虚拟实验室uuid
        $pfMsg['virtual_lab_uuid'] = $params['virtual_lab_uuid'];
        //实验室
        $pfMsg['virtual_lab_name'] = $params['labInfo']['virtual_lab_name'];
        //代理网关
        $pfMsg['proxy_name'] = $params['labInfo']['proxy_name'];
        //文件夹
        $pfMsg['folder_name'] = $params['labInfo']['folder_name'];
        //资源池
        $pfMsg['resource_pool_name'] = $params['labInfo']['resource_pool_name'];
        //虚拟交换机
        $pfMsg['isolated_vswitch_name'] = $params['labInfo']['isolated_vswitch_name'];
        $pfMsg['vcenter_uuid'] = $vcenter_uuid;
        $pfMsg['host_uuid'] = $host_uuid;
        $pfMsg['hypervisor_type'] = $submodule_type;
        $pfMsg['storage_uuid'] = $params['hostInfo']['storage_uuid'];
        $pfMsg['proxy_product_network_Info'] = $this->groupProxyNetworkInfo($params['hostInfo']['proxy_info']);
        $pfMsg['isolated_network_info'] = $this->groupIsolatedNetworkInfo($params['networkInfo']);

        $msg = json_encode($pfMsg);
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除虚拟实验室
     * @param unknown $params
     * @return string
     */
    public function deleteVirtualLab($params){
        $uuid = $params['uuid'];
        //删除虚拟实验室操作码
        $opName = 'VM_VCENTER_OP_UNDEPLOY_VIRTUAL_LAB_PROXY_VM';
        //获取操作码对应的描述
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        //检查虚拟实验室是否在被任务使用，部署中/修改中
        $this->checkLabTaskExist($uuid, $operate);

        //获取到传消息需要用的子模块
        $sql = "select hypervisor_type from sr_virtual_lab where virtual_lab_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        //获取备份服务器的节点uuid
        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $submodule_type = intval($data[0]['hypervisor_type']);
        $msg = json_encode(array("virtual_lab_uuid_list" => array($uuid)));
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];

        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 刷新虚拟实验室
     * @param unknown $params
     * @return string
     */
    public function refreshVirtualLab($params){
        $uuid = $params['list'];
        //刷新虚拟实验室操作码
        $opName = 'VM_VCENTER_OP_REFRESH_VIRTUAL_LAB';
        //获取操作码对应的描述
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        //获取到传消息需要用的子模块
        $sql = "select hypervisor_type from sr_virtual_lab where virtual_lab_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid[0]));
        //获取备份服务器的节点uuid
        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $submodule_type = intval($data[0]['hypervisor_type']);
        $msg = json_encode(array("virtual_lab_uuid_list" => $uuid));
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];

        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 获取代理网关生产网络信息
     * @param unknown $proxyInfo
     * @return array
     */
    private function groupProxyNetworkInfo($proxyInfo){
        if(empty($proxyInfo)) return array();
        //代理网关网络信息
        $info = array(
            "network_name" => $proxyInfo['network_name'],
            "network_uuid" => $proxyInfo['network_uuid'],
            "ip_address" => $proxyInfo['ip_address'],
            "ip_netmask" => $proxyInfo['netmask'],
            "ip_gateway" => $proxyInfo['gateway'],
            "ip_segment" => "",
            "dns_server" => "",
            "mac_address" => ""
        );

        return $info;

    }

    /**
     * 获取创建虚拟实验室隔离网络信息
     * @param unknown $networkInfo
     * @return array[]
     */
    private function groupIsolatedNetworkInfo($networkInfo){
        if(empty($networkInfo)) return array();
        $list = array();
        foreach ($networkInfo as $network){
            //生产网络
            $product = array(
                "network_name" => $network['productInfo']['name'],
                "network_uuid" => $network['productInfo']['uuid'],
                "ip_netmask" => $network['productInfo']['netmask'],
                "ip_gateway" => $network['productInfo']['gateway'],
                "ip_segment" => "",
                "dns_server" => "",
                "mac_address" => "",
                "ip_address" => ""
            );
            //隔离网络
            $isolated = array(
                "network_name" => $network['isolatedInfo']['name'],
                "network_uuid" => $network['isolatedInfo']['uuid'],
                "ip_netmask" => $network['isolatedInfo']['netmask'],
                "ip_gateway" => $network['isolatedInfo']['gateway'],
                "ip_segment" => "",
                "dns_server" => "",
                "mac_address" => "",
                "ip_address" => ""
            );
            $list[] = array(
                "productInfo" => $product,
                "isolatedInfo" => $isolated
            );
        }

        return $list;
    }

    /**
     * 获取验证虚拟机任务源
     * @param unknown $params
     * @return string
     */
    public function getVerifyTaskTree($params){
        $nodeuuid = $params['nodeuuid'];
        $vmHandler = Xphp::instance('Vmhandler');
        $vcenter = Xphp::instance('Vcenter');
        $sql = "select bt.task_uuid, bt.task_name, unix_timestamp(bt.create_time) create_time, vml.vm_uuid, vml.vm_name, vml.vcenter_uuid, vt.hypervisor_type from bd_task bt, vm_task vt, vm_machine_list vml where bt.task_uuid = vt.task_uuid and bt.task_uuid = vml.task_uuid and vt.hypervisor_type = ? and bt.node_uuid = ? and bt.delete_flag = ? and bt.task_type = ? ";
        $sqlParams = array(Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'],$nodeuuid, Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['TASKTYPE']['BACKUP']);

        $sql .= " and bt.user_uuid = ? ";
        $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));

        $data = $this->dbSelect($sql, $sqlParams);

        $node = array();
        $hypervisor = array();
        $task = array();
        $vm = array();
        foreach ($data as $d){
            $taskuuid = $d['task_uuid'];
            $vmuuid = $d['vm_uuid'];
            $vcenteruuid = $d['vcenter_uuid'];
            $hypervisorType = intval($d['hypervisor_type']);
            //先添加虚拟化类型
            if(!in_array($d['hypervisor_type'], $hypervisor)){
                $name = Xphp::$_config['VMHYPERVISORDES'][$hypervisorType];
                $node[] = array(
                    "id" => $hypervisorType,
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => true,
                    "type" => -1,
                    "iconSkin" => $vcenter->getHypervisorIcon($hypervisorType, false)
                );
                $hypervisor[] = $hypervisorType;
            }

            $pId = $hypervisorType;
            //在添加任务
            if(!in_array($pId.$taskuuid, $task)){
                $task[] = $pId.$taskuuid;
                $name = $d['task_name'];
                $node[] = array(
                    "id" => $pId.$taskuuid,
                    "pId" => $pId,
                    "name" => $name,
                    "open" => false,
                    "nocheck" => false,
                    "type" => 1,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['create_time']),
                    "vcenteruuid" => $vcenteruuid,
                    "taskuuid" => $taskuuid,
                    "hypervisor" =>$hypervisorType,
                    "isParent" => true,
                    "eventtype" => "task"
                );
            }

            $pId = $pId.$taskuuid;
            //添加虚拟机
            if(!in_array($pId.$vmuuid, $vm)){
                $vm[] = $pId.$vmuuid;
                $name = $d['task_name'];
                $node[] = array(
                    "id" => $pId.$vmuuid,
                    "pId" => $pId,
                    "name" => $d['vm_name'],
                    "nocheck" => true,
                    "type" => 2,
                    "icon" => './img/vm/vm.png',
                    "iconSkin" => 'vm',
                    "title" => $d['vm_name'],
                    "vcenteruuid" => $vcenteruuid,
                    "vmuuid" => $vmuuid,
                    "taskuuid" => $taskuuid,
                    "hypervisor" =>$hypervisorType,
                    "createtime" => $this->parseDate($d['create_time']),
                    "eventtype" => "vm",
                    "vmname" => $d['vm_name'],
                    'taskname' => $d['task_name']
                );
            }
        }

        return json_encode($node);
    }

    /**
     * 获取验证的虚拟机源树
     * @param unknown $params
     * @return string
     */
    public function getVerifyVmTree($params){
        $nodeuuid = $params['nodeuuid'];
        $vmHandler = Xphp::instance('Vmhandler');
        $vcenter = Xphp::instance('Vcenter');
        $sql = "select bt.task_uuid, bt.task_name, unix_timestamp(bt.create_time) create_time, vml.vm_uuid, vml.vm_name, vml.vcenter_uuid, vt.hypervisor_type from bd_task bt, vm_task vt, vm_machine_list vml where bt.task_uuid = vt.task_uuid and bt.task_uuid = vml.task_uuid and vt.hypervisor_type = ? and bt.node_uuid = ? and bt.delete_flag = ? and bt.task_type = ? ";
        $sqlParams = array(Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'],$nodeuuid, Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['TASKTYPE']['BACKUP']);

        $sql .= " and bt.user_uuid = ? ";
        $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));

        $data = $this->dbSelect($sql, $sqlParams);

        $node = array();
        $hypervisor = array();
        $task = array();
        $vm = array();
        foreach ($data as $d){
            $taskuuid = $d['task_uuid'];
            $vmuuid = $d['vm_uuid'];
            $vcenteruuid = $d['vcenter_uuid'];
            $hypervisorType = intval($d['hypervisor_type']);
            //先添加虚拟化类型
            if(!in_array($d['hypervisor_type'], $hypervisor)){
                $name = Xphp::$_config['VMHYPERVISORDES'][$hypervisorType];
                $node[] = array(
                    "id" => $hypervisorType,
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => true,
                    "type" => -1,
                    "iconSkin" => $vcenter->getHypervisorIcon($hypervisorType, false),
                );
                $hypervisor[] = $hypervisorType;
            }

            $pId = $hypervisorType;
            //在添加任务
            if(!in_array($pId.$taskuuid, $task)){
                $task[] = $pId.$taskuuid;
                $name = $d['task_name'];
                $node[] = array(
                    "id" => $pId.$taskuuid,
                    "pId" => $pId,
                    "name" => $name,
                    "open" => false,
                    "nocheck" => false,
                    "type" => 1,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['create_time']),
                    "vcenteruuid" => $vcenteruuid,
                    "taskuuid" => $taskuuid,
                    "hypervisor" =>$hypervisorType,
                    "isParent" => true,
                    "eventtype" => "task"
                );
            }

            $pId = $pId.$taskuuid;
            //添加虚拟机
            if(!in_array($pId.$vmuuid, $vm)){
                $vm[] = $pId.$vmuuid;
                $name = $d['task_name'];
                $node[] = array(
                    "id" => $pId.$vmuuid,
                    "pId" => $pId,
                    "name" => $d['vm_name'],
                    "nocheck" => false,
                    "type" => 2,
                    "icon" => './img/vm/vm.png',
                    "iconSkin" => 'vm',
                    "title" => $d['vm_name'],
                    "vcenteruuid" => $vcenteruuid,
                    "vmuuid" => $vmuuid,
                    "vmname" => $d['vm_name'],
                    "taskuuid" => $taskuuid,
                    "hypervisor" =>$hypervisorType,
                    "createtime" => $this->parseDate($d['create_time']),
                    "eventtype" => "vm",
                    'taskname' => $d['task_name']
                );
            }
        }

        return json_encode($node);
    }

    /**
     * 获取验证时间点源树
     * @param unknown $params
     * @return string
     */
    public function getVerifyPointTree($params){
        $nodeuuid = $params['nodeuuid'];
        $sql = "select bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time,
    			bbt.task_uuid, vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.hypervisor_type, bsr.node_uuid
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                      bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type in (".Xphp::$_config['MODULE_TYPE']['VM'].",".Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'].")  and bbt.data_local_flag = ? and vbt.hypervisor_type = ? ";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET'], Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE']);

        // 关联管理用户判断 存储资源 - 查看   resmanagement_look
        $authUser = $_SESSION['authUser']['data_manager_look'] ?? [];
        if (!empty($authUser)) {
            // 表示有管理的用户
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
            $sql .= "and bbt.user_uuid in " . $useruuidArr;
        } else {
            // 这个是之前的业务逻辑
            //对应用户
            $sql .= " and bbt.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
        }


        if(!empty($nodeuuid)){
            //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.node_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $sql .= " order by vbt.vm_uuid, bbt.timepoint desc, vbt.vm_timepoint_id  ";


        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();

        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        $vcenter = Xphp::instance('Vcenter');

        //得到当前任务所有uuid
        $currentTaskUUID = $vmHandler->getCurrentAllTaskUUID();





        //定义task vm timepoint
        $hypervisor = array();
        $task = array();
        $vm = array();
        $pid = null;
        foreach ($data as $d){

            $taskuuid = $d['task_uuid'];
            $taskCreateTime = $d['task_create_time'];
            //检查并添加虚拟化类型
            if(!in_array($d['hypervisor_type'], $hypervisor)){
                $hypervisorType = intval($d['hypervisor_type']);
                $name = Xphp::$_config['VMHYPERVISORDES'][$hypervisorType];
                $node[] = array(
                    "id" => $d['hypervisor_type'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "open" => true,
                    "nocheck" => true,
                    "type" => -1,
                    "iconSkin" => $vcenter->getHypervisorIcon($hypervisorType, false),
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                );
                $hypervisor[] = $hypervisorType;
            }
            $hypervisortype = $d['hypervisor_type'];

            $taskName = $jobHandler->getTimepointTaskname($d['task_uuid'],$d['task_name']);
            //检查并添加task
            if(!in_array($hypervisortype.$taskuuid, $task)){
                $task[] = $hypervisortype.$taskuuid;
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                $node[] = array(
                    "id" => $hypervisortype.$taskuuid,
                    "pId" => $d['hypervisor_type'],
                    "name" => $name,
                    "open" => false,
                    "nocheck" =>true,
                    "type" => 1,
                    "icon" => './img/platform/flag.png',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_TASK_CREATE_TIME'] . ': ' . date('Y-m-d H:i:s', $d['task_create_time']),
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "hypervisor" => $d['hypervisor_type'],
                    "isParent" => true,
                    "eventtype" => "task",
                    "taskuuid" => $taskuuid
                );
            }
            $vmuuid = $d['vm_uuid'];
            //检查并添加vm
            if(!in_array($hypervisortype. $taskuuid.$vmuuid, $vm)){
                $vm[] = $hypervisortype. $taskuuid. $vmuuid;
                $node[] = array(
                    "id" => $hypervisortype. $taskuuid.$vmuuid,
                    "pId" => $hypervisortype.$taskuuid,
                    "name" => $d['vm_name'],
                    "open" => false,
                    "nocheck" => true,
                    "type" => 2,
                    "icon" => './img/vm/vm.png',
                    "iconSkin" => 'vm',
                    "title" => Xphp::$_lang['WEB_PLATFORM_DC_VM_SRC_DIR_PATH'] . ': ' . $d['dir_path'],
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "vmuuid" => $d['vm_uuid'],
                    "taskuuid" => $taskuuid,
                    "nodeuuid" => $d['node_uuid'],
                    "createtime" => $this->parseDate($d['task_create_time']),
                    "hypervisor" => $d['hypervisor_type'],
                    "isParent" => true,
                    "clickshow" => true,
                    "eventtype" => "vm",
                    'taskname' => $d['task_name']
                );
            }else {
                continue;
            }

        }
        return json_encode($node);
    }

    /**
     * 获取数据验证的时间点树
     * @param unknown $params
     * @return string
     */
    public function getVerifyTimepoint($params){
        $taskuuid = $params['taskuuid'];
        $hypervisor = $params['hypervisor'];
        $vmuuid = $params['vmuuid'];
        $vmcheck = $params['vmcheck'];
        $nodeuuid =$params['nodeuuid'];
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        $node = array();
        $sqlPoint = "select bbt.detail, bbt.deleted_flag, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_name, bbt.depend_point_uuid,
                      unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid, bbt.remarks, bbt.archive_flag,
		              vbt.vm_config, vbt.vm_uuid, vbt.vm_name, vbt.dir_path, vbt.vcenter_uuid, vbt.version, vbt.hypervisor_type, bsr.node_uuid, bsr.status
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = vbt.timepoint_uuid and
                       bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = ?
	                   and bbt.import_flag = ? and bbt.module_type in (2, 9) and
                       bbt.task_uuid = ?  and
                       vbt.vm_uuid = ? and vbt.hypervisor_type = ? and data_local_flag = ? ";


        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['SET'], $flag['UNSET'], $taskuuid,  $vmuuid ,$hypervisor, $flag['SET']);
        if(!empty($nodeuuid)){
            $sqlPoint .= " and bsr.node_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }
        $sqlPoint .= " order by vbt.vm_uuid,  bbt.timepoint";
        $pointData = $this->dbSelect($sqlPoint, $sqlParams);
        $pid =  $vmuuid . $taskuuid;

        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        foreach ($pointData as $point){
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            }else{
                $unfullList[] = $point;

            }
        }
        while (!empty($unfullList)){
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key => $unfull){
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $k => $value){
                    if($dependId == $k){
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$k];
                        $unfullList = array_splice($unfullList, $key, 1);

                        $unfullCountTmp--;
                    }
                    continue;
                }
            }

            if($unfullCount == $unfullCountTmp || $unfullCountTmp == 0) break;

        }
        $timepoint = array();
        foreach ($pointData as $point){
            $availableFlag = true;

            //如果时间点正在合并且不可用
            if($point['archive_flag'] == Xphp::$_config['FLAG']['SET'] || $point['status'] != Xphp::$_config['STORAGE_STATUS']['ONLINE']){
                $availableFlag = false;
            }
            //判断是合并中还是离线状态
            $archive_status_str = true;
            $vm_status_str = true;
            //如果是合并中
            if($point['archive_flag'] == Xphp::$_config['FLAG']['SET']){
                $archive_status_str = false;
            }
            //如果不是在线状态
            if($point['status'] != Xphp::$_config['STORAGE_STATUS']['ONLINE']){
                $vm_status_str = false;
            }

            $taskuuid = $point['task_uuid'];
            $vmuuid = $point['vm_uuid'];
            $timepointuuid = $point['timepoint_uuid'];
            $taskCreateTimeIn = $this->parseDate($point['task_create_time']);
            $config = json_decode($point['detail'], true);
            $vmconfig = json_decode($point['vm_config'], true);
            $config['cpu_num'] = intval($vmconfig['numCPUs']);  //cpu插槽数
            $config['cpu_per_socket'] = intval($vmconfig['numCoresPerSocket']); //cpu核心数
            $memory = $utils->calSizeToValueAndUnit(intval($vmconfig['memoryMB']) * 1024 * 1024, true);

            $config['memory_size'] = $memory['value'];
            $config['memory_unit']  = $memory['unit'];
            //检查并添加完备点
            if($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']){
                if(!in_array($timepointuuid, $timepoint)){
                    $name = $this->parseDate($point['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($point['backup_mode']) . ")";
                    //根据合并和是否在线展示不同文字信息
                    if(!$archive_status_str){
                        $name .= "(" . Xphp::$_lang['UI_PUBLIC_IN_MERGE'] . ")";
                    }
                    if(!$vm_status_str){
                        $name .= "(" . Xphp::$_lang['UI_PUBLIC_STORAGE_OFF'] . ")";
                    }

                    if(!empty($config) && $config['password_auto_flag'] == 2 && !empty($config['password'])){
                        $name .= '<i class="fa fa-lock"></i>';
                    }
                    $timepoint[] =  $timepointuuid;
                    $node[] = array(
                        "id" =>  $timepointuuid,
                        "pId" =>  $point['hypervisor_type'] . $taskuuid . $vmuuid,
                        "name" => $name,
                        "checked" => $vmcheck && $availableFlag,
                        "type" => 3,
                        "vmuuid" => $point['vm_uuid'],
                        "vmname" => $point['vm_name'],
                        "pointname" => $this->parseDate($point['timepoint']),
                        "vcenteruuid" => $point['vcenter_uuid'],
                        "timepointuuid" => $timepointuuid,
                        "createtime" => $taskCreateTimeIn,
                        "hypervisor" => $point['hypervisor_type'],
                        "nodeuuid" => $point['node_uuid'],
                        "taskuuid" => $taskuuid,
                        "path" => $point['dir_path'],
                        "version" => $point['version'],
                        "icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
                        "title" => $vmHandler->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                        "hypervisor" => $hypervisor,
                        "chkDisabled" => !$availableFlag,
                        "config" => $config,
                        "eventtype" => "point",
                        'taskname' => $point['task_name'],
                        'taskuuid' => $point['task_uuid']
                    );
                    //添加了完全备份时间点继续下一次
                    $pid = $timepointuuid;
                    continue;
                }
            }


            $node[] = array(
                "id" => $timepointuuid,
                "pId" => $fulluuidList[$timepointuuid],
                "name" => $this->parseDate($point['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($point['backup_mode']) . ")",
                "checked" => $vmcheck,
                "type" => 4,
                "vmuuid" => $point['vm_uuid'],
                "vmname" => $point['vm_name'],
                "pointname" => $this->parseDate($point['timepoint']),
                "vcenteruuid" => $point['vcenter_uuid'],
                "nodeuuid" => $point['node_uuid'],
                "timepointuuid" => $timepointuuid,
                "hypervisor" => $point['hypervisor_type'],
                "path" => $point['dir_path'],
                "version" => $point['version'],
                "icon" => $vmHandler->getTimepointIcon($point['backup_mode']),
                "title" => $vmHandler->getBackupTimepointTreeTitle($point['dir_path'], $point['remarks']),
                "chkDisabled" => $disabledflag,
                "config" => $config,
                "eventtype" => "point",
                'taskname' => $point['task_name'],
                'taskuuid' => $point['task_uuid'],
            );


        }

        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);

    }

    /**
     * 根据验证类型获取相应的验证源类型
     * @param unknown $params
     * @return string
     */
    public function getVerifyType($params){
        $type = intval($params['type']);
        $sourceType = array(1=>Xphp::$_lang['UI_VERIFY_AS_JOB'], 2=>Xphp::$_lang['UI_VERIFY_AS_VM'],3=>Xphp::$_lang['UI_VERIFY_AS_TIMEPOINT']);
        $info = array();
        foreach ($sourceType as $key=>$s){
            //自动验证不需要按时间点验证
            if($type == 0 && $key == 3) continue;
            $info[] = array(
                'text' => $s,
                'value' => $key
            );
        }

        return json_encode($info);
    }


    /**
     * 获取需要选择的虚拟实验室
     * @return string
     */
    public function getSelectLabList(){
        $list = array();
        $sql = "select virtual_lab_uuid, virtual_lab_name,hypervisor_type from sr_virtual_lab where proxy_status not in (1,4,5) ";
        // 关联管理用户判断 存储资源 - 查看   resmanagement_look
        $authUser = $_SESSION['authUser']['data_manager_look'] ?? [];
        if (!empty($authUser)) {
            // 表示有管理的用户
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            // 下面是具体的业务逻辑 $useruuidArr 是最终的用户ID字符串
            $sql .= "and user_uuid in " . $useruuidArr;
        } else {
            // 这个是之前的业务逻辑
        }

        $data = $this->dbSelect($sql);
        $list[] = array(
            'text' => Xphp::$_lang['UI_PUBLIC_SELECT'],
            'uuid' => ""
        );
        if(!empty($data)){
            $third = Xphp::$_lang['UI_VIRTUAL_LAB_THIRD_PARTY_VIRTUALIZATION'];
            $agent = Xphp::$_lang['UI_VIRTUAL_LAB_EMBEDDED_VM'];
            foreach ($data as $d){
                $list[] = array(
                    'text' => $d['virtual_lab_name'] . '(' . ($d['hypervisor_type'] == 108 ? $agent : $third) . ')',
                    'uuid' => $d['virtual_lab_uuid'],
                    'is_vm' => $d['hypervisor_type'] == 108 ? 1 : 0
                );
            }
        }

        return json_encode($list);
    }


    /**
     * 获取可用数据验证任务名返回界面
     * @return string
     */
    public function getVerifyTaskName(){

        $taskName  ="";
        if($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw"){
            $taskName = Xphp::$_lang['WEB_PLATFORM_DES_SURE_BACKUP'].Xphp::$_lang['UI_VIRTURL_LAB_JOB'];
        }else{
            $taskName = Xphp::$_lang['WEB_PLATFORM_DES_SURE_BACKUP']." ".Xphp::$_lang['UI_VIRTURL_LAB_JOB'];
        }
        return $this->getValidTaskName($taskName);
    }

    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    private function getValidTaskName($taskName){
        $oldTaskName = $taskName;
        for($i=1; $i<1000; $i++){
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if(empty($data) && empty($data1)){
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }

    /**
     * 创建数据验证任务
     * @param unknown $params
     * @return string
     */
    public function createVerifyJob($params){
        //public params

        $opName = 'VM_PRIVATE_TASK_OP_CREATE_SURE_BACKUP_TASK';

        $submodule_type = $params['srcInfo']['hypervisor'];

        //获取源所在节点
        $nodeuuid = $params['nodeuuid'];
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        //全局策略标记
        $strategygroupuuid = "";

        //验证执行方式 立即执行|按时间策略
        $timeType = intval($params['highInfo']['timeStrategy']['type']);
        //任务名
        $task_name = $params['taskName'];
        //模块类型
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        //恢复方式 这里不需要，默认传0
        $recovery_position = 0;
        //时间策略
        $time_strategy_list = $vmHandler->groupRecoverTimeList($params['highInfo']['timeStrategy'], $strategygroupuuid);
        //传输策略 这里不需要
        $transport_strategy = array();

        //配置统一参数
        $pfMsg = $this->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position,
            $timeType, $time_strategy_list, $transport_strategy);

        //验证的备份任务名
        $pfMsg['backup_task_name'] = $params['backup_task_name'];

        //任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['SURE_BACKUP'];
        //虚拟实验室
        $pfMsg['virtual_lab_uuid'] = $params['labInfo']['labuuid'];
        //同时启动虚拟机个数
        $pfMsg['limit_boot_vm_num'] = $params['highInfo']['deal_vm_num'];
        //限速策略
        $pfMsg['speed_limit_strategy_list'] = array();
        //全局策略标记
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        //备份系统节点IP
        $pfMsg['backup_server_ip'] = "";
        //指定网段
        $pfMsg['transport_ip_segment'] = "";
        //备份服务器IP
        $pfMsg['nfs_server_ip'] = $params['highInfo']['nfs_server_ip'];
        //数据验证类型
        $pfMsg['automatic_verification_flag'] = intval($params['auto_verify_flag']);

        //虚拟机信息列表
        $pfMsg['sure_backup_vm_list'] = $this->groupVerifyVMInfo($params['srcInfo']['vmInfo']);

        //备份服务器IP
        $pfMsg['ping_warn_flag'] = $utils->parseBoolToFlag($params['highInfo']['ping_warn_flag']);

        //备份服务器IP
        $pfMsg['heartbeat_warn_flag'] = $utils->parseBoolToFlag($params['highInfo']['heartbeat_warn_flag']);

        //备份服务器IP
        $pfMsg['screenshot_warn_flag'] = $utils->parseBoolToFlag($params['highInfo']['screenshot_warn_flag']);

        //虚拟化类型
        // 获取虚拟实验室的类型
        $type = $this->dbSelect("select hypervisor_type from sr_virtual_lab where virtual_lab_uuid = ?", [$pfMsg['virtual_lab_uuid']]);
        if (!empty($type)) {
            $submodule_type = intval($type[0]['hypervisor_type']);
        }

        $pfMsg['hypervisor_type'] = $submodule_type;
        //任务所在节点
        $pfMsg['node_uuid'] = $nodeuuid;
        $msg = json_encode($pfMsg);
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);

        //返回结果到UI
        if($result){
            //立即启动数据验证任务
            if($timeType == Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY']){
                $startResult = $this->startVerifyJob($params['taskName']);
            }else{
                $startResult = true;
            }
            if($startResult){
                //启动任务成功,直接返回创建任务成功
                return $this->muOpResult($result, $operate, $msg);
            }else {
                //启动任务失败,返回创建任务成功加上启动任务失败提示, 暂时没加提示
                return $this->muOpResult($result, $operate, $msg);
            }
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改数据验证任务
     * @param unknown $params
     * @return string
     */
    public function editVerifyJob($params){
        //public params

        $opName = 'VM_PRIVATE_TASK_OP_MODIFY_SURE_BACKUP_TASK';

        $submodule_type = $params['srcInfo']['hypervisor'];

        //获取源所在节点
        $nodeuuid = $params['nodeuuid'];
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        //全局策略标记
        $strategygroupuuid = "";

        //验证执行方式 立即执行|按时间策略
        $timeType = intval($params['highInfo']['timeStrategy']);
        //任务名
        $task_name = $params['taskName'];
        //模块类型
        $module_type = Xphp::$_config['MODULE_TYPE']['VM'];
        //恢复方式 这里不需要，默认传0
        $recovery_position = 0;
        //时间策略
        $time_strategy_list = $vmHandler->groupRecoverTimeList($params['highInfo']['timeStrategy'], $strategygroupuuid);
        //传输策略 这里不需要
        $transport_strategy = array();

        //配置统一参数
        $pfMsg = $this->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position,
            $timeType, $time_strategy_list, $transport_strategy);

        //验证的备份任务名
        $pfMsg['backup_task_name'] = $params['backup_task_name'];
        //任务uuid
        $pfMsg['task_uuid'] = $params['taskuuid'];
        //任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['SURE_BACKUP'];
        //虚拟实验室
        $pfMsg['virtual_lab_uuid'] = $params['labInfo']['labuuid'];
        //同时启动虚拟机个数
        $pfMsg['limit_boot_vm_num'] = $params['highInfo']['deal_vm_num'];
        //限速策略
        $pfMsg['speed_limit_strategy_list'] = array();
        //全局策略标记
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        //备份系统节点IP
        $pfMsg['backup_server_ip'] = "";
        //指定网段
        $pfMsg['transport_ip_segment'] = "";
        //备份服务器IP
        $pfMsg['nfs_server_ip'] = $params['highInfo']['nfs_server_ip'];
        //数据验证类型
        $pfMsg['automatic_verification_flag'] = intval($params['auto_verify_flag']);

        //虚拟机信息列表
        $pfMsg['sure_backup_vm_list'] = $this->groupVerifyVMInfo($params['srcInfo']['vmInfo']);

        //虚拟化类型
        $pfMsg['hypervisor_type'] = $submodule_type;
        //任务所在节点
        $pfMsg['node_uuid'] = $nodeuuid;
        $msg = json_encode($pfMsg);

        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 立即启动数据验证任务
     * @param unknown $taskName
     * @return boolean|mixed
     */
    private function startVerifyJob($taskName){
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type, ssb.hypervisor_type
                from bd_task bt, sr_sure_backup ssb where bt.task_uuid = ssb.task_uuid and bt.task_name = ? and bt.module_type = ? and bt.task_type = ?
                order by bt.id desc";
        $data = $this->dbSelect($sql, array($taskName, Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['SURE_BACKUP']));
        if(!$data) return false;

        //任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
        $params = array(
            'uuid' => $data[0]['task_uuid'],
            'module' => $data[0]['module_type'],
            'subModule' => $data[0]['hypervisor_type'],
            'taskType' => $data[0]['task_type'],
            'startType' => Xphp::$_config['BACKUP_MODE']['FULL'],
        );
        //调用系统统一启动任务接口.不重新写
        $jobHandler = Xphp::instance('JobHandler');
        $result = $jobHandler->startJob($params);
        $result = json_decode($result, true);
        //这里直接返回成功或失败 bool
        return $result['re'];
    }

    /**
     * 组合数据验证需要虚拟机信息
     * @param unknown $vmList
     * @return string[][]|number[][]|unknown[][]|NULL[][]|mixed[][]
     */
    public function groupVerifyVMInfo($vmList){
        $list = array();
        $vmRecoveryHandler = Xphp::instance('VmRecoveryHandler');
        foreach ($vmList as $d){
            $list[] = array(
                'orig_vm_uuid' => $d['vmuuid'],
                'orig_vm_name' => $d['vmname'],
                'new_vm_name' => $d['new_vm_name'],
                'task_uuid' => $d['taskuuid'],
                'timepoint_uuid' => $d['timepointuuid'],
                'ping_test_flag' => $d['ping_test_flag'] ? 0 : 4,   //0代表等待 开启， 4代表跳过关闭
                'heartbeat_flag' => $d['heartbeat_flag'] ? 0 : 4,
                'print_screen_flag' => $d['print_screen_flag'] ? 0 : 4,
                'original_ip' => $d['original_ip'],
                'max_boot_time' => intval($d['max_boot_time']),
                'advance_vm_config' => $this->groupVerifyVMConfig($d, $vmRecoveryHandler),
                'extension_info' => ""
            );
        }

        return $list;
    }

    /**
     * 组合创建数据验证任务需要传递的虚拟机配置选项参数
     * @param unknown $vm
     * @return string
     */
    private function groupVerifyVMConfig($vm, $vmRecoveryHandler){
        $memory = intval($vm['memory']);
        //不是原配置，计算真实内存大小
        if($memory != 0){
            $memory = $vmRecoveryHandler->calUnitToSize( intval($vm['memory']), $vm['memory_unit']);
        }
        $info = array(
            'cpu_socket' => intval($vm['cpunum']),
            'cores_per_socket' => intval($vm['corenum']),
            'vm_memory' => $memory,
            'auto_conf_flag' => Xphp::$_config['FLAG']['UNSET'],
            'boot_mode' => 0,  //引导模式
            'vm_version' => "",
            'vm_network_list' => array(),    //网络
            'vm_disk_list' => array(),          //磁盘
            'zone_config' => "",                   //可用域
            'video_device' => array('video_type'=> 0, 'vram_memory' => 0),                              //显卡,暂时没有用,保留字段
            'other_config' => "",                 //其他配置
            'start_vm_flag' => $vm['autostart'] ? 1 : 2,
            'os_type' => $vm['ostype'],
        );

        return json_encode($info, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 获取虚拟实验室配置信息
     * @param unknown $params
     * @return string
     */
    public function getLabConfigInfo($params){
        $labuuid = $params['uuid'];
        $sql = "select vh.host_uuid, vh.host_name, vh.host_ip, svl.proxy_network_name, svl.proxy_ip, svl.proxy_netmask, svl.proxy_gateway from sr_virtual_lab svl, vm_host vh where svl.host_uuid = vh.host_uuid and svl.virtual_lab_uuid = ?";
        $data = $this->dbSelect($sql, array($labuuid));
        $proxyInfo = array();
        $networkList = array();
        if(!empty($data)){
            $hostInfo = $this->getHostVmNum($data[0]['host_uuid']);
            $proxyInfo = array(
                'host_name' => $data[0]['host_name'] == $data[0]['host_ip'] ? $data[0]['host_ip'] : $data[0]['host_name']."(".$data[0]['host_ip'].")",
                'proxy_name' => $data[0]['proxy_network_name'] == '' ? $data[0]['proxy_ip'] : ($data[0]['proxy_network_name'] . "(".$data[0]['proxy_ip'].")"),
                'proxy_netmask' => $data[0]['proxy_netmask'],
                'proxy_gateway' => $data[0]['proxy_gateway'],
                'all_vm_num' => $hostInfo['all_vm'],
                'run_vm_num' => $hostInfo['run_vm']
            );

        } else {
            // 内嵌的 主机也显示节点列表的名称、代理网关取  sr_virtual_lab 表里面的proxy_name(proxy_ip) 组合
            $sql = 'select bn.node_type,bn.host_name,svl.proxy_name,svl.proxy_ip,svl.proxy_gateway,svl.proxy_netmask,
                    (select count(*) from vm_emd where node_uuid = bn.node_uuid) total,
                    (select count(*) from vm_emd where node_uuid = bn.node_uuid and status = 1) running
                    from bd_node bn,sr_virtual_lab svl
                    where bn.node_uuid = svl.node_uuid and svl.virtual_lab_uuid = ?';
            $infos = $this->dbSelect($sql, [$labuuid]);
            if (!empty($infos)) {
                $nodetype = $infos[0]['node_type'] == 1 ? Xphp::$_lang['UI_STORAGE_REMOTE_NODE_MASTER'] : Xphp::$_lang['UI_STORAGE_REMOTE_NODE_SUB'];
                $proxyInfo = [
                    'host_name' => $infos[0]['host_name'] . '(' . $nodetype . ')',
                    'proxy_name' =>  $infos[0]['proxy_name'] . '(' . $infos[0]['proxy_ip'] . ')',
                    'proxy_gateway' => $infos[0]['proxy_gateway'],
                    'proxy_netmask' => $infos[0]['proxy_netmask'],
                    'all_vm_num' => $infos[0]['total'],
                    'run_vm_num' => $infos[0]['running'],
                ];
            }
        }

        //单独获取隔离网络列表信息
        $sql = "select product_network_name, product_network_netmask, product_network_gateway,
                isolated_network_name, isolated_network_netmask, isolated_network_gateway from sr_network_map_list where virtual_lab_uuid = ?";
        $data = $this->dbSelect($sql, array($labuuid));
        if(!empty($data)){
            foreach ($data as $d){
                $networkList[] = array(
                    'product_network_name' => $d['product_network_name'],
                    'product_network_netmask' => $d['product_network_netmask'],
                    'product_network_gateway' => $d['product_network_gateway'],
                    'isolated_network_name' => $d['isolated_network_name'],
                    'isolated_network_netmask' => $d['isolated_network_netmask'],
                    'isolated_network_gateway' => $d['isolated_network_gateway'],
                );
            }
        }

        //组合代理网关信息和隔离网络列表信息
        $info = array(
            'proxy_info' => $proxyInfo,
            'network_map_list' => $networkList
        );

        return json_encode($info);

    }

    /**
     * 获取宿主机虚拟机总数和运行的虚拟机个数
     * @param string $hostuuid
     * @return number[]
     */
    private function getHostVmNum($hostuuid){
        $info = array();
        $sql = "select distinct uuid, power_state from vm_tree where type = ? and host_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['VM_TREE_TYPE']['VM'], $hostuuid));
        $allVm = count($data);
        $runVm = 0;
        foreach ($data as $d){
            //计算出宿主机上所有运行中的虚拟机
            if(intval($d['power_state']) == Xphp::$_config['MACHINESTATUS']['POWEREDON']){
                $runVm ++;
            }
            continue;
        }
        $info = array(
            'all_vm' => $allVm,
            'run_vm' => $runVm
        );
        return $info;
    }


    /**
     * 获取修改虚拟实验室旧的信息
     * @param unknown $params
     * @return string
     */
    public function getLabEditAllInfo($params){
        $labuuid = $params['labuuid'];  //修改用虚拟实验室uuid
        $sql = "select host_uuid, vcenter_uuid, storage_uuid, virtual_lab_name, proxy_name, proxy_ip, proxy_netmask, proxy_gateway, proxy_network_name,proxy_network_uuid,
                hypervisor_type, isolated_vswitch_name, resource_pool_name, folder_name,node_uuid,hypervisor_type from sr_virtual_lab where virtual_lab_uuid = ? ";
        $data = $this->dbSelect($sql, array($labuuid));
        //组合修改第一步信息获取
        $labInfo = array(
            'virtual_lab_name' => $data[0]['virtual_lab_name'],
            'proxy_name' => $data[0]['proxy_name'],
            'resource_pool_name' => $data[0]['resource_pool_name'],
            'isolated_vswitch_name' => $data[0]['isolated_vswitch_name'],
            'folder_name' => $data[0]['folder_name'],
        );

        //组合代理网关网络信息
        $proxyInfo = array(
            'network_uuid' => $data[0]['proxy_network_uuid'],
            'network_name' => $data[0]['proxy_network_name'],
            'ip_address' => $data[0]['proxy_ip'],
            'netmask' => $data[0]['proxy_netmask'],
            'gateway' => $data[0]['proxy_gateway']
        );
        //组合第二步信息获取
        $hostInfo = array(
            'hypervisor' => $data[0]['hypervisor_type'],
            'host_uuid' => $data[0]['host_uuid'],
            'vcenter_uuid' => $data[0]['vcenter_uuid'],
            'storage_uuid' => $data[0]['storage_uuid'],
            'proxy_info' => $proxyInfo,
            'source_type' => $data[0]['hypervisor_type'] == 108 ? 2 : 1,
            'node_uuid' => $data[0]['node_uuid'],
        );

        //获取隔离网络配置信息
        $sql = "select product_network_name,product_network_uuid, product_network_netmask, product_network_gateway,
                isolated_network_name, isolated_network_netmask, isolated_network_gateway from sr_network_map_list where virtual_lab_uuid = ?";
        $data = $this->dbSelect($sql, array($labuuid));
        $networkInfo = array();
        foreach ($data as $d){
            $productInfo = array(
                'name' => $d['product_network_name'],
                'uuid' => $d['product_network_uuid'],
                'netmask' => $d['product_network_netmask'],
                'gateway' => $d['product_network_gateway']
            );
            $isolatedInfo = array(
                'name' => $d['isolated_network_name'],
                'uuid' => "",
                'netmask' => $d['isolated_network_netmask'],
                'gateway' => $d['isolated_network_gateway'],
            );
            $networkInfo[] = array(
                'productInfo' => $productInfo,
                'isolatedInfo' => $isolatedInfo
            );
        }

        $info = array(
            'virtual_lab_uuid' => $labuuid,
            'labInfo' => $labInfo,
            'hostInfo' => $hostInfo,
            'networkInfo' => $networkInfo
        );

        return json_encode($info);
    }


    /**
     * 获取数据验证任务报告
     * @param unknown $params
     * @return string
     */
    public function getVerifyJobReport($params){
        $historyuuid = $params['history_uuid'];
        $id = $params['id'];
        $taskuuid = $params['taskuuid'];
        $sendFlag = $params['send_flag'];
        if(!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], Xphp::$_config['ENTERPRISE'])){
            $content = file_get_contents(ROOT_PATH.'/email/email-verify-data-report-oem.html');
        }else if(Xphp::$_config['lang'] == "en-us"){
            $content = file_get_contents(ROOT_PATH.'/email/email-verify-data-report-en.html');
        }else{
            $content = file_get_contents(ROOT_PATH.'/email/email-verify-data-report.html');
        }
        //组合报告
        $sql = "select bht.history_uuid, bht.task_name, bht.task_type, bht.error_code, unix_timestamp(bht.start_time) start_time, unix_timestamp(bht.finish_time) finish_time, bht.details from bd_history_task bht ";
        $sqlParams = array();
        if(!empty($historyuuid)){
            $sql .= " where bht.id = ? ";
            $sqlParams = array($historyuuid);
        }else{
            $sql .= " where bht.id = ? ";
            $sqlParams = array($id);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $jobHandler = Xphp::instance('JobHandler');
        $utils = Xphp::instance('Utils');


        //任务名
        $taskName =  $data[0]['task_name']."[".$ptDes['TASKTYPEDES'][intval($data[0]['task_type'])]."]";
        $content = str_replace('taskName', $taskName, $content);
//         //创建时间
//         $createTime = $this->parseDate($data[0]['create_time']);
//         $content = str_replace('taskCreateTime', $createTime, $content);
        //历史任务状态
        $taskStatus = $jobHandler->getHistoryJobResultDes($data[0]['error_code']);
        $fontStyle = $data[0]['error_code'] == 0 ? 'font-success' : 'font-error';
        $content = str_replace('font-style', $fontStyle, $content);
        $content = str_replace('taskStatus', $taskStatus, $content);
        //开始时间
        $startTime = $this->parseDate($data[0]['start_time']);
        $content = str_replace('startTime', $startTime, $content);
        //结束时间
        $finishTime = $this->parseDate($data[0]['finish_time']);
         $content = str_replace('reportTime', $finishTime, $content);
        $content = str_replace('endTime', $finishTime, $content);
        //持续时间
        $intval = intval($data[0]['finish_time']) - intval($data[0]['start_time']);
        $intvalTime = $utils->secToTime($intval);
        $content = str_replace('runTime', $intvalTime, $content);

        $historyuuid = $data[0]['history_uuid'];
        $sqlVm = "select distinct ssr.report_id, ssr.timestamp, ssr.screen_shot_path, ssr.item_error_code, ssr.item_name, unix_timestamp(ssr.start_time) start_time, unix_timestamp(ssr.end_time) end_time,  ssr.extension_info,bht.module_type, bht.submodule_type
                  from sr_surebackup_report ssr, bd_history_task bht where ssr.history_uuid = bht.history_uuid and ssr.history_uuid = ? ";
        $dataVm = $this->dbSelect($sqlVm, array($historyuuid));
        //获取报告中的虚拟机
        $successNum = 0;
        $vmInfo = array();
        foreach ($dataVm as $d){
            $extensionInfo = json_decode($d['extension_info'],true);
            $info = array(
                'name' => $d['item_name'],//虚拟机名
                'status' => Xphp::$_vmdes['VERIFY_VM_STATUS'][intval($d['item_error_code'])],//状态
                'start_time' => $this->parseDate($d['start_time']), //开始时间
                'end_time' =>$this->parseDate($d['end_time']),//结束时间
                'integrity_check_status' => intval($extensionInfo['integrity_check_status']),//完整性校验
                'vir_det_kill_status' =>  intval($extensionInfo['vir_det_kill_status']),//病毒查杀
                'doc_consistency_status' =>  intval($extensionInfo['doc_consistency_status']),//文件对比
                'screen_compare_status' =>  intval($extensionInfo['screen_compare_status']),//截屏对比
                'item_status' =>  intval($extensionInfo['item_status']),//对象状态
                'ping_test_status' =>  intval($extensionInfo['ping_test_status']),//ping
                'heartbeat_status' =>  intval($extensionInfo['heartbeat_status']),//心跳测试
                'print_screen_status' =>  intval($extensionInfo['print_screen_status']),//截屏
                'item_status_des' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($extensionInfo['item_status'])],//对象状态描述
                'integrity_check_status_des' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($extensionInfo['integrity_check_status'])],//完整性校验
                'vir_det_kill_status_des' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($extensionInfo['vir_det_kill_status'])],//病毒查杀
                'doc_consistency_status_des' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($extensionInfo['doc_consistency_status'])],//文件对比
                'screen_compare_status_des' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($extensionInfo['screen_compare_status'])],//截屏对比
                'ping_test_status_des' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($extensionInfo['ping_test_status'])],//ping
                'heartbeat_status_des' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($extensionInfo['heartbeat_status'])],//心跳测试
                'print_screen_status_des' =>  Xphp::$_vmdes['VERIFY_FUNC_STATUS'][intval($extensionInfo['print_screen_status'])],//截屏
                'screen_src' => $d['screen_shot_path'],
                'report_id' => $d['report_id'],
                'timepoint' => $d['timestamp'],
                'total_file_num' => intval($extensionInfo['total_file_num']),//总文件数量
                'scan_file_num' => intval($extensionInfo['scan_file_num']),//扫描文件数量
                'infected_file_num' => intval($extensionInfo['infected_file_num']),//病毒文件数量
                 'task_name' => $extensionInfo['backup_task_name'],
                'task_type' => $extensionInfo['backup_task_type'],
                'backup_mode' => intval($extensionInfo['backup_mode']),
                'module_type' => intval($extensionInfo['item_module_type']),
                'submodule_type' => intval($extensionInfo['item_submodule_type'])
            );
            if($extensionInfo['item_status'] == 5){
                //成功
                $successNum++;
            }
            $vmInfo[] = $info;
        }

        //虚拟机总个数
        $content = str_replace('vmNum', count($vmInfo), $content);
        //成功虚拟机个数
        $content = str_replace('runNum', $successNum, $content);
        //虚拟机列表显示
        $vmtable = "";
        $imgContent = "";
         $imgSrc = "";
        foreach ($vmInfo as $d){
            //组合验证报告虚拟机列表信息显示
            $color = "color: #313344;";
            if($d['infected_file_num'] > 0){
                $color = "color:#f1416c;";
            }
             //组装时间点信息
            $timepointDes = $d['timepoint']."(".$ptDes['BACKUP_MODE_DES'][intval($d['backup_mode'])] .Xphp::$_lang['WEB_PLATFORM_DES_BACKUP']. ")";

            //数据库模块单独处理日志备份和归档日志备份描述显示
            if(intval($d['module_type']) == Xphp::$_config['MODULE_TYPE']['DB'] ){
                //以下是需要显示归档日志备份点的数据库类型
                $dbList = [2, 4, 5, 6, 7, 8, 10, 11, 12 ];
                if (intval($d['current_mode']) == Xphp::$_config['BACKUP_MODE']['LOG'] && in_array(intval($d['submodule_type']),$dbList)){
                    $timepointDes = $d['timepoint']."(".$ptDes['BACKUP_MODE_DES'][5] .Xphp::$_lang['WEB_PLATFORM_DES_BACKUP']. ")";//显示归档日志备份
                }
            }
            $taskTypeDes = $ptDes['MODULE_TYPE_DES'][intval($d['module_type'])]. $ptDes['TASKTYPEDES'][intval($d['task_type'])];
            if(intval($d['task_type']) == Xphp::$_config['TASKTYPE']['DB_BACKUP']){
                $taskTypeDes = $ptDes['TASKTYPEDES'][intval($d['task_type'])];
            }
            $info = ' <li>'.
                        '<div style="margin-bottom:30px;">'.$d['name'].'</div>'.
                         '<div class="content-list" >'.
                        '<p ><span>'.Xphp::$_lang['UI_PUBLIC_TASK_RNAME'].': </span><span>'.$d['task_name'].'</span></p>'.
                        '<p ><span>'.Xphp::$_lang['UI_PUBLIC_TASK_TYPE'].': </span><span>'.$taskTypeDes.'</span></p>'.
                        '<p ><span></span></p>'.
                        '</div>'.
                        '<div class="content-list" >'.
                            '<p ><span>'.Xphp::$_lang['UI_PUBLIC_STATUS'].': </span><span class="'.$this->getVerifyClass($d['item_status']).'">'.$d['item_status_des'].'</span></p>'.
                            '<p ><span>'.Xphp::$_lang['UI_SAFE_STRATEGY_INTEGRITY_CHECK'].': </span><span class="'.$this->getVerifyClass($d['integrity_check_status']).'">'.$d['integrity_check_status_des'].'</span></p>'.
                            '<p ><span>'.Xphp::$_lang['UI_PLATFORM_RECOVERY_JOB_VIRUS_SCAN'].': </span><span class="'.$this->getVerifyClass($d['vir_det_kill_status']).'">'.$d['vir_det_kill_status_des'].'</span></p>'.
                        '</div>'.
                        '<div class="content-list">'.
                                '<p ><span>'.Xphp::$_lang['UI_DRILLS_DETAIL_BACKUP_TIMEPOINT'].': </span><span>'.$timepointDes.'</span></p>'.
                                '<p ><span>'.Xphp::$_lang['UI_VERIFY_SCREEN'].': </span><span class="'.$this->getVerifyClass($d['print_screen_status']).'">'.$d['print_screen_status_des'].'</span></p>'.
                                '<p ><span>'.Xphp::$_lang['UI_VERIFY_VIRUS_TOTAL_FILE_NUM'].': </span><span style="text-align: left;">'.$d['total_file_num'].'</span></p>'.
                        '</div>'.
                        '<div class="content-list">'.
                            '<p ><span>'.Xphp::$_lang['UI_JOB_START_TIME'].': </span><span>'.$d['start_time'].'</span></p>'.
                            '<p ><span>'.Xphp::$_lang['UI_VERIFY_PING_TEST'].': </span><span class="'.$this->getVerifyClass($d['ping_test_status']).'">'.$d['ping_test_status_des'].'</span></p>'.
                            '<p ><span>'.Xphp::$_lang['UI_VERIFY_VIRUS_SCAN_FILE_NUM'].': </span><span style="text-align: left;">'.$d['scan_file_num'].'</span></p>'.
                        '</div>'.
                        '<div class="content-list">'.
                            '<p ><span>'.Xphp::$_lang['UI_JOB_OVER_TIME'].': </span><span>'.$d['end_time'].'</span></p>'.
                            '<p ><span>'.Xphp::$_lang['UI_VERIFY_HEARTBEAT'].': </span><span class="'.$this->getVerifyClass($d['heartbeat_status']).'">'.$d['heartbeat_status_des'].'</span></p>'.
                            '<p ><span>'.Xphp::$_lang['UI_VERIFY_VIRUS_INFECTED_FILE_NUM'].': </span><span style="text-align: left;'.$color.'">'.$d['infected_file_num'].'</span></p>'.
                        '</div>'.
                        '</li>';
            $vmtable .= $info;
            if(!empty($d['screen_src'])){
                if($sendFlag){
                    //发送邮件使用内嵌附件图片展示
                    $imgSrc .= '<img width="100%" style="margin-bottom:20px;" src="cid:'.$d['report_id'].'" >';
                }else {
                    $filepath = $d['screen_src'];                    
                    $img = file_get_contents($filepath);
                    //获取图片信息
                    $imgbase64 = base64_encode($img);
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo,$filepath);
                    finfo_close($finfo);
                    //输出base64图片
                    if (!empty(trim($imgbase64))) {
                         $imgSrc .= '<img width="100%" style="margin-bottom:20px;" src="data:' .$mimeType . ';base64,' . $imgbase64 . '" alt="">';
                    }
                }
            }
        }
        $imgContent = '<span class="screenshot-title">'.Xphp::$_lang['WEB_PLATFORM_GMP_JOB_SCREENS_VERIFY'].'</span>'.
            '<hr style="display: inline-block;width: 851px;height: 1px;background: #EFF2F5;border: none;margin-bottom: 5px">'.
            '<div style="margin-top: 10px">'.$imgSrc.'</div>';
        $content = str_replace('objectContent', $vmtable, $content);
        //截屏信息显示
        $content = str_replace('imgContent', $imgContent, $content);

        $content = str_replace('backupServerHost', Xphp::instance('SystemHandler')->getMasterNodeIpLink(), $content);
        $content = str_replace('supportEmailHref', 'mailto: '.Xphp::$_config['SYSTEM_INFO']['company_email'], $content);
        $content = str_replace('supportEmail', Xphp::$_config['SYSTEM_INFO']['company_email'], $content);

        $info = array(
            'report' => $content
        );
        return json_encode($info);
    }


    /**
     * 发送数据验证报告到邮箱
     * @param unknown $params
     * @return string
     */
    public function sendVerifyEmail($params){
        $historyuuid = $params['history_uuid'];
        $params = array(
            'history_uuid' => '',
            'taskuuid' => "",
            'id' => $historyuuid,
            'send_flag' => true
        );
        $report = $this->getVerifyJobReport($params);
        $content = json_decode($report, true)['report'];
        $sql = "select email from bd_user where user_uuid = ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $email = $data[0]['email'];
        //未配置邮箱返回失败提醒
        if(empty($email)){
            exit($this->muOpResult(false, Xphp::$_lang['UI_VERIFY_REPORT_SEND_TO_EMAIL_DETAIL'], Xphp::$_lang['UI_VERIFY_REPORT_SEND_TO_EMAIL_DETAIL_TIPS'], "warning"));
        }
        //获取报告附件图片
        $sql = "select ssbr.report_id, ssbr.screen_shot_path
                  from sr_surebackup_report ssbr, bd_history_task bht where ssbr.history_uuid = bht.history_uuid and bht.id = ?";
        $data = $this->dbSelect($sql, array($historyuuid));
        $attachment = array();
        $innerContent = array();
        //添加附件
        foreach ($data as $d) {
            $innerContent[] = array(
                'img_src' => $d['screen_shot_path'],
                'id' =>$d['report_id']
            );
            $attachment[] = $d['screen_shot_path'];
        }
        //发送报告
        $title = Xphp::$_lang['UI_VERIFY_REPORT'];
        $emailConf = Xphp::$_config['EMAIL'];
        //判断此次传入邮箱信息是否和之前一样
        //从数据库获取邮件信息
        $sql="select smtp_config, receive_email from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql);
        $setEmail = json_decode($data[0]['receive_email'], TRUE);
        $resultEmail = array_unique(array_merge(array($email), $setEmail));
        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        $utils = Xphp::instance('Utils');
        $pass = $utils->decrypt($smtpConfig['pass']);
        $encryption = intval($smtpConfig['encryption']);
        $encryption = Xphp::$_config['EMAIL_ENCRYPTION_TYPE'][$encryption];
        //直接调用发送邮件接口
        $emailUtils = Xphp::instance('Email');
        $emailConfig = Xphp::$_config['EMAIL'];
        $emailUtils->config($smtpConfig['host'], $smtpConfig['port'], $emailConfig['authentication'],
            $smtpConfig['email'], $pass, $encryption);
        $result = $emailUtils->sendmail($resultEmail, $title, $content, $attachment, $innerContent);
        $operate = Xphp::$_lang['UI_VERIFY_REPORT_SEND_TO_EMAIL_DETAIL'].": " . $email;
        if($result){
            return $this->muOpResult(true, $operate);
        }else {
            return $this->muOpResult(false, $operate);
        }

    }

    /**
     * 根据生产网络获取隔离网络信息
     * @param unknown $params
     */
    public function getIsolatedAutoNetwork($params){
        $list = $params['list'];
        //代理网关信息
        $proxyInfo = $this->groupProxyNetworkInfo($params['proxy_info']);

        $submodule_type = $params['hypervisor'];
        $info = array();
        foreach ($list as $d){
            $info[] = array(
                "network_name" => $d['name'],
                "network_uuid" => $d['uuid'],
                "ip_netmask" => $d['netmask'],
                "ip_gateway" => $d['gateway'],
                "ip_segment" => "",
                "dns_server" => "",
                "mac_address" => "",
                "ip_address" => ""
            );
        }


        //根据生产网络获取隔离网络信息
        $opName = 'VM_VCENTER_OP_AUTOMATIC_GET_ISOLATED_NETWORK';
        $msg = json_encode(array('product_network_list' => $info, 'proxy_product_network_Info' => $proxyInfo));
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, true);
        $msg = $mbResult['msg'];
        $list = $msg['isolate_network_list'];
        foreach ($list as $k => &$v) {
            $v['network_name'] = $info[$k]['network_name'];
            $v['network_uuid'] = $info[$k]['network_uuid'];
        }
        return json_encode($list);
    }

    /**
     * 得到虚拟化中心所有网络列表(虚拟化中心)
     * @param unknown $params
     */
    public function getVcenterNetworkList($params){
        $hypervisor = $params['hypervisor'];
        $vcenteruuid = $params['vcenteruuid'];
        $labuuid = $params['labuuid'];    //修改虚拟实验室唯一标识
        $this->paramsCheck($hypervisor, $vcenteruuid);
        $submodule_type = $hypervisor;

        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $network = array();
        $network[] = array(
            'uuid' => '0',
            'text' => Xphp::$_lang['WEB_AUTO_SELECT'],
        );

        if (!empty($labuuid)) {
            // 表示编辑 判断下是否是内嵌那么需要再 bd_emd 根据 node_uuid获取 emd_uuid
            $sql = "select be.emd_uuid from bd_emd be,sr_virtual_lab svl where be.node_uuid = svl.node_uuid and svl.virtual_lab_id = ? ";
            $emdUuid = $this->dbSelect($sql, [$labuuid]);
            if (!empty($emdUuid)) {
                $vcenteruuid = $emdUuid[0]['emd_uuid'];
            }
        }

        //获取网络信息
        $opName = 'VM_VCENTER_OP_QUERY_VCENTER_NETWORK_LIST';
        $msg = json_encode(array('vcenter_uuid'=>$vcenteruuid, 'host_uuid'=>""));
        $mbResult = $this->mbVMMsg($nodeuuid, $submodule_type, $opName, $msg, true);
        if($mbResult['result']){
            $networkList = $mbResult['msg']['network_list'];
            //获取当前已用生产网络列表
            $usedNetworkList = $this->getUsedNetworkList($labuuid, $vcenteruuid);
            foreach ($networkList as $list){
                if(in_array($list['network_uuid'], $usedNetworkList)) continue;
                $network[] = array(
                    'uuid' => $list['network_uuid'],
                    'text' => $this->getHostNetworkNameText($list)
                );
            }
        }
        $info = array(
            'network' => $network,
        );
        return json_encode($info);
    }

    /**
     * 得到宿主机网卡的名字显示
     * @param unknown $network
     */
    private function getHostNetworkNameText($network){
        $text = $network['network_name'];
        if(!empty($network['ip_address'])){
            $text .= "(" . $network['ip_address'] . ")";
        }
        return $text;
    }

    /**
     * 检查虚拟实验室是否在任务中，部署中的虚拟实验室不能删除
     * @param string $labuuid
     * @param string $operate
     */
    private function checkLabTaskExist($labuuid, $operate){
        $sql = "select ssb.task_uuid, svl.proxy_status from sr_virtual_lab svl left join sr_sure_backup ssb on ssb.virtual_lab_uuid = svl.virtual_lab_uuid where svl.virtual_lab_uuid = ?";
        $data = $this->dbSelect($sql,array($labuuid));
        //状态部署中/修改中不能删除
        if(intval($data[0]['proxy_status']) == Xphp::$_config['VIRTUAL_LAB_STATUS']['DEPLOYMENT'] || intval($data[0]['proxy_status']) == Xphp::$_config['VIRTUAL_LAB_STATUS']['MODIFY']){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_VIRTUAL_LAB_DELETE_DEPLOY_TIPS'], "warning"));
        }

        //虚拟实验室存在任务中，无法删除
        if(!empty($data[0]['task_uuid'])){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_VIRTUAL_LAB_TASK_EXIST_TIPS'], "warning"));
        }
    }

    /**
     * 获取已用生产网络列表
     * @param string $labuuid
     * @param string $vcenteruuid
     * @return unknown[]
     */
    private function getUsedNetworkList($labuuid, $vcenteruuid){
        // 又要放开， 不限制 Bug #16612
        return [];
        $sql = "select snml.product_network_uuid from sr_network_map_list snml, sr_virtual_lab svl where snml.virtual_lab_uuid = svl.virtual_lab_uuid ";
        $sqlParams = array();
        if(!empty($labuuid)){
            $sql .= " and svl.virtual_lab_uuid != ? ";
            $sqlParams = array_merge($sqlParams, array($labuuid));
        }
        if(!empty($vcenteruuid)){
            $sql .= " and svl.vcenter_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($vcenteruuid));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $list = array();
        foreach ($data as $d){
            $list[] = $d['product_network_uuid'];
        }

        return $list;
    }

    /****************************************************************************
     * 下面代码是短信和邮件通知接口
     ****************************************************************************/
    /**
     * 发送验证报告邮件接口(提供给后台调用)
     * @param array $params
     *  history_uuid:历史任务history_uuid
     *  uuid:历史任务uuid
     */
    public function sendVerifyNotice($params){
        $id = $params['history_uuid'];
        $uuid = $params['uuid'];
        $alarmID = $params['id'];
        $alarmType = $params['type'];
        $reportFlag = $params['email_report'];
        $this->paramsCheck($id);
        //获取当前任务对应用户
        $sql = "select bht.id, bu.email from bd_user bu, bd_task_alarm bta, bd_history_task bht where bu.user_uuid = bta.user_uuid and bta.history_uuid = bht.history_uuid and bta.history_uuid = ? ";
        $data = $this->dbSelect($sql, array($id));
        $params = array(
            'history_uuid' => intval($data[0]['id']),
            'taskuuid' => $uuid,
            'send_flag' => true
        );
        //验证报告邮件通知
        $alarmHandler = Xphp::instance('AlarmHandler');
        $sentFlag = $alarmHandler->checkEmailNoticeSetting($reportFlag, $alarmType, $alarmID);
        if(!$sentFlag) return;
        $verifyInfo = $this->getVerifyJobReport($params);
        $content = json_decode($verifyInfo, true)['report'];
        $emails = array();
        if(!empty($data)){
            $emails = array($data[0]['email']);
        }
        //获取报告附件图片
        $sql = "select ssbr.item_name, ssbr.report_id, unix_timestamp(ssbr.start_time) start_time, unix_timestamp(ssbr.end_time) end_time, ssbr.screen_shot_path
                  from sr_surebackup_report ssbr, bd_history_task bht where ssbr.history_uuid = bht.history_uuid and bht.history_uuid = ?";
        $data = $this->dbSelect($sql, array($id));
        $attachment = array();
        $innerContent = array();
        //添加附件
        foreach ($data as $d) {
            $innerContent[] = array(
                'img_src' => $d['screen_shot_path'],
                'id' =>$d['report_id']
            );
            $attachment[] = $d['screen_shot_path'];
        }
        //发送报告
        $title = Xphp::$_lang['UI_VERIFY_REPORT'];
        $emailConf = Xphp::$_config['EMAIL'];
        //判断此次传入邮箱信息是否和之前一样
        //从数据库获取邮件信息
        $sql="select smtp_config, receive_email from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql);
        $setEmail = json_decode($data[0]['receive_email'], TRUE);
        $resultEmail = array_unique(array_merge($emails, $setEmail));
        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        $utils = Xphp::instance('Utils');
        $pass = $utils->decrypt($smtpConfig['pass']);
        $encryption = intval($smtpConfig['encryption']);
        $encryption = Xphp::$_config['EMAIL_ENCRYPTION_TYPE'][$encryption];
        //直接调用发送邮件接口
        $emailUtils = Xphp::instance('Email');
        $emailConfig = Xphp::$_config['EMAIL'];
        $emailUtils->config($smtpConfig['host'], $smtpConfig['port'], $emailConfig['authentication'],
            $smtpConfig['email'], $pass, $encryption);
        $emailResult = $emailUtils->sendmail($resultEmail, $title, $content, $attachment, $innerContent);
        return $emailResult;

    }

    /**
     * 检查虚拟实验室名是否已用
     * @param string $params [username]
     * @return string
     */
    public function labnameAvailable($params){
        $labname = $params['labname'];
        $oldname = $params['oldname'];
        //修改检测没有修改名称
        if($labname == $oldname){
            return json_encode(true);
        }
        $tenantuuid = $params['tenantuuid'];
        $sql = "select virtual_lab_uuid from sr_virtual_lab where virtual_lab_name = ? ";
        $sqlParams = array($labname);
        $data = parent::dbSelect($sql, $sqlParams);
        return json_encode(empty($data));
    }

    public function getVerifyClass($status){
        $labelClass = "vm-status-waiting";
		switch(intval($status)){
            case 0:	//未知
                $labelClass = "vm-status-waiting";
                break;
            case 1:	//等待
                $labelClass = "vm-status-waiting";
                break;
            case 2:	//运行
                $labelClass = "vm-status-success";
                break;
            case 3:	//跳过
                $labelClass = "vm-status-waiting";
                break;
            case 4:	//错误
                $labelClass = "vm-status-error";
                break;
            case 5:	//成功
                $labelClass = "vm-status-success";
                break;
            case 6:	//完成
                $labelClass = "vm-status-success";
                break;
            case 7:	//异常
                $labelClass = "vm-status-waiting";
                break;
        }
		return $labelClass;
    }

}
?>