<?php

namespace app\v1\common\logic;

use app\v1\copy\v0\logic\CopyBackUp;
use app\v1\dbcdp\v0\logic\DbcdpJobInfo;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\filecopy\v0\logic\FileCopyJobInfo;
use app\v1\k8s\v0\logic\K8sJobInfo;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\logic\Time;
use app\v1\user\v0\logic\User;
use app\v1\volcdp\v0\logic\VolcdpJobInfo;
use app\v1\common\logic\Backup;

/**
 * note          一些公共任务信息方法
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/12 16:39
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class JobInfo extends Base
{
    /**
     * 公共方法
     * 得到标记图标,即WMYF的标记  表示GFS的W周,M月,Y年,F星标
     * 传入参数都为布尔值
     * @param boolean $wflag 保留周标记
     * @param boolean $mflag 保留月标记
     * @param boolean $yflag 保留年标记
     * @param boolean $fflag 永久保留标记
     * @return string
     */
    public function pGetTimepointMark($wflag, $mflag, $yflag, $fflag): string
    {
        $markStr = '';
        if ($wflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_WEEK_POINT') . '" 
                            class="viconfont vicon-remark-week"></i>';
        }
        if ($mflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_MONTH_POINT') . '" 
                        class="viconfont vicon-remark-month"></i>';
        }
        if ($yflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_YEAR_POINT') . '" 
                        class="viconfont vicon-remark-year"></i>';
        }
        if ($fflag) {
            $markStr .= '<i title="' . xphp_get_lang('WEB_VM_GFS_FOREVER_POINT') . '" 
                        class="viconfont vicon-remark-forever"></i>';
        }
        return $markStr;
    }

    /**
     * 公共方法
     * 获取对应功能模块的子模块号
     * @param string $taskuuid 任务唯一标识
     * @param int    $type     任务模块类型
     * @param int    $taskType 任务类型
     * @return number  返回子模块号
     * @author luokai@vinchin.com
     */
    public function pGetTaskSubmodule(string $taskuuid, int $type, int $taskType)
    {
        $subModule = 0;
        $moduleType = xphp_get_config('module', 'MODULE_TYPE');
        switch ($type) {
            case $moduleType['VM']:
                $subModule = $this->getVMTaskHypervisor($taskuuid);
                break;
            case $moduleType['DB']:
                $subModule = $this->getDBTaskHypervisor($taskuuid);
                break;
            case $moduleType['BACKUP_COPY_CLIENT']:
                //副本|归档
                $tasktype = xphp_get_config('task', 'TASKTYPE');

                $copyarchivesubtype = xphp_get_config('task', 'COPY_ARCHIVE_SUB_TYPE');

                if ($taskType == $tasktype['BACKUP_COPY'] || $taskType == $tasktype['BACKUP_COPY_FETCH']) {
                    $subModule = $copyarchivesubtype['BACKUP_COPY_TYPE_VM_BACKUP_COPY'];  //虚拟机副本子模块
                } elseif (
                    $taskType == $tasktype['FILE_BACKUP_COPY'] || $taskType == $tasktype['FILE_BACKUP_COPY_FETCH']
                ) {
                    $subModule = $copyarchivesubtype['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  //文件副本子模块
                } elseif ($taskType == $tasktype['DB_BACKUP_COPY'] || $taskType == $tasktype['DB_BACKUP_COPY_FETCH']) {
                    $subModule = $copyarchivesubtype['BACKUP_COPY_TYPE_DB_BACKUP_COPY'];  //数据库副本子模块
                } elseif (
                    $taskType == $tasktype['NAS_BACKUP_COPY'] || $taskType == $tasktype['NAS_BACKUP_COPY_FETCH']
                ) {
                    $subModule = $copyarchivesubtype['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  //nas副本子模块
                } elseif ($taskType == $tasktype['ARCHIVE'] || $taskType == $tasktype['ARCHIVE_FETCH']) {
                    //虚拟机归档
                    $subModule = $copyarchivesubtype['BACKUP_COPY_TYPE_VM_ARCHIVE'];
                }
                break;
            case $moduleType['OS']:
            // no break
            case $moduleType['VDDT_SERVER']:
            // no break
            default:
                break;
        }

        return $subModule;
    }

    /**
     * 根据备份任务时间策略得到当前任务对应的备份模式按钮
     * @param string  $taskuuid 任务uuid
     * @param boolean $type     是否添加启用策略按钮flag 8
     * @param int     $dbType   任务类型
     * @return array:
     */
    private function getTaskStartMode($taskuuid, $type, $dbType)
    {
        /**
         * 1 启动任务/镜像副本
         * 2 停止
         * 3 修改
         * 4 删除
         * 5 暂停
         * 6 启动差异
         * 7 启动增量/增量副本
         * 8 启用策略
         * 9 迁移
         * 10 启动完备/完整副本
         * 11 启动接管
         * 12 停止接管
         * 13 启动(归档)日志备份
         * 14 启动回切
         * 15 停止接管
         * 16 创建标签点
         * 41 停止回切
         */
        //根据任务uuid得到时间策略，然后根据时间策略类型得到返回的操作码
        //1.只有完备，可以同时又增量和差异 7 6
        //2.又增量或差异就只能启动增量或差异7/6
        //此方法现在只处理模块相关不展示的操作，需要展示但是置灰的操作在js处理
        $taskControl = xphp_get_config('task', 'TASK_CONTROL');
        $sql = "select bts.mode,bt.module_type from bd_task bt, bd_time_strategy bts where 
    			bt.strategy_id =  bts.strategy_id and bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $startMode = array();
        $modeInfo = array();
        foreach ($data as $d) {
            $modeInfo[] = $d['mode'];
        }
        //如果type为true 加上启用策略按钮8
        if ($type) {
            $startMode = array(8);
        }
        $modeType = xphp_get_config('task', 'BACKUP_MODE');

        if ($data[0]['module_type'] == xphp_get_config('module', 'MODULE_TYPE')['M365']) {//exchange不显示差异
            array_push($startMode, 10, 7);
        } else {
            array_push($startMode, 10, 7, 6);
        }

        if ($type == xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']) {  // 数据库备份
            $allDbType = xphp_get_config('db', 'DB_TYPE');
            $sql = "SELECT multi_task_flag, depend_task_uuid FROM db_task WHERE task_uuid = ? ";
            $dbTaskData = $this->dbSelect($sql, [$taskuuid]);
            switch ($dbType) {
                case $allDbType['SQLSERVER']:
                    $startMode = [
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'],
                        $taskControl['START_DIFF'],
                        $taskControl['LOG_BACKUP'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE']
                    ];
                    break;
                case $allDbType['DM']:
                    $startMode = [
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'],
                        $taskControl['START_INCR'],
                        $taskControl['START_DIFF'],
                        $taskControl['LOG_BACKUP'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE']
                    ];
                    break;
                case $allDbType['SAPHANA']:
                    $startMode = [
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'],
                        $taskControl['START_INCR'],
                        $taskControl['START_DIFF'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE']
                    ];
                    break;
                case $allDbType['ORACLE']:
                    $startMode = [
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'],
                        $taskControl['START_INCR'],
                        $taskControl['START_DIFF'],
                        $taskControl['LOG_BACKUP'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE']
                    ];
                    if (v1_parse_flag_to_bool($dbTaskData[0]['multi_task_flag'])) {  // 多任务(日志备份)不显示完全、增量、差异
                        $startMode = [
                            $taskControl['START_STRATEGY'],
                            $taskControl['LOG_BACKUP'],
                            $taskControl['STOP'],
                            $taskControl['MODIFY'],
                            $taskControl['DELETE']
                        ];
                    }
                    break;
                case $allDbType['MYSQL']:
                case $allDbType['MARIA']:
                    $startMode = [
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'],
                        $taskControl['START_INCR'],
                        $taskControl['LOG_BACKUP'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE']
                    ];
                    break;
                case $allDbType['POSTGRE']:
                case $allDbType['ANTDB']:
                case $allDbType['KINGBASE']:
                case $allDbType['UXDB']:
                case $allDbType['HIGHGO']:
                case $allDbType['OPENGAUSS']:
                case $allDbType['VASTBASE']:
                    $startMode = [
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'],
                        $taskControl['LOG_BACKUP'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE']
                    ];
                    break;
                case $allDbType['TIDB']:
                    $startMode = [
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE']
                    ];
                    if ($dbTaskData[0]['depend_task_uuid']) {
                        $startMode = [
                            $taskControl['STOP'],
                            $taskControl['DELETE']
                        ];
                    }
                    break;
                case $allDbType['MONGODB']:
                    $startMode = [
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'],
                        $taskControl['START_INCR'],
                        $taskControl['START_DIFF'],
                        $taskControl['LOG_BACKUP'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE']
                    ];
                    break;
                default:
                    break;
            }
        }
        return $startMode;
    }

    /**
     * 获取数据库恢复的控制操作码
     * @param mixed $taskUuid 任务uuid
     * @return array
     */
    private function getDBRecoveryControlOpCode($taskUuid)
    {
        $allDbRecoveryType = xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db');
        $allTaskControl = xphp_get_config('task', 'TASK_CONTROL');
        $sql = "SELECT recovery_type FROM bd_task WHERE task_uuid = ? ";
        $taskData = $this->dbSelect($sql, [$taskUuid]);
        if (!is_array($taskData) || $taskData[0]['recovery_type'] == $allDbRecoveryType['SPECIFIC_TIMEPOINT']) {  // 指定时间点
            return [
                $allTaskControl['START'],
                $allTaskControl['STOP'],
                $allTaskControl['DELETE'],
            ];
        }
        return [
            $allTaskControl['START_STRATEGY'],
            $allTaskControl['START'],
            $allTaskControl['STOP'],
            $allTaskControl['MODIFY'],
            $allTaskControl['DELETE'],
        ];
    }

    /**
     * 根据任务状态得到任务能够进行的操作码数组 公共方法 
     * @param int    $tasktype      类型
     * @param int    $status        状态
     * @param string $tasktypealias dbcdp的操作码sql子查询
     * @param string $taskuuid      任务id
     * @param int    $dbType        数据库类别
     * @param int    $devType       区分实时整机和卷 1：卷 2：整机
     * @return array 启动1
     * 停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切(新增)/15 停止回切(新增)/16创建标签点
     * @modify 将任务控制数字定义移植到配置文件中,读取Array TASK_CONTROL中对应的元素
     */
    protected function getTaskOpCodeByType($tasktype, $status, $tasktypealias = '', $taskuuid = '', $dbType = 0, $devType = 0): array
    {
        if ($status == xphp_get_config('task', 'TASKSTATUS')['DELETING']) {
            // 删除中
            return [xphp_get_config('task', 'TASK_CONTROL')['FORCE_DELETE']];
        }

        if ($status == xphp_get_config('task', 'TASKSTATUS')['CLEANING']) {
            // 清理中
            return [xphp_get_config('task', 'TASK_CONTROL')['FORCE_STOP']];
        }

        $mode = $this->getTaskStartMode($taskuuid, $tasktype, $dbType);
        $task = xphp_get_config('task');
        $taskTypeConf = $task['TASKTYPE'];
        $taskControl = $task['TASK_CONTROL'];

        switch ($tasktype) {
            case $taskTypeConf['BACKUP']:
                $opCode = array(
                    // $taskControl['START_STRATEGY'],
                    // $taskControl['START_FULL'],
                    // $taskControl['START_INCR'],
                    // $taskControl['START_DIFF'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE'],
                );
                $opCode = array_merge($mode, $opCode);
                break;
            case $taskTypeConf['VM_FILE_RECOVERY']:
            case $taskTypeConf['ARCHIVE_FETCH']:
            case $taskTypeConf['DB_CDP_RECOVERY']:
            case $taskTypeConf['FILE_CDP_RECOVERY']:
            case $taskTypeConf['RECOVERY']:
            case $taskTypeConf['BACKUP_COPY_FETCH']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['DB_RECOVERY']:
            case $taskTypeConf['DRILL']:
                $opCode = $this->getDBRecoveryControlOpCode($taskuuid);
                break;
            case $taskTypeConf['VM_INSTANT_RECOVERY']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['MIGRATION'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['VM_INSTANT_RECOVERY_MOTION']:
                $opCode = array(
                    $taskControl['STOP'],
                );
                break;
            case $taskTypeConf['FILE_CDP_BACKUP']:
            case $taskTypeConf['VM_CDP_BACKUP']:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['BACKUP_COPY']:
                $sql = "select copy_mode from copy_task where task_uuid = ?";
                $data = $this->dbSelect($sql, array($taskuuid));
                if (intval($data[0]['copy_mode']) == 2) {
                    $opCode = array(
                        $taskControl['START_STRATEGY'],
                        $taskControl['START_FULL'], // 完整副本
                        $taskControl['START_INCR'], // 增量副本
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE'],
                    );
                } else {
                    $opCode = array(
                        $taskControl['START_STRATEGY'],
                        $taskControl['START'], // 镜像副本
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE'],
                    );
                }
                break;
            case $taskTypeConf['ARCHIVE']:
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['DB_CDP_BACKUP']:
                $opCode = $this->getDbCdpOpCode($tasktypealias, $status);
                break;
            case $taskTypeConf['DB_BACKUP']:
                $opCode = $mode;
                break;
            case $taskTypeConf['VOL_CDP_BACKUP']: //卷CDP暂时添加上述可控制项
            case $taskTypeConf['VOL_CDP_REPLICATION']: // 整机复制
                if ($status == xphp_get_config('task', 'TASKSTATUS')['ERROR']) {
                    // 整机复制/实时备份任务出错时任务本身就已经处于停止状态，无需再执行停止操作
                    $opCode = array(
                        $taskControl['START'],
                        $taskControl['DELETE']
                    );
                } else {
                    if ($devType == 1) { // 实时卷没有修改操作
                        $opCode = array(
                            $taskControl['START'],
                            $taskControl['STOP'],
                            $taskControl['DELETE'],
                            $taskControl['START_TAKEOVER'],
                            $taskControl['STOP_TAKEOVER'],
                            $taskControl['START_FAILBACK'],
                            $taskControl['AUTO_TAKEOVER'],
                        );
                    } else {
                        $opCode = array(
                            $taskControl['START'],
                            $taskControl['STOP'],
                            $taskControl['MODIFY'],
                            $taskControl['DELETE'],
                            $taskControl['START_TAKEOVER'],
                            $taskControl['STOP_TAKEOVER'],
                            $taskControl['START_FAILBACK'],
                            $taskControl['AUTO_TAKEOVER'],
                        );
                    }
                }
                break;
            case $taskTypeConf['VOL_CDP_RECOVERY']: //卷CDP恢复添加上述可控制项
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    //                     $taskControl['MODIFY'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['VOL_CDP_TAKEOVER']: //手动接管添加上述可控制项
                $opCode = array(
                    $taskControl['START_TAKEOVER'],
                    $taskControl['STOP_TAKEOVER'],
                    $taskControl['START_FAILBACK'],
                    //                     $taskControl['MODIFY'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['OS_BACKUP']: //OS备份
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START_FULL'],
                    $taskControl['START_INCR'],
                    $taskControl['START_DIFF'],
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['OS_RECOVERY']: //OS恢复
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['OS_INSTANT_RECOVERY']: //OS瞬时恢复
            case $taskTypeConf['INSTANT_RECOVERY']: //vm瞬时恢复
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['MIGRATION'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['OS_INSTANT_RECOVERY_MOTION']: //OS迁移
            case $taskTypeConf['INSTANT_RECOVERY_MOTION']: // 迁移
                $opCode = array(
                    $taskControl['STOP_MOTION'],
                    $taskControl['FINISH_MOTION'],
                );
                break;
            case $taskTypeConf['SURE_BACKUP']: //数据验证
                //获取备份后验证标记
                $sql = "select surebackup_task_type from sr_surebackup where sr_task_uuid = ?";
                $data = $this->dbSelect($sql, array($taskuuid));
                //备份后验证去掉修改
                if (intval($data[0]['surebackup_task_type']) == 4) {
                    $opCode = array(
                        $taskControl['START_STRATEGY'],
                        $taskControl['START'],
                        $taskControl['STOP'],
                        $taskControl['DELETE'],
                    );
                } else {
                    $opCode = array(
                        $taskControl['START_STRATEGY'],
                        $taskControl['START'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE'],
                    );
                }
                break;
            case $taskTypeConf['VM_HUAWEI_CBR_SYNC']:
                //在bd_task中通过task_id去查找strategy_id 再通过getTimeStrategyInfo找到是否按策略同步
                $sql = "select strategy_id from bd_task where task_uuid = ?";
                $data = $this->dbSelect($sql, array($taskuuid));
                $strategyid = $data[0]['strategy_id'];
                // $vmHandler = Xphp::instance('Vmhandler');
                // $timestrategy  = $vmHandler->getTimeStrategyInfo($strategyid);
                $timestrategy = $this->getTimeStrategyInfo($strategyid);
                if ($timestrategy['type'] == 'oncetime') {
                    $opCode = array(
                        $taskControl['START'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE'],
                    );
                } else {
                    $opCode = array(
                        $taskControl['START_STRATEGY'],
                        $taskControl['START'],
                        $taskControl['STOP'],
                        $taskControl['MODIFY'],
                        $taskControl['DELETE'],
                    );
                }
                break;
            case $taskTypeConf['CDP_DB_BACKUP']: //数据库实时同步
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['PAUSE'],
                    $taskControl['START_TAKEOVER'],
                    $taskControl['STOP_TAKEOVER'],
                    $taskControl['STOP_FAILBACK'],
                    $taskControl['START_FAILBACK'],
                    $taskControl['DELETE'],
                    $taskControl['MODIFY'],
                );
                break;
            case $taskTypeConf['CDP_DB_RECOVERY']: //数据库实时恢复
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['PLATFORM_RECOVERY']: // 跨平台恢复
            case $taskTypeConf['GRAIN_RECOVERY']: // 细粒度恢复
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
            case $taskTypeConf['KUBE_BACKUP']: //k8s备份
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START_FULL'], // 完整副本
                    $taskControl['START_INCR'], // 增量副本
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['KUBE_RECOVERY']: //k8恢复
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],

                );
                break;
            case $taskTypeConf['FILE_COPY']: //文件复制
            case $taskTypeConf['FILE_COMPARE']: //文件对比
                $opCode = array(
                    $taskControl['START_STRATEGY'],
                    $taskControl['START_COPY'],
                    $taskControl['START_COMPARE'],
                    // $taskControl['PAUSE'],//后台还没做暂停 先注释
                    $taskControl['STOP'],
                    $taskControl['MODIFY'],
                    $taskControl['DELETE'],

                );
                break;
            default:
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE'],
                );
                break;
        }
        return $opCode ?? [];
    }

    /**
     * 检测任务状态
     * 有运行中的任务和瞬时恢复的任务时返回失败
     * @param $operate opreate
     * @return bool|json
     */
    public function checkTaskStatus($operate)
    {
        $sql = "select id from bd_task where task_status = ?  and delete_flag = ?";
        $sqlParams = array(xphp_get_config('task', 'TASKSTATUS')['RUNNING'], xphp_get_config('app', 'FLAG')['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        if ($data[0]) {
            return $this->muOpResult(false, $operate, xphp_get_lang('WEB_SYSTEM_SETTING_IP_HAS_TASK'), 'warning');
        }
        return true;
    }

    /**
     * 得到当前任务中每个任务的进度,public方法，其他地方也调用，如大屏
     * @param array $d d
     * @return string $progress
     */
    public function getCurrentTaskProgress(array $d): string
    {
        $taskType = intval($d['task_type']);
        $progress = $this->getTaskTotalProgress(
            $d['task_status'],
            $d['total_object_size'],
            $d['total_object_completed_size'],
            false,
            $d['task_type']
        );
        //数据验证任务单独获取状态
        $tasktypes = xphp_get_config('task', 'TASKTYPE');
        if ($taskType == $tasktypes['SURE_BACKUP']) {
            $progress = $this->getSureBackupProgress($d['task_status'], intval($d['task_progress']), false);
        }

        //卷CDP备份和恢复 整机复制
        if (
            $taskType == $tasktypes['VOL_CDP_BACKUP'] || $taskType == $tasktypes['VOL_CDP_RECOVERY'] ||
            $taskType == $tasktypes['VOL_CDP_TAKEOVER'] || $taskType == $tasktypes['VOL_CDP_REPLICATION']
        ) {
            $volCdpHandler = new VolcdpJobInfo();

            $volCdpDes = xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE');

            $taskRunningStage = $d['task_type_alias'];

            $currentSizeValue = $volCdpHandler->getTaskTotalCapacityInfo($d['task_uuid']);

            $progress = $this->getTaskTotalProgress(
                $d['task_status'],
                $d['total_object_size'],
                $currentSizeValue,
                false,
                $d['task_type']
            );

            // 任务阶段进入实时阶段，显示“--”，一致性校验阶段需要显示进度
            if (
                $taskRunningStage == $volCdpDes['REALTIME_SYNC'] ||
                $taskRunningStage == $volCdpDes['WAIT_CONVERT_TO_REALTIME_SYNC'] ||
                $taskRunningStage == $volCdpDes['FAILBACK_REALTIME_SYNC']
            ) {
                $progress = xphp_get_config('app', 'NULLSPACE');
            }
        }
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        if ($d['module_type'] == $moduletype['FS'] || $d['module_type'] == $moduletype['NAS']) {//文件当前任务进度
            $progress = $this->getFileBackupProgress(
                $d['task_status'],
                $d['total_object_size'],
                $d['total_object_completed_size'],
                $d['module_type_agent'],
                $d['task_type']
            );
        }
        if ($d['module_type'] == $moduletype['M365']) {//m365无法获取到总容量，不显示进度
            return xphp_get_config('app', 'NULLSPACE');
        }
        if (intval(floatval($progress) * 100) == 0) {
            return xphp_get_config('app', 'NULLSPACE');
        }
        return $progress;
    }

    /**
     * 根据任务状态计算进度
     * @param int     $taskStatus  status
     * @param      $totalSize   size
     * @param int     $currentSize size
     * @param boolean $percentFlag 是否一定要得到百分比格式
     * @param string  $tasktype    类型
     * @return array|mixed|string
     */
    public function getTaskTotalProgress(
        $taskStatus,
        $totalSize,
        $currentSize,
        bool $percentFlag,
        string $tasktype
    ) {
        $task = xphp_get_config('task');
        $tasktypeArr = $task['TASKTYPE'];

        if (
            in_array(
                $tasktype,
                [
                    $tasktypeArr['VM_FILE_RECOVERY'],
                    $tasktypeArr['VM_INSTANT_RECOVERY'],
                    $tasktypeArr['DB_CDP_BACKUP'],
                    $tasktypeArr['FILE_CDP_BACKUP'],
                    $tasktypeArr['DB_BACKUP'],
                    $tasktypeArr['OS_INSTANT_RECOVERY']
                ]
            )
        ) {
            //细粒度恢复,瞬时恢复,数据库实时备份,文件实时备份,操作系统瞬时恢复不显示进度
            return xphp_get_config('app', 'NULLSPACE');
        }
        //如果分母为0，则直接返回0%
        if (intval($currentSize) == 0) {
            return '0%';
        }
        $speed = v1_calpercent($totalSize, $currentSize);

        $taskstatusArr = $task['TASKSTATUS'];

        if (!in_array($taskStatus, [$taskstatusArr['RUNNING'], $taskstatusArr['PAUSED'], $taskstatusArr['ABNORMAL']])) {
            //如果任务没有运行
            if ($percentFlag) {
                return '0%';
            } else {
                return xphp_get_config('app', 'NULLSPACE');
            }
        }

        //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
        return sprintf('%.2f', substr($speed, 0, -1)) . '%';
    }

    /**
     * 获取数据验证任务进度
     * @param int     $taskStatus  状态
     * @param int     $progress    进度
     * @param boolean $percentFlag 是否一定要得到百分比格式
     * @return mixed|string
     */
    protected function getSureBackupProgress(int $taskStatus, int $progress, bool $percentFlag)
    {

        $taskstatus = xphp_get_config('task', 'TASKSTATUS');
        if (!in_array($taskStatus, [$taskstatus['RUNNING'], $taskstatus['PAUSED']])) {
            //如果任务没有运行
            if ($percentFlag) {
                return '0%';
            } else {
                return xphp_get_config('app', 'NULLSPACE');
            }
        }

        return $progress . '%';
    }

    /**
     * 获取nas和文件的当前任务进度   扫描中不显示
     * @param $taskstatus      staus
     * @param $totalSize       size
     * @param $currentSize     size
     * @param $moduletypeagent module_type_agent包含task_status和count
     * @return string
     */
    protected function getFileBackupProgress($taskstatus, $totalSize, $currentSize, $moduletypeagent, $taskType): string
    {

        $config = xphp_get_config('task', 'TASKSTATUS');
        if (!in_array($taskstatus, [$config['RUNNING'], $config['PAUSED'], $config['ABNORMAL']])) {
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
        }
        if ($taskType == 17 || $taskType == 18 || $taskType == 19 || $taskType == 20) {
            $speed = v1_calpercent($totalSize, $currentSize);
            //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
            return sprintf('%.2f', substr($speed, 0, -1)) . '%';
        }

        $data = explode('-', $moduletypeagent);
        if (count($data) < 2) {
            return xphp_get_config('app', 'NULLSPACE');
        }
        $ptDes = xphp_get_desc('Pf', 'AGENT_TASK_STATUS');

        $agentstatus = $ptDes[$data[0]];
        //客户端数量等于1，并且是传输中 总进度显示
        if ($data[1] == 1 && $agentstatus == xphp_get_lang('WEB_PLATFORM_DES_RUNNING')) {
            $speed = v1_calpercent($totalSize, $currentSize);
            //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
            return sprintf('%.2f', substr($speed, 0, -1)) . '%';
        } else {
            return xphp_get_config('app', 'NULLSPACE');
        }
    }

    /**
     * 得到任务速度
     * @param int $status    状态
     * @param int $speed     速度
     * @param $speedTime 时间
     * @return string
     */
    public function getCurrentJobSpeed(int $status, int $speed, $speedTime, $taskType): string
    {
        if (time() - $speedTime > 12) {
            $speed = 0;
        }

        $taskTypeArr = xphp_get_config('task', 'TASKTYPE');
        if (
            $taskType == $taskTypeArr['OS_INSTANT_RECOVERY'] || $taskType == $taskTypeArr['VM_INSTANT_RECOVERY']
            || $taskType == $taskTypeArr['VM_FILE_RECOVERY']
        ) {
            return xphp_get_config('app', 'NULLSPACE');
        }

        $taskstatus = xphp_get_config('task', 'TASKSTATUS');
        if (in_array($status, [$taskstatus['RUNNING'], $taskstatus['ABNORMAL']])) {
            if ($speed == 0) {
                // 如果速度为0则显示--
                return xphp_get_config('app', 'NULLSPACE');
            }
            return v1_calspeed($speed);
        }
        return xphp_get_config('app', 'NULLSPACE');
    }

    /**
     * 得到每个任务的操作控制辅助信息,用于链接到任务详情和任务控制
     * @param array $d 数据
     * @return array
     */
    protected function getOpInfo(array $d): array
    {

        $user = xphp_get_user_info();
        $opInfo = array(
            'uuid' => $d['task_uuid'],
            'module' => intval($d['module_type']),
            'taskType' => intval($d['task_type']),
            'subModule' => 0,
            'status' => intval($d['task_status']),
            'tenantuuid' => $user['tenantuuid']
        );

        // TODO 根据不同模块类型 ,添加不一样的子模块信息或其他信息
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');

        if (in_array(intval($d['module_type']), [$moduletype['VM'], $moduletype['VDDT_SERVER'],])) {
            $opInfo['subModule'] = $this->getVMTaskHypervisor($d['task_uuid']);
        }

        $tasktype = xphp_get_config('task', 'TASKTYPE');

        //演练任务,增加恢复模式
        if ($tasktype['ORCH_TASK'] == intval($d['task_type'])) {
            $opInfo['recoveryMode'] = intval($d['recovery_mode']);
            $opInfo['subModule'] = intval($d['hypervisor_type']);
        }

        //数据库任务
        if ($moduletype['DB'] == intval($d['module_type'])) {
            $opInfo['subModule'] = $this->getDBTaskHypervisor($d['task_uuid']);
        }

        //副本
        if ($moduletype['BACKUP_COPY_CLIENT'] == intval($d['module_type'])) {
            $copyarchivesubtype = xphp_get_config('task', 'COPY_ARCHIVE_SUB_TYPE');
            if (in_array(intval($d['task_type']), [$tasktype['BACKUP_COPY'], $tasktype['BACKUP_COPY_FETCH']])) {
                $opInfo['subModule'] = $copyarchivesubtype['BACKUP_COPY_TYPE_VM_BACKUP_COPY'];  // 虚拟机副本子模块
            } elseif (
                in_array(intval($d['task_type']), [$tasktype['FILE_BACKUP_COPY'], $tasktype['FILE_BACKUP_COPY_FETCH']])
            ) {
                $opInfo['subModule'] = $copyarchivesubtype['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  // 文件副本子模块
            } elseif (
                in_array(intval($d['task_type']), [$tasktype['DB_BACKUP_COPY'], $tasktype['DB_BACKUP_COPY_FETCH']])
            ) {
                $opInfo['subModule'] = $copyarchivesubtype['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  // 数据库副本子模块
            } elseif (
                in_array(intval($d['task_type']), [$tasktype['OS_BACKUP_COPY'], $tasktype['OS_BACKUP_COPY_FETCH']])
            ) {
                $opInfo['subModule'] = $copyarchivesubtype['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  // 操作系统副本子模块
            } elseif (in_array(intval($d['task_type']), [$tasktype['ARCHIVE'], $tasktype['ARCHIVE_FETCH']])) {
                $opInfo['subModule'] = $copyarchivesubtype['BACKUP_COPY_TYPE_FS_BACKUP_COPY'];  // 虚拟机归档
            }
        }

        return $opInfo;
    }

    /**
     * 根据任务UUID得到虚拟机模块的子模块号
     * @param string $taskuuid 任务uuid
     * @return int
     */
    protected function getVMTaskHypervisor(string $taskuuid): int
    {
        $this->paramsCheck($taskuuid);
        $sql = "select hypervisor_type from vm_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if ($data) {
            return $data[0]['hypervisor_type'];
        }
        return 0;
    }

    /**
     * 过滤下次启动时间
     * @param  $nextTime   下次开始时间
     * @param  $taskStatus 任务状态
     * @return string
     */
    public function getNextStartTime($nextTime, $taskStatus)
    {
        $nowTime = time();
        if ($nextTime <= 0 || $nextTime < $nowTime || $taskStatus == xphp_get_config('task', 'TASKSTATUS')['STOPPED']) {
            return xphp_get_config('app', 'TIMESPACE');
        }

        return $this->parseDate($nextTime);
    }

    /**
     * 根据任务UUID得到数据库的子模块号
     * @param string $taskuuid 任务uuid
     * @return int
     */
    protected function getDBTaskHypervisor(string $taskuuid): int
    {
        $this->paramsCheck($taskuuid);
        $sql = "select db_type from db_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if ($data) {
            return $data[0]['db_type'];
        }
        return 0;
    }

    /**
     * 获取时间策略的备份方式[strategy onectime manual]
     * @param Integer $strategyId 策略ID
     * @return string|mixed
     */
    public function getTimeStrategyBackupType(int $strategyId)
    {

        $sql = "SELECT time_strategy_backup_type FROM bd_strategy WHERE strategy_id = ? ";
        $timeStrategyBackupData = $this->dbSelect($sql, [$strategyId]);
        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time 
                from bd_time_strategy where strategy_id = ? ";
        $data = $this->dbSelect($sql, array($strategyId));

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
            $type = $timeStrategyBackupData[0]['time_strategy_backup_type'];
            $timeStrategyBackupType = xphp_get_config('task', 'TIME_STRATEGY_BACKUP_TYPE_MAP')[$type];
        }
        return $timeStrategyBackupType;
    }

    /**
     * 得到每个任务的额外信息展示            策略信息/保留策略/存储信息/其他信息
     * @param array $d 每一条数据
     * @return array
     */
    protected function getJobOtherInfo(array $d): array
    {
        $info = [];
        $info['time_strategy_backup_type'] = $this->getTimeStrategyBackupType(intval($d['strategy_id']));
        $info['time_strategy'] = $this->getJobTimeStrategy($d['strategy_id']);  // 时间策略

        $tasktype = xphp_get_config('task', 'TASKTYPE');
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        if (
            in_array(
                intval($d['task_type']),
                [
                    $tasktype['BACKUP'],
                    $tasktype['BACKUP_COPY'],
                    $tasktype['FILE_BACKUP_COPY'],
                    $tasktype['DB_BACKUP_COPY'],
                    $tasktype['ARCHIVE'],
                    $tasktype['DB_BACKUP'],
                    $tasktype['VOL_CDP_BACKUP'],
                    $tasktype['OS_BACKUP'],
                    $tasktype['VM_HUAWEI_CBR_SYNC'],
                    $tasktype['KUBE_BACKUP']
                ]
            )
        ) {
            //如果是备份任务,获取保留策略
            $reservedStrategy = $this->getReservedStrategy($d['strategy_id'], $d['task_uuid']);
        }

        if (in_array(intval($d['task_type']), [$tasktype['RECOVERY'], $tasktype['PLATFORM_RECOVERY']])) {
            if (in_array(intval($d['module_type']), [$moduletype['VM'], $moduletype['PUBLIC_CLOUD']])) {
                //如果是恢复任务,增加任务时间点信息显示
                $recoveryInfo = $this->getDesRecoveryInfo($d['task_uuid']);
            } elseif (in_array(intval($d['module_type']), [$moduletype['FS'], $moduletype['NAS']])) {
                //文件恢复
                $recoveryInfo = $this->getFsDesRecoveryInfo($d['task_uuid']);
            } elseif ($moduletype['M365'] == intval($d['module_type'])) {
                //M365恢复
                $recoveryInfo = $this->getM365DesRecoveryInfo($d['task_uuid']);
            }
        }
        if ($tasktype['VM_INSTANT_RECOVERY'] == intval($d['task_type'])) {
            //如果是瞬时恢复任务,
            $instantInfo = $this->getDesInstantInfo($d['task_uuid']);
        }

        if (
            in_array(
                intval($d['task_type']),
                [$tasktype['INSTANT_RECOVERY_MOTION'], $tasktype['OS_INSTANT_RECOVERY_MOTION']]
            )
        ) {
            //如果是迁移任务
            $motionInfo = $this->getDesMotionInfo($d['task_uuid']);
        }

        if (
            in_array(
                intval($d['task_type']),
                [
                    $tasktype['BACKUP_COPY'],
                    $tasktype['BACKUP_COPY_FETCH'],
                    $tasktype['ARCHIVE'],
                    $tasktype['ARCHIVE_FETCH']
                ]
            )
        ) {
            // 如果是副本和归档任务
            $transportStrategy = $this->getTransportInfo($d['task_uuid'], intval($d['task_type']));
        }

        if (
            $tasktype['OS_RECOVERY'] == intval($d['task_type']) ||
            ($tasktype['PLATFORM_RECOVERY'] == intval($d['task_type']) && $d['module_type'] == $moduletype['OS'])
        ) {
            //主机恢复
            $recoveryInfo = $this->getDesOSRecoveryInfo($d['task_uuid']);
        }

        if ($tasktype['KUBE_RECOVERY'] == intval($d['task_type'])) {
            $recoveryInfo = $this->getKubeDesRecoveryInfo($d['task_uuid']);
        }

        if (in_array(intval($d['task_type']), [$tasktype['OS_INSTANT_RECOVERY'], $tasktype['INSTANT_RECOVERY']])) {
            //操作系统/vm瞬时恢复任务
            $instantInfo = $this->getOSVMInstantInfo($d['task_uuid']);
        }

        if ($tasktype['GRAIN_RECOVERY'] == intval($d['task_type'])) {
            // 如果是细粒度恢复任务
            $grainInfo = $this->getGrainDetailInfo($d['task_uuid']);
            $agentInfo = '';
        } elseif ($moduletype['VOL_CDP'] == intval($d['module_type'])) {
            //如果是实时容灾 master_agent_uuid 是 cdp_vol_task 里面的
            $agentInfo = $this->getVolCdpTaskAgentInfo($d['task_uuid'], $d['task_type']);
            if ($tasktype['VOL_CDP_BACKUP'] == intval($d['task_type'])) {
                //备份
                $backupDetail = $this->getBackupVolInfo($d['task_uuid']);
            } elseif ($tasktype['VOL_CDP_RECOVERY'] == intval($d['task_type'])) {
                //恢复
                $recoveryDetail = $this->getRecoveryVolInfo($d['task_uuid']);
            } elseif ($tasktype['VOL_CDP_TAKEOVER'] == intval($d['task_type'])) {
                //接管
                $takeoverDetail = $this->getTakeoverDetail($d['task_uuid']);
            }
        }

        if ($moduletype['DB_CDP'] == intval($d['module_type'])) {
            //如果是数据库实时
            if ($tasktype['CDP_DB_BACKUP'] == intval($d['task_type'])) {
                //同步
                $dbcdpSyncInfo = $this->getDBcdpDesSyncInfo($d['task_uuid']);
            } elseif ($tasktype['CDP_DB_RECOVERY'] == intval($d['task_type'])) {
                //恢复
                $dbcdpRecoverInfo = $this->getDBcdpDesRecoverInfo($d['task_uuid']);
            }
        }

        $dependTaskUuid = '';
        $subModuleType = $d['sub_module_type'];
        if ($tasktype['DB_BACKUP'] == intval($d['task_type'])) {
            //数据库备份
            $dependTaskUuid = $this->getDbDependTaskUuid($d['task_uuid']);
            $subModuleType = $this->getDbType($d['task_uuid']);
        }

        $info['reserve_strategy'] = $reservedStrategy ?? []; // 保留策略
        $info['transport_strategy'] = $transportStrategy ?? [];  // 传输策略
        $info['time_point'] = $recoveryInfo ?? []; // 恢复时间点数组
        $info['backup_info'] = $backupDetail ?? []; // 备份数组
        $info['recovery_info'] = $recoveryDetail ?? []; // 恢复数组
        $info['takeover_info'] = $takeoverDetail ?? []; // 接管数组

        $info['agent_info'] = $agentInfo ?? ''; // 客户端信息
        $info['instant_detail'] = $instantInfo ?? []; //瞬时恢复信息
        $info['motion_detail'] = $motionInfo ?? []; //迁移信息
        // $info['instantDetail'] = $instantInfo;
        // $info['motionDetail'] = $motionInfo;
        $info['grain_detail'] = $grainInfo ?? [];

        $info['create_time'] = $d['create_time'];
        $info['task_type'] = $d['task_type'];
        $info['module_type'] = $d['module_type'];
        $info['sub_module_type'] = $subModuleType;
        $info['next_time'] = $this->getJobNextstarttime($d['task_uuid'], $d['task_status']);
        $info['dbcdp_sync_info'] = $dbcdpSyncInfo ?? [];           //数据库实时复制数组
        $info['dbcdp_recover_info'] = $dbcdpRecoverInfo ?? [];     //数据库实时恢复数组
        $info['depend_task_uuid'] = $dependTaskUuid;     // 数据库的关联任务uuid
        return $info;
    }

    /**
     * 获取备份卷概要信息
     * @param string $taskUuid 任务uuid
     * @return array
     */
    private function getBackupVolInfo(string $taskUuid): array
    {
        $volSql = "SELECT vol.display_name,vol.capacity from cdp_vol_task_vol task_vol,bd_agent_vol vol 
            where vol.vol_uuid = task_vol.vol_uuid and task_vol.task_uuid = ? ";
        $volData = $this->dbSelect($volSql, array($taskUuid));

        $volInfo = array();
        if (!empty($volData)) {
            foreach ($volData as $d) {
                $volCapacity = v1_calsize($d['capacity'], true);
                $volInfo[] = array(
                    'display_name' => $d['display_name'],
                    'capacity' => $volCapacity
                );
            }
        }
        return $volInfo;
    }

    /**
     * 获取恢复卷信息
     * @param string $taskUuid 任务uuid
     * @return array
     */
    private function getRecoveryVolInfo(string $taskUuid): array
    {
        $sql = "select DISTINCT vol_set.vol_display_name,vol_set.capacity,
                vol.recovery_target_timestamp,vol.recovery_target_vol_uuid,
                vol_task.rebuild_partition_flag,vol.recovery_target_disk_uuid
            from cdp_vol_backup_vol_set vol_set,cdp_vol_task_vol vol,cdp_vol_task vol_task 
            where vol_set.vol_uuid = vol.vol_uuid and vol.task_uuid = ? and vol_task.task_uuid = vol.task_uuid";
        $data = $this->dbSelect($sql, array($taskUuid));

        $dataArray = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $volCapacity = v1_calsize($d['capacity'], true);

                $targetVol = $this->getRecoverTargetVol($d['recovery_target_vol_uuid']);    //恢复目标卷
                $targetDisk = $this->getRecoverTargetDisk($d['recovery_target_disk_uuid']);  //恢复磁盘
                $targetInfo = $targetVol;
                if ($d['rebuild_partition_flag'] == 1) {
                    $targetInfo = $targetDisk;
                }
                $dataArray[] = array(
                    'display_name' => $d['vol_display_name'],
                    'capacity' => $volCapacity,
                    'recovery_target_timestamp' => $d['recovery_target_timestamp'],
                    'recovery_target_vol' => $targetInfo,
                    'rebuild_partition_flag' => $d['rebuild_partition_flag']
                );
            }
        }
        return $dataArray;
    }

    /**
     * 获取接管卷信息
     * @param string $taskUuid 任务uuid
     * @return array
     */
    private function getTakeoverDetail(string $taskUuid): array
    {
        $sql = "SELECT 
                    task_vol.vol_uuid, 
                    takeover_info.takeover_timestamp, 
                    takeover_info.takeover_standby_agent_uuid,
                    task_vol.takeover_standby_real_mount_point 
                FROM 
                    cdp_vol_task_vol task_vol,cdp_vol_task_takeover_info takeover_info, cdp_vol_task cvt
                WHERE 
                    task_vol.task_uuid = ? AND takeover_info.task_uuid = task_vol.task_uuid 
                GROUP BY
	                takeover_info.takeover_timestamp";
        $data = $this->dbSelect($sql, array($taskUuid));

        $dataArray = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $volUuid = $d['vol_uuid'];
                $takeoverTimestamp = $d['takeover_timestamp'];
                $dataSourceInfo = $this->getDataSourceVolInfo($volUuid, $takeoverTimestamp); //接管数据源卷
                // $volDisplayName = $d['dev_name'];
                // $volDisplayName = $dataSourceInfo['vol_display_name'];
                $capacity = v1_calsize($dataSourceInfo['capacity'], true);
                $realMountPoint = $d['takeover_standby_real_mount_point'];
                $dataArray[] = array(
                    // 'display_name' => $volDisplayName,
                    'capacity' => '--', //605暂时取不到容量，先返回--
                    // 'capacity' => $capacity ?? '--',
                    'takeover_timestamp' => $takeoverTimestamp,
                    // 'real_mount_point' => $realMountPoint
                );
            }
        }
        return $dataArray;
    }

    /**
     * 通过恢复恢复目标卷UUID获取卷信息
     * @param string $targetvoluuid uuid
     * @return string
     */
    private function getRecoverTargetVol(string $targetvoluuid): string
    {
        $sql = "select display_name from bd_agent_vol where vol_uuid = '{$targetvoluuid}'";
        $data = $this->dbSelect($sql);

        return !empty($data) ? $data[0]['display_name'] : '';
    }

    /**
     * 通过恢复数据源卷UUID获取恢复卷信息
     * @param string $volUuid   uuid
     * @param $timePoint 时间点
     * @return array
     */
    private function getDataSourceVolInfo(string $volUuid, $timePoint): array
    {
        $sql = "select DISTINCT vol_name,vol_display_name,capacity from cdp_vol_backup_vol_set where vol_uuid = ? 
            and ?  BETWEEN start_timestamp and end_timestamp ";
        $data = $this->dbSelect($sql, array($volUuid, $timePoint));

        if (empty($data)) {
            return [];
        }

        return [
            'vol_name' => $data[0]['vol_name'],
            'vol_display_name' => $data[0]['vol_display_name'],
            'capacity' => $data[0]['capacity']
        ];
    }

    /**
     * 得到当前任务节点和存储信息
     * @param int    $tasktype    任务类型
     * @param string $strategyid  策略id
     * @param string $storageuuid 资源uuid
     * @param string $nodeuuid    节点id
     * @param string $taskUuid    任务uuid
     * @return array
     */
    protected function getStorageInfo($tasktype, $strategyid, $storageuuid, $nodeuuid = '', $taskUuid = ''): array
    {
        $info = array('flag' => false);
        $tasktypeArr = xphp_get_config('task', 'TASKTYPE');
        if (
            !in_array(
                $tasktype,
                [
                    $tasktypeArr['BACKUP'],
                    $tasktypeArr['DB_BACKUP'],
                    $tasktypeArr['DB_RECOVERY'],
                    $tasktypeArr['DRILL'],
                    $tasktypeArr['VOL_CDP_BACKUP'],
                    $tasktypeArr['VOL_CDP_TAKEOVER'],
                    $tasktypeArr['OS_BACKUP'],
                    $tasktypeArr['CDP_DB_BACKUP'],
                    $tasktypeArr['CDP_DB_RECOVERY'],
                    $tasktypeArr['BACKUP_COPY'],
                    $tasktypeArr['BACKUP_COPY_FETCH'],
                    $tasktypeArr['ARCHIVE'],
                    $tasktypeArr['ARCHIVE_FETCH'],
                    $tasktypeArr['KUBE_BACKUP'],
                    $tasktypeArr['FILE_COPY'],
                    $tasktypeArr['FILE_COMPARE'],
                ]
            )
        ) {
            return $info;
        }

        //恢复任务显示节点信息
        if (
            in_array(
                $tasktype,
                [
                    $tasktypeArr['RECOVERY'],
                    $tasktypeArr['OS_RECOVERY'],
                    $tasktypeArr['DB_RECOVERY'],
                    $tasktypeArr['DRILL'],
                    $tasktypeArr['CDP_DB_BACKUP'],
                    $tasktypeArr['CDP_DB_RECOVERY'],
                ]
            )
        ) {
            $info['flag'] = true;
        }

        $sql = "SELECT bt.task_uuid, bt.node_uuid, bt.node_pool_uuid, bt.storage_uuid, bt.storage_pool_uuid,
                    bnp.node_pool_nickname, bsrp.storage_pool_nickname
                FROM bd_task bt
                    LEFT JOIN bd_node_pool bnp ON bt.node_pool_uuid = bnp.node_pool_uuid
                    LEFT JOIN bd_storage_resource_pool bsrp ON bt.storage_pool_uuid = bsrp.storage_pool_uuid
                WHERE bt.task_uuid = ? ";
        $taskData = $this->dbSelect($sql, [$taskUuid]);
        if (is_array($taskData) && $taskData) {
            $info['node_uuid'] = $taskData[0]['node_uuid'];
            $info['node_pool_uuid'] = $taskData[0]['node_pool_uuid'];
            $info['storage_uuid'] = $taskData[0]['storage_uuid'];
            $info['node_pool_uuid'] = $taskData[0]['node_pool_uuid'];
            $info['storage_pool_uuid'] = $taskData[0]['storage_pool_uuid'];
            $info['node_pool_nickname'] = $taskData[0]['node_pool_nickname'];
            $info['storage_pool_nickname'] = $taskData[0]['storage_pool_nickname'];
        }
        //节点信息
        $info['node'] = $nodeuuid ? $this->getNodeNameAndIp($nodeuuid) : [];
        //存储信息
        $sql = "select storage_nickname, storage_type, total_size, free_size, worm_flag 
                from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));
        if ($data) {
            $info['flag'] = true;
            $totalSize = v1_calsize($data[0]['total_size'], true);
            $freeSize = v1_calsize($data[0]['free_size'], true);
            $quotaDes = '';
            $quotaInfo = (new User())->getUserQuotaInfo();
            $quotaFlag = false;     //分配配额标记
            //设置了配额或者是在租户内部
            if ($quotaInfo['quota'] != -1 || !empty($_SESSION['tenantuuid'])) {
                $quotaDes = $quotaInfo['des'];
                $quotaFlag = true;
            }
            $info['storage'] = array(
                'name' => $data[0]['storage_nickname'],
                'type' => empty($data[0]['storage_type']) ?
                    '' :
                    xphp_get_desc('Pf', 'STORAGETYPE')[$data[0]['storage_type']],
                'size' => $totalSize,
                'free_size' => $freeSize,
                'quota_des' => $quotaDes,
                'worm_flag' => $data[0]['worm_flag'],
                'tenant_uuid' => $_SESSION['tenantuuid'],
                'quota_flag' => $quotaFlag,
                'storage_type' => $data[0]['storage_type']
            );
        } else {
            $info['storage'] = [];
        }
        //高级信息(重删/压缩/数据块大小等)
        $sql = "select deduplication_flag, block_size, compressed_flag,
                        encrypted_flag, password_auto_flag,compress_method, encrypt_method,
                        redundant_data_proportiont, data_container_size 
                from bd_storage_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        if ($data) {
            $info['high'] = array(
                'deduplication' => v1_parse_flag_to_bool($data[0]['deduplication_flag']),
                'blocksize' => intval($data[0]['block_size']) / 1024 . 'KB',
                'compressed' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                'encrypt_flag' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                'compress_method' => $data[0]['compress_method'],
                'encrypt_method' => $data[0]['encrypt_method'],
                'redundant_data_proportion' => intval($data[0]['redundant_data_proportiont']) . '%',
                'data_container_size' => intval($data[0]['data_container_size']) / 1073741824 . 'GB'
            );
        } else {
            $info['high'] = [];
        }
        return $info;
    }

    /**
     * 获取节点名称和ip
     * @param string $nodeuuid 节点uuid
     * @return array
     */
    private function getNodeNameAndIp(string $nodeuuid): array
    {
        $sql = "select ip from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));

        if (!empty($data)) {
            return array(
                'name' => (new Node())->getNodeName($nodeuuid),
                'ip' => $data[0]['ip'],
            );
        } else {
            return array(
                'name' => xphp_get_config('app', 'NULLSPACE'),
                'ip' => '',
            );
        }
    }

    /**
     * 获取限速策略配置信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    public function getSpeedlimitDes($taskuuid): array
    {
        // 先在 bd_task_speed_limit_strategy 根据task_uuid 查询出是否有关联的 全局限速策略 strategy_uuid
        $sql = "select strategy_uuid, task_priority from bd_task_speed_limit_strategy where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (!empty($data)) {
            $taskpriority = $data[0]['task_priority'];
            // 在 bd_global_speed_limit_strategy 表里查询出extra_info信息
            $sql = "select extra_info,is_global,strategy_name from bd_global_speed_limit_strategy where strategy_uuid = ? limit 1";
            $data = $this->dbSelect($sql, array($data[0]['strategy_uuid']));
            if (!empty($data)) {
                $extrainfo = json_decode($data[0]['extra_info'], true);
                $des = '';
                foreach ($extrainfo as $d) {
                    // $value .= $d['des']."<br>";
                    $des .= $d['des'] . ';' . "\n";
                }
                $value = $extrainfo[0]['des'];
                if (count($extrainfo) > 1) {
                    $value .= '...';
                }
                $global = $data[0]['is_global'] ? (xphp_get_lang('UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME') . ':' . $data[0]['strategy_name']) : '';
                $text = '';
                if ($global) {
                    $text = $global . '<br>';
                    $text .= xphp_get_lang('UI_JOB_TASK_PRIORITY') . ': ';
                    switch ($taskpriority) {
                        case 1:
                            $text .= xphp_get_lang('UI_JOB_TASK_PRIORITY_PRIMARY') . '<br>';
                            break;
                        case 2:
                            $text .= xphp_get_lang('UI_JOB_TASK_PRIORITY_HIGH') . '<br>';
                            break;
                        default:
                            $text .= xphp_get_lang('UI_JOB_TASK_PRIORITY_HIGHEST') . '<br>';
                            break;
                    }
                }
                $text .= $value;
                return [
                    'task_priority' => $data[0]['is_global'] ? $taskpriority : 0,
                    'value' => ($global ? $global . '<br>' : '') . $value,
                    'des' => $des,
                    'text' => $text,
                ];
            }
        }

        return [
            'task_priority' => 0,
            'value' => xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE'),
            'des' => xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE'),
            'text' => xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE'),
        ];
    }

    /**
     * 通过恢复目标磁盘UUID获取目标磁盘信息
     * @param string $diskuuid uuid
     * @return string
     */
    private function getRecoverTargetDisk(string $diskuuid): string
    {
        $sql = "select display_name from bd_agent_disk where disk_uuid = ?";
        $data = $this->dbSelect($sql, array($diskuuid));

        return !empty($data) ? $data[0]['display_name'] : '';
    }

    /**
     * 根据任务类型获取模块对应的客户端信息
     * @param string $taskUuid 任务uuid
     * @param int    $taskType 任务类型
     * @return string
     */
    private function getVolCdpTaskAgentInfo(string $taskUuid, int $taskType): string
    {

        $agentInfo = '--';
        $masterAgentUuidArr = $this->dbSelect(
            "select master_agent_uuid from cdp_vol_task where task_uuid = ?",
            [$taskUuid]
        );
        $masterAgentUuid = $masterAgentUuidArr[0]['master_agent_uuid'];

        $taskTypeArr = xphp_get_config('task', 'TASKTYPE');
        switch ($taskType) {
            case $taskTypeArr['VOL_CDP_BACKUP']:
                $sql = "select agent_name,hostname,ip,os_type from bd_agent where agent_uuid = ?";
                $data = $this->dbSelect($sql, array($masterAgentUuid));
                if (!empty($data)) {
                    $hostName = $data[0]['hostname'];
                    $agentName = $data[0]['agent_name'];
                    $ip = $data[0]['ip'];
                    $agentInfo = $this->agentStr($agentName, $hostName, $ip);
                }
                break;
            case $taskTypeArr['VOL_CDP_RECOVERY']:
                $sql = 'SELECT master_agent_detail FROM cdp_vol_backup_agent WHERE master_agent_uuid = ?';
                $data = $this->dbSelect($sql, array($masterAgentUuid));
                if (!empty($data)) {
                    $detail = json_decode($data[0]['master_agent_detail']);
                    $hostName = $detail->hostname;
                    $agentName = $detail->agent_name;
                    $agentIp = $detail->ip;
                    $agentInfo = $this->agentStr($agentName, $hostName, $agentIp);
                }
                break;
            case $taskTypeArr['VOL_CDP_TAKEOVER']:
                $sql = "select agent.master_agent_detail
                        from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol_set,
                             cdp_vol_task_takeover_info takeover
                        where agent.master_agent_uuid = ?  and
                              agent.id = vol_set.backup_agent_id and
                              takeover.task_uuid = ?
                         and takeover.takeover_timestamp
                            BETWEEN vol_set.start_timestamp and vol_set.end_timestamp";
                $data = $this->dbSelect($sql, array($masterAgentUuid, $taskUuid));
                if (!empty($data)) {
                    $hostDetal = $data[0]['master_agent_detail'];
                    $masterAgentDetail = json_decode($hostDetal);
                    $hostName = $masterAgentDetail->hostname;
                    $ip = $masterAgentDetail->ip;
                    $agentName = $masterAgentDetail->agent_name;
                    $agentInfo = $this->agentStr($agentName, $hostName, $ip);
                }
                break;
            default:
                $sql = 'SELECT master_agent_detail FROM cdp_vol_backup_agent WHERE master_agent_uuid = ?';
                $data = $this->dbSelect($sql, array($masterAgentUuid));
                if (!empty($data)) {
                    $detail = json_decode($data[0]['master_agent_detail']);
                    $hostName = $detail->hostname;
                    $agentName = $detail->agent_name;
                    $agentIp = $detail->ip;
                    $agentInfo = $this->agentStr($agentName, $hostName, $agentIp);
                }
        }
        return $agentInfo;
    }

    /**
     * 得到时间策略信息
     * @param int $strategyID 策略id
     * @return array
     */
    public function getJobTimeStrategy(int $strategyID): array
    {

        $sql = "select mode, strategy_type, days, start_time, roll_flag, roll_interval, roll_end_time, first_start_time,full_backup_compensation_flag  
                from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));

        $strategy = array();
        $backupmode = xphp_get_config('task', 'BACKUP_MODE');
        foreach ($data as $d) {
            $days = '';
            $rollInterval = 0;
            $startTime = $d['start_time'];
            if (
                intval($d['strategy_type']) == xphp_get_config('task', 'STRATEGY_TYPE')['ONCE'] &&
                strtotime($d['start_time']) < time()
            ) {
                $startTime = $d['start_time'] . '(' . xphp_get_lang('UI_PUBLIC_EXPIRED') . ')';
            }
            if (!empty($d['days'])) {
                $days = $d['days'];
            }
            if (!empty($d['roll_interval'])) {
                $rollInterval = $d['roll_interval'];
            }
            $strategy[] = array(
                //只有一条增量则是永久增量
                'mode' => 1 == count($data) && $backupmode['INCREMENTAL'] == $data[0]['mode']
                    ? $backupmode['PINCREMENTAL'] : $d['mode'],
                'type' => $d['strategy_type'],// 1是天,2是周,3是月,4是一次性
                'days' => $this->getDaysArr($days),
                'frequency' => $this->getFrequency($d['strategy_type'], $days),
                'start_time' => $startTime,
                'roll_flag' => xphp_get_config('app', 'FLAG')['SET'] == intval($d['roll_flag']),
                'roll_interval' => v1_sec_to_time($rollInterval),
                'end_time' => $d['roll_end_time'],
                'first_start_time' => $d['first_start_time'],
                'full_backup_compensation_flag' => v1_parse_flag_to_bool($d['full_backup_compensation_flag']),
            );
        }
        return $strategy;
    }

    /**
     * 得到每周的执行间隔
     * 目前只有每周会返回数据,
     * @param $strategyType 类型
     * @param string $days         时间
     * @return string  空字符串  s1 - s4
     */
    protected function getFrequency($strategyType, string $days): string
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
     * 得到天的数组
     * @param string $days 时间
     * @return array
     */
    protected function getDaysArr(string $days = ''): array
    {
        $count = strlen($days);
        $daysArr = array();
        for ($i = 0; $i < $count; $i++) {
            if ('s' == substr($days, $i, 1)) {
                break;
            }
            $daysArr[] = intval(substr($days, $i, 1));
        }
        return $daysArr;
    }

    /**
     * 得到保留策略信息
     * @param int $strategyID 策略id
     * @param $taskuuid   任务uuid
     * @return array
     */
    protected function getReservedStrategy($strategyID, $taskuuid)
    {

        $sql = "select strategy_type, number, auto_archive, strategy_mode from bd_reserved_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $reserved = false;

        $sqlGFS = "select level1_type, level2_type, retention_num from bd_task_gfs_retention_strategy 
                            where task_uuid = ?";
        $dataGFS = $this->dbSelect($sqlGFS, array($taskuuid));

        $thisGFS = array();
        if (!empty($dataGFS)) {
            foreach ($dataGFS as $d) {
                switch ($d['level1_type']) {
                    case 1:
                        $thisGFS['week'] = array(
                            $d['level2_type'],
                            $d['retention_num'],
                            'checked',
                        );
                        break;
                    case 2:
                        $thisGFS['month'] = array(
                            $d['level2_type'],
                            $d['retention_num'],
                            'checked',
                        );
                        break;
                    case 3:
                        $thisGFS['year'] = array(
                            $d['level2_type'],
                            $d['retention_num'],
                            'checked',
                        );
                        break;
                }
            }
            ;
        }
        foreach ($data as $d) {
            $reserved = array(
                'type' => $d['strategy_type'],
                'value' => $d['number'],
                'number' => $d['number'],
                'archive' => $d['auto_archive'], // 自动归档标记
                'gfs_info' => $thisGFS,
                'strategy_mode' => $d['strategy_mode'],
            );
        }
        return $reserved;
    }

    /**
     * 获取当前恢复展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array[]
     */
    protected function getDesRecoveryInfo(string $taskuuid): array
    {
        $sql = "select timepoint_uuid from vm_machine_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = array();
        foreach ($data as $d) {
            $timepoint[] = $this->getTimepoint($d['timepoint_uuid']);
        }

        return array(
            'timepoint' => $timepoint

        );
    }

    /**
     * 获取当前文件恢复展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getFsDesRecoveryInfo(string $taskuuid): array
    {
        $sql = "SELECT DISTINCT bbt.timepoint FROM fs_path_list fpl,bd_backup_timepoint bbt 
                WHERE fpl.recovery_timepoint_uuid =bbt.timepoint_uuid AND fpl.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = array();
        foreach ($data as $d) {
            $timepoint[] = $d['timepoint'];
        }

        return array(
            'timepoint' => $timepoint
        );
    }

    /**
     * 获取当前m365恢复展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getM365DesRecoveryInfo(string $taskuuid): array
    {
        $sql = "select bbt.timepoint from bd_backup_timepoint bbt, m365_task mt WHERE bbt.timepoint_uuid = mt.recovery_timepoint_uuid AND mt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = array();
        foreach ($data as $d) {
            $timepoint[] = $d['timepoint'];
        }
        return array(
            'timepoint' => $timepoint
        );
    }

    /**
     * 获取当前瞬时恢复展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getDesInstantInfo(string $taskuuid): array
    {
        $sql = "select timepoint_uuid, orig_vm_name, new_vm_name from vm_instant where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        return array(
            'oldname' => $data[0]['orig_vm_name'],
            'newname' => $data[0]['new_vm_name'],
            'timepoint' => $this->getTimepoint($data[0]['timepoint_uuid'])

        );
    }

    /**
     * 获取备份时间点
     * @param string $timepointuuid uuid
     * @return mixed
     */
    protected function getTimepoint(string $timepointuuid)
    {
        $sql = "select timepoint from bd_backup_timepoint where timepoint_uuid = ?";
        $data = $this->dbSelect($sql, array($timepointuuid));
        if ($data) {
            $timepoint = $data[0]['timepoint'];
        } else {
            $timepoint = xphp_get_lang('WEB_JOB_TIMEPOINT_NOE_EXIST');
        }
        return $timepoint;
    }

    /**
     * 获取当前迁移展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getDesMotionInfo(string $taskuuid): array
    {
        //获取 目标主机  cache_target
        $sql = "select ol.timepoint_uuid,ol.migrate_machine_name,
                obt.agent_uuid as sourceid,vbt.vm_name,
                ol.migrate_target_info
        from bd_instant_recovery_task ol
        left join os_backup_timepoint obt on ol.timepoint_uuid  = obt.timepoint_uuid
        left join vm_backup_timepoint vbt on ol.timepoint_uuid  = vbt.timepoint_uuid
        where ol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (empty($data[0])) {
            return [];
        }

        if (empty($data[0]['sourceid'])) {
            // 那么源是虚拟化的
            $sourcehost = $data[0]['vm_name'];
        } else {
            $sourceinfo = $this->getOSHostInfo($data[0]['sourceid']);
            $sourcehost = $this->getAgentNamebyName(
                $sourceinfo['hostname'],
                $sourceinfo['agent_name'],
                $sourceinfo['ip']
            ) . '(' . $sourceinfo['ip'] . ')';
        }

        //获取cache存放位置
        return array(
            'timepoint' => $this->getTimepoint($data[0]['timepoint_uuid']),
            'sourcehost' => $sourcehost,
            'targethost' => (empty($data[0]['migrate_machine_name'])) ? '--' : $data[0]['migrate_machine_name'],
        );
    }

    /**
     * 获取细粒度恢复任务展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getGrainDetailInfo(string $taskuuid): array
    {
        $sql = "select  bbt.timepoint,bgrt.os_type,bgrt.cdp_time
                    from  bd_task bt, bd_grain_recovery_task bgrt
                    left join bd_backup_timepoint bbt on bgrt.timepoint_uuid = bbt.timepoint_uuid
                    where bt.task_uuid = bgrt.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = $data[0]['cdp_time'] ?: $data[0]['timepoint'];
        if (empty($timepoint)) {
            $timepoint = xphp_get_lang('WEB_JOB_TIMEPOINT_NOE_EXIST');
        }
        return array(
            'os_type' => $data[0]['os_type'],
            'timepoint' => $timepoint,
        );
    }

    /**
     * 获取副本回传传输策略
     * @param string $taskuuid 任务uuid
     * @param $tasktype 任务类型
     * @return array
     */
    protected function getTransportInfo(string $taskuuid, $tasktype): array
    {
        $sql = 'select bts.encrypt_flag, bts.compress_flag,bts.block_size, bts.reconnect_times, bts.reconnect_interval, bts.encrypt_method, bts.compress_method,
                        bnn.ip,bnn.port, bnn.alias_name,bts.max_transport_speed,bts.compress_method,
                        bts.network_uuid, bts.network_pool_uuid, bnnp.network_pool_nickname
                    from bd_task bt, bd_transport_strategy bts 
                        left join bd_node_network bnn on bts.network_uuid = bnn.network_uuid
                        LEFT JOIN bd_node_network_pool bnnp ON bts.network_pool_uuid = bnnp.network_pool_uuid
                    where bt.strategy_id = bts.strategy_id and bt.task_uuid = ?';
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array(
            'encrypt_flag' => $this->getStrategySwitch(intval($data[0]['encrypt_flag'])),
            'compress_flag' => $this->getStrategySwitch(intval($data[0]['compress_flag'])),
            'encrypt_flag_value' => v1_parse_flag_to_bool(intval($data[0]['encrypt_flag'])),
            'compress_flag_value' => v1_parse_flag_to_bool(intval($data[0]['compress_flag'])),
            'encrypt_method' => intval($data[0]['encrypt_method']),
            'compress_method' => intval($data[0]['compress_method']),
        );

        $info['block_size'] = $data[0]['block_size'];
        $info['max_transport_speed'] = $data[0]['max_transport_speed']; //最大传输速度
        $name = '';
        if (!empty($data[0]['ip'])) {
            $name = $data[0]['ip'] . ':' . $data[0]['port'];
            if (!empty($data[0]['alias_name'])) {
                $name .= '(' . $data[0]['alias_name'] . ')';
            }
        }
        $info['network'] = $name;
        $info['network_uuid'] = $data[0]['network_uuid'];
        $info['network_pool_uuid'] = $data[0]['network_pool_uuid'];
        $info['network_pool_nickname'] = $data[0]['network_pool_nickname'];
        $info['transport_ip'] = $data[0]['ip'];
        $info['reconnect_times'] = $data[0]['reconnect_times'];
        $info['reconnect_interval'] = $data[0]['reconnect_interval'];
        return $info;
    }

    /**
     * 策略开关描述
     * @param int $flag flag
     * @return array|mixed
     */
    protected function getStrategySwitch(int $flag)
    {
        $switch = xphp_get_lang('UI_PUBLIC_OFF');

        $flags = xphp_get_config('app', 'FLAG');
        if ($flag == $flags['SET']) {
            $switch = xphp_get_lang('UI_PUBLIC_ON');
        } elseif ($flag == $flags['UNSET']) {
            $switch = xphp_get_lang('UI_PUBLIC_OFF');
        }
        return $switch;
    }

    /**
     * 获取数据库CDP任务的任务详情
     * @param string $taskuuid 任务uuid
     * @param string $tasktype 任务类型
     * @return array
     */
    protected function getDbCDPDetail(string $taskuuid, $tasktype): array
    {
        $sql = "select cdh1.host_name as productname, cdh1.ip as productip, cdh1.host_uuid as productuuid, 
                		cdh2.host_name as standbyname, cdh2.ip as standbyip, cdh2.host_uuid as standbyuuid, cdt.config 
                from cdp_db_task as cdt 
                inner join cdp_db_host as cdh1 
                on cdt.product_host_uuid = cdh1.host_uuid 
                inner join cdp_db_host as cdh2 
                on cdt.standby_host_uuid = cdh2.host_uuid 
                where cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        $backupType = '';
        if (xphp_get_config('task', 'TASKTYPE')['DB_CDP_BACKUP'] == $tasktype) {
            //如果是备份,添加备份类型,
            $config = json_decode($data[0]['config'], true);
            $backupType = intval($config['standbyhostInfo']['backuptype']);
        }
        return array(
            'productdes' => $data[0]['productname'] . '(' . $data[0]['productip'] . ')',
            'standbydes' => $data[0]['standbyname'] . '(' . $data[0]['standbyip'] . ')',
            'productip' => $data[0]['productip'],
            'standbyip' => $data[0]['standbyip'],
            'productuuid' => $data[0]['productuuid'],
            'standbyuuid' => $data[0]['standbyuuid'],
            'tasktype' => intval($tasktype),
            'backuptype' => $backupType
        );
    }

    /**
     * 获取文件CDP任务的任务详情
     * @param string $taskuuid 任务uuid
     * @param string $tasktype 任务类型
     * @return array
     */
    protected function getFileCDPDetail(string $taskuuid, $tasktype): array
    {
        $sql = "select cdh1.host_name as productname, cdh1.ip as productip, cdh1.host_uuid as productuuid,
                		cdh2.host_name as standbyname, cdh2.ip as standbyip, cdh2.host_uuid as standbyuuid, cdt.config
                from cdp_fs_task as cdt
                inner join cdp_db_host as cdh1
                on cdt.product_host_uuid = cdh1.host_uuid
                inner join cdp_db_host as cdh2
                on cdt.standby_host_uuid = cdh2.host_uuid
                where cdt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        $backupType = '';
        if (xphp_get_config('task', 'TASKTYPE')['FILE_CDP_BACKUP'] == $tasktype) {
            //如果是备份,添加备份类型,
            $config = json_decode($data[0]['config'], true);
            $backupType = intval($config['standbyhostInfo']['backuptype']);
        }
        return array(
            'productdes' => $data[0]['productname'] . '(' . $data[0]['productip'] . ')',
            'standbydes' => $data[0]['standbyname'] . '(' . $data[0]['standbyip'] . ')',
            'productip' => $data[0]['productip'],
            'standbyip' => $data[0]['standbyip'],
            'productuuid' => $data[0]['productuuid'],
            'standbyuuid' => $data[0]['standbyuuid'],
            'tasktype' => intval($tasktype),
            'backuptype' => $backupType
        );
    }

    /**
     * 获取数据库信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getDBProtectDetail(string $taskuuid): array
    {
        $sql = "select dt.db_type, bt.task_type from bd_task bt, db_task dt 
                    where bt.task_uuid = dt.task_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));

        return !empty($data) ? ['dbtype' => $data[0]['db_type'], 'tasktype' => $data[0]['task_type']] : [];
    }

    /**
     * 获取当前主机恢复展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getDesOSRecoveryInfo(string $taskuuid): array
    {
        $sql = "select timepoint_uuid from os_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = array();
        foreach ($data as $d) {
            $timepoint[] = $this->getTimepoint($d['timepoint_uuid']);
        }
        return array(
            'timepoint' => $timepoint
        );
    }

    /**
     * 获取当前主机恢复展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getKubeDesRecoveryInfo(string $taskuuid): array
    {
        $sql = "select bbt.timepoint, kc.cluster_name as cluster_name, srckc.cluster_name as src_cluster_name   from kube_task kt
left join bd_backup_timepoint bbt on kt.src_timepoint_uuid = bbt.timepoint_uuid
left join kube_cluster kc on kc.cluster_uuid = kt.cluster_uuid
left join kube_cluster srckc on srckc.cluster_uuid = kt.src_cluster_uuid
where  kt.task_uuid= ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $timepoint = array();
        foreach ($data as $d) {
            $timepoint[] = $d['timepoint'];
            // $timepoint[] = array(
            //     'timepoint' => $d['timepoint'],
            //     'cluster_name' => $d['cluster_name'],
            //     'src_cluster_name' => $d['src_cluster_name'],
            // );
        }
        return array(
            'timepoint' => $timepoint
        );
    }








    /**
     * 获取当前主机/虚拟机瞬时恢复展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getOSVMInstantInfo(string $taskuuid): array
    {
        //获取 目标主机  cache_target
        $sql = "select birt.timepoint_uuid, birt.instant_server_config,vbt.vm_name,
                birt.instant_target_info,obt.os_name,obt.agent_ip,obt.agent_uuid
        from bd_instant_recovery_task birt
        left join os_backup_timepoint obt on obt.timepoint_uuid  = birt.timepoint_uuid
        left join vm_backup_timepoint vbt on birt.timepoint_uuid  = vbt.timepoint_uuid
        where birt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (empty($data[0])) {
            return [];
        }
        $instanttargetinfo = json_decode($data[0]['instant_target_info'], true);
        $instantserverinfo = json_decode($data[0]['instant_server_config'], true);

        if (!empty($data[0]['vm_name'])) {
            // 那么源是虚拟化的
            $sourcehost = $data[0]['vm_name'];
        } else {
            $targetinfo = $this->getOSHostInfo($data[0]['agent_uuid']);
            $sourcehost = $this->getAgentNamebyName(
                $targetinfo['hostname'],
                $targetinfo['agent_name'],
                $targetinfo['ip']
            ) . '(' . $targetinfo['ip'] . ')';
        }

        if ($instanttargetinfo['target_type'] == 2) {
            // 是整机的目标
            $targetinfo = $this->getOSHostInfo($instanttargetinfo['target_uuid']);
            $targethost = $this->getAgentNamebyName(
                $targetinfo['hostname'],
                $targetinfo['agent_name'],
                $targetinfo['ip']
            ) . '(' . $targetinfo['ip'] . ')';
        } else {
            $sql = 'select `vm_config` from vm_machine_list where task_uuid = ?';
            $names = $this->dbSelect($sql, [$taskuuid]);
            $vmConfig = json_decode($names[0]['vm_config'], true);
            if (!empty($vmConfig)) {
                $targethost = $vmConfig['new_vm_name'];
            }
            $targethost = $targethost ?? xphp_get_config('app', 'NULLSPACE');
        }

        //获取cache存放位置
        return array(
            'timepoint' => $this->getTimepoint($data[0]['timepoint_uuid']),
            'sourcehost' => $sourcehost,
            'targethost' => $targethost,
            'cachetarget' => $instantserverinfo['cache_dir'] ?? '--', //瞬时恢复cache存放位置
        );
    }

    /**
     * 获取瞬时恢复展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getInstantInfo(string $taskuuid): array
    {
        $sql = "SELECT birt.timepoint_uuid FROM bd_instant_recovery_task birt WHERE birt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (empty($data[0])) {
            return [];
        }
        return array(
            'timepoint' => $this->getTimepoint($data[0]['timepoint_uuid']),
        );
    }

    /**
     * 获取当前主机迁移展开详情信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    protected function getOSMotionInfo(string $taskuuid): array
    {
        //获取 目标主机  cache_target
        $sql = "select ol.timepoint_uuid, ol.agent_uuid as targetid, obt.agent_uuid as sourceid
        from os_list ol
        left join os_task ot on ol.task_uuid = ot.task_uuid
        left join os_backup_timepoint obt on ol.timepoint_uuid  = obt.timepoint_uuid
        where ol.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $sourceinfo = $this->getOSHostInfo($data[0]['sourceid']);
        //如果是瞬时恢复任务 目标主机和源主机一样
        $targetinfo = $this->getOSHostInfo($data[0]['targetid']); //目标主机只取第一个原主机下的
        //获取cache存放位置
        $info = array(
            'timepoint' => $this->getTimepoint($data[0]['timepoint_uuid']),
            'sourcehost' => $this->getAgentNamebyName(
                $sourceinfo['hostname'],
                $sourceinfo['agent_name'],
                $sourceinfo['ip']
            ) . '(' . $sourceinfo['ip'] . ')',
            'targethost' => $this->getAgentNamebyName(
                $targetinfo['hostname'],
                $targetinfo['agent_name'],
                $targetinfo['ip']
            ) . '(' . $targetinfo['ip'] . ')'
        );
        return $info;
    }

    /**
     * 如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * @param unknown $hostname  主机名
     * @param unknown $agentname 别名
     * @param string  $ip        IP地址
     * @return unknown
     */
    public function getAgentNamebyName($hostname, $agentname, $ip)
    {
        if (empty($hostname) && !empty($agentname)) {
            return $agentname;
        } elseif (!empty($hostname) && empty($agentname)) {
            return $hostname;
        } else {
            if ($agentname == $ip) {
                return $hostname;
            } else {
                return $agentname;
            }
        }
    }

    /**
     * 获取操作系统迁移任务主机信息
     * @param string $agentuuid 客户端uuid
     * @return array
     */
    public function getOSHostInfo($agentuuid)
    {
        $sql = "select hostname, agent_name, ip, online_flag from bd_agent where agent_uuid  = ? ";
        $data = $this->dbSelect($sql, array($agentuuid));
        //组装信息
        $agentInfo = array(
            'hostname' => $data[0]['hostname'],
            'agent_name' => $data[0]['agent_name'],
            'ip' => $data[0]['ip'],
            'online_flag' => $data[0]['online_flag']
        );
        return $agentInfo;
    }

    /**
     * 获取备份时间策略
     * @param string $strategyID 策略id
     * @return array
     */
    protected function getBackupStrategy(string $strategyID): array
    {
        $sql = "select unix_timestamp(start_time) start_time, mode,strategy_type from bd_time_strategy 
                        where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $info = array();
        $type = 0;
        $flag = false;
        $backupmode = xphp_get_config('task', 'BACKUP_MODE');
        foreach ($data as $d) {
            //只有一条增量则是永久增量
            $info[] = 1 == count($data) && $backupmode['INCREMENTAL'] == $data[0]['mode']
                ? $backupmode['PINCREMENTAL'] : intval($d['mode']);
            if ($d['strategy_type'] == 4) {
                $type = intval($d['strategy_type']);
                if (intval($d['start_time']) <= time()) {
                    $flag = true;
                }
            }
        }
        return array(
            'modeList' => $info,
            'type' => $type,
            'timeout' => $flag
        );
    }

    /**
     * 一次性获取时间策略信息
     * @param array $strategyIDs 时间策略id数组
     * @return array 以 strategy_id 为键的映射表
     */
    protected function getBackupStrategies(array $strategyIDs)
    {
        if (empty($strategyIDs)) {
            return [];
        }

        $strategyIDs = array_unique($strategyIDs);
        $placeholders = implode(',', array_fill(0, count($strategyIDs), '?'));
        $sql = "select strategy_id, unix_timestamp(start_time) as start_time, mode, strategy_type 
                from bd_time_strategy 
                where strategy_id IN ({$placeholders})";

        $all_strategies_data = $this->dbSelect($sql, $strategyIDs);

        // Group data by strategy_id
        $grouped_data = [];
        foreach ($all_strategies_data as $row) {
            $grouped_data[$row['strategy_id']][] = $row;
        }

        $result_map = [];
        $backupmode = xphp_get_config('task', 'BACKUP_MODE');

        foreach ($grouped_data as $strategyID => $data) {
            $info = array();
            $type = 0;
            $flag = false;

            foreach ($data as $d) {
                // 只有一条增量则是永久增量
                $info[] = 1 == count($data) && $backupmode['INCREMENTAL'] == $data[0]['mode']
                    ? $backupmode['PINCREMENTAL'] : intval($d['mode']);

                if ($d['strategy_type'] == 4) { // 4 seems to be a special type
                    $type = intval($d['strategy_type']);
                    if (intval($d['start_time']) <= time()) {
                        $flag = true;
                    }
                }
            }

            $result_map[$strategyID] = [
                'modeList' => $info,
                'type' => $type,
                'timeout' => $flag
            ];
        }

        return $result_map;
    }

    /**
     * 获取任务下次开始时间 
     * @param string $taskuuid   任务uuid
     * @param $taskStatus 任务状态
     * @return array|mixed|string
     */
    protected function getJobNextstarttime(string $taskuuid, $taskStatus)
    {

        $sql = "select unix_timestamp(bs.next_start_time) next_start_time from bd_task bt, bd_strategy bs 
                        where bt.strategy_id = bs.strategy_id and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $nextTime = $timesapce = xphp_get_config('app', 'TIMESPACE');
        if (!empty($data)) {
            $nowTime = time();
            if ($data[0]['next_start_time'] < $nowTime) {
                $nextTime = $timesapce;
            } else {
                $nextTime = $this->parseDate($data[0]['next_start_time']);
            }
        }

        if (intval($taskStatus) == xphp_get_config('task', 'TASKSTATUS')['STOPPED']) {
            $nextTime = $timesapce;
        }

        return $nextTime;
    }

    /**
     * 批量获取任务下次开始时间
     * @param array $taskUuids 任务uuid数组
     * @param array $taskStatuses 任务状态映射 [task_uuid => status]
     * @return array [task_uuid => next_start_time]
     */
    protected function getJobsNextstarttime(array $taskUuids, array $taskStatuses): array
    {
        if (empty($taskUuids)) {
            return [];
        }

        // 1. 批量查询下次开始时间
        $placeholders = implode(',', array_fill(0, count($taskUuids), '?'));
        $sql = "select bt.task_uuid, unix_timestamp(bs.next_start_time) as next_start_time 
                from bd_task bt, bd_strategy bs 
                where bt.strategy_id = bs.strategy_id and bt.task_uuid IN ({$placeholders})";

        $data = $this->dbSelect($sql, $taskUuids);

        // 2. 将查询结果处理为 task_uuid => next_start_time 的映射
        $nextTimeMap = array_column($data, 'next_start_time', 'task_uuid');

        $result = [];
        $timespace = xphp_get_config('app', 'TIMESPACE');
        $stoppedStatus = xphp_get_config('task', 'TASKSTATUS')['STOPPED'];
        $nowTime = time();

        // 3. 循环处理每个任务
        foreach ($taskUuids as $taskuuid) {
            $taskStatus = $taskStatuses[$taskuuid] ?? null;

            // 如果任务已停止，下次执行时间为空
            if (intval($taskStatus) === $stoppedStatus) {
                $result[$taskuuid] = $timespace;
                continue;
            }

            // 如果查询到下次执行时间
            if (isset($nextTimeMap[$taskuuid])) {
                $nextStartTime = $nextTimeMap[$taskuuid];
                // 如果下次执行时间早于当前时间，说明已过期，下次执行时间为空
                if ($nextStartTime < $nowTime) {
                    $result[$taskuuid] = $timespace;
                } else {
                    // 否则，格式化时间
                    $result[$taskuuid] = $this->parseDate($nextStartTime);
                }
            } else {
                // 如果没有查询到，下次执行时间为空
                $result[$taskuuid] = $timespace;
            }
        }

        return $result;
    }

    /**
     * 获取任务运行阶段
     * @param string $taskuuid   任务uuid
     * @param int    $moduleType 类型
     * @return int
     */
    protected function getTaskRunStage(string $taskuuid, int $moduleType)
    {
        $taskCurrentStage = 0;
        if ($moduleType == xphp_get_config('module', 'MODULE_TYPE')['VOL_CDP']) {
            $sql = "SELECT current_task_running_stage from cdp_vol_task where task_uuid=?";
            $data = $this->dbSelect($sql, array($taskuuid));
            $taskCurrentStage = !empty($data) ? $data[0]['current_task_running_stage'] : 0; //任务阶段
        }
        return $taskCurrentStage;
    }

    /**
     * 获取虚拟机或文件传输大小
     * @param string $vMTranSize size
     * @param string $fSTranSize size
     * @param int    $taskType   type
     * @param array  $details    历史任务详情
     * @return string
     */
    public function getTransportSize(int $vMTranSize, int $fSTranSize, int $taskType, array $details): string
    {
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        if (
            in_array(
                $taskType,
                [
                    $moduletype['OS'],
                    $moduletype['VOL_CDP'],
                    $moduletype['DB'],
                    $moduletype['BACKUP_COPY_CLIENT'],
                    $moduletype['M365'],
                ]
            )
        ) {
            return $vMTranSize;
        } elseif (in_array($taskType, [$moduletype['FS'], $moduletype['NAS']])) {
            return $fSTranSize;
        } elseif ($moduletype['VM'] == $taskType) {
            // 虚拟机从历史任务详情计算
            $transportSize = 0;
            $vmDetail = $details['vms_details'] ?? $details;
            foreach ($vmDetail as $d) {
                $transportSize += intval($d['transport_size']);
            }
            return $transportSize;
        } else {
            return $vMTranSize;
        }
    }

    /**
     * 获取写入数据总大小
     * @param array $detail     历史任务详情
     * @param int   $moduleType 模块类型
     * @param int   $writeSize  写入大小
     * @return string
     */
    public function getTotalWriteSize(array $detail, int $moduleType, int $writeSize): string
    {
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        if ($moduletype['VM'] == $moduleType) {
            // 虚拟机从历史任务详情计算
            $writeSize = 0;
            $vmDetail = $detail['vms_details'] ?? $detail;
            foreach ($vmDetail as $d) {
                $writeSize += intval($d['write_size']);
            }
        }
        return v1_calSize($writeSize, true);
    }

    /**
     * 获取有效数据总大小   
     * @param array $detail    detail
     * @param int   $modeType  类型
     * @param int   $taskType  任务类型
     * @param int   $totalSize size
     * @return array|mixed|string
     */
    protected function getTotalValidSize(array $detail, int $modeType, int $taskType, int $totalSize)
    {
        $validSize = 0;
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY'] || $taskType == xphp_get_config('task', 'TASKTYPE')['BACKUP_COPY_FETCH'] || $taskType == xphp_get_config('task', 'TASKTYPE')['ARCHIVE'] || $taskType == xphp_get_config('task', 'TASKTYPE')['ARCHIVE_FETCH']) {
            foreach ($detail as $d) {
                $validSize += intval($d['valid_size']);
            }
            return v1_calsize($validSize, true);
        }
        if ($modeType == $moduletype['DB']) {
            return xphp_get_config('app', 'NULLSPACE');
        } elseif ($modeType == $moduletype['OS']) {
            foreach ($detail as $d) {
                $validSize += intval($d['os_valid_size']);
            }
            return v1_calsize($validSize, true);
        } elseif ($modeType == $moduletype['VOL_CDP']) {
            foreach ($detail as $d) {
                $validSize += intval($d['real_size']);
            }
            return v1_calsize($validSize, true);
        } elseif ($modeType != $moduletype['VM'] && $modeType != $moduletype['PUBLIC_CLOUD']) {
            // 虚拟机和公有云需要从detail累加，其他的直接返回
            return v1_calsize($totalSize, true);
        } elseif ($taskType == xphp_get_config('task', 'TASKTYPE')['BACKUP_EXPORT']) {
            return xphp_get_config('app', 'NULLSPACE');
        } elseif ($taskType == xphp_get_config('task', 'TASKTYPE')['VM_HUAWEI_CBR_SYNC']) {
            $detail = $detail['vms_details'] ?? $detail;
        }
        $detail = $detail['vms_details'] ?? $detail;
        foreach ($detail as $d) {
            $validSize += intval($d['vm_valid_size']);
        }
        return v1_calsize($validSize, true);
    }

    /**
     * 处理历史任务详情
     * @param array   $details     详情
     * @param array   $info        所有信息
     * @param ?string $errorDetail 错误详情
     * @return array
     */
    public function historyTaskDetailsHandler(array $details, array $info, ?string $errorDetail)
    {
        if (empty($details)) {
            return false;
        }

        $module = $info['module_type'];
        $taskType = $info['task_type'];
        $moduletype = xphp_get_config('module', 'MODULE_TYPE');
        $taskTypeArr = xphp_get_config('task', 'TASKTYPE');
        $nullSpace = xphp_get_config('app', 'TIMESPACE');

        if (
            in_array(
                $taskType,
                [
                    $taskTypeArr['BACKUP_COPY'],
                    $taskTypeArr['BACKUP_COPY_FETCH'],
                    $taskTypeArr['ARCHIVE'],
                    $taskTypeArr['ARCHIVE_FETCH']
                ]
            )
        ) {
            $data = $this->historyTaskDetailsHandlerCopy($details, $module, $info['submodule_type']);
        } else if ($taskType == $taskTypeArr['SURE_BACKUP']) {
            //数据验证
            $data = $this->historyTaskDetailsHandlerVerify($details, $info);
        } else {
            if ($module == $moduletype['VM']) {
                //数据验证任务
                if ($taskType == $taskTypeArr['SURE_BACKUP']) {
                    $data = $this->historyTaskDetailsVerify($details, $info);
                } elseif ($taskType == $taskTypeArr['VM_HUAWEI_CBR_SYNC']) {
                    $data = $this->historyTaskDetailsSync($details, $info);
                } else {
                    $data = $this->historyTaskDetailsHandlerVM($details);
                }
                //处理开始时间和结束时间为"0000-00-00 00:00:00"的情况
                foreach ($data as $key => $d) {
                    if ($d['start_transfer_time'] && strtotime($d['start_transfer_time']) <= 0) {
                        $data[$key]['start_transfer_time'] = $nullSpace;
                    }

                    if ($d['end_transfer_time'] && strtotime($d['end_transfer_time']) <= 0) {
                        $data[$key]['end_transfer_time'] = $nullSpace;
                    }
                }
                //处理开始时间和结束时间为"0000-00-00 00:00:00"的情况
                foreach ($data as $key => $d) {
                    if ($d['start_transfer_time'] && strtotime($d['start_transfer_time']) <= 0) {
                        $data[$key]['start_transfer_time'] = $nullSpace;
                    }

                    if ($d['end_transfer_time'] && strtotime($d['end_transfer_time']) <= 0) {
                        $data[$key]['end_transfer_time'] = $nullSpace;
                    }
                }
            } elseif ($module == $moduletype['FS'] || $module == $moduletype['NAS']) {//这里文件和nas一样
                $data = $this->historyTaskDetailsHandlerFile($details, $taskType, $info);
            } elseif ($module == $moduletype['DB']) {
                $data = $this->historyTaskDetailsHandlerDB($details, $taskType, $info, $errorDetail);
            } elseif ($module == $moduletype['OS']) {
                if ($taskType == $taskTypeArr['OS_INSTANT_RECOVERY_MOTION']) {
                    //如果是迁移
                    $data = $this->historyTaskDetailsHandlerOSMotion($details, $taskType, $info);
                } else {
                    $data = $this->historyTaskDetailsHandlerOS($details, $taskType, $info);
                }
            } elseif ($module == $moduletype['VOL_CDP']) {
                $data = $this->historyTaskDetailsHandlerVolCdp($details, $taskType, $info);
            } elseif ($module == $moduletype['M365']) {
                $data = $this->historyTaskDetailsHandlerEXCHANGE($details, $taskType, $info);
            } elseif ($module == $moduletype['PUBLIC_CLOUD']) {
                $data = $this->historyTaskDetailsHandlerVM($details);
            } elseif ($module == $moduletype['FILE_COPY']) {//文件复制
                $data = $this->historyTaskDetailsHandlerFileCopy($details, $info);
            } elseif ($module == $moduletype['KUBERNETES']) {
                $k8sHandler = new K8sJobInfo;
                $history_params = array(
                    'history_uuid' => $info['history_uuid'],
                );
                $data = $k8sHandler->getk8sJobHistoryInfo($history_params);
            } elseif ($module == $moduletype['DB_CDP']) {
                $dbcdpHandler = new DbcdpJobInfo();
                $data = $dbcdpHandler->historyTaskDetailsHandlerDbCdp($details, $taskType, $info);
            } else {
                return false;
            }
        }
        $desc = xphp_get_desc('Pf', 'TASKTYPEDES');
        return [
            'list' => $data,
            'info' => [
                'history_id' => $info['id'],
                'level' => $this->getJobStatusShowLevel($info['error_code']),
                'module_type' => $module,
                'job_type' => $taskType,
                'job_type_value' => xphp_get_lang($desc[$taskType]),
            ]
        ];
    }

    /**
     * 获取数据验证历史任务虚拟机详情信息
     * @param array $details details
     * @param array $info    info
     * @return array
     */
    private function historyTaskDetailsVerify(array $details, array $info): array
    {
        $historyuuid = $info['history_uuid'];
        $sqlVm = "SELECT 
                    item_name,
                    extension_info,
                    unix_timestamp( start_time ) start_time,
                    unix_timestamp( end_time ) end_time,
                    report_uuid
                  FROM 
                    sr_surebackup_report 
                  WHERE 
                    history_uuid = ?";
        $dataVm = $this->dbSelect($sqlVm, array($historyuuid));
        //获取报告中的虚拟机
        $vmInfo = array();
        $verfyVs = xphp_get_desc('Vm', 'VERIFY_VM_STATUS');
        $verfyFs = xphp_get_desc('Vm', 'VERIFY_FUNC_STATUS');
        $result = [];
        foreach ($details as $d) {
            $result = array(
                'vm_name' => $d['vm_name'],//虚拟机名
                'status' => $verfyVs[intval($d['task_status'])],//状态
                'error_code' => $this->getVMErrorCodeDes($d['task_status'], $d['error_code']),//错误信息描述
                'start_time' => xphp_get_config('app', 'TIMESPACE'), //开始时间
                'end_time' => xphp_get_config('app', 'TIMESPACE'),//结束时间
                'ping_status' => $verfyFs[0],//ping
                'heartbeat_status' => $verfyFs[0],//心跳测试
                'screen_status' => $verfyFs[0],//截屏
                'verify_way' => intval($details[0]['verify_way']),//验证方式
            );
            foreach ($dataVm as $vm) {
                $extensionInfo = json_decode($vm['extension_info'], true);
                //获取虚拟机报告信息
                if ($d['new_name'] == $vm['item_name']) {
                    //ping
                    $result['ping_status'] = $verfyFs[intval($extensionInfo['ping_test_status'])];    //默认等待
                    //心跳测试
                    $result['heartbeat_status'] = $verfyFs[intval($extensionInfo['heartbeat_status'])];    //默认等待
                    //开始时间
                    $result['start_time'] = $this->parseDate($vm['start_time']);
                    //结束时间
                    $result['end_time'] = $this->parseDate($vm['end_time']);
                    // 验证报告UUID
                    $result['report_uuid'] = $vm['report_uuid'];
                }
            }
            $vmInfo[] = $result;
        }

        return $vmInfo;
    }

    /**
     *  处理虚拟机历史任务详情
     * @param array $details details
     * @return array
     */
    private function historyTaskDetailsHandlerVM(array $details): array
    {
        // 开启节点资源限制时直接返回配置
        if (isset($details['resource_limiting_node_config'])) {
            return $details;
        }
        $i = 0;
        $vmsdetails = $details;
        $changed = ['add_vms' => [], 'remove_vms' => []];
        if (isset($details['vms_changed_status'])) {
            $changed = $details['vms_changed_status'];
        }
        if (isset($details['vms_details'])) {
            $vmsdetails = $details['vms_details'];
        }
        foreach ($vmsdetails as $d) {
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            $vmsdetails[$i]['transport_size'] = v1_calsize($d['transport_size'], true);
            $vmsdetails[$i]['vm_size'] = v1_calsize($d['vm_size'], true);
            $vmsdetails[$i]['vm_valid_size'] = v1_calsize($d['vm_valid_size'], true);
            $vmsdetails[$i]['real_size'] = v1_calsize($d['write_size'], true);
            $vmsdetails[$i]['task_status_value'] = intval($d['task_status']);
            $vmsdetails[$i]['task_status'] = xphp_get_desc('Vm', 'VmTaskStatus')[intval($d['task_status'])];
            $vmsdetails[$i]['error_code_value'] = intval($d['error_code']);
            $vmsdetails[$i]['task_status_value'] = intval($d['task_status']);
            $vmsdetails[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            $vmsdetails[$i]['backup_mode'] = $this->getHistoryTaskType(
                xphp_get_config('task', 'TASKTYPE')['BACKUP'],
                $d['mode']
            );
            $vmsdetails[$i]['transfer_speed'] = v1_calspeed(intval($d['transfer_speed']));
            if (!$d['version'] || empty($d['version'])) {
                $vmsdetails[$i]['version'] = xphp_get_config('app', 'NULLSPACE');
            }
            if (!empty($d['disk_list'])) {
                // 适配aws卷恢复相关数据
                foreach ($d['disk_list'] as $k => $disk) {
                    $vmsdetails[$i]['disk_list'][$k]['avalid_size'] = v1_calsize($disk['avalid_size'], true);
                    $vmsdetails[$i]['disk_list'][$k]['total_size'] = v1_calsize($disk['total_size'], true);
                    $vmsdetails[$i]['disk_list'][$k]['transfer_size'] = v1_calsize($disk['transfer_size'], true);
                    $vmsdetails[$i]['disk_list'][$k]['write_size'] = v1_calsize($disk['write_size'], true);
                }
            }
            $i++;
        }
        return ['vms_details' => $vmsdetails, 'vms_changed_status' => $changed];
    }

    /**
     *  处理exchange历史任务详情
     * @param array $details details
     * @return array
     */
    private function historyTaskDetailsHandlerEXCHANGE($details)
    {
        //这里是从数据库的detail字段取得的,然后二次处理一些数据
        $errorCodeDes = xphp_get_config('error', 'errorCodeDes');
        $details['error_code'] = $errorCodeDes[xphp_get_config('error', 'errorCode')[$details['error_code']]];
        $details['backup_mode'] = $this->getHistoryTaskType(
            xphp_get_config('task', 'TASKTYPE')['BACKUP'],
            $details['mode']
        );
        return $details;
    }

    /**
     *  处理文件复制历史任务详情
     * @param array $details details
     * @param array $info    历史任务信息
     * @return array
     */
    private function historyTaskDetailsHandlerFileCopy($details, $info)
    {
        //这里是从数据库的detail字段取得的,然后二次处理一些数据
        $error = xphp_get_config('error');
        $errorCodeDes = xphp_get_config('error', 'errorCodeDes');
        $fileCopy = new FileCopyJobInfo();
        //每一条路径的error_code
        $copy_list = $details['copy_list'];
        foreach ($copy_list as $key => $a) {
            $copy_list[$key]['error_code'] =
                $error['errorCodeDes'][$error['errorCode'][$a['error_code']]];
        }
        $details['copy_list'] = $copy_list;
        //最外面整个任务的error_code
        $details['error_code'] = $errorCodeDes[xphp_get_config('error', 'errorCode')[$info['error_code']]];
        $details['sync_task_mode'] = $this->getHistoryTaskType('', $details['sync_task_mode']);
        $details['source_name'] = $fileCopy->getSubAgentName($details['source_type'], $details['source_uuid']);
        $details['target_name'] = $fileCopy->getSubAgentName($details['target_type'], $details['target_uuid']);
        return $details;
    }

    /**
     * 获取下云同步历史任务虚拟机详情信息
     * @param array $details details
     * @param array $info    info
     * @return array
     */
    private function historyTaskDetailsSync(array $details, array $info): array
    {
        $synctype = $details[0]['version'];
        $infoList = array();
        $vmsdetail = $details;
        foreach ($vmsdetail as $d) {
            $extensioInfo = json_decode($d['cbr_detail'], true);
            $detailinfo = array(
                'path' => $d['dir_path'],
                "count" => intval($extensioInfo['count']),
                'backups' => $extensioInfo['backups'],
                'synctype' => $synctype
            );
            $infoList[] = $detailinfo;
        }
        return $infoList;
    }

    /**
     * 处理文件历史任务详情
     * @param array $details  details
     * @param int   $taskType 任务类型
     * @param mixed $info     其他信息
     * @return array
     */
    private function historyTaskDetailsHandlerFile(array $details, int $taskType, $info): array
    {
        $error = xphp_get_config('error');
        $list = $details['agent_info_list'];
        foreach ($list as $key => $a) {
            $list[$key]['description'] =
                $error['errorCodeDes'][$error['errorCode'][$a['description']]];
            //得到备份模式
            $details['backup_mode'] = xphp_get_desc('Pf', 'BACKUP_MODE_DES')[isset($list[$key]['backup_mode']) ? $list[$key]['backup_mode'] : 0] .
                xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
            $list[$key]['backup_mode'] = $this->getHistoryTaskType(
                $taskType,
                isset($list[$key]['backup_mode']) ? $list[$key]['backup_mode'] : 0
            );
        }
        $details['permission_operate_flag'] = $details['permission_operate_flag'] == 1 ? xphp_get_lang('UI_PUBLIC_ON_TWO') : xphp_get_lang('UI_PUBLIC_OFF_TWO');
        $details['same_file_strategy'] = $this->getSameNameStrategy($details['same_file_strategy'] ?? '');
        $details['link_file_pass_flag'] = $details['link_file_pass_flag'] == 1 ? xphp_get_lang('UI_PUBLIC_ON_TWO') : xphp_get_lang('UI_PUBLIC_OFF_TWO');
        $details['dir_tree_recovery_flag'] = $details['dir_tree_recovery_flag'] == 1 ? xphp_get_lang('UI_PUBLIC_ON_TWO') : xphp_get_lang('UI_PUBLIC_OFF_TWO');
        unset($details['agent_info_list']);
        $details['list'] = $list;
        $details['history_uuid'] = $info['history_uuid'];
        //再添加一个sourcetype 通过history_uuid找到task_uuid

        return $details;
    }

    /**
     *  同名文件处理
     * @param boolean $flag 处理方式
     * @return string 处理方式描述
     */
    private function getSameNameStrategy($flag)
    {
        $des = xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
        if ($flag == '') {
            return $des;
        }
        switch (intval($flag)) {
            case 1:
                $des = xphp_get_lang('UI_RECOVERY_FILE_PROCESS_SAME_FILE_COVER');
                break;
            case 2:
                $des = xphp_get_lang('UI_RECOVERY_FILE_PROCESS_SAME_FILE_KEEP_LATEST');
                break;
            case 3:
                $des = xphp_get_lang('UI_RECOVERY_FILE_PROCESS_SAME_FILE_ADD');
                break;
            case 4:
                $des = xphp_get_lang('UI_RECOVERY_FILE_PROCESS_SAME_FILE_RENAME');
                break;
        }
        return $des;
    }

    /**
     * 处理副本历史任务详情
     * @param array $details  details
     * @param int   $tasktype 任务类型
     * @return array|false
     */
    private function historyTaskDetailsHandlerCopy(array $details, int $module, $subtype = null)
    {
        if (empty($details)) {
            return false;
        }

        $vmdes = xphp_get_desc('Vm', 'CopyTaskStatus');
        $mouleType = xphp_get_config('module', 'MODULE_TYPE');
        $i = 0;
        foreach ($details as $d) {
            $details[$i]['transport_size'] = $d['transport_size'] ? v1_calsize($d['transport_size'], true) : 0;
            $details[$i]['write_size'] = $d['write_size'] ? v1_calsize($d['write_size'], true) : 0;
            $details[$i]['valid_size'] = $d['valid_size'] ? v1_calsize($d['valid_size'], true) : 0;
            $details[$i]['total_size'] = $d['total_size'] ? v1_calsize($d['total_size'], true) : 0;
            $details[$i]['error_code'] = $d['total_size'] ? $this->getCopyErrorCodeDes($d['item_status'], $d['error_code']) : '';
            $details[$i]['item_status'] = $d['item_status'] ? $vmdes[intval($d['item_status'])] : '';
            $details[$i]['transfer_speed'] = $d['item_status'] ? v1_calspeed(intval($d['transfer_speed'])) : 0;
            $details[$i]['timepoint_count'] = $d['timepoint_count'] ? intval($d['timepoint_count']) : 0;
            if ($module == $mouleType['NAS']) {
                if (stripos($d['item_name'], $d['ip'])) {
                    $details[$i]['item_name'] = $d['item_name'];
                } else {
                    $details[$i]['item_name'] = $d['ip'] . '( ' . $d['item_name'] . ')';
                }
            } elseif ($module == $mouleType['FS'] || $module == $mouleType['OS']) {
                if (stripos($d['item_name'], $d['ip'])) {
                    $details[$i]['item_name'] = $d['item_name'];
                } else {
                    $details[$i]['item_name'] = $d['item_name'] . '( ' . $d['ip'] . ')';
                }
                if ($subtype != 1) {
                    $details[$i]['item_name'] = $d['item_name'];
                }
            } else {
                $details[$i]['item_name'] = $d['item_name'];
            }
            //这里是从数据库的detail字段取得的,然后二次处理一些数据

            $i++;
        }
        return $details;
    }

    /**
     * 处理验证历史任务详情
     * @param array $details  details
     * @param int   $tasktype 任务类型
     * @return array|false
     */
    /**
     * 获取验证历史任务详情
     * @param array $details
     * @param array $info
     * @return array
     */
    private function historyTaskDetailsHandlerVerify(array $details, array $info)
    {
        if (empty($details)) {
            return [];
        }

        $vmNames = array_column($details, 'vm_name');
        if (empty($vmNames)) {
            return $details;
        }

        $historyuuid = $info['history_uuid'];

        $sql = "SELECT 
                    item_name,
                    item_uuid,
                    report_uuid
                  FROM 
                    sr_surebackup_report 
                  WHERE 
                    history_uuid = ?";
        $reports = $this->dbSelect($sql, array($historyuuid));
        // 构造以 item_uuid 为键，report_uuid 为值的MAP
        $reportMap = array_column($reports, 'report_uuid', 'item_uuid');

        $result = [];

        foreach ($details as $d) {
            $d['item_name'] = $d['vm_name'];
            $d['report_uuid'] = $reportMap[$d['vm_uuid']] ?? '';
            $result[] = $d;
        }

        return $result;
    }


    /**
     * 处理数据库历史任务详情
     * @param array   $details      details
     * @param int     $tasktype     任务类型
     * @param array   $info         info
     * @param ?string $errorDetails 错误详情
     * @return array
     */
    private function historyTaskDetailsHandlerDB(array $details, int $tasktype, array $info, ?string $errorDetails)
    {
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $allSourceRecoveryType = xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db');
        $i = 0;
        if (isset($details['resource_limiting_node_config'])) {
            return $details;
        }
        foreach ($details as $d) {
            $dbType = (int) $d['db_type'];
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            $details[$i]['transport_size'] = v1_calsize($d['transport_size'], true);
            $details[$i]['db_size'] = v1_calsize($d['total_size'] ?: 0, true);
            $details[$i]['real_size'] = v1_calsize($d['write_size'] ?: 0, true);
            $details[$i]['task_status'] = xphp_get_desc('Vm', 'VmTaskStatus')[intval($d['task_status'])];
            $details[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            $details[$i]['backup_mode'] = $this->getHistoryTaskType($tasktype, $d['mode'], $info['submodule_type']);
            $details[$i]['transfer_speed'] = v1_calspeed(intval($d['transfer_speed']));
            $details[$i]['average_speed'] = v1_calspeed(intval($info['average_speed']));
            $details[$i]['timepoint_des'] = $d['timepoint'] .
                '(' . $this->getTimepointTypeDes($d['mode'], $dbType) . ')';
            $details[$i]['error_details'] = $errorDetails;

            //恢复方式
            $details[$i]['recovery_mode_des'] =
                xphp_get_desc('Db', 'DB_RECOVERY_LEVEL_DES')[intval($d['recovery_level'])];
            $details[$i]['recovery_mode'] = intval($d['recovery_level']);
            if ($dbType == $allDbType['CACHE'] || $dbType == $allDbType['IRIS']) {  // Cache/IRIS与其他逻辑不一致
                $details[$i]['recovery_mode_des'] =
                    xphp_get_desc('Db', 'DB_RECOVERY_LEVEL_DES')[intval($d['recovery_mode'])];
                $details[$i]['recovery_mode'] = intval($d['recovery_mode']);
            }
            $details[$i]['timepoint_recovery_type'] = intval($d['recovery_type']);
            if ($d['recovery_level'] == xphp_get_config('db', 'DB_RECOVERY_TYPE')['COVER']) {
                // 定时恢复最新点
                if ($allSourceRecoveryType['LATEST_TIMEPOINT'] == $details[$i]['timepoint_recovery_type']) {
                    $details[$i]['recovery_mode_des'] = xphp_get_lang('UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY');
                } else {
                    switch ($dbType) {
                        case $allDbType['SQLSERVER']:
                        case $allDbType['SAPHANA']:
                            $details[$i]['recovery_mode_des'] = xphp_get_lang('UI_DB_ORIGINAL_COVERAGE_RECOVERY');
                            break;
                        default:
                            $details[$i]['recovery_mode_des'] =
                                xphp_get_lang('UI_DB_ORIGINAL_INSTANCE_COVERAGE_RECOVERY');
                            break;
                    }
                }
            } elseif ($d['recovery_level'] == xphp_get_config('db', 'DB_RECOVERY_TYPE')['CREATE']) {
                switch ($dbType) {
                    case $allDbType['KINGBASE']:
                    case $allDbType['POSTGRE']:
                    case $allDbType['UXDB']:
                    case $allDbType['HIGHGO']:
                    case $allDbType['OPENGAUSS']:
                    case $allDbType['ANTDB']:
                    case $allDbType['VASTBASE']:
                        $details[$i]['recovery_mode_des'] = xphp_get_lang('UI_DB_ADD_NEW_INSTANCE_RECOVERY');
                        break;
                    default:
                        $details[$i]['recovery_mode_des'] = xphp_get_lang('UI_DB_ADD_NEW_DATABASE_RECOVERY');
                        break;
                }
            }
            if (empty($d['new_db_name'])) {
                $details[$i]['new_db_name'] = $d['db_name'];
            }
            $details[$i]['new_db_password'] = $d['new_db_password'];

            if (
                $allTaskType['DB_RECOVERY'] == $tasktype ||
                $allTaskType['DRILL'] == $tasktype
            ) {  // 数据库恢复
                if ($dbType == $allDbType['ORACLE']) {  // Oracle有pfile
                    $details[$i]['pfile_content'] = $d['detail'];
                    $details[$i]['modify_pfile_flag'] = v1_parse_flag_to_bool($d['modify_pfile_flag']);
                } elseif ($allDbType['MONGODB'] == $dbType) {
                    $details[$i]['max_parallel_nums'] = (int) $d['max_object_transport_parallel_nums'];
                } elseif ($allDbType['TIDB'] == $dbType) {
                    // FIXME: 客户端并行数量待定
                    $details[$i]['max_parallel_nums'] = (int) $d['max_object_transport_parallel_nums'];
                } elseif ($dbType == $allDbType['CACHE'] || $dbType == $allDbType['IRIS']) {
                    $details[$i]['max_parallel_nums'] = (int) $d['max_object_transport_parallel_nums'];
                    if ($d['recovery_level'] == 2) {  // FIXME: 表空间恢复
                        $details[$i]['log_rollback_time'] = '0000-00-00 00:00:00';
                        $details[$i]['table_space_list'] = $d['table_space_list'];
                        foreach ($d['table_space_list'] as $tableSpace) {  // 显示是否显示回滚时间
                            if ($tableSpace['log_rollback_time'] != '0000-00-00 00:00:00') {
                                $details[$i]['log_rollback_time'] = $tableSpace['log_rollback_time'];
                                break;
                            }
                        }
                    }
                }
            }
            $details[$i]['instance_name'] = $d['instance_name'];
            $i++;
        }

        return $details;
    }

    /**
     * 处理主机历史任务详情
     * @param array $details  details
     * @param int   $tasktype 任务类型
     * @param $info     info
     * @return array
     */
    private function historyTaskDetailsHandlerOS($details, $tasktype, $info): array
    {
        $agentList = $this->getAllAgentList();
        $taskUuid = $info['task_uuid'];

        $i = 0;
        //如果是其他,直接获取模块描述
        $ptDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        foreach ($details as $d) {
            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            //得到应该展示的名称
            $details[$i]['display_name'] = empty($d['agent_uuid']) ? '--' : $this->getAgentNameByList(
                $agentList,
                $d['agent_uuid'],
                $d['os_name'],
                $d['agent_ip']
            );

            //获取备份点是否被删除信息
            $lang = xphp_get_lang('WEB_JOB_TIMEPOINT_NOE_EXIST');
            $timePoint = '';
            $timePointDeleteFlag = $this->getTimepointDeleteFlag($d['timepoint_uuid']);
            if ($timePointDeleteFlag) {
                $timePoint = $d['timepoint'] . '(' . $lang . ')';
            } else {
                $timePoint = $d['timepoint'];
            }

            //得到备份时间点
            $details[$i]['timepoint'] = empty($d['timepoint']) ? '--' : $d['timepoint'];
            //得到开始时间
            $details[$i]['start_transfer_time'] = empty($d['start_transfer_time']) ? '--' : $d['start_transfer_time'];
            //得到结束时间
            $details[$i]['end_transfer_time'] = empty($d['end_transfer_time']) ? '--' : $d['end_transfer_time'];
            //得到传输大小
            $details[$i]['transport_size'] = empty($d['transport_size']) ? '--' : v1_calsize($d['transport_size'], true);
            //得到总大小
            $details[$i]['os_size'] = empty($d['os_size']) ? '--' : v1_calsize($d['os_size'], true);
            //得到写入大小
            $details[$i]['write_size'] = empty($d['write_size']) ? '--' : v1_calsize($d['write_size'], true);
            //得到任务状态
            $details[$i]['task_status'] = empty($d['task_status']) ? '--' : $ptDes[intval($d['task_status'])];
            //得到错误码
            $details[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            //得到备份模式
            $details[$i]['backup_mode'] = empty($d['mode']) ? '--' : $this->getHistoryTaskType($tasktype, $d['mode']);
            //得到平均速度
            $details[$i]['average_speed'] = empty($d['average_speed']) ? '--' : v1_calspeed(intval($info['average_speed']));
            //得到传输速度
            $details[$i]['transfer_speed'] = empty($d['transfer_speed']) ? '--' : v1_calspeed(intval($d['transfer_speed']));
            //得到有效数据大小
            $details[$i]['os_valid_size'] = empty($d['os_valid_size']) ? '--' : v1_calsize(intval($d['os_valid_size']), true);
            //得到错误描述
            $details[$i]['error_code_des'] = $this->getErrorCodeDes($d['task_status'], $d['error_code']);
            //得到备份分区
            $details[$i]['volume_list'] = $d['volume_list'] ?? [];
            //恢复方式
            $details[$i]['recovery_level_des'] = empty($d['recovery_level']) ? '--' :
                xphp_get_desc('Db', 'DB_RECOVERY_LEVEL_DES')[intval($d['recovery_level'])];
            if (empty($d['new_os_name'])) {
                $details[$i]['new_os_name'] = empty($d['os_name']) ? '--' : $d['os_name'];
            }
            $i++;
        }
        return $details;
    }

    /**
     * 处理备份点是否被删除
     * @param string $taskUuid 任务uuid
     * @return string $timepoint 时间点
     */
    private function getTimepointDeleteFlag($timepointuuid)
    {
        $sql = 'select timepoint from bd_backup_timepoint where timepoint_uuid = ?';
        $sqlParams = array($timepointuuid);
        $data = $this->dbSelect($sql, $sqlParams);
        if (empty($data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 处理主机迁移历史任务详情
     * @param array $details  details
     * @param $tasktype 任务类型
     * @param $info     info
     * @return array
     */
    private function historyTaskDetailsHandlerOSMotion($details, $tasktype, $info): array
    {

        $pfDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $i = 0;
        foreach ($details as $d) {
            $sourcehoststr = $d['migration_sour_name'] ?? '--';
            if (!empty($d['migration_sour_ip'])) {
                $sourcehoststr .= '(' . $d['migration_sour_ip'] . ')';
            }

            $targethoststr = ($d['migration_tar_name'] ?? '--') . '(' . ($d['migration_tar_ip'] ?? '--') . ')';

            //这里是从数据库的detail字段取得的,然后二次处理一些数据
            $details[$i]['timepoint'] = empty($d['timepoint']) ? '--' : $d['timepoint'];
            //得到传输大小
            $details[$i]['transport_size'] = v1_calsize($d['transport_size'], true);
            //得到总大小
            $details[$i]['os_size'] = v1_calsize($d['os_size'], true);
            //得到写入大小
            $details[$i]['write_size'] = v1_calsize($d['write_size'], true);
            //得到任务状态
            $details[$i]['task_status'] = $pfDes[intval($d['task_status'])];
            //得到错误码
            $details[$i]['error_code'] = $this->getVMErrorCodeDes($d['task_status'], $d['error_code']);
            //得到错误描述
            $details[$i]['error_code_des'] = $this->getErrorCodeDes($d['task_status'], $d['error_code']);
            //迁移源主机
            $details[$i]['sourcehost'] = $sourcehoststr;
            //迁移目标主机
            $details[$i]['targethost'] = $targethoststr;

            $i++;
        }
        return $details;
    }

    /**
     * 处理卷CDP历史任务详情
     * @param string $details  details
     * @param $taskType 任务类型
     * @param $info     info
     * @return array
     */
    private function historyTaskDetailsHandlerVolCdp($details, $taskType, $info)
    {

        $pfDes = xphp_get_desc('Pf', 'TASKSTATUSDES');
        $i = 0;
        //查询takeover_agent_role，判断接管和验证任务
        $takeoveragentrole = $this->dbSelect('select takeover_agent_role from cdp_vol_task_takeover_info where task_uuid = ?', [$info['task_uuid']]);
        $takeoveragentrole = $takeoveragentrole[0]['takeover_agent_role'];
        $details[$i]['takeover_agent_role'] = $takeoveragentrole;
        foreach ($details as $d) {
            $details[$i]['task_status'] = $pfDes[intval($d['task_status'])];
            $details[$i]['agent_name'] = $this->agentStr($d['agent_name'], $d['hostname'], $d['agent_ip']);
            $details[$i]['agent_ip'] = $d['agent_ip'];
            $details[$i]['vol_display_name'] = $d['vol_display_name'];

            $details[$i]['vol_size'] = v1_calsize($d['vol_size'], true);
            $details[$i]['vol_complete_size'] = v1_calsize($d['vol_complete_size'], true);
            $details[$i]['real_complete_size'] = v1_calsize($d['real_complete_size'], true);
            $details[$i]['real_size'] = v1_calsize($d['real_size'], true);
            $details[$i]['draw_speed'] = v1_calsize($d['draw_speed'], true) . '/s';

            //双机镜像部分
            $details[$i]['standby_host_name'] = $this->agentStr(
                $d['standby_agent_name'],
                $d['standby_hostname'],
                $d['standby_host_ip']
            );
            $details[$i]['standby_host_ip'] = $d['standby_host_ip'];
            $details[$i]['standby_map_mount_point'] = $d['standby_map_mount_point'];
            //接管部分
            //增加takeover_agent_role，判断接管和验证任务
            $details[$i]['takeover_agent_role'] = $takeoveragentrole;
            $details[$i]['takeover_host_name'] = $this->agentStr(
                $d['takeover_agent_name'],
                $d['takeover_hostname'],
                $d['takeover_host_ip']
            );
            $details[$i]['takeover_host_ip'] = $d['takeover_host_ip'];
            $details[$i]['takeover_target_mount_point'] = $d['takeover_target_mount_point'];
            $details[$i]['takeover_time_point'] = $d['takeover_time_point'];
            //恢复任务部分
            $details[$i]['recovery_host_name'] = $this->agentStr(
                $d['recovery_agent_name'],
                $d['recovery_hostname'],
                $d['recovery_host_ip']
            );
            $details[$i]['recovery_host_ip'] = $d['recovery_host_ip'];
            $details[$i]['recovery_target_mount_point'] = $d['recovery_target_mount_point'];
            $details[$i]['recovery_time_point'] = $d['recovery_time_point'];


            $details[$i]['description'] = $this->getJobStatusShowPopover($info['error_code']);
            $i++;
        }
        return $details;
    }

    /**
     * 得到虚拟机错误描述
     * @param  $status    status
     * @param  $errorCode 错误码
     * @return mixed|string
     */
    private function getVMErrorCodeDes($status, $errorCode)
    {
        if (xphp_get_config('vm', 'VmTaskStatus')['ERROR'] == $status || $errorCode != 0) {
            $des = xphp_get_config('error', 'errorCodeDes')[xphp_get_config('error', 'errorCode')[$errorCode]];
        } else {
            $des = '';
        }
        return $des;
    }

    /**
     * 得到操作系统错误描述
     * @param int $errorCode 错误码
     * @return mixed|string
     */
    private function getOSMotionErrorCodeDes($errorCode)
    {
        $des = xphp_get_config('error', 'errorCodeDes')[xphp_get_config('error', 'errorCode')[$errorCode]];
        return $des;
    }

    /**
     * 得到历史任务的任务类型描述
     * @param int    $taskType      任务类型
     * @param int    $currentMode   //备份模式指完全差异增量这些模式
     * @param string $submoduleType 子模块类型
     * @return string
     */
    public function getHistoryTaskType($taskType, $currentMode, $submoduleType = ''): string
    {

        $des = '';
        $tasktype = xphp_get_config('task', 'TASKTYPE');
        $dbtype = xphp_get_config('db', 'DB_TYPE');
        $pfdesBmd = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $pfdesT = xphp_get_desc('Pf', 'TASKTYPEDES');

        if (in_array($taskType, [$tasktype['BACKUP'], $tasktype['DB_BACKUP'], $tasktype['OS_BACKUP'], $tasktype['KUBE_BACKUP']])) {
            if ($currentMode == 4) {
                if (
                    in_array(
                        $submoduleType,
                        [
                            $dbtype['ORACLE'],
                            $dbtype['DM'],
                            $dbtype['POSTGRE'],
                            $dbtype['KINGBASE'],
                            $dbtype['OPENGAUSS'],
                            $dbtype['UXDB'],
                            $dbtype['HIGHGO'],
                            $dbtype['VASTBASE'],
                            $dbtype['ANTDB']
                        ]
                    )
                ) {
                    //归档日志备份
                    $des = $pfdesBmd[5];
                } else {
                    //日志备份
                    $des = $pfdesBmd[$currentMode];
                }
            } else {
                $des = $pfdesBmd[$currentMode];
            }
        }
        if (in_array($taskType, [$tasktype['DB_BACKUP'], $tasktype['OS_BACKUP'], $tasktype['KUBE_BACKUP']])) {
            $des .= xphp_get_lang('WEB_PLATFORM_DES_BACKUP');
        } else {
            $des .= $pfdesT[$taskType];
        }
        return $des;
    }

    /**
     * 获得所有agent的name和ip信息
     * @return array
     */
    public function getAllAgentList()
    {
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent";
        $result = $this->dbSelect($sql);
        $info = array();
        foreach ($result as $each) {
            $info[] = array(
                'agent_uuid' => $each['agent_uuid'],
                'agent_name' => $each['agent_name'],
                'hostname' => $each['hostname'],
                'ip' => $each['ip'],
            );
        }
        return $info;
    }

    /**
     * 根据agent_uuid 查询出agent的信息并拼接成名字
     * @param $list      list
     * @param string $agentuuid 客户端uuid
     * @param $osname    操作系统名称
     * @param $osagentip 操作系统ip
     * @return string
     */
    public function getAgentNameByList($list, $agentuuid, $osname, $osagentip)
    {

        //如果没获取到主机则直接返回空
        if (empty($list)) {
            return empty($osname) ? '--' : $this->getAgentName($osname, '', $osagentip);
        }

        //开始循环获取主机
        foreach ($list as $each) {
            if ($each['agent_uuid'] == $agentuuid) {
                return $this->getAgentName($each['hostname'], $each['agent_name'], $each['ip']);
            }
        }

        return $this->getAgentName($osname, '', $osagentip);
    }

    /**
     * 过滤开始时间
     * @param  $startTime 开始时间
     * @param  $status    状态
     * @return string
     */
    public function getStartTIme($startTime, $status)
    {
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        if (
            in_array(
                $status,
                [
                    $taskStatus['RUNNING'],
                    $taskStatus['NETWORK_FAULT'],
                    $taskStatus['ABNORMAL'],
                    $taskStatus['SUCCESSED'],
                ]
            )
        ) {
            return $this->parseDate($startTime);
        }
        return xphp_get_config('app', 'TIMESPACE');
    }

    /**
     * 得到任务运行的持续时间
     * @param timestamp $startTime 开始时间
     * @param int       $status    任务状态
     * @return string
     */
    public function getTimeInterval($startTime, $status)
    {
        $taskStatus = xphp_get_config('task', 'TASKSTATUS');
        if (
            inArray(
                $status,
                [
                    $taskStatus['RUNNING'],
                    $taskStatus['NETWORK_FAULT'],
                    $taskStatus['ABNORMAL'],
                    $taskStatus['SUCCESSED'],
                ]
            )
        ) {
            $nowTime = (new Time())->getSystemTime();
            if (!$startTime || $startTime == 0) {
                return xphp_get_config('app', 'TIMESPACE');
            }
            $intval = $nowTime - $startTime;
            return v1_sec_to_time($intval);
        }

        return xphp_get_config('app', 'TIMESPACE');
    }

    /**
     * 如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * @param  $hostname  主机名
     * @param  $agentname 别名
     * @param $ip        ip
     * @return string (ip)
     */
    protected function getAgentName($hostname, $agentname, $ip)
    {
        $nameip = '(' . $ip . ')';
        if (empty($hostname) && !empty($agentname)) {
            return $agentname . $nameip;
        } elseif (!empty($hostname) && empty($agentname)) {
            return $hostname . $nameip;
        }

        return (($agentname == $ip) ? $hostname : $agentname) . $nameip;
    }

    /**
     * 得到副本错误描述
     * @param $status    状态
     * @param $errorCode 错误码
     * @return string
     */
    private function getCopyErrorCodeDes($status, $errorCode)
    {
        if (xphp_get_config('copy', 'COPY_TASK_STATUS')['COPY_ITEM_STATUS_FAILED'] == intval($status)) {
            $error = xphp_get_config('error');
            $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];
        } else {
            $des = '';
        }
        return $des;
    }

    /**
     * 得到备份时间点的类型
     * @param int $bakcupMode mode
     * @param $dbtype     type
     * @return string
     */
    public function getTimepointTypeDes(int $bakcupMode, $dbtype = 0): string
    {
        $pfdes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        if ($bakcupMode == 4) {
            $dbtypeArr = xphp_get_config('db', 'DB_TYPE');
            if (
                in_array(
                    $dbtype,
                    [
                        $dbtypeArr['DM'],
                        $dbtypeArr['ORACLE'],
                        $dbtypeArr['POSTGRE'],
                        $dbtypeArr['KINGBASE'],
                        $dbtypeArr['UXDB'],
                        $dbtypeArr['HIGHGO'],
                        $dbtypeArr['ANTDB'],
                        $dbtypeArr['OPENGAUSS'],
                        $dbtypeArr['VASTBASE'],
                    ]
                )
            ) {
                //归档日志备份
                $des = $pfdes[5];
            } else {
                //日志备份
                $des = $pfdes[$bakcupMode];
            }
        } else {
            $des = $pfdes[$bakcupMode];
        }

        return $des . xphp_get_lang('WEB_PLATFORM_DATA_BACKUP_POINT');
    }

    /**
     * 得到错误描述
     * @param int  $status    staus
     * @param int  $errorCode 错误码
     * @param bool $copyFlag  flag
     * @return string
     */
    public function getErrorCodeDes($status, $errorCode, $copyFlag = false): string
    {
        $des = '';
        if ($copyFlag) {
            if (xphp_get_config('copy', 'CopyTaskStatus')['WEB_PUBLIC_FAILURE'] == intval($status)) {
                $error = xphp_get_config('error');
                $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];
            }
            if ($des == null) {
                return '';
            }
            return $des;
        }

        $taskstatus = xphp_get_config('task', 'TASKSTATUS');
        if (in_array(intval($status), [$taskstatus['ABNORMAL'], $taskstatus['ERROR'], $taskstatus['NETWORK_FAULT']])) {
            $error = xphp_get_config('error');
            $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];
        }

        if ($des == null) {
            return '';
        }
        return $des;
    }

    /**
     * 根据条件组合客户端的名称
     * @param $agentName agentname
     * @param $hostName  hostname
     * @param $ip        ip
     * @return string
     */
    public function agentStr($agentName, $hostName, $ip): string
    {
        if (empty($agentName) && empty($hostName) && empty($ip)) {
            return '';
        }
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo = $agentName ? $agentName . '(' . $ip . ')' : $hostName . '(' . $ip . ')';
        } else {
            $hostInfo = $hostName . '(' . $ip . ')';
        }
        return $hostInfo;
    }

    /**
     * 根据任务状态得到显示状态等级
     * 1 success/2 warning/3 danger/4 info
     * @param int $errorCode 错误码
     * @return string
     */
    public function getJobStatusShowLevel(int $errorCode)
    {
        //异常的错误
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        //中止的错误
        $discontinue = array(
            'BD_TASK_BE_CANCELLED_ERROR'
        );
        $error = xphp_get_config('error', 'errorCode');
        if (in_array($error[$errorCode], $abnormal)) {
            return 2;
        }
        if (in_array($error[$errorCode], $discontinue)) {
            return 4;
        }
        $level = 3;
        if (0 == $errorCode) {
            $level = 1;
        }
        return $level;
    }

    /**
     * 根据任务状态得到显示的popover提示信息
     * @param int $errorCode 错误码
     * @return string
     */
    public function getJobStatusShowPopover(int $errorCode)
    {
        $error = xphp_get_config('error');
        $des = $error['errorCodeDes'][$error['errorCode'][$errorCode]];

        return $des . (($errorCode != 0)
            ? (',' . xphp_get_lang('WEB_OPHANDLER_ERROR_CODE') . ': #' . $errorCode) : '');
    }

    /**
     * 获取dbcdp的操作码
     * @param string $config 配置信息
     * @param int    $status 状态
     * @return array
     */
    private function getDbCdpOpCode(string $config, int $status): array
    {
        // 获取dbcdp配置
        $config = json_decode($config, true);

        $opCode = array(1, 2, 3, 4);
        if (empty($config)) {
            return $opCode;
        }

        if (
            $config['standbyhostInfo']['backuptype'] != xphp_get_config('db', 'DB_CDP_BACKUP_TYPE')['REALTIME_BACKUP']
        ) {
            //如果不是实时备份
            if ($config['highInfo']['takeover']['check']) {
                //如果接管开启
                //如果是业务接管或实时备份+业务接管
                //如果任务状态在接管中,启动或停止中
                $tasktype = xphp_get_config('task', 'TASKSTATUS');
                $opCodeArr = [
                    $tasktype['TAKEOVER'] => [11, 12],
                    $tasktype['TAKEOVER_STARTING'] => [11, 12],
                    $tasktype['TAKEOVER_STOPPING'] => [11, 12],
                ];
                $opCode = !empty($opCodeArr[$status]) ? $opCodeArr[$status] : [1, 2, 3, 4, 11, 12];
            }
        }
        return $opCode;
    }

    /**
     * 获取任务备份的部分任务基本信息(主要用于修改任务获取基本数据使用)
     * 备注: 目前只获取了部分公共模块的数据,有需要的自己在下面字段中添加,只能添加公共模块的字段!!!
     * @param string $taskuuid 任务uuid
     * @return array
     * @author liushuai@vinchin.com
     * @date 2023-7-3 11:21:00
     */
    public function getJobBackupInfo($taskuuid)
    {
        $sql = "select bt.task_name, bt.strategy_id, bt.node_uuid, bt.storage_uuid, bt.thread_num, 
       bt.strategy_group_uuid, bt.backup_server_ip, bt.transport_ip_segment, bt.node_pool_uuid,bt.storage_pool_uuid,
       bt.worm_flag, bt.virus_scan_flag,bt.integrity_check_flag, bt.ignore_resource_limiting_flag, 
       brs.strategy_type, brs.strategy_mode,
       brs.number,bts.encrypt_flag,bts.encrypt_method, bts.compress_flag,bts.network_pool_uuid, bts.network_uuid, bss.deduplication_flag, 
       bss.block_size, bss.compressed_flag, bss.encrypted_flag, bss.password_auto_flag, bss.password,bss.compress_method,bss.encrypt_method as bss_encrypt_method,
       bsrp.storage_pool_type ,
       btsc.worm_protection_time, btsc.virus_scan_config_list, btsc.integrity_check_strategy, 
       btsc.backup_integrity_check_full_error_policy,btsc.backup_integrity_check_inc_error_policy 
	   from bd_task bt 
       left join bd_reserved_strategy brs on bt.strategy_id = brs.strategy_id
	   left join bd_transport_strategy bts on bt.strategy_id = bts.strategy_id 
	   left join bd_storage_strategy bss on bt.strategy_id = bss.strategy_id 
       left join bd_storage_resource_pool bsrp on bt.storage_pool_uuid = bsrp.storage_pool_uuid
       left join bd_task_safe_config btsc on bt.task_uuid = btsc.task_uuid
        where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $ExchangeJobInfo = new ExchangeJobInfo;
        $info = array();
        if ($data) {
            $info = array(
                //任务UUID
                'task_uuid' => $taskuuid,
                //策略uuid
                'strategyuuid' => $data[0]['strategy_group_uuid'],
                //任务名
                'taskname' => $data[0]['task_name'],
                //时间策略
                'time_strategy' => $this->getTimeStrategyInfo($data[0]['strategy_id']),
                //限速策略
                'speed_strategy' => $this->getSpeedStrategy($taskuuid),
                //存储策略
                'storage_strategy' => array(
                    //获取重复数据删除
                    'deduplication' => v1_parse_flag_to_bool($data[0]['deduplication_flag']),
                    //获取数据块大小
                    'blocksize' => intval($data[0]['block_size']) / 1024,
                    //获取压缩存储
                    'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                    //压缩传输方法
                    'compress_method' => $data[0]['compress_method'],
                    //获取数据加密
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                    //获取加密算法
                    'encrypt_method' => intval($data[0]['bss_encrypt_method']),
                    //获取自动生成密码
                    'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                    //获取密码
                    'password' => base64_encode(v1_pt_pass_decrypt($data[0]['password'])),
                ),
                //保留策略
                'reserved_strategy' => $this->getReservedStrategy($data[0]['strategy_id'], $taskuuid) ?? [],
                //节点
                'node' => array(
                    //获取节点uuid
                    'nodeuuid' => $data[0]['node_uuid'],
                    //获取节点资源池
                    'node_pool_uuid' => $data[0]['node_pool_uuid'],
                    //获取存储uuid
                    'storageuuid' => $data[0]['storage_uuid'],
                    //获取存储资源池
                    'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                    //获取存储类型
                    'storage_pool_type' => $data[0]['storage_pool_type']
                ),
                //传输策略
                'transfer_strategy' => array(
                    //获取加密传输
                    'encrypt' => v1_parse_flag_to_bool($data[0]['encrypt_flag']),
                    // 获取加密传输算法
                    'encrypt_method' => $data[0]['encrypt_method'],
                    //获取压缩传输
                    'compress' => v1_parse_flag_to_bool($data[0]['compress_flag']),
                    //传输网络
                    'network' => $data[0]['network_uuid'],
                    //网络资源池
                    'network_pool_uuid' => $data[0]['network_pool_uuid'],
                ),
                //高级配置及高级策略
                'high_strategy' => array(
                    //获取线程数量
                    'threadnum' => intval($data[0]['thread_num']),
                ),
                //获取安全策略
                'safe_strategy' => array(
                    //获取worm开关
                    'worm_flag' => v1_parse_flag_to_bool($data[0]['worm_flag']),
                    //获取worm保护期限
                    'worm_protection_time' => intval($data[0]['worm_protection_time']),
                    //获取病毒是否开关
                    'virus_scan_flag' => v1_parse_flag_to_bool($data[0]['virus_scan_flag']),
                    //获取病毒检测配置
                    'virus_scan_config_list' => json_decode($data[0]['virus_scan_config_list'], true),
                    //获取完整性效验开关
                    'integrity_check_flag' => v1_parse_flag_to_bool($data[0]['integrity_check_flag']),
                    //获取完整性校验数据
                    'integrity_info' => array(
                        //获取效验周期
                        'integrity_check_strategy' => intval($data[0]['integrity_check_strategy']),
                        //获取完全备份点异常
                        'backup_integrity_check_full_error_policy' => intval($data[0]['backup_integrity_check_full_error_policy']),
                        //获取其他备份点异常
                        'backup_integrity_check_inc_error_policy' => intval($data[0]['backup_integrity_check_inc_error_policy']),
                    ),
                ),
                //忽略节点限制
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag']),
                //重试策略
                'retry_strategy' => $ExchangeJobInfo->getRetryStrategy($taskuuid),
            );
        }
        return $info;
    }

    /**
     * 获取任务限速策略列表信息
     * @param string $taskuuid 任务uuid
     * @return array
     */
    public function getSpeedStrategyInfo($taskuuid)
    {
        $sql = "select strategy_uuid, speed_limited_value, start_time, end_time, days, remark, strategy_type
                    from bd_task_speed_limit_strategy where task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskuuid));
        $info = array();
        foreach ($data as $d) {
            $value = intval($d['speed_limited_value']);
            $valueList = v1_calsize_to_value_and_unit($value);
            $info[] = array(
                'uuid' => $d['strategy_uuid'],
                'type' => $d['strategy_type'],
                'startTime' => $d['start_time'],
                'endTime' => $d['end_time'],
                'days' => $this->parseSpeedStrategyDay($d['days']),
                'value' => $value,
                'des' => $d['remark'],
                'unit' => $valueList['unit'] . '/s',
                'speednum' => intval($valueList['value'])
            );
        }
        return $info;
    }

    /**
     * 根据策略id得到时间策略信息
     * @param int $strategyID 策略id
     * @return {}
     */
    public function getTimeStrategyInfo($strategyID)
    {
        return (new Backup())->getTimeStrategyInfo($strategyID);
    }

    /**
     * 转换限速策略天数为一个数组,每一项为0,1
     * @param string $days 天
     * @return array
     */
    private function parseSpeedStrategyDay($days = '')
    {
        if (empty($days)) {
            return array();
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
    private function parseTimeStrategyDay($days = '')
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
     * @param int   $strategyType 类型
     * @param array $days         天
     * @return string  空字符串  s1 - s4
     */
    private function parseTimeStrategyFrequency($strategyType, $days)
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
     * 得到历史任务结果描述
     * @param int $errorCode 错误码
     * @see \app\v1\backupmanager\v0\logic\ReportInfo::getTaskReportData
     * @return string
     */
    public function getHistoryJobResultDes(int $errorCode)
    {
        //异常的错误
        $abnormal = array(
            'BD_TASK_ANBNORMAL_ERROR'
        );
        //中止的错误
        $discontinue = array(
            'BD_TASK_BE_CANCELLED_ERROR'
        );
        $errorCodeArr = xphp_get_config('error', 'errorCode');
        if (in_array($errorCodeArr[$errorCode], $abnormal)) {
            return xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL');
        }
        if (in_array($errorCodeArr[$errorCode], $discontinue)) {
            return xphp_get_lang('WEB_PLATFORM_DES_DISCONTINUE');
        }
        return $errorCode == 0 ? xphp_get_lang('WEB_PUBLIC_SUCCESS') : xphp_get_lang('WEB_PUBLIC_FAILURE');
    }

    /**
     * 得到数据库实时任务同步的详情
     *
     */
    private function getDBcdpDesSyncInfo(string $taskuuid)
    {
        $sqlHostInfo = "select cddt.delay_load_time,ba.hostname,ba.ip ,cddt.auto_takeover_flag from cdp_db_dr_task cddt 
                inner join bd_agent ba on ba.agent_uuid = cddt.source_agent_uuid
                where task_uuid = ?";

        $sqlInstanceInfo = "SELECT 
                 baa1.app_service_name AS source_app_service_name,
                 baa2.app_service_name AS target_app_service_name
                FROM 
                    cdp_db_dr_task cddt
                INNER JOIN 
                    bd_agent_app baa1 ON cddt.source_agent_uuid = baa1.agent_uuid
                INNER JOIN 
                    bd_agent_app baa2 ON cddt.target_agent_uuid = baa2.agent_uuid
                WHERE 
                    cddt.task_uuid = ?";

        $sqlFailbackInstanceInfo = "SELECT baa.app_service_name FROM cdp_db_dr_task_takeover_failback_info cddttfi 
		                            INNER JOIN bd_agent_app baa ON cddttfi.failback_target_agent_uuid = baa.agent_uuid 
                                    WHERE cddttfi.task_uuid = ?";

        $dataHostInfo = $this->dbSelect($sqlHostInfo, array($taskuuid));
        $dataInstanceInfo = $this->dbSelect($sqlInstanceInfo, array($taskuuid));
        $dataFailbackInstanceInfo = $this->dbSelect($sqlFailbackInstanceInfo, array($taskuuid));
        return array(
            'delay_load_time' => v1_sec_to_day_time($dataHostInfo[0]['delay_load_time']),
            'hostname' => $dataHostInfo[0]['hostname'],
            'ip' => $dataHostInfo[0]['ip'],
            'auto_takeover_flag' => $dataHostInfo[0]['auto_takeover_flag'],
            'sourceDbInstance' => $dataInstanceInfo[0]['source_app_service_name'],
            'targetDbInstance' => $dataInstanceInfo[0]['target_app_service_name'],
            'failbackDbInstance' => $dataFailbackInstanceInfo[0]['app_service_name'] ?? '--',
            'speedInfo' => $this->getSpeedStrategy($taskuuid),
        );
    }

    /**
     * 得到数据库实时任务同步的详情
     *
     */
    private function getDBcdpDesRecoverInfo(string $taskuuid)
    {
        $sql = "SELECT cddt.recovery_target_datetime,cddt.recovery_type,
                 baa1.app_service_name AS source_app_service_name,
                 baa2.app_service_name AS target_app_service_name
                FROM 
                    cdp_db_dr_task cddt
                INNER JOIN 
                    bd_agent_app baa1 ON cddt.source_agent_uuid = baa1.agent_uuid
                INNER JOIN 
                    bd_agent_app baa2 ON cddt.target_agent_uuid = baa2.agent_uuid
                WHERE 
                    cddt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        return array(
            'recovery_target_datetime' => $data[0]['recovery_target_datetime'],
            'recovery_type' => $data[0]['recovery_type'],
            'source_app_service_name' => $data[0]['source_app_service_name'],
            'target_app_service_name' => $data[0]['target_app_service_name'],
            'speedInfo' => $this->getSpeedStrategy($taskuuid),
        );
    }

    /**
     * 只显示传输网络所通信网段的IP
     * @param string $taskuuid 任务uuid
     * @return string
     */
    public function getNodeNetworkIp(string $taskuuid): string
    {
        $sql = "select bnn.ip from bd_transport_strategy bts 
                inner join  bd_node_network bnn on bts.network_uuid = bnn.network_uuid 
                where bts.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        return $data[0]['ip'] ?: '';
    }

    /**
     * 获取可用的任务名
     * @param string $taskName 任务名前缀
     * @return string
     */
    public function getValidTaskName(string $taskName): string
    {
        $oldTaskName = $taskName;
        for ($i = 1; $i < 1000; $i++) {
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if (empty($data) && empty($data1)) {
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }

    /**
     * 获取依赖的任务uuid
     * @param string $taskUuid 任务uuid
     * @return string
     */
    private function getDbDependTaskUuid(string $taskUuid): string
    {
        $dbTaskData = $this->dbSelect('SELECT depend_task_uuid FROM db_task WHERE task_uuid = ? ', [$taskUuid]);
        if (!is_array($dbTaskData) || !$dbTaskData) {
            return '';
        }
        return $dbTaskData[0]['depend_task_uuid'] ?: '';
    }

    /**
     * 获取任务的数据库类型
     * @param string $taskUuid 任务uuid
     * @return int
     */
    private function getDbType(string $taskUuid): int
    {
        $dbTaskData = $this->dbSelect('SELECT db_type FROM db_task WHERE task_uuid = ? ', [$taskUuid]);
        if (!is_array($dbTaskData) || !$dbTaskData) {
            return 0;
        }
        return intval($dbTaskData[0]['db_type']);
    }

    /**
     * 获取恢复任务详情的病毒扫描和完整性验证策略
     * @param string $jobUuid 任务uuid
     * @return array
     */
    public function getSafeConfig(string $jobUuid): array
    {
        $return = [
            'virus' => '',
            'complete' => '',
        ];
        $sql = 'select btsc.virus_scan_config_list,btsc.recovery_integrity_check_error_policy,bt.integrity_check_flag 
                from bd_task_safe_config btsc left join bd_task bt on btsc.task_uuid = bt.task_uuid 
                where btsc.task_uuid = ?';
        $data = $this->dbSelect($sql, [$jobUuid]);
        if (empty($data)) {
            return $return;
        }

        $completeArr = [
            0 => xphp_get_lang('UI_PLATFORM_INTERRUPT_RECOVERY'),
            1 => xphp_get_lang('UI_PLATFORM_CONTINUE_RECOVERY'),
            2 => xphp_get_lang('UI_PLATFORM_NONET_RECOVERY'),
        ];
        if ($data[0]['integrity_check_flag'] == 0) {
            $return['complete'] = xphp_get_lang('UI_PLATFORM_INTERGRITY_VERIFICACTION') . '：'
                . xphp_get_lang('WEB_DRILLS_NOT_CONFIG');
        } else {
            $return['complete'] = xphp_get_lang('UI_PLATFORM_INTERGRITY_VERIFICACTION') . '：'
                . ($completeArr[$data[0]['recovery_integrity_check_error_policy']] ?? '--');
        }
        $virus = '';
        $virusList = json_decode($data[0]['virus_scan_config_list'], true);
        //strategy_type  1 - 验证策略(备份或验证任务)， 2 - 未扫描策略， 3 - 健康策略， 4 - 已感染策略
        //  "recover_policy" 恢复策略： 1 - 直接恢复，2 - 杀除后恢复， 3 - 执行扫描
        // "interrupt_policy" 扫描中断策略： 1 - 扫出病毒后停止(验证任务停止检测，恢复任务停止恢复)， 2 - 扫出病毒后恢复到无网络环境， 3 - 扫出病毒后杀除并恢复
        $strategyList = [
            1 => xphp_get_lang('UI_PLATFORM_VERIFICACTION_STRATEGY') . ' -- ',
            2 => '<span style="color:#f19f00">' . xphp_get_lang('UI_PLATFORM_STRATEGY_NOSCAN') . '</span> -- ',
            3 => '<span style="color:#0fbf98">' . xphp_get_lang('UI_PLATFORM_STRATEGY_HEALTH') . '</span> -- ',
            4 => '<span style="color:#f1416c">' . xphp_get_lang('UI_PLATFORM_STRATEGY_INFECTED') . '</span> -- ',
        ];
        $backupPoint = xphp_get_lang('UI_PLATFORM_BACKUO_POINT_METHOD');
        $recoverList = [
            1 => $backupPoint . '：' . xphp_get_lang('UI_PLATFORM_DIRECT_RECOVERY'),
            2 => $backupPoint . '：' . xphp_get_lang('UI_PLATFORM_AFTER_KILLING_RECOVERY'),
            3 => $backupPoint . '：' . xphp_get_lang('UI_PLATFORM_AGAIN_SCAN'),
        ];
        $scanPoint = xphp_get_lang('UI_PLATFORM_ABNORMAL_SCAN');
        $interruptList = [
            1 => $scanPoint . '：' . xphp_get_lang('UI_PLATFORM_INTERRUPT_RECOVERY'),
            2 => $scanPoint . '：' . xphp_get_lang('UI_PLATFORM_NONET_RECOVERY'),
            3 => $scanPoint . '：' . xphp_get_lang('UI_PLATFORM_AFTER_KILLING_RECOVERY'),
        ];
        foreach ($virusList as $item) {
            $virus .= $strategyList[$item['strategy_type']] . $recoverList[$item['recover_policy']];
            if (in_array($item['interrupt_policy'], [1, 2, 3])) {
                $virus .= $interruptList[$item['interrupt_policy']];
            }
            $virus .= '</br>';
        }
        $return['virus'] = $virus;
        return $return;
    }

    /**
     * 解析任务阶段
     * @param $stage  任务节点值
     * @param bool $isReal 是否是实时任务
     * @return string
     */
    protected function getTaskStage($stage, $isReal = false): string
    {
        if ($isReal) {
            $stageArr = xphp_get_config('task', 'REAL_PROTECT_STAGE');
        } else {
            $stageArr = xphp_get_config('task', 'COMMON_STAGE');
        }
        return !empty($stageArr[$stage]) ? xphp_get_lang($stageArr[$stage]) : xphp_get_config('app', 'NULLSPACE');
    }

    /**
     * 获取网络显示标记
     * @param $taskUuid
     * @param $moduleType
     * @param $taskType
     * @return bool|void
     */
    public function getNetworkShowFlag($taskUuid, $moduleType, $taskType)
    {
        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        switch ($moduleType) {
            case $allModuleType['DB']:
                return $this->getDBNodeNetworkFlag($taskUuid, $taskType);
            case $allModuleType['OS']:
            case $allModuleType['FS']:
                return $this->getNormalNetworkFlag($taskUuid);
            case $allModuleType['M365']:
                return $this->getM365NetworkFlag($taskUuid);
            default:
                // 默认只判断备份对象是否为计算资源池
                return $this->judgeBackupTargetForShowNetwork($taskUuid);
        }
    }

    /**
     * 获取m365是否显示传输网络标记
     * @param string $taskuuid
     * @return boolean
     */
    public function getM365NetworkFlag($taskuuid)
    {
        $judgeRet = $this->judgeBackupTargetForShowNetwork($taskuuid);
        if (!$judgeRet) {
            return false;
        }
        $sql = "select bt.agent_uuid, ba.net_model from bd_agent ba, bd_task bt where ba.agent_uuid = bt.agent_uuid and bt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        foreach ($data as $d) {
            if (intval($d['net_model']) == 2) {
                return true;
            }
        }
        return true;
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid
     * @return boolean
     */
    public function getNormalNetworkFlag($taskuuid)
    {
        $judgeRet = $this->judgeBackupTargetForShowNetwork($taskuuid);
        if (!$judgeRet) {
            return false;
        }
        $sql = "select ba.agent_uuid, ba.net_model from bd_agent ba, bd_task_agent_list btal where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;  //定义传输网络标志
        foreach ($data as $d) {
            if (intval($d['net_model']) == 2) {
                $flag = true;
            }
        }
        if ($flag) {
            // 多主机备份不需要传输网络
            $agentUuidMap = [];
            foreach ($data as $d) {
                $agentUuidMap[$d['agent_uuid']] = $d['agent_uuid'];
            }
            if (count(array_values($agentUuidMap)) > 1) {
                $flag = false;
            }
        }
        return $flag;
    }

    /**
     * 备份目标为计算资源池时，不显示传输网络
     * @param $taskUuid
     * @return bool
     */
    public function judgeBackupTargetForShowNetwork($taskUuid)
    {
        $sql = "SELECT node_pool_uuid, storage_pool_uuid FROM bd_task WHERE task_uuid = ? ";
        $taskData = $this->dbSelect($sql, [$taskUuid]);
        if ($taskData[0]['node_pool_uuid']) {  // 备份目标为计算资源池不显示传输网络
            return false;
        }
        if ($taskData[0]['storage_pool_uuid']) {  // 备份目标为计算资源池不显示传输网络
            $sql = "SELECT storage_pool_type FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ";
            $storagePoolData = $this->dbSelect($sql, [$taskData[0]['storage_pool_uuid']]);
            if ($storagePoolData && $storagePoolData[0]['storage_pool_type'] == 1) {  // 集中式存储资源池不需要初始化传输网络
                return false;
            }
        }
        return true;
    }

    /**
     * 获取数据库任务是否显示传输网络标记
     * @param string $taskuuid
     * @param int $taskType
     * @return boolean
     */
    public function getDBNodeNetworkFlag($taskuuid, $taskType)
    {
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['DRILL']) {
            return false;
        }
        $judgeRet = $this->judgeBackupTargetForShowNetwork($taskuuid);
        if (!$judgeRet) {
            return false;
        }
        $sql = "SELECT ba.net_model, ba.agent_uuid, baa.cluster_uuid
                FROM db_list dl
                    INNER JOIN bd_agent ba ON ba.agent_uuid = dl.agent_uuid
                    INNER JOIN db_task dt ON dt.task_uuid = dl.task_uuid
                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = dl.agent_uuid AND baa.app_name = dl.instance_name AND baa.app_type = dt.db_type
                WHERE dl.task_uuid = ?";
        $data = $this->dbSelect($sql, [$taskuuid]);
        $flag = false;  //定义传输网络标志
        foreach ($data as $d) {
            if (intval($d['net_model']) == 2) {
                $flag = true;
            }
        }
        if ($flag) {
            // 集群不需要传输网络
            foreach ($data as $d) {
                if ($d['cluster_uuid']) {
                    $flag = false;
                }
            }
        }
        if ($flag) {
            // 数据库多主机备份不需要传输网络
            $agentUuidMap = [];
            foreach ($data as $d) {
                $agentUuidMap[$d['agent_uuid']] = $d['agent_uuid'];
            }
            if (count(array_values($agentUuidMap)) > 1) {
                $flag = false;
            }
        }

        return $flag;
    }

    /**
     * 解析对象返回业务类型
     * @param int $moduleType 模块标识
     * @param int $taskType   任务类型
     * @return array 1,备份。2实时保护，3复制
     */
    protected function getObject(int $moduleType, int $taskType = 0): array
    {
        $moduleTypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $taskTypeArr = xphp_get_config('task', 'TASKTYPE');
        $businessType = xphp_get_config('task', 'BUSINESS_TYPE');
        $return = $businessType['unknown'];
        // 数据备份对应的模块类型
        $dataArr = [
            $moduleTypeArr['VM'],
            $moduleTypeArr['OS'],
            $moduleTypeArr['FS'],
            $moduleTypeArr['DB'],
            $moduleTypeArr['NAS'],
            $moduleTypeArr['M365'],
            $moduleTypeArr['KUBERNETES'],
        ];
        // 数据复制对应的模块类型
        $copyArr = [
            $moduleTypeArr['VOL_CDP'],
            $moduleTypeArr['FILE_COPY'],
            $moduleTypeArr['DB_CDP'],
        ];

        if (in_array($moduleType, $dataArr)) {
            $return = $businessType['backup'];
        } elseif (in_array($moduleType, $copyArr)) {
            if ($moduleType == $moduleTypeArr['VOL_CDP'] && $taskType != $taskTypeArr['VOL_CDP_REPLICATION']) {
                $return = $businessType['vol'];
            } else {
                $return = $businessType['copy'];
            }
        }
        return $return;
    }
}
