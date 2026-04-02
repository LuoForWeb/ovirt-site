<?php
/******************************************* 
** Access_Token处理类
** 
** @author       xiezhuowei@vinchin.com 
** @date         2017-12-14 
** @version      1.0.0 
** @copyright    Copyright 2018 vinchin.com 
********************************************/
class APIAccessTokenHandler extends OPHandler{
    /**
     * 内部路由表
     */
    protected $routeTable = array(
        "/access_token" => array(
            'GET' => 'getAccessToken'
        )
    );
    
    //版本
    protected $version;
    
    //参数
    protected $params;
    
    /**
     * 获取Access_Token路由控制
     */
    protected function getAccessToken(){
        //定义方法版本
        $version = array(
            "v1" => "getAccessTokenV1"
        );
        //检查版本
        $this->checkVersion($version, $this->version);
        return call_user_func(array($this, $version[$this->version]));
    }
    
    /**********getAccessToken**********/
    private function getAccessTokenV1(){
    	//读取语言包
    	Xphp::$_lang  = require_once LANG_PATH . Xphp::$_config['lang'] . Xphp::$_config['ext'];
        $user_name = $this->params['user_name'];
        $password = $this->params['password'];
        $tenantname = $this->params['tenant_name'];
        $this->apiParamsCheck($user_name, $password);

        $sql = "select bu.user_uuid, bu.user_type, bu.language, bt.tenant_uuid from bd_user bu left join mt_user_tenant mut on bu.user_uuid = mut.user_uuid left join bd_tenant bt on mut.tenant_uuid = bt.tenant_uuid where user_name = ? and password = ? ";
        $sqlParams = array($user_name, md5($password));
        if(!empty($tenantname)){
            //租户内
            $sql .= " and bt.tenant_name = ? ";
            $sqlParams = array_merge($sqlParams, array($tenantname));
        }else{
            //租户外
            $sql .= " and bt.tenant_uuid is null ";
        }
        $data = $this->dbSelect($sql,$sqlParams);
        if(empty($data)){
            return $this->apiResponse(false, 'API_CODE_ACCESS_TOKEN_LOGIN_ERROR');
        }
        $loginTime = time();
        $outTime = $loginTime + Xphp::$_config['API_CONFIG']['timeout'];
        $accessTokenArr = array(
            'user_uuid' => $data[0]['user_uuid'],
            'user_name' => $user_name,
            'user_type' => $data[0]['user_type'],
            'language' => $data[0]['language'],
            'login_time' => $loginTime,
            'out_time' => $outTime,
        );
        //租户内
        if(!empty($tenantname)){
            $accessTokenArr['tenant_uuid'] = $data[0]['tenant_uuid'];
            $accessTokenArr['tenant_name'] = $tenantname;
        }
        $accessToken = Xphp::instance('Utils', 'encrype', json_encode($accessTokenArr));
        $data = array(
            'access_token' => $accessToken,
            'expires_in' => Xphp::$_config['API_CONFIG']['timeout']
        );
        return $this->apiResponse(true, '', $data);
    }
    
    
    /*************************************非直接调用接口,如工具类等↓*******************************************/
    
    /**
     * 检查Access token
     * @param string $accessToken
     */
    public function checkAccessToken($accessTokenIn){
        $accessToken = Xphp::instance('Utils', 'decrypt', $accessTokenIn);
        $accessToken = json_decode($accessToken, true);
        if (empty($accessToken)){
        	return $this->apiResponse(false, 'API_CODE_ACCESS_TOKEN_NOT_AVAILABLE_ERROR', array('access_token' => $accessTokenIn), array());
        }
        //注意这里调用了web的语言包!!!
        Xphp::$_lang = require_once LANG_PATH.$accessToken['language'].Xphp::$_config['ext'];
        //检查数据
        if(empty($accessToken['user_uuid']) || empty($accessToken['user_name']) || 
            empty($accessToken['user_type'])){
        	
            return $this->apiResponse(false, 'API_CODE_ACCESS_TOKEN_NOT_AVAILABLE');
        }
        //检查超时
        if(time() > $accessToken['out_time']){
        	
            return $this->apiResponse(false, 'API_CODE_ACCESS_TOKEN_TIMEOUT');
        }
        //设置全局属性
        Xphp::$_user = array(
            "username" => $accessToken['user_name'],
            "useruuid" => $accessToken['user_uuid'],
            "usertype" => $accessToken['user_type'],
            "language" => $accessToken['language'],
            'tenantname' => !empty($accessToken['tenant_name'])?$accessToken['tenant_name']:"",
            'tenantuuid' => !empty($accessToken['tenant_uuid'])?$accessToken['tenant_uuid']:"",
            
        );
        return true;
    }
    
    
    
}