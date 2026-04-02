<?php
/******************************************* 
 ** 数据库CDP备份处理类 
 ** 
 ** @author       xiezhuowei@vinchin.com
 ** @date         2019-11-07 下午06:18:21 
 ** @version      1.0.0 
 ** @copyright    Copyright 2019 vinchin.com 
 ********************************************/
class DbCDPHandler extends OPHandler
{
    /**
     * 创建数据库实时备份任务
     * @param unknown $params
     */
    public function createBackupJob($params)
    {
        $productHostuuid = $params['producthostInfo']['hostuuid'];
        $standbyHostuuid = $params['standbyhostInfo']['standbyhost'];
        $productHostInfo = $this->getHostInfoWithUUID($productHostuuid);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostuuid);
        $storageuuid = $params['standbyhostInfo']['storageuuid'];
        $nodeuuid = $params['standbyhostInfo']['backupHostIsServer']['nodeuuid'];

        $operate = '创建数据库实时备份任务';
        $taskuuid = Xphp::instance('Utils', 'uuid');

        //一个主机对应一个备机只能创建一个备份任务,检查这里
        $sql = "select count(cdt.id) as total from cdp_db_task cdt,  bd_task bt where 
                cdt.task_uuid = bt.task_uuid and cdt.product_host_uuid = ? and 
                cdt.standby_host_uuid = ? and bt.task_type = ?";
        $data = $this->dbSelect($sql, array($productHostuuid, $standbyHostuuid, Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP']));
        if ($data[0]['total'] > 0) {
            return $this->muOpResult(false, $operate, "对应生产主机和备份主机已经有备份任务存在!", "warning");
        }

        $productHostIP = $productHostInfo['ip'];
        $standbyHostIP = $standbyHostInfo['ip'];

        //停止主站备份系统
//         $this->stopBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);
        //先清理之前的任务.
        if (!$this->clearOldBackupInfo($productHostuuid, $standbyHostuuid)) {
            return $this->muOpResult(false, $operate, "检查环境并清理失败,请重试", "warning");
        }

        $prefixDir = $this->getStorageMountPoint($params['standbyhostInfo']['storageuuid'], $taskuuid);
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
            'taskname' => 'taskname',
            'dbInfo' => $params['producthostInfo']['dbInfo'],
            'instanceInfo' => $params['producthostInfo']['instanceInfo'],
            'backupType' => $params['standbyhostInfo']['backuptype'],
            'prefixdir' => $prefixDir,
            'backupdir' => $params['standbyhostInfo']['backupdir'],
            'historydir' => $params['standbyhostInfo']['historydir'],
            'hiscopys' => $params['standbyhostInfo']['historycopys'],        //历史份数
            "mirrdir" => '',        //镜像目录,全路径
//             'logSetting' => $params['highInfo']['log'],
            'productHostInfo' => array(
                'hipstr' => $productHostIP,
                'hport' => 0,
                'huser' => 'admin',
                'hpass' => 'admin',
            )
        );
        //设定远程数据库备份日志管理参数
        $this->setBackupLogInfo($productHostIP, $standbyHostIP, $params['highInfo']['log']);

        //发送消息到生产主机
        $result = $rpc->createBackupJob($productHostIP, $data);
        if (!$result['result']) {
            return $this->muOpResult(false, $operate, $productHostIP . $result['errorMsg'], 'warning', $result['errorCode']);
        }

        //发送消息到备份主机
        $data['syscode'] = Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'];
        $result = $rpc->createBackupJob($standbyHostIP, $data);
        if (!$result['result']) {
            return $this->muOpResult(false, $operate, $standbyHostIP . $result['errorMsg'], 'warning', $result['errorCode']);
        }

        //配置接管
        $result = $this->settingTakeoverConfig($productHostInfo, $standbyHostInfo, $params);
        if (true !== $result) {
            return $result;
        }

        //禁用单个从站备份任务
        $this->ControlBackupJob($productHostIP, $standbyHostIP, false);

        //启动主站备份系统
        $this->startBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);

        sleep(5);

        //如果都配置成功,插入信息到数据库
        $taskName = htmlspecialchars_decode($params['jobname']);
        $moduleType = Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'];
        $taskType = Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'];
        $taskStatus = Xphp::$_config['TASKSTATUS']['STOPPED'];
        $createTime = date("Y-m-d H:i:s", time());
        $useruuid = Xphp::$_user['useruuid'];
        $deleteFlag = Xphp::$_config['FLAG']['UNSET'];

        $this->dbBeginTransaction();
        if(!$storageuuid){
            $storageuuid = "";
        }
        if(!$nodeuuid){
            $nodeuuid = "";
        }
        $sql = "insert into bd_task(task_uuid, task_name, module_type, task_type, task_status, create_time, 
                      user_uuid, delete_flag,node_uuid,storage_uuid) values(?, ?, ?, ?, ?, ?, ?, ?,?,?)";
        $sqlParams = array($taskuuid, $taskName, $moduleType, $taskType, $taskStatus, $createTime, $useruuid, $deleteFlag,$nodeuuid,$storageuuid);
        $result = $this->dbQuery($sql, $sqlParams);

        $sql = "insert into bd_running_info(task_uuid) values(?)";
        $sqlParams = array($taskuuid);
        $result = $result && $this->dbQuery($sql, $sqlParams);

        $sql = "insert into cdp_db_task(task_uuid, product_host_uuid, standby_host_uuid, config) values(?, ?, ?, ?)";
        $sqlParams = array($taskuuid, $productHostuuid, $standbyHostuuid, json_encode($params, true));
        $result = $result && $this->dbQuery($sql, $sqlParams);

        if ($result) {
            $this->dbCommit();
            $this->writeTaskLog($taskuuid, $taskName, $taskType, 'BD_TASKLOG_DESC_KEY_CREATE_TASK_SUCCESS');
        } else {
            $this->dbRollBack();
            $this->writeTaskLog($taskuuid, $taskName, $taskType, 'BD_TASKLOG_DESC_KEY_CREATE_TASK_FAILURE');
        }

        return $this->muOpResult($result, '创建数据库CDP实时备份任务');
    }

    /**
     * 启用或禁用对应主机的备份任务
     * @param unknown $productHostIP
     * @param unknown $standbyHostIP
     * @param unknown $flag             true 启用  false禁用
     */
    private function ControlBackupJob($productHostIP, $standbyHostIP, $flag)
    {
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hipstr' => $productHostIP,
            'port' => 0,
            'username' => 'admin',
            'password' => 'admin',
            'disable' => $flag ? 0 : 1,
        );
        $operate = "设置备份任务启用状态";
        $result = $rpc->ChgConnectHost($standbyHostIP, $data);
        return $this->checkRPCMsg($operate, $result);
    }

    /**
     * 设置日志保留策略
     * @param unknown $logInfo
     */
    private function setBackupLogInfo($productHostIP, $standbyHostIP, $logInfo)
    {
        $logmode = intval($logInfo['logmode']);
        $maxstep = intval($logInfo['maxstep']);
        //大小按MB计算
        $maxsize = intval($logInfo['maxsize']) * pow(1024, intval($logInfo['sizeunits']));
        $maxhour = intval($logInfo['maxhour']);


        if (2 == $logmode) {
            //如果是自适应，maxsize为0
            $maxsize = 0;
        }
        $operate = "设定远程数据库备份日志管理参数";
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hipstr' => $productHostIP,
            'logmode' => $logmode,
            'maxstep' => $maxstep,
            'maxsize' => $maxsize,
            'maxhour' => $maxhour,
            //下面这两项暂时不用
            'maxdhour' => 1000,
            'maxdsize' => 1000
        );
        $result = $rpc->setRemoteDbArcMgParam($standbyHostIP, $data);
        $data = $this->checkRPCMsg($operate, $result);
        return true;
    }

    /**
     * 修改实时备份任务
     * @param unknown $params
     */
    public function modifyBackupJob($params)
    {
        $productHostuuid = $params['producthostInfo']['hostuuid'];
        $standbyHostuuid = $params['standbyhostInfo']['standbyhost'];
        $productHostInfo = $this->getHostInfoWithUUID($productHostuuid);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostuuid);
        $productHostStatus = intval($productHostInfo['status']);
        $standbyHostStatus = intval($standbyHostInfo['status']);
        $productHostIP = $productHostInfo['ip'];
        $standbyHostIP = $standbyHostInfo['ip'];

        $operate = '修改数据库实时备份任务';
        $taskuuid = $params['taskuuid'];

        //如果主站和从站任意一个不在线,直接返回,不做修改
        if (
            Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $productHostStatus ||
            Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus
        ) {
            return $this->muOpResult(false, $operate, "请检查生产主机/备份主机不在线,请检查!", "warning");
        }

        //清理接管
        $this->clearnTakeoverEvn($standbyHostIP, $taskuuid);

        //删除释放停止备份的数据库状态队列
        $rpc = Xphp::instance('DbRPCHandler');
        $dataMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hostip' => $productHostIP,
            'remoteip' => $standbyHostIP,
        );
        $result = $rpc->freeBackupStopDbQuen($standbyHostIP, $dataMsg);

        //清理之前的任务.
        $this->clearOldBackupInfo($productHostuuid, $standbyHostuuid);


        $prefixDir = $this->getStorageMountPoint($params['standbyhostInfo']['storageuuid'], $taskuuid);
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
            'taskname' => 'taskname',
            'dbInfo' => $params['producthostInfo']['dbInfo'],
            'instanceInfo' => $params['producthostInfo']['instanceInfo'],
            'backupType' => $params['standbyhostInfo']['backuptype'],
            'prefixdir' => $prefixDir,
            'backupdir' => $params['standbyhostInfo']['backupdir'],
            'historydir' => $params['standbyhostInfo']['historydir'],
            'hiscopys' => $params['standbyhostInfo']['historycopys'],        //历史份数
            "mirrdir" => '',        //镜像目录,全路径
            //             'logSetting' => $params['highInfo']['log'],
            'productHostInfo' => array(
                'hipstr' => $productHostIP,
                'hport' => 0,
                'huser' => 'admin',
                'hpass' => 'admin',
            )
        );
        //设定远程数据库备份日志管理参数
        $this->setBackupLogInfo($productHostIP, $standbyHostIP, $params['highInfo']['log']);

        //发送消息到生产主机
        $result = $rpc->createBackupJob($productHostIP, $data);
        if (!$result['result']) {
            return $this->muOpResult(false, $operate, $productHostIP . $result['errorMsg'], 'warning', $result['errorCode']);
        }

        //发送消息到备份主机
        $data['syscode'] = Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'];
        $result = $rpc->createBackupJob($standbyHostIP, $data);
        if (!$result['result']) {
            return $this->muOpResult(false, $operate, $standbyHostIP . $result['errorMsg'], 'warning', $result['errorCode']);
        }

        //配置接管
        $result = $this->settingTakeoverConfig($productHostInfo, $standbyHostInfo, $params);
        if (true !== $result) {
            return $result;
        }

        //禁用单个从站备份任务
        $this->ControlBackupJob($productHostIP, $standbyHostIP, false);

        //启动主站备份系统
        $this->startBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);

        //         sleep(5);

        //如果都配置成功,插入信息到数据库
        $taskName = htmlspecialchars_decode($params['jobname']);
        $moduleType = Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'];
        $taskType = Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'];
        $taskStatus = Xphp::$_config['TASKSTATUS']['STOPPED'];
        $createTime = date("Y-m-d H:i:s", time());
        $useruuid = Xphp::$_user['useruuid'];
        $deleteFlag = Xphp::$_config['FLAG']['UNSET'];

        $this->dbBeginTransaction();
        $result = true;
        //更新bd_task
        $sql = "update bd_task set task_name = ?, create_time = ?, user_uuid = ? where task_uuid = ?";
        $sqlParams = array($taskName, $createTime, $useruuid, $taskuuid);
        $result = $this->dbExec($sql, $sqlParams);


        //更新cdp_fs_task
        $sql = "update cdp_db_task set config = ? where task_uuid = ?";
        $sqlParams = array(json_encode($params, true), $taskuuid);
        $result = $result && $this->dbExec($sql, $sqlParams);

        if ($result) {
            $this->dbCommit();
            $this->writeTaskLog($taskuuid, $taskName, $taskType, 'BD_TASKLOG_DESC_KEY_MODIFY_TASK_SUCCESS');
        } else {
            $this->dbRollBack();
            $this->writeTaskLog($taskuuid, $taskName, $taskType, 'BD_TASKLOG_DESC_KEY_MODIFY_TASK_FAILURE');
        }

        return $this->muOpResult($result, $operate);
    }

    /**
     * 获取修改任务需要的任务所有信息,用于界面展示
     * @param unknown $params
     */
    public function getBackupTaskAllInfo($params)
    {
        $taskuuid = $params['taskuuid'];
        $this->paramsCheck($taskuuid);

        $sql = "select bt.task_name, cdt.product_host_uuid, cdt.standby_host_uuid,  cdt.config
                 from bd_task bt, cdp_db_task cdt where bt.task_uuid = cdt.task_uuid and bt.task_uuid= ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = json_decode($data[0]['config'], true);

        $productHostInfo = $this->getHostInfoWithUUID($data[0]['product_host_uuid']);
        $standbyHostInfo = $this->getHostInfoWithUUID($data[0]['standby_host_uuid']);
        $info['producthostInfo']['hoststatus'] = intval($productHostInfo['status']);
        $info['producthostInfo']['hostname'] = $productHostInfo['host_name'];
        $info['producthostInfo']['hostip'] = $productHostInfo['ip'];

        $info['standbyhostInfo']['hoststatus'] = intval($standbyHostInfo['status']);
        $info['standbyhostInfo']['hostname'] = $standbyHostInfo['host_name'];
        $info['standbyhostInfo']['hostip'] = $standbyHostInfo['ip'];

        //将服务md5
        $service = $info['highInfo']['takeover']['service'];
        $serviceMd5 = array();
        foreach ($service as $s) {
            $serviceMd5[] = md5($s);
        }
        $info['highInfo']['takeover']['service'] = $serviceMd5;

        return json_encode($info);
    }

    /**
     * 检查生产主机对应备份主机,对应关系下是否有任务存在
     * @param unknown $productHostuuid
     * @param unknown $standbyHostuuid
     * @return boolean  有 返回 true   无返回false
     */
    private function checkProductAndStandbyHostHaveTask($productHostuuid, $standbyHostuuid)
    {
        //         $sql = "select "
//         return true;
    }

    /**
     * 清除之前备份的配置
     * @param unknown $params
     */
    private function clearOldBackupInfo($productHostuuid, $standbyHostuuid)
    {
        $productHostInfo = $this->getHostInfoWithUUID($productHostuuid);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostuuid);
        $operate = "清理残留配置";
        $rpc = Xphp::instance('DbRPCHandler');
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hipstr' => $productHostInfo['ip']
        );
        $opResult = true;
        $result = $rpc->getallbackupRemotejobs($standbyHostInfo['ip'], $msgData);
        $data = $this->checkRPCMsg($operate, $result);

        $opResult = $opResult & $result['result'];
        //删除之前的配置
        foreach ($data as $d) {
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                'hipstr' => $productHostInfo['ip'],
                'dbtype' => $d['dbtype'],
                'dbsrv' => $d['dbsrv'],
                'dbname' => $d['dbname'],
            );
            $result = $rpc->delBackupRemotejob($standbyHostInfo['ip'], $msgData);
            $opResult = $opResult & $result['result'];
        }
        return $opResult;
    }

    /**
     * 配置接管信息
     * @param unknown $standbyhostInfo
     * @param unknown $takeoverInfo
     */
    private function settingTakeoverConfig($productHostInfo, $standbyhostInfo, $params)
    {
        $takeoverInfo = $params['highInfo']['takeover'];
        if (!$takeoverInfo['check'])
            return true; //如果接管关闭,直接返回

        //停止接管系统
        $this->stopTakeoveSys($standbyhostInfo['ip']);

        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
            'takeautoid' => $takeoverInfo['type'],
            'takenameid' => 1,
            'takenetid' => 1,
            'takchktime' => 0,
            'takechkips' => array(),
            'takechknames' => array()
        );
        $operate = '配置接管参数';
        $rpc = Xphp::instance('DbRPCHandler');
        $result = $rpc->saveParam($standbyhostInfo['ip'], $msgData);
        $this->checkRPCMsg($operate, $result);

        //先删除接管对象
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
        );
        $result = $rpc->getTakeAllObjs($standbyhostInfo['ip'], $msgData);
        $data = $this->checkRPCMsg($operate, $result);

        foreach ($data as $d) {
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
                'taskname' => $d['taskname']
            );
            $result = $rpc->delTakeObj($standbyhostInfo['ip'], $msgData);
            $this->checkRPCMsg($operate, $result);
        }

        //如果配置了接管服务
        if (!empty($takeoverInfo['service'])) {
            $i = 0;
            foreach ($takeoverInfo['service'] as $service) {
                $msgData = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
                    'taskname' => $params['jobname'] . "_s" . $i++,
                    'taketype' => 0,
                    'appname' => $service,
                    'apptype' => 1,
                    'startpath' => '',
                    'enable' => 1,
                    'param' => ''
                );
                $result = $rpc->addTakeObj($standbyhostInfo['ip'], $msgData);
                $this->checkRPCMsg($operate, $result);
            }
            if (!$result['result']) {
                exit($this->muOpResult(false, $operate, $result['errorMsg'], 'warning', $result['errorCode']));
            }
        }

        $standbyNetwork = $takeoverInfo['network']['standbyNetwork'];
        $networkInfo = array();
        foreach ($standbyNetwork as $network) {
            if (!empty($network['takeover'])) {
                //如果配置了网卡
                foreach ($network['takeover'] as $takeover) {
                    $networkInfo[] = array(
                        'radapter' => $network['adaptername'],
                        'hadapter' => $takeover
                    );
                }
            }
        }
        if (!empty($networkInfo)) {
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
                'network' => $networkInfo
            );
            $result = $rpc->saveAdapterMap($standbyhostInfo['ip'], $msgData);
            $this->checkRPCMsg($operate, $result);
        }

        //配置连接次数
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'ipstr' => $productHostInfo['ip'],
            'autotakect' => intval($takeoverInfo['step'])
        );
        $result = $rpc->setDbBkAutoParam($standbyhostInfo['ip'], $msgData);
        $this->checkRPCMsg($operate, $result);
        //         var_dump($standbyhostInfo['ip'], $msgData, $result);


        //设置自动和手动接管启动模式
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
            'startmode' => $takeoverInfo['type']
        );
        $result = $rpc->setServiceStartMode($standbyhostInfo['ip'], $msgData);
        $this->checkRPCMsg($operate, $result);

        //如果是自动接管,启动自动接管
        if (1 == intval($takeoverInfo['type'])) {
            //启动接管系统

            $this->startTakeoveSys($standbyhostInfo['ip']);
        }

        return true;
    }

    /**
     * 创建数据库恢复任务
     * @param unknown $params
     */
    public function createRecoveryJob($params)
    {
        $srchostuuid = $params['standbyhost'];
        $deshostuuid = $params['recoveryhost'];
        $srcHostInfo = $this->getHostInfoWithUUID($srchostuuid);
        $desHostInfo = $this->getHostInfoWithUUID($deshostuuid);

        $operate = "创建数据库恢复任务";
        //一个主机对应一个备机只能创建一个备份任务,检查这里
        $sql = "select count(cdt.id) as total from cdp_db_task cdt,  bd_task bt where
                cdt.task_uuid = bt.task_uuid and cdt.product_host_uuid = ? and
                cdt.standby_host_uuid = ? and bt.task_type = ?";
        $sqlParams = array($deshostuuid, $srchostuuid, Xphp::$_config['TASKTYPE']['DB_CDP_RECOVERY']);
        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]['total'] > 0) {
            return $this->muOpResult(false, $operate, "对应恢复源主机和恢复目标主机已经有恢复任务存在!请删除已有恢复任务后再重新创建任务!", "warning");
        }

        //如果都配置成功,插入信息到数据库
        $taskuuid = Xphp::instance('Utils', 'uuid');
        $taskName = $params['jobname'];
        $moduleType = Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'];
        $taskType = Xphp::$_config['TASKTYPE']['DB_CDP_RECOVERY'];
        $taskStatus = Xphp::$_config['TASKSTATUS']['STOPPED'];
        $createTime = date("Y-m-d H:i:s", time());
        $useruuid = Xphp::$_user['useruuid'];
        $deleteFlag = Xphp::$_config['FLAG']['UNSET'];

        $this->dbBeginTransaction();
        $sql = "insert into bd_task(task_uuid, task_name, module_type, task_type, task_status, create_time,
                      user_uuid, delete_flag) values(?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array($taskuuid, $taskName, $moduleType, $taskType, $taskStatus, $createTime, $useruuid, $deleteFlag);
        $result = $this->dbQuery($sql, $sqlParams);

        $sql = "insert into bd_running_info(task_uuid) values(?)";
        $sqlParams = array($taskuuid);
        $result = $result && $this->dbQuery($sql, $sqlParams);

        $sql = "insert into cdp_db_task(task_uuid, product_host_uuid, standby_host_uuid, config) values(?, ?, ?, ?)";
        $sqlParams = array($taskuuid, $deshostuuid, $srchostuuid, json_encode($params, true));
        $result = $result && $this->dbQuery($sql, $sqlParams);

        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }

        return $this->muOpResult($result, $operate);
    }

    /**
     * 获取所有主机列表
     * @param unknown $params
     */
    public function getDbHostInfo($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $search = $params['search']['name'];
        $utils = Xphp::instance('Utils');
        $search = $utils->escapeWildcard($search);
        $sortArr = array('', 'cdh.host_name', 'cdh.ip', 'cdh.os_version', 'cdh.host_type', 'cdh.register_time', 'cdh.status', 'bu.user_uuid', '');

        $sql = "select cdh.host_uuid, cdh.host_name, cdh.ip, cdh.os_version, 
                cdh.host_type, cdh.status, cdh.register_time, cdh.detail, bu.user_name, cdh.user_uuid from 
                cdp_db_host cdh, bd_user bu where cdh.user_uuid = bu.user_uuid and bu.user_uuid = ?";
        $sqlCount = "select count(ip) as total from cdp_db_host where user_uuid = ?";

        if (!empty($search)) {
            //按任务名或虚拟机名搜索
            $searchParams = "%" . $search . "%";
            $sql .= " and (host_name like '" . $searchParams . "'
            			or ip like '" . $searchParams . "') ";
            $sqlCount .= " where (host_name like '" . $searchParams . "' or ip like '" . $searchParams . "') ";
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid'], $start, $length));
        $count = $this->dbSelect($sqlCount, array(Xphp::$_user['useruuid']));

        $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
        $records = array();
        $records["data"] = array();
        foreach ($data as $d) {
            //             if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator'] && $d['user_uuid'] != Xphp::$_user['useruuid'] && !in_array($d['host_uuid'], $hostList) && $cdpPermissionType != "0"){
//                 //如果是操作员，主机不是该用户创建并且不是 管理员分配的主机直接过滤
//                 continue;
//             }
            $detail = json_decode($d['detail'], true);
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['host_uuid'] . '">',
                $d['host_name'],
                $d['ip'],
                $d['os_version'],
                $dbDes['HOST_TYPE_DES'][$d['host_type']],
                $detail['license'],
                $d['register_time'],
                $dbDes['HOST_STATUS_DES'][$d['status']],
                $d['user_name'],
                $d['status']
            );
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return json_encode($records);
    }

    /**
     * 添加主机信息
     * @param unknown $params
     */
    public function addHost($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_dbhost_add");
        $hosttype = intval($params['hosttype']);
        $hostip = $params['hostip'];
        $file = $params['file'];
        $database = $params['database'];
        $operate = '添加主机';
        $this->checkHostInSystem($operate, $hosttype, $hostip);

        $data = $this->checkHostInfo($hosttype, $hostip, $file, $database);

        $utils = Xphp::instance('Utils');
        $hostuuid = $utils->uuid();
        $hostname = $data['hostname'];
        $osVersion = $data['osVersion'];
        $status = 1;
        $registerTime = date('Y-m-d H:i:s', time());
        $useruuid = Xphp::$_user['useruuid'];
        $detail = array(
            'workMode' => $data['workMode'],
            'osType' => $data['osType'],
            'osMode' => $data['osMode'],
            'viripcheck' => false,
            'virip' => '',
            'license' => array(
                'file' => $file,
                'database' => $database
            ),
        );
        $detail = json_encode($detail, true);

        $sql = "insert into cdp_db_host(host_uuid, host_name, ip, os_version, host_type, status, 
            register_time, user_uuid, detail) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array($hostuuid, $hostname, $hostip, $osVersion, $hosttype, $status, $registerTime, $useruuid, $detail);
        $result = $this->dbQuery($sql, $sqlParams);

        $descriptionParams = array($hostname . "(" . $hostip . ")");
        if ($result) {
            //查询uuid
            $sql = "select host_uuid from cdp_db_host where user_uuid = ? ";
            $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));

            if (!empty($data)) {
                //添加资源和用户关联
                $userHandler = Xphp::instance('UsersHandler');
                $resourceInfo = array();
                $resourceInfo[] = array(
                    'resourceuuid' => $data[0]['host_uuid'],
                    'vmuuid' => "",
                    'vcenteruuid' => "",
                    'resourceType' => Xphp::$_config['RESOURCE_TYPE']['CDP_HOST']
                );
                $userHandler->pAddUserResource(Xphp::$_user['useruuid'], $resourceInfo);
            }

            $this->systemLog('SYSTEM_LOG_CDP_ADD_HOST', $descriptionParams);
        } else {
            $this->systemLog('SYSTEM_LOG_CDP_ADD_HOST', $descriptionParams, Xphp::$_config['LOGLEVEL']['ERROR']);
        }

        return $this->muOpResult($result, '添加主机');
    }

    /**
     * 检查远传主机状态,并获取远程主机信息后返回
     * @param unknown $hosttype
     * @param unknown $hostip
     */
    private function checkHostInfo($hosttype, $hostip, $file, $database)
    {
        $rpc = Xphp::instance('DbRPCHandler');
        $serverIp = $_SERVER['SERVER_ADDR'];
        $operate = '检查主机信息测试';
        //这里需要按照下面注释的顺序来,不然可能导致显示异常
        //配置本地授权许可列表性质
        $data = array(
            'tablemode' => 1
        );

        $result = $rpc->setLocAuthorTableMode($hostip, $data);
        $this->checkRPCMsg($operate, $result);


        //配置本地授权许可列表
        if ($hosttype == Xphp::$_config['DB_CDP_HOST_TYPE']['product']) {
            if ($database) {
                //添加数据库
                $syscode = Xphp::$_config['DB_CDP_SYSCODE']['producthost'];
                $data = array(
                    'syscode' => $syscode
                );
                $result = $rpc->addLocAuthorSyscode($hostip, $data);

                //配置子系统启动模式
                $data = array(
                    'syscode' => $syscode,
                    'startmode' => 1
                );
                $result = $rpc->setServiceStartMode($hostip, $data);

            } else {
                //else主要是用于修改
                //删除本地授权许可列表
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost']
                );
                $result = $rpc->delLocAuthorSyscode($hostip, $data);

                //配置子系统启动模式
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
                    'startmode' => 0
                );
                $result = $rpc->setServiceStartMode($hostip, $data);

                //释放授权
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
                    'ipstr' => $hostip
                );
                $result = $rpc->freeLicenseCenterAuth($serverIp, $data);

            }
            if ($file) {
                //添加文件
                $syscode = Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'];
                $data = array(
                    'syscode' => $syscode
                );
                $result = $rpc->addLocAuthorSyscode($hostip, $data);

                //配置子系统启动模式
                $data = array(
                    'syscode' => $syscode,
                    'startmode' => 1
                );
                $result = $rpc->setServiceStartMode($hostip, $data);


            } else {
                //else主要是用于修改
                //删除本地授权许可列表
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct']
                );
                $result = $rpc->delLocAuthorSyscode($hostip, $data);

                //配置子系统启动模式
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                    'startmode' => 0
                );
                $result = $rpc->setServiceStartMode($hostip, $data);

                //释放授权
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                    'ipstr' => $hostip
                );
                $result = $rpc->freeLicenseCenterAuth($serverIp, $data);
            }
        } else {
            //从站
            if ($database) {
                //添加数据库
                $syscode = Xphp::$_config['DB_CDP_SYSCODE']['takeover'];
                $data = array(
                    'syscode' => $syscode
                );
                $result = $rpc->addLocAuthorSyscode($hostip, $data);

                $syscode = Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'];
                $data = array(
                    'syscode' => $syscode
                );
                $result = $rpc->addLocAuthorSyscode($hostip, $data);

                //配置子系统启动模式
                $data = array(
                    'syscode' => $syscode,
                    'startmode' => 1
                );
                $result = $rpc->setServiceStartMode($hostip, $data);
            } else {
                //删除本地授权许可列表
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
                );
                $result = $rpc->delLocAuthorSyscode($hostip, $data);

                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']
                );
                $result = $rpc->delLocAuthorSyscode($hostip, $data);

                //配置子系统启动模式
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                    'startmode' => 0
                );
                $result = $rpc->setServiceStartMode($hostip, $data);
                //释放授权
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
                    'ipstr' => $hostip
                );
                $result = $rpc->freeLicenseCenterAuth($serverIp, $data);
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                    'ipstr' => $hostip
                );
                $result = $rpc->freeLicenseCenterAuth($serverIp, $data);

            }
            if ($file) {
                //添加文件
                $syscode = Xphp::$_config['DB_CDP_SYSCODE']['filestandby'];
                $data = array(
                    'syscode' => $syscode
                );
                $result = $rpc->addLocAuthorSyscode($hostip, $data);

                //配置子系统启动模式
                $data = array(
                    'syscode' => $syscode,
                    'startmode' => 1
                );
                $result = $rpc->setServiceStartMode($hostip, $data);
            } else {
                //else主要是用于修改
                //删除本地授权许可列表
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby']
                );
                $result = $rpc->delLocAuthorSyscode($hostip, $data);

                //配置子系统启动模式
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                    'startmode' => 0
                );
                $result = $rpc->setServiceStartMode($hostip, $data);

                //释放授权
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                    'ipstr' => $hostip
                );
                $result = $rpc->freeLicenseCenterAuth($serverIp, $data);
            }
        }

        //睡2秒,如果不睡,有可能网络授权(setNetLicenseSrv)快于(addLocAuthorSyscode),导致获取到257 258 1025
        sleep(2);

        //配置网络授权机器
        $data = array(
            'licport' => "7999",
            'licipstr' => $serverIp,
            'licuser' => "admin",
            'licpass' => "admin"
        );
        $result = $rpc->setNetLicenseSrv($hostip, $data);


        //激活当前授权配置立即生效
        $result = $rpc->actLicenseCfgValid($hostip, array());


        //获取主机信息
        $data = array();
        $result = $rpc->hostInfo($hostip, $data);


        return $this->checkRPCMsg($operate, $result);
    }

    /**
     * 统一检查RPC消息,如果失败直接返回并退出
     * @param unknown $result
     */
    public function checkRPCMsg($operate, $result)
    {
        //单独处理部分错误,不提示的.
        //1.-93001 停止任务成功,但是报错-93001, 不处理
        //2.-19150 启动任务,任务已经在运行了,报错,不处理
        $excludeError = array(
            -93001,
            -19150
        );
        if (in_array($result['errorCode'], $excludeError)) {
            $result['result'] = true;
            return $result;
        }
        if (!$result['result']) {
            //如果失败
            //不是vinchin_enterprise,是false的话直接return空
            $systemInfoEnterprise = Xphp::$_config['SYSTEM_INFO']['enterprise'];
            $enterprise = Xphp::$_config['ENTERPRISE'];
            $vinchinVersions = array(
                $enterprise['vinchin_standard'],
                $enterprise['vinchin_enterprise'],
                $enterprise['vinchin_advance_enterprise'],
            );
            if (!in_array($systemInfoEnterprise, $vinchinVersions)) {
                return '';
            }
            echo $this->muOpResult(false, $operate, $result['errorMsg'], 'warning', $result['errorCode']);
            exit();
        }

        $msgData = $result['data'];
        $msgCode = intval($msgData['msgcode']);
        //这里需要去解析错误消息TODO
        $msgTxt = $msgData['msgtxt'];
        if ($msgCode < 0) {
            //如果rpc返回 操作失败
            echo $this->muOpResult(false, $operate, $msgTxt, 'warning', $msgCode);
            exit();
        }
        //         $data = json_decode($result['data'], true);

        return $msgData['data'];
    }

    /**
     * 修改主机信息
     * @param unknown $params
     */
    public function editHost($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_dbhost_edit");
        $hostuuid = $params['hostuuid'];
        $hosttype = $params['hosttype'];
        $hostip = $params['hostip'];
        $hostname = $params['hostname'];
        $file = $params['file'];
        $database = $params['database'];
        $this->paramsCheck($hostuuid, $hostip, $hostname);
        $operate = '修改主机';

        $this->checkHostTaskLicense($params);

        //检查新的IP地址是否是其他已经使用的IP地址
        $sql = "select count(id) as total from cdp_db_host where ip = ? and host_uuid != ?";
        $data = $this->dbSelect($sql, array($hostip, $hostuuid));
        if ($data[0]['total'] > 0) {
            echo $this->muOpResult(false, $operate, '已经有对应IP存在,请检查您的输入!', 'warning');
            exit();
        }



        $data = $this->checkHostInfo($hosttype, $hostip, $file, $database);

        $detail = array(
            'workMode' => $data['workMode'],
            'osType' => $data['osType'],
            'osMode' => $data['osMode'],
            'license' => array(
                'file' => $file,
                'database' => $database
            ),
        );

        $sql = "update cdp_db_host set ip=?, host_name=?, detail=? where host_uuid = ?";
        $result = $this->dbExec($sql, array($hostip, $hostname, json_encode($detail, true), $hostuuid));


        $descriptionParams = array($hostname . "(" . $hostip . ")");
        if ($result) {
            $this->systemLog('SYSTEM_LOG_CDP_EDIT_HOST', $descriptionParams);
        } else {
            $this->systemLog('SYSTEM_LOG_CDP_EDIT_HOST', $descriptionParams, Xphp::$_config['LOGLEVEL']['ERROR']);
        }


        return $this->muOpResult($result, $operate);
    }

    /**
     * 修改主机的时候检查是否可以取消授权
     * @param unknown $params
     */
    private function checkHostTaskLicense($params)
    {
        $file = $params['file'];
        $database = $params['database'];
        $hostuuid = $params['hostuuid'];
        $operate = '修改主机检查';

        //如果是取消授权才检查是否有任务
        //检查文件实时
        if (!$file) {
            $sql = "select count(id) as total from cdp_fs_task where product_host_uuid = ? or standby_host_uuid = ?";
            $data = $this->dbSelect($sql, array($hostuuid, $hostuuid));
            if ($data[0]['total'] > 0) {
                echo $this->muOpResult(false, $operate, '该主机有文件实时任务存在,无法取消授权,请先删除任务后重试!', 'warning');
                exit();
            }
        }

        //检查数据库实时
        if (!$database) {
            $sql = "select count(id) as total from cdp_db_task where product_host_uuid = ? or standby_host_uuid = ?";
            $data = $this->dbSelect($sql, array($hostuuid, $hostuuid));
            if ($data[0]['total'] > 0) {
                echo $this->muOpResult(false, $operate, '该主机有数据库实时任务存在,无法取消授权,请先删除任务后重试!', 'warning');
                exit();
            }
        }
    }


    /**
     * 删除主机
     * @param unknown $params
     */
    public function deleteHost($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_dbhost_delete");
        $host = $params['host'];
        $serverIp = $_SERVER['SERVER_ADDR'];
        $operate = "删除主机";
        //检查是否有任务在,如果在,需要先删除任务 
        if ($this->checkHostInJob($host[0])) {
            return $this->muOpResult(false, $operate, "您要删除的主机有任务存在,请先删除任务", "warning");
        }
        $rpc = Xphp::instance('DbRPCHandler');
        $hostInfo = $this->getHostInfoWithUUID($host[0]);

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE'] == $hostInfo['status']) {
            //如果机器在线,清除授权
            //配置网络授权机器
            $data = array();
            $result = $rpc->clrNetLicenseSrv($hostInfo['ip'], $data);

            if ($hostInfo['host_type'] == Xphp::$_config['DB_CDP_HOST_TYPE']['product']) {
                //如果是是生产主机//删除本地授权允许列表
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost']
                );
                $result = $rpc->delLocAuthorSyscode($hostInfo['ip'], $data);

                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct']
                );
                $result = $rpc->delLocAuthorSyscode($hostInfo['ip'], $data);

                //修改备份系统手动启动
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
                    'startmode' => 0
                );
                $result = $rpc->setServiceStartMode($hostInfo['ip'], $data);

                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                    'startmode' => 0
                );
                $result = $rpc->setServiceStartMode($hostInfo['ip'], $data);
            } else {
                //如果是备份主机
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']
                );
                $result = $rpc->delLocAuthorSyscode($hostInfo['ip'], $data);
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
                );
                $result = $rpc->delLocAuthorSyscode($hostInfo['ip'], $data);
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby']
                );
                $result = $rpc->delLocAuthorSyscode($hostInfo['ip'], $data);

                //修改备份系统手动启动
                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                    'startmode' => 0
                );
                $result = $rpc->setServiceStartMode($hostInfo['ip'], $data);

                $data = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                    'startmode' => 0
                );
                $result = $rpc->setServiceStartMode($hostInfo['ip'], $data);
            }
            //设置配置立即生效
            $result = $rpc->actLicenseCfgValid($hostInfo['ip'], array());
        }
        //授权中心释放该IP的授权
        if ($hostInfo['host_type'] == Xphp::$_config['DB_CDP_HOST_TYPE']['product']) {
            //如果是是生产主机
            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
                'ipstr' => $hostInfo['ip']
            );
            $result = $rpc->freeLicenseCenterAuth($serverIp, $data);

            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                'ipstr' => $hostInfo['ip']
            );
            $result = $rpc->freeLicenseCenterAuth($serverIp, $data);

        } else {
            //如果是备份主机
            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                'ipstr' => $hostInfo['ip']
            );
            $result = $rpc->freeLicenseCenterAuth($serverIp, $data);
            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
                'ipstr' => $hostInfo['ip']
            );
            $result = $rpc->freeLicenseCenterAuth($serverIp, $data);
            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'ipstr' => $hostInfo['ip']
            );
            $result = $rpc->freeLicenseCenterAuth($serverIp, $data);
        }

        //设置配置立即生效
//         $result = $rpc->actLicenseCfgValid($hostInfo['ip'], array());

        //删除数据库记录
        $sql = "delete from cdp_db_host where host_uuid = ?";
        $result = $this->dbExec($sql, array($host[0]));

        $hostname = $hostInfo['host_name'];
        $hostip = $hostInfo['ip'];
        $descriptionParams = array($hostname . "(" . $hostip . ")");
        if ($result) {
            //添加资源和用户关联
            $userHandler = Xphp::instance('UsersHandler');
            $userHandler->pDeleteUserResource(Xphp::$_user['useruuid'], $host);
            $this->systemLog('SYSTEM_LOG_CDP_DELETE_HOST', $descriptionParams);
        } else {
            $this->systemLog('SYSTEM_LOG_CDP_DELETE_HOST', $descriptionParams, Xphp::$_config['LOGLEVEL']['ERROR']);
        }

        return $this->muOpResult($result, $operate);
    }

    /**
     * 检查主机是否有任务存在
     * 有返回 true
     * @param unknown $hostuuid
     */
    private function checkHostInJob($hostuuid)
    {
        $sql = "select count(task_uuid) as total from cdp_db_task where 
                product_host_uuid = ? or standby_host_uuid = ?";
        $data = $this->dbSelect($sql, array($hostuuid, $hostuuid));
        return (bool) $data[0]['total'];
    }


    /**
     * 检查生产/备份主机是否存在
     * @param unknown $hostType
     * @param unknown $hostIP
     */
    private function checkHostInSystem($operate, $hostType, $hostIP)
    {
        $sql = "select count(ip) as total from cdp_db_host where ip = ?";
        $data = $this->dbSelect($sql, array($hostIP));
        if ($data[0]['total'] > 0) {
            echo $this->muOpResult(false, $operate, '已经有相同IP地址的主机存在，请检查', 'warning');
            exit();
        }
    }

    /**
     * 通过主机UUID得到主机信息(用户修改主机)
     * @param unknown $params
     */
    public function getEditHostInfo($params)
    {
        $hostuuid = $params['hostuuid'];
        $this->paramsCheck($hostuuid);
        $sql = "select ip, host_type, host_name, detail from cdp_db_host where host_uuid = ?";
        $data = $this->dbSelect($sql, array($hostuuid));
        $info['ip'] = $data[0]['ip'];
        $info['hosttype'] = $data[0]['host_type'];
        $info['hostuuid'] = $hostuuid;
        $info['hostname'] = $data[0]['host_name'];
        $detail = json_decode($data[0]['detail'], true);
        $info['license'] = $detail['license'];

        return json_encode($info);
    }

    /**
     * 得到可用的主机信息
     * 传入参数为主机类型
     */
    public function gettHostInfoWithType($params)
    {
        $hosttype = $params['hosttype'];
        $this->paramsCheck($hosttype);
        $sql = "select host_uuid, host_name, host_type, ip from cdp_db_host where user_uuid = ? and status = 1 and host_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid'], $hosttype));
        $info = array();
        foreach ($data as $d) {
            //做一个是否是备份服务器的标记，提供给前端处理
            $bsFlag = false;
            if ($_SERVER['SERVER_ADDR'] == $d['ip']) {
                $bsFlag = true;
            }
            $info[] = array(
                'uuid' => $d['host_uuid'],
                'value' => $d['host_name'] . "(" . $d['ip'] . ")",
                'bsflag' => $bsFlag
            );
        }
        return json_encode($info);
    }

    /**
     * 得到可以用作恢复的主机 //TODO 哪些主机可以用做恢复,要确认
     */
    public function gettRecoveryHostInfo($params)
    {
        $sql = "select host_uuid, host_name, ip from cdp_db_host where status = 1 and user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $info = array();
        foreach ($data as $d) {
            $info[] = array(
                'uuid' => $d['host_uuid'],
                'value' => $d['host_name'] . "(" . $d['ip'] . ")"
            );
        }
        return json_encode($info);
    }

    /**
     * 根据主机UUID获取主机上的实例
     * @param unknown $params
     */
    public function getHostInstance($params)
    {
        $hostuuid = $params['hostuuid'];
        $dbtype = $params['dbtype'];
        $this->paramsCheck($hostuuid);
        $standbyhostuuid = $params['standbyhostuuid'];
        //备份的时候没有,恢复的时候有数据库类型
        if (!empty($standbyhostuuid) && in_array($params['dbtype'], Xphp::$_config['DB_CDP_VENDOR'])) {
            //恢复
            $standbyhostInfo = $this->getHostInfoWithUUID($standbyhostuuid);
            $desthostInfo = $this->getHostInfoWithUUID($hostuuid);

            if (intval($desthostInfo['host_type']) == Xphp::$_config['DB_CDP_HOST_TYPE']['product']) {
                $syscode = Xphp::$_config['DB_CDP_SYSCODE']['producthost'];
            }
            if (intval($desthostInfo['host_type']) == Xphp::$_config['DB_CDP_HOST_TYPE']['standby']) {
                $syscode = Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'];
            }
        } else {
            //备份
            $syscode = Xphp::$_config['DB_CDP_SYSCODE']['producthost'];
            //             $dbtype = -1;
        }


        $sql = "select ip, detail from cdp_db_host where host_uuid = ?";
        $data = $this->dbSelect($sql, array($hostuuid));
        //这里需要先请求远端数据,然后再从detail里面取实例账号密码信息,组合返回
        $hostIP = $data[0]['ip'];

        $rpc = Xphp::instance('DbRPCHandler');
        $msgData = array(
            'syscode' => $syscode,
            'dbType' => $dbtype
        );

        $result = $rpc->getHostInstance($hostIP, $msgData);
        $operate = '获取主机实例';
        $data = $this->checkRPCMsg($operate, $result);


        $instance = array();
        for ($i = 0; $i < count($data); $i++) {
            $instance[] = array(
                'dbtype' => $data[$i]['dbtype'],
                'instancename' => $data[$i]['sqlLName'],
                'username' => '',
                'password' => ''
            );
        }

        return $this->muOpResult(true, $operate, '', '', 0, $instance);
    }

    /**
     * 扫描数据库实例
     * @param unknown $params
     */
    public function scanHostDB($params)
    {
        $hostInfo = $this->getHostInfoWithUUID($params['hostuuid']);
        $instance = $params['instance'];
        $hostIP = $hostInfo['ip'];

        $rpc = Xphp::instance('DbRPCHandler');
        $operate = '扫描数据库';
        foreach ($instance as $key => $ins) {
            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
                'dbtype' => $ins['dbtype'],
                'dbsrv' => $ins['dbsrv'],
                'dbuser' => $ins['dbuser'],
                'dbpass' => $ins['dbpass'],
                'dbname' => $ins['dbname']
            );
            $result = $rpc->scanHostDB($hostIP, $data);

            //             var_dump($hostIP, $data, $result);

            $data = $this->checkRPCMsg($operate, $result);
            $instance[$key]['dbs'] = $data;
        }

        //返回数据库信息,组合成一个树 三层树,第一层为数据库类型,第二层为实例,第三层为数据库
        $info = $this->groupDbTree($instance);

        return $this->muOpResult(true, $operate, '', '', 0, $info);
    }

    /**
     * 检查备份主机备份目录是否可用
     * @param unknown $params
     */
    public function standbyHostDirCheck($params)
    {
        $hostInfo = $this->getHostInfoWithUUID($params['standbyhost']);
        $backuptype = intval($params['backuptype']);
        $backupDir = $params['backupdir'];
        $historyDir = $params['historydir'];
        $storageuuid = $params['storageuuid'];
        $taskuuid = Xphp::instance('Utils', 'uuid');
        if ($backuptype == Xphp::$_config['DB_CDP_BACKUP_TYPE']['TAKEOVER']) {
            //业务接管只检查备份目录
            $historyDir = '';
        }

        $hostIP = $hostInfo['ip'];
        $prefixDir = $this->getStorageMountPoint($storageuuid);

        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'backupDir' => $backupDir,
            'historyDir' => $historyDir,
            'prefixDir' => $prefixDir,
        );
        $result = $rpc->standbyHostDirCheck($hostIP, $data);
        $operate = '检查备机目录';


        if ($result['result']) {
            return $this->muOpResult(true, $operate);
        } else {
            return $this->muOpResult(false, $operate, $result['errorMsg'], 'warning', $result['errorCode']);
        }
    }

    /**
     * 接管环境检查
     * @param unknown $params
     */
    public function takeoverEnvironmentCheck($params)
    {
        //$operate = '检查业务接管环境';
        //return $this->muOpResult(true, $operate);
        
        $srchostuuid = $params['producthost'];
        $deshostuuid = $params['standbyhost'];
        $srcHostInfo = $this->getHostInfoWithUUID($srchostuuid);
        $desHostInfo = $this->getHostInfoWithUUID($deshostuuid);
        $operate = '检查业务接管环境';
        $srcHostInfoDetail = json_decode($srcHostInfo['detail'], true);
        $desHostInfoDetail = json_decode($desHostInfo['detail'], true);

        if ($desHostInfoDetail['osType'] != Xphp::$_config['DB_CDP_OS_TYPE']['WINDOWS']) {
            return $this->muOpResult(false, $operate, '暂时只支持Windows主机业务接管!', 'warning');
        }
        //接管要求操作系统一致
        if ($srcHostInfoDetail['osType'] == $desHostInfoDetail['osType']) {
            return $this->muOpResult(true, $operate);
        } else {
            return $this->muOpResult(false, $operate, '生产主机和备份主机操作系统不一致,请重新配置!', 'warning');
        }
    }

    /**
     * 通过存储uuid得到存储目录
     * @param unknown $storageuuid
     */
    private function getStorageMountPoint($storageuuid, $taskuuid)
    {
        $dir = "";
        if (empty($storageuuid)) {
            return $dir;
        }
        $sql = "select mount_point from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        return $data[0]['mount_point'] . "/dbcdp/" . $taskuuid;
    }

    /**
     * 得到主机服务
     */
    public function getHostService($params)
    {
        $hostInfo = $this->getHostInfoWithUUID($params['standbyhostuuid']);
        $hostIP = $hostInfo['ip'];
        $rpc = Xphp::instance('DbRPCHandler');
        $operate = '获取主机服务';

        $page = 0;
        $perPage = 500;
        $service = array();
        do {

            $data = array(
                'pageno' => $page++,
                'pagesize' => $perPage
            );
            $result = $rpc->getHostService($hostIP, $data);
            $data = $this->checkRPCMsg($operate, $result);

            $service = array_merge($service, $data);

        } while (count($data) == $perPage);

        $dbDes = include APP_PATH . "db/DbCDPDescription.php";
        $records = array();
        $records["data"] = array();

        $utils = Xphp::instance('Utils');
        $service = $utils->arraySort($service, 'displayName', 'asc', 0, -1);
        foreach ($service as $d) {
            $data = array(
                '<input type="checkbox" data-name="' . md5($d['serviceName']) . '" name="id[]" value="' . $d['serviceName'] . '">',
                $d['displayName'],
                $d['note'],
                $dbDes['SERVICE_STATUS_DES'][$d['state']],
                $dbDes['SERVICE_START_TYPE_DES'][$d['StartType']],
                md5($d['serviceName'])
            );
            $records["data"][] = $data;
        }
        $records["draw"] = $params['draw'];
        return json_encode($records);

    }

    /**
     * 得到主备机网卡配置,用于接管
     * @param unknown $params
     */
    public function getTakeoverNetworkCardInfo($params)
    {
        $productNetwork = $this->getHostNetworkCard($params['producthostuuid']);
        $standbyNetwork = $this->getHostNetworkCard($params['standbyhostuuid']);
        $info = array(
            'productNetwork' => $productNetwork,
            'standbyNetwork' => $standbyNetwork,
        );
        return json_encode($info, true);
    }

    /**
     * 得到备份主机网卡地址table数据
     * @param unknown $params
     * @return string
     */
    public function getStandbyHostNetworkCard($params)
    {
        $standbyNetwork = $this->getHostNetworkCard($params['standbyhostuuid']);
        $records = array();
        $records["data"] = array();
        foreach ($standbyNetwork as $network) {
            $data = array(
                $network['address'],
                $network['ipaddress'],
                '',
                $network
            );
            $records["data"][] = $data;
        }
        $records["draw"] = $params['draw'];
        return json_encode($records);
    }

    /**
     * 得到生产主机网卡地址table数据
     * @param unknown $params
     * @return string
     */
    public function getProductHostNetworkCard($params)
    {
        $productNetwork = $this->getHostNetworkCard($params['producthostuuid']);
        $records = array();
        $records["data"] = array();
        foreach ($productNetwork as $network) {
            $data = array(
                '<input type="checkbox" name="id[]" value="' . $network['adaptername'] . '">',
                $network['usename'],
                $network['address'],
                $network['ipaddress'],
                $network['Description'],
            );
            $records["data"][] = $data;
        }
        $total = count($productNetwork);
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;
        return json_encode($records);
    }

    /**
     * 得到主机网卡信息
     * @param unknown $params
     */
    private function getHostNetworkCard($hostuuid)
    {
        $hostInfo = $this->getHostInfoWithUUID($hostuuid);
        $operate = '获取主机网卡';
        $hostIP = $hostInfo['ip'];
        $rpc = Xphp::instance('DbRPCHandler');

        $data = array(
            'pageno' => 0,
            'pagesize' => 100
        );
        $result = $rpc->getHostNetworkCard($hostIP, $data);
        $data = $this->checkRPCMsg($operate, $result);
        return $data;
    }

    /**
     * 得到备机存储列表(只有是备份系统作为备份机器才获取这个)
     * @param unknown $params
     */
    public function getBackupStorageList($params)
    {

    }

    /**
     * 组合备份树展示
     * @param array $instance 二维数组,带数据库信息
     */
    private function groupDbTree($instance)
    {
        $dbTree = array();
        $vendorArr = array();
        $dbDes = include APP_PATH . "db/DbCDPDescription.php";
        foreach ($instance as $ins) {
            if (!in_array($ins['dbtype'], $vendorArr)) {
                //如果没有这个数据库类型,添加数据库
                $vendorArr[] = $ins['dbtype'];
                $name = $dbDes['DB_TYPE_DES'][$ins['dbtype']];
                $dbTree[] = array(
                    'id' => 'vendor' . $ins['dbtype'],
                    'pid' => 0,
                    'name' => $name,
                    "open" => true,
                    "title" => $name,
                    'icon' => $this->getDbTypeIcon($ins['dbtype']),
                    "type" => 1
                );
            }
            //得到第二层,数据库实例层
            $insID = md5($ins['dbsrv']);
            $dbTree[] = array(
                'id' => $insID,
                'pid' => 'vendor' . $ins['dbtype'],
                'name' => $ins['dbsrv'],
                "open" => true,
                "title" => $ins['dbsrv'],
                "icon" => './img/db/instance.png',
                "type" => 2
            );

            foreach ($ins['dbs'] as $db) {
                //得到第三层,数据库层
                $dbTree[] = array(
                    'id' => $db['dbname'],
                    'pid' => $insID,
                    'name' => $db['dbname'],
                    "open" => true,
                    "title" => $db['dbname'],
                    "icon" => './img/db/database.png',
                    "type" => 3,

                    "vendortype" => $ins['dbtype'],
                    "vendorname" => $name,
                    'instantname' => $ins['dbsrv'],
                );
            }
        }
        return $dbTree;
    }

    /**
     * 得到数据库类型的图标
     * @param unknown $type
     */
    private function getDbTypeIcon($type)
    {
        $type = intval($type);
        $vendor = Xphp::$_config['DB_CDP_VENDOR'];
        $icon = './img/db/sqlserver.png';
        switch ($type) {
            case $vendor['SQLSERVER']:
                $icon = './img/db/sqlserver.png';
                break;
            case $vendor['DB2']:
                $icon = './img/db/db2.png';
                break;
            case $vendor['ORACLE']:
                $icon = './img/db/oracle.png';
                break;
            case $vendor['SYBASE']:
                $icon = './img/db/sybase.png';
                break;
            case $vendor['HOTFILES']:
                $icon = './img/db/sqlserver.png';
                break;
            case $vendor['MYSQL']:
                $icon = './img/db/mysql.png';
                break;
            case $vendor['INTERBASE']:
                $icon = './img/db/interbase.png';
                break;
            case $vendor['INFORMIX']:
                $icon = './img/db/sqlserver.png';
                break;
            case $vendor['RENDAJINCANG']:
                $icon = './img/db/rendajincang.png';
                break;
            case $vendor['SHENTONG']:
                $icon = './img/db/shentong.png';
                break;
            case $vendor['DAMENG']:
                $icon = './img/db/dameng.png';
                break;
            case $vendor['EXCHANGE']:
                $icon = './img/db/exchange.png';
                break;
            case $vendor['LOTUSDOMINO']:
                $icon = './img/db/lotus-domino.png';
                break;
        }
        return $icon;
    }

    /**
     * 公共方法,通过主机uuid,得到主机信息
     * @param unknown $uuid
     */
    private function getHostInfoWithUUID($uuid)
    {
        $this->paramsCheck($uuid);
        $sql = "select host_uuid,host_name, ip, os_version, host_type, status, register_time, detail 
                from cdp_db_host where host_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        return $data[0];
    }

    /**
     * 检查备份主机是否是备份服务器
     * @param unknown $hostip
     */
    public function checkBackupHostIsServer($params)
    {
        $hostInfo = $this->getHostInfoWithUUID($params['hostuuid']);
        $sql = "select ip, node_uuid from bd_node ";
        $data = $this->dbSelect($sql, array());
        $flag = false;
        $nodeuuid = '';
        foreach ($data as $d) {
            $ipArr = explode(" ", $d['ip']);
            if (in_array($hostInfo['ip'], $ipArr)) {
                $flag = true;
                $nodeuuid = $d['node_uuid'];
                break;
            }
        }
        $info = array(
            'flag' => $flag,
            'nodeuuid' => $nodeuuid
        );
        return json_encode($info, true);
    }

    /**
     * 得到备份的备份数据目录和历史数据目录
     * @param unknown $params
     */
    public function getBackupDirInfo($params)
    {
        $hostInfo = $this->getHostInfoWithUUID($params['hostuuid']);

        $detail = json_decode($hostInfo['detail'], true);
        //判断操作系统类型,给用户初始目录
        if ($detail['osType'] != Xphp::$_config['DB_CDP_OS_TYPE']['WINDOWS']) {
            //非windows
            $dir = array(
                'backupdir' => "/home/backupdata",
                'historydir' => "/home/historydata"
            );
        } else {
            //windows
            $dir = array(
                'backupdir' => "d:/backupdata",
                'historydir' => "d:/historydata"
            );
        }

        $backupHostIsServer = $this->checkBackupHostIsServer($params);
        $backupHostIsServer = json_decode($backupHostIsServer, true);
        if ($backupHostIsServer['flag']) {
            //如果是备份服务器,重新定义dir路径
            $dir = array(
                'backupdir' => "/backupdata",
                'historydir' => "/historydata"
            );
        }

        //TODO,发送两个目录到备份主机,检查是否可用
        //先假设可用,发送到客户端

        return json_encode($dir);

    }

    /**
     * 得到数据库CDP实时备份任务名
     * @param unknown $params
     */
    public function getBackupTaskName($params)
    {
        $taskName = "";
        $taskName .= '数据库CDP实时备份';
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到数据库恢复任务名
     * @param unknown $params
     */
    public function getRecoveryTaskName($params)
    {
        $taskName = "";
        $taskName .= '数据库恢复';
        return $this->getValidTaskName($taskName);
    }

    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    public function getValidTaskName($taskName)
    {
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if (empty($data) && empty($data1)) {
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }

    /**
     * 获取数据库实时备份任务监控基本信息
     * @param unknown $params
     */
    public function getBackupBasicInfo($params)
    {
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);

        $sql = "select bt.task_name, bt.module_type, bt.task_type, bt.task_status, cdt.product_host_uuid, 
                cdt.standby_host_uuid, cdt.config from bd_task bt, cdp_db_task cdt where bt.task_uuid = 
                cdt.task_uuid and cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);

        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
        $utils = Xphp::instance('Utils');

        $taskConfig = json_decode($d['config'], true);
        $databases = $taskConfig['producthostInfo']['dbInfo'];
        $vendorType = intval($databases[0]['dbtype']);
        $logSetting = $taskConfig['highInfo']['log'];
        $takeoverSetting = $taskConfig['highInfo']['takeover'];


        $info = array(
            'taskName' => $d['task_name'],
            'taskTypeDes' => $ptDes['TASKTYPEDES'][$d['task_type']],
            'taskType' => intval($d['task_type']),
            'status' => intval($d['task_status']),
            'statusDes' => $ptDes['TASKSTATUSDES'][$d['task_status']],
            'productHost' => $productHost['host_name'] . "(" . $productHost['ip'] . ")",
            'standbyHost' => $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")",
            'vendor' => $dbDes['DB_TYPE_DES'][$vendorType],
            'databases' => $databases,
            'logSetting' => $logSetting,
            'takeoverSetting' => $takeoverSetting,
            'backupDir' => $taskConfig['standbyhostInfo']['backupdir'],
            'historyDir' => $taskConfig['standbyhostInfo']['historydir'],
            'backupType' => intval($taskConfig['standbyhostInfo']['backuptype']),
            'historyCopys' => intval($taskConfig['standbyhostInfo']['historycopys']),


            "productip" => $productHost['ip'],
            "standbyip" => $standbyHost['ip'],
            "productuuid" => $productHost['host_uuid'],
            "standbyuuid" => $standbyHost['host_uuid'],

        );

        return json_encode($info, true);
    }

    /**
     * 得到备份任务运行动态详情信息
     * @param unknown $params
     */
    public function getBackupRunningInfo($params)
    {
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $sql = "select bt.task_status, cdt.product_host_uuid, cdt.standby_host_uuid from 
                bd_task bt, cdp_db_task cdt where bt.task_uuid = cdt.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);

        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $taskStatus = intval($d['task_status']);
        $taskStatusDes = $ptDes['TASKSTATUSDES'][$d['task_status']];
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);
        $syncFlag = false;  //同步状态

        $operate = "获取实时备份任务详情";
        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
            //如果从站离线,无法远程获取状态
            //主机状态/备机状态/变化数据条数/变化数据时间/最后运行日志/最后运行日志时间/当前时间
            $hstatus = $productHostStatus;
            $rstatus = $standbyHostStatus;
            $chgnum = 0;
            $chgtime = '----';
            $runmsgtxt = sprintf("备份主机 %s 离线,请检查网络连接!", $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")");
            $runmsgtime = date("Y-m-d H:i:s", time());
            $currenttime = date("Y-m-d H:i:s", time());
            $startTime = '----';
            $intervalTime = '----';
            $syncFlag = false;
            $takeoverMode = 0;
        } else {
            //远程获取状态
            $rpc = Xphp::instance('DbRPCHandler');
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                'hostip' => $productHost['ip'],
                'remoteip' => $standbyHost['ip'],
            );
            $result = $rpc->getBackupStatus($standbyHost['ip'], $msgData);
            $data = $this->checkRPCMsg($operate, $result);

            //             var_dump($result, $data);

            $result = $rpc->getBackupDbStatus($standbyHost['ip'], $msgData);
            $dbData = $this->checkRPCMsg($operate, $result);

            //             var_dump($result, $dbData);
//             $dbData = array_reverse($dbData);
            $startTime = '----';
            $intervalTime = '----';
            if (!empty($dbData[0])) {
                $startTime = $dbData[0]['btime0'];
            }
            if ("----" != $startTime) {
                $startTimetoTime = strtotime($startTime);
                $currentTimetoTime = strtotime($data[0]['curTime']);
                $intervalTime = $currentTimetoTime - $startTimetoTime;
                $utils = Xphp::instance('Utils');
                $intervalTime = $utils->secToTime($intervalTime);
            }

            $dbStatusConf = Xphp::$_config['DB_CDP_BACKUP_DB_STATUS'];
            foreach ($dbData as $d) {
                if ($dbStatusConf['SYNCING'] == $d['bkstatus']) {
                    //如果有一个在同步
                    $syncFlag = true;
                    break;
                }
            }

            //主机状态/备机状态/变化数据条数/变化数据时间/最后运行日志/最后运行日志时间/当前时间
            if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $productHostStatus) {
                $hstatus = $productHostStatus;
            } else {
                $hstatus = $data[0]['hstatus'];
            }

            if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
                $rstatus = $standbyHostStatus;
            } else {
                $rstatus = $data[0]['rstatus'];
            }

            $chgnum = $data[0]['chgnum'];
            $chgtime = $data[0]['chgtime'];
            $runmsgtxt = $data[0]['runmsgtxt'];
            $runmsgtime = $data[0]['runmsgtime'];
            $currenttime = $data[0]['curTime'];


            //获取接管服务状态
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
            );
            $rpc = Xphp::instance('DbRPCHandler');
            $standbyResult = $rpc->getServiceStatus($standbyHost['ip'], $rpcMsg);
            $data = $this->checkRPCMsg($operate, $standbyResult);
            $takeoverMode = $data['runstatus'];

            //如果是接管任务,获取接管信息
            if (
                $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER'] ||
                $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER_STARTING'] ||
                $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER_STOPPING']
            ) {
                $rpcMsg = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
                );
                $rpc = Xphp::instance('DbRPCHandler');
                $standbyResult = $rpc->checkIfTakeOverId($standbyHost['ip'], $rpcMsg);
                $operate = "获取接管状态";
                $data = $this->checkRPCMsg($operate, $standbyResult);
                $takeroverid = $data[0]['takeroverid'];

                $startStatusArr = Xphp::$_config['DB_CDP_TAKEOVER_START_STATUS'];
                $stopStatusArr = Xphp::$_config['DB_CDP_TAKEOVER_STOP_STATUS'];

                if (in_array($takeroverid, $startStatusArr)) {
                    $rpcMsg = array(
                        'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
                    );
                    $rpc = Xphp::instance('DbRPCHandler');
                    $standbyResult = $rpc->getStartInfo($standbyHost['ip'], $rpcMsg);
                    $data = $this->checkRPCMsg($operate, $standbyResult);

                    $stepInfo = $data[0]['steps'];
                }
                if (in_array($takeroverid, $stopStatusArr)) {
                    $rpcMsg = array(
                        'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
                    );
                    $rpc = Xphp::instance('DbRPCHandler');
                    $standbyResult = $rpc->getStopInfo($standbyHost['ip'], $rpcMsg);
                    $data = $this->checkRPCMsg($operate, $standbyResult);

                    $stepInfo = $data[0]['steps'];
                }

            }
        }

        //只有运行状态才显示开始时间和持续时间
        if (
            !($taskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'] ||
                $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER'] ||
                $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER_STARTING'] ||
                $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER_STOPPING'])
        ) {
            $startTime = "----";
            $intervalTime = "----";
        }
        $info = array(
            're' => true,
            'productHostSatus' => $hstatus,
            'standbyHostSatus' => $rstatus,
            'dataChange' => $chgnum,
            'dataChangeTime' => $chgtime,
            'lastMsg' => $runmsgtxt,
            'lastMsgTime' => $runmsgtime,
            'taskStatus' => $taskStatus,
            'taskStatusDes' => $taskStatusDes,
            'currentTime' => $currenttime,
            'startTime' => $startTime,
            'intervalTime' => $intervalTime,
            'syncFlag' => $syncFlag,
            'takeoverMode' => $takeoverMode
        );

        return json_encode($info, true);
    }

    /**
     * 得到实时备份任务操作码
     * @param unknown $taskuuid
     * @param unknown $status
     */
    public function getBackupTaskOpCode($taskuuid, $status)
    {
        $sql = "select config from cdp_db_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $config = json_decode($data[0]['config'], true);

        $taskoverFlag = $config['highInfo']['takeover']['check'];
        $backuptype = intval($config['standbyhostInfo']['backuptype']);
        $opCode = array(1, 2, 3, 4);
        if ($backuptype == Xphp::$_config['DB_CDP_BACKUP_TYPE']['REALTIME_BACKUP']) {
            //如果是实时备份
            $opCode = array(1, 2, 3, 4);
        } else {
            if ($taskoverFlag) {
                //如果接管开启
                //如果是业务接管或实时备份+业务接管
                //如果任务状态在接管中,启动或停止中
                switch ($status) {
                    case Xphp::$_config['TASKSTATUS']['TAKEOVER']:
                        $opCode = array(11, 12);
                        break;
                    case Xphp::$_config['TASKSTATUS']['TAKEOVER_STARTING']:
                        $opCode = array(11, 12);
                        break;
                    case Xphp::$_config['TASKSTATUS']['TAKEOVER_STOPPING']:
                        $opCode = array(11, 12);
                        break;
                    default:
                        $opCode = array(1, 2, 3, 4, 11, 12);
                        break;
                }
            }
        }
        return $opCode;
    }

    /**
     * 得到实时备份任务的监控日志
     * @param unknown $params
     */
    public function getBackupRunningLog($params)
    {
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $sql = "select bt.task_status, cdt.product_host_uuid, cdt.standby_host_uuid, cdt.config from
                bd_task bt, cdp_db_task cdt where bt.task_uuid = cdt.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);
        $taskStatus = $d['task_status'];
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
            //备机离线
            $info = array();
            $runmsgtxt = sprintf("备份主机 %s 离线,请检查网络连接!", $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")");
            $info[] = array(date("Y-m-d H:i:s"), '错误', $runmsgtxt, array('level' => 3));
            return json_encode($info);
        }

        //如果是接管任务,获取接管任务日志
        if (
            $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER'] ||
            $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER_STARTING'] ||
            $taskStatus == Xphp::$_config['TASKSTATUS']['TAKEOVER_STOPPING']
        ) {
            return $this->getTakeoverRunningLog($taskStatus, $productHost, $standbyHost);
        }

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $productHostStatus) {
            //主机离线
            $info = array();
            $runmsgtxt = sprintf("生产主机 %s 离线,请检查网络连接!", $productHost['host_name'] . "(" . $productHost['ip'] . ")");
            $info[] = array(date("Y-m-d H:i:s"), '错误', $runmsgtxt, array('level' => 3));
            return json_encode($info);
        }

        $productHostTxt = $productHost['host_name'] . "(" . $productHost['ip'] . ")";
        $standbyHostTxt = $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")";

        //如果任务已经停止
        if ($taskStatus == Xphp::$_config['TASKSTATUS']['STOPPED']) {
            return json_encode(array());
        }



        $config = json_decode($d['config'], true);
        $dbCount = count($config['producthostInfo']['dbInfo']);

        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hostip' => $productHost['ip'],
            'remoteip' => $standbyHost['ip'],
        );

        $operate = "获取实时备份任务数据库详情";
        $rpc = Xphp::instance('DbRPCHandler');
        $result = $rpc->getBackupDbStatus($standbyHost['ip'], $msgData);
        $data = $this->checkRPCMsg($operate, $result);


        $utils = Xphp::instance('Utils');
        $data = $utils->arraySort($data, 'btime0', 'asc', 0, -1);

        //         $data = array_reverse($data);

        $dbFailureCount = 0;
        $finishCount = 0;
        $finishTime = '';
        $info = array();
        $endBkstatus = 0;
        $endBkstatusTime = '----';
        $dbStatusConf = Xphp::$_config['DB_CDP_BACKUP_DB_STATUS'];
        foreach ($data as $key => $value) {
            $bkstatus = $value['bkstatus'];
            $dbTxt = $value['dbsrv'] . " > " . $value['dbname'];
            if ($dbStatusConf['WAITING'] == $key) {
                //如果是第一个数据库,要初始化开始的日志
                $info[] = array($value['btime0'], '正常', '获取任务配置信息', array('level' => 1));
                $info[] = array($value['btime0'], '正常', '准备数据库实时备份环境', array('level' => 1));
                $info[] = array($value['btime0'], '正常', '检查并启动生产主机"' . $productHostTxt . '"服务', array('level' => 1));
                $info[] = array($value['btime0'], '正常', '检查并启动备份主机"' . $standbyHostTxt . '"服务', array('level' => 1));
                $info[] = array($value['etime0'], '正常', '备份环境检查完成，开始同步数据库，数据库数量"' . $dbCount . '"', array('level' => 1));
            }
            if ($dbStatusConf['SYNCING'] == $bkstatus) {
                //正在同步这个数据库
                $info[] = array($value['btime3'], '正常', '开始同步生产主机数据库"' . $dbTxt . '"', array('level' => 1));
            }
            if ($dbStatusConf['SYNCOVER'] == $bkstatus || $dbStatusConf['MONITOR'] == $bkstatus) {
                //这个数据库已经同步完成
                $info[] = array($value['btime3'], '正常', '开始同步生产主机数据库"' . $dbTxt . '"', array('level' => 1));
                $info[] = array($value['btime4'], '正常', '数据库"' . $dbTxt . '"同步完成,并完成数据校验', array('level' => 1));
                $finishCount++;
                $finishTime = $value['btime4'];
            }
            if (0 > $bkstatus) {
                //错误
                $info[] = array(
                    $value['btime_err'],
                    '错误',
                    '数据库"' . $dbTxt . '"备份错误,请检查! 错误码: "' . $bkstatus . '",
                                                错误描述: "' . $this->getErrorCodeDes($bkstatus) . '"',
                    array('level' => 3)
                );
                $dbFailureCount++;
            }
            $endBkstatus = $bkstatus;
            $endBkstatusTime = $value['btime5'];
        }
        //如果全部都完了,添加结束日志
        if ($finishCount + $dbFailureCount == $dbCount && $dbCount != 0) {
            if ($dbFailureCount > 0) {
                $finishDes = '同步备份主机数据库完成,数据库总数: "' . $dbCount . '" , 成功: "' . $finishCount . '" , 失败: "' . $dbFailureCount . '"';
                $level = 2;
            } else {
                $finishDes = '同步备份主机数据库完成,数据库总数: "' . $dbCount . '" , 成功: "' . $finishCount . '"';
                $level = 1;
            }
            $info[] = array($finishTime, '正常', $finishDes, array('level' => $level));
        }
        if (5 == $endBkstatus) {
            //crc ok
            $info[] = array($endBkstatusTime, '正常', '开始实时备份监控', array('level' => 1));
        }
        if ($taskStatus != Xphp::$_config['TASKSTATUS']['STOPPED']) {
            //得到最后消息
            $result = $rpc->getBackupStatus($standbyHost['ip'], $msgData);
            $data = $this->checkRPCMsg($operate, $result);
            $runmsgtxt = $data[0]['runmsgtxt'];
            $runmsgtime = $data[0]['runmsgtime'];
            if ($runmsgtime < $endBkstatusTime) {
                //特殊处理最后消息的时间小于CRC OK的时间
                $runmsgtime = $endBkstatusTime;
            }
            if (!empty($runmsgtxt)) {
                //TODO 过滤IP,这里在多主机对应一个备机的时候可能会出现其他机器的消息
                if (strpos($runmsgtxt, $productHost['ip']) !== false) {
                    $info[] = array($runmsgtime, '正常', $runmsgtxt, array('level' => 1));
                }
            }
        }
        $info = array_reverse($info);

        return json_encode($info);
    }

    /**
     * 获取接管日志
     * @param unknown $params
     */
    private function getTakeoverRunningLog($taskStatus, $productHost, $standbyHost)
    {
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
        );
        $rpc = Xphp::instance('DbRPCHandler');
        $standbyResult = $rpc->checkIfTakeOverId($standbyHost['ip'], $rpcMsg);
        $operate = "获取接管状态";
        $data = $this->checkRPCMsg($operate, $standbyResult);
        $takeroverid = $data[0]['takeroverid'];

        $startStatusArr = Xphp::$_config['DB_CDP_TAKEOVER_START_STATUS'];
        $stopStatusArr = Xphp::$_config['DB_CDP_TAKEOVER_STOP_STATUS'];

        $info = array();
        //启动接管状态,这里有点特殊的是自动接管也在这个状态中
        if (in_array($takeroverid, $startStatusArr)) {
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
            );
            $rpc = Xphp::instance('DbRPCHandler');
            $standbyResult = $rpc->getStartInfo($standbyHost['ip'], $rpcMsg);
            $data = $this->checkRPCMsg($operate, $standbyResult);

            $stepInfo = $data[0]['steps'];
            foreach ($stepInfo as $key => $value) {
                if (0 == $key)
                    continue;        //第一项未总状态,跳过,标准流程0,1,4,5,6,7,9,10,11,12
                switch ($value['step']) {
                    case 0://0
                        $info[] = array($value['btime'], '正常', "启动业务接管,进入接管流程", array('level' => 1));
                        break;
                    case $startStatusArr['S_PREPARAM']://1
                        $info[] = array($value['btime'], '正常', "获取接管配置,准备接管环境", array('level' => 1));
                        break;
                    case $startStatusArr['S_STARTING']://4
                        $info[] = array($value['btime'], '正常', "环境检查完成,开始启动接管", array('level' => 1));
                        break;
                    case $startStatusArr['S_STOPHOSTNAME']://5
//                         $info[] = array($value['btime'], '正常', "停用接管服务器主机名", array('level'=>1));
                        break;
                    case $startStatusArr['S_STOPHOSTIP']://6
//                         $info[] = array($value['btime'], '正常', "停用接管服务器IP", array('level'=>1));
                        break;
                    case $startStatusArr['S_STOPCFGTASK']://7
//                         $info[] = array($value['btime'], '正常', "停用接管服务器服务", array('level'=>1));
                        break;
                    case $startStatusArr['S_HOSTIP']://9
                        $info[] = array($value['btime'], '正常', "配置接管的生产主机IP地址", array('level' => 1));
                        break;
                    case $startStatusArr['S_HNAMEBINDIP']://10
                        $info[] = array($value['btime'], '正常', "绑定接管的生产主机IP地址到备份主机", array('level' => 1));
                        break;
                    case $startStatusArr['S_CFGTASK']://11
                        $info[] = array($value['btime'], '正常', "重启备份主机的操作系统接管服务", array('level' => 1));
                        break;
                    case $startStatusArr['S_TASKOK']://12
                        $info[] = array($value['btime'], '正常', "接管完成,业务接管中...", array('level' => 1));
                        break;
                    default:
                        break;
                }
            }

        }
        //停止接管状态
        if (in_array($takeroverid, $stopStatusArr)) {
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']
            );
            $rpc = Xphp::instance('DbRPCHandler');
            $standbyResult = $rpc->getStopInfo($standbyHost['ip'], $rpcMsg);
            $data = $this->checkRPCMsg($operate, $standbyResult);
            $stepInfo = $data[0]['steps'];
            foreach ($stepInfo as $key => $value) {
                if (0 == $key)
                    continue;        //第一项未总状态,跳过,标准流程0,101,105,106,107,113
                switch ($value['step']) {
                    case 0://0
                        $info[] = array($value['btime'], '正常', "停止业务接管,进入停止接管流程", array('level' => 1));
                        break;
                    case $stopStatusArr['P_PREPARAM']://101
                        $info[] = array($value['btime'], '正常', "获取接管配置,准备停止接管环境", array('level' => 1));
                        break;
                    case $stopStatusArr['P_STOPHOSTNAME']://105
                        $info[] = array($value['btime'], '正常', "停止接管主机名", array('level' => 1));
                        break;
                    case $stopStatusArr['P_STOPHOSTIP']://106
                        $info[] = array($value['btime'], '正常', "停止接管IP地址", array('level' => 1));
                        break;
                    case $stopStatusArr['P_STOPCFGTASK']://107
                        $info[] = array($value['btime'], '正常', "解除绑定接管的生产主机IP地址", array('level' => 1));
                        break;
                    case $stopStatusArr['P_TASKEND']://113
                        $info[] = array($value['btime'], '正常', "业务接管停止成功", array('level' => 1));
                        break;
                    default:
                        break;
                }
            }
        }
        $info = array_reverse($info);
        return json_encode($info);
    }

    /**
     * 统一获取CDP数据库备份错误定义描述
     * @param unknown $error    错误码
     */
    private function getErrorCodeDes($error)
    {
        $errorCode = abs($error);
        $error = include_once CONF_PATH . 'dbcdp_error.php';
        $errorKey = $error['errorCode'][$errorCode];
        $errorCodeDes = $error['errorCodeDes'][$errorKey];
        return $errorCodeDes;
    }

    /**
     * 得到实时备份任务详情: 得到数据库列表
     * @param unknown $params
     */
    public function getDetailsDatabase($params)
    {
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $sql = "select bt.task_status, cdt.product_host_uuid, cdt.standby_host_uuid, cdt.config from
                bd_task bt, cdp_db_task cdt where bt.task_uuid = cdt.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $dbConfigAll = json_decode($d['config'], true);
        $dbConfig = $dbConfigAll['producthostInfo']['dbInfo'];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);
        $taskStatus = intval($d['task_status']);
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);

        $backupType = intval($dbConfigAll['standbyhostInfo']['backuptype']);

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
            //备机离线
            $records = array();
            $records["data"] = array();
            $records["draw"] = $params['draw'];
            return json_encode($records);
        }
        $rpc = Xphp::instance('DbRPCHandler');
        $operate = '获取实时备份数据库状态';
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hostip' => $productHost['ip'],
            'remoteip' => $standbyHost['ip']
        );

        $result = $rpc->getBackupDbStatus($standbyHost['ip'], $data);
        $data = $this->checkRPCMsg($operate, $result);


        $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
        $records = array();
        $records["data"] = array();
        $i = 1;
        $utils = Xphp::instance('Utils');
        foreach ($data as $d) {
            //历史数据
            $historySize = $d['historySize'];
            //备份数据  rdbsize + rollbacksize
            $backupSize = $d['rdbsize'] + $d['rollbackSize'];
            //接管数据
            $roolbackSize = $d['rdbsize'];

            if ($backupType == Xphp::$_config['DB_CDP_BACKUP_TYPE']['REALTIME_BACKUP']) {
                //如果是实时备份任务类型,接管数据为空
                $roolbackSize = 0;
            } else if ($backupType == Xphp::$_config['DB_CDP_BACKUP_TYPE']['TAKEOVER']) {
                //如果是接管任务类型,备份数据为空,历史数据为空
                $backupSize = 0;
                $historySize = 0;
            }

            $filesize = 0;
            $recvsize = 0;
            $syncFlag = true;  //同步完成标志

            $productFileList = array();
            $standbyFileList = array();
            if (in_array($d['bkstatus'], Xphp::$_config['DB_CDP_BACKUP_DB_STATUS'])) {
                $dbStatusDes = $dbDes['BACKUP_DB_STATUS'][$d['bkstatus']];
                $dbStatus = $d['bkstatus'];
            } else {
                $dbStatus = $d['bkstatus'];
                $dbStatusDes = '错误';
            }

            //如果任务处于停止或错误,数据库状态处于停止状态
            if (
                $taskStatus == Xphp::$_config['TASKSTATUS']['STOPPED'] ||
                $taskStatus == Xphp::$_config['TASKSTATUS']['ERROR']
            ) {
                $dbStatus = 6;
                $dbStatusDes = '停止';
            }

            $syncStartTime = $d['btime3'];
            $syncEndTime = $d['etime3'];
            $currentTime = $d['curTime'];

            foreach ($d['productFile'] as $p) {
                $filesize += $p['fsize'];
                $recvsize += $p['recvsize'];            //当前同步大小
                $productFileList[] = $p['msqlfile'];
                $standbyFileList[] = $p['bsqlfile'];
                //如果结束时间是空  表示未结束,
                if (empty($p['etime'])) {
                    $syncFlag = false;
                }
            }

            $speedAndProcess = $this->calSyncDbSpeedAndProcess(
                $dbStatus,
                $syncStartTime,
                $syncEndTime,
                $currentTime,
                $filesize,
                $recvsize
            );

            //特殊处理一下文件列表,如果没有的时候置空
            $records["data"][] = array(
                $i++,
                $d['dbname'],
                $d['dbsrv'],
                $dbDes['DB_TYPE_DES'][$d['dbtype']],
                $utils->calSize($backupSize),
                $speedAndProcess['progress'],
                $speedAndProcess['speed'],
                $utils->calSize($historySize),
                $utils->calSize($roolbackSize),
                $dbStatusDes,
                array(
                    'productFileList' => $this->detectionFilelist($productFileList),
                    'standbyFileList' => $this->detectionFilelist($standbyFileList),
                    'bkStatus' => $dbStatus,
                    'dbtype' => $d['dbtype'],
                    'dbconfig' => $this->getDBconfigInfo($dbConfig, $d['dbtype'], $d['dbsrv'], $d['dbname']),
                )
            );
        }
        $records["draw"] = $params['draw'];
        return json_encode($records);
    }

    /**
     * 得到特定数据库的配置信息
     * @param unknown $config
     * @param unknown $dbtype
     * @param unknown $dbsrv
     * @param unknown $dbname
     */
    private function getDBconfigInfo($config, $dbtype, $dbsrv, $dbname)
    {
        $utils = Xphp::instance('Utils');
        foreach ($config as $conf) {
            if ($conf['dbtype'] == $dbtype && $conf['dbsrv'] == $dbsrv && $conf['dbname'] == $dbname) {
                if($conf['memory_target']==""){
                    $memoryTarget = 0;
                }else{
                    $memoryTarget = $conf['memory_target'];
                }
				if($conf['pga_aggregate']==""){
                    $pgaAggregate = 0;
                }else{
                    $pgaAggregate = $conf['pga_aggregate'];
                }
				if($conf['sga_max_size']==""){
                    $sgaMaxSize = 0;
                }else{
                    $sgaMaxSize = $conf['sga_max_size'];
                }
				if($conf['sga_target']==""){
                    $sgaTarget = 0;
                }else{
                    $sgaTarget = $conf['sga_target'];
                }
                $conf['memory_target'] = $utils->calSize($memoryTarget);
                $conf['pga_aggregate'] = $utils->calSize($pgaAggregate);
                $conf['sga_max_size'] = $utils->calSize($sgaMaxSize);
                $conf['sga_target'] = $utils->calSize($sgaTarget);
                return $conf;
            }
        }
    }

    /**
     * 计算数据库同步的速度和进度
     * @param unknown $dbStatus     数据库状态
     * @param unknown $startTime    同步开始时间
     * @param unknown $endTime      同步结束时间
     * @param unknown $totalSize    总大小
     * @param unknown $processSize  完成大小
     */
    private function calSyncDbSpeedAndProcess($dbStatus, $startTime, $endTime, $currentTime, $totalSize, $processSize)
    {
        $info = array(
            'speed' => '--',
            'progress' => '--'
        );
        //如果不在同步和监控状态,直接返回
        if (
            $dbStatus != Xphp::$_config['DB_CDP_BACKUP_DB_STATUS']['SYNCING'] &&
            $dbStatus != Xphp::$_config['DB_CDP_BACKUP_DB_STATUS']['SYNCOVER'] &&
            $dbStatus != Xphp::$_config['DB_CDP_BACKUP_DB_STATUS']['MONITOR']
        ) {
            return $info;
        }

        //如果正在同步,结束时间使用当前时间
        if ($dbStatus == Xphp::$_config['DB_CDP_BACKUP_DB_STATUS']['SYNCING']) {
            $endTime = $currentTime;
        }
        //如果同步完成,结束时间使用结束时间

        $unitTime = strtotime($endTime) - strtotime($startTime);
        $unitTime = $unitTime == 0 ? 1 : $unitTime;
        //计算进度
        $utils = Xphp::instance('Utils');
        $speed = $utils->calSpeed($processSize / $unitTime);
        $process = $utils->calPercent($totalSize, $processSize);

        $info = array(
            'speed' => $speed,
            'progress' => $process
        );

        return $info;
    }

    /**
     * 处理文件列表,防止返回不合规内容
     * @param unknown $filelist
     */
    private function detectionFilelist($filelist)
    {
        if (empty($filelist)) {
            $filelist = array(
                "----",
                "----"
            );
        }
        return $filelist;
    }

    /**
     * 获取恢复任务监控详情
     * @param unknown $params
     */
    public function getRecoveryAllInfo($params)
    {
        $taskuuid = $params['uuid'];
        $lastTime = $params['taskinfo']['t'];
        $lastSize = $params['taskinfo']['size'];
        $this->paramsCheck($taskuuid);

        $sql = "select bt.task_name, bt.module_type, bt.task_type, bt.task_status, cdt.product_host_uuid,
                cdt.standby_host_uuid, cdt.config from bd_task bt, cdp_db_task cdt where bt.task_uuid =
                cdt.task_uuid and cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);
        $config = json_decode($d['config'], true);

        $operate = "获取恢复任务详情";
        $rpc = Xphp::instance('DbRPCHandler');
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'ipstr' => $config['dbinfo']['hostip'],
            'dbtype' => $config['dbinfo']['dbtype'],
            'dbsrv' => $config['dbinfo']['instance'],
            'dbname' => $config['dbinfo']['name'],
            'histime' => $config['timeperiod']
        );
        if ($productHost['ip'] == $standbyHost['ip']) {
            //本机恢复
            $result = $rpc->getRecoveryInfo($standbyHost['ip'], $msgData);
        } else {
            //异机恢复
            $result = $rpc->getRecoveryCliInfo($standbyHost['ip'], $msgData);
            //             var_dump($msgData, $standbyHost['ip'], $result);
        }

        $dbData = array();
        if ($result['result']) {
            $data = $this->checkRPCMsg($operate, $result);
            $dbData = $data[0];
        }

        //         var_dump($msgData, $result, $data);
//         return;

        $info = array(
            'basicInfo' => $this->getRecoveryBasicInfo($d, $productHost, $standbyHost, $config, $dbData),
            'jobSpeed' => $this->getRecoveryJobSpeed($lastTime, $lastSize, $dbData),
            'log' => $this->getRecoveryRunningLog($dbData, $productHost, $standbyHost, $msgData)
        );
        return json_encode($info);
    }

    /**
     * 得到恢复任务监控的任务详情基本信息
     * @param unknown $params
     */
    private function getRecoveryBasicInfo($d, $productHost, $standbyHost, $config, $dbData)
    {
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
        $utils = Xphp::instance('Utils');
        $workidConf = Xphp::$_config['DB_CDP_RECOVERY_WORDID'];

        $vendorType = $config['dbinfo']['dbtype'];
        $instance = $config['instancename'];
        $databases = $config['databasename'];
        $timepoint = $config['timepointname'];

        $totalSize = $utils->calSize($dbData['totalflen']);
        if ($dbData['workid'] == $workidConf['FINISHED'] || $dbData['workid'] == $workidConf['UNWRITE']) {
            //如果是完成状态,直接使用totalflen
            $currentSize = $dbData['totalflen'];
        } else {
            //其他状态 okflen + copylen
            $currentSize = $dbData['okflen'] + $dbData['copylen'];
        }

        if (0 != $dbData['msgcode']) {
            //如果是错误状态
            $currentSize = $dbData['okflen'] + $dbData['copylen'];
        }

        $currentSizeDes = $utils->calSize($currentSize);
        $startTime = $dbData['btime'];
        $currentTime = $dbData['curtime'];
        if ($dbData['workid'] == $workidConf['FINISHED']) {
            //如果已经完成,计算持续时间的结束时间为etime
            $currentTime = $dbData['etime'];
        }
        $intervalTime = $utils->secToTime(strtotime($currentTime) - strtotime($startTime));

        $progress = $utils->calPercent($dbData['totalflen'], $currentSize);
        $progress = sprintf("%.2f", substr($progress, 0, -1)) . "%";

        $info = array(
            'flag' => true,
            'taskName' => $d['task_name'],
            'taskTypeDes' => $ptDes['TASKTYPEDES'][$d['task_type']],
            'taskType' => $d['task_type'],
            'status' => intval($d['task_status']),
            'statusDes' => $ptDes['TASKSTATUSDES'][$d['task_status']],
            'productHost' => $productHost['host_name'] . "(" . $productHost['ip'] . ")",
            'standbyHost' => $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")",
            'vendor' => $dbDes['DB_TYPE_DES'][$vendorType],
            'instance' => $instance,
            'databases' => $databases,
            'timepoint' => $timepoint,
            'totalSize' => $totalSize,
            'currentSize' => $currentSizeDes,
            'startTime' => $startTime,
            'intervalTime' => $intervalTime,
            'totalprogress' => $progress,
            'progress' => $progress,

            "productip" => $productHost['ip'],
            "standbyip" => $standbyHost['ip'],
            "productuuid" => $productHost['host_uuid'],
            "standbyuuid" => $standbyHost['host_uuid'],
        );

        return $info;
    }

    /**
     * 得到恢复任务流量
     * @param unknown $params
     */
    public function getRecoveryJobSpeed($lastTime, $lastSize, $dbData)
    {
        $utils = Xphp::instance('Utils');
        $workidConf = Xphp::$_config['DB_CDP_RECOVERY_WORDID'];
        $currentTime = $dbData['curtime'];

        if (empty($currentTime)) {
            $currentTime = date("Y-m-d H:i:s");
        }


        if ($dbData['workid'] == $workidConf['FINISHED'] || $dbData['workid'] == $workidConf['UNWRITE']) {
            //如果是完成状态,直接使用totalflen
            $currentSize = $dbData['totalflen'];
        } else {
            //其他状态 okflen + copylen
            $currentSize = $dbData['okflen'] + $dbData['copylen'];
        }

        //第一次没有上次时间.
        if (empty($lastTime) || empty($lastSize)) {
            $data = array(
                "speed" => 0,
                "t" => strtotime($currentTime),
                "nowTime" => date("H:i:s", strtotime($currentTime)),
                "size" => $currentSize / 1024
            );

            return $data;
        }

        $cSize = $currentSize - $lastSize * 1024;
        $cTime = strtotime($currentTime) - $lastTime;
        $speed = round($cSize / $cTime / 1024, 2);
        if ($speed < 0)
            $speed = 0;

        $data = array(
            "speed" => $speed,
            "t" => strtotime($currentTime),
            "nowTime" => date("H:i:s", strtotime($currentTime)),
            "size" => $currentSize / 1024
        );

        return $data;
    }

    /**
     * 得到恢复任务的任务监控日志
     * @param unknown $params
     */
    public function getRecoveryRunningLog($dbData, $productHost, $standbyHost, $msgData)
    {
        $startTime = $dbData['btime'];
        $endTime = $dbData['etime'];
        $currentTime = $dbData['curtime'];
        $totalfnum = $dbData['totalfnum'];
        $workidConf = Xphp::$_config['DB_CDP_RECOVERY_WORDID'];


        $info = array();
        if (empty($dbData)) {
            return $info;
        }
        //初始化开始日志 0 1
        $info[] = array($startTime, '正常', "启动数据恢复任务", array('level' => 1));
        $info[] = array($dbData['segtime0'], '正常', "检查数据恢复环境", array('level' => 1));
        $info[] = array($startTime, '正常', sprintf('检查并启动备份主机"%s"服务', $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")"), array('level' => 1));
        $info[] = array($startTime, '正常', sprintf('检查并启动恢复目标主机"%s"服务', $productHost['host_name'] . "(" . $productHost['ip'] . ")"), array('level' => 1));
        $info[] = array($dbData['segtime1'], '正常', sprintf("扫描恢复文件,总文件数:%s", $totalfnum), array('level' => 1));


        if ($dbData['workid'] == $workidConf['TRANSMITTING']) {
            //workid 3 传输
            $info[] = array($dbData['segtime3'], '正常', "开始数据恢复,传输恢复文件", array('level' => 1));

            if (!empty($dbData['okfnum']) && !empty($dbData['curfile'])) {
                $info[] = array($dbData['curtime'], '正常', sprintf('完成文件 %s 个, 当前处理文件 "%s"', $dbData['okfnum'], $dbData['curfile']), array('level' => 1));
            }

        }
        if ($dbData['workid'] == $workidConf['UNWRITE']) {
            //workid 4 导入
            $progress = round(($dbData['maxrollstep'] - $dbData['currollstep']) / ($dbData['maxrollstep'] - $dbData['dstrollstep']), 4) * 100;
            //计算导入百分比
            $info[] = array($dbData['segtime3'], '正常', "开始数据恢复,传输恢复文件", array('level' => 1));
            $info[] = array($dbData['segtime4'], '正常', sprintf("文件传输完成,正在恢复数据库,恢复进度 %s", $progress . "%"), array('level' => 1));

        }
        if ($dbData['workid'] == $workidConf['FINISHED'] && 0 == $dbData['msgcode']) {
            //workid 2 完成 scanBackupFiles 
            $info[] = array($dbData['segtime3'], '正常', "开始数据恢复,传输恢复文件", array('level' => 1));
            $info[] = array($dbData['segtime4'], '正常', "文件传输完成,正在恢复数据库,恢复进度 100%", array('level' => 1));

            $rpc = Xphp::instance('DbRPCHandler');
            $result = $rpc->scanBackupFiles($standbyHost['ip'], $msgData);
            if ($result['result']) {
                //如果请求成功,添加恢复路径
                $data = $result['data']['data'];
                foreach ($data as $d) {
                    $info[] = array($endTime, '正常', "恢复数据库文件路径:" . $d['file'], array('level' => 1));
                }
            }

            $info[] = array($endTime, '正常', "恢复任务成功完成", array('level' => 1));
        }
        if (0 != $dbData['msgcode']) {
            $info[] = array(
                $endTime,
                '错误',
                "数据恢复出错,错误码:" . $dbData['msgcode'] . ",错误消息:" . $dbData['msgtxt'],
                array('level' => 3)
            );
        }

        $info = array_reverse($info);

        return $info;
    }

    /**
     * 备份数据管理
     * 数据库树
     * @param unknown $params
     */
    public function getCDPDBTree($params)
    {
        //找到所有的任务的备份主机
        $sql = "select bt.task_uuid, bt.task_name, cdt.product_host_uuid, cdt.standby_host_uuid, cdt.config, 
                cdh.host_name, cdh.ip, cdh.status, cdh.user_uuid 
                from bd_task bt, cdp_db_task cdt,cdp_db_host cdh 
                where bt.task_uuid = cdt.task_uuid and cdt.standby_host_uuid = cdh.host_uuid 
                    and bt.task_type = ?";

        $sqlParams = array(Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP']);
        // 如果是有全局观察者权限，那么显示所有的数据
        if (!(in_array('global_read', $_SESSION['permissionArr']) || in_array('global_write', $_SESSION['permissionArr']))) {
            // 没有全局观察者权限
            $sql .= "  and cdh.user_uuid = ?";
            array_push($sqlParams, [Xphp::$_user['useruuid']]);
        }

        $data = $this->dbSelect($sql, $sqlParams);

        $dbTree = array();
        $hostuuid = array();
        foreach ($data as $d) {
            if (!in_array($d['standby_host_uuid'], $hostuuid)) {
                //添加第一层  主机层
                $dbTree[] = array(
                    'id' => $d['standby_host_uuid'],
                    'pid' => 0,
                    'name' => $d['host_name'] . "(" . $d['ip'] . ")",
                    'ip' => $d['ip'],
                    'status' => $d['status'],
                    "open" => false,
                    "title" => $d['host_name'] . "(" . $d['ip'] . ")",
                    "type" => 1,
                    "icon" => './img/db/host.png',
                    "isParent" => true
                );
                $hostuuid[] = $d['standby_host_uuid'];
            }
        }
        return json_encode($dbTree);
    }

    /**
     * 根据主机IP地址获取主机信息
     * @param unknown $ip
     */
    private function getHostInfoWithIP($ip)
    {
        $sql = "select host_uuid, host_name, ip, status from cdp_db_host where ip = ?";
        $data = $this->dbSelect($sql, array($ip));
        return $data[0];
    }

    /**
     * 异步获取备份树
     * @param unknown $params
     */
    public function getDatabaseSyncTree($params)
    {
        $hostuuid = $params['id'];
        $hostip = $params['ip'];
        $hostname = $params['name'];
        $this->paramsCheck($hostuuid, $hostip);

        $operate = "获取备份数据库信息";
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
        );
        $result = $rpc->scanBackupDatabase($hostip, $data);
        $data = $this->checkRPCMsg($operate, $result);

        $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
        $utils = Xphp::instance('Utils');
        $dbTree = array();
        $dbArr = array();
        $hostArr = array();
        $vendorArr = array();
        foreach ($data as $d) {
            if (!in_array($d['hostip'], $hostArr)) {
                $hostinfo = $this->getHostInfoWithIP($d['hostip']);
                //添加第二层  生产主机层
                $dbTree[] = array(
                    'id' => $d['hostip'],
                    'pid' => $hostuuid,
                    'name' => $hostinfo['host_name'] . "(" . $d['hostip'] . ")",
                    "open" => true,
                    "title" => $hostinfo['host_name'] . "(" . $d['hostip'] . ")",
                    "type" => 2,
                    "icon" => './img/db/host.png',
                );
                $hostArr[] = $d['hostip'];
            }

            $vendor = $d['hostip'] . "_" . $d['dbtype'];
            if (!in_array($vendor, $vendorArr)) {
                //添加第三层  数据库类型层
                $dbTree[] = array(
                    'id' => $vendor,
                    'pid' => $d['hostip'],
                    'name' => $dbDes['DB_TYPE_DES'][$d['dbtype']],
                    "open" => true,
                    "title" => $d['hostip'],
                    "type" => 3,
                    "icon" => $this->getDbTypeIcon($d['dbtype']),
                );
                $vendorArr[] = $vendor;
            }

            foreach ($d['instance'] as $ins) {
                $instance = md5($d['hostip'] . $d['dbtype'] . $ins['name']);
                //添加第四层  实例层
                $dbTree[] = array(
                    'id' => $instance,
                    'pid' => $vendor,
                    'name' => $ins['name'],
                    "open" => true,
                    "title" => $ins['name'],
                    "icon" => './img/db/instance.png',
                    "type" => 4
                );
                foreach ($ins['dbs'] as $db) {
                    //添加第五层  数据库层
                    $dbid = md5($d['hostip'] . $d['dbtype'] . $ins['name'] . $db['name']);
                    if (in_array($dbid, $dbArr)) {
                        continue;
                    }

                    $backupType = intval($db['bktype']);

                    //备份数据
                    $backupSize = $db['dbsize'] + $db['logsize'];
                    //历史数据
                    $historySize = $db['hissize'];
                    //接管数据
                    $roolbackSize = $db['dbsize'];

                    if ($backupType == Xphp::$_config['DB_CDP_BACKUP_TYPE']['REALTIME_BACKUP']) {
                        //如果是实时备份任务类型,接管数据为空
                        $roolbackSize = 0;
                    } else if ($backupType == Xphp::$_config['DB_CDP_BACKUP_TYPE']['TAKEOVER']) {
                        //如果是接管任务类型,备份数据为空,历史数据为空
                        $backupSize = 0;
                        $historySize = 0;
                    }
                    $dbTree[] = array(
                        'id' => $dbid,
                        'pid' => $instance,
                        'name' => $db['name'],
                        "open" => true,
                        "title" => $db['name'],
                        "icon" => './img/db/database.png',
                        "type" => 5,
                        'timeperiod' => $this->getScanDBSPeriods($ins['dbs'], $db['name']),
                        "productHostName" => $hostinfo['host_name'] . "(" . $d['hostip'] . ")",
                        "productHostIP" => $d['hostip'],
                        "standbyHostIP" => $hostip,
                        "standbyHostName" => $hostname,
                        "standbyHostUUID" => $hostuuid,
                        "dbtype" => $d['dbtype'],
                        "instance" => $ins['name'],

                        "backupsize" => $utils->calSize($backupSize),
                        "takeoversize" => $utils->calSize($roolbackSize),
                        "hissize" => $utils->calSize($historySize),
                    );

                    $dbArr[] = $dbid;
                }
            }
        }

        $info = array(
            "re" => true,
            "msg" => $dbTree
        );

        return json_encode($info, true);
    }

    /**
     * 测试恢复实例连接账号密码测试
     * @param unknown $params
     */
    public function recoveryInstanceTestCon($params)
    {
        $standbyhostuuid = $params['standbyhostuuid'];
        $hostuuid = $params['hostuuid'];
        $instance = $params['instance'][0];

        $hostInfo = $this->getHostInfoWithUUID($hostuuid);


        if (intval($hostInfo['host_type']) == Xphp::$_config['DB_CDP_HOST_TYPE']['product']) {
            $syscode = Xphp::$_config['DB_CDP_SYSCODE']['producthost'];
        }
        if (intval($hostInfo['host_type']) == Xphp::$_config['DB_CDP_HOST_TYPE']['standby']) {
            $syscode = Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'];
        }

        $rpc = Xphp::instance('DbRPCHandler');
        $msgData = array(
            'syscode' => $syscode,
            'dbtype' => $instance['dbtype'],
            'dbsrv' => $instance['instance'],
            'dbuser' => $instance['iusername'],
            'dbpass' => $instance['ipassword'],
            'dbname' => $instance['dbname']            //oracle需要这个
        );
        $operate = "测试数据库连接";

        //先测试账号密码是否正确
        $result = $rpc->loginDbTest($hostInfo['ip'], $msgData);
        $data = $this->checkRPCMsg($operate, $result);


        //获取配置是否存在,如果存在,删除配置,然后写入新的配置
        $result = $rpc->getDbLoginParam($hostInfo['ip'], $msgData);
        $data = $this->checkRPCMsg($operate, $result);


        foreach ($data as $d) {
            if (
                $d['dbtype'] == $instance['dbtype'] && $d['dbsrv'] == $instance['instance'] &&
                $d['dbuser'] == $instance['iusername']
            ) {
                //如果配置在,删除配置
                $result = $rpc->delDbLoginParam($hostInfo['ip'], $msgData);
            }
        }
        //写入新的配置
        $result = $rpc->addDbLoginParam($hostInfo['ip'], $msgData);

        if (!$result['result']) {
            return $this->muOpResult(false, $operate, $result['errorMsg'], 'warning', $result['errorCode']);
        }

        return $this->muOpResult(true, $operate);
    }

    /**
     * 获取主机备份系统启动状态
     * @param int $syscode
     * @param string $hostip
     * @return array['bakStatus'] 备份系统启动状态 
     *         array['resStatus'] 恢复系统启动状态
     */
    public function checkHostServiceStatus($syscode, $hostip)
    {
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => $syscode
        );
        $operate = "获取主机启动状态";
        $result = $rpc->getServiceStatus($hostip, $data);
        $data = $this->checkRPCMsg($operate, $result);
        $info = array(
            'bakStatus' => $data['runstatus'],  //备份系统启动状态
            'resStatus' => $data['rststatus'],  //恢复系统启动状态
        );
        return $info;
    }

    /**
     * 启动实时备份任务
     * @param unknown $params
     */
    public function startBackupJob($params)
    {
        $productHostIP = $params['dbcdp']['productip'];
        $standbyHostIP = $params['dbcdp']['standbyip'];
        $taskuuid = $params['uuid'];

        $rpc = Xphp::instance('DbRPCHandler');
        $operate = "启动数据库CDP实时备份任务";
        $opResult = true;
        $data = array();

        //任务检查,检查主站是否有恢复任务运行,有的话直接提示返回
        $checkStatus = $this->checkHostRecoveryJobInRunning($productHostIP);
        if ($checkStatus) {
            return $this->muOpResult(false, $operate, '生产主机有恢复任务正在运行,请先停止恢复任务!', 'warning');
        }
        //启用备份任务
        $this->ControlBackupJob($productHostIP, $standbyHostIP, true);

        //启动主站备份系统
        $this->startBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);
        sleep(2);

        $hostStatus = $this->checkHostServiceStatus(Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'], $standbyHostIP);
        if (Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] !== $hostStatus['bakStatus']) {
            //如果从站未启动,才启动,启动了就不用再启动了
            $opResult = $opResult && $this->startBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'], $standbyHostIP);
            $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STARTING']);
            //启动了从站,就不用再单独启动任务了.直接返回,因为从站启动的时候会自动把所有任务都启动
            return $this->muOpResult($opResult, $operate);
        }
        //启动任务
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'hipstr' => $productHostIP,
        );
        $result = $rpc->startRemoteHostIpTasks($standbyHostIP, $data);
        $data = $this->checkRPCMsg($operate, $result);

        $sql = "select task_name, task_type from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        if ($opResult) {
            //配置主站子系统启动模式为开机启动
            $syscode = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
                'startmode' => 1
            );
            $rpc->setServiceStartMode($productHostIP, $syscode);

            $this->settingTakeoverStartMode($taskuuid, $standbyHostIP);

            $this->updateBakDBConfig($taskuuid, $productHostIP);

            $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STARTING']);
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_START_TASK_SUCCESS');
        } else {
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_START_TASK_FAILURE');
        }

        return $this->muOpResult($opResult, $operate);
    }

    /**
     * 更新备份数据库的配置信息
     * @param unknown $taskuuid
     * @param unknown $productHostIP
     */
    private function updateBakDBConfig($taskuuid, $productHostIP)
    {
        $sql = "select config from  cdp_db_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $config = json_decode($data[0]['config'], true);

        $dbInfo = $config['producthostInfo']['dbInfo'];
        $rpc = Xphp::instance('DbRPCHandler');
        foreach ($dbInfo as $key => $db) {
            //sqlserver 不调用这个接口
            if (intval($db['dbtype']) != Xphp::$_config['DB_CDP_VENDOR']['ORACLE']) {
                return true;
            }

            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
                'dbtype' => $db['dbtype'],
                'dbsrv' => $db['dbsrv'],
                'dbname' => $db['dbname'],
            );
            $result = $rpc->getDbSysConfig($productHostIP, $msgData);
            $data = $this->checkRPCMsg("更新数据库配置信息", $result);
            $mergeArr = array_merge($data[0], $db);
            $config['producthostInfo']['dbInfo'][$key] = $mergeArr;
        }

        $sql = "update cdp_db_task set config = ? where task_uuid = ?";
        $this->dbExec($sql, array(json_encode($config, true), $taskuuid));
    }

    /**
     * 设置接管启动模式,
     * 接管不能在创建任务的时候启动,可能会导致接管比备份任务先启动
     * @param unknown $taskuuid
     * @param unknown $productIP
     */
    private function settingTakeoverStartMode($taskuuid, $productIP)
    {
        $sql = "select config from  cdp_db_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $config = json_decode($data[0]['config'], true);
        $takeoverType = intval($config['highInfo']['takeover']['type']);
        //如果是自动接管,启动自动接管
        if (1 == $takeoverType) {
            //启动接管系统
            $this->startTakeoveSys($productIP);
        }
    }

    /**
     * 停止实时备份任务
     * @param unknown $params
     */
    public function stopBackupJob($params)
    {
        $productHostIP = $params['dbcdp']['productip'];
        $standbyHostIP = $params['dbcdp']['standbyip'];
        $productHostUUID = $params['dbcdp']['productuuid'];
        $standbyHostUUID = $params['dbcdp']['standbyuuid'];
        $taskuuid = $params['uuid'];

        $productHostInfo = $this->getHostInfoWithUUID($productHostUUID);
        $productHostStatus = intval($productHostInfo['status']);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostUUID);
        $standbyHostStatus = intval($standbyHostInfo['status']);

        //禁用备份任务
        $this->ControlBackupJob($productHostIP, $standbyHostIP, false);

        $operate = "停止数据库CDP实时备份任务";
        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
            //如果备机离线,直接更新任务状态
            $result = $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPED']);
            return $this->muOpResult($result, $operate);
        }
        $opResult = true;
        $data = array();
        $rpc = Xphp::instance('DbRPCHandler');
        $hostStatus = $this->checkHostServiceStatus(Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'], $standbyHostIP);
        if (Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] === $hostStatus['bakStatus']) {
            //如果从站启动,才停止
            $data['syscode'] = Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'];
            $data['hipstr'] = $productHostIP;
            $result = $rpc->stopRemoteHostIpTasks($standbyHostIP, $data);
            if ($result['errorCode'] == -19015) {
                //如果没有找到任务,不管
                $result['result'] = true;
            }
            $opResult = $opResult && $result['result'];
        }

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE'] == $productHostStatus) {
            $hostStatus = $this->checkHostServiceStatus(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);
            if (Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] === $hostStatus['bakStatus']) {
                //如果主站启动,并且没有其他实时备份任务运行,才停止
                $sql = "select count(bt.task_uuid) as total from bd_task bt, cdp_db_task as cdt where
                bt.task_uuid = cdt.task_uuid and cdt.task_uuid != ? and bt.task_type = ? and
                cdt.product_host_uuid = ?";
                $sqlParams = array($taskuuid, Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'], $productHostUUID);
                $data = $this->dbSelect($sql, $sqlParams);
                if ($data[0]['total'] == 0) {
                    //                     $opResult = $opResult && $this->stopBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);
                }
            }

            //配置主站子系统启动模式为手动启动
            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
                'startmode' => 0
            );
            $rpc->setServiceStartMode($productHostIP, $data);
        }

        $sql = "select task_name, task_type from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        if ($opResult) {
            $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPING']);
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_STOP_TASK_SUCCESS');
        } else {
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_STOP_TASK_FAILURE');
        }

        return $this->muOpResult($opResult, $operate);
    }

    /**
     * 检查主机作为主/从站是否在正在运行的任务中(排除某个任务)
     * @param unknown $syscode
     * @param unknown $hostuuid
     * @param unknown $taskuuid
     */
    private function checkHostInBackupJob($syscode, $hostuuid, $taskuuid)
    {
        if ($syscode == Xphp::$_config['DB_CDP_SYSCODE']['producthost']) {
            $hostuuidName = "product_host_uuid";
        } elseif ($syscode == Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']) {
            $hostuuidName = "standby_host_uuid";
        }
        $sql = "select count(bt.task_uuid) as total from bd_task bt, cdp_db_task as cdt where 
                bt.task_uuid = cdt.task_uuid and cdt.task_uuid != ? and bt.task_status = ? and 
                $hostuuidName = ?";
        $sqlParams = array($taskuuid, Xphp::$_config['TASKSTATUS']['RUNNING'], $hostuuid);
        $data = $this->dbSelect($sql, $sqlParams);

        if (intval($data[0]['total']) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * 删除实时备份任务
     * @param unknown $params
     */
    public function deleteBackupJob($params)
    {
        $standbyHostIP = $params['dbcdp']['standbyip'];
        $productHostIP = $params['dbcdp']['productip'];
        $operate = "删除数据库CDP实时备份任务";
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);

        //检查任务状态,查看是否可以删除,然后删除(只是运行中无法删除)
        $sql = "select task_status, task_name, task_type from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['RUNNING']) {
            //如果任务正在运行,不能删除,直接提示.
            return $this->muOpResult(false, $operate, '删除备份任务失败,任务正在运行,请停止后再删除', 'warning');
        }

        $productHostInfo = $this->getHostInfoWithIP($productHostIP);
        $standbyHostInfo = $this->getHostInfoWithIP($standbyHostIP);
        $standbyHostStatus = intval($standbyHostInfo['status']);

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE'] == $standbyHostStatus) {
            //如果备机在线

            //先删除备份数据
            $delDataResult = $this->deleteBackupData($params);
            if (!$delDataResult) {
                return $this->muOpResult(false, $operate, '删除备份数据失败', 'warning');
            }

            //清理接管
            $this->clearnTakeoverEvn($standbyHostIP, $taskuuid);

            //删除释放停止备份的数据库状态队列,先检查从站是否在线,在线才发,不在线就不发
            $rpc = Xphp::instance('DbRPCHandler');
            $dataMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                'hostip' => $productHostIP,
                'remoteip' => $standbyHostIP,
            );
            $result = $rpc->freeBackupStopDbQuen($standbyHostIP, $dataMsg);

            $this->clearOldBackupInfo($productHostInfo['host_uuid'], $standbyHostInfo['host_uuid']);
            //             var_dump($standbyHostIP, $dataMsg, $result);

            //删除主机信息,防止之后残留多个主机信息,无法做接管.
            $dataMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                'hipstr' => $productHostIP,
                'port' => 0,
            );

            $result = $rpc->DelConnectHost($standbyHostIP, $dataMsg);

            if (!$result['result']) {
                return $this->muOpResult(false, $operate, $result['errorMsg'], 'warning', $result['errorCode']);
            }
        }

        $this->dbBeginTransaction();
        $sql = "delete from  bd_task where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $this->dbQuery($sql, $sqlParams);

        $sql = "delete from bd_running_info where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $result && $this->dbQuery($sql, $sqlParams);

        $sql = "delete from cdp_db_task where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $result && $this->dbQuery($sql, $sqlParams);


        if ($result) {
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_DELETE_TASK_SUCCESS');
            $this->dbCommit();
        } else {
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_DELETE_TASK_FAILURE');
            $this->dbRollBack();
        }

        return $this->muOpResult($result, $operate);
    }

    /**
     * 清理接管环境
     * 删除任务的时候调用
     * @param unknown $standbyHostIP
     * @param unknown $taskuuid
     */
    private function clearnTakeoverEvn($standbyHostIP, $taskuuid)
    {
        //先检查是否配置了接管
        $sql = "select config from cdp_db_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $config = json_decode($data[0]['config'], true);

        if (!$config['highInfo']['takeover']['check']) {
            return true;
        }

        $rpc = Xphp::instance('DbRPCHandler');
        $operate = "重置接管模式";
        //如果配置了接管,设置成手动接管
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
            'startmode' => 0
        );
        $result = $rpc->setServiceStartMode($standbyHostIP, $msgData);
        //         $this->checkRPCMsg($operate, $result);

        //停止接管系统
        $this->stopTakeoveSys($standbyHostIP);
    }

    /**
     * 删除备份数据
     * @param unknown $params
     */
    private function deleteBackupData($params)
    {
        $taskuuid = $params['uuid'];
        $standbyHostIP = $params['dbcdp']['standbyip'];
        $productHostIP = $params['dbcdp']['productip'];
        $config = $this->getTaskConfig($taskuuid);
        $rpc = Xphp::instance('DbRPCHandler');
        $dbInfo = $config['producthostInfo']['dbInfo'];
        $operate = "删除备份数据";
        $opResult = true;

        $hostInfo = $this->getHostInfoWithIP($standbyHostIP);
        if ($hostInfo['status'] == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']) {
            //如果主机离线,不删除
            return true;
        }
        foreach ($dbInfo as $db) {
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
                'hipstr' => $productHostIP,
                'dbtype' => $db['dbtype'],
                'dbsrv' => $db['dbsrv'],
                'dbname' => $db['dbname'],
                'purgebkbit' => 5
            );
            $result = $rpc->purgeDbBkData($standbyHostIP, $msgData);
            if ($result['errorCode'] == -19015) {
                //如果没有找到,继续下一个.
                continue;
            }
            $data = $this->checkRPCMsg($operate, $result);
            /**
             * $data
             * ["fokct"]=>文件成功数
             * ["foksize"]=>文件成功大小
             * ["ffailsct"]=>文件失败数
             * ["ffailssize"]=>文件失败大小
             * ["dokct"]=>目录成功数
             * ["dfailsct"]=>目录失败数
             */
            if ($data['ffailsct'] == 0 && $data['dfailsct'] == 0) {
                //没有失败
                $opResult = $opResult && TRUE;
            } else {
                $opResult = $opResult && FALSE;
            }
        }

        return $opResult;
    }

    /**
     * 启动恢复任务
     * @param unknown $params
     */
    public function startRecoveryJob($params)
    {
        $config = $this->getTaskConfig($params['uuid']);
        $taskuuid = $params['uuid'];
        $standbyHostIP = $params['dbcdp']['standbyip'];
        $productHostIP = $params['dbcdp']['productip'];
        $operate = "启动数据库恢复任务";

        //任务检查,检查目标主机是否有备份任务处于运行状态,如果有,提示用户先停止备份系统
        $checkStatus = $this->checkHostBackupJobInRunning($productHostIP);
        if ($checkStatus) {
            return $this->muOpResult(false, $operate, '恢复目标主机有备份任务正在运行中,请停止后再启动恢复任务!', 'warning');
        }

        if ($standbyHostIP == $productHostIP) {
            //启动目标主机恢复系统
            $this->startRecSystem(Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'], $productHostIP);

            //本机恢复
            return $this->standbyHostRecovery($taskuuid, $productHostIP, $standbyHostIP, $config);
        } else {
            //启动目标主机恢复系统
            $this->startRecSystem(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);

            //异机恢复
            return $this->productHostRecovery($taskuuid, $productHostIP, $standbyHostIP, $config);
        }
    }

    /**
     * 检查主机是否在正在运行的备份任务中,在的话返回true
     * @param unknown $hostIP
     */
    private function checkHostBackupJobInRunning($hostIP)
    {
        $hostInfo = $this->getHostInfoWithIP($hostIP);
        $sql = "select count(bt.task_uuid) as total from bd_task bt, cdp_db_task as cdt where
                bt.task_uuid = cdt.task_uuid and  bt.task_type = ? and bt.task_status = ? and 
                product_host_uuid = ?";
        $sqlParams = array(
            Xphp::$_config['TASKTYPE']['DB_CDP_BACKUP'],
            Xphp::$_config['TASKSTATUS']['RUNNING'],
            $hostInfo['host_uuid']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]['total'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * 检查主机是否在正在运行的恢复任务中,在的话返回true
     * @param unknown $hostIP
     */
    private function checkHostRecoveryJobInRunning($hostIP)
    {
        $hostInfo = $this->getHostInfoWithIP($hostIP);
        $sql = "select count(bt.task_uuid) as total from bd_task bt, cdp_db_task as cdt where
                bt.task_uuid = cdt.task_uuid and  bt.task_type = ? and bt.task_status = ? and 
                product_host_uuid = ?";
        $sqlParams = array(
            Xphp::$_config['TASKTYPE']['DB_CDP_RECOVERY'],
            Xphp::$_config['TASKSTATUS']['RUNNING'],
            $hostInfo['host_uuid']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]['total'] > 0) {
            return true;
        }
        return false;
    }



    /**
     * 启动从站本机恢复,恢复到自己
     * @param unknown $productHostIP
     * @param unknown $standbyHostIP
     * @param unknown $dbInfo
     */
    private function standbyHostRecovery($taskuuid, $productHostIP, $standbyHostIP, $config)
    {
        $operate = "启动数据库恢复任务";
        $opResult = true;
        $rpc = Xphp::instance('DbRPCHandler');
        $instance = $config['recoveryinstance'][0];
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'ipstr' => $config['dbinfo']['hostip'],
            'dbtype' => $config['dbinfo']['dbtype'],
            'dbsrv' => $config['dbinfo']['instance'],
            'dbname' => $config['dbinfo']['name'],
            'histime' => $config['timeperiod'],
            'step' => $config['step'],
            'unattach' => $config['attachdb'] ? 0 : 1,    //附加标志,只在主机自恢复使用,异机恢复是恢复完成后调用附加命令附加 1不附加  0按照默认的来(自动附加)
        );

        //先释放一下这个数据库
        $result = $rpc->freeRecoveryInfo($standbyHostIP, $data);
        $opResult = $opResult && $result['result'];
        $result = $rpc->startRecoveryJob($standbyHostIP, $data);
        $opResult = $opResult && $result['result'];
        if ($opResult) {
            $updateResult = $this->updateTaskStatus($taskuuid, Xphp::$_config['TASKSTATUS']['RUNNING']);
            $opResult = $opResult && $updateResult;
        } else {
            return $this->muOpResult($opResult, $operate, $result['errorMsg'], 'error', $result['errorCode']);
        }

        return $this->muOpResult($opResult, $operate);
    }

    /**
     * 启动从站恢复到其他机器,异机恢复
     * @param unknown $productHostIP
     * @param unknown $standbyHostIP
     * @param unknown $dbInfo
     */
    private function productHostRecovery($taskuuid, $productHostIP, $standbyHostIP, $config)
    {
        $operate = "启动数据库恢复任务";
        $opResult = true;
        $rpc = Xphp::instance('DbRPCHandler');

        //添加连接主站用户
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
            'username' => "admin",
            'usermm' => "admin"
        );
        $result = $rpc->addHostUser($productHostIP, $data);

        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['producthost'],
            'ipstr' => $config['dbinfo']['hostip'],     //备份源IP
            'dbtype' => $config['dbinfo']['dbtype'],
            'dbsrv' => $config['dbinfo']['instance'],
            'dbname' => $config['dbinfo']['name'],
            'histime' => $config['timeperiod'],
            'step' => $config['step'],
            'hipstr' => $productHostIP                //恢复目的机器IP
        );
        //主站启动恢复系统
        $result = $this->startRecSystem(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);
        //         $result = $rpc->startRecoverySrv($productHostIP, $data);
//         $opResult = $opResult && $result['result'];
//         if(!$result['result']){
//             return $this->muOpResult(false, $operate, $result['errorMsg'], 'warning',$result['errorCode']);
//         }

        //从站启动
        $data['syscode'] = Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'];
        $result = $rpc->freeRecoveryCliInfo($standbyHostIP, $data);

        //         $opResult = $opResult && $result['result'];
//         if(!$result['result']){
//             return $this->muOpResult(false, $operate, $result['errorMsg'], 'warning',$result['errorCode']);
//         }
        $result = $rpc->startRecoveryCli($standbyHostIP, $data);
        $opResult = $opResult && $result['result'];
        if (!$opResult) {
            return $this->muOpResult($opResult, $operate, $result['errorMsg'], 'error', $result['errorCode']);
        } else {
            $updateResult = $this->updateTaskStatus($taskuuid, Xphp::$_config['TASKSTATUS']['RUNNING']);
            $opResult = $opResult && $updateResult;
        }

        return $this->muOpResult($opResult, $operate);
    }

    /**
     * 停止恢复任务
     * @param unknown $params
     */
    public function stopRecoveryJob($params)
    {
        $config = $this->getTaskConfig($params['uuid']);
        $standbyhostuuid = $params['dbcdp']['standbyuuid'];
        $recoveryhostuuid = $params['dbcdp']['productuuid'];
        $standbyhostInfo = $this->getHostInfoWithUUID($standbyhostuuid);

        $operate = "停止数据库恢复任务";
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'ipstr' => $config['dbinfo']['hostip'],
            'dbtype' => $config['dbinfo']['dbtype'],
            'dbsrv' => $config['dbinfo']['instance'],
            'dbname' => $config['dbinfo']['name'],
        );
        if ($standbyhostuuid == $recoveryhostuuid) {
            //从站本机恢复
            $result = $rpc->stopRecoveryJob($standbyhostInfo['ip'], $data);
        } else {
            //从站异机恢复
            $result = $rpc->stopRecoveryCli($standbyhostInfo['ip'], $data);
        }
        if ($result['result']) {
            $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPED']);
        }

        if (-19015 == $result['errorCode']) {
            //任务已经删除特殊处理
            $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPED']);
        }

        return $this->muOpResult($result['result'], $operate);
    }


    /**
     * 删除恢复任务
     * @param unknown $params
     */
    public function deleteRecoveryJob($params)
    {
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $operate = '删除数据库恢复任务';

        //先检查任务是否可以删除,正在运行不能删除
        $sql = "select task_status from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['RUNNING']) {
            //如果任务正在运行,不能删除,直接提示.
            return $this->muOpResult(false, $operate, '删除任务失败,任务正在运行,请停止后再删除', 'warning');
        }

        $this->dbBeginTransaction();
        $sql = "delete from  bd_task where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $this->dbQuery($sql, $sqlParams);

        $sql = "delete from bd_running_info where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $result && $this->dbQuery($sql, $sqlParams);

        $sql = "delete from cdp_db_task where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $result && $this->dbQuery($sql, $sqlParams);


        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }

        return $this->muOpResult($result, $operate);
    }


    /**
     * 启动接管任务
     * @param unknown $params
     */
    public function takeoverJob($params)
    {
        $productHostIP = $params['dbcdp']['productip'];
        $standbyHostIP = $params['dbcdp']['standbyip'];
        $productHostUUID = $params['dbcdp']['productuuid'];
        $standbyHostUUID = $params['dbcdp']['standbyuuid'];

        $taskuuid = $params['uuid'];

        $productHostInfo = $this->getHostInfoWithUUID($productHostUUID);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostUUID);
        $operate = "启动业务接管";

        $rpc = Xphp::instance('DbRPCHandler');
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
        );

        //先看看是否已经启动，启动了就不用再启动了
        $result = $rpc->getServiceStatus($standbyHostIP, $msgData);
        $runStatus = $result['data']['data']['runstatus'];

        if (!empty($runStatus)) {
            if (intval($runStatus) == Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED']) {
                //如果正在运行
                return $this->muOpResult(true, $operate, "业务接管程序已经启动,已经进入自动接管检测状态!");
            }
        }


        //因为这里只启动,不停止,所以单独调用startService
        $result = $rpc->startService($standbyHostIP, $msgData);
        $data = $this->checkRPCMsg($operate, $result);

        return $this->muOpResult($result['result'], $operate);


        //先检查是否有自动接管在,在的话先停止接管,再启动接管,根据接管状态判断
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
        );
        $result = $rpc->checkIfTakeOverId($standbyHostIP, $msgData);
        $data = $this->checkRPCMsg($operate, $result);
        if (Xphp::$_config['DB_CDP_TAKEOVER_START_STATUS']['S_WAITCHKIF'] == $data[0]['takeroverid']) {
            //接管等待中,一般是自动接管运行在,直接提示用户接管程序正在自动运行,系统会在网络连接失败的情况下自动接管

            //             return $this->muOpResult($result, $operate);
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
            );
            $result = $rpc->manualStopTakeOver($standbyHostIP, $msgData);
            $this->checkRPCMsg($operate, $result);
        } elseif (in_array($data[0]['takeroverid'], Xphp::$_config['DB_CDP_TAKEOVER_START_STATUS'])) {
            //如果接管中
            return $this->muOpResult(true, $operate, '任务正在接管中', 'info');
        }

        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
        );
        $result = $rpc->manualStartTakeOver($standbyHostIP, $msgData);
        if (!$result['result']) {
            return $this->muOpResult(false, $operate, $result['errorMsg'], 'error', $result['errorCode']);
        }

        //更新任务状态为接管启动中
        $sql = "update bd_task set task_status = ? where task_uuid = ?";
        $result = $this->dbExec($sql, array(Xphp::$_config['TASKSTATUS']['TAKEOVER_STARTING'], $taskuuid));


        return $this->muOpResult(true, $operate);
    }

    /**
     * 停止接管任务
     * @param unknown $params
     */
    public function stopTakeoverJob($params)
    {
        $productHostIP = $params['dbcdp']['productip'];
        $standbyHostIP = $params['dbcdp']['standbyip'];
        $productHostUUID = $params['dbcdp']['productuuid'];
        $standbyHostUUID = $params['dbcdp']['standbyuuid'];

        $taskuuid = $params['uuid'];

        $productHostInfo = $this->getHostInfoWithUUID($productHostUUID);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostUUID);
        $standbyHostStatus = intval($standbyHostInfo['status']);

        $operate = "停止业务接管";

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
            //如果备机离线,直接更新任务状态
            $result = $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPED']);
            return $this->muOpResult($result, $operate);
        }


        $rpc = Xphp::instance('DbRPCHandler');
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
        );

        //先看看是否已经停止了，停止了就不用再停止了
        $result = $rpc->getServiceStatus($standbyHostIP, $msgData);
        $runStatus = $result['data']['data']['runstatus'];

        if (intval($runStatus) == Xphp::$_config['DB_CDP_HOST_START_STATUS']['PREPARE']) {
            //如果已经停止
            return $this->muOpResult(true, $operate, "业务接管程序已经停止,已经进入手动接管状态,系统不会自动接管!");
        }


        //因为这里只停止
        $result = $rpc->stopService($standbyHostIP, $msgData);
        $data = $this->checkRPCMsg($operate, $result);

        return $this->muOpResult($result['result'], $operate);




        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover'],
        );
        $result = $rpc->manualStopTakeOver($standbyHostIP, $data);
        if (!$result['result']) {
            return $this->muOpResult(false, $operate, $result['errorMsg'], 'error', $result['errorCode']);
        }

        //更新任务状态为接管停止中
        $sql = "update bd_task set task_status = ? where task_uuid = ?";
        $result = $this->dbExec($sql, array(Xphp::$_config['TASKSTATUS']['TAKEOVER_STOPPING'], $taskuuid));

        return $this->muOpResult(true, $operate);
    }

    /**
     * 更新任务状态
     * @param unknown $taskuuid
     * @param unknown $satus
     */
    private function updateTaskStatus($taskuuid, $satus)
    {
        $sql = "update bd_task set task_status = ? where task_uuid = ?";
        $result = $this->dbExec($sql, array($satus, $taskuuid));
        return $result;
    }

    /**
     * 根据任务uuid获取任务配置详情
     * @param unknown $uuid
     */
    private function getTaskConfig($uuid)
    {
        $sql = "select config from cdp_db_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $config = json_decode($data[0]['config'], true);
        return $config;
    }

    /**
     * 扫描主机已备份的数据库
     * @param unknown $params
     */
    public function scanBackupDatabase($params)
    {
        $hostuuid = $params['hostuuid'];
        $hostInfo = $this->getHostInfoWithUUID($hostuuid);

        $standbyHostIP = $hostInfo['ip'];
        $operate = "获取备份数据库信息";
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
        );
        $result = $rpc->scanBackupDatabase($standbyHostIP, $data);
        $data = $this->checkRPCMsg($operate, $result);


        $databaseInfo = array();
        $dbArr = array();   //用作判断
        foreach ($data as $d) {
            foreach ($d['instance'] as $instance) {
                //获取数据库
                $db = array();
                foreach ($instance['dbs'] as $dbs) {
                    if (in_array($d['hostip'] . $instance['name'] . $dbs['name'], $dbArr)) {
                        continue;
                    }
                    $thisdb = array(
                        'name' => $dbs['name'],
                        //                         'startTime' => $dbs['starttime'],
//                         'endTime' => $dbs['endtime'],
                        'dbtype' => $d['dbtype'],
                        'hostip' => $d['hostip'],
                        'instance' => $instance['name'],
                        'id' => md5($d['hostip'] . $d['dbtype'] . $instance['name'] . $dbs['name']),
                        'timeperiod' => $this->getScanDBSPeriods($instance['dbs'], $dbs['name'])
                    );
                    $db[] = $thisdb;
                    $dbArr[] = $d['hostip'] . $instance['name'] . $dbs['name'];  //防止一个数据库出现多次,从几个维度判断,不能只判断数据库名称
                }
                //获取实例
                $ins = array(
                    'id' => md5($d['hostip'] . $d['dbtype'] . $instance['name']),
                    'name' => $instance['name'],
                    'database' => $db,
                    'dbtype' => $d['dbtype'],
                    'hostip' => $d['hostip']
                );
                $databaseInfo[] = $ins;
            }
        }

        return json_encode($databaseInfo, true);
    }

    /**
     * 得到扫描数据库的恢复时间段
     * @param unknown $dbList
     * @param unknown $dbname
     */
    private function getScanDBSPeriods($dbList, $dbname)
    {
        $timeperiods = array();
        $dbArr = array();
        foreach ($dbList as $db) {
            if ($db['name'] == $dbname) {
                $timeperiods[] = array(
                    'name' => $db['name'],
                    "histime" => $db['histime'],
                    "starttime" => $db['starttime'],
                    "endtime" => $db['endtime'],
                    "backpath" => $db['backpath'],
                    "des" => $db['starttime'] . " > " . $db['endtime']
                );
            }
        }

        $utils = Xphp::instance('Utils');
        $timeperiods = $utils->arraySort($timeperiods, 'starttime', 'desc', 0, -1);

        return $timeperiods;
    }

    /**
     * 扫描数据库的某个时间点周围的可恢复点
     * 创建恢复和备份数据公用!!!!
     * @param unknown $params
     */
    public function scanBackupTimepoint($params)
    {
        $hostuuid = $params['hostuuid'];
        $hostInfo = $this->getHostInfoWithUUID($hostuuid);
        $checkbox = $params['checkbox'];

        $standbyHostIP = $hostInfo['ip'];
        $operate = "获取备份数据库时间点信息";
        $rpc = Xphp::instance('DbRPCHandler');
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'],
            'ipstr' => $params['hostip'],
            'dbtype' => $params['dbtype'],
            'dbsrv' => $params['instance'],
            'dbname' => $params['db'],
            'timepoint' => $params['timepoint'],
            'histime' => $params['timeperiod']
        );

        $result = $rpc->scanBackupTimepoint($standbyHostIP, $msgData);
        $data = $this->checkRPCMsg($operate, $result);

        $records = array();
        $records["data"] = array();
        foreach ($data as $d) {
            if ($checkbox) {
                //为了适配恢复和备份点管理,可以通过参数看是否要CheckBox
                $data = array(
                    '<input type="checkbox" name="id[]" value="' . $d['step'] . '">',
                    $d['step'],
                    $d['timepoint'],
                );
            } else {
                $data = array(
                    $d['step'],
                    $d['timepoint'],
                );
            }
            $records["data"][] = $data;
        }
        $records["draw"] = $params['draw'];
        return json_encode($records);
    }

    /**
     * 启动备份系统进入备份状态/
     * 主站备份和恢复状态互斥,只能在其中一个状态.
     * 从站备份和恢复状态可以同时存在
     * @param int $syscode
     * @param string $hostip
     */
    public function startBakSystem($syscode, $hostip)
    {
        //先获取系统状态
        $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
        $bakStatus = $serviceStatus['bakStatus'];
        $resStatus = $serviceStatus['resStatus'];
        $rpc = Xphp::instance('DbRPCHandler');
        //处理主站
        if (
            $syscode == Xphp::$_config['DB_CDP_SYSCODE']['producthost'] ||
            $syscode == Xphp::$_config['DB_CDP_SYSCODE']['fileproduct']
        ) {
            $i = 0;
            //检查并停止恢复系统
            while ($resStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['PREPARE'] && $i < 30) {
                $data = array('syscode' => $syscode);
                if ($i == 0) {
                    $result = $rpc->stopService($hostip, $data);
                }
                sleep(1);
                $i++;
                $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
                $bakStatus = $serviceStatus['bakStatus'];
                $resStatus = $serviceStatus['resStatus'];
            }
            $i = 0;
            //检查并启动备份系统
            while ($bakStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] && $i < 30) {
                $data = array('syscode' => $syscode);
                //在错误(3)的情况下启动没得用,只能重启
                if ($i == 0) {
                    $result = $rpc->reStartService($hostip, $data);
                }
                sleep(1);
                $i++;
                $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
                $bakStatus = $serviceStatus['bakStatus'];
                $resStatus = $serviceStatus['resStatus'];
            }
        }
        //处理从站
        if (
            $syscode == Xphp::$_config['DB_CDP_SYSCODE']['standbyhost'] ||
            $syscode == Xphp::$_config['DB_CDP_SYSCODE']['filestandby']
        ) {
            $i = 0;
            //检查并启动备份系统
            while ($bakStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] && $i < 30) {
                $data = array('syscode' => $syscode);
                //在错误(3)的情况下启动没得用,只能重启
                if ($i == 0) {
                    $result = $rpc->reStartService($hostip, $data);
                }
                sleep(1);
                $i++;
                $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
                $bakStatus = $serviceStatus['bakStatus'];
                $resStatus = $serviceStatus['resStatus'];
            }
        }

        return true;
    }

    /**
     * 停止备份系统
     * @param unknown $syscode
     * @param unknown $hostip
     */
    public function stopBakSystem($syscode, $hostip)
    {
        //先获取系统状态
        $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
        $bakStatus = $serviceStatus['bakStatus'];
        $resStatus = $serviceStatus['resStatus'];
        $rpc = Xphp::instance('DbRPCHandler');

        $i = 0;
        //检查并停止备份系统
        while ($bakStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['PREPARE'] && $i < 30) {
            $data = array('syscode' => $syscode);
            if ($i == 0) {
                $result = $rpc->stopService($hostip, $data);
            }
            sleep(1);
            $i++;
            $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
            $bakStatus = $serviceStatus['bakStatus'];
            $resStatus = $serviceStatus['resStatus'];
        }

        return true;
    }

    /**
     * 启动恢复系统进入恢复状态
     * @param unknown $syscode
     * @param unknown $hostIP
     */
    private function startRecSystem($syscode, $hostip)
    {
        //先获取系统状态
        $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
        $bakStatus = $serviceStatus['bakStatus'];
        $resStatus = $serviceStatus['resStatus'];
        $rpc = Xphp::instance('DbRPCHandler');
        //处理主站
        if ($syscode == Xphp::$_config['DB_CDP_SYSCODE']['producthost']) {
            $i = 0;
            //检查并停止备份系统
            while ($bakStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['PREPARE'] && $i < 30) {
                $data = array('syscode' => $syscode);
                if ($i == 0) {
                    $result = $rpc->stopService($hostip, $data);
                }
                sleep(1);
                $i++;
                $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
                $bakStatus = $serviceStatus['bakStatus'];
                $resStatus = $serviceStatus['resStatus'];
            }
            $i = 0;
            //检查并启动恢复系统
            while ($resStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] && $i < 30) {
                $data = array('syscode' => $syscode);
                if ($i == 0) {
                    $result = $rpc->startRecoverySrv($hostip, $data);
                }
                sleep(1);
                $i++;
                $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
                $bakStatus = $serviceStatus['bakStatus'];
                $resStatus = $serviceStatus['resStatus'];
            }
        }
        //处理从站
        $i = 0;
        if ($syscode == Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']) {
            //检查并启动恢复系统
            while ($resStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] && $i < 30) {
                $data = array('syscode' => $syscode);
                if ($i == 0) {
                    $result = $rpc->setRecoveryStatus($hostip, $data);
                }
                sleep(1);
                $i++;
                $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
                $bakStatus = $serviceStatus['bakStatus'];
                $resStatus = $serviceStatus['resStatus'];
            }
        }

        return true;
    }

    /**
     * 停止恢复系统
     * @param unknown $syscode
     * @param unknown $hostip
     */
    private function stopRecSystem($syscode, $hostip)
    {
        //先获取系统状态
        $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
        $bakStatus = $serviceStatus['bakStatus'];
        $resStatus = $serviceStatus['resStatus'];
        $rpc = Xphp::instance('DbRPCHandler');
        $i = 0;
        //检查并停止恢复系统
        while ($resStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['PREPARE'] && $i < 30) {
            $data = array('syscode' => $syscode);
            if ($i == 0) {
                $result = $rpc->stopRecoverySrv($hostip, $data);
            }
            sleep(1);
            $i++;
            $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
            $bakStatus = $serviceStatus['bakStatus'];
            $resStatus = $serviceStatus['resStatus'];
        }

        return true;
    }

    /**
     * 启动接管系统
     * @param unknown $hostip
     */
    private function startTakeoveSys($hostip)
    {
        $syscode = Xphp::$_config['DB_CDP_SYSCODE']['takeover'];
        $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
        $bakStatus = $serviceStatus['bakStatus'];
        //先检查接管系统运行状态,如果不在启动状态,才可以启动
        if ($bakStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED']) {
            $rpc = Xphp::instance('DbRPCHandler');
            $data = array('syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']);
            $result = $rpc->reStartService($hostip, $data);
        }
        return $result['result'];
    }

    /**
     * 停止接管系统
     * @param unknown $hostip
     */
    private function stopTakeoveSys($hostip)
    {
        $syscode = Xphp::$_config['DB_CDP_SYSCODE']['takeover'];
        $serviceStatus = $this->checkHostServiceStatus($syscode, $hostip);
        $bakStatus = $serviceStatus['bakStatus'];
        //先检查接管系统运行状态,如果在运行,才可以停止
        if ($bakStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['PREPARE']) {
            $rpc = Xphp::instance('DbRPCHandler');
            $data = array('syscode' => Xphp::$_config['DB_CDP_SYSCODE']['takeover']);
            $result = $rpc->stopService($hostip, $data);
        }
        return true;
    }

    /**
     * 检查主机日志是否可以下载,可以下载才到下载
     * @param unknown $params
     */
    public function checkHostLog($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_dbhost_download");
        $hostuuid = $params['hostuuid'];
        $this->paramsCheck($hostuuid);

        //先检查主机是否在线,不在线直接返回
        $hostInfo = $this->getHostInfoWithUUID($hostuuid);

        $operate = "下载主机日志";

        if (intval($hostInfo['status']) == Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE']) {
            return $this->muOpResult(false, $operate, "主机不在线,请稍后再试!", "warning");
        }
        //发送消息获取日志
        $rpc = Xphp::instance('DbRPCHandler');
        $msgData = array(
            'fno' => 0,
            'bfoff' => -1,
            'direct' => 1,
            'maxlines' => 1,    //读取一条记录
            'maxdlen' => 1
        );
        $result = $rpc->getRunLogs($hostInfo['ip'], $msgData);
        $data = $this->checkRPCMsg($operate, $result);
        //如果能够读取到记录,返回成功
        if (count($data) > 0) {
            return $this->muOpResult(true, $operate);
        } else {
            return $this->muOpResult(false, $operate, "读取日志文件失败,请重试", "warning");
        }

    }

    /**
     * 下载主机日志
     * @param unknown $params
     */
    public function getHostLogFile($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_dbhost_download");
        $hostuuid = $params['hostuuid'];
        $this->paramsCheck($hostuuid);

        $hostInfo = $this->getHostInfoWithUUID($hostuuid);
        $operate = "下载主机日志";

        $maxLines = 1000;
        $bfoff = -1;
        //发送消息获取日志
        $rpc = Xphp::instance('DbRPCHandler');

        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: ");
        Header("Content-Disposition: attachment; filename=" . $hostInfo['ip'] . ".txt");
        //获取日志
        do {
            $msgData = array(
                'fno' => 0,
                'bfoff' => $bfoff,
                'direct' => 1,
                'maxlines' => $maxLines,
                'maxdlen' => 1
            );
            $result = $rpc->getRunLogs($hostInfo['ip'], $msgData);


            $data = $this->checkRPCMsg($operate, $result);
            $count = count($data);
            $bfoff = $data[$count - 1]['foff'];

            foreach ($data as $d) {
                echo $d['txt'] . PHP_EOL . "\r\n";
            }
        } while ($count == $maxLines);

        //获取授权信息
        $result = $rpc->getLocalMachineAuthorInfo($hostInfo['ip'], array());
        $data = $this->checkRPCMsg($operate, $result);

        echo date("Y-m-d H:i:s") . " [9999,0] getLocalMachineAuthorInfo:" . json_encode($data);

        return;
    }


    /***********************************license start***********************************************/

    /**
     * 获取环境码
     * @param unknown $params
     */
    public function getEnvStr()
    {
        //        $hostip = Xphp::$_config['DB_CDP_LICENSE_IP'];
        $hostip = $_SERVER['SERVER_ADDR'];
        $operate = "获取备份系统环境码";
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array();
        $result = $rpc->getEnvStr($hostip, $data);
        $data = $this->checkRPCMsg($operate, $result);
        //适配返回空字符串的情况
        if (empty($data)) {
            return "";
        }
        return $data['envstr'];
    }

    /**
     * 上传license文件
     */
    public function upLicenseFile($licenseStr)
    {
        //        $hostip = Xphp::$_config['DB_CDP_LICENSE_IP'];
        $hostip = $_SERVER['SERVER_ADDR'];
        $operate = "上传授权文件";
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array(
            'licenseStr' => $licenseStr
        );
        $result = $rpc->upLicenseFile($hostip, $data);
        if (!$result['result']) {
            //授权失败,直接失败结果
            return $result;
        }
        //授权成功,立即设置成授权中心

        //配置本地授权许可列表性质
        $data = array(
            'tablemode' => 1
        );
        $result = $rpc->setLocAuthorTableMode($hostip, $data);

        //激活当前授权配置立即生效
        $data = array(
            'syscode' => $syscode,
        );
        $result = $rpc->actLicenseCfgValid($hostip, $data);

        $operate = "设置授权中心";
        $data = array(
            'licport' => '7999'
        );
        $result = $rpc->setLicenseCenter($hostip, $data);
        return $result;
    }

    /**
     * 获取授权信息
     * @param unknown $params
     */
    public function getLicenseCenterAuthorInfo()
    {
        //        $hostip = Xphp::$_config['DB_CDP_LICENSE_IP'];
        $hostip = $_SERVER['SERVER_ADDR'];
        $operate = "获取网络获取授权配置";
        $rpc = Xphp::instance('DbRPCHandler');
        $data = array();
        $result = $rpc->getLicenseCenterAuthorInfo($hostip, $data);
        $data = $this->checkRPCMsg($operate, $result);
        return $data;
    }

    /**
     * 停止lzbackup 看门狗会启动他
     */
    private function stopLzbackup()
    {

        //         check=`ps -e |grep lzback*  |grep -v grep |awk  '{printf $1}'`
//         echo $check

        //         kill $check
    }


    /***********************************license end***********************************************/

    /**
     * 写任务操作日志
     * @param unknown $taskuuid
     * @param unknown $taskName
     * @param unknown $task_type
     * @param unknown $desKey
     */
    private function writeTaskLog($taskuuid, $taskName, $task_type, $desKey)
    {
        $desParam = '["S:' . $taskName . '"]';
        $sql = "insert into bd_task_log(task_uuid, task_name, task_type, user_uuid, user_name, 
                        module_type, error_code, op_time, description_key, description_param, 
                        log_level, running_flag) values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array(
            $taskuuid,
            $taskName,
            $task_type,
            Xphp::$_user['useruuid'],
            Xphp::$_user['username'],
            Xphp::$_config['MODULE_TYPE']['OEM_DBCDP'],
            0,
            date("Y-m-d H:i:s"),
            $desKey,
            $desParam,
            1,
            Xphp::$_config['FLAG']['UNSET']
        );
        $result = $this->dbQuery($sql, $sqlParams);

        return $result;
    }
}

?>