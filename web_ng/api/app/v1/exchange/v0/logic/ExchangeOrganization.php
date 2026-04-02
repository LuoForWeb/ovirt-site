<?php

namespace app\v1\exchange\v0\logic;

use app\v1\common\logic\Base;
use app\v1\resources\v0\logic\Index;
use phpseclib3\File\ASN1\Element;

/**
 * note          office365（exchange） 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeOrganization extends Base
{
    /**
     * 获取组织信息
     * @param array $params 参数
     * @return array 组织相关信息
     */
    public function getOrganizationInfo($params = [])
    {
        $sql = "select mo.organization_uuid,mo.agent_uuid_list,mo.organization_name,mo.region,mo.auth_apps,mo.refresh_time,mo.refresh_status,
        mo.online_flag,mo.error_code,mo.create_time,maaa.app_secret,maaa.tenant_uuid,maaa.app_uuid,maaa.app_cert_info,maaa.app_name,mo.user_uuid,mo.skip_cert_auth_flag,
       mo.nickname, maaa.username,bu.user_name from m365_organization mo left join bd_user bu on bu.user_uuid = mo.user_uuid left join m365_azure_ad_app maaa on mo.organization_uuid 
        = maaa.organization_uuid ";
        $sqlcount = "select count(mo.organization_uuid) as total from m365_organization mo";
        //获取当前用户拥有的组织
        $concatSql = ' where ';
        if(v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['M365_EXCHANGE'], 'mo.organization_uuid');
            $sql .= $concatSql . " ({$resourceUuidSql})";
            $sqlcount .= $concatSql . " ({$resourceUuidSql})";
            $concatSql = ' and ';
        } else if (empty($user['tenantuuid'])) {// 非租户用户不能查看租户的资源
            $sql .= " LEFT JOIN mt_user_tenant mut on mo.user_uuid = mut.user_uuid WHERE mut.tenant_uuid IS NULL";
            $sqlcount .= " LEFT JOIN mt_user_tenant mut on mo.user_uuid = mut.user_uuid WHERE mut.tenant_uuid IS NULL";
            $concatSql = ' and ';
        }
          
        $sqlparams = array(
            $params['offset'],
            $params['limit']
        );
        if (!empty($params['search'])) {
            $sql .= $concatSql . " mo.organization_name like '%" . $params['search'] . "%' or mo.nickname like '%" . $params['search'] . "%'";
            $sqlcount .= $concatSql . " organization_name like '%" . $params['search'] . "%' or nickname like '%" . $params['search'] . "%'";
        }
        //排序
        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc']) ? 'desc' : strtolower($params['order']);
            $sortArr = [
                'organization_name' => 'mo.organization_name',
                'nickname' => 'mo.nickname',
                'agent_list' => 'mo.agent_uuid_list',
                'app_auth' => 'mo.auth_apps',
                'create_time' => 'mo.create_time',
                'type' => 'mo.region',
                'online_flag' => 'mo.online_flag',
                'sync_time' => 'mo.refresh_time',
                'refresh_status' => 'mo.refresh_status',
            ];
            $sort = empty($sortArr[$params['sort']]) ? ' mo.id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', mo.id desc');
            $sql .= " order by " . $sort;
        }
        $sql .= ' limit ?, ?';
        $result = $this->dbSelect($sql, $sqlparams);
        $count = $this->dbSelect($sqlcount);
        $info = array(
            'rows' => array(),
            'total' => $count[0]['total'],
        );

        if (!empty($result)) {
            foreach ($result as $each) {
                //认证方式:app_secret有值就是密码认证
                $verifyway = xphp_get_lang('WEB_M365_CERTIFICATE_METHOD');
                $verifyvalue = 2;
                if (!empty($each['app_secret'])) {
                    $verifyway = xphp_get_lang('WEB_M365_PASSWORD_METHOD');
                    $verifyvalue = 1;
                }
                //证书信息
                $appcertinfo = json_decode($each['app_cert_info'], true);
                // 拥有者
                $owner = $this->getOganizationUuidName($each['organization_uuid'], $each['user_name'] ?? '--');
                $arr = explode(',', $owner);
                if ($user['isThreePowers']) {
                    // 三权模式下，admin显示为sysadmin
                    foreach ($arr as $index => $name) {
                        if ($name == 'admin') {
                            $arr[$index] = 'sysadmin';
                        }
                    }
                    $owner = implode(',', $arr);
                }
                // 创建者
                $creator = $each['user_name'] ?? '--';
                if ('admin' == $creator && $user['isThreePowers']) {
                    // 三权模式下，admin显示为sysadmin
                    $creator = 'sysadmin';
                }
                //租户内判断创建者等不等于当前用户,等于当前用户才能操作
                $opFlag = true;
                if ($user['tenantuuid'] && $each['user_uuid'] != $user['userUuid']) {
                    $opFlag = false;
                }
                $info['rows'][] = array(
                    'organization_uuid' => $each['organization_uuid'],
                    'agent_list' => $this->getAgentIp(json_decode($each['agent_uuid_list'], true)),
                    'organization_name' => $each['organization_name'],
                    'region' => $each['region'],//组织区域
                    'online_flag' => $each['online_flag'],//在线标志
                    'error_code' => $each['error_code'],
                    'create_time' => $each['create_time'],
                    'app_auth' => json_decode($each['auth_apps'], true),//已授权的应用
                    'verify_way' => $verifyway,//认证方式
                    'type' => $each['region'] == 100 ? 'Microsoft 365 on-premises' : 'Microsoft 365',//类型
                    'type_value' => $each['region'] == 100 ? 2 : 1,//类型的value:2 server  1 online
                    'verify_value' => $verifyvalue,//认证方式的value  2密码  1证书
                    'tenant_uuid' => $each['tenant_uuid'],
                    'app_name' => $each['app_name'],
                    'app_uuid' => $each['app_uuid'],
                    'username' => $each['username'],
                    'app_secret' => $each['app_secret'],
                    'agentConnect_value' => json_decode($each['agent_uuid_list'], true),
                    'server_info' => $this->getExchangeServerInfo(json_decode($each['agent_uuid_list'], true)[0]),
                    'cert_name' => $appcertinfo == null ? '' : $appcertinfo['cert_name'],
                    'cert_password' => $appcertinfo == null ? '' : $appcertinfo['cert_password'],
                    'cert_content' => $appcertinfo == null ? '' : $appcertinfo['cert_content'],
                    'chkDisabled' => $each['online_flag'] == 2 ? true : false,
                    'refresh_status' => $each['refresh_status'],
                    'sync_time' => empty($each['refresh_time']) ? '--' : $each['refresh_time'],
                    'nickname' => empty($each['nickname']) ? '--' : $each['nickname'],
                    'creator' => $creator,//创建者
                    'owner' => $owner,//拥有者
                    'op_flag' => $opFlag,//当前用户是否可以操作该资源
                    'skip_cert_auth_flag' => v1_parse_flag_to_bool($each['skip_cert_auth_flag']),
                );
            }
        }
        return $info;
    }

    /**
     * 根据agent_uuid获取本地组织信息
     * @param string $agentuuid agentuuid
     * @return array $info
     */
    private function getExchangeServerInfo($agentuuid)
    {
        $info = array();
        $sql = 'select app_username, app_password, app_detail from bd_agent_app where agent_uuid = ?';
        $data = $this->dbSelect($sql, array($agentuuid));
        if (!empty($data)) {
            $info[] = array(
                'app_username' => $data[0]['app_username'],
                'app_password' => $data[0]['app_password'],
                'app_detail' => $data[0]['app_detail'],
            );
        }
        return $info;
    }

    /**
     * 不同应用显示不同的图标
     * @param array $authList 授权情况
     * @return sting 图标
     */
    private function getAppIcon($authList)
    {
        $icon = '';
        if ($authList['is_exch_auth'] == '1') {
            $icon .= '<i class="viconfont vicon-module-exchange c0FBF98"></i>';
        }
        if ($authList['is_onedrive_auth'] == '1') {
            $icon .= '<i class="viconfont vicon-module-exchange c0FBF98"></i>';
        }
        if ($authList['is_sharepoint_auth'] == '1') {
            $icon .= '<i class="viconfont vicon-module-exchange c0FBF98"></i>';
        }
        if ($authList['is_teams_auth'] == '1') {
            $icon .= '<i class="viconfont vicon-module-exchange c0FBF98"></i>';
        }
        if (empty($icon)) {
            $icon = '--';
        }
        return $icon;
    }

    /**
     * 获取客户端名/ip
     * @param array $agentList 客户端uuid
     * @return sting 客户端信息
     */
    private function getAgentIp($agentList)
    {
        $sql = "select agent_uuid,agent_name,hostname,ip from bd_agent";
        $allagent = $this->dbSelect($sql);
        $info = '--';
        foreach ($agentList as $item) {//关联的客户端列表
            foreach ($allagent as $eachAgent) {//所有客户端列表信息
                if ($item == $eachAgent['agent_uuid'] && !empty($eachAgent['hostname'])) {
                    $info .= $eachAgent['hostname'] . '(' . $eachAgent['ip'] . ')';
                } elseif ($item == $eachAgent['agent_uuid'] && empty($eachAgent['hostname'])) {
                    $info .= $eachAgent['agent_name'] . '(' . $eachAgent['ip'] . ')';
                }
            }
        }
        return $info;
    }

    /**
     * 添加组织
     * @param array  $params 参数
     * @param number $type   添加方式
     * @return unknown
     */
    public function addOrganization($params, $type, $addopid)
    {
        $info = array();
        if (empty($addopid)) {//add_op_id为空就去获取
            //获取本地nodeuuid
            $nodeuuid = $this->getLocalNodeUUID();
            $result = $this->service()->addOrganization($nodeuuid, $params, $type);
            $info = array(
                'add_op_id' => $result,
                'op_status' => 1,//运行中
            );
        } else {
            $sql = "select op_status, op_error_code, max_wait_time, detail from bd_operation where op_id = ?";
            $data = $this->dbSelect($sql, array($addopid));
            if ($data[0]['op_status'] == 3) {//报错
                $error = include '/usr/share/nginx/vinchin/web_ng/api/app/v1/config/error.php';
                $des = xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ': #' . $data[0]['op_error_code'] . ',';
                $des .= xphp_get_lang('WEB_OPHANDLER_ERROR_DES') . ': ' . xphp_get_lang($error['errorCodeDes'][$error['errorCode'][$data[0]['op_error_code']]]);
                $info = array(
                    'op_status' => 3,
                    'des' => $des,
                );
            } else {
                $info = array(
                    'op_status' => $data[0]['op_status'],
                    'add_op_id' => $addopid,
                );
            }
        }
        return $info;
    }

    /**
     * 修改组织
     * @param array  $params 参数
     * @param number $type   添加方式
     * @return unknown
     */
    public function editOrganization(array $params, $type)
    {
        //操作权限判断
        $this->checkAuthBySourceUuid($params['organization_uuid'], xphp_get_config('resource','RESOURCE_TYPE')['M365_EXCHANGE']);
        //获取本地nodeuuid
        $nodeuuid = $this->getLocalNodeUUID();
        $result = $this->service()->editOrganization($nodeuuid, $params, $type);
        return $result;
    }

    /**
     * 修改组织别名
     * @param string $nickName         别名
     * @param string $organizationUuid 组织uuid
     * @return unknown
     */
    public function editOrganizationNickname($nickName, $organizationUuid)
    {
        //操作权限判断
        $this->checkAuthBySourceUuid($organizationUuid, xphp_get_config('resource','RESOURCE_TYPE')['M365_EXCHANGE']);
        //获取本地nodeuuid
        $sql = "update m365_organization set nickname = ? where organization_uuid = ?";
        $result = $this->dbExec($sql, array($nickName,$organizationUuid));
        $result = array(
            'result' => $result
        );
        return $result;
    }

    /**
     * 删除组织
     * @param unkown $params 参数
     * @return unknown
     *
     */
    public function deleteOrganization($params)
    {
        //操作权限判断
        $this->checkAuthBySourceUuid(implode(',', $params['organization_uuid']), xphp_get_config('resource','RESOURCE_TYPE')['M365_EXCHANGE']);
        //获取本地nodeuuid
        $nodeuuid = $this->getLocalNodeUUID();
        //判断是否有任务在运行
        $this->checkTaskExist($params['organization_uuid']);
        $result = $this->service()->deleteOrganization($nodeuuid, $params);
        return $result;
    }

    /**
     * 检查该组织是否有任务
     * @param string $organizationuuid 组织uuid
     * @return boolean 是否有任务
     */
    private function checkTaskExist($organizationuuid)
    {
        $info = array();
        foreach ($organizationuuid as $uuid) {
            $sql = "select mt.task_uuid, bt.task_name from m365_task mt left join bd_task bt on mt.task_uuid = bt.task_uuid where mt.organization_uuid = ? ";
            $data = $this->dbSelect($sql, array($uuid));
            if (!empty($data)) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_M365_COMMON_OP_CODE_DELETE_ORGANIZATION'), xphp_get_lang('WEB_M365_COMMON_OP_CODE_DELETE_ORGANIZATION_TIPS') . $data[0]['task_name'], 'warning'));
            }
        }
    }

    /**
     * 获取用于身份验证的代码
     * 判断登录是否成功
     * @param unknown $params 参数
     * @return string 身份验证的代码
     */
    public function getVertifyCode($params)
    {
        $opIdFlag = $params['op_id_flag'];
        $vertifyCodeFlag = $params['vertify_code_flag'];
        $authFlag = $params['auth_flag'];
        $info = array();
        $sql = "select op_status, op_error_code, max_wait_time, detail from bd_operation where op_id = ?";
        if ($opIdFlag) {//获取opid
            //获取本地nodeuuid
            $nodeuuid = $this->getLocalNodeUUID();
            //获取后台返回的opId
            $opId = $this->service()->getVertifyCode($nodeuuid, $params);
            $info = array(
                'op_status' => 0,
                'op_id' => $opId,
            );
        } elseif ($vertifyCodeFlag) {//获取到opid就去获取验证码
            //轮询查询bd_operation表和m365_operation表
            //bd_operation中的op_status：运行中是1 成功是2 失败是3
            $startTime = time();
            do {
                $data = $this->dbSelect($sql, array($params['op_id']));
                $waitTime = $data[0]["max_wait_time"];
                if ($data[0]['op_status'] == 3) {//报错
                    $info = array(
                        'op_status' => 3,
                        'error_code' => $data[0]['op_error_code'],
                    );
                    break;
                } else {
                    if ($data[0]['op_status'] == 1 && !empty($data[0]['detail'])) {
                        $detail = json_decode($data[0]['detail'], true);
                        $vertifyCode = $detail['message']['login_code'];
                        $info = array(
                            'op_status' => 1,
                            'vertify_code' => $vertifyCode,
                            'op_id' => $params['op_id'],
                        );
                        break;
                    }
                }
                sleep(1);
            } while ((time() - $startTime) < $waitTime);
        } elseif ($authFlag) {
            //进行身份验证
            $data = $this->dbSelect($sql, array($params['op_id']));
            //登陆成功返回登录用户
            if ($data[0]['op_status'] == 2 && !empty($data[0]['detail'])) {
                $detail = json_decode($data[0]['detail'], true);
                $info = array(
                    'op_status' => 2,
                    'user' => $detail['message']['login_user'],
                    'tenant_uuid' => $detail['message']['tenant_uuid'],
                );
            } elseif ($data[0]['op_status'] == 3) {//报错
                $info = array(
                    'op_status' => 3,
                    'error_code' => $data[0]['op_error_code'],
                );
            } elseif ($data[0]['op_status'] == 1) {//请求中
                $info = array(
                    'op_status' => 1,
                );
            }
        }
        return $info;
    }

    /**
     * 得到本地节点UUID
     * @return string 本地节点UUID
     */
    public function getLocalNodeUUID()
    {
        $sql = "select node_uuid from bd_node where node_type = ?";
        $data = $this->dbSelect($sql, array(xphp_get_config('app', 'NODETYPE')['MASTER']));
        return $data[0]['node_uuid'];
    }

    /**
     * 获取Microsoft365组织自动刷新间隔时间
     * @return string 自动刷新间隔时间
     */
    public function getRefreshTime()
    {
        $sql = "select m365_refresh_interval from bd_system";
        $data = $this->dbSelect($sql);
        return ceil($data[0]['m365_refresh_interval']);
    }

    /**
     * 更新Microsoft365组织自动刷新间隔时间
     * @param unknown $params 参数
     * @return string 更新结果
     */
    public function editRefreshTime($params)
    {
        $refresh = intval($params['refresh_time']) * 60;
        $sql = "update bd_system set m365_refresh_interval = ?";
        $result = $this->dbExec($sql, array($refresh));
        return $result;
    }

    /**
     * 获取exchange server用于客户端关联的客户端
     * @return string 客户端信息
     */
    public function getServerAgent()
    {
        $info = '';
        $sql = "select ba.agent_uuid,ba.agent_name,ba.hostname,ba.ip,ba.net_model from bd_agent ba where os_type = 'Windows' ";
        //获取当前用户拥有的agent
        if(v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['CLIENT'], 'ba.agent_uuid');
            $sql .= " and ({$resourceUuidSql})";
        }
        $sql .= "and online_flag = 1";
        $result = $this->dbSelect($sql);
        if (!empty($result)) {
            foreach ($result as $each) {
                $name = $each['agent_name'] . '(' . $each['ip'] . ')';
                if ($each['agent_name'] == $each['ip']) {
                    $name = $each['hostname'] . '(' . $each['ip'] . ')';
                }
                $info .= '<option value="' . $each['agent_uuid'] . '" net_model="' . $each['net_model'] . '">' . $name . '</option>';
            }
        }
        return $info;
    }

    /**
     * 同步
     * @param $params 组织相关信息
     * @return string 同步结果
     */
    public function syncOrganization($params)
    {
        //操作权限判断
        $this->checkAuthBySourceUuid($params['info']['organization_uuid'], xphp_get_config('resource','RESOURCE_TYPE')['M365_EXCHANGE']);
        //获取本地nodeuuid
        $nodeuuid = $this->getLocalNodeUUID();
        $result = $this->service()->syncOrganization($nodeuuid, $params);
        return $result;
    }

    /**
     * 根据组织 uuid 获取当前的使用者是谁 只查询分配的资源/组
     * @param string $organizationUuid 组织uuid
     * @param string $userName  拥有者名称
     * @return string
     */
    public function getOganizationUuidName(string $organizationUuid, string $userName = ''): string
    {
        $sqlParams = [$organizationUuid];
        // 先 再分配的资源和资源组去查询
        $sql2 = "select user_uuid from mt_user_resource where resource_type = 56 and resource_uuid = ?";
        $array = $this->dbSelect($sql2, $sqlParams);
        if (!$array) {
            // 再去资源组里面查询
            $sql = "select murg.user_uuid from mt_user_resource_group murg
                join mt_resource_resource_group mrrg on mrrg.resource_group_uuid = murg.resource_group_uuid 
                where mrrg.resource_uuid = ? and mrrg.resource_type = 56";
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
