<?php

namespace app\v1\exchange\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Index;
use app\v1\resources\v0\logic\Node;
use app\v1\tenant\v0\logic\Tenant;
use xphp\db\Op;

/**
 * note          office365（exchange） 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class ExchangeBackUp extends Backup
{
    /**
     * 获取组织信息
     * @param array $params 参数
     * @return array 组织相关信息
     */
    public function getOrganizationInfo($params = [])
    {
        $taskUuid = $params['job_uuid'] ?? '';//修改任务用
        $info = array();
        if (!empty($params['restoreOrganization'])) {//获取恢复页面组织树
            $info = $this->getRecoOrganization($params['organization_uuid']);
            return $info;
        }
        if (!empty($params['organization_uuid']) && empty($params['recovery_key_word'])) {//获取恢复页面组织下的用户
            $info = $this->getRecoUserList($params);
            return $info;
        }
        if (!empty($params['recovery_key_word'])) {//恢复页面组织下的用户搜索
            $info = $this->getSearchRecoUserList($params);
            return $info;
        }
        $sql = "select mo.organization_uuid,mo.agent_uuid_list,mo.organization_name,mo.nickname,mo.region,mo.auth_apps,
       mo.online_flag,mo.refresh_status,mo.error_code from m365_organization mo";
        //组织分配给用户才显示
        $sqlParams = array();
        //获取当前用户拥有的组织
        if(v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['M365_EXCHANGE'], 'mo.organization_uuid');
            $sql .= " where ({$resourceUuidSql})";
        }
        $result = $this->dbSelect($sql, $sqlParams);
        $organizationList = array();//保存组织
        if (!empty($result)) {
            foreach ($result as $each) {
                $taskExitOrganization = $this->getTaskExitObj($each['organization_uuid'],$taskUuid)['organizationList'];//已经存在于备份任务中的组织
                //添加组织
                $chkDisabled = false;
                $isParent = true;
                $bakExit = false;//是否已经在备份任务中
                //组织已经存在于任务中，禁用
                if (in_array($each['organization_uuid'], $taskExitOrganization)) {
                    $chkDisabled = true;
                    $bakExit = true;
                }
                $name = $each['organization_name'];
                if (!empty($each['nickname'])) {
                    $name = $each['nickname'];
                }
                if ($each['online_flag'] == 2) {
                    $name .= xphp_get_lang('WEB_M365_OFFLINE');
                    $chkDisabled = true;
                    $isParent = false;
                }
                if (!in_array($each['organization_uuid'], $organizationList)) {
                    $organizationList[] = $each['organization_uuid'];
                    $netModel = false;
                    if ($each['region'] == 100) {
                        $netModel = $this->getNetModel(json_decode($each['agent_uuid_list'], true));
                    }
                    $info[] = array(
                        'uuid' => $each['organization_uuid'],
                        'organization_uuid' => $each['organization_uuid'],
                        'agent_list' => json_decode($each['agent_uuid_list'], true),
                        'organization_name' => $each['organization_name'],
                        'title' => $each['organization_name'],
                        'region' => $each['region'],//组织区域
                        'online_flag' => $each['online_flag'],//在线标志
                        'error_code' => $each['error_code'],
                        'type' => 10000,//代表组织类型
                        'app_auth' => json_decode($each['auth_apps'], true),//已授权的应用
                        'id' => $each['organization_uuid'],//树节点id
                        'name' => $name,//树节点名称
                        'members' => $each['members'],
                        'pId' => 0,//树节点父id
                        'iconSkin' => 'iconSkin-exch-organization',
                        'isParent' => $isParent,//是否是父节点
                        'chkDisabled' => $chkDisabled,
                        'open' => true,
                        'forbid_flag' => $each['online_flag'] == 2 ? true : false,
                        'net_model' => $netModel,
                        'region' => $each['region'],
                        'backup_object_name' => $each['organization_name'],
                        'backup_object_type' => 10000,//代表组织类型
                        'backup_object_uuid' => $each['organization_uuid'],
                        'bak_exit' => $bakExit,
                        'refresh_status' => $each['refresh_status'],
                        'event_type' => '10000',//组织
                    );
                    if ($isParent) {
                        //用户用户组目录
                        $info[] = array(
                            'uuid' => $each['organization_uuid'] . 1000,
                            'name' => xphp_get_lang('WEB_USERS_USER'),
                            'title' => xphp_get_lang('WEB_USERS_USER'),
                            'iconSkin' => 'iconSkin-exch-dir',
                            'id' => $each['organization_uuid'] . 1000,
                            'pId' => $each['organization_uuid'],
                            'isParent' => true,
                            'organization_uuid' => $each['organization_uuid'],
                            'open' => false,
                            'type' => 1,//代表目录类型
                            'forbid_flag' => $each['online_flag'] == 2 ? true : false,
                            //任务中用户个数等于总用户个数，禁用用户目录的树节点
                            'chkDisabled' => $chkDisabled,
                            'bak_exit' => $bakExit,
                            'online_flag' => $each['online_flag'],
                            'event_type' => '1000',//用户
                        );
                        $info[] = array(
                            'uuid' => $each['organization_uuid'] . 1001,
                            'name' => xphp_get_lang('UI_PLATFORM_SAFETY_USER_GROUP'),
                            'title' => xphp_get_lang('UI_PLATFORM_SAFETY_USER_GROUP'),
                            'iconSkin' => 'iconSkin-exch-dir',
                            'id' => $each['organization_uuid'] . 1001,
                            'pId' => $each['organization_uuid'],
                            'isParent' => true,
                            'organization_uuid' => $each['organization_uuid'],
                            'open' => false,
                            'type' => 1,//代表目录类型
                            'forbid_flag' => $each['online_flag'] == 2 ? true : false,
                            //任务中用户组个数等于总用户组个数，禁用用户组目录的树节点
                            'chkDisabled' => $chkDisabled,
                            'bak_exit' => $bakExit,
                            'online_flag' => $each['online_flag'],
                            'event_type' => '1001',//用户组
                        );
                    }
                }
            }
        }
        //修改页面组织树
        if (!empty($params['jobs_uuid'])) {
            foreach ($info as $key => $each) {//修改不能跨组织，先禁用所有的
                $info[$key]['chkDisabled'] = true;
                $info[$key]['isParent'] = false;
                $info[$key]['job_uuid'] = $params['jobs_uuid'];
            }
            $info = $this->editOrganizationInfo($info, $params['jobs_uuid']);
            return $info;
        }
        return $info;
    }

    /**
     * 获取已经存在于备份任务中的用户、用户组、组织
     * @param string $organizationUuid 组织uuid
     * @param string $taskUuid 任务uuid
     * @return array 已经存在于备份任务中的用户、用户组、组织
     */
    private function getTaskExitObj($organizationUuid,$taskUuid)
    {
        $info = array(
            'userList' => array(),
            'userCount' => 0,
            'organizationList' => array(),
            'userGroupCount' => 0,
        );
        $userCount = 0;
        $userGroupCount = 0;
        $sql = "select backup_object_info from m365_object_list where organization_uuid = ?";
        $sqlParams = array($organizationUuid);
        if (!empty($taskUuid)) {
            $sql .= " and task_uuid != ?";
            $sqlParams = array_merge($sqlParams, array($taskUuid));
        }
        $result = $this->dbSelect($sql, $sqlParams);
        if (!empty($result)) {
            foreach ($result as $each) {
                $backupObjectInfo = json_decode($each['backup_object_info'], true);
                if ($backupObjectInfo['backup_object_type'] == 10000) {//组织
                    $info['organizationList'][] = $backupObjectInfo['backup_object_uuid'];
                } else {//用户、用户组
                    $info['userList'][] = $backupObjectInfo['backup_object_uuid'];
                    if ($backupObjectInfo['backup_object_type'] == 1000) {
                        $userCount++;
                    } else {
                        $userGroupCount++;
                    }
                }
            }
            //如果是组织，还要查出组织下的用户和用户组
            if (!empty($info['organizationList'])) {
                foreach ($info['organizationList'] as $organization) {
                    $sql = "select user_uuid, type from m365_user where organization_uuid = ?";
                    $data = $this->dbSelect($sql, array($organization));
                    if (!empty($data)) {
                        foreach ($data as $d) {
                            $info['userList'][] = $d['user_uuid'];
                            if ($d['type'] == 1000) {
                                $userCount++;
                            } else {
                                $userGroupCount++;
                            }
                        }
                    }
                }
            }
        }
        $info['userCount'] = $userCount;
        $info['userGroupCount'] = $userGroupCount;
        return $info;
    }

    /**
     * 是否显示传输网络---至少有一个客户端是网络模式二就显示,agent_type=2是livecd,exchange用不上，排除了
     * @param array $agentuuidlist 关联的客户端uuid
     * @return boolean 是否显示网络模式
     */
    private function getNetModel($agentuuidlist)
    {
        $flag = false;
        if (!empty($agentuuidlist)) {
            foreach ($agentuuidlist as $agent) {
                $sql = "select net_model from bd_agent where agent_type != 2 and agent_uuid = ?";
                $result = $this->dbSelect($sql, array($agent));
                if (!empty($result) && $result[0]['net_model'] == 2) {
                    $flag = true;//显示传输网络
                    break;
                }
            }
        }
        return $flag;
    }

    /**
     * 获取组织下的用户信息
     * @param array $params 参数
     * @return array 用户相关信息
     */
    public function getUserInfo($params = [])
    {
        $startNum = $params['start_num'];//开始位置
        $limitNum = $params['limit_num'];//加载更多条数
        $eventType = $params['event_type'];//判断请求的是用户还是用户组
        $taskUuid = $params['job_uuid'] ?? '';//修改任务用
        $finishFlag = false;//是否加载完,false是未加载完
        $pId = '';//加载更多的pid
        $total = 0;
        $info = array();
        $sql = "select mo.online_flag, mu.organization_uuid, mu.user_uuid,mu.display_name,mu.mail,mu.type,mu.members from m365_user mu, m365_organization mo where
        mo.organization_uuid = mu.organization_uuid and mu.organization_uuid = ? and mu.type = ? limit ?, ?";
        $sqlParams = array($params['organization_uuid'],$eventType, $startNum, $limitNum);
        $result = $this->dbSelect($sql, $sqlParams);
        switch ($eventType) {
            case 1000:// 请求的是用户
                //组织下的用户数量
                $sqlUser = "select count(user_uuid) as usertotal from m365_user where type = 1000 and organization_uuid = ?";
                $userCount = $this->dbSelect($sqlUser, array($params['organization_uuid']));
                if ($userCount[0]['usertotal'] <= ($startNum + $limitNum)) {
                    $finishFlag = true;//加载完成
                } else {
                    $pId = $params['organization_uuid'] . '1000';
                }
                //用户目录下不在任务中的用户总数,第一次才计算
                if ($startNum == 0) {
                    $total = $this->getDirCount($params['organization_uuid'],1000, $taskUuid);
                }
                break;
            case 1001:// 请求的是用户组
                //组织下的用户组总数
                $sqlUserGroup = "select count(user_uuid) as usergrouptotal from m365_user where type = 1001 and organization_uuid = ?";
                $userGroupCount = $this->dbSelect($sqlUserGroup, array($params['organization_uuid']));
                if ($userGroupCount[0]['usergrouptotal'] <= ($startNum + $limitNum)) {
                    $finishFlag = true;//加载完成
                } else {
                    $pId = $params['organization_uuid'] . '1001';
                }
                //用户组目录下不在任务中的用户组总数,第一次才计算
                if ($startNum == 0) {
                    $total = $this->getDirCount($params['organization_uuid'],1001, $taskUuid);
                }
                break;
            default:
                break;
        }
        $taskExitUser = $this->getTaskExitObj($params['organization_uuid'],$taskUuid)['userList'];//已经存在于备份任务中的用户、用户组
        if (!empty($result)) {
            foreach ($result as $each) {
                $chkDisabled = false;
                $bakExit = false;//是否已经在备份任务中
                //用户、用户组已经存在于任务中，禁用
                if (in_array($each['user_uuid'], $taskExitUser)) {
                    $chkDisabled = true;
                    $bakExit = true;
                }
                if ($each['online_flag'] == 2) {//所在组织离线
                    $chkDisabled = true;
                }

                //添加用户用户组
                $info[] = array(
                    'name' => $each['display_name'] . ' ( ' . $each['mail'] . ' ) ',
                    'members' => json_decode($each['members'], true),
                    'uuid' => $each['user_uuid'],
                    'mail' => $each['mail'],
                    'id' => $each['user_uuid'],
                    'pId' => $each['organization_uuid'] . $each['type'],
                    'title' => $each['display_name'] . ' ( ' . $each['mail'] . ' ) ',
                    'organization_uuid' => $each['organization_uuid'],
                    'iconSkin' => (new ExchangeRecover())->getIconSkin($each['type']),
                    'type' => $each['type'],
                    'forbid_flag' => $each['online_flag'] == 2 ? true : false,
                    'chkDisabled' => $chkDisabled,
                    'backup_object_name' => $each['display_name'],
                    'backup_object_mail' => $each['mail'],
                    'backup_object_type' => $each['type'],
                    'backup_object_uuid' => $each['user_uuid'],
                    'bak_exit' => $bakExit,
                    'online_flag' => $each['online_flag'],
                    'job_uuid' => $taskUuid,
                    'total' => $total,
                );
            }
            if (!$finishFlag) {//数据未加载完成，显示加载更多
                $info[] = array(
                    'name' => xphp_get_lang('WEB_FILE_MORE'),
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    'id' => $params['organization_uuid'] . '_' . $eventType,
                    'pId' => $pId,
                    'organization_uuid' => $params['organization_uuid'],
                    'more' => true,
                    'start_num' => $startNum + $limitNum, //下一次开始位置
                    'nocheck' => true,
                    'bak_exit' => false,
                    'event_type' => $eventType,
                    'job_uuid' => $taskUuid,
                    'total' => $total,
                );
            }
        }
        return $info;
    }

    /**
     * 获取恢复页面组织信息
     * @param $organizationuuid 原组织uuid
     * @return array 组织相关信息
     */
    public function getRecoOrganization($organizationuuid)
    {
        $info = array();
        $sql = "select mo.organization_uuid,mo.agent_uuid_list,mo.organization_name,mo.nickname,mo.online_flag,mo.region
        from m365_organization mo";
        //获取当前用户拥有的组织
        if(v1_auth_need_check_look()) {
            //三权模式下的操作员只能查看分配存储列表
            //不是超级管理员也不是全局观察者, 也只能看到分配的资源
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource','RESOURCE_TYPE')['M365_EXCHANGE'], 'mo.organization_uuid');
            $sql .= " where ({$resourceUuidSql})";
        }
        $data = $this->dbSelect($sql);
        if (!empty($data)) {
            foreach ($data as $d) {
                $name = $d['organization_name'];
                if (!empty($each['nickname'])) {
                    $name = $each['nickname'];
                }
                $srcOrganizationFlag = false;
                if ($d['organization_uuid'] == $organizationuuid) {
                    $name .= xphp_get_lang('WEB_M365_ORIGINAL_ORGANIZATION');
                    $srcOrganizationFlag = true;
                }
                if ($d['online_flag'] == 2) {
                    $name .= xphp_get_lang('WEB_M365_OFFLINE');
                }
                $netModel = false;
                if ($d['region'] == 100) {
                    $netModel = $this->getNetModel(json_decode($d['agent_uuid_list'], true));
                }
                $info[] = array(
                    'id' => $d['organization_uuid'],
                    'name' => $name,
                    'title' => $d['organization_name'],
                    'region' => $d['region'],
                    'pId' => 0,
                    'chkDisabled' => $d['online_flag'] == 1 ? false : true,
                    'src_organization_flag' => $srcOrganizationFlag,
                    'net_model' => $netModel,
                    'agent_uuid_list' => json_decode($d['agent_uuid_list'], true),
                );
            }
        }
        return $info;
    }

    /**
     * 获取恢复页面组织下的用户
     * @param array $params 参数
     * @return string 用户相关信息
     */
    public function getRecoUserList($params)
    {
        $startNum = $params['start_num'];//开始位置
        $limitNum = $params['limit_num'];//加载更多条数
        $eventType = $params['event_type'];//判断请求的是用户还是用户组
        $finishFlag = false;//是否加载完,false是未加载完
        $pId = '';//加载更多的pid
        $total = 0;
        $info = array();
        $sql = "select organization_uuid, user_uuid,display_name,mail,type,members from m365_user where
        organization_uuid = ? and type = ? limit ?, ?";
        $sqlParams = array($params['organization_uuid'],$eventType, $startNum, $limitNum);
        $result = $this->dbSelect($sql, $sqlParams);
        switch ($eventType) {
            case 1000:// 请求的是用户
                //组织下的用户数量
                $sqlUser = "select count(user_uuid) as usertotal from m365_user where type = 1000 and organization_uuid = ?";
                $userCount = $this->dbSelect($sqlUser, array($params['organization_uuid']));
                if ($userCount[0]['usertotal'] <= ($startNum + $limitNum)) {
                    $finishFlag = true;//加载完成
                } else {
                    $pId = $params['organization_uuid'] . '1000';
                }
                break;
            case 1001:// 请求的是用户组
                //组织下的用户组总数
                $sqlUserGroup = "select count(user_uuid) as usergrouptotal from m365_user where type = 1001 and organization_uuid = ?";
                $userGroupCount = $this->dbSelect($sqlUserGroup, array($params['organization_uuid']));
                if ($userGroupCount[0]['usergrouptotal'] <= ($startNum + $limitNum)) {
                    $finishFlag = true;//加载完成
                } else {
                    $pId = $params['organization_uuid'] . '1001';
                }
                break;
            default:
                break;
        }
        if (!empty($result)) {
            foreach ($result as $each) {
                //添加用户用户组
                $info[] = array(
                    'name' => $each['display_name'] . ' ( ' . $each['mail'] . ' ) ',
                    'members' => json_decode($each['members'], true),
                    'uuid' => $each['user_uuid'],
                    'mail' => $each['mail'],
                    'id' => $each['user_uuid'],
                    'pId' => $each['organization_uuid'] . $each['type'],
                    'title' => $each['display_name'] . ' ( ' . $each['mail'] . ' ) ',
                    'organization_uuid' => $each['organization_uuid'],
                    'iconSkin' => (new ExchangeRecover())->getIconSkin($each['type']),
                    'type' => $each['type'],
                    'total' => $total,
                );
            }
            if (!$finishFlag) {//数据未加载完成，显示加载更多
                $info[] = array(
                    'name' => xphp_get_lang('WEB_FILE_MORE'),
                    'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                    'id' => $params['organization_uuid'] . '_' . $eventType,
                    'pId' => $pId,
                    'organization_uuid' => $params['organization_uuid'],
                    'more' => true,
                    'start_num' => $startNum + $limitNum, //下一次开始位置
                    'nocheck' => true,
                    'bak_exit' => false,
                    'event_type' => $eventType,
                    'total' => $total,
                );
            }
        }
        return $info;
    }

    /**
     * 搜索恢复页面组织下的用户
     * @param array $params 参数
     * @return string 用户相关信息
     */
    public function getSearchRecoUserList($params)
    {
        $keyword = $params['recovery_key_word'];
        $organizationName = $params['organization_name'];
        $total = 0;
        $info = array(
            ["name" => $organizationName, "id" => $params['organization_uuid'], "pId" => 0,"isParent" => true,"open" => true,"event_type" => 10000,"organization_uuid" => $params['organization_uuid'],"nocheck" => true],
            ["name" => xphp_get_lang("WEB_USERS_USER"), "id" => $params['organization_uuid'] . '1000', "pId" => $params['organization_uuid'],"isParent" => true,"event_type" => 1000,"organization_uuid" => $params['organization_uuid'],"nocheck" => true],
            ["name" => xphp_get_lang('UI_PLATFORM_SAFETY_USER_GROUP'), "id" => $params['organization_uuid'] . '1001', "pId" => $params['organization_uuid'],"isParent" => true,"event_type" => 1001,"organization_uuid" => $params['organization_uuid'],"nocheck" => true]
        );
        $sql = "select organization_uuid, user_uuid,display_name,mail,type,members from m365_user where
        organization_uuid = ? and (display_name like '%" . $keyword . "%' or mail like '%" . $keyword . "%')";
        $sqlParams = array($params['organization_uuid']);
        $result = $this->dbSelect($sql, $sqlParams);
        if (!empty($result)) {
            foreach ($result as $each) {
                //添加用户用户组
                $info[] = array(
                    'name' => $each['display_name'] . ' ( ' . $each['mail'] . ' ) ',
                    'members' => json_decode($each['members'], true),
                    'uuid' => $each['user_uuid'],
                    'mail' => $each['mail'],
                    'id' => $each['user_uuid'],
                    'pId' => $each['organization_uuid'] . $each['type'],
                    'title' => $each['display_name'] . ' ( ' . $each['mail'] . ' ) ',
                    'organization_uuid' => $each['organization_uuid'],
                    'iconSkin' => (new ExchangeRecover())->getIconSkin($each['type']),
                    'type' => $each['type'],
                    'total' => $total,
                );
            }
        }
        return $info;
    }

    /**
     * 修改组织信息
     * @param array   $info     所有组织
     * @param string  $jobsuuid 任务id
     * @param boolean $netmodel 是否显示传输网络
     * @return array 组织相关信息
     */
    public function editOrganizationInfo($info = [], $jobsuuid = '', $netmodel = false)
    {
        $selectAllFlag = false;//是否选中全部组织
        $limitNum = 50;
        $backupObjectInfo = array();
        $sql = "select organization_uuid,backup_object_info from m365_object_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($jobsuuid));
        $userList = $userGroupList = [];//用于保存该任务下选中的用户、用户组的uuid
        $editBakInfo = array();//用于保存该任务下选中的所有备份对象
        foreach ($info as $key => $item) {
            if ($item['organization_uuid'] == $data[0]['organization_uuid'] && $item['job_uuid'] == $jobsuuid) {
                $info[$key]['isParent'] = true;
                $info[$key]['chkDisabled'] = false;
            }
        }
        foreach ($data as $d) {
            //组装修改的备份源
            $backupObjectInfo = json_decode($d['backup_object_info'], true);
            $backupObjectInfo['organization_uuid'] = $d['organization_uuid'];
            $editBakInfo[] = $backupObjectInfo;
            switch (intval($backupObjectInfo['backup_object_type'])) {
                case 1000://用户
                    $userList[] = $backupObjectInfo['backup_object_uuid'];
                    foreach ($info as $key => $item) {
                        if (($item['event_type'] == 1000 || $item['event_type'] == 10000)
                            && $item['organization_uuid'] == $backupObjectInfo['organization_uuid']) {
                            $info[$key]['checked'] = true;
                        }
                    }
                    break;
                case 1001://用户组
                    $userGroupList[] = $backupObjectInfo['backup_object_uuid'];
                    foreach ($info as $key => $item) {
                        if (($item['event_type'] == 1001 || $item['event_type'] == 10000)
                            && $item['organization_uuid'] == $backupObjectInfo['organization_uuid']) {
                            $info[$key]['checked'] = true;
                        }
                    }
                    break;
                case 10000: //选中整个组织，直接返回
                    foreach ($info as $key => $item) {
                        if ($item['organization_uuid'] == $backupObjectInfo['organization_uuid']) {
                            $info[$key]['checked'] = true;
                            $selectAllFlag = true;
                        }
                    }
//                    return array(
//                        'info' => $info,
//                        'edit_bak_info' => $editBakInfo,
//                    );
            }
        }
        $userNodes = $userGroupNodes = [];
        //全部用户
        $userNodes = $this->getLoadMoreNodes($backupObjectInfo['organization_uuid'] , 1000, $userList,$jobsuuid,$selectAllFlag);
        //全部用户组
        $userGroupNodes = $this->getLoadMoreNodes($backupObjectInfo['organization_uuid'] , 1001, $userGroupList,$jobsuuid,$selectAllFlag);
        $info = array_merge($info, array_slice($userNodes,0,$limitNum), array_slice($userGroupNodes,0,$limitNum));
        if (count($userNodes) > $limitNum) {//数据大于limit_num显示加载更多
            $info = array_merge($info,array([ 'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'id' => $backupObjectInfo['organization_uuid'] . '_' . 1000,
                'pId' => $backupObjectInfo['organization_uuid'] . 1000,
                'organization_uuid' => $backupObjectInfo['organization_uuid'],
                'more' => true,
                'nocheck' => true,
                'event_type' => 1000,
                ]));
        }
        if (count($userGroupNodes) > $limitNum) {//数据大于limit_num显示加载更多
            $info = array_merge($info,array([
                'name' => xphp_get_lang('WEB_FILE_MORE'),
                'title' => xphp_get_lang('WEB_FILE_MORE_TITLE'),
                'id' => $backupObjectInfo['organization_uuid'] . '_' . 1001,
                'pId' => $backupObjectInfo['organization_uuid'] . 1001,
                'organization_uuid' => $backupObjectInfo['organization_uuid'],
                'more' => true,
                'nocheck' => true,
                'event_type' => 1001,
                ]));
        }

        $info = array(
            'info' => $info,//组织、目录一级,
            'user_nodes' => array_slice($userNodes,$limitNum),//用户
            'user_group_nodes' => array_slice($userGroupNodes,$limitNum),//用户组
            'edit_bak_info' => $editBakInfo,
            'user_total' => count($userNodes),
            'user_group_total' => count($userGroupNodes),
        );
        return $info;
    }

    /**
     * 用户或用户组--用于修改
     * @param string $organizationUuid 组织uuid
     * @param int $eventType 类型  1000用户  1001用户组
     * @param array $list 备份任务中的用户列表/用户组列表
     * @param string $jobsUuid 任务id
     * @param bool $selectAllFlag 是否选中全部组织
     * @return array 用户或用户组
     */
    public function getLoadMoreNodes($organizationUuid , $eventType, $list,$jobsUuid,$selectAllFlag)
    {
        $limitNum = 2147483647;//加载更多条数,这里直接取int的最大范围去获取全部用户、用户组
        $userNodes= [];//通过请求获取到的用户
        $p = array(
            'organization_uuid' => $organizationUuid,
            'start_num' => 0, //开始位置
            'limit_num' => $limitNum, //加载更多条数
            'event_type' => $eventType,//判断请求的是用户还是用户组
            'job_uuid' => $jobsUuid,
        );
        $userNodes = $this->getUserInfo($p);
        foreach ($userNodes as $key => $each) {
            if (in_array($each['uuid'], $list)) {
                $userNodes[$key]['checked'] = true;
                $userNodes[$key]['chkDisabled'] = false;
            }
            if ($selectAllFlag) {
                $userNodes[$key]['checked'] = true;
            }
        }
        return $userNodes;
    }

    /**
     * 修改任务,得到备份任务的所有信息
     * @param unknown $params 参数
     * @return array 备份任务的所有信息
     */
    public function getBackupTaskInfo($params)
    {
        $taskUUID = $params['jobs_uuid'];
        $sql = "select bt.agent_uuid,bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid,bt.strategy_group_uuid, bt.thread_num,
        bt.worm_flag, bt.virus_scan_flag, bt.integrity_check_flag,bt.ignore_resource_limiting_flag,bt.node_pool_uuid, bt.storage_pool_uuid,
        brs.strategy_type, brs.number,brs.strategy_mode,mt.detail,mt.organization_uuid,mol.exclude_folders, mt.m365_retry_info,
        bss.compressed_flag, bss.encrypted_flag,bss.password,bss.password_auto_flag,bss.compress_method,  bss.encrypt_method,
        bts.encrypt_flag, bts.compress_flag,bts.network_uuid, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method as transport_method,
        btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, btsc.backup_integrity_check_full_error_policy,btsc.backup_integrity_check_inc_error_policy 
        from bd_task bt, bd_reserved_strategy brs, bd_storage_strategy bss, m365_task mt,bd_transport_strategy bts,m365_object_list mol,bd_task_safe_config btsc
        where bt.strategy_id = brs.strategy_id
        and bt.strategy_id = bss.strategy_id
        and bt.strategy_id = bts.strategy_id
		and bt.task_uuid = mt.task_uuid
        and bt.task_uuid = mol.task_uuid
        and bt.task_uuid = btsc.task_uuid
        and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $info = array();
        $agentConfigFlag = false;
        if ($data) {
            if ($data[0]['agent_uuid'] != "") {
                $agentConfigFlag = true;
                $agentDes = ExchangeJobInfo::instance()->getAgentDes($data[0]['agent_uuid']);
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
                'strategy_uuid' => $data[0]['strategy_group_uuid'],
                //任务名
                'job_name' => $data[0]['task_name'],
                //组织id
                'organization' => $data[0]['organization_uuid'],
                //level
                'level' => $data[0]['level'],
                //备份列表信息
                'obj_list_info' => $this->getExchangeBackupObj($taskUUID),
                //排除目录信息
                'exclude_folders' => json_decode($data[0]['exclude_folders'], true),
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
                    'type' => $data[0]['strategy_type'],
                    'number' => $data[0]['number'],
                    'strategy_mode' => $data[0]['strategy_mode'],
                ),
                //存储策略
                'bss' => array(
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    'password' => base64_encode(v1_pt_pass_decrypt($data[0]['password'])),
                    'compress_method' => $data[0]['compress_method'],
                    'encrypt_method' => $data[0]['encrypt_method'],
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
                //时间策略
                'time_strategy' => $this->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
//                'speed_info' => $this->getSpeedStrategyInfo($taskUUID),
                'speedInfo' => $this->getSpeedStrategy($taskUUID),
                //高级策略
                'high' => array(
                    'thread_num' => $data[0]['thread_num'],
                ),
                'agent_config_flag' => $agentConfigFlag,//是否配置客户端
                'agent_des' => $agentDes,
                'agent_uuid' => $data[0]['agent_uuid'],
                 //安全策略
                'safe_strategy' => array(
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
                'retry_strategy' => ExchangeJobInfo::instance()->getRetryStrategy($taskUUID),
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag']),
            );
        }
        return $info;
    }


    /**
     * 根据任务ID得到exchange备份备份列表信息
     * @param string $jobuuid 任务uuid
     * @return array 备份列表信息
     */
    private function getExchangeBackupObj($jobuuid = '')
    {
        if (!empty($jobuuid)) {
            $sql = "select backup_object_info from m365_object_list where task_uuid = ?";
            $data = $this->dbSelect($sql, array($jobuuid));
        } else {//得到所有在任务中的备份对象
            $sql = "select backup_object_info from m365_object_list";
            $data = $this->dbSelect($sql, array());
        }
        $info = array();
        foreach ($data as $d) {
            $backupObjInfo = json_decode($d['backup_object_info'], true);
            $info[] = array(
                'uuid' => $backupObjInfo['backup_object_uuid'],
                'type' => $backupObjInfo['backup_object_type'],
                'name' => $this->getObjName($backupObjInfo['backup_object_uuid'], $backupObjInfo['recovery_object_type']),
            );
        }
        return $info;
    }

    /**
     * 根据任务uuid和type得到每个备份对象的名字
     * @param string $uuid uuid
     * @param int    $type 类型
     * @return string 对象的名字
     */
    private function getObjName($uuid, $type)
    {
        $name = '';
        switch (intval($type)) {
            //组织
            case 0:
            case 1:
            case 100:
                $sql = "select organization_name,nickname from m365_organization where organization_uuid = ?";
                $data = $this->dbSelect($sql, array($uuid));
                $name = $data[0]['organization_name'];
                if (!empty($data[0]['nickname'])) {
                    $name = $data[0]['nickname'];
                }
                break;
            //用户、用户组
            case 1000:
            case 1001:
                $sql = "select display_name,mail from m365_user where user_uuid = ?";
                $data = $this->dbSelect($sql, array($uuid));
                $name = $data[0]['display_name'] . '(' . $data[0]['mail'] . ')';
                break;
        }
        return $name;
    }

    /**
     * 创建备份任务
     * @param array $params 参数
     * @return string 创建的结果
     */
    public function createBackupJob($params = [])
    {
        $pfOpcode = new PfOpcode();
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $operate = $pfOpcode->getOpcodeDes($opName);
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        //public params
        $taskname = htmlspecialchars_decode($params['job_name']);
        //全局策略uuid  (没有为空值)
        $globalID = $params['strategygroupuuid'] ?? '';
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['M365'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            (new Tenant())->checkTenantAuth($moduletype, $params['src_info']['backup_object_info'], '');
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
        $nodeInfo = $this->groupBackupNodeInfo($params['high_info']['node']);
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
        $pfMsg['thread_num'] = $params['high_info']['newstr']['backupThreadNum'];//传输线程数量
         // 全局限速策略配置-新
         $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $globalID;
        //得到安全策略
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy'], $params['high_info']['node']['storage_uuid']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $this->groupRetryStrategy($params['retry_strategy']);
        $pfMsg['backup_exch_object_info'] = $this->getBakObjInfo($params['src_info']);
        $pfMsg['agent_uuid'] = $params['high_conf']['agent_uuid'];
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_info']['ignore_resource_limiting_flag']);
        $mbResult = $this->service()->createBackupJob(Node::instance()->getMasterNodeUuid(), $pfMsg, $opName);
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg']);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取备份对象相关信息传给后台
     * @param array $srcInfo 参数
     * @return object 备份对象相关信息
     */
    public function getBakObjInfo($srcInfo = [])
    {
        $backup_object_info = array();
        $allObjUUId = array();
        //全选了用户目录，获取用户目录下的数据
        if ($srcInfo['user_dir_flag']) {
            $backup_object_info = array_merge($backup_object_info, $this -> getDirInfo($srcInfo['organization_uuid'],1000));
        }
        //全选了用户组目录，获取用户组目录下的数据
        if ($srcInfo['user_group_dir_flag']) {
            $backup_object_info = array_merge($backup_object_info, $this -> getDirInfo($srcInfo['organization_uuid'],1001));
        }
        if (!empty($backup_object_info)) {
            foreach ($backup_object_info as $each) {
                if (!in_array($each['backup_object_uuid'], $allObjUUId)) {
                    $allObjUUId[] = $each['backup_object_uuid'];
                }
            }
        }
        //去重
        foreach ($srcInfo['backup_object_info'] as $each) {
            if (!in_array($each['backup_object_uuid'], $allObjUUId)) {
                $backup_object_info[] = $each;
                $allObjUUId[] = $each['backup_object_uuid'];
            }
        }
        $result = [
            'include_exch_backup_object_info_list' => $backup_object_info,
            'organization_uuid' => $srcInfo['organization_uuid'],
            'exclude_exch_backup_folder_list' => $srcInfo['exclude_folder_list']
        ];
        return (object) $result;
    }

    /**
     * 修改备份任务
     * @param array $params 参数
     * @return string 修改的结果
     */
    public function editBackupJob($params = [])
    {
        $pfOpcode = new PfOpcode();
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $operate = $pfOpcode->getOpcodeDes($opName);
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            exit($this->muOpResult(false, $operate, xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED') , "warning"));
        }
        //public params
        $taskname = htmlspecialchars_decode($params['job_name']);
        //全局策略uuid  (没有为空值)
        $globalID = $params['strategygroupuuid'] ?? '';
        $moduletype = xphp_get_config('module', 'MODULE_TYPE')['M365'];
        //租户内检测授权个数
        if (xphp_get_user_info()['tenantuuid']) {
            Tenant::instance()->checkTenantAuth($moduletype, $params['src_info']['backup_object_info'], $params['job_uuid']);
        }
        // 传输网络格式化
        $params['high_info']['transfer']['network'] = $this->buildNetworkUuid(
            $params['high_info']['node']['node_uuid'] ?: '',
            $params['high_info']['transfer']['network'] ?: ''
        );
        //组合备份方式完备差备等
        $timestrategylist = $this->groupBackupTimeList($params['backup_info'], $globalID);
        //组合保留策略
        $reserverstrategy = $this->groupReserverStrategy($params['high_info']['reserve']);
        //组合传输策略
        $transportstrategy = $this->groupTransportStrategy($params['high_info']['transfer']);
        //组合存储策略
        $storagestrategy = $this->groupStorageStrategy($params['high_info']['store']);
        //组合节点信息
        $nodeInfo = $this->groupBackupNodeInfo($params['high_info']['node']);
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
        $pfMsg['thread_num'] = $params['high_info']['newstr']['backupThreadNum'];//传输线程数量
        // 全局限速策略配置-新
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $globalID;
        $pfMsg['safe_config_strategy'] = $this->groupSafeConfigStrategy($params['safe_strategy'], $params['high_info']['node']['storage_uuid']);
        //得到重试策略
        $pfMsg['retry_strategy'] =  $this->groupRetryStrategy($params['retry_strategy']);
        $pfMsg['backup_exch_object_info'] = $this->getBakObjInfo($params['src_info']);
        $pfMsg['task_uuid'] =  $params['job_uuid'];
        $pfMsg['agent_uuid'] = $params['high_conf']['agent_uuid'];
        // 忽略节点资源限制
        $pfMsg['ignore_resource_limiting_flag'] = v1_parse_bool_to_flag($params['high_info']['ignore_resource_limiting_flag']);
        $mbResult = $this->service()->editBackupJob(Node::instance()->getMasterNodeUuid(), $pfMsg, $opName);
        //返回结果到UI
        if ($mbResult['result']) {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg']);
        } else {
            return $this->muOpResult($mbResult['result'], $operate, $mbResult['msg'], '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取备份任务名--job_name参数可以供其他模块调用
     * @param unkown $params 参数
     * @return string 返回结果
     */
    public function getBackupTaskName($params = [])
    {
        $JobName = $params['job_name'] ?? '';
        if ($JobName == '') {
            $JobName = xphp_get_lang('WEB_M365_BACKUP_NAME');
        }
        return $this->getValidTaskName($JobName);
    }

    /**
     * 获取可用的任务名
     * @param string $taskName 任务名前缀
     * @return string
     */
    public function getValidTaskName($taskName)
    {
        $oldTaskName = $taskName;
        for($i=1; $i<1000; $i++){
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
     * 获取告警信息
     * @param array $params 告警id
     * @return string
     */
    public function getM365Alarm($params)
    {
        $alarmId = $params['alarm_id'];
        $sql = "select bht.details,bht.task_type from bd_history_task bht, bd_task_alarm bta 
                where bht.history_uuid = bta.history_uuid and bta.task_alarm_id  = ? ";
        $sqlParams = array($alarmId);
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        if (!empty($data)) {
            $detail = json_decode($data[0]['details'], true);
            if ($data[0]['task_type'] == 1) {//备份
                $info[] = array(
                    'organization_name' => $detail['organization_name'],
                    'task_type' => $data[0]['task_type'],
                    'backup_obj' => $detail['backup_m365_object_info_list'],
                );
            } else {//恢复
                $info[] = array(
                    'organization_name' => $detail['organization_name'],
                    'task_type' => $data[0]['task_type'],
                    'recovery_obj' => $detail['recovery_m365_object_info_list'],
                );
            }
        }
        return $info;
    }

    /**
     * 获取目录下的数据总数
     * @param string $organizationUuid 组织uuid
     * @param string $eventType 类型
     * @param string $taskUuid 任务uuid
     * @return int
     */
    public function getDirCount($organizationUuid, $eventType, $taskUuid = '') {
        $count = 0;
        $dataArr = $uuidArr = [];
        $sql = "select user_uuid from m365_user where organization_uuid = ? and type = ?";
        $result = $this->dbSelect($sql, array($organizationUuid,$eventType));
        //在任务中的备份对象的uuid
        $sqlTaskSql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(backup_object_info, '$.backup_object_uuid')) AS backup_object_uuid  
        FROM m365_object_list WHERE organization_uuid = ? ";
        $params = array($organizationUuid);
        if (!empty($taskUuid)) {
            $sqlTaskSql .= " and task_uuid != ? ";
            $params = array($organizationUuid, $taskUuid);
        }
        $taskResult = $this->dbSelect($sqlTaskSql, $params);
        foreach ($taskResult as $each) {
            if (!in_array($each['backup_object_uuid'], $uuidArr)) {
                $uuidArr[] = $each['backup_object_uuid'];
            }
        }
        foreach ($result as $each) {
            if (!in_array($each['user_uuid'], $uuidArr)) {
                $dataArr[] = $each;
            }
        }
        if (!empty($dataArr)) {
            $count = count($dataArr);
        }
        return $count;
    }

    /**
     * 获取目录下的数据,不包含已经在任务中的数据
     * @param string $organizationUuid 组织uuid
     * @param string $eventType 类型
     * @return int
     */
    public function getDirInfo($organizationUuid, $eventType) {
        $info = array();
        $sql = "select user_uuid,display_name,mail,type,members from m365_user where organization_uuid = ? and type = ?";
        $result = $this->dbSelect($sql, array($organizationUuid,$eventType));
        $uuidArr = array_column($this->getExchangeBackupObj(), 'uuid');//在任务中的备份对象的uuid
        if (!empty($result)) {
            switch ($eventType) {
                case 1000: //用户
                    foreach ($result as $each) {
                        if (!in_array($each['user_uuid'], $uuidArr)) {
                            $info[] = array(
                                'backup_object_name' => $each['display_name'],
                                'backup_object_uuid' => $each['user_uuid'],
                                'backup_object_type' => $each['type'],
                                'backup_object_mail' => $each['mail'],
                            );
                        }
                    }
                    break;
                case 1001: //用户组
                    foreach ($result as $each) {
                        if (!in_array($each['user_uuid'], $uuidArr)) {
                            $info[] = array(
                                'backup_object_name' => $each['display_name'],
                                'backup_object_uuid' => $each['user_uuid'],
                                'backup_object_type' => $each['type'],
                                'backup_object_mail' => $each['mail'],
                                'members' => json_decode($each['members'], true),
                            );
                        }
                    }
            }
        }
        return $info;
    }

    /**
     * 获获取组织下本次要备份的用户数
     * @param string $organizationUuid 组织uuid
     * @return int
     */
    public function getM365UserNum($params) {
        $num = 0;
        $organizationUuid = $params['organization_uuid'];
        $jobUuid = $params['job_uuid'];
        $sql = "select user_uuid from m365_user where organization_uuid = ?";
        $result = $this->dbSelect($sql, array($organizationUuid));
        $uuidArr = array_column($this->getExchangeBackupUserNum($jobUuid), 'uuid');//在任务中的备份对象的uuid
        if (!empty($result)) {
            foreach ($result as $each) {
                if (!in_array($each['user_uuid'], $uuidArr)) {
                    $num ++;
                }
            }
        }
        return $num;
    }

    /**
     * 根据任务ID得到exchange备份任务中的用户uuid
     * @param string $jobuuid 任务uuid
     * @return array 备份列表信息
     */
    private function getExchangeBackupUserNum($jobuuid = '')
    {
        if (!empty($jobuuid)) {
            $sql = "select backup_object_info from m365_object_list where task_uuid != ?";
            $data = $this->dbSelect($sql, array($jobuuid));
        } else {//得到所有在任务中的备份对象
            $sql = "select backup_object_info from m365_object_list";
            $data = $this->dbSelect($sql, array());
        }
        $info = array();
        foreach ($data as $d) {
            $backupObjInfo = json_decode($d['backup_object_info'], true);
            $info[] = array(
                'uuid' => $backupObjInfo['backup_object_uuid'],
            );
        }
        return $info;
    }
}