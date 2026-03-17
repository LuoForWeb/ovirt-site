<?php

namespace app\v1\system\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\OrchestrationOpcode;
use app\v1\resources\v0\logic\Node;

/**
 * note          系统安全配置 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/12/19 16:17
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Safe extends Base
{
    /**
     * 获取日志安全配置
     * @return array
     */
    public function getDatas(): array
    {
        return $this->getDataSafe(true, true);
    }

    /**
     * 保存日志安全配置
     * @param array $params 参数数组
     * @return bool
     */
    public function setDatas(array $params): bool
    {
        // 先进行后台发送
        $opName = 'SS_OP_UPDATE_SYSTEM_RESERVED_STRATEGY';
        $msg = ['system_reserved_strategy' => $params['back_strategy']];
        $return = $this->service()->mbStrategyMsgs($opName, $msg);
        //$return = ['result' => true]; // 先不走后台，纯web逻辑
        if ($return['result']) {
            // 处理下
            $web = [];
            foreach ($params['web_strategy'] as $key => $item) {
                $keys = $this->getKeyByNum($key, 1);
                $web[$keys] = $item;
            }
            //成功
            $content = $this->getDataSafe(false);
            $user = xphp_get_user_info();
            $model = xphp_three_powers() ? 'three' : 'normal';
            if (empty($content)) {
                // 表示是插入
                $content = [$model => $web];

                $sql = "INSERT INTO `bd_system_settings`
                    (`settings_type`, `settings_content`, `modify_time`, `user_uuid`)
                    VALUES (?, ?, ?, ?)";
                $sqlParam = [
                    xphp_get_config('app', 'SETTINGS_CONF')['SYSTEM_CONFIG_RECORDS'],
                    json_encode($content),
                    $this->parseDate(time()),
                    $user['userUuid']
                ];
            } else {
                // 更新
                $content[$model] = $web;
                $sql = "update `bd_system_settings` set settings_content = ?,modify_time = ? where settings_type = ?";
                $sqlParam = [
                    json_encode($content),
                    $this->parseDate(time()),
                    xphp_get_config('app', 'SETTINGS_CONF')['SYSTEM_CONFIG_RECORDS'],
                ];
            }
            return $this->dbExec($sql, $sqlParam);
        } else {
            $nodeOpcode = new OrchestrationOpcode();
            $operate = $nodeOpcode->getOpcodeDes($opName);
            return $this->muOpResult(false, $operate, '', '', $return['errorCode']);
        }
    }

    /**
    * 内置方法 获取日志安全配置
     * @param bool $flag 是否获取最终的配置还是记录配置
     * @param bool $back 是否获取后台的保留策略
     * @return array
     */
    private function getDataSafe(bool $flag = true, $back = false): array
    {
        // 获取 bd_system_settings 表里面 settings_type 为 SYSTEM_CONFIG_RECORDS（12） 的配置内容
        $sql = "select settings_content from bd_system_settings where settings_type = ?";

        $data = $this->dbSelect($sql, [xphp_get_config('app', 'SETTINGS_CONF')['SYSTEM_CONFIG_RECORDS']]);

        if (!empty($data)) {
            // 处理下数据格式，因为存储的是json数组
            $content = json_decode($data[0]['settings_content'], true);
            if ($flag) {
                // 预留三权的不同的配置权限
                $model = xphp_three_powers() ? 'three' : 'normal';
                $content = !empty($content[$model]) ? $content[$model] : [];
            } elseif (empty($content['three']) && empty($content['normal'])) {
                // 兼容之前版本的，直接覆盖配置
                $content = [];
            }
        }

        if ($back) {
            $config = xphp_get_config('system', 'SYSTEM_CONFIG_ITEM');
            // 读取清理策略配置
            $sql = "select * from bd_system_reserved_strategy
                        where system_configuration_item in 
                   ( {$config['ITEM_HISTORICAL_TASK']}, {$config['ITEM_TASK_LOG']}, {$config['ITEM_SYSTEM_LOG']}
            ,{$config['ITEM_CLUSTER_HA_LOG']}, {$config['ITEM_TASK_ALARM']}, {$config['ITEM_SYSTEM_ALARM']})
                         group by system_configuration_item order by strategy_id desc";
            $data = $this->dbSelect($sql);
            if (empty($data)) {
                return [$content ?? [], []];
            }

            $items = [];
            foreach ($data as $item) {
                $keys = $this->getKeyByNum($item['system_configuration_item']);
                $items[$keys] = [
                    'system_configuration_item' => $item['system_configuration_item'],
                    'strategy_type' => $item['strategy_type'],
                    'strategy_status' => $item['strategy_status'],
                    'number' => $item['number'],
                ];
            }
            $content = $content ?? [];
            $web = [];
            foreach ($content as $key => $items2) {
                $keys = $this->getKeyByNum($key);
                if (empty($keys)) {
                    continue;
                }
                $web[$keys] = $items2;
            }
            return ['web' => $web, 'back' => $items];
        }
        return $content ?? [];
    }

    /**
    * 根据定义的日志枚举获取真实的key
     * @param $num  枚举值
     * @param $type 类型。默认是根据枚举获取对应的标识，传非0表示反转
     * @return string
     */
    private function getKeyByNum($num, $type = 0)
    {
        $arr = [
            1 => 'history_task', // 历史任务
            2 => 'task', // 当前任务
            3 => 'system', // 系统日志
            4 => 'cluster', // 集群
            5 => 'task_alarm', // 任务告警
            6 => 'system_alarm', // 系统告警
        ];
        if ($type != 0) {
            // 反转
            $arr = array_flip($arr);
        }

        return $arr[$num] ?? '';
    }

    /**
     * 获取账户安全配置
     * @return array
     */
    public function getAccounts(): array
    {
        $sql = "select login_timeout, login_failure, pass_timeout, pass_length,
                        pass_complexity,login_failed_lock_time,create_user_level
                    from bd_account_safe";
        $where = ' where create_user_level = ?';
        $user = xphp_get_user_info();
        $userLevel = 0;
        if ($user['isThreePowers']) {
            // 是三权模式 那么就必须按照当前用户级别来读取配置
            $userLevel = $user['userLevel'] ?? 0;
        }
        $data = $this->dbSelect($sql . $where, [$userLevel]);

        $info = [
            'passcomplexity' => 2,
            'is_three_powers' => $user['isThreePowers'],
            'user_level' => $user['userLevel'],
        ];
        if (!empty($data)) {
            if ($user['isThreePowers']) {
                $info = [
                    'out_time' => min($data[0]['login_timeout'], 600),
                    'faild_count' => min($data[0]['login_failure'], 5),
                    'password_time' => min($data[0]['pass_timeout'], 7),
                    'passlength' => max($data[0]['pass_length'], 8),
                    'passcomplexity' => max($data[0]['pass_complexity'], 2),
                    'faild_lock_time' => max($data[0]['login_failed_lock_time'], 1800),
                ];
            } else {
                $info = [
                    'out_time' => $data[0]['login_timeout'],
                    'faild_count' => $data[0]['login_failure'],
                    'password_time' => $data[0]['pass_timeout'],
                    'passlength' => $data[0]['pass_length'],
                    'passcomplexity' => $data[0]['pass_complexity'],
                    'faild_lock_time' => $data[0]['login_failed_lock_time'],
                ];
            }
            $info['is_three_powers'] = $user['isThreePowers'];
            $info['user_level'] = $user['userLevel'];
        }
        return $info;
    }

    /**
    * 保存账户安全配置
     * @param array $params 请求参数
     * @return array
     */
    public function setAccounts($params = []): array
    {

        $user = xphp_get_user_info();
        $createuserlevel = $user['isThreePowers'] ? $user['userLevel'] : 0;

        $sql = "select id from bd_account_safe where create_user_level = ?";
        $data = $this->dbSelect($sql, array($createuserlevel));
        $sqlParams = [
            $params['out_time'],
            $params['faild_count'],
            $params['password_time'],
            $params['passlength'],
            $params['passcomplexity'],
            $params['faild_lock_time'],
            $createuserlevel
        ];
        if (!empty($data)) {
            // 存在，那么就修改
            $sql = "update bd_account_safe set 
                           login_timeout =?, login_failure = ?, pass_timeout = ?, pass_length = ?,
                           pass_complexity = ?, login_failed_lock_time = ?, create_user_level = ?
                        where create_user_level = ?";
            $sqlParams = array_merge($sqlParams, [$createuserlevel]);
        } else {
            // 新增
            $sql = "insert into bd_account_safe
    (login_timeout,login_failure,pass_timeout,pass_length,pass_complexity,login_failed_lock_time,create_user_level) 
                    values (?,?,?,?,?,?,?);";
        }
        $result = $this->dbExec($sql, $sqlParams);
        $this->unifyWriteSystemLog($result, "SYSTEM_LOG_SETTINGS_ACCOUNT_SAFE");
        return [
            'code' => 0,
            'msg' => xphp_get_lang('UI_USER_CONFIG_SAFE_INFO')
        ];
    }

    /**
     * 获取勒索防护安全配置
     * @return array
     */
    public function getStorages(): array
    {
        $sql = "select data_protect_flag from bd_system";
        $data = $this->dbSelect($sql);

        return [
            'protect_flag' => $data[0]['data_protect_flag'] == xphp_get_config('app', 'FLAG')['SET']
        ];
    }

    /**
     * 保存存勒索防护安全配置
     * @param array $params 请求参数
     * @return array
     */
    public function setStorages($params = []): array
    {

        $flag = xphp_get_config('app', 'FLAG');
        $protectFlag = $params['protect_flag'] ? $flag['SET'] : $flag['UNSET'];

        // 改用后台交互
        $opName = 'NODE_SYS_OP_OPERTER_RANSOM_PROTECT';
        $msg = [
            'ransom_protect_enable_flag' => $protectFlag
        ];

        // 记录下开启成功的节点
        $nodesArr = [];
        // 给所有在线的节点发送一个消息
        $nodes = $this->dbSelect('select node_uuid,ip from bd_node');
        $nodec = new Node();
        foreach ($nodes as $node) {
            $status = $nodec->getNodeStatus($node['node_uuid']);
            if (!empty($status['online_flag'])) {
                $mbResult = $this->service()->mbNodeMsgs($opName, $node['node_uuid'], $msg, true, false);
                if (empty($mbResult['result'])) {
                    if ($params['protect_flag']) {
                        // 表示开启操作
                        // 那么需要回滚开启成功的节点列表
                        $msg = [
                            'ransom_protect_enable_flag' => $flag['UNSET']
                        ];
                        foreach ($nodesArr as $item) {
                            $mbResults = $this->service()->mbNodeMsgs($opName, $item, $msg, true, false);
                        }
                    }
                    $operate = xphp_get_lang('UI_PUBLIC_OPERATION');
                    return $this->muOpResult(
                        $mbResult['result'],
                        $operate,
                        $mbResult['msg'] . xphp_get_lang('UI_SETTINGS_STORAGE_SFAE_SET_FAIL') . $node['ip'],
                        '',
                        $mbResult['errorCode']
                    );
                }
                $nodesArr[] = $node['node_uuid'];
            }
        }

        $result = $mbResult['result'];

        if ($protectFlag == $flag['SET']) {
            $sysLogKey = 'SYSTEM_LOG_SETTINGS_STORAGE_SAFE_ON';
        } else {
            $sysLogKey = 'SYSTEM_LOG_SETTINGS_STORAGE_SAFE_OFF';
        }

        $this->unifyWriteSystemLog($result, $sysLogKey);
        if ($result) {
            return [
                'code' => 0,
                'msg' => xphp_get_lang('WEB_PUBLIC_SUCCESS')
            ];
        }
        $operate = xphp_get_lang('UI_PUBLIC_OPERATION');
        return $this->muOpResult($result, $operate, $mbResult['msg'], '', $mbResult['errorCode']);
    }

    /**
     * 获取系统安全配置
     * @return array
     */
    public function getOsSafe(): array
    {
        //以主节点状态展示
        $masterUUID = (new Node())->getLocalNodeUUID();
        $tool = new Tool();
        // 防火墙
        $firewalldMsg = $tool->getServiceStatus('firewalld', $masterUUID);
        $firewallcheck = $firewalldMsg === 0;

        // sshd
        $sshdMsg = $tool->getServiceStatus('sshd', $masterUUID);
        $sshcheck = $sshdMsg === 0;

        // 获取8080和3306以及nfs和rpcbind服务
        $port8080dMsg = $tool->getServiceStatus(8080, $masterUUID);
        $port8080check = $port8080dMsg === 0;

        $port3306dMsg = $tool->getServiceStatus(3306, $masterUUID);
        $port3306check = $port3306dMsg === 0;

        $port5000dMsg = $tool->getServiceStatus(5000, $masterUUID);
        $port5000check = $port5000dMsg === 0;

        $port6080dMsg = $tool->getServiceStatus(6080, $masterUUID);
        $port6080check = $port6080dMsg === 0;

        $nfsdMsg = $tool->getServiceStatus('nfs-server', $masterUUID);
        $rpcbinddMsg = $tool->getServiceStatus('rpcbind', $masterUUID);
        $nfscheck = $nfsdMsg === 0;
        $rpcbindcheck = $rpcbinddMsg === 0;

        // 查询api安全开关
        $apiSafe = $this->dbSelect(
            'select settings_content from bd_system_settings where settings_type = ? order by settings_id desc limit 1',
            [xphp_get_config('app', 'SETTINGS_CONF')['API_SAFE_CONFIG'] ?? 31]
        );
        $apiFlag = true; // 默认开启
        if (!empty($apiSafe[0]['settings_content'])) {
            $apiSafe = json_decode($apiSafe[0]['settings_content'], true);
            $apiFlag = !empty($apiSafe['flag']);
        }

        $temp = 3; // 默认是自定义的方式
        // 顺便在后台判断下选择的是哪种模板
        if ($firewallcheck) {
            if (
                $sshcheck && $port8080check && $port3306check && $port5000check && $port6080check
                && $nfscheck && $rpcbindcheck && $apiFlag
            ) {
                // 默认的模板
                $temp = 1;
            }
            if (
                !$sshcheck && !$port8080check && !$port3306check && !$port5000check && !$port6080check
                && !$nfscheck && !$rpcbindcheck && $apiFlag
            ) {
                // 安全的模板
                $temp = 2;
            }
        }

        return [
            'firewall_flag' => $firewallcheck,
            'ssh_flag' => $sshcheck,
            'port8080_flag' => $port8080check,
            'port3306_flag' => $port3306check,
            'port5000_flag' => $port5000check,
            'port6080_flag' => $port6080check,
            'nfs_flag' => $nfscheck,
            'rpcbind_flag' => $rpcbindcheck,
            'api_flag' => $apiFlag,
            'temp' => $temp,
        ];
    }

    /**
     * 保存系统安全配置
     * @param array $params 请求参数
     * @return array
     */
    public function setOsSafe($params = []): array
    {

        //组合后台命令
        $cmd = $this->groupServiceCtlCMD('firewalld', boolval($params['firewall_flag']));
        $cmd .= $this->groupServiceCtlCMD('sshd', boolval($params['ssh_flag']));
        $cmd .= $this->groupServiceCtlCMD(8080, boolval($params['port8080_flag']));
        $cmd .= $this->groupServiceCtlCMD(3306, boolval($params['port3306_flag']));
        $cmd .= $this->groupServiceCtlCMD(5000, boolval($params['port5000_flag']));
        $cmd .= $this->groupServiceCtlCMD(6080, boolval($params['port6080_flag']));
        $cmd .= $this->groupServiceCtlCMD('rpcbind', boolval($params['rpcbind_flag']));
        $cmd .= $this->groupServiceCtlCMD('nfs-server', boolval($params['nfs_flag']));

        $cmd .= 'firewall-cmd --reload --quiet;'; // 因为更改了端口，所以最后拼上重启防火墙命令

        //找到所有的节点,挨个发送命令
        $sql = "select bn.node_uuid from bd_node bn, bd_module_server bms where 
                bn.node_uuid = bms.node_uuid and bms.module_type = ? and bms.online_flag = ?";
        $data = $this->dbSelect(
            $sql,
            [xphp_get_config('module', 'MODULE_TYPE')['NODE'], xphp_get_config('app', 'FLAG')['SET']]
        );
        $result = true;
        foreach ($data as $d) {
            $opName = 'NODE_SYS_OP_DO_CMD';
            $msg = array('command' => $cmd);
            $mbResult = $this->service()->mbNodeMsgs($opName, $d['node_uuid'], $msg, true, false);
            $result = $result && $mbResult['result'];
        }

        // 保存api安全配置
        $apiFlag = boolval($params['api_flag']);
        $apiValue = xphp_get_config('app', 'SETTINGS_CONF')['API_SAFE_CONFIG'] ?? 31;
        $apiSafe = $this->dbSelect(
            'select settings_content from bd_system_settings where settings_type = ? order by settings_id desc limit 1',
            [$apiValue]
        );
        if (empty($apiSafe)) {
            $sql = "insert into `bd_system_settings`
                        (settings_content ,modify_time, settings_type, user_uuid) values (?, ?, ?, ?)";
            $sqlParam = [
                json_encode(['flag' => $apiFlag]),
                $this->parseDate(time()),
                $apiValue,
                xphp_get_user_info()['userUuid']
            ];
        } else {
            $sql = 'update `bd_system_settings` set settings_content = ?,modify_time = ? where settings_type = ?';
            $sqlParam = [
                json_encode(['flag' => $apiFlag]),
                $this->parseDate(time()),
                $apiValue
            ];
        }

        $this->dbExec($sql, $sqlParam);

        //写系统日志
        $this->unifyWriteSystemLog($result, 'SYSTEM_LOG_SETTINGS_OS_SAFE');

        return [
            'code' => 0,
            'msg' => xphp_get_lang('UI_PLATFORM_SET_SYSTEM_SAFE')
        ];
    }

    /**
     * 关机/重启
     * @param array $params 参数
     * @return array
     */
    public function powerSubmit($params = []): array
    {
        $nodeuuid = $params['node_uuid'];
        if (empty($params['password'])) {
            // 检查是否存在任务运行
            $sql = "select task_uuid, task_name from bd_task where task_status = ? and node_uuid = ?";
            $data = $this->dbSelect($sql, array(xphp_get_config('task', 'TASKSTATUS')['RUNNING'], $nodeuuid));
            if (!empty($data)) {
                foreach ($data as $d) {
                    $tasks[] = array(
                        'job_uuid' => $d['task_uuid'],
                        'job_name' => $d['task_name']
                    );
                }
            }
            return [
                'code' => 0,
                'msg' => '',
                'data' => [
                    'total' => count($data),
                    'rows' => $tasks ?? []
                ]
            ];
        }
        // 进行具体的操作
        $user = xphp_get_user_info();
        $useruuid = $user['userUuid'];
        $powertype = $params['power_type'];

        $sql = "select password from bd_user where user_uuid = ?";
        $data = $this->dbSelect($sql, [$useruuid]);
        if ($params['password'] != $data[0]['password']) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('UI_SETTINGS_POWEROFF_PASSWORD_MSG')
            ];
        }
        $opName = $powertype == 1 ? 'BD_SYSTEM_OP_REBOOT_SYSTEM' : 'BD_SYSTEM_OP_POWEROFF_SYSTEM';
        $mbResult = $this->service()->mbNodeMsgs($opName, $nodeuuid, [], false, true);
        $this->unifyWriteSystemLog(
            $mbResult['result'],
            $powertype == 1 ? 'SYSTEM_SETTING_REBOOT' : 'SYSTEM_SETTING_POWER_OFF'
        );
        return [
            'code' => $mbResult['result'] ? 0 : 1,
            'msg' => xphp_get_lang(
                $powertype == 1 ? 'UI_SETTINGS_POWEROFF_REBOOT_MSG' : 'UI_SETTINGS_POWEROFF_POWEROFF_MSG'
            )
        ];
    }

    /**
     * 统一写系统日志
     * @param boolean $result           结果
     * @param string  $descriptionKey   描述key
     * @param array   $descriptionParam 描述数组
     * @return boolean
     */
    private function unifyWriteSystemLog($result, string $descriptionKey, $descriptionParam = [])
    {
        if ($result) {
            $this->systemLog($descriptionKey, $descriptionParam);
        } else {
            $this->systemLog($descriptionKey, $descriptionParam, xphp_get_config('log', 'LOGLEVEL')['WARN']);
        }
    }

    /**
     * 组装服务控制命令,除了启动和停止外,还会禁用和启用服务开机启动
     * @param string|integer $serviceName 服务名
     * @param boolean        $flag        状态,开启或关闭
     * @return string
     */
    private function groupServiceCtlCMD($serviceName, bool $flag): string
    {
        $cmd = '';
        if ($flag) {
            //启动并开机启动服务
            // 整形的表示是端口
            if ($serviceName == '' . intval($serviceName) && intval($serviceName)) {
                $cmd = "firewall-cmd --permanent --zone public --add-port $serviceName/tcp --quiet; ";
            } elseif ($serviceName == 'rpcbind') {
                $cmd = 'systemctl start rpcbind.socket;systemctl start rpcbind;
                        systemctl enable rpcbind.socket;systemctl enable rpcbind;';
            } else {
                $cmd .= "systemctl start $serviceName;systemctl enable $serviceName;";
            }
        } else {
            //关闭并禁用开机启动服务
            // 整形的表示是端口
            if ($serviceName == '' . intval($serviceName) && intval($serviceName)) {
                $cmd = "firewall-cmd --permanent --zone public --remove-port $serviceName/tcp --quiet;";
            } elseif ($serviceName == 'rpcbind') {
                $cmd = 'systemctl stop rpcbind.socket;systemctl stop rpcbind;
                    systemctl disable rpcbind.socket;systemctl disable rpcbind;';
            } else {
                $cmd .= "systemctl stop $serviceName;systemctl disable $serviceName;";
            }
        }

        return $cmd;
    }
}
