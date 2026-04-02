<?php
/*******************************************
 ** 文件CDP备份处理类
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2020-09-23 上午10:22:13
 ** @version      1.0.0
 ** @copyright    Copyright 2020 vinchin.com
 ********************************************/
class FileCDPHandler extends OPHandler
{
    private $FileRPCHandler;

    /**
     * 创建文件实时备份任务
     * @param unknown $params
     */
    public function createBackupJob($params)
    {
        //         var_dump($params);
        //         return;
        $productHostuuid = $params['producthostInfo']['hostuuid'];
        $standbyHostuuid = $params['standbyhostInfo']['standbyhost'];
        $productHostInfo = $this->getHostInfoWithUUID($productHostuuid);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostuuid);

        $operate = Xphp::$_lang['WEB_FILE_CDP_CREATE_BACKUP_TASK'];
        $taskuuid = Xphp::instance('Utils', 'uuid');

        //检查生产主机是否授权
        $checkResult = $this->checkHostIsLicensed($productHostuuid);
        if (!$checkResult) {
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_PRODUCT_HOST_NOT_AUTH_TIPS'], "warning");
        }
        //检查备份主机是否授权
        $checkResult = $this->checkHostIsLicensed($standbyHostuuid);
        if (!$checkResult) {
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_BACKUP_HOST_NOT_AUTH_TIPS'], "warning");
        }

        //一个主机对应一个备机只能创建一个备份任务,检查这里
        $sql = "select count(cft.id) as total from cdp_fs_task cft,  bd_task bt where
                cft.task_uuid = bt.task_uuid and cft.product_host_uuid = ? and
                cft.standby_host_uuid = ? and bt.task_type = ?";
        $sqlParams = array($productHostuuid, $standbyHostuuid, Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP']);
        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]['total'] > 0) {
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_BACKUP_HOST_EXIST_TASK_TIPS'], "warning");
        }

        //先处理一下备份文件列表,加上一些特殊的配置,如16位MD5,存储路径等
        $params = $this->processBackupParams($taskuuid, $params);

        //配置主站信息
        $this->SettingBackupJobProductHost($productHostInfo, $params);
        //配置从站信息
        $this->SettingBackupJobStandbyHost($productHostInfo, $standbyHostInfo, $params, $taskuuid);
        //写入数据库信息


        //如果都配置成功,插入信息到数据库
        $taskName = htmlspecialchars_decode($params['jobname']);
        $moduleType = Xphp::$_config['MODULE_TYPE']['OEM_FSCDP'];
        $taskType = Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP'];
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

        $sql = "insert into cdp_fs_task(task_uuid, product_host_uuid, standby_host_uuid, config) values(?, ?, ?, ?)";
        $sqlParams = array($taskuuid, $productHostuuid, $standbyHostuuid, json_encode($params, true));
        $result = $result && $this->dbQuery($sql, $sqlParams);

        if ($result) {
            $this->dbCommit();
            $this->writeTaskLog($taskuuid, $taskName, $taskType, 'BD_TASKLOG_DESC_KEY_CREATE_TASK_SUCCESS');
        } else {
            $this->dbRollBack();
            $this->writeTaskLog($taskuuid, $taskName, $taskType, 'BD_TASKLOG_DESC_KEY_CREATE_TASK_FAILURE');
        }

        return $this->muOpResult($result, $operate);
    }

    /**
     * 处理备份任务参数
     * @param unknown $params
     */
    private function processBackupParams($taskuuid, $params)
    {
        $standbyhostInfo = $params['standbyhostInfo'];
        $producthostUUID = $params['producthostInfo']['hostuuid'];
        $storageuuid = $standbyhostInfo['storageuuid'];
        $dir = "";
        if (empty($storageuuid)) {
            //存储uuid为空,表示不是备份服务器
            $dir = "";
            //处理一下任务uuid,任务uuid太长了,导致部分Windows无法创建目录,这里取任务uuid的前两位和后两位,从32位变成4位
            $subTaskuuid = substr($taskuuid, 0, 2) . substr($taskuuid, -2);
            $backupdirparent = $dir . $standbyhostInfo['backupdir'];
            $historydirparent = $dir . $standbyhostInfo['historydir'];
        } else {
            //备份服务器
            $dir = $this->getStorageMountPoint($storageuuid, $taskuuid);
            $backupdirparent = $dir . $standbyhostInfo['backupdir'];
            $historydirparent = $dir . $standbyhostInfo['historydir'];
        }

        $fileInfo = $params['producthostInfo']['fileInfo'];
        $pathList = $params['producthostInfo']['pathList'];
        $newFileInfo = array();

        foreach ($fileInfo as $key => $value) {
            //过滤掉有父目录的子目录,选择了父目录后,子目录就不再需要了
            if (in_array(dirname($value['path']), $pathList)) {
                continue;
            }
            $taskname = substr(md5($producthostUUID . $standbyhostInfo['uuid'] . $value['path']), 8, 16);
            //将任务名保存起来,算16位MD5,因为可能不同主站备份到备份主机，需要加上主机uuid算，不然目录一样的话，会有问题,
            //不仅要加生产主机的uuid,还要加备份主机的uuid,因为在排除文件的时候是如果一个生产主机对应多个备份主机会有问题
            $value['taskname'] = $taskname;
            //备份/历史目录=用户填写的备份目录/备份系统默认的目录 + 任务名
            $value['backupdir'] = $backupdirparent;
            $value['historydir'] = $historydirparent;

            $newFileInfo[] = $value;
        }
        $params['producthostInfo']['fileInfo'] = $newFileInfo;
        $params['producthostInfo']['pathList'] = array();   //这个参数不用了,主要是为了判断用的
        $params['standbyhostInfo']['backupdirparent'] = $backupdirparent;
        $params['standbyhostInfo']['historydirparent'] = $historydirparent;
        return $params;
    }

    /**
     * 转化路径为一个文件夹,用于在备份数据中记录这个文件是从哪儿来的
     * @param unknown $file
     */
    private function getFilePathFullName($file)
    {
        //不管是目录还是文件,都是转化成一样的,取他们的路径为名称,然后加上文件名/目录名
        if ("." == dirname($file['path'])) {
            //如果上一级是根
            $fullName = $file['name'];
        } else {
            $fullName = strtr(dirname($file['path']), array("/" => '_', ":" => '')) . "/" . $file['name'];
        }
        return $fullName;
    }

    /**
     * 检查主机文件cdp是否授权
     * @param unknown $hostuuid
     */
    private function checkHostIsLicensed($hostuuid)
    {
        $this->paramsCheck($hostuuid);
        $sql = "select detail from cdp_db_host where host_uuid = ?";
        $data = $this->dbSelect($sql, array($hostuuid));
        $detail = json_decode($data[0]['detail'], true);
        return $detail['license']['file'];
    }

    /**
     * 配置备份任务主站信息
     * @param array $hostinfo 主机信息
     * @param array $params
     * @return boolean
     */
    private function SettingBackupJobProductHost($hostinfo, $params)
    {
        $rpc = Xphp::instance('FileRPCHandler');
        //*新加中心TCP/IP连接用户
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
            'username' => "admin",
            'usermm' => "admin",
        );
        $result = $rpc->addHostUser($hostinfo['ip'], $rpcMsg);
        //         var_dump($hostinfo['ip'], $rpcMsg, $result);
        //         exit();

        $i = 0;

        $fileList = $params['producthostInfo']['fileInfo'];
        $ransomwaretype = intval($params['highInfo']['ransomware']['ransomwaretype']);
        if (0 == $ransomwaretype) {
            $chkfmode = 0;
        } else {
            $chkfmode = 2;
        }
        foreach ($fileList as $file) {
            //一个文件/文件夹一个任务

            $taskname = $file['taskname'];
            //*新加中心允许备份的任务
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                'taskname' => $taskname,
                'status' => 0,                          //启用
                'mode' => $file['isParent'] ? 0 : 1,    //0目录  1文件
                'hostdir' => $file['path'],
                'maskfile' => "*.*",                      //匹配所有文件
                'subid' => 1,                           //1包含子目录,0不包含子目录
                'dateid' => $this->getFileTimeFilterID($params),    //文件过滤[时间过滤]
                'chkfmode' => $chkfmode,                //防勒索
            );
            $result = $rpc->addHostAllowBkTask($hostinfo['ip'], $rpcMsg);
            //             var_dump($rpcMsg, $result);

            //*修改默认搜索时间段[时间策略]
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                'taskname' => $file['taskname'],
                'b1time' => $this->getSearchTimeSeg($params),
            );
            $result = $rpc->chgSearchTimeSeg($hostinfo['ip'], $rpcMsg);
            //             var_dump($i++, $rpcMsg, $result);

            //*新加任务排除目录或文件
            $result = $this->addFileTickout($hostinfo, $params, $taskname, $file);

            //新加任务扩展名策略(防勒索)
            $result = $this->addFileRansomware($hostinfo, $params, $taskname);
        }


        return true;
    }

    /**
     * 新加防勒索配置
     * @param unknown $hostinfo
     * @param unknown $params
     * @param unknown $taskname
     */
    private function addFileRansomware($hostinfo, $params, $taskname)
    {
        $ransomware = $params['highInfo']['ransomware'];

        if (0 == intval($ransomware['ransomwaretype'])) {
            //如果没有开启防勒索,直接返回
            return true;
        }

        $rpc = Xphp::instance('FileRPCHandler');

        $selectTypes = $ransomware['selectTypes'];
        //一次设置每一个扩展名对应的文件类型
        foreach ($selectTypes as $type) {
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                'taskname' => $taskname,
                'extname' => $type['extname'],
                'chktype' => 1,
                'fsize0id' => 1,
                'matchid' => 1,
                'unmatch' => 0,
                'exttypeset' => implode(",", $type['data'])
            );
            $result = $rpc->addTaskExtPolicy($hostinfo['ip'], $rpcMsg);
            //             var_dump($hostinfo['ip'], $rpcMsg, $result);
        }
    }

    /**
     * 添加文件过滤
     * @param unknown $hostinfo
     * @param unknown $params
     * @param unknown $taskname
     */
    private function addFileTickout($hostinfo, $params, $taskname, $file)
    {
        $filter = $params['highInfo']['filter'];

        if (!$filter['namefilter']) {
            //如果没有开启文件名称过滤,直接返回
            return true;
        }

        if (!$file['isParent']) {
            //如果选择的是文件,不做排除,只对目录排除
            return true;
        }

        $rpc = Xphp::instance('FileRPCHandler');
        $nameFilterList = str_replace(",", "\\", $filter['namefilterlist']);

        //排除指定文件
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
            'taskname' => $taskname,
            'ticktype' => 0,
            'ticksubid' => 0,
            'tickpath' => $file['path'] . "/",
            'tickmask' => $nameFilterList,
            'tickoutop' => 1,

        );
        $result = $rpc->addTaskTickout($hostinfo['ip'], $rpcMsg);

        //                 var_dump($rpcMsg, $result);

        //排除指定目录
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
            'taskname' => $taskname,
            'ticktype' => 2,
            'ticksubid' => 0,
            'tickpath' => $file['path'] . "/",
            'tickmask' => $nameFilterList,
            'tickoutop' => 1,

        );
        $result = $rpc->addTaskTickout($hostinfo['ip'], $rpcMsg);

        //                 var_dump($rpcMsg, $result);
//                 exit;

        return true;
    }


    /**
     * 配置备份任务从站信息
     * @param array $hostinfo 主机信息
     * @param array $params
     * @return boolean
     */
    private function SettingBackupJobStandbyHost($productInfo, $standbyInfo, $params, $TASKUUID)
    {
        $rpc = Xphp::instance('FileRPCHandler');
        //*新加连接主站的信息
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productInfo['ip'],
            'port' => 0,
            'username' => "admin",
            'password' => "admin",
            'disable' => 1,         //设置好先禁用,启动任务的时候再启用,防止服务启动后任务自动启动
        );
        $result = $rpc->AddConnectHost($standbyInfo['ip'], $rpcMsg);
        //         var_dump($rpcMsg, $result);

        $fileList = $params['producthostInfo']['fileInfo'];
        foreach ($fileList as $file) {
            //一个文件/文件夹一个任务

            $taskname = $file['taskname'];
            //*新加远程备份任务
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productInfo['ip'],
                'port' => 0,
                'taskname' => $taskname,
                'mode' => $file['isParent'] ? 0 : 1,    //0目录  1文件
                'rmode' => $params['standbyhostInfo']['backuptype'],
                'rcfgdir' => $file['backupdir'],        //备份目录
                'hisdir' => $file['historydir'],        //历史目录
                'mirrid' => $params['highInfo']['general']['mirrorimage'] ? 1 : 0, //0不镜像 1镜像
                'threadid' => $params['highInfo']['general']['multithreading'] ? 1 : 0, //	0:使用共用线程处理 1：使用独立线程处理

                'realid' => $this->getRealidValue($params['highInfo']['strategy']['strategytype']),      //目录文件实时跟踪
                'empdirid' => $params['highInfo']['general']['emptydir'] ? 1 : 0, //0:空目录不处理 1：空目录要处理
                'dispmode' => 1,        //0: 显示细节 1：不显示细节
                'hiswriteid' => 1,      //0: 禁用历史保存为文件1: 启用历史保存为文件

            );
            $result = $rpc->addBackupRemotejob($standbyInfo['ip'], $rpcMsg);
            //             var_dump($rpcMsg, $result);

            //*修改远程备份任务历史文件策略
            $result = $this->modifyBackupHistoryStrategy($productInfo, $standbyInfo, $params, $taskname);

        }

        return true;
    }

    /**
     * 得到目录文件实时跟踪配置
     * @param unknown $strategytype
     */
    private function getRealidValue($strategytype)
    {
        $realid = 1;    //实时
        $strategytype = intval($strategytype);
        if ($strategytype == 1) {
            $realid = 0;//不实时
        }
        return $realid;
    }

    /**
     * 修改备份历史策略
     * @param unknown $params
     */
    private function modifyBackupHistoryStrategy($productInfo, $standbyInfo, $params, $taskname)
    {
        $backuptype = intval($params['standbyhostInfo']['backuptype']);
        //如果是实时备份,直接返回
        if ($backuptype == Xphp::$_config['FILE_CDP_BACKUP_TYPE']['REALTIME_BACKUP']) {
            return true;
        }

        $history = $params['highInfo']['history'];
        $rpc = Xphp::instance('FileRPCHandler');
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productInfo['ip'],
            'port' => 0,
            'taskname' => $taskname,
            'hisdays' => $history['historytime'],
            'chghismax' => $history['historymod'],
            'chghisdsec' => $history['historytimeinterval'],
            'delhismax' => $history['historydel'],
        );
        $result = $rpc->chgBackupjobHisFilePolicy($standbyInfo['ip'], $rpcMsg);
        //         var_dump($result);
        return true;
    }

    /**
     * 得到文件过滤时间ID
     * 发送到后台使用
     * 0.不理会日期 1-99 按天计算 101-199按月计算(v-100月) 201-299按年计算(v-200)年
     * @param unknown $params
     */
    private function getFileTimeFilterID($params)
    {
        $filter = $params['highInfo']['filter'];                //文件过滤
        $flag = $filter['timefilter'];                          //时间过滤开关
        $filterType = intval($filter['timefiltertype']);        //时间过滤类型 1天 2月 3年
        $timefiltervalue = intval($filter['timefiltervalue']);          //时间过滤值
        $dateid = 0;
        if ($flag) {
            $confType = Xphp::$_config['FILE_CDP_TIME_FILTER_TYPE'];
            //配置了时间过滤
            if ($confType['DAY'] == $filterType) {
                $dateid = $timefiltervalue;
            } elseif ($confType['MONTH'] == $filterType) {
                $dateid = $timefiltervalue + 100;
            } elseif ($confType['YEAR'] == $filterType) {
                $dateid = $timefiltervalue + 200;
            }
        } else {
            //没有配置时间过滤
            $dateid = 0;
        }

        return $dateid;
    }

    /**
     * 得到默认搜索时间段
     * 发送到后台使用
     * @param unknown $params
     */
    private function getSearchTimeSeg($params)
    {
        $strategy = $params['highInfo']['strategy'];
        $strategytype = intval($strategy['strategytype']);      //时间策略类型
        $starttime = $strategy['starttime'];                    //开始时间
        $endtime = $strategy['endtime'];                        //结束时间

        $confType = Xphp::$_config['FILE_CDP_TIME_STRATEGY_TYPE'];

        $timeSeg = "";
        $sacnInterval = 86400;     //扫描间隔默认1天


        $utils = Xphp::instance('Utils');
        if ($confType['ALLDAY'] == $strategytype) {
            //全天
            $timeSeg = "00:00:00" . "/" . "23:59:59" . "/" . $sacnInterval;
        } else {
            //如果是指定时间备份,扫描间隔为10秒
            $sacnInterval = 10;

            //指定时间段,一定要传8位的格式过去
            $timeSeg = $utils->formartTime($starttime) . "/" . $utils->formartTime($endtime) . "/" . $sacnInterval;
        }

        return $timeSeg;
    }

    /**
     * 获取修改任务需要的任务所有信息,用于界面展示
     * @param unknown $params
     */
    public function getBackupTaskAllInfo($params)
    {
        $taskuuid = $params['taskuuid'];
        $this->paramsCheck($taskuuid);

        $sql = "select bt.task_name, cft.product_host_uuid, cft.standby_host_uuid,  cft.config
                 from bd_task bt, cdp_fs_task cft where bt.task_uuid = cft.task_uuid and bt.task_uuid= ?";
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

        return json_encode($info);
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


        $operate = Xphp::$_lang['WEB_FILE_CDP_EDIT_BACKUP_TASK'];
        $taskuuid = $params['taskuuid'];

        //检查生产主机是否授权
        $checkResult = $this->checkHostIsLicensed($productHostuuid);
        if (!$checkResult) {
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_PRODUCT_HOST_NOT_AUTH_TIPS'], "warning");
        }
        //检查备份主机是否授权
        $checkResult = $this->checkHostIsLicensed($standbyHostuuid);
        if (!$checkResult) {
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_BACKUP_HOST_NOT_AUTH_TIPS'], "warning");
        }

        //如果主站和从站任意一个不在线,直接返回,不做修改
        if (
            Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $productHostStatus ||
            Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus
        ) {
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_PRODUCT_OR_BACKUP_HOST_OFFLINE_TIPS'], "warning");
        }

        //先处理一下备份文件列表,加上一些特殊的配置,如16位MD5,存储路径等
        $params = $this->processBackupParams($taskuuid, $params);

        $rpc = Xphp::instance('FileRPCHandler');

        //***********************************处理备份主机历史信息
        //*删除远程备份任务,先获取,然后删除
        //获取
        $msgData = $this->getAllBackupJob($productHostIP, $standbyHostIP);

        //删除
        foreach ($msgData as $d) {
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productHostIP,
                'port' => 0,
                'taskname' => $d['title'],
            );
            $result = $rpc->delBackupRemotejob($standbyHostIP, $rpcMsg);
        }

        //**********************************处理生产主机历史信息
        $fileInfo = $params['oldData']['producthostInfo']['fileInfo'];
        //删除任务
        foreach ($fileInfo as $file) {
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                'taskname' => $file['taskname'],
            );
            $result = $rpc->delHostAllowBkTask($productHostIP, $rpcMsg);
        }

        //配置主站信息
        $this->SettingBackupJobProductHost($productHostInfo, $params);
        //配置从站信息
        $this->SettingBackupJobStandbyHost($productHostInfo, $standbyHostInfo, $params, $taskuuid);
        //写入数据库信息

        //如果都配置成功,插入信息到数据库
        $taskName = htmlspecialchars_decode($params['jobname']);
        $moduleType = Xphp::$_config['MODULE_TYPE']['OEM_FSCDP'];
        $taskType = Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP'];
        $taskStatus = Xphp::$_config['TASKSTATUS']['STOPPED'];
        $createTime = date("Y-m-d H:i:s", time());
        $useruuid = Xphp::$_user['useruuid'];
        $deleteFlag = Xphp::$_config['FLAG']['UNSET'];

        $this->dbBeginTransaction();
        $result = true;
        //更新bd_task
        $sql = "update bd_task set task_name = ?, create_time = ?, user_uuid = ? where task_uuid = ?";
        $sqlParams = array($taskName, $createTime, $useruuid, $taskuuid);
        $result = $result && $this->dbExec($sql, $sqlParams);

        //更新cdp_fs_task
        $sql = "update cdp_fs_task set config = ? where task_uuid = ?";
        $params['oldData'] = "";    //不保存之前的数据
        $sqlParams = array(json_encode($params, true), $taskuuid);
        $result = $result && $this->dbExec($sql, $sqlParams);

        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }

        if ($result) {
            $this->writeTaskLog($taskuuid, $taskName, $taskType, 'BD_TASKLOG_DESC_KEY_MODIFY_TASK_SUCCESS');
        } else {
            $this->writeTaskLog($taskuuid, $taskName, $taskType, 'BD_TASKLOG_DESC_KEY_MODIFY_TASK_FAILURE');
        }

        return $this->muOpResult($result, $operate);
    }
    /**
     * 删除实时备份任务
     * @param unknown $params
     */
    public function deleteBackupJob($params)
    {
        //         return $this->modifyBackupJob($params);

        $standbyHostIP = $params['fscdp']['standbyip'];
        $productHostIP = $params['fscdp']['productip'];
        $operate = Xphp::$_lang['WEB_FILE_CDP_DELETE_BACKUP_TASK'];
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);

        //检查任务状态,查看是否可以删除,然后删除(只是运行中无法删除)
        $sql = "select bt.task_status, bt.task_name, bt.task_type,  cdt.config from
                bd_task bt, cdp_fs_task cdt where bt.task_uuid = cdt.task_uuid and bt.task_uuid = ?";

        $data = $this->dbSelect($sql, array($taskuuid));
        if (intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['RUNNING']) {
            //如果任务正在运行,不能删除,直接提示.
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_DELETE_BACKUP_TASK_ERROR_TIPS'], 'warning');
        }

        $productHostInfo = $this->getHostInfoWithIP($productHostIP);
        $standbyHostInfo = $this->getHostInfoWithIP($standbyHostIP);
        $productHostStatus = intval($productHostInfo['status']);
        $standbyHostStatus = intval($standbyHostInfo['status']);

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE'] == $standbyHostStatus) {
            //如果备机在线

            //先删除备份数据
            /**不删除备份数据
             $delDataResult = $this->deleteBackupData($productHostInfo, $standbyHostInfo, json_decode($data[0]['config'], true));
             if(!$delDataResult){
             return $this->muOpResult(false, $operate, '删除备份数据失败', 'warning');
             }*/

            //*删除连接主站的信息
            $rpc = Xphp::instance('FileRPCHandler');
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productHostIP,
                'port' => 0,
            );
            $result = $rpc->DelConnectHost($standbyHostIP, $rpcMsg);

            //*删除远程备份任务,先获取,然后删除

            //获取
            $msgData = $this->getAllBackupJob($productHostIP, $standbyHostIP);

            //删除
            foreach ($msgData as $d) {
                $rpcMsg = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                    'hipstr' => $productHostIP,
                    'port' => 0,
                    'taskname' => $d['title'],
                );
                $result = $rpc->delBackupRemotejob($standbyHostIP, $rpcMsg);
            }

            //             if(!$result['result']){
            //                 return $this->muOpResult(false, $operate, $result['errorMsg'], 'warning',$result['errorCode']);
            //             }
        }

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE'] == $productHostStatus) {
            //如果生产主机在线
            $config = json_decode($data[0]['config'], true);
            $fileInfo = $config['producthostInfo']['fileInfo'];
            //删除任务
            foreach ($fileInfo as $file) {
                $rpcMsg = array(
                    'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                    'taskname' => $file['taskname'],
                );
                $result = $rpc->delHostAllowBkTask($productHostIP, $rpcMsg);
            }
        }

        $this->dbBeginTransaction();
        $sql = "delete from  bd_task where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $this->dbQuery($sql, $sqlParams);

        $sql = "delete from bd_running_info where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $result && $this->dbQuery($sql, $sqlParams);

        $sql = "delete from cdp_fs_task where task_uuid = ?";
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
     * 获取从站上指定IP的所有备份任务
     * @param unknown $productHostIP
     * @param unknown $standbyHostIP
     */
    private function getAllBackupJob($productHostIP, $standbyHostIP)
    {
        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_IP_BACKUP_TASK'];
        $rpc = Xphp::instance('FileRPCHandler');
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHostIP,
            'port' => 0,
        );
        $result = $rpc->getbackupRemotejob($standbyHostIP, $rpcMsg);
        //         $data = $this->checkRPCMsg($operate, $result);
        $data = $result['data']['data'];    //如果主机不在线删除不掉,这里不调用checkRPCMsg
        return $data;
    }

    /**
     * 删除备份数据
     * @param unknown $params
     */
    private function deleteBackupData($productHostInfo, $standbyHostInfo, $config)
    {
        $operate = Xphp::$_lang['WEB_FILE_CDP_DELETE_BACKUP_DATA'];
        $rpc = Xphp::instance('FileRPCHandler');
        foreach ($config['producthostInfo']['fileInfo'] as $fileInfo) {
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productHostInfo['ip'],
                'port' => 0,
                'taskname' => $fileInfo['taskname'],
            );
            $result = $rpc->purgeTaskBackupFiles($standbyHostInfo['ip'], $rpcMsg);
            //             $data = $this->checkRPCMsg($operate, $result);
        }
        return true;
    }

    /**
     * 创建文件恢复任务
     * @param unknown $params
     */
    public function createRecoveryJob($params)
    {
        $srchostuuid = $params['standbyhostuuid'];
        $deshostuuid = $params['recoveryhostuuid'];
        $srcHostInfo = $this->getHostInfoWithUUID($srchostuuid);
        $desHostInfo = $this->getHostInfoWithUUID($deshostuuid);

        $operate = Xphp::$_lang['UI_RECOVERY_FILE_DESCRIPTION'];
        //一个主机对应一个备机只能创建一个备份任务,检查这里
        $sql = "select count(cft.id) as total from cdp_fs_task cft,  bd_task bt where
                cft.task_uuid = bt.task_uuid and cft.product_host_uuid = ? and
                cft.standby_host_uuid = ? and bt.task_type = ?";
        $sqlParams = array($deshostuuid, $srchostuuid, Xphp::$_config['TASKTYPE']['FILE_CDP_RECOVERY']);
        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]['total'] > 0) {
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_SOURCE_OR_TARGET_HOST_EXIST_TASK_TIPS'], "warning");
        }

        //先处理一下恢复列表,去掉重复的内容
        $params = $this->processRecoveryParams($params);

        //如果都配置成功,插入信息到数据库
        $taskuuid = Xphp::instance('Utils', 'uuid');
        $taskName = htmlspecialchars_decode($params['jobname']);
        $moduleType = Xphp::$_config['MODULE_TYPE']['OEM_FSCDP'];
        $taskType = Xphp::$_config['TASKTYPE']['FILE_CDP_RECOVERY'];
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

        $sql = "insert into cdp_fs_task(task_uuid, product_host_uuid, standby_host_uuid, config) values(?, ?, ?, ?)";
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
     * 处理并过滤恢复参数
     * @param unknown $params
     */
    private function processRecoveryParams($params)
    {
        $srcFileInfo = $params['srcFileInfo'];
        $pathList = $params['pathList'];

        $newFileInfo = array();
        foreach ($srcFileInfo as $file) {
            if (in_array(dirname($file['path']), $pathList)) {
                //去掉重复的文件和文件夹,选择了父目录,子目录和子文件就不需要了
                continue;
            }
            $newFileInfo[] = $file;
        }

        $params['srcFileInfo'] = $newFileInfo;
        $params['pathList'] = array();  //不用了,只是用来判断一下

        return $params;
    }

    /**
     * 删除恢复任务
     * @param unknown $params
     */
    public function deleteRecoveryJob($params)
    {
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);
        $operate = Xphp::$_lang['WEB_FILE_CDP_DELETE_RESTORE_TASK'];

        //先检查任务是否可以删除,正在运行不能删除
        $sql = "select task_status from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['RUNNING']) {
            //如果任务正在运行,不能删除,直接提示.
            return $this->muOpResult(false, $operate, Xphp::$_lang['WEB_FILE_CDP_DELETE_RESTORE_TASK_FAIL_TIPS'], 'warning');
        }

        $productHostIP = $params['fscdp']['productip'];
        $standbyHostIP = $params['fscdp']['standbyip'];
        $productHostUUID = $params['fscdp']['productuuid'];
        $standbyHostUUID = $params['fscdp']['standbyuuid'];

        $productHostInfo = $this->getHostInfoWithUUID($productHostUUID);
        $productHostStatus = intval($productHostInfo['status']);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostUUID);
        $standbyHostStatus = intval($standbyHostInfo['status']);


        if (Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE'] == $standbyHostStatus) {
            $rpc = Xphp::instance('FileRPCHandler');
            //如果从站在线,释放从站恢复任务
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productHostIP,
                'port' => 7817,
            );
            $result = $rpc->freeRecoveryCliInfo($standbyHostIP, $rpcMsg);
            //             var_dump($result);
        }


        $this->dbBeginTransaction();
        $sql = "delete from  bd_task where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $this->dbQuery($sql, $sqlParams);

        $sql = "delete from bd_running_info where task_uuid = ?";
        $sqlParams = array($taskuuid);
        $result = $result && $this->dbQuery($sql, $sqlParams);

        $sql = "delete from cdp_fs_task where task_uuid = ?";
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
     * 启动实时备份任务
     * @param unknown $params
     */
    public function startBackupJob($params)
    {
        //         var_dump($params);
        //         return;
        $productHostIP = $params['fscdp']['productip'];
        $standbyHostIP = $params['fscdp']['standbyip'];
        $taskuuid = $params['uuid'];

        $dbCDP = Xphp::instance('DbCDPHandler');
        $rpc = Xphp::instance('FileRPCHandler');
        $operate = Xphp::$_lang['WEB_FILE_CDP_START_BACKUP_TASK'];
        $opResult = true;
        $data = array();

        //任务检查,检查主站是否有恢复任务运行,有的话直接提示返回
        //         $checkStatus = $this->checkHostRecoveryJobInRunning($productHostIP);
        //         if($checkStatus){
        //             return $this->muOpResult(false, $operate, '生产主机有恢复任务正在运行,请先停止恢复任务!', 'warning');
        //         }

        //启用备份任务
        $this->ControlBackupJob($productHostIP, $standbyHostIP, true);

        //启动主站备份系统
        $dbCDP->startBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'], $productHostIP);
        sleep(2);


        $hostStatus = $dbCDP->checkHostServiceStatus(Xphp::$_config['DB_CDP_SYSCODE']['filestandby'], $standbyHostIP);
        if (Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] !== $hostStatus['bakStatus']) {
            //如果从站未启动,才启动,启动了就不用再启动了
            $opResult = $opResult && $dbCDP->startBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['filestandby'], $standbyHostIP);
        }


        //启动任务
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHostIP,
        );
        $result = $rpc->startRemoteHostIpConnect($standbyHostIP, $rpcMsg);

        //         var_dump($standbyHostIP, $rpcMsg, $result);
        $data = $this->checkRPCMsg($operate, $result);

        $sql = "select task_name, task_type from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        if ($opResult) {
            //配置主站子系统启动模式为开机启动
            $rpc = Xphp::instance('DbRPCHandler');
            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                'startmode' => 1
            );
            $rpc->setServiceStartMode($productHostIP, $data);
            $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STARTING']);
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_START_TASK_SUCCESS');
        } else {
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_START_TASK_FAILURE');
        }

        return $this->muOpResult($opResult, $operate);
    }

    /**
     * 获取文件实时备份任务监控基本信息
     * @param unknown $params
     */
    public function getBackupBasicInfo($params)
    {
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);

        $sql = "select bt.task_name, bt.module_type, bt.task_type, bt.task_status, cdt.product_host_uuid,
                cdt.standby_host_uuid, cdt.config from bd_task bt, cdp_fs_task cdt where bt.task_uuid =
                cdt.task_uuid and cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);

        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $dbDes = include APP_PATH . 'db/DbCDPDescription.php';
        $utils = Xphp::instance('Utils');

        $taskConfig = json_decode($d['config'], true);
        $producthostInfo = $taskConfig['producthostInfo'];
        $standbyhostInfo = $taskConfig['standbyhostInfo'];
        $highInfo = $taskConfig['highInfo'];


        $info = array(
            'taskName' => $d['task_name'],
            'taskTypeDes' => $ptDes['TASKTYPEDES'][$d['task_type']],
            'taskType' => intval($d['task_type']),
            'status' => intval($d['task_status']),
            'statusDes' => $ptDes['TASKSTATUSDES'][$d['task_status']],
            'productHost' => $productHost['host_name'] . "(" . $productHost['ip'] . ")",
            'standbyHost' => $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")",
            'backupDir' => $standbyhostInfo['backupdir'],
            'historyDir' => $standbyhostInfo['historydir'],
            'backupType' => intval($standbyhostInfo['backuptype']),
            'producthostInfo' => $producthostInfo,
            'standbyhostInfo' => $standbyhostInfo,
            'highInfo' => $highInfo,


            "productip" => $productHost['ip'],
            "standbyip" => $standbyHost['ip'],
            "productuuid" => $productHost['host_uuid'],
            "standbyuuid" => $standbyHost['host_uuid'],
        );

        return json_encode($info, true);
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
                bd_task bt, cdp_fs_task cdt where bt.task_uuid = cdt.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);
        $taskStatus = $d['task_status'];
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);
        $config = json_decode($data[0]['config'], true);

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
            //备机离线
            $info = array();
            $runmsgtxt = sprintf(Xphp::$_lang['WEB_LOG_FILE_BACKUP_HOST_OFFLINE'], $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")");
            $info[] = array(date("Y-m-d H:i:s"), Xphp::$_lang['API_CODE_ERROR'], $runmsgtxt, array('level' => 3));
            return json_encode($info);
        }

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $productHostStatus) {
            //主机离线
            $info = array();
            $runmsgtxt = sprintf(Xphp::$_lang['WEB_LOG_FILE_PRODUCT_HOST_OFFLINE'], $productHost['host_name'] . "(" . $productHost['ip'] . ")");
            $info[] = array(date("Y-m-d H:i:s"), Xphp::$_lang['API_CODE_ERROR'], $runmsgtxt, array('level' => 3));
            return json_encode($info);
        }

        $productHostTxt = $productHost['host_name'] . "(" . $productHost['ip'] . ")";
        $standbyHostTxt = $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")";

        //如果任务已经停止
        if ($taskStatus == Xphp::$_config['TASKSTATUS']['STOPPED']) {

            $fileInfo = array(
                'producthostName' => $productHostTxt,
                'standbyhostName' => $standbyHostTxt,
                'totalSize' => "----",
                'dirTotal' => "----",
                'fileTotal' => "----",
                'finishTotal' => "----",
                'successTotal' => "----",
                'failureTotal' => "----",
                'backupDir' => $config['standbyhostInfo']['backupdir'],
                'lastFinishTime' => "----",
                'currentFile' => "----",
                'filelist' => $config['producthostInfo']['fileInfo'],
            );

            $returnInfo = array(
                "log" => array(),
                "file" => $fileInfo,
            );
            return json_encode($returnInfo);
        }

        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_BACKUP_TASK_DETAILS'];
        $rpc = Xphp::instance('FileRPCHandler');
        $utils = Xphp::instance('Utils');

        //获取任务连接时间
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHost['ip'],
        );
        $result = $rpc->getRemoteHostIpConnStatus($standbyHost['ip'], $msgData);
        $data = $this->checkRPCMsg($operate, $result);
        $connectTime = "----";
        $commturntime = "----";      //最近一次结束时间
        if (!empty($data)) {
            $connectTime = $data[0]['logintime'];
            $commturntime = $data[0]['commturntime'];
        }

        //获取备份主机当前时间
        $result = $rpc->getTime($standbyHost['ip'], array());
        $data = $this->checkRPCMsg($operate, $result);
        $currentTime = "----";
        if (!empty($data)) {
            $currentTime = $data[0]['curtime'];
        }

        //获取任务详情
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHost['ip'],
        );
        $result = $rpc->getTaskStaticInfos($standbyHost['ip'], $msgData);
        $data = $this->checkRPCMsg($operate, $result);


        $totalbytes = 0;    //总大小
        $dircounter = 0;    //目录总数
        $filecounter = 0;   //文件总数
        $flishcounter = 0;  //完成文件总数
        $failscounter = 0;  //失败文件总数

        $realaddfiles = 0;  //新加文件数量
        $realaddsize = 0;   //新加文件大小
        $realchgfiles = 0;  //修改文件数量
        $realchgsize = 0;   //修改文件大小
        $realdelfiles = 0;  //删除文件数量
        $realdelsize = 0;   //删除文件大小



        $warnningInfo = array();
        foreach ($data as $key => $value) {
            if (0 == $key) {
                //添加初始化信息
                $info[] = array($connectTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_FILE_CDP_GET_TASK_CONFIG_INFO'], array('level' => 1));
                $info[] = array($connectTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_FILE_CDP_PREPARE_SYNC_ENVIRONMENT'], array('level' => 1));
                $info[] = array($connectTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_FILE_CDP_CHECK_START_PRODUCT_HOST'] . '"' . $productHostTxt . '"' . Xphp::$_lang['WEB_FILE_CDP_SERVICE'], array('level' => 1));
                $info[] = array($connectTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_FILE_CDP_CHECK_START_BACKUP_HOST'] . '"' . $standbyHostTxt . '"' . Xphp::$_lang['WEB_FILE_CDP_SERVICE'], array('level' => 1));
                $info[] = array($connectTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_FILE_CDP_CHECK_ENVIRONMENT_COMPLETE'], array('level' => 1));
            }
            $totalbytes += $value['totalbytes'] + $value['realaddsize'];
            $dircounter += $value['dircounter'] + $value['realadddirs'];
            //             $filecounter += $value['filecounter'];
            $flishcounter += $value['flishcounter'] + $value['realaddfiles'];
            $failscounter = $value['failscounter'];

            //             $realaddfiles += $value['realaddfiles'];
            //             $realaddsize += $value['realaddsize'];
            $realchgfiles += $value['realchgfiles'];
            $realchgsize += $value['realchgsize'];
            $realdelfiles += $value['realdelfiles'];
            $realdelsize += $value['realdelsize'];

            if ($failscounter > 0) {
                //如果有失败的文件,增加警告提醒日志
                $failsInfo = sprintf(
                    Xphp::$_lang['WEB_LOG_FILE_SYNC_DIR_ERROR'],
                    $this->getTaskDirPath($config['producthostInfo']['fileInfo'], $value['taskname']),
                    $failscounter,
                    '<a class="nowscan" data-taskname="' . $value['taskname'] . '">' . Xphp::$_lang['WEB_FILE_CDP_SCAN'] . '</a>',
                    $utils->secToTime($value['searchstatus'])
                );
                $warnningInfo[] = array($currentTime, Xphp::$_lang['WEB_PLATFORM_DES_ABNORMAL'], $failsInfo, array('level' => 2));
            }
        }


        //添加备份统计信息
        $totalbytesDes = $utils->calSize($totalbytes);
        $totalInfo = sprintf(Xphp::$_lang['WEB_LOG_FILE_SYNC_NUM'], $totalbytesDes, $dircounter, $flishcounter);
        //如果有修改文件,添加修改文件统计
        if ($realchgfiles > 0) {
            $totalInfo .= "; " . sprintf(Xphp::$_lang['WEB_LOG_FILE_EDIT_NUM'], $realchgfiles, $utils->calSize($realchgsize));
        }

        //如果有删除文件,添加删除文件统计
        if ($realdelfiles > 0) {
            $totalInfo .= "; " . sprintf(Xphp::$_lang['WEB_LOG_FILE_DELETE_NUM'], $realdelfiles, $utils->calSize($realdelsize));
        }

        $info[] = array($currentTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], $totalInfo, array('level' => 1));

        //获取是否正在备份
        $backupStatus = $this->getActiveBackupStatus($productHost['ip'], $standbyHost['ip']);
        if ($backupStatus) {
            //如果有文件正在备份
            $info[] = array($currentTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_FILE_CDP_SYNC_TIPS'], array('level' => 1));

            //获取一下当前正在备份的文件
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productHost['ip'],
                'maxnum' => 1,
            );
            $result = $rpc->getTaskWorkFiles($standbyHost['ip'], $msgData);
            $data = $this->checkRPCMsg($operate, $result);
            if (!empty($data)) {
                //按开始时间排序,这里返回的是每个"任务"会有一条记录
                $data = $utils->arraySort($data, 8, 'desc', 0, -1);
                $hfile = $data[0]['hfile'];                 //文件名
                $filesize = $data[0]['filesize'];           //文件总大小
                $writefsize = $data[0]['writefsize'];       //文件完成大小

                //如果获取到了数据,取第一条
                $currentText = sprintf(
                    '当前正在同步文件"%s",大小"%s",进度"%s"',
                    $hfile,
                    $utils->calSize($filesize),
                    $utils->calPercent($filesize, $writefsize)
                );
                $info[] = array($currentTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], $currentText, array('level' => 1));
            }

        } else {
            //如果当前没有备份
            $hisText = sprintf("已有文件同步完成,开始实时监控,等待下次同步,最近一次同步结束时间:%s", $commturntime);
            $info[] = array($currentTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], $hisText, array('level' => 1));
        }

        //合并正常和异常的日志
        $info = array_merge($info, $warnningInfo);
        //按时间反排
        $info = array_reverse($info);



        ////////////////////日志获取完毕,获取文件信息//////////////////
        if (empty($hfile)) {
            $hfile = Xphp::$_config['TIMESPACE'];
        }
        $fileInfo = array(
            'producthostName' => $productHostTxt,
            'standbyhostName' => $standbyHostTxt,
            'totalSize' => $totalbytesDes,
            'dirTotal' => $dircounter,
            'fileTotal' => $flishcounter,
            'finishTotal' => $flishcounter,
            'successTotal' => $flishcounter - $failscounter,
            'failureTotal' => $failscounter,
            'backupDir' => $config['standbyhostInfo']['backupdir'],
            'lastFinishTime' => $commturntime,
            'currentFile' => $hfile,
            'filelist' => $config['producthostInfo']['fileInfo'],
        );

        //这里返回运行日志以外,同时返回文件信息,以免二次再遍历数据
        $returnInfo = array(
            "log" => $info,
            "file" => $fileInfo,
        );

        return json_encode($returnInfo);

    }

    /**
     * 立即扫描
     * @param unknown $params
     */
    public function nowScanDir($params)
    {
        $productip = $params['productip'];
        $standbyip = $params['standbyip'];
        $taskname = $params['taskname'];
        $this->paramsCheck($productip, $standbyip, $taskname);

        $operate = Xphp::$_lang['WEB_FILE_CDP_SEND_SCAN_DIR_MESSAGE'];
        $rpc = Xphp::instance('FileRPCHandler');

        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productip,
            'port' => 0,
            'taskname' => $taskname
        );
        $result = $rpc->forceDoTask($standbyip, $msgData);

        return $this->muOpResult($result['result'], $operate);
    }


    /**
     * 获取恢复任务监控详情
     * @param unknown $params
     */
    public function getRecoveryAllInfo($params)
    {
        $taskuuid = $params['uuid'];
        $this->paramsCheck($taskuuid);

        $sql = "select bt.task_name, bt.module_type, bt.task_type, bt.task_status, cdt.product_host_uuid,
                cdt.standby_host_uuid, cdt.config, bri.speed, bri.speed_time from bd_task bt, cdp_fs_task cdt, bd_running_info bri
                where bt.task_uuid = cdt.task_uuid and bt.task_uuid = bri.task_uuid and cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);
        $config = json_decode($d['config'], true);

        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_RECOVERY_TASK_DETAILS'];
        $rpc = Xphp::instance('FileRPCHandler');
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHost['ip'],
        );
        $result = $rpc->getRecoveryCliInfo($standbyHost['ip'], $msgData);



        $fileData = array();
        if ($result['result']) {
            $data = $this->checkRPCMsg($operate, $result);
            $fileData = $data;
        }

        //                 var_dump($msgData, $result, $data);
        //                 return;

        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $utils = Xphp::instance('Utils');

        $totalfsum = 0;     //总文件个数
        $totalfsize = 0;    //总文件大小
        $copytotalsize = 0; //总完成大小
        $copyspeed = 0;     //即时速度

        $filshfsum = 0;     //文件完成总数
        $okfsum = 0;        //文件成功完成总数
        $failsfsum = 0;     //文件失败完成总数

        $workidArr = array();   //工作状态记录
        $runcodeArr = array();     //运行状态记录
        $etimeArr = array();       //结束时间记录
        $searchetimeArr = array(); //扫描结束时间记录

        $startTime = "----";
        $endTime = "";
        $i = 0;
        foreach ($fileData as $file) {
            $workidArr[] = $file['workid'];
            $runcodeArr[] = $file['runcode'];
            $etimeArr[] = $file['etime'];
            $searchetimeArr = $file['searchetime'];

            $totalfsum += $file['totalfsum'];
            $totalfsize += $file['totalfsize'];
            $copytotalsize += $file['copytotalsize'];

            $filshfsum += $file['filshfsum'];
            $okfsum += $file['okfsum'];
            $failsfsum += $file['failsfsum'];


            if (intval($file['workid']) == Xphp::$_config['FILE_CDP_RECOVERY_WORDID']['CHECKING']) {
                //如果在运行中才算速度
                $copyspeed += $file['copyspeed'];
            }
            if (0 == $i) {
                //第一次给一个开始时间
                $startTime = $file['btime'];
            }
            if (strtotime($file['btime']) - strtotime($startTime) < 0) {
                //取一个最小的开始时间
                $startTime = $file['btime'];
            }

            if (strtotime($file['etime']) - strtotime($endTime) > 0) {
                //取一个最大的结束时间
                $endTime = $file['etime'];
            }
            $i++;
        }


        //获取当前时间
        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_SYSTEM_TIME'];
        $rpc = Xphp::instance('FileRPCHandler');
        $result = $rpc->getTime($standbyHost['ip'], array());
        $data = $this->checkRPCMsg($operate, $result);
        $currentTime = "";
        if (!empty($data)) {
            $currentTime = $data[0]['curtime'];
        }


        if (
            $d['task_status'] == Xphp::$_config['TASKSTATUS']['FINISHED'] ||
            $d['task_status'] == Xphp::$_config['TASKSTATUS']['STOPPED']
        ) {
            //如果已经完成或停止,计算持续时间的结束时间为etime
            $currentTime = $endTime;
            $copyspeed = 0;
        }

        if ($startTime != "----") {
            $intervalTime = $utils->secToTime(strtotime($currentTime) - strtotime($startTime));
        } else {
            $intervalTime = "----";
        }


        $progress = $utils->calPercent($totalfsize, $copytotalsize);
        $progress = sprintf("%.2f", substr($progress, 0, -1)) . "%";
        $totalfsize = $utils->calSize($totalfsize);
        $copytotalsize = $utils->calSize($copytotalsize);

        $basicInfo = array(
            'flag' => true,
            'taskName' => $d['task_name'],
            'taskTypeDes' => $ptDes['TASKTYPEDES'][$d['task_type']],
            'taskType' => $d['task_type'],
            'status' => intval($d['task_status']),
            'statusDes' => $ptDes['TASKSTATUSDES'][$d['task_status']],
            'productHost' => $productHost['host_name'] . "(" . $productHost['ip'] . ")",
            'standbyHost' => $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")",
            'filelist' => $config['srcFileInfo'],
            'distDir' => $config['distDir'],
            'totalSize' => $totalfsize,
            'currentSize' => $copytotalsize,
            'startTime' => $startTime,
            'intervalTime' => $intervalTime,
            'totalprogress' => $progress,
            'progress' => $progress,

            "productip" => $productHost['ip'],
            "standbyip" => $standbyHost['ip'],
            "productuuid" => $productHost['host_uuid'],
            "standbyuuid" => $standbyHost['host_uuid'],
        );

        $jobSpeed = array(
            "speed" => round($d['speed'] / 1024, 2),
            "t" => $d['speed_time'],
            "nowTime" => date("H:i:s", strtotime($currentTime)),
        );

        //如果任务不在运行中,将速度置零
        if ($d['task_status'] != Xphp::$_config['TASKSTATUS']['RUNNING']) {
            $jobSpeed['speed'] = 0;
        }

        $fileInfo = array(
            'startTime' => $startTime,
            'endTime' => $endTime,
            'totalfsum' => $totalfsum,
            'filshfsum' => $filshfsum,
            'okfsum' => $okfsum,
            'failsfsum' => $failsfsum,

        );

        $info = array(
            'basicInfo' => $basicInfo,
            'jobSpeed' => $jobSpeed,
            'log' => $this->getRecoveryRunningLog($productHost, $standbyHost, $d['task_status'], $fileInfo, $i)
        );
        return json_encode($info);
    }


    /**
     * 得到恢复任务的任务监控日志
     * @param unknown $params
     */
    public function getRecoveryRunningLog($productHost, $standbyHost, $status, $fileInfo, $num)
    {
        $startTime = $fileInfo['startTime'];
        $endTime = $fileInfo['endTime'];
        $totalfsum = $fileInfo['totalfsum'];
        $filshfsum = $fileInfo['filshfsum'];
        $okfsum = $fileInfo['okfsum'];
        $failsfsum = $fileInfo['failsfsum'];

        $info = array();
        if (0 == $num) {
            return $info;
        }


        //初始化开始日志 0 1
        $info[] = array($startTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_FILE_CDP_START_RECOVERY_TASK'], array('level' => 1));
        $info[] = array($startTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_FILE_CDP_CHECK_RESTORE_ENVIRONMENT'], array('level' => 1));
        $info[] = array($startTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], sprintf(Xphp::$_lang['WEB_LOG_FILE_CHECK_START_BACKUP_HOST'], $standbyHost['host_name'] . "(" . $standbyHost['ip'] . ")"), array('level' => 1));
        $info[] = array($startTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], sprintf(Xphp::$_lang['WEB_LOG_FILE_CHECK_START_RECOVERY_TARGET_HOST'], $productHost['host_name'] . "(" . $productHost['ip'] . ")"), array('level' => 1));
        $info[] = array($startTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], sprintf(Xphp::$_lang['WEB_LOG_FILE_SCAN_RECOVERY_FILE_NUM'], $totalfsum), array('level' => 1));
        $info[] = array($startTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_LOG_FILE_START_RESTORE_DATA'], array('level' => 1));


        $level = 1;
        $failureStr = "";
        if ($failsfsum > 0) {
            //有几个异常
            $level = 2;
            $failureStr .= "," . sprintf(Xphp::$_lang['WEB_LOG_FILE_FAIL_NUM'], $failsfsum);
        }
        if ($filshfsum != 0 && $filshfsum == $failsfsum) {
            //完成的全部失败
            $level = 3;
            $failureStr .= "," . sprintf(Xphp::$_lang['WEB_LOG_FILE_FAIL_NUM'], $failsfsum);
        }

        if ($status == Xphp::$_config['TASKSTATUS']['FINISHED']) {
            //如果已经完成
            $overStr = sprintf(Xphp::$_lang['WEB_LOG_FILE_TRANSFER_COMPLETE_NUM'], $filshfsum, $okfsum);
            $info[] = array($endTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], $overStr . $failureStr, array('level' => $level));

            $info[] = array($startTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_LOG_FILE_TASK_COMPLETED'], array('level' => 1));
        } else if ($status == Xphp::$_config['TASKSTATUS']['STOPPED']) {
            //如果任务已经停止
            $overStr = sprintf(Xphp::$_lang['WEB_LOG_FILE_RECOVERY_TASK_STOP_NUM'], $filshfsum, $okfsum);
            $info[] = array($endTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], $overStr . $failureStr, array('level' => $level));

            $info[] = array($startTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], Xphp::$_lang['WEB_LOG_TASK_TASK_STOPPED'], array('level' => 1));
        } else {
            $overStr = sprintf(Xphp::$_lang['WEB_LOG_FILE_TRANSFERRING_NUM'], $filshfsum, $okfsum);
            $info[] = array($endTime, Xphp::$_lang['WEB_PLATFORM_DES_NORMAL'], $overStr . $failureStr, array('level' => $level));
        }

        $info = array_reverse($info);

        return $info;
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
                bd_task bt, cdp_fs_task cdt where bt.task_uuid = cdt.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $d = $data[0];
        $productHost = $this->getHostInfoWithUUID($d['product_host_uuid']);
        $standbyHost = $this->getHostInfoWithUUID($d['standby_host_uuid']);

        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $taskStatus = intval($d['task_status']);
        $taskStatusDes = $ptDes['TASKSTATUSDES'][$d['task_status']];
        $productHostStatus = intval($productHost['status']);
        $standbyHostStatus = intval($standbyHost['status']);
        $syncFlag = false;  //数据备份状态(是否正在传输)

        //主机状态/备机状态/
        $hstatus = $productHostStatus;
        $rstatus = $standbyHostStatus;

        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_BACKUP_TASK_DETAILS'];
        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
            //如果从站离线,无法远程获取状态
            $startTime = '----';
            $intervalTime = '----';
            $syncFlag = false;
        } else {
            //开始时间和当前时间，计算出来持续时间
            $rpc = Xphp::instance('FileRPCHandler');
            $msgData = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productHost['ip'],
            );
            $result = $rpc->getTaskStaticInfos($standbyHost['ip'], $msgData);
            $data = $this->checkRPCMsg($operate, $result);
            $startTime = $data[0]['createtime'];

            $result = $rpc->getTime($standbyHost['ip'], array());
            $data = $this->checkRPCMsg($operate, $result);
            $currentTime = "";
            if (!empty($data)) {
                $currentTime = $data[0]['curtime'];
            }

            $intervalTime = strtotime($currentTime) - strtotime($startTime);
            $utils = Xphp::instance('Utils');
            $intervalTime = $utils->secToTime($intervalTime);

            $syncFlag = $this->getActiveBackupStatus($productHost['ip'], $standbyHost['ip']);

        }

        //只有运行状态才显示开始时间和持续时间
        if (!($taskStatus == Xphp::$_config['TASKSTATUS']['RUNNING'])) {
            $startTime = "----";
            $intervalTime = "----";
        }
        $info = array(
            're' => true,
            'productHostSatus' => $hstatus,
            'standbyHostSatus' => $rstatus,
            'taskStatus' => $taskStatus,
            'taskStatusDes' => $taskStatusDes,
            'startTime' => $startTime,
            'intervalTime' => $intervalTime,
            'syncFlag' => $syncFlag,
        );

        return json_encode($info, true);
    }

    /**
     * 得到实时备份任务是否正在传输数据中
     * @param unknown $productHostIP
     * @param unknown $standbyHostIP
     * @return bool   true/false  正在备份文件/正在监控等待备份
     */
    private function getActiveBackupStatus($productHostIP, $standbyHostIP)
    {
        $rpc = Xphp::instance('FileRPCHandler');

        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_IS_BACKUP_FILE'];
        //获取备份主机当前时间
        $result = $rpc->getTime($standbyHostIP, array());
        $data = $this->checkRPCMsg($operate, $result);
        $currentTime = "";
        if (!empty($data)) {
            $currentTime = $data[0]['curtime'];
        }

        //获取任务忙/闲状态
        $msgData = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHostIP,
        );
        $result = $rpc->getRemoteHostIpConnStatus($standbyHostIP, $msgData);
        $data = $this->checkRPCMsg($operate, $result);
        $commstatus = "";
        $commturntime = "";

        if (!empty($data)) {
            $commstatus = intval($data[0]['commstatus']);
            $commturntime = $data[0]['commturntime'];
        }

        $confStatus = Xphp::$_config['FILE_CDP_BACKUP_STATUS'];

        if ($confStatus['BUSY'] == $commstatus) {
            //正在备份文件
            return true;
        }

        if (!empty($commturntime)) {
            if ($commturntime == "----") {
                //第一次同步完成后,没有数据变化
                return false;
            }
            $intervalTime = abs(strtotime($currentTime) - strtotime($commturntime));
            if ($intervalTime < 6) {
                //如果最近一次备份在当前时间6秒内,返回正在备份
                return true;
            }
        }


        return false;
    }

    /**
     * 根据任务名(永思任务名)得到任务目录
     * @param array $fileInfo     任务配置详情
     * @param string $taskname     任务名
     */
    private function getTaskDirPath($fileInfo, $taskname)
    {
        $dir = "";
        foreach ($fileInfo as $file) {
            if ($file['taskname'] == $taskname) {
                $dir = $file['path'];
                break;
            }
        }
        return $dir;
    }

    /**
     * 启用或禁用对应主机的备份任务
     * @param unknown $productHostIP
     * @param unknown $standbyHostIP
     * @param unknown $flag             true 启用  false禁用
     */
    private function ControlBackupJob($productHostIP, $standbyHostIP, $flag)
    {
        $rpc = Xphp::instance('FileRPCHandler');
        $operate = Xphp::$_lang['WEB_FILE_CDP_SET_BACKUP_TASK_ENABLE_STATUS'];

        //获取所有备份任务
        $data = $this->getAllBackupJob($productHostIP, $standbyHostIP);

        $opResult = true;
        //修改备份任务为启动或禁用,这里直接修改连接主站信息启用禁用
        foreach ($data as $d) {
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productHostIP,
                'port' => 0,
                'username' => "admin",
                'password' => "admin",
                'disable' => $flag ? 0 : 1,
            );
            $result = $rpc->ChgConnectHost($standbyHostIP, $rpcMsg);
            $opResult = $opResult & $result['result'];
        }
        if (!$opResult) {
            return $this->checkRPCMsg($operate, $result);
        }
        return true;
    }

    /**
     * 停止实时备份任务
     * @param unknown $params
     */
    public function stopBackupJob($params)
    {
        //         var_dump($params);
        //         return;

        $productHostIP = $params['fscdp']['productip'];
        $standbyHostIP = $params['fscdp']['standbyip'];
        $productHostUUID = $params['fscdp']['productuuid'];
        $standbyHostUUID = $params['fscdp']['standbyuuid'];
        $taskuuid = $params['uuid'];

        $productHostInfo = $this->getHostInfoWithUUID($productHostUUID);
        $productHostStatus = intval($productHostInfo['status']);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostUUID);
        $standbyHostStatus = intval($standbyHostInfo['status']);

        //禁用备份任务
        $this->ControlBackupJob($productHostIP, $standbyHostIP, false);

        $operate = Xphp::$_lang['WEB_FILE_CDP_STOP_BACKUP_TASK'];
        if (Xphp::$_config['DB_CDP_HOST_STATUS']['OFFLINE'] == $standbyHostStatus) {
            //如果备机离线,直接更新任务状态
            $result = $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPED']);
            return $this->muOpResult($result, $operate);
        }
        $opResult = true;
        $dbCDP = Xphp::instance('DbCDPHandler');
        $rpc = Xphp::instance('FileRPCHandler');
        $hostStatus = $dbCDP->checkHostServiceStatus(Xphp::$_config['DB_CDP_SYSCODE']['filestandby'], $standbyHostIP);
        if (Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] === $hostStatus['bakStatus']) {
            //如果从站启动,才停止
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'hipstr' => $productHostIP,
            );
            $result = $rpc->stopRemoteHostIpConnect($standbyHostIP, $rpcMsg);

            //             var_dump($standbyHostIP, $rpcMsg, $result);
            if ($result['errorCode'] == -19015) {
                //如果没有找到任务,不管
                $result['result'] = true;
            }
            $opResult = $opResult && $result['result'];
        }

        if (Xphp::$_config['DB_CDP_HOST_STATUS']['ONLINE'] == $productHostStatus) {
            $hostStatus = $dbCDP->checkHostServiceStatus(Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'], $productHostIP);
            if (Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] === $hostStatus['bakStatus']) {
                //如果主站启动,并且没有其他实时备份任务运行,才停止
                $sql = "select count(bt.task_uuid) as total from bd_task bt, cdp_fs_task as cdt where
                bt.task_uuid = cdt.task_uuid and cdt.task_uuid != ? and bt.task_type = ? and
                cdt.product_host_uuid = ?";
                $sqlParams = array($taskuuid, Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP'], $productHostUUID);
                $data = $this->dbSelect($sql, $sqlParams);
                if ($data[0]['total'] == 0) {
                    //                     $opResult = $opResult && $this->stopBakSystem(Xphp::$_config['DB_CDP_SYSCODE']['producthost'], $productHostIP);
                }
            }

            //配置主站子系统启动模式为手动启动
            $data = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                'startmode' => 0
            );
            $rpc = Xphp::instance('DbRPCHandler');
            $rpc->setServiceStartMode($productHostIP, $data);
        }

        $sql = "select task_name, task_type, task_status from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        //         if(intval($data[0]['task_status']) == Xphp::$_config['TASKSTATUS']['STOPPING']){
        //             //如果任务在停止中的状态,本次停止为强制停止
        //             $result = $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPED']);
        //             return $this->muOpResult($result, $operate);
        //         }

        if ($opResult) {
            $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPING']);
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_STOP_TASK_SUCCESS');
        } else {
            $this->writeTaskLog($taskuuid, $data[0]['task_name'], $data[0]['task_type'], 'BD_TASKLOG_DESC_KEY_STOP_TASK_FAILURE');
        }

        return $this->muOpResult($opResult, $operate);
    }
    /**
     * 启动恢复任务
     * @param unknown $params
     */
    public function startRecoveryJob($params)
    {
        $productHostIP = $params['fscdp']['productip'];
        $standbyHostIP = $params['fscdp']['standbyip'];
        $productHostUUID = $params['fscdp']['productuuid'];
        $standbyHostUUID = $params['fscdp']['standbyuuid'];
        $taskuuid = $params['uuid'];

        $productHostInfo = $this->getHostInfoWithUUID($productHostUUID);
        $productHostStatus = intval($productHostInfo['status']);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostUUID);
        $standbyHostStatus = intval($standbyHostInfo['status']);

        //获取恢复文件列表
        $sql = "select config from cdp_fs_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $config = json_decode($data[0]['config'], true);

        $srcFileInfo = $config['srcFileInfo'];
        $distDir = $config['distDir'];

        $rpc = Xphp::instance('FileRPCHandler');
        $opResult = true;

        //启动主站进入恢复等待状态
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
            'port' => 7817,         //恢复默认端口，尽量不改
            'rstmm' => "admin",
            'rstdstdir' => $distDir,
        );
        $operate = Xphp::$_lang['WEB_FILE_CDP_START_RECOVERY_TASK'];

        $this->startRecSystem(Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'], $productHostIP, $distDir);

        //先释放一下从站的恢复任务
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHostIP,
            'port' => 7817,
        );
        $result = $rpc->freeRecoveryCliInfo($standbyHostIP, $rpcMsg);

        //         var_dump("freeRecoveryCliInfo", $result);
        //         $opResult = $opResult && $result['result'];//这里可能报错,第一次的时候,所以不检查返回错误

        foreach ($srcFileInfo as $file) {
            $thisDistDir = "";
            if ($file['isParent']) {
                //如果是目录,修改目标目录
                $thisDistDir = $distDir . "/" . $file['name'];
            } else {
                $thisDistDir = $distDir;
            }
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
                'dstipstr' => $productHostIP,
                'dstport' => 7817,      //恢复默认端口，尽量不改
                'username' => 'admin',
                'usermm' => 'admin',
                'rstmm' => 'admin',
                'dirid' => $file['isParent'] ? 1 : 0,
                'subid' => 1,
                'srcpath' => $file['path'],
                'dstpath' => $thisDistDir,
            );
            $result = $rpc->startRecoveryDirCli($standbyHostIP, $rpcMsg);

            //             var_dump("startRecoveryDirCli", $result);

            $opResult = $opResult && $result['result'];
            //             var_dump($standbyHostIP, $rpcMsg, $result);
        }

        if (!$opResult) {
            return $this->muOpResult($opResult, $operate, $result['errorMsg'], 'error', $result['errorCode']);
        } else {
            $updateResult = $this->updateTaskStatus($taskuuid, Xphp::$_config['TASKSTATUS']['STARTING']);
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
        $productHostIP = $params['fscdp']['productip'];
        $standbyHostIP = $params['fscdp']['standbyip'];
        $productHostUUID = $params['fscdp']['productuuid'];
        $standbyHostUUID = $params['fscdp']['standbyuuid'];
        $taskuuid = $params['uuid'];

        $productHostInfo = $this->getHostInfoWithUUID($productHostUUID);
        $productHostStatus = intval($productHostInfo['status']);
        $standbyHostInfo = $this->getHostInfoWithUUID($standbyHostUUID);
        $standbyHostStatus = intval($standbyHostInfo['status']);

        //获取恢复文件列表
        $sql = "select config from cdp_fs_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $config = json_decode($data[0]['config'], true);

        $srcFileInfo = $config['srcFileInfo'];
        $distDir = $config['distDir'];

        $operate = Xphp::$_lang['WEB_FILE_CDP_STOP_RECOVERY_TASK'];
        $rpc = Xphp::instance('FileRPCHandler');
        $opResult = true;

        //停止主站的恢复等待状态
        $this->stopRecSystem(Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'], $productHostIP);

        //停止从站恢复任务
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['filestandby'],
            'hipstr' => $productHostIP,
            'dstport' => 7817,
        );
        $result = $rpc->stopRecoveryCli($standbyHostIP, $rpcMsg);
        $opResult = $opResult && $result['result'];
        //         var_dump($result);

        if ($opResult) {
            $this->updateTaskStatus($params['uuid'], Xphp::$_config['TASKSTATUS']['STOPPED']);
        }

        return $this->muOpResult($result['result'], $operate);
    }

    /**
     * 得到主机的文件树
     * @param unknown $params
     */
    public function getHostFileTree($params)
    {
        $page = intval($params['page']);
        $limit = intval($params['limit']);
        $dir = $params['dir'];
        $hostuuid = $params['hostuuid'];
        $pid = $params['pid'];          //父节点ID
        $dirFlag = $params['dirFlag'];  //是否只展示目录,用于恢复
        $checked = $params['checked'];  //是否选中
        $this->paramsCheck($hostuuid);

        $hostinfo = $this->getHostInfoWithUUID($hostuuid);
        $detail = json_decode($hostinfo['detail'], true);
        $osType = $detail['osType'];


        $rpcMsg = array(
            'bpath' => $dir,
            'bmode' => $dirFlag ? 1 : 0,
            'bpageno' => $page + 1,
            'bpagenum' => $limit,
            'bmask' => '',
        );
        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_HOST_FILE_LIST'];
        $rpc = Xphp::instance('FileRPCHandler');
        $result = $rpc->getPath($hostinfo['ip'], $rpcMsg);
        $endFlag = $result['data']['nexitid'];  //0表示没有后续, 1表示还有后续
        $data = $this->checkRPCMsg($operate, $result);


        if (empty($dir)) {
            $path = "";
            if ($osType == Xphp::$_config['DB_CDP_OS_TYPE']['LINUX']) {
                //如果是Linux操作系统,将目录最前面加上/
                $path = $dir . "/";
            }

        } else {
            $path = $dir . "/";
        }

        $utils = Xphp::instance('Utils');
        $i = 0;
        $tree = array();
        foreach ($data as $d) {
            //             var_dump( $d);
            //             if($dirFlag && $d['type'] == "file"){
            //                 //如果只要目录
            //                 continue;
            //             }
            $node = array(
                "id" => $pid . "_" . $i++,
                "pid" => $pid,
                "name" => $d['name'],
                "title" => $path . $d['name'],
                "dir" => $path . $d['name'],
                "isParent" => $d['type'] == "file" ? false : true,
                "nocheck" => false,
                "type" => $d['type'],
                "dirFlag" => $dirFlag,
                "hostuuid" => $hostuuid,
                "size" => $d['size'],
                "sizedes" => $utils->calSize($d['size'], true),
                "chgtime" => substr($d['chgtime'], 0, 19),
                "checked" => !!$checked,
                "more" => false,
            );
            $tree[] = $node;
        }
        if ($endFlag) {
            //             如果还没有显示完全,添加显示更多项
            $more = array(
                "id" => $pid . "_" . $i++,
                "pid" => $pid,
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "dir" => $dir,
                "isParent" => false,
                "nocheck" => true,
                "more" => true,
                "type" => 1,
                "page" => $page + 1,
                "dirFlag" => $dirFlag,
                "hostuuid" => $hostuuid,
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
     * 获取之前选择的节点(文件,文件夹)树链,从根到节点,修改任务使用
     * @param unknown $params
     */
    public function getEachCheckNodeTreeChain($params)
    {
        $hostuuid = $params['hostuuid'];
        $rootNodes = $params['rootNodes'];
        $checkPath = $params['checkPath'];
        $limit = $params['limit'];
        $this->paramsCheck($hostuuid, $rootNodes, $checkPath, $limit);


        $hostinfo = $this->getHostInfoWithUUID($hostuuid);
        $page = 0;
        //         var_dump($hostuuid, $rootNodes, $checkPath, $limit);

        $rootNode = array();
        $thisDir = "";
        foreach ($rootNodes as $node) {
            if (0 === strpos($checkPath, $node['dir'])) {
                //找到目录的根路径
                $rootNode = $node;
                $thisDir = $node['dir'];
                break;
            }
        }
        $tree = array();
        $rootFlag = true;
        $tree = $this->recursiveGetDirTreeChain($hostinfo, $rootNode['id'], $checkPath, $thisDir, $page, $limit, $tree);

        $info = array(
            'nodeid' => $rootNode['id'],
            'nodes' => $tree
        );
        return json_encode($info);
    }

    /**
     * 递归获取目录全节点
     * @param unknown $hostinfo 主机信息
     * @param unknown $pid      父节点id
     * @param unknown $allDir   全路径
     * @param unknown $thisDir  当前遍历路径
     * @param unknown $page     第几页
     * @param unknown $tree     最后的树
     * @return string[]|unknown[]|boolean[]|number[]|NULL[]|mixed[]
     */
    private function recursiveGetDirTreeChain($hostinfo, $pid, $allDir, $thisDir, $page, $limit, &$tree)
    {
        //         var_dump($pid, $allDir, $thisDir);
        $rpcMsg = array(
            'bpath' => $thisDir,
            'bmode' => 0,
            'bpageno' => $page + 1,
            'bpagenum' => $limit,
            'bmask' => '',
        );
        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_HOST_FILE_LIST'];
        $rpc = Xphp::instance('FileRPCHandler');
        $result = $rpc->getPath($hostinfo['ip'], $rpcMsg);
        $endFlag = $result['data']['nexitid'];  //0表示没有后续, 1表示还有后续
        $data = $this->checkRPCMsg($operate, $result);

        //         var_dump($rpcMsg, $data);
        $i = 0;
        $path = $thisDir . "/";
        $findDirFlag = false;   //是否找到了目录
        $countData = count($data);
        foreach ($data as $d) {
            //             echo $path . $d['name'] . PHP_EOL;
            $id = md5($path . $d['name']);
            $node = array(
                "id" => $id,
                "pid" => $pid,
                "name" => $d['name'],
                "title" => $path . $d['name'],
                "dir" => $path . $d['name'],
                "isParent" => $d['type'] == "file" ? false : true,
                "nocheck" => false,
                "type" => $d['type'],
                "dirFlag" => false,
                "hostuuid" => $hostuuid,
                "chgtime" => substr($d['chgtime'], 0, 19),
                "checked" => false,
                "more" => false,
            );

            $i++;

            if (0 === strpos($allDir, $path . $d['name'])) {
                $findDirFlag = true;    //找到了上级/全目录
                //                 $node['halfCheck'] = true;
                if ($allDir == $path . $d['name']) {
                    //找到了自己,或者自己本来就是根
                    $node['checked'] = true;
                }
            }

            if ($allDir == $thisDir) {
                //自己本来就是根
                $node['checked'] = true;
            }

            $tree[] = $node;

            //如果找到了这个目录,继续请求下一层,如果没有找到,继续请求本层,到找到为止
            if (0 === strpos($allDir, $path . $d['name']) && $allDir != $path . $d['name']) {
                //                 var_dump($allDir, $path . $d['name']);
                //找到了,请求下一层
                $this->recursiveGetDirTreeChain($hostinfo, $id, $allDir, $path . $d['name'], 0, $limit, $tree);
            } else {
                //没有找到,继续请求本层
                if (!$findDirFlag && $countData == $i) {
                    $page++;
                    $this->recursiveGetDirTreeChain($hostinfo, $pid, $allDir, $thisDir, $page, $limit, $tree);
                }
            }
        }
        if ($endFlag && $findDirFlag) {
            //             如果还没有显示完全,添加显示更多项
            $more = array(
                "id" => $pid . "_more",
                "pid" => $pid,
                "name" => Xphp::$_lang['WEB_FILE_MORE'],
                "title" => Xphp::$_lang['WEB_FILE_MORE_TITLE'],
                "dir" => $thisDir,
                "isParent" => false,
                "nocheck" => true,
                "more" => true,
                "type" => 1,
                "page" => $page + 1,
                "dirFlag" => false,
                "hostuuid" => $hostinfo['host_uuid'],
                "more" => true,
            );
            $tree[] = $more;
        }

        return $tree;
    }

    /**
     * 得到防勒索病毒文件类型表
     * @param unknown $params
     */
    public function getRansomwareFileTypeTable($params)
    {
        $producthostuuid = $params['producthostuuid'];
        $this->paramsCheck($producthostuuid);

        $hostinfo = $this->getHostInfoWithUUID($producthostuuid);
        $rpc = Xphp::instance('FileRPCHandler');
        $operate = Xphp::$_lang['WEB_FILE_CDP_SCAN_DIR_FILE'];

        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
        );
        $result = $rpc->getFileTypes($hostinfo['ip'], $rpcMsg);
        $fileTypeDes = $this->checkRPCMsg($operate, $result);


        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
        );
        $result = $rpc->getAnalyDirFilesExtType($hostinfo['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($operate, $result);

        $list = array();
        foreach ($data as $d) {
            foreach ($d['data'] as $each) {
                $fexttype = $each['fexttype'];
                $list[$fexttype] = array(
                    'fexttype' => $fexttype,
                    'extname' => $each['extname'],
                    'data' => $this->getGroupFtype($list[$fexttype], $each['data'])
                );
            }
        }

        //         var_dump($fileTypeDes, $list);
        //         return;

        $records = array();
        $records["data"] = array();

        $i = 0;
        foreach ($list as $l) {
            $dataname = substr(md5($l['extname']), 8, 16);
            $data = array(
                '<input type="checkbox" name="id[]" data-name="' . $dataname . '" value="' . $i++ . '">',
                $l['extname'],
                $this->getFileTypeDes($fileTypeDes, $l['data']),
                $l,
                $dataname,
            );
            $records["data"][] = $data;
        }

        $records["draw"] = $params['draw'];
        return json_encode($records);
    }

    /**
     * 得到文件类型描述
     */
    private function getFileTypeDes($fileTypeDes, $filetypeArr)
    {
        $thisDes = "";
        foreach ($filetypeArr as $type) {
            foreach ($fileTypeDes as $des) {
                if ($type == $des['ftype']) {
                    if (empty($thisDes)) {
                        $thisDes .= $des['name'];
                    } else {
                        $thisDes .= "," . $des['name'];
                    }
                }
            }
        }
        return $thisDes;
    }

    /**
     * 组合ftype
     * @param unknown $listEach
     * @param unknown $ftype
     */
    private function getGroupFtype($listEach, $ftype)
    {
        $typeArr = array();
        foreach ($ftype as $f) {
            $typeArr[] = $f['ftype'];
        }

        if (!empty($listEach)) {
            $gourpArr = array_merge($listEach['data'], $typeArr);
            $gourpArr = array_unique($gourpArr);
            return $gourpArr;
        }
        return $typeArr;
    }

    /**
     * 开始扫描指定目录的文件类型
     * @param unknown $params
     */
    public function startScanHostFileType($params)
    {
        $producthostuuid = $params['producthostuuid'];
        $pathlist = $params['pathlist'];
        $this->paramsCheck($producthostuuid, $pathlist);

        $hostinfo = $this->getHostInfoWithUUID($producthostuuid);
        $rpc = Xphp::instance('FileRPCHandler');

        //         $filterList = array();
        $muResult = true;
        foreach ($pathlist as $value) {
            //过滤掉有父目录的子目录,选择了父目录后,子目录就不再需要了
            if (in_array(dirname($value), $pathlist)) {
                continue;
            }
            //             $filterList[] = $value;
            $rpcMsg = array(
                'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
                'dstpath' => $value,
            );
            $result = $rpc->analyDirFilesExtType($hostinfo['ip'], $rpcMsg);
            $muResult = $muResult && $result['result'];
        }

        return $this->muOpResult($muResult, Xphp::$_lang['WEB_FILE_CDP_SCAN_FILE'], Xphp::$_lang['WEB_FILE_CDP_SEND_SCAN_FILE_SUCCESS']);
    }

    /**
     * 停止扫描所有的文件/文件夹
     * @param unknown $params
     */
    public function stopScanHostFileType($params)
    {
        $producthostuuid = $params['producthostuuid'];
        $this->paramsCheck($producthostuuid);

        $hostinfo = $this->getHostInfoWithUUID($producthostuuid);
        $rpc = Xphp::instance('FileRPCHandler');
        $operate = Xphp::$_lang['WEB_FILE_CDP_STOP_SCAN_DIR'];

        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
        );
        $result = $rpc->stopAnalyDirFilesExtTypeTask($hostinfo['ip'], $rpcMsg);

        return $this->muOpResult(true, $operate);
    }


    /**
     * 获取扫描详情
     * @param unknown $params
     */
    public function getScanHostFileTypeInfo($params)
    {
        $producthostuuid = $params['producthostuuid'];
        $pathlist = $params['pathlist'];
        $this->paramsCheck($producthostuuid, $pathlist);

        $hostinfo = $this->getHostInfoWithUUID($producthostuuid);
        $rpc = Xphp::instance('FileRPCHandler');
        $operate = Xphp::$_lang['WEB_FILE_CDP_SCAN_DIR_FILE'];

        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
        );
        $result = $rpc->getAnalyDirFilesExtType($hostinfo['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($operate, $result);

        $statusArr = array();   //状态数组
        $hisDirArr = array();   //历史目录
        $fcount = 0;
        $dircount = 0;
        foreach ($data as $d) {
            $statusArr[] = $d['status'];
            $hisDirArr[] = $d['dir'];
            $fcount += $d['fcount'];
            $dircount += $d['dircount'];
        }
        //判断状态,如果有1就是正在扫描,如果没有数据,就是停止(没有扫描存在)

        if (in_array(1, $statusArr)) {
            $statusDes = Xphp::$_lang['UI_FILE_JOB_SCANNING'];
            $status = 2;
        } else {
            $statusDes = Xphp::$_lang['UI_FILE_JOB_SCAN_COMPLETED'];
            $status = 3;
        }

        if (count($statusArr) == 0) {
            $statusDes = Xphp::$_lang['WEB_PLATFORM_DES_STOP'];
            $status = 1;
        }


        $info = array(
            'fcount' => $fcount,
            'dircount' => $dircount,
            'hisdir' => $hisDirArr,
            'statusdes' => $statusDes,
            'status' => $status,
        );

        return json_encode($info);
    }

    /**
     * 得到防勒索病毒指定文件类型
     * @param unknown $pamras
     */
    public function getRansomwareFileTypeTree($params)
    {
        $productHostUUID = $params['productuuid'];
        $this->paramsCheck($productHostUUID);

        $productHostInfo = $this->getHostInfoWithUUID($productHostUUID);
        $rpcMsg = array(
            'syscode' => Xphp::$_config['DB_CDP_SYSCODE']['fileproduct'],
        );
        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_FILE_CODE_LIST'];
        $rpc = Xphp::instance('FileRPCHandler');
        $result = $rpc->getFileTypes($productHostInfo['ip'], $rpcMsg);
        $data = $this->checkRPCMsg($operate, $result);

        $tree = array();
        $i = 1;
        foreach ($data as $d) {
            $node = array(
                "id" => $i++,
                "pid" => 0,
                "name" => $d['name'],
                "title" => $d['name'],
                "isParent" => false,
                "nocheck" => false,
                "ftype" => $d['ftype'],
            );
            $tree[] = $node;
        }

        $info = array(
            're' => true,
            'tree' => $tree
        );

        return json_encode($info);
    }

    /**
     * 获取根文件(直接备份文件)的信息
     * @param unknown $params
     */
    public function getRootFileInfo($params)
    {
        $page = intval($params['page']);
        $limit = intval($params['limit']);
        $dir = $params['dir'];
        $hostuuid = $params['hostuuid'];
        $pid = $params['pid'];          //父节点ID
        $this->paramsCheck($hostuuid);

        $hostinfo = $this->getHostInfoWithUUID($hostuuid);

        $rpcMsg = array(
            'bpath' => $dir,
            'bmode' => 0,
            'bpageno' => $page + 1,
            'bpagenum' => $limit,
            'bmask' => '',
        );
        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_HOST_FILE_LIST'];
        $rpc = Xphp::instance('FileRPCHandler');
        $result = $rpc->getPath($hostinfo['ip'], $rpcMsg);
        $endFlag = $result['data']['nexitid'];  //0表示没有后续, 1表示还有后续
        $data = $this->checkRPCMsg($operate, $result);
    }

    /**
     * 得到恢复任务树
     * @param unknown $params
     */
    public function getRecoveryTaskTree($params)
    {
        $hostuuid = $params['hostuuid'];
        $pid = $params['pid'];
        $this->paramsCheck($hostuuid);


        $sql = "select bt.task_name, cft.task_uuid, cft.product_host_uuid,
                cft.standby_host_uuid, cft.config from bd_task bt, cdp_fs_task cft
                where bt.task_uuid = cft.task_uuid and cft.standby_host_uuid = ? and
                bt.task_type = ?";
        $data = $this->dbSelect($sql, array($hostuuid, Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP']));

        $productUUIDArr = array();
        foreach ($data as $d) {
            //添加源主机
            if (!in_array($d['product_host_uuid'], $productUUIDArr)) {
                $productInfo = $this->getHostInfoWithUUID($d['product_host_uuid']);
                $node = array(
                    "id" => $d['product_host_uuid'],
                    "pid" => 0,
                    "name" => $productInfo['host_name'] . "(" . $productInfo['ip'] . ")",
                    "title" => $productInfo['host_name'] . "(" . $productInfo['ip'] . ")",
                    "isParent" => true,
                    "nocheck" => true,
                    "type" => 1,
                    "icon" => './img/db/host.png',
                );
                $tree[] = $node;

                $productUUIDArr[] = $d['product_host_uuid'];
            }

            //添加任务
            $node = array(
                "id" => $d['task_uuid'],
                "pid" => $d['product_host_uuid'],
                "name" => $d['task_name'],
                "title" => $d['task_name'],
                "isParent" => true,
                "nocheck" => true,
                "type" => 2,
            );
            $tree[] = $node;

            $config = json_decode($d['config'], true);
            $backuptype = intval($config['standbyhostInfo']['backuptype']);

            //添加备份数据节点
            $backupNodeID = $d['task_uuid'] . "_0";
            $node = array(
                "id" => $backupNodeID,
                "pid" => $d['task_uuid'],
                "name" => Xphp::$_lang['UI_PLATFORM_VOL_CDP_BACKUPSET'],
                "title" => Xphp::$_lang['UI_PLATFORM_VOL_CDP_BACKUPSET'],
                "isParent" => true,
                "nocheck" => true,
                "type" => 3,

                "dir" => $config['standbyhostInfo']['backupdirparent'],
                "page" => 0,
                "limit" => 20,
                "hostuuid" => $d['standby_host_uuid'],
            );
            $tree[] = $node;

            if ($backuptype == 1) {
                //如果是实时备份+数据回退,添加历史数据
                $historyNodeID = $d['task_uuid'] . "_1";
                $node = array(
                    "id" => $historyNodeID,
                    "pid" => $d['task_uuid'],
                    "name" => Xphp::$_lang['WEB_FILE_CDP_HISTORY_DATA'],
                    "title" => Xphp::$_lang['WEB_FILE_CDP_HISTORY_DATA'],
                    "isParent" => true,
                    "nocheck" => true,
                    "type" => 3,

                    "dir" => $config['standbyhostInfo']['historydirparent'],
                    "page" => 0,
                    "limit" => 20,
                    "hostuuid" => $d['standby_host_uuid'],
                );
                $tree[] = $node;
            }
        }

        $info = array(
            're' => true,
            'tree' => $tree
        );

        return json_encode($info);
    }

    /**
     * 下载单个文件
     * @param unknown $params
     */
    public function downloadFile($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_filecdpdata_download");
        $filesize = $_GET['size'];
        $path = $_GET['path'];
        $hostuuid = $_GET['hostuuid'];
        $this->paramsCheck($path, $hostuuid);
        $filename = basename($path);
        $hostInfo = $this->getHostInfoWithUUID($hostuuid);

        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $filesize);
        Header("Content-Disposition: attachment; filename=" . $filename);

        $operate = Xphp::$_lang['WEB_FILE_CDP_DOWNLOAD_BACKUP_FILE'];
        $url = "http://" . $hostInfo['ip'] . ":7816/api/Base/down?filename=" . rawurlencode($path);
        $fp = fopen($url, "r");
        $buffer = Xphp::$_config['FILE_CDP_FILE_BLOCK_SIZE'];
        while (!feof($fp)) {
            echo fread($fp, $buffer);
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }
        fclose($fp);
    }

    /**
     * 得到备份数据管理初始树
     * @param unknown $params
     */
    public function getCDPFSTree($params)
    {
        //找到所有的任务的备份主机
        $sql = "select bt.task_uuid, bt.task_name, cdt.product_host_uuid, cdt.standby_host_uuid, cdt.config,
                cdh.host_name, cdh.ip, cdh.status
                from bd_task bt, cdp_fs_task cdt,cdp_db_host cdh
                where bt.task_uuid = cdt.task_uuid and cdt.standby_host_uuid = cdh.host_uuid
                    and cdh.user_uuid = ? and bt.task_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid'], Xphp::$_config['TASKTYPE']['FILE_CDP_BACKUP']));

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
                    "type" => 0,
                    "icon" => './img/db/host.png',
                    "isParent" => true,
                    "baksys" => $this->getHostIfBackupSys($d['ip']),
                );
                $hostuuid[] = $d['standby_host_uuid'];
            }
        }
        return json_encode($dbTree);
    }

    /**
     * 得到指定ip是否是备份系统
     * @param unknown $hostip
     */
    private function getHostIfBackupSys($hostip)
    {
        $sql = "select ip, node_uuid from bd_node ";
        $data = $this->dbSelect($sql, array());
        $flag = false;
        foreach ($data as $d) {
            $ipArr = explode(" ", $d['ip']);
            if (in_array($hostip, $ipArr)) {
                return true;
            }
        }
        return $flag;
    }

    /**
     * 得到文件CDP实时备份任务名
     * @param unknown $params
     */
    public function getBackupTaskName($params)
    {
        $taskName .= Xphp::$_lang['UI_PLATFORM_CDP_FILE_REALTIME_BACKUP'];
        return $this->getValidTaskName($taskName);
    }

    /**
     * 得到数据库恢复任务名
     * @param unknown $params
     */
    public function getRecoveryTaskName($params)
    {
        $taskName .= Xphp::$_lang['UI_PLATFORM_CDP_FILE_DATA_RECOVERY'];
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

        if ($backuptype == Xphp::$_config['FILE_CDP_BACKUP_TYPE']['REALTIME_BACKUP']) {
            //实时备份只检查备份目录
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
        $operate = Xphp::$_lang['WEB_FILE_CHECK_BACKUP_DIR'];



        if ($result['result']) {
            return $this->muOpResult(true, $operate);
        } else {
            return $this->muOpResult(false, $operate, $result['errorMsg'], 'warning', $result['errorCode']);
        }
    }

    /**
     * 启动主站进入恢复等待状态
     * @param unknown $syscode
     * @param unknown $hostIP
     */
    private function startRecSystem($syscode, $hostip, $distDir)
    {
        $resStatus = $this->getRecSystemStatus($syscode, $hostip);

        $rpc = Xphp::instance('FileRPCHandler');
        $operate = Xphp::$_lang['WEB_FILE_START_FILE_RECOVERY_SYSTEM'];
        //检查并启动恢复系统
        $i = 0;
        while ($resStatus !== Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] && $i < 30) {
            $rpcMsg = array(
                'syscode' => $syscode,
                'port' => 7817,         //恢复默认端口，尽量不改
                'rstmm' => "admin",
                'rstdstdir' => $distDir,
            );
            $result = $rpc->startRecoverySrv($hostip, $rpcMsg);
            sleep(1);
            $i++;
            $resStatus = $this->getRecSystemStatus($syscode, $hostip);
        }
        return true;
    }

    /**
     * 停止主站进入恢复等待状态
     * @param unknown $syscode
     * @param unknown $hostIP
     */
    private function stopRecSystem($syscode, $hostip)
    {
        $resStatus = $this->getRecSystemStatus($syscode, $hostip);

        $rpc = Xphp::instance('FileRPCHandler');
        $operate = Xphp::$_lang['WEB_FILE_STOP_FILE_RECOVERY_SYSTEM'];
        //检查并停止恢复系统
        $i = 0;
        while ($resStatus == Xphp::$_config['DB_CDP_HOST_START_STATUS']['STARTED'] && $i < 30) {
            $rpcMsg = array(
                'syscode' => $syscode,
                'port' => 7817,         //恢复默认端口，尽量不改
            );
            $result = $rpc->stopRecoverySrv($hostip, $rpcMsg);
            sleep(1);
            $i++;
            $resStatus = $this->getRecSystemStatus($syscode, $hostip);
        }
        return true;
    }

    /**
     * 得到恢复系统等待状态
     * @param unknown $syscode
     */
    private function getRecSystemStatus($syscode, $hostip)
    {
        $rpc = Xphp::instance('FileRPCHandler');
        $data = array(
            'syscode' => $syscode
        );
        $operate = Xphp::$_lang['WEB_FILE_CDP_GET_RECOVERY_SYSTEM_WAIT_STATUS'];
        $result = $rpc->getRecoverySrvInfo($hostip, $data);
        $data = $this->checkRPCMsg($operate, $result);
        return intval($data['status']);
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
        return $data[0]['mount_point'] . "/fscdp/" . $taskuuid;
    }

    /**
     * 统一检查RPC消息,如果失败直接返回并退出
     * @param unknown $result
     */
    private function checkRPCMsg($operate, $result)
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
     * 公共方法,根据主机IP地址获取主机信息
     * @param unknown $ip
     */
    private function getHostInfoWithIP($ip)
    {
        $sql = "select host_uuid, host_name, ip, status from cdp_db_host where ip = ?";
        $data = $this->dbSelect($sql, array($ip));
        return $data[0];
    }


    /***********************************other***********************************************/

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
            Xphp::$_config['MODULE_TYPE']['OEM_FSCDP'],
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