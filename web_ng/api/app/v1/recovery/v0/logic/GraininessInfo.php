<?php

namespace app\v1\recovery\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\DbProtectOpcode;
use app\v1\opcode\NodeOpcode;
use  app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Node;

/**
 * note          细粒度恢复任务详情右边的文件等的逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2024/7/17 16:57
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class GraininessInfo extends Base
{
    private $nodeOpcode;
    private $dbProtectOpcode;

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->nodeOpcode = new NodeOpcode();
        $this->dbProtectOpcode = new DbProtectOpcode();
    }

    /**
     * 细粒度恢复任务详情资源树
     * @param array $params 请求参数
     * @return array
     */
    public function graininessJobTree(array $params): array
    {
        $pfMSg = [
            'task_uuid' => $params['recovery_uuid'],
            'dir_path' => html_entity_decode($params['dir_path'])
        ];
        $opName = 'ELITE_RCVY_WEB_OP_GET_GRAIN_DIR_FILES';
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $pfMSg,
            (new Node())->getNodeUUIDWithTaskUUID($params['recovery_uuid']),
            true
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($mbResult['result']) {
            foreach ($msg['file_list'] as $d) {
                $filename = $d['file_name'];
                $fileNodes[] = array(
                    'id' => $d['file_path'],
                    'pid' => $d['file_path'],
                    'name' => $filename,      //文件名或者磁盘名
                    'title' => $filename,
                    'is_parent' => $d['file_type'] != 1,
                    'open' => false,
                    'nocheck' => false,
                    'type' => $d['file_type'], //1文件 2 文件夹 3 磁盘
                    'size' => $d['file_size'],
                    'path' => $d['file_path'],
                    'modify_time' => $d['modify_time'],
                    'icon' => $d['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'icon_open' => $d['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjiaopen.png',
                    'icon_close' => $d['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'filepath' => $d['file_path'],
                    'more' => false,
                );
            }
            if (intval($msg['is_search_finish']) == xphp_get_config('app', 'FLAG')['UNSET']) {
                //如果还没有显示完全,添加显示更多项
                $more = array(
                    // "id" => $pid==0?0:$pid.'/',
                    'pid' => $msg['pid'],
                    'pId' => $msg['pid'],
                    'name' => xphp_get_lang('WEB_FILE_MORE'),
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    // "isParent" => false,
                    // "open" => false,
                    'uuid' => $params['agent_uuid'],
                    'nocheck' => true,
                    'more' => true,
                    'next_index' => $msg['current_next_index'],       //从哪个位置开始加载
                    'search_file_name' => $msg['search_file_name'],          //从哪个目录开始加载
                    'dir_path' => $params['dir'],
                    "group_uuid" => $msg['group_uuid'],                                //当前目录名

                );
                $fileNodes[] = $more;
            }
            $list['file_nodes'] = $fileNodes ?? [];

            return $list;
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 按客户名细粒度恢复任务详情资源树
     * @param array $params 请求参数
     * @return array
     */
    public function searchGraininessTree(array $params): array
    {
        $pfMSg = [
            'task_uuid' => $params['search_uuid'],
            'file_name' => $params['file_name'],
            'dir_path' => html_entity_decode($params['dir_path']),
        ];
        $opName = 'ELITE_RCVY_WEB_OP_SEARCH_GRAIN_FILE';
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $pfMSg,
            (new Node())->getNodeUUIDWithTaskUUID($params['search_uuid']),
            true
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($mbResult['result']) {
            $fileNodes = [];
            foreach ($msg['file_list'] as $d) {
                $filename = $d['file_name'];
                $dirname = dirname($d['file_path']);
                $dirname = $dirname == '/' ? '/' : ($dirname . '/');
                $fileNodes[] = array(
                    'id' => $d['file_path'],
                    'pid' => $dirname,
                    'name' => $filename,      //文件名或者磁盘名
                    'title' => $filename,
                    'is_parent' => $d['file_type'] != 1,
                    'open' => false,
                    'nocheck' => false,
                    'type' => $d['file_type'], //1文件 2 文件夹 3 磁盘
                    'size' => $d['file_size'],
                    'path' => $d['file_path'],
                    'modify_time' => $d['modify_time'],
                    'icon' => $d['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'icon_open' => $d['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjiaopen.png',
                    'icon_close' => $d['file_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'filepath' => $d['file_path'],
                    'more' => false,
                );
            }
            $list['file_list'] = $this->calcAllDepth($fileNodes);
            return $list;
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 再次组装搜索出来的目录结果，返回所有的层级
     * @param array $array 返回数组
     * @return array
     */
    private function calcAllDepth(array $array): array
    {
        $newArr = [];
        foreach ($array as $item) {
            if ($item['is_parent']) {
                // 需要先把搜索结果的是父级的也添加进去
                $newArr[] = $item['pid'] . $item['name'];
            }
            $exArr = array_filter(explode('/', $item['pid']));
            if (count($exArr) > 0) {
                // 表示这个是有多层的目录结构
                // 判断下是否是windows的
                $first = '/';
                if (str_contains($exArr[0], ':')) {
                    // 是windows的
                    $first = '';
                }
                $realPath = '';
                foreach ($exArr as $key => $item2) {
                    if ($key == 0 && empty($first)) {
                        // windows的第一层不累加
                        $realPath .= $first . $item2;
                        continue;
                    } else {
                        $realPath .= '/' . $item2;
                    }
                    if (!in_array($realPath, $newArr)) {
                        // 未解析的，解析一次
                        $newArr[] = $realPath;
                        $items = $item;
                        $dirname = dirname($realPath);
                        $dirname = $dirname == '/' ? '/' : ($dirname . '/');
                        $items['filepath'] = $realPath . '/';
                        $items['path'] = $realPath . '/';
                        $items['id'] = $realPath . '/';
                        $items['pid'] = $dirname;
                        $items['name'] = $item2;
                        $items['title'] = $item2;
                        $items['type'] = 2;
                        $items['open'] = true;
                        $items['is_parent'] = true;
                        $items['icon'] = './img/fs/wenjianjia.png';
                        $items['icon_open'] = './img/fs/wenjianjiaopen.png';
                        $items['icon_close'] = './img/fs/wenjianjia.png';
                        $array[] = $items;
                    }
                }
            }
        }
        return $array;
    }

    /**
     * 任务策略配置
     * @param array $params 请求参数
     * @return array
     */
    public function taskStrategy(array $params): array
    {
        $jobuuid = $params['jobuuid'];
        $sql = "select worm_flag,virus_scan_flag,integrity_check_flag
                from bd_task
                where machine_flag = 0 and task_uuid = ? ";
        $data = $this->dbSelect($sql, [$jobuuid]);

        return [
            'backupPoint' => $data[0]['worm_flag'],
            'virusScan' => $data[0]['virus_scan_flag'],
            'complete' => $data[0]['integrity_check_flag'],
        ];
    }

    /**
     * 客户端传输配置
     * @param array $params 请求参数
     * @return array
     */
    public function clientConfig(array $params): array
    {
        // 处理下 file_list 可能存在的特殊字符
        foreach ($params['file_list'] as $key => $item) {
            $params['file_list'][$key]['file_path'] = html_entity_decode($item['file_path']);
            $params['file_list'][$key]['file_name'] = html_entity_decode($item['file_name']);
        }
        $pfMSg = [
            'agent_uuid' => $params['agent_uuid'],
            'file_save_path' => $params['file_save_path'],
            'file_save_mode' => $params['file_save_mode'],
            'file_list' => $params['file_list'],
            'task_uuid' => $params['recovery_uuid'],
            'transport_task_name' => $params['transport_task_name'],
            'skip_dir_tree_flag' => v1_parse_bool_to_flag($params['skip_dir_tree_flag']),
            'reserve_file_permission_flag' => v1_parse_bool_to_flag($params['reserve_file_permission_flag']),
        ];
        $opName = 'ELITE_RCVY_WEB_OP_START_TRANSPORT_GRAIN_FILE';
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $pfMSg,
            (new Node())->getNodeUUIDWithTaskUUID($params['recovery_uuid']),
            true
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg) ;
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 网络共享配置
     * @param array $params 请求参数
     * @return array
     */
    public function networkConfig(array $params): array
    {

        $pfMSg = [
            'protocol' => $params['protocol'],
            'server_ip' => $params['server_ip'],
            'username' => $params['username'],
            'password' => v1_pt_pass_encrypt($params['password']),
            'task_uuid' => $params['recovery_uuid'],
            'allow_ip_list' => $params['allow_ip_list'],
            'mount_point_path' => html_entity_decode($params['mount_point_path']),
        ];
        $opName = 'ELITE_RCVY_WEB_OP_GREATE_NETWORK_FILE_SYSTEM_MOUNT_POINT';
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $pfMSg,
            (new Node())->getNodeUUIDWithTaskUUID($params['recovery_uuid']),
            true
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg) ;
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 网页下载验证
     * @param array $params 请求参数
     * @return array
     */
    public function downloadFile(array $params)
    {
        // 处理下 file_list 可能存在的特殊字符
        foreach ($params['file_list'] as $key => $item) {
            $params['file_list'][$key]['file_path'] = html_entity_decode($item['file_path']);
            $params['file_list'][$key]['file_name'] = html_entity_decode($item['file_name']);
        }

        $path = '/usr/share/nginx/' . xphp_get_config('app', 'SYSTEM_INFO')['vendor'] . '/grain/';
        $pfMSg = [
            'task_uuid' => $params['recovery_uuid'],
            'file_list' => $params['file_list'],
            'compressed_package_path' => $path
        ];
        $opName = 'ELITE_RCVY_WEB_OP_DOWNLOAD_GRAIN_FILE';
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $pfMSg,
            (new Node())->getNodeUUIDWithTaskUUID($params['recovery_uuid']),
            true
        );

        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($mbResult['result']) {
            if (!empty($msg['error_code'])) {
                // 说明有错误信息
                $error = xphp_get_config('error');
                $errorKey = $error['errorCode'][$msg['error_code']];
                $msgs = xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ': #' . $msg['error_code'] . ',';
                $msgs .= xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ': ' . $error['errorCodeDes'][$errorKey];
            }
            $msg['message'] = $msgs ?? '';
            return $this->muOpResult($mbResult['result'], $operate, $msg) ;
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 网页下载链接
     * @param array $params 请求参数
     * @return void|string
     */
    public function downloadFiles(array $params)
    {
        // 验证token是否有效
        $cache = xphp_get_cache('graininess_token');
        if (empty($params['token']) || empty($cache) || $params['token'] != $cache['token']) {
            exit('download error');
        }
        // 清除缓存 保证链接是一次性的
        xphp_set_cache('graininess_token', []);
        $params = $cache['params']; // 从cache中获取下载的参数
        if ($params['type'] == 1) {
            // 文件下载
            $this->downloadGrainRecoveryFile($params);
        } else {
            // 文件夹下载
            $this->downloadGrainRecoveryDir($params);
        }
    }

    /**
     * 客户端传输列表
     * @param array $params 请求参数
     * @return array
     */
    public function graininessClients(array $params)
    {
        $jobuuid = $params['recovery_uuid']; // 任务uuid
        $offset = ((int)$params['offset']) ?: 0;
        $limit = ((int)$params['limit']) ?: 10;
        $searchName = $params['search'];    //名称
        $sortArr = [
            'ip' => 'ba.ip',
            'transport_task_name' => 'bgrt.transport_task_name',
            'file_save_mode' => 'bgrt.file_save_mode',
            'completed_size' => 'bgrt.completed_transport_size',
            'transport_size' => 'bgrt.total_transport_size',
            'task_status' => 'bgrt.task_status',
            'create_time' => 'bgrt.create_time',
            'progress' => 'bgrt.completed_transport_size / bgrt.total_transport_size',
        ];
        if (!empty($sortArr[$params['sort']])) {
            $sort = $sortArr[$params['sort']];
        } else {
            $sort = 'bgrt.id';
        }
        $order = $params['order'] ?: 'desc';
        $field = 'distinct bgrt.id,bgrt.transport_task_name,bgrt.file_save_mode,
                  bgrt.completed_transport_size,bgrt.task_status,bgrt.create_time,
                bgrt.total_transport_size,bgrt.transport_info,bgrt.file_save_path,
                bgrt.transport_file_list,ba.ip,ba.hostname,bat.node_uuid,bgrt.finish_time ';
        $sql = " from bd_grain_recovery_transport bgrt
                left join bd_agent ba on bgrt.agent_uuid = ba.agent_uuid
                left join bd_task bat on bat.task_uuid = bgrt.task_uuid
                where bgrt.task_uuid = ? ";
        if ($this->checkEmpty($searchName)) {
            $sql .= " and bgrt.transport_task_name like '%" . $searchName . "%' ";
        }
        $countSql = 'select count(*) as total ' . $sql;

        $countData = $this->dbSelect($countSql, [$jobuuid]);
        $count = $countData[0]['total'] ?? 0;
        if ($count == 0) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $sql = 'select ' . $field . $sql . " ORDER BY {$sort} {$order} limit {$offset},{$limit}";
        $data = $this->dbSelect($sql, [$jobuuid]);

        $nodeIp = '';
        $isMasterNode = true;
        if (!empty($data)) {
            $node = (new Node())->getLocalNodeUUID();
            // 获取当前任务关联的节点ip地址
            $nodeIp = $this->dbSelect(
                'select ip from bd_node_network where node_uuid = ? and `type` = 1 order by network_order asc limit 1',
                [$data[0]['node_uuid']]
            );
            $nodeIp = $nodeIp[0]['ip'] ? 'https://' . $nodeIp[0]['ip'] : '';
            // 标记是否是主节点
            $isMasterNode = $data[0]['node_uuid'] == $node;
        }

        $nullspace = xphp_get_config('app', 'NULLSPACE');
        $rows = [];
        foreach ($data as $item) {
            if (in_array($item['task_status'], [1, 2, 3])) {
                $speed = v1_calpercent($item['total_transport_size'], $item['completed_transport_size']);
            } else {
                $speed = $nullspace;
            }

            if ($item['task_status'] == 1 && $item['completed_transport_size'] == 0) {
                // 传输为 0并且任务不是成功状态，那就进度为 0%
                $speed = '0%';
            }

            if (in_array($item['task_status'], [3, 4, 5, 6])) {
                $finishtime = $item['finish_time'] ?? $nullspace;
            } else {
                $finishtime = $nullspace;
            }

            $rows[] = [
                'id' => $item['id'],
                'node_uuid' => $item['node_uuid'],
                'is_master_node' => $isMasterNode,
                'node_ip' => $nodeIp,
                'transport_task_name' => $item['transport_task_name'],
                'file_save_mode' => $item['file_save_mode'],
                'progress' => $speed,
                'completed_size' => v1_calsize($item['completed_transport_size'], true),
                'transport_size' => v1_calsize($item['total_transport_size'], true),
                'task_status' => $item['task_status'],
                'create_time' => $item['create_time'],
                'finish_time' => $finishtime,
                'transport_info' => $item['transport_info'],
                'file_save_path' => $item['file_save_path'],
                'transport_file_list' => json_decode($item['transport_file_list'], true),
                'ip' => $item['hostname'] . '(' . $item['ip'] . ')',
            ];
        }

        return [
            'rows' => $rows,
            'total' => $count
        ];
    }

    /**
     * 网络共享列表
     * @param array $params 请求参数
     * @return array
     */
    public function graininessNetwork(array $params)
    {
        $jobuuid = $params['recovery_uuid']; // 任务uuid
        $offset = ((int)$params['offset']) ?: 0;
        $limit = ((int)$params['limit']) ?: 10;
        $searchName = $params['search'];    //名称
        $sort = $params['sort'] ?? 'id';
        $order = $params['order'] ?: 'desc';
        $field = 'distinct id,task_uuid,mount_path,mount_protocol,create_time,access_name,
                task_status,access_password,access_path,share_command,mount_other_config';
        $sql = " from bd_grain_recovery_share
                where task_uuid = ?";
        if ($this->checkEmpty($searchName)) {
            $sql .= " and access_path like '%" . $searchName . "%' ";
        }
        $countSql = 'select count(*) as total ' . $sql;
        $countData = $this->dbSelect($countSql, [$jobuuid]);
        $count = $countData[0]['total'] ?? 0;
        if ($count == 0) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $sql = 'select ' . $field . $sql . " ORDER BY {$sort} {$order} limit {$offset},{$limit}";

        $data = $this->dbSelect($sql, [$jobuuid]);
        $rows = [];
        foreach ($data as $row) {
            $allowIp = json_decode($row['mount_other_config'], true);
            $allowIpList = '';
            if (!empty($allowIp['allow_ip_list'])) {
                $allowIpList = implode(',', $allowIp['allow_ip_list']);
            }
            $rows[] = [
                'uuid' => $row['id'],
                'task_uuid' => $row['task_uuid'],
                'mount_path' => $row['mount_path'],
                'mount_protocol' => $row['mount_protocol'],
                'create_time' => $row['create_time'],
                'access_name' => $row['access_name'],
                'task_status' => $row['task_status'],
                'access_path' => $row['access_path'],
                'allow_ip_list' => $allowIpList,
                'share_command' => !empty($row['share_command']) ? json_decode($row['share_command'], true) : [],
                //'access_password' => v1_pt_pass_decrypt($row['access_password']),
                'access_password' => '',
            ];
        }

        return [
            'rows' => $rows,
            'total' => $count
        ];
    }

    /**
     * 查看共享任务的访问密码
     * @param array $params 请求参数
     * @return array
     */
    public function graininessNetworkPwd(array $params)
    {

        // 先校验输入的密码 和当前用户的密码是一致的
        $password = v1_decrypt_js_rsa($params['password']);
        $sql = 'select password from bd_user where user_uuid = ?';
        $user = $this->dbSelect($sql, [xphp_get_user_info()['userUuid']]);
        if (empty($user)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_AGENT_NO_USER')
            ];
        }
        if ($user[0]['password'] != md5($password)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_COMMON_VERIFY_PASSWORD_ERROR')
            ];
        }
        $sql = 'select access_password from bd_grain_recovery_share where task_uuid = ? and id = ?';
        $data = $this->dbSelect($sql, [$params['job_uuid'], $params['uuid']]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
            ];
        }

        return [
            'code' => 0,
            'msg' => v1_pt_pass_decrypt($data[0]['access_password']),
        ];
    }

    /**
     * 网络共享文件列表
     * @param array $params 请求参数
     * @return array
     */
    public function graininessNetworkFiles(array $params)
    {
        $jobuuid = $params['recovery_uuid']; // 任务uuid
        $nwtworkuuid = $params['network_uuid']; // 网络共享uuid
        $rows = [
            [
                'uuid' => '1233',
                'name' => '/usr/share/nginx/vinchin/index.php',
                'status' => 1,
            ]
        ];
        return [
            'rows' => $rows,
            'total' => 1
        ];
    }

    /**
     * 客户端传输操作
     * @param array $params 请求参数
     * @return array
     */
    public function graininessClientStop(array $params)
    {
        $task = $this->dbSelect(
            'select task_uuid from bd_grain_recovery_transport where id = ?',
            [$params['recovery_uuid']]
        );
        $pfMSg = [
            'grain_transport_id' => $params['recovery_uuid'],
        ];
        $opName = 'ELITE_RCVY_WEB_OP_STOP_TRANSPORT_GRAIN_FILE';
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $pfMSg,
            (new Node())->getNodeUUIDWithTaskUUID($task[0]['task_uuid']),
            true
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg) ;
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除客户端传输
     * @param array $params 请求参数
     * @return array
     */
    public function graininessClientDel(array $params)
    {
        $opcodeName = 'NODE_AGENT_OP_DEL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $task = $this->dbSelect(
            'select task_uuid from bd_grain_recovery_transport where id = ?',
            [$params['recovery_uuid']]
        );
        //组合消息
        $msg = array(
            'recovery_uuid' => $params['recovery_uuid'],
            'id_list' => $params['id_list'],
            'clients_uuid' => $params['clients_uuid'],
        );

        $mbResult = $this->mbNodeMsg(
            $opcodeName,
            (new Node())->getNodeUUIDWithTaskUUID($task[0]['task_uuid']),
            json_encode($msg)
        );
        $result = $mbResult['result'];
        if ($result) {
            $this->muOpResult(false, $operate, $mbResult['msg'], 0, $mbResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_DELETE_LIST_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_AGENT_DELETE_LIST_SUCCESS'), true, 200);
    }

    /**
     * 网络共享的操作
     * @param array $params 请求参数
     * @return array
     */
    public function graininessNetworkOperate(array $params)
    {
        $opcodeName = 'NODE_AGENT_OP_DEL';
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        //组合消息
        $msg = array(
            'recovery_uuid' => $params['recovery_uuid'],
            'network_uuid' => $params['network_uuid'],
            'clients_uuid' => $params['clients_uuid'],
        );
        $task = $this->dbSelect(
            'select task_uuid from bd_grain_recovery_share where id = ?',
            [$params['recovery_uuid']]
        );
        $mbResult = $this->mbNodeMsg(
            $opcodeName,
            (new Node())->getNodeUUIDWithTaskUUID($task[0]['task_uuid']),
            json_encode($msg)
        );
        $result = $mbResult['result'];
        if ($result) {
            $this->muOpResult(false, $operate, $mbResult['msg'], 0, $mbResult['errorCode']);
            return $this->sendResult(xphp_get_lang('WEB_AGENT_DELETE_LIST_ERROR'), false, 400);
        }
        return $this->sendResult(xphp_get_lang('WEB_AGENT_DELETE_LIST_SUCCESS'), true, 200);
    }

    /**
     * 删除网络共享
     * @param array $params 请求参数
     * @return array
     */
    public function graininessNetworkDel(array $params)
    {
        $task = $this->dbSelect(
            'select task_uuid from bd_grain_recovery_share where id = ?',
            [$params['recovery_uuid']]
        );
        $pfMSg = [
            'grain_share_id' => $params['recovery_uuid'],
        ];
        $opName = 'ELITE_RCVY_WEB_OP_DESTROY_NETWORK_FILE_SYSTEM_MOUNT_POINT';
        $mbResult = $this->service()->mbGrainMsgs(
            $opName,
            $pfMSg,
            (new Node())->getNodeUUIDWithTaskUUID($task[0]['task_uuid']),
            true
        );
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        $msg = $mbResult['msg'];
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $msg) ;
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 客户端传输错误，下载跳过文件(子节点)
     * @param array $params 请求参数
     * @return array
     */
    public function getTransferLog(array $params): array
    {

        if (!empty($params['url'])) {
            $cmd = '[ "$(systemctl is-active nginx)" != "active" ] && systemctl restart nginx;cp -f ' . $params['url'] . ' ' . $params['url'] . '.log';
            $opName = 'NODE_SYS_OP_DO_CMD';
            $msg = array('command' => $cmd);
            $mbResult = $this->service()->mbNodeMsgs($opName, $msg, $params['node_uuid'] ?? '', true, true);
            //返回结果到UI
            if ($mbResult['result']) {
                $rootPath = dirname(API_PATH, 2);
                $url = $params['node_ip'] . '/' . str_replace($rootPath, '', $params['url'] . '.log');
                // 发送成功
                return [
                    'code' => 0,
                    'url' => $url
                ];
            } else {
                return [
                    'code' => 1,
                    'msg' => $mbResult['msg']
                ];
            }
        }
        return [
            'code' => 1,
            'msg' => 'empty'
        ];
    }

    /**
     * 细粒度文件下载完后删除对应的资源文件
     * @param array $params 请求参数
     * @return bool
     */
    public function grainSourceClear(array $params)
    {

        $realList = [];
        $rootPath = dirname(API_PATH, 2);
        foreach ($params['files'] as $item) {
            $file = $rootPath . $item;
            //if (file_exists($file)) {
            // 注释是因为可能是子节点的数据，检测不到
            $realList[] = $file;
            //}
        }

        if (!empty($realList)) {
            $cmd = [];
            foreach ($realList as $items) {
                if (strpos($items, '*') === false) {
                    //$cmd[] = 'rm -r ' . $items;
                    // 只允许删除没得 通配符 * 的文件
                    // 转义路径以防止命令注入
                    $escapedItems = escapeshellarg($items);
                    $cmd[] = '[ -f ' . $escapedItems . ' ] && rm -f ' . $escapedItems;
                    // [ -f "' . $items . '" ] && rm -f "' . $items . '"
                }
            }
            $cmds = implode(';', $cmd);
            $opName = 'NODE_SYS_OP_DO_CMD';
            $msg = array('command' => $cmds);
            $this->service()->mbNodeMsgs($opName, $msg, $params['node_uuid'] ?? '', false, true);
        }
        return true;
    }

    /**
     * 根据任务名或语言包获取新的任务名 客户端传输任务
     * @param array $param 任务名称/语言包健名;任务uuid
     * @return array
     */
    public function getValidName(array $param): array
    {
        $name = $param['job_name'];
        $jobUuid = $param['job_uuid'];
        // 获取真正的任务名
        if (strpos($name, 'WEB_') !== false || strpos($name, 'UI_') !== false) {
            /// 表示传递的是键名
            $name = xphp_get_lang($name);
        }
        // 这里判断下任务表里面是否存在同名的任务，存在就在编号前加上1
        $sqlParams = [$jobUuid, $name . '%'];
        $sql = "select transport_task_name from bd_grain_recovery_transport
                where task_uuid = ? and transport_task_name like ?";
        $data = $this->dbSelect($sql, $sqlParams);

        if (empty($data)) {
            return ['value' => $name . '1'];
        }

        // 取出任务名后面的编号并降序
        $array = str_replace($name, '', array_column($data, 'transport_task_name'));

        $key = array_map('intval', $array);
        $key = !empty($key) ? max($key) : 0;

        $new = $name . ($key + 1);

        return ['value' => $new];
    }

    /**
     * 下载细粒度恢复文件
     * @param array $params 请求参数
     * @return void
     */
    private function downloadGrainRecoveryFile(array $params): void
    {
        $taskuuid = $params['job_uuid'];
        $filepath = $params['path'];
        $filesize = $params['size'];
        $filename = $params['name'];
        $showtype = intval($_GET['showtype']);
        $device = $_GET['device'];

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskuuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_READ_FILE_BLOCK';

        Header('Content-type: application/octet-stream');
        Header('Accept-Ranges: bytes');
        Header('Accept-Length: ' . $filesize);
        Header('Content-Disposition: attachment; filename=' . $filename);
        $blockSize = xphp_get_config('GRAIN_FILE_BLOCK_SIZE');
        //如果大于分块大小,分块下载
        for ($i = 0; $i < $filesize; $i = $i + $blockSize) {
            if ($filesize - $i <= $blockSize) {
                $readLen = $filesize - $i;
            } else {
                $readLen = $blockSize;
            }
            $msg = array(
                'task_uuid' => $taskuuid,
                'guest_file_path' => $filepath,
                'read_offset' => $i,
                'read_len' => $readLen,
                'display_mode' => $showtype,
                'device_path' => $device
            );
            $mbResult = $this->service()->mbVMMsgs($nodeuuid, $hypervisor, $opName, $msg, true);
            echo $mbResult;
            ob_flush(); //将数据从php的buffer中释放出来
            flush(); //将释放出来的数据发送给浏览器
        }
    }

    /**
     * 细粒度下载目录
     * @param array $params 请求参数
     * @return void
     */
    private function downloadGrainRecoveryDir(array $params)
    {
        $taskuuid = $params['job_uuid'];
        $filepath = $params['path'];
        $filename = $params['name'];
        $showtype = intval($_GET['showtype']);
        $device = $params['device'];
        $uuid = $params['uuid'];

        $msg = array(
            'task_uuid' => $taskuuid,
            'key' => $uuid,
            'dir_path' => $filepath,
            'display_mode' => $showtype,
            'device_path' => $device,
            'read_len' => xphp_get_config('vm', 'GRAIN_FILE_BLOCK_SIZE')
        );

        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $hypervisor = $data[0]['hypervisor_type'];

        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskuuid);
        $opName = 'VM_PRIVATE_TASK_OP_GRAIN_PROCESS_DOWNLOAD_DIR';

        Header('Content-type: application/octet-stream');
        Header('Accept-Ranges: bytes');
        //         Header("Accept-Length: " . $filesize);
        Header('Content-Disposition: attachment; filename="' . $filename . '.tar.gz"');

        $this->readGrainRecoveryDirContent($nodeuuid, $hypervisor, $opName, $msg);
    }

    /**
     * 循环读取细粒度恢复目录内容
     * @param string $nodeuuid   节点uuid
     * @param int    $hypervisor 虚拟化类型
     * @param string $opName     操作码
     * @param array  $msg        消息
     * @return void
     */
    private function readGrainRecoveryDirContent(string $nodeuuid, int $hypervisor, string $opName, array $msg)
    {
        $mbResult = $this->service()->mbVMMsgs($nodeuuid, $hypervisor, $opName, $msg, true);
        //$mbResult socket.class中特殊处理
        //返回数据  array('head'=>array(),'msg'=>string)
        /*!!!!!!!!!!!!!!!!!!!!!!!!!!!注意这几个下标从1开始的!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
         * uint32    reserved   保留字段
         * uint32    is_eof     是否读完   1读完 0未读完
         * unit32    error_code 错误码，正确为0
         * uint32    size       本次读取大小
         * */
        if (0 == $mbResult['head'][3]) {
            //如果成功,输出内容
            echo $mbResult['msg'];
            //判断是否结束,结束关闭读取,未结束,继续读取
            if (1 == $mbResult['head'][2]) {
                //结束
                $opName = 'VM_PRIVATE_TASK_OP_GRAIN_STOP_DOWNLOAD_DIR';
                $mbResult = $this->service()->mbVMMsgs($nodeuuid, $hypervisor, $opName, $msg, false);
            } else {
                //未结束
                $mbResult = null;
                $this->readGrainRecoveryDirContent($nodeuuid, $hypervisor, $opName, $msg);
            }
        } else {
            exit('Read Dir error!111');
        }
    }

    /**
     * 检查细粒度任务状态,只在运行状态的时候可以继续,如果不是运行状态,直接返回没有数据的参数恢复
     * @param string $jobuuid 任务uuid
     * @return bool
     */
    private function checkGrainRecoveryTaskStatus(string $jobuuid): bool
    {
        $sql = "select task_status from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobuuid));
        $taskStatus = intval($data[0]['task_status']);
        if (xphp_get_config('task', 'TASKSTATUS')['RUNNING'] != $taskStatus) {
            return false;
        }
        return true;
    }
}
