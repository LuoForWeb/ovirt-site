<?php
/*******************************************
 ** 计费管理类
 **
 ** @author       xiezhuowei@vinchin.com;luokai@vinchin.com;liushuai@vinchin.com
 ** @date         2020-09-10 下午17:04:00
 ** @version      1.0.0
 ** @copyright    Copyright 2020 vinchin.com
 ********************************************/
class BillingHandler extends OPHandler{
    /**
     * 增加费用策略
     * @param unknown $params (billing_name,billing_description,billing_type,monetary_unit,billing_num_type
      num_selected,billing_vm,billing_fs,billing_db,billing_capacity,inform,inform_time)
     
     *@author liushuai@vinchin.com
     *return bool
     */
    public function addBilling($params){
        $billing_name = $params['billing_name'];
        $billing_description = $params['billing_description'];
        $billing_type = $params['billing_type'];
        $monetary_unit = $params['monetary_unit'][0];
        if(!$monetary_unit){
            $monetary_unit="";
        }
        $billing_num_type = $params['billing_num_type'];
        
        $num_selected1 = json_encode($params['num_selected']);
        $num_selected="'".$num_selected1."'";
        
        $billing_vm = $params['billing_vm'];
        if(!$billing_vm){
            $billing_vm="";
        }
        $billing_fs = $params['billing_fs'];
        if(!$billing_fs){
            $billing_fs="";
        }
        $billing_db = $params['billing_db'];
        if(!$billing_db){
            $billing_db="";
        }
        $billing_capacity = $params['billing_capacity'];
        if(!$billing_capacity){
            $billing_capacity="";
        }
        $inform = $params['inform'];
        $inform_period = $params['inform_period'];
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
            return $this->muOpResult($result, Xphp::$_lang['BILLING_ADD_BILLING']);
        }else{
            return $this->muOpResult($result, Xphp::$_lang['BILLING_ADD_BILLING'],'','warning');
        }
    }
    
    /**
     * 得到计费策略列表
     * @param unknown $params
     * @author liushuai@vinchin.com
     * @return array
     */
    public function getBillingList($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];//排序的参数
        $sortType = $params['sortType'];//排序类型
        $params_sort = array('','name','description','','billing_type','create_time','','inform','','lock_flag');
        $sql = "select * from bd_billing";
        $sqlParams =  array($start,$length);
        $sql .=" order by $params_sort[$sortColumn] $sortType limit ?,?";
        $result = $this->dbSelect($sql, $sqlParams);
        $sql_count = "select count(id) as count_all from bd_billing";
        $result_count = $this->dbSelect($sql_count);
        $count = intval($result_count[0]['count_all']);
        $info['data'] = array();
        $PFDes = include APP_PATH . "platform/PFDescription.php";
        foreach ($result as $op){
            $monetary_unit = $this->getMonetaryUnitStr($op['billing_uuid']);
            $info['data'][] = array(
                '<input type="checkbox" name="id[]" value="'. $op['billing_uuid'] .'">',
                $op['name'],
                $op['description'],
                $monetary_unit,
                $PFDes['BILLING_TYPE'][$op['billing_type']],
                $op['create_time'],
                $this->getUnitCostStr($op['billing_uuid']),
                $PFDes['INFORM_STATE_DES'][$op['inform']],
                $PFDes['INFORM_PERIOD_DES'][$op['inform_period']],
//                 $op['inform_time'],
                $PFDes['LOCK_DESC'][$op['lock_flag']],
                $op['billing_uuid'],
            );
        }
        
        $info["draw"] = $params['draw'];
        $info["recordsTotal"] = $count;
        $info["recordsFiltered"] = $count;
        return json_encode($info);
    }
    
    /**
     * 得到计费单条数据
     * @param unknown $params billing_uuid
     * @author liushuai@vinchin.com
     * @return array
     */
    public function getEditBillingList($params){
        $billing_uuid = $params['billing_uuid'];
        $sql = "select * from bd_billing where billing_uuid = ?";
        $result = $this->dbSelect($sql,array($billing_uuid));
        $info = array();
        foreach ($result as $op){
            $info = array(
                'name' => $op['name'],
                'description' => $op['description'],
                'billing_type' => $op['billing_type'],
                'billing_mode' => $op['billing_mode'],
                'num_type' => $op['num_type'],
                'monetary_unit' => $op['monetary_unit'],
                'unit_cost' => $op['unit_cost'],
                'inform' => $op['inform'],
                'inform_time' => $op['inform_time'],
                'inform_period' => $op['inform_period'],
                'MonetaryStr' => $this->getMonetaryUnitStr($billing_uuid),
            );
        }
        return json_encode($info);
        
    }
    /**
     * 
     * @param unknown $params array (billing_name,billing_description,billing_type,monetary_unit,billing_num_type
      num_selected,billing_vm,billing_fs,billing_db,billing_capacity,inform,inform_period)
     *@author liushuai@vinchin.com
     *@return 1 表示成功     0表示失败
     */
    public function editBilling($params){
        $billingUUID = $params['billing_uuid'];
        $billing_name = $params['billing_name'];
        $billing_description = $params['billing_description'];
        $billing_type = $params['billing_type'];
        $monetary_unit = $params['monetary_unit'][0];
        if(!$monetary_unit){
            $monetary_unit="";
        }
        $billing_num_type = $params['billing_num_type'];
        
        $num_selected1 = json_encode($params['num_selected']);
        $num_selected="'".$num_selected1."'";
        
        $billing_vm = $params['billing_vm'];
        if(!$billing_vm){
            $billing_vm="";
        }
        $billing_fs = $params['billing_fs'];
        if(!$billing_fs){
            $billing_fs="";
        }
        $billing_db = $params['billing_db'];
        if(!$billing_db){
            $billing_db="";
        }
        $billing_capacity = $params['billing_capacity'];
        if(!$billing_capacity){
            $billing_capacity="";
        }
        $inform = $params['inform'];
        $inform_period = $params['inform_period'];
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
            return $this->muOpResult($result, Xphp::$_lang['BILLING_EDIT_BILLING']);
        }else{
            return $this->muOpResult($result, Xphp::$_lang['BILLING_EDIT_BILLING'],'','warning');
            }
    }
    
    /**
     * 修改为启用费用策略状态
     * @param unknown $params array 一个billing_uuid列表
     * @author liushuai@vinchin.com
     * @return 0失败 1成功 -1已经有启用状态的了
     * 
     */
    public function onBilling($params){
        $uuid_list = $params['uuid_list'];
        $string = implode("','",$uuid_list);
        //先检查是否有已经启用的
        $check_sql = "select count(billing_uuid) as check_count from bd_billing where lock_flag=1 and billing_uuid in";   
        $sql = "update bd_billing set lock_flag=1 where billing_uuid in";
        $check_sql .=" ("."'"."$string"."'".")";
        $sql .=" ("."'"."$string"."'".")";
        $result_check = $this->dbSelect($check_sql);
        $count_check_num = $result_check[0]['check_count'];
        $info = array();
        if($count_check_num != 0){
            return $this->muOpResult(false,Xphp::$_lang['BILLING_ON_LOCK_BILLING'],Xphp::$_lang['BILLING_ON_LOCK_INFO'],'warning');
        }
        $result = $this->dbQuery($sql);
        if($result){
            return $this->muOpResult($result,Xphp::$_lang['BILLING_ON_LOCK_BILLING']);
        }else{
            return $this->muOpResult($result,Xphp::$_lang['BILLING_ON_LOCK_BILLING'],'','warning');
        }
    }
    
    
    
    /**
     * 修改为禁用费用策略状态
     * @param unknown $params array 一个billing_uuid列表
     * @author liushuai@vinchin.com
     * @return 0失败 1成功 -1已经有禁用状态的了
     *
     */
    public function offBilling($params){
        $uuid_list = $params['uuid_list'];
        $string = implode("','",$uuid_list);
        //先检查是否有已经启用的
        $check_sql = "select count(billing_uuid) as check_count from bd_billing where lock_flag=2 and billing_uuid in";
        $sql = "update bd_billing set lock_flag=2 where billing_uuid in";
        $check_sql .=" ("."'"."$string"."'".")";
        $sql .=" ("."'"."$string"."'".")";
        $result_check = $this->dbSelect($check_sql);
        $count_check_num = $result_check[0]['check_count'];
        $info = array();
        if($count_check_num != 0){
            return $this->muOpResult(false,Xphp::$_lang['BILLING_OFF_LOCK_BILLING'],Xphp::$_lang['BILLING_OFF_LOCK_INFO'],'warning');
        }
        $result = $this->dbQuery($sql);
        if($result){
            return $this->muOpResult($result,Xphp::$_lang['BILLING_OFF_LOCK_BILLING']);
        }else{
            return $this->muOpResult($result,Xphp::$_lang['BILLING_OFF_LOCK_BILLING'],'','warning');
        }
    }
    /**
     * 删除计费策略
     * @param unknown $params array 一个billing_uuid列表
     * @author liushuai@vinchin.com
     * @return 0失败   1成功
     */
    public function deleteBinlling($params){
        $uuid_list = $params['uuid_list'];
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
            return $this->muOpResult($result,Xphp::$_lang['BILLING_DELETE_BILLING']);
        }else{
            return $this->muOpResult($result,Xphp::$_lang['BILLING_DELETE_BILLING'],'','warning');
        }
    }
    
    /**
     * 得到租户列表,只得到未分配的租户
     * @author liushuai@vinchin.com
     * @return array [[tenant_uuid:"",tenant_name:""],'''']
     */
    public function getTenantList(){
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
        return json_encode($info);
        
    }
    /**
     * 分配租户到策略后生成订单---只有包月包年才能一次性生成订单,如果是按量则不立即生成订单
     * @param unknown $params   tenant_uuid_list
    	                        billing_capacity
                            	billing_vm 
                                billing_fs
                                billing_db
                                billing_time
     * @author liushuai@vinchin.com
     * return 0失败 1成功 3关联表插入出错
     */
      public function createBillingOrder($params){
          $tenant_uuid_list = $params['tenant_uuid_list'];
          
          $billing_uuid = $params['billing_uuid'];
          $billing_type = $params['billing_type_get'];
          $create_time = date("Y-m-d H:i:s");
          $billing_total = $params['billing_total'];
          
          $this->paramsCheck($tenant_uuid_list);
          $num_capacity = $params['num_capacity'];
          $num_vm = $params['num_vm'];
          $num_fs = $params['num_fs'];
          $num_db = $params['num_db'];
          $billing_time = $params['billing_time'];
          
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
              return $this->muOpResult(false, Xphp::$_lang['BILLING_ALLOCATION_TENANT'],Xphp::$_lang['BILLING_ALLOCATION_INFO'],'warning');
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
                  return $this->muOpResult($result_order,Xphp::$_lang['BILLING_ALLOCATION_TENANT']);
              }else{
                  return $this->muOpResult($result_order,Xphp::$_lang['BILLING_ALLOCATION_TENANT'],'','warning');
              }
             
          }
          if($result_relation){
              return $this->muOpResult($result_relation,Xphp::$_lang['BILLING_ALLOCATION_TENANT']);
          }else{
              return $this->muOpResult($result_relation,Xphp::$_lang['BILLING_ALLOCATION_TENANT'],'','warning');
          }
      }
      
      /**
       * 得到费用策略详情表
       * @param unknown $params
       * @author liushuai@vinchin.com
       * @return array
       */
      
      public function getBillingDetails($params){
          $start = $params['start'];
          $length = $params['length'];
          $billing_uuid = $params['billing_uuid'];
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
          $info['data'] = array();
          foreach ($tenant_uuid_list as $tenant_uuid){
              //先判断是按数量还是按容量
              if($billing_mode ==1){  //按容量--获取租户使用容量
                  $size = $this->pGetTenantCapacity($tenant_uuid);
                  $Utils = Xphp::instance("Utils");
                  $string_size = $Utils->calSize($size,true);
              }else{     //按数量---获取租户使用数量
                  $num_all = $this->pGetTenantUseNum($tenant_uuid);
                  
                  //再检查此策略下勾选了哪几个数量类型
                  $string_size = "";
                  foreach ($billing_num_type as $one_num_type){
                      if($one_num_type ==1){
                          $string_size .= Xphp::$_lang['BILLING_VM'].":".$num_all[num_vm].Xphp::$_lang['BILLING_UNIT_ONE']."<br>";
                      }
                      if($one_num_type ==2){
                          $string_size .= Xphp::$_lang['BILLING_FS'].":".$num_all[num_fs].Xphp::$_lang['BILLING_UNIT_ONE']." <br>";
                      }
                      if($one_num_type ==3){
                          $string_size .= Xphp::$_lang['BILLING_DB'].":".$num_all[num_db].Xphp::$_lang['BILLING_UNIT_ONE']." <br>";
                      }
                  }
                 
              }
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
              }else{
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
              }
              //得到租户的名字
              $sql_tenant_name = "select tenant_name from bd_tenant where tenant_uuid = ?";
              $result_tenant_name = $this->dbSelect($sql_tenant_name,array($tenant_uuid));
              $tenant_name = $result_tenant_name[0]['tenant_name'];
              
              $info['data'][] = array(
                  '<input type="checkbox" name="id[]" value="'. $tenant_uuid .'">',
                  $tenant_name,
                  $string_size,
                  $start_time,
                  $end_time,
                  $total_billing.$MonetaryStr,
                  Xphp::$_lang['BILLING_VIEW_DETAILS'],
                  array(
                      'tenant_uuid'=> $tenant_uuid,
                      'use_num' => $use_num,
                      'capacity_size' => $capacity_size,
                      'start_total' =>$total_billing,
                  ),
              );
          }
          $info["draw"] = $params['draw'];
          $info["recordsTotal"] = $count;
          $info["recordsFiltered"] = $count;
         
          return json_encode($info);
          
      }
      
      
      /**
       * 根据单个租户uuid删除有关租户的所有费用订单和联系表
       * @param unknown $params tenant_uuid
       * @author liushuai@vinchin.com
       * @return 0失败 1成功
       */
      public function deleteBillingOfTenant($params){
          $tenant_uuid = $params['tenant_uuid'];
          $this->paramsCheck($tenant_uuid);
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
             return $this->muOpResult($result_relation,Xphp::$_lang['BILLING_REMOVE_TENANT']);
         }else{
             return $this->muOpResult($result_relation,Xphp::$_lang['BILLING_REMOVE_TENANT'],'','warning');
         }
      }
      
      /**
       * 更新时间并创建一张新的订单表
       * @param unknown $params
       */
      public function updateBillingDetailsTime($params){
          $tenant_uuid = $params['tenant_uuid'];
          $new_time = $params['new_time'];
          $now_time = date("Y-m-d H:i:s");
          $new_total = $params['new_total'];
          //复制最新的一条数据然后插入新数据并更新时间
          $sql = "insert into bd_billing_details(tenant_uuid,billing_uuid,billing_type,create_time
,end_time,update_time,use_num,capacity_size,billing_total) select tenant_uuid,billing_uuid,billing_type,create_time,? as end_time,? as update_time,use_num,capacity_size,
? as billing_total from bd_billing_details where tenant_uuid = ? order by update_time desc limit 1";
          $params = array($new_time,$now_time,$new_total,$tenant_uuid);
          $result = $this->dbQuery($sql,$params);
          if($result){
              return $this->muOpResult($result,Xphp::$_lang['BILLING_RENEW']);
          }else{
              return $this->muOpResult($result,Xphp::$_lang['BILLING_RENEW'],'','warning');
          }
      }
      
      
      
      /**
       * 得到此租户的详细费用信息
       * @param unknown $params tenant_uuid
       * @author liushuai@vinchin.com
       * @return array
       */
      public function getTenantBillingDetails($params){
          $start = $params['start'];
          $length = $params['length'];
          $tenant_uuid = $params['tenant_uuid'];
          
          $sql_details = "select billing_type,billing_uuid from bd_billing_details where tenant_uuid = ? order by update_time desc limit 1";
          $resule_details = $this->dbSelect($sql_details,array($tenant_uuid));
          //如果没有查询到值 ,可能每天计费时,第一条还没有生成订单,直接返回空即可
          if(empty($resule_details)){
              $info['data'] = array();
              $info["recordsTotal"] = 0;
              $info["recordsFiltered"] = 0;
              return json_encode($info);
          }
          //得到当前租户使用的费用策略
          $billing_uuid = $resule_details[0]['billing_uuid'];
          $billing_type = $resule_details[0]['billing_type'];
          $sql_billing = "select billing_mode,num_type from bd_billing where billing_uuid = ?";
          $result_billing = $this->dbSelect($sql_billing,array($billing_uuid));
          $billing_mode = $result_billing[0]['billing_mode'];
          
          $num_type = $result_billing[0]['num_type'];
          
          $sql_one_details = "select * from bd_billing_details where tenant_uuid = ? order by update_time desc limit ?,?";
          $resule_one_details = $this->dbSelect($sql_one_details,array($tenant_uuid,$start,$length));
          $sql_count = "select count(id) as count_all from bd_billing_details where tenant_uuid = ?";
          $result_count = $this->dbSelect($sql_count,array($tenant_uuid));
          $count = $result_count[0]['count_all'];
          $info['data'] = array();
          foreach ($resule_one_details as $op){
              $start_time = $op['create_time'];
              $end_time = $op['end_time'];
              $billing_uuid = $op['billing_uuid'];
              $monetaryStr = $this->getMonetaryUnitStr($billing_uuid);
              $billing_total = $op['billing_total'];
              if(empty($billing_total)){
                  $billing_total = 0;
              };
              $info['data'][]= array(
                  $start_time,
                  $end_time,
                  $this->getUseNumStr($billing_mode, $op['capacity_size'], $op['use_num'], $num_type),
                  $this->getUnitCostStr($billing_uuid),//单价
                  $billing_total.$monetaryStr,
              );
          }
          
          
          $info["draw"] = $params['draw'];
          $info["recordsTotal"] = $count;
          $info["recordsFiltered"] = $count;
          return json_encode($info);
          
      }
      
      
      
      /**
       * 获取策略类型单价
       * @param unknown $params 一个billing_uuid
       * @author liushuai@vinchin
       * @return str
       */
      public function getUnitCostStr($billing_uuid){
          $sql = "select * from bd_billing where billing_uuid = ?";
          $result = $this->dbSelect($sql,array($billing_uuid));
          $billing_type = $result[0]['billing_type'];
          $monetary_unit = $result[0]['monetary_unit'];
          $billing_mode = $result[0]['billing_mode'];
          $unit_cost = json_decode($result[0]['unit_cost'],true);
          $billing_vm = $unit_cost['billing_vm'];
          $billing_fs = $unit_cost['billing_fs'];
          $billing_db = $unit_cost['billing_db'];
          $billing_capacity = $unit_cost['billing_capacity'];
          //如果设置为0或空  赋值为0
          if(empty($billing_vm)){
              $billing_vm = 0;
          }
          if(empty($billing_fs)){
              $billing_fs = 0;
          }
          if(empty($billing_db)){
              $billing_db = 0;
          }
          if(empty($billing_capacity)){
              $billing_capacity = 0;
          }
          $num_type = json_decode($result[0]['num_type'],true);
          $monetary_unit = $this->getMonetaryUnitStr($billing_uuid);
          
          //根据所选策略类型不同显示不同时间单位---主要是单价的地方用
          if($billing_type==1){
              $time_unit = Xphp::$_lang['BILLING_MONTH'];
          }else if($billing_type==2){
              $time_unit = Xphp::$_lang['BILLING_YEAR'];
          }else{
              $time_unit = Xphp::$_lang['BILLING_DAY'];
          }
          //根据单价不同显示不同的单价结构
          //如果为容量的话就直接显示容量单价
          if($billing_mode==1){
              $str_unit_cost = Xphp::$_lang['BILLING_CAPACITY'].":".$billing_capacity.$monetary_unit."/GB/".$time_unit;
          }else{
              $str_unit_cost= "";
              //如果为数量的话,先判断选择了哪些数量单价,再显示
              foreach ($num_type as $num){
                  if($num == 1){
                      $str_unit_cost .= Xphp::$_lang['BILLING_VM'].":".$billing_vm.$monetary_unit."/".$time_unit."<br>";
                  }
                  if($num == 2){
                      $str_unit_cost .= Xphp::$_lang['BILLING_FS'].":".$billing_fs.$monetary_unit."/".$time_unit."<br>";
                  }
                  if($num == 3){
                      $str_unit_cost .= Xphp::$_lang['BILLING_DB'].":".$billing_db.$monetary_unit."/".$time_unit."<br>";
                  }
              }
          }
         return $str_unit_cost;
      }
      
      
      
      
     /**
      * 得到购买个数或者容量的字符串
      * @param unknown $billing_mode 策略模式1按容量 2按数量
      * @param int $capacity_size 容量大小
      * @param json $use_num 个数
      * @param json $num_type 数量类型
      * @author liushuai@vinchin.com
      * @return string
      * 
      */
      public function getUseNumStr($billing_mode,$capacity_size,$use_num,$num_type){
          $use_num_list = json_decode($use_num,true);
          $num_type_list= json_decode($num_type,true);
          if($billing_mode==1){
              $str_use_num = Xphp::$_lang['BILLING_CAPACITY'].":".$capacity_size."GB";
          }else{
              $str_use_num="";
              //如果为数量的话,先判断选择了哪些数量单价,再显示
              foreach ($num_type_list as $num){
                  if($num == 1){
                      $str_use_num .= Xphp::$_lang['BILLING_VM'].":".$use_num_list['num_vm']."".Xphp::$_lang['BILLING_UNIT_ONE'].""."<br>";
                  }
                  if($num == 2){
                      $str_use_num .= Xphp::$_lang['BILLING_FS'].":".$use_num_list['num_fs']."".Xphp::$_lang['BILLING_UNIT_ONE'].""."<br>";
                  }
                  if($num == 3){
                      $str_use_num .= Xphp::$_lang['BILLING_DB'].":".$use_num_list['num_db']."".Xphp::$_lang['BILLING_UNIT_ONE'].""."<br>";
                  }
              }
          }
          return $str_use_num;
          
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
          $MonetaryStr = $unit['MONETARY_UNIT'][$result[0]['monetary_unit']][0];
          return $MonetaryStr;
      }
      
      
      
      /**
       * 得到此租户的一些基本数据,处理后返回给页面
       * @param unknown $params tenant_uuid
       * @author liushuai@vinchin.com
       * @return array
       */
      public function getMsgOfTenant($params){
          $tenant_uuid = $params['tenant_uuid'];
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
      
      /**
       * 获得更新后的时间
       * @param unknown $params time_unit:为1为月 2为年  value:正整数 start_time:开始时间 类型为"Y-m-d H:i:s"
       * @author liushuai@vinchin.com;
       * @return str 时间
       */
      public function pGetNewTime($params){
          $time_unit = $params['time_unit'];
          $value = $params['value'];
          if(empty($value)){
              $value = 0;
          }
          $start_time = $params['end_time'];
          $this->paramsCheck($time_unit,$start_time);
          if($time_unit ==1){
              $new_time = date("Y-m-d H:i:s", strtotime("$start_time +$value month"));
          }else{
              $new_time = date("Y-m-d H:i:s", strtotime("$start_time +$value year"));
          }
          return json_encode($new_time);
      }
    
    
    /**
     * 获取租户使用容量
     * @param unknown $tenant_uuid
     * @author liushuai@vinchin.com
     * @return int
     */
    public function pGetTenantCapacity($tenant_uuid){
        $UsersHandler = Xphp::instance("UsersHandler");
        $get_user_uuid_list = $UsersHandler->pGetUseruuidList($tenant_uuid);
        $string = implode("','",$get_user_uuid_list);
        //计算每个用户大小的sql语句
        $sql = "select sum(write_size) as total_size from bd_backup_timepoint where user_uuid in";
        $sql .=" ("."'"."$string"."'".")";
        //计算当前租户下每个用户用的资源大小的和
        $result_size = $this->dbSelect($sql);
        $info_size = $result_size[0]['total_size'];
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
        $UsersHandler = Xphp::instance("UsersHandler");
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
     * 获取bd_billing费用策略表的最大id
     * @author liushuai@vinchin.com
     * @return number
     */
    public function getNumOfName(){
        $sql = "select max(id) as max_id from bd_billing";
        $result = $this->dbSelect($sql);
        $max_id = intval($result[0]['max_id']);
        $next_id = $max_id+1;
        return json_decode($next_id);
    }
    
    
    /**
     * 获取费用总价--只针对每天计费方式使用
     * @param billing_uuid,tenant_uuid,使用数量，使用容量
     * @author liushuai@vinchin.com
     * @return;
     */
    public function getBillingTotalToday($billing_uuid,$tenant_uuid,$use_num,$use_capacity){
        //先查询此租户选用的费用策略方式
       $sql_billing = "select billing_mode,num_type,unit_cost from bd_billing where billing_uuid = ?";
       $result_billing = $this->dbSelect($sql_billing,array($billing_uuid));
       $unit_cost = json_decode($result_billing[0]['unit_cost'],true);
       
       $billing_mode = $result_billing[0]['billing_mode'];
       $billing_total = 0;
       //判断是按容量还是按数量
       if($billing_mode == 1){  //按容量
           //先换算单位换算成GB
           $use_capacity_GB =round($use_capacity/1024/1024/1024,2);
           $billing_total = round($unit_cost['billing_capacity']*$use_capacity_GB,2);
       }else{
           $billing_total = round($unit_cost['billing_vm']*$use_num['num_vm']+$unit_cost['billing_fs']*$use_num['num_fs']+$unit_cost['billing_db']*$use_num['num_db'],2);
       }
       return $billing_total;
       
    }
    
    
    
    /**
     * 监控-生成按量订单
     */
    public function checkBillingDetails(){
        //先获取已分配费用策略且费用策略为3按量的的租户uuid集合
        $sql_mt = "select mt.tenant_uuid as tenant_uuid_list,mt.create_time as time,bd.billing_uuid,bd.billing_type from mt_tenant_billing as mt join bd_billing as bd on mt.billing_uuid = bd.billing_uuid where bd.billing_type =3 and bd.lock_flag=1";
        $result_mt = $this->dbSelect($sql_mt);
        //今天的日期
        $today = date("Y-m-d");
        $today_now = date("Y-m-d H:i:s");
        $today_sort = date("Y-m-d H:i:s",strtotime($today));//今日0点
        $yesterday_sort = date("Y-m-d H:i:s",strtotime("$today_sort-1 day"));//昨日0点
        $tenant_uuid_list = array();
        foreach ($result_mt as $op){
            $tenant_time = date("Y-m-d",strtotime($op['time']));
            if($today == $tenant_time){  //判断是否和今天的时间相等,如果相等就结束本次循环
                continue;
            }else{
                $tenant_uuid_list[] = array(
                    'tenant_uuid' => $op['tenant_uuid_list'],
                    'billing_uuid' => $op['billing_uuid'],
                    'billing_type' => $op['billing_type'],
                );
            }
        }
        //如果生成日期不等于今天且没有关于昨天日期的数据,则生成昨天的订单
        foreach ($tenant_uuid_list as $p){
            $sql_update_time = "select count(update_time) as count_time from bd_billing_details where tenant_uuid =? and update_time>?";
            $result_update_time = $this->dbSelect($sql_update_time,array($p['tenant_uuid'],$today_sort));
            if($result_update_time[0]['count_time'] == 0){
                //生成昨天的消费订单
                $use_num_array = $this->pGetTenantUseNum($p['tenant_uuid']);
                $use_num = json_encode($use_num_array);
                $use_num="'".$use_num."'";
                $capacity_size_int = $this->pGetTenantCapacity($p['tenant_uuid']);
                $Utils = Xphp::instance("Utils");
                $capacity_size = $Utils->calSize($capacity_size_int,true);
                $total = $this->getBillingTotalToday($p['billing_uuid'], $p['tenant_uuid'], $use_num_array, $capacity_size_int);
                $sql_details = "insert into bd_billing_details (tenant_uuid,billing_uuid
,billing_type,create_time,end_time,update_time,use_num,capacity_size,billing_total) values (?,?,?,?,?,?,$use_num,?,?)";
                $sql_params = array($p['tenant_uuid'],$p['billing_uuid'],$p['billing_type'],$yesterday_sort,$today_sort,$today_now,$capacity_size,$total);
                $result_details = $this->dbQuery($sql_details,$sql_params);
            }
        }
    }
    
    /**
     * 定时发送邮箱提示
     */
    public function sendEmailToTenant(){
        $sql_mt = "select billing_uuid,tenant_uuid from mt_tenant_billing";
        $result_mt = $this->dbSelect($sql_mt);
        //获取时间段
//         $today = date("Y-m-d H:i:s");//每天时间
        $today = date("2021-4-22 03:20:00");//每天时间-测试用
        foreach ($result_mt as $op){
            $sql_billing = "select inform,inform_period from bd_billing where billing_uuid = ? and lock_flag = 1";
            $result_billing = $this->dbSelect($sql_billing,array($op['billing_uuid']));
            
            if($result_billing[0]['inform'] ==2){
                continue;
            }else{
                //执行发送邮件的代码
                $inform_period = $result_billing[0]['inform_period'];
                $result = $this->getDate($today, $inform_period);
                if($result){
                    $this-> getEmailContent($op['tenant_uuid']);
                }else{
                    continue;
                }
                
            }
        }
    }
    
    
    /**
     * 输入一个日期，判断这个日期是否在指定时间段内，间隔半小时（需要和监控时间搭配）
     * @param unknown $date 输入日期
     * @return boolean true在这个时间段，false不在这个时间段
     */
        public function getDate($date,$flag){
            $date = date("Y-m-d H:i:s",strtotime($date));
            $result =false;
            switch ($flag){
                case 1://每天
                    $start_day = date("Y-m-d 03:00:00",strtotime($date));
                    $end_day = date("Y-m-d 03:30:00",strtotime($date));
                    if($date > $start_day && $date < $end_day){
                        $result = true;
                    }
                    break;
                case 2://每周
                    if(date("w",strtotime($date))==1){//每个星期一
                        $start_day = date("Y-m-d 03:00:00",strtotime($date));
                        $end_day = date("Y-m-d 03:30:00",strtotime($date));
                        if($date > $start_day && $date < $end_day){
                            $result = true;
                        }
                    }
                    break;
                 case 3://每月一号
                     $start_day = date("Y-m-01 03:00:00",strtotime($date));
                     $end_day = date("Y-m-01 03:30:00",strtotime($date));
                     if($date > $start_day && $date < $end_day){
                         $result = true;
                     }
                     break;
                 case 4://每年一月一号
                     $start_day = date("Y-01-01 03:00:00",strtotime($date));
                     $end_day = date("Y-01-01 03:30:00",strtotime($date));
                     if($date > $start_day && $date < $end_day){
                         $result = true;
                     }
                     break;
            }
            return $result;
        }
    
    
    /**
     * 编辑邮箱内容
     * @author liushuai@vinchin.com
     * @return 
     */
    public function getEmailContent($tenant_uuid){
        $params = array(
            'tenant_uuid' => $tenant_uuid
        );
        //获得今天的日期
        $today = date("Y-m-d H:i:s");
        //先得到租户的一些信息
        $date_list = $this->getMsgOfTenant($params);
        $tenant_info = json_decode($date_list,true);
        //再得到租户的费用表
        $billing_info = $this->getTenantBillingArray($tenant_uuid);
        if(!$billing_info){//如果读取的费用详情为空则退出
            return;
        }
        $billing_type = "";
        if($tenant_info['billing_type']==1){
            $billing_type .=Xphp::$_lang['BILLING_PAY_MONTH']."-";
        }elseif ($tenant_info['billing_type']==2){
            $billing_type .=Xphp::$_lang['BILLING_PAY_YEAR']."-";
        }else{
            $billing_type .=Xphp::$_lang['BILLING_PAY_SIZE']."-";
        }
        if($tenant_info['billing_mode']==1){
            $billing_type .=Xphp::$_lang['BILLING_PAY_CAPACITY'];
        }else{
            $billing_type .=Xphp::$_lang['BILLING_PAY_NUM'];
            }
        if ($tenant_info['billing_type']==1 ||$tenant_info['billing_type']==2){
            if($tenant_info['billing_mode']==1){
                $titleChange =Xphp::$_lang['BILLING_BUY_CAPACITY'];
            }else{
                $titleChange =Xphp::$_lang['BILLING_BUY_NUM'];
            }
        }else{
            if($tenant_info['billing_mode']==1){
                $titleChange =Xphp::$_lang['BILLING_USE_CAPACITY'];
            }else{
                $titleChange =Xphp::$_lang['BILLING_USE_NUM'];
            }
        }
        $content = "";
        foreach ($billing_info as $op){
            $info = 
                "<tr><td>".$op['start_time']."</td>".
                "<td>".$op['end_time']."</td>".
                "<td>".$op['num']."</td>".
                "<td>".$op['unitCost']."</td>".
                "<td style='color:red;'>".$op['billingtotalStr']."</td></tr>";
            $content .= $info;
        }
            
        //开始往html中写入内容
        $systemHandler = Xphp::instance('SystemHandler');
        $language = $systemHandler->getSystemLang(); //获取系统语言
        if($language == "zh-cn" || $language == "zh-tw"){
            $message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-billing-report.html');
        }else{
            $message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-billing-report-en.html');
        }
        $message = str_replace('tenantName', $tenant_info['tenant_name'], $message);
        $message = str_replace('billingType', $billing_type, $message);
        $message = str_replace('billingTotal', $tenant_info['billing_total'], $message);
        $message = str_replace('startTime', $tenant_info['start_time'], $message);
        $message = str_replace('endTime', $tenant_info['end_time'], $message);
        $message = str_replace('useDay', $tenant_info['use_day'], $message);
        $message = str_replace('useNum', $tenant_info['use_num'], $message);
        $message = str_replace('useCapacity', $tenant_info['use_capacity'], $message);
        $message = str_replace('titleChange', $titleChange, $message);
        $message = str_replace('tabaleContent', $content, $message);
        $message = str_replace('reportTime', $today, $message);
        //检查是否配置了邮箱，如果没有邮箱则退出，如果有邮箱则发送
        $result_email = $this->getTenantEmailAddress($tenant_uuid);
        if($result_email){//如果邮箱不存在返回false，存在返回邮箱
            $this->sendUnifyEmail($result_email, $message);
        };

        
    }
    
    
    /**
     * 得到租户的费用详情
     * @param unknown $tenant_uuid
     * @author liushuai@vinchin.com
     * @return array
     */
    public function getTenantBillingArray($tenant_uuid){
        $sql_details = "select billing_type,billing_uuid from bd_billing_details where tenant_uuid = ? order by update_time desc limit 1";
        $resule_details = $this->dbSelect($sql_details,array($tenant_uuid));
        //如果没有查询到值 ,可能每天计费时,第一条还没有生成订单,直接返回空即可
        if(empty($resule_details)){
            return false;
        }
        //得到当前租户使用的费用策略
        $billing_uuid = $resule_details[0]['billing_uuid'];
        $billing_type = $resule_details[0]['billing_type'];
        $sql_billing = "select billing_mode,num_type from bd_billing where billing_uuid = ?";
        $result_billing = $this->dbSelect($sql_billing,array($billing_uuid));
        $billing_mode = $result_billing[0]['billing_mode'];
        $num_type = $result_billing[0]['num_type'];
        
        $sql_one_details = "select * from bd_billing_details where tenant_uuid = ? order by update_time";
        $resule_one_details = $this->dbSelect($sql_one_details,array($tenant_uuid));
        $info = array();
        foreach ($resule_one_details as $op){
            $start_time = $op['create_time'];
            $end_time = $op['end_time'];
            $billing_uuid = $op['billing_uuid'];
            $monetaryStr = $this->getMonetaryUnitStr($billing_uuid);
            $billing_total = $op['billing_total'];
            if(empty($billing_total)){
                $billing_total = 0;
            };
            $info[]= array(
                'start_time' => $start_time,
                'end_time' => $end_time,
                'num' => $this->getUseNumStr($billing_mode, $op['capacity_size'], $op['use_num'], $num_type),
                'unitCost' => $this->getUnitCostStr($billing_uuid),//单价
                'billingtotalStr' => $billing_total.$monetaryStr,
                'billingtotalNum' => $billing_total,
            );
        }
        return $info;
    }
    
    /**
     * 统一发送报表通知邮件
     * @param string $title
     * @param string $content
     */
    public function sendUnifyEmail($email, $content){
        $today = date("Y-m-d");
        $attachment = "";
        $email = array($email);
        $params = array(
            'email' => $email,
            'title' => $today.Xphp::$_lang['BILLING_EMAIL_TITLE'],
            'info' => $content,
            "attachment" => $attachment
        );
       
        $userHandler = Xphp::instance('UsersHandler');
        $userHandler->sendEmail($params);
        
    }
    
    
    /**
     * 获取租户邮箱
     * @param unknown $tenant_uuid
     * @author liushuai@vinchin.com
     * @return 邮箱不存在 false
     * @return 邮箱存在返回邮箱地址
     */
    public function getTenantEmailAddress($tenant_uuid){
        $sql_admin = "select admin_uuid from bd_tenant where tenant_uuid = ?";
        $result_admin = $this->dbSelect($sql_admin,array($tenant_uuid));
        $admin_uuid = $result_admin[0]['admin_uuid'];
        //获取租户管理员的邮箱
        $sql_tenant_email = "select email from bd_user where user_uuid =?";
        $result_tenant_email = $this->dbSelect($sql_tenant_email,array($admin_uuid));
        if(empty($result_tenant_email)){
            return false;
        }else{
            $email = $result_tenant_email[0]['email'];
            return $email;
        }
    }
    
    
   
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
     * 删除租户费用策略
     * @param unknown $tenant_uuid 租户uuid
     * @author liushuai@vinchin.com
     * @return true 成功   false失败
     */
    public function pDelTenant($tenant_uuid){
        if(empty($tenant_uuid)) return true;
        //先删除关联表
        $sql_mt = "delete from mt_tenant_billing where tenant_uuid = ?";
        $result_mt = $this->dbExec($sql_mt,array($tenant_uuid));
        //删除费用详情表
        $sql_details = "delete from bd_billing_details where tenant_uuid = ?";
        $result_details = $result_mt && $this->dbExec($sql_details,array($tenant_uuid));
        if($result_details){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 得到select货币的option字符串
     * @author liushuai@vinchin.com
     * @return string
     */
    public function pGetOptionOfUnit(){
        $unit = include APP_PATH . "platform/MonetaryUnitArray.php";
        $str_option = "";
        $list = $unit['MONETARY_UNIT'];
        $count = count($list);
        foreach ($list as $i=>$val){
            $option = '<option data-tokens="'.$val[0].'" value="'.$i.'">'.$val[0].' | '.$val[1].'</option>';
            $str_option .=$option;
        }
        return json_encode($str_option);
    }
    
    
    //封装导出excel函数
    public function my_export($expTitle,$expCellName,$expTableData){
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
       
        include_once("../tools/PHPExcel.php");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for($i=0;$i<$dataNum;$i++){
            for($j=0;$j<$cellNum;$j++){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$j]);
            }
        }
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }   
    
    /**
     * 导出费用详情列表
     */
    public function exportExcel($params){
        $tenant_uuid = $params['list'];
        $today = date("Y-m-d H:i:s");
        $sql_details = "select billing_type,billing_uuid from bd_billing_details where tenant_uuid = ? order by update_time desc limit 1";
        $resule_details = $this->dbSelect($sql_details,array($tenant_uuid));
        //如果没有查询到值 ,可能每天计费时,第一条还没有生成订单,直接返回空即可
        if(empty($resule_details)){
            $xlsCell  = array(
                array('startTime',Xphp::$_lang['BILLING_START_TIME']),
                array('endTime',Xphp::$_lang['BILLING_END_TIME']),
                array('size',Xphp::$_lang['BILLING_CAPACITY_NUM']),
                array('Unit',Xphp::$_lang['BILLING_UNIT_COST']),
                array('total',Xphp::$_lang['BILLING_TOTAL']),
            );
            $this->my_export(Xphp::$_lang['BILLING_DETAILS']."_".$today,$xlsCell, $SellList = array());
            return;
        }
        //得到当前租户使用的费用策略
        $billing_uuid = $resule_details[0]['billing_uuid'];
        $monetaryStr = $this->getMonetaryUnitStr($billing_uuid);
        $billing_type = $resule_details[0]['billing_type'];
        $sql_billing = "select billing_mode,num_type from bd_billing where billing_uuid = ?";
        $result_billing = $this->dbSelect($sql_billing,array($billing_uuid));
        $billing_mode = $result_billing[0]['billing_mode'];
        $num_type = $result_billing[0]['num_type'];
        $sql_one_details = "select * from bd_billing_details where tenant_uuid = ? order by update_time desc";
        $resule_one_details = $this->dbSelect($sql_one_details,array($tenant_uuid));
        $SellList = array();
        foreach($resule_one_details as $op){
            $start_time = $op['create_time'];
            $end_time = $op['end_time'];
            $billing_uuid = $op['billing_uuid'];
           
            $billing_total = $op['billing_total'];
            if(empty($billing_total)){
                $billing_total = 0;
            };
            $SellList[]= array(
                $start_time,
                $end_time,
                str_replace('<br>',' ',$this->getUseNumStr($billing_mode, $op['capacity_size'], $op['use_num'], $num_type)),
                str_replace('<br>',' ',$this->getUnitCostStr($billing_uuid)),//单价
                $billing_total,
                
            );
        }
        $xlsName  = Xphp::$_lang['BILLING_DETAILS'];
        if($billing_type ==3){
            if($billing_mode ==1){
                $sizeStr = Xphp::$_lang['BILLING_USE_CAPACITY'];
            }else{
                $sizeStr = Xphp::$_lang['BILLING_USE_NUM'];
            }
        }else{
            if($billing_mode ==1){
                $sizeStr = Xphp::$_lang['BILLING_BUY_CAPACITY'];
            }else{
                $sizeStr = Xphp::$_lang['BILLING_BUY_NUM'];
            }
        };
        $xlsCell  = array(
            array('startTime',Xphp::$_lang['BILLING_START_TIME']),
            array('endTime',Xphp::$_lang['BILLING_END_TIME']),
            array('size',$sizeStr),
            array('Unit',Xphp::$_lang['BILLING_UNIT_COST']),
            array('total',Xphp::$_lang['BILLING_TOTAL'].'('.$monetaryStr.')'),
        );
        ob_clean();
        $rs = $this->my_export($xlsName,$xlsCell,$SellList);
    }
    
    
    
    
    
}
?>