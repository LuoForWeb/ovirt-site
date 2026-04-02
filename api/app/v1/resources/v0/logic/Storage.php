<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\NodeOpcode;
use app\v1\tenant\v0\logic\Index;
use app\v1\user\v0\logic\User;
use app\v1\backupData\v0\service\Service;

/**
 * note          存储管理处理类 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/6 14:14
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Storage extends Base
{
    /**
     * 添加生产存储
     * @param array $params 请求参数
     * @return array
     */
    public function addLunStorage($params = []): array
    {
        // 因为不需要节点信息，并且前端是多选的，所以直接赋值为主节点接口
        $params['node_uuid'] = (new Node())->getMasterNodeUuid();
        return $this->service()->addLunStorageService($params);
    }

    /**
     * 检查存储启动器
     * @param array $params 请求参数
     * @return array
     */
    public function checkInitiator($params = []): array
    {
        if ($params['op_type'] == 1) {
            //如果是修改，密码解密后发送到后台
            $params['password'] = v1_pt_pass_decrypt($params['password']);
        }
        $return = $this->service()->checkInitiator($params);
        // 因为返回的数据是多个节点组合的，所以需要解析处理下
        $check = [];
        $err = [
            763 => xphp_get_lang('UI_STORAGE_LUN_CHECK_INITIATOR_ERROR_CODE763'),
            765 => xphp_get_lang('UI_STORAGE_LUN_CHECK_INITIATOR_ERROR_CODE765'),
            940 => xphp_get_lang('UI_STORAGE_LUN_CHECK_INITIATOR_ERROR_CODE940'),
        ];
        $fail = xphp_get_lang('WEB_PUBLIC_FAILURE');
        $nodes = new Node();
        foreach ($return as $item) {
            if (!$item['return']['result']) {
                // 只要存在错误，就记录
                $check[] = $nodes->getNodeName($item['node_uuid']) . $err[$item['return']['errorCode']] ?? $fail;
            }
        }
        return $check;
    }

    /**
     * 修改生产存储
     * @param array $params 请求参数
     * @return array
     */
    public function editLunStorage($params = []): array
    {
        $sql = "select storage_config,user_uuid from bd_storage_resource where storage_uuid = ?";
        $sqlParams = array($params['storage_uuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);
        $oldPass = json_decode($data[0]['storage_config'], true)['chap_password'];

        if ($oldPass == $params['chap_password']) {
            //没有修改密码
            $password = v1_pt_pass_decrypt($oldPass);
            $params['chap_password'] = $password;
            $params['discover_chap_password'] = $password;
        }
        // 因为不需要节点信息，并且前端是多选的，所以直接赋值为主节点接口
        $params['node_uuid'] = (new Node())->getMasterNodeUuid();
        return $this->service()->editLunStorageService($params);
    }

    /**
     * 同步生产存储
     * @param array $params 请求参数
     * @return array
     */
    public function syncLunStorage($params = []): array
    {

        $storageUuid = $params['storage_uuid_list'][0]['storage_uuid'];
        $sql = "select user_uuid from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, [$storageUuid]);
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);

        $mbResult = $this->service()->syncLunStorageService($params);

        $operate = xphp_get_lang('UI_LUN_STORAGE_SYNC');
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $operate, '', 'info', $mbResult['errorCode']);
        } else {
            //添加成功
            return $this->muOpResult(true, $operate);
        }
    }

    /**
     * 获取生产存储列表
     * @param array $params 请求参数
     * @return array
     */
    public function getLunStorageList($params = []): array
    {

        $start = intval($params['offset']);
        $length = intval($params['limit']);
        $sourceType = intval($params['source_type']);

        $sql = "select bsr.storage_nickname, bsr.storage_uuid, bsr.storage_type,
                bsr.total_size, bsr.free_size, bsr.use_mode, bsr.user_uuid,
                bsr.status, bsr.mount_flag, bsr.error_code, bsr.storage_config, 
                bsr.warning_flag, bsr.warning_type, bsr.warning_value, 
                bn.node_nickname, bn.host_name, bn.ip, bn.node_uuid, bsr.node_uuid as storage_node_uuid 
                from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and 
                bsr.lan_free_flag = ? and bsr.source_type = ?";
        $sqlCount = "select count(bsr.storage_uuid) as total from bd_storage_resource bsr, bd_node bn 
                     where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? and bsr.source_type = ?";

        $accurateFlag = $params['accurate_flag'];
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET'], $sourceType);
        $sqlCountParams = array($flag['UNSET'], $sourceType);

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['source']
            );
            $sqlNew = " and bsr.user_uuid in ({$userUuidSql}) ";
            $sql .= $sqlNew;
            $sqlCount .= $sqlNew;
        }

        if ($accurateFlag) {
            $nickName = $params['nick_name'];
            $nickName = v1_escape_wildcard($nickName);
            $storageType = intval($params['storage_type']);
            $storageStatus = intval($params['storage_status']);
            $nodeuuid = $params['node_uuid'];

            //别名
            if ($this->checkEmpty($nickName)) {
                $sql .= ' and bsr.storage_nickname like ? ';
                $sqlCount .= ' and bsr.storage_nickname like ? ';
                $sqlParams = array_merge($sqlParams, array('%' . $nickName . '%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%' . $nickName . '%'));
            }

            //存储类型
            if (!empty($storageType)) {
                $sql .= ' and bsr.storage_type = ? ';
                $sqlCount .= ' and bsr.storage_type = ? ';
                $sqlParams = array_merge($sqlParams, array($storageType));
                $sqlCountParams = array_merge($sqlCountParams, array($storageType));
            }

            //存储状态
            if (!empty($storageStatus)) {
                $sql .= ' and bsr.status = ? ';
                $sqlCount .= ' and bsr.status = ? ';
                $sqlParams = array_merge($sqlParams, array($storageStatus));
                $sqlCountParams = array_merge($sqlCountParams, array($storageStatus));
            }

            //节点唯一标识
            if (!empty($nodeuuid)) {
                $sql .= ' and bsr.node_uuid = ? ';
                $sqlCount .= ' and bsr.node_uuid = ? ';
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                $sqlCountParams = array_merge($sqlCountParams, array($nodeuuid));
            }
        } else {
            $searchValue = $params['search'];
            $searchValue = v1_escape_wildcard($searchValue);
            //别名
            if ($this->checkEmpty($searchValue)) {
                $sql .= ' and bsr.storage_nickname like ?';
                $sqlCount .= ' and bsr.storage_nickname like ?';
                $sqlParams = array_merge($sqlParams, array('%' . $searchValue . '%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%' . $searchValue . '%'));
            }
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = [
                'num' => 'bsr.storage_id',
                'ip' => 'bsr.storage_uuid',
                'storage_type' => 'bsr.storage_type',
                'sync_time' => 'bsr.storage_type',
                'status' => 'bsr.status'
            ];

            if (!in_array(strtolower($params['order']), [' asc', ' desc'])) {
                $params['order'] = ' desc';
            }

            $params['sort'] = $sortArr[$params['sort']];

            $sql .= " order by " . $params['sort'] . $params['order'];
        }

        $sql .= ' limit ? , ? ';

        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $records = array();
        $records['rows'] = array();
        $i = 1;
        $storageNum = intval($count[0]['total']);
        foreach ($data as $d) {
            $config = json_decode($d['storage_config'], true);
            $records['rows'][] = array(
                'storage_uuid' => $d['storage_uuid'],
                'num' => $i++,
                'storage_nickname' => $d['storage_nickname'],
                'storage_type' => $d['storage_type'],
                'ip' => $config['ip'],
                'status' => $d['status'],
                'config' => $config,
                'login_user' => $config['username'],
                'version' => $config['version'],
                'sync_time' => $config['cbr_refresh_time'],
                'create_user' => $config[''],
                'node_uuid' => $d['storage_node_uuid'],
                'user_uuid' => $d['user_uuid'],
            );
        }

        $records['total'] = $storageNum;

        return $records;
    }

    /**
     * 获取生产存储详情列表
     * @param array $params 请求参数
     * @return array
     */
    public function getLunList($params = []): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $search = $params['search'];

        $sql = "select bsl.lun_uuid, bsl.storage_uuid, bsl.lun_name, bsl.wwn,
                        bsl.lun_type, bsl.capacity, bsl.alloc_capacity 
                from bd_storage_infrastructure_lun bsl where bsl.storage_uuid = ? ";
        $sqlCount = "select count(*) as total from bd_storage_infrastructure_lun bsl 
                    where bsl.storage_uuid = ?";

        $sqlParams = array($params['storage_uuid']);
        $sqlCountParams = array($params['storage_uuid']);

        $searchValue = v1_escape_wildcard($search);
        //别名
        if ($this->checkEmpty($searchValue)) {
            $sql .= ' and bsl.lun_name like ? ';
            $sqlCount .= ' and bsl.lun_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $searchValue . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $searchValue . '%'));
        }
        // }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = ['num' => 'lun_id', 'total_size' => 'capacity', 'free_size' => 'alloc_capacity'];

            $params['sort'] = $sortArr[$params['sort']];

            if (!in_array(strtolower($params['order']), ['asc', 'desc'])) {
                $params['order'] = ' desc';
            }

            $sql .= " order by " . $params['sort'] . ' ' . $params['order'];
        }

        $sql .= ' limit ? , ? ';

        $sqlParams = array_merge($sqlParams, array($start, $length));
        // dump($sql, $sqlParams);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $records = array();
        $records['rows'] = array();
        $i = 1;
        $storageNum = intval($count[0]['total']);
        foreach ($data as $d) {
            $records['rows'][] = array(
                'lun_uuid' => $d['lun_uuid'],
                'num' => $i++,
                'storage_nickname' => $d['storage_nickname'],
                'storage_uuid' => $d['storage_uuid'],
                'wwn' => $d['wwn'],
                'lun_name' => $d['lun_name'],
                'lun_type' => $d['lun_type'],
                'total_size' => v1_calsize($d['capacity'], true),
                'free_size' => v1_calsize($d['alloc_capacity'], true),
            );
        }

        $records['total'] = $storageNum;

        return $records;
    }

    /**
     * 获取生产存储快照详情列表
     * @param array $params 请求参数
     * @return array
     */
    public function getLunSnap($params = []): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $search = $params['search'];

        $sql = "select bss.snapshot_id, bss.snapshot_name, bss.snapshot_uuid, bss.description,
                        bss.wwn, bss.running_status, bss.parent_uuid, 
                bss.storage_uuid, bss.consumed_capacity, bss.time_stamp, bss.detail 
                from bd_storage_infrastructure_snapshot bss where bss.storage_uuid = ? and bss.parent_uuid = ? ";
        $sqlCount = "select count(*) as total from  bd_storage_infrastructure_snapshot bss  
                    where bss.storage_uuid = ? and bss.parent_uuid = ? ";

        $sqlParams = array($params['storage_uuid'], $params['parent_id']);
        $sqlCountParams = array($params['storage_uuid'], $params['parent_id']);

        $searchValue = v1_escape_wildcard($search);
        //别名
        if ($this->checkEmpty($searchValue)) {
            $sql .= ' and bsl.lun_name like ? ';
            $sqlCount .= ' and bsl.lun_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $searchValue . '%'));
            $sqlCountParams = array_merge($sqlCountParams, array('%' . $searchValue . '%'));
        }
        // }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = [
                'status' => 'bss.running_status',
                'total_size' => 'bss.consumed_capacity',
                'free_size' => 'bss.consumed_capacity',
                'create_time' => 'bss.time_stamp'
            ];

            if (!in_array(strtolower($params['order']), ['asc', 'desc'])) {
                $params['order'] = 'desc';
            }

            $sql .= " order by " . $sortArr[$params['sort']] . ' ' . $params['order'];
        }

        $sql .= ' limit ? , ? ';

        $sqlParams = array_merge($sqlParams, array($start, $length));
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);

        $records = array();
        $records['rows'] = array();
        $i = 1;
        $storageNum = intval($count[0]['total']);
        foreach ($data as $d) {
            $records['rows'][] = array(
                'num' => $i++,
                'snapshot_id' => $d['snapshot_id'],
                'snapshot_uuid' => $d['snapshot_uuid'],
                'snapshot_name' => $d['snapshot_name'],
                'storage_uuid' => $d['storage_uuid'],
                'wwn' => $d['wwn'],
                'status' => $d['running_status'],
                'parent_uuid' => $d['parent_uuid'],
                'total_size' => v1_calsize($d['consumed_capacity'], true),
                'free_size' => v1_calsize($d['consumed_capacity'], true),
                'create_time' => $d['time_stamp'],
            );
        }

        $records['total'] = $storageNum;

        return $records;
    }

    /**
     * 得到当前所有节点中存储最大的那个节点存储返回
     * @return array
     */
    public function getMaxStorage(): array
    {
        $sql = "select node_uuid, total_size from bd_storage_resource 
                    where lan_free_flag = ? and use_mode not in (2,3) ";
        $sqlParams = array(xphp_get_config('app', 'FLAG')['UNSET']);
        $data = dbSelect($sql, $sqlParams);

        $data = v1_array_sort($data, 'node_uuid', '', 0, -1);
        $node = array();
        $storageList = array();
        $storageLists = array();
        $nodeStorage = array();
        foreach ($data as $d) {
            if (!in_array($d['node_uuid'], $node)) {
                $node[] = $d['node_uuid'];
                if (!empty($storageList)) {
                    $storageLists[] = $storageList;
                    $storageList = array();
                }
            }
            $storageList[] = $d['total_size'];
        }
        $storageLists[] = $storageList;
        foreach ($storageLists as $s) {
            $nodeStorage[] = array_sum($s);
        }
        asort($nodeStorage);
        $maxSpace = array_sum($nodeStorage);
        //租户内计算租户的配额
        $user = xphp_get_user_info();
        if (!empty($user['tenantuuid'])) {
            $settings = (new Index())->pGetTenantSettings(xphp_get_user_info()['tenantuuid']);
            $maxSpace = empty($maxSpace) ? 0 : $settings['quota_size'];
        } else {
            //租户外检查是否为容量授权
            $liceseTypeInfo = (new \app\v1\system\v0\logic\Index())->getSystemLicenseType();
            $liceseType = $liceseTypeInfo['licensetype'];
            //容量授权配额按容量授权总大小显示
            if ($liceseType == xphp_get_config('auth', 'LISENCE_INFO')['type']['storage']) {
                $maxSpace = $liceseTypeInfo['storage_size'];
            }
        }
        if ($maxSpace == -1) {
            $result = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
        } else {
            $result = v1_calsize($maxSpace, true);
        }
        return [
            'svalue' => $maxSpace,
            'stext' => $result,
        ];
    }

    /**
     * 获取操作名
     * @param $opCode opccode
     * @return string
     */
    public function getUnifyOpcodeDes($opCode)
    {
        $nodeOpcode = new NodeOpcode();
        return $nodeOpcode->getOpcodeDes($opCode);
    }

    /**
     * 得到存储列表信息
     * @param array $params 数据
     * @return array
     */
    public function getStorageList($params = []): array
    {

        $cloudFlag = $params['cloud_flag'];
        $copyFlag = $params['copy_flag'];
        $lanFreeFlag = $params['lan_free_flag'];
        $start = $params['offset'];
        $length = $params['limit'];
        $sourceType = $params['source_type'];
        $search = $params['search'];

        $subSql = "SELECT
                    	bnsnl.storage_uuid,
                    	bn1.node_nickname,
                    	bn1.host_name,
                    	bn1.ip,
                    	bn1.node_uuid AS node_uuid,
                    	GROUP_CONCAT(
                    	IF
                    		(
                    			ISNULL( bn1.node_nickname ) 
                    			OR bn1.node_nickname = '' 
                    			OR bn1.node_nickname = bn1.ip,
                    			CONCAT( bn1.host_name, '(', bn1.ip, ')' ),
                    			CONCAT( bn1.node_nickname, '(', bn1.ip, ')' ) 
                    		) SEPARATOR '<br>' 
                    	) AS all_node_name,
                        GROUP_CONCAT(bn1.node_uuid) AS all_node_uuid
                    FROM
                    	`bd_shared_storage_node_layout` bnsnl
                    	INNER JOIN bd_node bn1 ON bnsnl.node_uuid = bn1.node_uuid 
                    GROUP BY
                    	bnsnl.storage_uuid ";

        $allStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $nasTypes = '(' . $allStorageType['NFS'] . ', ' . $allStorageType['CIFS'] . ')';
        $sql = "select distinct bsr.storage_uuid, bsr.storage_nickname, bsr.storage_type,
                bsr.total_size, bsr.free_size, bsr.use_mode, bsr.user_uuid,
                bsr.worm_flag,bsr.worm_allocate_type,bsr.worm_allocate_value,
                bsr.status, bsr.mount_flag, bsr.error_code, bsr.storage_config, 
                bsr.warning_flag, bsr.warning_type, bsr.warning_value, 
                bnsnl.all_node_name, bnsnl.all_node_uuid,
                IF (bsr.storage_type IN $nasTypes, bnsnl.node_nickname, bn.node_nickname) AS node_nickname,
                IF (bsr.storage_type IN $nasTypes, bnsnl.host_name, bn.host_name) AS host_name,
                IF (bsr.storage_type IN $nasTypes, bnsnl.ip, bn.ip) AS ip,
                IF (bsr.storage_type IN $nasTypes, bnsnl.node_uuid, bsr.node_uuid) AS node_uuid
                from bd_storage_resource bsr
                    LEFT JOIN bd_node bn ON bn.node_uuid = bsr.node_uuid
                    LEFT JOIN ($subSql) bnsnl ON bnsnl.storage_uuid = bsr.storage_uuid ";
        $sqlCount = "select count(distinct bsr.storage_uuid) as total
                    from bd_storage_resource bsr
                        LEFt JOIN bd_node bn ON bn.node_uuid = bsr.node_uuid";

        $left = '';
        $where = ' where bsr.lan_free_flag = ? ';

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'],
                'bsr.storage_uuid'
            );
            $sqlNew = " and ({$resourceUuidSql}) ";
            $where .= $sqlNew;
        }

        $accurateFlag = $params['accurate_flag'];
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = array($flag['UNSET']);
        if ($lanFreeFlag) {
            $sqlParams = array($flag['SET']);
        }
        if (!empty($sourceType)) {
            $where .= 'and bsr.source_type = ?';
            $sqlParams = array_merge($sqlParams, array($sourceType));
        }

        if ($accurateFlag) {
            $nickName = $params['nick_name'];
            $nickName = v1_escape_wildcard($nickName);
            $storageType = intval($params['storage_type']);
            $storageStatus = intval($params['storage_status']);
            $nodeuuid = $params['node_uuid'];

            //别名
            if ($this->checkEmpty($nickName)) {
                $where .= ' and bsr.storage_nickname like ? ';
                $sqlParams = array_merge($sqlParams, array('%' . $nickName . '%'));
            }

            //存储类型
            if (!empty($storageType)) {
                $where .= ' and bsr.storage_type = ? ';
                $sqlParams = array_merge($sqlParams, array($storageType));
            }

            //存储状态
            if (!empty($storageStatus)) {
                $where .= ' and bsr.status = ? ';
                $sqlParams = array_merge($sqlParams, array($storageStatus));

                // 在线未挂载才是真实的未挂载
                // 离线未挂载显示未离线
                if ($storageStatus == 1) {
                    $left = ' join bd_module_server bms on bn.node_uuid = bms.node_uuid
                     and bms.online_flag = ' . $flag['SET'];
                    $where .= ' and bsr.mount_flag = 1 ';
                }
                if ($storageStatus == 3) {
                    $left = ' join bd_module_server bms on bn.node_uuid = bms.node_uuid ';
                    $where .= ' and ((bms.online_flag = ' . $flag['UNSET'] . ' and bsr.storage_type != 9) 
                                or bsr.status = 3)';
                } elseif ($storageStatus == 4) {
                    // 未挂载显示
                    $where .= ' and bsr.status = 1 and bsr.mount_flag = 2 ';
                } else {
                    $where .= ' and bsr.status = ? ';
                    $sqlParams = array_merge($sqlParams, array($storageStatus));
                }
            }

            //节点唯一标识
            if (!empty($nodeuuid)) {
                $where .= ' and bsr.node_uuid = ? ';
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
                // NAS查bd_nas_storage_node_layout.node_uuid
                $nasSql = "SELECT storage_uuid FROM bd_shared_storage_node_layout WHERE node_uuid = ? ";
                $nasData = $this->dbSelect($nasSql, [$nodeuuid]);
                if (is_array($nasData) && $nasData) {
                    $nasStorageUuids = "'" . implode("', '", array_column($nasData, 'storage_uuid')) . "'";
                    $where .= " AND (bsr.node_uuid = ? OR bsr.storage_uuid IN ($nasStorageUuids) )";
                    $sqlParams = array_merge($sqlParams, array($nodeuuid));
                }
            }
        } else {
            if (isset($search) && $search) {
                $searchValue = v1_escape_wildcard($search);
                //别名
                if ($this->checkEmpty($searchValue)) {
                    $where .= ' and bsr.storage_nickname like ? ';
                    $sqlParams = array_merge($sqlParams, array('%' . $searchValue . '%'));
                }
            }
        }

        //异地备份系统页面
        $bdstorageTypeArr = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        if ($copyFlag) {
            $where .= ' and bsr.storage_type = ? ';
            $sqlParams = array_merge($sqlParams, array($bdstorageTypeArr['REMOTE']));
        } elseif ($cloudFlag) {
            //云存储页面
            $where .= ' and bsr.storage_type = ? ';
            $sqlParams = array_merge($sqlParams, array($bdstorageTypeArr['CLOUD']));
        }

        if (isset($params['use_mode']) && $params['use_mode']) {
            $where .= ' AND bsr.use_mode = ? ';
            $sqlParams = array_merge($sqlParams, [(int) $params['use_mode']]);
        }

        // 存储资源池类型
        if (isset($params['storage_pool_type'])) {
            $storagePoolTypeMap = xphp_get_config('storage', 'STORAGE_POOL_TYPE_MAP');
            $storageTypeList = $storagePoolTypeMap[(int) $params['storage_pool_type']];
            $storageTypes = "'" . implode("', '", $storageTypeList) . "'";
            $where .= " AND bsr.storage_type IN ($storageTypes) ";
        }

        // 排除的存储类型
        if (isset($params['exclude_storage_type_list']) && $params['exclude_storage_type_list']) {
            $excludeStorageTypes = "'" . implode("', '", $params['exclude_storage_type_list']) . "'";
            $where .= " AND bsr.storage_type NOT IN ($excludeStorageTypes) ";
        }

        // 磁带库是否可用
        if (isset($params['tape_available_flag']) && $params['tape_available_flag'] == 1) {
            // 不可用的磁带组为: 1.磁带策略为总是一个 2.备份集为冻结状态
            $tapeSql = "SELECT btg.group_uuid
                        FROM bd_tape_group btg
                            INNER JOIN bd_tape_backup_set btbs ON btg.group_uuid = btbs.group_uuid
                        WHERE btg.backup_set_strategy_type = 1 AND btbs.freeze_flag = 1 ";
            $where .= " AND (bsr.storage_type != ? OR bsr.storage_type = ? AND bsr.storage_uuid NOT IN ($tapeSql) )";
            $sqlParams[] = $allStorageType['TAPE'];
            $sqlParams[] = $allStorageType['TAPE'];
        }

        if (!empty($lanFreeFlag)) {
            // lanfee 存储 （和备份存储不一样，没得资源池的说法）
            $paramss = $params;
            $paramss['count_lanfree'] = 1;
            $lanfee = $this->getStorageLanfee($paramss);
            $count = $this->dbSelect($lanfee['sql'], $lanfee['sqlParams']);
        } else {
            $count = $this->dbSelect($sqlCount . $left . $where, $sqlParams);
        }

        $storageNum = intval($count[0]['total']);
        if (empty($storageNum)) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sortFields = [
            'storage_nickname' => 'bsr.storage_nickname',
            'storage_type' => 'bsr.storage_type',
            'node_des' => 'node_nickname',
            'node_status' => 'bsr.status',
            'total_size' => 'bsr.total_size',
            'free_size' => 'bsr.free_size',
            'storage_status' => 'bsr.error_code',
            'use_mode' => 'bsr.use_mode',
        ];


        $order = strtoupper($params['order'] ?? 'desc') == 'ASC' ? 'ASC' : 'DESC';

        $sort = empty($sortFields[$params['sort']])
            ? ' bsr.storage_id desc' : ($sortFields[$params['sort']] . ' ' . $order . ', bsr.storage_id desc');

        $where .= " order by $sort " ;

        $where .= ' limit ? , ? ';
        $sqlParams = array_merge($sqlParams, array($start, $length));

        if (!empty($lanFreeFlag)) {
            // lanfee 存储 （和备份存储不一样，没得资源池的说法）
            $lanfee = $this->getStorageLanfee($params);
            $data = $this->dbSelect($lanfee['sql'], $lanfee['sqlParams']);
        } else {
            $data = $this->dbSelect($sql . $left . $where, $sqlParams);
        }

        $records = [
            'rows' => [],
            'total' => $storageNum
        ];
        // 查询存储资源池
        $storageUuidList = array_column($data, 'storage_uuid');
        $storagePoolMap = (new StoragePool())->buildStoragePoolInfoWithStorageUuid($storageUuidList);

        $nodeHandler = new Node();
        $i = $params['offset'] + 1;
        $ptDes = xphp_get_desc('Pf', 'STORAGESTATUS');

        foreach ($data as $d) {
            $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);

            if (xphp_get_config('resource', 'STORAGEWARNINGTYPE')['SIZE'] == $d['warning_type']) {
                //如果是按照大小来告警
                $warningValue = v1_calsize($d['warning_value'], true);
            } else {
                $warningValue = $d['warning_value'] . '%';
            }

            if (xphp_get_config('resource', 'STORAGEWORMTYPE')['SIZE'] == $d['worm_allocate_type']) {
                //如果是按照大小来
                $wormValue = v1_calsize($d['worm_allocate_value'], true);
            } elseif (xphp_get_config('resource', 'STORAGEWORMTYPE')['PERCENT'] == $d['worm_allocate_type']) {
                //如果是按照百分比来
                $wormValue = $d['worm_allocate_value'] . '%';
            } else {
                // 无限制
                $wormValue = xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED');
            }

            $nodeback = $this->getNodeStatus($d);

            $nodeStatus = $nodeback[0];
            $nodeDes = $nodeback[1];
            $storageConfig = json_decode($d['storage_config'], true);

            //异地备份系统节点显示
            if ($d['storage_type'] == $bdstorageTypeArr['REMOTE']) {
                $nodeDes = !empty($storageConfig['remote_name'])
                    ? ($storageConfig['remote_name'] . '(' . $storageConfig['remote_ip'] . ')')
                    : $storageConfig['remote_ip'];
                $nodeStatus = v1_parse_flag_to_bool(intval($storageConfig['remote_status']));
            }
            // 如果是云存储和CBR存储 那么节点和节点状态都是 --
            if (in_array($d['storage_type'], [$bdstorageTypeArr['CLOUD'], $bdstorageTypeArr['HUAWEI_CBR']])) {
                $nodeDes = $nodeStatus = '--';
            }

            //华为CBR存储
            if ($d['storage_type'] == $bdstorageTypeArr['HUAWEI_CBR']) {
                $refreshflag = v1_parse_flag_to_bool(intval($storageConfig['cbr_refresh_flag']));
                $refreshtime = intval($storageConfig['cbr_refresh_interval']) / 60; //转化成分钟
            }
            $flag = $this->getStorageStatus(
                $nodeAllStatus,
                intval($d['status']),
                intval($d['mount_flag']),
                $d['storage_type']
            );
            $desc = $ptDes[$flag] ?? '';

            // 存储资源池
            $storagePoolList = $storagePoolMap[$d['storage_uuid']] ?? [];
            $mountList = $this->getNasMountPointList($d['storage_uuid'], (int) $d['storage_type']);
            // 判断下共享存储的所有挂载点是否是正常的，如果有离线的，需要给存储名称一个告警标识
            $mountFlag = true;
            if (!empty($mountList)) {
                foreach ($mountList as $items) {
                    if ($items['mount_status'] != 1) {
                        $mountFlag = false;
                        break;
                    }
                }
            }
            $records['rows'][] = [
                'storage_uuid' => $d['storage_uuid'],
                'num' => $i++,
                'storage_nickname' => $d['storage_nickname'],
                'storage_type' => $d['storage_type'],
                'node' => $nodeDes,
                'status' => $nodeStatus,
                'mount_flag' => $mountFlag,
                'total_size' => v1_calsize($d['total_size'], true),
                'free_size' => v1_calsize($d['free_size'], true),
                'total_size_value' => $d['total_size'],
                'free_size_value' => $d['free_size'],
                'flag' => $flag,
                'desc' => $desc,
                'use_mode' => intval($d['use_mode']),
                'use_mode_des' => $this->getUsemodeDes(intval($d['use_mode'])),
                'use_mode_value' => $d['use_mode'],
                'config' => $this->getStorageConfig($d['storage_type'], $d['storage_config'], $d['storage_uuid']),
                'nodedes' => $nodeHandler->getOffLineModuleDes($nodeAllStatus['module']),    //不在线的模块进程
                'warning' => [
                    'flag' => v1_parse_flag_to_bool($d['warning_flag']),
                    'type' => $d['warning_type'],
                    'value' => $warningValue,
                ],
                // 新增worm显示
                'worm' => [
                    'flag' => v1_parse_flag_to_bool($d['worm_flag']),
                    'type' => $d['worm_allocate_type'],
                    'value' => $wormValue,
                ],
                //新增自动刷新flag
                'refresh' => [
                    'refresh_flag' => $refreshflag ?? false,
                    'refresh_time' => $refreshtime ?? 0
                ],
                // 新增自动导入时间点标记
                'autoscan_flag' => v1_parse_flag_to_bool($storageConfig['timepoint_auto_scan_flag']), // 自动扫描
                'allocate_flag' => v1_parse_flag_to_bool($storageConfig['timepoint_auto_assgin_flag']), // 自动分配
                'storage_pool_name' => array_column($storagePoolList, 'storage_pool_nickname'),
                'storage_pool_list' => $storagePoolList,
                'mount_point_list' => $mountList,
                'node_ip' => $d['ip'],
                'node_uuid' => $d['node_uuid'],
                'user_uuid' => $d['user_uuid'],
            ];
        }

        return $records;
    }

    /**
     * 组装lanfree存储的sql
     * @param array $params 数据
     * @return array
     */
    private function getStorageLanfee($params = [])
    {
        $cloudFlag = $params['cloud_flag'];
        $copyFlag = $params['copy_flag'];
        $start = $params['offset'];
        $length = $params['limit'];
        $sourceType = $params['source_type'];

        $field = 'distinct bsr.storage_uuid, bsr.storage_nickname, bsr.storage_type,
                bsr.total_size, bsr.free_size, bsr.use_mode, bsr.user_uuid,
                bsr.status, bsr.mount_flag, bsr.error_code, bsr.storage_config, 
                bsr.warning_flag, bsr.warning_type, bsr.warning_value, 
                bn.node_nickname, bn.host_name, bn.ip, bn.node_uuid';

        $tables = ' from bd_storage_resource bsr, bd_node bn';

        $sql = "select {$field} {$tables}";
        $sqlCount = "select count(distinct bsr.storage_uuid) total {$tables}";

        $left = '';
        $where = ' where bsr.node_uuid = bn.node_uuid and bsr.lan_free_flag = ? ';

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['source']
            );
            $sqlNew = " and bsr.user_uuid in ({$userUuidSql}) ";
            $where .= $sqlNew;
        }

        $accurateFlag = $params['accurate_flag'];
        $flag = xphp_get_config('app', 'FLAG');

        $sqlParams = array($flag['SET']);

        if (!empty($sourceType)) {
            $where .= 'and bsr.source_type = ?';
            $sqlParams = array_merge($sqlParams, array($sourceType));
        }

        if ($accurateFlag) {
            $nickName = $params['nick_name'];
            $nickName = v1_escape_wildcard($nickName);
            $storageType = intval($params['storage_type']);
            $storageStatus = intval($params['storage_status']);
            $nodeuuid = $params['node_uuid'];

            //别名
            if ($this->checkEmpty($nickName)) {
                $where .= ' and bsr.storage_nickname like ? ';
                $sqlParams = array_merge($sqlParams, array('%' . $nickName . '%'));
            }

            //存储类型
            if (!empty($storageType)) {
                $where .= ' and bsr.storage_type = ? ';
                $sqlParams = array_merge($sqlParams, array($storageType));
            }

            //存储状态
            if (!empty($storageStatus)) {
                $where .= ' and bsr.status = ? ';
                $sqlParams = array_merge($sqlParams, array($storageStatus));

                // 在线未挂载才是真实的未挂载
                // 离线未挂载显示未离线
                if ($storageStatus == 1) {
                    $left = ' join bd_module_server bms on bn.node_uuid = bms.node_uuid
                     and bms.online_flag = ' . $flag['SET'];
                    $where .= ' and bsr.mount_flag = 1 ';
                }
                if ($storageStatus == 3) {
                    $left = ' join bd_module_server bms on bn.node_uuid = bms.node_uuid ';
                    $where .= ' and ((bms.online_flag = ' . $flag['UNSET'] . ' and bsr.storage_type != 9) 
                                or bsr.status = 3)';
                } elseif ($storageStatus == 4) {
                    // 未挂载显示
                    $where .= ' and bsr.status = 1 and bsr.mount_flag = 2 ';
                } else {
                    $where .= ' and bsr.status = ? ';
                    $sqlParams = array_merge($sqlParams, array($storageStatus));
                }
            }

            //节点唯一标识
            if (!empty($nodeuuid)) {
                $where .= ' and bsr.node_uuid = ? ';
                $sqlParams = array_merge($sqlParams, array($nodeuuid));
            }
        } else {
            $searchValue = $params['search'];
            $searchValue = v1_escape_wildcard($searchValue);
            //别名
            if ($this->checkEmpty($searchValue)) {
                $where .= ' and bsr.storage_nickname like ? ';
                $sqlParams = array_merge($sqlParams, array('%' . $searchValue . '%'));
            }
        }

        //异地备份系统页面
        $bdstorageTypeArr = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        if ($copyFlag) {
            $where .= ' and bsr.storage_type = ? ';
            $sqlParams = array_merge($sqlParams, array($bdstorageTypeArr['REMOTE']));
        } elseif ($cloudFlag) {
            //云存储页面
            $where .= ' and bsr.storage_type = ? ';
            $sqlParams = array_merge($sqlParams, array($bdstorageTypeArr['CLOUD']));
        }

        if (isset($params['use_mode']) && $params['use_mode']) {
            $where .= ' AND bsr.use_mode = ? ';
            $sqlParams = array_merge($sqlParams, [(int) $params['use_mode']]);
        }

        if (!empty($params['count_lanfree'])) {
            return [
                'sql' => $sqlCount . $left . $where,
                'sqlParams' => $sqlParams
            ];
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = [
                'storage_nickname' => 'storage_nickname',
                'storage_type' => 'storage_type',
                'node' => 'node_nickname',
                'status' => 'status',
                'total_size' => 'total_size',
                'free_size' => 'free_size',
                'flag' => 'error_code',
                'use_mode' => 'use_mode'
            ];

            if (!in_array($sortArr[$params['sort']], $sortArr)) {
                $params['sort'] = 'storage_uuid';
            }

            if ($params['sort'] == 'node') {
                $params['sort'] = 'bn.' . $sortArr[$params['sort']];
            } else {
                $params['sort'] = 'bsr.' . $sortArr[$params['sort']];
            }

            if (!in_array(strtolower($params['order']), ['asc', 'desc'])) {
                $params['order'] = 'desc';
            }

            $where .= " order by " . $params['sort'] . ' ' . $params['order'];
        }

        $where .= ' limit ? , ? ';
        $sqlParams = array_merge($sqlParams, array($start, $length));

        return [
            'sql' => $sql . $left . $where,
            'sqlParams' => $sqlParams
        ];
    }

    /**
     * 获取存储池的名称
     * @param string $storageUuid 存储uuid
     * @return array
     */
    private function getStoragePoolNameList(string $storageUuid): array
    {
        $sql = "SELECT bsrp.storage_pool_uuid, bsrp.storage_pool_type, bsrp.storage_pool_nickname
                FROM bd_storage_resource_pool bsrp
                    INNER JOIN bd_storage_resource_pool_list bsrpl ON bsrp.storage_pool_uuid = bsrpl.storage_pool_uuid
                WHERE bsrpl.storage_uuid = ? GROUP BY bsrp.storage_pool_uuid ";
        $data = $this->dbSelect($sql, [$storageUuid]);
        if (!$data || !is_array($data)) {
            return [];
        }
        return array_values(array_unique(array_column($data, 'storage_pool_nickname')));
    }

    /**
     * 获取NAS设备的挂载点
     * @param string $storageUuid 存储uuid
     * @param int    $storageType 存储类别
     * @return array
     */
    public function getNasMountPointList(string $storageUuid, int $storageType): array
    {
        $allStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $mountPointList = [];
        if (
            $storageType != $allStorageType['NFS'] &&
            $storageType != $allStorageType['CIFS'] &&
            $storageType != $allStorageType['CLOUD']
        ) {
            return $mountPointList;
        }
        $sql = "SELECT bnsnl.mount_flag, bnsnl.status, bnsnl.storage_uuid, bnsnl.mount_point,
                    bn.node_uuid, bn.host_name, bn.ip,bn.node_nickname,bn.node_type
                FROM bd_shared_storage_node_layout bnsnl
                    INNER JOIN bd_node bn ON bn.node_uuid = bnsnl.node_uuid
                WHERE bnsnl.storage_uuid = ? ";
        $data = $this->dbSelect($sql, [$storageUuid]);
        if (!$data || !is_array($data)) {
            return $mountPointList;
        }
        foreach ($data as $item) {
            $nodeName = $item['host_name'];
            if ($item['ip'] !== $item['node_nickname'] && $item['node_nickname']) {
                $nodeName = $item['node_nickname'];
            }

            $mountPointList[$item['node_uuid']] = [
                'mount_flag' => (int) $item['mount_flag'],
                'mount_point' => $item['mount_point'],
                'mount_status' => (int) $item['status'],
                'node_uuid' => $item['node_uuid'],
                'node_ip' => $item['ip'],
                'node_name' => $nodeName,
            ];
        }
        return array_values($mountPointList);
    }

    /**
     * 得到存储详情
     * @param string $storagesuuid 资源uuid
     * @return array
     */
    public function getStorageDetail(string $storagesuuid)
    {

        $sql = "select bsr.storage_nickname, bsr.storage_uuid, bsr.storage_type,
                bsr.total_size, bsr.free_size, bsr.use_mode, 
                bsr.status, bsr.mount_flag, bsr.error_code, bsr.storage_config, 
                bsr.warning_flag, bsr.warning_type, bsr.warning_value,
                bsr.worm_flag, bsr.worm_allocate_type, bsr.worm_allocate_value,
                bn.node_nickname, bn.host_name, bn.ip, bn.node_uuid  
                from bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and bsr.storage_uuid = ?";

        $data = $this->dbSelect($sql, [$storagesuuid]);

        if (empty($data)) {
            return false;
        }
        $d = $data[0];

        $nodeHandler = new Node();
        $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);

        if (xphp_get_config('resource', 'STORAGEWARNINGTYPE')['SIZE'] == $d['warning_type']) {
            //如果是按照大小来告警
            $warningValue = v1_calsize($d['warning_value']);
        } else {
            $warningValue = $d['warning_value'];
        }

        if (xphp_get_config('resource', 'STORAGEWORMTYPE')['SIZE'] == $d['worm_allocate_type']) {
            //如果是按照大小来告警 因为页面只有单位是 gb
            $wormValue = $d['worm_allocate_value'] / 1024 / 1024 / 1024;
        } else {
            $wormValue = $d['worm_allocate_value'];
        }

        $nodeback = $this->getNodeStatus($d);
        $storageConfig = json_decode($d['storage_config'], true);

        $storageType = xphp_get_config('storage', 'BD_STORAGE_TYPE');
        if (in_array($d['storage_type'], [$storageType['NFS'], $storageType['CIFS'], $storageType['CLOUD']])) {
            // 如果是共享存储，还需要在bd_task表查询下是否有使用
            $task = $this->dbSelect('select count(*) num from bd_task where storage_uuid = ?', [$d['storage_uuid']]);
        }
        return [
            'storage_uuid' => $d['storage_uuid'],
            'storage_nickname' => html_entity_decode($d['storage_nickname'], ENT_QUOTES, 'UTF-8'),
            'storage_type' => $d['storage_type'],
            'node' => $nodeback[1],
            'status' => $nodeback[0],
            'total_size' => v1_calsize($d['total_size']),
            'free_size' => v1_calsize($d['free_size']),
            'flag' => $this->getStorageStatus($nodeAllStatus, intval($d['status']), intval($d['flag'])),
            'use_mode_value' => $this->getUsemodeDes(intval($d['use_mode'])),
            'use_mode' => intval($d['use_mode']),
            'config' => $this->getStorageConfig($d['storage_type'], $d['storage_config'], $d['storage_uuid']),
            'nodedes' => $nodeHandler->getOffLineModuleDes($nodeAllStatus['module']),    //不在线的模块进程
            'warning' => array(
                'flag' => v1_parse_flag_to_bool($d['warning_flag']),
                'type' => $d['warning_type'],
                'value' => $warningValue,
            ),
            'worm' => array(
                'flag' => v1_parse_flag_to_bool($d['worm_flag']),
                'type' => $d['worm_allocate_type'],
                'value' => $wormValue,
            ),
            'scandata_flag' => v1_parse_flag_to_bool($storageConfig['cbr_refresh_flag']),
            'scandata_value' => intval($storageConfig['cbr_refresh_interval']) / 60,
            'allocate_flag' => v1_parse_flag_to_bool($storageConfig['timepoint_auto_assgin_flag']), // 自动分配
            'autoscan_flag' => v1_parse_flag_to_bool($storageConfig['timepoint_auto_scan_flag']), // 自动扫描
            'remote_ip' => $storageConfig['remote_ip'] ?? '',
            'remote_port' => $storageConfig['remote_port'] ?? '',
            'in_task' => !empty($task[0]['num'])
        ];
    }

    /**
     * 得到存储设备自动导入时间点时间
     * @param array $params 请求参数
     * @return array
     */
    public function getImportRefreshTime(array $params): array
    {
        $sql = "select timepoint_import_interval from bd_system";
        $result = $this->dbSelect($sql);
        return [
            'value' => intval($result[0]['timepoint_import_interval']) / 60,
        ];
    }

    /**
     * 设置存储设备自动导入时间点时间
     * @param array $params 请求参数
     * @return bool
     */
    public function setImportRefreshTime(array $params): bool
    {
        $refreshvalue = intval($params['value']) * 60;
        $sql = "update bd_system set timepoint_import_interval = ?";
        return $this->dbExec($sql, array($refreshvalue));
    }

    /**
     * 添加存储设备1,2,3,4,5,6,7 及 11本地目录 101华为NFS 102华为cifs和16dddb
     * @param array $params 请求数组
     * @return array
     */
    public function addNewStorage($params = []): array
    {
        // 密码还原
        $params['password'] = html_entity_decode($params['password']);
        $nodeuuid = $params['node_uuid'];
        $storageType = $params['storage_type'];
        $rawDevPath = $params['storage_name'];
        $nickname = $params['rname'];
        $usemode = $params['use_mode'];
        $mountparams = $params['mount_params'];
        $warningSetting = $params['warning_setting'];
        $wormSetting = $params['worm_setting'];
        $formatFlag = v1_parse_bool_to_flag(boolval($params['format']));
        $importFlag = v1_parse_bool_to_flag(boolval($params['import']));
        $lanfreeFlag = v1_parse_bool_to_flag(false);
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        $check = $this->checkStorageName($nickname, $lanfreeFlag); //检查存储名是否重复
        if ($check) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_STORAGE_NICKNAME_EXIST_ERROR')
            ];
        }
        $bdStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        switch ($storageType) {
            case $bdStorageType['DISK']: // 1
                // no break
            case $bdStorageType['LVM']: // 2
                // no break
            case $bdStorageType['PARTITION']: // 3
                // no break
            case $bdStorageType['FC']: // 4
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'raw_dev_path' => $rawDevPath,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams
                );
                $opName = 'NODE_SR_OP_ADD_DISK_PART_LVM';
                break;
            case $bdStorageType['ISCSI']: // 5
                $iscsiTargetList = [];
                foreach ($params['server_list'] as $server) {
                    // 这里判断处理下是否是ipv6，是的话，就加上中括号
                    if (filter_var($server['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        // 是 ipv6
                        $server['ip'] = '[' . inet_ntop(inet_pton($server['ip'])) . ']'; // 获取缩写后的ipv6
                    }
                    $iscsiTargetList[] = array(
                        'iscsi_target' => $server['ip'] . ':' . $server['port']
                    );
                }
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'nickname' => $nickname,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'raw_dev_path' => $params['lun'],
                    'iscsi_target_list' => $iscsiTargetList,
                    'iscsi_target' => $params['iscsi_target'], // 选择的ip和端口
                    'target_iqn' => $params['target_iqn'], // 选择的iqn
                    'chap_username' => $params['chap_username'] ?? '', //用户如果输入了chap认证就写，没有就为空
                    'chap_password' => $params['chap_password'] ?? '',
                    'discover_chap_username' => $params['discover_chap_username'] ?? '', //用户如果输入了门户chap认证就写，没有就为空
                    'discover_chap_password' => $params['discover_chap_password'] ?? '',
                    'logout_iscsi_list' => $params['logout_iscsi_list'] ?? [], // 未选择的iscsi列表
                    'username' => '',
                    'password' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams
                );
                $opName = 'NODE_SR_OP_ADD_ISCSI_DISK';
                break;
            case $bdStorageType['NFS']: // 6
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams,
                    'node_uuid_list' => $params['node_list']
                );
                $nodeuuid = $params['node_list'][0];
                $opName = 'NODE_SR_OP_ADD_NFS';
                break;
            case $bdStorageType['CIFS']: // 7
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'username' => $params['username'],
                    'passwd' => $params['password'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams,
                    'node_uuid_list' => $params['node_list']
                );
                $nodeuuid = $params['node_list'][0];
                $opName = 'NODE_SR_OP_ADD_CIFS';
                break;
            case $bdStorageType['LOCALDIR']: // 11
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'raw_dev_path' => $params['dirname'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => ''
                );
                $opName = 'NODE_SR_OP_ADD_DIR';
                break;
            case $bdStorageType['DDDB']: // 16
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'data_domain_system' => $params['data_domain_system'],
                    'storage_unit' => $params['storage_unit'],
                    'auth_type' => $params['auth_type'],
                    'username' => $params['username'],
                    'password' => $params['password'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams,
                );
                $opName = 'NODE_SR_OP_ADD_DDBOOST';
                break;
            default:
                return [
                    'code' => 1,
                    'msg' => xphp_get_lang('WEB_ERROR_BD_UNKNOW_STORAGE_TYPE_ERROR')
                ]; // 不在范围内 返回错误
        }
        //添加告警配置
        $msg = array_merge($msg, $this->getWarnningRule($warningSetting));
        // 添加worm配置
        $msg = array_merge($msg, $this->getWormRule($wormSetting));

        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg));
        $operate = $this->getUnifyOpcodeDes($opName);
        if ($mbResult['result']) {
            //返回结果到UI
            return $this->muOpResult(true, $operate, $mbResult['msg']);
        }

        //如果是超时,处理存储过大会创建很久的问题.
        $timeoutCode = v1_get_error_num('PF_SOCKET_GET_OP_TIMEOUT');
        if (intval($mbResult['errorCode']) == $timeoutCode) {
            return $this->muOpResult(true, $operate, xphp_get_lang('WEB_STORAGE_CREATE_TO_WAIT'));
        }
        return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
    }

    /**
     * 添加存储设备之nas系列11,101,102,16等test
     * @param array $params 请求数组
     * @return array
     */
    public function addNasStorage($params = []): array
    {
        // 密码还原
        $params['password'] = html_entity_decode($params['password']);
        $nodeuuid = $params['node_uuid'];
        $storageType = $params['storage_type'];
        $nickname = $params['rname'];
        $usemode = $params['use_mode'];
        $mountparams = $params['mount_params'] ?? '';
        $warningSetting = $params['warning_setting'];
        $wormSetting = $params['worm_setting'];
        $formatFlag = v1_parse_bool_to_flag(boolval($params['format']));
        $importFlag = v1_parse_bool_to_flag(boolval($params['import']));
        $lanfreeFlag = v1_parse_bool_to_flag(false);
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        $check = $this->checkStorageName($nickname, $lanfreeFlag); //检查存储名是否重复
        if ($check) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_STORAGE_NICKNAME_EXIST_ERROR')
            ];
        }
        $bdStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        switch ($storageType) {
            case $bdStorageType['NFS']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams,
                    'node_uuid_list' => $params['node_list']
                );
                $nodeuuid = $params['node_list'][0];
                $opName = 'NODE_SR_OP_TEST_NFS';
                break;
            case $bdStorageType['CIFS']:
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'username' => $params['username'],
                    'passwd' => $params['password'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams,
                    'node_uuid_list' => $params['node_list']
                );
                $nodeuuid = $params['node_list'][0];
                $opName = 'NODE_SR_OP_TEST_CIFS';
                break;
            case $bdStorageType['LOCALDIR']: // 11
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'raw_dev_path' => $params['dirname'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => ''
                );
                $opName = 'NODE_SR_OP_TEST_DIR';
                break;
            case $bdStorageType['DDDB']: // 16
                $msg = array(
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'data_domain_system' => $params['data_domain_system'],
                    'storage_unit' => $params['storage_unit'],
                    'auth_type' => $params['auth_type'],
                    'username' => $params['username'],
                    'password' => $params['password'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
                    'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
                    'options' => '',
                    'usemode' => $usemode,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => ''
                );
                $opName = 'NODE_SR_OP_TEST_DDBOOST';
                break;
            default:
                return [
                    'code' => 1,
                    'msg' => xphp_get_lang('WEB_ERROR_BD_UNKNOW_STORAGE_TYPE_ERROR')
                ]; // 不在范围内 返回错误
        }
        //添加告警配置
        $msg = array_merge($msg, $this->getWarnningRule($warningSetting));
        // 添加worm配置
        $msg = array_merge($msg, $this->getWormRule($wormSetting));

        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), true);
        $operate = $this->getUnifyOpcodeDes($opName);
        if ($mbResult['result']) {
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d) {
                if ($d['timepoint_count'] > 0) {
                    $timepointCount = $d['timepoint_count'];
                    break;
                }
            }
            // new add onlyread
            if (
                in_array(1, array_column($data, 'readonly_flag')) &&
                $usemode != xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['READ_ONLY']
            ) {
                // means just readonly and not choose readonly
                $readonly = true;
            }

            if (empty($timepointCount) && empty($readonly)) {
                //如果没有数据,直接添加
                return $this->addNewStorage($params);
            } elseif (!empty($readonly)) {
                // just readonly
                $extInfo = array('readonly' => $readonly);
                //返回结果到UI
                return $this->muOpResult(true, $operate, '', '', 0, $extInfo);
            } else {
                //如果有备份数据,需要页面确认是否导入数据,然后再发送消息添加存储
                $extInfo = array('timepoint' => $timepointCount);
                //返回结果到UI
                return $this->muOpResult(true, $operate, '', '', 0, $extInfo);
            }
            //返回结果到UI
            return $this->muOpResult(true, $operate, $mbResult['msg']);
        }
        // 扫描失败,获取失败
        return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
    }

    /**
     * 添加华为CBR存储
     * @param array $params 参数
     * @return array
     */
    public function addHuaweiCBRStorage($params = [])
    {

        //获取名称
        $nickname = $params['rname'];
        $warningSetting = $params['warning_setting'];
        $wormSetting = $params['worm_setting'];
        //是否导入数据 （前端没有这个值）
        $importflag = v1_parse_bool_to_flag(false);
        //是否格式化存储（前端没有这个值）
        $formatflag = v1_parse_bool_to_flag(false);
        //是否配置lan-free
        $lanfreeflag = v1_parse_bool_to_flag(false);
        //存储用途(传过来的是0)
        $usemode = intval($params['use_mode']);
        //lanfree名称列表
        $lanfreenamelist = [];
        //lanfree别名列表
        $lanfreenicknamelist = [];
        //挂载参数
        $mountparams = '';
        //用户uuid
        $user = xphp_get_user_info();
        $useruuid = $user['userUuid'];
        //Access Key id
        $accesskeyid = $params['cbraccessid'];
        //Secret Access Key
        $secretaccesskey = $params['cbraccesskey'];
        //存储库id
        $vaultid = $params['cbrstorageid'];
        //区域列表
        $regionidlist = $params['cbrarea'];
        //是否自动扫描数据
        $refreshflag = v1_parse_bool_to_flag($params['scandata_settings']['power']);
        //扫描数据时间间隔 转成秒
        $refreshtime = intval($params['scandata_settings']['value']) * 60;
        //用户管理员ak
        $userak = $params['cbruserak'];
        //用户管理员sk
        $usersk = $params['cbrusersk'];
        //授权用户名
        $username = $params['cbrusername'];
        $lanfreeFlag = v1_parse_bool_to_flag(false);
        //检查别名
        $check = $this->checkStorageName($nickname, $lanfreeFlag); //检查存储名是否重复
        if ($check) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_STORAGE_NICKNAME_EXIST_ERROR')
            ];
        }
        //整理参数
        $pfMsg = [
            'nickname' => $nickname,
            'import_flag' => $importflag,
            'format_flag' => $formatflag,
            'lan_free_flag' => $lanfreeflag,
            'usemode' => $usemode,
            'lanfree_name_list' => $lanfreenamelist,
            'lanfree_nickname_list' => $lanfreenicknamelist,      // 不使用，lanfree别名列表，默认为[]
            'mount_params' => $mountparams,
            'user_uuid' => $useruuid,
            'access_key_id' => $accesskeyid,
            'secret_access_key' => $secretaccesskey,
            'vault_id' => $vaultid,
            'region_id_list' => $regionidlist,
            'cbr_refresh_flag' => $refreshflag,
            'cbr_refresh_interval' => $refreshtime,
            'language' => $user['language'],
            "admin_access_key_id" => $userak,
            "admin_secret_access_key" => $usersk,
            'auth_user_list' => $username
        ];
        //添加告警配置
        $pfMsg = array_merge($pfMsg, $this->getWarnningRule($warningSetting));
        // 添加worm配置
        $pfMsg = array_merge($pfMsg, $this->getWormRule($wormSetting));
        //获取操作码
        $opName = 'NODE_HUAWEI_CBR_OP_ADD';
        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($pfMsg), false);

        $operate = $this->getUnifyOpcodeDes($opName);
        //如果添加失败
        if (!$mbResult['result']) {
            return $this->muOpResult(false, $operate, '', 'info', $mbResult['errorCode']);
        } else {
            //添加成功
            return $this->muOpResult(true, $operate);
        }
    }

    /**
     * 添加lan-free存储
     * @param array $params 参数
     * @return string
     */
    public function addLanfreeStorage($params = [])
    {
        $mountparams = $params['mountparams'];
        $nodeuuid = $params['node_uuid'];
        $storageType = $params['storagetype'];
        $nickname = $params['rname'];

        $formatFlag = v1_parse_bool_to_flag(false);
        $importFlag = v1_parse_bool_to_flag(false);
        $lanfreeFlag = v1_parse_bool_to_flag(true);
        $check = $this->checkStorageName($nickname, $lanfreeFlag); //检查存储名是否重复
        if ($check) {
            return [false, xphp_get_lang('API_CODE_STORAGES_NICKNAME_EXISTS')];
        }

        $nameList = $list = [];
        if (!empty($params['pathlist'])) {
            $i = 1;
            $count = count($params['pathlist']);
            foreach ($params['pathlist'] as $path) {
                $nameList[] = ['lanfree_nickname' => $nickname . ($count == 1 ? '' : $i)];
                $list[] = ['lanfree_name' => $path];
                $i++;
            }
        }
        $storageTypeArr = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        switch ($storageType) {
            case $storageTypeArr['FC']:
                $msg = [
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'raw_dev_path' => '',
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => 1,
                    'lanfree_name_list' => $list,
                    'lanfree_nickname_list' => $nameList,
                    'mount_params' => ''
                ];
                $opName = 'NODE_SR_OP_ADD_DISK_PART_LVM';
                break;
            case $storageTypeArr['ISCSI']:
                $iscsiTargetList = [];
                foreach ($params['serverlist'] as $server) {
                    // 这里判断处理下是否是ipv6，是的话，就加上中括号
                    if (filter_var($server['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                        // 是 ipv6
                        $server['ip'] = '[' . inet_ntop(inet_pton($server['ip'])) . ']'; // 获取缩写后的ipv6
                    }
                    $iscsiTargetList[] = [
                        'iscsi_target' => $server['ip'] . ':' . $server['port']
                    ];
                }
                $msg = [
                    'lan_free_flag' => $lanfreeFlag,
                    'nickname' => $nickname,
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'raw_dev_path' => $params['lun'],
                    'iscsi_target_list' => $iscsiTargetList,
                    'iscsi_target' => $params['iscsi_target'], // 选择的ip和端口
                    'target_iqn' => $params['target_iqn'], // 选择的iqn
                    'chap_username' => $params['chap_username'] ?? '', //用户如果输入了chap认证就写，没有就为空
                    'chap_password' => $params['chap_password'] ?? '',
                    'discover_chap_username' => $params['discover_chap_username'] ?? '', //用户如果输入了门户chap认证就写，没有就为空
                    'discover_chap_password' => $params['discover_chap_password'] ?? '',
                    'logout_iscsi_list' => $params['logout_iscsi_list'] ?? [], // 未选择的iscsi列表
                    'username' => '',
                    'password' => '',
                    'usemode' => 1,
                    'lanfree_name_list' => $list,
                    'lanfree_nickname_list' => $nameList,
                    'mount_params' => ''
                ];
                $opName = 'NODE_SR_OP_ADD_ISCSI_DISK';
                break;
            case $storageTypeArr['NFS']:
                $msg = [
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => 1,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams
                ];
                $opName = 'NODE_SR_OP_ADD_NFS';
                break;
            case $storageTypeArr['CIFS']:
                $msg = [
                    'lan_free_flag' => $lanfreeFlag,
                    'storage_type' => $storageType,
                    'nickname' => $nickname,
                    'remote_path' => $params['host'],
                    'username' => $params['username'],
                    'passwd' => $params['password'],
                    'format_flag' => $formatFlag,
                    'import_flag' => $importFlag,
                    'options' => '',
                    'usemode' => 1,
                    'lanfree_name_list' => '',
                    'lanfree_nickname_list' => '',
                    'mount_params' => $mountparams
                ];
                $opName = 'NODE_SR_OP_ADD_CIFS';
                break;
            default:
                return [false, xphp_get_lang('WEB_ERROR_BD_UNKNOW_STORAGE_TYPE_ERROR')];
        }
        //添加告警配置
        $msg['warning_flag'] = '';
        $msg['warning_type'] = '';
        $msg['warning_value'] = '';
        // 组装worm配置信息
        $msg = array_merge($msg, $this->getWormRule());

        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg));

        return $this->unifyMuOpResult($opName, $mbResult);
    }

    /**
     * 获取存储名字
     * @param string $storageuuid uuid
     * @return string
     */
    public function getStorageName($storageuuid)
    {
        $sql = "select storage_nickname, node_uuid, storage_config, storage_type
                    from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $name = $data[0]['storage_nickname'];
        $type = intval($data[0]['storage_type']);

        if ($type == xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE']) {
            $storageConfig = json_decode($data[0]['storage_config'], true);
            $nodename = $storageConfig['remote_ip'];
        } else {
            $nodename = (new Node())->getNodeName($data[0]['node_uuid']);
        }
        $name .= "\n" . '(' . $nodename . ')';
        return $name;
    }

    /**
     * 添加存储设备之异地备份8
     * @param array $params 参数
     * @return array
     */
    public function addCopyStorage($params = []): array
    {

        $this->checkRemoteIP($params['remoteip']);

        $storageType = $params['storage_type'];
        $usemode = intval($params['use_mode']);
        $nickname = $params['rname'];
        $storagelistuuid = $params['storage_list_uuid'];
        $password = md5(base64_decode($params['password']));
        $lanfreeFlag = v1_parse_bool_to_flag(false);
        $formatFlag = v1_parse_bool_to_flag(false);
        $importFlag = v1_parse_bool_to_flag(boolval($params['import']));
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        $check = $this->checkStorageName($nickname, $lanfreeFlag); //检查存储名是否重复
        if ($check) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_STORAGE_NICKNAME_EXIST_ERROR')
            ];
        }

        // 这里判断处理下是否是ipv6
        if (filter_var($params['remoteip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // 是 ipv6
            $params['remoteip'] = inet_ntop(inet_pton($params['remoteip'])); // 获取缩写后的ipv6
        }

        $nodeuuid = (new Node())->getLocalNodeUUID();

        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'remote_ip' => $params['remoteip'],
            'remote_port' => $params['remote_port'],
            'ip' => $params['remoteip'],
            'port' => $params['remote_port'],
            'username' => $params['username'],
            'password' => $password,
            'passwd' => $password,
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
            'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
            'options' => '',
            'usemode' => $usemode,
            'lanfree_name_list' => '',
            'lanfree_nickname_list' => '',
            'mount_params' => '',
            'storage_uuid_list' => $storagelistuuid,
        );

        //添加告警配置
        $msg = array_merge($msg, $this->getWarnningRule($params['warning_setting']));
        // 添加worm配置
        $msg = array_merge($msg, $this->getWormRule($params['worm_setting']));

        // 测试异地备份系统连接
        $opName = 'NODE_SR_OP_ADD_REMOTE_SYSTEM';
        if (!empty($params['confirm'])) {
            // 确认添加
            $opName = 'NODE_SR_OP_ADD_REMOTE_SYSTEM';
        }
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg));

        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            if (!empty($msg['timepointcount'])) {
                $info = array(
                    'importflag' => true,
                    'timepoint_count' => $msg['timepointcount']
                );
                // 返回用户用于确认
                return $this->muOpResult($result, $operate, '', '', 0, $info);
            }

            return $this->muOpResult($result, $operate, $msg);
        } else {
            //如果是超时,处理存储过大会创建很久的问题.
            $timeoutCode = v1_get_error_num('PF_SOCKET_GET_OP_TIMEOUT');
            if (intval($mbResult['errorCode']) == $timeoutCode) {
                return $this->muOpResult(true, $operate, xphp_get_lang('WEB_STORAGE_CREATE_TO_WAIT'));
            }
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 添加存储设备之云存储9
     * @param array $params 参数
     * @return array
     */
    public function addCloudStorage(&$params = []): array
    {

        $storageType = $params['storage_type'];
        $usemode = intval($params['use_mode']);
        $nickname = $params['rname'];
        $params['folder'] = html_entity_decode($params['folder']);
        $formatFlag = v1_parse_bool_to_flag(false);
        $importFlag = v1_parse_bool_to_flag(boolval($params['import']));
        $lanfreeFlag = v1_parse_bool_to_flag(false);
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        $check = $this->checkStorageName($nickname, $lanfreeFlag); //检查存储名是否重复
        if ($check) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_STORAGE_NICKNAME_EXIST_ERROR')
            ];
        }
        $limitsize = intval(sprintf('%.0f', intval($params['limit_size']) * 1024 * 1024 * 1024 * 1024));

        // $nodeuuid = (new Node())->getLocalNodeUUID();
        $nodeuuid = $params['node_list'][0];
        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'vendor' => $params['vendor'],
            'region' => $params['region'],
            'username' => $params['username'],
            'passwd' => $params['password'],
            'bucket' => $params['bucket'],
            'folder_path' => $params['folder'],
            'limit_size' => $limitsize,
            'custom_flag' => v1_parse_bool_to_flag(boolval($params['diy_flag'])),
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
            'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
            'options' => '',
            'usemode' => $usemode,
            'lanfree_name_list' => '',
            'lanfree_nickname_list' => '',
            'mount_params' => '',
            'use_ssl_flag' => v1_parse_bool_to_flag(boolval($params['ssl_flag'])),
            'service_endpoint' => $params['server_node'],
            'node_uuid_list' => $params['node_list']
        );
        //添加告警配置
        $msg['warning_type'] = xphp_get_config('app', 'FLAG')['UNSET'];
        $msg['warning_value'] = $limitsize;
        $msg['warning_flag'] = v1_parse_bool_to_flag(boolval($params['warning_setting']['power']));
        // 添加worm配置
        $msg = array_merge($msg, $this->getWormRule($params['worm_setting']));
        $opName = 'NODE_SR_OP_SCAN_CLOUD_STORAGE';
        if (!empty($params['confirm'])) {
            // 确认添加
            $opName = 'NODE_SR_OP_ADD_CLOUD_STORAGE';
        }
        $mbResult = $this->service()->mbNodeMsgs(
            $opName,
            $nodeuuid,
            json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            empty($params['confirm'])
        );
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $message = $mbResult['msg'];
        if (!$result) {
            if (!empty($params['confirm'])) {
                //如果是超时,处理存储过大会创建很久的问题.
                $timeoutCode = v1_get_error_num('PF_SOCKET_GET_OP_TIMEOUT');
                if (intval($mbResult['errorCode']) == $timeoutCode) {
                    return $this->muOpResult(true, $operate, xphp_get_lang('WEB_STORAGE_CREATE_TO_WAIT'), 'info');
                }
            }
            return $this->muOpResult($result, $operate, $message, 'warning', $mbResult['errorCode']);
        }
        if (!empty($params['confirm'])) {
            return $this->muOpResult($result, $operate, $message);
        }

        if ($message['import_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
            $info = [
                'import_flag' => $message['import_flag'],
                'timepoint_count' => $message['timepoint_count']
            ];
            // 返回用户用于确认
            return $this->muOpResult($result, $operate, '', '', 0, $info);
        }

        $params['confirm'] = 1;
        $this->addCloudStorage($params);
    }

    /**
     * 添加存储设备之并行文件13
     * @param array $params 参数
     * @return array
     */
    public function addParallelStorage(&$params = []): array
    {

        $storageType = $params['storage_type'];
        $usemode = intval($params['use_mode']);
        $nickname = $params['rname'];
        $formatFlag = v1_parse_bool_to_flag(false);
        $importFlag = v1_parse_bool_to_flag(boolval($params['import']));
        $lanfreeFlag = v1_parse_bool_to_flag(false);
        $timepointAutoAssginFlag = $params['allocate'] ? 1 : 0; // 是否自动分配
        $timepointAutoScanFlag = $params['autoscan'] ? 1 : 0; // 是否自动扫描
        $check = $this->checkStorageName($nickname, $lanfreeFlag); //检查存储名是否重复
        if ($check) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_STORAGE_NICKNAME_EXIST_ERROR')
            ];
        }
        $limitsize = intval(sprintf('%.0f', intval($params['limit_size']) * 1024 * 1024 * 1024 * 1024));

        $nodeuuid = $params['node_uuid'];
        $msg = array(
            'node_uuid' => $nodeuuid,
            'lan_free_flag' => $lanfreeFlag,
            'storage_type' => $storageType,
            'nickname' => $nickname,
            'vendor' => $params['vendor'],
            'region' => $params['region'],
            'username' => $params['username'],
            'passwd' => $params['password'],
            'filesystem_name' => $params['system_name'],
            'limit_size' => $limitsize,
            'custom_flag' => v1_parse_bool_to_flag(boolval($params['diy_flag'])),
            'format_flag' => $formatFlag,
            'import_flag' => $importFlag,
            'timepoint_auto_assgin_flag' => $timepointAutoAssginFlag,
            'timepoint_auto_scan_flag' => $timepointAutoScanFlag,
            'options' => '',
            'usemode' => $usemode,
            'lanfree_name_list' => '',
            'lanfree_nickname_list' => '',
            'mount_params' => '',
            'use_ssl_flag' => v1_parse_bool_to_flag(boolval($params['ssl_flag'])),
            'service_endpoint' => $params['server_node']
        );
        //添加告警配置
        $msg['warning_type'] = xphp_get_config('app', 'FLAG')['UNSET'];
        $msg['warning_value'] = $limitsize;
        $msg['warning_flag'] = v1_parse_bool_to_flag(boolval($params['warning_setting']['power']));

        // 添加worm配置
        $msg = array_merge($msg, $this->getWormRule($params['worm_setting']));

        $opName = 'NODE_SR_OP_TEST_PARALLEL_FILESYSTEM';
        if (!empty($params['confirm'])) {
            // 确认添加
            $opName = 'NODE_SR_OP_ADD_PARALLEL_FILESYSTEM';
        }
        $mbResult = $this->service()->mbNodeMsgs(
            $opName,
            $nodeuuid,
            json_encode($msg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            empty($params['confirm'])
        );
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $message = $mbResult['msg'];
        if (!$result) {
            if (!empty($params['confirm'])) {
                //如果是超时,处理存储过大会创建很久的问题.
                $timeoutCode = v1_get_error_num('PF_SOCKET_GET_OP_TIMEOUT');
                if (intval($mbResult['errorCode']) == $timeoutCode) {
                    return $this->muOpResult(true, $operate, xphp_get_lang('WEB_STORAGE_CREATE_TO_WAIT'), 'info');
                }
            }
            return $this->muOpResult($result, $operate, $message, 'warning', $mbResult['errorCode']);
        }
        if (!empty($params['confirm'])) {
            return $this->muOpResult($result, $operate, $message);
        }

        $data = $message['raw_storage_list'] ?? [];
        foreach ($data as $d) {
            if ($d['timepoint_count'] > 0) {
                $timepointCount = $d['timepoint_count'];
                break;
            }
        }

        if (!empty($timepointCount)) {
            $info = array(
                'importflag' => true,
                'timepoint_count' => count($timepointCount)
            );
            // 返回用户用于确认
            return $this->muOpResult($result, $operate, '', '', 0, $info);
        }

        $params['confirm'] = 1;
        $this->addParallelStorage($params);
    }

    /**
     * 告警规则组装
     * @param array $params 告警数组
     * @return array
     */
    private function getWarnningRule($params = []): array
    {

        if (empty($params)) {
            return [
                'warning_flag' => v1_parse_bool_to_flag(false),
                'warning_type' => 1,
                'warning_value' => 0,
            ];
        }
        $msg = [];
        //添加告警配置
        $msg['warning_flag'] = v1_parse_bool_to_flag(boolval($params['power']));
        $msg['warning_type'] = intval($params['type']);
        if (xphp_get_config('resource', 'STORAGEWARNINGTYPE')['SIZE'] == $msg['warning_type']) {
            //如果是按照大小来告警
            $msg['warning_value'] = intval($params['value']) * 1024 * 1024 * 1024;
        } else {
            $msg['warning_value'] = intval($params['value']);
        }
        return $msg;
    }

    /**
     * worm规则组装
     * @param array $params 告警数组
     * @return array
     */
    private function getWormRule($params = []): array
    {

        if (empty($params)) {
            return [
                'worm_flag' => v1_parse_bool_to_flag(false),
                'worm_allocate_type' => 3,
                'worm_allocate_value' => 0,
            ];
        }
        $msg = [];
        //添加配置
        $msg['worm_flag'] = v1_parse_bool_to_flag(boolval($params['power']));
        $msg['worm_allocate_type'] = intval($params['type']);
        if (xphp_get_config('resource', 'STORAGEWORMTYPE')['SIZE'] == $msg['worm_allocate_type']) {
            //如果是按照大小来告警
            $msg['worm_allocate_value'] = intval($params['value']) * 1024 * 1024 * 1024;
        } else {
            $msg['worm_allocate_value'] = intval($params['value']);
        }
        return $msg;
    }

    /**
     * 检查添加的IP是否属于备份节点
     * @param string $ip ip
     * @return void
     */
    private function checkRemoteIP(string $ip)
    {

        $sql = "select ip from bd_node";
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            foreach ($data as $d) {
                $ipList = explode(' ', $d['ip']);
                foreach ($ipList as $nodeIp) {
                    if ($ip == $nodeIp) {
                        $this->muOpResult(
                            false,
                            xphp_get_lang('WEB_NODE_OP_ADD_REMOTE_SYSTEM'),
                            xphp_get_lang('WEB_STORAGE_INPUT_WRONG_IP')
                        );
                    }
                }
            }
        }
    }

    /**
     * 修改存储别名和告警信息以及worm配置
     * @param array $params 参数
     * @return bool
     */
    public function editStorageInfo($params = [])
    {
        $storageuuid = $params['storages_uuid'];

        // 查询出原来的一些信息 $usemode $storagetype
        $data = $this->dbSelect(
            "select storage_nickname,storage_type,use_mode,total_size,free_size,
                        storage_config,node_uuid,user_uuid,lan_free_flag 
                        from bd_storage_resource where storage_uuid = ?",
            [$storageuuid]
        );
        $isLanfree = $data[0]['lan_free_flag'] == xphp_get_config('app', 'FLAG')['SET'];

        if ($isLanfree) {
            // lanfree不是分配资源
            $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);
        } else {
            $this->checkAuthBySourceUuid($storageuuid, xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']);
        }

        $storagename = trim($params['storage_name']);

        $lanfreeFlag = v1_parse_bool_to_flag(false);
        $check = $this->checkStorageName($storagename, $lanfreeFlag); //检查存储名是否重复
        if ($check && $check != $storageuuid) {
            return [false, xphp_get_lang('UI_STORAGE_NICKNAME_EXIST_ERROR')];
        }

        if (empty($data)) {
            return [false, xphp_get_lang('WEB_ERROR_BD_STORAGE_IS_NOT_EXIST_ERROR')];
        }

        $storagetype = $data[0]['storage_type'];
        $usemode = $data[0]['use_mode'];

        $storageTypeArr = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        if ($storagetype == $storageTypeArr['CIFS'] && !empty($params['username'])) {
            // 如果是cifs 并且修改了密码
            $msg = [];
            $msg['username'] = $params['username'];
            $password = base64_decode($params['password']);
            $msg['passwd'] = $password;
            $msg['storage_uuid'] = $storageuuid;
            $opName = 'NODE_SR_OP_MODIFY_CIFS_SR';
            $nodeuuid = (new Node())->getLocalNodeUUID();
            $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), false);
            //如果修改失败
            if (!$mbResult['result']) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('WEB_STORAGE_EDIT_INFO'),
                    '',
                    'info',
                    $mbResult['errorCode']
                );
            }
        }

        if (
            empty($isLanfree) &&
            in_array($storagetype, [$storageTypeArr['NFS'], $storageTypeArr['CIFS'], $storageTypeArr['CLOUD']])
        ) {
            // cifs或者nfs有修改存储挂载节点的 ，那么需要单独发送消息给后台先 或云存储
            $mount = $this->getNasMountPointList($storageuuid, (int) $storagetype);
            $oldMount = array_column($mount, 'node_uuid');
            if (
                empty(array_diff($oldMount, $params['node_list'])) && empty(array_diff($params['node_list'], $oldMount))
            ) {
            } else {
                // 不相同，那么发送后台更改挂载点
                $msg = [];
                $msg['node_uuid_list'] = $params['node_list'];
                $msg['storage_uuid'] = $storageuuid;
                // 判断下当前存储是否在共享存储池里面，
                $sqls = "select storage_pool_uuid from bd_storage_resource_pool_list where storage_uuid = ? limit 1";
                $pool = $this->dbSelect($sqls, [$storageuuid]);
                if (!empty($pool)) {
                    // 如果在共享池里面，需要判断修改后的节点是否与池里面的存储还有公共的挂载节点，如果没有，则不允许修改
                    $sqls2 = "select storage_uuid,node_uuid from bd_shared_storage_node_layout where storage_uuid in (
                            select storage_uuid from bd_storage_resource_pool_list where storage_pool_uuid = ? and 
                               storage_uuid != ?                                                          
                        )";
                    $nodeList = $this->dbSelect($sqls2, [$pool[0]['storage_pool_uuid'], $storageuuid]);
                    $nodeArr = [];
                    foreach ($nodeList as $item) {
                        $nodeArr[$item['storage_uuid']] = array_merge(
                            $nodeArr[$item['storage_uuid']] ?? [],
                            [$item['node_uuid']]
                        );
                    }

                    $nodeArr = array_values($nodeArr);

                    // 把 $nodeArr 所有子数组合并成一个一维数组
                    $allNodes = array_merge(...array_values($nodeArr));
                    // 求交集
                    $intersection = array_intersect($params['node_list'], $allNodes);
                    // 判断是否有交集
                    if (empty($intersection)) {
                        // 表示去除了公共的
                        return $this->muOpResult(
                            false,
                            xphp_get_lang('WEB_STORAGE_EDIT_INFO'),
                            xphp_get_lang('WEB_STORAGE_CHANGE_POINT_IN_POOL')
                        );
                    }
                }
                if ($storagetype == $storageTypeArr['CLOUD']) {
                    // 云存储
                    $opName = 'NODE_SR_OP_MODIFY_CLOUD_STORAGE_NODE';
                } elseif ($storagetype == $storageTypeArr['NFS']) {
                    $opName = 'NODE_SR_OP_MODIFY_NFS_STORAGE_NODE';
                } else {
                    // cifs
                    $opName = 'NODE_SR_OP_MODIFY_CIFS_STORAGE_NODE';
                }

                $nodeuuid = $data[0]['node_uuid'];
                $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), false);

                //如果修改失败
                if (!$mbResult['result']) {
                    return $this->muOpResult(
                        false,
                        xphp_get_lang('WEB_STORAGE_EDIT_INFO'),
                        '',
                        'info',
                        $mbResult['errorCode']
                    );
                }
            }
        }

        $warningFlag = v1_parse_bool_to_flag(boolval($params['warning_setting']['power']));
        $autoimportFlag = v1_parse_bool_to_flag(boolval($params['auto_scan_flag']));
        $autoassginFlag = v1_parse_bool_to_flag(boolval($params['auto_assign_flag']));
        $warningType = $params['warning_setting']['type'];
        if (xphp_get_config('resource', 'STORAGEWARNINGTYPE')['SIZE'] == $warningType) {
            //如果是按照大小来告警
            $warningValue = $params['warning_setting']['value'] * 1024 * 1024 * 1024;
            if ($storagetype == xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD']) {
                $warningValue = $warningValue * 1024;
            }
        } else {
            $warningValue = $params['warning_setting']['value'];
        }

        // worm配置
        // 如果开启worm需要检测cdp驱动信息
        if (!empty(boolval($params['worm_setting']['power']))) {
            $opNames = 'NODE_SYS_OP_CHECK_VDP_LOADED_STATUS';
            $mbResult = $this->service()->mbNodeMsgs($opNames, $data[0]['node_uuid'], json_encode([]), true);
            //如果检测失败
            if (!$mbResult['result'] || empty($mbResult['msg']['vdp_status'])) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('UI_STORAGE_EDIT'),
                    $mbResult['msg'],
                    'info',
                    $mbResult['errorCode']
                );
            }
        }

        $wormFlag = v1_parse_bool_to_flag(boolval($params['worm_setting']['power']));
        $wormType = intval($params['worm_setting']['type']);
        if (xphp_get_config('resource', 'STORAGEWORMTYPE')['SIZE'] == $wormType) {
            //如果是按照大小来告警
            $wormValue = $params['worm_setting']['value'] * 1024 * 1024 * 1024;
        } else {
            $wormValue = $params['worm_setting']['value'];
        }

        $storageConfig = json_decode($data[0]['storage_config'], true);
        $storageConfig['timepoint_auto_scan_flag'] = $autoimportFlag;
        $storageConfig['timepoint_auto_assgin_flag'] = $autoassginFlag;

        $msg = [];
        if ($storagetype == $storageTypeArr['REMOTE'] && $params['remote_flag']) {
            $msg = [
                'remote_ip' => $storageConfig['remote_ip'],
                'remote_port' => $storageConfig['remote_port'],
            ];
        }

        $sql = "update bd_storage_resource set storage_nickname = ?, warning_flag = ?, warning_type = ?,
                warning_value = ?, use_mode = ?, worm_flag = ?, worm_allocate_type = ?, worm_allocate_value = ?,
                storage_config = ? ";
        $sqlParams = [
            $storagename,
            $warningFlag,
            $warningType,
            $warningValue,
            $usemode,
            $wormFlag,
            $wormType,
            $wormValue
        ];
        //如果是云存储
        if ($storagetype == $storageTypeArr['CLOUD']) {
            $freeSize = $warningValue - $data[0]['total_size'] + $data[0]['free_size'];
            $storageConfig = json_encode($storageConfig);
            $sql .= ' ,free_size = ?,total_size = ?  ';
            $sqlParams = array_merge($sqlParams, [$storageConfig, $freeSize, $warningValue]);
        } elseif ($storagetype == $storageTypeArr['HUAWEI_CBR']) {
            //如果是华为CBR存储
            $refreshflag = v1_parse_bool_to_flag($params['scandata_settings']['power']);
            $refreshtime = intval($params['scandata_settings']['value']) * 60;

            $storageConfig['cbr_refresh_flag'] = $refreshflag;
            $storageConfig['cbr_refresh_interval'] = $refreshtime;
            $storageConfig = json_encode($storageConfig);
            //warningType转成int
            $warningType = 0;
            $warningValue = 0;
            $sqlParams = [$storagename, $warningFlag, $warningType, $warningValue, $usemode, $storageConfig];
        } else {
            $storageConfig = json_encode($storageConfig);
            $sqlParams = array_merge($sqlParams, [$storageConfig]);
        }
        $sql .= " where storage_uuid = ?";
        $sqlParams = array_merge($sqlParams, [$storageuuid]);
        $result = $this->dbExec($sql, $sqlParams);

        //如果是华为Cbr 在更新完之后要更新vm_tree
        if ($result && $storagetype == $storageTypeArr['HUAWEI_CBR']) {
            //更新vmtree中的存储名 调用刷新更新vm_tree这张表
            //设置参数
            $pfMsg['storage_uuid_list'] = [
                [
                    'storage_uuid' => $storageuuid
                ]
            ];
            $opName = 'NODE_HUAWEI_CBR_OP_REFERSH';
            $nodeuuid = (new Node())->getLocalNodeUUID();
            $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($pfMsg), false);
            //如果刷新失败
            if (!$mbResult['result']) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('API_CODE_STORAGES_EDIT_STORAGE_NICKNAME'),
                    '',
                    'info',
                    $mbResult['errorCode']
                );
            }
        }

        if ($result && $storagetype == $storageTypeArr['REMOTE'] && $params['remote_flag']) {
            // 如果是异地备份系统 并且修改了密码
            $msg['username'] = $params['username'];
            $password = md5(base64_decode($params['password']));
            $msg['password'] = $password;
            $msg['storage_uuid'] = $storageuuid;
            $opName = 'NODE_SR_OP_MODIFY_REMOTE_SYSTEM_INFO';
            $nodeuuid = (new Node())->getLocalNodeUUID();
            $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), false);
            //如果修改失败
            if (!$mbResult['result']) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('API_CODE_STORAGES_EDIT_STORAGE_NICKNAME'),
                    '',
                    'info',
                    $mbResult['errorCode']
                );
            }
        }

        // 如果是DELL Data Domain Boost存储 并且修改了 存储服务器、存储单元、用户名和密码任意一个，都需要请求后台接口完成
        if (
            $result && $storagetype == $storageTypeArr['DDDB'] &&
            (!empty($params['username']) || !empty($params['password']))
        ) {
            $msg = [
                'storage_uuid' => $storageuuid,
                'username' => $params['username'],
                'password' => base64_decode($params['password']),
                'auth_type' => 'user',
            ];

            $opName = 'NODE_SR_OP_MODIFY_DDBOOST_SR';
            $nodeuuid = $data[0]['node_uuid'];
            $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), false);
            //如果修改失败
            if (!$mbResult['result']) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('API_CODE_STORAGES_EDIT_STORAGE_NICKNAME'),
                    '',
                    'info',
                    $mbResult['errorCode']
                );
            }
        }

        //插系统日志
        $usemodeDes = xphp_get_desc('Pf', 'STORAGE_USE_DES')[$usemode];
        $descriptionParam = array($data[0]['storage_nickname'], $storagename);
        if ($result) {
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_EDIT', $descriptionParam);
        } else {
            $this->systemLog(
                'BD_SYSTEMLOG_DESC_KEY_STORAGE_EDIT',
                $descriptionParam,
                xphp_get_config('log', 'LOGLEVEL')['ERROR']
            );
        }
        return [$result, xphp_get_lang('WEB_STORAGE_EDIT_INFO')];
    }

    /**
     * 获取节点状态 和描述
     * @param array $d 数据
     * @return array
     */
    public function getNodeStatus($d = []): array
    {

        $nodeHandler = new Node();

        $nodeAllStatus = $nodeHandler->getNodeAllStatus($d['node_uuid']);

        $nodeDes = $nodeHandler->getNodeGridName($d['ip'], $d['node_nickname'], $d['host_name']) . '(' . $d['ip'] . ')';
        $nodeStatus = $nodeAllStatus['flag'];

        //异地备份系统节点显示
        if ($d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE']) {
            $storageConfig = json_decode($d['storage_config'], true);
            $nodeDes = $storageConfig['remote_ip'];
            if (!empty($storageConfig['remote_name'])) {
                $nodeDes = $storageConfig['remote_name'] . '(' . $storageConfig['remote_ip'] . ')';
            }
            $nodeStatus = v1_parse_flag_to_bool(intval($storageConfig['remote_status']));
        }
        return [$nodeStatus, $nodeDes];
    }

    /**
     * 得到存储状态码
     * @param array  $nodeAllStatus 节点所有程序状态
     * @param int    $storageStatus 存储状态码
     * @param int    $mountFlag     存储挂载标志
     * @param string $storageType   存储类型
     * @return int
     */
    public function getStorageStatus($nodeAllStatus, $storageStatus, $mountFlag, $storageType = '')
    {

        //首先检查节点的状态
        $storagestatus = xphp_get_config('resource', 'STORAGE_STATUS');
        $storagestype = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $flag = xphp_get_config('app', 'FLAG');
        if (!$nodeAllStatus['flag'] && $storageType != $storagestype['CLOUD']) {
            //节点已经有异常了,部分程序不在线
            return $storagestatus['OFFLINE'];
        }
        if (
            $storageStatus == $storagestatus['ONLINE'] &&
            $mountFlag == $flag['SET']
        ) {
            //存储在线 且挂载
            return $storagestatus['ONLINE'];
        } elseif ($storageStatus == $storagestatus['OFFLINE']) {
            //存储离线
            return $storagestatus['OFFLINE'];
        } elseif ($storageStatus == $storagestatus['SYNCING']) {
            //存储同步中
            return $storagestatus['SYNCING'];
        } elseif (
            $storageStatus == $storagestatus['ONLINE'] &&
            $mountFlag == $flag['UNSET']
        ) {
            //在线未挂载
            return $storagestatus['UNMOUNT'];
        }

        //其他所有状态(创建中,离线已挂载)统一为异常
        return $storagestatus['CREATING'];
    }

    /**
     * 批量获取存储状态
     * @param array $storageUuidList 存储uuid集合
     * @return array
     */
    public function batchGetStorageStatus(array $storageUuidList): array
    {
        $storageUuidList = array_values(array_unique($storageUuidList));
        $storageUuids = "'" . implode("','", $storageUuidList) . "'";
        $sql = "SELECT bsr.status, bsr.storage_uuid, bsr.storage_type, bsr.node_uuid, bsr.mount_flag,
                    bsr.storage_nickname, bn.node_nickname, bn.ip, bn.host_name, bn.node_type
                FROM bd_storage_resource bsr
                    LEFT JOIN bd_node bn ON bn.node_uuid = bsr.node_uuid
                WHERE bsr.storage_uuid IN ($storageUuids) ";
        $storageData = $this->dbSelect($sql);
        if (!is_array($storageData)) {
            $storageData = [];
        }
        $nodeUuidList = [];
        $allStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $excludeStorageType = [
            $allStorageType['CLOUD'],
            $allStorageType['NFS'],
            $allStorageType['CIFS'],
        ];
        $shareStorageUuidList = [];
        foreach ($storageData as $storageItem) {
            if (in_array($storageItem['storage_type'], $excludeStorageType)) {
                $shareStorageUuidList[] = $storageItem['storage_uuid'];
                continue;
            }
            $nodeUuidList[] = $storageItem['node_uuid'];
        }

        // 查询共享存储信息
        $shareStorageUuids = "'" . implode("','", $shareStorageUuidList) . "'";
        $sql = "SELECT storage_uuid, node_uuid, status FROM bd_shared_storage_node_layout WHERE storage_uuid IN ($shareStorageUuids) ";
        $shareStorageData = $this->dbSelect($sql);
        if (!is_array($shareStorageData)) {
            $shareStorageData = [];
        }
        $shareStorageMap = [];
        foreach ($shareStorageData as $shareStorageItem) {
            if (!isset($shareStorageMap[$shareStorageItem['storage_uuid']])) {
                $shareStorageMap[$shareStorageItem['storage_uuid']] = [];
            }
            $shareStorageMap[$shareStorageItem['storage_uuid']][] = $shareStorageItem;
        }

        $nodeStatusList = (new Node())->batchGetNodeStatus($nodeUuidList);
        $nodeStatusMap = [];
        foreach ($nodeStatusList as $nodeStatus) {
            $nodeStatusMap[$nodeStatus['node_uuid']] = $nodeStatus;
            $nodeStatusMap[$nodeStatus['node_uuid']]['flag'] = $nodeStatus['online_flag'];
        }

        // 查询共享存储挂载的节点信息
        $sql = "SELECT node_uuid, storage_uuid FROM bd_shared_storage_node_layout WHERE storage_uuid IN ($storageUuids) ";
        $shareStorageNodeUuidListData = $this->dbSelect($sql);
        if (!is_array($shareStorageNodeUuidListData)) {
            $shareStorageNodeUuidListData = [];
        }
        $shareStorageNodeUuidListMap = [];
        foreach ($shareStorageNodeUuidListData as $shareStorageNodeUuidListItem) {
            if (!isset($shareStorageMap[$shareStorageNodeUuidListItem['storage_uuid']])) {
                $shareStorageNodeUuidListMap[$shareStorageNodeUuidListItem['storage_uuid']] = [];
            }
            $shareStorageNodeUuidListMap[$shareStorageNodeUuidListItem['storage_uuid']][] = $shareStorageNodeUuidListItem['node_uuid'];
        }

        $allStorageStatus = xphp_get_config('resource', 'STORAGE_STATUS');
        $storageStatusList = [];
        foreach ($storageData as $storageItem) {
            $tmp = [
                'storage_uuid' => $storageItem['storage_uuid'],
                'storage_status' => $allStorageStatus['OFFLINE'],
                'storage_type' => $storageItem['storage_type'],
                'storage_nickname' => $storageItem['storage_nickname'],
                'storage_online_flag' => false,
                'node_online_flag' => false,
                'node_uuid' => $storageItem['node_uuid'],
                'node_nickname' => $storageItem['node_nickname'] ?: '',
                'node_ip' => $storageItem['ip'],
                'node_hostname' => $storageItem['host_name'] ?: '',
                'node_type' => (int) $storageItem['node_type'],
                'mount_node_uuid_list' => $shareStorageNodeUuidListMap[$storageItem['storage_uuid']] ?? []
            ];
            if (in_array($storageItem['storage_type'], $excludeStorageType)) {  // 判断是否status
                if (isset($shareStorageMap[$storageItem['storage_uuid']])) {
                    foreach ($shareStorageMap[$storageItem['storage_uuid']] as $shareStorageItem) {
                        if ($shareStorageItem['status'] == $allStorageStatus['ONLINE']) {
                            $tmp['storage_online_flag'] = true;
                            $tmp['node_online_flag'] = true;
                            $tmp['storage_status'] = $allStorageStatus['ONLINE'];
                            break;
                        }
                    }
                }
            } else {  // 其他存储判断节点是否在线
                if (isset($nodeStatusMap[$storageItem['node_uuid']])) {
                    if ($nodeStatusMap[$storageItem['node_uuid']]['online_flag']) {
                        $tmp['node_online_flag'] = true;
                        $tmp['storage_status'] = $this->getStorageStatus(
                            $nodeStatusMap[$storageItem['node_uuid']],
                            $storageItem['status'],
                            $storageItem['mount_flag'],
                            $storageItem['storage_type']
                        );
                        if ($tmp['storage_status'] == $allStorageStatus['ONLINE']) {
                            $tmp['storage_online_flag'] = true;
                        }
                    }
                }
            }
            $storageStatusList[] = $tmp;
        }
        return $storageStatusList;
    }

    /**
     * 获取存储用途描述
     * @param int $usemode mode
     * @return string
     */
    public function getUsemodeDes($usemode = 0): string
    {

        switch ($usemode) {
            /*case 2:
            case 3:
                $des = xphp_get_lang('WEB_PLATFORM_DES_COPY') . '|' . xphp_get_lang('UI_PLATFORM_ARCHIVE');
                break;*/
            case 4:
                $des = 'NAS';
                break;
            case 5:
                $des = xphp_get_lang('UI_NAS_MANAGE_ONLY_READ');
                break;
            /*case 1:
                $des = xphp_get_lang('UI_PLATFORM_BACKUP');*/
            default:
                // 备份 和副本归档显示 --
                $des = xphp_get_config('app', 'NULLSPACE');
                break;
        }
        return $des;
    }

    /**
     * 删除存储时的检测
     * @param array $uuids 存储设备uuid集合
     * @return bool|string
     */
    public function checkStorageInfo($uuids = [])
    {

        $sql = "select count(task_name) as timepoint, sum(write_size) as size, task_uuid
                from bd_backup_timepoint 
                where storage_uuid = ? and deleted_flag = ? and available_flag = ?";
        $flag = xphp_get_config('app', 'FLAG');

        $sqluuid = "select task_uuid from bd_backup_timepoint 
        where storage_uuid = ? and deleted_flag = ? and available_flag = ?";

        $storageUuid = $uuids[0];

        $sql1 = "select storage_type,lan_free_flag,user_uuid from bd_storage_resource where storage_uuid = ?";
        $data1 = $this->dbSelect($sql1, [$storageUuid]);
        if ($data1[0]['lan_free_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
            // lanfree不是分配资源
            $this->checkAuthByUserUuid($data1[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);
        } else {
            $this->checkAuthBySourceUuid($storageUuid, xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']);
        }

        $sqlParams = array($storageUuid, $flag['UNSET'], $flag['SET']);
        $data = $this->dbSelect($sql, $sqlParams);
        if (empty($data)) {
            return $this->muOpResult(
                false,
                xphp_get_lang('WEB_NODE_OP_DELETE'),
                xphp_get_lang('WEB_PLATFORM_DC_NO_DATA')
            );
        }
        $datauuid = $this->dbSelect($sqluuid, $sqlParams);

        $timepointCount = $data[0]['timepoint'];
        $timepointSize = v1_calsize($data[0]['size'] ?? 0, true);
        $recovertaskuuids = array_unique(array_column($datauuid, 'task_uuid'));

        //检测备份任务
        $sql = "select task_name from bd_task where storage_uuid = ?";
        $data = $this->dbSelect($sql, [$storageUuid]);
        $taskCount = count($data);
        $taskname = array_column($data, 'task_name');

        //获取存储类型
        $storagetype = $data1[0]['storage_type'];
        $storageTypeArr = xphp_get_config('resource', 'BD_STORAGE_TYPE');

        if ($storagetype == $storageTypeArr['HUAWEI_CBR']) {
            //如果是华为CBR 需要用以下方法判断是否存在时间点
            //下云同步
            $canflag = true; //此标志代表存储可删
            $sql2 = "select vt.detail,bt.task_uuid
                        from vm_task vt, bd_task bt
                        where bt.task_uuid = vt.task_uuid and vt.hypervisor_type = ?
                          and bt.task_type = ? and bt.task_status = ? ";
            $vmhtype = xphp_get_config('vm', 'VMHYPERVISORTYPE');
            $taskType = xphp_get_config('task', 'TASKTYPE');
            $taskStatus = xphp_get_config('task', 'TASKSTATUS');
            $sql2Params = [
                $vmhtype['VM_HYPERVISOR_TYPE_HUAWEI_CBR'],
                $taskType['VM_HUAWEI_CBR_SYNC'],
                $taskStatus['RUNNING']
            ];
            $data2 = $this->dbSelect($sql2, $sql2Params);
            //获取storage_uuid
            $synctaskuuids = [];
            foreach ($data2 as $d) {
                $detail = json_decode($d['detail'], true);
                $synclist = $detail['cbr_sync_list'];
                foreach ($synclist as $syncitem) {
                    if ($syncitem['storage_uuid'] == $storageUuid) {
                        $canflag = false;
                        if (!in_array($d['task_uuid'], $synctaskuuids)) {
                            $synctaskuuids[] = $d['task_uuid'];
                        }
                    }
                }
            }
            //设置taskname
            if (!empty($synctaskuuids)) {
                $taskuuidsStr1 = implode("', '", $synctaskuuids);
                $sql3 = "select task_name from bd_task where task_uuid in ('" . $taskuuidsStr1 . "')";

                $data3 = $this->dbSelect($sql3);
                $taskname = array_merge($taskname, array_column($data3, 'task_name'));
                $taskCount += count($data3);
            }
            //下云恢复
            //判断时间点 可以用上面的第一条sql 语句
            if ($timepointCount != 0) {
                $recoveryflag = true;
                //代表有下云恢复任务 找到task_uuid
                $taskuuidsStr = implode("', '", $recovertaskuuids);
                $sql3 = "select task_name from bd_task
                            where task_uuid in ('" . $taskuuidsStr . "') and task_status = ?";
                $taskStatus = xphp_get_config('task', 'TASKSTATUS');
                $data3 = $this->dbSelect($sql3, array($taskStatus['RUNNING']));
                $taskname = array_merge($taskname, array_column($data3, 'task_name'));
                $taskCount += count($data3);
            }
            if (!empty($recoveryflag) && $taskCount == 0) {
                //如果是有下云恢复任务
                return $this->delStorageInfo($uuids);
            }

            //下云同步任务
            if ($timepointCount == 0 && $taskCount == 0 && $canflag) {
                return $this->delStorageInfo($uuids);
            }
        } elseif ($timepointCount == 0 && $taskCount == 0) {
            //其他存储可以直接这样
            //如果没有数据和任务,直接删除
            return $this->delStorageInfo($uuids);
        }
        $info = [
            'timepoint_count' => $timepointCount,
            'timepoint_size' => $timepointSize,
            'task_count' => $taskCount,
            'tasks' => $taskname,
            'storage_type' => $storagetype,
        ];
        return $this->muOpResult(true, xphp_get_lang('WEB_NODE_OP_DELETE'), '', '', 0, $info);
    }

    /**
     * 删除存储设备
     * @param array $uuids 存储设备uuid集合
     * @return bool
     */
    public function delStorageInfo($uuids = []): bool
    {
        // 1，获取节点和类型 以及得到节点的状态
        $sql = "select node_uuid,storage_type,storage_uuid from bd_storage_resource where ";

        if (count($uuids) == 1) {
            $where = ' storage_uuid = ?';
            $params = array($uuids[0]);
        } else {
            $where = ' storage_uuid in ?';
            $params = array("('" . implode("','", $uuids) . "')");
        }
        $resourceArr = $this->dbSelect($sql . $where, $params);
        $node = new Node();
        $storageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $user = xphp_get_user_info();
        $storageUuid = []; // 初始一个空数组 后续一起处理
        /*$nodeSql = "select node_uuid from bd_node"; //生产存储需要所有往节点发送消息
        $nodeData = $this->dbSelect($nodeSql);*/
        foreach ($resourceArr as $item) {
            // 在存储资源池中存储设备不能删除
            $sql = "SELECT bsrpl.storage_uuid, bsrp.storage_pool_uuid, bsrp.storage_pool_nickname
                FROM bd_storage_resource_pool bsrp
                    INNER JOIN bd_storage_resource_pool_list bsrpl ON bsrp.storage_pool_uuid = bsrpl.storage_pool_uuid
                WHERE bsrpl.storage_uuid = ? ";
            $storagePoolData = $this->dbSelect($sql, [$item['storage_uuid']]);
            if (is_array($storagePoolData) && $storagePoolData) {
                $storagePoolNameList = array_column($storagePoolData, 'storage_pool_nickname');
                $this->muOpResult(
                    false,
                    xphp_get_lang('UI_STORAGE_DELETE'),
                    sprintf(xphp_get_lang('WEB_STORAGE_DELETE_IN_POOL'), implode('、', $storagePoolNameList)),
                    'error',
                    1
                );
                return false;
            }

            $result = true;
            // 获取节点状态
            $nodeStatus = $node->getNodeAllStatus($item['node_uuid']);

            if ($nodeStatus['flag']) {
                // 2，如果节点在线,发送删除消息到节点进程处理
                $msg = array('storage_uuid_list' => array(array('storage_uuid' => $item['storage_uuid'])));
                $opName = "NODE_SR_OP_DELETE";
                if ($item['storage_type'] == $storageType['REMOTE']) {
                    $opName = "NODE_SR_OP_DELETE_REMOTE_SYSTEM";
                }
                //如果是生产存储（华为ocean）,发送检测消息到当前节点检测是否占用
                if ($item['storage_type'] == $storageType['HUAWEI_OCEAN']) {
                    $opName = "NODE_OP_LUN_DELETE";
                    $checkOp = 'NODE_STORAGE_INFRASTRUCTURE_OP_CHECK_IS_MAPPED'; //检测所有节点生产存储操作码
                    $this->checkProductionStorage($item['node_uuid'], $item['storage_uuid'], $checkOp, $msg);
                }
                //如果存储类型是华为CBR
                if ($storageType == $storageType['HUAWEI_CBR']) {
                    $opName = "NODE_HUAWEI_CBR_OP_DELETE";
                }
                //进行当前存储是否有任务在使用
                $this->checkStorageInRunningTask($item['storage_uuid']);
                $mbResult = $this->service()->mbNodeMsgs($opName, $item['node_uuid'], json_encode($msg));
                $this->checkStorageDeleteResult($item['storage_uuid'], $mbResult);

                $result = $mbResult['result'];
                // 这里如果是错误直接返回
                if (!$result) {
                    $operate = (new NodeOpcode())->getOpcodeDes($opName);
                    return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
                }
            } else {
                // 3，节点不在线,检测是否有正在运行的任务使用该存储,如果有返回,没有的话直接删除存储和存储上的时间点记录
                $this->checkStorageInRunningTask($item['storage_uuid']);
                $result = $result && $this->deleteStorageByWeb($item['storage_uuid']);

                //添加云存储和租户内部用户关联
                if (!empty($user['tenantuuid']) && $item['storage_type'] == $storageType['CLOUD']) {
                    $storageUuid[] = $item['storage_uuid'];
                }
            }
        }
        if (!empty($result) && $result && !empty($storageUuid)) {
            (new User())->deleteUserStorage(
                ['userUuid' => $user['userUuid'], 'storage_uuid' => $storageUuid]
            );
        }
        return $result ?? false;
    }

    /**
     * 检查生产存储
     * @param string $nodeUUid    所在节点
     * @param string $storageuuid 存储uuid
     * @param mixed  $op          操作码
     * @param mixed  $msg         发送消息
     * @return mixed
     */
    private function checkProductionStorage($nodeUUid, $storageuuid, $op, $msg)
    {

        $checkResult = $this->service()->mbNodeMsgs($op, $nodeUUid, json_encode($msg));
        if (!$checkResult['result']) {
            exit($this->muOpResult(
                false,
                xphp_get_lang('WEB_STORAGE_DELETE_FAILURE'),
                xphp_get_lang('WEB_STORAGE_DELETE_PRODUCTION_STORAGE_FAILURE_TIPS')
            ));
        }
    }

    /**
     * 导入数据管理列表
     * @param array $params 参数
     * @return array
     */
    public function getImportDataList($params = []): array
    {
        $user = xphp_get_user_info();
        $flag = xphp_get_config('app', 'FLAG');

        $where = ' deleted_flag = ? and available_flag = ? and import_flag = ? and user_uuid = ? ';
        $field = "  task_uuid, task_name, module_type, sub_module_type, task_type, task_create_time, 
                    count(task_uuid) as point, sum(write_size) as size  ";
        $count = "count(DISTINCT task_uuid) as num";

        $sql = "select " . $field . " from bd_backup_timepoint where " . $where . " group by task_uuid";

        $sqlparam = [$flag['UNSET'], $flag['SET'], $flag['SET'], $user['userUuid']];

        $dataCount = $this->dbSelect("select " . $count . " from bd_backup_timepoint where " . $where, $sqlparam);

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'task_name' => 'task_name',
                'module_type_value' => 'module_type',
                'create_time' => 'task_create_time',
                'point_num' => 'point',
                'size' => 'size',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' task_create_time desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', id desc');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ? , ? ';

        $data = $this->dbSelect($sql, array_merge($sqlparam, [$params['offset'], $params['limit']]));

        $records = [];
        $i = 0;
        $jobInfo = new JobInfo();
        foreach ($data as $d) {
            $records[] = [
                'task_uuid' => $d['task_uuid'],
                'num' => ++$i,
                'task_name' => $d['task_name'],
                'module_type' => $d['module_type'],
                'module_type_value' => $jobInfo->getModuleName($d),
                'create_time' => $d['task_create_time'],
                'point_num' => $d['point'],
                'size' => v1_calsize($d['size'], true),
            ];
        }

        return [
            'rows' => $records,
            'total' => $dataCount[0]['num'] ?? 0
        ];
    }

    /**
     * 获取备份数据信息详情
     * @param string $taskuuid uuid
     * @return array
     */
    public function getImportDataDetail(string $taskuuid): array
    {

        $flag = xphp_get_config('app', 'FLAG');

        $where = ' deleted_flag = ? and available_flag = ? and import_flag = ? and task_uuid = ? ';
        $field = " task_uuid, task_name, module_type, task_type, task_create_time as create_time, 
                    count(task_uuid) as point_num, sum(write_size) as size  ";

        $sql = "select " . $field . " from bd_backup_timepoint where " . $where . " group by task_uuid";

        $sqlparam = [$flag['UNSET'], $flag['SET'], $flag['SET'], $taskuuid];

        $data = $this->dbSelect($sql, $sqlparam);

        if (empty($data)) {
            return false;
        }
        $return = [
            'task_uuid' => $data[0]['task_uuid'],
            'task_name' => $data[0]['task_name'],
            'module_type' => $data[0]['module_type'],
            'task_type' => $data[0]['task_type'],
            'create_time' => $data[0]['create_time'],
            'point_num' => $data[0]['point_num'],
            'size' => v1_calsize($data[0]['size'], true),
        ];
        // 处理下详细信息
        return array_merge($return, $this->getTaskPointDetails($data[0]['task_uuid'], $data[0]['module_type']));
    }

    /**
     * 分配导入数据到其他用户
     * @param array $params 参数
     * @return bool|string
     */
    public function distributeDataToUser($params = [])
    {

        $taskuuids = $params['uuids'];
        $useruuid = $params['user_uuid'];

        $userdata = $this->dbSelect("select user_name from bd_user where user_uuid = ?", [$useruuid]);
        if (empty($userdata)) {
            return false;
        }
        $username = $userdata[0]['user_name'];
        $user = xphp_get_user_info();

        $taskuuidsStr = implode("','", $taskuuids);
        $sqlCount = "select count(timepoint_uuid) as point_num 
                        from bd_backup_timepoint where user_uuid = ? and task_uuid in ('$taskuuidsStr')";
        $dataCount = $this->dbSelect($sqlCount, array($useruuid));
        $sql = "update bd_backup_timepoint 
                    set user_uuid = ?, user_name = ?, import_flag = ?  
                    where user_uuid = ? and task_uuid in ('$taskuuidsStr')";
        $this->dbExec(
            $sql,
            [$useruuid, $username, xphp_get_config('app', 'FLAG')['UNSET'], $user['userUuid']]
        );
        $operate = xphp_get_lang('WEB_STORAGE_DATA_TO_USER') . $username;
        $descriptionParams = array($username, intval($dataCount[0]['point_num']));
        $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_DISTRIBUTION_IMPORT_DATA', $descriptionParams);

        return $operate;
    }

    /**
     * 获取添加存储的表格数据
     * @param array $params 请求参数
     * @return array
     */
    public function getAddStorageTable($params = []): array
    {

        $msg = ['storage_type' => $params['storage_type'], 'lan_free_flag' => v1_parse_bool_to_flag(false)];
        $opName = 'NODE_SR_OP_SCAN_LOCAL';
        $mbResult = $this->service()->mbNodeMsgs($opName, $params['node_uuid'], json_encode($msg), true);
        $info = [];
        if ($mbResult['result']) {
            $data = $mbResult['msg']['raw_storage_list'];
            $vmHype = xphp_get_config('vm', 'VMHYPERVISORDES');
            foreach ($data as $d) {
                $info[] = array(
                    'storage_uuid' => $d['name'],
                    'storage_name' => $d['name'],
                    'description' => $this->getStorageScanTypeDes(false, $d),
                    'size' => v1_calsize($d['size'], true),
                    'initflag' => v1_parse_flag_to_bool($d['init_flag']),  //初始化标志
                    'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                    'children' => $d['children'] ?? [],     //孩子节点
                    'hypervisor' => $d['hypervisor'],
                    'hypervisor_des' => $vmHype[$d['hypervisor']],
                    'hypervisor_used' => v1_parse_flag_to_bool($d['hypervisor_used'])

                );
            }
            $total = count($data ?? []);
        }

        return [
            'rows' => $info,
            'total' => $total ?? 0
        ];
    }

    /**
     * 获取存储WWN信息
     * @param array $params 请求参数
     * @return array
     */
    public function getWwnNum($params = []): array
    {

        $mbResult = $this->service()->mbNodeMsgs('NODE_SR_OP_GET_FC_HOST_WWN', $params['node_uuid'], '', true);
        $info = [];
        if (!empty($mbResult['result'])) {
            $data = $mbResult['msg']['wwn_list'];
            $i = 1;
            $des = xphp_get_desc('Pf', 'ONLINEDES');
            foreach ($data as $d) {
                $info[] = array(
                    'num' => $i++,
                    'host_name' => $d['host_name'],
                    'wwnn' => $d['wwnn'],
                    'wwpn' => $d['wwpn'],
                    'speed' => $d['speed'],
                    'status' => $d['state'],
                    'status_des' => $des[$d['state']]
                );
            }
            $total = count($info);
        }

        return [
            'rows' => $info,
            'total' => $total ?? 0
        ];
    }

    /**
     * 得到iscsi名称
     * @param array $params 参数
     * @return array|json
     */
    public function getIscsiName(array $params)
    {

        $opName = 'NODE_SR_OP_GET_ISCSI_INIT_IQN';
        $mbResult = $this->service()->mbNodeMsgs($opName, $params['node_uuid'], json_encode([]), true);
        if (empty($mbResult['result'])) {
            $opcodeDes = (new NodeOpcode())->getOpcodeDes($opName);
            return $this->muOpResult(false, $opcodeDes, '', '', $mbResult['errorCode']);
        }

        return [
            'name' => $mbResult['msg']['initiator_name']
        ];
    }

    /**
     * 获取IQN对应的LUN信息
     * @param array $params 请求参数
     * @return array
     */
    public function getIscsiLunTable($params = []): array
    {

        $iscsiTargetList = [];
        foreach ($params['server_list'] as $server) {
            // 这里判断处理下是否是ipv6，是的话，就加上中括号
            if (filter_var($server['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                // 是 ipv6
                $server['ip'] = '[' . inet_ntop(inet_pton($server['ip'])) . ']'; // 获取缩写后的ipv6
            }
            $iscsiTargetList[] = [
                'iscsi_target' => $server['ip'] . ':' . $server['port']
            ];
        }

        $msg = [
            'iscsi_target_list' => $iscsiTargetList,
            'username' => '',
            'password' => '',
            'lan_free_flag' => v1_parse_flag_to_bool(false)
        ];
        $opName = 'NODE_SR_OP_SCAN_ISCSI';
        $mbResult = $this->service()->mbNodeMsgs($opName, $params['node_uuid'], json_encode($msg), true);

        if (!$mbResult['result']) {
            $opcodeDes = (new NodeOpcode())->getOpcodeDes($opName);
            return $this->muOpResult($mbResult['result'], $opcodeDes, $mbResult['msg'], '', $mbResult['errorCode']);
        }

        $iqnArr = $target = $info = [];
        if ($mbResult['result']) {
            $data = $mbResult['msg']['raw_storage_list'];
            $vmHype = xphp_get_config('vm', 'VMHYPERVISORDES');
            foreach ($data as $d) {
                $iqn = str_replace('|', '<br>', substr($d['iqn'], 0, -1));
                $isopen = (bool) intval($d['size']);
                if ($iqn && !in_array($iqn, array_column($iqnArr, 'iqn'))) {
                    $iqnArr[] = [
                        'iqn' => $iqn,
                        'isopen' => $isopen,
                        'iscsi_target' => $d['iscsi_target'], // target信息
                    ];
                }

                if (!in_array($d['iscsi_target'], array_column($target, 'iscsi_target'))) {
                    $target[] = [
                        'iqn' => $iqn,
                        'isopen' => (bool) $iqn,
                        'iscsi_target' => $d['iscsi_target'], // target信息
                    ];
                }

                if (!$isopen || !$iqn) {
                    continue;
                }

                // 重新组合下列表
                $info[] = [
                    'id' => $d['name'],
                    'pid' => $iqn,
                    'name' => $d['name'] . ' ' . $this->getStorageScanTypeDes(false, $d)
                        . ' ' . v1_calsize($d['size'], true),
                    'nocheck' => false,
                    'clickshow' => false,
                    'is_parent' => false,
                    'open' => false,
                    'iscsi_target' => $d['iscsi_target'], // target信息
                    'initflag' => v1_parse_flag_to_bool($d['init_flag']),  //初始化标志
                    'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                    // 'children' => $d['children'] ?? [],     //孩子节点
                    'children' => [],     //孩子节点
                    'hypervisor' => $d['hypervisor'],
                    'hypervisordes' => $vmHype[$d['hypervisor']],
                    'hypervisor_used' => v1_parse_flag_to_bool($d['hypervisor_used']),
                ];
            }

            // 再次重新组织下
            $return = [];
            foreach ($target as $item) {
                $return[] = [
                    'id' => $item['iscsi_target'],
                    'pid' => 0,
                    'name' => $item['iscsi_target'],
                    'nocheck' => true,
                    'clickshow' => !$item['isopen'],
                    'is_parent' => true,
                    'open' => $item['isopen'],
                    'iscsi_target' => $item['iscsi_target'],
                ];
            }

            foreach ($iqnArr as $item) {
                $return[] = [
                    'id' => $item['iqn'],
                    'pid' => $item['iscsi_target'],
                    'name' => $item['iqn'],
                    'nocheck' => true,
                    'clickshow' => !$item['isopen'],
                    'is_parent' => true,
                    'open' => $item['isopen'],
                    'iscsi_target' => $item['iscsi_target'],
                ];
            }

            $info = array_merge($return, $info);
            $total = count($info);
        }

        return [
            'rows' => $info,
            'total' => $total ?? 0
        ];
    }

    /**
     * 进行chap认证信息填写后再次扫描-第一次认证
     * @param array $params 请求参数
     * @return array
     */
    public function getIscsiChapList1($params = []): array
    {

        $msg = [
            'iscsi_target' => $params['iscsi_target'],
            'discover_chap_username' => $params['username'],
            'discover_chap_password' => $params['passwd'],
        ];
        $opName = 'NODE_SR_OP_SCAN_ISCSI_WITH_DISCOVER_CHAP';
        $mbResult = $this->service()->mbNodeMsgs($opName, $params['node_uuid'], json_encode($msg), true);

        if (!$mbResult['result']) {
            $opcodeDes = (new NodeOpcode())->getOpcodeDes($opName);
            return $this->muOpResult($mbResult['result'], $opcodeDes, $mbResult['msg'], '', $mbResult['errorCode']);
        }

        $info = [];
        if ($mbResult['result']) {
            $vmHype = xphp_get_config('vm', 'VMHYPERVISORDES');
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d) {
                $iqn = str_replace('|', '<br>', substr($d['iqn'], 0, -1));
                $isopen = (bool) intval($d['size']);
                // 重新组合下列表
                $info[] = [
                    'id' => $iqn,
                    'pid' => $d['iscsi_target'],
                    'name' => $iqn,
                    'nocheck' => true,
                    'clickshow' => !$isopen,
                    'is_parent' => true,
                    'open' => $isopen,
                    'title' => $d['name'],
                    'iscsi_target' => $d['iscsi_target'], // target信息
                    'initflag' => v1_parse_flag_to_bool($d['init_flag']),  //初始化标志
                    'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                    'childrens' => $d['children'] ?? [],     //孩子节点
                    'hypervisor' => $d['hypervisor'],
                    'hypervisordes' => $vmHype[$d['hypervisor']],
                    'hypervisor_used' => v1_parse_flag_to_bool($d['hypervisor_used'])
                ];
                if ($d['size']) {
                    // 表示是展开的，那么需要读取出最下面的一层的信息
                    $info[] = [
                        'id' => $d['name'],
                        'pid' => $iqn,
                        'name' => $d['name'] . ' ' . $this->getStorageScanTypeDes(false, $d)
                            . ' ' . v1_calsize($d['size']),
                        'nocheck' => false,
                        'clickshow' => false,
                        'is_parent' => false,
                        'open' => false,
                        'title' => $d['name'],
                        'iscsi_target' => $d['iscsi_target'], // target信息
                        'initflag' => v1_parse_flag_to_bool($d['init_flag']),  //初始化标志
                        'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                        'childrens' => $d['children'] ?? [],     //孩子节点
                        'hypervisor' => $d['hypervisor'],
                        'hypervisordes' => $vmHype[$d['hypervisor']],
                        'hypervisor_used' => v1_parse_flag_to_bool($d['hypervisor_used'])
                    ];
                }
            }
        }
        return [
            'rows' => $info,
            'total' => count($info)
        ];
    }

    /**
     * 进行chap认证信息填写后再次扫描-第二次认证
     * @param array $params 请求参数
     * @return array
     */
    public function getIscsiChapList($params = []): array
    {

        $msg = [
            'iscsi_target' => $params['iscsi_target'],
            'username' => $params['username'],
            'passwd' => $params['passwd'],
            'target_iqn' => $params['target_iqn'],
        ];
        $opName = 'NODE_SR_OP_SCAN_ISCSI_WITH_CHAP';
        $mbResult = $this->service()->mbNodeMsgs($opName, $params['node_uuid'], json_encode($msg), true);

        if (!$mbResult['result']) {
            $opcodeDes = (new NodeOpcode())->getOpcodeDes($opName);
            return $this->muOpResult($mbResult['result'], $opcodeDes, $mbResult['msg'], '', $mbResult['errorCode']);
        }

        $info = [];
        if ($mbResult['result']) {
            $vmHype = xphp_get_config('vm', 'VMHYPERVISORDES');
            $data = $mbResult['msg']['raw_storage_list'];
            foreach ($data as $d) {
                $iqn = str_replace('|', '<br>', substr($d['iqn'], 0, -1));
                // 重新组合下列表
                $info[] = [
                    'id' => $d['name'],
                    'pid' => $iqn,
                    'name' => $d['name'] . ' ' . $this->getStorageScanTypeDes(false, $d)
                        . ' ' . v1_calsize($d['size']),
                    'nocheck' => false,
                    'clickshow' => false,
                    'is_parent' => false,
                    'open' => false,
                    'title' => $d['name'],
                    'iscsi_target' => $d['iscsi_target'], // target信息
                    'initflag' => v1_parse_flag_to_bool($d['init_flag']),  //初始化标志
                    'timepointcount' => intval($d['timepoint_count']),      //时间点个数
                    'childrens' => $d['children'] ?? [],     //孩子节点
                    'hypervisor' => $d['hypervisor'],
                    'hypervisordes' => $vmHype[$d['hypervisor']],
                    'hypervisor_used' => v1_parse_flag_to_bool($d['hypervisor_used'])
                ];
            }
        }
        return [
            'rows' => $info,
            'total' => count($info)
        ];
    }

    /**
     * 获取云存储的云服务商列表
     * @param array $params 请求参数
     * @return array
     */
    public function getVendors($params = []): array
    {

        $list = xphp_get_config('resource', 'STORAGE_VENDOR');
        $return = [];
        foreach ($list as $item => $value) {
            $return[] = [
                'text' => xphp_get_lang($value),
                'value' => $item
            ];
        }
        return [
            'rows' => $return,
            'total' => count($list) ?? 0
        ];
    }

    /**
     * 获取云存储bucket folder列表
     * @param array $params 请求参数
     * @return array|json
     */
    public function getBucketFolder($params = [])
    {

        $msg = [
            'vendor' => intval($params['vendor']),
            'region' => $params['region'] ?? '',
            'username' => $params['username'],
            'passwd' => $params['password'],
            'bucket' => $params['bucket'],
            'use_ssl_flag' => v1_parse_bool_to_flag(boolval($params['ssl_flag'])),
            'service_endpoint' => $params['server_node'] ?? ''
        ];
        $opName = 'NODE_SR_OP_GET_CLOUD_FOLDER';

        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg), true);

        $opcodeDes = (new NodeOpcode())->getOpcodeDes($opName);

        if (!$mbResult['result']) {
            return $this->muOpResult(false, $opcodeDes, '', '', $mbResult['errorCode']);
        }
        $info = [];
        $folderList = $mbResult['msg']['folder_list'] ?? [];
        if (!empty($folderList)) {
            foreach ($folderList as $folder) {
                $info[] = [
                    'text' => $folder,
                    'value' => $folder
                ];
            }
            $total = count($folderList);
        }

        return [
            'rows' => $info,
            'immutable_flag' => $mbResult['msg']['immutable_flag'] == 1, // 1 开 2关 worm存储开关状态
            'total' => $total ?? 0
        ];
    }

    /**
     * 获取CBR区域
     * @param array $params 请求参数
     * @return array|json
     */
    public function getCBRArea($params = [])
    {

        $pfMsg = [
            'storage_uuid' => '', // 获取区域时默认传空
            'access_key_id' => $params['access_key_id'],
            'secret_access_key' => $params['secret_access_key'],
        ];

        $opName = 'NODE_HUAWEI_CBR_OP_GET_REGIONS';

        $nodeuuid = (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($pfMsg), true);

        $opcodeDes = (new NodeOpcode())->getOpcodeDes($opName);

        if (!$mbResult['result']) {
            return $this->muOpResult(false, $opcodeDes, '', '', $mbResult['errorCode']);
        }
        $info = [];
        $arealist = $mbResult['msg']['regions'];
        if (empty($arealist)) {
            return $this->muOpResult(false, $opcodeDes, xphp_get_lang('WEB_STORAGE_NOT_GET_REGION'), 'info');
        }
        foreach ($arealist as $area) {
            $info[] = [
                'nameen' => $area['locales']['en-us'],
                'namecn' => $area['locales']['zh-cn'],
                'id' => $area['id']
            ];
        }
        return [
            'rows' => $info,
            'total' => count($arealist) ?? 0
        ];
    }

    /**
     * 根据存储类型获取新的存储名称
     * @param array $params 请求参数
     * @return array|json
     */
    public function getNewStorageName($params = [])
    {
        $storageType = $params['storage_type'];
        $typeDes = $this->getStorageTypeDes($storageType);

        $sql = "select storage_nickname from bd_storage_resource where storage_nickname like ? ";
        $data = $this->dbSelect($sql, [$typeDes . '%']);

        if (empty($data)) {
            return ['name' => $typeDes . '1'];
        }

        // 取出任务名后面的编号并降序
        $array = str_replace($typeDes, '', array_column($data, 'storage_nickname'));
        $key = array_map('intval', $array);

        return ['name' => $typeDes . (max($key) + 1)];
    }

    /**
     * 获取云存储region
     * @param array $params 请求参数
     * @return array
     */
    public function getCloudRegion($params = []): array
    {

        $cloud = xphp_get_config('cloud_vendor', 'CLOUD_STORAGE_REGION');
        $array = [
            1 => $cloud['AWS_REGION'],  //aws
            3 => $cloud['ALI_REGION'],   //ali
            4 => $cloud['HUAWEI_REGION'],    //huawei
            5 => $cloud['TENCENT_REGION'],   //tencent
            7 => $cloud['WASABI_REGION'],   //wasabi
        ];

        $region = $array[$params['vendor']] ?? [];

        $info = [];
        foreach ($region as $r) {
            $info[] = array(
                'text' => xphp_get_lang($r['text']),
                'value' => $r['value']
            );
        }
        return [
            'rows' => $info,
            'total' => count($region) ?? 0
        ];
    }

    /**
     * 得到存储类型描述
     * @param int $storageType BD_STORAGE_TYPE
     * @return string
     */
    public function getStorageTypeDes(int $storageType): string
    {
        $des = '';
        if (empty($storageType)) {
            return $des;
        }
        $ptDes = xphp_get_desc('Pf', 'STORAGETYPE');
        return xphp_get_lang($ptDes[$storageType]);
    }

    /**
     * 统一发送结果到UI
     * @param string $opName   操作键名
     * @param array  $mbResult 操作结果
     * @return array
     */
    private function unifyMuOpResult(string $opName, array $mbResult): array
    {
        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
    }

    /**
     * 得到存储扫描的类型列显示结果
     * 添加备份存储显示标准类型 ; 添加lanfree存储显示虚拟化类型或者空
     * @param boolean $lanfreeFlag lanfree标志
     * @param array   $eachData    每项数据
     * @return string
     */
    private function getStorageScanTypeDes(bool $lanfreeFlag, array $eachData): string
    {
        if (!$lanfreeFlag) {
            if ($eachData['type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['PARTITION']) {
                $ptDes = xphp_get_desc('Pf', 'PARTITIONTYPE');
                return $ptDes[intval($eachData['part_tran'])] ?? '';
            }

            if (empty($eachData['type'])) {
                return '';
            }

            $ptDes = xphp_get_desc('Pf', 'STORAGETYPE');
            return $ptDes[$eachData['type']] ?? '';
        }

        return xphp_get_config('vm', 'VMHYPERVISORDES')[intval($eachData['hypervisor'])];
    }

    /**
     * 得到每一个存储的配置信息
     * @param int  $type        存储类型
     * @param json $config      存储配置详情
     * @param $storageuuid uuid
     * @return array
     */
    private function getStorageConfig($type, $config, $storageuuid)
    {

        $type = intval($type);
        $config = json_decode($config, true);
        $details = array(
            'type' => $type,                    //存储类型
            'dev_path' => $config['pathname'],  //设备源(公用)
            'storageuuid' => $storageuuid        //存储UUID
        );

        $dbstoragetype = xphp_get_config('resource', 'BD_STORAGE_TYPE');

        switch ($type) {
            case $dbstoragetype['DISK']:
                $details['disk_type'] = $this->getStorageDevDes($config);       //磁盘类型(磁盘)
                break;
            case $dbstoragetype['PARTITION']:
                $ptDes = xphp_get_desc('Pf', 'PARTITIONTYPE');
                $partitionType = intval($config['part_tran']);
                $details['partition_type'] = $ptDes[$partitionType];      //分区类型(分区)
                break;
            case $dbstoragetype['FC']:
                $details['fc_type'] = $this->getStorageDevDes($config);      //fc类型(fc)
                break;
            case $dbstoragetype['ISCSI']:
                $details['iscsi_type'] = $this->getStorageDevDes($config);      //iscsi类型(iscsi)
                $host = '';
                foreach ($config['iscsi_target_list'] as $iscsitarget) {
                    $host .= $iscsitarget['iscsi_target'] . ',';
                }
                $details['iscsi_host'] = substr($host, 0, -1);          //iscsi服务器(iscsi)
                break;
            case $dbstoragetype['CIFS']:
                $details['username'] = $config['username'];          //用户名
                break;
            case $dbstoragetype['REMOTE']:
                $details['dev_path'] = $config['pathname'];          //异地系统IP
                $details['username'] = $config['username'];
                $details['remote_ip'] = $config['remote_ip'];
                $details['remote_node_ip'] = $config['remote_node_ip'];
                $details['remote_storage_nickname'] = $config['remote_storage_nickname'];
                $details['remote_storage_type'] = $config['remote_storage_type'];
                break;
            case $dbstoragetype['CLOUD']:
                $vendorInfo = $this->getVendor(
                    intval($config['vendor']),
                    $config['region'],
                    $config['service_endpoint']
                );
                $details['vendor'] = $vendorInfo['vendor_des'];
                $details['region'] = $vendorInfo['region_des'];
                $details['user_key'] = $config['username'];
                $details['dev_path'] = $config['pathname'];
                $details['vendor_type'] = intval($config['vendor']);
                $serviceNode = $config['service_endpoint'];
                //如果服务节点为空
                if (empty($serviceNode)) {
                    $serviceNode = xphp_get_config('app', 'NULLSPACE');
                }
                $details['service_endpoint'] = $serviceNode;
                break;                                               //云存储
            case $dbstoragetype['FILE_SYSTEM']: // 并行文件系统
                $vendorInfo = $this->getVendor(
                    intval($config['vendor']),
                    $config['region'],
                    $config['service_endpoint']
                );
                $details['vendor'] = $vendorInfo['vendor_des'];
                $details['region'] = $vendorInfo['region_des'];
                $details['user_key'] = $config['username'];
                $details['dev_path'] = $config['pathname'];
                $details['vendor_type'] = intval($config['vendor']);
                $serviceNode = $config['service_endpoint'];
                //如果服务节点为空
                if (empty($serviceNode)) {
                    $serviceNode = xphp_get_config('app', 'NULLSPACE');
                }
                $details['service_endpoint'] = $serviceNode;
                break;
            case $dbstoragetype['DDDB']:
                $details['auth_type'] = $config['auth_type'];       // 类型、user/kerberos
                $details['username'] = $config['username'];       // 用户名
                $details['data_domain_system'] = $config['data_domain_system'];       // 存储服务器
                $details['storage_unit'] = $config['storage_unit'];       // 存储单元
                break;
            case $dbstoragetype['LOCALDIR']:
            case $dbstoragetype['NFS']:
            case $dbstoragetype['LVM']:
            default:
                break;
        }

        return $details;
    }

    /**
     * 得到设备类型描述,适用于本地磁盘/iscsi/fc/
     * @param array $config 配置数组
     * @return string
     */
    private function getStorageDevDes($config)
    {
        $vender = trim($config['vendor']);
        $model = trim($config['model']);
        $des = $vender . ' ' . $model;
        return trim($des);
    }

    /**
     *
     * @param int    $vendor      云存储类型
     * @param string $region      云存储地区
     * @param string $serverPoint 云存储服务终端节点
     * @return string[]|mixed[]|unknown[]
     */
    private function getVendor(int $vendor, $region = '', $serverPoint = '')
    {

        $vendorDes = $nullspace = xphp_get_config('app', 'NULLSPACE');
        $regionDes = $region;
        //地区为空
        if (empty($region)) {
            $regionDes = $nullspace;
        }
        $regionInfo = array();
        $cloudstorageregion = xphp_get_config('cloud_vendor', 'CLOUD_STORAGE_REGION');

        switch ($vendor) {
            case 1:
                $vendorDes = 'AWS S3';
                $regionInfo = $cloudstorageregion['AWS_REGION'];
                break;
            case 2:
                $vendorDes = 'Azure';
                $regionDes = $nullspace;
                break;
            case 3:
                $vendorDes = xphp_get_lang('UI_STORAGE_CLOUD_VENDOR_ALI');
                $regionInfo = $cloudstorageregion['ALI_REGION'];
                break;
            case 4:
                $vendorDes = xphp_get_lang('UI_STORAGE_CLOUD_VENDOR_HUAWEI');
                $regionInfo = $cloudstorageregion['HUAWEI_REGION'];
                break;
            case 5:
                $vendorDes = xphp_get_lang('UI_STORAGE_CLOUD_VENDOR_TENCENT');
                $regionInfo = $cloudstorageregion['TENCENT_REGION'];
                break;
            case 6:
                $vendorDes = 'Ceph S3';
                break;
            case 7:
                $vendorDes = 'Wasabi';
                $regionInfo = $cloudstorageregion['WASABI_REGION'];
                break;
            case 8:
                $vendorDes = "MinIO";
                break;
            case 9:
                $vendorDes = 'Huawei OceanStor Pacific';
                break;
            case 10:
                $vendorDes = xphp_get_lang('UI_STORAGE_CLOUD_VENDOR_OTHER');
                break;
        }

        if (!empty($regionInfo)) {
            foreach ($regionInfo as $r) {
                if ($r['value'] == $region) {
                    $regionDes = xphp_get_lang($r['text']);
                }
                //wasabi云存储
                if ($vendor == 7 && $r['value'] == $serverPoint) {
                    $regionDes = xphp_get_lang($r['text']);
                }
            }
        }

        return array(
            'vendor_des' => $vendorDes,
            'region_des' => $regionDes
        );
    }

    /**
     * 检测存储是否在正在运行的任务中,如果在的话,直接退出程序,给予提示
     * @param string $storageuuid 资源uuid
     * @return json
     */
    private function checkStorageInRunningTask($storageuuid = '')
    {
        $sql = "select task_name from bd_task where task_status = ? and storage_uuid = ?";
        $sqlParams = array(xphp_get_config('task', 'TASKSTATUS')['RUNNING'], $storageuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $tasks = array_column($data, 'task_name');

        if (count($tasks) > 0) {
            $tasksStr = implode(',', $tasks);
            exit($this->muOpResult(
                false,
                xphp_get_lang('WEB_STORAGE_DELETE_FAILURE'),
                $tasksStr . xphp_get_lang('WEB_STORAGE_DELETE_FAILURE_TIPS')
            ));
        }
    }

    /**
     * 检测并过滤存储删除结果
     * NFS/CIFS删除失败的时候需要判断存储是否是离线状态,离线的时候是无法删除的,需要提示用户重启备份系统,然后再执行删除操作
     * @param string $storageuuid 存储UUID
     * @param array  $mbResult    后台删除存储结果
     * @return josn|bool
     */
    private function checkStorageDeleteResult($storageuuid = '', $mbResult = [])
    {
        if ($mbResult['result']) {
            //如果删除是成功的,直接返回
            return true;
        }
        //如果删除失败,先检测是否是CIFS/NFS,并且是否是离线状态
        $sql = "select storage_nickname, storage_type, status, mount_flag, source_type 
                    from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $storageType = intval($data[0]['storage_type']);
        $status = intval($data[0]['status']);
        $sourceType = intval($data[0]['source_type']); //区分备份存储还是生产存储，1备份2生产
        if ($sourceType == 2) {
            //生产存储自定义返回
            exit($this->muOpResult(
                false,
                xphp_get_lang('WEB_PUBLIC_FAILURE'),
                $data[0]['storage_nickname'] . xphp_get_lang('WEB_STORAGE_DELETE_PRODUCTION_STORAGE_FAILURE_TIPS'),
                'warning',
                $mbResult['errorCode']
            ));
        }

        $dbType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        if (!in_array($storageType, [$dbType['NFS'], $dbType['CIFS']])) {
            //如果不是NFS/CIFS,直接返回
            return true;
        }

        $storageStatus = xphp_get_config('resource', 'STORAGE_STATUS');
        if ($status == $storageStatus['OFFLINE']) {
            //如果是离线状态,报错,并直接退出
            $operate = $this->getUnifyOpcodeDes('NODE_SR_OP_DELETE');
            exit($this->muOpResult(
                false,
                $operate . xphp_get_lang('WEB_PUBLIC_FAILURE'),
                $data[0]['storage_nickname'] . xphp_get_lang('WEB_STORAGE_DELETE_NFS_CIFS_FAILURE_TIPS'),
                'warning'
            ));
        }
        return true;
    }

    /**
     * 节点不在线时,通过WEB自己删除存储
     * @param string $storageuuid 资源uuid
     * @return bool
     */
    private function deleteStorageByWeb(string $storageuuid)
    {
        $sql = "select bsr.storage_nickname, bn.ip, bn.host_name, bn.node_nickname from 
                bd_storage_resource bsr, bd_node bn where bsr.node_uuid = bn.node_uuid and bsr.storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $name = empty($data[0]['node_nickname']) ? $data[0]['host_name'] : $data[0]['node_nickname'];
        $ip = $data[0]['ip'];
        $descriptionParam = array($data[0]['storage_nickname'], $name, $ip);

        //开始事务
        $this->dbBeginTransaction();
        //查找所有使用存储的时间点
        $sql = "select timepoint_uuid from bd_backup_timepoint where storage_uuid = ? ";
        $data = $this->dbSelect($sql, array($storageuuid));
        $timepoint = '';
        foreach ($data as $d) {
            $timepoint .= "'" . $d['timepoint_uuid'] . "',";
        }
        $timepoint = substr($timepoint, 0, -1);
        if (empty($timepoint)) {
            $timepoint = "''"; //防止为空的时候SQL出错
        }

        //删除bd_backup_timepoint
        $sql = "delete from bd_backup_timepoint where storage_uuid = ?";
        $result = $this->dbExec($sql, array($storageuuid));

        //删除vm_backup_timepoint
        $sql = "delete from vm_backup_timepoint where timepoint_uuid in ($timepoint)";
        $result = $result && $this->dbExec($sql);

        //删除fs_backup_timepoint
        $sql = "delete from fs_backup_timepoint where fs_timepoint_uuid in ($timepoint)";
        $result = $result && $this->dbExec($sql);

        //删除bd_storage_resource
        $sql = "delete from bd_storage_resource where storage_uuid = ?";
        $result = $result && $this->dbExec($sql, array($storageuuid));

        if ($result) {
            $this->dbCommit();
            $this->systemLog('BD_SYSTEMLOG_DESC_KEY_STORAGE_DELETE', $descriptionParam);
        } else {
            $this->dbRollBack();
            $this->systemLog(
                'BD_SYSTEMLOG_DESC_KEY_STORAGE_DELETE',
                $descriptionParam,
                xphp_get_config('log', 'LOGLEVEL')['ERROR']
            );
        }

        return $result;
    }

    /**
     * 检查名字是否存在
     * @param string $name 名称
     * @param int    $flag flag
     * @return bool|string
     */
    private function checkStorageName(string $name, int $flag)
    {
        $sql = "select storage_uuid from bd_storage_resource where storage_nickname = ? and lan_free_flag = ? ";
        $data = $this->dbSelect($sql, array($name, $flag));

        return !empty($data) ? $data[0]['storage_uuid'] : false;
    }

    /**
     * 获取修改云存储的剩余空间大小
     * @param string $storageuuid  资源uuid
     * @param bigint $warningValue 告警值
     * @return numeric
     */
    private function getCloudStorageFreeSize(string $storageuuid, $warningValue = 0)
    {
        $sql = "select total_size, free_size from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        $usedSize = intval($data[0]['total_size']) - intval($data[0]['free_size']);
        return $warningValue - $usedSize;
    }

    /**
     * 得到任务的详情
     * 文件是文件列表
     * 虚拟机是虚拟机列表
     * @param string $taskuuid   任务uuid
     * @param int    $moduletype 模块类型
     * @return array
     */
    private function getTaskPointDetails(string $taskuuid, int $moduletype): array
    {
        $moduletypes = xphp_get_config('module', 'MODULE_TYPE');
        $moduleArr = [
            $moduletypes['FS'] => 'getFSPointDetails', // 文件
            $moduletypes['VM'] => 'getVMPointDetails',  // 虚拟机
            $moduletypes['BACKUP_COPY_CLIENT'] => 'getCopyPointDetails',     // 副本|归档
            $moduletypes['DB'] => 'getDBPointDetails',   // 数据库
            $moduletypes['OS'] => 'getOSPointDetails',  // 操作系统
            $moduletypes['NAS'] => 'getFSPointDetails',  // nas
            $moduletypes['M365'] => 'getM365PointDetails',  // M365
        ];

        if (!empty($moduleArr[$moduletype])) {
            $method = $moduleArr[$moduletype];
            return $this->$method($taskuuid, $moduletype);
        }
        return [];
    }

    /**
     * 获取文件的备份列表
     * @param string $taskuuid   任务uuid
     * @param int    $moduletype 模块类型
     * @return array
     */
    private function getFSPointDetails(string $taskuuid, int $moduletype): array
    {
        $sql = "select distinct(fbt.agent_uuid),fbt.agent_ip, fbt.agent_name 
                from fs_backup_timepoint fbt, bd_backup_timepoint bbt 
                where fbt.fs_timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $nameIP = array();
        if ($moduletype == xphp_get_config('module', 'MODULE_TYPE')['FS']) {
            foreach ($data as $each) {
                $nameIP[] = $each['agent_name'] . '(' . $each['agent_ip'] . ')';
            }
        } else {
            foreach ($data as $each) {
                $nameIP[] = $each['agent_ip'] . '(' . $each['agent_name'] . ')';
            }
        }

        return [
            'module_des' => $this->getModuleTypeDes($moduletype),
            'details_des' => xphp_get_lang('UI_DB_BACKUP_AGENT_LIST'),
            'details' => $nameIP
        ];
    }

    /**
     * 获取exchange/m365的备份列表
     * @param string $taskuuid   任务uuid
     * @param int    $moduletype 模块类型
     * @return array
     */
    private function getM365PointDetails(string $taskuuid, int $moduletype): array
    {

        $sql = "select mbt.organization_info,mbt.user_config
                from bd_backup_timepoint bbt,m365_backup_timepoint mbt 
                where bbt.timepoint_uuid = mbt.backup_timepoint_uuid 
                  and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $nameIP = array();
        $name = '';
        foreach ($data as $infos) {
            $info = json_decode($infos['organization_info'], true);
            $config = json_decode($infos['user_config'], true);
            $name = $info['organization_name'];
            if (!empty($config['backup_m365_object_info_list'])) {
                foreach ($config['backup_m365_object_info_list'] as $item) {
                    $nameIP[] = $info['organization_name'] .
                        (!empty($item['backup_object_mail']) ? '(' . $item['backup_object_mail'] . ')' : '');
                }
            }
        }

        return [
            'module_des' => $this->getModuleTypeDes($moduletype),
            'details_des' => xphp_get_lang('UI_PLATFORM_VOL_CDP_BACKUPSET'),
            'details' => array_unique($nameIP),
            'other_info' => $name,
        ];
    }

    /**
     * 获取虚拟机的备份列表
     * @param string $taskuuid   任务uuid
     * @param int    $moduletype 模块类型
     * @return array
     */
    private function getVMPointDetails(string $taskuuid, int $moduletype): array
    {
        $sql = "select vbt.hypervisor_type, vbt.dir_path from vm_backup_timepoint vbt, bd_backup_timepoint bbt 
                where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $path = array();
        foreach ($data as $d) {
            if (!in_array($d['dir_path'], $path)) {
                $path[] = $d['dir_path'];
            }
        }

        return array(
            'module_des' => $this->getModuleTypeDes($moduletype, $data[0]['hypervisor_type']),
            'details_des' => xphp_get_lang('WEB_STORAGE_BACKUP_VM_LIST'),
            'details' => $path
        );
    }

    /**
     * 获取虚拟机的副本列表
     * @param string $taskuuid   任务uuid
     * @param int    $moduletype 模块类型
     * @return array
     */
    private function getCopyPointDetails(string $taskuuid, int $moduletype): array
    {
        //先查询任务类型
        $sqlTasktype = "SELECT DISTINCT(task_uuid),task_type FROM bd_backup_timepoint where task_uuid = ?";
        $resultTasktype = $this->dbSelect($sqlTasktype, array($taskuuid));
        //得到任务类型,根绝任务类型来判断是什么类型
        $tasktype = $resultTasktype[0]['task_type'];

        $detailsDes = '';
        $path = array();
        $taskType = xphp_get_config('task', 'TASKTYPE');
        switch ($tasktype) {
            //虚拟机
            case $taskType['BACKUP_COPY']:
                // no break;
            case $taskType['BACKUP_COPY_FETCH']:
                // no break;
            case $taskType['ARCHIVE']:
                // no break;
            case $taskType['ARCHIVE_FETCH']:
                $sql = "select bbt.task_type, vbt.dir_path as dir_path 
                        from vm_backup_timepoint vbt, bd_backup_timepoint bbt
                        where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ?";
                $detailsDes = xphp_get_lang('WEB_PLATFORM_DES_VM');

                $data = $this->dbSelect($sql, array($taskuuid));
                $i = 0; //计数显示多少个数目
                foreach ($data as $d) {
                    if (!in_array($d['dir_path'], $path) && $i <= 10) {
                        $path[] = $d['dir_path'];
                        $i++;
                    }
                    if ($i > 10) {
                        $path[] = '...';
                        break;
                    }
                }
                break;
            //数据库
            case $taskType['DB_BACKUP_COPY']:
                // no break;
            case $taskType['DB_BACKUP_COPY_FETCH']:
                $sql = "select bbt.task_type, vbt.dir_path as dir_path 
                        from db_backup_timepoint vbt, bd_backup_timepoint bbt
                        where bbt.timepoint_uuid = vbt.timepoint_uuid and bbt.task_uuid = ?";
                $detailsDes = xphp_get_lang('WEB_PLATFORM_DES_DB');

                $data = $this->dbSelect($sql, array($taskuuid));
                $i = 0; //计数显示多少个数目
                foreach ($data as $d) {
                    if (!in_array($d['dir_path'], $path) && $i <= 10) {
                        $path[] = $d['dir_path'];
                        $i++;
                    }
                    if ($i > 10) {
                        $path[] = '...';
                        break;
                    }
                }
                break;
            //文件
            case $taskType['FILE_BACKUP_COPY']:
                // no break;
            case $taskType['FILE_BACKUP_COPY_FETCH']:
                $sql = "select fbt.backup_path_list as dir_path 
                        from bd_backup_timepoint bbt, fs_backup_timepoint fbt  
                        where bbt.timepoint_uuid = fbt.fs_timepoint_uuid and bbt.task_uuid = ?";
                $detailsDes = xphp_get_lang('WEB_PLATFORM_DES_FS');

                $data = $this->dbSelect($sql, array($taskuuid));
                $i = 0;
                foreach ($data as $d) {
                    $dirpathlist = json_decode($d['dir_path'], true);
                    foreach ($dirpathlist as $op) {
                        $dirpath = $op['backup_path'];
                        if (!in_array($dirpath, $path) && $i <= 10) {
                            $path[] = $dirpath;
                            $i++;
                        }
                        if ($i > 10) {
                            $path[] = '...';
                            break;
                        }
                    }
                    if ($i > 10) {
                        break;
                    }
                }
                break;
            case $taskType['OS_BACKUP_COPY']:
                // no break;
            case $taskType['OS_BACKUP_COPY_FETCH']:
                $sql = "select distinct(obt.agent_uuid), bbt.task_type, obt.agent_ip, 
                                        obt.os_name, obt.dir_path as dir_path 
                            from os_backup_timepoint obt, bd_backup_timepoint bbt
                            where bbt.timepoint_uuid = obt.timepoint_uuid and bbt.task_uuid = ?";
                $detailsDes = xphp_get_lang('WEB_PLATFORM_DES_OS');
                $data = $this->dbSelect($sql, array($taskuuid));
                $oshandler = new JobInfo();
                $agentList = $oshandler->getAllAgentList();
                $i = 0; //计数显示多少个数目
                foreach ($data as $d) {
                    if (!in_array($d['os_name'] . '(' . $d['agent_ip'] . ')', $path) && $i <= 10) {
                        $path[] = $oshandler->getAgentNameByList(
                            $agentList,
                            $d['agent_uuid'],
                            $d['os_name'],
                            $d['agent_ip']
                        );
                        $i++;
                    }
                    if ($i > 10) {
                        $path[] = '...';
                        break;
                    }
                }
                break;
        }

        $moduleDes = $this->getModuleTypeDes($moduletype, '', $tasktype);
        return array(
            'module_des' => $moduleDes,
            'details_des' => $detailsDes . $moduleDes,
            'details' => $path
        );
    }

    /**
     * 获取数据库的备份列表
     * @param string $taskuuid   任务uuid
     * @param int    $moduletype 模块类型
     * @return array
     */
    private function getDBPointDetails(string $taskuuid, int $moduletype): array
    {
        $sql = "select distinct dbt.dir_path from bd_backup_timepoint bbt, db_backup_timepoint dbt
                where bbt.timepoint_uuid = dbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $path = array();
        foreach ($data as $l) {
            $path[] = $l['dir_path'];
        }

        return array(
            'module_des' => $this->getModuleTypeDes($moduletype),
            'details_des' => xphp_get_lang('WEB_STORAGE_BACKUP_DB_LIST'),
            'details' => $path
        );
    }

    /**
     * 获取操作系统的备份列表
     * @param string $taskuuid   任务uuid
     * @param int    $moduletype 模块类型
     * @return array
     */
    private function getOSPointDetails(string $taskuuid, int $moduletype): array
    {
        $sql = "select distinct(obt.agent_uuid),obt.agent_ip, obt.os_name 
                from os_backup_timepoint obt, bd_backup_timepoint bbt 
                where obt.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $oshandler = new JobInfo();
        $agentList = $oshandler->getAllAgentList();
        $nameIP = array();
        foreach ($data as $each) {
            $nameIP[] = $oshandler->getAgentNameByList(
                $agentList,
                $each['agent_uuid'],
                $each['os_name'],
                $each['agent_ip']
            );
        }

        return array(
            'module_des' => $this->getModuleTypeDes($moduletype),
            'details_des' => xphp_get_lang('UI_DB_BACKUP_AGENT_LIST'),
            'details' => $nameIP
        );
    }

    /**
     * 扫描异地备份系统类型资源
     * @param array $params 参数数组
     * @return array|string
     */
    public function getRemoteStorages(array $params)
    {

        $passwordMd5 = md5(base64_decode($params['password']));
        $opName = 'NODE_SR_OP_SCAN_REMOTE_SYSTEM';
        $msg = array(
            'remote_ip' => $params['remoteip'],
            'remote_port' => $params['remoteport'],
            'username' => $params['username'],
            'password' => $passwordMd5,
        );

        $mbResult = $this->service()->getRemoteStorages($msg, $opName);
        if (!$mbResult['result']) {
            $operate = $this->getUnifyOpcodeDes($opName);
            return $this->muOpResult(false, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }

        $sql = "select storage_uuid from bd_storage_resource where storage_type = ?";
        $storages = $this->dbSelect($sql, [xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE']]);
        $storageIds = array_column($storages, 'storage_uuid');

        $data = $mbResult['msg']['storage_list'];
        $records = [];
        // 获取pf描述
        $pfDes = xphp_get_desc('Pf', 'STORAGETYPE');
        $i = 0;
        foreach ($data as $d) {
            // 已添加过的异地存储不再显示
            if (in_array($d['storage_uuid'], $storageIds)) {
                continue;
            }
            $i++;
            $storageconfig = json_decode($d['storage_config'], true);
            $records[] = array(
                'id' => $i,
                'storage_name' => $d['name'],
                'storage_type' => $pfDes[$d['storage_type']] ?? $pfDes[0],
                'storage_size' => v1_calSize($d['size'], true),
                'storage_node' => $storageconfig['remote_node_type'] == 1 ?
                    xphp_get_lang('UI_STORAGE_REMOTE_NODE_MASTER') . '(' . $storageconfig['remote_node_ip'] . ')' :
                    xphp_get_lang('UI_STORAGE_REMOTE_NODE_SUB') . '(' . $storageconfig['remote_node_ip'] . ')',
                'uuid' => $d['storage_uuid'],
            );
        }

        //返回结果到UI
        return [
            'rows' => $records,
            'total' => count($records),
        ];
    }

    /**
     *
     * 获取存储列表
     * @param array $params 请求参数
     * @return array
     */
    public function getStorageRecoveryList(array $params)
    {
        $instantRecoverFlag = $params['instantRecoverFlag'];
        $grainRecoverFlag = $params['grainRecoverFlag'];
        $osinstantModuleFlag = $params['osinstantModuleFlag'];

        $sql = "SELECT 
                    bsr.storage_nickname, bsr.storage_uuid, bsr.storage_type, bsr.node_uuid, bn.host_name, bn.ip 
                FROM 
                    bd_storage_resource bsr 
                LEFT JOIN 
                    mt_user_resource mur 
                ON 
                    bsr.storage_uuid = mur.resource_uuid AND mur.resource_type = ?,
                    bd_node bn 
                WHERE 
                    bsr.node_uuid = bn.node_uuid AND bsr.lan_free_flag = 2 AND bsr.source_type = 1 ";

        $sqlParams = [xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']];

        if ($params['backupDataFlag']) { // 备份数据页面获取
            $sql .= ' AND bsr.use_mode = ? ';
            $sqlParams = array_merge($sqlParams, [xphp_get_config('resource', 'BD_STORAGE_USE_MODE')['BACKUP']]);
        } else {
            // 恢复页面获取时排除异地备份系统存储
            $sql .= ' and bsr.storage_type != ? ';
            $sqlParams[] = xphp_get_config('resource', 'BD_STORAGE_TYPE')['REMOTE'];
        }
        // 如果是瞬时恢复或者细粒度恢复 排除云存储/磁带 也要排除华为CBR存储
        if ($instantRecoverFlag || $grainRecoverFlag) {
            $sql .= ' and bsr.storage_type not in (' . xphp_get_config('resource', 'BD_STORAGE_TYPE')['HUAWEI_CBR']
                . ',' . xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD']
                . ',' . xphp_get_config('resource', 'BD_STORAGE_TYPE')['CLOUD'] . ') ';
        }
        //如果是操作系统瞬时恢复 还需要屏蔽磁带
        if ($osinstantModuleFlag) {
            $sql .= ' and bsr.storage_type not in (' . xphp_get_config('resource', 'BD_STORAGE_TYPE')['TAPE'] . ') ';
        }

        // 查看权限
        if (v1_auth_need_operation()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(
                xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE'],
                'bsr.storage_uuid'
            );
            $sqlNew = " and ({$resourceUuidSql}) ";
            $sql .= $sqlNew;
        }

        $data = $this->dbSelect($sql . ' group by storage_uuid', $sqlParams);

        $storagelist = array();

        $allstoragename = xphp_get_lang('WEB_STORAGE_ALL_STORAGE');
        $flag = false;

        // 如果不包含网络存储 则不显示不包含网络存储
        foreach ($data as $d) {
            //网络存储包含华为CBR S3 磁带 以后需要加上
            if ($d['storage_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['HUAWEI_CBR']) {
                $flag = true;
            }
        }

        if ($flag) {
            $allstoragename .= '(' . xphp_get_lang('WEB_STORAGE_NOT_INCLUDE_NET_STORAGE') . ')';
        }

        // 新增所有存储
        $storagelist[] = array(
            'storagename' => $allstoragename,
            'storageid' => '',
            'storagetypedes' => '',
            'storagetype' => '',
            'node_uuid' => '',
            'text' => $allstoragename,
        );
        $allStorageType = xphp_get_config('storage', 'BD_STORAGE_TYPE');
        $shareStorageTypeList = [  // 共享存储
            $allStorageType['NFS'],
            $allStorageType['CIFS'],
            $allStorageType['CLOUD'],
        ];
        foreach ($data as $d) {
            //获取存储所在节点，共享存储需要调用后台接口获取可用节点uuid
            $nodeuuid = $d['node_uuid'];
            if (inArray($d['storage_type'], $shareStorageTypeList)) {
                //处理共享存储逻辑
                $nodeuuid = (new Service())->getClusterAccessableNode(array('storage_uuid' => $d['storage_uuid']))['node_uuid'];
                if (empty($nodeuuid)) {
                    //节点uuid不存在的话直接获取主节点使用
                    $nodeuuid = (new Node())->getLocalNodeUUID();
                }
            }
            $storagename = html_entity_decode($d['storage_nickname'], ENT_QUOTES, 'UTF-8');
            $storagelist[] = array(
                'storagename' =>  $storagename,
                'storageid' => $d['storage_uuid'],
                'storagetypedes' => $this->getStorageTypeDes($d['storage_type']),
                'storagetype' => $d['storage_type'],
                'node_uuid' => $nodeuuid,
                'text' => $this->getStorageShowName(
                    $storagename,
                    $d['storage_type'],
                    $d['host_name'],
                    $d['ip']
                ),
            );
        }

        return $storagelist;
    }

    /**
     * @param string $storagename 存储别名
     * @param int    $storagetype 存储类型
     * @param string $hostname    主机名
     * @param string $ip          ip
     * @return string
     */
    private function getStorageShowName($storagename, $storagetype, $hostname, $ip)
    {
        $storagedes = $this->getStorageTypeDes($storagetype);
        $storageTypes = xphp_get_config('storage', 'BD_STORAGE_TYPE');
        //如果是本地需要去找节点
        if (in_array($storagetype, [$storageTypes['CLOUD'], $storageTypes['HUAWEI_CBR']])) {
            $name = $storagename . '(' . $storagedes . ')';
        } else {
            $name = $storagename . '(' . $storagedes . '，' . xphp_get_lang('WEB_PLATFORM_DES_NODE')
                . ':' . $hostname . '(' . $ip . ')' . ')';
        }
        return $name;
    }

    /**
     * 手动同步
     * @param array $param 请求参数
     * @return array
     */
    public function syncStorageInfo(array $param): array
    {
        // 查询当前是否有运行中的备份任务
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $taskIng = [
            $taskStatus['RUNNING'],
            $taskStatus['NETWORK_FAULT'],
            $taskStatus['PAUSED'],
            $taskStatus['STARTING'],
            $taskStatus['STOPPING'],
            $taskStatus['TAKEOVER'],
            $taskStatus['TAKEOVER_STARTING'],
            $taskStatus['TAKEOVER_STOPPING'],
            $taskStatus['SUCCESSED'],
        ];
        $taskTypeBack = [
            $taskType['BACKUP'],
            $taskType['DISK_BACKUP'],
            $taskType['VM_FILE_BACKUP'],
            $taskType['VM_REPLICATION'],
            $taskType['SYNC'],
            $taskType['VM_CDP_BACKUP'],
            $taskType['DB_CDP_BACKUP'],
            $taskType['DB_CDP_TAKEOVER'],
            $taskType['FILE_CDP_BACKUP'],
            $taskType['DB_BACKUP'],
            $taskType['VOL_CDP_BACKUP'],
            $taskType['VOL_CDP_TAKEOVER'],
            $taskType['OS_BACKUP'],
            $taskType['NAS_BACKUP'],
            $taskType['CDP_DB_BACKUP'],
            $taskType['VM_HUAWEI_CBR_SYNC'],
            $taskType['KUBE_BACKUP'],
            $taskType['FILE_COPY'],
        ];
        $storageUuid = array_filter($param['storage_uuid'] ?? []);
        $this->checkAuthBySourceUuid($storageUuid[0], xphp_get_config('resource', 'RESOURCE_TYPE')['STORAGE']);
        $storageUuid = implode("','", $storageUuid);
        $sql = "select task_uuid from bd_task
                where task_status in (" . implode(',', $taskIng) . ')
                 and task_type in (' . implode(',', $taskTypeBack) . ')'
            . " and storage_uuid in ('{$storageUuid}')";
        $data = $this->dbSelect($sql);

        if (!empty($data)) {
            return [
                'code' => -1,
                'msg' => xphp_get_lang('UI_STORAGE_SYNC_TASK_TIPS')
            ];
        }
        $uuid = [];
        foreach ($param['storage_uuid'] as $item) {
            $uuid[] = [
                'storage_uuid' => $item
            ];
        }
        // 给后台发消息完成同步操作
        $msg = [
            'storage_uuid_list' => $uuid
        ];
        $opName = 'NODE_SR_OP_TIMEPOINTS_IMPORT_MANUAL_SYNC';
        $sql = "select node_uuid from bd_storage_resource where storage_uuid in ('{$storageUuid}')";
        $node = $this->dbSelect($sql);
        $nodeuuid = $node[0]['node_uuid'] ?? (new Node())->getLocalNodeUUID();
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, json_encode($msg));

        $opcodeDes = (new NodeOpcode())->getOpcodeDes($opName);

        return $this->muOpResult($mbResult['result'], $opcodeDes, '', '', $mbResult['errorCode']);
    }
}
