<?php
namespace app\v1\tape\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\vm\v0\logic\VmPlatform;
use DateTime;
use PhpOffice\PhpSpreadsheet\Collection\Memory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * note磁带设备信息 logic
 */

class TapeInfo extends Base
{
    /**
     * 获取磁带库信息
     */
    public function getTapeLibInfo($params)
    {
        $start = $params['offset']; // 开始
        $length = $params['limit']; // 长度
        $search = $params['search'];// 搜索条件
        $node = $params['node_uuid']; //节点
        $sql = "select distinct btl.id, btl.node_uuid, btl.vendor, btl.name, btl.firmware, 
                btl.device_path, btl.robot_arm_num, btl.slot_num, btl.ie_slot_num, btl.detail
            from bd_tape_library btl ";
        $sqlCount = "select count(*) as total
                        from bd_tape_library btl ";
        $sqls = [];
        $sqlParams = [];

        if (!empty($node)) {
            $sqls = array_merge($sqls, array(' btl.node_uuid = ?'));
            $sqlParams = array_merge($sqlParams, array($node));
        }

        if (!empty($search)) {
            $sqls = array_merge($sqls, array(' btl.name = ?'));
            $sqlParams = array_merge($sqlParams, array($search));
        }

        $sqls = empty($sqls) ? '' : (" where " . implode(' and ', $sqls));
        $count = $this->dbSelect($sqlCount . $sqls, $sqlParams);
        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            ) ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'name' => 'btl.name',
                'lib_firmware' => 'btl.firmware',
                'slot_num' => 'btl.slot_num',
                'ie_slot_num' => 'btl.ie_slot_num',
                'vendor' => 'btl.vendor'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? 'id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', id desc');
            $sqls .= 'order by ' . $sort;
        }

        $sqls .= ' limit ?, ?';
        $sqlParams = array_merge($sqlParams, array($start, $length));

        $data = $this->dbSelect($sql . $sqls, $sqlParams);

        $records = [];
        foreach ($data as $d) {
            $records[] = array(
                'id' => intval($d['id']),
                'name' => $d['vendor'] . '-' . $d['name'] . '(' . $d['firmware'] . ')',
                'lib_name' => $d['name'],
                'vendor' => $d['vendor'],
                'lib_firmware' => $d['firmware'],
                'slot_num' => intval($d['slot_num']),
                'ie_slot_num' => intval($d['ie_slot_num']),
                'detail' => $d['detail'] ?? '',
            );
        }
        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 获取磁带信息
     */
    public function getTapeCarriageInfo($params)
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $search = $params['search'];
        $libName = $params['lib_name'];
        $isOpGroup = $params['is_op_group'];
        $groupUuid = $params['group_uuid'];
        $taskUuid = $params['task_uuid'];

        $sql = "select id, lib_name, name, serial_number, capacity,
                used_space, free_space, status, type, slot_number, group_uuid,
                backup_set_uuid, description, detail
            from bd_tape_carriage btc";
        $sqlCount = "select count(*) as total from bd_tape_carriage btc";

        $sqls = [];
        $sqlParams = [];

        //按磁带名或编号搜索
        if (!empty($search)) {
            // $sqls .= ' where lib_name = ?';
            array_push($sqls, 'serial_number like ?');
            array_push($sqlParams, '%' . $search . '%');
        }

        //根据task_uuid查到任务对应使用的磁带
        if (!empty($taskUuid)) {
            array_push($sqls, 'task_uuid = ?');
            array_push($sqlParams, $taskUuid);
        }

        //带库名
        if (!empty($libName)) {
            // $sqls .= ' where lib_name = ?';
            array_push($sqls, 'lib_name = ?');
            array_push($sqlParams, $libName);
        }

        //磁带组
        if (!empty($groupUuid)) {
            // $sqls .= ' where lib_name = ?';
            array_push($sqls, 'group_uuid = ?');
            array_push($sqlParams, $groupUuid);
        }

        //磁带组添加或修改时
        if ($isOpGroup) {
            // 不返回清洗带
            array_push($sqls, 'type != 100');
            //不返回已存在磁带组的磁带
            array_push($sqls, 'group_uuid = ""');
            //不返回离线的磁带
            array_push($sqls, 'status != 2');
        }

        $sqls = empty($sqls) ? '' : (" where " . implode(' and ', $sqls));

        // dump($sqlCount . $sqls, $sqlParams);
        $count = $this->dbSelect($sqlCount . $sqls, $sqlParams);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        if (!empty($params['sort'] && !empty($params['order']))) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            ) ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'name' => 'btc.name, btc.serial_number',
                'tape_type' => 'btc.type',
                'serial_number' => 'btc.serial_number',
                'total_size' => 'btc.capacity',
                'used_size' => 'btc.used_size',
                'free_size' => 'btc.free_space',
                'tape_status' => 'btc.status',
                'slot_number' => 'btc.slot_number'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . '');
            $sqls .= " order by " . $sort;
        }
        // dump($sql . $sqls, $sqlParams);
        if (!empty($length)) {
            $sqls .= ' limit ?, ?';
            $sqlParams = array_merge($sqlParams, array($start, $length));
        }

        $data = $this->dbSelect($sql . $sqls, $sqlParams);

        $records = [];
        foreach ($data as $d) {
            $records[] = [
                'id' => $d['id'],
                'name' => $d['name'] === '' ? $d['serial_number'] : $d['name'],
                'lib_name' => $d['lib_name'],
                'serial_number' => $d['serial_number'],
                'total_size' => v1_calsize($d['capacity'], true),
                'used_size' => v1_calsize($d['used_space'], true),
                'free_size' => v1_calsize($d['free_space'], true),
                'tape_status' => $d['status'],
                'tape_type' => $d['type'],
                'slot_number' => $d['slot_number'],
                'group_uuid' => $d['group_uuid'] === '' ? '--' : $d['group_uuid'],
                'group_name' => $this->getGroupNameByUuid($d['group_uuid']) ?? '--',  //通过group_uuid再查找并返回group_name,backup_set同理
                'backup_set_uuid' => $d['backup_set_uuid'] === '' ? '' : $d['backup_set_uuid'],
                'backup_set_name' => $this->getBackupSetNameByUuid($d['backup_set_uuid']) ?? '--',
                'driver_name' => $this->getDriverNameBySerialNumber($d['serial_number']) ?? '--',
                'driver_path' => $this->getDriverPathBySerialNumber($d['serial_number']) ?? '--',
                'description' => $d['description'] === '' ? '--' : $d['description'],
                'detail' => $d['detail'] ?? new \stdClass(),
            ];
        }

        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * @function 根据uuid查找磁带组名称
     */
    public function getGroupNameByUuid($groupUuid)
    {
        $sql = 'select name from bd_tape_group where group_uuid = ?';
        $data = $this->dbSelect($sql, [$groupUuid]);
        $groupName = $data[0];
        return $data[0]['name'];
    }

    /**
     * @function 根据uuid查找对应的备份集名称
     */
    public function getBackupSetNameByUuid($backupSetUuid)
    {
        if (empty($backupSetUuid)) {
            return '--';
        }
        $sql = 'select name, generated_time from bd_tape_backup_set where backup_set_uuid = ?';
        $data = $this->dbSelect($sql, [$backupSetUuid]);
        // dump($data[0]['name'] ?? xphp_get_lang('WEB_TAPE_BACKUPSET_NAME') . '(' . $data[0]['generated_time'] . ')');

        return empty($data[0]['name']) ? xphp_get_lang('WEB_TAPE_BACKUPSET_NAME') . '(' . $data[0]['generated_time'] . ')' : $data[0]['name'];
    }

    /**
     * @function 查找当前磁带所在的驱动器
     */
    public function getDriverNameBySerialNumber($tapeSerialNumber)
    {
        $sql = 'select name from bd_tape_driver where load_tape_serial_number = ?';
        $data = $this->dbSelect($sql, [$tapeSerialNumber]);
        return $data[0]['name'];
    }

    /**
     * @function 查找当前磁带所在的驱动器路径
     */
    public function getDriverPathBySerialNumber($tapeSerialNumber)
    {
        $sql = 'select driver_path from bd_tape_driver where load_tape_serial_number = ?';
        $data = $this->dbSelect($sql, [$tapeSerialNumber]);
        return $data[0]['driver_path'];
    }

    /**
     * 修改磁带
     */
    public function modifyTapeCarriageInfo($params)
    {
        // dump($params);
        $id = $params['id'];
        $old_name = $params['name'];
        $new_name = $params['new_name'];
        $description = $params['description'];

        $sql = "update bd_tape_carriage 
            set name = ?, description = ?
            where id = ?";

        $sqlParams = array(
            $new_name,
            $description,
            $id
        );
        // dump($sql, $sqlParams);
        $result = $this->dbExec($sql, $sqlParams);

        return $result;
    }


    /**
     * 修改备份集
     */
    public function modifyTapeBackupSet($params)
    {
        $backupSetUuid = $params['backup_set_uuid'];
        $backupSetName = $params['backup_set_new_name'];
        $sql = "update bd_tape_backup_set
        set name = ? where backup_set_uuid = ?";
        $sqlParams = [$backupSetName, $backupSetUuid];
        $result = $this->dbExec($sql, $sqlParams);
        return $result;
    }

    /**
     * 获取驱动器信息
     */
    public function getTapeDriverInfo($params)
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $libName = $params['lib_name'];

        $sql = "select distinct btd.id, btd.lib_name, btd.status, btd.firmware, btd.name,
                btd.load_tape_serial_number, btd.detail
            from bd_tape_driver btd ";
        $sqlCount = "select count(*) as total
                        from bd_tape_driver btd";

        $sqls = '';
        $sqlParams = [];

        if (!empty($libName)) {
            $sqls .= ' where lib_name = ?';
            array_push($sqlParams, $libName);
        }

        // $sqls = empty($sqls) ? '' : (" where " . implode(' and ', $sqls));
        // dump($sql . $sqls, $sqlParams);
        $count = $this->dbSelect($sqlCount . $sqls, $sqlParams);

        // $countkey = md5($sqls . json_encode($sqlParams));
        // $count = cache($countkey);
        // if (empty($count)) {
        //     $count = $this->dbSelect($sqlCount . $sqls, $sqlParams);
        //     cache($countkey, $count, 60);
        // }

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            ) ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'id' => 'btd.id',
                'status' => 'btd.status'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', id desc');
            $sqls .= " order by " . $sort;
        }

        $data = $this->dbSelect($sql . $sqls, $sqlParams);

        $sqls .= ' limit ?, ?';
        $sqlParams = array_merge($sqlParams, array($start, $length));

        $records = [];
        foreach ($data as $d) {
            $records[] = [
                'id' => intval($d['id']),
                'lib_name' => $d['lib_name'],
                'name' => $d['name'],
                'firmware' => $d['firmware'],
                'status' => $d['status'],
                'load_tape_number' => $d['load_tape_serial_number'] === '' ? '--' : $d['load_tape_serial_number'],
                'detail' => $d['detail']
            ];
        }
        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    public function getTapeGroupStrategy($params)
    {
        $groupUuid = $params['group_uuid'];

        $sql = "select name, reserve_strategy_type, reserve_days, backup_set_strategy_type, backup_set_generated_days from bd_tape_group where group_uuid = ?";
        $sqlParams = [$groupUuid];
        $data = $this->dbSelect($sql, $sqlParams);
        if (empty($data)) {
            return '';
        }
        $d = $data[0];

        $backupSetDes = '';
        $reserveDes = '';

        // 生成策略
        if ($d['backup_set_strategy_type'] == 1) {
            $backupSetDes .= xphp_get_lang('UI_TAPE_SELECT_GENERATE_STRATEGY1');
        } else if ($d['backup_set_strategy_type'] == 2) {
            $backupSetDes .= xphp_get_lang('UI_TAPE_SELECT_GENERATE_STRATEGY2');
        } else if ($d['backup_set_strategy_type'] == 3){
            $backupSetDes .= xphp_get_lang('UI_TAPE_SELECT_GENERATE_STRATEGY3') . '(' . $d['backup_set_generated_days'] . ''.xphp_get_lang('WEB_UTILS_DAY').')';
        }

        // 保留策略
        if ($d['reserve_strategy_type'] == 1) {
            $reserveDes .= xphp_get_lang('UI_TAPE_RESERVE_STRATEGY1');
        } else if ($d['reserve_strategy_type'] == 2) {
            $reserveDes .= xphp_get_lang('UI_TAPE_RESERVE_STRATEGY2') . '(' . $d['reserve_days'] . ''.xphp_get_lang('WEB_UTILS_DAY').')';
        } else if ($d['reserve_strategy_type'] == 3){
            $reserveDes .= xphp_get_lang('UI_TAPE_RESERVE_STRATEGY3');
        }

        // $des .= '</div></div>';

        $info = array(
            'name' => $d['name'],
            'backup_set_strategy_type' => $d['backup_set_strategy_type'],
            'backup_set_generated_days' => $d['backup_set_generated_days'] ?? 0,
            'reserve_strategy_type' => $d['reserve_strategy_type'],
            'reserve_days' => $d['reserve_days'] ?? 0,
            'backup_set_strategy_des' => $backupSetDes,
            'reserve_strategy_des' => $reserveDes,
        );
        return $info;
    }

    /**
     * 获取磁带组信息
     * @param array $params
     * @return array
     */
    public function getTapeGroupInfo($params)
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $nodeUuid = $params['node_uuid'];
        $sql = "select btg.id, btg.group_uuid, btg.name, btg.reserve_strategy_type, btg.reserve_days,
                btg.backup_set_strategy_type, btg.backup_set_generated_days, btg.description, btg.detail, btg.status, 
                bsr.warning_flag, bsr.use_mode, bsr.warning_type, bsr.warning_value, bsr.node_uuid, btg.delay_days, btg.delay_space_threshold 
            from bd_tape_group btg 
            LEFT JOIN bd_storage_resource bsr on btg.group_uuid = bsr.storage_uuid";
        $sqlCount = "select count(*) as total 
                    from bd_tape_group btg 
                    LEFT JOIN bd_storage_resource bsr on btg.group_uuid = bsr.storage_uuid ";

        //若有需要添加where
        $sqls = [];
        $sqlParams = [];

        if (!empty($nodeUuid)) {
            $sql .= " where bsr.node_uuid = ?";
            $sqlCount .= " where bsr.node_uuid = ?";
            array_push($sqlParams, $nodeUuid);
        }

        $sqls = empty($sqls) ? '' : (" where " . implode(' and ', $sqls));
        $count = $this->dbSelect($sqlCount, $sqlParams);
        $count = intval($count[0]['total']);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
       
        if (!empty($params['sort'] && !empty($params['order']))) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            ) ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'name' => 'btg.name',
                'description' => 'btg.description',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . '');
            $sqls .= " order by " . $sort;
        }

        if (!empty($length)) {
            $sqls .= ' limit ?, ?';
            $sqlParams = array_merge($sqlParams, array($start, $length));
        }

        $data = $this->dbSelect($sql . $sqls, $sqlParams);

        $records = [];
        foreach ($data as $d) {
            $name = $d['name'];
            if ($d['status'] == 2) {
                // 待导入
                $name = $d['name'] . '<span  style="color:#f19f00">(' . xphp_get_lang('UI_TAPE_WAIT_IMPORT') . ')</span>';
            }
            $records[] = [
                'group_uuid' => $d['group_uuid'],
                'name_value' => $d['name'],
                'name' => $name,
                'lib_name' => $this->getLibNameByGroup($d['group_uuid']),
                'tape_count' => $this->getTapesByGroupUuid($d['group_uuid']),
                'total_size' => v1_calsize($this->getTapeGroupTotalSize($d['group_uuid']), true),
                'free_size' => v1_calsize($this->getTapeGroupFreeSize($d['group_uuid']), true),
                'is_backupset' => $this->getBackupSetFlag($d['group_uuid']),
                'reserve_strategy_type' => intval($d['reserve_strategy_type']),
                'reserve_days' => intval($d['reserve_days']),
                'delay_days' => intval($d['delay_days']),
                'delay_space_threshold' => intval($d['delay_space_threshold']),
                'status' => $d['status'],
                'use_mode' => $d['use_mode'],
                'warning_flag' => v1_parse_flag_to_bool($d['warning_flag']),
                'warning_type' => $d['warning_type'],
                'warning_value' => $d['warning_value'],
                'backup_set_strategy_type' => intval($d['backup_set_strategy_type']),
                'backup_set_generated_days' => intval($d['backup_set_generated_days']),
                'description' => empty($d['description']) ? '--' : $d['description'],
                'detail' => $d['detail'] ?? '--',
            ];
        }

        return [
            'rows' => $records,
            'total' => $count
        ];
    }

    public function getBackupSetFlag($groupUuid)
    {
        $sql = "select backup_set_uuid from bd_tape_backup_set where group_uuid = ?";
        $sqlParam = array($groupUuid);

        $data = $this->dbSelect($sql, $sqlParam);

        if (empty($data)) { // 没有备份集
            return false;
        } else {
            return true;
        }
    }

    /**
     * 通过组中磁带编号反向查询带库名
     */
    public function getLibNameByGroup($groupUuid)
    {
        $sql = 'select serial_number from bd_tape_carriage where group_uuid = ?';
        $sqlParams = [$groupUuid];

        $data = $this->dbSelect($sql, $sqlParams);

        $serialNumber = $data[0]['serial_number'];

        return $this->getLibNameBySerialNumber($serialNumber);
    }

    public function getLibNameBySerialNumber($serialNumber)
    {
        $sql = 'select lib_name from bd_tape_carriage where serial_number = ?';
        $sqlParams = [$serialNumber];

        $data = $this->dbSelect($sql, $sqlParams);

        return $data[0]['lib_name'];
    }

    /**
     * 获取组中的磁带数
     */
    public function getTapesByGroupUuid($group_uuid)
    {
        $sqlCount = "select count(*) as total
                        from bd_tape_carriage btc where btc.group_uuid = ?";
        $sqlParam = [$group_uuid];

        $count = $this->dbSelect($sqlCount, $sqlParam);

        return $count[0]['total'];
    }

    /**
     * 获取磁带组总容量
     */
    public function getTapeGroupTotalSize($group_uuid)
    {
        $sql = "select btc.capacity from bd_tape_carriage btc where group_uuid = ?";
        $sqlParam = [$group_uuid];

        $data = dbSelect($sql, $sqlParam);

        $total_size = 0;
        foreach ($data as $d) {
            $total_size += $d['capacity'];
        }

        return $total_size;
    }

    /**
     * 获取磁带组总剩余容量
     */
    public function getTapeGroupFreeSize($group_uuid)
    {
        $sql = "select free_space from bd_tape_carriage where group_uuid = ?";
        $sqlParam = [$group_uuid];

        $data = dbSelect($sql, $sqlParam);
        // dump($data);
        $free_size = 0;
        foreach ($data as $d) {
            $free_size += $d['free_space'];
        }

        return $free_size;
    }

    /**
     * 获取备份集信息
     */
    public function getBackupSetInfo($params)
    {
        $start = $params['offset'];
        $length = $params['limit'];
        $groupUuid = $params['group_uuid'];

        $sql = "select distinct btb.id, btb.backup_set_uuid, btb.group_uuid, btb.name, btb.generated_time,
                btb.detail, btb.last_write_time, btg.reserve_days ,btb.freeze_flag, btg.reserve_strategy_type
        from bd_tape_backup_set btb
        left join bd_tape_group btg on btb.group_uuid = btg.group_uuid where btb.group_uuid = ?";
        $sqlCount = "select count(*) as total
                        from bd_tape_backup_set btb where group_uuid = ?";

        $sqls = [];
        $sqlParams = array($groupUuid);

        $sqls = empty($sqls) ? '' : (" where " . implode(' and ', $sqls));

        $count = $this->dbSelect($sqlCount . $sqls, $sqlParams);

        if (empty($count)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        if (!empty($params['sort'] && !empty($params['order']))) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            ) ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'name' => 'btb.name, btb.generated_time',
                'generated_time' => 'btb.generated_time',
                'last_write_time' => 'btb.last_write_time',
                'group_name' => 'btb.group_uuid',
                'freeze_flag' => 'btb.freeze_flag',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . '');
            $sqls .= " order by " . $sort;
        }

        $sqls .= ' limit ?, ?';
        $sqlParams = array_merge($sqlParams, array($start, $length));

        $data = $this->dbSelect($sql . $sqls, $sqlParams);

        $records = [];
        foreach ($data as $d) {
            $records[] = [
                'name' => empty($d['name']) ? xphp_get_lang('WEB_TAPE_BACKUPSET_NAME') . '(' . $d['generated_time'] . ')' : $d['name'],
                'backup_set_uuid' => $d['backup_set_uuid'],
                'expired_flag' => $this->getBackupSetExpiredFlag($d['last_write_time'], $d['reserve_days']),
                'group_uuid' => $d['group_uuid'],
                'group_name' => $this->getGroupName($d['group_uuid']),
                'generated_time' => $d['generated_time'],
                'last_write_time' => $d['last_write_time'],
                //'time_point' => $this->getTimePoint($d['backup_set_uuid']),
                'time_point' => [],
                'valid_start_time' => empty($this->getValidTime($d['backup_set_uuid'])) ? $d['generated_time'] : $this->getValidTime($d['backup_set_uuid']),
                'detail' => $d['detail'],
                'freeze_flag' => $d['freeze_flag'],
                'reserve_strategy_type' => $d['reserve_strategy_type'],
            ];
        }

        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 获取备份集的过期标志
     */
    public function getBackupSetExpiredFlag ($lastTime, $days)
    {
        if ($days == 0) {
            return false;
        }
        $nowTime = new DateTime();
        $lastTime = new DateTime($lastTime);
        $interval  = $nowTime->diff($lastTime);
        $gapDays = $interval ->days;
        if ($gapDays >= $days - 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取备份集所有时间点
     */
    public function getTimePoint($backupSetUuid)
    {

        $subSql = v1_tape_timepoint_sql(" and btbs.backup_set_uuid = '{$backupSetUuid}'");
        $sql = "SELECT DISTINCT btt.timepoint_uuid, btt.task_uuid, btt.task_name, btt.deleted_flag, btt.module_type,
         btt.sub_module_type, btt.task_type, btt.timepoint, btt.backup_mode, btt.depend_point_uuid, dbt.db_uuid
        , dbt.db_type, dbt.agent_uuid, dbt.instance_name, dbt.agent_ip, dbt.db_name,
         btt.weekly_flag,btt.monthly_flag,btt.yearly_flag,btt.importance_flag 
        FROM bd_backup_timepoint btt 
        LEFT JOIN db_backup_timepoint dbt ON btt.timepoint_uuid = dbt.timepoint_uuid 
        inner join ({$subSql}) AS valid_timepoints ON btt.timepoint_uuid = valid_timepoints.timepoint_uuid
        WHERE btt.available_flag = ? order by btt.timepoint asc";

        $sqlParams = [xphp_get_config('app', 'FLAG')['SET']];

        $data = $this->dbSelect($sql, $sqlParams);

        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allDbTypeDes = xphp_get_config('db', 'DB_TYPE_DES');
        $dbType = array();
        $type = 0;
        $task = array();
        $db = array();
        $instance = array();
        $timepoint = array();
        $pid = null;

        $info = [];
        $taskInfo = [];
        $taskName = [];
        $timePoint = [];
        $dbTypeInfo = [];
        $jobInfoHandler = new JobInfo();
        $dbTypeModule = xphp_get_config('module', 'MODULE_TYPE')['DB'];
        $fullBackupMode = xphp_get_config('task', 'BACKUP_MODE')['FULL'];
        foreach ($data as $d) {
            $taskuuid = $d['task_uuid'];
            $agentuuid = $d['agent_uuid'];
            $timepointuuid = $d['timepoint_uuid'];

            $taskInfo[] = [
                'task_uuid' => $d['task_uuid'],
                'name' => $d['task_name'],
                'module_type' => $d['module_type'],
                'sub_module_type' => $d['sub_module_type'],
                'pId' => $d['module_type'],
            ];
            $flags = $this->pGetTimepointMark(
                v1_parse_flag_to_bool($d['weekly_flag']),
                v1_parse_flag_to_bool($d['monthly_flag']),
                v1_parse_flag_to_bool($d['yearly_flag']),
                v1_parse_flag_to_bool($d['importance_flag'])
            );
            $timepointTypeDes = $jobInfoHandler->getTimepointTypeDes($d['backup_mode'], $d['db_type']);
            $icon = $this->getTimepointIcon($d['backup_mode']);
            $timePoint[] = [
                'timepoint_uuid' => $d['timepoint_uuid'],
                'task_uuid' => $d['task_uuid'],
                'deleted_flag' => $d['deleted_flag'],
                'backup_mode' => $d['backup_mode'],
                'backup_mode_des' => $timepointTypeDes,
                'pId' => $this->getFullTimePointByTimePoint($d['timepoint_uuid'], $data, $d['module_type'], $d['backup_mode'], $fullBackupMode),
                'title' => $d['timepoint'] . '(' . $timepointTypeDes . ')',
                'name' => $d['timepoint'] . '(' . $timepointTypeDes . ')' . $flags,
                'db_type' => $d['db_type'] ?? 0,
            ];

            if ($d['module_type'] == $dbTypeModule) {
                //检查并添加数据库类型
                if(!in_array($d['db_type'], $dbType)){
                    $name = $allDbTypeDes[$d['db_type']];
                    $dbTypeInfo[] = array(
                        "id" => $d['db_type'],
                        "pId" => 0,
                        "name" => $name,
                        "type" => -1,
                        "icon" => './img/vm/host.png',
                        "agent_uuid" => $d['agent_uuid'],
                        "db_type" => $d['db_type'],
                        "isParent" => true,
                        'eventtype' => 'db_type',
                    );
                    $dbType[] = $d['db_type'];
                }
                $type = intval($d['db_type']);
                $name = $d['task_name'];

                //检查并添加task
                if(!in_array($d['db_type'].$taskuuid, $task)){
                    $task[] = $d['db_type'].$taskuuid;
                    // $taskAvailable = in_array($taskuuid, $currentTaskUUID);
                    // $name = $taskAvailable ? $name : $name . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                    $dbTypeInfo[] = array(
                        "id" => $type.$taskuuid,
                        "pId" => $type,
                        "name" => $name,
                        "open" => false,
                        "type" => 0,
                        "icon" => './img/platform/flag.png',
                        "agent_uuid" => $d['agent_uuid'],
                        "task_uuid" => $d['task_uuid'],
                        "db_type" => $d['db_type'],
                        "isParent" => true,
                        'eventtype' => 'task',
                    );
                }

                $dbuuid = $d['db_uuid'];
                $pId = $type.$taskuuid;
                $dbname = $d['db_name']."(".$d['agent_ip'].")";
                $id = $type. $agentuuid. $dbuuid . $taskuuid;

                //sqlserver和SAP Hana多显示一层
                if($type == $allDbType['SQLSERVER'] || $type == $allDbType['SAPHANA']){
                    $instancename = $d['instance_name'];
                    //检查并添加INSTANCE
                    if(!in_array($type.$agentuuid.$instancename. $taskuuid, $instance)){
                        $instance[] = $type.$agentuuid.$instancename. $taskuuid;
                        $dbTypeInfo[] = array(
                            "id" => $type.$agentuuid.$instancename. $taskuuid,
                            "pId" => $type.$taskuuid,
                            "name" => $d['instance_name']."(".$d['agent_ip'].")",
                            "open" => false,
                            "type" => 5,
                            "icon" => './img/vm/host.png',
                            "title" => $d['instance_name']."(".$d['agent_ip'].")",
                            "agentuuid" => $d['agent_uuid'],
                            "instanceuuid" => $d['instance_name'],
                            "taskuuid" => $taskuuid,
                            "dbtype" => $d['db_type'],
                            "isParent" => true,
                            'eventtype' => 'instance',
                        );
                    }

                    $dbname = $d['db_name'];
                    $pId = $type.$agentuuid.$instancename. $taskuuid;
                    $id = $type.$agentuuid. $instancename. $dbuuid .$taskuuid;
                }

                if (!in_array($id, $db)) {
                    // 检查并添加db
                    $db[] = $id;
                    $dbIcon = './img/platform/storage.png';

                    $isParent = true;
                    // $clickShow = true;
                    $dbTypeInfo[] = array(
                        "id" => $id,
                        "pId" => $pId,
                        "name" => $dbname,
                        "type" => 1,
                        "icon" => $dbIcon,
                        "agentuuid" => $d['agent_uuid'],
                        "instance_uuid" => $d['instance_name'],
                        "db_uuid" => $d['db_uuid'],
                        "taskuuid" => $taskuuid,
                        // "nodeuuid" => $d['node_uuid'],
                        "dbtype" => $d['db_type'],
                        // "path" => $d['dir_path'],
                        "isParent" => $isParent,
                        'eventtype' => 'db',
                    );

                }


                // 获取时间点
                $pId = $type.$agentuuid. $dbuuid .$taskuuid;
                $timepointuuid = $d['timepoint_uuid'];
                $timepoint[] =  $timepointuuid;
                //sqlserver新增一层实例
                if($type == $allDbType['SQLSERVER'] || $type == $allDbType['SAPHANA']){
                    $pId = $type.$agentuuid. $instancename. $dbuuid .$taskuuid;
                }
                if ($d['backup_mode'] == $fullBackupMode) {
                    //完备点
                    $dbTypeInfo[] = [
                        "id" =>  $timepointuuid. $dbuuid,
                        "pId" =>  $pId,
                        "icon" => $icon,
                        'timepoint_uuid' => $d['timepoint_uuid'],
                        'task_uuid' => $d['task_uuid'],
                        'deleted_flag' => $d['deleted_flag'],
                        'backup_mode' => $d['backup_mode'],
                        'backup_mode_des' => $timepointTypeDes,
                        'title' => $d['timepoint'] . '(' . $timepointTypeDes . ')',
                        'name' => $d['timepoint'] . '(' . $timepointTypeDes . ')' . $flags,
                        'db_type' => $d['db_type'] ?? 0,
                        'eventtype' => "full"
                    ];
                    $pid = $timepointuuid;
                } else {
                    //非完备点
                    $dbTypeInfo[] = [
                        "id" =>  $d['timepoint_uuid'],
                        'pId' => $this->getFullTimePointByTimePoint($d['timepoint_uuid'], $data, $dbTypeModule, $d['backup_mode'], $fullBackupMode). $dbuuid,
                        "icon" => $icon,
                        'timepoint_uuid' => $d['timepoint_uuid'],
                        'task_uuid' => $d['task_uuid'],
                        'deleted_flag' => $d['deleted_flag'],
                        'backup_mode' => $d['backup_mode'],
                        'backup_mode_des' => $timepointTypeDes,
                        'title' => $d['timepoint'] . '(' . $timepointTypeDes . ')',
                        'name' => $d['timepoint'] . '(' . $timepointTypeDes . ')' . $flags,
                        'db_type' => $d['db_type'] ?? 0,
                        'eventtype' => "diff"
                    ];
                }
            }
        }


        $tmp_arr = array();
        $key = 'task_uuid';
        foreach ($taskInfo as $k => $v) {
            if (in_array($v[$key], $tmp_arr)) {//搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
                unset($taskInfo[$k]);
            } else {
                $tmp_arr[] = $v[$key];
            }
        }
        sort($taskInfo); //sort函数对数组进行排序
        $taskName = array_unique($taskName);
        $info['task_info'] = $taskInfo;
        $info['time_point'] = $timePoint;
        $info['db_type_info'] = json_encode($dbTypeInfo);
        return $info;
    }

    /**
     * 获取备份点的完备点
     * @param string $timePointUuid 备份点uuid
     * @param array  $allData       所有数据
     * @param int  $module       模块类型
     * @param int  $mode       当前点的备份模式
     * @return string
     */
    private function getFullTimePointByTimePoint($timePointUuid, $allData, $module, $mode, $fullBackupMode)
    {

        if ($mode == $fullBackupMode) {
            return $module; //完备点直接用模块类型做pId
        }
        // 用索引构建比较快
        $dependentUuidList = [
            $timePointUuid => 1,
        ];
        $fullTimePointUuid = null;
        $loopFlag = true;
        while ($loopFlag) {
            $loopFlag = false;
            foreach ($allData as $point) {
                if (
                    isset($dependentUuidList[$point['timepoint_uuid']]) &&  // 这个备份点被需要查询的点依赖
                    !isset($dependentUuidList[$point['depend_point_uuid']]) // 这个备份点的依赖点还没有加入缓存中
                ) {
                    if ($point['backup_mode'] == $fullBackupMode) {
                        $fullTimePointUuid = $point['timepoint_uuid'];
                        break;
                    }
                    $loopFlag = true;
                    $dependentUuidList[$point['depend_point_uuid']] = 1;
                }
            }
            if (!is_null($fullTimePointUuid)) {
                break;
            }
        }
        if (is_null($fullTimePointUuid)) {
            return $module;
        }
        return $fullTimePointUuid;
    }

    /**
     * 获取有效起始时间
     */
    private function getValidTime($uuid)
    {
        $sql = 'select first_write_time from bd_tape_carriage where backup_set_uuid = ? order by first_write_time limit 1';
        $sqlParams = [$uuid];

        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]['first_write_time'] == "0000-00-00 00:00:00" || empty($data[0]['first_write_time'])) {
            return '';
        }
        return $data[0]['first_write_time'];
    }

    /**
     * 获取磁带任务
     */
    public function getTapeJobInfo($params)
    {
        $start = $params['offset'];
        $length = $params['limit'];

        $taskName = v1_escape_wildcard($params['job_name'] ?: $params['search']); // 任务名称
        $userName = v1_escape_wildcard($params['user_name']); // 创建者
        $otherHostName = v1_escape_wildcard($params['other_host_name']); // 主机名/IP/别名
        $otherVmName = v1_escape_wildcard($params['other_vm_name']); // 虚拟机名/IP搜索
        
        $taskType = $params['job_type'] ? explode(',', trim($params['job_type'])) : ''; // 任务类型 支持以英文逗号隔开的多个
        $taskStatus = $params['job_status'] ? explode(',', trim($params['job_status'])) : ''; // 任务状态 支持以英文逗号隔开的多个
        $moduleType = $params['module_type'] ? explode(',', trim($params['module_type'])) : ''; // 模块类型 支持以英文逗号隔开的多个
        $subModuleType = $params['sub_module_type'] ? explode(',', trim($params['sub_module_type'])) : ''; //子模块类型，目前只有AWS使用

        $dbType = intval($params['db_type']); // module_type 参数为数据库（4）时可能存在的数据库类型
        $vmType = intval($params['vm_type']); // module_type 参数为虚拟机（2）时可能存在的虚拟化类型

        $taskType = $params['job_type'] ? explode(',', trim($params['job_type'])) : ''; //任务状态，支持英文逗号隔开的多个
        $taskStatus = $params['job_status'] ? explode(',', trim($params['job_status'])) : ''; // 任务状态 支持以英文逗号隔开的多个
        $moduleType = $params['module_type'] ? explode(',', trim($params['module_type'])) : '';//模块类型，支持英文逗号隔开的多个
        $subModuleType = $params['sub_module_type'] ? explode(',', trim($params['sub_module_type'])) : ''; //子模块类型，目前只有AWS使用

        $nodeuuid = $params['node_uuid'] ?? ''; // 节点uuid
        $storageuuid = $params['storage_uuid'] ?? ''; // 存储设备uuid

        // 先查备份集的所有备份点（恢复任务用）

        //查询磁带的storage_uuid信息
        $groupUuids = "(select group_uuid from bd_tape_group)";

        $field = "select distinct bt.task_uuid, tpt.add_time, tpt.priority, trt.tape_serial_number, bsr.storage_uuid, unix_timestamp(bri.start_time) start_time, 
                    bt.task_type, bt.task_status, bt.module_type, bt.sub_module_type, bt.task_name ,trt.driver_serial_number, bsr.storage_nickname
                    , CASE trt.task_uuid
                        WHEN trt.task_uuid IS NOT NULL THEN 1
                        ELSE 2 END AS job_status ,
                      CASE tpt.priority
                        WHEN tpt.priority IS NULL AND trt.task_uuid IS NOT NULL THEN 1 
                        ELSE tpt.priority END AS PRIORITY
                    , ( CASE bt.task_type 
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['CDP_DB_BACKUP'] . " THEN 1
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'] . " THEN 1
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] . " THEN 1
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['OS_BACKUP'] . " THEN 1
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['CDP_DB_RECOVERY'] . " THEN 2
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['DB_RECOVERY'] . " THEN 2
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY'] . " THEN 2
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['OS_RECOVERY'] . " THEN 2
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['OS_INSTANT_RECOVERY'] . " THEN 7
                    WHEN " . xphp_get_config('task', 'TASKTYPE')['OS_INSTANT_RECOVERY_MOTION'] . " THEN 8
                    ELSE bt.task_type
                    END ) AS JOB_TYPE ";
        $from = " from bd_task bt";
        $left = " LEFT JOIN bd_pending_task tpt ON bt.task_uuid = tpt.task_uuid
                LEFT JOIN bd_tape_running_task trt ON bt.task_uuid = trt.task_uuid
                LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                INNER JOIN bd_running_info bri ON bt.task_uuid = bri.task_uuid
                LEFT JOIN m365_task ON bt.task_uuid = m365_task.task_uuid
                LEFT JOIN db_list ON bt.task_uuid = db_list.task_uuid
                LEFT JOIN fs_path_list ON bt.task_uuid = fs_path_list.task_uuid
                LEFT JOIN vm_machine_list ON bt.task_uuid = vm_machine_list.task_uuid
                LEFT JOIN os_list ON bt.task_uuid = os_list.task_uuid
                LEFT JOIN copy_list ON bt.task_uuid = copy_list.task_uuid ";
        $where = "where bt.delete_flag = ? and ( ( bt.storage_uuid in {$groupUuids} AND bt.storage_uuid != '' ))";

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['source']
            );
            $sqlNew = " and bt.user_uuid in ({$userUuidSql}) ";
            $where .= $sqlNew;
        }

        //添加where
        $flag = xphp_get_config('app', 'FLAG');
        $sqlParams = [$flag['UNSET']];
        $tasktypeArr = xphp_get_config('task', 'TASKTYPE');
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');

        //模块类型
        if (!empty($moduleType)) {
            if (!empty($subModuleType) && $subModuleType != ['1','3','2']) {
                $where .= ' and bt.module_type in (' . implode(',', $moduleType) . ') and bt.sub_module_type in (' . implode(',', $subModuleType) . ') ';
            } else {
                $where .= ' and bt.module_type in (' . implode(',', $moduleType) . ') ';
            }
        }

        // 虚拟机/ip查询
        $tasktype = xphp_get_config('task', 'TASKTYPE');
        if (!empty($otherVmName)) {
            //vm_machine_list 表联合查询
            $left .= ' left join vm_machine_list vml on bt.task_uuid = vml.task_uuid ';
            $where .= ' and (vml.vm_name like ? or vml.vcenter_ip like ? )';
            $sqlParams = array_merge($sqlParams, [
                '%' . $otherVmName . '%',
                '%' . $otherVmName . '%'
            ]);
        }

        //任务类型
        if (!empty($taskType)) {
            // 默认备份恢复包含vm/fs/db/os/nas
            if (in_array($tasktypeArr['BACKUP'], $taskType)) {
                $taskType = array_merge($taskType, [$tasktypeArr['DB_BACKUP'], $tasktypeArr['OS_BACKUP']]);
            }

            if (in_array($tasktypeArr['RECOVERY'], $taskType)) {
                $taskType = array_merge($taskType, [$tasktypeArr['DB_RECOVERY'], $tasktypeArr['OS_RECOVERY']]);
            }
            $where .= ' and bt.task_type in (' . implode(',', $taskType) . ') ';
        }

        //任务状态
        if (!empty($taskStatus)) {
            $where .= 'and bt.task_status in (' . implode(',', $taskStatus) . ') ';
        }

        //任务名
        if (!empty($taskName)) {
            $where .= ' and bt.task_name like ? ';
            $sqlParams = array_merge($sqlParams, array('%' . $taskName . '%'));
        }

        // 主机名/IP/别名
        if (!empty($otherHostName)) {
            // 加上 bd_agent 表联合查询
            // bd_task与bd_agent通过bd_task_agent_list关联, 一个任务可能备份多个客户端，一个客户端可能由多个任务，多对多关系
            // 查询采用task_uuid IN ()
            // 获取满足条件的任务uuid
            $clientSql = "SELECT btal.task_uuid
                            FROM bd_task_agent_list btal
                                INNER JOIN bd_agent ba ON ba.agent_uuid = btal.agent_uuid
                            WHERE ba.agent_name LIKE ? OR ba.hostname LIKE ? OR ba.ip LIKE ? ";
            $agentName = '%' . $otherHostName . '%';
            $taskList = $this->dbSelect($clientSql, [$agentName, $agentName, $agentName]);
            $taskUuidList = [];
            if (is_array($taskList) && $taskList) {
                $taskUuidList = array_values(array_unique(array_column($taskList, 'task_uuid')));
            }
            $taskUuids = "'" . implode("', '", $taskUuidList) . "'";
            $where .= " and bt.task_uuid IN ($taskUuids) ";
        }

        // 模块类型为虚拟机可能会有虚拟化类型
        if ($vmType > 0) {
            $from .= ', vm_task vt_s';
            $where .= ' and bt.task_uuid = vt_s.task_uuid and vt_s.hypervisor_type = ? ';
            $sqlParams = array_merge($sqlParams, [$vmType]);
        }

        // 模块类型为数据库可能会有数据库类型
        if ($dbType > 0) {
            $from .= ', db_task dt';
            $where .= ' and bt.task_uuid = dt.task_uuid and dt.db_type = ? ';
            $sqlParams = array_merge($sqlParams, [$dbType]);
        }

        // 节点
        if (!empty($nodeuuid)) {
            $where .= ' and bt.node_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($nodeuuid));
        }

        // 存储设备uuid
        if (!empty($storageuuid)) {
            $where .= ' and bt.storage_uuid = ? ';
            $sqlParams = array_merge($sqlParams, array($storageuuid));
        }
        $count = $this->dbSelect('select count(DISTINCT bt.task_uuid) as total ' . $from . $left . $where, $sqlParams);
        // dump('select count(DISTINCT bt.task_uuid) as total ' . $from . $left . $where, $sqlParams);
        if (empty($count[0]['total'])) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            )
                ? 'desc' : strtolower($params['order']);
            $sortArr = [
                'job_name' => 'bt.task_name',
                'module_type' => 'bt.module_type, bt.sub_module_type',
                'job_type' => 'JOB_TYPE',
                'job_status' => 'bt.task_status',
                'add_time' => 'tpt.add_time',
                'priority' => 'PRIORITY'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? 'bt.id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ' , bt.id desc');
            $order = " order by " . $sort;
        }

        $limit = ' limit ? , ?';
        $sqlParams = array_merge($sqlParams, array($start, $length));
        // dump($field . $from . $left . $where . $order . $limit , $sqlParams);
        $data = $this->dbSelect($field . $from . $left . $where . $order . $limit, $sqlParams);
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        $records = [];
        $jobInfo = new JobInfo();
        foreach ($data as $d) {
            $records[] = array(
                'job_uuid' => $d['task_uuid'], //任务uuid
                'job_name' => $d['task_name'],
                'add_time' => !empty($d['add_time']) ? $d['add_time'] : '--',
                'job_status' => $d['task_status'],
                'tape_job_status' => $d['job_status'],
                'continue_time' => $jobInfo->getTimeInterval($d['start_time'], $d['task_status']), // 持续时间
                'priority' => !empty($d['PRIORITY']) ? intval($d['PRIORITY']) : '--',
                'job_type' => $this->getTaskNameString(
                    $moduleType,
                    $taskType,
                    $d['module_type'],
                    $d['task_type']
                ),  // 任务类型
                'job_type_value' => $d['task_type'],
                'module_type' => $d['module_type'],
                'sub_module_type_value' => $d['sub_module_type'],
                'driver_serial_number' => empty($d['driver_serial_number']) ? '--' : $d['driver_serial_number'],
                'driver_name' => $this->getDriverName($d['driver_serial_number']) ?? '--',
                'group_uuid' => $d['storage_uuid'] ?? '--',
                'group_name' => $d['storage_nickname'] ?? '--',
            );
        }

        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 任务中的任务类型修改部分描述,在任务中的当前任务虚拟机  文件  数据库中用
     * @param array $moduleType 所有模块列表
     * @param array $taskType   所有任务类型
     * @param int   $moduletype 模块类型
     * @param int   $tasktype   任务类型
     * @return string
     */
    private function getTaskNameString(array $moduleType, array $taskType, int $moduletype, int $tasktype): string
    {

        $moduleTypeArr = [
            $moduleType['DB'] => [ // 如果为数据库
                $taskType['DB_BACKUP'] => ('WEB_PLATFORM_DES_BACKUP'),
                $taskType['DB_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OEM_DBCDP'] => [ // 如果为数据库实时
                $taskType['DB_CDP_BACKUP'] => 'CDP',
                $taskType['DB_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OEM_FSCDP'] => [
                $taskType['FILE_CDP_BACKUP'] => ('WEB_PLATFORM_DES_REAL_TIME_SYN'),
                $taskType['DB_CDP_RECOVERY'] => ('WEB_PLATFORM_DES_RECOVERY'),
            ],
            $moduleType['OS'] => [
                $taskType['OS_BACKUP'] => ('WEB_PLATFORM_DES_BACKUP'),
                $taskType['OS_INSTANT_RECOVERY'] => ('WEB_PLATFORM_DES_INSTANT_RECOVERY'),
                $taskType['OS_INSTANT_RECOVERY_MOTION'] => ('WEB_PLATFORM_DES_MOTION'),
            ],
        ];

        return !empty($moduleTypeArr[$moduletype][$tasktype])
            ? xphp_get_lang($moduleTypeArr[$moduletype][$tasktype]) :
            (xphp_get_desc('Pf', 'TASKTYPEDES')[$tasktype] ?? '');
    }

    /**
     * 通过磁带编号获取磁带组名
     */
    public function getDriverName($serialNumber)
    {
        $sql = "select name, driver_path from bd_tape_driver where serial_number = ?";
        $sqlParam = array($serialNumber);
        $driverName = $this->dbSelect($sql, $sqlParam);

        return $driverName[0]['name'];
    }

    /**
     * 通过磁带编号获取磁带组名
     * @param string $tape_serial_number 磁带编号
     * @param bool $flag 是否获取name，false则只返回group_uuid
     */
    public function getGroupNameByTape($tape_serial_number, $flag)
    {
        $sql = "select group_uuid from bd_tape_carriage where serial_number = ?";
        $sqlParam = array($tape_serial_number);
        $group_uuid = $this->dbSelect($sql, $sqlParam);
        if (!$flag) {
            return $group_uuid[0]['group_uuid'];
        }

        $group_name = $this->getGroupName($group_uuid[0]['group_uuid']);

        return $group_name;
    }

    /**
     * 获取磁带组名
     */
    public function getGroupName($group_uuid)
    {
        $sqls = "select name from bd_tape_group where group_uuid = ?";
        $sqlParams = array($group_uuid);
        $group_name = $this->dbSelect($sqls, $sqlParams);
        // dump($group_name);
        return $group_name[0]['name'];
    }

    /**
     * 获取磁带监控信息/列表
     * @param $params
     * @return array
     */
    public function getTapeMonitorInfo ($params)
    {
        $offset = $params['offset'];
        $length = $params['limit']; 
        $search = $params['search'];
        $refreshFlag = $params['refresh_flag'];

        $sql = "SELECT 
        t.op_uuid,
        t.node_uuid,
        CASE 
            WHEN t.type IN (13, 14) THEN COALESCE(b.name, '--')
            ELSE t.object_name
        END AS object_name,
        t.type,
        t.status,
        t.start_time,
        t.end_time,
        t.error_code,
        t.details,
        t.user_name 
    FROM bd_tape_operation t
    LEFT JOIN bd_tape_backup_set b 
        ON t.type IN (13, 14) AND t.object_name = b.backup_set_uuid";
        $sqlParam = [$offset, $length];
        $sqlCount = 'select count(*) as total from bd_tape_operation';

        //如果refreshFlag，那么只查询扫描全部操作的一部分信息并且返回
        if ($refreshFlag) {
            $sql = "select status, start_time from bd_tape_operation where type = 1 order by start_time desc limit 1";
            $data = $this->dbSelect($sql);
            return [
                'status' => $data[0]['status'],
                'start_time' => $data[0]['start_time']
            ];
        }

        if (!empty($search)) {
            $sql .= ' where object_name like "%'.$search.'%"';
            $sqlCount .= ' where object_name like "%'.$search.'%"';
        }

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            ) ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'type' => 'type',
                'status' => 'status',
                'start_time' => 'start_time',
                'end_time' => 'end_time'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? 'id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ' ');
            $sql .= ' order by ' . $sort;
        }

        $limit = ' limit ? , ?';
        $count = $this->dbSelect($sqlCount);
        $data = $this->dbSelect($sql . $limit, $sqlParam);

        $records = [];
        foreach ($data as $d) {
            $records[] = array(
                'object_name' => $d['object_name'] == '' ? '--' : $d['object_name'],
                'op_uuid' => $d['op_uuid'],
                'type' => intval($d['type']),
                'status' => intval($d['status']),
                'start_time' => $d['start_time'],
                'end_time' => !empty($d['end_time']) ? $d['end_time'] : '--',
                'user_name' => $d['user_name'],
                'error_code' => $d['error_code'],
                'description' => $this->getErrorCodeDes($d['error_code'])
            );
        }

        return [
            'rows' => $records,
            'total' => $count[0]['total']
        ];
    }

    /**
     * 获取磁带监控错误信息描述(先简单解析错误码描述)
     * @param $errorCode
     * @return string
     */
    private function getErrorCodeDes ($errorCode)
    {
        $desStr = xphp_get_lang('WEB_PUBLIC_SUCCESS');
        if (!empty($errorCode)) {
            $errorConf = xphp_get_config('error');
            $errorStr = $errorConf['errorCodeDes'][$errorConf['errorCode'][$errorCode]];
            $errorClass = 'font-red-thunderbird';
            $desStr = xphp_get_lang('WEB_PUBLIC_FAILURE') .'[' . '<span  class="' . $errorClass . '">#' . $errorCode . '</span>' . ']' . $errorStr;
        }

        return $desStr;
    }

    /**
     * 获取磁带备份集模块
     * @param array $params 请求参数
     * @return array
     */
    public function getBackupModule(array $params): array
    {
        // 从时间点主表查询出所有是磁带备份的模块
        $storageType = xphp_get_config('storage', 'BD_STORAGE_TYPE');
        $where = ' where bbt.storage_uuid = bsr.storage_uuid and bsr.storage_type = ? ';
        if (!empty($params['module_type'])) {
            $where .= ' and bbt.module_type = ' . $params['module_type'];
        }

        $table = ' bd_backup_timepoint bbt,bd_storage_resource bsr ';
        $sqlCount = "select count(DISTINCT bbt.module_type) num
                    from {$table} {$where}";
        $sqlParam = [$storageType['TAPE']];

        $total = $this->dbSelect($sqlCount, $sqlParam);
        $return = [
            'total' => 0,
            'rows' => []
        ];

        if (empty($total[0]['num'])) {
            return $return;
        }

        $return['total'] = $total[0]['num'];

        $sql = "select DISTINCT bbt.module_type
                    from {$table} {$where} 
                    order by bbt.module_type asc
                    limit ?,?";
        $sqlParam = array_merge($sqlParam, [$params['offset'], $params['limit']]);
        $data = $this->dbSelect($sql, $sqlParam);

        $moduleType = [
            2 => xphp_get_lang('UI_VISUAL_VM'),          // 虚拟机 2
            4 => xphp_get_lang('WEB_PLATFORM_DES_DB'),          // 数据库  4
            3 => xphp_get_lang('WEB_PLATFORM_DES_FS'),        // 文件   3
            5 => xphp_get_lang('WEB_PLATFORM_DES_OS'),          // 操作系统 5
            11 => xphp_get_lang('WEB_PLATFORM_DES_NAS'),         // NAS  11
            14 => xphp_get_lang('WEB_PLATFORM_DES_EXCHANGE'),   // office365（exchange）
            28 => xphp_get_lang('WEB_K8S_PROTECT'),        // 容器 28
        ];

        foreach ($data as $item) {
            $return['rows'][] = [
                'uuid' => $item['module_type'],
                'module_type' => $item['module_type'],
                'name' => $moduleType[$item['module_type']] ?? '--',
            ];
        }
        return $return;
    }

    /**
     * 获取磁带备份集模块树
     * @param array $params 请求参数
     * @return array
     */
    public function getBackupTree(array $params): array
    {

        session_write_close();
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $storageType = xphp_get_config('storage', 'BD_STORAGE_TYPE');
        if (!empty($params['pid']) && !empty($params['module_type'])) {
            // 表示某个对象下面还有更多
            $subSql = "SELECT 1 
                        FROM bd_backup_timepoint bbt
                        INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                        WHERE bbt.backup_mode = 1 
                          AND bsr.storage_type = {$storageType['TAPE']} 
                          AND bbt.module_type = {$params['module_type']}";
            // 具体的对象
            switch ($params['module_type']) {
                case $moduleType['VM']:
                    $news = $this->getVmList($subSql, $params, $params['module_type']);
                    break;
                case $moduleType['DB']:
                    $news = $this->getDbList($subSql, $params, $params['module_type']);
                    break;
                case $moduleType['OS']:
                    $news = $this->getOsList($subSql, $params, $params['module_type']);
                    break;
                case $moduleType['FS']:
                case $moduleType['NAS']:
                    $news = $this->getFsList($subSql, $params, $params['module_type']);
                    break;
                case $moduleType['M365']:
                    $news = $this->getM365List($subSql, $params, $params['module_type']);
                    break;
                case $moduleType['KUBERNETES']:
                    $news = $this->getKubuList($subSql, $params, $params['module_type']);
                    break;
                default:
                    $news = [];
            }
            return [
                'rows' => $news
            ];
        }
        $paramss = $params;
        $paramss['limit'] = 10;
        $module = $this->getBackupModule($paramss);
        $list = $module['rows'];
        $return = [];
        if (!empty($list)) {
            $vmPlatformLogic = new VmPlatform();
            // 查询出模块下面的所有虚拟化中心
            foreach ($list as $item) {
                $return[] = [
                    'id' => $item['uuid'],
                    'uuid' =>  $item['uuid'],
                    'pId' => 0,
                    'title' => $item['name'],
                    'name' => $item['name'],
                    'open' => true,
                    'nocheck' => false,
                    'iconSkin' => 'ztree-li-img-' . $item['module_type'],
                    'module_type' => $item['module_type'],
                    'isParent' => true,
                    'clickshow' => true,
                    'is_more' => false,
                ];
                $subSql = "SELECT 1 
                                FROM bd_backup_timepoint bbt
                                INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                                WHERE bsr.storage_type = {$storageType['TAPE']} 
                                  AND bbt.backup_mode = 1 
                                  AND bbt.module_type = {$item['module_type']}";
                if ($item['module_type'] == $moduleType['VM']) {
                    // 如果是虚拟化，那么第二层是虚拟化中心
                    $sql = "SELECT vbt.vcenter_uuid, dir_path,hypervisor_type
                            FROM vm_backup_timepoint vbt
                            WHERE EXISTS ({$subSql} and bbt.timepoint_uuid = vbt.timepoint_uuid)
                            group by vbt.vcenter_uuid";

                    $second = $this->dbSelect($sql);
                    if (!empty($second)) {
                        foreach ($second as $item2) {
                            $name = explode('/', $item2['dir_path']);
                            $name2 = explode(',', $item2['dir_path']);
                            $return[] = [
                                'id' => $item2['vcenter_uuid'],
                                'uuid' => $item2['vcenter_uuid'],
                                'pId' => $item['uuid'],
                                'title' => $name2[0],
                                'name' => $name[0],
                                'open' => true,
                                'nocheck' => false,
                                'iconSkin' => $vmPlatformLogic->getHypervisorIcon($item2['hypervisor_type'], 1),
                                'module_type' => $item['module_type'],
                                'isParent' => true,
                                'clickshow' => true,
                                'is_more' => false,
                            ];
                        }
                    }
                }
                // 具体的对象
                switch ($item['module_type']) {
                    case $moduleType['VM']:
                        $news = $this->getVmList($subSql, $params, $item['module_type']);
                        break;
                    case $moduleType['DB']:
                        $news = $this->getDbList($subSql, $params, $item['module_type']);
                        break;
                    case $moduleType['OS']:
                        $news = $this->getOsList($subSql, $params, $item['module_type']);
                        break;
                    case $moduleType['FS']:
                    case $moduleType['NAS']:
                        $news = $this->getFsList($subSql, $params, $item['module_type']);
                        break;
                    case $moduleType['M365']:
                        $news = $this->getM365List($subSql, $params, $item['module_type']);
                        break;
                    case $moduleType['KUBERNETES']:
                        $news = $this->getKubuList($subSql, $params, $params['module_type']);
                        break;
                    default:
                        $news = [];
                }

                $return = array_merge($return, $news);
            }
        }
        return [
            'rows' => $return
        ];
    }

    /**
     * 获取虚拟化的所有对象
     * @param string $subSql 子查询
     * @param array $params 请求参数
     * @param int $moduleType 模块
     * @return array
     */
    private function getVmList(string $subSql, array $params, int $moduleType = 2): array
    {

        $return = [];
        // vm_backup_timepoint
        $where = '';
        if (!empty($params['pid'])) {
            $where = " and vbt.vcenter_uuid = '{$params['pid']}'";
        }
        if (!empty($params['keyword'])) {
            $where .= " and vbt.vm_name like '%{$params['keyword']}%'";
        }

        $subSql .= ' and bbt.timepoint_uuid = vbt.timepoint_uuid';
        $where .= " group by vbt.vm_uuid,vbt.vcenter_uuid";
        $sqlCount = "SELECT count(*) num from (
                        select vbt.vm_timepoint_id
                        FROM vm_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql}
                        ) {$where} ) ss";
        $total = $this->dbSelect($sqlCount);
        if (!empty($total[0]['num'])) {
            $field = 'vbt.vm_uuid,vbt.vm_name,vbt.vcenter_uuid,vbt.dir_path,vbt.timepoint_uuid';
            $sql = "select * from (SELECT {$field}
                        FROM vm_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql}
                        ) {$where} ) ss
                        ORDER BY timepoint_uuid ASC 
                        LIMIT {$params['offset']}, {$params['limit']}";
            $lists = $this->dbSelect($sql);
            // 计算下是否还有更多
            $isMore = $params['offset'] + $params['limit'] < $total[0]['num'];
            $length = count($lists);
            $k = 0;
            foreach ($lists as $itemv) {
                $k ++;
                $return[] = [
                    'id' => $itemv['vm_uuid'],
                    'uuid' => $itemv['vm_uuid'] . '_' . $moduleType,
                    'pId' => $itemv['vcenter_uuid'],
                    'title' => $itemv['dir_path'],
                    'name' => $itemv['vm_name'],
                    'open' => true,
                    'nocheck' => false,
                    'iconSkin' => 'vm_vmware_vm_poweroff',
                    'module_type' => $moduleType,
                    'isParent' => false,
                    'clickshow' => false,
                    'is_more' => ($k == $length) ? $isMore : false,
                ];
            }

        }
        return $return;
    }

    /**
     * 获取数据库的所有对象
     * @param string $subSql 子查询
     * @param array $params 请求参数
     * @param int $moduleType 模块
     * @return array
     */
    private function getDbList(string $subSql, array $params, int $moduleType = 4): array
    {

        $return = [];
        // db_backup_timepoint
        $where = '';
        if (!empty($params['keyword'])) {
            $where = " and vbt.agent_ip like '%{$params['keyword']}%'";
        }
        $field = 'count(vbt.agent_uuid) num';
        $subSql .= ' and bbt.timepoint_uuid = vbt.timepoint_uuid';
        $sqlCount = "SELECT {$field}
                        FROM db_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        ) ";
        $total = $this->dbSelect($sqlCount);
        if (!empty($total[0]['num'])) {
            $field = 'distinct vbt.agent_uuid,vbt.agent_ip';
            $sql = "SELECT {$field}
                        FROM db_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        )
                        ORDER BY vbt.agent_uuid ASC 
                        LIMIT {$params['offset']}, {$params['limit']}";
            $lists = $this->dbSelect($sql);
            // 计算下是否还有更多
            $isMore = $params['offset'] + $params['limit'] < $total[0]['num'];
            $length = count($lists);
            $k = 0;
            foreach ($lists as $itemv) {
                $k ++;
                $return[] = [
                    'id' => $itemv['agent_uuid'],
                    'uuid' => $itemv['agent_uuid'] . '_' . $moduleType,
                    'pId' => $moduleType,
                    'title' => $itemv['agent_ip'],
                    'name' => $itemv['agent_ip'],
                    'open' => true,
                    'nocheck' => false,
                    'iconSkin' => 'vm_vmware_host',
                    'module_type' => $moduleType,
                    'isParent' => false,
                    'clickshow' => false,
                    'is_more' => ($k == $length) ? $isMore : false,
                ];
            }
        }
        return $return;
    }

    /**
     * 获取操作系统的所有对象
     * @param string $subSql 子查询
     * @param array $params 请求参数
     * @param int $moduleType 模块
     * @return array
     */
    private function getOsList(string $subSql, array $params, int $moduleType = 5): array
    {

        $return = [];
        // os_backup_timepoint
        $where = '';
        if (!empty($params['keyword'])) {
            $where = " and (
            vbt.agent_ip like '%{$params['keyword']}%' or
            vbt.os_name like '%{$params['keyword']}%'
            )";
        }
        $subSql .= ' and bbt.timepoint_uuid = vbt.timepoint_uuid';
        $field = 'count(distinct vbt.agent_uuid) num';
        $sqlCount = "SELECT {$field}
                        FROM os_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        ) ";
        $total = $this->dbSelect($sqlCount);
        if (!empty($total[0]['num'])) {
            $field = 'distinct vbt.agent_uuid,vbt.agent_ip,vbt.os_name,vbt.dir_path';
            $sql = "SELECT {$field}
                        FROM os_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        )
                        ORDER BY vbt.agent_uuid ASC 
                        LIMIT {$params['offset']}, {$params['limit']}";
            $lists = $this->dbSelect($sql);
            // 计算下是否还有更多
            $isMore = $params['offset'] + $params['limit'] < $total[0]['num'];
            $length = count($lists);
            $k = 0;
            foreach ($lists as $itemv) {
                $k ++;
                $return[] = [
                    'id' => $itemv['agent_uuid'],
                    'uuid' => $itemv['agent_uuid'] . '_' . $moduleType,
                    'pId' => $moduleType,
                    'title' => $itemv['dir_path'],
                    'name' => $itemv['os_name'] . '(' . $itemv['agent_ip'] . ')',
                    'open' => true,
                    'nocheck' => false,
                    'iconSkin' => 'vm_vmware_host',
                    'module_type' => $moduleType,
                    'isParent' => false,
                    'clickshow' => false,
                    'is_more' => ($k == $length) ? $isMore : false,
                ];
            }
        }
        return $return;
    }

    /**
     * 获取文件的所有对象
     * @param string $subSql 子查询
     * @param array $params 请求参数
     * @param int $moduleType 模块
     * @return array
     */
    private function getFsList(string $subSql, array $params, int $moduleType = 3): array
    {

        $return = [];
        // os_backup_timepoint
        $where = '';
        if (!empty($params['keyword'])) {
            $where = " and (
            vbt.agent_ip like '%{$params['keyword']}%' or
            vbt.agent_name like '%{$params['keyword']}%'
            )";
        }
        $subSql .= ' and bbt.timepoint_uuid = vbt.fs_timepoint_uuid';
        $field = 'count(distinct vbt.agent_uuid) num';
        $sqlCount = "SELECT {$field}
                        FROM fs_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        ) ";
        $total = $this->dbSelect($sqlCount);
        if (!empty($total[0]['num'])) {
            $field = 'distinct vbt.agent_uuid,vbt.agent_ip,vbt.agent_name';
            $sql = "SELECT {$field}
                        FROM fs_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        )
                        ORDER BY vbt.agent_uuid ASC 
                        LIMIT {$params['offset']}, {$params['limit']}";
            $lists = $this->dbSelect($sql);
            // 计算下是否还有更多
            $isMore = $params['offset'] + $params['limit'] < $total[0]['num'];
            $length = count($lists);

            $k = 0;
            foreach ($lists as $itemv) {
                $k ++;
                $return[] = [
                    'id' => $itemv['agent_uuid'],
                    'uuid' => $itemv['agent_uuid'] . '_' . $moduleType,
                    'pId' => $moduleType,
                    'title' => $itemv['agent_name'],
                    'name' => $itemv['agent_name'] . '(' . $itemv['agent_ip'] . ')',
                    'open' => true,
                    'nocheck' => false,
                    'iconSkin' => 'vm_vmware_host',
                    'module_type' => $moduleType,
                    'isParent' => false,
                    'clickshow' => false,
                    'is_more' => ($k == $length) ? $isMore : false,
                ];
            }
        }
        return $return;
    }

    /**
     * 获取文M365的所有对象
     * @param string $subSql 子查询
     * @param array $params 请求参数
     * @param int $moduleType 模块
     * @return array
     */
    private function getM365List(string $subSql, array $params, int $moduleType = 14): array
    {

        $return = [];
        // m365_backup_timepoint
        $where = '';
        if (!empty($params['keyword'])) {
            $where = " and vbt.organization_name like '%{$params['keyword']}%'";
        }
        $subSql .= ' and bbt.timepoint_uuid = vbt.m365_timepoint_uuid';
        $field = 'count(vbt.organization_uuid) num';
        $sqlCount = "SELECT {$field}
                        FROM m365_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        ) ";
        $total = $this->dbSelect($sqlCount);
        if (!empty($total[0]['num'])) {
            $field = 'distinct vbt.organization_uuid,vbt.organization_name,vbt.cache_path';
            $sql = "SELECT {$field}
                        FROM m365_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        )
                        ORDER BY vbt.organization_uuid ASC 
                        LIMIT {$params['offset']}, {$params['limit']}";
            $lists = $this->dbSelect($sql);
            // 计算下是否还有更多
            $isMore = $params['offset'] + $params['limit'] < $total[0]['num'];
            $length = count($lists);
            $k = 0;
            foreach ($lists as $itemv) {
                $k ++;
                $return[] = [
                    'id' => $itemv['organization_uuid'],
                    'uuid' => $itemv['organization_uuid'] . '_' . $moduleType,
                    'pId' => $moduleType,
                    'title' => $itemv['cache_path'],
                    'name' => $itemv['organization_name'],
                    'open' => true,
                    'nocheck' => false,
                    'iconSkin' => 'vm_vmware_host',
                    'module_type' => $moduleType,
                    'isParent' => false,
                    'clickshow' => false,
                    'is_more' => ($k == $length) ? $isMore : false,
                ];

            }
        }
        return $return;
    }

    /**
     * 获取容器的所有对象
     * @param string $subSql 子查询
     * @param array $params 请求参数
     * @param int $moduleType 模块
     * @return array
     */
    private function getKubuList(string $subSql, array $params, int $moduleType = 14): array
    {

        $return = [];
        // kube_backup_timepoint
        $where = '';
        if (!empty($params['keyword'])) {
            $where = " and vbt.cluster_name like '%{$params['keyword']}%'";
        }
        $subSql .= ' and bbt.timepoint_uuid = vbt.timepoint_uuid';
        $field = 'count(vbt.cluster_uuid) num';
        $sqlCount = "SELECT {$field}
                        FROM kube_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        ) ";
        $total = $this->dbSelect($sqlCount);
        if (!empty($total[0]['num'])) {
            $field = 'distinct vbt.cluster_uuid,vbt.cluster_name';
            $sql = "SELECT {$field}
                        FROM kube_backup_timepoint vbt
                        WHERE EXISTS (
                            {$subSql} {$where}
                        )
                        ORDER BY vbt.cluster_uuid ASC 
                        LIMIT {$params['offset']}, {$params['limit']}";
            $lists = $this->dbSelect($sql);
            // 计算下是否还有更多
            $isMore = $params['offset'] + $params['limit'] < $total[0]['num'];
            $length = count($lists);
            $k = 0;
            foreach ($lists as $itemv) {
                $k++;
                $return[] = [
                    'id' => $itemv['cluster_uuid'],
                    'uuid' => $itemv['cluster_uuid'] . '_' . $moduleType,
                    'pId' => $moduleType,
                    'title' => $itemv['cluster_name'],
                    'name' => $itemv['cluster_name'],
                    'open' => true,
                    'nocheck' => false,
                    'iconSkin' => 'vm_vmware_host',
                    'module_type' => $moduleType,
                    'isParent' => false,
                    'clickshow' => false,
                    'is_more' => ($k == $length) ? $isMore : false,
                ];

            }
        }
        return $return;
    }

    /**
     * 获取磁带备份对象下的时间点列表
     * @param array $params 请求参数
     * @return array|void
     * @throws \Exception
     */
    public function getBackupTimepoint(array $params)
    {

        session_write_close();
        $params['limit'] = $params['limit'] ?? 10;
        if ($params['offset'] == 0) {
            // 构建时间点表和磁带设备对应临时表
            $delTable = "DROP TABLE IF EXISTS temp_bd_tape_backup_file";
            $this->dbExec($delTable);

            $tempTable2 = "CREATE TABLE IF NOT EXISTS temp_bd_tape_backup_file AS SELECT
                        SUBSTRING_INDEX( SUBSTRING_INDEX( file_path, '/', 2 ), '/', - 1 ) AS timepoint_uuid,
                        GROUP_CONCAT(DISTINCT tape_serial_number) tape_serial_number 
                        FROM
                            bd_tape_backup_file 
                        GROUP BY
                            timepoint_uuid";
            $result = $this->dbQuery($tempTable2);
            if ($result) {
                $buildSql = "ALTER TABLE temp_bd_tape_backup_file ADD INDEX idx_timepoint_uuid (timepoint_uuid)";
                $this->dbExec($buildSql);
            }
        } else {
            // 判断下，如果不存在
            $tempTable2 = "CREATE TABLE IF NOT EXISTS temp_bd_tape_backup_file AS SELECT
                        SUBSTRING_INDEX( SUBSTRING_INDEX( file_path, '/', 2 ), '/', - 1 ) AS timepoint_uuid,
                        GROUP_CONCAT(DISTINCT tape_serial_number) tape_serial_number 
                        FROM
                            bd_tape_backup_file 
                        GROUP BY
                            timepoint_uuid";
            $result = $this->dbQuery($tempTable2);
            if ($result) {
                $buildSql = "ALTER TABLE temp_bd_tape_backup_file ADD INDEX idx_timepoint_uuid (timepoint_uuid)";
                $this->dbExec($buildSql);
            }
        }

        $fields = "bbt.timepoint,bbt.task_type,bbt.task_name,bbt.task_uuid, bbt.backup_mode,
                    bbt.module_type,bbt.timepoint_uuid,btg.reserve_days,btg.NAME taps_name,
                    btbs.last_write_time, btbs.NAME tap_set_name,btg.reserve_strategy_type,
                    COALESCE(tape_agg.tape_serials, '') as lib_name,
                CASE
                        bbt.module_type 
                        WHEN 2 THEN
                        ( SELECT vm_name FROM vm_backup_timepoint WHERE timepoint_uuid = bbt.timepoint_uuid ) 
                        WHEN 4 THEN
                        ( SELECT agent_ip FROM db_backup_timepoint WHERE timepoint_uuid = bbt.timepoint_uuid ) 
                        WHEN 5 THEN
                        ( SELECT agent_ip FROM os_backup_timepoint WHERE timepoint_uuid = bbt.timepoint_uuid ) 
                        WHEN 14 THEN
                        ( SELECT organization_name FROM m365_backup_timepoint WHERE organization_uuid = bbt.timepoint_uuid )
                        ELSE ( SELECT agent_ip FROM fs_backup_timepoint WHERE fs_timepoint_uuid = bbt.timepoint_uuid ) 
                    END AS object_name";
        $table = " INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                    INNER JOIN bd_tape_group btg ON btg.group_uuid = bbt.storage_uuid
                    INNER JOIN bd_tape_backup_set btbs ON btbs.group_uuid = bbt.storage_uuid 
                    LEFT JOIN (
                            SELECT 
                                timepoint_uuid,
                                (tape_serial_number) as tape_serials
                            FROM temp_bd_tape_backup_file 
                            GROUP BY timepoint_uuid
                        ) tape_agg ON tape_agg.timepoint_uuid = bbt.timepoint_uuid
                    ";

        $where = " bsr.storage_type = 10 ";
        if (!empty($params['keyword'])) {
            $where .= " and bbt.task_name like  '%{$params['keyword']}%'";
        }

        $tables = '';
        // 处理UUID过滤条件
        if (!empty($params['uuid']) && empty($params['timepoint_uuids'])) {
            // 表示选择了对象并且未选指定的时间点
            // 1、对象的是 对象uuid + module
            // 2、有可能只是选择了模块或者虚拟化中心
            // uuid是36位，那就是虚拟化中心、uuid是数字，那就是模块
            $modules = [];
            $vcenter = [];
            $objectUuid = [];
            if (is_string($params['uuid'])) {
                $params['uuid'] = array_filter(explode(',', $params['uuid']));
            }
            foreach ($params['uuid'] as $ite) {
                if (is_numeric($ite)) {
                    $modules[] = $ite;
                    continue;
                }
                $uuids = explode('_', $ite);
                if (count($uuids) == 2) {
                    $objectUuid[] = $uuids[0];
                    // $modules[] = $uuids[1];
                    continue;
                }
                if (strlen($ite) == 36) {
                    $vcenter[] = $ite;
                }
            }

            $wheres = [];

            if (!empty($modules)) {
                $moduleString = "'" . implode("','", $modules) . "'";
                $wheres[] = " bbt.module_type in ({$moduleString}) ";
            }

            $sqlv = [];
            if (!empty($vcenter) && !empty($objectUuid)) {
                // 存在虚拟化中心，存在对象
                $vcenterString = "'" . implode("','", $vcenter) . "'";
                $objectUuidString = "'" . implode("','", $objectUuid) . "'";
                $sqlv[] = " WHEN 2 THEN (vbt.vcenter_uuid IN ({$vcenterString}) OR vbt.vm_uuid IN ({$objectUuidString})) ";
            } elseif (!empty($vcenter) && empty($objectUuid)) {
                // 存在虚拟化中心，不存在对象
                $vcenterString = "'" . implode("','", $vcenter) . "'";
                $sqlv[] = " WHEN 2 THEN (vbt.vcenter_uuid IN ({$vcenterString})) ";
            } elseif (empty($vcenter) && !empty($objectUuid)) {
                // 存在对象，不存在虚拟化中心
                $objectUuidString = "'" . implode("','", $objectUuid) . "'";
                $sqlv[] = " WHEN 2 THEN (vbt.vm_uuid IN ({$objectUuidString})) ";
            }

            if (!empty($objectUuid)) {
                // 存在对象
                $objectUuidString = "'" . implode("','", $objectUuid) . "'";
                $sqlv[] = " WHEN 4 THEN (dbt.agent_uuid IN ({$objectUuidString})) ";
                $sqlv[] = " WHEN 5 THEN (obt.agent_uuid IN ({$objectUuidString})) ";
                $sqlv[] = " WHEN 14 THEN (mbt.organization_uuid IN ({$objectUuidString})) ";
                $sqlv[] = " ELSE (fbt.agent_uuid IN ({$objectUuidString})) ";
            }

            if (!empty($sqlv)) {
                $wheres[] = "  CASE bbt.module_type " . implode(' ', $sqlv) . " END ";
                // 在连表上面的sql进行过滤筛选
                $tables = " left join vm_backup_timepoint vbt on vbt.timepoint_uuid = bbt.timepoint_uuid
                            left join db_backup_timepoint dbt on dbt.timepoint_uuid = bbt.timepoint_uuid
                            left join os_backup_timepoint obt on obt.timepoint_uuid = bbt.timepoint_uuid
                            left join m365_backup_timepoint mbt on mbt.m365_timepoint_uuid = bbt.timepoint_uuid
                            left join fs_backup_timepoint fbt on fbt.fs_timepoint_uuid = bbt.timepoint_uuid ";
            }
            if (!empty($wheres)) {
                $where .= ' and (' . implode(' or ', $wheres) . ') ';
            }
        }

        $timepointUuids = array_filter(explode(',', $params['timepoint_uuids']));
        if (!empty($params['export']) && !empty($timepointUuids)) {
            // 导出指定的时间点列表
            $timepointUUids = "'" . implode("','", $timepointUuids) . "'";
            $where .= " and bbt.timepoint_uuid in ({$timepointUUids}) ";
        }

        $return = [
            'total' => 0,
            'rows' => []
        ];

        $sqlCount = "select count(DISTINCT bbt.timepoint_uuid) num from bd_backup_timepoint bbt {$table} {$tables} where {$where}";

        $total = $this->dbSelect($sqlCount);
        if (empty($total[0]['num'])) {
            return $return;
        }
        $return['total'] = $total[0]['num'];

        if (!empty($params['sort']) && !empty($params['order'])) {
            $sortType = !in_array(
                strtolower($params['order']),
                [
                    'desc',
                    'asc'
                ]
            )
                ? 'desc' : strtolower($params['order']);

            $sortArr = [
                'timepoint' => 'bbt.timepoint',
                'backup_mode' => 'bbt.backup_mode',
                'job_name' => 'bbt.job_name',
                'job_type' => 'bbt.job_type'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' bbt.timepoint_uuid desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', bbt.timepoint_uuid desc');
            $where .= " order by " . $sort;
        }
        $where .= " limit {$params['offset']},{$params['limit']}";
        $tabs = "(SELECT DISTINCT bbt.timepoint_uuid
                        FROM bd_backup_timepoint bbt
                        {$table} 
                        {$tables}
                        WHERE {$where}) as main_ids
                    INNER JOIN bd_backup_timepoint bbt ON main_ids.timepoint_uuid = bbt.timepoint_uuid";
        $sql = "select {$fields} from {$tabs} {$table} GROUP BY bbt.timepoint_uuid";

        $data = $this->dbSelect($sql);

        // reserve_strategy_type 为 3是不保留，1是永久保留，2是保留天数
        $nullSpace = xphp_get_config('app', 'NULLSPACE');
        $forever = xphp_get_lang('UI_STRATEGY_FOREVER');
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        $taskType = xphp_get_config('task', 'TASKTYPE');
        foreach ($data as $item) {
            if ($item['reserve_strategy_type'] == 3) {
                $leaveDays = $nullSpace;
                $expiredTime = $nullSpace;
            } elseif ($item['reserve_strategy_type'] == 1) {
                $leaveDays = $forever;
                $expiredTime = $nullSpace;
            } else {
                $leaveDays = $item['reserve_days'];
                $expiredTime = date('Y-m-d H:i:s',strtotime($item['last_write_time']) + $item['reserve_days'] * 24 * 3600);
            }
            $return['rows'][] = [
                'object_name' => $item['object_name'], // 对象名
                'id' => $item['timepoint_uuid'],
                'timepoint' => $item['timepoint'],
                'backup_mode' => $item['backup_mode'],
                'job_name' => $item['task_name'],
                'job_type' => $this->getTaskNameString(
                    $moduleType,
                    $taskType,
                    $item['module_type'],
                    $item['task_type']
                ),  // 任务类型,
                'leave_days' => $leaveDays, // 保留时间
                'expired_time' => $expiredTime, // 过期时间
                'tape_device' => $item['lib_name'], // 带库名称
                'backup_set_name' => $item['tap_set_name'], // 备份集
                'tape_group' => $item['taps_name'], // 磁带组
            ];
        }

        return $return;
    }

    /**
     * 导出数据 csv
     * @param array $params 请求参数
     * @return void
     */
    public function exportBackupTimepointWithBatchCsv(array $params)
    {
        session_write_close();

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        $dataGenerator = function($batchCount) use ($params) {
            $batchSize = 1000;
            $offset = $batchCount * $batchSize;
            // 获取总数据量
            $params['offset'] = $offset;
            $params['limit'] = $batchSize;
            return $this->getBackupTimepoint($params)['rows'];
        };
        $title = xphp_get_lang('UI_TAPE_EXPORT_TITLE');
        $headers = [
            xphp_get_lang('UI_TAPE_EXPORT_OBJECT'),
            'timepoint_uuid',
            xphp_get_lang('UI_DATA_TIMEPOINT'),
            xphp_get_lang('UI_JOB_TASK_ORCHESTRATION_EVENT_SELECT_MODE'),
            xphp_get_lang('UI_REPORT_TASK_VOL'),
            xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            xphp_get_lang('UI_TAPE_EXPORT_LEAVE_TIME'),
            xphp_get_lang('UI_TAPE_EXPORT_EXPIRED_TIME'),
            xphp_get_lang('UI_TAPE_EXPORT_DEVICE'),
            xphp_get_lang('UI_TAPE_BACKUP_SET_NAME'),
            xphp_get_lang('UI_TAPE_EXPORT_GROUP')
        ];
        $this->downloadLargeCsvStream($dataGenerator, $headers, $title . '.csv');
    }
    /**
     * 大数据量分批次下载 CSV
     * @param callable $dataGenerator 数据生成器回调函数
     * @param array $headers CSV 表头
     * @param string $filename 下载文件名
     */
    private function downloadLargeCsvStream(callable $dataGenerator, array $headers = [], string $filename = null)
    {
        set_time_limit(0);
        ini_set('memory_limit', '256M');

        if (!$filename) {
            $filename = 'large_export_' . date('Ymd_His') . '.csv';
        }

        // 设置 HTTP 头
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // 禁用输出缓冲
        if (ob_get_level()) {
            ob_end_clean();
        }

        $output = fopen('php://output', 'w');

        // 写入 BOM 头解决中文乱码
        fwrite($output, "\xEF\xBB\xBF");

        try {
            // 写入表头
            if (!empty($headers)) {
                fputcsv($output, $headers);
                flush();
            }

            $totalProcessed = 0;
            $batchCount = 0;

            // 分批获取并处理数据
            while ($batchData = $dataGenerator($batchCount)) {
                if (empty($batchData)) {
                    break;
                }

                foreach ($batchData as $row) {
                    fputcsv($output, $row);
                    $totalProcessed++;
                }

                // 立即刷新输出缓冲区
                flush();

                $batchCount++;

                // 每处理10批记录一次日志
                if ($batchCount % 10 === 0) {
                    // echo("CSV 导出进度: 已处理 {$totalProcessed} 条记录, 第 {$batchCount} 批");
                }

                // 检查客户端是否断开连接
                if (connection_aborted()) {
                    // echo("客户端断开连接，已处理 {$totalProcessed} 条记录");
                    break;
                }

                // 释放内存
                unset($batchData);
                gc_collect_cycles();
            }

            // echo("导出完成: 总共处理 {$totalProcessed} 条记录");

        } catch (\Exception $e) {
            echo("CSV error: " . $e->getMessage());
            //throw $e;
        } finally {
            fclose($output);
            exit;
        }
    }

    /**
     * 导出数据 xlsx
     * @param array $params 请求参数
     * @return void
     */
    public function exportBackupTimepointWithBatch(array $params)
    {
        session_write_close();
        set_time_limit(0);
        ini_set('memory_limit', '2048M'); // 可以适当降低

        // 🚨 启用缓存
        $cache = new Memory();
        \PhpOffice\PhpSpreadsheet\Settings::setCache($cache);

        $title = xphp_get_lang('UI_TAPE_EXPORT_TITLE');
        $header = [
            'object_name' => xphp_get_lang('UI_TAPE_EXPORT_OBJECT'),
            'timepoint' => xphp_get_lang('UI_DATA_TIMEPOINT'),
            'job_name' => xphp_get_lang('UI_REPORT_TASK_VOL'),
            'job_type' => xphp_get_lang('UI_PUBLIC_TASK_TYPE'),
            'leave_days' => xphp_get_lang('UI_TAPE_EXPORT_LEAVE_TIME'),
            'expired_time' => xphp_get_lang('UI_TAPE_EXPORT_EXPIRED_TIME'),
            'tape_device' => xphp_get_lang('UI_TAPE_EXPORT_DEVICE'),
            'backup_set_name' => xphp_get_lang('UI_TAPE_BACKUP_SET_NAME'),
            'tape_group' => xphp_get_lang('UI_TAPE_EXPORT_GROUP'),
        ];

        $relation = [
            'object_name' => ['col_name' => 'A', 'width' => 25],
            'timepoint' => ['col_name' => 'B', 'width' => 50],
            'job_name' => ['col_name' => 'C', 'width' => 50],
            'job_type' => ['col_name' => 'D', 'width' => 20],
            'leave_days' => ['col_name' => 'E', 'width' => 15],
            'expired_time' => ['col_name' => 'F', 'width' => 30],
            'tape_device' => ['col_name' => 'G', 'width' => 20],
            'backup_set_name' => ['col_name' => 'H', 'width' => 30],
            'tape_group' => ['col_name' => 'I', 'width' => 20],
        ];

        $batchSize = 2000; // 🚨 增大批次大小
        $params['offset'] = 0;
        $params['limit'] = $batchSize;
        $lists = $this->getBackupTimepoint($params);
        $total = $lists['total'];
        $totalBatches = ceil($total / $batchSize);

        $objPHPExcel = new Spreadsheet();
        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle($title);

        // 设置表头
        $headerRow = [];
        foreach ($relation as $key => $colInfo) {
            $headerRow[] = $header[$key];
            $activeSheet->getColumnDimension($colInfo['col_name'])->setWidth($colInfo['width']);
        }
        $activeSheet->fromArray([$headerRow], null, 'A1');

        // 🚨 设置表头样式
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => 'E6E6FA']
            ]
        ];
        $activeSheet->getStyle('A1:I1')->applyFromArray($headerStyle);

        $currentRow = 2;
        $startTime = time();

        // 分批处理
        for ($batch = 0; $batch < $totalBatches; $batch++) {
            $batchStart = microtime(true);

            $offset = $batch * $batchSize;
            $params['offset'] = $offset;
            $params['limit'] = $batchSize;

            if ($batch == 0) {
                $batchData = $lists['rows'];
            } else {
                $batchData = $this->getBackupTimepoint($params)['rows'];
            }

            // 🚨 批量写入数据
            $dataToWrite = [];
            foreach ($batchData as $row) {
                $dataRow = [];
                foreach ($relation as $key => $colInfo) {
                    $dataRow[] = $row[$key] ?? '';
                }
                $dataToWrite[] = $dataRow;
            }

            if (!empty($dataToWrite)) {
                $activeSheet->fromArray($dataToWrite, null, 'A' . $currentRow);
                $currentRow += count($dataToWrite);
            }

            // 释放内存
            unset($batchData, $dataToWrite);

            // 🚨 定期垃圾回收
            if ($batch % 3 === 0) {
                gc_collect_cycles();
            }

            // 记录进度
            $batchTime = round(microtime(true) - $batchStart, 2);
            $elapsed = time() - $startTime;
            $estimated = $totalBatches > 0 ? round(($elapsed / ($batch + 1)) * ($totalBatches - $batch - 1)) : 0;

            $this->writeLog("Batch {$batch}/{$totalBatches} - {$batchTime}s - Estimated: {$estimated}s remaining");
        }

        // 🚨 优化输出
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $title . '_' . date('Ymd_His') . '.xlsx"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $objWriter->setPreCalculateFormulas(false);
        $objWriter->save('php://output');

        $totalTime = time() - $startTime;
        $this->writeLog("Export completed: {$total} records in {$totalTime} seconds");

        exit;
    }
}
