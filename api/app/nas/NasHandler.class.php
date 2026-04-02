<?php
/*******************************************
 ** NAS备份,继承文件备份
 **
 ** @author       wuxian@vinchin.com
 ** @date         2022-05-19
 ********************************************/
require_once XPHP_PATH . 'utils/BLLHandler.class.php';
class NasHandler extends BLLHandler
{
    private $opcodeHandler;
    function __construct()
    {
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('NodeOpcode');
    }
    /**
     * 创建备份任务
     * @param {} $params
     */
    public function createFsBackupJob($params)
    {
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $utils = Xphp::instance('Utils');
        $expireDate = $utils->getExpireDays();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_LICENSE_AUTH_INFO_EXPIRED'] , "warning"));
        }
        //public params
        $task_name = $params['taskName'];
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['strategygroupuuid'];
        $this->paramsCheck($task_name);
        $module_type = Xphp::$_config['MODULE_TYPE']['NAS'];

        //租户内检查可用数量是否超过授权个数
        if(!empty($_SESSION['tenantuuid'])){
            $tenantHandler = Xphp::instance('TenantHandler');
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['NAS'], array($params['srcInfo']['nasuuid']), "");
        }
        //组合备份方式完备差备等
        $time_strategy_list = $this->groupBackupTimeList($params['strategyInfo']['time']);
        //组合保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['strategyInfo']['reserve']['reserveInfo']);
        //组合传输策略
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer']);
        //组合存储策略
        $storage_strategy = $this->groupStorageStrategy($params['strategyInfo']['store']['storeInfo']);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo,
            $params['strategyInfo']['time']['type']
        );
        //得到任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['BACKUP'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupNAS($params['srcInfo']['fileInfo'], $params['srcInfo']['nasuuid']);
        $pfMsg['snap_shot_flag'] = $params['highInfo']['snap_shot_flag'] ? 1 : 2; //快照
        $pfMsg['thread_num'] = $params['highInfo']['newstr']['backupThreadNum']; //传输线程数量
        $pfMsg['scan_thread_num'] = $params['highInfo']['newstr']['scanThreadNum']; //扫描线程数量
        $pfMsg['scan_file_num'] = $params['highInfo']['newstr']['scanFileNum']; //扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['highInfo']['newstr']['wildcard_list']); //通配符
        //限速策略
        $pfMsg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['strategyInfo']['speedlimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        $pfMsg['nas_uuid'] = $params['srcInfo']['nasuuid'];
        $pfMsg['file_archive_flag'] = $params['highInfo']['file_archive']; //归档
        $pfMsg['skip_file_alarm_flag'] = $params['highInfo']['skip_file_alarm_flag'] ? 1 : 2; //跳过文件告警开关
        $pfMsg['permission_operate_flag'] = $params['highInfo']['permission_operate_flag'] ? 1 : 2; //文件权限备份
        $pfMsg['skip_file_alarm_min_num'] = $params['highInfo']['skip_file_alarm_min_num']; //跳过文件告警个数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['highInfo']['skip_file_alarm_min_ratio']; //跳过文件告警比例
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['highInfo']['ignore_resource_limiting_flag']);
        //得到安全策略
        $pfMsg['safe_config_strategy'] = $jobHandler->groupSafeConfigStrategy($params['safe_strategy'], $params['highInfo']['node']['storageuuid']);
        $pfMsg['retry_strategy'] =  $jobHandler->groupRetryStrategy($params['retry_strategy']);
        $pfMsg['submodule_type'] = Xphp::$_config['SUBMODULE_TYPE']['NAS'];
        //按代理分组批量创建文件备份任务
        $agentList = $params['srcInfo']['agentList'];
        $nodeHandler = Xphp::instance('NodeHandler');
        if ($pfMsg['node_uuid']) {
            $nodeuuid = $nodeInfo['node_uuid'];
        } else {
            $nodeuuid = $nodeHandler->getMasterNodeUuid();
        }
        if (!empty($agentList)) {
            $msg = json_encode($pfMsg);
            $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
            sleep(1);   //睡一秒
        } else {
            //单个代理创建任务
            $msg = json_encode($pfMsg);
            $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        }


        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 组合备份时间策略
     * @param array $params
     * @return array
     */
    private function groupBackupTimeList($params)
    {
        $msg = array();
        if ('strategy' == $params['type']) {
            //按时间策略备份
            if (!empty($params['timeInfo']['fullInfo'])) {
                //完全策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params['timeInfo']['fullInfo']);
            }
            if (!empty($params['timeInfo']['incrInfo'])) {
                //增量策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['INCREMENTAL'], $params['timeInfo']['incrInfo']);
            }
            if (!empty($params['timeInfo']['diffInfo'])) {
                //差异策略
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL'], $params['timeInfo']['diffInfo']);
            }
            if (!empty($params['timeInfo']['pIncrInfo'])) {
                //永久增量（mode==增量备份）
                $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['INCREMENTAL'], $params['timeInfo']['pIncrInfo']);
            }
        } else if ('oncetime' == $params['type']) {
            //一次性备份
            $strategy = array('startTime' => $params['data']);

            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            //获取一次性备份时间
            $taskCreateTime = strtotime($params['data']);
            if ($systemTime >= $taskCreateTime) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_DB_BACKUP_TIME'], Xphp::$_lang['WEB_DB_BACKUP_TIME_TIPS'], 'warning'));
            }

            $strategy['type'] = Xphp::$_config['STRATEGY_TYPE']['ONCE'];
            $msg[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $strategy);
        } else if ('manual' == $params['type']) {
            $msg = [];
        }
        return $msg;
    }
    /**
     * 组合保留策略
     * @param array $reserve    保留策略信息
     *  @param  int type        类型
     *  @param  int value       值
     *  @param  bool archive    归档标记
     * @return array
     */
    private function groupReserverStrategy($reserve)
    {
        $this->paramsCheck($reserve);
        $strArr = array(
            'enable_flag' => $reserve['enable_flag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'strategy_mode' => intval($reserve['strategy_mode']) ? intval($reserve['strategy_mode']) : 0,
            'strategy_type' => intval($reserve['type']),
            'number' => intval($reserve['value']),
            'auto_archive_flag' => $reserve['archive'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
        );
        return $strArr;
    }
    /**
     * 组合存储策略
     * @param array $storage  存储策略信息
     *  @param  int blocksize    数据块大小
     *  @param  bool compress    压缩
     *  @param  bool deduplication  重删
     *  @param  bool  encrypt     加密
     * @return array
     */
    private function groupStorageStrategy($storage)
    {
        $strArr = array(
            'deduplication_flag' => $storage['deduplication'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'blocksize' => intval($storage['blocksize']) ?? '',
            'encrypt_flag' => $storage['encrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'password_auto_flag' => $storage['password_auto_flag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'password' => $storage['password'],
            'compress_flag' => $storage['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_method' => $storage['compress_method'] ? $storage['compress_method'] : 0,
            'encrypt_method' => intval($storage['encrypt_method']),
        );
        return $strArr;
    }
    /**
     * 得到备份的文件列表
     * @param unknown $filelists
     */
    private function groupBackupNAS($filelists, $nasuuid)
    {
        $fileList = array();
        foreach ($filelists as $file) {
            $fileList[] = array(
                'path_type' => strval($file[0]),    //文件类型
                'path_name' => htmlspecialchars_decode($file[1]),             //文件路径
                'agent_uuid' => $nasuuid, //nasuuid
                'code_type' => $file[2], //编码类型
            );
        }
        return $fileList;
    }
    /**
     * 得到备份的通配符相关信息
     * @param unknown $wildcard
     */
    private function getWildCardList($wildcard)
    {
        $data = array();
        foreach ($wildcard as $w) {
            // if($w[2]!=0){
            $data[] = array(
                'agent_uuid' => $w[0],
                'wildcard' => $w[1],
                'wildcard_mode' => $w[2],
            );
            // }
        }
        return $data;
    }

    /**
     * 得到nas备份任务名
     * @param unknown $params
     */
    public function getNasBackupTaskName($params)
    {
        if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
            return $this->getValidTaskName(Xphp::$_lang['UI_NAS_BACKUP_TASK']);
        } else {
            return $this->getValidTaskName('NAS Backup Job');
        }

    }

    /**
     * 得到nas恢复任务名
     * @param unknown $params
     */
    public function getNasRecoverTaskName($params)
    {
        // return $this->getValidTaskName('nas恢复任务');
        if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
            return $this->getValidTaskName(Xphp::$_lang['UI_NAS_RESTORE_TASK']);
        } else {
            return $this->getValidTaskName('NAS Restore Job');
        }
    }

    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    private function getValidTaskName($taskName)
    {
        $oldTaskName = $taskName;
        for($i=1; $i<1000; $i++){
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if(empty($data) && empty($data1)){
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }

    /**
     * 获取文件备份
     * @return string
     */
    public function getNasBackupTree($params = array())
    {
        $search = $params['content'];
        $timepointuuid = $params['timepointuuid'];
        $hostFlag = $params['hostFlag'];
        $sql = "select nsr.nas_name,nsr.nas_nickname,nsr.nas_uuid,nsr.share_path,nsr.ip,nsr.user_name,nsr.password,nsr.nas_status,nsr.mount_params,nsr.nas_version, nsr.snapshot_flag, nsr.detail,
        nsr.vendor,nsr.port,nsr.nas_type,nsr.authorization_status,nml.agent_uuid,nml.mount_point from nas_storage_resource nsr left join nas_mount_list nml on nsr.nas_uuid = nml.nas_uuid";
        $utils = Xphp::instance('Utils');
        $concatSql = " where ";
        if ($utils->v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的资源列表
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到分配的资源
            $resourceUuidSql = $utils->v1_auth_get_source_by_type(57, 'nsr.nas_uuid');
            $sqlNew = $concatSql . " ({$resourceUuidSql}) ";
            $sql .= $sqlNew;
            $concatSql = " and ";
        }
        if (!empty($search)) {
            $sql .= $concatSql . "(nsr.nas_nickname like '%" . $search . "%' or nsr.share_path like '%" . $search . "%' or nsr.ip like '%" . $search . "%')";
        }
        //恢复页面选择nas设备 只显示当前勾选的备份数据的节点挂载过的nas设备，防止用户勾选未挂载的设备导致恢复失败
        $nasuuidList = array();
        if (!empty($timepointuuid)) {
            //判断real_node_uuid是否是空
            $realNodeUuidSql = "select real_node_uuid from bd_backup_timepoint where timepoint_uuid = ?";
            $realNodeUuid = $this->dbSelect($realNodeUuidSql, array($timepointuuid));
            if (!empty($realNodeUuid[0]['real_node_uuid'])) {
                $sqlList = "select nml.nas_uuid,bbt.real_node_uuid from bd_backup_timepoint bbt left join nas_mount_list nml
                on bbt.real_node_uuid = nml.node_uuid where bbt.timepoint_uuid = ?";
            } else {
                $sqlList = "select nml.nas_uuid from bd_backup_timepoint bbt left join bd_storage_resource bsr
                on bbt.storage_uuid = bsr.storage_uuid left join nas_mount_list nml
                on bsr.node_uuid = nml.node_uuid where  bbt.timepoint_uuid = ?";
            }
            $nasdata = $this->dbSelect($sqlList, array($timepointuuid));
            foreach ($nasdata as $n) {
                $nasuuidList[] = $n['nas_uuid'];
            }
        }
        $data = $this->dbSelect($sql);
        $nodes = array();
        $nas = array();//nas设备
        $groupVendor = array();//厂商
        if (!empty($data)) {
            foreach ($data as $d) {
                $authflag = $this->checkSystemAuth();
                if ($authflag != 1 && $d['nas_status'] != 1) {
                    $authorization_status = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')';
                } else if ($authflag == 1 && $d['nas_status'] != 1) {
                    $authorization_status = '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')';
                } else if ($authflag != 1 && $d['nas_status'] == 1) {
                    $authorization_status = '(' . Xphp::$_lang['WEB_SYSTEM_LISENCE_UNAUTHORIZED'] . ')';
                } else {
                    $authorization_status = '';
                }
                if ($hostFlag) { //恢复页面nas设备树
                    $chkDisabled = $authorization_status == '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')' ? true : false;
                    if (!in_array($d['nas_uuid'], $nasuuidList) && $hostFlag) { // 不是同样的nodeuuid的显示成禁用状态
                        // continue;
                        $chkDisabled = true;
                        // 有离线不显示未挂载
                        if ($authorization_status != '(' . Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'] . ')') {
                            $authorization_status .= '('. Xphp::$_lang['WEB_STORAGE_STATUS_UNMOUNT'] .')';
                        }
                    }
                } else {
                    $chkDisabled = $authorization_status == '' ? false : true;
                }
                //图标区分不同类型
                if ($d['nas_type'] == 6) {
                    $icon = "./img/nas/nfs.png";
                } else if ($d['nas_type'] == 7) {
                    $icon = "./img/nas/cifs.png";
                }
                if ($d['nas_nickname'] == $d['ip']) {
                    $name = $d['ip'] . '(' . $d['share_path'] . ')';
                } else {
                    $name = $d['ip'] . '(' . $d['nas_nickname'] . ')';
                }
                if (!in_array('vendor_' . $d['vendor'], $groupVendor)) {
                    array_push($groupVendor, 'vendor_' . $d['vendor']);
                    $nodes[] = array(
                        "id" => 'vendor_' . $d['vendor'],
                        "pId" => 0,
                        "name" => $this->getVendorDes($d['vendor']),
                        "isParent" => true,
                        "icon" => $this->getVendorIcon($d['vendor']),
                        "eventtype" => "vendor",
                        "nocheck" => true,
                    );
                }
                if (!in_array($d['nas_uuid'], $nas)) {
                    array_push($nas, $d['nas_uuid']);
                    $nodes[] = array(
                        "id" => $d['nas_uuid'],
                        "pId" => 'vendor_' . $d['vendor'],
                        "name" => htmlspecialchars_decode($authorization_status . $name),
                        "title" => $d['ip'],
                        "isParent" => false,
                        "uuid" => $d['nas_uuid'],
                        "agentuuid" => $d['agent_uuid'],
                        "nocheck" => false,
                        "path" => $d['mount_point'],
                        "sharepath" => $d['share_path'],
                        // "type" => 2,
                        "icon" => $icon,
                        "eventtype" => "nas",
                        // "ostype" => 'windows',
                        "chkDisabled" => $chkDisabled,
                        "authorization_status" => $this->checkSystemAuth(),
                        'snapshot_flag' => $d['snapshot_flag'],
                        'detail' => json_decode($d['detail'], true),
                        'vendor' => $d['vendor'],
                    );
                }
            }
        }
        return json_encode($nodes);
    }

     /**
     * 获取厂商描述
     * @param int $type 厂商类型
     * @return boolean
     */
    public function getVendorDes($type)
    {
        $des = '';
        switch (intval($type)) {
            case 0:
                $des = '其他';
                break;
            case 1:
                $des = 'HUAWEI OceanStor Dorado';
                break;
            default:
                $des = '其他';
                break;
        }
        return $des;
    }

    /**
     * 获取厂商icon
     * @param int $type 厂商类型
     * @return boolean
     */
    public function getVendorIcon($type)
    {
        $icon = '';
        switch (intval($type)) {
            case 0:
                $icon = './img/s3/other-cloud.svg';
                break;
            case 1:
                $icon = './img/s3/huawei.svg';
                break;
            default:
                $icon = './img/s3/other-cloud.svg';
                break;
        }
        return $icon;
    }

    /**
     * 根据文件类型判断是否是父节点
     * @param int $type 文件类型
     * @return boolean
     */
    public function getBoolType($type)
    {
        $bool = '';
        switch (intval($type)) {
            case 0:
            case 1:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
                $bool = false;
                break;
            case 2:
            case 3:
                $bool = true;
                break;
        }
        return $bool;
    }

    /**
     * 得到NAS列表
     * @param array $params
     *      start  开始位置
     *      limit  查找个数
     *      searchFileName 从哪个文件名开始查找
     *      dir            进入目录的目录名
     * @return array
     */
    public function getNasDir($params)
    {
        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
//         if(count($params) != 7){
//             exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_OPHANDLER_PARAMS_NULL'], 'warning'));
//         }
        $start = intval($params['start']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $pid = $params['pid'];          //父节点ID
        $agentUUID = $params['agentuuid'];
        $code_type = 2;
        if (!empty($params['code_type'])) {
            $code_type = $params['code_type']; //编码类型
        }
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $nodeInfo = $this->getMountNode($params['nasuuid']);
        //消息发往各个节点，成功就返回  失败就继续循环
        $sendFlag = false; //发送消息到后台标志
        foreach ($nodeInfo as $info) {
            $msg = array(
                'agent_uuid' => $info['agent_uuid'],
                'search_index' => $start,
                'limit_count' => $limit,
                'search_file_name' => $searchFileName,
                'dir_path' => $dir,
                'code_type' => $code_type,
            );
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodeStatus = $nodeHandler->getNodeAllStatus($info['node_uuid']);
            if ($nodeStatus['flag'] && !$sendFlag) {
                //如果节点状态正常
                $mbResult = $this->mbNodeMsg($opName, $info['node_uuid'], json_encode($msg), true);
                if ($mbResult['result']) {
                    $sendFlag = true;
                }
            }
        }
        $mbResult['msg']['pid'] = $pid;
        $mbResult['msg']['agentuuid'] = $agentUUID;
        return $mbResult;
    }

    // 代理端文件子树
    public function getNasSonTree($params)
    {
        $allresult = $this->getNasDir($params);
        $editFlag = $params['editFlag'];    //修改标记
        $agentuuid = $params['agentuuid'];
        $taskuuid = $params['taskuuid'];
        $nasuuid = $params['nasuuid'];
        $filelist = array();
        $fileNodes = array();
        //获取当前代理端文件备份路径列表
        $newParent = false;
        if ($editFlag) {
            $sql = "select path_name, path_type from fs_path_list where task_uuid = ? and agent_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid, $nasuuid));
            if (!empty($data)) {
                $onceflag = true; //根目录只加载一次
                foreach ($data as $d) {
                    //判断是不是/,如果是并且nasuuid不为空，需新增的用于支持全选的父节点
                    if ($d["path_name"] == "/" && !empty($nasuuid) && $onceflag) {
                        $newParent = true; //用于判断新增的父节点下的子节点是否选中
                        $onceflag = false;
                        $fileNodes[] = array(
                            "id" => $params["dir"],
                            "pId" => 0,
                            "name" => $params['sharepath'],
                            "title" => $params['sharepath'],
                            "isParent" => true,
                            "open" => true,
                            "uuid" => $params['nasuuid'],
                            "nocheck" => false,
                            "type" => 2, //1文件 2 文件夹 3 磁盘
                            "icon" => "./img/fs/wenjianjia.png",
                            "iconOpen" => "./img/fs/wenjianjiaopen.png",
                            "iconClose" => "./img/fs/wenjianjia.png",
                            "filepath" => $params["dir"],
                            "more" => false,
                            "checked" => true,
                            "parentFlag" => true,
                        );
                    } else if ($d["path_name"] != "/" && !empty($nasuuid) && $onceflag) {
                        $onceflag = false;
                        $fileNodes[] = array(
                            "id" => $params["dir"],
                            "pId" => 0,
                            "name" => $params['sharepath'],
                            "title" => $params['sharepath'],
                            "isParent" => true,
                            "open" => true,
                            "uuid" => $params['nasuuid'],
                            "nocheck" => false,
                            "type" => 2, //1文件 2 文件夹 3 磁盘
                            "icon" => "./img/fs/wenjianjia.png",
                            "iconOpen" => "./img/fs/wenjianjiaopen.png",
                            "iconClose" => "./img/fs/wenjianjia.png",
                            "filepath" => $params["dir"],
                            "more" => false,
                            "checked" => true,
                            "halfCheck" => true,
                            "parentFlag" => true,
                        );
                    }
                    if ($d['path_type'] == 1 || $d['path_type'] == 2) {
                        //截取文件信息
                        $info = explode('/', $d['path_name']);
                        $path = "";
                        //获取已选择的文件信息列表
                        foreach ($info as $key => $i) {
                            if ($key == count($info) - 1) {
                                if ($d['path_type'] == 1) {
                                    $path .= $i;
                                } else {
                                    continue;
                                }
                            } else {
                                $path .= $i . "/";
                            }
                            if (!in_array($path, $filelist)) {
                                $filelist[] = $path;
                            }
                        }
                    } else {
                        if (!in_array($d['path_name'], $filelist)) {
                            $filelist[] = $d['path_name'];
                        }
                    }
                }
            } else { //修改切换节点
                $fileNodes[] = array(
                    "id" => $params["dir"],
                    "pId" => 0,
                    "name" => $params['sharepath'],
                    "title" => $params['sharepath'],
                    "isParent" => true,
                    "open" => true,
                    "uuid" => $params['nasuuid'],
                    "nocheck" => false,
                    "type" => 2, //1文件 2 文件夹 3 磁盘
                    "icon" => "./img/fs/wenjianjia.png",
                    "iconOpen" => "./img/fs/wenjianjiaopen.png",
                    "iconClose" => "./img/fs/wenjianjia.png",
                    "filepath" => $params["dir"],
                    "more" => false,
                    "checked" => false,
                    "halfCheck" => true,
                    "parentFlag" => true,
                );
            }
        }
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($filelist as $l) {
            $index = strripos($l, "/");
            //文件夹
            if ($index == strlen($l) - 1) {
                $str = substr($l, 0, strripos($l, "/"));
                $str = substr($str, 0, strripos($str, "/"));
            } else {
                //文件
                $str = substr($l, 0, strripos($l, "/"));
            }
            $str .= "/";
            if ($str == "/") {
                $info[] = $l;
            }
        }
        $result = $allresult['result'];
        $NodeOpcode = Xphp::instance('NodeOpcode');
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $operate = $NodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $allresult['errorCode']);
        }
        $result = $allresult['msg'];
        $list = array(
            "re" => true,                                   //成功标志,方面前端统一处理
            "finishFlag" => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            "searchIndex" => $result['current_next_index'],   //未完成时,下一个开始位置
            "searchFilename" => $result['search_file_name'],  //未完成时,下一个开始名字
            "root_dir_check" => $newParent,
        );
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            if ($newParent) { //新增的父节点下的子节点都选中
                $checked = true;
            } else {
                $checked = false;
            }

            $open = false;
            //检查节点是否在修改列表里
            $str = explode($nasuuid, $d['item_path']);
            if (in_array($str[1], $filelist)) {
                $checked = true;
                //排除$info
                foreach ($filelist as $key => $file) {
                    if ($file == $str[1]) {
                        unset($filelist[$key]);
                    }
                }
                //排除$info
                foreach ($info as $key => $file) {
                    if ($file == $str[1]) {
                        unset($info[$key]);
                    }
                }
            }
            //判判断$list存在父节点是c/1/2
            $flag = false;
            foreach ($filelist as $l) {
                if (!is_bool(strpos($l, $str[1]))) {
                    $flag = true;
                    if ($d['item_type'] != 1) {
                        //文件夹
                        $open = true;
                    }
                }
            }
            if (!empty($params['dir'])) {
                $fileNodes[] = array(
                    "id" => $d['item_path'],
                    "pId" => $result['pid'] == "" ? 0 : $result['pid'],
                    "name" => $filename,      //文件名或者磁盘名
                    "title" => $filename,
                    "isParent" => $this->getBoolType($d['item_type']),
                    "open" => $open,
                    "uuid" => $params['nasuuid'],
                    "agentuuid" => $result['agentuuid'],
                    "nocheck" => false,
                    "type" => $d['item_type'], //1文件 2 文件夹 3 磁盘
                    "icon" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjia.png",
                    "iconOpen" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjiaopen.png",
                    "iconClose" => $d['item_type'] == 1 ? "./img/fs/wenjian.png" : "./img/fs/wenjianjia.png",
                    "filepath" => $d['item_path'],
                    "more" => false,
                    "groupuuid" => $result['groupUUID'],
                    "checked" => $checked,
                    "code_type" => intval($d['code_type']),
                );
            }
            //判断根节点下是否展开子节点
            if ($checked && $d['item_type'] != 1) {
                //判判断$list存在父节点是c/1/2
                $flag = false;
                foreach ($filelist as $l) {
                    if (!is_bool(strpos($l, $str[1]))) {
                        $flag = true;
                    }
                }
                if (!$flag)
                    continue;
                //获取需要展开的节点和加载更多节点
                $paramsSon = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $filename,
                    'dir' => $d['item_path'],
                    'agentuuid' => $agentuuid,
                    'pid' => $d['item_path'],
                    'nasuuid' => $nasuuid

                );
                //展开子节点
                $sonList = $this->getOnLoadTree($filelist, $paramsSon, $d['item_path'], $nasuuid);
                $fileNodes = array_merge($fileNodes, $sonList);
            }

        }
        if (intval($result['is_search_finish']) == Xphp::$_config['FLAG']['UNSET']) {
            // 如果还没有显示完全,添加显示更多项
            if (count($info) != 0) {
                //递归加载更多
                $data = array(
                    'start' => $result['current_next_index'],
                    'limit' => 40,
                    'filename' => $result['search_file_name'],
                    'dir' => $params['dir'],
                    'agentuuid' => $params['agentuuid'],
                    'pid' => $params['dir'],
                    'nasuuid' => $nasuuid
                );
                $moreNodes = $this->getMoreData($filelist, $data, $nasuuid);
                $fileNodes = array_merge($fileNodes, $moreNodes);
            } else {
                $more = array(
                    // "id" => $pid==0?0:$pid.'/',
                    "pId" => $result['pid'],
                    "name" => Xphp::$_lang['WEB_FILE_MORE'],
                    "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                    // "isParent" => false,
                    // "open" => false,
                    "uuid" => $params['nasuuid'],
                    "agentuuid" => $result['agentuuid'],
                    "nocheck" => true,
                    "more" => true,
                    "next_index" => $result['current_next_index'],       //从哪个位置开始加载
                    "search_file_name" => $result['search_file_name'],          //从哪个目录开始加载
                    "dir_path" => $params['dir'],
                    "groupuuid" => $result['groupUUID'],                                //当前目录名
                    "filepath" => "",
                    // "filepath" =>$result['pid'].$result['search_file_name'],

                );
                $fileNodes[] = $more;
            }
        }
        $list['fileNodes'] = $fileNodes;

        return json_encode($list);
    }

    /**
     * 递归获取加载子节点
     * @param array $list
     * @param unknown $params
     * @param unknown $node
     * @return array|unknown[]
     */
    private function getOnLoadTree($list, $params, $path, $nasuuid)
    {
        $fileNodes = array();
        //获取子节点
        $data = $this->getNasSonTree($params);
        $data = json_decode($data, true);
        $fileData = $data['fileNodes'];
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($list as $l) {
            $index = strripos($l, "/");
            //文件夹
            if ($index == strlen($l) - 1) {
                $str = substr($l, 0, strripos($l, "/"));
                $str = substr($str, 0, strripos($str, "/"));
            } else {
                //文件
                $str = substr($l, 0, strripos($l, "/"));
            }
            $str .= "/";
            $pathstr = explode($nasuuid, $path);
            if ($str == $pathstr[1]) {
                $info[] = $l;
            }
        }
        //获取展开子节点
        foreach ($fileData as $key => $d) {
            $checked = false;
            $str = explode($nasuuid, $d['filepath']);
            if (in_array($str[1], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$list
                foreach ($list as $key => $file) {
                    if ($file == $str[1]) {
                        unset($list[$key]);
                    }
                }
                //排除$info
                foreach ($info as $key => $i) {
                    if ($i == $str[1]) {
                        unset($info[$key]);
                    }
                }

            }
            if (count($info) != 0 && $d['more'])
                continue;
            //判判断$list存在父节点是c/1/2
            $flag = false;
            foreach ($list as $l) {
                if (!is_bool(strpos($l, $str[1]))) {
                    $flag = true;
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            if ($checked && $d['type'] != 1) {
                if (!$flag)
                    continue;
                $data = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'agentuuid' => $params['agentuuid'],
                    'pid' => $d['filepath'],
                    'nasuuid' => $nasuuid,
                    'code_type' => $d['code_type'],
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['filepath'], $nasuuid);
                $fileNodes = array_merge($fileNodes, $sonList);
            }

        }
        //获取加载更多
        if (count($info) != 0 && $fileData[count($fileData) - 1]['more']) {
            //递归加载更多
            $data = array(
                'start' => $fileData[count($fileData) - 1]['next_index'],
                'limit' => 40,
                'filename' => $fileData[count($fileData) - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agentuuid' => $params['agentuuid'],
                'pid' => $params['dir'],
                'nasuuid' => $nasuuid
            );
            $moreNodes = $this->getMoreData($list, $data, $nasuuid);
            $fileNodes = array_merge($fileNodes, $moreNodes);

        }

        return $fileNodes;
    }


    /**
     * 递归获取加载更多
     * @param array $list
     * @param unknown $params
     * @return array
     */
    private function getMoreData($list, $params, $nasuuid)
    {
        $fileNodes = array();
        $moreData = //获取加载更多的节点
            $data = $this->getNasSonTree($params);
        $data = json_decode($data, true);
        $moreData = $data['fileNodes'];
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($list as $l) {
            $index = strripos($l, "/");
            //文件夹
            if ($index == strlen($l) - 1) {
                $str = substr($l, 0, strripos($l, "/"));
                $str = substr($str, 0, strripos($str, "/"));
            } else {
                //文件
                $str = substr($l, 0, strripos($l, "/"));
            }
            $str .= "/";
            $pathstr = explode($nasuuid, $params['dir']);
            if ($str == $pathstr[1]) {
                $info[] = $l;
            }
        }
        foreach ($moreData as $key => $d) {
            $checked = false;
            $str = explode($nasuuid, $d['filepath']);
            if (in_array($str[1], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$info
                foreach ($list as $key => $i) {
                    if ($i == $str[1]) {
                        unset($list[$key]);
                    }
                }

                foreach ($info as $key => $i) {
                    if ($i == $str[1]) {
                        unset($info[$key]);
                    }
                }

            }
            if (count($info) != 0 && $d['more'])
                continue;
            //判判断$list存在父节点是c/1/2
            $flag = false;
            foreach ($list as $l) {
                if (!is_bool(strpos($l, $str[1]))) {
                    $flag = true;
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            if ($checked && $d['type'] != 1) {
                if (!$flag)
                    continue;
                $data = array(
                    'start' => 0,
                    'limit' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'agentuuid' => $params['agentuuid'],
                    'pid' => $d['filepath'],
                    'nasuuid' => $nasuuid
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['filepath'], $nasuuid);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }
        $count = count($moreData);
        if ($moreData[$count - 1]['more'] && count($info) != 0) {
            //加载更多
            $data = array(
                'start' => $moreData[$count - 1]['next_index'],   //依次累加
                'limit' => 40,
                'filename' => $moreData[$count - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agentuuid' => $params['agentuuid'],
                'groupuuid' => $params['groupuuid'],
                'pid' => $params['dir'],
                'nasuuid' => $nasuuid
            );
            $moreNodes = $this->getMoreData($list, $data, $nasuuid);
            $fileNodes = array_merge($fileNodes, $moreNodes);

        }

        return $fileNodes;
    }



    /**
     * 得到nas设备管理列表信息
     * @param unknown $params
     * @return string
     */

    public function getNasInfo($params)
    {
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];
        $search = $params['search']['name'];
        // Xphp::$_config['NULLSPACE'];
        $sql = 'select nsr.id,nsr.nas_nickname,nsr.share_path,nsr.nas_create_time,nsr.nas_uuid,nsr.ip,nsr.user_name,
       nsr.password,nsr.nas_status,nsr.mount_params,nsr.nas_version,nsr.port,nsr.nas_type,nsr.authorization_status,
       nsr.permission_flag,bu.user_name as bu_username from nas_storage_resource nsr left join bd_user bu on bu.user_uuid = nsr.user_uuid';
        $sqlcount = "select count(nas_uuid) as total from nas_storage_resource";
        //userLevel是1 2 3就全部显示
        $concatSql = ' and';
        if (!in_array($_SESSION['userLevel'],[1,2,3])) {
            $resourceHandler = Xphp::instance('ResourceHandler');
            $uuid = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], 57);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                $records = array("data" => array());
                $records["draw"] = $params['draw'];
                $records["recordsTotal"] = 0;
                $records["recordsFiltered"] = 0;
                return json_encode($records);
            } else {
                // $uuidArr 是一个一维数组  organization_uuid in
                $nasUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " where nsr.nas_uuid in ($nasUuidsIn)";
                $sqlcount .= " where nas_uuid in ($nasUuidsIn)";
            }
        } else {
            $concatSql = ' where';
        }
        if (!empty($search)) {
            $sql .= $concatSql ." nsr.ip like '%" . $search . "%' or nsr.share_path like '%" . $search . "%' ";
            $sqlcount .= $concatSql . " ip like '%" . $search . "%' or share_path like '%" . $search . "%' ";
        }
        //表头排序
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array(
            '',
            'nsr.ip',
            'nsr.share_path',
            'nsr.nas_nickname',
            'nsr.nas_type',
            'nsr.nas_create_time',
            'nsr.nas_status',
            'nsr.authorization_status',
            ''
        );
        $sql .= " order by  $sortArr[$sortColumn]  $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, array($start, $length));
        $count = $this->dbSelect($sqlcount, array());
        $total = intval($count[0]['total']);
        $records = array("data" => array());
        foreach ($data as $d) {
            // 拥有者
            $owner = $this->getNasUuidName($d['nas_uuid'], $d['bu_username'] ?? '--');
            if ('admin' == $owner && $_SESSION['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                $owner = 'sysadmin';
            }
            // 创建者
            $creator = $d['bu_username'] ?? '--';
            if ('admin' == $creator && $_SESSION['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                $creator = 'sysadmin';
            }
            $records["data"][] = array(
                '<input type="checkbox" value="' . $d['nas_uuid'] . '">',
                $d['ip'],
                $d['share_path'],
                $d['nas_nickname'],
                intval($d['nas_type']) == 6 ? 'NFS' : 'CIFS',
                $d['nas_create_time'],
                intval($d['nas_status']),
                $this->getAuthorizationDes($this->checkSystemAuth()),
                $creator,//创建者
                $owner,//所有者
                array(1, 2),    //1 挂载  2解挂  3授权
                $this->getNasDetails($d),
            );
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;
        return json_encode($records);
    }

    /**
     * 检测系统授权状态
     * @return int 授权状态
     */
    public function checkSystemAuth()
    {
        $authflag = 2;
        $systemHandler = Xphp::instance('SystemHandler');
        $status = $systemHandler->getSystemAuthorizationStatus();
        if ($status == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            $authflag = 1;
        }
        return $authflag;
    }

    /**
     * 授权状态
     * @param int $onlineFlag
     * @param int $deployFlag
     */
    public function getAuthorizationDes($authorization_status)
    {
        if ($authorization_status == 1) {
            $authorization_des = Xphp::$_lang['WEB_SYSTEM_LISENCE_AUTHORIZED'];
        } else if ($authorization_status == 2) {
            $authorization_des = Xphp::$_lang['WEB_SYSTEM_LISENCE_UNAUTHORIZED'];
        }

        return $authorization_des;
    }

    /**
     * 组装修改客户端需要显示信息
     * @param unknown $d
     * @return number[]|unknown[]|mixed[]
     */
    private function getNasDetails($d)
    {
        $mount_list = array();
        $mount_list_name = array();
        $sql = 'select nml.node_uuid, bn.ip from nas_mount_list nml,bd_node bn where nml.node_uuid = bn.node_uuid and nas_uuid = ?';
        $data = $this->dbSelect($sql, array($d['nas_uuid']));
        foreach ($data as $nodeuuid) {
            $mount_list[] = $nodeuuid['node_uuid'];
            $mount_list_name[] = $nodeuuid['ip'];
        }
        $info = array(
            'nas_nickname' => $d['nas_nickname'],
            'share_path' => $d['share_path'],
            'nas_uuid' => $d['nas_uuid'],
            'ip' => $d['ip'],
            'user_name' => $d['user_name'],
            'password' => $d['password'],
            'nas_status' => $this->getNasStatusDes(intval($d['nas_status'])),
            'mount_params' => $d['mount_params'],
            'nas_version' => $d['nas_version'],
            'port' => intval($d['port']),
            'nas_type' => intval($d['nas_type']),
            'authorization_status' => $this->checkSystemAuth(),
            'mount_list' => $mount_list,
            'mount_list_name' => $mount_list_name,
            'permission_flag' => $d['permission_flag'] == 1 ? Xphp::$_lang['UI_NAS_MANAGE_WRITE_AND_READ'] : Xphp::$_lang['UI_NAS_MANAGE_ONLY_READ'],
        );
        return $info;
    }

    /**
     * nas设备状态
     * @param int $onlineFlag
     * @param int $deployFlag
     */
    public function getNasStatusDes($onlineFlag)
    {
        // 1在线 2异常 3离线
        if (Xphp::$_config['NAS_MOUNT_STATUS']['NS_NAS_MOUNT_UNKWON'] == $onlineFlag) {
            $des = Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'];
        } else if (Xphp::$_config['NAS_MOUNT_STATUS']['NS_NAS_MOUNT_NORMAL'] == $onlineFlag) {
            $des = Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'];
        } else if (Xphp::$_config['NAS_MOUNT_STATUS']['NS_NAS_MOUNT_ABNORMAL'] == $onlineFlag) {
            $des = Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'];
        } else if (Xphp::$_config['NAS_MOUNT_STATUS']['NS_NAS_MOUNT_ERROR'] == $onlineFlag) {
            $des = Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'];
        }

        return $des;
    }


    /**
     * 授权--获取所有ip
     * @param string $authModule
     */
    public function getLicenseIp($params)
    {
        $start = intval($params['start']);
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $draw = $params['draw'];
        $sortArr = array(
            '',
            'ip',
            'authorization_status',
        );
        $sql = "select distinct ip,authorization_status from nas_storage_resource";
        if (!in_array($_SESSION['userLevel'],[1,2,3])) {
            $resourceHandler = Xphp::instance('ResourceHandler');
            $uuid = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], 57);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                // 没得 返回空
                $records = array("data" => array());
                $records["draw"] = $draw;
                $records["recordsTotal"] = 0;
                $records["recordsFiltered"] = 0;
                return json_encode($records);
            } else {
                // $uuidArr 是一个一维数组  organization_uuid in
                $nasUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " where nas_uuid in ($nasUuidsIn)";
            }
        }
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        
        
        
        
        
        
        $sqlParams = array($start, $length);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = count($data);
        $records = array("data" => array());
        foreach ($data as $d) {
            $records["data"][] = array(
                '<input type="checkbox" value="' . $d['ip'] . '">',
                $d['ip'],
                $this->getAuthorizationDes($this->checkSystemAuth()),
                "info" => $this->getNasLisenceInfo(),
            );
        }
        $records["draw"] = $params['draw'];
        ;
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;
        return json_encode($records);
    }
    /**
     * 得到nas授权信息
     * @param unknown $params
     * @return string
     */
    public function getNasLisenceInfo()
    {
        $systemHandler = Xphp::instance('SystemHandler');
        $nasInfo = $systemHandler->getOneModuleLisenceInfo('nas');
        $licenseInfo = json_decode($systemHandler->getSystemLicenseType(), true);
        $info = array(
            'nas' => $nasInfo,
            'licensetype' => $licenseInfo['licensetype']
        );
        return $info;
    }


    /**
     * 添加nas设备
     * @param unknown $params
     * @return string
     */
    public function addNasDevice($params)
    {
        $tenantHandler = Xphp::instance('TenantHandler');
        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
        //租户内部 添加ip已经授权的nas时，授权不足不允许添加
        if(!empty($_SESSION['tenantuuid']) && $settings['auth_way'] ==2){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $systemHandler = Xphp::instance('SystemHandler');
            $nasUuids = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], 57);
            $nasUuids = array_column($nasUuids, 'resource_uuid');
            $nasUuids = "'" . implode("','", $nasUuids) . "'";
            $ip = $params['info']['ip'];
            $ipData = $this->dbSelect("select distinct ip from nas_storage_resource where nas_status = 1 and ip = '" . $ip . "'
            and nas_uuid in ($nasUuids)");
            if(empty($ipData)) {//是ip已经授权的nas
                $nasAuthInfo = $systemHandler->getOneModuleLisenceInfo('nas');
                if ($nasAuthInfo['valid'] < 1) {
                    exit($this->muOpResult(false, Xphp::$_lang['WEB_TENANT_AVAILABLE_CHECK'], Xphp::$_lang['WEB_TENANT_AVAILABLE_CHECK_ERROR'], "warning"));
                }
            }
        }
        $msg = array();
        // $allResult = array();
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeArr = $params['node_mount_list'];
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        //定义操作名

        if ($params['info']['nas_type'] == Xphp::$_config["BD_STORAGE_TYPE"]["CIFS"]) {
            $opcodeName = 'NODE_OP_ADD_CIFS_NAS';
        } else if ($params['info']['nas_type'] == Xphp::$_config["BD_STORAGE_TYPE"]["NFS"]) {
            $opcodeName = 'NODE_OP_ADD_NFS_NAS';
        }
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        //组合消息
        $msg = $params['info'];
        //分别发往各个节点
        $error = include CONF_PATH . 'error.php';
        $resultArr = array();
        $des = '';
        foreach ($nodeArr as $nodeuuid) {
            $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
            if ($mbResult['result']) {
                $des .= $this->getNodeIp($nodeuuid) . Xphp::$_lang['WEB_NAS_ADD_NAS_DEVICE_SUCCESS']  . '<br>';
            } else {
                $des .= Xphp::$_lang['WEB_OPHANDLER_ERROR_CODE'] . ": #" . $mbResult['errorCode'] . ", " . Xphp::$_lang['WEB_OPHANDLER_ERROR_DES'] . ":" . $this->getNodeIp($nodeuuid) . $error['errorCodeDes'][$error['errorCode'][$mbResult['errorCode']]] . '<br>';
            }
            $resultArr[] = $mbResult['result'];
        }
        if (!in_array(false, $resultArr)) {
            $result = true;
            $info = array(
                "re" => $result,
                "msg" => Xphp::$_lang['WEB_NAS_ADD_NAS_DEVICE_SUCCESS'] ,
            );
        } else {
            $result = false;
            $info = array(
                "re" => $result,
                "msg" => $des,
            );
        }
        return json_encode($info);
    }

    /**
     *得到所有节点
     * @param unknown $params
     * @return string'
     */
    public function getNodeIp($params)
    {
        $sql = "select ip from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($params));
        return $data[0]['ip'];
    }

    /**
     *得到所有节点
     * @param unknown $params
     * @return string'
     */
    public function getAllnodes($params)
    {
        $sql = "select ip,node_uuid, host_name, node_nickname from bd_node";
        $data = $this->dbSelect($sql);
        $result = array();
        foreach ($data as $d) {
            $result[] = array(
                'name' => $d['ip'],
                'value' => $d['node_uuid'],
            );
        }
        return json_encode($result);
    }







    /**
     *删除nas设备
     * @param unknown $params
     * @return string
     */
    public function delNasDevice($params)
    {
        $nodeHandler = Xphp::instance('NodeHandler');
        $localnodeuuid = $nodeHandler->getLocalNodeUUID();
        //定义操作名
        $opcodeName = 'NODE_OP_DELETE_NAS';
        //组合消息
        $msg = array(
            "nas_uuid" => $params['nas_uuid']
        );
        $nasuuiList = $params['nas_uuid'];
        //检测该设备是否有任务存在
        foreach ($nasuuiList as $nasuuid) {
            $sql = "select nst.ip, nst.nas_nickname,nst.share_path, nt.nas_uuid,bt.task_name from nas_storage_resource nst, nas_task nt,bd_task bt where nst.nas_uuid=nt.nas_uuid and bt.task_uuid=nt.task_uuid and nt.nas_uuid = ?";
            $data = $this->dbSelect($sql, array($nasuuid));
            if (!empty($data)) {
                $namedes = $data[0]['ip'] . '(' . $data[0]['nas_nickname'] . ')';
                if ($data[0]['nas_nickname'] == $data[0]['ip']) {
                    $namedes = $data[0]['ip'] . '(' . $data[0]['share_path'] . ')';
                }
                exit($this->muOpResult(false, Xphp::$_lang['UI_NAS_MANAGE_DELETE'], Xphp::$_lang['UI_NAS_MANAGE_DELETE_TASK']
                    . ', ' . Xphp::$_lang['UI_NAS_DEVICE_NAME'] . ':' . $namedes
                    . ', ' . Xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . ':' . $data[0]['task_name'], "warning"));
            }
        }
        //先解挂该nas设备上  除了主节点之外的节点
        foreach ($nasuuiList as $nasuuid) {
            $sql = "select node_uuid from nas_mount_list where nas_uuid = ?";
            $data = $this->dbSelect($sql, array($nasuuid));
            foreach ($data as $d) {
                if ($d['node_uuid'] != $localnodeuuid) {
                    $nasMsg = array(
                        "nas_uuid" => array($nasuuid)
                    );
                    $mbResult = $this->mbNodeMsg('NODE_OP_UMOUNT_NAS', $d['node_uuid'], json_encode($nasMsg));
                }
            }
        }
        $mbResult = $this->mbNodeMsg($opcodeName, $localnodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }




    /**
     *修改NAS设备信息
     * @param unknown $params
     * @return string
     */
    public function editNasDevice($params)
    {
        $nodeHandler = Xphp::instance('NodeHandler');
        $localnodeuuid = $nodeHandler->getLocalNodeUUID(); //主节点
        $opdes = Xphp::$_lang['WEB_NODE_OP_MODIFY_NFS_NAS'];
        //定义操作名
        if ($params['nas_type'] == Xphp::$_config["BD_STORAGE_TYPE"]["CIFS"]) {
            $opcodeName = 'NODE_OP_MODIFY_CIFS_NAS';
        } else if ($params['nas_type'] == Xphp::$_config["BD_STORAGE_TYPE"]["NFS"]) {
            $opcodeName = 'NODE_OP_MODIFY_NFS_NAS';
        }
        $msg = $params['info'];
        $flag = json_decode($this->checkEditNasParams($params), true)['flag'];
        $msg['nickname'] = htmlspecialchars_decode($msg['nickname']);
        if ($flag) {
            //如果修改了除别名之外的信息  检查是否有运行的任务存在
            $this->checkNasTaskExist($opdes, $params['info']['nas_uuid'], $params['mount_list']);
            //组合消息
            $mountResult = array(); //用于保存挂载结果
            foreach ($params['mount_list'] as $nodeuuid) {
                //先挂载再修改--
                $mbResult = $this->mbNodeMsg('NODE_OP_MOUNT_NAS_AGAIN', $nodeuuid, json_encode($msg));
                $mountResult[] = $mbResult["result"];
                $errorCode = $mbResult["errorCode"];
            }
            // $mbResult = $this->mbNodeMsg('NODE_OP_MOUNT_NAS_AGAIN', $localnodeuuid, json_encode($msg));
            if (!in_array(false, $mountResult)) { //全部挂载成功,直接把修改消息发到主节点
                $mbResult = $this->mbNodeMsg($opcodeName, $localnodeuuid, json_encode($msg));
            } else if (in_array(true, $mountResult)) { //部分挂载成功,返回挂载失败的节点,把修改消息发到主节点
                //获取挂载失败的节点
                $des = $this->getFailNode($mountResult, $params['mount_list']);
                $mbResult = $this->mbNodeMsg($opcodeName, $localnodeuuid, json_encode($msg));
                return $this->muOpResult(false, Xphp::$_lang['UI_NAS_MOUNT_SUCCESS_PART'], $des, 'warning');
            } else { //全部失败，不修改直接返回
                return $this->muOpResult(false, Xphp::$_lang['UI_NAS_MODIFY_FAIL'], '', 'warning', $errorCode);
            }
        } else {
            // 只修改了别名，直接修改，不需要挂载
            $mbResult = $this->mbNodeMsg($opcodeName, $localnodeuuid, json_encode($msg));
        }
        $result = $mbResult["result"];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     *获取挂载失败节点ip
     * @param array $mountResult 挂载成功或失败
     * @param array $nodelist 挂载节点列表
     * @return string
     */
    private function getFailNode($mountResult, $nodelist)
    {
        $des = "";
        foreach ($mountResult as $key => $m) {
            if (!$m) { //挂载失败
                $sql = "SELECT ip from bd_node where node_uuid = ?";
                $data = $this->dbSelect($sql, array($nodelist[$key]));
                $des .= $data[0]['ip'] . " ";
            }
        }
        $info = Xphp::$_lang['WEB_PLATFORM_DES_NODE'] . $des . Xphp::$_lang['WEB_ERROR_VM_ICS_KVM_RESOURCE_220109_ERROR'];
        return $info;
    }

    /**
     *检测是否修改了除了别名之外的信息
     * @param unknown $nasuuid
     * @return string
     */
    public function checkEditNasParams($params)
    {
        $nasuuid = $params['info']['nas_uuid'];
        $nastype = $params['nas_type'];
        $permission_flag = $params['info']['permission_flag'];
        $port = $params['info']['port'];
        $mount_params = $params['info']['mount_params'];
        $username = $params['info']['username'];
        $passwd = $params['info']['passwd'];
        $version = $params['info']['version'];
        $info = array(
            "flag" => false,
        );
        if ($nastype == Xphp::$_config["BD_STORAGE_TYPE"]["NFS"]) {
            $sql = "select permission_flag, port, mount_params, nas_version from nas_storage_resource where 	nas_uuid = ?";
            $data = $this->dbSelect($sql, array($nasuuid));
            if ($data[0]['permission_flag'] != $permission_flag || $data[0]['port'] != $port || $data[0]['mount_params'] != $mount_params || $data[0]['nas_version'] != $version) {
                $info = array(
                    "flag" => true,
                );
            }
        } else if ($nastype == Xphp::$_config["BD_STORAGE_TYPE"]["CIFS"]) {
            $sql = "select user_name, permission_flag, port, mount_params, nas_version, detail from nas_storage_resource where nas_uuid = ?";
            $data = $this->dbSelect($sql, array($nasuuid)); //密码不为空就是修改过了
            if ($data[0]['user_name'] != $username || $passwd != "" || $data[0]['port'] != $port || $data[0]['mount_params'] != $mount_params || $data[0]['permission_flag'] != $permission_flag || $data[0]['nas_version'] != $version) {
                $info = array(
                    "flag" => true,
                );
            }
        }
        return json_encode($info);
    }

    /**
     *nas授权
     * @param unknown $params
     * @return string
     */
    public function nasLisence($params)
    {
        //检测可用个数  如果是按容量授权  不检测
        $systemHandler = Xphp::instance('SystemHandler');
        $licenseInfo = json_decode($systemHandler->getSystemLicenseType(), true);
        if (($licenseInfo['licensetype'] != Xphp::$_config['LISENCE_INFO']['type']['storage']) && $params['info']['nas_auth_flag'] == 1) {
            //租户内检查可用数量是否超过授权个数
            if(!empty($_SESSION['tenantuuid'])){
                $tenantHandler = Xphp::instance('TenantHandler');
                $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
                if ($settings['auth_way'] ==2) {
                    $currentCount = count($params['info']['nas_ip_list']);
                    $systemHandler = Xphp::instance('SystemHandler');
                    $nasAuthInfo = $systemHandler->getOneModuleLisenceInfo('nas');
                    if ($nasAuthInfo['valid'] < $currentCount) {
                        exit($this->muOpResult(false, Xphp::$_lang['WEB_TENANT_AVAILABLE_CHECK'], Xphp::$_lang['WEB_TENANT_AVAILABLE_CHECK_ERROR'], "warning"));
                    }
                }
            }
            $nasinfo = $this->getNasLisenceInfo();
            if ($nasinfo['nas']['valid'] < count($params['info']['nas_ip_list'])) {
                exit($this->muOpResult(false, Xphp::$_lang['WEB_PT_LICENSE_OP_MODIFY_NAS_LICENSE'], Xphp::$_lang['UI_NAS_MANAGE_IP_NOT_ENOUGH']));
            }
        }
        $opName = 'PT_LICENSE_OP_MODIFY_NAS_LICENSE';
        $msg = $params['info'];
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        if ($params['info']['nas_auth_flag'] == 1) {
            //授权
            return $this->muOpResult($mbResult['result'], Xphp::$_lang['UI_VCENTER_AUTH_ADD']);
        } else if ($params['info']['nas_auth_flag'] == 2) {
            //取消授权
            return $this->muOpResult($mbResult['result'], Xphp::$_lang['UI_VCENTER_AUTH_DELETE']);
        }
    }

    /**
     *挂载
     * @param unknown $params
     * @return string
     */
    public function mountNas($params)
    {
        //定义操作名
        $opcodeName = 'NODE_OP_MOUNT_NAS';
        //组合消息
        $msg = $params['info'];
        $node_mount_list = $params['node_mount_list'];
        foreach ($node_mount_list as $nodeuuid) {
            $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        }
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     *解挂
     * @param unknown $params
     * @return string
     */
    public function umountNas($params)
    {
        $opdes = Xphp::$_lang['UI_NAS_MANAGE_UMOUNT'];
        $node_mount_list = $params['node_mount_list'];
        $nasuuid = $params['info']['nas_uuid'][0];
        //检查是否有运行的任务存在
        $this->checkNasTaskExist($opdes, $nasuuid, $node_mount_list);
        //定义操作名
        $opcodeName = 'NODE_OP_UMOUNT_NAS';
        //组合消息
        $msg = $params['info'];
        foreach ($node_mount_list as $nodeuuid) {
            $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
        }
        $operate = $this->opcodeHandler->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        $result = $mbResult['result'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 检查删除的设备在对应节点是否有任务存在
     * @param unknown $uuids
     */
    public function checkNasTaskExist($opdes, $nasuuid, $nodeList)
    { //运行  2
        // 判断已挂载节点是否是空
        if (empty($nodeList)) {
            exit($this->muOpResult(false, $opdes, Xphp::$_lang['UI_NAS_MANAGE_UMOUNT_NODE_TIPS'], 'warning'));
        }
        $sql = "select bt.id, bt.node_uuid from nas_task nt, bd_task bt where nt.task_uuid = bt.task_uuid and nt.nas_uuid = ? and bt.task_status = ?;";
        foreach ($nodeList as $nodeuuid) {
            $data = $this->dbSelect($sql, array($nasuuid, Xphp::$_config['TASKSTATUS']['RUNNING']));
            if (!empty($data) && $nodeuuid == $data[0]['node_uuid']) {
                exit($this->muOpResult(false, $opdes, Xphp::$_lang['UI_NAS_MANAGE_UMOUNT_EXIST_RUNNING'], 'warning'));
            }
        }
    }

    /**
     * 得到nas时间点树
     * @param unknown $params
     */
    public function getNasDataTree($params)
    {
        $dataflag = $params['dataflag'];
        $storageUuid = $params['storage_uuid'];
        $sql = "select bbt.real_node_uuid, bsr.node_uuid,bsr.storage_type, bbt.id, bbt.module_type, bbt.task_type, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag, bbt.src_data_deleted_flag,
		              bbt.user_uuid, bbt.user_name, fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, bbt.task_name, fbt.detail
                from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and 
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ? 
                       and bbt.import_flag = ? and
	                   bbt.module_type = " . Xphp::$_config['MODULE_TYPE']['NAS'] . " and
	                   bbt.data_local_flag = ?";
        $flag = Xphp::$_config['FLAG'];
        $userUUID = Xphp::$_user['useruuid'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $flag['SET']);
        //租户管理员特殊处理数据显示
        $tenantHandler = Xphp::instance('TenantHandler');
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        //获取租户管理员是否可以控制所有备份数据标志
        if (!empty($_SESSION['tenantuuid'])) {
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }
        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 备份数据恢复属于操作权限，不能归属于查看
            // 三权模式下的操作员只能查看自身的数据
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到自身的或者管理的用户的的数据
            $userUuidSql = $utils->v1_auth_get_users('nas_protect');
            $sqlNew = " and bbt.user_uuid in ({$userUuidSql}) ";
            $sql .= $sqlNew;
        }
        //admin不能看租户内部的资源
        if (empty($_SESSION['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        } else {
            $sql .= " and bbt.user_uuid = '" . Xphp::$_user['useruuid'] . "'";
        }

        if ($dataflag) {
            $sql .= " and bbt.copy_flag = ? and bbt.module_type = ? ";
            $sqlParams = array_merge($sqlParams, array($flag['UNSET'], Xphp::$_config['MODULE_TYPE']['NAS']));
        }
        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        $sql .= " order by bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $node = array();
        //定义agent task 数组
        $agent = array();
        $task = array();

        $vmHandler = Xphp::instance('Vmhandler');
        //得到当前任务所有uuid
        $currentTaskUUID = $vmHandler->getCurrentAllTaskUUID();
        foreach ($data as $d) {
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $detail = json_decode($d['detail'], true);
            $nasuuid = $detail['nas_uuid'];
            $nastype = $detail['nas_type'];
            //图标区分不同类型
            if ($nastype == 6) {
                $icon = "./img/nas/nfs.png";
            } else if ($nastype == 7) {
                $icon = "./img/nas/cifs.png";
            }
            if ($d['module_type'] == Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT'] && $dataflag)
                continue;
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];
            //检查并添加task
            if (!in_array($d['task_uuid'], $task)) {
                $task[] = $d['task_uuid'];
                $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                $name = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                // 副本数据
                if ($d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['BACKUP_COPY_FETCH']) {
                    $name = $taskName . "(" . Xphp::$_lang['UI_COPY_DATA'] . ")";
                }
                // 归档数据
                if ($d['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE'] || $d['task_type'] == Xphp::$_config['TASKTYPE']['ARCHIVE_FETCH']) {
                    $name = $taskName . "(" . Xphp::$_lang['UI_ARCHIVE_DATA'] . ")";
                }
                //租户开启管理所有数据，租户管理员在备份数据管理可以看见所有用户数据，需要对任务显示指定的用户名方便排查
                if ($dataflag && $tenantMangerFlag && $allManageFlag && $d['user_uuid'] != Xphp::$_user['useruuid']) {
                    $name .= "(" . $d['user_name'] . ")";
                }
                $node[] = array(
                    "id" => $d['task_uuid'],
                    "pId" => 0,
                    "name" => $name,
                    "title" => $name,
                    "task_name" => $taskName,
                    "open" => true,
                    "nocheck" => true,
                    "type" => 1,
                    "icon" => './img/platform/flag.png',
                    "nodeuuid" => $nodeUuid,
                    "taskuuid" => $d['task_uuid'],
                    "isParent" => true,
                    "nasuuid" => $nasuuid,
                    "agentuuid" => $d['agent_uuid'],
                    "storage_uuid" => $storageUuid,
                );
            }
            //检查并添加agent
            if (!in_array($nasuuid . "_" . $d['task_uuid'], $agent)) {
                $agent[] = $nasuuid . "_" . $d['task_uuid'];
                $node[] = array(
                    "id" => $nasuuid . "_" . $d['task_uuid'],
                    "pId" => $d['task_uuid'],
                    'name' => htmlspecialchars_decode($this->getNasNameStr($nasuuid, $d['agent_name'], $d['agent_ip'])),
                    "title" => htmlspecialchars_decode($this->getNasNameStr($nasuuid, $d['agent_name'], $d['agent_ip'])),
                    // "open" => true,
                    "nocheck" => !$dataflag,
                    "type" => 2,
                    "clickshow" => true,
                    "icon" => $icon,
                    "nodeuuid" => $nodeUuid,
                    "taskuuid" => $d['task_uuid'],
                    "nasuuid" => $nasuuid,
                    "agentuuid" => $d['agent_uuid'],
                    "isParent" => true,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                    "storage_uuid" => $storageUuid,
                );
            }

        }
        return json_encode($node);
    }

    /**
     * 1、nas设备没删，如果有别名就显示别名,没有别名就显示share_path,如果别名和IP一样则显示share_path
     * 2、nas设备已删除，显示时间点中的主机名+ip
     * @param unknown $fbt_name 时间点中的主机名
     * @return string name(ip)
     */
    public function getNasNameStr($nas_uuid, $fbt_name, $ip)
    {
        $sql = "select nas_nickname, share_path from nas_storage_resource where nas_uuid = ?";
        $result = $this->dbSelect($sql, array($nas_uuid));
        if (!empty($result)) {
            if ($result[0]['nas_nickname'] == $ip) {
                return $ip . '(' . $result[0]['share_path'] . ')';
            } else {
                return $ip . '(' . $result[0]['nas_nickname'] . ')';
            }
        } else {
            return $ip . '(' . $fbt_name . ')';
        }
    }

    /**
     * 异步获取nas时间点
     * @param unknown $params
     * @return string
     */
    public function getSyncNasTimepoint($params)
    {
        $recoverflag = $params['recoverflag']; //文件恢复加载时间点
        $taskuuid = $params['taskuuid'];
        $id = $params['id'];
        $storageUuid = $params['storage_uuid'];
        $dataflag = $params['dataFlag']; //备份数据的树形结构
        $agentuuid = $params['agentuuid'];
        $chkDisabled = false;
        $sql = "select bsr.storage_uuid, bsr.storage_nickname, bsr.node_uuid,bsr.storage_type, bbt.real_node_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,bbt.src_data_deleted_flag,
		              fbt.agent_name, fbt.agent_ip, bbt.task_name, fbt.detail as nasdetail, bbt.integrity_check_flag,
                      bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status   
                from fs_backup_timepoint fbt, bd_storage_resource bsr, bd_backup_timepoint bbt
                left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
                where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
        			   bbt.storage_uuid = bsr.storage_uuid and
                       bbt.deleted_flag = ? and bbt.available_flag = ?
                       and bbt.import_flag = ? and
	                   bbt.module_type = " . Xphp::$_config['MODULE_TYPE']['NAS'] . " and
	                    bbt.task_uuid = ? and bbt.data_local_flag = ? and fbt.agent_uuid = ?";

        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['SET'], $flag['UNSET'], $taskuuid, $flag['SET'], $agentuuid);

        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageUuid));
        }
        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 备份数据恢复属于操作权限，不能归属于查看
            // 三权模式下的操作员只能查看自身的数据
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到自身的或者管理的用户的的数据
            $userUuidSql = $utils->v1_auth_get_users('nas_protect');
            $sqlNew = " and bbt.user_uuid in ({$userUuidSql}) ";
            $sql .= $sqlNew;
        }
        $sql .= " order by bbt.task_uuid, bbt.timepoint";
        $data = $this->dbSelect($sql, $sqlParams);
        if (!is_array($data)) {
            $data = [];
        }
        //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        foreach ($data as $point) {
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else {
                $unfullList[] = $point;

            }
        }
        while (!empty($unfullList)) {
            $unfullCount = count($unfullList);
            $unfullCountTmp = count($unfullList);
            foreach ($unfullList as $key => $unfull) {
                $dependId = $unfull['depend_point_uuid'];
                foreach ($fulluuidList as $key => $value) {
                    if ($dependId == $key) {
                        $fulluuidList[$unfull['timepoint_uuid']] = $fulluuidList[$key];
                        // $unfullList = array_splice($unfullList, $key, 1);
                        if (!empty($unfullList[$key])) {
                            unset($unfullList[$key]);
                            $unfullList = array_values($unfullList);
                            $unfullCountTmp--;
                        }
                    }
                    continue;
                }
            }
            if ($unfullCount == $unfullCountTmp || $unfullCountTmp == 0)
                break;
        }
        $storageHandler = Xphp::instance('StorageHandler');
        $storageUuidList = array_column($data, 'storage_uuid');
        $storageStatusList = $storageHandler->batchGetStorageStatus($storageUuidList);
        $storageOfflineStatus = Xphp::$_config['STORAGE_STATUS']['OFFLINE'];
        $node = array();
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        $nodeHandler = Xphp::instance('NodeHandler');
        $jobHandler = Xphp::instance('JobHandler');
        $pid = null;
        foreach ($data as $d) {
            $pointMixedStatus = $jobHandler->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
             // 判断当前时间点所在存储是否离线，是则置灰节点
             $isOfflineStorage = 1;
             foreach ($storageStatusList as $key => $storageStatusInfo) {
                 if ($d['storage_uuid'] == $storageStatusInfo['storage_uuid']) {
                     $isOfflineStorage = $storageStatusInfo['storage_status'];
                     break;
                 }
             }
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $detail = json_decode($d['detail'], true);
            $nasdetail = json_decode($d['nasdetail'], true);
            $nasuuid = $nasdetail['nas_uuid'];
            $nasname = $d['agent_ip'] . "(" . $d['agent_name'] . ")";
            $name = $this->parseDate($d['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($d['backup_mode']) . ")";
            $title = $name;
            if ($isOfflineStorage == $storageOfflineStatus) { // 存储离线需拼接 (存储离线)
                $name = '<span style="color:#999999">' . $name . '(' . Xphp::$_lang['UI_PUBLIC_STORAGE_OFF'] . ')' . '</span>';
            }
            $chkDisabled = $isOfflineStorage == $storageOfflineStatus;
            if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }
            $mark = "";
            if ($dataflag) {
                //添加GFS标识
                $mark = $vmHandler->pGetTimepointMark(false, false, false, $utils->parseFlagToBool($d['importance_flag']));
                $gfsforever = $mark;
                if ($d["src_data_deleted_flag"] == 1) { //归档开启
                    $mark = '('. Xphp::$_lang['UI_ARCHIVE_DATA'] .')' . $mark;
                }
                //添加备注
                // if(!empty($d['remarks'])){
                //     $mark .= '<a id="remark_'.$d['timepoint_uuid'].'" style="display:inline-block;color: #5b9bd1;position: relative;top:5px;left:-4px;" class="popovers remarktips" data-container="body" data-trigger="hover"
                //             data-placement="right" data-content="'.$d['remarks'].'"><i class="fa fa-info-circle fa-lg" style="font-size: 21px !important; position:relative;top:-4px;left:-4px;"></i></a>';
                //         }
                $chkDisabled = true;

            }

            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $node[] = array(
                    "id" => $d['timepoint_uuid'],
                    "pId" => $id,
                    "name" => htmlspecialchars_decode($name . $mark),
                    "title" => htmlspecialchars_decode($title),
                    "checked" => false,
                    "type" => 3,
                    "nocheck" => false,
                    "oldname" => $name,
                    "nasuuid" => $nasuuid,
                    "nasname" => $nasname,
                    "gfsforever" => $gfsforever,
                    "point_uuid" => $d['timepoint_uuid'],
                    "depend_uuid" => $d['depend_point_uuid'],
                    "agent_name" => htmlspecialchars_decode($d['agent_name']),
                    "agent_ip" => $d['agent_ip'],
                    "task_name" => $d['task_name'],
                    "star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
                    "icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
                    "mode" => intval($d['backup_mode']),
                    "timepointuuid" => $d['timepoint_uuid'],
                    "taskuuid" => $taskuuid,
                    "nodeuuid" => $nodeUuid,
                    'storagename' => $d['storage_nickname'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    "encrypted_flag" => $encrypted_flag,
                    "ostype" => $nasdetail['nas_type'],
                    "password_auto_flag" => $detail['password_auto_flag'] == 1 ? true : false,
                    "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                    "storage_type" => $d['storage_type'],
                    'chkDisabled' => $chkDisabled,
                    'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                    "point_status" => $pointMixedStatus['status'],
                    "available_flag" => $pointMixedStatus['available_flag'],
                );
                continue;
            }
            $node[] = array(
                "id" => $d['timepoint_uuid'],
                "pId" => $fulluuidList[$timepointuuid],
                "name" => htmlspecialchars_decode($name . $mark),
                "title" => htmlspecialchars_decode($title),
                "checked" => false,
                "type" => 4,
                "oldname" => $name,
                "nasuuid" => $nasuuid,
                "nasname" => $nasname,
                "gfsforever" => $gfsforever,
                "nocheck" => $recoverflag,
                "point_uuid" => $d['timepoint_uuid'],
                "depend_uuid" => $d['depend_point_uuid'],
                "agent_name" => htmlspecialchars_decode($d['agent_name']),
                "agent_ip" => $d['agent_ip'],
                "task_name" => $d['task_name'],
                "star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
                "icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
                "mode" => intval($d['backup_mode']),
                "timepointuuid" => $d['timepoint_uuid'],
                "taskuuid" => $taskuuid,
                "nodeuuid" => $nodeUuid,
                'chkDisabled' => $chkDisabled,
                "storagename" => $d['storage_nickname'],
                'timepoint' => $this->parseDate($d['timepoint']),
                "encrypted_flag" => $encrypted_flag,
                "ostype" => $nasdetail['nas_type'],
                "password_auto_flag" => $detail['password_auto_flag'] == 1 ? true : false,
                "src_data_deleted_flag" => intval($d['src_data_deleted_flag']) == 1 ? true : false,
                "storage_type" => $d['storage_type'],
                'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                "point_status" => $pointMixedStatus['status'],
                "available_flag" => $pointMixedStatus['available_flag'],
            );
        }

        $msg = array(
            're' => true,
            'msg' => $node,
        );
        return json_encode($msg);
    }

    /**
     * 在查出来的时间点中找完备点
     */
    public function findFullPoint($data, $depend_point_uuid)
    {
        foreach ($data as $each) {
            if ($each['timepoint_uuid'] == $depend_point_uuid && intval($each['backup_mode']) == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $pid = $each['timepoint_uuid'];
                return $pid;
            } else if ($each['timepoint_uuid'] == $depend_point_uuid && intval($each['backup_mode']) == Xphp::$_config['BACKUP_MODE']['INCREMENTAL']) {
                // 找到的是增量
                return $this->findFullPoint($data, $each['depend_point_uuid']);
            }
        }
    }

    /**
     * 得到nas恢复文件树
     * @param array $params
     * @return string
     */
    public function getRecoveryNasDir($params)
    {

        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        if (count($params) != 11) {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_OPHANDLER_PARAMS_NULL'], 'warning'));
        }
        $timepointUUID = $params['timepoint_uuid'];
        $rootFlag = intval($params['root_flag']);
        $start = intval($params['start']);
        $number = intval($params['number']);
        $path = $params['path'];
        $md5Flag = intval($params['md5_flag']);
        $md5High = $params['md5_high'];
        $md5Low = $params['md5_low'];
        $pid = $params['pid'];
        $sclass = $params['sclass'];
        $taskuuid = $params['taskuuid'];
        $this->paramsCheck($timepointUUID);
        $msg = array(
            'timepoint_uuid' => $timepointUUID,
            'root_flag' => $rootFlag,
            'start' => $start,
            'number' => $number,
            'path' => $path,
            'md5_flag' => $md5Flag,
            'md5_high' => $md5High,
            'md5_low' => $md5Low
        );

        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointUUID);
        $opName = 'BD_BACKUP_POINT_OP_SCAN';
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($msg), true);
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            $pfOpcode = Xphp::instance('PFOpcode');
            $operate = $pfOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];
        $list = array(
            "re" => true,                               //成功标志,方面前端统一处理
            "finishFlag" => $data['finish_flag'],       //文件是否列完成   1完成,2未完成
            "nextStart" => $data['next_start'],       //未完成时,下一个开始位置
            "path" => $data['path'],                    //当前路径
            "timepointUUID" => $data['timepoint_uuid'],
        );
        $fileList = array();
        // $utils = Xphp::instance('Utils');
        foreach ($data['item_list'] as $d) {
            switch ($d['type']) {
                case 1:
                    $icon = "./img/fs/wenjian.png";
                    break;
                case 2:
                    $icon = "./img/fs/wenjianjia.png";
                    break;
                case 3:
                    $icon = "./img/fs/cipan.png";
                    break;
                default:
                    $icon = "./img/fs/wenjian.png";
                    break;
            }
            // $filesize = $utils->calSize($d['file_size']);
            $fileList[] = array(
                // $filename,      //文件名
                // $filesize,      //文件大小
                "id" => $d['path'],
                "pid" => $pid,
                "name" => $d['filename'],
                "nocheck" => false,
                "title" => $d['filename'],
                "path" => $d['path'],
                "btype" => $d['type'],   //文件类型
                "isParent" => $this->getBoolType($d['type']),
                "icon" => $icon,
                "iconOpen" => $d['type'] == 2 ? './img/fs/wenjianjiaopen.png' : $icon,
                "iconClose" => $d['type'] == 2 ? './img/fs/wenjianjia.png' : $icon,                                       //从后台获取的文件类型
                "isfile" => $d['type'] == Xphp::$_config['FILETYPE']['FILE'] ? true : false,   //是否是文件
                "sclass" => $this->getFileClassName($d['type'], $d['filename'], $sclass),                //显示类型
                "md5Flag" => $d['md5_flag'],
                "md5High" => $d['md5_high'],
                "md5Low" => $d['md5_low'],
                "createTime" => $d['create_time'],
                "modifyTime" => $d['modify_time'],
                "pointuuid" => $params['timepoint_uuid'],
                "more" => false,
            );
        }
        $list['filelist'] = $fileList;
        if (intval($data['finish_flag']) == Xphp::$_config['FLAG']['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                "id" => $data['path'] . '/more',
                "pId" => $pid,
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "nocheck" => true,
                "more" => true,
                "next_index" => $data['next_start'],       //从哪个位置开始加载
                // "search_file_name" => $data['path'],          //从哪个目录开始加载
                // "dir_path" => $params['dir'],
                "pointuuid" => $params['timepoint_uuid'],
                "sclass" => $sclass,                //显示类型
                "md5Flag" => $md5Flag,
                "md5High" => $md5High,
                "md5Low" => $md5Low,
                "filepath" => "",                            //当前目录名
                // "filepath" =>$result['pid'].$result['search_file_name'],

            );
            array_push($list['filelist'], $more);
            // $list['filelist'] = $more;
        }


        return json_encode($list);
    }
    /**
     * 得到nas设备下的恢复文件目录树
     * @param array $params
     * @return string
     */
    public function getRecoverPathTree($params)
    {
        $start = intval($params['start']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $agentUUID = $params['agentuuid'];
        $pid = $params['pid'];          //父节点ID
        $this->paramsCheck($agentUUID);
        $code_type = 2;
        if (!empty($params['code_type'])) {
            $code_type = $params['code_type']; //编码类型
        }
        $msg = array(
            'agent_uuid' => $agentUUID,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
            'code_type' => $code_type,
        );
        $opName = 'NODE_AGENT_OP_QUERY_RECOVERY_DIR_LIST';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            $NodeOpcode = Xphp::instance('NodeOpcode');
            $operate = $NodeOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $data = $mbResult['msg'];
        $i = 0;
        foreach ($data['item_list'] as $d) {
            $node = array(
                "id" => $pid . "_" . $i++,
                "pId" => $pid == "" ? 0 : $pid,
                "name" => $d['item_name'],
                "title" => $d['item_path'],
                "isParent" => true,
                "nocheck" => false,
                "icon" => "./img/fs/wenjianjia.png",
                "iconOpen" => "./img/fs/wenjianjiaopen.png",
                "iconClose" => "./img/fs/wenjianjia.png",
                "type" => $d['item_type'],
                "more" => false,
                "noRemoveBtn" => true,
                "isnew" => false, //文件夹是否是最新的新建
                "new_dir_create" => Xphp::$_config['FLAG']['UNSET'], //文件夹是否是新建的
                "noEditBtn" => true,
                //                 "icon" => "./img/vm/host.png",
                "code_type" => intval($d['code_type']),
            );
            $tree[] = $node;
        }
        if (intval($data['is_search_finish']) == Xphp::$_config['FLAG']['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                "id" => $pid . "_" . $i++,
                "pId" => $pid == "" ? 0 : $pid,
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "icon" => "./img/fs/wenjianjia.png",
                "iconOpen" => "./img/fs/wenjianjiaopen.png",
                "iconClose" => "./img/fs/wenjianjia.png",
                "isParent" => false,
                "nocheck" => true,
                "more" => true,
                "next_index" => $data['current_next_index'],       //从哪个位置开始加载
                "search_file_name" => $data['search_file_name'],          //从哪个目录开始加载
                "dir_path" => $dir,                                  //当前目录名
                "noRemoveBtn" => true,
                "isnew" => false, //文件夹是否是最新的新建
                "new_dir_create" => Xphp::$_config['FLAG']['UNSET'], //文件夹是否是新建的
                "noEditBtn" => true,
            );
            $tree[] = $more;
        }

        $info = array(
            're' => true,
            'tree' => $tree
        );

        return json_encode($info);

    }
    /**
     * 根据文件类型得到显示的Class
     * @param int $filetype 文件类型
     * @param string $filename  文件名
     * @param string $size  图标大小   s/m/l 24/32/48px
     */
    public function getFileClassName($filetype, $filename, $size)
    {
        $class = "filetype-unknown-" . $size;
        if (intval($filetype) != Xphp::$_config['FILETYPE']['FILE']) {
            $class = "filetype-dir-" . $size;
            return $class;
        }
        $allFileType = array(
            'aac',
            'ai',
            'aiff',
            'asp',
            'avi',
            'bmp',
            'c',
            'cpp',
            'css',
            'dat',
            'dmg',
            'doc',
            'docx',
            'dot',
            'dotx',
            'dwg',
            'dxf',
            'eps',
            'exe',
            'flv',
            'gif',
            'h',
            'html',
            'ics',
            'iso',
            'java',
            'jpg',
            'key',
            'm4v',
            'mid',
            'mov',
            'mp3',
            'mp4',
            'mpg',
            'odp',
            'ods',
            'odt',
            'otp',
            'ots',
            'ott',
            'pdf',
            'php',
            'png',
            'pps',
            'ppt',
            'psd',
            'py',
            'qt',
            'rar',
            'rb',
            'rtf',
            'sql',
            'tga',
            'tgz',
            'tiff',
            'txt',
            'wav',
            'xls',
            'xlsx',
            'xml',
            'yml',
            'zip'
        );
        $fileArr = explode('.', $filename);
        $index = (count($fileArr) - 1) <= 0 ? 0 : (count($fileArr) - 1);
        if (in_array(strtolower($fileArr[$index]), $allFileType)) {
            $class = "filetype-" . strtolower($fileArr[$index]) . "-" . $size;
        }
        return $class;
    }
    /**
     * 创建恢复任务
     * @param unknown $params
     */
    public function createRecoverJob($params)
    {
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        $vmHandler = Xphp::instance('Vmhandler');
        $module_type = Xphp::$_config['MODULE_TYPE']['NAS'];
        $recovery_position = intval($params['recoverInfo']['pathtype']);
        $recovery_time_type = intval($params['typeInfo']['type']);
        $time_strategy_list = $this->groupRecoverTimeList($params['typeInfo']);
        $transport_strategy = $this->groupTransportStrategy($params['typeInfo']['high']['trasfer']);

        $pfMSg = $this->pfCreateRecoveryTaskMessage(
            $task_name,
            $module_type,
            $recovery_position,
            $recovery_time_type,
            $time_strategy_list,
            $transport_strategy
        );
        $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['RECOVERY'];
        //private params
        //disk or file
        $pfMSg['recovery_level'] = 1;
        $pfMSg['destination_agent_uuid'] = $params['recoverInfo']['agentUUID'];
        $pfMSg['fs_path_list'] = $this->getRecoverNasList($params['pointInfo'], $params['recoverInfo']);
        $pfMSg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], "");
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        $pfMSg['password'] = $params['recoverInfo']['password'];
        $pfMSg['thread_num'] = $params['thread_num'];
        $pfMSg['nas_uuid'] = $params['pointInfo']['nasuuid'];
        $pfMSg['new_dir_create'] = $params['recoverInfo']['new_dir_create'];
        //是否是跨平台传输
        $pfMSg['cross_platform_transform'] = $params['recoverInfo']['cross_platform_transform'];
        $submodule_type = intval($params['pointInfo']['type']);
        $pfMSg['distinct_flag'] = intval($params['distinct_flag']);
        //目录树恢复
        $pfMSg['dir_tree_recovery_flag'] = intval($params['highInfo']['dir_tree_recovery_flag']);
        //同名文件处理
        $pfMSg['same_file_strategy'] = intval($params['highInfo']['same_file_strategy']);
        //无效快捷方式清理
        $pfMSg['link_file_pass_flag'] = intval($params['highInfo']['link_file_pass_flag']);
        //文件权限恢复
        $pfMSg['permission_operate_flag'] = intval($params['highInfo']['permission_operate_flag']);
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['pointInfo']['pointUUID']);
        $msg = json_encode($pfMSg);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            //恢复方式,是立即恢复还是按策略恢复,如果是立即恢复,需要创建完成后启动任务
            $recoveryType = $params['typeInfo']['type'];
            if (Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == $recoveryType) {
                $startResult = $this->startRecoverJob($task_name);
            } else {
                $startResult = true;
            }
            if ($startResult) {
                //启动任务成功,直接返回创建任务成功
                return $this->muOpResult($result, $operate, $msg);
            } else {
                //启动任务失败,返回创建任务成功加上启动任务失败提示,这里考虑到每次发消息都是成功的就暂时没有加
                return $this->muOpResult($result, $operate, $msg);
            }
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 组合恢复时间策略
     * @param array $params
     * @return array
     */
    private function groupRecoverTimeList($params)
    {
        $timeList = array();
        if (Xphp::$_config['RECOVERY_TIME_TYPE']['IMMEDIATELY'] == intval($params['type'])) {
            //立即恢复
            return $timeList;
        } elseif (Xphp::$_config['RECOVERY_TIME_TYPE']['STRATEGY'] == intval($params['type'])) {
            //按时间策略恢复
            $timeList[] = $this->groupEachTimestrategy(Xphp::$_config['BACKUP_MODE']['FULL'], $params['strategy']);
            return $timeList;
        }
    }

    /**
     * 组合每一个时间策略
     * @param int $mode         完全1/增量2/差异3/日志4/标签5
     * @param array $strategy
     * @return array
     */
    private function groupEachTimestrategy($mode, $strategy)
    {
        $utils = Xphp::instance('Utils');
        $this->paramsCheck($mode, $strategy);
        $strArr = array("mode" => $mode);
        if ($strategy['globalID']) {
            //使用全局策略
            $strArr['global_id'] = $strategy['globalID'];
        }
        //时间策略
        $strArr['strategy_type'] = $strategy['type'];
        $strArr['full_backup_compensation_flag'] = $utils->parseBoolToFlag($strategy['full_backup_compensation_flag']);
        $strArr['days'] = implode("", is_array($strategy['days']) ? $strategy['days'] : []) . $strategy['frequency'];
        $strArr['start_time'] = $strategy['startTime'];
        $strArr['roll_flag'] = $strategy['rollFlag'];
        $strArr['roll_interval'] = $this->getRollInterval($strategy['rollInterval']);
        $strArr['roll_end_time'] = $strategy['endTime'];
        if (Xphp::$_config['STRATEGY_TYPE']['EVERY_DAY'] == intval($strArr['strategy_type'])) {
            //每天备份
            $strArr['days'] = "1111111";
        }

        if (Xphp::$_config['STRATEGY_TYPE']['ONCE'] == intval($strArr['strategy_type'])) {
            //一次性备份
            $strArr['days'] = '';
        }

        if ($strArr['roll_flag']) {
            //滚动备份
            $strArr['roll_flag'] = Xphp::$_config['STRATEGY_ROLL_TYPE']['ON'];
        } else {
            //不滚动
            $strArr['roll_flag'] = Xphp::$_config['STRATEGY_ROLL_TYPE']['OFF'];
            $strArr['roll_interval'] = 0;
            $strArr['roll_end_time'] = '';
        }
        return $strArr;
    }
    /**
     * 转化滚动间隔为秒
     * @param string $rollInterval
     * @return number
     */
    private function getRollInterval($rollInterval)
    {
        if (empty($rollInterval)) {
            return 0;
        }
        $intervalArr = explode(":", $rollInterval);
        $second = intval($intervalArr[0]) * 3600 + intval($intervalArr[1]) * 60 + intval($intervalArr[2]);
        return $second;
    }
    /**
     * 组合传输策略
     * @param array $transport  传输策略信息
     *  @param  bool encrypt    加密
     *  @param  bool compress   压缩
     *  @param  bool speedFlag  限速
     *  @param  int  speed
     * @return array
     */
    private function groupTransportStrategy($transport)
    {
        $strArr = array(
            'encrypt_flag' => $transport['encrypt'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'compress_flag' => $transport['compress'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'speed_limit_flag' => $transport['speedFlag'] ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'],
            'max_speed' => intval($transport['speed']),
            'compress_method' => $transport['compress_method'] ? $transport['compress_method'] : 0,
            'encrypt_method' => $transport['encrypt'] && $transport['encrypt_method'] ? $transport['encrypt_method'] : 0,
        );
        return $strArr;
    }
    /**
     * 得到恢复的nas列表信息
     * @param array $pointInfo
     *          agentUUID   恢复源代理UUID
     *          pointUUID   恢复时间点
     *          fileInfo    array
     *              [类型,路径,名字,MD5high, MD5low]
     * @param array $recoverInfo
     *          type        恢复类型    原机1/异机2
     *          agentUUID   恢复目标UUID    原机就是原机的UUID,异机就是异机的UUID
     *          pathtype    恢复路径类型      原路径1/新路径2
     *          path        恢复新路径
     * @return array
     */
    private function getRecoverNasList($pointInfo, $recoverInfo)
    {
        $fileInfo = $pointInfo['fileInfo'];
        $files = array();
        foreach ($fileInfo as $f) {
            $pathtype = intval($recoverInfo['pathtype']);
            $newRootPath = '';
            if ($pathtype == Xphp::$_config['FLAG']['UNSET']) {
                //异机恢复
                $newRootPath = $recoverInfo['path'];
                $agentUUID = $recoverInfo['agentUUID'];
            }
            $files[] = array(
                'agentUUID' => $agentUUID,
                'path_type' => intval($f[0]),
                'path_name' => $f[1],
                'new_root_path' => $newRootPath,
                'recovery_timepoint_uuid' => $pointInfo['pointUUID'],
                'md5_high' => $f[3],
                'md5_low' => $f[4],
                'code_type' => $recoverInfo['code_type'],
            );
        }
        return $files;
    }
    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName  任务名
     */
    private function startRecoverJob($taskName)
    {
        $sql = "select bt.task_uuid, bt.module_type, bt.task_type
                from bd_task bt where bt.task_name = ? and bt.module_type = ? and bt.task_type = ?";
        $data = $this->dbSelect($sql, array($taskName, Xphp::$_config['MODULE_TYPE']['NAS'], Xphp::$_config['TASKTYPE']['RECOVERY']));
        if (!$data)
            return false;

        //任务uuid, 模块号, 子模块号, 任务类型 ,启动类型
        $params = array(
            'uuid' => $data[0]['task_uuid'],
            'module' => $data[0]['module_type'],
            'subModule' => 0,
            'taskType' => $data[0]['task_type'],
            'startType' => Xphp::$_config['BACKUP_MODE']['FULL'],
        );
        //调用系统统一启动任务接口.不重新写
        $jobHandler = Xphp::instance('JobHandler');
        $result = $jobHandler->startJob($params);
        $result = json_decode($result, true);
        //这里直接返回成功或失败 bool
        return $result['re'];
    }
    /**
     * 搜索nas时间点
     * @param unknown $params
     */
    public function searchNasTimepoint($params)
    {
        $search = $params['search'];
        $forever = $params['forever'];
        $storageUuid = $params['storage'];
        $dataFlag = $params['dataFlag'];
        $sql = "select bbt.deleted_flag,bsr.storage_nickname,bsr.storage_type, bsr.node_uuid, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, bbt.integrity_check_flag,
        bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status  
  from fs_backup_timepoint fbt, bd_storage_resource bsr,bd_backup_timepoint bbt
  left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
  where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
         bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
         bbt.module_type = ". Xphp::$_config['MODULE_TYPE']['NAS'] ." and bbt.sub_module_type = " . Xphp::$_config['SUBMODULE_TYPE']['NAS'] . " and bbt.data_local_flag = 1 and bbt.backup_mode = 1";
         if (empty($_SESSION['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $tenantUuids = $this->dbSelect('select user_uuid from mt_user_tenant');
            $tenantUuids = array_column($tenantUuids,'user_uuid');
            $tenantUuids = "('" . implode("','", $tenantUuids) . "')";
            $sql .= " and bbt.user_uuid not in $tenantUuids ";
        }
         //筛选出每个增备点和差异点对应PID
        $fulluuidList = array();
        $unfullList = array();
        if ($forever) {
            $sql .= " and bbt.importance_flag like '%" . $forever . "%'";
        }
        if (!empty($search)) {
            $sql .= " and (bbt.timepoint like '%" . $search . "%' or fbt.task_name like '%" . $search . "%' or fbt.agent_name like '%" . $search . "%' or fbt.agent_ip like '%" . $search . "%')";
        }
        $sqlParams = array();
        $taskType = Xphp::$_config['TASKTYPE'];
        $chkDisabled = true;
        if (!$dataFlag) { //恢复页面
            $chkDisabled = false;
            $sql .= " and bbt.task_type in ({$taskType['BACKUP']},{$taskType['BACKUP_COPY']},{$taskType['BACKUP_COPY_FETCH']})";
        } else {
            $sql .= " and bbt.task_type = {$taskType['BACKUP']}";
        }
        if (!empty($params['recovery_range'])) {
            $sql .= ' and bbt.timepoint between ? and ?';
            $sqlParams = array_merge($sqlParams, array($params['startTime'],$params['endTime']));
        }
        $utils = Xphp::instance('Utils');
        if ($utils->v1_auth_need_operation()) {
            // 备份数据恢复属于操作权限，不能归属于查看
            // 三权模式下的操作员只能查看自身的数据
            // 不是超级管理员也不是全局观察者-查看并操作，也只能看到自身的或者管理的用户的的数据
            $userUuidSql = $utils->v1_auth_get_users('nas_protect');
            $sqlNew = " and bbt.user_uuid in ({$userUuidSql}) ";
            $sql .= $sqlNew;
        }
        if (!empty($storageUuid)) {
            $sql .= " and bsr.storage_uuid = ? order by fbt.agent_uuid, bbt.timepoint";
            $pointData = $this->dbSelect($sql, array_merge($sqlParams,array($storageUuid)));
        } else {
            $sql .= ' order by fbt.agent_uuid, bbt.timepoint';
            $pointData = $this->dbSelect($sql,$sqlParams);
        }
        foreach ($pointData as $key => $point) {
            if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $fulluuidList[$point['timepoint_uuid']] = $point['timepoint_uuid'];
            } else { //如果查找到增量和差异,在$fulluuidList找不到对应的完备点，添加对应完备点
                // $unfullList[] = $point;
                if ($point['backup_mode'] == Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']) { //差异
                    $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname,bsr.storage_type, bsr.node_uuid, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
                    fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, bbt.integrity_check_flag,bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status  
                    from fs_backup_timepoint fbt, bd_storage_resource bsr,bd_backup_timepoint bbt
                    left join bd_backup_timepoint_safe_info bsi on bsi.timepoint_uuid = bbt.timepoint_uuid 
                    where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
                     bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
                     bbt.module_type = 11 and bbt.data_local_flag = 1 and bbt.timepoint_uuid=? order by fbt.agent_uuid, bbt.timepoint";
                    $data = $this->dbSelect($sqlfull, array($point['depend_point_uuid']));
                    if (!in_array($data[0]['timepoint_uuid'], $fulluuidList)) {
                        $pointData[] = $data[0]; //完备点
                    }
                } else { //增量
                    $fullpoint = $this->getFulllPoint($point['depend_point_uuid']);
                    if (!in_array($fullpoint['timepoint_uuid'], $fulluuidList)) {
                        $fulluuidList[] = $fullpoint['timepoint_uuid']; //避免搜出重复完备点
                        $pointData[] = $fullpoint;
                    }
                    $pointData[$key]['depend_point_uuid'] = $fullpoint['timepoint_uuid'];
                }
            }
        }
        $timepoint = array();
        $node = array();
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        $nodeHandler = Xphp::instance('NodeHandler');
        $pid = null;
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        foreach ($pointData as $d) {
            $pointMixedStatus = $jobHandler->getTimePointStatus($d['merge_status'], $d['operation_status'], $d['virus_scan_status'], $d['integrity_check_status']);
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $detail = json_decode($d['detail'], true);
            $name = $this->parseDate($d['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($d['backup_mode']) . ")";
            if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                $name .= '<i class="fa fa-lock"></i>';
                $encrypted_flag = true;
            } else {
                $encrypted_flag = false;
            }
            $mark = "";
            //添加GFS标识
            $mark = $vmHandler->pGetTimepointMark(false, false, false, $utils->parseFlagToBool($d['importance_flag']));
            $gfsforever = $mark;
            if (!$dataFlag) {
                $mark = '';
            }
            $timepointuuid = $d['timepoint_uuid'];
            if (intval($d['backup_mode']) == Xphp::$_config['BACKUP_MODE']['FULL']) {
                $fullTimepoint = $d['timepoint_uuid'];
                $node[] = array(
                    "id" => $d['timepoint_uuid'],
                    "pId" => $d['agent_uuid'] . "_" . $d['task_uuid'],
                    "name" => htmlspecialchars_decode($name . $mark),
                    "checked" => false,
                    "type" => 3,
                    "nocheck" => false,
                    "oldname" => $name,
                    "gfsforever" => $gfsforever,
                    "point_uuid" => $d['timepoint_uuid'],
                    "depend_uuid" => $d['depend_point_uuid'],
                    "agent_name" => htmlspecialchars_decode($d['agent_name']),
                    "agent_ip" => $d['agent_ip'],
                    "agent_uuid" => $d['agent_uuid'],
                    "task_name" => $d['task_name'],
                    "star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
                    "icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
                    "mode" => intval($d['backup_mode']),
                    "timepointuuid" => $d['timepoint_uuid'],
                    "taskuuid" => $d['$taskuuid'],
                    "nodeuuid" => $nodeUuid,
                    'storagename' => $d['storage_nickname'],
                    'timepoint' => $this->parseDate($d['timepoint']),
                    "nodename" => $nodeHandler->getNodeName($nodeUuid),
                    "encrypted_flag" => $encrypted_flag,
                    "ostype" => $detail['os_type'],
                    "password_auto_flag" => $detail['password_auto_flag'] == 1 ? true : false,
                    "storage_type" => $d['storage_type'],
                    'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                    "point_status" => $pointMixedStatus['status'],
                    "available_flag" => $pointMixedStatus['available_flag'],
                );
                $incList = $this->getSearchIncTimepoint($d['timepoint_uuid']);
                if (!empty($incList)) {
                    foreach ($incList as $d) {
                        if($d['timepoint_uuid'] == $fullTimepoint){
                            continue;
                        }
                        $detail = json_decode($d['detail'], true);
                        $name = $this->parseDate($d['timepoint']) . " (" . $vmHandler->getTimepointTypeDes($d['backup_mode']) . ")";
                        if ($d["encrypted_flag"] == 1 && $detail['password_auto_flag'] == 2) { // 非自动加密的时间点加锁
                            $name .= '<i class="fa fa-lock"></i>';
                            $encrypted_flag = true;
                        } else {
                            $encrypted_flag = false;
                        }
                        //添加GFS标识
                        $mark = $vmHandler->pGetTimepointMark(false, false, false, $utils->parseFlagToBool($d['importance_flag']));
                        $gfsforever = $mark;
                        if (!$dataFlag) {
                            $mark = '';
                        }
                        $node[] = array(
                            "id" => $d['timepoint_uuid'],
                            "pId" => $fullTimepoint,
                            "name" => htmlspecialchars_decode($name . $mark),
                            "checked" => false,
                            "type" => 4,
                            "oldname" => $name,
                            "gfsforever" => $gfsforever,
                            "nocheck" => $dataFlag,
                            "point_uuid" => $d['timepoint_uuid'],
                            "depend_uuid" => $d['depend_point_uuid'],
                            "agent_name" => htmlspecialchars_decode($d['agent_name']),
                            "agent_ip" => $d['agent_ip'],
                            "agent_uuid" => $d['agent_uuid'],
                            "task_name" => $d['task_name'],
                            "star" => intval($d['importance_flag']) == Xphp::$_config['FLAG']['SET'],
                            "icon" => $vmHandler->getTimepointIcon($d['backup_mode']),
                            "mode" => intval($d['backup_mode']),
                            "timepointuuid" => $d['timepoint_uuid'],
                            "taskuuid" => $d['$taskuuid'],
                            "nodeuuid" => $nodeUuid,
                            "chkDisabled" => $chkDisabled,
                            "storagename" => $d['storage_nickname'],
                            'timepoint' => $this->parseDate($d['timepoint']),
                            "nodename" => $nodeHandler->getNodeName($nodeUuid),
                            "encrypted_flag" => $encrypted_flag,
                            "ostype" => $detail['os_type'],
                            "password_auto_flag" => $detail['password_auto_flag'] == 1 ? true : false,
                            "storage_type" => $d['storage_type'],
                            'integrity_check_flag' => intval($d['integrity_check_flag']) == 1,
                            "point_status" => $pointMixedStatus['status'],
                            "available_flag" => $pointMixedStatus['available_flag'],
                        );
                    }
                }
                continue;
            }
        }
        return json_encode($node);
    }

    /**
     * 搜索时获取增量点
     */
    public function getSearchIncTimepoint($depend_point_uuid){
        $sql = "WITH RECURSIVE cte AS (
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                   bsr.node_uuid,bsr.storage_type, bbt.integrity_check_flag,
                   bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status 
            FROM bd_backup_timepoint bbt
            LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            LEFT JOIN bd_backup_timepoint_safe_info bsi ON bsi.timepoint_uuid = bbt.timepoint_uuid
            WHERE bbt.timepoint_uuid = ? 
                  AND bbt.available_flag = 1 
                  AND bbt.import_flag = 2
            UNION ALL
            SELECT bbt.timepoint_uuid, bbt.storage_uuid, bbt.task_name, bbt.task_type, bbt.real_node_uuid,
                   bbt.module_type, unix_timestamp(bbt.task_create_time) task_create_time, bbt.task_uuid,
                   bbt.backup_mode, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, 
                   bbt.detail, bbt.importance_flag, bbt.remarks, bbt.archive_flag,bbt.encrypted_flag,
                   fbt.agent_uuid, fbt.agent_name, fbt.agent_ip,
                   bsr.node_uuid,bsr.storage_type, bbt.integrity_check_flag,
                   bbt.operation_status, bbt.merge_status, bsi.virus_scan_status, bsi.integrity_check_status 
            FROM bd_backup_timepoint bbt
            LEFT JOIN fs_backup_timepoint fbt ON bbt.timepoint_uuid = fbt.fs_timepoint_uuid
            LEFT JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
            JOIN cte ON bbt.depend_point_uuid = cte.timepoint_uuid
            LEFT JOIN bd_backup_timepoint_safe_info bsi ON bsi.timepoint_uuid = bbt.timepoint_uuid
            WHERE bbt.available_flag = 1 
                  AND bbt.import_flag = 2
        )
        SELECT * FROM cte ORDER BY timepoint;
        ";
        $result = $this->dbSelect($sql,array($depend_point_uuid));
        if(empty($result)){
            return array();
        }
        return $result;
    }

    /**
     * 找增量点的完备点
     * @param unknown $params
     */
    private function getFulllPoint($timepoint_uuid)
    {
        $sqlfull = "select bbt.deleted_flag,bsr.storage_nickname,bsr.storage_type, bsr.node_uuid, bbt.real_node_uuid, bbt.task_uuid, bbt.id, bbt.timepoint_uuid, bbt.depend_point_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.task_uuid, bbt.importance_flag,bbt.detail,bbt.encrypted_flag,bbt.remarks,
        fbt.agent_uuid, fbt.agent_name, fbt.agent_ip, fbt.task_name, bbt.integrity_check_flag 
        from bd_backup_timepoint bbt, fs_backup_timepoint fbt, bd_storage_resource bsr
        where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and
         bbt.storage_uuid = bsr.storage_uuid and bbt.available_flag = 1 and
         bbt.module_type = 11 and bbt.data_local_flag = 1 and bbt.timepoint_uuid=? order by fbt.agent_uuid, bbt.timepoint";
        $data = $this->dbSelect($sqlfull, array($timepoint_uuid));
        if (!empty($data[0]['depend_point_uuid'])) { //不是完备点继续找
            return $this->getFulllPoint($data[0]['depend_point_uuid']);
        } else {
            return $data[0];
        }
    }
    /**
     * 删除备份时间点
     * @param unknown $params
     * storageHandler deleteFSImportData调用
     */
    public function deleteTimepoint($params)
    {
        $pointUUID = $params['uuid'];
        $taskuuid = $params['taskuuid'];
        $agentuuid = $params['agentuuid'];
        $sql = "select timepoint,timepoint_uuid from bd_backup_timepoint where timepoint_uuid = ? ";
        $data = $this->dbSelect($sql, array($pointUUID[0]));
        //检测时间点是否在磁带上
        $fileHandler = Xphp::instance('FileHandler');
        $fileHandler->checkTimepointStorage([$data[0]['timepoint_uuid']]);
        $this->paramsCheck($pointUUID);
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid = ?", [Xphp::$_user['useruuid'], $pointUUID]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'error'));
            }
        }

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            $authUser = $_SESSION['authUser']['nas_protect_operate'] ?? [];
            if (!empty($authUser)) {
                $authUserStr = "'" . implode("','", $authUser) . "'";
                $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid not in 
                   ({$authUserStr}) and timepoint_uuid = ?", [$pointUUID]);
            } else {
                $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid = ?", [Xphp::$_user['useruuid'], $pointUUID]);
            }
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'error'));
            }
        }



        $msg = json_encode(array('timepoint_uuids' => $pointUUID));
        // $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = null;
        // $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($pointUUID);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];

        $msg = $mbResult['msg'];
        $descriptionParam = array($data[0]['timepoint']);
        //返回结果到UI
        if ($result) {
            //             $this->systemLog('SYSTEM_LOG_DELETE_FILE_ONE_TIMEPOINT', $descriptionParam);
            $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, fs_backup_timepoint fbt
                    where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.deleted_flag = ? and
                    bbt.available_flag = ? and fbt.agent_uuid = ? and bbt.task_uuid = ? and bbt.timepoint_uuid = ?
                    ";
            $flag = Xphp::$_config['FLAG'];
            $dataCount = $this->dbSelect($sqlCount, array($flag['UNSET'], $flag['SET'], $agentuuid, $taskuuid, $pointUUID));
            $count = $dataCount[0]['total'];
            return $this->muOpResult($result, $operate, $msg, '', 0, array("count" => intval($count), "id" => $pointUUID));
        } else {
            //             $this->systemLog('SYSTEM_LOG_DELETE_FILE_ONE_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 删除批量备份时间点
     * @param array $params 二维数组
     * 如 array(array('nodeuuid'=>'', 'hypervisor'=>'', 'timepointuuid'=>''),array())
     */
    public function deleteSelectTimepoint($params)
    {
        $pointList = $params['pointList'];
        $agentList = $params['agentList'];
        $selectTimepoints = array();
        if (!empty($agentList)) {
            foreach ($agentList as $agent) {
                $selectTimepoints = $this->getTimepointByNas($agent, $params['storage_uuid']);
                $pointList = array_merge($pointList, $selectTimepoints);
            }
        }
        $utils = Xphp::instance('Utils');
        $pointList = $utils->arraySort($pointList, 'nodeuuid', '', 0, -1);
        $info = array();
        $nodeuuids = array();
        $timepointuuids = array();
        $timepointuuid = array();
        // 日志信息
        $details = '';
        $i = 0;
        foreach ($pointList as $d) {
            $i++;
            if (!in_array($d['nodeuuid'], $nodeuuids)) {
                $nodeuuids[] = $d['nodeuuid'];
                $info[] = array(
                    "nodeuuid" => $d['nodeuuid'],
                    "type" => $d['type']
                );
                if (!empty($timepointuuid)) {
                    $timepointuuids[] = $timepointuuid;
                    $timepointuuid = array();
                }

                $timepointuuid[] = $d['timepointuuid'];
            } else {
                $timepointuuid[] = $d['timepointuuid'];
            }
            // 时间点信息
            $details .= $this->getPointDetails($i, $d['timepointuuid']);
        }
        $opName = 'BD_BACKUP_POINT_OP_DELETE';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);

        // 没有全局观察者操作权限，但是有查看的权限，需要判断下是否全是属于当前用户的
        if (!in_array('global_write', $_SESSION['permissionArr']) && in_array('global_read', $_SESSION['permissionArr'])) {
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $chk_list = $this->dbSelect("select task_name from bd_backup_timepoint where user_uuid != ? and timepoint_uuid in ('" . implode("','", $timepointuuid) . "')", [Xphp::$_user['useruuid']]);
            if (!empty($chk_list)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chk_list, 'task_name')));
                exit($this->muOpResult(false, $operate, $msg . Xphp::$_lang['WEB_ERROR_BD_PRIVILEGE_ERROR'], 'error'));
            }
        }

        $timepointuuids[] = $timepointuuid;
        //检测时间点是否在磁带上
        $fileHandler = Xphp::instance('FileHandler');
        $fileHandler->checkTimepointStorage($timepointuuids[0]);
        $nodeuuidsCount = count($nodeuuids);   //计算nodeuuids数组的长度用于分组发送消息
        //检测是否在任务中  在任务就直接返回
        $uuidList = array();
        foreach ($timepointuuids as $d) {
            $uuidList = array_merge($uuidList, $d);
        }
        // $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
        //检查时间点是否有任务存在
        $countPoint = 0;
        $nodeHandler = Xphp::instance('NodeHandler');
        for ($i = 0; $i < $nodeuuidsCount; $i++) {
            $msg = $timepointuuids[$i];
            $countPoint += count($timepointuuids[$i]);
            $msg = array(
                'timepoint_uuids' => $timepointuuids[$i]
            );
            $msg = json_encode($msg);
            if (empty($nodeuuids[$i])) {
                $nodeuuids[$i] = $nodeHandler->getNodeUUIDWithTimepointUUID($timepointuuids[$i][0]);
            }
            //检查时间点是否有恢复任务正在使用
            $fileHandler = Xphp::instance('FileHandler');
            $fileHandler->checkRecoveryPoint($nodeuuids[$i], $timepointuuids[$i], 'nas_protect_operate');
            $mbResult = $this->mbFSMsg($nodeuuids[$i], $opName, $msg);
        }
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $descriptionParam = array($details, $countPoint, Xphp::$_lang['WEB_PLATFORM_DES_NAS']);
        //返回结果到UI
        if ($result) {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam);
            return $this->muOpResult($result, $operate, $msg, '', 0, '');
        } else {
            $this->systemLog('SYSTEM_LOG_DELETE_BATCH_TIMEPOINT', $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR'], $mbResult['errorCode']);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * @description: 获取时间点信息
     * @param {*} $index
     * @param {*} $pointUUID
     * @return {*}
     */
    private function getPointDetails($index, $pointUUID)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $sql = "select bbt.timepoint, bbt.task_name, bbt.backup_mode, fbt.agent_name, fbt.agent_ip from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = ? and fbt.fs_timepoint_uuid = bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, array($pointUUID));
        $timepointDes = $pfDes['BACKUP_MODE_DES'][intval($data[0]['backup_mode'])] . xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'] . '：' . $data[0]['timepoint'];
        if ($index > 1) {
            $details = "\n" . xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_NAS'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_STORAGE_SHARED_FOLDERS'] . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        } else {
            $details = xphp::$_lang['UI_PUBLIC_TASK_RNAME'] . "：" . $data[0]['task_name'] . "，" . xphp::$_lang['UI_PUBLIC_MODULE_TYPE'] . "：" . xphp::$_lang['WEB_PLATFORM_DES_NAS'] . "，" . $timepointDes . "，" . xphp::$_lang['UI_STORAGE_SHARED_FOLDERS'] . "：" . $data[0]['agent_name'] . "(" . $data[0]['agent_ip'] . ")";
        }
        return $details;
    }
    /**
     * 副本获取删除的主机对应时间点信息---nas副本备份数据
     * 根据类型和任务筛选
     * @param array $info
     */
    public function getTimepointByCopyNas($fsinfo, $storageuuid)
    {
        $info = array();
        $sql = "select bbt.timepoint_uuid from bd_backup_timepoint bbt, fs_backup_timepoint fbt where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ? and fbt.agent_uuid = ? and available_flag = 1";
        $params = array($fsinfo['taskuuid'], $fsinfo['agentuuid']);
        if (!empty($storageuuid)) {
            $sql .= " and bbt.storage_uuid = ?";
            $params = array_merge($params, array($storageuuid));
        }
        $sql .= " group by bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, $params);
        foreach ($data as $d) {
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $fsinfo['nodeuuid'],
                'type' => $fsinfo['type']
            );
        }
        return $info;
    }

    /**
     * 获取删除的主机对应时间点信息--nas备份数据
     * @param array $info
     */
    public function getTimepointByNas($fsinfo, $storageuuid)
    {
        $info = array();
        $sql = "select bbt.timepoint_uuid, bsr.node_uuid,bbt.real_node_uuid,bbt.storage_uuid from bd_backup_timepoint bbt,bd_storage_resource bsr,
        fs_backup_timepoint fbt where bbt.storage_uuid = bsr.storage_uuid and bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ? and fbt.agent_uuid = ? and available_flag = 1";
        $params = array($fsinfo['taskuuid'], $fsinfo['agentuuid']);
        if (!empty($storageuuid)) {
            $sql .= " and bsr.storage_uuid = ?";
            $params = array_merge($params, array($storageuuid));
        }
        $sql .= " group by bbt.timepoint_uuid";
        $data = $this->dbSelect($sql, $params);
        foreach ($data as $d) {
            $nodeUuid = $d['node_uuid'];
            if (!empty($d['real_node_uuid'])) {
                $nodeUuid = $d['real_node_uuid'];
            }
            $info[] = array(
                'timepointuuid' => $d['timepoint_uuid'],
                'nodeuuid' => $nodeUuid,
                'type' => $fsinfo['type']
            );
        }
        return $info;
    }

    /**
     * 获取nas时间点列表信息
     * @param unknown $params
     */
    public function getNasTimepointGrid($params)
    {
        $storageUuid = $params['storage_uuid'];
        $start = intval($params['start']);
        $length = intval($params['length']);
        $draw = $params['draw'];
        $agentuuid = $params['agentuuid'];
        $taskuuid = $params['taskuuid'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $search = $params['search'];
        $copyFlag = $params['copyflag'];        //副本数据标志
        $archiveFlag = $params['archiveFlag'];  //归档数据标志
        $localflag = intval($params['localflag']); //本地标志
        $sortArr = array(
            'bbt.timepoint',
            'bbt.backup_mode',
            'bbt.total_size',
            'bbt.write_size',
            '',
            'bbt.remarks',
            '',
            'bbt.importance_flag'
        );

        //         编号	时间点	类型	数据大小	用户	备注	操作	星标
        $sql = 'select bsr.storage_type, bsr.node_uuid, bbt.real_node_uuid, bbt.storage_uuid, bbt.timepoint_uuid, unix_timestamp(bbt.timepoint) timepoint, bbt.backup_mode, bbt.total_size, bbt.write_size, bbt.data_local_flag, bbt.importance_flag, bbt.remarks,bbt.src_data_deleted_flag, bbt.user_uuid from bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ? and bbt.task_uuid = ? and fst.agent_uuid = ?';
        $sqlCount = "select count(bbt.id) as total from bd_backup_timepoint bbt, fs_backup_timepoint fst, bd_storage_resource bsr where bbt.timepoint_uuid = fst.fs_timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid and bbt.deleted_flag = ? and bbt.import_flag = ? and bbt.available_flag = ? and bbt.task_uuid = ? and fst.agent_uuid = ?";
        $flag = Xphp::$_config['FLAG'];
        $sqlParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $taskuuid, $agentuuid);
        $sqlCountParams = array($flag['UNSET'], $flag['UNSET'], $flag['SET'], $taskuuid, $agentuuid);
        //如果带有搜索条件
        if (!empty($search)) {
            //如果开始时间和结束时间都有 则添加时间查询
            if (!empty($search['startTime']) && !empty($search['endTime'])) {
                $sql .= " and bbt.timepoint between ? and ? ";
                $sqlCount .= " and bbt.timepoint between ? and ? ";
                $sqlParams = array_merge($sqlParams, array($search['startTime'], $search['endTime']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['startTime'], $search['endTime']));
            }
            //如果有类型 则添加类型
            if (!empty($search['timepointType'])) {
                $sql .= " and bbt.backup_mode = ? ";
                $sqlCount .= " and bbt.backup_mode = ? ";
                $sqlParams = array_merge($sqlParams, array($search['timepointType']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['timepointType']));
            }
            //如果有永久标记点 则添加永久标记的搜索
            if (!empty($search['forever'])) {
                $sql .= " and bbt.importance_flag = ? ";
                $sqlCount .= " and bbt.importance_flag = ? ";
                $sqlParams = array_merge($sqlParams, array($search['forever']));
                $sqlCountParams = array_merge($sqlCountParams, array($search['forever']));
            }
        }
        //如果切换了节点
        if ($storageUuid) {
            $sql .= " and bsr.storage_uuid = ?";
            $sqlCount .= " and bsr.storage_uuid = ?";
            $sqlParams = array_merge($sqlParams, array($storageUuid));
            $sqlCountParams = array_merge($sqlCountParams, array($storageUuid));
        }
        if ($copyFlag || $archiveFlag) {
            if ($storageUuid && !empty($storageUuid)) {
                $sql .= " and bsr.storage_uuid = ? ";
                $sqlCount .= " and bsr.storage_uuid = ? ";
                $sqlParams = array_merge($sqlParams, array($storageUuid));
                $sqlCountParams = array_merge($sqlCountParams, array($storageUuid));
            }
        }
        //区分异地还是本地
        if (!empty($localflag)) {
            $sql .= " and bbt.data_local_flag = ? ";
            $sqlParams = array_merge($sqlParams, array($localflag));
        }

        $sqlParams = array_merge($sqlParams, array($start, $length));

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $utils = Xphp::instance('Utils');
        $vmHandler = Xphp::instance('Vmhandler');
        $fileHandler = Xphp::instance('FileHandler');
        $records = array("data" => array());
        $i = 1;
        $userUuidList = array_column($data, 'user_uuid');
        $userUuidList = array_values(array_unique($userUuidList));
        $userUuids = "'" . implode("', '", $userUuidList) . "'";
        $sql = "select user_name, user_uuid from bd_user where user_uuid in ($userUuids)";
        $userData = $this->dbSelect($sql);
        $userMapping = [];
        foreach ($userData as $userInfo) {
            $userMapping[$userInfo['user_uuid']] = $userInfo['user_name'];
        }
        foreach ($data as $d) {
            $nodeuuid = $d['real_node_uuid'];
            if (empty($d['real_node_uuid'])) {
                $nodeuuid = $d['node_uuid'];
            }
            $mark = $vmHandler->pGetTimepointMark($utils->parseFlagToBool($d['weekly_flag']), $utils->parseFlagToBool($d['monthly_flag']), $utils->parseFlagToBool($d['yearly_flag']), $utils->parseFlagToBool($d['importance_flag']));
            $remark = "";   //备注
            $remotFlag = !$utils->parseFlagToBool(intval($d['data_local_flag']));
            $op = array(1, 2, 3);
            if ($remotFlag) { //如果是异地副本，不能设置星标
                $op = array(1, 2);
            }

            //1、增备和差备只能备注、2、时间点在合并中只能备注 3、磁带存储类型只能备注
            if ($d['backup_mode'] == Xphp::$_config['BACKUP_MODE']['INCREMENTAL'] || $d['backup_mode'] == Xphp::$_config['BACKUP_MODE']['DIFFERENTIAL']
                || $d['archive_flag'] == Xphp::$_config['FLAG']['SET']
                || $d['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['TAPE']) {
                $op = array(1);
            }

            //添加备注
            if (!empty($d['remarks'])) {
                //根据标记是否显示固定备注显示高度
                $top = "";
                if (!empty($mark)) {
                    $top = "top:-4px;";
                }
                $remark .= '<a class="popovers remarktips" data-container="body" data-trigger="hover"
                            data-placement="right" data-content="' . $d['remarks'] . '"><i class="viconfont vicon-remark-info"></i></a>';
            }
            $records["data"][] = array(
                '<span title="' . $d['timepoint_uuid'] . '">' . $this->parseDate($d['timepoint']) . '</span><br>' . $mark . $remark,  //隐藏展示时间点uuid出来,方便运维
                $this->getTimepointTypeDes($d['backup_mode'], $d['db_type']),
                $utils->calSize($d['total_size'], true),
                $utils->calSize($d['write_size'], true),
                $fileHandler->getStorageName($d['storage_uuid'], $d['timepoint_uuid']),
                //所有者
                $userMapping[$d['user_uuid']],
                $op,
                $d['importance_flag'],
                array(
                    'uuid' => $d['timepoint_uuid'],
                    // 'dbtype'=>$d['db_type'],
                    'mode' => $d['backup_mode'],
                    'taskuuid' => $taskuuid,
                    'remote_flag' => !$utils->parseFlagToBool(intval($d['data_local_flag'])),
                    'weekly_flag' => $utils->parseFlagToBool($d['weekly_flag']),
                    'monthly_flag' => $utils->parseFlagToBool($d['monthly_flag']),
                    'yearly_flag' => $utils->parseFlagToBool($d['yearly_flag']),
                    'importance_flag' => $utils->parseFlagToBool($d['importance_flag']),
                    'remark' => $d['remarks'],
                    'nodeuuid' => $nodeuuid,
                    'src_data_deleted_flag' => $d['src_data_deleted_flag'],
                ),
            );
        }
        $records["draw"] = $params['draw'];
        ;
        $records["recordsTotal"] = $dataCount[0]['total'];
        $records["recordsFiltered"] = $dataCount[0]['total'];

        return json_encode($records);
    }

    /**
     * 得到备份时间点的类型
     * @param int $bakcupMode
     * @return string
     */
    public function getTimepointTypeDes($bakcupMode, $db_type)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = '';
        if ($bakcupMode == 4) {
            if ($db_type == Xphp::$_config['DB_TYPE']['DM'] || $db_type == Xphp::$_config['DB_TYPE']['ORACLE']) {
                $des = $pfDes['BACKUP_MODE_DES'][5];
            } else {
                $des = $pfDes['BACKUP_MODE_DES'][$bakcupMode];
            }
        } else {
            $des = $pfDes['BACKUP_MODE_DES'][$bakcupMode];
        }
        $des .= Xphp::$_lang['WEB_PLATFORM_DATA_BACKUP_POINT'];
        return $des;
    }
    /**
     * 修改任务,得到备份任务的所有信息
     * @param unknown $params
     */
    public function getBackupTaskAllInfo($params)
    {
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid,bt.strategy_group_uuid, bt.agent_uuid, bt.thread_num,
        bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,bt.ignore_resource_limiting_flag,bt.node_pool_uuid, bt.storage_pool_uuid,
        brs.strategy_type, brs.number, brs.strategy_mode,nt.nas_uuid, nt.detail as ntdetail,nt.file_archive_flag,nt.skip_file_alarm_flag,nt.skip_file_alarm_min_num,nt.skip_file_alarm_min_ratio, 
        bss.compressed_flag, bss.compress_method, bss.encrypted_flag,bss.password,bss.password_auto_flag, bss.encrypt_method,
        nt.permission_operate_flag,nt.snap_shot_flag,bss.compressed_flag, bss.compress_method, bss.encrypted_flag,bss.password,bss.password_auto_flag,
        btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,btsc.backup_integrity_check_inc_error_policy
        from bd_task bt, bd_reserved_strategy brs, bd_storage_strategy bss, nas_task nt, bd_task_safe_config btsc
        where bt.strategy_id = brs.strategy_id
        and bt.strategy_id = bss.strategy_id
		and bt.task_uuid = nt.task_uuid
        and bt.task_uuid = btsc.task_uuid
        and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $sqlwild = 'select detail from bd_task_agent_list where task_uuid = ?';
        $sqlwilddata = $this->dbSelect($sqlwild, array($taskUUID));
        $info = array();
        $vmHandler = Xphp::instance('Vmhandler');
        $utils = Xphp::instance('Utils');
        if ($data) {
            $wildcardinfo = array();
            foreach ($sqlwilddata as $d) {
                array_push($wildcardinfo, json_decode($d['detail'], true));
            }
            $ntdetail = json_decode($data[0]['ntdetail'], true);
            // 查询存储池类型
            $storagePoolType = 0;
            if ($data[0]['storage_pool_uuid']) {
                $storagePoolData = $this->dbSelect("SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ", [$data[0]['storage_pool_uuid']]);
                $storagePoolType = $storagePoolData[0]['storage_pool_type'];
            }
            $info = array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //策略uuid
                "strategyuuid" => $data[0]['strategy_group_uuid'],
                //任务名
                'taskname' => $data[0]['task_name'],
                // //nasUID
                'nasuuid' => $data[0]['nas_uuid'],
                //level
                'level' => $data[0]['level'],
                //文件信息
                'fileinfo' => $this->getFileBackupFiles($taskUUID),
                //节点
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'storageuuid' => $data[0]['storage_uuid'],
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    'storage_pool_type' => $storagePoolType,  // 需要返回存储池类别
                ),
                //保留策略
                'brs' => array(
                    'type' => $data[0]['strategy_type'],
                    'value' => $data[0]['number'],
                    'strategy_mode' => $data[0]['strategy_mode'],
                ),
                //存储策略
                'bss' => array(
                    'compress' => $utils->parseFlagToBool($data[0]['compressed_flag']),
                    'encrypt' => $utils->parseFlagToBool($data[0]['encrypted_flag']),
                    'password_auto_flag' => $utils->parseFlagToBool($data[0]['password_auto_flag']),
                    'password' => base64_encode($utils->ptPassDecrypt($data[0]['password'])),
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method'])
                ),
                //时间策略
                'timestrategy' => $vmHandler->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                'speedInfo' => $vmHandler->getSpeedGlobalStrategyInfo($taskUUID),
                //高级策略
                'high' => array(
                    'thread_num' => $data[0]['thread_num'],
                    'snap_shot_flag' => $utils->parseFlagToBool($data[0]['snap_shot_flag']),
                    'wildcardinfo' => $wildcardinfo,
                    'scan_thread_num' => $ntdetail['scan_thread_num'],
                    'scan_file_num' => $ntdetail['scan_file_num'],
                    'file_archive_flag' => $data[0]['file_archive_flag'],
                    'skip_file_alarm_flag' => $data[0]['skip_file_alarm_flag'] == 1 ? true : false,
                    'skip_file_alarm_min_num' => $data[0]['skip_file_alarm_min_num'],
                    'skip_file_alarm_min_ratio' => $data[0]['skip_file_alarm_min_ratio'],
                    'permission_operate_flag' => $data[0]['permission_operate_flag'] == 1 ? true : false,
                ),
                'safeStrategy' => array(
                    //获取worm开关
                    'worm_flag' => $utils->parseFlagToBool($data[0]['worm_flag']),
                    //获取worm保护期限
                    'worm_protection_time' => intval($data[0]['worm_protection_time']),
                    //获取病毒是否开关
                    'virus_scan_flag' => $utils->parseFlagToBool($data[0]['virus_scan_flag']),
                    //获取病毒检测配置
                    'virus_scan_config_list' => json_decode($data[0]['virus_scan_config_list'], true),  
                    //获取完整性效验开关
                    'integrity_check_flag' => $utils->parseFlagToBool($data[0]['integrity_check_flag']),
                    //获取完整性校验数据
                    'integrity_check_config' => array(
                        //获取效验周期
                        'check_strategy' => intval($data[0]['integrity_check_strategy']),
                        //获取完全备份点异常
                        'full_error_policy' => intval($data[0]['backup_integrity_check_full_error_policy']),
                        //获取其他备份点异常
                        'inc_error_policy' => intval($data[0]['backup_integrity_check_inc_error_policy']),
                    ),
                ),
                //重试策略
                'retry_strategy' =>$this->getRetryStrategy($taskUUID),
                'ignore_resource_limiting_flag' => $utils->parseFlagToBool($data[0]['ignore_resource_limiting_flag']),
            );

        }
        return json_encode($info);
    }
    /**
     * 根据任务ID得到nas备份备份列表信息
     * @param unknown $taskuuid
     */
    private function getFileBackupFiles($taskuuid)
    {
        $sql = "select fpl.path_name, fpl.path_type, nt.nas_uuid from fs_path_list fpl, nas_task nt where fpl.task_uuid=nt.task_uuid and fpl.task_uuid = ?;";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        foreach ($data as $d) {
            $filename = $this->getFileName(intval($d['path_type']), $d['path_name']);
            $info[] = array(
                'nasuuid' => $d['nas_uuid'],
                'type' => $d['path_type'],
                'name' => $filename,
                'path' => $d['path_name'],
                'sclass' => $this->getFileClassName($d['path_type'], $filename, 's'),
            );
        }
        return $info;
    }
    /**
     * 根据路径得到文件名
     * @param unknown $path
     */
    private function getFileName($pathType, $pathName)
    {
        $pathArr = explode("/", $pathName);
        $arrLen = count($pathArr);
        if (Xphp::$_config['FILETYPE']['FILE'] == $pathType) {
            //文件
            $filename = $pathArr[$arrLen - 1];
        } else {
            //目录
            $filename = $pathArr[$arrLen - 2];
        }
        return $filename;
    }

    /**
     * 转化时间策略天数为一个数组,每一项为boll
     * @param unknown $days
     */
    private function parseTimeStrategyDay($days)
    {
        if (empty($days)) {
            $daysArr = array();
            return $daysArr;
        }
        $daysArr = str_split($days);
        $trueDays = array();
        foreach ($daysArr as $key => $d) {
            //如果遇到s,结束    现在每周存储格式为0000001s1   s1表示间隔一周,以此类推
            if ("s" == $d) {
                break;
            }
            if ($d) {
                $trueDays[$key] = true;
            } else {
                $trueDays[$key] = false;
            }
        }
        return $trueDays;
    }
    /**
     * 得到每周的执行间隔
     * 目前只有每周会返回数据,
     * @param int $StrategyType
     * @param string $days
     * @return string  空字符串  s1 - s4
     */
    private function parseTimeStrategyFrequency($strategyType, $days)
    {
        $frequency = "";
        if (Xphp::$_config['STRATEGY_TYPE']['EVERY_WEEK'] != intval($strategyType)) {
            return $frequency;
        }
        $strIndex = strpos($days, "s");
        if ($strIndex) {
            $frequency = substr($days, $strIndex);
        }
        return $frequency;
    }
    /**
     * 得到修改nas备份任务nas设备树
     * @param unknown $params
     */
    public function getBackupTreeOldInfo($params)
    {
        $nasuuid = $params['nasuuid'];
        $utils = Xphp::instance('Utils');
        $agentList = $utils->object_array(json_decode($this->getNasBackupTree()));
        if (!empty($agentList)) {
            foreach ($agentList as $key => $node) {
                if ($nasuuid == $node["uuid"]) {
                    $agentList[$key]["checked"] = true;
                }
            }
        }
        return json_encode($agentList);
    }
    /**
     * 修改备份任务，比创建备份多传一个taskuuid
     * @param unknown $params
     * @return string
     */
    public function editFsBackupJob($params)
    {
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $utils = Xphp::instance('Utils');
        $expireDate = $utils->getExpireDays();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_LICENSE_AUTH_INFO_EXPIRED'] , "warning"));
        }
        //public params
        $task_name = $params['taskName'];
        $vmHandler = Xphp::instance('Vmhandler');
        $jobHandler = Xphp::instance('JobHandler');
        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['strategygroupuuid'];
        $this->paramsCheck($task_name);
        $module_type = Xphp::$_config['MODULE_TYPE']['NAS'];
        //租户内检查可用数量是否超过授权个数
        if(!empty($_SESSION['tenantuuid'])){
            $tenantHandler = Xphp::instance('TenantHandler');
            $tenantHandler->checkTenantAuth(Xphp::$_config['MODULE_TYPE']['NAS'], array($params['srcInfo']['nasuuid']), $params['taskuuid']);
        }
        //组合备份方式完备差备等
        $time_strategy_list = $this->groupBackupTimeList($params['strategyInfo']['time']);
        //组合保留策略
        $reserver_strategy = $this->groupReserverStrategy($params['strategyInfo']['reserve']['reserveInfo']);
        //组合传输策略
        $transport_strategy = $this->groupTransportStrategy($params['highInfo']['transfer']);
        //组合存储策略
        $storage_strategy = $this->groupStorageStrategy($params['strategyInfo']['store']['storeInfo']);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $task_name,
            $module_type,
            $time_strategy_list,
            $reserver_strategy,
            $transport_strategy,
            $storage_strategy,
            $nodeInfo,
            $params['strategyInfo']['time']['type']
        );
        //得到任务类型
        $pfMsg['task_type'] = Xphp::$_config['TASKTYPE']['BACKUP'];
        $pfMsg['task_uuid'] = $params['taskuuid'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupNAS($params['srcInfo']['fileInfo'], $params['srcInfo']['nasuuid']);
        $pfMsg['snap_shot_flag'] = $params['highInfo']['snap_shot_flag'] ? 1 : 2; //快照
        $pfMsg['thread_num'] = $params['highInfo']['newstr']['backupThreadNum']; //线程数量
        $pfMsg['scan_thread_num'] = $params['highInfo']['newstr']['scanThreadNum']; //扫描线程数量
        $pfMsg['scan_file_num'] = $params['highInfo']['newstr']['scanFileNum']; //扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['highInfo']['newstr']['wildcard_list']); //通配符
        //限速策略
        $pfMsg['speed_limit_strategy_list'] = $vmHandler->groupTaskSpeedList($params['speedInfo'], $strategygroupuuid);
        // 全局限速策略
        $pfMsg['speed_limit_strategy'] = $vmHandler->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        $pfMsg['nas_uuid'] = $params['srcInfo']['nasuuid'];
        $pfMsg['file_archive_flag'] = $params['highInfo']['file_archive']; //归档
        $pfMsg['skip_file_alarm_flag'] = $params['highInfo']['skip_file_alarm_flag'] ? 1 : 2; //跳过文件告警开关
        $pfMsg['permission_operate_flag'] = $params['highInfo']['permission_operate_flag'] ? 1 : 2; //文件权限备份
        $pfMsg['skip_file_alarm_min_num'] = $params['highInfo']['skip_file_alarm_min_num']; //跳过文件告警个数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['highInfo']['skip_file_alarm_min_ratio']; //跳过文件告警比例
         // 忽略节点资源限制
         $pfMsg['ignore_resource_limiting_flag'] = $utils->parseBoolToFlag($params['highInfo']['ignore_resource_limiting_flag']);
        //得到安全策略
        $pfMsg['safe_config_strategy'] = $jobHandler->groupSafeConfigStrategy($params['safe_strategy'], $params['highInfo']['node']['storageuuid']);
        $pfMsg['retry_strategy'] =  $jobHandler->groupRetryStrategy($params['retry_strategy']);
        $pfMsg['submodule_type'] = Xphp::$_config['SUBMODULE_TYPE']['NAS'];
        $msg = json_encode($pfMsg);
        $nodeHandler = Xphp::instance('NodeHandler');
        if ($pfMsg['node_uuid']) {
            $nodeuuid = $nodeInfo['node_uuid'];
        } else {
            $nodeuuid = $nodeHandler->getMasterNodeUuid();
        }
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 得到所有挂载成功节点，用于备份第二步检测节点是否挂载
     * @param unknown $params
     */
    public function getAllMountNode($params)
    {
        $sql = 'select node_uuid from nas_mount_list where nas_uuid = ?';
        $data = $this->dbSelect($sql, array($params['nas_uuid']));
        $nodeList = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $nodeList[] = $d['node_uuid'];
            }
        }
        return json_encode($nodeList);
    }

    /**
     * 得到所有挂载成功节点和节点的agent_uuid，用于备份第一步扫描目录 消息发往各个节点
     * @param unknown $params
     */
    public function getMountNode($nasuuid)
    {
        $sql = 'select node_uuid, agent_uuid from nas_mount_list where nas_uuid = ?';
        $data = $this->dbSelect($sql, array($nasuuid));
        $nodeList = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $nodeList[] = array(
                    'node_uuid' => $d['node_uuid'],
                    'agent_uuid' => $d['agent_uuid'],
                );
            }

        }
        return $nodeList;
    }

    /**
     * 文件恢复搜索---创建搜索消息
     * @param unknown $params
     */
    public function createSearchJob($params)
    {
        $opName = 'FS_OP_TYPE_SEARCH_THREAD_CREATE';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['info']['search_timepoint']);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($params['info']), true, true);
        if ($mbResult['result']) {
            $info = array(
                "re" => true,
                "thread_uuid" => $mbResult['msg']['task_uuid'],
            );
        } else {
            //失败
            return $this->muOpResult($mbResult['result'], $opName, '', '', $mbResult['errorCode']);
        }

        return json_encode($info);
    }

    /**
     * 文件恢复搜索---获取搜索结果
     * @param unknown $params
     */
    public function getRecoSearchInfo($params)
    {
        $opName = 'FS_OP_TYPE_SEARCH_RESULT_GET';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['search_timepoint']);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($params['info']), true, false);
        $pfOpcode = Xphp::instance('PFOpcode');
        $utils = Xphp::instance('Utils');
        $operate = $pfOpcode->getOpcodeDes($opName);
        $result = $mbResult['result'];
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $mbResult['errorCode']);
        }
        $result = $mbResult['msg'];
        $filenode = array();
        foreach ($result['item_list'] as $file) {
            if ($file['file_type'] == 1) { //文件
                $titleDes = Xphp::$_lang['UI_BR_AUTOBAK_LIST_FILESIZE'] . ':'  . $utils->calSize($file['file_size'], true) . PHP_EOL . Xphp::$_lang['UI_DATA_FILE_MODIFY_TIME'] . ':' . $this->parseDate($file['modify_time']);
            } else {
                $titleDes = Xphp::$_lang['UI_DATA_FILE_MODIFY_TIME'] . ':' . $this->parseDate($file['modify_time']);
            }
            $filenode[] = array(
                "pid" => 0,
                "id" => $file['path_name'],
                "name" => $file['path_name'],
                "title" => $titleDes,
                "file_type" => $file['file_type'],
                "md5High" => $file['md5_high'],
                "md5Low" => $file['md5_low'],
                "file_size" => $file['file_size'],
                "modify_time" => $file['modify_time'],
                "file_offset" => $file['offset'],
                "icon" => $file['file_type'] == 2 ? "./img/fs/wenjianjia.png" : "./img/fs/wenjian.png",
                "iconOpen" => $file['file_type'] == 2 ? "./img/fs/wenjianjiaopen.png" : "./img/fs/wenjian.png",
                "iconClose" => $file['file_type'] == 2 ? "./img/fs/wenjianjia.png" : "./img/fs/wenjian.png",
                "isParent" => $this->getBoolType($file['file_type']),
                "path" => $file['path_name'],
                "pointuuid" => $params['search_timepoint'],
                "md5Flag" => '1',
                "searchNode" => true,
            );

        }
        $info = array(
            "re" => true,
            "search_finish_flag" => $result['finish_flag'],
            "current_total_num" => $result['number'],
            "current_dir_num" => $result['dir_num'],
            "current_file_num" => $result['file_num'],
            "all_file_num" => $result['all_file_num'],
            "all_dir_num" => $result['all_dir_num'],
            "offset" => $result['offset'],
            "filenode" => $filenode,
        );
        return json_encode($info);
    }

    /**
     * 文件恢复搜索---停止搜索
     * @param unknown $params
     */
    public function stopSearchJob($params)
    {
        $opName = 'FS_OP_TYPE_SEARCH_STOP';
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTimepointUUID($params['search_timepoint']);
        $mbResult = $this->mbFSMsg($nodeuuid, $opName, json_encode($params['info']));
        if ($mbResult['result']) { //成功
            $info = array(
                "re" => true,
            );
            return json_encode($info);
        } else {
            //失败
            return $this->muOpResult($mbResult['result'], $opName, '', '', $mbResult['errorCode']);
        }
    }

    /**
     * 根据nas uuid 获取当前的使用者是谁 只查询分配的资源/组
     * @param string $nasUuid nas设备uuid
     * @param string $userName  拥有者名称
     * @return string
     */
    public function getNasUuidName(string $nasUuid, string $userName = ''): string
    {
        $sqlParams = [$nasUuid];
        // 先 再分配的资源和资源组去查询
        $sql2 = "select user_uuid from mt_user_resource where resource_type = 57 and resource_uuid = ? limit 1";
        $array = $this->dbSelect($sql2, $sqlParams);
        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 57";
            $array = $this->dbSelect($sql, $sqlParams);
        }
        if ($array) {
            $sql = "select user_name from bd_user where user_uuid = ?";
            $data = $this->dbSelect($sql, [$array[0]['user_uuid']]);
            $userName = $data[0]['user_name'];
        }
        return $userName;
    }
    
    /**
     * 任务详情-根据任务uuid获取重试策略相关信息
     * @param string $taskUUID 任务uuid
     * @return object 重试策略相关信息
     */
    public function getRetryStrategy($taskUUID) {
        $info = array();
        $sql = "select network_retry_times,network_retry_interval,op_retry_times,op_retry_interval,task_retry_object,
            task_retry_times, task_retry_interval from bd_retry_strategy where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            //操作重试开关
            $opRetryFlag = true;
            if ($data[0]['op_retry_times'] == 0) {
                $opRetryFlag = false;
            }
            //任务重试开关
            $taskRetryFlag = true;
            if ($data[0]['task_retry_times'] == 0) {
                $taskRetryFlag = false;
            }
            $info = array(
                'network_retry_times' => $data[0]['network_retry_times'],
                'network_retry_interval' => $data[0]['network_retry_interval'],
                'op_retry_flag' => $opRetryFlag,
                'op_retry_times' => $data[0]['op_retry_times'],
                'op_retry_interval' => $data[0]['op_retry_interval'],
                'task_retry_flag' => $taskRetryFlag,
                'task_retry_object' => $data[0]['task_retry_object'],
                'task_retry_times' => $data[0]['task_retry_times'],
                'task_retry_interval' => $data[0]['task_retry_interval'],
            );
        }
        return $info;
    }
}
