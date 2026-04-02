<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\FsOpcode;

class S3 extends Base
{

    private $fsOpcode;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->fsOpcode = new FsOpcode();
    }

    /**
     * 添加对象存储
     *
     * @param array $params
     * @return string
     */
    public function addOBStorage(array $params)
    {

        // 获取所有云服务商
        $storageVendor = xphp_get_config('resource', 'OBJECT_STORAGE_VENDOR');

        // 获取用户
        $user = xphp_get_user_info();

        $opcodeName = 'FS_PRIVATE_OPERATION_CODE_TARGET_ADD';

        $accessKeySecret = v1_decrypt_js_rsa($params['access_key_secret']);

        // 组合消息
        $msg = [
            'vendor' => $params['vendor'],
            'access_key_id' => $params['access_key_id'],
            'access_key_secret' => base64_encode($accessKeySecret),
            'endpoint_override' => $params['endpoint_override'],
            'ssl_verify_flag' => $params['ssl_verify_flag'],
            'nickname' => htmlspecialchars_decode($params['nickname']),
            'user_uuid' => $user['userUuid'],
            'detail' => json_encode(['appid' => $params['appid'], 'aws_account_type' => $params['aws_account_type']]),
            'appliance_uuid' => $params['appliance_uuid']
        ];

        $nodeuuid = (new Node())->getLocalNodeUUID();

        // 发送至主节点 
        $mbResult = $this->service()->addOBStorage($nodeuuid, $opcodeName, $msg);

        $result = $mbResult['result'];
        $operate = $this->fsOpcode->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取对象存储列表
     *
     * @param array $params
     * @return array
     */
    public function getOBStorageList(array $params): array
    {

        $search = !empty($params['search']) ? v1_escape_wildcard(htmlspecialchars_decode($params['search'])) : '';

        $sql = 'SELECT 
                    obs.id, obs.obs_uuid, obs.user_uuid, obs.obs_nickname, obs.obs_create_time, obs.vendor, obs.access_key_id, obs.access_key_secret, obs.endpoint_override, obs.ssl_verify_flag, obs.status, obs.detail, obs.proxy_uuid, obs.authorization,obs.refresh_time,
                    bu.user_name as bu_username  
                FROM 
                    obs_resource obs
                LEFT JOIN 
                    bd_user bu on bu.user_uuid = obs.user_uuid';
        $sqlcount = 'SELECT count(obs_uuid) as total FROM obs_resource obs LEFT JOIN bd_user bu on bu.user_uuid = obs.user_uuid';

        $where = ' WHERE obs.id is not null';

        if (v1_auth_need_check_look()) {
            $obsUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['OBS'], 'obs.obs_uuid');

            $where .= " AND ({$obsUuidSql})";
        }

        if (!empty($search)) {
            $search = str_replace('\\\\', '\\\\\\\\', $search);
            $where .= " AND obs.obs_nickname LIKE '%" . $search . "%'";
        }

        if (isset($params['start_time']) && isset($params['end_time'])) {
            $where .= " AND obs.obs_create_time BETWEEN " . ' "' . $params['start_time'] . '"' . " AND " . '"' . $params['end_time'] . '"';
        }

        if (isset($params['vendor'])) {
            $where .= " AND obs.vendor = " . $params['vendor'];
        }

        if (isset($params['status']) && $params['status'] !== '') {
            $where .= " AND obs.status = " . $params['status'];
        }

        if (isset($params['endpoint_override'])) {
            $where .= "  AND obs.endpoint_override LIKE '%" . $params['endpoint_override'] . "%'";
        }

        if (isset($params['nickname'])) {
            $nickName = v1_escape_wildcard(htmlspecialchars_decode($params['nickname']));
            $nickName = str_replace('\\\\', '\\\\\\\\', $nickName);
            $where .= "  AND obs.obs_nickname LIKE '%" . $nickName . "%'";
        }

        //拼接搜索条件
        $sql .= $where;
        $sqlcount .= $where;

        $count = $this->dbSelect($sqlcount);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        $total = intval($count[0]['total']);
        // 表头排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'obs_uuid' => 'obs_uuid',
                'obs_nickname' => 'obs_nickname',
                'vendor' => 'vendor',
                'obs_create_time' => 'obs_create_time',
                'access_key_id' => 'access_key_id',
                'access_key_secret' => 'access_key_secret',
                'endpoint_override' => 'endpoint_override',
                'authorization' => 'authorization'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . '');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ? , ? ';
        $sqlParams = array($params['offset'], $params['limit']);
        $data = $this->dbSelect($sql, $sqlParams);

        $records = [];
        $verdorArr = [
            0 => xphp_get_lang('UI_OBS_VENDOR_AWS'),
            1 => xphp_get_lang('UI_OBS_VENDOR_OSS'),
            2 => xphp_get_lang('UI_OBS_VENDOR_COS'),
            3 => xphp_get_lang('UI_OBS_VENDOR'),
            4 => 'Ceph S3',
            5 => 'Wasabi',
            6 => 'MinIO',
            7 => xphp_get_lang('UI_OBS_VENDOR_AZURE'),
            8 => xphp_get_lang('UI_OBS_VENDOR_OTHER')
        ];

        $user = xphp_get_user_info();
        foreach ($data as $d) {
            $appliance_agency = '';
            if (!empty($d['proxy_uuid'])) {
                $appliance_agency = $this->getApplianceAgency($d['proxy_uuid']);
            }

            // 拥有者
            $owner = $this->getObsUUIDName($d['obs_uuid'], $d['bu_username'] ?? '--');
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

            // 创建者
            $creator = $d['bu_username'] ?? '--';
            if ('admin' == $creator && $user['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                $creator = 'sysadmin';
            }

            $records[] = [
                'id' => $d['id'],
                'obs_uuid' => $d['obs_uuid'],
                'nickname' => $d['obs_nickname'],
                'obs_create_time' => $d['obs_create_time'],
                'vendor' => $d['vendor'],
                'vendorDesc' => $verdorArr[$d['vendor']],
                'access_key_id' => $d['access_key_id'],
                'endpoint_override' => $d['endpoint_override'],
                'ssl_verify_flag' => $d['ssl_verify_flag'],
                'status' => $d['status'],
                'detail' => $d['detail'],
                'appliance_uuid' => $d['proxy_uuid'],
                'appliance_agency' => $appliance_agency,
                'creator' => $creator,
                'owner' => $owner,
                'authorization' => $d['authorization'],
                'refresh_time' => $d['refresh_time']
            ];
        }

        $finalRecords = [];

        if (!empty($records)) {
            // 高级搜索 - 拥有者
            if (isset($params['owner'])) {
                if (isset($params['creator'])) { // 同时传了creator 和 owner
                    foreach ($records as $d) {
                        if (strpos($d['owner'], $params['owner']) !== false && strpos($d['creator'], $params['creator'])) { // 包含指定的传参中的owner 和 creator
                            $finalRecords[] = [
                                'id' => $d['id'],
                                'obs_uuid' => $d['obs_uuid'],
                                'nickname' => $d['nickname'],
                                'obs_create_time' => $d['obs_create_time'],
                                'vendor' => $d['vendor'],
                                'vendorDesc' => $verdorArr[$d['vendor']],
                                'access_key_id' => $d['access_key_id'],
                                'endpoint_override' => $d['endpoint_override'],
                                'ssl_verify_flag' => $d['ssl_verify_flag'],
                                'status' => $d['status'],
                                'detail' => $d['detail'],
                                'appliance_uuid' => $d['proxy_uuid'],
                                'appliance_agency' => $d['appliance_agency'],
                                'creator' => $d['creator'],
                                'owner' => $d['owner'],
                                'authorization' => $d['authorization'],
                                'refresh_time' => $d['refresh_time']
                            ];
                        }
                    }
                } else { // 未传 creator，但传了 owner
                    foreach ($records as $d) {
                        if (strpos($d['owner'], $params['owner']) !== false) { // 包含指定的传参中的owner
                            $finalRecords[] = [
                                'id' => $d['id'],
                                'obs_uuid' => $d['obs_uuid'],
                                'nickname' => $d['nickname'],
                                'obs_create_time' => $d['obs_create_time'],
                                'vendor' => $d['vendor'],
                                'vendorDesc' => $verdorArr[$d['vendor']],
                                'access_key_id' => $d['access_key_id'],
                                'endpoint_override' => $d['endpoint_override'],
                                'ssl_verify_flag' => $d['ssl_verify_flag'],
                                'status' => $d['status'],
                                'detail' => $d['detail'],
                                'appliance_uuid' => $d['proxy_uuid'],
                                'appliance_agency' => $d['appliance_agency'],
                                'creator' => $d['creator'],
                                'owner' => $d['owner'],
                                'authorization' => $d['authorization'],
                                'refresh_time' => $d['refresh_time']
                            ];
                        }
                    }
                }
            } else {
                if (isset($params['creator'])) { // 未传owner，但传了creator
                    foreach ($records as $d) {
                        if (strpos($d['creator'], $params['creator']) !== false) { // 包含指定的传参中的creator
                            $finalRecords[] = [
                                'id' => $d['id'],
                                'obs_uuid' => $d['obs_uuid'],
                                'nickname' => $d['nickname'],
                                'obs_create_time' => $d['obs_create_time'],
                                'vendor' => $d['vendor'],
                                'vendorDesc' => $verdorArr[$d['vendor']],
                                'access_key_id' => $d['access_key_id'],
                                'endpoint_override' => $d['endpoint_override'],
                                'ssl_verify_flag' => $d['ssl_verify_flag'],
                                'status' => $d['status'],
                                'detail' => $d['detail'],
                                'appliance_uuid' => $d['proxy_uuid'],
                                'appliance_agency' => $d['appliance_agency'],
                                'creator' => $d['creator'],
                                'owner' => $d['owner'],
                                'authorization' => $d['authorization'],
                                'refresh_time' => $d['refresh_time']
                            ];
                        }
                    }
                } else { // 未传owner，也没有传creator
                    $finalRecords = $records;
                }
            }
        }

        if (empty($finalRecords)) {
            $total = 0;
        }

        return ['rows' => $finalRecords, 'total' => $total];
    }

    /**
     * 获取对象存储授权列表信息
     * @param array $params
     * @return array
     */
    public function getObsAuthList(array $params): array
    {
        $sql = 'SELECT 
                    obs.id, obs.obs_uuid, obs.user_uuid, obs.obs_nickname, obs.authorization,
                    bu.user_name as bu_username  
                FROM 
                    obs_resource obs
                LEFT JOIN 
                    bd_user bu on bu.user_uuid = obs.user_uuid';
        $sqlcount = 'SELECT count(obs_uuid) as total FROM obs_resource obs LEFT JOIN bd_user bu on bu.user_uuid = obs.user_uuid';

        $where = '';

        $where = ' WHERE obs.id is not null';

        if (v1_auth_need_check_look()) {
            $obsUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['OBS'], 'obs.obs_uuid');

            $where .= " AND ({$obsUuidSql})";
        }

        //拼接搜索条件
        $sql .= $where;
        $sqlcount .= $where;

        $count = $this->dbSelect($sqlcount);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        $total = intval($count[0]['total']);
        // 表头排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'obs_uuid' => 'obs_uuid',
                'obs_nickname' => 'obs_nickname',
                'obs_create_time' => 'obs_create_time',
                'authorization' => 'authorization'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . '');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ? , ? ';
        $sqlParams = array($params['offset'], $params['limit']);

        $data = $this->dbSelect($sql, $sqlParams);

        $records = [];

        $user = xphp_get_user_info();
        foreach ($data as $d) {
            // 拥有者
            $owner = $this->getObsUUIDName($d['obs_uuid'], $d['bu_username'] ?? '--');
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

            // 创建者
            $creator = $d['bu_username'] ?? '--';
            if ('admin' == $creator && $user['isThreePowers']) {
                // 三权模式下，admin显示为sysadmin
                $creator = 'sysadmin';
            }

            $records[] = [
                'id' => $d['id'],
                'obs_uuid' => $d['obs_uuid'],
                'obs_nickname' => $d['obs_nickname'],
                'authorization' => $d['authorization']
            ];
        }

        return ['rows' => $records, 'total' => $total];
    }

    /**
     * 获取传输代理 agent ip
     *
     * @param string $appliance_uuid
     * @return void
     */
    private function getApplianceAgency(string $appliance_uuid)
    {
        $sql = 'select agent_name, ip from bd_agent where agent_uuid = ?';
        $data = $this->dbSelect($sql, array($appliance_uuid));

        $result = '';
        if (!empty($data)) {
            $result = $data[0]['agent_name'] . '(' . $data[0]['ip'] . ')';
        }

        return $result;
    }

    /**
     * 根据对象存储uuid获取当前的使用者是谁 只查询佩芬的资源/组
     *
     * @param string $obsUUID 对象存储uuid
     * @param string $userName 拥有者
     * @return string
     */
    private function getObsUUIDName(string $obsUUID, string $userName = ''): string
    {
        $sqlParams = [$obsUUID];
        // 先 再分配的资源和资源组去查询
        $sql = "select user_uuid from mt_user_resource where resource_type = 59 and resource_uuid = ? limit 1";
        $array = $this->dbSelect($sql, $sqlParams);

        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 59";
            $array = $this->dbSelect($sql, $sqlParams);
        }

        if ($array) {
            $sql = "select user_name from bd_user where user_uuid in ('" . implode("','", array_column($array, 'user_uuid')) . "')";
            $data = $this->dbSelect($sql);
            $userName = implode(',', array_column($data, 'user_name'));
        }

        return $userName;
    }

    /**
     * 修改对象存储
     *
     * @param array $params
     * @return void
     */
    public function editOBStorage(array $params)
    {
        $opdes = xphp_get_lang('WEB_NODE_OBS_OP_MODIFY');

        // 判断操作权限
        $this->checkAuthBySourceUuid($params['obs_uuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['OBS']);

        // 查询出库里已有的记录
        $data = $this->dbSelect("select * from obs_resource where obs_uuid = ?", [$params['obs_uuid']]);

        if (empty($data)) {
            return;
        }

        // 定义操作名
        $opcodeName = 'FS_PRIVATE_OPERATION_CODE_TARGET_MODIFY';

        $accessKeySecret = v1_decrypt_js_rsa($params['access_key_secret']);

        $nodeuuid = (new Node())->getLocalNodeUUID();

        // 组合消息
        $msg = [
            'obs_uuid' => $params['obs_uuid'],
            'access_key_id' => $params['access_key_id'],
            'access_key_secret' => base64_encode($accessKeySecret),
            'nickname' => htmlspecialchars_decode($params['nickname']),
            'endpoint_override' => $params['endpoint_override'],
            'ssl_verify_flag' => $params['ssl_verify_flag'],
            'new_connect_flag' => $params['new_connect_flag'],
            'detail' => json_encode(['appid' => $params['appid'], 'aws_account_type' => $params['aws_account_type']]),
            'appliance_uuid' => $params['appliance_uuid']
        ];

        $mbResult = $this->service()->editOBStorage($nodeuuid, $opcodeName, $msg);

        $result = $mbResult['result'];
        $operate = $this->fsOpcode->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 删除对象存储
     * @param array $params
     * @return string
     */
    public function delOBStorage(array $params)
    {
        // 判断操作权限
        $this->checkAuthBySourceUuid(implode(',', $params['obs_uuids']), xphp_get_config('resource', 'RESOURCE_TYPE')['OBS']);

        $localnodeuuid = (new Node())->getLocalNodeUUID();
        // 定义操作名
        $opcodeName = 'FS_PRIVATE_OPERATION_CODE_TARGET_DELETE';
        $obsuuids = $params['obs_uuids'];

        foreach ($obsuuids as $uuid) {
            // 检测该对象存储上是否有任务存在
            $sql = "select task_uuid from bd_task where agent_uuid = ?";
            $data = $this->dbSelect($sql, array($uuid));

            if (!empty($data)) {
                return $this->muOpResult(false, xphp_get_lang('WEB_OBS_DELETE_OBS'), xphp_get_lang('WEB_OBS_DELETE_OBS_TIPS'));
            }
        }

        // 组合消息
        $msg = array(
            'obs_uuid_list' => $obsuuids
        );

        $mbResult = $this->service()->delOBStorage($localnodeuuid, $opcodeName, $msg);

        $result = $mbResult['result'];
        $operate = $this->fsOpcode->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 
     * 添加授权
     * @param array $params
     * @return string
     */
    public function addAuth(array $params)
    {
        // 定义操作名
        $opcodeName = 'PT_LICENSE_OP_MODIFY_OBS_LICENSE';
        $obsids = $params['obs_ids'];

        // 组合消息
        $msg = array(
            'obs_id_list' => $obsids,
            'obs_auth_flag' => '1'
        );

        $mbResult = $this->service()->addAuth($opcodeName, $msg);

        $result = $mbResult['result'];
        $operate = $this->fsOpcode->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 
     * 取消授权
     * @param array $params
     * @return void
     */
    public function removeAuth(array $params)
    {
        // 定义操作名
        $opcodeName = 'PT_LICENSE_OP_MODIFY_OBS_LICENSE';
        $obsids = $params['obsids'];

        // 组合消息
        $msg = array(
            'obs_id_list' => $obsids,
            'obs_auth_flag' => '2'
        );

        $mbResult = $this->service()->removeAuth($opcodeName, $msg);

        $result = $mbResult['result'];
        $operate = $this->fsOpcode->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];

        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 刷新对象存储
     * @param array $params
     * @return string
     */
    public function refreshOBStorage(array $params)
    {
        // 判断操作权限
        $this->checkAuthBySourceUuid($params['obs_uuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['OBS']);

        $localnodeuuid = (new Node())->getLocalNodeUUID();//主节点

        // 定义操作名
        $opcodeName = 'FS_PRIVATE_OPERATION_CODE_TARGET_REFRESH';

        // 组合消息
        $msg = [
            'obs_uuid' => $params['obs_uuid']
        ];

        $mbResult = $this->service()->refreshOBStorage($localnodeuuid, $opcodeName, $msg);

        $result = $mbResult['result'];
        $operate = $this->fsOpcode->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }

    /**
     * 获取对象存储自动刷新间隔时间
     *
     * @return void
     */
    public function getAutoRefreshInterval()
    {
        $sql = "select obs_refresh_interval, authorized_flag from bd_system";
        $result = $this->dbSelect($sql);

        $info = array(
            'obs_refresh_interval' => $result[0]['obs_refresh_interval']
        );

        return $info;
    }

    /**
     * 修改对象存储自动刷新间隔时间
     *
     * @param [type] $params
     * @return void
     */
    public function editAutoRefreshInterval($params)
    {
        $refresh = intval($params['refresh_interval']) * 60;
        $sql = "update bd_system set obs_refresh_interval = ?";
        $result = $this->dbExec($sql, array($refresh));

        return $result;
    }

    public function getObsDefaultName(array $params)
    {
        $name = $params['job_name'];

        // 这里判断下任务表里面是否存在同名的任务，存在就在编号前加上1
        $sqlParams = [$name . '%'];
        $sql = "select obs_nickname from obs_resource where obs_nickname like ?";
        $data = $this->dbSelect($sql, $sqlParams);

        if (empty($data)) {
            return ['value' => $name . '1'];
        }

        // 取出任务名后面的编号并降序
        $array = str_replace($name, '', array_column($data, 'obs_nickname'));

        $key = array_map('intval', $array);
        $key = !empty($key) ? max($key) : 0;

        $new = $name . ($key + 1);

        return ['value' => $new];
    }

    /**
     * 
     * 获取对象存储授权信息
     * @return void
     */
    public function getObsLisenceInfo()
    {
        $systemHandler = new \app\v1\system\v0\logic\Index();
        $obsInfo = $systemHandler->getOneModuleLisenceInfo('obs');
        $licenseInfo = $systemHandler->getSystemLicenseType();
        return [
            'obs' => $obsInfo,
            'licensetype' => $licenseInfo['licensetype']
        ];
    }
}