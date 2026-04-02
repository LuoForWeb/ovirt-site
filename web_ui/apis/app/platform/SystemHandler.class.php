<?php
/******************************************* 
** 系统管理处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2015-06-25 下午15:16:44 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
class SystemHandler extends OPHandler{
    private $opcodeHandler;
    
    function __construct(){
        //初始化消息等级
        $this->opcodeHandler = Xphp::instance('PFOpcodePrivate');
    }
    
    /**
     * 获取系统指纹信息(验证)
     */
    public function getThumbprint(){
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        if($result){
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($msg));
            Header("Content-Disposition: attachment; filename=". Xphp::$_config['LISENCE_INFO']['thumbprintFileName']);
            return $msg;
        }else{
            $operate = $this->opcodeHandler->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 获取系统指纹信息文件
     */
    public function getThumbprintFile(){
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, null, true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        $msg .= "\r\n" . date("Y-m-d H:i:s", $this->getSystemTime());
        $msg .= "\r\n" . Xphp::$_config['SYSTEM_INFO']['enterprise'] . $this->getThumbprintEnterprise() . " " . Xphp::$_config['SYSTEM_INFO']['version'];
        $msg .= "\r\n" . Xphp::$_config['SYSTEM_INFO']['company'];
        if($result){
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($msg));
            Header("Content-Disposition: attachment; filename=". Xphp::$_config['LISENCE_INFO']['thumbprintFileName']);
            return $msg;
        }else{
            $operate = $this->opcodeHandler->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 得到软件版本信息
     */
    private function getThumbprintEnterprise(){
        $des = "";
        $softType = $this->getSoftwareType();
        if(Xphp::$_config['SOFTWARE_VERSION']['FREE_EDITION'] == $softType){
            //如果是免费版本
            $softType = " free edition ";
        }
        return $des;
    }
    
    /**
     * 上传LISENCE文件
     */
    public function uploadLisence(){
        $this->checkUploadFile();
        $key = file_get_contents($_FILES['files']['tmp_name']);
        return $this->submitLisenceKey($key);
    }
    
    /**
     * 选择新的LOGO文件
     * @param unknown $params
     */
    public function selectLogo($params){
        $uploadfile = Xphp::$_config['LOGO_INFO']['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        $operate = Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'];
        if($files){
            //检测上传文件状态
            if(0 != $files['error']){
                exit($this->muOpResult(false, $operate));
            }
            //检测文件后缀名
            $arr = explode(".", $files['name']);
            $suffixesArr = explode("|", $uploadfile['suffixes']);
            if(!in_array($arr[count($arr) - 1], $suffixesArr)){
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_LOGO_EXT'], 'error'));
            }
            //检测上传文件大小
            if($files['size'] > $uploadfile['size']){
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_LOGO_SIZE'], 'error'));
            }
            //保存临时文件
            $count = file_put_contents(Xphp::$_config['TMP_PATH_LOGO'], file_get_contents($files['tmp_name']));
            if($count > 0){
                return $this->muOpResult(true, $operate, '', 'success', 0, Xphp::$_config['TMP_PATH_RE_LOG']);
            }
            exit($this->muOpResult(false, $operate));
        }else {
            exit($this->muOpResult(false, $operate));
        }
        
    }
    
    /**
     * 保存上传的LOGO
     * @param unknown $params
     */
    public function setNewLogo($params){
        $tmpFile = $params['file'];
        $this->paramsCheck($tmpFile);
        $cmd = "mv -f " . Xphp::$_config['TMP_PATH_LOGO'] . " " . Xphp::$_config['LOGO_PATH'];
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_LOGO');
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_SYSTEM_SET_DIY_LOGO']);
    }
    
    /**
     * 添加宿主机授权
     * @param unknown $params
     */
    public function addHostAuth($params){
        $hosts = $params['hosts'];
        $this->paramsCheck($hosts);
        $msg = array('host_list' => $hosts);
        $opName = "PT_LICENSE_OP_ADD_VM_LICENSE";
        $unifyMsg = $this->unifyMsg($opName, json_encode($msg));
        foreach ($hosts as $host){
            $this->writeSystemLogHostAuth('BD_SYSTEMLOG_DESC_KEY_LISENCE_HOST', $unifyMsg, $host['vm_host_uuid'], $host['vcenter_uuid']);
        }
        return $unifyMsg;
    }
    
    /**
     * 取消宿主机授权
     * @param unknown $params
     */
    public function deleteHostAuth($params){
        $hosts = $params['hosts'];
        $this->paramsCheck($hosts);
        $msg = array('host_list' => $hosts);
        $opName = "PT_LICENSE_OP_DEL_VM_LICENSE";
        $unifyMsg = $this->unifyMsg($opName, json_encode($msg));
        foreach ($hosts as $host){
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
    private function writeSystemLogHostAuth($descriptionKey, $result, $hostuuid, $vcenteruuid){
        $sql = "select host_name, host_ip from vm_host where vcenter_uuid = ? and host_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid, $hostuuid));
        $descriptionParam = array($data[0]['host_name'], $data[0]['host_ip']);
        $result = json_decode($result, true);
        if($result['re']){
            $this->systemLog($descriptionKey, $descriptionParam);
        }else{
            $this->systemLog($descriptionKey, $descriptionParam, Xphp::$_config['LOGLEVEL']['ERROR']);
        }
    }
    
    /**
     * 得到宿主机表格信息
     */
    public function getHostInfo($params){
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
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
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
        foreach ($data as $d){
            $records["data"][] = array(
                $i++,
                $d['host_name'],
                $d['host_ip'],
                $d['vcenter_ip'] == $d['nickname'] ? $d['vcenter_ip'] : $d['nickname'] . "(" . $d['vcenter_ip'] . ")",
                Xphp::$_config['VMHYPERVISORDES'][intval($d['hypervisor_type'])],
                $d['cpu_count'],
                $this->getAuthorizationStatusDes($d['authorization_flag']),
                $this->getOpcode($systemAuthStatus, $d['authorization_flag']),
                array('hostuuid'=>$d['host_uuid'],'hypervisor'=>$d['hypervisor_type'], 
                    'flag' => intval($d['authorization_flag']),
                    'vcenteruuid' => $d['vcenter_uuid'],
                    'showflag' => $hostDivShowFlag
                ),
            );
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $dataCount[0]['total'];
        $records["recordsFiltered"] = $dataCount[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 根据系统授权类型得到虚拟机授权标签是否显示
     */
    private function getHostDivShowFlag(){
        //如果是按照宿主机和CPU授权,就显示,否则,不显示
        $sql = "select license_type from bd_license";
        $data = $this->dbSelect($sql);
        if($data){
            $lisenceType = Xphp::$_config['LISENCE_INFO']['type'];
            if($data[0]['license_type'] == $lisenceType['host'] || $data[0]['license_type'] == $lisenceType['cpu']){
                return true;
            }
        }
        return false;
    }
    
    /**
     * 得到系统授权信息
     * (!!!!!注意)Vmhandler checkTaskLegal有调用
     */
    public function getSystemLisenceInfo(){
        $systemStatus = $this->getSystemAuthorizationStatus();
        $systemStatusDes = $this->getSystemAuthorizationDes($systemStatus);
        
        return $this->getAuthorizedInfo(intval($systemStatus), $systemStatusDes);
    }
    
    /**
     * 得到虚拟机授权的基本信息
     * @param unknown $params
     */
    public function getVMLisenceInfo($params){
        $systemLisence = json_decode($this->getSystemLisenceInfo(), true);
        return json_encode($systemLisence['vminfo']);
    }
    
    /**
     * 得到文件授权的基本信息
     * @param unknown $params
     * @return string
     */
    public function getFileLisenceInfo($params){
        $fileInfo = $this->getOneModuleLisenceInfo('file');
        return json_encode($fileInfo);
    }
    
    /**
     * 得到已授权的信息
     * @param int $status       授权状态
     * @param string $statusDes 授权描述
     */
    private function getAuthorizedInfo($status, $statusDes){
        $info = array(
            'status' => $status,
        );
        $sql = "select unix_timestamp(register_time) register_time, license_type, vm_max_num, cpu_count, storage_count, node_count, file_max_num, 
                days, trial_type, software_type, user_name, extension from bd_license ";
        $data = $this->dbSelect($sql);
        $registerTime = $this->parseDate($data[0]['register_time']);
        $licenseType = intval($data[0]['license_type']);
        $vmMaxNum = intval($data[0]['vm_max_num']);
        $cpuMaxNum = intval($data[0]['cpu_count']);
        $storageMaxNum = intval($data[0]['storage_count']);
        $nodeMaxNum = intval($data[0]['node_count']);
        $fileMaxNum = intval($data[0]['file_max_num']);
        $days = $data[0]['days'];
        $expireTime = "----";
        
        $authFlag = Xphp::$_config['LISENCE_INFO']['authflag'];
        if($status == $authFlag['authorized']){
            //已授权
            $trialType = intval($data[0]['trial_type']);
            if($trialType == Xphp::$_config['TRIAL_TYPE']['TRIAL']){
                $statusDes = Xphp::$_lang['WEB_SYSTEM_TRIAL_AUTHIORIZED'];
            }elseif ($trialType == Xphp::$_config['TRIAL_TYPE']['NORMAL']){
                $statusDes = Xphp::$_lang['WEB_SYSTEM_FORMAL_AUTHIORIZED'];
            }
            
            //剩余天数 等于 授权天数 - 授权时间到当前时间的天数
            if("-1" == $days){
                $statusDes .= " (" . Xphp::$_lang['WEB_SYSTEM_AUTH_FOREVER'] . ")";
                $expireTime = Xphp::$_config['TIMESPACE'];
            }else{
                $dayInterval = round((time() - strtotime($registerTime))/3600/24);
                $expireDays = ($days - $dayInterval) <= 0 ? 0 : ($days - $dayInterval);
                $statusDes .= " (" . Xphp::$_lang['WEB_SYSTEM_AUTH_VALID'] . $expireDays  . Xphp::$_lang['WEB_SYSTEM_AUTH_DAY'] . ")";
                //到期天数等于注册时间+授权天数转换成时间
                $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
                $expireTime = date("Y-m-d H:i:s", $endTimeStamp);
            }
        }elseif ($status == $authFlag['expire'] || $status == $authFlag['invalid']){
            //到期天数等于注册时间+授权天数转换成时间
            $endTimeStamp = strtotime($registerTime) + ($days * 24 * 3600);
            $expireTime = date("Y-m-d H:i:s", $endTimeStamp);
        }
        
        
        
        $info['statusDes'] = $statusDes;
        $vmTypeName = array('', 'host', 'cpu', 'storage', 'vm');
        //虚拟机要根据授权类型得到使用量
        $info['vminfo'] = $this->getOneModuleLisenceInfo($vmTypeName[$licenseType], $licenseType);
        $info['vminfo'] = $this->filterModuleInfo($licenseType, $info['vminfo']);
        $info['fileinfo'] = $this->getOneModuleLisenceInfo('file');
        $info['expireTime'] = $expireTime;
        $info['trial'] = intval($data[0]['trial_type']);
     	$info['software'] = intval($data[0]['software_type']);
        if(empty($data)){
        	$info['software'] = $this->getSoftwareType();
        }
        $info['customer'] = empty($data[0]['user_name']) ? "----" : $data[0]['user_name'];
        
        $info['extension'] = json_decode($data[0]['extension'], true);
        
        return json_encode($info);
    }
    
 /**
     * 得到软件版本,
     * 1标准版,2企业版,3企业增强版,4免费版本
     * OEM版本暂时划归为企业版,企业增强版暂时没有
     */
    public function getSoftwareType(){
        $systemInfo = Xphp::$_config['SYSTEM_INFO'];
        $enterprise = Xphp::$_config['ENTERPRISE'];
        $sql = "select software_type from bd_license";
        $data = $this->dbSelect($sql, array());
        if(empty($data)){
            //如果没有记录,就是还没有授权,需要获取配置文件的软件版本
            if($systemInfo['enterprise'] == $enterprise['standard']){
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['STANDARD'];
            }else if($systemInfo['enterprise'] == $enterprise['enterprise']){
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE'];
            }else if($systemInfo['enterprise'] == $enterprise['enterprise_en']){
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE_EN'];
            }else{
                //如果是其他OEM版本,默认为企业版
                $softwareVersion = Xphp::$_config['SOFTWARE_VERSION']['ENTERPRISE'];
            }
        }else{
            //有记录,获取数据库的记录
            $softwareVersion = intval($data[0]['software_type']);
        }
        return $softwareVersion;
    }
    
    /**
     * 得到软件是否是OEM版本,云祺版本暂时就只有三个,其他的都是OEM版本.
     * 界面会根据这个判断来做一些特别处理
     */
    public function getSoftwareIsOem(){
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
    private function getUnAuthorizadInfo($status, $statusDes){
        $info = array(
            'status' => $status,
            'statusDes' => $statusDes,
        );
        $authFlag = Xphp::$_config['LISENCE_INFO']['authflag'];
        $tips = "";
        if($status == $authFlag['unauthorized']){
            //未授权
            $tips = Xphp::$_lang['WEB_SYSTEM_UNAUTHORIZED_TIP'];
        }elseif($status == $authFlag['expire']){
            //授权过期
            $tips = Xphp::$_lang['WEB_SYSTEM_EXPIRE_TIP'];
        }elseif($status == $authFlag['invalid']){
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
    private function getAuthorizationStatusDes($flag){
        $des = Xphp::$_lang['WEB_SYSTEM_UNAUTHIORIZED'];
        if($flag == Xphp::$_config['FLAG']['SET']){
            $des = Xphp::$_lang['WEB_SYSTEM_AUTHIORIZED'];
        }
        return $des;
    }
    
    /**
     * 得到宿主机授权操作
     * @param int $flag   1 取消授权/2 添加授权
     */
    private function getOpcode($systemAuthStatus, $flag){
        if($systemAuthStatus == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            //系统已授权
            $flag = intval($flag);
            return array($flag);
        }else{
            //系统未授权
            return array(0);
        }
    }
    
    /**
     * 获取系统授权状态
     */
    public function getSystemAuthorizationStatus(){
        $sql = "select authorized_flag from bd_system ";
        $data = $this->dbSelect($sql);
        if($data){
            return intval($data[0]['authorized_flag']);
        }
    }
    
    /**
     * 获取授权描述
     * @param int $status   授权状态标志
     * @return string
     */
    public function getSystemAuthorizationDes($status){
        $pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = $pfDes['LISENCE_STATUS_DES'][$status];
        return $des;
    }
    
    /**
     * 检测上传文件
     */
    private function checkUploadFile(){
        $uploadfile = Xphp::$_config['LISENCE_INFO']['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        if($files){
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
    private function checkUploadStatus($error){
        if(0 == $error){
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE']));
    }
    
    /**
     * 检测文件名字
     * @param string $name  客户端文件的原名称
     * @param array $conf   上传文件配置
     */
    private function checkFileName($name, $conf){
        $arr = explode(".", $name);
        if($arr[count($arr) - 1] == $conf['suffixes']){
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE_TYPE_ERROR'], 'error'));
    }
    
    /**
     * 检测文件大小
     * @param int $size 已上传文件的大小，单位为字节
     * @param array $conf   上传文件配置
     */
    private function checkFIleSize($size, $conf){
        if($size <= $conf['size']){
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE_SIZE_ERROR'], 'error'));
    }
    
    /**
     * 检测上传文件的文件的 MIME类型
     * @param string $type
     * @param array $conf   上传文件配置
     */
    private function checkUploadType($type, $conf){
        //浏览器有差异,暂时不检测
        return true;
        if($type == $conf['type']){
            return true;
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE'], Xphp::$_lang['WEB_SYSTEM_UPLOAD_FILE_ERROR'], 'error'));
    }
    
    /**
     * 提交授权码
     * @param unknown $key
     */
    private function submitLisenceKey($key){
        $msg = array('license' => $key);
        $opName = 'PT_LICENSE_OP_ADD_LICENSE';
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), false);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            $usersHandler = Xphp::instance('UsersHandler');
            $usersHandler -> updateUserPermissionAndSoftwareType();
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
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
    private function unifyMsg($opName, $jsonMsg, $sync = false){
        $mbResult = $this->mbPFMsg($opName, $jsonMsg, $sync);
        $result = $mbResult['result'];
        $operate = $this->opcodeHandler->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
    /**
     * 得到某个模块的授权信息
     * @param string $moduleName   模块名
     * @return array [total, used, valid]
     */
    public function getOneModuleLisenceInfo($moduleName){
        $moduleInfo = array(
            'total' => 0,
            'used' => 0,
            'valid' => 0,
        );
        $moduleNames = array('file', 'vm', 'oracle', 'sqlserver', 'os', 'cdp', 'desktop', 'host', 'cpu', 'storage', 'node');
        if(!in_array($moduleName, $moduleNames)){
            //非法请求
            return $moduleInfo;
        }
        
        $total = $this->getModulesLisenceTotal();
        if($total['authFlag'] != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
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
    private function filterModuleInfo($licenseType, $moduleInfo){
        if($licenseType == Xphp::$_config['LISENCE_INFO']['type']['storage']){
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
    public function getModulesLisenceTotal(){
        $authFlag = $this->getSystemAuthorizationStatus();
        $total = array(
            "authFlag" => $authFlag
        );
        if($authFlag == Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            //如果是已授权状态
            $sql = "select file_max_num, vm_max_num, mysql_max_num, oracle_max_num, sqlserver_max_num, os_max_num, 
                    cdp_max, desktop_max, cpu_count, storage_count, node_count from bd_license";
            $data = $this->dbSelect($sql);
            foreach ($data as $d){
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
                    'node' => $d['node_count']
                );
            }
        }
        
        return $total;
    }
    
    /**
     * 得到所有模块使用总数
     */
    public function getModulesLisenceUsed(){
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
        );
        
        $sql = "select authorization_module from bd_agent where register_flag = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['SET']));
        foreach ($data as $d){
            if(!empty($d['authorization_module'])){
                $authInfo = json_decode($d['authorization_module'], true);
                $used['file'] += $authInfo['file'];
                $used['mysql'] += $authInfo['mysql'];
                $used['oracle'] += $authInfo['oracle'];
                $used['sqlserver'] += $authInfo['sqlserver'];
                $used['os'] += $authInfo['os'];
                $used['desktop'] += $authInfo['desktop'];
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
     * 得到虚拟机模块的使用量
     */
    private function getVMModuleUsedArr(){
        $vmUsed = array(
            'vm' => 0,
            'host' => 0,
            'storage' => 0,
            'cpu' => 0,
        );
        $authFlag = $this->getSystemAuthorizationStatus();
        if($authFlag != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
            return $vmUsed;
        }
        $sql = "select license_type  from bd_license";
        $data = $this->dbSelect($sql);
        $licenseType = intval($data[0]['license_type']);
        $licenseTypeConf = Xphp::$_config['LISENCE_INFO']['type'];
        $sql ="";
        $sqlParams = array();
        $key = "";
        switch ($licenseType){
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
                $sql = "select sum(total_size) as total from bd_storage_resource where lan_free_flag = ?";
                $sqlParams = array(Xphp::$_config['FLAG']['UNSET']);
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
        if(empty($count)) $count = 0;
        $vmUsed[$key] = $count;
        return $vmUsed;
    }
    
    /**
     * 得到所有模块可用总数
     */
    public function getModulesLisenceValid(){
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
            'node' => 0
        );
        $totalInfo = $this->getModulesLisenceTotal();
        if($totalInfo['authFlag'] != Xphp::$_config['LISENCE_INFO']['authflag']['authorized']){
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
        );
        
        return $valid;
    }
    
    /**
     * 检测模块是否授权
     * @param string $authModule   代理模块授权字段authorization_module
     * @param string $module       模块名
     * @return boolean
     */
    public function checkModuleValid($authModule, $module){
        if(empty($authModule)) return false;
        $authModule = json_decode($authModule, true);
        if($authModule[$module]){
            return true;
        }
        return false;
    }
    
    /**
     * 得到代理端信息
     * @param unknown $params
     * @return string
     */
    public function getAgentInfo($params){
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
        if(Xphp::$_user['usertype'] == Xphp::$_config['USERTYPE']['operator']){
            $sql .= "and bu.user_uuid = ? ";
            $sqlCount .= "and bu.user_uuid = ? ";
            $sqlParams = array($setFlag, Xphp::$_user['useruuid'], $start, $length);
            $sqlCountParams = array($setFlag, Xphp::$_user['useruuid']);
        }else{
            $sqlParams = array($setFlag, $start, $length);
            $sqlCountParams = array($setFlag);
        }
        $sql .= " order by ba.id desc limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        
        $agentHandler = Xphp::instance('AgentHandler');
        $records = array("data" => array());
        foreach ($data as $d){
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
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return  json_encode($records);
    }
    
    /**
     * 添加代理端授权操作
     * @param array $params
     * [hostName(string)主机名,agentUUID(string)代理端UUID,module(array)模块]
     */
    public function addAgentLisence($params){
        $opName = "PT_LICENSE_OP_ADD_AGENT_LICENSE";
        return $this->addAndEditAgentLisence($opName, $params);
    }
    
    /**
     * 修改代理端授权操作
     * @param array $params
     * [hostName(string)主机名,agentUUID(string)代理端UUID,module(array)模块]
     */
    public function editAgentLisence($params){
        $opName = "PT_LICENSE_OP_MODIFY_AGENT_LICENSE";
        return $this->addAndEditAgentLisence($opName, $params);
    }
    
    /**
     * 添加/修改代理端授权统一操作
     * 因为他们传到后台的参数是一样的O(∩_∩)O
     * @param string $opName   
     * @param array $params   [hostName(string)主机名,agentUUID(string)代理端UUID,module(array)模块]
     */
    private function addAndEditAgentLisence($opName, $params){
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
    public function getNetworkCardList($params){
        //获取网卡名
        $cmd = "ip link list | awk '{if ($1 ~ \"[0-9]*:\")print $2}'";
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND_WITH_DETAIL';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        $cardName = array();
        if(!$mbResult['result']){
            return json_encode($cardName);
        }
        //获取消息成功
        $msgDetail = $mbResult['msg']['command_result_detail'];
        $cardNameArr = explode(":" . PHP_EOL, $msgDetail);
        
        //获取mac
        $cmd = "ip link list | grep link|awk '{print $2}'";
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND_WITH_DETAIL';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        if(!$mbResult['result']){
            return json_encode($cardName);
        }
        $cardHwAddrArr = explode(PHP_EOL, $mbResult['msg']['command_result_detail']);
        
        foreach ($cardNameArr as $key => $value){
            if("lo" != $value && !empty($value)){
                $cardName[] = array(
                    'name' => $value,
                    'hwaddr' => $cardHwAddrArr[$key]
                );
            }
        }
        
        return json_encode($cardName);
    }
    
    /**
     * 获取某块网卡的信息
     * @param unknown $params
     */
    public function getNetworkCardInfo($params){
        $cardName = $params['cardName'];
        $cardHwaddr = $params['cardHwaddr'];
        $this->paramsCheck($cardName, $cardHwaddr);
        $cardInfo = array(
            "NAME" => $cardName,
            "IPADDR" => '',
            "NETMASK" => '',
            "GATEWAY" => '',
            "DNS" => '',
        );
        $cardName = explode("@", $cardName); //考虑分割挂载的情况
        $networkCardPath = Xphp::$_config['NETWORKCARD']['path'] . Xphp::$_config['NETWORKCARD']['prefix'] . $cardName[0];
        if(file_exists($networkCardPath)){
            //如果网卡配置文件存在,读取配置文件的信息
            $cmd = "cat " . $networkCardPath;
            exec($cmd, $info);
            //需要查找的关键字
            $keyArr = array("IPADDR", "NETMASK", "GATEWAY", "DNS");
            foreach ($info as $each){
                foreach ($keyArr as $key){
                    if(stristr($each, $key)){
                        //如果匹配到了关键字
                        $needArr = explode("=", $each);
                        //如果是DNS,特殊处理,主要是处理多个情况
                        if("DNS" == $key){
                            if($key != substr($needArr[0], 0, 3)){
                                //如果不是DNS配置,排除其他关键字带有DNS三个字符存在的情况
                                break;
                            }
                            if(empty($cardInfo[$key])){
                                $cardInfo[$key] = $needArr[1];
                            }else{
                                $cardInfo[$key] .= "," . $needArr[1];
                            }
                        }else{
                            $cardInfo[$key] = $needArr[1];
                        }
                    }
                }
            }
        }
        return json_encode($cardInfo);
    }
    
    /**
     * 设置网卡信息
     * @param unknown $params
     */
    public function setNetworkCardInfo($params){
        $name = $params['NAME'];
        $ipaddr = $params['IPADDR'];
        $netmask = $params['NETMASK'];
        $gateway = $params['GATEWAY'];
        $dns = $params['DNS'];
        //检测参数是否为空
        $this->paramsCheck($name);
        
        $params['TYPE'] = "Ethernet";             //网卡类型
        $params['BOOTPROTO'] = "static";//配置静态获取IP
        $params['ONBOOT'] = "yes";      //开机启动
		$params['DEVICE'] = $name;        $operate = Xphp::$_lang['WEB_SYSTEM_SETTING_NETWORK'] . "[" . $name . "]" . Xphp::$_lang['WEB_SYSTEM_SETTING_IP'];
        //检测系统任务状态
        $this->checkTaskStatus($operate);
        
        //配置文件内容
        $configFileContent = '';
        $networkCardPath = Xphp::$_config['NETWORKCARD']['path'] . Xphp::$_config['NETWORKCARD']['prefix'] . $name;
        if(file_exists($networkCardPath)){
            //如果网卡配置文件存在,修改配置文件
            $cmd = "cat " . $networkCardPath;
            exec($cmd, $info);
            //需要查找的关键字,带这些关键字的需要替换,其他的照抄
            $keyArr = array("NAME", "DEVICE", "IPADDR", "NETMASK", "GATEWAY", "TYPE", "BOOTPROTO", "ONBOOT");
            foreach ($keyArr as $each){
                $flag = true;
                foreach ($info as $key){
                    if(stristr($key, $each)){
                        //如果匹配到了关键字
                        $configFileContent .= $each . "=" . $params[$each] . PHP_EOL;
                        $flag = false;
                        break;
                    }
                }
                if ($flag){
                    //添加关键字外除了DNS的选项
                    if(!empty($each) && "DNS" != substr($each, 0, 3)){
                        $configFileContent .=  $each . "=" . $params[$each] . PHP_EOL;
                    }
                }
            }
        }else{
        	
            //如果网卡配置文件不存在,按模板写入一个配置文件
            $keyArr = array("NAME", "DEVICE", "IPADDR", "NETMASK", "GATEWAY", "TYPE", "BOOTPROTO", "ONBOOT");
            foreach ($keyArr as $key){
                $configFileContent .= $key . "=" . $params[$key] . PHP_EOL;
            }
        }
        
        //最后处理DNS,添加用户配置的DNS到配置文件
        if(!empty($dns)){
            //按逗号分隔,处理一下半角和全角标点
            $comma = ",";
            if(stristr($dns, "，")){
                $comma = "，";
            }
            $dnsArr = explode($comma, $dns);
            $i = 1;
            foreach ($dnsArr as $each){
                $configFileContent .= "DNS" . $i++ . "=" . $each . PHP_EOL;
            }
        }
        //写入配置文件
        $cmd = "echo '" . $configFileContent . "'>" . $networkCardPath;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        
        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_IP');
        
        if($mbResult['result']){
            $cmd = "/usr/sbin/service network restart;";
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            $msg = array('command'=>$cmd);
            $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
            if($mbResult['result']){
                //这里因为要重启后台所有服务，无法获取最终执行命令结果。所以不判断了
                $this->restartService();
            }
        }
        
        return $this->muOpResult($mbResult['result'], $operate);
    }
    
    /**
     * 统一写系统日志
     * @param boolean $result
     * @param string $descriptionKey
     * @param array $descriptionParam
     */
    private function unifyWriteSystemLog($result, $descriptionKey, $descriptionParam = array()){
        if($result){
            $this->systemLog($descriptionKey, $descriptionParam);
        }else{
            $this->systemLog($descriptionKey, $descriptionParam, Xphp::$_config['LOGLEVEL']['WARN']);
        }
    }
    
    /**
     * 检测任务状态 
     * 有运行中的任务和瞬时恢复的任务时返回失败
     */
    private function checkTaskStatus($operate){
        $sql = "select id from bd_task where (task_status = ? or task_type = ? or task_type = ?) and delete_flag = ?";
        $sqlParams = array(Xphp::$_config['TASKSTATUS']['RUNNING'], Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY'], 
                           Xphp::$_config['TASKTYPE']['VM_INSTANT_RECOVERY_MOTION'], Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        if($data[0]){
            exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_SETTING_IP_HAS_TASK'], 'warning'));
        }
        return true;
    }
    
    /**
     * 重启后台服务
     * @return boolean
     */
    private function restartService(){
        $cmd = "/usr/sbin/service vinfs restart;/usr/sbin/service vm_server restart;/usr/sbin/service node_server restart;" . 
               "/usr/sbin/service fs_server restart;/usr/sbin/service rt_server restart;/usr/sbin/service pt_server restart;";
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        return $mbResult['result'];
    }
    
    /**
     * 获取所有时区配置文件
     * @param unknown $parmas
     */
    public function getAllTimezone($parmas){
        $cmd = "timedatectl list-timezones";
        exec($cmd, $data);
        return json_encode($data);
    }
    
    /**
     * 获取系统默认的时区和时间
     * @param unknown $params
     */
    public function getDefaultTimeInfo($params){
        $timeInfo = array();
        $cmd = "timedatectl |grep Timezone|awk '{print $2}'";
        exec($cmd, $data);
        if(!$data[0]){
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
        if(file_exists(Xphp::$_config['NTP_SERVERS_CONF'])){
            $ntpConf = json_decode(file_get_contents(Xphp::$_config['NTP_SERVERS_CONF']), true);
        }
        $timeInfo['ntp'] = $ntpConf;
        
        return json_encode($timeInfo);
    }
    
    /**
     * 获取系统时间
     * @param unknown $params
     */
    public function getSystemTime($params){
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        return strtotime($data[0]);
    }
    
    /**
     * 获取系统时间
     * @param unknown $params
     */
    public function getSystemTimeJSFormat($params){
        $cmd = "timedatectl |grep Local |awk '{print $4,$5}'";
        exec($cmd, $data);
        return date("Y/m/d H:i:s", strtotime($data[0]));
    }
    
    /**
     * 设置时间
     * @param unknown $params
     */
    public function setTimeInfo($params){
        $timezone = $params['timezone'];
        $timeinput = $params['timeinput'];
        $ntpcheck = $params['ntpcheck'];
        $ntphost = $params['ntphost'];
        $this->paramsCheck($timezone, $timeinput);
        if($ntpcheck){
            //如果是配置NTP服务器,直接到配置NTP服务器处
            return $this->setNtpServer($ntphost);
        }
        $cmd = "timedatectl set-timezone " . $timezone;
        $cmd .= ";date -s '" . $timeinput . "'";
        $cmd .= ";hwclock -w";
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        
        $operate = Xphp::$_lang['WEB_SYSTEM_SETTING_TIME'];
        if($mbResult['result']){
            //如果修改成功,需要停止NTP服务器,禁用开机启动,修改配置文件
            $cmd = "timedatectl set-ntp no;";
            $cmd .="systemctl disable ntpd;";
            $cmd .= "systemctl stop ntpd;";
            //因为修改了时区后日期有问题,所以这里重启所有后台进程和数据库
            $cmd .= "systemctl restart mariadb;";
            $cmd .= "systemctl restart vm_server;";
            
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            $msg = array('command'=>$cmd);
            $mbResult = $this->mbPFMsg($opName, json_encode($msg), false, true);
            $ntpConf = json_decode(file_get_contents(Xphp::$_config['NTP_SERVERS_CONF']), true);
            $ntpConf['ntpFlag'] = false;
            $ntpConf = json_encode($ntpConf);
            file_put_contents(Xphp::$_config['NTP_SERVERS_CONF'], $ntpConf, LOCK_EX);
        }
        
        
        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_TIME');
        
        //修正发送到后台,往后修改时间一直不成功的BUG.
        $nowtime = $this->getSystemTime();
        $setTime = strtotime($timeinput);
        if(abs($nowtime - $setTime) <= 10){
            return $this->muOpResult(true, $operate);
        }
        return $this->muOpResult($mbResult['result'], $operate);
    }
    
    /**
     * 配置NTP服务器
     * @param string $ntphost
     */
    private function setNtpServer($ntphost){
        $this->paramsCheck($ntphost);
        $ntpConf = json_decode(file_get_contents(Xphp::$_config['NTP_SERVERS_CONF']), true);
        $oldNtpServer = $ntpConf['ntpServers'][0];
        //修改NTP服务器配置文件
        $confFile = Xphp::$_config['NTP_SERVERS_FILE'];
        $conf = file_get_contents($confFile);
        $conf = str_replace($oldNtpServer, $ntphost, $conf);
        $cmd = "echo '" . $conf . "' > " . $confFile;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        //开启NTP时间配置NTP enabled
        $cmd = "timedatectl set-ntp yes;";
        //设置开机启动NTP服务
        $cmd .= "systemctl enable ntpd;";
        //重启NTP服务器
        $cmd .= "systemctl restart ntpd;";
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        //写入新的NTP配置文件(备份系统识别用)
        if(in_array($ntphost, $ntpConf['ntpServers'])){
            //如果在列表中,提前到第一个,系统默认为第一个为设置项
            $key = array_search($ntphost, $ntpConf['ntpServers']);
            array_splice($ntpConf['ntpServers'], $key, 1);
        }
        array_unshift($ntpConf['ntpServers'], $ntphost);
        $ntpConf['ntpFlag'] = true;
        $ntpConf = json_encode($ntpConf);
        $count = file_put_contents(Xphp::$_config['NTP_SERVERS_CONF'], $ntpConf, LOCK_EX);
        $flag = false;
        if($count > 0){
            $flag = true;
        }
        $operate = Xphp::$_lang['WEB_SYSTEM_SETTING_TIME'];
        return $this->muOpResult($flag, $operate);
    }
    
    /**
     * 立即同步ntp时间
     * @param unknown $params
     */
    public function syncNtpTime($params){
        $ntphost = $params['ntphost'];
        $this->paramsCheck($ntphost);
        //停止NTP服务,不停止手动同步会报正在运行
        $cmd = "systemctl stop ntpd";
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        if(!$mbResult['result']){
            return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_SYSTEM_SETTING_TIME_NTP_STOP']);
        }
        //手动同步
        $cmd = "ntpdate " . $ntphost;
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        if($mbResult['result']){
            $cmd = "hwclock -w";
            $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
            $msg = array('command'=>$cmd);
            $this->mbPFMsg($opName, json_encode($msg), true);
            $ext = array(
                "newTime" => date("Y-m-d H:i:s", $this->getSystemTime())
            );
        }
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_SYSTEM_SETTING_TIME_NTP_SYNC'], null, null, 0, $ext);
    }
    
    /**
     * 获取系统通知默认配置
     * @param unknown $params
     */
    public function getDefaultNoticeConf($params){
        $utils = Xphp::instance('Utils');
        $sql = "select email_notice_flag, system_notice_flag, system_notice_level, task_notice_flag, 
                task_notice_level, report_config, receive_email, smtp_config from bd_email_notice";
        $data = $this->dbSelect($sql);
        
        $smtpConfig = json_decode($data[0]['smtp_config'], true);
        $pass = $utils->decrypt($smtpConfig['pass']);
        if(empty($pass)){
            $pass = "";
        }else{
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
            'pass' => $pass,
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
        if($data){
            $userConf['userEmail'] = empty($data[0]['email']) ? Xphp::$_lang['WEB_SYSTEM_SETTING_NO_SET'] : $data[0]['email'];
            $userConf['userEmailFlag'] = !empty($data[0]['email']);
            $userConf['userPhone'] = empty($data[0]['telephone']) ? Xphp::$_lang['WEB_SYSTEM_SETTING_NO_SET'] : $data[0]['telephone'];
            $userConf['userPhoneFlag'] = !empty($data[0]['telephone']);
        }
        
        $records = array(
            'email' => $emailConf,
            'sms' => $smsConf,
            'user' => $userConf
        );
        
        return json_encode($records);
    }
    
    /**
     * 得到通知的等级设置字符串,直接可以插入到数据库的格式     1,2,3
     * @param unknown $levleArr
     */
    private function getNoticeSettingLevelStr($levleArr){
        $levelFlag = array();
        $i = 1;
        foreach ($levleArr as $level){
            if($level){
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
    private function setNoticeConf($operate, $tableName, $flagCol, $params, $systemLogKey){
        $utils = Xphp::instance('Utils');
        
        $flag = $utils->parseBoolToFlag($params['flag']);
        $systemFlag = $utils->parseBoolToFlag($params['systemFlag']);
        $taskFlag = $utils->parseBoolToFlag($params['taskFlag']);
        $systemLevel = $this->getNoticeSettingLevelStr($params['systemLevel']);
        $taskLevel = $this->getNoticeSettingLevelStr($params['taskLevel']);
        $reportConf = $params['reportConf'];
        $recevieEmail = $params['recEmail'];
        
        if("bd_email_notice" == $tableName){
            //邮件通知
            $sql = "update $tableName set $flagCol = ?, system_notice_flag = ?, system_notice_level = ?, 
            task_notice_flag = ?, task_notice_level = ?, report_config = ?, receive_email = ?";
            $sqlParams = array($flag, $systemFlag, $systemLevel, $taskFlag, $taskLevel, $reportConf, $recevieEmail);
        }else if("bd_sms_notice" == $tableName){
            //短信通知
            $sql = "update $tableName set $flagCol = ?, system_notice_flag = ?, system_notice_level = ?, 
            task_notice_flag = ?, task_notice_level = ?";
            $sqlParams = array($flag, $systemFlag, $systemLevel, $taskFlag, $taskLevel);
        }
        
        
        $result = $this->dbExec($sql, $sqlParams);
        
        $this->unifyWriteSystemLog($result, $systemLogKey);
        
        if($result){
            return $this->muOpResult(true, $operate);
        }else{
            return $this->muOpResult(false, $operate);
        }
    }
    
    /**
     * 配置邮件通知
     * @param unknown $params
     */
    public function setEmailConf($params){
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
    public function setSmsConf($params){
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
    public function testEmailNotice($params){
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
        if($user == $emailConf['from_email'] && $host == $emailConf['smpt_host'] && 
            $port == $emailConf['port'] && $pass == md5($emailConf['from_email_pass'])){
            //默认邮件,设置
            $pass = $emailConf['from_email_pass'];
        }
        
        //直接调用发送邮件接口
        $email = Xphp::instance('Email');
        $emailConfig = Xphp::$_config['EMAIL'];
        $email->config($host, $port, $emailConfig['authentication'],$user, $pass, Xphp::$_config['EMAIL_ENCRYPTION_TYPE'][$encryption]);
        $result = $email->sendmail($recEmail, $title, $info, array());
        $recEmailStr = implode($recEmail, ',');
        $operate = Xphp::$_lang['WEB_SYSTEM_SETTING_SEND_EMAIL_TO'] . $recEmailStr;
        if($result){
            return $this->muOpResult(true, $operate);
        }else {
            return $this->muOpResult(false, $operate);
        }
    }
    
    /**
     * 保存SMTP配置信息
     * @param unknown $params
     */
    public function setSmtpSetting($params){
        $host = $params['host'];
        $port = $params['port'];
        $user = $params['user'];
        $pass = base64_decode($params['pass']);
        $encryption = intval($params['encryption']);
        $this->paramsCheck($host, $port, $user);
        
        $operate = Xphp::$_lang['WEB_SYSTEM_SAVE_SMTP_INFO'];
        $emailConf = Xphp::$_config['EMAIL'];
        if($user == $emailConf['from_email'] && $host == $emailConf['smpt_host'] &&
            $port == $emailConf['port'] && $pass == md5($emailConf['from_email_pass'])){
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
        
        $sql = "update bd_email_notice set smtp_config = ?";
        $result = $this->dbExec($sql, array(json_encode($smtpConfig, true)));
        if($result){
            return $this->muOpResult(true, $operate);
        }else {
            return $this->muOpResult(false, $operate);
        }
    }
    
    /**
     * 测试短信发送
     * @param unknown $params
     */
    public function testSmsNotice($params){
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
    private function updateInternetSettings($sendResult){
        $result = json_decode($sendResult, true);
        if(!$result['re']) return true;
        $sql = "update bd_sms_notice set sms_send_type = ?";
        $sqlParams = array(Xphp::$_config['SMS_CONFIG']['SEND_TYPE']['INTERNET']);
        $result = $this->dbExec($sql, $sqlParams);
        return $result;
    }
    
    /**
     * 测试短信猫发送
     * @param unknown $params
     */
    public function testSmsModemNotice($params){
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
    private function updateModemSettings($sendResult, $config){
        $result = json_decode($sendResult, true);
        if(!$result['re']) return true;
        $sql = "update bd_sms_notice set sms_send_type = ?, sms_device_config = ?";
        $sqlParams = array(Xphp::$_config['SMS_CONFIG']['SEND_TYPE']['MODEM'], json_encode($config));
        $result = $this->dbExec($sql, $sqlParams);
        return $result;
    }
    
    /**
     * 获取软件版本信息
     * @param unknown $params
     */
    public function getSoftVersion($params){
        //TODO
        $info = array(
            'version' => '3.1.2154',
            'releasetime' => '2015-12-11 12:12:14'
        );
        return json_encode($info);
    }
    
    /**
     * 获取软件最新版本信息
     * @param unknown $params
     */
    public function getNewVersionInfo($params){
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
    public function doFactoryReset($params){
        //检测用户权限
        $userType = Xphp::$_config['USERTYPE'];
        if(Xphp::$_user['usertype'] == $userType['manager'] or
            Xphp::$_user['usertype'] == $userType['administrator']){
            //发送恢复出厂设置指令
            //TODO
            sleep(5);
            return $this->muOpResult(true, Xphp::$_lang['WEB_SYSTEM_RESTORE_FACTORY_SETTING']);
        }
        exit($this->muOpResult(false, Xphp::$_lang['WEB_USERS_PERMISSION_CHECK']));
    }
    
    /**
     * 设置系统名称
     * @param unknown $params
     */
    public function setSystemName($params){
        $systemName = $params['systemname'];
        $count = file_put_contents(Xphp::$_config['SYSTEM_NAME_FILE'], $systemName, LOCK_EX);
        $flag = false;
        if($count > 0){
            $flag = true;
        }
        
        $this->unifyWriteSystemLog($flag, 'SYSTEM_SETTING_SYSTEM_NAME', array($systemName));
        
        return $this->muOpResult($flag, Xphp::$_lang['WEB_SYSTEM_SET_SYSTEM_NAME']);
    }
    
    /**
     * 获取系统名称
     * @param unknown $params
     */
    public function getSystemName($params){
        if(file_exists(Xphp::$_config['SYSTEM_NAME_FILE'])){
            //如果自定义系统名称存在
            return file_get_contents(Xphp::$_config['SYSTEM_NAME_FILE']);
        }else{
            //如果自定义系统名称不存在
            return Xphp::$_config['SYSTEM_INFO']['system_name'];
        }
    }
    
    /**
     * 导出系统任务信息
     * @param unknown $parmas
     */
    public function exportTaskInfo($params){
        $dbInfo = Xphp::$_config['DB_INFO'];
        $timeStamp = date("Ymd.His");
        $tmpFilePath = Xphp::$_config['TMP_PATH'] . "taskinfo";
        $cmd = "mysqldump -P" . $dbInfo['port'] ." -h" . $dbInfo['host'] . " -u" . $dbInfo['user'] . 
            " -p" . $dbInfo['pass'] . " " . $dbInfo['dbname'] . " bd_reserved_strategy " . 
            "bd_running_info bd_storage_strategy bd_strategy bd_task bd_time_strategy " .
            "bd_transport_strategy vm_host vm_instant vm_machine vm_machine_list " .
            "vm_task vm_tree vm_vcenter bd_user bd_user_extension > " . $tmpFilePath;
        $cmd .= ";cd " . Xphp::$_config['TMP_PATH'] . " ; rm -f *.zip ";
        $cmd .= ";zip -q -r -m -P " . Xphp::$_config['ZIP_PASS'] . " $tmpFilePath.$timeStamp.zip taskinfo";
        $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
        $msg = array('command'=>$cmd);
        $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
        $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_EXPORT_DATA');
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['WEB_SYSTEM_EXPORT_TASK_INFO'], '', 'warning', 0, array(Xphp::$_config['TMP_PATH_RE'] . "taskinfo." . $timeStamp . ".zip"));
    }
    
    /**
     * 导入系统任务信息
     * @param unknown $params
     */
    public function importTaskInfo($params){
        $uploadfile = Xphp::$_config['IMPORT_INFO']['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        $tmpPath = Xphp::$_config['TMP_PATH'];
        $operate = Xphp::$_lang['WEB_SYSTEM_IMPORT_TASK_INFO'];
        if($files){
            //检测上传文件状态
            if(0 != $files['error']){
                exit($this->muOpResult(false, $operate));
            }
            //检测文件后缀名
            $arr = explode(".", $files['name']);
            if($arr[count($arr) - 1] != $uploadfile['suffixes']){
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_IMPORT_FILE_TYPE_ERROR'], 'error'));
            }
            //检测上传文件大小
            if($files['size'] > $uploadfile['size']){
                exit($this->muOpResult(false, $operate, Xphp::$_lang['WEB_SYSTEM_IMPORT_FILE_SIZE_ERROR'], 'error'));
            }
            //保存临时文件
            $count = file_put_contents($tmpPath . "task.zip", file_get_contents($files['tmp_name']));
            if($count > 0){
                $dbuser = Xphp::$_config['DB_INFO']['user'];
                $dbpass = Xphp::$_config['DB_INFO']['pass'];
                //导入成功,解压文件,导入到数据库
                $cmd = "unzip -P" . Xphp::$_config['ZIP_PASS'] . " -qo " . $tmpPath . "task.zip -d " . $tmpPath . "task";
                $cmd .= ";mysql -u$dbuser -p$dbpass vinchin_db < " . $tmpPath . "task/taskinfo";
                $opName = 'PT_SYSTEM_BACKGROUND_OP_DO_COMMAND';
                $msg = array('command'=>$cmd);
                $mbResult = $this->mbPFMsg($opName, json_encode($msg), true);
                if($mbResult['result']){
                    $this->unifyWriteSystemLog($mbResult['result'], 'SYSTEM_SETTING_IMPORT_DATA');
                    return $this->muOpResult($mbResult['result'], $operate);
                }
                return $this->muOpResult($mbResult['result'], $operate, '', 'warning');
            }
            exit($this->muOpResult(false, $operate));
        }else {
            exit($this->muOpResult(false, $operate));
        }
    }
    
    /**
     * 重启备份节点
     * @param unknown $params
     */
    public function doRebootBackupNode($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $opName = 'BD_SYSTEM_OP_REBOOT_SYSTEM';
        $msg = array();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['UI_SETTINGS_POWEROFF_REBOOT_MSG']);
    }
    
    /**
     * 关闭备份节点
     * @param unknown $params
     */
    public function doPoweroffBackupNode($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $opName = 'BD_SYSTEM_OP_POWEROFF_SYSTEM';
        $msg = array();
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), false, true);
        return $this->muOpResult($mbResult['result'], Xphp::$_lang['UI_SETTINGS_POWEROFF_POWEROFF_MSG']);
    }
    
    /**
     * 获取节点的hosts文件信息
     * @param unknown $params
     */
    public function getNodeDnsHosts($params){
        $nodeuuid = $params['nodeuuid'];
        $this->paramsCheck($nodeuuid);
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $cmd = "cat /etc/hosts";
        $msg = array(
            "command" => $cmd
        );
        $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
        $msg = '';
        if($mbResult['result']){
            $detail = $mbResult['msg']['detail'];
            $detailArr = explode(PHP_EOL, $detail);
            foreach ($detailArr as $d){
                //过滤空行
                $d = trim($d);
                if(empty($d)){
                    continue;
                } 
                //过滤127.0.0.1和::1
                if(false === strpos($d, "127.0.0.1") && false === strpos($d, "::1")){
                    $msg .= $d . PHP_EOL;
                }
            }
            return $msg;
        }else{
            return '';
        }
    }
    
    /**
     * 配置节点hosts文件信息
     * @param unknown $params
     */
    public function setNodeDnsHosts($params){
        $nodeuuid = $params['nodeuuid'];
        $setting = $params['setting'];
        $syncnode = $params['syncnode'];
        $this->paramsCheck($nodeuuid);
        $settingNodes = array();
        if($syncnode){
            //如果是同步所有节点
            //先得到所有在线节点,然后再一次同步设置
            $nodeHandler = Xphp::instance('NodeHandler');
            $nodes = $nodeHandler->getAddStorageNodeSelect();
            $nodes = json_decode($nodes, true);
            foreach ($nodes as $node){
                $settingNodes[] = $node['uuid'];
            }
        }else{
            $settingNodes[] = $nodeuuid;
        }
        
        return $this->setNodesDnsHosts($settingNodes, $setting);
    }
    
    /**
     * 配置多个节点的hosts文件信息
     * @param array $settingNodes 多个节点uuid
     * @param string $setting
     */
    private function setNodesDnsHosts($settingNodes, $setting){
        $setting = $this->groupDnsSetting($setting);
        $opName = 'NODE_SYS_OP_DO_CMD';
        $cmd = "echo '" . $setting . "' > /etc/hosts";
        $msg = array(
            "command" => $cmd
        );
        $result = true;
        foreach ($settingNodes as $nodeuuid){
            $mbResult = $this->mbNodeMsg($opName, $nodeuuid, json_encode($msg), true, false);
            $result = $result & $mbResult;
        }
        return $this->muOpResult($result, Xphp::$_lang['UI_SETTINGS_DNS_SETTING']);
    }
    
    /**
     * 根据用户配置组合最终的hosts配置文件
     * @param string $setting
     */
    private function groupDnsSetting($setting){
        $settingHeader = "127.0.0.1   localhost localhost.localdomain localhost4 localhost4.localdomain4" . PHP_EOL;
        $settingHeader .= "::1         localhost localhost.localdomain localhost6 localhost6.localdomain6" . PHP_EOL;
        return $settingHeader . $setting;
    }
    
	/**
	     * 虚拟化中心自动刷新
	     * @param array params
	     */
	    public function refreshVcenterTime($params){
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
	    public function getrefreshVcenterTime($params){
	    	$sql = "select vcenter_refresh_interval, authorized_flag from bd_system";
	    	$result = $this->dbSelect($sql);
	    	$info = array(
	    		'vcenter_refresh_interval' => $result[0]['vcenter_refresh_interval'],
	    		'authorized_flag'	=> intval($result[0]['authorized_flag'])
	    	);
	    	return json_encode($info);
	    }    

   /**
    * 消息推送配置
    * @param array $params
    */
	public function messagePush($params){
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
		$result = $this->dbExec($sql, array($username, $password,  $protocol, $domain, $port, $pushflag, $mode, $pushtype));
		
		return $this->muOpResult($result, Xphp::$_lang['UI_SETTINGS_MESSAGE_PUSH_CONFIG_MODIFY']);
	}
	
	/**
	 * 消息推送测试
	 * @param array $settings
	 */
	private function messagePushTest($settings){
	    switch (intval($settings['protocol'])){
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
	private function messagePushTestStomp($settings){
	    $username = $settings['username'];
	    $password = $settings['password'];
	    $domain = $settings['domain'];
	    $port = $settings['port'];
	    $connect = "tcp://" . $domain . ":" . $port;
	    try{
	        $stomp = new Stomp($connect, $username, base64_decode($password));
	    } catch(StompException $e) {
	        exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_MESSAGE_PUSH_CONNECT_TEST'], $e->getMessage(), 'warning'));
	    }
	}
	
	/**
	 * 消息推送测试 openwire协议
	 * @param array $settings
	 */
	private function messagePushTestOpenwire($settings){
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
	    if(!$msg['result']){
	        exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_MESSAGE_PUSH_CONNECT_TEST'], $msg['errorMsg'], 'warning', 
	            $msg['errorCode']));
	    }
	}
	
	/**
	 * 消息推送历史数据获取
	 * @param array $params
	 */
	public function messagePushOldInfo(){
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
	public function sendReportNotice($settings){
		$strategy = $settings['timeStrategy'];
		$reportList = $settings['report_check_list'];
		$currentTime = strtotime(date("H:i:s"));
		$currentDate = strtotime(date('Y-m-d H:i:s'));
		$this->writeLog('start check every report time!!!!');
		$typeList = array();
		foreach ($strategy as $s){
			$type = $s['type'];
			switch($type){
				case 1:
					$time = strtotime($s['notice_time']);
					$days = array();
					$diffTime = $currentTime - $time;
					if($diffTime < 60 && $diffTime>= 0){
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
					if($weekDay == 0){
						$weekDay = 7;
					}
					$days = $s['days'];
					$dayList = array();
					for($i=0;$i<count($days);$i++){
						if($days[$i] == 1){
							$dayList[] = $i + 1;
						}
					}
					$diffTime = $currentTime - $time;
					if($diffTime < 60 && $diffTime>= 0 && in_array($weekDay, $dayList)){
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
					for($i=0;$i<count($days);$i++){
						if($days[$i] == 1){
							$dayList[] = $i + 1;
						}
					}
					$diffTime = $currentTime - $time;
					if($diffTime <60 && $diffTime>= 0 && in_array($monthDay, $dayList)){
						$flag = true;
						$typeList[] = array(
							'flag' => $flag,
							'type' => $type
						);
					}
					break;
				case 4:
					$time = strtotime($s['notice_time']);
					$days = array();
					$diffTime = $currentDate-$time;
					if($diffTime <60 && $diffTime>= 0){
						$flag = true;
						$typeList[] = array(
							'flag' => $flag,
							'type' => $type
						);
					}
					break;
			}
			
		}
		//如果没有符合时间的类型
		if(count($typeList) == 0){
			$this->writeLog('no report!!!!');
			return true;
		}
		$this->sendTimeReport($typeList,$reportList);
	}
	
	public function sendTimeReport($typeList, $reportList){
		foreach ($typeList as $t){
			$type = $t['type'];
			switch ($type){
				case 1: 
					if($t['flag']){
						$this->writeLog('send Days report !!!!');
						$this->sendReportEmail($t, $reportList);
					}
					break;
					
				case 2:
					if($t['flag']){
						$this->writeLog('send Week report !!!!');
						$this->sendReportEmail($t, $reportList);
					}
					break;
					
				case 3:
					if($t['flag']){
						$this->writeLog('send Month report !!!!');
						$this->sendReportEmail($t, $reportList);
					}
					break;
				case 4:
					if($t['flag']){
						$this->writeLog('send Year report !!!!');
						$this->sendReportEmail($t, $reportList);
					}
					break;
				
			}
		}
	}
	
	/**
	 * 设置发送报表邮件接口
	 * @param array $strategy
	 * @param array $reportList
	 */
	public function sendReportEmail($strategy, $reportList){
		if($reportList['storage']){
			$this->writeLog("start send storage report!!".$strategy['type']);
			$this->sendStorageReport($strategy);	
		}
		if($reportList['vm']){
			$this->writeLog	("start send VM report!!".$strategy['type']);
			$this->sendVmReport($strategy);
		}
	}
	
	/**
	 * 发送存储报表通知
	 * @param array $strategy
	 * 
	 */
	public function sendStorageReport($strategy){
		$utils = Xphp::instance('Utils');
		$type = $strategy['type'];
		$sql = "select count(storage_id) as total, sum(total_size) as total_size, sum(free_size) as free_size from bd_storage_resource where lan_free_flag = ?";
		$data = $this->dbSelect($sql, array(Xphp::$_config['FLAG']['UNSET']));
		$totalSize = $utils->calSize($data[0]['total_size']);
		$freeSize = $utils->calSize($data[0]['free_size']);
		$totalNum = $data[0]['total'];
		$sqlReport = "select count(distinct bbt.storage_uuid)as storage_num, sum(bbt.real_size) as storage_size from bd_backup_timepoint bbt,bd_storage_resource bsr where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ? ";
		$sqlStorageSize = "select bsr.storage_nickname, sum(bbt.real_size) current_size from bd_storage_resource bsr,bd_backup_timepoint bbt where bsr.storage_uuid = bbt.storage_uuid and bsr.lan_free_flag = ? ";
		$sqlReportParams = array();
		switch($type){
			case 1:
				$sqlReport .=" and to_days(now()) - to_days(bbt.timepoint) = 1";  //昨天
				$sqlStorageSize .=" and to_days(now()) - to_days(bbt.timepoint) = 1";
				
				$timeRange = date("Y-m-d",strtotime("-1 day"));
				$reportTitle = Xphp::$_lang['UI_REPORT_DAILY'] . $timeRange;
				$timeDes = Xphp::$_lang['UI_REPORT_THIS_DAY'];
				break;
			case 2:
				$sqlReport .= " and  YEARWEEK(date_format(bbt.timepoint,'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1"; // 上一周
				$sqlStorageSize .=" and  YEARWEEK(date_format(bbt.timepoint,'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
				
				$timeRange =date('Y-m-d', strtotime('-2 monday', time())) . "--" . date('Y-m-d', strtotime('-1 sunday', time()));
				$reportTitle = Xphp::$_lang['UI_REPORT_WEEKLY'] . $timeRange;
				$timeDes = Xphp::$_lang['UI_REPORT_THIS_WEEK'];
				break;
			case 3:
				$sqlReport .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1"; //上个月
				$sqlStorageSize .=" and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";
				
				$timeRange =date('Y-m-01',strtotime('-1 month')) . "--" . date('Y-m-t',strtotime('-1 month'));
				$reportTitle = Xphp::$_lang['UI_REPORT_MONTHLY'] . $timeRange;
				$timeDes = Xphp::$_lang['UI_REPORT_THIS_MONTH'];
				break;
			case 4:
				$sqlReport .= " and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))"; //上一年
				$sqlStorageSize .=" and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))";
				
				$timeRange = date('Y-01-01',strtotime('-1 year')) . "--" . date('Y-12-31',strtotime('-1 year'));
				$reportTitle = Xphp::$_lang['UI_REPORT_ANNALS'] . $timeRange;
				$timeDes = Xphp::$_lang['UI_REPORT_THIS_YEAR'];
				break;
		}	
		$dataReport = $this->dbSelect($sqlReport, array(Xphp::$_config['FLAG']['UNSET']));
		$dataStorageSize = $this->dbSelect($sqlStorageSize, array(Xphp::$_config['FLAG']['UNSET']));
		$currentSize= $utils->calSize($dataReport[0]['storage_size']);
		$currentNum = intval($dataReport[0]['storage_num']);
		
		$sqlStorage = "select bn.ip, bn.node_nickname, bn.host_name, bsr.storage_uuid, bsr.storage_nickname, bsr.storage_type, bsr.node_uuid, bsr.total_size, bsr.free_size from bd_storage_resource bsr, bd_node bn where bn.node_uuid = bsr.node_uuid and bsr.lan_free_flag = ? order by bsr.free_size desc";
		$dataStorage = $this->dbSelect($sqlStorage, array(Xphp::$_config['FLAG']['UNSET']));
		$storageList = array();
		$storageHandler = Xphp::instance('StorageHandler');
		$nodeHandler = Xphp::instance('NodeHandler');
		foreach ($dataStorage as $storage){
			foreach ($dataStorageSize as $d){
				$currentStorageSize = 0;
				if($d['storage_nickname'] && $storage['storage_nickname'] == $d['storage_nickname']){
					$currentStorageSize = $d['current_size'];
				}
				$usedSize = $storage['total_size'] - $storage['free_size'];
				$storageList[] = array(
						"storage_name" => $storage['storage_nickname'],
						"storage_type" => $storageHandler->getStorageTypeDes(intval($storage['storage_type'])),
						"node" =>  $nodeHandler->getNodeGridName($storage['ip'], $storage['node_nickname'], $storage['host_name']) . "(" . $storage['ip'] . ")",
						"total_size" => $utils->calSize($storage['total_size']),
						"used_size" => $utils->calSize($usedSize),
						"free_size" => $utils->calSize($storage['free_size']),
						"current_size" => $utils->calSize($currentStorageSize)
				);
			}
		}
		$reportTime = date('Y-m-d H:i:s');
		
		$language = $this->getSystemLang();
        if($language == "zh-cn" || $language == "zh-tw"){
            $message = file_get_contents(ROOT_PATH.'/email/email-storage-report.html');
        }else{
            $message = file_get_contents(ROOT_PATH.'/email/email-storage-report-en.html');
        }
		$content = "";
		if ($storageList){
			foreach ($storageList as $s){
				$info = "<tr><td>".$s['storage_name']."</td>".
						"<td>".$s['storage_type']."</td>".
						"<td>".$s['node']."</td>".
						"<td>".$s['total_size']."</td>".
						"<td>".$s['used_size']."</td>".
						"<td style='color:green;'>".$s['free_size']."</td>".
						"<td>".$s['current_size']."</td></tr>";
				$content .= $info;
			}
			$message = str_replace('Content', $content, $message);
		}else{
			$noInfo = '<table border="0" width= "100%">
                  			<thead style="text-align:left;">
                  				<tr>
                  					<th width="16%" style="text-align:left;">'.Xphp::$_lang['UI_REPORT_STORAGE_NAME'].'</th>
                  					<th width="16%" style="text-align:left;">'.Xphp::$_lang['UI_REPORT_STORAGE_TYPE'].'</th>
                  					<th width="18%" style="text-align:left;">'.Xphp::$_lang['UI_REPORT_NODE'].'</th>
                  					<th width="12%" style="text-align:left;">'.Xphp::$_lang['UI_REPORT_STORAGE_TOTAL_SIZE'].'</th>
                  					<th width="12%" style="text-align:left;">'.Xphp::$_lang['UI_REPORT_STORAGE_USED_SIZE'].'</th>
                  					<th width="12%" style="text-align:left;">'.Xphp::$_lang['UI_REPORT_STORAGE_FREE_SIZE'].'</th>
                  					<th width="14%" style="text-align:left;">'.Xphp::$_lang['UI_REPORT_CURRENT_USED_SIZE'].'</th>
                  				</tr>
                  			</thead>
                  			<tbody style="text-align:left;">
                  				Content
                  			</tbody>
                  		</table>';
			$content = '<div style="text-align:center;font-size:16px;">'.Xphp::$_lang['UI_REPORT_NO_INFO'].'</div>';
			$message = str_replace($noInfo, $content, $message);
		}
		$message = str_replace('timeRange', $reportTitle, $message);
		$message = str_replace('reportTime', $reportTime, $message);
		
		$message = str_replace('totalSize', $totalSize, $message);
		$message = str_replace('freeSize', $freeSize, $message);
		$message = str_replace('currentNum', $currentNum, $message);
		$message = str_replace('currentSize', $currentSize, $message);
		$message = str_replace('timeDes', $timeDes, $message);
		
		$title =  Xphp::$_lang['UI_REPORT_STORAGE'] ."—— ".$reportTitle;
		
		$this->writeLog("send storage report email!!!!!!!!!!!".$reportTime);
		$this->sendUnifyEmail($title, $message);
	}	
	
	/**
	 * 发送虚拟机报表通知
	 *
	 */
	public function sendVmReport($strategy){
		$utils = Xphp::instance('Utils');
		$type = $strategy['type'];
		$totalSql = "select count(vt.dir_path) as total_vm_num from vm_tree vt, vm_vcenter vv where vt.vcenter_uuid = vv.vcenter_uuid and vt.type = ? and vt.display_mode =?";
		$dataTotalVm = $this->dbSelect($totalSql, array(7, Xphp::$_config['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']));
		$protectedSql = "select count(distinct dir_path) as protected_vm_num, sum(real_size) as storage_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid";
		$dataProtect = $this->dbSelect($protectedSql, array());
		$totalVms = intval($dataTotalVm[0]['total_vm_num']);
		$protectVms = intval($dataProtect[0]['protected_vm_num']);
		$totalSize = $utils->calSize($dataProtect[0]['storage_size']);
		
		$sqlBackupNum = "select count(timepoint_uuid) as current_success_num ,sum(real_size) as storage_size from bd_backup_timepoint ";
		$sqlvm = "select distinct vbt.dir_path from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid ";
		$sqlCurrentVm = "select count(distinct vbt.dir_path) as current_vms from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid ";
		$sqlDayInfo = "select unix_timestamp(finish_time) finish_time, details, submodule_type, task_name from bd_history_task where task_type = ? and module_type = ? ";
		
		switch($type){
			case 1:
				$sqlDayInfo .= " and to_days(now()) - to_days(date_format(finish_time, '%Y-%m-%d')) = 1";
				$sqlBackupNum .=" where to_days(now()) - to_days(date_format(timepoint, '%Y-%m-%d')) = 1";  //昨天
				$sqlCurrentVm .=" and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天
				$sqlvm .=" and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天
				
				$timeRange = date("Y-m-d",strtotime("-1 day"));
				$reportTitle = Xphp::$_lang['UI_REPORT_DAILY'] . $timeRange;
				$timeDes = Xphp::$_lang['UI_REPORT_THIS_DAY'];
				break;
			case 2:
				$sqlDayInfo .= " and YEARWEEK(date_format(date_format(finish_time, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
				$sqlBackupNum .= " where YEARWEEK(date_format(date_format(timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1"; // 上一周
				$sqlCurrentVm .=" and YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1"; 
				$sqlvm .=" and YEARWEEK(date_format(date_format(bbt.timepoint, '%Y-%m-%d'),'%Y-%m-%d'),1) = YEARWEEK(now(),1) - 1";
				
				$timeRange =date('Y-m-d', strtotime('-2 monday', time())) . "--" . date('Y-m-d', strtotime('-1 sunday', time()));
				$reportTitle = Xphp::$_lang['UI_REPORT_WEEKLY'] . $timeRange;
				$timeDes = Xphp::$_lang['UI_REPORT_THIS_WEEK'];
				break;
			case 3:
				$sqlDayInfo .= " and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(finish_time, '%Y%m')) =1";
				$sqlBackupNum .= " where PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(timepoint, '%Y%m')) =1"; //上个月
				$sqlCurrentVm .=" and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1"; 
				$sqlvm .=" and PERIOD_DIFF(date_format(now(), '%Y%m'), date_format(bbt.timepoint, '%Y%m')) =1";
				
				$timeRange =date('Y-m-01',strtotime('-1 month')) . "--" . date('Y-m-t',strtotime('-1 month'));
				$reportTitle = Xphp::$_lang['UI_REPORT_MONTHLY'] . $timeRange;
				$timeDes = Xphp::$_lang['UI_REPORT_THIS_MONTH'];
				break;
			case 4:
				$sqlDayInfo .= " and year(finish_time)=year(date_sub(now(),interval 1 year))";
				$sqlBackupNum .= " where year(timepoint)=year(date_sub(now(),interval 1 year))"; //上一年
				$sqlCurrentVm .=" and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))"; 
				$sqlvm .=" and year(bbt.timepoint)=year(date_sub(now(),interval 1 year))";
				
				$timeRange = date('Y-01-01',strtotime('-1 year')) . "--" . date('Y-12-31',strtotime('-1 year'));
				$reportTitle = Xphp::$_lang['UI_REPORT_ANNALS'] . $timeRange;
				$timeDes = Xphp::$_lang['UI_REPORT_THIS_YEAR'];
				break;
		}
		
		$dataVm = $this->dbSelect($sqlvm);
		$vmList = array();
		if($dataVm){
			foreach($dataVm as $d){
				$vmList[]  = $this->getReportVmInfo($type, $d['dir_path']); //虚拟机列表信息
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
		foreach ($dataInfo as $d){
			$details = json_decode($d['details'],true);
			foreach ($details as $detail){
				$currentNum ++;
				if($detail['task_status'] == Xphp::$_config['VmTaskStatus']['FINISH']){
					$currentSuccessNum ++;
				}else{
					$failedNum ++;
				}
				$daysVmList = array(
					"vm_name" => $detail['vm_name'],
					"hypervisor" =>  Xphp::$_config['VMHYPERVISORDES'][intval($d['submodule_type'])],
					"task_name" => $d['task_name'],
					"backup_time" => date('Y-m-d H:i:s', $d['finish_time']),
					"status" => intval($detail['task_status']),
					"statusDes" => $this->getVmBackupStatus(intval($detail['task_status'])), 
					"storage_size" =>  $utils->calSize(intval($detail['real_size']))	
				);
				if(intval($detail['task_status']) == 3){
					$successList[] = $daysVmList;
				}else{
					$failedList[] = $daysVmList;
				}
			}
			
		}
		
		$vmInfoList = array_merge($successList, $failedList);
		
		$currentNum = $currentNum;
		$currentVms = $dataCurrenVms[0]['current_vms'];
// 		$currentSuccessNum = $dataCurrent[0]['current_success_num'];
		$currentSize = $utils->calSize($dataCurrent[0]['storage_size']);
		$failedNum = str_replace("-", "", $failedNum);	
		$language = $this->getSystemLang(); //获取系统语言
		$content = "";
		if($type == 1){
			if($language =="en-us"){
				$message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-days-report-en.html');
			}else{
				$message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-days-report.html');
			}
			if($vmInfoList){
				foreach ($vmInfoList as $vmInfo){
					if($vmInfo['status'] == 3){
						$status = "<td style='color:green;'>".$vmInfo['statusDes']."</td>";
					}else{
						$status = "<td style='color:red;'>".$vmInfo['statusDes']."</td>";
					}
					$info = "<tr><td>".$vmInfo['vm_name']."</td>".
							"<td>".$vmInfo['hypervisor']."</td>".
							"<td>".$vmInfo['task_name']."</td>".
							"<td>".$vmInfo['backup_time']."</td>".
							$status.
							"<td style='color:green;'>".$vmInfo['storage_size']."</td></tr>";
					$content .= $info;
				}
				$message = str_replace('Content', $content, $message);
			}else{
				$noInfo = '<table border="0" width= "100%">
                  			<thead style="text-align:left;">
                  				<tr>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_VM_NAME'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_HYPERVISOR'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_JOB_NAME'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_FINISH_TIME'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_BACKUP_STATUS'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_BACKUP_SIZE'].'</th>
                  				</tr>
                  			</thead>
                  			<tbody style="text-align:left;">
                  				Content
                  			</tbody>
                  		</table>';
				$content = '<div style="text-align:center;font-size:16px;">'.Xphp::$_lang['UI_REPORT_NO_INFO'].'</div>';
				$message = str_replace($noInfo, $content, $message);
			}
		}else{
			if($language =="en-us"){
				$message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-vmreport-notice-en.html');
			}else{
				$message = file_get_contents(ROOT_PATH.'/content/platform/reports/page/email-vmreport-notice.html');
			}
			if($vmList){
				foreach ($vmList as $vm){
					$info = "<tr><td>".$vm['vm_name']."</td>".
							"<td>".$vm['hypervisor']."</td>".
							"<td>".$vm['task_name']."</td>".
							"<td>".$vm['last_backup_time']."</td>".
							"<td>".$vm['backup_num']."</td>".
							"<td style='color:green;'>".$vm['storage_size']."</td></tr>";
					$content .= $info;
				}
				$message = str_replace('Content', $content, $message);
			}else{
				$noInfo = '<table border="0" width= "100%">
                  			<thead style="text-align:left;">
                  				<tr>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_VM_NAME'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_HYPERVISOR'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_JOB_NAME'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_LAST_BACKUP_TIME'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_SUCCESS_BACKUP_NUM'].'</th>
                  					<th style="text-align:left;">'.Xphp::$_lang['UI_REPORT_BACKUP_SIZE'].'</th>
                  				</tr>
                  			</thead>
                  			<tbody style="text-align:left;">
                  				Content
                  			</tbody>
                  		</table>';
				$content = '<div style="text-align:center;font-size: 16px;">'.Xphp::$_lang['UI_REPORT_NO_INFO'].'</div>';
				$message = str_replace($noInfo, $content, $message);
			}
			
		}
		$reportTime = date('Y-m-d H:i:s');
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
		$title =  Xphp::$_lang['UI_REPORT_VM']."—— ".$reportTitle;
		$this->writeLog("send VM report email!!!!!!!!!!!".$reportTime);
		$this->sendUnifyEmail($title, $message);
	}
	
	public function getReportVmInfo($type, $dirPath){
		$sqlVMInfo = "select vbt.vm_name, vbt.hypervisor_type, bbt.task_name from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
		$dataInfo = $this->dbSelect($sqlVMInfo,array($dirPath));
				
		$sql = "select count(bbt.timepoint_uuid) as total, sum(bbt.real_size) as storage_size from vm_backup_timepoint vbt, bd_backup_timepoint bbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
		$sqlTime = "select bbt.timepoint from bd_backup_timepoint bbt, vm_backup_timepoint vbt where bbt.timepoint_uuid = vbt.timepoint_uuid and vbt.dir_path = ? ";
		$sqlParams = array($dirPath);
		switch($type){
			case 1:
				$sql .=" and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1";  //昨天
				$sqlTime .=" and to_days(now()) - to_days(date_format(bbt.timepoint, '%Y-%m-%d')) = 1 ";
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
		if($dataTime){
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
	public function sendUnifyEmail($title, $content){
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
		$userHandler->sendEmail($params);
		
	}
	
	/**
	 * 读取报告通知配置信息
	 */
	public function getSettings(){
		$sql = "select report_config from bd_email_notice";
		$data = $this->dbSelect($sql, array());
		return json_decode($data[0]['report_config'], true);
	}
	
	/**
	 * 获取虚拟机报表虚拟机备份状态描述
	 * @param int $status
	 */
	private function getVmBackupStatus($status){
		$statusDes = Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
		
		if($status == 3){
			$statusDes = Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
		}else{
			$statusDes = Xphp::$_lang['WEB_PUBLIC_FAILURE'];
		}
		return $statusDes;
	}
	
	public function getSystemLang(){
		$sql ="select language from bd_user";
		$data = $this->dbSelect($sql, array());
		
		return $data[0]['language'];
	}
	
	

	/**
	 * 上传升级包到指定目录
	 * 
	 */
	public function uploadPatch(){
		// Support CORS
		// header("Access-Control-Allow-Origin: *");
		// other CORS headers if any...
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
			exit; // finish preflight CORS requests here
		}
		
		
		if ( !empty($_REQUEST[ 'debug' ]) ) {
			$random = rand(0, intval($_REQUEST[ 'debug' ]) );
			if ( $random === 0 ) {
				header("HTTP/1.0 500 Internal Server Error");
				exit;
			}
		}
		
		
		// 5 minutes execution time
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
		while ($buff = fread($in, 4096)) {
			fwrite($out, $buff);
		}
		
		@fclose($out);
		@fclose($in);
		
		rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
		
		$index = 0;
		$done = true;
		for( $index = 0; $index < $chunks; $index++ ) {
			if ( !file_exists("{$filePath}_{$index}.part") ) {
				$done = false;
				break;
			}
		}
		if ( $done ) {
			if (!$out = @fopen($uploadPath, "wb")) {
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			}
		
			if ( flock($out, LOCK_EX) ) {
				for( $index = 0; $index < $chunks; $index++ ) {
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
	 * 更新升级包列表记录
	 * @param unknown $params
	 */
	public function updatePatchList($params){
		$utils = Xphp::instance('Utils');
		$uuid = $utils->uuid();
		$name = $params['name'];
		$fileSize = $params['size'];
		$fileInfo = array(
			"file_size" => $fileSize
		);
		$filePath = Xphp::$_config['UPLOAD_PATH'].$name;
		$md5file = "";
		if(md5_file($filePath)){
			$md5file = md5_file($filePath);
		}
		$this->checkPatchExist($md5file); //检查文件是否存在
		
		$fileUrl = "/tmp/upgrade/".$name;
// 		$nodeInfo = $this->getNodeInfo();
		$nodeuuid = $this->getMasterNode();
		
		$status = 6;
		$uploadTime = date("Y-m-d H:i:s");
		$sql = "insert into bd_update_file (uuid, level1_md5, node_uuid, name, path, status, controller_file_url, upload_time, extra) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$result = $this->dbQuery($sql, array($uuid, $md5file,  $nodeuuid, $name, $filePath, $status, $fileUrl, $uploadTime, json_encode($fileInfo)));
		if($result == 1){
			return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_UPDATE_FILE_UPLOAD']);
		}else{
			return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_FILE_UPLOAD'], Xphp::$_lang['UI_SETTINGS_UPDATE_FILE_UPLOAD_ERROR'], "warning");
		}
		
	}
	
	/**
	 * 得到补丁包列表
	 * @param unknown $params
	 */
	public function getPatches($params){
		$start = $params['start'];
		$length = $params['length'];
		$sortColumn = $params['sortColumn'];
		$sortType = $params['sortType'];
		$sortArr = array('', '', '', '', 'upload_time');
		$nodeuuid = $this->getMasterNode();
		$sql = "select uuid, name,level1_md5,upload_time, extra from bd_update_file where node_uuid = ? order by $sortArr[$sortColumn] $sortType limit ? , ? ";
		$sqlCount = "select count(uuid) as total from bd_update_file where node_uuid = ?";
		
		$data = $this->dbSelect($sql, array($nodeuuid,$start, $length));
		$count =$this->dbSelect($sqlCount, array($nodeuuid));
	
		$records = array();
		$records["data"] = array();
		$id = $start + 1;
		$utils = Xphp::instance('Utils');
		foreach ($data as $d){
			$fileInfo = json_decode($d['extra'],true);
			$fileSize = $fileInfo['file_size'];
			$records["data"][] = array(
					'<input type="checkbox" name="id[]" value="'. $d['uuid'] .'">',
					$d['name'],
					$d['level1_md5'],
					$utils->calSize($fileSize),
					$d['upload_time']
			);
		}
	
		$records["draw"] = $params['draw'];
		$records["recordsTotal"] = $count[0]['total'];
		$records["recordsFiltered"] = $count[0]['total'];
		return  json_encode($records);
	}
	
	/**
	 * 删除升级包
	 * @param unknown $params
	 */
	public function deletePatch($params){
		$uuids= $params['uuids'];
		$nameList= array();
		foreach ($uuids as $uuid){
			$nameList[] = $this->getPatchName($uuid);
		}
		$this->paramsCheck($uuids);
		$uuidList = implode("','", $uuids);
		$sql = "delete from bd_update_file where uuid in ('". $uuidList ."')";
		$result = $this->dbExec($sql, array());
		if($result){
			foreach ($nameList as $name){
				$deleteFile = Xphp::$_config['UPLOAD_PATH'].$name;
				if(file_exists($deleteFile)){
					$cmd = "rm -rf ".Xphp::$_config['UPLOAD_PATH'].$name;
					exec($cmd);
				}
			}
		}
		return $this->muOpResult($result, Xphp::$_lang['UI_SETTINGS_UPDATE_DELETE_PATCH']);
	}
	
	
	/**
	 * 删除升级历史
	 * @param unknown $params
	 */
	public function deletePatchHistory($params){
		$ids= $params['ids'];
		$nameList= array();
		foreach ($ids as $id){
			$this->checkDeleteHistory($id);
		}
		$this->paramsCheck($ids);
		$idsList = implode("','", $ids);
		$sql = "delete from bd_update_log where id in ('". $idsList ."')";
		$result = $this->dbExec($sql, array());
		return $this->muOpResult($result, Xphp::$_lang['UI_SETTINGS_UPDATE_DELETE_HISTORY']);
	}
	
	/**
	 * 获取升级包名字
	 * @param string $uuid
	 */
	public function getPatchName($uuid){
		$sql = "select name from bd_update_file where uuid = ?";
		$data = $this->dbSelect($sql, array($uuid));
		$name = $data[0]['name'];
		return $name;
	}
	
	/**
	 * 检查是否为失败的升级历史
	 * @param int $id
	 */
	public function checkDeleteHistory($id){
		$sql = "select errno from bd_update_log where id = ?";
		$data = $this->dbSelect($sql, array($id));
		$errno = intval($data[0]['errno']);
		if($errno == 0) {
			exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_DELETE_HISTORY'],Xphp::$_lang['UI_SETTINGS_UPDATE_DELETE_HISTORY_ERROR_TIPS'], "warning"));
		}
	}
	
	
	/**
	 * 得到升级历史
	 * @param unknown $params
	 */
	public function getPatchHistory($params){
		$start = $params['start'];
		$length = $params['length'];
		$sortColumn = $params['sortColumn'];
		$sortType = $params['sortType'];
		$sortArr = array('', '', '', '', "",'log_time', 'errno', '');
	
		$sql = "select id, node_uuid, patch_file_name, log_time, log_file_path, errno from bd_update_log order by $sortArr[$sortColumn] $sortType limit ? , ? ";
		$sqlCount = "select count(id) as total from bd_update_log";
	
		$data = $this->dbSelect($sql, array($start, $length));
		$count =$this->dbSelect($sqlCount);
	
		$records = array();
		$records["data"] = array();
		$id = $start + 1;
		$utils = Xphp::instance('Utils');
		foreach ($data as $d){
			$nodeInfo = $this->getNodeInfo($d['node_uuid']);
			$checkbox = '<input type="checkbox" name="id[]" value="'. $d['id'] .'">';
			$downloadDiv = '<a id="download'.$d['id'].'"  value="'.$d['log_file_path'].'">'.Xphp::$_lang['UI_NODE_DOWNLOAD'].'</a>';
			if(empty($d['log_file_path'])){
				$downloadDiv = Xphp::$_config['NULLSPACE'];
			}
			$records["data"][] = array(
					$checkbox,
					$id++,
					$nodeInfo['node_name'],
					$nodeInfo['ip'],
					$d['patch_file_name'],
					$d['log_time'],
					$this->getlogDes($d['errno']),
					$downloadDiv,
					intval($d['errno']),
					$d['id'],
					$d['log_file_path']
					
			);
		}
		
		$records["draw"] = $params['draw'];
		$records["recordsTotal"] = $count[0]['total'];
		$records["recordsFiltered"] = $count[0]['total'];
		return  json_encode($records);
	}
	
	/**
	 * 下载升级历史日志
	 * @param unknown $params
	 */
	public function downloadHistory($params){
		$path = $params['path'];
		$info = $this->getUpdateHistory($path);
		$time = date("Y-m-d_H-i-s",strtotime($info['log_time']));
		$fileName = $info['node_name'].'_'.$time.'_'.'update_log.txt';
		$content = '';
		$opName = 'BD_SYSTEM_OP_READ_FILE';
        $msg = array('file_path' => $path);
       	$msg = $this->mbNodeMsg($opName, $info['node_uuid'], json_encode($msg), true);
       	if($msg['result']){
       		$content = $msg['msg']['file_content']; 
       	}else{
       		return $this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_GET_HISTORY_LOG'],Xphp::$_lang['UI_SETTINGS_UPDATE_GET_HISTORY_LOG_ERROR'],"warning");
       	}
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: " . strlen($content));
		Header("Content-Disposition: attachment; filename=".$fileName);
		return $content;
	}
	
	/**
	 * 获取升级历史日志文件名字信息
	 * @param string $path
	 */
	public function getUpdateHistory($path){
		$sql = "select node_uuid, log_time from bd_update_log where log_file_path = ?";
		$data = $this->dbSelect($sql, array($path));
		$nodeInfo = $this->getNodeInfo($data[0]['node_uuid']);
		$info = array(
			'node_name' => $nodeInfo['node_name'],
			'log_time' => $data[0]['log_time'],
			'node_uuid' => $data[0]['node_uuid']
		);
		return $info;
	}
	
	/**
	 * 获取节点信息
	 * @param string $nodeuuid
	 */
	public function getNodeInfo($nodeuuid){ 
		$sql ="select node_uuid, ip,host_name, node_nickname from bd_node ";
		$sqlParams = array();
		if($nodeuuid){
			$sql .= " where node_uuid = ?";
			$sqlParams = array($nodeuuid);
		}
		$data = $this->dbSelect($sql, $sqlParams);
		$nodeHandler = Xphp::instance('NodeHandler');
		$info = array(
				'node_uuid'=> $data[0]['node_uuid'],
				'ip' => $data[0]['ip'],
				"node_name" => $nodeHandler->getNodeShowName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name'])
		);
		return $info;
	}
	
	/**
	 * 获取升级历史状态描述
	 * @param int $error
	 */
	public function getlogDes($error){
		$des = Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
		if($error == 0){
			$des = Xphp::$_lang['WEB_PUBLIC_SUCCESS'];
		}else{
			$des = Xphp::$_lang['WEB_PUBLIC_FAILURE'];
		}
		return $des;
	}
	
	/**
	 * 获取选中的升级包的信息
	 * @param unknown $params
	 */
	public function getSelectPatch($params){
		$sql = "select name,path from bd_update_file where uuid = ?";
		$data = $this->dbSelect($sql, array($params['uuid']));
		$info =array(
			'name' => $data[0]['name'],
			'path' => $data[0]['path']
		);
		return json_encode($info);
	}
	
	/**
	 * 启动升级检查
	 * @param unknown $params
	 */
	public function upgradeCheck($params){
		$this->checkRunningJob();//检查是否有运行任务
		$nodeuuids = $params['nodeuuids'];
		$masterFlag = $params['masterFlag'];
		$name = $params['name'];
		$path = Xphp::$_config['UPLOAD_PATH'].$name;
		$md5 = md5_file($path);
		$url =  "/tmp/upgrade/".$name;
		$patchuuid = $params['uuid'];
		if($masterFlag){
			$this->checkReUpdate($nodeuuids[0], $name);
			
		}else{
			foreach ($nodeuuids as $nodeuuid){
				$this->checkReUpdate($nodeuuid, $name);
			}
		}
		
		return $this->muOpResult(true, Xphp::$_lang['UI_SETTINGS_UPDATE_START']);
	}
	
	
	/**
	 * 升级补丁检查是否存在正在运行中的任务
	 */
	private function checkRunningJob(){
		$sql = "select task_uuid from bd_task where task_status = ?";
		$data = $this->dbSelect($sql, array(Xphp::$_config['TASKSTATUS']['RUNNING']));
		if(!empty($data)){
			exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR'], Xphp::$_lang['UI_SETTINGS_UPDATE_RUNNING_JOB_EXIST'],"warning"));
		}
	}
	
	/**
	 * 重新升级检查
	 * @param string $nodeuuid
	 * @param string $md5
	 */
	public function checkReUpdate($nodeuuid, $name){
		$sql = "select log_file_path from bd_update_log where patch_file_name = ? and node_uuid = ? and errno = ?";
		$data = $this->dbSelect($sql, array($name,$nodeuuid, 0));
		if($data){
			exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR'], Xphp::$_lang['UI_SETTINGS_UPDATE_START_ERROR_TIPS'],"warning"));
		}
	}
	
	/**
	 * 启动升级
	 * @param unknown $params
	 */
	public function upgradeSystem($params){
		$nodeuuids = $params['nodeuuids'];
		$masterFlag = $params['masterFlag'];
		$name = $params['name'];
		$path = Xphp::$_config['UPLOAD_PATH'].$name;
		$md5 = md5_file($path);
		$url =  "/tmp/upgrade/".$name;
		$patchuuid = $params['uuid'];
		
		if($masterFlag){
			$opName = "NODE_SYS_OP_CHECK_PATCH_VALIDITY";
			$msg = array("patch_uuid" => $patchuuid, "patch_file_path" => $path);
			$mbResult = $this->mbNodeMsg($opName,  $nodeuuids[0], json_encode($msg));
			if($mbResult['result']){
				$opName = 'NODE_SYS_OP_DO_MASTER_UPDATE';
				$msg = array("patch_uuid" => $patchuuid, "patch_file_path" => $path);
				$mbResult = $this->mbNodeMsg($opName, $nodeuuids[0], json_encode($msg));
			}
		}else{
			$childInfo = array();
			$sqlParams = array(
				"file_url" => $url,
				"file_name"=> $name,
				"file_md5" => $md5,
				"file_path" => $path
			);
			foreach ($nodeuuids as $nodeuuid){
				$this->updateNodePatch($sqlParams,$nodeuuid);
			}
// 			$this->checkMasterUpdate($md5); //检查有没有先更新主节点
			foreach ($nodeuuids as $nodeUUID){	//插入备份节点补丁包信息导数据库 
				$patchuuid = $this->updateNodePatch($sqlParams,$nodeUUID);
				$opName = 'NODE_SYS_OP_DOWNLOAD';
				$msg = array(
					"patch_uuid" => $patchuuid,
					"file_url" => $url,
					"file_name"=> $name,
					"file_md5" => $md5
				);
				$mbResult = $this->mbNodeMsg($opName, $nodeUUID, json_encode($msg));
				
				if($mbResult['result']){
					$opName = "NODE_SYS_OP_CHECK_PATCH_VALIDITY";
					$msg = array("patch_uuid" => $patchuuid, "patch_file_path" => $path);
					$mbResult = $this->mbNodeMsg($opName, $nodeUUID, json_encode($msg));
				}
				
				if($mbResult['result']){
					$opName = 'NODE_SYS_OP_DO_MASTER_UPDATE';
					$msg = array("patch_uuid" => $patchuuid, "patch_file_path" => $path);
					$mbResult = $this->mbNodeMsg($opName, $nodeUUID, json_encode($msg));
					
				}
			}
			return;
		}
	}
	
	/**
	 * 检查主节点是否已经升级
	 * @param unknown $params
	 */
	public function checkMasterUpdate($params){
		$nodeuuid = $params['nodeuuid'];
		$patchuuid = $params['patchuuid'];
		$md5 = $this->getMd5ByUUID($patchuuid);
		$sql = "select bul.log_file_path from bd_update_log bul, bd_update_file buf where bul.level1_md5 = buf.level1_md5 and bul.level1_md5 = ? and bul.node_uuid= ?";
		$data = $this->dbSelect($sql, array($md5, $nodeuuid));
		if(empty($data)){
			exit($this->muOpResult(false, Xphp::$_lang['UI_PLATFORM_SYSTEM_UPDATE'],Xphp::$_lang['UI_SETTINGS_UPDATE_MASTER_CHECK']));
		}
	}
	
	/**
	 * 获取主节点
	 * 
	 */
	public function getMasterNode(){
		$sqlNode = "select node_uuid from bd_node where node_type = ?";
		$dataNode = $this->dbSelect($sqlNode, array(Xphp::$_config['NODETYPE']['MASTER']));
		return $dataNode[0]['node_uuid'];
	}
	
	/**
	 * 得到升级可用的备份节点列表
	 * @param unknown $params
	 */
	public function getUpdateNodeList($params){
		$patchuuid = $params['uuid'];
		$md5 = $this->getMd5ByUUID($patchuuid);
		$sql = "select ip, host_name, node_nickname, node_uuid from bd_node order by node_type asc";
		$data = $this->dbSelect($sql, array());
		
		$info = array();
		$nodeHandler = Xphp::instance('NodeHandler');
		foreach ($data as $d){
			$info[] = array(
					"node_name" => $nodeHandler->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
					"node_uuid" => $d['node_uuid'],
					"ip" => $d['ip'],
					"update_flag" => $this->getUpdateFlag($d['node_uuid'], $md5)
			);
		}
		return json_encode($info);
	}
	
	/**
	 * 获取升级标志
	 * @param string $nodeuuid
	 * @param string $md5
	 */
	public function getUpdateFlag($nodeuuid,$md5){
		$sql = "select log_file_path from bd_update_log where node_uuid = ? and level1_md5 = ?";
		$data = $this->dbSelect($sql, array($nodeuuid,$md5));
		if($data){
			return true;
		}
		return false;
	}
	
	/**
	 * 插入备份节点升级记录信息，获取记录唯一uuid
	 * @param array $sqlParams
	 * @param string $nodeuuid
	 */
	public function updateNodePatch($sqlParams,$nodeuuid){
		$md5file = $sqlParams['file_md5'];
		//检查是否已经插入数据到数据库
		$sqlcheck = "select uuid from bd_update_file where level1_md5 = ? and node_uuid = ?";
		$dataCheck = $this->dbSelect($sqlcheck,array($md5file,$nodeuuid));
		if($dataCheck) return $dataCheck[0]['uuid'];
		$utils = Xphp::instance('Utils');
		$uuid = $utils->uuid();
		$name = $sqlParams['file_name'];
		$filepath = $sqlParams['file_path'];
		$fileUrl = $sqlParams['file_url'];
		$status = 6;
		$uploadTime = date('Y-m-d H:i:s');
		$sql = "insert into bd_update_file (uuid, level1_md5, node_uuid, name, path, status, controller_file_url, upload_time) values (?, ?, ?, ?, ?, ?, ?, ?)";
		$result = $this->dbQuery($sql, array($uuid, $md5file,  $nodeuuid, $name, $filepath, $status, $fileUrl, $uploadTime));
		return $uuid;
	}
	
	/**
	 * 获取升级包第一级MD5
	 * @param string $uuid
	 */
	public function getMd5ByUUID($uuid){
		$sqlmd5 = "select level1_md5 from bd_update_file where uuid =?";
		$datamd5 = $this->dbSelect($sqlmd5,array($uuid));
		return $datamd5[0]['level1_md5'];
	}
	
	/**
	 * 获取升级进度信息
	 * @param unknown $params
	 */
	public function getUpgradeInfo($params){
		$initFlag = $params['initflag'];
		$uuid = $params['uuid'];
		$fileMd5 = $this->getMd5ByUUID($uuid);
		$nodeuuids=$params['nodeuuids'];
		$nodeArray = implode("','", $nodeuuids);
		$sql = "select bn.node_uuid, bn.ip, bn.host_name, bn.node_nickname, bpf.update_progress,bpf.status from bd_update_file bpf,bd_node bn where bpf.node_uuid = bn.node_uuid and bpf.level1_md5 = ? and bpf.node_uuid in ('". $nodeArray ."')";
		$data = $this->dbSelect($sql,array($fileMd5));
		if(!$data){
			$basicInfo = array('flag' => 2);
			return json_encode($basicInfo);
		}
		$info = array();
		$nodeHandler = Xphp::instance('NodeHandler');
		foreach ($data as $d){
			$status = intval($d['status']);
			if(!$initFlag){
				$status = 6;
			}
			$info[] = array(
				"node_uuid" => $d['node_uuid'],
				"node_name" => $nodeHandler->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
				"progress" => $d['update_progress']."%",
				"flag" => true,
				"status" => $status,
				"statusDes" => $this->getUpdateStatusDes($status)
			);
		}
		
		return json_encode($info);
		
	}
	
	/**
	 * 获取升级状态描述
	 * @param int $status
	 */
	public function getUpdateStatusDes($status){
		$pfDes = include APP_PATH . "platform/PFDescription.php";
        $des = $pfDes['UPDATE_PATCH_DES'][$status];
        return $des;
	}
	
	/**
	 * 检查升级包是否存在
	 * @param string $md5
	 */
	public function checkPatchExist($md5){
		$nodeuuid = $this->getMasterNode();
		$sql = "select * from bd_update_file where level1_md5 = ? and node_uuid = ?";
		$data = $this->dbSelect($sql, array($md5, $nodeuuid));
		if($data){
			exit($this->muOpResult(false, Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_CHECK'],Xphp::$_lang['UI_SETTINGS_UPDATE_UPLOAD_CHECK_ERROR'], "warning"));
		}
	}
	
	/**
	 * 报表导出PDF文件
	 * @param unknown $params
	 */
	public function reportExportToPdf($params){
		
	}
	
	public function getDefaultVisualInfo($params){
		$visualConf = array();
		if(file_exists('/etc/vinchin/web/visualConf.ini')){
			$visualConf = json_decode(file_get_contents('/etc/vinchin/web/visualConf.ini'), true);
		}
		
		return json_encode($visualConf);
	}
	
	public function setVisualInfo($params){
		$title = $params['title'];
		$visualConf = array(
			"config" => array(
				'title' => $title
			)
		);
		$visualConf = json_encode($visualConf);
		file_put_contents('/etc/vinchin/web/visualConf.ini', $visualConf, LOCK_EX);
		return $this->muOpResult(true, Xphp::$_lang['UI_VISUAL_CONFIG_SETTING']);
	}
    
}
?>