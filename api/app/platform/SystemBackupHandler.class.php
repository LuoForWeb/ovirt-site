<?php
/******************************************* 
** 系统备份恢复类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2021-4-20 下午16:17:21 
** @version      1.0.0 
** @copyright    Copyright 2021 vinchin.com 
********************************************/
class SystemBackupHandler extends OPHandler{
    //备份工作目录
    private $systemBakDir = "";
    //自动备份文件相对URL
    private $systemAutoUrl = "";
    //自动备份自描述文件相对URL
    private $systemAutoSelfMarkUrl = "";
    //自动备份存储路径
    private $autoBackupStoragePath = "";
    //自动备份导出文件路径
    private $autoBackupExportFilePath = "";
    //自动备份文件名
    private $autoBackupFileName = "";
    //自动备份自描述文件,供存储导入的时候后台读取信息到数据库使用
    private $autoBackupSelfMarkFileName = "";
    //自动备份时间
    private $autoBackupTime = "";
    
    //恢复工作目录
    private $systemRecDir = "";
    //恢复日志文件
    private $systemRecLogFile = "";
    //恢复节点对应关系
    private $recNodeCompareList = array();
    //恢复策略对应关系
    private $strategyIDCompareList = array();
    //恢复的vcenter记录
    private $vcenterRefleshList = array();
    
    //主节点uuid
    private $masterNodeUUID = "";


    /**
     * 导出备份系统数据,手动备份
     * @param unknown $parmas
     * @return string
     */
    public function doOnceBackupSystem($parmas){
        $nodes = $parmas['nodes'];
        $this->paramsCheck($nodes);
        $operate = Xphp::$_lang['UI_SBH_PRODUCE_BACKUPFILE'];
        //准备备份导出环境,清理目录
        $this->prepareBackenvironment();
        
        //导出数据库数据
        $bakDes = include CONF_PATH . "system_bak.php";
        $result = true;
        foreach ($bakDes['BakTreeConf'] as $key => $value){

            //按配置文件第一层遍历,如果选择了此项,则调用此项的备份方法,进入备份流程
            if(in_array($key, $nodes)){
                $function = $value['bakFuntion'];
                $callResult = call_user_func_array(array($this, $function), array($nodes, $value['child']));
                $result = $result && $callResult;
            }
        }
        if(!$result){
            //如果导出失败
            return $this->muOpResult(false, $operate, Xphp::$_lang['UI_SBH_EXPORT_BACKUPFILE_FAIL'], "warning", 0, array($extArray));
        }
        
        //写入本次配置文件
        $config = array(
            "nodes" => $nodes,
        );
        $filename = $this->systemBakDir . "/config";
        $result = file_put_contents($filename, json_encode($config));
        
        //打包数据文件
        $timeStamp = date("Ymd.His");
        $desZip = $this->systemBakDir . "/systembak." . $timeStamp . ".bak";
        $cmd = "zip -q -j -r -m -P " . Xphp::$_config['ZIP_PASS'] . " " . $desZip . " " . $this->systemBakDir;
        
        $result = "";
        exec($cmd, $info, $result);
        
        if(0 == $result){
            $systemHandler = Xphp::instance('SystemHandler');
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeuuid = $nodeHandler->getLocalNodeUUID();
            $url = $systemHandler->groupUnifyDownloadUrl($nodeuuid, Xphp::$_config['TMP_PATH'] . "systembak/systembak." . $timeStamp . ".bak");
            $this->systemLog("SYSTEM_LOG_SYSBAKREC_USER_BACKUP_SYSTEM", array(), Xphp::$_config['LOGLEVEL']['NORMAL']);
            $extArray = array(
                "url" => $url
            );
            return $this->muOpResult(true, $operate, "", "", 0, $extArray);
        }else{
            $this->systemLog("SYSTEM_LOG_SYSBAKREC_USER_BACKUP_SYSTEM", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
            return $this->muOpResult(false, $operate);
        }
    }
    
    /**
     * 得到自动备份数据列表
     * @param unknown $params
     */
    public function getAutoBakDataList($params){
        $p = json_decode($_POST['p'], true);
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $search = $p['search'];
        $utils = Xphp::instance("Utils");
        $name = $search['name'];
        $name = $utils->escapeWildcard($name);
        
        $sortArr = array('', 'file_name', 'file_size', 'backup_time', '', '');
        
        $sql = "select bsbd.backup_uuid, bsbd.file_name, bsbd.file_size, bsbd.file_path,  unix_timestamp(backup_time) backup_time, 
                        bsr.storage_nickname, bn.host_name, bn.ip, bn.node_nickname   
                    from bd_system_backup_data bsbd, bd_storage_resource bsr, bd_node bn 
                    where bsbd.node_uuid = bn.node_uuid and bsbd.storage_uuid = bsr.storage_uuid ";
        $sqlCount = "select count(bsbd.id) as total from bd_system_backup_data bsbd, bd_storage_resource bsr, bd_node bn 
                    where bsbd.node_uuid = bn.node_uuid and bsbd.storage_uuid = bsr.storage_uuid  ";
        
        if(!empty($name)){
            //如果有搜索
            $sql .= " and bsbd.file_name like '%" . $name . "%' ";
            $sqlCount .= " and bsbd.file_name like '%" . $name . "%' ";
        }
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = parent::dbSelect($sql, array($start, $length));
        $count = parent::dbSelect($sqlCount, array());
        
        
        
        $records = array();
        $records["data"] = array();
        
        $nodeHandler = Xphp::instance('NodeHandler');
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="uuid[]" value="'. $d['backup_uuid'] .'">',
                $d['file_name'],
                $utils->calSize($d['file_size'], true),
                date("Y-m-d H:i:s", $d['backup_time']),
                $nodeHandler->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                $d['storage_nickname'],
            );
        }
        
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 得到自动备份数据列表
     * @param unknown $params
     */
    public function getAutoBakDataListForRec($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        
        $sortArr = array('', 'file_name', 'file_size', 'backup_time', '', '');
        
        $sql = "select bsbd.backup_uuid, bsbd.file_name, bsbd.file_size, bsbd.file_path,  unix_timestamp(backup_time) backup_time,
                        bsbd.config 
                    from bd_system_backup_data bsbd, bd_storage_resource bsr, bd_node bn
                    where bsbd.node_uuid = bn.node_uuid and bsbd.storage_uuid = bsr.storage_uuid ";
        $sqlCount = "select count(bsbd.id) as total from bd_system_backup_data bsbd, bd_storage_resource bsr, bd_node bn
                    where bsbd.node_uuid = bn.node_uuid and bsbd.storage_uuid = bsr.storage_uuid  ";
        
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = parent::dbSelect($sql, array($start, $length));
        $count = parent::dbSelect($sqlCount, array());
        
        $utils = Xphp::instance("Utils");
        
        $records = array();
        $records["data"] = array();
        
        foreach ($data as $d){
            $records["data"][] = array(
                '<input type="checkbox" name="uuid[]" value="'. $d['backup_uuid'] .'">',
                $d['file_name'],
                $utils->calSize($d['file_size'], true),
                date("Y-m-d H:i:s", $d['backup_time']),
            );
        }
        
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 下载系统备份文件检查,
     * 这里直接检查文件大小,如果检查失败,表示这个文件不存在或节点不可用,就不能下载
     * @param unknown $params
     */
    public function downloadAutoBakDataCheck($params){
        $uuid = $params['uuid'];
        $this->paramsCheck($uuid);
        
        $sql = "select file_name, file_size, file_path, node_uuid, storage_uuid from bd_system_backup_data where backup_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid[0]));
        
        $filepath = $data[0]['file_path'];
        $nodeuuid = $data[0]['node_uuid'];
        
        //先检查文件的大小
        $opName = "NODE_SYS_OP_GET_FILE_SIZE";
        $storageHandler = Xphp::instance('StorageHandler');
        $operate = $storageHandler->getUnifyOpcodeDes($opName);
        $msg = array(
            'file_path' => $filepath
        );
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        if(!$mbResult['result']){  //获取文件大小失败
            return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }else{  //获取成功,检查获取的大小是否和数据库存储的大小一致
            return $this->muOpResult(true, $operate);
        }
    }
    
    /**
     * 下载系统备份文件
     * @param unknown $params
     */
    public function downloadAutoBakData($params){
        $uuid = $_GET['uuid'];
        $this->paramsCheck($uuid);
        
        $sql = "select file_name, file_size, file_path, node_uuid, storage_uuid from bd_system_backup_data where backup_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        
        $filesize = $data[0]['file_size'];
        $filepath = $data[0]['file_path'];
        $nodeuuid = $data[0]['node_uuid'];
        $filename = $data[0]['file_name'];
        
        
        //先检查文件的大小
        $opName = "NODE_SYS_OP_GET_FILE_SIZE";
        $storageHandler = Xphp::instance('StorageHandler');
        $operate = $storageHandler->getUnifyOpcodeDes($opName);
        $msg = array(
            'file_path' => $filepath
        );
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        if(!$mbResult['result']){
            //获取文件大小失败
            return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }else{
            //获取成功,检查获取的大小是否和数据库存储的大小一致
            if($mbResult['msg']['file_size'] != $filesize){
                return $this->muOpResult(false, $operate, '', 'warning');
            }
        }
        
        //下载文件到本地
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $filesize);
        Header("Content-Disposition: attachment; filename=". $filename);
        
        $opName = "NODE_SYS_OP_PREAD_FILE";
        $storageHandler = Xphp::instance('StorageHandler');
        $operate = $storageHandler->getUnifyOpcodeDes($opName);
        $blockSize = Xphp::$_config['GRAIN_FILE_BLOCK_SIZE'];
        //如果大于分块大小,分块下载
        for($i=0; $i<$filesize; $i = $i + $blockSize){
            if($filesize - $i <= $blockSize){
                $readLen = $filesize - $i;
            }else{
                $readLen = $blockSize;
            }
            $msg = array(
                "file_path" => $filepath,
                "offset" => $i,
                "length" => $readLen,
            );
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
            echo  $mbResult;
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }
        
    }
    
    /**
     * 删除系统备份文件
     * @param unknown $params
     * @return boolean
     */
    public function deleteAutoBakData($params){
        $uuid = $params['uuid'];
        $this->paramsCheck($uuid);
        
        $vendor = Xphp::$_config['SYSTEM_INFO']['vendor'];
        $operate = Xphp::$_lang['UI_SBH_REMOVE_BACKUPFILE'];
        $uuidStr = implode("','", $uuid);
        $sql = "select id, file_path, node_uuid from bd_system_backup_data where backup_uuid in ('" . $uuidStr . "')";
        $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        foreach ($data as $d){
            $file_path = $d['file_path'];
            //检查一下全路径的格式,删除是一个危险的操作,方式误删
            //标准的格式如:/backup_storage/e406b71d-31a2-40bb-a80f-2a8d88b5c188/systembak/systembak.20210427.195215.bak
            $startPathStr = substr($file_path, 0, 16);
            $endPathStr = substr($file_path, -3, 3);
            if("/backup_storage/" == $startPathStr && "bak" == $endPathStr){
                $selfMarkFilePath = substr($file_path, 0, -3) . "self";     //删除self文件
                //检查标准:以"/backup_storage/"开始,以"bak"结束
                $cmd = "/opt/$vendor/sysdm -rf " . $file_path . ";" . "/opt/$vendor/sysdm -rf " . $selfMarkFilePath . ";";
                $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
                $msg = array('command'=>$cmd);
                $mbResult = $this->mbNodeMsg($opName, $d['node_uuid'], json_encode($msg), true, false);
                if($mbResult['result']){
                    //删除数据库记录
                    $sql = "delete from bd_system_backup_data where id = ?";
                    $result = $this->dbExec($sql, array($d['id']));
                    if(!$result){
                        $this->systemLog("SYSTEM_LOG_SYSBAKREC_DELETE_TIMEPOINT", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
                        return $this->muOpResult(false, $operate);
                    }
                }else{
                    $this->systemLog("SYSTEM_LOG_SYSBAKREC_DELETE_TIMEPOINT", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
                    return $this->muOpResult(false, $operate);
                }
            }
        }
        
        $this->systemLog("SYSTEM_LOG_SYSBAKREC_DELETE_TIMEPOINT", array(), Xphp::$_config['LOGLEVEL']['NORMAL']);
        return $this->muOpResult(true, $operate);
    }
    
    /**
     * 清理恢复环境,创建恢复目录
     * @return boolean
     */
    private function clearnRecoveryDir(){
        $recDir = Xphp::$_config['TMP_PATH'] . "systemrec/";
        $cmd = "rm -rf " . $recDir;
        $cmd .= ";mkdir " . $recDir;
        $cmd .= ";chown nginx:nginx " . $recDir;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        return $mbResult['result'];
    }
    
    /**
     * 上传手动备份文件
     * @param unknown $params
     */
    public function uploadRecoverySrc($params){
        //清理并创建恢复目录
        $this->clearnRecoveryDir();
        ini_set('memory_limit', '2000m');   //临时修改一下配置,适应直接上传大文件
        $uploadfile = Xphp::$_config['IMPORT_INFO']['uploadfile'];
        $files = $_FILES['file'];
        $tmpPath = Xphp::$_config['TMP_PATH'] . "systemrec/";
        $operate = Xphp::$_lang['UI_SBH_UPLOAD_BACKUPFILE'];
        if($files){
            //检测上传文件状态
            if(0 != $files['error']){
                exit($this->muOpResult(false, $operate));
            }
            //检测文件后缀名
            $arr = explode(".", $files['name']);
            if($arr[count($arr) - 1] != "bak"){
                exit($this->muOpResult(false, $operate,  Xphp::$_lang['UI_SBH_ERROR_FILE_TYPE'], 'error'));
            }
            //检测上传文件大小
            if($files['size'] > $uploadfile['size']){
                exit($this->muOpResult(false, $operate,  Xphp::$_lang['UI_SBH_FILE_OVERSIZE'], 'error'));
            }
            //保存临时文件
            $count = file_put_contents($tmpPath . 'data.bak', file_get_contents($files['tmp_name']));
            if($count > 0){
                exit($this->muOpResult(true, $operate));
            }else{
                exit($this->muOpResult(false, $operate));
            }
        }else {
            exit($this->muOpResult(false, $operate));
        }
    }
    
    /**
     * 获取自动别分文件恢复项
     * @param unknown $params
     */
    public function getAutoBakSrcContent($params){
        $uuid = $params['uuid'];
        $this->paramsCheck($uuid);
        $operate = Xphp::$_lang['UI_SBH_GET_RECOVERY_ITEM'];
        $sql = "select config from bd_system_backup_data where backup_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        
        if(empty($data)){
            return $this->muOpResult(false, $operate, Xphp::$_lang['UI_SBH_GETINFO_FAIL'], 'warning');
        }
        $info = array(
            're' => true,
            'allNode' => json_decode($this->getBackupTree(), true),
            'selectNode' => json_decode($data[0]['config'], true)
        );
        
        return json_encode($info);
    }
    
    /**
     * 获取手动备份文件恢复项
     * @param unknown $params
     */
    public function getUploadSrcContent($params){
        //检查恢复文件是否存在
        $operate = Xphp::$_lang['UI_SBH_GET_RECOVERY_ITEM'];
        $tmpPath = Xphp::$_config['TMP_PATH'] . "systemrec/";
        $zipFile = $tmpPath . "data.bak";
        if(!file_exists($zipFile)){
            return $this->muOpResult(false, $operate,Xphp::$_lang['UI_SBH_CONFIGFILE_NOT_EXIST'], 'warning');
        }
        //解压恢复文件
        $cmd = "unzip -P " . Xphp::$_config['ZIP_PASS'] . " " . $zipFile . " -d " . $tmpPath;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        if(!$mbResult['result']){
            return $this->muOpResult(false, $operate, Xphp::$_lang['UI_SBH_FILE_DECOMPRESS_FAIL'], 'warning');
        }
        //获取配置文件
        $content = file_get_contents($tmpPath . "config");
        
        $info = array(
            're' => true,
            'allNode' => json_decode($this->getBackupTree(), true),
            'selectNode' => json_decode($content, true)
        );
        
        return json_encode($info);
    }
    
    /**
     * 导出备份系统数据,自动备份
     * @param unknown $parmas
     */
    public function autoBackupSystem($parmas){
        $nodes = $parmas['nodes'];
        $nodeuuid = $parmas['nodeuuid'];
        $storageuuid = $parmas['storageuuid'];
        $serverAddr = $parmas['serverAddr'];
        $reservedNum = intval($parmas['reservedNum']);
        
        $utils = Xphp::instance('Utils');
        $backup_uuid = $utils->uuid();
        
        echo 0;
        //创建并检查存储目录
        $result = $this->checkAndCreateAutoBakDir($nodeuuid, $storageuuid);
        if(!$result){
            //写日志
            $this->writeLog("check auto backup storage dir failure!");
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE1", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
            return false;
        }
        echo 1;
        //生成备份文件,并生成相对URL地址
        $result = $this->exportAutoBakFile($backup_uuid, $nodeuuid, $storageuuid, $nodes);
        if(!$result){
            //写日志
            $this->writeLog("export auto backup file failure!");
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE2", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
            return false;
        }
        echo 2;
        //保存备份数据到存储目录
        $result = $this->saveAutoBakFileToStorage($nodeuuid, $serverAddr);
        if(!$result){
            //写日志
            $this->writeLog("save auto backup file to storage failure!");
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE3", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
            return false;
        }
        echo 3;
        //写入备份数据到数据库
        $result = $this->writeAutoBakToDatabase($backup_uuid, $nodeuuid, $storageuuid, $nodes);
        if(!$result){
            //写日志
            $this->writeLog("witer backup info into databases failure!");
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE4", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
            return false;
        }
        echo 4;
        //检查保留配置,删除过期保留备份点
        $result = $this->deleteOldAutoBakData($nodeuuid, $reservedNum);
        if(!$result){
            //写日志
            $this->writeLog("delete old backup point failure!");
            $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_FAILURE5", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
            return false;
        }
        echo 5;
        
        $this->daemonLog("SYSTEM_LOG_SYSBAKREC_AUTO_BACKUP_SYSTEM_SUCCESS", array(), Xphp::$_config['LOGLEVEL']['NORMAL']);
        return true;
    }
    
    /**
     * 删除过期备份点
     */
    private function deleteOldAutoBakData($nodeuuid, $reservedNum){
        //从数据库查找过期备份点
        $sql = "select id, file_path, node_uuid from bd_system_backup_data order by id desc limit ?, 999";
        $data = $this->dbSelect($sql, array($reservedNum));
        if(empty($data)) return true;
        
        $vendor = Xphp::$_config['SYSTEM_INFO']['vendor'];
        //由于用户可能在备份中途更换备份节点和存储,所以,这里需要一个一个点的删除,不能全部一个命令删除 
        $cmd = "";
        //调用删除命令删除文件
        foreach ($data as $d){
            $file_path = $d['file_path'];
            //检查一下全路径的格式,删除是一个危险的操作,方式误删
            //标准的格式如:/backup_storage/e406b71d-31a2-40bb-a80f-2a8d88b5c188/systembak/systembak.20210427.195215.bak
            $startPathStr = substr($file_path, 0, 16);
            $endPathStr = substr($file_path, -3, 3);
            if("/backup_storage/" == $startPathStr && "bak" == $endPathStr){
                $selfMarkFilePath = substr($file_path, 0, -3) . "self";     //删除self文件
                //检查标准:以"/backup_storage/"开始,以"bak"结束
                $cmd = "/opt/$vendor/sysdm -rf " . $file_path . ";" . "/opt/$vendor/sysdm -rf " . $selfMarkFilePath . ";";
                $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
                $msg = array('command'=>$cmd);
                $mbResult = $this->mbNodeMsg($opName, $d['node_uuid'], json_encode($msg), true, false);
                if($mbResult['result']){
                    //删除数据库记录
                    $sql = "delete from bd_system_backup_data where id = ?";
                    $result = $this->dbExec($sql, array($d['id']));
                    if(!$result){
                        return false;
                    }
                }else{
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * 写入本次备份信息到数据库
     */
    private function writeAutoBakToDatabase($backupuuid, $nodeuuid, $storageuuid, $nodes){
        $sql = "INSERT INTO bd_system_backup_data (backup_uuid, file_name, file_size, file_path, backup_time, node_uuid, storage_uuid, config) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $fullPath = $this->autoBackupExportFilePath . "/" . $this->autoBackupFileName;
        $file_size = filesize($fullPath);
        $storagePath = $this->autoBackupStoragePath . "/" . $this->autoBackupFileName;
        $config = array(
            "nodes" => $nodes
        );
        $sqlParams = array($backupuuid, $this->autoBackupFileName, $file_size, $storagePath, $this->autoBackupTime, $nodeuuid, $storageuuid, json_encode($config));
        $result = $this->dbExec($sql, $sqlParams);
        return $result;
    }
    
    /**
     * 保存备份文件到存储
     * @param unknown $nodeuuid
     * @param unknown $storageuuid
     */
    private function saveAutoBakFileToStorage($nodeuuid, $serverAddr){
        $vendor = Xphp::$_config['SYSTEM_INFO']['vendor'];
        $cmd = "/opt/$vendor/sysget \-\-no-check-certificate -P " . $this->autoBackupStoragePath . " https://" . $serverAddr . $this->systemAutoUrl;
        $cmd .= ";" . "/opt/$vendor/sysget \-\-no-check-certificate -P " . $this->autoBackupStoragePath . " https://" . $serverAddr . $this->systemAutoSelfMarkUrl;
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if(!$mbResult['result']){
            //保存失败
            return false;
        }
        
        return true;
    }
    
    /**
     * 生成自动备份文件,并保存到存储目录
     * @param unknown $settings
     */
    private function exportAutoBakFile($backupuuid, $nodeuuid, $storageuuid, $nodes){
        if(empty($nodes)) return true;
        //准备备份导出环境,清理目录,导出是先导出到本地
        $this->prepareBackenvironment();
        
        //导出数据库数据
        $bakDes = include CONF_PATH . "system_bak.php";
        $result = true;
        foreach ($bakDes['BakTreeConf'] as $key => $value){
            //按配置文件第一层遍历,如果选择了此项,则调用此项的备份方法,进入备份流程
            if(in_array($key, $nodes)){
                $function = $value['bakFuntion'];
                $callResult = call_user_func_array(array($this, $function), array($nodes, $value['child']));
                $result = $result && $callResult;
            }
        }
        if(!$result){
            //如果导出失败
            return false;
        }
        
        //写入本次配置文件
        $config = array(
            "nodes" => $nodes,
        );
        $filename = $this->systemBakDir . "/config";
        $result = file_put_contents($filename, json_encode($config));
        
        //打包数据文件
        $nowTime = time();
        $timeStamp = date("Ymd.His", $nowTime);
        $this->autoBackupExportFilePath = $this->systemBakDir;
        $this->autoBackupFileName = "systembak." . $timeStamp . ".bak";
        $this->autoBackupSelfMarkFileName = "systembak." . $timeStamp . ".self";
        $this->autoBackupTime = date("Y-m-d H:i:s", $nowTime);
        $desZip = $this->systemBakDir . "/" . $this->autoBackupFileName;
        $desSelfMarkPath = $this->systemBakDir . "/" . $this->autoBackupSelfMarkFileName;
        
        //生成备份文件
        $cmd = "zip -q -j -r -m -P " . Xphp::$_config['ZIP_PASS'] . " " . $desZip . " " . $this->systemBakDir;
        $result = "";
        exec($cmd, $info, $result);
        if(0 == $result){
            $this->systemAutoUrl = Xphp::$_config['TMP_PATH_RE'] . "systembak/systembak." . $timeStamp . ".bak";
        }else{
            return false;
        }
        //生成自描述文件
        $nodes = array(
            "nodes" => $nodes,
        );
        $data = array(
            "backup_uuid" => $backupuuid,
            "file_name" => $this->autoBackupFileName,
            "file_size" => filesize($desZip),
            "backup_time" => $this->autoBackupTime,
            "config" => json_encode($nodes),
        );
        $result = file_put_contents($desSelfMarkPath, json_encode($data));
        
        if(false !== $result){
            $this->systemAutoSelfMarkUrl = Xphp::$_config['TMP_PATH_RE'] . "systembak/systembak." . $timeStamp . ".self";
        }else{
            return false;
        }
        
        return true;
    }
    
    /**
     * 获取自动备份存储目录
     * @param unknown $storageuuid
     */
    private function getAutoBakStoragePath($storageuuid){
        $sql = "select mount_point from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $mountPoint = $data[0]['mount_point'];
        
        return $mountPoint;
    }
    
    /**
     * 检查并创建存储目录
     * @param unknown $storageuuid
     */
    private function checkAndCreateAutoBakDir($nodeuuid, $storageuuid){
        $mountPoint = $this->getAutoBakStoragePath($storageuuid);
        if(empty($mountPoint)) return false;
        
        $this->autoBackupStoragePath = $mountPoint . "/" . "systembak";
        //检查目录是否存在,这里涉及到节点,只能通过命令去检测
        $cmd = "test -d " . $this->autoBackupStoragePath . "; echo $?;";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        
        if(!$mbResult['result']){
            //获取失败,可能是节点不可用,返回失败
            return false;
        }
        $flag = trim($mbResult['msg']['detail']);
        if("1" == $flag){
            //如果目录不存在,创建目录
            $cmd = "mkdir " . $this->autoBackupStoragePath;
            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            $msg = array('command'=>$cmd);
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            return $mbResult['result'];
        }
        return true;
    }
    
    /**
     * 得到自动备份配置
     * @param unknown $params
     */
    public function getAutoBakConfig($params){
        $filePath = Xphp::$_config['TMP_PATH'] . "autobak.config";
        if(!file_exists($filePath)){
            //如果配置文件不存在
            $info = array(
                "autoBakCheck" => false,
            );
        }
        //如果存在
        $fileInfo = file_get_contents($filePath);
        return $fileInfo;
    }
    
    /**
     * 设置自动备份配置
     * @param unknown $params
     */
    public function setAutoBakConfig($params){
        $autoBakCheck = $params['autoBakCheck'];
        $backupTime = $params['backupTime'];
        $reservedNum = $params['reservedNum'];
        $nodeuuid = $params['nodeuuid'];
        $storageuuid = $params['storageuuid'];
        $nodes = $params['nodes'];
        if($autoBakCheck){
            $this->paramsCheck($backupTime, $reservedNum, $nodeuuid, $storageuuid, $nodes);
        }
        //写入配置文件
        $filePath = Xphp::$_config['TMP_PATH'] . "autobak.config";
        $utils = Xphp::instance('Utils');
        $info = array(
            "autoBakCheck" => $autoBakCheck,
            "backupTime"  => $utils->formartTime($backupTime),
            "reservedNum"  => $reservedNum,
            "nodeuuid"  => $nodeuuid,
            "storageuuid"  => $storageuuid,
            "nodes"  => $nodes,
            "serverAddr" => $_SERVER['SERVER_ADDR'],
        );
        
        $operate = Xphp::$_lang['UI_SBH_AUTO_SETTING'];
        $result = file_put_contents($filePath, json_encode($info));
        
        if($result === false){
            $this->systemLog("SYSTEM_LOG_SYSBAKREC_SETTINGS", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
            return $this->muOpResult(false, $operate);
        }
        $this->systemLog("SYSTEM_LOG_SYSBAKREC_SETTINGS", array(), Xphp::$_config['LOGLEVEL']['NORMAL']);
        return $this->muOpResult(true, $operate);
    }
   	
   	/**
   	 *获取备份项目树
   	 */
    public function getBackupTree(){
        $bakTree = array();
        $bakDes = include CONF_PATH . "system_bak.php";
        
        $systemHandler = Xphp::instance('SystemHandler');
        $extension = $systemHandler->getExtensionLicense();
        $info = $extension['p'];
        
        foreach ($bakDes['BakTreeConf'] as $key => $value){
            $isin = in_array("tenant",$info);
            $licenseMulitTenant = false;
            if($isin){
                $licenseMulitTenant = true;
            }
            
            if($key==4 && !$licenseMulitTenant){
                continue;
            }
            
            $bakTree[] = array(
                'pid' => 0,
                'id' => $key,
                'name' => $value['title'],
                "open" => false,
                "title" => $value['title'],
                "type" => 1
            );
            foreach ($value['child'] as $mkey => $mvalue){
                $bakTree[] = array(
                    'pid' => $key,
                    'id' => $mkey,
                    'name' => $mvalue['title'],
                    "open" => false,
                    "title" => $mvalue['title'],
                    "type" => 2
                );
            }
        }
        return json_encode($bakTree);
	}
	
	//**********************************************************备份*********************************************************************//
	
	/**
	 * 准备手动备份环境
	 */
	private function prepareBackenvironment(){
	    $bakDir = Xphp::$_config['TMP_PATH'] . "systembak";
	    
	    $file = Xphp::instance('File');
	    //删除目录,并新建一个目录
	    if(file_exists($bakDir)){
	        //如果目录存在,给目录nginx:nginx权限(自动备份的时候是后台执行的,创建的目录是root:root权限)
	        $cmd = "chown nginx:nginx " . $bakDir;
	        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
	        $msg = array('command'=>$cmd);
	        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
	        if($mbResult['result']){
	            $file->removedir($bakDir);
	        }
	    }
	    $result = mkdir($bakDir, 0777);
	    if($result){
	        $this->systemBakDir = $bakDir;
	    }
	    
	    return $result;
	}
	
	/**
	 * 统一导出数据库数据到文件
	 * @param unknown $sql         sql语句
	 * @param unknown $filename    保存文件名
	 * @return boolean
	 */
	private function exportDatabasesToFile($sql, $tablename, $filename){
	    ini_set ('memory_limit', '2048M');
	    //获取所有信息
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $filename;
	    $info = array(
	        $tablename => $data
	    );
	    unset($data);
	    $result = file_put_contents($filename, json_encode($info));
	    if($result === false){
	        return false;
	    }
	    unset($result);
	    return true;
	}
	
	/**
	 * 备份节点
	 * @param unknown $nodes
	 * @param unknown $value 
	 */
	private function backupNodeManager($nodes, $value){
        //获取所有信息
//        $sql = "select * from bd_node";
//        return $this->exportDatabasesToFile($sql, "bd_node", $value['name']);

        $sql = "select * from bd_node";
        $data1 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $sql = "select * from bd_system_cache_config";
        $data2 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);

        //写入信息到文件
        $filename = $this->systemBakDir . "/" . $value['name'];
        $info = array(
            'bd_node' => $data1,
            'bd_system_cache_config' => $data2,
        );
        $result = file_put_contents($filename, json_encode($info));

        if($result === false){
            return false;
        }
        return true;
	}
	
	/**
	 * 备份存储
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupStorageManager($nodes, $value){
	    //获取所有信息
	    $sql = "select * from bd_storage_resource where lan_free_flag = " . Xphp::$_config['FLAG']['UNSET'];
	    return $this->exportDatabasesToFile($sql, "bd_storage_resource", $value['name']);
	}
	
	/**
	 * LAN-Free配置
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupStorageLanFree($nodes, $value){
	    //获取所有信息
	    $sql = "select * from bd_storage_resource where lan_free_flag = " . Xphp::$_config['FLAG']['SET'];
	    return $this->exportDatabasesToFile($sql, "bd_storage_resource", $value['name']);
	}
	
	/**
	 * 策略组
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupGlobalStrategy($nodes, $value){
	    //获取bd_strategy_group表
	    $sql = "select * from bd_strategy_group";
	    return $this->exportDatabasesToFile($sql, "bd_strategy_group", $value['name']);
	}
    /**
     * 限速策略
     */
    private function backupGlobalSpeedLimitStrategy($nodes,$value){
        //获取bd_global_speed_limit_strategy表
        $sql = "select * from bd_global_speed_limit_strategy";
        return $this->exportDatabasesToFile($sql, "bd_global_speed_limit_strategy", $value['name']);
    }
	
	/**
	 * 资源组
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupResourceGroup($nodes, $value){
	    //获取bd_resource_group表
	    $sql = "select * from bd_resource_group";
	    $data1 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_resource_resource_group表
	    $sql = "select * from mt_resource_resource_group";
	    $data2 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_user_group_resource_group表
	    $sql = "select * from mt_user_group_resource_group";
	    $data3 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_user_resource_group表
	    $sql = "select * from mt_user_resource_group";
	    $data4 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
		//获取mt_user_resource表
		$sql = "select * from mt_user_resource";
		$data5 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
		//获取mt_user_group_resource表
		$sql = "select * from mt_user_group_resource";
		$data6 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);


	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $info = array(
	        'bd_resource_group' => $data1,
	        'mt_resource_resource_group' => $data2,
	        'mt_user_group_resource_group' => $data3,
	        'mt_user_resource_group' => $data4,
			'mt_user_resource' => $data5,
			'mt_user_group_resource'=> $data6
	    );
	    $result = file_put_contents($filename, json_encode($info));
	    
	    if($result === false){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 用户
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupSafetyUser($nodes, $value){
	    //获取bd_user表
	    $sql = "select * from bd_user";
	    $data1 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取bd_user_extension表
	    $sql = "select * from bd_user_extension";
	    $data2 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);

        //获取资源转移数据（中间状态）bd_user_resource_transfer
        $sql = "select * from bd_user_resource_transfer";
        $data3 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);

        //获取关联用户信息
        $sql = "select * from bd_account_safe";
        $data4 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);

	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $info = array(
	        'bd_user' => $data1,
	        'bd_user_extension' => $data2,
            'bd_user_resource_transfer' => $data3,
            'bd_account_safe' => $data4,
	    );
	    $result = file_put_contents($filename, json_encode($info));
	    
	    if($result === false){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 用户组
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupSafetyUserGroup($nodes, $value){
	    //获取bd_user_group表
	    $sql = "select * from bd_user_group";
	    $data1 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取bd_user_extension表
	    $sql = "select * from mt_user_user_group";
	    $data2 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $info = array(
	        'bd_user_group' => $data1,
	        'mt_user_user_group' => $data2,
	    );
	    $result = file_put_contents($filename, json_encode($info));
	    
	    if($result === false){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 角色
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupSafetyRole($nodes, $value){
	    //获取bd_role表
	    $sql = "select * from bd_role";
	    $data1 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_user_role表
	    $sql = "select * from mt_user_role";
	    $data2 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_user_group_role表
	    $sql = "select * from mt_user_group_role";
	    $data3 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取bd_permission表
	    $sql = "select * from bd_permission";
	    $data4 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $info = array(
	        'bd_role' => $data1,
	        'mt_user_role' => $data2,
	        'mt_user_group_role' => $data3,
	        'bd_permission' => $data4,
	    );
	    $result = file_put_contents($filename, json_encode($info));
	    
	    if($result === false){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 域
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupSafetyDomain($nodes, $value){
	    //获取所有信息
	    $sql = "select * from bd_domain_server";
	    return $this->exportDatabasesToFile($sql, "bd_domain_server", $value['name']);
	}
	
	/**
	 * 域名解析
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupSystemDns($nodes, $value){
	    return true;
	}
	
	/**
	 * 网卡聚合
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupNicTeaming($nodes, $value){
	    return true;
	}
	
	/**
	 * 账户安全
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupAccountSafe($nodes, $value){
	    return true;
	}
	
	/**
	 * 存储安全
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupStorageSage($nodes, $value){
	    return true;
	}
	
	/**
	 * 可视化配置
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupVisualConfig($nodes, $value){
	    return true;
	}
	
	/**
	 * 多租户
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupTenantManager($nodes, $value){
	    //获取 bd_tenant表
	    $sql = "select * from  bd_tenant";
	    $data1 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_resource_group_tenant表
	    $sql = "select * from mt_resource_group_tenant";
	    $data2 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_user_group_tenant表
	    $sql = "select * from mt_user_group_tenant";
	    $data3 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_user_tenant表
	    $sql = "select * from mt_user_tenant";
	    $data4 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $info = array(
	        'bd_tenant' => $data1,
	        'mt_resource_group_tenant' => $data2,
	        'mt_user_group_tenant' => $data3,
	        'mt_user_tenant' => $data4,
	    );
	    $result = file_put_contents($filename, json_encode($info));
	    
	    if($result === false){
	        return false;
	    }
	    return true;
	}
	
	/**
	 * 费用
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupBillingManager($nodes, $value){
	    //获取 bd_billing表
	    $sql = "select * from  bd_billing";
	    $data1 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取bd_billing_details表
	    $sql = "select * from bd_billing_details";
	    $data2 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //获取mt_tenant_billing表
	    $sql = "select * from mt_tenant_billing";
	    $data3 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    
	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $info = array(
	        'bd_billing' => $data1,
	        'bd_billing_details' => $data2,
	        'mt_tenant_billing' => $data3,
	    );
	    $result = file_put_contents($filename, json_encode($info));
	    
	    if($result === false){
	        return false;
	    }
	    
	    return true;
	}
	
	/**
	 * 虚拟化中心
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupVcenterManager($nodes, $value){
	    //获取所有信息
//	    $sql = "select * from vm_vcenter";
//	    return $this->exportDatabasesToFile($sql, "vm_vcenter", $value['name']);
        //获取 vm_vcenter表
        $sql = "select * from  vm_vcenter";
        $data1 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);

        //获取mt_platform_node表
        $sql = "select * from mt_platform_node";
        $data2 = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);

        //写入信息到文件
        $filename = $this->systemBakDir . "/" . $value['name'];
        $info = array(
            'vm_vcenter' => $data1,
            'mt_platform_node' => $data2,
        );
        $result = file_put_contents($filename, json_encode($info));
        if($result === false){
            return false;
        }
        return true;
	}
	
	/**
	 * 客户端管理
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupAgentManager($nodes, $value){
	    //获取所有信息
	    $sql = "select * from bd_agent";
	    $bdAgent = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $info = array(
	        'bd_agent' => $bdAgent,
	    );
	    unset($bdAgent);
	    
	    $sql = "select * from bd_agent_app";
	    $agentApp = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $agentAppArray = array(
	        'bd_agent_app' => $agentApp,
	    );
	    $info = array_merge($info,$agentAppArray);
	    unset($agentApp);
	    unset($agentAppArray);
	    
	    $sql = "select * from bd_agent_disk";
	    $agentDisk = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $agentDiskArray = array(
	        'bd_agent_disk' => $agentDisk,
	    );
	    $info = array_merge($info,$agentDiskArray);
	    unset($agentDisk);
	    unset($agentDiskArray);
	    
	    $sql = "select * from bd_agent_group";
	    $agentGroup = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $agentGroupArray = array(
	        'bd_agent_group' => $agentGroup,
	    );
	    $info = array_merge($info,$agentGroupArray);
	    unset($agentGroup);
	    unset($agentGroupArray);

	    $sql = "select * from bd_agent_vol";
	    $agentVol = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $agentVolArray = array(
	        'bd_agent_vol' => $agentVol,
	    );
	    $info = array_merge($info,$agentVolArray);
	    unset($agentVol);
	    unset($agentVolArray);

        $sql = "select * from cdp_db_host";
        $cdpDbhost = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $cdpDbHostArray = array(
            'cdp_db_host' => $cdpDbhost,
        );

        $info = array_merge($info,$cdpDbHostArray);
        unset($cdpDbhost);
        unset($cdpDbHostArray);

	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $result = file_put_contents($filename, json_encode($info));
	    if($result === false){
	        return false;
	    }
	    unset($info);
	    return true;
	    
	}
	
	/**
	 * 实时备份代理
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupCDPAgentManager($nodes, $value){
	    //获取所有信息
	    $sql = "select * from cdp_db_host";
	    return $this->exportDatabasesToFile($sql, "cdp_db_host", $value['name']);
	}
	
	/**
	 * 传输代理
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupApplianceManager($nodes, $value){
	    //获取所有信息
	    $sql = "select * from bd_appliance where node_uuid  = ''"; //系统默认是一个appliance,有节点uuid,这里只导出其他appliance
	    return $this->exportDatabasesToFile($sql, "bd_appliance", $value['name']);
	}
	/**
	 * 虚拟实验室
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private  function backupSrvirtualManager($nodes,$value){
	    $sql = "select * from sr_network_map_list";
	    $srNetworkMapList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $info = array(
	        'sr_network_map_list' => $srNetworkMapList,
	    );
	    unset($srNetworkMapList);
	    
	    $sql = "select * from sr_virtual_lab";
	    $srVirtualLab = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $srVirtualLabArray = array(
	        'sr_virtual_lab' => $srVirtualLab,
	    );
	    $info = array_merge($info,$srVirtualLabArray);
	    unset($srVirtualLab);
	    unset($srVirtualLabArray);
    	    
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $result = file_put_contents($filename, json_encode($info));
	    if($result === false){
	        return false;
	    }
	    unset($info);
	    return true;
	    
	}
	
	/**
	 * nas 设备管理
	 * @param unknown $node
	 * @param unknown $value
	 */
	private function backupNasDeviceManager($node,$value){
	    //获取nas_storage_resource表
	    $sql = "select * from nas_storage_resource";
	    $nasStorageResoureTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	  
	    $info = array(
	        'nas_storage_resource' => $nasStorageResoureTask,
	    );
	    unset($nasStorageResoureTaskArray);
	    
	    //获取表nas_mount_list
	    $sql = "select * from nas_mount_list";
	    $nasMountListTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $nasMountListTaskArray = array(
	        'nas_mount_list' => $nasMountListTask,
	    );
	    $info = array_merge($info,$nasMountListTaskArray);
	    unset($nasMountListTaskArray);
	    
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    $result = file_put_contents($filename, json_encode($info));
	    if($result === false){
	        return false;
	    }
	    unset($info);
	    return true;
	}

    /**
     * m365组织管理
     * @param unknown $node
     * @param unknown $value
     */
    private function backupM365Manager($node,$value){
        //获取m365_organization表
        $sql = "select * from m365_organization";
        $m365Organization = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);

        $info = array(
            'm365_organization' => $m365Organization,
        );
        unset($m365Organization);

        //获取表m365_azure_ad_app Azure AD应用程序表
        $sql = "select * from m365_azure_ad_app";
        $m365AzureAdApp = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $m365AzureAdAppArray = array(
            'm365_azure_ad_app' => $m365AzureAdApp,
        );
        $info = array_merge($info,$m365AzureAdAppArray);
        unset($m365AzureAdAppArray);


        //获取表m365_user Microsoft365组织下用户或用户组表
        $sql = "select * from m365_user";
        $m365User = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $m365UserArray = array(
            'm365_user' => $m365User,
        );

        $info = array_merge($info,$m365UserArray);
        unset($m365UserArray);

        $filename = $this->systemBakDir . "/" . $value['name'];
        $result = file_put_contents($filename, json_encode($info));
        if($result === false){
            return false;
        }
        unset($info);
        return true;
    }
	/**
	 * Summary of backupHadoopManager
	 * 获取Hadoop 资源
	 * @return void
	 */
	private function backupHadoopManager($node,$value){
		//获取hadoop cluster 表
		$sql = "select * from hadoop_cluster";
		$hadoopCluster = $this->dbSelect($sql,array(),PDO::FETCH_ASSOC);
		$info = array(
            'hadoop_cluster' => $hadoopCluster,
        );
        unset($hadoopCluster);

		$sql = "select * from hadoop_namenode";
		$hadoopNamenode = $this->dbSelect($sql,array(),PDO::FETCH_ASSOC);
		$m365UserArray = array(
            'hadoop_namenode' => $hadoopNamenode,
        );

        $info = array_merge($info,$m365UserArray);
        unset($m365UserArray);

		$filename = $this->systemBakDir . "/" . $value['name'];
        $result = file_put_contents($filename, json_encode($info));
        if($result === false){
            return false;
        }
        unset($info);
        return true;
	}
	/**
	 * 获取对象存储资源，对应表：obs_resource
	 * @param mixed $node
	 * @param mixed $value
	 * @return void
	 */
	private function backupObsManager($node,$value) {
		$sql = "select * from obs_resource";
		$obsResource = $this->dbSelect($sql,array(),PDO::FETCH_ASSOC);
		$info = array(
            'obs_resource' => $obsResource,
        );
        unset($obsResource);

		$filename = $this->systemBakDir . "/" . $value['name'];
        $result = file_put_contents($filename, json_encode($info));
        if($result === false){
            return false;
        }
        unset($info);
        return true;
	}
	/**
	 * 获取任务UUID，通过策略id
	 */
	private function getTaskuuidByStrategyId($strategy_id){
	    $sql = "select task_uuid from bd_task where strategy_id = ?";
	    $data = $this->dbSelect($sql,array($strategy_id));
	    $task_uuid = "";
	    if(!empty($data)){
	        $task_uuid = $data[0]['task_uuid'];
	    }
	    return $task_uuid;
	}
	/**
	 * 当前任务
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupCurrentJob($nodes, $value){ 
	    //获取bd_task表:任务总表
	    $sql = "select bt.*,bn.node_type from bd_task as bt left join bd_node as bn on bt.node_uuid = bn.node_uuid order by task_uuid";
	    $bdTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $info = array(
	        'bd_task' => $bdTask,
	    );
	    unset($bdTask);
	    //获取bd_running_info表:任务运行表
	    $sql = "select * from bd_running_info order by task_uuid";
	    $bdRunningInfo = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdRunningInfoArray = array(
	        'bd_running_info' => $bdRunningInfo,
	    );
	    $info = array_merge($info,$bdRunningInfoArray);
	    unset($bdRunningInfoArray);
	    
	    //获取bd_strategy表:策略总表
	    $sql = "select * from bd_strategy";
	    $bdStrategy = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdStrategyArray = array(
	        'bd_strategy' => $bdStrategy,
	    );
	    $info = array_merge($info,$bdStrategyArray);
	    unset($bdStrategyArray);
	    
	    //获取bd_time_strategy表:时间策略
	    /**
	    $sql = "select * from bd_time_strategy";
	    $bdTimeStrategy = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdTimeStrategyArray = array(
	        'bd_time_strategy' => $bdTimeStrategy,
	    );
	    **/
	    //因副本任务bd_time_strategy表中未插入任务uuid，恢复时无法去重。这里特殊处理，判断任务uuid 为空时根据策略id在任务表中获取任务uuid
	    $sql = "select * from bd_time_strategy";
	    $data =  $this->dbSelect($sql, array());
	    $bdTimeStrategy = array();
	    if(!empty($data)){
	        foreach ($data as $d){
	            $task_uuid = $d['task_uuid'];
	            $strategy_id = $d['strategy_id'];
	            if(empty($task_uuid)){
	                $task_uuid = $this->getTaskuuidByStrategyId($strategy_id);
	            }
	            $bdTimeStrategy[] = array(
	                'id' => $d['id'],
	                "strategy_id" => $strategy_id,
	                "mode"=> $d['mode'],
	                "strategy_type"=> $d['strategy_type'],
	                "days"=> $d['days'],
	                "start_time"=> $d['start_time'],
	                "roll_flag"=> $d['roll_flag'],
	                "roll_interval"=> $d['roll_interval'],
	                "roll_end_time"=> $d['roll_end_time'],
	                "last_start_time"=> $d['last_start_time'],
	                "last_finish_time"=> $d['last_finish_time'],
	                "global_id"=> $d['global_id'],
	                "strategy_group_uuid"=> $d['strategy_group_uuid'],
	                "task_uuid"=> $task_uuid,
	            );
	        }
	        $bdTimeStrategyArray = array(
	            'bd_time_strategy' => $bdTimeStrategy,
	        );
	    }

	    if(!empty($bdTimeStrategyArray)){
            $info = array_merge($info,$bdTimeStrategyArray);
        }

	    unset($bdTimeStrategyArray);
	    
	    //获取bd_reserved_strategy表:保留策略
	    $sql = "select * from bd_reserved_strategy";
	    $bdReservedStrategy = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdReservedStrategyArray = array(
	        'bd_reserved_strategy' => $bdReservedStrategy,
	    );
        if(!empty($bdReservedStrategyArray)){
            $info = array_merge($info,$bdReservedStrategyArray);
        }

	    unset($bdReservedStrategyArray);
	    
	    //获取bd_transport_strategy表:传输策略
	    $sql = "select * from bd_transport_strategy";
	    $bdTransportStrategy = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdTransportStrategyArray = array(
	        'bd_transport_strategy' => $bdTransportStrategy,
	    );
        if(!empty($bdTransportStrategyArray)){
            $info = array_merge($info,$bdTransportStrategyArray);
        }
	    unset($bdTransportStrategyArray);
	    
	    //获取bd_transport_strategy表:传输策略
	    $sql = "select * from bd_node_network";
	    $bdNodeNetwork = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdNodeNetworkArray = array(
	        'bd_node_network' => $bdNodeNetwork,
	    );

	    $info = array_merge($info,$bdNodeNetworkArray);
	    unset($bdNodeNetworkArray);
	    
	    //获取bd_task_agent_list表
	    $sql = "select * from bd_task_agent_list";
	    $bdTaskAgentList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdTaskAgentListArray = array(
	        'bd_task_agent_list' => $bdTaskAgentList,
	    );
	    $info = array_merge($info,$bdTaskAgentListArray);
	    unset($bdTaskAgentListArray);
	    
	    //获取bd_storage_strategy表:存储策略
	    $sql = "select * from bd_storage_strategy";
	    $bdStorageStrategy = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdStorageStrategyArray = array(
	        'bd_storage_strategy' => $bdStorageStrategy,
	    );
	    $info = array_merge($info,$bdStorageStrategyArray);
	    unset($bdStorageStrategyArray);
	    
	    //获取bd_task_speed_limit_strategy表:限速策略
	    $sql = "select * from bd_task_speed_limit_strategy";
	    $bdTaskSpeedLimitStrategy = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $SpeedLimitStrategyArray = array(
	        'bd_task_speed_limit_strategy' => $bdTaskSpeedLimitStrategy,
	    );
	    $info = array_merge($info,$SpeedLimitStrategyArray);
	    unset($SpeedLimitStrategyArray);
	    
	    //*************************************************实时备份****************************************//
	    
	    //获取cdp_db_task表:数据库CDP
	    $sql = "select * from cdp_db_task";
	    $cdpDbTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpDbTaskArray = array(
	        'cdp_db_task' => $cdpDbTask,
	    );
	    $info = array_merge($info,$cdpDbTaskArray);
	    unset($cdpDbTask);
	    
	    //获取cdp_fs_task表:文件实时同步
	    $sql = "select * from cdp_fs_task";
	    $cdpFsTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpFsTaskArray = array(
	        'cdp_fs_task' => $cdpFsTask,
	    );
	    $info = array_merge($info,$cdpFsTaskArray);
	    unset($cdpFsTaskArray);
	    
	    //*************************************************实时容灾****************************************//
	    //获取cdp_vol_task表
	    $sql = "select * from cdp_vol_task";
	    $cdpVolTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $volCdpTaskArray = array(
	        'cdp_vol_task' => $cdpVolTask,
	    );
	    $info = array_merge($info,$volCdpTaskArray);
	    unset($volCdpTaskArray);
	    
	    //获取cdp_vol_task_cache_info表
	    $sql = "select * from cdp_vol_task_cache_info";
	    $cdpVolTaskCache = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $volCdpTaskCacheArray = array(
	        'cdp_vol_task_cache_info' => $cdpVolTaskCache,
	    );
	    $info = array_merge($info,$volCdpTaskCacheArray);
	    unset($volCdpTaskCacheArray);
	    
	    //获取cdp_vol_task_data_consistency_check_info表
	    $sql = "select * from cdp_vol_task_data_consistency_check_info";
	    $cdpVolTaskDataCons = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $volCdpTaskDataConsArray = array(
	        'cdp_vol_task_data_consistency_check_info' => $cdpVolTaskDataCons,
	    );
	    $info = array_merge($info,$volCdpTaskDataConsArray);
	    unset($volCdpTaskDataConsArray);
	    
	    //获取cdp_vol_task_io_mapping_vol表信息
	    $sql = "select * from cdp_vol_task_io_mapping_vol";
	    $cdpVolTaskIoMapp = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $volCdpTaskIoMappArray = array(
	        'cdp_vol_task_io_mapping_vol' => $cdpVolTaskIoMapp,
	    );
	    $info = array_merge($info,$volCdpTaskIoMappArray);
	    unset($volCdpTaskIoMappArray);
	    
	    //获取cdp_vol_task_progress_info表信息
	    $sql = "select * from cdp_vol_task_progress_info";
	    $cdpVolTaskProgress = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $volCdpTaskProgressArray = array(
	        'cdp_vol_task_progress_info' => $cdpVolTaskProgress,
	    );
	    $info = array_merge($info,$volCdpTaskProgressArray);
	    unset($volCdpTaskProgressArray);
	    
	    //获取cdp_vol_task_running_info表信息
	    $sql = "select * from cdp_vol_task_running_info";
	    $cdpVolTaskRunningInfo = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $volCdpTaskRunningArray = array(
	        'cdp_vol_task_running_info' => $cdpVolTaskRunningInfo,
	    );
	    $info = array_merge($info,$volCdpTaskRunningArray);
	    unset($volCdpTaskRunningArray);
	    
	    //获取cdp_vol_task_takeover_app表信息
	    $sql = "select * from cdp_vol_task_takeover_app";
	    $cdpVolTaskTakeoverApp = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpVolTaskTakeoverAppArray = array(
	        'cdp_vol_task_takeover_app' => $cdpVolTaskTakeoverApp,
	    );
	    $info = array_merge($info,$cdpVolTaskTakeoverAppArray);
	    unset($cdpVolTaskTakeoverAppArray);
	    
	    //获取cdp_vol_task_takeover_failback_info表信息
	    $sql = "select * from cdp_vol_task_takeover_failback_info";
	    $cdpVolTaskTakeoverFailback = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpVolTaskTakeoverFailbackArray = array(
	        'cdp_vol_task_takeover_failback_info' => $cdpVolTaskTakeoverFailback,
	    );
	    $info = array_merge($info,$cdpVolTaskTakeoverFailbackArray);
	    unset($cdpVolTaskTakeoverFailbackArray);
	    
	    //获取cdp_vol_task_takeover_info表信息
	    $sql = "select * from cdp_vol_task_takeover_info";
	    $cdpVolTaskTakeoverInfo = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpVolTaskTakeoverInfoArray = array(
	        'cdp_vol_task_takeover_info' => $cdpVolTaskTakeoverInfo,
	    );
	    $info = array_merge($info,$cdpVolTaskTakeoverInfoArray);
	    unset($cdpVolTaskTakeoverInfoArray);
	    
	    //获取cdp_vol_task_takeover_lun表信息
	    $sql = "select * from cdp_vol_task_takeover_lun";
	    $cdpVolTaskTakeoverLun = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpVolTaskTakeoverLunArray = array(
	        'cdp_vol_task_takeover_lun' => $cdpVolTaskTakeoverLun,
	    );
	    $info = array_merge($info,$cdpVolTaskTakeoverLunArray);
	    unset($cdpVolTaskTakeoverLunArray);
	    
	    //获取cdp_vol_task_takeover_script表信息
	    $sql = "select * from cdp_vol_task_takeover_script";
	    $cdpVolTaskTakeoverScript = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpVolTaskTakeoverScriptArray = array(
	        'cdp_vol_task_takeover_script' => $cdpVolTaskTakeoverScript,
	    );
	    $info = array_merge($info,$cdpVolTaskTakeoverScriptArray);
	    unset($cdpVolTaskTakeoverScriptArray);
	    
	    //获取cdp_vol_task_takeover_target表信息
	    $sql = "select * from cdp_vol_task_takeover_target";
	    $cdpVolTaskTakeoverTarget = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpVolTaskTakeoverTargetArray = array(
	        'cdp_vol_task_takeover_target' => $cdpVolTaskTakeoverTarget,
	    );
	    $info = array_merge($info,$cdpVolTaskTakeoverTargetArray);
	    unset($cdpVolTaskTakeoverTargetArray);
	    
	    //获取cdp_vol_task_vol表信息
	    $sql = "select * from cdp_vol_task_vol";
	    $cdpVolTaskVol = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpVolTaskVolArray = array(
	        'cdp_vol_task_vol' => $cdpVolTaskVol,
	    );
	    $info = array_merge($info,$cdpVolTaskVolArray);
	    unset($cdpVolTaskVolArray);
	    
	    //*************************************************虚拟机****************************************//
	    //获取vm_task表:虚拟机任务
	    $sql = "select * from vm_task";
	    $vmTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $vmTaskArray = array(
	        'vm_task' => $vmTask,
	    );
	    $info = array_merge($info,$vmTaskArray);
	    unset($vmTaskArray);
	    
	    //获取vm_machine_list表:虚拟机列表
	    $sql = "select * from vm_machine_list";
	    $vmMachineList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $vmMachineListArray = array(
	        'vm_machine_list' => $vmMachineList,
	    );
	    $info = array_merge($info,$vmMachineListArray);
	    unset($vmMachineListArray);

        //获取vm_object_list表: 虚拟机对象列表
        $sql = "select * from vm_object_list";
        $vmObjectList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $vmObjectListArray = array(
            'vm_object_list' => $vmObjectList,
        );
        $info = array_merge($info,$vmObjectListArray);
        unset($vmObjectListArray);

	    //获取虚拟机瞬时恢复列表
	    /*该模块的备份与恢复在9.19后评估添加
	    $sql = "select * from vm_instant";
	    $vmInstant = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $vmInstantListArray = array(
	        "vm_instant" => $vmInstantListArray,
	    );
	    $info = array_merge($info,$vmInstantListArray);
	    unset($vmInstantListArray);
	    
	    //获取细粒度恢复列表
	    $sql = "select * from vm_grain_info";
	    $vmGrainInfo = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $vmGrainInfoArray = array(
	        "vm_grain_info" => $vmGrainInfo,
	    );
	    $info = array_merge($info,$vmGrainInfoArray);
	    unset($vmGrainInfoArray);
	    */
	    
	    //*************************************************虚拟机****************************************//
	    
	    
	    //*************************************************文件****************************************//
	    
	    //获取fs_task表:文件任务表
	    $sql = "select * from fs_task";
	    $fsTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $fsTaskArray = array(
	        'fs_task' => $fsTask,
	    );
	    $info = array_merge($info,$fsTaskArray);
	    unset($fsTaskArray);
	    
	    //获取fs_running_info表:文件任务运行表
	    $sql = "select * from fs_running_info";
	    $fsRunningInfo = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $fsRunningInfoArray = array(
	        'fs_running_info' => $fsRunningInfo,
	    );
	    $info = array_merge($info,$fsRunningInfoArray);
	    unset($fsRunningInfoArray);
	    
	    //获取fs_path_list表:文件列表
	    $sql = "select * from fs_path_list";
	    $fsPathList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $fsPathListArray = array(
	        'fs_path_list' => $fsPathList,
	    );
	    $info = array_merge($info,$fsPathListArray);
	    unset($fsPathListArray);
	   
	    //*************************************************NAS***************************************//
	    //获取nas_task表:nas任务表
	    $sql = "select * from nas_task";
	    $nasTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $nasTaskArray = array(
	        'nas_task' => $nasTask,
	    );
	    $info = array_merge($info,$nasTaskArray);
	    unset($nasTaskArray);
	    
	    
	    //*************************************************数据库****************************************//
	    
	    //获取db_instance表:数据库实例认证表
	    $sql = "select * from db_instance";
	    $dbInstance = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $dbInstanceArray = array(
	        'db_instance' => $dbInstance,
	    );
	    $info = array_merge($info,$dbInstanceArray);
	    unset($dbInstanceArray);
	    
	    //获取db_list表:数据库列表
	    $sql = "select * from db_list";
	    $dbList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $dbListArray = array(
	        'db_list' => $dbList,
	    );
	    $info = array_merge($info,$dbListArray);
	    unset($dbListArray);
	    
	    //获取db_task表:数据库任务列表
	    $sql = "select * from db_task";
	    $dbTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $dbTaskArray = array(
	        'db_task' => $dbTask,
	    );
	    $info = array_merge($info,$dbTaskArray);
	    unset($dbTaskArray);
	    //*************************************************操作系统****************************************//
	    //获取os_task表:操作系统任务表
	    $sql = "select * from os_task";
	    $osTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $osTaskArray = array(
	        'os_task' => $osTask,
	    );
	    $info = array_merge($info,$osTaskArray);
	    unset($osTaskArray);
	    
	    //获取os_list表:备份/恢复操作系统列表
	    $sql = "select * from os_list";
	    $osList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $osListArray = array(
	        'os_list' => $osList,
	    );
	    $info = array_merge($info,$osListArray);
	    unset($osList);
	    
	    //*************************************************副本和归档****************************************//
	    
	    //获取backup_copy_task表:副本和归档任务表
	    $sql = "select * from backup_copy_task";
	    $backupCopyTask = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $backupCopyTaskArray = array(
	        'backup_copy_task' => $backupCopyTask,
	    );
	    $info = array_merge($info,$backupCopyTaskArray);
	    unset($backupCopyTaskArray);
	    
	    //获取backup_copy_item_list表:副本和归档备份列表
	    $sql = "select * from backup_copy_item_list";
	    $backCopyItemList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $backCopyItemListArray = array(
	        'backup_copy_item_list' => $backCopyItemList,
	    );
	    $info = array_merge($info,$backCopyItemListArray);
	    unset($backCopyItemListArray);

        //copy_task    //副本任务二级表
        $sql = "select * from copy_task";
        $copyTaskList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $copyTaskListArray = array(
            'copy_task' => $copyTaskList,
        );
        $info = array_merge($info,$copyTaskListArray);
        unset($copyTaskListArray);
        //======================================Microsoft 365============================================//
        //Microsoft365任务二级表，存放Microsoft365任务特有信息
        $sql = "select * from m365_task";
        $m365TaskList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $m365TaskListArray = array(
            'm365_task' => $m365TaskList,
        );
        $info = array_merge($info,$m365TaskListArray);
        unset($m365TaskListArray);

        //Microsoft365任务数据二级表，存放选择的备份或恢复数
        $sql = "select * from m365_object_list";
        $m365ObjectList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $m365ObjectListArray = array(
            'm365_object_list' => $m365ObjectList,
        );
        $info = array_merge($info,$m365ObjectListArray);
        unset($m365ObjectListArray);

        //Microsoft365任务运行信息二级表，存放Microsoft365
        $sql = "select * from m365_running_info";
        $m365RunningInfoList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $m365RunningInfoListArray = array(
            'm365_running_info' => $m365RunningInfoList,
        );
        $info = array_merge($info,$m365RunningInfoListArray);
        unset($m365RunningInfoListArray);

        //copy_running_info 1副本运行信息 也可以视为bd_running_info的二级表
        $sql = "select * from copy_running_info";
        $copyRunningInfoList = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $copyRunningInfoArray = array(
            'copy_running_info' => $copyRunningInfoList,
        );
        $info = array_merge($info,$copyRunningInfoArray);
        unset($copyRunningInfoArray);

        //copy_list 副本任务对象列表
        $sql = "select * from copy_list";
        $copyListInfo = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $copyListInfoArray = array(
            'copy_list' => $copyListInfo,
        );
        $info = array_merge($info,$copyListInfoArray);
        unset($copyListInfoArray);

	    //*************************************************副本和归档****************************************//
	    
	    //*************************************************GFS****************************************//
	    
	    //获取bd_task_gfs_retention_strategy表：GFS保留策略表
	    $sql = "select * from bd_task_gfs_retention_strategy";
	    $bdTaskGfsRetentionStrategy = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $gfsRetentionStrategyArray = array(
	        'bd_task_gfs_retention_strategy' => $bdTaskGfsRetentionStrategy,
	    );
	    $info = array_merge($info,$gfsRetentionStrategyArray);
	    unset($gfsRetentionStrategyArray);
	    
	    //获取bd_gfs_entity_waiting_map表: GFS等待map表
	    $sql = "select * from bd_gfs_entity_waiting_map";
	    $bdGfsEntityWaitingMap = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $gfsEntityWaitingMapArray = array(
	        'bd_gfs_entity_waiting_map' => $bdGfsEntityWaitingMap,
	    );
	    $info = array_merge($info,$gfsEntityWaitingMapArray);
	    unset($gfsEntityWaitingMapArray);
	    //*************************************************GFS****************************************//
	    
	    //虚拟实验室相关任务
	    $sql = "select * from sr_sure_backup";
	    $srSureBackup = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $srSureBackupArray = array(
	        'sr_sure_backup' => $srSureBackup,
	    );
	    $info = array_merge($info,$srSureBackupArray);
	    unset($srSureBackup);
	    unset($srSureBackupArray);
	    
	    $sql = "select * from sr_sure_backup_instant_vm";
	    $srSureBackupInstantVm = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $srSureBackupInstantVmArray = array(
	        'sr_sure_backup_instant_vm' => $srSureBackupInstantVm,
	    );
	    $info = array_merge($info,$srSureBackupInstantVmArray);
	    unset($srSureBackupInstantVm);
	    unset($srSureBackupInstantVmArray);
	    
	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    //TODO如果后面发现这里文件太大后,可以分成几个文件
	    $result = file_put_contents($filename, json_encode($info));
	    if($result === false){
	        return false;
	    }
	    unset($info);
	    return true;
	}
	
	/**
	 * 历史任务
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupHistoryJob($nodes, $value){
	    //获取所有信息
// 	    $sql = "select * from bd_history_task";
// 	    return $this->exportDatabasesToFile($sql, "bd_history_task", $value['name']);
	    //获取bd_history_task表
	    ini_set ('memory_limit', '2048M');
	    $sql = "select * from bd_history_task";
	    $bdHistoryTaskArray = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $info = array(
	        'bd_history_task' => $bdHistoryTaskArray,
	    );
	    unset($bdHistoryTaskArray);
	    
	    $sql = "select * from bd_storage_monitor";
	    $bdStorageMonitorArray = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $bdStorageMonitor = array(
	        'bd_storage_monitor' => $bdStorageMonitorArray,
	    );
	    $info = array_merge($info,$bdStorageMonitor);
	    unset($bdStorageMonitorArray);
	    unset($bdStorageMonitor);
	    
	    //虚拟实验室自动验证报告表 sr_sure_backup_report
	    $sql = "select * from sr_sure_backup_report";
	    $srSureBackupReportArray = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $srSureBackupReport = array(
	        'sr_sure_backup_report' => $srSureBackupReportArray,
	    );
	    $info = array_merge($info,$srSureBackupReport);
	    unset($srSureBackupReportArray);
	    unset($srSureBackupReport);
	    
	    //卷CDP模块历史任务信息
	    $sql = "select * from cdp_vol_history_task";
	    $cdpVolHistoryTaskArray = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    $cdpVolHistoryTask = array(
	        'cdp_vol_history_task' => $cdpVolHistoryTaskArray,
	    );
	    $info = array_merge($info,$cdpVolHistoryTask);
	    unset($cdpVolHistoryTaskArray);
	    unset($cdpVolHistoryTask);

	    //新增表os_migration_history   //操作系统迁移历史，历史任务使用
        $sql = "select * from os_migration_history";
        $osMigrationHistoryArray = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        $osMigrationHistory = array(
            'os_migration_history' => $osMigrationHistoryArray,
        );
        $info = array_merge($info,$osMigrationHistory);
        unset($osMigrationHistory);
	    
	    //写入信息到文件
	    $filename = $this->systemBakDir . "/" . $value['name'];
	    //TODO如果后面发现这里文件太大后,可以分成几个文件
	    $result = file_put_contents($filename, json_encode($info));
	    if($result === false){
	        return false;
	    }
	    unset($info);
	    return true;
	  
	}

	
	/**
	 * 任务告警
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupTaskAlarm($nodes, $value){
	    //获取所有信息     
	    $sql = "select * from bd_task_alarm";
	    return $this->exportDatabasesToFile($sql, "bd_task_alarm", $value['name']);
	}
	
	/**
	 * 系统告警
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupSystemAlarm($nodes, $value){
	    //获取所有信息
	    $sql = "select * from bd_system_alarm";
	    return $this->exportDatabasesToFile($sql, "bd_system_alarm", $value['name']);
	}
	
	/**
	 * 任务操作日志
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupJobLog($nodes, $value){
	    //获取所有信息
	    $sql = "select * from bd_task_log";
	    return $this->exportDatabasesToFile($sql, "bd_task_log", $value['name']);
	}
	
	/**
	 * 系统操作日志
	 * @param unknown $nodes
	 * @param unknown $value
	 */
	private function backupSystemLog($nodes, $value){
	    //获取所有信息
	    $sql = "select * from bd_system_log";
	    return $this->exportDatabasesToFile($sql, "bd_system_log", $value['name']);
	}
	
	
	
	/**
	 * 备份第一层统一调用
	 * @param unknown $nodes
	 * @param unknown $child
	 * @return boolean
	 */
	private function backupLayerOneCall($nodes, $child){
	    $result = true;
	    foreach ($child as $key => $value){
	        //按配置文件第二层遍历,如果选择了此项,则调用此项的备份方法,进入备份流程
	        if(in_array($key, $nodes) && $result){
	            $function = $value['bakFuntion'];
	            $callResult = call_user_func_array(array($this, $function), array($nodes, $value));
	            $result = $result && $callResult;
	        }
	    }
	    return $result;
	}
	
	/**
	 * 备份基础设施
	 * 备份节点 备份存储 LAN-Free配置 备份策略 资源组
	 * @param unknown $nodes
	 */
	private function backupInfrastructure($nodes, $child){
	    return $this->backupLayerOneCall($nodes, $child);
	}
	
	/**
	 * 备份安全信息
	 * 用户 用户组 角色 域
	 * @param unknown $nodes
	 */
	private function backupSecurityInfo($nodes, $child){
	    return $this->backupLayerOneCall($nodes, $child);
	}
	
	/**
	 * 备份系统配置
	 * 域名解析 网卡聚合 账户安全 存储安全 可视化配置
	 * @param unknown $nodes
	 */
	private function backupSystemInfo($nodes, $child){
	    return $this->backupLayerOneCall($nodes, $child);
	}
	
	/**
	 * 备份多租户
	 * 租户 费用
	 * @param unknown $nodes
	 */
	private function backupMultitenant($nodes, $child){
	    return $this->backupLayerOneCall($nodes, $child);
	}
	
	/**
	 * 备份备份资源
	 * 虚拟化中心 数据库代理
	 * @param unknown $nodes
	 */
	private function backupBackupResource($nodes, $child){
	    return $this->backupLayerOneCall($nodes, $child);
	}
	
	/**
	 * 备份任务
	 * 当前任务 历史任务
	 * @param unknown $nodes
	 */
	private function backupJob($nodes, $child){
	    return $this->backupLayerOneCall($nodes, $child);
	}
	
	/**
	 * 备份告警
	 * 任务告警 系统告警
	 * @param unknown $nodes
	 */
	private function backupAlarm($nodes, $child){
	    return $this->backupLayerOneCall($nodes, $child);
	}
	
	/**
	 * 备份日志
	 * 任务操作日志 系统操作日志
	 * @param unknown $nodes
	 */
	private function backupLog($nodes, $child){
	    return $this->backupLayerOneCall($nodes, $child);
	}
	
	//**********************************************************备份*********************************************************************//
	
	//**********************************************************恢复*********************************************************************//
	
	/**
	 * 检查用户选择的恢复信息,并返回恢复信息,供用户确认
	 * @param unknown $params
	 */
	public function checkRecData($params){
	    $bakDes = include CONF_PATH . "system_bak.php";
	    $operate = Xphp::$_lang['UI_SBH_CHECK_DATA'];
	    $recType = intval($params['srctype']);
	    $uuid = $params['uuid'];
	    $nodes = $params['nodes'];
	    $this->paramsCheck($nodes);
	    $recDir = Xphp::$_config['TMP_PATH'] . "systemrec/";
	    if(!in_array($recType,$bakDes['RecSrcType'])){
	        return $this->muOpResult(false, $operate, Xphp::$_lang['UI_SBH_PLEASE_CHECK'], "warning");
	    }
	    //检查恢复文件是否可用
	    if($bakDes['RecSrcType']['autoSrc'] == $recType){
	        //如果是自动恢复源,拷贝备份文件到本地工作目录,并解压,然后可以进入下一步
	        $sql = "select file_name, file_size, file_path, node_uuid, storage_uuid from bd_system_backup_data where backup_uuid = ?";
	        $data = $this->dbSelect($sql, array($uuid));
	        
	        $filesize = $data[0]['file_size'];
	        $filepath = $data[0]['file_path'];
	        $nodeuuid = $data[0]['node_uuid'];
	        $filename = $data[0]['file_name'];
	        
	        //先检查文件的大小
	        $opName = "NODE_SYS_OP_GET_FILE_SIZE";
	        $storageHandler = Xphp::instance('StorageHandler');
	        $operate = $storageHandler->getUnifyOpcodeDes($opName);
	        $msg = array(
	            'file_path' => $filepath
	        );
	        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
	        if(!$mbResult['result']){
	            //获取文件大小失败
	            return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
	        }else{
	            //获取成功,检查获取的大小是否和数据库存储的大小一致
	            if($mbResult['msg']['file_size'] != $filesize){
	                return $this->muOpResult(false, $operate, '', 'warning');
	            }
	        }
	        
	        //清理并创建恢复目录
	        $this->clearnRecoveryDir();
	        $recFileName = Xphp::$_config['TMP_PATH'] . "systemrec/" . $filename;
	        
	        //下载文件到本地
	        $opName = "NODE_SYS_OP_PREAD_FILE";
	        $storageHandler = Xphp::instance('StorageHandler');
	        $operate = $storageHandler->getUnifyOpcodeDes($opName);
	        $blockSize = Xphp::$_config['GRAIN_FILE_BLOCK_SIZE'];
	        //如果大于分块大小,分块下载
	        for($i=0; $i<$filesize; $i = $i + $blockSize){
	            if($filesize - $i <= $blockSize){
	                $readLen = $filesize - $i;
	            }else{
	                $readLen = $blockSize;
	            }
	            $msg = array(
	                "file_path" => $filepath,
	                "offset" => $i,
	                "length" => $readLen,
	            );
	            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
	            
	            if(0 == $i){
	                $result = file_put_contents($recFileName, $mbResult);
	            }else{
	                $result = file_put_contents($recFileName, $mbResult, FILE_APPEND);
	            }
	            ob_flush(); //将数据从php的buffer中释放出来
	        }
	        //解压文件
	        $tmpPath = Xphp::$_config['TMP_PATH'] . "systemrec/";
	        if(!file_exists($recFileName)){
	            return $this->muOpResult(false, $operate, Xphp::$_lang['UI_SBH_NOTFILE_DECOMPRESS_FAIL'], 'warning');
	        }
	        $cmd = "unzip -P " . Xphp::$_config['ZIP_PASS'] . " " . $recFileName . " -d " . $tmpPath;
	        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
	        $msg = array('command'=>$cmd);
	        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
	        if(!$mbResult['result']){
	            return $this->muOpResult(false, $operate, Xphp::$_lang['UI_SBH_FILE_DECOMPRESS_FAIL'], 'warning');
	        }
	        
	        
	    }elseif($bakDes['RecSrcType']['uploadSrc'] == $recType){
	        //如果是手动恢复源,因为上传的时候已经解压了,这里再检查一下解压后的配置文件是否存在,存在表示可以进入下一步
	        $configPath = $recDir . "config";
	        if(!file_exists($configPath)){
	            return $this->muOpResult(false, $operate, Xphp::$_lang['UI_SBH_NOTFILE_PLEASE_RETRY'], "warning");
	        }
	    }
	    
	    //检查是否有任务运行中
	    $sql = "select task_name from bd_task where task_status = ?";
	    $data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['RUNNING']));
	    $taskNames = array();
	    if(count($data) > 0){
	        foreach ($data as $d){
	            $taskNames[] = $d['task_name'];
	        }
	        return $this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_CHECK_TASK'], Xphp::$_lang['WEB_SYSTEM_RUNNING_NOW'], "warning", 0, $taskNames);
	    }
	    
	    return $this->muOpResult(true, $operate);
	}
	
	/**
	 * 开始恢复系统
	 */
	public function startRecoverySystemNow($params){
	    //由于是前端发过来的数据,这里再检查一下参数
	    $checkResult = $this->checkRecData($params);
	    $checkResult = json_decode($checkResult, true);
	    if(!$checkResult['re']){
	        //如果检查失败
	        return false;
	    }
	    $nodes = $params['nodes'];
	    return $this->startRecoverySystem($nodes);
	}
	
	/**
	 * 获取恢复过程信息
	 * @param unknown $params
	 */
	public function getRecoveryInfo($params){
	    $logPath = Xphp::$_config['TMP_PATH'] . "systemrec/log.txt";
	    $fileInfo = file_get_contents($logPath);
	    $fileInfo = explode(PHP_EOL, $fileInfo);
	    $info = array();
	    foreach ($fileInfo as $file){
	        if(empty($file)) continue;
	        $eachFile = explode("|", $file);
	        $info[] = array(
	            intval($eachFile[0]),
	            $eachFile[1],
	            $eachFile[2],
	        );
	    }
	    
	    return json_encode($info);
	}
	
	
	/**
	 * 恢复第一层统一调用
	 * @param unknown $nodes
	 * @param unknown $child
	 * @return boolean
	 */
	private function recoveryLayerOneCall($nodes, $child){
	    $result = true;
	    foreach ($child as $key => $value){
	        //按配置文件第二层遍历,如果选择了此项,则调用此项的恢复方法,进入恢复流程
	        if(in_array($key, $nodes) && $result){
	            $function = $value['recFunction'];
	            $callResult = call_user_func_array(array($this, $function), array($value));
	            $result = $result && $callResult;
	            if(!$result){
	                //如果恢复失败,这里不再往下恢复,直接返回false
	                return $result;
	            }
	        }
	    }
	    return $result;
	}
	
	/**
	 * 恢复过程写日志
	 * @param array $value   节点内容
	 * @param boolean $result  结果
	 */
	private function writeRecLog($value, $result, $des = ''){
	    $thisDate = date("Y-m-d H:i:s");
	    if(empty($des)){
	        $thisDes = Xphp::$_lang['UI_PLATFORM_RECOVER'] . '[' . $value['title'] . ']';
	    }else{
	        $thisDes = $des;
	    }
	    if($result){
	        $thisCode = 1;
	    }else{
	        $thisCode = 0;
	    }
	    
	    $logInfo = $thisCode . "|" . $thisDate . '|' . $thisDes . PHP_EOL;
	    file_put_contents($this->systemRecLogFile, $logInfo, FILE_APPEND);
	}
	
	/**
	 * 开始恢复备份系统
	 * 这个步骤的前提是所有备份文件已经在恢复目录准备好,解压完成
	 * @param unknown $params
	 */
	private function startRecoverySystem($nodes){
	    $bakDes = include CONF_PATH . "system_bak.php";
	    
	    
	    //定义恢复目录和恢复日志文件
	    $this->systemRecDir = Xphp::$_config['TMP_PATH'] . "systemrec";
	    $this->systemRecLogFile = $this->systemRecDir . "/log.txt";
	    
	    //清空日志文件
	    file_put_contents($this->systemRecLogFile, "");
	    
	    $this->writeRecLog(array(), true, Xphp::$_lang['UI_SBH_SYSRECOVERY_START']);
	    
	    //开始事务
	    $this->dbBeginTransaction();
	    
	    $result = true;
	    foreach ($bakDes['BakTreeConf'] as $key => $value){
	        //按配置文件第一层遍历,如果选择了此项,则调用此项的恢复方法,进入恢复流程
	        if(in_array($key, $nodes)){
	            $function = $value['recFunction'];
	            $callResult = call_user_func_array(array($this, $function), array($nodes, $value['child']));
	            $result = $result && $callResult;
	            
	            if(!$result){
	                //如果恢复失败,这里不再往下恢复,直接结束循环
	                break;
	            }
	        }
	    }
	    
	    if($result){
	        $this->dbCommit();
	        $this->recoveryEnding();
	        $this->writeRecLog(array(), true, Xphp::$_lang['UI_SBH_SYSRECOVERY_SUCCESS']);
	        $this->systemLog("SYSTEM_LOG_SYSBAKREC_RECOVERY_SYSTEM", array(), Xphp::$_config['LOGLEVEL']['NORMAL']);
	    }else{
	        $this->dbRollBack();
	        $this->writeRecLog(array(), false, Xphp::$_lang['UI_SBH_SYSRECOVERY_FAIL']);
	        $this->systemLog("SYSTEM_LOG_SYSBAKREC_RECOVERY_SYSTEM", array(), Xphp::$_config['LOGLEVEL']['ERROR']);
	    }
	    
	    return $result;
	}
	
	/**
	 * 恢复收尾工作,比如刷新虚拟化中心这些
	 */
	private function recoveryEnding(){
	    $result = true;
	    //刷新虚拟化中心
	    $result = $result && $this->refleshVcenter();
	    
	    return $result;
	}
	
	/**
	 * 刷新虚拟化中心
	 */
	private function refleshVcenter(){
	    $result = true;
	    $nodeHandler = Xphp::instance('NodeHandler');
	    $nodeuuid = $nodeHandler->getvCenterUUID();
	    $opName = 'VM_VCENTER_OP_REFLASH';
	    foreach ($this->vcenterRefleshList as $value){
	        $msg = json_encode(array('vcenter_uuid'=>$value['vcenter_uuid'], 'display_mode' => Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']));
	        $mbResult = $this->mbVMMsg($nodeuuid, $value['hypervisor_type'], $opName, $msg, false, true);
	        $result = $result && $mbResult['result'];
	    }
	    
	    if(!empty($this->vcenterRefleshList)){
	        $this->writeRecLog(0, $result, Xphp::$_lang['UI_SBH_UPDATE_VCENTER']);
	    }
	    
	    return $result;
	}
	
	/**
	 * 恢复基础设施
	 */
	private function recoveryInfrastructure($nodes, $child){
	    return $this->recoveryLayerOneCall($nodes, $child);
	}
	
	/**
	 * 恢复安全
	 */
	private function recoverySecurityInfo($nodes, $child){
	    return $this->recoveryLayerOneCall($nodes, $child);
	}
	
	/**
	 * 恢复系统配置
	 */
	private function recoverySystemInfo($nodes, $child){
	    return $this->recoveryLayerOneCall($nodes, $child);
	}
	
	/**
	 * 恢复多租户
	 */
	private function recoveryMultitenant($nodes, $child){
	    return $this->recoveryLayerOneCall($nodes, $child);
	}
	
	/**
	 * 恢复备份资源
	 */
	private function recoveryBackupResource($nodes, $child){
	    return $this->recoveryLayerOneCall($nodes, $child);
	}
	
	/**
	 * 恢复任务
	 */
	private function recoveryJob($nodes, $child){
	    return $this->recoveryLayerOneCall($nodes, $child);
	}
	
	/**
	 * 恢复告警
	 */
	private function recoveryAlarm($nodes, $child){
	    return $this->recoveryLayerOneCall($nodes, $child);
	}
	
	/**
	 * 恢复日志
	 */
	private function recoveryLog($nodes, $child){
	    return $this->recoveryLayerOneCall($nodes, $child);
	}
	
	/**
	 * 得到恢复配置文件信息
	 * @param unknown $filename    文件名
	 */
	private function getRecoveryFileInfo($filename){
	    $path = $this->systemRecDir . "/" . $filename;
	    if(!file_exists($path)){
	        return false;
	    }
	    $content = file_get_contents($path);
	    
	    return json_decode($content, true);
	}
	
	/**
	 * 根据旧的节点uuid得到新的节点uuid
	 * 这里主要是主节点的对应关系
	 * @param unknown $oldUUID
	 */
	private function getNewNodeUUID($oldUUID){
	    if(!empty($this->recNodeCompareList[$oldUUID])){
	        return $this->recNodeCompareList[$oldUUID];
	    }else{
	        return $oldUUID;
	    }
	}
	
	/**
	 * 根据旧节点uuid和节点类型得到任务的信的uuid
	 * @param unknown $oldUUID
	 * @param unknown $oldNodeType
	 */
	private function getTaskNewNodeUUID($oldUUID, $oldNodeType){
	    //如果是主节点,得到新的主节点uuid
	    $nodeHandler = Xphp::instance('NodeHandler');
	    if(empty($this->masterNodeUUID)){
	        //获取并设置主节点uuid
	        $this->masterNodeUUID = $nodeHandler->getLocalNodeUUID();
	    }
	    //如果是主节点,返回新的主节点uuid,如果是子节点,返回原来的节点uuid
	    if(intval($oldNodeType) == Xphp::$_config['NODETYPE']['MASTER']){
	        return $this->masterNodeUUID;
	    }else{
	        return $oldUUID;
	    }
	}
	
	/**
	 * 根据旧的策略ID得到新的策略ID
	 * 主要是因为策略ID按照自增长进行的
	 * @param unknown $oldID
	 */
	private function getNewStrategyID($oldID){
	    if(!empty($this->strategyIDCompareList[$oldID])){
	        return $this->strategyIDCompareList[$oldID];
	    }else{
	        return $oldID;
	    }
	}
	
	/**
	 * 恢复备份节点
	 */
	private function recoveryNodeManager($value){
	    /**
	     * 恢复逻辑
	     * 1.如果是主节点,不恢复,增加节点对应关系
	     * 2.如果是子节点,如果系统没有对应节点,恢复此节点
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有备份节点
	    $nodeuuids = array();
	    $sql = "select node_uuid from bd_node";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $nodeuuids[] = $d['node_uuid'];
	    }
	    $result = true;
	    
	    $bd_node = $content['bd_node'];
        $bd_system_cache_config = $content['bd_system_cache_config'];

	    $nodeMasterType = Xphp::$_config['NODETYPE']['MASTER'];
	    foreach ($bd_node as $c){
	        if($nodeMasterType == intval($c['node_type'])){
	            //主节点,查找主节点uuid,做对应关系
	            $sql = "select node_uuid from bd_node where node_type = ?";
	            $data = $this->dbSelect($sql, array($nodeMasterType), PDO::FETCH_ASSOC);
	            $localMasterUUID = $data[0]['node_uuid'];
	            if($localMasterUUID != $c['node_uuid']){
	                $key = $c['node_uuid'];
	                $this->recNodeCompareList[$key] = $localMasterUUID;//键名是以前的uuid,值是新的uuid
	            }
	            continue;
	        }else{
	            //子节点
	            if(!in_array($c['node_uuid'], $nodeuuids)){
	                //如果现在系统没有这个节点,可以插入到数据库
	                $sql = "insert into bd_node(auto_upgrade_flag, ip, port, user_name, password, node_uuid,
                      host_name, node_nickname, os_type, os_version, process_type, register_time, authorization_time, 
                        authorization_flag, global_share_flag, owner_user_list, node_type) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	                $sqlParams = array($c['auto_upgrade_flag'], $c['ip'], $c['port'], $c['user_name'], $c['password'], $c['node_uuid'], 
	                    $c['host_name'], $c['node_nickname'], $c['os_type'], $c['os_version'], $c['process_type'], $c['register_time'], 
	                    $c['authorization_time'], $c['authorization_flag'], $c['global_share_flag'], $c['owner_user_list'], $c['node_type']);
	                $result = $result && $this->dbExec($sql, $sqlParams);
	            }
	        }
	    }

        foreach ($bd_system_cache_config as $c){
            if(!in_array($c['node_uuid'], $nodeuuids)){
                $sql = "insert into bd_system_cache_config(node_uuid, cache_type, cache_dir_path, cache_storage_uuid, 
                            mount_point, switch_strategy,warning_flag, warning_value, last_warning_time, config_source, details ) 
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sqlParams = array($c['node_uuid'], $c['cache_type'], $c['cache_dir_path'], $c['cache_storage_uuid'],
                    $c['mount_point'], $c['switch_strategy'],
                    $c['warning_flag'], $c['warning_value'], $c['last_warning_time'], $c['config_source'], $c['details']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	/**
	 * 恢复备份存储
	 */
	private function recoveryStorageManager($value){
	    /**
	     * 恢复逻辑
	     * 如果有相同UUID的存储,不插入,如果没有才插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有存储
	    $uuids = array();
	    $sql = "select storage_uuid from bd_storage_resource where lan_free_flag = ?";
	    $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['UNSET']), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['storage_uuid'];
	    }
	    $result = true;
	    
	    $bd_storage_resource = $content['bd_storage_resource'];
	    foreach ($bd_storage_resource as $c){
	        if(!in_array($c['storage_uuid'], $uuids)){
	            //如果现在系统没有这个存储,可以插入到数据库
                $sql = "insert into bd_storage_resource(storage_nickname, storage_uuid, storage_type, node_uuid, total_size, 
                            free_size,status, mount_flag, mount_point, global_flag, user_uuid_list, backend_sr_list, error_code,
                            storage_config, warning_flag, warning_type, warning_value, last_warning_time, lan_free_flag, 
                            use_mode, share_access_flag,user_uuid) 
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sqlParams = array($c['storage_nickname'], $c['storage_uuid'], $c['storage_type'], $this->getNewNodeUUID($c['node_uuid']), $c['total_size'], $c['free_size'],
                    $c['status'], $c['mount_flag'], $c['mount_point'], $c['global_flag'], $c['user_uuid_list'], $c['backend_sr_list'], $c['error_code'],
                    $c['storage_config'], $c['warning_flag'], $c['warning_type'], $c['warning_value'], $c['last_warning_time'], $c['lan_free_flag'], 
                    $c['use_mode'], $c['share_access_flag'],$c['user_uuid']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    $this->writeRecLog($value, $result);
	    return $result;
	}
	
	/**
	 * 恢复LAN-Free配置
	 */
	private function recoveryStorageLanFree($value){
	    /**
	     * 恢复逻辑
	     * 如果有相同UUID的存储,不插入,如果没有才插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有LAN-Free配置
	    $uuids = array();
	    $sql = "select storage_uuid from bd_storage_resource where lan_free_flag = ?";
	    $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['storage_uuid'];
	    }
	    $result = true;
	    
	    $bd_storage_resource = $content['bd_storage_resource'];
	    foreach ($bd_storage_resource as $c){
	        if(!in_array($c['storage_uuid'], $uuids)){
	            //如果现在系统没有这个LAN-Free配置,可以插入到数据库
	            $sql = "insert into bd_storage_resource(storage_nickname, storage_uuid, storage_type, node_uuid, total_size, free_size,
                  status, mount_flag, mount_point, global_flag, user_uuid_list, backend_sr_list, error_code,
                    storage_config, warning_flag, warning_type, warning_value, last_warning_time, lan_free_flag,
                    use_mode, share_access_flag) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['storage_nickname'], $c['storage_uuid'], $c['storage_type'], $this->getNewNodeUUID($c['node_uuid']), $c['total_size'], $c['free_size'],
	                $c['status'], $c['mount_flag'], $c['mount_point'], $c['global_flag'], $c['user_uuid_list'], $c['backend_sr_list'], $c['error_code'],
	                $c['storage_config'], $c['warning_flag'], $c['warning_type'], $c['warning_value'], $c['last_warning_time'], $c['lan_free_flag'],
	                $c['use_mode'], $c['share_access_flag']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	/**
	 * 恢复策略组
	 */
	private function recoveryGlobalStrategy($value){
	    /**
	     * 恢复逻辑
	     * 如果有相同UUID,不插入,如果没有才插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有用户
	    $uuids = array();
	    $sql = "select strategy_group_uuid from bd_strategy_group";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['strategy_group_uuid'];
	    }
	    $result = true;
	    
	    $bd_strategy_group = $content['bd_strategy_group'];
	    foreach ($bd_strategy_group as $c){
	        if(!in_array($c['strategy_group_uuid'], $uuids)){
	            //如果现在系统没有这个策略组,可以插入到数据库
	            
	            //插入bd_strategy_group表
	            $sql = "insert into bd_strategy_group(strategy_group_uuid, strategy_group_name, strategy_group_type, remark, create_time, user_uuid,
                  extra_info) values(?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['strategy_group_uuid'], $c['strategy_group_name'], $c['strategy_group_type'], $c['remark'], $c['create_time'], $c['user_uuid'],
	                $c['extra_info']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    $this->writeRecLog($value, $result);
	    return $result;
	}

    /**
     * 恢复全局限速策略
     * @param $value
     * @return void
     */
	private function recoveryGlobalSpeedLimitStrategy($value){
        $content = $this->getRecoveryFileInfo($value['name']);
        //获取所有现有用户
        $uuids = array();
        $sql = "select strategy_group_uuid from bd_strategy_group";
        $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        foreach ($data as $d){
            $uuids[] = $d['strategy_group_uuid'];
        }
        $result = true;
        $bd_speed_limit_strategy = $content['bd_global_speed_limit_strategy'];
        foreach ($bd_speed_limit_strategy as $c){
            if(!in_array($c['strategy_group_uuid'], $uuids)){
                //插入bd_speed_limit_strategy表
                $sql = "insert into bd_global_speed_limit_strategy(strategy_uuid, strategy_name, strategy_group_uuid, strategy_type, 
                                    start_time,end_time, days,speed_limited_value,remark,extra_info,is_global,user_uuid,create_time) 
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sqlParams = array($c['strategy_uuid'], $c['strategy_name'], $c['strategy_group_uuid'], $c['strategy_type'],
                    $c['start_time'], $c['end_time'],$c['days'],$c['speed_limited_value'],$c['remark'],$c['extra_info'],
                    $c['is_global'],$c['user_uuid'],$c['create_time']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }
        $this->writeRecLog($value, $result);
        return $result;
    }

	/**
	 * 恢复资源组
	 */
	private function recoveryResourceGroup($value){
	    /**
	     * 恢复逻辑
	     * bd_resource_group如果有相同UUID,不插入,如果没有才插入
	     * mt_resource_resource_group没有才插入
	     * mt_user_group_resource_group没有才插入
	     * mt_user_resource_group没有才插入
	     */
	    
		 $content = $this->getRecoveryFileInfo($value['name']);
		 //获取所有现有用户
		 $uuids = array();
		 $sql = "select resource_group_uuid from bd_resource_group";
		 $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
		 foreach ($data as $d){
			 $uuids[] = $d['resource_group_uuid'];
		 }
		 $result = true;
		 
		 $bd_resource_group = $content['bd_resource_group'];
		 $mt_resource_resource_group = $content['mt_resource_resource_group'];
		 $mt_user_group_resource_group = $content['mt_user_group_resource_group'];
		 $mt_user_resource_group = $content['mt_user_resource_group'];
		 $mt_user_resource = $content['mt_user_resource'];
		 $mt_user_group_resource = $content['mt_user_group_resource'];
		 foreach ($bd_resource_group as $c){
			 if(!in_array($c['resource_group_uuid'], $uuids)){
				 //如果现在系统没有这个资源组,可以插入到数据库
				 
				 //插入bd_resource_group表
				 $sql = "insert into bd_resource_group(resource_group_uuid, resource_group_name, config, description, tenant_uuid, create_user_uuid,
				   create_user_name, create_time) values(?, ?, ?, ?, ?, ?, ?, ?)";
				 $sqlParams = array($c['resource_group_uuid'], $c['resource_group_name'], $c['config'], $c['description'], $c['tenant_uuid'], $c['create_user_uuid'],
					 $c['create_user_name'], $c['create_time']
				 );
				 $result = $result && $this->dbExec($sql, $sqlParams);
				 
			 }
		 }
		 
		 //插入mt_resource_resource_group表
		 foreach ($mt_resource_resource_group as $c){
			 $sql = "insert into mt_resource_resource_group(resource_uuid, resource_group_uuid, vm_uuid, vcenter_uuid, resource_type) 
					 select ?, ?, ?, ?, ? from dual where not exists (select id from mt_resource_resource_group where resource_uuid = ? and resource_group_uuid = ?)";
			 $sqlParams = array(
				 $c['resource_uuid'], $c['resource_group_uuid'], $c['vm_uuid'], $c['vcenter_uuid'], $c['resource_type'],
				 $c['resource_uuid'], $c['resource_group_uuid']
			 );
			 $result = $result && $this->dbExec($sql, $sqlParams);
		 }
		 
		 //插入mt_user_group_resource_group表
		 foreach ($mt_user_group_resource_group as $c){
			 $sql = "insert into mt_user_group_resource_group(user_group_uuid, resource_group_uuid) 
					 select ?, ? from dual where not exists (select id from mt_user_group_resource_group where user_group_uuid = ? and resource_group_uuid = ?)";
			 $sqlParams = array(
				 $c['user_group_uuid'], $c['resource_group_uuid'],
				 $c['user_group_uuid'], $c['resource_group_uuid'],
			 );
			 $result = $result && $this->dbExec($sql, $sqlParams);
		 }
		 
		 //插入mt_user_resource_group表
		 foreach ($mt_user_resource_group as $c){
			 $sql = "insert into mt_user_resource_group(user_uuid, resource_group_uuid) 
					 select ?, ? from dual where not exists (select id from mt_user_resource_group where user_uuid = ? and resource_group_uuid = ?)";
			 $sqlParams = array(
				 $c['user_uuid'], $c['resource_group_uuid'],
				 $c['user_uuid'], $c['resource_group_uuid'],
			 );
			 $result = $result && $this->dbExec($sql, $sqlParams);
		 }
		 //插入mt_user_resource表
		 foreach ($mt_user_resource as $c){
			 $sql = "insert into mt_user_resource(user_uuid, resource_uuid,vm_uuid,vcenter_uuid,resource_type) 
					 select ?, ?, ?, ?, ?  from dual where not exists (select user_uuid from mt_user_resource where user_uuid = ? and resource_uuid = ?)";
			 $sqlParams = array(
				$c['user_uuid'],
				$c['resource_uuid'],
				$c['vm_uuid'], 
				$c['vcenter_uuid'],
				$c['resource_type'],
				$c['user_uuid'],
				$c['resource_uuid'],
			 );
			 $result = $result && $this->dbExec($sql, $sqlParams);
		 }
 
		 //插入mt_user_group_resource表
		 foreach ($mt_user_group_resource as $c){
			 $sql = "insert into mt_user_group_resource(user_group_uuid, resource_uuid,vm_uuid,vcenter_uuid,resource_type) 
					 select ?, ?, ?, ?, ?  from dual where not exists (select resource_uuid from mt_user_group_resource where resource_uuid = ? )";
			 $sqlParams = array(
				$c['user_group_uuid'],
				$c['resource_uuid'],
				$c['vm_uuid'], 
				$c['vcenter_uuid'],
				$c['resource_type'], 
				$c['resource_uuid'],
			 );
			 $result = $result && $this->dbExec($sql, $sqlParams);
		 }
		 $this->writeRecLog($value, $result);
		 
		 return $result;
	    
	}
	
	
	/**
	 * 恢复用户
	 */
	private function recoverySafetyUser($value){
	    /**
	     * 恢复逻辑
	     * 如果有相同UUID,不插入,如果没有才插入
	     */
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有用户
	    $uuids = array();
	    $sql = "select user_uuid from bd_user";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['user_uuid'];
	    }
	    $result = true;
	    $bd_user = $content['bd_user'];
	    $bd_user_extension = $content['bd_user_extension'];
        $bd_user_resource_transfer = $content['bd_user_resource_transfer'];
        $bd_account_safe = $content['bd_account_safe'];
	    foreach ($bd_user as $key => $c){
	        if(!in_array($c['user_uuid'], $uuids)){
	            //如果现在系统没有这个用户,可以插入到数据库
	            
	            //插入bd_user表
	            $sql = "insert into bd_user(user_uuid, user_name, nickname, password, email, telephone,
                  user_type, permission, create_time, create_user_name, create_user_uuid, last_login_time, lock_flag,
                    login_error_count, max_login_error_count, language, description, page_layout, last_login_ip,
                    domain_uuid,force_password_change_flag,manager_uuid,manager_auth,user_level,user_auth) 
                    values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?)";
	            $sqlParams = array($c['user_uuid'], $c['user_name'], $c['nickname'], $c['password'], $c['email'], $c['telephone'],
	                $c['user_type'], $c['permission'], $c['create_time'], $c['create_user_name'], $c['create_user_uuid'], $c['last_login_time'], $c['lock_flag'],
	                $c['login_error_count'], $c['max_login_error_count'], $c['language'], $c['description'], $c['page_layout'], $c['last_login_ip'],
	                $c['domain_uuid'],$c['force_password_change_flag'],$c['manager_uuid'],$c['manager_auth'],$c['user_level'],$c['user_auth']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	            
	            //插入bd_user_extension表
	            $sql = "insert into bd_user_extension(user_uuid, vmware_data, xs_data, xen_data, kvm_data, hyperv_data,
                  fs_data, db_data, task_success, task_failure, quota,os_data,copy_data,archive_data,vol_cdp_data,nas_data,m365_data,obs_data,hadoop_data) 
                    values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

	            $sqlParams = array($bd_user_extension[$key]['user_uuid'],$bd_user_extension[$key]['vmware_data'],$bd_user_extension[$key]['xs_data'],
	                $bd_user_extension[$key]['xen_data'],$bd_user_extension[$key]['kvm_data'],$bd_user_extension[$key]['hyperv_data'],
	                $bd_user_extension[$key]['fs_data'],$bd_user_extension[$key]['db_data'],$bd_user_extension[$key]['task_success'],
	                $bd_user_extension[$key]['task_failure'],$bd_user_extension[$key]['quota'],$bd_user_extension[$key]['os_data'],
                    $bd_user_extension[$key]['copy_data'],$bd_user_extension[$key]['archive_data'],$bd_user_extension[$key]['vol_cdp_data'],
                    $bd_user_extension[$key]['nas_data'],$bd_user_extension[$key]['m365_data'],$bd_user_extension[$key]['obs_data'],$bd_user_extension[$key]['hadoop_data']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
            }
	    }
        //插入bd_account_safe表
        foreach ($bd_account_safe as $c) {
            $sql = "insert into bd_account_safe(login_timeout,login_failure,pass_timeout,pass_length,pass_complexity,
                            login_failed_lock_time,create_user_level) 
                        values (?,?,?,?,?,?,?)";
            $sqlParams = array($c['login_timeout'], $c['login_failure'], $c['pass_timeout'],$c['pass_length'],
                $c['pass_complexity'], $c['login_failed_lock_time'],$c['create_user_level']);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

        //插入bd_user_resource_transfer表
        foreach ($bd_user_resource_transfer as $c){
            $sql = "insert into bd_user_resource_transfer(source_user_uuid,source_user_resource,target_user_uuid,status) 
                        values (?,?,?,?)";
            $sqlParams = array($c['source_user_uuid'],$c['source_user_resource'],$c['target_user_uuid'],$c['status']);
            $result = $result && $this->dbExec($sql, $sqlParams);
            $this->writeRecLog($value, $result);
        }

	    
	    return $result;
	}
	
	/**
	 * 恢复用户组
	 */
	private function recoverySafetyUserGroup($value){
	    /**
	     * 恢复逻辑
	     * bd_user_group表如果有相同UUID,不插入,如果没有才插入
	     * mt_user_user_group表直接插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有用户
	    $uuids = array();
	    $sql = "select user_group_uuid from bd_user_group";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['user_group_uuid'];
	    }
	    $result = true;
	    $bd_user_group = $content['bd_user_group'];
	    $mt_user_user_group = $content['mt_user_user_group'];
	    //插入bd_user_group表
	    foreach ($bd_user_group as $c){
	        if(!in_array($c['user_group_uuid'], $uuids)){
	            //如果现在系统没有这个用户组,可以插入到数据库
	            $sql = "insert into bd_user_group(user_group_uuid, user_group_name, user_group_type, lock_flag, description, config,
                  create_user_uuid, create_user_name, create_time) values(?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['user_group_uuid'], $c['user_group_name'], $c['user_group_type'], $c['lock_flag'], $c['description'], $c['config'],
	                $c['create_user_uuid'], $c['create_user_name'], $c['create_time']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	            
	        }
	    }
	    
	    //插入mt_user_user_group表
	    foreach ($mt_user_user_group as $c){
	        $sql = "insert into mt_user_user_group(user_uuid, user_group_uuid) 
					select ?, ? from dual where not exists (select id from mt_user_user_group where user_uuid = ? and user_group_uuid = ?)";
	        $sqlParams = array(
	            $c['user_uuid'], $c['user_group_uuid'],
	            $c['user_uuid'], $c['user_group_uuid'],
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	        
	    }
	    $this->writeRecLog($value, $result);
	    
	    
	    return $result;
	}
	
	/**
	 * 恢复角色
	 */
	private function recoverySafetyRole($value){
	    /**
	     * 恢复逻辑
	     * bd_role表如果有相同UUID,不插入,如果没有才插入
	     * mt_user_role表没有才插入
	     * mt_user_group_role表没有才插入
	     * bd_permission表如果有相同UUID,不插入,如果没有才插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有角色
	    $roleuuids = array();
	    $sql = "select role_uuid from bd_role";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $roleuuids[] = $d['role_uuid'];
	    }
	    //获取所有现有权限
	    $permissionuuids = array();
	    $sql = "select permission_uuid from bd_permission";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $permissionuuids[] = $d['permission_uuid'];
	    }
	    $result = true;
	    
	    $bd_role = $content['bd_role'];
	    $mt_user_role = $content['mt_user_role'];
	    $mt_user_group_role = $content['mt_user_group_role'];
	    $bd_permission = $content['bd_permission'];
	    
	    //插入bd_role表
	    foreach ($bd_role as $c){
	        if(!in_array($c['role_uuid'], $roleuuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_role(role_uuid, role_name, permission_uuid, config, lock_flag, tenant_uuid,
                  create_user_uuid, create_user_name, create_time) values(?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['role_uuid'], $c['role_name'], $c['permission_uuid'], $c['config'], $c['lock_flag'], $c['tenant_uuid'],
	                $c['create_user_uuid'], $c['create_user_name'], $c['create_time']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    //插入mt_user_role表
	    foreach ($mt_user_role as $c){
	        $sql = "insert into mt_user_role(user_uuid, role_uuid) 
					select ?, ? from dual where not exists (select id from mt_user_role where user_uuid = ? and role_uuid = ?)";
	        $sqlParams = array(
	            $c['user_uuid'], $c['role_uuid'],
	            $c['user_uuid'], $c['role_uuid'],
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入mt_user_group_role表
	    foreach ($mt_user_group_role as $c){
	        $sql = "insert into mt_user_group_role(user_group_uuid, role_uuid) 
					select ?, ? from dual where not exists (select id from mt_user_group_role where user_group_uuid = ? and role_uuid = ?)";
	        $sqlParams = array(
	            $c['user_group_uuid'], $c['role_uuid'],
	            $c['user_group_uuid'], $c['role_uuid'],
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入bd_permission表
	    foreach ($bd_permission as $c){
	        if(!in_array($c['permission_uuid'], $permissionuuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_permission(permission_uuid, name, type, content) values(?, ?, ?, ?)";
	            $sqlParams = array($c['permission_uuid'], $c['name'], $c['type'], $c['content']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    $this->writeRecLog($value, $result);
	    return $result;
	}
	
	/**
	 * 恢复域
	 */
	private function recoverySafetyDomain($value){
	    /**
	     * 恢复逻辑
	     * bd_domain_server表如果有相同domain_name,不插入,如果没有才插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有域服务器
	    $uuids = array();
	    $sql = "select domain_name from bd_domain_server";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['domain_name'];
	    }
	    $result = true;
	    
	    $bd_domain_server = $content['bd_domain_server'];
	    
	    //插入bd_domain_server表
	    foreach ($bd_domain_server as $c){
	        if(!in_array($c['domain_name'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_domain_server(domain_uuid, domain_name, domain_type, port, domain_ip, username,
                  password, register_time, tenant_uuid, create_user_uuid, create_user_name) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['domain_uuid'], $c['domain_name'], $c['domain_type'], $c['port'], $c['domain_ip'], $c['username'],
	                $c['password'], $c['register_time'], $c['tenant_uuid'], $c['create_user_uuid'], $c['create_user_name']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	
	/**
	 * 恢复域名解析
	 */
	private function recoverySystemDns($value){
	    return true;
	}
	
	/**
	 * 恢复网卡聚合
	 */
	private function recoveryNicTeaming($value){
	    return true;
	}
	
	/**
	 * 恢复账户安全
	 */
	private function recoveryAccountSafe($value){
	    return true;
	}
	
	/**
	 * 恢复存储安全
	 */
	private function recoveryStorageSage($value){
	    return true;
	}
	
	/**
	 * 恢复可视化配置
	 */
	private function recoveryVisualConfig($value){
	    return true;
	}
	
	
	/**
	 * 恢复租户
	 */
	private function recoveryTenantManager($value){
	    /**
	     * 恢复逻辑
	     * bd_tenant表如果有相同tenant_uuid,不插入,如果没有才插入
	     * mt_resource_group_tenant没有才插入
	     * mt_user_group_tenant没有才插入
	     * mt_user_tenant没有才插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有域服务器
	    $uuids = array();
	    $sql = "select tenant_uuid from bd_tenant";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['tenant_uuid'];
	    }
	    $result = true;
	    
	    $bd_tenant = $content['bd_tenant'];
	    $mt_resource_group_tenant = $content['mt_resource_group_tenant'];
	    $mt_user_group_tenant = $content['mt_user_group_tenant'];
	    $mt_user_tenant = $content['mt_user_tenant'];
	    
	    //插入bd_tenant表
	    foreach ($bd_tenant as $c){
	        if(!in_array($c['tenant_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_tenant(tenant_uuid, tenant_name, nick_name, create_time, admin_uuid, lock_flag,
                  config) values(?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['tenant_uuid'], $c['tenant_name'], $c['nick_name'], $c['create_time'], $c['admin_uuid'], $c['lock_flag'],
	                $c['config']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    //插入mt_resource_group_tenant表
	    foreach ($mt_resource_group_tenant as $c){
	        $sql = "insert into mt_resource_group_tenant(resource_group_uuid, tenant_uuid) 
					select ?, ? from dual where not exists (select id from mt_resource_group_tenant where resource_group_uuid = ? and tenant_uuid = ?)";
	        $sqlParams = array(
	            $c['resource_group_uuid'], $c['tenant_uuid'],
	            $c['resource_group_uuid'], $c['tenant_uuid'],
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入mt_user_group_tenant表
	    foreach ($mt_user_group_tenant as $c){
	        $sql = "insert into mt_user_group_tenant(user_group_uuid, tenant_uuid) 
                    select ?, ? from dual where not exists (select id from mt_user_group_tenant where user_group_uuid = ? and tenant_uuid = ?)";
	        $sqlParams = array(
	            $c['user_group_uuid'], $c['tenant_uuid'],
	            $c['user_group_uuid'], $c['tenant_uuid'],
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入mt_user_tenant表
	    foreach ($mt_user_tenant as $c){
	        $sql = "insert into mt_user_tenant(user_uuid, tenant_uuid) 
					select ?, ? from dual where not exists (select id from mt_user_tenant where user_uuid = ? and tenant_uuid = ?)";
	        $sqlParams = array(
	            $c['user_uuid'], $c['tenant_uuid'],
	            $c['user_uuid'], $c['tenant_uuid'],
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	    
	}
	
	/**
	 * 恢复费用
	 */
	private function recoveryBillingManager($value){
	    /**
	     * 恢复逻辑
	     * bd_billing表如果有相同billing_uuid,不插入,如果没有才插入
	     * bd_billing_details没有才插入
	     * mt_tenant_billing没有才插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有域服务器
	    $uuids = array();
	    $sql = "select billing_uuid from bd_billing";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['billing_uuid'];
	    }
	    $result = true;
	    
	    $bd_billing = $content['bd_billing'];
	    $bd_billing_details = $content['bd_billing_details'];
	    $mt_tenant_billing = $content['mt_tenant_billing'];
	    
	    //插入bd_billing表
	    foreach ($bd_billing as $c){
	        if(!in_array($c['billing_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_billing(billing_uuid, name, description, monetary_unit, billing_type, billing_mode,
                  num_type, unit_cost, inform, inform_time, inform_period, config, lock_flag, create_time) values(?, ?, ?, ?, ?, ?,
                     ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['billing_uuid'], $c['name'], $c['description'], $c['monetary_unit'], $c['billing_type'], $c['billing_mode'],
	                $c['num_type'], $c['unit_cost'], $c['inform'], $c['inform_time'], $c['inform_period'], $c['config'], $c['lock_flag'], $c['create_time'] 
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    //插入bd_billing_details表
	    foreach ($bd_billing_details as $c){
	        //有对应字段不插入,没有才插入
	        $sql = "insert into bd_billing_details(tenant_uuid, billing_uuid, billing_type, create_time, end_time, update_time, 
                    use_num, capacity_size, billing_total) 
                    select ?, ?, ?, ?, ?, ?, ?, ?, ? from dual where not exists 
                    (select id from bd_billing_details where tenant_uuid = ? and billing_uuid = ? and create_time = ?)";
	        $sqlParams = array(
	            $c['tenant_uuid'], $c['billing_uuid'], $c['billing_type'], $c['create_time'], $c['end_time'], $c['update_time'], 
	            $c['use_num'], $c['capacity_size'], $c['billing_total'],
	            $c['tenant_uuid'], $c['billing_uuid'], $c['create_time']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入mt_tenant_billing表
	    foreach ($mt_tenant_billing as $c){
	        //有对应字段不插入,没有才插入
	        $sql = "insert into mt_tenant_billing(tenant_uuid, billing_uuid, create_time) 
                    select ?, ?, ? from dual where not exists (select id from mt_tenant_billing where tenant_uuid = ? and billing_uuid = ?) ";
	        $sqlParams = array(
	            $c['tenant_uuid'], $c['billing_uuid'], $c['create_time'], $c['tenant_uuid'], $c['billing_uuid']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	/**
	 * 恢复虚拟化中心
	 */
	private function recoveryVcenterManager($value){
	    /**
	     * 恢复逻辑
	     * vm_vcenter表如果有相同vcenter_uuid,不插入,如果没有才插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
        $vm_vcenter = $content['vm_vcenter'];
        $mt_platform_node = $content['mt_platform_node'];
	    //获取所有现有域服务器
	    $uuids = array();
	    $sql = "select vcenter_uuid from vm_vcenter";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['vcenter_uuid'];
	    }
	    $result = true;

	    //插入vm_vcenter表
	    foreach ($vm_vcenter as $c){
	        if(!in_array($c['vcenter_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into vm_vcenter(vcenter_ip, vcenter_uuid, vcenter_name, username, nickname, password,
                  hypervisor_type, vcenter_flag, online_flag, register_time, refresh_time, user_uuid, version, detail) values(?, ?, ?, ?, ?, ?,
                     ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['vcenter_ip'], $c['vcenter_uuid'], $c['vcenter_name'], $c['username'], $c['nickname'], $c['password'],
	                $c['hypervisor_type'], $c['vcenter_flag'], $c['online_flag'], $c['register_time'], $c['refresh_time'], $c['user_uuid'], $c['version'], $c['detail']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	            if($result){
	                //将本次新增的是vcenter加入到刷新列表,所有数据库插入完成后再刷新,如果数据库没有记录刷新会失败,所以不在这里刷新
	                $this->vcenterRefleshList[] = array(
	                    'vcenter_uuid' => $c['vcenter_uuid'],
	                    'hypervisor_type' => $c['hypervisor_type'],
	                );
	            }
	        }
	        //经商讨决定，恢复后的vm_host全部赋值为未授权
	        $updateVmHost = "update vm_host set authorization_flag = ? where vcenter_uuid =?";
	        $updateResult = $result && $this->dbExec($updateVmHost, array(Xphp::$_config['FLAG']['UNSET'],$c['vcenter_uuid']));
	    }

        //插入mt_platform_node表
        foreach ($mt_platform_node as $c){
            //有对应字段不插入,没有才插入
            $sql = "insert into mt_platform_node(platform_uuid, node_uuid, type,detail) 
                    select ?, ?, ?, ?  from dual where not exists (select platform_uuid from mt_platform_node where platform_uuid = ?) ";
            $sqlParams = array(
                $c['platform_uuid'], $c['node_uuid'], $c['type'], $c['detail'], $c['platform_uuid']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	/**
	 * 恢复数据库代理
	 */
	private function recoveryAgentManager($value){
	    /**
	     * 恢复逻辑
	     * bd_agent表如果有相同agent_uuid,不插入,如果没有才插入
	     */
	    $content = $this->getRecoveryFileInfo($value['name']);
	    $bd_agent = $content['bd_agent'];
	    $bd_agent_disk = $content['bd_agent_disk'];
	    $bd_agent_vol = $content['bd_agent_vol'];
	    $bd_agent_app = $content['bd_agent_app'];
	    $bd_agent_group = $content['bd_agent_group'];

        $cdp_db_host = $content['cdp_db_host'];
        //获取所有现有cdp代理
        $uuids = array();
        $sql = "select host_uuid from cdp_db_host";
        $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
        foreach ($data as $d){
            $uuids[] = $d['host_uuid'];
        }
        $result = true;

	    //获取所有现有客户端
	    $agentUuids = array();
	    $sql = "select agent_uuid from bd_agent";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $agentUuids[] = $d['agent_uuid'];
	    }
	    
	    $bdAgentDiskUuids = array();
	    $diskSql = "select disk_uuid from bd_agent_disk";
	    $diskData = $this->dbSelect($diskSql, array(), PDO::FETCH_ASSOC);
	    foreach ($diskData as $d){
	        $bdAgentDiskUuids[] = $d['disk_uuid'];
	    }
	    
	    $bdAgentVolUuids = array();
	    $sql = "select vol_uuid from bd_agent_vol";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $bdAgentVolUuids[] = $d['vol_uuid'];
	    }
	    
	    $bdAgentAppUuids = array();
	    $sql = "select app_uuid from bd_agent_app";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $bdAgentAppUuids[] = $d['app_uuid'];
	    }
	    
	    $bdAgentGroupUuids = array();
	    $sql = "select group_uuid from bd_agent_group";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $bdAgentGroupUuids[] = $d['group_uuid'];
	    }
	    
	    $result = true;
        foreach ($cdp_db_host as $c){
            if(!in_array($c['host_uuid'], $uuids)){
                //如果现在系统没有这个,可以插入到数据库
                $sql = "insert into cdp_db_host(host_uuid, host_name, ip, os_version, host_type, status,
                  register_time, user_uuid, detail) values(?, ?, ?, ?, ?, ?,
                     ?, ?, ?)";
                $sqlParams = array($c['host_uuid'], $c['host_name'], $c['ip'], $c['os_version'], $c['host_type'], $c['status'],
                    $c['register_time'], $c['user_uuid'], $c['detail']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }

	    //插入bd_agent表
	    foreach ($bd_agent as $c){
	        if(!in_array($c['agent_uuid'], $agentUuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_agent(agent_uuid, agent_name, hostname, ip, os_type, os_version,
                  process_type, register_time, register_flag, online_flag, user_uuid, authorization_module, 
                  port, agent_type, transport_port, client_transport_port, detail,net_model,storage_topology,
                  agent_os_type,group_uuid,server_username,server_password,agent_username,agent_password,
                  plugin_deploy_status,error_code,plugin_version,node_uuid,agent_package_type,domain_config) 
                  values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
	            $sqlParams = array($c['agent_uuid'], $c['agent_name'], $c['hostname'], $c['ip'], $c['os_type'], $c['os_version'],
	                $c['process_type'], $c['register_time'], $c['register_flag'], $c['online_flag'], $c['user_uuid'], $c['authorization_module'], 
	                $c['port'], $c['agent_type'], $c['transport_port'], $c['client_transport_port'], $c['detail'],$c['net_model'],$c['storage_topology'],
	                $c['agent_os_type'],$c['group_uuid'],$c['server_username'],$c['server_password'],$c['agent_username'],$c['agent_password'],
	                $c['plugin_deploy_status'],$c['error_code'],$c['plugin_version'],$c['node_uuid'],$c['agent_package_type'],$c['domain_config']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    //插入bd_agent_disk表
	    foreach ($bd_agent_disk as $v){
	        if(!in_array($v['disk_uuid'], $bdAgentDiskUuids)){
    	        $sql = "insert into bd_agent_disk(agent_uuid,disk_name,disk_uuid,partition_table_type,capacity,
                          free_space,display_name,detail) 
                        values (?, ?, ?, ?, ?, ?, ?, ?)";
    	        $sqlParams = array($v['agent_uuid'],$v['disk_name'],$v['disk_uuid'],$v['partition_table_type'],
                    $v['capacity'],$v['free_space'],$v['display_name'],$v['detail']);
    	        $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    //插入bd_agent_vol
	    foreach ($bd_agent_vol as $v){
	        if(!in_array($v['vol_uuid'], $bdAgentVolUuids)){
    	        $sql = "insert into bd_agent_vol(disk_uuid,vol_name,vol_uuid,capacity,free_space,start_offset,mount_point,display_name,is_boot,detail)
                        values (?,?,?,?,?,?,?,?,?,?)";
    	        $sqlParams = array($v['disk_uuid'],$v['vol_name'],$v['vol_uuid'],$v['capacity'],$v['free_space'],$v['start_offset'],$v['mount_point'],
    	            $v['display_name'],$v['is_boot'],$v['detail']);
    	        $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    //插入bd_agent_app
	    foreach ($bd_agent_app as $v){
	        if(!in_array($v['app_uuid'], $bdAgentAppUuids)){
    	        $sql = "insert into bd_agent_app(agent_uuid,app_uuid,alias_name,app_type,app_name,app_auth_type,app_username,
                         app_password,app_listen_port,app_listen_ip,dir_path,install_app_username,update_time,online_flag,
                         cluster_flag,cluster_uuid,install_vol_uuid,app_detail,app_version,cluster_name,cluster_service_ip)
                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    	        $sqlParams = array($v['agent_uuid'],$v['app_uuid'],$v['alias_name'],$v['app_type'],$v['app_name'],$v['app_auth_type'],
                    $v['app_username'],$v['app_password'],$v['app_listen_port'],$v['app_listen_ip'],$v['dir_path'],$v['install_app_username'],
                    $v['update_time'],$v['online_flag'],$v['cluster_flag'],$v['cluster_uuid'],$v['install_vol_uuid'],$v['app_detail'],
                    $v['app_version'],$v['cluster_name'],$v['cluster_service_ip']
    	        );
    	        $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    //插入bd_agent_copy
	    foreach ($bd_agent_group as $v){
	        if(!in_array($v['group_uuid'], $bdAgentGroupUuids)){
    	        $sql = "insert into bd_agent_group (group_uuid,group_name,create_time,user_uuid,group_type,remark,detail) values (?,?,?,?,?,?,?)";
    	        $sqlParams = array($v['group_uuid'],$v['group_name'],$v['create_time'],$v['user_uuid'],$v['group_type'],$v['remark'],$v['detail']);
    	        $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	/**
	 * 恢复虚拟实验室
	 * @param unknown $value
	 */
	private function recoverySrvirtualManager($value){
	    $content = $this->getRecoveryFileInfo($value['name']);
	    $result = true;
	    
	    $sr_network_map_list = $content['sr_network_map_list'];
	    $sr_virtual_lab = $content['sr_virtual_lab'];
	    foreach ($sr_network_map_list as $v){
	        $sql = "insert into sr_network_map_list(product_network_name, product_network_segment, product_network_netmask, 
                        product_network_gateway, product_dns_server, isolated_network_name,isolated_network_segment, 
                        isolated_network_netmask, isolated_network_gateway, isolated_dns_server, virtual_lab_uuid,
                        virtual_switch_name,network_map_id,product_network_uuid) 
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?  
                    from dual where not exists 
                    (select network_map_id  from sr_network_map_list where virtual_lab_uuid = ? )";
	        $sqlParams = array($v['product_network_name'],$v['product_network_segment'],$v['product_network_netmask'],
                $v['product_network_gateway'],$v['product_dns_server'],$v['isolated_network_name'],$v['isolated_network_segment'],
                $v['isolated_network_netmask'],$v['isolated_network_gateway'],$v['isolated_dns_server'],$v['virtual_lab_uuid'],
                $v['virtual_switch_name'],$v['network_map_id'],$v['product_network_uuid'],$v['virtual_lab_uuid']);
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    foreach ($sr_virtual_lab as $v){
	        $sql = "insert into sr_virtual_lab(host_uuid, vcenter_uuid, storage_uuid, virtual_lab_uuid,virtual_lab_name, 
                        proxy_uuid,proxy_name, proxy_ip, proxy_netmask, proxy_gateway, proxy_network_name,network_map,
                        hypervisor_type, proxy_status,error_code,user_uuid,isolated_vswitch_name,resource_pool_name,
                        folder_name,create_time,task_progress,resource_pool_uuid,folder_uuid,proxy_network_uuid) 
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    from dual where not exists (select virtual_lab_id from sr_virtual_lab where virtual_lab_uuid = ? )";
	        
	        $sqlParams = array($v['host_uuid'],$v['vcenter_uuid'],$v['storage_uuid'],$v['virtual_lab_uuid'],$v['virtual_lab_name'],
                $v['proxy_uuid'],$v['proxy_name'],$v['proxy_ip'],$v['proxy_netmask'],$v['proxy_gateway'],$v['proxy_network_name'],
                $v['network_map'],$v['hypervisor_type'],$v['proxy_status'],$v['error_code'],$v['user_uuid'],$v['isolated_vswitch_name'],
                $v['resource_pool_name'],$v['folder_name'],$v['create_time'],$v['task_progress'],$v['resource_pool_uuid'],
                $v['folder_uuid'],$v['proxy_network_uuid'],$v['virtual_lab_uuid']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    $this->writeRecLog($value, $result);
	    return $result;
	}
	
	/**
	 * 恢复nas设备信息
	 * @param unknown $value
	 */
	private function recoveryNasDeviceManager($value){
	    $content = $this->getRecoveryFileInfo($value['name']);
	    $result = true;
	    
	    $nas_mount_list = $content['nas_mount_list'];
	    $nas_storage_resource = $content['nas_storage_resource'];
	    
	    //插入nas_storage_resource表
	    foreach ($nas_storage_resource as $c){
	        $sql =  "insert into nas_storage_resource(nas_name,nas_nickname,nas_uuid,share_path,nas_create_time,ip,user_name,
                        password,authorization_status,mount_params,nas_version,port,nas_type,nas_status,permission_flag,detail,user_uuid) 
                    select ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? 
                    from dual where not exists (select nas_uuid from nas_storage_resource where nas_uuid = ?)";
	        $sqlParams = array($c['nas_name'], $c['nas_nickname'], $c['nas_uuid'], $c['share_path'], $c['nas_create_time'],$c['ip'],$c['user_name'],
	            $c['password'],$c['authorization_status'],$c['mount_params'],$c['nas_version'],$c['port'],$c['nas_type'],$c['nas_status'],
	            $c['permission_flag'],$c['detail'],$c['user_uuid'],$c['nas_uuid']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
        //插入nas_mount_list表
	    foreach ($nas_mount_list as $c){
	        $sql =  "insert into nas_mount_list(nas_uuid,node_uuid,nas_state,mount_point,agent_uuid) select ?,?,?,?,?
                    from dual where not exists (select nas_uuid from nas_mount_list where nas_uuid = ?)";
	        $sqlParams = array($c['nas_uuid'], $c['node_uuid'], $c['nas_state'], $c['mount_point'], $c['agent_uuid'],$c['nas_uuid']);
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    $this->writeRecLog($value, $result);
	    return $result;
	}
	
    /**
     * 恢复m365组织信息
     * @param unknown $value
     */
    private function recoveryM365Manager($value){
        $content = $this->getRecoveryFileInfo($value['name']);
        $result = true;

        $m365Organization = $content['m365_organization'];
        $m365AzureAdApp = $content['m365_azure_ad_app'];
        $m365User = $content['m365_user'];

        //插入m365_organization表
        foreach ($m365Organization as $c){
            $sql =  "insert into m365_organization(organization_uuid,agent_uuid_list,organization_name,region,
                              auth_apps,online_flag,error_code,create_time,refresh_status,nickname,refresh_time,user_uuid) 
                    select ?,?,?,?,?,?,?,?,?,?,?,? 
                    from dual where not exists (select organization_uuid from m365_organization where organization_uuid = ?)";
            $sqlParams = array($c['organization_uuid'], $c['agent_uuid_list'], $c['organization_name'], $c['region'],
                $c['auth_apps'],$c['online_flag'],$c['error_code'], $c['create_time'],$c['refresh_status'],
                $c['nickname'],$c['refresh_time'],$c['user_uuid'],$c['organization_uuid']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

        //插入m365_azure_ad_app表
        foreach ($m365AzureAdApp as $c){
            $sql =  "insert into m365_azure_ad_app(organization_uuid,tenant_uuid,app_uuid,app_secret,app_cert_info,app_name,username) 
                    select ?,?,?,?,?,?,? 
                    from dual where not exists (select app_uuid from m365_azure_ad_app where app_uuid = ?)";
            $sqlParams = array($c['organization_uuid'], $c['tenant_uuid'], $c['app_uuid'], $c['app_secret'],
                $c['app_cert_info'],$c['app_name'],$c['username'],$c['app_uuid']);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

        //插入m365_user表
        foreach ($m365User as $c){
            $sql =  "insert into m365_user(organization_uuid,user_uuid,display_name,mail,type,members) 
                    select ?,?,?,?,?,?
                    from dual where not exists (select user_uuid from m365_user where user_uuid = ?)";
            $sqlParams = array($c['organization_uuid'], $c['user_uuid'], $c['display_name'], $c['mail'],
                $c['type'],$c['members'],$c['user_uuid']);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

        $this->writeRecLog($value, $result);
        return $result;
    }
	/**
	 * 恢复hadoop资源数据
	 * @param mixed $value
	 * @return void
	 */
	private function recoveryHadoopManager($value) {
		$content = $this->getRecoveryFileInfo($value['name']);
		$result = true;
		$hadoopCluster = $content['hadoop_cluster'];
        $hadoopNamenode = $content['hadoop_namenode'];

		//插入hadoop_cluster表
        foreach ($hadoopCluster as $c){
            $sql =  "insert into hadoop_cluster(hadoop_cluster_uuid,hadoop_cluster_name,proxy_uuid,addition_time,detail,status,authorization,user_uuid) 
                    select ?,?,?,?,?,?,?,? 
                    from dual where not exists (select hadoop_cluster_uuid from hadoop_cluster where hadoop_cluster_uuid = ?)";
            $sqlParams = array($c['hadoop_cluster_uuid'], $c['hadoop_cluster_name'], $c['proxy_uuid'], $c['addition_time'], $c['detail'],$c['status'],$c['authorization'],
							$c['user_uuid'],$c['hadoop_cluster_uuid']);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

		//插入hadoop_namenode表
        foreach ($hadoopNamenode as $c){
            $sql =  "insert into hadoop_namenode(hadoop_cluster_uuid,namenode_username,namenode_ip,namenode_hostname,rest_api_port,verification_type,realm_name,realm_kdc_server,
						realm_manage_server,rest_api_principal,udp_preference_limit,krb5_keytab,status,detail,ssl_verify_flag) 
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? 
                    from dual where not exists (select namenode_ip from hadoop_namenode where namenode_ip = ?)";
            $sqlParams = array($c['hadoop_cluster_uuid'], $c['namenode_username'], $c['namenode_ip'], $c['namenode_hostname'], $c['rest_api_port'],$c['verification_type'],
							$c['realm_name'],$c['realm_kdc_server'],$c['realm_manage_server'],$c['rest_api_principal'],$c['udp_preference_limit'],$c['krb5_keytab'],$c['status'],
							$c['detail'],$c['ssl_verify_flag'],$c['namenode_ip']);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }
        $this->writeRecLog($value, $result);
        return $result;
	}

	/**
	 * 恢复obs_resource资源数据
	 * @return void
	 */
	private function recoveryObsManager($value)  {
		$content = $this->getRecoveryFileInfo($value['name']);
		$result = true;
		$obsResource = $content['obs_resource'];
		//插入obs_resource表
        foreach ($obsResource as $c){
			$sql =  "insert into obs_resource(obs_uuid,user_uuid,obs_nickname,obs_create_time,vendor,access_key_id,access_key_secret,endpoint_override,ssl_verify_flag,status,detail,proxy_uuid,authorization) 
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    from dual where not exists (select vendor,access_key_id,endpoint_override from obs_resource where vendor = ? and access_key_id = ? and endpoint_override = ? and user_uuid = ?)";
            $sqlParams = array($c['obs_uuid'], $c['user_uuid'], $c['obs_nickname'], $c['obs_create_time'], $c['vendor'],$c['access_key_id'],$c['access_key_secret'],
					$c['endpoint_override'],$c['ssl_verify_flag'],$c['status'],$c['detail'],$c['proxy_uuid'],$c['authorization'],$c['vendor'],$c['access_key_id'],$c['endpoint_override'],$c['user_uuid']);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }
		$this->writeRecLog($value, $result);
        return $result;
	}
	/**
	 * 恢复实时备份代理
	 * 恢复逻辑
     * cdp_db_host表如果有相同host_uuid,不插入,如果没有才插入
	 */
	private function recoveryCDPAgentManager($value){
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有cdp代理
	    $uuids = array();
	    $sql = "select host_uuid from cdp_db_host";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['host_uuid'];
	    }
	    $result = true;
	    $cdp_db_host = $content['cdp_db_host'];
	    //插入cdp_db_host表
	    foreach ($cdp_db_host as $c){
	        if(!in_array($c['host_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_db_host(host_uuid, host_name, ip, os_version, host_type, status,
                  register_time, user_uuid, detail) values(?, ?, ?, ?, ?, ?,
                     ?, ?, ?)";
	            $sqlParams = array($c['host_uuid'], $c['host_name'], $c['ip'], $c['os_version'], $c['host_type'], $c['status'],
	                $c['register_time'], $c['user_uuid'], $c['detail']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	/**
	 * 恢复appliance
     * 恢复逻辑
     * bd_appliance表如果有相同host_uuid,不插入,如果没有才插入
	 */
	private function recoveryApplianceManager($value){
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有cdp代理
	    $uuids = array();
	    $sql = "select appliance_uuid from bd_appliance";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['appliance_uuid'];
	    }
	    $result = true;
	    $bd_appliance = $content['bd_appliance'];
	    //插入bd_appliance表
	    foreach ($bd_appliance as $c){
	        if(!in_array($c['appliance_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_appliance(appliance_uuid, node_uuid, user_uuid, register_time, nickname, online_flag,
                  ip, port, progress_server_listen_port, progress_server_start_port, progress_server_end_port, cdp_client_listen_port, 
                    cdp_client_log_listen_port, log_server_listen_port, system_info, detail) values(?, ?, ?, ?, ?, ?,
                     ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['appliance_uuid'], $this->getNewNodeUUID($c['node_uuid']), $c['user_uuid'], $c['register_time'], $c['nickname'], $c['online_flag'],
	                $c['ip'], $c['port'], $c['progress_server_listen_port'], $c['progress_server_start_port'], $c['progress_server_end_port'], $c['cdp_client_listen_port'], 
	                $c['cdp_client_log_listen_port'], $c['log_server_listen_port'], $c['system_info'] , $c['detail']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    $this->writeRecLog($value, $result);
	    return $result;
	}
	/**
	 * 恢复当前任务
	 */
	private function recoveryCurrentJob($value){
	    /**
	     * 恢复逻辑(按照如下顺序插入,因为bd_strategy的ID值有变化)
	     * bd_strategy表直接插入,插入后记录新的ID,并做对应关系
	     * bd_time_strategy表直接插入,使用bd_strategy的新插入的ID值
	     * bd_reserved_strategy表直接插入,使用bd_strategy的新插入的ID值
	     * bd_transport_strategy表直接插入,使用bd_strategy的新插入的ID值
	     * bd_node_network 表如果有相同的network_uuid,不插入，反之插入
	     * bd_task_agent_list 表如何有相同的task_uuid,不插入，反之插入
	     * bd_storage_strategy表直接插入,使用bd_strategy的新插入的ID值
	     * bd_task_speed_limit_strategy表直接插入,使用bd_strategy的新插入的ID值
	     * bd_task表如果有相同task_uuid,不插入,如果没有才插入,插入时使用bd_strategy的新插入的ID值
	     * bd_running_info表如果有相同task_uuid,不插入,如果没有才插入
	     * cdp_db_task表如果有相同task_uuid,不插入,如果没有才插入
	     * cdp_fs_task表如果有相同task_uuid,不插入,如果没有才插入
	     * vm_task表如果有相同task_uuid,不插入,如果没有才插入
	     * vm_machine_list表判断task_uuid,vcenter_uuid,vm_uuid,如果没有才插入
	     * vm_instant 表如果有相同值的task_uuid存在不插入，反之需要插入
	     * vm_grain_info 表如果有相同值的task_uuid存在不插入，反之需要插入
	     * fs_task表如果有相同task_uuid,不插入,如果没有才插入
	     * fs_running_info表如果有相同task_uuid,不插入,如果没有才插入
	     * fs_path_list表直接插入
	     * db_instance表直接插入
	     * db_list表直接插入
	     * db_task表如果有相同task_uuid,不插入,如果没有才插入
	     * backup_copy_task表如果有相同task_uuid,不插入,如果没有才插入
	     * backup_copy_item_list表直接插入
	     * bd_task_gfs_retention_strategy表，判断task_uuid，level1_type， level2_type， retention_num四个值是否重复，如果不重复，插入
	     * bd_gfs_entity_waiting_map表，判断task_uuid，entity_uuid，level1_type判断重复值，如果没有插入
	     * os_list 表，如果有相同agent_uuid，不插入，如果没有才插入
	     * os_task 表，如果有相同task_uuid，不插入，反之需要插入
	     * cdp_vol_*相关表再有相同task_uuid时不能写入，反之才可以写入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有当前任务
	    $uuids = array();
	    $sql = "select task_uuid from bd_task";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['task_uuid'];
	    }
	    $result = true;
	    
	    $bd_strategy = $content['bd_strategy'];
	    $bd_time_strategy = $content['bd_time_strategy'];
	    $bd_reserved_strategy = $content['bd_reserved_strategy'];
	    $bd_transport_strategy = $content['bd_transport_strategy'];
	    $bd_node_network = $content['bd_node_network'];
	    $bd_task_agent_list = $content['bd_task_agent_list'];
	    $bd_storage_strategy = $content['bd_storage_strategy'];
	    $bd_task_speed_limit_strategy = $content['bd_task_speed_limit_strategy'];
	    $bd_task = $content['bd_task'];
	    $bd_running_info = $content['bd_running_info'];
	    $cdp_db_task = $content['cdp_db_task'];
	    $cdp_fs_task = $content['cdp_fs_task'];
	    
	    $vm_task = $content['vm_task'];
	    $vm_machine_list = $content['vm_machine_list'];
        $vm_object_list = $content['vm_object_list'];
	    
	    /**9.19版本暂不支持该功能模块的备份与恢复
	    $vm_instant = $content['vm_instant'];
	    $vm_grain_info = $content['vm_grain_info'];
	    */
	    $fs_task = $content['fs_task'];
	    $fs_running_info = $content['fs_running_info'];
	    $fs_path_list = $content['fs_path_list'];
	    
	    $nas_task = $content['nas_task'];
	    
	    $db_instance = $content['db_instance'];
	    $db_list = $content['db_list'];
	    $db_task = $content['db_task'];
	    $backup_copy_task = $content['backup_copy_task'];
	    $backup_copy_item_list = $content['backup_copy_item_list'];
        $copy_task = $content['copy_task'];
        $copy_list = $content['copy_list'];
        $copy_running_info = $content['copy_running_info'];

	    $bd_task_gfs_retention_strategy = $content['bd_task_gfs_retention_strategy'];
	    $bd_gfs_entity_waiting_map = $content['bd_gfs_entity_waiting_map'];
	    
	    $sr_sure_backup = $content['sr_sure_backup'];
	    $sr_sure_backup_instant_vm = $content['sr_sure_backup_instant_vm'];
	    
	    $os_list = $content['os_list'];
	    $os_task = $content['os_task'];
	    
	    //不支持恢复的任务类型
	    $tasktypeList = array(
	        Xphp::$_config['TASKTYPE']['RECOVERY'],
	        Xphp::$_config['TASKTYPE']['DB_RECOVERY'],
	        Xphp::$_config['TASKTYPE']['OS_RECOVERY'],
	        Xphp::$_config['TASKTYPE']['VM_FILE_RECOVERY'],
	        Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'],  //虚拟机瞬时恢复
	        Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'],  //在线迁移
	        Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'],
	    );
	    
	    //插入bd_strategy表
	    foreach ($bd_strategy as $c){
	        $sql = "insert into bd_strategy(strategy_name, description, next_start_time, next_strategy_type, next_mode) 
                    select ?, ?, ?, ?, ? from dual where not exists (select strategy_id from bd_strategy where 
                    strategy_name = ? and next_start_time = ? and strategy_id =?)";
	        $sqlParams = array($c['strategy_name'], $c['description'], $c['next_start_time'], $c['next_strategy_type'], 
	            $c['next_mode'],$c['strategy_name'],$c['next_start_time'],$c['strategy_id']);
	       
	        $queryResult = $this->dbQuery($sql, $sqlParams);
	        
	        if($queryResult > 0){
	            //添加策略ID对应关系
	            $lastInsertId = $this->dbLastInsertId(); 
	            $key = $c['strategy_id'];
	            $this->strategyIDCompareList[$key] = $lastInsertId;//键名是以前的id,值是新的id 
	        }
	    }
	    
	    //插入bd_time_strategy表
	    foreach ($bd_time_strategy as $c){ 
	        //因为副本任务时间策略表中无任务uuid无法去重，在导出数据时获取策略对应的uuid写入到对应的导出数据中。导入时需要删除创建时任务uuid为空的时间策略信息，后可重复导入；
	        $lastSql = "select task_uuid,strategy_id from bd_time_strategy where strategy_id = ?";
	        $data = $this->dbSelect($lastSql,array($c['strategy_id']));
	        if(!empty($data)){
	            foreach ($data as $v){
	                $strategy_id = $v['strategy_id'];
	                if($v['task_uuid']=="" || $v['task_uuid']==NULL){
	                    $delSql = "delete from bd_time_strategy where strategy_id = ?";
	                    $dataDel = $this->dbExec($delSql,array($strategy_id));
	                }
	            }
	        }
	        
	        $sql = "insert into bd_time_strategy (strategy_id, mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time, 
                last_start_time, last_finish_time, global_id, strategy_group_uuid, task_uuid) select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? 
                from dual where not exists 
                (select strategy_type,days,start_time,task_uuid from bd_time_strategy where strategy_type = ? and task_uuid = ? and  start_time=? and mode = ?)";
	        
	        $sqlParams = array($this->getNewStrategyID($c['strategy_id']), $c['mode'], $c['strategy_type'], $c['days'], $c['start_time'], $c['roll_flag'],
	            $c['roll_interval'], $c['roll_end_time'], $c['last_start_time'], $c['last_finish_time'], $c['global_id'], $c['strategy_group_uuid'],
	            $c['task_uuid'],$c['strategy_type'],$c['task_uuid'], $c['start_time'], $c['mode']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入bd_reserved_strategy表
	    foreach ($bd_reserved_strategy as $c){
	        $sql = "insert into bd_reserved_strategy(strategy_id, strategy_type, number,auto_archive, strategy_group_uuid,
                                 task_uuid,strategy_mode)
                    select ?, ?, ?, ?, ?, ?, ? 
                    from dual 
                    where not exists (select strategy_id from bd_reserved_strategy where strategy_id =? and task_uuid =?)";
	        $sqlParams = array($this->getNewStrategyID($c['strategy_id']), $c['strategy_type'], $c['number'], $c['auto_archive'], 
	            $c['strategy_group_uuid'], $c['task_uuid'],$c['strategy_mode'],$c['strategy_id'],$c['task_uuid']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入bd_transport_strategy表
	    foreach ($bd_transport_strategy as $c){
	        $sql = "insert into bd_transport_strategy(strategy_id, encrypt_flag, compress_flag, max_transport_speed, 
                        strategy_group_uuid,task_uuid,block_size,network_uuid,compress_method,reconnect_times,
                        reconnect_interval,encrypt_method  )
                    select ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?  
                    from dual where not exists (select strategy_id from bd_transport_strategy where strategy_id =? and task_uuid =?)";
	        $sqlParams = array($this->getNewStrategyID($c['strategy_id']), $c['encrypt_flag'], $c['compress_flag'],
                $c['max_transport_speed'], $c['strategy_group_uuid'], $c['task_uuid'],$c['block_size'],$c['network_uuid'],
                $c['compress_method'],$c['reconnect_times'],$c['reconnect_interval'],$c['encrypt_method'],$c['strategy_id'],
                $c['task_uuid']);
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入bd_node_network表
	    foreach ($bd_node_network as $c){
            $sql = "INSERT INTO bd_node_network (node_uuid, network_uuid, alias_name, ip, port, network_order, detail, 
                             type, mac, netmask, gateway, network_name, dns, ip_type)  
                    SELECT ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    FROM DUAL  WHERE NOT EXISTS (  
                        SELECT 1 FROM bd_node_network  WHERE network_uuid = ? 
                    ) AND EXISTS (  
                        SELECT 1 FROM bd_node_network  
                    WHERE mac = ? AND ip <> ?  
                    )";
            $sqlParams = array($c['node_uuid'],$c['network_uuid'],$c['alias_name'],$c['ip'],$c['port'],$c['network_order'],
                $c['detail'],$c['type'],$c['mac'],$c['netmask'],$c['gateway'],$c['network_name'],$c['dns'],$c['ip_type'],
                $c['network_uuid'],$c['mac'],$c['ip']
            );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入bd_task_agent_list表
	    foreach ($bd_task_agent_list as $c){
	        $sql = "insert into bd_task_agent_list (task_uuid,group_uuid,agent_uuid,task_status,detail)
                    select ?,?,?,?,? 
                    from dual where not exists (select task_uuid from bd_task_agent_list where task_uuid = ? )";
	        $sqlParams = array($c['task_uuid'],$c['group_uuid'],$c['agent_uuid'],$c['task_status'],$c['detail'],$c['task_uuid']);
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入bd_storage_strategy表
	    foreach ($bd_storage_strategy as $c){
	        $sql = "insert into bd_storage_strategy(strategy_id,deduplication_flag, block_size, compressed_flag, encrypted_flag, strategy_group_uuid,
                    task_uuid, password_auto_flag, password,compress_method,encrypt_method) 
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? 
                    from dual where not exists (select strategy_id from bd_storage_strategy where strategy_id =? and task_uuid =?)";
	        
	        $sqlParams = array($this->getNewStrategyID($c['strategy_id']),$c['deduplication_flag'], $c['block_size'],
                $c['compressed_flag'], $c['encrypted_flag'],$c['strategy_group_uuid'], $c['task_uuid'], $c['password_auto_flag'],
                $c['password'],$c['compress_method'],$c['encrypt_method'],$c['strategy_id'],$c['task_uuid']);
	        
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入bd_task_speed_limit_strategy表
	    foreach ($bd_task_speed_limit_strategy as $c){
	        $sql = "insert into bd_task_speed_limit_strategy(strategy_uuid, strategy_name, 	strategy_group_uuid, task_uuid, strategy_type, start_time,
                    end_time, speed_limited_value, days, remark, extra_info)
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? 
                    from dual where not exists (select strategy_uuid from bd_task_speed_limit_strategy where strategy_uuid =? and task_uuid =?)";
	        $sqlParams = array($c['strategy_uuid'], $c['strategy_name'], $c['strategy_group_uuid'], $c['task_uuid'], $c['strategy_type'],
	            $c['start_time'], $c['end_time'], $c['speed_limited_value'], $c['days'], $c['remark'], $c['extra_info'],$c['strategy_uuid'], $c['task_uuid']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    //插入bd_task表
	    foreach ($bd_task as $c){
	        if(!in_array($c['task_uuid'], $uuids) && !in_array($c['task_type'], $tasktypeList)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_task(task_uuid, task_name, module_type,sub_module_type,task_type, recovery_position, task_status,
                        strategy_id, create_time, agent_uuid, user_uuid, delete_flag, node_uuid, auto_find_sr_flag, 
                        storage_uuid, thread_num, strategy_group_uuid,backup_server_ip,transport_ip_segment,cache_dir_path,
						task_orchestration_plan_flag,recovery_type,inc_mode)
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array($c['task_uuid'], $c['task_name'], $c['module_type'],$c['sub_module_type'],$c['task_type'], $c['recovery_position'], 
								Xphp::$_config['TASKSTATUS']['STOPPED'],$this->getNewStrategyID($c['strategy_id']), $c['create_time'],$c['agent_uuid'],
								$c['user_uuid'], $c['delete_flag'], $this->getTaskNewNodeUUID($c['node_uuid'],$c['node_type']),
								$c['auto_find_sr_flag'],$c['storage_uuid'], $c['thread_num'], $c['strategy_group_uuid'], $c['backup_server_ip'],$c['transport_ip_segment'],
								$c['cache_dir_path'],$c['task_orchestration_plan_flag'],$c['recovery_type'],$c['inc_mode']);
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    //插入bd_running_info表
	    foreach ($bd_running_info as $c){
	        if(!in_array($c['task_uuid'],$uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_running_info(task_uuid, current_mode, auto_run_flag, total_object_size, 
                            total_object_completed_size,current_object,current_object_total_size, current_object_completed_size,
                            total_object_write_size ,current_object_write_size,total_object_transport_size,current_object_transport_size,
                            speed,speed_time, start_time, finish_time, storage_uuid,total_object_valid_size,current_object_valid_size,
                            total_object_completed_valid_size,current_object_completed_valid_size) 
                        select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?  
                        from dual where not exists(select task_uuid from bd_running_info where task_uuid = ?) ";
	            //兼容性处理导入数据
// 	            $c = $this->compatibleBdRunningInfo($c);
	            $sqlParams = array(
	                $c['task_uuid'],
	                $c['current_mode'], 
	                $c['auto_run_flag'], 
	                $c['total_object_size'], 
	                $c['total_object_completed_size'],
	                $c['current_object'],
	                $c['current_object_total_size'], 
	                $c['current_object_completed_size'],
	                $c['total_object_write_size'], 
	                $c['current_object_write_size'],
	                $c['total_object_transport_size'],
	                $c['current_object_transport_size'],
	                $c['speed'],
	                $c['speed_time'], 
	                $c['start_time'], 
	                $c['finish_time'], 
	                $c['storage_uuid'],
	                $c['total_object_valid_size'], 
	                $c['current_object_valid_size'], 
	                $c['total_object_completed_valid_size'], 
	                $c['current_object_completed_valid_size'],
                    $c['task_uuid'],
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    //插入cdp_db_task表
	    foreach ($cdp_db_task as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_db_task(task_uuid, product_host_uuid, standby_host_uuid, config) values(?, ?, ?, ?)";
	            $sqlParams = array(
	                $c['task_uuid'], $c['product_host_uuid'], $c['standby_host_uuid'], $c['config']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    //插入nas_task表
	    foreach ($nas_task as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            $sql = "insert into nas_task(task_uuid, nas_uuid, level, detail,file_archive_flag,skip_file_alarm_flag,
                     skip_file_alarm_min_num,skip_file_alarm_min_ratio,same_file_strategy,link_file_pass_flag,
                     dir_tree_recovery_flag,permission_operate_flag) 
                    values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            $sqlParams = array(
	                $c['task_uuid'], $c['nas_uuid'], $c['level'], $c['detail'],$c['file_archive_flag'],$c['skip_file_alarm_flag'],
                    $c['skip_file_alarm_min_num'],$c['skip_file_alarm_min_ratio'],$c['same_file_strategy'],
                    $c['link_file_pass_flag'],$c['dir_tree_recovery_flag'],$c['permission_operate_flag']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    //插入cdp_fs_task表
	    foreach ($cdp_fs_task as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_fs_task(task_uuid, product_host_uuid, standby_host_uuid, config) values(?, ?, ?, ?)";
	            $sqlParams = array(
	                $c['task_uuid'], $c['product_host_uuid'], $c['standby_host_uuid'], $c['config']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    //插入vm_task表
	    foreach ($vm_task as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                //如果现在系统没有这个,可以插入到数据库
                $sql = "insert into vm_task(task_uuid, level, quiesce_snapshot, grain_level, hypervisor_type, valid_data_backup, 
                            transport_priority, serial_snapshot_flag, parse_fs_flag, not_backup_swap_file_flag,
                            not_backup_deleted_file_flag,not_backup_partition_gap_flag, agent_uuid, log_appliance_uuid,
                            pre_create_snap_flag,display_mode,select_conditions,detail,storage_snapshot_enable_flag) 
                        select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,? 
                        from dual where not exists(select task_uuid from vm_task where task_uuid = ?) ";
                $sqlParams = array(
                    $c['task_uuid'], $c['level'], $c['quiesce_snapshot'], $c['grain_level'], $c['hypervisor_type'],
                    $c['valid_data_backup'],$c['transport_priority'], $c['serial_snapshot_flag'], $c['parse_fs_flag'],
                    $c['not_backup_swap_file_flag'], $c['not_backup_deleted_file_flag'],$c['not_backup_partition_gap_flag'],
                    $c['agent_uuid'],$c['log_appliance_uuid'], $c['pre_create_snap_flag'],$c['display_mode'],$c['select_conditions'],
					$c['detail'],$c['storage_snapshot_enable_flag'],$c['task_uuid']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
	    }
	    //插入vm_machine_list表
	    foreach ($vm_machine_list as $c){
	        //先通过task_uuid , vcenter_uuid , vm_uuid 联合查询是否已经有重复值的记录,如果有不插入,没有才插入
	        $sql = "select count(machine_id) as total from vm_machine_list where task_uuid = ? and vcenter_uuid = ? and vm_uuid = ?";
	        $data = $this->dbSelect($sql, array($c['task_uuid'], $c['vcenter_uuid'], $c['vm_uuid']));
	        if($data[0]['total'] > 0){
	            //如果有记录了,跳过本条记录
	            continue;
	        }
            $sql = "insert into vm_machine_list(task_uuid, vcenter_uuid, vm_uuid, vm_name, version, host_uuid,
                        vcenter_ip, host_ip, dir_path, mode, vm_size, vm_valid_size, 
                        completed_size, write_size, transport_size, auto_datastore_flag, datastore, new_name, 
                        timepoint_uuid, timestamp, task_status, appliance_uuid, error_code, start_vm_flag, 
                        vm_config, extension_info)
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            //兼容性处理导入数据
            $c = $this->compatibleVmMachineList($c);
            $sqlParams = array(
                $c['task_uuid'], $c['vcenter_uuid'], $c['vm_uuid'], $c['vm_name'], $c['version'], $c['host_uuid'],
                $c['vcenter_ip'], $c['host_ip'], $c['dir_path'], $c['mode'], $c['vm_size'], $c['vm_valid_size'],
                $c['completed_size'], $c['write_size'], $c['transport_size'], $c['auto_datastore_flag'], $c['datastore'], $c['new_name'],
                $c['timepoint_uuid'], $c['timestamp'], $c['task_status'],$c['appliance_uuid'],$c['error_code'],$c['start_vm_flag'],
                $c['vm_config'],$c['extension_info']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
	    }

        //插入vm_object_list
        foreach ($vm_object_list as $c){
            //先通过task_uuid , vcenter_uuid , oobject_uuid  联合查询是否已经有重复值的记录,如果有不插入,没有才插入
            $sql = "select count(object_name) as total from vm_object_list where task_uuid = ? and vcenter_uuid = ? and object_uuid = ?";
            $data = $this->dbSelect($sql, array($c['task_uuid'], $c['vcenter_uuid'], $c['object_uuid']));
            if($data[0]['total'] > 0){ //如果有记录了,跳过本条记录
                continue;
            }
            $sql = "insert into vm_object_list(task_uuid, object_uuid, vcenter_uuid, type, exclude_vm_uuid_list,
                    detail,object_name, vm_config, dir_path)
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?)";
            //兼容性处理导入数据
            $c = $this->compatibleVmMachineList($c);
            $sqlParams = array(
                $c['task_uuid'], $c['object_uuid'], $c['vcenter_uuid'], $c['type'], $c['exclude_vm_uuid_list'],
                $c['detail'],$c['object_name'], $c['vm_config'], $c['dir_path']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

	    //插入fs_task表
	    foreach ($fs_task as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                //如果现在系统没有这个,可以插入到数据库
                $sql = "insert into fs_task(task_uuid, level,agent_group_uuid,snap_shot_flag,detail,file_archive_flag,
                    skip_file_alarm_flag,skip_file_alarm_min_num,skip_file_alarm_min_ratio,same_file_strategy,
                    link_file_pass_flag,dir_tree_recovery_flag,permission_operate_flag) 
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    from dual where not exists (select task_uuid from fs_task where task_uuid = ?)";
                $sqlParams = array(
                    $c['task_uuid'], $c['level'],$c['agent_group_uuid'],$c['snap_shot_flag'],$c['detail'],
                    $c['file_archive_flag'],$c['skip_file_alarm_flag'],$c['skip_file_alarm_min_num'],
                    $c['skip_file_alarm_min_ratio'],$c['same_file_strategy'],$c['link_file_pass_flag'],
                    $c['dir_tree_recovery_flag'],$c['permission_operate_flag'],$c['task_uuid']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
	    }

	    //插入fs_running_info表
        foreach ($fs_running_info as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                //如果现在系统没有这个,可以插入到数据库
                $sql = "insert into fs_running_info(task_uuid, current_fs_count, total_fs_count,current_dir_count,agent_uuid,current_object_total_size,current_object_completed_size)
                        values(?, ?, ?, ?, ?, ?, ?)";
                $sqlParams = array(
                    $c['task_uuid'], $c['current_fs_count'], $c['total_fs_count'],$c['current_dir_count'],$c['agent_uuid'],
                    $c['current_object_total_size'],$c['current_object_completed_size']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }
        //插入fs_path_list表
        foreach ($fs_path_list as $c){
            $sql = "insert into fs_path_list(task_uuid, path_uuid, path_name, path_type, new_root_path, latest_snapshot_set_uuid,
                        latest_snapshot_uuid, recovery_timepoint_uuid, recovery_md5,group_uuid,agent_uuid,backup_mode,code_type)
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?
                    from dual where not exists (select task_uuid,path_uuid from fs_path_list where task_uuid = ? and path_uuid = ?)";
            $sqlParams = array(
                $c['task_uuid'], $c['path_uuid'], $c['path_name'], $c['path_type'], $c['new_root_path'], $c['latest_snapshot_set_uuid'],
                $c['latest_snapshot_uuid'], $c['recovery_timepoint_uuid'], $c['recovery_md5'],$c['group_uuid'],$c['agent_uuid'],
                $c['backup_model'],$c['code_type'],$c['task_uuid'], $c['path_uuid']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        }
	    //插入db_instance表
	    foreach ($db_instance as $c){
            $sql = "insert into db_instance(agent_uuid, instance_name, db_type, auth_type, username, password,
                        save_time, dir_path, verify_detail, install_db_username, cluster_flag, cluster_uuid,db_version)
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
            $sqlParams = array(
                $c['agent_uuid'], $c['instance_name'], $c['db_type'], $c['auth_type'], $c['username'], $c['password'],
                $c['save_time'], $c['dir_path'], $c['verify_detail'], $c['install_db_username'], $c['cluster_flag'],
                $c['cluster_uuid'], $c['db_version']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
	    }

	    //插入db_list表
	    foreach ($db_list as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                $sql = "insert into db_list(task_uuid, db_name, backup_mode, file_num, total_size, new_db_name,
                            timepoint_uuid, instance_name, data_file_path, log_file_path, dir_path, task_status,
                            error_code, transport_size, write_size, log_rollback_time, new_db_password, db_uuid,
                            group_uuid,agent_uuid,detail,recovery_mode,	modify_pfile_flag)
                            values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,? ,? ,? ,?,?)";
                //兼容性处理导入数据
                $c = $this->compatibleDbList($c);
                $sqlParams = array(
                    $c['task_uuid'], $c['db_name'], $c['backup_mode'], $c['file_num'], $c['total_size'], $c['new_db_name'],
                    $c['timepoint_uuid'], $c['instance_name'], $c['data_file_path'], $c['log_file_path'], $c['dir_path'], $c['task_status'],
                    $c['error_code'], $c['transport_size'], $c['write_size'], $c['log_rollback_time'], $c['new_db_password'], $c['db_uuid'],
                    $c['group_uuid'],$c['agent_uuid'],$c['detail'],$c['recovery_mode'],$c['modify_pfile_flag']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
	    }
	    //插入db_task表
	    foreach ($db_task as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                //如果现在系统没有这个,可以插入到数据库
                $sql = "insert into db_task(task_uuid, db_type, level, check_db_flag, compress_flag, checksum_flag,
                        channel_count, last_archive_days, delete_archive_log_flag,detail,set_filesperset_flag,
                        datafile_filesperset_num,archivelog_filesperset_num,log_backup_days_flag,
                        log_backup_days,log_backup_times_flag,log_backup_times)
                        select ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?,?,? 
                        from dual where not exists(select task_uuid from db_task where task_uuid = ?)";
                $sqlParams = array(
                    $c['task_uuid'], $c['db_type'], $c['level'], $c['check_db_flag'], $c['compress_flag'],
                    $c['checksum_flag'],$c['channel_count'], $c['last_archive_days'], $c['delete_archive_log_flag'],
                    $c['detail'],$c['set_filesperset_flag'],$c['datafile_filesperset_num'],$c['archivelog_filesperset_num'],
                    $c['log_backup_days_flag'],$c['log_backup_days'],$c['log_backup_times_flag'],$c['log_backup_times'],
                    $c['task_uuid']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
	    }
	    //插入os_task表
	    foreach ($os_task as $c){
            $sql =  "insert into os_task(task_uuid,transport_priority,serial_snapshot_flag,cbt_flag,valid_data_flag,
                    silent_snapshot_flag,cache_target,inst_iscsi_network_uuid)
                        select ?, ?, ?, ?, ?, ?, ?, ?
                    from dual where not exists (select task_uuid from os_task where task_uuid = ?)";
            $sqlParams = array($c['task_uuid'], $c['transport_priority'], $c['serial_snapshot_flag'], $c['cbt_flag'],
                            $c['valid_data_flag'],$c['silent_snapshot_flag'],$c['cache_target'],$c['inst_iscsi_network_uuid'],
                            $c['task_uuid']);
            $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    //插入os_list表
	    foreach ($os_list as $c){
	        $sql =  "insert into os_list(task_uuid,os_name,agent_uuid,agent_ip,backup_mode,dir_path,exclude_devices_list,
                        disk_allocation_strategy,partition_allocation_strategy,timepoint_uuid,total_size,completed_size,
                        error_code,task_status,valid_size,transport_size,handling_partition,write_size,system_recovery_flag,
                        reparted_flag,os_config,tid,inst_recovery_agent_uuid,last_cache_migration_flag)
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?
                    from dual where not exists (select agent_uuid from os_list where agent_uuid = ?)";
	        $sqlParams = array($c['task_uuid'], $c['os_name'], $c['agent_uuid'], $c['agent_ip'], $c['backup_mode'], $c['dir_path'],
	            $c['exclude_devices_list'],$c['disk_allocation_strategy'], $c['partition_allocation_strategy'], $c['timepoint_uuid'],
	            $c['total_size'],$c['completed_size'], $c['error_code'], $c['task_status'], $c['valid_size'], $c['transport_size'],
	            $c['handling_partition'],$c['write_size'], $c['system_recovery_flag'],$c['reparted_flag'],$c['os_config'],
                $c['tid'],$c['inst_recovery_agent_uuid'],$c['last_cache_migration_flag'],$c['agent_uuid']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }

	    //插入backup_copy_task表
	    foreach ($backup_copy_task as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into backup_copy_task(task_uuid, real_storage_uuid, real_storage_name, real_storage_type, real_storage_total_size, real_storage_free_size,
                            last_storage_uuid) select ?, ?, ?, ?, ?, ?, ?
                            from dual where not exists(select task_uuid from backup_copy_task where task_uuid =?)";
	            $sqlParams = array(
	                $c['task_uuid'], $c['real_storage_uuid'], $c['real_storage_name'], $c['real_storage_type'], $c['real_storage_total_size'], $c['real_storage_free_size'],
	                $c['last_storage_uuid'],$c['task_uuid']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    //插入backup_copy_item_list表
	    foreach ($backup_copy_item_list as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into backup_copy_item_list(task_uuid, item_uuid, item_name, vcenter_uuid, hypervisor_type, source_task_uuid,
                            dir_path, target_storage_uuid, actual_storage_uuid, target_storage_type, actual_storage_type, node_uuid, 
                            total_copy_size, complete_size, transport_size, write_size, copy_status, error_code, 
                            src_timepoint_count, source_timepoint_list, handled_timepoint_list, new_timepoint_list, last_succeed_timepoint,
                            handling_timepoint, timepoint_files, handling_file, offset_in_file, specified_timepoint_list, detail) 
                            values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            //兼容性处理导入数据
	            $c = $this->compatibleBackupCopyItemList($c);
	            $sqlParams = array(
	                $c['task_uuid'], $c['item_uuid'], $c['item_name'], $c['vcenter_uuid'], $c['hypervisor_type'], $c['source_task_uuid'],
	                $c['dir_path'], $c['target_storage_uuid'], $c['actual_storage_uuid'], $c['target_storage_type'], $c['actual_storage_type'], $this->getNewNodeUUID($c['node_uuid']),
	                $c['total_copy_size'], $c['complete_size'], $c['transport_size'], $c['write_size'], $c['copy_status'], $c['error_code'],
	                $c['src_timepoint_count'], $c['source_timepoint_list'], $c['handled_timepoint_list'], $c['new_timepoint_list'], $c['last_succeed_timepoint'], $c['handling_timepoint'],
	                $c['timepoint_files'], $c['handling_file'], $c['offset_in_file'], $c['specified_timepoint_list'], $c['detail']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
        //插入copy_task表
        foreach ($copy_task as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                $sql = "insert into copy_task(task_uuid, copy_mode, chain_length, transport_ip, transport_port, transport_data_encrypt,
                            hash_inc_flag,wan_accelerate_flag,detail) values(?, ?, ?, ?, ?, ?, ?,?,?)";
                $sqlParams = array(
                    $c['task_uuid'], $c['copy_mode'], $c['chain_length'], $c['transport_ip'], $c['transport_port'], $c['transport_data_encrypt'],
                    $c['hash_inc_flag'],$c['wan_accelerate_flag'],$c['detail']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }

        //插入copy_list表
        foreach ($copy_list as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                $sql = "insert into copy_list(task_uuid, item_uuid, item_name, parent_uuid, sub_type, source_task_uuid,
                            source_storage_uuid,total_size,transport_size,write_size,flowate_size,item_status,error_code,timepoint_count,
                            specified_timepoint_list,archive_timepoint_uuid,handled_timepoint_list,detail,dir_path
                            ) values(?, ?, ?, ?, ?, ?, ?,?,?,?, ?, ?, ?, ?, ?, ?,?,?,?)";
                $sqlParams = array(
                    $c['task_uuid'], $c['item_uuid'], $c['item_name'], $c['parent_uuid'], $c['sub_type'], $c['source_task_uuid'],
                    $c['source_storage_uuid'],$c['total_size'],$c['transport_size'],$c['write_size'],$c['flowate_size'],$c['item_status'],
                    $c['error_code'],$c['timepoint_count'],$c['specified_timepoint_list'],$c['archive_timepoint_uuid'],
                    $c['handled_timepoint_list'],$c['detail'],$c['dir_path']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }
        //插入copy_running_info表
        foreach ($copy_running_info as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                $sql = "insert into copy_running_info(task_uuid,remainder_time, upload_flowrate, download_flowrate, api_count, detail) 
                        select ?, ?, ?, ?, ?, ?  
                        from dual where not exists (select task_uuid from copy_running_info where task_uuid = ?)";
                $sqlParams = array(
                    $c['task_uuid'], $c['remainder_time'], $c['upload_flowrate'], $c['download_flowrate'], $c['api_count'], $c['detail'],$c['task_uuid']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }

        //=====================================m365==================================//
        $m365_task = $content['m365_task'];
        $m365_object_list = $content['m365_object_list'];
        $m365_running_info = $content['m365_running_info'];

        //插入m365_task表
        foreach ($m365_task as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                $sql = "insert into m365_task(task_uuid,m365_type, detail, last_max_index_container_id, user_delta_token, 
                      group_delta_token,last_max_data_container_id,organization_uuid,recovery_timepoint_uuid,m365_retry_info) 
                        select  ?, ?, ?, ?, ?, ?,?, ?, ?, ?
                        from dual where not exists (select task_uuid from m365_task where task_uuid = ?) ";
                $sqlParams = array(
                    $c['task_uuid'], $c['m365_type'], $c['detail'], $c['last_max_index_container_id'], $c['user_delta_token'],
                    $c['group_delta_token'],$c['last_max_data_container_id'],$c['organization_uuid'],
                    $c['recovery_timepoint_uuid'],$c['m365_retry_info'],$c['task_uuid']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }
        //插入m365_object_list表
        foreach ($m365_object_list as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                $sql = "insert into m365_object_list(task_uuid,organization_uuid, exclude_organization_items, exclude_folders, recovery_object_info, 
                      backup_object_info,destination_organization_uuid,destination_user_uuid,destination_mail,destination_user_type) 
                        select  ?, ?, ?, ?, ?, ?,?, ?, ?, ?
                        from dual where not exists (select task_uuid from m365_object_list where task_uuid = ?) ";
                $sqlParams = array(
                    $c['task_uuid'], $c['organization_uuid'], $c['exclude_organization_items'], $c['exclude_folders'],
                    $c['recovery_object_info'],$c['backup_object_info'],$c['destination_organization_uuid'],$c['destination_user_uuid'],
                    $c['destination_mail'],$c['destination_user_type'],$c['task_uuid']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }
        //插入m365_running_info表
        foreach ($m365_running_info as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                $sql = "insert into m365_running_info(task_uuid,current_complete_item_count, current_complete_user_count, 
                        total_user_count, total_item_count) select  ?, ?, ?, ?, ?
                        from dual where not exists (select task_uuid from m365_running_info where task_uuid = ?) ";
                $sqlParams = array(
                    $c['task_uuid'], $c['current_complete_item_count'], $c['current_complete_user_count'], $c['total_user_count'],
                    $c['total_item_count'],$c['task_uuid']);
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
        }
	    //==================================================卷CDP相关===================================================================//
	    $cdp_vol_task = $content['cdp_vol_task'];
	    $cdp_vol_task_cache_info = $content['cdp_vol_task_cache_info'];
	    $cdp_vol_task_data_consistency_check_info = $content['cdp_vol_task_data_consistency_check_info'];
	    $cdp_vol_task_io_mapping_vol = $content['cdp_vol_task_io_mapping_vol'];
	    $cdp_vol_task_progress_info = $content['cdp_vol_task_progress_info'];
	    $cdp_vol_task_running_info = $content['cdp_vol_task_running_info'];
	    $cdp_vol_task_takeover_app = $content['cdp_vol_task_takeover_app'];
	    $cdp_vol_task_takeover_failback_info = $content['cdp_vol_task_takeover_failback_info'];
	    $cdp_vol_task_takeover_info = $content['cdp_vol_task_takeover_info'];
	    $cdp_vol_task_takeover_lun = $content['cdp_vol_task_takeover_lun'];
	    $cdp_vol_task_takeover_script = $content['cdp_vol_task_takeover_script'];
	    $cdp_vol_task_takeover_target = $content['cdp_vol_task_takeover_target'];
	    $cdp_vol_task_vol = $content['cdp_vol_task_vol'];

	    //插入cdp_vol_task表
	    foreach ($cdp_vol_task as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task(task_uuid, current_task_running_stage, master_agent_uuid, standby_agent_uuid, 
                         host_ha_standby_agent_uuid, master_agent_online_flag,master_agent_ip, recovery_target_agent_uuid, 
                         auto_takeover_flag,rebuild_partition_flag,recovery_data_source, monitor_data_io_replication_mode, 
                         recovery_type, takeover_data_source,mirror_backup_flag,wait_resume_flag,monitor_invalid_flag)
                            values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	            //兼容性处理导入数据
	            $sqlParams = array(
	                $c['task_uuid'], $c['current_task_running_stage'], $c['master_agent_uuid'], $c['standby_agent_uuid'],
                    $c['host_ha_standby_agent_uuid'],$c['master_agent_online_flag'], $c['master_agent_ip'],
                    $c['recovery_target_agent_uuid'], $c['auto_takeover_flag'],$c['rebuild_partition_flag'],$c['recovery_data_source'],
	                $c['monitor_data_io_replication_mode'], $c['recovery_type'],$c['takeover_data_source'],$c['mirror_backup_flag'],
                    $c['wait_resume_flag'],$c['monitor_invalid_flag']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_cache_info表
	    foreach ($cdp_vol_task_cache_info as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_cache_info(task_uuid, agent_memory_cache_alloc_space, agent_memory_cache_used_space, agent_file_cache_alloc_space,
                            agent_file_cache_used_space, agent_cache_file_storage_path, server_cache_file_storage_path)
                            values(?, ?, ?, ?, ?, ?, ?)";
	            //兼容性处理导入数据
	            $sqlParams = array(
	                $c['task_uuid'], $c['agent_memory_cache_alloc_space'], $c['agent_memory_cache_used_space'], $c['agent_file_cache_alloc_space'],
	                $c['agent_file_cache_used_space'], $c['agent_cache_file_storage_path'], $c['server_cache_file_storage_path']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }


	    //插入cdp_vol_task_data_consistency_check_info表
	    foreach ($cdp_vol_task_data_consistency_check_info as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
			  	 $sql = "insert into cdp_vol_task_data_consistency_check_info(task_uuid,total_size, completed_size,current_vol_uuid) 
					select  ?, ?, ?, ? 
					from dual where not exists (select task_uuid from cdp_vol_task_data_consistency_check_info where task_uuid = ?) ";
	   			//兼容性处理导入数据
	  			$sqlParams = array($c['task_uuid'], $c['total_size'], $c['completed_size'],$c['current_vol_uuid'],$c['task_uuid']);
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_io_mapping_vol表
	    foreach ($cdp_vol_task_io_mapping_vol as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_io_mapping_vol(task_uuid, vol_uuid, mapping_vol_path,vol_real_path,index_bitmap_path,vol_cache_path,vol_partition_header_path)
                            values(?,?,?,?,?,?,?)";
	            //兼容性处理导入数据
	            $sqlParams = array(
	                $c['task_uuid'], $c['vol_uuid'], $c['mapping_vol_path'], $c['vol_real_path'], $c['index_bitmap_path'], $c['vol_cache_path'], $c['vol_partition_header_path']);
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_progress_info表
	    foreach ($cdp_vol_task_progress_info as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_progress_info(task_uuid, status, vol_uuid,total_size,completed_size,valid_size,completed_valid_size)
                            values(?, ?, ?, ?, ?, ?, ?)";
	            //兼容性处理导入数据
	            $sqlParams = array($c['task_uuid'],$c['status'],$c['vol_uuid'],$c['total_size'],$c['completed_size'],
                    $c['valid_size'],$c['completed_valid_size']);
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_running_info表
	    foreach ($cdp_vol_task_running_info as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_running_info(task_uuid, master_to_server_sync_delay_time, master_to_server_sync_delay_size,
                            master_to_standby_sync_delay_time,master_to_standby_sync_delay_size)
                        values(?,?,?,?,?)";
	            //兼容性处理导入数据
	            $sqlParams = array($c['task_uuid'],$c['master_to_server_sync_delay_time'],$c['master_to_server_sync_delay_size'],
	                $c['master_to_standby_sync_delay_time'],$c['master_to_standby_sync_delay_size']);
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_takeover_app表
	    foreach ($cdp_vol_task_takeover_app as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_takeover_app(task_uuid, app_uuid, target_app_uuid,failback_target_app_uuid)
                        values(?,?,?,?)";
	            //兼容性处理导入数据
	            $sqlParams = array($c['task_uuid'],$c['app_uuid'],$c['target_app_uuid'],$c['failback_target_app_uuid']);
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_takeover_failback_info表
	    foreach ($cdp_vol_task_takeover_failback_info as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_takeover_failback_info(task_uuid, failback_target_agent_uuid, memory_cache_alloc_space,file_cache_alloc_space,
                         file_cache_storage_path,transport_encrypt_flag,transport_compress_flag,monitor_data_io_replication_mode,storage_uuid,transport_thread_num,
                         transport_block_size,mirror_backup_flag)
                        values(?,?,?,?,?,?,?,?,?,?,?,?)";
	            //兼容性处理导入数据
	            $sqlParams = array($c['task_uuid'],$c['failback_target_agent_uuid'],$c['memory_cache_alloc_space'],$c['file_cache_alloc_space'],
	                   $c['file_cache_storage_path'],$c['transport_encrypt_flag'],$c['transport_compress_flag'],$c['monitor_data_io_replication_mode'],
	                   $c['storage_uuid'],$c['transport_thread_num'],$c['transport_block_size'],$c['mirror_backup_flag']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_takeover_info表
	    foreach ($cdp_vol_task_takeover_info as $c){
            if(!in_array($c['task_uuid'], $uuids)){
                //如果现在系统没有这个,可以插入到数据库
                $sql = "insert into cdp_vol_task_takeover_info(task_uuid, takeover_standby_agent_uuid, app_takeover_flag,
                            app_consecutive_failure_num,app_fault_detection_interval,agent_failback_standby_ip,takeover_timestamp,
                            takeover_type,agent_heartbeat_failure_time,master_agent_ip_switch_flag,detail)
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                //兼容性处理导入数据
                $sqlParams = array($c['task_uuid'],$c['takeover_standby_agent_uuid'],$c['app_takeover_flag'],
                    $c['app_consecutive_failure_num'],$c['app_fault_detection_interval'],$c['agent_failback_standby_ip'],
                    $c['takeover_timestamp'],$c['takeover_type'],$c['agent_heartbeat_failure_time'],
                    $c['master_agent_ip_switch_flag'],$c['detail']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
	    }

	    $targets = array();
	    $sql = "select target_id from cdp_vol_task_takeover_lun";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $targets[] = $d['target_id'];
	    }

	    //插入cdp_vol_task_takeover_lun表
	    foreach ($cdp_vol_task_takeover_lun as $c){
	        if(!in_array($c['target_id'], $targets)){
	            //如果现在系统没有这个,可以插入到数据库
                $sql = "insert into cdp_vol_task_takeover_lun(target_id, vol_uuid, lun_id,standby_mount_point,standby_vol_uuid)
                        values(?,?,?,?,?)";
                //兼容性处理导入数据
                $sqlParams = array($c['target_id'],$c['vol_uuid'],$c['lun_id'],$c['standby_mount_point'],$c['standby_vol_uuid']);
                $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_takeover_script表
	    foreach ($cdp_vol_task_takeover_script as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_takeover_script(task_uuid, script_uuid, script_type, script_path,exec_type,exec_interval,
                            trigger_fail_num,exec_result,exec_error_code)
                        values(?,?,?,?,?,?,?,?,?)";
	            //兼容性处理导入数据
	            $sqlParams = array($c['task_uuid'],$c['script_uuid'],$c['script_type'],$c['script_path'],$c['exec_type'],$c['exec_interval'],
	                $c['trigger_fail_num'],$c['trigger_fail_num'],$c['exec_result'],$c['exec_error_code']);
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    //插入cdp_vol_task_takeover_target表
	    foreach ($cdp_vol_task_takeover_target as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_takeover_target(task_uuid, target_name, takeover_timestamp)
                        values(?,?,?)";
	            //兼容性处理导入数据
	            $sqlParams = array($c['task_uuid'],$c['target_name'],$c['takeover_timestamp']);
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    //插入cdp_vol_task_vol表
	    foreach ($cdp_vol_task_vol as $c){
	        if(!in_array($c['task_uuid'], $uuids)){
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into cdp_vol_task_vol(task_uuid, vol_uuid, standby_target_vol_uuid,recovery_target_vol_uuid, takeover_failback_target_vol_uuid, 
                        status,takeover_target_mount_point,takeover_standby_real_mount_point,recovery_target_timestamp,recovery_target_disk_uuid)
                        values(?,?,?,?,?,?,?,?,?,?)";
	            //兼容性处理导入数据
	            $sqlParams = array($c['task_uuid'],$c['vol_uuid'],$c['standby_target_vol_uuid'],$c['recovery_target_vol_uuid'],$c['takeover_failback_target_vol_uuid'],
	                $c['status'],$c['takeover_target_mount_point'],$c['takeover_standby_real_mount_point'],$c['recovery_target_timestamp'],$c['recovery_target_disk_uuid']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }

	    $sr_sure_backup = $content['sr_sure_backup'];
	    $sr_sure_backup_instant_vm = $content['sr_sure_backup_instant_vm'];
	    //插入虚拟实验室任务相关
	    foreach ($sr_sure_backup as $c){
            $sql = "insert into sr_sure_backup(task_uuid,virtual_lab_uuid, automatic_verifitied_flag, hypervisor_type, recovery_level, 
                        limit_boot_vm_num, backup_task_name,task_progress,ping_warn_flag,heartbeat_warn_flag,screenshot_warn_flag)
                    select ?, ?, ?, ?, ?, ?, ?, ?,?,?,? 
                    from dual where not exists (select task_uuid from sr_sure_backup where task_uuid = ?)";
            //兼容性处理导入数据
            $c = $this->compatibleBdHistoryTask($c);
            $sqlParams = array($c['task_uuid'], $c['virtual_lab_uuid'], $c['automatic_verifitied_flag'], $c['hypervisor_type'],
                $c['recovery_level'],$c['limit_boot_vm_num'], $c['backup_task_name'],$c['task_progress'],$c['ping_warn_flag'],
                $c['heartbeat_warn_flag'], $c['screenshot_warn_flag'],$c['task_uuid']);
            $result = $result && $this->dbExec($sql, $sqlParams);
	    }

	    foreach ($sr_sure_backup_instant_vm as $c){
	        $sql = "insert into sr_sure_backup_instant_vm(task_uuid,orig_vm_uuid, new_vm_name, new_vm_uuid, timepoint_uuid,timestamp, error_code,
                    target_vcenter_uuid,orig_vm_version,target_host_uuid,start_vm_flag,orig_vm_name,nfs_server_ip,advance_vm_config,extension_info,
                    ping_test_flag,heartbeat_flag,print_screen_flag,max_boot_time,global_uuid,script_pathname,task_status,backup_task_uuid,original_ip)
                    select ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ? 
                    from dual where not exists 
                    (select orig_vm_uuid from sr_sure_backup_instant_vm where task_uuid = ? and orig_vm_uuid=?)";
	        //兼容性处理导入数据
	        $c = $this->compatibleBdHistoryTask($c);
	        $sqlParams = array($c['task_uuid'], $c['orig_vm_uuid'], $c['new_vm_name'], $c['new_vm_uuid'], $c['timepoint_uuid'],$c['timestamp'], $c['error_code'],
	            $c['target_vcenter_uuid'], $c['orig_vm_version'],$c['target_host_uuid'], $c['start_vm_flag'],$c['orig_vm_name'], $c['nfs_server_ip'],
	            $c['advance_vm_config'], $c['extension_info'],$c['ping_test_flag'], $c['heartbeat_flag'],$c['print_screen_flag'], $c['max_boot_time'],
	            $c['global_uuid'], $c['script_pathname'],$c['task_status'], $c['backup_task_uuid'],$c['original_ip'], $c['task_uuid'],$c['orig_vm_uuid']);
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    $this->writeRecLog($value, $result);
	    return $result;
	}
	
	/**
	 * 恢复历史任务
	 */
	private function recoveryHistoryJob($value){
	    /**
	     * 恢复逻辑
	     * bd_history_task表如果有相同history_uuid,不插入,如果没有才插入
	     */
        ini_set('memory_limit', '2048M');
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有历史任务
	    $uuids = array();
	    $sql = "select history_uuid from bd_history_task";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['history_uuid'];
	    }
	    $result = true;

	    $bd_history_task = $content['bd_history_task'];

	    $bd_storage_monitor = $content['bd_storage_monitor'];
	    $sr_sure_backup_report = $content['sr_sure_backup_report'];
	    $cdp_vol_history_task = $content['cdp_vol_history_task'];
	    //插入bd_history_task表
	    foreach ($bd_history_task as $c){
	        if(!in_array($c['history_uuid'], $uuids)){
	            //获取到这个记录的用户,如果记录的用户UUID存在,插入之前的用户UUID,如果不存在,插入操作用户的UUID
	            $useruuid = $this->getAvailableUserUUID($c['user_uuid']);
	            //如果现在系统没有这个,可以插入到数据库
	            $sql = "insert into bd_history_task(task_uuid, task_name, module_type, submodule_type, task_type, current_mode,auto_run_flag, 
                            total_object_size, total_object_write_size, total_object_transport_size, total_object_completed_size, average_speed,
                            start_time, finish_time, user_name, error_code, details, history_uuid, user_uuid,error_detail) 
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
	            //兼容性处理导入数据
	            $c = $this->compatibleBdHistoryTask($c);
	            $sqlParams = array($c['task_uuid'], $c['task_name'], $c['module_type'], $c['submodule_type'], $c['task_type'], $c['current_mode'],
	                $c['auto_run_flag'], $c['total_object_size'], $c['total_object_write_size'], $c['total_object_transport_size'], 
	                $c['total_object_completed_size'], $c['average_speed'],$c['start_time'], $c['finish_time'], $c['user_name'], 
	                $c['error_code'], $c['details'], $c['history_uuid'], $useruuid,$c['error_detail']
	            );
	            $result = $result && $this->dbExec($sql, $sqlParams);
	        }
	    }
	    
	    foreach ($cdp_vol_history_task as $c){
	        $sql = "insert into cdp_vol_history_task(history_uuid,master_agent_uuid, standby_agent_uuid, host_ha_standby_agent_uuid, target_agent_uuid)
                    select  ?, ?, ?, ?, ? from dual where not exists (select history_uuid from cdp_vol_history_task where history_uuid = ?)";
	        //兼容性处理导入数据
	        $c = $this->compatibleBdHistoryTask($c);
	        $sqlParams = array($c['history_uuid'], $c['master_agent_uuid'], $c['standby_agent_uuid'], $c['host_ha_standby_agent_uuid'], $c['target_agent_uuid'],
	           $c['history_uuid']);
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
        $osMigrationHistory = $content['os_migration_history'];

        foreach ($osMigrationHistory as $c){
            $sql = "insert into os_migration_history(task_uuid,task_status, migration_map, reparted_flag, history_uuid)
                    select  ?, ?, ?, ?, ? 
                    from dual where not exists (select history_uuid from os_migration_history where history_uuid = ?)";
            //兼容性处理导入数据
            $c = $this->compatibleBdHistoryTask($c);
            $sqlParams = array($c['task_uuid'], $c['task_status'], $c['migration_map'], $c['reparted_flag'], $c['history_uuid'],
                $c['history_uuid']);
            $result = $result && $this->dbExec($sql, $sqlParams);
        }

	    
	    foreach ($bd_storage_monitor as $c){
	        $sql = "insert into bd_storage_monitor(date,backup_total_size, backup_write_size, copy_total_size, copy_write_size, archive_total_size, 
                        archive_write_size, backup_num, backup_success_num, vm_num, success_vm_num,details,remarks)
                    select  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? from dual where not exists (select date,backup_total_size from bd_storage_monitor where 
                    date = ? and backup_total_size = ?)";
	        //兼容性处理导入数据
	        $c = $this->compatibleBdHistoryTask($c);
	        $sqlParams = array($c['date'], $c['total_backup_size'],$c['backup_write_size'],$c['copy_total_size'],$c['copy_write_size'],$c['archive_total_size'],
                $c['archive_write_size'], $c['backup_num'], $c['backup_success_num'], $c['vm_num'], $c['success_vm_num'],$c['details'],
                $c['remarks'],$c['date'],$c['total_backup_size']);
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    foreach ($sr_sure_backup_report as $c){
            $sql = "insert into sr_sure_backup_report(task_uuid,task_name, virtual_lab_name, vm_name, ping_test, script_path,
                        screen_shot,heartbeat_test,start_time,end_time,extension_info,history_uuid,screen_test) 
                    select  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,? 
                    from dual where not exists (
                        select start_time,end_time from sr_sure_backup_report where task_uuid = ? and history_uuid=? and vm_name = ?)";
            $sqlParams = array($c['task_uuid'], $c['task_name'], $c['virtual_lab_name'], $c['vm_name'], $c['ping_test'], $c['script_path'],
                $c['screen_shot'],$c['heartbeat_test'],$c['start_time'],$c['end_time'],$c['extension_info'],$c['history_uuid'],
                $c['screen_test'],$c['task_uuid'],$c['history_uuid'], $c['vm_name']);
            $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	/**
	 * 得到可用的用户uuid
	 * 如果老用户uuid存在,就返回老的uuid
	 * 如果老用户不存在,就返回当前用户uuid
	 * @param unknown $oldUUID
	 */
	private function getAvailableUserUUID($oldUUID){
	    $sql = "select count(id) as total from bd_user where user_uuid = ?";
	    $data = $this->dbSelect($sql, array($oldUUID));
	    if($data[0]['total'] > 0){
	        return $oldUUID;
	    }else{
	        return Xphp::$_user['useruuid'];
	    }
	}
	
	
	/**
	 * 恢复任务告警
	 */
	private function recoveryTaskAlarm($value){
	    /**
	     * 恢复逻辑
	     * bd_task_alarm表如果有相同history_uuid,不插入,如果没有才插入
	     */
        ini_set('memory_limit', '2048M');
	    $content = $this->getRecoveryFileInfo($value['name']);
	    //获取所有现有任务告警
	    $uuids = array();
	    $sql = "select history_uuid from bd_task_alarm";
	    $data = $this->dbSelect($sql, array(), PDO::FETCH_ASSOC);
	    foreach ($data as $d){
	        $uuids[] = $d['history_uuid'];
	    }
	    $result = true;
	    if(count($data)==0){
            return true;
        }
	    $bd_task_alarm = $content['bd_task_alarm'];
	    //插入bd_task_alarm表
	    foreach ($bd_task_alarm as $c) {
            if (!in_array($c['history_uuid'], $uuids)) {
                //如果现在系统没有这个,可以插入到数据库
                $sql = "insert into bd_task_alarm(alarm_level, description_key, description_param, alarm_time, task_uuid, 
                          task_name,task_type, module_type, submodule_type, node_uuid,node_name, storage_name, user_uuid,user_name, 
                          solved_flag, solved_time, solved_username, email_send_flag, sms_send_flag, error_code, 
                          task_log_path, history_uuid,wechat_send_flag,enterprise_wechat_send_flag) 
                        values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sqlParams = array($c['alarm_level'], $c['description_key'], $c['description_param'], $c['alarm_time'],
                    $c['task_uuid'], $c['task_name'], $c['task_type'], $c['module_type'], $c['submodule_type'],
                    $this->getNewNodeUUID($c['node_uuid']), $c['node_name'], $c['storage_name'], $c['user_uuid'], $c['user_name'],
                    $c['solved_flag'], $c['solved_time'], $c['solved_username'], $c['email_send_flag'], $c['sms_send_flag'],
                    $c['error_code'], $c['task_log_path'], $c['history_uuid'], $c['wechat_send_flag'], $c['enterprise_wechat_send_flag']
                );
                $result = $result && $this->dbExec($sql, $sqlParams);
            }
            $this->writeRecLog($value, $result);
            return $result;
        }
	}
	
	/**
	 * 恢复系统告警
	 */
	private function recoverySystemAlarm($value){
	    /**
	     * 恢复逻辑
	     * bd_system_alarm表直接插入
	     */
        $content = $this->getRecoveryFileInfo($value['name']);
	    $result = true;
	    $bd_system_alarm = $content['bd_system_alarm'];
	    //插入bd_system_alarm表
	    foreach ($bd_system_alarm as $c){
            $sql = "insert into bd_system_alarm(alarm_level, description_key, description_param, alarm_time, solved_flag,
                            solved_time, solved_username, email_send_flag, sms_send_flag, error_code, system_log_path,
                            wechat_send_flag,enterprise_wechat_send_flag) 
                    select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? 
                    from dual where not exists (select system_alarm_id from bd_system_alarm 
                    where description_key = ? and description_param = ? and alarm_time = ?)";
            $sqlParams = array($c['alarm_level'], $c['description_key'], $c['description_param'], $c['alarm_time'],
                $c['solved_flag'], $c['solved_time'],$c['solved_username'], $c['email_send_flag'], $c['sms_send_flag'],
                $c['error_code'], $c['system_log_path'],$c['wechat_send_flag'],$c['enterprise_wechat_send_flag'],
                $c['description_key'], $c['description_param'], $c['alarm_time']
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    $this->writeRecLog($value, $result);
	    return $result;
	}
	
	
	/**
	 * 恢复任务操作日志
	 */
	private function recoveryJobLog($value){
	    /**
	     * 恢复逻辑
	     * bd_task_log表直接插入
	     */
        ini_set('memory_limit', '2048M');
	    $content = $this->getRecoveryFileInfo($value['name']);
	    $result = true;
	    
	    $bd_task_log = $content['bd_task_log'];
	    //插入bd_task_log表
	    foreach ($bd_task_log as $c){
	        $sql = "insert into bd_task_log(task_uuid, task_name, task_type, user_uuid, user_name, agent_uuid,
                agent_name, module_type, submodule_type, error_code, op_time, description_key, description_param, 
                log_level, search_key, running_flag, task_log_path, node_name, storage_name)
                select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? from dual where not exists (select id from bd_task_log where 
                op_time = ? and description_key = ? and description_param = ?);";
	        $sqlParams = array($c['task_uuid'], $c['task_name'], $c['task_type'], $c['user_uuid'],  $c['user_name'], $c['agent_uuid'],
	            $c['agent_name'], $c['module_type'], $c['submodule_type'], $c['error_code'], $c['op_time'], $c['description_key'], $c['description_param'], 
	            $c['log_level'], $c['search_key'], $c['running_flag'], $c['task_log_path'], $c['node_name'], $c['storage_name'],
	            $c['op_time'], $c['description_key'], $c['description_param']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	    }
	    
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	/**
	 * 恢复系统操作日志
	 */
	private function recoverySystemLog($value){
	    /**
	     * 恢复逻辑
	     * bd_system_log表直接插入
	     */
	    
	    $content = $this->getRecoveryFileInfo($value['name']);
	    $result = true;
	    
	    $bd_system_log = $content['bd_system_log'];
	    //插入bd_system_log表
	    foreach ($bd_system_log as $c){
	        $sql = "insert into bd_system_log(user_uuid, user_name, agent_uuid, agent_name, error_code, op_time,
                description_key, description_param, log_level)
                select ?, ?, ?, ?, ?, ?, ?, ?, ? from dual where not exists (select id from bd_system_log where 
                op_time = ? and description_key = ? and description_param = ?);";
	        $sqlParams = array($c['user_uuid'], $c['user_name'], $c['agent_uuid'], $c['agent_name'],  $c['error_code'], $c['op_time'],
	            $c['description_key'], $c['description_param'], $c['log_level'],
	            $c['op_time'], $c['description_key'], $c['description_param']
	        );
	        $result = $result && $this->dbExec($sql, $sqlParams);
	        
	    }
	    
	    $this->writeRecLog($value, $result);
	    
	    return $result;
	}
	
	/**
	 * 兼容性处理bd_running_info表
	 * 从520到6.0新增、修改、删除了字段，兼容后可以用520的
	 * @param unknown $data
	 */
	private function compatibleBdRunningInfo($data){
	    //因为6.0删除了current_vm_uuid字段，通过判断这个字段确认是520还是6.0
	    if(array_key_exists("current_vm_uuid", $data)){
	        //520,处理修改的字段,左边是新的字段名,右边是之前的字段名
	        $data['current_object'] = $data['current_file_name'];
	        $data['total_object_size'] = $data['total_size'];
	        $data['total_object_completed_size'] = $data['current_total_size'];
	        $data['current_object_total_size'] = $data['current_file_size'];
	        $data['current_object_completed_size'] = $data['current_completed_size'];
	        $data['total_object_write_size'] = $data['total_real_size'];
	        $data['current_object_write_size'] = $data['current_real_size'];
	        $data['total_object_transport_size'] = $data['total_transport_size'];
	        $data['current_object_transport_size'] = $data['current_transport_size'];
	        $data['current_object'] = $data['current_file_name'];
	        $data['current_object'] = $data['current_file_name'];
	        
	        //520,处理新增的字段,设置成默认值
	        $data['total_object_valid_size'] = 0;
	        $data['total_object_completed_valid_size'] = 0;
	        $data['current_object_valid_size'] = 0;
	        $data['current_object_completed_valid_size'] = 0;
	    }
	    //如果不是520直接是最新的,直接返回,不需要重复处理
	    
	    return $data;
	}
	
	/**
	 * 兼容性处理bd_history_task表
	 * 从520到6.0新增、修改、删除了字段，兼容后可以用520的
	 * @param unknown $data
	 */
	private function compatibleBdHistoryTask($data){
	    //因为6.0将real_size修改成了total_object_write_size，通过判断这个字段确认是520还是6.0
	    if(array_key_exists("real_size", $data)){
	        //520,处理修改的字段,左边是新的字段名,右边是之前的字段名
	        $data['total_object_size'] = $data['total_size'];
	        $data['total_object_write_size'] = $data['real_size'];
	        $data['total_object_transport_size'] = $data['total_transport_size'];
	        $data['total_object_completed_size'] = $data['total_completed_size'];
	        
	        //处理detail,real_size关键字修改为write_size
	        $data['details'] = str_replace("real_size", "write_size", $data['details']);
	    }
	    //如果不是520直接是最新的,直接返回,不需要重复处理
	    
	    return $data;
	}
	
	/**
	 * 兼容性处理vm_machine_list表
	 * 从520到6.0,real_size修改成了write_size
	 * @param unknown $data
	 */
	private function compatibleVmMachineList($data){
	    if(array_key_exists("real_size", $data)){
	        $data['write_size'] = $data['real_size'];
	    }
	    //如果不是520直接是最新的,直接返回,不需要重复处理
	    return $data;
	}
	
	/**
	 * 兼容性处理db_list表
	 * 从520到6.0,real_size修改成了write_size
	 * @param unknown $data
	 */
	private function compatibleDbList($data){
	    if(array_key_exists("real_size", $data)){
	        $data['write_size'] = $data['real_size'];
	    }
	    //如果不是520直接是最新的,直接返回,不需要重复处理
	    return $data;
	}
	
	/**
	 * 兼容性处理backup_copy_item_list表
	 * 从520到6.0,real_size修改成了write_size
	 * @param unknown $data
	 */
	private function compatibleBackupCopyItemList($data){
	    if(array_key_exists("real_size", $data)){
	        $data['write_size'] = $data['real_size'];
	    }
	    //如果不是520直接是最新的,直接返回,不需要重复处理
	    return $data;
	}
	
	
	//**********************************************************恢复*********************************************************************//
	
}	
?>