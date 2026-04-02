<?php
/*******************************************
 ** 系统监控处理类
 **
 ** @author      liushuai@vinchin.com 
 ** @date         2022-7-26 16:29:00
 ** @version      1.0.0
 ** @copyright    Copyright 2022 vinchin.com
 ********************************************/
class SystemMonitorHandler extends OPHandler{
    /**
     * 初始化数据
     * @param unknown $params
     * node_uuid
		time_range
		range_start_time
		range_end_time
		cpu_alarm
		ram_alarm 
		root_alarm
     */
    public function initDataFunc($params){
        //获取节点信息
        $node_uuid = $params['node_uuid'];
        //获取时间类型
        $time_range = $params['time_range'];
        //获取时间范围开始时间
        $range_start_time = $params['range_start_time'];
        //获取时间范围结束时间
        $range_end_time = $params['range_end_time'];
        //获取CPU告警阈值
        $cpu_alarm = $params['cpu_alarm'];
        //获取内存告警阈值
        $ram_alarm = $params['ram_alarm'];
        //获取根分区使用率告警阈值
        $root_alarm = $params['root_alarm'];
        //初始化数据
        $info = array(
            'cpuMsg' => array(
                //每条线的名称
                'name' => array(),
                //x轴时间
                'x_time' => array(),
                //y轴百分比
                'y_percent' => array(),
                //告警阈值
                'alarm_val' => $cpu_alarm,
            ),
            'ramMsg' => array(
                //内存名字
                'name' => array(Xphp::$_lang['UI_SYSTEM_MONITOR_RAM_PERCENTAGE']),
                //x轴时间
                'x_time' => array(),
                //y轴百分比
                'y_percent' => array(),
                //告警阈值
                'alarm_val' => $ram_alarm,
            ),
            'loadMsg' => array(
                //每条线的名称
                'name' => array('Load 1min','Load 5min','Load 15min'),
                //x轴时间
                'x_time' => array(),
                //y轴百分比
                'y_val' => array(
                    'Load 1min' => array(),
                    'Load 5min' => array(),
                    'Load 15min' => array(),
                ),
            ),
            'netWorkMsg' => array(
                'name' => array(),
                'x_time' => array(),
                'y_val' => array(),
            ),
            'bpsMsg' => array(
                'name' => array(),
                'x_time' => array(),
                'y_val' => array(),
            ),
            'iopsMsg' => array(
                'name' => array(),
                'x_time' => array(),
                'y_val' => array(),
            ),
        );
        
        //根据是否有时间类型来判断是否页面实时刷新 获取数据
        if(!empty($time_range)){
            //静态数据
            $result = $this->getDataOfSilence($params);
        }else{
            //动态刷新
            $result = $this->getDataOfMove($params);
        }
        
        //名字只存一次
        $cpu_name_flag = false;
        $network_name_flag = false;
        $bps_name_flag = false;
        $iops_name_flag = false;
        
        foreach ($result as $each){
            //获取CPU,BPS,IOPS,NETWORK详情
            $details = json_decode($each['details'],true);
            //-------------处理CPU相关数据-------
//             $info['cpuMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['cpuMsg']['x_time'][] = date("m-d H:i:s", strtotime($each['monitor_time']));
            
            //得到CPU详情
            $cpuDetails = $details['cpuMsg'];
            foreach ($cpuDetails as $cpuEach){
                $name_cpu = $cpuEach['name'].Xphp::$_lang['UI_SYSTEM_MONITOR_PERCENTAGE_SYSTEM_CHART'];
                if(!$cpu_name_flag){
                    $info['cpuMsg']['name'][] = $name_cpu;
                }
                $info['cpuMsg']['y_percent'][$name_cpu][] = $cpuEach['usedPercent'];
            }
            $cpu_name_flag = true;
            //-------------处理内存相关数据-------
//             $info['ramMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['ramMsg']['x_time'][] = date("m-d H:i:s", strtotime($each['monitor_time']));
            $info['ramMsg']['y_percent'][] = $each['ram_percentage'];
            //-------------处理系统负载相关数据-------
//             $info['loadMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['loadMsg']['x_time'][] = date("m-d H:i:s", strtotime($each['monitor_time']));
            $info['loadMsg']['y_val']['Load 1min'][] = $each['system_load_1'];
            $info['loadMsg']['y_val']['Load 5min'][] = $each['system_load_5'];
            $info['loadMsg']['y_val']['Load 15min'][] = $each['system_load_15'];
            //-------------处理网络流量相关数据-------
//             $info['netWorkMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['netWorkMsg']['x_time'][] = date("m-d H:i:s", strtotime($each['monitor_time']));
            //得到CPU详情
            $networkDetails = $details['networkMsg'];
            foreach ($networkDetails as $networkEach){
                $name_in = $networkEach['name']." in";
                $name_out = $networkEach['name']." out";
                if(!$network_name_flag){
                    $info['netWorkMsg']['name'][] = $name_in;
                    $info['netWorkMsg']['name'][] = $name_out;
                }
                $info['netWorkMsg']['y_val'][$name_in][] = $networkEach['receive_avg'];
                $info['netWorkMsg']['y_val'][$name_out][] = $networkEach['transmit_avg'];
            }
            $network_name_flag = true;
            //-------------处理BPS相关数据-------
//             $info['bpsMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['bpsMsg']['x_time'][] = date("m-d H:i:s", strtotime($each['monitor_time']));
            //得到BPS详情
            $bpsDetails = $details['bpsMsg'];
            foreach ($bpsDetails as $bpsEach){
                //判断是否以dm开头的存储 如果是则跳过
                $namebps = $bpsEach['name'];
                if(preg_match("/^dm*/", $namebps)){
                    continue;
                };
                $name_write = $bpsEach['name']." write";
                $name_read = $bpsEach['name']." read";
                if(!$bps_name_flag){
                    $info['bpsMsg']['name'][] = $name_write;
                    $info['bpsMsg']['name'][] = $name_read;
                }
                $info['bpsMsg']['y_val'][$name_write][] = $bpsEach['write_avg'];
                $info['bpsMsg']['y_val'][$name_read][] = $bpsEach['read_avg'];
            }
            $bps_name_flag = true;
            //-------------处理IOPS相关数据-------
//             $info['iopsMsg']['x_time'][] = $each['monitor_time'];
            //去掉年份
            $info['iopsMsg']['x_time'][] = date("m-d H:i:s", strtotime($each['monitor_time']));
            //得到BPS详情
            $iopsDetails = $details['iopsMsg'];
            foreach ($iopsDetails as $iopsEach){
                //判断是否以dm开头的存储 如果是则跳过
                $nameiops = $iopsEach['name'];
                if(preg_match("/^dm*/", $nameiops)){
                    continue;
                };
                $name_write = $iopsEach['name']." write";
                $name_read = $iopsEach['name']." read";
                if(!$iops_name_flag){
                    $info['iopsMsg']['name'][] = $name_write;
                    $info['iopsMsg']['name'][] = $name_read;
                }
                $info['iopsMsg']['y_val'][$name_write][] = $iopsEach['write_avg'];
                $info['iopsMsg']['y_val'][$name_read][] = $iopsEach['read_avg'];
            }
            $iops_name_flag = true;
            
        }
        $info = $this->checkData(reset($result), end($result), $info);
        return json_encode($info);
    }
    
    
    /**
     * 验证返回的数据的正确性 一般只有少数情况下需要对数据进行处理
     * @param unknown $startInfo 取的所有数据的第一个元素
     * @param unknown $endInfo 取的所有数据的最后一个元素
     * @param unknown $info 最后组装成的数据
     */
    private function checkData($startInfo,$endInfo,$info){
        $startInfo = json_decode($startInfo['details'],true);
        $endInfo = json_decode($endInfo['details'],true);
        $networkFlag = false;
        $bpsFlag = false;
        $iopsFlag = false;
        //---------判断各自数组个数是否与y轴个数相同------
        //** 网络流量 **
        //判断网络流量
        $netWorkMsg = $info['netWorkMsg'];
        //获取x轴长度
        $xNetWorkCount = count($netWorkMsg['x_time']);
        //获取y轴长度
        foreach ($netWorkMsg['y_val'] as $each){
            if($xNetWorkCount != count($each)){
                $networkFlag = true;
            }
        }
        //** 读写bps **
        //bps
        $bpsMsg = $info['bpsMsg'];
        //获取x轴长度
        $xBpsCount = count($bpsMsg['x_time']);
        //获取y轴长度
        foreach ($bpsMsg['y_val'] as $each){
            if($xBpsCount != count($each)){
                $bpsFlag = true;
            }
        }
        //** 读写iops **
        //iops
        $iopsMsg = $info['iopsMsg'];
        //获取x轴长度
        $xIopsCount = count($iopsMsg['x_time']);
        //获取y轴长度
        foreach ($iopsMsg['y_val'] as $each){
            if($xIopsCount != count($each)){
                $iopsFlag = true;
            }
        }
        
        //判断是否一致
        if(!$networkFlag && !$bpsFlag && !$iopsFlag){
//             var_dump("1");
            return $info;
        }
        //如果数据长度不一致 则判断是在前面加0 还是后面加0
        if($networkFlag){
            //开始数目
            $networkStart = $startInfo['networkMsg'];
            $countStartNetwork = count($networkStart) * 2;
            //最后一个数目
            $networkEnd = $endInfo['networkMsg'];
            $countEndNetwork = count($networkEnd) * 2;
            //如果开始大于结尾的则在末尾填0
            if($countStartNetwork > $countEndNetwork){
                foreach ($netWorkMsg['y_val'] as $key=>$value){
                    if($xNetWorkCount != count($value)){
                        $info['netWorkMsg']['y_val'][$key] = array_pad($value,$xNetWorkCount,0);
                        if(!in_array($key, $info['netWorkMsg']['name'])){
                            $info['netWorkMsg']['name'][] = $key;
                        }
                    }
                }
            }else if($countStartNetwork < $countEndNetwork){ //在前面+0
                foreach ($netWorkMsg['y_val'] as $key=>$value){
                    if($xNetWorkCount != count($value)){
                        $info['netWorkMsg']['y_val'][$key] = array_pad($value,$xNetWorkCount*-1,0);
                        if(!in_array($key, $info['netWorkMsg']['name'])){
                            $info['netWorkMsg']['name'][] = $key;
                        }
                    }
                }
            }
        }
        //---
        if($bpsFlag){
            //开始数目
            $bpsStart = $startInfo['bpsMsg'];
            $countStartBps = count($bpsStart) * 2;
            //最后一个数目
            $bpsEnd = $endInfo['bpsMsg'];
            $countEndBps = count($bpsEnd) * 2;
            //如果开始大于结尾的则在末尾填0
            if($countStartBps > $countEndBps){
                foreach ($bpsMsg['y_val'] as $key=>$value){
                    if($xBpsCount != count($value)){
                        $info['bpsMsg']['y_val'][$key] = array_pad($value,$xBpsCount,0);
                        if(!in_array($key, $info['bpsMsg']['name'])){
                            $info['bpsMsg']['name'][] = $key;
                        }
                    }
                }
            }else if($countStartBps < $countEndBps){ //在前面+0
                foreach ($bpsMsg['y_val'] as $key=>$value){
                    if($xBpsCount != count($value)){
                        $info['bpsMsg']['y_val'][$key] = array_pad($value,$xBpsCount*-1,0);
                        if(!in_array($key, $info['bpsMsg']['name'])){
                            $info['bpsMsg']['name'][] = $key;
                        }
                    }
                }
            }
        }
        //---
        if($iopsFlag){
            //开始数目
            $iopsStart = $startInfo['iopsMsg'];
            $countStartIops = count($iopsStart) * 2;
            //最后一个数目
            $iopsEnd = $endInfo['iopsMsg'];
            $countEndIops = count($iopsEnd) * 2;
            //如果开始大于结尾的则在末尾填0
            if($countStartIops > $countEndIops){
                foreach ($iopsMsg['y_val'] as $key=>$value){
                    if($xIopsCount != count($value)){
                        $info['iopsMsg']['y_val'][$key] = array_pad($value,$xIopsCount,0);
                        if(!in_array($key, $info['iopsMsg']['name'])){
                            $info['iopsMsg']['name'][] = $key;
                        }
                    }
                }
            }else if($countStartIops < $countEndIops){ //在前面+0
                foreach ($iopsMsg['y_val'] as $key=>$value){
                    if($xIopsCount != count($value)){
                        $info['iopsMsg']['y_val'][$key] = array_pad($value,$xIopsCount*-1,0);
                        if(!in_array($key, $info['iopsMsg']['name'])){
                            $info['iopsMsg']['name'][] = $key;
                        }
                    }
                }
            }
        }
        return $info;
    }
    
    
    
    
        
    
    /**
     * 动态刷新
     * 只取实时监控的表格来动态刷新数据
     */
    public function getDataOfMove($params){
        //获取节点信息
        $node_uuid = $params['node_uuid'];
        $sql = "select monitor_time, cpu_percentage, ram_percentage, system_load_1, system_load_5, system_load_15, details
                from bd_system_monitor";
        //获取时间固定为最近半小时的量 每张表理论上平均900条
        $date_start= date ("Y-m-d H:i:s",strtotime ( "-10 minute" ));;
        $date_end= date("Y-m-d H:i:s");
        $sql .= " where monitor_time between ? and ? and node_uuid = ? order by monitor_time asc";
        $result = $this->dbSelect($sql,array($date_start,$date_end,$node_uuid));
        return $result;
    }
    
    
    /**
     * 静态数据
     * 获取静态数据  根据时间范围不同查询不同的表
     */
    public function getDataOfSilence($params){
        $node_uuid = $params['node_uuid'];
        $start_time = $params['range_start_time'];
        $end_time = $params['range_end_time'];
        $tableName = $this->getTableFromTime($start_time,$end_time);
        $sql = "select monitor_time, cpu_percentage, ram_percentage,system_load_1, system_load_5, system_load_15, details
                from ".$tableName;
        $sql .= " where monitor_time between ? and ? and node_uuid = ? order by monitor_time asc";
        $result = $this->dbSelect($sql,array($start_time,$end_time,$node_uuid));
        return $result;
    }
    
    /**
     * 根据时间间隔查询哪张表
     * 1小时-6小时	秒表	240-1440
       6小时-4天	分表	84-1152
       4天-1个月	时表	120-472
     * @param unknown $start_time
     * @param unknown $end_time
     */
    public function getTableFromTime($start_time,$end_time){
        //计算时间差(秒)
        $res = intval(strtotime($end_time)-strtotime($start_time));
        $tableName = "";
        if($res<21600){
            $tableName = "bd_system_monitor_s";
        }elseif ($res>=21600 && $res<345600){
            $tableName = "bd_system_monitor_m";
        }else{
            $tableName = "bd_system_monitor_h";
        }
        return $tableName;
    }
    
    
    
    
    /**
     * 得到所有uuid
     */
    public function getNodeUUid(){
        $sql = "select ip, node_uuid, node_type from bd_node order by node_type asc";
        $result = $this->dbSelect($sql);
        $info = array();
        $nodehandler = Xphp::instance('NodeHandler');
        foreach ($result as $each){
            $name = "";
            if($each['node_type'] == 1){
                $name = Xphp::$_lang['UI_PALTFORM_MASTER_NODE']."(".$each['ip'].")";
            }else{
                $name = Xphp::$_lang['UI_PALTFORM_CHILD_NODE']."(".$each['ip'].")";
            }
            //得到节点状态
            $nodeInfo = $nodehandler->getNodeAllStatus($each['node_uuid']);
            //如果节点状态是false  则不显示主节点
            if(!$nodeInfo['flag']){
                continue;
            }
            $info[] = array(
                'ip'=>  $each['ip'],
                'name' => $name,
                'node_uuid' => $each['node_uuid'],
            );
        }
        return json_encode($info);
        
        
    }
    
    
    /**
     * 得到设置的告警规则值
     */
    public function getAlarmVal(){
        $info = array(
            'enable_flag' => $this->intToBool(2),
            'item_period' => 5,
            'silence_time' => 3,
            'cpu_val' => "",
            'ram_val' => "",
            'root_val' => "",
        );
        $sql = "select settings_content from bd_system_settings where settings_type = ?";
        $result = $this->dbSelect($sql,array(Xphp::$_config['SETTINGS_CONF']['SYSTEM_MONITOR_PROGRESS']));
        //如果为空值 则表示没有设置告警信息
        if(empty($result)){
            return json_encode($info);
        }
        //得到设置的值
        $settings_content = json_decode($result[0]['settings_content'],true);
        $info = array(
            'enable_flag' => $this->intToBool($settings_content['enable_flag']),
            'item_period' => $settings_content['item_period'],
            'silence_time' => $settings_content['silence_time'],
            'cpu_val' => $settings_content['config']['cpu']['value'],
            'ram_val' => $settings_content['config']['ram']['value'],
            'root_val' => $settings_content['config']['root']['value'],
        );
        return json_encode($info);
    }
    
    /**
     * 将int类型转换为布尔类型
     * 1为true,2为false
     */
    public function intToBool($intVal){
        $intVal = intval($intVal);
        $boolVal = false;
        if(empty($intVal)){
            return $boolVal;
        }else{
            if($intVal == 1){
                $boolVal = true;
                return $boolVal;
            }else{
                return $boolVal;
                }
        }
    }
    
    
    /**
     * 设置告警阈值
     */
    public function setAlarmVal($params){
        $enable_flag = $params['alarm_flag'];
        $item_period = $params['item_period'];
        $silence_time = $params['silence_time'];
        $cpu_val = $params['cpu_alarm'];
        $ram_val = $params['ram_alarm'];
        $root_val = $params['root_alarm'];
        if($enable_flag){
            $enable_flag = 1;
        }else{
            $enable_flag = 2;
        };
        
        //开始组装数据
        $data = array(
            'enable_flag' => $enable_flag,
            'item_period' => $item_period,
            'silence_time' => $silence_time,
            'config' => array(
                'cpu' => array('value'=> $cpu_val),
                'ram' => array('value'=> $ram_val),
                'root' => array('value'=> $root_val),
            ),
        );
        
        $settings_content = json_encode($data);
        $modify_time = date('Y-m-d H:i:s');
        $user_uuid = Xphp::$_user['useruuid'];
        $settings_type = Xphp::$_config['SETTINGS_CONF']['SYSTEM_MONITOR_PROGRESS'];
        //先删除相关设置再统一添加
        $sqldelete = "delete from bd_system_settings where settings_type = ?";
        $resultDelete = $this->dbExec($sqldelete,array($settings_type));
        //删除了后再添加
        $sql = "insert into bd_system_settings(settings_type,settings_content,modify_time,user_uuid) values(?,?,?,?)";
        $result = $this->dbExec($sql,array($settings_type,$settings_content,$modify_time,$user_uuid));
        if($result && $resultDelete){
            $info = array(
                'flag' => true,
                'enable_flag' => $this->intToBool($enable_flag),
                'item_period' => $item_period,
                'silence_time' => $silence_time,
                'cpu_val' => $cpu_val,
                'ram_val' => $ram_val,
                'root_val' => $root_val,
            );
            return json_encode($info);
        }else{
            $info = array(
                'flag' => false,
            );
            return json_encode($info);
        }
    }
    
    
    
    /**
     * 得到指定节点的机器的相关数据
     * @param unknown $params  节点信息
     * @return array
     */
    public function getBasicInfo($params){
        //得到节点
        $node_uuid = $params['node_uuid'];
        //获取部分基础信息
        $info = $this->getOSBasicInfo($node_uuid);
        //获取磁盘信息
        $diskInfo = $this->getDiskInfo($node_uuid);
        $netInfo = $this->getNetworkInfo($node_uuid);
        $HBAInfo = $this->getHBAInfo($node_uuid);
        $AIOInfo = $this->getAIOInfo();
        
        $info['disk_info'] = $diskInfo;
        $info['network_info'] = $netInfo;
        $info['HBA_info'] = $HBAInfo;
        $info['AIO_version'] = $AIOInfo;
        return json_encode($info);
        
    }
    
    /**
     * 获得一部分系统信息的值
     */
    public function getOSBasicInfo($node_uuid){
        //初始化值
        $info = array(
            //内核名称
            'kernel_name' => "--",
            //网络节点主机名
            'nodename' => "--",
            //内核发行编号
            'kernel_release' => "--",
            //内核版本
            'kernel_version' => "--",
            //硬件名称
            'machine' => "--",
            //处理器类型
            'processor' => "--",
            //硬件平台
            'hardware_platform' => "--",
            //操作系统
            'operating_system' => "--",
            //cpu名称型号主频
            'cpu_name' => "--",
            //CPU个数
            'cpu_count' => "--",
            //单CPU物理核数
            'cpu_cores' => "--",
            //总逻辑核数
            'cpu_processor' => "--",
            //存储适配器IQN
            'storage_adapter_IQN' => "--",
            //内存总量
            'menTotal' => "--",
            //根分区总大小
            'root_total' => "--",
            //已使用根分区
            'root_used' => "--",
            //根分区使用率
            'root_percentage' => "--",
        );
        
        
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        //内核名称
        $cardcmd = "export LC_ALL=en_US.UTF-8;uname -s;";
        //网络节点主机名
        $cardcmd .= "uname -n;";
        //内核发行编号
        $cardcmd .= "uname -r;";
        //内核内核版本
        $cardcmd .= "uname -v;";
        //硬件名称
        $cardcmd .= "uname -m;";
        //处理器类型
        $cardcmd .= "uname -p;";
        //硬件平台
        $cardcmd .= "uname -i;";
        //操作系统
        $cardcmd .= "test -f /etc/centos-release && cat /etc/centos-release ||cat /etc/os-release |awk -F'=' '/PRETTY_NAME=/ {print $2}'|tr -d '\"' ;";


        //获取CPU名称型号主频
        $cardcmd .= "lscpu | awk '/^Model name:/ {split(\$0,a,\":\"); print a[2]; exit}';";
        //物理cpu个数
        $cardcmd .= "lscpu | grep '^Socket(s)'| awk '{print $2}';";
        //单cpu物理核数
        $cardcmd .= "lscpu | grep '^Core(s) per socket' | awk '{print $4}';";
        //总逻辑核数
        $cardcmd .= "lscpu | grep '^CPU(s)'| awk '{print $2}';";
        //存储适配器IQN号
        $cardcmd .= "cat /etc/iscsi/initiatorname.iscsi | awk 'NR==1';";
        //内存总量
        $cardcmd .= "grep MemTotal /proc/meminfo | awk {'print $2'};";
        //根分区总量
        $cardcmd .= "timeout 2s df -BG | grep -w '/$' | awk {'print $2'};";
        //根分区已使用
        $cardcmd .= "timeout 2s df -BG | grep -w '/$' | awk {'print $3'};";
        //根分区使用率
        $cardcmd .= "timeout 2s df -BG | grep -w '/$' | awk {'print $5'};";
        $msg = array(
            "command" => $cardcmd
        );
        $mbResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true, false);
        $utils = Xphp::instance('Utils');
        if($mbResult['result']){
            $BasicData = explode("\n", trim($mbResult['msg']['detail']));
            //处理IQN号
            $iqnStr = $this->dealStrToNull($BasicData[12]);
            if($iqnStr != "--"){
                $iqnList = explode("=",$BasicData[12]);
                $iqnStr = $iqnList[1];
            }
            $info = array(
                //内核名称
                'kernel_name' => $this->dealStrToNull($BasicData[0]),
                //网络节点主机名
                'nodename' => $this->dealStrToNull($BasicData[1]),
                //内核发行编号
                'kernel_release' => $this->dealStrToNull($BasicData[2]),
                //内核内核版本
                'kernel_version' => $this->dealStrToNull($BasicData[3]),
                //硬件名称
                'machine' => $this->dealStrToNull($BasicData[4]),
                //处理器类型
                'processor' => $this->dealStrToNull($BasicData[5]),
                //硬件平台
                'hardware_platform' => $this->dealStrToNull($BasicData[6]),
                //操作系统
                'operating_system' => $this->dealStrToNull($BasicData[7]),
                //cpu名称型号主频
                'cpu_name' => $this->dealStrToNull(trim($BasicData[8])),
                //CPU个数
                'cpu_count' => $this->dealStrToNull($BasicData[9]),
                //单CPU物理核数
                'cpu_cores' => $this->dealStrToNull($BasicData[10]),
                //总逻辑核数
                'cpu_processor' => $this->dealStrToNull($BasicData[11]),
                //存储适配器IQN
                'storage_adapter_IQN' => $iqnStr,
                //内存总量
                'menTotal' => $utils->calSize($BasicData[13]*1024,true),
                //根分区总大小
                'root_total' => $this->dealStrToNull($BasicData[14])."B",
                //已使用根分区
                'root_used' => $this->dealStrToNull($BasicData[15])."B",
                //根分区使用率
                'root_percentage' => $this->dealStrToNull($BasicData[16]),
            );
            
            
        }
        return $info;
    }
    
    
    
    
    
    /**
     * 获取linux磁盘信息
     * @param unknown $node_uuid
     */
    public function getDiskInfo($node_uuid){
        //初始化值
        $info = array(
        );
        //-------------------获取设备名称 类型 版本 大小-----------
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        //设备名称
        $cardcmdpart1 = "lsblk -d --output NAME,TYPE,SIZE | grep -v '^.*nbd';";
        $msgpart1 = array(
            "command" => $cardcmdpart1
        );
        $mbResultpart1 = $this->mbNodeMsg($opName, $node_uuid, json_encode($msgpart1), true, false);
        if($mbResultpart1['result']){
            //结果转换成数组
            $BasicDatapart1 = explode("\n", trim($mbResultpart1['msg']['detail']));
            for($i=1;$i<count($BasicDatapart1);$i++){
                //获得每一行
                $thisStr = $BasicDatapart1[$i];
                //每一行以空格拆分成数组
                $thislist = $this->stringToList($thisStr);
                $info[$i]['name'] = $thislist[0];
                $info[$i]['type'] = $thislist[1];
                $info[$i]['size'] = $thislist[2]."B";
            }
        }
        //--------------因为REV可能不能用空格分开 单独取值-------
        //设备 厂商
        $cardcmdpart4 = "lsblk -d --output REV;";
        $msgpart4 = array(
            "command" => $cardcmdpart4
        );
        $mbResultpart4 = $this->mbNodeMsg($opName, $node_uuid, json_encode($msgpart4), true, false);
        if($mbResultpart4['result']){
            $BasicDatapart4 = explode("\n", trim($mbResultpart4['msg']['detail']));
            for($i=1;$i<count($BasicDatapart1);$i++){
                if(empty($BasicDatapart4[$i])){
                    $info[$i]['rev'] = "--";
                }else{
                    $info[$i]['rev'] = $BasicDatapart4[$i];
                }
            }
        }
        //--------------因为设备厂商和型号可能不能用空格分开 单独取值-------
        //设备 厂商
        $cardcmdpart2 = "lsblk -d --output VENDOR;";
        $msgpart2 = array(
            "command" => $cardcmdpart2
        );
        $mbResultpart2 = $this->mbNodeMsg($opName, $node_uuid, json_encode($msgpart2), true, false);
        if($mbResultpart2['result']){
            $BasicDatapart2 = explode("\n", trim($mbResultpart2['msg']['detail']));
            for($i=1;$i<count($BasicDatapart1);$i++){
                if(empty($BasicDatapart2[$i])){
                    $info[$i]['vendor'] = "--";
                }else{
                    $info[$i]['vendor'] = $BasicDatapart2[$i];
                }
            }
        }
        
        //--------------因为设备厂商和型号可能不能用空格分开 单独取值-------
        //设备 型号
        $cardcmdpart3 = "lsblk -d --output MODEL;";
        $msgpart3 = array(
            "command" => $cardcmdpart3
        );
        $mbResultpart3 = $this->mbNodeMsg($opName, $node_uuid, json_encode($msgpart3), true, false);
        if($mbResultpart3['result']){
            $BasicDatapart3 = explode("\n", trim($mbResultpart3['msg']['detail']));
            for($i=1;$i<count($BasicDatapart1);$i++){
                if(empty($BasicDatapart3[$i])){
                    $info[$i]['model'] = "--";
                }else{
                    $info[$i]['model'] = $BasicDatapart3[$i];
                }
            }
        }
        return $info;
    }
  
    
    
    /**
     * 得到网卡信息
     * @param unknown $node_uuid
     */
    public function getNetworkInfo($node_uuid){
        //初始化值
        $info = array();
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        //查询所有网卡的名字
        $cardcmd = "ls -l /sys/class/net | grep -v 'virtual';";
        $msg = array(
            "command" => $cardcmd
        );
        $netCompanyList = $this->getNetworkCompanyMsg($node_uuid);
        
        $mbResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true, false);
        if($mbResult['result']){
            $nameList = array();
            $BasicData = explode("\n", trim($mbResult['msg']['detail']));
            foreach ($BasicData as $each){
                $listName = $this->stringToList($each);
                $thisName = "";
                $thisPath = "";
                if(!empty($listName[8])){
                    if($listName[8] == "lo"){ //lo的跳过
                        continue;
                    }
                    $thisName = $listName[8];
                }else{
                    continue; //如果名称为空则跳过
                }
                if(!empty($listName[10])){
                    $thisPath = $listName[10];
                }else{
                    continue; //如果路径为空则跳过
                }
                
                $nameList[] = array(
                    'name' => $thisName,
                    'path' => $thisPath,
                );
            }
            //得到数组后 开始循环数组取值
            $x = 0;
            $speedResult = "";
            $otherResult = "";
            foreach ($nameList as $eachName){
                if($eachName['name'] == "lo"){
                    continue;
                }
                //获取厂家信息
                $thisCompany = "";
                if(!empty($netCompanyList)){
                    foreach ($netCompanyList as $eachCompany){
                        $listEachComList = $this->stringToList($eachCompany);
                        //获取地址
                        $pathAdress = $listEachComList[0];
                        //得到地址后再匹配 通过当前地址确定其名字
                        if(strpos($eachName['path'],$pathAdress)){
                            //如果找到
                            $thisCompany = $eachCompany;
                        }
                    }
                }
                if(!empty($thisCompany)){
                    $thisCompanyList = explode("Ethernet controller:", trim($thisCompany));
                    $thisCompany = trim($thisCompanyList[1]);
                }
                //网卡地址
                $othercmd = "cat /sys/class/net/".$eachName['name']."/address;";
                //网卡速度
                $speedcmd = "cat /sys/class/net/".$eachName['name']."/speed;";
                //网卡状态
                $othercmd .= "cat /sys/class/net/".$eachName['name']."/operstate;";
                $othermsg = array(
                    "command" => $othercmd
                );
                $speedmsg = array(
                    "command" => $speedcmd
                );
                
                $otherResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($othermsg), true, false);
                $speedResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($speedmsg), true, false);
                if($speedResult['result']){
                    $speedData = explode("\n", trim($speedResult['msg']['detail']));
                    $speed = "--";
                    if(is_numeric($speedData[0])){
                        $intSpeedData = intval($speedData[0]);
                        if($intSpeedData > 0){
                            $speed = $speedData[0]."Mbps";
                        }else{
                            $speed = "--";
                        }
                    }
                    $info[$x]= array(
                        'speed' => $speed,
                    );
                }else{
                    $info[$x]= array(
                        'speed' => "--",
                    );
                }
                
                
                if($otherResult['result']){
                    $otherData = explode("\n", trim($otherResult['msg']['detail']));
                    //检测速度是否为字符串
                   
                    if($otherData[2] == "up" || $otherData[2] == "Online"){
                        $operstate = $otherData[2];
                    }
                    $info[$x]['name'] = $eachName['name'];
                    $info[$x]['address'] = $otherData[0];
                    $info[$x]['operstate'] = $otherData[1];
                    $info[$x]['vender'] = $thisCompany;
                }
                $x++;
            }
        }
        return $info;
    }




    
    /**
     * 得到网络厂商信息
     * @param unknown $node_uuid
     */
    public function getNetworkCompanyMsg($node_uuid){
        //初始化值
        $info = array();
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        //查询所有网卡的名字
        $cardcmd = "lspci |grep -i Ethernet";
        $msg = array(
            "command" => $cardcmd
        );
        $mbResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true, false);
        if($mbResult['result']){
            $nameList = array();
            $BasicData = explode("\n", trim($mbResult['msg']['detail']));
            return $BasicData;
        }
    }
    
    /**
     * 得到HBA厂商信息
     * @param unknown $node_uuid
     */
    public function getHBACompanyMsg($node_uuid){
        //初始化值
        $info = array();
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        //查询所有网卡的名字
        $cardcmd = "lspci |grep -i  fibre";
        $msg = array(
            "command" => $cardcmd
        );
        $mbResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true, false);
        if($mbResult['result']){
            $nameList = array();
            $BasicData = explode("\n", trim($mbResult['msg']['detail']));
            return $BasicData;
        }
    }
    
    
    
    
    
    /**
     * 获取HBA卡信息
     * @param unknown $node_uuid
     */
    public function getHBAInfo($node_uuid){
        
        //初始化值
        $info = array();
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        //查询所有网卡的名字
        $cardcmd = "ls -l /sys/class/fc_host";
        $msg = array(
            "command" => $cardcmd
        );
        
        $HBACompanyList = $this->getHBACompanyMsg($node_uuid);
        if(empty($HBACompanyList)){
            return $info;
        }
        $mbResult = $this->mbNodeMsg($opName, $node_uuid, json_encode($msg), true, false);
        if($mbResult['result']){
            $nameList = array();
            $BasicData = explode("\n", trim($mbResult['msg']['detail']));
            $thisStr = $BasicData[0];
            //通过判断查询结果来判断是否有HBA卡信息
            if(strpos($thisStr,"No such file or directory")){
                //没有HBA卡信息返回空数组
                return $info;
            }
            foreach ($BasicData as $each){
                $listName = $this->stringToList($each);
                $thisName = "";
                $thisPath = "";
                if(!empty($listName[8])){
                    $thisName = $listName[8];
                }else{
                    continue; //如果名称为空则跳过
                }
                if(!empty($listName[10])){
                    $thisPath = $listName[10];
                }else{
                    continue; //路径没有就跳过
                }
                
                $nameList[] = array(
                    'name' => $thisName,
                    'path' => $thisPath,
                );
            }
            foreach ($nameList as $eachName){
                //获取厂家信息
                $thisCompany = "";
                if(!empty($HBACompanyList)){
                    foreach ($HBACompanyList as $eachCompany){
                        $listEachComList = $this->stringToList($eachCompany);
                        //获取地址
                        $pathAdress = $listEachComList[0];
                        //得到地址后再匹配 通过当前地址确定其名字
                        if(strpos($eachName['path'],$pathAdress)){
                            //如果找到
                            $thisCompany = $eachCompany;
                        }
                    }
                }
                if(!empty($thisCompany)){
                    $thisCompanyList = explode("Fibre Channel:", trim($thisCompany));
                    $thisCompany = trim($thisCompanyList[1]);
                }
                //HBA卡型号
                $othercmd1 = "cat /sys/class/scsi_host/".$eachName['name']."/model_name;";
                //HBA卡WWN号
                $othercmd2 = "cat /sys/class/fc_host/".$eachName['name']."/port_name;";
                //HBA卡端口状态
                $othercmd3 = "cat /sys/class/fc_host/".$eachName['name']."/port_state;";
                //HBA卡支持速率
                $othercmd4 = "cat /sys/class/fc_host/".$eachName['name']."/supported_speeds;";
                //HBA卡速率
                $othercmd5 = "cat /sys/class/fc_host/".$eachName['name']."/speed;";
                $othermsg1 = array(
                    "command" => $othercmd1
                );
                $otherResult1 = $this->mbNodeMsg($opName, $node_uuid, json_encode($othermsg1), true, false);
                $othermsg2 = array(
                    "command" => $othercmd2
                );
                $otherResult2 = $this->mbNodeMsg($opName, $node_uuid, json_encode($othermsg2), true, false);
                $othermsg3 = array(
                    "command" => $othercmd3
                );
                $otherResult3 = $this->mbNodeMsg($opName, $node_uuid, json_encode($othermsg3), true, false);
                $othermsg4 = array(
                    "command" => $othercmd4
                );
                $otherResult4 = $this->mbNodeMsg($opName, $node_uuid, json_encode($othermsg4), true, false);
                $othermsg5 = array(
                    "command" => $othercmd5
                );
                $otherResult5 = $this->mbNodeMsg($opName, $node_uuid, json_encode($othermsg5), true, false);
                if($otherResult1['result']){
                    $otherData1 = explode("\n", trim($otherResult1['msg']['detail']));
                    $model_name = $this->dealStrToNull($otherData1[0]);
                }
                if($otherResult2['result']){
                    $otherData2 = explode("\n", trim($otherResult2['msg']['detail']));
                    $port_name = $this->dealStrToNull($otherData2[0]);
                }
                if($otherResult3['result']){
                    $otherData3 = explode("\n", trim($otherResult3['msg']['detail']));
                    $port_state = $this->dealStrToNull($otherData3[0]);
                }
                if($otherResult4['result']){
                    $otherData4 = explode("\n", trim($otherResult4['msg']['detail']));
                    $supported_speeds = $this->dealStrToNull($otherData4[0]);
                }
                if($otherResult5['result']){
                    $otherData5 = explode("\n", trim($otherResult5['msg']['detail']));
                    $speed = $this->dealStrToNull($otherData5[0]);
                }
                $info[]= array(
                    'name' => $eachName['name'],
                    'model_name' => $model_name,
                    'port_name' => $port_name,
                    'port_state' => $port_state,
                    'supported_speeds' => $supported_speeds,
                    'speed' => $speed,
                    'vender' => $thisCompany,
                );
                
                
            }
        }
        return $info;
    }
    
    
    public function getAIOInfo(){
        $info = "";
        $sql = "select extension from bd_license";
        $data = $this->dbSelect($sql);
        //授权模块以及自定义版本信息
        $utils = Xphp::instance('Utils');
        if(json_decode($utils->decrypt($data[0]['extension']), true) == null){
            $info = "";
        }else{
            $extension = json_decode($utils->decrypt($data[0]['extension']), true);
            $info = $extension['softwareNameModel'];
        }
        return $info;
    }
    
    
    
    /**
     * 判断是否有取到值 如果没有取到值则返回--
     */
    public function dealStrToNull($str){
//         return $str;
        $info = "--";
        if(empty($str)){
            return $info;
        }
        if(strpos($str,"No such file or directory")){
            return $info;
        }else{
            return trim($str);
        }
    }
    
    
    
    
    
    
    
    
    
    /**
     * 字符串转换为列表
     */
    public function stringToList($val){
        $string = trim($val);
        if(empty($string)){
            return array();
        }
        //删除多余空格
        $string = preg_replace('/[\s*]+/'," ",$string);
        //拆分为数组
        $list = explode(" ",$string);
        return $list;
    }
    
    
    
    
    
    
    
    
    
}