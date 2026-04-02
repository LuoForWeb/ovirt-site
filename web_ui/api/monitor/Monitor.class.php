<?php

use xphp\db\Op;

/*******************************************
 ** 消息推送处理类（优化版）
 **
 ** @author       Vinchin
 ** @date         2024-04-11
 ** @version      1.2.0 (优化重试 + 格式兼容)
 ** @copyright    Copyright 2024 vinchin.com
 ********************************************/
class Monitor extends Op {

    // 存储监控进程号
    protected  $storagePid = 0;
    // 报表通知进程号
    protected  $reportPid = 0;
    // Vpdo 实例
    protected  $vpdo;
    // 进程检测命令

    protected $cmd = "ps aux |grep /api/monitor//monitor.php |grep -v grep | awk '{print $2}'";

    public function __construct() {
        parent::__construct();
        if (empty( $this->vpdo)) {
            $this->vpdo = new xphp\db\builder\Vpdo();
        }
    }

    /**
     * 主运行入口（避免构造函数死循环）
     */
    public function run() {
        while (true) {
            $info = [];
            exec( $this->cmd,  $info);
             $info = array_filter( $info);

             $this->writeLog('Main loop: checking processes. Current PIDs: ' . json_encode( $info));

             $settings =  $this->getCheckSettings();

            // 启动报表推送子进程（如果未运行）
            if (!in_array((string) $this->reportPid,  $info) && !empty( $settings)) {
                $this->reportPid = 0;
                 $this->startReportPush();
            }

            // 启动存储监控子进程（如果未运行）
            if (!in_array((string) $this->storagePid,  $info)) {
                $this->storagePid = 0;
                 $this->startMonitor();
            }

            sleep(5);
        }
    }

    /**
     * 根据进程号判断是不是孤儿进程，是的话就全部杀掉
     */
    public function checkOnlyMonitor() {
        $info = [];
        exec( $this->cmd,  $info);
         $info = array_filter( $info);
        if (!empty( $info)) {
            foreach ( $info as  $item) {
                $output = [];
                exec("ps -p  $item -o ppid=",  $output);
                if (!empty( $output[0])) {
                    $ppid = trim( $output[0]);
                    if (!empty( $ppid) &&  $ppid == '1') {
                        exec("kill -9  $item");
                        $this->writeLog("Killed orphan process:  $item");
                    }
                }
            }
        }
        sleep(180);
    }

    /**
     * 读取报告通知配置信息
     */
    public function getCheckSettings() {
        $sql = "SELECT id, report_config, receive_email FROM bd_email_notice WHERE email_notice_type = 2";
         $data =  $this->vpdo->sqlQuery( $sql, [], true);

         $return = [];
        if (!empty( $data)) {
            foreach ( $data as  $item) {
                $config = json_decode( $item['report_config'], true);
                if (!empty( $config) && !empty( $config['reportFlag'])) {
                    $receiveEmail = json_decode( $item['receive_email'], true);
                     $receiveEmail = array_filter( $receiveEmail ?: []);
                    if (!empty( $receiveEmail)) {
                        $config['receiveEmail'] =  $receiveEmail;
                         $return[ $item['id']] =  $config;
                    }
                }
            }
        }
         $this->writeLog('Loaded report settings: ' . print_r( $return, true));
        return  $return;
    }

    /**
     * 开启存储统计监控
     */
    private function startMonitor() {
        $pid = pcntl_fork();
        if ( $pid == -1) {
            die( $this->writeLog("Create storage monitor process failure"));
        } elseif ( $pid) {
            $this->storagePid =  $pid;
             $this->writeLog("Started storage monitor, PID:  $pid");
        } else {
            $this->writeLog("Storage monitor child process started");
            while (true) {
                $this->checkOnlyMonitor();
                 $result =  $this->getStorageMonitor();
                if ( $result == 0) {
                    $this->writeLog("Inserting into bd_storage_monitor for yesterday's data");
                     $date = date('Y-m-d');
                     $this->addStorageMointor( $date);
                }
                sleep(600);
            }
        }
    }

    private function getStorageMonitor() {
        $sql = "SELECT COUNT(*) AS total FROM bd_storage_monitor WHERE `date` = ?";
         $result =  $this->vpdo->sqlQuery( $sql, [date('Y-m-d')], true);
        return intval( $result[0]['total']);
    }

    private function addStorageMointor( $storagedate) {
         $backupDate = date("Y-m-d", strtotime( $storagedate . ' -1 day'));
         $taskType = xphp_get_config('task', 'TASKTYPE');
         $flag = xphp_get_config('app', 'FLAG');

        // 备份
         $sqlBackup = "SELECT SUM(bbt.write_size) AS write_size, SUM(bbt.total_size) AS total_size
                      FROM bd_backup_timepoint bbt
                      JOIN bd_storage_resource bsr ON bsr.storage_uuid = bbt.storage_uuid
                      WHERE bsr.lan_free_flag = ?
                        AND bbt.task_type NOT IN ({$taskType['BACKUP_COPY']}, {$taskType['BACKUP_COPY_FETCH']}, {$taskType['ARCHIVE']}, {$taskType['ARCHIVE_FETCH']})
                        AND DATE(bbt.timepoint) = ?";
         $dataBackup =  $this->vpdo->sqlQuery( $sqlBackup, [ $flag['UNSET'],  $backupDate], true);

        // 复制
         $sqlCopy = "SELECT SUM(bbt.write_size) AS write_size, SUM(bbt.total_size) AS total_size
                    FROM bd_backup_timepoint bbt
                    JOIN bd_storage_resource bsr ON bsr.storage_uuid = bbt.storage_uuid
                    WHERE bsr.lan_free_flag = ?
                      AND bbt.task_type IN ({$taskType['BACKUP_COPY']}, {$taskType['BACKUP_COPY_FETCH']})
                      AND DATE(bbt.timepoint) = ?";
         $dataCopy =  $this->vpdo->sqlQuery( $sqlCopy, [ $flag['UNSET'],  $backupDate], true);

        // 归档
         $sqlArchive = "SELECT SUM(bbt.write_size) AS write_size, SUM(bbt.total_size) AS total_size
                       FROM bd_backup_timepoint bbt
                       JOIN bd_storage_resource bsr ON bsr.storage_uuid = bbt.storage_uuid
                       WHERE bsr.lan_free_flag = ?
                         AND bbt.task_type IN ({$taskType['ARCHIVE']}, {$taskType['ARCHIVE_FETCH']})
                         AND DATE(bbt.timepoint) = ?";
         $dataArchive =  $this->vpdo->sqlQuery( $sqlArchive, [ $flag['UNSET'],  $backupDate], true);

         $params = [
            date('Y-m-d'),
            intval( $dataBackup[0]['total_size'] ?? 0),
            intval( $dataBackup[0]['write_size'] ?? 0),
            intval( $dataCopy[0]['total_size'] ?? 0),
            intval( $dataCopy[0]['write_size'] ?? 0),
            intval( $dataArchive[0]['total_size'] ?? 0),
            intval( $dataArchive[0]['write_size'] ?? 0),
            0, 0, 0, 0, '', ''
        ];

         $sqlInsert = "INSERT INTO bd_storage_monitor (
            date, backup_total_size, backup_write_size, copy_total_size, copy_write_size,
            archive_total_size, archive_write_size, backup_num, backup_success_num,
            vm_num, success_vm_num, details, remarks
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        return  $this->vpdo->newExec( $sqlInsert,  $params, true);
    }

    /**
     * 开启报表通知监控
     */
    private function startReportPush() {
        pcntl_signal(SIGCHLD, SIG_IGN);
        $pid = pcntl_fork();
            if ( $pid == -1) {
            die( $this->writeLog("Create report monitor process failure"));
            } elseif ( $pid) {
            $this->reportPid =  $pid;
                 $this->writeLog("Started report notice monitor, PID:  $pid");
            } else {
            $this->writeLog("Report notice child process started");
                while (true) {
                    $this->checkOnlyMonitor();
                     $settings =  $this->getCheckSettings();
                     $this->sendReportNotice( $settings);
                    sleep(5);
                }
            }
        }

    /**
     * 修复原有逻辑，统一处理send_date和重试机制
     */
    private function calcTimestrategy(array $strategy) {
        $currentDateStr = date('Y-m-d');
        $currentTimestamp = time();
        $weekDay = (int)date("w");
        if ($weekDay == 0) $weekDay = 7;
        $monthDay = (int)date("j");
        $currentYear = date('Y');

        $typeList = [];

        foreach ($strategy as $s) {
            $type = (int)($s['type'] ?? 0);
            $noticeTime = trim($s['notice_time'] ?? '');
            $sendDate = trim($s['send_date'] ?? '');

            // 统一修复：正确判断是否应该今天发送
            $shouldSendToday = false;

            // 各类型日期判断
            switch ($type) {
                case 1: // 日报
                    $shouldSendToday = true;
                    break;

                case 2: // 周报
                    if (empty($s['days'])) continue;
                    $dayList = array_map('intval', (array)($s['days'] ?? []));
                    $shouldSendToday = in_array($weekDay, $dayList);
                    break;

                case 3: // 月报
                    if (empty($s['days'])) continue;
                    $dayList = array_map('intval', (array)($s['days'] ?? []));
                    $shouldSendToday = in_array($monthDay, $dayList);
                    break;

                case 4: // 年报
                    $annualDate = $noticeTime ?: '12-31';
                    $shouldSendToday = (date('m-d') === $annualDate);
                    break;
            }

            // 关键修复：只有在应该今天发送，且今天还没发送过的情况下才发送
            if (!$shouldSendToday) {
                continue;
            }

            if ($sendDate === $currentDateStr) {
                $this->writeLog("类型{$type}：今天已经发送过，跳过");
                continue;
            }

            // 时间判断（如果配置了时间）
            if (!empty($noticeTime) && strpos($noticeTime, ':') !== false) {
                $targetTime = strtotime($currentDateStr . ' ' . $noticeTime);
                if ($targetTime !== false && $currentTimestamp < $targetTime) {
                    $this->writeLog("类型{$type}：未到指定时间 " . $noticeTime);
                    continue;
                }
            }

            $typeList[] = ['flag' => true, 'type' => $type];
        }

        return $typeList;
    }

    /**
     * 系统发送报表通知接口
     */
    private function sendReportNotice(array  $settings) {
        $currentDateStr = date('Y-m-d'); // 👈 在此处定义！
        foreach ( $settings as  $key =>  $item) {
            $strategy =  $item['timeStrategy'];
                 $typeList =  $this->calcTimestrategy( $strategy);
                 $id =  $key;
                 $module =  $item['moudleType'];
                 $receiveEmail =  $item['receiveEmail'];

                if (empty( $typeList)) {
                continue;
            }

             $this->writeLog("Sending reports for ID= $id, types: " . json_encode( $typeList));

             $send_flags =  $this->sendTimeReport( $typeList,  $module,  $id,  $receiveEmail);

            // 更新 send_date
            foreach ( $strategy as  $k =>  $s) {
            if (isset( $send_flags[ $s['type']])) {
                $strategy[ $k]['send_date'] = $send_flags[$s['type']] ?  $currentDateStr : '';
                }
            }

             $item['timeStrategy'] =  $strategy;
             $sql = "UPDATE bd_email_notice SET report_config = ? WHERE id = ?";
             $this->vpdo->newExec( $sql, [json_encode( $item),  $id], true);
        }
    }

    private function sendTimeReport( $typeList,  $module,  $id,  $receiveEmail) {
    $re = [];
        foreach ( $typeList as  $t) {
        if ( $t['flag'] && in_array( $t['type'], [1, 2, 3, 4])) {
            $re[ $t['type']] =  $this->sendReportEmail( $t,  $module,  $id,  $receiveEmail);
            }
        }
        return  $re;
    }

    /**
     * 发送报表邮件（无数据也发）
     */
    public function sendReportEmail( $strategy,  $module,  $id,  $receiveEmail) {
    $report = new app\v1\report\v0\logic\Report();
         $return =  $report->getReportNoticeInfo( $module,  $id,  $strategy['type']);

        if (empty( $return)) {
        $typeName = match( $strategy['type']) {
            1 => '日报',
                2 => '周报',
                3 => '月报',
                4 => '年报',
                default => '报表'
            };
             $return = [
            'title' => "【系统通知】{ $typeName} - " . date('Y-m-d'),
            'content' => "<p>您好，今日 { $typeName} 暂无相关数据。</p>"
        ];
        }

         $notice = new app\v1\system\v0\logic\Notice();
         $params = [
        'title' =>  $return['title'],
            'info' =>  $return['content'],
            'email' =>  $receiveEmail
        ];

         $result =  $notice->sendEmail( $params);
         $success = !empty( $result) && ( $result['code'] ?? -1) == 0;

         $this->writeLog("Send report email: " . ( $success ? 'SUCCESS' : 'FAILED') 
            . ", module={ $module}, type={$strategy['type']}, to=" . json_encode( $receiveEmail));

        return  $success;
    }

    public function __destruct() {
    $this->writeLog("Monitor process stopped.");
    }
}