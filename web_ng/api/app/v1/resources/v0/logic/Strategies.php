<?php
/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 资源 全局策略 logic
 * @Date: 2024-01-15 17:31:12
 * @LastEditTime: 2025-08-20 11:59:59
 * @Version: 2.0
 * @copyright: Copyright 2023 vinchin.com
 */

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;

class Strategies extends Base
{
    /**
     * @description: 策略管理---获取策略组列表
     * @param {*} $params 参数
     * @return array
     */
    public function getStrategyList($params)
    {
        //开始页
        $start = $params['offset'];
        //每页数量
        $length = $params['limit'];
        // 查询策略组
        $sql = "SELECT bsg.strategy_group_uuid, bsg.strategy_group_name, bsg.create_time, bsg.strategy_group_type, bsg.remark, bsg.extra_info, bu.user_name,bu.user_uuid FROM bd_strategy_group bsg, bd_user bu WHERE bu.user_uuid = bsg.user_uuid ";
        // 查询策略组个数
        $sqlCount = "SELECT COUNT(bsg.strategy_group_id) AS total FROM bd_strategy_group bsg, bd_user bu WHERE bu.user_uuid = bsg.user_uuid ";
        $sqlParams = [];
        // 权限判断
        if (v1_auth_need_check_look()) {
            $user_uuid_arr = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['source']);
            $sql .= " AND bsg.user_uuid IN ({$user_uuid_arr})";
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortArr = array(
                'strategy_name' => 'bsg.strategy_group_name',
                'strategy_type' => 'bsg.strategy_group_type',
                'update_time' => 'bsg.create_time',
                'update_user' => 'bu.user_name',
            );
            if (!empty($sortArr[$params['sort']]) && in_array($params['order'], ['asc', 'desc'])) {
                $sql .= " order by {$sortArr[$params['sort']]} {$params['order']} ";
            }
        }
        $sql .= ' limit ?, ?';
        $data = $this->dbSelect($sql, array_merge($sqlParams, [$start, $length]));
        $count = $this->dbSelect($sqlCount, $sqlParams);
        $records = array();
        $records['rows'] = array();

        if (!empty($data)) {
            foreach ($data as $d) {
                $records['rows'][] = array(
                    'uuid' => $d['strategy_group_uuid'], //策略uuid
                    'strategy_name' => $d['strategy_group_name'], //策略组名称
                    'strategy_type' => $d['strategy_group_type'], //策略组类型
                    'update_time' => $d['create_time'], //更新时间
                    'update_user' => $d['user_name'], //作者
                    'strategy_marks' => $d['remark'], //备注
                    'strategy_actions' => array(1, 2), //暂未使用仅仅占位置
                    'strategy_details' => json_decode($d['extra_info'], true), //策略详细信息
                    'related_task' => $this->getTaskInStrategy($d['strategy_group_uuid']), //策略关联任务
                    'user_uuid' => $d['user_uuid'],
                );
            }
        }
        $records['total'] = $count[0]['total'] ?? 0;
        return $records;
    }

    /**
     * @description: 策略管理---获取策略组相关任务
     * @param {*} $strategyID
     */
    private function getTaskInStrategy($strategyID)
    {
        $sql = "SELECT task_name FROM bd_task WHERE strategy_group_uuid = ? ";
        $data = $this->dbSelect($sql, array($strategyID));
        $info = '';
        if (!empty($data)) {
            foreach ($data as $d) {
                $info .= $d['task_name'] . '<br>';
            }
        } else {
            $info = xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
        }
        return $info;
    }

    /**
     * @description: 策略管理---获取可分发的所有任务列表
     * @param {*} $params
     */
    public function getTaskList($params)
    {
        $strategyUUid = $params['strategy_uuid'];
        // 起始页
        $start = $params['offset'];
        // 每页数量
        $length = $params['limit'];
        $module_type = $params['module_type'];
        $module = $params['module_type'];
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $fsType = xphp_get_config('module', 'SUBMODULE_TYPE');
        $vmType = xphp_get_config('module', 'VM_SUB_MODULE');
        $sqlParams = array();
        $sql = "SELECT bt.task_name, bt.task_uuid, bt.task_type, unix_timestamp(bt.create_time) create_time, bt.module_type, bt.sub_module_type, bt.task_status, bt.strategy_group_uuid FROM bd_task bt WHERE bt.task_type IN (" . $taskType['BACKUP'] . ',' . $taskType['OS_BACKUP'] . ',' . $taskType['DB_BACKUP'] . ',' . $taskType['NAS_BACKUP'] . ") AND bt.task_status = ? AND bt.module_type = ?";
        $sqlCount = "SELECT COUNT(bt.task_uuid) AS total FROM bd_task bt WHERE bt.task_type IN (" . $taskType['BACKUP'] . ',' . $taskType['OS_BACKUP'] . ',' . $taskType['DB_BACKUP'] . ',' . $taskType['NAS_BACKUP'] . ") AND bt.task_status = ? AND bt.module_type = ?";
        switch ($params['module_type']) {
            case 2:
                $sql .= " AND bt.sub_module_type = {$vmType['VM']}";
                $sqlCount .= " AND bt.sub_module_type = {$vmType['VM']}";
                $module = $moduleType['VM'];
                break;
            case 17:
                $sql .= " AND bt.sub_module_type = {$vmType['PUBLIC_CLOUD']}";
                $sqlCount .= " AND bt.sub_module_type = {$vmType['PUBLIC_CLOUD']}";
                $module = $moduleType['VM'];
                break;
            case 22:
                $sql .= " AND bt.sub_module_type = {$vmType['PRIVATE_CLOUD']}";
                $sqlCount .= " AND bt.sub_module_type = {$vmType['PRIVATE_CLOUD']}";
                $module = $moduleType['VM'];
                break;
            case 3:
                $sql .= " AND bt.sub_module_type = '{$fsType['FS']}'";
                $sqlCount .= " AND bt.sub_module_type = '{$fsType['FS']}'";
                $module = $moduleType['FS'];
                break;
            case 999:
                $sql .= " AND bt.sub_module_type = {$fsType['OBS']}";
                $sqlCount .= " AND bt.sub_module_type = {$fsType['OBS']}";
                $module = $moduleType['FS'];
                break;
            case 998:
                $sql .= " AND bt.sub_module_type = {$fsType['HADOOP']}";
                $sqlCount .= " AND bt.sub_module_type = {$fsType['HADOOP']}";
                $module = $moduleType['FS'];
                break;
            case 5:
                $sql .= " AND bt.sub_module_type = 1";
                $sqlCount .= " AND bt.sub_module_type = 1";
                $module = $moduleType['OS'];
                break;
            case 6:
                $sql .= " AND bt.sub_module_type = 0";
                $sqlCount .= " AND bt.sub_module_type = 0";
                $module = $moduleType['OS'];
                break;
        }
        // 权限判断
        if (v1_auth_need_check_look()) {
            $user_uuid_arr = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['source']);
            $sql .= " AND bt.user_uuid IN ({$user_uuid_arr})";
        }
        $sql .= " GROUP BY bt.task_uuid ORDER BY bt.create_time desc LIMIT {$start}, {$length}";
        $sqlParams = array(xphp_get_config('task', 'TASKSTATUS')['STOPPED'], $module);
        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlParams);
        $records = array();
        $records['rows'] = array();
        $ptDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        foreach ($data as $d) {
            $records['rows'][] = array(
                'task_name' => $d['task_name'], //任务名
                'module_type' => $module_type, //模块
                'module_type_des' => $this->getModuleType($d['module_type'], $d['sub_module_type']), //模块
                'create_time' => $this->parseDate($d['create_time']), //创建时间
                'strategy_diff' => $params['diffName'], //策略差异
                'diff_info' => xphp_get_lang('UI_PUBLIC_DETAIL'), //详情
                'task_uuid' => $d['task_uuid'], //任务uuid
                'uuid' => $d['task_uuid'],
                'type' => intval($d['task_type']), //任务类型
                'task_status' => $ptDes[$d['task_status']], // 任务状态
                'related' => $d['strategy_group_uuid'] == $strategyUUid ? true : false
            );
        }
        $records['total'] = $count[0]['total'] ?? 0;

        return  $records;
    }

    /**
     * 获取模块类型
     * 文件和虚拟机的子模块最终需要使用文件和虚拟机的模块类型来查询
     * @param mixed $module
     * @param mixed $sub
     * @return mixed
     */
    private function getModuleType($module, $sub)
    {
        $MODULE = xphp_get_config('module', 'MODULE_TYPE');
        $FS_DES = xphp_get_desc('Pf', 'SUB_MODULE_DES_FS');
        $VM_DES = xphp_get_desc('Pf', 'SUB_MODULE_DES_VM');
        $MODULE_DES = xphp_get_desc('Pf', 'MODULE_TYPE_DES');
        $des = $MODULE_DES[$module];
        // 虚拟机子模块
        if ($module == $MODULE['VM']) {
            $des = $VM_DES[$sub];
        }
        // 文件子模块
        if ($module == $MODULE['FS']) {
            $des = $FS_DES[$sub];
        }
        // 区分整机和卷
        if ($module == $MODULE['OS']) {
            if ($sub == 1) {
                $des = xphp_get_lang('UI_PLATFORM_MACHINE_COMPLETE_BACKUP');
            } else {
                $des = xphp_get_lang('UI_PLATFORM_MACHINE_REEL_BACKUP');
            }
        }
        return $des;
    }

    /**
     * @description: 删除策略---删除时检查是否有关联任务并返回一颗任务树，没有则删除任务
     * @param {*} $params
     */
    public function checkTaskStrategy($params)
    {
        $uuidList = $params['uuid_list'];
        $uuidDes =  implode("','", $uuidList);
        $sql = "SELECT bt.task_name, bsg.strategy_group_name, bt.task_uuid, bsg.strategy_group_uuid FROM bd_task bt, bd_strategy_group bsg  WHERE bsg.strategy_group_uuid = bt.strategy_group_uuid AND bsg.strategy_group_uuid IN ('" . $uuidDes . "')";
        $data = $this->dbSelect($sql);
        // 没有关联任务直接删除
        if (empty($data)) {
            return $this->deleteStrategy($params);
        }
        $node = array();
        $strategy = array();
        $task = array();
        // 返回关联任务
        foreach ($data as $d) {
            $task_uuid = $d['task_uuid'];
            $strategy_uuid = $d['strategy_group_uuid'];
            $task_name = $d['task_name'];
            $strategy_name = $d['strategy_group_name'];

            //检查添加策略
            if (!in_array($strategy_uuid, $strategy)) {
                $node[] = array(
                    'id' =>  $strategy_uuid,
                    'pId' => 0,
                    'name' => $strategy_name,
                    'title' => $strategy_name,
                    'open' => true,
                    'nocheck' => true,
                    'type' => -1,
                    'icon' => './img/platform/strategy.png',
                    'task_uuid' => $task_uuid,
                    'strategy_uuid' => $strategy_uuid
                );
                $strategy[] = $strategy_uuid;
            }

            //检查添加任务
            if (!in_array($task_uuid, $task)) {
                $node[] = array(
                    'id' =>  $task_uuid,
                    'pId' => $strategy_uuid,
                    'name' => $task_name,
                    'title' => $task_name,
                    'nocheck' => true,
                    'type' => 0,
                    'icon' => './img/platform/flag.png',
                    'task_uuid' => $task_uuid,
                    'strategy_uuid' => $strategy_uuid
                );
                $task[] = $task_uuid;
            }
        }
        return $node;
    }
    private function getStrategyTypeDes($module)
    {
        $moduleDes = '';
        switch ($module) {
            case 2:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_VM');
                break;
            case 3:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_FS');
                break;
            case 4:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_DB');
                break;
            case 5:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_OS');
                break;
            case 11:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_NAS');
                break;
            case 17:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_PUBLIC_CLOUD');
                break;
            case 22:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_PUBLIC_CLOUD');
                break;
            case 14:
                $moduleDes = xphp_get_lang('WEB_PLATFORM_DES_M365');
                break;
            case 998:
                $moduleDes = xphp_get_lang('UI_PLATFORM_HADOOP_HDFS');
                break;
            case 999:
                $moduleDes = xphp_get_lang('UI_PLATFORM_OBS_STORAGE');
                break;
        }
        return $moduleDes;
    }

    /**
     * @description: 删除策略---删除单个策略组
     * @param {*} $params
     */
    public function deleteStrategy($params)
    {
        $uuidList = $params['uuid_list'];
        $uuidDes = implode("','", $uuidList);

        foreach ($uuidList as $id) {
            $sql = "SELECT strategy_group_name, strategy_group_type, user_uuid FROM bd_strategy_group WHERE strategy_group_uuid = ?";
            $strategyDetail = $this->dbSelect($sql, [$id]) ?? [];
            $descriptionParams = array($this->getStrategyTypeDes($strategyDetail[0]['strategy_group_type']), $strategyDetail[0]['strategy_group_name']);
            // 操作权限判断
            $this->checkAuthByUserUuid($strategyDetail[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);
            // 删除策略
            $sql = "DELETE FROM bd_strategy_group WHERE strategy_group_uuid = '" . $id . "'";
            $result = $this->dbExec($sql);
            if ($result) {
                //取消任务与策略关联
                $this->systemLog('SYSTEM_LOG_DESC_KEY_DELETE_STRATEGY', $descriptionParams);
                $strategy = '';
                $sql = "UPDATE bd_task SET strategy_group_uuid = ? WHERE strategy_group_uuid IN ('" . $uuidDes . "')";
                $result = $this->dbExec($sql, array($strategy));
            }
        }
        return $result;
    }

    /**
     * @description: 添加策略---得到策略组名称
     * @param {*} $params
     */
    public function getStrategyName()
    {
        $strategyName = xphp_get_lang('UI_GLOBAL_STRATEGY_GROUP');
        $name = $this->getValidName($strategyName);
        $info = array(
            'name' => $name
        );
        return $info;
    }

    /**
     * @description: 添加策略---获取一个可用的策略组名称
     * @param {*} $strategyName
     */
    public function getValidName($strategyName)
    {
        // 名称模板
        $oldName = $strategyName;
        // 名称模板后加数字，被占用则继续+1
        for ($i = 1; $i < 1000; $i++) {
            $strategyName .= $i;
            $sql = "SELECT strategy_group_id FROM bd_strategy_group WHERE strategy_group_name = ?";
            $data = $this->dbSelect($sql, array($strategyName));
            // 名字不存在则返回名称
            if (empty($data)) {
                return $strategyName;
            }
            $strategyName = $oldName;
        }
        return $oldName;
    }

    /**
     * @description: 添加策略---新建全局策略
     * @param {*} $params
     */
    public function addStrategy($params)
    {
        $user = xphp_get_user_info();
        // 策略信息
        $strategy = $params['strategyInfo'];
        // 策略名称检查
        $strategyName = htmlspecialchars_decode($params['strategy_name']);
        $this->checkStrategyName($strategyName);
        // 备注
        $remark = $params['remark'];
        // 策略类型
        $strategyType = $params['strategy_type'];
        $createTime = $params['create_time'];
        $useruuid =  $user['userUuid'];
        $sqlParams = array(xphp_uuid(), $strategyName, $strategyType, $useruuid, $createTime, $remark, json_encode($strategy));
        // 插入新的策略
        $sql = "INSERT bd_strategy_group (strategy_group_uuid, strategy_group_name, strategy_group_type, user_uuid, create_time, remark, extra_info) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $result = $this->dbExec($sql, $sqlParams);
        // 编写日志信息
        $this->systemLog('SYSTEM_LOG_DESC_KEY_ADD_STRATEGY', array($this->getStrategyTypeDes($strategyType), $strategyName));
        return $this->muOpResult($result, xphp_get_lang('UI_GLOBAL_STRATEGY_ADD'));
    }

    /**
     * @description: 添加策略---检查策略组名称是否可用
     * @param {*} $strategyName
     */
    private function checkStrategyName($strategyName)
    {
        $user = xphp_get_user_info();
        $sql = "SELECT COUNT(strategy_group_uuid) AS total FROM bd_strategy_group WHERE strategy_group_name = ? AND user_uuid = ?";
        $data = $this->dbSelect($sql, array($strategyName, $user['userUuid']));
        if (intval($data[0]['total']) != 0) {
            exit($this->muOpResult(false, xphp_get_lang('UI_GLOBAL_STRATEGY_NAME'), xphp_get_lang('UI_GLOBAL_STRATEGY_NAME_EXIST'), 'warning'));
        }
    }

    /**
     * @description: 修改策略---获取旧的策略信息
     * @param {*} $params
     */
    public function getOldStrategyInfo($params)
    {
        // 策略uuid
        $uuid = $params['uuid'];
        // 查询策略信息
        $sql = "SELECT strategy_group_name, strategy_group_type, remark, extra_info FROM bd_strategy_group WHERE strategy_group_uuid = ? ";
        $data = $this->dbSelect($sql, array($uuid));
        $info = array(
            // 策略名称
            'strategy_name' => $data[0]['strategy_group_name'],
            // 策略类型
            'strategy_type' => intval($data[0]['strategy_group_type']),
            // 备注
            'remark' => $data[0]['remark'],
            // 策略信息
            'strategyInfo' => $data[0]['extra_info'],
        );
        return $info;
    }

    /**
     * @description: 修改策略---检查策略是否被占用
     * @param {*} $params
     */
    public function editStrategyCheck($params)
    {
        // 需要检查的策略组uuid
        $strategy_uuid = $params['check_uuid'];
        $sql = "SELECT bt.task_uuid, bsg.extra_info FROM bd_task bt, bd_strategy_group bsg WHERE bsg.strategy_group_uuid = bt.strategy_group_uuid AND bsg.strategy_group_uuid = ? AND bt.task_status = ?";
        // 获取使用策略的任务uuid
        $data = $this->dbSelect($sql, array($strategy_uuid, xphp_get_config('task', 'TASKSTATUS')['STOPPED']));
        $taskIdList = array();
        // 获取任务uuid数组
        if (!empty($data)) {
            foreach ($data as $d) {
                $taskIdList[] = $d['task_uuid'];
            }
        }
        // 得到任务数量
        $count = count($taskIdList);
        $info = array();
        if ($count == 0) {
            return $info;
        }
        $info = array(
            'task_count' => $count
        );
        return $info;
    }

    /**
     * @description: 修改策略---修改策略
     * @param {*} $params
     */
    public function editStrategy($params)
    {
        // 策略uuid
        $strategy_uuid = $params['strategy_uuid'];
        // 策略组名称
        $strategyName = rawurldecode($params['strategy_name']);
        // 策略组类型
        $strategyType = $params['strategy_type'];
        // 备注
        $remark = $params['remark'];
        // 策略组信息
        $strategyInfo = $params['strategyInfo'];
        // 需要分发的uuid数组
        $taskUUids = $params['taskuuids'];
        $createTime = $params['create_time'];

        // 操作权限判断
        $sql = "select user_uuid from bd_strategy_group where strategy_group_uuid = ? ";
        $data = $this->dbSelect($sql, [$params['strategy_uuid']]);
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);

        // 修改策略
        $sqlParams = array($strategyName, $remark, $createTime, json_encode($strategyInfo), $strategy_uuid);
        $sql = "UPDATE bd_strategy_group SET strategy_group_name = ?, remark = ?, create_time = ?, extra_info = ? WHERE strategy_group_uuid = ? ";
        $result = $this->dbExec($sql, $sqlParams);

        // 分发策略到关联任务
        if (!empty($taskUUids)) {
            foreach ($taskUUids as $task_uuid) {
                $result = $result && $this->dispenseStrategyToJob($strategyInfo, $task_uuid, $strategy_uuid, $strategyType);
            }
            // 分发失败就退出
            if (!$result) {
                return $this->muOpResult(false, xphp_get_lang('UI_GLOBAL_STRATEGY_DISPENSE'), '', 'warning');
            }
        }
        $this->systemLog('SYSTEM_LOG_DESC_KEY_EDIT_STRATEGY', array($this->getStrategyTypeDes($strategyType), $strategyName));
        return $this->muOpResult($result, xphp_get_lang('UI_GLOBAL_STRATEGY_EDIT'));
    }

    /**
     * @description: 修改策略---获取策略的id并进行分发
     * @param {*} $info
     * @param {*} $task_uuid
     * @param {*} $strategyGroupUUid
     * @param {*} $strategyType
     */
    private function dispenseStrategyToJob($info, $task_uuid, $strategyGroupUUid, $strategyType)
    {
        // 获取任务的策略id
        $sql = "SELECT bt.task_type, bt.strategy_id, dt.db_type FROM bd_task bt LEFT JOIN db_task dt on dt.task_uuid = bt.task_uuid WHERE bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $strategyID = $data[0]['strategy_id'];
        $subType = $data[0]['db_type'];
        // 分发策略
        return $this->dispenseBackupJob($info, $task_uuid, $strategyID, $strategyGroupUUid, $strategyType, $subType);
    }

    /**
     * @description: 修改策略---分发策略到备份任务
     * @param {*} $info              策略信息
     * @param {*} $task_uuid          任务uuid
     * @param {*} $strategyID        策略id
     * @param {*} $strategyGroupUUid 策略uuid
     * @param {*} $strategyType      策略类型
     */
    public function dispenseBackupJob($info, $task_uuid, $strategyID, $strategyGroupUUid, $strategyType, $subType = null)
    {
        $DB_NO_INCR = [1, 5, 6, 7, 8, 10, 11, 12];
        // 无差异备份
        $DB_NO_DIFF = [3, 5, 6, 7, 8, 9, 10, 11, 12];
        //开始事务
        $this->dbBeginTransaction();
        $result = true;
        $sqlOld = "SELECT strategy_type, mode FROM bd_time_strategy WHERE strategy_id = ?";
        $time_strategy_backup_type = 3;
        $oldTimeStrategy = $this->dbSelect($sqlOld, array($strategyID));
        $count = count((array)$oldTimeStrategy);
        //时间策略
        if (!empty($info['time'])) {
            //更新时间策略表 bd_time_strategy
            $sql = "DELETE FROM bd_time_strategy WHERE strategy_id = ?";
            $result =  $this->dbExec($sql, array($strategyID));
            $timeStrategy = $info['time']['timeInfo'];
            if ('oncetime' == $info['time']['type']) {
                $time_strategy_backup_type = 2;
                //一次性策略
                $sql = "INSERT bd_time_strategy (strategy_id, mode, strategy_type, start_time, task_uuid) VALUES (?, ?, ?, ?, ?)";
                $sqlParams = array($strategyID, xphp_get_config('task', 'BACKUP_MODE')['FULL'], 4, $info['time']['data'], $task_uuid);
                $result = $result && $this->dbExec($sql, $sqlParams);
                // // 修改下一次执行时间
                $sql = "DELETE FROM bd_strategy WHERE strategy_id = ?";
                $result = $result && $this->dbExec($sql, array($strategyID));
                $sql = "INSERT bd_strategy (strategy_id, next_start_time, next_strategy_type, next_mode) VALUES (?, ?, ?, ?)";
                $result = $result && $this->dbExec($sql, array($strategyID, $info['time']['data'], 4, xphp_get_config('task', 'BACKUP_MODE')['FULL']));
            } elseif ('strategy' == $info['time']['type']) {
                $time_strategy_backup_type = 1;
                //时间策略
                if (!empty($timeStrategy['fullInfo'])) {
                    //完全策略
                    $modeType = xphp_get_config('task', 'BACKUP_MODE')['FULL'];

                    //单独处理完全备份修改
                    $sql = "SELECT strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time FROM bd_time_strategy WHERE strategy_id = ?";
                    $data = $this->dbSelect($sql, array($strategyID));
                    if (empty($data[0])) {
                        //如果数据库没有,直接插入,
                        $result = $result && $this->insertTimeStrategy($task_uuid, $strategyGroupUUid, $strategyID, $modeType, $timeStrategy['fullInfo']);
                    } else {
                        $sql = "DELETE FROM bd_time_strategy WHERE strategy_id = ? AND mode = ?";
                        $result = $result && $this->dbExec($sql, array($strategyID, xphp_get_config('task', 'BACKUP_MODE')['FULL']));

                        $result = $result && $this->insertTimeStrategy($task_uuid, $strategyGroupUUid, $strategyID, $modeType, $timeStrategy['fullInfo']);
                    }
                } else {
                    $sql = "DELETE FROM bd_time_strategy WHERE strategy_id = ? AND mode = ?";
                    $result = $result && $this->dbExec($sql, array($strategyID, xphp_get_config('task', 'BACKUP_MODE')['FULL']));
                }
                if (!empty($timeStrategy['incrInfo']) && !in_array($subType, $DB_NO_INCR)) {
                    //增量策略
                    $modeType = xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL'];
                    $result = $result && $this->insertTimeStrategy($task_uuid, $strategyGroupUUid, $strategyID, $modeType, $timeStrategy['incrInfo']);
                }
                if (!empty($timeStrategy['diffInfo']) && !in_array($subType, $DB_NO_DIFF)) {
                    //差异策略
                    $modeType = xphp_get_config('task', 'BACKUP_MODE')['DIFFERENTIAL'];
                    $result = $result && $this->insertTimeStrategy($task_uuid, $strategyGroupUUid, $strategyID, $modeType, $timeStrategy['diffInfo']);
                }
                if (!empty($timeStrategy['pIncrInfo'])) {
                    //永久增量
                    $modeType = xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL'];
                    $result = $result && $this->insertTimeStrategy($task_uuid, $strategyGroupUUid, $strategyID, $modeType, $timeStrategy['pIncrInfo']);
                }
                if (!empty($timeStrategy['logInfo'])) {
                    //日志备份
                    $modeType = xphp_get_config('task', 'BACKUP_MODE')['LOG'];
                    $result = $result && $this->insertTimeStrategy($task_uuid, $strategyGroupUUid, $strategyID, $modeType, $timeStrategy['logInfo']);
                }
            } elseif ('manual' == $info['time']['type']) {
                $time_strategy_backup_type = 3;
            }
            $sql = "UPDATE bd_strategy SET time_strategy_backup_type = '{$time_strategy_backup_type}' WHERE strategy_id = '{$strategyID}'";
            $result = $result && $this->dbExec($sql, []);
        }
        //限速策略
        if (!empty($info['speedlimit']) && !empty($info['speedlimit']['speedInfo'])) {
            if ($info['speedlimit']['type'] === 1) {
                $sql = "DELETE FROM bd_task_speed_limit_strategy WHERE task_uuid = ?";
                $result = $result && $this->dbExec($sql, array($task_uuid));
                $sql = "INSERT bd_task_speed_limit_strategy (strategy_uuid, strategy_group_uuid, task_priority, strategy_type, remark, task_uuid) VALUES (?, ?, ?, ?, ?, ?)";
                $sqlParams = array($info['speedlimit']['uuid'], $strategyGroupUUid, $info['speedlimit']['level'], $strategyType, $info['speedlimit']['des'], $task_uuid);
                $result = $result && $this->dbExec($sql, $sqlParams);
            } else {
                $speedList = $this->groupTaskSpeedList($info['speedlimit']['speedInfo'], $strategyGroupUUid);
                //更新时间策略表 bd_task_speed_limit_strategy
                $sql = "DELETE FROM bd_task_speed_limit_strategy WHERE task_uuid = ?";
                $result = $result && $this->dbExec($sql, array($task_uuid));
                $sql = "INSERT bd_task_speed_limit_strategy (strategy_uuid, strategy_group_uuid, strategy_type, days, start_time, end_time, remark, speed_limited_value, task_uuid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $globalSql = "INSERT bd_global_speed_limit_strategy (strategy_uuid, strategy_group_uuid, strategy_type, days, start_time, end_time, remark, speed_limited_value, extra_info, is_global) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                foreach ($speedList as $speed) {
                    $sqlParams = array($speed['strategy_uuid'], $strategyGroupUUid, intval($speed['strategy_type']), $speed['days'], $speed['start_time'], $speed['end_time'], $speed['remark'], intval($speed['speed_limited_value']), $task_uuid);
                    $globalParams = array($speed['strategy_uuid'], $strategyGroupUUid, intval($speed['strategy_type']), $speed['days'], $speed['start_time'], $speed['end_time'], $speed['remark'], intval($speed['speed_limited_value']), json_encode($info['speedlimit']['speedInfo']), 0);
                    $result = $result && $this->dbExec($sql, $sqlParams);
                    $result = $result && $this->dbExec($globalSql, $globalParams);
                }
            }
        }
        //存储策略
        if (!empty($info['store']) && !empty($info['store']['storeInfo'])) {
            //更新存储策略表 bd_storage_strategy
            $sql = "UPDATE bd_storage_strategy SET deduplication_flag = ?, compressed_flag = ?, encrypted_flag =?, encrypt_method = ?, password_auto_flag=?, password=?, compress_method = ? WHERE task_uuid = ?";
            $sqlParams = array(
                v1_parse_bool_to_flag($info['store']['storeInfo']['deduplication']),
                v1_parse_bool_to_flag($info['store']['storeInfo']['compress']),
                v1_parse_bool_to_flag($info['store']['storeInfo']['dataencrypt']),
                $info['store']['storeInfo']['encrypt_method'],
                v1_parse_bool_to_flag($info['store']['storeInfo']['password_auto_flag']),
                $this->updateStorePassword($info['store']['storeInfo']['password'], $info['store']['storeInfo']['dataencrypt'], $info['store']['storeInfo']['password_auto_flag']),
                intval($info['store']['storeInfo']['compress_method']),
                $task_uuid
            );
            $result = $result && $this->dbExec($sql, $sqlParams);
        }
        //保留策略
        if (!empty($info['reserve']) && !empty($info['reserve']['reserveInfo'])) {
            if ($strategyType == 14) {
                if (!($count == 1 && $oldTimeStrategy[0]['mode'] == 2)) {
                    //更新保留策略表 bd_reserved_strategy
                    $sql = "UPDATE bd_reserved_strategy SET strategy_type = ?, number = ?, strategy_mode = ? WHERE task_uuid = ?";
                    $sqlParams = array($info['reserve']['reserveInfo']['type'], intval($info['reserve']['reserveInfo']['value']), intval($info['reserve']['reserveInfo']['strategyMode']), $task_uuid);
                    $result = $result && $this->dbExec($sql, $sqlParams);
                    //更新GFS策略
                    //先查询此任务下有多少个虚拟机,有关虚拟机的都要修改
                    if (in_array($strategyType, [2, 17, 22])) {
                        $sqlVm = "SELECT vm_uuid FROM vm_machine_list WHERE task_uuid = ?";
                        $resultVm = $this->dbSelect($sqlVm, array($task_uuid));
                        $vmList = array();
                        foreach ($resultVm as $each) {
                            $vmList[] = array(
                                'vm_uuid' => $each['vm_uuid'],
                            );
                        };
                        $result = $result && $this->updateGFSStrategy($info['reserve']['reserveInfo']['gfs_strategy_item_list'], true, $task_uuid, $vmList);
                    }
                }
            } else {
                //更新保留策略表 bd_reserved_strategy
                $sql = "UPDATE bd_reserved_strategy SET strategy_type = ?, number = ?, strategy_mode = ? WHERE task_uuid = ?";
                $sqlParams = array($info['reserve']['reserveInfo']['type'], intval($info['reserve']['reserveInfo']['value']), intval($info['reserve']['reserveInfo']['strategyMode']), $task_uuid);
                $result = $result && $this->dbExec($sql, $sqlParams);
                //更新GFS策略
                //先查询此任务下有多少个虚拟机,有关虚拟机的都要修改
                if (in_array($strategyType, [2, 17, 22])) {
                    $sqlVm = "SELECT vm_uuid FROM vm_machine_list WHERE task_uuid = ?";
                    $resultVm = $this->dbSelect($sqlVm, array($task_uuid));
                    $vmList = array();
                    foreach ($resultVm as $each) {
                        $vmList[] = array(
                            'vm_uuid' => $each['vm_uuid'],
                        );
                    };
                    $result = $result && $this->updateGFSStrategy($info['reserve']['reserveInfo']['gfs_strategy_item_list'], true, $task_uuid, $vmList);
                }
            }
        }
        $sql = "SELECT strategy_group_uuid FROM bd_task WHERE task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        if ($data[0]['strategy_group_uuid'] != $strategyGroupUUid) {
            $sql = "UPDATE bd_task SET strategy_group_uuid = ? WHERE task_uuid = ?";
            $result = $result && $this->dbExec($sql, array($strategyGroupUUid, $task_uuid));
        }
        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }

        return $result;
    }

    /**
     * @description: 修改策略---插入时间差策略信息
     * @param {*} $task_uuid
     * @param {*} $strategyGroupUUid
     * @param {*} $strategyID
     * @param {*} $modeType
     * @param {*} $strategyInfo
     */
    public function insertTimeStrategy($task_uuid, $strategyGroupUUid, $strategyID, $modeType, $strategyInfo)
    {
        // 策略描述
        $days = $this->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
        // 滚动标志
        $rollFlag = v1_parse_bool_to_flag($strategyInfo['rollFlag']);
        $sql = "INSERT bd_time_strategy ( task_uuid, strategy_group_uuid, strategy_id, mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array(
            $task_uuid,
            $strategyGroupUUid,
            $strategyID,
            $modeType,
            $strategyInfo['type'],
            $days,
            v1_formart_time($strategyInfo['startTime']),
            $rollFlag,
            v1_time_to_sec($strategyInfo['rollInterval']),
            v1_formart_time($strategyInfo['endTime'])
        );

        return $this->dbQuery($sql, $sqlParams);
    }

    /**
     * @description: 修改策略---获取时间策略描述
     * @param {*} $days
     */
    public function getTimeStrategyDaysStr($days)
    {
        $str = '';
        if (empty($days)) {
            return $str;
        }
        $str = implode('', $days);
        return $str;
    }

    /**
     * @description: 修改策略---得到限速策略信息
     * @param {*} $speedList         限速列表
     * @param {*} $strategyGroupUUid 策略组uuid
     */
    public function groupTaskSpeedList($speedList, $strategyGroupUUid)
    {
        $info = array();
        foreach ($speedList as $speed) {
            $info[] = array(
                'strategy_uuid' => $speed['uuid'],
                'strategy_name' => '',
                'strategy_type' => $speed['type'],
                'start_time' => v1_formart_time($speed['startTime']),
                'end_time' =>  v1_formart_time($speed['endTime']),
                'days' => implode('', $speed['days']),
                'speed_limited_value' => $speed['value'],
                'extra_info' => '',
                'remark' => $speed['des'],
                'strategy_group_uuid' => $strategyGroupUUid,
            );
        }
        return $info;
    }

    /**
     * @description: 修改策略--- 更新存储加密密码
     * @param {*} $password      密码
     * @param {*} $encrypt       加密标志
     * @param {*} $password_auto 自动密码标志
     */
    private function updateStorePassword($password, $encrypt, $autoFlag)
    {
        $passwordEncryptStr = $password;
        if ($encrypt && $autoFlag) {  //如果加密和自动生成密码都打开,使用默认密码
            $passwordDefault = '/mnt/vm_vinfs';
            $passwordEncryptStr = v1_pt_pass_encrypt($passwordDefault);
        } elseif ($encrypt && !$autoFlag) { //如果加密打开  自动密码关闭  使用传输的密码
            //先base64解码
            $passwordDecode = base64_decode($password);
            //再平台加密
            $passwordEncryptStr = v1_pt_pass_encrypt($passwordDecode);
        } else {
            $passwordEncryptStr = '';
        }
        return $passwordEncryptStr;
    }

    /**
     * @description: 修改策略---更新gfs策略
     * @param {*} $info        策略组信息
     * @param {*} $oldinfoflag 修改标志
     * @param {*} $task_uuid   任务uuid
     * @param {*} $vm_list     对象列表
     */
    public function updateGFSStrategy($info, $flag, $task_uuid, $vmList)
    {
        //$flag为true有做修改,$flag为false未作修改
        if (!$flag) {
            //未做修改则直接返回
            return true;
        }
        $result = true;
        $deleteList = array(1, 2, 3); //未被勾选的策略集合
        if (!empty($info)) {
            //修改GFS等待表,统一先删除后再添加 并且等待状态全部置成2,所以如果以前有1状态的就不管了 重新来
            //先删除所有此任务有关的等待表
            $sqlDelete = "DELETE FROM bd_gfs_entity_waiting_map WHERE task_uuid = ?";
            $this->dbExec($sqlDelete, array($task_uuid));
            //修改勾选的GFS策略信息
            foreach ($info as $oneGFS) {
                $level1type = intval($oneGFS['level1_type']);
                //删除完以后再添加
                foreach ($vmList as $each) {
                    $sqlAdd = "INSERT into bd_gfs_entity_waiting_map(task_uuid, entity_uuid, level1_type, waiting_flag) VALUES(?,?,?,?)";
                    $sqlAddParams = array($task_uuid, $each['vm_uuid'], $level1type, 2);
                    $resultAdd = $this->dbExec($sqlAdd, $sqlAddParams);
                    if (!$resultAdd) {
                        return false;
                    }
                }
                //---
                unset($deleteList[$level1type - 1]);
                //先检查数据库是否有 有就修改 没有就添加
                $checkSql = "SELECT id FROM bd_task_gfs_retention_strategy WHERE task_uuid = ? AND level1_type = ?";
                $checkResult = $this->dbSelect($checkSql, array($task_uuid, $level1type));
                if (!empty($checkResult)) {
                    //修改bd_task_gfs_retention_strategy
                    $sql = "UPDATE bd_task_gfs_retention_strategy SET level2_type = ?, retention_num = ? WHERE task_uuid = ? AND level1_type = ?";
                    $sqlParams = array($oneGFS['level2_type'], $oneGFS['retention_num'], $task_uuid, $oneGFS['level1_type']);
                    $result1 = $this->dbExec($sql, $sqlParams);
                } else {
                    // 插入新的gfs策略
                    $sql = "INSERT INTO bd_task_gfs_retention_strategy(task_uuid,level1_type,level2_type,retention_num) VALUES(?,?,?,?)";
                    $sqlParams = array($task_uuid, $oneGFS['level1_type'], $oneGFS['level2_type'], $oneGFS['retention_num']);
                    $result1 = $this->dbExec($sql, $sqlParams);
                }
                if (!$result1) {
                    $result = false;
                    return $result;
                }
            }
        } else {
            //如果传入的为空则清空所有GFS有关
            $deleteStrategy = "DELETE FROM bd_task_gfs_retention_strategy WHERE task_uuid = ?";
            $deleteMap = "DELETE FROM bd_gfs_entity_waiting_map WHERE task_uuid = ?";
            $resultStrategy = $this->dbExec($deleteStrategy, array($task_uuid));
            $resultMap = $this->dbExec($deleteMap, array($task_uuid));
            if (!$resultStrategy || !$resultMap) {
                $result = false;
                return false;
            }
        }
        //开始删除未被勾选的集合
        if (!empty($deleteList)) {
            $thisList = implode(',', $deleteList);
            $deleteSql = "DELETE FROM bd_task_gfs_retention_strategy WHERE task_uuid = ? AND level1_type IN(" . $thisList . ') ';
            $result2 = $this->dbExec($deleteSql, array($task_uuid));
            if (!$result2) {
                $result = false;
                return false;
            }
        }
        return $result;
    }

    /**
     * @description: 策略管理---分发策略
     * @param {*} $params
     */
    public function dispenseStrategy($params)
    {
        // 需要分发的任务uuid数组
        $taskUUids = $params['taskuuids'];
        // 策略组信息
        $strategyInfo = $params['strategyInfo'];
        // 策略组uuid
        $strategy_uuid = $params['strategyuuid'];
        // 策略组类型
        $strategyType = $params['strategyType'];
        // 分发结果
        $result = true;

        // 操作权限判断
        $sql = "select user_uuid from bd_strategy_group where strategy_group_uuid = ? ";
        $data = $this->dbSelect($sql, [$strategy_uuid]) ?? [];
        $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);

        // 逐个分发
        if (!empty($taskUUids)) {
            foreach ($taskUUids as $task_uuid) {
                $result = $result && $this->dispenseStrategyToJob($strategyInfo, $task_uuid, $strategy_uuid, $strategyType);
            }
            // 失败则退出
            if (!$result) {
                return $this->muOpResult(false, xphp_get_lang('UI_GLOBAL_STRATEGY_DISPENSE'), '', 'warning');
            }
        }
    }

    /**
     * @description: 获取策略组列表详细信息
     * @param {*} $params
     * @return {*}
     */
    public function getStrategySelect($params)
    {
        // 用户
        $user = xphp_get_user_info();
        // 模块
        $type = intval($params['type']);
        $info = array();
        $sql = "SELECT strategy_group_uuid, strategy_group_name, extra_info FROM bd_strategy_group WHERE strategy_group_type = ? AND user_uuid = ? ORDER BY create_time DESC";
        $sqlParams = array($type, $user['userUuid']);
        $data = $this->dbSelect($sql, $sqlParams);
        $info[] = array(
            'uuid' => '',
            'text' => xphp_get_lang('UI_GLOBAL_STRATEGY_AUTO'),
            'strategy' => array()
        );
        if (!empty($data)) {
            foreach ($data as $d) {
                $info[] = array(
                    'uuid' => $d['strategy_group_uuid'],
                    'text' => $d['strategy_group_name'],
                    'strategy' => json_decode($d['extra_info'], true)
                );
            }
        }
        return $info;
    }
}
