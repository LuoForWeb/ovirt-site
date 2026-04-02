<?php
/******************************************* 
** 报表管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-04-10 下午15:16:44 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
class ReportHandler extends OPHandler{
	
	/**
	 * 根据存储状态得到每个状态包含多少个存储
	 */
	public function getStoragePie($params){
		$sql = "select node_uuid,status, mount_flag, warning_type, warning_value, total_size, free_size ,storage_type 
		    from bd_storage_resource where lan_free_flag = ?";
		$data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET']));
		 
		$nodeHandler = Xphp::instance('NodeHandler');
		$storageHandler = Xphp::instance('StorageHandler');
		$info = array();
		//初始化每种状态
		$online = 0;	//在线
		$offline = 0;	//离线
		$unmount = 0;	//未挂载
		$warning = 0;	//告警
		foreach ($data as $d){
			$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
			$status = $storageHandler->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
			if($d['warning_type'] == 1){
				$value = round(intval($d['free_size'])/intval($d['total_size']) * 100, 2);
				if(floatval($value) <= floatval($d['warning_value'])){
					$status = Xphp::$_config['STORAGE_STATUS']['WARNING'];
				}
			}else{
			    if($d['storage_type'] ==Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']){
			        if($d['free_size']<=0){
			            $status = Xphp::$_config['STORAGE_STATUS']['WARNING'];
			        }
			    }else{
			        if($d['free_size'] <= intval($d['warning_value'])){
			            $status = Xphp::$_config['STORAGE_STATUS']['WARNING'];
			        }
			    }
			}
			
			//获取到存储的状态，根据状态增加对应状态值的个数
			if($status == Xphp::$_config['STORAGE_STATUS']['ONLINE']){
				$online ++;
			}else if($status == Xphp::$_config['STORAGE_STATUS']['OFFLINE']){
				$offline ++;
			}else if($status == Xphp::$_config['STORAGE_STATUS']['UNMOUNT']){
				$unmount ++;
			}else if ($status == Xphp::$_config['STORAGE_STATUS']['WARNING']){
				$warning ++;
			}
		}
		
		//在线个数
		$info[] = array(
				"value" => $online,
				"des" => $online,
				"name" => Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'],
		);
		
		//离线个数
		$info[] = array(
				"value" => $offline,
				"des" => $offline,
				"name" => Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'],
		);
		 
		//未挂载个数
		$info[] = array(
				"value" => $unmount,
				"des" => $unmount,
				"name" => Xphp::$_lang['WEB_STORAGE_STATUS_UNMOUNT'],
		);
		 
		//告警个数
		$info[] = array(
				"value" => $warning,
				"des" => $warning,
				"name" => Xphp::$_lang['WEB_STORAGE_STATUS_WARNING'],
		);
		 
		return json_encode($info);
	}
	
	
	
	/**
	 * 得到存储统计柱状图
	 */
	public function getStorageBar($params){
		$sql = "select ip, node_uuid, node_nickname, host_name from bd_node order by node_id asc";
		$data = $this->dbSelect($sql,array());
		$info = array();
		$nodeHandler = Xphp::instance('NodeHandler');
		foreach($data as $d){
			$info[] = array(
					'node_name' => $nodeHandler->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']) . "\n(" . $d['ip'] . ")",
					'storage_list' => $this->getStorageList($d['node_uuid'])
			);
		}
		
		foreach ($info as $key => $row) {
			$list[$key]  = $row['storage_list']; //把每个节点存储列表取出来存入list
		}
		array_multisort($list, SORT_DESC, $info); //给存储列表排序
		return json_encode($info);
		 
	}
	
	/**
	 * 得到存储列表
	 * @param string $nodeuuid
	 */
	public function getStorageList($nodeuuid){
		$sql = 'select bn.ip, bsr.storage_nickname, bsr.storage_type, bsr.total_size, bsr.free_size, bsr.warning_value, bsr.warning_type from bd_storage_resource bsr, bd_node bn where bsr.lan_free_flag = ? and bsr.node_uuid = bn.node_uuid and bsr.node_uuid = ? order by bsr.node_uuid desc, free_size desc';
		$data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET'], $nodeuuid));
		$list = array();
		$utils = Xphp::instance('Utils');
		foreach($data as $d){
			$warningFlag = false;
			//超出阈值，告警
			if((intval($d['free_size']) <= $d['warning_value'] && intval($d['warning_type']) == Xphp::$_config['STORAGEWARNINGTYPE']['SIZE']) || (intval($d['free_size']) <= intval($d['warning_value'])* intval($d['total_size']) / 100 && intval($d['warning_type']) == Xphp::$_config['STORAGEWARNINGTYPE']['PERCENT'])){
				$warningFlag = true;
			}
            if (Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'] == $d['storage_type']) {
                $warningFlag = false;
            }
			$used = $d['total_size'] - $d['free_size']; //存储用量
			$list[] = array(
					'storage_name' => $d['storage_nickname'],
					'used_size' => round($used/1024/1024/1024, 2),
					'free_size' => round($d['free_size']/1024/1024/1024, 2),
					'warning' => $warningFlag,
			);
		}
		 
		return $list;
	}
	
	/**
	 * 查询今天是否有插入统计数据
	 */
	public function getStorageMonitor(){
		$sql = "select count(*) as total from bd_storage_monitor where date = ?";
		$result = $this->dbSelect($sql, array(date('Y-m-d')));
		return intval($result[0]['total']);
	}
	
	/**
	 * 插入每天存储监控信息
	 * @param date $storagedate
	 */
	public function addStorageMointor($storagedate){
        //获取查询的存储监控日期
        $backupDate = strtotime($storagedate) - 3600*24;
        $backupDate = date("Y-m-d", $backupDate);
        $sqlBackup = "select  sum(bbt.write_size) as write_size, sum(bbt.total_size) as total_size from bd_backup_timepoint bbt, bd_storage_resource bsr where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ? and bbt.task_type not in (".Xphp::$_config['TASKTYPE']['BACKUP_COPY'].",".Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH'].",".Xphp::$_config['TASKTYPE']['ARCHIVE'].",".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") and bbt.timepoint like '%". $backupDate . "%'";
        $dataBackup = $this->dbSelect($sqlBackup, array(Xphp::$_config['FLAG']['UNSET']));
        $backupTotalSize = intval($dataBackup[0]['total_size']);  //备份数据总大小
        $backupWriteSize = intval($dataBackup[0]['write_size']);  //备份数据写入大小
        $sqlCopy = "select  sum(bbt.write_size) as write_size, sum(bbt.total_size) as total_size from bd_backup_timepoint bbt, bd_storage_resource bsr where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ?  and bbt.task_type in (".Xphp::$_config['TASKTYPE']['BACKUP_COPY'].",".Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH'].") and bbt.timepoint like '%". $backupDate . "%'";
        $dataCopy = $this->dbSelect($sqlCopy, array(Xphp::$_config['FLAG']['UNSET']));
        $copyTotalSize = intval($dataCopy[0]['total_size']);  //备份数据总大小
        $copyWriteSize = intval($dataCopy[0]['write_size']);  //备份数据写入大小
        $sqlArchive = "select sum(bbt.write_size) as write_size, sum(bbt.total_size) as total_size from bd_backup_timepoint bbt, bd_storage_resource bsr where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ?  and bbt.task_type in (".Xphp::$_config['TASKTYPE']['ARCHIVE'].",".Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH'].") and bbt.timepoint like '%". $backupDate . "%'";
        $dataArchive = $this->dbSelect($sqlArchive, array(Xphp::$_config['FLAG']['UNSET']));
        $archiveTotalSize = intval($dataArchive[0]['total_size']);  //备份数据总大小
        $archiveWriteSize = intval($dataArchive[0]['write_size']);  //备份数据写入大小
        $backupNum = 0;
        $successNum = 0;
        $backupVmNum = 0;
        $vmSuccessNum = 0;
        $detail = "";
        $date = date("Y-m-d"); //获取当前日期
        $remark = "";
        $sqlParams = array($date, $backupTotalSize, $backupWriteSize, $copyTotalSize, $copyWriteSize, $archiveTotalSize, $archiveWriteSize, $backupNum, $successNum, $backupVmNum, $vmSuccessNum, $detail, $remark);
        //插入前一天存储统计监控
        $sqlInsert = "insert into bd_storage_monitor (date, backup_total_size, backup_write_size, copy_total_size, copy_write_size, archive_total_size, archive_write_size, backup_num, backup_success_num, vm_num, success_vm_num, details, remarks)
				values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbExec($sqlInsert, $sqlParams);
        return $result;
		 
	}
	
	/**
	 * 获取存储统计详情信息
	 * @param array $vmList
	 */
	public function getStatisticsDetails($vmList){
		$value = array();
		//遍历虚拟机列表
		foreach ($vmList as $vm){
			$key = $vm['dir_path'];
			if(empty($value[$key])){
				$value[$key] = array(
						'vm_name' =>  $vm['vm_name'],
						'vm_path' => $vm['dir_path'],
						'storage_size' => $vm['write_size'],
						'backup_num' => 1
				);
			}else{
				$value[$key]['storage_size'] += $vm['write_size'];
				$value[$key]['vm_size'] += $vm['vm_size'];
				$value[$key]['backup_num'] += 1;
			}
		}
		return json_encode($value);
		 
	}
	
	/**
	 * 获取当天虚拟机备份总次数
	 */
	public function getBackupNum($backupDate){
		$sqlCount = "select count(id) as total from bd_history_task where module_type = ? and task_type = ? and finish_time like '%". $backupDate . "%'";
		$dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['BACKUP']));
		return intval($dataCount[0]['total']);
	}
	
	/**
	 * 获取当天虚拟机备份成功总次数
	 */
	public function getSuccessNum($backupDate){
		$sqlCount = "select count(id) as total from bd_history_task where module_type = ? and task_type = ? and error_code = ? and finish_time like '%". $backupDate . "%'";
		$dataCount = $this->dbSelect($sqlCount, array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['TASKTYPE']['BACKUP'], 0));
		return intval($dataCount[0]['total']);
	}
	
	/**
	 * 获取当天备份虚拟机个数
	 */
	public function getVmNum($backupDate){
		$sqlCount = "select count(distinct vbt.dir_path) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.timepoint like '%". $backupDate . "%'";
		$dataCount = $this->dbSelect($sqlCount, array());
		return intval($dataCount[0]['total']);
	}
	
	/**
	 * 获取当天备份文件代理个数
	 */
	public function getFsNum($backupDate){
	    $sqlCount = "select count(distinct fbt.agent_uuid) as total from fs_backup_timepoint fbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.timepoint like '%". $backupDate . "%'";
	    $dataCount = $this->dbSelect($sqlCount, array());
	    return intval($dataCount[0]['total']);
	}
	
	/**
	 * 获取当天备份数据库代理个数
	 */
	public function getDbNum($backupDate){
	    $sqlCount = "select count(distinct dbt.agent_uuid) as total from db_backup_timepoint dbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.timepoint like '%". $backupDate . "%'";
	    $dataCount = $this->dbSelect($sqlCount, array());
	    return intval($dataCount[0]['total']);
	}
	
	/**
	 * 获取存储近期统计折线图
	 * 
	 */
	public function getStorageLine($params){
		$days = intval($params['days']); //获取天数
		$sql = 'select date_format(timepoint, "%Y-%m-%d") as timepoint_date, sum(write_size) as write_size_total 
                from bd_backup_timepoint where DATE_SUB(CURDATE(),INTERVAL ? DAY) <= timepoint 
                group by date_format(timepoint, "%Y-%m-%d");';
		$sqlParams = array($days);
		$data = $this->dbSelect($sql, $sqlParams);

		$sql2 = 'SELECT date_format(end_timestamp, "%Y-%m-%d") as date, SUM(backup_file_size + log_file_total_size) as cdp_write_size_total
                  FROM cdp_vol_backup_vol_set WHERE DATE_SUB(CURDATE(), INTERVAL ? DAY) <= end_timestamp GROUP BY date';
        $data2 = $this->dbSelect($sql2, $sqlParams);

		$tmp = $info = array();
		$setValueDateArr = array();
		foreach ($data as $d){
            $tmp[$d['timepoint_date']] = round($d['write_size_total']/1024/1024/1024, 2);
		    //将已经设置的日期放入数组
		    $setValueDateArr[] = $d['timepoint_date'];
		}
        foreach ($data2 as $d){
            $cdp_size = round($d['cdp_write_size_total']/1024/1024/1024, 2);
            if (array_key_exists($d['date'], $tmp)) {
                $tmp[$d['date']] += $cdp_size;
            } else {
                $tmp[$d['date']] = $cdp_size;
                //将已经设置的日期放入数组
                $setValueDateArr[] = $d['date'];
            }
        }
        foreach ($tmp as $date => $size) {
            $info[] = array(
                'date' => $date,
                'storage_size' => $size,
            );
        }

		//添加最近x天内没有数据的日期
		for($i=$days; $i>0; $i--){
		    $dayValue = strtotime(date("Y-m-d")) - 3600*24*($i-1);
		    $dayDate = date("Y-m-d", $dayValue);
		    
		    if(!in_array($dayDate, $setValueDateArr)){
		        $info[] = array(
		            'date' => $dayDate,
		            'storage_size' => 0,
		        );
		    }
		}
		
		//重新排序数组
		$utils = Xphp::instance('Utils');
		$info = $utils->arraySort($info, 'date', "asc", 0, -1);
		
		return json_encode($info);
	}
	
	/**
	 * 获取虚拟化类型虚拟机受保护情况列表
	 *
	 */
	public function getVmTypeList($params){
        //租户里
        if(!empty($_SESSION['tenantuuid'])){
            return $this->getTenantVmTypeList();
        }
        $sql = 'select distinct vv.hypervisor_type from vm_vcenter vv, vm_tree vt where vv.vcenter_uuid = vt.vcenter_uuid ';
        $sqlCount = 'select count(distinct vv.hypervisor_type) as total from vm_vcenter vv, vm_tree vt where vv.vcenter_uuid = vt.vcenter_uuid ';

        $sqlParams = array();
        $sqlCountParams = array();

        $sql .= ' order by vv.hypervisor_type asc';
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);

        $utils = Xphp::instance("Utils");
        $records = array();
        $id = 0;

        //获取用户已分配的虚拟机
        $resourceHandler = Xphp::instance('ResourceHandler');
        $vmResource = $resourceHandler->pGetUserResourceVM(Xphp::$_user['useruuid'],"tree_id", "desc", $limit = 0, "all");
        $vmList = array();
        $vcenterList = array();
        //不是全局观察者获取对应用户的任务
        if(!in_array("global_observer", $_SESSION['permission'])){
            foreach ($vmResource['data'] as $vm){
                if(!in_array($vm['vcenter_uuid'], $vcenterList)){
                    $vcenterList[] = $vm['vcenter_uuid'];
                }
                if(!in_array($vm['uuid'], $vmList)){
                    $vmList[] = $vm['uuid'];
                }
            }
        }
        $vmuuids = implode("','", $vmList);
        $vcenteruuids = implode("','", $vcenterList);
        foreach ($data as $d){
            $id++;
            $vmNumList = $this->getVmNumList(intval($d['hypervisor_type']), $vmuuids, $vcenteruuids);
            $totalNum = intval($vmNumList['total_vm_num']);
            //检查当前虚拟化如果没有虚拟机就不展示
            if($totalNum == 0) continue;
            $protectNum = intval($vmNumList['protected_vm_num']);
            if($protectNum > $totalNum){
                $protectNum = $totalNum;
            }
            $records[] = array(
                'num' => $id,
                'hypervisor' => Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])],
                'total_vm_num' => $totalNum,
                'protected_vm_num' => $protectNum,
                'type' => intval($d['hypervisor_type']),
            );
        }


        return  json_encode($records);
	}
	
	
	/**
	 * 获取系统添加虚拟机总个数和保护个数
	 * @param intval $hypervisor
	 */
	public function getVmNumList($hypervisor, $vmuuids = "", $vcenteruuids = "", $userListDes = "", $tenantMangerFlag = false){
		$totalSql = "select count(distinct vt.vcenter_uuid, vt.uuid) as total_vm_num from vm_tree vt, vm_vcenter vv where vt.vcenter_uuid = vv.vcenter_uuid and vt.display_mode = ? and vt.type = ? and vv.hypervisor_type = ? ";
		$sqlParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM'], $hypervisor);
		if(!empty($_SESSION['tenantuuid'])){
		    //租户内
		    $totalSql .= " and vt.uuid in ($vmuuids) and vt.vcenter_uuid in ($vcenteruuids)";
		}else{
		    //租户外
		    if(Xphp::$_user['useruuid'] != "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && !in_array("global_observer", $_SESSION['permission'])){
		        $totalSql .= " and (vv.user_uuid = ? or (vt.uuid in ('".$vmuuids."') and vt.vcenter_uuid in ('".$vcenteruuids."')))";
		        $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid']));
		    }
		}
		$dataTotal = $this->dbSelect($totalSql, $sqlParams);
		$protectedSql = "select count(vml.machine_id) as protected_vm_num from vm_machine_list vml, vm_vcenter vv, bd_task bt where bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = vv.vcenter_uuid and bt.task_type = ? and vv.hypervisor_type = ? ";
		$sqlProtectedParams = array(Xphp::$_config['TASKTYPE']['BACKUP'], $hypervisor);
		if(!empty($_SESSION['tenantuuid'])){
		    //租户内
		    if(!empty($vmuuids) && !empty($vcenteruuids)){
		        $protectedSql .= " and vml.vm_uuid in ($vmuuids) and vml.vcenter_uuid in ($vcenteruuids)";
		    }else{
		        $protectedSql .= " and vml.vm_uuid in ('') and vml.vcenter_uuid in ('')";
		    }
		    
		    if($tenantMangerFlag){
		        $protectedSql .= " and bt.user_uuid in ('".$userListDes."') ";
		    }else{
		        $protectedSql .= " and bt.user_uuid = ? ";
		        $sqlProtectedParams = array_merge($sqlProtectedParams,array(Xphp::$_user['useruuid']));
		    }
		}else{
		    //租户外
		    if(Xphp::$_user['useruuid'] != "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && !in_array("global_observer", $_SESSION['permission'])){
		        $protectedSql .= " and bt.user_uuid = ?";
		        $sqlProtectedParams = array_merge($sqlProtectedParams,array(Xphp::$_user['useruuid']));
		    }
		}
		
		$dataProtect = $this->dbSelect($protectedSql, $sqlProtectedParams);
		$info = array(
			'total_vm_num' => intval($dataTotal[0]['total_vm_num']),
			'protected_vm_num' => intval($dataProtect[0]['protected_vm_num'])
		);
		return $info;
	}
	
	/**
	 * 获取各个虚拟化虚拟机备份情况饼状图
	 *
	 */
	public function getVmTypePie($params){
	    if(!empty($_SESSION['tenantuuid'])){
	        return $this->getTenantVmPie();
	    }
		$sql = "select distinct vv.hypervisor_type from vm_vcenter vv, vm_tree vt where vv.vcenter_uuid = vt.vcenter_uuid ";
		$sqlParams = array();
		$sql .= ' order by vv.hypervisor_type asc';
		$data = $this->dbSelect($sql, $sqlParams);
		$info = array();
		$totalSql = "select count(dir_path) as all_total_vm_num from vm_tree  where type = ? and display_mode =?";
		$dataTotal = $this->dbSelect($totalSql, array(7, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']));
		
		//获取用户已分配的虚拟机
		$resourceHandler = Xphp::instance('ResourceHandler');
		$vmResource = $resourceHandler->pGetUserResourceVM(Xphp::$_user['useruuid'],"tree_id", "desc", $limit = 0, "all");
		$vmList = array();
		$vcenterList = array();
		//不是全局观察者获取对应用户的任务
		if(!in_array("global_observer", $_SESSION['permission'])){
		    foreach ($vmResource['data'] as $vm){
		        if(!in_array($vm['vcenter_uuid'], $vcenterList)){
		            $vcenterList[] = $vm['vcenter_uuid'];
		        }
		        if(!in_array($vm['uuid'], $vmList)){
		            $vmList[] = $vm['uuid'];
		        }
		    }
		}
		$vmuuids = implode("','", $vmList);
		$vcenteruuids = implode("','", $vcenterList);
		
		foreach ($data as $d){
		    $vmNumList = $this->getVmNumList(intval($d['hypervisor_type']), $vmuuids , $vcenteruuids);
		    //检查用户是否有该虚拟化虚拟机
		    if(intval($vmNumList['total_vm_num']) == 0) continue;
		    $hypervisor = intval($d['hypervisor_type']);
			$info[] = array(
				'hypervisor' => Xphp::$_config['VMHYPERVISORDES'][$hypervisor],
				'total_vm_num' => $vmNumList['total_vm_num'],
				'protected_vm_num' => $vmNumList['protected_vm_num'],
				'color' => $this->getHypervisorColor($hypervisor),
				'all_total_vm_num' => intval($dataTotal[0]['all_total_vm_num'])
			);
		}
		
		return json_encode($info);
		
	}
	
	/**
	 * 获取虚拟机备份概况列表
	 * @param unknown $params
	 */
	public function getVmBackupList($params){
		$start = $params['start'];
		$length = $params['length'];
		$sortType = $params['sortType'];
		$sql = 'select vbt.vm_uuid, vbt.vcenter_uuid, vbt.vm_name, vv.detail, vv.vcenter_ip, vv.username, vbt.hypervisor_type, bbt.task_uuid from bd_backup_timepoint bbt, vm_backup_timepoint vbt, vm_vcenter vv where vbt.vcenter_uuid = vv.vcenter_uuid and bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.module_type = ? 
                and vbt.vm_timepoint_id in (select max(vm_timepoint_id) from vm_backup_timepoint vbt2 join bd_backup_timepoint bbt2 on vbt2.timepoint_uuid = bbt2.timepoint_uuid and bbt2.module_type = ? group by vm_uuid)';
		$sqlCount = 'select count(distinct vbt.vm_uuid, vbt.vcenter_uuid) as total from bd_backup_timepoint bbt, vm_backup_timepoint vbt, vm_vcenter vv where vbt.vcenter_uuid = vv.vcenter_uuid and bbt.timepoint_uuid = vbt.timepoint_uuid  and bbt.module_type = ? ';
		
		$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['MODULE_TYPE']['VM']);
		$sqlCountParams = array(Xphp::$_config['MODULE_TYPE']['VM']);
		//admin用户获取所有，其余全局用户获取自己的，租户内管理员获取租户所有的，其余租户内用户获取自己的
		//检查是否是租户管理员
		$tenantHandler = Xphp::instance('TenantHandler');
		$userHandler = Xphp::instance('UsersHandler');
		$vmHandler = Xphp::instance("Vmhandler");
		$tenantMangerFlag = $userHandler->pCheckTenantManager();
		$userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
		$userListDes = implode("','", $userList);
		//Master用户或全局观察者
		if(in_array("global_observer", $_SESSION['permission']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
		    
		}else if($tenantMangerFlag){
		    $sql .= " and bbt.user_uuid in ('".$userListDes."')";
		    $sqlCount .= " and bbt.user_uuid in ('".$userListDes."')";
		}else{
		    $sql .= " and bbt.user_uuid = ? ";
		    $sqlCount .= " and bbt.user_uuid = ? ";
		    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
		    $sqlCountParams = array_merge($sqlCountParams,array(Xphp::$_user['useruuid']));
		}
		
		$sql .= " group by vbt.vm_uuid, vbt.vcenter_uuid order by vbt.vm_name $sortType limit ?, ?";
		$sqlParams = array_merge($sqlParams, array($start, $length));
		$data = $this->dbSelect($sql, $sqlParams);
		$dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
		$utils = Xphp::instance("Utils");
		$vcenter = Xphp::instance('Vcenter');
		$records = array();
		$records["data"] = array();
		$id = $start + 1;
		foreach ($data as $d){
		    $vmBackupInfo = $this->getVmBackupInfo($d, $vmHandler, $tenantHandler, $userHandler); //通过虚拟机路径获取虚拟机备份情况
			$records["data"][] = array(
				$id++,
				$vmBackupInfo['vm_name'],
			    $vcenter->getVcenterIp($d['vcenter_ip'], $d['detail'], intval($d['hypervisor_type']), $d['username']),
				$vmBackupInfo['hypervisor'],
				$vmBackupInfo['backup_count'],
				$vmBackupInfo['backup_storage'],
			);
		}
		
		$records["draw"] = $params['draw'];
		$records["recordsTotal"] = intval($dataCount[0]['total']);
		$records["recordsFiltered"] = intval($dataCount[0]['total']);
		
		return  json_encode($records);
	}
    /**
     * 获取所有虚拟化中心
     * @param unknown $params
     */
    public function getAllVmCenter($params){
        $hypervisor = intval($params['hypervisor']);  //虚拟化类型
        $vcenter = Xphp::instance('Vcenter');//实例化虚拟化中心类
        //查询当前虚拟化类型下的所有虚拟化中心
        $sql ="select distinct vcenter_ip from vm_vcenter where hypervisor_type = ?";
        $data = $this->dbSelect($sql, array($hypervisor));
        $list = array();
        foreach ($data as $d){
            $list[] = array(
                'text' => $d['vcenter_ip'],
                'value' => $d['vcenter_ip']
            );
        }
        return  json_encode($list);
    }

    /**
     * 获取指定虚拟机报表
     * @param unknown $params
     */
    public function getVmDetailList($params){
        $start = $params['start'];    //第几条开始
        $length = $params['length'];  //显示长度
        $sortColumn = $params['sortColumn']; //排序索引
        $sortType = $params['sortType'];  //排序类型 asc/desc
        $vcenterIp = $params['vcenterIp'];    //虚拟化中心IP
        $hypervisor = intval($params['hypervisor']);  //虚拟化类型
        $utils = Xphp::instance('Utils'); //实例化工具类
        $records = array();
        $records["data"] = array();
        $list = array();
        $id = $start + 1;
        //虚拟化中心表格查询
        $sql = "select vv.vcenter_uuid, vv.detail, vv.username, count(vt.tree_id) as total_vm, vt.parent_uuid as tenant_uuid, (select name from vm_tree where vm_tree.uuid = vt.parent_uuid) as tenant_name
                from vm_vcenter vv left join vm_tree vt on vv.vcenter_uuid = vt.vcenter_uuid and vv.vcenter_uuid <> vt.parent_uuid
                where vv.hypervisor_type = ? and vv.vcenter_ip = ? and vt.display_mode = ? and vt.type = ? group by vt.parent_uuid ";
        //虚拟化中心个数统计
        $sqlCount = "select count(distinct vv.vcenter_uuid) as total from vm_vcenter vv left join vm_tree vt on vv.vcenter_uuid = vt.vcenter_uuid
                 where vv.hypervisor_type = ? and vv.vcenter_ip = ? and vt.display_mode = ? and vt.type = ? ";
        //sql查询参数
        $sqlParams = array($hypervisor, $vcenterIp, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        $sqlCountParams = array($hypervisor, $vcenterIp, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        //查询条数
        $sql .= " limit ?, ?";
        $sqlParams = array_merge($sqlParams, array($start,$length));
        //执行sql语句
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        //组装表格参数返回界面
        foreach ($data as $d){
            $tenantInfo = $this->getTenantBackupInfo($d['tenant_uuid']);
            $detail = json_decode($d['detail'],true);
            $list[] = array(
                $id++,          //自增长id
                $d['tenant_name'], //租户用户名
                intval($d['total_vm']),  //虚拟机总数
                $tenantInfo['protect_vm'],//受保护虚拟机数
                $tenantInfo['task_num'],    //备份任务个数
                $tenantInfo['timepoint_count'], //时间点个数
                $tenantInfo['total_size'],  //数据总大小
                $tenantInfo['real_size'],   //写入总大小
                $tenantInfo['timepoint'],   //最近一次备份时间点
                $d['tenant_uuid'],           //用于详情展开虚拟化中心唯一标识
                array(   //虚拟机大小和写入大小展示需要用到
                    'totalSizeDes' => $utils->calSize($tenantInfo['total_size']),
                    'realSizeDes' => $utils->calSize($tenantInfo['real_size']),
                )
            );
        }
        //按照对应参数排序
        $list = $utils->arraySort($list, $sortColumn, $sortType, $start, $length);  //排序
        $list2 = array();
        foreach ($list as $key=>$d){
            $list2[$key] = $d;
            $list2[$key][6] = $utils->calSize($d[6]);
            $list2[$key][7] = $utils->calSize($d[7]);
            if(empty($d[8])){
                $list2[$key][8] = Xphp::$_config['TIMESPACE'];
            }
        }

        $records["data"] = $list2;
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = intval($dataCount[0]['total']);
        $records["recordsFiltered"] = intval($dataCount[0]['total']);

        return  json_encode($records);
    }
    /**
     * 获取指定虚拟机租户报表
     * @param unknown $params
     */
    public function getRentDetails($params){
        $start = $params['start'];    //第几条开始
        $length = $params['length'];  //显示长度
        $sortColumn = $params['sortColumn']; //排序索引
        $sortType = $params['sortType'];  //排序类型 asc/desc
        $tenantuuid = $params['vcenteruuid'];//租户的vm_tree.uuid
        $hypervisor = $params['hypervisor']; //虚拟化类型
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        $records = array();
        $records["data"] = array();
        $vmlist = array();
        $id = $start + 1;

        $sql = "select name, detail, uuid, vcenter_uuid from vm_tree where parent_uuid = ? and display_mode = ? and type = ? ";
        $sqlCount = "select count(tree_id) as total from vm_tree where parent_uuid = ? and display_mode = ? and type = ? ";
        $sqlParams = array($tenantuuid, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        $sqlCountParams = array($tenantuuid, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        //查询条数
        $sql .= " limit ?, ?";
        $sqlParams = array_merge($sqlParams, array($start,$length));
        //执行sql语句
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        if(!empty($data)){
            $vmBackupInfoList = $vmHandler->getVMReportLastBackupInfo($data);
            foreach ($data as $d){
                $lastBackupInfo = array(
                    'lastbackuptime' => Xphp::$_config['TIMESPACE'],
                    'backuptask' => Xphp::$_config['NULLSPACE'],
                    'timepointcount' => Xphp::$_config['NULLSPACE'],
                    'backupstorage' => Xphp::$_config['NULLSPACE'],
                    'totalsizeDes' => Xphp::$_config['NULLSPACE'],
                );

                //虚拟机是否在任务中
                if(!empty($vmBackupInfoList)){
                    foreach ($vmBackupInfoList as $list){
                        if($list['vm_uuid'] == $d['uuid'] && $list['vcenter_uuid'] == $d['vcenter_uuid']){
                            $lastBackupInfo = $list;
                        }
                    }
                }
                $vmIp = '';
                //获取虚拟机IP
                if(!empty($d['detail'])){
                    $detail = json_decode($d['detail'], true);
                    if(in_array($hypervisor, Xphp::$_config['VMHYPERVISORGROUP']['openstack']) ||
                        $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD_STACK'] ||
                        $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM'] ||
                        $hypervisor == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE']){
                        $network = $detail['network_list'];
                        $vmIp = '';
                        $ipList = array();
                        if(!empty($network)){
                            foreach ($network as $net){
                                $list = $net['ip_list'];
                                foreach ($list as $ip){
                                    $ipList[] = $ip;
                                    $vmIp .= $ip . PHP_EOL;
                                }
                            }
                        }

                    }
                }

                $vmlist[] = array(
                    $id++,    //自增长id
                    $d['name'],//虚拟机名
                    $vmIp,    //虚拟机IP
                    $lastBackupInfo['backuptask'],  //备份任务名
                    $lastBackupInfo['timepointcount'],  //备份时间点个数
                    $lastBackupInfo['totalsize'],    //虚拟机总大小
                    $lastBackupInfo['backupstorageSize'],//备份数据写入总大小
                    $lastBackupInfo['lastbackuptime'],    //最近一次备份时间
                    array(    //需要用到的额外参数信息
                        'vcenteruuid' => $d['vcenter_uuid'],
                        'vmuuid' => $d['uuid'],
                        'hypervisor' => $hypervisor,
                        'totalSizeDes' => $lastBackupInfo['totalsizeDes'],
                        'realSizeDes' => $lastBackupInfo['backupstorage']
                    ),
                );
            }
        }
        //按照对应参数排序
        $vmlist = $utils->arraySort($vmlist, $sortColumn, $sortType, 0,-1);  //排序
        $list2 = array();
        foreach ($vmlist as $key=>$d){
            $list2[$key] = $d;
            if($d[2] == ''){
                $list2[$key][2] = Xphp::$_config['NULLSPACE'];
            }
            $list2[$key][5] = $utils->calSize($d[5]);
            $list2[$key][6] = $utils->calSize($d[6]);
        }
        $records["data"] = $list2;
        $records["draw"] = $params['draw'];
        $records["start"] =  $start ;
        $records["sql"] =  $sql ;
        $records["sqlParams"] =  $sqlParams ;
        $records["recordsTotal"] = intval($dataCount[0]['total']);
        $records["recordsFiltered"] = intval($dataCount[0]['total']);

        return  json_encode($records);
    }
	
	/**
	 * 获取虚拟机备份统计信息
	 * @param string $dirPath
	 */
	public function getVmBackupInfo($details, $vmHandler, $tenantHandler, $userHandler){
	    $uuid = array(
	        'vcenter_uuid' => $details['vcenter_uuid'],
	        'uuid' => $details['vm_uuid']
	    );
	    $list[] = $uuid;
	    $lastBackupInfo = $vmHandler->getVMReportLastBackupInfo($list);
		$info = array(
		    'vm_name' => $details['vm_name'],
		    'hypervisor' => Xphp::$_config['VMHYPERVISORDES'][intval($details['hypervisor_type'])],
			'backup_task' => $lastBackupInfo[0]['backuptask'],
		    'last_backup_time'=> $lastBackupInfo[0]['lastbackuptime'],
		    'backup_count'=> $lastBackupInfo[0]['backupcount'],
		    'backup_storage'=> $lastBackupInfo[0]['backupstorage']
		);
		return $info;
	}
	
	/**
	 * 获取文件代理备份信息
	 * @param unknown $agentuuid
	 * @return unknown[]|fetchAll()[]
	 */
	public function getFsAgentInfo($agentuuid){
	    $sql = "select bbt.timepoint, fbt.agent_name, fbt.agent_ip from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and fbt.agent_uuid = ? order by bbt.timepoint desc";
	    $data = $this->dbSelect($sql, array($agentuuid));
	    $info = array(
	        'agent_name' =>  $data[0]['agent_ip']."(". $data[0]['agent_name'] .")",
	        'last_backup_time'=> $data[0]['timepoint'],
	        'backup_count'=> count($data),
	    );
	    
	    return $info;
	}
	
	/**
	 * 获取数据库代理备份信息
	 * @param unknown $agentuuid
	 * @return unknown[]|fetchAll()[]
	 */
	public function getDbAgentInfo($agentuuid){
	    $sql = "select bbt.timepoint, dbt.agent_ip from bd_backup_timepoint bbt, db_backup_timepoint dbt where bbt.timepoint_uuid = dbt.timepoint_uuid and dbt.agent_uuid = ? order by bbt.timepoint desc";
	    $data = $this->dbSelect($sql, array($agentuuid));
	    $info = array(
	        'agent_name' => $data[0]['agent_ip'],
	        'last_backup_time'=> $data[0]['timepoint'],
	        'backup_count'=> count($data),
	    );
	    
	    return $info;
	}
	
	/**
	 * 根据虚拟化类型获取对应虚拟化饼状图配色
	 * @param intval $hypervisor
	 */
	public function getHypervisorColor($hypervisor){
		$color = '#4ad1cd';
		$type = Xphp::$_config['VMHYPERVISORTYPE'];
		switch ($hypervisor){
			case $type['VM_HYPERVISOR_TYPE_VMWARE']:
			case $type['VM_HYPERVISOR_TYPE_SUGON_CLOUD_VIEW_SRM']:
			case $type['VM_HYPERVISOR_TYPE_XHERE']:
				$color = "#76B61A";
				break;
			case $type['VM_HYPERVISOR_TYPE_HYPERV']:
				$color = "#0D0B64";
				break;
			case $type['VM_HYPERVISOR_TYPE_XENSERVER']:
			case $type['VM_HYPERVISOR_TYPE_XCP_NG']:
				$color = "#2268D5";
				break;
			case $type['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE']:
				$color = "#002976";
				break;
			case $type['VM_HYPERVISOR_TYPE_HALSIGN_VGATE']:
				$color = "#FD2B34";
				break;
			case $type['VM_HYPERVISOR_TYPE_WINHONG_WINSERVER']:
				$color = "#B41911";
				break;
			case $type['VM_HYPERVISOR_TYPE_KVM']:
				$color = "#7F9581";
				break;
			case $type['VM_HYPERVISOR_TYPE_NEOKYLIN_KVM']:
				$color = "#E8862F";
				break;
			case $type['VM_HYPERVISOR_TYPE_H3C_KVM']:
			case $type['VM_HYPERVISOR_TYPE_H3C_CAS_CVD']:
				$color = "#FB2619";
				break;
			case $type['VM_HYPERVISOR_TYPE_SANGFOR_KVM']:
			case $type['VM_HYPERVISOR_TYPE_SANGFOR_VVDK_KVM']:
				$color = "#39B860";
				break;
			case $type['VM_HYPERVISOR_TYPE_SDC_OS_KVM']:
				$color = "#A18B8B";
				break;
			case $type['VM_HYPERVISOR_TYPE_OPENSTACK_KVM']:
				$color = "#00B8EF";
				break;
			case $type['VM_HYPERVISOR_TYPE_FLEX_CLOUD_KVM']:
			case $type['VM_HYPERVISOR_TYPE_FLEX_HCS_KVM']:
				$color = "#004E9A";
				break;
			case $type['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_KVM']:
			case $type['VM_HYPERVISOR_TYPE_HUAWEI_FUSION_SPHERE_XEN']:
			case $type['VM_HYPERVISOR_TYPE_XFUSION_KVM']:
				$color = "#FBAB8B";
				break;
			case $type['VM_HYPERVISOR_TYPE_RHV_KVM']:
			case $type['VM_HYPERVISOR_TYPE_OVIRT_KVM']:
			case $type['VM_HYPERVISOR_TYPE_KVM_ZVIRT']:
			case $type['VM_HYPERVISOR_TYPE_OLVM']:
			case $type['VM_HYPERVISOR_TYPE_HOSTVM']:
            case $type['VM_HYPERVISOR_TYPE_RED_VIRT']:
            case $type['VM_HYPERVISOR_TYPE_ROSA_VIRT']:
				$color = "#FF1823";
				break;
			case $type['VM_HYPERVISOR_TYPE_XEN']:
				$color = "#1659DE";
				break;
			case $type['VM_HYPERVISOR_TYPE_ORACLEVM']:
				$color = "#E47277";
				break;
			case $type['VM_HYPERVISOR_TYPE_DONGCHEN_DSERVER']:
				$color = "#74BCF9";
				break;
			case $type['VM_HYPERVISOR_TYPE_CLOUD_VIEW_SVM_VMWARE']:
				$color = "#327333";
				break;
			case $type['VM_HYPERVISOR_TYPE_OS_EASY_V_SERVER']:
				$color = "#C2C621";
				break;
			case $type['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM']:
			case $type['VM_HYPERVISOR_TYPE_INSPUR_VVDK']:
			case $type['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_OPENSTACK']:
				$color = "#002976";
				break;
            case $type['VM_HYPERVISOR_TYPE_DIY_WINGHONG']:
                $color = "#BD1FB7";
                break;
            case $type['VM_HYPERVISOR_TYPE_SMARTX_KVM']:
                $color = "#BD1FB7";
                break;
            case $type['VM_HYPERVISOR_TYPE_ZSTACK']:
            case $type['VM_HYPERVISOR_TYPE_CLOUDVIEW_KVM']:
                $color = '#fcefe8';
                break;
            case $type['VM_HYPERVISOR_TYPE_WINHONG_KVM']:
                $color = '#f2be45';
                break;
            case $type['VM_HYPERVISOR_TYPE_PROXMOX']:
            case $type['VM_HYPERVISOR_TYPE_LENOVO_AIO']:
                $color = '#c0ebd7';
                break;
		}
		return $color;
	}
	
	/**
	 * 获取备份的虚拟机列表
	 * @param unknown $params
	 */
	public function getBackupVmList($params){
		$sql = "select distinct vbt.dir_path, vbt.vcenter_uuid, vbt.vm_uuid, vbt.vm_name from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid 
                and bbt.module_type = ? and vbt.vm_timepoint_id in (select max(vm_timepoint_id) from vm_backup_timepoint vbt2 join bd_backup_timepoint bbt2 on vbt2.timepoint_uuid = bbt2.timepoint_uuid and bbt2.module_type = ? group by vm_uuid)"; //只获取最新的虚拟机名称
		
		$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], Xphp::$_config['MODULE_TYPE']['VM']);
		//admin用户获取所有，其余全局用户获取自己的，租户内管理员获取租户所有的，其余租户内用户获取自己的
		//检查是否是租户管理员
		$tenantHandler = Xphp::instance('TenantHandler');
		$userHandler = Xphp::instance('UsersHandler');
		$tenantMangerFlag = $userHandler->pCheckTenantManager();
		$userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
		$userListDes = implode("','", $userList);
		//Master用户或全局观察者
		if(in_array("global_observer", $_SESSION['permission']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
		    
		}else if($tenantMangerFlag){
		    $sql .= " and bbt.user_uuid in ('".$userListDes."')";
		}else{
		    $sql .= " and bbt.user_uuid = ? ";
		    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
		}
		
		$data = $this->dbSelect($sql, $sqlParams);
		$info = array();
		foreach($data as $d){
			$info[] = array(
				'name' => $d['vm_name'],
				'value' => $d['vcenter_uuid'] . '_' . $d['vm_uuid'] //通过vcenter_uuid+vm_uuid确定唯一性
			);
			
		}
		
		return json_encode($info);
	}
	
	/**
	 * 获取选中的虚拟机备份点统计信息
	 * @param unknown $params
	 */
	public function getPointPie($params){
		$path = $params['dirPath']; //改为vcenter_uuid.'_'.vm_uuid
		$pathArr = $params['dirPath']? explode('_', $path):$params['dirPath'];
		$vcenter_uuid = $pathArr[0];
		$vm_uuid = $pathArr[1];
		$sql = "select count(vbt.vm_timepoint_id) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.module_type = ? ";
		$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM']);
		if(!empty($vcenter_uuid) && !empty($vm_uuid)){
			$sql .= " and vbt.vcenter_uuid = ? and vbt.vm_uuid = ? ";
			$sqlParams = array_merge($sqlParams, array($vcenter_uuid, $vm_uuid));
		}
		
		//admin用户获取所有，其余全局用户获取自己的，租户内管理员获取租户所有的，其余租户内用户获取自己的
		//检查是否是租户管理员
		$tenantHandler = Xphp::instance('TenantHandler');
		$userHandler = Xphp::instance('UsersHandler');
		$tenantMangerFlag = $userHandler->pCheckTenantManager();
		$userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
		$userListDes = implode("','", $userList);
		//Master用户或全局观察者
		if(in_array("global_observer", $_SESSION['permission']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
		    
		}else if($tenantMangerFlag){
		    $sql .= " and bbt.user_uuid in ('".$userListDes."')";
		}else{
		    $sql .= " and bbt.user_uuid = ? ";
		    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
		}
		
		
		$data = $this->dbSelect($sql, $sqlParams);
		$info = array(
			'total_point' => $data[0]['total'],
			'full_point' => $this->getTimepointNum($vcenter_uuid, $vm_uuid, Xphp::$_config['BACKUP_MODE']['FULL']),
			'incr_point' => $this->getTimepointNum($vcenter_uuid, $vm_uuid, Xphp::$_config['BACKUP_MODE']['INCREMENTAL']),
			'diff_point' => $this->getTimepointNum($vcenter_uuid, $vm_uuid, Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'])
		);
		
		return json_encode($info);
	}
	
	/**
	 * 获取备份时间点个数
	 * @param string $vcenter_uuid
	 * @param string $vm_uuid
	 * @param int $mode
     * @return mixed
	 */
	public function getTimepointNum($vcenter_uuid, $vm_uuid, $mode){
		$sql = 'select count(vbt.vm_timepoint_id) as total from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.module_type = ? and bbt.backup_mode = ? ';
		$sqlParams = array(Xphp::$_config['MODULE_TYPE']['VM'], $mode);
		if(!empty($vcenter_uuid) && !empty($vm_uuid)){
		    $sql .= " and vbt.vcenter_uuid = ? and vbt.vm_uuid = ?";
		    $sqlParams = array_merge($sqlParams, array($vcenter_uuid, $vm_uuid));
		}
		
		//admin用户获取所有，其余全局用户获取自己的，租户内管理员获取租户所有的，其余租户内用户获取自己的
		//检查是否是租户管理员
		$tenantHandler = Xphp::instance('TenantHandler');
		$userHandler = Xphp::instance('UsersHandler');
		$tenantMangerFlag = $userHandler->pCheckTenantManager();
		$userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
		$userListDes = implode("','", $userList);
		//Master用户或全局观察者
		if(in_array("global_observer", $_SESSION['permission']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
		    
		}else if($tenantMangerFlag){
		    $sql .= " and bbt.user_uuid in ('".$userListDes."')";
		}else{
		    $sql .= " and bbt.user_uuid = ? ";
		    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
		}
		$data = $this->dbSelect($sql, $sqlParams);
		
		return $data[0]['total'];
	}
	
	/**
	 * 获取虚拟机备份存储大小统计图信息
	 * @param unknown $params
	 */
	public function getVmStoragePie($params){
		$path = $params['dirPath'];
        $pathArr = $params['dirPath']? explode('_', $path):$params['dirPath'];
        $vcenter_uuid = $pathArr[0];
        $vm_uuid = $pathArr[1];
		$sql = "select sum(bbt.write_size) as used from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and 
                vbt.vcenter_uuid = ? and vbt.vm_uuid = ?";
		$sqlParams = array($vcenter_uuid, $vm_uuid);
		if(empty($path)){
			$sql = "select sum(bbt.write_size) as used from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid ";
			$sqlParams = array();
		}
		
		//admin用户获取所有，其余全局用户获取自己的，租户内管理员获取租户所有的，其余租户内用户获取自己的
		//检查是否是租户管理员
		$tenantHandler = Xphp::instance('TenantHandler');
		$userHandler = Xphp::instance('UsersHandler');
		$tenantMangerFlag = $userHandler->pCheckTenantManager();
		$userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
		$userListDes = implode("','", $userList);
		//Master用户或全局观察者
		if(in_array("global_observer", $_SESSION['permission']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
		    
		}else if($tenantMangerFlag){
		    $sql .= " and bbt.user_uuid in ('".$userListDes."')";
		}else{
		    $sql .= " and bbt.user_uuid = ? ";
		    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
		}
		
		$data = $this->dbSelect($sql, $sqlParams);
		
		$utils = Xphp::instance('Utils');
		$vmSize = $this->getVmFreeSize();
		$freeSize = $vmSize['free'];
		$usedSizeDes = $utils->calSize($data[0]['used']);
		$usedSize = $data[0]['used'];
		if(empty($data[0]['used'])){
			$usedSize = 0;
		}
		$info = array(
			'used_size' => $usedSize,
			'usedDes' => $usedSizeDes,
			'free_size' => $freeSize,
			'freeDes' => $utils->calSize($freeSize)
		);
		
		return json_encode($info);
	}
	
	/**
	 * 获取虚拟机空闲大小 
	 */
	public function getVmFreeSize(){
	    $storageuuids = $this->pGetUserStorageuuids();
	    $storageDes = implode("','", $storageuuids);
	    $vmSize = array(
	        'free' => 0,
	        'used' => 0,
	        'total' => 0
	    );
	    
		$sql = "select sum(total_size) as total, sum(free_size) as free from bd_storage_resource where lan_free_flag = ? and use_mode = ? ";
		
		//全局观察者
		if(in_array("global_observer", $_SESSION['permission']) || Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9" && empty($_SESSION['tenantuuid'])){
		
		}else{
		    $sql .= " and storage_uuid in ('".$storageDes."')";
		}
		$data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP']));
		if(!empty($data)){
		    $vmSize = array(
		        'free' => $data[0]['free'],
		        'used' => $data[0]['total'] - $data[0]['free'],
		        'total' => $data[0]['total']
		    );
		}
		return $vmSize;
	}
	
	/**
	 * 获取虚拟机备份存储大小近期统计信息 
	 * @param unknown $params
	 */
	public function getVmStorageLine($params){
		$path = $params['dirPath'];
        $pathArr = $params['dirPath']? explode('_', $path):$params['dirPath'];
        $vcenter_uuid = $pathArr[0];
        $vm_uuid = $pathArr[1];
		$days = intval($params['days']);
		$sql = "select bbt.write_size, bbt.timepoint from bd_backup_timepoint bbt, vm_backup_timepoint vbt where vbt.timepoint_uuid = bbt.timepoint_uuid and  DATE_SUB(CURDATE(),INTERVAL ". $days ." DAY) <= timepoint ";
		$sqlParams = array();
		if(!empty($vcenter_uuid) && !empty($vm_uuid)){
			$sql .= " and vbt.vcenter_uuid = ? and vbt.vm_uuid = ?";
			$sqlParams = array_merge($sqlParams, array($vcenter_uuid, $vm_uuid));
		} 
		//admin用户获取所有，其余全局用户获取自己的，租户内管理员获取租户所有的，其余租户内用户获取自己的
		//检查是否是租户管理员
        $tenantHandler = Xphp::instance('TenantHandler');
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
        $userListDes = implode("','", $userList);
        //Master用户获取全局观察者
        if(in_array("global_observer", $_SESSION['permission']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
            
        }else if($tenantMangerFlag){
            $sql .= " and bbt.user_uuid in ('".$userListDes."')";
        }else{
    		$sql .= " and bbt.user_uuid = ? ";
    		$sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
		}
		$sql .= " order by bbt.timepoint asc";
		$data = $this->dbSelect($sql,$sqlParams);
		$info = array();
		$utils = Xphp::instance('Utils');
		for($i=$days;$i>0;$i--){
			$dayValue = strtotime(date("Y-m-d")) - 3600*24*($i-1);
			$dayDate = date("Y-m-d", $dayValue);
			$storageSize = 0;
			$storageDes = '0';
			foreach($data as $d){
				$day = date('Y-m-d',strtotime($d['timepoint']));
				if($day == $dayDate){
					$storageSize += $d['write_size'];
				}
			}
			$storageDes = $utils->calSize($storageSize);
			if($storageSize == 0){
				$storageDes = 0;
			}
			$info[] = array(
				'date' => $dayDate,
				'storage_size' => $storageSize,
				'des' => $storageDes
			);
			
		}
		
		return json_encode($info);
		
	}
	
	/**
	 * 获取日历时间点统计
	 * @param unknown $params
	 */
	public function getTimepointDate($params){
		$path = $params['dirPath'];
        $pathArr = $params['dirPath']?explode('_', $path):$params['dirPath'];
        $vcenter_uuid = $pathArr[0];
        $vm_uuid = $pathArr[1];
		$date = $params['date'];
		if(empty($date)){
		    $day = date("Y-m-d");
		}
		$flag = $params['changeFlag'];
		$sql = "select bbt.timepoint, bbt.backup_mode from bd_backup_timepoint bbt, vm_backup_timepoint vbt where vbt.timepoint_uuid = bbt.timepoint_uuid and bbt.timepoint like '%".$date."%' 
		        and bbt.module_type = " . Xphp::$_config['MODULE_TYPE']['VM'];
		$sqlParams = array();
		if(!empty($vcenter_uuid) && !empty($vm_uuid)){
			$sql .= " and vbt.vcenter_uuid = ? and vbt.vm_uuid = ?";
			$sqlParams= array($vcenter_uuid, $vm_uuid);
		}
		//admin用户获取所有，其余全局用户获取自己的，租户内管理员获取租户所有的，其余租户内用户获取自己的
		//检查是否是租户管理员
		$tenantHandler = Xphp::instance('TenantHandler');
		$userHandler = Xphp::instance('UsersHandler');
		$tenantMangerFlag = $userHandler->pCheckTenantManager();
		$userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
		$userListDes = implode("','", $userList);
		//Master用户或全局观察者
		if(in_array("global_observer", $_SESSION['permission']) || (empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9")){
		    
		}else if($tenantMangerFlag){
		    $sql .= " and bbt.user_uuid in ('".$userListDes."')";
		}else{
		    $sql .= " and bbt.user_uuid = ? ";
		    $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
		}
		$sql .= " order by bbt.timepoint asc";
		$data = $this->dbSelect($sql, $sqlParams);
		$info =  array();
		$timepointInfo = array();
		$dayCount =  intval(date('t', strtotime($date)));
		for($i= 0;$i<$dayCount;$i++){
			$dayValue = strtotime(date('Y-m-01', strtotime(date("Y-m-d")))) + 3600*24*$i;
			if($flag){
				$dayValue = strtotime($date) + 3600*24*$i;
			}
			$dayDate = date("Y-m-d", $dayValue);
			$fullPoint = array();
			$incrPoint = array();
			$diffPoint = array();
			foreach ($data as $d){
				$timepoint = date('Y-m-d',strtotime($d['timepoint']));
				$mode = $d['backup_mode'];
				if($mode == Xphp::$_config['BACKUP_MODE']['FULL'] && $timepoint == $dayDate){
					$fullPoint[] = $d['timepoint'];
				}else if($mode == Xphp::$_config['BACKUP_MODE']['INCREMENTAL'] && $timepoint == $dayDate){
					$incrPoint[] = $d['timepoint'];
				}else if($mode == Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'] && $timepoint == $dayDate){
					$diffPoint[] = $d['timepoint'];
				}
			}
			if(!empty($fullPoint)){
				$fullNum = count($fullPoint);
				$timepointInfo[] = array(
					"id" => $fullPoint[0],
					"title" => $fullNum . Xphp::$_lang['WEB_REPORT_FULL_BACKUP_NUM'],
					"class" =>"mode-full",
					"start" => strtotime($fullPoint[0])* 1000,
					"end"=> strtotime($fullPoint[$fullNum-1])*1000
				);
			}
			
			if(!empty($incrPoint)){
				$incrNum = count($incrPoint);
				$timepointInfo[] = array(
						"title" => $incrNum . Xphp::$_lang['WEB_REPORT_INCR_BACKUP_NUM'],
						"class" =>"mode-incr",
						"start" => strtotime($incrPoint[0])*1000,
						"end"=> strtotime($incrPoint[$incrNum-1])*1000
				);
			}
			
			if(!empty($diffPoint)){
				$diffNum = count($diffPoint);
				$timepointInfo[] = array(
						"title" => $diffNum . Xphp::$_lang['WEB_REPORT_DIFF_BACKUP_NUM'],
						"class" =>"mode-diff",
						"start" => strtotime($diffPoint[0])*1000,
						"end"=> strtotime($diffPoint[$diffNum-1])*1000
				);
			}
			
		}
		$day = date("Y-m-d");
		$language = $this->getSystemLang();
		$info = array(
			'language' => $language,
			'day'=> $day,
			'source' => $timepointInfo
		);
		return json_encode($info);
		
	}
	
	/**
	 * 获取系统设置语言
	 */
	public function getSystemLang(){
		$userUUID= Xphp::$_user['useruuid'];
		$sql = "select language from bd_user where user_uuid = ?";
		$data = $this->dbSelect($sql, array($userUUID));
		return $data[0]['language'];
	}
	
	/**
	 * 获取设备信息
	 * @param unknown $params
	 * @return string
	 */
	public function getMachineInfo($params){
	    $utils = Xphp::instance('Utils');
	    //运行时间
	    $cmd = "cat /proc/uptime | awk '{print $1}'";
	    exec($cmd, $timeInfo);
	    $value = round($timeInfo[0]);
	    $runTime = "";
	    $year = round($value/3600/24/365, 1);
	    if($year != 0){
	        $runTime .= $year . Xphp::$_lang['WEB_UTILS_YEAR'];
	    }
	    
	    $day = round($value/3600/24%365, 1);
	    if($day != 0){
	        $runTime .= $day .Xphp::$_lang['WEB_UTILS_DAY'];
	    }
	    $hour = round($value/3600%24, 1);
	    if($hour != 0){
	        $runTime .= $hour .Xphp::$_lang['WEB_UTILS_HOUR'];
	    }
	    if(empty($runTime)){
	        $minute = round($value/60%60, 1);
	        if($minute != 0){
	            $runTime .= $minute .Xphp::$_lang['WEB_UTILS_MINUTE'];
	        }
	        $second = round($value%60, 1);
	        if($second != 0){
	            $runTime .= $second .Xphp::$_lang['WEB_UTILS_SECOND'];
	        }
	    }
	    //CPU信息
	    $cmd = "cat /proc/cpuinfo | grep name | cut -f2 -d: | uniq -c";
	    exec($cmd, $cpuInfo);
	    $cpuInfo = $cpuInfo[0];
	    //内存大小
	    $cmd = "cat /proc/meminfo | grep MemTotal| awk '{print $2}'";
	    exec($cmd, $memoryInfo);
	    $memory = $utils->calSize(intval($memoryInfo[0]) * 1024);
	    //网卡个数
	    $cmd = "";
	    exec($cmd, $networkInfo);
	    $network = count(explode(PHP_EOL, $networkInfo[0]));
	    //存储个数
	    $sql = "select count(distinct storage_uuid) as total from bd_backup_timepoint";
	    $data = $this->dbSelect($sql);
	    $storageNum = intval($data[0][total]);
	    $records["data"][] = array(
	        $runTime,
	        $cpuInfo,
	        $memory,
	        $network,
	        $storageNum
	    );
	    
	    $records["draw"] = $params['draw'];;
	    $records["recordsTotal"] = 1;
	    $records["recordsFiltered"] = 1;
	    
	    return  json_encode($records);
	}
	
	/**
	 * 公共方法
	 * 获取当前用户所有存储uuid
	 * @author luokai@vinchin.com
	 * @return fetchAll()[]
	 */
	public function pGetUserStorageuuids(){
	    $sql = "select resource_uuid from mt_user_resource where resource_type = ? ";
	    $sqlParams = array(Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
	    //admin用户获取所有，其余全局用户获取自己的，租户内管理员获取租户所有的，其余租户内用户获取自己的
	    //检查是否是租户管理员
	    $tenantHandler = Xphp::instance('TenantHandler');
	    $userHandler = Xphp::instance('UsersHandler');
	    $tenantMangerFlag = $userHandler->pCheckTenantManager();
	    $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
	    $userListDes = implode("','", $userList);
	    if($tenantMangerFlag){
	        $sql .= " and user_uuid in ('".$userListDes."')";
	    }else{
	        $sql .= " and user_uuid = ? ";
	        $sqlParams = array_merge($sqlParams,array(Xphp::$_user['useruuid']));
	    }
	    $data = $this->dbSelect($sql, $sqlParams);
	    $info = array();
	    if(!empty($data)){
	        foreach ($data as $d){
	            $info[] = $d['resource_uuid'];
	        }
	    }
	    
	    return $info;
	}
	
	
	/**
	 * 获取租户下面虚拟机统计
	 * @return string
	 */
	public function getTenantVmTypeList(){
	    $resourceHandler = Xphp::instance('ResourceHandler');
	    $userHandler = Xphp::instance('UsersHandler');
	    $tenantHandler = Xphp::instance('TenantHandler');
	    $tenantMangerFlag = $userHandler->pCheckTenantManager();
	    $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
	    $userListDes = implode("','", $userList);
	    //租户直属管理员直接获取租户内所有虚拟机信息
	    if($tenantMangerFlag){
	        $resourceUUIDList = $resourceHandler->pGetTenantAllResource($_SESSION['tenantuuid'], Xphp::$_config['RESOURCE_TYPE']['VM']);
	    }else{
	        $resourceUUIDList = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['VM']);
	    }
	    
	    
	    //组合资源uuid方便查询
	    $vmuuidStr = $resourceHandler->groupResourceUUIDToString($resourceUUIDList, "vm_uuid");
	    $vcenterStr = $resourceHandler->groupResourceUUIDToString($resourceUUIDList, "vcenter_uuid");
	    $sql = "select distinct vv.hypervisor_type from vm_tree vt left join vm_vcenter vv
        on vt.vcenter_uuid = vv.vcenter_uuid where vt.display_mode = ? and vt.type = ? ";
	    $sqlParams = array(Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER'], Xphp::$_config['VM_TREE_TYPE']['VM']);
        if(!empty($vmuuidStr) && !empty($vcenterStr)){
            $sql .= "and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
            $sqlCount .= "and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
        }else{
            $sql .= "and vt.uuid in ('') and vt.vcenter_uuid in ('')";
            $sqlCount .= "and vt.uuid in ('') and vt.vcenter_uuid in ('')";
        }
        $sql .= " order by vv.hypervisor_type asc";
        $data = $this->dbSelect($sql, $sqlParams);
	    $utils = Xphp::instance("Utils");
	    $records = array();
	    $id = 0;
	    foreach ($data as $d){
	        $id++;
	        $vmNumList = $this->getVmNumList(intval($d['hypervisor_type']), $vmuuidStr, $vcenterStr, $userListDes, $tenantMangerFlag);
	        $totalNum = intval($vmNumList['total_vm_num']);
	        $protectNum = intval($vmNumList['protected_vm_num']);
	        if($protectNum > $totalNum){
	            $protectNum = $totalNum;
	        }
	        $records[] = array(
	            'num' => $id,
	            'hypervisor' => Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])],
	            'total_vm_num' => $totalNum,
	            'protected_vm_num' => $protectNum,
	        );
	    }
	    
	    return  json_encode($records);
	    
	}
	
	/**
	 * 获取租户内虚拟机分布饼图
	 * @return string
	 */
	public function getTenantVmPie(){
	    $resourceHandler = Xphp::instance('ResourceHandler');
	    $userHandler = Xphp::instance('UsersHandler');
	    $tenantMangerFlag = $userHandler->pCheckTenantManager();
	    //租户直属管理员直接获取租户内所有虚拟机信息
	    if($tenantMangerFlag){
	        $resourceUUIDList = $resourceHandler->pGetTenantAllResource($_SESSION['tenantuuid'], Xphp::$_config['RESOURCE_TYPE']['VM']);
	    }else{
	        $resourceUUIDList = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['VM']);
	    }
	    //组合资源uuid方便查询
	    $vmuuidStr = $resourceHandler->groupResourceUUIDToString($resourceUUIDList, "vm_uuid");
	    $vcenterStr = $resourceHandler->groupResourceUUIDToString($resourceUUIDList, "vcenter_uuid");
	    $sql = "select distinct vv.hypervisor_type from vm_vcenter vv, vm_tree vt where vv.vcenter_uuid = vt.vcenter_uuid ";
	    if(!empty($vmuuidStr) && !empty($vcenterStr)){
	        $sql .= " and vt.uuid in ($vmuuidStr) and vt.vcenter_uuid in ($vcenterStr)";
	    }else{
	        $sql .= " and vt.uuid in ('') and vt.vcenter_uuid in ('')";
	    }
	    $sql .= ' order by vv.hypervisor_type asc';
	    $sqlParams = array();
	    $data = $this->dbSelect($sql, $sqlParams);
	    $info = array();
	    $totalSql = "select count(dir_path) as all_total_vm_num from vm_tree  where type = ? and display_mode =? ";
	    if(!empty($vmuuidStr) && !empty($vcenterStr)){
	        $totalSql .= " and uuid in ($vmuuidStr) and vcenter_uuid in ($vcenterStr)";
	    }else{
	        $totalSql .= " and uuid in ('') and vcenter_uuid in ('')";
	    }
	    $dataTotal = $this->dbSelect($totalSql, array(7, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']));
	    foreach ($data as $d){
	        $vmNumList = $this->getVmNumList(intval($d['hypervisor_type']), $vmuuidStr, $vcenterStr);
	        $hypervisor = intval($d['hypervisor_type']);
	        $info[] = array(
	            'hypervisor' => Xphp::$_config['VMHYPERVISORDES'][$hypervisor],
	            'total_vm_num' => $vmNumList['total_vm_num'],
	            'protected_vm_num' => $vmNumList['protected_vm_num'],
	            'color' => $this->getHypervisorColor($hypervisor),
	            'all_total_vm_num' => intval($dataTotal[0]['all_total_vm_num'])
	        );
	    }
	    return json_encode($info);
	}
    /**
     * 获取每个虚拟化中心的备份统计信息
     * @param string $vcenteruuid  虚拟化中心唯一标识
     * @return number[]|fetchAll()[]
     */
    public function getVcenterBackupInfo($vcenteruuid){
        //获取受保护虚拟机个数和备份任务个数
        $sql = "select count(distinct vml.machine_id) as protect_vm, count(distinct vml.task_uuid) as task_num from vm_machine_list vml, bd_task bt where vml.vcenter_uuid = ? and bt.task_type = ? ";
        $data1 = $this->dbSelect($sql, array($vcenteruuid, Xphp::$_config['TASKTYPE']['BACKUP']));
        //获取备份数据统计
        $sql = "select bbt.timepoint, sum(bbt.total_size) as total_size, sum(bbt.write_size) as real_size, count(bbt.timepoint_uuid) as timepoint_count from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.vcenter_uuid = ? order by bbt.timepoint desc";
        $data2 = $this->dbSelect($sql, array($vcenteruuid));
        $info = array(
            'protect_vm' => intval($data1[0]['protect_vm']),
            'task_num' => intval($data1[0]['task_num']),
            'timepoint_count' => intval($data2[0]['timepoint_count']),
            'total_size' => intval($data2[0]['total_size']),
            'real_size' => intval($data2[0]['real_size']),
            'timepoint' => $data2[0]['timepoint'],
        );

        return $info;
    }

    /**
     * 获取每个租户的备份统计信息
     * @param string $tenantuuid 租户vm_tree.uuid
     * @return array
     */
    public function getTenantBackupInfo($tenantuuid){
        //获取受保护虚拟机个数和备份任务个数
        $sql = "select count(distinct vml.machine_id) as protect_vm, count(distinct vml.task_uuid) as task_num 
                from vm_machine_list vml, bd_task bt, vm_tree vt where vml.vm_uuid = vt.uuid and vt.parent_uuid = ? and bt.task_type = ? ";
        $data1 = $this->dbSelect($sql, array($tenantuuid, Xphp::$_config['TASKTYPE']['BACKUP']));
        //获取备份数据统计
        $sql = "select bbt.timepoint, sum(bbt.total_size) as total_size, sum(bbt.write_size) as real_size, count(bbt.timepoint_uuid) as timepoint_count 
                from bd_backup_timepoint bbt, vm_backup_timepoint vbt, vm_tree vt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.vm_uuid = vt.uuid 
                and vt.parent_uuid = ? order by bbt.timepoint desc";
        $data2 = $this->dbSelect($sql, array($tenantuuid));
        $info = array(
            'protect_vm' => intval($data1[0]['protect_vm']),
            'task_num' => intval($data1[0]['task_num']),
            'timepoint_count' => intval($data2[0]['timepoint_count']),
            'total_size' => intval($data2[0]['total_size']),
            'real_size' => intval($data2[0]['real_size']),
            'timepoint' => $data2[0]['timepoint'],
        );

        return $info;
    }
    /**
     * 获取任务报表的任务状态
     * @param int $status
     * @return string
     */
    private function getTaskReportStatusDes(int $status): string
    {
        switch ($status) {
            case 1:
            case 10://等待运行 蓝色
                return Xphp::$_lang['WEB_PLATFORM_DES_WAITING'];
            case 17:
            case 13:
                return Xphp::$_lang['UI_HOMEPAGE_STATUS_RUNNING'];
            case 2:
            case 9:
            case 12:
            case 14:
            case 15:
                return Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'];
            case 3:
            case 4: //灰色
                return Xphp::$_lang['WEB_PLATFORM_DES_STOP'];
            case 5: //灰色
            case 11: //灰色
            case 16: //灰色
                return Xphp::$_lang['WEB_PLATFORM_DES_STOPPING'];
            case 0: //失败
            case 6: //失败
            case 7: //失败
            case 8: //失败
                return Xphp::$_lang['WEB_PLATFORM_DES_ERROR'];
        }
        return Xphp::$_config['NULLSPACE'];
    }

    /**
     * @param array $params   请求参数
     * @param bool  $isExport 是否为导出操作
     * @return array
     */
    private function queryTaskReport(array $params, bool $isExport = false): array
    {
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $allTaskTypeDes = $ptDes['TASKTYPEDES'];
        $sortFields = [
            'task_name' => 'bt.task_name',
            'module_type' =>'bt.module_type',
            'task_type_des' =>'bt.task_type',
            'create_time' =>'bt.create_time',
            'user_name' =>'bu.user_name',
            'task_status' =>'bt.task_status',
            'host_name' =>'bn.host_name',
            'storage_nickname' =>'br.storage_nickname',
            'next_start_time' =>'next_start_time',
            'starttime' => 'starttime',
        ];
        $name = $params['search'];
        $job_type = $params['job_type'];
        $module_type = $params['module_type'];
        $job_status = $params['job_status'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'bt.create_time';
        $order = $params['order'] ?: 'desc';

        $sql = "select bt.task_name, bt.module_type, bt.task_type, bt.create_time,bt.sub_module_type,
                    bu.user_name,bt.task_status,bn.host_name, br.storage_nickname,
                    UNIX_TIMESTAMP(bt.create_time) AS starttime,
                    UNIX_TIMESTAMP(bs.next_start_time) AS next_start_time
                from bd_task bt
                    INNER JOIN bd_node bn ON bt.node_uuid=bn.node_uuid
                    INNER JOIN bd_user bu ON bt.user_uuid=bu.user_uuid
                    LEFT JOIN bd_storage_resource br ON bt.storage_uuid =br.storage_uuid
                    LEFT  JOIN bd_strategy bs ON bt.strategy_id =bs.strategy_id WHERE 1=1";
        $sqlCount = "select count(bt.task_uuid) as total
                     from bd_task bt
                    INNER JOIN bd_node bn ON bt.node_uuid=bn.node_uuid
                    INNER JOIN bd_user bu ON bt.user_uuid=bu.user_uuid
                    LEFT JOIN bd_storage_resource br ON bt.storage_uuid =br.storage_uuid
                    LEFT  JOIN bd_strategy bs ON bt.strategy_id =bs.strategy_id WHERE 1=1";

        $sqlParams = array();
        $sqlCountParams = array();
        // 按名字搜索
        if($this->checkEmpty($name)){
            $sql .= " and bt.task_name like ? ";
            $sqlCount .= " and bt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$name.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$name.'%'));
        }
        // 模块类型筛选
        if(!empty($module_type)){
            $sql .= " and bt.module_type in ($module_type) ";
            $sqlCount .= " and bt.module_type in ($module_type)";
        }

        // 任务类型筛选
        if(!empty($job_type)){
            $sql .= " and bt.task_type in ($job_type) ";
            $sqlCount .= " and bt.task_type in ($job_type) ";
        }

        if(!empty($job_status)){
            $sql .= " and bt.task_status in ($job_status) ";
            $sqlCount .= " and bt.task_status in ($job_status) ";
        }

        // 将结果转换为JSON格式并返回给前端
        $sql .= " ORDER BY $sort $order ";
        if (!$isExport) {  // 导出全部不需要分页
            $sql .= " LIMIT $offset, $limit ";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $jobHandler = Xphp::instance('JobHandler');
        $rows = array_map(function ($row) use($allTaskTypeDes, $jobHandler) {
            return [
                'task_name' => $row['task_name'],
                'module_type' => $row['module_type'],
                'task_type' => $row['task_type'],
                'task_type_des' => $allTaskTypeDes[$row['task_type']],
                'create_time' => $row['create_time'],
                'user_name' => $row['user_name'],
                'task_status' => $row['task_status'],
                'task_status_des' => $this->getTaskReportStatusDes((int) $row['task_status']),
                'host_name' => $row['host_name'],
                'sub_module_type_value' => $row['sub_module_type'],
                'storage_nickname' => $row['storage_nickname'],
                'nextstarttime' => $jobHandler->getNextStartTime($row['next_start_time'], $row['task_status']),
                'intervalTime' => $jobHandler->getTimeInterval($row['starttime'], $row['task_status']),
            ];
        }, $data);
        return ['total' => $count[0]['total'], 'rows' => $rows];
    }

    /**
     * 获取任务报表数据
     * @param $params
     * @return string
     */
    public function getTaskReport($params): string
    {
        return json_encode($this->queryTaskReport($params));
    }

    /**
     * 导出任务报表
     * @param $params
     * @return void
     */
    public function exportTaskReport($params)
    {
        $reportData = $this->queryTaskReport($params, true)['rows'];
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $allModuleTypeDes = $ptDes['MODULE_TYPE_DES'];
        $title = Xphp::$_lang['UI_TASK_REPORT'];
        $header = [
            'task_name' => Xphp::$_lang['UI_TASK_REPORT_TASK_NAME'],
            'module_type_des' => Xphp::$_lang['UI_TASK_REPORT_MODULE_TYPE'],
            'task_type_des' => Xphp::$_lang['UI_TASK_REPORT_TASK_TYPE'],
            'create_time' => Xphp::$_lang['UI_TASK_REPORT_CREATE_TIME'],
            'user_name' => Xphp::$_lang['UI_TASK_REPORT_USER_NAME'],
            'task_status_des' => Xphp::$_lang['UI_TASK_REPORT_TASK_STATUS'],
            'node_name' => Xphp::$_lang['UI_TASK_REPORT_NODE_NAME'],
            'storage_name' => Xphp::$_lang['UI_TASK_REPORT_STORAGE_NAME'],
            'next_start_time' => Xphp::$_lang['UI_TASK_REPORT_NEXT_START_TIME'],
            'interval_time' => Xphp::$_lang['UI_TASK_REPORT_INTERVAL_TIME'],
            'task_strategy' => Xphp::$_lang['UI_TASK_REPORT_TASK_STRATEGY'],
        ];
        $reportData = array_map(function ($row) use ($allModuleTypeDes) {
            $nullSpace = Xphp::$_config['NULLSPACE'];
            $timeSpace = Xphp::$_config['TIMESPACE'];
            return [
                'task_name' => $row['task_name'] ?: $nullSpace,
                'module_type_des' => $allModuleTypeDes[$row['module_type']] ?? $nullSpace,
                'task_type_des' => $row['task_type_des'] ?: $nullSpace,
                'create_time' => $row['create_time'] ?: $timeSpace,
                'user_name' => $row['user_name'] ?: $nullSpace,
                'task_status_des' => $row['task_status_des'] ?: $nullSpace,
                'node_name' => $row['host_name'] ?: $nullSpace,
                'storage_name' => $row['storage_nickname'] ?: $nullSpace,
                'next_start_time' => $row['nextstarttime'] ?: $timeSpace,
                'interval_time' => $row['intervalTime'] ?: $timeSpace,
                'task_strategy' => $nullSpace,
            ];
        }, $reportData);
        $relation = [
            'task_name' => ['col_name' => 'A', 'width' => 30],
            'module_type_des' => ['col_name' => 'B', 'width' => 15],
            'task_type_des' => ['col_name' => 'C', 'width' => 20],
            'create_time' => ['col_name' => 'D', 'width' => 20],
            'user_name' => ['col_name' => 'E', 'width' => 15],
            'task_status_des' => ['col_name' => 'F', 'width' => 10],
            'node_name' => ['col_name' => 'G', 'width' => 25],
            'storage_name' => ['col_name' => 'H', 'width' => 20],
            'next_start_time' => ['col_name' => 'I', 'width' => 20],
            'interval_time' => ['col_name' => 'J', 'width' => 20],
            'task_strategy' => ['col_name' => 'K', 'width' => 15],
        ];
        $baseExcel = require_once ROOT_PATH . 'tools/BaseExcel.class.php';
        $baseExcel->export($title, $header, $reportData, $relation);
    }

    /**
     * @param array $params   请求参数
     * @param bool  $isExport 是否为导出操作
     * @return array
     */
    private function queryHistoryReport(array $params, bool $isExport = false): array
    {
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $allTaskTypeDes = $ptDes['TASKTYPEDES'];
        $sortFields = [
            'task_name' => 'bht.task_name',
            'module_type' =>'bht.module_type',
            'submodule_type' =>'bht.submodule_type',
            'task_type_des' =>'bht.task_type',
            'user_name' =>'bht.user_name',
            'details' =>'bht.details',
            'start_time' =>'bht.start_time',
            'finish_time' =>'bht.finish_time',
            'total_object_size' =>'bht.total_object_size',
            'total_object_completed_size' =>'bht.total_object_completed_size',
            'total_object_transport_size' =>'bht.total_object_transport_size',
            'total_object_write_size' => 'bht.total_object_write_size',
            'error_code' =>'bht.error_code',
        ];
        $search = $params['search'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $job_type = $params['job_type'];
        $module_type = $params['module_type'];
        $job_status = $params['job_status'];
        $sort = $sortFields[$params['sort']] ?? 'bht.start_time';
        $order = $params['order'] ?: 'desc';
        $utils = Xphp::instance('Utils');

        $sql = "select  bht.task_name, bht.module_type, bht.submodule_type, bht.task_type,bht.user_name,bht.details,bht.start_time,bht.finish_time,bht.total_object_size,
                      bht.total_object_completed_size, bht.total_object_transport_size,bht.total_object_write_size,bht.error_code
                from bd_history_task bht WHERE 1=1 ";
        $sqlCount = "select count(*) as total from bd_history_task bht WHERE 1=1 ";

        $sqlParams = array();
        $sqlCountParams = array();
        if($this->checkEmpty($search)){
            $sql .= " and bht.task_name like ? ";
            $sqlCount .= " and bht.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$search.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$search.'%'));
        }
        //模块类型筛选
        if(!empty($module_type)){
            $sql .= " and bht.module_type in ($module_type) ";
            $sqlCount .= " and bht.module_type in ($module_type)";
        }

        //任务类型筛选

        if(!empty($job_type)){
            $sql .= " and bht.task_type in ($job_type) ";
            $sqlCount .= " and bht.task_type in ($job_type) ";
        }

        if(!empty($job_status)){
            $job_status = explode("," , $job_status);
            foreach ( $job_status as &$d){
                if($d == 17){
                    $d = 0;
                } elseif ($d == 7) {
                    $d = 47;
                } elseif ($d == 4) {
                    $d = 49;
                }
            }
            unset($d);
            if (in_array(8, $job_status)) {  // 查询包含错误
                $otherStatus = [0, 47, 49];
                $selectStatus = array_diff($otherStatus, $job_status);
                $selectStatus = implode(', ', $selectStatus);
                if ($selectStatus) {
                    $sql .= " and bht.error_code not in ($selectStatus) ";
                    $sqlCount .= " and bht.error_code not in ($selectStatus) ";
                }
            } elseif (in_array(1, $job_status)) {
                $sql .= " and 1<>1 ";
                $sqlCount .= " and 1<>1 ";
            } else {
                $job_status = implode("," , $job_status);
                $sql .= " and bht.error_code in ($job_status) ";
                $sqlCount .= " and bht.error_code in ($job_status) ";
            }
        }

        $sql .= " ORDER BY $sort $order ";
        if (!$isExport) {
            $sql .= " LIMIT $offset, $limit ";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $jobHandler = Xphp::instance('JobHandler');
        $rows = array_map(function ($row) use ($utils, $allTaskTypeDes, $jobHandler) {
            $agentIP = '--';
            $hostName = '--';
            $agentName = '--';
            $sub_module_type = $row['submodule_type'];
            if ($row['details']) {
                $detail = json_decode($row['details'], true);
                if ($detail) {
                    if ($detail[0]['agent_ip']) {  // 源主机/IP
                        $agentIP = $detail[0]['agent_ip'];
                    }
                    if ($detail[0]['hostname']) {  // 备份节点
                        $hostName = $detail[0]['hostname'];
                    }
                    if ($detail[0]['vol_display_name']) {  // 备份存储
                        $agentName = $detail[0]['agent_name'];
                    }
                }
            }
            if($agentName == '--' && $agentName == '--' && $agentIP== '--') {
                $hostInfo = '--';
            }
            elseif ($agentName!= '--' && $agentName != $agentIP) {
                $hostInfo =  $agentName ? $agentName."(". $agentIP .")" : $hostName."(". $agentIP .")";
            }elseif($agentName == '--' && $agentName == '--' && $agentIP!= '--'){
                $hostInfo = $agentIP;
            }else{
                $hostInfo = $hostName."(".$agentIP .")";
            }
            return [
                'task_name' => $row['task_name'],
                'module_type' => $row['module_type'],
                'sub_module_type_value' => $this->getSubModuleType($sub_module_type),
                'task_type_des' => $allTaskTypeDes[$row['task_type']],
                'user_name' => $row['user_name'],
                'host_name' => $hostName,
                'agent_ip' => $hostInfo,
                'agent_name' => $agentName,
                'start_time' => $row['start_time'],
                'finish_time' => $row['finish_time'],
                'total_object_size' => $utils->calSize($row['total_object_size'],true),
                'total_object_completed_size' => $utils->calSize($row['total_object_completed_size'],true),
                'total_object_transport_size' => $utils->calSize($row['total_object_transport_size'],true),
                'total_object_write_size' => $utils->calSize($row['total_object_write_size'],true),
                'error_code'=> $jobHandler->getHistoryJobResultDes($row['error_code']),
            ];
        }, $data);
        return ['total' => $count[0]['total'], 'rows' => $rows];
    }

    /**
     * 获取虚拟机模块的子模块类型:1虚拟机，2私有云，3公有云
     * @param int $subModuleType
     * @return int
     */
    private function getSubModuleType(int $subModuleType): int
    {
        if (in_array($subModuleType, Xphp::$_config['VMHYPERVISORGROUP']['openstack'])) {
            return 2;
        }
        if (in_array($subModuleType, Xphp::$_config['VMHYPERVISORGROUP']['publiccloud'])) {
            return 3;
        }
        return 1;
    }

    /**
     * 获取历史报表
     * @param $params
     * @return string
     */
    public function getHistoryReport($params): string
    {
        return json_encode($this->queryHistoryReport($params));
    }

    /**
     * 导出日志报表
     * @param $params
     * @return void
     */
    public function exportHistoryReport($params)
    {
        $reportData = $this->queryHistoryReport($params, true)['rows'];
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $allModuleTypeDes = $ptDes['MODULE_TYPE_DES'];
        $title = Xphp::$_lang['UI_HISTORY_TASK_REPORT'];
        $header = [
            'task_name' => Xphp::$_lang['UI_TASK_REPORT_TASK_NAME'],
            'module_type_des' => Xphp::$_lang['UI_TASK_REPORT_MODULE_TYPE'],
            'task_type_des' => Xphp::$_lang['UI_TASK_REPORT_TASK_TYPE'],
            'user_name' => Xphp::$_lang['UI_TASK_REPORT_USER_NAME'],
            'hostname' => Xphp::$_lang['UI_HISTORY_TASK_REPORT_HOSTNAME'],
            'node_name' => Xphp::$_lang['UI_TASK_REPORT_NODE_NAME'],
            'storage_name' => Xphp::$_lang['UI_TASK_REPORT_STORAGE_NAME'],
            'start_time' => Xphp::$_lang['UI_HISTORY_TASK_REPORT_START_TIME'],
            'end_time' => Xphp::$_lang['UI_HISTORY_TASK_REPORT_END_TIME'],
            'total_object_size' => Xphp::$_lang['UI_HISTORY_TASK_REPORT_TOTAL_SIZE'],
            'total_object_completed_size' => Xphp::$_lang['UI_HISTORY_TASK_REPORT_COMPLETED_SIZE'],
            'total_object_transport_size' => Xphp::$_lang['UI_HISTORY_TASK_REPORT_TRANSPORT_SIZE'],
            'total_object_write_size' => Xphp::$_lang['UI_HISTORY_TASK_REPORT_WRITE_SIZE'],
            'status' => Xphp::$_lang['UI_HISTORY_TASK_REPORT_STATUS'],
        ];
        $reportData = array_map(function ($row) use ($allModuleTypeDes) {
            $nullSpace = Xphp::$_config['NULLSPACE'];
            $timeSpace = Xphp::$_config['TIMESPACE'];
            return [
                'task_name' => $row['task_name'] ?: $nullSpace,
                'module_type_des' => $allModuleTypeDes[$row['module_type']] ?? $nullSpace,
                'task_type_des' => $row['task_type_des'] ?: $nullSpace,
                'user_name' => $row['user_name'] ?: $nullSpace,
                'hostname' => $row['details'] ?: $nullSpace,
                'node_name' => $row['detailshome'] ?: $nullSpace,
                'storage_name' => $row['detailsnode'] ?: $nullSpace,
                'start_time' => $row['start_time'] ?: $timeSpace,
                'end_time' => $row['finish_time'] ?: $timeSpace,
                'total_object_size' => $row['total_object_size'] ?: $nullSpace,
                'total_object_completed_size' => $row['total_object_completed_size'] ?: $nullSpace,
                'total_object_transport_size' => $row['total_object_transport_size'] ?: $nullSpace,
                'total_object_write_size' => $row['total_object_write_size'] ?: $nullSpace,
                'status' => $row['error_code'] ?: $nullSpace,
            ];
        }, $reportData);
        $relation = [
            'task_name' => ['col_name' => 'A', 'width' => 30],
            'module_type_des' => ['col_name' => 'B', 'width' => 15],
            'task_type_des' => ['col_name' => 'C', 'width' => 20],
            'user_name' => ['col_name' => 'D', 'width' => 15],
            'hostname' => ['col_name' => 'E', 'width' => 30],
            'node_name' => ['col_name' => 'F', 'width' => 20],
            'storage_name' => ['col_name' => 'G', 'width' => 20],
            'start_time' => ['col_name' => 'H', 'width' => 20],
            'end_time' => ['col_name' => 'I', 'width' => 20],
            'total_object_size' => ['col_name' => 'J', 'width' => 25],
            'total_object_completed_size' => ['col_name' => 'K', 'width' => 15],
            'total_object_transport_size' => ['col_name' => 'L', 'width' => 15],
            'total_object_write_size' => ['col_name' => 'M', 'width' => 15],
            'status' => ['col_name' => 'N', 'width' => 10],
        ];
        $baseExcel = require_once ROOT_PATH . 'tools/BaseExcel.class.php';
        $baseExcel->export($title, $header, $reportData, $relation);
    }

    /**
     * @param array $params   请求参数
     * @param bool  $isExport 是否为导出操作
     * @return array
     */
    private function queryClientReport(array $params, bool $isExport = false): array
    {
        $search = $params['search'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $utils = Xphp::instance('Utils');
        $sortFields = [
            'ip' => 'ba.ip',
            'online_flag' =>'ba.online_flag',
            'is_protect' => 'backup_status',
        ];
        $sort = $sortFields[$params['sort']] ?? 'ba.register_time';
        $order = $params['order'] ?: 'desc';
        $sql = "select  ba.hostname, ba.agent_name, ba.ip, ba.os_version,bu.user_name,ba.register_time, ba.authorization_module, ba.online_flag,task_name,IF(task_name IS NOT NULL, 1, 0 ) AS backup_status,ba.plugin_deploy_status,ba.agent_uuid
                from bd_agent ba
                INNER JOIN bd_user bu ON ba.user_uuid=bu.user_uuid
                LEFT JOIN 
                (SELECT group_concat(task_name separator ', ') as task_name, bl.agent_uuid FROM bd_task_agent_list bl INNER JOIN bd_task bt ON bl.task_uuid = bt.task_uuid GROUP BY bl.agent_uuid) 
                bl ON bl.agent_uuid=ba.agent_uuid
                where ba.agent_type not in (3, 4)  ";
        $sqlCount = "select count(*) as total 
                     from bd_agent ba 
                     INNER JOIN bd_user bu ON ba.user_uuid=bu.user_uuid
                     LEFT JOIN (SELECT group_concat(task_name separator ', ') as task_name, bl.agent_uuid FROM bd_task_agent_list bl INNER JOIN bd_task bt ON bl.task_uuid = bt.task_uuid GROUP BY bl.agent_uuid) 
                     bl ON bl.agent_uuid=ba.agent_uuid
                      where ba.agent_type not in (3, 4)  ";

        $sqlParams = array();
        $sqlCountParams = array();
        if($this->checkEmpty($search)){
            $sql .= " and ba.ip like ? ";
            $sqlCount .= " and ba.ip like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$search.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$search.'%'));
        }
        $clientHandler = Xphp::instance('ClientHandler'); //引入这个实例化
        $agentuuidArr = $clientHandler->getClientUuids(0);
        if (empty($agentuuidArr)) {
            // 表示没得数据
            $sql .= " and ba.user_uuid = ''";
            $sqlCount .= " and ba.user_uuid = ''";
        } else {
            if (is_array($agentuuidArr)) {
                $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
                $sql .= " and ba.agent_uuid in {$agentuuidArrStr} ";
                $sqlCount .= " and ba.agent_uuid in {$agentuuidArrStr} ";
            }
        }
        $sql .= " GROUP BY ba.agent_uuid ORDER BY $sort $order ";
        if (!$isExport) {
            $sql .= " LIMIT $offset, $limit";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $rows = array_map(function ($row) use($utils) {
            $nullSpace = Xphp::$_config['NULLSPACE'];
            $backupCurrentTime = $this->getCurrentTime($row['agent_uuid']); //最近备份时间
            $fullPoint = $this->getFullPoint($row['agent_uuid']); //完全备份点个数
            $incrementPoint = $this->getIncrementPoint($row['agent_uuid']); //增量备份点个数
            $diffrencePoint = $this->getDiffrencePoint($row['agent_uuid']); //差异备份点个数
            $allSplace = $this->getAllSplace($row['agent_uuid']);//获取备份点的总存储空间占用
            $isProtect = $this->getIsProtect($row['agent_uuid']); //受否受保护
            return [
                'hostname' => $row['hostname'] ?: $nullSpace,
                'agent_name' => $row['agent_name'] ?: $nullSpace,
                'ip' => $row['ip'],
                'os_version' => $row['os_version'],
                'register_time' => $row['register_time'],
                'owner' => $row['user_name'],
                'authorization_module' => $row['authorization_module'],
                'online_flag' => $row['online_flag'],
                'task_name' => $row['task_name'] ?: $nullSpace,
                'plugin_deploy_status' => $row['plugin_deploy_status'],
                'back_current_time' => $backupCurrentTime ?: $nullSpace,
                'full_point' => $fullPoint ?: $nullSpace,
                'increment_point' => $incrementPoint ?: $nullSpace,
                'diffrence_point' => $diffrencePoint ?: $nullSpace,
                'all_splace' => $utils->calSize($allSplace,true) ?: $nullSpace,
                'is_protect' => $row['backup_status'],

            ];
        }, $data);
        return ['total' => $count[0]['total'], 'rows' => $rows];
    }

    /**
     * 获取客户端最近备份时间
     * @param $nodeuuid string
     * @return string
     */
    public function getCurrentTime($nodeuuid)
    {
        $sql = "select timepoint  from bd_agent ba 
                left join bd_task_agent_list  bl ON bl.agent_uuid=ba.agent_uuid
                inner join bd_backup_timepoint  bbt on bbt.task_uuid = bl.task_uuid
                where bbt.task_type in ('1','24','28','32','35') and ba.agent_uuid = ?
                ORDER BY timepoint DESC LIMIT 1;";

        $data = $this->dbSelect($sql,[$nodeuuid]);
        return $data[0]['timepoint'];
    }

    /**
     * 获取客户端完全备份点个数
     * @param $nodeuuid
     * @return string
     */
    public function getFullPoint($nodeuuid) {
        $sql = "select count(*) as total from bd_agent ba 
                left join bd_task_agent_list  bl ON bl.agent_uuid=ba.agent_uuid
                inner join bd_backup_timepoint  bbt on bbt.task_uuid = bl.task_uuid
                where bbt.task_type in ('1','24','28','32','35') and backup_mode = '1' and ba.agent_uuid = ?";
        $data = $this->dbSelect($sql,  [$nodeuuid]);
        return $data[0]['total'];
    }

    /**
     * 获取客户端增量备份点个数
     * @param $nodeuuid
     * @return string
     */
    public function getIncrementPoint($nodeuuid) {
        $sql = "select count(*) as total from bd_agent ba 
                left join bd_task_agent_list  bl ON bl.agent_uuid=ba.agent_uuid
                inner join bd_backup_timepoint  bbt on bbt.task_uuid = bl.task_uuid
                where bbt.task_type in ('1','24','28','32','35') and backup_mode = '2' and ba.agent_uuid = ?";
        $data = $this->dbSelect($sql,  [$nodeuuid]);
        return $data[0]['total'];
    }

    /**
     * 获取客户端增量备份点个数
     * @param $nodeuuid
     * @return string
     */
    public function getDiffrencePoint($nodeuuid) {
        $sql = "select count(*) as total from bd_agent ba 
                left join bd_task_agent_list  bl ON bl.agent_uuid=ba.agent_uuid
                inner join bd_backup_timepoint  bbt on bbt.task_uuid = bl.task_uuid
                where bbt.task_type in ('1','24','28','32','35') and backup_mode = '3' and ba.agent_uuid = ?";
        $data = $this->dbSelect($sql,  [$nodeuuid]);
        return $data[0]['total'];
    }

    /**
     * 获取客户端备份总存储空间
     * @param $nodeuuid
     * @return string
     */
    public function getAllSplace($nodeuuid) {
        $sql = "select SUM(total_size) AS total  from bd_agent ba 
                left join bd_task_agent_list  bl ON bl.agent_uuid=ba.agent_uuid
                inner join bd_backup_timepoint  bbt on bbt.task_uuid = bl.task_uuid
                where bbt.task_type in ('1','24','28','32','35') and ba.agent_uuid = ?";
        $data = $this->dbSelect($sql,  [$nodeuuid]);
        return $data[0]['total'];
    }

    public function getIsProtect($nodeuuid) {
        $sql = "select IF( bbt.task_uuid IS NOT NULL, 1, 0 ) AS backup_status  from bd_agent ba 
                left join bd_task_agent_list  bl ON bl.agent_uuid=ba.agent_uuid
                inner join bd_backup_timepoint  bbt on bbt.task_uuid = bl.task_uuid
                where bbt.task_type in ('1','24','28','32','35') and ba.agent_uuid = ?";
        $data = $this->dbSelect($sql,  [$nodeuuid]);
        return $data[0]['backup_status'];
    }
    /**
     * 获取客户端报表
     * @param $params
     * @return string
     */
    public function getClientReport($params): string
    {
        return json_encode($this->queryClientReport($params));
    }

    /**
     * 导出客户端报表
     * @param $params
     * @return void
     */
    public function getExportClient($params)
    {
        $reportData = $this->queryClientReport($params, true)['rows'];
        $title = Xphp::$_lang['UI_CLIENT_REPORT'];
        $header = [
            'hostname' => Xphp::$_lang['UI_CLIENT_REPORT_HOSTNAME'],
            'ip' => Xphp::$_lang['UI_CLIENT_REPORT_IP'],
            'os' => Xphp::$_lang['UI_CLIENT_REPORT_OS'],
            'owner' => Xphp::$_lang['UI_CLIENT_REPORT_OWNER'],
            'create_time' => Xphp::$_lang['UI_CLIENT_REPORT_CREATE_TIME'],
            'auth_module' => Xphp::$_lang['UI_CLIENT_REPORT_AUTH_MODULE'],
            'config_task' => Xphp::$_lang['UI_CLIENT_REPORT_CONFIG_TASK'],
            'status' => Xphp::$_lang['UI_CLIENT_REPORT_STATUS'],
        ];
        $reportData = array_map(function ($row) {
            $nullSpace = Xphp::$_config['NULLSPACE'];
            $timeSpace = Xphp::$_config['TIMESPACE'];
            // 授权模块
            $authModule = json_decode($row['authorization_module'], true);
            $authModuleDes = [];
            if ($authModule['file']) {
                $authModuleDes[] = Xphp::$_lang['UI_PLATFORM_FILE'];
            }
            if ($authModule['os']) {
                $authModuleDes[] = Xphp::$_lang['UI_PUBLIC_OS'];
            }
            if ($authModule['database']) {
                $authModuleDes[] = Xphp::$_lang['UI_PLATFORM_DB'];
            }
            // 状态
            if ($row['online_flag'] == Xphp::$_config['FLAG']['SET']) {
                $status = Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'];
            } else {
                $status = Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'];
            }
            $allDeployStatus = Xphp::$_config['AGENT_DEPLOY_STATUS'];
            if ($allDeployStatus['DEPLOY_WAITING'] == $row['plugin_deploy_status']) {
                $status .= '(' . Xphp::$_lang['WEB_CLIENT_DEPLOYING'] . ')';
            } elseif ($allDeployStatus['DEPLOY_SUCCESS'] == $row['plugin_deploy_status']) {
                $status .= '(' . Xphp::$_lang['WEB_CLIENT_DEPLOY_SUCCESS'] . ')';
            } else {
                $status .= '(' . Xphp::$_lang['WEB_CLIENT_DEPLOY_FAILED'] . ')';
            }
            return [
                'hostname' => $row['hostname'] . '/' . $row['agent_name'],
                'ip' => $row['ip'] ?: $nullSpace,
                'os' => $row['os_version'] ?: $nullSpace,
                'owner' => $row['owner'] ?: $nullSpace,
                'create_time' => $row['register_time'] ?: $timeSpace,
                'auth_module' => implode(', ', $authModuleDes) ?: $nullSpace,
                'config_task' => $row['task_name'] ?: $nullSpace,
                'status' => $status ?: $nullSpace,
            ];
        }, $reportData);
        $relation = [
            'hostname' => ['col_name' => 'A', 'width' => 30],
            'ip' => ['col_name' => 'B', 'width' => 15],
            'os' => ['col_name' => 'C', 'width' => 25],
            'owner' => ['col_name' => 'D', 'width' => 15],
            'create_time' => ['col_name' => 'E', 'width' => 30],
            'auth_module' => ['col_name' => 'F', 'width' => 30],
            'config_task' => ['col_name' => 'G', 'width' => 25],
            'status' => ['col_name' => 'H', 'width' => 20],
        ];
        $baseExcel = require_once ROOT_PATH . 'tools/BaseExcel.class.php';
        $baseExcel->export($title, $header, $reportData, $relation);
    }

    /**
     * @param array $params   请求参数
     * @param bool  $isExport 是否为导出操作
     * @return array
     */
    private function queryBackupReport(array $params, bool $isExport = false): array
    {
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $allTaskTypeDes = $ptDes['TASKTYPEDES'];
        $allMode = $ptDes['BACKUP_MODE_DES'];
        $utils = Xphp::instance('Utils');
        $name = $params['search'];
        $sortFields = [
            'module_type' => 'bbt.module_type',
            'backup_mode' =>'bbt.backup_mode',
            'timepoint' => 'bbt.timepoint',
            'total_size' => 'bbt.total_size',
            'write_size' => 'bbt.write_size',

        ];
        $data_type = $params['data_type'];
        $module_type = $params['module_type'];
        $offset = ((int) $params['offset']) ?: 0;
        $limit = ((int) $params['limit']) ?: 10;
        $sort = $sortFields[$params['sort']] ?? 'bbt.timepoint';
        $order = $params['order'] ?: 'desc';

        $sql = "select  bbt.module_type, bbt.timepoint,bbt.total_size,bbt.write_size,bbt.backup_mode,
                COALESCE(bt.task_name,bbt.task_name) as task_name,bbt.task_type,bbt.remarks,bn.ip,bn.host_name,bsr.storage_nickname,bt.sub_module_type
                from bd_backup_timepoint bbt
                LEFT JOIN bd_task bt ON bt.task_uuid=bbt.task_uuid
                LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid 
                LEFT JOIN bd_node bn ON bsr.node_uuid = bn.node_uuid WHERE 1=1";

        $sqlCount = "select count(bbt.task_name) as total
                     from bd_backup_timepoint bbt
                     LEFT JOIN bd_task bt ON bt.task_uuid=bbt.task_uuid
                     LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid 
                     LEFT JOIN bd_node bn ON bsr.node_uuid = bn.node_uuid WHERE 1=1";


        $sqlParams = array();
        $sqlCountParams = array();
        //按名字搜索
        if($this->checkEmpty($name)){
            $sql .= " and bbt.task_name like ? ";
            $sqlCount .= " and bbt.task_name like ? ";
            $sqlParams = array_merge($sqlParams, array('%'.$name.'%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%'.$name.'%'));
        }
        if(!empty($module_type)){
            $sql .= " and bbt.module_type in ($module_type) ";
            $sqlCount .= " and bbt.module_type in ($module_type)";
        }

        //任务类型筛选

        if(!empty($data_type)){
            $sql .= " and bbt.backup_mode in ($data_type) ";
            $sqlCount .= " and bbt.backup_mode in ($data_type) ";
        }
        // 将结果转换为JSON格式并返回给前端
        $sql .= " ORDER BY $sort $order ";
        if (!$isExport) {
            $sql .= " LIMIT $offset, $limit ";
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $rows = array_map(function ($row) use($utils, $allTaskTypeDes, $allMode) {
            return [
                'timepoint' => $row['timepoint'],
                'module_type' => $row['module_type'],
                'task_type_des' => $allTaskTypeDes[$row['task_type']],
                'task_name' => $row['task_name']? $row['task_name'] : '--',
                'total_size' => $utils->calSize($row['total_size'],true),
                'write_size' => $utils->calSize($row['write_size'],true),
                'node_nickname' => $row['host_name'],
                'storage_nickname' => $row['storage_nickname'],
                'remarks' => $row['remarks'],
                'sub_module_type_value' => $row['sub_module_type'],
                'ip' =>$row['ip'],
                'backup_mode' =>$allMode[$row['backup_mode']],
            ];
        }, $data);
        return ['total' => $count[0]['total'], 'rows' => $rows];
    }

    /**
     * 获取备份数据报表
     * @param $params
     * @return string
     */
    public function getBackupReport($params): string
    {
        return json_encode($this->queryBackupReport($params));
    }

    /**
     * 导出备份数据报表报表
     * @param $params
     * @return void
     */
    public function exportBackupReport($params)
    {
        $reportData = $this->queryBackupReport($params, true)['rows'];
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $allModuleTypeDes = $ptDes['MODULE_TYPE_DES'];
        $title = Xphp::$_lang['UI_BACKUP_DATA_REPORT'];
        $header = [
            'time_point' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_TIME_POINT'],
            'module_type' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_MODULE_TYPE'],
            'backup_mode' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_BACKUP_MODE'],
            'task_name' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_TASK_NAME'],
            'data_size' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_DATA_SIZE'],
            'write_size' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_WRITE_SIZE'],
            'node_name' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_NODE_NAME'],
            'storage_name' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_STORAGE_NAME'],
            'remark' => Xphp::$_lang['UI_BACKUP_DATA_REPORT_REMARK'],
        ];
        $reportData = array_map(function ($row) use ($allModuleTypeDes) {
            $nullSpace = Xphp::$_config['NULLSPACE'];
            $timeSpace = Xphp::$_config['TIMESPACE'];
            return [
                'time_point' => $row['timepoint'] ?: $timeSpace,
                'module_type' => $allModuleTypeDes[$row['module_type']] ?? $nullSpace,
                'backup_mode' => $row['backup_mode'] ?: $nullSpace,
                'task_name' => $row['task_name'] ?: $nullSpace,
                'data_size' => $row['total_size'] ?: $nullSpace,
                'write_size' => $row['write_size'] ?: $nullSpace,
                'node_name' => $row['node_nickname'] ?: $nullSpace,
                'storage_name' => $row['storage_nickname'] ?: $nullSpace,
                'remark' => $row['remarks'] ?: $nullSpace,
            ];
        }, $reportData);
        $relation = [
            'time_point' => ['col_name' => 'A', 'width' => 30],
            'module_type' => ['col_name' => 'B', 'width' => 15],
            'backup_mode' => ['col_name' => 'C', 'width' => 15],
            'task_name' => ['col_name' => 'D', 'width' => 20],
            'data_size' => ['col_name' => 'E', 'width' => 15],
            'write_size' => ['col_name' => 'F', 'width' => 15],
            'node_name' => ['col_name' => 'G', 'width' => 30],
            'storage_name' => ['col_name' => 'H', 'width' => 30],
            'remark' => ['col_name' => 'I', 'width' => 15],
        ];
        $baseExcel = require_once ROOT_PATH . 'tools/BaseExcel.class.php';
        $baseExcel->export($title, $header, $reportData, $relation);
    }

}
?>