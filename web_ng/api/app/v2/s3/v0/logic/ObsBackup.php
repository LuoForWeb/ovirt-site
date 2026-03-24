<?php

namespace app\v2\s3\v0\logic;

use app\v2\common\logic\Backup;
use app\v2\common\logic\JobInfo;
use app\v2\opcode\NodeOpcode;
use app\v2\opcode\PfOpcode;
use app\v2\resources\v0\logic\Node;
use app\v2\tenant\v0\logic\Tenant;
use app\v2\resources\v0\logic\Index;
use app\v2\exchange\v0\logic\ExchangeJobInfo;

/**
 * note          对象存储 备份管理 logic
 * @auther       chengjiafu@vinchin.com
 * @date         2023/9/8 17:40
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ObsBackup extends Backup
{

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->nodeOpcode = new NodeOpcode();
    }

    /**
     * 获取备份任务对象存储树
     * @param $params
     * @return array
     */
    public function getObsBackupTree($params = []): array
    {
        // 1.获取对象存储表中的所有对象存储
        $sql = 'SELECT 
                    obs.id, obs.obs_uuid, obs.obs_nickname, obs.obs_create_time, obs.vendor, 
                    obs.access_key_id, obs.access_key_secret, obs.endpoint_override, obs.ssl_verify_flag,
                    obs.status, obs.authorization 
                    FROM 
                    obs_resource obs ';
        // 2.获取用户
        $user = xphp_get_user_info();
        $sqlParams = array();

        if (v1_auth_need_check_look()) {
            $obsUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['OBS'], 'obs.obs_uuid');

            $sql .= ' WHERE ' . $obsUuidSql;
        }

        // 3.过滤出当前用户下的所有对象存储
        $data = $this->dbSelect($sql, $sqlParams);

        // 4.获取所有云服务商
//        $storageVendors = xphp_get_config('resource', 'OBJECT_STORAGE_VENDOR');
        $hasPushedFlag = [false, false, false, false, false, false, false, false, false, false];

        $firstLevelArr = [];
        foreach ($data as $d) {
            switch ($d['vendor']) {
                case 0:
                    $item = array('pId' => 0, 'id' => 1, 'name' => xphp_get_lang('UI_OBS_VENDOR_AWS'), 'title' => xphp_get_lang('UI_OBS_VENDOR_AWS'), 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/aws.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[0]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[0] = true;
                    }
                    break;
                case 1:
                    $item = array('pId' => 0, 'id' => 2, 'name' => xphp_get_lang('UI_OBS_VENDOR_OSS'), 'title' => xphp_get_lang('UI_OBS_VENDOR_OSS'), 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/ali.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[1]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[1] = true;
                    }
                    break;
                case 2:
                    $item = array('pId' => 0, 'id' => 3, 'name' => xphp_get_lang('UI_OBS_VENDOR_COS'), 'title' => xphp_get_lang('UI_OBS_VENDOR_COS'), 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/tengxun.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[2]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[2] = true;
                    }
                    break;
                case 3:
                    $item = array('pId' => 0, 'id' => 4, 'name' => xphp_get_lang('UI_OBS_VENDOR'), 'title' => xphp_get_lang('UI_OBS_VENDOR'), 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/huawei.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[3]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[3] = true;
                    }
                    break;
                case 4:
                    $item = array('pId' => 0, 'id' => 5, 'name' => 'Ceph S3', 'title' => 'Ceph S3', 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/ceph.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[4]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[4] = true;
                    }
                    break;
                case 5:
                    $item = array('pId' => 0, 'id' => 6, 'name' => 'Wasabi', 'title' => 'Wasabi', 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/wasabi.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[5]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[5] = true;
                    }
                    break;
                case 6:
                    $item = array('pId' => 0, 'id' => 7, 'name' => 'MinIO', 'title' => 'MinIO', 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/minio.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[6]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[6] = true;
                    }
                    break;
                case 7:
                    $item = array('pId' => 0, 'id' => 8, 'name' => xphp_get_lang('UI_OBS_VENDOR_AZURE'), 'title' => xphp_get_lang('UI_OBS_VENDOR_AZURE'), 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/azure.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[7]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[7] = true;
                    }
                    break;
                case 8:
                    $item = array('pId' => 0, 'id' => 9, 'name' => 'Huawei OceanStor Pacific', 'title' => 'Huawei OceanStor Pacific', 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/huawei.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[8]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[8] = true;
                    }
                    break;
                case 9:
                    $item = array('pId' => 0, 'id' => 10, 'name' => xphp_get_lang('UI_OBS_VENDOR_OTHER'), 'title' => xphp_get_lang('UI_OBS_VENDOR_OTHER'), 'open' => false, 'nocheck' => false, 'isParent' => true, 'icon' => './img/s3/other-cloud.svg', 'type' => 'obsGroup', 'event_type' => 'obsGroup', 'checkDisabled' => false);
                    if (!$hasPushedFlag[9]) {
                        array_push($firstLevelArr, $item);
                        $hasPushedFlag[9] = true;
                    }
                    break;
                default:
                    break;
            }
        }

        $nodes = [];
        if (!empty($data)) {
            foreach ($data as $d) {
                if ($d['status'] === 0) { // 优先显示离线
                    $name = '(' . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ')' . $d['obs_nickname'] . '(' . $d['access_key_id'] . '@' . $d['endpoint_override'] . ')';
                } else {
                    $name = $d['obs_nickname'] . '(' . $d['access_key_id'] . '@' . $d['endpoint_override'] . ')';
                }

                // 4.根据不同服务商进行分组
                $nodes[] = array(
                    'id' => $d['obs_uuid'],
                    'pId' => $d['vendor'] + 1,
                    'name' => $name,
                    'title' => $d['obs_nickname'] . '(' . $d['access_key_id'] . '@' . $d['endpoint_override'] . ')',
                    'isParent' => false,
                    'nocheck' => false,
                    'open' => false,
                    'type' => 'obsItem',
                    'event_type' => 'obsItem',
                    'uuid' => $d['obs_uuid'],
                    'chkDisabled' => $d['status'] === 0 ? true : false,
                    'chk_disabled' => $d['status'] === 0 ? true : false,
                    'isOfflineNode' => $d['status'] === 0 ? true : false, // 是否为禁用且已勾选节点
                    'icon' => './img/s3/cloud-platform.svg',
                );
            }
        }

        return array_merge($firstLevelArr, $nodes);
    }

    /**
     * 获取对象存储对应的目录树
     * @param array $params
     * @return array
     */
    public function getFileDirTree($params = [])
    {

        $opName = 'NODE_OBS_OP_QUERY_DIR_LIST';
        $allresult = $this->getFileDir($params);
        $editFlag = $params['editFlag']; //修改标记
        $obsuuid = $params['obs_uuid'];
        $filelist = array(); //用于保存修改文件列表

        //获取当前代理端文件备份路径列表
        if ($editFlag) {
            $taskuuid = $params['taskId'];
            $sql = "select path_name, path_type from fs_path_list where agent_uuid = ? and task_uuid = ? ";
            $data = $this->dbSelect($sql, array($obsuuid, $taskuuid));

            foreach ($data as $d) {
                if ($d['path_type'] == 1 || $d['path_type'] == 2) {//文件或文件夹
                    //截取文件信息
                    $info = explode('/', $d['path_name']);
                    $path = '';
                    //获取已选择的文件信息列表
                    foreach ($info as $key => $i) {
                        if ($key == count($info) - 1) {//不是路径最后一层就拼接 /
                            if ($d['path_type'] == 1) {//是最后一层判断是否是文件，文件不用在最后拼 /
                                if (empty($i)) { // 如果最后一层恰好是空文件要补上 /
                                    $path .= '/';
                                } else {
                                    $path .= $i;
                                }
                            } else {
                                continue;
                            }
                        } else { // 非路径的最后一层
                            $path .= $i . '/';
                        }

                        if (!in_array($path, $filelist)) {
                            $filelist[] = html_entity_decode($path);
                        }
                    }
                } else {//磁盘
                    if (!in_array($d['path_name'], $filelist)) {
                        $filelist[] = html_entity_decode($d['path_name']);
                    }
                }
            }
        }

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
            're' => true,
            //成功标志, 方便前端统一处理
            'finishFlag' => $result['is_search_finish'],
            //文件是否列完成   1完成,2未完成
            'searchIndex' => $result['current_next_index'],
            //未完成时,下一个开始位置
            'searchFilename' => $result['search_file_name'],
            //未完成时,下一个开始名字
        );

        $fileNodes = array();
        foreach ($result['item_list'] as $d) {
            $checked = false;
            $open = false;

            //检查节点是否在修改列表里
            if (in_array(html_entity_decode($d['item_path']) . '/', $filelist)) {
                $checked = true;

                //从修改文件列表中 排除已经选中的节点
                foreach ($filelist as $key => $file) {
                    if ($file == $d['item_path'] . '/') {
                        unset($filelist[$key]);
                    }
                }
            }

            //检查节点是否需要展开
            $flag = false;
            foreach ($filelist as $l) {
                //strpos返回第二个字符串在第一个字符串中第一次出现的位置，如果没有找到字符串则返回 FALSE
                if (strpos($l, $d['item_path'] . '/') !== false) {
                    $flag = true;
                    $open = true;
                }
            }

            $filename = explode('|', $d['item_name'])[0];

            $fileNodes[] = array(
                'id' => $d['item_path'] . '/',
                'pId' => 0,
                'name' => $filename,
                //文件名或者磁盘名
                'title' => $filename,
                'isParent' => $d['item_type'] != 1,
                'open' => $open,
                'uuid' => $obsuuid,
                'nocheck' => false,
                'type' => $d['item_type'], //1文件 2 文件夹 3 磁盘
                'icon' => './img/s3/cunchutong.svg',
                'filepath' => $d['item_path'] . '/',
                'checked' => $checked,
                'code_type' => $d['code_type'],
                "event_type" => "obsItem",
            );

            //获取要展开节点的子节点
            if ($checked && $d['item_type'] != 1) {
                if (!$flag) {
                    continue;
                }
                //获取需要展开的节点和加载更多节点
                $params = array(
                    'start' => 0,
                    'limit' => 20,
                    'filename' => $filename,
                    'dir' => $d['item_path'] . '/',
                    'obs_uuid' => $obsuuid,
                    'pid' => $d['item_path'] . '/'
                );

                //展开子节点
                $sonList = $this->getOnLoadTree($filelist, $params, $d['item_path']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }

        //如果list0长度不为空 继续加载更多
        // $list['filelist'] = $fileList;
        $list['fileNodes'] = $fileNodes;

        return $list;
    }

    /**
     * 对象存储目录子树 
     * @param $params
     * @return array|string
     */
    public function getFileDirSonTree($params = [])
    {

        $opName = 'NODE_OBS_OP_QUERY_DIR_LIST';
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
            're' => true,
            //成功标志,方面前端统一处理
            'finish_flag' => $result['is_search_finish'],
            //文件是否列完成   1完成,2未完成
            'search_index' => $result['current_next_index'],
            //未完成时,下一个开始位置
            'search_filename' => $result['search_file_name'],
            //未完成时,下一个开始名字
        );

        // 判断子节点有没有超过20个 有的话在第21个加上 加载更多的节点
        foreach ($result['item_list'] as $d) {
            $filename = $d['item_name'];
            if (!empty($params['dir'])) {
                $fileNodes[] = array(
                    'item_type' => $d['item_type'],
                    'id' => $filename === '/' ? $d['item_path'] . '/' : $d['item_path'],
                    'pId' => $result['pid'] == '' ? 0 : $result['pid'],
                    'name' => $filename, //文件名或者磁盘名
                    'title' => $filename,
                    'isParent' => $d['item_type'] != 1,
                    'open' => false,
                    'uuid' => $params['obs_uuid'],
                    'nocheck' => false,
                    'type' => $d['item_type'],
                    //1文件 2 文件夹 3 磁盘
                    'icon' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'iconOpen' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjiaopen.png',
                    'iconClose' => $d['item_type'] == 1 ? './img/fs/wenjian.png' : './img/fs/wenjianjia.png',
                    'filepath' => $filename === '/' ? $d['item_path'] . '/' : $d['item_path'],
                    'more' => false,
                    'code_type' => $d['code_type'],
                    "event_type" => "obsItem",
                );
            }
        }

        if (intval($result['is_search_finish']) === 0) {
            //如果还没有显示完全,添加显示更多项
            $more = array(
                'id' => '',
                'pId' => $result['pid'] == '' ? 0 : $result['pid'],
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                "isParent" => false,
                "open" => false,
                'uuid' => $params['obs_uuid'],
                'nocheck' => true,
                'more' => true,
                'search_file_name' => $result['search_file_name'], //从哪个目录开始加载
                'dir_path' => $params['dir'],
                "event_type" => "obsItem",
            );

            $fileNodes[] = $more;
        }

        $list['fileNodes'] = $fileNodes ?? [];

        return $list;
    }

    /**
     * 创建对象存储备份任务
     * @param $params
     * @return string
     */
    public function createBackupJob($params = [])
    {
        // 判断授权是否过期
        $license = v1_license_get_expire_days();

        if ($license['expire_days'] < 0) { // 授权过期不能创建备份
            return $this->muOpResult(
                false,
                xphp_get_lang('UI_LICENSE_AUTH_INFO_TITLE'),
                xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED')
            );
        }

        $taskname = htmlspecialchars_decode($params['job_name']);
        //全局策略uuid  (没有为空值)
        $globalID = $params['strategy_group_uuid'] ?? '';

        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            $info = array();
            foreach ($params['srcInfo']['fileInfo'] as $each) {
                if (!in_array($each['obs_uuid'], $info)) {
                    $info[] = $each['obs_uuid'];
                }
            }
            Tenant::instance()->checkTenantAuth(xphp_get_config('module')['MODULE_TYPE']['OBS'], $info, '');
        }
        //组合备份方式完备差备等
        $timestrategylist = $this->groupBackupTimeList($params['backupInfo'], $globalID);
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
            $nodeInfo,
            $params['backupInfo']['type']
        );

        //得到任务类型
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        $pfMsg['submodule_type'] = xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupOBS($params['srcInfo']['fileInfo']);
        $pfMsg['group_list'] = $this->getGroupList($params['srcInfo']['groupList'], $params['srcInfo']['fileInfo']);

        $pfMsg['thread_num'] = $params['highInfo']['newstr']['backupThreadNum']; //传输线程数量
        $pfMsg['scan_thread_num'] = $params['highInfo']['newstr']['scanThreadNum']; //扫描线程数量
        $pfMsg['scan_file_num'] = $params['highInfo']['newstr']['scanFileNum']; //扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['highInfo']['newstr']['wildcardList']); //通配符
        $pfMsg['permission_operate_flag'] = v1_parse_bool_to_flag($params['highInfo']['permission_operate_flag']); // 是否开启对象存储备份
        $pfMsg['skip_file_alarm_flag'] = v1_parse_bool_to_flag($params['highInfo']['skip_file_alarm_flag']); // 是否跳过智能告警
        $pfMsg['skip_file_alarm_min_num'] = $params['highInfo']['skip_file_alarm_min_num']; // 跳过最小文件数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['highInfo']['skip_file_alarm_min_ratio']; // 跳过最小文件比例
        $pfMsg['retry_strategy']['network_retry_times'] = $params['highInfo']['network_retry_times']; // 网络重试次数
        $pfMsg['retry_strategy']['network_retry_interval'] = $params['highInfo']['network_retry_interval']; // 网络重试间隔时间
        $pfMsg['retry_strategy']['op_retry_flag'] = v1_parse_bool_to_flag($params['highInfo']['op_retry_flag']); // 操作异常自动重试
        $pfMsg['retry_strategy']['op_retry_times'] = $params['highInfo']['op_retry_times']; // 操作异常重试次数
        $pfMsg['retry_strategy']['op_retry_interval'] = $params['highInfo']['op_retry_interval']; // 操作异常重试间隔时间
        $pfMsg['retry_strategy']['task_retry_flag'] = v1_parse_bool_to_flag($params['highInfo']['task_retry_flag']); // 任务自动重试
        $pfMsg['retry_strategy']['task_retry_object'] = $params['highInfo']['task_retry_object']; // 任务重试对象
        $pfMsg['retry_strategy']['task_retry_times'] = $params['highInfo']['task_retry_times']; // 任务重试次数
        $pfMsg['retry_strategy']['task_retry_interval'] = $params['highInfo']['task_retry_interval']; // 任务重试间隔时间
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['highInfo']['ignore_resource_limiting_flag']); // 忽略节点资源限制

        //限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);

        // 安全策略
        $pfMsg['safe_config_strategy'] = $params['safe_config_strategy'];

        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $globalID;

        // 传输代理
        $pfMsg['appliance_uuid'] = $params['highInfo']['transfer']['appliance_uuid'];
        $pfMsg['agent_pool_uuid'] = $params['highInfo']['transfer']['appliance_pool_uuid'];

        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        //按代理分组批量创建文件备份任务
        $agentList = $params['srcInfo']['agentList'];
        if (!empty($agentList)) {
            $msg = json_encode($pfMsg);
            $mbResult = $this->service()->mbFSMsgs($nodeInfo['node_uuid'], $opName, $msg);
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
     * 修改备份任务
     * @param $params
     * @return string|void
     */
    public function editBackupJob($params)
    {
        // 判断授权是否过期
        $license = v1_license_get_expire_days();

        if ($license['expire_days'] < 0) { // 授权过期不能创建备份
            return $this->muOpResult(
                false,
                xphp_get_lang('UI_LICENSE_AUTH_INFO_TITLE'),
                xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED')
            );
        }

        $taskuuid = $params['taskuuid'];
        //全局策略uuid  (没有为空值)
        $globalID = $params['strategy_group_uuid'];
        $taskname = htmlspecialchars_decode($params['job_name']);
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['FS'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            $info = array();
            foreach ($params['srcInfo']['fileInfo'] as $each) {
                if (!in_array($each['obs_uuid'], $info)) {
                    $info[] = $each['obs_uuid'];
                }
            }
            Tenant::instance()->checkTenantAuth(xphp_get_config('module')['MODULE_TYPE']['OBS'], $info, $taskuuid);
        }
        //组合备份方式完备差备等
        $timestrategylist = $this->groupBackupTimeList($params['backupInfo'], $globalID);
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
            $nodeInfo,
            $params['backupInfo']['type']
        );

        //得到任务类型
        $pfMsg['task_uuid'] = $taskuuid;
        $pfMsg['submodule_type'] = xphp_get_config('module', 'SUBMODULE_TYPE')['OBS'];
        $pfMsg['task_type'] = xphp_get_config('task', 'TASKTYPE')['BACKUP'];
        $pfMsg['backup_level'] = 1;
        $pfMsg['fs_path_list'] = $this->groupBackupOBS($params['srcInfo']['fileInfo']);
        $pfMsg['group_list'] = $this->getGroupList($params['srcInfo']['groupList'], $params['srcInfo']['fileInfo']);

        $pfMsg['thread_num'] = $params['highInfo']['newstr']['backupThreadNum']; //传输线程数量
        $pfMsg['scan_thread_num'] = $params['highInfo']['newstr']['scanThreadNum']; //扫描线程数量
        $pfMsg['scan_file_num'] = $params['highInfo']['newstr']['scanFileNum']; //扫描文件速度
        $pfMsg['wildcard_list'] = $this->getWildCardList($params['highInfo']['newstr']['wildcardList']); //通配符
        $pfMsg['permission_operate_flag'] = v1_parse_bool_to_flag($params['highInfo']['permission_operate_flag']); // 是否开启对象存储备份
        $pfMsg['skip_file_alarm_flag'] = v1_parse_bool_to_flag($params['highInfo']['skip_file_alarm_flag']); // 是否跳过智能告警
        $pfMsg['skip_file_alarm_min_num'] = $params['highInfo']['skip_file_alarm_min_num']; // 跳过最小文件数
        $pfMsg['skip_file_alarm_min_ratio'] = $params['highInfo']['skip_file_alarm_min_ratio']; // 跳过最小文件比例
        $pfMsg['retry_strategy']['network_retry_times'] = $params['highInfo']['network_retry_times']; // 网络重试次数
        $pfMsg['retry_strategy']['network_retry_interval'] = $params['highInfo']['network_retry_interval']; // 网络重试间隔时间
        $pfMsg['retry_strategy']['op_retry_flag'] = v1_parse_bool_to_flag($params['highInfo']['op_retry_flag']); // 操作异常自动重试
        $pfMsg['retry_strategy']['op_retry_times'] = $params['highInfo']['op_retry_times']; // 操作异常重试次数
        $pfMsg['retry_strategy']['op_retry_interval'] = $params['highInfo']['op_retry_interval']; // 操作异常重试间隔时间
        $pfMsg['retry_strategy']['task_retry_flag'] = v1_parse_bool_to_flag($params['highInfo']['task_retry_flag']); // 任务自动重试
        $pfMsg['retry_strategy']['task_retry_object'] = $params['highInfo']['task_retry_object']; // 任务重试对象
        $pfMsg['retry_strategy']['task_retry_times'] = $params['highInfo']['task_retry_times']; // 任务重试次数
        $pfMsg['retry_strategy']['task_retry_interval'] = $params['highInfo']['task_retry_interval']; // 任务重试间隔时间
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['highInfo']['ignore_resource_limiting_flag']); // 忽略节点资源限制

        //限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);

        // 安全策略
        $pfMsg['safe_config_strategy'] = $params['safe_config_strategy'];

        // 传输代理
        $pfMsg['appliance_uuid'] = $params['highInfo']['transfer']['appliance_uuid'];
        $pfMsg['agent_pool_uuid'] = $params['highInfo']['transfer']['appliance_pool_uuid'];

        $pfMsg['strategy_group_uuid'] = $globalID;

        $opName = 'BD_TASK_OP_BACKUP_MODIFY';

        //按代理分组批量创建文件备份任务
        $agentList = $params['srcInfo']['agentList'];

        if (!empty($agentList)) {
            $msg = json_encode($pfMsg);
            $mbResult = $this->service()->mbFSMsgs($nodeInfo['node_uuid'], $opName, $msg);
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
     * 获取备份任务信息
     * @param string $taskUUID
     * @return array
     */
    public function getBackupTaskInfo(string $taskUUID)
    {
        $sql = "SELECT  bt.task_name, bt.strategy_id, bt.strategy_group_uuid,   
                        bt.agent_uuid, bt.thread_num, bt.ignore_resource_limiting_flag,
                        bt.node_uuid, bt.node_pool_uuid, bt.storage_uuid, bt.storage_pool_uuid, 
                        ot.level,ot.permission_operate_flag,ot.skip_file_alarm_flag,
					    ot.skip_file_alarm_min_num,ot.skip_file_alarm_min_ratio,ot.detail as otdetail,ot.proxy_uuid,
                	    brs.strategy_type, brs.number, brs.strategy_mode, 
                	    bts.encrypt_flag, bts.compress_flag,bts.network_uuid,bts.reconnect_times,bts.reconnect_interval,bts.encrypt_method as ts_encrypt_method,
                	    bss.deduplication_flag, bss.compressed_flag, bss.encrypted_flag, bss.password, bss.password_auto_flag, bss.compress_method, bss.encrypt_method as bs_encrypt_method,
                        bs.time_strategy_backup_type, bt.worm_flag, bt.integrity_check_flag,
                        btsc.worm_protection_time, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy   
                FROM bd_task bt, fs_task ot, bd_reserved_strategy brs,
                     bd_transport_strategy bts, bd_storage_strategy bss, bd_strategy bs, bd_task_safe_config btsc     
                WHERE 
                    bt.task_uuid = ot.task_uuid AND 
                    bt.strategy_id = brs.strategy_id AND
                    bt.strategy_id = bts.strategy_id AND
                    bt.strategy_id = bss.strategy_id AND
                    bt.strategy_id = bs.strategy_id AND
                    bt.task_uuid = btsc.task_uuid AND 
                    bt.task_uuid = ?";

        $data = $this->dbSelect($sql, array($taskUUID));

        $sqlwild = 'select group_uuid,agent_uuid,detail from bd_task_agent_list where task_uuid = ?';
        $sqlwilddata = $this->dbSelect($sqlwild, array($taskUUID));

        if (!empty($data)) {
            $wildcardinfo = [];
            foreach ($sqlwilddata as $d) {
                $wildInfo = json_decode($d['detail'], true);
                if (empty($wildInfo)) {
                    $wildInfo = array(
                        'wildcard' => [],
                        'wildcard_mode' => '0',
                        'wildcard_real_length' => [],
                    );
                }
                $wildInfo['obs_uuid'] = $d['agent_uuid'];
                array_push($wildcardinfo, $wildInfo);
            }

            $ftdetail = json_decode($data[0]['otdetail'], true);

            // 获取任务对应的对象存储
            $selectAgentSql = "SELECT 
                                    btal.agent_uuid, btal.group_uuid,  
                                    obs.obs_nickname, obs.status, obs.authorization  
                                FROM 
                                    bd_task_agent_list btal 
                                LEFT JOIN 
                                    obs_resource obs ON btal.agent_uuid = obs.obs_uuid 
                                WHERE btal.task_uuid = ?";
            $agentData = $this->dbSelect($selectAgentSql, array($taskUUID));
            $agentList = [];
            if (!empty($agentData)) {
                foreach ($agentData as $d) {
                    $agentList[] = array(
                        'obs_uuid' => $d['agent_uuid'],
                        'group_uuid' => $d['group_uuid'],
                        'obs_nickname' => $d['obs_nickname'],
                        'status' => $d['status'],
                        'authorization' => $d['authorization']
                    );
                }
            }

            // 查询存储池类型
            $storagePoolType = 0;
            if ($data[0]['storage_pool_uuid']) {
                $storagePoolData = $this->dbSelect("SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ", [$data[0]['storage_pool_uuid']]);
                $storagePoolType = $storagePoolData[0]['storage_pool_type'];
            }

            $info = array(
                //任务UUID
                'job_uuid' => $taskUUID,
                //策略uuid
                'strategy_group_uuid' => $data[0]['strategy_group_uuid'],
                //任务名
                'job_name' => $data[0]['task_name'],
                // 对象存储
                'checkedObsList' => $agentList,
                'level' => $data[0]['level'],
                //文件信息
                'fileInfo' => $this->getFileBackupFiles($taskUUID),
                //节点
                'node' => array(
                    'node_uuid' => $data[0]['node_uuid'],
                    'storage_uuid' => $data[0]['storage_uuid'],
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    'storage_pool_type' => $storagePoolType,  // 需要返回存储池类别
                ),
                //保留策略
                'brs' => array(
                    'strategy_mode' => $data[0]['strategy_mode'],
                    'type' => $data[0]['strategy_type'],
                    'number' => $data[0]['number'],
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypt_flag']),
                    'encrypt_method' => $data[0]['ts_encrypt_method'],
                    'compress' => v1_parse_flag_to_bool($data[0]['compress_flag']),
                    'network' => $data[0]['network_uuid'],
                    'reconnect_times' => $data[0]['reconnect_times'],
                    'reconnect_interval' => $data[0]['reconnect_interval'],
                    'appliance_agency_flag' => $data[0]['proxy_uuid'] ? true : false,
                    'appliance_uuid' => $data[0]['proxy_uuid'],
                    'appliance_pool_uuid' => $this->getJobAgentPoolInfo($taskUUID),
                ),
                //存储策略
                'bss' => array(
                    'deduplication_flag' => v1_parse_flag_to_bool($data[0]['deduplication_flag']),
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    'password' => !v1_parse_flag_to_bool($data[0]['password_auto_flag']) ? base64_encode(v1_pt_pass_decrypt($data[0]['password'])) : $data[0]['password'],
                    'compress_method' => $data[0]['compress_method'],
                    'encrypt_method' => $data[0]['bs_encrypt_method']
                ),
                //时间策略
                'time_strategy' => (new JobInfo())->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                'speedInfo' => $this->getSpeedStrategy($taskUUID),
                //高级策略
                'high' => array(
                    'thread_num' => $data[0]['thread_num'],
                    'wild_card_info' => $wildcardinfo,
                    'scan_thread_num' => $ftdetail['scan_thread_num'],
                    'scan_file_num' => $ftdetail['scan_file_num'],
                    'permission_operate_flag' => v1_parse_flag_to_bool($data[0]['permission_operate_flag']), // 是否开启对象权限备份
                    'skip_file_alarm_flag' => v1_parse_flag_to_bool($data[0]['skip_file_alarm_flag']), // 是否跳过文件告警智能判断
                    'skip_file_alarm_min_num' => $data[0]['skip_file_alarm_min_num'],
                    'skip_file_alarm_min_ratio' => $data[0]['skip_file_alarm_min_ratio'],
                    'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag'])
                ),
                'time_strategy_backup_type' => $data[0]['time_strategy_backup_type'],
                // 重试策略
                'retry_strategy' => (new ExchangeJobInfo())->getRetryStrategy($taskUUID),
                // 安全策略
                'safe_config_strategy' => array(
                    'worm_flag' => $data[0]['worm_flag'],
                    'worm_protection_time' => $data[0]['worm_protection_time'],
                    'integrity_check_flag' => $data[0]['integrity_check_flag'],
                    'integrity_check_config' => array(
                        'check_strategy' => $data[0]['integrity_check_strategy'],
                        'full_error_policy' => $data[0]['backup_integrity_check_full_error_policy']
                    )
                )
            );
        }

        return $info ?? [];
    }

    /**
     * 获取对象存储对应目录树
     * @param array $params
     * @return array
     */
    private function getFileDir(array $params): array
    {
        $start = intval($params['offset']);
        $limit = intval($params['limit']);
        $searchFileName = $params['filename'] ?? '';
        $dir = htmlspecialchars_decode($params['dir']) ?? '';
        $pid = $params['pid'] ?? 0; //父节点ID
        $obsUUID = $params['obs_uuid'];

        $this->paramsCheck($obsUUID);

        $codetype = !empty($params['code_type']) ? $params['code_type'] : 2; // 编码类型
        $moduleType = xphp_get_config('module', 'MODULE_TYPE')['OBS'];
        $startAfter = $params['startAfter'];

        $msg = array(
            'target_uuid' => $obsUUID,
            'search_index' => $start,
            'limit_count' => $limit,
            'search_file_name' => $searchFileName,
            'dir_path' => $dir,
            'code_type' => $codetype,
            'module_type' => $moduleType,
            'start_after' => $startAfter,
            'submodule_type' => xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']
        );

        $opName = 'FS_PRIVATE_OPERATION_CODE_TARGET_DIR_QUERY';

        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->mbOBSMsg($nodeuuid, $opName, json_encode($msg), true, false, xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']);
        // $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), true);

        $mbResult['msg']['pid'] = $pid;
        //        $mbResult['msg']['group_uuid'] = $groupUUID;

        return $mbResult;
    }

    /**
     * 递归获取加载子节点
     * @param array  $list   list
     * @param array  $params 参数
     * @param string $path   路径
     * @return array
     */
    private function getOnLoadTree($list, $params, $path)
    {
        $fileNodes = array();
        //获取子节点
        $data = $this->getFileDirSonTree($params);
        $fileData = $data['fileNodes'];

        //info用于是否获取加载更多
        $info = array();

        // 判断加载更多
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

        // 获取展开子节点
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
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }

            $fileNodes[] = $d;
            if ($checked && $d['type'] != 1) {
                //判判断$list存在父节点是c/1/2
                if (!$flag) {
                    continue;
                }

                $data = array(
                    'start' => 0,
                    'limit' => 20,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'obs_uuid' => $params['obs_uuid'],
                    'pid' => $d['filepath']
                );

                $sonList = $this->getOnLoadTree($list, $data, $d['filepath']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }

        //获取加载更多
        if ($fileData[count($fileData) - 1]['more']) {
            //递归加载更多
            $data = array(
                'start' => $fileData[count($fileData) - 1]['next_index'],
                'limit' => 20,
                'filename' => $fileData[count($fileData) - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agent_uuid' => $params['agent_uuid'],
                'group_uuid' => $params['group_uuid'],
                'obs_uuid' => $params['obs_uuid'],
                'pid' => $params['dir'],
                'startAfter' => $fileData[count($fileData) - 1]['search_file_name']
            );

            $moreNodes = $this->getMoreData($list, $data, $params['obs_uuid']);
            $fileNodes = array_merge($fileNodes, $moreNodes);
        }

        return $fileNodes;
    }

    /**
     * 递归获取加载更多
     * @param array $list   list
     * @param array $params 参数
     * @return array
     */
    private function getMoreData(array $list, array $params, string $obsUUID): array
    {
        $fileNodes = array();
        $data = $this->getFileDirSonTree($params);

        $moreData = $data['fileNodes'];
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
            // if ($d['more']) {
            //     continue;
            // }
            //检查节点是否需要展开
            $flag = false;
            foreach ($list as $l) {
                if (strpos($l, $d['filepath']) !== false) {
                    $flag = true;
                    if ($d['type'] != 1) {
                        //文件夹
                        $d['open'] = true;
                    }
                }
            }
            $fileNodes[] = $d;
            if ($checked && $d['type'] != 1) {
                //判判断$list存在父节点是c/1/2
                if (!$flag) {
                    continue;
                }
                $data = array(
                    'start' => 0,
                    'limit' => 20,
                    'filename' => $d['name'],
                    'dir' => $d['filepath'],
                    'agent_uuid' => $params['agent_uuid'],
                    'group_uuid' => $params['group_uuid'],
                    'pid' => $d['filepath'],
                    'obs_uuid' => $obsUUID,
                );
                $sonList = $this->getOnLoadTree($list, $data, $d['filepath']);
                $fileNodes = array_merge($fileNodes, $sonList);
            }
        }

        $count = count($moreData);

        if ($moreData[$count - 1]['more'] && count($info) != 0) {
            //加载更多
            $data = array(
                'start' => $moreData[$count - 1]['next_index'],
                //依次累加
                'limit' => 20,
                'filename' => $moreData[$count - 1]['search_file_name'],
                'dir' => $params['dir'],
                'agent_uuid' => $params['agent_uuid'],
                'group_uuid' => $params['group_uuid'],
                'pid' => $params['dir'],
                'obs_uuid' => $obsUUID,
                'startAfter' => $moreData[$count - 1]['search_file_name']
            );

            $moreNodes = $this->getMoreData($list, $data, $obsUUID);

            $fileNodes = array_merge($fileNodes, $moreNodes);
        }

        return $fileNodes;
    }

    /**
     * 得到备份的对象列表
     * @param array $filelists 参数
     * @return array
     */
    private function groupBackupOBS(array $filelists): array
    {
        $fileList = array();
        foreach ($filelists as $file) {
            $fileList[] = array(
                'path_type' => strval($file['type']),    //文件类型
                'path_name' => html_entity_decode($file['filePath']),             //文件路径
                'file_path' => $file['filePath'],
                'agent_uuid' => $file['obs_uuid'], // 对象存储
                'group_uuid' => $file['group_uuid'], // 对象存储group
                'code_type' => !empty($file['codeType']) ? $file['codeType'] : 2,//编码类型
            );
        }
        return $fileList;
    }

    /**
     * 得到备份的通配符相关信息
     * @param array $wildcardArrs 列表
     * @return array
     */
    private function getWildCardList(array $wildcardArrs): array
    {
        $result = [];

        if (!empty($wildcardArrs)) {
            foreach ($wildcardArrs as $item) {
                $result[] = array(
                    'agent_uuid' => $item['obsuuid'],
                    'wildcard' => $item['wildcardList'],
                    'wildcard_mode' => $item['wildcardMode']
                );
            }
        }

        return $result;
    }

    /**
     * 得到备份的对象存储列表
     * @param array $grouplists 分组列表
     * @param array $fileinfo   文件列表
     * @return array
     */
    private function getGroupList($grouplists, $fileinfo): array
    {
        $data = array();
        foreach ($grouplists as $item) {
            $agentList = array();
            foreach ($fileinfo as $i) {
                if ($i['group_uuid'] === $item && !in_array($i['obs_uuid'], $agentList)) {
                    array_push($agentList, $i['obs_uuid']);
                }
            }
            $data[] = array(
                'group_uuid' => $item,
                'agent_uuid' => $agentList,
            );
        }
        return $data;
    }

    /**
     * 根据任务ID得到文件备份备份列表信息
     * @param string $taskuuid 任务uuid
     * @return array
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
                'name' => html_entity_decode($filename),
                'path' => html_entity_decode($d['path_name']),
                'sclass' => $this->getFileClassName($d['path_type'], $filename, 's'),
                'code_type' => $d['code_type'],
            );
        }
        return $info;
    }
}