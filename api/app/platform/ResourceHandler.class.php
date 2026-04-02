<?php
/*******************************************
 ** 资源,资源组管理类
 **
 ** @author       xiezhuowei@vinchin.com;luokai@vinchin.com;liushuai@vinchin.com
 ** @date         2020-09-10 下午17:04:00
 ** @version      1.0.0
 ** @copyright    Copyright 2020 vinchin.com
 ********************************************/
class ResourceHandler extends OPHandler{
    /**
     * 获取资源组列表
     * @param unknown $params
     */
    public function getResourceGroupLists($params){
        //TODO
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("", "brg.resource_group_name", "brg.description", "brg.create_time", "brg.create_user_name", "");
        
        //
        $sql = "select brg.create_time, brg.create_user_name, brg.resource_group_uuid, brg.resource_group_name, brg.description, brg.config from bd_resource_group brg left join mt_resource_group_tenant mrgt 
                on brg.resource_group_uuid = mrgt.resource_group_uuid ";
        $sqlCount = "select count(brg.resource_group_uuid) as total from bd_resource_group brg left join mt_resource_group_tenant mrgt 
                on brg.resource_group_uuid = mrgt.resource_group_uuid ";
        
        $sqlParams = array();
        $sqlCountParams = array();
        //检查是否是租户管理员
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();

        //如果是超级管理员展示所有的
        if(
            Xphp::$_user['useruuid'] != "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && Xphp::$_user['usertype'] != Xphp::$_config['USER_TYPES']['USER_ADMIN']
            && !($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['sysadmin'])
        ) {
            // 如果是三权模式并且是系统管理员也显示所有
            $sql .= "where brg.create_user_uuid = ? ";
            $sqlCount .= "where brg.create_user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid']);
            $sqlCountParams = array(Xphp::$_user['useruuid']);
        }
        $sqlParams = array_merge($sqlParams, array($start, $length));
        
        $sql .= "order by  $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $records = array();
        $records['data'] = array();
        if(!empty($data)){
            foreach ($data as $d){
                $createTime = $d['create_time'];
                if($d['create_time'] == "0000-00-00 00:00:00"){
                    $createTime = Xphp::$_config['TIMESPACE'];
                }
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['resource_group_uuid'].'">',
                    $d['resource_group_name'],
                    $d['description'],
                    $createTime,
                    $d['create_user_name'],
                    array(),
                    $d['resource_group_uuid']
                );
            }
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($dataCount[0]['total']);
        $records['recordsFiltered'] = intval($dataCount[0]['total']);
        
        return json_encode($records);
        
    }
    
    /**
     * 添加资源组
     * @param unknown $params
     */
    public function addResourceGroup($params){
        //TODO
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_resource_group_add");
        $resourceGroupName = $params['resourcegroupname'];
        $opName = Xphp::$_lang['UI_RESOURCE_GROUP_ADD']; //添加资源组
        $description = $params['description'];
        $utils = Xphp::instance('Utils');
        $uuid = $utils->uuid(); //生成资源组uuid
        $createTime = date('Y-m-d H:i:s');
        $sqlParams = array($uuid, $resourceGroupName, $description, $_SESSION['tenantuuid'], $createTime, Xphp::$_user['useruuid'], Xphp::$_user['username']);
        $sql = "insert into bd_resource_group (resource_group_uuid, resource_group_name, description, tenant_uuid, create_time, create_user_uuid, create_user_name) values (?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbQuery($sql, $sqlParams);
        
        //添加资源组返回结果
        if($result){
            $userHandler = Xphp::instance('UsersHandler');
            //添加资源组与用户关联
            $userHandler->pAddUserResourceGroup(Xphp::$_user['useruuid'], array($uuid));
            //添加资源组与租户关联
            if(!empty($_SESSION['tenantuuid'])){
                $userHandler->pAddResourceGroupTenant($_SESSION['tenantuuid'], array($uuid));
            }
            //添加系统操作日志
            $this->systemLog('SYSTEM_RESOURCE_GROUP_ADD_SUCCESS', array($resourceGroupName));
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName);
        }
        
    }
    
    /**
     * 修改资源组
     * @param unknown $params
     */
    public function editResourceGroup($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_resource_group_edit");
        //TODO
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $resourceGroupName = $params['resourcegroupname'];
        $opName = Xphp::$_lang['UI_RESOURCE_GROUP_MODIFY']; //修改资源组
        $description = $params['description'];
        $editTime = date('Y-m-d H:i:s');
        $sqlParams = array($resourceGroupName, $description, $editTime, $resourceGroupUUID);
        $sql = "update bd_resource_group set resource_group_name = ?, description = ?, create_time = ? where resource_group_uuid = ?";
        $result = $this->dbQuery($sql, $sqlParams);
        
        //修改资源组返回结果
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName);
        }
        
    }
    
    /**
     * 删除资源组
     * @param unknown $params
     */
    public function deleteResourceGroup($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_resource_group_delete");
        //TODO
        $list = $params['uuids'];
        
        $sqlParams = array($list[0]);
        $sql = "delete from bd_resource_group where resource_group_uuid = ? ";
        $result = $this->dbQuery($sql, $sqlParams);
        
        //删除资源组返回结果到界面
        if($result){
            $userHandler = Xphp::instance('UsersHandler');
            //取消资源组与用户关联
            $this->pDeleteResourceGroupUser($list);
            //取消资源组与用户组关联
            $this->pDeleteResourceGroupUserGroup($list);
            //取消资源组与资源关联
            $this->pDeleteResourceGroupResource($list);
            //取消资源组与租户关联
            if(!empty($_SESSION['tenantuuid'])){
                $userHandler->pDeleteResourceGroupTenant($_SESSION['tenantuuid'], $list);
            }
            
            return $this->muOpResult(true, Xphp::$_lang['UI_RESOURCE_GROUP_DELETE']);
        }else{
            return $this->muOpResult(false, Xphp::$_lang['UI_RESOURCE_GROUP_DELETE']);
        }
        
    }
    /**
     * 得到用户所有资源 获取某个用户的资源详情,文件代理主机  数据库实时主机  虚拟机 Appliance 节点 存储
     * @param unknown $useruuid
     */
    public function getUserAllResource($useruuid){
        //TODO,先获取用户本身的资源,然后获取再用户组的所有资源,求并
        //TODO,再调用$this->groupResourceInfo
    }
    
    /**
     * 获取用户所有文件代理(过滤当前资源组已分配的)
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserAllFileHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];  //资源组UUID
        $userUUID = $params['userUUID'];                    //用户UUID
        $userGroupUUID = $params['userGroupUUID'];          //用户组UUID
        $sourceFrom = $params['sourceFrom'];                      //资源分配来源：资源组|用户|用户组
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $search = $params['search'];
        $sortArr = array("agent_uuid", "hostname", "ip", "os_type", "" );
        
        
        $resourceInfo = $this->pGetUserResourceFileHost(Xphp::$_user['useruuid'], "ba.id", $sortType, $start, $length, $search);
        
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $userHandler = Xphp::instance('UsersHandler');
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                //检查是否拥有该资源
                $haveFlag = false;    
                switch ($sourceFrom){
                    case Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP']:
                        if($resourceGroupUUID){
                            $haveFlag = $this->pGetResourceGroupResourceFlag($resourceGroupUUID, $d['agent_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER']:
                        if($userUUID){
                            $haveFlag = $userHandler->pGetUserResourceFlag($userUUID, $d['agent_uuid'], true);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']:
                        if($userGroupUUID){
                            $haveFlag = $userHandler->pGetUserGroupResourceFlag($userGroupUUID, $d['agent_uuid']);
                        }
                        break;
                        
                }
                $checkbox = '<input type="checkbox" name="id[]" value="'.$d['agent_uuid'].'">';
                if($haveFlag){
                    $checkbox = '';
                }
                $records['data'][] = array(
                    $checkbox,
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    /**
     * 获取用户所有数据库定时主机(过滤当前资源组已分配的)
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserAllDbHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];  //资源组UUID
        $userUUID = $params['userUUID'];                    //用户UUID
        $userGroupUUID = $params['userGroupUUID'];          //用户组UUID
        $sourceFrom = $params['sourceFrom'];                      //资源分配来源：资源组|用户|用户组
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("agent_uuid", "hostname", "ip", "os_type", "" );
        $search = $params['search'];
        
        $resourceInfo = $this->pGetUserResourceDbHost(Xphp::$_user['useruuid'], "id", $sortType, $start, $length, $search);
        
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $userHandler = Xphp::instance('UsersHandler');
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                //检查是否拥有该资源
                $haveFlag = false;
                switch ($sourceFrom){
                    case Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP']:
                        if($resourceGroupUUID){
                            $haveFlag = $this->pGetResourceGroupResourceFlag($resourceGroupUUID, $d['agent_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER']:
                        if($userUUID){
                            $haveFlag = $userHandler->pGetUserResourceFlag($userUUID, $d['agent_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']:
                        if($userGroupUUID){
                            $haveFlag = $userHandler->pGetUserGroupResourceFlag($userGroupUUID, $d['agent_uuid']);
                        }
                        break;
                        
                }
                
                if($haveFlag){
                    $count--;
                    continue;
                }
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['agent_uuid'].'">',
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    /**
     * 得到当前用户拥有的数据库实时主机(过滤当前资源组已分配的)
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserAllCDPHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];  //资源组UUID
        $userUUID = $params['userUUID'];                    //用户UUID
        $userGroupUUID = $params['userGroupUUID'];          //用户组UUID
        $sourceFrom = $params['sourceFrom'];                      //资源分配来源：资源组|用户|用户组
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("host_uuid", "hostname", "ip", "os_version", "host_type", "status");
        $search = $params['search'];
        
        $resourceInfo = $this->pGetUserResourceCDPHost(Xphp::$_user['useruuid'], "id", $sortType, $start, $length, $search);
        
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $userHandler = Xphp::instance('UsersHandler');
            $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
            foreach ($list as $d){
                //检查是否拥有该资源
                $haveFlag = false;
                switch ($sourceFrom){
                    case Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP']:
                        if($resourceGroupUUID){
                            $haveFlag = $this->pGetResourceGroupResourceFlag($resourceGroupUUID, $d['host_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER']:
                        if($userUUID){
                            $haveFlag = $userHandler->pGetUserResourceFlag($userUUID, $d['host_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']:
                        if($userGroupUUID){
                            $haveFlag = $userHandler->pGetUserGroupResourceFlag($userGroupUUID, $d['host_uuid']);
                        }
                        break;
                        
                }
                if($haveFlag){
                    $count--;
                    continue;
                }
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['host_uuid'].'">',
                    $d['host_name'],
                    $d['ip'],
                    $d['os_version'],
                    $dbDes['HOST_TYPE_DES'][$d['host_type']],
                    $dbDes['HOST_STATUS_DES'][$d['status']],
                    intval($d['status'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    
    /**
     * 得到当前用户拥有的虚拟机 (过滤当前资源组已分配的)
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserAllVM($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];  //资源组UUID
        $userUUID = $params['userUUID'];                    //用户UUID
        $userGroupUUID = $params['userGroupUUID'];          //用户组UUID
        $sourceFrom = $params['sourceFrom'];                      //资源分配来源：资源组|用户|用户组
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("uuid", "name", "vcenter_uuid", "dir_path", "power_state" );
        $search = $params['search'];
        
        //获取资源内容
        $resourceInfo = $this->pGetUserResourceVM(Xphp::$_user['useruuid'], "tree_id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $userHandler = Xphp::instance('UsersHandler');
            $vmDes = require APP_PATH . "/vm/VmDescription.php";
            $vcenter = Xphp::instance('Vcenter');
            $lisenceHosts = $vcenter->getAllLisenceHostUUID();
            foreach ($list as $d){
                //检查是否虚拟机已经被分配
                $haveFlag = false;
                switch ($sourceFrom){
                    case Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP']:
                        if($resourceGroupUUID){
                            $haveFlag = $this->pGetResourceGroupResourceFlag($resourceGroupUUID, $d['uuid'], true);
//                             $haveFlag = $this->pGetResourceGroupVmFlag($d['uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER']:
                        if($userUUID){
                            $haveFlag = $userHandler->pGetUserResourceFlag($userUUID, $d['uuid'], true);
//                             $haveFlag = $userHandler->pGetUserVmFlag($d['uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']:
                        if($userGroupUUID){
                            $haveFlag = $userHandler->pGetUserGroupResourceFlag($userGroupUUID, $d['uuid'], true);
//                             $haveFlag = $userHandler->pGetUserGroupVmFlag($d['uuid']);
                        }
                        break;
                        
                }
                $checkBox = '<input type="checkbox" name="id[]" value="'.$d['uuid'].'">';
                $authFlag = $vcenter->getVMHostLisenced($lisenceHosts, $d['vcenter_uuid'], $d['host_uuid'], false, false);
                //虚拟机名
                $name = $d['name'];
                if($haveFlag || !$authFlag){
                    $checkBox = "";
                }
                //未授权
                if(!$authFlag){
                    $name = "(" . Xphp::$_lang['WEB_SYSTEM_LISENCE_UNAUTHORIZED'] . ")".$d['name'];
                }
                //获取虚拟化中心信息
                $vcenterInfo = $vcenter->pGetVcenterIPByUUID($d['vcenter_uuid']);
                $records['data'][] = array(
                    $checkBox,
                    $name,
                    $vcenterInfo['vcenter_ip'],
                    $d['dir_path'],
                    $vmDes['VmMachineStatus'][intval($d['power_state'])],
                    $d['uuid'],
                    $d['vcenter_uuid'],
                    intval($d['power_state']),
                    $vcenterInfo['hypervisor']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    /**
     * 得到当前用户拥有的虚拟机备份代理 (过滤当前资源组已分配的)
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserAllAppliance($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];  //资源组UUID
        $userUUID = $params['userUUID'];                    //用户UUID
        $userGroupUUID = $params['userGroupUUID'];          //用户组UUID
        $sourceFrom = $params['sourceFrom'];                      //资源分配来源：资源组|用户|用户组
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("appliance_uuid", "nickname", "ip", "port", "online_flag" );
        $search = $params['search'];
        
        //获取资源内容
        $resourceInfo = $this->pGetUserResourceAppliance(Xphp::$_user['useruuid'], "id", $sortType, $start, $length, $search);
        
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $userHandler = Xphp::instance('UsersHandler');
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                //检查是否拥有该资源
                $haveFlag = false;
                switch ($sourceFrom){
                    case Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP']:
                        if($resourceGroupUUID){
                            $haveFlag = $this->pGetResourceGroupResourceFlag($resourceGroupUUID, $d['appliance_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER']:
                        if($userUUID){
                            $haveFlag = $userHandler->pGetUserResourceFlag($userUUID, $d['appliance_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']:
                        if($userGroupUUID){
                            $haveFlag = $userHandler->pGetUserGroupResourceFlag($userGroupUUID, $d['appliance_uuid']);
                        }
                        break;
                        
                }
                
                if($haveFlag){
                    $count--;
                    continue;
                }
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['appliance_uuid'].'">',
                    $d['nickname'],
                    $d['ip'],
                    $d['port'],
                    $nodeHandler->getApplianceStatus(intval($d['online_flag'])),
                    intval($d['online_flag'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    /**
     * 得到当前用户拥有的节点 (过滤当前资源组已分配的)
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserAllNode($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];  //资源组UUID
        $userUUID = $params['userUUID'];                    //用户UUID
        $userGroupUUID = $params['userGroupUUID'];          //用户组UUID
        $sourceFrom = $params['sourceFrom'];                      //资源分配来源：资源组|用户|用户组
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("node_uuid", "host_name", "ip", "auto_upgrade_flag", "" );
        $search = $params['search'];
        
        ///获取资源内容
        $resourceInfo = $this->pGetUserResourceNode(Xphp::$_user['useruuid'], "node_id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $userHandler = Xphp::instance('UsersHandler');
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                //检查是否拥有该资源
                $haveFlag = false;
                switch ($sourceFrom){
                    case Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP']:
                        if($resourceGroupUUID){
                            $haveFlag = $this->pGetResourceGroupResourceFlag($resourceGroupUUID, $d['node_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER']:
                        if($userUUID){
                            $haveFlag = $userHandler->pGetUserResourceFlag($userUUID, $d['node_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']:
                        if($userGroupUUID){
                            $haveFlag = $userHandler->pGetUserGroupResourceFlag($userGroupUUID, $d['node_uuid']);
                        }
                        break;
                        
                }
                if($haveFlag){
                    $count--;
                    continue;
                }
                $nodeDeployStatus = $nodeHandler->getNodeDeployStatus($d['node_uuid']);
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['node_uuid'].'">',
                    $d['host_name'],
                    $d['ip'],
                    $nodeDeployStatus,
                    $nodeAllStatus['flag']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    /**
     * 得到当前用户拥有的存储 (过滤当前资源组已分配的)
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserAllStorage($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];  //资源组UUID
        $userUUID = $params['userUUID'];                    //用户UUID
        $userGroupUUID = $params['userGroupUUID'];          //用户组UUID
        $sourceFrom = $params['sourceFrom'];                      //资源分配来源：资源组|用户|用户组
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("storage_uuid", "storage_nickname", "storage_type", "node_uuid", "total_size", "free_size", "status", "use_mode");
        $search = $params['search'];
        
        ///获取资源内容
        $resourceInfo = $this->pGetUserResourceStorage(Xphp::$_user['useruuid'], "storage_id", $sortType, $start, $length, $search);
        
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $userHandler = Xphp::instance('UsersHandler');
            $utils = Xphp::instance('Utils');
            $nodeHandler = Xphp::instance('NodeHandler');
            $storageHandler = Xphp::instance('StorageHandler');
            foreach ($list as $d){
                //检查是否拥有该资源
                $haveFlag = false;
                switch ($sourceFrom){
                    case Xphp::$_config['RESOURCE_FROM']['RESOURCE_GROUP']:
                        if($resourceGroupUUID){
                            $haveFlag = $this->pGetResourceGroupResourceFlag($resourceGroupUUID, $d['storage_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER']:
                        if($userUUID){
                            $haveFlag = $userHandler->pGetUserResourceFlag($userUUID, $d['storage_uuid']);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']:
                        if($userGroupUUID){
                            $haveFlag = $userHandler->pGetUserGroupResourceFlag($userGroupUUID, $d['storage_uuid']);
                        }
                        break;
                        
                }
                
                if($haveFlag){
                    $count--;
                    continue;
                }
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $nodeDes =  $nodeHandler->pGetStorageNodeDes($d['node_uuid']);
                $nodeStatus = $nodeAllStatus['flag'];
                
                //异地备份系统节点显示
                if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
                    $storageConfig = json_decode($d['storage_config'], true);
                    $nodeDes = $storageConfig['remote_ip'];
                    if(!empty($storageConfig['remote_name'])){
                        $nodeDes = $storageConfig['remote_name'].'('.$storageConfig['remote_ip'].')';
                    }
                    $nodeStatus = $utils->parseFlagToBool(intval($storageConfig['remote_status']));
                }
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['storage_uuid'].'">',
                    $d['storage_nickname'],
                    $storageHandler->getStorageTypeDes($d['storage_type']),
                    $nodeDes,
                    $utils->calSize($d['total_size']),
                    $utils->calSize($d['free_size']),
                    $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                    $storageHandler->getUsemodeDes(intval($d['use_mode'])),
                    $storageHandler->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag']))
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }




    /**
     * 公共方法
     * 得到用户所有资源(只有资源uuid和类型)
     * 用户-资源
     * 用户-资源组-资源
     * 用户-用户组-资源
     * 用户-用户组-资源组-资源
     * @param string $useruuid  用户uuid
     * @param int $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @author xiezhuowei@vinchin.com
     * @return array  资源UUID和资源类型列表
     */
    public function pGetUserAllResource($useruuid, $resourceType=""){
        $this->paramsCheck($useruuid);
        //如果按类型查找
        $sqlMore = "";
        $sqlParams = array($useruuid);
        if(!empty($resourceType)){
            $sqlMore = " and resource_type = ? ";
            $sqlParams = array($useruuid, $resourceType);
        }
        //用户-资源
        $sql = "select distinct mur.resource_uuid, mur.vm_uuid, mur.vcenter_uuid, mur.resource_type from 
                bd_user bu, mt_user_resource mur 
                where bu.user_uuid = mur.user_uuid and bu.user_uuid = ? ";
        $sql .= $sqlMore;
        $data1 = $this->dbSelect($sql, $sqlParams);

        //用户-资源组-资源
        $sql = "select distinct mrrg.resource_uuid, mrrg.vm_uuid, mrrg.vcenter_uuid, mrrg.resource_type from
                bd_user bu, mt_user_resource_group murg, mt_resource_resource_group mrrg 
                where bu.user_uuid = murg.user_uuid and murg.resource_group_uuid = mrrg.resource_group_uuid 
                and bu.user_uuid = ? ";
        $sql .= $sqlMore;
        $data2 = $this->dbSelect($sql, $sqlParams);

        //用户-用户组-资源
        $sql = "select distinct mugr.resource_uuid, mugr.vm_uuid, mugr.vcenter_uuid, mugr.resource_type from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_resource mugr
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugr.user_group_uuid and bug.user_group_uuid = mugr.user_group_uuid and bu.user_uuid = ? and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $data3 = $this->dbSelect($sql, $sqlParams);

        //用户-用户组-资源组-资源
        $sql = "select distinct mrrg.resource_uuid, mrrg.vm_uuid, mrrg.vcenter_uuid, mrrg.resource_type from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_resource_group mugrg, mt_resource_resource_group mrrg 
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugrg.user_group_uuid and bug.user_group_uuid = mugrg.user_group_uuid and
                mugrg.resource_group_uuid = mrrg.resource_group_uuid and bu.user_uuid = ? and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $data4 = $this->dbSelect($sql, $sqlParams);

        //合并四个资源,并去重
        $data = array_merge($data1, $data2, $data3, $data4);
        // 根据 $useruuid 判断当前的用户是不是租户
        //$systemHandler = Xphp::instance("SystemHandler");
        //$tenant = $systemHandler->getUserPermission();
        //if (!empty($tenant)) {
        // 是租户的话，需要把自己创建的这些资源都给包含进来
        $dataNew = [];
        if (!empty($resourceType)) {
            // 查询某一项的
            $datas = $this->pGetUserCreateResource($useruuid, $resourceType, 'array', true);
            foreach ($datas as $items) {
                $dataNew[] = [
                    'resource_uuid' => $items['uuid'],
                    'vm_uuid' => in_array($resourceType, [3, 58, 61]) ? $items['uuid'] : '',
                    'vcenter_uuid' => '',
                    'resource_type' => $resourceType,
                ];
            }
        } else {
            $resourceTypes = Xphp::$_config['RESOURCE_DIS_TYPE'];
            foreach ($resourceTypes as $key => $item) {
                $datas = $this->pGetUserCreateResource($useruuid, $key, 'array', true);
                foreach ($datas as $items) {
                    $dataNew[] = [
                        'resource_uuid' => $items['uuid'],
                        'vm_uuid' => in_array($key, [3, 58, 61]) ? $items['uuid'] : '',
                        'vcenter_uuid' => '',
                        'resource_type' => $key,
                    ];
                }
            }
        }
        $data = array_merge($data, $dataNew);
        //}
        $utils = Xphp::instance('Utils');
        $data = $utils->unique_multidim_array($data, "resource_uuid");
        return $data;
    }

    /**
     * 公共方法
     * 得到用户组所有资源(只有资源uuid和类型)
     * 用户-用户组-资源
     * 用户-用户组-资源组-资源
     * @param string $userGroupUUID  用户组uuid
     * @param int $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @author luokai@vinchin.com
     * @return array  资源UUID和资源类型列表
     */
    public function pGetUserGroupAllResource($userGroupUUID, $resourceType=""){
        $this->paramsCheck($userGroupUUID);
        //如果按类型查找
        $sqlMore = "";
        $sqlParams = array($userGroupUUID);
        if(!empty($resourceType)){
            $sqlMore = " and resource_type = ? ";
            $sqlParams = array($userGroupUUID, $resourceType);
        }
        //用户组-资源
        $sql = "select distinct mugr.resource_uuid, mugr.vm_uuid, mugr.vcenter_uuid, mugr.resource_type from
                bd_user_group bug, mt_user_group_resource mugr
                where bug.user_group_uuid = mugr.user_group_uuid and mugr.user_group_uuid = ? ";
        $sql .= $sqlMore;
        $data1 = $this->dbSelect($sql, $sqlParams);

        //用户组-资源组-资源
        $sql = "select distinct mrrg.resource_uuid, mrrg.vm_uuid, mrrg.vcenter_uuid, mrrg.resource_type from
                bd_user_group bug, mt_user_group_resource_group mugrg, mt_resource_resource_group mrrg
                where bug.user_group_uuid = mugrg.user_group_uuid and 
                mugrg.resource_group_uuid = mrrg.resource_group_uuid and bug.user_group_uuid = ? ";
        $sql .= $sqlMore;
        $data2 = $this->dbSelect($sql, $sqlParams);

        //合并四个资源,并去重
        $data = array_merge($data1, $data2);
        $utils = Xphp::instance('Utils');
        $data = $utils->unique_multidim_array($data, "resource_uuid");
        return $data;
    }
    
    /**
     * 公共方法
     * 得到租户所有资源(只有资源uuid和类型)
     * @param string $tenantuuid  租户uuid
     * @param int $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @author xiezhuowei@vinchin.com
     * @return array  资源UUID和资源类型列表
     */
    public function pGetTenantAllResource($tenantuuid, $resourceType=""){
        $this->paramsCheck($tenantuuid);
        //获取租户下所有用户
        $tenantHandler = Xphp::instance('TenantHandler');
        $users = $tenantHandler->pGetTenantAllUser($tenantuuid);
        
        //获取所有用户的资源列表
        $resourceList = array();
        foreach ($users as $user){
            $resourceUUIDList = $this->pGetUserAllResource($user['user_uuid'], $resourceType);
            $resourceList = array_merge($resourceList, $resourceUUIDList);
        }
        $utils = Xphp::instance('Utils');
        $resourceUUIDList = $utils->unique_multidim_array($resourceList, "resource_uuid");
        
        return $resourceUUIDList;
    }
    
    /**
     * 通过资源uuid列表获取文件代理主机
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array $search     搜索参数
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceFileHost($resourceUUIDList, $orderby, $sort, $limit, $count, $search = array(), $flag = false){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(ba.id) as total from bd_agent ba left join bd_user bu on ba.user_uuid = bu.user_uuid where ba.agent_type = 0 ";
        $sql = "select ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_type, ba.os_version, ba.process_type, ba.os_version, ba.process_type,
        ba.register_time, ba.register_flag, ba.online_flag, ba.user_uuid, ba.authorization_module from bd_agent ba left join bd_user bu on ba.user_uuid = bu.user_uuid
        where ba.agent_type = 0 ";
        
        $sqlParams = array();
        $sqlCountParams = array();
        $utils = Xphp::instance('Utils');
        
        //不是Master用户
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " and ba.agent_uuid in ($listStr) ";
                $sqlCount .= " and ba.agent_uuid in ($listStr) ";
            }else{
                $sql .= " and ba.agent_uuid in ('') ";
                $sqlCount .= " and ba.agent_uuid in ('') ";
            }
        }
        
        $accurateFlag = $search['accurateFlag'];
        //高级搜索
        if($accurateFlag){
            $hostname = $search['hostName'];
            $hostname = $utils->escapeWildcard($hostname);
            $ip = $search['agentIp'];
            $onlineFlag = $search['onlineFlag'];
            $registerFlag = $search['registerFlag'];
            $username = $search['userName'];
            $sql .= " and (ba.online_flag = ". $onlineFlag ." or ". $onlineFlag . " = '') and
                 (ba.register_flag = ". $registerFlag ." or ". $registerFlag . " = '') and
                 hostname like '%". $hostname ."%' ";
            $sqlCount .= " and (ba.online_flag = ". $onlineFlag ." or ". $onlineFlag . " = '') and
                 (ba.register_flag = ". $registerFlag ." or ". $registerFlag . " = '') and
                 hostname like '%". $hostname ."%'";
            
            if(!empty($ip)){
                $sql .= " and ba.ip = ? ";
                $sqlCount .= "and ba.ip = ? ";
                $sqlParams = array_merge($sqlParams, array($ip));
                $sqlCountParams = array_merge($sqlCountParams, array($ip));
            }
            
            if(!empty($username)){
                $sql .= " and bu.user_name = ? ";
                $sqlCount .= " and bu.user_name = ?";
                $sqlParams = array_merge($sqlParams, array($username));
                $sqlCountParams = array_merge($sqlCountParams, array($username));
            }
        }else{
            $sql .= " and (ba.online_flag = ?
                or ba.register_flag = ?) ";
            $sqlCount .= " and (ba.online_flag = ? or ba.register_flag = ?) ";
            //根据主机名搜索
            $hostName = $search['name'];
            $hostName = $utils->escapeWildcard($hostName);
            if(!empty($hostName)){
                $sql .= " and ba.hostname like '%". $hostName ."%' ";
                $sqlCount .= " and ba.hostname like '%". $hostName ."%' ";
            }
            $setFlag = Xphp::$_config['FLAG']['SET'];
            $sqlParams = array_merge($sqlParams, array($setFlag, $setFlag));
            $sqlCountParams = array_merge($sqlCountParams, array($setFlag, $setFlag));
        }
        
        $sql .= " order by $orderby $sort ";
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array_merge($sqlParams, array($limit, $count));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        //如果资源为空
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        return $info;
    }
    
    /**
     * 公共方法
     * 得到用户文件代理主机
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceFileHost($useruuid, $orderby = "ba.id", $sort = "desc", $limit = 0, $count = 10, $search = []){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['FILE_HOST']);
        
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);
        
        //获取资源内容
        $resource = $this->getResourceFileHost($resourceUUIDList, $orderby, $sort, $limit, $count, $search, $masterFlag);
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户组文件代理主机
     * @param string $userUUID 当前用户唯一标识
     * @param string $userGroupUUID  用户组uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceFileHost($userUUID = "", $userGroupUUID = '', $orderby = "ba.id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['FILE_HOST']);
        
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($userUUID);
        
        //获取资源内容
        $resource = $this->getResourceFileHost($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户文件代理主机
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceFileHost($tenantuuid, $orderby = "ba.id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['FILE_HOST']);
        
        //获取资源内容
        $resource = $this->getResourceFileHost($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    
    /**
     * 通过资源uuid列表获取数据库定时
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param number $search    搜索参数集合
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceDbHost($resourceUUIDList, $orderby, $sort, $limit, $count, $search = array(), $flag = false){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(id) as total from bd_agent where agent_type = 1 ";
        
        $sql = "select agent_uuid, agent_name, hostname, ip, os_type, os_version, process_type, os_version, process_type,
        register_time, register_flag, online_flag, user_uuid, authorization_module from bd_agent
        where agent_type = 1 ";
        $utils = Xphp::instance('Utils');
        
        //不是Master用户
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " and agent_uuid in ($listStr)";
                $sqlCount .= " and agent_uuid in ($listStr)";
            }else{
                $sql .= " and agent_uuid in ('')";
                $sqlCount .= " and agent_uuid in ('')";
            }
        }
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $name = $utils->escapeWildcard($name);
            $sql .= " and (hostname like '%". $name ."%' or ip like'". $name ."')";
            $sqlCount .= " and (hostname like '%". $name ."%' or ip like'". $name ."')";
        }
        
        $sql .= " order by $orderby $sort ";
        
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array($limit, $count);
        }
        
        
        $sqlCountParams = array();
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $data = $this->dbSelect($sql, $sqlParams);
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        return $info;
    }
    
    /**
     * 公共方法
     * 得到用户数据库定时主机
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceDbHost($useruuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10, $search = ''){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['DB_HOST']);
        
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);
        //获取资源内容
        $resource = $this->getResourceDbHost($resourceUUIDList, $orderby, $sort, $limit, $count, $search, $masterFlag);
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户组数据库定时主机
     * @param string $userUUID 当前用户唯一标识
     * @param string $userGroupUUID  用户组uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceDbHost($userUUID = "", $userGroupUUID = '', $orderby = "id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['DB_HOST']);
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($userUUID);
        //获取资源内容
        $resource = $this->getResourceDbHost($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户数据库定时主机
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceDbHost($tenantuuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['DB_HOST']);
        
        //获取资源内容
        $resource = $this->getResourceDbHost($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 通过资源uuid列表获取实时主机
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceCDPHost($resourceUUIDList, $orderby, $sort, $limit, $count, $search = array(), $flag = false){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        
        $sqlCount = "select count(id) as total from cdp_db_host ";
        
        $sql = "select host_uuid, host_name, ip, os_version, host_type, status, register_time, user_uuid, detail from cdp_db_host ";
        $utils = Xphp::instance('Utils');
        
        //不是Master用户
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " where host_uuid in ($listStr)";
                $sqlCount .= " where host_uuid in ($listStr) ";
            }else{
                $sql .= " where host_uuid in ('') ";
                $sqlCount .= " where host_uuid in ('') ";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $name = $utils->escapeWildcard($name);
            if($flag){
                $sql .= " where ";
                $sqlCount .= " where ";
            }else{
                $sql .= " and ";
                $sqlCount .= " and ";
            }
            $sql .= " (host_name like '%". $name ."%' or ip like'". $name ."')";
            $sqlCount .= " (hostname like '%". $name ."%' or ip like'". $name ."')";
        }
        
        $sql .= " order by $orderby $sort ";        
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array($limit, $count);
        }
        $sqlCountParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        
        return $info;
    }
    
    /**
     * 公共方法
     * 得到用户实时主机(文件实时和数据库实时)
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceCDPHost($useruuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10, $search = []){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['CDP_HOST']);
        
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);
        
        //获取资源内容
        $resource = $this->getResourceCDPHost($resourceUUIDList, $orderby, $sort, $limit, $count, $search, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户组实时主机(文件实时和数据库实时)
     * @param string $userUUID 当前用户唯一标识
     * @param string $userGroupUUID  用户组uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceCDPHost($userUUID = "", $userGroupUUID = '', $orderby = "id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['CDP_HOST']);
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($userUUID);
        
        //获取资源内容
        $resource = $this->getResourceCDPHost($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户实时主机(文件实时和数据库实时)
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceCDPHost($tenantuuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['CDP_HOST']);
        
        //获取资源内容
        $resource = $this->getResourceCDPHost($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 通过资源uuid列表获取虚拟机
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * @param array $search     虚拟机资源搜索
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceVM($resourceUUIDList, $orderby, $sort, $limit, $count, $flag = false, $search = array()){
        //组合资源uuid方便查询
        $vmuuidStr = $this->groupResourceUUIDToString($resourceUUIDList, "vm_uuid");
        $vcenterStr = $this->groupResourceUUIDToString($resourceUUIDList, "vcenter_uuid");
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $utils = Xphp::instance('Utils');
        
        $sqlCount = "select count(vt.tree_id) as total from vm_tree vt left join vm_vcenter vv
        on vt.vcenter_uuid = vv.vcenter_uuid where vt.display_mode = ? and vt.type = ? ";
        $sql = "select vt.vcenter_uuid, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.version, vt.conn_state, vt.power_state, vt.host_uuid, vt.detail from vm_tree vt left join vm_vcenter vv
        on vt.vcenter_uuid = vv.vcenter_uuid where vt.display_mode = ? and vt.type = ? ";
        $sqlParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        $sqlCountParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        if(!$flag){
            if(!empty($vmuuidStr) && !empty($vcenterStr)){
                $sql .= "and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
                $sqlCount .= "and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
            }else{
                $sql .= "and vt.uuid in ('') and vt.vcenter_uuid in ('')";
                $sqlCount .= "and vt.uuid in ('') and vt.vcenter_uuid in ('')";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $name = $utils->escapeWildcard($name);
            $hypervisor = $search['hypervisor'];
            if($this->checkEmpty($name)){
                
                $sql .= " and (vt.name like ? or vt.dir_path like ? )";
                $sqlCount .= " and (vt.name like ? or vt.dir_path like ? )";
                $sqlParams = array_merge($sqlParams, array('%'.$name.'%', '%'.$name.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$name.'%', '%'.$name.'%'));
            }
            
            if(!empty($hypervisor)){
                $sql .= " and vv.hypervisor_type = ? ";
                $sqlCount .= " and vv.hypervisor_type = ? ";
                $sqlParams = array_merge($sqlParams,array($hypervisor));
                $sqlCountParams = array_merge($sqlCountParams,array($hypervisor));
            }
        }
        
        $sql .= " order by $orderby $sort";
        
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array_merge($sqlParams,array($limit, $count));
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        return $info;
    }

    /**
     * 公共方法
     * 得到用户虚拟机
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  search     虚拟机资源搜索
     * @param bool  $publicCloudFlag    是否公有云获取
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceVM($useruuid, $orderby = "tree_id", $sort = "desc", $limit = 0, $count = 10, $search = array(), $publicCloudFlag = false){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        if ($publicCloudFlag) {
            $resourceUUIDList = $this->pGetUserAllResource($useruuid, 58);
        } else {
            $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['VM']);
        }

        //检查用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);
        //获取资源内容
        $resource = $this->getResourceVM($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag, $search);

        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户组虚拟机
     * @param string $userUUID 当前用户唯一标识
     * @param string $userGroupUUID  用户组uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array $search     查询虚拟机资源
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceVM($userUUID = "", $userGroupUUID = '', $orderby = "tree_id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->paramsCheck($userGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['VM']);
        
        //检查用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($userUUID);
        //获取资源内容
        $resource = $this->getResourceVM($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag, $search);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户虚拟机
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array $search     虚拟机资源搜索
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceVM($tenantuuid, $orderby = "tree_id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->paramsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['VM']);
        
        //获取资源内容
        $resource = $this->getResourceVM($resourceUUIDList, $orderby, $sort, $limit, $count, $search);
        
        return $resource;
    }
    
    /**
     * 通过资源uuid列表获取appliance
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @param boolean $flag     是否是master组
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceAppliance($resourceUUIDList, $orderby, $sort, $limit, $count, $search = array(), $flag = false){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(id) as total from bd_appliance where node_uuid = '' ";
        
        $sql = "select appliance_uuid, node_uuid, user_uuid, register_time, nickname, online_flag, ip, 	port,
        progress_server_listen_port, progress_server_start_port, progress_server_end_port, cdp_client_listen_port,
        cdp_client_log_listen_port, log_server_listen_port, system_info, detail from bd_appliance
        where node_uuid = '' ";
        
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " and appliance_uuid in ($listStr)";
                $sqlCount .= " and appliance_uuid in ($listStr)";
            }else{
                $sql .= " and appliance_uuid in ('')";
                $sqlCount .= " and appliance_uuid in ('')";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $sql .= " and ip like '%". $name ."%'";
            $sqlCount .= " and ip like '%". $name ."%'";
        }
        
        $sql .= " order by $orderby $sort ";
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array($limit, $count);
        }
        $sqlCountParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        
        return $info;
    }

    /**
     * 通过资源uuid列表获取agent
     * @param array $resourceUUIDList 资源列表
     * @param string $orderby 按哪个字段排序,默认使用id字段
     * @param string $sort 排序方式,默认desc,可传入asc
     * @param number $limit 从哪个开始,默认0
     * @param number $count 本次取多少个,默认10, 特别:"all"取所有
     * @param array $search 搜索参数集合
     * @param boolean $flag 是否是master组
     * @return array  资源列表,对应资源表
     */
    private function getResourceAgent($resourceUUIDList, $orderby, $sort, $limit, $count, $search = array(), $flag = false)
    {
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(id) as total from bd_agent where 1=1 ";

        $sql = "select * from bd_agent where 1=1 ";

        if (!$flag) {
            if (!empty($listStr)) {
                $sql .= " and agent_uuid in ($listStr)";
                $sqlCount .= " and agent_uuid in ($listStr)";
            } else {
                $sql .= " and agent_uuid in ('')";
                $sqlCount .= " and agent_uuid in ('')";
            }
        }

        //条件搜索
        if (!empty($search)) {
            $name = $search['name'];
            $sql .= " and ip like '%" . $name . "%'";
            $sqlCount .= " and ip like '%" . $name . "%'";
        }

        $sql .= " order by $orderby $sort ";
        if ($count != "all") {
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array($limit, $count);
        }
        $sqlCountParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if (!empty($data)) {
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }

        return $info;
    }
    
    /**
     * 公共方法
     * 得到用户appliance
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceAppliance($useruuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']);
        
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);
        
        //获取资源内容
        $resource = $this->getResourceAppliance($resourceUUIDList, $orderby, $sort, $limit, $count, $search, $masterFlag);
        
        return $resource;
    }

    /**
     * 公共方法
     * 得到用户agent
     * @param string $useruuid 用户uuid
     * @param string $orderby 按哪个字段排序,默认使用id字段
     * @param string $sort 排序方式,默认desc,可传入asc
     * @param number $limit 从哪个开始,默认0
     * @param number $count 本次取多少个,默认10, 特别:"all"取所有
     * @param array $search 搜索参数
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceAgent($useruuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10, $search = array()): array
    {
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']);

        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);

        //获取资源内容
        return $this->getResourceAgent($resourceUUIDList, $orderby, $sort, $limit, $count, $search, $masterFlag);
    }
    
    /**
    * 公共方法
    * 得到用户组appliance
    * @param string $userUUID  用户uuid
    * @param string $userGroupUUID 用户组uuid
    * @param string $orderby   按哪个字段排序,默认使用id字段
    * @param string $sort      排序方式,默认desc,可传入asc
    * @param number $limit     从哪个开始,默认0
    * @param number $count     本次取多少个,默认10, 特别:"all"取所有
    * @author luokai@vinchin.com
    * @return array  资源列表,对应资源表
    */
    public function pGetUserGroupResourceAppliance($userUUID = "", $userGroupUUID = '', $orderby = "id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']);
        
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($userUUID);
        //获取资源内容
        $resource = $this->getResourceAppliance($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户appliance
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceAppliance($tenantuuid, $orderby = "id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']);
        
        //获取资源内容
        $resource = $this->getResourceAppliance($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 通过资源uuid列表获取节点
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * @param array  $search    搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceNode($resourceUUIDList, $orderby, $sort, $limit, $count, $flag = false, $search = array()){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(node_id) as total from bd_node ";
        $sql = "select auto_upgrade_flag, ip, port, node_uuid, host_name, os_type, os_version, process_type, register_time, node_type
        from bd_node ";
        $utils = Xphp::instance('Utils');
        
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " where node_uuid in ($listStr)";
                $sqlCount .= " where node_uuid in ($listStr) ";
            }else{
                $sql .= " where node_uuid in ('')";
                $sqlCount .= " where node_uuid in ('') ";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $name = $utils->escapeWildcard($name);
            if($flag){
                $sql .= " where ";
                $sqlCount .= " where ";
            }else{
                $sql .= " and ";
                $sqlCount .= " and ";
            }
            $sql .= " (host_name like '%". $name ."%' or ip like'%". $name ."%')";
            $sqlCount .= " (host_name like '%". $name ."%' or ip like'%". $name ."%')";
        }
        
        $sql .= " order by $orderby $sort";
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array($limit, $count);
        }
        $sqlCountParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        
        return $info;
    }
    
    /**
     * 公共方法
     * 得到用户可用节点
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceNode($useruuid, $orderby = "node_id", $sort = "desc", $limit = 0, $count = 10, $search = array()){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['NODE']);
        
        //检查用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);
        
        //获取资源内容
        $resource = $this->getResourceNode($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag, $search);
        
        return $resource;
    }
    
    
    /**
     * 公共方法
     * 得到用户组可用节点
     * @param string $userUUID  用户uuid
     * @param string $userGroupUUID 用户组uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceNode($userUUID = "", $userGroupUUID = '', $orderby = "node_id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['NODE']);
        
        //检查当前用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($userUUID);
        
        //获取资源内容
        $resource = $this->getResourceNode($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户可用节点
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceNode($tenantuuid, $orderby = "node_id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['NODE']);
        
        //获取资源内容
        $resource = $this->getResourceNode($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 通过资源uuid列表获取存储
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * $param array   $search   搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceStorage($resourceUUIDList, $orderby, $sort, $limit, $count, $flag = false, $search = array()){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        //如果资源为空
        $info = array(
            'total' => 0,
            'data' => array()
        );
        $sqlCount = "select count(storage_id) as total from bd_storage_resource where lan_free_flag = ? and use_mode not in (2,3) ";
        
        $sql = "select storage_nickname, storage_uuid, storage_type, node_uuid, total_size, free_size, status, mount_flag, mount_point,
        global_flag, error_code, storage_config, warning_flag, warning_type, warning_value, use_mode, share_access_flag
        from bd_storage_resource
        where lan_free_flag = ? and use_mode not in (2,3) ";
        $utils = Xphp::instance('Utils');
        
        if(!$flag){
            if(!empty($listStr)){
                $sql .= " and storage_uuid in ($listStr)";
                $sqlCount .= "and storage_uuid in ($listStr) ";
            }else{
                $sql .= " and storage_uuid in ('')";
                $sqlCount .= "and storage_uuid in ('') ";
            }
        }
        
        //条件搜索
        if(!empty($search)){
            $name = $search['name'];
            $name = $utils->escapeWildcard($name);
            $sql .= " and storage_nickname like '%". $name ."%'";
            $sqlCount .= " and storage_nickname like '%". $name ."%'";
        }
        
        $sql .= " order by $orderby $sort";
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $limit, $count);
        }
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!empty($data)){
            $info = array(
                'total' => $dataCount[0]['total'],
                'data' => $data
            );
        }
        
        return $info;
    }
    
    /**
     * 公共方法
     * 得到用户可用存储
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param array  $search    搜索参数集合
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceStorage($useruuid, $orderby = "storage_id", $sort = "desc", $limit = 0, $count = 10, $search = []){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
        //检查用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($useruuid);
        
        //获取资源内容
        $resource = $this->getResourceStorage($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag, $search);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到用户组可用存储
     * @param string $userUUID  用户uuid
     * @param string $userGroupUUID 用户组uudi
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author luokai@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserGroupResourceStorage($userUUID = "", $userGroupUUID = '', $orderby = "storage_id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有指定资源uuid
        $resourceUUIDList = $this->pGetUserGroupAllResource($userGroupUUID, Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
        //检查用户是否属于Master组
        $userHandler = Xphp::instance("UsersHandler");
        $masterFlag = $userHandler->pCheckUserIsMaster($userUUID);
        //获取资源内容
        $resource = $this->getResourceStorage($resourceUUIDList, $orderby, $sort, $limit, $count, $masterFlag);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户可用存储
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceStorage($tenantuuid, $orderby = "storage_id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
        
        //获取资源内容
        $resource = $this->getResourceStorage($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 通过资源uuid列表获取策略组
     * @param unknown $resourceUUIDList 资源列表
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    private function getResourceStrategyGroup($resourceUUIDList, $orderby, $sort, $limit, $count){
        //组合资源uuid方便查询
        $listStr = $this->groupResourceUUIDToString($resourceUUIDList);
        $sql = "select count(strategy_group_id) as total from bd_strategy_group where strategy_group_uuid in ($listStr)";
        $sqlParams = array();
        $dataCount = $this->dbSelect($sql, $sqlParams);
        
        $sql = "select strategy_group_uuid, strategy_group_name, strategy_group_type, remark, create_time, user_uuid, extra_info
        from bd_strategy_group
        where  strategy_group_uuid in ($listStr) order by $orderby $sort ";
        if($count != "all"){
            //如果不是查找所有,带上limit
            $sql .= " limit ?, ? ";
            $sqlParams = array($limit, $count);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        
        $info = array(
            'total' => $dataCount[0]['total'],
            'data' => $data
        );
        
        return $info;
    }
    
    /**
     * 公共方法
     * 得到用户可用策略组
     * @param string $useruuid  用户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetUserResourceStrategyGroup($useruuid, $orderby = "strategy_group_id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($useruuid);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetUserAllResource($useruuid, Xphp::$_config['RESOURCE_TYPE']['STRATEGY']);
        
        //获取资源内容
        $resource = $this->getResourceStrategyGroup($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 公共方法
     * 得到租户可用策略组
     * @param string $tenantuuid  租户uuid
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @author xiezhuowei@vinchin.com
     * @return array  资源列表,对应资源表
     */
    public function pGetTenantResourceStrategyGroup($tenantuuid, $orderby = "strategy_group_id", $sort = "desc", $limit = 0, $count = 10){
        $this->paramsCheck($tenantuuid);
        
        //获取租户下所有资源列表
        $resourceUUIDList = $this->pGetTenantAllResource($tenantuuid, Xphp::$_config['RESOURCE_TYPE']['STRATEGY']);
        
        //获取资源内容
        $resource = $this->getResourceStrategyGroup($resourceUUIDList, $orderby, $sort, $limit, $count);
        
        return $resource;
    }
    
    /**
     * 组合资源列表成为可查询字符串形式,方便查询如:'uuid1','uuid2','uuid3'
     * @param unknown $resourceUUIDList
     */
    public function groupResourceUUIDToString($resourceUUIDList, $keyword = "resource_uuid"){
        $listStr = "";
        foreach ($resourceUUIDList as $resource){
            if (!empty($listStr)){
                $listStr .= ", '" . $resource[$keyword] . "'";
            }else{
                $listStr .= "'" . $resource[$keyword] . "'";
            }
        }
        return $listStr;
    }
    
    /**
     * 获取资源组需要修改的信息
     * @param unknown $params
     */
    public function getResourceGroupOldInfo($params){
        $uuid = $params['uuid'];
        $sql = "select resource_group_name, description from bd_resource_group where resource_group_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $info = array();
        if(!empty($data)){
            $info = array(
                'name' => $data[0]['resource_group_name'],
                'description' => $data[0]['description'],
                'uuid' => $uuid
            );
        }
        
        return json_encode($info);
    }
    
    
    /**
     * 公共方法
     * 获取资源组拥有资源唯一标识列表
     * @param string $resourceGroupUUID 资源组唯一标识
     * @param int $resourceType 资源类型
     * @author luokai@vinchin.com
     * @return array 资源组所拥有资源唯一标识列表
     */
    public function pGetResourceGroupResource($resourceGroupUUID, $resourceType=""){
        $sql = "select resource_uuid, vm_uuid, vcenter_uuid from mt_resource_resource_group where resource_group_uuid = ? ";
        $sqlParams = array($resourceGroupUUID);
        if(!empty($resourceType)){
            $sql .= " and resource_type = ?";
            $sqlParams = array($resourceGroupUUID, $resourceType);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $resourceList = array();
        if(!empty($data)){
            $resourceList = $data;
        }
        
        return $resourceList;
        
    }
    
    
    /**
     * 得到资源组拥有的文件代理主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getResourceGroupFileHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("agent_uuid", "hostname", "ip", "os_type", "" );
        
        $this->paramsCheck($resourceGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetResourceGroupResource($resourceGroupUUID, Xphp::$_config['RESOURCE_TYPE']['FILE_HOST']);
        //获取资源内容
        $resourceInfo = $this->getResourceFileHost($resourceUUIDList, "ba.id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['agent_uuid'].'">',
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到资源组拥有的数据库定时主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getResourceGroupDbHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("agent_uuid", "hostname", "ip", "os_type", "" );
        
        $this->paramsCheck($resourceGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetResourceGroupResource($resourceGroupUUID, Xphp::$_config['RESOURCE_TYPE']['DB_HOST']);
        //获取资源内容
        $resourceInfo = $this->getResourceDbHost($resourceUUIDList, "id", $sortType, $start, $length);
        
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['agent_uuid'].'">',
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到资源组拥有的数据库实时主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getResourceGroupCDPHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("host_uuid", "hostname", "ip", "os_version", "host_type", "status");
        
        $this->paramsCheck($resourceGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetResourceGroupResource($resourceGroupUUID, Xphp::$_config['RESOURCE_TYPE']['CDP_HOST']);
        //获取资源内容
        $resourceInfo = $this->getResourceCDPHost($resourceUUIDList, "id", $sortType, $start, $length);
        
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['host_uuid'].'">',
                    $d['host_name'],
                    $d['ip'],
                    $d['os_version'],
                    $dbDes['HOST_TYPE_DES'][$d['host_type']],
                    $dbDes['HOST_STATUS_DES'][$d['status']],
                    intval($d['status'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到资源组拥有的虚拟机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getResourceGroupVM($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("uuid", "name", "vcenter_uuid", "dir_path", "power_state" );
        $serach = $params['search'];
        
        $this->paramsCheck($resourceGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetResourceGroupResource($resourceGroupUUID, Xphp::$_config['RESOURCE_TYPE']['VM']);
        //获取资源内容
        $resourceInfo = $this->getResourceVM($resourceUUIDList, "tree_id", $sortType, $start, $length, false, $serach);
        
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $vmDes = require APP_PATH . "/vm/VmDescription.php";
            $vcenter = Xphp::instance('Vcenter');
            foreach ($list as $d){
                //获取虚拟化中心信息
                $vcenterInfo = $vcenter->pGetVcenterIPByUUID($d['vcenter_uuid']);
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['uuid'].'">',
                    $d['name'],
                    $vcenterInfo['vcenter_ip'],
                    $d['dir_path'],
                    $vmDes['VmMachineStatus'][intval($d['power_state'])],
                    $d['uuid'],
                    $d['vcenter_uuid'],
                    intval($d['power_state']),
                    $vcenterInfo['hypervisor']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到资源组拥有的备份代理
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getResourceGroupAppliance($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("appliance_uuid", "nickname", "ip", "port", "online_flag" );
        
        $this->paramsCheck($resourceGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetResourceGroupResource($resourceGroupUUID, Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM']);
        //获取资源内容
        $resourceInfo = $this->getResourceAppliance($resourceUUIDList, "id", $sortType, $start, $length);
        
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['appliance_uuid'].'">',
                    $d['nickname'],
                    $d['ip'],
                    $d['port'],
                    $nodeHandler->getApplianceStatus(intval($d['online_flag'])),
                    intval($d['online_flag'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到资源组拥有的节点
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getResourceGroupNode($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("node_uuid", "host_name", "ip", "auto_upgrade_flag", "" );
        
        $this->paramsCheck($resourceGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetResourceGroupResource($resourceGroupUUID, Xphp::$_config['RESOURCE_TYPE']['NODE']);
        //获取资源内容
        $resourceInfo = $this->getResourceNode($resourceUUIDList, "node_id", $sortType, $start, $length);
        
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                $nodeDeployStatus = $nodeHandler->getNodeDeployStatus($d['node_uuid']);
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['node_uuid'].'">',
                    $d['host_name'],
                    $d['ip'],
                    $nodeDeployStatus,
                    $nodeAllStatus['flag']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到资源组拥有的存储
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getResourceGroupStorage($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("storage_uuid", "storage_nickname", "storage_type", "node_uuid", "total_size", "free_size", "status", "use_mode");
        
        $this->paramsCheck($resourceGroupUUID);
        //获取用户所有指定资源uuid
        $resourceUUIDList = $this->pGetResourceGroupResource($resourceGroupUUID, Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
        //获取资源内容
        $resourceInfo = $this->getResourceStorage($resourceUUIDList, "storage_id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $utils = Xphp::instance('Utils');
            $nodeHandler = Xphp::instance('NodeHandler');
            $storageHandler = Xphp::instance('StorageHandler');
            foreach ($list as $d){
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $nodeDes =  $nodeHandler->pGetStorageNodeDes($d['node_uuid']);
                $nodeStatus = $nodeAllStatus['flag'];
                if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
                    $storageConfig = json_decode($d['storage_config'], true);
                    $nodeDes = $storageConfig['remote_name'].'('.$storageConfig['remote_ip'].')';
                    $nodeStatus = $utils->parseFlagToBool(intval($storageConfig['remote_status']));
                }
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['storage_uuid'].'">',
                    $d['storage_nickname'],
                    $storageHandler->getStorageTypeDes($d['storage_type']),
                    $nodeDes,
                    $utils->calSize($d['total_size']),
                    $utils->calSize($d['free_size']),
                    $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                    $storageHandler->getUsemodeDes(intval($d['use_mode'])),
                    $storageHandler->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag']))
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
        
    }
    
    /**
     * 得到用户拥有的文件代理主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserFileHostResource($params){
        $userUUID = $params['userUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("agent_uuid", "hostname", "ip", "os_type", "" );
        $search = $params['search'];
        
        $this->paramsCheck($userUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceFileHost($userUUID, "ba.id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['agent_uuid'].'">',
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户拥有的数据库定时主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserDbHostResource($params){
        $userUUID = $params['userUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("agent_uuid", "hostname", "ip", "os_type", "" );
        $search = $params['search'];
        
        $this->paramsCheck($userUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceDbHost($userUUID, "id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['agent_uuid'].'">',
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户拥有的数据库实时主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserCDPHostResource($params){
        $userUUID = $params['userUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("host_uuid", "hostname", "ip", "os_version", "host_type", "status");
        $search = $params['search'];
        
        $this->paramsCheck($userUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceCDPHost($userUUID, "id", $sortType, $start, $length, $search);
        
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['host_uuid'].'">',
                    $d['host_name'],
                    $d['ip'],
                    $d['os_version'],
                    $dbDes['HOST_TYPE_DES'][$d['host_type']],
                    $dbDes['HOST_STATUS_DES'][$d['status']],
                    intval($d['status'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户拥有的虚拟机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserVMResource($params){
        $userUUID = $params['userUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("uuid", "name", "vcenter_uuid", "dir_path", "power_state" );
        $search = $params['search'];
        
        if(!$userUUID){
            $userUUID = Xphp::$_user['useruuid'];
        }
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceVM($userUUID, "tree_id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $vmDes = require APP_PATH . "/vm/VmDescription.php";
            $vcenter = Xphp::instance('Vcenter');
            foreach ($list as $d){
                //获取虚拟化中心信息
                $vcenterInfo = $vcenter->pGetVcenterIPByUUID($d['vcenter_uuid']);
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['uuid'].'">',
                    $d['name'],
                    $vcenterInfo['vcenter_ip'],
                    $d['dir_path'],
                    $vmDes['VmMachineStatus'][intval($d['power_state'])],
                    $d['uuid'],
                    $d['vcenter_uuid'],
                    intval($d['power_state']),
                    $vcenterInfo['hypervisor']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户拥有的备份代理
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserApplianceResource($params){
        $userUUID = $params['userUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("appliance_uuid", "nickname", "ip", "port", "online_flag" );
        $search = $params['search'];
        
        $this->paramsCheck($userUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceAppliance($userUUID, "id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                $checkBox = '';
                //当前用户创建的传输代理不允许取消关联
                if($d['user_uuid'] != $userUUID){
                    $checkBox = '<input type="checkbox" name="id[]" value="'.$d['appliance_uuid'].'">';
                }
                $records['data'][] = array(
                    $checkBox,
                    $d['nickname'],
                    $d['ip'],
                    $d['port'],
                    $nodeHandler->getApplianceStatus(intval($d['online_flag'])),
                    intval($d['online_flag'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户拥有的节点
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserNodeResource($params){
        $userUUID = $params['userUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("node_uuid", "host_name", "ip", "auto_upgrade_flag", "" );
        $search = $params['search'];
        
        $this->paramsCheck($userUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceNode($userUUID, "node_id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                $nodeDeployStatus = $nodeHandler->getNodeDeployStatus($d['node_uuid']);
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['node_uuid'].'">',
                    $d['host_name'],
                    $d['ip'],
                    $nodeDeployStatus,
                    $nodeAllStatus['flag']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户拥有的存储
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserStorageResource($params){
        $userUUID = $params['userUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("storage_uuid", "storage_nickname", "storage_type", "node_uuid", "total_size", "free_size", "status", "use_mode");
        $search = $params['search'];
        
        $this->paramsCheck($userUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceStorage($userUUID, "storage_id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $utils = Xphp::instance('Utils');
            $nodeHandler = Xphp::instance('NodeHandler');
            $storageHandler = Xphp::instance('StorageHandler');
            foreach ($list as $d){
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $nodeDes =  $nodeHandler->pGetStorageNodeDes($d['node_uuid']);
                $nodeStatus = $nodeAllStatus['flag'];
                if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
                    $storageConfig = json_decode($d['storage_config'], true);
                    $nodeDes = $storageConfig['remote_name'].'('.$storageConfig['remote_ip'].')';
                    $nodeStatus = $utils->parseFlagToBool(intval($storageConfig['remote_status']));
                }
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['storage_uuid'].'">',
                    $d['storage_nickname'],
                    $storageHandler->getStorageTypeDes($d['storage_type']),
                    $nodeDes,
                    $utils->calSize($d['total_size']),
                    $utils->calSize($d['free_size']),
                    $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                    $storageHandler->getUsemodeDes(intval($d['use_mode'])),
                    $storageHandler->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag']))
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
        
    }
    
    
    /**
     * 
     * 获取用户拥有的资源组
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json 返回资源组列表
     */
    public function getUserResourceGroup($params){
        $userUUID = $params['userUUID']; //需要添加关联的用户UUID
        $start = $params['start'];
        $length = $params['length'];
        $search = $params['search'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("resource_group_uuid", "resource_group_name", "description");
        
        $this->paramsCheck($userUUID);
        //获取用户拥有资源组
        $resourceInfo = $this->pGetUserResourceGroup($userUUID, $sortArr[$sortColumn], $sortType, $start, $length,false, $search,false);
        $list = $resourceInfo['data'];
        
        $count = intval($resourceInfo['total']);
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            foreach ($list as $d){
                $checkbox = "";
                //当前用户创建资源组需要到对应资源组列表进行删除
                if($d['create_user_uuid'] != $userUUID){
                    $checkbox = '<input type="checkbox" name="id[]" value="'.$d['resource_group_uuid'].'">';
                }
                $records['data'][] = array(
                    $checkbox,
                    $d['resource_group_name'],
                    $d['description']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    
    /**
     * 得到用户组拥有的文件代理主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserGroupFileHostResource($params){
        $userGroupUUID = $params['userGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("agent_uuid", "hostname", "ip", "os_type", "" );
        
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有资源内容
        $resourceInfo = $this->pGetUserGroupResourceFileHost("", $userGroupUUID, "id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['agent_uuid'].'">',
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户组拥有的数据库定时主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserGroupDbHostResource($params){
        $userGroupUUID = $params['userGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("agent_uuid", "hostname", "ip", "os_type", "" );
        
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有资源内容
        $resourceInfo = $this->pGetUserGroupResourceDbHost("", $userGroupUUID, "id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['agent_uuid'].'">',
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    
    /**
     * 得到用户组拥有的数据库实时主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserGroupCDPHostResource($params){
        $userGroupUUID = $params['userGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("host_uuid", "hostname", "ip", "os_version", "host_type", "status");
        
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有资源内容
        $resourceInfo = $this->pGetUserGroupResourceCDPHost("", $userGroupUUID, "id", $sortType, $start, $length);
        
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['host_uuid'].'">',
                    $d['host_name'],
                    $d['ip'],
                    $d['os_version'],
                    $dbDes['HOST_TYPE_DES'][$d['host_type']],
                    $dbDes['HOST_STATUS_DES'][$d['status']],
                    intval($d['status'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户组拥有的虚拟机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserGroupVMResource($params){
        $userGroupUUID = $params['userGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("uuid", "name", "vcenter_uuid", "dir_path", "power_state" );
        $search = $params['search'];
        
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有资源内容
        $resourceInfo = $this->pGetUserGroupResourceVM("", $userGroupUUID, "tree_id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $vmDes = require APP_PATH . "/vm/VmDescription.php";
            $vcenter = Xphp::instance('Vcenter');
            foreach ($list as $d){
                //获取虚拟化中心信息
                $vcenterInfo = $vcenter->pGetVcenterIPByUUID($d['vcenter_uuid']);
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['uuid'].'">',
                    $d['name'],
                    $vcenterInfo['vcenter_ip'],
                    $d['dir_path'],
                    $vmDes['VmMachineStatus'][intval($d['power_state'])],
                    $d['uuid'],
                    $d['vcenter_uuid'],
                    intval($d['power_state']),
                    $vcenterInfo['hypervisor']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户组拥有的备份代理
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserGroupApplianceResource($params){
        $userGroupUUID = $params['userGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("appliance_uuid", "nickname", "ip", "port", "online_flag" );
        
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有资源内容
        $resourceInfo = $this->pGetUserGroupResourceAppliance("", $userGroupUUID, "id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['appliance_uuid'].'">',
                    $d['nickname'],
                    $d['ip'],
                    $d['port'],
                    $nodeHandler->getApplianceStatus(intval($d['online_flag'])),
                    intval($d['online_flag'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户组拥有的节点
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserGroupNodeResource($params){
        $userGroupUUID = $params['userGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("node_uuid", "host_name", "ip", "auto_upgrade_flag", "" );
        
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有资源内容
        $resourceInfo = $this->pGetUserGroupResourceNode("", $userGroupUUID, "node_id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                $nodeDeployStatus = $nodeHandler->getNodeDeployStatus($d['node_uuid']);
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['node_uuid'].'">',
                    $d['host_name'],
                    $d['ip'],
                    $nodeDeployStatus,
                    $nodeAllStatus['flag']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到用户组拥有的存储
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getUserGroupStorageResource($params){
        $userGroupUUID = $params['userGroupUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("storage_uuid", "storage_nickname", "storage_type", "node_uuid", "total_size", "free_size", "status", "use_mode");
        
        $this->paramsCheck($userGroupUUID);
        //获取用户组所有资源内容
        $resourceInfo = $this->pGetUserGroupResourceStorage("", $userGroupUUID, "storage_id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $utils = Xphp::instance('Utils');
            $nodeHandler = Xphp::instance('NodeHandler');
            $storageHandler = Xphp::instance('StorageHandler');
            foreach ($list as $d){
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $nodeDes =  $nodeHandler->pGetStorageNodeDes($d['node_uuid']);
                $nodeStatus = $nodeAllStatus['flag'];
                if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
                    $storageConfig = json_decode($d['storage_config'], true);
                    $nodeDes = $storageConfig['remote_name'].'('.$storageConfig['remote_ip'].')';
                    $nodeStatus = $utils->parseFlagToBool(intval($storageConfig['remote_status']));
                }
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['storage_uuid'].'">',
                    $d['storage_nickname'],
                    $storageHandler->getStorageTypeDes($d['storage_type']),
                    $nodeDes,
                    $utils->calSize($d['total_size']),
                    $utils->calSize($d['free_size']),
                    $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                    $storageHandler->getUsemodeDes(intval($d['use_mode'])),
                    $storageHandler->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag']))
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
        
    }
    
    
    /**
     *
     * 获取用户组拥有的资源组
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json 返回资源组列表
     */
    public function getUserGroupResourceGroup($params){
        $userGroupUUID = $params['userGroupUUID']; //需要添加关联的用户组UUID
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("brg.resource_group_uuid", "brg.resource_group_name", "brg.description");
        
        $this->paramsCheck($userGroupUUID);
        //获取用户组拥有资源组
        $resourceInfo = $this->pGetUserGroupResourceGroup($userGroupUUID, $sortArr[$sortColumn], $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $count = intval($resourceInfo['total']);
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            foreach ($list as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'.$d['resource_group_uuid'].'">',
                    $d['resource_group_name'],
                    $d['description']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    
    /**
     * 获取当前用户所有可添加资源组
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json 返回资源组列表
     */
    public function getAllResourceGroup($params){
        $userUUID = $params['userUUID'];                    //需要添加关联的用户UUID
        $userGroupUUID = $params['userGroupUUID'];          //需要添加关联的用户组UUID
        $sourceFrom = $params['sourceFrom'];                      //资源分配来源：资源组|用户|用户组
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("resource_group_uuid", "resource_group_name", "description");
        $search = $params['search'];
        
            //获取用户拥有资源组
        $resourceInfo = $this->pGetUserResourceGroup(Xphp::$_user['useruuid'], $sortArr[$sortColumn], $sortType, $start, $length, false, $search,true);
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $userHandler = Xphp::instance('UsersHandler');
            foreach ($list as $d){
                //检查是否拥有该资源
                $haveFlag = false;
                switch ($sourceFrom){
                    case Xphp::$_config['RESOURCE_FROM']['USER']:
                        if($userUUID){
                            $haveFlag = $userHandler->pGetUserResourceGroupFlag($userUUID, $d['resource_group_uuid'], true);
                        }
                        break;
                    case Xphp::$_config['RESOURCE_FROM']['USER_GROUP']:
                        if($userGroupUUID){
                            $haveFlag = $userHandler->pGetUserGroupResourceGroupFlag($userGroupUUID, $d['resource_group_uuid'], true);
                        }
                        break;
                        
                }
                $checkBox = '<input type="checkbox" name="id[]" value="'.$d['resource_group_uuid'].'">';
                if($haveFlag){
//                     $count--;
//                     continue;
                    $checkBox = "";
                }
                $records['data'][] = array(
                    $checkBox,
                    $d['resource_group_name'],
                    $d['description']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    
    /**
     * 得到租户拥有的文件代理主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getTenantFileHostResource($params){
        $tenantUUID = $params['tenantUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("hostname", "ip", "os_type", "");
        
        $this->paramsCheck($tenantUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetTenantResourceFileHost($tenantUUID, "ba.id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    
    /**
     * 得到租户拥有的数据库定时主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getTenantDbHostResource($params){
        $tenantUUID = $params['tenantUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("hostname", "ip", "os_type", "");
        
        $this->paramsCheck($tenantUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetTenantResourceDbHost($tenantUUID, "id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $agentHandler = Xphp::instance('AgentHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    $d['hostname'],
                    $d['ip'],
                    $d['os_type'],
                    $agentHandler->getAgentStatusDes($d['online_flag'], $d['register_flag']),
                    $agentHandler->getAgentStatus($d['online_flag'], $d['register_flag']),
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到租户拥有的数据库实时主机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getTenantCDPHostResource($params){
        $tenantUUID = $params['tenantUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("hostname", "ip", "os_version", "host_type", "status");
        
        $this->paramsCheck($tenantUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetTenantResourceCDPHost($tenantUUID, "id", $sortType, $start, $length);
        
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
            foreach ($list as $d){
                $records['data'][] = array(
                    $d['host_name'],
                    $d['ip'],
                    $d['os_version'],
                    $dbDes['HOST_TYPE_DES'][$d['host_type']],
                    $dbDes['HOST_STATUS_DES'][$d['status']],
                    intval($d['status'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到租户拥有的虚拟机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getTenantVMResource($params){
        $tenantUUID = $params['tenantUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("name", "vcenter_uuid", "dir_path", "power_state" );
        
        $this->paramsCheck($tenantUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetTenantResourceVM($tenantUUID, "tree_id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $vmDes = require APP_PATH . "/vm/VmDescription.php";
            $vcenter = Xphp::instance('Vcenter');
            foreach ($list as $d){
                //获取虚拟化中心信息
                $vcenterInfo = $vcenter->pGetVcenterIPByUUID($d['vcenter_uuid']);
                $records['data'][] = array(
                    $d['name'],
                    $vcenterInfo['vcenter_ip'],
                    $d['dir_path'],
                    $vmDes['VmMachineStatus'][intval($d['power_state'])],
                    $d['uuid'],
                    $d['vcenter_uuid'],
                    intval($d['power_state']),
                    $vcenterInfo['hypervisor']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到租户拥有的备份代理
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getTenantApplianceResource($params){
        $tenantUUID = $params['tenantUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("nickname", "ip", "port", "online_flag" );
        
        $this->paramsCheck($tenantUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetTenantResourceAppliance($tenantUUID, "id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                $records['data'][] = array(
                    $d['nickname'],
                    $d['ip'],
                    $d['port'],
                    $nodeHandler->getApplianceStatus(intval($d['online_flag'])),
                    intval($d['online_flag'])
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到租户拥有的节点
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getTenantNodeResource($params){
        $tenantUUID = $params['tenantUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("host_name", "ip", "auto_upgrade_flag", "" );
        
        $this->paramsCheck($tenantUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetTenantResourceNode($tenantUUID, "node_id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($list as $d){
                $nodeDeployStatus = $nodeHandler->getNodeDeployStatus($d['node_uuid']);
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $records['data'][] = array(
                    $d['host_name'],
                    $d['ip'],
                    $nodeDeployStatus,
                    $nodeAllStatus['flag']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
    }
    
    /**
     * 得到租户拥有的存储
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getTenantStorageResource($params){
        $tenantUUID = $params['tenantUUID'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("storage_nickname", "storage_type", "node_uuid", "total_size", "free_size", "status", "use_mode");
        
        $this->paramsCheck($tenantUUID);
        //获取用户所有资源内容
        $resourceInfo = $this->pGetTenantResourceStorage($tenantUUID, "storage_id", $sortType, $start, $length);
        $list = $resourceInfo['data'];
        
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $utils = Xphp::instance('Utils');
            $nodeHandler = Xphp::instance('NodeHandler');
            $storageHandler = Xphp::instance('StorageHandler');
            foreach ($list as $d){
                $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
                $nodeDes =  $nodeHandler->pGetStorageNodeDes($d['node_uuid']);
                $nodeStatus = $nodeAllStatus['flag'];
                if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
                    $storageConfig = json_decode($d['storage_config'], true);
                    $nodeDes = $storageConfig['remote_name'].'('.$storageConfig['remote_ip'].')';
                    $nodeStatus = $utils->parseFlagToBool(intval($storageConfig['remote_status']));
                }
                $records['data'][] = array(
                    $d['storage_nickname'],
                    $storageHandler->getStorageTypeDes($d['storage_type']),
                    $nodeDes,
                    $utils->calSize($d['total_size']),
                    $utils->calSize($d['free_size']),
                    $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                    $storageHandler->getUsemodeDes(intval($d['use_mode'])),
                    $storageHandler->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag']))
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = intval($resourceInfo['total']);
        $records['recordsFiltered'] = intval($resourceInfo['total']);
        
        return json_encode($records);
        
    }
    
    
    
    /**
     * 通过用户UUID获取资源组列表
     * @param string $userUUID 用户唯一标识
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * $param array   $search   搜索参数集合
     * $param boolean $createFlag   分配资源组标记
     * @author luokai@vinchin.com
     * @return array  资源组信息列表
     */
    public function pGetUserResourceGroup($userUUID, $orderby, $sort, $limit, $count, $flag = false, $search = array(), $createFlag = false){
        
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.description, brg.create_user_uuid from bd_resource_group brg, mt_user_resource_group murg where brg.resource_group_uuid = murg.resource_group_uuid ";
        
        
        $sqlCount = "select count(murg.resource_group_uuid) as total from bd_resource_group brg, mt_user_resource_group murg where brg.resource_group_uuid = murg.resource_group_uuid ";
        $utils = Xphp::instance('Utils');
        
        $sqlParams = array();
        $sqlCountParams = array();
        if(!$flag){
            $sql .= " and murg.user_uuid = ? ";
            $sqlCount .= " and murg.user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($userUUID));
            $sqlCountParams = array_merge($sqlCountParams, array($userUUID));
        }
        
        //分配资源组只能分配自己创建的
        if($createFlag){
            $sql .= " and brg.create_user_uuid = ? ";
            $sqlCount .= " and brg.create_user_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($userUUID));
            $sqlCountParams = array_merge($sqlCountParams, array($userUUID));
        }
        
        if(!empty($search)){
            $name = $search['name'];
            $name = $utils->escapeWildcard($name);
            $sql .= " and  brg.resource_group_name like '%". $name ."%'";
            $sqlCount .= " and  brg.resource_group_name like '%". $name ."%'";
        }
        
        
        $sql .= " order by $orderby $sort limit ?, ?";
        $sqlParams = array_merge($sqlParams, array($limit, $count));
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $info = array(
            'total' => $dataCount[0]['total'],
            'data' => $data
        );
        return $info;
    }
    
    /**
     * 通过用组UUID获取资源组列表
     * @param string $userGroupUUID 用户组唯一标识
     * @param string $orderby   按哪个字段排序,默认使用id字段
     * @param string $sort      排序方式,默认desc,可传入asc
     * @param number $limit     从哪个开始,默认0
     * @param number $count     本次取多少个,默认10, 特别:"all"取所有
     * @param boolean $flag     检查用户是否属于Master用户组
     * @author luokai@vinchin.com
     * @return array  资源组信息列表
     */
    public function pGetUserGroupResourceGroup($userGroupUUID, $orderby, $sort, $limit, $count, $flag = false){
        
        $sql = "select distinct brg.resource_group_uuid, brg.resource_group_name, brg.description from bd_resource_group brg, mt_user_group_resource_group mugrg where brg.resource_group_uuid = mugrg.resource_group_uuid ";
        
        
        $sqlCount = "select count(mugrg.resource_group_uuid) as total from bd_resource_group brg, mt_user_group_resource_group mugrg where brg.resource_group_uuid = mugrg.resource_group_uuid ";
        
        $sqlParams = array($limit, $count);
        $sqlCountParams = array();
        if(!$flag){
            $sql .= " and mugrg.user_group_uuid = ? ";
            $sqlCount .= " and mugrg.user_group_uuid = ? ";
            $sqlParams = array($userGroupUUID, $limit, $count);
            $sqlCountParams = array($userGroupUUID);
        }
        $sql .= " order by $orderby $sort limit ?, ?";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $info = array(
            'total' => $dataCount[0]['total'],
            'data' => $data
        );
        return $info;
    }
    
    
    
    /**
     * 添加资源组和文件代理关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addResourceGroupFileHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['FILE_HOST'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_ADD_FILE_HOST'];
        return $this->pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType, $opName);
    
    }
    
    
    /**
     * 取消资源组和文件代理关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteResourceGroupFileHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_CANCEL_FILE_HOST'];
        
        //调用统一处理取消资源组和资源关联
        return $this->pDeleteResourceGroupResourceUnify($resourceGroupUUID, $uuids, $opName);
        
    }
    
    /**
     * 添加资源组和数据库定时主机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addResourceGroupDbHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['DB_HOST'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_ADD_DB_HOST'];
        return $this->pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType, $opName);
        
    }
    
    
    /**
     * 取消资源组和数据库定时主机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteResourceGroupDbHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_CANCEL_DB_HOST'];
        
        //调用统一处理取消资源组和资源关联
        return $this->pDeleteResourceGroupResourceUnify($resourceGroupUUID, $uuids, $opName);
        
    }
    
    
    /**
     * 添加资源组和数据库实时主机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addResourceGroupCDPHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['CDP_HOST'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_ADD_CDP_HOST'];
        return $this-> pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType, $opName);
    }
    
    /**
     * 取消资源组和数据库实时主机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteResourceGroupCDPHost($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_CANCEL_CDP_HOST'];
        
        //调用统一处理取消资源组和资源关联
        return $this->pDeleteResourceGroupResourceUnify($resourceGroupUUID, $uuids, $opName);
        
    }
    
    /**
     * 添加资源组和虚拟机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addResourceGroupVM($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['VM'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_ADD_VM'];
        return $this-> pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType, $opName);
    }
    
    /**
     * 取消资源组和虚拟机关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteResourceGroupVM($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_CANCEL_VM'];
        
        //调用统一处理取消资源组和资源关联
        return $this->pDeleteResourceGroupResourceUnify($resourceGroupUUID, $uuids, $opName);
        
    }
    
    /**
     * 添加资源组和虚拟机备份代理关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addResourceGroupAppliance($params){
        
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['APPLIANCE_VM'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_ADD_APPLIANCE'];
        return $this-> pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType, $opName);
    }
    
    /**
     * 取消资源组和虚拟机备份代理关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteResourceGroupAppliance($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_CANCEL_APPLIANCE'];
        
        //调用统一处理取消资源组和资源关联
        return $this->pDeleteResourceGroupResourceUnify($resourceGroupUUID, $uuids, $opName);
        
    }
    
    /**
     * 添加资源组和节点关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addResourceGroupNode($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $resourceList = $params['list'];
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['NODE'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_ADD_NODE'];
        return $this-> pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType, $opName);
    }
    
    /**
     * 取消资源组和节点关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteResourceGroupNode($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_CANCEL_NODE'];
        
        //调用统一处理取消资源组和资源关联
        return $this->pDeleteResourceGroupResourceUnify($resourceGroupUUID, $uuids, $opName);
        
    }
    
    /**
     * 添加资源组和存储关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function addResourceGroupStorage($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $resourceList = $params['list'];
        //关联存储所在节点
        $this->addResourceGroupStorageNode($resourceGroupUUID, $resourceList);
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['STORAGE'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_ADD_STORAGE'];
        return $this-> pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType, $opName);
    }
    
    /**
     * 取消资源组和存储关联关系
     * @param unknown $params
     * @return boolean 返回取消关联结果 成功|失败
     */
    public function deleteResourceGroupStorage($params){
        $resourceGroupUUID = $params['resourceGroupUUID'];
        $uuids = $params['uuids'];
        $opName = Xphp::$_lang['WEB_RESOURCE_GROUP_CANCEL_STORAGE'];
        
        //调用统一处理取消资源组和资源关联
        return $this->pDeleteResourceGroupResourceUnify($resourceGroupUUID, $uuids, $opName);
        
    }
    
    
    
    /**
     * 添加资源与资源组关联公共调用方法
     * @param string $resourceGroupUUID 资源组唯一标识
     * @param array $resourceList 资源信息列表
     * @param int $resourceType 资源类型
     * @param string $opName 添加关联操作描述
     * @author luokai@vinchin.com
     * @return string 统一返回操作结果消息到界面
     */
    public function pAddResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $resourceType = "", $opName = ''){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_resource_group_manager");
        //调用添加资源组与资源关联公共方法
        $userHandler = Xphp::instance("UsersHandler");
        $resourceInfo=  array();
        if(!empty($resourceList)){
            foreach ($resourceList as $res){
                $resourceInfo[] = array(
                    'resourceuuid' => $res['resourceuuid'],
                    'vmuuid' => $res['vmuuid'],
                    'vcenteruuid' => $res['vcenteruuid'],
                    'resourceType' => $resourceType
                );
            }
        }
        $result = $userHandler->pAddResourceGroupResource($resourceGroupUUID, $resourceInfo);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName,"", "warning");
        }
    }
    
    
    
    /**
     * 
     * @param string $resourceGroupUUID
     * @param array $resourceList
     * @param string $opName
     * @return string
     */
    public function pDeleteResourceGroupResourceUnify($resourceGroupUUID, $resourceList, $opName){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_resource_group_manager");
        //调用取消资源组与资源关联公共方法
        $userHandler = Xphp::instance("UsersHandler");
        $result = $userHandler->pDeleteResourceGroupResource($resourceGroupUUID, $resourceList);
        if($result){
            return $this->muOpResult(true, $opName);
        }else{
            return $this->muOpResult(false, $opName,"", "warning");
        }
    }
    
    /**
     * 公共方法
     * 获取资源组是否关联资源标记
     * @param string $resourcegroupuuid
     * @param string $resourceuuid
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pGetResourceGroupResourceFlag($resourceGroupUUID, $resourceuuid, $vmFlag = false){
        $sql = "select id from mt_resource_resource_group where resource_uuid = ? ";
        $sqlParams = array($resourceuuid);
        //除虚拟机资源外其余资源可以共享分配
        if(!$vmFlag){
            $sql .= " and resource_group_uuid = ? ";
            $sqlParams = array_merge($sqlParams, array($resourceGroupUUID));
        }
        
        $data = $this->dbSelect($sql, $sqlParams);
        $flag = FALSE;
        if(!empty($data)){
            $flag = TRUE;
        }
        
        return $flag;
    }
    
    /**
     * 公共方法
     * 获取资源组是否关联虚拟机标记
     * @param string $resourcegroupuuid
     * @param string $resourceuuid
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pGetResourceGroupVmFlag($resourcegroupuuid){
        $sql = "select id from mt_resource_resource_group where resource_group_uuid = ?";
        $data = $this->dbSelect($sql, array($resourcegroupuuid));
        $flag = FALSE;
        if(!empty($data)){
            $flag = TRUE;
        }
        
        return $flag;
    }
    
    /**
     * 得到用户拥有的虚拟机
     * @param unknown $params
     * @author luokai@vinchin.com
     * @return json
     */
    public function getBackupVMResource($params){
        $search = $params['search'];
        $hypervisor = $params['hypervisor'];
        $search['hypervisor'] = $hypervisor;
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("uuid", "name", "vcenter_uuid", "dir_path", "", "power_state" );
        
        $userUUID = Xphp::$_user['useruuid'];
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceVM($userUUID, "tree_id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        $count = intval($resourceInfo['total']);
        $records = array();
        $records['data'] = array();
        if(!empty($list)){
            $vmDes = require APP_PATH . "/vm/VmDescription.php";
            $vcenter = Xphp::instance('Vcenter');
            $vmInfo = $vcenter->getTenantBackupVMInfo();
            foreach ($list as $d){
                //获取虚拟化中心信息
                $vcenterInfo = $vcenter->pGetVcenterIPByUUID($d['vcenter_uuid']);
                $checkBox = '<input type="checkbox" name="id[]" value="'.$d['uuid'].'">';
                $inBackupFlag = $vcenter->getVMInBackupFlag($d['type'], $d['uuid'], $d['vcenter_uuid'], $vmInfo);
                $backupStatus = false;
                $backupDes = Xphp::$_lang['UI_VM_REPORT_NOIN_BACKUP'];
                if($inBackupFlag){
                    $checkBox = "";
                    $backupDes = Xphp::$_lang['UI_VM_REPORT_IN_BACKUP'];
                    $username = $this->getVMTaskUserName($d['uuid'], $d['vcenter_uuid']);
                    if($username != Xphp::$_user['username']){
                        $backupDes .= "(".$username.")";
                    }
                    $backupStatus = true;
                }
                $records['data'][] = array(
                    $checkBox,
                    $d['name'],
                    $vcenterInfo['vcenter_ip'],
                    $d['dir_path'],
                    $backupDes,
                    $vmDes['VmMachineStatus'][intval($d['power_state'])],
                    $d['uuid'],
                    $d['vcenter_uuid'],
                    intval($d['power_state']),
                    $vcenterInfo['hypervisor'],
                    $backupStatus,
                    $d['type']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    /**
     * 获取修改备份任务虚拟机列表
     * @param unknown $params
     * @return string
     * @author luokai@vinchin.com
     */
    public function getBackupOldVMResource($params){
        $search = $params['search'];
        $hypervisor = $params['hypervisor'];
        $search['hypervisor'] = $hypervisor;
        $taskuuid = $params['taskuuid'];
        $sql = "select vm_uuid, vm_config from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $taskVmList = array();
        $diskList = array();
        if(!empty($data)){
            foreach ($data as $d){
                
                $taskVmList[] = $d['vm_uuid'];
                $diskList[$d['vm_uuid']] = json_decode($d['vm_config'], true);
            }
            
        }
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array("uuid", "name", "vcenter_uuid", "dir_path", "", "power_state" );
        
        $userUUID = Xphp::$_user['useruuid'];
        //获取用户所有资源内容
        $resourceInfo = $this->pGetUserResourceVM($userUUID, "tree_id", $sortType, $start, $length, $search);
        $list = $resourceInfo['data'];
        $records = array();
        $records['data'] = array();
        $count = intval($resourceInfo['total']);
        if(!empty($list)){
            $vmDes = require APP_PATH . "/vm/VmDescription.php";
            $vcenter = Xphp::instance('Vcenter');
            $vmInfo = $vcenter->getBackupVMInfo();
            foreach ($list as $d){
                //获取虚拟化中心信息
                $vcenterInfo = $vcenter->pGetVcenterIPByUUID($d['vcenter_uuid']);
                $checkBox = '<input type="checkbox" name="id[]" value="'.$d['uuid'].'">';
                $inBackupFlag = $vcenter->getVMInBackupFlag($d['type'], $d['uuid'], $d['vcenter_uuid'], $vmInfo);
                $diskArray = array();
                $backupStatus = false;
                $backupDes = Xphp::$_lang['UI_VM_REPORT_NOIN_BACKUP'];
                if(in_array($d['uuid'], $taskVmList)){
                    $checkBox = '<input checked type="checkbox" name="id[]" value="'.$d['uuid'].'">';
                    $diskArray = $diskList[$d['uuid']];
                }else if($inBackupFlag){
                    $checkBox = "";
                }
                
                if($inBackupFlag){
                    $backupDes = Xphp::$_lang['UI_VM_REPORT_IN_BACKUP'];
                    $backupStatus = true;
                }
                $otherInfo = array(
                    'hostuuid' => $d['host_uuid'],
                    'version' => $d['version'],
                    'name' => $d['name']
                );
                $records['data'][] = array(
                    $checkBox,
                    $d['name'],
                    $vcenterInfo['vcenter_ip'],
                    $d['dir_path'],
                    $backupDes,
                    $vmDes['VmMachineStatus'][intval($d['power_state'])],
                    $d['uuid'],
                    $d['vcenter_uuid'],
                    intval($d['power_state']),
                    $vcenterInfo['hypervisor'],
                    $backupStatus,
                    $diskArray,
                    $otherInfo,
                    $d['type']
                );
            }
            
        }
        $records['draw'] = $params['draw'];
        $records['recordsTotal'] = $count;
        $records['recordsFiltered'] = $count;
        
        return json_encode($records);
    }
    
    
    
    /**
     * 公共方法
     * 取消选择资源组与所有用户的关联
     * @param array $list  资源组uuid集合
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pDeleteResourceGroupUser($list){
        if(empty($list)) return true;
        $listDes = implode("','", $list);
        $sql = "delete from mt_user_resource_group where resource_group_uuid in ('".$listDes."')";
        $result = $this->dbExec($sql);
        
        return true;
    }
    
    /**
     * 公共方法
     * 取消选择资源组与所有用户组的关联
     * @param array $list  资源组uuid集合
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pDeleteResourceGroupUserGroup($list){
        if(empty($list)) return true;
        $listDes = implode("','", $list);
        $sql = "delete from mt_user_group_resource_group where resource_group_uuid in ('".$listDes."')";
        $result = $this->dbExec($sql);
        
        return true;
    }
    
    /**
     * 公共方法
     * 取消选择资源组与所有用户的关联
     * @param array $list  资源组uuid集合
     * @author luokai@vinchin.com
     * @return boolean
     */
    public function pDeleteResourceGroupResource($list){
        if(empty($list)) return true;
        $listDes = implode("','", $list);
        $sql = "delete from mt_resource_resource_group where resource_group_uuid in ('".$listDes."')";
        $result = $this->dbExec($sql);
        
        return true;
    }
    
    /**
     * 检查资源组名是否已存在
     * @param unknown $params
     * @return string
     */
    public function resourceGroupAvailable($params){
        $resourcegroupname = $params['name'];
        $sql = "select resource_group_uuid from bd_resource_group where resource_group_name = ? and create_user_uuid = ? ";
        $sqlParams = array($resourcegroupname, Xphp::$_user['useruuid']);
        $data = parent::dbSelect($sql, $sqlParams);
        return json_encode(empty($data));
    }
    
    
    /**
     * 获取虚拟机任务用户名
     * @param string $vmuuid
     * @param string $vcenteruuid
     * @return mixed|fetchAll()
     */
    private function getVMTaskUserName($vmuuid, $vcenteruuid){
        $sql = "select bu.user_name from bd_task bt, vm_machine_list vml, bd_user bu where 
                bt.task_uuid = vml.task_uuid and bt.user_uuid = bu.user_uuid and vml.vm_uuid = ? and vml.vcenter_uuid = ? ";
        $data = $this->dbSelect($sql, array($vmuuid, $vcenteruuid));
        $name = Xphp::$_config['NULLSPACE'];
        if(!empty($data)){
            $name = $data[0]['user_name'];
        }
        
        return $name;
    }
    
    /**
     * 添加存储关联时增加资源组和节点强关联
     * @param unknown $resourceGroupUUID
     * @param unknown $resourceList
     */
    private function addResourceGroupStorageNode($resourceGroupUUID, $resourceList){
        if(empty($resourceList)) return;
        $list = array();
        foreach ($resourceList as $res){
            $list[] = $res['resourceuuid'];
        }
        $listDes = implode("','", $list);
        //获取存储所在节点uuid
        $sql = "select distinct node_uuid from bd_storage_resource where storage_uuid in ('".$listDes."')";
        $data = $this->dbSelect($sql);
        
        $nodes = array();
        if(!empty($data)){
            foreach ($data as $d){
                $nodes[] = $d['node_uuid'];
            }
        }
        //节点资源类型
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['NODE'];
        //根据资源组UUID获取到已关联的节点UUID集合
        $resourceInfo = $this->pGetResourceGroupResource($resourceGroupUUID, $resourceType);
        $haveNode = array();
        if(!empty($resourceInfo)){
            foreach ($resourceInfo as $res){
                $haveNode[] = $res['resource_uuid'];
            }
        }
        
        //整理需要加的节点
        $resourceInfo=  array();
        foreach ($nodes as $node){
            if(!in_array($node, $haveNode)){
                $resourceInfo[] = array(
                    'resourceuuid' => $node,
                    'vmuuid' => "",
                    'vcenteruuid' => "",
                    'resourceType' => $resourceType
                );
            }
        }
        
        if(!empty($resourceInfo)){
            //调用添加资源组与资源关联公共方法
            $userHandler = Xphp::instance("UsersHandler");
            $userHandler->pAddResourceGroupResource($resourceGroupUUID, $resourceInfo);
        }
        
        return;
    }
    
    
    /**
     * 添加存储关联时增加用户和节点强关联
     * @param unknown $userUUID
     * @param unknown $resourceList
     */
    public function addUserStorageNode($userUUID, $resourceList){
        if(empty($resourceList)) return;
        $list = array();
        foreach ($resourceList as $res){
            $list[] = $res['resourceuuid'];
        }
        $listDes = implode("','", $list);
        //获取存储所在节点uuid
        $sql = "select distinct node_uuid from bd_storage_resource where storage_uuid in ('".$listDes."')";
        $data = $this->dbSelect($sql);
        
        $nodes = array();
        if(!empty($data)){
            foreach ($data as $d){
                $nodes[] = $d['node_uuid'];
            }
        }
        //节点资源类型
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['NODE'];
        //根据资源组UUID获取到已关联的节点UUID集合
        $resourceInfo = $this->pGetUserAllResource($userUUID, $resourceType);
        $haveNode = array();
        if(!empty($resourceInfo)){
            foreach ($resourceInfo as $res){
                $haveNode[] = $res['resource_uuid'];
            }
        }
        
        //整理需要加的节点
        $resourceInfo=  array();
        foreach ($nodes as $node){
            if(!in_array($node, $haveNode)){
                $resourceInfo[] = array(
                    'resourceuuid' => $node,
                    'vmuuid' => "",
                    'vcenteruuid' => "",
                    'resourceType' => $resourceType
                );
            }
        }
        
        if(!empty($resourceInfo)){
            //调用添加资源组与资源关联公共方法
            $userHandler = Xphp::instance("UsersHandler");
            $userHandler->pAddUserResource($userUUID, $resourceInfo);
        }
        
        return;
    }
    
    /**
     * 添加存储关联时增加用户组和节点强关联
     * @param unknown $userGroupUUID
     * @param unknown $resourceList
     */
    public function addUserGroupStorageNode($userGroupUUID, $resourceList){
        if(empty($resourceList)) return;
        $list = array();
        foreach ($resourceList as $res){
            $list[] = $res['resourceuuid'];
        }
        $listDes = implode("','", $list);
        //获取存储所在节点uuid
        $sql = "select distinct node_uuid from bd_storage_resource where storage_uuid in ('".$listDes."')";
        $data = $this->dbSelect($sql);
        
        $nodes = array();
        if(!empty($data)){
            foreach ($data as $d){
                $nodes[] = $d['node_uuid'];
            }
        }
        //节点资源类型
        $resourceType = Xphp::$_config['RESOURCE_TYPE']['NODE'];
        //根据资源组UUID获取到已关联的节点UUID集合
        $resourceInfo = $this->pGetUserGroupAllResource($userGroupUUID, $resourceType);
        $haveNode = array();
        if(!empty($resourceInfo)){
            foreach ($resourceInfo as $res){
                $haveNode[] = $res['resource_uuid'];
            }
        }
        
        //整理需要加的节点
        $resourceInfo=  array();
        foreach ($nodes as $node){
            if(!in_array($node, $haveNode)){
                $resourceInfo[] = array(
                    'resourceuuid' => $node,
                    'vmuuid' => "",
                    'vcenteruuid' => "",
                    'resourceType' => $resourceType
                );
            }
        }
        
        if(!empty($resourceInfo)){
            //调用添加资源组与资源关联公共方法
            $userHandler = Xphp::instance("UsersHandler");
            $userHandler->pAddUserGroupResource($userGroupUUID, $resourceInfo);
        }
        
        return;
    }

    /**
     * 公共方法
     * 获取用户自身创建的资源
     * @param string $userUuid     用户uuid
     * @param int    $resourceType 资源类型
     * @param string $back         返回类型 默认sql
     * @param bool   $allocation   默认不去除分配的资源
     * @param string $userType     请求useruuid类型，是某个uuid还是sql语句的结果，就是需要in
     * @return array|string
     */
    public function pGetUserCreateResource(string $userUuid = '', $resourceType = 0, $back = 'sql',  $allocation = false, $userType = '')
    {

        // 定义的分配的资源的类型
        $resourceTypes = Xphp::$_config['RESOURCE_DIS_TYPE'];
        if (empty($resourceTypes)) {
            return $back == 'sql' ? '' : [];
        }
        if (empty($userUuid)) {
            $userUuid = Xphp::$_user['useruuid'];
        }
        $not = ' NOT IN (
                SELECT resource_uuid
                FROM mt_user_resource
                where resource_type != 0
                UNION
                SELECT resource_uuid
                FROM mt_resource_resource_group
                where resource_type != 0
              )';
        $sqlArr = [];
        $userSql = $userType == 'sql' ? "user_uuid in ($userUuid)" : "user_uuid = '{$userUuid}'";
        foreach ($resourceTypes as $key => $item) {
            switch ($key) {
                case 3: // 虚拟机
                    $vmConfig = Xphp::$_config['VMHYPERVISORGROUP'];
                    $sqls = "select vt.uuid from vm_tree vt, vm_vcenter vv
                                                where vt.vcenter_uuid = vv.vcenter_uuid and vv.{$userSql}
                                                and vv.hypervisor_type not in
                                                    (" . implode(',', $vmConfig['publiccloud']) . ')
                                                    and vv.hypervisor_type not in
                                                     (' . implode(',', $vmConfig['privatecloud']) . ')';
                    if ($allocation) {
                        $sqls .= ' and vt.uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 8: // 存储资源
                    $flag = Xphp::$_config['FLAG']['UNSET'];
                    $sqls = "select storage_uuid as uuid from bd_storage_resource 
                                        where lan_free_flag = {$flag} and use_mode not in (2,3)
                                          and {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and storage_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 10: // 客户端
                    $agentType = Xphp::$_config['AGENT_TYPE'];
                    $sqls = "select ba.agent_uuid as uuid from bd_agent ba
                                        where ba.agent_type not in ({$agentType['NAS']}, {$agentType['APPLIANCE']})
                                          and ba.{$userSql}";
                    if ($allocation) {
                        $sqls .= ' and ba.agent_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 2: // 传输代理
                    $agentType = Xphp::$_config['AGENT_TYPE'];
                    $sqls = "select ba.agent_uuid as uuid from bd_agent ba
                                        where ba.agent_type = {$agentType['APPLIANCE']}
                                          and ba.{$userSql}";
                    if ($allocation) {
                        $sqls .= ' and ba.agent_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 56: // 组织管理
                    $sqls = "select organization_uuid as uuid from m365_organization
                                            where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and organization_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 57: // nas设备
                    $sqls = "select nas_uuid as uuid from nas_storage_resource where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and nas_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 58: // 公有云平台
                    $vmConfig = Xphp::$_config['VMHYPERVISORGROUP'];
                    $sqls = "select vt.uuid from vm_tree vt, vm_vcenter vv
                                                where vt.vcenter_uuid = vv.vcenter_uuid and vv.{$userSql}
                                                and vv.hypervisor_type in
                                                    (" . implode(',', $vmConfig['publiccloud']) . ')';
                    if ($allocation) {
                        $sqls .= ' and vt.uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 59: // 对象存储
                    $sqls = "select obs_uuid as uuid from obs_resource where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and obs_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 60: // Hadoop集群
                    $sqls = "select hadoop_cluster_uuid as uuid
                                    from hadoop_cluster where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and hadoop_cluster_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 61: // 私有云
                    $vmConfig = Xphp::$_config['VMHYPERVISORGROUP'];
                    $sqls = "select vt.uuid from vm_tree vt, vm_vcenter vv
                                                where vt.vcenter_uuid = vv.vcenter_uuid and vv.{$userSql}
                                                and vv.hypervisor_type in
                                                    (" . implode(',', $vmConfig['privatecloud']) . ')';
                    if ($allocation) {
                        $sqls .= ' and vt.uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
                case 62: // k8s集群
                    $sqls = "select cluster_uuid as uuid
                                    from kube_cluster where {$userSql}";
                    if ($allocation) {
                        $sqls .= ' and cluster_uuid ' . $not;
                    }
                    $sqlArr[$key] = $sqls;
                    break;
            }
        }

        $sql = '';
        if (empty($resourceType)) {
            // 查询所有的
            $sql = implode(' union all ', array_values($sqlArr));
        } elseif (!empty($sqlArr[$resourceType])) {
            $sql = $sqlArr[$resourceType];
        }
        if ($back != 'sql') {
            return empty($sql) ? [] : $this->dbSelect($sql);
        }
        return $sql;
    }

    /**
     * 公共方法
     * 得到用户所有资源uuid sql
     * 用户-资源
     * 用户-资源组-资源
     * 用户-用户组-资源
     * 用户-用户组-资源组-资源
     * @param string $useruuid     用户uuid
     * @param int    $resourceType 资源类型(可带可不带,带资源类型就是查找对应资源类型的资源,对应配置文件RESOURCE_TYPE)
     * @param string $userType     请求useruuid类型，是某个uuid还是sql语句的结果，就是需要in
     * @param string $field        如果带入了主表的主键，那么就会直接返回一个完整的where条件，不需要主表再拼一次
     * @return string  资源UUID的sql集合
     */
    public function pGetUserAllResourceSql($useruuid, $resourceType = '', $userType = '', $field = '')
    {

        //如果按类型查找
        if ($userType == 'sql') {
            $sqlMore = " and bu.user_uuid in ({$useruuid})";
        } else {
            $sqlMore = " and bu.user_uuid = '{$useruuid}'";
        }
        if (!empty($resourceType)) {
            $sqlMore .= ' and resource_type = ' . $resourceType;
        }
        $sqls = [];
        //用户-资源
        $sql = "select distinct mur.resource_uuid uuid from 
                bd_user bu, mt_user_resource mur 
                where bu.user_uuid = mur.user_uuid";
        $sql .= $sqlMore;
        $sqls[] = $sql;

        //用户-资源组-资源
        $sql = "select distinct mrrg.resource_uuid uuid from
                bd_user bu, mt_user_resource_group murg, mt_resource_resource_group mrrg 
                where bu.user_uuid = murg.user_uuid and murg.resource_group_uuid = mrrg.resource_group_uuid";
        $sql .= $sqlMore;
        $sqls[] = $sql;

        //用户-用户组-资源
        $sql = "select distinct mugr.resource_uuid uuid from
                bd_user bu, bd_user_group bug, mt_user_user_group muug, mt_user_group_resource mugr
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugr.user_group_uuid
                  and bug.user_group_uuid = mugr.user_group_uuid and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $sqls[] = $sql;

        //用户-用户组-资源组-资源
        $sql = "select distinct mrrg.resource_uuid uuid from
                bd_user bu, bd_user_group bug, mt_user_user_group muug,
                mt_user_group_resource_group mugrg,mt_resource_resource_group mrrg 
                where bu.user_uuid = muug.user_uuid and muug.user_group_uuid = mugrg.user_group_uuid
                  and bug.user_group_uuid = mugrg.user_group_uuid and
                mugrg.resource_group_uuid = mrrg.resource_group_uuid and bug.lock_flag = 1 ";
        $sql .= $sqlMore;
        $sqls[] = $sql;

        // 需要把自己创建的这些资源都给包含进来
        if (!empty($resourceType)) {
            // 查询某一项的
            $sqls[] = $this->pGetUserCreateResource($useruuid, $resourceType, 'sql', true, $userType);
        } else {
            $resourceTypes = Xphp::$_config['RESOURCE_DIS_TYPE'];
            foreach ($resourceTypes as $key => $item) {
                $sqls[] = $this->pGetUserCreateResource($useruuid, $key, 'sql', true, $userType);
            }
        }
        $sqls = array_filter($sqls);
        $return = '(' . implode(' union all ', $sqls) . ')';
        if (!empty($field)) {
            $return = " EXISTS (select uuid from ({$return}) v1_auth_all where v1_auth_all.uuid = {$field})";
        }
        return $return;
    }


}
?>