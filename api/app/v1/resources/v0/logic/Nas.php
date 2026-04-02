<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\NodeOpcode;
use app\v1\system\v0\logic\Settings;
use app\v1\tenant\v0\logic\Index;
use app\v1\resources\v0\logic\Index as ResourceHandler;
use app\v1\system\v0\logic\Index as SystemHandler;
use app\v1\tenant\v0\logic\Index as TenantHandler;

/**
 * note          nas设备管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/5/22 16:45
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Nas extends Base
{
    private $nodeOpcode;

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        $this->nodeOpcode = new NodeOpcode();
    }

    /**
     * 得到nas设备管理列表信息
     * @param array $params 参数数组
     * @return array
     */
    public function getNasInfo(array $params): array
    {
        $search = !empty($params['search']) ? v1_escape_wildcard($params['search']) : '';
        $sql = 'select nsr.id,nsr.nas_nickname,nsr.share_path,nsr.nas_create_time,nsr.nas_uuid,nsr.ip,nsr.user_name,nsr.vendor,
       nsr.password,nsr.nas_status,nsr.mount_params,nsr.nas_version,nsr.port,nsr.nas_type,nsr.authorization_status,nsr.user_uuid, nsr.detail,
       nsr.permission_flag,bu.user_name as bu_username from nas_storage_resource nsr left join bd_user bu on bu.user_uuid = nsr.user_uuid';
        $sqlcount = "select count(nsr.nas_uuid) as total from nas_storage_resource nsr";
       //获取当前用户拥有的nas设备
       $concatSql = ' where ';
       if(v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['NAS'], 'nsr.nas_uuid');
            $sql .= $concatSql . "  ({$resourceUuidSql})";
            $sqlcount .= $concatSql . " ({$resourceUuidSql})";
            $concatSql = ' and ';
        }

        if (!empty($search)) {
            $sql .= $concatSql . "  nsr.ip like '%" . $search . "%' or nsr.share_path like '%" . $search . "%' ";
            $sqlcount .= $concatSql . "  nsr.ip like '%" . $search . "%' or nsr.share_path like '%" . $search . "%' ";
        }

        $count = $this->dbSelect($sqlcount);
        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $total = intval($count[0]['total']);
        //表头排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'ip' => 'nsr.ip',
                'share_path' => 'nsr.share_path',
                'nickname' => 'nsr.nas_nickname',
                'nas_type' => 'nsr.nas_type',
                'create_time' => 'nsr.nas_create_time',
                'status' => 'nsr.nas_status',
                'auth_status' => 'nsr.authorization_status',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', id desc');
            $sql .= " order by " . $sort;
        }

        $sql .= ' limit ? , ? ';

        $sqlParams = array($params['offset'], $params['limit']);
        $data = $this->dbSelect($sql, $sqlParams);

        $records = [];
        $user = xphp_get_user_info();
        foreach ($data as $d) {
            // 拥有者
            $owner = $this->getNasUuidName($d['nas_uuid'], $d['bu_username'] ?? '--');
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
            //租户内判断创建者等不等于当前用户,等于当前用户才能操作
            $opFlag = true;
            if ($user['tenantuuid'] && $d['user_uuid'] != $user['userUuid']) {
                $opFlag = false;
            }
            $detail = json_decode($d['detail'], true);
            $records[] = [
                'nas_uuid' => $d['nas_uuid'],
                'ip' => $d['ip'],
                'share_path' => $d['share_path'],
                'nickname' => htmlspecialchars_decode($d['nas_nickname']),
                'nas_type' => $d['nas_type'] == 6 ? 'NFS' : 'CIFS',
                'nas_type_value' => $d['nas_type'],
                'create_time' => $d['nas_create_time'],
                'status' => $d['nas_status'],
                'auth_status' => $d['authorization_status'] == 1 ?
                    xphp_get_lang('WEB_SYSTEM_LISENCE_AUTHORIZED') :
                    xphp_get_lang('WEB_SYSTEM_LISENCE_UNAUTHORIZED'),
                'username' => $d['user_name'],
                'permission_flag' => $d['permission_flag'] == 1 ?
                    xphp_get_lang('UI_NAS_MANAGE_WRITE_AND_READ') :
                    xphp_get_lang('UI_NAS_MANAGE_ONLY_READ'),
                'permission_flag_value' => $d['permission_flag'],
                'mount_params' => $d['mount_params'],
                'mount_list' => $this->getNasMount($d['nas_uuid']),
                'opcode' => array(1, 2),    //1 挂载  2解挂  3授权
                'creator' => $creator,
                'owner' => $owner,
                'detail' => $this->getNasDetails($d),
                'op_flag' => $opFlag,//当前用户是否可以操作该资源
                'vendor' => $d['vendor'],
                'vendor_des' => $this->getVendorDes($d['vendor'])
            ];
        }
        return ['rows' => $records, 'total' => $total];
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
                $des = xphp_get_lang('UI_EMERGENCY_PLAN_OTHERS');
                break;
            case 1:
                $des = 'HUAWEI OceanStor Dorado';
                break;
            default:
                $des = xphp_get_lang('UI_EMERGENCY_PLAN_OTHERS');
                break;
        }
        return $des;
    }

    /**
     * 组装修改客户端需要显示信息
     * @param unknown $d
     * @return number[]|unknown[]|mixed[]
     */
    private function getNasDetails($d)
    {
        $mount_list = array();
        $mount_list_name = array();
        $sql = 'select nml.node_uuid, nml.nas_state, bn.ip from nas_mount_list nml,bd_node bn where nml.node_uuid = bn.node_uuid and nas_uuid = ?';
        $data = $this->dbSelect($sql, array($d['nas_uuid']));
        foreach ($data as $nodeuuid) {
            $mount_list[] = $nodeuuid['node_uuid'];
            $mount_list_name[] = $nodeuuid['ip'] . '(' . ($nodeuuid['nas_state'] == 1 ? xphp_get_lang('WEB_AGENT_STATUS_ONLINE') : xphp_get_lang('WEB_AGENT_STATUS_OFFLINE')) . ')';
        }
        $info = array(
            'nas_nickname' => $d['nas_nickname'],
            'share_path' => $d['share_path'],
            'nas_uuid' => $d['nas_uuid'],
            'ip' => $d['ip'],
            'user_name' => $d['user_name'],
            'password' => $d['password'],
            'nas_status' => $this->getNasStatusDes(intval($d['nas_status'])),
            'mount_params' => $d['mount_params'],
            'nas_version' => $d['nas_version'],
            'port' => intval($d['port']),
            'nas_type' => intval($d['nas_type']),
            'mount_list' => $mount_list,
            'mount_list_name' => $mount_list_name,
            'permission_flag' => $d['permission_flag'] == 1 ? xphp_get_lang('UI_NAS_MANAGE_WRITE_AND_READ') : xphp_get_lang('UI_NAS_MANAGE_ONLY_READ'),
            'detail' => json_decode($d['detail'], true),
        );
        return $info;
    }

    /**
     * 检测系统授权状态
     * @return int 授权状态
     */
    public function checkSystemAuth()
    {
        $authflag = 2;
        $status = (new Settings())->getSystemAuthorizationStatus();
        if ($status == xphp_get_config('auth', 'LISENCE_INFO')['authflag']['authorized']) {
            $authflag = 1;
        }
        return $authflag;
    }

    /**
     * nas设备状态
     * @param int $onlineFlag
     * @param int $deployFlag
     */
    public function getNasStatusDes($onlineFlag)
    {
        // 1在线 2异常 3离线
        if (xphp_get_config('nas', 'NAS_MOUNT_STATUS')['NS_NAS_MOUNT_UNKWON'] == $onlineFlag) {
            $des = xphp_get_lang('WEB_PLATFORM_PUBLIC_UNKNOWN');
        } else if (xphp_get_config('nas', 'NAS_MOUNT_STATUS')['NS_NAS_MOUNT_NORMAL'] == $onlineFlag) {
            $des = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
        } else if (xphp_get_config('nas', 'NAS_MOUNT_STATUS')['NS_NAS_MOUNT_ABNORMAL'] == $onlineFlag) {
            $des = xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL');
        } else if (xphp_get_config('nas', 'NAS_MOUNT_STATUS')['NS_NAS_MOUNT_ERROR'] == $onlineFlag) {
            $des = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
        }

        return $des;
    }

    /**
     * 添加nas设备
     * @param array $params 参数
     * @return string
     */
    public function addNasDevice(array $params)
    {
        $settings = (new Index())->pGetTenantSettings(xphp_get_user_info()['tenantuuid']);
        // 租户内部 添加ip已经授权的nas时，授权不足不允许添加
        if (!empty($_SESSION['tenantuuid']) && $settings['auth_way'] == 2) {
            $systemHandler = new SystemHandler();
            //获取当前用户拥有的nas设备
            if(v1_auth_need_check_look()) {
                //三权模式下的操作员只能查看分配存储列表
                //不是超级管理员也不是全局观察者, 也只能看到分配的资源
                $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['NAS'], 'nas_uuid');
                $ip = $params['info']['ip'];
                $ipData = $this->dbSelect("select distinct ip from nas_storage_resource where nas_status = 1 and ip = '" . $ip . "'
                and ({$resourceUuidSql})");
                if (empty($ipData)) {//是ip已经授权的nas
                    $nasAuthInfo = $systemHandler->getOneModuleLisenceInfo('nas');
                    if ($nasAuthInfo['valid'] < 1) {
                        exit($this->muOpResult(false, xphp_get_lang('WEB_TENANT_AVAILABLE_CHECK'), xphp_get_lang('WEB_TENANT_AVAILABLE_CHECK_ERROR'), "warning"));
                    }
                }
            }
        }

        $msg = array();
        // $allResult = array();
        $nodeHandler = new Node();
        $nodeArr = $params['node_mount_list'];
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        // 定义操作名
        if ($params['info']['nas_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['CIFS']) {
            $opcodeName = 'NODE_OP_ADD_CIFS_NAS';
        } else if ($params['info']['nas_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['NFS']) {
            $opcodeName = 'NODE_OP_ADD_NFS_NAS';
        }

        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);

        //组合消息
        $msg = $params['info'];
        //分别发往各个节点
        $errorConf = xphp_get_config('error');
        $resultArr = array();
        $des = '';
        foreach ($nodeArr as $nodeuuid) {
            $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
            if ($mbResult['result']) {
                $des .= $this->getNodeIp($nodeuuid) . xphp_get_lang('WEB_NAS_ADD_NAS_DEVICE_SUCCESS') . '<br>';
            } else {
                $des .= xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ": #" . $mbResult['errorCode'] . ", " . xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ":" . $this->getNodeIp($nodeuuid) . $errorConf['errorCodeDes'][$errorConf['errorCode'][$mbResult['errorCode']]] . '<br>';
            }
            $resultArr[] = $mbResult['result'];
        }

        if (!in_array(false, $resultArr)) {
            $result = true;
            $info = array(
                "re" => $result,
                "msg" => xphp_get_lang('WEB_NAS_ADD_NAS_DEVICE_SUCCESS'),
            );
        } else {
            $result = false;
            $info = array(
                "re" => $result,
                "msg" => $des,
            );
        }

        return $info;
    }

    /**
     *得到所有节点
     * @param unknown $params
     * @return string'
     */
    private function getNodeIp($params)
    {
        $sql = "select ip from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($params));
        return $data[0]['ip'];
    }

    /**
     *修改NAS设备信息
     * @param array $params 参数
     * @return string|bool
     */
    public function editNasDevice(array $params)
    {
        $localnodeuuid = (new Node())->getLocalNodeUUID();//主节点
        $opdes = xphp_get_lang('WEB_NODE_OP_MODIFY_NFS_NAS');
        //操作权限判断
        $this->checkAuthBySourceUuid($params['info']['nas_uuid'],xphp_get_config('resource','RESOURCE_TYPE')['NAS']);
        //定义操作名
        if ($params['nas_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['CIFS']) {
            $opcodeName = 'NODE_OP_MODIFY_CIFS_NAS';
        } else if ($params['nas_type'] == xphp_get_config('resource', 'BD_STORAGE_TYPE')['NFS']) {
            $opcodeName = 'NODE_OP_MODIFY_NFS_NAS';
        }

        $msg = $params['info'];
        $flag = $this->checkEditNasParams($params)['flag'];

        if ($flag) {
            //如果修改了除别名之外的信息  检查是否有运行的任务存在
            $this->checkNasTaskExist($opdes, $params['info']['nas_uuid'], $params['mount_list']);
            //组合消息
            $mountResult = array(); //用于保存挂载结果
            foreach ($params['mount_list'] as $nodeuuid) {
                //先挂载再修改--
                $mbResult = $this->mbNodeMsg('NODE_OP_MOUNT_NAS_AGAIN', $nodeuuid, json_encode($msg));
                $mountResult[] = $mbResult["result"];
                $errorCode = $mbResult["errorCode"];
            }

            // $mbResult = $this->mbNodeMsg('NODE_OP_MOUNT_NAS_AGAIN', $localnodeuuid, json_encode($msg));
            if (!in_array(false, $mountResult)) { //全部挂载成功,直接把修改消息发到主节点
                $mbResult = $this->mbNodeMsg($opcodeName, $localnodeuuid, json_encode($msg));
            } else if (in_array(true, $mountResult)) { //部分挂载成功,返回挂载失败的节点,把修改消息发到主节点
                //获取挂载失败的节点
                $des = $this->getFailNode($mountResult, $params['mount_list']);
                $mbResult = $this->mbNodeMsg($opcodeName, $localnodeuuid, json_encode($msg));
                return $this->muOpResult(false, xphp_get_lang('UI_NAS_MOUNT_SUCCESS_PART'), $des, 'warning');
            } else { //全部失败，不修改直接返回
                return $this->muOpResult(false, xphp_get_lang('UI_NAS_MODIFY_FAIL'), '', 'warning', $errorCode);
            }
        } else {
            // 只修改了别名，直接修改，不需要挂载
            $mbResult = $this->mbNodeMsg($opcodeName, $localnodeuuid, json_encode($msg));
        }

        $result = $mbResult["result"];
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     *获取挂载失败节点ip
     * @param array $mountResult 挂载成功或失败
     * @param array $nodelist 挂载节点列表
     * @return string
     */
    private function getFailNode($mountResult, $nodelist)
    {
        $des = "";
        foreach ($mountResult as $key => $m) {
            if (!$m) { //挂载失败
                $sql = "SELECT ip from bd_node where node_uuid = ?";
                $data = $this->dbSelect($sql, array($nodelist[$key]));
                $des .= $data[0]['ip'] . " ";
            }
        }
        $info = xphp_get_lang('WEB_PLATFORM_DES_NODE') . $des . xphp_get_lang('WEB_ERROR_VM_ICS_KVM_RESOURCE_220109_ERROR');
        return $info;
    }

    /**
     *检测是否修改了除了别名之外的信息
     * @param array $params 参数
     * @return array|bool
     */
    public function checkEditNasParams(array $params)
    {
        $nasuuid = $params['info']['nas_uuid'];
        $nastype = $params['nas_type'];
        $permission_flag = $params['info']['permission_flag'];
        $port = $params['info']['port'];
        $mount_params = $params['info']['mount_params'];
        $username = $params['info']['username'];
        $passwd = $params['info']['passwd'];
        $info = array(
            "flag" => false,
        );

        if ($nastype == xphp_get_config('resource', 'BD_STORAGE_TYPE')['NFS']) {
            $sql = "select permission_flag, port, mount_params from nas_storage_resource where 	nas_uuid = ?";
            $data = $this->dbSelect($sql, array($nasuuid));
            if ($data[0]['permission_flag'] != $permission_flag || $data[0]['port'] != $port || $data[0]['mount_params'] != $mount_params) {
                $info = array(
                    "flag" => true,
                );
            }
        } else if ($nastype == xphp_get_config('resource', 'BD_STORAGE_TYPE')['CIFS']) {

            $sql = "select user_name, permission_flag, port, mount_params, nas_version, detail from nas_storage_resource where nas_uuid = ?";
            $data = $this->dbSelect($sql, array($nasuuid)); //密码不为空就是修改过了
            if ($data[0]['user_name'] != $username || $passwd != "" || $data[0]['port'] != $port || $data[0]['mount_params'] != $mount_params || $data[0]['permission_flag'] != $permission_flag || $data[0]['nas_version'] != $version) {
                $info = array(
                    "flag" => true,
                );
            }
        }

        return $info;
    }

    /**
     *删除nas设备
     * @param string $nasUuid nas设备uuid
     * @return string
     */
    public function delNasDevice($params)
    {
         //操作权限判断
         $this->checkAuthBySourceUuid(implode(',', $params['nas_uuid']),xphp_get_config('resource','RESOURCE_TYPE')['NAS']);
        $localnodeuuid = (new Node())->getLocalNodeUUID();
        //定义操作名
        $opcodeName = 'NODE_OP_DELETE_NAS';
        //组合消息
        $msg = array(
            "nas_uuid" => [$params['nas_uuid']]
        );

        $nasuuiList = $params['nas_uuid'];
        // 检测该设备是否有任务存在
        foreach ($nasuuiList as $nasuuid) {
            $sql = "SELECT 
                        nst.ip, nst.nas_nickname, nst.share_path, nt.nas_uuid, bt.task_name 
                    FROM 
                        nas_storage_resource nst, nas_task nt,bd_task bt 
                    WHERE 
                        nst.nas_uuid=nt.nas_uuid AND bt.task_uuid=nt.task_uuid AND nt.nas_uuid = ?";
            $data = $this->dbSelect($sql, array($nasuuid));
            if (!empty($data)) {
                $namedes = $data[0]['ip'] . '(' . $data[0]['nas_nickname'] . ')';
                if ($data[0]['nas_nickname'] == $data[0]['ip']) {
                    $namedes = $data[0]['ip'] . '(' . $data[0]['share_path'] . ')';
                }

                exit($this->muOpResult(false, xphp_get_lang('UI_NAS_MANAGE_DELETE'), xphp_get_lang('UI_NAS_MANAGE_DELETE_TASK')
                    . ', ' . xphp_get_lang('UI_NAS_DEVICE_NAME') . ':' . $namedes
                    . ', ' . xphp_get_lang('UI_PUBLIC_TASK_RNAME') . ':' . $data[0]['task_name'], "warning"));
            }
        }

        // 先解挂该nas设备上  除了主节点之外的节点
        foreach ($nasuuiList as $nasuuid) {
            $sql = "SELECT node_uuid FROM nas_mount_list WHERE nas_uuid = ?";
            $data = $this->dbSelect($sql, array($nasuuid));
            foreach ($data as $d) {
                if ($d['node_uuid'] != $localnodeuuid) {
                    $nasMsg = array(
                        "nas_uuid" => array($nasuuid)
                    );
                    $mbResult = $this->mbNodeMsg('NODE_OP_UMOUNT_NAS', $d['node_uuid'], json_encode($nasMsg));
                }
            }
        }

        $mbResult = $this->mbNodeMsg($opcodeName, $localnodeuuid, json_encode($msg));
        $result = $mbResult['result'];
        $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     *nas授权
     * @param array $params 参数
     * @return string
     */
    public function nasLisence(array $params)
    {
        $systemHandler = new SystemHandler();
        $licenseInfo = $systemHandler->getSystemLicenseType();

        if (($licenseInfo['licensetype'] != xphp_get_config('auth', 'LISENCE_INFO')['type']['storage']) && $params['info']['nas_auth_flag'] == 1) {
            //租户内检查可用数量是否超过授权个数
            if (!empty($_SESSION['tenantuuid'])) {
                $tenantHandler = new TenantHandler();
                $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
                if ($settings['auth_way'] == 2) {
                    $currentCount = count($params['info']['nas_ip_list']);
                    $nasAuthInfo = $systemHandler->getOneModuleLisenceInfo('nas');
                    if ($nasAuthInfo['valid'] < $currentCount) {
                        exit($this->muOpResult(false, xphp_get_lang('WEB_TENANT_AVAILABLE_CHECK'), xphp_get_lang('WEB_TENANT_AVAILABLE_CHECK_ERROR'), "warning"));
                    }
                }
            }
            $nasinfo = $this->getNasLisenceInfo();
            if ($nasinfo['nas']['valid'] < count($params['info']['nas_ip_list'])) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_PT_LICENSE_OP_MODIFY_NAS_LICENSE'), xphp_get_lang('UI_NAS_MANAGE_IP_NOT_ENOUGH')));
            }
        }

        $opName = 'PT_LICENSE_OP_MODIFY_NAS_LICENSE';
        $msg = $params['info'];
        $mbResult = $this->mbPFMsg($opName, json_encode($msg));
        if ($params['info']['nas_auth_flag'] == 1) {
            //授权
            return $this->muOpResult($mbResult['result'], xphp_get_lang('UI_VCENTER_AUTH_ADD'));
        } else if ($params['info']['nas_auth_flag'] == 2) {
            //取消授权
            return $this->muOpResult($mbResult['result'], xphp_get_lang('UI_VCENTER_AUTH_DELETE'));
        }
    }

    /**
     * 检测是否修改其它信息
     * @param array $params 请求参数
     * @param array $nas    nas信息
     * @return boolean
     */
    private function checkOther(array $params, array $nas): bool
    {
        $permissionflag = $params['permission_flag'];

        $mountparams = $params['mount_params'] ?? '';
        $username = $params['username'] ?? '';
        $passwd = $params['passwd'] ?? '';

        $bdStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        if ($nas['nas_type'] == $bdStorageType['NFS']) {
            if (
                $nas['permission_flag'] != $permissionflag ||
                $nas['port'] != $params['port'] ||
                $nas['mount_params'] != $mountparams
            ) {
                $flag = true;
            }
        } elseif ($nas['nas_type'] == $bdStorageType['CIFS']) {
            //密码不为空就是修改过了
            if (
                $nas['user_name'] != $username ||
                $passwd != '' ||
                $nas['mount_params'] != $mountparams ||
                $nas['permission_flag'] != $permissionflag
            ) {
                $flag = true;
            }
        }
        return $flag ?? false;
    }

    /**
     * 获取nas列表挂载信息
     * @param string $nasUuid 存储uuid
     * @return array
     */
    private function getNasMount(string $nasUuid): array
    {
        $mountlist = [];
        $sql = 'select nml.node_uuid, bn.ip
                    from nas_mount_list nml,bd_node bn
                where nml.node_uuid = bn.node_uuid and nas_uuid = ?';
        $data = $this->dbSelect($sql, [$nasUuid]);
        foreach ($data as $nodeuuid) {
            $mountlist[] = [
                'uuid' => $nodeuuid['node_uuid'],
                'name' => $nodeuuid['ip'],
            ];
        }
        return $mountlist;
    }

    /**
     * 检查删除的设备在对应节点是否有任务存在
     * @param string $opdes   操作描述
     * @param string $nasuuid nasuuid
     * @return string|void
     */
    private function checkNasTaskExist($opdes, $nasuuid, $nodeList)
    {
        // 判断已挂载节点是否是空
        if (empty($nodeList)) {
            exit($this->muOpResult(false, $opdes, xphp_get_lang('UI_NAS_MANAGE_UMOUNT_NODE_TIPS'), 'warning'));
        }

        $sql = "SELECT bt.id, bt.node_uuid FROM nas_task nt, bd_task bt WHERE nt.task_uuid = bt.task_uuid AND nt.nas_uuid = ? AND bt.task_status = ?;";
        $statusArr = xphp_get_config('task', 'TASKSTATUS');
        foreach ($nodeList as $item) {
            $data = $this->dbSelect($sql, array($nasuuid, $statusArr['RUNNING']));
            if (!empty($data) && $item['uuid'] == $data[0]['node_uuid']) {
                return $this->muOpResult(false, $opdes, xphp_get_lang('UI_NAS_MANAGE_UMOUNT_EXIST_RUNNING'), 'warning');
            }
        }
    }

    /**
     * 得到nas授权信息
     * @return array
     */
    private function getNasLisenceInfo(): array
    {
        $systemHandler = new \app\v1\system\v0\logic\Index();
        $nasInfo = $systemHandler->getOneModuleLisenceInfo('nas');
        $licenseInfo = $systemHandler->getSystemLicenseType();
        return [
            'nas' => $nasInfo,
            'licensetype' => $licenseInfo['licensetype']
        ];
    }

    /**
     * 根据nas uuid 获取当前的使用者是谁 只查询分配的资源/组
     * @param string $nasUuid nas设备uuid
     * @param string $userName  拥有者名称
     * @return string
     */
    public function getNasUuidName(string $nasUuid, string $userName = ''): string
    {
        $sqlParams = [$nasUuid];
        // 先 再分配的资源和资源组去查询
        $sql2 = "select user_uuid from mt_user_resource where resource_type = 57 and resource_uuid = ?";
        $array = $this->dbSelect($sql2, $sqlParams);
        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 57";
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
     *挂载、解挂
     * @param unknown $params
     * @return string
     */
    public function opreateNas($params)
    {
        //操作权限判断
        $this->checkAuthBySourceUuid($params['info']['nas_uuid'],xphp_get_config('resource','RESOURCE_TYPE')['NAS']);
        if ($params['flag'] == 1) { // 挂载
            $opcodeName = 'NODE_OP_MOUNT_NAS';
            $msg = $params['info'];
            $node_mount_list = $params['node_mount_list'];

            foreach ($node_mount_list as $nodeuuid) {
                $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
            }

            $result = $mbResult['result'];
            $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
            $msg = $mbResult['msg'];
            //返回结果到UI
            if ($result) {
                return $this->muOpResult($result, $operate, $msg);
            } else {
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            }
        } else { // 解挂
            $opcodeName = 'NODE_OP_UMOUNT_NAS';
            $node_mount_list = $params['node_mount_list'];
            $nasuuid = $params['info']['nas_uuid'][0];

            //检查是否有运行的任务存在
            $this->checkNasTaskExist(xphp_get_lang('UI_NAS_MANAGE_UMOUNT'), $nasuuid, $node_mount_list);

            $msg = $params['info'];
            foreach ($node_mount_list as $nodeuuid) {
                $mbResult = $this->mbNodeMsg($opcodeName, $nodeuuid, json_encode($msg));
            }

            $operate = $this->nodeOpcode->getOpcodeDes($opcodeName);
            $msg = $mbResult['msg'];
            $result = $mbResult['result'];

            //返回结果到UI
            if ($result) {
                return $this->muOpResult($result, $operate, $msg);
            } else {
                return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
            }
        }
    }
}