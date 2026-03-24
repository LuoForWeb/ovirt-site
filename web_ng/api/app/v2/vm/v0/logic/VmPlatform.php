<?php

namespace app\v2\vm\v0\logic;

use app\v2\cloud\v0\logic\CloudPlatform;
use app\v2\common\logic\Base;
use app\v2\opcode\PfOpcode;
use app\v2\opcode\VmOpcode;
use app\v2\system\v0\logic\Index as SystemHandler;
use app\v2\resources\v0\logic\Index as ResourcesHandler;
use app\v2\resources\v0\logic\Node;
use app\v2\tenant\v0\logic\Tenant;
use xphp\OpcodeHandler;

/**
 * Class VmPlatform
 * @package app\v2\vm\v0\logic
 */
class VmPlatform extends Base
{
    /**
     * 添加虚拟化平台
     * @param array $params 参数
     * @return string
     */
    public function addVmPlatform(array $params): string
    {
        $hypervisorType = $params['hypervisor_type'];
        $ip = $params['ip'];
        $username = $params['username'];
        $password = $params['password'];
        $rName = $params['rname'];
        $detail = $params['detail'];
        $subModule = $this->hypervisorTypeToSubModule($hypervisorType);
        $authKey = $this->getUserAuthKeyBySubModule($subModule);
        $this->checkAuthByUserUuid(xphp_get_user_info()['userUuid'], $authKey);
        if ($detail['backup_time']) {
            $time = strtotime($detail['backup_time']);
            $detail['backup_time'] = date('H:i:s', $time);
        }
        //针对深信服6.8以后国际化版本处理增加系统语言参数
        if ($hypervisorType == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']) {
            if (is_string($detail)) {
                $detail = array();
            }
            $detail['language'] = xphp_get_config('app')['lang'];
        }
        $detailJson = '';
        if (!empty($detail)) {
            if (in_array($hypervisorType, xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'])) {
                $detail['mac_address'] = '';
                if ('cloud' == $detail['backup_server_location']) {
                    // 云上备份系统需获取备份系统网卡mac地址传给后台
                    $nodeUuid = Node::instance()->getLocalNodeUUID();
                    $macSql = "select mac from bd_node_network where node_uuid = ? order by network_order limit 1";
                    $detail['mac_address'] = $this->dbSelect($macSql, [$nodeUuid])[0]['mac'];
                }
                $detail['vpc_id'] = '';
            }
            $detailJson = json_encode($detail);
        }
        //检查参数
        if (
            xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisorType
            || xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisorType
            || xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_KSPHERE'] == $hypervisorType
        ) {
            $this->paramsCheck($hypervisorType, $ip);
            if (!($username && $password) && !($detail['access_key_id'] && $detail['access_key_secret'])) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_OPHANDLER_PARAMS_CHECK'), xphp_get_lang('UI_VCENTER_ICS_ADD_TIPS'), 'warning'));
            }
        } else {
            $this->paramsCheck($hypervisorType, $username, $password);
        }
        //ICS AccessKey
        if (!$username || !$password) {
            $username = $detail['access_key_id'];
            $password = $detail['access_key_secret'];
        }
        //ipv6处理
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ip = '[' . $ip . ']';
        }

        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_ADD';
        //组合消息
        $msg = array(
            'hypervisor_type' => $hypervisorType,
            'ip' => !empty($ip) ? $ip : urlencode($username), // ip不能为空且唯一，若没有则传用户名
            'username' => str_replace('\\\\', '\\', $username),
            'password' => $password,
            'nickname' => $rName,
            'display_mode' => xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['ALL'],
            'detail' => $detailJson
        );
        $paramsUsername = $msg['username'];
        $jsonMsg = json_encode($msg);
        $nodeUuid = Node::instance()->getMasterNodeUuid();

        $mbResult = $this->service()->unifyVmPlatformService($nodeUuid, $hypervisorType, $opcodeName, $jsonMsg);
        $result = $mbResult['result'];
        $operate = VmOpcode::instance()->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        // 添加公有云平台的操作
        if (in_array($hypervisorType, xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'])) {
            $operate = xphp_get_lang('UI_CLOUD_PLATFORM_ADD_VC');
        }
        if ($result) {
            if (
                !in_array($hypervisorType, xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'])
                && xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_VMWARE'] != $hypervisorType
            ) {
                //调用自动刷新虚拟化平台
                $this->refreshVmPlatformDefault($hypervisorType, $ip);
            }
            if (xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD'] == $hypervisorType) {
                // 华为云同步代理镜像
                $sql = "select vcenter_uuid from vm_vcenter where username = ?";
                $platformUuid = parent::dbSelect($sql, [$paramsUsername])[0]['vcenter_uuid'];
                CloudPlatform::instance()->syncProxyImage($platformUuid);
            }
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 修改虚拟化平台
     * @param array $params 参数
     * @return string
     */
    public function modifyVmPlatform(array $params): string
    {
        $uuid = $params['platform_uuid'];
        $username = $params['username'];
        $password = $params['password'];
        $rName = $params['rname'];
        $detail = $params['extra'];
        if ($detail['backup_time']) {
            $time = strtotime($detail['backup_time']);
            $detail['backup_time'] = date('H:i:s', $time);
        }

        $sql = "select vcenter_ip, password, hypervisor_type,user_uuid from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $hypervisorType = $data[0]['hypervisor_type'];
        $subModule = $this->hypervisorTypeToSubModule($hypervisorType);
        $authKey = $this->getUserAuthKeyBySubModule($subModule);
        $this->checkAuthByUserUuid(xphp_get_user_info()['userUuid'], $authKey);
        //检查参数
        if (
            xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_INCLOUD_SPHERE_KVM'] == $hypervisorType
            || xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_INSPUR_VVDK'] == $hypervisorType
        ) {
            $this->paramsCheck($uuid, $rName);
            if (!($username && $password) && !($detail['access_key_id'] && $detail['access_key_secret'])) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_OPHANDLER_PARAMS_CHECK'), xphp_get_lang('UI_VCENTER_ICS_ADD_TIPS'), 'warning'));
            }
        } else {
            $this->paramsCheck($uuid, $username, $password, $rName);
        }

        $userInfo = xphp_get_user_info();
        $publicCloudFlag = in_array($hypervisorType, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']);
        if (empty($userInfo['tenantuuid']) && $userInfo['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
        } elseif (xphp_three_powers() && $this->isAdmin()) {
            // 三权模式下 系统管理员可以查看所有
        } else {
            // 关联管理用户判断 存储资源 - 操作
            $authKey = $publicCloudFlag ? 'cloud_platform_operate' : 'vcenter_manager_operate';
            $userArr = array_unique(array_column($data, 'user_uuid'));
            if (!xphp_check_operate($authKey, $userArr)) {
                // 没有操作权限
                exit(
                    $this->muOpResult(
                        false,
                        xphp_get_lang('UI_ROLE_PERMISSION'),
                        xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'),
                        'warning'
                    )
                );
            }
        }

        $ip = $data[0]['vcenter_ip'];
        //检查用户是否修改了密码,如果修改了用新的密码,如果没有修改用原来的密码(数据库里面的)
        $oldPass = base64_encode(md5($data[0]['password']));
        if ($oldPass == $password) {
            //没有修改密码
            $password = base64_encode(v2_pt_pass_decrypt($data[0]['password']));
        }
        if ($detail['access_key_id'] && $detail['access_key_secret'] && !$username && !$password) {
            $username = $detail['access_key_id'];
            $password = $detail['access_key_secret'];
        }

        //针对深信服6.8以后国际化版本处理增加系统语言参数
        if ($hypervisorType == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_SANGFOR_KVM']) {
            if (is_string($detail)) {
                $detail = array();
            }
            $detail['language'] = xphp_get_config('app')['lang'];
        }
        $detailJson = '';
        if (!empty($detail)) {
            if (in_array($hypervisorType, xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'])) {
                $detail['mac_address'] = '';
                if ('cloud' == $detail['backup_server_location']) {
                    // 云上备份系统需获取备份系统网卡mac地址传给后台
                    $nodeUuid = Node::instance()->getLocalNodeUUID();
                    $macSql = "select mac from bd_node_network where node_uuid = ? order by network_order limit 1";
                    $detail['mac_address'] = $this->dbSelect($macSql, [$nodeUuid])[0]['mac'];
                }
                $detail['vpc_id'] = '';
            }
            $detailJson = json_encode($detail);
        }

        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_MODIFY';
        //组合消息
        $msg = array(
            'vcenter_uuid' => $uuid,
            'hypervisor_type' => $hypervisorType,
            'ip' => $ip,
            'username' => str_replace('\\\\', '\\', $username),
            'password' => $password,
            'nickname' => $rName,
            'display_mode' => xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['ALL'],
            'detail' => $detailJson
        );
        $jsonMsg = json_encode($msg);
        return $this->unifyMsg($hypervisorType, $opcodeName, $jsonMsg);
    }

    /**
     * 删除虚拟化平台
     * @param array $platformsUuids 平台UUID数组
     * @return string
     */
    public function deleteVmPlatforms(array $platformsUuids): string
    {
        $this->paramsCheck($platformsUuids);
        $this->deleteVmPlatformsCheck($platformsUuids);
        $submoduleType = $this->getVmPlatformSubModule($platformsUuids[0]);
        $authKey = $this->getUserAuthKeyBySubModule($submoduleType);
        $this->checkAuthByUserUuid(xphp_get_user_info()['userUuid'], $authKey);
        // 华为云先删除共享镜像，等待数秒后删除平台
        if (xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD']) {
            CloudPlatform::instance()->deleteSharedImage($platformsUuids[0]);
            sleep(7);
        }
        $opName = 'VM_VCENTER_OP_DELETE';
        $msg = array('vcenter_uuid_list' => $platformsUuids);
        $result = $this->unifyMsg($submoduleType, $opName, json_encode($msg));
        if (json_decode($result, true)['success']) {
            //取消备份节点关联关系
            $sql = "delete from mt_platform_node where platform_uuid in (?) and type = ? ";
            $this->dbExec($sql, array("'" . implode("','", $platformsUuids) . "'", 1));
        }
        return $result;
    }

    /**
     * 获取虚拟化平台列表
     * @param array $params 参数
     * @return array
     */
    public function getVmPlatformsList(array $params): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $sortArr = array(
            'ip' => 'vv.vcenter_ip',
            'nickname' => 'vv.nickname',
            'hypervisor_des' => 'vv.hypervisor_type',
            'version' => 'vv.version',
            'username' => 'vv.username',
            'user_name' => 'bu.user_name',
            'refresh_time' => 'vv.refresh_time',
        );

        $sql = "select distinct vv.vcenter_id, vv.vcenter_uuid, vv.vcenter_ip, vv.nickname, vv.hypervisor_type, 
            vv.username, vv.detail, vv.register_time, 
            vv.refresh_time, vv.user_uuid, vv.version,bu.user_name,b_tt.tenant_name, 
            count(vh.host_id) as count_host, count(if(vh.authorization_flag = 1, 1, null)) as auth_host, count(if(vh.authorization_flag = 2, 1, null)) as unauth_host,
            count(vblr.record_id) as license_used 
            from vm_vcenter vv left join mt_user_resource mur on vv.vcenter_uuid = mur.vcenter_uuid
            left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid
            left join bd_tenant b_tt on mut.tenant_uuid = b_tt.tenant_uuid
            left join bd_user bu on vv.user_uuid = bu.user_uuid
            left join vm_host vh on vv.vcenter_uuid = vh.vcenter_uuid 
            left join vm_backup_license_records vblr on vv.vcenter_uuid = vblr.vcenter_uuid 
                 where vv.hypervisor_type != ? ";
        $sqlCount = "select count(distinct vv.vcenter_id) as total 
            from vm_vcenter vv left join mt_user_resource mur on vv.vcenter_uuid = mur.vcenter_uuid
            left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid
            left join bd_tenant b_tt on mut.tenant_uuid = b_tt.tenant_uuid
            left join bd_user bu on vv.user_uuid = bu.user_uuid
                where vv.hypervisor_type != ? ";

        //查询参数
        $sqlParams = array(xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_EMD']);
        $sqlCountParams = array(xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_EMD']);

        $accurateFlag = $params['accurate_flag'] ?? 0;

        //云平台的
        $privateCloudType = xphp_get_config('vm')['VMHYPERVISORGROUP']['privatecloud'];
        $publicCloudType = xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'];
        $privateCloudTypeStr = implode(',', $privateCloudType);
        $publicCloudTypeStr = implode(',', $publicCloudType);
        if ($params['cloud_flag']) {
            if ('private' == $params['cloud_type']) {
                $sql .= "and vv.hypervisor_type in({$privateCloudTypeStr}) ";
                $sqlCount .= "and vv.hypervisor_type in({$privateCloudTypeStr}) ";
                $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
            } else {
                $sql .= "and vv.hypervisor_type in({$publicCloudTypeStr}) ";
                $sqlCount .= "and vv.hypervisor_type in({$publicCloudTypeStr}) ";
                $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
            }
        } else {
            $sql .= "and vv.hypervisor_type not in({$privateCloudTypeStr}) 
                and vv.hypervisor_type not in({$publicCloudTypeStr})";
            $sqlCount .= "and vv.hypervisor_type not in({$privateCloudTypeStr}) 
                and vv.hypervisor_type not in({$publicCloudTypeStr})";
            $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
        }

        $userInfo = xphp_get_user_info();
        if (empty($userInfo['tenantuuid']) && $userInfo['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
            $sql .= ' and mut.tenant_uuid is null ';
            $sqlCount .= ' and mut.tenant_uuid is null ';
        }
        // 获取非分配资源的权限
        if (v2_auth_need_check_look()) {
            $resourceUuidSql = v2_auth_get_users($this->getUserAuthKeyBySubModule($subModule));
            $sql .= " and vv.user_uuid in $resourceUuidSql";
            $sqlCount .= " and vv.user_uuid in $resourceUuidSql";
        }

        //租户内判断创建者等不等于当前用户,等于当前用户才显示
        if ($userInfo['tenantuuid']) {
            $sql .= ' and vv.user_uuid = ? ';
            $sqlCount .= ' and vv.user_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($userInfo['userUuid']));
            $sqlCountParams = array_merge($sqlCountParams, array($userInfo['userUuid']));
        }

        //精确搜索
        if (!$accurateFlag) {
            $searchValue = $params['search_nickname'];
            $searchValue = v2_escape_wildcard($searchValue);
            if ($this->checkEmpty($searchValue)) {
                $sql .= ' and vv.nickname like ? ';
                $sqlCount .= ' and vv.nickname like ? ';
                $sqlParams = array_merge($sqlParams, array('%' . $searchValue . '%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%' . $searchValue . '%'));
            }
        } else {
            $userName = $params['username'];
            $hypervisor = intval($params['hypervisor_type']);
            $vcenterIp = $params['platform_ip'];
            $nickName = $params['nickname'];


            //判断输入的用户名是否在当前用户的管理范围
            if ($this->checkEmpty($userName)) {
                $sql .= 'and bu.user_name = ? ';
                $sqlCount .= 'and bu.user_name = ? ';
                $sqlParams = array_merge($sqlParams, array($userName));
                $sqlCountParams = array_merge($sqlCountParams, array($userName));
            }

            //虚拟化类型
            if (!empty($hypervisor)) {
                $sql .= ' and vv.hypervisor_type = ? ';
                $sqlCount .= ' and vv.hypervisor_type = ? ';
                $sqlParams = array_merge($sqlParams, array($hypervisor));
                $sqlCountParams = array_merge($sqlCountParams, array($hypervisor));
            }

            //别名
            if ($this->checkEmpty($nickName)) {
                $sql .= 'and vv.nickname like ? ';
                $sqlCount .= 'and vv.nickname like ? ';
                $sqlParams = array_merge($sqlParams, array('%' . $nickName . '%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%' . $nickName . '%'));
            }
            //虚拟化中心IP
            if ($this->checkEmpty($vcenterIp)) {
                $sql .= 'and vv.vcenter_ip like ? ';
                $sqlCount .= 'and vv.vcenter_ip like ? ';
                $sqlParams = array_merge($sqlParams, array('%' . $vcenterIp . '%'));
                $sqlCountParams = array_merge($sqlCountParams, array('%' . $vcenterIp . '%'));
            }
        }

        if ($sortArr[$sortColumn]) {
            $sql .= " group by vv.vcenter_uuid order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        } else {
            $sql .= ' limit ? , ? ';
        }
        $sqlParams = array_merge($sqlParams, array($start, $length));

        $datas = parent::dbSelect($sql, $sqlParams);
        $count = parent::dbSelect($sqlCount, $sqlCountParams);

        $hostPermission = $this->getLicenseHostPermission();
        $records = array();
        $records['rows'] = array();
        $id = $start + 1;
        $sql = "select root_type, private_cloud_instance_max_num, public_cloud_instance_max_num from bd_license";
        $license = $this->dbSelect($sql)[0];
        $vmDes = require APP_PATH . 'v2/description/Vm.php';
        foreach ($datas as $data) {
            $tenantName = '';
            if (!empty($data['tenant_name']) && $userInfo['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
                $tenantName = '(' . $data['tenant_name'] . ')';
            }
            if (in_array($data['hypervisor_type'], $privateCloudType)) {
                // Zstack Cloud用的虚拟化授权
                if (
                    xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_ZSTACK'] == $data['hypervisor_type']
                    || xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_NEXAVM_NCSSV'] == $data['hypervisor_type']
                ) {
                    $licenseInfo = $this->getVmPlatformHostLicenseStatus($vmDes, $data['count_host'], $data['auth_host'], $data['unauth_host']);
                } else {
                    $licenseInfo = $this->getCloudPlatformLicenseStatus($license, $data['license_used'], xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD']);
                }
            } elseif (in_array($data['hypervisor_type'], $publicCloudType)) {
                $licenseInfo = $this->getCloudPlatformLicenseStatus($license, $data['license_used'], xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD']);
            } else {
                $licenseInfo = $this->getVmPlatformHostLicenseStatus($vmDes, $data['count_host'], $data['auth_host'], $data['unauth_host']);
            }
            $records['rows'][] = array(
                'checkbox_html' => '<input type="checkbox" name="id[]" value="' . $data['vcenter_uuid'] . '">',
                'no' => $id, //编号
                'ip' => $this->getPlatformIp($data['vcenter_ip'], intval($data['hypervisor_type']), $data['username']),
                'nickname' => $data['nickname'],
                'hypervisor_type' => $data['hypervisor_type'],
                'hypervisor_des' => xphp_get_config('vm', 'VMHYPERVISORDES')[intval($data['hypervisor_type'])],
                'version' => $this->getVersion($data['version']),
                'username' => $data['username'],
                'user_uuid' => $data['user_uuid'],
                'refresh_time' => $data['refresh_time'],
                'license_info' => $licenseInfo,
                'platform_uuid' => $data['vcenter_uuid'],
                'user_name' => $data['user_name'] . $tenantName,
                'permission_flag' => $hostPermission,
                'detail' => json_decode($data['detail'], true)
            );
            $id++;
        }
        $records['total'] = $count[0]['total'];
        //return $this->muOpResult(true, '', 'Get vm platforms list', '', 0, $records);
        return $records;
    }

    /**
     * 获取单个虚拟化平台详情
     * @param string $platformUuid 平台UUID
     * @return string
     */
    public function getVmPlatformDetail(string $platformUuid): string
    {
        $this->paramsCheck($platformUuid);
        $sql = "select hypervisor_type, vcenter_ip, vcenter_uuid, username, password, nickname, detail 
            from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($platformUuid));
        $arr = array();
        if ($data) {
            $arr = array(
                'hypervisor_type' => $data[0]['hypervisor_type'],
                'ip' => $data[0]['vcenter_ip'],
                'username' => $data[0]['username'],
                'password' => md5($data[0]['password']),
                'rname' => html_entity_decode($data[0]['nickname']),
                'platform_uuid' => $data[0]['vcenter_uuid'],
                'detail' => json_decode($data[0]['detail'], true)
            );
        }
        return $this->muOpResult(true, '', 'Get vm platform details', '', 0, $arr);
    }

    /**
     * 获取虚拟化平台自动刷新配置
     * @return array
     */
    public function getRefreshVcenterTime(): array
    {
        $sql = "select vcenter_refresh_interval, public_cloud_platform_refresh_interval, 
            private_cloud_platform_refresh_interval, authorized_flag from bd_system";
        $result = $this->dbSelect($sql);
        return array(
            'vcenter_refresh_interval' => $result[0]['vcenter_refresh_interval'],
            'public_cloud_platform_refresh_interval' => $result[0]['public_cloud_platform_refresh_interval'],
            'private_cloud_platform_refresh_interval' => $result[0]['private_cloud_platform_refresh_interval'],
            'authorized_flag' => intval($result[0]['authorized_flag']),
            'user_uuid' => xphp_get_user_info()['userUuid']
        );
    }

    /**
     * 虚拟化平台自动刷新配置
     * @param array $params 参数
     * @return bool
     */
    public function vmPlatformAutoRefresh(array $params)
    {
        $refresh = $params['refresh'] * 60;
        $type = $params['platform_type'];
        $this->paramsCheck($refresh);
        if ('public' == $type) {
            $sql = "update bd_system set public_cloud_platform_refresh_interval = ?";
            $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
        } elseif ('private' == $type) {
            $sql = "update bd_system set private_cloud_platform_refresh_interval = ?";
            $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        } else {
            $sql = "update bd_system set vcenter_refresh_interval = ?";
            $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
        }
        $authKey = $this->getUserAuthKeyBySubModule($subModule);
        $this->checkAuthByUserUuid(xphp_get_user_info()['userUuid'], $authKey);
        return $this->dbExec($sql, array($refresh));
    }

    /**
     * 得到虚拟化中心详情的树
     * @param array $params 参数
     * @return string
     */
    public function getVmPlatformsTree(array $params): string
    {
        $archiveFlag = $params['archive_flag']; //归档标记
        $displayMode = intval($params['display_mode']); //显示方式
        $platformUuid = $params['platform_uuid']; //虚拟化中心UUID,从虚拟机管理点击进去才有
        $cloudFlag = $params['cloud_flag']; //是否云平台
        $cloudType = $params['cloud_type'] ?? 'public'; //类型：public/private
        $awsFlag = $params['aws_flag']; //是否AWS备份获取
        $onlyHypervisorFlag = $params['only_hypervisor_flag'] ?? false; // 只获取虚拟化类型那层
        $hypervisorType = intval($params['hypervisor_type']);
        $this->paramsCheck($displayMode);
        $selectVc = [];
        if ($platformUuid) {
            $selectVc = $this->getVmPlatformInfo($platformUuid, $displayMode);
        }

        $sql = "select vcenter_id, vcenter_ip, vcenter_uuid, nickname, vcenter_name, 
            hypervisor_type, vcenter_flag, detail, vv.user_uuid, username from vm_vcenter vv ";
        if (empty($userInfo['tenantuuid'])) {
            // 不能看租户内部的资源
            $sql .= " LEFT JOIN mt_user_tenant mut on vv.user_uuid = mut.user_uuid WHERE mut.tenant_uuid IS NULL";
        } else {
            $sql .= ' where 1 = 1';
        }
        //        $cloudTypes = array_merge(
//            xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'],
//            [xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_AWS']]
//        );
//        $cloudTypesStr = implode("','", $cloudTypes);
        $publicCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'];
        $publicCloudTypesStr = implode("','", $publicCloudTypes);
        $privateCloudTypes = xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud'];
        $privateCloudTypesStr = implode("','", $privateCloudTypes);
        if ($cloudFlag) {
            if ($awsFlag) {
                $sql .= " and hypervisor_type = '" .
                    xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_AWS'] . "'";
            }
            if ('private' == $cloudType) {
                $sql .= " and hypervisor_type in ('" . $privateCloudTypesStr . "')";
                $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
            } else {
                $sql .= " and hypervisor_type in ('" . $publicCloudTypesStr . "')";
                $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
            }
        } else {
            $sql .= " and hypervisor_type not in ('" . $publicCloudTypesStr . "')";
            $sql .= " and hypervisor_type not in ('" . $privateCloudTypesStr . "')";
            $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
        }
        if ($hypervisorType) {
            $sql .= " and hypervisor_type = {$hypervisorType}";
        }

        // 获取分配资源的权限-operate
        if (v2_auth_checks()) {
            // not admin or global-operator
            $resourceUuidSql = v2_auth_get_source_by_type($this->getResourceTypeBySubModule($subModule), 'uuid');
            $sqlNew = " EXISTS (select vcenter_uuid from 
                                        (select DISTINCT vcenter_uuid
                                            from vm_tree
                                             where {$resourceUuidSql}
                                        ) v2_auth_all2
                                 where v2_auth_all2.vcenter_uuid = vv.vcenter_uuid) ";
            $sql .= " and {$sqlNew}";
        }

        $sqlParams = array();
        $data = $this->dbSelect($sql, $sqlParams);
        $tree = array();
        $hypervisor = array();

        $userInfo = xphp_get_user_info();
        // 获取当前用户所有拥有虚拟机
        $vcenterList = array();
        $vmList = ResourcesHandler::instance()->pGetUserResourceVM(
            xphp_get_user_info()['userUuid'],
            'tree_id',
            'desc',
            0,
            'all',
            [],
            $subModule
        );
        foreach ($vmList['data'] as $vm) {
            if (!in_array($vm['vcenter_uuid'], $vcenterList)) {
                $vcenterList[] = $vm['vcenter_uuid'];
            }
        }

        $chkDisabled = v2_auth_need_operation();
        // 标记哪些虚拟化有“已分配虚拟机”节点
        $allocatedVmHypervisors = [];

        // 判断系统授权状态，若为异常则不可选
        $sql = "select authorized_flag from bd_system";
        $authFlag = $this->dbSelect($sql)[0]['authorized_flag'];
        if (xphp_get_config('app', 'FLAG')['SET'] != $authFlag) {
            $chkDisabled = true;
        }

        foreach ($data as $d) {
            $openFlag = false;
            $hypervisorType = intval($d['hypervisor_type']);
            //虚拟化中心不属于该用户,也不是分配的资源直接排除
            if (xphp_get_user_info()['userUuid'] != $d['user_uuid'] && !in_array($d['vcenter_uuid'], $vcenterList)) {
                continue;
            }

            //归档占时不支持hyper-v
            if (
                $archiveFlag &&
                $hypervisorType == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']
            ) {
                continue;
            }
            if (
                !in_array($hypervisorType, xphp_get_config('vm')['VMHYPERVISORGROUP']['vmware']) &&
                !in_array($hypervisorType, xphp_get_config('vm')['VMHYPERVISORGROUP']['huawei'])
            ) {
                if ($displayMode != xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']) {
                    //因为vmware多种形式显示
                    continue;
                }
            }

            if (in_array($hypervisorType, xphp_get_config('vm')['VMHYPERVISORGROUP']['huawei'])) {
                if ($displayMode == xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_VM']) {
                    //huawei暂时不支持主机和虚拟机显示
                    continue;
                }
            }
            if (!in_array($hypervisorType, $hypervisor)) {
                if (!in_array($hypervisorType, v2_license_get_v())) {
                    // 未勾选授权的排除
                    continue;
                }
                //没有hypervisor层时,自动添加
                $hypervisorLevNode = $this->getHypervisorLevTree($hypervisorType, 1);
                $hypervisor[] = $hypervisorType;

                //如果选择的vcenter虚拟化类型就是他
                if ($selectVc && $selectVc['hypervisor_type'] == $d['hypervisor_type']) {
                    $hypervisorLevNode['open'] = true;
                }
                $tree[] = $hypervisorLevNode;
            }
            if ($onlyHypervisorFlag) {
                continue;
            }

            $platformFlag = false;
            if ($d['vcenter_flag'] == xphp_get_config('app')['FLAG']['SET']) {
                //如果是vcenter
                $platformFlag = true;
            }

            //如果选中的就是他
            if ($selectVc && $selectVc['vcenter_uuid'] == $d['vcenter_uuid']) {
                $openFlag = true;
            }

            // 租户模式下，虚拟化中心不属于该用户，但属于分配的资源，第二层显示为“已分配虚拟机”或“已分配实例”
            if (
                !empty($userInfo['tenantuuid']) && xphp_get_user_info()['userUuid'] != $d['user_uuid']
                && !in_array($hypervisorType, $allocatedVmHypervisors)
            ) {
                $allocatedVmHypervisors[] = $hypervisorType;
                $name = $cloudFlag ? xphp_get_lang('WEB_VM_TREE_ALLOCATED_INSTANCE')
                    : xphp_get_lang('WEB_VM_TREE_ALLOCATED_VM');
                $node = array(
                    "id" => $hypervisorType . '_allocated_vm', // 特殊处理
                    "pid" => $hypervisorType,
                    "name" => html_entity_decode($name),
                    "title" => $name,
                    "isParent" => true,
                    "nocheck" => true,
                    "type" => 1,
                    "platform_flag" => false,
                    "flush" => true,
                    "iconSkin" => $this->getHypervisorIcon($hypervisorType, 4), // 显示为文件夹
                    "hypervisor_type" => $d['hypervisor_type'],
                    "hypervisor" => $d['hypervisor_type'],
                    'platform_uuid' => $d['vcenter_uuid'],
                    "vcenteruuid" => $d['vcenter_uuid'],
                    "clickshow" => true,
                    "open" => false,
                    "eventtype" => '',
                    "path" => $name,
                    "path_show" => html_entity_decode($name),
                    "nosnapshot" => true,
                    'chkDisabled' => true,
                    'allocated_vm_flag' => true,
                );
                $tree[] = $node;
                continue;
            }
            if (!empty($userInfo['tenantuuid']) && xphp_get_user_info()['userUuid'] != $d['user_uuid'] && in_array($d['vcenter_uuid'], $vcenterList)) {
                // 平台是已分配的不再显示
                continue;
            }

            if (!in_array($hypervisorType, v2_license_get_v())) {
                // 未勾选授权的排除
                continue;
            }

            // 公有云获取平台下所有区域
            $regions = [];
            if ($cloudFlag && $cloudType == 'public') {
                $sql = "select name, uuid from vm_tree where vcenter_uuid = ? and type = ?";
                $data = $this->dbSelect($sql, [$d['vcenter_uuid'], xphp_get_config('vm', 'VM_TREE_TYPE')['CLUSTER']]);
                foreach ($data as $da) {
                    $regions[] = [
                        'host_uuid' => $da['uuid'],
                        'host_name' => $da['name'],
                    ];
                }
            }

            $name = $this->getVmPlatformNameInTree(
                $d['vcenter_uuid'],
                $hypervisorType,
                $d['vcenter_flag'],
                $d['vcenter_ip'],
                $d['nickname'],
                $d['vcenter_name'],
                $d['username']
            );
            $node = array(
                'treeId' => $d['vcenter_uuid'] . '_' . $d['vcenter_uuid'],
                'id' => $d['vcenter_uuid'],
                'pid' => $hypervisorType,
                'name' => html_entity_decode($name),
                'title' => $name,
                'path' => $name,
                'path_show' => html_entity_decode($name),
                'isParent' => true,
                'nocheck' => true,
                'type' => 1,
                'platform_flag' => $platformFlag,
                'flush' => true,
                'iconSkin' => $this->getHypervisorIcon($hypervisorType, $d['vcenter_flag']),
                'hypervisor_type' => $d['hypervisor_type'],
                'hypervisor' => $d['hypervisor_type'],
                'platform_uuid' => $d['vcenter_uuid'],
                'vcenteruuid' => $d['vcenter_uuid'],
                'clickshow' => true,
                'open' => $openFlag,
                'eventtype' => '',
                'chkDisabled' => $chkDisabled,
                'regions' => $regions
            );
            $tree[] = $node;
        }
        if ($onlyHypervisorFlag) {
            // 按虚拟化排序
            $tree = v2_array_sort($tree, 'id', 'asc', 0, -1);
        }

        return $this->muOpResult(true, '', 'Get vm platforms tree', '', 0, $tree);
    }

    /**
     * 得到某台宿主机下所有虚拟机
     * @param array $params 参数
     * @return array
     */
    public function getVmPlatformVms(array $params): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $platformUuid = $params['platform_uuid'];  //虚拟化中心uuid
        //父节点
        //是否只获取虚拟机
        $hypervisorType = $params['hypervisor_type'];    //虚拟化类型
        $displayMode = $params['display_mode'];        //显示模式
        $parentNodes = $params['new_node'] ?? $params['node'];
        $nodeUuids = json_decode($parentNodes, true);           //所有父节点
        $platformFlag = $params['platform_flag'];  //是否是平台
        $this->paramsCheck($platformUuid, $hypervisorType, $displayMode, $nodeUuids);
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $sortArr = array('vm_name' => 'vt.name', 'power_state' => 'vt.power_state');

        $uuidStr = $this->getParentUUIDNodeStr($platformFlag, $platformUuid, $displayMode, $nodeUuids);
        $sql = "select vt.name, vt.uuid, vt.conn_state, vt.power_state, vt.dir_path from vm_tree vt 
                join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                left join mt_user_resource mur on vt.uuid = mur.vm_uuid and mur.resource_type in (3, 58) 
                where vt.vcenter_uuid = ? and vt.display_mode = ? and vt.type = ?
                and vt.parent_uuid in ('" . $uuidStr . "')";
        $sqlCount = "select count(vt.name) as total from vm_tree vt 
                     join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
                     left join mt_user_resource mur on vt.uuid = mur.vm_uuid and mur.resource_type in (3, 58) 
                     where vt.vcenter_uuid = ? and vt.display_mode = ? and vt.type = ?
                     and vt.parent_uuid in ('" . $uuidStr . "')";

        $sqlParams = array($platformUuid, $displayMode, xphp_get_config('vm')['VM_TREE_TYPE']['VM']);
        $sqlCountParams = array($platformUuid, $displayMode, xphp_get_config('vm')['VM_TREE_TYPE']['VM']);

        $publicCloudFlag = in_array($hypervisorType, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']);
        $userInfo = xphp_get_user_info();
        if (empty($userInfo['tenantuuid']) && $userInfo['userUuid'] == "a508b813-19c7-eb4e-d6fa-bb61b25a4de9") {
            // 超级管理查看所有的资源
        } elseif (xphp_three_powers() && $this->isAdmin()) {
            // 三权模式 并且是系统管理员才能看到所有的资源
        } else {
            // 关联管理用户判断 存储资源 - 查看
            $authKey = $publicCloudFlag ? 'cloud_platform_look' : 'vcenter_manager_look';
            $authUser = $userInfo['authUser'][$authKey] ?? [];
            if (!empty($authUser)) {
                // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and (vv.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
                $sqlCount .= " and (vv.user_uuid in {$useruuidArr} or mur.user_uuid in {$useruuidArr}) ";
            } else {
                $userUuid = xphp_get_user_info()['userUuid'];
                $sql .= " and (vv.user_uuid = ? or mur.user_uuid = ?) ";
                $sqlCount .= " and (vv.user_uuid = ? or mur.user_uuid = ?) ";
                $sqlParams = array_merge($sqlParams, array($userUuid, $userUuid));
                $sqlCountParams = array_merge($sqlCountParams, array($userUuid, $userUuid));
            }
        }

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ?";
        $sqlParams = array_merge($sqlParams, array($start, $length));

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $vmInfo = $this->getTenantBackupVMInfo();
        $vms = array();
        $i = 1;
        $vmDes = require APP_PATH . 'v2/description/Vm.php';
        $treeTypeConf = xphp_get_config('vm', 'VM_TREE_TYPE');
        $vmGroupConf = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        foreach ($data as $d) {
            $vmBackupFlag = $this->getVMInBackupFlag(
                $treeTypeConf['VM'],
                $d['uuid'],
                $platformUuid,
                $vmInfo,
                $hypervisorType,
                $treeTypeConf,
                $vmGroupConf
            );
            $vms[] = array(
                'checkbox_html' => '<input type="checkbox" name="id[]" value="' . $d['uuid'] . '">',
                'no' => $i,
                'vm_name' => $d['name'],
                'vm_status_des' => $vmDes['VmMachineStatus'][intval($d['power_state'])],
                'operations' => $this->getVMOpcode($d['power_state'], $vmBackupFlag),
                'backup_flag' => $vmBackupFlag,
                'vm_status' => intval($d['power_state']),
                'vm_uuid' => $d['uuid'],
                'platform_uuid' => $platformUuid
            );
            $i++;
        }
        $records['rows'] = $vms;
        $records['total'] = $count[0]['total'];

        return $records;
    }

    /**
     * 同步虚拟化平台信息
     * @param array $params 参数
     * @return string
     */
    public function syncVmPlatform(array $params): string
    {
        $uuid = $params['platform_uuid'];
        $ip = $params['ip'];//如果IP存在
        if ($ip) {
            $uuid = $this->getVmPlatformUuidByIp($ip);
        }
        $sql = "select hypervisor_type, vcenter_ip from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $displayMode = xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['ALL'];
        $refreshResult = $this->refreshVmPlatform($data[0]['hypervisor_type'], $uuid, $displayMode);
        $operate = xphp_get_lang('WEB_VM_VCENTER_SYNC') . $data[0]['vcenter_ip'];
        return $this->muOpResult($refreshResult['result'], $operate, '', 0, $refreshResult['errorCode']);
    }

    /**
     * 获取虚拟化平台下宿主机列表，授权使用
     * @param array $params 参数
     * @return array
     */
    public function getVmPlatformHosts(array $params): array
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $sortColumn = $params['sort'];
        $sortType = $params['order'];
        $platformUuid = $params['platform_uuid'];
        $sortArr = array(
            'host_name' => 'vh.host_name',
            'host_ip' => 'vh.host_ip',
            'platform_info' => 'vv.vcenter_ip',
            'online_flag' => 'vh.online_flag',
            'cpu_count' => 'cpu_count',
            'authorization_flag' => 'vh.authorization_flag'
        );
        $sql = "select vh.host_name, vh.host_uuid, vh.host_ip, vh.authorization_flag, vh.cpu_count,vh.online_flag, vh.detail,
                vv.vcenter_ip, vv.hypervisor_type, vv.nickname, vv.vcenter_uuid
                from vm_host vh, vm_vcenter vv
                where vh.vcenter_uuid = vv.vcenter_uuid and vh.vcenter_uuid = ?";
        $sqlCount = "select count(vh.host_name) as total from vm_host vh, vm_vcenter vv
                where vh.vcenter_uuid = vv.vcenter_uuid and vh.vcenter_uuid = ?";
        $sqlParams = array($platformUuid, $start, $length);
        $sqlCountParams = array($platformUuid);

        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

        $data = $this->dbSelect($sql, $sqlParams);
        $dataCount = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array('rows' => array());
        foreach ($data as $d) {
            $detail = json_decode($d['detail'], true);
            $userLang = xphp_get_user_info()['language'];
            $hostName = $d['host_name'];
            if (('zh-cn' == $userLang || 'zh-tw' == $userLang) && $detail['cn_name']) {
                $hostName = $detail['cn_name'];
            }
            if ('en-us' == $userLang && $detail['en_name']) {
                $hostName = $detail['en_name'];
            }
            $records['rows'][] = array(
                'checkbox_html' => '<input type="checkbox" name="id[]" value="' . $d['host_uuid'] . '">',
                'host_uuid' => $d['host_uuid'],
                'host_name' => $hostName,
                'host_ip' => $d['host_ip'],
                'platform_info' => $d['vcenter_ip'] == $d['nickname']
                    ? $d['vcenter_ip'] : $d['nickname'] . '(' . $d['vcenter_ip'] . ')', //别名
                'online_flag_des' => $this->getHostStatusDes($d['online_flag']),
                'cpu_count' => $d['cpu_count'],
                'authorization_flag_des' => $this->getAuthorizationStatusDes($d['authorization_flag']),
                'authorization_flag' => intval($d['authorization_flag']),
                'online_flag' => intval($d['online_flag'])
            );
        }

        $records['total'] = $dataCount[0]['total'];
        return $records;
    }

    /**
     * 得到某个虚拟化中心的所有任务
     * @param string $platformUuid 平台uuid
     * @return array
     */
    public function getPlatformCurrentJobs(string $platformUuid): array
    {
        $sql = "select bt.task_uuid,bt.task_name from bd_task bt,vm_machine_list vml
            where bt.task_uuid = vml.task_uuid and vml.vcenter_uuid = ? and bt.task_type = ? and bt.user_uuid = ?
            group by bt.task_uuid ";
        $order = " order by CASE
                        WHEN ASCII(SUBSTRING(bt.task_name, 1, 1)) = 32 THEN 0
                        WHEN ASCII(SUBSTRING(bt.task_name, 1, 1)) BETWEEN 33 AND 47 THEN 1
                        WHEN ASCII(SUBSTRING(bt.task_name, 1, 1)) BETWEEN 48 AND 57 THEN 2
                        WHEN ASCII(SUBSTRING(bt.task_name, 1, 1)) BETWEEN 65 AND 90 THEN 3
                        WHEN ASCII(SUBSTRING(bt.task_name, 1, 1)) BETWEEN 97 AND 122 THEN 4
                        ELSE 5
                    END asc,
                    CASE 
                        WHEN bt.task_name REGEXP '^[0-9]' 
                        THEN CAST(REGEXP_SUBSTR(bt.task_name, '^[0-9]+') AS UNSIGNED)
                        ELSE 9999999
                    END asc,
                    LOWER(SUBSTRING(bt.task_name, 1, 1)) asc,
                    CONVERT(bt.task_name USING gbk) asc,
                    BINARY bt.task_name";
        $sql .= $order;
        $sqlParams = array(
            $platformUuid,
            xphp_get_config('task', 'TASKTYPE')['BACKUP'],
            xphp_get_user_info()['userUuid']
        );
        $data = $this->dbSelect($sql, $sqlParams);
        $taskInfo = array();
        foreach ($data as $d) {
            $taskInfo[] = array(
                'uuid' => $d['task_uuid'],
                'name' => $d['task_name'],
            );
        }
        return ['rows' => $taskInfo, 'total' => count($taskInfo)];
    }

    /**
     * 异步获取/刷新虚拟化平台的虚拟机树(老方法 getBackupSyncVcenter)
     * @param array $params
     * @return array
     */
    public function getVmPlatformsVmTree(array $params): array
    {
        $refreshFlag = $params['refresh_flag'] ?? false;
        $hypervisorType = $params['hypervisor_type'];
        $platformUuid = $params['platform_uuid'] ?? '';
        $displayMode = $params['display_mode'];
        $platformFlag = $params['platform_flag'];
        $showVmFlag = $params['show_vm_flag'] ?? true;
        $openFlag = $params['open_flag'] ?? false;
        $modifyFlag = $params['modify_flag'] ?? false;
        $awsFlag = $params['aws_flag'] ?? false;
        $jobUuid = $params['job_uuid'] ?? '';
        $parentUuid = $params['parent_uuid'] ?? '';
        $loadAllFlag = $params['loadall_flag'] ?? false;
        $offset = $params['offset'];
        $limit = $params['limit'];
        $keyword = strlen($params['keyword']) > 0 ? $params['keyword'] : null;
        // "已分配虚拟机"节点获取标志，租户使用
        $allocatedVmFlag = $params['allocated_vm_flag'] ?? false;
        // "指定虚拟机恢复"获取标志
        $specificVmRecoveryFlag = $params['specific_vm_recovery_flag'] ?? false;
        if (!$allocatedVmFlag) {
            $this->refreshVmPlatformCheck($refreshFlag, $hypervisorType, $platformUuid, $displayMode);
        }
        return $this->getTreeFromVMTree(
            $hypervisorType,
            $platformFlag,
            $platformUuid,
            $displayMode,
            $showVmFlag,
            $openFlag,
            $modifyFlag,
            $awsFlag,
            $jobUuid,
            $allocatedVmFlag,
            $specificVmRecoveryFlag,
            $parentUuid,
            $loadAllFlag,
            $offset,
            $limit,
            $keyword
        );
    }

    /**
     * 检测并刷新虚拟化中心
     * @param bool   $refreshFlag    刷新标志
     * @param int    $hypervisorType 虚拟化类型
     * @param string $platformUuid   平台uuid
     * @param int    $displayMode    展示方式
     * @return bool
     */
    public function refreshVmPlatformCheck(
        bool $refreshFlag,
        int $hypervisorType,
        string $platformUuid,
        int $displayMode
    ): bool {
        if (!$refreshFlag) {
            return true;
        }
        $mbResult = $this->refreshVmPlatform($hypervisorType, $platformUuid, $displayMode);

        $result = $mbResult['result'];
        if (!$result) {
            //如果刷新失败,返回错误消息
            //因为改成了同步 提示信息就改成同步
            $operate = xphp_get_lang('WEB_VM_VCENTER_SYNC');
            exit($this->muOpResult($result, $operate, '', '', $mbResult['errorCode']));
        }
        return true;
    }

    /**
     * 根据虚拟化平台uuid和展示方式得到虚拟机树
     * @param int     $hypervisor   虚拟化类型
     * @param bool    $platformFlag 是否虚拟化平台获取
     * @param string  $platformUuid 虚拟化平台uuid
     * @param int     $displayMode  显示方式：1主机和集群，2虚拟机和模板，3主机和虚拟机
     * @param boolean $showVMFlag   是否显示虚拟机,用于虚拟化中心详情
     * @param boolean $openFlag     父节点是否展开标志
     * @param boolean $modifyFlag   是否是修改任务标志(修改任务的时候复选框可用)
     * @param boolean $awsFlag      是否AWS获取
     * @param string  $taskUuid     任务uuid
     * @param bool $allocatedVmFlag "已分配虚拟机"节点获取标志，租户使用
     * @param bool $specificVmRecoveryFlag "指定虚拟机恢复"获取标志
     * @param string $parentUuid 父级粒度的uuid
     * @param bool $loadAllFlag 加载全部标志
     * @param int|null $offset 加载更多的偏移量
     * @param int|null $limit 加载更多的条数
     * @param string|null $keyword 搜索的关键词
     * @return array
     */
    public function getTreeFromVMTree(
        int $hypervisor,
        bool $platformFlag,
        string $platformUuid,
        int $displayMode,
        bool $showVMFlag,
        bool $openFlag = false,
        bool $modifyFlag = false,
        bool $awsFlag = false,
        string $taskUuid = '',
        bool $allocatedVmFlag = false,
        bool $specificVmRecoveryFlag = false,
        string $parentUuid = '',
        bool $loadAllFlag = false,
        int $offset = null,
        int $limit = null,
        string $keyword = null
    ): array {
        $userInfo = xphp_get_user_info();
        if (isset($keyword)) {
            $platformUuid = '';
            if (!empty($userInfo['tenantuuid'])) {
                // 直接返回空通过页面搜索
                return ['rows' => [], 'total' => 0, 'tenant_flag' => true];
            }
        }
        // 全部展开，即一次性加载
        $loadOnceFlag = $platformFlag && !isset($limit);
        //hyperv宿主机是虚拟化中心时屏蔽，即sql中if那部分
        $hypervType = xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV'];
        if ($allocatedVmFlag) {
            // 租户获取已分配虚拟机时是所有虚拟化中心
            $sql = "select distinct vv.user_uuid, vv.hypervisor_type, vv.vcenter_name, vv.vcenter_flag, vt.tree_id, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.conn_state, 
            vt.power_state, vt.host_uuid, vt.vcenter_uuid, vt.version, vt.detail as tree_detail, vh.host_name, vv.hypervisor_type, vm.detail, '' as vml_task_uuid 
            from vm_tree vt 
            join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
            left join vm_machine vm on vt.vcenter_uuid = vm.vcenter_uuid and vt.uuid = vm.vm_uuid 
            left join vm_host vh on vt.host_uuid = vh.host_uuid and vt.vcenter_uuid = vh.vcenter_uuid 
            where vt.display_mode = ? and if(vv.hypervisor_type = ? and vt.type = 4 and vt.uuid = ?, 0, 1) ";
            $sqlParams = array($displayMode, $hypervType, $platformUuid);
        } elseif ($specificVmRecoveryFlag) {
            $sql = "select distinct vv.user_uuid, vv.hypervisor_type, vv.vcenter_name, vv.vcenter_flag, vv.username, vv.password, vt.tree_id, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.conn_state, 
            vt.power_state, vt.host_uuid, vt.vcenter_uuid, vt.version, vt.detail as tree_detail, vh.host_name, vv.hypervisor_type, vm.detail, vt2.name as parent_name, '' as vml_task_uuid 
            from vm_tree vt 
            join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
            left join vm_machine vm on vt.vcenter_uuid = vm.vcenter_uuid and vt.uuid = vm.vm_uuid 
            left join vm_host vh on vt.host_uuid = vh.host_uuid and vt.vcenter_uuid = vh.vcenter_uuid 
            left join vm_tree vt2 on vt.parent_uuid = vt2.uuid and vt2.vcenter_uuid = vt.vcenter_uuid
            where vt.vcenter_uuid = ? and vt.display_mode = ? ";
            $sqlParams = array($platformUuid, $displayMode);
        } elseif ($loadAllFlag) {
            // 右侧对象展开，通过dir_path获取粒度下的所有vm
            if ($parentUuid == $platformUuid) {
                $parentSql = "select vcenter_ip from vm_vcenter where vcenter_uuid = ?";
                $parentPath = $this->dbSelect($parentSql, [$parentUuid])[0]['vcenter_ip'];
            } else {
                $parentSql = "select dir_path from vm_tree where vcenter_uuid = ? and uuid = ?";
                $parentPath = $this->dbSelect($parentSql, [$platformUuid, $parentUuid])[0]['dir_path'];
            }
            if ($hypervType == $hypervisor) {
                // HyperV的dir_path是反斜杠
                $parentPath = str_replace('\\', '\\\\\\\\', $parentPath);
                $parentPath .= '\\\\\\\\';
            } else {
                $parentPath .= '/';
            }
            // 虚拟机列表的in_current_task从vm_machine_list获取
            $sql = "select distinct vv.user_uuid, vv.hypervisor_type, vv.vcenter_name, vv.vcenter_flag, vt.tree_id, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.conn_state,
                vt.power_state, vt.host_uuid, vt.vcenter_uuid, vt.version, vt.detail as tree_detail, vh.host_name, '' as vml_task_uuid,
                if(!isnull(vml.machine_id) and vml.delete_flag != 1, 1, 0) as in_task, 
                if(!isnull(vml.machine_id) and vml.delete_flag != 1, 1, 0 and vml.task_uuid = ?) as in_current_task, bt.task_name 
                from vm_tree vt
                join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid
                left join vm_host vh on vt.host_uuid = vh.host_uuid and vt.vcenter_uuid = vh.vcenter_uuid
                left join vm_machine_list vml on vt.type = 7 and vt.uuid = vml.vm_uuid and vt.vcenter_uuid = vml.vcenter_uuid and vml.timepoint_uuid = '' -- 只join备份任务的
                left join bd_task bt on vml.task_uuid = bt.task_uuid
                where vt.vcenter_uuid = ? and vt.dir_path like '{$parentPath}%' and vt.display_mode = ? and if(vv.hypervisor_type = ? and vt.type = 4 and vt.uuid = ?, 0, 1) and vt.type = 7";
            $sqlParams = array($taskUuid, $platformUuid, $displayMode, $hypervType, $platformUuid);
        } else {
            // 左侧初始化加载、加载更多、全部展开、搜索
            $sqlInfo = $this->queryVmTree($taskUuid, $platformUuid, $displayMode, $hypervType);
            $sql = $sqlInfo['sql'];
            $sqlParams = $sqlInfo['sqlParams'];
        }
        if (!$allocatedVmFlag && !$loadOnceFlag && !isset($keyword) && !$loadAllFlag && $parentUuid) {
            $sql .= " and vt.parent_uuid = ? ";
            $sqlParams = array_merge($sqlParams, [$parentUuid]);
        }
        if (isset($keyword)) {
            $sql .= " and vt.name like '%{$keyword}%' and vv.hypervisor_type = ? ";
            $sqlParams = array_merge($sqlParams, [$hypervisor]);
        }

        // 获取分配资源的权限-operate
        if (v2_auth_checks()) {
            // not admin or global-operator
            //获取用户已分配的虚拟机
            $subModule = in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud'])
                ? xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD']
                : xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
            if ($awsFlag) {
                $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
            }
            $filed = $loadAllFlag ? 'vtr.uuid' : 'vt.uuid';
            $resourceUuidSql = v2_auth_get_source_by_type($this->getResourceTypeBySubModule($subModule), $filed);
            $sql .= " and {$resourceUuidSql} ";
        }

        if (in_array(intval($hypervisor), xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware'])) {
            //VMware按照中文排序,保持和vcenter显示顺序一致
            $sql .= ' order by vt.type, convert(vt.name USING gbk) COLLATE gbk_chinese_ci';
        } else {
            $sql .= ' order by vt.type, vt.name';
        }
        if (!$allocatedVmFlag && !$awsFlag && !$loadAllFlag && isset($offset) && isset($limit) && !isset($keyword)) {
            $sql .= ' limit ?, ?';
            $sqlParams = array_merge($sqlParams, [$offset, $limit]);
        }
        $data = (array) $this->dbSelect($sql, $sqlParams);
        //        dump($sql, $sqlParams);
//        dump($data);

        // 获取一遍配置文件
        $iconskin = xphp_get_config('vm', 'VIRTUALIZATIONICONCLASSNAME')[$hypervisor];
        $treeTypeConf = xphp_get_config('vm', 'VM_TREE_TYPE');
        $vmGroupConf = xphp_get_config('vm', 'VMHYPERVISORGROUP');
        $hypervisorTypeConf = xphp_get_config('vm', 'VMHYPERVISORTYPE');
        $displayModeConf = xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE');
        $hostStatusConf = xphp_get_config('vm', 'HOSTSTATUS');

        $node = array();
        $lisenceHosts = $this->getAllLisenceHostUUID();
        //检查Xenserver高版本8.0以上没有静默快照
        $noSnapshot = false;
        if (
            $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XENSERVER']
            || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XCP_NG']
        ) {
            $noSnapshot = $this->getSnapshotFlag($platformUuid);
        }
        if ($modifyFlag) {
            $vmModifyData = $this->getTaskVmList($taskUuid);
            $vmModifyList = $vmModifyData['list'];
            $taskVcenterUuid = $vmModifyData['vcenter_uuid'];
        }
        $disconnectHostUuids = array();//记录断开连接的主机uuid
        $allPath = []; // 记录每个已选粒度的全部层级的路径
        $platformUuids = []; // 记录每个已选粒度的vcenter_uuid

        if (!empty($data) && isset($keyword)) {
            // 查询出的节点的路径
            $allPath = array_column($data, 'dir_path');
            // 分解dir_path获取每个搜索出的vm/粒度的全部上级路径并去重
            $dirPathArr = [];
            foreach ($data as $d) {
                $info = explode('/', $d['dir_path']);
                $prefix = strstr($d['dir_path'], '://', true); // http://或https://前缀
                $path = $prefix ? $prefix . '://' : '';
                if ($prefix) {
                    // 有http://或https://前缀则从ip开始取
                    array_shift($info);
                    array_shift($info);
                }
                foreach ($info as $key => $i) {
                    if ($key == count($info) - 1) {
                        // 当前节点的路径不重复加入
                        continue;
                    }
                    if ($key == 0) {
                        $path .= $i;
                    } else {
                        $path .= '/' . $i;
                    }
                    if (!in_array($path, $dirPathArr) && !in_array($path, $allPath)) {
                        $dirPathArr[] = $path;
                    }
                }
                if (!in_array($d['vcenter_uuid'], $platformUuids)) {
                    $platformUuids[] = $d['vcenter_uuid'];
                }
            }
            $sqlInfo = $this->queryVmTree($taskUuid, $platformUuid, $displayMode, $hypervType, $dirPathArr);
            $sql = $sqlInfo['sql'];
            $sqlParams = $sqlInfo['sqlParams'];
            $pathData = $this->dbSelect($sql, $sqlParams);
            // 合并搜索到的粒度和所有上级
            $data = array_merge($data, $pathData);
        }

        if ($taskUuid) {
            foreach ($data as $d) {
                if ($d['in_current_task']) {
                    // 在任务中，记录所有上级的路径
                    if ($hypervType == $hypervisor) {
                        // HyperV的dir_path是反斜杠
                        $separator = '\\';
                    } else {
                        $separator = '/';
                    }
                    $info = explode($separator, $d['dir_path']);
                    $prefix = strstr($d['dir_path'], '://', true); // http://或https://前缀
                    $path = $prefix ? $prefix . '://' : '';
                    if ($prefix) {
                        // 有http://或https://前缀则从ip开始取
                        array_shift($info);
                        array_shift($info);
                    }
                    foreach ($info as $key => $i) {
                        if ($key == 0) {
                            $path .= $i;
                        } else {
                            $path .= $separator . $i;
                        }
                        if (!in_array($path, $allPath)) {
                            $allPath[] = $path;
                        }
                    }
                }
                if ($d['show_current_task']) {
                    // 当前粒度下有在任务中的粒度，则添加该粒度的路径
                    $allPath[] = $d['dir_path'];
                }
            }
            $allPath = array_unique($allPath);
        }

        $objCount = []; // 统计每个粒度下已添加的节点的个数
        $selectedObjCount = []; // 统计每个粒度下在任务中的节点的个数
        $parentList = array();
        foreach ($data as $d) {
            $pUuid = $d['parent_uuid'];
            if (!isset($objCount[$pUuid])) {
                $objCount[$pUuid] = 0;
            }
            if (!isset($selectedObjCount[$pUuid])) {
                $selectedObjCount[$pUuid] = 0;
            }

            $vmFlag = intval($d['type']) == $treeTypeConf['VM'];
            if (!$loadAllFlag && $vmFlag && !$showVMFlag) {
                $objCount[$pUuid] = $limit + 1; // 不显示加载更多
                continue;   //如果是虚拟机,而且不显示虚拟机
            }

            $name = $d['name'];
            if (!$vmFlag) {
                // 非vm的粒度
                $name = $this->getVMTreeName($d['type'], $d['name'], $hypervisor, $d['vcenter_name'], $d['vcenter_flag'], $treeTypeConf, $hypervisorTypeConf);
                $name = $this->getOffLineStatusDes($d['conn_state'], $name, $hostStatusConf);
            }
            $checkEnabled = true;
            // 检查虚拟化平台的主机授权
            if (!in_array($hypervisor, $vmGroupConf['privatecloud']) && !in_array($hypervisor, $vmGroupConf['publiccloud'])) {
                $licenseData = $this->getVMNameWithLisence($lisenceHosts, $d['vcenter_uuid'], $d['host_uuid'], $name, $d['type'], $vmFlag, $treeTypeConf);
                $name = $licenseData['name'];
                $checkEnabled = $licenseData['check'];
            }
            // 华为云的区域从vm_tree.detail获取名称
            if ($hypervisorTypeConf['VM_HYPERVISOR_TYPE_HUAWEI_CLOUD'] == $hypervisor) {
                $name = CloudPlatform::instance()->getRegionNameMultiLang($name, $d['tree_detail']);
            }
            $inBackupFlag = false;
            // 是否在当前任务备份列表
            $inCurrentBackupFlag = $taskUuid && $d['vml_task_uuid'] == $taskUuid;

            // 任务中的处理
            $vmInfo = [
                'vmuuid' => $d['uuid'],
                'in_task' => $d['in_task'],
                'taskname' => $d['task_name']
            ];
            $titleData = $this->getBackupTreeNodeTitle($d['type'], $name, $vmInfo, $treeTypeConf);
            $title = $titleData['title'];
            $inBackupFlag = $titleData['backupFlag'];
            // 在任务中不可选
            if ($vmFlag && $inBackupFlag && !$inCurrentBackupFlag) {
                $checkEnabled = false;
            }

            //判断修改任务
            $checkedFlag = false;
            $skipFlag = false; // 修改任务时是否跳过
            if (in_array($d['dir_path'], $allPath)) {
                if ($taskUuid) {
                    if (isset($offset) && isset($limit)) {
                        // 加载更多时不重复获取已选的
                        $skipFlag = true;
                    }
                    if ($d['in_current_task']) {
                        // 在当前任务中已勾选
                        $checkedFlag = true;
                        $checkEnabled = true;
                    }
                    ++$selectedObjCount[$pUuid];
                }
            } else {
                // 不在路径列表且不是加载更多时则跳过不显示
                if ($modifyFlag) {
                    $skipFlag = true;
                }
            }

            //若主机断开连接，则虚拟机不可选
            $connState = $this->getVMHostOnlineFlag($d['conn_state'], $hostStatusConf);
            if (intval($d['type']) == $treeTypeConf['HOST'] && !$connState) {
                $checkEnabled = false;
                $connState = false;
                $disconnectHostUuids[] = $d['uuid'];
            }
            if ($vmFlag && in_array($d['host_uuid'], $disconnectHostUuids)) {
                $checkEnabled = false;
                $connState = false;
            }
            ;

            //主机和集群展示方式下屏蔽主机勾选
            if (
                in_array(intval($hypervisor), $vmGroupConf['vmware'])
                && intval($d['type']) == $treeTypeConf['HOST']
                && $displayMode == $displayModeConf['HOST_AND_CLUSTER']
            ) {
                $checkEnabled = false;
            }

            //模板不可选
            if ($treeTypeConf['TEMPLATE'] == $d['type']) {
                $checkEnabled = false;
            }

            // 指定虚拟机恢复获取时都可选
            if ($specificVmRecoveryFlag) {
                $checkEnabled = !$inBackupFlag;
            }

            // 租户模式，获取不属于用户但是已分配的vm
            if ($allocatedVmFlag) {
                if (
                    xphp_get_user_info()['userUuid'] != $d['user_uuid']
                    && $treeTypeConf['VM'] == $d['type']
                    && $hypervisor == $d['hypervisor_type']
                ) {
                    $node[] = array(
                        'id' => $d['uuid'],
                        'pid' => $d['parent_uuid'],
                        'name' => htmlspecialchars_decode(rawurldecode($name)),
                        'hostuuid' => $d['host_uuid'],
                        'hostName' => $d['host_name'],
                        'vcenteruuid' => $d['vcenter_uuid'],
                        'hypervisor' => $hypervisor,
                        'iconSkin' => $this->getTreeTypeIcon($hypervisor, $d['type'], $d['conn_state'], $d['power_state'], $iconskin, $treeTypeConf),
                        "nocheck" => false,
                        "path" => htmlspecialchars_decode(rawurldecode($d['dir_path'])),
                        'path_show' => htmlspecialchars($d['dir_path']),
                        "eventtype" => 'vm',
                        "version" => $d['version'],
                        "inbackup" => $inBackupFlag,
                        "title" => $title ?? '',
                        "chkDisabled" => !$checkEnabled,

                        "online" => $connState,
                        "open" => $openFlag,
                        "clickshow" => $d['uuid'] == $d['vcenter_uuid'],    //虚拟化中心详情使用
                        "detail" => "",
                        "diskChecked" => "",
                        "vmChecked" => "",
                        "initDisk" => false,
                        "nosnapshot" => $noSnapshot,
                        "checked" => $checkedFlag,
                        "type" => $d['type'],
                        "machineDetail" => $d['detail'],
                    );
                }
                continue;
            }

            if ($loadAllFlag) {
                if (!$checkEnabled && !$d['in_current_task']) {
                    // 跳过在其他任务中或未授权等不可选的
                    continue;
                }
                $node[] = array(
                    'id' => $d['uuid'],
                    'pid' => $d['parent_uuid'],
                    'name' => htmlspecialchars_decode(rawurldecode($name)),
                    'vcenteruuid' => $platformUuid,
                    'hypervisor' => $hypervisor,
                    'path' => htmlspecialchars_decode(rawurldecode($d['dir_path'])),
                    'path_show' => htmlspecialchars($d['dir_path']),
                );
            } elseif (isset($keyword)) {
                $node[] = array(
                    'treeId' => $d['vcenter_uuid'] . '_' . $d['uuid'],
                    'id' => $d['uuid'],
                    'pid' => $d['vcenter_uuid'] . '_' . $d['parent_uuid'],
                    'name' => htmlspecialchars_decode(rawurldecode($name)),
                    'hostuuid' => ($awsFlag && $treeTypeConf['CLUSTER'] == $d['type'])
                        ? $d['uuid'] : $d['host_uuid'],
                    'hostName' => ($awsFlag && $treeTypeConf['CLUSTER'] == $d['type'])
                        ? $d['name'] : $d['host_name'],
                    'vcenteruuid' => $d['vcenter_uuid'],
                    'hypervisor' => $hypervisor,
                    'iconSkin' => $this->getTreeTypeIcon($hypervisor, $d['type'], $d['conn_state'], $d['power_state'], $iconskin, $treeTypeConf),
                    'nocheck' => false,
                    'path' => htmlspecialchars_decode(rawurldecode($d['dir_path'])),
                    'path_show' => htmlspecialchars($d['dir_path']),
                    'eventtype' => $vmFlag ? 'vm' : '',
                    'version' => $d['version'],
                    'inbackup' => $inBackupFlag,
                    'title' => htmlspecialchars_decode(rawurldecode($title)),
                    'chkDisabled' => !$checkEnabled,
                    'online' => $connState,
                    'open' => v2_parse_flag_to_bool($d['is_search_parent']),
                    'clickshow' => !$vmFlag, // vm上级都为true
                    'detail' => '',
                    'diskChecked' => '',
                    'vmChecked' => '',
                    'initDisk' => false,
                    'nosnapshot' => $noSnapshot,
                    'checked' => $checkedFlag,
                    'type' => $d['type'],
                    'isParent' => !$vmFlag,
                );
            } else {
                if (($objCount[$pUuid] < $limit || $limit === null || $modifyFlag || $loadOnceFlag) && !$skipFlag || $awsFlag) {
                    $node[] = array(
                        'treeId' => $d['vcenter_uuid'] . '_' . $d['uuid'],
                        'id' => $d['uuid'],
                        'uuid' => $d['uuid'],
                        'pid' => $d['vcenter_uuid'] . '_' . $d['parent_uuid'],
                        'name' => htmlspecialchars_decode(rawurldecode($name)),
                        'hostuuid' => ($awsFlag && $treeTypeConf['CLUSTER'] == $d['type'])
                            ? $d['uuid'] : $d['host_uuid'],
                        'hostName' => ($awsFlag && $treeTypeConf['CLUSTER'] == $d['type'])
                            ? $d['name'] : $d['host_name'],
                        'vcenteruuid' => $platformUuid,
                        'hypervisor' => $hypervisor,
                        'iconSkin' => $this->getTreeTypeIcon($hypervisor, $d['type'], $d['conn_state'], $d['power_state'], $iconskin, $treeTypeConf),
                        'nocheck' => false,
                        'path' => htmlspecialchars_decode(rawurldecode($d['dir_path'])),
                        'path_show' => htmlspecialchars($d['dir_path']),
                        'eventtype' => $vmFlag ? 'vm' : '',
                        'version' => $d['version'],
                        'inbackup' => $inBackupFlag,
                        'title' => htmlspecialchars_decode(rawurldecode($title)),
                        'chkDisabled' => !$checkEnabled,

                        'online' => $connState,
                        'open' => $openFlag && !$d['in_current_task'],
                        'clickshow' => !$vmFlag, // vm上级都为true
                        'detail' => '',
                        'diskChecked' => '',
                        'vmChecked' => '',
                        'initDisk' => false,
                        'nosnapshot' => $noSnapshot,
                        'checked' => $checkedFlag,
                        'type' => $d['type'],
                        'isParent' => !$vmFlag && $showVMFlag,
                        "machineDetail" => $d['detail'],
                        "parentName" => $d['parent_name'],
                        "username" => $d['username'],
                        "password" => $d['password'],
                        "passwordDe" => base64_encode(v2_pt_pass_decrypt($d['password'])),
                    );
                }
                ++$objCount[$pUuid];
                // 修改任务时粒度下在任务的节点数小于节点总数时需要加载更多
                if (!$awsFlag && ($modifyFlag && $selectedObjCount[$pUuid] > 0 && $selectedObjCount[$pUuid] < $objCount[$pUuid] || $objCount[$pUuid] == $limit)) {
                    // 记录需要加载更多的父节点
                    $parent = array(
                        'parent_uuid' => $pUuid,
                        'vcenter_uuid' => $platformUuid,
                        'hypervisor' => $hypervisor,
                        'num' => $offset + $limit
                    );
                    if (!in_array($parent, $parentList)) {
                        $parentList[] = $parent;
                    }
                }
            }
        }
        //        dump($parentList);
        // 添加加载更多节点
        foreach ($parentList as $d) {
            $node[] = [
                'treeId' => $d['vcenter_uuid'] . '_' . $d['parent_uuid'] . '_loadMore',
                'id' => $d['parent_uuid'] . '_loadMore',
                'pid' => $d['vcenter_uuid'] . '_' . $d['parent_uuid'],
                'vcenteruuid' => $d['vcenter_uuid'],
                'hypervisor' => $d['hypervisor'],
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'nocheck' => true,
                'isParent' => false,
                'nextOffset' => $d['num'],
                'eventtype' => 'more',
                'type' => 0
            ];
        }

        if (isset($keyword)) {
            // 搜索时添加vcenter层
            $vcenterNode = $this->getVcenterNode($platformUuids);
            foreach ($vcenterNode as &$item) {
                $item['treeId'] = $item['platform_uuid'] . '_' . $item['platform_uuid'];
            }
            $node = array_merge($node, $vcenterNode);
        }
        return ['rows' => $node, 'total' => count($node), 'obj_count' => $objCount];
    }

    //todo:以下为非VmPlatform控制器直接调用的public方法
    /**
     * 得到查询vm树的sql
     * @param string $taskUuid
     * @param string $platformUuid
     * @param int $displayMode
     * @param int $hypervType
     * @param array $dirPathArr
     * @return array
     */
    private function queryVmTree(string $taskUuid, string $platformUuid, int $displayMode, int $hypervType, array $dirPathArr = []): array
    {
        // 605任务中in_task需判断vm_machine_list.delete_flag != 1
        $searchParentFlag = v2_parse_bool_to_flag(!empty($dirPathArr));
        if ($taskUuid) {
            $sql = "select distinct vv.user_uuid, vv.hypervisor_type, vv.vcenter_name, vv.vcenter_flag, vt.tree_id, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.conn_state, 
            vt.power_state, vt.host_uuid, vt.vcenter_uuid, vt.version, vt.detail as tree_detail, vh.host_name, vml.task_uuid as vml_task_uuid, 
            !isnull(vol.id) as in_current_task, !isnull(vol2.id) as show_current_task, if(!isnull(vml.machine_id) and vml.delete_flag != 1, 1, 0) as in_task,  bt.task_name, {$searchParentFlag} as is_search_parent 
            from vm_tree vt 
            join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
            left join vm_host vh on vt.host_uuid = vh.host_uuid and vt.vcenter_uuid = vh.vcenter_uuid
            left join vm_object_list vol on vt.uuid = vol.object_uuid and vt.vcenter_uuid = vol.vcenter_uuid and vol.task_uuid = ? 
            left join vm_object_list vol2 on vt.type != 7 and vol2.dir_path like if (vv.hypervisor_type = 2, concat(REPLACE(vt.dir_path, ?, '\\\\\\\\'), '%'), concat(vt.dir_path, '%')) and vt.vcenter_uuid = vol2.vcenter_uuid and vol2.task_uuid = ? 
            left join vm_machine_list vml on vt.type = 7 and vt.uuid = vml.vm_uuid and vt.vcenter_uuid = vml.vcenter_uuid and vml.timepoint_uuid = '' -- 只join备份任务的
            left join bd_task bt on vml.task_uuid = bt.task_uuid
            where vt.display_mode = ? ";
            // hyperv的路径要转义反斜杠
            $sqlParams = array($taskUuid, '\\', $taskUuid, $displayMode);
        } else {
            $sql = "select distinct vv.user_uuid, vv.hypervisor_type, vv.vcenter_name, vv.vcenter_flag, vt.tree_id, vt.type, vt.name, vt.uuid, vt.parent_uuid, vt.dir_path, vt.conn_state, 
            vt.power_state, vt.host_uuid, vt.vcenter_uuid, vt.version, vt.detail as tree_detail, vh.host_name, vml.task_uuid as vml_task_uuid, 
            if(!isnull(vml.machine_id) and vml.delete_flag != 1, 1, 0) as in_task, bt.task_name, {$searchParentFlag} as is_search_parent  
            from vm_tree vt 
            join vm_vcenter vv on vt.vcenter_uuid = vv.vcenter_uuid 
            left join vm_host vh on vt.host_uuid = vh.host_uuid and vt.vcenter_uuid = vh.vcenter_uuid
            left join vm_machine_list vml on vt.type = 7 and vt.uuid = vml.vm_uuid and vt.vcenter_uuid = vml.vcenter_uuid and vml.timepoint_uuid = '' -- 只join备份任务的
            left join bd_task bt on vml.task_uuid = bt.task_uuid
            where vt.display_mode = ? ";
            $sqlParams = array($displayMode);
        }
        if ($platformUuid) {
            $sql .= " and vt.vcenter_uuid = ? and if(vv.hypervisor_type = ? and vt.type = 4 and vt.uuid = ?, 0, 1) ";
            $sqlParams = array_merge($sqlParams, [$platformUuid, $hypervType, $platformUuid]);
        }
        if ($dirPathArr) {
            $sql .= " and vt.dir_path in ('" . implode("','", $dirPathArr) . "')";
        }
        return [
            'sql' => $sql,
            'sqlParams' => $sqlParams
        ];
    }

    /**
     * 获取vcenter层的树节点
     * @param array $platformUuids
     * @return array
     */
    private function getVcenterNode(array $platformUuids): array
    {
        $sql = "select vcenter_id, vcenter_ip, vcenter_uuid, nickname, vcenter_name, 
            hypervisor_type, vcenter_flag, detail, vv.user_uuid, username 
            from vm_vcenter vv 
            where vcenter_uuid in ('" . implode("','", $platformUuids) . "')";
        $data = $this->dbSelect($sql);
        $tree = array();
        $chkDisabled = v2_auth_check();
        foreach ($data as $d) {
            $name = $this->getVmPlatformNameInTree(
                $d['vcenter_uuid'],
                $d['hypervisor_type'],
                $d['vcenter_flag'],
                $d['vcenter_ip'],
                $d['nickname'],
                $d['vcenter_name'],
                $d['username']
            );
            $node = array(
                'id' => $d['vcenter_uuid'],
                'pid' => $d['hypervisor_type'],
                'name' => html_entity_decode($name),
                'title' => $name,
                'path' => $name,
                'path_show' => html_entity_decode($name),
                'isParent' => true,
                'nocheck' => false,
                'type' => 1,
                'platform_flag' => true,
                'flush' => true,
                'iconSkin' => $this->getHypervisorIcon($d['hypervisor_type'], $d['vcenter_flag']),
                'hypervisor_type' => $d['hypervisor_type'],
                'hypervisor' => $d['hypervisor_type'],
                'platform_uuid' => $d['vcenter_uuid'],
                'vcenteruuid' => $d['vcenter_uuid'],
                'clickshow' => true,
                'open' => true,
                'eventtype' => '',
                'chkDisabled' => $chkDisabled,
            );
            $tree[] = $node;
        }
        return $tree;
    }

    /**
     * 得到系统所有授权宿主机的UUID
     * @return array
     */
    public function getAllLisenceHostUUID(): array
    {
        $sql = "select vcenter_uuid, host_uuid from vm_host where authorization_flag = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'FLAG')['SET']));
        $hosts = array(
            'hostuuid' => array(),
            'vcenteruuid' => array()
        );
        foreach ($data as $d) {
            $hosts['hostuuid'][] = $d['host_uuid'];
            $hosts['vcenteruuid'][] = $d['vcenter_uuid'];
        }
        return $hosts;
    }

    /**
     * 获取静默快照flag
     * @param string|null $platformUuid 平台uuid
     * @return bool
     */
    public function getSnapshotFlag(string $platformUuid = null): bool
    {
        if (!$platformUuid) {
            return false;
        }
        $flag = false;
        $sql = "select hypervisor_type, version from vm_vcenter where vcenter_uuid = ? ";
        $data = $this->dbSelect($sql, array($platformUuid));
        if (!empty($data)) {
            $hypervisor = intval($data[0]['hypervisor_type']);
            $version = $data[0]['version'];
            if (
                $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XENSERVER']
                || $hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_XCP_NG']
            ) {
                $value = intval(substr($version, 0, 1));
                if ($value >= 8) {
                    $flag = true;
                }
            }
        }

        return $flag;
    }

    /**
     * 根据节点类型得到名称,主要解决KVM的 8 9 10 类型的语言问题
     * @param int    $type         vm_tree树节点类型
     * @param string $name         名称
     * @param int    $hypervisor   虚拟化类型
     * @param string $vcenterName   平台名称
     * @param int    $vcenterFlag   平台flag
     * @param array  $vmTreeType
     * @param array  $hypervisorTypeConf
     * @return string
     */
    public function getVMTreeName(int $type, string $name, int $hypervisor, string $vcenterName, int $vcenterFlag, array $vmTreeType, array $hypervisorTypeConf): string
    {
        if ($type == $vmTreeType['OVIRT_FAKE_HOST_FOLDER']) {
            $langKey = 'WEB_VM_TREE_OVIRT_FAKE_HOST_FOLDER';
            return xphp_get_lang($langKey);
        } elseif ($type == $vmTreeType['OVIRT_FAKE_VM_FOLDER']) {
            $langKey = 'WEB_VM_TREE_OVIRT_FAKE_VM_FOLDER';
            return xphp_get_lang($langKey);
        } elseif ($type == $vmTreeType['OVIRT_CLUSTER_FOLDER']) {
            $langKey = 'WEB_VM_TREE_OVIRT_CLUSTER_FOLDER';
            return xphp_get_lang($langKey);
        } elseif ($type == $vmTreeType['HOST']) {
            if ($hypervisorTypeConf['VM_HYPERVISOR_TYPE_HYPERV'] == $hypervisor) {
                //针对于hyper-v单机宿主机
                if ($vcenterFlag == xphp_get_config('vm', 'HYPERV_VCENTER_FLAG')['MANAGER']) {
                    $name = $vcenterName;
                }
            }
            return $name;
        } else {
            return $name;
        }
    }

    /**
     * 得到节点是否断开状态的描述,断开的时候增加已断开描述
     * @param int    $status 状态
     * @param string $name   描述
     * @param array $hostStatusConf
     * @return string
     */
    public function getOffLineStatusDes(int $status, string $name, array $hostStatusConf): string
    {
        if ($status == $hostStatusConf['DISCONNECT']) {
            $name .= '  ' . '(' . xphp_get_lang('WEB_VM_HOST_DISCONNECTED') . ')';
        }
        return $name;
    }

    /**
     * 根据虚拟机所在宿主机授权情况得到虚拟机名字
     * @param array  $lisenceHostArr 所有授权的宿主机 UUID数组array('vcenteruuid' => array(),
     *                               'hostuuid' => array())
     * @param string $platformUuid   虚拟机所在虚拟化中心UUID
     * @param string $vmHostUUID     虚拟机所在宿主机UUID
     * @param string $vmname         虚拟机的名字
     * @param int    $type           虚拟机树节点类型
     * @param bool   $vmFlag
     * @param array  $treeTypeConf
     * @return array
     */
    public function getVMNameWithLisence(
        array $lisenceHostArr,
        string $platformUuid,
        string $vmHostUUID,
        string $vmname,
        int $type,
        bool $vmFlag,
        array $treeTypeConf
    ): array {
        if (
            $type != $treeTypeConf['VM']
            && $type != $treeTypeConf['HOST']
        ) {
            return ['name' => $vmname, 'check' => true];
        }

        if (empty($vmHostUUID)) {
            //TODO 这里因为虚拟机没有启动的时候可能拿不到虚拟机的宿主机,暂时放过
            return ['name' => $vmname, 'check' => true];
        }

        if (in_array($vmHostUUID, $lisenceHostArr['hostuuid'])) {
            foreach ($lisenceHostArr['hostuuid'] as $key => $value) {
                if ($vmHostUUID == $value) {
                    if ($platformUuid == $lisenceHostArr['vcenteruuid'][$key]) {
                        return ['name' => $vmname, 'check' => true];
                    }
                }
            }
        }


        return ['name' => $vmFlag ? $vmname : '(' . xphp_get_lang('WEB_SYSTEM_LISENCE_UNAUTHORIZED') . ')' . $vmname, 'check' => false];
    }

    /**
     * 得到虚拟机备份树节点标题
     * @param int    $type         vm_tree节点类型
     * @param string $name         节点名字
     * @param array  $backupVMInfo 处于备份任务的虚拟机信息
     * @param array $treeTypeConf
     * @return array
     */
    public function getBackupTreeNodeTitle(
        int $type,
        string $name,
        array $backupVMInfo,
        array $treeTypeConf
    ): array {
        //如果不是虚拟机,直接返回名字
        if ($type != $treeTypeConf['VM']) {
            return ['title' => $name, 'backupFlag' => false];
        }
        //        $backupFlag = $this->getVMInBackupFlag($type, $vmUuid, $platformUuid, $backupVMInfo, $hypervisor, $treeTypeConf, $vmGroupConf);
        if (!$backupVMInfo['in_task']) {
            //虚拟机不在任务中,如果有备注,返回备注信息
//            if (!empty($detail)) {
//                return ['title' => $detail, 'backupFlag' => false];
//            }
            return ['title' => $name, 'backupFlag' => false];
        }
        //在任务中的虚拟机
        return ['title' => $name . "('" . $backupVMInfo['taskname'] . "'" . xphp_get_lang('WEB_BACKUP_TASK_PROTECTED') . ')', 'backupFlag' => true];
    }

    /**
     * 得到虚拟机是否处于备份任务中
     * @param int $vmFlag vm_tree中虚拟机类型
     * @param string $vmUuid 虚拟机uuid
     * @param string $platformUuid 平台UUID
     * @param array $backupVMInfo 处于备份任务的虚拟机信息
     * @param int $hypervisor 虚拟化类型
     * @param array $treeTypeConf
     * @param array $vmGroupConf
     * @return bool
     */
    public function getVMInBackupFlag(int $vmFlag, string $vmUuid, string $platformUuid, array $backupVMInfo, int $hypervisor, array $treeTypeConf, array $vmGroupConf): bool
    {
        //如果不是虚拟机,直接返回
        if ($vmFlag != $treeTypeConf['VM']) {
            return false;
        }
        //如果没有备份的虚拟机,直接返回
        if (empty($backupVMInfo['vmuuid'])) {
            return false;
        }
        //因为虚拟机uuid可能相同，所以这里需要对虚拟机进行遍历查找
        foreach ($backupVMInfo['vmuuid'] as $key => $eachUUID) {
            if ($vmUuid == $eachUUID) {
                //如果是Openstack系列，租户概念，仅检测虚拟机uuid
                if (in_array($hypervisor, $vmGroupConf['openstack'])) {
                    return true;
                }
                //如果找到了虚拟机UUID对应的虚拟机,再找对应位置的vcenteruuid是否一样,一样的话这台虚拟机就是备份状态
                if ($platformUuid == $backupVMInfo['vcenteruuid'][$key]) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 得到虚拟机(所在宿主机)是否授权
     * @param array  $lisenceHostArr 所有授权的宿主机UUID数组
     * @param string $vcenterUUID    平台uuid
     * @param string $vmHostUUID     虚拟机所在宿主机UUID
     * @param bool   $inBackupFlag   是否在备份任务
     * @param bool   $inCurrentBackupFlag     是否在当前备份任务
     * @param int    $hypervisor     虚拟化类型
     * @return boolean
     */
    public function getVMHostLisenced(
        array $lisenceHostArr,
        string $vcenterUUID,
        string $vmHostUUID,
        bool $inBackupFlag,
        bool $inCurrentBackupFlag,
        int $hypervisor
    ): bool {
        if ($inBackupFlag && !$inCurrentBackupFlag) {
            //如果是在备份任务中且不在当前任务,不可用
            return false;
        }
        if (
            in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])
            && !in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['zstack'])
            || in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])
        ) {
            // 私有云、公有云不用判断
            return true;
        }
        if (in_array($vmHostUUID, $lisenceHostArr['hostuuid'])) {
            foreach ($lisenceHostArr['hostuuid'] as $key => $value) {
                if ($vmHostUUID == $value) {
                    if ($vcenterUUID == $lisenceHostArr['vcenteruuid'][$key]) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 检测虚拟机连接状态
     * @param int $connState 连接状态
     * @param array $hostStatusConf
     * @return boolean
     */
    public function getVMHostOnlineFlag(int $connState, array $hostStatusConf): bool
    {
        return $connState != $hostStatusConf['DISCONNECT'];
    }

    /**
     * 根据节点类型和状态得到节点的图标 vm_tree
     * @param int $hypervisor 虚拟化类型
     * @param int $type       节点类型
     * @param int $connState  连接状态
     * @param int $powerState 开机状态
     * @param string $iconskin
     * @param array $treeTypeConf
     * @return string
     */
    public function getTreeTypeIcon(int $hypervisor, int $type, int $connState, int $powerState, string $iconskin, array $treeTypeConf): string
    {
        $icon = '';
        switch ($type) {
            case $treeTypeConf['FOLDER']:
                $icon = $iconskin . '_folder_vm';
                break;
            case $treeTypeConf['OVIRT_DATACENTER']:
            case $treeTypeConf['DATACENTER']:
                $icon = $iconskin . '_datacenter';
                break;
            case $treeTypeConf['OVIRT_CLUSTER_FOLDER']:
            case $treeTypeConf['OVIRT_CLUSTER']:
            case $treeTypeConf['CLUSTER']:
                $icon = $iconskin . '_cluster';
                break;
            case $treeTypeConf['HOST']:
                $icon = $this->getHostStateIcon($connState, $hypervisor);
                break;
            case $treeTypeConf['POOL']:
                $icon = $iconskin . '_pool';
                break;
            case $treeTypeConf['VAPP']:
                $icon = $iconskin . '_vapp';
                break;
            case $treeTypeConf['VM']:
                $icon = $this->getVmStateIcon($connState, $powerState, $hypervisor);
                break;
            case $treeTypeConf['OVIRT_FAKE_HOST_FOLDER']:
                $icon = $iconskin . '_host';
                break;
            case $treeTypeConf['OVIRT_FAKE_VM_FOLDER']:
                $icon = $iconskin . '_vm';
                break;
            case $treeTypeConf['TEMPLATE']:
                $icon = $iconskin . '_template';
                break;
            default:
                break;
        }
        return $icon;
    }

    /**
     * 得到处于备份任务的虚拟机信息
     * @return array
     */
    public function getBackupVMInfo(): array
    {
        $sql = "select vml.vcenter_uuid, vml.vm_uuid, bt.task_name from vm_machine_list vml, bd_task bt
                where vml.task_uuid = bt.task_uuid and bt.task_type = ? ";
        $data = $this->dbSelect($sql, array(xphp_get_config('task')['TASKTYPE']['BACKUP']));
        $info = array(
            'vmuuid' => array(),
            'vcenteruuid' => array(),
            'taskname' => array()
        );
        foreach ($data as $d) {
            $idStr = $d['vcenter_uuid'] . '_' . $d['vm_uuid'];
            $info['vmuuid'][] = $d['vm_uuid'];
            $info['vcenteruuid'][] = $d['vcenter_uuid'];
            $info['taskname'][] = $d['task_name'];
            $info['tasklist'][$idStr] = $d['task_name'];
        }
        return $info;
    }

    /**
     * 得到处于备份任务的虚拟机信息
     * @return array
     */
    public function getTenantBackupVMInfo(): array
    {
        $sql = "select vml.vcenter_uuid, vml.vm_uuid, bt.task_name from vm_machine_list vml, bd_task bt
                where vml.task_uuid = bt.task_uuid and bt.task_type = ? and vml.delete_flag != ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('task')['TASKTYPE']['BACKUP'], xphp_get_config('app')['FLAG']['SET']));
        $info = array(
            'vmuuid' => array(),
            'vcenteruuid' => array(),
            'taskname' => array()
        );
        foreach ($data as $d) {
            $info['vmuuid'][] = $d['vm_uuid'];
            $info['vcenteruuid'][] = $d['vcenter_uuid'];
            $info['taskname'][] = $d['task_name'];
        }
        return $info;
    }

    /**
     * 得到指定虚拟机恢复任务的目标虚拟机信息
     * @return array
     */
    public function getRecoveryTargetVMInfo(): array
    {
        $sql = "select vml.vcenter_uuid, vml.vm_uuid, vml.vm_config, bt.task_name from vm_machine_list vml, bd_task bt
                where vml.task_uuid = bt.task_uuid and bt.task_type = ? and vml.delete_flag != ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('task')['TASKTYPE']['RECOVERY'], xphp_get_config('app')['FLAG']['SET']));
        $info = array(
            'vmuuid' => array(),
            'vcenteruuid' => array(),
            'taskname' => array()
        );
        foreach ($data as $d) {
            $vmConfig = json_decode($d['vm_config'], true);
            if ($vmConfig['new_vm_uuid']) {
                $info['vmuuid'][] = $vmConfig['new_vm_uuid'];
                $info['vcenteruuid'][] = $d['vcenter_uuid'];
                $info['taskname'][] = $d['task_name'];
            } elseif ($vmConfig['new_vm_name']) {
                $sqlMachine = "select vm_uuid from vm_machine where vcenter_uuid = ? and vm_name = ?";
                $dataMachine = $this->dbSelect($sqlMachine, [$d['vcenter_uuid'], $vmConfig['new_vm_name']]);
                if ($dataMachine) {
                    $info['vmuuid'][] = $dataMachine[0]['vm_uuid'];
                    $info['vcenteruuid'][] = $d['vcenter_uuid'];
                    $info['taskname'][] = $d['task_name'];
                }
            }
        }
        return $info;
    }

    /**
     * 得到虚拟化平台的名字，主要是区别XenServer和VMware以及有别名和没有别名
     * @param string $platformUuid 平台UUID
     * @param int    $hypervisor   虚拟化类型
     * @param int    $platformFlag 平台标记：1平台，2宿主机
     * @param string $platformIp   平台IP
     * @param string $nickname     别名
     * @param string $platformName 平台名称
     * @param string $username     用户名
     * @return string
     */
    public function getVmPlatformNameInTree(
        string $platformUuid,
        int $hypervisor,
        int $platformFlag,
        string $platformIp,
        string $nickname,
        string $platformName,
        string $username
    ): string {
        if (in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['xenserver'])) {
            //如果是XenServer,显示IP+(主节点IP)
            $name = $platformName . ' (' . xphp_get_lang('WEB_VM_VCENTER_XENSERVER_MASTER_NODE')
                . ':' . $platformIp . ')';
        } elseif (in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'])) {
            $name = $platformName . '(' . $username . ')';
        } else {
            $name = $platformIp == $nickname ? $platformIp : $nickname . '(' . $platformIp . ')';
        }
        return $this->getHostIsLicensed($platformFlag, $platformUuid, $name, $hypervisor);
    }

    /**
     * 根据虚拟化类型得到树图标
     * @param int $hypervisor   虚拟化类型
     * @param int $platformFlag 标记：1平台，2宿主机，3集群，4文件夹
     * @return string
     */
    public function getHypervisorIcon(int $hypervisor, int $platformFlag): string
    {
        $this->paramsCheck($hypervisor);
        $iconSkin = xphp_get_config('vm')['VIRTUALIZATIONICONCLASSNAME'][$hypervisor];
        if ($platformFlag == xphp_get_config('app')['FLAG']['UNSET']) {
            //如果不是vcenter
            return $iconSkin . '_host';
        } elseif ($platformFlag == xphp_get_config('app')['FLAG']['SET']) {
            return $iconSkin . '_vcenter';
        } elseif ($platformFlag == 3) {
            return $iconSkin . '_hostcluster';
        } elseif ($platformFlag == 4) {
            return $iconSkin . '_folder_vm';
        }

        return $iconSkin;
    }

    /**
     * 获取平台备份数据管理的数据条数
     * @return array
     */
    public function getEngineCount(): array
    {
        $sql = "select count(vpmb.data_uuid) as total from vm_platform_meta_backup_data vpmb, bd_node bn, 
            bd_storage_resource bsr, vm_vcenter vv 
            where vpmb.vcenter_uuid = vv.vcenter_uuid and vpmb.node_uuid = bn.node_uuid 
            and vpmb.storage_uuid = bsr.storage_uuid";
        $result = $this->dbSelect($sql);
        return ['total' => intval($result[0]['total'])];
    }

    /**
     * 平台备份测试连接虚拟化平台
     * @param array $params 参数
     * @return string
     */
    public function testEngine(array $params): string
    {
        $username = $params['username'];
        $password = base64_decode($params['password']);
        $hypervisor = $params['hypervisor_type'];
        $ip = $params['ip'];
        //测试连接操作码
        $opcodeName = 'VM_VCENTER_OP_ENGINE_TEST';
        //组合消息
        $msg = array(
            'ip' => $ip,
            'port' => 22,
            'username' => $username,
            'password' => $password

        );
        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg), FALSE);
        $result = $mbResult['result'];
        $operate = VmOpcode::instance()->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * openstack获取vcenter名字
     * @param string $ip
     * @param int $hypervisor
     * @param string $username
     * @return string
     */
    public function getVcenterIp(string $ip, int $hypervisor, string $username): string
    {
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $ip = $ip . '(' . $username . ')';
        }
        return $ip;
    }

    //todo:以下为内部调用private方法

    /**
     * 获取任务中的虚拟机列表
     * @param mixed $taskUuid 任务uuid
     * @return array
     */
    private function getTaskVmList($taskUuid): array
    {
        $data = [];
        if ($taskUuid) {
            $sql2 = "select vol.object_uuid, vol.vcenter_uuid from vm_object_list vol where vol.task_uuid = ?";
            $data = $this->dbSelect($sql2, array($taskUuid));
        }
        $list = array();
        $platformUuid = '';
        if (!empty($data)) {
            foreach ($data as $d) {
                $list[] = $d['object_uuid'];
            }
            $platformUuid = $data[0]['vcenter_uuid'];
        }

        return ['list' => $list, 'vcenter_uuid' => $platformUuid];
    }

    /**
     * 根据连接状态得到宿主机图标 vm_tree
     * @param int $connState  连接状态
     * @param int $hypervisor 虚拟化类型
     * @return string
     */
    private function getHostStateIcon(int $connState, int $hypervisor): string
    {
        $iconskin = xphp_get_config('vm', 'VIRTUALIZATIONICONCLASSNAME')[$hypervisor];
        switch ($connState) {
            case xphp_get_config('vm', 'HOSTSTATUS')['CONNECT']:
                $icon = $iconskin . '_host_on';
                break;
            case xphp_get_config('vm', 'HOSTSTATUS')['DISCONNECT']:
                $icon = $iconskin . '_host_off';
                break;
            default:
                $icon = $iconskin . '_host';
                break;
        }
        return $icon;
    }

    /**
     * 根据连接状态和虚拟机状态得到虚拟机图标 vm_tree  LS
     * @param int $connState  连接状态
     * @param int $powerState 开机状态
     * @param int $hypervisor 虚拟化类型
     * @return string
     */
    private function getVmStateIcon(int $connState, int $powerState, int $hypervisor): string
    {
        $iconskin = xphp_get_config('vm', 'VIRTUALIZATIONICONCLASSNAME')[$hypervisor];
        if (
            $connState == xphp_get_config('vm', 'VmMachineConnectState')['DISCONNECTED']
            || $connState == xphp_get_config('vm', 'VmMachineConnectState')['COMPLETED']
        ) {
            //虚拟机处于离线状态,不可用
            return $iconskin . '_vm_disable';
        }
        return $this->getVMStatusIcon($powerState, $hypervisor);
    }

    /**
     * 根据虚拟机状态获取状态图标  LS
     * @param int $status     虚拟机状态
     * @param int $hypervisor 虚拟化类型
     * @return string
     */
    private function getVMStatusIcon(int $status, int $hypervisor): string
    {
        $iconskin = xphp_get_config('vm', 'VIRTUALIZATIONICONCLASSNAME')[$hypervisor];
        $icon = $iconskin . '_vm';
        switch ($status) {
            case xphp_get_config('vm', 'MACHINESTATUS')['POWEREDOFF']:
                $icon = $iconskin . '_vm_poweroff';
                break;
            case xphp_get_config('vm', 'MACHINESTATUS')['POWEREDON']:
                $icon = $iconskin . '_vm_running';
                break;
            case xphp_get_config('vm', 'MACHINESTATUS')['SUSPENDED']:
            case xphp_get_config('vm', 'MACHINESTATUS')['PAUSE']:
                $icon = $iconskin . '_vm_suspend';
                break;
        }
        return $icon;
    }

    /**
     * 通过IP获取虚拟化平台UUID
     * @param string $ip 虚拟化平台IP
     * @return string
     */
    private function getVmPlatformUuidByIp(string $ip): string
    {
        $sql = "select vcenter_uuid from vm_vcenter where vcenter_ip = ?";
        $data = $this->dbSelect($sql, array($ip));
        $uuid = '';
        if (!empty($data)) {
            $uuid = $data[0]['vcenter_uuid'];
        }

        return $uuid;
    }

    /**
     * 根据虚拟机状态得到虚拟机可以进行的操作
     * @param int  $status       1关机、2启动、3暂停、4添加到备份任务
     * @param bool $vmBackupFlag 是否备份任务获取
     * @return array
     */
    private function getVMOpcode(int $status, bool $vmBackupFlag): array
    {
        $opArr = array();
        switch ($status) {
            case xphp_get_config('vm')['MACHINESTATUS']['SUSPENDED']:
            case xphp_get_config('vm')['MACHINESTATUS']['PAUSE']:
            case xphp_get_config('vm')['MACHINESTATUS']['POWEREDOFF']:
                $opArr = array(2);
                break;
            case xphp_get_config('vm')['MACHINESTATUS']['POWEREDON']:
                $opArr = array(3, 1);
                break;
        }
        if (!$vmBackupFlag) {
            $opArr[] = 4;
        }

        return $opArr;
    }

    /**
     * 得到父节点的uuid字符串
     * @param bool   $platformFlag 是否平台
     * @param string $platformUuid 平台UUID
     * @param int    $displayMode  展示方式
     * @param array  $nodeUuids    父节点UUID数组
     * @return string
     */
    public function getParentUUIDNodeStr(
        bool $platformFlag,
        string $platformUuid,
        int $displayMode,
        array $nodeUuids
    ): string {
        if (!$platformFlag) {
            //如果是宿主机,查找宿主机的uuid,替换$nodeUuids里面的vcenteruuid
            $sql = "select uuid from vm_tree where vcenter_uuid = ? and parent_uuid = ?  and display_mode = ?
                    and type != ?";
            $sqlParams = array($platformUuid, $platformUuid, $displayMode, xphp_get_config('vm')['VM_TREE_TYPE']['VM']);
            $data = $this->dbSelect($sql, $sqlParams);
            if ($data) {
                $hostUuid = $data[0]['uuid'];
                foreach ($nodeUuids as $key => $value) {
                    if ($value == $platformUuid) {
                        $nodeUuids[$key] = $hostUuid;
                        break;
                    }
                }
            }
        }

        return implode("','", $nodeUuids);
    }

    /**
     * 删除虚拟化平台检查
     * @param array $platformUuids 虚拟化平台ID数组
     * @return void
     */
    private function deleteVmPlatformsCheck(array $platformUuids): void
    {
        $vcenterStr = '';
        foreach ($platformUuids as $vcenter) {
            $vcenterStr .= $vcenter . ' ,';
        }
        if ($vcenterStr) {
            $vcenterStr = substr($vcenterStr, 0, -1);
        }

        $userInfo = xphp_get_user_info();
        if (empty($userInfo['tenantuuid']) && $userInfo['userUuid'] == 'a508b813-19c7-eb4e-d6fa-bb61b25a4de9') {
        } elseif (xphp_three_powers() && $this->isAdmin()) {
            // 三权模式下 系统管理员可以查看所有
        } else {
            $sqlcheck = "select user_uuid, hypervisor_type from vm_vcenter where vcenter_uuid in ( ? )";
            $data = $this->dbSelect($sqlcheck, [$vcenterStr]);
            $publicCloudFlag = in_array(
                $data[0]['hypervisor_type'],
                xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']
            );
            // 关联管理用户判断 存储资源 - 操作
            $authKey = $publicCloudFlag ? 'cloud_platform_operate' : 'vcenter_manager_operate';
            $userArr = array_unique(array_column($data, 'user_uuid'));
            if (!xphp_check_operate($authKey, $userArr)) {
                // 没有操作权限
                exit(
                    $this->muOpResult(
                        false,
                        xphp_get_lang('UI_ROLE_PERMISSION'),
                        xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'),
                        'warning'
                    )
                );
            }
        }

        //检测备份和恢复任务
        $sql = "select vv.nickname, vv.hypervisor_type, bt.task_name from vm_vcenter vv,vm_machine_list vml, bd_task bt  
                where vv.vcenter_uuid = vml.vcenter_uuid 
                and bt.delete_flag = ? 
                and vml.task_uuid = bt.task_uuid 
                and vml.vcenter_uuid in ( ? ) 
                group by vv.nickname ";
        $sqlParams = array(xphp_get_config('app')['FLAG']['UNSET'], $vcenterStr);
        $data = $this->dbSelect($sql, $sqlParams);
        if (!empty($data)) {
            if (
                in_array($data[0]['hypervisor_type'], xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'])
                || in_array($data[0]['hypervisor_type'], xphp_get_config('vm')['VMHYPERVISORGROUP']['privatecloud'])
            ) {
                //云平台
                $msg = xphp_get_lang('UI_CLOUD_PLATFORM') . "'" . $data[0]['nickname'] . "'" .
                    xphp_get_lang('UI_CLOUD_PLATFORM_DELETE_TIPS') . "'" . $data[0]['task_name'] . "'";
                exit($this->muOpResult(false, xphp_get_lang('UI_CLOUD_PLATFORM_DELETE'), $msg, 'warning'));
            } else {
                //如果虚拟化中心有虚拟机存在于备份或恢复任务中,直接返回
                $msg = xphp_get_lang('WEB_VM_VCENTER') . "'" . $data[0]['nickname'] . "'" .
                    xphp_get_lang('WEB_VM_VCENTER_DELETE_TIPS') . "'" . $data[0]['task_name'] . "'";
                exit($this->muOpResult(false, xphp_get_lang('WEB_VM_VCENTER_DELETE'), $msg, 'warning'));
            }
        }
        //检测瞬时恢复和迁移任务
        $sql = "select vv.nickname, bt.task_name from vm_vcenter vv,vm_instant vi, bd_task bt  
                where vv.vcenter_uuid = vi.target_vcenter_uuid 
                and bt.delete_flag = ? 
                and vi.task_uuid = bt.task_uuid 
                and vi.target_vcenter_uuid in ( ? ) 
                group by vv.nickname ";
        $sqlParams = array(xphp_get_config('app')['FLAG']['UNSET'], $vcenterStr);
        $data = $this->dbSelect($sql, $sqlParams);
        if (!empty($data)) {
            //如果虚拟化中心有虚拟机存在于瞬时恢复或迁移任务中,直接返回
            $msg = xphp_get_lang('WEB_VM_VCENTER') . "'" . $data[0]['nickname'] . "'" .
                xphp_get_lang('WEB_VM_VCENTER_DELETE_TIPS') . "'" . $data[0]['task_name'] . "'";
            exit($this->muOpResult(false, xphp_get_lang('WEB_VM_VCENTER_DELETE'), $msg, 'warning'));
        }
        //检查是否有关联虚拟实验室
        $sql = "select svl.virtual_lab_name, vv.nickname from sr_virtual_lab svl, vm_vcenter vv 
            where svl.vcenter_uuid = vv.vcenter_uuid and vv.vcenter_uuid in ( ? ) ";
        $data = $this->dbSelect($sql, array($vcenterStr));
        if (!empty($data)) {
            //如果虚拟化中心有虚拟实验室存在,直接返回
            $msg = xphp_get_lang('WEB_VM_VCENTER') . "'" . $data[0]['nickname'] . "'" .
                xphp_get_lang('WEB_VM_VCENTER_DELETE_VIRTUAL_LAB_EXIST_TIPS') . "'" .
                $data[0]['virtual_lab_name'] . "'";
            exit($this->muOpResult(false, xphp_get_lang('WEB_VM_VCENTER_DELETE'), $msg, 'warning'));
        }
    }

    /**
     * 根据平台UUID得到虚拟化子模块类型
     * @param string $platformUuid 平台UUID
     * @return int
     */
    private function getVmPlatformSubModule(string $platformUuid): int
    {
        $sql = "select hypervisor_type from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($platformUuid));
        if ($data) {
            return intval($data[0]['hypervisor_type']);
        }
        $this->writeLog('VM_VCENTER_GET_SUB_MODULE_ERROR', xphp_get_config('log')['LOG_INFO']['WARNING']);
        return 0;
    }

    /**
     * 得到宿主机是否是授权状态,没有授权需要添加未授权标志
     * @param int    $platformFlag 平台标记
     * @param string $platformUuid 平台UUID
     * @param string $name         名称
     * @param int    $hypervisor   虚拟化类型
     * @return string
     */
    private function getHostIsLicensed(int $platformFlag, string $platformUuid, string $name, int $hypervisor): string
    {
        if ($platformFlag == xphp_get_config('app')['FLAG']['SET']) {
            return $name;
        }
        if ($hypervisor == xphp_get_config('vm')['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_HYPERV']) {
            return $name;
        }
        //如果是宿主机,$platformUuid 其实就是hostuuid
        $sql = "select authorization_flag from vm_host where host_uuid = ?";
        $data = $this->dbSelect($sql, array($platformUuid));
        if ($data[0]['authorization_flag'] != xphp_get_config('app')['FLAG']['SET']) {
            $name = '(' . xphp_get_lang('WEB_SYSTEM_UNAUTHIORIZED') . ')' . $name;
        }
        return $name;
    }

    /**
     * 根据hypervisor类型得到顶层树的描述
     * @param int $hypervisor   虚拟化类型
     * @param int $platformFlag 标记：1平台，2宿主机
     * @param bool $openFlag 是否展开
     * @return array
     */
    private function getHypervisorLevTree(int $hypervisor, int $platformFlag, bool $openFlag = false): array
    {
        $name = xphp_get_config('vm')['VMHYPERVISORDES'][$hypervisor];
        return array(
            'treeId' => $hypervisor,
            'id' => $hypervisor,
            'pId' => 0,
            'name' => $name,
            'title' => $name,
            'open' => $openFlag,
            'nocheck' => true,
            'sid' => $hypervisor,
            'hypervisor_type' => $hypervisor,
            'type' => 0,
            'iconSkin' => $this->getHypervisorIcon($hypervisor, $platformFlag),
        );
    }

    /**
     * 得到虚拟化平台的信息
     * @param string $platformUuid 虚拟化平台UUID
     * @param int    $displayMode  显示方式：1主机和集群，2虚拟机和模板，3主机和虚拟机
     * @return array
     */
    private function getVmPlatformInfo(string $platformUuid, int $displayMode): array
    {
        if ($displayMode != xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_CLUSTER']) {
            //如果不是主机和集群模式,直接返回(其他模式树形不要展开)
            return array();
        }
        $sql = "select hypervisor_type, vcenter_flag from vm_vcenter where vcenter_uuid = ? ";
        $data = $this->dbSelect($sql, array($platformUuid));
        return array(
            'vcenter_uuid' => $platformUuid,
            'hypervisor_type' => $data[0]['hypervisor_type'],
            'vcenter_flag' => $data[0]['vcenter_flag'],
        );
    }

    /**
     * 得到平台下所有宿主机授权的状态
     * @param array $vmDes 描述信息
     * @param int $countHost 宿主机总数
     * @param int $authHost 已授权个数
     * @param int $unAuthHost 未授权个数
     * @return array
     */
    private function getVmPlatformHostLicenseStatus(array $vmDes, int $countHost, int $authHost, int $unAuthHost): array
    {
        $info = array();
        if ($countHost == $authHost) {
            //全部授权
            $info['status'] = xphp_get_config('vm')['HOST_LISENCE']['ALL'];
        } elseif ($countHost == $unAuthHost) {
            //未授权
            $info['status'] = xphp_get_config('vm')['HOST_LISENCE']['NONE'];
        } else {
            //部分授权
            $info['status'] = xphp_get_config('vm')['HOST_LISENCE']['PART'];
        }
        $info['status_des'] = xphp_get_lang($vmDes['VcenterHostAuthDes'][$info['status']]);
        return $info;
    }

    /**
     * 得到云平台授权状态
     * @param array $license 授权信息
     * @param int $used 已用授权个数
     * @param int $subModuleType 类型：2-私有云，3-公有云
     * @return array
     */
    private function getCloudPlatformLicenseStatus(array $license, int $used, int $subModuleType): array
    {
        if (xphp_get_config('auth', 'LISENCE_INFO')['roottype']['other'] == $license['root_type']) {
            // 一级授权为自定义时，授权状态显示为：该平台下已用个数/授权总个数
            return [
                'status' => xphp_get_config('vm')['HOST_LISENCE']['ALL'],
                'status_des' => $used . ' / ' . (2 == $subModuleType ? $license['private_cloud_instance_max_num'] : $license['public_cloud_instance_max_num'])
            ];
        } else {
            // 容量授权时，写死为：无限制
            $status = xphp_get_config('vm')['HOST_LISENCE']['ALL'];
            return [
                'status' => $status,
                'status_des' => xphp_get_lang('UI_SETTINGS_AUTH_UNLIMITED')
            ];
        }
    }

    /**
     * openstack获取平台IP
     * @param string $ip         虚拟化平台IP
     * @param int    $hypervisor 虚拟化类型
     * @param string $username   用户名
     * @return string
     */
    public function getPlatformIp(string $ip, int $hypervisor, string $username): string
    {
        if (in_array($hypervisor, xphp_get_config('vm')['VMHYPERVISORGROUP']['openstack'])) {
            $ip = $ip . '(' . $username . ')';
        }
        return $ip;
    }

    /**
     * 得到版本号
     * @param string $version 版本号
     * @return string
     */
    private function getVersion(string $version): string
    {
        if ($version == null) {
            $version = xphp_get_config('app')['NULLSPACE'];
        }
        return $version;
    }

    /**
     * 统一消息处理结果,流程为:发送消息到SOCKET,接收SOCKET消息,处理接收到的SOCEKT消息,统一接口发送到UI
     * @param int    $submoduleType 虚拟化类型
     * @param string $opName        操作码
     * @param string $jsonMsg       参数json串
     * @return string
     */
    public function unifyMsg(int $submoduleType, string $opName, string $jsonMsg): string
    {
        $nodeUuid = Node::instance()->getMasterNodeUuid();
        $mbResult = $this->service()->unifyVmPlatformService($nodeUuid, $submoduleType, $opName, $jsonMsg);
        $result = $mbResult['result'];
        $operate = VmOpcode::instance()->getOpcodeDes($opName);
        // 修改公有云平台的操作
        if ('VM_VCENTER_OP_MODIFY' == $opName && in_array($submoduleType, xphp_get_config('vm')['VMHYPERVISORGROUP']['publiccloud'])) {
            $operate = xphp_get_lang('UI_CLOUD_PLATFORM_MODIFY_VC');
        }
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 添加虚拟化中心成功后自动刷新另外两种模式
     * @param int    $submoduleType 虚拟化类型
     * @param string $platformIp    虚拟化平台IP
     * @return void
     */
    private function refreshVmPlatformDefault(int $submoduleType, string $platformIp): void
    {
        $sql = "select vcenter_uuid from vm_vcenter where vcenter_ip = ?";
        $data = $this->dbSelect($sql, array($platformIp));
        if (empty($data)) {
            return;
        }
        $platformUuid = $data[0]['vcenter_uuid'];
        $this->refreshVmPlatform(
            $submoduleType,
            $platformUuid,
            xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['HOST_AND_VM'],
            true,
            true
        );
        $this->refreshVmPlatform(
            $submoduleType,
            $platformUuid,
            xphp_get_config('vm')['VM_TREE_DISPLAY_MODE']['VM_AND_TEMPLATE'],
            true,
            true
        );
    }

    /**
     * 刷新虚拟化平台
     * @param int     $submoduleType 虚拟化类型
     * @param string  $platformUuid  虚拟化平台UUID
     * @param int     $displayMode   显示模式
     * @param boolean $command       是否为命令模式,命令模式将直接返回
     * @param boolean $newInstance   是否重新实例化
     * @return array
     */
    public function refreshVmPlatform(
        int $submoduleType,
        string $platformUuid,
        int $displayMode,
        $command = false,
        $newInstance = false
    ): array {
        session_write_close();
        $nodeUuid = Node::instance()->getMasterNodeUuid();
        return $this->service()->refreshVmPlatformService(
            $nodeUuid,
            $submoduleType,
            $platformUuid,
            $displayMode,
            $command,
            $newInstance
        );
    }

    /**
     * 得到是否可以进行授权操作的权限
     * @return boolean
     */
    private function getLicenseHostPermission(): bool
    {
        if (SystemHandler::instance()->getSystemAuthorizationStatus() != xphp_get_config('auth')['LISENCE_INFO']['authflag']['authorized']) {
            //如果系统不是已授权状态
            return false;
        }

        $sql = "select unix_timestamp(register_time) register_time, license_type, node_count,
                desktop_max, days, trial_type, software_type, user_name, extension, cdp_max, cdp_takeover_max_num, cdp_capacity_max, root_type from bd_license ";
        $data = $this->dbSelect($sql);
        $vmLicenseType = SystemHandler::instance()->getAuthVmInfo($data[0])['type'];
        if (
            ($vmLicenseType == xphp_get_config('auth')['LISENCE_INFO']['type']['host'] ||
                $vmLicenseType == xphp_get_config('auth')['LISENCE_INFO']['type']['cpu']) && $data[0]['root_type'] == xphp_get_config('auth', 'LISENCE_INFO')['roottype']['other']
        ) {
            //如果是宿主机授权或CPU授权,才可以进行授权操作
            return true;
        }
        return false;
    }

    /**
     * 得到宿主机状态描述
     * @param int $flag 状态：1在线，2离线
     * @return string
     */
    private function getHostStatusDes(int $flag): string
    {
        $des = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
        if ($flag == xphp_get_config('app')['FLAG']['SET']) {
            $des = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
        }
        return $des;
    }

    /**
     * 得到授权状态描述
     * @param int $flag 状态：1已授权，2未授权
     * @return string
     */
    private function getAuthorizationStatusDes(int $flag): string
    {
        $des = xphp_get_lang('WEB_SYSTEM_UNAUTHIORIZED');
        if ($flag == xphp_get_config('app')['FLAG']['SET']) {
            $des = xphp_get_lang('WEB_SYSTEM_AUTHIORIZED');
        }
        return $des;
    }

    /**
     * 得到快速创建备份任务的树
     * @param array $params
     * @return array
     */
    public function getBackupTreeSpeed(array $params): array
    {
        $showtype = $params['display_mode'];
        $vmuuid = $params['vm_uuid'];
        $vcenteruuid = $params['platform_uuid'];
        $this->paramsCheck($showtype, $vmuuid, $vcenteruuid);
        //获取最顶层虚拟化中心,同备份第一步
        $topNode = $this->getBackupTree($params);
        $sql = "select vcenter_flag, hypervisor_type, user_uuid, vcenter_uuid from vm_vcenter where vcenter_uuid = ?";
        // 获取非分配资源的权限
        if (v2_auth_need_check_look()) {
            $subModule = $this->hypervisorTypeToSubModule($params['hypervisor_type']);
            $resourceUuidSql = v2_auth_get_users($this->getUserAuthKeyBySubModule($subModule));
            $sql .= " and user_uuid in $resourceUuidSql";
        }
        $data = $this->dbSelect($sql, array($vcenteruuid));
        $syncParams = array(
            'platform_uuid' => $vcenteruuid,
            'platform_flag' => $data[0]['vcenter_flag'],
            'hypervisor_type' => $data[0]['hypervisor_type'],
            'open_flag' => true,
            'display_mode' => $showtype,
            'modify_flag' => false,
            'loadall_flag' => true,
            'parent_uuid' => $vcenteruuid
        );
        $vmuuidArr = explode(",", $vmuuid);
        // 租户模式下，虚拟化中心不属于该用户，但属于分配的资源，备份树为三层：hypervisor - 已分配虚拟机 - vm
        if (!empty($_SESSION['tenantuuid']) && xphp_get_user_info()['userUuid'] != $data[0]['user_uuid']) {
            return $this->getTenantAllocatedVmTree($data[0]['hypervisor_type'], $data[0]['vcenter_uuid'], '', $vmuuidArr);
        }
        //获取对应vcenter的子树
        $syncVcenter = $this->getVmPlatformsVmTree($syncParams);
        if ($syncVcenter['rows']) {
            //获取成功
            $msg = $syncVcenter['rows'];
            foreach ($msg as $key => $each) {
                //如果找到对应虚拟机,直接赋值选中
                if (in_array($each['id'], $vmuuidArr)) {
                    $msg[$key]['checked'] = true;
                }
            }
            $childArr = $msg;
        }
        //展开虚拟化中心,如果虚拟化中心就是宿主机,修改虚拟化中心的节点ID为宿主机UUID
        foreach ($topNode as $key => $each) {
            if ($each['id'] == $vcenteruuid) {
                //展开虚拟化中心
                $topNode[$key]['open'] = true;
                if ($each['vcflag'] == xphp_get_config('app', 'FLAG')['UNSET']) {
                    //如果虚拟化中心就是宿主机,修改虚拟化中心的节点ID为宿主机UUID
                    $topNode[$key]['id'] = $this->getVcAndHostUUID($vcenteruuid, $topNode[$key]['hypervisor']);
                    break;
                }
                break;
            }
        }

        $result = [];
        if (!empty($childArr)) {
            $result = array_merge($topNode, $childArr);
        }

        return ['rows' => $result, 'total' => count($result)];
    }

    /**
     * 得到备份树 vcenter层
     * @param array $params
     * @return array
     */
    public function getBackupTree(array $params): array
    {
        $hypervisor = $params['hypervisor_type'];
        $taskuuid = $params['job_uuid'];
        $vcenteruuid = $params['platform_uuid'];
        $this->paramsCheck($hypervisor);

        $tree = array();
        //获取用户已分配的虚拟机
        $subModule = $this->hypervisorTypeToSubModule($hypervisor);

        $sql = "select vv.vcenter_id, vv.vcenter_uuid, vv.vcenter_ip, vv.nickname, vv.vcenter_name, vv.hypervisor_type, 
                vv.vcenter_flag, vv.detail, vv.username, vv.user_uuid, vh.online_flag
                from vm_vcenter vv left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid, vm_host vh
                where vv.hypervisor_type = ?
                and vv.vcenter_uuid = vh.vcenter_uuid ";
        if (empty($_SESSION['tenantuuid'])) {
            // 不能看租户内部的资源
            $sql .= " and mut.tenant_uuid IS NULL ";
        }

        // 获取分配资源的权限-operate
        if (v2_auth_checks()) {
            // not admin or global-operator
            $resourceUuidSql = v2_auth_get_source_by_type($this->getResourceTypeBySubModule($subModule), 'uuid');
            $sqlNew = " EXISTS (select vcenter_uuid from 
                                        (select DISTINCT vcenter_uuid
                                            from vm_tree
                                             where {$resourceUuidSql}
                                        ) v2_auth_all2
                                 where v2_auth_all2.vcenter_uuid = vv.vcenter_uuid) ";
            $sql .= " and {$sqlNew}";
        }

        $sql .= " group by vv.vcenter_uuid ";
        $sqlParams = array($hypervisor);

        $data = $this->dbSelect($sql, $sqlParams);
        $vmModifyData = $this->getTaskVmList($taskuuid);
        $vmModifyList = $vmModifyData['list'];
        $chkDisabled = !$this->isAdmin();

        // 判断系统授权状态，若为异常则不可选
        $sql = "select authorized_flag from bd_system";
        $authFlag = $this->dbSelect($sql)[0]['authorized_flag'];
        if (xphp_get_config('app', 'FLAG')['SET'] != $authFlag) {
            $chkDisabled = true;
        }

        foreach ($data as $d) {
            $checkedFlag = false;
            $checked = false;
            if (in_array($d['vcenter_uuid'], $vmModifyList)) {
                $checked = true;
            }
            if ($d['vcenter_uuid'] == $vcenteruuid) {
                //如果是在同一个虚拟化中心 则可以勾选
                $checkedFlag = true;
            }
            $name = $this->getVcenterNameInTree($d['vcenter_uuid'], $hypervisor, $d['vcenter_flag'], $d['vcenter_ip'], $d['nickname'], $d['vcenter_name'], $d['detail'], $d['username']);
            $sql = "select vol.exclude_vm_uuid_list from vm_object_list vol where vol.object_uuid = ? and vol.task_uuid = ?";
            $exclude_vm_uuid_list = $this->dbSelect($sql, array($d['vcenter_uuid'], $taskuuid))[0]['exclude_vm_uuid_list'];
            $exclude_vm_uuid_list = $exclude_vm_uuid_list ?? '';
            $exclude_vm_uuid_list = explode(',', trim($exclude_vm_uuid_list, ','));

            $node = array(
                "treeId" => $d['vcenter_uuid'] . '_' . $d['vcenter_uuid'],
                "id" => $d['vcenter_uuid'],
                "pid" => $hypervisor,
                "pId" => 0,
                "name" => $name,
                "title" => $name,
                "open" => false,
                "isParent" => true,
                "uuid" => $d['vcenter_uuid'],
                "sid" => $d['vcenter_id'],
                "nocheck" => !$checkedFlag,
                "hypervisor" => $hypervisor,
                "eventtype" => "",
                "vcflag" => $d['vcenter_flag'],
                "iconSkin" => $this->getHypervisorIcon($hypervisor, $d['vcenter_flag']),
                "type" => 1,
                "vcenterFlag" => $d['vcenter_flag'],
                "path" => $name,
                "checked" => $checked,
                "vmChecked" => $exclude_vm_uuid_list ?: '',
                'vcenteruuid' => $d['vcenter_uuid'],
                'nosnapshot' => true,
                'chkDisabled' => $chkDisabled,
                'userUuid' => $d['user_uuid']
            );
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * 验证虚拟化平台分组用户
     * @param array $params
     * @return string
     */
    public function verifyVcenterGroupUser(array $params): string
    {
        $vcenteruuid = $params['platform_uuid'];
        $hypervisor = $params['hypervisor_type'];
        $groupname = $params['group_name'];
        $groupuuid = $params['group_uuid'];
        $username = $params['username'];
        $password = base64_decode($params['password']);
        $this->paramsCheck($vcenteruuid, $groupname, $username, $password);

        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_TEST_OPENSTACK_RECOVERY_USER_CONNECTION';
        //组合消息
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'group_name' => $groupname,
            'group_uuid' => $groupuuid,
            'user_name' => $username,
            'password' => $password
        );

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg));

        $result = $mbResult['result'];
        $operate = (new PfOpcode())->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取虚拟化平台分组
     * @param array $params
     * @return array
     */
    public function getSyncVcenterGroup(array $params): array
    {
        $vcenteruuid = $params['platform_uuid'];
        $hypervisor = intval($params['hypervisor_type']);
        $refresh = boolval($params['refresh_flag']);
        $iconskin = xphp_get_config('vm', 'VIRTUALIZATIONICONCLASSNAME')[$hypervisor];
        $this->paramsCheck($vcenteruuid, $hypervisor);

        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_QUERY_OPENSTACK_USER_GROUP';
        $operate = (new PfOpcode())->getOpcodeDes($opcodeName);
        //组合消息
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
        );

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $userInfo = $this->getVcenterUserAndPassword($vcenteruuid);

        if ($refresh) {
            $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg), true);
            $result = $mbResult['result'];
            if (!$result) {
                return $this->muOpResult(false, $operate, '', '', $mbResult['errorCode']);
            }
            $groupList = $mbResult['msg']['user_group_list'];
        } else {
            $result = true;
            $groupList = array();
            $sql = "select name, uuid from vm_tree where parent_uuid = ? order by name";
            $data = $this->dbSelect($sql, array($vcenteruuid));
            foreach ($data as $d) {
                $groupList[] = array(
                    'group_name' => $d['name'],
                    'group_uuid' => $d['uuid'],
                    'user_list' => array($userInfo['username'])
                );
            }
        }

        $sql = "select mut.tenant_uuid from vm_vcenter vv left join mt_user_tenant mut on vv.user_uuid = mut.user_uuid 
            where vv.vcenter_uuid = ?";
        $vcenterTenantUuid = $this->dbSelect($sql, [$vcenteruuid])[0]['tenant_uuid'];
        $tenantGroups = [];
        //如果是租户内用户操作，检测是否已配置指定分组
        if (!empty($_SESSION['tenantuuid'])) {
            $tenantHandler = Tenant::instance();
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            if ($settings['host_list']) {
                $tenantGroups = $settings['host_list'];
            }
        }

        // 获取分组下的区域
        $groupUuidsStr = "'" . implode("','", array_column($groupList, 'group_uuid')) . "'";
        $sqlRegion = "select name, uuid, parent_uuid from vm_tree where vcenter_uuid = ? and parent_uuid in ($groupUuidsStr) and type = ?";
        $dataRegion = $this->dbSelect($sqlRegion, [$vcenteruuid, xphp_get_config('vm', 'VM_TREE_TYPE')['CLUSTER']]);

        $userList = array();
        foreach ($groupList as $group) {
            if (!empty($_SESSION['tenantuuid'])) {
                // 获取已分配的和租户自有的
                if (!in_array($group['group_uuid'], $tenantGroups) && $_SESSION['tenantuuid'] != $vcenterTenantUuid)
                    continue;
            }
            $username = '';
            $password = '';
            if (in_array($userInfo['username'], $group['user_list'])) {
                //如果添加虚拟化中心的用户,在某个分组下面,用户名密码就用这个用户的,如果没有,就需要用户输入
                $username = $userInfo['username'];
                $password = $userInfo['password'];
            }
            $userList[] = array(
                "type" => 2,
                "isParent" => true,
                "vcuuid" => $vcenteruuid,
                "pid" => $vcenteruuid,
                "pId" => $vcenteruuid,
                "hypervisor" => $hypervisor,
                "hypervisor_des" => xphp_get_config('vm')['VMHYPERVISORDES'][$hypervisor],
                "name" => $group['group_name'],
                "groupuuid" => $group['group_uuid'],
                "groupusers" => $group['user_list'],
                "username" => $username,
                "password" => $password,
                "iconSkin" => $iconskin . "_folder_vm",
                //                 "icon" => "./img/vm/host.png",
                "id" => $group['group_uuid'],
                "nocheck" => true,
            );

            // 添加分组下的区域
            foreach ($dataRegion as $region) {
                if ($region['parent_uuid'] != $group['group_uuid']) {
                    continue;
                }
                $userList[] = array(
                    "type" => 3,
                    "isParent" => false,
                    "vcuuid" => $vcenteruuid,
                    "pid" => $group['group_uuid'],
                    "pId" => $group['group_uuid'],
                    "hypervisor" => $hypervisor,
                    "hypervisor_des" => xphp_get_config('vm')['VMHYPERVISORDES'][$hypervisor],
                    "name" => $region['name'],
                    "groupuuid" => $group['group_uuid'],
                    "groupname" => $group['group_name'],
                    "groupusers" => $group['user_list'],
                    "username" => $username,
                    "password" => $password,
                    "iconSkin" => $iconskin . "_hostcluster",
                    "id" => $region['uuid'],
                );
            }
        }

        return [
            're' => $result,
            'op' => $operate,
            'data' => v2_array_sort($userList, 'name', 'asc', 0, -1)
        ];
    }

    /**
     * 测试Openstack控制IP连接
     * @param array $params
     * @return string
     */
    public function testControllerIP(array $params): string
    {
        $controllerIP = $params['controller_ip'];
        $hypervisor = $params['hypervisor_type'];
        $this->paramsCheck($controllerIP, $hypervisor);

        //测试连接操作码
        $opcodeName = 'VM_VCENTER_OP_TEST_CONTROLLER_IP';
        //组合消息
        $msg = array(
            'controller_ip' => $controllerIP,
        );

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg));
        $result = $mbResult['result'];
        $operate = VmOpcode::instance()->getOpcodeDes($opcodeName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取虚拟化平台支持的配置
     * @param array $params
     * @return string|array
     */
    public function getSupportInfo(array $params)
    {
        $vcenteruuid = $params['platform_uuid'];
        $hostuuid = $params['host_uuid'];
        $region = $params['region'] ?? '';
        $hypervisor = intval($params['hypervisor_type']);
        $timepointOsVersion = intval($params['os_version']);
        $this->paramsCheck($vcenteruuid);

        //定义操作名
        $opcodeName = 'VM_VCENTER_OP_QUERY_VCENTER_SUPPORT_INFO';
        //组合消息
        $msg = array(
            'vcenter_uuid' => $vcenteruuid,
            'host_uuid' => $hostuuid,
            'region' => $region,
            'os_version' => $timepointOsVersion,
        );

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();

        $mbResult = $this->mbVMMsg($nodeuuid, $hypervisor, $opcodeName, json_encode($msg), true);
        $msg = $mbResult['msg'];

        // 从配置组合名称
        $finalCpuList = [];
        $cpuModeListDes = xphp_get_config('vm_config', 'BdMiddleCpuModeTypeDes');
        $cpuDes = xphp_get_config('vm_config', 'BdMiddleCpuArchDes');
        $archArr = []; // 记录已添加的架构
        if (!empty($msg['cpu_info_list'])) {

            foreach ($msg['cpu_info_list'] as $v) {
                if ($v['arch_type'] == 0)
                    continue;
                $archArr[] = $v['arch_type'];
                $cpuModeList = [];
                foreach ($v['cpu_mode_list'] as $cpuMode) {
                    $cpuModeList[] = [
                        'value' => $cpuMode,
                        'name' => $cpuModeListDes[intval($cpuMode)]
                    ];
                }
                $finalCpuList[] = [
                    'arch_type' => $v['arch_type'],
                    'arch_name' => $cpuDes[$v['arch_type']],
                    'cpu_mode_list' => $cpuModeList,
                ];
            }
        }
        $msg['cpu_info_list'] = $finalCpuList;

        $osDes = xphp_get_config('vm_config', 'BdMiddleOsTypeV2Des');
        $finalOsList = [];
        $archArr = []; // 记录已添加的架构
        foreach ($msg['os_info_list'] as &$archItem) {
            $archItem['os_type_list'] = [];
            $arr = []; // 记录已添加的操作系统类型
            // 每个cpu架构支持的操作系统
            foreach ($archItem['os_list'] as &$v) {
                if (0 == $v['os_type'] || 0 == $v['os_version']) {
                    // 排除无效数据
                    unset($v);
                    continue;
                }
                if (!in_array($v['os_type'], $arr)) {
                    $arr[] = $v['os_type'];
                    $archItem['os_type_list'][] = [
                        'os_value' => $v['os_type'],
                        'os_name' => $osDes[$v['os_type']],
                    ];
                }
            }
            // 没添加架构则新增，有了则合并os_type_list和os_list
            if (!in_array($archItem['arch_type'], $archArr)) {
                $archItem['os_list'] = v2_array_sort($archItem['os_list'], 'os_description', 'asc', 0, -1);
                $finalOsList[] = $archItem;
                $archArr[] = $archItem['arch_type'];
            } else {
                foreach ($finalOsList as &$finalOsItem) {
                    if ($finalOsItem['arch_type'] == $archItem['arch_type']) {
                        $finalOsItem['os_type_list'] = array_unique(array_merge($finalOsItem['os_type_list'], $archItem['os_type_list']), SORT_REGULAR);
                        $osList = array_merge($finalOsItem['os_list'], $archItem['os_list']);
                        $finalOsItem['os_list'] = v2_array_sort($osList, 'os_description', 'asc', 0, -1);
                    }
                }
            }
        }
        $msg['os_info_list'] = $finalOsList;
        return $msg;
    }

    /**
     * 获取zstack用户角色列表
     * @param array $params
     * @return array|string
     */
    public function getUserRoleList(array $params)
    {
        $opcodeName = 'VM_VCENTER_OP_QUERY_USER_ROLE_LIST';
        $operate = VmOpcode::instance()->getOpcodeDes($opcodeName);
        $password = $params['password'];
        // 修改时获取
        if ($params['platform_uuid']) {
            $sql = "select password from vm_vcenter where vcenter_uuid = ?";
            $data = $this->dbSelect($sql, array($params['platform_uuid']));
            //检查用户是否修改了密码,如果修改了用新的密码,如果没有修改用原来的密码(数据库里面的)
            $oldPass = base64_encode(md5($data[0]['password']));
            if ($oldPass == $password) {
                //没有修改密码
                $password = base64_encode(v2_pt_pass_decrypt($data[0]['password']));
            }
        }
        //组合消息
        $msg = array(
            'hypervisor_type' => $params['hypervisor_type'],
            'ip' => $params['ip'],
            'username' => $params['username'],
            'password' => $password,
            'detail' => json_encode(['account_type' => 'tenant'])
        );

        $nodeHandler = Node::instance();
        $nodeuuid = $nodeHandler->getLocalNodeUUID();
        $mbResult = $this->mbVMMsg($nodeuuid, $params['hypervisor_type'], $opcodeName, json_encode($msg), true);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        if ($mbResult['errorCode']) {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
        //返回结果到UI
        return $msg['user_role_list'] ?? [];
    }

    /**
     * 得到虚拟化中心的名字
     * 主要是区别XenServer和VMware
     * 以及有别名和没有别名
     * @param string $vcenteruuid
     * @param int $hypervisor
     * @param int $vcenterflag
     * @param string $vcenterip
     * @param string $nickname
     * @param string $vcentetname
     * @param string $detail
     * @param string $username
     * @return string
     */
    public function getVcenterNameInTree(
        string $vcenteruuid,
        int $hypervisor,
        int $vcenterflag,
        string $vcenterip,
        string $nickname,
        string $vcentetname,
        string $detail,
        string $username
    ): string {
        $detail = json_decode($detail, true);
        if (in_array(intval($hypervisor), xphp_get_config('vm', 'VMHYPERVISORGROUP')['xenserver'])) {
            //如果是XenServer,显示IP+(主节点IP)
            $name = $vcentetname . " (" . xphp_get_lang('WEB_VM_VCENTER_XENSERVER_MASTER_NODE') . ":" . $vcenterip . ")";
        } else if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['openstack'])) {
            $name = $vcentetname . '(' . $username . ')';
        } else if (
            xphp_get_config('vm', 'VMHYPERVISORGROUP')['VM_HYPERVISOR_TYPE_HYPERV'] == $hypervisor
            && xphp_get_config('app', 'FLAG')['UNSET'] == $vcenterflag
        ) {
            //hyperv单机
//            $name = $vcenterip . "(" . $vcentetname . ")";
            $name = $nickname . "(" . $vcenterip . ")";
        } else {
            $name = $vcenterip == $nickname ? $vcenterip : $nickname . "(" . $vcenterip . ")";
        }
        return $this->getHostIsLisenced($vcenterflag, $vcenteruuid, $name, $hypervisor);
    }

    /**
     * 得到宿主机是否是授权状态,没有授权需要添加未授权标志
     * @param int $vcenterflag
     * @param string $vcenteruuid
     * @param string $name
     */
    private function getHostIsLisenced($vcenterflag, $vcenteruuid, $name, $hypervisor)
    {
        if ($vcenterflag == xphp_get_config('app', 'FLAG')['SET']) {
            return $name;
        }
        if ($hypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_HYPERV']) {
            return $name;
        }
        //如果是宿主机,$vcenteruuid 其实就是hostuuid
        $sql = "select authorization_flag from vm_host where host_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        if ($data[0]['authorization_flag'] != xphp_get_config('app', 'FLAG')['SET']) {
            $name = "(" . xphp_get_lang('WEB_SYSTEM_UNAUTHIORIZED') . ")" . $name;
        }
        return $name;
    }

    /**
     * 获取租户已分配的虚拟机树
     * @param int $hypervisorType
     * @param string $vcenterUuid
     * @param string $taskUuid
     * @param array $vmUuidArr 选中的虚拟机uuid，从概览添加使用
     * @return array
     */
    private function getTenantAllocatedVmTree(int $hypervisorType, string $vcenterUuid, string $taskUuid = '', $vmUuidArr = []): array
    {
        // hypervisor层
        $hypervisorLevNode = $this->getHypervisorLevTree($hypervisorType, false);
        $hypervisorLevNode['open'] = true; // 展开
        $result[] = $hypervisorLevNode;
        // 已分配虚拟机
        $name = in_array($hypervisorType, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])
            ? xphp_get_lang('WEB_VM_TREE_ALLOCATED_INSTANCE') : xphp_get_lang('WEB_VM_TREE_ALLOCATED_VM');
        $allocatedVmNode = [
            "checked" => false,
            "chkDisabled" => true,
            "eventtype" => '',
            "hypervisor" => $hypervisorType,
            "iconSkin" => $this->getHypervisorIcon($hypervisorType, 1),
            "id" => $hypervisorType . '_allocated_vm', // 特殊处理
            "isParent" => true,
            "name" => html_entity_decode($name),
            "nocheck" => true,
            "nosnapshot" => true,
            "open" => true,
            "pid" => $hypervisorType,
            "path" => $name,
            "sid" => 0, //vcenter_id
            "title" => $name,
            "type" => 1,
            "uuid" => '',
            "vcenterFlag" => false,
            "vcenteruuid" => $vcenterUuid,
            "vcflag" => false,
            "vmChecked" => [""]
        ];
        $result[] = $allocatedVmNode;
        // vm层
        $vmData = $this->getTreeFromVMTree(
            $hypervisorType,
            false,
            $allocatedVmNode['id'],
            1,
            true,
            true,
            true,
            true,
            $taskUuid,
            true
        );
        $data = $vmData['rows'];
        // 处理选中
        foreach ($data as &$d) {
            if (in_array($d['id'], $vmUuidArr)) {
                $d['checked'] = true;
            }
        }
        return array_merge($result, $data);
    }

    /**
     * 得到直接作为vcenter的宿主机的mofer uuid
     * @param string $vcenteruuid
     * @param int $hypervisor
     */
    private function getVcAndHostUUID($vcenteruuid, $hypervisor)
    {
        if (in_array(intval($hypervisor), xphp_get_config('vm', 'VMHYPERVISORGROUP')['vmware'])) {
            $sql = "select uuid from vm_tree where parent_uuid = ?";
            $data = $this->dbSelect($sql, array($vcenteruuid));
            if ($data) {
                $uuid = $data[0]['uuid'];
            }
            return $uuid;
        } else {
            return $vcenteruuid;
        }
    }

    /**
     * 获取对象下不在任务中的虚拟机
     * @param $display_mode
     * @param $vcenter_uuid
     * @param $object_uuid
     * @param $task_uuid
     * @return array
     */
    public function getObjectVmsNotInTask($display_mode, $vcenter_uuid, $object_uuid, $task_uuid): array
    {
        $vms = [];
        $sql = "select vt.name, vt.vcenter_uuid, vt.uuid, vt.type, vt.dir_path  
            from vm_tree vt where vt.display_mode = ? and vt.vcenter_uuid = ? and vt.parent_uuid = ? and vt.uuid != vt.parent_uuid 
            and vt.uuid not in (select vm_uuid from vm_machine_list where task_uuid = ?)";
        $data = $this->dbSelect($sql, [$display_mode, $vcenter_uuid, $object_uuid, $task_uuid]);
        foreach ($data as $item) {
            if (xphp_get_config('vm', 'VM_TREE_TYPE')['VM'] == $item['type']) {
                $vms[] = $item;
            } else {
                $vms = array_merge($vms, $this->getObjectVmsNotInTask($display_mode, $item['vcenter_uuid'], $item['uuid'], $task_uuid));
            }
        }
        return $vms;
    }

    /**
     * 得到修改备份任务树
     * @param array $params
     * @return array
     */
    public function getBackupTreeOldInfo(array $params): array
    {
        //获取最顶层虚拟化中心,同备份第一步
        $topNode = $this->getBackupTree($params);
        //获取hypervisor层
        $hypervisorLevNode = $this->getHypervisorLevTree($params['hypervisor_type'], 1, true);
        //获取该任务备份对象
        $sql = "select vv.user_uuid, vol.object_uuid, vol.type, vol.vcenter_uuid, vv.vcenter_flag, vol.vm_config, vol.exclude_vm_uuid_list 
            from vm_vcenter vv, vm_object_list vol where vol.vcenter_uuid = vv.vcenter_uuid and vol.task_uuid = ?";
        // 获取分配资源的权限
        if (v2_auth_checks()) {
            $subModule = $this->hypervisorTypeToSubModule($params['hypervisor_type']);
            $resourceUuidSql = v2_auth_get_source_by_type($this->getResourceTypeBySubModule($subModule), 'vol.object_uuid');
            $treeTypeVM = xphp_get_config('vm', 'VM_TREE_TYPE')['VM'];
            $sql .= " and if (vol.type = $treeTypeVM, $resourceUuidSql, true)";
        }
        $data = $this->dbSelect($sql, array($params['job_uuid']));
        if (empty($data)) {
            //获取该任务所备份的虚拟机
            $sql = "select vv.user_uuid, vml.vm_name, vml.vm_uuid as object_uuid, 7 as type, vml.vcenter_uuid, vv.vcenter_flag , vml.vm_config, '' as exclude_vm_uuid_list 
                from vm_machine_list vml, vm_vcenter vv  where vml.vcenter_uuid = vv.vcenter_uuid and vml.task_uuid = ?";
            // 获取分配资源的权限
            if (v2_auth_need_check_look()) {
                $subModule = $this->hypervisorTypeToSubModule($params['hypervisor_type']);
                $resourceUuidSql = v2_auth_get_source_by_type($this->getResourceTypeBySubModule($subModule), 'vml.vm_uuid');
                $sql .= " and $resourceUuidSql";
            }
            $data = $this->dbSelect($sql, array($params['job_uuid']));
        }
        $vcenterUUID = null;
        if (!empty($data)) {
            // 租户模式下，虚拟化中心不属于该用户，但属于分配的资源，备份树为三层：hypervisor - 已分配虚拟机 - vm
            if (!empty($_SESSION['tenantuuid']) && xphp_get_user_info()['userUuid'] != $data[0]['user_uuid']) {
                $hypervisorType = $params['hypervisor_type'];
                return $this->getTenantAllocatedVmTree($hypervisorType, $data[0]['vcenter_uuid'], $params['job_uuid']);
            }
            //备份任务限制只有在一个虚拟化中心下,所以虚拟化中心只有一个
            $vcenterUUID = $data[0]['vcenter_uuid'];
            $showType = $params['display_mode'] ?: xphp_get_config('vm', 'VM_TREE_DISPLAY_MODE')['HOST_AND_CLUSTER'];
            $syncParams = array(
                'platform_uuid' => $vcenterUUID,
                'platform_flag' => $data[0]['vcenter_flag'],
                'hypervisor_type' => $params['hypervisor_type'],
                'open_flag' => true,
                'display_mode' => $showType,
                'modify_flag' => true,
                'job_uuid' => $params['job_uuid'],
                'loadall_flag' => false,
                'aws_flag' => in_array($params['hypervisor_type'], xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud']), // 是否公有云
            );
            //得到所有备份对象数组
            $vmuuidArr = array();
            $diskVMList = array();
            $exVmList = array();
            foreach ($data as $d) {
                if (empty($d['vm_config']))
                    continue;
                $vmuuidArr[] = $d['object_uuid'];
                $diskVMList[] = array(
                    'id' => $d['object_uuid'],
                    'vm_config' => json_decode($d['vm_config']),
                );
            }

            foreach ($data as $d) {
                //获取创建任务后对象下新增的虚拟机，未开启自动备份则要排除，已开启自动备份无需处理会自动勾选
                $notInTaskVmUuidList = [];
                if (xphp_get_config('vm', 'VM_TREE_TYPE')['VM'] != $d['type'] && !$params['auto_join_flag']) {
                    $notInTaskVms = $this->getObjectVmsNotInTask($params['display_mode'], $d['vcenter_uuid'], $d['object_uuid'], $params['job_uuid']);
                    if ($notInTaskVms) {
                        $notInTaskVmUuidList = array_column($notInTaskVms, 'uuid');
                    }
                }
                //if(empty($d['exclude_vm_uuid_list'])) continue;
                $vmuuidList = explode(',', trim($d['exclude_vm_uuid_list'], ','));
                $vmuuidArr[] = $d['object_uuid'];
                $exVmList[] = array(
                    'id' => $d['object_uuid'],
                    'exclude_vm_uuid_list' => array_values(array_unique(array_merge($vmuuidList, $notInTaskVmUuidList))),
                );
            }

            //获取对应vcenter的子树
            $syncVcenter = $this->getVmPlatformsVmTree($syncParams);
            $msg = $syncVcenter['rows'];
            foreach ($msg as $key => $each) {
                //如果找到对应虚拟机,直接赋值选中
                foreach ($diskVMList as $disk) {
                    $diskVMuuid = array($disk['id']);
                    if (in_array($each['id'], $diskVMuuid)) {
                        $msg[$key]['checked'] = true;
                        $msg[$key]['chkDisabled'] = false;
                        $msg[$key]['diskChecked'] = $disk['vm_config'];
                    }
                }

                //如果找到对应对象,直接赋值选中
                foreach ($exVmList as $vm) {
                    $diskVMuuid = array($vm['id']);
                    if (in_array($each['id'], $diskVMuuid)) {
                        $msg[$key]['checked'] = true;
                        $msg[$key]['chkDisabled'] = false;
                        $msg[$key]['vmChecked'] = $vm['exclude_vm_uuid_list'];
                    }
                }
            }
            $childArr = $msg;
        }
        //展开虚拟化中心,如果虚拟化中心就是宿主机,修改虚拟化中心的节点ID为宿主机UUID
        foreach ($topNode as $key => $each) {
            if (!empty($_SESSION['tenantuuid']) && xphp_get_user_info()['userUuid'] != $each['userUuid']) {
                // 不是租户添加的不显示
                unset($topNode[$key]);
                continue;
            }
            if ($each['id'] == $vcenterUUID) {
                //展开虚拟化中心
                $topNode[$key]['open'] = true;
                if ($each['vcflag'] == xphp_get_config('app', 'FLAG')['UNSET']) {
                    //如果虚拟化中心就是宿主机,修改虚拟化中心的节点ID为宿主机UUID
                    $topNode[$key]['id'] = $this->getVcAndHostUUID($vcenterUUID, $topNode[$key]['hypervisor']);
                }
            }
        }
        $result = array_merge([$hypervisorLevNode], $topNode);
        if (!empty($childArr)) {
            $result = array_merge($result, $childArr);
        }
        return ['rows' => $result, 'total' => count($result)];
    }

    /**
     * 得到vcenter添加的时候的用户名和密码
     * @param string $vcenteruuid
     * @return array
     */
    private function getVcenterUserAndPassword(string $vcenteruuid): array
    {
        $this->paramsCheck($vcenteruuid);
        $sql = "select username, password from vm_vcenter where vcenter_uuid = ?";
        $data = $this->dbSelect($sql, array($vcenteruuid));
        return array(
            "username" => $data[0]['username'],
            "password" => $data[0]['password']
        );
    }

    /**
     * 得到vcenter下所有宿主机授权的状态
     * @param string $vcenteruuid
     * @return array
     */
    public function getVcenterHostLisenceStatus(string $vcenteruuid): array
    {
        $sql = "select authorization_flag from vm_host where vcenter_uuid = ?";
        $data = (array) $this->dbSelect($sql, array($vcenteruuid));
        $countHost = count($data);
        $authHost = 0;
        $unAuthHost = 0;
        foreach ($data as $d) {
            if (intval($d['authorization_flag']) == xphp_get_config('app', 'FLAG')['SET']) {
                $authHost++;
            } else {
                $unAuthHost++;
            }
        }
        $vmDes = include APP_PATH . 'v2/description/Vm.php';
        $info = array();
        if ($countHost == $authHost) {
            //全部授权
            $info['status'] = xphp_get_config('vm', 'HOST_LISENCE')['ALL'];
        } elseif ($countHost == $unAuthHost) {
            //未授权
            $info['status'] = xphp_get_config('vm', 'HOST_LISENCE')['NONE'];
        } else {
            //部分授权
            $info['status'] = xphp_get_config('vm', 'HOST_LISENCE')['PART'];
        }
        $info['statusDes'] = $vmDes['VcenterHostAuthDes'][$info['status']];
        return $info;
    }

    /**
     * 获取子模块类型对应虚拟机或实例的资源类型
     * @param int $subModuleType
     * @return int
     */
    public function getResourceTypeBySubModule(int $subModuleType): int
    {
        $subModuleConfig = xphp_get_config('vm', 'VM_SUB_MODULE');
        $resourceTypeConfig = xphp_get_config('resource', 'RESOURCE_TYPE');
        switch ($subModuleType) {
            case $subModuleConfig['VM']:
                return $resourceTypeConfig['VM'];
            case $subModuleConfig['PRIVATE_CLOUD']:
                return $resourceTypeConfig['PRIVATE_CLOUD'];
            case $subModuleConfig['PUBLIC_CLOUD']:
                return $resourceTypeConfig['PUBLIC_CLOUD'];
            default:
                return $resourceTypeConfig['UNKNOWN'];
        }
    }

    /**
     * 获取子模块类型对应的用户权限类型
     * @param int $subModuleType
     * @return string
     */
    public function getUserAuthKeyBySubModule(int $subModuleType): string
    {
        $subModuleConfig = xphp_get_config('vm', 'VM_SUB_MODULE');
        $userAuthConfig = xphp_get_config('user', 'USER_AUTH');
        switch ($subModuleType) {
            case $subModuleConfig['VM']:
                return $userAuthConfig['vm'];
            case $subModuleConfig['PRIVATE_CLOUD']:
                return $userAuthConfig['cloud'];
            case $subModuleConfig['PUBLIC_CLOUD']:
                return $userAuthConfig['aws'];
            default:
                return '';
        }
    }

    /**
     * 虚拟化类型转子模块类型
     * @param $hypervisor
     * @return int
     */
    public function hypervisorTypeToSubModule($hypervisor): int
    {
        $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['VM'];
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['privatecloud'])) {
            $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PRIVATE_CLOUD'];
        }
        if (in_array($hypervisor, xphp_get_config('vm', 'VMHYPERVISORGROUP')['publiccloud'])) {
            $subModule = xphp_get_config('vm', 'VM_SUB_MODULE')['PUBLIC_CLOUD'];
        }
        return $subModule;
    }
}
