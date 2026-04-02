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
//         var_dump($_FILES[$uploadfile['name']]);
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
                days, trial_type, software_type, user_name from bd_license ";
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
				
				$statusDes = "----";
				$expireTime = "----";
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
        $networkCardPath = Xphp::$_config['NETWORKCARD']['path'] . Xphp::$_config['NETWORKCARD']['prefix'] . $cardName;
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
        $this->paramsCheck($name, $ipaddr, $netmask);
        
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
        $sql = "select email_notice_flag, system_notice_flag, system_notice_level, task_notice_flag, task_notice_level, 
                smtp_config from bd_email_notice";
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
        
        $sql = "update $tableName set $flagCol = ?, system_notice_flag = ?, system_notice_level = ?, task_notice_flag = ?, task_notice_level = ?";
        $sqlParams = array($flag, $systemFlag, $systemLevel, $taskFlag, $taskLevel);
        
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
     * 执行自动升级
     * @param unknown $params
     */
    public function doAutoUpgrade($params){
        //TODO
        $version = $params['version'];
        sleep(5);
        return $this->muOpResult(true, Xphp::$_lang['WEB_SYSTEM_AUTO_UPDATE']);
    }
    
    /**
     * 上传升级包,手动升级
     * @param unknown $params
     */
    public function uploadUpgradeFile($params){
//         var_dump($_FILES);
        //TODO检测上传文件名字,类型, 大小莫法检测, 
        $opName = Xphp::$_lang['WEB_SYSTEM_UPLOAD_UPGRADE_PACKAGE'];
        $uploadfile = Xphp::$_config['UPLOAD_PATH'] . $_FILES['files']['name'];
        if(move_uploaded_file($_FILES['files']['tmp_name'], $uploadfile)) {
            $utils = Xphp::instance('Utils');
            $extInfo = array(
                'name' => $_FILES['files']['name'],
                'size' => $utils->calSize($_FILES['files']['size'])
            );
            return $this->muOpResult(true, $opName, '', '', 0, $extInfo);
        } else {
            return $this->muOpResult(false, $opName);
        }
        
    }
    
    /**
     * 手动升级 立即升级
     * @param unknown $params
     */
    public function doManualUpgrade($params){
        //TODO
        $pkgname = $params['pkgname'];
        sleep(5);
        return $this->muOpResult(true, Xphp::$_lang['WEB_SYSTEM_MANUAL_UPDATE']);
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
        //         var_dump($_FILES[$uploadfile['name']]);
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
	
    
}
?>