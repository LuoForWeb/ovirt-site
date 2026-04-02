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
        $cloudFlag = $params['cloudflag'];
        $selectId = $params['selectId'];
        $copyFlag = $params['copyflag'];
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $utils = Xphp::instance("Utils");
        $search = $params['search'];
        $sortArr = array('', '', 'bsr.storage_nickname', 'bsr.storage_type', 'bn.node_nickname', 'bsr.status',
            'bsr.total_size', 'bsr.free_size',  'bsr.error_code', 'bsr.use_mode'
        );

        $left = '';

        $sql1 = "select DISTINCT(bsr.storage_uuid),bsr.storage_nickname, bsr.storage_type, bsr.total_size, bsr.free_size, bsr.use_mode, 
                bsr.status, bsr.mount_flag, bsr.error_code, bsr.storage_config, 
                bsr.warning_flag, bsr.warning_type, bsr.warning_value, 
                bn.node_nickname, bn.host_name, bn.ip, bn.node_uuid  
                from bd_storage_resource bsr, bd_node bn  ";

        $sql = " where bsr.node_uuid = bn.node_uuid and 
                bsr.lan_free_flag = ? ";
        $sqlCount1 = "select count(DISTINCT(bsr.storage_uuid)) as total from bd_storage_resource bsr, bd_node bn 
                     ";
        $sqlCount = " where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? ";

        if ($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == 5) {
            // 三权模式下的操作员只能查看分配的存储列表
            $resourceHandler =  Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
            $sqlNew = !empty($resourceInfo) ? array_column($resourceInfo, 'resource_uuid') : [];
            $sqlNew = " and bsr.storage_uuid in ('" . implode("','", $sqlNew) . "') ";
            $sql .= $sqlNew;
            $sqlCount .= $sqlNew;
        }

        $accurateFlag = $params['accurateFlag'];
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET']);
        if($accurateFlag){
            $search = $params['search'];
            $nickName = $search['nickName'];
            $nickName = $utils->escapeWildcard($nickName);
            $storageType = intval($search['storageType']);
            $storageStatus = intval($search['storageStatus']);
            $nodeuuid = $search['nodeValue'];

            //别名
            if($this->checkEmpty($nickName)){
                $sql .= " and bsr.storage_nickname like ? ";
                $sqlCount .= " and bsr.storage_nickname like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$nickName.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$nickName.'%'));
            }

            //存储类型
            if(!empty($storageType)){
                $sql .= " and bsr.storage_type = ? ";
                $sqlCount .= " and bsr.storage_type = ? ";
                $sqlParams = array_merge($sqlParams, array($storageType));
                $sqlCountParams = array_merge($sqlCountParams, array($storageType));
            }

            //存储状态
            if(!empty($storageStatus)){
                // 在线未挂载才是真实的未挂载
                // 离线未挂载显示未离线
                if ($storageStatus == 1) {
                    $left = ' join bd_module_server bms on bn.node_uuid=bms.node_uuid and bms.online_flag = ' . Xphp::$_config['FLAG']['SET'];
                    $sql .= " and bsr.mount_flag = 1 ";
                    $sqlCount .= " and bsr.mount_flag = 1 ";
                }
                if ($storageStatus == 3) {
                    $left = ' join bd_module_server bms on bn.node_uuid=bms.node_uuid ';
                    $sql .= " and ((bms.online_flag = ".Xphp::$_config['FLAG']['UNSET']." and bsr.storage_type != 9) or bsr.status = 3)";
                    $sqlCount .= " and ((bms.online_flag = ".Xphp::$_config['FLAG']['UNSET']." and bsr.storage_type != 9) or bsr.status = 3)";
                } else if ($storageStatus == 4) {
                    // 未挂载显示
                    $sql .= " and bsr.status = 1 and bsr.mount_flag = 2 ";
                    $sqlCount .= " and bsr.status = 1 and bsr.mount_flag = 2 ";
                } else {
                    $sql .= " and bsr.status = ? ";
                    $sqlCount .= " and bsr.status = ? ";
                    $sqlParams = array_merge($sqlParams, array($storageStatus));
                    $sqlCountParams = array_merge($sqlCountParams, array($storageStatus));
                }
            }

            //节点唯一标识
            if(!empty($nodeuuid)){
                $sql .= " and bsr.node_uuid = ? ";
                $sqlCount .= " and bsr.node_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
            }

        }else{

            $searchValue = $search['search'];
            $searchValue = $utils->escapeWildcard($searchValue);
            //别名
            if($this->checkEmpty($searchValue)){
                $sql .= " and bsr.storage_nickname like ? ";
                $sqlCount .= " and bsr.storage_nickname like ? ";
                $sqlParams = array_merge($sqlParams, array('%'.$searchValue.'%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%'.$searchValue.'%'));
            }
        }

        $sql = $sql1 . $left . $sql;
        $sqlCount = $sqlCount1 . $left . $sqlCount;


        //异地备份系统页面
        if($copyFlag){
            $sql .= " and storage_type = ? ";
            $sqlCount .= " and storage_type = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']));
        }else if($cloudFlag){
            //云存储页面
            $sql .= " and storage_type = ? ";
            $sqlCount .= " and storage_type = ? ";
            $sqlParams = array_merge($sqlParams, array(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']));
            $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']));
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);



        $records = array();
        $records["data"] = array();
        $nodeHandler = Xphp::instance('NodeHandler');
        $i = 1;
        $storageNum = intval($count[0]['total']);
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
            $storageConfig = json_decode($d['storage_config'], true);
            //异地备份系统节点显示
            if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
                $nodeDes = $storageConfig['remote_ip'];
                if(!empty($storageConfig['remote_name'])){
                    $nodeDes = $storageConfig['remote_name'].'('.$storageConfig['remote_ip'].')';
                }
                $nodeStatus = $utils->parseFlagToBool(intval($storageConfig['remote_status']));
            }
            // 如果是云存储和CBR存储 那么节点和节点状态都是 --
            if (
                in_array(
                    $d['storage_type'],
                    [Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'], Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']]
                )
            ) {
                $nodeDes = $nodeStatus = '--';
            }

            //华为CBR存储
            if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']){
                $refresh_flag  = $utils->parseFlagToBool(intval($storageConfig['cbr_refresh_flag']));
                $refresh_time = intval($storageConfig['cbr_refresh_interval']) / 60; //转化成分钟
            }

            // 处理下自动导入时间点

            $records["data"][] = array(
                $d['storage_uuid']==$selectId? '<input type="checkbox" checked name="id[]" value="'. $d['storage_uuid'] .'">': '<input type="checkbox" name="id[]" value="'. $d['storage_uuid'] .'">',
                $i++,
                $d['storage_nickname'],
                $this->getStorageTypeDes($d['storage_type']),
                $nodeDes,
                $nodeStatus,
                $utils->calSize($d['total_size']),
                $utils->calSize($d['free_size']),
                $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']), $d['storage_type']),
                $this->getUsemodeDes(intval($d['use_mode'])),
                array(
                    'storagedes' => $this->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag']),$d['storage_type']),
                    'nodedes' => $nodeHandler->getOffLineModuleDes($nodeAllStatus['module']),    //不在线的模块进程
                    'config' => $this->getStorageConfig($d['storage_type'], $d['storage_config'], $d['storage_uuid']),
                    'warning' => array(
                        'flag' => $utils->parseFlagToBool($d['warning_flag']),
                        'type' => $d['warning_type'],
                        'value' => $warningValue,
                    ),
                    //新增自动刷新flag
                    'refresh'=>array(
                        'refresh_flag'=> $refresh_flag,
                        'refresh_time'=> $refresh_time
                    ),
                    // 新增自动导入时间点标记
                    'autoscan_flag' => $utils->parseFlagToBool($storageConfig['timepoint_auto_scan_flag']), // 自动扫描
                    'allocate_flag' => $utils->parseFlagToBool($storageConfig['timepoint_auto_assgin_flag']), // 自动分配
                ),
            );
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $storageNum;
        $records["recordsFiltered"] = $storageNum;

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
        $search = $params['search'];
        $sortArr = array('', 'bsr.storage_nickname', 'bsr.storage_type', 'bn.node_nickname', 'bsr.status',
            'bsr.total_size', 'bsr.free_size',  'bsr.error_code'
        );
        $utils = Xphp::instance("Utils");
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
        	$nickName = $utils->escapeWildcard($nickName);
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
        }else{
            $searchValue = $search['search'];
            $searchValue = $utils->escapeWildcard($searchValue);
            $sql .= "and bsr.storage_nickname like '%" . $searchValue . "%'";
            $sqlCount .= "and bsr.storage_nickname like '%" . $searchValue . "%'";
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);



        $records = array();
        $records["data"] = array();
        $nodeHandler = Xphp::instance('NodeHandler');
        $i=1;
        foreach ($data as $d){
            $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['storage_uuid'] .'">',
                $i++,
                $d['storage_nickname'],
                $this->getStorageTypeDes($d['storage_type']),
                $nodeHandler->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']) . "(" . $d['ip'] . ")",
                $nodeAllStatus['flag'],
                $utils->calSize($d['total_size']),
                $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                array(
                    'storagedes' => $this->getStorageStatusDes($nodeAllStatus, intval($d['status']), intval($d['mount_flag'])),
                    'nodedes' => $nodeHandler->getOffLineModuleDes($nodeAllStatus['module']),    //不在线的模块进程
                    'config' => $this->getStorageConfig($d['storage_type'], $d['storage_config'],  $d['storage_uuid']),
                    'node_uuid' => $d['node_uuid']
                ),
                $d['storage_uuid']
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
                $details['remote_node_ip'] = $config['remote_node_ip'];
                $details['remote_storage_nickname'] = $config['remote_storage_nickname'];
                $details['remote_storage_type'] = $config['remote_storage_type'];
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']:
                $vendorInfo = $this->getVendor(intval($config['vendor']), $config['region'], $config['service_endpoint']);
                $details['vendor'] = $vendorInfo['vendor_des'];
                $details['region']  = $vendorInfo['region_des'];
                $details['user_key'] = $config['username'];
                $details['dev_path'] = $config['pathname'];
                $details['vendor_type'] = intval($config['vendor']);
                $serviceNode = $config['service_endpoint'];
                //如果服务节点为空
                if(empty($serviceNode)){
                    $serviceNode = Xphp::$_config['NULLSPACE'];
                }
                $details['service_endpoint'] = $serviceNode;
                break;                                               //云存储
            case Xphp::$_config['BD_STORAGE_TYPE']['LOCALDIR']:
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['FILE_SYSTEM']: // 并行文件系统
                $vendorInfo = $this->getVendor(intval($config['vendor']), $config['region'], $config['service_endpoint']);
                $details['vendor'] = $vendorInfo['vendor_des'];
                $details['region']  = $vendorInfo['region_des'];
                $details['user_key'] = $config['username'];
                $details['dev_path'] = $config['pathname'];
                $details['vendor_type'] = intval($config['vendor']);
                $serviceNode = $config['service_endpoint'];
                //如果服务节点为空
                if(empty($serviceNode)){
                    $serviceNode = Xphp::$_config['NULLSPACE'];
                }
                $details['service_endpoint'] = $serviceNode;
                break;
            default:
                break;
        }

        return $details;
    }

    /**
     *
     * @param int $vendor       云存储类型
     * @param string $region    云存储地区
     * @param string $serverPoint   云存储服务终端节点
     * @return string[]|mixed[]|unknown[]
     */
    private function getVendor($vendor, $region, $serverPoint){
        $vendorDes = Xphp::$_config['NULLSPACE'];
        $regionDes = $region;
        //地区为空
        if(empty($region)){
           $regionDes =  Xphp::$_config['NULLSPACE'];
        }
        $regionInfo = array();
        switch ($vendor){
            case 1:
                $vendorDes = "AWS S3";
                $regionInfo = Xphp::$_cloud['CLOUD_STORAGE_REGION']['AWS_REGION'];
                break;
            case 2:
                $vendorDes = "Azure";
                $regionDes =  Xphp::$_config['NULLSPACE'];
                break;
            case 3:
                $vendorDes = Xphp::$_lang['UI_STORAGE_CLOUD_VENDOR_ALI'];
                $regionInfo = Xphp::$_cloud['CLOUD_STORAGE_REGION']['ALI_REGION'];
                break;
            case 4:
                $vendorDes = Xphp::$_lang['UI_STORAGE_CLOUD_VENDOR_HUAWEI'];
                $regionInfo = Xphp::$_cloud['CLOUD_STORAGE_REGION']['HUAWEI_REGION'];
                break;
            case 5:
                $vendorDes = Xphp::$_lang['UI_STORAGE_CLOUD_VENDOR_TENCENT'];
                $regionInfo = Xphp::$_cloud['CLOUD_STORAGE_REGION']['TENCENT_REGION'];
                break;
            case 6:
                $vendorDes = "Ceph S3";
                break;
            case 7:
                $vendorDes = "Wasabi";
                $regionInfo = Xphp::$_cloud['CLOUD_STORAGE_REGION']['WASABI_REGION'];
                break;
            case 8:
                $vendorDes = "MinIO";
                break;
            case 9:
                $vendorDes = "Huawei OceanStor Pacific";
                break;
        }

        if(!empty($regionInfo)){
            foreach ($regionInfo as $r){
                if($r['value'] == $region){
                    $regionDes = Xphp::$_lang[$r['text']];
                }
                //wasabi云存储
                if($vendor == 7 && $r['value'] == $serverPoint){
                    $regionDes = Xphp::$_lang[$r['text']];
                }

                continue;
            }
        }

        $info = array(
            'vendor_des' => $vendorDes,
            'region_des' => $regionDes
        );

        return $info;
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
    public function getStorageStatus($nodeAllStatus, $storageStatus, $mountFlag, $storageType = ''){
        //首先检查节点的状态
        if(!$nodeAllStatus['flag'] && $storageType != 9){
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
    public function getStorageStatusDes($nodeAllStatus, $storageStatus, $mountFlag,$storageType = ''){
        $status = $this->getStorageStatus($nodeAllStatus, $storageStatus, $mountFlag, $storageType);
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
        $sql = "select storage_nickname, warning_flag, warning_type, warning_value, use_mode, storage_type, total_size,storage_config from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $utils = Xphp::instance('Utils');
        $usemode = intval($data[0]['use_mode']);
        //byte转换成GB
        $sizeGB = floor(intval($data[0]['total_size'])/1024/1024/1024);
        $storageConfig = json_decode($data[0]['storage_config'],true);
        $info = array(
            'storageuuid' => $storageuuid,
            'storagename' => $data[0]['storage_nickname'],
            'warning_flag' => $utils->parseFlagToBool($data[0]['warning_flag']),
            'warning_type' => $data[0]['warning_type'],
        	'checkmode' => $usemode,
        	'storage_type' => intval($data[0]['storage_type']),
            'sizeGB' => $sizeGB,
            'scandata_flag' => $utils->parseFlagToBool($storageConfig['cbr_refresh_flag']),
            'scandata_value' => intval($storageConfig['cbr_refresh_interval']) / 60,
            'allocate_flag' => $utils->parseFlagToBool($storageConfig['timepoint_auto_assgin_flag']), // 自动分配
            'autoscan_flag' => $utils->parseFlagToBool($storageConfig['timepoint_auto_scan_flag']), // 自动扫描
        );

        //添加告警配置
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == intval($data[0]['warning_type'])){
            //如果是按照大小来告警
            $info['warning_value'] = $data[0]['warning_value'] / 1024 / 1024 / 1024;
            if(intval($data[0]['storage_type']) == Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']
            || intval($data[0]['storage_type']) == Xphp::$_config['BD_STORAGE_TYPE']['FILE_SYSTEM']){
                $info['warning_value'] = $data[0]['warning_value'] / 1024 / 1024 / 1024 / 1024;
            }
        }else{
            $info['warning_value'] = $data[0]['warning_value'];
        }

        if ($info['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['CIFS']) {
            // 如果是cifs的话，那么还要读取出用户名 和 密码
            $info['username'] = $storageConfig['username'];
        }

        if ($info['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']) {
            // 如果是异地备份系统的话，那么还要读取出IP地址/域名和端口
            $info['remote_ip'] = $storageConfig['remote_ip'];
            $info['remote_port'] = $storageConfig['remote_port'];
        }

        return json_encode($info);
    }

    /**
     * 修改存储名称
     * @param unknown $params
     */
    public function editStorageName($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_storage_manager_edit");
        $storageuuid = $params['storageuuid'];
        $storagename = trim($params['storagename']);
        $usemode= $params['usemode'];
        $storagetype = $params['storagetype'];
        $storageOldname = $this->getStorageNameByUUID($storageuuid);
        if($storagename != $storageOldname){
            $this->checkStorageName($storagename, Xphp::$_config['FLAG']['UNSET'], Xphp::$_lang['WEB_STORAGE_EDIT_INFO']); //检查存储名是否和其他已存在存储重复
        }
        //检查存储名是否为空
        if(empty($storagename)){
            return $this->muOpResult(false, Xphp::$_lang['WEB_STORAGE_EDIT_INFO'], Xphp::$_lang['WEB_STORAGE_NAME_IS_EMPTY'],"warning");
        }
        $this->paramsCheck($storageuuid);

        if ($storagetype == Xphp::$_config['BD_STORAGE_TYPE']['CIFS'] && !empty($params['username'])) {
            // 如果是cifs 并且修改了密码
            $msg = [];
            $msg['username'] = $params['username'];
            $password = base64_decode($params['password']);
            $msg['passwd'] = $password;
            $msg['storage_uuid'] = $storageuuid;
            $opName = "NODE_SR_OP_MODIFY_CIFS_SR";
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false);
            //如果修改失败
            if(!$mbResult['result']){
                return $this->muOpResult(false, Xphp::$_lang['WEB_STORAGE_EDIT_INFO'], '', 'info', $mbResult['errorCode']);
            }
        }

        $utils = Xphp::instance('Utils');
        $warningFlag = $utils->parseBoolToFlag($params['warningSettings']['power']);
        $warningType = $params['warningSettings']['type'];
        $autoimportFlag =  $utils->parseBoolToFlag($params['autoScanFlag']);
        $autoassginFlag =  $utils->parseBoolToFlag($params['autoAssignFlag']);
        if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == intval($warningType)){
            //如果是按照大小来告警
            $warningValue = $params['warningSettings']['value'] * 1024 * 1024 * 1024;
            if($storagetype == Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']){
                $warningValue = $params['warningSettings']['value'] * 1024 * 1024 * 1024 * 1024;
            }
        }else{
            $warningValue = $params['warningSettings']['value'];
        }

        $sql = "select storage_nickname, storage_config from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $storageConfig =  json_decode($data[0]['storage_config'],true);
        $storageConfig['timepoint_auto_scan_flag'] = $autoimportFlag;
        $storageConfig['timepoint_auto_assgin_flag'] = $autoassginFlag;
        $msg = [];
        if ($storagetype == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE'] && $params['remoteflag']) {
            $msg = [
                'remote_ip' => $storageConfig['remote_ip'],
                'remote_port' => $storageConfig['remote_port'],
            ];
        }
        $sql = "update bd_storage_resource set storage_nickname = ?, warning_flag = ?, warning_type = ?,
                warning_value = ?, use_mode = ?, storage_config = ? ";
        //如果是云存储
        if($storagetype == Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']){
            $freeSize = $this->getCloudStorageFreeSize($storageuuid, $warningValue);
            $storageConfig  = json_encode($storageConfig);
            $sql .= " , total_size = ?, free_size = ? ";
            $sqlParams = array($storagename, $warningFlag, $warningType, $warningValue, $usemode, $storageConfig, $warningValue, $freeSize, $storageuuid );
        }
        //如果是华为CBR存储
        else if($storagetype ==  Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']){
            $refreshflag = $utils->parseBoolToFlag($params['scandataSettings']['power']);
            $refreshtime = intval($params['scandataSettings']['value']) * 60;

            $storageConfig['cbr_refresh_flag']  = $refreshflag;
            $storageConfig['cbr_refresh_interval']  = $refreshtime;
            $storageConfig  = json_encode($storageConfig);
            //warningType转成int
            $warningType = 0;
            $warningValue = 0;
            $sqlParams = array($storagename, $warningFlag, $warningType, $warningValue, $usemode, $storageConfig, $storageuuid );
        }else{
            $storageConfig  = json_encode($storageConfig);
            $sqlParams = array($storagename, $warningFlag, $warningType, $warningValue, $usemode, $storageConfig,  $storageuuid);
        }

        $sql .= " where storage_uuid = ?";

        $result = $this->dbExec($sql, $sqlParams);
        //如果是华为Cbr 在更新完之后要更新vm_tree
        if($result && $storagetype ==  Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']){
            //更新vmtree中的存储名 调用刷新更新vm_tree这张表
            //设置参数
            $storageid = array();
            $storageid[]= array(
                "storage_uuid" => $storageuuid
            );
            $pfMsg['storage_uuid_list'] = $storageid;
            $opName = "NODE_HUAWEI_CBR_OP_REFERSH";
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($pfMsg), false);
            //如果刷新失败
            if(!$mbResult['result']){
                return $this->muOpResult(false, Xphp::$_lang['API_CODE_STORAGES_EDIT_STORAGE_NICKNAME'], '', 'info', $mbResult['errorCode']);
            }
        }

        if ($result && $storagetype == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE'] && $params['remoteflag']) {
            // 如果是异地备份系统 并且修改了密码
            $msg['username'] = $params['username'];
            $password = md5(base64_decode($params['password']));
            $msg['password'] = $password;
            $msg['storage_uuid'] = $storageuuid;
            $opName = "NODE_SR_OP_MODIFY_REMOTE_SYSTEM_INFO";
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false);
            //如果修改失败
            if(!$mbResult['result']){
                return $this->muOpResult(false, Xphp::$_lang['API_CODE_STORAGES_EDIT_STORAGE_NICKNAME'], '', 'info', $mbResult['errorCode']);
            }
        }

        //插系统日志
        $oldName = $data[0]['storage_nickname'];
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $usemodeDes = $pfDes['STORAGE_USE_DES'][$usemode];
        $descriptionParam = array($usemodeDes, $oldName, $storagename);
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
            // 这里判断处理下是否是ipv6，是的话，就加上中括号
            if (filter_var($server['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                // 是 ipv6
                $server['ip'] = '[' . inet_ntop(inet_pton($server['ip'])) . ']'; // 获取缩写后的ipv6
            }
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

        if (!$mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $opcodeDes, $mbResult['msg'], '', $mbResult['errorCode']);
        }

        $info = $iqnArr = $target = array();
        if($mbResult['result']){
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d){
                $iqn = substr($d['iqn'], 0, -1);
                $iqn = str_replace("|", "<br>", $iqn);
                $isopen = (bool)intval($d['size']);

                if ($iqn && !in_array($iqn, array_column($iqnArr, 'iqn'))) {
                    $iqnArr[] = [
                        'iqn' => $iqn,
                        'isopen' => $isopen,
                        'iscsi_target' => $d['iscsi_target'], // target信息
                    ];
                }

                if (!in_array($d['iscsi_target'], array_column($target, 'iscsi_target'))) {
                    $target[] = [
                        'iqn' => $iqn,
                        'isopen' => (bool)$iqn,
                        'iscsi_target' => $d['iscsi_target'], // target信息
                    ];
                }

                if (!$isopen || !$iqn) {
                    continue;
                }
                // 重新组合下列表
                $info[] = [
                    'id' => $d['name'],
                    'pid' => $iqn,
                    'name' => $d['name'] . ' ' . $this->getStorageScanTypeDes(false, $d) . ' ' . $utis->calSize($d['size']),
                    'nocheck' => false,
                    'clickshow' => false,
                    'isParent' => false,
                    'open' => false,
                    'desc' => array(
                        'title' => $d['name'],
                        'iscsi_target' => $d['iscsi_target'], // target信息
                        'initflag' => $utis->parseFlagToBool($d['init_flag']),  //初始化标志
                        'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                        'children' => $this->getStorageChildren($d),     //孩子节点
                        'hypervisor' => $d['hypervisor'],
                        'hypervisordes' => Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor']],
                        'hypervisor_used' => $utis->parseFlagToBool($d['hypervisor_used'])
                    )
                ];
            }
        }

        // 再次重新组织下
        $return = [];
        foreach ($iqnArr as $item) {
            $return[] = [
                'id' => $item['iqn'],
                'pid' => $item['iscsi_target'],
                'name' => $item['iqn'],
                'nocheck' => true,
                'clickshow' => !$item['isopen'],
                'isParent' => true,
                'open' => $item['isopen'],
                'desc' => [
                    'iscsi_target' => $item['iscsi_target'],
                ]
            ];
        }

        foreach ($target as $item) {
            $return[] = [
                'id' => $item['iscsi_target'],
                'pid' => 0,
                'name' => $item['iscsi_target'],
                'nocheck' => true,
                'clickshow' => !$item['isopen'],
                'isParent' => true,
                'open' => $item['isopen'],
                'desc' => [
                    'iscsi_target' => $item['iscsi_target'],
                ]
            ];
        }
        $info = array_merge($return, $info);

        $records["data"] = $info;
        $records["draw"] = $params['draw'];
        $records["re"] = true;

        return json_encode($records);
    }

    /**
     * 进行chap认证信息填写后再次扫描-第一次认证
     * @param unknown $params
     */
    public function getIscsiChapList1($params)
    {

        $nodeuuid = $params['nodeuuid'];
        $iscsiTarget = $params['iscsi_target'];
        $username = $params['username'];
        $passwd = $params['passwd'];
        $targetIqn = $params['target_iqn'];

        $this->paramsCheck($nodeuuid, $iscsiTarget, $username, $passwd, $targetIqn);

        $utis = Xphp::instance('Utils');
        $msg = array(
            'iscsi_target' => $iscsiTarget,
            'discover_chap_username' => $username,
            'discover_chap_password' => $passwd,
        );
        $opName = "NODE_SR_OP_SCAN_ISCSI_WITH_DISCOVER_CHAP";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);

        $opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);

        if (!$mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $opcodeDes, $mbResult['msg'], '', $mbResult['errorCode']);
        }

        $info = array();
        if($mbResult['result']){
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d){
                $iqn = substr($d['iqn'], 0, -1);
                $iqn = str_replace("|", "<br>", $iqn);
                $isopen = (bool)intval($d['size']);
                // 重新组合下列表
                $info[] = [
                    'id' => $iqn,
                    'pid' => $d['iscsi_target'],
                    'name' => $iqn,
                    'nocheck' => true,
                    'clickshow' => !$isopen,
                    'isParent' => true,
                    'open' => $isopen,
                    'desc' => array(
                        'title' => $d['name'],
                        'iscsi_target' => $d['iscsi_target'], // target信息
                        'initflag' => $utis->parseFlagToBool($d['init_flag']),  //初始化标志
                        'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                        'children' => $this->getStorageChildren($d),     //孩子节点
                        'hypervisor' => $d['hypervisor'],
                        'hypervisordes' => Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor']],
                        'hypervisor_used' => $utis->parseFlagToBool($d['hypervisor_used'])
                    )
                ];
                if ($d['size']) {
                    // 表示是展开的，那么需要读取出最下面的一层的信息
                    $info[] = [
                        'id' => $d['name'],
                        'pid' => $iqn,
                        'name' => $d['name'] . ' ' . $this->getStorageScanTypeDes(false, $d) . ' ' . $utis->calSize($d['size']),
                        'nocheck' => false,
                        'clickshow' => false,
                        'isParent' => false,
                        'open' => false,
                        'desc' => array(
                            'title' => $d['name'],
                            'iscsi_target' => $d['iscsi_target'], // target信息
                            'initflag' => $utis->parseFlagToBool($d['init_flag']),  //初始化标志
                            'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                            'children' => $this->getStorageChildren($d),     //孩子节点
                            'hypervisor' => $d['hypervisor'],
                            'hypervisordes' => Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor']],
                            'hypervisor_used' => $utis->parseFlagToBool($d['hypervisor_used'])
                        )
                    ];
                }
            }
        }

        $records["data"] = $info;
        $records["draw"] = $params['draw'];
        $records["re"] = true;

        return json_encode($records);
    }
    /**
     * 进行chap认证信息填写后再次扫描-第二次认证
     */
    public function getIscsiChapList($params){

        $nodeuuid = $params['nodeuuid'];
        $iscsiTarget = $params['iscsi_target'];
        $username = $params['username'];
        $passwd = $params['passwd'];
        $targetIqn = $params['target_iqn'];

        $this->paramsCheck($nodeuuid, $iscsiTarget, $username, $passwd, $targetIqn);

        $utis = Xphp::instance('Utils');
        $msg = array(
            'iscsi_target' => $iscsiTarget,
            'username' => $username,
            'passwd' => $passwd,
            "target_iqn" => $targetIqn
        );
        $opName = "NODE_SR_OP_SCAN_ISCSI_WITH_CHAP";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);

        $opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);

        if (!$mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $opcodeDes, $mbResult['msg'], '', $mbResult['errorCode']);
        }

        $info = array();
        if($mbResult['result']){
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d){
                $iqn = substr($d['iqn'], 0, -1);
                $iqn = str_replace("|", "<br>", $iqn);
                // 重新组合下列表
                $info[] = [
                    'id' => $d['name'],
                    'pid' => $iqn,
                    'name' => $d['name'] . ' ' . $this->getStorageScanTypeDes(false, $d) . ' ' . $utis->calSize($d['size']),
                    'nocheck' => false,
                    'clickshow' => false,
                    'isParent' => false,
                    'open' => false,
                    'desc' => array(
                        'title' => $d['name'],
                        'iscsi_target' => $d['iscsi_target'], // target信息
                        'initflag' => $utis->parseFlagToBool($d['init_flag']),  //初始化标志
                        'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                        'children' => $this->getStorageChildren($d),     //孩子节点
                        'hypervisor' => $d['hypervisor'],
                        'hypervisordes' => Xphp::$_config['VMHYPERVISORDES'][$d['hypervisor']],
                        'hypervisor_used' => $utis->parseFlagToBool($d['hypervisor_used'])
                    )
                ];
            }
        }

        $records["data"] = $info;
        $records["draw"] = $params['draw'];
        $records["re"] = true;

        return json_encode($records);
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
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_storage_lanfree_add");
        $mountparams = $params['mountparams'];
        $nodeuuid = $params['nodeuuid'];
        $storageType = $params['storagetype'];
        $rawDevPath = $params['storagename'];
        $nickname = $params['rname'];
        //检查别名
        $this->checkStorageNameEmpty($nickname);
        $this->paramsCheck($nodeuuid, $storageType);
        $utils = Xphp::instance('Utils');
        $formatFlag = $utils->parseBoolToFlag(false);
        $importFlag = $utils->parseBoolToFlag(false);
        $lanfreeFlag = $utils->parseBoolToFlag(true);
        $this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['API_CODE_STORAGES_ADD_LANFREE']); //检查存储名是否重复
        $pathList = $params['pathlist'];
        $nameList = array();
        $list = array();
        $i = 1;
        if(!empty($pathList)){
           foreach ($pathList as $path){
               $nameList[] = array(
                   "lanfree_nickname" => count($pathList) == 1 ? $nickname : $nickname.$i
               );
               $list[] = array(
                   'lanfree_name' => $path
               );
               $i++;
           }
        }

        switch ($storageType){
            case Xphp::$_config['BD_STORAGE_TYPE']['FC']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    "raw_dev_path" => "",
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => 1,
                    'lanfree_name_list' => $list,
                    'lanfree_nickname_list' => $nameList,
                    'mount_params' => ''
                );
                $opName = "NODE_SR_OP_ADD_DISK_PART_LVM";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['ISCSI']:
                $iscsiTargetList = array();
                foreach ($params['serverlist'] as $server){
                    // 这里判断处理下是否是ipv6，是的话，就加上中括号
                    if (filter_var($server['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        // 是 ipv6
                        $server['ip'] = '[' . inet_ntop(inet_pton($server['ip'])) . ']'; // 获取缩写后的ipv6
                    }
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
                    'iscsi_target' => $params['iscsi_target'], // 选择的ip和端口
                    'target_iqn' => $params['target_iqn'], // 选择的iqn
                    "chap_username" => $params['chap_username'] ?? '', //用户如果输入了chap认证就写，没有就为空
                    "chap_password" => $params['chap_username'] ?? '',
                    "discover_chap_username" => $params['discover_chap_username'] ?? '', //用户如果输入了门户chap认证就写，没有就为空
                    "discover_chap_password" => $params['discover_chap_password'] ?? '',
                    "logout_iscsi_list" => $params['logout_iscsi_list'] ?? [], // 未选择的iscsi列表
                    'username' => '',
                    'password' => '',
                	'usemode' => 1,
                    'lanfree_name_list' => $list,
                    'lanfree_nickname_list' => $nameList,
                    'mount_params' => ''
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
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams
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
                'lanfree_name_list' => "",
                'lanfree_nickname_list' => "",
                'mount_params' => $mountparams
                );
                $opName = "NODE_SR_OP_ADD_CIFS";
                break;

        }
        //添加告警配置
        $msg['warning_flag'] = '';
        $msg['warning_type'] = '';
        $msg['warning_value'] = '';
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeInfo = $nodeHandler->getNodeNameWithUUID($nodeuuid);
        $descriptionParam = array('lanfree', $nickname, $nodeInfo['name'], $nodeInfo['ip']);
//         if($result){
//             $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_ADD', $descriptionParam);
//         }else{
//             $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_ADD', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
//         }
        return $this->unifyMuOpResult($opName, $mbResult);
    }

    /**
     * 添加存储
     * @param unknown $params
     */
    public function addNewStorage($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_storage_manager_add");
        $nodeuuid = $params['nodeuuid'];
        $storageType = $params['storagetype'];
        $rawDevPath = $params['storagename'];
        $nickname = $params['rname'];
        $usemode = $params['usemode'];
        $mountparams = $params['mountparams'];
        //检查别名
        $this->checkStorageNameEmpty($nickname);
        $this->paramsCheck($nodeuuid, $storageType);
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag($params['format']);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        $this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['UI_STORAGE_ADD']); //检查存储名是否重复
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
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams
                );
                $opName = "NODE_SR_OP_ADD_DISK_PART_LVM";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['ISCSI']:
                $iscsiTargetList = array();
                foreach ($params['serverlist'] as $server){
                    // 这里判断处理下是否是ipv6，是的话，就加上中括号
                    if (filter_var($server['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        // 是 ipv6
                        $server['ip'] = '[' . inet_ntop(inet_pton($server['ip'])) . ']'; // 获取缩写后的ipv6
                    }
                    $iscsiTargetList[] = array(
                        'iscsi_target' => $server['ip'] . ":" . $server['port']
                    );
                }
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'nickname' => $nickname,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'raw_dev_path' => $params['lun'],
                    // "iscsi_target_list" => $iscsiTargetList, // 之前的ip和端口 本次未用 废弃
                    "iscsi_target_list" => '',
                    'iscsi_target' => $params['iscsi_target'], // 选择的ip和端口
                    'target_iqn' => $params['target_iqn'], // 选择的iqn
                    "chap_username" => $params['chap_username'] ?? '', //用户如果输入了chap认证就写，没有就为空
                    "chap_password" => $params['chap_password'] ?? '',
                    "discover_chap_username" => $params['discover_chap_username'] ?? '', //用户如果输入了门户chap认证就写，没有就为空
                    "discover_chap_password" => $params['discover_chap_password'] ?? '',
                    "logout_iscsi_list" => $params['logout_iscsi_list'] ?? [], // 未选择的iscsi列表
                    'username' => '',
                    'password' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams
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
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams
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
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams
                );
                $opName = "NODE_SR_OP_ADD_CIFS";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['LOCALDIR']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'raw_dev_path' => $params['dirname'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => ""
                );
                $opName = "NODE_SR_OP_ADD_DIR";
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

        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $usemodeDes = $pfDes['STORAGE_USE_DES'][$usemode];
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeInfo = $nodeHandler->getNodeNameWithUUID($nodeuuid);
        $descriptionParam = array($usemodeDes, $nickname, $nodeInfo['name'], $nodeInfo['ip']);
        //返回结果到UI
        if($result){
//             $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_ADD', $descriptionParam);
        	$sql = "select total_size, storage_uuid, mount_point from bd_storage_resource where storage_nickname = ? ";
        	$data = $this->dbSelect($sql, array($nickname));
        	// if($usemode == Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'] && intval($data[0]['total_size']) > 256*1024*1024*1024){
        	// 	$cmdStr = "ps aux|grep daserver";
        	// 	exec($cmdStr, $info);
        	// 	if(count($info) > 2){
        	// 		if(!$_SESSION['datapp_auth'] || time() > $_SESSION['datapp_outtime']){
        	// 			$this->getDBAuth(); //获取datapp认证
        	// 		}
        	// 		$systemHandler = Xphp::instance('SystemHandler');
        	// 		$storageInfo = $systemHandler->getDatappStorageInfo(); //获取datapp授权信息
        	// 		$path = $data[0]['mount_point'];
        	// 		$size = intval($data[0]['total_size']);
        	// 		$path = str_replace("//", "/", $path);
        	// 		$systemHandler->addDatappStorage($storageInfo['id'], $path, $size);
        	// 	}
        	// }
            return $this->muOpResult($result, $operate, $msg);
        }else{
//             $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_ADD', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
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
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_storage_manager_add");
        $nodeuuid = $params['nodeuuid'];
        $storageType = $params['storagetype'];
        $rawDevPath = $params['storagename'];
        $nickname = $params['rname'];
        $usemode = $params['usemode'];
        $utils = Xphp::instance('Utils');
        $mountparams = $params['mountparams'];
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag($params['format']);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        //检查别名
        $this->checkStorageNameEmpty($nickname);
        $this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['UI_STORAGE_ADD']); //检查存储名是否重复
        switch ($storageType){
            case Xphp::$_config['BD_STORAGE_TYPE']['NFS']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams
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
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => $mountparams

                );
                $opName = "NODE_SR_OP_TEST_CIFS";
                break;
            case Xphp::$_config['BD_STORAGE_TYPE']['LOCALDIR']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'raw_dev_path' => $params['dirname'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => "",
                    'lanfree_nickname_list' => "",
                    'mount_params' => ""
                );
                $opName = "NODE_SR_OP_TEST_DIR";
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
     * 添加并行文件系统
     * @param unknown $params
     */
    public function addCloudhwStorage($params){
        //权限检查
        $storageType = $params['storagetype'];
        $usemode = intval($params['usemode']);
        $nickname = $params['rname'];
        //检查别名
        $this->checkStorageNameEmpty($nickname);
        $this->paramsCheck($storageType);
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag(false);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        $this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['UI_STORAGE_ADD']); //检查存储名是否重复
        //$nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $nodeuuid = $params['nodeuuid'];

        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'vendor' => $params['vendor'],
            'region' => $params['region'],
            'username' => $params['username'],
            'passwd' => $params['password'],
            'filesystem_name' => $params['system_name'],
            'limit_size' => intval($params['limitsize']) * 1024 * 1024 * 1024 * 1024,
            'custom_flag' => $utils->parseBoolToFlag($params['diyflag']),
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'options' => '',
            'usemode' => $usemode,
            'lanfree_name_list' => "",
            'lanfree_nickname_list' => "",
            'mount_params' => '',
            'use_ssl_flag' => $utils->parseBoolToFlag($params['usesslflag']),
            'service_endpoint' => $params['servernode']
        );
        //添加告警配置
        $msg['warning_flag'] = $utils->parseBoolToFlag($warningSetting['power']);
        $msg['warning_type'] = Xphp::$_config['FLAG']['UNSET']; //云存储默认按大小告警
        $msg['warning_value'] = intval($params['limitsize']) * 1024 * 1024 * 1024 * 1024;

        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $opName = "NODE_SR_OP_TEST_PARALLEL_FILESYSTEM";

        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $message = $mbResult['msg'];
        if(!$result){
            return $this->muOpResult($result, $operate, "", 'warning', $mbResult['errorCode']);
        }
        $data = $mbResult['msg']['raw_storage_list'];
        foreach ($data as $d){
            if($d['timepoint_count'] > 0){
                $timepointCount = $d['timepoint_count'];
                break;
            }
        }
        if(!empty($timepointCount)){
            $info = array(
                'importflag' => true,
                'timepointcount' => count($timepointCount)
            );
            return json_encode($info);
        }else{
            return $this->addCloudhwStorageConfirm($params);
        }
    }

    /**
     * 添加并行文件系统确认
     * @param unknown $params
     */
    public function addCloudhwStorageConfirm($params){
        //权限检查
        $storageType = $params['storagetype'];
        $usemode = intval($params['usemode']);
        $nickname = $params['rname'];
        //检查别名
        $this->checkStorageNameEmpty($nickname);
        $this->paramsCheck($storageType);
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag(false);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        $this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['UI_STORAGE_ADD']); //检查存储名是否重复
        //$nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $nodeuuid = $params['nodeuuid'];

        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'vendor' => $params['vendor'],
            'region' => $params['region'],
            'username' => $params['username'],
            'passwd' => $params['password'],
            'filesystem_name' => $params['system_name'],
            'limit_size' => intval($params['limitsize']) * 1024 * 1024 * 1024 * 1024,
            'custom_flag' => $utils->parseBoolToFlag($params['diyflag']),
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
            'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
            'options' => '',
            'usemode' => $usemode,
            'lanfree_name_list' => "",
            'lanfree_nickname_list' => "",
            'mount_params' => '',
            'use_ssl_flag' => $utils->parseBoolToFlag($params['usesslflag']),
            'service_endpoint' => $params['servernode']
        );
        //添加告警配置
        $msg['warning_flag'] = $utils->parseBoolToFlag($warningSetting['power']);
        $msg['warning_type'] = Xphp::$_config['FLAG']['UNSET']; //云存储默认按大小告警
        $msg['warning_value'] = intval($params['limitsize']) * 1024 * 1024 * 1024 * 1024;

        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $opName = "NODE_SR_OP_ADD_PARALLEL_FILESYSTEM";

        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        //返回结果到UI
        if($result){
            //添加云存储和租户内部用户关联
            if(!empty($_SESSION['tenantuuid'])){
                $sql = "select storage_uuid from bd_storage_resource where storage_nickname = ? and storage_type =?";
                $data = $this->dbSelect($sql, array($nickname, Xphp::$_config['BD_STORAGE_TYPE']['FILE_SYSTEM']));
                if(!empty($data)){
                    $userHandler = Xphp::instance('UsersHandler');
                    $userHandler->addUserStorage(Xphp::$_user['useruuid'], array('resourceuuid' => $data[0]['storage_uuid']));
                }
            }

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
     * 添加云存储
     * @param unknown $params
     */

     public function addCloudStorage($params){
        //权限检查
        $storageType = $params['storagetype'];
        $usemode = intval($params['usemode']);
        $nickname = $params['rname'];
        $params['folder'] = html_entity_decode($params['folder']);
        //检查别名
        $this->checkStorageNameEmpty($nickname);
        $this->paramsCheck($storageType);
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag(false);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        $this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['UI_STORAGE_ADD']); //检查存储名是否重复
        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $limit_size = intval(sprintf("%.0f", intval($params['limitsize']) * 1024 * 1024 * 1024 * 1024));
        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'vendor' => $params['vendor'],
            'region' => $params['region'],
            'username' => $params['username'],
            'passwd' => $params['password'],
            'bucket' => $params['bucket'],
            'folder_path' => $params['folder'],
            'limit_size' => $limit_size,
            'custom_flag' => $utils->parseBoolToFlag($params['diyflag']),
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'options' => '',
            'usemode' => $usemode,
            'lanfree_name_list' => "",
            'lanfree_nickname_list' => "",
            'mount_params' => '',
            'use_ssl_flag' => $utils->parseBoolToFlag($params['usesslflag']),
            'service_endpoint' => $params['servernode']
        );
        //添加告警配置
        $msg['warning_flag'] = $utils->parseBoolToFlag($warningSetting['power']);
        $msg['warning_type'] = Xphp::$_config['FLAG']['UNSET']; //云存储默认按大小告警
        $msg['warning_value'] = $limit_size;
        // if(Xphp::$_config['STORAGEWARNINGTYPE']['SIZE'] == $msg['warning_type']){
        //     //如果是按照大小来告警
        //     $msg['warning_value'] = intval($warningSetting['value']) * 1024 * 1024 * 1024;
        // }else{
        //     $msg['warning_value'] = intval($warningSetting['value']);
        // }

        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $opName = "NODE_SR_OP_SCAN_CLOUD_STORAGE";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), true);
    	$result = $mbResult['result'];
    	$operate = $this->getUnifyOpcodeDes($opName);
        $message = $mbResult['msg'];
        if(!$result){
        	return $this->muOpResult($result, $operate, "", 'warning', $mbResult['errorCode']);
        }
        if($message['import_flag'] == Xphp::$_config['FLAG']['SET']){
        	$info = array(
        			'importflag' => $message['import_flag'],
        			'timepointcount' => intval($message['timepoint_count'])
            );
        	return json_encode($info);
        }else{
        	$msg = array(
                'storagetype' => $storageType,
                'rname' => $nickname,
                'vendor' => $params['vendor'],
                'region' => $params['region'],
                'username' => $params['username'],
                'password' => $params['password'],
                'bucket' => $params['bucket'],
                'folder' => $params['folder'],
                'limitsize' => $params['limitsize'],
                'diyflag' => $params['diyflag'],
                'import' => $importFlag,
                'usemode' => $usemode,
        	    'warningSettings' => $warningSetting,
        	    'usesslflag' => $params['usesslflag'],
        	    'servernode' => $params['servernode']
        	);
        	return $this->addCloudStorageConfirm($msg);
        }
     }

     /**
      * 添加云存储确认
      * @param unknow $params
      */
     public function addCloudStorageConfirm($params){
        //权限检查
        $storageType = $params['storagetype'];
        $usemode = intval($params['usemode']);
        $nickname = $params['rname'];
        $this->paramsCheck($storageType);
        $params['folder'] = html_entity_decode($params['folder']);
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag(false);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
         $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
         $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        $this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['UI_STORAGE_ADD']); //检查存储名是否重复
        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');

        $limit_size = intval(sprintf("%.0f", intval($params['limitsize']) * 1024 * 1024 * 1024 * 1024));

        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'vendor' => $params['vendor'],
            'region' => $params['region'],
            'username' => $params['username'],
            'passwd' => $params['password'],
            'bucket' => $params['bucket'],
            'folder_path' => $params['folder'],
            'limit_size' => $limit_size,
            'custom_flag' => $utils->parseBoolToFlag($params['diyflag']),
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
            'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
            'options' => '',
            'usemode' => $usemode,
            'lanfree_name_list' => "",
            'lanfree_nickname_list' => "",
            'mount_params' => "",
            'use_ssl_flag' => $utils->parseBoolToFlag($params['usesslflag']),
            'service_endpoint' => $params['servernode']
        );
        //添加告警配置
        $msg['warning_flag'] = $utils->parseBoolToFlag($warningSetting['power']);
        $msg['warning_type'] = Xphp::$_config['FLAG']['UNSET']; //云存储默认按大小告警
        $msg['warning_value'] = $limit_size;

        $nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');
        $opName = "NODE_SR_OP_ADD_CLOUD_STORAGE";
    	$mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    	$result = $mbResult['result'];
    	$operate = $this->getUnifyOpcodeDes($opName);
    	$msg = $mbResult['msg'];

    	$pfDes = include APP_PATH . "platform/PFDescription.php";
    	$usemodeDes = $pfDes['STORAGE_USE_DES'][$usemode];
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeInfo = $nodeHandler->getNodeNameWithUUID($nodeuuid);
    	$descriptionParam = array($usemodeDes, $nickname, $nodeInfo['name'], $nodeInfo['ip']);
    	//返回结果到UI
    	if($result){
    	    //添加云存储和租户内部用户关联
    	    if(!empty($_SESSION['tenantuuid'])){
    	        $sql = "select storage_uuid from bd_storage_resource where storage_nickname = ? and storage_type =?";
    	        $data = $this->dbSelect($sql, array($nickname, Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']));
    	        if(!empty($data)){
    	            $userHandler = Xphp::instance('UsersHandler');
    	            $userHandler->addUserStorage(Xphp::$_user['useruuid'], array('resourceuuid' => $data[0]['storage_uuid']));
    	        }
    	    }

//     	    $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_ADD', $descriptionParam);
    		return $this->muOpResult($result, $operate, $msg);
    	}else{
//     	    $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_ADD', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
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
     * 添加COPY存储
     * @param unknown $params
     */
    public function addCopyStorage($params){
        //TODO 这里参考NAS是否做检查
        //权限检查
        $storageType = $params['storagetype'];
        $usemode = intval($params['usemode']);
        $nickname = $params['rname'];
        $storage_list_uuid = $params['storage_list_uuid'];
        $password = md5(base64_decode($params['password']));
        //检查别名
        $this->checkStorageNameEmpty($nickname);
        $this->paramsCheck($storageType);
        $this->checkRemoteIP($params['remoteip']);
        $utils = Xphp::instance('Utils');
        $warningSetting = $params['warningSettings'];
        $formatFlag = $utils->parseBoolToFlag(false);
        $importFlag = $utils->parseBoolToFlag($params['import']);
        $lanfreeFlag = $utils->parseBoolToFlag(false);
        $this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['UI_STORAGE_ADD']); //检查存储名是否重复
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        // 这里判断处理下是否是ipv6
        if (filter_var($params['remoteip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // 是 ipv6
            $params['remoteip'] = inet_ntop(inet_pton($params['remoteip'])); // 获取缩写后的ipv6
        }

        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'remote_ip' => $params['remoteip'],
            'remote_port' => $params['remoteport'],
            'username' => $params['username'],
            'password' => $password,
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'options' => '',
            'usemode' => $usemode,
            'lanfree_name_list' => "",
            'lanfree_nickname_list' => "",
            'mount_params' => '',
            'storage_uuid_list' => $storage_list_uuid,
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
        // var_dump(json_encode($msg));
        // exit();
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
     * 检查添加的IP是否属于备份节点
     * @param string $ip
     */
    private function checkRemoteIP($ip){
        $sql = "select ip from bd_node";
        $data = $this->dbSelect($sql);
        if(!empty($data)){
            foreach ($data as $d){
                $ipList = explode(" ", $d['ip']);
                foreach ($ipList as $nodeIp){
                    if($ip == $nodeIp){
                        exit($this->muOpResult(false, Xphp::$_lang['WEB_NODE_OP_ADD_REMOTE_SYSTEM'], Xphp::$_lang['WEB_STORAGE_INPUT_WRONG_IP'], 'warning'));
                    }
                }
            }
        }

    }

   	/**
   	 * 添加副本存储确认
   	 * @param unknown $params
   	 * @return string
   	 */
    public function addCopyStorageConfirm($params){
        //权限检查
//         $roleHandler = Xphp::instance('RoleHandler');
//         $roleHandler->pOperationPermissionCheckExit("p_storage_manager_add");
    	$storageType = $params['storagetype'];
    	$rawDevPath = $params['storagename'];
    	$nickname = $params['rname'];
    	$usemode = $params['usemode'];
    	$this->paramsCheck($storageType);
    	$utils = Xphp::instance('Utils');
    	$warningSetting = $params['warningSettings'];
    	$formatFlag = $utils->parseBoolToFlag(false);
    	$importFlag = $utils->parseBoolToFlag($params['import']);
    	$lanfreeFlag = $utils->parseBoolToFlag(false);
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
    	$this->checkStorageName($nickname, $lanfreeFlag, Xphp::$_lang['UI_STORAGE_ADD']); //检查存储名是否重复
    	$nodeuuid = Xphp::instance('NodeHandler', 'getLocalNodeUUID');

        // 这里判断处理下是否是ipv6
        if (filter_var($params['remoteip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // 是 ipv6
            $params['remoteip'] = inet_ntop(inet_pton($params['remoteip'])); // 获取缩写后的ipv6
        }

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
            'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
            'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
			'options' => '',
			'usemode' => $usemode,
    	    'lanfree_name_list' => "",
    	    'lanfree_nickname_list' => "",
    	    'mount_params' => ""
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

    	$pfDes = include APP_PATH . "platform/PFDescription.php";
    	$usemodeDes = $pfDes['STORAGE_USE_DES'][$usemode];
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$nodeInfo = $nodeHandler->getNodeNameWithUUID($nodeuuid);
    	$descriptionParam = array($usemodeDes, $nickname, $nodeInfo['name'], $nodeInfo['ip']);
    	//返回结果到UI
    	if($result){
//     	    $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_ADD', $descriptionParam);
    		return $this->muOpResult($result, $operate, $msg);
    	}else{
//     	    $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_ADD', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
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
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_storage_manager_delete");
        $storageuuid = $params['uuids'];
        $storageList = array();
        $resourceList = array();
        foreach($storageuuid as $uuid){
            $storageList[] = array(
                'storage_uuid' => $uuid
            );
            $resourceList[] = array(
                'resouceuuid' => $uuid
            );
        }
        $storageType = $this->getTypebyUUID($storageuuid[0]);
        $nodeuuid = $this->getStorageInNode($storageuuid[0]);
        //得到节点的状态
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeStatus = $nodeHandler->getNodeAllStatus($nodeuuid);
        if($nodeStatus['flag']){
            //如果节点在线,发送删除消息到节点进程处理
            $msg = array("storage_uuid_list" => $storageList);
            $opName = "NODE_SR_OP_DELETE";
            if($storageType == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']){
            	$opName = "NODE_SR_OP_DELETE_REMOTE_SYSTEM";
            }
            //如果存储类型是华为CBR
            if($storageType == Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR'] ){
                $opName = "NODE_HUAWEI_CBR_OP_DELETE";
            }
            //进行当前存储是否有任务在使用
            foreach ($storageuuid as $uuid){
                $this->checkStorageInRunningTask($uuid);

            }
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
            foreach ($storageuuid as $uuid){
                $this->checkStorageDeleteResult($uuid, $mbResult);
            }
            return $this->unifyMuOpResult($opName, $mbResult);
        }
        //节点不在线,检测是否有正在运行的任务使用该存储,如果有返回,没有的话直接删除存储和存储上的时间点记录
        $result = true;
        foreach ($storageuuid as $uuid){
            $this->checkStorageInRunningTask($uuid);
            $result = $result && $this->deleteStorageByWeb($uuid);
        }
        //添加云存储和租户内部用户关联
        if(!empty($_SESSION['tenantuuid'])){
            $sql = "select storage_uuid from bd_storage_resource where storage_nickname = ? and storage_type =?";
            $data = $this->dbSelect($sql, array($nickname, Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']));
            if(!empty($data)){
                $userHandler = Xphp::instance('UsersHandler');
                $userHandler->deleteUserStorage(Xphp::$_user['useruuid'], $resourceList);
            }
        }

        return $this->muOpResult($result, Xphp::$_lang['WEB_NODE_OP_DELETE']);

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

        return $result;
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
        $sql = "select count(task_name) as timepoint, sum(write_size) as size, task_uuid from bd_backup_timepoint 
                where storage_uuid = ? and deleted_flag = ? and available_flag = ?";
        $sqluuid = "select task_uuid from bd_backup_timepoint 
        where storage_uuid = ? and deleted_flag = ? and available_flag = ?";
        $sqlParams = array($params['uuids'][0], Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['FLAG']['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $datauuid = $this->dbSelect($sqluuid, $sqlParams);
        $utils = Xphp::instance('Utils');
        $timepointCount = $data[0]['timepoint'];
        $timepointSize = $utils->calSize($data[0]['size']);
        $recovertaskuuids = array();
        foreach($datauuid as $d){
            if(!in_array($d['task_uuid'],$recovertaskuuids)){
                $recovertaskuuids[] = $d['task_uuid'];
            }
        }
        //检测备份任务
        $sql = "select task_name from bd_task where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($params['uuids'][0]));
        $taskCount = count($data);
        $taskname = array();
        foreach ($data as $d){
            $taskname[] = $d['task_name'];
        }
        //获取存储类型
        $sql1 = "select storage_type from bd_storage_resource where storage_uuid = ?";
        $data1 = $this->dbSelect($sql1, array($params['uuids'][0]));
        $storage_type =  $data1[0]['storage_type'];


        if($storage_type == Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']){
            //如果是华为CBR 需要用以下方法判断是否存在时间点
            //下云同步
            $canflag =  true; //此标志代表存储可删
            $sql2 = "select vt.detail,bt.task_uuid from vm_task vt, bd_task bt where bt.task_uuid = vt.task_uuid and vt.hypervisor_type = ? and bt.task_type = ? and bt.task_status = ? ";
            $sql2Params = array(Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CBR'], Xphp::$_config['TASKTYPE']['VM_HUAWEI_CBR_SYNC'], Xphp::$_config['TASKSTATUS']['RUNNING']);
            $data2 = $this->dbSelect($sql2, $sql2Params);
            //获取storage_uuid
            $synctaskuuids = array();
            foreach($data2 as $d){
                $detail =  json_decode($d['detail'],true);
                $synclist  = $detail['cbr_sync_list'];
                foreach($synclist as $syncitem){
                    if($syncitem['storage_uuid'] == $params['uuids'][0] ){
                        $canflag =  false;
                        if(!in_array($d['task_uuid'],$synctaskuuids)){
                            $synctaskuuids[] = $d['task_uuid'];
                        }
                    }
                }
            };
            //设置taskname
            if(!empty($synctaskuuids)){
                $taskuuidsStr1 = implode("', '", $synctaskuuids);
                $sql3 = "select task_name from bd_task where task_uuid in ('" .$taskuuidsStr1. "')";
                // var_dump("sql311",$sql3);
                $data3 = $this->dbSelect($sql3);
                foreach ($data3 as $d){
                    $taskname[] = $d['task_name'];
                }
                $taskCount += count($data3);
            }
            //下云恢复
            //判断时间点 可以用上面的第一条sql 语句
            if($timepointCount != 0){
                $recoveryflag =  true;
                //代表有下云恢复任务 找到task_uuid
                $taskuuidsStr = implode("', '", $recovertaskuuids);
                $sql3 = "select task_name from bd_task where task_uuid in ('" .$taskuuidsStr. "') and task_status = ?";
                $data3 = $this->dbSelect($sql3,array(Xphp::$_config['TASKSTATUS']['RUNNING']));
                foreach ($data3 as $d){
                    $taskname[] = $d['task_name'];
                }
                $taskCount += count($data3);
            }
            if($recoveryflag){
                //如果是有下云恢复任务
                if($taskCount == 0){
                    return $this->deleteStorage($params);
                }
            }
            else{
                    //下云同步任务
                    if($timepointCount == 0 && $taskCount == 0 && $canflag){
                    return $this->deleteStorage($params);
                }
            }

        }else{
            //其他存储可以直接这样
            if($timepointCount == 0 && $taskCount == 0){
                //如果没有数据和任务,直接删除
                return $this->deleteStorage($params);
            }
        }
        $info = array(
            'timepointCount' => $timepointCount,
            'timepointSize' => $timepointSize,
            'taskCount' => $taskCount,
            'tasks' => $taskname,
            'storageType' => $storage_type,
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
    public function getUnifyOpcodeDes($opCode){
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
        $cloudFlag = $params['cloudflag'];//用于云存储初始化可用存储列表
        $copybackFlag = $params['copybackflag'];//用于副本拉回初始化可用存储列表
        $archivebackFlag = $params['archivebackflag']; //用于归档回传初始化化可用存储
        $labFlag = $params['labFlag']; //用于创建内嵌虚拟演练室
        $backupFlag = false;
        $nodeuuid = $params['nodeuuid'];
        $sql = "select mount_flag, node_uuid, storage_nickname, storage_uuid, storage_type, total_size, free_size, status, error_code 
                from bd_storage_resource where status = ? and mount_flag = ? 
                and error_code = ? and lan_free_flag = ? ";
        $sqlParams = array();
        if (($_SESSION['isThreePowers'] && $_SESSION['userLevel'] == 5) || (!$_SESSION['isThreePowers'] && $_SESSION['userLevel'] != 1)) {
            // 三权模式下的操作员只能查看分配的存储列表  非三权非admin
            $resourceHandler =  Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
            $sqlNew = !empty($resourceInfo) ? array_column($resourceInfo, 'resource_uuid') : [];
            $sqlNew = " and storage_uuid in ('" . implode("','", $sqlNew) . "') ";
            $sql .= $sqlNew;
        }
        if($copyFlag){ //副本目的存储
        	$sql .= " and use_mode = ? and storage_type not in (8,9)";
        	$sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        			Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['BD_STORAGE_USE_MODE']['COPY']);
        }else if($labFlag){ //副本目的存储
            $sql .= " and node_uuid = ? and storage_type not in (8,9,10) and use_mode != 5";
            $sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'], Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET'], $nodeuuid);
        }else if($copybackFlag){ //副本回传存储
        	$sql .= " and node_uuid = ? and use_mode in (1,2) and storage_type not in (8,9)";
        	$sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        			Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET'],$nodeuuid);
        }else if($archivebackFlag){ //归档回传存储
            $sql .= " and node_uuid = ? and use_mode in (1,3) and storage_type not in (8,9)";
            $sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
                Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET'],$nodeuuid);
        }else if($cloudFlag){ //归档存储
            $sql .= " and use_mode in (". Xphp::$_config['BD_STORAGE_USE_MODE']['COPY'] . "," . Xphp::$_config['BD_STORAGE_USE_MODE']['ARCHIVE'] . ") and storage_type not in (8,9)";
        	$sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        			Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET']);
        }else{ //备份存储
            $backupFlag = true;
        	$sql .= " and use_mode in (". Xphp::$_config['BD_STORAGE_USE_MODE']['UNKNOWN'] . "," . Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'] . ") and storage_type not in (8)";
        	$sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
        	    Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET']);
        }


        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $utils = Xphp::instance("Utils");
        $nodeHandler = Xphp::instance('NodeHandler');
        $systemHandler = Xphp::instance('SystemHandler');
        $permission = $systemHandler->getExtensionLicense();
        $permissionArr = $permission['p'];
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($_SESSION['tenantuuid'])){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $userHandler =  Xphp::instance('UsersHandler');
            $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
            if(!empty($resourceInfo)){
                foreach ($resourceInfo as $r){
                    $resourceList[] = $r['resource_uuid'];
                }
            }

            //获取用户配额

        }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            if(!empty($_SESSION['tenantuuid']) && !in_array($d['storage_uuid'], $resourceList)) continue;

            //如果当前存储不是云存储的情况
            if($d['storage_type'] != Xphp::$_config['BD_STORAGE_TYPE']['CLOUD']){
                //排除不是当前节点的存储
                if($backupFlag && $d['node_uuid'] != $nodeuuid) continue;
            }else{
                //是云存储
                if(!$backupFlag) continue;
            }

        	$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
        	$storageStatus = $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
        	if($storageStatus != Xphp::$_config['STORAGE_STATUS']['ONLINE']) continue;
            if (!in_array('tape_manage', $permissionArr) && $d['storage_type'] == 10) {
                //磁带没有授权不显示
                continue;
            }
        	$nodeName = $this->getNodeName($d['node_uuid']);
        	$name = "";
        	//副本|归档没有选中节点，需要显示节点信息
        	if($copyFlag || $copybackFlag || $cloudFlag || $archivebackFlag){
        		$name = ", ".Xphp::$_lang['WEB_PLATFORM_DES_NODE'].': '.$nodeName;
        	}
        	$storageDes = $d['storage_nickname'];
        	if(empty($_SESSION['tenantuuid'])){
        	    $storageDes .= "(" . $this->getStorageTypeDes($d['storage_type']) . ", ".Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($d['total_size']) .
        	    ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($d['free_size']) . ")";
        	}
        	$storageDes .= $name;

            $info[] = array(
                'uuid' => $d['storage_uuid'],
                'text' => $storageDes,
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

        $sql = "select task_uuid, task_name, module_type, task_type, task_create_time, count(task_uuid) as point, 
                sum(write_size) as size from bd_backup_timepoint  
                where deleted_flag = ? and available_flag = ? and import_flag = ? and user_uuid = ? group by task_uuid 
                order by $sortArr[$sortColumn] $sortType limit ? , ? ";

        $flag = Xphp::$_config['FLAG'];
        $data = $this->dbSelect($sql, array($flag['UNSET'], $flag['SET'], $flag['SET'], Xphp::$_user['useruuid'], $start, $length));


        $sql = "select task_uuid, task_name, module_type, task_create_time, count(task_uuid) as point,
        sum(write_size) as size from bd_backup_timepoint
        where deleted_flag = ? and available_flag = ? and import_flag = ? and user_uuid = ? group by task_uuid
        order by $sortArr[$sortColumn] $sortType ";
        $dataCount = $this->dbSelect($sql, array($flag['UNSET'], $flag['SET'], $flag['SET'], Xphp::$_user['useruuid']));
        $records = array();
        $records["data"] = array();
        $countNum = count($dataCount);
        $i=0;
        $utils = Xphp::instance("Utils");
        $logHandler = Xphp::instance('LogHandler');
        foreach ($data as $d){
            $details = $this->getTaskPointDetails($d['task_uuid'], $d['module_type']);
            $records['data'][] = array(
                '<input type="checkbox" name="id[]" value="'. $d['task_uuid'] .'">',
                ++$i,
                $d['task_name'],
                $logHandler->getModuleTypeDes(intval($d['module_type']), null, $d['task_type']),
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
            return $this->getFSPointDetails($taskuuid, $module_type);
        }else if(intval($module_type) == Xphp::$_config['MODULE_TYPE']['VM']){
            //虚拟机
            return $this->getVMPointDetails($taskuuid, $module_type);
        }else if(intval($module_type) == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']){
        	//副本|归档
        	return $this->getCopyPointDetails($taskuuid, $module_type);
        }else if(intval($module_type) == Xphp::$_config['MODULE_TYPE']['DB']){
            //数据库
            return $this->getDBPointDetails($taskuuid, $module_type);
        }else if(intval($module_type) == Xphp::$_config['MODULE_TYPE']['OS']){
            //操作系统
            return $this->getOSPointDetails($taskuuid, $module_type);
        }else if(intval($module_type) == Xphp::$_config['MODULE_TYPE']['NAS']){
            //nas
            return $this->getFSPointDetails($taskuuid, $module_type);
        }
    }

    /**
     * 获取数据库的备份列表
     * @param string $taskuuid
     */
    private function getDBPointDetails($taskuuid, $module_type){
        $sql = "select distinct dbt.dir_path from bd_backup_timepoint bbt, db_backup_timepoint dbt
                where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $path = array();
        foreach ($data as $l){
            $path[] = $l['dir_path'];
        }
        $logHandler = Xphp::instance('LogHandler');
        $info = array(
            'moduleDes' => $logHandler->getModuleTypeDes($module_type),
            'detailsDes' => Xphp::$_lang['WEB_STORAGE_BACKUP_DB_LIST'],
            'details' => $path
        );
        return $info;
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
        //先查询任务类型
        $sqlTask_type = "SELECT DISTINCT(task_uuid),task_type FROM bd_backup_timepoint where task_uuid = ?";
        $resultTask_type = $this->dbSelect($sqlTask_type,array($taskuuid));
        //得到任务类型,根绝任务类型来判断是什么类型
        $task_type = $resultTask_type[0]['task_type'];
        $logHandler = Xphp::instance('LogHandler');
        $sql = "";
        $detailsDes = "";
        $path = array();
        switch ($task_type){
            //虚拟机
            case Xphp::$_config['TASKTYPE']['BACKUP_COPY']:
            case Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']:
            case Xphp::$_config['TASKTYPE']['ARCHIVE']:
            case Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']:
                $sql = "select bbt.task_type, vbt.dir_path as dir_path from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ?";
                $detailsDes = Xphp::$_lang['WEB_PLATFORM_DES_VM'];

                $data = $this->dbSelect($sql, array($taskuuid));
                $i = 0;//计数显示多少个数目
                foreach ($data as $d){
                    if(!in_array($d['dir_path'], $path) && $i <= 10){
                        $path[] = $d['dir_path'];
                        $i++;
                    }
                    if($i > 10){
                        $path[] = "...";
                        break;
                    }
                }
                break;

            //数据库
            case Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY']:
            case Xphp::$_config['TASKTYPE']['DB_BACKUP_COPY_FETCH']:

                $sql = "select bbt.task_type, vbt.dir_path as dir_path from db_backup_timepoint vbt, bd_backup_timepoint bbt
                where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ?";
                $detailsDes = Xphp::$_lang['WEB_PLATFORM_DES_DB'];

                $data = $this->dbSelect($sql, array($taskuuid));
                $i = 0;//计数显示多少个数目
                foreach ($data as $d){
                    if(!in_array($d['dir_path'], $path) && $i <= 10){
                        $path[] = $d['dir_path'];
                        $i++;
                    }
                    if($i > 10){
                        $path[] = "...";
                        break;
                    }
                }
                break;
            //文件
            case Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY']:
            case Xphp::$_config['TASKTYPE']['FILE_BACKUP_COPY_FETCH']:
                $sql = "select fbt.backup_path_list as dir_path from bd_backup_timepoint bbt, fs_backup_timepoint fbt  
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ?";
                $detailsDes = Xphp::$_lang['WEB_PLATFORM_DES_FS'];

                $data = $this->dbSelect($sql, array($taskuuid));
                $i = 0;
                foreach ($data as $d){
                    $dir_path_list = json_decode($d['dir_path'],true);
                    foreach ($dir_path_list as $op){
                        $dir_path = $op['backup_path'];
                        if(!in_array($dir_path, $path) && $i <= 10){
                            $path[] = $dir_path;
                            $i++;
                        }
                        if($i > 10){
                            $path[] = "...";
                            break;
                        }
                    }
                    if($i > 10){
                        break;
                    }
                }
                break;
            case Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY']:
            case Xphp::$_config['TASKTYPE']['OS_BACKUP_COPY_FETCH']:
                $sql = "select distinct(obt.agent_uuid), bbt.task_type, obt.agent_ip, obt.os_name, obt.dir_path as dir_path from os_backup_timepoint obt, bd_backup_timepoint bbt
                where bbt.timepoint_uuid = obt.timepoint_uuid and bbt.task_uuid = ?";
                $detailsDes = Xphp::$_lang['WEB_PLATFORM_DES_OS'];
                $data = $this->dbSelect($sql, array($taskuuid));
                $oshandler = Xphp::instance('OsHandler');
                $agentList = $oshandler->getAllAgentList();
                $i = 0;//计数显示多少个数目
                foreach ($data as $d){
                    if(!in_array($d['os_name']."(".$d['agent_ip'].")", $path) && $i <= 10){
                        $path[] = $oshandler->getAgentNameByList($agentList,$d['agent_uuid'],$d['os_name'], $d['agent_ip']);
                        $i++;
                    }
                    if($i > 10){
                        $path[] = "...";
                        break;
                    }
                }
                break;
        }

        $moduleDes = $logHandler->getModuleTypeDes($module_type,'',$task_type);
    	$info = array(
    	    'moduleDes' => $moduleDes,
    	    'detailsDes' => $detailsDes.$moduleDes,
			'details' => $path
    	);
    	return $info;
    }

    /**
     * 获取操作系统的备份列表
     * @param string $taskuuid
     */
    private function getOSPointDetails($taskuuid, $module_type){
        $sql = "select distinct(obt.agent_uuid),obt.agent_ip, obt.os_name from os_backup_timepoint obt, bd_backup_timepoint bbt 
                where obt.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $oshandler = Xphp::instance('OsHandler');
        $agentList = $oshandler->getAllAgentList();
        $nameIP = array();
        foreach ($data as $each){
            $nameIP[] = $oshandler->getAgentNameByList($agentList,$each['agent_uuid'],$each['os_name'], $each['agent_ip']);
        }
        $logHandler = Xphp::instance('LogHandler');
        $info = array(
            'moduleDes' => $logHandler->getModuleTypeDes($module_type),
            'detailsDes' => Xphp::$_lang['UI_DB_BACKUP_AGENT_LIST'],
            'details' => $nameIP
        );
        return $info;
    }

    /**
     * 获取文件的备份列表
     * @param string $taskuuid
     */
    private function getFSPointDetails($taskuuid, $module_type){
        $sql = "select distinct(fbt.agent_uuid),fbt.agent_ip, fbt.agent_name from fs_backup_timepoint fbt, bd_backup_timepoint bbt 
                where fbt.fs_timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $nameIP = array();
        if($module_type == Xphp::$_config['MODULE_TYPE']['FS']) {
            foreach ($data as $each){
                $nameIP[] = $each['agent_name']."(".$each['agent_ip'].")";
            }
        }else {
            foreach ($data as $each){
                $nameIP[] = $each['agent_ip']."(".$each['agent_name'].")";
            }
        }

        $logHandler = Xphp::instance('LogHandler');
        $info = array(
            'moduleDes' => $logHandler->getModuleTypeDes($module_type),
            'detailsDes' => Xphp::$_lang['UI_DB_BACKUP_AGENT_LIST'],
            'details' => $nameIP
        );
        return $info;
    }

    /**
     * 分配导入数据到其他用户
     * @param unknown $params
     */
    public function distributeOldData($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_storage_manager_data");
        $taskuuids = $params['uuids'];
        $useruuid = $params['useruuid'];
        $username = $params['username'];
        $this->paramsCheck($taskuuids, $useruuid);
        $taskuuidsStr = implode("','", $taskuuids);
        $sqlCount = "select count(timepoint_uuid) as point_num from bd_backup_timepoint where user_uuid = ? and task_uuid in ('$taskuuidsStr')";
        $dataCount = $this->dbSelect($sqlCount, array($useruuid));
        $sql = "update bd_backup_timepoint set user_uuid = ?, user_name = ?, import_flag = ?  where user_uuid = ? and task_uuid in ('$taskuuidsStr')";
        $result = $this->dbExec($sql, array($useruuid, $username, Xphp::$_config['FLAG']['UNSET'], Xphp::$_user['useruuid']));
        $operate = Xphp::$_lang['WEB_STORAGE_DATA_TO_USER'] . $username;
        $descriptionParams = array($username, intval($dataCount[0]['point_num']));
        $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_DISTRIBUTION_IMPORT_DATA', $descriptionParams);
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
        	and import_flag = ?  order by backup_mode desc";
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
            $descriptionParams = array(count($data));
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_DELETE_IMPORT_DATA', $descriptionParams);
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
    	$sql = "select node_uuid, total_size from bd_storage_resource where lan_free_flag = ? and use_mode not in (2,3) ";
    	$sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
    	$data = $this->dbSelect($sql, $sqlParams);

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
		$maxSpace = array_sum($nodeStorage);
		//租户内计算租户的配额
	    if(!empty($_SESSION['tenantuuid'])){
	        $tenantHandler = Xphp::instance('TenantHandler');
	        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
	        $maxSpace = $settings['common']['quotaSize'];
	        if(empty($maxSpace)){
	            $maxSpace = 0;
	        }
	    }else{
	       //租户外检查是否为容量授权
	        $systemHandler = Xphp::instance('SystemHandler');
	        $liceseTypeInfo = $systemHandler->getSystemLicenseType();
	        $liceseTypeInfo = json_decode($liceseTypeInfo, true);
	        $liceseType = $liceseTypeInfo['licensetype'];
	        //容量授权配额按容量授权总大小显示
	        if($liceseType == Xphp::$_config['LISENCE_INFO']['type']['storage']){
	            $maxSpace = $liceseTypeInfo['storage_size'];
	        }

	    }
	    if($maxSpace == -1){
	        $result = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
	    }else{
	        $result = $utils->calSize($maxSpace);
	    }
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
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'bn.host_name', '', '', '', '', '', 'storage_percent');
        $sortColumnStr = $sortArr[$sortColumn];
        $orderByStr = $sortColumnStr ? "order by {$sortColumnStr} {$sortType}" : '';
    	$sql = "select bn.node_uuid, bn.host_name, bn.ip, bn.node_nickname,count(bt.task_uuid) as taskcount, (total_size - free_size)/total_size as storage_percent 
                from bd_node bn left join bd_task bt on bn.node_uuid = bt.node_uuid 
                left join bd_storage_resource bs on bn.node_uuid = bs.node_uuid and bs.lan_free_flag = ? 
                group by bn.node_uuid {$orderByStr} limit ?, ?";
    	$sqlCount = 'select count(distinct bn.node_uuid) as total from bd_node bn left join bd_task bt on bn.node_uuid = bt.node_uuid';
    	$data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['UNSET'], $start,$length));
    	$dataCount = $this->dbSelect($sqlCount,array());
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$utils = Xphp::instance("Utils");
    	$records = array();
    	$records["data"] = array();
    	$id = $start + 1;
    	foreach ($data as $d){
    		$storageInfo = $this->getNodeStorage($d['node_uuid']);
            $storageNum = $storageInfo['storage_num'];
    		$usedSize = $storageInfo['total_size'] - $storageInfo['free_size'];
    		$records["data"][] = array(
                    $id++,
                    $nodeHandler->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']),
                    $d['ip'],
                    $storageInfo['storage_num'] ==0? $d['taskcount']: $d['taskcount']/$storageInfo['storage_num'],
                    $storageNum,
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

    /**
     * 获取存储用途描述
     * @param int $usemode
     * @return string|mixed
     */
    public function getUsemodeDes($usemode){
    	$des = "";
    	switch ($usemode){
            //华为cbr默认选择--
            case 0:
                $des = "--";
                break;
    		case 1:
    			$des = Xphp::$_lang['UI_PLATFORM_BACKUP'];
    			break;
    		case 2:
    			$des = Xphp::$_lang['WEB_PLATFORM_DES_COPY'] . '|' . Xphp::$_lang['UI_PLATFORM_ARCHIVE'];
    			break;
    		case 3:
    			$des = Xphp::$_lang['UI_PLATFORM_ARCHIVE'];
    			break;
    		case 4:
    		    $des = "NAS";
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
    public function getNodeName($nodeuuid){
    	$sql = "select ip, node_nickname, host_name from bd_node where node_uuid = ?";
    	$data = $this->dbSelect($sql, array($nodeuuid));
    	$nodeHandler = Xphp::instance('NodeHandler');
    	$name = $nodeHandler->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']);

    	return $name;
    }


    /**
     * 获取云存储bucket folder列表
     * @param unknow $params
     */
    public function getBucketFolder($params){
        $vendor = intval($params['vendor']);
        $region = $params['region'];
        $username = $params['username'];
        $password = $params['password'];
        $bucket = $params['bucket'];
        $utils = Xphp::instance('Utils');
        $msg = array(
            'vendor' => $vendor,
            'region' => $region,
            'username' => $username,
            'passwd' => $password,
            "bucket" => $bucket,
            "use_ssl_flag" => $utils->parseBoolToFlag($params['usesslflag']),
            "service_endpoint" => $params['servernode']
        );
        $opName = "NODE_SR_OP_GET_CLOUD_FOLDER";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);

        $opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);
        $info = array();
        if(!$mbResult['result']){
            return $this->muOpResult(false, $opcodeDes, '', '', $mbResult['errorCode']);
        }

        $folderList = $mbResult['msg']['folder_list'];
        foreach($folderList as $folder){
            // 去掉 斜杠
            //$folder = str_replace('/', '', $folder);
            $info[] = array(
                'text' => $folder,
                'uuid' => $folder
            );
        }

        return json_encode($info);

    }

    /**
     * 获取云存储region
     * @param unknown $params
     */
    public function getCloudRegion($params){
        $vendor = intval($params['vendor']);
        switch ($vendor){
            case 1:
                //aws
                $region = Xphp::$_cloud['CLOUD_STORAGE_REGION']['AWS_REGION'];
                break;
            case 3:
                //ali
                $region = Xphp::$_cloud['CLOUD_STORAGE_REGION']['ALI_REGION'];
                break;
            case 4:
                //huawei
                $region = Xphp::$_cloud['CLOUD_STORAGE_REGION']['HUAWEI_REGION'];
                break;
            case 5:
                //tencent
                $region = Xphp::$_cloud['CLOUD_STORAGE_REGION']['TENCENT_REGION'];
                break;
            case 6:
                //ceph s3
                break;
            case 7:
                //wasabi
                $region = Xphp::$_cloud['CLOUD_STORAGE_REGION']['WASABI_REGION'];
                break;
        }

        $info = array();
        foreach ($region as $r){
            $info[] = array(
                'text' => Xphp::$_lang[$r['text']],
                'value' => $r['value']
            );
        }
        return json_encode($info);
    }

    /**
     * 检查名字是否存在
     * @param string $name
     * @param int $flag
     */
    private function checkStorageName($name, $flag, $opname){
       $sql = "select storage_uuid from bd_storage_resource where storage_nickname = ? and lan_free_flag = ? ";
       $data = $this->dbSelect($sql, array($name, $flag));
       if(!empty($data)){
          exit($this->muOpResult(false, $opname, Xphp::$_lang['UI_STORAGE_NICKNAME_EXIST_ERROR'],'warning'));
       }
    }

    /**
     * 获取存储别名
     * @param string $uuid
     */
    private function getStorageNameByUUID($uuid){
        $sql = "select storage_nickname from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $name = "";
        if(!empty($data)){
            $name = $data[0]['storage_nickname'];
        }

        return $name;
    }

    /**
     * 获取修改云存储的剩余空间大小
     * @param string $storageuuid
     * @param bigint $warningValue
     */
    private function getCloudStorageFreeSize($storageuuid, $warningValue){
        $sql = "select total_size, free_size from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $usedSize = intval($data[0]['total_size']) - intval($data[0]['free_size']);
        return $warningValue - $usedSize;
    }

    /**
     * 存储别名检测
     * @param unknown $name
     */
    private function checkStorageNameEmpty($name){
        if(!$this->checkEmpty($name)){
            exit($this->muOpResult(false, Xphp::$_lang['UI_STORAGE_ADD'], Xphp::$_lang['UI_STORAGE_NICKNAME_EMPTY_TIPS'], 'warning'));
        }
    }

    /**
     * @description: 扫描异地备份系统得节点信息
     * @return {*}
     */
    public function getRemoteStorageTable($params){
        $username = $params['username'];
        $password = base64_decode($params['password']);
        $remoteip = $params['remoteip'];
        $remoteport = $params['remoteport'];
        $passwordMd5 = md5($password);
        $utils = Xphp::instance("Utils");
        $this->paramsCheck($username,$password,$remoteip,$remoteport);
        $opName = "NODE_SR_OP_SCAN_REMOTE_SYSTEM";
        $msg = array(
            'remote_ip' => $remoteip,
            'remote_port' => $remoteport,
            'username' => $username,
            'password' => $passwordMd5,
        );
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        // var_dump(json_encode($mbResult));
        // exit();
        $data = $mbResult['msg']['storage_list'];
        $info = array();
        foreach ($data as $d){
            $storage_config = json_decode($d['storage_config'],true);
            $info[] = array(
                '<input type="checkbox" name="id[]" value="' . $d['storage_uuid'] . '">',
                $d['name'],
                $d['storage_type'],
                $utils->calSize($d['size']),
                $storage_config['remote_node_type'] == 1 ? Xphp::$_lang['UI_STORAGE_REMOTE_NODE_MASTER'] . '(' . $storage_config['remote_node_ip'] . ')' : Xphp::$_lang['UI_STORAGE_REMOTE_NODE_SUB'] . '(' . $storage_config['remote_node_ip'] . ')' ,
            );
        }

        $records["data"] = $info;
        $records["draw"] = $params['draw'];
        $result = $mbResult['result'];
    	$operate = $this->getUnifyOpcodeDes($opName);
    	$msg = $mbResult['msg'];
    	//返回结果到UI
    	if($result){
            return  json_encode($records);
    	}else{
    		exit($this->muOpResult($result, $operate, Xphp::$_lang['WEB_ERROR_BD_USER_NAME_AND_PASSWD_NOT_MATCH'], '', $mbResult['errorCode']));
    	}
    }


    //--------------------------------------------------华为CBR新增方法start-------------------------------------------------------------------
    //获取CBR区域
    public function getCBRArea($params){
        $access_key_id = $params['access_key_id'];
        $secret_access_key = $params['secret_access_key'];
        $storage_uuid = "";//获取区域时默认传空
        $this->paramsCheck($access_key_id);
        $pfMsg['storage_uuid'] = $storage_uuid;
        $pfMsg['access_key_id']  = $access_key_id;
        $pfMsg['secret_access_key'] = $secret_access_key;

        $opName = "NODE_HUAWEI_CBR_OP_GET_REGIONS";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($pfMsg),true);

        //写入假数据
        //后续需要删除
        // $mbResult = array(
        //     "error_code" => 0,
        //     "result"=> true,
        //     "msg"=>array(
        //         "regions"=>[
        //             array(
        //                 "locales"=>array(
        //                     "en-us"=>"TR-Istanbul",
        //                     "zh-cn"=>"土耳其-伊斯坦布尔"
        //                 ),
        //                 "id"=> "tr-west-1"   // 区域id
        //             ),
        //             array(
        //                 "locales"=>array(
        //                     "en-us"=>"TR-Istanbul",
        //                     "zh-cn"=>"土耳其-伊斯坦布尔2"
        //                 ),
        //                 "id"=> "tr-west-2"   // 区域id
        //             )
        //         ]
        //     )
        // );
        $opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);
        if(!$mbResult['result']){
            return $this->muOpResult(false, $opcodeDes, '', 'info', $mbResult['errorCode']);
        }
        //添加成功
        $arealistnew  = array();
        //  var_dump("message",$mbResult);
        $arealist  = $mbResult['msg']['regions'];
        if(empty($arealist)){
            return  $this->muOpResult(false, $opcodeDes, Xphp::$_lang['WEB_STORAGE_NOT_GET_REGION'], 'info');
        }
        foreach($arealist  as $area){
            $arealistnew[] = array(
                "nameen"=>$area['locales']['en-us'],
                "namecn"=>$area['locales']['zh-cn'],
                "id"=>$area['id']
            );
        }
        return json_encode($arealistnew);
        // return  $this->muOpResult(true,'','','','',$arealistnew);
    }
    /**
   	 * 添加华为CBR存储
   	 * @param unknown $params
   	 * @return boolean
   	 */
    public function addHuaweiCBRStorage($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_storage_manager_add");
        //获取数据，强制转换数据，处理异常参数
        $utils = Xphp::instance('Utils');
        //获取名称
        $nickname = $params['rname'];
        //存储告警(前端没有这个值)
        $warning_flag = $utils->parseBoolToFlag(false);
        //获取告警类型（前端没有这个值）
        $warning_type = intval($params['type']);
        //告警阈值（前端没有这个值）
        $warning_value = intval($params['value']);
        //是否导入数据 （前端没有这个值）
        $import_flag = $utils->parseBoolToFlag(false);
        //是否格式化存储（前端没有这个值）
        $format_flag =  $utils->parseBoolToFlag(false);
        //是否配置lan-free
        $lan_free_flag = $utils->parseBoolToFlag(false);
        //存储用途(传过来的是0)
        $usemode = intval($params['usemode']);
        //lanfree名称列表
        $lanfree_name_list = [];
        //lanfree别名列表
        $lanfree_nickname_list = [];
        //挂载参数
        $mount_params= "";
        //用户uuid
        $user_uuid = Xphp::$_user['useruuid'];
        //Access Key id
        $access_key_id =  $params['cbraccessid'];
        //Secret Access Key
        $secret_access_key = $params['cbraccesskey'];
        //存储库id
        $vault_id = $params['cbrstorageid'];
        //区域列表
        $region_id_list = $params['cbrarea'];
        //是否自动扫描数据
        $refresh_flag = $utils->parseBoolToFlag($params['scandataSettings']['power']);
        //扫描数据时间间隔 转成秒
        $refresh_time = intval($params['scandataSettings']['value']) * 60;
        //用户管理员ak
        $userak = $params['cbruserak'];
        //用户管理员sk
        $usersk = $params['cbrusersk'];
        //授权用户名
        $username  = $params['cbrusername'];
        //检查别名
        $this->checkStorageNameEmpty($nickname);
        //数据检测
        $this->paramsCheck($nickname,$region_id_list,$access_key_id,$secret_access_key);
        //整理参数
        $pfMsg = array(
            "nickname"=>$nickname,
            "warning_flag"=>$warning_flag,
            "warning_type" => $warning_type,
            "warning_value"=>$warning_value,
            "import_flag"=>$import_flag,
            "format_flag"=>$format_flag,
            "lan_free_flag"=>$lan_free_flag,
            "usemode"=>$usemode,
            "lanfree_name_list"=>$lanfree_name_list,
            "lanfree_nickname_list"=>$lanfree_nickname_list,      // 不使用，lanfree别名列表，默认为[]
            "mount_params"=> $mount_params,
            "user_uuid"=>$user_uuid,
            "access_key_id"=>$access_key_id,
            "secret_access_key"=> $secret_access_key,
            "vault_id"=>$vault_id,
            "region_id_list"=>$region_id_list,
            "cbr_refresh_flag"=>$refresh_flag,
            "cbr_refresh_interval"=> $refresh_time,
            "language"=>Xphp::$_config['lang'],
            "admin_access_key_id"=>$userak,
            "admin_secret_access_key"=>$usersk,
            "auth_user_list"=>$username
        );
        //获取操作码
        $opName = "NODE_HUAWEI_CBR_OP_ADD";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($pfMsg), false);
        // var_dump("msg",$mbResult);
        // return;

        // var_dump("mbresult",$mbResult);
       // 编写假数据 ----测试成功
        // $mbResult = array(
        //     "error_code" => 0,
        //     "result"=> true,
        //     "msg"=>""
        // );
        $opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);
        //如果添加失败
        if(!$mbResult['result']){
            return $this->muOpResult(false, $opcodeDes, '', 'info', $mbResult['errorCode']);
        }else{
            //添加成功
            return $this->muOpResult(true,$opcodeDes,'');
        }
    }
    /**
   	 * 虚拟机恢复时获取存储类型  与getTimepointAllNode方法类型
   	 * @param array $params
   	 * @return boolean
   	 */
    public function getStorageType(array $params){
        $instantRecoverFlag =  $params['instantRecoverFlag'];
        $grainRecoverFlag =  $params['grainRecoverFlag'];
        $osinstantModuleFlag = $params['osinstantModuleFlag'];

        $sql = "select bsr.storage_nickname, bsr.storage_uuid, bsr.storage_type,bsr.node_uuid,bn.host_name,bn.ip 
            from bd_storage_resource bsr 
                left join mt_user_resource mur on bsr.storage_uuid = mur.resource_uuid and mur.resource_type = ?,
            bd_node bn 
            where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = 2 and bsr.source_type = 1 ";

//        $sqlParams = array(Xphp::$_user['useruuid']); //先暂时没有判断user_uuid 先暂时没有添加租户部分
        $sqlParams = [Xphp::$_config['RESOURCE_TYPE']['STORAGE']];
        if ($params['backupDataFlag']) {
            // 备份数据页面获取
            $sql .= " and bsr.use_mode = ? ";
            $sqlParams[] = Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'];
        } else {
            // 恢复页面获取时排除异地备份系统存储
            $sql .= " and bsr.storage_type != ? ";
            $sqlParams[] = Xphp::$_config['BD_STORAGE_TYPE']['REMOTE'];
        }
        //如果是瞬时恢复或者细粒度恢复 排除云存储/磁带 也要排除华为CBR存储
        if($instantRecoverFlag || $grainRecoverFlag){
            $sql .= " and bsr.storage_type not in (". Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR'] . "," . Xphp::$_config['BD_STORAGE_TYPE']['CLOUD'] . "," . Xphp::$_config['BD_STORAGE_TYPE']['TAPE'] . ") ";
        }
        //如果是操作系统瞬时恢复 还需要屏蔽磁带
        if($osinstantModuleFlag){
            $sql .= " and bsr.storage_type not in (". Xphp::$_config['BD_STORAGE_TYPE']['TAPE']. ") ";
        }
        // 查看权限
        if(empty($_SESSION['tenantuuid']) && Xphp::$_user['useruuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9"){
            // 超级管理查看所有的资源
        } elseif ($_SESSION['isThreePowers'] && ($_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['sysadmin']
                || $_SESSION['userLevel'] == Xphp::$_config['THREE_POWERS_USER']['safeadmin'])) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        }else{
            // 关联管理用户判断
            $authUser = $_SESSION['authUser']['storage_manager_look'] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and (bsr.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
            } else {
                $userUuid = Xphp::$_user['useruuid'];
                $sql .= " and (bsr.user_uuid = ? or mur.user_uuid = ?) ";
                $sqlParams = array_merge($sqlParams,array($userUuid, $userUuid));
            }
        }
        $data = parent::dbSelect($sql . ' group by storage_uuid', $sqlParams);
        $storagelist = array();


        $allstoragename = Xphp::$_lang['WEB_STORAGE_ALL_STORAGE'];
        $flag = false;
        //如果不包含网络存储 则不显示不包含网络存储
        foreach($data as $d){
            //网络存储包含华为CBR S3 磁带 以后需要加上
            if($d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['HUAWEI_CBR']){
                $flag =  true;
            }
        }
        if($flag){
            $allstoragename.="(". Xphp::$_lang['WEB_STORAGE_NOT_INCLUDE_NET_STORAGE'] . ")";
        }
        //新增所有存储
        $storagelist[]= array(
            "storagename"=>$allstoragename,
            "storageid"=>"",
            "storagetypedes"=>"",
            "storagetype"=>"",
            "text"=>$allstoragename,
        );



        foreach ($data as $d){
            $storagelist[] =  array(
                "storagename"=>$d["storage_nickname"],
                "storageid"=>$d["storage_uuid"],
                "storagetypedes"=>$this->getStorageTypeDes($d['storage_type']),
                "storagetype"=>$d["storage_type"],
                "text" => $this->getStorageShowName($d['storage_nickname'], $d['storage_type'],$d['host_name'],$d['ip']),
            );
        }
        return json_encode($storagelist);
    }

    /**
     * 得到所有可用存储(新建备份任务,修改备份任务使用)
     * @param unknown $params
     */
    public function getBackupStorageListNew($params){
        // $copyFlag = $params['copyflag'];//用于副本初始化可用存储列表
        // $cloudFlag = $params['cloudflag'];//用于云存储初始化可用存储列表
        // $copybackFlag = $params['copybackflag'];//用于副本拉回初始化可用存储列表
        // $archivebackFlag = $params['archivebackflag']; //用于归档回传初始化化可用存储
        // $nodeuuid = $params['nodeuuid'];
        $vmFlag = $params['vmflag'];
        $usemode ="";
        $usemodes =  [
            Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'],
            Xphp::$_config['BD_STORAGE_USE_MODE']['UNKNOWN']
        ];
        $usemode  = implode("', '", $usemodes);
        //备份上云先屏蔽异地备份系统
        $sql = "select mount_flag, node_uuid, storage_nickname, storage_uuid, storage_type, total_size, free_size, status, error_code 
                from bd_storage_resource where status = ? and mount_flag = ? 
                and error_code = ? and lan_free_flag = ?  and use_mode in ('$usemode') ";
        if ($vmFlag) {
            // 虚拟机备份屏蔽异地和cbr
            $sql .= " and storage_type not in (8, 12)";
        } else {
            $sql .= " and storage_type not in (8)";
        }
        $sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
     	    Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $utils = Xphp::instance("Utils");
        $nodeHandler = Xphp::instance('NodeHandler');
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($_SESSION['tenantuuid'])){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $userHandler =  Xphp::instance('UsersHandler');
            $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
            if(!empty($resourceInfo)){
                foreach ($resourceInfo as $r){
                    $resourceList[] = $r['resource_uuid'];
                }
            }
            //获取用户配额
        }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            if(!empty($_SESSION['tenantuuid']) && !in_array($d['storage_uuid'], $resourceList)) continue;

        	$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
        	$storageStatus = $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
        	if($storageStatus != Xphp::$_config['STORAGE_STATUS']['ONLINE']) continue;
        	$nodeName = $this->getNodeName($d['node_uuid']);
        	$name = "";
        	$storageDes = $d['storage_nickname'];
        	if(empty($_SESSION['tenantuuid'])){
        	    $storageDes .= "(" . $this->getStorageTypeDes($d['storage_type']) . ", ".Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($d['total_size']) .
        	    ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($d['free_size']) . ")";
        	}else{
        	    $quotaInfo = $userHandler->getUserQuotaInfo();
        	    if(!empty($quotaInfo['des'])){
        	        $storageDes .= "(" . $this->getStorageTypeDes($d['storage_type']) . ", " . $quotaInfo['des'] . ")";
        	    }
        	}
        	$storageDes .= $name;

            $info[] = array(
                'uuid' => $d['storage_uuid'],
                'text' => $storageDes,
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
     * @param string $storage_nickname
     * @param string $storagetypedes
     * @param string $hostname
   	 */
    public function getStorageShowName($storagename, $storagetype,$hostname,$ip){
        $storagedes  = $this->getStorageTypeDes($storagetype);
        //如果是本地需要去找节点
        if($storagetype == 12 || $storagetype == 9){
            $name  = $storagename."(".$storagedes.")";
        }else{
            $name  = $storagename."(".$storagedes."，" . Xphp::$_lang['WEB_PLATFORM_DES_NODE']. ":".$hostname."(".$ip.")".")";
        }
        return $name;
        // 本地存储1(本地磁盘，节点：localhost.localdomain（192.168.30.61）)
    }

    //手动同步时间点
    /**
     * @param array $params
   	 */
    public function manualImportTime($params){
        $pfMsg  = array(
            "storage_uuid_list"=>array()
        );
        foreach($params['uuids'] as $uuid ){
            $storageuuid =  array(
                "storage_uuid" => $uuid
            );
            $pfMsg['storage_uuid_list'][] =  $storageuuid;
        }
        $opName = "NODE_SR_OP_TIMEPOINTS_IMPORT_SYNC";
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($pfMsg), true);
        //var_dump("mbresult",$mbResult);
        // 编写假数据 ----测试成功
        // $mbResult = array(
        //     "error_code" => 0,
        //     "result"=> true,
        //     "msg"=>""
        // );
        $opcodeDes = $this->opcodeHandler->getOpcodeDes($opName);
        //如果添加失败
        if(!$mbResult['result']){
            return $this->muOpResult(false, $opcodeDes, '', 'info', $mbResult['errorCode']);
        }else{
            //添加成功
            return $this->muOpResult(true,$opcodeDes,'');
        }
    }

    //--------------------------------------------------华为CBR新增方法end-------------------------------------------------------------------

    /**
     * 得到操作系统瞬时恢复的可用存储（catch）
     * @param unknown $params
     */
    public function getOsCatchStorage($params){
        // $copyFlag = $params['copyflag'];//用于副本初始化可用存储列表
        // $cloudFlag = $params['cloudflag'];//用于云存储初始化可用存储列表
        // $copybackFlag = $params['copybackflag'];//用于副本拉回初始化可用存储列表
        // $archivebackFlag = $params['archivebackflag']; //用于归档回传初始化化可用存储
        $nodeuuid = $params['nodeuuid'];
        $sql = "select mount_flag, node_uuid, storage_nickname, storage_uuid, storage_type, total_size, free_size, status, error_code 
                from bd_storage_resource where status = ? and mount_flag = ? 
                and error_code = ? and lan_free_flag = ?  and use_mode = ? and storage_type not in (8,9,10) and node_uuid = ?";
        $sqlParams = array(Xphp::$_config['STORAGE_STATUS']['ONLINE'],
            Xphp::$_config['FLAG']['SET'], 0, Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['BD_STORAGE_USE_MODE']['BACKUP'], $nodeuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $utils = Xphp::instance("Utils");
        $nodeHandler = Xphp::instance('NodeHandler');
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($_SESSION['tenantuuid'])){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $userHandler =  Xphp::instance('UsersHandler');
            $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['STORAGE']);
            if(!empty($resourceInfo)){
                foreach ($resourceInfo as $r){
                    $resourceList[] = $r['resource_uuid'];
                }
            }
        }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            if(!empty($_SESSION['tenantuuid']) && !in_array($d['storage_uuid'], $resourceList)) continue;
        	$nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);
        	$storageStatus = $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['mount_flag']));
        	if($storageStatus != Xphp::$_config['STORAGE_STATUS']['ONLINE']) continue;
        	$storageDes = $d['storage_nickname'];
        	if(empty($_SESSION['tenantuuid'])){
        	    $storageDes .= "(" . $this->getStorageTypeDes($d['storage_type']) . ", ".Xphp::$_lang['UI_STORAGE_TOTAL_SIZE'] . ":" . $utils->calSize($d['total_size']) .
        	    ", " . Xphp::$_lang['WEB_PLATFORM_DC_AVAILABLE_SPACE'] . ":" . $utils->calSize($d['free_size']) . ")";
        	}else{
        	    $quotaInfo = $userHandler->getUserQuotaInfo();
        	    if(!empty($quotaInfo['des'])){
        	        $storageDes .= "(" . $this->getStorageTypeDes($d['storage_type']) . ", " . $quotaInfo['des'] . ")";
        	    }
        	}
            $info[] = array(
                'uuid' => $d['storage_uuid'],
                'text' => $storageDes,
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
     * 批量获取存储状态
     * @param array $storageUuidList 存储uuid集合
     * @return array
     */
    public function batchGetStorageStatus(array $storageUuidList): array
    {
        $storageUuidList = array_values(array_unique($storageUuidList));
        $storageUuids = "'" . implode("','", $storageUuidList) . "'";
        $sql = "SELECT status, storage_uuid, storage_type, node_uuid FROM bd_storage_resource WHERE storage_uuid IN ($storageUuids) ";
        $storageData = $this->dbSelect($sql);
        if (!is_array($storageData)) {
            $storageData = [];
        }
        $nodeUuidList = [];
        $allStorageType = Xphp::$_config['BD_STORAGE_TYPE'];
        $excludeStorageType = [
            $allStorageType['CLOUD'],
            $allStorageType['NFS'],
            $allStorageType['CIFS'],
        ];
        foreach ($storageData as $storageItem) {
            if (in_array($storageItem['storage_type'], $excludeStorageType)) {
                continue;
            }
            $nodeUuidList[] = $storageItem['node_uuid'];
        }
        $nodeStatusList = $this->batchGetNodeStatus($nodeUuidList);
        $nodeStatusMap = [];
        foreach ($nodeStatusList as $nodeStatus) {
            $nodeStatusMap[$nodeStatus['node_uuid']] = $nodeStatus;
        }

        $allStorageStatus = Xphp::$_config['STORAGE_STATUS'];
        $storageStatusList = [];
        foreach ($storageData as $storageItem) {
            $tmp = [
                'storage_uuid' => $storageItem['storage_uuid'],
                'storage_status' => $allStorageStatus['OFFLINE'],
                'node_online_flag' => false,
            ];
            if (in_array($storageItem['storage_type'], $excludeStorageType)) {  // 判断是否status
                $tmp['storage_status'] = $storageItem['status'];
            } else {  // 其他存储判断节点是否在线
                if (isset($nodeStatusMap[$storageItem['node_uuid']])) {
                    if ($nodeStatusMap[$storageItem['node_uuid']]['online_flag']) {
                        $tmp['storage_status'] = $allStorageStatus['ONLINE'];
                        $tmp['node_online_flag'] = true;
                    }
                }
            }
            $storageStatusList[] = $tmp;
        }
        return $storageStatusList;
    }

    /**
     * 批量获取节点的状态
     * @param array $nodeUuidList 节点uuid列表
     * @return array
     */
    public function batchGetNodeStatus(array $nodeUuidList): array
    {
        $nodeUuidList = array_values(array_unique($nodeUuidList));
        $nodeUuids = "'" . implode("', '", $nodeUuidList) . "'";
        $sql = "SELECT online_flag, module_type, node_uuid FROM bd_module_server WHERE node_uuid IN ($nodeUuids) ";
        $moduleData = $this->dbSelect($sql);
        $moduleMap = [];
        foreach ($moduleData as $moduleInfo) {
            if (!isset($moduleMap[$moduleInfo['node_uuid']])) {
                $moduleMap[$moduleInfo['node_uuid']] = [];
            }
            $moduleMap[$moduleInfo['node_uuid']][] = $moduleInfo;
        }
        $setFlag = Xphp::$_config['FLAG'];
        $nodeStatusList = [];

        foreach ($nodeUuidList as $nodeUuid) {
            $tmp = [
                'online_flag' => false,
                'deploy_flag' => false,
                'offline_module' => [],
                'node_uuid' => $nodeUuid,
            ];
            if (!isset($moduleMap[$nodeUuid])) {
                $nodeStatusList[] = $tmp;
                continue;
            }
            $tmp['deploy_flag'] = true;
            $tmp['online_flag'] = true;
            foreach ($moduleMap[$nodeUuid] as $moduleInfo) {
                if ($moduleInfo['online_flag'] == $setFlag['UNSET']) {
                    $tmp['online_flag'] = false;
                    $tmp['offline_module'][] = intval($moduleInfo['module_type']);
                }
            }
            $nodeStatusList[] = $tmp;
        }
        return $nodeStatusList;
    }
}
?>