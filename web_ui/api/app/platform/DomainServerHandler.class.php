<?php
/*******************************************
 ** 域管理类
 **
 ** @author       xiezhuowei@vinchin.com;luokai@vinchin.com;liushuai@vinchin.com
 ** @date         2020-09-10 下午17:04:00
 ** @version      1.0.0
 ** @copyright    Copyright 2020 vinchin.com
 ********************************************/
class DomainServerHandler extends OPHandler{
    
    /**
     * 获取域服务器列表
     * @param unknown $params
     */
    public function getDomainServerLists($params){
        //TODO
    }
    
    /**
     * 添加域服务器
     * @param unknown $params
     */
    public function addDomainServer($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_domain_add");
        $domain = $params['domainname'];
        $domaintype = $params['domaintype'];
        $port = $params['port'];
        $username = $params['username'];
        $password = $params['password'];
        $conpassword = base64_decode($password);
        $tenantuuid = "";
        //如果是在租户内部创建
        if(!empty($_SESSION['tenantuuid'])){
            $tenantuuid = $_SESSION['tenantuuid'];
        }
        $ip = gethostbyname($domain);
        $utils = Xphp::instance('Utils');
        //加密密码
        $password = $utils->encrype($conpassword);
        //连接AD域服务器
        $conn = ldap_connect($ip, $port);
        if ($conn) {
            //设置参数
            ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
            ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
            $bd = ldap_bind($conn, $username . "@" . $domain, $conpassword);
            if ($bd) {
                // 	            echo 'LDAP true';//相当于登录成功
                //创建domain_uuid;
                $uuid = $utils->uuid();
                $registerTime = date('Y-m-d H:i:s');
                //域服务器插入数据库
                $sql = "insert into bd_domain_server (domain_uuid, domain_name, domain_ip, domain_type, username, password, register_time, tenant_uuid, port, create_user_uuid, create_user_name)
                        values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sqlParams = array($uuid, $domain, $ip, $domaintype, $username, $password, $registerTime, $tenantuuid, $port, Xphp::$_user['useruuid'], Xphp::$_user['username']);
                $result = $this->dbExec($sql, $sqlParams);
                if($result){
                    //添加系统操作日志
                    $this->systemLog('SYSTEM_DOMAIN_ADD_SUCCESS', array($domain));
                }
                return $this->muOpResult($result, Xphp::$_lang['UI_DOMAIN_SERVER_ADD']);
            } else {
                // 	            echo 'LDAP false';
                return $this->muOpResult(false, Xphp::$_lang['UI_DOMAIN_SERVER_ADD'], Xphp::$_lang['WEB_DOMAIN_SERVER_ADD_USER_PASSWORD_ERROR'], "warning");
            }
        } else {
            //连接失败返回错误
            return $this->muOpResult(false, Xphp::$_lang['UI_DOMAIN_SERVER_ADD'], Xphp::$_lang['WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR'], "warning");
            // 	        echo '无法连接到AD域服务器';
        }
    }
    
    /**
     * 修改域服务器
     * @param unknown $params
     */
    public function editDomainServer($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_domain_edit");
        $domainuuid = $params['domainuuid'];
        $domain = $params['domainname'];
        $domaintype = $params['domaintype'];
        $port = $params['port'];
        $username = $params['username'];
        $password = $params['password'];
        $conpassword = base64_decode($password);
        $tenantuuid = "";
        //如果是在租户内部创建
        if(!empty($_SESSION['tenantuuid'])){
            $tenantuuid = $_SESSION['tenantuuid'];
        }
        $ip = gethostbyname($domain);
        $utils = Xphp::instance('Utils');
        //加密密码
        $password = $utils->encrype($conpassword);
        //连接AD域服务器
        $conn = ldap_connect($ip, $port);
        if ($conn) {
            //设置参数
            ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);//声明使用版本3
            ldap_set_option($conn, LDAP_OPT_REFERRALS, 0); // Binding to ldap server
            $bd = ldap_bind($conn, $username . "@" . $domain, $conpassword);
            if ($bd) {
                // 	            echo 'LDAP true';//相当于登录成功
                $registerTime = date('Y-m-d H:i:s');
                //域服务器更新数据库
                $sql = "update bd_domain_server set domain_name = ?, domain_ip = ?, domain_type = ?, username = ?, password = ?, register_time = ?, tenant_uuid = ?, port = ? where domain_uuid = ?";
                $sqlParams = array($domain, $ip, $domaintype, $username, $password, $registerTime, $tenantuuid, $port, $domainuuid);
                $result = $this->dbQuery($sql, $sqlParams);
                return $this->muOpResult($result, Xphp::$_lang['UI_DOMAIN_SERVER_MODIFY']);
            } else {
                // 	            echo 'LDAP false';
                return $this->muOpResult(false, Xphp::$_lang['UI_DOMAIN_SERVER_MODIFY'], Xphp::$_lang['WEB_DOMAIN_SERVER_ADD_USER_PASSWORD_ERROR'], "warning");
            }
        } else {
            //连接失败返回错误
            return $this->muOpResult(false, Xphp::$_lang['UI_DOMAIN_SERVER_MODIFY'], Xphp::$_lang['WEB_DOMAIN_SERVER_ADD_NOT_CONNECT_ERROR'], "warning");
            // 	        echo '无法连接到AD域服务器';
        }
    }
    
    /**
     * 删除域服务器
     * @param unknown $params
     */
    public function deleteDomainServer($params){
        //权限检查
        $roleHandler = Xphp::instance('RoleHandler');
        $roleHandler->pOperationPermissionCheckExit("p_safety_domain_delete");
        //TODO
        $domainuuid = $params['domainuuid'];
        //检查是否存在域用户
        $this->checkDomainUserExist($domainuuid);
        //是否删除domain域添加用户
        $sql = "delete from bd_domain_server where domain_uuid = ?";
        $result = $this->dbQuery($sql, array($domainuuid));
        
        if($result){
            return $this->muOpResult(true, Xphp::$_lang['UI_DOMAIN_SERVER_DELETE']);
        }else{
            return $this->muOpResult(false, Xphp::$_lang['UI_DOMAIN_SERVER_DELETE'], "", "warning");
        }
        
    }
    
    
    /**
     * 获取活动目录列表
     * @param unknown $params
     */
    public function getDomainSelectList($params){
//         $tenantuuid = $_SESSION['tenantuuid'];
        $sql = "select domain_uuid, domain_name from bd_domain_server where create_user_uuid = ? ";
        $sqlParams = array(Xphp::$_user['useruuid']);
//         if(!empty($tenantuuid)){
//             $sql .= " where tenant_uuid = ? ";
//             $sqlParams = array_merge($sqlParams, array($tenantuuid));
//         }
        $sql .= " order by register_time desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        if(!empty($data)){
            foreach ($data as $d){
                $info[] = array(
                    'text' => $d['domain_name'],
                    'value' => $d['domain_uuid']
                );
            }
        }
        
        return json_encode($info);
    }
    
    /**
     * 获取域服务器列表
     * @param unknown $params
     */
    public function getDomainServerInfo($params){
        $start = $params['start'];
        $length = $params['length'];
        $sortColumn = $params['sortColumn'];//排序的参数
        $sortType = $params['sortType'];//排序类型
        $sortArr = array('', 'domain_name', 'domain_type', 'domain_name', 'domain_ip', 'register_time', 'tenant_uuid', 'create_user_name');
        
        $sql = "select domain_uuid, domain_name, domain_type, domain_ip, unix_timestamp(register_time) register_time, tenant_uuid, create_user_name from bd_domain_server ";
        $sqlCount = "select count(domain_uuid) as total from bd_domain_server ";
        $sqlParams = array($start, $length);
        $sqlCountParams = array();
        //检查是否是租户管理员
        $userHandler = Xphp::instance('UsersHandler');
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
//         if(empty($_SESSION['tenantuuid']) && Xphp::$_user['username'] == "admin"){
//             $sqlParams = array($start, $length);
//             $sqlCountParams = array();
//         }else if($tenantMangerFlag){
//             $sql .= " where tenant_uuid = ? ";
//             $sqlCount .= " where tenant_uuid = ? ";
//             $sqlParams = array( $_SESSION['tenantuuid'], $start,$length);
//             $sqlCountParams = array( $_SESSION['tenantuuid']);
//         }else{
            $sql .= "where create_user_uuid = ? ";
            $sqlCount .= "where create_user_uuid = ? ";
            $sqlParams = array(Xphp::$_user['useruuid'], $start, $length);
            $sqlCountParams = array(Xphp::$_user['useruuid']);
//         }
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        //活动目录类型对应描述
        $domainDes = array(Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'], 'Active Directory', Xphp::$_lang['UI_PLATFORM_APPLE_DIR_SERVICE'], Xphp::$_lang['UI_PLATFORM_ORACLE_DIR'], 'OpenLDAP');
        $records = array();
        $records['data'] = array();
        $tenantHandler = Xphp::instance('TenantHandler');
        if(!empty($data)){
            foreach ($data as $d){
                $records['data'][] = array(
                    '<input type="checkbox" name="id[]" value="'. $d['domain_uuid'] .'">',
                    $d['domain_name'],
                    $domainDes[intval($d['domain_type'])],
                    $d['domain_name'],
                    $d['domain_ip'],
                    date('Y-m-d H:i:s', $d['register_time']),
                    $tenantHandler->getTenantNameByUUID($d['tenant_uuid']),
                    $d['create_user_name']
                );
            }
        }
        $records["draw"] = $params['draw'];;
        $records["recordsTotal"] = $count[0]['total'];
        $records["recordsFiltered"] = $count[0]['total'];
        
        return json_encode($records);
    }
    
    /**
     * 获取域服务器修改信息
     * @param unknown $params
     */
    public function getOldDomainServerInfo($params){
        $domainuuid = $params['domainuuid'];
        $sql = "select domain_name, domain_type, username, password, tenant_uuid from bd_domain_server where domain_uuid = ?";
        $data = $this->dbSelect($sql, array($domainuuid));
        $info = array();
        $utils = Xphp::instance('Utils');
        if(!empty($data)){
            $info = array(
                'domain' => $data[0]['domain_name'],
                'domaintype' => $data[0]['domain_type'],
                'username' => $data[0]['username'],
                'password' => base64_encode($utils->decrypt($data[0]['password'])),
                'tenantuuid' => $data[0]['tenant_uuid']
            );
        }
        
        return json_encode($info);
    }
    
    
    
    /**
     * 检查域是否已注册
     * @param string $params [username]
     * @return string
     */
    public function domainAvailable($params){
        $domainname = $params['domainname'];
        $sql = "select domain_uuid from bd_domain_server where domain_name = ? ";
        $sqlParams = array($domainname);
        $data = parent::dbSelect($sql, $sqlParams);
        return json_encode(empty($data));
    }
    
    /**
     * 检查是否存在域用户
     * @param string $domainuuid
     */
    private function checkDomainUserExist($domainuuid){
        $sql = "select id from bd_user where domain_uuid = ? ";
        $data = $this->dbSelect($sql, array($domainuuid));
        if(!empty($data)){
            exit($this->muOpResult(false, Xphp::$_lang['UI_DOMAIN_SERVER_DELETE'],Xphp::$_lang['UI_DOMAIN_INCLUDE_USER_DEL_FIRST'],"warning"));
        }
        
        return true;
    }
    
    
}
?>