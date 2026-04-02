<?php
/*******************************************
 ** 文件远程调用处理类 
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2020-09-23 上午10:22:13
 ** @version      1.0.0
 ** @copyright    Copyright 2020 vinchin.com
 ********************************************/
class FileRPCHandler{
    private $url = array(
        //**********************公共************************//
        'getPath' => "/api/Base/getPath",                                       //获取目录或文件
        'down' => "/api/Base/down",                                             //下载指定目录下的文件
        'getTime' => "/api/Base/getTime",                                     //获取机器当前时间
        
        //**********************主站************************//
        'setHostBaseParam' => "/api/Filebk/Host/setHostBaseParam",              //配置主站的基本信息
        'addHostUser' => "/api/Filebk/Host/addHostUser",                        //新加中心TCP/IP连接用户
        'chgHostUser' => "/api/Filebk/Host/chgHostUser",                        //修改中心TCP/IP连接用户
        'delHostUser' => "/api/Filebk/Host/delHostUser",                        //删除中心TCP/IP连接用户
        'getHostUsers' => "/api/Filebk/Host/getHostUsers",                      //获取中心TCP/IP连接用户(可单可多)
        'addHostAllowBkTask' => "/api/Filebk/Host/addHostAllowBkTask",          //新加中心允许备份的任务
        'chgHostAllowBkTask' => "/api/Filebk/Host/chgHostAllowBkTask",          //修改中心允许备份的任务
        'delHostAllowBkTask' => "/api/Filebk/Host/delHostAllowBkTask",          //删除中心允许备份的任务
        'getHostAllowBkTask' => "/api/Filebk/Host/getHostAllowBkTask",          //获取中心允许备份的任务
        'getallHostAllowBkTasks' => "/api/Filebk/Host/getallHostAllowBkTasks",  //获取所有中心允许备份的任务
        'chgSearchTimeSeg' => "/api/Filebk/Host/chgSearchTimeSeg",              //修改默认搜索时间段
        'chgWeekSearchTimeSeg' => "/api/Filebk/Host/chgWeekSearchTimeSeg",      //修改自定义周搜索时间段
        'chgWorkWeekPolicy' => "/api/Filebk/Host/chgWorkWeekPolicy",            //星期日期
        'chgWorkDDayPolicy' => "/api/Filebk/Host/chgWorkDDayPolicy",            //间隔天数
        'chgWorkMonthPolicy' => "/api/Filebk/Host/chgWorkMonthPolicy",          //月份日期
        'chgWorkSpecialDayPolicy' => "/api/Filebk/Host/chgWorkSpecialDayPolicy",        //特定日期
        'chgWorkTickOutDayPolicy' => "/api/Filebk/Host/chgWorkTickOutDayPolicy",        //排除日期
        'chgWorkForceDayPolicy' => "/api/Filebk/Host/chgWorkForceDayPolicy",            //强制日期
        'addTaskTickout' => "/api/Filebk/Host/addTaskTickout",                  //新加任务排除目录或文件
        'chgTaskTickout' => "/api/Filebk/Host/chgTaskTickout",                  //修改任务排除目录或文件
        'delTaskTickout' => "/api/Filebk/Host/delTaskTickout",                  //删除任务排除目录或文件
        'getTaskTickout' => "/api/Filebk/Host/getTaskTickout",                  //获取任务排除目录或文件
        'addTaskExtPolicy' => "/api/Filebk/Host/addTaskExtPolicy",              //新加任务扩展名策略
        'chgTaskExtPolicy' => "/api/Filebk/Host/chgTaskExtPolicy",              //修改任务扩展名策略
        'delTaskExtPolicy' => "/api/Filebk/Host/delTaskExtPolicy",              //删除任务扩展名策略
        'getTaskExtPolicy' => "/api/Filebk/Host/getTaskExtPolicy",              //获取任务扩展名策略
        'getExtTypes' => "/api/Filebk/Host/getExtTypes",                        //获取扩展名代码列表
        'getFileTypes' => "/api/Filebk/Host/getFileTypes",                      //获取文件类型代码列表
        'analyDirFilesExtType' => "/api/Filebk/Host/analyDirFilesExtType",                  //分析目录下文件类型
        'getAnalyDirFilesExtType' => "/api/Filebk/Host/getAnalyDirFilesExtType",            //获取分析目录下文件类型结果
        'getAnalyDirFilesUnProcExtname' => "/api/Filebk/Host/getAnalyDirFilesUnProcExtname",//获取分析目录下没有处理的扩展名
        'freeAnalyDirFilesExtTypeTask' => "/api/Filebk/Host/freeAnalyDirFilesExtTypeTask",  //释放分析目录下扩展名任务
        'stopAnalyDirFilesExtTypeTask' => "/api/Filebk/Host/stopAnalyDirFilesExtTypeTask",  //停止分析目录下扩展名任务
        'startRecoverySrv' => "/api/Filebk/Host/startRecoverySrv",              //启动主站进入恢复等待状态(从到主恢复)
        'stopRecoverySrv' => "/api/Filebk/Host/stopRecoverySrv",                //停止主站的恢复等待状态(从到主恢复)
        'getRecoverySrvInfo' => "/api/Filebk/Host/getRecoverySrvInfo",          //获取主站的恢复等待状态(从到主恢复)
        
        
        
        //**********************从站************************//
        'AddConnectHost' => "/api/Filebk/Remote/AddConnectHost",                        //新加连接主站的信息
        'ChgConnectHost' => "/api/Filebk/Remote/ChgConnectHost",                        //修改连接主站的信息
        'DelConnectHost' => "/api/Filebk/Remote/DelConnectHost",                        //删除连接主站的信息
        'GetConnectHost' => "/api/Filebk/Remote/GetConnectHost",                        //获取连接主站的信息
        'addBackupRemotejob' => "/api/Filebk/Remote/addBackupRemotejob",                //新加远程备份任务
        'chgBackupRemotejob' => "/api/Filebk/Remote/chgBackupRemotejob",                //修改远程备份任务
        'delBackupRemotejob' => "/api/Filebk/Remote/delBackupRemotejob",                //删除远程备份任务
        'getbackupRemotejob' => "/api/Filebk/Remote/getbackupRemotejob",                //获取远程备份任务
        'getallbackupRemotejobs' => "/api/Filebk/Remote/getallbackupRemotejobs",        //获取所有远程备份任务
        'chgBackupjobHisFilePolicy' => "/api/Filebk/Remote/chgBackupjobHisFilePolicy",  //修改远程备份任务历史文件策略
        'chgBackupjobHisDirPolicy' => "/api/Filebk/Remote/chgBackupjobHisDirPolicy",    //修改远程备份任务历史目录策略
        'chgBackupjobSpacePolicy' => "/api/Filebk/Remote/chgBackupjobSpacePolicy",      //修改远程备份任务空间策略
        'chgBackupjobOverDatePolicy' => "/api/Filebk/Remote/chgBackupjobOverDatePolicy",//修改远程备份任务超期策略
        'addBackupjobUserDefExtNamePolicy' => "/api/Filebk/Remote/addBackupjobUserDefExtNamePolicy",    //远程备份任务自定义扩展名策略新加
        'chgBackupjobUserDefExtNamePolicy' => "/api/Filebk/Remote/chgBackupjobUserDefExtNamePolicy",    //远程备份任务自定义扩展名策略修改
        'delBackupjobUserDefExtNamePolicy' => "/api/Filebk/Remote/delBackupjobUserDefExtNamePolicy",    //远程备份任务自定义扩展名策略删除
        'getBackupjobUserDefExtNamePolicy' => "/api/Filebk/Remote/getBackupjobUserDefExtNamePolicy",    //远程备份任务自定义扩展名策略获取
        'startRemoteHostIpConnect' => "/api/Filebk/Remote/startRemoteHostIpConnect",    //远程启动指定的中心IP地址任务
        'stopRemoteHostIpConnect' => "/api/Filebk/Remote/stopRemoteHostIpConnect",      //远程停止指定的中心IP地址任务
        'getRemoteHostIpConnStatus' => "/api/Filebk/Remote/getRemoteHostIpConnStatus",  //获取指定的中心IP地址连接状态
        'stopOneTask' => "/api/Filebk/Remote/stopOneTask",                              //停止单个备份的任务
        'startOneTask' => "/api/Filebk/Remote/startOneTask",                            //启动单个备份的任务
        'GetTaskStatus' => "/api/Filebk/Remote/GetTaskStatus",                                //获取单个备份的任务起停状态
        
        'getTaskStaticInfos' => "/api/Filebk/Remote/getTaskStaticInfos",                //获取任务当前运行统计信息
        'getTaskWorkFiles' => "/api/Filebk/Remote/getTaskWorkFiles",                    //获取任务当前正在备份的文件
        'purgeTaskBackupFiles' => "/api/Filebk/Remote/purgeTaskBackupFiles",            //删除备份任务备份的文件
        
        'forceDoTask' => "/api/Filebk/Remote/forceDoTask",                              //强制单个备份的任务扫描一次
        'forceCrcTask' => "/api/Filebk/Remote/forceCrcTask",                            //启动单个备份的任务CRC一次
        
        
        'startRecoveryTaskCli' => "/api/Filebk/Remote/startRecoveryTaskCli",            //普通任务的恢复(从到主恢复)
        'startRecoveryFullCli' => "/api/Filebk/Remote/startRecoveryFullCli",            //传统全备差异增量备份方式恢复
        'startRecoveryDirCli' => "/api/Filebk/Remote/startRecoveryDirCli",              //指定目录或文件恢复(从到主恢复)
        'stopRecoveryCli' => "/api/Filebk/Remote/stopRecoveryCli",                      //停止从站恢复任务(从到主恢复)
        'freeRecoveryCliInfo' => "/api/Filebk/Remote/freeRecoveryCliInfo",              //释放从站恢复任务(从到主恢复)
        'getRecoveryCliInfo' => "/api/Filebk/Remote/getRecoveryCliInfo",                //获取从站恢复任务信息(从到主恢复)
        
    );
    
    private $hostIP;
    private $hostPort = 7816;
    private $header = array(
        'Authorization: 4cnFp72qejY8xaIqQnk46CW4qGh+14kL'
    );
    private $timeout;
    private $curl;
    private $rpcUrl;
    
    /**
     * 初始化
     * @param unknown $hostIP
     * @param unknown $header
     * @param number $timeout
     */
    private function config($hostIP, $header = null, $timeout = 300){
        $this->hostIP = $hostIP;
        if(!empty($header)){
            $this->header = $header;
        }
        //如果有值了就不再设置
        if(empty($this->timeout)){
            $this->timeout = $timeout;
        }
        $this->curl = Xphp::instance('Curl');
    }
    
    /**
     * 设置连接参数
     * 这里主要是开放给监控程序,好配置超时时间,如果出现一个机器忽然断线,监控程序会卡很久
     * @param unknown $params
     */
    public function setDefaultParams($params){
        if(!empty($params['timeout'])){
            $this->timeout = $params['timeout'];
        }
    }
    
    /**
     * get url
     * @param unknown $key
     */
    private function get($key, $data){
        $this->rpcUrl = 'http://' . $this->hostIP . ':' . $this->hostPort . $this->url[$key] . "?";
        foreach ($data as $key => $value){
            $this->rpcUrl .= $key . "=" . $value . "&";
        }
        $data = json_encode($data);
        return $this->curl->get($this->rpcUrl, $data, $this->header, $this->timeout);
    }
    
    /**
     * post put del url
     * @param unknown $key
     */
    private function post($key, $data){
        $this->rpcUrl = 'http://' . $this->hostIP . ':' . $this->hostPort . $this->url[$key];
        $data = json_encode($data);
        return $this->curl->post($this->rpcUrl, $data, $this->header, $this->timeout);
    }
    
    /**
     * put url
     * @param unknown $key
     */
    private function put($key, $data){
        $this->rpcUrl = 'http://' . $this->hostIP . ':' . $this->hostPort . $this->url[$key];
        $data = json_encode($data);
        return $this->curl->put($this->rpcUrl, $data, $this->header, $this->timeout);
    }
    
    /**
     * del url
     * @param unknown $key
     */
    private function del($key, $data){
        $this->rpcUrl = 'http://' . $this->hostIP . ':' . $this->hostPort . $this->url[$key];
        $data = json_encode($data);
        return $this->curl->del($this->rpcUrl, $data, $this->header, $this->timeout);
    }
    
    
    //*****************主机管理*****************start//
    /**
     * 获取目录或文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getPath($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 下载指定目录下的文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function down($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取机器当前时间
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getTime($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     * 配置主站的基本信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function setHostBaseParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 新加中心TCP/IP连接用户
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addHostUser($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改中心TCP/IP连接用户
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgHostUser($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *删除中心TCP/IP连接用户
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delHostUser($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取中心TCP/IP连接用户(可单可多)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getHostUsers($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *新加中心允许备份的任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addHostAllowBkTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改中心允许备份的任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgHostAllowBkTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *删除中心允许备份的任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delHostAllowBkTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取中心允许备份的任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getHostAllowBkTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取所有中心允许备份的任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getallHostAllowBkTasks($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改默认搜索时间段
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgSearchTimeSeg($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改自定义周搜索时间段
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgWeekSearchTimeSeg($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *星期日期
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgWorkWeekPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *间隔天数
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgWorkDDayPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *月份日期
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgWorkMonthPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *特定日期
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgWorkSpecialDayPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *排除日期
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgWorkTickOutDayPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *强制日期
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgWorkForceDayPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *新加任务排除目录或文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addTaskTickout($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改任务排除目录或文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgTaskTickout($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *删除任务排除目录或文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delTaskTickout($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取任务排除目录或文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getTaskTickout($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *新加任务扩展名策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addTaskExtPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改任务扩展名策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgTaskExtPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *删除任务扩展名策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delTaskExtPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取任务扩展名策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getTaskExtPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取扩展名代码列表
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getExtTypes($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取文件类型代码列表
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getFileTypes($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *分析目录下文件类型
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function analyDirFilesExtType($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取分析目录下文件类型结果
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getAnalyDirFilesExtType($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取分析目录下没有处理的扩展名
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getAnalyDirFilesUnProcExtname($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *释放分析目录下扩展名任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function freeAnalyDirFilesExtTypeTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *停止分析目录下扩展名任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopAnalyDirFilesExtTypeTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *启动主站进入恢复等待状态(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRecoverySrv($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *停止主站的恢复等待状态(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopRecoverySrv($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取主站的恢复等待状态(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRecoverySrvInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    /**
     *新加连接主站的信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function AddConnectHost($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改连接主站的信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function ChgConnectHost($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *删除连接主站的信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function DelConnectHost($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取连接主站的信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function GetConnectHost($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     *新加远程备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addBackupRemotejob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改远程备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgBackupRemotejob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *删除远程备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delBackupRemotejob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取远程备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getbackupRemotejob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取所有远程备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getallbackupRemotejobs($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改远程备份任务历史文件策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgBackupjobHisFilePolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改远程备份任务历史目录策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgBackupjobHisDirPolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改远程备份任务空间策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgBackupjobSpacePolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *修改远程备份任务超期策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgBackupjobOverDatePolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *远程备份任务自定义扩展名策略新加
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addBackupjobUserDefExtNamePolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *远程备份任务自定义扩展名策略修改
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgBackupjobUserDefExtNamePolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *远程备份任务自定义扩展名策略删除
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delBackupjobUserDefExtNamePolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *远程备份任务自定义扩展名策略获取
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getBackupjobUserDefExtNamePolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    /**
     *修改远程备份任务扩展名策略
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgBackupjobExtNamePolicy($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *远程启动指定的中心IP地址任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRemoteHostIpConnect($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *远程停止指定的中心IP地址任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopRemoteHostIpConnect($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取指定的中心IP地址连接状态
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRemoteHostIpConnStatus($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *停止单个备份的任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopOneTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *启动单个备份的任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startOneTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取单个备份的任务起停状态
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function GetTaskStatus($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     *获取任务当前运行统计信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getTaskStaticInfos($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取任务当前正在备份的文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getTaskWorkFiles($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    /**
     *删除备份任务备份的文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function purgeTaskBackupFiles($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *强制单个备份的任务扫描一次
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function forceDoTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *启动单个备份的任务CRC一次
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function forceCrcTask($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    /**
     *普通任务的恢复(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRecoveryTaskCli($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *传统全备差异增量备份方式恢复
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRecoveryFullCli($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *指定目录或文件恢复(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRecoveryDirCli($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *停止从站恢复任务(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopRecoveryCli($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *释放从站恢复任务(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function freeRecoveryCliInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     *获取从站恢复任务信息(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRecoveryCliInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    
    //*****************后台监控*******************start//
    
    
    //*****************后台监控*******************end//

}

?>