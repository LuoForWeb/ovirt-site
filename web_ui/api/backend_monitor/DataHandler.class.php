<?php
/******************************************* 
** 后台数据统一处理
** 
** @author       luokai@vinchin.com 
** @date         2019-03-15 上午9:40 
** @version      1.0.0 
** @copyright    Copyright 2019 vinchin.com 
********************************************/
require_once RUN_PATH.'backend_monitor/OPHandler.class.php';
class DataHandler extends OPHandler{
	/**
	 * //TODO
	 * 得到监控数据
	 * @param unknown $params
	 */
	public function getMonitorData(){
		$networkCard = $this->getNetworkCardName();
		$firstNetwork = $this->getNetworkSize($networkCard);
// 		$firstCPU = $this->getCpuInfo();
		$firstCpuList = $this->getCpuDetails();
		$time1 = time();
		$cputest = array();
		$cmdCpu = "top -n 2 -b -d 2 |grep Cpu";
		exec($cmdCpu, $cputest);
		$cpuList = explode(",", $cputest[1]);
		$cpuList1 = explode(" ", trim($cpuList[3]));
		
		//计算CPU使用率
		$cpuRate = round(100 - floatval($cpuList1[0]), 2);
		$time2 = time();
		$sleepTime = 2;
		$time3 = $time2-$time1;
		if($time3 > 0 && $time3 < 2){
			$sleepTime = 2 - $time3;
		}else{
			$sleepTime = 0;
		}
		$this->writeLog("sleep time ddd.......----". $sleepTime);
		sleep($sleepTime);
		$secondNetwork = $this->getNetworkSize($networkCard);
// 		$secondCPU = $this->getCpuInfo();
		$secondCpuList = $this->getCpuDetails();
		
		//计算CPU每个核信息
		$cpuDetails = array();
		$row = count($firstCpuList);
		$cmd = "cat /proc/cpuinfo |grep MHz";
		exec($cmd, $mhz);
		$value = 0;
		foreach ($mhz as $d){
			$mhzRow = explode(":", $d);
			$cpuMhz = trim($mhzRow[1]);
			$value += $cpuMhz;
		}
		
		for($i=0;$i<$row;$i++){
			$cpuTotal = $secondCpuList[$i]['total'] - $firstCpuList[$i]['total'];
			$cpuUsed = $secondCpuList[$i]['used'] - $firstCpuList[$i]['used'];
			$rate = round($cpuUsed/$cpuTotal * 100, 2);
			$mHz = round(floatval($value) * $cpuUsed/$cpuTotal, 2); 
			$cpuDetails[] = array(
				'name' => $firstCpuList[$i]['name'],
				'cpu_rate' => $rate,
				'mhz' => $mHz
			);
		}
		$totalUsed = round(floatval($value) * $cpuRate/100, 2);
		$cpuData = array(
			'cpuRate' => $cpuRate,
			'cpu_total' => $value,
			'cpu_used' => $totalUsed,
			'details' => array(
				"cpu_list" => $cpuDetails
			)
		);
		$memData = $this->getMemorydata();
		$network = array();
		//计算网络流量
		foreach ($secondNetwork as $key => $value){
			$receive = $value['receive'] - $firstNetwork[$key]['receive'];    //接收
			$transmit = $value['transmit'] - $firstNetwork[$key]['transmit'];   //发送
			if($receive < 0){
			    $receive = 0;
			}
			if($transmit < 0){
			    $transmit = 0;
			}
			$network[] = array(
					'card' => $value['card'],
					'receive' => round($receive / 1024 / 2 , 2),
					'transmit' => round($transmit / 1024 / 2, 2),
// 					'mac_id' => $this->getNetworkMac($value['card']),
					'myTime' => date("Y-m-d H:i:s")
			);
		}
	
		//显示接收(备份)和发送(恢复)流量
		$charData = array(
				"cpuData" => $cpuData,
				"memData" => $memData,
				"netData" => $network,
				"timeInterval" => time(),
				"currentTime" => date("Y-m-d H:i:s")
		);
	
		return $charData;
	}
	
	public function addCpuMemoryMonitor($nodeuuid, $data){
	    $this->writeLog('start insert cpu data!!!!! ');
	    //获取有超出当前时间的把数据全部清除
	    $this->checkCurrentCpuMemory($nodeuuid);
		$cpuData = $data['cpuData'];
		$memoryData = $data['memData'];
		$time = $data['currentTime'];
		$nodeuuid = $this->getLocalNodeuud();
		$cpuRate = $cpuData['cpuRate'];
		$cpuTotal = $cpuData['cpu_total'];
		$cpuUsed = $cpuData['cpu_used'];
		$details = json_encode($cpuData['details']);
		$memoryRate = $memoryData['memRate'];
		$memTotal = $memoryData['memory_total'];
		$memUsed = $memoryData['memory_used'];
		$sqlParams = array($time, $cpuRate, $memoryRate, $cpuTotal, $cpuUsed, $memTotal, $memUsed, $nodeuuid,$details);
		$sqlInsert = "insert into bd_cpu_memory_monitor (monitor_time, cpu_rate, memory_rate, cpu_total, cpu_used, memory_total, memory_used, node_uuid, details)
				values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$result = $this->dbQuery($sqlInsert, $sqlParams);
		$this->writeLog('end insert cpu data!!!!! '. $result);
		return true;
	}
	
	public function getLocalNodeuud(){
		$str= file_get_contents('/etc/backup_system/common_server.conf.xml');
		//获取config_dir
		$list = explode("<config_dir>", $str);
		$listOne = explode("</config_dir>", $list[1]);
		$configDir = $listOne[0];
		$cmd = "cat ".$configDir."/backup-system/uuid/node-sid";
		exec($cmd, $output);
		return $output[0];
	}
	
	public function addNetworkMonitor($nodeuuid, $data){
	    //获取有超出当前时间的把数据全部清除
	    $this->checkCurrentNetwork($nodeuuid);
		$networkList =$data['netData'];
		$nodeuuid = $this->getLocalNodeuud();
		foreach($networkList as $net){
			$time = $net['myTime'];
			$name = $net['card'];
			$receive = $net['receive'];
			$transmit = $net['transmit'];
			$sqlParams = array($time, $name, $receive, $transmit, $nodeuuid);
			$sqlInsert = "insert into bd_network_monitor (monitor_time, card_name, receive_size, transmit_size, node_uuid)
				values (?, ?, ?, ?, ?)";
			$result = $this->dbQuery($sqlInsert, $sqlParams);
		}
		return true;
	}
	
	/**
	 * 获取首页后台数据记录条数
	 */
	public function getCpuRecordCount($nodeuuid){
		$cpuMemSql = "select count(*) as total from bd_cpu_memory_monitor where node_uuid = ?";
		$dataCpuMem = $this->dbSelect($cpuMemSql, array($nodeuuid));
		$cpuMemCount = intval($dataCpuMem[0]['total']);
		return $cpuMemCount;
		 
	}
	
	/**
	 * 获取首页后台数据记录条数
	 */
	public function getNetworkCardCount($nodeuuid, $cardName){
	    $networkSql = "select count(*) as total from bd_network_monitor where node_uuid = ? and card_name = ?";
	    $dataNetwork = $this->dbSelect($networkSql, array($nodeuuid, $cardName));
	    $networkCount = intval($dataNetwork[0]['total']);
	    return $networkCount;
	    
	}
	
	
	/**
	 * 清理cpu和内存旧数据条数
	 * @param unknown $num
	 * @return boolean
	 */
	public function deleteCpuMemoryDate($num, $nodeuuid){
		$sql = "delete from bd_cpu_memory_monitor where node_uuid = ? order by monitor_time asc limit ?";
		$result = $this->dbExec($sql, array($nodeuuid, $num));
		return $result;
	}
	
	/**
	 * 如果时间回退删除所有数据
	 */
	public function checkCurrentCpuMemory($nodeuuid){
	    
	    $sql = "select monitor_time from bd_cpu_memory_monitor where node_uuid = ? and unix_timestamp(monitor_time) > ?";
	    $currentTime = time();
	    $data = $this->dbSelect($sql, array($nodeuuid, $currentTime));
	    if(!empty($data)){
	        $deleteSql = "delete from bd_cpu_memory_monitor where node_uuid = ? and unix_timestamp(monitor_time) > ?";
	        $result = $this->dbExec($deleteSql, array($nodeuuid, $currentTime));
	        $this->writeLog('delete cpu memory data '. $result);
	        return $result;
	    }
	    
	    return true;
	}
	
	/**
	 * 清理网卡监控旧数据条数
	 * @param unknown $num
	 * @return boolean
	 */
	public function deleteNetworkDate($num,$nodeuuid, $cardName){
		$sql = "delete from bd_network_monitor where node_uuid = ? and card_name = ? order by monitor_time asc limit ?";
		$result = $this->dbExec($sql, array($nodeuuid, $cardName, $num));
		return $result;
	}
	
	/**
	 * 如果时间回退删除所有数据
	 */
	public function checkCurrentNetwork($nodeuuid){
	    
	    $sql = "select monitor_time from bd_network_monitor where node_uuid = ? and unix_timestamp(monitor_time) > ?";
	    $currentTime = time();
	    $data = $this->dbSelect($sql, array($nodeuuid, $currentTime));
	    if(!empty($data)){
	        $deleteSql = "delete from bd_network_monitor where node_uuid = ? and unix_timestamp(monitor_time) > ?";
	        $result = $this->dbExec($deleteSql, array($nodeuuid, $currentTime));
	        $this->writeLog('delete network data '. $result);
	        return $result;
	    }
	    
	    return true;
	}
	
	
	private function getNetworkMac($name){
		$cmd = "cat /sys/class/net/".$name."/address";
		exec($cmd, $mac);
		return $mac[0];
	}
	
	/**
	 * 根据所有网卡的流量
	 * @param array $networkCard    网卡名
	 * @return array    [Receive,Transmit]
	 */
	private function getNetworkSize($networkCard){
		$network = array();
		foreach ($networkCard as $card){
			$cardData = array();
			//获取网络接收数据
			$cmd = 'cat /proc/net/dev | grep ' . $card . ' | tr : " " | awk \'{print $2}\'';
			$receive = intval(exec($cmd));
			//获取网络发送数据
			$cmd = 'cat /proc/net/dev | grep ' . $card . ' | tr : " " | awk \'{print $10}\'';
			$transmit = intval(exec($cmd));
	
			$network[] = array(
					'card' => $card,
					'receive' => $receive,
					'transmit' => $transmit
			);
		}
		return $network;
	}
	
	/**
	 * 得到所有网卡名字
	 * @return array
	 */
	private function getNetworkCardName(){
		$cmd = "cat /proc/net/dev";
		exec($cmd, $output);
		$rows = count($output);
		$networkCard = array();
		for($i=2; $i<$rows; $i++){
			$netRow = explode(":", $output[$i]);
			if(trim($netRow[0]) == "lo") continue;
			$networkCard[] = trim($netRow[0]);
		}
		return $networkCard;
	}
	
	private function getMemorydata(){
		$cmd = "free -b";
		exec($cmd, $output);
		$memRow = explode(":", $output[1]);
		$memRow1 = explode(" ", $memRow[1]);
		$arr = array_filter($memRow1);// 删除空元素 
		$memArr = array();
		foreach ($arr as $value){
			$memArr[] = $value;
		}
		$used = $memArr[1];
		$total = $memArr[0];
		$rate = round($used/$total * 100, 2);
		$data = array(
			'memRate' => $rate, 
			'memory_used' => intval($used), 
			'memory_total' => intval($total)
		);
		return $data;
	}
	
	/**
	 * 得到CPU使用信息
	 * @return array    [总时间, 使用时间]
	 */
	private function getCpuInfo(){
		$mode = "/(cpu)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)[\s]+([0-9]+)/";
		$string=shell_exec("more /proc/stat");
		preg_match_all($mode,$string,$arr);
		
		//得到CPU使用总时间
		$total = $arr[2][0] + $arr[3][0] + $arr[4][0] + $arr[5][0] + $arr[6][0] + $arr[7][0] + $arr[8][0] + $arr[9][0];
		//得到CPU使用时间
		$used = $arr[2][0] + $arr[3][0] + $arr[4][0] + $arr[6][0] + $arr[7][0] + $arr[8][0] + $arr[9][0];
		return array($total, $used);
	}
		
	private function getCpuDetails(){
		$cmd = "cat /proc/stat |grep cpu";
		exec($cmd,$output);
		$rows = count($output);
		$info = array();
		for($i=0; $i<$rows; $i++){
			$cpuRow = explode(" ", $output[$i]);
			$total = intval($cpuRow[1]) + intval($cpuRow[2]) + intval($cpuRow[3]) + intval($cpuRow[4]) + intval($cpuRow[5]) + intval($cpuRow[6]) + intval($cpuRow[7]) + intval($cpuRow[8]) + intval($cpuRow[9]);
			$used = intval($cpuRow[1]) + intval($cpuRow[2]) + intval($cpuRow[3]) + intval($cpuRow[5]) + intval($cpuRow[6]) + intval($cpuRow[7]) + intval($cpuRow[8]) + intval($cpuRow[9]);
			if(trim($cpuRow[0]) == "cpu"){
				continue;
// 				$total = intval($cpuRow[2]) + intval($cpuRow[3]) + intval($cpuRow[4]) + intval($cpuRow[5]) + intval($cpuRow[6]) + intval($cpuRow[7]) + intval($cpuRow[8]) + intval($cpuRow[9]) + intval($cpuRow[10]);
// 				$used = intval($cpuRow[2]) + intval($cpuRow[3]) + intval($cpuRow[4]) + intval($cpuRow[6]) + intval($cpuRow[7]) + intval($cpuRow[8]) + intval($cpuRow[9]) + intval($cpuRow[10]);
			}
			
			$info[] = array(
				"name" => $cpuRow[0],
				"total" => $total,
				"used" => $used,
			);
		}
		return $info;
	}
	
}
    
?>