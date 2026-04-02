<?php
/******************************************* 
** 数据库备份处理类 
** 
** @author       xiezhuowei@vinchin.com
** @date         2015-11-12 上午10:23:05 
** @version      1.0.0 
** @copyright    Copyright 2015 vinchin.com 
********************************************/
require_once XPHP_PATH.'utils/BLLHandler.class.php';
class DbHandler extends BLLHandler{
    /**
     * //TODO 测试
     * @param unknown $params
     */
    public function testMsg($params){
        
        $opName = 'DB_MYSQL_OP_GET_INSTANCE_INFO';
        $agentUUID = 'b4890b93-3379-45c0-95eb-3405c3f2a396';
        $msg = array(
            'username' => 'liyuehua',
            'password' => 'dashabi',
            'port' => 3306
        );
        $mbResult = $this->mbDBAgentMsg(3, $opName, $agentUUID, json_encode($msg));
        
        var_dump($mbResult);
    }
    
    /**
     * 获取所有MySQL授权的代理端信息
     * @param unknown $params
     */
    public function getMysqlAgentInfo($params){
        $sql = "select agent_uuid, agent_name, ip, authorization_module from bd_agent where user_uuid = ?";
        $data = $this->dbSelect($sql, array(Xphp::$_user['useruuid']));
        $agentInfo = array();
        $systemHandler = Xphp::instance('SystemHandler');
        foreach ($data as $d){
            if(!($systemHandler->checkModuleValid($d['authorization_module'], 'mysql'))){
                continue;
            }
            $agentInfo[] = array(
                'uuid' => $d['agent_uuid'],
                'name' => $d['agent_name'],
                'ip' => $d['ip']
            );
        }
        return json_encode($agentInfo);
    }
}

?>