<?php

namespace app\v1\common\logic;

use app\v1\resources\v0\logic\Node;

/**
 * note          备份任务公共的一些方法
 * @author       wanggongxi@vinchin.com
 * @date         2023/6/20 11:21
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Backup extends Base
{
    /**
     * 组合备份时间策略
     * @param array $params   参数
     * @param int   $globalID 全局策略id
     * @return array
     */
    protected function groupBackupTimeList($params = [], $globalID = 0): array
    {
        $msg = array();
        $backupMode = xphp_get_config('task', 'BACKUP_MODE');
        if ('strategy' == $params['type']) {
            //按时间策略备份
            if (!empty($params['full_info'])) {
                //完全策略
                $msg[] = $this->groupEachTimestrategy($backupMode['FULL'], $params['full_info'], $globalID);
            }
            if (!empty($params['incr_info'])) {
                //增量策略
                $msg[] = $this->groupEachTimestrategy($backupMode['INCREMENTAL'], $params['incr_info'], $globalID);
            }
            if (!empty($params['diff_info'])) {
                //差异策略
                $msg[] = $this->groupEachTimestrategy($backupMode['DIFFERENTIAL'], $params['diff_info'], $globalID);
            }
            if (!empty($params['log_info'])) {
                //日志策略
                $msg[] = $this->groupEachTimestrategy($backupMode['LOG'], $params['log_info'], $globalID);
            }
            if (!empty($params['pincr_info'])) {
                //永久增量（mode==增量备份）
                $msg[] = $this->groupEachTimestrategy($backupMode['INCREMENTAL'], $params['pincr_info'], $globalID);
            }
        } elseif (in_array($params['type'],['immediate', 'manual'])) {  // 手动启动不需要传list
            $msg = [];
        } else {
            //一次性备份
            $strategy = array('start_time' => $params['datetime']);

            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            //获取一次性备份时间
            $taskCreateTime = strtotime($params['datetime']);
            if ($systemTime >= $taskCreateTime) {
                return $this->muOpResult(
                    false,
                    xphp_get_lang('WEB_DB_BACKUP_TIME'),
                    xphp_get_lang('WEB_DB_BACKUP_TIME_TIPS'),
                    'warning'
                );
            }

            $strategy['type'] = xphp_get_config('task', 'STRATEGY_TYPE')['ONCE'];
            $msg[] = $this->groupEachTimestrategy($backupMode['FULL'], $strategy, $globalID);
        }

        return $msg;
    }

    /**
     * 组合保留策略
     * @param array $reserve 保留策略信息
     *                       int type 类型
     *                       int value 值
     *                       bool archive 归档标记
     * @return array
     */
    public function groupReserverStrategy(array $reserve): array
    {
        $this->paramsCheck($reserve);
        $flag = xphp_get_config('app', 'FLAG');
        return [
            'enable_flag' => $reserve['enable_flag'] ? $flag['SET'] : $flag['UNSET'],
            'strategy_mode' => intval($reserve['strategyMode']) ? intval($reserve['strategyMode']) : 0,
            'strategy_type' => intval($reserve['type']),
            'number' => intval($reserve['value']),
            'auto_archive_flag' => $reserve['archive'] ? $flag['SET'] : $flag['UNSET'],
        ];
    }

    /**
     * 组合传输策略
     * @param array $transport 传输策略信息
     *                         bool encrypt 加密
     *                         bool compress 压缩
     *                         bool speed_flag 限速
     *                         int  speed
     * @return array
     */
    public function groupTransportStrategy(array $transport): array
    {
        $flag = xphp_get_config('app', 'FLAG');
        return array(
            'encrypt_flag' => $transport['encrypt'] ? $flag['SET'] : $flag['UNSET'],
            'encrypt_method' => intval($transport['encrypt_method']),
            'compress_flag' => $transport['compress'] ? $flag['SET'] : $flag['UNSET'],
            'speed_limit_flag' => $transport['speed_flag'] ? $flag['SET'] : $flag['UNSET'],
            'max_speed' => intval($transport['speed']),
            'network_uuid' => !empty($transport['network']) ? $transport['network'] : '', //传输网络
            'network_pool_uuid' => $transport['network_pool_uuid'] ?: '',
            'compress_method' => intval($transport['compress_method']) ? intval($transport['compress_method']) : 0,
            'reconnect_times' => 0,
            'reconnect_interval' => 0,
        );
    }

    /**
     * 组合存储策略
     * @param array $storage 存储策略信息
     *                        bool  encrypt  加密
     *                        int blocksize   数据块大小
      *                       bool compress   压缩
      *                       bool deduplication 重删
     * @return array
     */
    public function groupStorageStrategy(array $storage): array
    {
        $flag = xphp_get_config('app', 'FLAG');
        return array(
            //重删
            'deduplication_flag' => $storage['deduplication'] ? $flag['SET'] : $flag['UNSET'],
            'blocksize' => intval($storage['blocksize']),
            //加密
            'encrypt_flag' => $storage['dataencrypt'] ? $flag['SET'] : $flag['UNSET'],
            'password_auto_flag' => $storage['password_auto_flag'] ? $flag['SET'] : $flag['UNSET'],
            'password' => $storage['password'],
            'compress_flag' => $storage['compress'] ? $flag['SET'] : $flag['UNSET'],
            'compress_method' => intval($storage['compress_method']) ? intval($storage['compress_method']) : 0,
            'encrypt_method' => intval($storage['encrypt_method']),

            //新加的公共参数
            'redundant_data_proportion' => intval($storage['redundant_data_proportion']),
            'data_container_size' => intval($storage['data_container_size']),

        );
    }

    /**
     * 组合备份节点信息
     * @param array $node 备份节点信息
                          bool nodecheck   自动选择节点
                          string nodeuuid  节点UUID
                          bool storagecheck 自动选择存储
                          string storageuuid   存储UUID
     * @return array
     * ("node_uuid","auto_find_sr_flag",'storage_uuid')
     */
    protected function groupBackupNodeInfo(array $node): array
    {
        $flag = xphp_get_config('app', 'FLAG');
        return array(
            'node_uuid' => $this->getBackupNodeUUID($node),
            'node_pool_uuid' => $node['node_pool_uuid'] ?? '',
            'auto_find_sr_flag' => $node['storagecheck'] ? $flag['SET'] : $flag['UNSET'],
            'storage_uuid' => $node['storage_uuid'],
            'storage_pool_uuid' => $node['storage_pool_uuid'] ?? '',
        );
    }

    /**
     * 创建备份任务消息,有点烦,注释都晓不到纳闷写Σ( ° △ °|||)︴,将就看,看不懂就去看函数实现/ 创建(修改)副本任务
     * @param string $taskname          task name
     * @param int    $moduletype        product module type
     * @param array  $timestrategylist  array[array each_strategy, array
     *                                  each_strategy..多项]
     *                                  二维数组!!! array
     *                                  each_strategy int
     *                                  'strategy_type' int   'mode'
     *                                  string   'days' string
     *                                  'start_time' int   'roll_flag'
     *                                  int   'roll_interval' string
     *                                  'roll_end_time' int 'global_id'
     * @param array  $reserverstrategy  reserve strategy
     *                                  int strategy_type
     *                                  int number int
     *                                  auto_archive_flag
     * @param array  $transportstrategy transport strategy
     *                                  int encrypt_flag
     *                                  int compress_flag
     *                                  int speed_limit_flag
     *                                  int max_speed
     * @param array  $storagestrategy   存储资源
     *                                  int deduplication_flag
     *                                  int blocksize
     *                                  int encrypt_flag
     *                                  int compress_flag
     * @param array  $nodeinfo          节点信息
     * @param string $timStrategyBackupType 时间策略备份方式[strategy按策略备份 oncetime一次性备份 manual手动启动]
     * @return array
     *
     */
    protected function pfCreateBackupTaskMessage(
        string $taskname,
        int $moduletype,
        array $timestrategylist,
        array $reserverstrategy,
        array $transportstrategy,
        array $storagestrategy,
        array $nodeinfo,
        string $timeStrategyBackupType = 'strategy'
    ): array {
        $backupTaskMessage = array(
            'task_name' => $taskname,
            'module_type' => $moduletype,
            'auto_find_sr_flag' => $nodeinfo['auto_find_sr_flag'],
            'node_pool_uuid' => $nodeinfo['node_pool_uuid'] ?: '',
            'node_uuid' => $nodeinfo['node_uuid'] ?: '',
            'storage_uuid' => $nodeinfo['storage_uuid'] ?: '',
            'storage_pool_uuid' => $nodeinfo['storage_pool_uuid'] ?: '',
            'agent_uuid' => $transportstrategy['agent_uuid'] ?: '',
            'agent_pool_uuid' => $transportstrategy['agent_pool_uuid'] ?: '',
            'ignore_resource_limiting_flag' => 1,
            'time_strategy_backup_type' => xphp_get_config('app', 'TIME_STRATEGY_BACKUP_TYPE')[$timeStrategyBackupType]
        );
        $timestrategylistarray = array();
        foreach ($timestrategylist as $list) {
            //组合时间策略
            $timestrategylistarray[] = $this->pfTimeStrategyMessage(
                $list['strategy_type'],
                $list['mode'],
                $list['days'],
                $list['start_time'],
                $list['roll_flag'],
                $list['roll_interval'],
                $list['end_time'],
                $list['global_id'],
                $list['strategy_group_uuid'],
                $list['roll_end_time'],
                $list['full_backup_compensation_flag']
            );
        }
        $backupTaskMessage['time_strategy_list'] = $timestrategylistarray;

        if (!empty($reserverstrategy)) {
            //组合保留策略
            $backupTaskMessage['reserved_strategy'] = $this->pfReservedStrategyMessage(
                $reserverstrategy['strategy_type'],
                $reserverstrategy['number'],
                $reserverstrategy['auto_archive_flag'],
                $reserverstrategy['strategy_group_uuid'],
                $reserverstrategy['strategy_mode'],
                $reserverstrategy['enable_flag']
            );
        }
        $network = !empty($transportstrategy['network_uuid']) ? $transportstrategy['network_uuid'] : '';
        $networkPoolUuid = !empty($transportstrategy['network_pool_uuid']) ? $transportstrategy['network_pool_uuid'] : "";
        $agentUuid = $transportstrategy['agent_uuid'] ?: '';
        $agentPoolUuid = $transportstrategy['agent_pool_uuid'] ?: '';
        //组合传输策略
        $backupTaskMessage['transport_strategy'] = $this->pfTransportStrategyMessage(
            $transportstrategy['encrypt_flag'],
            $transportstrategy['compress_flag'],
            $transportstrategy['speed_limit_flag'],
            $transportstrategy['max_speed'],
            $network,
            $transportstrategy['strategy_group_uuid'],
            $transportstrategy['compress_method'],
            $transportstrategy['reconnect_times'],
            $transportstrategy['reconnect_interval'],
            $transportstrategy['encrypt_method'],
            $transportstrategy['appliance_agency_flag'],
            $transportstrategy['appliance_uuid'],
            $networkPoolUuid,
            $agentUuid,
            $agentPoolUuid
        );
        //组合存储策略
        $backupTaskMessage['storage_strategy'] = $this->pfStorageStrategyMessage(
            $storagestrategy['encrypt_flag'],
            $storagestrategy['compress_flag'],
            $storagestrategy['deduplication_flag'],
            $storagestrategy['blocksize'],
            $storagestrategy['strategy_group_uuid'],
            $storagestrategy['password_auto_flag'],
            $storagestrategy['password'],
            $storagestrategy['compress_method'],
            $storagestrategy['encrypt_method'],
            $storagestrategy['redundant_data_proportion'],
            $storagestrategy['data_container_size']
        );

        return $backupTaskMessage;
    }

    /**
     * 组合限速策略列表
     * @param array  $speedList         速度列表
     * @param string $strategygroupuuid 全局策略id
     * @return array
     */
    public function groupTaskSpeedList($speedList, $strategygroupuuid = ''): array
    {
        $info = array();
        foreach ($speedList as $speed) {
            $info[] = array(
                'strategy_uuid' => $speed['uuid'],
                'strategy_name' => '',
                'strategy_type' => $speed['type'],
                'start_time' => v1_formart_time($speed['start_time']),
                'end_time' => v1_formart_time($speed['end_time']),
                'days' => implode('', $speed['days']),
                'speed_limited_value' => $speed['value'],
                'extra_info' => '',
                'remark' => $speed['des'],
                'strategy_group_uuid' => $strategygroupuuid,
            );
        }
        return $info;
    }

    /**
     * 组合全局限速策略
     * @param array $speedList 限速策略
     * @return array
     */
    public function groupTaskSpeedGlobalList($speedList)
    {
        $info = [
            'task_priority' => $speedList['level'], // 任务级别
            'remark' => ''
        ];
        if ($speedList['type'] == 1) {
            // 全局策略
            if (empty($speedList['uuid'])) {
                return [];
            }
            $info['strategy_uuid'] = $speedList['uuid'];
            $info['strategy_name'] = $speedList['name'];
            $info['strategy_type'] = $speedList['strategy_type'];
            $info['is_global'] = 1;
            $info['speed_limit_info'] = [];
            $info['extra_info'] = '';
        } else {
            // 自定义
            $info['strategy_uuid'] = '';
            $info['strategy_name'] = '';
            $info['is_global'] = 0;
            foreach ($speedList['speedInfo'] as &$speedItem) {
                $speedItem['startTime'] = v1_formart_time($speedItem['startTime']);
                $speedItem['endTime'] = v1_formart_time($speedItem['endTime']);
            }
            $info['extra_info'] = json_encode($speedList['speedInfo']);
            // 查询出type和组装下策略
            if (count($speedList['speedInfo'])) {
                $strategytype = $speedList['speedInfo'][0]['type'];
                $info['strategy_type'] = $strategytype;
                if (in_array($strategytype, [1, 4, 5])) {
                    // 这三个类型的没有days的值
                    $timelist = [];
                    foreach ($speedList['speedInfo'] as $item) {
                        $timelist[] = [
                            'start_time' => $strategytype != 5 ? v1_formart_time($item['startTime']) : $item['startTime'],
                            'end_time' => $strategytype != 5 ? v1_formart_time($item['endTime']) : $item['endTime'],
                            'speed_limited_value' => $item['value'],
                        ];
                    }
                    $speedlimitinfo[] = [
                        'days' => '',
                        'time_list' => $timelist
                    ];
                } else {
                    $speedlimitinfos = [];
                    foreach ($speedList['speedInfo'] as $item) {
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
                $info['speed_limit_info'] = $speedlimitinfo;
            } else {
                // 未定义，直接传空数组
                return [];
            }
        }

        return $info;
    }

    /**
     * 插入时间策略
     * @param int   $strategyID   策略ID
     * @param int   $modeType     mode
     * @param array $strategyInfo 信息
     * @return int
     */
    protected function insertTimeStrategy($strategyID, $modeType, $strategyInfo)
    {
        if (!empty($strategyInfo['roll_end_time'])) {
            $strategyInfo['end_time'] = $strategyInfo['roll_end_time'];
        }
        $days = $this->getTimeStrategyDaysStr($strategyInfo['days']) . $strategyInfo['frequency'];
        $rollFlag = v1_parse_bool_to_flag($strategyInfo['roll_flag']);
        $sql = "insert bd_time_strategy (strategy_id, mode, strategy_type, days, start_time,
                    roll_flag, roll_interval, roll_end_time) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $sqlParams = array(
            $strategyID,
            $modeType,
            $strategyInfo['type'],
            $days,
            v1_formart_time($strategyInfo['start_time']),
            $rollFlag,
            v1_time_to_sec($strategyInfo['roll_interval']),
            v1_formart_time($strategyInfo['end_time'])
        );

        return $this->dbQuery($sql, $sqlParams);
    }

    /**
     * 获取时间策略天数的字符串表示
     * @param array $days 天数数组
     * @return string
     */
    protected function getTimeStrategyDaysStr($days = []): string
    {
        return empty($days) ? '' : implode('', $days);
    }

    /**
     * 获取代理列表信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getAgentLists(string $taskuuid): array
    {

        $sql = "select btal.agent_uuid,ba.authorization_module,ba.online_flag
                    from bd_task_agent_list btal,bd_agent ba
                    where btal.task_uuid = ?
                      and btal.agent_uuid = ba.agent_uuid";
        $data = $this->dbSelect($sql, array($taskuuid));
        $agentOnlineNum = $agentOfflineNum = 0;

        if (!empty($data)) {
            foreach ($data as $d) {
                $authorization = $this->checkModuleValid($d['authorization_module'], 'file');
                if ($authorization && $d['online_flag'] == 1) {
                    $agentOnlineNum++;
                } else {
                    $agentOfflineNum++;
                }
            }
        }
        return [
            [
                'agent_online_num' => $agentOnlineNum,
                'agent_offline_num' => $agentOfflineNum,
                'agent_list' => !empty($data) ? array_column($data, 'agent_uuid') : [],
            ]
        ];
    }

    /**
     * 获取任务限速策略列表信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getSpeedStrategyInfo(string $taskuuid)
    {
        $sql = "select strategy_uuid, speed_limited_value, start_time, end_time, days, remark, strategy_type
                    from bd_task_speed_limit_strategy where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();

        foreach ($data as $d) {
            $value = intval($d['speed_limited_value']);
            $valueList = v1_calsize_to_value_and_unit($value);
            $info[] = array(
                'strategy_uuid' => $d['strategy_uuid'],
                'strategy_type' => $d['strategy_type'],
                'start_time' => $d['start_time'],
                'end_time' => $d['end_time'],
                'days' => $this->parseSpeedStrategyDay($d['days']),
                'value' => $value,
                'des' => $d['remark'],
                'unit' => $valueList['unit'] . '/s',
                'speed_num' => intval($valueList['value'])
            );
        }
        return $info;
    }

    /**
     * 转换限速策略天数为一个数组,每一项为0,1
     * @param string $days 天
     * @return array
     */
    protected function parseSpeedStrategyDay($days)
    {
        if (empty($days)) {
            return [];
        }
        $daysArr = str_split($days);
        foreach ($daysArr as $key => $d) {
            if ($d) {
                $daysArr[$key] = 1;
            } else {
                $daysArr[$key] = 0;
            }
        }
        return $daysArr;
    }

    /**
     * 转化时间策略天数为一个数组,每一项为boll
     * @param string $days 天
     * @return array
     */
    protected function parseTimeStrategyDay($days)
    {
        if (empty($days)) {
            return array();
        }
        $daysArr = str_split($days);
        $trueDays = array();
        foreach ($daysArr as $key => $d) {
            //如果遇到s,结束    现在每周存储格式为0000001s1   s1表示间隔一周,以此类推
            if ('s' == $d) {
                break;
            }
            if ($d) {
                $trueDays[$key] = true;
            } else {
                $trueDays[$key] = false;
            }
        }
        return $trueDays;
    }

    /**
     * 得到每周的执行间隔
     * 目前只有每周会返回数据,
     * @param int    $strategyType 类型
     * @param string $days         天
     * @return string  空字符串  s1 - s4
     */
    protected function parseTimeStrategyFrequency($strategyType, $days): string
    {
        $frequency = '';
        if (xphp_get_config('task', 'STRATEGY_TYPE')['EVERY_WEEK'] != intval($strategyType)) {
            return $frequency;
        }
        $strIndex = strpos($days, 's');
        if ($strIndex) {
            $frequency = substr($days, $strIndex);
        }
        return $frequency;
    }

    /**
     * 检测系统授权状态
     * @param int $authorizationstatus nas授权状态
     * @return int 授权状态
     */
    protected function checkSystemAuth(int $authorizationstatus): int
    {

        $status = $this->checkSystemAuthStatus();
        if (!$status && $authorizationstatus == 1) {
            // 未正常授权返回错误提示
            return 1;
        }

        return 2;
    }

    /**
     * 根据路径得到文件名
     * @param int    $pathType 类型
     * @param string $pathName 名称
     * @return string
     */
    protected function getFileName(int $pathType, string $pathName): string
    {
        $pathArr = explode('/', $pathName);
        $arrLen = count($pathArr);
        if (xphp_get_config('file', 'FILETYPE')['FILE'] == $pathType) {
            //文件
            $filename = $pathArr[$arrLen - 1];
        } else {
            //目录
            $filename = $pathArr[$arrLen - 2] ?? '';
        }
        return $filename;
    }

    /**
     * 存储策略消息
     * @param int    $encryptflag       加密flag
     * @param int    $compressflag      压缩flag
     * @param int    $deduplicationflag 持续
     * @param int    $blocksize         块
     * @param string $strategygroupuuid 资源分组uuid
     * @param int    $passwordautoflag  密码自动
     * @param string $password          密码
     * @return array $storageStrategyMessage
     */
    private function pfStorageStrategyMessage(
        $encryptflag,
        $compressflag,
        $deduplicationflag,
        $blocksize,
        $strategygroupuuid,
        $passwordautoflag,
        $password,
        $compressmethod,
        $encryptmethod,
        $redundantdataproportion,
        $datacontainersize
    ): array {
        return array(
            'encrypt_flag' => $encryptflag,
            'compress_flag' => $compressflag,
            'deduplication_flag' => $deduplicationflag,
            'block_size' => $blocksize,
            'strategy_group_uuid' => $strategygroupuuid,
            'password_auto_flag' => $passwordautoflag,
            'password' => $password,
            'compress_method' => $compressmethod,
            'encrypt_method' => $encryptmethod,
            'redundant_data_proportion' => $redundantdataproportion,
            'data_container_size' => $datacontainersize,
        );
    }

    /**
     * 传输策略消息
     * @param int    $encryptflag       加密flag
     * @param int    $compressflag      压缩flag
     * @param int    $speedlimitflag    限速flag
     * @param int    $maxspeed          最大速度
     * @param int    $networkuuid       网关uuid
     * @param string $strategygroupuuid 资源分组uuid
     * @param int    $appliance_agency_flag 传输代理flag
     * @param string $appliance_uuid 传输代理uuid
     * @return array $transportStrategyMessage
     */
    public function pfTransportStrategyMessage(
        $encryptflag,
        $compressflag,
        $speedlimitflag,
        $maxspeed,
        $networkuuid,
        $strategygroupuuid,
        $compress_method,
        $reconnect_times,
        $reconnect_interval,
        $encrypt_method,
        $appliance_agency_flag,
        $appliance_uuid,
        string $networkPoolUuid = '',
        $agentUuid = '',
        $agentPoolUuid = ''
    ): array {
        return array(
            'encrypt_flag' => $encryptflag,
            'compress_flag' => $compressflag,
            'speed_limit_flag' => $speedlimitflag,
            'max_speed' => $maxspeed,
            'network_uuid' => $networkuuid,
            'network_pool_uuid' => $networkPoolUuid,
            'agent_uuid' => $agentUuid,
            'agent_pool_uuid' => $agentPoolUuid,
            'strategy_group_uuid' => $strategygroupuuid,
            'compress_method' => $compress_method,
            'reconnect_times' => $reconnect_times,
            'reconnect_interval' => $reconnect_interval,
            'encrypt_method' => $encrypt_method,
            'appliance_agency_flag' => $appliance_agency_flag,
            'appliance_uuid' => $appliance_uuid
        );
    }

    /**
     * 保留策略消息
     * @param int    $strategytype      days or number
     * @param int    $number            value of days or number
     * @param int    $autoarchiveflag   auto archive flag
     * @param  string $strategygroupuuid 资源分组uuid
     * @return array $reservedStrategyMessage
     */
    protected function pfReservedStrategyMessage($strategytype, $number, $autoarchiveflag, $strategygroupuuid, $strategy_mode, $enbale_flag = 1): array
    {

        return array(
            'strategy_type' => $strategytype,
            'number' => $number,
            'auto_archive_flag' => empty($autoarchiveflag) ? false : $autoarchiveflag,
            'strategy_group_uuid' => $strategygroupuuid,
            'strategy_mode' => $strategy_mode,
            'enable_flag' => $enbale_flag,
        );
    }

    /**
     * 时间策略消息
     * @param int    $strategytype      strategy type
     * @param int    $mode              bakup or recovery mode
     * @param string $days              the select days map
     * @param string $starttime         strategy start time
     * @param int    $rollflag          wheather start roll strategy
     * @param int    $rollinterval      the roll interval time
     * @param string $rollendtime       the end time of roll
     * @param int    $globalid          the global strategy id, 0-indicate this is not a global strategy
     * @param string $strategygroupuuid 资源分组uuid
     * @return array
     */
    private function pfTimeStrategyMessage(
        $strategytype,
        $mode,
        $days,
        $starttime,
        $rollflag,
        $rollinterval,
        $endtime,
        $globalid,
        $strategygroupuuid,
        $rollendtime,
        $full_backup_compensation_flag = 2
    ): array {

        return array(
            'strategy_type' => $strategytype,
            'mode' => $mode,
            'days' => $days,
            'start_time' => v1_formart_time($starttime),
            'roll_flag' => $rollflag,
            'roll_interval' => $rollinterval,
            'end_time' => v1_formart_time($endtime),
            'global_id' => empty($globalid) ? 0 : $globalid,
            'strategy_group_uuid' => $strategygroupuuid,
            'roll_end_time' => v1_formart_time($rollendtime),
            'full_backup_compensation_flag' => $full_backup_compensation_flag
        );
    }

    /**
     * 得到备份的节点信息
     * @param array $node 备份节点信息
     *   bool nodecheck   自动选择节点
     *  string nodeuuid  节点UUID
     *  bool storagecheck 自动选择存储
     *  string storageuuid   存储UUID
     * @return string
     */
    public function getBackupNodeUUID(array $node): string
    {
        if (!$node['nodecheck']) {
            //自定义节点
            return $node['node_uuid'];
        }
        //自动选择节点
        return (new Node())->getAutoFindNode();
    }

    /**
     * 组合每一个时间策略
     * @param int   $mode     完全1/增量2/差异3/日志4/标签5
     * @param array $strategy 策略
     * @param int   $globalID 全局策略id
     * @return array
     */
    public function groupEachTimestrategy(int $mode, array $strategy, $globalID = 0): array
    {

        $strArr = array('mode' => $mode, 'global_id' => 0, 'strategy_group_uuid' => '');
        if ($globalID) {
            //使用全局策略
            $strArr['global_id'] = $globalID;
        }
        foreach ($strategy['days'] as &$item) {
            if (empty($item)) {
                $item = 0;
            }
        }

        //时间策略
        $strArr['strategy_type'] = $strategy['type'];
        $strArr['full_backup_compensation_flag'] = v1_parse_bool_to_flag($strategy['full_backup_compensation_flag']);
        $strArr['days'] = implode('', is_array($strategy['days']) ? $strategy['days'] : []) . $strategy['frequency'];
        $strArr['start_time'] = v1_formart_time($strategy['start_time']);
        $strArr['roll_flag'] = $strategy['roll_flag'];
        $strArr['roll_interval'] = $this->getRollInterval($strategy['roll_interval']);
        $strArr['end_time'] = v1_formart_time($strategy['end_time']);
        $strArr['roll_end_time'] = v1_formart_time($strategy['roll_end_time']);
        $storategyType = xphp_get_config('task', 'STRATEGY_TYPE');
        if ($storategyType['EVERY_DAY'] == intval($strArr['strategy_type'])) {
            //每天备份
            $strArr['days'] = '1111111';
        }

        if ($storategyType['ONCE'] == intval($strArr['strategy_type'])) {
            //一次性备份
            $strArr['days'] = '';
        }
        $storategyRollType = xphp_get_config('task', 'STRATEGY_ROLL_TYPE');
        if ($strArr['roll_flag']) {
            //滚动备份
            $strArr['roll_flag'] = $storategyRollType['ON'];
        } else {
            //不滚动
            $strArr['roll_flag'] = $storategyRollType['OFF'];
            $strArr['roll_interval'] = 0;
            $strArr['end_time'] = '';
            $strArr['roll_end_time'] = '';
        }
        return $strArr;
    }

    /**
     * 转化滚动间隔为秒
     * @param string $rollInterval roll
     * @return float|int
     */
    private function getRollInterval($rollInterval)
    {
        if (empty($rollInterval)) {
            return 0;
        }
        $intervalArr = explode(':', $rollInterval);
        return intval($intervalArr[0]) * 3600 + intval($intervalArr[1]) * 60 + intval($intervalArr[2]);
    }

    /**
     * 获取时间策略的备份方式[strategy oncetime manual]
     * @param int $strategyID
     * @return mixed|string
     */
    public function getTimeStrategyBackupType($strategyID)
    {
        $sql = "SELECT time_strategy_backup_type FROM bd_strategy WHERE strategy_id = ? ";
        $timeStrategyBackupData = $this->dbSelect($sql, [$strategyID]);
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time 
                from bd_time_strategy where strategy_id = ? ";
        $data = $this->dbSelect($sql, array($strategyID));

        $timeStrategyBackupType = 'strategy';
        if (
            !$timeStrategyBackupData ||
            !is_array($timeStrategyBackupData) ||
            !$timeStrategyBackupData[0]['time_strategy_backup_type']
        ) {  // 走之前逻辑
            if (1 == count($data) && xphp_get_config('task', 'STRATEGY_TYPE')['ONCE'] == $data[0]['strategy_type']) {
                $timeStrategyBackupType = 'oncetime';
            }
        } else {
            $timeStrategyBackupType = xphp_get_config('task', 'TIME_STRATEGY_BACKUP_TYPE_MAP')[$timeStrategyBackupData[0]['time_strategy_backup_type']];
        }
        return $timeStrategyBackupType;
    }
    /**
     * 组合备份时间策略
     * @param {} $params
     * @return array
     */
    public function newGroupBackupTimeList($params = [])
    {
        $BACKUP_MODE = xphp_get_config('task', 'BACKUP_MODE');
        $msg = [];
        if ('strategy' == $params['type']) {
            //按时间策略备份
            if (!empty($params['timeInfo']['fullInfo'])) {
                //完全策略
                $msg[] = $this->newGroupEachTimestrategy($BACKUP_MODE['FULL'], $params['timeInfo']['fullInfo']);
            }
            if (!empty($params['timeInfo']['incrInfo'])) {
                //增量策略
                $msg[] = $this->newGroupEachTimestrategy($BACKUP_MODE['INCREMENTAL'], $params['timeInfo']['incrInfo']);
            }
            if (!empty($params['timeInfo']['diffInfo'])) {
                //差异策略
                $msg[] = $this->newGroupEachTimestrategy($BACKUP_MODE['DIFFERENTIAL'], $params['timeInfo']['diffInfo']);
            }
            if (!empty($params['timeInfo']['pIncrInfo'])) {
                //差异策略
                $msg[] = $this->newGroupEachTimestrategy($BACKUP_MODE['INCREMENTAL'], $params['timeInfo']['pIncrInfo']);
            }
        } else if ('oncetime' == $params['type']) {
            //一次性备份
            $strategy = array('startTime' => $params['data']);

            //获取系统时间
            $systemTime = strtotime(date('Y-m-d H:i:s'));
            //获取一次性备份时间
            $taskCreateTime = strtotime($params['data']);
            if ($systemTime >= $taskCreateTime) {
                exit($this->muOpResult(false, xphp_get_lang('WEB_DB_BACKUP_TIME'), xphp_get_lang('WEB_DB_BACKUP_TIME_TIPS'), 'warning'));
            }

            $strategy['type'] = xphp_get_config('task','STRATEGY_TYPE')['ONCE'];
            $msg[] = $this->newGroupEachTimestrategy($BACKUP_MODE['FULL'], $strategy);
        } else if ('manual' == $params['type']) {
            $msg = [];
        }
        return $msg;
    }
    /**
     * 组合每一个时间策略
     * @param int   $mode     完全1/增量2/差异3/日志4/标签5
     * @param array $strategy 策略
     * @param int   $globalID 全局策略id
     * @return array
     */
    public function newGroupEachTimestrategy(int $mode, array $strategy, $globalID = 0): array
    {

        $strArr = array('mode' => $mode, 'global_id' => 0, 'strategy_group_uuid' => '');
        if ($globalID) {
            //使用全局策略
            $strArr['global_id'] = $globalID;
        }
        foreach ($strategy['days'] as &$item) {
            if (empty($item)) {
                $item = 0;
            }
        }

        //时间策略
        $strArr['strategy_type'] = $strategy['type'];
        $strArr['full_backup_compensation_flag'] = v1_parse_bool_to_flag($strategy['full_backup_compensation_flag']);
        $strArr['days'] = implode('', is_array($strategy['days']) ? $strategy['days'] : []) . $strategy['frequency'];
        $strArr['start_time'] = v1_formart_time($strategy['startTime']);
        $strArr['roll_flag'] = $strategy['rollFlag'];
        $strArr['roll_interval'] = $this->getRollInterval($strategy['rollInterval']);
        $strArr['end_time'] = v1_formart_time($strategy['endTime']);
        $strArr['roll_end_time'] = v1_formart_time($strategy['endTime']);
        $storategyType = xphp_get_config('task', 'STRATEGY_TYPE');
        if ($storategyType['EVERY_DAY'] == intval($strArr['strategy_type'])) {
            //每天备份
            $strArr['days'] = '1111111';
        }

        if ($storategyType['ONCE'] == intval($strArr['strategy_type'])) {
            //一次性备份
            $strArr['days'] = '';
        }
        $storategyRollType = xphp_get_config('task', 'STRATEGY_ROLL_TYPE');
        if ($strArr['roll_flag']) {
            //滚动备份
            $strArr['roll_flag'] = $storategyRollType['ON'];
        } else {
            //不滚动
            $strArr['roll_flag'] = $storategyRollType['OFF'];
            $strArr['roll_interval'] = 0;
            $strArr['end_time'] = '';
            $strArr['roll_end_time'] = '';
        }
        return $strArr;
    }
     /**
     * 根据策略id得到时间策略信息
     * @param int $strategyID
     * @return array
     */
    public function getTimeStrategyInfo(int $strategyID): array
    {
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time,full_backup_compensation_flag
                from bd_time_strategy where strategy_id = ?";
        $data = (array) $this->dbSelect($sql, array($strategyID));
        if (!$data) {
            // 手动启动
            return [
                'type' => 'manual',
                'data' => [],
            ];
        }
        $timeStrategyBackupType = $this->getTimeStrategyBackupType($strategyID);
        $info = [
            'type' => $timeStrategyBackupType,
            'data' => [],
        ];

        switch ($timeStrategyBackupType) {
            case 'oncetime':
                $info['data'] = $data[0]['start_time'];
                break;
            case 'strategy':
                $strategyData = array();
                $allBackupMode = array_column($data, 'mode');
                //可能有多个策略类型(每天,每周,每月)
                foreach ($data as $d) {
                    if ($d['roll_flag'] == xphp_get_config('app', 'FLAG')['SET']) {
                        $rollInterval = $d['roll_interval'];
                        $endTime = $d['roll_end_time'];
                    } else {
                        $rollInterval = '3600';
                        $endTime = '23:59:59';
                    }
                    $strategyData[] = array(
                        'start_time' => $d['start_time'],
                        'roll_flag' => v1_parse_flag_to_bool($d['roll_flag']),
                        'roll_interval' => v1_sec_to_time($rollInterval),
                        'roll_end_time' => $endTime,
                        'mode' => !in_array(xphp_get_config('task', 'BACKUP_MODE')['FULL'], $allBackupMode)
                            && xphp_get_config('task', 'BACKUP_MODE')['INCREMENTAL'] == $d['mode']
                            ? xphp_get_config('task', 'BACKUP_MODE')['PINCREMENTAL'] : $d['mode'],  // 没有完全备份就是永久增量
                        'strategy_type' => intval($d['strategy_type']),
                        'days' => $this->parseTimeStrategyDay($d['days']),
                        'frequency' => $this->parseTimeStrategyFrequency($d['strategy_type'], $d['days']),
                        'full_backup_compensation_flag' => v1_parse_flag_to_bool($d['full_backup_compensation_flag']),
                    );
                }
                $info['data'] = $strategyData;
                break;
            default:
                break;
        }
        return $info;
    }




    /**
     * 组合重试策略
     * @param {} $params
     * @return array
     */
    public function groupRetryStrategy($params = [])
    {
        $params['op_retry_flag'] = v1_parse_bool_to_flag($params['op_retry_flag']);
        $params['task_retry_flag'] = v1_parse_bool_to_flag($params['task_retry_flag']);
        return $params;
    }


    /**
     * 获取脚本详请
     * $module_type 模块类型 必须
     * $task_type  任务类型 必须
     * $sub_module_type 子模块类型 可选
     * $extraInfo = array('agent_uuid'=>"xxxxx")  agent_uuid额外参数 必填
     * 
     */
    public function getScriptList($module_type,$task_type,$extraInfo,$sub_module_type = 0){
        $resultData = array();
        
        switch ($module_type) {
            case xphp_get_config('module','MODULE_TYPE'):
                
                break;
        }

    }

    /**
     * 组合安全策略
     * @param mixed  $safeConfigStrategy 安全策略
     * @param mixed  $storageUuid        存储设备uuid
     * @return mixed
     */
    public function groupSafeConfigStrategy($safeConfigStrategy, $storageUuid = '')
    {
        if ($storageUuid) {
            $sql = "SELECT storage_type FROM bd_storage_resource WHERE storage_uuid = ? ";
            $storageData = $this->dbSelect($sql, [$storageUuid]);
            if (is_array($storageData) && $storageData) {
                if ($storageData[0]['storage_type'] == xphp_get_config('storage', 'BD_STORAGE_TYPE')['TAPE']) {
                    return [
                        'worm_flag' => v1_parse_bool_to_flag(false),
                        'worm_protection_time' => 0,
                        'virus_scan_flag' => v1_parse_bool_to_flag(false),
                        'virus_scan_config_list' => '',
                        'integrity_check_flag' => v1_parse_bool_to_flag(false),
                        'integrity_check_config' => [],
                    ];
                }
            }
        }
        $safeConfigStrategy['worm_flag'] =  v1_parse_bool_to_flag($safeConfigStrategy['worm_flag']);
        $safeConfigStrategy['virus_scan_flag'] =  v1_parse_bool_to_flag($safeConfigStrategy['virus_scan_flag']);
        $safeConfigStrategy['integrity_check_flag'] =  v1_parse_bool_to_flag($safeConfigStrategy['integrity_check_flag']);
        return $safeConfigStrategy;
    }

    /**
     * 构建网络uuid
     * @param string $nodeUuid    节点uuid
     * @param string $networkUuid 网络uuid
     * @return string
     */
    public function buildNetworkUuid(string $nodeUuid, string $networkUuid): string
    {
        if (!$nodeUuid) {
            return '';
        }
        if (!$networkUuid) {
            return '';
        }
        // 继续获取新加节点的网络
        $sql = "SELECT network_uuid FROM bd_node_network WHERE node_uuid = ? ORDER BY network_order ";
        $networkData = $this->dbSelect($sql, [$nodeUuid]);
        foreach ($networkData as $networkInfo) {
            if ($networkUuid == $networkInfo['network_uuid']) {
                // 如果选择了当前节点网络，直接返回
                return $networkUuid;
            }
        }
        // 返回选择节点默认第一顺序网络
        return $networkData[0]['network_uuid'];
    }
}
