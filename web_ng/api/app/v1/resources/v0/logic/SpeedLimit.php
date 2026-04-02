<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\OrchestrationOpcode;

/**
 * note          SpeedLimit 全局限速策略logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/9/11 14:47
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class SpeedLimit extends Base
{
    /**
     * 获取策略列表
     * @param array $params 数组
     * @return array
     */
    public function getList(array $params)
    {
        $search = !empty($params['search']) ? v1_escape_wildcard($params['search']) : '';
        $uuid = !empty($params['uuid']) ? $params['uuid'] : '';
        $where = 'where is_global = 1';
        $sqlParams = [];
        if (!empty($search)) {
            $where .= " and strategy_name like '%" . $search . "%'";
            $sqlParams[] = $search;
        }
        $order = "";

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['source']
            );
            $sqlNew = " and user_uuid in ({$userUuidSql}) ";
            $where .= $sqlNew;
        }

        $totalsql = "(select id from bd_global_speed_limit_strategy {$where} group by strategy_uuid)";

        $total = $this->dbSelect("select count(*) as num from {$totalsql} a", $sqlParams);

        $num = $total[0]['num'] ?? 0;

        if ($num == 0) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        // 可能会存在多条记录，所以必须group by strategy_uuid
        /*$buildsql1 = "(SELECT GROUP_CONCAT( CONCAT_WS( '</br>', extra_info ) SEPARATOR '</br>' )
                    AS extra_info FROM bd_global_speed_limit_strategy WHERE strategy_uuid = a.strategy_uuid )";*/

        // 还要查询出关联的任务 bd_task_speed_limit_strategy 里面的 strategy_uuid 查询出task_uuid在 bd_task查询出task_name任务名称
        $buildsql2 = "(SELECT GROUP_CONCAT( CONCAT_WS( '</br>', task_name ) SEPARATOR '</br>' ) AS task_name FROM
	                 bd_task WHERE task_uuid in (
                        SELECT task_uuid from bd_task_speed_limit_strategy where strategy_uuid = a.strategy_uuid
                    ) 
	            )";

        $field = 'strategy_uuid uuid,strategy_name name,strategy_type,user_uuid,create_time,extra_info';

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(strtolower($params['order']), ['desc', 'asc'])
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'name' => 's.name',
                'strategy_type' => 's.strategy_type',
                'user_name' => 'bu.user_name',
                'create_time' => 's.create_time',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' uuid desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', uuid desc');
            // 为了初始化选中，将选中的策略放到前面
            if(!empty($uuid)){
                $order = " ORDER BY CASE WHEN s.uuid = '{$uuid}' THEN 0 ELSE 1 END, " . $sort;
            } else {
                $order = " ORDER BY " . $sort;
            }
        } else {
            // 为了初始化选中，将选中的策略放到前面
            if(!empty($uuid)){
                $order = " ORDER BY CASE WHEN s.uuid = '{$uuid}' THEN 0 ELSE 1 END";
            } else {
                $order = "";
            }
        }

        $sql = "select s.*,bu.user_name from (select {$field},{$buildsql2} job_list
        from bd_global_speed_limit_strategy a {$where}
        group by a.strategy_uuid) s 
        left join bd_user bu on bu.user_uuid = s.user_uuid
        {$order} 
        limit ? , ?";

        $sqlParams = array_merge($sqlParams, [$params['offset'], $params['limit']]);

        $list = $this->dbSelect($sql, $sqlParams);

        $lang = xphp_get_config('speed_limit', 'LIMIT_TYPE_LANG');

        $lists = [];
        foreach ($list as $item) {
            $lists[] = [
                'checked' => $uuid == $item['uuid'] ? true : false,
                'uuid' => $item['uuid'],
                'name' => $item['name'],
                'user_uuid' => $item['user_uuid'],
                'create_time' => $item['create_time'],
                'extra_info' => $item['extra_info'],
                'user_name' => $item['user_name'],
                'type' => $item['strategy_type'],
                'strategy_type' => xphp_get_lang($lang[$item['strategy_type']]),
                'detail'    => implode('</br>', array_column(json_decode($item['extra_info'], true), 'des')),
                'job_list' => $item['job_list'] ?? xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE'),
                'has_job'   => (bool)$item['job_list']
            ];
        }

        return [
            'rows' => $lists,
            'total' => $num
        ];
    }

    /**
     * 获取策略详情
     * @param array $params 数组
     * @return array
     */
    public function viewSpeed(array $params)
    {
        $field = 'strategy_uuid,strategy_name,strategy_type,extra_info';
        $sql = "select {$field} from bd_global_speed_limit_strategy where strategy_uuid = ? limit 1";
        $data = $this->dbSelect($sql, [$params['strategy_uuid']]);
        if (empty($data)) {
            return [];
        }
        // 处理下返回数据结构
        $db = $data[0];

        $db['speedInfo'] = json_decode($db['extra_info'], true);
        return $db;
    }

    /**
     * 添加/修改策略
     * @param array $params 数组
     * @return string
     */
    public function saveSpeed(array $params)
    {

        $user = xphp_get_user_info();
        if (!empty($params['strategy_uuid'])) {
            // 修改才判断
            $sql = "select user_uuid from bd_global_speed_limit_strategy where strategy_uuid = ? ";
            $data = $this->dbSelect($sql, [$params['strategy_uuid']]);
            $this->checkAuthByUserUuid($data[0]['user_uuid'], xphp_get_config('user', 'USER_AUTH')['source']);
        }

        // 组装数据
        $data = [
            'speed_limit_strategy' => [
                'strategy_uuid' => $params['strategy_uuid'] ?? '',
                'strategy_name' => htmlspecialchars_decode($params['name']),
                'strategy_type' => $params['speed_type'],
                'is_global' => 1,
                'speed_limit_info' => [],
            ]
        ];
        // 处理下具体的时间段
        if (in_array($params['speed_type'], [1, 4, 5])) {
            // 这三个类型的没有days的值
            $timelist = [];
            foreach ($params['speed_info'] as $item) {
                $timelist[] = [
                    'start_time' => $params['speed_type'] != 5
                        ? v1_formart_time($item['startTime']) : $item['startTime'],
                    'end_time' => $params['speed_type'] != 5
                        ? v1_formart_time($item['endTime']) : $item['endTime'],
                    'speed_limited_value' => $item['value'],
                ];
            }
            $speedlimitinfo[] = [
                'days' => '',
                'time_list' => $timelist
            ];
        } else {
            $speedlimitinfos = [];
            foreach ($params['speed_info'] as $item) {
                $days = implode('', $item['days']);
                $speedlimitinfos[$days][] = [
                    'start_time' => v1_formart_time($item['startTime']),
                    'end_time' => v1_formart_time($item['endTime']),
                    'speed_limited_value' => $item['value'],
                ];
            }
            $speedlimitinfo = [];
            foreach ($speedlimitinfos as $keys => $items) {
                $speedlimitinfo[] = [
                    'days' => $keys,
                    'time_list' => $items,
                ];
            }
        }
        $data['speed_limit_strategy']['speed_limit_info'] = $speedlimitinfo;
        $data['speed_limit_strategy']['remark'] = '';
        $data['speed_limit_strategy']['extra_info'] = json_encode($params['speed_info']);

        $data['speed_limit_strategy']['user_uuid'] = $user['userUuid'];
        $return = $this->service()->saveSpeed($data);
        if ($return['result']) {
            //成功
            return true;
        } else {
            $nodeOpcode = new OrchestrationOpcode();
            $opName = empty($data['speed_limit_strategy']['strategy_uuid']) ?
                'SS_OP_ADD_SPEED_LIMITING_STRATEGY' : 'SS_OP_MOD_SPEED_LIMITING_STRATEGY';
            $operate = $nodeOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
     * 删除策略
     * @param array $params 数组
     * @return string
     */
    public function delSpeed(array $params)
    {

        // 权限判断
        $uuids = implode("','", $params['uuids']);
        $sql = "select user_uuid from bd_global_speed_limit_strategy where strategy_uuid in ('{$uuids}')";
        $data = $this->dbSelect($sql, []);
        $userArr = implode(',', array_unique(array_column($data, 'user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['source']);

        $data = [
            'strategy_uuid_list' => $params['uuids']
        ];
        $return = $this->service()->delSpeed($data);
        if ($return['result']) {
            //成功
            return true;
        } else {
            $nodeOpcode = new OrchestrationOpcode();
            $opName = empty($data['speed_limit_strategy']['strategy_uuid']) ?
                'SS_OP_ADD_SPEED_LIMITING_STRATEGY' : 'SS_OP_MOD_SPEED_LIMITING_STRATEGY';
            $operate = $nodeOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
     * 分发
     * @param array $params 数组
     * @return bool
     */
    public function sendSpeed(array $params)
    {

        // 分发限速策略给新的任务
        // 判断之前的任务是否有自定义的策略，存在则先删除
        // 然后去更改 bd_task_speed_limit_strategy 里面的关联
        $jobUuid = implode("','", array_column($params['uuids'], 'job_uuid'));
        $buildsql = "select strategy_uuid from bd_task_speed_limit_strategy where task_uuid in ('{$jobUuid}')";

        // 权限判断-取任务关联的用户uuid
        $sql = "select user_uuid from bd_task where task_uuid in ('{$jobUuid}')";
        $data = $this->dbSelect($sql, []);
        $userArr = implode(',', array_unique(array_column($data, 'user_uuid')));
        $this->checkAuthByUserUuid($userArr, xphp_get_config('user', 'USER_AUTH')['current_job']);

        $this->dbBeginTransaction();
        // 删除关联的自定义的策略
        $sql = "delete from bd_global_speed_limit_strategy where strategy_uuid in ({$buildsql}) and is_global = 0";

        $result = $this->dbExec($sql);

        // 删除原先的策略关联记录
        $sql2 = "delete from bd_task_speed_limit_strategy where task_uuid in ('{$jobUuid}')";
        $result = $result && $this->dbExec($sql2);

        // 绑定新的策略关联关系

        $update = [];
        foreach ($params['uuids'] as $item) {
            $jobid = $item['job_uuid'];
            $level = $item['task_priority'];
            $update[] = "('" . $params['global_speed_send_uuid'] . "','{$jobid}',{$level})";
        }
        $update = implode(',', $update);
        $sql = 'insert into bd_task_speed_limit_strategy (strategy_uuid,task_uuid,task_priority) values ' . $update;
        $result = $result && $this->dbExec($sql);

        if ($result) {
            $this->dbCommit();
        } else {
            $this->dbRollBack();
        }

        return $result;
    }

    /**
     * 获取可以分发的任务列表
     * @param array $params 数组
     * @return array
     */
    public function getSpeedJob(array $params)
    {
        $search = !empty($params['search']) ? v1_escape_wildcard($params['search']) : '';
        $startTime = $params['start_time'] ?? ''; // 开始时间
        $endTime = $params['end_time'] ?? '';     // 结束时间
        $where = ' where bt.task_status = ? and bt.delete_flag = ? ';
        $taskTypeArr = xphp_get_config('task', 'TASKTYPE');
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = [
            xphp_get_config('task', 'TASKSTATUS')['STOPPED'],
            $flag['UNSET']
        ];
        $arrType = [
            $taskTypeArr['VM_FILE_RECOVERY'],
            $taskTypeArr['VM_INSTANT_RECOVERY'],
            $taskTypeArr['VM_CDP_INSTANT_RECOVERY'],
            $taskTypeArr['VOL_CDP_TAKEOVER'],
            $taskTypeArr['OS_INSTANT_RECOVERY'],
            $taskTypeArr['SURE_BACKUP']
        ];
        // 不需要数据验证模块的任务
        $where .= 'and bt.task_type not in (' . implode(',', $arrType) . ')
         and bt.module_type != ' . $moduleTypeArr['SUREBACKUP'];

        if (!empty($search)) {
            $where .= ' and bt.task_name like ?';
            $sqlParams = array_merge($sqlParams, ['%' . $search . '%']);
        }
        //如果填了开始时间范围查询
        if (!empty($startTime) && !empty($endTime)) {
            $where .= ' and bt.create_time between ? and ? ';
            $sqlParams = array_merge($sqlParams, array($startTime, $endTime));
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['current_job']
            );
            $sqlNew = " and bt.user_uuid in ({$userUuidSql}) ";
            $where .= $sqlNew;
        }

        $total = $this->dbSelect("select count(*) total from bd_task bt {$where}", $sqlParams);

        if (empty($total)) {
            return [
                'total' => 0,
                'rows' => []
            ];
        }

        $field = 'bt.task_uuid,bt.task_name,bt.module_type,bt.sub_module_type,bt.task_type,bt.create_time,bt.user_uuid';
        // 这里处理下子查询 可能会查询虚拟机任务的 hypervisor_type
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        $field .= ',case bt.module_type 
                            when ' . $moduletype['VM'] . ' then  vt.hypervisor_type
							else bt.id
	                        end as module_type_alias';
        $left = ' left join vm_task vt on bt.task_uuid = vt.task_uuid ';

        $sqlParams = array_merge($sqlParams, [$params['offset'] ?? 0, $params['limit']]);
        $data = $this->dbSelect(
            "select {$field} from bd_task bt {$left} {$where} order by bt.create_time desc limit ?,?",
            $sqlParams
        );

        $return = [];
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $jobInfo = new JobInfo();
        foreach ($data as $item) {
            $return[] = [
                'job_uuid' => $item['task_uuid'],
                'user_uuid' => $item['user_uuid'],
                'job_name' => $item['task_name'],
                'create_time' => $item['create_time'],
                'module_type' => $jobInfo->getModuleName($item), // 模块类型
                'job_type' => $jobInfo->getTaskNameString(
                    $moduleType,
                    $taskType,
                    $item['module_type'],
                    $item['task_type']
                ),  // 任务类型
            ];
        }
        return [
            'total' => $total[0]['total'],
            'rows' => $return
        ];
    }

    /**
     * 获取不重复的限速策略名称
     * @return array|string
     */
    public function getGlobalSpeedName()
    {
        $taskName = xphp_get_lang('WEB_COMMON_SPEED_STRATEGY');
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "SELECT id FROM bd_global_speed_limit_strategy WHERE strategy_name = ?";
            $data = $this->dbSelect($sql, [$taskName]);
            if (empty($data)) {
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }
}
