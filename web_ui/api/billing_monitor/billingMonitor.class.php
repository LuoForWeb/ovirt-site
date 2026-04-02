<?php
/******************************************* 
** 订单费用生成以及费用邮件通知
** 
** @author      liushuai@vinchin.com 
** @date         2021-4-20
********************************************/
class billingMonitor extends OPHandler{
     
    
    //存储监控进程号
    protected $BillingPid = 0;
    //报表通知进程号
    protected $reportPid = 0;
    
    //初始化进程VPDO
    protected $billingVPDOObj;
    
    public function __construct(){
        $this->insertIntoBillingDetails(); 
        $this->sendEmailFork(); 
        $this->writeLog('start report notice monitor!!!');
        
    }
    
    /**
     * 按量每天插入一条计费记录
     */
    private function insertIntoBillingDetails(){
        $this-> writeLog("start billing monitor!");
        $pid = pcntl_fork();//此函数创建进程,返回的值如果有值,则为子进程的pid号,如果为0则为子进程
        //父进程和子进程都会执行下面代码
        if($pid == -1){
            die($this->writeLog("Create billing monitor failed!"));
        }else if($pid){
            //父进程得到子进程的好,这是父进程的执行程序,父进程不需要执行任何程序,则
            $this->BillingPid = $pid;//把子进程的号存储起来
        }else{//另外一种情况就是$pid为0的情况,为0 则是子进程执行的逻辑
                $this->writeLog("Create billing monitor process success!");
                while (true){
                    $this->startHostMonitor();
                    $this->checkBillingDetails();//循环执行函数
                    $this->writeLog("sleep!!!!!!!!!!!!!!");
                    sleep(1800);
                }
            }
    }
    
    
    /**
     * 发送邮件
     */
    private function sendEmailFork(){
        $this-> writeLog("start billingEmai monitor!");
        $pid = pcntl_fork();//此函数创建进程,返回的值如果有值,则为子进程的pid号,如果为0则为子进程
        //父进程和子进程都会执行下面代码
        if($pid == -1){
            die($this->writeLog("Create billingEmai monitor failed!"));
        }else if($pid){
            //父进程得到子进程的好,这是父进程的执行程序,父进程不需要执行任何程序,则
            $this->reportPid = $pid;//把子进程的号存储起来
        }else{//另外一种情况就是$pid为0的情况,为0 则是子进程执行的逻辑
            $this->writeLog("Create billingEmai monitor process success!");
            while (true){
                $this->startHostMonitor();
                $this->sendEmailToTenant();//循环执行函数
                $this->writeLog("sleep!!!!!!!!!!!!!!");
                sleep(1800);
            }
        }
    }
    
    /**
     * 开始主机监控
     */
    private function startHostMonitor(){
        if(empty($this->billingVPDOObj)){
            $this->billingVPDOObj = new VPDO();
        }
       
    }
   
    
    //  ------------------------------------------------------------  
    /**
     * 监控-生成按量订单
     */
    public function checkBillingDetails(){
        //先获取已分配费用策略且费用策略为3按量的的租户uuid集合
        $sql_mt = "select mt.tenant_uuid as tenant_uuid_list,mt.create_time as time,bd.billing_uuid,bd.billing_type from mt_tenant_billing as mt join bd_billing as bd on mt.billing_uuid = bd.billing_uuid where bd.billing_type =3 and bd.lock_flag=1";
        $result_mt = $this->billingVPDOObj->sqlQuery($sql_mt,array(),true);
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
            $result_update_time = $this->billingVPDOObj->sqlQuery($sql_update_time,array($p['tenant_uuid'],$today_sort),true);
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
                $result_details = $this->billingVPDOObj->sqlQuery($sql_details,$sql_params,false);
            }
        }
    }
    
    
    /**
     * 获取租户使用容量
     * @param unknown $tenant_uuid
     * @author liushuai@vinchin.com
     * @return int
     */
    public function pGetTenantCapacity($tenant_uuid){
        $get_user_uuid_list = $this->pGetUseruuidList($tenant_uuid);
        $string = implode("','",$get_user_uuid_list);
        //计算每个用户大小的sql语句
        $sql = "select sum(write_size) as total_size from bd_backup_timepoint where user_uuid in";
        $sql .=" ("."'"."$string"."'".")";
        //计算当前租户下每个用户用的资源大小的和
        $result_size = $this->billingVPDOObj->sqlQuery($sql,array(),true);
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
        $get_user_uuid_list = $this->pGetUseruuidList($tenant_uuid);//得到用户uuid列表
        $string = implode("','",$get_user_uuid_list);
        
        //获取虚拟机timepoint_uuid集合
        $sql_vm_time_list = "select timepoint_uuid from bd_backup_timepoint where module_type = 2 and user_uuid in ";
        $sql_vm_time_list .= " ("."'"."$string"."'".")";
        $result_vm_time_list = $this->billingVPDOObj->sqlQuery($sql_vm_time_list,array(),true);
        $vm_timepoint_list = array();
        foreach ($result_vm_time_list as $op){
            $vm_timepoint_list[] = $op['timepoint_uuid'];
        }
        $string_vm = implode("','",$vm_timepoint_list);
        //获取虚文件timepoint_uuid集合
        $sql_fs_time_list = "select timepoint_uuid from bd_backup_timepoint where module_type = 3 and user_uuid in ";
        $sql_fs_time_list .= " ("."'"."$string"."'".")";
        $result_fs_time_list = $this->billingVPDOObj->sqlQuery($sql_fs_time_list,array(),true);
        $fs_timepoint_list = array();
        foreach ($result_fs_time_list as $op){
            $fs_timepoint_list[] = $op['timepoint_uuid'];
        }
        $string_fs = implode("','",$fs_timepoint_list);
        //获取数据库timepoint_uuid集合
        $sql_db_time_list = "select timepoint_uuid from bd_backup_timepoint where module_type = 4 and user_uuid in ";
        $sql_db_time_list .= " ("."'"."$string"."'".")";
        $result_db_time_list = $this->billingVPDOObj->sqlQuery($sql_db_time_list,array(),true);
        $db_timepoint_list = array();
        foreach ($result_db_time_list as $op){
            $db_timepoint_list[] = $op['timepoint_uuid'];
        }
        $string_db = implode("','",$db_timepoint_list);
        
        //查询已经完成备份点的虚拟机数量
        $sql_vm = "select count(distinct vcenter_uuid,vm_uuid) as num_vm from vm_backup_timepoint where timepoint_uuid in";
        $sql_vm .=" ("."'"."$string_vm"."'".")";
        $result_vm = $this->billingVPDOObj->sqlQuery($sql_vm,array(),true);
        $info_num_vm = $result_vm[0]['num_vm'];
        
        //查询已使用的文件数量
        $sql_fs = "select count(distinct agent_uuid) as num_fs from fs_backup_timepoint where fs_timepoint_uuid in";
        $sql_fs .=" ("."'"."$string_fs"."'".")";
        $result_fs = $this->billingVPDOObj->sqlQuery($sql_fs,array(),true);
        $info_num_fs = $result_fs[0]['num_fs'];
        
        //查询已使用的数据库数量
        $sql_db = "select count(distinct agent_uuid) as num_db from db_backup_timepoint where timepoint_uuid in";
        $sql_db .=" ("."'"."$string_db"."'".")";
        $result_db = $this->billingVPDOObj->sqlQuery($sql_db,array(),true);
        $info_num_db = $result_db[0]['num_db'];
        
        $info = array(
            'num_vm' => $info_num_vm,
            'num_fs' => $info_num_fs,
            'num_db' => $info_num_db,
        );
        return $info;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * 根据tenant_uuid获取当前租户下所有的用户uuid列表
     * @param unknown $tenant_uuid
     * @author liushuai@vinchin.com
     * @return array 返回一个用户uuid数组
     */
    public function pGetUseruuidList($tenant_uuid){
        $sql = "select user_uuid from mt_user_tenant where tenant_uuid = ?";
        $result = $this->billingVPDOObj->sqlQuery($sql,array($tenant_uuid),true);
        $user_uuid_list = array();
        if($result){
            foreach ($result as $op){
                $user_uuid_list[] = $op['user_uuid'];
            };
        }
        return $user_uuid_list;
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
        $result_billing = $this->billingVPDOObj->sqlQuery($sql_billing,array($billing_uuid),true);
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
    
//  ------------------------------------------------------------   
    
    
   
    //  ------------------------------------------------------------ 
    
    /**
     * 定时发送邮箱提示
     */
    public function sendEmailToTenant(){
        $sql_mt = "select billing_uuid,tenant_uuid from mt_tenant_billing";
        $result_mt = $this->billingVPDOObj->sqlQuery($sql_mt,array(),true);
        //获取时间段
         $today = date("Y-m-d H:i:s");//每天时间
//         $today = date("2021-4-22 03:20:00");//每天时间-测试用
        foreach ($result_mt as $op){
            $sql_billing = "select inform,inform_period from bd_billing where billing_uuid = ? and lock_flag = 1";
            $result_billing = $this->billingVPDOObj->sqlQuery($sql_billing,array($op['billing_uuid']),true);
            
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
        $language = $this->getSystemLang();
        //开始往html中写入内容
        switch ($language){
            case "zh-cn":
                $message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-billing-report.html');
                //如果是oem
                if(!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], Xphp::$_config['ENTERPRISE'])){
                    $message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-billing-report-oem.html');
                }
                break;
            case "zh-tw":
                $message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-billing-report-tw.html');
                break;
            case "en-us":
                $message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-billing-report-en.html');
                break;
            default:
                $message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-billing-report.html');
                break;
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
        $resule_details = $this->billingVPDOObj->sqlQuery($sql_details,array($tenant_uuid),true);
        //如果没有查询到值 ,可能每天计费时,第一条还没有生成订单,直接返回空即可
        if(empty($resule_details)){
            return false;
        }
        //得到当前租户使用的费用策略
        $billing_uuid = $resule_details[0]['billing_uuid'];
        $billing_type = $resule_details[0]['billing_type'];
        $sql_billing = "select billing_mode,num_type from bd_billing where billing_uuid = ?";
        $result_billing = $this->billingVPDOObj->sqlQuery($sql_billing,array($billing_uuid),true);
        $billing_mode = $result_billing[0]['billing_mode'];
        $num_type = $result_billing[0]['num_type'];
        
        $sql_one_details = "select * from bd_billing_details where tenant_uuid = ? order by update_time";
        $resule_one_details = $this->billingVPDOObj->sqlQuery($sql_one_details,array($tenant_uuid),true);
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
        
        $this->sendEmail($params);
        
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
        $result_admin = $this->billingVPDOObj->sqlQuery($sql_admin,array($tenant_uuid),true);
        $admin_uuid = $result_admin[0]['admin_uuid'];
        //获取租户管理员的邮箱
        $sql_tenant_email = "select email from bd_user where user_uuid =?";
        $result_tenant_email = $this->billingVPDOObj->sqlQuery($sql_tenant_email,array($admin_uuid),true);
        if(empty($result_tenant_email)){
            return false;
        }else{
            $email = $result_tenant_email[0]['email'];
            return $email;
        }
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
        $result_mt = $this->billingVPDOObj->sqlQuery($sql_mt,array($tenant_uuid),true);
        $billing_uuid = $result_mt[0]['billing_uuid'];
        $mt_create_time = $result_mt[0]['create_time'];
        $monetaryStr = $this->getMonetaryUnitStr($billing_uuid);
        //获取费用策略表的数据
        $sql_billing = "select * from bd_billing where billing_uuid =?";
        $result_billing = $this->billingVPDOObj->sqlQuery($sql_billing,array($billing_uuid),true);
        $billing_type = $result_billing[0]['billing_type'];
        $billing_mode = $result_billing[0]['billing_mode'];
        $use_num_list = json_decode($result_billing[0]['num_type'],true);
        //查询租户的租户名
        $sql_tenant_name = "select tenant_name from bd_tenant where tenant_uuid = ?";
        $result_tenant_name = $this->billingVPDOObj->sqlQuery($sql_tenant_name,array($tenant_uuid),true);
        $tenant_name = $result_tenant_name[0]['tenant_name'];
        $info = array();
        //根据类型不容有不同的处理方式
        if($billing_type ==1 || $billing_type ==2){
            //获得租户费用详情表
            $sql_tenant_details = "select * from bd_billing_details where tenant_uuid = ? order by update_time desc limit 1";
            $result_tenant_details = $this->billingVPDOObj->sqlQuery($sql_tenant_details,array($tenant_uuid),true);
            $start_time = $result_tenant_details[0]['create_time'];
            $end_time = $result_tenant_details[0]['end_time'];
            $billing_total = $result_tenant_details[0]['billing_total'];
            //计算出str格式
//             $str_start_time = date("Y",strtotime($start_time)).Xphp::$_lang['BILLING_YEAR'].date("m",strtotime($start_time)).Xphp::$_lang['BILLING_MONTH'].date("d",strtotime($start_time)).Xphp::$_lang['BILLING_DAY_OTHER'];
//             $str_end_time = date("Y",strtotime($end_time)).Xphp::$_lang['BILLING_YEAR'].date("m",strtotime($end_time)).Xphp::$_lang['BILLING_MONTH'].date("d",strtotime($end_time)).Xphp::$_lang['BILLING_DAY_OTHER'];
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
            $result_tenant_details = $this->billingVPDOObj->sqlQuery($sql_tenant_details,array($tenant_uuid),true);
            if(empty($result_tenant_details)){//如果为空值,则还没生成第一天的数据,直接返回空
                $info = array(
                    'tenant_name' => $tenant_name,
                    'billing_type' => $billing_type,
                    'billing_mode' => $billing_mode,
                    'start_time' => $mt_create_time,
                    'end_time' => 'N/A',
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
            $result_tenant_details = $this->billingVPDOObj->sqlQuery($sql_tenant_details,array($tenant_uuid),true);
            $start_time = $result_tenant_details[0]['create_time'];
            $end_time = date("Y-m-d H:i:s");
            
            //计算费用
            $sql_billing_total = "select sum(billing_total) as total from bd_billing_details where tenant_uuid = ?";
            $result_billing_total = $this->billingVPDOObj->sqlQuery($sql_billing_total,array($tenant_uuid),true);
            if(empty($result_billing_total)){
                $billing_total = 0;
            }else{
                $billing_total = $result_billing_total[0]['total'];
            }
            //计算出str格式
//             $str_start_time = date("Y",strtotime($start_time))." 年 ".date("m",strtotime($start_time))." 月 ".date("d",strtotime($start_time))." 日";
//             $str_end_time = date("Y",strtotime($end_time))." 年 ".date("m",strtotime($end_time))." 月 ".date("d",strtotime($end_time))." 日";
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
                'tenant_name' => $tenant_name,
                'billing_type' => $billing_type,
                'billing_mode' => $billing_mode,
                'start_time' => $str_start_time,
                'end_time' => $str_end_time,
                'use_day' => $str_day,
                'use_capacity' => $string_size,
                'use_num' => $string_num,
                'billing_total' => $billing_total.$monetaryStr,
            );
        }
        return json_encode($info);
    }
    /**
     * 发送邮件接口
     * @param unknown $params
     */
    public function sendEmail($params){
        $title = $params['title'];
        $email = $params['email'];
        $info = $params['info'];
        $attachment = $params['attachment'];
        $emailStr = implode($email, ',');
        $p = array(
            'm' => Xphp::$_config['API_MODULE']['Xemail'],
            'f' => 'sendEmail',
            'p' => array(
                'email' => $email,
                'title' => $title,
                'info' => $info,
                'attachment' => $attachment
            )
        );
        //获取邮件配置
        $sql = "select smtp_config from bd_email_notice";
        $emailConf = Xphp::$_config['EMAIL'];
        $data = $this->billingVPDOObj->sqlQuery($sql, array(),true);
        $utils = Xphp::instance('Utils');
        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        
        $pass = $utils->decrypt($smtpConfig['pass']);
        $encryption = intval($smtpConfig['encryption']);
        $encryption = Xphp::$_config['EMAIL_ENCRYPTION_TYPE'][$encryption];
        //直接调用发送邮件接口
        $emailUtils = Xphp::instance('Email');
        $emailConfig = Xphp::$_config['EMAIL'];
        $emailUtils->config($smtpConfig['host'], $smtpConfig['port'], $emailConfig['authentication'],
            $smtpConfig['email'], $pass, $encryption);
        $result = $emailUtils->sendmail($email, $title, $info, $attachment);
        $operate = Xphp::$_lang['WEB_USERS_SEND_EMAIL_TO'] . $emailStr;
        
        if($result){
            $this->writeLog("send Email success!!!!!!!!!!");
            return $this->muOpResult(true, $operate);
        }else {
            $this->writeLog("send Email failed!!!!!!!!!!");
            return $this->muOpResult(false, $operate);
        }
    }
    /**
     * 得到费用的单位
     * @param unknown $billing_uuid
     * @author liushuai@vinchin.com
     * @return string
     */
    public function getMonetaryUnitStr($billing_uuid){
        $sql = "select monetary_unit from bd_billing where billing_uuid = ?";
        $result = $this->billingVPDOObj->sqlQuery($sql,array($billing_uuid),true);
        $unit = include APP_PATH . "platform/MonetaryUnitArray.php";
        $MonetaryStr = $unit['MONETARY_UNIT'][$result[0]['monetary_unit']][1];
        return $MonetaryStr;
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
                    $str_use_num .= Xphp::$_lang['BILLING_VM'].":".$use_num_list['num_vm'].Xphp::$_lang['BILLING_UNIT_ONE']."<br>";
                }
                if($num == 2){
                    $str_use_num .= Xphp::$_lang['BILLING_FS'].":".$use_num_list['num_fs'].Xphp::$_lang['BILLING_UNIT_ONE']."<br>";
                }
                if($num == 3){
                    $str_use_num .= Xphp::$_lang['BILLING_DB'].":".$use_num_list['num_db'].Xphp::$_lang['BILLING_UNIT_ONE']."<br>";
                }
            }
        }
        return $str_use_num;
        
    }
    /**
     * 获取策略类型单价
     * @param unknown $params 一个billing_uuid
     * @author liushuai@vinchin
     * @return str
     */
    public function getUnitCostStr($billing_uuid){
        $sql = "select * from bd_billing where billing_uuid = ?";
        $result = $this->billingVPDOObj->sqlQuery($sql,array($billing_uuid),true);
        $billing_type = $result[0]['billing_type'];
        $monetary_unit = $result[0]['monetary_unit'];
        $billing_mode = $result[0]['billing_mode'];
        $unit_cost = json_decode($result[0]['unit_cost'],true);
        $billing_vm = $unit_cost['billing_vm'];
        $billing_fs = $unit_cost['billing_fs'];
        $billing_db = $unit_cost['billing_db'];
        $billing_capacity = $unit_cost['billing_capacity'];
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
            $str_unit_cost="";
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
    
    //  ------------------------------------------------------------ 
    public function getSystemLang(){
        $sql ="select language from bd_user";
        $data = $this->dbSelect($sql, array());
        
        return $data[0]['language'];
    }
    
}