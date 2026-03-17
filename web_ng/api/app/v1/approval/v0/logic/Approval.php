<?php

namespace app\v1\approval\v0\logic;

use app\v1\common\logic\Base;

/**
 * note          审批流 controller
 * @author       wuyihang@vinchin.com
 * @date         2024/7/30 14:22
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Approval extends Base
{
    /**
     * 获取审批流列表
     * @param array $params 参数
     * @return array
     */
    public function getApprovalList($params)
    {
        $offset = $params['offset'];
        $limit = $params['limit'];

        $sql = 'select ap.approval_uuid, ap.approval_pid, ap.name, ap.depth, ap.user_uuid, ap.create_time,
                    ap.update_time, ap.status, ap.action, ap.content, 
                bu.user_name as create_name, ac.approval_classfy_uuid, ac.name as classify_name
                from approval_list ap
                left join bd_user bu on ap.user_uuid = bu.user_uuid
                left join approval_classfy ac on ap.approval_pid = ac.approval_classfy_uuid ';
        $sqlCount = 'select count(*) as total from approval_list ap ';
        $sqlParams = [];
        $where = '';
        if (!empty($params['search'])) {
            $where = ' where ap.name like ? ';
            $sqlParams = ['%' . $params['search'] . '%'];
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users('');
            $sqlNew = " ap.user_uuid in ({$userUuidSql}) ";
            $where .= (empty($where) ? ' where ' : ' and ') . $sqlNew;
        }

        $sqlCount .= $where;
        $count = $this->dbSelect($sqlCount, $sqlParams);
        if (empty($count[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }
        $sql .= $where;
        if (!empty($params['limit'])) {
            $sql .= ' limit ? , ? ';
            $sqlParams = array_merge($sqlParams, [$offset, $limit]);
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $item = [xphp_get_user_info()['userName']];
        $this->systemLog('PT_INDUSTRY_APPROVAL_LOOK', $item);
        $records = [];
        foreach ($data as $d) {
            $records[] = array(
                'name' => $d['name'],
                'approval_uuid' => $d['approval_uuid'],
                'classify_uuid' => $d['approval_pid'],
                'classify_name' => $d['classify_name'],
                'depth' => $d['depth'],
                'create_uuid' => $d['user_uuid'],
                'create_name' => $d['create_name'],
                'create_time' => $d['create_time'] ?? '--',
                'update_time' => $d['update_time'] ?? '--',
                'status' => $d['status'],
                'action' => $d['action'],
                'content' => json_decode($d['content'])
            );
        }

        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 添加审批流
     * @param array $params 参数
     * @return boolean
     */
    public function addApproval($params)
    {
        $uuid = xphp_uuid();
        $pid = $params['pid'] ?? '';
        $name = $params['name'];
        $depth = 1; // 默认层级创建时为1
        $depth = intval($params['depth']);
        $createUuid = xphp_get_user_info()['userUuid'];
        $createTime = date(xphp_get_config('special', 'dateformat'));
        $updateTime = $createTime;
        $status = v1_parse_bool_to_flag($params['status']);
        $action = intval($params['action']);
        $content = json_encode($params['content']);
        $sql = 'insert into approval_list 
                (approval_uuid, approval_pid, name, depth, user_uuid, create_time, update_time, status, action, content) 
                values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $sqlParams = [$uuid, $pid, $name, $depth, $createUuid, $createTime, $updateTime, $status, $action, $content];
        // dump($sqlParams);
        $result = $this->dbExec($sql, $sqlParams);

        $item = [xphp_get_user_info()['userName'], $name];
        $this->systemLog('PT_INDUSTRY_APPROVAL_ADD', $item);

        return $result;
    }

    /**
     * 修改审批流
     * @param array $params 参数
     * @return boolean
     */
    public function modifyApproval($params)
    {
        $pid = $params['pid'];
        $uuid = $params['approval_uuid'];

        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from approval_list where approval_uuid = '{$uuid}'"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        $name = $params['name'];
        $depth = $params['depth']; // 默认层级创建时为1
        $updateTime = date(xphp_get_config('special', 'dateformat'));
        $status = intval($params['status']);
        $action = intval($params['action']);
        $content = json_encode($params['content']);
        $sql = 'update approval_list set
                         approval_pid = ?, name = ?, depth = ?, update_time =?, status = ?, action = ?, content = ?
                    where approval_uuid = ?';
        $sqlParams = [$pid, $name, $depth, $updateTime, $status, $action, $content, $uuid];
        $result = $this->dbExec($sql, $sqlParams);

        $item = [xphp_get_user_info()['userName'], $name];
        $this->systemLog('PT_INDUSTRY_APPROVAL_EDIT', $item);

        return $result;
    }

    /**
     * 启用/禁用审批流
     * @param array $params 参数
     * @return boolean
     */
    public function enableOrDisableApproval($params)
    {

        $enableFlag = $params['enable_flag'];
        $uuid = "('" . implode("','", $params['uuids']) . "')";

        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from approval_list where approval_uuid in {$uuid}"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        $flag = xphp_get_config('app', 'FLAG');
        $sql = "update approval_list set status = ? where approval_uuid in {$uuid}";
        $approval = $this->dbSelect(
            "select GROUP_CONCAT(name) names from approval_list where approval_uuid in {$uuid}"
        );
        if ($enableFlag == $flag['SET']) {
            //启用操作
            $sqlParams = [$flag['SET']];
            // dump($sql, $sqlParams);
            $result = $this->dbExec($sql, $sqlParams);
            $item = [xphp_get_user_info()['userName'], $approval[0]['names']];
            $this->systemLog('PT_INDUSTRY_APPROVAL_ENABLE', $item);
            return $result;
        } elseif ($enableFlag == $flag['UNSET']) {
            //禁用操作
            $sqlParams = [$flag['UNSET']];
            // dump($sql, $sqlParams);
            $result = $this->dbExec($sql, $sqlParams);
            $item = [xphp_get_user_info()['userName'], $approval[0]['names']];
            $this->systemLog('PT_INDUSTRY_APPROVAL_DISABLE', $item);
            return $result;
        }
        return false;
    }

    /**
     * 删除审批流
     * @param array $params 参数
     * @return mixed
     */
    public function delApproval($params)
    {
        $uuidList = $params['approval_list'];
        $uuidList = "('" . implode("','", $uuidList) . "')";

        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from approval_list where approval_uuid in {$uuidList}"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        $sql1 = "select report_uuid from industry_report where approval_uuid in {$uuidList}";
        $data = $this->dbSelect($sql1);
        $opName = xphp_get_lang('UI_GMP_APPROVAL_DELETE');
        if (!empty($data)) {
            return $this->muOpResult(false, $opName, xphp_get_lang('UI_GMP_APPROVAL_DELETE_TIP'), 'warning');
        }
        $approval = $this->dbSelect(
            "select GROUP_CONCAT(name) names from approval_list where approval_uuid in {$uuidList}"
        );
        $sql = "delete from approval_list where approval_uuid in {$uuidList}";

        $result = $this->dbExec($sql);
        $item = [xphp_get_user_info()['userName'], $approval[0]['names']];
        $this->systemLog('PT_INDUSTRY_APPROVAL_DELETE', $item);
        return $result;
    }

    /**
     * 获取审批分类列表
     * @param array $params 参数数组，可包含search字段用于模糊搜索
     * @return array 包含审批分类详细信息的数组
     */
    public function getClassifyList($params)
    {
        $offset = $params['offset'];
        $limit = $params['limit'];
        $sql = "select ac.approval_classfy_uuid, ac.name, ac.remark, ac.user_uuid,
                        ac.create_time, ac.status, bu.user_name
                    from approval_classfy ac
                    left join bd_user bu on ac.user_uuid = bu.user_uuid";
        $sqlCount = "select count(*) as total from approval_classfy";

        $sqlParams = [];
        $where = '';
        if (!empty($params['search'])) {
            $where .= ' where name like ?';
            $sqlParams = array('%' . $params['search'] . '%');
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users('');
            $sqlNew = " ac.user_uuid in ({$userUuidSql}) ";
            $where .= (empty($where) ? ' where ' : ' and ') . $sqlNew;
        }
        $sqlCount .= $where;
        $count = $this->dbSelect($sqlCount, $sqlParams);
        if (empty($count[0]['total'])) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $sql .= $where;
        if (!empty($limit)) {
            $sql .= ' limit ?, ?';
            $sqlParams = [$offset, $limit];
        }

        $data = $this->dbSelect($sql, $sqlParams);
        $records = [];

        $item = [xphp_get_user_info()['userName']];
        $this->systemLog('PT_INDUSTRY_APPROVAL_GROUP_LOOK', $item);

        foreach ($data as $d) {
            $records[] = array(
                'classify_uuid' => $d['approval_classfy_uuid'],
                'name' => $d['name'],
                'remark' => $d['remark'],
                'create_uuid' => $d['user_uuid'],
                'create_name' => $d['user_name'],
                'create_time' => $d['create_time'] ?? '--',
                'status' => $d['status']
            );
        }

        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 添加审批分类信息
     * @param array $params 包含分类名称和备注的数组
     * @return bool 插入操作是否成功的布尔值
     */
    public function addClassify($params)
    {
        $uuid = xphp_uuid();
        $name = $params['name'];
        $remark = $params['remark'];
        $createUuid = xphp_get_user_info()['userUuid'];
        $createTime = date(xphp_get_config('special', 'dateformat'));
        $status = v1_parse_bool_to_flag($params['status']);
        $sql = 'insert into approval_classfy 
                (approval_classfy_uuid, name, remark, user_uuid, create_time, status) 
                values (?, ?, ?, ?, ?, ?)';
        $sqlParams = [$uuid, $name, $remark, $createUuid, $createTime, $status];
        $result = $this->dbExec($sql, $sqlParams);
        $item = [xphp_get_user_info()['userName'], $name];
        $this->systemLog('PT_INDUSTRY_APPROVAL_GROUP_ADD', $item);
        return $result;
    }

    /**
     * 修改分类信息
     * @param array $params 包含修改分类所需信息的数组，包括分类UUID、分类名称、备注和状态
     * @return boolean 修改操作的结果
     */
    public function modifyClassify($params)
    {
        $uuid = $params['approval_classfy_uuid'];

        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from approval_classfy where approval_classfy_uuid = '{$uuid}'"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        $name = $params['name'];
        $remark = $params['remark'];
        $status = intval($params['status']);
        $sql = 'update approval_classfy set name = ?, remark = ?, status= ? where approval_classfy_uuid = ?';
        $sqlParams = [$name, $remark, $status, $uuid];
        $result = $this->dbExec($sql, $sqlParams);
        $item = [xphp_get_user_info()['userName'], $name];
        $this->systemLog('PT_INDUSTRY_APPROVAL_GROUP_EDIT', $item);

        return $result;
    }

    /**
     * 启用或禁用分类
     * @param array $params 参数数组，包含启用标志和UUID列表
     * @return boolean 返回操作结果，成功或失败
     */
    public function enableOrDisableClassify($params)
    {
        $enableFlag = $params['enable_flag'];
        $uuid = "('" . implode("','", $params['uuids']) . "')";

        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from approval_classfy where approval_classfy_uuid in {$uuid}"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        $flag = xphp_get_config('app', 'FLAG');
        $sql = "update approval_classfy set status = ? where approval_classfy_uuid in {$uuid}";
        $approval = $this->dbSelect(
            "select GROUP_CONCAT(name) names from approval_classfy where approval_classfy_uuid in {$uuid}"
        );
        if ($enableFlag == $flag['SET']) {
            //启用操作
            $sqlParams = [$flag['SET']];
            $result = $this->dbExec($sql, $sqlParams);
            $item = [xphp_get_user_info()['userName'], $approval[0]['names']];
            $this->systemLog('PT_INDUSTRY_APPROVAL_GROUP_ENABLE', $item);
            return $result;
        } elseif ($enableFlag == $flag['UNSET']) {
            //禁用操作
            $sqlParams = [$flag['UNSET']];
            $result = $this->dbExec($sql, $sqlParams);
            $item = [xphp_get_user_info()['userName'], $approval[0]['names']];
            $this->systemLog('PT_INDUSTRY_APPROVAL_GROUP_DISABLE', $item);
            return $result;
        }

        return false;
    }

    /**
     * 删除审批分类
     * 本函数接受一个参数数组，从中提取出分类UUID列表，并将其转换为适合SQL语句的格式，
     * 然后执行删除操作，从approval表中删除指定的审批分类记录
     * @param array $params 包含分类列表的参数数组，数组中应包含键名为'classify_list'的项
     * @return mixed 删除操作的结果，通常是影响的行数或操作成功与否的布尔值，也可以是失败的提示string
     */
    public function delApprovalClassify($params)
    {
        $uuidList = $params['classify_list'];
        $uuidList = "('" . implode("','", $uuidList) . "')";

        // 权限判断
        $info = $this->dbSelect(
            "select user_uuid from approval_classfy where approval_classfy_uuid in {$uuidList}"
        );
        $this->checkAuthByUserUuid(implode(',', array_column($info, 'user_uuid')), '');

        $opName = xphp_get_lang('UI_GMP_APPROVAL_CLASSIFY_DELETE');
        if ($this->checkClassify($uuidList)) {
            return $this->muOpResult(false, $opName, xphp_get_lang('UI_GMP_APPROVAL_CLASSIFY_DELETE_TIP'), 'warning');
        }
        $approval = $this->dbSelect(
            "select GROUP_CONCAT(name) names from approval_classfy where approval_classfy_uuid in {$uuidList}"
        );
        $sql = "delete from approval_classfy where approval_classfy_uuid in {$uuidList}";
        $result = $this->dbExec($sql);
        $item = [xphp_get_user_info()['userName'], $approval[0]['names']];
        $this->systemLog('PT_INDUSTRY_APPROVAL_GROUP_DELETE', $item);
        return $result;
    }

     /**
     * 检查UUID列表中的分类是否创建了审批流，创建了则不能删除分类
     * @param array $uuidList UUID列表，用于查询数据库中是否存在对应的分类数据
     * @return bool 如果UUID列表中有相关的分类数据存在，则返回true；否则返回false
     */
    private function checkClassify($uuidList)
    {
        $sql = "select count(*) as total from approval_list where approval_pid in {$uuidList}";
        $result = $this->dbSelect($sql);
        if ($result[0]['total'] == 0) {
            return false;
        }

        return true;
    }
}
