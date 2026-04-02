<?php
/******************************************* 
** 存储管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2016-05-25 下午14:51:32 
** @version      1.0.0 
** @copyright    Copyright 2015-2016 vinchin.com 
********************************************/
class StorageHandler extends OPHandler{
    private $opcodeHandler;
    
    function __construct(){
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('NodeOpcode');
    }
    
    /**
     * 得到存储列表信息
     * @param unknown $params
     */
    public function getStorageInfo($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'bsr.storage_nickname', 'bsr.storage_type', 'bn.node_nickname', 'bsr.status',
            'bsr.total_size', 'bsr.free_size',  'bsr.error_code'
        );
        
        $sql = "select bsr.storage_nickname, bsr.storage_uuid, bsr.storage_type, bsr.total_size, bsr.free_size, bsr.use_mode, 
                bsr.status, bsr.mount_flag, bsr.error_code, bsr.storage_config, 
                bsr.warning_flag, bsr.warning_type, bsr.warning_value, 
                bn.node_nickname, bn.host_name, bn.ip, bn.node_uuid  
                from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and 
                bsr.lan_free_flag = ? ";
        $sqlCount = "select count(bsr.storage_uuid) as total from bd_storage_resource bsr, bd_node bn 
                     where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? ";
        
        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], $start, $length);
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET']);
        if($accurateFlag){
        	$search = $params['search'];
        	$nickName = $search['nickName'];
        	$storageType = intval($search['storageType']);
        	$storageStatus = intval($search['storageStatus']);
        	$nodeuuid = $search['nodeValue'];
        
        	$sql .= " and (bsr.storage_type = ". $storageType ." or ". $storageType ." = '') and
        			 (bsr.status = ". $storageStatus ." or ". $storageStatus ." = '') and
        			 bsr.storage_nickname like '%". $nickName ."%' ";
        	$sqlCount .= " and (bsr.storage_type = ". $storageType ." or ". $storageType ." = '') and
        			 (bsr.status = ". $storageStatus ." or ". $storageStatus ." = '') and
        			 bsr.storage_nickname like '%". $nickName ."%' ";
        	if($nodeuuid){
        		$sql .= " and (bsr.node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '')";
        		$sqlCount .= " and (bsr.node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '')";
        	}
        }
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);
        
        $utils = Xphp::instance("Utils");
        
        $records = array();
        $records["data"] = array();
        $nodeHandler = Xphp::instance('NodeHandler');
        foreach ($data as $d){
            $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
            
            if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $d['warning_type']){
                //如果是按照大小来告警
                $warningValue = $utils->calSize($d['warning_value']);
            }else{
                $warningValue = $d['warning_value'] . "%";
            }
            
           	$nodeDes =  $nodeHandler->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']) . "(" . $d['ip'] . ")";
            $nodeStatus = $nodeAllStatus['flag'];
            if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
            	$storageConfig = json_decode($d['storage_config'], true);
            	$nodeDes = $storageConfig['remote_name'].'('.$storageConfig['remote_ip'].')';
            	$nodeStatus = $utils->parseFlagToBool(intval($storageConfig['remote_status']));
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['storage_uuid'] .'">',
                $d['storage_nickname'],
                $this->getStorageTypeDes($d['storage_type']),
               	$nodeDes,
               	$nodeStatus,
                $utils->calSize($d['total_size']),
                $utils->calSize($d['free_size']),
                $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
            	$this->getUsemodeDes(intval($d['use_mode'])),
                array(
                    'storagedes' => $this->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                    'nodedes' => $nodeHandler->getOffLineModuleDes($nodeAllStatus['module']),    //不在线的模块进程
                    'config' => $this->getStorageConfig($d['storage_type'], $d['storage_config'], $d['storage_uuid']),
                    'warning' => array(
                        'flag' => $utils->parseFlagToBool($d['warning_flag']),
                        'type' => $d['warning_type'],
                        'value' => $warningValue,
                    )
                ),
            );
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到lan-free存储信息
     * @param unknown $params
     */
    public function getLanfreeStorageInfo($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'bsr.storage_nickname', 'bsr.storage_type', 'bn.node_nickname', 'bsr.status',
            'bsr.total_size', 'bsr.free_size',  'bsr.error_code'
        );
        
        $sql = "select bsr.storage_nickname, bsr.storage_uuid, bsr.storage_type, bsr.total_size, bsr.free_size,
                bsr.status, bsr.mount_flag, bsr.error_code, bsr.storage_config, bn.node_nickname, bn.host_name, bn.ip, bn.node_uuid
                from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and 
                bsr.lan_free_flag = ? ";
        $sqlCount = "select count(bsr.storage_uuid) as total from bd_storage_resource bsr, bd_node bn
                     where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? ";
        
        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array(Xphp::$_config['FLAG']['SET'], $start, $length);
        $sqlCountParams = array(Xphp::$_config['FLAG']['SET']);
        if($accurateFlag){
        	$search = $params['search'];
        	$nickName = $search['nickName'];
        	$storageType = intval($search['storageType']);
        	$storageStatus = intval($search['storageStatus']);
        	$nodeuuid = $search['nodeValue'];
        	 
        	$sql .= " and (bsr.storage_type = ". $storageType ." or ". $storageType ." = '') and
        			 (bsr.status = ". $storageStatus ." or ". $storageStatus ." = '') and
        			 bsr.storage_nickname like '%". $nickName ."%' ";
        	$sqlCount .= " and (bsr.storage_type = ". $storageType ." or ". $storageType ." = '') and
        			 (bsr.status = ". $storageStatus ." or ". $storageStatus ." = '') and
        			 bsr.storage_nickname like '%". $nickName ."%' ";
        	
        	if($nodeuuid){
        		$sql .= " and (bsr.node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '')";
        		$sqlCount .= " and (bsr.node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '')";
        	}
        }
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);
        
        $utils = Xphp::instance("Utils");
        
        $records = array();
        $records["data"] = array();
        $nodeHandler = Xphp::instance('NodeHandler');
        foreach ($data as $d){
            $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['storage_uuid'] .'">',
                $d['storage_nickname'],
                $this->getStorageTypeDes($d['storage_type']),
                $nodeHandler->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']) . "(" . $d['ip'] . ")",
                $nodeAllStatus['flag'],
                $utils->calSize($d['total_size']),
                $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                array(
                    'storagedes' => $this->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                    'nodedes' => $nodeHandler->getOffLineModuleDes($nodeAllStatus['module']),    //不在线的模块进程
                    'config' => $this->getStorageConfig($d['storage_type'], $d['storage_config'])
                ),
            );
        }
        
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到每一个存储的配置信息
     * @param int  $type    存储类型
     * @param json $config  存储配置详情
     */
    private function getStorageConfig($type, $config, $storageuuid){
        $type = intval($type);
        $config = json_decode($config, true);
        $details = array(
            'type' => $type,                    //存储类型
            'dev_path' => $config['pathname'],  //设备源(公用)
            'storageuuid'=> $storageuuid        //存储UUID
        );
        switch ($type){
            case Xphp::$_config['BD_STORAGE_TYPE']['DISK']:
                $details['disk_type'] = $this->getStorageDevDes($config);       //磁盘类型(磁盘)
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['LVM']:
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['PARTITION']:
                $ptDes = include APP_PATH . 'platform/PFDescription.php';
                $partitionType = intval($config['part_tran']);
                $details['partition_type'] = $ptDes['PARTITIONTYPE'][$partitionType];      //分区类型(分区)
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['FC']:
                $details['fc_type'] = $this->getStorageDevDes($config);      //fc类型(fc)
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['ISCSI']:
                $details['iscsi_type'] = $this->getStorageDevDes($config);      //iscsi类型(iscsi)
                $host = '';
                foreach ($config['iscsi_target_list'] as $iscsi_target){
                    $host .= $iscsi_target['iscsi_target'] . ",";
                }
                $details['iscsi_host'] = substr($host, 0, -1);          //iscsi服务器(iscsi)
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['NFS']:
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['CIFS']:
                $details['username'] = $config['username'];          //用户名
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']:
                $details['dev_path'] = $config['pathname'];          //异地系统IP
                $details['username'] = $config['username'];
                break;
            default:
                break;
        }
        
        return $details;
    }
    
    /**
     * 得到设备类型描述,适用于本地磁盘/iscsi/fc/
     * @param array $config
     */
    private function getStorageDevDes($config){
        $vender = trim($config['vendor']);
        $model = trim($config['model']);
        $des = $vender . " " . $model;
        $des = trim($des);
        return $des;
    }
    
    /**
     * 得到存储状态码
     * @param array $nodeAllStatus  节点所有程序状态
     * @param int $storageStatus    存储状态码
     * @param int $mountFlag        存储挂载标志
     * @return int
     */
    public function getStorageStatus($nodeAllStatus, $storageStatus, $mountFlag){
        //首先检查节点的状态
        if(!$nodeAllStatus['flag']){
            //节点已经有异常了,部分程序不在线
            return Xphp::$_config['STORAGE_STATUS']['OFFLINE'];
        }
        if($storageStatus == Xphp::$_config['STORAGE_STATUS']['ONLINE'] && 
            $mountFlag == Xphp::$_config['FLAG']['SET']){
            //存储在线 且挂载
            return Xphp::$_config['STORAGE_STATUS']['ONLINE'];
        }elseif ($storageStatus == Xphp::$_config['STORAGE_STATUS']['OFFLINE']){
            //存储离线
            return Xphp::$_config['STORAGE_STATUS']['OFFLINE'];
        }elseif ($storageStatus == Xphp::$_config['STORAGE_STATUS']['ONLINE'] && 
            $mountFlag == Xphp::$_config['FLAG']['UNSET']){
            //在线未挂载
            return Xphp::$_config['STORAGE_STATUS']['UNMOUNT'];
        }else{
            //其他所有状态(创建中,离线已挂载)统一为异常
            return Xphp::$_config['STORAGE_STATUS']['CREATING'];
        }
    }
    
    /**
     * 得到存储的错误码(这里返回的数据一种是0,一种是非0)
     * @param array $nodeAllStatus  节点所有程序状态
     * @param int $storageErrorCode    存储错误码
     * @return int
     */
    private function getStorageErrorCode($nodeAllStatus, $storageErrorCode){
        //首先检查节点的状态
        if(!$nodeAllStatus['flag']){
            //节点已经有异常了,部分程序不在线,返回一个非0的整数表示错误
            return 1;
        }
        return $storageErrorCode;
    }
    
    /**
     * 得到存储在线状态描述
     * @param unknown $status
     */
    private function getStorageStatusDes($nodeAllStatus, $storageStatus, $mountFlag){
        $status = $this->getStorageStatus($nodeAllStatus, $storageStatus, $mountFlag);
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $des = $ptDes['STORAGESTATUS'][$status];
        return $des;
    }
    
    /**
     * 得到存储使用状态描述
     * @param unknown $nodeAllStatus
     * @param unknown $errorCode
     */
    private function getStorageErrorStatusDes($nodeAllStatus, $errorCode){
        //首先检查节点的状态
        if(!$nodeAllStatus['flag']){
            //节点已经有异常了,部分程序不在线,返回一个非0的整数表示错误
            $errorCode = 1;
        }
        $utils = Xphp::instance("Utils");
        return $utils->getStatusDes($errorCode);
    }
    
    /**
     * 得到状态错误描述信息
     * @param unknown $errorCode
     * @return string
     */
    private function getErrorDetail($nodeAllStatus, $errorCode){
        //首先检查节点的状态
        if(!$nodeAllStatus['flag']){
            //节点已经有异常了,部分程序不在线,返回一个非0的整数表示错误
            $nodeHandler = Xphp::instance('NodeHandler');
            return $nodeHandler->getOffLineModuleDes($nodeAllStatus['module']);
        }
        if(0 == $errorCode){
            return '';
        }
        $utils = Xphp::instance("Utils");
        return $utils->getErrorDes($errorCode);
    }
    
    /**
     * 得到存储类型描述
     * @param unknown $storageType
     */
    public function getStorageTypeDes($storageType){
        $des = '';
        if(empty($storageType)) return $des;
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $des = $ptDes['STORAGETYPE'][$storageType];
        return $des;
    }
    
    /**
     * 得到存储的名称
     * @param unknown $params
     */
    public function getStorageName($params){
        $storageType = $params['type'];
        $this->paramsCheck($storageType);
        $typeDes = $this->getStorageTypeDes($storageType);
        for($i=1; $i<1000; $i++){
            $sql = "select storage_id from bd_storage_resource where storage_nickname = ? and lan_free_flag = ?";
            $data = $this->dbSelect($sql, array($typeDes . $i, Xphp::$_config['FLAG']['UNSET']));
            if(empty($data)){
                return $typeDes . $i;
            }
        }
        return $typeDes;
    }
    
    /**
     * 得到Lanfree的名称
     * @param unknown $params
     */
    public function getLanfreeName($params){
    	$storageType = $params['type'];
    	$this->paramsCheck($storageType);
    	$typeDes = $this->getStorageTypeDes($storageType);
    	for($i=1; $i<1000; $i++){
    		$sql = "select storage_id from bd_storage_resource where storage_nickname = ? and lan_free_flag = ?";
    		$data = $this->dbSelect($sql, array($typeDes . $i, Xphp::$_config['FLAG']['SET']));
    		if(empty($data)){
    			return $typeDes . $i;
    		}
    	}
    	return $typeDes;
    }
    
    /**
     * 根据存储UUID得到存储的名字
     * @param unknown $params
     * @return string
     */
    public function getStorageNameWithuuid($params){
        $storageuuid = $params['storageuuid'];
        $this->paramsCheck($storageuuid);
        $sql = "select storage_nickname, warning_flag, warning_type, warning_value, use_mode, storage_type from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $utils = Xphp::instance('Utils');
        $usemode = intval($data[0]['use_mode']);
        $backupCheck = 'uncheck';
        $copyCheck = 'uncheck';
        if($usemode == 1){
        	$backupCheck = 'check';
        	$copyCheck = 'uncheck';
        }else if($usemode == 2){
        	$backupCheck = 'uncheck';
        	$copyCheck = 'check';
        }else if($usemode == 3){
        	$backupCheck = 'check';
        	$copyCheck = 'check';
        }
        $info = array(
            'storageuuid' => $storageuuid,
            'storagename' => $data[0]['storage_nickname'],
            'warning_flag' => $utils->parseFlagToBool($data[0]['warning_flag']),
            'warning_type' => $data[0]['warning_type'],
        	'backupcheck' => $backupCheck,
        	'copycheck' => $copyCheck,
        	'storage_type' => $data[0]['storage_type']
        );
         
        //添加告警配置
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == intval($data[0]['warning_type'])){
            //如果是按照大小来告警
            $info['warning_value'] = $data[0]['warning_value'] / 1024 / 1024 / 1024;
        }else{
            $info['warning_value'] = $data[0]['warning_value'];
        }
        
        return json_encode($info);
    }
    
    /**
     * 修改存储名称
     * @param unknown $params
     */
    public function editStorageName($params){
        $storageuuid = $params['storageuuid'];
        $storagename = $params['storagename'];
        $usemode= $params['usemode'];
        $this->paramsCheck($storagename, $storageuuid);
        $utils = Xphp::instance('Utils');
        $warningFlag = $utils->parseBoolToFlag($params['warningSettings']['power']);
        $warningType = $params['warningSettings']['type'];
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == intval($warningType)){
            //如果是按照大小来告警
            $warningValue = $params['warningSettings']['value'] * 1024 * 1024 * 1024;
        }else{
            $warningValue = $params['warningSettings']['value'];
        }
        
        $sql = "select storage_nickname from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        
        $sql = "update bd_storage_resource set storage_nickname = ?, warning_flag = ?, warning_type = ?, 
                warning_value = ?, use_mode = ? where storage_uuid = ?";
        $result = $this->dbExec($sql, array($storagename, $warningFlag, $warningType, $warningValue, $usemode, $storageuuid));
        
        //插系统日志
        $oldName = $data[0]['storage_nickname'];
        $descriptionParam = array($oldName, $storagename);
        if($result){
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_EDIT', $descriptionParam);
        }else{
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_EDIT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
        }
        return $this->muOpResult($result, Xphp::$_lang['WEB_STORAGE_EDIT_INFO']);
    }
    
    /**
     * 得到iscsi名称
     * @param unknown $params
     */
    public function getIscsiName($params){
        $nodeuuid = $params['nodeuuid'];
        $msg = array();
        $opName = "NODE_SR_OP_GET_ISCSI_INIT_IQN";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        if(!$mbResult['result']){
            $opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);
            return $this->muOpResult(false, $opcodeDes, '', '', $mbResult['errorCode']);
        }
        $info = array();
        $info[] = $mbResult['msg']['initiator_name'];
        return json_encode($info);
    }
    
    /**
     * 获取IQN对应的LUN信息
     * @param unknown $params
     */
    public function getIscsiLunTable($params){
        $nodeuuid = $params['nodeuuid'];
        $serverList = $params['serverlist'];
        $lanfree = $params['lanfree'];
        $this->paramsCheck($nodeuuid, $serverList);
        
        $iscsiTargetList = array();
        foreach ($serverList as $server){
            $iscsiTargetList[] = array(
                'iscsi_target' => $server['ip'] . ":" . $server['port']
            );
        }
        
        $utis = Xphp::instance('Utils');
        $msg = array(
            'iscsi_target_list' => $iscsiTargetList,
            'username' => '',
            'password' => '',
            "lan_free_flag" => $utis->parseBoolToFlag($lanfree)
        );
        $opName = "NODE_SR_OP_SCAN_ISCSI";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        
        $opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);
        $info = array();
        if($mbResult['result']){
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d){
                $iqn = substr($d['iqn'], 0, -1);
                $iqn = str_replace("|", "<br>", $iqn);
                $info[] = array(
                    '<input type="checkbox" name="id[]" value="'. $d['name'] .'">',
                    $d['name'],
                    $iqn,
                    $this->getStorageScanTypeDes(false, $d),
                    $utis->calSize($d['size']),
                    array(
                        'initflag' => $utis->parseFlagToBool($d['init_flag']),  //初始化标志
                        'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                        'children' => $this->getStorageChildren($d),     //孩子节点
                        'hypervisor' => $d['hypervisor'],
                        'hypervisordes' => Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor']],
                    	'hypervisor_used' => $utis->parseFlagToBool($d['hypervisor_used'])
                    )
                );
            }
        }
        
        $records["data"] = $info;
        $records["draw"] = $params['draw'];;
        
        return  json_encode($records);
    }
    
    /**
     * 得到存储WWN信息
     * @param unknown $params
     */
    public function getWwnNum($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $opName = "NODE_SR_OP_GET_FC_HOST_WWN";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, '', true);
        if(!$mbResult['result']){
            //错误,没有WWN
//             return $this->muOpResult(false, '获取节点WWN号信息', '', '', $mbResult['errorCode']);
            
            $records["data"] = array();
            $records["draw"] = $params['draw'];
            
            return  json_encode($records);
        }
        $info = array();
        $data = $mbResult['msg']['wwn_list'];
        $i=1;
        foreach ($data as $d){
            $info[] = array(
                $i++,
                $d['host_name'],
                $d['wwnn'],
                $d['wwpn'],
                $d['speed'],
                $d['state'],
                array('stateDes' => $this->getWwnStatusDes($d['state']))
            );
        }
        $records["data"] = $info;
        $records["draw"] = $params['draw'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到WWN状态描述
     * @param unknown $status
     */
    private function getWwnStatusDes($status){
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $des = $ptDes['ONLINEDES'][$status];
        return $des;
    }
    
    /**
     * 得到添加存储的表格数据
     * @param unknown $params
     */
    public function getAddStorageTable($params){
        $nodeuuid = $params['nodeuuid'];
        $storageType = $params['type'];
        $lanfree = $params['lanfree'];
        $this->paramsCheck($nodeuuid, $storageType);
        $utis = Xphp::instance('Utils');
        $msg = array("storage_type" => $storageType, "lan_free_flag" => $utis->parseBoolToFlag($lanfree));
        $opName = "NODE_SR_OP_SCAN_LOCAL";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $info = array();
        if($mbResult['result']){
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d){
                $info[] = array(
                    '<input type="checkbox" name="id[]" value="'. $d['name'] .'">',
                    $d['name'],
                    $this->getStorageScanTypeDes(false, $d),
                    $utis->calSize($d['size']),
                    array(
                        'initflag' => $utis->parseFlagToBool($d['init_flag']),  //初始化标志
                        'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                        'children' => $this->getStorageChildren($d),     //孩子节点
                        'hypervisor' => $d['hypervisor'],
                        'hypervisordes' => Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor']],
                    	'hypervisor_used' => $utis->parseFlagToBool($d['hypervisor_used'])
                    )
                );
            }
        }
        
        $records["data"] = $info;
        $records["draw"] = $params['draw'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到存储扫描的类型列显示结果
     * 添加备份存储显示标准类型 ; 添加lanfree存储显示虚拟化类型或者空
     * @param boolean $lanfreeFlag  lanfree标志
     * @param array $eachData       每项数据
     */
    private function getStorageScanTypeDes($lanfreeFlag, $eachData){
        if(!$lanfreeFlag){
            return $this->getStorageTypeDesDetail($eachData['type'], $eachData);
        }else{
            $hypervisor = intval($eachData['hypervisor']);
            return Xphp::$_config['VMHYPERVISORDES'][$hypervisor];
        }
    }
    
    /**
     * 得到存储类型描述,这里主要是为了处理分区的类型,分区的时候获取详细类型,其余情况调用函数getStorageTypeDes
     * @param int $type
     * @param array $eachData
     */
    private function getStorageTypeDesDetail($type, $eachData){
        if(intval($type) == Xphp::$_config['BD_STORAGE_TYPE']['PARTITION']){
            $ptDes = include APP_PATH . 'platform/PFDescription.php';
            $partitionType = intval($eachData['part_tran']);
            $des = $ptDes['PARTITIONTYPE'][$partitionType];
            return $des;
        }else{
            return $this->getStorageTypeDes($type);
        }
    }
    
    /**
     * 得到某个节点的孩子节点
     * @param array $storage  节点
     * @return array
     */
    private function getStorageChildren($storage){
        $children = array();
        if(!empty($storage['children'])){
            $children = $storage['children'];
        }
        return $children;
    }
    
    /**
     * 添加lan-free存储
     * @param unknown $params
     */
    public function addLanfreeStorage($params){
        $nodeuuid = $params['nodeuuid'];
        $storageType = $params['storagetype'];
        $rawDevPath = $params['storagename'];
        $nickname = $params['rname'];
        $this->paramsCheck($nodeuuid, $storageType, $nickname);
        $utils = Xphp::instance('Utils');
        $formatFlag = $utils->parseBoolToFlag(false);
        $importFlag = $utils->parseBoolToFlag(false);
        $lanfreeFlag = $utils->parseBoolToFlag(true);
        switch ($storageType){
            case Xphp::$_config['BD_STORAGE_TYPE']['FC']:
                $msg = array(
                'lan_free_flag' => $lanfreeFlag,
                'storage_type' => $storageType,
                'nickname' => $nickname,
                "raw_dev_path" => $rawDevPath,
                'format_flag' => $formatFlag,
                'import_flag' => $importFlag,
                'options' => '',
                'usemode' => 1,
                );
                $opName = "NODE_SR_OP_ADD_DISK_PART_LVM";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['ISCSI']:
                $iscsiTargetList = array();
                foreach ($params['serverlist'] as $server){
                    $iscsiTargetList[] = array(
                        'iscsi_target' => $server['ip'] . ":" . $server['port']
                    );
                }
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'nickname' => $nickname,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'raw_dev_path' => $params['lun'],
                    "iscsi_target_list" => $iscsiTargetList,
                    'target_iqn' => '',
                    'username' => '',
                    'password' => '',
                	'usemode' => 1,
                );
                $opName = "NODE_SR_OP_ADD_ISCSI_DISK";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['NFS']:
                $msg = array(
                'lan_free_flag' => $lanfreeFlag,
                'storage_type' => $storageType,
                'nickname' => $nickname,
                'remote_path' => $params['host'],
                'format_flag' => $formatFlag,
                'import_flag' => $importFlag,
                'options' => '',
                'usemode' => 1,
                );
                $opName = "NODE_SR_OP_ADD_NFS";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['CIFS']:
                $msg = array(
                'lan_free_flag' => $lanfreeFlag,
                'storage_type' => $storageType,
                'nickname' => $nickname,
                'remote_path' => $params['host'],
                'username' => $params['username'],
                'passwd' => $params['password'],
                'format_flag' => $formatFlag,
                'import_flag' => $importFlag,
                'options' => '',
                'usemode' => 1,
                );
                $opName = "NODE_SR_OP_ADD_CIFS";
                break;
        
        }
        //添加告警配置
        $msg['warning_flag'] = '';
        $msg['warning_type'] = '';
        $msg['warning_value'] = '';
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
        return $this->unifyMuOpResult($opName, $mbResult);
    }
    
    /**
     * 添加存储
     * @param unknown $params
     */
    public function addNewStorage($params){
        $nodeuuid = $params['nodeuuid'];
        $storageType = $params['storagetype'];
        $rawDevPath = $params['storagename'];
        $nickname = $params['rname'];
        $usemode = $params['usemode'];
        $this->paramsCheck($nodeuuid, $storageType, $nickname);
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag($params['format']);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        switch ($storageType){
            case Xphp::$_config['BD_STORAGE_TYPE']['DISK']:
            case Xphp::$_config['BD_STORAGE_TYPE']['LVM']:
            case Xphp::$_config['BD_STORAGE_TYPE']['PARTITION']:
            case Xphp::$_config['BD_STORAGE_TYPE']['FC']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType, 
                    'nickname' => $nickname,
                    "raw_dev_path" => $rawDevPath,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => $usemode
                );
                $opName = "NODE_SR_OP_ADD_DISK_PART_LVM";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['ISCSI']:
                $iscsiTargetList = array();
                foreach ($params['serverlist'] as $server){
                    $iscsiTargetList[] = array(
                        'iscsi_target' => $server['ip'] . ":" . $server['port']
                    );
                }
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'nickname' => $nickname,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'raw_dev_path' => $params['lun'],
                    "iscsi_target_list" => $iscsiTargetList,
                    'target_iqn' => '',
                    'username' => '',
                    'password' => '',
                	'usemode' => $usemode
                );
                $opName = "NODE_SR_OP_ADD_ISCSI_DISK";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['NFS']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType, 
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => $usemode
                );
                $opName = "NODE_SR_OP_ADD_NFS";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['CIFS']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType, 
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'username' => $params['username'],
                    'passwd' => $params['password'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => $usemode
                );
                $opName = "NODE_SR_OP_ADD_CIFS";
                break;
                                
        }
        //添加告警配置
        $msg['warning_flag'] = $utils->parseBoolToFlag($warningSetting['power']);
        $msg['warning_type'] = intval($warningSetting['type']);
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $msg['warning_type']){
            //如果是按照大小来告警
            $msg['warning_value'] = intval($warningSetting['value']) * 1024 * 1024 * 1024;
        }else{
            $msg['warning_value'] = intval($warningSetting['value']);
        }
        
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
//         return $this->unifyMuOpResult($opName, $mbResult);
        
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            //如果是超时,处理存储过大会创建很久的问题.
            $timeoutCode = Xphp::instance('Utils', 'getErrorNum', 'PF_SOCKET_GET_OP_TIMEOUT');
            if(intval($mbResult['errorCode']) == $timeoutCode){
                $msg = Xphp::$_lang['WEB_STORAGE_CREATE_TO_WAIT'];
                return $this->muOpResult(true, $operate, $msg, 'info');
            }
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        
    }
    
    /**
     * 添加NAS测试备份数据
     * @param unknown $params
     */
    public function addNasStorage($params){
        $nodeuuid = $params['nodeuuid'];
        $storageType = $params['storagetype'];
        $rawDevPath = $params['storagename'];
        $nickname = $params['rname'];
        $usemode = $params['usemode'];
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag($params['format']);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        switch ($storageType){
            case Xphp::$_config['BD_STORAGE_TYPE']['NFS']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => $usemode
                );
                $opName = "NODE_SR_OP_TEST_NFS";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['CIFS']:
                $msg = array(
                'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'username' => $params['username'],
                    'passwd' => $params['password'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => $usemode
                );
                $opName = "NODE_SR_OP_TEST_CIFS";
                break;
        }
        //添加告警配置
        $msg['warning_flag'] = $utils->parseBoolToFlag($warningSetting['power']);
        $msg['warning_type'] = intval($warningSetting['type']);
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $msg['warning_type']){
            //如果是按照大小来告警
            $msg['warning_value'] = intval($warningSetting['value']) * 1024 * 1024 * 1024;
        }else{
            $msg['warning_value'] = intval($warningSetting['value']);
        }
        
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $info = array();
        if($mbResult['result']){
            $utis = Xphp::instance('Utils');
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d){
                if($d['timepoint_count'] > 0){
                    $timepointCount = $d['timepoint_count'];
                    break;
                }
            }
            if(empty($timepointCount)){
                //如果没有数据,直接添加
                return $this->addNewStorage($params);
            }else{
                //如果有备份数据,需要页面确认是否导入数据,然后再发送消息添加存储
                $operate = $this->getUnifyOpcodeDes($opName);
                $extInfo = array('timepoint' => $timepointCount);
                //返回结果到UI
                return $this->muOpResult(true, $operate, '', '', '', $extInfo);
            }
        }else{
            //扫描失败,获取失败
            return $this->unifyMuOpResult($opName, $mbResult);
        }
    }
    
    
    /**
     * 添加COPY存储
     * @param unknown $params
     */
    public function addCopyStorage($params){
        //TODO 这里参考NAS是否做检查
        $storageType = $params['storagetype'];
        $usemode = intval($params['usemode']);
        $nickname = $params['rname'];
        $this->paramsCheck($storageType, $nickname);
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag(false);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        
        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        
        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'ip' => $params['remoteip'],
            'port' => $params['remoteport'],
            'username' => $params['username'],
            'passwd' => $params['password'],
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'options' => '',
            'usemode' => $usemode
        );
        //添加告警配置
        $msg['warning_flag'] = $utils->parseBoolToFlag($warningSetting['power']);
        $msg['warning_type'] = intval($warningSetting['type']);
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $msg['warning_type']){
            //如果是按照大小来告警
            $msg['warning_value'] = intval($warningSetting['value']) * 1024 * 1024 * 1024;
        }else{
            $msg['warning_value'] = intval($warningSetting['value']);
        }
        
        
        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        //测试异地备份系统连接
        $opName = "NODE_SR_OP_TEST_REMOTE_SYSTEM";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
//         return $this->unifyMuOpResult($opName, $mbResult);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $message = $mbResult['msg'];
        if(!$result){
        	return $this->muOpResult($result, $operate, "", 'warning', $mbResult['errorCode']);
        }
        if($message['import_flag'] == Xphp::$_config['FLAG']['SET']){
        	$info = array(
        			'importflag' => $message['import_flag'],
        			'timepointcount' => $message['timepoint_count']
        	);
        	return json_encode($info);
        }else{
        	$msg = array(
        			'storagetype' => $storageType,
        			'rname' => $nickname,
        			'remoteip' => $params['remoteip'],
        			'remoteport' => $params['remoteport'],
        			'username' => $params['username'],
        			'password' => $params['password'],
        			'import' => $importFlag,
        			'usemode' => $usemode,
        			'warningSettings' => $warningSetting
        	);
        	return $this->addCopyStorageConfirm($msg);
//         	return $this->muOpResult(true, $operate,"",'success');
        }
    }
    
    
    public function addCopyStorageConfirm($params){
    	$storageType = $params['storagetype'];
    	$rawDevPath = $params['storagename'];
    	$nickname = $params['rname'];
    	$usemode = $params['usemode'];
    	$this->paramsCheck($storageType, $nickname);
    	$utils = Xphp::instance('Utils');
    	$warningSetting = $params['warningSettings'];
    	$formatFlag = $utils->parseBoolToFlag(false);
    	$importFlag = $utils->parseBoolToFlag($params['import']);
    	$lanfreeFlag = $utils->parseBoolToFlag(false);
    	
    	$nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
    	
    	$msg = array(
    			'node_uuid' => $nodeuuid,
    			'lan_free_flag' => $lanfreeFlag,
    			'storage_type' => $storageType,
    			'nickname' => $nickname,
    			'ip' => $params['remoteip'],
    			'port' => $params['remoteport'],
    			'username' => $params['username'],
    			'passwd' => $params['password'],
    			'format_flag' => $formatFlag,
    			'import_flag' => $importFlag,
    			'options' => '',
    			'usemode' => $usemode
    	);
    	
    	//添加告警配置
    	$msg['warning_flag'] = $utils->parseBoolToFlag($warningSetting['power']);
    	$msg['warning_type'] = intval($warningSetting['type']);
    	if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $msg['warning_type']){
    		//如果是按照大小来告警
    		$msg['warning_value'] = intval($warningSetting['value']) * 1024 * 1024 * 1024;
    	}else{
    		$msg['warning_value'] = intval($warningSetting['value']);
    	}
    	
    	$opName = "NODE_SR_OP_ADD_REMOTE_SYSTEM";
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
    	$result = $mbResult['result'];
    	$operate = $this->getUnifyOpcodeDes($opName);
    	$msg = $mbResult['msg'];
    	//返回结果到UI
    	if($result){
    		return $this->muOpResult($result, $operate, $msg);
    	}else{
    		//如果是超时,处理存储过大会创建很久的问题.
    		$timeoutCode = Xphp::instance('Utils', 'getErrorNum', 'PF_SOCKET_GET_OP_TIMEOUT');
    		if(intval($mbResult['errorCode']) == $timeoutCode){
    			$msg = Xphp::$_lang['WEB_STORAGE_CREATE_TO_WAIT'];
    			return $this->muOpResult(true, $operate, $msg, 'info');
    		}
    		return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
    	}
    }
    /**
     * 删除存储
     * @param unknown $params
     */
    public function deleteStorage($params){
        $storageuuid = $params['uuids'][0];
        $storageType = $this->getTypebyUUID($storageuuid);
        $nodeuuid = $this->getStorageInNode($storageuuid);
        //得到节点的状态
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeStatus = $nodeHandler->getNodeAllStatus($nodeuuid);
        if($nodeStatus['flag']){
            //如果节点在线,发送删除消息到节点进程处理
            $msg = array("storage_uuid" => $storageuuid);
            $opName = "NODE_SR_OP_DELETE";
            if($storageType == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
            	$opName = "NODE_SR_OP_DELETE_REMOTE_SYSTEM";
            }
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
            $this->checkStorageDeleteResult($storageuuid, $mbResult);
            return $this->unifyMuOpResult($opName, $mbResult);
        }
        //节点不在线,检测是否有正在运行的任务使用该存储,如果有返回,没有的话直接删除存储和存储上的时间点记录
        $this->checkStorageInRunningTask($storageuuid);
        return $this->deleteStorageByWeb($storageuuid);
    }
    
    public function getTypebyUUID($storageuuid){
    	$sql = "select storage_type from bd_storage_resource where storage_uuid = ?";
    	$data = $this->dbSelect($sql, array($storageuuid));
    	return intval($data[0]['storage_type']);
    }
    
    /**
     * 检测并过滤存储删除结果
     * NFS/CIFS删除失败的时候需要判断存储是否是离线状态,离线的时候是无法删除的,需要提示用户重启备份系统,然后再执行删除操作
     * @param string $storageuuid   存储UUID
     * @param array $mbResult       后台删除存储结果
     */
    private function checkStorageDeleteResult($storageuuid, $mbResult){
        if($mbResult['result']){
            //如果删除是成功的,直接返回
            return true;
        }
        //如果删除失败,先检测是否是CIFS/NFS,并且是否是离线状态
        $sql = "select storage_nickname, storage_type, status, mount_flag from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $storageType = intval($data[0]['storage_type']);
        $status = intval($data[0]['status']);
        if($storageType != Xphp::$_config['BD_STORAGE_TYPE']['NFS'] && 
            $storageType != Xphp::$_config['BD_STORAGE_TYPE']['CIFS']){
            //如果不是NFS/CIFS,直接返回
        }
        if($status == Xphp::$_config['STORAGE_STATUS']['OFFLINE']){
            //如果是离线状态,报错,并直接退出
            $opName = "NODE_SR_OP_DELETE";
            $operate = $this->getUnifyOpcodeDes($opName);
            exit($this->muOpResult(false, $operate . Xphp::$_lang['WEB_PUBLIC_FAILURE'], $data[0]['storage_nickname'] . Xphp::$_lang['WEB_STORAGE_DELETE_NFS_CIFS_FAILURE_TIPS'], 'warning'));
        }
        return true;
    }
    
    /**
     * 节点不在线时,通过WEB自己删除存储
     * @param string $storageuuid
     */
    private function deleteStorageByWeb($storageuuid){
        $sql = "select bsr.storage_nickname, bn.ip, bn.host_name, bn.node_nickname from 
                bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and bsr.storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $name = empty($data[0]['node_nickname']) ? $data[0]['host_name'] : $data[0]['node_nickname'];
        $ip = $data[0]['ip'];
        $descriptionParam = array($data[0]['storage_nickname'], $name, $ip);
        
        //开始事务
        $this->dbBeginTransaction();
        //查找所有使用存储的时间点
        $sql = "select timepoint_uuid from bd_backup_timepoint where storage_uuid = ? ";
        $data = $this->dbSelect($sql, array($storageuuid));
        $timepoint = "";
        foreach ($data as $d){
            $timepoint .= "'" . $d['timepoint_uuid'] . "',";
        }
        $timepoint = substr($timepoint, 0, -1);
        if(empty($timepoint)){
            $timepoint = "''"; //防止为空的时候SQL出错
        }
        
        //删除bd_backup_timepoint
        $sql = "delete from bd_backup_timepoint where storage_uuid = ?";
        $result = $this->dbExec($sql, array($storageuuid));
        
        //删除vm_backup_timepoint
        $sql = "delete from vm_backup_timepoint where timepoint_uuid in ($timepoint)";
        $result = $result && $this->dbExec($sql);
        
        //删除fs_backup_timepoint
        $sql = "delete from fs_backup_timepoint where fs_timepoint_uuid in ($timepoint)";
        $result = $result && $this->dbExec($sql);
        
        //删除bd_storage_resource
        $sql = "delete from bd_storage_resource where storage_uuid = ?";
        $result = $result && $this->dbExec($sql, array($storageuuid));
        
        if($result){
            $this->dbCommit();
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_DELETE', $descriptionParam);
        }else{
            $this->dbRollBack();
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_DELETE', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
        }
        
        return $this->muOpResult($result, Xphp::$_lang['WEB_NODE_OP_DELETE']);
    }
    
    /**
     * 检测存储是否在正在运行的任务中,如果在的话,直接退出程序,给予提示
     * @param string $storageuuid
     */
    private function checkStorageInRunningTask($storageuuid){
        $sql = "select task_name from bd_task where task_status = ? and storage_uuid = ?";
        $sqlParams = array(Xphp::$_config['TASKSTATUS']['RUNNING'], $storageuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $tasks = array();
        foreach ($data as $d){
            $tasks[] = $d['task_name'];
        }
        if(count($tasks) > 0){
            $tasksStr = implode(',', $tasks);
            exit($this->muOpResult(false, Xphp::$_lang['WEB_STORAGE_DELETE_FAILURE'], 
                $tasksStr . Xphp::$_lang['WEB_STORAGE_DELETE_FAILURE_TIPS']));
        }
    }
    
    /**
     * 删除存储的时候验证
     * 1.检测存储上的时间点个数
     * 2.检测使用了该存储的备份任务
     * @param unknown $params
     */
    public function checkStorageInfo($params){
        //检测时间点
        $sql = "select count(task_name) as timepoint, sum(real_size) as size from bd_backup_timepoint 
                where storage_uuid = ? and deleted_flag = ? and available_flag = ?";
        $sqlParams = array($params['uuids'][0], Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['FLAG']['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $utils = Xphp::instance('Utils');
        $timepointCount = $data[0]['timepoint'];
        $timepointSize = $utils->calSize($data[0]['size']);
        //检测备份任务
        $sql = "select task_name from bd_task where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($params['uuids'][0]));
        $taskCount = count($data);
        $taskname = array();
        foreach ($data as $d){
            $taskname[] = $d['task_name'];
        }
        if($timepointCount == 0 && $taskCount == 0){
            //如果没有数据和任务,直接删除
            return $this->deleteStorage($params);
        }
        $info = array(
            'timepointCount' => $timepointCount,
            'timepointSize' => $timepointSize,
            'taskCount' => $taskCount,
            'tasks' => $taskname
        );
        return $this->muOpResult(true, '', '', '', '', $info);
    }
    
    /**
     * 根据存储UUID得到节点uuid
     * @param string $storageuuid
     */
    private function getStorageInNode($storageuuid){
        $sql = "select node_uuid from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        return $data[0]['node_uuid'];
    }
    
    /**
     * 获取操作名
     * @param string $opName
     */
    private function getUnifyOpcodeDes($opCode){
        $nodeOpcode = Xphp::instance('NodeOpcode');
        $operate = $nodeOpcode->getOpcodeDes($opCode);
        return $operate;
    }
    
    /**
     * 统一发送结果到UI
     * @param string $opName    操作键名
     * @param array $mbResult 操作结果
     * @return json
     */
    private function unifyMuOpResult($opName, $mbResult){
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 得到某个节点下的可用存储(新建备份任务,修改备份任务使用)
     * @param unknown $params
     */
    public function getBackupStorageList($params){
    	$copyFlag = $params['copyflag'];//用于副本初始化可用存储列表
    	$copybackFlag = $params['copybackflag'];//用于副本拉回初始化可用存储列表
        $nodeuuid = $params['nodeuuid'];
        $sql = "select mount_flag, node_uuid, storage_nickname, storage_uuid, storage_type, total_size, free_size, status, error_code 
                from bd_storage_resource where status = ? and mount_flag = ? 
                and error_code = ? and lan_free_flag = ? ";
        $sqlParams = array();
        if($copyFlag){
        	$sql .= " and use_mode not in (0, 1) and storage_type not in (8)";
        	$sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        			Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET']);
        }else if($copybackFlag){
        	$sql .= " and node_uuid = ? and use_mode in (2, 3) and storage_type not in (8)";
        	$sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        			Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET'],$nodeuuid);
        }else{
        	$sql .= " and use_mode not in (2) and storage_type not in (8) and node_uuid = ?";
        	$sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        			Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET'], $nodeuuid);
        }
        
        
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $utils = Xphp::instance("Utils");
        $nodeHandler = Xphp::instance('NodeHandler');
        foreach ($data as $d){
        	$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
        	$storageStatus = $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
        	if($storageStatus != Xphp::$_config['STORAGE_STATUS']['ONLINE']) continue;
        	$nodeName = $this->getNodeName($d['node_uuid']);
        	$name = "";
        	if($copyFlag){
        		$name = ", ".Xphp::$_lang['WEB_PLATFORM_DES_NODE'].': '.$nodeName;
        	}
            $info[] = array(
                'uuid' => $d['storage_uuid'],
                'text' => $d['storage_nickname'] . "(" . $this->getStorageTypeDes($d['storage_type']) . 
                ", " . Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($d['total_size']) . 
                ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($d['free_size']) . ")".$name,
            	'name' => $d['storage_nickname'],
            	'type' => intval($d['storage_type']),
            	'total_size' => intval($d['total_size']),
            	'free_size' => intval($d['free_size'])
            );
        }
        
        $platformHandler = Xphp::instance('PlatformHandler');
        $sortInfo = $platformHandler->_array_column($info,'free_size');
        array_multisort($sortInfo ,SORT_DESC, $info);
        
        return json_encode($info);
    }
    
    
    
    /**
     * 根据uuid得到存储的名字等信息
     * @param string $storageuuid
     * @return string
     */
    public function getOneBackupStorageInfo($storageuuid){
        $sql = "select storage_nickname, storage_type, total_size, free_size, status, error_code
                from bd_storage_resource where storage_uuid = ? ";
        $sqlParams = array($storageuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = '';
        $utils = Xphp::instance("Utils");
        foreach ($data as $d){
            $info = $d['storage_nickname'] . "(" . $this->getStorageTypeDes($d['storage_type']) .
                ", " . Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($d['total_size']) .
                ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($d['free_size']) . ")";
        }
        return $info;
    }
    
    /**
     * 得到某个节点下自动选择存储的UUID(选择一个最大空间的存储)
     * @param string $nodeuuid
     * @return string $storageuuid
     */
    public function getAutoFindStorage($nodeuuid){
        $sql = "select storage_uuid, max(free_size) from bd_storage_resource 
                where node_uuid = ? and status = ? and mount_flag = ?";
        $data = $this->dbSelect($sql, array($nodeuuid, Xphp::$_config['STORAGE_STATUS']['ONLINE'], Xphp::$_config['FLAG']['SET']));
        return $data[0]['storage_uuid'];
    }
    
    /**
     * 得到所有通过节点导入的备份数据信息
     * @param unknown $params
     */
    public function getImportDataList($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', 'task_name', 'module_type', 'task_create_time', 'point', 'size');
        
        $sql = "select task_uuid, task_name, module_type, task_create_time, count(task_uuid) as point, 
                sum(real_size) as size from bd_backup_timepoint  
                where deleted_flag = ? and available_flag = ? and import_flag = ? and user_uuid = ? group by task_uuid 
                order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        
        $flag = Xphp::$_config['FLAG'];
        $data = $this->dbSelect($sql, array($flag['UNSET'], $flag['SET'], $flag['SET'], Xphp::$_user['useruuid'], $start, $length));
        
        
        $sql = "select task_uuid, task_name, module_type, task_create_time, count(task_uuid) as point,
        sum(real_size) as size from bd_backup_timepoint
        where deleted_flag = ? and available_flag = ? and import_flag = ? and user_uuid = ? group by task_uuid
        order by $sortArr[$sortColumn] $sortType ";
        $dataCount = $this->dbSelect($sql, array($flag['UNSET'], $flag['SET'], $flag['SET'], Xphp::$_user['useruuid']));
        $records = array();
        $records["data"] = array();
        $countNum = count($dataCount);
        $i=0;
        $utils = Xphp::instance("Utils");
        foreach ($data as $d){
            $details = $this->getTaskPointDetails($d['task_uuid'], $d['module_type']);
            $records['data'][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['task_uuid'] .'">',
                ++$i,
                $d['task_name'],
                $details['moduleDes'],
                $d['task_create_time'],
                $d['point'],
                $utils->calSize($d['size']),
                $details,
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $countNum;
        $records["recordsFiltered"] = $countNum;
        
        return  json_encode($records);
    }
    
    /**
     * 得到任务的详情
     * 文件是文件列表
     * 虚拟机是虚拟机列表
     * @param string $taskuuid
     * @param int $module_type
     */
    private function getTaskPointDetails($taskuuid, $module_type){
        if(intval($module_type) == Xphp::$_config['MODULE_TYPE']['FS']){
            //文件
            return $this->getFilePointDetails($taskuuid, $module_type);
        }else if(intval($module_type) == Xphp::$_config['MODULE_TYPE']['VM']){
            //虚拟机
            return $this->getVMPointDetails($taskuuid, $module_type);
        }else if(intval($module_type) == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
        	//副本
        	return $this->getCopyPointDetails($taskuuid, $module_type);
        }
    }
    
    /**
     * 获取文件的备份列表
     * @param string $taskuuid
     */
    private function getFilePointDetails($taskuuid, $module_type){
        $sql = "select fbt.backup_path_list from bd_backup_timepoint bbt, fs_backup_timepoint fbt  
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $list = json_decode($data[0]['backup_path_list'], true);
        $path = array();
        foreach ($list as $l){
            $path[] = $l['backup_path'];
        }
        $logHandler = Xphp::instance('LogHandler');
        $info = array(
            'moduleDes' => $logHandler->getModuleTypeDes($module_type),
            'detailsDes' => Xphp::$_lang['WEB_STORAGE_BACKUP_FILE_LIST'],
            'details' => $path
        );
        return $info;
    }
    
    /**
     * 获取虚拟机的备份列表
     * @param string $taskuuid
     */
    private function getVMPointDetails($taskuuid, $module_type){
        $sql = "select vbt.hypervisor_type, vbt.dir_path from vm_backup_timepoint vbt, bd_backup_timepoint bbt 
                where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $path = array();
        foreach ($data as $d){
            if(!in_array($d['dir_path'], $path)){
                $path[] = $d['dir_path'];
            }
        }
        $logHandler = Xphp::instance('LogHandler');
        $info = array(
            'moduleDes' => $logHandler->getModuleTypeDes($module_type, $data[0]['hypervisor_type']),
            'detailsDes' => Xphp::$_lang['WEB_STORAGE_BACKUP_VM_LIST'],
            'details' => $path
        );
        return $info;
    }
    
    /**
     * 获取虚拟机的副本列表
     * @param string $taskuuid
     */
    private function getCopyPointDetails($taskuuid, $module_type){
    	$sql = "select vbt.hypervisor_type, vbt.dir_path from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
    	$path = array();
    	foreach ($data as $d){
    		if(!in_array($d['dir_path'], $path)){
    			$path[] = $d['dir_path'];
    		}
    	}
    	$logHandler = Xphp::instance('LogHandler');
    	$info = array(
    			'moduleDes' => $logHandler->getModuleTypeDes($module_type),
    			'detailsDes' => Xphp::$_lang['UI_COPY_DATA_VM_LIST'],
    			'details' => $path
    	);
    	return $info;
    }
    
    /**
     * 分配导入数据到其他用户
     * @param unknown $params
     */
    public function distributeOldData($params){
        $taskuuids = $params['uuids'];
        $useruuid = $params['useruuid'];
        $username = $params['username'];
        $this->paramsCheck($taskuuids, $useruuid);
        $taskuuidsStr = implode("','", $taskuuids);
        $sql = "update bd_backup_timepoint set user_uuid = ?, user_name = ?, import_flag = ?  where user_uuid = ? and task_uuid in ('$taskuuidsStr')";
        $result = $this->dbExec($sql, array($useruuid, $username, Xphp::$_config['FLAG']['UNSET'], Xphp::$_user['useruuid']));
        $operate = Xphp::$_lang['WEB_STORAGE_DATA_TO_USER'] . $username;
        return $this->muOpResult($result, $operate);
    }
    
    /**
     * 删除导入备份数据
     * @param unknown $params
     */
    public function deleteImportData($params){
        $taskuuids = $params['uuids'];
        $this->paramsCheck($taskuuids);
        $taskuuidsStr = implode("','", $taskuuids);
        $flag = Xphp::$_config['FLAG'];
        //因为XenServer不支持对增量点的删除,所以增量点要先选出来,所以这里反排
        $sql = "select timepoint_uuid, module_type, backup_mode from bd_backup_timepoint 
                where task_uuid in ('$taskuuidsStr') and deleted_flag = ? and available_flag = ?
        	and import_flag = ? order by backup_mode desc";
        $data = $this->dbSelect($sql,array($flag['UNSET'], $flag['SET'], $flag['SET']));
        $vmTimepointuuids = array();
        $result = true;
        foreach($data as $d){
            if(intval($d['module_type']) == Xphp::$_config['MODULE_TYPE']['FS']){
                $result = $result && $this->deleteFSImportData($d['timepoint_uuid']);
                $operate = Xphp::$_lang['WEB_STORAGE_DELETE_FILE_DATA'];
            }else{
            	$vmTimepointuuids[] = $d['timepoint_uuid'];
            }
        }
        //处理文件备份点
        if(!$result){
        	return $this->muOpResult($result, $operate);
        }
        //处理虚拟机备份点
        $result = $result && $this->deleteVMImportData($vmTimepointuuids);
        $operate = Xphp::$_lang['WEB_STORAGE_DELETE_IMPORT_DATA'];
        return $this->muOpResult($result, $operate);
    }
    
    /**
     * 删除文件导入数据
     * @param string $timepointuuid
     */
    private function deleteFSImportData($timepointuuid){
        $fileHandler = Xphp::instance('FileHandler');
        $params = array('uuid' => $timepointuuid);
        $jsonData = $fileHandler->deleteTimepoint($params);
        $result = json_decode($jsonData, true);
        if(!$result['re']){
            exit($jsonData);
        }
        return $result['re'];
    }
    
    /**
     * 批量删除虚拟机导入数据
     * @param array $timepointuuids
     */
    private function deleteVMImportData($timepointuuids){
    	if(empty($timepointuuids)){
    		return true;
    	}
    	
    	$deleteTimepointArr = array();
    	$timepointuuidsStr = implode("', '", $timepointuuids);
    	$sql = "select hypervisor_type, timepoint_uuid from vm_backup_timepoint where timepoint_uuid 
    			in ('" . $timepointuuidsStr . "') order by hypervisor_type";
    	$data = $this->dbSelect($sql, array());
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$vmHandler = Xphp::instance('Vmhandler');
    	//根据hypervisor分组发送给后台批量删除，如果有一组失败直接返回失败错误消息，退出程序
    	$hypervisor = '0';
    	
    	//删除时间点参数组合
    	$deleteParams = array(
    		'timepointlist' => array(),
    		'vmlist' => array()
    	);
    	foreach ($data as $d){
    		if($hypervisor != $d['hypervisor_type']){
    			if(!empty($deleteTimepointArr)){
    				$deleteParams['timepointlist'] = $deleteTimepointArr;
    				$jsonData = $vmHandler->deleteSelectTimepoint($deleteParams);
    				$result = json_decode($jsonData, true);
    				if(!$result['re']){
    					exit($jsonData);
    				}
    				$deleteTimepointArr = array();
    			}else{
    				$deleteTimepointArr[] = array(
    						"nodeuuid" => $nodeHandler->getNodeUUIDWithTimepointUUID($d['timepoint_uuid']),
    						"hypervisor" => $d['hypervisor_type'],
    						"timepointuuid" => $d['timepoint_uuid'],
    				);
    				$hypervisor = $d['hypervisor_type'];
    				continue;
    			}
    		}
    		$deleteTimepointArr[] = array(
    			"nodeuuid" => $nodeHandler->getNodeUUIDWithTimepointUUID($d['timepoint_uuid']),
    			"hypervisor" => $d['hypervisor_type'],
    			"timepointuuid" => $d['timepoint_uuid'],
    		);
    		$hypervisor = $d['hypervisor_type'];
    	}
    	$deleteParams['timepointlist'] = $deleteTimepointArr;
    	$jsonData = $vmHandler->deleteSelectTimepoint($deleteParams);
    	$result = json_decode($jsonData, true);
    	if(!$result['re']){
    		exit($jsonData);
    	}
    	return true;
    }
    
    /**
     * 得到当前所有节点中存储最大的那个节点存储返回
     */
    public function getMaxStorage(){
    	$sql = "select node_uuid, total_size from bd_storage_resource where lan_free_flag = ?";
        $data = $this->dbSelect($sql,array(Xphp::$_config['FLAG']['UNSET']));
        
        $utils = Xphp::instance('Utils');
        $data =$utils->arraySort($data, 'node_uuid', '', 0, -1);
     	$node = array();
        $storageList = array();
        $storageLists = array();
        $nodeStorage = array();
	    foreach($data as $d){
        	if(!in_array($d['node_uuid'],$node)){
        		$node[] = $d['node_uuid'];
        		if(!empty($storageList)){
        			$storageLists[] = $storageList;
        			$storageList = array();
        		}
        		$storageList[] = $d['total_size'];
        	}else{
        		$storageList[] =$d['total_size'];
        	}
		}
		$storageLists[] = $storageList;
		foreach($storageLists as $s){
			$nodeStorage[] = array_sum($s);
		}
		asort($nodeStorage);
		$maxSpace = end($nodeStorage);
		$result = $utils->calSize($maxSpace);
		$info =array(
			'svalue'  => $maxSpace,
			'stext'   => $result,
		);
		return json_encode($info);
    }
    
    /**
     * 得到存储报表节点列表
     */
    public function getReportNodeList($params){
    	$start = $params['start'];
    	$length = $params['length'];
    	$sql = 'select node_uuid, host_name, ip, node_nickname from bd_node order by node_type asc limit ?, ?';
    	$sqlCount = 'select count(node_uuid) as total from bd_node';
    	$data = $this->dbSelect($sql, array($start,$length));
    	$dataCount = $this->dbSelect($sqlCount,array());
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$utils = Xphp::instance("Utils");
    	$records = array();
    	$records["data"] = array();
    	$id = $start + 1;
    	foreach ($data as $d){
    		$storageInfo = $this->getNodeStorage($d['node_uuid']);
    		$usedSize = $storageInfo['total_size'] - $storageInfo['free_size'];
    		$records["data"][] = array(
    				$id++,
    				$nodeHandler->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']),
    				$d['ip'],
    				$storageInfo['storage_num'],
    				$utils->calSize($storageInfo['total_size']),
    				$utils->calSize($storageInfo['free_size']),
    				$utils->calPercent($storageInfo['total_size'], $usedSize)
    		);
    	}
    	if(empty($records['data'])){
    		$dataCount[0]['total'] = 0;
    	}		
    	$records["draw"] = $params['draw'];
		$records["recordsTotal"] = $dataCount[0]['total'];
		$records["recordsFiltered"] = $dataCount[0]['total'];
		
    	return  json_encode($records);
    			
    }
    
    //得到每个节点上的存储数据统计
    private function getNodeStorage($nodeuuid){
    	$sql = "select count(storage_uuid) as storage_num, sum(total_size) as total_size, sum(free_size) as free_size 
    	    from bd_storage_resource where lan_free_flag = ? and node_uuid = ?";
    	$data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['UNSET'], $nodeuuid));
    	$info = array(
    		'storage_num' => intval($data[0]['storage_num']),
    		'total_size' => intval($data[0]['total_size']),
    		'free_size'=> intval($data[0]['free_size'])
    	);
    	return $info;
    }
	
    private function getUsemodeDes($usemode){
    	$des = "";
    	switch ($usemode){
    		case 1:
    			$des = Xphp::$_lang['UI_PLATFORM_BACKUP'];
    			break;
    		case 2:
    			$des = Xphp::$_lang['WEB_PLATFORM_DES_COPY'];
    			break;
    		case 3:
    			$des = Xphp::$_lang['UI_PLATFORM_BACKUP'].",".Xphp::$_lang['WEB_PLATFORM_DES_COPY'];
    			break;
    		default:
    			$des = Xphp::$_lang['UI_PLATFORM_BACKUP'];
    			break;
    	}
    	return $des;
    	
    }
    
    /**
     * 获取节点对应名字
     * @param string $nodeuuid
     */
    private function getNodeName($nodeuuid){
    	$sql = "select ip, node_nickname, host_name from bd_node where node_uuid = ?";
    	$data = $this->dbSelect($sql, array($nodeuuid));
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$name = $nodeHandler->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']);

    	return $name;
    }
    
}
?>