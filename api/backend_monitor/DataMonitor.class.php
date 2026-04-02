<?php
/******************************************* 
** 消息推送处理类
** 
** @author       
** @date         2018-04-08
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
require_once RUN_PATH.'backend_monitor/DataHandler.class.php';
class DataMonitor extends OPHandler{
    //首页后台数据监控进程号
    protected  $dataPid = 0;
    
    protected $settings = array();
    
    //初始化进程VPDO
    protected $reportVPDOObj;
    
    
    
    public function __construct(){
        $this->startHomeMonitor(); //创建首页后台数据监控进程
        
    }
    
    
    public function __destruct(){
        $this->writeLog("stop data monitor!");
    }
    
    public function getSystemConf(){
    	$str= file_get_contents('/etc/backup_system/common_server.conf.xml');
    	return $str;
    }
    
    
    
    /**
     * 开启后台数据监控
     */
    private function startHomeMonitor(){
        $this->writeLog("start backend data monitor!");
        $this->writeLog("Create backend data monitor process success");
        $this->writeLog("Insert data into bd_cpu_memory_monitor and bd_network_monitor!!!!!");
		$dataHandler = new DataHandler();
		$nodeuuid = $dataHandler->getLocalNodeuud();
    	while (true){
    	    $this->writeLog("start xunhuan!!!!!");
    		$newSettings = $this->getSystemConf(); //获取系统配置信息
    		//获取config_dir
    		$list = explode("<config_dir>", $newSettings);
    		$listOne = explode("</config_dir>", $list[1]);
    		$configDir = $listOne[0];
    		if(file_exists($configDir."/backup-system/uuid/node-sid")){ //检查系统文件是否存在
    			if(md5(json_encode($newSettings)) != md5(json_encode($this->settings))){ //如果系统配置文件改变
    				$this->writeLog("Settings change, try reconnect!!!!!");
//     				$this->reconnectVDPD();//尝试重新连接
    				$this->settings = $newSettings;
    			}
    			$data = $dataHandler->getMonitorData();//获取后台监控数据
    			if($data){
    			    $dataHandler->addCpuMemoryMonitor($nodeuuid, $data);
    			    $dataHandler->addNetworkMonitor($nodeuuid, $data);
    			}
    			$cpuCount = $dataHandler->getCpuRecordCount($nodeuuid); //获取监控数据记录数 规定为200条，如果超出要执行删除旧记录
    				
    			//检查是否需要删除多的记录
    			if($cpuCount >200){
    			    $count = $cpuCount - 200;
    				$result = $dataHandler->deleteCpuMemoryDate($count,$nodeuuid);
    			}
    			//检查删除节点网卡超出条
    			$networkData = $data['netData'];
    			foreach ($networkData as $d){
    			    $networkCount = $dataHandler->getNetworkCardCount($nodeuuid, $d['card']);
    			    //删除该网卡超出记录
    			    if($networkCount > 200){
    			        $count = $networkCount - 200;
    			        $result = $dataHandler->deleteNetworkDate($count,$nodeuuid, $d['card']); //删除网卡旧记录
    			    }
    			}
    		}else{
    			$this->writeLog("system file does not exist!!!!!!!!!!");
    			sleep(60);
    		}
    			
    	}
    }
    
    
}