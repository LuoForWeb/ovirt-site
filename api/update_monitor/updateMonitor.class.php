<?php
use PhpMyAdmin\Config\Descriptions;

/******************************************* 
** 升级检查更新
** 
** @author      liushuai@vinchin.com 
** @date         2021-4-20
********************************************/
class updateMonitor extends OPHandler{
    
    //存储监控进程号
    protected $UpdatePid = 0;
    
    //初始化进程VPDO
    protected $updateVPDOObj;
    
    public function __construct(){
        $this->updateFork(); 
        $this->writeLog('start update monitor!!!');
        
    }
    
    /**
     * 按量每天插入一条计费记录
     */
    private function updateFork(){
        
        $this-> writeLog("start update monitor!");
        $pid = pcntl_fork();//此函数创建进程,返回的值如果有值,则为子进程的pid号,如果为0则为子进程
        //父进程和子进程都会执行下面代码
        if($pid == -1){
            die($this->writeLog("Create update monitor failed!"));
        }else if($pid){
            //父进程得到子进程的好,这是父进程的执行程序,父进程不需要执行任何程序,则
            $this->UpdatePid = $pid;//把子进程的号存储起来
        }else{//另外一种情况就是$pid为0的情况,为0 则是子进程执行的逻辑
                $this->writeLog("Create update process success!");
                while (true){
                    $this->startHostMonitor();
                    $this->checkUpdate();//循环执行函数
                    $this->writeLog("sleep!!!!!!!!!!!!!!");
                    sleep(180);
                }
            }
    }
    
    /**
     * 开始主机监控
     */
    private function startHostMonitor(){
        if(empty($this->updateVPDOObj)){
            $this->updateVPDOObj = new VPDO();
        }
        
    }
    
    //检查更新逻辑
    private function checkUpdate(){
        //先查询数据库 看是否有开启自动检测
        $sql = "select settings_content from bd_system_settings where settings_type = 5";
        $resultsql =  $this->updateVPDOObj->sqlQuery($sql,array(),true);
        if(empty($resultsql)){
            //如果返回的是空的 说明没有设置检查更新 则直接退出
            return;
        }
        //得到内容
        $settings_content = $resultsql[0]['settings_content'];
        $settings_content = json_decode($settings_content,true);
        //得到是否开启检查更新
        $checkflag = $settings_content['checkflag'];
        //得到是否勾选协议
        $TCPflag = $settings_content['TCPflag'];
        //得到周期
        //no 不提示, day 每天一次, week 每周一次, month 每月一次;
        $timeperiod = $settings_content['timeperiod'];
        if($timeperiod == "no"){
            return;
        }
        
        //这里先判断是否符合时间
        $checkTime = $this->getDate($timeperiod);
        if(!$checkTime){
            //时间不符合要求
            return; 
        }
        
        $systemHandler = Xphp::instance("SystemHandler");
        $result = $systemHandler->getDataFromServer();
        //处理得到的结果
        $infoList = json_decode($result,true);
        if(!$infoList['re']){//如果返回失败则不提示
            return;
        }else{
            //如果返回成功则添加系统日志
            //查询未响应的检查更新系统告警
            $sqlcheck = "select system_alarm_id, alarm_time from bd_system_alarm where description_key = ? and solved_flag = ?";
            $resultcheck = $this->updateVPDOObj->sqlQuery($sqlcheck,array("SYSTEM_ALARM_UPDATE_ONLINE_SUCCESS",2),true);
            if(!empty($resultcheck)){
                //如果存在未响应的 则只更新时间
                $updateTime = "update bd_system_alarm set alarm_time = ? where system_alarm_id = ?";
                $resultupdateTime = $this->updateVPDOObj->sqlQuery($updateTime,array(date("Y-m-d H:i:s"), $resultcheck[0]['system_alarm_id']),false);
                return;
            }else{
                //如果不存在相关记录 则插入新的一条
                $alarm_level = 1;
                $description_key = "SYSTEM_ALARM_UPDATE_ONLINE_SUCCESS";
                $alarm_time = date("Y-m-d H:i:s");
                $solved_flag = 2;
                $email_send_flag = 2;
                $sms_send_flag = 2;
                
                $sqlInsert = "insert into bd_system_alarm(alarm_level,description_key,alarm_time,solved_flag,email_send_flag,sms_send_flag) values(?,?,?,?,?,?)";
                $resultInsert = $this->updateVPDOObj->sqlQuery($sqlInsert,array($alarm_level,$description_key,$alarm_time,$solved_flag,$email_send_flag,$sms_send_flag),false);
                return;
            }
        }
    }
    
    
    /**
     * 输入一个日期，判断这个日期是否在指定时间段内，间隔半小时（需要和监控时间搭配）
     * @param unknown $date 输入日期
     * @return boolean true在这个时间段，false不在这个时间段
     */
    public function getDate($flag){
//         $date = date("Y-m-d H:i:s");
        $date = date("2021-4-22 03:20:00");//每天时间-测试用
        $date = date("Y-m-d H:i:s",strtotime($date));
        $result =false;
        switch ($flag){
            case "day"://每天
                $start_day = date("Y-m-d 03:00:00",strtotime($date));
                $end_day = date("Y-m-d 03:30:00",strtotime($date));
                if($date > $start_day && $date < $end_day){
                    $result = true;
                }
                break;
            case "week"://每周
                if(date("w",strtotime($date))==1){//每个星期一
                    $start_day = date("Y-m-d 03:00:00",strtotime($date));
                    $end_day = date("Y-m-d 03:30:00",strtotime($date));
                    if($date > $start_day && $date < $end_day){
                        $result = true;
                    }
                }
                break;
            case "month"://每月一号
                $start_day = date("Y-m-01 03:00:00",strtotime($date));
                $end_day = date("Y-m-01 03:30:00",strtotime($date));
                if($date > $start_day && $date < $end_day){
                    $result = true;
                }
                break;
//             case 4://每年一月一号
//                 $start_day = date("Y-01-01 03:00:00",strtotime($date));
//                 $end_day = date("Y-01-01 03:30:00",strtotime($date));
//                 if($date > $start_day && $date < $end_day){
//                     $result = true;
//                 }
//                 break;
        }
        return $result;
    }
    
    
    
    
    
    
    
    
    
   
    
}