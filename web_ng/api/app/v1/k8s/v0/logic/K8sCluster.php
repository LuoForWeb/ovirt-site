<?php

namespace app\v1\k8s\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\KubeOpcode;
use app\v1\resources\v0\logic\Client;
use app\v1\resources\v0\logic\Index;
use xphp\db\Op;
use xphp\helper\Utils;

/**
 * note          容器 脚本管理
 * @author       liushuai@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class K8sCluster extends Base
{
    /**
     * 获取集群列表
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/4/14
     * @return unknown
     */
    public function getCluster($params)
    {
        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //排序字段
        $sort = $params['sort'];
        //排序方式
        $order = $params['order'];
        //获取搜索值
        $search_val = $params['search_val'];
        //排序参数转换(多表要转换,单表不用转换)
        $sort_params = array();
        //获取数据
        $sql = "select kube_cluster.id, cluster_uuid, cluster_name, host, port, hostname, nodes, cpu, memory, kube_cluster.create_time,
                online_flag, net_model, bd_user.user_name as create_user_name ,kube_cluster.description 
                from kube_cluster 
                left join bd_user 
                on bd_user.user_uuid = kube_cluster.user_uuid where 1=1";
        $sqlcount = "select count(id) as count from kube_cluster where 1=1";
        $sqlparams = array();
        //获取当前用户所拥有的集群列表
        if(v1_auth_need_check_look()){
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql= v1_auth_get_source_by_type(
                xphp_get_config('resource','RESOURCE_TYPE')['K8S'],
				'cluster_uuid'
            );
            $sql .= " and ({$resourceUuidSql})";
            $sqlcount .= " and ({$resourceUuidSql})";
        }
        
        if(!empty($search_val)){
            $sql .= " and cluster_name like '%".$search_val."%'  or host like '%".$search_val."%'";
            $sqlcount .= " and cluster_name like '%".$search_val."%'  or host like '%".$search_val."%'";
        }
        //如果还有排序参数,则进行排序
        if(!empty($sort) && !empty($order)){
            $sql .= " order by ".$sort." ".$order;
        }
        if(!empty($offset) && !empty($limit)){
            $sql .= " limit ?, ?";
            $sqlparams = array_merge($sqlparams, array($offset,$limit));
        }
        //处理数据
        $result = $this->dbSelect($sql,$sqlparams);
        $count = $this->dbSelect($sqlcount);
        $info = array(
            'rows' => array(),
            'total' => $count[0]['count'],
        );
        //如果数据为空
        if (empty($result)) {
            return $info;
        }

      
        //循环处理
        foreach ($result as $each) {
           

            $errorDescription = json_decode($each['description'],true);
            $info['rows'][] = array(
                'id' => $each['id'],
                //获取集群名称
                'cluster_name' => $each['cluster_name'],
                //获取IP或域名
                'host' => $each['host'],
                //获取集群master名称
                'hostname' => $each['hostname'],
                //获取总节点数
                'nodes' => $each['nodes'],
                //获取总CPU数
                'cpu' => $each['cpu'],
                //获取总内存
                'memory' => v1_calsize($each['memory'], true)   ,
                //获取创建时间
                'create_time' => $each['create_time'],
                //获取集群在线状态
                'online_flag' => array(
                    'online_flag' => $each['online_flag'],
                    'error_code' => $errorDescription['error_code'] ?? "--",
                    'error_code_description' => $errorDescription['error_code_description'] ?? "--",
                    'error_message' => $errorDescription['error_message'] ?? "--",
                ),
                //获取创建人
                'create_user_name' => $each['create_user_name'] ?? "--",
                'owner_name' => $this-> getUuidName($each['cluster_uuid'],$each['create_user_name']),
                //获取集群唯一标识
                'cluster_uuid' => $each['cluster_uuid'],
            );
        }
        return $info;
    }


    /**
     * 根据 uuid 获取当前的使用者是谁 只查询分配的资源/组
     * @param string $nasUuid cluster设备uuid
     * @param string $userName  拥有者名称
     * @return string
     */
    public function getUuidName(string $clusterUuid, string $userName = ''): string
    {
        $sqlParams = [$clusterUuid];
        // 先 再分配的资源和资源组去查询
        $sql2 = "select user_uuid from mt_user_resource where resource_type = 62 and resource_uuid = ? limit 1";
        $array = $this->dbSelect($sql2, $sqlParams);
        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 62";
            $array = $this->dbSelect($sql, $sqlParams);
        }
        if ($array) {
            $sql = "select user_name from bd_user where user_uuid = ?";
            $data = $this->dbSelect($sql, [$array[0]['user_uuid']]);
            $userName = $data[0]['user_name'];
        }
        return $userName;
    }
    

    /**
     * 获取单个集群详情(主要就是集群节点相关信息)
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getClusterThis($params)
    {
        //获取集群uuid
        $clusteruuid = $params['cluster_uuid'];
        //SQL
        $sql = "select node_uuid,agent_uuid,cluster_uuid,ip,port,hostname,os_type,os_name,cpu,memory,create_time,in_master,
online_flag from kube_node where cluster_uuid = ?";
        $sqlcount = "select count(id) as count from kube_node where cluster_uuid = ?";
        $sqlparams = array($clusteruuid);
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount, $sqlparams);
        $info = array(
            'rows' => array(),
            'total' => $count[0]['count'],
        );
        if (empty($result)) {
            return $info;
        }
        foreach ($result as $each) {
            $info['rows'][] = array(
                'node_uuid' => $each['node_uuid'],
                //获取集群唯一标识uuid
                'cluster_uuid' => $each['cluster_uuid'],
                //获取节点IP
                'ip' => $each['ip'],
                //获取端口
                'port' => $each['port'],
                //获取节点主机名
                'hostname' => $each['hostname'],
                //获取操作系统
                'os' => $each['os_type'],
                //获取CPU个数
                'cpu' => $each['cpu'],
                //获取内存数据
                'memory_val' => $each['memory'],
                'memory' => v1_calsize($each['memory'],true),
                //获取创建时间
                'create_time' => $each['create_time'],
                //获取节点是否在master上
                'in_master' => $each['in_master'],
                //获取节点状态
                'online_flag' => $each['online_flag'],
            );
        }
        return $info;
    }

    /**
     * 手动添加集群
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function addClusterManual($params)
    {
        //获取其中部分参数
        //获取IP地址
        $host = $params['host'];
        //获取端口号
        $port = $params['port'];
        //获取名字
        $name = $params['name'];
        //后台发消息调用后端接口
        $dataList = array(
            'host' => $host,
            'port' => $port,
            'name' => $name,
        );
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->service()->addClusterManual($nodeuuid, $dataList);

        // 错误消息转换
        if (!$mbResult['result'] && !empty($mbResult['error_code'])) {
            $mbResult['message'] = xphp_get_lang(v1_get_error_des($mbResult['error_code']));
        }

        return $mbResult;
    }

    /**
     * 远程添加集群
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function addClusterRemote($params)
    {
        //获取参数
        $dataList = array();
        //获取类型
        $dataList['type'] = $params['type'];
        //获取集群地址
        $dataList['ssh_host'] = $params['ssh_host'];
        //获取SSH端口
        $dataList['ssh_port'] = $params['ssh_port'];
        //获取用户名
        $dataList['ssh_user'] = $params['ssh_user'];
        //获取密码
        $dataList['ssh_password'] = $params['ssh_password'];

        //远程config添加模式
        //获取文件内容
        $dataList['kube_config'] = $params['kube_config'];

        //高级配置
        //获取代理节点CPU限制
        $dataList['limit_cpu'] = $params['limit_cpu'];
        //获取内存限制
        $dataList['limit_memory'] = $params['limit_memory'];
        //获取网络模式
        $dataList['net_mode'] = $params['net_mode'];
        //获取网络模式IP地址
        $dataList['net_mode_host'] = $params['net_mode_host'];
        //获取网络模式IP端口
        $dataList['net_mode_port'] = $params['net_mode_port'];

        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->service()->addClusterRemote($nodeuuid, $dataList);
        return $mbResult;
    }

    /**
     * 删除集群
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function deleteCluster($params)
    {
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_DELETE_CLUSTER_SUCCESS'),
            'data' => array(),
        );
        //获取参数
        $dataList = array(
            'cluster_uuids' => $params['cluster_uuid_list'],
        );
        //操作权限判断
        $this->checkAuthBySourceUuid(implode(',', $params['cluster_uuid_list']),xphp_get_config('resource','RESOURCE_TYPE')['K8S']);
        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->service()->deleteCluster($nodeuuid, $dataList);
        if(!$mbResult['result']){
            $resultInfo['success'] = false;
            $resultInfo['code'] = $mbResult['error_code'];
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CLUSTER_DELETE_CLUSTER_FAILURE');
        }
        return $resultInfo;
    }

    /**
     * 刷新集群
     * @author liushuai@vinchin.com
     * @date 2023/7/12
     * @return unknown
     *
     */
    public function refreshCluster()
    {
        //获取参数
        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->service()->refreshCluster($nodeuuid);
        return $mbResult;
    }

    /**
     * 修改单个集群
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function editCluster($params)
    {
        //获取集群uuid
        $clusteruuid = $params['cluster_uuid'];
        //操作权限判断
        $this->checkAuthBySourceUuid($params['cluster_uuid'],xphp_get_config('resource','RESOURCE_TYPE')['K8S']);
        //获取集群名称
        $clustername = $params['cluster_name'];
        $sql = "update kube_cluster set cluster_name = ? where cluster_uuid = ?";
        $sqlparams = array($clustername,$clusteruuid);
        $result = $this->dbExec($sql, $sqlparams);
        return $result;
    }
   
    
    /**
     * 获取集群列表---第一层type=0
     * @param array $params
     * @author liushuai@vinchin.com
     * @date 2023/4/14
     * @return unknown
     */
    public function getClusterZtree($params)
    {
        //获取数据
        $sql = "SELECT count(*) as not_master_node,kc.cluster_name,kc.online_flag,kc.host,kc.hostname,kc.create_time,kc.cluster_uuid,kc.nodes 
        FROM `kube_cluster` kc
        left join kube_node kn on kc.cluster_uuid = kn.cluster_uuid
        where kn.in_master !=1 and kc.online_flag = 1";

        //获取当前用户所拥有的集群列表
        if(v1_auth_need_check_look()){
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql= v1_auth_get_source_by_type(
                xphp_get_config('resource','RESOURCE_TYPE')['K8S'],
				'kc.cluster_uuid'
            );
            $sql .= " and ({$resourceUuidSql})";
        };
        $sql .= " group by kc.cluster_uuid order by create_time desc";
        //处理数据
        $result = $this->dbSelect($sql);
        //如果数据为空
        if (empty($result)) {
            return array();
        }
        $info = array();
        //循环处理
        foreach ($result as $each) {
            $info[] = array(
                'id' => $each['cluster_uuid'],
                'pId' => 0,
                'type' => 0,
                'isParent' => true,
                'nocheck' => true,
                'iconSkin' => "ztree_cluster",
                'title' => $each['cluster_name'],
                'name' => $each['cluster_name'],

                //-----
                //获取集群名称
                'cluster_name' => $each['cluster_name'],
                //获取集群master名称
                'hostname' => $each['hostname'],
                //获取创建时间
                'create_time' => $each['create_time'],
                //获取集群在线状态
                'online_flag' => $each['online_flag'],
                //获取集群唯一标识
                'cluster_uuid' => $each['cluster_uuid'],
                //获取集群节点数量
                'nodes' => $each['nodes'],
                'not_master_node' => $each['not_master_node'],
            );
        }  
        return $info;
    }


    /**
     * 按应用获取命名空间---第二层type=1
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getNameSpace($params)
    {

        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_GET_NAMESPACE_SUCCESS'),
            'data' => array(),
        );
        //获取参数
        $dataList = array();
        $dataList['cluster_uuid'] = $params['cluster_uuid'];
        // 1是命名空间 2是应用
        $get_namespace_type = intval($params['get_namespace_type']);
        $nocheck = true;
        if($get_namespace_type == 1){
            //是否获取pvcs
            $dataList['with_pvcs'] = 1;
            //是否有勾选框
            $nocheck = false;
        }else{
            $dataList['with_pvcs'] = 2;
            $nocheck = true;
        }


        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();

        $mbResult = $this->service()->getNameSpace($nodeuuid, $dataList);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CLUSTER_GET_NAMESPACE_FAILURE');
            return $resultInfo;
        }
        //获取命名空间
        $message = $mbResult['message'];
        //获取命名空间
        $namespacesList = $message['namespaces'];
        


        $nodeList = array();
        foreach ($namespacesList as $each){
            $nodeList[] = array(
                'id' => $params['cluster_uuid']."_".$each['name'],
                'pId' => $params['cluster_uuid'],
                'type' => 1,
                'name' => $each['name'],
                'title' => $each['name'],
                'iconSkin' => "ztree_namespace",
                'nocheck' => $nocheck,
                'isParent' => true,
                //----
                'cluster_uuid' => $params['cluster_uuid'],
                'namespace' => $each['name'],
                'pvcs' =>$each['pvcs'],
            );
        }

        //获取命名空间时额外获取一层集群资源
        $nodeList[] = array(
            'id' => 'Cluster_Resources',
            'pId' => $params['cluster_uuid'],
            'type' => 3,
            'isParent' => true,
            'nocheck' => false,
            'name' =>xphp_get_lang('WEB_KUBE_CLUSTER_CLUSTER_RESOURCES'),
            'title' => xphp_get_lang('WEB_KUBE_CLUSTER_CLUSTER_RESOURCES'),
            'iconSkin' => "ztree_CLUSTER",
            //--
            'cluster_uuid' => $params['cluster_uuid'],
            'namespace' => "",
            'app' => "",
            'app_type' => 0,
            'pvcs' =>"",
            'category_description' =>  'CLUSTER',
        );




        $resultInfo['data'] = $nodeList;
        return $resultInfo;
    }


     /**
     * 获取命名空间下应用---第二(额外)层type=2
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getApp($params)
    {
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_GET_APPLICATION_SUCCESS'),
            'data' => array(),
        );
        //获取参数
        $dataList = array();
        $dataList['cluster_uuid'] = $params['cluster_uuid'];
        $dataList['namespace'] = $params['namespace'];
        $dataList['with_pvcs'] = 1; //获取app层一定会获取pvcs
        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->service()->getApp($nodeuuid, $dataList);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CLUSTER_GET_APPLICATION_FAILURE');
            return $resultInfo;
        }
        $message = $mbResult['message'];
        $apps = $message['apps'];
        $nodeList = array();
        if(empty($apps)){
            $resultInfo['data'][] = array(
                'id' => $params['cluster_uuid']."_".$params['namespace']."_1",
                'pId' =>$params['cluster_uuid']."_".$params['namespace'],
                'type' => 2,
                'name' => xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE'),
                'iconSkin' => "ztree_app",
                'isParent' => false,
                'nocheck' => true,
            );
            return $resultInfo;
        }
        foreach ($apps as $each){
            $nodeList[] = array(
                'id' => $params['cluster_uuid']."_".$params['namespace']."_".$each['app']."_".$each['app_type'],
                'pId' =>$params['cluster_uuid']."_".$params['namespace'],
                'type' => 2,
                'name' => $each['app'],
                'title' => $each['app'],
                'iconSkin' => "ztree_app",
                'isParent' => true,
                'nocheck' => false,
                //----
                'cluster_uuid' => $params['cluster_uuid'],
                'namespace' => $params['namespace'],
                'app' => $each['app'],
                'app_type' => $each['app_type'],
                'app_type_name' => $each['app_type_name'],
                'pvcs' => $each['pvcs'],
            );
        }


        $resultInfo['data'] = $nodeList;
        return $resultInfo;
    }

    /**
     * 获取大分组---第三层type=3
     * @param $cluster_uuid 集群uuid
     * @param $namespace 命名空间
     * @param $app app名称 选填 如果是app那一层则有
     * @author liushuai@vinchin.com
     * @date 2023/5/19
     * @return unknown
     */
    public function getResourceBigGroup($params){
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_GROUP_SUCCESS'),
            'data' => array(),
        );

        $dataList = array();
        //获取集群uuid
        $dataList['cluster_uuid'] = $params['cluster_uuid'];
        //获取命名空间
        $dataList['namespace'] = $params['namespace'];
        //获取app名称
        $dataList['app'] = $params['app'];
        //获取app类型
        $dataList['app_type'] = $params['app_type'];
        //获取集群备份类型
        $dataList['get_namespace_type'] = $params['get_namespace_type'];
        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->service()->getResourceBigGroup($nodeuuid, $dataList);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_GROUP_FAILURE');
            return $resultInfo;
        }
        //获取信息
        $message = $mbResult['message'];
        //获取资源分组列表
        $categoryList = $message['categories'];
        $info = array();
        foreach ($categoryList as $each){
            //如果后端给出不显示该分类则不显示
            if(!$each['has_items']){
                continue;
            }
            //获取资源分类的名称
            $categoryName = $each['description'];
            $name = $this->get_categoryName($categoryName);
            $pid = "";
            if(intval($params['get_namespace_type']) == 2){
                //按应用备份
                $pid = $params['cluster_uuid']."_".$params['namespace']."_".$each['app']."_".$each['app_type'];
            }else{
                $pid = $params['cluster_uuid']."_".$params['namespace'];
            }
            

            $info[] = array(
                'id' => $params['cluster_uuid']."_".$params['namespace']."_".$each['name'],
                'pId' => $pid,
                'type' => 3,
                'isParent' => true,
                'nocheck' => true,
                'name' =>$name['categoryName'],
                'title' => $name['categoryName'],
                'iconSkin' => "ztree_".$name['iconName'],
                //--
                'cluster_uuid' => $params['cluster_uuid'],
                'namespace' => $params['namespace'],
                'app' => $params['app'] ?? "",
                'app_type' => $params['app_type'] ?? 0,
                'category_description' =>  $each['name'],

            );
        }
        $resultInfo['data'] = $info;
        return $resultInfo;
    }

     /**
     * 获取命名空间或应用下资源小分组---第四层type=4
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getResourceGroup($params)
    {

        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_SUBCATEGORY_SUCCESS'),
            'data' => array(),
        );
        //获取参数
        $dataList = array();
        //获取集群唯一标识uuid
        $dataList['cluster_uuid'] = $params['cluster_uuid'];
        //获取命名空间名称
        $dataList['namespace'] = $params['namespace'];
        $dataList['get_namespace_type'] = $params['get_namespace_type'];
        //获取应用名称.如果是按命名空间备份时该值为空
        $dataList['app'] = $params['app'] ?? "";
        $dataList['app_type'] = $params['app_type'];
        $dataList['category'] = $params['category'];

        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
       
        $mbResult = $this->service()->getResourceGroup($nodeuuid, $dataList);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_SUBCATEGORY_FAILURE');
            return $resultInfo;
        }

        //得到message
        $message = $mbResult['message'];
        //得到类型
        $kinds = $message['kinds'];
        $nodeList = array();
        foreach ($kinds as $each){
            //获取资源数目 如果资源为0 则不可展开下一级
            $sub_resource_items_count = intval($each['sub_resource_items_count']);
            $isParent = true;
            if($sub_resource_items_count == 0){
                $isParent = false;
            }


            $nodeList[] = array(
                'id' => $params['cluster_uuid']."_".$params['namespace']."_".$params['category']."_".$each['name'],
                'pId' => $params['cluster_uuid']."_".$params['namespace']."_".$params['category'],
                'type' => 4,
                'name' => $each['pretty_name']."<span class='titile_gray_show'>  (version: ".$each['version'].") </span>",
                'title' => "group: ".$each['group'].";version: ".$each['version'],
                'iconSkin' => "ztree_group",
                'nocheck' => true,
                'isParent' => $isParent,
                'kind_name' => $each['name'],
                //-----
                'cluster_uuid' => $params['cluster_uuid'],
                'namespace' => $params['namespace'],
                'app' => $params['app'],
                'app_type' => $params['app_type'],
                'category' => $params['category'],
                'pretty_name' => $each['pretty_name'],
                'version' => $each['version'],
                'group' => $each['group'],
                'kind' => $each['kind'],
                'sub_resource_items_count' => $each['sub_resource_items_count'],
            );

        }

        $resultInfo['data'] = $nodeList;

        return  $resultInfo;
    }

    
    /**
     * 获取资源分类的名称
     */
    public function get_categoryName($description){
        $resultDes = array(
            'iconName' => '',
            'categoryName' => xphp_get_lang('WEB_KUBE_CLUSTER_UNKNOWN'),
        );

        switch($description){
            case 'PVC':
                $resultDes['iconName'] = '';
                $resultDes['categoryName'] = xphp_get_lang('WEB_KUBE_CLUSTER_PERSISTENT_VOLUME_CLAIM');
                break;
            case 'Workloads':
                $resultDes['iconName'] = 'WORKLOADS';
                $resultDes['categoryName'] = xphp_get_lang('WEB_KUBE_CLUSTER_WORKLOADS');
                break;
            case 'Services & Load Balancing':
                $resultDes['iconName'] = 'SERVICES';
                $resultDes['categoryName'] = xphp_get_lang('WEB_KUBE_CLUSTER_SERVICES_AND_LOAD_BALANCING');
                break;
            case 'Config & Storage':
                $resultDes['iconName'] = 'CONF_STORAGE';
                $resultDes['categoryName'] = xphp_get_lang('WEB_KUBE_CLUSTER_CONFIGURATIONS_AND_STORAGE');
                break;
            case 'Access Control & Permissions':
                $resultDes['iconName'] = 'ACCESS_CONTROL';
                $resultDes['categoryName'] = xphp_get_lang('WEB_KUBE_CLUSTER_ACCESS_CONTROL_AND_PERMISSIONS');
                break;
            case 'Network Policies':
                $resultDes['iconName'] = 'NETWORK';
                $resultDes['categoryName'] = xphp_get_lang('WEB_KUBE_CLUSTER_NETWORK_POLICIES');
                break;
            case 'CRD Instances':
                $resultDes['iconName'] = 'CUSTOM';
                $resultDes['categoryName'] = xphp_get_lang('WEB_KUBE_CLUSTER_CUSTOM_RESOURCES');
                break;
            default:
                $resultDes['iconName'] = '';
                $resultDes['categoryName'] = xphp_get_lang('WEB_KUBE_CLUSTER_UNKNOWN');
                break;
        }
        return $resultDes;

    }


    /**
     * 获取命名空间或应用下资源---第五层type=5
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getResource($params)
    {

        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_SUCCESS'),
            'data' => array(),
        );
        //获取参数
        $dataList = array();
        //获取集群唯一标识uuid
        $dataList['cluster_uuid'] = $params['cluster_uuid'];
        $dataList['get_namespace_type'] = $params['get_namespace_type'];
        //获取命名空间名称
        $dataList['namespace'] = $params['namespace'];
        //获取应用名称.如果是按命名空间备份时该值为空
        $dataList['app'] = $params['app'];
        //获取app类型
        $dataList['app_type'] = $params['app_type'];
        //获取资源分组
        $dataList['category'] = $params['category'];
        //获取资源小分组所属分组
        $dataList['group'] = $params['group'];
        //获取资源小分组所属版本
        $dataList['version'] = $params['version'];
        //获取资源小分组所属类型
        $dataList['kind'] = $params['kind'];
        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        // dump(json_encode($dataList));
        $mbResult = $this->service()->getResource($nodeuuid, $dataList);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_FAILURE');
            return $resultInfo;
        }
       
        //获取信息
        $message = $mbResult['message'];
        //获取资源
        $resources = $message['resources'];
        $nodeList = array();
        foreach ($resources as $each){
            $nodeList[] = array(
                'id' => $each['name'],
                'pId' => $params['cluster_uuid']."_".$params['category']."_".$each['name'],
                'type' => 5,
                'name' => $each['name'],
                'title' => $each['name'],
                'iconSkin' => "ztree_resource",
                'nocheck' => true,
                'isParent' => false,
                //-----
                'cluster_uuid' => $params['cluster_uuid'],
                'namespace' => $params['namespace'],
                'app' => $params['app'],
                'app_type' => $params['app_type'],
                'category' => $params['category'],
                'version' => $each['version'],
                'group' => $each['group'],
                'kind' => $each['kind'],
            );
        }
        $resultInfo['data'] = $nodeList;
        return $resultInfo;
    }

     /**
     * 从客户端获取资源详情---第六层 最后一层
     * @author liushuai@vinchin.com
     * @date 2023/4/17
     * @return unknown
     *
     */
    public function getResourceDetails($params)
    {
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_DETAIL_SUCCESS'),
            'data' => array(),
        );
        //获取参数
        $dataList = array();
        //获取集群唯一标识uuid
        $dataList['cluster_uuid'] = $params['cluster_uuid'];
        //获取命名空间名称
        $dataList['namespace'] = $params['namespace'];
        //获取资源所属分组
        $dataList['group'] = $params['group'];
        //获取资源所属版本
        $dataList['version'] = $params['version'];
        //获取资源所属类型
        $dataList['kind'] = $params['kind'];
         //获取名字
         $dataList['name'] = $params['name'];
        //获取节点
        $nodeuuid = $this->getLocalNodeUUID();
        $mbResult = $this->service()->getResourceDetails($nodeuuid, $dataList);
        $resultInfo['success'] = $mbResult['result'];
        $resultInfo['code'] = $mbResult['error_code'];
        if(!$resultInfo['success']){
            $resultInfo['message'] = xphp_get_lang('WEB_KUBE_CLUSTER_GET_RESOURCE_DETAIL_FAILURE');
            return $resultInfo;
        }
        //获取信息
        $message = $mbResult['message'];
        //获取yaml信息
        $yaml  = $message['yaml'];
        $resultInfo['data'] = $yaml;
        return $resultInfo;
    }









   

   

    
   

    /**
     * 得到本地节点UUID
     */
    public function getLocalNodeUUID()
    {
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app')['NODETYPE']['MASTER']));
        return $data[0]['node_uuid'];
    }

    /**
     * 得到所有节点网络信息
     * @param unknown $params
     */
    public function getNetworkInfo($params){
        $resultInfo = array(
            'success' => true,
            'code' => 0,
            'message' =>xphp_get_lang('WEB_KUBE_CLUSTER_GET_NODE_NETWORK_SUCCESS'),
            'data' => array(),
        );
      
        $portList = array();
        $sql = "select bnn.network_uuid, bnn.node_uuid, bnn.alias_name, bnn.ip, bnn.port, bnn.type, bnn.network_order , bn.node_type
from bd_node_network bnn 
left join bd_node bn on bn.node_uuid = bnn.node_uuid 
where bn.node_type = 1 and bnn.ip != '' ";
        $result = $this->dbSelect($sql);
        foreach ($result as $one){
            $portList[] = array(
                'ip' => $one['ip'],
                'port' => $one['port'],
                "ip_port" => $one['ip'].":".$one['port']
            );
        }
        $resultInfo['data'] = $portList;
        return $resultInfo;
    }


     















}
