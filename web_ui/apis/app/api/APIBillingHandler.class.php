<?php
/******************************************* 
** 计费处理类
** 
** @author       luokai@vinchin.com 
** @date         2022-04-14 
** @version      1.0.0 
** @copyright    Copyright 2022 vinchin.com 
********************************************/
class APIBillingHandler extends OPHandler{
    
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/billing" => array(
            'POST' => 'createBillStra',     //新建计费策略(单)
            'PUT' => 'editBillStra',        //修改计费策略(单)
            'DELETE' => 'deleteBillStra',   //删除计费策略(1-n)
            'GET' => 'getBillStraInfo'      //获取单个计费策略详细信息(单)
        ),
        "/billing/lock" => array(
            'POST' => 'lockBillStra'     //禁用计费策略(1-n)
        ),
        "/billing/unlock" => array(
            'POST' => 'unlockBillStra'     //启用计费策略(1-n)
        ),
        "/billing/allocate_tenant" => array(
            'POST' => 'allocateTenant',     //分配租户集合到计费策略(多对1)
            'GET' => 'getTenantUnallocatedList'  //得到未分配的租户集合
        ),
        "/billing/tenant_renew" => array(
            'POST' => 'renewBilling'     //租户使用计费策略续费(单)
        ),
        "/billing/cancel_tenant" => array(
            'DELETE' => 'cancelBillTenant'     //取消指定计费与指定租户的关联(单)
        ),
        "/billing/money_unit" => array(
            'GET' => 'getMoneyUnit'     //获取货币单位集合(全部)
        ),
        "/billing/tenant_lists" => array(
            'GET' => 'getBillTenantList'     //获取计费策略下的租户列表(1-n)
        ),
        "/billing/lists" => array(
            'GET' => 'getBillStraLists'     //获取计费策略列表(1-n)
        ),
        "/billing/tenant_details" => array(
            'GET' => 'getTenantBillDetails'     //获取租户使用费用详情(单)
        ),
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 创建计费策略路由控制
     */
    protected function createBillStra(){
        //定义方法版本
        $version = array(
            "v1" => "createBillStraV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改计费策略路由控制
     */
    protected function editBillStra(){
        //定义方法版本
        $version = array(
            "v1" => "editBillStraV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除计费策略路由控制
     */
    protected function deleteBillStra(){
        //定义方法版本
        $version = array(
            "v1" => "deleteBillStraV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取计费策略信息(单个)路由控制
     */
    protected function getBillStraInfo(){
        //定义方法版本
        $version = array(
            "v1" => "getBillStraInfoV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 禁用计费策略路由控制
     */
    protected function lockBillStra(){
        //定义方法版本
        $version = array(
            "v1" => "lockBillStraV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 启用计费策略路由控制
     */
    protected function unlockBillStra(){
        //定义方法版本
        $version = array(
            "v1" => "unlockBillStraV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 分配指定计费策略到租户集合路由控制
     */
    protected function allocateTenant(){
        //定义方法版本
        $version = array(
            "v1" => "allocateTenantV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 分配指定计费策略到租户集合路由控制
     */
    protected function getTenantUnallocatedList(){
        //定义方法版本
        $version = array(
            "v1" => "getTenantUnallocatedListV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    
    /**
     *租户使用续费路由控制
     */
    protected function renewBilling(){
        //定义方法版本
        $version = array(
            "v1" => "renewBillingV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 取消指定计费策略和租户关联路由控制
     */
    protected function cancelBillTenant(){
        //定义方法版本
        $version = array(
            "v1" => "cancelBillTenantV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取货币单位集合(多个)路由控制
     */
    protected function getMoneyUnit(){
        //定义方法版本
        $version = array(
            "v1" => "getMoneyUnitV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取计费策略下的租户列表(多个)路由控制
     */
    protected function getBillTenantList(){
        //定义方法版本
        $version = array(
            "v1" => "getBillTenantListV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取计费策略列表(多个)路由控制
     */
    protected function getBillStraLists(){
        //定义方法版本
        $version = array(
            "v1" => "getBillStraListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取租户使用费用详情路由控制
     */
    protected function getTenantBillDetails(){
        //定义方法版本
        $version = array(
            "v1" => "getTenantBillDetailsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**********createBillStra**********/
    private function createBillStraV1(){
        //获取数据        
        $billing_name = $this->params['billing_name'];
        $this->apiParamsCheck($billing_name);
        $billing_description = $this->params['billing_description'];
        $billing_type = $this->params['billing_type'];
        $monetary_unit = $this->params['monetary_unit'][0];
        if(!$monetary_unit){
            $monetary_unit="";
        }
        $billing_num_type = $this->params['billing_num_type'];
        
        $num_selected1 = json_encode($this->params['num_selected']);
        $num_selected="'".$num_selected1."'";
        
        $billing_vm = $this->params['billing_vm'];
        if(!$billing_vm){
            $billing_vm="";
        }
        $billing_fs = $this->params['billing_fs'];
        if(!$billing_fs){
            $billing_fs="";
        }
        $billing_db = $this->params['billing_db'];
        if(!$billing_db){
            $billing_db="";
        }
        $billing_capacity = $this->params['billing_capacity'];
        if(!$billing_capacity){
            $billing_capacity="";
        }
        $inform = $this->params['inform'];
        $inform_period = $this->params['inform_period'];
        //生成一个UUID
        $utils = Xphp::instance("Utils");
        $billingUUID = $utils->uuid();
        //时间
        $c_time = date("Y-m-d H:i:s");
        $lock_flag = 1;
        $config = "";
        //把单价存为json格式
        $unit_cost1 = json_encode(array(
            "billing_vm" => $billing_vm,
            "billing_fs" => $billing_fs,
            "billing_db" => $billing_db,
            "billing_capacity" => $billing_capacity,
        ));
        $unit_cost="'".$unit_cost1."'";
        $sql = "insert into bd_billing (billing_uuid,name,description,monetary_unit,billing_type,billing_mode,
        num_type,unit_cost,inform,inform_period,config,lock_flag,create_time) values (?,?,?,?,?,?,$num_selected,$unit_cost,?,?,?,?,?)";
        $sql_params = array(
            $billingUUID,
            $billing_name,
            $billing_description,
            $monetary_unit,
            $billing_type,
            $billing_num_type,
            //$num_selected
            //$unit_cost
            $inform,
            $inform_period,
            $config,
            $lock_flag,
            $c_time,
        );
        $result = $this->dbQuery($sql,$sql_params);
        if($result){
            $result = true;
        }else{
            $result = false;
        }
        $data = array();
        $data['billing_uuid'] =  $billingUUID;
        return $this->apiResponse($result, "API_CODE_BILLING_ADD_STRATEGY", $data, array());
    }
    
    /**********editBillStra**********/
    private function editBillStraV1(){
        $billingUUID = $this->params['billing_uuid'];
        $this->apiParamsCheck($billingUUID);
        $billing_name = $this->params['billing_name'];
        $billing_description = $this->params['billing_description'];
        $billing_type = $this->params['billing_type'];
        $monetary_unit = $this->params['monetary_unit'][0];
        if(!$monetary_unit){
            $monetary_unit="";
        }
        $billing_num_type = $this->paramsams['billing_num_type'];
        
        $num_selected1 = json_encode($this->paramsms['num_selected']);
        $num_selected="'".$num_selected1."'";
        
        $billing_vm = $this->params['billing_vm'];
        if(!$billing_vm){
            $billing_vm="";
        }
        $billing_fs = $this->params['billing_fs'];
        if(!$billing_fs){
            $billing_fs="";
        }
        $billing_db = $this->params['billing_db'];
        if(!$billing_db){
            $billing_db="";
        }
        $billing_capacity = $this->params['billing_capacity'];
        if(!$billing_capacity){
            $billing_capacity="";
        }
        $inform = $this->params['inform'];
        $inform_period = $this->params['inform_period'];
        //把单价存为json格式
        $unit_cost1 = json_encode(array(
            "billing_vm" => $billing_vm,
            "billing_fs" => $billing_fs,
            "billing_db" => $billing_db,
            "billing_capacity" => $billing_capacity,
        ));
        $unit_cost="'".$unit_cost1."'";
        $sql ="update bd_billing set name = ?,description = ?,monetary_unit = ?,billing_type = ?,
        billing_mode = ?,num_type = $num_selected,unit_cost = $unit_cost,inform = ?,inform_period = ? where billing_uuid = ?";
        $sql_params = array($billing_name,$billing_description,$monetary_unit,$billing_type,$billing_num_type,$inform,$inform_period,$billingUUID);
        $result = $this->dbExec($sql,$sql_params);
        if($result){
            $this->getBillingTotalAgain($billingUUID);
        }
        return $this->apiResponse($result, "API_CODE_BILLING_EDIT_STRATEGY", array(), array());
    }
    
    /**********deleteBillStra**********/
    private function deleteBillStraV1(){
        $uuid_list = $this->params['uuid_list'];
        $this->apiParamsCheck($uuid_list);
        $string = implode("','",$uuid_list);
        $sql = "delete from bd_billing where billing_uuid in";
        $sql .=" ("."'"."$string"."'".")";
        $result = $this->dbExec($sql);
        //删除策略组里用户费用详情
        $sql_teuuid = "select tenant_uuid from mt_tenant_billing where billing_uuid in";
        $sql_teuuid .=" ("."'"."$string"."'".")";
        $result_teuuid = $this->dbSelect($sql_teuuid);
        $tenent_uuid_list = array();
        foreach ($result_teuuid as $op){
            $tenent_uuid_list[] = $op['tenant_uuid'];
        }
        $string_tenant_list = implode("','",$tenent_uuid_list);
        $sql_details = "delete from bd_billing_details where tenant_uuid in";
        $sql_details .=" ("."'"."$string_tenant_list"."'".")";
        $result_details = $this->dbExec($sql_details);
        //删除关联数据
        $sql_mt = "delete from mt_tenant_billing where billing_uuid in";
        $sql_mt .=" ("."'"."$string"."'".")";
        $result_mt = $this->dbExec($sql_mt);
        
        if($result && $result_mt && $result_details){
            return $this->apiResponse(true, "API_CODE_BILLING_DELETE_STRATEGY", array(), array());
        }else{
            return $this->apiResponse(false, "API_CODE_BILLING_DELETE_STRATEGY", array(), array());
        }
    }
    
    /**********getBillStraInfo**********/
    private function getBillStraInfoV1(){
        $billing_uuid = $this->params['billing_uuid'];
        $this->apiParamsCheck($billing_uuid);
        $sql = "select name,description,billing_type,billing_mode,num_type,monetary_unit,unit_cost,inform,inform_time,inform_period from bd_billing where billing_uuid = ?";
        $result = $this->dbSelect($sql,array($billing_uuid));
        $info = array();
        foreach ($result as $op){
            $info = array(
                'name' => $op['name'],
                'description' => $op['description'],
                'billing_type' => intval($op['billing_type']),
                'billing_mode' => intval($op['billing_mode']),
                'num_type' => json_decode($op['num_type'],true),
                'monetary_unit' => intval($op['monetary_unit']),
                'unit_cost' => json_decode($op['unit_cost'],true),
                'inform' => intval($op['inform']),
//                 'inform_time' => strtotime($op['inform_time']),
                'inform_period' => intval($op['inform_period']),
                'MonetaryStr' => $this->getMonetaryUnitStr($billing_uuid),
            );
        }
        return $this->apiResponse(true, "API_CODE_BILLING_GET_STRATEGY_INFO", $info, array());
    }
    
    /**********lockBillStra**********/
    private function lockBillStraV1(){
        $uuid_list = $this->params['uuid_list'];
        $this->apiParamsCheck($uuid_list);
        $string = implode("','",$uuid_list);
        //先检查是否有已经启用的
        $check_sql = "select count(billing_uuid) as check_count, billing_uuid from bd_billing where lock_flag=2 and billing_uuid in";
        $sql = "update bd_billing set lock_flag=2 where billing_uuid in";
        $check_sql .=" ("."'"."$string"."'".")";
        $sql .=" ("."'"."$string"."'".")";
        $result_check = $this->dbSelect($check_sql);
        $count_check_num = $result_check[0]['check_count'];
        $info = array();
        if($count_check_num != 0){
            $checkData = array();
            foreach ($result_check as $op){
                $checkData[] = $op['billing_uuid'];
            }
            return $this->apiResponse(false, "API_CODE_BILLING_LOCK_STRATEGY", $checkData, array());
        }
        $result = $this->dbQuery($sql);
        if($result){
            return $this->apiResponse(true, "API_CODE_BILLING_LOCK_STRATEGY", $info, array());
        }else{
            return $this->apiResponse(false, "API_CODE_BILLING_LOCK_STRATEGY", $info, array());
        }
    }
    
    /**********unlockBillStra**********/
    private function unlockBillStraV1(){
        $uuid_list = $this->params['uuid_list'];
        $this->apiParamsCheck($uuid_list);
        $string = implode("','",$uuid_list);
        //先检查是否有已经启用的
        $check_sql = "select count(billing_uuid) as check_count, billing_uuid from bd_billing where lock_flag=1 and billing_uuid in";
        $sql = "update bd_billing set lock_flag=1 where billing_uuid in";
        $check_sql .=" ("."'"."$string"."'".")";
        $sql .=" ("."'"."$string"."'".")";
        $result_check = $this->dbSelect($check_sql);
        $count_check_num = $result_check[0]['check_count'];
        $info = array();
        if($count_check_num != 0){
            $checkData = array();
            foreach ($result_check as $op){
                $checkData[] = $op['billing_uuid'];
            }
            return $this->apiResponse(false, "API_CODE_BILLING_UNLOCK_STRATEGY", $checkData, array());
        }
        $result = $this->dbQuery($sql);
        if($result){
            return $this->apiResponse(true, "API_CODE_BILLING_UNLOCK_STRATEGY", $info, array());
        }else{
            return $this->apiResponse(false, "API_CODE_BILLING_UNLOCK_STRATEGY", $info, array());
        }
    }
    
    /**********allocateTenant**********/
    private function allocateTenantV1(){
        $tenant_uuid_list = $this->params['tenant_uuid_list'];
        $this->apiParamsCheck($tenant_uuid_list);
        $billing_uuid = $this->params['billing_uuid'];
        $billing_type = $this->params['billing_type_get'];
        $create_time = date("Y-m-d H:i:s");
        $billing_total = $this->params['billing_total'];
        
        $this->paramsCheck($tenant_uuid_list);
        $num_capacity = $this->params['num_capacity'];
        $num_vm = $this->params['num_vm'];
        $num_fs = $this->params['num_fs'];
        $num_db = $this->params['num_db'];
        $billing_time = $this->params['billing_time'];
        
        //拼接输入的数量为json
        $use_num = json_encode(array(
            "num_vm" => $num_vm,
            "num_fs" => $num_fs,
            "num_db" => $num_db,
        ));
        
        //先在租户和费用关联表中插入数据
        $val = array();
        foreach ($tenant_uuid_list as $one_uuid){
            $val[]= "('".$one_uuid."','".$billing_uuid."','".$create_time."')";
        }
        $string_val = implode(",",$val);
        $sql_relation = "insert into mt_tenant_billing (tenant_uuid,billing_uuid,create_time) values ";
        
        $sql_relation .= $string_val;
        $result_relation = $this->dbQuery($sql_relation);
        if(!$result_relation){
            return $this->apiResponse(false, "API_CODE_BILLING_ALLOCATE_STRATEGY_TENANT", array(), array());
        }
        
        //判断是否为包月包年,如果是包月包年执行生成订单,如果不是则退出
        if($billing_type ==1 || $billing_type ==2){
            //根据类型不同创建不同的到期时间
            //如果为包月则单位为month   如果为包年,则单位为year
            if($billing_type ==1){
                $end_time = date("Y-m-d H:i:s", strtotime("$create_time +$billing_time month"));
            }else{
                $end_time = date("Y-m-d H:i:s", strtotime("$create_time +$billing_time year"));
            }
            
            
            $val_insert = array();
            foreach ($tenant_uuid_list as $one_tenant_uuid){
                $val_insert[] = "('".$one_tenant_uuid."','".$billing_uuid."','".$billing_type."','".$create_time."','".$end_time."','".$use_num."','".$num_capacity."','".$billing_total."')";
            };
            $string_val_insert = implode(",",$val_insert);
            $sql_order = "insert into bd_billing_details (tenant_uuid,billing_uuid,billing_type,create_time
,end_time,use_num,capacity_size,billing_total) values ";
            $sql_order .= $string_val_insert;
            $result_order = $this->dbQuery($sql_order);
            if($result_order){
                return $this->apiResponse(true, "API_CODE_BILLING_ALLOCATE_STRATEGY_TENANT", array(), array());
            }else{
                return $this->apiResponse(false, "API_CODE_BILLING_ALLOCATE_STRATEGY_TENANT", array(), array());
            }
            
        }
        if($result_relation){
            return $this->apiResponse(true, "API_CODE_BILLING_ALLOCATE_STRATEGY_TENANT", array(), array());
        }else{
            return $this->apiResponse(false, "API_CODE_BILLING_ALLOCATE_STRATEGY_TENANT", array(), array());
        }
        
        
    }
    /**********getTenantUnallocatedList**********/
    private function getTenantUnallocatedListV1(){
        //联合租户表以及租户费用关联表,查询没有分配策略的租户
        $sql = "select b.tenant_uuid as tenant_uuid,b.tenant_name as tenant_name from bd_tenant as b left join mt_tenant_billing as t on b.tenant_uuid = t.tenant_uuid where t.tenant_uuid is null";
        $result = $this->dbSelect($sql);
        $info = array();
        foreach ($result as $op){
            $info[]= array(
                'tenant_uuid' => $op['tenant_uuid'],
                'tenant_name' => $op['tenant_name'],
            );
        };
        return $this->apiResponse(true, "API_CODE_BILLING_GET_TENANT_UNALLOCATED", $info, array());
        
    }
    
    /**********renewBilling**********/
    private function renewBillingV1(){
        $tenant_uuid = $this->params['tenant_uuid'];
        $this->apiParamsCheck($tenant_uuid);
        $new_time = $this->params['new_time'];
        $new_time = date("Y-m-d H:i:s",$new_time);
        $now_time = date("Y-m-d H:i:s");
        $new_total = $this->params['new_total'];
        //复制最新的一条数据然后插入新数据并更新时间
        $sql = "insert into bd_billing_details(tenant_uuid,billing_uuid,billing_type,create_time
,end_time,update_time,use_num,capacity_size,billing_total) select tenant_uuid,billing_uuid,billing_type,create_time,? as end_time,? as update_time,use_num,capacity_size,
? as billing_total from bd_billing_details where tenant_uuid = ? order by update_time desc limit 1";
        $params = array($new_time,$now_time,$new_total,$tenant_uuid);
        $result = $this->dbQuery($sql,$params);
        if($result){
            return $this->apiResponse(true, "API_CODE_BILLING_TENANT_RENEW", array(), array());
        }else{
            return $this->apiResponse(false, "API_CODE_BILLING_TENANT_RENEW", array(), array());
        }
    }
    
    /**********cancelBillTenant**********/
    private function cancelBillTenantV1(){
        $tenant_uuid = $this->params['tenant_uuid'];
        $this->apiParamsCheck($tenant_uuid);
        //先删除有关租户的费用详细表
        $sql_delete_details = "delete from bd_billing_details where tenant_uuid = ?";
        $result_delete_details = $this->dbQuery($sql_delete_details,array($tenant_uuid));
        
        //删除租户费用关联表
        $sql_relation = "delete from mt_tenant_billing where tenant_uuid = ?";
        $result_relation = $this->dbQuery($sql_relation,array($tenant_uuid));
        //           if($result_relation){
        //               return $this->muOpResult(true,"删除已分配租户");
        //           }else{
        //               return $this->muOpResult(false,"删除已分配租户","sadda","warning");
        //           }
        if($result_relation){
            return $this->apiResponse(true, "API_CODE_BILLING_DELETE_STRATEGY_TENANT", array(), array());
        }else{
            return $this->apiResponse(false, "API_CODE_BILLING_DELETE_STRATEGY_TENANT", array(), array());
        }
    }
    
    /**********getMoneyUnit**********/
    private function getMoneyUnitV1(){
        $unit = include APP_PATH . "platform/MonetaryUnitArray.php";
        $list = $unit['MONETARY_UNIT'];
        return $this->apiResponse(true, "API_CODE_BILLING_GET_MONEY_UNIT_LIST", $list, array());
    }
    
    /**********getBillTenantList**********/
    private function getBillTenantListV1(){
        $start = $this->params['begin'];
        $length = $this->params['count'];
        $billing_uuid = $this->params['billing_uuid'];
        $this->apiParamsCheck($billing_uuid);
        //通过billing_uuid获取到费用策略的信息
        $sql_billing = "select billing_type,billing_mode,num_type from bd_billing where billing_uuid = ?";
        $result_billing = $this->dbSelect($sql_billing,array($billing_uuid));
        $billing_type = $result_billing[0]['billing_type'];
        $billing_mode = $result_billing[0]['billing_mode'];
        $billing_num_type = json_decode($result_billing[0]['num_type'],true);
        $MonetaryStr = $this->getMonetaryUnitStr($billing_uuid);
        
        //得到此策略下所有的租户
        $sql_tenant_uuid_list = "select tenant_uuid from mt_tenant_billing where billing_uuid = ? limit ?,?";
        $resilt_tenant_uuid_list = $this->dbSelect($sql_tenant_uuid_list,array($billing_uuid,$start,$length));
        $tenant_uuid_list = array();
        foreach ($resilt_tenant_uuid_list as $op){
            $tenant_uuid_list[] = $op['tenant_uuid'];
        };
        $sql_count = "select count(id) as count_all from mt_tenant_billing where billing_uuid = ?";
        $result_count = $this->dbSelect($sql_count,array($billing_uuid));
        $count = $result_count[0]['count_all'];
        //循环获取每个租户的信息
        $info['records'] = array();
        foreach ($tenant_uuid_list as $tenant_uuid){
            //先判断是按数量还是按容量
            $size = $this->pGetTenantCapacity($tenant_uuid);
            $num_all = $this->pGetTenantUseNum($tenant_uuid);
            
            $sql_details ="select create_time,end_time,billing_total,use_num,capacity_size from bd_billing_details where tenant_uuid =? order by update_time desc limit 1";
            $resilt_details = $this->dbSelect($sql_details,array($tenant_uuid));
            $start_time = $resilt_details[0]['create_time'];
            $end_time = $resilt_details[0]['end_time'];
            $total_billing = $resilt_details[0]['billing_total'];
            if(empty($total_billing)){
                $total_billing = 0;
            }
            $use_num = json_decode($resilt_details[0]['use_num'],true);
            $capacity_size = $resilt_details[0]['capacity_size'];
            
            //查询开始日期和结束日期和费用总计--只针对包月包年情况
            if($billing_type ==3){
                $params_tenant = array(
                    'tenant_uuid' => $tenant_uuid
                );
                //先得到租户的一些信息
                $date_list = $this->getMsgOfTenant($params_tenant);
                $tenant_info = json_decode($date_list,true);
                
                $start_time = $tenant_info['start_time'];
                $end_time = $tenant_info['end_time'];
                $total_billing = $tenant_info['billing_totalNum'];
                if(empty($total_billing)){
                    $total_billing = 0;
                }
            }
            
            
            //得到租户的名字
            $sql_tenant_name = "select tenant_name from bd_tenant where tenant_uuid = ?";
            $result_tenant_name = $this->dbSelect($sql_tenant_name,array($tenant_uuid));
            $tenant_name = $result_tenant_name[0]['tenant_name'];
            
            $info['records'][] = array(
                'tenant_name' =>$tenant_name,
                'int_size' => $size,
                'num_all' => $num_all,
                'start_time' => intval(strtotime($start_time)),
                'end_time' => intval(strtotime($end_time)),
                'total_billing' => floatval($total_billing),
                'MonetaryStr' => $MonetaryStr,
                'tenant_uuid' => $tenant_uuid,
                'use_num' => $use_num,
                'capacity_size' => $capacity_size,
            );
        }
        $info["total"] = $count;
        $info["begin"] = $start;
        $info["count"] = $length;
        
        return $this->apiResponse(true, "API_CODE_BILLING_GET_STRATEGY_TENANT_LIST", $info, array());
    }
    
    /**********getBillStraLists**********/
    private function getBillStraListsV1(){
        $start = $this->params['begin'];
        $length = $this->params['count'];
        $sql = "select name,description,billing_uuid,billing_type,create_time,inform,inform_period,lock_flag,billing_mode, num_type, unit_cost, create_time from bd_billing limit ?,?";
        $sqlParams =  array($start,$length);
        $result = $this->dbSelect($sql, $sqlParams);
        $sql_count = "select count(id) as count_all from bd_billing";
        $result_count = $this->dbSelect($sql_count);
        $count = intval($result_count[0]['count_all']);
        $info['records'] = array();
        foreach ($result as $op){
            $info['records'][] = array(
                'billing_uuid' => $op['billing_uuid'],
                'name' => $op['name'],
                'description' => $op['description'],
                'billing_type' => $op['billing_type'],
                'create_time' => strtotime($op['create_time']),
                'inform' => $op['inform'],
                'inform_period' => $op['inform_period'],
                'lock_flag' => $op['lock_flag'],
                'num_type' => json_decode($op['num_type'],true),
                'unit_cost' => json_decode($op['unit_cost'],true),
            );
        }
        
        $info["total"] = $count;
        $info["begin"] = $start;
        $info["count"] =$length;
        return $this->apiResponse(true, "API_CODE_BILLING_GET_STRATEGY_LIST", $info, array());
    }
    
    /**********getTenantBillDetails**********/
    private function getTenantBillDetailsV1(){
        $start = $this->params['begin'];
        $length = $this->params['count'];
        $tenant_uuid = $this->params['tenant_uuid'];
        $this->apiParamsCheck($tenant_uuid);
        $sql_details = "select billing_type,billing_uuid from bd_billing_details where tenant_uuid = ? order by update_time desc limit 1";
        $resule_details = $this->dbSelect($sql_details,array($tenant_uuid));
        //如果没有查询到值 ,可能每天计费时,第一条还没有生成订单,直接返回空即可
        if(empty($resule_details)){
            $info['records'] = array();
            $info["total"] = 0;
            $info["begin"] = $start;
            $info["count"] = $length;
            return $this->apiResponse(true, "API_CODE_BILLING_GET_TENANT_BILL_DETAIL", $info, array());
        }
        //得到当前租户使用的费用策略
        $billing_uuid = $resule_details[0]['billing_uuid'];
        $billing_type = $resule_details[0]['billing_type'];
        $sql_billing = "select billing_mode,num_type from bd_billing where billing_uuid = ?";
        $result_billing = $this->dbSelect($sql_billing,array($billing_uuid));
        $billing_mode = $result_billing[0]['billing_mode'];
        
        $num_type = $result_billing[0]['num_type'];
        
        $sql_one_details = "select create_time,end_time,billing_uuid,billing_total,capacity_size,use_num  from bd_billing_details where tenant_uuid = ? order by update_time desc limit ?,?";
        $resule_one_details = $this->dbSelect($sql_one_details,array($tenant_uuid,$start,$length));
        $sql_count = "select count(id) as count_all from bd_billing_details where tenant_uuid = ?";
        $result_count = $this->dbSelect($sql_count,array($tenant_uuid));
        $count = $result_count[0]['count_all'];
        $info['records'] = array();
        foreach ($resule_one_details as $op){
            $start_time = $op['create_time'];
            $end_time = $op['end_time'];
            $billing_uuid = $op['billing_uuid'];
            $monetaryStr = $this->getMonetaryUnitStr($billing_uuid);
            $info['records'][]= array(
                'start_time' => strtotime($op['create_time']),
                'end_time' => strtotime($op['end_time']),
                'billing_uuid' => $op['billing_uuid'],
                'billing_total' => floatval($op['billing_total']),
                'capacity_size' => intval($op['capacity_size']),
                'use_num' => json_decode($op['use_num'],true),
                'billing_mode' => intval($billing_mode),
                'num_type' => json_decode($num_type,true),
                'monetaryStr' => $monetaryStr,
            );
        }
        
        $info["total"] = $count;
        $info["begin"] = $start;
        $info["count"] = $length;
        return $this->apiResponse(true, "API_CODE_BILLING_GET_TENANT_BILL_DETAIL", $info, array());
    }
    
    /**********************************其他工具方法************************************/
    /**
     * 修改后重新计算价格
     * @param 费用uuid
     * @author liushuai@vinchin.com
     * @return 费用单位
     */
    public function getBillingTotalAgain($billing_uuid){
        //先查询什么样的计费再计算单价
        $sql_billing = "select billing_type,billing_mode,num_type,unit_cost from bd_billing where billing_uuid = ?";
        $result_billing = $this->dbSelect($sql_billing,array($billing_uuid));
        $billing_type = $result_billing[0]['billing_type'];
        $billing_mode = $result_billing[0]['billing_mode'];
        $unit_cost = json_decode($result_billing[0]['unit_cost'],true);
        $billing_vm = $unit_cost['billing_vm'];
        $billing_fs = $unit_cost['billing_fs'];
        $billing_db = $unit_cost['billing_db'];
        $billing_capacity = $unit_cost['billing_capacity'];
        $num_type = json_decode($result_billing[0]['num_type'],true);
        //查询此费用策略下拥有的租户有哪些
        $sql_mt = "select * from mt_tenant_billing where billing_uuid = ?";
        $result_mt = $this->dbSelect($sql_mt,array($billing_uuid));
        //得到租户的数组
        $tenant_list = array();
        foreach ($result_mt as $tenant){
            $tenant_list[] = $tenant['tenant_uuid'];
        }
        //再查询每个租户下的费用详情表
        foreach ($tenant_list as $tenant_uuid){
            //查询单个租户下的费用详情表
            $sql_det = "select id,create_time,end_time,use_num,capacity_size from bd_billing_details where tenant_uuid=? order by update_time desc";
            $result_det = $this->dbSelect($sql_det,array($tenant_uuid));
            if(empty($result_det)){
                continue;
            }
            //得到详细费用的每一条记录重新计算单价
            foreach ($result_det as $op){
                $id = $op['id'];
                $start_time = $op['create_time'];
                $end_time = $op['end_time'];
                $use_num = json_decode($op['use_num'],true);
                $use_vm = $use_num['num_vm'];
                $use_fs = $use_num['num_fs'];
                $use_db = $use_num['num_db'];
                $capacity_size = $op['capacity_size'];
                //计算时间差
                $time_t = $this->getTimeDifference($start_time, $end_time);
                //根据类型不同重新计算单价
                if($billing_type ==1){//如果是包月
                    if($billing_mode ==1){
                        $totalBilling = $billing_capacity*$capacity_size*$time_t[1];
                    }else{
                        foreach ($num_type as $i){
                            if($i ==1){
                                $b_vm = 0;
                                $b_vm = $billing_vm*$use_vm;
                            }
                            if($i ==2){
                                $b_fs = 0;
                                $b_fs = $billing_fs*$use_fs;
                            }
                            if($i ==3){
                                $b_db = 0;
                                $b_db = $billing_db*$use_db;
                            }
                            
                            $totalBilling = ($b_vm+$b_fs+$b_db)*$time_t[1];
                        }
                    }
                }elseif ($billing_type==2){//如果是包年
                    if($billing_mode ==1){
                        $totalBilling = $billing_capacity*$capacity_size*$time_t[0];
                    }else{
                        foreach ($num_type as $i){
                            if($i ==1){
                                $b_vm = 0;
                                $b_vm = $billing_vm*$use_vm;
                            }
                            if($i ==2){
                                $b_fs = 0;
                                $b_fs = $billing_fs*$use_fs;
                            }
                            if($i ==3){
                                $b_db = 0;
                                $b_db = $billing_db*$use_db;
                            }
                            $totalBilling = ($b_vm+$b_fs+$b_db)*$time_t[0];
                        }
                    }
                }else{//如果是按量
                    if($billing_mode ==1){
                        $totalBilling = $billing_capacity*$capacity_size;
                    }else{
                        foreach ($num_type as $i){
                            if($i ==1){
                                $b_vm = 0;
                                $b_vm = $billing_vm*$use_vm;
                            }
                            if($i ==2){
                                $b_fs = 0;
                                $b_fs = $billing_fs*$use_fs;
                            }
                            if($i ==3){
                                $b_db = 0;
                                $b_db = $billing_db*$use_db;
                            }
                            $totalBilling = ($b_vm+$b_fs+$b_db);
                        }
                    }
                    
                };
                //                 得到每一条记录后更新每一条记录
                $sql_up = "update bd_billing_details set billing_total = ? where id=?";
                $result_up = $this->dbQuery($sql_up,array($totalBilling,$id));
            }
        }
    }
    
    
    /**
     * 得到时间差
     * @param unknown $start_time 开始时间
     * @param unknown $end_time 结束时间
     * return array(年,月,日,时,分,秒)
     */
    public function getTimeDifference($start_time,$end_time){
        $date1 = strtotime(date($start_time));
        $date2 = strtotime(date($end_time));
        //计算两个日期之间的时间差
        $diff = abs($date2 - $date1);
        //转换时间差的格式
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24)  / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 -  $months*30*60*60*24)/ (60*60*24));
        $hours = floor(($diff - $years * 365*60*60*24   - $months*30*60*60*24 - $days*60*60*24)  / (60*60));
        $minutes = floor(($diff - $years * 365*60*60*24  - $months*30*60*60*24 - $days*60*60*24  - $hours*60*60)/ 60);
        $seconds = floor(($diff - $years * 365*60*60*24  - $months*30*60*60*24 - $days*60*60*24  - $hours*60*60 - $minutes*60));
        return array($years,$months,$days,$hours,$minutes,$seconds);
    }
    
    /**
     * 得到费用的单位
     * @param unknown $billing_uuid
     * @author liushuai@vinchin.com
     * @return string
     */
    public function getMonetaryUnitStr($billing_uuid){
        $sql = "select monetary_unit from bd_billing where billing_uuid = ?";
        $result = $this->dbSelect($sql,array($billing_uuid));
        $unit = include APP_PATH . "platform/MonetaryUnitArray.php";
        $MonetaryStr = "";
        $MonetaryStr = $unit['MONETARY_UNIT'][$result[0]['monetary_unit']][0];
        return $MonetaryStr;
    }
    
    /**
     * 获取租户使用容量
     * @param unknown $tenant_uuid
     * @author liushuai@vinchin.com
     * @return int
     */
    public function pGetTenantCapacity($tenant_uuid){
        $UsersHandler = Xphp::instance("APIUsersHandler");
        $get_user_uuid_list = $UsersHandler->pGetUseruuidList($tenant_uuid);
        $string = implode("','",$get_user_uuid_list);
        //计算每个用户大小的sql语句
        $sql = "select sum(write_size) as total_size from bd_backup_timepoint where user_uuid in";
        $sql .=" ("."'"."$string"."'".")";
        //计算当前租户下每个用户用的资源大小的和
        $result_size = $this->dbSelect($sql);
        $info_size = intval($result_size[0]['total_size']);
        return $info_size;
    }
    
    /**
     * 获取租户使用的虚拟机数量,文件数量以及数据库数量
     * @param unknown $tenant_uuid
     * @author liushuai@vinchin.com
     * @return array 返回一个数组,里面有num_vm,num_fs,num_db的使用数量的值
     */
    public function pGetTenantUseNum($tenant_uuid){
        //先获取当前租户的所有用户列表
        $UsersHandler = Xphp::instance("APIUsersHandler");
        $get_user_uuid_list = $UsersHandler->pGetUseruuidList($tenant_uuid);//得到用户uuid列表
        $string = implode("','",$get_user_uuid_list);
        
        //获取虚拟机timepoint_uuid集合
        $sql_vm_time_list = "select timepoint_uuid from bd_backup_timepoint where module_type = 2 and user_uuid in ";
        $sql_vm_time_list .= " ("."'"."$string"."'".")";
        $result_vm_time_list = $this->dbSelect($sql_vm_time_list);
        $vm_timepoint_list = array();
        foreach ($result_vm_time_list as $op){
            $vm_timepoint_list[] = $op['timepoint_uuid'];
        }
        $string_vm = implode("','",$vm_timepoint_list);
        //获取虚文件timepoint_uuid集合
        $sql_fs_time_list = "select timepoint_uuid from bd_backup_timepoint where module_type = 3 and user_uuid in ";
        $sql_fs_time_list .= " ("."'"."$string"."'".")";
        $result_fs_time_list = $this->dbSelect($sql_fs_time_list);
        $fs_timepoint_list = array();
        foreach ($result_fs_time_list as $op){
            $fs_timepoint_list[] = $op['timepoint_uuid'];
        }
        $string_fs = implode("','",$fs_timepoint_list);
        //获取数据库timepoint_uuid集合
        $sql_db_time_list = "select timepoint_uuid from bd_backup_timepoint where module_type = 4 and user_uuid in ";
        $sql_db_time_list .= " ("."'"."$string"."'".")";
        $result_db_time_list = $this->dbSelect($sql_db_time_list);
        $db_timepoint_list = array();
        foreach ($result_db_time_list as $op){
            $db_timepoint_list[] = $op['timepoint_uuid'];
        }
        $string_db = implode("','",$db_timepoint_list);
        
        //查询已经完成备份点的虚拟机数量
        $sql_vm = "select count(distinct vcenter_uuid,vm_uuid) as num_vm from vm_backup_timepoint where timepoint_uuid in";
        $sql_vm .=" ("."'"."$string_vm"."'".")";
        $result_vm = $this->dbSelect($sql_vm);
        $info_num_vm = $result_vm[0]['num_vm'];
        
        //查询已使用的文件数量
        $sql_fs = "select count(distinct agent_uuid) as num_fs from fs_backup_timepoint where fs_timepoint_uuid in";
        $sql_fs .=" ("."'"."$string_fs"."'".")";
        $result_fs = $this->dbSelect($sql_fs);
        $info_num_fs = $result_fs[0]['num_fs'];
        
        //查询已使用的数据库数量
        $sql_db = "select count(distinct agent_uuid) as num_db from db_backup_timepoint where timepoint_uuid in";
        $sql_db .=" ("."'"."$string_db"."'".")";
        $result_db = $this->dbSelect($sql_db);
        $info_num_db = $result_db[0]['num_db'];
        
        $info = array(
            'num_vm' => $info_num_vm,
            'num_fs' => $info_num_fs,
            'num_db' => $info_num_db,
        );
        return $info;
    }
    
    
    /**
     * 得到此租户的一些基本数据,处理后返回给页面
     * @param unknown $params tenant_uuid
     * @author liushuai@vinchin.com
     * @return array
     */
    public function getMsgOfTenant($params){
        $tenant_uuid = $params['tenant_uuid'];
        $this->apiParamsCheck($tenant_uuid);
        //先获取此租户的费用策略表数据
        $sql_mt = "select billing_uuid,create_time from mt_tenant_billing where tenant_uuid =?";
        $result_mt = $this->dbSelect($sql_mt,array($tenant_uuid));
        $billing_uuid = $result_mt[0]['billing_uuid'];
        $mt_create_time = $result_mt[0]['create_time'];
        $monetaryStr = $this->getMonetaryUnitStr($billing_uuid);
        //获取费用策略表的数据
        $sql_billing = "select billing_type,billing_mode,num_type from bd_billing where billing_uuid =?";
        $result_billing = $this->dbSelect($sql_billing,array($billing_uuid));
        $billing_type = $result_billing[0]['billing_type'];
        
        $billing_mode = $result_billing[0]['billing_mode'];
        $use_num_list = json_decode($result_billing[0]['num_type'],true);
        //查询租户的租户名
        $sql_tenant_name = "select tenant_name,nick_name from bd_tenant where tenant_uuid = ?";
        $result_tenant_name = $this->dbSelect($sql_tenant_name,array($tenant_uuid));
        $tenant_name = $result_tenant_name[0]['tenant_name'];
        $nick_name = $result_tenant_name[0]['nick_name'];
        
        $info = array();
        //根据类型不容有不同的处理方式
        if($billing_type ==1 || $billing_type ==2){
            //获得租户费用详情表
            $sql_tenant_details = "select * from bd_billing_details where tenant_uuid = ? order by update_time desc limit 1";
            $result_tenant_details = $this->dbSelect($sql_tenant_details,array($tenant_uuid));
            $start_time = $result_tenant_details[0]['create_time'];
            $end_time = $result_tenant_details[0]['end_time'];
            $billing_total = $result_tenant_details[0]['billing_total'];
            //计算出str格式
            //               $str_start_time = date("Y",strtotime($start_time)).Xphp::$_lang['BILLING_YEAR'].date("m",strtotime($start_time)).Xphp::$_lang['BILLING_MONTH'].date("d",strtotime($start_time)).Xphp::$_lang['BILLING_DAY_OTHER'];
            //               $str_end_time = date("Y",strtotime($end_time)).Xphp::$_lang['BILLING_YEAR'].date("m",strtotime($end_time)).Xphp::$_lang['BILLING_MONTH'].date("d",strtotime($end_time)).Xphp::$_lang['BILLING_DAY_OTHER'];
            $str_start_time = $start_time;
            $str_end_time = $end_time;
            
            //计算天数
            $str_day_list = $this->pGetTimeDay($start_time, $end_time);
            $str_day = $str_day_list[0];
            //获得使用容量大小
            $size = $this->pGetTenantCapacity($tenant_uuid);
            $Utils = Xphp::instance("Utils");
            $string_size = $Utils->calSize($size,true);
            //获得使用数量
            $num_all = $this->pGetTenantUseNum($tenant_uuid);
            //再检查此策略下勾选了哪几个数量类型
            $string_num = "";
            foreach ($use_num_list as $one_num_type){
                if($one_num_type ==1){
                    $string_num .= Xphp::$_lang['BILLING_VM'].":".$num_all[num_vm].Xphp::$_lang['BILLING_UNIT_ONE']."  ";
                }
                if($one_num_type ==2){
                    $string_num .= Xphp::$_lang['BILLING_FS'].":".$num_all[num_fs].Xphp::$_lang['BILLING_UNIT_ONE']."  ";
                }
                if($one_num_type ==3){
                    $string_num .= Xphp::$_lang['BILLING_DB'].":".$num_all[num_db].Xphp::$_lang['BILLING_UNIT_ONE']."  ";
                }
            }
            $info = array(
                'nick_name' => $nick_name,
                'tenant_name' => $tenant_name,
                'billing_type' => $billing_type,
                'billing_mode' => $billing_mode,
                'start_time' => $str_start_time,
                'end_time' => $str_end_time,
                'use_day' => $str_day,
                'use_capacity' => $string_size,
                'use_num' => $string_num,
                'billing_total' => $billing_total.$monetaryStr,
                'billing_totalNum' =>$billing_total,
            );
        }else{ //如果是按量计费,则需要计算其总价
            //获得租户费用详情表
            $sql_tenant_details = "select * from bd_billing_details where tenant_uuid = ?";
            $result_tenant_details = $this->dbSelect($sql_tenant_details,array($tenant_uuid));
            if(empty($result_tenant_details)){//如果为空值,则还没生成第一天的数据,直接返回空
                $info = array(
                    'nick_name' => $nick_name,
                    'tenant_name' => $tenant_name,
                    'billing_type' => $billing_type,
                    'billing_mode' => $billing_mode,
                    'start_time' => $mt_create_time,
                    'end_time' => Xphp::$_lang['WEB_PLATFORM_PUBLIC_NONE'],
                    'use_day' => 0,
                    'use_capacity' => 0,
                    'use_num' => 0,
                    'billing_total' => 0,
                    'billing_totalNum' =>0,
                );
                return json_encode($info);
            };
            //获得租户费用详情表
            $sql_tenant_details = "select * from bd_billing_details where tenant_uuid = ? order by update_time asc limit 1";
            $result_tenant_details = $this->dbSelect($sql_tenant_details,array($tenant_uuid));
            $start_time = $result_tenant_details[0]['create_time'];
            $end_time = date("Y-m-d H:i:s");
            
            //计算费用
            $sql_billing_total = "select sum(billing_total) as total from bd_billing_details where tenant_uuid = ?";
            $result_billing_total = $this->dbSelect($sql_billing_total,array($tenant_uuid));
            if(empty($result_billing_total)){
                $billing_total = 0;
            }else{
                $billing_total = $result_billing_total[0]['total'];
            }
            //计算出str格式
            //               $str_start_time = date("Y",strtotime($start_time)).Xphp::$_lang['BILLING_YEAR'].date("m",strtotime($start_time)).Xphp::$_lang['BILLING_MONTH'].date("d",strtotime($start_time)).Xphp::$_lang['BILLING_DAY_OTHER'];
            //               $str_end_time = date("Y",strtotime($end_time)).Xphp::$_lang['BILLING_YEAR'].date("m",strtotime($end_time)).Xphp::$_lang['BILLING_MONTH'].date("d",strtotime($end_time)).Xphp::$_lang['BILLING_DAY_OTHER'];
            $str_start_time = $start_time;
            $str_end_time = $end_time;
            //计算天数
            $str_day_list = $this->pGetTimeDay($start_time, $end_time);
            $str_day = $str_day_list[0];
            //获得使用容量大小
            $size = $this->pGetTenantCapacity($tenant_uuid);
            $Utils = Xphp::instance("Utils");
            $string_size = $Utils->calSize($size,true);
            //获得使用数量
            $num_all = $this->pGetTenantUseNum($tenant_uuid);
            //再检查此策略下勾选了哪几个数量类型
            
            $string_num = "";
            foreach ($use_num_list as $one_num_type){
                if($one_num_type ==1){
                    $string_num .= Xphp::$_lang['BILLING_VM'].":".$num_all['num_vm'].Xphp::$_lang['BILLING_UNIT_ONE']."  ";
                }
                if($one_num_type ==2){
                    $string_num .= Xphp::$_lang['BILLING_FS'].":".$num_all['num_fs'].Xphp::$_lang['BILLING_UNIT_ONE']."  ";
                }
                if($one_num_type ==3){
                    $string_num .= Xphp::$_lang['BILLING_DB'].":".$num_all['num_db'].Xphp::$_lang['BILLING_UNIT_ONE']."  ";
                }
            }
            
            $info = array(
                'nick_name' => $nick_name,
                'tenant_name' => $tenant_name,
                'billing_type' => $billing_type,
                'billing_mode' => $billing_mode,
                'start_time' => $str_start_time,
                'end_time' => $str_end_time,
                'use_day' => $str_day,
                'use_capacity' => $string_size,
                'use_num' => $string_num,
                'billing_total' => $billing_total.$monetaryStr,
                'billing_totalNum' =>$billing_total,
            );
        }
        return json_encode($info);
    
    }
    
    
    /**
     * 获得两个时间差
     * @param unknown $startdate   开始时间
     * @param unknown $enddate   结束时间
     * @author liushuai@vinchin.com
     * @return [天,小时,分钟,秒]
     *
     */
    public function pGetTimeDay($startdate,$enddate){
        $date=floor((strtotime($enddate)-strtotime($startdate))/86400);
        $hour=floor((strtotime($enddate)-strtotime($startdate))%86400/3600);
        $minute=floor((strtotime($enddate)-strtotime($startdate))%86400/60);
        $second=floor((strtotime($enddate)-strtotime($startdate))%86400%60);
        return array($date,$hour,$minute,$second);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

}