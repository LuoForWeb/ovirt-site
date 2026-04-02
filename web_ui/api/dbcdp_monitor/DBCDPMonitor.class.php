<?php
/******************************************* 
**  CDP监控类
*   主要监控主机和任务状态
** 
** @author       
** @date         2019-12-12
** @version      1.0.0 
** @copyright    Copyright 2019 vinchin.com 
********************************************/
class DBCDPMonitor extends OPHandler{
    
    //主机状态监控进程号
    protected $hostPid = 0;
    //任务状态监控进程号
    protected $jobPid = 0;
    
    //文件CDP任务状态监控进程号
    protected $filejobPid = 0;
    
    //初始化主机监控进程VPDO
    protected $hostVPDOObj;
    
    //初始化任务监控进程VPO
    protected $databaseJobVPDOObj;
    protected $fileJobVPDOObj;
    
    //数据库恢复running信息
    protected $lastTime;
    protected $lastSize;
    
    //文件恢复running信息
    protected $fileLastTime;
    protected $fileLastSize;
    
    //监控超时时间
    protected $timeout = 5;
    
    
    /**
     * 构造函数
     */
    public function __construct(){
        //循环检查是否正常启动进程
        while(true){
            //获取所有进程号,检查是否有未启动的
            $info = array();
            $cmd = "ps aux |grep dbCDPMonitor.php |grep -v grep | awk '{print $2}'";
            exec($cmd, $info);
            
            //如果监控进程不存在
            if(!in_array($this->hostPid, $info)){
                $this->hostPid = 0;
                $this->hostMonitor();
                $this->writeLog('start cdp host monitor.');
            }
            
            //如果监控进程不存在
            if(!in_array($this->jobPid, $info)){
                $this->jobPid = 0;
                $this->dbJobMonitor();
                $this->writeLog('start database cdp job monitor.');
            }
            
            //如果监控进程不存在
            if(!in_array($this->filejobPid, $info)){
                $this->filejobPid = 0;
                $this->fileJobMonitor();
                $this->writeLog('start file cdp job monitor.');
            }
            
            sleep(60);
        }
    }
    
    /**
     * 主机监控
     */
    private function hostMonitor(){
        $this->writeLog("start host monitor!");
        $pid = pcntl_fork();
        //父进程和子进程都会执行下面代码
        if ($pid == -1) {
            //错误处理：创建子进程失败时返回-1.
            die($this->writeLog("create host monitor process failure!"));
        } else if ($pid) {
            //父进程会得到子进程号，所以这里是父进程执行的逻辑
            $this->hostPid = $pid;

            // 非阻塞回收僵尸进程（关键！）
            pcntl_signal(SIGCHLD, SIG_IGN); // 让内核自动回收（最简单）
            //             pcntl_wait($status); //等待子进程中断，防止子进程成为僵尸进程。
        } else {
            //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
            $this->writeLog("create host monitor process success!");
            while (true){
                $this->startHostMonitor(); 
                $this->writeLog("host monitor sleep!!!!!!!!!!!!!!");
                sleep(Xphp::$_config['DB_CDP_HOST_MONITOR_SLEEP']);
            }
        }
    }
    
    /**
     * 开始主机监控
     */
    private function startHostMonitor(){
        if(empty($this->hostVPDOObj)){
            $this->hostVPDOObj = new VPDO();
        }
        //先找到所有添加到系统的主机
        $sql = "select host_uuid, ip, status from cdp_db_host";
        $data = $this->hostVPDOObj->sqlQuery($sql, array(), true);
        
        //挨个更新主机状态
        $rpc = Xphp::instance('DbRPCHandler');
        $rpc->setDefaultParams(array("timeout" => $this->timeout));
        foreach ($data as $d){
            $this->updateHostStatus($rpc, $d);
        }
    }
    
    /**
     * 更新主机状态
     * @param unknown $rpc
     * @param unknown $hostInfo
     */
    private function updateHostStatus($rpc, $hostInfo){
        $result = $rpc->getTime($hostInfo['ip'], array());
        if($result['result']){
            //如果成功返回
            $status = Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE'];
        }else{
            $status = Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'];
        }
        if($status == intval($hostInfo['status'])) return true;
        $sql = "update cdp_db_host set status = ? where host_uuid = ?";
        $this->writeLog("update cdp_db_host set status = $status where host_uuid = " . $hostInfo['host_uuid']);
        $data = $this->hostVPDOObj->sqlQuery($sql, array($status, $hostInfo['host_uuid']), true);
    }
    
    /**
     * 数据库CDP任务监控
     */
    private function dbJobMonitor(){
        $this->writeLog("start database job monitor!");
        $pid = pcntl_fork();
        //父进程和子进程都会执行下面代码
        if ($pid == -1) {
            //错误处理：创建子进程失败时返回-1.
            die($this->writeLog("create database job monitor process failure!"));
        } else if ($pid) {
            //父进程会得到子进程号，所以这里是父进程执行的逻辑
            $this->jobPid = $pid;
            //             pcntl_wait($status); //等待子进程中断，防止子进程成为僵尸进程。
        } else {
            //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
            $this->writeLog("create database job monitor process success!");
            while (true){
                $this->startDatabaseJobMonitor();
                $this->writeLog("database job monitor sleep!!!!!!!!!!!!!!");
                sleep(Xphp::$_config['DB_CDP_JOB_MONITOR_SLEEP']);
            }
        }
    }
    
    /**
     * 文件CDP任务监控
     */
    private function fileJobMonitor(){
        $this->writeLog("start file job monitor!");
        $pid = pcntl_fork();
        //父进程和子进程都会执行下面代码
        if ($pid == -1) {
            //错误处理：创建子进程失败时返回-1.
            die($this->writeLog("create file job monitor process failure!"));
        } else if ($pid) {
            //父进程会得到子进程号，所以这里是父进程执行的逻辑
            $this->filejobPid = $pid;
            //             pcntl_wait($status); //等待子进程中断，防止子进程成为僵尸进程。
        } else {
            //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
            $this->writeLog("create file job monitor process success!");
            while (true){
                $this->startFileJobMonitor();
                $this->writeLog("file job monitor sleep!!!!!!!!!!!!!!");
                sleep(Xphp::$_config['DB_CDP_JOB_MONITOR_SLEEP']);
            }
        }
    }
    
    /**
     * 开始数据库任务监控
     */
    private function startDatabaseJobMonitor(){
        if(empty($this->databaseJobVPDOObj)){
            $this->databaseJobVPDOObj = new VPDO();
        }
        //先得到所有数据库实时的任务列表
        $sql = "select  bt.task_uuid, bt.task_type, bt.task_status, cdt.product_host_uuid,
                cdt.standby_host_uuid, cdt.config  from bd_task bt, cdp_db_task cdt 
                where bt.task_uuid = cdt.task_uuid and bt.module_type = ?";
        $data = $this->databaseJobVPDOObj->sqlQuery($sql, array(Xphp::$_config['MODULE_TYPE']['OEM_DBCDP']), true);
        foreach ($data as $d){
            //考虑到任务处于所有状态下都有可能改变其状态(如果通过web创建系统,再通过其他控制台调试),这个时候需要能够更新任务状态
            //所以这里不判断任务状态,直接去获取每个任务详情来确认任务新的状态,然后更新
            $taskType = intval($d['task_type']);
            $taskStatus = intval($d['task_status']);
            $productHost = $this->getHostInfoWithUUID($d['product_host_uuid'], $this->databaseJobVPDOObj);
            $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid'], $this->databaseJobVPDOObj);
            if($taskType == Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP']){
                //备份任务
                $this->updateBackupJobStatus($d['task_uuid'], $taskStatus, $productHost, $standbyHost);
            }elseif ($taskType == Xphp::$_config['TASKTYPE']['DB_CDP_RECOVERY']){
                //恢复任务
                $this->updateRecoveryJobStatus($d['task_uuid'], $taskStatus, $productHost, $standbyHost, $d['config']);
            }
        }
    }
    
    /**
     * 开始文件任务监控
     */
    private function startFileJobMonitor(){
        if(empty($this->fileJobVPDOObj)){
            $this->fileJobVPDOObj = new VPDO();
        }
        //先得到所有数据库实时的任务列表
        $sql = "select  bt.task_uuid, bt.task_type, bt.task_status, cdt.product_host_uuid,
                cdt.standby_host_uuid, cdt.config  from bd_task bt, cdp_fs_task cdt
                where bt.task_uuid = cdt.task_uuid and bt.module_type = ?";
        $data = $this->fileJobVPDOObj->sqlQuery($sql, array(Xphp::$_config['MODULE_TYPE']['OEM_FSCDP']), true);
        foreach ($data as $d){
            //考虑到任务处于所有状态下都有可能改变其状态(如果通过web创建系统,再通过其他控制台调试),这个时候需要能够更新任务状态
            //所以这里不判断任务状态,直接去获取每个任务详情来确认任务新的状态,然后更新
            $taskType = intval($d['task_type']);
            $taskStatus = intval($d['task_status']);
            $productHost = $this->getHostInfoWithUUID($d['product_host_uuid'], $this->fileJobVPDOObj);
            $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid'], $this->fileJobVPDOObj);
            if($taskType == Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP']){
                //备份任务
                $this->updateFileBackupJobStatus($d['task_uuid'], $taskStatus, $productHost, $standbyHost);
            }elseif ($taskType == Xphp::$_config['TASKTYPE']['FILE_CDP_RECOVERY']){
                //恢复任务
                $this->updateFileRecoveryJobStatus($d['task_uuid'], $taskStatus, $productHost, $standbyHost, $d['config']);
            }
        }
    }
    
    //停止  运行  错误   异常  等待
    
    /**
     * 更新备份任务状态
     * @param unknown $taskuuid
     * @param unknown $productHost
     * @param unknown $standbyHost
     *      生产主机	           备份主机	            从站数据库备份状态	任务状态
                            在线状态|任务状态   在线状态|任务状态		
                            
                            离线		     离线		        --	                             错误
                            离线		     在线	--	        --	                             错误
                            在线	--	     离线		        --	                             错误
                            在线	启动	    在线	停止	        --	                             停止
                            在线	停止	    在线	停止	        --	                             停止
                            在线	停止	    在线	启动	        --	                             停止
                            在线	启动	    在线	启动	                    全部正常	                 运行
                            在线	启动	    在线	启动	                    部分错误	                 异常
                            在线	启动	    在线	启动	                    全部错误	                 错误

     */
    private function updateBackupJobStatus($taskuuid, $taskstatus, $productHost, $standbyHost){
//         echo time() . " update start!" . PHP_EOL;
        //判断主机在线状态
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);
        $statusArr = Xphp::$_config['TASKSTATUS'];
        $rpc = Xphp::instance('DbRPCHandler');
        $rpc->setDefaultParams(array("timeout" => $this->timeout));
        if($productHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']){
            //生产主机离线
            if($standbyHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']){
                //备份主机离线
                if($taskstatus != $statusArr['STOPPED']){
                    //停止状态不更新
                    return $this->updateJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
                }
            }else{
                //备份主机在线,看是否接管,如果接管了,直接是接管状态,如果不是接管,变成错误状态
                $rpcMsg = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
                );
                $standbyResult = $rpc->checkIfTakeOverId($standbyHost['ip'], $rpcMsg);
                $data = $this->checkRPCMsg($standbyResult);
//                 echo time() . " takeroerid1: " . $data[0]['takeroverid'] . PHP_EOL;
                if($data[0]['takeroverid'] > 0){
                    //如果接管中
                    return $this->updateTaskoverStatus($taskuuid, $standbyHost['ip'], $taskstatus, $data[0]['takeroverid']);
                }else{
                    if($taskstatus == $statusArr['TAKEOVER'] || $taskstatus == $statusArr['TAKEOVER_STARTING'] || $taskstatus == $statusArr['TAKEOVER_STOPPING']){
                        //如果在接管的几个状态,但是又获取不到takeroverid,任务设置为停止
                        return $this->updateJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
                    }
                    if($taskstatus != $statusArr['STOPPED']){
                        //停止状态不更新
//                         return $this->updateJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
                        return $this->updateJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
                    }
                    
                }
            }
        }else{
            //生产主机在线，备份主机离线
            if($standbyHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']){
                //备份主机离线
                if($taskstatus != $statusArr['STOPPED']){
                    //停止状态不更新
                    return $this->updateJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
                }
            }
        }
        
        //生产主机和备份主机都在线,先判断接管状态
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
        );
        $standbyResult = $rpc->checkIfTakeOverId($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($standbyResult);
//         echo time() . " takeroerid2: " . $data[0]['takeroverid'] . PHP_EOL;
        if($data[0]['takeroverid'] > 3){
            //如果接管中
            return $this->updateTaskoverStatus($taskuuid, $standbyHost['ip'], $taskstatus, $data[0]['takeroverid']);
        }
        
        //判断主机连接状态,如果是禁用,直接修改为停止
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']
        );
        $data = array();
        $standbyResult = $rpc->GetConnectHost($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($standbyResult);
        foreach ($data as $d){
            if($d['ipstr'] == $productHost['ip']){
                //找到生产主机,再判断连接状态
                if(1 === $d['disable']){
                    //如果是禁用状态,直接修改任务为停止状态
                    return $this->updateJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
                }
            }
        }
        
        //判断主机服务状态
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']
        );
        
        $data = array();
        $standbyResult = $rpc->getServiceStatus($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($standbyResult);
        $standbyStatus = $data["runstatus"];
        
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost']
        );
        $productResult = $rpc->getServiceStatus($productHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($productResult);
        $productStatus = $data["runstatus"];
        
        if($standbyStatus === Xphp::$_config['DB_CDP_HOST_START_STATUS']['ERROR'] || 
           $productStatus === Xphp::$_config['DB_CDP_HOST_START_STATUS']['ERROR']){
            //如果生产主机或备份主机有一个是错误状态,任务状态为错误
               return $this->updateJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
        }
        if($standbyStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] ||
            $productStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED']){
                //如果生产主机或备份主机有一个是停止状态,任务不在启动中任务状态为停止
                if($taskstatus != $statusArr['STARTING']){
                    return $this->updateJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
                }
        }
        //判断从站到主站的连接状态
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hipstr' => $productHost['ip'],
        );
        $result = $rpc->getRemoteHostIpConnStatus($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($result);
        //主机连接状态   4 5 6 表示在备份   7表示在等待,一般是等待60秒  如果空的话($data没东西)表示停止
        if(empty($data)){
            return $this->updateJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
        }
        $connStatus = $data[0]['runstatus'];
        //如果在等待,切换成等待状态
        if($connStatus == 7){
            return $this->updateJobStatus($taskuuid, $statusArr['WAITTING'], $taskstatus);
        }
        
        //如果在运行,再看数据库的状态
        
        
        
        //判断从站数据库备份状态
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hostip' => $productHost['ip'],
            'remoteip' => $standbyHost['ip'],
        );
        $result = $rpc->getBackupDbMStatus($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($result);
        $totalDB = count($data);    //数据库总数
        
//         echo time() . " " . $totalDB . PHP_EOL;
//         if($totalDB == 0){
//             //任务还没开始,获取到的数据库列表是空的.任务不在启动中的状态,任务处于停止状态
//             if($taskstatus != $statusArr['STARTING']){
//                 return $this->updateJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
//             }else{
//                 return true;
//             }
//         }
        $normalDB = 0;              //正常的总数
        foreach ($data as $d){
//             echo time() . " " . $d['bkstatus'] . PHP_EOL;
            if($d['bkstatus'] >= 0){
                $normalDB++;
            }
//             if(intval($d['bkstatus']) == Xphp::$_config['DB_CDP_BACKUP_DB_STATUS']['STOPPED']){
//                 //如果任务停止
//                 return $this->updateJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
//             }
//             if(intval($d['bkstatus']) == Xphp::$_config['DB_CDP_BACKUP_DB_STATUS']['WAITNEXT']){
//                 //如果任务等待
//                 return $this->updateJobStatus($taskuuid, $statusArr['WAITTING'], $taskstatus);
//             }
        }
        $status = $statusArr['RUNNING'];    //默认全部正常,任务运行中
        if($totalDB == 0){
            $status = $statusArr['STOPPED'];
        }
        if($totalDB > 0 &&  $normalDB == 0){
            //全部错误,任务错误
            $status = $statusArr['ERROR'];
        }
        if($normalDB > 0 && $totalDB - $normalDB > 0){
            //部分错误,任务异常
            $status = $statusArr['ABNORMAL'];
        }
        return $this->updateJobStatus($taskuuid, $status, $taskstatus);
    }
    
    /**
     * 更新文件备份任务状态
     * @param unknown $taskuuid
     * @param unknown $taskstatus
     * @param unknown $productHost
     * @param unknown $standbyHost
     * * *      生产主机	           备份主机	            从站数据库备份状态	任务状态
                            在线状态|任务状态   在线状态|任务状态		
                            
                            离线		     离线		        --	                             错误
                            离线		     在线	--	        --	                             错误
                            在线	--	     离线		        --	                             错误
                            在线	启动	    在线	停止	        --	                             停止
                            在线	停止	    在线	停止	        --	                             停止
                            在线	停止	    在线	启动	        --	                             停止
                            在线	启动	    在线	启动	                    全部正常	                 运行
                            在线	启动	    在线	启动	                    部分错误	                 运行,在日志中体现异常
                            在线	启动	    在线	启动	                    全部错误	                 错误
     */
    private function updateFileBackupJobStatus($taskuuid, $taskstatus, $productHost, $standbyHost){
//         echo time() . " update start!" . PHP_EOL;
        //判断主机在线状态
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);
        $statusArr = Xphp::$_config['TASKSTATUS'];
        $rpc = Xphp::instance('DbRPCHandler');
        $rpc->setDefaultParams(array("timeout" => $this->timeout));
        $fileRpc = Xphp::instance('FileRPCHandler');
        if($productHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']){
            //生产主机离线,不管备份主机状态,直接更新
            if($taskstatus != $statusArr['STOPPED']){
                //停止状态不更新
                return $this->updateFileJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
            }
        }else{
            //生产主机在线，备份主机离线
            if($standbyHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']){
                //备份主机离线
                if($taskstatus != $statusArr['STOPPED']){
                    //停止状态不更新
                    return $this->updateFileJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
                }
            }
        }
        //生产主机和备份主机都在线
        
        //判断主机连接状态,如果是禁用,直接修改为停止
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']
        );
        $data = array();
        $standbyResult = $rpc->GetConnectHost($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($standbyResult);
        foreach ($data as $d){
            if($d['ipstr'] == $productHost['ip']){
                //找到生产主机,再判断连接状态
                if(1 === $d['disable']){
                    //如果是禁用状态,直接修改任务为停止状态
                    return $this->updateFileJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
                }
            }
        }
        
        //判断主机服务状态
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby']
        );
        
        $data = array();
        $standbyResult = $rpc->getServiceStatus($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($standbyResult);
        $standbyStatus = $data["runstatus"];
        
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct']
        );
        $productResult = $rpc->getServiceStatus($productHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($productResult);
        $productStatus = $data["runstatus"];
        
        if($standbyStatus === Xphp::$_config['DB_CDP_HOST_START_STATUS']['ERROR'] ||
            $productStatus === Xphp::$_config['DB_CDP_HOST_START_STATUS']['ERROR']){
                //如果生产主机或备份主机有一个是错误状态,任务状态为错误
                return $this->updateFileJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
        }
        if($standbyStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] ||
            $productStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED']){
                //如果生产主机或备份主机有一个是停止状态,任务不在启动中任务状态为停止
                if($taskstatus != $statusArr['STARTING']){
                    return $this->updateFileJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
                }
        }
        //判断从站到主站的连接状态
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHost['ip'],
        );
        $result = $fileRpc->getRemoteHostIpConnStatus($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($result);
        //主机连接状态   4 5 6 表示在备份   7表示在等待,一般是等待60秒  如果空的话($data没东西)表示停止
        if(empty($data)){
            return $this->updateFileJobStatus($taskuuid, $statusArr['STOPPED'], $taskstatus);
        }
        $connStatus = $data[0]['runstatus'];
        //如果在等待,切换成等待状态
        if($connStatus == 7){
            return $this->updateFileJobStatus($taskuuid, $statusArr['WAITTING'], $taskstatus);
        }
        
        //如果在运行,再看文件的状态
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHost['ip'],
        );
        $result = $fileRpc->getTaskStaticInfos($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($result);
        $totalDir = count($data);    //数据库总数
        
//         echo time() . " " . $totalDir . PHP_EOL;
        $normalDir = 0;              //正常的总数
        foreach ($data as $d){
            //             echo time() . " " . $d['bkstatus'] . PHP_EOL;
            if($d['failscounter'] == 0){
                $normalDir++;
            }
        }
        $status = $statusArr['RUNNING'];    //默认全部正常,任务运行中
        if($totalDir == 0){
            $status = $statusArr['STOPPED'];
        }
        if($totalDir > 0 &&  $normalDir == 0){
            //全部错误,任务错误
            $status = $statusArr['ERROR'];
        }
        if($normalDir > 0 && $totalDir - $normalDir > 0){
            //部分错误,任务正常,在日志里面显示异常情况
            $status = $statusArr['RUNNING'];
        }
        return $this->updateFileJobStatus($taskuuid, $status, $taskstatus);
    }
    
    /**
     * 更新文件恢复任务状态
     * @param unknown $taskuuid
     * @param unknown $taskstatus
     * @param unknown $productHost
     * @param unknown $standbyHost
     * @param unknown $config
     */
    private function updateFileRecoveryJobStatus($taskuuid, $taskstatus, $productHost, $standbyHost, $config){
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);
        $statusArr = Xphp::$_config['TASKSTATUS'];
        //判断任务现在的状态,如果任务是停止状态,就不更新
        if($taskstatus == $statusArr['STOPPED'] || $taskstatus == $statusArr['FINISHED']){
            return true;
        }
        //判断主机在线状态
        if($productHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] ||
            $standbyHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']){
                //如果有一个是离线状态,任务状态为停止
                return $this->updateFileJobStatus($taskuuid, $statusArr['STOPPED']);
        }
        //判断恢复文件实时状态
        $rpc = Xphp::instance('FileRPCHandler');
        $rpc->setDefaultParams(array("timeout" => $this->timeout));
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHost['ip'],
        );
        $result = $rpc->getRecoveryCliInfo($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($result);
        
        //用户选择恢复的文件/文件夹的总数  
        $selectCount = count($data);
        
        //如果一个任务信息都获取不到,不做更新
        if(0 == $selectCount){
            return true;
        }
        
        $totalfsum = 0;     //总文件个数
        $totalfsize = 0;    //总文件大小
        $copytotalsize = 0; //总完成大小
        $copyspeed = 0;     //即时速度
        
        $workidArr = array();   //工作状态记录
        $runcodeArr = array();     //运行状态记录
        $etimeArr = array();       //结束时间记录
        $searchetimeArr = array(); //扫描结束时间记录
        
        $i = 0;
        foreach ($data as $d){
            $workidArr[] = intval($d['workid']);
            $runcodeArr[] = $d['runcode'];
            $etimeArr[] = $d['etime'];
            $searchetimeArr = $d['searchetime'];
            
            $totalfsum += $d['totalfsum'];
            $totalfsize += $d['totalfsize'];
            $copytotalsize += $d['copytotalsize'];
            if($d['workid'] == Xphp::$_config['FILE_CDP_RECOVERY_WORDID']['CHECKING']){
                //如果在运行中才算速度
                $copyspeed += $d['copyspeed'];
            }
            $i++;
        }
        
        $fileInfo = array(
            "totalfsize" => $totalfsize,
            "copytotalsize" => $copytotalsize,
            "copyspeed" => $copyspeed,
        );
        
        if(in_array(Xphp::$_config['FILE_CDP_RECOVERY_WORDID']['CHECKING'], $workidArr)){
            //如果有一个任务在运行中,任务为运行状态
            $this->updateFileProgressAndSpeed($taskuuid, $statusArr['RUNNING'], $fileInfo);
            return $this->updateFileJobStatus($taskuuid, $statusArr['RUNNING'], $taskstatus);
        }
        
        if(!in_array(Xphp::$_config['FILE_CDP_RECOVERY_WORDID']['CHECKING'], $workidArr)){
            //如果所有的任务都已经结束
            $this->updateFileProgressAndSpeed($taskuuid, $statusArr['FINISHED'], $fileInfo);
            return $this->updateFileJobStatus($taskuuid, $statusArr['FINISHED'], $taskstatus);
        }
        
    }
    
    /**
     * 更新接管状态
     * @param unknown $taskuuid
     * @param unknown $oldStatus
     * @param unknown $takeroverid
     */
    private function updateTaskoverStatus($taskuuid, $standbyHostIP, $oldStatus, $takeroverid){
        $statusArr = Xphp::$_config['TASKSTATUS'];
        $startStatusArr = Xphp::$_config['DB_CDP_TAKEOVER_START_STATUS'];
        $stopStatusArr = Xphp::$_config['DB_CDP_TAKEOVER_STOP_STATUS'];
        $status = $oldStatus;
        //启动接管状态,这里有点特殊的是自动接管也在这个状态中
        if(in_array($takeroverid, $startStatusArr)){
            if($startStatusArr['S_WAITCHKIF'] == $takeroverid){
                //自动接管等待,不更新状态
                $status = $oldStatus;
            }elseif($startStatusArr['S_TASKOK'] == $takeroverid){
                //接管中
                $status = $statusArr['TAKEOVER'];
            }else{
                //其他状态,接管启动中
                $status = $statusArr['TAKEOVER_STARTING'];
            }
        }
        //停止接管状态
        if(in_array($takeroverid, $stopStatusArr)){
            if($stopStatusArr['P_TASKEND'] == $takeroverid){
                //停止接管完成
                $status = $statusArr['STOPPED'];
            }else{
                //其他状态,接管停止中
                $status = $statusArr['TAKEOVER_STOPPING'];
            }
        }
        
//         //检查接管服务状态,如果是没启动,接管转为停止状态
//         $rpc = Xphp::instance('DbRPCHandler');
//         $rpcMsg = array(
//             'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
//         );
//         $standbyResult = $rpc->getServiceStatus($standbyHostIP, $rpcMsg);
//         $data = $this->checkRPCMsg($standbyResult);
//         if($data['runstatus'] != Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED']){
//             $status = $statusArr['STOPPED'];
//         }
            
        return $this->updateJobStatus($taskuuid, $status, $oldStatus);
    }
    
    /**
     * 统一更新数据库任务状态
     * @param unknown $taskuuid
     */
    private function updateJobStatus($taskuuid, $status, $oldStatus){
        //如果状态一样,不更新
        if($status == $oldStatus) return true;
        //如果是不合法的状态更新,直接返回
        if(!$this->updateStatusCheck($oldStatus, $status)) return true;
        $sql = "update bd_task set task_status = ? where task_uuid = ?";
        $this->writeLog("update bd_task set task_status = $status where task_uuid = $taskuuid");
        $data = $this->databaseJobVPDOObj->sqlQuery($sql, array($status, $taskuuid), true);
    }
    
    /**
     * 统一更新文件任务状态
     * @param unknown $taskuuid
     */
    private function updateFileJobStatus($taskuuid, $status, $oldStatus){
        //如果状态一样,不更新
        if($status == $oldStatus) return true;
        //如果是不合法的状态更新,直接返回
        if(!$this->updateStatusCheck($oldStatus, $status)) return true;
        $sql = "update bd_task set task_status = ? where task_uuid = ?";
        $this->writeLog("update bd_task set task_status = $status where task_uuid = $taskuuid");
        $data = $this->fileJobVPDOObj->sqlQuery($sql, array($status, $taskuuid), true);
    }
    
    /**
     * 更新任务状态检查
     * 停止中只能够到  停止,异常,错误
     * 启动中只能够到  运行,异常,错误
     * @param unknown $oldStatus
     * @param unknown $newStatus
     * @return ture 可以更新  false 不可以更新
     */
    private function updateStatusCheck($oldStatus, $newStatus){
        $oldStatus = intval($oldStatus);
        $newStatus = intval($newStatus);
        
        $statusConf = Xphp::$_config['TASKSTATUS'];
        if($statusConf['STOPPING'] == $oldStatus){
            //停止中
            $enableStatus = array($statusConf['STOPPED'], $statusConf['ABNORMAL'], $statusConf['ERROR']);
            return in_array($newStatus, $enableStatus);
        }
        if($statusConf['STARTING'] == $oldStatus){
            //停止中
            $enableStatus = array($statusConf['RUNNING'], $statusConf['ABNORMAL'], $statusConf['ERROR']);
            return in_array($newStatus, $enableStatus);
        }
        return true;
    }
    
    /**
     * 更新数据库恢复任务状态
     */
    private function updateRecoveryJobStatus($taskuuid, $taskstatus, $productHost, $standbyHost, $config){
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);
        $statusArr = Xphp::$_config['TASKSTATUS'];
        //判断任务现在的状态,如果任务是停止状态,就不更新
        if($taskstatus == $statusArr['STOPPED'] || $taskstatus == $statusArr['FINISHED']){
            return true;
        }
        //判断主机在线状态
        if($productHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] ||
            $standbyHostStatus == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']){
                //如果有一个是离线状态,任务状态为停止
                return $this->updateJobStatus($taskuuid, $statusArr['STOPPED']);
        }
        //判断恢复数据库实时状态
        $config = json_decode($config, true);
        
        $rpc = Xphp::instance('DbRPCHandler');
        $rpc->setDefaultParams(array("timeout" => $this->timeout));
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'ipstr' => $config['dbinfo']['hostip'],
            'dbtype' => $config['dbinfo']['dbtype'],
            'dbsrv' => $config['dbinfo']['instance'],
            'dbname' => $config['dbinfo']['name'],
            'histime' => $config['timeperiod']
        );
        if($productHost['ip'] == $standbyHost['ip']){
            //本机恢复
//             echo "本机恢复, 目标ip: " . $productHost['ip'] . PHP_EOL;
            $result = $rpc->getRecoveryInfo($standbyHost['ip'], $rpcMsg);
        }else{
            //异机恢复
//             echo "异机恢复, 目标ip: " . $productHost['ip'] . PHP_EOL;
            $result = $rpc->getRecoveryCliInfo($standbyHost['ip'], $rpcMsg);
        }
        $data = $this->checkRPCMsg($result);
        $data = $data[0];
        $msgcode =  $data['msgcode'];
        $wordid = $data['workid'];
        
        
//         echo "恢复workid:" . $wordid . PHP_EOL;
        
        if(0 != $msgcode){
            //错误
            return $this->updateJobStatus($taskuuid, $statusArr['ERROR'], $taskstatus);
        }
        $wordidConf = Xphp::$_config['DB_CDP_RECOVERY_WORDID'];
        if($wordidConf['FINISHED'] == $wordid){
            //完成
            $this->updateProgressAndSpeed($taskuuid, $data);
            //完成后进行附加
            $this->attachRecoveryDb($productHost, $standbyHost, $taskuuid, $taskstatus, $rpcMsg);
            return $this->updateJobStatus($taskuuid, $statusArr['FINISHED'], $taskstatus);
        }else{
            //其他状态在运行中
            $this->updateProgressAndSpeed($taskuuid, $data);
            return $this->updateJobStatus($taskuuid, $statusArr['RUNNING'], $taskstatus);
        }
    }
    
    /**
     * 异机恢复完成后附加数据库,
     * 暂时支持sqlserver的数据库附加
     * @param unknown $taskuuid
     * @param unknown $data
     */
    private function attachRecoveryDb($productHost, $standbyHost, $taskuuid, $taskstatus, $rpcMsg){
        //先检查是否是sqlerver,暂时只支持SqlServer附加
        if(intval($rpcMsg['dbtype']) != Xphp::$_config['DB_CDP_VENDOR']['SQLSERVER']){
            return true;
        }
        //再检查是否是异机恢复,本机恢复会自动附加,异机恢复才需要调用附加
        if($productHost['ip'] == $standbyHost['ip']){
            return true;
        }
        //再检查目前任务状态,如果任务是完成状态,就不再附加,保证附加操作只做一次
        $statusArr = Xphp::$_config['TASKSTATUS'];
        if($taskstatus == $statusArr['FINISHED']){
            return true;
        }
        //获取数据库文件原始路径
        $rpc = Xphp::instance('DbRPCHandler');
        $rpc->setDefaultParams(array("timeout" => $this->timeout));
        $result = $rpc->scanBackupFiles($standbyHost['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($result);
        
        //发送附加命令,发送到目的主机
        if(intval($productHost['host_type']) == Xphp::$_config['DB_CDP_HOST_TYPE']['product']){
            $syscode = Xphp::$_config['DB_CDP_SYSCODE']['producthost'];
        }
        if(intval($productHost['host_type']) == Xphp::$_config['DB_CDP_HOST_TYPE']['standby']){
            $syscode = Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'];
        }
        
        $rpcMsg['syscode'] = $syscode;
        $rpcMsg['files'] = $data;
        $result = $rpc->attachRecoveryDb($productHost['ip'], $rpcMsg);
        var_dump($productHost['ip'], $rpcMsg, $result);
        if(!$result['result']){
            $this->writeLog('attachRecoveryDb failure, product ip ' . $productHost['ip']);
        }
    }
    
    /**
     * 更新恢复任务的进度和速度
     * @param unknown $dbInfo
     */
    private function updateProgressAndSpeed($taskuuid, $dbData){
        $totalSize = $dbData['totalflen'];
        $currentSize = $dbData['okflen'] +  $dbData['copylen'];
        $currentTime = $dbData['curtime'];      //只是客户端时间
        if(empty($this->lastSize) || empty($this->lastTime)){
            $this->lastSize = $currentSize;
            $this->lastTime = $currentTime;
            return true;
        }
        $cSize = $currentSize - $this->lastSize;
        $cTime = strtotime($currentTime) - strtotime($this->lastTime);
        $speed = round($cSize / $cTime , 2);
        if($speed < 0) $speed = 0;
        $speedTime = time();    //数据库使用服务器时间
        
        $workidConf = Xphp::$_config['DB_CDP_RECOVERY_WORDID'];
        if($dbData['workid'] ==  $workidConf['FINISHED'] || $dbData['workid'] ==  $workidConf['UNWRITE']){
            //如果是完成状态
            $speed = 0;
            $totalSize = 0;
            $currentSize = 0;
        }
        
        $sql = "update bd_running_info set total_object_size = ?, total_object_completed_size = ?, speed = ?, 
                speed_time = ? where task_uuid = ?";
        $sqlParams = array($totalSize, $currentSize, $speed, $speedTime, $taskuuid);
        $this->writeLog("update bd_running_info set total_object_size = $totalSize, total_object_completed_size = $currentSize, 
                speed = $speed, speed_time = $speedTime where task_uuid = $taskuuid");
        $data = $this->databaseJobVPDOObj->sqlQuery($sql, $sqlParams, true);
        //更新完成后,设置上一次时间为当前时间,作为下一次使用
        $this->lastSize = $currentSize;
        $this->lastTime = $currentTime;
    }
    
    /**
     * 更新文件恢复任务的进度和速度
     * @param unknown $taskuuid
     * @param unknown $newStatus    任务最新的状态
     * @param unknown $fileInfo
     * @return boolean
     */
    private function updateFileProgressAndSpeed($taskuuid, $newStatus, $fileInfo){
        $totalSize = $fileInfo['totalfsize'];
        $currentSize = $fileInfo['copytotalsize'];
//         $speed = $fileInfo['copyspeed'];
        
        $speedTime = time();    //数据库使用服务器时间
        
        if(empty($this->fileLastSize) || empty($this->fileLastTime)){
            $this->fileLastSize = $currentSize;
            $this->fileLastTime = $speedTime;
            return true;
        }
        
        $cSize = $currentSize - $this->fileLastSize;
        $cTime = $speedTime - $this->fileLastTime;
        $speed = round($cSize / $cTime , 2);
        if($speed < 0) $speed = 0;
        
        $statusArr = Xphp::$_config['TASKSTATUS'];
        if($newStatus == $statusArr['FINISHED']){
            //如果是完成状态
            $speed = 0;
            $totalSize = 0;
            $currentSize = 0;
        }
        
        $sql = "update bd_running_info set total_object_size = ?, total_object_completed_size = ?, speed = ?,
                speed_time = ? where task_uuid = ?";
        $sqlParams = array($totalSize, $currentSize, $speed, $speedTime, $taskuuid);
        $this->writeLog("update bd_running_info set total_object_size = $totalSize, total_object_completed_size = $currentSize,
            speed = $speed, speed_time = $speedTime where task_uuid = $taskuuid");
        $data = $this->fileJobVPDOObj->sqlQuery($sql, $sqlParams, true);
        
        $this->fileLastSize = $currentSize;
        $this->fileLastTime = $speedTime;
    }
    
    /**
     * 公共方法,通过主机uuid,得到主机信息
     * @param unknown $uuid
     */
    private function getHostInfoWithUUID($uuid, $VPDO){
        $this->paramsCheck($uuid);
        $sql = "select  ip, host_type, status from cdp_db_host where host_uuid = ?";
        $data = $VPDO->sqlQuery($sql, array($uuid), true);
        return $data[0];
    }
    
    /**
     * 公共方法,统一检查RPC消息
     * @param unknown $result
     */
    private function checkRPCMsg($result){
        $data = array();
        if(!$result['result']){
            //如果失败
            return $data;
        }
        $msgData = $result['data'];
        $msgCode = intval($msgData['msgcode']);
        if($msgCode < 0){
            //如果rpc返回 操作失败
            return $data;
        }
        return $msgData['data'];
    }
    
    
    
}