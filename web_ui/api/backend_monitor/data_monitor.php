<?php
header("Content-Type:text/html;charset=utf-8");  //设置系统的输出字符为utf-8
	defined('RUN_PATH') or define('RUN_PATH', dirname(dirname(__FILE__)) . "/");
	//设置时区,和操作系统一致
	$cmd = "timedatectl |grep Timezone|awk '{print $2}'";
	exec($cmd, $data);
	if(!$data){
		$cmd = "timedatectl |grep Time\\ zone|awk '{print $3}'";
		exec($cmd, $data);
	}
	date_default_timezone_set($data[0]);    		 	 //设置时区（系统本地）
	require_once RUN_PATH.'backend_monitor/DataMonitor.class.php';
	$monitor = new DataMonitor();

	