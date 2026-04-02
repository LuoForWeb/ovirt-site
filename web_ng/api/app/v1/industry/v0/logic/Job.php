<?php

namespace app\v1\industry\v0\logic;

use app\v1\common\logic\JobInfo;

/**
 * note          行业合规任务详情相关logic
 * @author       wanggongxi@vinchin.com
 * @date         2025/3/13 18:25
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class Job extends JobInfo
{
    /**
     * 获取数据验证获取任务详情
     * @param string $jobUuid 任务uuid
     * @return array
     */
    public function getVerifyJobDetail(string $jobUuid): array
    {
        //获取数据
        $sql = "select bt.strategy_id, bt.task_uuid, bt.node_uuid, bt.task_name, bt.task_type, bt.task_status,
               unix_timestamp(bt.create_time) create_time,unix_timestamp(bri.start_time) as start_time,
               ssb.surebackup_task_type, ssb.automatic_verifitied_flag, ssb.limit_boot_vm_num,
               ssb.mount_protocol, ssb.nfs_server_ip, ssb.appgroup_uuid, ssb.virtual_lab_uuid,
                ssb.backup_server_ip,unix_timestamp(bs.next_start_time) next_start_time,
                 (select ssi.advance_config_json
                    from sr_surebackup_item ssi where ssi.sr_task_uuid = bt.task_uuid limit 1
                  ) as advance,bt.ignore_resource_limiting_flag
                from bd_task bt
                left join sr_surebackup ssb on bt.task_uuid = ssb.sr_task_uuid
                left join bd_running_info bri on bt.task_uuid = bri.task_uuid
                left join bd_strategy bs on bt.strategy_id = bs.strategy_id 
                where bt.task_uuid = ? limit 1";

        //处理数据
        $result = $this->dbSelect($sql, [$jobUuid]);

        //如果数据为空
        if (empty($result)) {
            return [];
        }
        $data = $result[0];
        $verifyMode = xphp_get_desc('Verification', 'VERIFY_MODE_DES');
        $verifyType = xphp_get_desc('Verification', 'VERIFY_TYPE_DES');
        $advance = !empty($data['advance']) ? json_decode($data['advance'], true) : [];
        return [
            'basic_info' => [
                'task_name' => $data['task_name'],
                'task_type' => intval($data['task_type']),
                'task_type_des' => xphp_get_desc('Pf', 'TASKTYPEDES')[intval($data['task_type'])],
                'task_status' => intval($data['task_status']),
                'task_status_des' => xphp_get_desc('Pf', 'TASKSTATUSDES')[intval($data['task_status'])],
                'start_time' => $this->getStartTIme($data['start_time'], $data['task_status']),
                'run_time' => $this->getTimeInterval($data['start_time'], $data['task_status']),
                'verify_mode' => intval($data['surebackup_task_type']),
                'verify_mode_des' => $verifyMode[intval($data['surebackup_task_type'])],
                'automatic_verifitied_flag' => intval($data['automatic_verifitied_flag']),
                'verify_type_des' => $verifyType[intval($data['automatic_verifitied_flag'])],
                'create_time' => $this->parseDate($data['create_time']),
                'node_uuid' => $data['node_uuid'],
                'backup_server_ip' => $data['backup_server_ip'], // 备份节点IP地址
                'limit_boot_vm_num' => $data['limit_boot_vm_num'], // 并发验证对象数量
                'doc_compare_thread' => $advance['doc_compare_thread'] ?: 1, // 线程数量
                //下次开始时间
                'next_time' => $this->getNextStartTime($data['next_start_time'], $data['task_status']),
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data['ignore_resource_limiting_flag']),
            ],
            'time_strategy' => $this->getJobTimeStrategy($data['strategy_id']),
            'high_strategy' => $this->groupHighInfo($data),
        ];
    }

    /**
     * 组合高级配置
     * @param array $d 参数
     * @return array
     */
    private function groupHighInfo(array $d): array
    {
        if (!empty($d['appgroup_uuid'])) {
            $sql1 = "select appgroup_name from sr_application_group where appgroup_uuid = ? ";
            $data1 = $this->dbSelect($sql1, array($d['appgroup_uuid']));
        }

        if (!empty($d['virtual_lab_uuid'])) {
            $sql2 = "select virtual_lab_name from sr_virtual_lab where virtual_lab_uuid = ? ";
            $data2 = $this->dbSelect($sql2, array($d['virtual_lab_uuid']));
        }

        $nullSpace = xphp_get_config('app')['NULLSPACE'];

        return array(
            'limit_boot_vm_num' => intval($d['limit_boot_vm_num']),
            'mount_protocol' => intval($d['mount_protocol']),
            'nfs_server_ip' => $d['nfs_server_ip'],
            'appgroup_uuid' => $d['appgroup_uuid'],
            'appgroup_name' => !empty($d['appgroup_uuid']) ? $data1[0]['appgroup_name'] : $nullSpace,
            'virtual_lab_uuid' => $d['virtual_lab_uuid'],
            'virtual_lab_name' => !empty($d['virtual_lab_uuid']) ? $data2[0]['virtual_lab_name'] : $nullSpace,
        );
    }

    /**
     * 获取数据验证对象列表
     * @param array $params 请求参数
     * @return array
     */
    public function getVerifyObjectList($params = []): array
    {
        $jobuuid = $params['job_uuid'];
        //起始页
        $offset = $params['offset'];
        //每页呈现的数据量
        $limit = $params['limit'];
        //排序字段
        $sort = $params['sort'];
        //排序方式
        $order = $params['order'];
        //搜索参数
        $keyword = $params['search'];
        //获取数据
        $sql = "select distinct ssi.ori_uuid,ssi.item_status,ssi.ori_name,ir.status,ssi.max_boot_time,
                bt.task_status,ba.hostname,ba.ip,ssi.advance_config_json
                 from sr_surebackup_item ssi
                      left join bd_agent ba on ba.agent_uuid = ssi.ori_uuid
                     left join industry_report ir on ir.task_uuid = ssi.sr_task_uuid and ir.agent_uuid = ssi.ori_uuid
                     left join bd_task bt on bt.task_uuid = ir.task_uuid
                where ssi.sr_task_uuid = ? ";

        $sqlcount = "select count(ssi.item_id) as total
                        from sr_surebackup_item ssi
                        left join bd_agent ba on ba.agent_uuid = ssi.ori_uuid
                        left join vm_emd ve on ve.uuid = ssi.new_uuid where ssi.sr_task_uuid = ? ";
        $sqlparams = array($jobuuid);

        //按对象名称搜索
        if (!empty($keyword)) {
            $like = " like '%" . $keyword . "%' ";
            $where = " and ( ssi.ori_name {$like}  or ba.hostname {$like} or ba.ip {$like})";
            $sql .= $where;
            $sqlcount .= $where;
        }
        $count = $this->dbSelect($sqlcount, $sqlparams);
        if (empty($count[0]['total'])) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        //如果还有排序参数,则进行排序
        if (!empty($sort) && !empty($order)) {
            $sql .= " order by " . $sort . ' ' . $order . ',ssi.item_id desc';
        } else {
            $sql .= '  order by ssi.item_status desc,ssi.item_id desc';
        }
        if (!empty($offset) && !empty($limit)) {
            $sql .= ' limit ?, ?';
            $sqlparams = array_merge($sqlparams, array($offset, $limit));
        }
        //处理数据
        $result = $this->dbSelect($sql, $sqlparams);
        $info = array(
            'rows' => [],
            'total' => intval($count[0]['total']),
        );
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        $statusDesArr = xphp_get_desc('Vm', 'VERIFY_VM_STATUS');
        foreach ($result as $each) {
            // 需要根据报告的状态判断下当前设备的状态
            if (
                in_array(
                    $each['task_status'],
                    [$taskStatus['RUNNING'], $taskStatus['FINISHED'], $taskStatus['SUCCESSED']]
                )
                && $each['status'] == 9 && in_array($each['item_status'], [5, 6])
            ) {
                // 报告未生成，并且设备状态是已完成和成功，需要手动改为进行中
                $each['item_status'] = 2;
            }
            if ($each['task_status'] == $taskStatus['STOPPED']) {
                // 任务状态是停止的，那么对象列表显示未等待
                $each['item_status'] = 1;
                $statusDes = xphp_get_lang('WEB_PLATFORM_DES_STOP');
            } elseif ($each['item_status'] == $taskStatus['RUNNING']) {
                // 状态是运行中-》验证中
                $statusDes = xphp_get_lang('UI_HOMEPAGE_VERIFYING');
            } else {
                $statusDes = $statusDesArr[intval($each['item_status'])];
            }
            $advance = json_decode($each['advance_config_json'], true);
            $objectName = empty($each['ori_name']) ? ($each['hostname'] . '(' . $each['ip'] . ')') : $each['ori_name'];
            $info['rows'][] = array(
                'object_uuid' => $each['ori_uuid'],
                'object_name' => $objectName,
                'status' => intval($each['item_status']),
                'max_boot_time' => xphp_formatSeconds(intval($each['max_boot_time'])),
                'status_des' => $statusDes,
                'screen_flag' => $advance['screen_flag'] ?? 1, // 开机结果截屏验证
            );
        }

        return $info;
    }

    /**
     * 切换设备获取右边的详细信息
     * @param array $params 请求参数
     * @return array
     */
    public function getClientInfos(array $params)
    {
        $jobUuid = $params['job_uuid'];
        $clientUuid = $params['client_uuid'];

        $sql = 'SELECT ssi.ping_status,ssi.heartbeat_status,ssi.screen_status,ssi.item_status,ssi.ori_uuid,
                        ssi.new_uuid,ssi.timepoint_uuids_json,ve.console_url,ssi.doc_list_json,
                        ba.id,ba.agent_name,ba.hostname,ba.ip,ir.status,ir.result,ssi.doc_consistency_status,
                        ir.create_time,ir.screen_list,ir.screen_result,ir.item_plan,ir.item_result,ir.description,
                        ssr.doc_compare_result_json,ssr.screen_shot_path,ssr.extension_info,ssr.start_time,ssr.end_time,
                        ir.report_uuid,ssr.`timestamp`,ssr.timepoint_uuid,bu.user_name,bt.task_status
                FROM sr_surebackup_item ssi
                LEFT JOIN vm_emd ve ON ve.uuid = ssi.new_uuid and ve.prefix_status = ?
                LEFT JOIN bd_agent ba ON ba.agent_uuid = ssi.ori_uuid 
                LEFT JOIN industry_report ir ON ir.agent_uuid = ssi.ori_uuid and ir.task_uuid = ssi.sr_task_uuid
                left join bd_user bu on bu.user_uuid = ir.user_uuid
                left join sr_surebackup_report ssr on ssr.item_uuid = ssi.item_uuid
                left join bd_task bt on bt.task_uuid = ssi.sr_task_uuid
                WHERE ssi.sr_task_uuid = ? AND ssi.ori_uuid = ? ';

        $where = ' and ir.report_uuid = ssr.report_uuid';
        $preStatus = xphp_get_config('tempagent', 'PREFIX_STATUS');

        $data = $this->dbSelect($sql . $where, [$preStatus['STATUS_SUCCESS'], $jobUuid, $clientUuid]);
        $isReady = true;
        if (empty($data)) {
            // 表明任务还未生成报告。此时前端不能进行任何操作
            $isReady = false;
            $data = $this->dbSelect($sql, [$preStatus['STATUS_SUCCESS'],$jobUuid, $clientUuid]);
            if (empty($data)) {
                return [
                    'code' => 1,
                    'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
                ];
            }
        }

        // 这里需要进行一系列的处理
        $info = $data[0];
        // 解析信息判断是否设置了开机截屏、文件比对和截屏验证
        $description = json_decode($info['description'], true);
        $openFlag = !empty($description['item_open']['show']); // 是否设置开机截屏验证
        $screenFlag = !empty($description['item_screen']['show']); // 是否设置截屏比对验证
        $documentFlag = !empty($description['item_document']['show']); // 是否设置文件比对验证
        $return = [
            'open_flag' => $openFlag,
            'screen_flag' => $screenFlag,
            'document_flag' => $documentFlag,
        ];
        // 开机验证结果
        $openResult = 0; // 等待
        $verifyStatus = xphp_get_config('industry', 'VERIFY_STATUS');
        $statusArr = [
            $info['ping_status'],
            $info['heartbeat_status'],
            $info['screen_status'],
        ];
        $uniqueStatus = array_unique($statusArr);
        if (count($uniqueStatus) == 1 && $uniqueStatus[0] == $verifyStatus['WAITING']) {
            $openResult = 0; // 等待
        } elseif (in_array($verifyStatus['DOING'], $statusArr)) {
            $openResult = 2; // 验证中
        } elseif (count($uniqueStatus) == 1 && $uniqueStatus[0] == $verifyStatus['SUCCESS']) {
            $openResult = 1; // 成功
        } elseif (in_array($verifyStatus['ERROR'], $statusArr)) {
            $openResult = 3; // 错误
        } elseif (
            $info['ping_status'] == $verifyStatus['SUCCESS'] && $info['heartbeat_status'] == $verifyStatus['SUCCESS']
            && $info['screen_status'] == $verifyStatus['SKIP']
        ) {
            $openResult = 1; // 成功
        }
        $return['open_result'] = $openResult; // 开机验证结果

        $fileTotal = 0; // 总数量
        $fileCompareNormal = 0; // 一致数量
        $fileCompareError = 0; // 不一致数量

        if (!empty($info['doc_compare_result_json'])) {
            $filesInfos = json_decode($info['doc_compare_result_json'], true);
            if (is_array($filesInfos)) {
                foreach ($filesInfos as $filesInfo) {
                    if (!empty($filesInfo)) {
                        $fileTotal += $filesInfo['total_compare_count'];
                        $fileCompareNormal += $filesInfo['same_file_count'];
                        $fileCompareError += $filesInfo['abnormal_file_count'] + $filesInfo['diff_file_count'];
                    }
                }
            }
        }

        $return['file_total_num'] = $fileTotal; // 总数量
        $return['file_total_compare_num'] = $fileTotal; // 总对比数量
        $return['file_total_normal_num'] = $fileCompareNormal; // 一致数量
        $return['file_total_error_num'] = $fileCompareError; // 不一致数量

        $filesFlag = 0; // 等待 文件比对状态
        if ($info['doc_consistency_status'] == $verifyStatus['WAITING']) {
            $filesFlag = 0; // 等待
        } elseif ($info['doc_consistency_status'] == $verifyStatus['DOING']) {
            $filesFlag = 2; // 验证中
        } elseif ($info['doc_consistency_status'] == $verifyStatus['SUCCESS']) {
            $filesFlag = 1; // 成功
        } elseif ($info['doc_consistency_status'] == $verifyStatus['ERROR']) {
            $filesFlag = 3; // 错误
        }
        if (empty($info['doc_list_json']) && !in_array($openResult, [0, 2])) {
            // 表示没有设置文件比对-创建任务的时候,并且开机验证不是等待和验证中
            $filesFlag = 1; // 设置为一致
        } elseif ($fileCompareError > 0) {
            // 有不一致的，那么设置状态为错误
            $filesFlag = 3; // 错误
        }
        $return['document_result'] = $filesFlag; // 文件比对验证结果

        $return['screen_result'] = $info['screen_result'] == 2 ? 3 : $info['screen_result']; // 截屏比对验证结果
        if (
            (
                ($openResult == 1 && empty($info['doc_list_json']))
                || $filesFlag == 1
            )
            && $return['screen_result'] == 0
        ) {
            $return['screen_result'] = 2;
        }

        $return['result_result'] = $info['result'] == 2 ? 3 : $info['screen_result']; // 最终验证结果

        $return['item_plan'] = $info['item_plan']; // 验证方案
        $return['item_result'] = $info['item_result']; // 验证结论
        $return['item_host_name'] = $info['hostname']  ?? '--'; // 设备名称
        $return['item_agent_name'] = $info['agent_name']  ?? '--'; // 主机名
        $return['item_agent_uuid'] = $info['id']  ?? '--'; // 设备编号
        $return['item_ip'] = $info['ip']  ?? '--'; // 设备ip
        $return['item_timestamp'] = $info['timestamp'] ?? '--'; // 验证时间点
        $return['user_name'] = $info['user_name']  ?? '--'; // 操作员

        $itemOpenFlag = 0; // 等待
        if ($info['screen_status'] == 5) {
            $itemOpenFlag = 1;
        } elseif ($info['screen_status'] == 4) {
            $itemOpenFlag = 3; // 错误
        } elseif ($info['screen_status'] == 3) {
            $itemOpenFlag = 2; // 跳过
        }
        $return['item_open_flag'] = $itemOpenFlag; // 开机的状态结果

        $itemNetworkFlag = 0; // 等待
        if ($info['ping_status'] == 5) {
            $itemNetworkFlag = 1;
        } elseif ($info['ping_status'] == 4) {
            $itemNetworkFlag = 3; // 错误
        } elseif ($info['ping_status'] == 7) {
            $itemNetworkFlag = 3; // 告警
        }
        $return['item_network_flag'] = $itemNetworkFlag; // 网络的状态结果

        $itemHearthFlag = 0; // 等待
        if ($info['heartbeat_status'] == 5) {
            $itemHearthFlag = 1;
        } elseif ($info['heartbeat_status'] == 4) {
            $itemHearthFlag = 3; // 错误
        }
        $return['item_heartbeat_flag'] = $itemHearthFlag; // 心跳的状态结果

        if ($return['open_result'] == 0 && in_array(3, [$itemOpenFlag, $itemNetworkFlag, $itemHearthFlag])) {
            // 开机验证结果未更新，但是ping，截屏和网络不是等待且有异常
            $return['open_result'] = 3;
        }

        // 复制文件到指定的目录下
        if (!empty($info['screen_shot_path'])) {
            $fix = strtolower(pathinfo($info['screen_shot_path'], PATHINFO_EXTENSION));
            $openUrl = DATA_PATH . '/industry/pdf/' . md5($info['report_uuid']) . '.' . $fix;
            if (!file_exists($openUrl)) {
                $imageData = file_get_contents($info['screen_shot_path']);
                if (file_put_contents($openUrl, $imageData) !== false) {
                    //return "File copied successfully using Base64.";
                } else {
                    //return "Failed to write the destination file.";
                }
            }
            $openUrl = './web_ng/api/data/industry/pdf/' . md5($info['report_uuid']) . '.' . $fix;
        }

        $return['item_open_url'] = $openUrl ?? ''; // 开机截图

        if ($return['item_open_flag'] != 1) {
            // 如果开机状态不是成功，那么开机截图改为空
            $return['item_open_url'] = ''; // 开机截图
        }

        $screenList = []; // 截图比对结果
        $screenTotal = 0; // 总数量
        $screenTotalCompare = 0; // 总对比数量
        $screenCompareNormal = 0; // 一致数量
        $screenCompareError = 0; // 不一致数量
        if (!empty($info['screen_list'])) {
            $screenList = json_decode($info['screen_list'], true);
            $screenTotal = $screenTotalCompare = count($screenList);
            foreach ($screenList as $item) {
                if (intval($item['result']) == 1) {
                    // 一致
                    $screenCompareNormal++;
                }
            }
            $screenCompareError = $screenTotalCompare - $screenCompareNormal;
        }
        $return['screen_list'] = $screenList; // 截图比对列表
        $return['screen_total_num'] = $screenTotal; // 总数量
        $return['screen_total_compare_num'] = $screenTotalCompare; // 总对比数量
        $return['screen_total_normal_num'] = $screenCompareNormal; // 一致数量
        $return['screen_total_error_num'] = $screenCompareError; // 不一致数量

        $timeSpace = xphp_get_config('app', 'TIMESPACE');
        $return['verify_time'] = $info['create_time'] ?? $timeSpace; // 验证日期(任务创建时间)
        $return['start_time'] = $info['start_time'] ?? $timeSpace; // 开始时间
        if ($info['end_time'] == '1970-01-01 08:00:00' || $info['end_time'] == '0000-00-00 00:00:00') {
            $info['end_time'] = '';
        }
        $return['end_time'] = !empty($info['end_time']) ? $info['end_time'] : $timeSpace; // 完成时间
        $return['interval_time'] = $timeSpace; // 持续时间
        if (!empty($info['end_time']) && !empty($info['start_time'])) {
            $return['interval_time'] = xphp_format_duration(
                strtotime($info['start_time']),
                strtotime($info['end_time'])
            );
        } elseif (!empty($info['start_time'])) {
            // 结束时间取当前时间
            $return['interval_time'] = xphp_format_duration(
                strtotime($info['start_time']),
                time()
            );
        }
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        if (
        !in_array(
            $info['task_status'],
            [$taskStatus['RUNNING'], $taskStatus['FINISHED'], $taskStatus['SUCCESSED']]
        )
        ) {
            // 任务不是运行中。完成和成功。都显示 --
            $return['interval_time'] = $timeSpace;
        }

        if (!empty($info['console_url'])) {
            // 把 0.0.0.0 换成当前的域名
            $newconsoleurl = str_replace(
                '0.0.0.0:6080',
                $_SERVER['HTTP_HOST'] . '/web_console',
                $data[0]['console_url']
            );

            $newconsoleurl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($newconsoleurl);
        }

        // 需要根据报告的状态判断下当前设备的状态
        if (
            in_array(
                $info['task_status'],
                [$taskStatus['RUNNING'], $taskStatus['FINISHED'], $taskStatus['SUCCESSED']]
            )
            && $info['status'] == 9 && in_array($info['item_status'], [5, 6])
        ) {
            // 报告未生成，并且设备状态是已完成和成功，需要手动改为进行中
            $info['item_status'] = 2;
        }
        if (in_array($info['task_status'], [$taskStatus['WAITTING'], $taskStatus['STOPPED']])) {
            // 任务状态是停止/等待的，那么对象列表显示未等待
            $info['item_status'] = 1;
        }

        $return['vm_uuid'] = $info['new_uuid']; // 容灾演练主机的uuid
        $return['vnc_url'] = $newconsoleurl ?? ''; // 容灾演练主机的url
        $return['item_status'] = intval($info['item_status']); // 设备验证状态 2是运行中
        $return['report_status'] = intval($info['status']); // 报告生成状态
        $return['report_uuid'] = $info['report_uuid']; // 报告uuid
        $return['is_ready'] = $isReady; // 是否可以截屏操作
        // 组装下 doc_list_json 文件比对目录
        $return['doc_list'] = [];
        if (!empty($info['doc_list_json'])) {
            $docList = json_decode($info['doc_list_json'], true);
            $return['doc_list'] = array_column($docList['doc_list'], 'doc_path');
        }

        if ($info['task_status'] == $taskStatus['STOPPED'] || !$isReady) {
            // 任务是停止状态
            $return['document_result'] = 0;
            $return['file_total_compare_num'] = 0;
            $return['file_total_error_num'] = 0;
            $return['file_total_normal_num'] = 0;
            $return['file_total_num'] = 0;
            $return['item_heartbeat_flag'] = 0;
            $return['item_network_flag'] = 0;
            $return['item_open_flag'] = 0;
            $return['open_result'] = 0;
            $return['result_result'] = 0;
            $return['screen_list'] = [];
            $return['screen_result'] = 0;
            $return['screen_total_compare_num'] = 0;
            $return['screen_total_error_num'] = 0;
            $return['screen_total_normal_num'] = 0;
            $return['screen_total_num'] = 0;
            $return['item_open_url'] = '';
        }
        return [
            'code' => 0,
            'msg' => $return
        ];
    }

    /**
     * 切换设备保存报告的某些信息
     * @param array $params 请求参数
     * @return array
     */
    public function updateClientInfos(array $params)
    {
        (new Report())->checkIsGlobal();

        $sql = 'select report_uuid from industry_report where task_uuid = ? and agent_uuid = ?';
        $sqlParam = [$params['job_uuid'], $params['client_uuid']];
        $data = $this->dbSelect($sql, $sqlParam);
        if (empty($data)) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
            ];
        }
        // 可以修改的哪些字段枚举
        $fields = [
            'item_plan' => 'item_plan',
            'item_result' => 'item_result',
            'screen_list' => 'screen_list',
            'status' => 'status',
        ];
        if (empty($fields[$params['field']])) {
            return [
                'code' => 1,
                'msg' => xphp_get_lang('WEB_PUBLIC_OPRATION_UNKNOWN')
            ];
        }

        $sql = 'update industry_report set ' . $fields[$params['field']] . ' = ?
                where task_uuid = ? and agent_uuid = ?';
        $sqlParam = array_merge([$params['value']], $sqlParam);
        $result = $this->dbExec($sql, $sqlParam);
        if ($result) {
            return [
                'code' => 0,
                'msg' => xphp_get_lang('UI_PUBLIC_OPERATION')
            ];
        }
        return [
            'code' => -1,
            'msg' => xphp_get_lang('UI_PUBLIC_OPERATION')
        ];
    }
}
