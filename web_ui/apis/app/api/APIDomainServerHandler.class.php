<?php
/******************************************* 
** 域服务器处理类
** 
** @author       luokai@vinchin.com 
** @date         2022-04-14 
** @version      1.0.0 
** @copyright    Copyright 2022 vinchin.com 
********************************************/
class APIDomainServerHandler extends OPHandler{
    
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/domainserver" => array(
            'POST' => 'createDomainServer',     //创建域服务器(单)
            'PUT' => 'editDomainServer',        //修改域服务器(单)
            'DELETE' => 'deleteDomainServer',   //删除域服务器(1-n)
            'GET' => 'getDomainServerInfo'      //获取指定域服务器详细信息(单)
        ),
        
        "/domainserver/lists" => array(
            'GET' => 'getDomainServerLists'     //获取域服务器列表(1-n)
        )
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 创建域服务器路由控制
     */
    protected function createDomainServer(){
        //定义方法版本
        $version = array(
            "v1" => "createDomainServerV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 修改域服务器路由控制
     */
    protected function editDomainServer(){
        //定义方法版本
        $version = array(
            "v1" => "editDomainServerV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 删除域服务器路由控制
     */
    protected function deleteDomainServer(){
        //定义方法版本
        $version = array(
            "v1" => "deleteDomainServerV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取域服务器详细信息(单个)路由控制
     */
    protected function getDomainServerInfo(){
        //定义方法版本
        $version = array(
            "v1" => "getDomainServerInfoV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**
     * 获取域服务器列表(多个)路由控制
     */
    protected function getDomainServerLists(){
        //定义方法版本
        $version = array(
            "v1" => "getDomainServerListsV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**********createDomainServer**********/
    private function createDomainServerV1(){
        //TODO
        $domain = $this->params['domain_name']; //域服务器名
        $domaintype = 1;    //目前只支持活动目录         //域服务器类型
        $port = $this->params['port'];         //域服务器连接端口
        $username = $this->params['user_name'];//域服务器连接账户名
        $conpassword = $this->params['password']; //用于连接域服务器账户密码
        //检查参数是否为空
        $this->apiParamsCheck($domain, $port, $username, $conpassword);
        $tenantuuid = $this->params['tenantuuid'];
        //如果是租户内部创建
        if(empty($tenantuuid)){
            $tenantuuid = Xphp::$_user['tenantuuid'];
        }
        $createUseruuid = Xphp::$_user['useruuid'];
        $createUsername = Xphp::$_user['username'];
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
                $sqlParams = array($uuid, $domain, $ip, $domaintype, $username, $password, $registerTime, $tenantuuid, $port, $createUseruuid, $createUsername);
                $result = $this->dbExec($sql, $sqlParams);
                if($result){
                    //添加成功
                    $data = array(
                        'domain_uuid' => $uuid
                    );
                    return $this->apiResponse(true, "API_CODE_DOMAIN_SERVER_ADD", $data, array());
                }else{
                    //添加失败
                    return $this->apiResponse(false, "API_CODE_DOMAIN_SERVER_ADD");
                }
            } else {
                // 	            echo 'LDAP false';
                return $this->apiResponse(false, "API_CODE_DOMAIN_SERVER_USER_PASSWORD_ERROR");
            }
        } else {
            //连接失败返回错误
            return $this->apiResponse(false, "API_CODE_DOMAIN_SERVER_NOT_CONNECT_ERROR");
            // 	        echo '无法连接到AD域服务器';
        }
    }
    
    /**********editDomainServer**********/
    private function editDomainServerV1(){
        //TODO、
        $domainuuid = $this->params['domain_uuid']; //域服务器名
        $domain = $this->params['domain_name']; //域服务器名
        $domaintype = 1;    //目前只支持活动目录         //域服务器类型
        $port = $this->params['port'];         //域服务器连接端口
        $username = $this->params['user_name'];//域服务器连接账户名
        $conpassword = $this->params['password']; //用于连接域服务器账户密码
        //检查参数是否为空
        $this->apiParamsCheck($domainuuid, $domain, $port, $username, $conpassword);
        $tenantuuid = Xphp::$_user['tenantuuid'];//租户唯一标识
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
                $result = $this->dbExec($sql, $sqlParams);
                return $this->apiResponse(true, "API_CODE_DOMAIN_SERVER_EDIT");
            } else {
                // 	            echo 'LDAP false';
                return $this->apiResponse(false, "API_CODE_DOMAIN_SERVER_USER_PASSWORD_ERROR");
            }
        } else {
            //连接失败返回错误
            return $this->apiResponse(false, "API_CODE_DOMAIN_SERVER_NOT_CONNECT_ERROR");
            // 	        echo '无法连接到AD域服务器';
        }
    }
    
    /**********deleteDomainServer**********/
    private function deleteDomainServerV1(){
        //TODO
        $domainuuid = $this->params['domain_uuid'];
        //检查参数是否为空
        $this->apiParamsCheck($domainuuid);
        //检查是否存在域用户
        $this->checkDomainUserExist($domainuuid);
        //是否删除domain域添加用户
        $sql = "delete from bd_domain_server where domain_uuid = ? ";
        $result = $this->dbExec($sql, array($domainuuid));
        
        return $this->apiResponse($result, "API_CODE_DOMAIN_SERVER_DELETE");
    }
    
    /**********getDomainServerInfo**********/
    private function getDomainServerInfoV1(){
        //TODO
        $domainuuid = $this->params['domain_uuid'];
        //检查参数是否为空
        $this->apiParamsCheck($domainuuid);
        $sql = "select domain_name, domain_ip, domain_type, username, password, tenant_uuid, unix_timestamp(register_time) register_time, create_user_uuid, create_user_name from bd_domain_server where domain_uuid = ?";
        $data = $this->dbSelect($sql, array($domainuuid));
        $info = array();
        $utils = Xphp::instance('Utils');
        if(!empty($data)){
            $info = array(
                'domain_uuid' => $domainuuid,
                'domain_name' => $data[0]['domain_name'],
                'domain_ip' => $data[0]['domain_ip'],
                'domain_type' => intval($data[0]['domain_type']),
                'user_name' => $data[0]['username'],
                'password' => base64_encode($utils->decrypt($data[0]['password'])),
                'tenant_uuid' => $data[0]['tenant_uuid'],
                'create_user_uuid' => $data[0]['create_user_uuid'],
                'create_user_name' => $data[0]['create_user_name'],
                'register_time' => intval($data[0]['register_time']),
            );
        }
        
        return $this->apiResponse(true, "API_CODE_DOMAIN_SERVER_GET_INFO", $info);
    }
    
    /**********getDomainServerLists**********/
    private function getDomainServerListsV1(){
        //TODO
        $begin = intval($this->params['begin']);
        $count = intval($this->params['count']);
        $this->apiParamsCheck($count);
        
        $sql = "select domain_uuid, domain_name, domain_type, domain_ip, unix_timestamp(register_time) register_time, tenant_uuid, create_user_uuid, create_user_name from bd_domain_server ";
        $sqlCount = "select count(domain_uuid) as total from bd_domain_server ";
        $sql .= " limit ? , ? ";
        $sqlParams = array($begin, $count);
        $sqlCountParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        //活动目录类型对应描述
        $domainDes = array(Xphp::$_lang['WEB_PLATFORM_PUBLIC_UNKNOWN'], 'Active Directory', Xphp::$_lang['UI_PLATFORM_APPLE_DIR_SERVICE'], Xphp::$_lang['UI_PLATFORM_ORACLE_DIR'], 'OpenLDAP');
        $records = array();
        if(!empty($data)){
            foreach ($data as $d){
                $records[] = array(
                    'domain_uuid' => $d['domain_uuid'],
                    'domain_name' => $d['domain_name'],
                    'domain_type' => intval($d['domain_type']),
                    'domain_ip' => $d['domain_ip'],
                    'register_time' => intval($d['register_time']),
                    'tenant_uuid' => $d['tenant_uuid'],
                    'create_user_uuid' => $d['create_user_uuid'],
                    'create_user_name' => $d['create_user_name']
                );
            }
        }
        $data = array(
            "total" => intval($dataCount[0]['total']),
            "begin" => $begin,
            "count" => $count,
            "records" => $records
        );
        
        return $this->apiResponse(true, "API_CODE_DOMAIN_SERVER_GET_LIST", $data);
    }
    
    
    
    /**********************************其他工具方法************************************/
    /**
     * 检查是否存在域用户
     * @param string $domainuuid
     */
    private function checkDomainUserExist($domainuuid){
        $sql = "select id from bd_user where domain_uuid = ? ";
        $data = $this->dbSelect($sql, array($domainuuid));
        if(!empty($data)){
            
            exit($this->apiResponse(false,"API_CODE_DOMAIN_SERVER_INCLUDE_USER_DEL_FIRST"));
        }
        
        return true;
    }
}