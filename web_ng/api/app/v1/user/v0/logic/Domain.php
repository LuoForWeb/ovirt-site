<?php

namespace app\v1\user\v0\logic;

use app\v1\common\logic\Base;
use app\v1\user\v0\logic\User as UserHandler;

/**
 * note          用户组管理类
 * @author       ZHENGXIANGQIN@vinchin.com
 * @date         2024/4/16 11:17
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Domain extends Base
{
    /**
     * 获取域服务器列表
     * @param array $params 参数
     * @return array
     */
    public function getDomainList($params = []): array
    {
        $start = $params['offset'];
        $length = $params['limit'];

        $sql = "select domain_uuid, domain_name, domain_type, domain_ip, unix_timestamp(register_time) register_time, tenant_uuid, create_user_name, create_user_uuid from bd_domain_server ";
        $sqlCount = "select count(domain_uuid) as total from bd_domain_server ";
        $sqlParams = array($start, $length);
        $sqlCountParams = array();
        //检查是否是租户管理员
        $userHandler = UserHandler::instance();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        $checkAuth = v1_auth_is_admin();
        if($checkAuth['global_observer']){
            $sqlNew = " where bd_domain_server.create_user_uuid not in ('a508b813-19c7-eb4e-d6fa-bb61b25a4de9')";
            $sql .= $sqlNew;
            $sqlCount .= $sqlNew;
        }else if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users('');
            $sqlNew = " where bd_domain_server.create_user_uuid in ({$userUuidSql}) ";
            $sql .= $sqlNew;
            $sqlCount .= $sqlNew;
        }
        $data = $this->dbSelect($sql);
        $count = $this->dbSelect($sqlCount);
        //活动目录类型对应描述
        $domainDes = array(xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN'), 'Active Directory', xphp_get_lang('UI_PLATFORM_APPLE_DIR_SERVICE'), xphp_get_lang('UI_PLATFORM_ORACLE_DIR'), 'OpenLDAP');
        $records = array();
        $records['rows'] = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $records['rows'][] = array(
                    'domain_uuid' => $d['domain_uuid'],
                    'domain_name' => $d['domain_name'],
                    'domain_type' =>  $domainDes[intval($d['domain_type'])],
                    'domain_ip' => $d['domain_ip'],
                    'register_time' => date('Y-m-d H:i:s', $d['register_time']),
                    'tenant_uuid' => $this->getTenantNameByUUID($d['tenant_uuid']),
                    'create_user_name' => $d['create_user_name'],
                    'create_user_uuid' => $d['create_user_uuid']
                );
            }
        }
        $records['total'] = $count[0]['total'];
        return $records;
    }

    /**
     * 新建域服务器
     * @param array $params 参数
     * @return array
     */
    public function addDomainServer($params = []): array
    {
        $protocolValue = array('',"ldaps://","ldap://");
        //权限检查
        (new Role())->pOperationPermissionCheckExit('p_safety_domain_add');
        $domain = $params['domainname'];
        $domaintype = $params['domaintype'];
        $protocol = $protocolValue[intval($params['protocol'])];
        $ip = $params['ip'];
        $port = $params['port'];
        $username = $params['username'];
        $password = $params['password'];
        $cheakFlag = $params['checkflag'];
        $tenantuuid = '';
        $details = array(
            'checkflag' => $cheakFlag,
            'protocol' => $params['protocol']
        );
        //如果是在租户内部创建
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantuuid = $_SESSION['tenantuuid'];
        }

        $hostname = gethostbyaddr($ip);
        $host = $protocol.$hostname;
        $password = v1_decrypt_js_rsa($password);
        //加密密码
        $encrype_password = v1_encrype($password);

        //扫描所有证书进行校验
        ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7); // LDAP调试
        if($cheakFlag){
            // 设置验证证书步骤
            $certDir = xphp_get_config('app','CERT_PATH');
            $files = scandir($certDir);
            $files = array_diff($files, array('..', '.'));
            $success = false;
            foreach ($files as $file){
                ldap_set_option(null, LDAP_OPT_X_TLS_CACERTFILE, $certDir.$file);
                ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_HARD);
                $conn = ldap_connect($host, $port);
                if (!$conn){
                    //连接失败返回错误
                    return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_ADD'), xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR'), 'warning');
                }
                //设置参数
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
                $bd = ldap_bind($conn, $username.'@'.$domain, $password);
                $success = $success || $bd;
            }

            //所有证书认证都不通过直接返回对应错误
            if (!$success){
                $msg = xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR').","."#" . ldap_errno($conn) .",". xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ": ".ldap_error($conn);
                return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_ADD'), $msg, 'warning');
            }
        }else{
            //设置跳过验证证书步骤
            ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
            $conn = ldap_connect($host, $port);
            if (!$conn){
                //连接失败返回错误
                return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_ADD'), xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR'), 'warning');
            }
            //设置参数
            ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
            ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
            $bd = ldap_bind($conn, $username.'@'.$domain, $password);
            if (!$bd){
                $msg = xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR').","."#" . ldap_errno($conn) .",". xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ": ".ldap_error($conn);
                return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_ADD'), $msg, 'warning');
            }
        }

        //创建domain_uuid;
        $uuid = xphp_uuid();
        $registerTime = date('Y-m-d H:i:s');
        //域服务器插入数据库
        $sql = "insert into bd_domain_server (domain_uuid, domain_name, domain_ip, domain_type, username, password, register_time, tenant_uuid, port, create_user_uuid, create_user_name, detail)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array($uuid, $domain, $ip, $domaintype, $username, $encrype_password, $registerTime, $tenantuuid, $port, xphp_get_user_info()['userUuid'], xphp_get_user_info()['userName'], json_encode($details));
        $result = $this->dbExec($sql, $sqlParams);
        if ($result) {
            //添加系统操作日志
            $this->systemLog('SYSTEM_DOMAIN_ADD_SUCCESS', array($domain));
        }
        return $this->muOpResult($result, xphp_get_lang('UI_DOMAIN_SERVER_ADD'));
    }

    /**
     * 修改域服务器
     * @param unknown $params
     */
    public function editDomainServer($params)
    {
        $protocolValue = array('',"ldaps://","ldap://");
        //权限检查
        (new Role())->pOperationPermissionCheckExit('p_safety_domain_edit');
        $domainuuid = $params['domainuuid'];
        $domain = $params['domainname'];
        $domaintype = $params['domaintype'];
        $protocol = $protocolValue[intval($params['protocol'])];
        $ip = $params['ip'];
        $port = $params['port'];
        $username = $params['username'];
        $password = $params['password'];
        $cheakFlag = $params['checkflag'];
        $conpassword = base64_decode($password);
        $tenantuuid = '';
        $details = array(
            'checkflag' => $cheakFlag,
            'protocol' => $params['protocol']
        );
        //如果是在租户内部创建
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantuuid = $_SESSION['tenantuuid'];
        }

        $hostname = gethostbyaddr($ip);
        $host = $protocol.$hostname;
        //加密密码
        $password = v1_encrype($conpassword);
        ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 7); // LDAP调试
        if($cheakFlag){
            // 设置验证证书步骤
            $certDir = xphp_get_config('app','CERT_PATH');
            $files = scandir($certDir);
            $files = array_diff($files, array('..', '.'));
            $success = false;
            foreach ($files as $file){
                ldap_set_option(null, LDAP_OPT_X_TLS_CACERTFILE, $certDir.$file);
                ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_HARD);
                $conn = ldap_connect($host, $port);
                if (!$conn){
                    //连接失败返回错误
                    return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_ADD'), xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR'), 'warning');
                }
                //设置参数
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
                $bd = ldap_bind($conn, $username.'@'.$domain, $conpassword);
                $success = $success || $bd;
            }

            //所有证书认证都不通过直接返回对应错误
            if (!$success){
                $msg = xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR').","."#" . ldap_errno($conn) .",". xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ": ".ldap_error($conn);
                return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_ADD'), $msg, 'warning');
            }
        }else{
            //设置跳过验证证书步骤
            ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
            $conn = ldap_connect($host, $port);
            if (!$conn){
                //连接失败返回错误
                return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_MODIFY'), xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR'), 'warning');
            }
            //设置参数
            ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
            ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
            $bd = ldap_bind($conn, $username.'@'.$domain, $conpassword);
            if (!$bd){
                $msg = xphp_get_lang('WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR').","."#" . ldap_errno($conn) .",". xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ": ".ldap_error($conn);
                return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_MODIFY'), $msg, 'warning');
            }
        }
        $registerTime = date('Y-m-d H:i:s');
        //域服务器更新数据库
        $sql = "update bd_domain_server set domain_name = ?, domain_ip = ?, domain_type = ?, username = ?, password = ?, register_time = ?, tenant_uuid = ?, port = ?, detail = ? where domain_uuid = ?";
        $sqlParams = array($domain, $ip, $domaintype, $username, $password, $registerTime, $tenantuuid, $port, json_encode($details), $domainuuid);
        $result = $this->dbQuery($sql, $sqlParams);
        return $this->muOpResult($result, xphp_get_lang('UI_DOMAIN_SERVER_MODIFY'));
    }

    /**
     * 获取租户名字
     * @param string $tenantuuid
     */
    public function getTenantNameByUUID($tenantuuid)
    {
        $sql = "select tenant_name from bd_tenant where tenant_uuid = ? ";
        $data = $this->dbSelect($sql, array($tenantuuid));
        $tenantName = '';
        if (!empty($data)) {
            $tenantName = $data[0]['tenant_name'];
        }

        return $tenantName;
    }

    /**
     * 删除域服务器
     * @param unknown $params
     */
    public function deleteDomainServer($params)
    {
        //权限检查
        (new Role())->pOperationPermissionCheckExit("p_safety_domain_delete");
        //TODO
        $domainuuid = $params['domainuuid'];
        //检查是否存在域用户
        foreach ($params['domainuuid'] as $d) {
            $this->checkDomainUserExist($d);
        }
        //是否删除domain域添加用户
        $domainStr = implode("','", $params['domainuuid']);
        $sql = "delete from bd_domain_server where domain_uuid in ('" . "$domainStr" . "')";
        $result = $this->dbQuery($sql);

        if ($result) {
            return $this->muOpResult(true, xphp_get_lang('UI_DOMAIN_SERVER_DELETE'));
        } else {
            return $this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_DELETE'), '', 'warning');
        }
    }

    /**
     * 检查是否存在域用户
     * @param string $domainuuid
     */
    private function checkDomainUserExist($domainuuid)
    {
        $sql = "select id from bd_user where domain_uuid = ? ";
        $data = $this->dbSelect($sql, array($domainuuid));
        if (!empty($data)) {
            exit($this->muOpResult(false, xphp_get_lang('UI_DOMAIN_SERVER_DELETE'), xphp_get_lang('UI_DOMAIN_INCLUDE_USER_DEL_FIRST'), 'warning'));
        }

        return true;
    }

    /**
     * 获取活动目录列表
     * @param unknown $params
     */
    public function getDomainSelectList($params)
    {
        $sql = "select domain_uuid, domain_name from bd_domain_server where create_user_uuid = ? ";
        $sqlParams = array(xphp_get_user_info()['userUuid']);
        $sql .= " order by register_time desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $info[] = array(
                    'text' => $d['domain_name'],
                    'value' => $d['domain_uuid']
                );
            }
        }
        return $info;
    }

    /**
     * 获取域服务器修改信息
     * @param array $params 参数
     */
    public function getOldDomainServerInfo($params)
    {
        $domainuuid = $params['domainuuid'];
        $sql = "select 
                    domain_name, domain_type, domain_ip, username, password, tenant_uuid, port, domain_ip, detail
                from 
                    bd_domain_server 
                where 
                    domain_uuid = ?";
        $data = $this->dbSelect($sql, array($domainuuid));
        $info = array();
        if (!empty($data)) {
            $details = json_decode($data[0]['detail'], true);
            $info = array(
                'domain' => $data[0]['domain_name'],
                'domaintype' => $data[0]['domain_type'],
                'username' => $data[0]['username'],
                'password' => base64_encode(v1_decrypt($data[0]['password'])),
                'tenantuuid' => $data[0]['tenant_uuid'],
                'ip' => $data[0]['domain_ip'],
                'port' => $data[0]['port'],
                'protocol' => $details['protocol'],
                'checkflag' => $details['checkflag']
            );
        }
        return $info;
    }
}
