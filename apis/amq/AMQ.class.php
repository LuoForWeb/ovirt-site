<?php
/******************************************* 
** 消息推送处理类
** 
** @author       
** @date         2018-04-08
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class AMQ extends OPHandler{
    //推送配置数组,注意里面需要添加消息名称
    protected $settings = array();
    
    //任务列表
    protected $jobLists = NULL;
    protected $newJobLists = NULL;
    
    //任务告警列表
    protected $alarmJobList = NULL;
    
    //系统告警列表
    protected $alarmSystemList = NULL;
    
    //任务推送进程号
    protected $jobPid = 0;
    
    //告警推送进程号
    protected $alarmPid = 0;
    
    //单独实例化每个进程数据库连接
    protected $amqVPDOObj;
    protected $jobVPDOObj;
    protected $alarmVPDOObj;
    
    //瞬时恢复虚拟机创建标志
    protected $instanVMFlagList = array();
    
    public function __construct(){
        $this->setUserInfo();
        $this->writeLog("Start message push!");
        //获取推送配置
        while(true){
            //TODO,处理配置改变，需要重连的情况，先停止推送，再开启推送
            $newSettings = $this->getSettings();
            if(md5(json_encode($newSettings)) != md5(json_encode($this->settings))){
                $this->stopPush();
            }
            $this->settings = $newSettings;
            if($this->settings['push_flag'] == Xphp::$_config['FLAG']['SET']){
                //如果开启推送
                $this->startPush();
            }else{
                //如果停止推送
                $this->stopPush();
            }
            sleep(Xphp::$_config['MQ_VALUE']['PUSHSTOP']);
        }
    }
    
    /**
     * 设置用户信息,socket连接会用到
     */
    private function setUserInfo(){
        $sql = "select user_uuid, user_name from bd_user where user_type = ? limit 0, 1";
        $data = $this->dbSelect($sql, array(Xphp::$_config['USERTYPE']['manager']));
        Xphp::$_user = array(
            "username" => $data[0]['user_name'],
            "useruuid" => $data[0]['user_uuid'],
            "usertype" => Xphp::$_config['USERTYPE']['manager'],
        );
    }
    
    public function __destruct(){
        $this->writeLog("Stop message push!");
    }
    
    /**
     * 开启推送
     */
    private function startPush(){
        if(!$this->jobPid){
            $this->startJobPush();
        }
        if(!$this->alarmPid){
            $this->startAlarmPush();
        }
    }
    
    /**
     * 停止推送
     */
    private function stopPush(){
        //停止任务推送
        if($this->jobPid){
            $result = posix_kill($this->jobPid, SIGKILL);
            $resultStr =  $result? "success" : "failure";
            $this->writeLog("Stop job message push process " . $resultStr);
            if($result){
                //停止,重新初始化
                $this->jobPid = 0;
                $this->jobLists = NULL;
            }
        }
        //停止告警推送
        if($this->alarmPid){
            $result = posix_kill($this->alarmPid, SIGKILL);
            $resultStr =  $result? "success" : "failure";
            $this->writeLog("Stop alarm message push process " . $resultStr);
            if($result){
                $this->alarmPid = 0;
                $this->alarmJobList = NULL;
                $this->alarmSystemList = NULL;
            }
        }
    }
    
    /**
     * 开启任务推送进程
     */
    private function startJobPush(){
        $pid = pcntl_fork();
        //父进程和子进程都会执行下面代码
        if ($pid == -1) {
            //错误处理：创建子进程失败时返回-1.
            die($this->writeLog("Create job process failure"));
        } else if ($pid) {
            //父进程会得到子进程号，所以这里是父进程执行的逻辑
            $this->jobPid = $pid;
//             pcntl_wait($status); //等待子进程中断，防止子进程成为僵尸进程。
        } else {
            //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
            $this->writeLog("Create job process success");
            
            while (true){
                $this->jobMsg();
                sleep(Xphp::$_config['MQ_VALUE']['JOBSTOP']);
            }
        }
    }
    
    /**
     * 开启告警推送进程
     */
    private function startAlarmPush(){
        $pid = pcntl_fork();
        //父进程和子进程都会执行下面代码
        if ($pid == -1) {
            //错误处理：创建子进程失败时返回-1.
            die($this->writeLog("Create alarm process failure"));
        } else if ($pid) {
            //父进程会得到子进程号，所以这里是父进程执行的逻辑
            $this->alarmPid = $pid;
//             pcntl_wait($status); //等待子进程中断，防止子进程成为僵尸进程。
        } else {
            //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
        	$this->writeLog("Create alarm process success");
            while (true){
                $this->alarmMsg();
                sleep(Xphp::$_config['MQ_VALUE']['ALARMSTOP']);
            }
        }
    }
    
    /**
     * 获取消息推送配置
     * 暂时只支持一个消息推送配置,默认关闭
     */
    private function getSettings(){
    	if(empty($this->amqVPDOObj)){
    		$this->amqVPDOObj = new VPDO();
    	}
    	
        $sql = "select protocol, ip_domain, port, push_flag, push_type, mode, user_name, password from bd_message_push";
        $data = $this->amqVPDOObj->sqlQuery($sql, array(), true);
        
        if(empty($data[0])){
            exit ($this->writeLog("Get message push settings error"));
        }
        $utils = Xphp::instance('Utils');
        $settings = array(
            'protocol' => intval($data[0]['protocol']),
            'ip_domain' => $data[0]['ip_domain'],
            'port' => $data[0]['port'],
            'push_flag' => intval($data[0]['push_flag']),
            'push_type' => intval($data[0]['push_type']),
            'mode' => intval($data[0]['mode']),
            'user_name' => $data[0]['user_name'],
            'password' => $utils->decrypt($data[0]['password']),
            'msg_name' => ''
        );
        
//         $this->writeLog("get push msg settings success**************");
        return $settings;
    }
    
    /**
     * 调用消息发送
     * @param array $msg
     * @param int $type
     */
    private function unifySendMsg($msgName, $msg, $type){
        //这里因为暂时只有ActiveMQ,所以不用判断,之后又其他的就在这里区别判断
        $activeMQ = Xphp::instance('ActiveMQ');
        $msg = array(
        	'type' => $type,
        	'timestamp' => strtotime(date("Y-m-d H:i:s")),
        	'data' => $msg 
        );
        return $activeMQ->sendMsg($this->settings, $msgName, json_encode($msg), true);
        
    }
    
    //**********************具体推送消息*************************//
    
    /**
     * 获取任务推送消息
     */
    private function jobMsg(){
        //如果是首次,初始化维护列表
        if(NULL === $this->jobLists){
            $this->jobLists = $this->getCurrentJobList();
            return true;
        }
        $this->newJobLists = $this->getCurrentJobList();
        $this->checkInstantVmflagList($this->newJobLists);
        foreach ($this->newJobLists as $key => $value){
            //处理在运行中的任务
            if(Xphp::$_config['TASKSTATUS']['RUNNING'] == $value['task_status']){
            	if($this->jobLists[$key]['task_type'] == Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'] && $this->jobLists[$key]['task_type'] != $value['task_type']){
            		$this->jobMsgRouter($this->jobLists[$key]['task_type'], $key , true);
            	}else{
            		$this->jobMsgRouter($value['task_type'], $key , false);
            	}
                continue;
            }
            //处理状态改变的任务
            if(!empty($this->jobLists[$key]) && $value['task_status'] != $this->jobLists[$key]['task_status']){
            	if($value['task_status']!= Xphp::$_config['TASKSTATUS']['RUNNING']){
            		$this->jobMsgRouter($value['task_type'], $key, true);
            	}else{
            		$this->jobMsgRouter($value['task_type'], $key, false);
            	}
                continue;
            }
        }
        
		//处理上一次在运行中,这一次已经没有运行了的任务,解决进度无法到达100%的情况
        foreach ($this->jobLists as $key => $value){
            if(Xphp::$_config['TASKSTATUS']['RUNNING'] == $value['task_status'] 
               && empty($this->newJobLists[$key])){
                //这里处理上一次保留的信息,把百分比和大小设置成100%,然后发送出去.
            	$this->jobMsgRouter($value['task_type'], $key, true);
            	continue;
            }
        }
        
        //处理完毕,更新任务列表
        $this->jobLists = $this->newJobLists;
        unset($this->newJobLists);
    }
    
    /**
     * 任务处理路由
     * @param int $jobType
     */
    private function jobMsgRouter($jobType, $jobUUID, $flag){
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        switch ($jobType){
            case Xphp::$_config['TASKTYPE']['BACKUP']:
                $this->backupJobMsg($jobUUID, $flag);
                break;
            case Xphp::$_config['TASKTYPE']['RECOVERY']:
                $this->recoveryJobMsg($jobUUID, $flag);
                break;
            case Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY']:
                $this->instantJobMsg($jobUUID, $flag);
                break;
            case Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION']:
                $this->motionJobMsg($jobUUID, $flag);
                break;
            default:
                break;
        }
    }
    
    /**
     * 得到当前任务列表
     */
    private function getCurrentJobList(){
    	if(empty($this->jobVPDOObj)){
    		$this->jobVPDOObj = new VPDO();
    	}
    	
        $jobLists = array();
        $sql = "select task_uuid, task_type, task_status from bd_task where module_type = ? and delete_flag = ?";
        $data = $this->jobVPDOObj->sqlQuery($sql, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['FLAG']['UNSET']), true);
        foreach ($data as $d){
            $taskUUID = $d['task_uuid'];
            $jobLists[$taskUUID] = array(
                "task_uuid" => $d['task_uuid'],
                "task_type" => intval($d['task_type']),
                "task_status" => intval($d['task_status']),
            	"details" => ''
            );
        }
        return $jobLists;
    }
    
    /**
     * 获取备份任务推送消息
     */
    private function backupJobMsg($jobUUID, $flag){
        $msgName = Xphp::$_config['MQ_MSG_NAME']['job']['backup'] . $jobUUID;
        
        $msg = Xphp::instance('APIJobsHandler', 'getCurrentPushJobDetail', $jobUUID);
        if($flag){
        	$msg = $this->jobLists[$jobUUID]['details'];
        	$msg['processed_size'] = $msg['total_size'];
        	$msg['job_process'] = '100%';
        	foreach ($msg['vm_list'] as $key=>$val){
        		$msg['vm_list'][$key]['process'] = '100%';
        	}
        }
        $this->newJobLists[$jobUUID]['details'] = $msg;
//         $this->writeLog("push backup job msg success**************");
        $type = Xphp::$_config['MESSAGETYPE']['JOB.BACKUP'];
        return $this->unifySendMsg($msgName, $msg, $type);
    }
    
    /**
     * 获取恢复任务推送消息
     */
    private function recoveryJobMsg($jobUUID, $flag){
        $msgName = Xphp::$_config['MQ_MSG_NAME']['job']['recovery'] . $jobUUID;
        
        $msg = Xphp::instance('APIJobsHandler', 'getCurrentPushJobDetail', $jobUUID);
        if($flag){
        	$msg = $this->jobLists[$jobUUID]['details'];
        	$msg['processed_size'] = $msg['total_size'];
        	$msg['job_process'] = '100%';
        	foreach ($msg['vm_list'] as $key=>$val){
        		$msg['vm_list'][$key]['process'] = '100%';
        	}
        }
        $this->newJobLists[$jobUUID]['details'] = $msg;
//         $this->writeLog("push recovery job msg success**************");
        $type = Xphp::$_config['MESSAGETYPE']['JOB.RECOVERY'];
        return $this->unifySendMsg($msgName, $msg, $type);
    }
    
    /**
     * 获取瞬时恢复任务推送消息
     */
    private function instantJobMsg($jobUUID, $flag){
        $msgName = Xphp::$_config['MQ_MSG_NAME']['job']['instant'] . $jobUUID;
        
        $msg = Xphp::instance('APIJobsHandler', 'getCurrentPushJobDetail', $jobUUID);
        $runlogList = $msg['running_log'];
        foreach($runlogList as $runlog){
        	if(!$this->instanVMFlagList[$jobUUID] && $runlog['description'] == Xphp::$_lang['WEB_LOG_TASK_DESC_KEY_CONN_TO_VXEFS']){
        		$msg['vm_flag'] = Xphp::$_config['INSTANT_VM_FLAG']['CREATE'];
        		$this->instanVMFlagList[$jobUUID] = true;
        	}
        }
        if($flag){
        	$msg['vm_flag'] = Xphp::$_config['INSTANT_VM_FLAG']['DELETE'];
        	$this->instanVMFlagList[$jobUUID] = false;
        }
        $this->writeLog("push instant job msg success**************");
        
        $type = Xphp::$_config['MESSAGETYPE']['JOB.INSTANT'];
        return $this->unifySendMsg($msgName, $msg, $type);
    }
    
    /**
     * 获取迁移任务推送消息
     */
    private function motionJobMsg($jobUUID, $flag){
        $msgName = Xphp::$_config['MQ_MSG_NAME']['job']['motion'] . $jobUUID;
        
        $msg = Xphp::instance('APIJobsHandler', 'getCurrentPushJobDetail', $jobUUID);
        if($flag){
        	$msg = $this->jobLists[$jobUUID]['details'];
        	$msg['processed_size'] = $msg['total_size'];
        	$msg['job_process'] = '100%';
        	foreach ($msg['vm_list'] as $key=>$val){
        		$msg['vm_list'][$key]['process'] = '100%';
        	}
        }
         $this->newJobLists[$jobUUID]['details'] = $msg;
//         $this->writeLog("push motion job msg success**************");
        
        $type = Xphp::$_config['MESSAGETYPE']['JOB.MOTION'];
        return $this->unifySendMsg($msgName, $msg, $type);
    }
    
    /**
     * 获取告警推送消息
     */
    private function alarmMsg(){
        $listNum = Xphp::$_config['MQ_VALUE']['LISTNUM'];
        //如果是首次,初始化维护列表
        if(NULL === $this->alarmJobList && NULL === $this->alarmSystemList){
            $this->alarmJobList = $this->getNewListAlarmIDList('task_alarm_id', 'bd_task_alarm', $listNum);
            $this->alarmSystemList = $this->getNewListAlarmIDList('system_alarm_id', 'bd_system_alarm', $listNum);
            return true;
        }
        $newAlarmJobList = $this->getNewListAlarmIDList('task_alarm_id', 'bd_task_alarm', $listNum);
        $newAlarmSystemList = $this->getNewListAlarmIDList('system_alarm_id', 'bd_system_alarm', $listNum);
        //如果不是首次,遍历新列表,和老的做比较,不在老列表里面的,直接推送,推送完成后,更新列表为新列表
        //TODO 这里遍历次数太多,可以优化
        foreach ($newAlarmJobList as $alarmID){
            if(!in_array($alarmID, $this->alarmJobList)){
                $this->jobAlarmMsg($alarmID);
            }
        }
        
        foreach ($newAlarmSystemList as $alarmID){
            if(!in_array($alarmID, $this->alarmSystemList)){
                $this->systemAlarmMsg($alarmID);
            }
        }
        $this->alarmJobList = $newAlarmJobList;
        $this->alarmSystemList = $newAlarmSystemList;
    }
    
    /**
     * 得到最近的告警ID列表
     * @param string $idStr     ID字段
     * @param string $tableName 表名
     * @param int $count        总数
     * @return array
     */
    private function getNewListAlarmIDList($idStr, $tableName, $count){
    	if(empty($this->alarmVPDOObj)){
    		$this->alarmVPDOObj = new VPDO();
    	}
    	
        $sql = "select ". $idStr . " from " . $tableName . " order by " . $idStr . " desc limit 0, " . $count;
        $data = $this->alarmVPDOObj->sqlQuery($sql, array(), true);
        
        $alarmIDList = array();
        foreach($data as $d){
            array_push($alarmIDList, $d[$idStr]);
        }
        return $alarmIDList;
    }
    
    /**
     * 获取任务告警推送消息
     */
    private function jobAlarmMsg($alarmID){
    	//从数据库取出对应ID的告警，然后组合成array，调用unifySendMsg方法。
        $msgName = Xphp::$_config['MQ_MSG_NAME']['alarm']['job'];
		        
        $msg = Xphp::instance('APIAlarmsHandler', 'getJobAlarmDetail', $alarmID);
//         $this->writeLog("push system alarm  msg success**************");
        
        $type = Xphp::$_config['MESSAGETYPE']['ALARM.JOB'];
        return $this->unifySendMsg($msgName, $msg , $type);
    }
    
    /**
     * 获取系统告警推送消息
     */
    private function systemAlarmMsg($alarmID){
    	//从数据库取出对应ID的告警，然后组合成array，调用unifySendMsg方法。
        $msgName = Xphp::$_config['MQ_MSG_NAME']['alarm']['system'];
        
        $msg = Xphp::instance('APIAlarmsHandler', 'getSystemAlarmDetail', $alarmID);
//         $this->writeLog("push system alarm job msg success**************");
        
        $type = Xphp::$_config['MESSAGETYPE']['ALARM.SYSTEM'];
        return $this->unifySendMsg($msgName, $msg, $type);
    }
    
    private function checkInstantVmflagList($newJobList){
    	//把瞬时恢复存入的uuid 和 新的任务列表作比较，如果任务已经删除了，清空删除了的uuid
    	//获取瞬时恢复标志uuid数组1
    	$flagUuidList = array();
    	foreach ($this->instanVMFlagList as $flagKey => $flagValue){
    		$flagUuidList[] = $flagKey;
    	}
    	//获取当前任务列表uuid数组2
    	$newUuidList = array();
    	foreach ($newJobList as $newKey => $flagValue){
    		$newUuidList[] = $newKey;
    	}
    	
    	//遍历宿主1，获取到每个uuid，判断是否在数组2中，如果不在，将$this->instanVMFlagList中对应的uuid项移除
    	$newFlagList = array();
    	foreach ($flagUuidList as $uuid){
    		if(in_array($uuid, $newUuidList)){
    			$newFlagList[$uuid] = $this->instanVMFlagList[$uuid];
    		}
    	}
    	$this->instanVMFlagList = $newFlagList;
    }
    
}