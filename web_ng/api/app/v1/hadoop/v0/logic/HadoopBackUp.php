<?php

namespace app\v1\hadoop\v0\logic;
use app\v1\common\logic\Backup;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Tenant;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\FsOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Index;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\hadoop\v0\service\Service;
use xphp\BLLHandler;


/**
 * note          hadoop 备份管理 logic
 * @author       lilingyu@vinchin.com
 * @date         2023/10/19 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class HadoopBackUp extends Backup
{
    /**
     * 获取备份集群树
     */
    public function getBackupZtree($params){
        //获取搜索值
        $keyword =  v1_escape_wildcard($params['content']);
        //是否是恢复时所用
        $recoverflag = $params['recoverflag'];
        $editUuid = $params['edit_uuid'];
        //获取数据
        $concatSql = ' where ';
        $sql  = "select hc.hadoop_cluster_uuid, hc.hadoop_cluster_name, hc.status, hc.authorization from hadoop_cluster as hc ";
        if(v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['HADOOP'], 'hc.hadoop_cluster_uuid');
          
            $sql .= $concatSql ." ($resourceUuidSql)";
            $concatSql = ' and ';
        }
        //关键字
        if(!empty($keyword)){
            //如果搜索的是IP  先通过hadoop_namenode这张表找出集群名 然后在匹配集群名
            $nodesql = "select hadoop_cluster_uuid from hadoop_namenode where namenode_ip like '%".$keyword."%'";
            $noderesult = $this->dbSelect($nodesql);
            if(!empty($noderesult)){
                foreach($noderesult as $each){
                    $clusteruuid[] = '"'.$each['hadoop_cluster_uuid'].'"';
                }
                $clusteruuid = implode(",",$clusteruuid);
                $sql .= $concatSql." hc.hadoop_cluster_uuid in (".$clusteruuid.") or hc.hadoop_cluster_name like '%".$keyword."%'";
            }else{
                $sql .= $concatSql." hc.hadoop_cluster_name like '%".$keyword."%'";
            }
        }
        $info = array();
        //处理数据
        $result = $this->dbSelect($sql);
        if(empty($result)){
            return $info;
        }
        $editHadoopUuid = [];
        if (!empty($editUuid)) {//修改任务
            $sqlHadoop = "SELECT agent_uuid FROM bd_task_agent_list WHERE task_uuid = ?";
            $dataHadoop = $this->dbSelect($sqlHadoop, array($editUuid));
            $editHadoopUuid = array_column($dataHadoop, 'agent_uuid');
        }
        foreach($result as $each){
            //修改名字
            if($each['status'] == 3){
                $authorizationonlineinfo = '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')';
            }else if($each['status'] == 4){
                $authorizationonlineinfo = '(' . xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL') . ')';
            }else {
                $authorizationonlineinfo = '';
            }
            //查找集群下的节点
            $noderesult =  $this -> getClusterNode($each['hadoop_cluster_uuid']);
            $nodelist =  array();
            $nodestr = '';
            foreach($noderesult as $node){
                $nodelist[] =  $node['namenode_ip'];
            }
            $nodestr = implode(" ",$nodelist);
            $chk = in_array(
                $authorizationonlineinfo,
                [
                    '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')'
                ]
            );
            $info[]  = array(
                'id' => $each['hadoop_cluster_uuid'],
                'pId' => "-1",
                'name' => $authorizationonlineinfo.$each['hadoop_cluster_name']."(".$nodestr.")",
                'title' => implode("\n",$nodelist),
                'isParent' => false,
                'uuid'=> $each['hadoop_cluster_uuid'],
                'icon' =>'./img/hadoop/hadoop.png',
                'nocheck' => false,
                 //是否可以被选中
                'chkDisabled' => $chk,
                'chk_disabled' => $chk,
                'type' => 0,
                'checked' => in_array($each['hadoop_cluster_uuid'], $editHadoopUuid) ?? false,
                'event_type' => 'hadoop_cluster'
            );
        }
        return $info;
    }
    /**
     * 获取集群下面的节点列表
    */
    public function getClusterNode($params){
         //查找集群下的节点
         $sqlnode = "select namenode_ip, status from hadoop_namenode where hadoop_cluster_uuid  = ? ";
         $sqlparams = array($params);
         $noderesult =  $this->dbSelect($sqlnode,$sqlparams);
         return $noderesult;
    }
    
    /**
     * 获取集群文件树(创建备份任务的时候用)
     */
    public function getBackupFileZtree($params){
        $parent_dir = $params['fetch_root_dir'];
        $cluster_uuid = $params['hadoop_cluster_id'];
        $edit_flag =  $params['edit_flag'];
        $apply_path_list = $params['apply_path_list']; //用于应用到其他客户端
        $filelist = array();//用于保存修改文件列表
        //获取当前集群备份的文件路径
        if($edit_flag){
            $taskuuid = $params['taskuuid'];
            if(!empty($apply_path_list)){
                $data = $apply_path_list;
            }else{
                $sql = "select path_name, path_type from fs_path_list where agent_uuid = ? and task_uuid = ? ";
                $data = $this->dbSelect($sql, array($cluster_uuid,$taskuuid));
            }
            foreach ($data as $d){
                //截取文件信息
                $info = explode('/', $d['path_name']);
                $path = "";
                //获取已选择的文件信息列表
                foreach ($info as $key=>$i){
                    if($key == count($info) - 1){//不是路径最后一层就拼接 /
                        if($d['path_type'] == 1){//是最后一层判断是否是文件，文件不用在最后拼 /
                            $path .= $i;
                        }else{
                            continue;
                        }
                    }else{
                        $path .= $i."/";
                    }
                    if (!in_array($path, $filelist)){
                        $filelist[] = $path;
                    }
                }
            }
        }
        $info =  array(
            "result" => true,
            "data" => array(),
            "msg" => "",
            "errorCode" => 0,
        );
        $tree =  array();
        //先写第一次加载的情况
        if($parent_dir == ""){
            //获取集群名
            $sql = "select hadoop_cluster_name from hadoop_cluster where hadoop_cluster_uuid = ? ";
            $result = $this->dbSelect($sql, array($cluster_uuid));
            $clustername = $result[0]['hadoop_cluster_name'];
            //集群那一层级
            // $tree[] =  array(
            //     "icon" => "./img/hadoop/hadoop.png",
            //     "iconClose" => "./img/hadoop/hadoop.png",
            //     "iconOpen"=> "./img/hadoop/hadoop.png",
            //     "id" => $cluster_uuid,
            //     "isParent" => true,
            //     "name" => $clustername,
            //     "nocheck" => false,
            //     "pId" => "-1",
            //     "title" => $clustername,
            //     "type" => 1,
            //     "clusteruuid"=> $cluster_uuid,
            //     "checked"=> false,
            //     "open" => false,
            //     "code_type"=>1,
            //     'more' => false,
            //     "event_type" => "hadoop_cluster",
            // );
            //根目录那一层级
            $tree[] = array(
                "icon" => "./img/fs/wenjianjia.png",
                "icon_open" => "./img/fs/wenjianjia.png",
                "icon_close" => "./img/fs/wenjianjia.png",
                "id" => "/",
                "isParent" => true,
                "name"=> "(/)",
                "nocheck" => false,
                "pId"=> "-1",
                "title" => "/",
                "type" => 3,
                "checked" => false,
                "open"=> false,
                "clusteruuid"=> $cluster_uuid,
                "code_type" => 1,
                "filepath" => "/",
                "filetype"=> 2, //1文件 2 文件夹 3 磁盘,
                'more' => false,
                "event_type" => "hadoop_cluster",
            );
            $info['data']['file_nodes'] = $tree;
            $info['data']['finish_flag'] = 1; //文件是否完成 1完成 ，2未完成
            //如果是修改 第一次请求需要加载数据
            if($edit_flag){
                $params['dir'] = $params['fetch_root_dir'];
                $params['pid'] = $params['fetch_root_dir'];
                $list = $this->getFileDirTree($params);
                $info['data']['finish_flag'] =  $list['finish_flag'];
                $info['data']['file_nodes'] =  $list['file_nodes'];
            }
        }else{
            //获取该集群下的目录
            //获取参数
            $data = array();
            $data['target_uuid']  = $cluster_uuid;
            $data['limit_count']  = $params['limit_count'];
            $data['search_file_name'] = '';
            $data['dir_path']  = htmlspecialchars_decode($parent_dir);
            $data['start_after'] = $params['start_after'];
            $data['code_type']=  2;
            $data['search_index']  = intval($params['start']) ;
            $data['module_type'] = xphp_get_config('module', 'MODULE_TYPE')['FS'];
            $data['submodule_type'] = xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'];
            //获取节点
            $nodeuuid = (new Node())->getLocalNodeUUID();
            $mbResult = $this->service()->getBackupFileZtree($nodeuuid, $data);
            $result = $mbResult['result'];
            $msg = $mbResult['msg'];
            if(!$result){
                $opName =  'FS_PRIVATE_OPERATION_CODE_TARGET_DIR_QUERY';
                $operate = (new FsOpcode())->getOpcodeDes($opName);
                return $this->muOpResult($result, $operate, $msg,'', $mbResult['errorCode']);
            }
            $info['result'] = $mbResult['result'];
            $info['errorCode'] = $mbResult['errorCode'];
            $list =  $mbResult['msg']['item_list'];
            //构建文件目录树
            foreach($list as $d){
                $checked = false;
                $open = false;
                //检查是否在修改列表里面
                if(in_array($d['item_path'], $filelist)){
                    $checked = true;
                    //从修改文件列表中 排除已经选中的节点
                    foreach ($filelist as $key=>$file){
                        if($file == $d['item_path']){
                            unset($filelist[$key]);
                        }
                    }
                }
                //检查节点是否需要展开
                $flag = false;
                foreach ($filelist as $l){
                    //strpos返回第二个字符串在第一个字符串中第一次出现的位置，如果没有找到字符串则返回 FALSE
                    if(strpos($l, $d['item_path']) !== false){ 
                        $flag =true;
                        $open = true;
                    }
                }
                $name = $d['item_name'];
                $tree[] =  array( 
                    "icon" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                    "icon_open" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                    "icon_close" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                    "id" => $d['item_path'],
                    "isParent" =>  $d['item_type'] == 2,
                    "name" => $name,
                    "nocheck" => false,
                    "pId" => $parent_dir,
                    "title" => $d['item_path'],
                    "type" => 3,
                    "checked" => $checked,
                    "open"=> $open,
                    "clusteruuid"=> $cluster_uuid,
                    "code_type" => $d['code_type'],
                    "filepath" => $d['item_path'],
                    "filetype"=> $d['item_type'],
                    'more' => false,
                    "event_type" => "hadoop_cluster",
                );
                //获取要展开节点的子节点
                if($checked && $d['item_type'] != 1){
                    if(!$flag) continue;
                   
                }

            }
            if(intval($mbResult['msg']['is_search_finish']) == xphp_get_config('app', 'FLAG')['UNSET']){
                //如果没有显示完全 则显示加载更多
                $more  = array(
                    "isParent" => false,
                    "name" => xphp_get_lang('WEB_FILE_MORE'),
                    "nocheck" => true,
                    "pId"=>$parent_dir,
                    "title" => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    "clusteruuid"=> $cluster_uuid,
                    "more" => true,
                    "next_start" => $mbResult['msg']['start_after'], //未完成时 下一个开始的文件路径
                    "next_index" => $mbResult['msg']['current_next_index'],       //从哪个位置开始加载
                    "event_type" => "hadoop_cluster",
                );
                $tree[] =  $more;
            }
            $info['data']['file_nodes'] = $tree;
            $info['data']['finish_flag'] = $mbResult['msg']['is_search_finish'];
        }
        return $info;
    }

    //--------------------------修改任务开始-----------------------------

    //得到代理端文件树
    //只有修改会走这个函数（修改时候调用 入口）
    public function getFileDirTree($params = [])
    {
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $clusteruuid = $params['hadoop_cluster_id'];
        $taskuuid = $params['edit_uuid'];
        $parent_dir = $params['fetch_root_dir'];
        $apply_path_list = $params['apply_path_list']; //用于应用到其他客户端
        $filelist = array();//用于保存修改文件列表
        $fileNodes = array();
        if($parent_dir == ""){
            //获取集群名
            $sql = "select hadoop_cluster_name from hadoop_cluster where hadoop_cluster_uuid = ? ";
            $result = $this->dbSelect($sql, array($clusteruuid));
            $clustername = $result[0]['hadoop_cluster_name'];
            //集群那一层级
            // $fileNodes[] =  array(
            //     "icon" => "./img/hadoop/hadoop.png",
            //     "iconClose" => "./img/hadoop/hadoop.png",
            //     "iconOpen"=> "./img/hadoop/hadoop.png",
            //     "id" => $clusteruuid,
            //     "isParent" => true,
            //     "name" => $clustername,
            //     "nocheck" => false,
            //     "pId" => "-1",
            //     "title" => $clustername,
            //     "type" => 1,
            //     "clusteruuid"=> $clusteruuid,
            //     "checked"=> true,
            //     "open" => true,
            //     "code_type"=>1,
            //     'more' => false,

            // );
            //根目录那一层级
            $fileNodes[] = array(
                "icon" => "./img/fs/wenjianjia.png",
                "icon_open" => "./img/fs/wenjianjia.png",
                "icon_close" => "./img/fs/wenjianjia.png",
                "id" => "/",
                "isParent" => true,
                "name"=> "(/)",
                "nocheck" => false,
                "pId"=> "-1",
                "title" => "/",
                "type" => 3,
                "checked" => true,
                "open"=> true,
                "clusteruuid"=> $clusteruuid,
                "code_type" => 1,
                "filepath" => "/",
                "filetype"=> 2, //1文件 2 文件夹 3 磁盘,
                'more' => false
            );
        }
        //获取当前代理端文件备份路径列表
        //只有修改会走这里
        if (!empty($apply_path_list)) {
            $data = $apply_path_list;
        }else{
            $sql = "select path_name, path_type from fs_path_list where agent_uuid = ? and task_uuid = ? ";
            $data = $this->dbSelect($sql, array($clusteruuid, $taskuuid));
        }
       
        // $sql = "select path_name, path_type from fs_path_list where agent_uuid = ? and task_uuid = ? ";
        // $data = $this->dbSelect($sql, array($clusteruuid, $taskuuid));
        foreach ($data as $d) {
            if ($d['path_type'] == 1 || $d['path_type'] == 2) {//文件或文件夹
                //截取文件信息
                $info = explode('/', $d['path_name']);
                $path = '';
                //获取已选择的文件信息列表
                foreach ($info as $key => $i) {
                    if ($key == count($info) - 1) {//不是路径最后一层就拼接 /
                        if ($d['path_type'] == 1) {//是最后一层判断是否是文件，文件不用在最后拼 /
                            $path .= $i;
                        } else {
                            continue;
                        }
                    } else {
                        $path .= $i . '/';
                    }
                    if (!in_array($path, $filelist)) {
                        $filelist[] = $path;
                    }
                }
            } else {//磁盘
                if (!in_array($d['path_name'], $filelist)) {
                    $filelist[] =  $d['path_name'];
                }
            }
        }
        if(count($filelist) ==  1 && $filelist[0] == '/' && $parent_dir == ''){
            //这是备份根目录的情况
            $fileNodes[0]['open'] =  false;
            $list['file_nodes'] = $fileNodes;
            return $list;
        }
        //这是修改的时候点击另一个集群的情况
        if( $parent_dir == '' && empty($filelist)){
            for ($i=0; $i < count($fileNodes) ; $i++) { 
                $fileNodes[$i]['checked'] =  false;
                $fileNodes[$i]['open'] =  false;
            }
            $list['file_nodes'] = $fileNodes;
            return $list;
        }
        $allresult = $this->getFileDir($params);
        $result = $allresult['result'];
        $nodeOpcode = new NodeOpcode();
        $operate = $nodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $allresult['errorCode']);
        }
        $result = $allresult['msg'];
        $list = array(
            "re" => true,                                   //成功标志,方面前端统一处理
            "finish_flag" => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            "search_index" => $result['current_next_index'],   //未完成时,下一个开始位置
            "start_after" => $result['start_after'],  //未完成时,下一个开始名字
        );
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            $checked = false;
            $open = false;
            //检查节点是否在修改列表里
            if (in_array($d['item_path'], $filelist)) {
                $checked = true;
                //从修改文件列表中 排除已经选中的节点
                foreach ($filelist as $key => $file) {
                    if ($file == $d['item_path']) {
                        unset($filelist[$key]);
                    }
                }
            }
            //检查节点是否需要展开
            $flag = false;
            foreach ($filelist as $l) {
                //strpos返回第二个字符串在第一个字符串中第一次出现的位置，如果没有找到字符串则返回 FALSE
                if (strpos($l, $d['item_path']) !== false) {
                    $flag = true;
                    $open = true;
                }
            }
            $name = $d['item_name'];
            $fileNodes[] = array(
                "id" => $d['item_path'],
                "pId" => $parent_dir== "" ||  $parent_dir== null ? '/':$parent_dir,
                "name" => $name,
                "title" => $name,
                "isParent" =>  $d['item_type'] == 2,
                "icon" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                "icon_open" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                "icon_close" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                "open"=> $open,
                "clusteruuid"=> $clusteruuid,
                "nocheck" => false,
                "type" => 3,
                "checked" => $checked,
                "filepath" => $d['item_path'],
                "filetype"=> $d['item_type'],
                "code_type" => $d['code_type'],
                "more" => false
            );
            //获取要展开节点的子节点(文件夹会展开)
            if ($checked && $d['item_type'] == 2) {
                if (!$flag) {
                    continue;
                }
                //获取需要展开的节点和加载更多节点
                $params = array(
                    'start' => 0,
                    'limit_count' => 40,
                    'filename' => $filename,
                    'dir' => $d['item_path'],
                    'hadoop_cluster_id' => $clusteruuid,
                    'pid' => $d['item_path'],
                    'start_after'=>"",
                );
                //展开子节点
                $sonList = $this->getOnLoadTree($filelist, $params, $d['item_path']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }
         //添加加载更多的节点
         if(intval($result['is_search_finish']) == xphp_get_config('app', 'FLAG')['UNSET']){
            //如果没有显示完全 则显示加载更多
            $more  = array(
                "isParent" => false,
                "name" => xphp_get_lang('WEB_FILE_MORE'),
                "nocheck" => true,
                "pId"=> $parent_dir== "" ||  $parent_dir== null ? '/':$parent_dir,
                "title" => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                "clusteruuid"=> $clusteruuid,
                "more" => true,
                "next_start" =>  $result['start_after'], //未完成时 下一个开始的文件路径
                "next_index" => $result['current_next_index'],    //从哪个位置开始加载
            );
            $fileNodes[] =  $more;
        }
        
        $list['file_nodes'] = $fileNodes;
        return $list;
    }
    //代理端文件列表（修改的时候用 getFileDirTree  getFileDirSonTree调用）
    private function getFileDir(array $params): array
    {
        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        $start = intval($params['start']);
        $limit = intval($params['limit_count']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'] == ''|| $params['dir'] == null ? '/' : $params['dir'];
        $pid = $params['pid'] == '' || $params['dir'] == null ? '/' : $$params['pid'];          //父节点ID
        $agentUUID = $params['hadoop_cluster_id'];
        $this->paramsCheck($agentUUID);
        $codetype = 2; // 编码类型
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        $submoduleType = xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'];
        $startAfter = $params['start_after'];
        $msg = array(
            'target_uuid' => $agentUUID,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'start_after' => $startAfter,
            'dir_path' => htmlspecialchars_decode($dir),
            'code_type' => $codetype,
            'search_index' => $start,
            'module_type' => $moduleType,
            'submodule_type'  => $submoduleType,
        );  
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = (new Service)->getBackupFileZtree($nodeuuid, $msg);
        $mbResult['msg']['pid'] = $pid;
        return $mbResult;
    }
    //递归获取加载子节点(修改的时候用)
    private function getOnLoadTree($list, $params, $path)
    {
        $fileNodes = array();
        //获取子节点
        $data = $this->getFileDirSonTree($params);
        $fileData = $data['file_nodes'];
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($list as $l) {
            $index = strripos($l, '/');
            //文件夹
            if ($index == strlen($l) - 1) {
                $str = substr($l, 0, strripos($l, '/'));
                $str = substr($str, 0, strripos($str, '/'));
            } else {
                //文件
                $str = substr($l, 0, strripos($l, '/'));
            }
            $str .= '/';
            if ($str == $path) {
                $info[] = $l;
            }
        }
        //获取展开子节点
        foreach ($fileData as $key => $d) {
            $checked = false;
            if (in_array($d['filepath'], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$list
                foreach ($list as $key => $file) {
                    if ($file == $d['filepath']) {
                        unset($list[$key]);
                    }
                }
                //排除$info
                foreach ($info as $key => $i) {
                    if ($i == $d['filepath']) {
                        unset($info[$key]);
                    }
                }
            }

            if (count($info) != 0 && $d['more']) {
                continue;
            }
            //检查节点是否需要展开
            $flag = false;
            foreach ($list as $l) {
                if (strpos($l, $d['filepath']) !== false) {
                    $flag = true;
                    // if ($d['filetype'] != 1) {
                    //     //文件夹
                    //     $d['open'] = true;
                    // }
                    if ($d['filetype'] == 2) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            if ($checked && $d['filetype'] == 2) {
                //判判断$list存在父节点是c/1/2
                if (!$flag) {
                    continue;
                }
                $data = array(
                    'start' => 0,
                    'limit_count' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'hadoop_cluster_id' => $params['hadoop_cluster_id'],
                    'pid' => $d['filepath'],
                    'start_after'=>"",
                    'code_type'=> $d['code_type'],
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['filepath']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
            
        }
        // var_dump("aaa",$fileData[count($fileData) - 1]);
        //获取加载更多
        if (count($info) != 0 && $fileData[count($fileData) - 1]['more']) {
            //递归加载更多
            $data = array(
                'start' => $fileData[count($fileData) - 1]['next_index'],
                'limit' => 40,
                'filename' => $fileData[count($fileData) - 1]['search_file_name'],
                'dir' => $params['dir'],
                'hadoop_cluster_id' => $params['hadoop_cluster_id'],
                'pid' => $params['dir'],
                'start_after'=>$fileData[count($fileData) - 1]['start_after'],
            );
            // var_dump("nextstart",$fileData[count($fileData) - 1]['next_index']);
            $moreNodes = $this->getMoreData($list, $data);
            $fileNodes = array_merge($fileNodes, $moreNodes);
            
        }
        // var_dump("filenodes--",$fileNodes[count($fileNodes) - 1]);
        return $fileNodes;
    }

    //获取代理端文件子树（修改的时候用）
    public function getFileDirSonTree($params = [])
    {
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $allresult = $this->getFileDir($params);
        $result = $allresult['result'];
        $nodeOpcode = new NodeOpcode();
        $operate = $nodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult($result, $operate, '', '', $allresult['errorCode']);
        }
        $result = $allresult['msg'];
        $list = array(
            're' => true,                                   //成功标志,方面前端统一处理
            'finish_flag' => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            'search_index' => $result['current_next_index'],   //未完成时,下一个开始位置
            'start_after' => $result['start_after'],  //未完成时,下一个开始名字
        );
        $parent_dir = $params['pid'];
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            if (!empty($params['dir'])) {
                $fileNodes[] = array(
                    'id' => $d['item_path'],
                    'pId' => $parent_dir== "" ||  $parent_dir== null ? '/':$parent_dir,
                    'name' => $filename,      //文件名或者磁盘名
                    'title' => $filename,
                    'isParent' =>  $d['item_type'] == 2,
                    'open' => false,
                    'clusteruuid' => $params['hadoop_cluster_id'],
                    'nocheck' => false,
                    "icon" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                    "icon_open" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                    "icon_close" => $d['item_type'] == 2 ?  './img/fs/wenjianjia.png' : './img/fs/wenjian.png',
                    "type" => 3,
                    'more' => false,
                    'code_type' => $d['code_type'],
                    "filetype"=> $d['item_type'],
                    'filepath' => $d['item_path']
                );
            }
        }
        //判断是否已经显示完全
        if (intval($result['is_search_finish']) == xphp_get_config('app', 'FLAG')['UNSET']) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                // "id" => $pid==0?0:$pid.'/',
                "isParent" => false,
                'pId' =>  $parent_dir== "" ||  $parent_dir== null ? '/':$parent_dir,
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'clusteruuid' => $params['hadoop_cluster_id'],
                'nocheck' => true,
                'more' => true,
                'next_index' => $result['current_next_index'],       //从哪个位置开始加载
                'search_file_name' => $result['search_file_name'],          //从哪个目录开始加载
                'dir_path' => $params['dir'],
                "start_after" => $result['start_after'], //未完成时 下一个开始的文件路径
                "next_start" => $result['start_after'], //未完成时 下一个开始的文件路径
                //当前目录名
                // "filepath" =>$result['pid'].$result['search_file_name'],

            );
            $fileNodes[] = $more;
        }
        $list['file_nodes'] = $fileNodes ?? [];
        return $list;
    }

    //递归获取加载更多（修改时候用）
    private function getMoreData(array $list, array $params): array
    {
        $fileNodes = array();
        $data = $this->getFileDirSonTree($params);
        
        $moreData = $data['file_nodes'];
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($list as $l) {
            $index = strripos($l, '/');
            //文件夹
            if ($index == strlen($l) - 1) {
                $str = substr($l, 0, strripos($l, '/'));
                $str = substr($str, 0, strripos($str, '/'));
            } else {
                //文件
                $str = substr($l, 0, strripos($l, '/'));
            }
            $str .= '/';
            if ($str == $params['dir']) {
                $info[] = $l;
            }
        }   
        foreach ($moreData as $key => $d) {
            $checked = false;
            if (in_array($d['filepath'], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$info
                foreach ($list as $key => $i) {
                    if ($i == $d['filepath']) {
                        unset($list[$key]);
                    }
                }
                foreach ($info as $key => $i) {
                    if ($i == $d['filepath']) {
                        unset($info[$key]);
                    }
                }
            }
            if (count($list) != 0 && $d['more']) {
                continue;
            }
            //检查节点是否需要展开
            $flag = false;
            foreach ($list as $l) {
                if (strpos($l, $d['filepath']) !== false) {
                    $flag = true;
                    // if ($d['type'] != 1) {
                    //     //文件夹
                    //     $d['open'] = true;
                    // }
                    if ($d['filetype'] == 2) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            // if ($checked && $d['type'] != 1) {
            if ($checked && $d['filetype'] == 2) {
                //判判断$list存在父节点是c/1/2
                if (!$flag) {
                    continue;
                }
                $data = array(
                    'start' => 0,
                    'limit_count' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'hadoop_cluster_id' => $params['hadoop_cluster_id'],
                    'pid' => $d['filepath'],
                    'start_after'=>"",
                    'code_type'=> $d['code_type'],
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['filepath']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }
        $count = count($moreData);
        if ($moreData[$count - 1]['more']) {
            //加载更多
            if(count($info) != 0){
                $data = array(
                    'start' => $moreData[$count - 1]['next_index'],   //依次累加
                    'limit_count' => 40,
                    'filename' => $moreData[$count - 1]['search_file_name'],
                    'dir' => $params['dir'],
                    'hadoop_cluster_id' => $params['hadoop_cluster_id'],
                    'pid' => $params['dir'],
                    'start_after'=>$moreData[$count - 1]['start_after'],
                );
                $moreNodes = $this->getMoreData($list, $data);
                $fileNodes = array_merge($fileNodes, $moreNodes);
            }else{
                $more =  array(
                    "isParent" => false,
                    "name" => xphp_get_lang('WEB_FILE_MORE'),
                    "nocheck" => true,
                    "pId"=> $moreData[$count - 1]['pId'],
                    "title" => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    "clusteruuid"=> $moreData[$count - 1]['clusteruuid'],
                    "more" => true,
                    "next_start" => $moreData[$count - 1]['start_after'], //未完成时 下一个开始的文件路径
                    "next_index" => $moreData[$count - 1]['next_index']
                );
                $fileNodes[] = $more;
            }
          
        }
        return $fileNodes;
    }
    //--------------------------修改任务结束-----------------------------


    /**
     * 获取备份任务名
     */
    public function getBackupTaskName()
    {
        return $this->getValidTaskName(xphp_get_lang('WEB_HADOOP_BACKUP_TASK_NAME'));
    }

    /**
     * 获取可用的任务名
     * @param string $taskName 任务名前缀
     * @return string
     */
    public function getValidTaskName($taskName)
    {
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if(empty($data) && empty($data1)){
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }
    

    /**
     * 创建备份任务
     */
    public function createBackupJob($params) {
        // $paramsobj  = json_decode($params['data']) ;
        // $params = $this->object_to_array($paramsobj);
        // 检查授权是否到期
        $this->licenseCheck();
        //任务名
        $taskname = htmlspecialchars_decode($params['taskName']);
        //全局策略uuid  (没有为空值)
        $globalID = $params['strategy_group_uuid'] ?? '';
        //模块类型
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            $info = array();
            foreach ($params['src_info']['cluster_uuid_list'] as $each) {
                if (!in_array($each ,$info)) {
                    $info[] = $each;
                }
            }
            Tenant::instance()->checkTenantAuth(xphp_get_config('module')['MODULE_TYPE']['HADOOP'], $info, '');
        }
        //组合备份方式完备差备等
        $timestrategylist = $this->groupBackupTimeList($params['backup_info'], $globalID);
        //组合保留策略
        $reserverstrategy = $this->groupReserverStrategy($params['high_info']['reserve']);
        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['high_info']['transfer']);
        //组合存储策略
        $storagestrategy = $this->groupStorageStrategy($params['high_info']['store']);
        //组合节点信息
        $nodeInfo = (new BLLHandler())->groupBackupNodeInfo($params['high_info']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $taskname,
            $moduletype,
            $timestrategylist,
            $reserverstrategy,
            $transportstrategy,
            $storagestrategy,
            $nodeInfo,
            $params['backup_info']['type']
        );
        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] =$this->groupBackupFS($params['src_info']['file_info']);
        $pfMsg['snap_shot_flag'] = $params['high_info']['newstr']['silentsnapshotcheck'] ? 1 : 2;//快照
        $pfMsg['thread_num'] = $params['high_info']['newstr']['backup_thread_num'];//传输线程数量
        $pfMsg['scan_thread_num'] = $params['high_info']['newstr']['scan_thread_num'];//扫描线程数量
        $pfMsg['scan_file_num'] = $params['high_info']['newstr']['scan_file_num'];//扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['high_info']['newstr']['wildcard_list']);//通配符
        //限速策略
        $pfMsg['speed_limit_strategy_list'] = $this->groupTaskSpeedList($params['speedInfo'], '');
        $pfMsg['speed_limit_strategy'] =  $this->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $globalID;
        $pfMsg['file_archive_flag'] = 2;//归档
        $pfMsg['skip_file_alarm_flag'] =  v1_parse_bool_to_flag($params['high_info']['skip_file_alarm_flag']) ;//归档
        $pfMsg['permission_operate_flag'] =  v1_parse_bool_to_flag ($params['high_info']['permission_operate_flag']);//文件权限备份
        $pfMsg['skip_file_alarm_min_num'] = $params['high_info']['skip_file_alarm_min_num'];//归档
        $pfMsg['skip_file_alarm_min_ratio'] = $params['high_info']['skip_file_alarm_min_ratio'];//归档
        $pfMsg['group_list']  = $this->getGroupList($params['src_info']['file_info']);;
        //得到备份节点ip (没有 传空值)
        $pfMsg['backup_server_ip'] = '';
        //得到指定网段 (没有 传空值)
        $pfMsg['transport_ip_segment'] = '';
        //得到传输代理
        $pfMsg['appliance_uuid'] = $params['applianceuuid'];
        $pfMsg['agent_uuid'] = $params['applianceuuid'];
        $pfMsg['agent_pool_uuid'] = $params['appliance_pool_uuid'];
        //得到安全策略
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy'],$params['high_info']['node']['storageuuid']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $this->groupRetryStrategy($params['retry_strategy']);
        //得到过载保护
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_info']['ignore_resource_limiting_flag']);
        //submodule
        $pfMsg['submodule_type'] = xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'];
        $mbResult = $this->service()->createBackupJob($nodeInfo['node_uuid'], $pfMsg);
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 得到备份的文件列表
     * @param unknown $filelists
     */
    private function groupBackupFS($filelists){
        $fileList = array();
        foreach ($filelists as $file){
            $fileList[] = array(
                'path_type' => strval($file[0]),    //文件类型
                'path_name' => htmlspecialchars_decode($file[1]),             //文件路径
                'agent_uuid' => $file[2],
                'group_uuid' => $file[3],
                'code_type' => $file[4],//编码类型
            );
        }
        return $fileList;
    }

    /**
     * 得到备份的对象存储列表
     * @param array $grouplists 分组列表
     * @param array $fileinfo   文件列表
     * @return array
     */
    private function getGroupList($fileinfo): array
    {
        $data = array();
        $agentList = array();
        foreach ($fileinfo as $i) {
            if (!in_array($i['2'], $agentList)) {
                array_push($agentList, $i['2']);
            }
        }
        $data[] = array(
            'group_uuid' => "",
            'agent_uuid' => $agentList,
        );
        return $data;
    }


     /**
     * 得到备份的通配符相关信息
     * @param array $wildcard 列表
     * @return array
     */
    private function getWildCardList(array $wildcard): array
    {
        $data = [];
        foreach ($wildcard as $w) {
            //$w[1]是个数组 需要单独解码
            $decryw1 = [];
            foreach ($w[1] as $w1) {
                $decryw1[] = v1_decrypt_js_rsa($w1);
            }
            $data[] = array(
                'agent_uuid' => $w[0],
                'wildcard' => $decryw1,
                'wildcard_mode' => $w[2],
            );
        }
        return $data;
    }

    /**
     * 对象 转 数组
     *
     * @param object $obj 对象
     * @return array
     */
    private function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)$this->object_to_array($v);
            }
        }
    
        return $obj;
    }

    /**
     * 修改任务,得到备份任务的所有信息
     * @param unknown $params 参数
     * @return array 备份任务的所有信息
     */
    public function getBackupTaskInfo($params){
        $taskUUID = $params['taskuuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid,bt.strategy_group_uuid, bt.agent_uuid, bt.thread_num, bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,
        bt.node_pool_uuid, bt.storage_pool_uuid, bt.ignore_resource_limiting_flag,
        ft.level, ft.agent_group_uuid,ft.snap_shot_flag,ft.detail as ftdetail,ft.file_archive_flag,ft.skip_file_alarm_flag,ft.skip_file_alarm_min_num,ft.skip_file_alarm_min_ratio,
        ft.permission_operate_flag, ft.proxy_uuid ,brs.strategy_type, brs.number, brs.strategy_mode, 
        bts.encrypt_flag, bts.compress_flag,bts.network_uuid, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method,
        bss.compressed_flag, bss.compress_method, bss.encrypted_flag,bss.password,bss.password_auto_flag, bss.encrypt_method, 
        btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,btsc.backup_integrity_check_inc_error_policy 
        from bd_task bt, fs_task ft, bd_reserved_strategy brs, bd_transport_strategy bts, bd_storage_strategy bss, bd_task_safe_config btsc
        where bt.task_uuid = ft.task_uuid
        and bt.strategy_id = brs.strategy_id
        and bt.strategy_id = bts.strategy_id
        and bt.strategy_id = bss.strategy_id
        and bt.task_uuid = btsc.task_uuid
        and bt.task_uuid = ?";
            
        $data = $this->dbSelect($sql, array($taskUUID));
        $sqlwild = 'select group_uuid,agent_uuid,detail from bd_task_agent_list where task_uuid = ?';
        $sqlwilddata = $this->dbSelect($sqlwild, array($taskUUID));
        $info = array();
        if($data){
            $wildcardinfo= array();
            foreach($sqlwilddata as $d) { 
                $wildInfo = json_decode($d['detail'],true);
                if(empty($wildInfo)) {
                    $wildInfo = array(
                        "wildcard" => [],
                        "wildcard_mode" => "0",
                        "wildcard_real_length" => [],
                    );
                }
                $wildInfo['agent_uuid'] = $d['agent_uuid'];
                array_push($wildcardinfo,$wildInfo);
            }
            $clusterInfo = $this->getClusterList($taskUUID);
            $ftdetail = json_decode($data[0]['ftdetail'],true);
            // 查询存储池类型
            $storagePoolType = 0;
            if ($data[0]['storage_pool_uuid']) {
                $storagePoolData = $this->dbSelect("SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ", [$data[0]['storage_pool_uuid']]);
                $storagePoolType = $storagePoolData[0]['storage_pool_type'];
            }
            $info =  array(
                //任务UUID
                'taskuuid' => $taskUUID,
                //策略uuid
                "strategy_group_uuid" => $data[0]['strategy_group_uuid'],
                //任务名
                'taskname' => $data[0]['task_name'],
                // 集群列表
                // 'clusterList' => $clusterInfo[0]['agentList'],
                'checkedClusterList' => $clusterInfo,
                //level
                'level' => $data[0]['level'],
                //文件信息
                'fileinfo' => $this->getFileBackupFiles($taskUUID),
                //节点信息
                'node' => array(
                    'nodeuuid' => $data[0]['node_uuid'],
                    'storageuuid' => $data[0]['storage_uuid'],
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    'storage_pool_type' => $storagePoolType,  // 需要返回存储池类别
                ),
                //appliance
                'applianceuuid' => $data[0]['proxy_uuid'],
                'agentuuid' => $data[0]['proxy_uuid'],
                'appliance_pool_uuid' => $this->getJobAgentPoolInfo($taskUUID),
                 //保留策略
                 'brs' => array(
                    'strategy_mode' => $data[0]['strategy_mode'],
                    'type' => $data[0]['strategy_type'],
                    'number' => $data[0]['number'],
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypt_flag']),
                    'compress' => v1_parse_flag_to_bool($data[0]['compress_flag']),
                    'network' => $data[0]['network_uuid'],
                    'reconnect_times' => intval($data[0]['reconnect_times']),
                    'reconnect_interval' => intval($data[0]['reconnect_interval']),
                    'encrypt_method' => intval($data[0]['transport_method']),
                ),
                //存储策略
                'bss' => array(
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    'password' =>  !v1_parse_flag_to_bool($data[0]['password_auto_flag']) ? base64_encode(v1_pt_pass_decrypt($data[0]['password'])) : $data[0]['password'],
                    'compress_method' => intval($data[0]['compress_method']),
                    'encrypt_method' => intval($data[0]['encrypt_method'])
                ),
                //时间策略
                'timestrategy' =>$this->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                'speedInfo' => $this->getSpeedStrategy($taskUUID),
                //高级策略
                'high' => array(
                    'thread_num' => $data[0]['thread_num'],
                    'snap_shot_flag' => v1_parse_flag_to_bool($data[0]['snap_shot_flag']),
                    'wild_card_info' => $wildcardinfo,
                    'scan_thread_num' => $ftdetail['scan_thread_num'],
                    'scan_file_num' => $ftdetail['scan_file_num'],
                    'file_archive_flag' => $data[0]['file_archive_flag'],
                    'skip_file_alarm_flag' => $data[0]['skip_file_alarm_flag'] == 1 ? true : false,
                    'skip_file_alarm_min_num' => $data[0]['skip_file_alarm_min_num'],
                    'skip_file_alarm_min_ratio' => $data[0]['skip_file_alarm_min_ratio'],
                    'permission_operate_flag' => $data[0]['permission_operate_flag'] == 1 ? true : false,
                    'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag'])
                ),
                //新增安全策略
                'safeStrategy' => array(
                    //获取worm开关
                    'worm_flag' => v1_parse_flag_to_bool($data[0]['worm_flag']),
                    //获取worm保护期限
                    'worm_protection_time' => intval($data[0]['worm_protection_time']),
                    //获取病毒是否开关
                    'virus_scan_flag' => v1_parse_flag_to_bool($data[0]['virus_scan_flag']),
                    //获取病毒检测配置
                    'virus_scan_config_list' => json_decode($data[0]['virus_scan_config_list'], true),  
                    //获取完整性效验开关
                    'integrity_check_flag' => v1_parse_flag_to_bool($data[0]['integrity_check_flag']),
                    //获取完整性校验数据
                    'integrity_check_config' => array(
                        //获取效验周期
                        'check_strategy' => intval($data[0]['integrity_check_strategy']),
                        //获取完全备份点异常
                        'full_error_policy' => intval($data[0]['backup_integrity_check_full_error_policy']),
                        //获取其他备份点异常
                        'inc_error_policy' => intval($data[0]['backup_integrity_check_inc_error_policy']),
                    ),
                ),
                //重试策略
                'retry_strategy' =>(new ExchangeJobInfo())->getRetryStrategy($taskUUID),
            );
        }
        return $info ?? [];
    }

    /**
     * 根据任务id得到备份的文件列表信息
     * @param unknown $taskuuid
     */
    private function getFileBackupFiles(string $taskuuid): array
    {
        $sql = "select path_name, path_type, agent_uuid,group_uuid,code_type from fs_path_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        foreach ($data as $d) {
            $filename = $this->getFileName(intval($d['path_type']), $d['path_name']);
            $info[] = array(
                'agent_uuid' => $d['agent_uuid'],
                'type' => $d['path_type'],
                'name' => $filename,
                'path' => $d['path_name'],
                'sclass' => $this->getFileClassName($d['path_type'], $filename, 's'),
                'code_type' => $d['code_type'],
            );
        }
        return $info;
    }


     /**
     * 获取集群列表信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getClusterList(string $taskuuid)
    {

        $sql = "select btal.agent_uuid,hc.status
                    from bd_task_agent_list btal,hadoop_cluster hc
                    where btal.task_uuid = ?
                      and btal.agent_uuid = hc.hadoop_cluster_uuid" ;
        $data = $this->dbSelect($sql, array($taskuuid));
        $list  = !empty($data) ? array_column($data, 'agent_uuid') : [];
        return $list;
    }

    /**
     * 修改备份任务
     * @param $params
     * @return string|void
     */
    public function editBackupJob($params)
    {
        // 检查授权是否到期
        $this->licenseCheck();
        $taskuuid = $params['taskuuid'];
        $taskname = htmlspecialchars_decode($params['taskName']);
        //全局策略uuid  (没有为空值)
        $globalID = $params['strategy_group_uuid'] ?? '';
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            $info = array();
            foreach ($params['src_info']['cluster_uuid_list'] as $each) {
                if (!in_array($each ,$info)) {
                    $info[] = $each;
                }
            }
            Tenant::instance()->checkTenantAuth(xphp_get_config('module')['MODULE_TYPE']['HADOOP'], $info, $taskuuid);
        }

        //组合备份方式完备差备等
        $timestrategylist = $this->groupBackupTimeList($params['backup_info'], $globalID);
        //组合保留策略
        $reserverstrategy = $this->groupReserverStrategy($params['high_info']['reserve']);
        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['high_info']['transfer']);
        //组合存储策略
        $storagestrategy = $this->groupStorageStrategy($params['high_info']['store']);
        //组合节点信息
        $nodeInfo = (new BLLHandler())->groupBackupNodeInfo($params['high_info']['node']);

        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $taskname,
            $moduletype,
            $timestrategylist,
            $reserverstrategy,
            $transportstrategy,
            $storagestrategy,
            $nodeInfo,
            $params['backup_info']['type']
        );

         //得到任务类型
         $pfMsg['task_uuid'] = $taskuuid;
         $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
         $pfMsg['backup_level'] = 1;
         $pfMsg['fs_path_list'] = $this->groupBackupFS($params['src_info']['file_info']);
         $pfMsg['snap_shot_flag'] = $params['high_info']['newstr']['silentsnapshotcheck'] ? 1 : 2;//快照
         $pfMsg['thread_num'] = $params['high_info']['newstr']['backup_thread_num'];//传输线程数量
         $pfMsg['scan_thread_num'] = $params['high_info']['newstr']['scan_thread_num'];//扫描线程数量
         $pfMsg['scan_file_num'] = $params['high_info']['newstr']['scan_file_num'];//扫描文件速度
         $pfMsg['wildcard_list'] = $this->getWildCardList($params['high_info']['newstr']['wildcard_list']);//通配符
        //限速策略
        // $pfMsg['speed_limit_strategy_list'] = $this->groupTaskSpeedList($params['speedInfo'], '');
        $pfMsg['speed_limit_strategy'] =  $this->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $globalID;
        $pfMsg['file_archive_flag'] = 2;//归档
        $pfMsg['skip_file_alarm_flag'] =  v1_parse_bool_to_flag($params['high_info']['skip_file_alarm_flag']) ;//归档
        $pfMsg['permission_operate_flag'] =  v1_parse_bool_to_flag ($params['high_info']['permission_operate_flag']);//文件权限备份
        $pfMsg['skip_file_alarm_min_num'] = $params['high_info']['skip_file_alarm_min_num'];
        $pfMsg['skip_file_alarm_min_ratio'] = $params['high_info']['skip_file_alarm_min_ratio'];
        $pfMsg['group_list']  = $this->getGroupList($params['src_info']['file_info']);;
        //得到备份节点ip (没有 传空值)
        $pfMsg['backup_server_ip'] = '';
        //得到指定网段 (没有 传空值)
        $pfMsg['transport_ip_segment'] = '';
        //得到传输代理
        $pfMsg['appliance_uuid'] = $params['applianceuuid'];
        $pfMsg['agent_uuid'] = $params['applianceuuid'];
        $pfMsg['agent_pool_uuid'] = $params['appliance_pool_uuid'];
         // 安全策略
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy'],$params['high_info']['node']['storageuuid']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $this->groupRetryStrategy($params['retry_strategy']);
        $pfMsg['submodule_type'] =  xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'];
        //得到过载保护
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_info']['ignore_resource_limiting_flag']);
        $mbResult = $this->service()->editBackupJob($nodeInfo['node_uuid'], $pfMsg);
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $result = $mbResult['result'];
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    
     /**
     * 获取任务全局限速策略列表信息
     * @param string $taskuuid
     */
    public function getSpeedGlobalStrategyInfo($taskuuid)
    {
        $sql = "select strategy_uuid,task_priority from bd_task_speed_limit_strategy where task_uuid = ? limit 1";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = [
            'level' => 1,
            'type' => 1,
            'speedInfo' => [],
        ];
        if (!empty($data)) {
            // 查询出全局限速策略
            $sql = "select * from bd_global_speed_limit_strategy where strategy_uuid = ? limit 1";
            $strategy = $this->dbSelect($sql, array($data[0]['strategy_uuid']));
            if (!empty($strategy)) {
                $info['level'] = $data[0]['task_priority']; // 任务级别
                $info['strategy_type'] = $strategy[0]['strategy_type']; // 限速类型
                $info['type'] = $strategy[0]['is_global'] ? 1 : 2; // 任务类型 选择策略还是自定义策略
                if ($info['type'] == 1) {
                    // 全局策略
                    $info['global_speed_limit'] = $strategy[0]['strategy_uuid'];
                } else {
                    $info['speedInfo'] = json_decode($strategy[0]['extra_info'], true);
                }
            }
        }
        return $info;
    }

    // /**
    //  * 根据策略id得到时间策略信息
    //  * @param int $strategyID
    //  */
    // public function getTimeStrategyInfo($strategyID){
    //     $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time
    //             from bd_time_strategy where strategy_id = ?";
    //     $data = $this->dbSelect($sql, array($strategyID));
    //     if (!$data) {
    //         // 手动启动
    //         return [
    //             'type' => 'manual',
    //             'data' => [],
    //         ];
    //     }
    //     $timeStrategyBackupType = $this->getTimeStrategyBackupType($strategyID);
    //     $info = [
    //         'type' => $timeStrategyBackupType,
    //         'data' => [],
    //     ];
        
    //     switch ($timeStrategyBackupType) {
    //         case 'oncetime':
    //             $info['data'] = $data[0]['start_time'];
    //             break;
    //         case 'strategy':
    //             $strategyData = array();
    //             $allBackupMode = array_column($data, 'mode');
    //             //可能有多个策略类型(每天,每周,每月)
    //             foreach ($data as $d){
    //                 if($d['roll_flag'] == xphp_get_config('app', 'FLAG')['SET']){
    //                     $rollInterval = $d['roll_interval'];
    //                     $endTime = $d['roll_end_time'];
    //                 }else{
    //                     $rollInterval = '3600';
    //                     $endTime = '23:59:59';
    //                 }
    //                 $strategyData[] = array(
    //                     'start_time' => $d['start_time'],
    //                     'roll_flag' => v1_parse_flag_to_bool($d['roll_flag']),
    //                     'roll_interval' => v1_sec_to_time($rollInterval),
    //                     'roll_end_time' => $endTime,
    //                     'mode' => !in_array(xphp_get_config('task', 'BACKUP_MODE')['FULL'], $allBackupMode) && xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL'] == $d['mode']
    //                         ? xphp_get_config('task', 'BACKUP_MODE')['PINCREMENTAL'] : $d['mode'],  // 没有完全备份就是永久增量
    //                     'strategy_type' => intval($d['strategy_type']),
    //                     'days' => $this->parseTimeStrategyDay($d['days']),
    //                     'frequency' => $this->parseTimeStrategyFrequency($d['strategy_type'], $d['days'])
    //                 );
    //             }
    //             $info['data'] = $strategyData;
    //             break;
    //         default:
    //             break;
    //     }
    //     return $info;
    // }

}