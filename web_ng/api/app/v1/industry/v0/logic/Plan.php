<?php

namespace app\v1\industry\v0\logic;

use app\v1\common\logic\Base;

/**
 * note          方案逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2025/9/29 10:59
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class Plan extends Base
{
    /**
     * 获取方案列表
     * @param array $params 参数
     * @return array
     */
    public function getList(array $params): array
    {

        $offset = $params['offset'];
        $limit = $params['limit'];
        $sort = $params['sort'];
        $order = $params['order'];
        $search = $params['search'];
        $mainUuid = $params['main_uuid'];
        $userInfo = xphp_get_user_info();
        $planType = xphp_get_config('industry', 'PLAN_TYPE');
        $table = 'industry_plan ir';
        $left = '';
        $filed = '';
        $sqlParams = [];
        $userGroup = $this->dbSelect(
            "select user_group_uuid from mt_user_user_group where user_uuid = ?",
            [$userInfo['userUuid']]
        );
        $userGroupArr = array_column($userGroup, 'user_group_uuid');
        if ($params['type'] == 3) {
            // 分享或抄送
            $left = " LEFT JOIN industry_user iu ON iu.report_uuid = ir.plan_uuid";
            $where = " where iu.user_uuid = ?";
            $filed = ', iu.content content_remark';
            $sqlParams = [$userInfo['userUuid']];
        } elseif (in_array($params['type'], [1, 2])) {
            $planArr = [
                1 => $planType['DATA_VALIDATE'], // 数据验证
                2 => $planType['APPROVALING'],  // 功能验证
            ];
            $where = " where ir.plan_type = {$planArr[$params['type']]}";
            // 如果不是全局观察者或者超级管理员，只能查看对应的
            if (v1_auth_need_check_look()) {
                // 这里需要查询出用户所在的用户组，可能是一个数组，需要用 in
                $whereor = '';
                foreach ($userGroupArr as $item) {
                    $whereor .= " or ir.approval_user_uuid like  '%,{$item},%' ";
                }
                $where .= " and ( ir.user_uuid = ? or ir.approval_user_uuid like ? {$whereor})";
                $sqlParams = [$userInfo['userUuid'], '%,' . $userInfo['userUuid'] . ',%'];
            }
        } elseif (in_array($params['type'], [4, 5, 6])) {
            // 4 待审批方案；5 待发起方案；6已通过方案-这是其它页面会调用的
            $arr = [
                4 => [0, 2],
                5 => [3, 4],
                6 => [1]
            ];
            $statusString = implode(',', $arr[$params['type']]);
            $where = " where ir.status in ({$statusString}) ";

            // 只查询本人的
            if ($params['type'] == 4) {
                $allUser = array_merge($userGroupArr, [$userInfo['userUuid']]);
                $where .= " and ir.now_user_uuid in ('" . implode("','", $allUser) . "') ";
            } else {
                $where .= " and ir.user_uuid = '{$userInfo['userUuid']}' ";
            }
        } else {
            $where = ' where 1 = 1';
        }

        if (!empty($mainUuid)) {
            $where .= ' and ir.main_uuid = ?';
            $sqlParams = array_merge($sqlParams, [$mainUuid]);
        } elseif ($params['type'] < 3) {
            // 同一个版本只会查询一个最新的
            $table = "( SELECT ir.*,
                           ROW_NUMBER() OVER (PARTITION BY ir.main_uuid ORDER BY ir.id DESC) AS rn
                    FROM industry_plan ir
                    {$left}
                    {$where}
                ) ir";
            $where = ' where ir.rn = 1 ';
            // 还需要查询出所有的子版本数量
            $filed .= ',(select count(*) from industry_plan where main_uuid = ir.main_uuid) total_version ';
        }

        if (!empty($search)) {
            $where .= ' and ir.name like ?';
            $sqlParams = array_merge($sqlParams, ["%{$search}%"]);
        }

        if (!empty($params['plan_title'])) {
            $where .= ' and ir.name like ?';
            $sqlParams = array_merge($sqlParams, ["%{$params['plan_title']}%"]);
        }
        if (!empty($params['plan_num'])) {
            $where .= ' and ir.serial_number like ?';
            $sqlParams = array_merge($sqlParams, ["%{$params['plan_num']}%"]);
        }
        if (!empty($params['plan_version'])) {
            $where .= ' and ir.version like ?';
            $sqlParams = array_merge($sqlParams, ["%{$params['plan_version']}%"]);
        }
        if (!empty($params['plan_approval'])) {
            $where .= ' and ir.approval_uuid = ?';
            $sqlParams = array_merge($sqlParams, [$params['plan_approval']]);
        }

        $sql = "select ir.plan_type,ir.plan_uuid, ir.name, ir.approval_time, ir.update_time, ir.status,
               ir.now_user_depth,ir.create_time, ir.approval_user_uuid, ir.approval_list, ir.version,ir.plan_type,
               ir.approval_uuid, ir.now_user_uuid, ir.user_uuid, ir.now_user_type,ir.serial_number,ir.main_uuid,
               bu.user_name, bu2.user_name as now_user_name, bug.user_group_name {$filed} from {$table} 
        left join bd_user bu on bu.user_uuid = ir.user_uuid
        left join bd_user bu2 on bu2.user_uuid = ir.now_user_uuid
        left join bd_user_group bug on bug.user_group_uuid = ir.now_user_uuid
        {$left} {$where}";

        $sqlCount = "select count(*) as total from {$table} {$left} {$where}";

        $count = $this->dbSelect($sqlCount, $sqlParams);

        if (empty($count[0]['total'])) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        if (!empty($sort) && !empty($order)) {
            $sortArr = [
                'user_name' => 'bu.user_name',
                'name' => 'ir.name',
                'update_time' => 'ir.update_time',
                'approval_time' => 'ir.approval_time',
                'status' => 'ir.status',
                'num' => 'ir.serial_number',
                'total_version' => 'total_version',
                'version' => 'ir.version',
            ];
            $sort = $sortArr[$sort] ?? 'ir.id';
            $sql .= " order by {$sort} {$order}";
        } else {
            //默认按照时间倒序进行排序
            $sql .= " order by ir.update_time desc";
        }

        if (!empty($limit)) {
            $sql .= " limit {$offset}, {$limit}";
        }

        $data = $this->dbSelect($sql, $sqlParams);

        $reportInfo = new Report();
        $records = [];
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        foreach ($data as $d) {
            $records[] = array(
                'num' =>  $d['serial_number'],
                'uuid' => $d['plan_uuid'],
                'main_uuid' => $d['main_uuid'],
                'plan_type' => $d['plan_type'],
                'approval_uuid' => $d['approval_uuid'],
                'name' => $d['name'],
                'total_version' => $d['total_version'] ?? 0,
                'update_time' => $d['update_time'],
                'version' => $d['version'],
                'approval_time' => $d['approval_time'] ?? $nullSpace,
                'status' => intval($d['status']), // 如果在审批中，那么则不能编辑
                'status_value' => $this->getApprovalStatusDes($d['status'], $d['now_user_name']),
                'now_user_uuid' => $d['now_user_uuid'] ?? $nullSpace,
                'now_user_depth' => $d['now_user_depth'],
                //如果当前用户不等于now_user_uuid，就看now_user_uuid是否在当前用户所在的用户组arr里面
                'approval_flag' =>
                    $userInfo['userUuid'] == $d['now_user_uuid'] || in_array($d['now_user_uuid'], $userGroupArr),
                'user_flag' => $userInfo['userUuid'] == $d['user_uuid'],
                'master_flag' => $reportInfo->getMasterFlag(), //是否是管理员
                'user_name' => $d['user_name'],
                'user_uuid' => $d['user_uuid'],
                'now_user_name' => $d['now_user_name'] ?? $d['user_group_name'],
                'approval_list' => $d['approval_list'],
                'content' => $d['content'] ?? '',
                'content_remark' => $d['content_remark'] ?? '',
            );
        }

        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
    * 查看方案
     * @param string $planUuid 方案uuid
     * @return array
     */
    public function viewPlan(string $planUuid): array
    {

        $sql = "select 
               ip.plan_uuid,ip.name,ip.serial_number,ip.version,ip.content,ip.approval_uuid,al.name approval_name
            from industry_plan ip left join approval_list al on ip.approval_uuid = al.approval_uuid
            where plan_uuid = ?";
        $data = $this->dbSelect($sql, [$planUuid]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
            ];
        }
        $data = [
            'plan_uuid' => $data[0]['plan_uuid'],
            'name' => $data[0]['name'],
            'serial_number' => $data[0]['serial_number'],
            'approval_uuid' => $data[0]['approval_uuid'],
            'version' => $data[0]['version'],
            'content' => $data[0]['content'],
            'approval_name' => $data[0]['approval_name'],
        ];
        $item = [$data['name'], xphp_get_lang('UI_PUBLIC_LOOK')];
        // 写日志
        $this->systemLog('PT_INDUSTRY_PLAN_OPERATE', $item);

        return [
            'code' => 0,
            'msg' => $data
        ];
    }

    /**
     * 新建、修改方案
     * @param array $params 方案uuid
     * @return array
     */
    public function submitPlan(array $params): array
    {

        $return = ['code' => -1, 'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')];
        // 获取审批流信息
        $sql = "select content from approval_list where approval_uuid = ?";
        $approval = $this->dbSelect($sql, [$params['approval_uuid']]);
        if (empty($approval)) {
            return $return;
        }

        $isUpdate = false;
        $planUuid = xphp_uuid();
        if (!empty($params['plan_uuid'])) {
            $isUpdate = true;
            $planUuid = $params['plan_uuid'];
        }

        $content = json_decode($approval[0]['content'], true);
        $nowUserUuid = $content[0]['approval_user_uuid']; // 当前该审批的用户uuid
        $nowUserType = $content[0]['type']; // 当前该审批的用户类型
        $reportLogic = new Report();
        $user = xphp_get_user_info();
        foreach ($content as $key => $item) {
            //抄送人员插入industry_user
            $reportLogic->insertCopySendUsers(
                $item['users']['user'],
                $item['users']['user_group'],
                $params['approval_uuid'],
                $key,
                $planUuid,
                1
            );
        }
        // 审批流
        $approvalList = $reportLogic->makeApproval(
            $user['userUuid'],
            $user['userName'],
            $params['approval_uuid']
        );

        // 审核流关联的用户uuid集合，英文逗号隔开
        $userUuidString = implode(',', array_column($approvalList, 'user_uuid'));

        $planStatus = xphp_get_config('industry', 'PLAN_STATUS');
        $nowTime = date(xphp_get_config('special', 'dateformat'));

        $data = [
            $params['name'], // name
            $params['serial_number'], // serial_number
            $params['content'], // content
            $planStatus['PENDING'], // status
            $user['userUuid'], // user_uuid
            $params['approval_uuid'], // approval_uuid
            $userUuidString, // approval_user_uuid
            $nowUserUuid, // now_user_uuid
            json_encode($approvalList), // approval_list
            $nowUserType, // now_user_type
            $nowTime, // update_time
            0, // now_user_depth
            null, // approval_time
        ];

        if (!empty($isUpdate)) {
            // 修改 如果是已审批 1，那么就会产生一个新的小版本，如果是 0 3 4 那么就是编辑
            $oldData = $this->dbSelect(
                'select status,main_uuid,plan_type from industry_plan where plan_uuid = ?',
                [$planUuid]
            );

            if ($oldData[0]['status'] == $planStatus['ARCHIVED']) {
                // 已审批 1
                // 查询出当前大版本内的最新的小版本是多少(直接根据id降序最新的那一个)
                $maxVersion = $this->dbSelect(
                    'select version from industry_plan where main_uuid = ? order by id desc limit 1',
                    [$oldData[0]['main_uuid']]
                );

                // 去掉开头的 'V'（不区分大小写更安全）
                $numberPart = ltrim($maxVersion[0]['version'], 'vV'); // "003"

                $number = (int)$numberPart; // 3
                $nextNumber = $number + 1;  // 4

                $newVersion = 'V' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT); // "V004"

                $sql = 'insert into industry_plan (`name`, serial_number, content, status, user_uuid, approval_uuid,
                           approval_user_uuid, now_user_uuid, approval_list, now_user_type, update_time, now_user_depth
                           ,approval_time, plan_type, plan_uuid, main_uuid, version, create_time
                           ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,? ,?, ?, ?, ?)';
                $data = array_merge(
                    $data,
                    [$oldData[0]['plan_type'], $planUuid, $oldData[0]['main_uuid'], $newVersion, $nowTime]
                );
                $item = [$params['name'], xphp_get_lang('UI_PUBLIC_ADD')];
            } else {
                // 编辑
                $sql = 'update industry_plan set `name`=?,serial_number=?,content=?,status=?,user_uuid=?,
                         approval_uuid=?,approval_user_uuid=?,now_user_uuid=?,approval_list=?,now_user_type=?,
                         update_time=?,now_user_depth=?,approval_time=? where plan_uuid = ?';
                $data = array_merge($data, [$planUuid]);

                $item = [$params['name'], xphp_get_lang('UI_PUBLIC_MODIFY')];
            }
        } else {
            // 新建 会产生一个新的v001的版本
            $sql = 'insert into industry_plan (`name`, serial_number, content, status, user_uuid, approval_uuid,
                           approval_user_uuid, now_user_uuid, approval_list, now_user_type, update_time, now_user_depth
                           ,approval_time, plan_type, plan_uuid, main_uuid, version, create_time
                           ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,? ,?, ?, ?, ?)';
            $data = array_merge($data, [$params['plan_type'], $planUuid, $planUuid, 'V001', $nowTime]);

            $item = [$params['name'], xphp_get_lang('UI_PUBLIC_ADD')];
        }

        // 写日志
        $this->systemLog('PT_INDUSTRY_PLAN_OPERATE', $item);

        $this->dbExec($sql, $data);
        return [
            'code' => 0,
            'msg' => ''
        ];
    }

    /**
     * 复制方案
     * @param array $params 方案uuid
     * @return array
     */
    public function copyPlan(array $params): array
    {

        // 会产生一个新的大版本，V001
        $oldData = $this->dbSelect(
            'select * from industry_plan where plan_uuid = ?',
            [$params['plan_uuid']]
        );

        $approvalUuid = $params['approval_uuid'];

        $return = ['code' => -1, 'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')];
        // 获取审批流信息
        $sql = "select content from approval_list where approval_uuid = ?";
        $approval = $this->dbSelect($sql, [$approvalUuid]);
        if (empty($approval)) {
            return $return;
        }

        $planUuid = xphp_uuid();

        $content = json_decode($approval[0]['content'], true);
        $nowUserUuid = $content[0]['approval_user_uuid']; // 当前该审批的用户uuid
        $nowUserType = $content[0]['type']; // 当前该审批的用户类型
        $reportLogic = new Report();
        $user = xphp_get_user_info();
        foreach ($content as $key => $item) {
            //抄送人员插入industry_user
            $reportLogic->insertCopySendUsers(
                $item['users']['user'],
                $item['users']['user_group'],
                $approvalUuid,
                $key,
                $planUuid,
                1
            );
        }
        // 审批流
        $approvalList = $reportLogic->makeApproval(
            $user['userUuid'],
            $user['userName'],
            $approvalUuid
        );

        // 审核流关联的用户uuid集合，英文逗号隔开
        $userUuidString = implode(',', array_column($approvalList, 'user_uuid'));

        $planStatus = xphp_get_config('industry', 'PLAN_STATUS');
        $nowTime = date(xphp_get_config('special', 'dateformat'));
        $data = [
            $oldData[0]['plan_type'], // plan_type
            $planUuid, // plan_uuid
            $planUuid, // main_uuid
            $params['name'], // name
            $user['userUuid'], // user_uuid
            $params['serial_number'], // serial_number
            'V001', // version
            $nowTime, // create_time
            $nowTime, // update_time
            null, // approval_time
            $planStatus['PENDING'], // status
            $params['content'], // content
            $approvalUuid, // approval_uuid
            $userUuidString, // approval_user_uuid
            $nowUserUuid, // now_user_uuid
            json_encode($approvalList), // approval_list
            $nowUserType, // now_user_type
            0, // now_user_depth
        ];
        $sql = 'insert into industry_plan (plan_type, plan_uuid, main_uuid, `name`, user_uuid, serial_number, version,
                           create_time, update_time, approval_time, status, content, approval_uuid,
                           approval_user_uuid, now_user_uuid, approval_list, now_user_type,now_user_depth
                           ) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,? ,?, ?, ?, ?)';
        $this->dbExec($sql, $data);

        $item = [$params['name'], xphp_get_lang('UI_PUBLIC_COPY')];
        // 写日志
        $this->systemLog('PT_INDUSTRY_PLAN_OPERATE', $item);

        return [
            'code' => 0,
            'msg' => ''
        ];
    }

    /**
     * 获取方案列表状态描述
     * @param int    $status      状态参数
     * @param string $nowUserName 当前待审批用户名
     * @return string
     */
    private function getApprovalStatusDes(int $status, $nowUserName = ''): string
    {

        $allStatus = xphp_get_config('industry', 'PLAN_STATUS');
        $status = intval($status);
        switch ($status) {
            case $allStatus['ARCHIVED']: // 1已审批
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_NORMAL');
                break;
            case $allStatus['APPROVALING']: // 2审批中
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_ING');
                break;
            case $allStatus['REJECTED']: // 3已驳回
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_STATUS_REJECTED');
                break;
            case $allStatus['REVOKE']: // 4已撤回
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_BACK');
                break;
            default:
                $statusDes = xphp_get_lang('WEB_PLATFORM_INDUSTRY_REPORT_INGS') . "{$nowUserName}"
                    . xphp_get_lang('WEB_INDUSTRY_APPROVE');
                break;
        }

        return $statusDes;
    }
}
