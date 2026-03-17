<?php

namespace app\v1\nas\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\NodeOpcode;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Index;
use app\v1\resources\v0\logic\Nas;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Tenant;

/**
 * note          NAS 备份管理 logic 
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class NasBackUp extends Backup
{
    /**
     * 得到所有挂载成功节点，用于备份第二步检测节点是否挂载
     * @param array $params 参数
     * @return array
     */
    public function getAllMountNode(array $params): array
    {
        $sql = 'select node_uuid from nas_mount_list where nas_uuid = ?';
        $data = $this->dbSelect($sql, array($params['nas_uuid']));
        return !empty($data) ? array_column($data, 'node_uuid') : [];
    }

    /**
     * 获取nas设备树
     * @param array $params 参数
     * @return array
     */
    public function getNasBackupTree($params = array()): array
    {
        $search = $params['keyword'];
        $timepointuuid = $params['timepoint_uuid'];
        $hostFlag = $params['host_flag'];
        $editUuid = $params['edit_uuid'];
        $sql = "select nsr.nas_name,nsr.nas_nickname,nsr.nas_uuid,nsr.share_path,nsr.ip,nsr.user_name,nsr.password,nsr.nas_status,nsr.mount_params,nsr.nas_version,nsr.port,nsr.nas_type,nsr.authorization_status,nsr.snapshot_flag, nsr.detail,
        nsr.vendor,nml.agent_uuid,nml.mount_point from nas_storage_resource nsr left join nas_mount_list nml on nsr.nas_uuid = nml.nas_uuid";
        //获取当前用户拥有的nas设备
        $concatSql = ' where ';
        if(v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['NAS'], 'nsr.nas_uuid');
            $sql .= $concatSql . " ({$resourceUuidSql})";
            $concatSql = ' and ';
        }
        if (!empty($search)) {
            $sql .= $concatSql . "(nsr.nas_nickname like '%" . $search . "%' or nsr.share_path like '%" . $search . "%' or nsr.ip like '%" . $search . "%')";
        }
        //恢复页面选择nas设备 只显示当前勾选的备份数据的节点挂载过的nas设备，防止用户勾选未挂载的设备导致恢复失败
        $nasuuidList = array();
        if (!empty($timepointuuid)) {
            //判断real_node_uuid是否是空
            $realNodeUuidSql = "select real_node_uuid from bd_backup_timepoint where timepoint_uuid = ?";
            $realNodeUuid = $this->dbSelect($realNodeUuidSql, array($timepointuuid));
            if (!empty($realNodeUuid[0]['real_node_uuid'])) {
                $sqlList = "select nml.nas_uuid,bbt.real_node_uuid from bd_backup_timepoint bbt left join nas_mount_list nml
                on bbt.real_node_uuid = nml.node_uuid where bbt.timepoint_uuid = ?";
            } else {
                $sqlList = "select nml.nas_uuid from bd_backup_timepoint bbt left join bd_storage_resource bsr
                on bbt.storage_uuid = bsr.storage_uuid left join nas_mount_list nml
                on bsr.node_uuid = nml.node_uuid where  bbt.timepoint_uuid = ?";
            }
            $nasdata = $this->dbSelect($sqlList, array($timepointuuid));
            foreach ($nasdata as $n) {
                $nasuuidList[] = $n['nas_uuid'];
            }
        }
        $data = $this->dbSelect($sql);
        $nodes = array();
        $ediNasUuid = [];
        if (!empty($editUuid)) {//修改任务
            $sqlNas = "SELECT nas_uuid FROM nas_task WHERE task_uuid = ?";
            $dataNas = $this->dbSelect($sqlNas, array($editUuid));
            $ediNasUuid = array_column($dataNas, 'nas_uuid');
        }
        $nas = array();
        $groupVendor = array();//厂商
        if (!empty($data)) {
            foreach ($data as $d) {
                $authflag = (new Nas())->checkSystemAuth();
                if ($authflag != 1 && $d['nas_status'] != 1) {
                    $authorization_status = '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')';
                } else if ($authflag == 1 && $d['nas_status'] != 1) {
                    $authorization_status = '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')';
                } else if ($authflag != 1 && $d['nas_status'] == 1) {
                    $authorization_status = '(' . xphp_get_lang('WEB_SYSTEM_LISENCE_UNAUTHORIZED') . ')';
                } else {
                    $authorization_status = '';
                }
                if ($hostFlag) { //恢复页面nas设备树
                    $chkDisabled = $authorization_status == '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')' ? true : false;
                    if (!in_array($d['nas_uuid'], $nasuuidList) && $hostFlag) { // 不是同样的nodeuuid的显示成禁用状态
                        // continue;
                        $chkDisabled = true;
                        // 有离线不显示未挂载
                        if ($authorization_status != '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')') {
                            $authorization_status .= '(' . xphp_get_lang('WEB_STORAGE_STATUS_UNMOUNT') . ')';
                        }
                    }
                } else {
                    $chkDisabled = $authorization_status == '' ? false : true;
                }
                //图标区分不同类型
                if ($d['nas_type'] == 6) {
                    $icon = "./img/nas/nfs.png";
                } else if ($d['nas_type'] == 7) {
                    $icon = "./img/nas/cifs.png";
                }
                if ($d['nas_nickname'] == $d['ip']) {
                    $name = $d['ip'] . '(' . $d['share_path'] . ')';
                } else {
                    $name = $d['ip'] . '(' . $d['nas_nickname'] . ')';
                }
                if (!in_array('vendor_' . $d['vendor'], $groupVendor)) {
                    array_push($groupVendor, 'vendor_' . $d['vendor']);
                    $nodes[] = array(
                        "id" => 'vendor_' . $d['vendor'],
                        "pId" => 0,
                        "name" => $this->getVendorDes($d['vendor']),
                        "isParent" => true,
                        "icon" => $this->getVendorIcon($d['vendor']),
                        "eventtype" => "vendor",
                        "nocheck" => true,
                    );
                }
                if (!in_array($d['nas_uuid'], $nas)) {
                    array_push($nas, $d['nas_uuid']);
                    $checked = in_array($d['nas_uuid'], $ediNasUuid) ? true : false;
                    $nodes[] = array(
                        "id" => $d['nas_uuid'],
                        "pId" => 'vendor_' . $d['vendor'],
                        "pid" => 'vendor_' . $d['vendor'],
                        "name" => htmlspecialchars_decode($authorization_status . $name),
                        "title" => $d['ip'],
                        "is_parent" => false,
                        "isParent" => false,
                        "uuid" => $d['nas_uuid'],
                        "agent_uuid" => $d['agent_uuid'],
                        "nocheck" => false,
                        "path" => $d['mount_point'],
                        "sharepath" => $d['share_path'],
                        'icon' => $icon ?? '',
                        "event_type" => "nas",
                        "chkDisabled" => $chkDisabled,
                        "chk_disabled" => $chkDisabled,
                        "authorization_status" => $this->checkSystemAuth(1),
                        "checked" => $checked,
                        'snapshot_flag' => $d['snapshot_flag'],
                        'detail' => json_decode($d['detail'], true),
                        'vendor' => $d['vendor'],
                    );
                }
            }
        }

        return $nodes;
    }

      /**
     * 获取厂商描述
     * @param int $type 厂商类型
     * @return boolean
     */
    public function getVendorDes($type)
    {
        $des = '';
        switch (intval($type)) {
            case 0:
                $des = '其他';
                break;
            case 1:
                $des = 'HUAWEI OceanStor Dorado';
                break;
            default:
                $des = '其他';
                break;
        }
        return $des;
    }

    /**
     * 获取厂商icon
     * @param int $type 厂商类型
     * @return boolean
     */
    public function getVendorIcon($type)
    {
        $icon = '';
        switch (intval($type)) {
            case 0:
                $icon = './img/s3/other-cloud.svg';
                break;
            case 1:
                $icon = './img/s3/huawei.svg';
                break;
            default:
                $icon = './img/s3/other-cloud.svg';
                break;
        }
        return $icon;
    }

    /**
     * // 代理端文件子树
     * @param array $params 参数
     * @return array
     */
    public function getNasSonTree(array $params): array
    {
        $allresult = $this->getNasDir($params);
        $agentuuid = $params['agent_uuid'];
        $taskuuid = $params['edit_uuid'];
        $nasuuid = $params['nas_uuid'];
        $filelist = $fileNodes = [];
        //获取当前代理端文件备份路径列表
        $newParent = false;
        if ($params['edit_uuid']) {
            $sql = "select path_name, path_type, code_type, agent_uuid, group_uuid from fs_path_list where task_uuid = ? and agent_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid, $agentuuid));
            if (!empty($data)) {
                $onceflag = true;//根目录只加载一次
                foreach ($data as $d) {
                    //判断是不是/,如果是并且nasuuid不为空，需新增的用于支持全选的父节点
                    if ($d['path_name'] == '/' && !empty($nasuuid) && $onceflag) {
                        $newParent = true;//用于判断新增的父节点下的子节点是否选中
                        $onceflag = false;
                        $fileNodes[] = array(
                            'id' => $params['dir'],
                            'pid' => 0,
                            'pId' => 0,
                            'name' => $params['share_path'],
                            'title' => $params['share_path'],
                            "isParent" => true,
                            'open' => true,
                            'uuid' => $params['nas_uuid'],
                            'nocheck' => false,
                            'type' => 2, //1文件 2 文件夹 3 磁盘
                            'icon' => './img/fs/wenjianjia.png',
                            'iconOpen' => './img/fs/wenjianjiaopen.png',
                            'iconClose' => './img/fs/wenjianjia.png',
                            'file_path' => $params['dir'],
                            'more' => false,
                            'checked' => true,
                            'parent_flag' => true,
                            "event_type" => "nas",
                            "code_type" => $d['code_type'],
                            "group_uuid" => $d['group_uuid'],
                            "agent_uuid" => $d['agent_uuid'],
                        );
                    } elseif ($d['path_name'] != '/' && !empty($nasuuid) && $onceflag) {
                        $onceflag = false;
                        $fileNodes[] = array(
                            'id' => $params['dir'],
                            'pid' => 0,
                            'pId' => 0,
                            'name' => $params['share_path'],
                            'title' => $params['share_path'],
                            'isParent' => true,
                            'open' => true,
                            'uuid' => $params['nas_uuid'],
                            'nocheck' => false,
                            'type' => 2, //1文件 2 文件夹 3 磁盘
                            'icon' => './img/fs/wenjianjia.png',
                            'iconOpen' => './img/fs/wenjianjiaopen.png',
                            'iconClose' => './img/fs/wenjianjia.png',
                            'file_path' => $params['dir'],
                            'more' => false,
                            'checked' => true,
                            'half_check' => true,
                            'parent_flag' => true,
                            "event_type" => "nas",
                            "code_type" => $d['code_type'],
                            "group_uuid" => $d['group_uuid'],
                            "agent_uuid" => $d['agent_uuid'],
                        );
                    }
                    if ($d['path_type'] == 1 || $d['path_type'] == 2) {
                        //截取文件信息
                        $info = explode('/', $d['path_name']);
                        $path = '';
                        //获取已选择的文件信息列表
                        foreach ($info as $key => $i) {
                            if ($key == count($info) - 1) {
                                if ($d['path_type'] == 1) {
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
                    } else {
                        if (!in_array($d['path_name'], $filelist)) {
                            $filelist[] = $d['path_name'];
                        }
                    }
                }
            } else {//修改切换节点
                $fileNodes[] = array(
                    'id' => $params['dir'],
                    'pid' => 0,
                    'pId' => 0,
                    'name' => $params['share_path'],
                    'title' => $params['share_path'],
                    'isParent' => true,
                    'open' => true,
                    'uuid' => $params['nas_uuid'],
                    'nocheck' => false,
                    'type' => 2, //1文件 2 文件夹 3 磁盘
                    'icon' => './img/fs/wenjianjia.png',
                    'iconOpen' => './img/fs/wenjianjiaopen.png',
                    'iconClose' => './img/fs/wenjianjia.png',
                    'file_path' => $params['dir'],
                    'more' => false,
                    'checked' => false,
                    'half_check' => true,
                    'parent_flag' => true,
                    "event_type" => "nas",
                );
            }
        }
        //info用于是否获取加载更多
        $info = array();
        //判断加载更多
        foreach ($filelist as $l) {
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
            if ($str == '/') {
                $info[] = $l;
            }
        }
        $result = $allresult['result'];
        $nodeOpcode = new NodeOpcode();
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $operate = $nodeOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if (!$result) {
            //失败
            return $this->muOpResult(false, $operate, '', '', $allresult['errorCode']);
        }
        $result = $allresult['msg'];
        $list = array(
            'finish_flag' => $result['is_search_finish'],      //文件是否列完成   1完成,2未完成
            'search_index' => $result['current_next_index'],   //未完成时,下一个开始位置
            'search_filename' => $result['search_file_name'],  //未完成时,下一个开始名字
            'root_dir_check' => $newParent,
        );
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            if ($newParent) {//新增的父节点下的子节点都选中
                $checked = true;
            } else {
                $checked = false;
            }

            $open = false;
            //检查节点是否在修改列表里
            $str = explode($nasuuid, $d['item_path']);
            if (in_array($str[1], $filelist)) {
                $checked = true;
                //排除$info
                foreach ($filelist as $key => $file) {
                    if ($file == $str[1]) {
                        unset($filelist[$key]);
                    }
                }
                //排除$info
                foreach ($info as $key => $file) {
                    if ($file == $str[1]) {
                        unset($info[$key]);
                    }
                }
            }
            //判判断$list存在父节点是c/1/2
            $flag = false;
            foreach ($filelist as $l) {
                if (!is_bool(strpos($l, $str[1]))) {
                    $flag = true;
                    if ($d['item_type'] != 1) {
                        //文件夹
                        $open = true;
                    }
                }
            }
            if (!empty($params['dir'])) {
                $fileNodes[] = array(
                    'id' => $d['item_path'],
                    'pid' => $result['pid'] == '' ? 0 : $result['pid'],
                    'pId' => $result['pid'] == '' ? 0 : $result['pid'],
                    'name' => $filename,      //文件名或者磁盘名
                    'title' => $filename,
                    'isParent' => $d['item_type'] != 1,
                    'open' => $open,
                    'uuid' => $params['nas_uuid'],
                    'agent_uuid' => $result['agent_uuid'],
                    'nocheck' => false,
                    'type' => $d['item_type'], //1文件 2 文件夹 3 磁盘
                    'icon' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'iconOpen' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjiaopen.png',
                    'iconClose' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'file_path' => $d['item_path'],
                    'more' => false,
                    "group_uuid" => $result['group_uuid'],
                    'checked' => $checked,
                    'code_type' => intval($d['code_type']),
                    "event_type" => "nas",
                );
            }
            //判断根节点下是否展开子节点
            if ($checked && $d['item_type'] != 1) {
                //判判断$list存在父节点是c/1/2
                $flag = false;
                foreach ($filelist as $l) {
                    if (!is_bool(strpos($l, $str[1]))) {
                        $flag = true;
                    }
                }
                if (!$flag) {
                    continue;
                }
                //获取需要展开的节点和加载更多节点
                $params = array(
                    'offset' => 0,
                    'limit' => 40,
                    'filename' => $filename,
                    'dir' => $d['item_path'],
                    'agent_uuid' => $agentuuid,
                    'pid' => $d['item_path'],
                    'nas_uuid' => $nasuuid

                );
                //展开子节点
                $sonList = $this->getOnLoadTree($filelist, $params, $d['item_path'], $nasuuid);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }
        if (intval($result['is_search_finish']) == xphp_get_config('app', 'FLAG')['UNSET']) {
            // 如果还没有显示完全,添加显示更多项
            if (count($info) != 0) {
                //递归加载更多
                $data = array(
                    'offset' => $result['current_next_index'],
                    'limit' => 40,
                    'filename' => $result['search_file_name'],
                    'dir' => $params['dir'],
                    'agent_uuid' => $params['agent_uuid'],
                    'pid' => $params['dir'],
                    'nas_uuid' => $nasuuid
                );
                $moreNodes = $this->getMoreData($filelist, $data, $nasuuid);
                $fileNodes = array_merge($fileNodes, $moreNodes);
            } else {
                $more = array(
                    'id' => $d['item_path'] . '_more',
                    'pid' => $result['pid'],
                    'pId' => $result['pid'] == '' ? 0 : $result['pid'],
                    'name' => xphp_get_lang('WEB_FILE_MORE'),
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    // "isParent" => false,
                    // "open" => false,
                    'uuid' => $params['nas_uuid'],
                    'agent_uuid' => $result['agent_uuid'],
                    'nocheck' => true,
                    'more' => true,
                    'next_index' => $result['current_next_index'],       //从哪个位置开始加载
                    'search_file_name' => $result['search_file_name'],          //从哪个目录开始加载
                    'dir_path' => $params['dir'],
                    "group_uuid" => $result['group_uuid'],                                //当前目录名
                    'file_path' => '',
                    "event_type" => "nas",
                    // "file_path" =>$result['pid'].$result['search_file_name'],

                );
                $fileNodes[] = $more;
            }
        }
        $list['file_nodes'] = $fileNodes;

        return $list;
    }

    /**
     * 修改任务,得到备份任务的所有信息
     * @param array $params 参数
     * @return array
     */
    public function getBackupTaskAllInfo(array $params)
    {
        $taskUUID = $params['job_uuid'];

        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid,bt.strategy_group_uuid,
       bt.agent_uuid, bt.thread_num,brs.strategy_type, brs.number,nt.nas_uuid, nt.detail as ntdetail,
       nt.file_archive_flag,nt.skip_file_alarm_flag,nt.skip_file_alarm_min_num,nt.skip_file_alarm_min_ratio, 
        bss.compressed_flag, bss.encrypted_flag,bss.password,bss.password_auto_flag
        from bd_task bt, bd_reserved_strategy brs, bd_storage_strategy bss, nas_task nt
        where bt.strategy_id = brs.strategy_id
        and bt.strategy_id = bss.strategy_id
		and bt.task_uuid = nt.task_uuid
        and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $sqlwild = 'select detail from bd_task_agent_list where task_uuid = ?';
        $sqlwilddata = $this->dbSelect($sqlwild, array($taskUUID));
        $info = array();

        if ($data) {
            $wildcardinfo = array();
            foreach ($sqlwilddata as $d) {
                array_push($wildcardinfo, json_decode($d['detail'], true));
            }
            $ntdetail = json_decode($data[0]['ntdetail'], true);
            $info = array(
                //任务UUID
                'job_uuid' => $taskUUID,
                //策略uuid
                'strategy_uuid' => $data[0]['strategy_group_uuid'],
                //任务名
                'job_name' => $data[0]['task_name'],
                // //nasUID
                'nas_uuid' => $data[0]['nas_uuid'],
                //level
                'level' => $data[0]['level'],
                //文件信息
                'fileinfo' => $this->getFileBackupFiles($taskUUID),
                //节点
                'node' => array(
                    'node_uuid' => $data[0]['node_uuid'],
                    'storage_uuid' => $data[0]['storage_uuid']
                ),
                //保留策略
                'brs' => array(
                    'type' => $data[0]['strategy_type'],
                    'number' => $data[0]['number'],
                ),
                //存储策略
                'bss' => array(
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    'password' => $data[0]['password'],
                ),
                //时间策略
                'time_strategy' => (new JobInfo())->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                'speed_info' => $this->getSpeedStrategyInfo($taskUUID),
                //高级策略
                'high' => array(
                    'thread_num' => $data[0]['thread_num'],
                    // 'snap_shot_flag' => $utils->parseFlagToBool($data[0]['snap_shot_flag']),
                    'wild_card_info' => $wildcardinfo,
                    'scan_thread_num' => $ntdetail['scan_thread_num'],
                    'scan_file_num' => $ntdetail['scan_file_num'],
                    'file_archive_flag' => $data[0]['file_archive_flag'],
                    'skip_file_alarm_flag' => $data[0]['skip_file_alarm_flag'] == 1,
                    'skip_file_alarm_min_num' => $data[0]['skip_file_alarm_min_num'],
                    'skip_file_alarm_min_ratio' => $data[0]['skip_file_alarm_min_ratio'],
                ),
            );
        }
        return $info;
    }

    /**
     * 得到修改nas备份任务nas设备树
     * @param array $params 参数
     * @return array
     */
    public function getBackupTreeOldInfo(array $params)
    {
        $nasuuid = $params['nas_uuid'];

        $agentList = $this->getNasBackupTree();
        if (!empty($agentList)) {
            foreach ($agentList as $key => $node) {
                if ($nasuuid == $node['uuid']) {
                    $agentList[$key]['checked'] = true;
                }
            }
        }
        return $agentList;
    }

    /**
     * 创建备份任务
     * @param array $params 参数
     * @return string
     */
    public function createBackupJob($params)
    {
        //public params
        $taskname = htmlspecialchars_decode($params['job_name']);
        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['strategy_group_uuid'] ?? '';

        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['NAS'];

        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            $info = array();
            foreach ($params['srcInfo']['fileInfo'] as $each) {
                if (!in_array($each['nas_uuid'], $info)) {
                    $info[] = $each['nas_uuid'];
                }
            }
            Tenant::instance()->checkTenantAuth(xphp_get_config('module')['MODULE_TYPE']['NAS'], $info, '');
        }

        //组合备份方式完备差备等
        $timestrategylist = $this->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        //组合保留策略
        $reserverstrategy = $this->groupReserverStrategy($params['highInfo']['reserve']);
        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['highInfo']['transfer']);
        //组合存储策略
        $storagestrategy = $this->groupStorageStrategy($params['highInfo']['store']);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $taskname,
            $moduletype,
            $timestrategylist,
            $reserverstrategy,
            $transportstrategy,
            $storagestrategy,
            $nodeInfo
        );
        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupNAS(
            $params['srcInfo']['fileInfo'],
            $params['srcInfo']['nas_uuid']
        );
        $pfMsg['snap_shot_flag'] = $params['highInfo']['newstr']['silentsnapshotcheck'] ? 1 : 2;//快照
        $pfMsg['thread_num'] = $params['highInfo']['newstr']['backup_thread_num'];//传输线程数量
        $pfMsg['scan_thread_num'] = $params['highInfo']['newstr']['scan_thread_num'];//扫描线程数量
        $pfMsg['scan_file_num'] = $params['highInfo']['newstr']['scan_file_num'];//扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['highInfo']['newstr']['wildcard_list']);//通配符
        //限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedInfo']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        $pfMsg['nas_uuid'] = $params['srcInfo']['nas_uuid'];
        $pfMsg['file_archive_flag'] = $params['highInfo']['file_archive'];//归档
        $pfMsg['skip_file_alarm_flag'] = $params['highInfo']['skip_file_alarm_flag'] ? 1 : 2;//跳过文件告警开关
        $pfMsg['permission_operate_flag'] = v1_parse_bool_to_flag($params['highInfo']['permission_operate_flag']); // 是否开启对象存储备份
        $pfMsg['skip_file_alarm_flag'] = v1_parse_bool_to_flag($params['highInfo']['skip_file_alarm_flag']); // 是否跳过智能告警
        $pfMsg['skip_file_alarm_min_ratio'] = $params['highInfo']['skip_file_alarm_min_ratio'];//跳过文件告警比例
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbFSMsgs($opName, $nodeInfo['node_uuid'], $msg);
        if (!empty($agentList)) {
            sleep(1);   //睡一秒
        }

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
     * 修改备份任务，比创建备份多传一个job_uuid
     * @param array $params 参数
     * @return string
     */
    public function editBackupJob(array $params)
    {
        //public params
        $taskname = htmlspecialchars_decode($params['job_name']);

        //全局策略uuid  (没有为空值)
        $strategygroupuuid = $params['strategy_group_uuid'];

        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['NAS'];
        //组合备份方式完备差备等
        $timestrategylist = $this->groupBackupTimeList($params['backupInfo'], $strategygroupuuid);
        //组合保留策略
        $reserverstrategy = $this->groupReserverStrategy($params['highInfo']['reserve']);
        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['highInfo']['transfer']);
        //组合存储策略
        $storagestrategy = $this->groupStorageStrategy($params['highInfo']['store']);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['highInfo']['node']);
        //组合所有策略
        $pfMsg = $this->pfCreateBackupTaskMessage(
            $taskname,
            $moduletype,
            $timestrategylist,
            $reserverstrategy,
            $transportstrategy,
            $storagestrategy,
            $nodeInfo
        );
        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        $pfMsg['task_uuid'] = $params['task_uuid'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupNAS(
            $params['srcInfo']['fileInfo'],
            $params['srcInfo']['nas_uuid']
        );
        $pfMsg['snap_shot_flag'] = $params['highInfo']['newstr']['silentsnapshotcheck'] ? 1 : 2;//快照
        $pfMsg['thread_num'] = $params['highInfo']['newstr']['backup_thread_num'];//线程数量
        $pfMsg['scan_thread_num'] = $params['highInfo']['newstr']['scan_thread_num'];//扫描线程数量
        $pfMsg['scan_file_num'] = $params['highInfo']['newstr']['scan_file_num'];//扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['highInfo']['newstr']['wildcard_list']);//通配符
        //限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedInfo']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategygroupuuid;
        $pfMsg['nas_uuid'] = $params['srcInfo']['nas_uuid'];
        $pfMsg['file_archive_flag'] = $params['highInfo']['file_archive'];//归档
        $pfMsg['permission_operate_flag'] = v1_parse_bool_to_flag($params['highInfo']['permission_operate_flag']); // 是否开启对象存储备份
        $pfMsg['skip_file_alarm_flag'] = v1_parse_bool_to_flag($params['highInfo']['skip_file_alarm_flag']); // 是否跳过智能告警
        $pfMsg['skip_file_alarm_min_num'] = $params['highInfo']['skip_file_alarm_min_num'];//跳过文件告警个数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['highInfo']['skip_file_alarm_min_ratio'];//跳过文件告警比例
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $msg = json_encode($pfMsg);
        $mbResult = $this->service()->mbFSMsgs($opName, $nodeInfo['node_uuid'], $msg);
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
     * 得到NAS列表
     * @param array $params 参数数组
     *                      start  开始位置
     *                      limit  查找个数
     *                      searchFileName 从哪个文件名开始查找
     *                      dir            进入目录的目录名
     * @return array
     */
    private function getNasDir(array $params): array
    {
        //请确保每一个参数都有,因为这里情况太复杂,不好检测,请在POST请求的时候带齐所有参数
        $start = intval($params['offset']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'];
        $dir = $params['dir'];
        $pid = $params['pid'];          //父节点ID
        $agentUUID = $params['agent_uuid'];
        $codetype = 2;
        if (!empty($params['code_type'])) {
            $codetype = $params['code_type'];//编码类型
        }
        $opName = 'NODE_AGENT_OP_QUERY_DIR_LIST';
        $nodeInfo = $this->getMountNode($params['nas_uuid']);
        //消息发往各个节点，成功就返回  失败就继续循环
        $sendFlag = false;//发送消息到后台标志
        $servive = $this->service();
        foreach ($nodeInfo as $info) {
            $msg = array(
                'agent_uuid' => $info['agent_uuid'],
                'search_index' => $start,
                'limit_count' => $limit,
                'search_file_name' => $searchFileName,
                'dir_path' => $dir,
                'code_type' => $codetype,
            );

            $nodeStatus = (new Node())->getNodeAllStatus($info['node_uuid']);
            if ($nodeStatus['flag'] && !$sendFlag) {
                //如果节点状态正常
                $mbResult = $servive->mbNodeMsgs($opName, $info['node_uuid'], json_encode($msg), true);
                if ($mbResult['result']) {
                    $sendFlag = true;
                }
            }
        }
        $mbResult['msg']['pid'] = $pid;
        $mbResult['msg']['agent_uuid'] = $agentUUID;
        return $mbResult;
    }

    /**
     * 得到所有挂载成功节点和节点的agent_uuid，用于备份第一步扫描目录 消息发往各个节点
     * @param string $nasuuid nas_uuid
     * @return array
     */
    private function getMountNode(string $nasuuid): array
    {
        $sql = 'select node_uuid, agent_uuid from nas_mount_list where nas_uuid = ?';
        $data = $this->dbSelect($sql, array($nasuuid));

        $nodeList = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                $nodeList[] = array(
                    'node_uuid' => $d['node_uuid'],
                    'agent_uuid' => $d['agent_uuid'],
                );
            }
        }
        return $nodeList;
    }

    /**
     * 递归获取加载子节点
     * @param array  $list    list
     * @param array  $params  参数
     * @param string $path    路径
     * @param string $nasuuid nas_uuid
     * @return array|unknown[]
     */
    private function getOnLoadTree($list, $params, $path, $nasuuid)
    {
        $fileNodes = array();
        //获取子节点
        $data = $this->getNasSonTree($params);
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
            $pathstr = explode($nasuuid, $path);
            if ($str == $pathstr[1]) {
                $info[] = $l;
            }
        }
        //获取展开子节点
        foreach ($fileData as $key => $d) {
            $checked = false;
            $str = explode($nasuuid, $d['file_path']);
            if (in_array($str[1], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$list
                foreach ($list as $key => $file) {
                    if ($file == $str[1]) {
                        unset($list[$key]);
                    }
                }
                //排除$info
                foreach ($info as $key => $i) {
                    if ($i == $str[1]) {
                        unset($info[$key]);
                    }
                }
            }
            if (count($info) != 0 && $d['more']) {
                continue;
            }
            //判判断$list存在父节点是c/1/2
            $flag = false;
            foreach ($list as $l) {
                if (!is_bool(strpos($l, $str[1]))) {
                    $flag = true;
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            if ($checked && $d['type'] != 1) {
                if (!$flag) {
                    continue;
                }
                $data = array(
                    'offset' => 0,
                    'limit' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['file_path'],
                    'agent_uuid' => $params['agent_uuid'],
                    'pid' => $d['file_path'],
                    'nas_uuid' => $nasuuid
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['file_path'], $nasuuid);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }
        //获取加载更多
        if (count($info) != 0 && $fileData[count($fileData) - 1]['more']) {
            //递归加载更多
            $data = array(
                'offset' => $fileData[count($fileData) - 1]['next_index'],
                'limit' => 40,
                'filename' => $fileData[count($fileData) - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agent_uuid' => $params['agent_uuid'],
                'pid' => $params['dir'],
                'nas_uuid' => $nasuuid
            );
            $moreNodes = $this->getMoreData($list, $data, $nasuuid);
            $fileNodes = array_merge($fileNodes, $moreNodes);
        }

        return $fileNodes;
    }

    /**
     * 递归获取加载更多
     * @param array  $list    list
     * @param array  $params  参数
     * @param string $nasuuid nas_uuid
     * @return array
     */
    private function getMoreData($list, $params, $nasuuid)
    {
        $fileNodes = array();
        $moreData = //获取加载更多的节点
            $data = $this->getNasSonTree($params);
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
            $pathstr = explode($nasuuid, $params['dir']);
            if ($str == $pathstr[1]) {
                $info[] = $l;
            }
        }
        foreach ($moreData as $key => $d) {
            $checked = false;
            $str = explode($nasuuid, $d['file_path']);
            if (in_array($str[1], $list)) {
                $checked = true;
                $d['checked'] = $checked;
                //排除$info
                foreach ($list as $key => $i) {
                    if ($i == $str[1]) {
                        unset($list[$key]);
                    }
                }

                foreach ($info as $key => $i) {
                    if ($i == $str[1]) {
                        unset($info[$key]);
                    }
                }
            }
            if (count($info) != 0 && $d['more']) {
                continue;
            }
            //判判断$list存在父节点是c/1/2
            $flag = false;
            foreach ($list as $l) {
                if (!is_bool(strpos($l, $str[1]))) {
                    $flag = true;
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            if ($checked && $d['type'] != 1) {
                if (!$flag) {
                    continue;
                }
                $data = array(
                    'offset' => 0,
                    'limit' => 40,
                    'filename' => $d['name'],
                    'dir' => $d['file_path'],
                    'agent_uuid' => $params['agent_uuid'],
                    'pid' => $d['file_path'],
                    'nas_uuid' => $nasuuid
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['file_path'], $nasuuid);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }
        $count = count($moreData);
        if ($moreData[$count - 1]['more'] && count($info) != 0) {
            //加载更多
            $data = array(
                'offset' => $moreData[$count - 1]['next_index'],   //依次累加
                'limit' => 40,
                'filename' => $moreData[$count - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agent_uuid' => $params['agent_uuid'],
                'group_uuid' => $params['group_uuid'],
                'pid' => $params['dir'],
                'nas_uuid' => $nasuuid
            );
            $moreNodes = $this->getMoreData($list, $data, $nasuuid);
            $fileNodes = array_merge($fileNodes, $moreNodes);
        }

        return $fileNodes;
    }

    /**
     * 根据任务ID得到nas备份备份列表信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    private function getFileBackupFiles(string $taskuuid)
    {
        $sql = "select fpl.path_name, fpl.path_type, nt.nas_uuid
                    from fs_path_list fpl, nas_task nt where fpl.task_uuid=nt.task_uuid and fpl.task_uuid = ?;";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        foreach ($data as $d) {
            $filename = $this->getFileName(intval($d['path_type']), $d['path_name']);
            $info[] = array(
                'nas_uuid' => $d['nas_uuid'],
                'type' => $d['path_type'],
                'name' => $filename,
                'path' => $d['path_name'],
                'sclass' => $this->getFileClassName($d['path_type'], $filename, 's'),
            );
        }
        return $info;
    }

    /**
     * 得到备份的文件列表
     * @param array  $filelists 列表
     * @param string $nasuuid   nas
     * @return array
     */
    private function groupBackupNAS($filelists, $nasuuid): array
    {
        $fileList = array();
        foreach ($filelists as $file) {
            $fileList[] = array(
                'path_type' => strval($file[0]),    //文件类型
                'path_name' => $file[1],             //文件路径
                'agent_uuid' => $nasuuid,//nasuuid
                'code_type' => $file[2],//编码类型
            );
        }
        return $fileList;
    }

    /**
     * 得到备份的通配符相关信息
     * @param array $wildcard 数组
     * @return array
     */
    private function getWildCardList($wildcard)
    {
        $data = array();
        foreach ($wildcard as $w) {
            // if($w[2]!=0){
            $data[] = array(
                'agent_uuid' => $w[0],
                'wildcard' => $w[1],
                'wildcard_mode' => $w[2],
            );
            // }
        }
        return $data;
    }
}