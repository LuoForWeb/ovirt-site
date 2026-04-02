<?php
/******************************************* 
** 数据库远程调用处理类 
** 
** @author       xiezhuowei@vinchin.com
** @date         2019-11-26 
** @version      1.0.0 
** @copyright    Copyright 2019 vinchin.com 
********************************************/
class DbRPCHandler{
    private $url = array(
        //主机管理
        'getHostInstance' => "/api/Hotdb/getInstances",             //获取可以备份的实例   
        'getRunLogs' => "/api/Base/getRunLogs",                     //获取运行日志
        'hostInfo' => "/api/Base/getOsInfo",                        //获取操作系统信息
        'scanHostDB' => "/api/Hotdb/scandbnames",                   //获取可以备份的数据库
        'standbyHostDirCheck' => "/api/Hotdb/standbyhostdircheck",  //磁盘目录可写检查
        'getHostService' => "/api/Base/getOsServices",              //获取机器服务程序信息
        'getHostNetworkCard' => "/api/Base/getNetworkCards",        //获取机器网卡信息
        
        'loginDbTest' => "/api/Hotdb/loginDbTest",                  //数据库登陆测试连接
        'addDbLoginParam' => "/api/Hotdb/addDbLoginParam",          //新加数据库登陆参数
        'delDbLoginParam' => "/api/Hotdb/delDbLoginParam",          //删除数据库登陆参数
        'chgDbLoginParam' => "/api/Hotdb/chgDbLoginParam",          //修改数据库登陆参数
        'getDbLoginParam' => "/api/Hotdb/getDbLoginParam",          //获取数据库登陆参数
        
        
        //任务管理
        'createBackupJob' => "/api/Hotdb/createbackupjob",          //创建备份任务
        'addHostUser' => "/api/Hotdb/addHostUser",                  // 新加中心TCP/IP连接用户	
        'delBackupRemotejob' => "/api/Hotdb/delBackupRemotejob",            //删除远程备份任务
        'getallbackupRemotejobs' => "/api/Hotdb/getallbackupRemotejobs",    //获取所有远程备份任务
        'setRemoteDbArcMgParam' => "/api/Hotdb/setRemoteDbArcMgParam",      //设定远程数据库备份日志管理参数
        'getRemoteDbArcMgParam' => "/api/Hotdb/getRemoteDbArcMgParam",      //获取远程数据库备份日志管理参数
        'startRemoteHostIpTasks' => "/api/Hotdb/startRemoteHostIpTasks",    //远程启动指定的中心IP地址任务
        'stopRemoteHostIpTasks' => "/api/Hotdb/stopRemoteHostIpTasks",    //远程停止指定的中心IP地址任务
        'getRemoteHostIpConnStatus' => "/api/Hotdb/getRemoteHostIpConnStatus",    //获取指定的中心IP地址连接状态
        
        
        'startService' => "/api/BkService/startService",          //启动备份系统
        'reStartService' => "/api/BkService/reStartService",      //重启备份系统
        'stopService' => "/api/BkService/stopService",            //停止备份系统
        'getServiceStatus' => "/api/BkService/getServiceStatus",    //获取备份系统状态
        'setServiceStartMode' => "/api/BkService/setServiceStartMode",  //配置子系统启动模式
        
//         'modifyBackupJob' => '/api/modifyBackup',
//         'deleteBackupJob' => '/api/deleteBackup',
        
        //恢复
        'setRecoveryStatus' => "/api/Hotdb/setRecoveryStatus",      //设定恢复状态
        'startRecoveryJob' => "/api/Hotdb/startRecovery",           //启动恢复
        'stopRecoveryJob' => "/api/Hotdb/stopRecovery",             //停止恢复
        'getRecoveryInfo' => "/api/Hotdb/getRecoveryInfo",          //获取恢复信息
        'freeRecoveryInfo' => "/api/Hotdb/freeRecoveryInfo",        //释放恢复信息
        
        //异机恢复
        'startRecoverySrv' => "/api/Hotdb/startRecoverySrv",        //启动主站进入恢复等待状态(从到主恢复)
        'stopRecoverySrv' => "/api/Hotdb/stopRecoverySrv",          //停止主站的恢复等待状态(从到主恢复)
        'startRecoveryCli' => "/api/Hotdb/startRecoveryCli",        //启动从站进入恢复状态(从到主恢复)
        'stopRecoveryCli' => "/api/Hotdb/stopRecoveryCli",          //停止从站恢复任务(从到主恢复)
        'freeRecoveryCliInfo' => "/api/Hotdb/freeRecoveryCliInfo",  //释放从站恢复信息(从到主恢复)
        'getRecoveryCliInfo' => "/api/Hotdb/getRecoveryCliInfo",    //获取从站恢复信息(从到主恢复)
        'getRecoveryCliAll' => "/api/Hotdb/getRecoveryCliAll",      //获取从站所有恢复信息(从到主恢复) 
        'attachRecoveryDb' => "/api/Hotdb/attachRecoveryDb",        //附加所恢复的数据库
        
        
        //接管
        'readAdapterMap' => "/api/TakeOver/readAdapterMap",         //获取接管配置的ADAPTER
        'saveAdapterMap' => "/api/TakeOver/saveAdapterMap",         //配置接管的ADAPTER
        'checkIfTakeOverId' => "/api/TakeOver/checkIfTakeOverId",   //检查系统是否接管
        'readParam' => "/api/TakeOver/readParam",                   //获取接管的参数
        'saveParam' => "/api/TakeOver/saveParam",                   //配置接管的参数
        'addTakeObj' => "/api/TakeOver/addTakeObj",                 //新加接管的对象
        'chgTakeObj' => "/api/TakeOver/chgTakeObj",                 //修改接管的对象
        'delTakeObj' => "/api/TakeOver/delTakeObj",                 //删除接管的对象
        'getTakeObj' => "/api/TakeOver/getTakeObj",                 //获取接管的对象
        'getTakeAllObjs' => "/api/TakeOver/getTakeAllObjs",         //获取所有接管的对象
        'manualStartTakeOver' => "/api/TakeOver/manualStartTakeOver",   //启动立即接管
        'autoStartTakeOver' => "/api/TakeOver/autoStartTakeOver",       //启动自动接管
        'manualStopTakeOver' => "/api/TakeOver/manualStopTakeOver",     //停止接管
        'setDbBkAutoParam' => "/api/Hotdb/setDbBkAutoParam",        //设定从站自动接管参数
        'getStartInfo' => "/api/TakeOver/getStartInfo",             //获取接管运行结果
        'getStopInfo' => "/api/TakeOver/getStopInfo",               //获取接管停止结果
        
        
        //备份数据
        'scanBackupDatabase' => "/api/Hotdb/scanBackupDatabase",    //获取已备份的数据库
        'scanBackupTimepoint' => "/api/Hotdb/scanBackupTimepoint",  //获取数据库的备份时间点
        'scanBackupFiles' => "/api/Hotdb/scanBackupFiles",          //获取已备份数据库的文件
        'purgeDbBkData' => "/api/Hotdb/purgeDbBkData",              //清除备份数据
        'getDbSysConfig' => "/api/Hotdb/getDbSysConfig",            //获取数据库系统安装配置
        
        //任务监控
        'getBackupStatus' => "/api/Hotdb/getBackupStatus",          //获取备份系统主从连接信息和运行消息
        'getBackupDbMStatus' => "/api/Hotdb/getBackupDbMStatus",      //获取正在备份的数据库(除文件)状态,快速获取
        'getBackupDbStatus' => "/api/Hotdb/getBackupDbStatus",      //获取正在备份的数据库状态
        'freeBackupStopDbQuen' => "/api/Hotdb/freeBackupStopDbQuen",      //释放停止备份的数据库状态队列
        
        //授权管理
        'getEnvStr' => "/api/License/getEnvStr",                    //获取环境码
        'upLicenseFile' => "/api/License/upLicenseFile",            //上传授权文件
        'setLicenseCenter' => "/api/License/setLicenseCenter",      //设置成授权中心
        'clrLicenseCenter' => "/api/License/clrLicenseCenter",      //清除成授权中心
        'getLicenseCenter' => "/api/License/getLicenseCenter",      //获取授权中心配置状态
        'addLicenseCenterUser' => "	/api/License/addLicenseCenterUser", //新加授权中心允许用户
        'setNetLicenseSrv' => "/api/License/setNetLicenseSrv",      //配置网络获取授权机器
        'clrNetLicenseSrv' => "/api/License/clrNetLicenseSrv",      //清除网络获取授权机器
        'actLicenseCfgValid' => "/api/License/actLicenseCfgValid",  //激活当前授权配置生效
        'getNetLicenseSrv' => "/api/License/getNetLicenseSrv",      //获取网络获取授权配置
        'getLocalMachineAuthorInfo' => "/api/License/getLocalMachineAuthorInfo",          //获取本机授权信息
        'getLicenseCenterAuthorInfo' => "/api/License/getLicenseCenterAuthorInfo",     //获取授权中心授权信息
        'freeLicenseCenterAuth' => "/api/License/freeLicenseCenterAuth",                //授权中心释放该IP的授权
        
        
        'addLocAuthorSyscode' => "/api/License/addLocAuthorSyscode",        //将系统代码加入本地授权许可列表
        'delLocAuthorSyscode' => "/api/License/delLocAuthorSyscode",        //将系统代码从本地授权许可列表中删除
        'getLocAuthorSyscodes' => "/api/License/getLocAuthorSyscodes",      //获取本地授权许可列表
        'setLocAuthorTableMode' => "/api/License/setLocAuthorTableMode",    //配置本地授权许可列表性质
        'getLocAuthorTableMode' => "/api/License/getLocAuthorTableMode",    //获取本地授权许可列表性质
        
        
        //后台监控
        'monitorHostInfo' => '/api/Base/getOsInfo',
    );
    
    private $hostIP;
    private $hostPort = 7816;
    private $header = array(
//         'Accept: application/vnd.vinchin-v1+json',
        'Authorization: 4cnFp72qejY8xaIqQnk46CW4qGh+14kL'
        //     'Authorization: 4cnFp72qejY8xaIqQnk46CW4qGh+14kLNcyrNtWldDofTqMlyIT+PVrV0Q5cscRfz+otTL0C4XfJTmWW0ZT6vfioZklq+A9eK5AEgFTWGMkwvhHeH3+iXiK2R2Bq4xNdSAOfGZOydhkNPwpl4MgklbDfocLzG9ldKFs\/yz9FbHrGT2B0iiT45vjUX4nxu5d8pJZOSbzDsdjcJaPdLR1e4gtTt9gDS8l6VKahgWdl4nZA4CteZznyDw2t8FLwpzi\/'
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
     * 获取主机信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function hostInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取实例名称
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getHostInstance($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 扫描实例下的数据库
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function scanHostDB($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 备份目录检查
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function standbyHostDirCheck($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取主机服务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getHostService($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 得到主机网卡信息
     * @param unknown $hostIP
     * @param unknown $data
     */
    public function getHostNetworkCard($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 数据库登陆测试连接
     * @param unknown $hostIP
     * @param unknown $data
     */
    public function loginDbTest($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 新加数据库登陆参数
     * @param unknown $hostIP
     * @param unknown $data
     */
    public function addDbLoginParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 删除数据库登陆参数
     * @param unknown $hostIP
     * @param unknown $data
     */
    public function delDbLoginParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 修改数据库登陆参数
     * @param unknown $hostIP
     * @param unknown $data
     */
    public function chgDbLoginParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取数据库登陆参数
     * @param unknown $hostIP
     * @param unknown $data
     */
    public function getDbLoginParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    //*****************主机管理*******************end//
    
    //*****************任务管理*****************start//
    
    /**
     * 创建实时备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function createBackupJob($hostIP, $data){
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
     * 删除远程备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delBackupRemotejob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取所有远程备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getallbackupRemotejobs($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 设定远程数据库备份日志管理参数
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function setRemoteDbArcMgParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取远程数据库备份日志管理参数
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRemoteDbArcMgParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     * 远程启动指定的中心IP地址任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRemoteHostIpTasks($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 远程停止指定的中心IP地址任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopRemoteHostIpTasks($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取指定的中心IP地址连接状态
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRemoteHostIpConnStatus($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 启动实时备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startService($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 重启
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function reStartService($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     * 停止实时备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopService($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取实时任务任务是否启动状态
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getServiceStatus($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 配置子系统启动模式
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function setServiceStartMode($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     * 修改实时备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function modifyBackupJob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 删除实时备份任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function deleteBackupJob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 设置恢复任务状态
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function setRecoveryStatus($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     * 启动恢复任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRecoveryJob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 停止恢复任务
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopRecoveryJob($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 扫描主机已备份的数据库
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function scanBackupDatabase($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 扫描数据库的某个时间点周围的可恢复点
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function scanBackupTimepoint($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取已备份数据库的文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function scanBackupFiles($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 清除备份数据
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function purgeDbBkData($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取数据库系统安装配置
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getDbSysConfig($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取备份任务监控详情
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getBackupStatus($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取备份任务监控详情
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRunLogs($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取正在备份的数据库(除文件)状态
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getBackupDbMStatus($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取备份数据库监控详情
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getBackupDbStatus($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     * 释放停止备份的数据库状态队列
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function freeBackupStopDbQuen($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取恢复任务监控详情
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRecoveryInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 释放恢复信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function freeRecoveryInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 启动主站进入恢复等待状态(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRecoverySrv($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 停止主站的恢复等待状态(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopRecoverySrv($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 启动从站进入恢复状态(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function startRecoveryCli($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 停止从站恢复任务(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function stopRecoveryCli($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 释放从站恢复信息(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function freeRecoveryCliInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取从站恢复信息(从到主恢复)
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRecoveryCliInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取从站所有恢复信息(从到主恢复) 
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getRecoveryCliAll($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 附加所恢复的数据库
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function attachRecoveryDb($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    /**
     * 获取接管配置的ADAPTER
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function readAdapterMap($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 配置接管的ADAPTER
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function saveAdapterMap($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 检查系统是否接管
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function checkIfTakeOverId($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取接管的参数
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function readParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 配置接管的参数
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function saveParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 新加接管的对象
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addTakeObj($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 修改接管的对象
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function chgTakeObj($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 删除接管的对象
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delTakeObj($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取接管的对象
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getTakeObj($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取所有接管的对象
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getTakeAllObjs($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 手动启动接管
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function manualStartTakeOver($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 启动自动接管
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function autoStartTakeOver($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 手动停止接管
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function manualStopTakeOver($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 设定从站自动接管参数
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function setDbBkAutoParam($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取接管运行结果
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getStartInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取接管停止结果
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getStopInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    //*****************任务管理*******************end//
    
    /**
     * 获取环境信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getEnvStr($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 上传授权文件
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function upLicenseFile($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 设置成授权中心
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function setLicenseCenter($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 清除成授权中心
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function clrLicenseCenter($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    /**
     * 获取授权中心配置状态
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getLicenseCenter($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 新加授权中心允许用户
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addLicenseCenterUser($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    /**
     * 配置网络获取授权机器
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function setNetLicenseSrv($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 清除网络获取授权机器
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function clrNetLicenseSrv($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 激活当前授权配置生效
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function actLicenseCfgValid($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取网络获取授权配置
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getNetLicenseSrv($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取本机授权信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getLocalMachineAuthorInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取授权中心授权信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getLicenseCenterAuthorInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 授权中心释放该IP的授权
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function freeLicenseCenterAuth($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    /**
     * 获取授权信息
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getAuthorInfo($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 将系统代码加入本地授权许可列表
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function addLocAuthorSyscode($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 将系统代码从本地授权许可列表中删除
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function delLocAuthorSyscode($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取本地授权许可列表
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getLocAuthorSyscodes($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 配置本地授权许可列表性质
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function setLocAuthorTableMode($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    /**
     * 获取本地授权许可列表性质
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function getLocAuthorTableMode($hostIP, $data){
        $this->config($hostIP);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    
    
    //*****************授权管理*******************end//
    
    
    
    //*****************后台监控*******************start//
    /**
     * 主机后台监控
     * @param unknown $hostIP
     * @param unknown $data
     * @return unknown
     */
    public function monitorHostInfo($hostIP, $data){
        $this->config($hostIP, null, 3);
        return $this->post(__FUNCTION__, $data);
    }
    
    
    //*****************后台监控*******************end//

}

?>