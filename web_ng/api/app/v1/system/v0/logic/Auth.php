<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\system\v0\logic\Index;
use app\v1\user\v0\logic\Role;
use app\v1\opcode\PfOpcode;
use app\v1\system\v0\logic\Upgrade;
use app\v1\system\v0\logic\Time;
use app\v1\db\v0\logic\DbCdp;
use app\v1\db\v0\logic\DbRpc;
use app\v1\virus\v0\logic\Virus;

class Auth extends Base{
    //获取系统授权基本信息
    public function getSystemLisenceInfo(){
        //返回数据
          $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('UI_SETTINGS_AUTH_INFO'),
            'data' => array(),
        );
        $systemStatus = (new Index())->getSystemAuthorizationStatus();
        $systemStatusDes = (new Index())->getSystemAuthorizationDes($systemStatus);
        $authInfo =   (new Index())->getAuthorizedInfoV2(intval($systemStatus), $systemStatusDes);
        $resultInfo['data'] = json_decode($authInfo,true);
        return $resultInfo;   
    }
    // 获取指纹信息（权限验证）
    public function getThumbprint(){
        (new Role())->pOperationPermissionCheckExit("p_authorization_module_download");
        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, "", true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg']['thumbprint'];
        if($result){
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($msg));
            Header("Content-Disposition: attachment; filename=".  xphp_get_config('auth', 'LISENCE_INFO')['thumbprintFileName']);
            // return $msg;
              //返回数据
            $resultInfo = array(
                'success' => true,
                'code' => 0,
                'message' => xphp_get_lang('UI_SETTINGS_AUTH_INFO'),
                'data' => $msg
            );
            return $resultInfo;
        }else{
            $pfOpcode = new PfOpcode();
            $operate = $pfOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    //获取指纹信息文件
    public function getThumbprintFile(){
        (new Role())->pOperationPermissionCheckExit("p_authorization_module_download");
        $msg = "";
        //深信服oem版本
		if(xphp_get_config('app', 'SYSTEM_INFO')['enterprise'] == "sangfor_enterprise"){
		    //获取SNcode
		    $snCode = $this->getMachinSNCode();
		    if($snCode != "None"){
		        //如果不是EDS,就不获取机器指纹
		        $msg .= "SN:". $snCode . PHP_EOL;
		    }
		}

        $opName = 'PT_LICENSE_OP_QUERY_THUMBPRINT';
        $mbResult = $this->mbPFMsg($opName, "", true);
        $result = $mbResult['result'];
        $msg .= $mbResult['msg']['thumbprint'];

        //添加数据库实时,先判断是否有
        $cmdStr = "ps aux|grep lzbackupsys";
        exec($cmdStr, $info);
        if(count($info) > 2){
            $msg .= PHP_EOL . (new DbCdp())->getEnvStr();
        }
        //添加数据库定时,先判断是否有
        $msg .= "\r\n" . date("Y-m-d H:i:s", (new Time())->getSystemTime()); 
        $msg .= $this->getPlatformInfo();
        $msg .= "\r\n" . xphp_get_config('app', 'SYSTEM_INFO')['enterprise']  . $this->getThumbprintEnterprise() . " " . xphp_get_config('app', 'SYSTEM_INFO')['version'];
        if(count($info) > 2){
            $$database_msg = "";
            $database_msg .= " " . "t2-";
            $hostip = $_SERVER['SERVER_ADDR'];
            //是否获取cdp授权信息
            $result = (new DbRpc())->getLicenseCenterAuthorInfo($hostip, array());
            if($result['result']){
                $cdpInfo =  (new Index())->getDBcdpLicenseInfo();
                $database_msg  .= $cdpInfo['endtime'];
            }
            $msg .= "\r\n". base64_encode($database_msg);
        }
        $msg .= "\r\n" . xphp_get_config('app', 'SYSTEM_INFO')['company'];
        //添加定制版本信息
        $sql = "select settings_content from bd_system_settings where settings_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'SETTINGS_CONF')['CUSTOM_VERSION']));
        if(!empty($data)){
            $version = $data[0]['settings_content'];
            $msg .= "\r\n" .  xphp_get_config('app', 'CUSTOM_VERSION')[$version];
        }
        if($result){
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Accept-Length: " . strlen($msg)); 
            Header("Content-Disposition: attachment; filename=".  xphp_get_config('auth', 'LISENCE_INFO')['thumbprintFileName']);
            echo $msg;
        }else{
            $pfOpcode = new PfOpcode();
            $operate = $pfOpcode->getOpcodeDes($opName);
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    //通过接口的方式获取SN号
    public function getMachinSNCode(){
        $mbResult = $this->service()->getMachinSNCode();
        $result = $mbResult['result'];
        if(!$result){
            return "None";
        }
        return $mbResult['msg']['sn_code'];
        
    }

        /**
     * 得到平台信息，现在有的平台
     * x86 centos7
     * arm centos7
     * arm kylin10
     */
    private function getPlatformInfo(){
        //因为目前x86就只有一个版本centos7，所以暂时x86返回信息为空，只在arm平台的时候返回对应的平台信息
        $platformInfo = "";
        
        if("arm" ==xphp_get_config('app', 'SYSTEM_INFO')['arch_type']){   
            $platformInfo = "\r\n" . xphp_get_config('app', 'SYSTEM_INFO')['arch_type'] . "_" . xphp_get_config('app', 'SYSTEM_INFO')['os_type'];
        }
        return $platformInfo;
    }

    /**
     * 得到软件版本信息
     */
    private function getThumbprintEnterprise(){
        $des = "";
        $softType = (new Index())->getSoftwareType();
        if(xphp_get_config('app', 'SOFTWARE_VERSION')['FREE_EDITION'] == $softType){
            //如果是免费版本
            $softType = " free edition ";
        }
        return $des;
    }

    //上传授权文件 
    public function uploadLicense(){
        (new Role())->pOperationPermissionCheckExit("p_authorization_module_upload");
        $this->checkUploadFile();
        $key = file_get_contents($_FILES['files']['tmp_name']);
        return (new Index())->submitLisenceKey($key);
    }
    /**
     * 检测上传文件
     */
    private function checkUploadFile(){
        $uploadfile = xphp_get_config('auth', 'LISENCE_INFO')['uploadfile'];
        $files = $_FILES[$uploadfile['name']];
        if($files){
            $this->checkUploadStatus($files['error']);
            $this->checkFileName($files['name'], $uploadfile);
            $this->checkFIleSize($files['size'], $uploadfile);
            $this->checkUploadType($files['type'], $uploadfile);
            return true;
        }
        exit($this->muOpResult(false, xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE')));
    }

    /**
     * 检测文件上传状态
     * @param int $error    文件上传相关的错误代码
     */
    private function checkUploadStatus($error){
        if(0 == $error){
            return true;
        }
        exit($this->muOpResult(false, xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE')));
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
        exit($this->muOpResult(false, xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE'), xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE_TYPE_ERROR'), 'error'));
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
        exit($this->muOpResult(false, xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE'), xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE_SIZE_ERROR'), 'error'));
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
        exit($this->muOpResult(false,  xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE'),  xphp_get_lang('WEB_SYSTEM_UPLOAD_FILE_ERROR'), 'error'));
    }

    // 获取授权信息new 
    public function getSystemAuthInfo(){
        //返回数据
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' => xphp_get_lang('UI_SETTINGS_AUTH_INFO'),
            'data' => '',
        );
        $IndexHandler = new Index();
        $systemStatus = $IndexHandler->getSystemAuthorizationStatus();
        //未授权
        if($systemStatus == xphp_get_config('auth', 'LISENCE_INFO')['authflag']['unauthorized']){
            $resultInfo['data'] = array(
                'system_info'=>array(
                    'status' => $systemStatus
                )
            );
            return $resultInfo;
        }
        $sql = "SELECT * FROM bd_license";
        $data = dbSelect($sql);
        $auth = $data[0];
        $info = array();
        $info['first_type'] = $auth['root_type']; //第一大类  1 2 3
        $info['second_type'] = $auth['client_type']; //第二大类 1 2 3 4
        //获取主控服务器及备份节点信息
        $info['master_info'] = array(
            'master_num' => 1,  //主节点默认1个
            'node_num' => intval($auth['node_count']),  //节点个数
        );
        $info['module_info'] = v1_license_get_auth();
        //获取数据库实时信息
        //授权模块以及自定义版本信息
        $extension = [];
        if (json_decode(v1_decrypt($auth['extension']), true) != null) {
            $extension = json_decode(v1_decrypt($auth['extension']), true);
        }
        $extension['p'] =  $extension['p'] ?? [];
        $extension['db'] =  $extension['db'] ?? [];
        $backup_show_flag = array(
            'client' => $auth['root_type'] == 3 && ($auth['client_type'] == 4||$auth['client_type'] == 2) ? true : false,
            'vm' => in_array('vmprotect', $extension['p'])? true : false,
            'private_cloud' => in_array('prcloud_protect', $extension['p'])? true : false,
            'cloud' => in_array('awsprotect', $extension['p'])? true : false,
            'machine_os' => in_array('complete_machine', $extension['p'])? true : false,
            'os' => in_array('osbackup', $extension['p'])? true : false,
            'file' => in_array('fileprotect', $extension['p'])? true : false,
            'oracle' => in_array('db_protect', $extension['p'])? true : false,
            'nas' => in_array('nas_protect', $extension['p'])? true : false,
            'hadoop' => in_array('hadoop_protect', $extension['p'])? true : false,
            'obs' => in_array('obs_protect', $extension['p'])? true : false,
            //exchange server 和 exchange online 如果是第一大类和第二大类授权 就判断extension p; 如果不是第一大类和第二大类 除了判断extension p 还需要判断数量是否为0 不为0 就显示
            'exchange' => (in_array('office365_protect', $extension['p']) && ((($auth['root_type'] == 3 || $auth['root_type'] == 4) && $info['module_info']['exchange']['total'] == 0 ) ? false : true))? true : false,
            'exchange_online' => (in_array('office365_protect', $extension['p']) && ((($auth['root_type'] == 3 || $auth['root_type'] == 4) && $info['module_info']['exchange_online']['total'] == 0 ) ? false : true))? true : false,
            'k8s' => in_array('k8s_protect', $extension['p'])? true : false,
            'oracle_hana' => in_array(xphp_get_config('db', 'DB_TYPE')['SAPHANA'],$extension['db'])? true : false, //先默认设置成true
        );
        $copy_show_flag = array(
            'copy_machine' => in_array('machine_copy', $extension['p'])? true : false, //整机复制 
            'copy_os' => in_array('vol_cdp_copy', $extension['p'])? true : false, //卷复制
            'copy_db' => in_array('dbcdpcopy', $extension['p'])? true : false, //数据库复制
            'copy_fs' => in_array('file_copy_protect', $extension['p'])? true : false, // 本地文件复制
            'copy_nas' => in_array('file_copy_protect', $extension['p'])? true : false, //nas复制
            // 'copy_hadoop' => in_array('file_copy_protect', $extension['p'])? true : false , //hadoop复制
            // 'copy_obs' => in_array('file_copy_protect', $extension['p'])? true : false , //obs复制
            //605版本不支持hadoop复制和对象存储复制，因此屏蔽
            'copy_hadoop' => false , //hadoop复制
            'copy_obs' => false , //obs复制
        );
        $cdp_show_flag = array(
            'cdp_machine' => in_array('complete_cdp_backup', $extension['p'])? true : false, //整机实时
            'cdp_vol' => in_array('vol_cdp_backup', $extension['p'])? true : false, //卷实时
            'third_db_cdp' => in_array('dbprotect', $extension['p'])? true : false, //数据库实时
        );
        $advance_show_flag = array(
            'verify' => in_array('data_verification', $extension['p'])? true : false,
            'embed' => false, //内嵌 默认任何授权方式都不显示
            'v2v' => $this->getCrossAuthFlag($extension), //跨平台
            'emergency_takeover'=> in_array('cdp_takeover', $extension['p'])? true : false,
            
        );
        $info['backup_show_flag'] = $backup_show_flag;
        $info['copy_show_flag'] = $copy_show_flag;
        $info['cdp_show_flag'] = $cdp_show_flag;
        $info['advance_show_flag'] = $advance_show_flag;    
        //获取授权系统信息
        $info['system_info'] = $this->getSystemBasicInfo();
        //如果604升级到605 不存在备份资源 那么就显示授权异常 因为605都会授权备份节点
        //写入假数据
        if(!in_array('backup_manager', $extension['p'])){   
            $info['system_info']['status'] =  4 ;//授权异常
            $info['system_info']['statusDes'] = $IndexHandler->getSystemAuthorizationDes(4);
        }
        $info['extension']  = $extension;
        $info['server_info'] = $IndexHandler->getAuthServerInfo($extension);  //授权服务信息
        //获取当前版本信息
        $info['version_info'] = xphp_get_config('app', 'SYSTEM_INFO')['enterprise']. " " . xphp_get_config('app', 'SYSTEM_INFO')['version'];  //当前版本信息
        //文件系列容量授权
        if($auth['client_type'] == 3 || $auth['client_type'] == 4){
            $used = v1_license_get_files_capacity_used(['file', 'nas', 'hadoop', 'obs']);
            $avail = $auth['client_capacity_max'] - $used;
            $file_capacity = array(
                'total' => $auth['client_capacity_max'],
                'used' => $used,
                'avail' => $avail,
                'total_des' => v1_calsize($auth['client_capacity_max'],true),
                'used_des' => v1_calsize($used,true),
                'avail_des' => v1_calsize($avail,true)
            );
            $info['file_capacity'] = $file_capacity;
        }
        //CDP 容量授权
        if($auth['root_type'] == 3 && $info['module_info']['cdp']['auth_type'] == 2){
            $cdp_capacity_used = $info['module_info']['cdp']['used'];
            $cdp_capacity_total = $info['module_info']['cdp']['total'];
            $cdp_capacity_avail = $info['module_info']['cdp']['total'] - $info['module_info']['cdp']['used'];
            $used = $cdp_capacity_used;
            $total = $cdp_capacity_total;
            $avail =  $cdp_capacity_avail;
            $info['all_storage_capacity'] = array(
                'total' => $total,
                'used' => $used,
                'avail' => $avail,
                'total_des' => v1_calsize($total,true),
                'used_des' => v1_calsize($used,true),
                'avail_des' => v1_calsize($avail,true)
            );
        };
        if($auth['root_type'] == 2){
            //如果是定时和实时分别授权，则需要把两个授权信息加起来
            $used = $info['module_info']['storage']['used'] + $info['module_info']['vol_capacity']['used'];
            $total = $info['module_info']['storage']['total']  + $info['module_info']['vol_capacity']['total'];
            $avail = $total - $used;
            $info['all_storage_capacity'] = array(
                'total' => $total,
                'used' => $used,
                'avail' => $avail,
                'total_des' => v1_calsize($total,true),
                'used_des' => v1_calsize($used,true),
                'avail_des' => v1_calsize($avail,true)
            );
        }
        //获取所有功能授权信息
        $info['function_info'] = $extension['f'] ?? [];
        $info['function_info']['embed'] = true; //加入内嵌
        $info['function_info']['tenant'] = in_array('tenant_manager', $extension['p'])? true : false; //加入多租户
        //info里面还需要加入病毒库 用于授权页面显示
        $allVirus = (new Virus()) -> getVirus(array());
        $virusName =  array_map(function ($item) {
            return $item['vendor'];
        },$allVirus['rows']);
        $info['virus_name']  = $virusName;
        $resultInfo['data'] = $info;
        return $resultInfo;
    }

    // 获取系统授权基本信息
    public function getSystemBasicInfo() {
        $IndexHandler = new Index();
        $status = $IndexHandler->getSystemAuthorizationStatus();
        $statusDes = $IndexHandler->getSystemAuthorizationDes($status);
        $sql = "select unix_timestamp(register_time) register_time, 
                days, trial_type, software_type, user_name, extension from bd_license";
        $data = $this->dbSelect($sql);
        //获取授权状态描述和超时时间
        if(empty($data[0])){
            $expireTime = "";
            $expireDays = 0;
        }else{
            $statusInfo = $IndexHandler->getSystemStatusInfo($data[0], $status, $statusDes); 
            $expireTime = $statusInfo['expireTime'];
            $expireDays = $statusInfo['expireDays'];
        }
        $info = array(
            'status' => $status,
            'statusDes' => $statusDes, //授权状态描述
            'expireTime'=> $expireTime, //授权到期时间
            'expireDays' => $expireDays, //授权剩余天数
            'trial' => intval($data[0]['trial_type']), //1试用 2永久
            'customer' => empty($data[0]['user_name']) ? "----" : $data[0]['user_name'], //用户名称
            'software' => empty($data) ? $IndexHandler->getSoftwareType() : intval($data[0]['software_type']), //软件版本
        );
        return $info;
    
    
    }         

    /**
     * 获取授权的一些基本信息
     * @param array $param 请求参数
     * @return array
     */
    public function getLicenceInfo(array $param): array
    {
        if ($param['type'] == 'a') {
            return v1_license_get_auth($param['module'] ?? '', $param['uuids'], $param['task_uuid']);
        } elseif ($param['type'] == 'v') {
            // 获取支持的虚拟化类型
            return v1_license_get_v($param['source'] ?? 0);
        } elseif ($param['type'] == 'v2v') {
            // 根据传递的虚拟化类型，判断可以跨平台、瞬时恢复和迁移的虚拟化类型
            return v1_license_get_v2v(explode(',', $param['array'] ?? ''));
        }
        return v1_license_get_func($param['type']);
    }
    /**
     * 判断跨平台恢复是否授权
     * @param array $param 请求参数
     * @return bloolean
     */
    public function getCrossAuthFlag(array $extension){
        $authflag = false;
        $sql = "select desktop_max from bd_license ";
        $data = $this->dbSelect($sql);
        $desktop_max = $data[0]['desktop_max'];
        //判断extension['p']里面有没有 虚拟机跨平台恢复--私有云跨平台恢复--公有云跨平台恢复
        $crassflag =  false;
        if(in_array('vm_platform_recovery', $extension['p']) || in_array('vm_prcloud_platform_recovery', $extension['p']) || in_array('vm_awsprotect_platform_recovery', $extension['p'])){
            $crassflag = true;
        }
        if ($crassflag && ($desktop_max > 0||$desktop_max == -1)) {
            $authflag = true;
        }
        return $authflag;
    }
}
