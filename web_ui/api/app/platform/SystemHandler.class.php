<?php
/*******************************************
 ** 系统管理处理类
 **
 ** @author       xiezhuowei@vinchin.com
 ** @date         2015-06-25 下午15:16:44
 ** @version      1.0.0
 ** @copyright    Copyright 2015 vinchin.com
 ********************************************/
class SystemHandler extends OPHandler
{
    private $opcodeHandler;
    private $nodeOpcodeHandler;

    function __construct()
    {
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('PFOpcodePrivate');
        $this->nodeOpcodeHandler = Xphp::instance('NodeOpcode');
    }

    /**
     * 获取系统指纹信息(验证)
     */
    public function getThumbprint()
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_authorization_module_download");
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        if ($result) {
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($msg));
            Header("Content-Disposition: attachment; filename=" . Xphp::$_config['LISENCE_INFO']['thumbprintFileName']);
            return $msg;
        } else {
            $operate = $this->opcodeHandler->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取系统指纹信息文件
     */
    public function getThumbprintFile()
    {
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_authorization_module_download");
        $msg = "";
        //深信服oem版本
        if (Xphp::$_config['SYSTEM_INFO']['enterprise'] == "sangfor_enterprise") {
            //获取SNcode
            $snCode = $this->getMachinSNCode();
            if ($snCode != "unknown") {
                //如果不是EDS,就不获取机器指纹
                $msg .= "SN:" . $snCode . PHP_EOL;
            }
        }

        $utils = Xphp::instance('Utils');
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg .= $mbResult['msg']['thumbprint'];

        //添加数据库实时,先判断是否有
        $cmdStr = "ps aux|grep lzbackupsys";
        exec($cmdStr, $info);
        if (count($info) > 2) {
            $dbCDPHandler = Xphp::instance('DbCDPHandler');
            $msg .= PHP_EOL . $dbCDPHandler->getEnvStr();
        }
        //添加数据库定时,先判断是否有
        $msg .= "\r\n" . date("Y-m-d H:i:s", $this->getSystemTime());
        $msg .= $this->getPlatformInfo();
        $msg .= "\r\n" . Xphp::$_config['SYSTEM_INFO']['enterprise'] . $this->getThumbprintEnterprise() . " " . Xphp::$_config['SYSTEM_INFO']['version'];
        if (count($info) > 2) {
            $database_msg .= " " . "t2-";

            //            $hostip = Xphp::$_config['DB_CDP_LICENSE_IP'];
            $hostip = $_SERVER['SERVER_ADDR'];
            $rpc = Xphp::instance('DbRPCHandler');
            //是否获取cdp授权信息
            $result = $rpc->getLicenseCenterAuthorInfo($hostip, array());
            if ($result['result']) {
                $cdpInfo = $this->getDBcdpLicenseInfo();
                $database_msg .= $cdpInfo['endtime'];
            }
            $msg .= "\r\n" . base64_encode($database_msg);
        }
        $msg .= "\r\n" . Xphp::$_config['SYSTEM_INFO']['company'];

        //添加定制版本信息
        $sql = "select settings_content from bd_system_settings where settings_type = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['SETTINGS_CONF']['CUSTOM_VERSION']));
        if (!empty($data)) {
            $version = $data[0]['settings_content'];
            $msg .= "\r\n" . Xphp::$_config['CUSTOM_VERSION'][$version];
        }


        if ($result) {
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($msg));
            Header("Content-Disposition: attachment; filename=" . Xphp::$_config['LISENCE_INFO']['thumbprintFileName']);
            return $msg;
        } else {
            $operate = $this->opcodeHandler->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 得到平台信息，现在有的平台
     * x86 centos7
     * arm centos7
     * arm kylin10
     */
    private function getPlatformInfo()
    {
        //因为目前x86就只有一个版本centos7，所以暂时x86返回信息为空，只在arm平台的时候返回对应的平台信息
        $platformInfo = "";
        if ("arm" == Xphp::$_config['SYSTEM_INFO']['arch_type']) {
            $platformInfo = "\r\n" . Xphp::$_config['SYSTEM_INFO']['arch_type'] . "_" . Xphp::$_config['SYSTEM_INFO']['os_type'];
        }
        return $platformInfo;
    }

    /**
     * 得到软件版本信息
     */
    private function getThumbprintEnterprise()
    {
        $des = "";
        $softType = $this->getSoftwareType();
        if (Xphp::$_config['SOFTWARE_VERSION']['FREE_EDITION'] == $softType) {
            //如果是免费版本
            $softType = " free edition ";
        }
        return $des;
    }

    /**
     * 上传LISENCE文件
     */
    public function uploadLisence()
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_authorization_module_upload");
        $this->checkUploadFile();
        $key = file_get_contents($_FILES['files']['tmp_name']);
        return $this->submitLisenceKey($key);
    }

    /**
     * 上传EDS LISENCE文件
     */
    public function uploadEDSLisence($params)
    {
        $edstype = $_GET['edstype'];
        $this->paramsCheck($edstype);
        $uploadfile = Xphp::$_config['LISENCE_INFO']['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        if ($files) {
            $this->checkUploadStatus($files['error']);
            $this->checkEDSFileName($files['name'], $uploadfile);
            $this->checkFIleSize($files['size'], $uploadfile);
        }


        $key = file_get_contents($_FILES['files']['tmp_name']);

        return $this->submitEDSLisenceKey($edstype, $key);
    }

    /**
     * 获取数据库CDP授权信息
     */
    private function getDBcdpLicenseInfo()
    {
        $dbCDPHandler = Xphp::instance('DbCDPHandler');
        $result = $dbCDPHandler->getLicenseCenterAuthorInfo();
        $totalArr = $result[0]['units'];
        $usedArr = $result[0]['alloced'];
        $info = array(
            'endtime' => "",
            'total' => array(
                'producthost' => 0,
                'standbyhost' => 0,
                'takeover' => 0,
                'fsproducthost' => 0,
                'fsstandbyhost' => 0,
            ),
            'used' => array(
                'producthost' => 0,
                'standbyhost' => 0,
                'takeover' => 0,
                'fsproducthost' => 0,
                'fsstandbyhost' => 0,
            )
        );
        if ($result && !empty($result)) {
            foreach ($totalArr as $total) {
                if ($total['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['producthost']) {
                    $info['total']['producthost'] = $total['authors'];
                }
                if ($total['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']) {
                    $info['total']['standbyhost'] = $total['authors'];
                }
                if ($total['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['takeover']) {
                    $info['total']['takeover'] = $total['authors'];
                }
                if ($total['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['fileproduct']) {
                    $info['total']['fsproducthost'] = $total['authors'];
                }
                if ($total['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['filestandby']) {
                    $info['total']['fsstandbyhost'] = $total['authors'];
                }
                $info['endtime'] = $total['etime'];
            }

            foreach ($usedArr as $used) {
                if ($used['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['producthost']) {
                    $info['used']['producthost'] = $info['used']['producthost'] + 1;
                }
                if ($used['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['standbyhost']) {
                    $info['used']['standbyhost'] = $info['used']['standbyhost'] + 1;
                }
                if ($used['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['takeover']) {
                    $info['used']['takeover'] = $info['used']['takeover'] + 1;
                }
                if ($used['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['fileproduct']) {
                    $info['used']['fsproducthost'] = $info['used']['fsproducthost'] + 1;
                }
                if ($used['syscode'] == Xphp::$_config['DB_CDP_SYSCODE']['filestandby']) {
                    $info['used']['fsstandbyhost'] = $info['used']['fsstandbyhost'] + 1;
                }

            }
        }
        return $info;
    }

    /**
     * 选择新的LOGO文件
     * @param unknown $params
     */
    public function selectLogo($params)
    {
        $uploadfile = Xphp::$_config['LOGO_INFO']['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        $operate = Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'];
        if ($files) {
            //检测上传文件状态
            if (0 != $files['error']) {
                exit($this->muOpResult(false, $operate));
            }
            //检测文件后缀名
            $arr = explode(".", $files['name']);
            $suffixesArr = explode("|", $uploadfile['suffixes']);
            if (!in_array($arr[count($arr) - 1], $suffixesArr)) {
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_LOGO_EXT'], 'error'));
            }
            //检测上传文件大小
            if ($files['size'] > $uploadfile['size']) {
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_LOGO_SIZE'], 'error'));
            }
            //保存临时文件
            $count = file_put_contents(Xphp::$_config['TMP_PATH_LOGO'], file_get_contents($files['tmp_name']));
            if ($count > 0) {
                return $this->muOpResult(true, $operate, '', 'success', 0, Xphp::$_config['TMP_PATH_RE_LOG']);
            }
            exit($this->muOpResult(false, $operate));
        } else {
            exit($this->muOpResult(false, $operate));
        }

    }

    /**
     * 保存上传的LOGO
     * @param unknown $params
     */
    public function setNewLogo($params)
    {
        $tmpFile = $params['file'];
        $this->paramsCheck($tmpFile);
        $cmd = "mv -f " . Xphp::$_config['TMP_PATH_LOGO'] . " " . Xphp::$_config['LOGO_PATH'];
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_LOGO');
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_SYSTEM_SET_DIY_LOGO']);
    }

    /**
     * 添加宿主机授权
     * @param unknown $params
     */
    public function addHostAuth($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_license");
        $hosts = $params['hosts'];
        $this->paramsCheck($hosts);
        $msg = array('host_list' => $hosts);
        $opName = "PT_LICENSE_OP_ADD_VM_LICENSE";
        $unifyMsg = $this->unifyMsg($opName, json_encode($msg));
        foreach ($hosts as $host) {
            $this->writeSystemLogHostAuth('BD_SYSTEMLOG_DESC_KEY_LISENCE_HOST', $unifyMsg, $host['vm_host_uuid'], $host['vcenter_uuid']);
        }
        return $unifyMsg;
    }

    /**
     * 取消宿主机授权
     * @param unknown $params
     */
    public function deleteHostAuth($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_license");
        $hosts = $params['hosts'];
        $this->paramsCheck($hosts);
        $msg = array('host_list' => $hosts);
        $opName = "PT_LICENSE_OP_DEL_VM_LICENSE";
        $unifyMsg = $this->unifyMsg($opName, json_encode($msg));
        foreach ($hosts as $host) {
            $this->writeSystemLogHostAuth('BD_SYSTEMLOG_DESC_KEY_LISENCE_UNHOST', $unifyMsg, $host['vm_host_uuid'], $host['vcenter_uuid']);
        }
        return $unifyMsg;
    }

    /**
     * 添加/取消宿主机授权
     * @param string $descriptionKey    日志键名
     * @param json $result              操作结果
     * @param string $hostuuid          宿主机UUID
     * @param string $vcenteruuid       vcenter uuid
     */
    private function writeSystemLogHostAuth($descriptionKey, $result, $hostuuid, $vcenteruuid)
    {
        $sql = "select host_name, host_ip from vm_host where vcenter_uuid = ? and host_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid, $hostuuid));
        $descriptionParam = array($data[0]['host_name'], $data[0]['host_ip']);
        $result = json_decode($result, true);
        if ($result['re']) {
            $this->systemLog($descriptionKey, $descriptionParam);
        } else {
            $this->systemLog($descriptionKey, $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
        }
    }

    /**
     * 得到宿主机表格信息
     */
    public function getHostInfo($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', 'vh.host_name', 'vh.host_ip', 'vv.vcenter_ip', 'vv.hypervisor_type', 'cpu_count', 'vh.authorization_flag', 'vh.authorization_flag');
        $draw = $params['draw'];
        $sql = "select vh.host_name, vh.host_uuid, vh.host_ip, vh.authorization_flag, vh.cpu_count,  
                vv.vcenter_ip, vv.hypervisor_type, vv.nickname, vv.vcenter_uuid   
                from vm_host vh, vm_vcenter vv 
                where vh.vcenter_uuid = vv.vcenter_uuid ";
        $sqlCount = "select count(vh.host_name) as total from vm_host vh, vm_vcenter vv 
                where vh.vcenter_uuid = vv.vcenter_uuid ";
        $sqlParams = array($start, $length);
        $sqlCountParams = array();
        if (Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']) {
            //如果是操作员
            $sql .= " and vv.user_uuid = ? ";
            $sqlCount .= " and vv.user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
            $sqlCountParams = array(Xphp::$_user['useruuid']);
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $systemAuthStatus = $this->getSystemAuthorizationStatus();
        $hostDivShowFlag = $this->getHostDivShowFlag();
        $records = array("data" => array());
        $i = 1;
        $op = array(1, 2);
        foreach ($data as $d) {
            $records["data"][] = array(
                $i++,
                $d['host_name'],
                $d['host_ip'],
                $d['vcenter_ip'] == $d['nickname'] ? $d['vcenter_ip'] : $d['nickname'] . "(" . $d['vcenter_ip'] . ")",
                Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])],
                $d['cpu_count'],
                $this->getAuthorizationStatusDes($d['authorization_flag']),
                $this->getOpcode($systemAuthStatus, $d['authorization_flag']),
                array(
                    'hostuuid' => $d['host_uuid'],
                    'hypervisor' => $d['hypervisor_type'],
                    'flag' => intval($d['authorization_flag']),
                    'vcenteruuid' => $d['vcenter_uuid'],
                    'showflag' => $hostDivShowFlag
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
     * 根据系统授权类型得到虚拟机授权标签是否显示
     */
    private function getHostDivShowFlag()
    {
        //如果是按照宿主机和CPU授权,就显示,否则,不显示
        $sql = "select license_type from bd_license";
        $data = $this->dbSelect($sql);
        if ($data) {
            $lisenceType = Xphp::$_config['LISENCE_INFO']['type'];
            if ($data[0]['license_type'] == $lisenceType['host'] || $data[0]['license_type'] == $lisenceType['cpu']) {
                return true;
            }
        }
        return false;
    }

    /**
     * 得到系统授权信息
     * (!!!!!注意)Vmhandler checkTaskLegal有调用
     */
    public function getSystemLisenceInfo()
    {
        $systemStatus = $this->getSystemAuthorizationStatus();
        $systemStatusDes = $this->getSystemAuthorizationDes($systemStatus);
        //调用新的获取授权信息方法
        return $this->getAuthorizedInfoV2(intval($systemStatus), $systemStatusDes);
    }

    /**
     * 得到虚拟机授权的基本信息
     * @param unknown $params
     */
    public function getVMLisenceInfo($params)
    {
        $systemLisence = json_decode($this->getSystemLisenceInfo(), true);
        return json_encode($systemLisence['vminfo']);
    }

    /**
     * 得到文件授权的基本信息
     * @param unknown $params
     * @return string
     */
    public function getFileLisenceInfo($params)
    {
        $fileInfo = $this->getOneModuleLisenceInfo('file');
        if (!empty($_SESSION['tenantuuid'])) {
            $fileInfo = $this->getTenantFsLisenceInfo();
        }
        return json_encode($fileInfo);
    }

    /**
     * 得到数据库备份授权的基本信息
     * @param unknown $params
     * @return string
     */
    public function getDBLisenceInfo($params)
    {
        $dbInfo = $this->getOneModuleLisenceInfo('database');
        if (!empty($_SESSION['tenantuuid'])) {
            $dbInfo = $this->getTenantDbLisenceInfo();
        }
        return json_encode($dbInfo);
    }

    /**
     * 得到已授权的信息
     * @param int $status       授权状态
     * @param string $statusDes 授权描述
     */
    private function getAuthorizedInfo($status, $statusDes)
    {
        $utils = Xphp::instance('Utils');
        $info = array(
            'status' => $status,
        );
        $sql = "select unix_timestamp(register_time) register_time, license_type, vm_max_num, cpu_count, storage_count, node_count, file_max_num, oracle_max_num,
                desktop_max, days, trial_type, software_type, user_name, extension from bd_license ";
        $data = $this->dbSelect($sql);
        $registerTime = $this->parseDate($data[0]['register_time']);
        $licenseType = intval($data[0]['license_type']);
        $vmMaxNum = intval($data[0]['vm_max_num']);
        $cpuMaxNum = intval($data[0]['cpu_count']);
        $storageMaxNum = intval($data[0]['storage_count']);
        $nodeMaxNum = intval($data[0]['node_count']);
        $fileMaxNum = intval($data[0]['file_max_num']);
        $dbMaxNum = intval($data[0]['oracle_max_num']);
        $v2vMaxNum = intval($data[0]['desktop_max']);
        $days = $data[0]['days'];
        $expireTime = "----";

        $authFlag = Xphp::$_config['LISENCE_INFO']['authflag'];
        if ($status == $authFlag['authorized']) {
            //已授权
            $trialType = intval($data[0]['trial_type']);
            if ($trialType == Xphp::$_config['TRIAL_TYPE']['TRIAL']) {
                $statusDes = Xphp::$_lang['WEB_SYSTEM_TRIAL_AUTHIORIZED'];
            } elseif ($trialType == Xphp::$_config['TRIAL_TYPE']['NORMAL']) {
                $statusDes = Xphp::$_lang['WEB_SYSTEM_FORMAL_AUTHIORIZED'];
            }

            //剩余天数 等于 授权天数 - 授权时间到当前时间的天数
            if ("-1" == $days) {
                $statusDes .= " (" . Xphp::$_lang['WEB_SYSTEM_AUTH_FOREVER'] . ")";
                $expireTime = Xphp::$_config['TIMESPACE'];
            } else {
                $dayInterval = round((time() - strtotime($registerTime)) / 3600 / 24);
                $expireDays = ($days - $dayInterval) <= 0 ? 0 : ($days - $dayInterval);
                $statusDes .= " (" . Xphp::$_lang['WEB_SYSTEM_AUTH_VALID'] . $expireDays . Xphp::$_lang['WEB_SYSTEM_AUTH_DAY'] . ")";
                //到期天数等于注册时间+授权天数转换成时间
                $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
                $expireTime = date("Y-m-d H:i:s", $endTimeStamp);
            }
        } elseif ($status == $authFlag['expire'] || $status == $authFlag['invalid']) {
            //到期天数等于注册时间+授权天数转换成时间
            $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
            $expireTime = date("Y-m-d H:i:s", $endTimeStamp);
        }



        $info['statusDes'] = $statusDes;
        $vmTypeName = array('', 'host', 'cpu', 'storage', 'vm');
        //虚拟机要根据授权类型得到使用量
        $info['vminfo'] = $this->getOneModuleLisenceInfo($vmTypeName[$licenseType], $licenseType);
        $info['vminfo'] = $this->filterModuleInfo($licenseType, $info['vminfo']);
        $v2vUsedNum = $this->getV2VUsedNum();
        $info['v2v'] = array(
            'total' => $v2vMaxNum,
            'used' => $v2vUsedNum,
            'valid' => $v2vMaxNum - $v2vUsedNum,
            'v2vdes' => Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $v2vUsedNum . "/" . $v2vMaxNum
        );
        //如果是不限制，修改描述
        if ("-1" == $v2vMaxNum) {
            $info['v2v']['v2vdes'] = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
        }
        $info['fileinfo'] = $this->getOneModuleLisenceInfo('file');
        $info['dbinfo'] = $this->getOneModuleLisenceInfo('database');
        $info['expireTime'] = $expireTime;
        $info['trial'] = intval($data[0]['trial_type']);
        $info['software'] = intval($data[0]['software_type']);
        if (empty($data)) {
            $info['software'] = $this->getSoftwareType();
        }
        $info['customer'] = empty($data[0]['user_name']) ? "----" : $data[0]['user_name'];

        $extension = $utils->decrypt($data[0]['extension']);
        $extension = json_decode($extension, true);
        $info['extension'] = $extension;
        $cmdStr = "ps aux|grep daserver";
        exec($cmdStr, $cmdinfo);
        $file = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . ($info['fileinfo']['total'] - $info['fileinfo']['valid']) . '/' . $info['fileinfo']['total'];
        $info['fileinfo']['filedes'] = $file;

        $db = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . ($info['dbinfo']['total'] - $info['dbinfo']['valid']) . '/' . $info['dbinfo']['total'];
        $info['dbinfo']['dbdes'] = $db;
        //获取 数据库定时信息
        if ($extension['dbtiminglic'] && $status == $authFlag['authorized']) {
            $dbTimingHandler = Xphp::instance('DBTimingHandler');
            if (!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime) {
                $dbTimingHandler->getDBAuth(); //获取datapp认证
            }
            $dbTimingInfo = $dbTimingHandler->getDBLisenceInfo(); //获取datapp授权信息
            if (!empty($dbTimingInfo['agent'])) {
                foreach ($dbTimingInfo['agent'] as $agent) {
                    $database_size += $agent['size'];
                    $database_free += $agent['free'];
                }
                if ($database_size != 0) {
                    $database = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . ($database_size - $database_free) . '/' . $database_size;
                    $info['dbtiming'] = array(
                        'database' => $database
                    );
                }
            }
        }
        $info['authfun'] = $_SESSION['authfun'];
        //获取数据库实时信息
        if ($extension['dbcdplic'] && $status == $authFlag['authorized']) {

            //            $hostip = Xphp::$_config['DB_CDP_LICENSE_IP'];
            $hostip = $_SERVER['SERVER_ADDR'];
            $rpc = Xphp::instance('DbRPCHandler');
            $data = array();
            //是否获取cdp授权信息
            $result = $rpc->getLicenseCenterAuthorInfo($hostip, $data);
            if ($result['result']) {
                $cdpInfo = $this->getDBcdpLicenseInfo();
                $producthost = Xphp::$_config['NULLSPACE'];
                $standbyhost = Xphp::$_config['NULLSPACE'];
                $fsproducthost = Xphp::$_config['NULLSPACE'];
                $fsstandbyhost = Xphp::$_config['NULLSPACE'];
                if ($cdpInfo['total']['producthost'] != 0) {
                    $producthost = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $cdpInfo['used']['producthost'] . '/' . $cdpInfo['total']['producthost'];
                }
                if ($cdpInfo['total']['standbyhost'] != 0) {
                    $standbyhost = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $cdpInfo['used']['standbyhost'] . '/' . $cdpInfo['total']['standbyhost'];
                }
                if ($cdpInfo['total']['takeover'] != 0) {
                    $takeover = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $cdpInfo['used']['takeover'] . '/' . $cdpInfo['total']['takeover'];
                }
                if ($cdpInfo['total']['fsproducthost'] != 0) {
                    $fsproducthost = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $cdpInfo['used']['fsproducthost'] . '/' . $cdpInfo['total']['fsproducthost'];
                }
                if ($cdpInfo['total']['fsstandbyhost'] != 0) {
                    $fsstandbyhost = Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $cdpInfo['used']['fsstandbyhost'] . '/' . $cdpInfo['total']['fsstandbyhost'];
                }
                $cdpendtime = $cdpInfo['endtime'];
                if (!empty($cdpendtime)) {
                    $info['cdp_auth'] = array(
                        'endtime' => $cdpendtime,
                        'producthost' => $producthost,
                        'standbyhost' => $standbyhost,
                        'takeover' => $takeover,
                        'fsproducthost' => $fsproducthost,
                        'fsstandbyhost' => $fsstandbyhost,
                    );
                }
            }
        }
        //服务信息
        $serverInfo = $this->getServiceLicense();
        $info['server_auth'] = array(
            'serverType' => Xphp::$_config['NULLSPACE'],
            'serverTime' => Xphp::$_config['NULLSPACE'],
            'serviceFlag' => false,
        );
        if (!empty($serverInfo)) {
            $info['server_auth'] = array(
                'serverType' => Xphp::$_config['LISENCE_INFO']['servertype'][$serverInfo['serviceType']],
                'serverTime' => $serverInfo['serviceTime'],
                'serviceFlag' => $serverInfo['serviceFlag'],
            );
        }
        //主控节点信息,主要用于国内展示用,合同里面会签
        $info['mastinfo'] = array(
            'mastflag' => true,
            'mastdes' => Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . '1',
        );

        //添加EDS容量授权标志
        $info['edslic'] = $this->getEDSLicenseFlag();
        $info['sncode'] = $this->getMachinSNCode();
        $info['edsShowFlag'] = true;	//是否显示EDS的内容


        //如果不显示标记文件存在，返回未授权，JS要用这个控制显示不显示
        $flagFilePath = Xphp::$_config['TMP_PATH'] . "noeds";
        if (file_exists($flagFilePath)) {
            $info['edsShowFlag'] = false;	//是否显示EDS的内容
        }

        return json_encode($info);
    }

    /**
     * 得到软件版本,
     * 1标准版,2企业版,3企业增强版,4免费版本
     * OEM版本暂时划归为企业版,企业增强版暂时没有
     */
    public function getSoftwareType()
    {
        $systemInfo = Xphp::$_config['SYSTEM_INFO'];
        $enterprise = Xphp::$_config['ENTERPRISE'];
        $sql = "select software_type from bd_license";
        $data = $this->dbSelect($sql, array());
        if (empty($data)) {
            //如果没有记录,就是还没有授权,需要获取配置文件的软件版本
            if ($systemInfo['enterprise'] == $enterprise['standard']) {
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['STANDARD'];
            } else if ($systemInfo['enterprise'] == $enterprise['enterprise']) {
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE'];
            } else if ($systemInfo['enterprise'] == $enterprise['enterprise_en']) {
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE_EN'];
            } else {
                //如果是其他OEM版本,默认为企业版
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE'];
            }
        } else {
            //有记录,获取数据库的记录
            $softwareVersion = intval($data[0]['software_type']);
        }
        return $softwareVersion;
    }

    /**
     * 得到软件是否是OEM版本,云祺版本暂时就只有三个,其他的都是OEM版本.
     * 界面会根据这个判断来做一些特别处理
     */
    public function getSoftwareIsOem()
    {
        $systemInfoEnterprise = Xphp::$_config['SYSTEM_INFO']['enterprise'];
        $enterprise = Xphp::$_config['ENTERPRISE'];
        $vinchinVersions = array(
            $enterprise['vinchin_standard'],
            $enterprise['vinchin_enterprise'],
            $enterprise['vinchin_advance_enterprise'],
        );
        return !in_array($systemInfoEnterprise, $vinchinVersions);
    }

    /**
     * 得到未授权/授权过期/授权异常信息
     * @param int $status       授权状态
     * @param string $statusDes 授权描述
     */
    private function getUnAuthorizadInfo($status, $statusDes)
    {
        $info = array(
            'status' => $status,
            'statusDes' => $statusDes,
        );
        $authFlag = Xphp::$_config['LISENCE_INFO']['authflag'];
        $tips = "";
        if ($status == $authFlag['unauthorized']) {
            //未授权
            $tips = Xphp::$_lang['WEB_SYSTEM_UNAUTHORIZED_TIP'];
        } elseif ($status == $authFlag['expire']) {
            //授权过期
            $tips = Xphp::$_lang['WEB_SYSTEM_EXPIRE_TIP'];
        } elseif ($status == $authFlag['invalid']) {
            //授权异常
            $tips = Xphp::$_lang['WEB_SYSTEM_INVALID_TIP'];
        }
        $info['tips'] = $tips;
        return json_encode($info);
    }

    /**
     * 得到授权状态描述
     * @param int $flag
     */
    private function getAuthorizationStatusDes($flag)
    {
        $des = Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'];
        if ($flag == Xphp::$_config['FLAG']['SET']) {
            $des = Xphp::$_lang['WEB_SYSTEM_AUTHIORIZED'];
        }
        return $des;
    }

    /**
     * 得到宿主机授权操作
     * @param int $flag   1 取消授权/2 添加授权
     */
    private function getOpcode($systemAuthStatus, $flag)
    {
        if ($systemAuthStatus == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            //系统已授权
            $flag = intval($flag);
            return array($flag);
        } else {
            //系统未授权
            return array(0);
        }
    }

    /**
     * 获取系统授权状态
     */
    public function getSystemAuthorizationStatus()
    {
        $sql = "select authorized_flag from bd_system ";
        $data = $this->dbSelect($sql);
        if ($data) {
            return intval($data[0]['authorized_flag']);
        }
        return 2;
    }

    /**
     * 得到v2v的使用量
     */
    public function getV2VUsedNum()
    {
        $sql = "select vc_lic_total_num from bd_system";
        $data = $this->dbSelect($sql);
        if ($data) {
            return intval($data[0]['vc_lic_total_num']);
        }
        return 0;
    }

    /**
     * 获取授权描述
     * @param int $status   授权状态标志
     * @return string
     */
    public function getSystemAuthorizationDes($status)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = $pfDes['LISENCE_STATUS_DES'][$status];
        return $des;
    }

    /**
     * 检测上传文件
     */
    private function checkUploadFile()
    {
        $uploadfile = Xphp::$_config['LISENCE_INFO']['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        if ($files) {
            $this->checkUploadStatus($files['error']);
            $this->checkFileName($files['name'], $uploadfile);
            $this->checkFIleSize($files['size'], $uploadfile);
            $this->checkUploadType($files['type'], $uploadfile);
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE']));
    }

    /**
     * 检测文件上传状态
     * @param int $error    文件上传相关的错误代码
     */
    private function checkUploadStatus($error)
    {
        if (0 == $error) {
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE']));
    }

    /**
     * 检测文件名字
     * @param string $name  客户端文件的原名称
     * @param array $conf   上传文件配置
     */
    private function checkFileName($name, $conf)
    {
        $arr = explode(".", $name);
        if ($arr[count($arr) - 1] == $conf['suffixes']) {
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE_TYPE_ERROR'], 'error'));
    }

    /**
     * 检测文件名字
     * @param string $name  客户端文件的原名称
     * @param array $conf   上传文件配置
     */
    private function checkEDSFileName($name, $conf)
    {
        $arr = explode(".", $name);
        if ($arr[count($arr) - 1] == "lic") {
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE_TYPE_LIC_ERROR'], 'error'));
    }

    /**
     * 检测上传升级包文件名字
     * @param string $name  客户端文件的原名称
     * @param array $conf   上传文件后缀
     */
    private function checkUpgradeFileName($name, $suffixes)
    {
        $typedes = substr($name, -6);   //tar.gz
        if ($typedes == $suffixes) {
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE_TYPE_TAR_GZ_ERROR'], 'error'));
    }

    /**
     * 检测文件大小
     * @param int $size 已上传文件的大小，单位为字节
     * @param array $conf   上传文件配置
     */
    private function checkFIleSize($size, $conf)
    {
        if ($size <= $conf['size']) {
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE_SIZE_ERROR'], 'error'));
    }

    /**
     * 检测上传文件的文件的 MIME类型
     * @param string $type
     * @param array $conf   上传文件配置
     */
    private function checkUploadType($type, $conf)
    {
        //浏览器有差异,暂时不检测
        return true;
        if ($type == $conf['type']) {
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE_ERROR'], 'error'));
    }

    /**
     * 处理服务授权文件
     * @param unknown $serviceLic
     */
    private function serviceLisenceKey($serviceLic, $operate)
    {
        //检查机器指纹,保存文件
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        if ($msg != $serviceLic['thumbprint'] && $serviceLic['thumbprint'] != "-1") {
            //机器指纹不一致
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_SH_FINGERPRINT_NO_SAME'], "warning"));
        }
        //加密存储服务授权信息到文件
        $utils = Xphp::instance('Utils');
        $serviceLic = $utils->encrype(json_encode($serviceLic, true));
        //配置文件保存到数据库
        $settingsHandler = Xphp::instance('SettingsHandler');
        //获取服务授权文件信息
        $data = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['SERVICE']);
        if (empty($data)) {
            //如果数据库没有
            $result = $settingsHandler->addSettingsInfos(Xphp::$_config['SETTINGS_CONF']['SERVICE'], $serviceLic);
        } else {
            $result = $settingsHandler->modifySettingsInfosWithType(Xphp::$_config['SETTINGS_CONF']['SERVICE'], $serviceLic);
        }
        return $result;
    }

    /**
     * 得到服务授权信息
     */
    private function getServiceLicense()
    {
        $fileInfo = file_get_contents(Xphp::$_config['LISENCE_INFO']['serviceFilePath']);
        if (!$fileInfo) {
            return false;
        }
        $utils = Xphp::instance('Utils');
        $data = $utils->decrypt($fileInfo);
        return json_decode($data, true);
    }

    /**
     * 得到extension license
     * 这个函数多处使用,请谨慎修改.
     */
    public function getExtensionLicense()
    {
        $utils = Xphp::instance('Utils');
        $sql = "select extension from bd_license";
        $data = $this->dbSelect($sql, array());
        $data = $utils->decrypt($data[0]['extension']);
        return json_decode($data, true);
    }

    /**
     * 提交授权码
     * @param unknown $key
     */
    private function submitLisenceKey($key)
    {
        $utils = Xphp::instance('Utils');
        $key = $utils->decrypt($key);
        $key = json_decode($key, true);
        //vinchin license,数据库定时license,数据库实时license
        $filetype = $key['filetype'];
        $vinchinLic = $key['v'];
        $dbtimingLic = $key['d'];
        $dbcdpLic = $key['c'];
        $serviceLic = $key['s'];
        $opName = 'PT_LICENSE_OP_ADD_LICENSE';
        $operate = $this->opcodeHandler->getOpcodeDes($opName);

        //如果只是上传的服务授权文件,直接到服务授权文件处理.
        if ($filetype == Xphp::$_config['LISENCE_INFO']['filetype']['service']) {
            $result = $this->serviceLisenceKey($serviceLic, $operate);
            if ($result) {
                return $this->muOpResult($result, $operate);
            } else {
                return $this->muOpResult($result, $operate, Xphp::$_lang['UI_SH_AUTHORIZATION_FAIL'], 'error');
            }
        }
        //提交数据库实时
        $cmdStr = "ps aux|grep lzbackupsys";
        exec($cmdStr, $info);
        if (count($info) > 2) {
            //授权数据库实时,先看是否有
            if (!empty($dbcdpLic)) {
                $dbCDPHandler = Xphp::instance('DbCDPHandler');
                $result = $dbCDPHandler->upLicenseFile($dbcdpLic);
                if (!$result['result']) {
                    //授权失败
                    return $this->muOpResult(false, $operate, $result['errorMsg'], 'warning', $result['errorCode']);
                }
            }
        }
        //提交数据库定时
        $cmdStr = "ps aux|grep daserver";
        exec($cmdStr, $info);
        $dbTimingHandler = Xphp::instance('DBTimingHandler');
        if (count($info) > 2) {
            if (!empty($dbtimingLic)) {
                if (!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime) {
                    $dbTimingHandler->getDBAuth(); //获取datapp认证
                }
                $result = $dbTimingHandler->uploadDBLicense($dbtimingLic); //授权datapp
                $result = json_decode($result, true);
                if (0 != $result['code']) {
                    //授权失败
                    return $this->muOpResult(false, $operate, $result['info'], 'warning', 60000 + $result['code']);
                }
            }
        }
        $msg = array('license' => $key['v']);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), false);
        $result = $mbResult['result'];

        $msg = $mbResult['msg'];



        //返回结果到UI
        if ($result) {
            $extension = $this->getExtensionLicense();
            if (!empty($extension['f'])) {
                session_start();
                $_SESSION['authfun'] = $extension['f'];
                session_commit();
            }
            $this->systemLog('PT_SYSTEMLOG_DESC_KEY_UPDATE_LICENSE');
            //系统授权成功后,处理服务授权
            $result = $this->serviceLisenceKey($serviceLic, $operate);
            if (!$result) {
                return $this->muOpResult($result, $operate, Xphp::$_lang['UI_SH_AUTHORIZATION_FAIL'], 'error');
            }
            $usersHandler = Xphp::instance('UsersHandler');
            $usersHandler->updateUserPermissionAndSoftwareType();
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 提交EDS授权码
     * @param unknown $key
     */
    private function submitEDSLisenceKey($type, $licenseStr)
    {
        $type = intval($type);

        //获取keyid,
        $licenseInfo = explode(PHP_EOL, $licenseStr);
        foreach ($licenseInfo as $key) {
            $strKeyid = strpos($key, "keyid");
            if (false !== $strKeyid) {
                //如果找到了keyid字符串,然后获取keyid的值
                $keyidArr = explode("=", $key);
                $keyid = trim($keyidArr[1]);
            }
        }

        if (empty($keyid)) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_ERROR_CAPACITY_AUTHORIZATION_FILE'], Xphp::$_lang['WEB_ERROR_CAPACITY_AUTHORIZATION_FILE_KEYID_ERROR'], 'error');
        }

        //根据不同情况判断
        if (1 == $type) {
            //如果是备份存储一体机:对比EDS授权文件中的keyid前10位是否和SN一致
            //获取机器指纹
            $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
            $mbResult = $this->mbPFMsg($opName, null, true);
            $result = $mbResult['result'];
            $thumbprint = $mbResult['msg']['thumbprint'];

            //将keyid转成指纹然后再比对
            $sncode = substr($keyid, 0, 10);
            $sncodeThumbprint = strrev(md5($sncode));

            if ($thumbprint != $sncodeThumbprint) {
                //如果比对不成功
                return $this->muOpResult(false, Xphp::$_lang['WEB_ERROR_CAPACITY_AUTHORIZATION_FILE'], Xphp::$_lang['WEB_ERROR_BACKUP_STORAGE_ALLINONE_FILE_ERROR'], 'error');
            }

        } elseif (2 == $type) {
            //如果是备份存储集群:判断keyid长度为16位，且后7位不连续为0
            $lengKeyid = strlen($keyid);
            $subKeyid = substr($keyid, -7);
            if (16 != $lengKeyid || "0000000" == $subKeyid) {
                return $this->muOpResult(false, Xphp::$_lang['WEB_ERROR_CAPACITY_AUTHORIZATION_FILE'], Xphp::$_lang['WEB_ERROR_BACKUP_STORAGE_CLUSTER_FILE_ERROR'], 'error');
            }

        }


        //保存授权文件
        $filePath = Xphp::$_config['TMP_PATH'] . "eds.lic";
        $result = file_put_contents($filePath, $licenseStr);


        if (false == $result) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_ERROR_CAPACITY_AUTHORIZATION_FILE'], Xphp::$_lang['WEB_ERROR_SAVE_AUTHORIZATION_FILE_ERROR'], 'error');
        }

        return $this->muOpResult(true, Xphp::$_lang['WEB_ERROR_CAPACITY_AUTHORIZATION_FILE']);
    }

    /**
     * 得到EDS授权标志,
     * 已授权  true
     * 未授权 false
     */
    public function getEDSLicenseFlag()
    {
        //文件存在即表示已经授权
        $filePath = Xphp::$_config['TMP_PATH'] . "eds.lic";
        return file_exists($filePath);
    }

    /**
     * 得到机器SN号
     */
    public function getMachinSNCode()
    {
        $nodeuuid = $this->getMasterNode();
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        //          因为深信服又采用了一款华勤的服务器,这个服务器没有定制       chassis_vendor,所以这里不做判断了
//         $cmd = "cat /sys/class/dmi/id/chassis_vendor";
//         $msg = array('command'=>$cmd);
//         $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
//         if(!$mbResult['result']){
//             return "";
//         }
//         $msgDetail = trim($mbResult['msg']['detail']);
//         //先判断是否是SANGFOR
//         if("SANGFOR" != $msgDetail){
//             //如果不是深信服,返回空
//             return "unknown";
//         }


        //如果是深信服,再获取board_serial
        $cmd = "cat /sys/class/dmi/id/board_serial";
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if (!$mbResult['result']) {
            return "";
        }
        $msgDetail = trim($mbResult['msg']['detail']);
        if (10 == strlen($msgDetail)) {
            //如果是10个字符,这个就是SN号
            return $msgDetail;
        }


        //如果不是10个字符,从board_asset_tag获取
        $cmd = "cat /sys/class/dmi/id/board_asset_tag";
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if (!$mbResult['result']) {
            return "";
        }
        $msgDetail = trim($mbResult['msg']['detail']);
        if (!empty($msgDetail)) {
            return $msgDetail;
        } else {
            return '';
        }
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param int $submodule_type
     * @param string $opName
     * @param json $msg
     * @param bool $sync 默认异步
     * @return string
     */
    private function unifyMsg($opName, $jsonMsg, $sync = false)
    {
        $mbResult = $this->mbPFMsg($opName, $jsonMsg, $sync);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 得到某个模块的授权信息
     * @param string $moduleName   模块名
     * @return array [total, used, valid]
     */
    public function getOneModuleLisenceInfo($moduleName)
    {
        $moduleInfo = array(
            'total' => 0,
            'used' => 0,
            'valid' => 0,
        );
        $moduleNames = array('file', 'vm', 'oracle', 'sqlserver', 'os', 'cdp', 'desktop', 'host', 'cpu', 'storage', 'node', 'database', 'nas', 'exchange', 'hadoop', 'obs');
        if (!in_array($moduleName, $moduleNames)) {
            //非法请求
            return $moduleInfo;
        }

        $total = $this->getModulesLisenceTotal();
        if ($total['authFlag'] != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            //未授权或授权异常
            return $moduleInfo;
        }
        $used = $this->getModulesLisenceUsed();
        $valid = $this->getModulesLisenceValid();
        $moduleInfo = array(
            'total' => intval($total['module'][$moduleName]),
            'used' => $used[$moduleName],
            'valid' => $valid[$moduleName],
        );
        return $moduleInfo;
    }

    /**
     * 如果是虚拟机授权,需要转换存储类型的格式,如1TB
     * @param int $licenseType
     * @param array $moduleInfo
     */
    private function filterModuleInfo($licenseType, $moduleInfo)
    {
        $moduleInfo['total_int'] = intval($moduleInfo['total']);
        $moduleInfo['used_int'] = intval($moduleInfo['used']);
        $moduleInfo['valid_int'] = intval($moduleInfo['valid']);
        if ($licenseType == Xphp::$_config['LISENCE_INFO']['type']['storage']) {
            $utils = Xphp::instance('Utils');
            $moduleInfo['total'] = $utils->calSize($moduleInfo['total'], true);
            $moduleInfo['used'] = $utils->calSize($moduleInfo['used'], true);
            $moduleInfo['valid'] = $utils->calSize($moduleInfo['valid'], true);
        }

        $moduleInfo['type'] = $licenseType;
        return $moduleInfo;
    }


    /**
     * 得到所有模块授权总数
     */
    public function getModulesLisenceTotal()
    {
        $authFlag = $this->getSystemAuthorizationStatus();
        $total = array(
            "authFlag" => $authFlag
        );
        if ($authFlag == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            //如果是已授权状态
            $sql = "select file_max_num, vm_max_num, mysql_max_num, oracle_max_num, sqlserver_max_num, os_max_num, nas_max_num, 
                    cdp_max, desktop_max, cpu_count, storage_count, node_count, exchange_user_max_num, hadoop_cluster_max_num, obs_max_num from bd_license";
            $data = $this->dbSelect($sql);
            foreach ($data as $d) {
                $total['module'] = array(
                    'file' => $d['file_max_num'],
                    'vm' => $d['vm_max_num'],
                    'mysql' => $d['mysql_max_num'],
                    'oracle' => $d['oracle_max_num'],
                    'sqlserver' => $d['sqlserver_max_num'],
                    'os' => $d['os_max_num'],
                    'cdp' => $d['cdp_max'],
                    'desktop' => $d['desktop_max'],
                    'host' => $d['vm_max_num'],     //根据类型和虚拟机共用
                    'cpu' => $d['cpu_count'],
                    'storage' => $d['storage_count'],
                    'node' => $d['node_count'],
                    'database' => $d['oracle_max_num'],
                    'nas' => $d['nas_max_num'],
                    'exchange' => $d['exchange_user_max_num'],
                    'hadoop' => $d['hadoop_cluster_max_num'],
                    'obs' => $d['obs_max_num'],
                );
            }
        }
        //租户内部授权总数
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Xphp::instance('TenantHandler');
            $config = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $total['module']['nas'] = $config['nas_num'];
            $total['module']['vm'] = $config['vm_num'];
            $total['module']['file'] = $config['file_num'];
            $total['module']['db'] = $config['db_num'];
            $total['module']['exchange'] = $config['m365_num'];
            $total['module']['hadoop'] = $config['hadoop_num'];
            $total['module']['obs'] = $config['obs_num'];
            $total['module']['os'] = $config['os_num'];
        }
        return $total;
    }


    /**
     * 得到所有模块使用总数
     */
    public function getModulesLisenceUsed()
    {
        $used = array(
            'file' => 0,
            'vm' => 0,
            'mysql_max_num' => 0,
            'oracle' => 0,
            'sqlserver' => 0,
            'os' => 0,
            'cdp' => 0,
            'desktop' => 0,
            'host' => 0,
            'cpu' => 0,
            'storage' => 0,
            'node' => 0,
            'database' => 0,
            'nas' => 0,
            'exchange' => 0,
            'hadoop' => 0,
            'obs' => 0,
        );

        $sql = "select authorization_module from bd_agent";
        $data = $this->dbSelect($sql);
        //nas已授权的单独从nas_storage_resource中计算
        $nasUsed = $this->getNasUsedInfo();
        $used['nas'] = $nasUsed;
        //exchange授权需要从时间点中计算
        $used['exchange'] = $this->getExchangeAuth();
        //hadoop授权需要从hadoop_cluster中计算
        $used['hadoop'] = $this->getHadoopAuth();
        //obs授权需要从obs_resource中计算
        $used['obs'] = $this->getObsAuth();
        foreach ($data as $d) {
            if (!empty($d['authorization_module'])) {
                $authInfo = json_decode($d['authorization_module'], true);
                $used['file'] += $authInfo['file'];
                $used['mysql'] += $authInfo['mysql'];
                $used['oracle'] += $authInfo['oracle'];
                $used['sqlserver'] += $authInfo['sqlserver'];
                $used['os'] += $authInfo['os'];
                $used['desktop'] += $authInfo['desktop'];
                $used['database'] += $authInfo['database'];
            }
        }
        //获取虚拟机模块的使用量
        $vmModuleUsed = $this->getVMModuleUsedArr();
        $used['vm'] = $vmModuleUsed['vm'];
        $used['cpu'] = $vmModuleUsed['cpu'];
        $used['host'] = $vmModuleUsed['host'];
        $used['storage'] = $vmModuleUsed['storage'];

        return $used;
    }

    /**
     * 获取exchange已使用用户数
     * @return int 已使用用户总数
     */
    private function getExchangeAuth()
    {
        $userNum = 0;
        //获取m365所有备份任务
        $sql = "select task_uuid from m365_task where m365_type = 1";
        $taskList = $this->dbSelect($sql);
        if (!empty($taskList)) {
            foreach ($taskList as $task) {
                //获取每个任务最新的时间点,解析出每个任务的最新时间点的授权用户数，然后相加
                $userNum += $this->getNewestTimepoit($task['task_uuid']);
            }
        }
        return $userNum;
    }

    /**
     * 获取hadoop已使用集群数
     * @return int 已使用集群数
     */
    private function getHadoopAuth()
    {
        $sql = "select distinct hadoop_cluster_uuid from hadoop_cluster where authorization = 1";
        $data = $this->dbSelect($sql);
        $count = count($data);
        return $count;
    }

    /**
     * 获取obs已使用授权数
     * @return int 已使用集群数
     */
    private function getObsAuth()
    {
        $sql = "select distinct obs_uuid from obs_resource where authorization = 1";
        $data = $this->dbSelect($sql);
        $count = count($data);
        return $count;
    }


    /**
     * 根据taskuuid获取授权用户数
     * @param string $taskUuid 任务uuid
     * @return int 用户数
     */
    private function getNewestTimepoit($taskUuid)
    {
        $sql = "select mbt.organization_info from m365_backup_timepoint mbt, bd_backup_timepoint bbt where bbt.available_flag = 1
        and bbt.timepoint_uuid = mbt.m365_timepoint_uuid and bbt.task_uuid = ? order by timepoint desc limit 0 , 1";
        $data = $this->dbSelect($sql, array($taskUuid));
        if (!empty($data)) {
            foreach ($data as $d) {
                $organizationInfo = json_decode($d['organization_info'], true);
                return intval($organizationInfo['current_reserverd_user_num']);
            }
        }
    }

    /**
     * 得到nas模块任务中的使用量
     */
    private function getNasTaskUsedInfo()
    {
        $sql = "select distinct fpl.agent_uuid as nas_uuid from fs_path_list fpl, bd_task bt where bt.task_uuid = fpl.task_uuid and bt.task_type = ? and bt.sub_module_type = ?";
        if (!in_array($_SESSION['userLevel'], [1, 2, 3])) {
            $resourceHandler = Xphp::instance('ResourceHandler');
            $uuid = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], 57);
            $uuidArr = array_column($uuid, 'resource_uuid');
            if (empty($uuidArr)) {
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $nasUuidsIn = "'" . implode("','", $uuidArr) . "'";
                $sql .= " and fpl.agent_uuid in ($nasUuidsIn)";
            }
        }
        $data = $this->dbSelect($sql, array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['SUBMODULE_TYPE']['NAS']));
        if (!empty($data)) {
            $nasUuids = array_column($data, 'nas_uuid');
            $nasDes = implode("','", $nasUuids);
            $sql = "SELECT COUNT(DISTINCT ip) AS total FROM nas_storage_resource WHERE nas_uuid IN ('" . $nasDes . "')";
            $data = $this->dbSelect($sql);
            return $data[0]['total'];
        }
        return 0;
    }

    /**
     * 得到nas模块任务中的使用量
     */
    private function getNasUsedInfo()
    {
        $sql = "select distinct ip from nas_storage_resource where authorization_status = 1";
        if (!empty($_SESSION['tenantuuid'])) {
            $resourceHandler = Xphp::instance('ResourceHandler');
            $nasUuids = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], 57);
            $nasUuids = array_column($nasUuids, 'resource_uuid');
            if (empty($nasUuids)) {
                return 0;
            } else {
                // $uuidArr 是一个一维数组
                $nasUuids = "'" . implode("','", $nasUuids) . "'";
                $sql .= " and nas_uuid in ($nasUuids)";
            }
        }
        return count($this->dbSelect($sql));
    }

    /**
     * 得到虚拟机模块的使用量
     */
    private function getVMModuleUsedArr()
    {
        $vmUsed = array(
            'vm' => 0,
            'host' => 0,
            'storage' => 0,
            'cpu' => 0,
        );
        $authFlag = $this->getSystemAuthorizationStatus();
        if ($authFlag != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            return $vmUsed;
        }
        $sql = "select license_type  from bd_license";
        $data = $this->dbSelect($sql);
        $licenseType = intval($data[0]['license_type']);
        $licenseTypeConf = Xphp::$_config['LISENCE_INFO']['type'];
        $sql = "";
        $sqlParams = array();
        $key = "";
        switch ($licenseType) {
            case $licenseTypeConf['host']:
                $sql = "select count(host_id) as total from vm_host where authorization_flag = ? ";
                $sqlParams = array(Xphp::$_config['FLAG']['SET']);
                $key = 'host';
                break;
            case $licenseTypeConf['cpu']:
                $sql = "select sum(cpu_count) as total from vm_host where authorization_flag = ? ";
                $sqlParams = array(Xphp::$_config['FLAG']['SET']);
                $key = 'cpu';
                break;
            case $licenseTypeConf['storage']:
                //                 $sql = "select sum(total_size) as total from bd_storage_resource where lan_free_flag = ?";
                $sql = "select sum(write_size) as total from bd_backup_timepoint where module_type != ?";
                $sqlParams = array(Xphp::$_config['MODULE_TYPE']['BACKUP_COPY_CLIENT']);
                $key = 'storage';
                break;
            case $licenseTypeConf['vm']:
                $sql = "select count(vml.vm_uuid) as total from vm_machine_list vml, bd_task bt 
                        where vml.task_uuid = bt.task_uuid and bt.task_type = ?";
                $sqlParams = array(Xphp::$_config['TASKTYPE']['BACKUP']);
                $key = 'vm';
                break;
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $data[0]['total'];
        if (empty($count))
            $count = 0;
        $vmUsed[$key] = $count;
        return $vmUsed;
    }

    /**
     * 得到所有模块可用总数
     */
    public function getModulesLisenceValid()
    {
        $valid = array(
            'file' => 0,
            'vm' => 0,
            'mysql' => 0,
            'oracle' => 0,
            'sqlserver' => 0,
            'os' => 0,
            'cdp' => 0,
            'desktop' => 0,
            'host' => 0,
            'cpu' => 0,
            'storage' => 0,
            'node' => 0,
            'database' => 0,
            'nas' => 0,
            'exchange' => 0,
            'hadoop' => 0,
            'obs' => 0,
        );
        $totalInfo = $this->getModulesLisenceTotal();
        if ($totalInfo['authFlag'] != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            return $valid;
        }
        $used = $this->getModulesLisenceUsed();
        $total = $totalInfo['module'];
        $valid = array(
            'file' => $total['file'] - $used['file'],
            'vm' => $total['vm'] - $used['vm'],
            'mysql' => $total['mysql'] - $used['mysql'],
            'oracle' => $total['oracle'] - $used['oracle'],
            'sqlserver' => $total['sqlserver'] - $used['sqlserver'],
            'os' => $total['os'] - $used['os'],
            'cdp' => $total['cdp'] - $used['cdp'],
            'desktop' => $total['desktop'] - $used['desktop'],
            'host' => $total['host'] - $used['host'],
            'cpu' => $total['cpu'] - $used['cpu'],
            'storage' => $total['storage'] - $used['storage'],
            'node' => $total['node'] - $used['node'],
            'database' => $total['database'] - $used['database'],
            'nas' => $total['nas'] - $used['nas'],
            'exchange' => $total['exchange'] - $used['exchange'],
            'hadoop' => $total['hadoop'] - $used['hadoop'],
            'obs' => $total['obs'] - $used['obs'],
        );

        return $valid;
    }

    /**
     * 检测模块是否授权
     * @param string $authModule   代理模块授权字段authorization_module
     * @param string $module       模块名
     * @return boolean
     */
    public function checkModuleValid($authModule, $module)
    {
        if (empty($authModule))
            return false;
        $authModule = json_decode($authModule, true);
        if ($authModule[$module]) {
            return true;
        }
        return false;
    }

    /**
     * 得到代理端信息
     * @param unknown $params
     * @return string
     */
    public function getAgentInfo($params)
    {
        $start = intval($params['start']);
        $length = $params['length'];
        $draw = $params['draw'];

        $sql = "select ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_type, ba.os_version, ba.process_type,
                		ba.register_time, ba.register_flag, ba.online_flag, ba.authorization_module,
                		bu.user_name, bu.user_uuid
                from bd_agent ba
                left join  bd_user bu
                on ba.user_uuid = bu.user_uuid
                where ba.register_flag = ? ";

        $sqlCount = "select count(ba.id) as total from bd_agent ba left join  bd_user bu on ba.user_uuid = bu.user_uuid
                    where ba.register_flag = ? ";

        $setFlag = Xphp::$_config['FLAG']['SET'];
        if (Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']) {
            $sql .= "and bu.user_uuid = ? ";
            $sqlCount .= "and bu.user_uuid = ? ";
            $sqlParams = array($setFlag, Xphp::$_user['useruuid'], $start, $length);
            $sqlCountParams = array($setFlag, Xphp::$_user['useruuid']);
        } else {
            $sqlParams = array($setFlag, $start, $length);
            $sqlCountParams = array($setFlag);
        }
        $sql .= " order by ba.id desc limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $agentHandler = Xphp::instance('AgentHandler');
        $records = array("data" => array());
        foreach ($data as $d) {
            $records["data"][] = array(
                $agentHandler->getAgentName($d['hostname'], $d['agent_name']),
                $d['ip'],
                $d['os_version'],
                $d['register_flag'] == Xphp::$_config['FLAG']['SET'] ? $d['register_time'] : "----",
                $agentHandler->getAgentModule($d['authorization_module']),
                $d['user_name'],
                $agentHandler->getOnlineDes($d['online_flag']),
                intval($d['online_flag']),
                array('uuid' => $d['agent_uuid'], 'authFlag' => $agentHandler->getAgentAuthorise($d['authorization_module'])),
            );
        }
        $records["draw"] = $params['draw'];
        ;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];

        return json_encode($records);
    }

    /**
     * 添加代理端授权操作
     * @param array $params
     * [hostName(string)主机名,agentUUID(string)代理端UUID,module(array)模块]
     */
    public function addAgentLisence($params)
    {
        $opName = "PT_LICENSE_OP_ADD_AGENT_LICENSE";
        return $this->addAndEditAgentLisence($opName, $params);
    }

    /**
     * 修改代理端授权操作
     * @param array $params
     * [hostName(string)主机名,agentUUID(string)代理端UUID,module(array)模块]
     */
    public function editAgentLisence($params)
    {
        $opName = "PT_LICENSE_OP_MODIFY_AGENT_LICENSE";
        return $this->addAndEditAgentLisence($opName, $params);
    }

    /**
     * 添加/修改代理端授权统一操作
     * 因为他们传到后台的参数是一样的O(∩_∩)O
     * @param string $opName
     * @param array $params   [hostName(string)主机名,agentUUID(string)代理端UUID,module(array)模块]
     */
    private function addAndEditAgentLisence($opName, $params)
    {
        $agentUUID = $params['agentUUID'];
        $hostName = $params['hostName'];
        $this->paramsCheck($agentUUID, $hostName);
        $agentHandler = Xphp::instance('AgentHandler');
        //msg里面的authorization_module是一个包含了所有模块授权信息的JSON字符串
        $msg = array(
            'agent_uuid' => $agentUUID,
            'agent_name' => $hostName,
            'authorization_module' => $agentHandler->getFullAuthorizationModule($params['module']),
        );
        return $this->unifyMsg($opName, json_encode($msg));
    }

    /**
     * 获取所有网卡名字
     * ManoeuvreHandler->getOrchProxyVMConfig有调用
     * @param unknown $params
     */
    public function getNetworkCardList($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $setIpFlag = $params['setipflag'];  //配置网卡信息入口标记
        $this->paramsCheck($nodeuuid);
        //获取网卡名
        $cmd = "ip link list | grep -v bridge | awk '{if ($1 ~ \"[0-9]*:\")print $2}'";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        $cardName = array();
        if (!$mbResult['result']) {
            return json_encode($cardName);
        }
        //获取消息成功
        $msgDetail = $mbResult['msg']['detail'];
        $cardNameArr = explode(":" . PHP_EOL, $msgDetail);

        //获取mac
        $cmd = "ip link list | grep link|awk '{print $2}'";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if (!$mbResult['result']) {
            return json_encode($cardName);
        }
        $cardHwAddrArr = explode(PHP_EOL, $mbResult['msg']['detail']);

        //获取网卡聚合信息
        $settingsHandler = Xphp::instance('SettingsHandler');
        $data = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['NIC']);
        $nicConfig = json_decode($data[0]['settings_content'], true);
        $info = array();
        $nicList = $nicConfig['niclist'];
        if (!empty($nicList[$nodeuuid])) {
            $nicInfo = $nicList[$nodeuuid];
            $oldList = explode(",", $nicInfo['cardList']);
            $bondName = $nicInfo['bondname'];
        } else {
            $oldList = [];
            $bondName = '';
        }

        foreach ($cardNameArr as $key => $value) {
            if ("lo" != $value && !empty($value)) {
                $name = $value;
                if (!empty($oldList) && in_array($value, $oldList) && !empty($bondName)) {
                    $name .= "(" . $bondName . ")";
                }
                //添加了网卡聚合的网卡不在单独配置网卡信息
                if ($setIpFlag && in_array($value, $oldList))
                    continue;
                $cardName[] = array(
                    'name' => $name,
                    'value' => $value,
                    'hwaddr' => $cardHwAddrArr[$key]
                );
            }
        }

        return json_encode($cardName);
    }

    /**
     * 检查网卡ip是否存在
     * @param string $params [ip,node_uuid,network_name]
     * @return string
     */
    public function ipaddrAvailable($params)
    {
        $ip = $params['ip'];
        $sql = "select ip,node_uuid,network_name from bd_node_network where ip = ?";
        $sqlParams = array($ip);

        $address = parent::dbSelect($sql, $sqlParams);
        $return = false;
        if (count($address) == 1 && $address[0]['node_uuid'] == $params['node_uuid'] && $address[0]['network_name'] == $params['network_name']) {
            $return = true;
        }
        if (empty($address)) {
            $return = true;
        }
        return json_encode($return);
    }

    /**
     * 统一写系统日志
     * @param boolean $result
     * @param string $descriptionKey
     * @param array $descriptionParam
     */
    private function unifyWriteSystemLog($result, $descriptionKey, $descriptionParam = array())
    {
        if ($result) {
            $this->systemLog($descriptionKey, $descriptionParam);
        } else {
            $this->systemLog($descriptionKey, $descriptionParam, Xphp::$_config['LOGLEVEL']['WARN']);
        }
    }

    /**
     * 检测任务状态
     * 有运行中的任务和瞬时恢复的任务时返回失败
     */
    private function checkTaskStatus($operate)
    {
        $sql = "select id from bd_task where task_status = ?  and delete_flag = ?";
        $sqlParams = array(Xphp::$_config['TASKSTATUS']['RUNNING'], Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_SETTING_IP_HAS_TASK'], 'warning'));
        }
        // 卷cdp的成功状态任务也不能修改
        $sql = "select id from bd_task where module_type = ? and task_status = ?  and delete_flag = ?";
        $sqlParams = array(Xphp::$_config['MODULE_TYPE']['VOL_CDP'], Xphp::$_config['TASKSTATUS']['SUCCESSED'], Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_SETTING_IP_HAS_TASK'], 'warning'));
        }
        return true;
    }

    /**
     * 重启后台服务
     * @return boolean
     */
    private function restartService($nodeuuid, $extraCMD = "")
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_tools");
        $cmd = "";
        if (!empty($extraCMD)) {
            $cmd .= $extraCMD;
            $cmd .= "sleep 30s;";
        }
        $this->restartDbcdpServer($nodeuuid);
        $cmd .= "systemctl restart mariadb;" .
            "systemctl restart rt_server;" .
            "systemctl restart pt_server;" .
            "systemctl restart progress_server;" .
            "systemctl restart appliance_server;" .
            "systemctl restart log_server;" .
            "systemctl restart fs_server;" .
            "systemctl restart rdm_server;" .
            "systemctl restart backup_copy_client;" .
            "systemctl restart backup_copy_server;" .
            "systemctl restart vm_server;" .
            "systemctl restart db_server;" .
            "systemctl restart node_server;" .
            "systemctl restart os_server;" .
            "systemctl restart agentlink_server;" .
            "systemctl restart volcdp_server;" .
            "systemctl restart network;";
        $opName = 'NODE_SYS_OP_DO_CMD';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
        return $mbResult;
    }

    /**
     * 获取所有时区配置文件
     * @param unknown $parmas
     */
    public function getAllTimezone($parmas)
    {
        $cmd = "timedatectl list-timezones";
        exec($cmd, $data);
        return json_encode($data);
    }

    /**
     * 获取系统默认的时区和时间
     * @param unknown $params
     */
    public function getDefaultTimeInfo($params)
    {
        $timeInfo = array();
        $cmd = "timedatectl |grep Timezone|awk '{print $2}'";
        exec($cmd, $data);
        if (!$data[0]) {
            $cmd = "timedatectl |grep Time\\ zone|awk '{print $3}'";
            exec($cmd, $data);
        }
        $timeInfo['timezone'] = $data[0];
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        $timeInfo['date'] = $data[1];
        $timeInfo['authFlag'] = $this->getSystemAuthorizationStatus();


        $ntpConf = array(
            "ntpFlag" => false,
            "ntpServers" => array(
                'time.nist.gov',
                'time-nw.nist.gov',
                'time-a.nist.gov',
                'time-b.nist.gov',
            )
        );

        $settingsHandler = Xphp::instance('SettingsHandler');
        $ntpConf = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['NTP']);
        if (!empty($ntpConf)) {
            $ntpConf = json_decode($ntpConf[0]['settings_content'], true);
        }
        $timeInfo['ntp'] = $ntpConf;

        return json_encode($timeInfo);
    }

    /**
     * 获取系统时间
     * @param unknown $params
     */
    public function getSystemTime()
    {
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        return strtotime($data[0]);
    }

    /**
     * 获取系统时间
     * @param unknown $params
     */
    public function getSystemTimeJSFormat($params)
    {
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        return date("Y/m/d H:i:s", strtotime($data[0]));
    }

    /**
     * 配置NTP服务器
     * NTP配置信息存储在主节点,其他节点只有NTP服务
     * 从主节点读取配置,然后同步包括主节点的所有节点
     * @param string $ntphost
     */
    private function setNtpServer($ntphost)
    {
        $this->paramsCheck($ntphost);
        $operate = Xphp::$_lang['WEB_SYSTEM_SETTING_TIME'];

        $settingsHandler = Xphp::instance('SettingsHandler');
        $ntpConf = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['NTP']);
        $ntpConf = json_decode($ntpConf[0]['settings_content'], true);

        $oldNtpServer = $ntpConf['ntpServers'][0];
        //修改NTP服务器配置文件
        $confFile = Xphp::$_config['NTP_SERVERS_FILE'];
        $conf = file_get_contents($confFile);
        $conf = str_replace($oldNtpServer, $ntphost, $conf);
        $cmd = "echo '" . $conf . "' > " . $confFile;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);

        //同步所有备份节点
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodes = $nodeHandler->getAddStorageNodeSelect();
        $nodes = json_decode($nodes, true);
        //必须先修改子节点,再修改主节点,不然服务重启的时候web请求会失败
        $utils = Xphp::instance('Utils');
        $nodes = $utils->arraySort($nodes, 'type', 'desc', 0, -1);
        foreach ($nodes as $node) {
            //关闭chrony时间同步服务器
            $cmd = "systemctl disable chronyd;";
            $cmd .= "systemctl stop chronyd;";

            //停止NTP服务,手动同步的时候需要停止,不然要报错.
            $cmd .= "systemctl stop ntpd;";
            //手动从时间服务器同步
            $cmd .= "ntpdate " . $ntphost . ";";
            //开启NTP时间配置NTP enabled
            $cmd .= "timedatectl set-ntp yes;";
            //设置开机启动NTP服务
            $cmd .= "systemctl enable ntpd;";
            //重启NTP服务器
            $cmd .= "systemctl restart ntpd;";

            // 因为 timedatectl set-ntp yes; 命令会使 chronyd 服务变为enable
            $cmd .= "systemctl disable chronyd;";

            $opName = 'NODE_SYS_OP_DO_CMD';
            $msg = array('command' => $cmd);
            $mbResult = $this->mbNodeMsg($opName, $node['uuid'], json_encode($msg), true, false);

            if (!$mbResult['result']) {
                $msg = sprintf(Xphp::$_lang['UI_PLATFORM_BAKNODE_SUNC_NTR_FAIL'], $node['text']);
                return $this->muOpResult(false, $operate, $msg, "warning");
            }

            //重启服务,连接要断开,无法判断,不管结果
            $this->restartService($node['uuid']);
        }

        //写入新的NTP配置文件(备份系统显示用),配置存储在主节点,子节点没有配置信息
        if (in_array($ntphost, $ntpConf['ntpServers'])) {
            //如果在列表中,提前到第一个,系统默认为第一个为设置项
            $key = array_search($ntphost, $ntpConf['ntpServers']);
            array_splice($ntpConf['ntpServers'], $key, 1);
        }
        array_unshift($ntpConf['ntpServers'], $ntphost);
        $ntpConf['ntpFlag'] = true;
        $ntpConf = json_encode($ntpConf);

        $settingsHandler = Xphp::instance('SettingsHandler');
        $result = $settingsHandler->modifySettingsInfosWithType(Xphp::$_config['SETTINGS_CONF']['NTP'], $ntpConf);
        $flag = false;
        if ($result) {
            $flag = true;
            $this->unifyWriteSystemLog(true, 'SYSTEM_SETTING_TIME');
        }
        return $this->muOpResult($flag, $operate);
    }

    /**
     * 获取系统通知默认配置
     * @param unknown $params
     */
    public function getDefaultNoticeConf($params)
    {
        $utils = Xphp::instance('Utils');
        $sql = "select email_notice_flag, system_notice_flag, system_notice_level, task_notice_flag, 
                task_notice_level, report_config, receive_email, smtp_config, verify_report_flag, verify_report_level  from bd_email_notice where email_notice_type = 1 ";
        $data = $this->dbSelect($sql);

        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        if (empty($smtpConfig['pass'])) {
            $pass = "";
        } else {
            $pass = $utils->decrypt($smtpConfig['pass']);
            $pass = md5($pass);
        }
        $emailConf = array(
            'emailFlag' => $utils->parseFlagToBool($data[0]['email_notice_flag']),
            'systemFlag' => $utils->parseFlagToBool($data[0]['system_notice_flag']),
            'systemLevel' => explode(',', $data[0]['system_notice_level']),
            'taskFlag' => $utils->parseFlagToBool($data[0]['task_notice_flag']),
            'taskLevel' => explode(',', $data[0]['task_notice_level']),
            'reportConf' => json_decode($data[0]['report_config'], true),
            'receEmail' => json_decode($data[0]['receive_email'], true),
            'host' => $smtpConfig['host'],
            'port' => $smtpConfig['port'],
            'user' => $smtpConfig['email'],
            'encryption' => $smtpConfig['encryption'],
            'pass' => $pass,
            'verifyFlag' => $utils->parseFlagToBool($data[0]['verify_report_flag']),
            'verifyLevel' => explode(',', $data[0]['verify_report_level']),
        );

        $sql = "select sms_notice_flag, sms_quantity, system_notice_flag, system_notice_level, task_notice_flag, 
                task_notice_level, sms_send_type, sms_device, sms_device_config from bd_sms_notice";
        $data = $this->dbSelect($sql);
        $deviceConfig = json_decode($data[0]['sms_device_config'], true);
        $deviceConfig['pass'] = base64_encode(Xphp::instance('Utils', 'decrypt', $deviceConfig['pass']));
        $smsConf = array(
            'smsFlag' => $utils->parseFlagToBool($data[0]['sms_notice_flag']),
            'systemFlag' => $utils->parseFlagToBool($data[0]['system_notice_flag']),
            'systemLevel' => explode(',', $data[0]['system_notice_level']),
            'taskFlag' => $utils->parseFlagToBool($data[0]['task_notice_flag']),
            'taskLevel' => explode(',', $data[0]['task_notice_level']),
            'quantity' => $data[0]['sms_quantity'],

            'sendType' => intval($data[0]['sms_send_type']),
            'device' => $data[0]['sms_device'],
            'deviceConfig' => $deviceConfig
        );

        $sql = "select email, telephone from bd_user where user_uuid = ?";
        $sqlParams = array(Xphp::$_user['useruuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        $userConf = array();
        if ($data) {
            $userConf['userEmail'] = empty($data[0]['email']) ? Xphp::$_lang['WEB_SYSTEM_SETTING_NO_SET'] : $data[0]['email'];
            $userConf['userEmailFlag'] = !empty($data[0]['email']);
            $userConf['userPhone'] = empty($data[0]['telephone']) ? Xphp::$_lang['WEB_SYSTEM_SETTING_NO_SET'] : $data[0]['telephone'];
            $userConf['userPhoneFlag'] = !empty($data[0]['telephone']);
        }

        //---检查更新
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 5";
        $resultupdate = $this->dbSelect($sqlupdate);
        $infoupdate = array();
        if (!empty($resultupdate)) {
            $contentupdate = json_decode($resultupdate[0]['settings_content'], true);
            $infoupdate = array(
                'checkflag' => $contentupdate['checkflag'],
                'TCPflag' => $contentupdate['TCPflag'],
                'timeperiod' => $contentupdate['timeperiod'],
            );
        }

        // 获取微信公众号的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 10 的默认值
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sqlupdate);
        $wechatContent = [];
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
        }

        $wechatConf = array(
            'wechatFlag' => $wechatContent['wechatFlag'] ? $utils->parseFlagToBool($wechatContent['wechatFlag']) : false,
            'systemFlag' => $wechatContent['systemFlag'] ? $utils->parseFlagToBool($wechatContent['systemFlag']) : false,
            'systemLevel' => explode(',', $wechatContent['systemLevel'] ?? ''),
            'taskFlag' => $wechatContent['taskFlag'] ? $utils->parseFlagToBool($wechatContent['taskFlag']) : false,
            'taskLevel' => explode(',', $wechatContent['taskLevel'] ?? ''),
            'deviceConfig' => [
                'gh_id' => $wechatContent['gh_id'] ?? Xphp::$_config['WECHAT_CONFIG']['gh_id'],
                'appid' => $wechatContent['appid'] ?? Xphp::$_config['WECHAT_CONFIG']['appid'],
                'wechat_mode' => $wechatContent['wechat_mode'] ?? Xphp::$_config['WECHAT_CONFIG']['wechat_mode'], // 默认是系统出厂模式
                'appsecret' => $wechatContent['appsecret'] ?? Xphp::$_config['WECHAT_CONFIG']['appsecret'],
                'template_id' => $wechatContent['template_id'] ?? Xphp::$_config['WECHAT_CONFIG']['template_id'],
                'template_param1' => $wechatContent['template_param1'] ?? Xphp::$_config['WECHAT_CONFIG']['template_param1'],
                'template_param2' => $wechatContent['template_param2'] ?? Xphp::$_config['WECHAT_CONFIG']['template_param2'],
                'template_url' => $wechatContent['template_url'] ?? '',
                // 授权二维码地址
                'auth_qrcode' => '',
                // 微信二维码地址
                'wechat_qrcode' => '',
            ],
        );

        $url = $wechatContent['template_url'] ?? $utils->get_current_url();

        $qr_param = $utils->xphp_encrpt(json_encode(
            [
                'userUuid' => Xphp::$_user['useruuid'],
                'appid' => $wechatConf['deviceConfig']['appid'],
                'appsecret' => $wechatConf['deviceConfig']['appsecret'],
                'template_url' => $url,
            ]
        ));
        // "userUuid=" . Xphp::$_user['useruuid'] . '&appid=' . $wechatConf['deviceConfig']['appid']. '&appsecret=' . $wechatConf['deviceConfig']['appsecret'] . '&template_url=' . $url;
        $wechat_url = 'https://open.weixin.qq.com/qr/code?username=' . $wechatConf['deviceConfig']['gh_id'];
        if ($wechatConf['deviceConfig']['wechat_mode'] == 1) {
            // 中转服务
            $base_url = Xphp::$_config['WECHAT_TRANSFER_URL'];
            $qr_url = $base_url . "/index.php?param=" . $qr_param;
        } else {
            $qr_url = $url . "/wechat.php?param=" . $qr_param;
        }

        $outfile = md5($qr_url) . '.png';
        $qrcode = $utils->qrcode($qr_url, $outfile);

        // 从URL获取图片内容
        $imageContent = file_get_contents($wechat_url);
        // 将图片内容转换为Base64编码
        $base64Image = base64_encode($imageContent);
        $qrcode2 = 'data:image/jpeg;base64,' . $base64Image;

        $wechatConf['deviceConfig']['auth_qrcode'] = $qrcode;
        $wechatConf['deviceConfig']['wechat_qrcode'] = $qrcode2;

        // 获取企业微信的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 11 的默认值
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 11";
        $wechats = $this->dbSelect($sqlupdate);
        $wechatContent = [];
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
        }

        $wechatConf2 = array(
            'wechatFlag' => $wechatContent['wechatFlag'] ? $utils->parseFlagToBool($wechatContent['wechatFlag']) : false,
            'systemFlag' => $wechatContent['systemFlag'] ? $utils->parseFlagToBool($wechatContent['systemFlag']) : false,
            'systemLevel' => explode(',', $wechatContent['systemLevel'] ?? ''),
            'taskFlag' => $wechatContent['taskFlag'] ? $utils->parseFlagToBool($wechatContent['taskFlag']) : false,
            'taskLevel' => explode(',', $wechatContent['taskLevel'] ?? ''),
            'deviceConfig' => [
                'wechat_url' => $wechatContent['wechat_url'] ?? '',
                // 企业微信配置信息
                'wechat_core_id' => $wechatContent['wechat_core_id'] ?? '', // 企业的id，在管理端->"我的企业" 可以看到
                // 某个自建应用的id及secret, 在管理端 -> 企业应用 -> 自建应用, 点进相应应用可以看到
                "wechat_app_id" => $wechatContent['wechat_app_id'] ?? '',
                "wechat_app_secret" => $wechatContent['wechat_app_secret'] ?? '',
            ],
        );

        $records = array(
            'email' => $emailConf,
            'sms' => $smsConf,
            'wechat' => $wechatConf,
            'wechat2' => $wechatConf2,
            'user' => $userConf,
            'update' => $infoupdate,
            'wechat_conf' => Xphp::$_config['WECHAT_CONFIG'], // 返回默认的配置，为了前端的模式切换有数据替换
        );

        return json_encode($records);
    }

    /**
     * 保存微信通知配置
     */
    public function updateWechat($params)
    {
        if (!empty($params['qrcode'])) {
            $utils = Xphp::instance('Utils');
            $qr_param = $utils->xphp_encrpt(json_encode(
                [
                    'userUuid' => Xphp::$_user['useruuid'],
                    'appid' => $params['appid'],
                    'appsecret' => $params['appsecret'],
                    'template_url' => $params['template_url'],
                ]
            ));

            if ($params['wechat_mode'] == 1) {
                // 中转服务
                $base_url = Xphp::$_config['WECHAT_TRANSFER_URL'];
                $qr_url = $base_url . "/index.php?param=" . $qr_param;
            } else {
                $qr_url = $params['template_url'] . "/wechat.php?param=" . $qr_param;
            }
            $wechat_url = 'https://open.weixin.qq.com/qr/code?username=' . $params['gh_id'];
            // 获取授权二维码
            $outfile = md5($qr_url) . '.png';
            $qrcode = $utils->qrcode($qr_url, $outfile);
            // 从URL获取图片内容
            $imageContent = file_get_contents($wechat_url);
            // 将图片内容转换为Base64编码
            $base64Image = base64_encode($imageContent);
            $qrcode2 = 'data:image/jpeg;base64,' . $base64Image;
            return json_encode(['code' => 0, 'qrcode' => $qrcode, 'qrcode2' => $qrcode2]);
        }

        // 需要先校验下 bd_system_settings 表里面是否存在了openid参数值
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = '10'";
        $wechats = $this->dbSelect($sqlupdate);

        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            // 这里判断下，如果更换了appid，那么之前的授权用户都要删掉
            if (!empty($wechatContent['openid']) && $params['appid'] == $wechatContent['appid']) {
                $wechatContent = array_merge($wechatContent, $params);
                if (!empty($params['test_notice'])) {
                    // 发送测试微信通知
                    $userhandler = Xphp::instance('UsersHandler');
                    $openid = array_column($wechatContent['openid'], 'openid'); // 消息接收人的openid
                    $return = $userhandler->sendWechat($openid, $wechatContent, Xphp::$_lang['UI_TEST_WECHAT_TITLE'], Xphp::$_lang['UI_TEST_WECHAT_DESCTION']);
                    if ($return) {
                        return json_encode(['code' => 0, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_TEST_DESCTION_SUCCESS']]);
                    }
                    return json_encode(['code' => -1, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP']]);
                }
                // 保存更新数据
                $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = '10'";
                $this->dbExec($sql, [json_encode($wechatContent), date('Y-m-d H:i:s')]);
                return json_encode(['code' => 0, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'] . xphp::$_lang['WEB_ERROR_BD_GENERIC_SUCCESS']]);
            }
        }
        return json_encode(['code' => -1, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP']]);
    }

    /**
     * 配置微信通知
     * @param unknown $params
     */
    public function setWechatConf($params)
    {
        // 需要先校验下 bd_system_settings 表里面是否存在了openid参数值
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sqlupdate);

        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);

            if (!empty($wechatContent['openid']) || !$params['wechatFlag']) {
                $utils = Xphp::instance('Utils');
                $wechatContent['wechatFlag'] = $utils->parseBoolToFlag($params['wechatFlag']);
                $wechatContent['systemFlag'] = $utils->parseBoolToFlag($params['systemFlag']);
                $wechatContent['taskFlag'] = $utils->parseBoolToFlag($params['taskFlag']);
                $systemLevel = [];
                foreach ($params['systemLevel'] as $key => $item) {
                    if ($item) {
                        $systemLevel[] = $key + 1;
                    }
                }
                $wechatContent['systemLevel'] = implode(',', $systemLevel);

                $taskLevel = [];
                foreach ($params['taskLevel'] as $key => $item) {
                    if ($item) {
                        $taskLevel[] = $key + 1;
                    }
                }
                $wechatContent['taskLevel'] = implode(',', $taskLevel);

                // 保存更新数据
                $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = 10";
                $this->dbExec($sql, [json_encode($wechatContent), date('Y-m-d H:i:s')]);

                // 删除data下面的所有二维码资源
                $qr_param = $utils->xphp_encrpt(json_encode(
                    [
                        'userUuid' => Xphp::$_user['useruuid'],
                        'appid' => $params['appid'],
                        'appsecret' => $params['appsecret'],
                        'template_url' => $params['template_url'],
                    ]
                ));
                $qr_url = $params['template_url'] . "/wechat.php?param=" . $qr_param;
                // 获取授权二维码
                $outfile = md5($qr_url) . '.png';

                $path = ROOT_PATH . 'web_ng/api/data/qrcode/';
                $mydir = dir($path);
                while ($file = $mydir->read()) {
                    if (($file != ".") and ($file != "..")) {
                        if ($outfile != $file) {
                            unlink($path . '/' . $file);
                        }
                    }
                }
                $mydir->close();

                return json_encode(['code' => 0, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_EMERGENCY_PLAN_CONFIGURE'] . xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'] . xphp::$_lang['WEB_ERROR_BD_GENERIC_SUCCESS']]);
            }
        }
        if (!$params['wechatFlag']) {
            // 表示关闭
            return json_encode(['code' => 0, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_EMERGENCY_PLAN_CONFIGURE'] . xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'] . xphp::$_lang['WEB_ERROR_BD_GENERIC_SUCCESS']]);
        }

        return json_encode(['code' => -1, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP']]);
    }

    /**
     * 获取微信通知用户列表
     */
    public function getWechatMember($params)
    {
        $start = intval($params['start']);
        $length = $params['length'];

        // 获取微信公众号的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 10 的默认值
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sqlupdate);
        $data = [];
        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            $data = $wechatContent['openid'] ?? [];
        }

        $total = count($data);
        if (!empty($data)) {
            $data = array_slice($data, $start, $length, true);
        }
        $records = array("data" => array());
        foreach ($data as $d) {
            $headimgurl = $d['headimgurl'];
            // 从URL获取图片内容
            $imageContent = file_get_contents($headimgurl);
            // 将图片内容转换为Base64编码
            $base64Image = 'data:image/jpeg;base64,' . base64_encode($imageContent);

            $records["data"][] = array(
                '<input type="checkbox" value="' . $d['openid'] . '">',
                $d['nickname'],
                $d['openid'],
                "<a href='{$headimgurl}' title='click to view ' target='_blank'><img src='{$base64Image}' style='height:50px;'/></a>"
            );
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $total;
        $records["recordsFiltered"] = $total;
        return json_encode($records);
    }

    /**
     * 删除微信授权用户
     */
    public function delWechatMember($params)
    {
        $openid_list = $params['openid'];
        // 获取微信公众号的配置 从bd_system_settings 的 settings_type 为 SYSTEM_WECHAT_OPENID 10 的默认值
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 10";
        $wechats = $this->dbSelect($sqlupdate);

        $lev = 'warning';
        $result = false;
        $operate = xphp::$_lang['UI_VCENTER_AUTH_DELETE'];
        $msg = $operate . Xphp::$_lang['WEB_PUBLIC_FAILURE'];

        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);
            $data = $wechatContent['openid'] ?? [];
        }
        if (!empty($data)) {
            // 进行数组剔除
            foreach ($data as $key => $val) {
                if (in_array($val['openid'], $openid_list)) {
                    unset($data[$key]);
                }
            }
            $wechatContent['openid'] = array_values($data);
            // 保存更新数据
            $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = 10";
            $this->dbExec($sql, [json_encode($wechatContent), date('Y-m-d H:i:s')]);
            $result = true;
            $msg = $operate . Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
            $lev = 'success';
        }

        $opMsg = array(
            're' => $result,
            'lev' => $lev,
            'msg' => $msg,
            'title' => $operate,
        );

        return json_encode($opMsg);
    }

    /**
     * 保存企业微信通知配置
     */
    public function updateEnterpriseWechat($params)
    {

        // 企业微信通知 ，测试是否能够获取accesstoken
        $utils = Xphp::instance('Utils');
        $config = [
            // 企业的id，在管理端->"我的企业" 可以看到
            "CORP_ID" => $params['wechat_core_id'],
            // 某个自建应用的id及secret, 在管理端 -> 企业应用 -> 自建应用, 点进相应应用可以看到
            "APP_ID" => $params['wechat_app_id'],
            "APP_SECRET" => $params['wechat_app_secret'],
        ];
        if (!empty($params['test_notice'])) {
            // 发送测试的企业微信通知
            $chk = $utils->send_wework_api($config, [
                'title' => xphp::$_lang['UI_TEST_WECHAT2_TITLE'],
                'content' => xphp::$_lang['UI_TEST_WECHAT2_DESCTION'],
                //'url' => $params['wechat_url'],
            ]);
            if ($chk) {
                return json_encode(['code' => 0, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'] . xphp::$_lang['WEB_ERROR_BD_GENERIC_SUCCESS']]);
            }
            return json_encode(['code' => -1, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_TPS']]);
        }
        $chk = $utils->send_wework_api($config, '', 1);
        if ($chk) {
            $sqlupdate = "select settings_content from bd_system_settings where settings_type = 11";
            $wechats = $this->dbSelect($sqlupdate);
            if (!empty($wechats)) {
                $wechatContent = json_decode($wechats[0]['settings_content'], true);
                // 保存更新数据
                $wechatContent = array_merge($wechatContent, $params);
                $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = '11'";
                $this->dbExec($sql, [json_encode($wechatContent), date('Y-m-d H:i:s')]);
            } else {
                // 插入数据
                $sql = "INSERT INTO `bd_system_settings` (`settings_type`, `settings_content`, `modify_time`, `user_uuid`) VALUES (11, ?, ?, ?)";
                $this->dbExec($sql, [json_encode($params), date('Y-m-d H:i:s'), Xphp::$_user['useruuid']]);
            }
            return json_encode(['code' => 0, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'] . xphp::$_lang['WEB_ERROR_BD_GENERIC_SUCCESS']]);
        }
        return json_encode(['code' => -1, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_TPS']]);
    }

    /**
     * 配置企业微信通知
     * @param unknown $params
     */
    public function setEnterpriseWechatConf($params)
    {
        // 需要先校验下 bd_system_settings 表里面是否存在了openid参数值
        $sqlupdate = "select settings_content from bd_system_settings where settings_type = 11";
        $wechats = $this->dbSelect($sqlupdate);

        if (!empty($wechats)) {
            $wechatContent = json_decode($wechats[0]['settings_content'], true);

            $utils = Xphp::instance('Utils');
            $wechatContent['wechatFlag'] = $utils->parseBoolToFlag($params['wechatFlag']);
            $wechatContent['systemFlag'] = $utils->parseBoolToFlag($params['systemFlag']);
            $wechatContent['taskFlag'] = $utils->parseBoolToFlag($params['taskFlag']);
            $systemLevel = [];
            foreach ($params['systemLevel'] as $key => $item) {
                if ($item) {
                    $systemLevel[] = $key + 1;
                }
            }
            $wechatContent['systemLevel'] = implode(',', $systemLevel);

            $taskLevel = [];
            foreach ($params['taskLevel'] as $key => $item) {
                if ($item) {
                    $taskLevel[] = $key + 1;
                }
            }
            $wechatContent['taskLevel'] = implode(',', $taskLevel);

            // 保存更新数据
            $sql = "update bd_system_settings set settings_content = ?, modify_time = ? where settings_type = 11";
            $this->dbExec($sql, [json_encode($wechatContent), date('Y-m-d H:i:s')]);
            return json_encode(['code' => 0, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'] . xphp::$_lang['WEB_ERROR_BD_GENERIC_SUCCESS']]);
        }
        return json_encode(['code' => -1, 'title' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT'], 'msg' => xphp::$_lang['UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP']]);
    }

    /**
     * 得到通知的等级设置字符串,直接可以插入到数据库的格式     1,2,3
     * @param unknown $levleArr
     */
    private function getNoticeSettingLevelStr($levleArr)
    {
        $levelFlag = array();
        $i = 1;
        foreach ($levleArr as $level) {
            if ($level) {
                $levelFlag[] = $i;
            }
            $i++;
        }
        $levelStr = implode(',', $levelFlag);
        return $levelStr;
    }

    /**
     * 统一设置通知
     * @param string $operate   操作描述
     * @param string $tableName 表名
     * @param string $flagCol   标识字段名
     * @param array $params     参数
     * @param string $systemLogKey 系统日志键名
     * @return string
     */
    private function setNoticeConf($operate, $tableName, $flagCol, $params, $systemLogKey)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        //         $roleHandler->pOperationPermissionCheckExit("p_setting_manager_notice");
        $utils = Xphp::instance('Utils');

        $flag = $utils->parseBoolToFlag($params['flag']);
        $systemFlag = $utils->parseBoolToFlag($params['systemFlag']);
        $taskFlag = $utils->parseBoolToFlag($params['taskFlag']);
        $verifyFlag = $utils->parseBoolToFlag($params['verifyFlag']);
        $systemLevel = $this->getNoticeSettingLevelStr($params['systemLevel']);
        $taskLevel = $this->getNoticeSettingLevelStr($params['taskLevel']);
        $verifyLevel = $this->getNoticeSettingLevelStr($params['verifyLevel']);
        $reportConf = $params['reportConf'];
        $recevieEmail = $params['recEmail'];

        if ("bd_email_notice" == $tableName) {
            $settings = json_decode($reportConf, true);
            $strategy = &$settings['timeStrategy'];
            $currentTime = strtotime(date("H:i:s"));
            $currentDate = strtotime(date('Y-m-d H:i:s'));
            foreach ($strategy as &$s) {
                $send_date = ''; //最后发送日期作为已发送标记，未发送设为空，当前时间超过设置时间就设为已发送
                $type = $s['type'];
                $time = strtotime($s['notice_time']);
                switch ($type) {
                    case 1:
                        if ($currentTime >= $time) {
                            $send_date = date('Y-m-d');
                        }
                        break;
                    case 2:
                        $weekDay = date("w");
                        if ($weekDay == 0) {
                            $weekDay = 7;
                        }
                        $days = $s['days'];
                        $dayList = array();
                        for ($i = 0; $i < count($days); $i++) {
                            if ($days[$i] == 1) {
                                $dayList[] = $i + 1;
                            }
                        }
                        if ($weekDay == $dayList[0] && $currentTime >= $time || $weekDay > $dayList[0]) {
                            $send_date = date('Y-m-d');
                        }
                        break;
                    case 3:
                        $monthDay = date("j");
                        $days = $s['days'];
                        $dayList = array();
                        for ($i = 0; $i < count($days); $i++) {
                            if ($days[$i] == 1) {
                                $dayList[] = $i + 1;
                            }
                        }
                        if ($monthDay == $dayList[0] && $currentTime >= $time || $monthDay > $dayList[0]) {
                            $send_date = date('Y-m-d');
                        }
                        break;
                    case 4:
                        if ($currentDate >= $time) {
                            $send_date = date('Y-m-d');
                        }
                        break;
                }
                $s['send_date'] = $send_date;
            }
            $reportConf = json_encode($settings);

            //邮件通知
            $sql = "update $tableName set $flagCol = ?, system_notice_flag = ?, system_notice_level = ?, 
            task_notice_flag = ?, task_notice_level = ?, report_config = ?, receive_email = ?, verify_report_flag = ?, verify_report_level = ? where email_notice_type = 1";
            $sqlParams = array($flag, $systemFlag, $systemLevel, $taskFlag, $taskLevel, $reportConf, $recevieEmail, $verifyFlag, $verifyLevel);
        } else if ("bd_sms_notice" == $tableName) {
            //短信通知
            $sql = "update $tableName set $flagCol = ?, system_notice_flag = ?, system_notice_level = ?, 
            task_notice_flag = ?, task_notice_level = ?";
            $sqlParams = array($flag, $systemFlag, $systemLevel, $taskFlag, $taskLevel);
        }


        $result = $this->dbExec($sql, $sqlParams);

        $this->unifyWriteSystemLog($result, $systemLogKey);

        if ($result) {
            return $this->muOpResult(true, $operate);
        } else {
            return $this->muOpResult(false, $operate);
        }
    }

    /**
     * 配置邮件通知
     * @param unknown $params
     */
    public function setEmailConf($params)
    {
        $operate = Xphp::$_lang['WEB_SYSTEM_SETTING_EMAIL_NOTICE'];
        $tableName = 'bd_email_notice';
        $flagCol = 'email_notice_flag';
        $systemLogKey = 'SYSTEM_SETTING_EMAIL_NOTICE';

        return $this->setNoticeConf($operate, $tableName, $flagCol, $params, $systemLogKey);
    }

    /**
     * 配置短信通知
     * @param unknown $params
     */
    public function setSmsConf($params)
    {
        $operate = Xphp::$_lang['WEB_SYSTEM_SETTING_SMS_NOTICE'];
        $tableName = 'bd_sms_notice';
        $flagCol = 'sms_notice_flag';
        $systemLogKey = 'SYSTEM_SETTING_SMS_NOTICE';

        return $this->setNoticeConf($operate, $tableName, $flagCol, $params, $systemLogKey);
    }

    /**
     * 测试发送邮件
     * @param unknown $params
     */
    public function testEmailNotice($params)
    {
        $host = $params['host'];
        $port = $params['port'];
        $user = $params['user'];
        $pass = base64_decode($params['pass']);
        $encryption = intval($params['encryption']);
        $recEmail = array($params['recEmail']);
        $this->paramsCheck($host, $port, $user, $recEmail);

        $title = Xphp::$_lang['WEB_SYSTEM_SETTING_TEST_EMAIL_TITLE'];
        $info = Xphp::$_lang['WEB_SYSTEM_SETTING_TEST_EMAIL_CONTENT'];
        $emailConf = Xphp::$_config['EMAIL'];
        //判断此次传入邮箱信息是否和之前一样
        //从数据库获取邮件信息
        $sql = "select smtp_config from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql);
        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        $utils = Xphp::instance('Utils');
        $primaryPass = $utils->decrypt($smtpConfig['pass']);

        if (
            $user == $smtpConfig['email'] && $host == $smtpConfig['host'] &&
            $pass == md5($primaryPass)
        ) {
            //默认邮件,设置
            $pass = $primaryPass;
        }

        //直接调用发送邮件接口
        $email = Xphp::instance('Email');
        $emailConfig = Xphp::$_config['EMAIL'];
        $email->config($host, $port, $emailConfig['authentication'], $user, $pass, Xphp::$_config['EMAIL_ENCRYPTION_TYPE'][$encryption]);
        if (!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], Xphp::$_config['ENTERPRISE'])) {
            $message = file_get_contents(ROOT_PATH . '/email/email-test-oem.html');
        } else if (Xphp::$_config['lang'] == "en-us") {
            $message = file_get_contents(ROOT_PATH . '/email/email-test-en.html');
        } else {
            $message = file_get_contents(ROOT_PATH . '/email/email-test.html');
        }

        $message = str_replace('reportTime', date('Y-m-d H:i:s'), $message);
        $message = str_replace('content', $info, $message);
        $message = str_replace('backupServerHost', $this->getMasterNodeIpLink(), $message);
        $message = str_replace('supportEmailHref', 'mailto: ' . Xphp::$_config['SYSTEM_INFO']['company_email'], $message);
        $message = str_replace('supportEmail', Xphp::$_config['SYSTEM_INFO']['company_email'], $message);
        $result = $email->sendmail($recEmail, $title, $message, array());
        $recEmailStr = implode(',', $recEmail);
        $operate = Xphp::$_lang['WEB_SYSTEM_SETTING_SEND_EMAIL_TO'] . $recEmailStr;
        if ($result) {
            return $this->muOpResult(true, $operate);
        } else {
            return $this->muOpResult(false, $operate);
        }
    }

    /**
     * 保存SMTP配置信息
     * @param unknown $params
     */
    public function setSmtpSetting($params)
    {
        $host = $params['host'];
        $port = $params['port'];
        $user = $params['user'];
        $pass = base64_decode($params['pass']);
        $encryption = intval($params['encryption']);
        $this->paramsCheck($host, $port, $user);

        $operate = Xphp::$_lang['WEB_SYSTEM_SAVE_SMTP_INFO'];
        $emailConf = Xphp::$_config['EMAIL'];
        if (
            $user == $emailConf['from_email'] && $host == $emailConf['smpt_host'] &&
            $pass == md5($emailConf['from_email_pass'])
        ) {
            return $this->muOpResult(true, $operate);
        }

        $utils = Xphp::instance('Utils');
        $pass = $utils->encrype($pass);

        $smtpConfig = array(
            'host' => $host,
            'port' => $port,
            'email' => $user,
            'pass' => $pass,
            'encryption' => $encryption
        );

        $sql = "update bd_email_notice set smtp_config = ? where email_notice_type = 1";
        $result = $this->dbExec($sql, array(json_encode($smtpConfig, true)));
        if ($result) {
            return $this->muOpResult(true, $operate);
        } else {
            return $this->muOpResult(false, $operate);
        }
    }

    /**
     * 测试短信发送
     * @param unknown $params
     */
    public function testSmsNotice($params)
    {
        $phone = $params['phone'];
        $this->paramsCheck($phone);
        $usersHandler = Xphp::instance('UsersHandler');
        $smsParams = array(
            'tels' => $phone,
            'msg' => Xphp::$_lang['WEB_SYSTEM_SETTING_SEND_SMS_SUCCESS'] . date("Y-m-d H:i:s"),
            'sendTime' => '',
        );
        $sendResult = $usersHandler->sendSmsInernet($smsParams);
        $this->updateInternetSettings($sendResult);

        return $sendResult;
    }

    /**
     * 互联网发送成功配置
     * @param json $sendResult
     */
    private function updateInternetSettings($sendResult)
    {
        $result = json_decode($sendResult, true);
        if (!$result['re'])
            return true;
        $sql = "update bd_sms_notice set sms_send_type = ?";
        $sqlParams = array(Xphp::$_config['SMS_CONFIG']['SEND_TYPE']['INTERNET']);
        $result = $this->dbExec($sql, $sqlParams);
        return $result;
    }

    /**
     * 测试短信猫发送
     * @param unknown $params
     */
    public function testSmsModemNotice($params)
    {
        $ip = $params['ip'];
        $database = $params['database'];
        $port = $params['port'];
        $user = $params['user'];
        $pass = base64_decode($params['pass']);
        $pass = Xphp::instance('Utils', 'encrype', $pass);
        $phone = $params['recPhone'];
        $this->paramsCheck($ip, $database, $port, $user, $pass, $phone);
        $smsParams = array(
            'tels' => $phone,
            'msg' => Xphp::$_lang['WEB_SYSTEM_SETTING_SEND_SMS_SUCCESS'] . date("Y-m-d H:i:s"),
        );
        $config = array(
            'ip' => $ip,
            'port' => $port,
            'user' => $user,
            'pass' => $pass,
            'database' => $database
        );

        $usersHandler = Xphp::instance('UsersHandler');
        $sendResult = $usersHandler->sendSmsModem($smsParams, $config);
        $this->updateModemSettings($sendResult, $config);

        return $sendResult;
    }

    /**
     * 更新短信猫配置(测试发送成功后)
     * @param json $sendResult
     * @param array $config
     */
    private function updateModemSettings($sendResult, $config)
    {
        $result = json_decode($sendResult, true);
        if (!$result['re'])
            return true;
        $sql = "update bd_sms_notice set sms_send_type = ?, sms_device_config = ?";
        $sqlParams = array(Xphp::$_config['SMS_CONFIG']['SEND_TYPE']['MODEM'], json_encode($config));
        $result = $this->dbExec($sql, $sqlParams);
        return $result;
    }

    /**
     * 获取软件最新版本信息
     * @param unknown $params
     */
    public function getNewVersionInfo($params)
    {
        $oldVersion = $params['version'];
        $this->paramsCheck($oldVersion);
        $usersHandler = Xphp::instance('UsersHandler');
        $p = array(
            'm' => Xphp::$_config['API_MODULE']['Upgrade'],
            'f' => 'checkNewVersion',
            'p' => array(
                'oldVersion' => $oldVersion,
            )
        );
        $operate = Xphp::$_lang['WEB_SYSTEM_GET_NEWEST_SOFTWARE_INFO'];
        return $usersHandler->remoteApi($operate, $p);
    }

    /**
     * 恢复出厂设置
     * @param unknown $params
     */
    public function doFactoryReset($params)
    {
        //检测用户权限
        $userType = Xphp::$_config['USERTYPE'];
        if (
            Xphp::$_user['usertype'] == $userType['manager'] or
            Xphp::$_user['usertype'] == $userType['administrator']
        ) {
            //发送恢复出厂设置指令
            sleep(5);
            return $this->muOpResult(true, Xphp::$_lang['WEB_SYSTEM_RESTORE_FACTORY_SETTING']);
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_PERMISSION_CHECK']));
    }

    /**
     * 设置系统名称
     * @param unknown $params
     */
    public function setSystemName($params)
    {
        $systemName = $params['systemname'];
        $count = file_put_contents(Xphp::$_config['SYSTEM_NAME_FILE'], $systemName, LOCK_EX);
        $flag = false;
        if ($count > 0) {
            $flag = true;
        }

        $this->unifyWriteSystemLog($flag, 'SYSTEM_SETTING_SYSTEM_NAME', array($systemName));

        return $this->muOpResult($flag, Xphp::$_lang['WEB_SYSTEM_SET_SYSTEM_NAME']);
    }

    /**
     * 获取系统名称
     * @param unknown $params
     */
    public function getSystemName($params)
    {
        if (file_exists(Xphp::$_config['SYSTEM_NAME_FILE'])) {
            //如果自定义系统名称存在
            return file_get_contents(Xphp::$_config['SYSTEM_NAME_FILE']);
        } else {
            //如果自定义系统名称不存在
            return Xphp::$_config['SYSTEM_INFO']['system_name'];
        }
    }

    /**
     * 导出系统任务信息
     * @param unknown $parmas
     */
    public function exportTaskInfo($params)
    {
        $dbInfo = Xphp::$_config['DB_INFO'];
        $timeStamp = date("Ymd.His");
        $tmpFilePath = Xphp::$_config['TMP_PATH'] . "taskinfo";
        $cmd = "mysqldump -P" . $dbInfo['port'] . " -h" . $dbInfo['host'] . " -u" . $dbInfo['user'] .
            " -p" . $dbInfo['pass'] . " " . $dbInfo['dbname'] . " bd_reserved_strategy " .
            "bd_running_info bd_storage_strategy bd_strategy bd_task bd_time_strategy " .
            "bd_transport_strategy vm_host vm_instant vm_machine vm_machine_list " .
            "vm_task vm_tree vm_vcenter bd_user bd_user_extension > " . $tmpFilePath;
        $cmd .= ";cd " . Xphp::$_config['TMP_PATH'] . " ; rm -f *.zip ";
        $cmd .= ";zip -q -j -r -m -P " . Xphp::$_config['ZIP_PASS'] . " $tmpFilePath.$timeStamp.zip taskinfo";
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_EXPORT_DATA');
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_SYSTEM_EXPORT_TASK_INFO'], '', 'warning', 0, array(Xphp::$_config['TMP_PATH_RE'] . "taskinfo." . $timeStamp . ".zip"));
    }

    /**
     * 导入系统任务信息
     * @param unknown $params
     */
    public function importTaskInfo($params)
    {
        $uploadfile = Xphp::$_config['IMPORT_INFO']['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        $tmpPath = Xphp::$_config['TMP_PATH'];
        $operate = Xphp::$_lang['WEB_SYSTEM_IMPORT_TASK_INFO'];
        if ($files) {
            //检测上传文件状态
            if (0 != $files['error']) {
                exit($this->muOpResult(false, $operate));
            }
            //检测文件后缀名
            $arr = explode(".", $files['name']);
            if ($arr[count($arr) - 1] != $uploadfile['suffixes']) {
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_IMPORT_FILE_TYPE_ERROR'], 'error'));
            }
            //检测上传文件大小
            if ($files['size'] > $uploadfile['size']) {
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_IMPORT_FILE_SIZE_ERROR'], 'error'));
            }
            //保存临时文件
            $count = file_put_contents($tmpPath . "task.zip", file_get_contents($files['tmp_name']));
            if ($count > 0) {
                $dbuser = Xphp::$_config['DB_INFO']['user'];
                $dbpass = Xphp::$_config['DB_INFO']['pass'];
                //导入成功,解压文件,导入到数据库
                $cmd = "unzip -P" . Xphp::$_config['ZIP_PASS'] . " -qo " . $tmpPath . "task.zip -d " . $tmpPath . "task";
                $cmd .= ";mysql -u$dbuser -p$dbpass vinchin_db < " . $tmpPath . "task/taskinfo";
                $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
                $msg = array('command' => $cmd);
                $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
                if ($mbResult['result']) {
                    $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_IMPORT_DATA');
                    return $this->muOpResult($mbResult['result'], $operate);
                }
                return $this->muOpResult($mbResult['result'], $operate, '', 'warning');
            }
            exit($this->muOpResult(false, $operate));
        } else {
            exit($this->muOpResult(false, $operate));
        }
    }

    /**
     * 重启、关机，先检查密码正确后再执行
     * @param $params
     * @return string
     */
    public function powerSubmit($params)
    {
        $user_uuid = $_SESSION['userUUID'];
        $powertype = $params['powertype'];
        if (1 == $powertype) {
            $operate = Xphp::$_lang['UI_SETTINGS_POWEROFF_REBOOT_MSG'];
        } elseif (2 == $powertype) {
            $operate = Xphp::$_lang['UI_SETTINGS_POWEROFF_POWEROFF_MSG'];
        } else {
            $operate = '';
        }
        $password = $params['password'];
        $sql = "select password from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, [$user_uuid]);
        if ($password != $data[0]['password']) {
            exit($this->muOpResult(false, $operate, Xphp::$_lang['UI_SETTINGS_POWEROFF_PASSWORD_MSG'], 'error'));
        }

        if (1 == $powertype) {
            return $this->doRebootBackupNode($params);
        } elseif (2 == $powertype) {
            return $this->doPoweroffBackupNode($params);
        }
    }

    /**
     * 重启备份节点
     * @param unknown $params
     */
    public function doRebootBackupNode($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_power");
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $opName = 'BD_SYSTEM_OP_REBOOT_SYSTEM';
        $msg = array();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_REBOOT');
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['UI_SETTINGS_POWEROFF_REBOOT_MSG']);
    }

    /**
     * 关闭备份节点
     * @param unknown $params
     */
    public function doPoweroffBackupNode($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_power");
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $opName = 'BD_SYSTEM_OP_POWEROFF_SYSTEM';
        $msg = array();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_POWER_OFF');
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['UI_SETTINGS_POWEROFF_POWEROFF_MSG']);
    }

    /**
     * 配置多个节点的hosts文件信息
     * @param array $settingNodes 多个节点uuid
     * @param string $setting
     */
    private function setNodesDnsHosts($settingNodes, $setting)
    {
        $setting = $this->groupDnsSetting($setting);
        $opName = 'NODE_SYS_OP_DO_CMD';
        $cmd = "echo '" . $setting . "' > /etc/hosts;mkdir -p /lib/backupsystem.host.conf;echo '" . $setting . "' > /lib/backupsystem.host.conf/hosts";
        $msg = array(
            "command" => $cmd
        );
        $result = true;
        foreach ($settingNodes as $nodeuuid) {
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            $result = $result & $mbResult['result'];
        }

        $this->unifyWriteSystemLog($result, 'SYSTEM_LOG_SETTINGS_DNS');
        return $this->muOpResult($result, Xphp::$_lang['UI_SETTINGS_DNS_SETTING']);
    }

    /**
     * 根据用户配置组合最终的hosts配置文件
     * @param string $setting
     */
    private function groupDnsSetting($setting)
    {
        $settingHeader = "127.0.0.1   localhost localhost.localdomain localhost4 localhost4.localdomain4" . PHP_EOL;
        $settingHeader .= "::1         localhost localhost.localdomain localhost6 localhost6.localdomain6" . PHP_EOL;
        return $settingHeader . $setting;
    }

    /**
     * 虚拟化中心自动刷新
     * @param array params
     */
    public function refreshVcenterTime($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_vcenter_manager_refresh");
        $refresh = intval($params['refresh']) * 60;
        $this->paramsCheck($refresh);
        $sql = "update bd_system set vcenter_refresh_interval = ?";
        $result = $this->dbExec($sql, array($refresh));
        return $result;
    }

    /**
     * 得到虚拟化中心自动刷新时间
     * @param array params
     */
    public function getrefreshVcenterTime($params)
    {
        $sql = "select vcenter_refresh_interval, public_cloud_platform_refresh_interval, private_cloud_platform_refresh_interval, authorized_flag from bd_system";
        $result = $this->dbSelect($sql);
        $info = array(
            'vcenter_refresh_interval' => $result[0]['vcenter_refresh_interval'],
            'public_cloud_platform_refresh_interval' => $result[0]['public_cloud_platform_refresh_interval'],
            'private_cloud_platform_refresh_interval' => $result[0]['private_cloud_platform_refresh_interval'],
            'authorized_flag' => intval($result[0]['authorized_flag'])
        );
        return json_encode($info);
    }

    /**
     * 消息推送配置
     * @param array $params
     */
    public function messagePush($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_push");
        //连接测试
        $this->messagePushTest($params);

        $username = $params['username'];
        $password = $params['password'];
        $protocol = $params['protocol'];
        $domain = $params['domain'];
        $port = $params['port'];
        $mode = $params['mode'];
        $pushtype = $params['pushtype'];
        $utils = Xphp::instance('Utils');
        $pushflag = $utils->parseBoolToFlag($params['pushflag']);
        $password = $utils->encrype(base64_decode($password));

        $sql = "update bd_message_push set user_name = ?, password = ?, protocol = ?, ip_domain = ?, port = ?, push_flag = ?, mode = ?, push_type = ?";
        $result = $this->dbExec($sql, array($username, $password, $protocol, $domain, $port, $pushflag, $mode, $pushtype));

        //配置消息推送日志
        $this->systemLog('SYSTEM_SETTING_CONFIG_MESSAGE_PUSH');
        return $this->muOpResult($result, Xphp::$_lang['UI_SETTINGS_MESSAGE_PUSH_CONFIG_MODIFY']);
    }

    /**
     * 消息推送测试
     * @param array $settings
     */
    private function messagePushTest($settings)
    {
        switch (intval($settings['protocol'])) {
            case Xphp::$_config['MQPROTOCOL']['STOMP']:
                return $this->messagePushTestStomp($settings);
                break;
            case Xphp::$_config['MQPROTOCOL']['OPENWIRE']:
                return $this->messagePushTestOpenwire($settings);
                break;
        }
    }

    /**
     * 消息推送测试 stomp协议
     * @param array $settings
     */
    private function messagePushTestStomp($settings)
    {
        $username = $settings['username'];
        $password = $settings['password'];
        $domain = $settings['domain'];
        $port = $settings['port'];
        $connect = "tcp://" . $domain . ":" . $port;
        try {
            $stomp = new Stomp($connect, $username, base64_decode($password));
        } catch (StompException $e) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_MESSAGE_PUSH_CONNECT_TEST'], $e->getMessage(), 'warning'));
        }
    }

    /**
     * 消息推送测试 openwire协议
     * @param array $settings
     */
    private function messagePushTestOpenwire($settings)
    {
        $opName = 'BD_SYSTEM_OP_MQ_CONNCT_TEST';
        $msg = array(
            'msg_name' => '',
            'msg_content' => '',
            'config' => array(
                'middleware' => Xphp::$_config['MIDDLEWARE']['ACTIVEMQ'],
                'protocol' => intval($settings['protocol']),
                'mode' => intval($settings['mode']),
                'ip_domain' => $settings['domain'],
                'port' => $settings['port'],
                'user_name' => $settings['username'],
                'password' => base64_decode($settings['password']),
            )
        );
        $msg = $this->mbPFMsg($opName, json_encode($msg), true);
        if (!$msg['result']) {
            exit($this->muOpResult(
                false,
                Xphp::$_lang['UI_SETTINGS_MESSAGE_PUSH_CONNECT_TEST'],
                $msg['errorMsg'],
                'warning',
                $msg['errorCode']
            ));
        }
    }

    /**
     * 消息推送历史数据获取
     * @param array $params
     */
    public function messagePushOldInfo()
    {
        $utils = Xphp::instance('Utils');
        $sql = "select user_name, password, protocol, ip_domain, port, mode, push_flag, push_type from bd_message_push";
        $data = $this->dbSelect($sql, array());
        $info = array(
            'username' => $data[0]['user_name'],
            'password' => $utils->decrypt($data[0]['password']),
            'protocol' => $data[0]['protocol'],
            'domain' => $data[0]['ip_domain'],
            'port' => $data[0]['port'],
            'pushflag' => $utils->parseFlagToBool($data[0]['push_flag']),
            'pushtype' => $data[0]['push_type'],
            'mode' => $data[0]['mode'],
        );
        return json_encode($info);
    }


    /**
     * 系统发送报表通知接口
     * @param array $settings
     */
    public function sendReportNotice($settings)
    {
        $strategy = $settings['timeStrategy'];
        $reportList = $settings['report_check_list'];
        $currentTime = strtotime(date("H:i:s"));
        $currentDate = strtotime(date('Y-m-d H:i:s'));
        $this->writeLog('start check every report time!!!!');
        $typeList = array();
        foreach ($strategy as $s) {
            $type = $s['type'];
            $unsend_flag = empty($s['send_date']) || strtotime($s['send_date']) < strtotime(date('Y-m-d')); //当天是否未发送，未发送状态且到了设置时间后才发送
            switch ($type) {
                case 1:
                    $time = strtotime($s['notice_time']);
                    $days = array();
                    $diffTime = $currentTime - $time;
                    if ($unsend_flag && $diffTime >= 0) {
                        $flag = true;
                        $typeList[] = array(
                            'flag' => $flag,
                            'type' => $type
                        );
                    }
                    break;
                case 2:
                    $time = strtotime($s['notice_time']);
                    $weekDay = date("w");
                    if ($weekDay == 0) {
                        $weekDay = 7;
                    }
                    $days = $s['days'];
                    $dayList = array();
                    for ($i = 0; $i < count($days); $i++) {
                        if ($days[$i] == 1) {
                            $dayList[] = $i + 1;
                        }
                    }
                    $diffTime = $currentTime - $time;
                    if ($unsend_flag && $diffTime >= 0 && in_array($weekDay, $dayList)) {
                        $flag = true;
                        $typeList[] = array(
                            'flag' => $flag,
                            'type' => $type
                        );
                    }
                    break;
                case 3:
                    $time = strtotime($s['notice_time']);
                    $monthDay = date("j");
                    $days = $s['days'];
                    $dayList = array();
                    for ($i = 0; $i < count($days); $i++) {
                        if ($days[$i] == 1) {
                            $dayList[] = $i + 1;
                        }
                    }
                    $diffTime = $currentTime - $time;
                    if ($unsend_flag && $diffTime >= 0 && in_array($monthDay, $dayList)) {
                        $flag = true;
                        $typeList[] = array(
                            'flag' => $flag,
                            'type' => $type
                        );
                    }
                    break;
                case 4:
                    $time = strtotime($s['notice_time']);
                    $unsend_flag = empty($s['send_date']) || date('Y', strtotime($s['send_date'])) < date('Y');
                    $diffTime = $currentDate - $time;
                    if ($unsend_flag && $diffTime >= 0) {
                        $flag = true;
                        $typeList[] = array(
                            'flag' => $flag,
                            'type' => $type
                        );
                    }
                    break;
            }

        }
        $this->writeLog('list!++' . json_encode($typeList));

        //如果没有符合时间的类型
        if (count($typeList) == 0) {
            $this->writeLog('no report!!!!');
            return true;
        }

        $send_flags = $this->sendTimeReport($typeList, $reportList);
        $this->writeLog('email send flags:' . json_encode($send_flags));

        //保存已发送标记
        foreach ($strategy as &$s) {
            if (isset($send_flags[strval($s['type'])])) {
                if ($send_flags[strval($s['type'])]) {
                    //发送成功
                    $s['send_date'] = date('Y-m-d');
                } else {
                    //发送失败
                    $s['send_date'] = '';
                }
            }
        }
        $settings['timeStrategy'] = $strategy;
        $sql = "update bd_email_notice set report_config = ? where email_notice_type = 1";
        $this->dbQuery($sql, [json_encode($settings)]);
    }

    public function sendTimeReport($typeList, $reportList)
    {
        $re = [];
        foreach ($typeList as $t) {
            $type = $t['type'];
            switch ($type) {
                case 1:
                    if ($t['flag']) {
                        $this->writeLog('send Days report !!!!');
                        $re[$type] = $this->sendReportEmail($t, $reportList);
                    }
                    break;

                case 2:
                    if ($t['flag']) {
                        $this->writeLog('send Week report !!!!');
                        $re[$type] = $this->sendReportEmail($t, $reportList);
                    }
                    break;

                case 3:
                    if ($t['flag']) {
                        $this->writeLog('send Month report !!!!');
                        $re[$type] = $this->sendReportEmail($t, $reportList);
                    }
                    break;
                case 4:
                    if ($t['flag']) {
                        $this->writeLog('send Year report !!!!');
                        $re[$type] = $this->sendReportEmail($t, $reportList);
                    }
                    break;

            }
        }
        return $re;
    }

    /**
     * 设置发送报表邮件接口
     * @param array $strategy
     * @param array $reportList
     */
    public function sendReportEmail($strategy, $reportList)
    {
        $re = false;
        if ($reportList['storage']) {
            $this->writeLog("start send storage report!!" . $strategy['type']);
            $re1 = $this->sendStorageReport($strategy);
            $re = $re || $re1;
        }
        if ($reportList['vm']) {
            $this->writeLog("start send VM report!!" . $strategy['type']);
            $re2 = $this->sendVmReport($strategy);
            $re = $re || $re2;
        }
        return $re;
    }

    /**
     * 发送存储报表通知
     * @param array $strategy
     *
     */
    public function sendStorageReport($strategy)
    {
        $utils = Xphp::instance('Utils');
        $type = $strategy['type'];
        $sql = "select count(bsr.storage_id) as total, sum(bsr.total_size) as total_size, sum(bsr.free_size) as free_size from bd_storage_resource bsr where bsr.lan_free_flag = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['UNSET']));
        $totalSize = $utils->calSize($data[0]['total_size']);
        $freeSize = $utils->calSize($data[0]['free_size']);

        $totalNum = $data[0]['total'];
        $sqlReport = "select count(distinct bbt.storage_uuid)as storage_num, sum(bbt.write_size) as storage_size from bd_backup_timepoint bbt,bd_storage_resource bsr where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ? ";
        $sqlStorageSize = "select bsr.storage_nickname, sum(bbt.write_size) current_size from bd_storage_resource bsr,bd_backup_timepoint bbt where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ? ";
        $sqlReportParams = array();
        switch ($type) {
            case 1:
                $sqlReport .= " and to_days(now()) - to_days(bbt.timepoint) = 1";  //昨天
                $sqlStorageSize .= " and to_days(now()) - to_days(bbt.timepoint) = 1";

                $timeRange = date("Y/m/d", strtotime("-1 day"));
                $reportTitle = $timeRange;
                $reportType = Xphp::$_lang['UI_REPORT_DAILY'];
                $timeDes = Xphp::$_lang['UI_REPORT_THIS_DAY'];
                break;
            case 2:
                $sqlReport .= " and  YEARWEEK(date_format(bbt.timepoint,'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1"; // 上一周
                $sqlStorageSize .= " and  YEARWEEK(date_format(bbt.timepoint,'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
                $timeRangeStart = date('w', time()) == 1 ? '-1 monday' : '-2 monday';//当天是周一
                $timeRange = date('Y/m/d', strtotime($timeRangeStart, time())) . "--" . date('Y/m/d', strtotime('-1 sunday', time()));
                $reportTitle = $timeRange;
                $reportType = Xphp::$_lang['UI_REPORT_WEEKLY'];
                $timeDes = Xphp::$_lang['UI_REPORT_THIS_WEEK'];
                break;
            case 3:
                $sqlReport .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1"; //上个月
                $sqlStorageSize .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";

                $timeRange = date('Y/m/01', strtotime('-1 month')) . "--" . date('Y/m/t', strtotime('-1 month'));
                $reportTitle = $timeRange;
                $reportType = Xphp::$_lang['UI_REPORT_MONTHLY'];
                $timeDes = Xphp::$_lang['UI_REPORT_THIS_MONTH'];
                break;
            case 4:
                $sqlReport .= " and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))"; //上一年
                $sqlStorageSize .= " and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))";

                $timeRange = date('Y/01/01', strtotime('-1 year')) . "--" . date('Y/12/31', strtotime('-1 year'));
                $reportTitle = $timeRange;
                $reportType = Xphp::$_lang['UI_REPORT_ANNALS'];
                $timeDes = Xphp::$_lang['UI_REPORT_THIS_YEAR'];
                break;
        }
        $emailTitle = $reportType . $timeRange;

        $dataReport = $this->dbSelect($sqlReport, array(Xphp::$_config['FLAG']['UNSET']));
        $dataStorageSize = $this->dbSelect($sqlStorageSize, array(Xphp::$_config['FLAG']['UNSET']));
        $currentSize = $utils->calSize(intval($dataReport[0]['storage_size']));
        $currentNum = intval($dataReport[0]['storage_num']);

        $sqlStorage = "select bn.ip, bn.node_nickname, bn.host_name, bsr.storage_uuid, bsr.storage_nickname, bsr.storage_type, bsr.node_uuid, bsr.total_size, bsr.free_size, bsr.storage_config from bd_storage_resource bsr, bd_node bn where bn.node_uuid = bsr.node_uuid and bsr.lan_free_flag = ? order by bsr.free_size desc";
        $dataStorage = $this->dbSelect($sqlStorage, array(Xphp::$_config['FLAG']['UNSET']));
        $storageList = array();
        $storageHandler = Xphp::instance('StorageHandler');
        $nodeHandler = Xphp::instance('NodeHandler');
        foreach ($dataStorage as $storage) {
            foreach ($dataStorageSize as $d) {
                $currentStorageSize = 0;
                if ($d['storage_nickname'] && $storage['storage_nickname'] == $d['storage_nickname']) {
                    $currentStorageSize = $d['current_size'];
                }
                $usedSize = $storage['total_size'] - $storage['free_size'];
                $usage = $storage['total_size'] ? round(intval($currentStorageSize) / intval($storage['total_size']) * 100, 2) : 0;
                $nodeName = $nodeHandler->getNodeGridName($storage['ip'], $storage['node_nickname'], $storage['host_name']) . "(" . $storage['ip'] . ")";
                //异地备份系统节点显示
                if ($storage['storage_type'] == Xphp::$_config['BD_STORAGE_TYPE']['REMOTE']) {
                    $storageConfig = json_decode($storage['storage_config'], true);
                    $nodeName = $storageConfig['remote_ip'];
                    if (!empty($storageConfig['remote_name'])) {
                        $nodeName = $storageConfig['remote_name'] . '(' . $storageConfig['remote_ip'] . ')';
                    }
                }
                $storageList[] = array(
                    "storage_name" => $storage['storage_nickname'],
                    "storage_type" => $storageHandler->getStorageTypeDes(intval($storage['storage_type'])),
                    "node" => $nodeName,
                    "total_size" => $utils->calSize($storage['total_size']),
                    "used_size" => $utils->calSize($usedSize),
                    "free_size" => $utils->calSize($storage['free_size']),
                    "useage" => $usage . '%'
                );
            }
        }
        $reportTime = date('Y-m-d H:i:s');

        if (!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], Xphp::$_config['ENTERPRISE'])) {
            $message = file_get_contents(ROOT_PATH . '/email/email-storage-report-oem.html');
        } else if (Xphp::$_config['lang'] == "en-us") {
            $message = file_get_contents(ROOT_PATH . '/email/email-storage-report-en.html');
        } else {
            $message = file_get_contents(ROOT_PATH . '/email/email-storage-report.html');
        }


        $content = "";
        if ($storageList) {
            foreach ($storageList as $s) {
                $info = "<tr><td>" . $s['storage_name'] . "</td>" .
                    "<td>" . $s['storage_type'] . "</td>" .
                    "<td>" . $s['node'] . "</td>" .
                    "<td>" . $s['total_size'] . "</td>" .
                    "<td>" . $s['used_size'] . "</td>" .
                    "<td class='font-success'>" . $s['free_size'] . "</td>" .
                    "<td>" . $s['useage'] . "</td></tr>";
                $content .= $info;
            }
            $message = str_replace('display-hide', 'display-show', $message);
            $message = str_replace('<tr><td>tableTd</td></tr>', $content, $message);
        }
        $message = str_replace('reportType', $reportType, $message);
        $message = str_replace('hostIp', $this->getMasterNodeIp(), $message);
        $message = str_replace('timeRange', $reportTitle, $message);
        $message = str_replace('reportTime', $reportTime, $message);

        $message = str_replace('totalSize', $totalSize, $message);
        $message = str_replace('freeSize', $freeSize, $message);
        //		$message = str_replace('currentNum', $currentNum, $message);
        $message = str_replace('currentSize', $currentSize, $message);
        $message = str_replace('timeDes', $timeDes, $message);

        $message = str_replace('backupServerHost', $this->getMasterNodeIpLink(), $message);

        $email = $this->getCompanyEmail();
        $message = str_replace('supportEmailHref', 'mailto: ' . $email, $message);
        $message = str_replace('supportEmail', $email, $message);

        $title = Xphp::$_lang['UI_REPORT_STORAGE'] . "—— " . $emailTitle;
        $this->writeLog("send storage report email!!!!!!!!!!!" . $reportTime);
        return $this->sendUnifyEmail($title, $message);
    }

    /**
     * 发送虚拟机报表通知
     *
     */
    public function sendVmReport($strategy)
    {
        $utils = Xphp::instance('Utils');
        $type = $strategy['type'];
        $totalSql = "select count(vt.dir_path) as total_vm_num from vm_tree vt, vm_vcenter vv where vt.vcenter_uuid = vv.vcenter_uuid and vt.type = ? and vt.display_mode =?";
        $dataTotalVm = $this->dbSelect($totalSql, array(7, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']));
        $protectedSql = "select count(distinct dir_path) as protected_vm_num, sum(write_size) as storage_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid";
        $dataProtect = $this->dbSelect($protectedSql, array());
        $totalVms = intval($dataTotalVm[0]['total_vm_num']);
        $protectVms = intval($dataProtect[0]['protected_vm_num']);
        $totalSize = $utils->calSize($dataProtect[0]['storage_size']);

        $sqlBackupNum = "select count(timepoint_uuid) as current_success_num ,sum(write_size) as storage_size from bd_backup_timepoint ";
        $sqlvm = "select distinct vbt.dir_path from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid ";
        $sqlCurrentVm = "select count(distinct vbt.dir_path) as current_vms from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid ";
        $sqlDayInfo = "select unix_timestamp(finish_time) finish_time, details, submodule_type, task_name from bd_history_task where task_type = ? and module_type = ? ";

        switch ($type) {
            case 1:
                $sqlDayInfo .= " and to_days(now()) - to_days(date_format(finish_time, '%Y-%m-%d')) = 1";
                $sqlBackupNum .= " where to_days(now()) - to_days(date_format(timepoint, '%Y-%m-%d')) = 1";  //昨天
                $sqlCurrentVm .= " and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天
                $sqlvm .= " and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天

                $timeRange = date("Y/m/d", strtotime("-1 day"));
                $reportTitle = $timeRange;
                $reportType = Xphp::$_lang['UI_REPORT_DAILY'];
                $timeDes = Xphp::$_lang['UI_REPORT_THIS_DAY'];
                break;
            case 2:
                $sqlDayInfo .= " and YEARWEEK(date_format(date_format(finish_time, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
                $sqlBackupNum .= " where YEARWEEK(date_format(date_format(timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1"; // 上一周
                $sqlCurrentVm .= " and YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
                $sqlvm .= " and YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
                $timeRangeStart = date('w', time()) == 1 ? '-1 monday' : '-2 monday';
                $timeRange = date('Y/m/d', strtotime($timeRangeStart, time())) . "--" . date('Y/m/d', strtotime('-1 sunday', time()));
                $reportTitle = $timeRange;
                $reportType = Xphp::$_lang['UI_REPORT_WEEKLY'];
                $timeDes = Xphp::$_lang['UI_REPORT_THIS_WEEK'];
                break;
            case 3:
                $sqlDayInfo .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(finish_time, '%Y%m')) =1";
                $sqlBackupNum .= " where PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(timepoint, '%Y%m')) =1"; //上个月
                $sqlCurrentVm .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";
                $sqlvm .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";

                $timeRange = date('Y/m/01', strtotime('-1 month')) . "--" . date('Y/m/t', strtotime('-1 month'));
                $reportTitle = $timeRange;
                $reportType = Xphp::$_lang['UI_REPORT_MONTHLY'];
                $timeDes = Xphp::$_lang['UI_REPORT_THIS_MONTH'];
                break;
            case 4:
                $sqlDayInfo .= " and year(finish_time)=year(date_sub(now(),interval 1 year))";
                $sqlBackupNum .= " where year(timepoint)=year(date_sub(now(),interval 1 year))"; //上一年
                $sqlCurrentVm .= " and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))";
                $sqlvm .= " and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))";

                $timeRange = date('Y/01/01', strtotime('-1 year')) . "--" . date('Y/12/31', strtotime('-1 year'));
                $reportTitle = $timeRange;
                $reportType = Xphp::$_lang['UI_REPORT_ANNALS'];
                $timeDes = Xphp::$_lang['UI_REPORT_THIS_YEAR'];
                break;
        }
        $emailTitle = $reportType . $timeRange;

        $dataVm = $this->dbSelect($sqlvm);
        $vmList = array();
        if ($dataVm) {
            foreach ($dataVm as $d) {
                $vmList[] = $this->getReportVmInfo($type, $d['dir_path']); //虚拟机列表信息
            }
        }
        $dataCurrent = $this->dbSelect($sqlBackupNum, array());
        $dataCurrenVms = $this->dbSelect($sqlCurrentVm);
        $dataInfo = $this->dbSelect($sqlDayInfo, array(Xphp::$_config['TASKTYPE']['BACKUP'], Xphp::$_config['MODULE_TYPE']['VM']));
        $currentNum = 0;
        $failedNum = 0;
        $currentSuccessNum = 0;
        $successList = array();
        $failedList = array();
        foreach ($dataInfo as $d) {
            $details = json_decode($d['details'], true);
            $vms_details = $details['vms_details'] ?? $details;
            foreach ($vms_details as $detail) {
                $currentNum++;
                if ($detail['task_status'] == Xphp::$_config['VmTaskStatus']['FINISH']) {
                    $currentSuccessNum++;
                } else {
                    $failedNum++;
                }
                $storageSize = $utils->calSize(intval($detail['write_size']));
                if (intval($detail['task_status']) != 3) {
                    //失败无存储空间占用
                    $storageSize = Xphp::$_config['NULLSPACE'];
                }
                $daysVmList = array(
                    "vm_name" => $detail['vm_name'],
                    "hypervisor" => Xphp::$_config['VMHYPERVISORDES'][intval($d['submodule_type'])],
                    "task_name" => $d['task_name'],
                    "backup_time" => date('Y-m-d H:i:s', $d['finish_time']),
                    "status" => intval($detail['task_status']),
                    "statusDes" => $this->getVmBackupStatus(intval($detail['task_status'])),
                    "storage_size" => $storageSize
                );
                if (intval($detail['task_status']) == 3) {
                    $successList[] = $daysVmList;
                } else {
                    $failedList[] = $daysVmList;
                }
            }

        }

        $vmInfoList = array_merge($successList, $failedList);
        $currentVms = $dataCurrenVms[0]['current_vms'];
        // 		$currentSuccessNum = $dataCurrent[0]['current_success_num'];
        $currentSize = $utils->calSize($dataCurrent[0]['storage_size']);
        $failedNum = str_replace("-", "", $failedNum);
        $content = "";
        if (!in_array(Xphp::$_config['SYSTEM_INFO']['enterprise'], Xphp::$_config['ENTERPRISE'])) {
            $message = file_get_contents(ROOT_PATH . '/email/email-vm-report-oem.html');
        } else if (Xphp::$_config['lang'] == "en-us") {
            $message = file_get_contents(ROOT_PATH . '/email/email-vm-report-en.html');
        } else {
            $message = file_get_contents(ROOT_PATH . '/email/email-vm-report.html');
        }
        if ($vmInfoList) {
            foreach ($vmInfoList as $vmInfo) {
                if ($vmInfo['status'] == 3) {
                    $status = "<td><span class='vm-status-success'>" . $vmInfo['statusDes'] . "</span></td>";
                } elseif ($vmInfo['status'] == 1) {
                    $status = "<td><span class='vm-status-waiting'>" . $vmInfo['statusDes'] . "</span></td>";
                } else {
                    $status = "<td><span class='vm-status-error'>" . $vmInfo['statusDes'] . "</span></td>";
                }
                $info = "<tr><td>" . $vmInfo['vm_name'] . "</td>" .
                    "<td>" . $vmInfo['hypervisor'] . "</td>" .
                    "<td>" . $vmInfo['task_name'] . "</td>" .
                    "<td>" . $vmInfo['backup_time'] . "</td>" .
                    $status .
                    "<td>" . $vmInfo['storage_size'] . "</td></tr>";
                $content .= $info;
            }
            $message = str_replace('display-hide', 'display-show', $message);
            $message = str_replace('<tr><td>tableTd</td></tr>', $content, $message);
        }

        $reportTime = date('Y-m-d H:i:s');
        $message = str_replace('reportType', $reportType, $message);
        $message = str_replace('hostIp', $this->getMasterNodeIp(), $message);
        $message = str_replace('timeRange', $reportTitle, $message);
        $message = str_replace('reportTime', $reportTime, $message);
        $message = str_replace('timeDes', $timeDes, $message);

        $message = str_replace('totalVms', $totalVms, $message);
        $message = str_replace('protectVms', $protectVms, $message);
        $message = str_replace('currentVms', $currentVms, $message);

        $message = str_replace('currentNum', $currentNum, $message);
        $message = str_replace('successNum', $currentSuccessNum, $message);
        $message = str_replace('failedNum', $failedNum, $message);
        $message = str_replace('totalSize', $totalSize, $message);
        $message = str_replace('currentSize', $currentSize, $message);
        $message = str_replace('backupServerHost', $this->getMasterNodeIpLink(), $message);

        $email = $this->getCompanyEmail();
        $message = str_replace('supportEmailHref', 'mailto: ' . $email, $message);
        $message = str_replace('supportEmail', $email, $message);

        $title = Xphp::$_lang['UI_REPORT_VM'] . "—— " . $emailTitle;
        $this->writeLog("send VM report email!!!!!!!!!!!" . $reportTime);
        return $this->sendUnifyEmail($title, $message);
    }

    public function getReportVmInfo($type, $dirPath)
    {
        $sqlVMInfo = "select vbt.vm_name, vbt.hypervisor_type, bbt.task_name from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
        $dataInfo = $this->dbSelect($sqlVMInfo, array($dirPath));

        $sql = "select count(bbt.timepoint_uuid) as total, sum(bbt.write_size) as storage_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
        $sqlTime = "select bbt.timepoint from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
        $sqlParams = array($dirPath);
        switch ($type) {
            case 1:
                $sql .= " and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天
                $sqlTime .= " and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1 ";
                break;
            case 2:
                $sql .= " and  YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1"; // 上一周
                $sqlTime .= " and  YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
                break;
            case 3:
                $sql .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1"; //上个月
                $sqlTime .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";
                break;
            case 4:
                $sql .= " and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))"; //上一年
                $sqlTime .= " and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))";
                break;
        }

        $sqlTime .= " order by bbt.timepoint desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $dataTime = $this->dbSelect($sqlTime, $sqlParams);
        $utils = Xphp::instance('Utils');
        $lastTime = "--";
        if ($dataTime) {
            $lastTime = $dataTime[0]['timepoint'];
        }
        $info = array(
            "vm_name" => $dataInfo[0]['vm_name'],
            "hypervisor" => Xphp::$_config['VMHYPERVISORDES'][intval($dataInfo[0]['hypervisor_type'])],
            "task_name" => $dataInfo[0]['task_name'],
            "last_backup_time" => $lastTime,
            "backup_num" => $data[0]['total'],
            "storage_size" => $utils->calSize($data[0]['storage_size'])
        );

        return $info;

    }


    /**
     * 统一发送报表通知邮件
     * @param string $title
     * @param string $content
     */
    public function sendUnifyEmail($title, $content)
    {
        $alarmHandler = Xphp::instance('AlarmHandler');
        $userConf = $alarmHandler->getManagerEmailAndTelephone();
        $email = $userConf['email'];
        $email = $alarmHandler->getAllEmail($email); //管理员邮件累加额外添加邮件地址
        $attachment = "";

        $params = array(
            'email' => $email,
            'title' => $title,
            'info' => $content,
            "attachment" => $attachment
        );
        $userHandler = Xphp::instance('UsersHandler');
        $result = $userHandler->sendEmail($params);
        $result = json_decode($result, true);
        $this->writeLog('unify email result:' . json_encode($result));
        return $result['re'];

    }

    /**
     * 读取报告通知配置信息
     */
    public function getSettings()
    {
        $sql = "select report_config from bd_email_notice where email_notice_type = 1";
        $data = $this->dbSelect($sql, array());
        return json_decode($data[0]['report_config'], true);
    }

    /**
     * 获取虚拟机报表虚拟机备份状态描述
     * @param int $status
     */
    private function getVmBackupStatus($status)
    {
        $statusDes = Xphp::$_lang['WEB_PUBLIC_SUCCESS'];

        if ($status == 3) {
            $statusDes = Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
        } elseif ($status == 1) {
            $statusDes = Xphp::$_lang['WEB_PLATFORM_DES_WAITING'];
        } else {
            $statusDes = Xphp::$_lang['WEB_PUBLIC_FAILURE'];
        }
        return $statusDes;
    }

    public function getSystemLang()
    {
        $sql = "select language from bd_user";
        $data = $this->dbSelect($sql, array());

        return $data[0]['language'];
    }



    /**
     * 上传升级包到指定目录
     *
     */
    public function uploadPatch()
    {
        // Support CORS
        // header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...

        //检查上传升级包格式
        $this->checkUpgradeFile();

        //检查请求类型是否有效
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }

        //调试模式
        if (!empty($_REQUEST['debug'])) {
            $random = rand(0, intval($_REQUEST['debug']));
            if ($random === 0) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }


        // 5 minutes execution time，设置函数执行最大时间，0为无限制
        @set_time_limit(5 * 60);


        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $uploadDir = Xphp::$_config['UPLOAD_PATH'];
        $targetDir = Xphp::$_config['UPLOADTMP_PATH'];
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // 		@file_put_contents('./a.txt',print_r($_FILES,true),FILE_APPEND); //上传文件写入日志到a.txt

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;


        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }

        // Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }


        while ($buff = fread($in, 5 * 1024 * 1024)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        // 检查是否是最后一个分片，并判断是否已经完成所有分片上传，且中间分片等于分片大小5M
        $maxChunk = $chunks - 1;
        $done = true;
        for ($index = 0; $index <= $chunk; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
            }
        }
        if (!file_exists("{$filePath}_{$maxChunk}.part")) {
            $done = false;
        }
        if ($done) {
            if (!$out = @fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        //赋予文件读写执行权限，重新获取一次
                        chmod("{$filePath}_{$index}.part", 0755);
                        $in = @fopen("{$filePath}_{$index}.part", "rb");
                    }
//                    if ($in == false) {
//                        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
//                    }
                    while ($buff = fread($in, 10 * 1024 * 1024)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        }

        die($_SESSION['fileName']);
    }

    /**
     * 更新升级包列表记录
     * @param unknown $params
     */
    public function updatePatchList($params)
    {
        $utils = Xphp::instance('Utils');
        $uuid = $utils->uuid();
        $name = $params['name'];
        $fileSize = $params['size'];
        $filePath = Xphp::$_config['UPLOAD_PATH'] . $name;
        $md5file = $params['md5'];
        if (empty($md5file)) {
            $md5file = md5_file($filePath);
        }

        //检测文件完整性
        // $this->checkFileIntegrity($name, $md5file);

        //		$this->checkPatchExist($md5file); //检查文件是否存在
        $fileUrl = "/tmp/upgrade/" . $name;
        // 		$nodeInfo = $this->getNodeInfo();
        $nodeuuid = $this->getMasterNode();

        //新加,这里要获取文件中的更新json数据,无论是自动升级还是手动上传 都要获取文件中的更新json数据
//		$extraData = $this->checkFileExist($name);
//		//得到大小
//		if(empty($fileSize)){
//		    $fileSize = $extraData['fileSize'];
//		};
//		//解压出来json
//		$extraData = json_decode($extraData['updateJson'],true);
        //屏蔽解压升级包获取升级日志信息，解决上次升级包太大界面崩溃问题
        $extraData = "";
        if (empty($extraData)) {
            $extraData = "";
        }
        $extraDataList = array(
            'file_size' => $fileSize,
            'updateDetails' => $this->getExtraDataUpdate($extraData),
        );

        $status = Xphp::$_config['UPDATE_PATCH_STATUS']['UPDATE_WAITING'];
        $uploadTime = date("Y-m-d H:i:s");
        //插入之前检测该信息是否存在 避免重复插入
        $sqlcheck = "select id from bd_update_file where level1_md5 = ?";
        $resultcheck = $this->dbSelect($sqlcheck, array($md5file));
        if (!empty($resultcheck)) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_FILE_UPLOAD'], Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_CHECK_ERROR'], "warning"));
        }
        $sql = "insert into bd_update_file (uuid, level1_md5, node_uuid, name, path, status, controller_file_url, upload_time, extra) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbQuery($sql, array($uuid, $md5file, $nodeuuid, $name, $filePath, $status, $fileUrl, $uploadTime, json_encode($extraDataList)));
        if ($result == 1) {
            return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_UPDATE_FILE_UPLOAD']);
        } else {
            return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_FILE_UPLOAD'], Xphp::$_lang['UI_SETTINGS_UPDATE_FILE_UPLOAD_ERROR'], "warning");
        }
    }

    /**
     * 已下载完成 存在数据库的更新日志为目前版本符合的日志
     * @param unknown $extraData
     * return array $extraData
     */
    public function getExtraDataUpdate($extraData)
    {
        //如果为空则返回空
        if (empty($extraData)) {
            return array();
        }
        $versionNum = $this->getVersionNum(Xphp::$_config['SYSTEM_INFO']['version']);
        $getversionList = explode(".", $versionNum);
        $getversionNum = sprintf('%03s', $getversionList[0]) . sprintf('%03s', $getversionList[1]) . sprintf('%03s', $getversionList[2]) . sprintf('%07s', $getversionList[3]);
        $infoLogList = array();
        foreach ($extraData['info'] as $op) {
            //这里需要处理下版本比如5.1.1.12589=> 0050010010012589
            $eachVersion = $op['version'];
            $versionList = explode(".", $eachVersion);
            $eachNum = sprintf('%03s', $versionList[0]) . sprintf('%03s', $versionList[1]) . sprintf('%03s', $versionList[2]) . sprintf('%07s', $versionList[3]);
            if ($eachNum >= $getversionNum) {
                $infoLogList[] = $op;
            }
        }
        //把新得到的日志覆盖掉原来的日志
        $extraData['info'] = $infoLogList;
        return $extraData;
    }

    /**
     * 根据版本号处理成能识别的版本号  现在是//build: 5.0.21.18676 -> 5.0.21.18676
     * @param unknown $version
     */
    private function getVersionNum($version)
    {
        //先去掉build
        $versionList = explode(":", $version);
        $versionList = explode("-", $versionList[1]);
        if (empty($versionList[0])) {
            return;
        }
        $versionNum = trim($versionList[0]);
        return $versionNum;
    }





    /**
     * 检查文件是否完整  对比文件md5
     * @param unknown $filename
     * @param unknown $md5
     */
    private function checkFileIntegrity($filename, $md5)
    {
        //文件路径
        $path = Xphp::$_config['UPDATE_DOWNLOAD_FILE_PATH'];
        $path_file = $path . $filename;
        //检查文件是否存在
        if (file_exists($path_file)) {
            //如果文件存在 则计算出文件的md5值
            $this_md5 = md5_file($path_file);
            if ($this_md5 != $md5) {
                //如果md5的值不相等 说明安装包不完整,则删除掉原来的安装包
                $cmd = "rm -rf " . $path_file;
                exec($cmd);
                exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_INTEGRITY_CHECK'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_NOT_COMPLETE']));
            }
        } else {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_INTEGRITY_CHECK'], Xphp::$_lang['WEB_ERROR_BD_FILE_NOT_EXIST_ERROR']));
        }
    }

    /**
     * 得到补丁包列表
     * @param unknown $params
     */
    public function getPatches($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', '', '', 'upload_time');
        $nodeuuid = $this->getMasterNode();
        $sql = "select uuid, name,level1_md5,upload_time, extra from bd_update_file where node_uuid = ? order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlCount = "select count(uuid) as total from bd_update_file where node_uuid = ?";

        $data = $this->dbSelect($sql, array($nodeuuid, $start, $length));
        $count = $this->dbSelect($sqlCount, array($nodeuuid));

        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $utils = Xphp::instance('Utils');
        foreach ($data as $d) {
            $fileInfo = json_decode($d['extra'], true);
            $fileSize = $fileInfo['file_size'];
            $fileUpdateInfo = $fileInfo['updateDetails'];
            //得到版本信息
            $versionNum = "5.0.0";
            //得到所有日志
            $log_list = $fileUpdateInfo['info'];
            //得到符合其版本的日志注意事项描述
            $infoLogList = array();
            foreach ($log_list as $each) {
                if ($each['version'] >= $versionNum) {
                    $infoLogList[] = $each;
                }
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $d['uuid'] . '">',
                $d['name'],
                $d['level1_md5'],
                $utils->calSize($fileSize),
                $d['upload_time'],
                $infoLogList,
                $d['uuid']
            );
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return json_encode($records);
    }

    /**
     * 删除升级包
     * @param unknown $params
     */
    public function deletePatch($params)
    {
        $uuids = $params['uuids'];
        $nameList = array();
        foreach ($uuids as $uuid) {
            $nameList[] = $this->getPatchName($uuid);
        }
        $this->paramsCheck($uuids);
        $uuidList = implode("','", $uuids);
        //获取需要删除的md5
        $sql = "select distinct level1_md5 from bd_update_file where uuid in ('" . $uuidList . "')";
        $data = $this->dbSelect($sql);
        $md5List = array();
        foreach ($data as $d) {
            $md5List[] = $d['level1_md5'];
        }
        $md5ListDes = implode("','", $md5List);
        //获取md5对应的记录uuid集合
        $sql = "select buf.uuid, buf.node_uuid, bn.node_type from bd_update_file buf, bd_node bn where buf.node_uuid = bn.node_uuid and buf.level1_md5 in ('" . $md5ListDes . "')";
        $data = $this->dbSelect($sql);
        $uuidList = $nodeList = array();
        foreach ($data as $d) {
            $uuidList[] = $d['uuid'];
            $nodeList[$d['node_uuid']] = $d['node_type'];
        }
        //删除对应的升级包
        $uuidListDes = implode("','", $uuidList);
        $sql = "delete from bd_update_file where uuid in ('" . $uuidListDes . "')";
        $result = $this->dbExec($sql, array());
        if ($result) {
            //			foreach ($nameList as $name){
//				$deleteFile = Xphp::$_config['UPLOAD_PATH'].$name;
//				if(file_exists($deleteFile)){
//					$cmd = "rm -rf ".Xphp::$_config['UPLOAD_PATH'].$name;
//					exec($cmd);
//				}
//			}
            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            foreach ($nodeList as $nodeuuid => $nodetype) {
                $cmd = '';
                foreach ($nameList as $name) {
                    if (Xphp::$_config['NODETYPE']['MASTER'] == $nodetype) {
                        $findCmd = 'find ' . Xphp::$_config['UPLOAD_PATH'] . $name;
                        $subcmd = 'rm -rf ' . Xphp::$_config['UPLOAD_PATH'] . $name . ';';
                    } else {
                        $findCmd = 'find /patch/' . $name;
                        $subcmd = 'rm -rf /patch/' . $name . ';';
                    }

                    //检查文件是否存在
                    $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode(['command' => $findCmd]), true, false);
                    if ($mbResult['result'] && $mbResult['msg']['detail']) {
                        $cmd .= $subcmd;
                    }
                }

                $this->mbNodeMsg($opName, $nodeuuid, json_encode(['command' => $cmd]), true, false);
            }
        }
        return $this->muOpResult($result, Xphp::$_lang['UI_SETTINGS_UPDATE_DELETE_PATCH']);
    }


    /**
     * 删除升级历史
     * @param unknown $params
     */
    public function deletePatchHistory($params)
    {
        $ids = $params['ids'];
        $nameList = array();
        foreach ($ids as $id) {
            $this->checkDeleteHistory($id);
        }
        $this->paramsCheck($ids);
        $idsList = implode("','", $ids);
        $sql = "delete from bd_update_log where id in ('" . $idsList . "')";
        $result = $this->dbExec($sql, array());
        return $this->muOpResult($result, Xphp::$_lang['UI_SETTINGS_UPDATE_DELETE_HISTORY']);
    }

    /**
     * 获取升级包名字
     * @param string $uuid
     */
    public function getPatchName($uuid)
    {
        $sql = "select name from bd_update_file where uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $name = $data[0]['name'];
        return $name;
    }

    /**
     * 检查是否为失败的升级历史
     * @param int $id
     */
    public function checkDeleteHistory($id)
    {
        $sql = "select errno from bd_update_log where id = ?";
        $data = $this->dbSelect($sql, array($id));
        $errno = intval($data[0]['errno']);
        if ($errno == 0) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_DELETE_HISTORY'], Xphp::$_lang['UI_SETTINGS_UPDATE_DELETE_HISTORY_ERROR_TIPS'], "warning"));
        }
    }


    /**
     * 得到升级历史
     * @param unknown $params
     */
    public function getPatchHistory($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $sortArr = array('', '', '', '', "", 'bul.log_time', 'bul.errno', '');

        $sql = "select bul.id, bul.node_uuid, bul.patch_file_name, bul.log_time, bul.log_file_path, bul.errno, bul.extra from bd_update_log bul, bd_node bn where bul.node_uuid = bn.node_uuid order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlCount = "select count(bul.id) as total from bd_update_log bul, bd_node bn where bul.node_uuid = bn.node_uuid";

        $data = $this->dbSelect($sql, array($start, $length));
        $count = $this->dbSelect($sqlCount);

        $records = array();
        $records["data"] = array();
        $id = $start + 1;
        $utils = Xphp::instance('Utils');
        foreach ($data as $d) {
            $nodeInfo = $this->getNodeInfo($d['node_uuid']);
            $checkbox = '<input type="checkbox" name="id[]" value="' . $d['id'] . '">';
            // 			$downloadDiv = '<a id="download'.$d['id'].'"  value="'.$d['log_file_path'].'">'.Xphp::$_lang['UI_NODE_DOWNLOAD'].'</a>';
// 			if(empty($d['log_file_path'])){
// 				$downloadDiv = Xphp::$_config['NULLSPACE'];
// 			}

            //得到日志
            $extraLog = "";
            if (!empty($d['extra'])) {
                $extraLog = json_decode($d['extra'], true);
                $extraLog = $extraLog['updateDetails'];
                if (!empty($extraLog)) {
                    $extraLog = $extraLog['info'];
                }
            }

            $records["data"][] = array(
                $checkbox,
                $id++,
                $nodeInfo['node_name'],
                $nodeInfo['ip'],
                $d['patch_file_name'],
                $d['log_time'],
                $this->getlogDes($d['errno']),
                // 					$downloadDiv,
                intval($d['errno']),
                $d['id'],
                $d['log_file_path'],
                $extraLog,
            );
        }

        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        return json_encode($records);
    }

    /**
     * 下载升级历史日志
     * @param unknown $params
     */
    public function downloadHistory($params)
    {
        $id = $params['id'];
        $info = $this->getUpdateHistory($id);
        $path = $info['path'];
        $time = date("Y-m-d_H-i-s", strtotime($info['log_time']));
        $fileName = $info['node_name'] . '_' . $time . '_' . 'update_log.txt';
        $content = '';
        $opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
        $msg = $this->mbNodeMsg($opName, $info['node_uuid'], json_encode($msg), true);
        if ($msg['result']) {
            $content = $msg['msg']['file_content'];
        } else {
            return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_GET_HISTORY_LOG'], Xphp::$_lang['UI_SETTINGS_UPDATE_GET_HISTORY_LOG_ERROR'], "warning");
        }
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . strlen($content));
        Header("Content-Disposition: attachment; filename=" . $fileName);
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        return $content;

    }

    /**
     * 获取升级历史日志文件名字信息
     * @param string $path
     */
    public function getUpdateHistory($id)
    {
        $sql = "select node_uuid, log_time, log_file_path from bd_update_log where id = ?";
        $data = $this->dbSelect($sql, array($id));
        $nodeInfo = $this->getNodeInfo($data[0]['node_uuid']);
        $info = array(
            'node_name' => $nodeInfo['node_name'],
            'log_time' => $data[0]['log_time'],
            'node_uuid' => $data[0]['node_uuid'],
            'path' => $data[0]['log_file_path'],
        );
        return $info;
    }

    /**
     * 获取节点信息
     * @param string $nodeuuid
     */
    public function getNodeInfo($nodeuuid)
    {
        $sql = "select node_uuid, ip,host_name, node_nickname from bd_node ";
        $sqlParams = array();
        if ($nodeuuid) {
            $sql .= " where node_uuid = ?";
            $sqlParams = array($nodeuuid);
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $nodeHandler = Xphp::instance('NodeHandler');
        $info = array(
            'node_uuid' => $data[0]['node_uuid'],
            'ip' => $data[0]['ip'],
            "node_name" => $nodeHandler->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name'])
        );
        return $info;
    }

    /**
     * 获取升级历史状态描述
     * @param int $error
     */
    public function getlogDes($error)
    {
        $des = Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
        if ($error == 0) {
            $des = Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
        } else {
            $des = Xphp::$_lang['WEB_PUBLIC_FAILURE'];
        }
        return $des;
    }

    /**
     * 检测是否有升级中的升级包
     * @return false|string
     */
    public function checkIsUpgrading()
    {
        $sql = "select id from bd_update_file where status = 5 limit 1";
        $data = $this->dbSelect($sql);
        return json_encode(['isUpgrading' => !empty($data)]);
    }

    /**
     * 获取选中的升级包的信息
     * @param unknown $params
     */
    public function getSelectPatch($params)
    {
        $sql = "select name,path from bd_update_file where uuid = ?";
        $data = $this->dbSelect($sql, array($params['uuid']));
        $info = array(
            'name' => $data[0]['name']
        );
        return json_encode($info);
    }

    /**
     * 启动升级检查
     * @param unknown $params
     */
    public function upgradeCheck($params)
    {
        $this->checkLabStatus();//检查是否有虚拟实验室部署中/或者修改中
        $nodeuuids = $params['nodeuuids'];
        $this->checkRunningJob($nodeuuids);//检查是否有运行任务
        $this->checkRunningCluster();
        $masterFlag = $params['masterFlag'];
        $name = $params['name'];
        $md5 = $params['md5'];
        //检查升级包版本是否是对应版本
        $this->checkUpgradeVersion($name);

        //检查节点是否已经在升级
        $this->checkNodeInUpgrad($nodeuuids, $md5);

        if ($masterFlag) {
            $this->checkReUpdate($nodeuuids[0], $md5);
        } else {
            foreach ($nodeuuids as $nodeuuid) {
                $this->checkReUpdate($nodeuuid, $md5);
            }
        }

        return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_UPDATE_START']);
    }

    /**
     * 检查节点是否正在升级
     * @param unknown $nodeuuids
     * @param unknown $md5
     */
    private function checkNodeInUpgrad($nodeuuids, $md5)
    {
        $updatePatchConf = Xphp::$_config['UPDATE_PATCH_STATUS'];
        //正在升级中的状态
        $upgradeRuningArr = array(
            $updatePatchConf['UPDATE_UPLOAD_TO_NODE'],
            $updatePatchConf['UPDATING'],
            $updatePatchConf['PATCH_INVALID_ING'],
        );
        foreach ($nodeuuids as $nodeuuid) {
            $sql = "select status from bd_update_file where node_uuid = ? and level1_md5 = ? and error_code = ?";
            $data = $this->dbSelect($sql, array($nodeuuid, $md5, 0));
            if (in_array($data[0]['status'], $upgradeRuningArr)) {
                //如果检测到有正在升级中的状态,直接返回.
                exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR'], Xphp::$_lang['UI_SETTINGS_UPDATE_CHECK_ERROR_TIPS'], "warning"));
            }
        }
    }


    /**
     * 升级补丁检查是否存在正在运行中的任务
     */
    private function checkRunningJob($nodeuuids)
    {
        $nodeuuidsDes = implode("','", $nodeuuids);
        $sql = "select task_uuid from bd_task where task_status in (" . Xphp::$_config['TASKSTATUS']['RUNNING'] . "," . Xphp::$_config['TASKSTATUS']['NETWORK_FAULT'] . "," . Xphp::$_config['TASKSTATUS']['PAUSED'] . "," . Xphp::$_config['TASKSTATUS']['STARTING'] . "," . Xphp::$_config['TASKSTATUS']['STOPPING'] . "," . Xphp::$_config['TASKSTATUS']['TAKEOVER'] . "," . Xphp::$_config['TASKSTATUS']['TAKEOVER_STARTING'] . "," . Xphp::$_config['TASKSTATUS']['TAKEOVER_STOPPING'] . "," . Xphp::$_config['TASKSTATUS']['SUCCESSED'] . ") and node_uuid in ('" . $nodeuuidsDes . "')";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR'], Xphp::$_lang['UI_SETTINGS_UPDATE_RUNNING_JOB_EXIST'], "warning"));
        }
    }

    /**
     * 升级补丁检查是否存在正在运行中的集群
     */
    private function checkRunningCluster()
    {
        $sql = "select cluster_uuid from bd_cluster where cluster_status in (" . Xphp::$_config['TASKSTATUS']['RUNNING'] . "," . Xphp::$_config['TASKSTATUS']['PAUSED'] . "," . Xphp::$_config['TASKSTATUS']['STARTING'] . "," . Xphp::$_config['TASKSTATUS']['STOPPING'] . ")";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR'], Xphp::$_lang['UI_SETTINGS_UPDATE_RUNNING_CLUSTER_EXIST'], "warning"));
        }
    }

    /**
     * 重复升级检查
     * @param string $nodeuuid
     * @param string $md5
     */
    public function checkReUpdate($nodeuuid, $md5)
    {
        $sql = "select log_file_path from bd_update_log where level1_md5 = ? and node_uuid = ? and errno = ?";
        $data = $this->dbSelect($sql, array($md5, $nodeuuid, 0));
        if ($data) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR'], Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR_TIPS'], "warning"));
        }
    }

    /**
     * 启动升级
     * @param unknown $params
     */
    public function upgradeSystem($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_upgrade");
        $nodeuuids = $params['nodeuuids'];
        $masterFlag = $params['masterFlag'];
        $name = $params['name'];
        $path = Xphp::$_config['UPLOAD_PATH'] . $name;
        $url = "/tmp/upgrade/" . $name;
        $patchuuid = $params['uuid'];

        if ($masterFlag) {
            // new: 检查升级包是否有效的操作已经融入到 NODE_SYS_OP_DO_MASTER_UPDATE 操作码中了，不再用 NODE_SYS_OP_CHECK_PATCH_VALIDITY 操作码
            $opName = 'NODE_SYS_OP_DO_MASTER_UPDATE';
            $msg = array("patch_uuid" => $patchuuid, "patch_file_path" => $path);
            $mbResult = $this->mbNodeMsg($opName, $nodeuuids[0], json_encode($msg), false, true);
        } else {
            //子节点，下载升级包
            $md5 = md5_file($path);
            $childInfo = array();
            $fileSize = filesize($path);
            $sqlParams = array(
                "file_url" => $url,
                "file_name" => $name,
                "file_md5" => $md5,
                "file_path" => $path,
                "file_size" => $fileSize
            );
            foreach ($nodeuuids as $nodeuuid) {
                $this->insertNodePatch($sqlParams, $nodeuuid);
            }
            foreach ($nodeuuids as $nodeUUID) {	//插入备份节点补丁包信息导数据库
                $patchInfo = $this->getNodePatchInfo($nodeUUID, $name);
                $patchuuid = $patchInfo['uuid'];
                $opName = 'NODE_SYS_OP_DOWNLOAD';
                $msg = array(
                    "patch_uuid" => $patchuuid,
                    "file_url" => $url,
                    "file_name" => $name,
                    "file_md5" => $md5,
                    "file_size" => $fileSize
                );

                $mbResult = $this->mbNodeMsg($opName, $nodeUUID, json_encode($msg), false, true);
            }
            return;
        }
    }

    /**
     * 启动子节点升级
     * @param unknown $params
     */
    public function upgradeChildNode($params)
    {
        $nodeuuids = $params['nodeuuids'];
        $name = $params['name'];
        $patch_options = array(
            'update_web_config' => $params['patch_options']['update_web_config']?"true":"false",
            'update_web_cert' => $params['patch_options']['update_web_cert']?"true":"false",
            'update_firewall_rule' => $params['patch_options']['update_firewall_rule']?"true":"false"
        );
        $updatePatchConf = Xphp::$_config['UPDATE_PATCH_STATUS'];
        //正在升级中的状态
        $upgradeRuningArr = array(
            $updatePatchConf['UPDATE_UPLOAD_TO_NODE'],
            $updatePatchConf['UPDATING'],
            $updatePatchConf['PATCH_INVALID_ING'],
        );
        foreach ($nodeuuids as $nodeUUID) {	//插入备份节点补丁包信息导数据库
            $patchInfo = $this->getNodePatchInfo($nodeUUID, $name);
            $patchuuid = $patchInfo['uuid'];
            $path = $patchInfo['path'];
            $status = $patchInfo['status'];
            //如果这个节点正在升级,跳过本次消息
            if (in_array($status, $upgradeRuningArr)) {
                continue;
            }
            //检查升级包是否有效
//            $checkResult = $this->checkPatchValidity($nodeUUID, $patchuuid, $path);
//            if($checkResult){
            //启动升级
            $opName = 'NODE_SYS_OP_DO_NODE_UPDATE';
            $msg = array("patch_uuid" => $patchuuid, "patch_file_path" => $path, "patch_options" => $patch_options);
            $mbResult = $this->mbNodeMsg($opName, $nodeUUID, json_encode($msg), false, true);
            //            }
        }
        return;
    }

    /**
     * 升级包检查，超时时间三分钟，直到检查成功(1分钟有可能还是会失败)
     * @param unknown $nodeuuid
     * @param unknown $msg
     */
    private function checkPatchValidity($nodeUUID, $patchUUID, $patchFilePath)
    {
        //检查升级包是否有效，先发消息到后台检查，如果检查成功直接返回，如果检查超时，查询数据库检查
        $opName = "NODE_SYS_OP_CHECK_PATCH_VALIDITY";
        $msg = array("patch_uuid" => $patchUUID, "patch_file_path" => $patchFilePath);
        $mbResult = $this->mbNodeMsg($opName, $nodeUUID, json_encode($msg));
        if ($mbResult['result']) {
            //检查成功，直接返回
            return true;
        } else {
            //检查失败，查询数据库
            $i = 0;
            while ($i < 900) {
                $sql = "select status from bd_update_file where uuid = ?";
                $data = $this->dbSelect($sql, array($patchUUID));
                if (empty($data)) {
                    return false;
                }
                if ($data[0]['status'] == Xphp::$_config['UPDATE_PATCH_STATUS']['PATCH_INVALID_COMPLETE']) {
                    //检查完成
                    return true;
                }
                if ($data[0]['status'] != Xphp::$_config['UPDATE_PATCH_STATUS']['PATCH_INVALID_ING']) {
                    //如果不是检查中,直接返回
                    return false;
                }
                sleep(1);
                $i++;
            }
            return false;
        }

    }

    /**
     * 检查主节点是否已经升级
     * @param unknown $params
     */
    public function checkMasterUpdate($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $patchuuid = $params['patchuuid'];
        $md5 = $this->getMd5ByUUID($patchuuid);
        $sql = "select bul.log_file_path from bd_update_log bul, bd_update_file buf where bul.level1_md5 = buf.level1_md5 and bul.level1_md5 = ? and bul.node_uuid= ?";
        $data = $this->dbSelect($sql, array($md5, $nodeuuid));
        if (empty($data)) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_SYSTEM_UPDATE'], Xphp::$_lang['UI_SETTINGS_UPDATE_MASTER_CHECK']));
        }
    }

    /**
     * 获取主节点
     *
     */
    public function getMasterNode()
    {
        $sqlNode = "select node_uuid from bd_node where node_type = ?";
        $dataNode = $this->dbSelect($sqlNode, array(Xphp::$_config['NODETYPE']['MASTER']));
        return $dataNode[0]['node_uuid'];
    }

    /**
     * 得到升级可用的备份节点列表
     * @param unknown $params
     */
    public function getUpdateNodeList($params)
    {
        $patchuuid = $params['uuid'];
        $md5 = $this->getMd5ByUUID($patchuuid);
        $sql = "select ip, host_name, node_nickname, node_uuid from bd_node order by node_type asc";
        $data = $this->dbSelect($sql, array());

        $info = array();
        $nodeHandler = Xphp::instance('NodeHandler');
        foreach ($data as $d) {
            $info[] = array(
                "node_name" => $nodeHandler->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                "node_uuid" => $d['node_uuid'],
                "ip" => $d['ip'],
                "update_flag" => $this->getUpdateFlag($d['node_uuid'], $md5),
                "use_flag" => $this->getChildNodeStatus($d['node_uuid']),
            );
        }
        return json_encode($info);
    }

    /**
     * 获取所有子节点状态
     */
    private function getChildNodeStatus($nodeuuid)
    {
        $sql = "select module_type, online_flag from bd_module_server where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $flag = true;
        $module = array();

        foreach ($data as $d) {
            // 只要node_server不在线，子节点升级就不可用
            if ($d['module_type'] === 1000 && $d['online_flag'] == Xphp::$_config['FLAG']['UNSET']) {
                $flag = false;
                $module[] = $d['module_type'];
            }
        }

        if (empty($data))
            $flag = false; //如果没有记录
        $info = array(
            'flag' => $flag,
            'module' => $module
        );
        return $info;
    }

    /**
     * 获取升级标志
     * @param string $nodeuuid
     * @param string $md5
     */
    public function getUpdateFlag($nodeuuid, $md5)
    {
        $sql = "select log_file_path from bd_update_log where node_uuid = ? and level1_md5 = ?";
        $data = $this->dbSelect($sql, array($nodeuuid, $md5));
        if ($data) {
            return true;
        }
        return false;
    }

    /**
     * 插入备份节点升级记录信息
     * @param array $sqlParams
     * @param string $nodeuuid
     */
    private function insertNodePatch($sqlParams, $nodeuuid)
    {
        $md5file = $sqlParams['file_md5'];
        //检查是否已经插入数据到数据库
        $sql = "select uuid from bd_update_file where level1_md5 = ? and node_uuid = ?";
        $data = $this->dbSelect($sql, array($md5file, $nodeuuid));
        if (!empty($data))
            return true;
        $utils = Xphp::instance('Utils');
        $uuid = $utils->uuid();
        $name = $sqlParams['file_name'];
        $filepath = $sqlParams['file_path'];
        $fileUrl = $sqlParams['file_url'];
        $status = Xphp::$_config['UPDATE_PATCH_STATUS']['UPDATE_WAITING'];
        $uploadTime = date('Y-m-d H:i:s');
        $sql = "insert into bd_update_file (uuid, level1_md5, node_uuid, name, path, status, controller_file_url, upload_time) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbQuery($sql, array($uuid, $md5file, $nodeuuid, $name, $filepath, $status, $fileUrl, $uploadTime));
        return $result;
    }

    /**
     * 得到安装包的uuid
     * @param unknown $nodeuuid
     * @param unknown $name
     */
    private function getNodePatchInfo($nodeuuid, $name)
    {
        $sql = "select uuid, level1_md5, node_uuid, status, update_progress, name, path, controller_file_url from bd_update_file where name = ? and node_uuid = ?";
        $data = $this->dbSelect($sql, array($name, $nodeuuid));
        return $data[0];
    }

    /**
     * 获取升级包第一级MD5
     * @param string $uuid
     */
    public function getMd5ByUUID($uuid)
    {
        $sqlmd5 = "select level1_md5 from bd_update_file where uuid =?";
        $datamd5 = $this->dbSelect($sqlmd5, array($uuid));
        return $datamd5[0]['level1_md5'];
    }

    /**
     * 获取升级进度信息
     * @param unknown $params
     */
    public function getUpgradeInfo($params)
    {
        $initFlag = $params['initflag'];
        $uuid = $params['uuid'];
        $fileMd5 = $this->getMd5ByUUID($uuid);
        $nodeuuids = $params['nodeuuids'];
        $nodeArray = implode("','", $nodeuuids);
        $sql = "select bn.node_uuid, bn.ip, bn.host_name, bn.node_nickname, bpf.update_progress,bpf.status from bd_update_file bpf,bd_node bn where bpf.node_uuid = bn.node_uuid and bpf.level1_md5 = ? and bpf.node_uuid in ('" . $nodeArray . "')";
        $data = $this->dbSelect($sql, array($fileMd5));
        if (!$data) {
            $basicInfo = array('flag' => 2);
            return json_encode($basicInfo);
        }
        $info = array();
        $nodeHandler = Xphp::instance('NodeHandler');
        foreach ($data as $d) {
            $status = intval($d['status']);
            if (!$initFlag) {
                $status = Xphp::$_config['UPDATE_PATCH_STATUS']['UPDATE_WAITING'];
            }
            $info[] = array(
                "node_uuid" => $d['node_uuid'],
                "node_name" => $nodeHandler->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                "progress" => $d['update_progress'] . "%",
                "flag" => true,
                "status" => $status,
                "statusDes" => $this->getUpdateStatusDes($status)
            );
            //如果升级成功，杀掉所有web监控进程，并由看门狗重启
            if ($status == 4) {
                $this->restartWebProcess($d['node_uuid']);
            }
        }

        return json_encode($info);

    }

    /**
     * 获取升级状态描述
     * @param int $status
     */
    public function getUpdateStatusDes($status)
    {
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = $pfDes['UPDATE_PATCH_DES'][$status];
        return $des;
    }

    /**
     * 检查升级包是否存在
     * @param string $fileName
     */
    public function checkPatchExist($fileName)
    {
        $nodeuuid = $this->getMasterNode();
        $sql = "select * from bd_update_file where name = ? and node_uuid = ?";
        $data = $this->dbSelect($sql, array($fileName, $nodeuuid));
        if ($data) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_CHECK'], Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_CHECK_ERROR'], "warning"));
        }
    }

    /**
     * 得到大屏配置
     * @param unknown $params
     * @return string
     */
    public function getDefaultVisualInfo($params)
    {
        $visualConf = array();

        $settingsHandler = Xphp::instance('SettingsHandler');
        $visualConf = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['VISUAL']);
        $visualConf = json_decode($visualConf[0]['settings_content'], true);

        return json_encode($visualConf);
    }


    /**
     * 自定义设置大屏标题
     * @param unknown $params
     * @return string
     */
    public function setVisualInfo($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_visualization");
        $title = $params['title'];
        $localName = $params['localName'];
        $offsiteName = $params['offsiteName'];
        $cloudName = $params['cloudName'];
        $taskAlertCheck = $params['taskAlertCheck'];
        $systemAlertCheck = $params['systemAlertCheck'];

        $visualConf = array(
            "config" => array(
                'title' => $title,
                'localName' => $localName,
                'offsiteName' => $offsiteName,
                'cloudName' => $cloudName,
                'taskAlertCheck' => $taskAlertCheck,
                'systemAlertCheck' => $systemAlertCheck,
            )
        );
        $visualConf = json_encode($visualConf);

        $settingsHandler = Xphp::instance('SettingsHandler');
        $result = $settingsHandler->modifySettingsInfosWithType(Xphp::$_config['SETTINGS_CONF']['VISUAL'], $visualConf);
        if (!$result) {
            return $this->muOpResult(false, Xphp::$_lang['UI_VISUAL_CONFIG_SETTING'], "", 'warning');
        }
        $this->systemLog('SYSTEM_SETTING_CONFIG_VISUAL_NAME', array("$title"));
        return $this->muOpResult(true, Xphp::$_lang['UI_VISUAL_CONFIG_SETTING']);
    }


    /**
     * 获取各节点系统服务列表
     * @param unknown $params
     */
    public function getServiceList($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $p = json_decode($_POST['p'], true);
        $start = intval($p['start']);
        $length = intval($p['length']);
        $draw = $params['draw'];
        $search = $p['search'];
        $name = $search['name'];
        if ($params['start'] > $start) {
            $start = intval($params['start']);
        }
        if ($params['length'] > $length) {
            $length = intval($params['length']);
        }
        //获取service列表
        $cmd = "systemctl list-unit-files |grep -E 'enabled|disabled' ";
        if (!empty($name)) {
            if ($name == "ssh") {
                $cmd = "systemctl -a list-units --type=service ";
            }

            $cmd .= "|grep " . $name . " ";
        }
        $cmd .= "|sort |sed -n '" . ($start + 1) . "," . ($start + $length) . "p'";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        $records = array();
        $records["data"] = array();
        $id = $start * 1 + 1;
        $count = 0;

        if ($mbResult['result']) {
            $details = explode("\n", trim($mbResult['msg']['detail']));
            $count = count($details);
            //如果有服务或搜索到服务
            if (!empty($details[0])) {
                foreach ($details as $key => $value) {
                    $info = trim($value);
                    $info = explode(" ", $info);
                    $info = array_filter($info);
                    if ($info[1] == "not-found" || $info[3] == "dead")
                        continue;    //不显示未找到的或者已经死掉的服务
                    $newInfo = array();
                    foreach ($info as $i) {
                        $newInfo[] = $i;
                    }
                    $status = $this->getServiceStatus($newInfo[0], $nodeuuid);
                    $records["data"][] = array(
                        $id++,
                        $newInfo[0],
                        $this->getServiceStatusDes($status),
                        $this->getServiceOperate($status),
                        $status
                    );
                }
            } else {
                $count = 0;
            }
        }

        //计算可用服务总数
        $cmd = "systemctl list-unit-files |grep -E 'enabled|disabled' ";
        if (!empty($name)) {
            $cmd .= "|grep " . $name . " ";
        }
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if ($mbResult['result']) {
            $details = explode("\n", trim($mbResult['msg']['detail']));
            $count = count($details);
            if (empty($details[0])) {
                $count = 0;
            }
        }
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = $count;
        $records["recordsFiltered"] = $count;

        return json_encode($records);
    }

    /**
     * 获取服务运行状态
     * @param string $name
     */
    private function getServiceStatus($name, $nodeuuid)
    {
        // 整形的那么就是端口
        if ($name == '' . intval($name) && intval($name)) {
            // $cmd = "firewall-cmd --permanent --list-ports | grep " . $name . "/tcp 2>&1 >/dev/null;echo $?";
            $cmd = "systemctl status firewalld  2>&1 >/dev/null && grep -E $name /etc/firewalld/zones/public.xml | grep tcp 2>&1 >/dev/null;echo $?";
        } else {
            $cmd = "systemctl status " . $name . " 2>&1 >/dev/null;echo $?";
        }

        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if ($mbResult['result']) {
            return intval($mbResult['msg']['detail']);
        }
    }


    /**
     * 获取状态描述
     * @param int $stauts
     */
    private function getServiceStatusDes($stauts)
    {
        $statusDes = Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'];
        if ($stauts === 0) {
            $statusDes = Xphp::$_lang['WEB_PLATFORM_DES_RUNNING'];
        } else {
            $statusDes = Xphp::$_lang['WEB_PLATFORM_DES_STOP'];
        }

        return $statusDes;
    }

    /**
     * 获取服务对应操作 1开启 2关闭 3重启
     * @param int $status
     */
    private function getServiceOperate($status)
    {
        $operateCode = array(1, 2, 3);

        return $operateCode;
    }

    /**
     * 启动服务
     * @param unknown $params
     */
    public function startService($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_tools");
        $name = str_replace(" ", '', $params['name']);
        $nodeuuid = $params['nodeuuid'];
        $cmd = "systemctl start " . $name;
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if ($mbResult['result']) {
            return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_SERVICE_START']);
        } else {
            return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_SERVICE_START'], "", "warning", $mbResult['errorCode']);
        }
    }

    /**
     * 停止服务
     * @param unknown $params
     */
    public function stopService($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_tools");
        $name = str_replace(" ", '', $params['name']);
        $nodeuuid = $params['nodeuuid'];
        $cmd = "systemctl stop " . $name;
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if ($mbResult['result']) {
            return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_SERVICE_STOP']);
        } else {
            return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_SERVICE_STOP'], "", "warning", $mbResult['errorCode']);
        }
    }

    /**
     * 重启服务
     * @param unknown $params
     */
    public function resetService($params)
    {
        $name = str_replace(" ", '', $params['name']);
        $nodeuuid = $params['nodeuuid'];
        $cmd = "systemctl restart " . $name;
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if ($mbResult['result']) {
            return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_SERVICE_RESTART']);
        } else {
            return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_SERVICE_RESTART'], "", "warning", $mbResult['errorCode']);
        }
    }

    /**
     * 测试网络连接
     * @param unknown $params
     */
    public function testConnectTool($params)
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_setting_manager_tools");
        $ip = $params['ip'];
        //         $domainMatch = "/^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i";
        $domainMatch = "/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/";
        $ipMatch1 = "/^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i";
        $ipMatch2 = "/^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i";
        //http|https
//         $domainMatchHTTP = "/^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i";
        $domainMatchHTTP = "/^(http|https):\/\/([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}$/";
        $ipMatch1HTTP = "/^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i";
        $ipMatch2HTTP = "/^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i";
        //检查是否是IP或域名
        if (!preg_match($domainMatch, $ip) && !preg_match($ipMatch1, $ip) && !preg_match($ipMatch2, $ip) && !preg_match($domainMatchHTTP, $ip) && !preg_match($ipMatch1HTTP, $ip) && !preg_match($ipMatch2HTTP, $ip)) {
            return $this->muOpResult(false, Xphp::$_lang['UI_VCENTER_ENGINE_BACKUP_TEST_CONNECT'], Xphp::$_lang['UI_SETTINGS_TOOL_IP_OR_DOMAIN_ERROR'], "warning");
        }

        // 判断下当前的 IPADDR 是否是ipv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // 是 ipv6手动更改 tooltype 的值为ping6或者telnet6
            $params['tooltype'] = $params['tooltype'] . 6;
        }
        $port = $params['port'];
        $type = $params['tooltype'];
        $nodeuuid = $params['nodeuuid'];
        $result = true;
        $opName = '';
        if (in_array($type, ['ping', 'ping6'])) {
            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            $cmd = $type . " -c 1 " . $ip . " -W 3 2>&1>/dev/null;echo $?";
            $msg = array('command' => $cmd);
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            $result = trim($mbResult['msg']['detail']) == "0" ? true : false;
            $opName = $type . " " . $ip;

        } elseif (in_array($type, ['telnet', 'telnet6'])) {
            //socket连接
            if ($type == 'telnet') {
                $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            } else {
                $socket = socket_create(AF_INET6, SOCK_STREAM, SOL_TCP);
            }

            if ($socket === false) {
                return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_TOOL_TEST_SOCKET_ERROR'], "warning");
            } else {
                socket_set_block($socket);
                socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
                socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 3, "usec" => 0));
            }
            $result = socket_connect($socket, $ip, $port);
            socket_close($socket);
            $opName = $type . " " . $ip . " : " . $port;
        }
        $opName .= Xphp::$_lang['UI_VCENTER_ENGINE_BACKUP_TEST_CONNECT'];
        if ($result) {
            return $this->muOpResult(true, $opName);
        } else {
            return $this->muOpResult(false, $opName, "", "warning");
        }
    }

    /**
     * 同步所有proxy DNS
     * @param string $settings
     * @return boolean
     */
    private function setAllProxyDns($settings)
    {
        $sql = "select appliance_uuid, ip, port, nickname, progress_server_listen_port, progress_server_start_port, progress_server_end_port, cdp_client_listen_port, cdp_client_log_listen_port, log_server_listen_port, system_info from bd_appliance where node_uuid = ''";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            $storageHandler = Xphp::instance('StorageHandler');
            $nodeHandler = Xphp::instance('NodeHandler');
            foreach ($data as $d) {
                $details = json_decode($d['system_info'], true);
                //修改同步域名解析
                $domainConfig = $details['domain_config'];
                //检查是否重复同步相同的域名解析
                if (!strpos($domainConfig, $settings)) {
                    $domainConfig = $details['domain_config'] . $settings;
                }
                $msg = array(
                    "appliance_uuid" => $d['appliance_uuid'],
                    "ip" => $d['ip'],
                    "port" => $d['port'],
                    "progress_server_listen_port" => $d['progress_server_listen_port'],
                    "progress_server_start_port" => $d['progress_server_start_port'],
                    "progress_server_end_port" => $d['progress_server_end_port'],
                    "cdp_client_listen_port" => $d['cdp_client_listen_port'],
                    "cdp_client_log_listen_port" => $d['cdp_client_log_listen_port'],
                    "log_server_listen_port" => $d['log_server_listen_port'],
                    "nickname" => $d['nickname'],
                    "domain_config" => $domainConfig
                );
                $opName = "NODE_APPLIANCE_OP_MODIFY";
                $operate = $storageHandler->getUnifyOpcodeDes($opName);
                $nodeuuid = $nodeHandler->getLocalNodeUUID();
                $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg));
            }
        }


        return true;
    }

    /**
     * 获取系统授权类型
     * @param unknown $params
     * @return unknown
     */
    public function getSystemLicenseType()
    {
        $sql = "select license_type, storage_count from bd_license";
        $data = $this->dbSelect($sql);
        $type = 0;
        $storageCount = 0;
        if (!empty($data)) {
            $type = intval($data[0]['license_type']);
            $storageCount = intval($data[0]['storage_count']);
        }
        $info = array(
            'licensetype' => $type,
            'storage_size' => $storageCount
        );

        return json_encode($info);
    }


    /**
     * 重启数据库实时和定时服务
     * @param unknown $nodeuuid
     */
    private function restartDbcdpServer($nodeuuid)
    {
        $cmd = "ps aux|grep lzbackupsys";
        $opName = 'NODE_SYS_OP_DO_CMD';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
        $countDb = !empty($mbResult['msg']['details']) ? count($mbResult['msg']['details']) : 0;
        $cmd = "";
        if ($countDb > 2) {
            $list = explode(' ', $mbResult['msg']['details'][0]);
            $proceID = $list[4];
            $cmd .= "kill -9 " . $proceID;
            $cmd .= ";systemctl restart daserver;systemctl restart dastorage;";

        } else {
            $cmd .= "systemctl restart daserver;systemctl restart dastorage;";
        }
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
        return;
    }


    /**
     * 获取首页和租户界面展示权限
     *
     */
    public function getUserPermission()
    {
        $sql = "select mut.tenant_uuid from bd_user bu, mt_user_tenant mut where bu.user_uuid = mut.user_uuid and bu.user_uuid = ?";
        $data = $this->dbSelect($sql, array($_SESSION['userUUID']));
        $tenantuuid = "";
        if (!empty($data)) {
            $tenantuuid = $data[0]['tenant_uuid'];
        }
        return $tenantuuid;
    }

    /**
     * 获取存储安全配置
     * @param unknown $params
     */
    public function getStorageProtect($params)
    {
        $sql = "select data_protect_flag from bd_system";
        $data = $this->dbSelect($sql, array());

        $info = array(
            "storageprotectcheck" => $data[0]['data_protect_flag'] == Xphp::$_config['FLAG']['SET'] ? true : false
        );
        return json_encode($info);
    }

    /**
     * 设置存储安全配置
     * @param unknown $params
     */
    public function setStorageProtect($params)
    {
        $storageprotectcheck = $params['storageprotectcheck'];
        $sql = "update bd_system set data_protect_flag = ?";
        $storageprotectcheck = $storageprotectcheck ? Xphp::$_config['FLAG']['SET'] : Xphp::$_config['FLAG']['UNSET'];
        $result = $this->dbExec($sql, array($storageprotectcheck));
        $operate = Xphp::$_lang['UI_SH_SECURITY_STORAGE'];

        if ($storageprotectcheck == Xphp::$_config['FLAG']['SET']) {
            $sysLogKey = "SYSTEM_LOG_SETTINGS_STORAGE_SAFE_ON";
        } else {
            $sysLogKey = "SYSTEM_LOG_SETTINGS_STORAGE_SAFE_OFF";
        }

        $this->unifyWriteSystemLog($result, $sysLogKey);
        return $this->muOpResult($result, $operate);
    }

    /**
     * 接收前端传过来的文件并写入后台指定文件目录中去
     * @authr liushuai@vinchin.com
     */
    public function UploadToSystem()
    {
        //检查文件名，主要是检查后缀
        $checkFileExt = preg_match("/^.*\.(zip|tar|tar.gz|rar)$/i", $_FILES['file']['name']);
        if (0 === $checkFileExt) {
            exit;
        }

        // Support CORS
        // header("Access-Control-Allow-Origin: *");
        // other CORS headers if any...
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }


        if (!empty($_REQUEST['debug'])) {
            $random = rand(0, intval($_REQUEST['debug']));
            if ($random === 0) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }


        // 5 minutes execution time
        @set_time_limit(5 * 60);


        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        $uploadDir = Xphp::$_config['UPLOAD_DIR'];
        $targetDir = Xphp::$_config['TARGET_DIR'];
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // 		@file_put_contents('./a.txt',print_r($_FILES,true),FILE_APPEND); //上传文件写入日志到a.txt

        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;

        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }


        // Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        $index = 0;
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
                break;
            }
        }
        if ($done) {
            if (!$out = @fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        }
        die($_SESSION['fileName']);
    }
    /**
     * 删除临时文件，当文件过大时候会创建切片文件
     * @authr liushuai@vinchin.com
     */
    public function empty_filetmp()
    {
        $targetDir = Xphp::$_config['TARGET_DIR'];
        if (!file_exists($targetDir)) {
            return;
        } else {
            $cmd = 'rm -rf ' . $targetDir . '/*';
            shell_exec($cmd);
        }
        ;
    }

    /**
     * ip段转换为掩码位
     * @param $tmp_net_mask
     * @return int
     */
    private function getNetmask($tmp_net_mask)
    {
        $turn = explode(".", $tmp_net_mask);
        $shi_mask = "";
        for ($i = 0; $i < 4; $i++) {
            $shi_mask .= decbin($turn[$i]);
        }
        $mask = substr_count($shi_mask, "1");
        return $mask;
    }

    /**
     * 掩码位转换成子网掩码ip
     * @param $bitcount
     * @return string
     */
    private function createNetmaskAddr($bitcount)
    {

        $netmask = str_split(str_pad(str_pad('', $bitcount, '1'), 32, '0'), 8);

        foreach ($netmask as &$element)
            $element = bindec($element);

        return join('.', $netmask);

    }

    /**
     * 保存各个节点网卡聚合配置信息
     * @param unknown $params
     * @return number
     */
    public function saveNicInfo($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $nicType = $params['nicType'];
        $cardList = $params['cardList'];

        $ipaddr = $params['nicipaddr'];
        $netmask = $params['nicnetmask'];
        $gateway = $params['nicgateway'];
        $dns = $params['nicdns'];
        $num = $params['num'];
        $prefix = $params['prefix'];
        $bondName = $params['bondname'];
        //获取网卡聚合信息
        $settingsHandler = Xphp::instance('SettingsHandler');
        $data = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['NIC']);
        $nicList = json_decode($data[0]['settings_content'], true);
        $list = $nicList['niclist'];
        $newList = array();
        //如果信息为空初始化数组
        if (!empty($list)) {
            foreach ($list as $key => $l) {
                if ($key != $nodeuuid) {
                    $newList[$key] = $l;
                }
            }
        }
        $info = array(
            'ipaddr' => $ipaddr,
            'netmask' => $netmask,
            'gateway' => $gateway,
            'dns' => $dns,
            'nictype' => $nicType,
            'cardList' => implode(",", $cardList),
            'num' => intval($num),
            'prefix' => intval($prefix),
            'bondname' => $bondName
        );
        $newList[$nodeuuid] = $info;
        $nicInfo = array(
            'niclist' => $newList
        );
        return $settingsHandler->modifySettingsInfosWithType(Xphp::$_config['SETTINGS_CONF']['NIC'], json_encode($nicInfo));
    }


    /**
     * 检查如果存在网卡聚合信息同步信息
     * @param unknown $params
     * @return boolean
     */
    private function checkSaveNicInfo($params)
    {
        $nodeuuid = $params['nodeuuid'];
        $networkName = $params['NAME'];
        $ipaddr = $params['IPADDR'];
        $netmask = $params['NETMASK'];
        $gateway = $params['GATEWAY'];
        $dns = $params['DNS'];
        //获取网卡聚合信息
        $settingsHandler = Xphp::instance('SettingsHandler');
        $data = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['NIC']);
        $nicConfig = json_decode($data[0]['settings_content'], true);
        $nicList = $nicConfig['niclist'];
        $nicinfo = $nicList[$nodeuuid];

        if (!empty($info)) {
            //找到网卡聚合的网卡同步信息
            if ($networkName == $nicinfo['bondname']) {
                $info = array(
                    'nodeuuid' => $nodeuuid,
                    'nicipaddr' => $ipaddr,
                    'nicnetmask' => $netmask,
                    'nicgateway' => $gateway,
                    'nicdns' => $dns,
                    'nicType' => $nicinfo['nictype'],
                    'cardList' => $nicinfo['cardList'],
                    'num' => $nicinfo['num'],
                    'bondname' => $nicinfo['bondname']
                );
                //保存网卡聚合信息
                $this->saveNicInfo($info);
            }
        }

        return true;
    }


    /**
     * 杀掉php.monitor监控进程
     * @return boolean
     */
    public function restartWebProcess($nodeuuid)
    {
        //获取php定义进程
        $cmd = "ps aux|grep monitor";
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        if (!$mbResult['result']) {
            return true;
        }
        //获取消息成功
        $info = $mbResult['msg']['detail'];
        $info = explode(PHP_EOL, $info);
        //如果有对应查询php进程存在
        if (!empty($info)) {
            $cmd = '';
            foreach ($info as $key => $d) {
                $i = 0;
                $list = explode(" ", trim($d));
                //依次杀掉对应php监控进程
                foreach ($list as $l) {
                    //如果不是root权限下的不管
                    if ($l == "nginx")
                        break;
                    //转换成数组后会有空格字符串，排除这些项
                    if (!empty($l)) {
                        $i++;
                    }
                    //进程号
                    if ($i == 2) {
                        $cmd .= 'kill -9 ' . $l . ';';
                        exec($cmd);
                        break;
                    }
                }
            }
            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            $msg = array('command' => $cmd);
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        }

        return $mbResult['result'];
    }


    /**
     * 生成下载统一链接
     * @param unknown $nodeuuid
     * @param unknown $filePath
     * @param boolean $deleteFlag 下载完成后删除
     */
    public function groupUnifyDownloadUrl($nodeuuid, $filePath, $deleteFlag = false, $showName = '')
    {
        $module = array_keys(Xphp::$_config['ROUTE'], "SystemHandler");
        //目前只检查用户名和文件名,之后还可以加上下载有效期等其他信息
        $ivc = array(
            'username' => Xphp::$_user['username'],
            'nodeuuid' => $nodeuuid,
            'filepath' => $filePath,
            'deleteFlag' => $deleteFlag,
            'filename' => $showName,
        );

        $utils = Xphp::instance('Utils');
        $ivc = $utils->encrype(json_encode($ivc));
        $url = "/api/?m=" . $module[0] . "&f=downloadFile&p=" . urlencode($ivc);
        return $url;
    }

    /**
     * 下载文件统一入口，功能为获取到ivc校验码，然后解析校验码，判断校验码的用户和文件，然后输出文件
     * @param unknown $ivc
     */
    public function downloadFile()
    {
        $ivc = $_REQUEST['p'];

        $utils = Xphp::instance('Utils');
        $ivc = $utils->decrypt($ivc);
        $ivc = json_decode($ivc, true);

        //检查用户名是否匹配
        if ($ivc['username'] != Xphp::$_user['username']) {
            return false;
        }

        //检查文件是否存在
        if (!file_exists($ivc['filepath'])) {
            return false;
        }

        //下载文件
        if (!empty($ivc['filename'])) {
            $filename = $ivc['filename'];
        } else {
            $filename = basename($ivc['filepath']);
        }
        $filesize = filesize($ivc['filepath']);

        //下载文件到本地
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $filesize);
        Header("Content-Disposition: attachment; filename=" . $filename);

        $opName = "NODE_SYS_OP_PREAD_FILE";
        $storageHandler = Xphp::instance('StorageHandler');
        $operate = $storageHandler->getUnifyOpcodeDes($opName);

        $blockSize = Xphp::$_config['GRAIN_FILE_BLOCK_SIZE'];
        //如果大于分块大小,分块下载
        for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
            if ($filesize - $i <= $blockSize) {
                $readLen = $filesize - $i;
            } else {
                $readLen = $blockSize;
            }
            $msg = array(
                "file_path" => $ivc['filepath'],
                "offset" => $i,
                "length" => $readLen,
            );
            $mbResult = $this->mbNodeMsg($opName, $ivc['nodeuuid'], json_encode($msg), true);
            echo $mbResult;
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }

        if (isset($ivc['deleteFlag']) && $ivc['deleteFlag']) {
            unlink($ivc['filepath']);
        }
    }


    /**
     * 还原踢出聚合的网卡信息
     * @param unknown $oldList
     * @param unknown $newList
     * @return boolean
     */
    private function reSaveCardInfo($reList, $nodeuuid, $bondName = "", $cleanflag = '')
    {
        if (empty($reList))
            return true;
        //还原
        foreach ($reList as $card) {
            $cardDes .= $card . " ";
            $cardContent = "";
            $cardPath = Xphp::$_config['NETWORKCARD']['path'] . Xphp::$_config['NETWORKCARD']['prefix'] . $card;
            $cardPathCopy = Xphp::$_config['NETWORKCARD']['path'] . Xphp::$_config['NETWORKCARD']['prefix'] . $card . '.old';

            $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
            $cardcmd = "[ -f " . $cardPath . " ] && echo yes || echo no;";
            $cardcmd .= "[ -f " . $cardPathCopy . " ] && echo yes || echo no";
            $msg = array(
                "command" => $cardcmd
            );
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            if ($mbResult['result']) {
                $fileFlag = explode("\n", trim($mbResult['msg']['detail']));
                $cmd = "";
                //如果网卡存在旧的文件，把现在文件删除, 把old还原
                if ($fileFlag[0] == "yes" && $fileFlag[1] == "yes") {
                    $cmd .= "rm -rf " . $cardPath . ";";
                    $cmd .= "mv " . $cardPathCopy . " " . $cardPath;
                } else if ($fileFlag[0] == "yes" && $fileFlag[1] == "no") {
                    if ($card == $bondName) {
                        //直接清除聚合网卡
                        $cmd .= "rm -rf " . $cardPath . ";";
                    } else {
                        //如果只存在网卡文件把绑定聚合信息移除
                        $content = "DEVICE=" . $card . PHP_EOL . "ONBOOT=yes";
                        $cmd .= "echo '" . $content . "' > " . $cardPath . ";";


                    }
                }

                $msg = array(
                    "command" => $cmd
                );
                $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);

                if ($cleanflag && $card != $bondName) {
                    //还原BOOTPROTO=none
                    $fileContent = file_get_contents($cardPath);

                    $position = strpos($fileContent, "BOOTPROTO=");
                    $line = substr($fileContent, $position, strpos($fileContent, "\n", $position) - $position);
                    $new_line = "BOOTPROTO=none";
                    $file_content = str_replace($line, $new_line, $fileContent);

                    $cmd = "echo '" . $file_content . "' > " . $cardPath;
                    $msg = array(
                        "command" => $cmd
                    );
                    $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
                }


            }

        }

        return true;

    }


    /**
     * 获取数据库代理授权信息详情
     * @return number[]|boolean[]
     */
    public function getTenantDbLisenceInfo()
    {
        $tenantHandler = Xphp::instance('TenantHandler');
        $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
        $userDes = implode("','", $userList);
        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
        $showFlag = false;
        $used = 0;
        $total = 0;
        $valid = 0;
        if ($settings['common']['authtype'] == 2) {
            $total = intval($settings['common']['db']);
            $sql = "select authorization_module from bd_agent where agent_type =1 and user_uuid in('" . $userDes . "')";
            $data = $this->dbSelect($sql);
            if (!empty($data)) {
                foreach ($data as $d) {
                    $config = json_decode($d['authorization_module'], true);
                    if ($config['database']) {
                        $used++;
                    }
                }
            }
            $valid = $total - $used;
            $showFlag = true;
        }

        $info = array(
            'total' => $total,
            'used' => $used,
            'valid' => $valid,
            'showflag' => $showFlag
        );

        return $info;
    }

    /**
     * 获取文件代理授权信息详情
     * @return number[]|boolean[]
     */
    public function getTenantFsLisenceInfo()
    {
        $tenantHandler = Xphp::instance('TenantHandler');
        $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
        $userDes = implode("','", $userList);
        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
        $showFlag = false;
        $used = 0;
        $total = 0;
        $valid = 0;
        if ($settings['common']['authtype'] == 2) {
            $total = intval($settings['common']['fs']);
            $sql = "select authorization_module from bd_agent where agent_type = 0 and user_uuid in('" . $userDes . "')";
            $data = $this->dbSelect($sql);
            if (!empty($data)) {
                foreach ($data as $d) {
                    $config = json_decode($d['authorization_module'], true);
                    if ($config['file']) {
                        $used++;
                    }
                }
            }
            $valid = $total - $used;
            $showFlag = true;
        }

        $info = array(
            'total' => $total,
            'used' => $used,
            'valid' => $valid,
            'showflag' => $showFlag
        );

        return $info;
    }

    /**
     * 得到系统安全配置选项
     */
    public function getOsSafeConfig()
    {
        //以主节点状态展示
        $nodeHandler = Xphp::instance('NodeHandler');
        $masterUUID = $nodeHandler->getLocalNodeUUID();

        // 防火墙
        $firewalldMsg = $this->getServiceStatus("firewalld", $masterUUID);
        $firewallcheck = $firewalldMsg === 0;

        // sshd
        $sshdMsg = $this->getServiceStatus("sshd", $masterUUID);
        $sshcheck = $sshdMsg === 0;

        // 获取8080和3306以及nfs和rpcbind服务
        $port8080dMsg = $this->getServiceStatus(8080, $masterUUID);
        $port8080check = $port8080dMsg === 0;

        $port3306dMsg = $this->getServiceStatus(3306, $masterUUID);
        $port3306check = $port3306dMsg === 0;

        $nfsdMsg = $this->getServiceStatus('nfs', $masterUUID);
        $rpcbinddMsg = $this->getServiceStatus('rpcbind', $masterUUID);
        $nfscheck = $nfsdMsg === 0;
        $rpcbindcheck = $rpcbinddMsg === 0;
        $temp = 3; // 默认是自定义的方式
        // 顺便在后台判断下选择的是哪种模板
        if ($firewallcheck) {
            if ($sshcheck && $port8080check && $port3306check && $nfscheck && $rpcbindcheck) {
                // 默认的模板
                $temp = 1;
            }
            if (!$sshcheck && !$port8080check && !$port3306check && !$nfscheck && !$rpcbindcheck) {
                // 安全的模板
                $temp = 2;
            }
        }

        $info = array(
            "firewallcheck" => $firewallcheck,
            "sshcheck" => $sshcheck,
            "port8080check" => $port8080check,
            "port3306check" => $port3306check,
            "nfscheck" => $nfscheck,
            "rpcbindcheck" => $rpcbindcheck,
            "temp" => $temp,
        );

        return json_encode($info);
    }

    /**
     * 设置系统安全
     * @param unknown $params
     * @return string
     */
    public function setOsSafeConfig($params)
    {
        $firewallcheck = $params['firewallcheck'];
        $sshcheck = $params['sshcheck'];
        $port8080check = $params['port8080check'];
        $port3306check = $params['port3306check'];
        $nfscheck = $params['nfscheck'];
        $rpcbindcheck = $params['rpcbindcheck'];

        //组合后台命令
        $cmd = "";
        $cmd .= $this->groupServiceCtlCMD("firewalld", $firewallcheck);
        $cmd .= $this->groupServiceCtlCMD("sshd", $sshcheck);
        $cmd .= $this->groupServiceCtlCMD(8080, $port8080check);
        $cmd .= $this->groupServiceCtlCMD(3306, $port3306check);
        $cmd .= $this->groupServiceCtlCMD("rpcbind", $rpcbindcheck);
        $cmd .= $this->groupServiceCtlCMD("nfs", $nfscheck);

        $cmd .= "firewall-cmd --reload --quiet;"; // 因为更改了端口，所以最后拼上重启防火墙命令

        //找到所有的节点,挨个发送命令
        $sql = "select bn.node_uuid from bd_node bn, bd_module_server bms where 
                bn.node_uuid = bms.node_uuid and bms.module_type = ? and bms.online_flag = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['MODULE_TYPE']['NODE'], Xphp::$_config['FLAG']['SET']));
        $result = true;
        foreach ($data as $d) {
            $opName = 'NODE_SYS_OP_DO_CMD';
            $msg = array('command' => $cmd);
            $mbResult = $this->mbNodeMsg($opName, $d['node_uuid'], json_encode($msg), true, false);
            $result = $result && $mbResult['result'];
        }

        //写系统日志
        $this->unifyWriteSystemLog($result, "SYSTEM_LOG_SETTINGS_OS_SAFE");
        return $this->muOpResult($result, Xphp::$_lang['UI_PLATFORM_SET_SYSTEM_SAFE']);

    }

    /**
     * 组装服务控制命令,除了启动和停止外,还会禁用和启用服务开机启动
     * @param unknown $serviceName  服务名
     * @param unknown $flag         状态,开启或关闭
     */
    private function groupServiceCtlCMD($serviceName, $flag)
    {
        $cmd = "";
        if ($flag) {
            //启动并开机启动服务
            // 整形的表示是端口
            if ($serviceName == '' . intval($serviceName) && intval($serviceName)) {
                $cmd = "firewall-cmd --permanent --zone public --add-port $serviceName/tcp --quiet; ";
            } elseif ($serviceName == 'rpcbind') {
                $cmd = "systemctl start rpcbind.socket;systemctl start rpcbind;
                        systemctl enable rpcbind.socket;systemctl enable rpcbind;";
            } else {
                $cmd .= "systemctl start $serviceName;systemctl enable $serviceName;";
            }
        } else {
            //关闭并禁用开机启动服务
            // 整形的表示是端口
            if ($serviceName == '' . intval($serviceName) && intval($serviceName)) {
                $cmd = "firewall-cmd --permanent --zone public --remove-port $serviceName/tcp --quiet;";
            } elseif ($serviceName == 'rpcbind') {
                $cmd = "systemctl stop rpcbind.socket;systemctl stop rpcbind;
                    systemctl disable rpcbind.socket;systemctl disable rpcbind;";
            } else {
                $cmd .= "systemctl stop $serviceName;systemctl disable $serviceName;";
            }
        }

        return $cmd;
    }

    /**
     * 上传系统升级包检查
     * @return boolean
     */
    private function checkUpgradeFile()
    {
        $files = $_FILES['file'];
        if (!empty($files)) {
            //             $this->checkUploadStatus($files['error']);
            $this->checkUpgradeFileName($files['name'], "tar.gz");
            return true;
        } else {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE']));
        }

        //开启存储保护后的检查
        $sql = "select data_protect_flag from bd_system";
        $data = $this->dbSelect($sql);
        if (!empty($data) && intval($data[0]['data_protect_flag']) == Xphp::$_config['FLAG']['SET']) {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_DATA_PROTECT_ERROR']));
        }
    }


    /**
     * 发送信息到官网得到更新信息
     */
    public function getUpdateMsgFromOnline($params)
    {
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];
        $draw = $params['draw'];
        //先获取数据
        $check = json_decode($params['getData'], true);
        //判断获取数据是否失败,如果失败并且data内容为空则返回空列表
        if (empty($params['getData']) || !$check['re'] || empty($check['data'])) {
            $records = array("data" => array());
            $records["draw"] = 0;
            $records["recordsTotal"] = 0;
            $records["recordsFiltered"] = 0;
            return json_encode($records);
        }
        ;
        //另一种情况时正常  或者  失败但是data有数据(这种一般没有下载连接) 也给出列表
        $result = $check['data']['data'];
        $records = array("data" => array());
        $records["draw"] = $params['draw'];
        $records["recordsTotal"] = count($result);
        $records["recordsFiltered"] = count($result);
        $utils = Xphp::instance('Utils');
        $i = 0;
        foreach ($result as $d) {
            $statusArray = $this->checkOnlineStatus($d['name'], $d['md5'], $d['size']);
            //这里要判断是否有下载连接
            if (empty($d['download_url'])) {
                $download_url = "";
            } else {
                $download_url = $d['download_url'];
            }
            $records["data"][] = array(
                '<input type="checkbox" name="id[]" value="' . $i . '">',
                $d['name'],
                $utils->calSize($d['size'], true),
                $d['put_time'],
                $statusArray['msg'],
                array(
                    'download_url' => $download_url,
                    'log_attention' => $d['log_attention'],
                    'remark' => $d['remark'],
                    'author' => $d['author'],
                    'size' => $d['size'],
                    'status' => $statusArray['status'],
                    'software' => $d['software'],
                    'newname' => $d['name'],
                    'md5' => $d['md5'],
                ),

            );
            $i++;

        }
        return json_encode($records);
    }


    /**
     * 主要发送信息到升级服务器获取升级的一些信息
     * @return [
     *  're': boolean 返回bool类型 成功或失败
     *  'code': int  错误编码 目前没用 预留着
     *  'msg':  string  成功返回成功 失败返回失败原因
     *  'info': array 失败返回的消息
     *  'data': array 成功返回的消息
     * ]
     */
    public function getDataFromServer()
    {
        //先检测是否能连接到外网
        if (!$this->checkPing()) {
            //先检查网络
            $thisMsg = $this->getUnityData(false, 55004, Xphp::$_lang['WEB_SETTINGS_UPDATE_PING_NETWORK_ERROR'], array(), array());
            return $this->getUnityDataOnline($thisMsg);
        }
        $msg = array(
            'm' => 1,
            'f' => 'getUpdateMsg',
            'p' => $this->getSoftwareMsg(),
        );
        $url = Xphp::$_config['UPDATE_REQUEST_URL'];
        $curl = Xphp::instance('Curl');
        //得到返回的数据
        $result = $curl->post($url, $msg);
        if (empty($result['data'])) {
            $thisMsg = $this->getUnityData(false, 55005, Xphp::$_lang['WEB_SETTINGS_UPDATE_GET_NETWORK_ERROR'], array(), array());
            return $this->getUnityDataOnline($thisMsg);
        }
        return $this->getUnityDataOnline($result['data']);
    }





    /**
     * 检测是否可以联网
     * $url 用于检测的http网址
     */
    public function checkPing()
    {
        $url = Xphp::$_config['UPDATE_CHECK_URL'];
        $check = @fopen($url, "r");
        if ($check) {
            $status = true;
        } else {
            $status = false;
        }
        return $status;
    }


    /**
     * 在线升级统一返回数据接口
     * @param boolean $flag 是否成功
     * @param int $code 错误码
     * @param string $msg 描述
     * @param array $info 失败的数据返回
     * @param array $data 成功的数据返回
     * @return Json
     */
    public function getUnityData($flag = false, $code = 0, $msg = "success", $info = array(), $data = array())
    {
        $datas = array(
            're' => $flag,
            'code' => $code,
            'msg' => $msg,
            'info' => $info,
            'data' => $data,
        );
        return $datas;
    }

    /**
     * 因为要处理错误码这种情况 所以同意信息返回到该函数处理返回提示数据 其他不变
     * @param unknown $data 该data的函数消息体为getUnityData封装好返回的消息体
     * @return json
     */
    public function getUnityDataOnline($data)
    {
        //得到的data是未转化成json的
        //只需要处理下返回的消息体msg数据
        //如果code为0 则为正常
        $utils = Xphp::instance('Utils');
        $msg = $data['msg'];
        $codeNum = intval($data['code']);
        if ($codeNum == 0) {
            $msg = $data['msg'];
        } else {
            $msg = $utils->getErrorDes($codeNum);
        }

        $datas = array(
            're' => $data['re'],
            'code' => $data['code'],
            'msg' => $msg,
            'info' => $info,
            'data' => $data,
        );
        return json_encode($datas);

    }





    /**
     * 得到状态  0为未下载 1为已下载 2为已下载百分比 3已下载百分百 4暂停  枚举类有时间提到config去
     * @param unknown $url
     * @param unknown $filename
     * @param unknown $md5
     * @param unknown $file_size
     */
    private function checkOnlineStatus($filename, $md5, $filesize)
    {
        $filesize = intval($filesize);
        //先检测文件是否存在
        $path_file = Xphp::$_config['UPDATE_DOWNLOAD_FILE_PATH'];
        $path = $path_file . $filename;
        if (file_exists($path)) {
            //如果文件存在,则得到文件的大小
            $file_size = filesize($path);
            if ($filesize == $file_size) {
                //如果大小相等
                //查询md5是否已经安装过  查询md5是否有,如果有说明已经下载此安装包
                $sql = "select count(id) as countID from bd_update_file where level1_md5 = ?";
                $resultcount = $this->dbSelect($sql, array($md5));
                if ($resultcount[0]['countID'] != 0) {
                    return array(
                        'status' => 1,
                        'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_SUCCESS'],
                    );
                } else {
                    return array(
                        'status' => 3,
                        'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_100'],
                    );
                }
            }
            return array(
                'status' => 2,
                'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_HALFWAY'] . $this->getPercentOfSize($file_size, $filesize),
            );
        } else {
            //如果文件不存在 则检查是否有.tmp临时文件 这种文件是因为中断或暂停所生成的临时下载文件
            $path_tmp = $path_file . $filename . ".tmp";
            if (file_exists($path_tmp)) {//如果临时文件存在 则为暂停
                //获取临时文件大小
                $file_size_tmp = filesize($path_tmp);
                return array(
                    'status' => 4,
                    'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_STOP'] . $this->getPercentOfSize($file_size_tmp, $filesize),
                );
            } else {
                return array(
                    'status' => 0,
                    'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_0'],
                );
            }
        }
        ;
    }

    /**
     * 刷新状态
     * @param unknown $params
     */
    public function checkStatusOfOnline($params)
    {
        $filename = $params['filename'];
        $md5 = $params['md5'];
        $filesize = $params['filesize'];

        $filesize = intval($filesize);
        //先检测文件是否存在
        $path_file = Xphp::$_config['UPDATE_DOWNLOAD_FILE_PATH'];
        $path = $path_file . $filename;
        if (file_exists($path)) {
            //如果文件存在,则得到文件的大小
            clearstatcache();
            $file_size = filesize($path);
            if ($filesize == $file_size) {
                //如果大小相等
                //查询md5是否已经安装过  查询md5是否有,如果有说明已经下载此安装包
                $sql = "select count(id) as countID from bd_update_file where level1_md5 = ?";
                $resultcount = $this->dbSelect($sql, array($md5));
                if ($resultcount[0]['countID'] != 0) {
                    $result = array(
                        'status' => 1,
                        'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_SUCCESS'],
                    );
                    return json_encode($result);
                } else {
                    $result = array(
                        'status' => 3,
                        'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_100'],
                    );

                    //如果下载完成则打开里面的文件并存入数据库
                    $infoParams = array(
                        'name' => $filename,
                        'fileSize' => $filesize,
                        'md5' => $md5,
                    );
                    //载入文件数据到数据库
                    $this->updatePatchList($infoParams);

                    return json_encode($result);
                }
            }
            $result = array(
                'status' => 2,
                'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_HALFWAY'] . $this->getPercentOfSize($file_size, $filesize),
            );
            return json_encode($result);
        } else {
            //如果文件不存在 则检查是否有.tmp临时文件 这种文件是因为中断或暂停所生成的临时下载文件
            $path_tmp = $path_file . $filename . ".tmp";
            if (file_exists($path_tmp)) {//如果临时文件存在 则为暂停
                //获取临时文件大小
                $file_size_tmp = filesize($path_tmp);
                $result = array(
                    'status' => 4,
                    'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_STOP'] . $this->getPercentOfSize($file_size_tmp, $filesize),
                );
                return json_encode($result);
            } else {
                $result = array(
                    'status' => 0,
                    'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_PER_0'],
                );
                return json_encode($result);
            }
            ;
        }
    }

    /**
     * 得到大小的百分比
     * @param unknown $fileSize
     * @param unknown $TotalSize
     * @return 百分比
     */
    public function getPercentOfSize($fileSize, $totalSize)
    {
        //先检测是否有数值
        if ($fileSize == "" || $fileSize == 0) {
            //如果检测到有文件并且大小为0的话则返回0
            return "0%";
        }
        if ($totalSize == "" || $totalSize == 0) {
            //如果检测到分母为0,则退出报错
            return "0%";
            //             exit($this->muOpResult(false, '下载升级包', "为检测升级包大小!", "warning"));
        }
        //得到 百分数
        $percent = round($fileSize / $totalSize * 100);
        return $percent . "%";

    }


    /**
     * 获取到软件的相关信息
     */
    public function getSoftwareMsg()
    {
        //软件发送的信息
        $info = array();
        //得到授权的所有数据
        $data = json_decode($this->getSystemLisenceInfo(), true);
        //IP: 用于检测用户地区 根据IP分配下载节点,
        //根据IP地址获取URL地址
        $utils = Xphp::instance('Utils');
        $info['ip'] = $utils->getClientIP();
        //测试内容: 是否是测试人员发来的请求,如果testInfo内容为空则为普通用户
        $info['testInfo'] = $this->getTestInfo();
        //1是普通用户  2是测试人员
        $path = Xphp::$_config['UPDATE_TEST_FLAG_PATH_FILE'];
        //检查文件是否存在
        if (file_exists($path)) {
            $info['file_type'] = 2;
        } else {
            $info['file_type'] = 1;
        }
        //底层架构 x86/arm
        $info['arch_type'] = Xphp::$_config['SYSTEM_INFO']['arch_type'];
        //操作系统 centos7/kylin10
        $info['os_type'] = Xphp::$_config['SYSTEM_INFO']['os_type'];
        //软件版本: 是海外版还是国内版还是其他制定版本,
        $info['oemdiy'] = Xphp::$_config['SYSTEM_INFO']['enterprise'];
        //版本号: 具体的版本号,即5.0.1这种,用于判断是否有符合条件的软件,
        $info['version'] = Xphp::$_config['SYSTEM_INFO']['version'];
        //用户软件类型: 即标准版还是企业版或者其他版本或其他
        $info['softwareversion'] = $data['software'];
        //语言
        $info['language'] = Xphp::$_config['lang'];
        //机器指纹或主板指纹
        $info['thumb'] = array(
            'type' => 1,
            'thumbprint' => $this->getThumbprintIDNoPermission(),
        );
        //授权信息,有则填写 没有则为空也行 比如结束时间
        $AuthorizationInfo = $this->getAuthorizationInfo();
        $info['authorization'] = array(
            'authflag' => $data['status'], //授权状态,1已授权,2未授权,3授权过期,4授权异常,
            'statusDes' => $data['statusDes'], //授权状态描述
            'trial_type' => $data['trial'], //1试用 2永久
            'days' => $AuthorizationInfo['days'],//授权天数,如果是-1则为永久授权,结束时间可为""
            'expireDays' => $AuthorizationInfo['expireDays'], //剩余天数
            'start_time' => $AuthorizationInfo['start_time'], //开始时间
            'end_time' => $AuthorizationInfo['end_time'], //结束时间  如果days为-1 则结束时间没用
        );
        //服务信息
        $info['server_auth'] = $data['server_auth'];
        //软件当前时间
        $info['software_time'] = date("Y-m-d H:i:s");
        $datas = array();
        $datas['encrype'] = $utils->my_encrype(json_encode($info));
        return json_encode($datas);
    }


    /**
     * 查看是否是测试人员就检查目的路径下是否有指定文件 指定文件在目的路径下为 testInfo.json
     * 如果文件存在并且文件不为空则返回内容(为测试人员)
     * 如果文件不存在或者内容为空则返回空(为用户)
     */
    public function getTestInfo()
    {
        $result = "";
        //文件路径
        $path = Xphp::$_config['UPDATE_TEST_FLAG_PATH_FILE'];
        //先检查文件是否存在
        if (file_exists($path)) {
            //如果文件存在则读取文件内容
            $testJson = file_get_contents($path);
            if (empty($testJson)) {
                return $result;
            } else {
                return $testJson;
            }
        }
        return $result;
    }




    /**
     * 获取授权的部分时间信息
     * @return array|string[]|number[]|unknown[]|fetchAll()[]
     */
    private function getAuthorizationInfo()
    {
        $sql = "select register_time, days from bd_license";
        $result = $this->dbSelect($sql);
        $info = array();
        if (!empty($result)) {
            $registerTime = $result[0]['register_time'];
            $days = $result[0]['days'];
            //开始计算剩余天数
            $dayInterval = round((time() - strtotime($registerTime)) / 3600 / 24);
            $expireDays = ($days - $dayInterval) <= 0 ? 0 : ($days - $dayInterval);
            //到期时间
            $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
            $expireTime = date("Y-m-d H:i:s", $endTimeStamp);

            $info = array(
                'start_time' => $registerTime,
                'end_time' => $expireTime,
                'days' => $days,
                'expireDays' => $expireDays,
            );
        }
        return $info;
    }


    /**
     * 得到机器指纹-唯一识别码
     * @return boolean[]|array[]|string[][]
     */
    private function getThumbprintID()
    {
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_authorization_module_download");
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        if ($result) {
            return $mbResult['msg']['thumbprint'];
        } else {
            $reInfo = array(
                're' => false,
                'msg' => array(
                    'title' => Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE'],
                    'content' => Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE_THUMB_ERROR'],
                    'level' => "warning",
                ),
                'info' => array(),
            );
            return $reInfo;
        }
    }


    /**
     * 注: 这里获取机器指纹不需要权限,检测进程需要获取指纹,这个函数只用作升级接口用
     * 得到机器指纹-唯一识别码
     * @return boolean[]|array[]|string[][]
     */
    private function getThumbprintIDNoPermission()
    {
        //权限检查
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        if ($result) {
            return $mbResult['msg']['thumbprint'];
        } else {
            $reInfo = array(
                're' => false,
                'msg' => array(
                    'title' => Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE'],
                    'content' => Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE_THUMB_ERROR'],
                    'level' => "warning",
                ),
                'info' => array(),
            );
            return $reInfo;
        }
    }



    /**
     * 远程下载指定文件
     * @param unknown $params $url下载链接  save_dir为存储路径 filename下载名
     * @return boolean|string
     */
    function downLoadOnline($params)
    {
        //下载链接
        $url = $params['url'];
        //往url中添加range参数
        $url = $this->getRangeUpdateFile($url);
        //文件夹路径
        $save_dir = Xphp::$_config['UPDATE_DOWNLOAD_FILE_PATH'];
        //文件名
        $filename = $params['filename'];
        //文件大小
        $filesize = intval($params['filesize']);
        //文件md5值
        $md5 = $params['md5'];
        //完整路径
        $path_to = $save_dir . $filename;
        //获取磁盘剩余空间大小
        $free_use_size = intval(disk_free_space($save_dir));
        //判断磁盘空间是否够用
        if ($free_use_size < $filesize * 2) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_SIZE_OVER'], "warning");
        }
        //在下载前 检测是否有临时文件 如果有临时文件则修改为下载文件名
        //得到tmp完整路径
        $path_to_tmp = $save_dir . $filename . ".tmp";
        if (file_exists($path_to_tmp)) {
            //如果文件存在 则修改为原来的文件名
            $cmdStr = "mv " . $path_to_tmp . " " . $path_to;
            //执行命令
            exec($cmdStr, $info);
        }
        //这里屏蔽检测文件类型,不做检测
        //         //获取文件的扩展名
        //         $allowDownExt = array ( 'rar', 'zip', 'png', 'txt', 'mp4', 'html', 'apk', 'webp','tar.gz');
        //         //获取文件信息
        //         $fileExt = pathinfo($filename);
        //         //检测文件类型是否允许下载
        //         if(!in_array($fileExt['extension'], $allowDownExt)) {
        //             return false;
        //         }
        //设置脚本的最大执行时间，设置为0则无时间限制
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        //获得文件大小
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        if (empty($filesize)) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_NAME_NULL'], "warning");
        }
        //针对大文件，规定每次读取文件的字节数为4096字节，直接输出数据
        $read_buffer = 4 * 1024 * 1024;
        $handle = fopen($url, 'rb');
        //分割字符串
        $urlList = explode('/update/?software=', $url);
        if (!$handle) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_GET_URL_ERROR'] . $urlList, "warning");
        }
        //总的缓冲的字节数
        $sum_buffer = 0;
        //只要没到文件尾，就一直读取
        $h = fopen($path_to, 'ab');


        if (!$h) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_PATH_NO_PERMISSON'], "warning");
        }
        //先返回消息然后再执行保存
        echo $this->muOpResult(true, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE']);
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        //是否忽视断开连接  如果为false 则断开连接后会终止脚本的执行
        ignore_user_abort(true);


        //开始保存文件,如果没有读取到文件末尾则一直接收
        while (!feof($handle)) {
            //先检测文件是否存在
            if (!file_exists($path_to)) {
                header("connection:close");
                fclose($handle);
                exit();
            }


            if (connection_aborted() || CONNECTION_NORMAL != connection_status()) {
                //                 file_put_contents('/usr/share/nginx/vinchin/tmp/upgrade/log.txt',"#状态出错#".connection_aborted()."*".connection_status()."##",FILE_APPEND | LOCK_EX);
                fclose($handle);
                exit();
            }

            $res = fread($handle, $read_buffer);
            fwrite($h, $res);
            $sum_buffer += $read_buffer;
            //检查文件是否下载完成
            //清空文件状态缓存
            clearstatcache();
            $file_download_size = filesize($path_to);
            if ($file_download_size == $filesize) {
                //如果文件下载完成 即大小相等  --如果需要更精确可对比文件md5
                //下载完成后就插入数据库

                //如果下载完成则打开里面的文件并存入数据库
                $infoParams = array(
                    'name' => $filename,
                    'fileSize' => $filesize,
                    'md5' => $md5,
                );
                //载入文件数据到数据库
                $this->updatePatchList($infoParams);

                //插入成功后关闭退出
                header("connection:close");
                fclose($handle);
                exit();
            }
        }

        //关闭句柄
        fclose($handle);

    }


    /**
     * 暂停任务的执行 通过改变文件名为 xxx.tar.gz.tmp 后面添加.tmp来关闭资源暂停下载
     * @param unknown $file_name
     * @return bool
     */
    public function pauseUpdateOnline($params)
    {
        $file_name = $params['file_name'];
        //下载文件存放路径
        $path = Xphp::$_config['UPDATE_DOWNLOAD_FILE_PATH'];
        //得到完整路径
        $path_to = $path . $file_name;
        //检测文件是否存在
        if (file_exists($path_to)) {
            //如果文件存在则修改文件名
            $cmdStr = "mv " . $path . $file_name . " " . $path . $file_name . ".tmp";
            //执行命令
            exec($cmdStr, $info);
            return $this->muOpResult(true, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_STOP'], "", "success");
        } else {
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_STOP'], "", "success");
        }

    }

    /**
     * 看情况添加range下载范围
     * @param unknown $url
     * $return 带range的url地址
     */
    private function getRangeUpdateFile($url)
    {
        //解压出url里面software的内容
        if (empty($url)) {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_URL_NULL'], "warning"));
        }
        $paramsList = explode("software=", $url);
        $paramsEncrypt = $paramsList[1];
        if (empty($paramsEncrypt)) {
            exit($this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_URL_ERROR
'], "warning"));
        }
        //解压出参数
        $utils = Xphp::instance('Utils');
        $paramsEncrypt = urldecode($paramsEncrypt);
        $params = $utils->my_decrypt($paramsEncrypt);
        //得到参数
        $updateList = json_decode($params, true);
        //得到文件名
        $file_name = $updateList['file_name'];
        //得到文件大小
        $file_size = $updateList['file_size'];
        //目前只有在任务为暂停或者未下载的时候才能点击开始下载  检测目录下是否有临时文件 如果有则修改临时文件然后开始继续下载
        //需要传输一个range下载范围
        //bytes 用来表示请求的范围，单位为字节，start 和 end 用来表示这个范围的起始位置。例如：
        //Range: bytes=20- ：获取请求中第 20 个字节之后数据；
        //Range: bytes= -50 ：获取请求中最后 50 个字节的数据；
        //Range: bytes=40-100 ：获取请求中第 40 个字节到第 100 个字节之间的数据。
        //先判断是否含有临时.tmp临时文件
        $tmpFile = $file_name . ".tmp";
        $path = Xphp::$_config['UPDATE_DOWNLOAD_FILE_PATH'];
        //得到完整路径
        $path_to = $path . $tmpFile;
        if (file_exists($path_to)) {
            //如果文件存在 则说明未下载完
            //获取到未下载完的文件大小
            $tmpFileSize = filesize($path_to);
            //获取继续下载的范围
            $range = "bytes=" . $tmpFileSize . "-";
        } else {
            //如果range为空则默认下载整个文件
            $range = "";
        }
        //向参数中添加范围参数
        $updateList['range'] = $range;
        //json编码
        $json_list = json_encode($updateList);
        //编码后加密
        $encrypt_json = $utils->my_encrype($json_list);
        //加密后进行url编码
        $urlEncode = $paramsList[0] . "software=" . urlencode($encrypt_json);
        return $urlEncode;
    }



    /**
     * 检查文件是否存在并解压得到json数据
     * @param unknown $file_name
     * 更新名称务必按照规定的结构来,否则解压会出错
     * //$file_name = "vinchin_enterprise-update-package-5.0.20.15491-to-5.0.20.16696-HOTFIX-20211222120121.tar.gz";
     *
     */
    private function checkFileExist($file_name)
    {
        $path_to = Xphp::$_config['UPDATE_DOWNLOAD_FILE_PATH'];
        //得到具体的文件路径
        $path = $path_to . $file_name;
        //指定一个存放jsonlog的位置,这个需提前创建好一个空的json文件,虽然后面代码有创建的代码 但是最好先创建一个
        $path_json = Xphp::$_config['UPDATE_TMP_LOG_FILE'];
        //先判断文件是否存在
        if (file_exists($path)) {
            //获取文件大小
            //             $utils = Xphp::instance('Utils');
            $fileSize = filesize($path);
            //检查json文件是否存在
            //如果存在则开始解压json数据到指定文件中去,在此之前先清空文件内容,FILE_USE_INCLUDE_PATH设置如果不存在则创建一个文件
            $result = file_put_contents($path_json, Xphp::$_lang['WEB_SETTINGS_UPDATE_WAIT_WRITE_JSONSTR'], FILE_USE_INCLUDE_PATH);
            //如果清空成功则等待写入内容
            if ($result) {
                //然后解压文件到指定目录
                //获取到文件夹名称
                $tarDir = substr($file_name, 0, -7);
                //执行linux解压命令 -O是以流的形式存到指定文件中去,并且文件必须存在
                $cmdStr = "tar -zxvf $path $tarDir/updateLog.json -O > $path_json";
                //执行命令
                exec($cmdStr, $info);
                //执行成功后读取文件内容
                $updateJson = file_get_contents($path_json);
                if ($updateJson == Xphp::$_lang['WEB_SETTINGS_UPDATE_WAIT_WRITE_JSONSTR']) {
                    $info = array(
                        'flag' => false,
                        'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_UNZIP_ERROR'],
                    );
                    exit(json_encode($info));
                }
                $updateLogList = json_decode($updateJson, true);
                if (empty($updateLogList)) {
                    //                     $info = array(
//                         'flag' => false,
//                         'msg' => "读取到的json数据为空,请检查json文件内容!",
//                     );
//                     exit(json_encode($info));
                    return array(
                        'updateJson' => "",
                        'fileSize' => $fileSize,
                    );
                }
                return array(
                    'updateJson' => $updateJson,
                    'fileSize' => $fileSize,
                );
            }
        } else {
            $info = array(
                'flag' => false,
                'msg' => Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_NOT_EXIST'],
            );
            exit(json_encode($info));
        }
    }


    /**
     * 删除升级包(这个函数不作为删除已下载完成的安装包  如果已下载完成在系统升级中去删除 )
     * @param unknown $params
     */
    public function deleteUpdateAPK($params)
    {
        //得到文件md5
        $md5 = $params['md5'];
        //得到文件名
        $file_name = $params['file_name'];
        //         $file_name = "vinchin_enterprise-update-package-5.0.20.15491-to-5.0.20.16696-HOTFIX-20211222120121.tar.gz";
        // 校验是否非法
        $pattern = '/^vinchin_enterprise-update-package-[a-zA-Z0-9-.]*\.tar\.gz$/';
        if (!preg_match($pattern, $file_name)) {
            // 文件名不匹配
            // 错误处理...
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_CANCEL'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_PATH_NO_PERMISSON'], "warning");
        }
        //检查安装包名称是否为空
        if (empty($file_name)) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_CANCEL'], Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_CANCEL_NAME_NULL'], "warning");
        }
        //得到安装路径
        $path_to = Xphp::$_config['UPDATE_DOWNLOAD_FILE_PATH'];
        //得到具体的文件路径
        $path = $path_to . $file_name;
        //得到临时文件路径
        $path_tmp = $path_to . $file_name . ".tmp";
        //删除的时候连同临时文件一起删除
        $cmd = "rm -rf " . $path;
        $cmd_tmp = "rm -rf " . $path_tmp;
        exec($cmd);
        exec($cmd_tmp);

        $sql = "delete from bd_update_file where level1_md5 = ?";
        $result = $this->dbExec($sql, array($md5));

        return $this->muOpResult(true, Xphp::$_lang['WEB_SETTINGS_UPDATE_DOWNLOAD_FILE_CANCEL'], "", "success");
    }


    /**
     * 设置检查更新
     */
    public function setUpdateConf($params)
    {
        //获取协议开关
        $updateTCP = $params['updateTCP'];
        //获取检查更新开关
        $updatecheck = $params['updatecheck'];
        //获取检测周期
        $updatetime = $params['updatetime'];
        //获取现在时间
        $datanow = date("Y-m-d H:i:s");
        $datalist = array(
            'checkflag' => $updatecheck,
            'TCPflag' => $updateTCP,
            'timeperiod' => $updatetime,
        );
        //封装数据成json
        $content = json_encode($datalist);
        //先检查网络是否连通
        $checkOnline = $this->checkPing();
        if (!$checkOnline) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_PING_NETWORK_ERROR'], 'warning');
        }
        //先统一删除
        $sqldelete = "delete from bd_system_settings where settings_type = 5 and user_uuid = ?";
        $resultdelete = $this->dbExec($sqldelete, array(Xphp::$_user['useruuid']));
        if (!$resultdelete) {
            return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_SETTING_FAILD'], 'warning');
        }
        ;
        //删除了后判断检查更新的开关
        if ($updatecheck) { //检查更新打开
            $sqlinsert = "insert into bd_system_settings(settings_type,settings_content,modify_time, user_uuid) values(?,?,?,?)";
            $resultinsert = $this->dbExec($sqlinsert, array(5, $content, $datanow, Xphp::$_user['useruuid']));
            if ($resultinsert) {
                return $this->muOpResult(true, Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_SETTING_SUCCESS'], 'success');
            } else {
                return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_SETTING_FAILD'], 'warning');
            }
        } else {//检查更新关闭
            if ($resultdelete) {
                return $this->muOpResult(true, Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_SETTING_SUCCESS'], 'success');
            } else {
                return $this->muOpResult(false, Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_ONLINE'], Xphp::$_lang['WEB_SETTINGS_UPDATE_CHECK_SETTING_FAILD'], 'warning');
            }
        }

    }



    /**
     * 检查升级包版本是否匹配
     * @param string $name
     */
    private function checkUpgradeVersion($name)
    {
        $list = explode("-update-package-", $name);
        //获取升级包版本号
        $listTwo = explode("-to-", $list[1]);
        $listNum = explode(".", $listTwo[0]);
        $listOne = explode("-", $listNum[0]);
        //获取当前版本号
        $version = explode("build: ", Xphp::$_config['SYSTEM_INFO']['version']);
        $versionList = explode(".", $version[1]);
        //组装当前版本号
        $currentVersion = $versionList[0] . '.' . $versionList[1] . "." . $versionList[2];
        //组装升级包版本号
        $upgradeVersion = $listOne[1] . '.' . $listNum[1] . "." . $listNum[2];
        //如果操作系统和底层架构参数存在
        if (!empty(Xphp::$_config['SYSTEM_INFO']['os_type']) && !empty(Xphp::$_config['SYSTEM_INFO']['arch_type'])) {
            //当前操作系统+底层架构
            $currentOs = Xphp::$_config['SYSTEM_INFO']['os_type'] . "-" . Xphp::$_config['SYSTEM_INFO']['arch_type'];
            //检查当前版本操作系统和底层架构和升级包是否匹配
            if (!strpos($name, $currentOs)) {
                exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR'], Xphp::$_lang['UI_SETTINGS_PATCH_NOT_MATCH'], "warning"));
            }
        }
        //检查版本是否匹配，版本号是否大于等于当前版本
        if ($list[0] != Xphp::$_config['SYSTEM_INFO']['enterprise'] || $upgradeVersion < $currentVersion) {
            exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR'], Xphp::$_lang['UI_SETTINGS_PATCH_NOT_MATCH'], "warning"));
        }
    }

    /**
     * 得到已授权的信息2.0优化版本
     * @date  2022-02-28 15:23:44
     * @param int $status       授权状态
     * @param string $statusDes 授权描述
     */
    private function getAuthorizedInfoV2($status, $statusDes)
    {
        $utils = Xphp::instance('Utils');
        $info = array(
            'status' => $status,
        );
        $sql = "select unix_timestamp(register_time) register_time, license_type, node_count,
                desktop_max, days, trial_type, software_type, user_name, extension, cdp_max, cdp_takeover_max_num, cdp_capacity_max from bd_license ";
        $data = $this->dbSelect($sql);

        //获取授权状态描述和超时时间
        $statusInfo = $this->getSystemStatusInfo($data[0], $status, $statusDes);
        //授权状态描述
        $info['statusDes'] = $statusInfo['statusDes'];
        //超时时间
        $info['expireTime'] = $statusInfo['expireTime'];
        //剩余天数
        $info['expireDays'] = $statusInfo['expireDays'];
        //1试用 2永久
        $info['trial'] = intval($data[0]['trial_type']);
        //用户名
        $info['customer'] = empty($data[0]['user_name']) ? "----" : $data[0]['user_name'];
        //软件版本
        $info['software'] = intval($data[0]['software_type']);
        if (empty($data)) {
            $info['software'] = $this->getSoftwareType();
        }
        //授权模块以及自定义版本信息
        if (json_decode($utils->decrypt($data[0]['extension']), true) == null) {
            $info['extension'] = [];
        } else {
            $info['extension'] = json_decode($utils->decrypt($data[0]['extension']), true);
        }

        //新的授权方式6.0
        $info['newStorageInfo'] = $this->getLicenseInfoDetials($info['extension']);

        //授权功能
        $info['authfun'] = $_SESSION['authfun'];

        //虚拟机要根据授权类型得到使用量
        //授权类型  host|cpu|storage|vm
        $info['vminfo'] = $this->getAuthVmInfo($data[0]);
        //跨平台虚拟化
        $info['v2v'] = $this->getAuthV2VInfo($data[0]);

        //文件保护
        $info['fileinfo'] = $this->getOneModuleLisenceInfo('file');

        //数据库保护
        $info['dbinfo'] = $this->getOneModuleLisenceInfo('database');

        //操作系统保护
        $info['osinfo'] = $this->getOneModuleLisenceInfo('os');

        //nas保护
        $info['nasinfo'] = $this->getOneModuleLisenceInfo('nas');

        //实时容灾
        $info['volcdpinfo'] = $this->getVolcdpLisenceInfo($data[0]);

        //exchange保护
        $info['exchangeinfo'] = $this->getOneModuleLisenceInfo('exchange');

        //hadoop保护
        $info['hadoopinfo'] = $this->getOneModuleLisenceInfo('hadoop');

        //对象存储保护
        $info['obsinfo'] = $this->getOneModuleLisenceInfo('obs');

        //文件保护|数据库|操作系统保护的无限制
        $info['fileinfo']['des'] = $this->getNumProtect($info['fileinfo'], $info['vminfo']['type']);
        $info['dbinfo']['des'] = $this->getNumProtect($info['dbinfo'], $info['vminfo']['type']);
        $info['osinfo']['des'] = $this->getNumProtect($info['osinfo'], $info['vminfo']['type']);
        $info['nasinfo']['des'] = $this->getNumProtect($info['nasinfo'], $info['vminfo']['type']);
        $info['exchangeinfo']['des'] = $this->getNumProtect($info['exchangeinfo'], $info['vminfo']['type']);
        $info['hadoopinfo']['des'] = $this->getNumProtect($info['hadoopinfo'], $info['vminfo']['type']);
        $info['obsinfo']['des'] = $this->getNumProtect($info['obsinfo'], $info['vminfo']['type']);
        //虚拟机保护，文件保护,数据库保护,跨平台保护,nas在存储模式下是否显示
        $info['extension']['p'] = $info['extension']['p'] ?? [];
        in_array('db_protect', $info['extension']['p']) ? $info['dbinfo']['showFlag'] = true : $info['dbinfo']['showFlag'] = false;
        in_array('fileprotect', $info['extension']['p']) ? $info['fileinfo']['showFlag'] = true : $info['fileinfo']['showFlag'] = false;
        in_array('vmprotect', $info['extension']['p']) ? $info['vminfo']['showFlag'] = true : $info['vminfo']['showFlag'] = false;
        $info['extension']['f']['crossHypervisorRecovery'] ? $info['v2v']['showFlag'] = true : $info['v2v']['showFlag'] = false;
        in_array('os_protect', $info['extension']['p']) ? $info['osinfo']['showFlag'] = true : $info['osinfo']['showFlag'] = false;
        in_array('nas_protect', $info['extension']['p']) ? $info['nasinfo']['showFlag'] = true : $info['nasinfo']['showFlag'] = false;
        in_array('vol_cdp_protect', $info['extension']['p']) ? $info['volcdpinfo']['showFlag'] = true : $info['volcdpinfo']['showFlag'] = false;
        in_array('office365_protect', $info['extension']['p']) ? $info['exchangeinfo']['showFlag'] = true : $info['exchangeinfo']['showFlag'] = false;
        in_array('hadoop_protect', $info['extension']['p']) ? $info['hadoopinfo']['showFlag'] = true : $info['hadoopinfo']['showFlag'] = false;
        in_array('obs_protect', $info['extension']['p']) ? $info['obsinfo']['showFlag'] = true : $info['obsinfo']['showFlag'] = false;
        //数存主机保护
        $info['dbtiming'] = $this->getAuthDatappInfo($status, $info['extension']);
        in_array('dbtiming', $info['extension']['p']) ? ($info['dbtiming']['showFlag'] = true) : ($info['dbtiming']['showFlag'] = false);


        //数据库CDP和文件同步
        $info['file_cdp_auth'] = $this->getAuthCDPInfo($status, $info['extension'], 'file');
        $info['data_cdp_auth'] = $this->getAuthCDPInfo($status, $info['extension'], 'data');
        in_array('filecdpbackup', $info['extension']['p']) ? $info['file_cdp_auth']['showFlag'] = true : $info['file_cdp_auth']['showFlag'] = false;
        in_array('dbcdpbackup', $info['extension']['p']) ? $info['data_cdp_auth']['showFlag'] = true : $info['data_cdp_auth']['showFlag'] = false;

        //授权服务信息
        $info['server_auth'] = $this->getAuthServerInfo($info['extension']);

        //主控节点信息,主要用于国内展示用,合同里面会签
        $info['mastinfo'] = array(
            'mastflag' => true,
            'master_num' => 1,  //主节点默认1个
            'node_num' => intval($data[0]['node_count']),  //节点个数
            'mastdes' => Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . '1',
        );


        //添加EDS容量授权标志
        $info['edslic'] = $this->getEDSLicenseFlag();
        $info['sncode'] = $this->getMachinSNCode();
        $info['edsShowFlag'] = true;	//是否显示EDS的内容
        //如果不显示标记文件存在，返回未授权，JS要用这个控制显示不显示
        $flagFilePath = Xphp::$_config['TMP_PATH'] . "noeds";
        if (!file_exists($flagFilePath)) {
            $info['edsShowFlag'] = false;	//是否显示EDS的内容
        }
        return json_encode($info);
    }

    /**
     * 获取实时容灾的授权显示
     * @return string[]
     */
    private function getVolcdpLisenceInfo($data)
    {
        $utils = Xphp::instance('Utils');
        $extension = json_decode($utils->decrypt($data['extension']), true);
        $volcdpLisenceType = $extension['volcdpLisenceType'];


        $homepageHandler = Xphp::instance('HomepageHandler');

        //获取接管使用量
        $takeoverUsedNum = $homepageHandler->getVolcdpTakeoverNum();

        $volcdpLisenceTypeConf = Xphp::$_config['VOLCDP_LISENCE_TYPE'];
        if ($volcdpLisenceType == $volcdpLisenceTypeConf['NUM']) {
            //数量授权
            $module = Xphp::$_config['MODULE_TYPE']['VOL_CDP'];
            $productUsed = $homepageHandler->getVolcdpProtectHost($module);      //使用的主机数
            $productDes = $productUsed . "/" . $data['cdp_max'];
        } else {
            //容量授权
            $productDes = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
        }

        $info = array(
            'productDes' => $productDes,
            'takeoverDes' => $takeoverUsedNum . "/" . $data['cdp_takeover_max_num'],
        );
        return $info;
    }

    /**
     * 得到页面授权信息显示信息
     * @param unknown $extension
     */
    private function getLicenseInfoDetials($extension)
    {
        $licenseBigTypeConf = Xphp::$_config['LICENSE_BIG_TYPE'];
        $volcdpLisenceTypeConf = Xphp::$_config['VOLCDP_LISENCE_TYPE'];

        //这里需要判断只有定时备份，和只有实时容灾的情况，两者都有就需要不同的显示
        //先检查是哪种显示方式
        $timingPageArr = array('vmprotect', 'fileprotect', 'db_protect', 'os_protect', 'nas_protect', 'office365_protect', 'hadoop_protect', 'obs_protect');
        $realtimePageArr = array('vol_cdp_protect');

        $allPage = $extension['p'] ?? [];                             //所有授权的页面
        $licenseBigType = $extension['licenseBigType'];         //授权容量/数量类型
        $volcdpLisenceType = $extension['volcdpLisenceType'];  //CDP授权方式

        $timingFlag = false;
        $reltimeFlag = false;

        //检查是否有定时,有一个就算有
        foreach ($timingPageArr as $timingPage) {
            if (in_array($timingPage, $allPage)) {
                $timingFlag = true;
                break;
            }
        }
        //检查是否有实时,目前只有一个,后面可能会扩展,所以也做成了数组
        foreach ($realtimePageArr as $realtimePage) {
            if (in_array($realtimePage, $allPage)) {
                $reltimeFlag = true;
                break;
            }
        }

        $sql = "select cdp_max, license_type, storage_count, cdp_takeover_max_num, cdp_capacity_max from bd_license";
        $data = $this->dbSelect($sql, array());
        $timingStorage = $data[0]['storage_count'];
        $realtimeStorage = $data[0]['cdp_capacity_max'];
        $realtimeNum = $data[0]['cdp_max'];
        $realtimeTakeover = $data[0]['cdp_takeover_max_num'];

        $utils = Xphp::instance('Utils');

        $timingStorageDes = "";
        $realtimeStorageDes = "";

        $homepageHandler = Xphp::instance('HomepageHandler');

        if ($timingFlag && !$reltimeFlag) {
            //只有定时,用定时存储容量当总容量
            if ($licenseBigTypeConf['NUM'] == $licenseBigType || null == $licenseBigType) {
                //数量授权,null值兼容6.0tmp2以前授权数据
                $timingStorageDes = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
            } elseif ($licenseBigTypeConf['STORAGE'] == $licenseBigType) {
                //容量授权
                $usedSize = $this->getAllTimingUseStorageSize();
                $totalSize = $utils->calSize($timingStorage, true);
                $timingStorageDes = $usedSize['des'] . "/" . $totalSize;

            }
        }
        if (!$timingFlag && $reltimeFlag) {
            //只有实时,用实时存储容量当总容量,判断CDP的授权方式显示
            if ($volcdpLisenceTypeConf['NUM'] == $volcdpLisenceType || null == $volcdpLisenceType) {
                //数量授权
                $realtimeStorageDes = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
            } else {
                //容量授权
                $usedSize = $homepageHandler->getVolcdpProtectData();
                $totalSize = $utils->calSize($realtimeStorage, true);
                $realtimeStorageDes = $usedSize['des'] . "/" . $totalSize;
            }
        }
        if ($timingFlag && $reltimeFlag) {
            //两者都有
            if ($licenseBigTypeConf['NUM'] == $licenseBigType || null == $licenseBigType) {
                //数量授权
                $timingStorageDes = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
                if ($volcdpLisenceTypeConf['NUM'] == $volcdpLisenceType || null == $volcdpLisenceType) {
                    //数量授权
                    $realtimeStorageDes = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
                } else {
                    //容量授权
                    $usedSize = $homepageHandler->getVolcdpProtectData();
                    $totalSize = $utils->calSize($realtimeStorage, true);
                    $realtimeStorageDes = $usedSize['des'] . "/" . $totalSize;
                }
            } elseif ($licenseBigTypeConf['STORAGE'] == $licenseBigType) {
                //容量授权
                $usedSize = $this->getAllTimingUseStorageSize();
                $totalSize = $utils->calSize($timingStorage, true);
                $timingStorageDes = $usedSize['des'] . "/" . $totalSize;
                if ($volcdpLisenceTypeConf['NUM'] == $volcdpLisenceType || null == $volcdpLisenceType) {
                    //数量授权
                    $realtimeStorageDes = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
                    $usedSizeInt = "-1";
                    $totalSize = "-1";
                } else {
                    //容量授权
                    $usedSize = $homepageHandler->getVolcdpProtectData();
                    $totalSize = $utils->calSize($realtimeStorage, true);
                    $realtimeStorageDes = $usedSize['des'] . "/" . $totalSize;
                    $usedSizeInt = intval($usedSize['size']);
                    $totalSize = intval($realtimeStorage);
                }
            }
        }

        $info = array(
            'timingFlag' => $timingFlag,
            'reltimeFlag' => $reltimeFlag,
            'timingStorageDes' => $timingStorageDes,
            'realtimeStorageDes' => $realtimeStorageDes,
            'licenseBigType' => $licenseBigType,
            'usedSizeInt' => $usedSizeInt,
            'totalSize' => $totalSize,
        );


        return $info;
    }

    /**
     * 获取所有定时备份使用的备份空间,不算副本和归档
     */
    private function getAllTimingUseStorageSize()
    {
        //         'BACKUP' => 1,              //备份
//         'DB_BACKUP' => 28,              //数据库备份
//         'VOL_CDP_BACKUP' => 32, 		//卷CDP备份
//         'OS_BACKUP' => 35,  //OS备份

        $sql = "select sum(write_size) as size from bd_backup_timepoint where task_type = ? or task_type = ? or task_type = ?";
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $sqlParams = array(
            $taskTypeConf['BACKUP'],
            $taskTypeConf['DB_BACKUP'],
            // $taskTypeConf['VOL_CDP_BACKUP'],
            $taskTypeConf['OS_BACKUP'],
        );
        $data = $this->dbSelect($sql, $sqlParams);

        $size = $data[0]['size'];
        $utils = Xphp::instance('Utils');

        $info = array(
            'value' => $size,
            'des' => $utils->calSize($size, true)
        );

        return $info;
    }

    /**
     * 如果授权是存储模式 文件保护与数据库保护为无限制
     * @param array $data       //文件保护或数据库数据
     * @param int $flag       //授权状态
     * @return string[]|mixed[]
     */
    private function getNumProtect($data, $flag)
    {
        if ($flag == 3) {
            $des = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
        } else {
            $des = $data['used'] . "/" . $data['total'];
        }
        return $des;
    }



    /**
     * 获取系统授权状态信息及授权到期时间
     * @param array $data       //系统授权信息
     * @param int $status       //授权状态
     * @param string $statusDes //授权状态描述
     * @return string[]|mixed[]
     */
    private function getSystemStatusInfo($data, $status, $statusDes)
    {
        //授权时间
        $registerTime = $this->parseDate($data['register_time']);
        //授权天数
        $days = $data['days'];
        //到期时间
        $expireTime = "----";
        //剩余天数
        $expireDays = 0;

        //授权状态
        $authFlag = Xphp::$_config['LISENCE_INFO']['authflag'];
        if ($status == $authFlag['authorized']) {
            //已授权
            $trialType = intval($data['trial_type']);
            if ($trialType == Xphp::$_config['TRIAL_TYPE']['TRIAL']) {
                $statusDes = Xphp::$_lang['WEB_SYSTEM_TRIAL_AUTHIORIZED'];
            } elseif ($trialType == Xphp::$_config['TRIAL_TYPE']['NORMAL']) {
                $statusDes = Xphp::$_lang['WEB_SYSTEM_FORMAL_AUTHIORIZED'];
                //海外版正式授权+有时间限制描述改成订阅授权
                if ($_SESSION['language'] != "zh-cn" && $_SESSION['language'] != "zh-tw" && "-1" != $days) {
                    $statusDes = Xphp::$_lang['WEB_SYSTEM_SUBSCRIPTION_AUTHIORIZED'];
                }
            }

            //剩余天数 等于 授权天数 - 授权时间到当前时间的天数
            if ("-1" == $days) {
                $expireDays = Xphp::$_lang['WEB_SYSTEM_AUTH_FOREVER'];
                $expireTime = Xphp::$_config['TIMESPACE'];
            } else {
                $dayInterval = round((time() - strtotime($registerTime)) / 3600 / 24);
                $expireDays = ($days - $dayInterval) <= 0 ? 0 : ($days - $dayInterval);
                //到期天数等于注册时间+授权天数转换成时间
                $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
                $expireTime = date("Y-m-d H:i:s", $endTimeStamp);
            }
        } elseif ($status == $authFlag['expire'] || $status == $authFlag['invalid']) {
            //到期天数等于注册时间+授权天数转换成时间
            $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
            $expireTime = date("Y-m-d H:i:s", $endTimeStamp);
        }

        $info = array(
            'expireTime' => $expireTime,
            'statusDes' => $statusDes,
            'expireDays' => $expireDays
        );

        return $info;
    }

    /**
     * 获取虚拟机授权信息
     * @param array $data
     * @return array
     */
    private function getAuthVmInfo($data)
    {
        $info = array();
        $licenseType = intval($data['license_type']);
        $vmTypeName = array('', 'host', 'cpu', 'storage', 'vm');
        $info = $this->getOneModuleLisenceInfo($vmTypeName[$licenseType], $licenseType);
        $info = $this->filterModuleInfo($licenseType, $info);
        $info['des'] = $info['used'] . "/" . $info['total'];

        //如果是容量授权,直接不限制
        if ($licenseType == Xphp::$_config['LISENCE_INFO']['type']['storage']) {
            $info['des'] = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
        }

        return $info;
    }

    /**
     * 获取跨平台恢复虚拟化授权信息
     * @param array $data 授权表信息
     * @return mixed|number[]|string[]
     */
    private function getAuthV2VInfo($data)
    {
        $info = array();
        //跨平台虚拟化
        $v2vMaxNum = intval($data['desktop_max']); //跨平台虚拟化最多数量
        $v2vUsedNum = $this->getV2VUsedNum();
        $info = array(
            'total' => $v2vMaxNum,
            'used' => $v2vUsedNum,
            'valid' => $v2vMaxNum - $v2vUsedNum,
            'v2vdes' => Xphp::$_lang['UI_SETTINGS_AUTH_NUM_DES'] . $v2vUsedNum . "/" . $v2vMaxNum
        );
        //如果是不限制，修改描述
        if ("-1" == $v2vMaxNum) {
            $info['des'] = Xphp::$_lang['UI_SETTINGS_AUTH_UNLIMITED'];
        } else {
            $info['des'] = $info['used'] . '/' . $info['total'];
        }

        return $info;
    }

    /**
     * 获取数存主机保护返回授权信息
     * @param intval $status  备份系统授权状态
     * @return array|string[]
     */
    private function getAuthDatappInfo($status, $extension)
    {
        $info = [];

        //获取 数据库定时信息
        if ($extension['dbtiminglic'] && $status == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {
            $dbTimingHandler = Xphp::instance('DBTimingHandler');
            if (!$dbTimingHandler->authKey || time() > $dbTimingHandler->authTime) {
                $dbTimingHandler->getDBAuth(); //获取datapp认证
            }
            $dbTimingInfo = $dbTimingHandler->getDBLisenceInfo(); //获取datapp授权信息
            if (!empty($dbTimingInfo['agent'])) {
                $database_size = 0;
                $database_free = 0;
                foreach ($dbTimingInfo['agent'] as $agent) {
                    $database_size += $agent['size'];
                    $database_free += $agent['free'];
                }
                if ($database_size != 0) {
                    $database = ($database_size - $database_free) . '/' . $database_size;
                    $info = array(
                        'database' => $database
                    );
                }
            }
        }

        return $info;
    }

    /**
     * 获取数据库CDP和文件同步信息
     * @param intval $status 备份系统授权状态
     * @return array|string[]|mixed[]|number[][]|unknown[]
     */
    private function getAuthCDPInfo($status, $extension, $flag)
    {
        $info = [];
        //获取数据库实时信息
        if ($extension['dbcdplic'] && $status == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']) {

            //            $hostip = Xphp::$_config['DB_CDP_LICENSE_IP'];
            $hostip = $_SERVER['SERVER_ADDR'];
            $rpc = Xphp::instance('DbRPCHandler');
            $data = array();
            //是否获取cdp授权信息
            $result = $rpc->getLicenseCenterAuthorInfo($hostip, $data);
            if ($result['result']) {
                $cdpInfo = $this->getDBcdpLicenseInfo();
                $producthost = Xphp::$_config['NULLSPACE'];
                $standbyhost = Xphp::$_config['NULLSPACE'];
                $fsproducthost = Xphp::$_config['NULLSPACE'];
                $fsstandbyhost = Xphp::$_config['NULLSPACE'];
                if ($cdpInfo['total']['producthost'] != 0) {
                    $producthost = $cdpInfo['used']['producthost'] . '/' . $cdpInfo['total']['producthost'];
                }
                if ($cdpInfo['total']['standbyhost'] != 0) {
                    $standbyhost = $cdpInfo['used']['standbyhost'] . '/' . $cdpInfo['total']['standbyhost'];
                }
                if ($cdpInfo['total']['takeover'] != 0) {
                    $takeover = $cdpInfo['used']['takeover'] . '/' . $cdpInfo['total']['takeover'];
                }
                if ($cdpInfo['total']['fsproducthost'] != 0) {
                    $fsproducthost = $cdpInfo['used']['fsproducthost'] . '/' . $cdpInfo['total']['fsproducthost'];
                }
                if ($cdpInfo['total']['fsstandbyhost'] != 0) {
                    $fsstandbyhost = $cdpInfo['used']['fsstandbyhost'] . '/' . $cdpInfo['total']['fsstandbyhost'];
                }
                $cdpendtime = $cdpInfo['endtime'];
                if (!empty($cdpendtime)) {
                    if ($flag == 'file') {
                        $info = array(
                            'fsproducthost' => $fsproducthost,
                            'fsstandbyhost' => $fsstandbyhost,
                        );
                    } else if ($flag == 'data') {
                        $info = array(
                            'endtime' => $cdpendtime,
                            'producthost' => $producthost,
                            'standbyhost' => $standbyhost,
                            'takeover' => $takeover,
                        );
                    }
                }
            }
        }

        return $info;

    }

    /**
     * 获取服务授权信息
     * @return boolean[]|NULL[]|mixed[]|unknown[]
     */
    private function getAuthServerInfo($extension)
    {
        $settingsHandler = Xphp::instance('SettingsHandler');
        //获取服务授权文件信息
        $data = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['SERVICE']);
        if (!empty($data)) {
            $utils = Xphp::instance('Utils');
            $serverInfo = json_decode($utils->decrypt($data[0]['settings_content']), true);
        }
        $info = array(
            'serverType' => Xphp::$_config['NULLSPACE'],
            'serverTime' => Xphp::$_config['NULLSPACE'],
            'serviceFlag' => false,
        );
        if (!empty($serverInfo)) {
            $serverDes = Xphp::$_config['LISENCE_INFO']['servertype'][$serverInfo['serviceType']];
            if (!empty($serverInfo['serviceDiyName'])) {
                $serverDes = $serverInfo['serviceDiyName'];
            }
            $info = array(
                'serverType' => $serverDes,
                'serverTime' => $serverInfo['serviceTime'],
                'serviceFlag' => $serverInfo['serviceFlag'],
            );
        }

        return $info;
    }



    /**
     * 获取评论
     * @param unknown $params
     */
    public function getUserComment($params)
    {
        $systemInfo = Xphp::$_config['SYSTEM_INFO'];
        $enterprise = Xphp::$_config['ENTERPRISE'];
        $systemInfo['enterprise'] == $enterprise['enterprise_en'] ? $info = Xphp::$_comment['comment_EN'] : $info = Xphp::$_comment['comment_ZH'];
        return json_encode($info);
    }

    /**
     * 检查虚拟实验室状态，部署中/修改中不能升级
     * @param string $labuuid
     * @param string $operate
     */
    private function checkLabStatus()
    {
        $sql = "select proxy_status from sr_virtual_lab ";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            foreach ($data as $d) {
                //状态部署中不能升级
                if (intval($d['proxy_status']) == Xphp::$_config['VIRTUAL_LAB_STATUS']['DEPLOYMENT'] || intval($d['proxy_status']) == Xphp::$_config['VIRTUAL_LAB_STATUS']['MODIFY']) {
                    exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START'], Xphp::$_lang['UI_VIRTUAL_LAB_DEPLOY_UPGRADE_TIPS'], "warning"));
                }
            }
        }
    }

    /**
     * 获取主节点IP即备份系统IP
     * @return string
     */
    public function getMasterNodeIp()
    {
        $ip = '';
        $sql = "select ip from bd_node where node_type = ? limit 1";
        $data = $this->dbSelect($sql, [Xphp::$_config['NODETYPE']['MASTER']]);
        if (!empty($data)) {
            $ip = $data[0]['ip'];
        }
        return $ip;
    }

    /**
     * 获取备份系统IP链接，邮件使用
     * @return string
     */
    public function getMasterNodeIpLink()
    {
        $ip = $this->getMasterNodeIp();
        $serverIpArr = explode(' ', $ip);
        $host = 'https://' . $serverIpArr[0];
        return '<a class="font-success" style="text-decoration: none" href="' . $host . '">' . $host . '</a>  ';
    }

    /**
     * 获取配置文件的company_email
     * @return string
     */
    public function getCompanyEmail()
    {
        $configFile = Xphp::$_config['SPECIAL_DIR'] . Xphp::$_config['SPECIAL_CONFIG'];
        $email = Xphp::$_config['SYSTEM_INFO']['company_email'];
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            $content = json_decode($content, true);
            $email = !empty($content['SYSTEM_INFO']['company_email']) ? $content['SYSTEM_INFO']['company_email'] : Xphp::$_config['SYSTEM_INFO']['company_email'];
        }
        return $email;
    }

    /**
     * 容灾演练平台配置获取
     * @param unknown $params
     * @return string
     */
    public function getDefaultRecoveryInfo($params)
    {
        $recoveryConfs = array();

        $settingsHandler = Xphp::instance('SettingsHandler');
        $recoveryConf = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['PLATFORM_SYSTEM_RECOVERY']);
        if (!empty($recoveryConf)) {
            $recoveryConfs = json_decode($recoveryConf[0]['settings_content'], true);
        }

        return json_encode($recoveryConfs);
    }

    /**
     * 容灾演练平台配置设置
     * @param unknown $params
     * @return string
     */
    public function setRecoveryInfo($params)
    {
        $httpType = $params['httpType'];
        $ipaddr = $params['ipaddr'];

        $settingsContent = array(
            'httpType' => $httpType,
            'ipaddr' => $ipaddr,
        );
        $settingsContent = json_encode($settingsContent);

        $settingsHandler = Xphp::instance('SettingsHandler');

        $recoveryConf = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['PLATFORM_SYSTEM_RECOVERY']);
        if (!empty($recoveryConf)) {
            $result = $settingsHandler->modifySettingsInfosWithType(Xphp::$_config['SETTINGS_CONF']['PLATFORM_SYSTEM_RECOVERY'], $settingsContent);
            if (!$result) {
                return $this->muOpResult(false, Xphp::$_lang['UI_EXERCISE_PLATFORM'], "", 'warning');
            }
        } else {
            $result = $settingsHandler->addSettingsInfos(Xphp::$_config['SETTINGS_CONF']['PLATFORM_SYSTEM_RECOVERY'], $settingsContent);
            if (!$result) {
                return $this->muOpResult(false, Xphp::$_lang['UI_EXERCISE_PLATFORM'], "", 'warning');
            }
        }

        $this->systemLog('SYSTEM_LOG_EXERCISE_PLATFORM', array("'" . $httpType . $ipaddr . "'"));
        return $this->muOpResult(true, Xphp::$_lang['UI_EXERCISE_PLATFORM_MSG']);
    }

    //----------------------------------------华为新增------------------------------------------
    /**
     * 得到存储设备自动导入时间点时间
     * @param array params
     */
    public function getImportRefreshTime()
    {
        $utils = Xphp::instance('Utils');
        $sql = "select timepoint_import_interval from bd_system";
        $result = $this->dbSelect($sql);
        $info = array(
            'storage_import_sync_interval' => intval($result[0]['timepoint_import_interval']) / 60,
        );
        return json_encode($info);
    }
    /**
     * 设置存储设备自动导入时间点时间
     * @param array params
     */
    public function setImportRefreshTime($params)
    {
        $refreshvalue = intval($params['value']) * 60;
        $sql = "update bd_system set timepoint_import_interval = ?";
        $result = $this->dbExec($sql, array($refreshvalue));
        return $result;
    }
    //----------------------------------------华为结束------------------------------------------

    /**
     * 上传升级包检查系统空间是否足够满足上传
     * @param $params
     */
    public function checkSystemSpaceEnough($params)
    {
        $fileSize = $params['filesize'];
        $fileName = $params['filename'];
        $filePath = Xphp::$_config['UPLOAD_PATH'];
        $this->checkPatchExist($fileName); //检查文件是否存在
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $msg = array(
            'file_size' => $fileSize,
            'upload_path' => $filePath
        );
        $opName = "NODE_SYS_OP_PATCH_UPLOAD_CHECK";
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true);
        $opcodeDes = $this->nodeOpcodeHandler->getOpcodeDes($opName);
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $opcodeDes, "", "warning", $mbResult['errorCode']);
        } else {
            return $this->muOpResult(true, $opcodeDes);
        }

    }

    /**
     * 能耗监控平台配置获取
     * @param unknown $params
     * @return string
     */
    public function getCarbonMonitorInfo($params)
    {
        $carbonMonitorInfo = array();
        $settingsHandler = Xphp::instance('SettingsHandler');
        $carbonMonitorInfo = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['PLATFORM_SYSTEM_CARBON_MONITOR']);
        if (!empty($carbonMonitorInfo)) {
            $carbonMonitorInfo = json_decode($carbonMonitorInfo[0]['settings_content'], true);
        }
        return json_encode($carbonMonitorInfo);
    }

    /**
     * 能耗监控平台配置
     * @param unknown $params
     * @return string
     */
    public function setCarbonMonitorConf($params)
    {
        $httpType = $params['httpType'];
        $ipaddr = $params['ipaddr'];
        $configCarbonFlag = $params['config_carbon_flag'];
        $settingsContent = array(
            'httpType' => '',
            'ipaddr' => '',
            'config_carbon_flag' => $configCarbonFlag,
        );
        if ($configCarbonFlag) {//能耗监控开启才检测
            $settingsContent = array(
                'httpType' => $httpType,
                'ipaddr' => $ipaddr,
                'config_carbon_flag' => $configCarbonFlag,
            );
            //测试是否能联通
            $url = $httpType . $ipaddr . ':/YQ';
            $data = json_encode([
                'apikey' => 'PCo87AuFXHGjnf7PfKsjYYtpGsni9jlS'
            ]);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 返回结果而不是直接输出
            curl_setopt($ch, CURLOPT_POST, true); // 发送 POST 请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data); // 设置 POST 字段
            curl_setopt($ch, CURLOPT_HTTPHEADER, [ // 设置 HTTP 头
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20); // 设置超时时间为 30 秒
            $response = curl_exec($ch);
            if (!$response) {
                return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_CARBON_MONITOR_PLATFORM_WARN'], Xphp::$_lang['UI_SETTINGS_CARBON_MONITOR_PLATFORM_WARN_TIPS'], 'warning');
            }
        }
        $settingsContent = json_encode($settingsContent);
        $settingsHandler = Xphp::instance('SettingsHandler');
        $carbonMonitorInfo = $settingsHandler->getSettingsInfos(Xphp::$_config['SETTINGS_CONF']['PLATFORM_SYSTEM_CARBON_MONITOR']);
        if (!empty($carbonMonitorInfo)) {
            $result = $settingsHandler->modifySettingsInfosWithType(Xphp::$_config['SETTINGS_CONF']['PLATFORM_SYSTEM_CARBON_MONITOR'], $settingsContent);
            if (!$result) {
                return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_CARBON_MONITOR_PLATFORM'], "", 'warning');
            }
        } else {
            $result = $settingsHandler->addSettingsInfos(Xphp::$_config['SETTINGS_CONF']['PLATFORM_SYSTEM_CARBON_MONITOR'], $settingsContent);
            if (!$result) {
                return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_CARBON_MONITOR_PLATFORM'], "", 'warning');
            }
        }
        $this->systemLog('SYSTEM_LOG_CARBON_MONITOR_PLATFORM', array("'" . $httpType . $ipaddr . "'"));
        return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_CARBON_MONITOR_PLATFORM_MSG']);
    }

}

?>