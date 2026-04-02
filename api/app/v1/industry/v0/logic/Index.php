<?php

namespace app\v1\industry\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\Backup;
use app\v1\system\v0\logic\Time;
use DateTime;

/**
 * note          GMP首页相关
 * @author       wuyihang@vinchin.com
 * @date         2024/11/4 10:32
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class Index extends Base
{
    /**
     * 客户端报告统计信息
     * @param array $params 请求参数
     * @return array
     */
    public function clientReportInfo(array $params): array
    {
        $type = $params['type']; //最近一年‘year’, 最近一月month, 最近一周week
        $where = '';
        $allAgentSql = "SELECT agent_name, agent_uuid FROM bd_agent where agent_type!=3 and agent_type!=5";
        $agent = $this->dbSelect($allAgentSql);
        if ($type == 'year') {
            $where .= "WHERE ssr.end_time >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        } elseif ($type == 'season') {
            //季度
            // 获取当前的年份和月份
            $currentYear = date('Y');
            $currentMonth = date('m');
            // 计算当前季度的开始月份
            $quarterStartMonth = ceil($currentMonth / 3) * 3 - 2;
            // 计算季度开始的年份
            $quarterStartYear = $currentYear;
            if ($quarterStartMonth < 1) {
                $quarterStartMonth += 12;
                $quarterStartYear -= 1;
            }
            // 循环获取当前季度的每个月
            for ($i = 0; $i < 3; $i++) {
                // 计算月份
                $month = $quarterStartMonth + $i;
                // 如果月份超过12，年份加1，月份减12
                if ($month > 12) {
                    $month -= 12;
                    $year = $currentYear + 1;
                } else {
                    $year = $currentYear;
                }
                // 格式化年月
                $yearMonth = $year . '-' . $month;
                // 添加到数组
                $yearMonthArray[] = $yearMonth;
            }
            $startYearMonth = $yearMonthArray[0];
            $endYearMonth = $yearMonthArray[2];

            $where .= "WHERE ssr.end_time >= '{$startYearMonth}-01'
             AND ssr.end_time < '{$endYearMonth}-01' + INTERVAL 1 MONTH ";
        } else {
            $where .= "WHERE ssr.end_time >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
        }

        $sql = "SELECT ba.agent_uuid, ba.agent_name from sr_surebackup_item ssi 
                LEFT JOIN bd_agent ba on ba.agent_uuid = ssi.ori_uuid 
                left join sr_surebackup_report ssr on ssr.item_uuid = ssi.item_uuid ";

        $group = "GROUP BY ba.agent_uuid ";
        $data = $this->dbSelect($sql . $where . $group);
        // if (empty($data)) {
        //     return [];
        // }
        //$nameArr = array_column($agent, 'agent_name');
        $uuidArr = array_column($agent, 'agent_uuid');
        $uuidStr =  implode("','", $uuidArr);
        //只展示设备验证次数数量最多的十台
        $sqlCount  = "select count(ir.report_uuid) as total,ir.agent_uuid, ba.agent_name
                    from sr_surebackup_report ssr,industry_report ir, bd_agent ba
                        where ssr.report_uuid = ir.report_uuid and ba.agent_uuid =  ir.agent_uuid
                          and ir.agent_uuid in ('" . $uuidStr . "')
                           group by ir.agent_uuid order by total desc limit 10 ";

        $count = $this->dbSelect($sqlCount);
        $info['agent_uuid'] = array_column($count, 'agent_uuid');
        $info['agent_name'] = array_column($count, 'agent_name');
        $info['data'] = array_column($count, 'total');
        return $info;
    }

    /**
     * 验证报告统计信息(根据报告结果)
     * @param $params 请求参数
     * @return array
     */
    public function reportSummary($params)
    {
        $allStatus = xphp_get_config('industry', 'REPORT_STATUS');
        $statusCondition = array(
            'archived' => "status={$allStatus['ARCHIVED']}",
            'approving' => "status in ( {$allStatus['APPROVALING']},
             {$allStatus['PENDING']} )",
            'rejected' => "status={$allStatus['REJECTED']}"
        );
        $info = [];
        foreach ($statusCondition as $key => $condition) {
            $sql = "select count(*) as total from industry_report where {$condition}";
            $result = $this->dbSelect($sql);
            $info[$key] = $result[0]['total'];
        }
        return $info;
    }

    /**
     * 验证报告统计信息(根据报告时间)
     * @param array $params 请求参数
     * @return array
     */
    public function getReportCountSummary(array $params): array
    {
        $yearMonthArray = [];
        $monthArr = [];
        $totalArr = []; //总报告数组
        $normalArr = []; //正常报告数组
        $abnormalArr = []; // 异常报告数组

        if ($params['type'] == 'year') {
            // 获取当前的时间戳
            $now = time();
            // 循环最近12个月
            for ($i = 0; $i < 12; $i++) {
                // 计算每个月的时间戳
                $timestamp = $now - $i * 30 * 24 * 60 * 60; // 每个月减少30天，这是一个近似值
                // 格式化年月
                $yearMonth = date('Y-m', $timestamp);
                // 添加到数组
                $yearMonthArray[] = $yearMonth;
            }
            $yearMonthArray = array_reverse($yearMonthArray);

            foreach ($yearMonthArray as $ym) {
                //所有
                $sql = "SELECT count(*) as total
                            FROM industry_report
                           WHERE create_time >= '{$ym}-01' AND create_time < '{$ym}-01' + INTERVAL 1 MONTH
                             AND status != 9";
                $total = $this->dbSelect($sql);
                //正常
                $normalSql = "SELECT count(*) as total
                                FROM industry_report WHERE create_time >= '{$ym}-01'
                                                       AND create_time < '{$ym}-01' + INTERVAL 1 MONTH
                                                       AND result = 1 AND status != 9";
                $normal = $this->dbSelect($normalSql);
                //异常
                $abnormalSql = "SELECT count(*) as total
                                      FROM industry_report
                                    WHERE create_time >= '{$ym}-01' AND create_time < '{$ym}-01' + INTERVAL 1 MONTH
                                      AND result != 1 AND status != 9";
                $abnormal = $this->dbSelect($abnormalSql);
                array_push($totalArr, $total[0]['total']);
                array_push($normalArr, $normal[0]['total']);
                array_push($abnormalArr, $abnormal[0]['total']);
                $month = explode('-', $ym)[1];
                array_push($monthArr, $month);
            }
        } elseif ($params['type'] == 'season') {
            //季度
            // 获取当前的年份和月份
            $currentYear = date('Y');
            $currentMonth = date('m');
            // 计算当前季度的开始月份
            $quarterStartMonth = ceil($currentMonth / 3) * 3 - 2;
            // 计算季度开始的年份
            $quarterStartYear = $currentYear;
            if ($quarterStartMonth < 1) {
                $quarterStartMonth += 12;
                $quarterStartYear -= 1;
            }
            // 循环获取当前季度的每个月
            for ($i = 0; $i < 3; $i++) {
                // 计算月份
                $month = $quarterStartMonth + $i;
                // 如果月份超过12，年份加1，月份减12
                if ($month > 12) {
                    $month -= 12;
                    $year = $currentYear + 1;
                } else {
                    $year = $currentYear;
                }
                // 格式化年月
                $yearMonth = $year . '-' . $month;
                // 添加到数组
                $yearMonthArray[] = $yearMonth;
            }
            foreach ($yearMonthArray as $ym) {
                //所有
                $sql = "SELECT count(*) as total
                            FROM industry_report
                            WHERE create_time >= '{$ym}-01' AND create_time < '{$ym}-01' + INTERVAL 1 MONTH
                              AND status != 9";
                $total = $this->dbSelect($sql);
                //正常
                $normalSql = "SELECT count(*) as total
                                FROM industry_report
                                WHERE create_time >= '{$ym}-01' AND create_time < '{$ym}-01' + INTERVAL 1 MONTH
                                  AND result = 1 AND status != 9";
                $normal = $this->dbSelect($normalSql);
                //异常
                $abnormalSql = "SELECT count(*) as total
                                FROM industry_report
                                WHERE create_time >= '{$ym}-01' AND create_time < '{$ym}-01' + INTERVAL 1 MONTH
                                  AND result != 1 AND status != 9";
                $abnormal = $this->dbSelect($abnormalSql);
                array_push($totalArr, $total[0]['total']);
                array_push($normalArr, $normal[0]['total']);
                array_push($abnormalArr, $abnormal[0]['total']);
                $month = explode('-', $ym)[1];
                array_push($monthArr, $month);
            }
        } elseif ($params['type'] == 'week') {
            //每周
            // 获取当前的时间戳
            $now = time();

            // 循环最近7天
            for ($i = 0; $i < 7; $i++) {
                // 计算每天的时间戳
                $timestamp = $now - $i * 24 * 60 * 60; // 每天减少24小时
                // 格式化日期为 y-m-d
                $date = date('Y-m-d', $timestamp);
                // 添加到数组
                $yearMonthArray[] = $date;
            }
            //将时间按从远到近的顺序排列
            $yearMonthArray = array_reverse($yearMonthArray);

            foreach ($yearMonthArray as $ym) {
                //所有
                $sql = "SELECT count(*) as total
                        FROM industry_report
                        WHERE create_time >= '{$ym} 00:00:00' AND create_time < '{$ym} 23:59:59' AND status != 9";
                $total = $this->dbSelect($sql);
                //正常
                $normalSql = "SELECT count(*) as total
                                FROM industry_report
                                WHERE create_time >= '{$ym} 00:00:00' AND create_time < '{$ym} 23:59:59'
                                  AND status != 9 AND result = 1";
                $normal = $this->dbSelect($normalSql);
                //异常
                $abnormalSql = "SELECT count(*) as total
                                    FROM industry_report
                                    WHERE create_time >= '{$ym} 00:00:00' AND create_time < '{$ym} 23:59:59'
                                      AND status != 9 AND result != 1";
                $abnormal = $this->dbSelect($abnormalSql);
                array_push($totalArr, $total[0]['total']);
                array_push($normalArr, $normal[0]['total']);
                array_push($abnormalArr, $abnormal[0]['total']);
            }
        }

        return [
            'date' => $yearMonthArray,
            'total' => $totalArr,
            'normal' => $normalArr,
            'abnormal' => $abnormalArr,
            'month' => $monthArr
        ];
    }

    /**
     * 设备统计信息
     * @param array $params 请求参数
     * @return array
     */
    public function getDeviceSummary($params)
    {
        $sql = 'select count(*) as total from bd_agent where agent_type != 3 and agent_type != 5';
        $total = $this->dbSelect($sql);
        $total = $total[0]['total'];
        
        $backedSql = '
            SELECT COUNT(DISTINCT ba.agent_uuid) AS total
            FROM bd_agent ba
            INNER JOIN (
                SELECT agent_uuid FROM fs_backup_timepoint
                UNION
                SELECT agent_uuid FROM db_backup_timepoint
                UNION
                SELECT agent_uuid FROM os_backup_timepoint
            ) AS all_backups ON ba.agent_uuid = all_backups.agent_uuid
            WHERE ba.agent_type NOT IN (3, 5)
        ';
        $backedCount = $this->dbSelect($backedSql);
        $backedCount = $backedCount[0]['total']; //已备份的设备数量，也是可验证设备数
        $notBackedCount = $total - $backedCount; //未备份的设备数量

        // 已验证判断的是bd_backup_timepoint表中verify_flag字段  1 未验证 2 验证成功 3 验证告警
        $verified = "select count(distinct obt.agent_uuid) as total from os_backup_timepoint obt 
        left join bd_agent ba on obt.agent_uuid = ba.agent_uuid
        left join bd_backup_timepoint bbt on obt.timepoint_uuid = bbt.timepoint_uuid 
        where ba.agent_type != 3 and ba.agent_type != 5 
        and bbt.verify_flag = 2 or bbt.verify_flag = 3 and ba.agent_uuid is not null";
        $verifiedCount = $this->dbSelect($verified);
        $verifiedCount = $verifiedCount[0]['total']; //已验证的设备数量
        $notVerifiedCount = $backedCount - $verifiedCount; //未验证的设备数量

        $info = [];
        $info['total'] = $total ?? 0;
        $info['backed'] = $backedCount ?? 0;
        $info['not_backed'] = $notBackedCount ?? 0;
        $info['verified'] = $verifiedCount ?? 0;
        $info['not_verified'] = $notVerifiedCount ?? 0;
        $planArr = $this->getPlanByTimes();
        if ($planArr['total_plan'] == 0) {
            // 那么计算去年的
            $planArr = $this->getPlanByTimes(
                strtotime(date('Y') - 1 . '-01-01 00:00:00'),
                strtotime(date('Y') - 1 . '-12-31 23:59:59')
            );
            // 那么未验证的应该是总的
            $planArr['remaining'] = $planArr['total_plan'];
            $planArr['verified'] = 0;
        }
        //获取本年度验证计划
        $info['year_plan'] = $planArr['total_plan'];
        //获取今年已经验证的任务数
        $info['year_history'] = $planArr['verified'];
        //获取今年未验证的任务数
        $info['year_not_verify'] = $planArr['remaining'];
        return $info;
    }

    /**
     * 统计消息的数量信息
     * @param array $params 请求参数
     * @return array
     */
    public function getMessageCount($params = [])
    {

        $userInfo = xphp_get_user_info();
        $user = $userInfo['userUuid'];
        $reportStatus = xphp_get_config('industry', 'REPORT_STATUS');
        $table = 'industry_report ir';
        // 待生成不查
        $where = ' where ir.status != ' . $reportStatus['GENERATED'];
        $sqlParams = [];
        // 这里需要查询出用户所在的用户组，可能是一个数组，需要用 in
        $userGroup = $this->dbSelect(
            "select user_group_uuid from mt_user_user_group where user_uuid = ?",
            [$user]
        );
        $userGroupArr = array_column($userGroup, 'user_group_uuid');
        // 如果不是全局观察者或者超级管理员，只能查看对应的
        if (v1_auth_need_check_look()) {
            $whereor = '';
            foreach ($userGroupArr as $item) {
                $whereor .= " or ir.approval_user_uuid like  '%,{$item},%' ";
            }
            $where .= " and ( ir.user_uuid = ? or ir.approval_user_uuid like ? {$whereor})";
            $sqlParams = [$user, '%,' . $user . ',%'];
        }
        $statusArr = [
            // 待审批，审批中 ，已归档，已驳回，已撤回 总数
            'pending' => 0, 'approving' => 0, 'archived' => 0, 'rejected' => 0, 'revoke' => 0, 'total' => 0
        ];
        $sqlCount = "select
        CASE status
        WHEN 0 THEN 'pending'
        WHEN 1 THEN 'archived'
        WHEN 2 THEN 'approving'
        WHEN 3 THEN 'rejected'
        WHEN 4 THEN 'revoke'
        ELSE 'unknown'
    END as status_name,
       status,count(*) as total from {$table} {$where} group by status";

        $list = $this->dbSelect($sqlCount, $sqlParams);

        $total = array_sum(array_column($list, 'total'));
        foreach ($list as $item) {
            $statusArr[$item['status_name']] = $item['total'];
        }
        $statusArr['total'] = $total;
        return $statusArr;
    }

    /**
     * 获取一段时间内的验证总计划次数
     * 1、先计算指定时间的：根据当前时间和任务运行策略，统计任务未来一年将要运行的次数
     * 2、再加上已经验证的次数（产生的报告数量）
     *
     * @param int $startTime 开始时间
     * @param int $endTime   结束时间
     * @return array 包含总计划次数和已验证次数的数组
     */
    private function getPlanByTimes(int $startTime = 0, int $endTime = 0)
    {
        if ($startTime <= 0) {
            // 默认为今年开始时间
            $startTime = strtotime(date('Y') . '-01-01');
        }
        if ($endTime <= 0) {
            // 默认为今年结束时间
            $endTime = strtotime(date('Y') . '-12-31');
        }

        $taskType = xphp_get_config('task', 'TASKTYPE');

        $sql = "SELECT strategy_type, days, start_time
            FROM bd_time_strategy
            WHERE strategy_id IN (
                SELECT strategy_id FROM bd_task WHERE task_type = {$taskType['SURE_BACKUP']}
            )";
        $strategy = $this->dbSelect($sql);
        $total = 0;

        if (!empty($strategy)) {
            // 处理时间策略
            // 获取日期部分（去掉时间）
            $endDay = date('Y-m-d', $endTime);

            // 计算相差天数
            $endDate = new DateTime($endDay);

            // 当前日期和时间
            $currentDate = new DateTime();
            $currentTime = date('H:i:s');

            if ($currentDate > $endDate) {
                // 如果当前日期大于结束日期，那么未来会执行的次数为0
                $strategy = [];
            }

            foreach ($strategy as $item) {
                $strategyStartTime = $item['start_time'];
                $daysPattern = $item['days'];

                switch ($item['strategy_type']) {
                    case 1:
                        // 按天执行 - 1111111 格式，每天执行
                        $total += $this->calculateDailySchedule($endDate, $strategyStartTime, $currentDate, $currentTime);
                        break;

                    case 2:
                        // 按周执行 - 1000100 格式，7位分别表示周一到周日
                        $total += $this->calculateWeeklySchedule($endDate, $daysPattern, $strategyStartTime, $currentDate, $currentTime);
                        break;

                    case 3:
                        // 按月执行 - 二进制格式，32位分别表示每月的1-31日
                        $total += $this->calculateMonthlySchedule($endDate, $daysPattern, $strategyStartTime, $currentDate, $currentTime);
                        break;
                }
            }
        }

        // 获取已验证的次数
        $verifiedCount = $this->getVerifiedCount($startTime, $endTime);

        return [
            'total_plan' => $total + $verifiedCount,
            'verified' => $verifiedCount,
            'remaining' => $total
        ];
    }

    /**
     * 计算按天执行的计划次数
     * @param DateTime $endDate           结束日期
     * @param string   $strategyStartTime 策略开始时间 H:i:s
     * @param DateTime $currentDate       当前日期
     * @param string   $currentTime       当前时间 H:i:s
     * @return int
     */
    private function calculateDailySchedule(DateTime $endDate, string $strategyStartTime, DateTime $currentDate, string $currentTime): int
    {
        $interval = $currentDate->diff($endDate);
        $totalDays = $interval->days + 1; // 包含开始和结束日期

        // 如果当前时间已经过了策略开始时间，今天就不算了
        if ($currentTime > $strategyStartTime) {
            $totalDays--;
        }

        return $totalDays;
    }

    /**
     * 计算按周执行的计划次数
     * @param DateTime $endDate            结束日期
     * @param string   $daysPattern        策略
     * @param string   $strategyStartTime  策略开始时间 H:i:s
     * @param DateTime $currentDate        当前日期
     * @param string   $currentTime        当前时间 H:i:s
     * @return int
     */
    private function calculateWeeklySchedule(DateTime $endDate, string $daysPattern, string $strategyStartTime, DateTime $currentDate, string $currentTime): int
    {
        $count = 0;
        $patternLength = strlen($daysPattern);

        // 确保模式长度为7（一周7天）
        if ($patternLength < 7) {
            $daysPattern = str_pad($daysPattern, 7, '0', STR_PAD_LEFT);
        } elseif ($patternLength > 7) {
            $daysPattern = substr($daysPattern, 0, 7);
        }

        // 将字符串转换为数组，索引0对应周一，6对应周日
        $weekDays = str_split($daysPattern);

        // 创建当前日期的副本
        $currentDay = clone $currentDate;

        // 遍历从开始到结束的每一天
        while ($currentDay <= $endDate) {
            $dayOfWeek = (int)$currentDay->format('N') - 1; // 转换为0-6的索引
            // 如果这一天需要执行
            if (isset($weekDays[$dayOfWeek]) && $weekDays[$dayOfWeek] == '1') {
                if ($currentDay == $currentDate) {
                    // 今天，需要检查时间
                    if ($currentTime <= $strategyStartTime) {
                        $count++;
                    }
                } else {
                    // 未来的日子都算
                    $count++;
                }
            }

            $currentDay->modify('+1 day');
        }

        return $count;
    }

    /**
     * 计算按月执行的计划次数
     * @param DateTime $endDate            结束日期
     * @param string   $daysPattern        策略
     * @param string   $strategyStartTime  策略开始时间 H:i:s
     * @param DateTime $currentDate        当前日期
     * @param string   $currentTime        当前时间 H:i:s
     * @return int
     */
    private function calculateMonthlySchedule(DateTime $endDate, string $daysPattern, string $strategyStartTime, DateTime $currentDate, string $currentTime): int
    {
        $count = 0;
        $patternLength = strlen($daysPattern);

        // 确保模式长度为31（每月的天数）
        if ($patternLength < 31) {
            $daysPattern = str_pad($daysPattern, 31, '0', STR_PAD_LEFT);
        } elseif ($patternLength > 31) {
            $daysPattern = substr($daysPattern, 0, 31);
        }

        // 将字符串转换为数组，索引0对应1号，30对应31号
        $monthDays = str_split($daysPattern);

        // 创建当前日期的副本
        $currentDay = clone $currentDate;

        // 遍历从开始到结束的每一天
        while ($currentDay <= $endDate) {
            $dayOfMonth = (int)$currentDay->format('j') - 1; // 转换为0-30的索引
            // 如果这一天需要执行
            if (isset($monthDays[$dayOfMonth]) && $monthDays[$dayOfMonth] == '1') {
                if ($currentDay == $currentDate) {
                    // 今天，需要检查时间
                    if ($currentTime <= $strategyStartTime) {
                        $count++;
                    }
                } else {
                    // 未来的日子都算
                    $count++;
                }
            }

            $currentDay->modify('+1 day');
        }

        return $count;
    }

    /**
     * 获取已验证的次数
     */
    private function getVerifiedCount(int $startTime, int $endTime): int
    {
        // 这里需要查询你的验证报告表，统计在时间范围内的报告数量
        $sql = "SELECT count(*) count
                    from industry_report
                    where status != 9 and
                     report_time between FROM_UNIXTIME({$startTime}) and FROM_UNIXTIME({$endTime})";

        $result = $this->dbSelect($sql);
        return $result ? (int)$result[0]['count'] : 0;
    }
}
