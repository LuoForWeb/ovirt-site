<?php

namespace app\v1\hadoop\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Index as TenantIndex;
/**
 * note          Hadoop 集群管理
 * @author       lilingyu@vinchin.com
 * @date         2023/9/14 13:23
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopCluster extends Base
{
    /**
     * 获取集群列表
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     * @return unknown
     */
    public function getCluster($params)
    {
        //起始页
        $offset =  $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //排序字段
        $sort = $params['sort'];
        //排序方式
        $order = $params['order'];
        //获取搜索值
        $keyword = $params['keyword'];
        //获取数据
        // $sql  = "select hc.hadoop_cluster_uuid, hc.hadoop_cluster_name, hc.addition_time, hc.status as cluster_status, hc.authorization, 
        // hn.namenode_ip, hn.rest_api_port, hn.rpc_api_port,verification_type, hn.namenode_username, hn.status  as node_status
        // from hadoop_cluster hc left join hadoop_namenode hn 
        // on hc.hadoop_cluster_uuid = hn.hadoop_cluster_uuid";

        $sql = "select  hn.namenode_ip, hn.rest_api_port, hn.namenode_username, hn.verification_type, hn.status as node_status, hn.ssl_verify_flag, hn.version as node_version,
        temp_hc.hadoop_cluster_uuid,  temp_hc.hadoop_cluster_name as cluster_name, temp_hc.addition_time as add_time, temp_hc.status as online_flag, temp_hc.authorization  as auth_status, temp_hc.proxy_uuid, temp_hc.refresh_time, temp_hc.version,
        temp_hc.agent_name, temp_hc.ip, temp_hc.bu_username, temp_hc.user_uuid 
        from hadoop_namenode as hn
        INNER join ( select hc.hadoop_cluster_uuid, hc.hadoop_cluster_name, hc.proxy_uuid, hc.addition_time, hc.status, hc.authorization, hc.refresh_time, hc.version, hc.user_uuid, ba.agent_name, ba.ip, bu.user_name as bu_username from hadoop_cluster hc left join bd_agent ba on hc.proxy_uuid = ba.agent_uuid 
        left join bd_user bu on hc.user_uuid = bu.user_uuid";
       
        // $sql = "select hc.hadoop_cluster_uuid, hc.hadoop_cluster_name as cluster_name , hc.addition_time as add_time, hc.status as online_flag, hc.authorization as auth_status, count(*) as node_number
        // from hadoop_cluster hc, hadoop_namenode hn where hc.hadoop_cluster_uuid =  hn.hadoop_cluster_uuid";
        $sqlcount  = "select count(hc.id) as count from hadoop_cluster as hc";
        $sqlparams =  array();
        $sqlcountparams =  array();
        $sqlcontxt = ' where ';
        if(v1_auth_need_check_look()){
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
             $resourceUuidSql= v1_auth_get_source_by_type(
                xphp_get_config('resource','RESOURCE_TYPE')['HADOOP'],
				'hc.hadoop_cluster_uuid'
            );
            $sql .= " where  ({$resourceUuidSql})";
            $sqlcount .= " where ({$resourceUuidSql})";
            $sqlcontxt = " and ";
        }

        //关键字
        if(!empty($keyword)){
            $sql .= $sqlcontxt. " hc.hadoop_cluster_name like '%".$keyword."%' ";
            $sqlcount .= $sqlcontxt ." hc.hadoop_cluster_name like '%".$keyword."%' ";
            $sqlcontxt = " and ";
        }
        //如果有开始时间和结束时间 则添加时间查询
        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            //后面拼接and
            $sql .= $sqlcontxt . " hc.addition_time between ? and ? ";
            $sqlparams = array_merge($sqlparams, array($params['start_time'], $params['end_time']));
            $sqlcount .= $sqlcontxt . " hc.addition_time between ? and ? ";
            $sqlcountparams = array_merge($sqlcountparams, array($params['start_time'], $params['end_time']));
            $sqlcontxt = " and ";
        }
        //如果有集群状态
        if(!empty($params['status'])){
            //后面拼接and
            //如果是在线状态 
            if($params['status'] == 'online'){
                $sql .= $sqlcontxt . " hc.status in (1,2) ";
                $sqlcount .= $sqlcontxt . " hc.status in (1,2) ";
            }else{
                $sql .= $sqlcontxt . " hc.status = ? ";
                $sqlparams = array_merge($sqlparams, array($params['status']));
                $sqlcount .= $sqlcontxt . " hc.status = ? ";
                $sqlcountparams = array_merge($sqlcountparams, array($params['status']));
            }
        }
        if(!empty($limit)){
            $sql .= " limit ?, ?";
            $sqlparams = array_merge($sqlparams, array($offset,$limit));
        }
        $sql.= ") as temp_hc on temp_hc.hadoop_cluster_uuid  =  hn.hadoop_cluster_uuid";
        //排序
        if(!empty($sort) && !empty($order)){
            if($sort  != 'node_number'){
                $sql .= " order by ".$sort." ".$order;
            }else{
                $sqlnew  = "select hc.hadoop_cluster_uuid,count(hn.id) as node_count 
                from hadoop_cluster hc 
                left join hadoop_namenode hn on hn.hadoop_cluster_uuid = hc.hadoop_cluster_uuid 
                group by hc.hadoop_cluster_uuid
                order by node_count ".$order;;
                $result = $this->dbSelect($sqlnew);
                $uuidarr =  [];
                foreach($result as $each){
                    $uuidarr[] = "'".$each['hadoop_cluster_uuid']. "'";
                };
                $str = implode(',',$uuidarr);
                $sql .= " order by field(temp_hc.hadoop_cluster_uuid,$str)";
            }
        }else{
            //默认按照时间倒序进行排序
               $sql .= " order by addition_time desc";
        }
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount,$sqlcountparams);
        $info =  array(
            'rows' => array(),
            'total' => $count[0]['count'],
            'authinfo' => $this->getClusterAuthInfo()
        );
        //如果数据为空
        if (empty($result)) {
            return $info;
        }
        $clusteridlist = [];
        //循环处理
        foreach ($result as $each){
            if(!in_array($each['hadoop_cluster_uuid'],$clusteridlist)){
                //拥有者
                $owner =  $this->getUUIDName($each['hadoop_cluster_uuid'], $each['bu_username'] ?? '--');
                $arr = explode(',', $owner);
                if ('admin' == $owner && $user['isThreePowers']) {
                    // 三权模式下，admin显示为sysadmin
                    foreach ($arr as $index => $name) {
                        if ($name == 'admin') {
                            $arr[$index] = 'sysadmin';
                        }
                    }
                    $owner = implode(',', $arr);
                }
                //创建者
                $creator =  $each['bu_username'] ?? '--';
                if ('admin' == $creator && $user['isThreePowers']) {
                    // 三权模式下，admin显示为sysadmin
                    $creator = 'sysadmin';
                }
                // 租户内判断创建者等不等于当前用户,等于当前用户才能操作
                $opFlag = true;
                if ($user['tenantuuid'] && $each['user_uuid'] != $user['userUuid']) {
                    $opFlag = false;
                }
                //如果是不存在的集群
                $node_list = array();
                $node_list[] =  array(
                    "namenode_ip" => $each['namenode_ip'],
                    "username" => $each['namenode_username'],
                    "rest_api" => $each['rest_api_port'],
                    "verify_type" =>  $each['verification_type'],
                    "online_status" => $each['node_status'],
                    "ssl_verify_flag" => $each['ssl_verify_flag'],
                    "version" => $each['node_version'] != null && $each['node_version'] != "" ? $each['node_version']: "--" ,
                );
                $info['rows'][] = array(
                    "cluster_name" => $each['cluster_name'],
                    "cluster_uuid" => $each['hadoop_cluster_uuid'],
                    "node_number" => 1,
                    "add_time" => $each['add_time'],
                    "auth_status" => $each['auth_status'],
                    "online_flag" => $each['online_flag'],
                    "appliance_uuid" => $each['proxy_uuid'],
                    "appliance_des" => $each['agent_name'] . '(' . $each['ip'] . ')',
                    "refresh_time" => $each['refresh_time'],
                    "version" => $each['version'] != null && $each['version'] != "" ? $each['version']: "--" ,
                    "node_info" => $node_list,
                    'op_flag' => $opFlag, // 当前用户是否可以操作该资源
                    'creator' => $creator,
                    'owner' => $owner,
                );
                $clusteridlist[] = $each['hadoop_cluster_uuid'];;
            }else{
                for ($index=0; $index < count($info['rows']); $index++) { 
                    //如果是已经存在集群，则找出集群，新增节点信息
                    if($each['hadoop_cluster_uuid']  == $info['rows'][$index]['cluster_uuid']){
                        $node_info = array(
                            "namenode_ip" => $each['namenode_ip'],
                            "username" => $each['namenode_username'],
                            "rest_api" => $each['rest_api_port'],
                            "verify_type" =>  $each['verification_type'],
                            "online_status" => $each['node_status'],
                            "ssl_verify_flag" => $each['ssl_verify_flag'],
                            "version" => $each['node_version'] != null && $each['node_version'] != "" ? $each['node_version']: "--" ,
                        );
                        $info['rows'][$index]['node_info'][]  = $node_info;
                        $info['rows'][$index]['node_number']++;
                        break;
                    }
                }
            }
        }
        return $info;
    }

    /**
     * 添加集群
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/10/18
     * @return unknown
     */
    public function addCluster($params)
    {
        $tenantHandler = new TenantIndex();
        $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
        //租户内部 添加hadoop集群已经超过授权的hadoop时，授权不足不允许添加
        if(!empty($_SESSION['tenantuuid']) && $settings['auth_way'] ==2){
            $systemHandler = new \app\v1\system\v0\logic\Index();
            $hadooopAuthInfo = $systemHandler->getOneModuleLisenceInfo('hadoop');
            if ($hadooopAuthInfo['valid'] < 1) {
                return $this->muOpResult(false, xphp_get_lang('WEB_TENANT_AVAILABLE_CHECK'),xphp_get_lang('UI_HADOOP_CLUSTER_NOT_ENOUGH'));
            }
        }
        //获取参数
        $data = array();
        // 获取用户
        $user = xphp_get_user_info();
        $data['hadoop_cluster_name']  = strval($params['hadoop_cluster_name']);
        $data['namenodes'] = $params['namenodes'];
        $data['appliance_uuid'] = $params['applianceuuid'];
        $data['user_uuid'] = $user['userUuid'];
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->addCluster($nodeuuid, $data);
        $result = $mbResult['result'];
        $operate = xphp_get_lang('WEB_HADOOP_ADD_CLUSTER');
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
        
    /**
     * 获取单个集群
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/9/14
     * @return unknown
     */
    public function getClusterInfo($params)
    {
        $clusteruuid =  $params['cluster_uuid'];
        $sqlparams =  array($clusteruuid);
        // $sql = "select  hn.namenode_ip, hn.rest_api_port, hn.rpc_api_port, hn.namenode_username, hn.verification_type, hn.status as node_status, 
        // temp_hc.hadoop_cluster_uuid,  temp_hc.hadoop_cluster_name as cluster_name, temp_hc.addition_time as add_time, temp_hc.status as online_flag, temp_hc.authorization  as auth_status
        // from hadoop_namenode as hn INNER join ( select *  from hadoop_cluster as hc where hc.hadoop_cluster_uuid  = ? ) as temp_hc on temp_hc.hadoop_cluster_uuid  =  hn.hadoop_cluster_uuid";
        $sql = "select  hn.namenode_ip, hn.rest_api_port, hn.namenode_username, hn.verification_type, hn.realm_name, hn.krb5_keytab, hn.realm_kdc_server, hn.realm_manage_server, hn.rest_api_principal, hn.udp_preference_limit, hn.ssl_verify_flag,
        hc.hadoop_cluster_uuid, hc.hadoop_cluster_name, hc.proxy_uuid from hadoop_namenode hn, hadoop_cluster hc where hc.hadoop_cluster_uuid = hn.hadoop_cluster_uuid and hc.hadoop_cluster_uuid  = ?";
        $result = $this->dbSelect($sql, $sqlparams);
        $info =  array();
        $node_list =  [];
        if(empty($result)){
            return $info;
        }
        foreach($result as $each){
            $node_list[] =  array(
                "verification_type" =>  $each['verification_type'],
                "namenode_ip" => $each['namenode_ip'],
                "rest_api_port" => $each['rest_api_port'],
                "hadoop_user_name" => $each['namenode_username'],
                "realm_name" => $each['realm_name'],
                "kdc_server" => $each['realm_kdc_server'],
                "manager_server" => $each['realm_manage_server'],
                "rest_api_principal" => $each['rest_api_principal'],
                "udp_preference_limit" => $each['udp_preference_limit'],
                "krb5_keytab" => $each['krb5_keytab'],
                "ssl_verify_flag" => $each['ssl_verify_flag'],
            );
        }
        $info['name_nodes'] = $node_list;
        $info['cluster_name'] =  $result[0]['hadoop_cluster_name'];
        $info['cluster_uuid'] =  $result[0]['hadoop_cluster_uuid'];
        $info['applianceuuid'] =  $result[0]['proxy_uuid'];
        return $info;
    }

    /**
     * 修改集群
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/10/18
     * @return unknown
     */
    public function editCluster($params)
    {
        //获取参数
        $data['hadoop_cluster_name']  = $params['hadoop_cluster_name'];
        $data['hadoop_cluster_uuid'] = $params['cluster_id'];
        $data['deleted_namenode_ip'] = $params['deleted_namenode_ip'] ;
        $data['modify_namenodes'] = $params['modify_namenodes'] ;
        $data['appliance_uuid'] = $params['applianceuuid'];
         //操作权限判断
        $this->checkAuthBySourceUuid($params['cluster_id'],xphp_get_config('resource','RESOURCE_TYPE')['HADOOP']);
        //获取节点
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->editCluster($nodeuuid, $data);
        $result = $mbResult['result'];
        $operate = xphp_get_lang('WEB_HADOOP_EDIT_CLUSTER');
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除集群
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/10/18
     * @return unknown
     */
    public function deleteCluster($params){
        //获取参数
        $data['deleted_hadoop_uuid_list'] = json_decode($params['hadoop_uuid_list']) ;
        //操作权限判断
        $this->checkAuthBySourceUuid(implode("','", $data['deleted_hadoop_uuid_list']),xphp_get_config('resource','RESOURCE_TYPE')['HADOOP']);
        //获取节点
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->deleteCluster($nodeuuid, $data);
        $result = $mbResult['result'];
        $operate = xphp_get_lang('WEB_HADOOP_DELETE_CLUSTER');
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            //如果删除成功则删除租户资源表和租户资源组表
            // $user = xphp_get_user_info();
            $resourceDes = implode("','", $data['deleted_hadoop_uuid_list']);
            $sql =  "delete from mt_user_resource where resource_uuid in ('" . $resourceDes . "') and resource_type = 60";
            $sql1 = "delete from mt_resource_resource_group where resource_uuid in ('" . $resourceDes . "') and resource_type = 60";
            $result1 = $this->dbExec($sql);
            $result2 = $this->dbExec($sql1);
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 更新时间
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2024/4/9
     * @return unknown
     */
    public function updateTime($params){
        $refresh = intval($params['refresh']) * 60;
    	$sql = "update bd_system set hadoop_refresh_interval = ?";
        $sqlparams = array($refresh);
        $result = $this->dbExec($sql,$sqlparams);
        return $result; 
    }

    
    /**
     * 获取时间
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2024/4/9
     * @return unknown
     */
    public function getTime(){
        $sql = "select hadoop_refresh_interval from bd_system";
        $data = $this->dbSelect($sql);
        return ceil($data[0]['hadoop_refresh_interval']);
    }

    /**
     * 刷新集群
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/10/18
     * @return unknown
     */
    public function refreshCluster($params){
        
        //获取节点
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $data['hadoop_cluster_uuid'] =  $params['hadoop_cluster_uuid'];
         //操作权限判断
        $this->checkAuthBySourceUuid($params['hadoop_cluster_uuid'],xphp_get_config('resource','RESOURCE_TYPE')['HADOOP']);
        $mbResult = $this->service()->refreshCluster($nodeuuid, $data);
        $result = $mbResult['result'];
        $operate = xphp_get_lang('WEB_HADOOP_REFRESH_CLUSTER');
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 获取集群名称
     * @param array $params
     * @author lilingyu@vinchin.com
     * @date 2023/10/18
     * @return unknown
     */
    public function getClusterName(){
        //获取集群名称
        $clusterName = xphp_get_lang('WEB_HADOOP_CLUSTER_NAME');
        return $this->getValidClusterName($clusterName);
    }
    /**
     * 获取可用的任务名
     * @param string $taskName 任务名前缀
     * @return string
     */
    public function getValidClusterName($clusterName)
    {
        $oldClusterName = $clusterName;
        for ($i = 1; $i < 1000; $i++) {
            $clusterName .= $i;
            $sql = "select id from hadoop_cluster where hadoop_cluster_name = ?";
            $data = $this->dbSelect($sql, array($clusterName));
            if (empty($data)) {
                return $clusterName;
            }
            $clusterName = $oldClusterName;
        }
        return $oldClusterName;
    }
    /**
     * 授权集群
     * @param unknowun $params
     * @author lilingyu@vinchin.com
     * @date 2023/10/18
     * @return unknown
     */
    public function authCluster($params){
        //检测可用个数  如果是按容量授权  不检测
        $systemHandler = new \app\v1\system\v0\logic\Index();
        $licenseInfo = $systemHandler->getSystemLicenseType();
        if (($licenseInfo['licensetype'] != xphp_get_config('auth', 'LISENCE_INFO')['type']['storage'])&&
        $params['authflag'] == 1) {
            //租户内部检查可用数量是否超过授权个数
            if(!empty($_SESSION['tenantuuid'])){
                $tenantHandler = new TenantIndex();
                $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
                if ($settings['auth_way'] ==2) {
                    $currentCount = count($params['hadoop_uuid_list']);
                    $systemHandler = new \app\v1\system\v0\logic\Index();
                    $hadoopAuthInfo = $systemHandler->getOneModuleLisenceInfo('hadoop');
                    if ($hadoopAuthInfo['valid'] < $currentCount) {
                        return $this->muOpResult(false, xphp_get_lang('WEB_PT_LICENSE_OP_MODIFY_HADOOP_CLUSTER_LICENSE'),xphp_get_lang('UI_HADOOP_CLUSTER_NOT_ENOUGH'));
                    }
                }
            }
            $hadoopinfo = $this->getClusterAuthInfo();
            if ($hadoopinfo['hadoop']['valid'] < count($params['hadoop_uuid_list'])) {
                return $this->muOpResult(false, xphp_get_lang('WEB_PT_LICENSE_OP_MODIFY_HADOOP_CLUSTER_LICENSE'),xphp_get_lang('UI_HADOOP_CLUSTER_NOT_ENOUGH'));
            }
        }
        $msg =  array(
            'hadoop_auth_flag' => $params['authflag'],
            'cluster_uuid_list' => $params['hadoop_uuid_list']
        );
        $mbResult = $this->service()->authCluster($msg);
        return $mbResult;

    }
    /**
     * 得到hadoop授权信息
     * @param unknown $params
     * @return unknown
     */
    public function getClusterAuthInfo()
    {
        $systemHandler = new \app\v1\system\v0\logic\Index();
        $hadoopInfo = $systemHandler->getOneModuleLisenceInfo('hadoop');
        $licenseInfo = $systemHandler->getSystemLicenseType();
        $info = array(
            'hadoop' => $hadoopInfo,
            'licensetype' => $licenseInfo['licensetype']
        );
        return $info;
    }


    /**
     * 根据hadoop集群uuid获取当前的使用者是谁
     *
     * @param string $clusterUUID hadoop集群uuid
     * @param string $userName 拥有者
     * @return string
     */
    private function getUUIDName(string $clusterUUID, string $userName = ''): string
    {
        $sqlParams = [$clusterUUID];
        // 先 再分配的资源和资源组去查询
        $sql = "select user_uuid from mt_user_resource where resource_type = 60 and resource_uuid = ? limit 1";
        $array = $this->dbSelect($sql, $sqlParams);

        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 60";
            $array = $this->dbSelect($sql, $sqlParams);
        }

        if ($array) {
            $sql = "select user_name from bd_user where user_uuid in ('" . implode("','", array_column($array, 'user_uuid')) . "')";
            $data = $this->dbSelect($sql);
            $userName = implode(',', array_column($data, 'user_name'));
        }

        return $userName;
    }
    
    
}
