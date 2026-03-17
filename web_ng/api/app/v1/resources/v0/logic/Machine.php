<?php

namespace app\v1\resources\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\VolcdpOpcode;

/**
 * note          虚拟机管理 逻辑
 * @author       wanggongxi@vinchin.com
 * @date         2024/3/5 18:24
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Machine extends Base
{
    /**
    * 获取主机列表
     * @param array $params 请求参数
     * @return array
     */
    public function getMachineLists(array $params): array
    {

        // 刷新主机列表
        $this->refreshMachine();
        /*// 测试更细节点虚拟化状态
        $opName = 'EMD_VM_UPDATE_VIRTUALIZATION_STATUS';
        $this->service()->mbTempAgentMsgs($msg, $opName, $msg['node_uuid']);*/

        $field = 'ta.*';
        $table = ' vm_emd ta ';
        $where = $array = [];

        if (!empty($params['node_uuid'])) {
            // 节点搜索
            $where[] = ' ta.node_uuid = ? ';
            $array = array_merge($array, [$params['node_uuid']]);
        }

        if (!empty($params['search'])) {
            // 主机名称
            $where[] = ' ta.name like ? ';
            $array = array_merge($array, ['%' . $params['search'] . '%']);
        }

        if (!empty($params['job_name'])) {
            // 任务名称
            $table .= ', bd_task bt ';
            $where[] = ' bt.task_name like ? ';
            $array = array_merge($array, ['%' . $params['job_name'] . '%']);
        }

        if (!empty($params['status'])) {
            // 主机状态
            $where[] = ' ta.status = ? ';
            $array = array_merge($array, [$params['status']]);
        }
        $where1 =  implode(' and ', $where);
        $wheres = ' where role != 101 ';
        if (!empty($where)) {
            $wheres .=  ' and' . $where1;
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['current_job']
            );
            $sqlNew = " and ta.task_uuid in (select task_uuid from bd_task where user_uuid in ({$userUuidSql})) ";
            $wheres .= $sqlNew;
        }

        $count = "select count(ta.id) as num from {$table} {$wheres}";

        $num = $this->dbSelect($count, $array);
        $total = $num[0]['num'];
        if (empty($total)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }

        // 还要查出任务名
        $alis = 'bt';
        if (empty($params['job_name'])) {
            $alis = 'bt2';
            $table .= ' left join bd_task bt2 on bt2.task_uuid = ta.task_uuid ';
            $table .= ' left join bd_node bn on bn.node_uuid = ta.node_uuid ';
            $table .= ' left join cdp_vol_task cvt on cvt.task_uuid = ta.task_uuid ';
        } else {
            $table .= ' left join bd_node bn on bn.node_uuid = bt.node_uuid ';
            $table .= ' left join cdp_vol_task cvt on cvt.task_uuid = bt.task_uuid ';
        }
        $field .= ",$alis.task_uuid,$alis.task_name,$alis.task_type,$alis.module_type,
        $alis.task_status,bn.ip,bn.host_name,cvt.current_task_running_stage,cvt.dev_type";
        $wheres .= " group by ta.uuid ";

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
                'vm_name' => 'ta.name',
                'job_name' => 'bt2.task_name',
                'node' => 'bn.ip',
                'status' => 'ta.status',
                'num' => 'ta.id'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' ta.id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', ta.id ' . $sortType);
            $wheres .= " order by " . $sort;
        }
        $sql = "select {$field} from {$table} {$wheres} limit " . $params['offset'] . ',' . $params['limit'];

        $list = $this->dbSelect($sql, $array);
        // 处理下数据
        $lists = [];
        $statusMsg = xphp_get_config('tempagent', 'STATUS_MSG');
        // $osType = xphp_get_config('tempagent', 'OS_TYPE');
        $diskType = xphp_get_config('tempagent', 'DISK_TYPE');
        $diskBus = xphp_get_config('tempagent', 'DISK_TARGET_BUS');
        $roleType = xphp_get_config('tempagent', 'AGENT_ROLE');
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $preStatus = xphp_get_config('tempagent', 'PREFIX_STATUS');
        $cors = xphp_get_lang('UI_VM_MACHINE_CORS');
        $driver = xphp_get_lang('UI_VM_MACHINE_DRIVER_TYPE');
        $bus = xphp_get_lang('UI_VM_MACHINE_TARGET_BUS');
        $vendor = xphp_get_config('app', 'SYSTEM_INFO');
        // 判断下num的取值
        if ($params['sort'] == 'num' && $params['order'] == 'desc') {
            // 那么为降序
            $base = $total - $params['offset'];
            $basetype = 1;
        } else {
            // 那么为升序
            $base = $params['offset'] + 1;
            $basetype = 0;
        }
        foreach ($list as $item) {
            $config = json_decode($item['config'], true);
            // 处理下磁盘
            $disk = [];
            if (!empty($config['disks'])) {
                foreach ($config['disks'] as $items2) {
                    $type = $driver . ($diskType[$items2['driver_type']] ?? '');
                    $bus2 = $bus . ($diskBus[$items2['target_bus']] ?? '');
                    $disk[] = $type . ',' . $bus2;
                }
            }
            // 处理下网络信息
            $network = [];
            if (!empty($config['interfaces'])) {
                $netModel = xphp_get_config('tempagent', 'NET_MODEL_TYPE');
                $sourceNetwork = xphp_get_lang('UI_VM_MACHINE_NETWORK_SET') . '：';
                $interfaceWork = xphp_get_lang('UI_VM_MACHINE_NETWORK_INTETFACE') . '：';
                foreach ($config['interfaces'] as $items) {
                    $type = $sourceNetwork .
                        ($items['interface_type'] == 2 ? $items['source_network'] : $items['source_bridge']);
                    $bus2 = $interfaceWork . ($netModel[$items['model_type']] ?? '');
                    $network[] = $type . ',' . $bus2;
                }
            }

            // 处理下是否处于接管启动中 61,接管启动中不能关闭/启动虚拟机
            // 如果任务是错误的情况是可以操作的 task_status = 8
            // 任务是停止的也可以操作 task_status = 4
            $closeFlag = in_array($item['current_task_running_stage'], [61]) &&
                !in_array($item['task_status'], [$taskStatus['ERROR'], $taskStatus['STOPPED']]);

            // 处理console_url https://0.0.0.0:6080/vnc.html?port=6080&path=/conf?token=
            // 把 0.0.0.0 换成当前的域名
            $newconsoleurl = str_replace('0.0.0.0:6080', $_SERVER['HTTP_HOST'] . '/web_console', $item['console_url']);

            $newconsoleurl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($newconsoleurl);
            // 角色列表
            $role = $roleType[$item['role']] ?? 'WEB_PLATFORM_PUBLIC_UNKNOWN';

            $lists[] = [
                'num' => $base,
                'vm_uuid' => $item['uuid'],
                'vm_name' => $item['name'],
                'vcpu_num' => $config['num_cpus'] . $cors,
                'memory_total' => $config['memoryMB'] / 1024 . 'GB',
                'job_name' => $item['task_name'] ?? '---',
                'status' => $item['status'],
                'status_value' => xphp_get_lang($statusMsg[$item['status']]) ?? '---',
                'os_type' => $config['os']['os_type'] ?: $item['os_type'],
                'back_source' => '---',
                'desk_list' => $disk,
                'net_list' => $network,
                'job_type' => xphp_get_lang($role),
                'job_stage' => $item['current_task_running_stage'] ?? 0,
                'vnc_url' => $newconsoleurl,
                'node' => $item['host_name'] . '(' . $item['ip'] . ')',
                'close_flag' => $closeFlag,
                'module_type_value' => $item['module_type'] ?? 0,
                'job_type_value' => $item['task_type'] ?? 0,
                'job_uuid' => $item['task_uuid'] ?? 0,
                'job_status' => $item['task_status'] ?? 0,
                'dev_type' => $item['dev_type'] ?? 0,
                'prefix_status' =>
                    !($vendor['vendor'] ==   xphp_get_config('app','VENDOR_LIST')['gmp']) || $item['prefix_status'] === $preStatus['STATUS_SUCCESS'],
            ];

            if ($basetype == 0) {
                $base++;
            } else {
                $base--;
            }
        }

        return [
            'rows' => $lists,
            'total' => $total
        ];
    }

    /**
    * 刷新各个节点的主机列表状态
     * @return bool
     */
    private function refreshMachine()
    {
        // 查询所有的节点列表
        $sql = 'select node_uuid,
                      (SELECT GROUP_CONCAT(online_flag) FROM bd_module_server WHERE node_uuid = bn.node_uuid) flag
                from bd_node bn ';
        $node = $this->dbSelect($sql);
        $unset = xphp_get_config('app', 'FLAG')['UNSET'];
        foreach ($node as $item) {
            // 判断下节点是否在线
            $flag = explode(',', $item['flag']);
            if (!in_array($unset, $flag)) {
                // 那么在线
                // 需要刷新状态 进来就刷新
                $msg = [
                    'node_uuid' => $item['node_uuid'],
                    'emd_vm_uuid' => '',
                    'hypervisor_type' => ''
                ];
                $opName = 'TEMP_AGENT_VM_OP_GET_STATUS_ALL_EMD';
                $this->service()->mbTempAgentMsgs($msg, $opName, $msg['node_uuid']);
            }
        }
    }

    /**
     * 获取主机信息
     * @param string $uuid 请求参数
     * @return array
     */
    public function getMachineInfo(string $uuid): array
    {

        $sql = "select ta.*,bt.task_type
                from vm_emd ta left join bd_task bt on bt.task_uuid = ta.task_uuid
                where uuid = ? limit 1";
        $data = $this->dbSelect($sql, [$uuid]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_AGENT_FAIL_TO_OBTAIN_DATA')
            ];
        }
        // 处理数据
        $db = $data[0];
        $config = json_decode($db['config'], true);
        if (empty($config['cdroms'])) {
            // 那么给定个默认的
            $cdroms = [
                'operation' => 1,
                'uuid' => xphp_uuid(),
            ];
        } else {
            $cdroms = $config['cdroms'][0];
            $cdroms['operation'] = 2;
        }
        $communicate = [];
        foreach ($config['interfaces'] as $item) {
            $communicate[] = [
                'network_type' => $item['interface_type'] == 2 ? $item['source_network'] : $item['source_bridge'],
                'uuid' => $item['uuid'],
                'name' => $item['device_name'],
                'gateway' => $item['device_name'],
                'cark_type' => $item['interface_type'],
                'mac' => $item['mac_address'],
                'model_type' => $item['model_type'],
            ];
        }
        $resourceInfo = $this->getResources($db['node_uuid']);
        $info = [
            'vm_uuid' => $db['uuid'],
            'vm_name' => $db['name'],
            'vm_status' => $db['status'],
            'vcpu_num' => $config['num_cpus'],
            'cpu_mode' => $config['cpu_mode'],
            'memory_total' => round($config['memoryMB'] / 1024, 2),
            'boot_firmware' => $config['os']['firmware'],
            'cdroms' => $cdroms,
            'boot_mode' => $config['os']['boot_dev'],
            'iso_path' => $cdroms['source_path'] ?? '',
            'communicate' => $communicate,
            'task_type' => $db['task_type'],
            'node_uuid' => $db['node_uuid']
        ];
        // 这里的可用cpu和核心数都要减少本机使用的
        $info['total_cpu'] = max($resourceInfo['cpus'] - $resourceInfo['used_cpu'], 0) + $config['num_cpus'];
        $info['total_mems'] = max($resourceInfo['mems'] - $resourceInfo['used_memory'], 0) + $config['memoryMB'];

        return [
            'code' => 0,
            'msg' => $info
        ];
    }

    /**
    * 去除多维数组的元素为0或空数组
     * @param array $arr 数组
     * @return void
     */
    private function processArray(array &$arr)
    {
        foreach ($arr as $key => &$value) {
            if (is_array($value) && count(array_filter($value, 'is_array')) !== 0) {
                // 如果当前元素是一维数组，将其置空
                $value = [];
            } elseif (is_array($value)) {
                // 如果当前元素是多维数组或嵌套对象，递归处理
                $this->processArray($value);
            } else {
                // 如果当前元素是对象，将其设置为 0
                if (is_numeric($value)) {
                    $value = 0;
                } elseif (!in_array($key, ['node_uuid', 'vm_uuid'])) {
                    $value = '';
                }
            }
        }
    }

    /**
    * 修改主机信息
     * @param array $params 请求参数
     * @return array
     */
    public function editMachine(array $params): array
    {

        $tempAgent = $params['temp_agent']; // 组装好了的虚拟机信息
        // 这里需要判断下 operation 1add 2edit 3del
        // 获取旧的信息
        $sql = "select * from vm_emd where uuid = ? limit 1";
        $data = $this->dbSelect($sql, [$tempAgent['uuid']]);
        $oldInfo = $data[0];
        $config = json_decode($oldInfo['config'], true);

        // 修改 只传修改的参数内容
        $this->processArray($config);
        // 现阶段只处理 cpu大小和内存大小的修改

        if ($config['num_cpus'] != $tempAgent['config']['num_cpus']) {
            // 那么表示修改
            $config['num_cpus'] = $tempAgent['config']['num_cpus'];
        }
        if ($config['memoryMB'] != $tempAgent['config']['memoryMB']) {
            // 那么表示修改
            $config['memoryMB'] = $tempAgent['config']['memoryMB'];
        }
        if (empty($config['node_uuid'])) {
            $config['node_uuid'] = $oldInfo['node_uuid'];
        }
        if (empty($config['vm_uuid'])) {
            $config['vm_uuid'] = $oldInfo['uuid'];
        }

        // 发送消息给后台
        $opName = 'TEMP_AGENT_OP_EDIT';
        $msg = [
            'emd_vm' => ['config' => $config],
            'node_uuid' => $tempAgent['node_uuid'],
            'vm_uuid' => $tempAgent['uuid'],
        ];

        $return = $this->service()->mbTempAgentMsgs($msg, $opName, $tempAgent['node_uuid']);

        if ($return['result']) {
            //成功
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
     * 修改主机信息 old
     * @param array $params 请求参数
     * @return array
     */
    public function editMachineOld(array $params): array
    {

        $tempAgent = $params['temp_agent']; // 组装好了的虚拟机信息
        // 这里需要判断下 operation 1add 2edit 3del
        // 获取旧的信息
        $sql = "select * from vm_emd where uuid = ? limit 1";
        $data = $this->dbSelect($sql, [$tempAgent['uuid']]);
        $oldInfo = $data[0];
        $config = json_decode($oldInfo['config'], true);

        // 修改 只传修改的参数内容
        // 先处理基本信息
        if ($oldInfo['vm_name'] == $tempAgent['config']['name']) {
            // 那么表示不修改
            $tempAgent['config']['vm_name'] = ''; // 置空
        }
        if ($config['num_cpus'] == $tempAgent['config']['num_cpus']) {
            // 那么表示不修改
            $tempAgent['config']['num_cpus'] = 0; // 置0
        }
        if ($config['memoryMB'] == $tempAgent['config']['memoryMB']) {
            // 那么表示不修改
            $tempAgent['config']['memoryMB'] = 0; // 置0
        }
        $tempAgent['config']['os']['arch_type'] = 0; // 置0
        // 高级配置
        if ($config['os']['firmware'] == $tempAgent['config']['os']['firmware']) {
            // 那么表示不修改
            $tempAgent['config']['os']['firmware'] = 0; // 置0
        }
        if ($config['os']['boot_dev'] == $tempAgent['config']['os']['boot_dev']) {
            // 那么表示不修改
            $tempAgent['config']['os']['boot_dev'] = 0; // 置0
        }
        if (!empty($tempAgent['config']['cdroms'])) {
            if ($tempAgent['config']['cdroms'][0]['source_path'] != $config['cdroms'][0]['source_path']) {
                $tempAgent['config']['cdroms'][0]['operation'] = 2; // 修改
            } else {
                $tempAgent['config']['cdroms'] = [];
            }
        }

        // 然后通信连接处理
        if (
            $this->arrayCompare($config['interfaces'], $tempAgent['config']['interfaces'])
        ) {
            $tempAgent['config']['interfaces'] = [];
        } else {
            // 有变动
            $oldInfaces = []; // 旧的通信连接
            $newInfaces = []; // 新的通信连接
            foreach ($config['interfaces'] as $item) {
                $oldInfaces[$item['uuid']] = $item;
            }
            foreach ($tempAgent['config']['interfaces'] as $item) {
                $newInfaces[$item['uuid']] = $item;
            }

            $realNewInfaces = [];
            foreach ($newInfaces as $key => $item) {
                if (in_array($key, array_column($oldInfaces, 'uuid'))) {
                    // 查看是否有更改
                    if ($item == $oldInfaces[$key]) {
                        // 没变化这项
                        continue;
                    }
                    $item['operation'] = 2; // 是编辑
                    $realNewInfaces[] = $item;
                    continue;
                }
                // 是新增 直接ping上
                $realNewInfaces[] = $item;
            }

            $diffArr = array_column($config['interfaces'], 'uuid');
            if (count($diffArr)) {
                // 表示有删除的
                foreach ($diffArr as $item) {
                    $oldInfaces[$item]['operation'] = 3;
                    $realNewInfaces[] = $oldInfaces[$item];
                }
            }
            $tempAgent['config']['interfaces'] = $realNewInfaces;
        }

        // 发送消息给后台
        $opName = 'TEMP_AGENT_OP_EDIT';
        $msg = [
            'emd_vm' => $tempAgent
        ];

        $return = $this->service()->mbTempAgentMsgs($msg, $opName, $tempAgent['node_uuid']);

        if ($return['result']) {
            //成功
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
    * 删除主机
     * @param array $uuid 主机uuid集合
     * @return array
     */
    public function delMachine(array $uuid)
    {

        // 发送消息给后台
        $opName = 'TEMP_AGENT_OP_DESTROY';
        return $this->operateMachines($opName, $uuid);
    }

    /**
    * 操作主机
     * @param string $opName 操作码
     * @param array  $uuid   虚拟机uuid集合
     * @return array
     */
    public function operateMachines(string $opName, array $uuid): array
    {
        // 先查询信息
        $data = $this->dbSelect(
            "select id,hypervisor_type,node_uuid from vm_emd where uuid = ? limit 1",
            [$uuid[0]]
        );
        if (empty($data)) {
            return [
                'code' => 1,
                'msg'   => xphp_get_lang('WEB_ERROR_VM_MACHINE_NOT_EXIST_ERROR')
            ];
        }

        if ($opName == 'EMD_VM_SYNC_ALL_VM_VNC_TOKEN') {
            $msg = [
                'node_uuid' => (new Node())->getLocalNodeUuid(),
                'emd_vm_uuid' => '',
                'hypervisor_type' => ''
            ];
            // 是打开控制台操作
            $return = $this->service()->mbTempAgentMsgs($msg, $opName, (new Node())->getLocalNodeUuid());
        } else {
            // 发送消息给后台
            $msg = [
                'node_uuid' => $data[0]['node_uuid'],
                'emd_vm_uuid' => $uuid[0],
                'hypervisor_type' => $data[0]['hypervisor_type']
            ];
            $return = $this->service()->mbTempAgentMsgs($msg, $opName, $data[0]['node_uuid']);
        }

        if ($return['result']) {
            //成功
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
    * 获取资源隔离配置
     * @param string $nodeUuid 节点uuid
     * @return array
     */
    public function getResources(string $nodeUuid)
    {

        $resource = $this->getResourcesCM($nodeUuid);
        $resourceUsed = $this->getResourcesCMUsed($nodeUuid);
        $return = [
            'cpu_total' => $resource['cpu'],
            'memory_total' => $resource['mems'],
            'used_cpu' => $resourceUsed['cpu'],
            'used_memory' => $resourceUsed['mems'],
            'cpus' => 0, // 分配给虚拟机的总的cpu大小
            'mems' => 0, // 分配给虚拟机的总的内存大小
            'cpus_virus' => 0, // 分配给病毒沙箱总的cpu大小
            'mems_virus' => 0, // 分配给病毒沙箱总的内存大小
        ];
        $data = $this->dbSelect(
            "select * from bd_emd_resource where node_uuid = ?",
            [$nodeUuid]
        );
        if (!empty($data)) {
            $return['cpus'] = $data[0]['cpus'];
            $return['mems'] = $data[0]['mems'];
            $return['cpus_virus'] = $data[0]['virus_sandbox_cpus'];
            $return['mems_virus'] = $data[0]['virus_sandbox_mems'];
            $return['cpu_mode'] = max($data[0]['cpu_mode'], 0); // 最小是默认的
            $return['virtualization_status'] = v1_parse_flag_to_bool($data[0]['virtualization_status']);
        }
        return $return;
    }

    /**
     * 获取备份系统的资源 总的cpu和总的内存大小
     * @param string $nodeUuid 节点uuid
     * @return array
     */
    public function getResourcesCM(string $nodeUuid): array
    {
        // 判断下节点是否在线，
        $node = new Node();
        $status = $node->getNodeAllStatus($nodeUuid);
        if (!$status['flag']) {
            // 不在线都显示0
            return [
                'cpu' => 0,
                'mems' => 0,
            ];
        }
        $cmd = 'cat /proc/cpuinfo | grep "processor" | wc -l';
        $opName = 'NODE_SYS_OP_DO_CMD_WITH_DETAIL';
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg), true, false);
        if ($mbResult['result'] === false) {
            // 获取失败 手动获取主节点的试试
            exec($cmd, $cpu);
            $cpu = $cpu[0];
        } else {
            $cpu = intval($mbResult['msg']['detail']);
        }

        //获取内存总大小
        $cmd = "cat /proc/meminfo | grep MemTotal | awk '{print $2}'";
        $msg = array('command' => $cmd);
        $mbResult = $this->mbNodeMsg($opName, $nodeUuid, json_encode($msg), true, false);
        if ($mbResult['result'] === false) {
            // 获取失败 收到获取
            exec($cmd, $cmdRAMtotalval);
            $mems = round(intval($cmdRAMtotalval[0] ?? 0) / 1024, 2);
        } else {
            $mems = round($mbResult['msg']['detail'] / 1024, 2);
        }

        return [
            'cpu' => $cpu,
            'mems' => $mems
        ];
    }

    /**
     * 获取虚拟机的资源已经使用的总的cpu和总的内存大小
     * @param string $nodeUuid 节点uuid
     * @return array
     */
    public function getResourcesCMUsed(string $nodeUuid): array
    {
        $sql = "select uuid,config from vm_emd where node_uuid = ?";
        $data = $this->dbSelect($sql, [$nodeUuid]);
        $cpu = $mems = 0;
        $uuidArr = array_column($data, 'uuid');
        if (!empty($data)) {
            foreach ($data as $item) {
                $config = json_decode($item['config'], true);
                $cpu += $config['num_cpus'];
                $mems += $config['memoryMB'];
            }
        }
        // 因为容灾任务不启动不会再vm_emd表中存储，所以还需要再 cdp_vol_task_takeover_info 表的 takeover_vm_config字段里面查询
        // 还需要根据vm_uuid去重
        /*$sql = 'select takeover_vm_config from cdp_vol_task_takeover_info where takeover_vm_node_uuid = ?';
        $list = $this->dbSelect($sql, [$nodeUuid]);
        foreach ($list as $item) {
            if (empty($item['takeover_vm_config'])) {
                continue;
            }
            $config = json_decode($item['takeover_vm_config'], true);
            if (in_array($config['vm_uuid'], $uuidArr)) {
                // 去重
                continue;
            }
            $cpu += $config['cpu_slot_core_num'] * $config['cpu_slot_num'];
            $mems += $config['memoryMB'];
        }*/

        return [
            'cpu' => $cpu,
            'mems' => $mems
        ];
    }

    /**
    * 保存资源隔离配置
     * @param array $params 请求参数
     * @return array
     */
    public function editResources(array $params)
    {

        $opName = 'TEMP_AGENT_OP_ADD_EMBED_RESOURCE_CONFIG';
        $msg = [
            'node_uuid' => $params['node_uuid'],
            'mems' => $params['mems'],
            'cpus' => $params['cpus'],
            'cpu_mode' => intval($params['cpu_mode']),
            'virus_sandbox_cpus' => $params['cpus_virus'], // （病毒沙箱默认cpu核心数量） 最小2核
            'virus_sandbox_mems' => $params['mems_virus'], // （病毒沙箱默认mem数量(MB)） 最小4g
        ];

        $return = $this->service()->mbTempAgentMsgs($msg, $opName, $params['node_uuid']);

        if ($return['result']) {
            //成功
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
     * 获取备份系统节点资源隔离列表
     * @param array $params 请求参数
     * @return array
     */
    public function getNodeSource(array $params): array
    {
        $table = " bd_node bn LEFT JOIN bd_emd_resource evr on bn.node_uuid = evr.node_uuid";
        $field = 'bn.node_id,bn.node_uuid,bn.ip,bn.host_name,evr.cpus,evr.mems,evr.virtualization_status';
        $count = "select count(*) num from " . $table;

        $num = $this->dbSelect($count);
        $total = $num[0]['num'];
        if (empty($total)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $table .= ' LEFT JOIN bd_emd be on bn.node_uuid = be.node_uuid';
        $field .= ',be.use_vt_flag';

        $sql = "select {$field} from " . $table . ' limit ' . $params['offset'] . ',' . $params['limit'];
        $list = $this->dbSelect($sql);
        // 处理数据
        $lists = [];
        foreach ($list as $item) {
            // 计算总共的cpu和物理内存
            $totals = $this->getResourcesCM($item['node_uuid']);

            $lists[] = [
                'num' => $item['node_id'],
                'name' => $item['ip'],
                'status' => $item['virtualization_status'],
                'cpu_used' => $item['cpus'], // cpu已分配
                'cpu_max' => $totals['cpu'], // cpu总共
                'mems_used' => round($item['mems'] / 1024, 2), // 物理内存已分配
                'mems_max' => round($totals['mems'] / 1024, 2), // 物理内存总共
                'use_flag' => $item['use_vt_flag'], // 状态
                'node_uuid' => $item['node_uuid'], // 节点
            ];
        }
        return [
            'rows' => $lists,
            'total' => $total
        ];
    }

    /**
     *  更改资源列表的是否使用VT
     * @param array $params 请求参数
     * @return boolean
     */
    public function setNodeSourceVt(array $params)
    {
        $sql = "update bd_emd set use_vt_flag = ? where node_uuid = ?";
        return $this->dbExec($sql, [ $params['type'], $params['node_uuid']]);
    }

    /**
     * 获取虚拟机和计算资源的一些统计信息
     * @param array $params 请求参数
     * @return array
     */
    public function getStatistInfo(array $params): array
    {

        $vmarr = [0, 0, 0, 0]; // 默认的虚拟机返回
        $cpusarr = $memsarr = [0, 0, 0]; // 默认的计算资源返回
        // 获取虚拟机的信息
        $count = "select count(*) num, status from vm_emd group by status";
        $vm = $this->dbSelect($count);
        if (!empty($vm)) {
            foreach ($vm as $item) {
                if ($item['status'] == 1) {
                    // 运行中
                    $vmarr[1] = $item['num'];
                } elseif ($item['status'] == 0) {
                    // 异常
                    $vmarr[2] = $item['num'];
                } else {
                    $vmarr[3] += $item['num'];
                }
                $vmarr[0] += $item['num'];
            }
        }

        // 获取计算资源的分配
        $count = "select sum(mems) mems, sum(cpus) cpus from bd_emd_resource";
        $res = $this->dbSelect($count);
        if (!empty($res)) {
            $cpusarr[1] = intval($res[0]['cpus']);
            $memsarr[1] = round($res[0]['mems'] / 1024, 2);
        }

        // 计算已用的资源
        $count = "select config from vm_emd";
        $res = $this->dbSelect($count);
        if (!empty($res)) {
            foreach ($res as $item) {
                $config = json_decode($item['config'], true);
                $cpusarr[2] += intval($config['num_cpus']);
                $memsarr[2] += $config['memoryMB'];
            }
            $memsarr[2] = round($memsarr[2] / 1024, 2);
        }

        // 获取总的资源
        $sql = "select node_uuid from bd_node";
        $node = $this->dbSelect($sql);
        foreach ($node as $item) {
            $totals = $this->getResourcesCM($item['node_uuid']);
            $cpusarr[0] += intval($totals['cpu']);
            $memsarr[0] += round($totals['mems'] / 1024, 2);
        }

        $memsarr[0] = round($memsarr[0], 2);
        return [
            'vm' => $vmarr,
            'cpus' => $cpusarr,
            'mems' => $memsarr
        ];
    }

    /**
    * 获取网络列表
     * @param array $params 请求参数
     * @return array
     */
    public function getNetworkLists(array $params): array
    {

        $where = $array = [];
        if (!empty($params['node_uuid'])) {
            // 节点搜索
            $where[] = ' evn.node_uuid = ? ';
            $array = array_merge($array, [$params['node_uuid']]);
        }
        if (!empty($params['card_type'])) {
            // 网络类型搜索
            $where[] = ' evn.forword_mode = ? ';
            $array = array_merge($array, [$params['card_type']]);
        }
        if (!empty($params['bridge_name'])) {
            // 桥接网卡名称搜索
            $where[] = ' evn.bridge_name like ? ';
            $array = array_merge($array, ['%' . $params['bridge_name'] . '%']);
        }
        if (!empty($params['search'])) {
            // 主机名称
            $where[] = ' evn.name like ? ';
            $array = array_merge($array, ['%' . $params['search'] . '%']);
        }

        if (!empty($params['product_network'])) {
            // 因为不能创建隔离网络和隔离桥接网卡 所以只需要判断 name != isolate_network 即可
            if ($params['product_network'] == 1) {
                $where[] = "evn.name != 'isolate_network'";
            } else {
                // 整机验证场景，只能显示隔离网络
                $where[] = "evn.name = 'isolate_network'";
            }
        }

        if (!empty($where)) {
            $wheres = ' where ' . implode(' and ', $where);
        } else {
            $wheres = '';
        }

        $count = "select count(id) as num from bd_emd_network evn {$wheres}";

        $num = $this->dbSelect($count, $array);
        $total = $num[0]['num'];
        if (empty($total)) {
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
                'name' => 'evn.name',
                'type_value' => 'evn.forword_mode',
                'flag_value' => 'evn.flag',
                'node' => 'bn.ip',
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' evn.id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', evn.id ' . $sortType);
            $wheres .= " order by " . $sort;
        }

        $left = " left join bd_node bn on bn.node_uuid = evn.node_uuid";
        $field = 'evn.*,bn.ip,bn.host_name';
        $field .= ', (select GROUP_CONCAT(bnn.ip) from bd_node_network bnn
         where bnn.node_uuid = evn.node_uuid and bnn.network_name = evn.bridge_name) network';
        $sql = "select {$field} from bd_emd_network evn {$left} 
                {$wheres} limit " . $params['offset'] . ',' . $params['limit'];

        $list = $this->dbSelect($sql, $array);

        // 查询出所有在 vm_emd 表里面已经使用了的网络列表
        $netwrorkEmd = $this->getNetworkByNode();

        // 处理数据
        $lists = [];
        $i = $params['offset'];
        $tags = xphp_get_config('tempagent', 'NETWORK_TAGS');
        $bridge = xphp_get_lang('UI_VM_MACHINE_NETWORK_TYPE_BRIDGE');
        $divide = xphp_get_lang('UI_VM_MACHINE_NETWORK_TYPE_DIVIDE');
        foreach ($list as $item) {
            $flag = !empty($netwrorkEmd[$item['node_uuid']])
                && (in_array($item['name'], $netwrorkEmd[$item['node_uuid']])
                || in_array($item['uuid'], $netwrorkEmd[$item['node_uuid']])
                );

            if ($item['name'] == 'isolate_network') {
                // 这个网络也不能删除
                $flag = true;
            }

            $lists[] = [
                'num' => ++$i,
                'uuid' => $item['uuid'],
                'name' => $item['name'],
                'type' => $item['forword_mode'],
                'bridge_name' => !empty($item['bridge_name']) ? $item['bridge_name'] : '',
                'type_value' => $item['forword_mode'] == 3 ? ($bridge . '(' . $item['bridge_name'] . ')') : $divide,
                'node' => $item['host_name'] . '(' . $item['ip'] . ')',
                'flag' => $flag,
                'network' => $item['network'] ?? '--',
                'flag_value' => xphp_get_lang($tags[$item['flag']] ?? 'WEB_PLATFORM_PUBLIC_UNKNOWN')
            ];
        }
        return [
            'rows' => $lists,
            'total' => $total
        ];
    }

    /**
     * 获取每个节点下面的所有的虚拟机使用了的通信连接的集合
     * @return array
     */
    private function getNetworkByNode()
    {
        $list = $this->dbSelect("select node_uuid,config from vm_emd");
        $return = [];
        foreach ($list as $item) {
            if (empty($item['config'])) {
                continue;
            }
            $config = json_decode($item['config'], true);
            $arr = array_column($config['interfaces'], 'source_network');
            $return[$item['node_uuid']] = array_merge($return[$item['node_uuid']] ?? [], $arr);
        }
        // 因为容灾任务不启动不会再vm_emd表中存储，所以还需要再 cdp_vol_task_takeover_info 表的 takeover_vm_config字段里面查询
        $sql = 'select takeover_vm_node_uuid node_uuid,takeover_vm_config from cdp_vol_task_takeover_info ';
        $list = $this->dbSelect($sql);
        foreach ($list as $item) {
            if (empty($item['takeover_vm_config'])) {
                continue;
            }
            $config = json_decode($item['takeover_vm_config'], true);
            $arr = array_column($config['interfaces'], 'source_network');
            $return[$item['node_uuid']] = array_merge($return[$item['node_uuid']] ?? [], $arr);
        }
        // 需要兼容查询虚拟演练室的情况
        $sql = "select node_uuid,proxy_network_uuid from sr_virtual_lab where proxy_network_uuid != ''";
        $list = $this->dbSelect($sql);
        foreach ($list as $item) {
            $return[$item['node_uuid']] = array_merge($return[$item['node_uuid']] ?? [], [$item['proxy_network_uuid']]);
        }
        return $return;
    }

    /**
     * 获取网络信息
     * @param string $uuid 请求参数
     * @return array
     */
    public function getNetworkInfo(string $uuid): array
    {

        $sql = "select * from bd_emd_network where uuid = ? limit 1";
        $data = $this->dbSelect($sql, [$uuid]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PUBLIC_FAILURE')
            ];
        }
        // 处理数据
        $info = [
            'network_uuid' => $data[0]['uuid'],
            'name' => $data[0]['name'],
            'node_uuid' => $data[0]['node_uuid'],
            'forword_mode' => $data[0]['forword_mode'],
            'bridge_name' => $data[0]['bridge_name'],
        ];

        return [
            'code' => 0,
            'msg' => $info
        ];
    }

    /**
     *  获取虚拟网络添加时的所有桥接网卡列表
     * @param array $params 参数
     * @return array
     */
    public function getNetCard(array $params)
    {
        $nodeUuid = $params['node_uuid'];
        $returnsArr = $this->service()->mbTempAgentMsgs(
            [],
            'TEMP_AGENT_OP_GET_ALL_NET_DEVICE_INFO',
            $nodeUuid,
            true
        );

        if ($returnsArr['result'] && !empty($returnsArr['msg']['detail'])) {
            // 获取所有已经使用了的桥接网卡列表
            $bridgename = $this->dbSelect("select bridge_name from bd_emd_network where node_uuid = ?", [$nodeUuid]);
            if (!empty($bridgename)) {
                $bridgenameArr = array_filter(array_column($bridgename, 'bridge_name'));
            } else {
                $bridgenameArr = [];
            }
            $returns = json_decode($returnsArr['msg']['detail'], true);
            $nicType = xphp_get_config('tempagent', 'DEVICE_TYPE_NIC');

            $type = empty($params['type']) ? 'NIC_DEVICE_TYPE_BRIDGE' : $params['type'];
            $typeArr = explode(',', $type);
            $typeArrs = [];
            foreach ($typeArr as $item) {
                $typeArrs[] = $nicType[$item];
            }

            // 查询出集群已经配置了的网卡，这些网卡也不能进行配置
            $cluser = $this->dbSelect(
                'select bcnn.nic_name from bd_cluster_node_network bcnn,bd_cluster bc
                        where bcnn.cluster_uuid = bc.cluster_uuid and bc.cluster_status = 2 and bcnn.node_uuid = ?',
                [$nodeUuid]
            );
            $cluserArr = [];
            if (!empty($cluser)) {
                $cluserArr = array_column($cluser, 'nic_name');
            }

            $return = [];
            foreach ($returns as $items) {
                if (
                    !in_array($items['type'], $typeArrs) || in_array($items['name'], $bridgenameArr)
                    || in_array($items['name'], $cluserArr)
                ) {
                    // 默认只显示桥接网卡，如果有传递，那么以新的为准
                    continue;
                }

                $return[] = [
                    'name' => $items['name'],
                    'value' => $items['name'],
                ];
            }
            return [
                'total' => count($return),
                'rows' => $return
            ];
        }
        return [
            'total' => 0,
            'rows' => []
        ];
    }

    /**
     * 保存网络配置
     * @param array $params 请求参数
     * @return array
     */
    public function editNetworks(array $params)
    {

        // 需要先校验名称是否重复 可以重复
        if (!$this->checkNameAvailable($params['name'], $params['node_uuid'], $params['network_uuid'] ?? '')) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_VM_MACHINE_NETWORK_EXISTS')
            ];
        }

        $opName = 'TEMP_AGENT_OP_ADD_EMBED_NETWORK_CONFIG';
        // 修改的流程是先删除，然后在添加
        if (!empty($params['network_uuid'])) {
            // 删除
            $this->delNetworks([$params['network_uuid']]);
        }
        $msg = [
            'node_uuid' => $params['node_uuid'],
            'uuid' => xphp_uuid(),
            'name' => $params['name'],
            'forword_mode' => $params['forword_mode'],
            'bridge_name' => empty($params['bridge_name']) ? '' : $params['bridge_name'],
            'flag' => 2
        ];
        $return = $this->service()->mbTempAgentMsgs($msg, $opName, $params['node_uuid']);

        if ($return['result']) {
            //成功
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
    * 校验是否添加了重复的桥接网卡
     * @param string $name     网卡名称
     * @param string $nodeUuid 节点uuid
     * @param string $uuid     网络uuid
     * @return boolean
     */
    private function checkCard(string $name, string $nodeUuid, $uuid = '')
    {
        $sql = "select bridge_name from bd_emd_network where forword_mode = 3 and node_uuid = ?";
        empty($uuid) ? '' : $sql .= " and uuid != '{$uuid}'";
        $data =  $this->dbSelect($sql, [$nodeUuid]);
        if (!empty($data)) {
            $nameArr = array_column($data, 'bridge_name');
            if (in_array($name, $nameArr)) {
                return false;
            }
        }
        return true;
    }

    /**
    * 校验网络名称是否重复
     * @param string $name     名称
     * @param string $nodeUuid 节点uuid
     * @param string $uuid     uuid
     * @return boolean
     */
    private function checkNameAvailable(string $name, string $nodeUuid, $uuid = '')
    {
        $where = 'name = ? and node_uuid = ? ';
        if (!empty($uuid)) {
            $where .= " and uuid != '{$uuid}' ";
        }
        $sql = "select uuid from bd_emd_network where {$where}";
        $data = $this->dbSelect($sql, [$name, $nodeUuid]);

        return empty($data);
    }

    /**
     * 删除网络
     * @param array $params 请求参数
     * @return array
     */
    public function delNetworks(array $params)
    {
        $sql = "select * from bd_emd_network where uuid = ? limit 1";
        $data = $this->dbSelect($sql, [$params[0]]);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_AGENT_FAIL_TO_OBTAIN_DATA')
            ];
        }
        $opName = 'TEMP_AGENT_OP_DEL_EMBED_NETWORK_CONFIG';
        $msg = [
            'node_uuid' => $data[0]['node_uuid'],
            'uuid' => $params[0],
            'name' => '',
            'forword_mode' => '',
            'bridge_name' => '',
            'flag' => 0
        ];
        $return = $this->service()->mbTempAgentMsgs($msg, $opName, $data[0]['node_uuid']);

        if ($return['result']) {
            //成功
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        } else {
            $volcdpOpcode = new VolcdpOpcode();
            $operate = $volcdpOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
    * 获取日志
     * @param array $params 请求参数
     * @return array
     */
    public function getLogs($params = []): array
    {
        // 任务名称 操作来源  操作名  操作对象  操作结果  详细信息  开始时间 结束时间  所属节点
        $where = $param = [];
        if (!empty($params['search'])) {
            // 关键词搜索
            $where[] = 'item_name like ? ';
            $param[] = "%{$params['search']}%";
        }

        if (!empty($params['node_uuid'])) {
            // 所属节点
            $where[] = 'node_uuid = ? ';
            $param[] = $params['node_uuid'];
        }

        if (!empty($params['job_name'])) {
            // 任务名
            $where[] = 'task_name like ? ';
            $param[] = "%{$params['job_name']}%";
        }

        if (!empty($params['status'])) {
            // 状态
            if ($params['status'] == 1) {
                // 正常的
                $where[] = 'operation_result = 0';
            } else {
                $where[] = 'operation_result != 0';
            }
        }

        if (!empty($params['item_name'])) {
            // 关键词搜索
            $where[] = 'item_name like ? ';
            $param[] = "%{$params['item_name']}%";
        }

        if (!empty($params['start_time'])) {
            // 开始时间
            $where[] = 'UNIX_TIMESTAMP(start_time) <= ?';
            $param[] = strtotime($params['start_time']);
        }

        if (!empty($params['end_time'])) {
            // 开始时间
            $where[] = 'UNIX_TIMESTAMP(end_time) >= ?';
            $param[] = strtotime($params['end_time']);
        }

        if (!empty($where)) {
            $wheres = ' where ' . implode(' and ', $where);
        } else {
            $wheres = '';
        }

        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看分配的存储列表
            // 不是超级管理员也不是全局观察者，也只能看到分配的资源
            $userUuidSql = v1_auth_get_users(
                xphp_get_config('user', 'USER_AUTH')['current_job']
            );
            $sqlNew = "item_uuid in (
                select uuid
                    from vm_emd
                     where task_uuid in 
                        (select task_uuid from bd_task where user_uuid in ({$userUuidSql}))
                )";
            $wheres .= (empty($wheres) ? ' where ' : ' and ') . $sqlNew;
        }

        $sql = 'select count(*) num from bd_emd_log ' . $wheres;

        $total = $this->dbSelect($sql, $param);
        if (empty($total)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $num = $total[0]['num'];

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
                'job_name' => 'task_name',
                'item_name' => 'item_name',
                'result' => 'operation_result',
                'start_time' => 'start_time',
                'end_time' => 'end_time'
            ];

            $sort = empty($sortArr[$params['sort']])
                ? ' log_id desc' : ($sortArr[$params['sort']] . ' ' . $sortType . ', log_id ' . $sortType);
            $wheres .= " order by " . $sort;
        }

        $sql = 'select * from bd_emd_log ' . $wheres . ' limit ' . $params['offset'] . ',' . $params['limit'];
        $data = $this->dbSelect($sql, $param);
        $return = [];
        $emdOperationUser = xphp_get_config('tempagent', 'EmdOperationUser');
        $emdOperation = xphp_get_config('tempagent', 'EmdOperation');
        $space = xphp_get_config('app', 'NULLSPACE');
        foreach ($data as $item) {
            $return[] = [
                'uuid' => $item['log_id'],
                'job_name' => !empty($item['task_name']) ? $item['task_name'] : $space,
                'source' => xphp_get_lang($emdOperationUser[$item['user_flag']]),
                'source_name' => xphp_get_lang($emdOperation[$item['operation_type']]),
                'item_name' => !empty($item['item_name']) ? $item['item_name'] : $space,
                'result' => $item['operation_result'],
                'description' => !empty($item['description']) ? $item['description'] : $space,
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time'],
            ];
        }
        return [
            'rows' => $return,
            'total' => $num
        ];
    }

    /**
     * 删除日志
     * @return array
     */
    public function delLogs()
    {
        return [
            'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS'),
            'code' => 0
        ];
    }

    /**
    * 获取备份系统iso/光盘列表
     * @param array $params 请求参数
     * @return array
     */
    public function getDiskLists(array $params): array
    {
        // 假设base根目录是 项目根目录下面的 livecd 路径
        $base = dirname(__DIR__, 7) . '/livecd';
        $list = xphp_get_files($base, [], 'iso');
        $lists = [];
        foreach ($list as $item) {
            $items = explode('/', $item);
            $lists[] = [
                'id' => $item,
                'name' => end($items),
                'path' => $item,
            ];
        }
        return [
            'rows' => $lists,
            'total' => count($lists),
        ];
    }

    /**
     * 获取新的uuid地址集合
     * @param array $params 请求参数
     * @return array
     */
    public function getUuid(array $params): array
    {
        $num = $params['num'] ?? 1;
        $arr = [];
        for ($i = 0; $i < $num; $i++) {
            $arr[] = xphp_uuid();
        }
        //成功
        return [
            'value' => $arr
        ];
    }

    /**
     * 获取创建虚拟机默认的一些信息
     * @param array $params 请求参数
     * @return array
     */
    public function getDefault(array $params): array
    {

        // 获取 虚拟机名称（name），cpu，内存（mems），网卡名称（card）
        $name = xphp_get_lang('UI_VM_MACHINE_DEFAULT_NAME');
        $card = 'network_card';
        // 这里判断下任务表里面是否存在同名的任务，存在就在编号前加上1
        $sqlParams = [$name . '%'];
        $sql = "select `name` from vm_emd where `name` like ?";
        $data = $this->dbSelect($sql, $sqlParams);

        if (empty($data) && empty($data1)) {
            $name .= '1';
        } else {
            // 取出虚拟机名后面的编号并降序
            $array = str_replace($name, '', array_column($data, 'name'));
            $name = $name . (intval(max($array)) + 1);
        }

        //成功
        return [
            'name' => $name,
            'cpu' => 2,
            'mems' => 2,
            'card' => $card
        ];
    }

    /**
     * 代理网关列表
     * @param array $params 请求参数
     * @return array
     */
    public function getProxyList(array $params): array
    {

        // sr_virtual_lab 表里面的 proxy_uuid 就是 vm_emd 表的uuid
        // join查询，
        $table = ' sr_virtual_lab svl inner join vm_emd ta on svl.proxy_uuid = ta.uuid ';
        $where = '';
        if (!empty($params['search'])) {
            $search = trim($params['search']);
            $where = " where (svl.proxy_name like '%{$search}%' or svl.proxy_ip like '%{$search}%') ";
        }
        $count = "select count(*) num from " . $table . $where;

        $num = $this->dbSelect($count);
        $total = $num[0]['num'];
        if (empty($total)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $field = ' svl.proxy_name,svl.proxy_ip,svl.virtual_lab_name,ta.name,ta.config,ta.node_uuid,ta.uuid,
        ta.os_type,ta.status,ta.console_url,ta.prefix_status,
        bn.ip,bn.host_name,bt.task_name,bt.task_status,bt.task_uuid';
        $left = ' LEFT JOIN bd_node bn ON bn.node_uuid = ta.node_uuid
                LEFT JOIN sr_sure_backup ssb ON ssb.virtual_lab_uuid = svl.virtual_lab_uuid 
                LEFT JOIN bd_task bt on ssb.task_uuid = bt.task_uuid ';

        $sql = "select {$field} from " . $table . $left . $where
            . ' GROUP BY ta.uuid limit ' . $params['offset'] . ',' . $params['limit'];

        $list = $this->dbSelect($sql);
        // 处理数据
        $lists = [];
        $cors = xphp_get_lang('UI_VM_MACHINE_CORS');
        $statusMsg = xphp_get_config('tempagent', 'STATUS_MSG');
        $preStatus = xphp_get_config('tempagent', 'PREFIX_STATUS');
        foreach ($list as $item) {
            $config = json_decode($item['config'], true);
            // 把 0.0.0.0 换成当前的域名
            $newconsoleurl = str_replace('0.0.0.0:6080', $_SERVER['HTTP_HOST'] . '/web_console', $item['console_url']);
            $newconsoleurl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($newconsoleurl);
            $lists[] = [
                'uuid' => $item['uuid'],
                'name' => $item['proxy_name'] . '(' . $item['proxy_ip'] . ')',
                'vm_name' => $item['name'],
                'vcpu_num' => $config['num_cpus'] . $cors,
                'memory_total' => $config['memoryMB'] / 1024 . 'GB',
                'os_type' => $config['os']['os_type'] ?: $item['os_type'],
                'status' => $item['status'],
                'status_value' => xphp_get_lang($statusMsg[$item['status']]) ?? '---',
                'source' => $item['virtual_lab_name'],
                'node' => $item['host_name'] . '(' . $item['ip'] . ')',
                'vnc_url' => $newconsoleurl,
                'job_name' => $item['task_name'] ?? '---',
                'job_uuid' => $item['task_uuid'] ?? 0,
                'job_status' => $item['task_status'] ?? 0,
                'prefix_status' => $item['prefix_status'] === $preStatus['STATUS_SUCCESS'],
            ];
        }
        return [
            'rows' => $lists,
            'total' => $total
        ];
    }

    /**
    * 比对二维数组是否一致
     * @param array $array1 数组1
     * @param array $array2 数组2
     * @return bool
     */
    private function arrayCompare(array $array1, array $array2): bool
    {
        if (count($array1) !== count($array2)) {
            return false;
        }

        foreach ($array1 as $key => $value) {
            if (!array_key_exists($key, $array2)) {
                return false;
            }

            if (is_array($value) && is_array($array2[$key])) {
                if (!$this->arrayCompare($value, $array2[$key])) {
                    return false;
                }
            } elseif ($value !== $array2[$key]) {
                return false;
            }
        }

        return true;
    }
}
