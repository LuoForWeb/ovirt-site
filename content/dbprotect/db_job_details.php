<?php
include_once '../../tpl/permission.php';
include_once '../../content/platform/public/bs_table.php';
global $LANG;
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="../../css/dbprotect/custom_config.css" type="text/css"/>
<link rel="stylesheet" href="../../css/dbprotect/db_job_details.css" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <?php
        if ($_GET['orcPlan']) {
            $url = './content/platform/orchestration/orchestration_details.php?uuid=' . $_GET['orcPlan'];
            echo "<a href=\"$url\" class='ajaxify' name='task'><span>" . $LANG['UI_JOB_TASK_ORCHESTRATION_DETAILS'] . "</span></a>";
        } else {
            echo '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php"><span>' . $LANG['UI_PLATFORM_CURRENT_JOB'] . '</span></a>';
        }
        ?>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'] ?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail" id="jobDetail">
    <div class="col-md-12 job-detail__halftop">
        <div class="col-md-8 job-detail__halftop__charts">
            <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
            <input id="task_type" value="<?php echo $_GET['type']; ?>" class="display-none"></input>
            <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
            <!-- BEGIN DYNAMIC CHART PORTLET-->
            <div class="portlet-charts">
                <div class="portlet-charts__title">
                    <div class="caption" id="dbFlowTitle">
                        <i class="viconfont vicon-renwuliuliang"></i>
                        <span class="text"><?php echo $LANG['UI_JOB_FLOW'] ?></span>
                    </div>
                </div>
                <div class="portlet-charts__body">
                    <div class="portlet-charts__body__speedchart">
                        <div id="speedcharthover"></div>
                        <div id="speedchart"></div>
                    </div>
                    <div class="progressDiv display-none">
                        <div class="progressDiv__label">
							<span>
								<?php echo $LANG['UI_JOB_TOTAL_PROGRESS'] ?>
							</span>
                        </div>
                        <div class="progress progress-striped active">
                            <div id="total-progress" class="progress-bar progress-bar-success" role="progressbar"
                                 aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            </div>
                        </div>
                        <div class="taskprogress" id="progressright"></div>
                    </div>
                </div>
            </div>
            <!-- END DYNAMIC CHART PORTLET-->
        </div>

        <div class="col-md-4 job-detail__halftop__navtabs">
            <!-- BEGIN PORTLET-->
            <div class="portlet">
                <div class="portlet-body">
                    <!--BEGIN TABS-->
                    <div class="tabbable tabbable-custom">
                        <ul class="nav nav-tabs" id="recoverySummaryTitle">
                            <li class="active nolb">
                                <a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
                                    <i class="viconfont vicon-gaiyao1"></i>
                                    <span class="text"><?php echo $LANG['UI_JOB_SUMMARY'] ?></span>
                                </a>
                            </li>
                            <li id="recoveryConfigLi" style="display: none;">
                                <a href="#tab_1_6" data-toggle="tab" aria-expanded="false">
                                    <i class="viconfont vicon-shili1"></i>
                                    <span class="text"><?php echo $LANG['UI_CLIENT_APPLICATION_CONFIG'] ?></span>
                                </a>
                            </li>
                        </ul>
                        <div class="portlet-title init_tab_title display-none" id="backupSummaryTitle">
                            <i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
                        </div>
                        <div class="tab-content" id="backupRecoverySummaryContent">
                            <!-- 概要 -->
                            <div class="tab-pane active" id="tab_1_1">
                                <div class="portlet-body">
                                    <div class="row static-info taskOperateDiv ">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>:
                                        </div>
                                        <div class="col-md-8 col-operate value">
                                            <div class="btn-group dropdown-wrapper">
                                                <button type="button"
                                                        class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle"
                                                        data-toggle="dropdown" data-hover="dropdown" data-delay="1000"
                                                        data-close-others="true">
                                                    <?php echo $LANG['UI_PUBLIC_OPERATION'] ?>
                                                    <i class="fa fa-angle-down"></i>
                                                </button>
                                                <ul class="dropdown-menu" role="menu" id="dbOpList">
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_TASK_REPORT_TASK_NAME'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="taskName"
                                             style="word-break:break-word; max-width:300px;">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="taskType">
                                        </div>
                                    </div>
                                    <div class="row static-info display-none">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_ANOTHER_NAME_IP'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="taskIP">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="status">
                                        </div>
                                    </div>
                                    <!-- 任务阶段 -->
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="jobTaskStage">
                                        </div>
                                    </div>
                                    <div class="row static-info totalsizeDiv">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_TOTAL_SIZES'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="totalSize">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="currentSize">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_START_TIME'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="startTime">
                                        </div>
                                    </div>
                                    <div class="row static-info">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_JOB_INTERVAL_TIME'] ?>:
                                        </div>
                                        <div class="col-md-8 value" id="intervalTime">
                                        </div>
                                    </div>
                                    <!-- <div class="row static-info">
										<div class="col-md-4 name">
											<?php echo $LANG['UI_JOB_ESTIMATED_OVER_TIME'] ?>:
										</div>
										<div class="col-md-8 value" id="endTime">
										</div>
									</div> -->

                                    <!-- 公共配置 -->
                                    <!-- 更多配置 -->
                                    <div class="row static-info configuration_more flex-items-center">
                                        <div class="col-md-4 name">
                                            <?php echo $LANG['UI_PUBLIC_MORE_CONFIGURATION'] ?>:
                                        </div>
                                        <div class="col-md-8 value">
                                            <a class="colorgreen" id="configuration_moreConfiguration"
                                               data-target="#moreConfigurationDetailDrawer" data-toggle="drawer">
                                                <?php echo $LANG['UI_PUBLIC_MORE_DETAIL'] ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 配置	 -->
                            <!-- <div class="tab-pane display-none" id="tab_1_5">
                                <div class="portlet-body">
                                </div>
                            </div>//END: tab_1_5 -->

                            <!-- 恢复配置 -->
                            <div class="tab-pane display-none" id="tab_1_6">
                                <!-- 恢复方式 -->
                                <div class="row static-info recoveryDiv recoverTypeDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_RECOVERY_TYPE'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="recoverType">
                                    </div>
                                </div>
                                <!-- Oracle-时间点恢复方式 -->
                                <div class="row static-info recoveryDiv display-none oracleRecoveryTimepointDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIMEPOINT'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="oracleRecoveryTimepoint">
                                    </div>
                                </div>
                                <!-- Oracle-恢复时间 -->
                                <div class="row static-info recoveryDiv display-none oracleRecoveryTimeSelectDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_ORACLE_RECOVERY_TIME_SELECT'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="oracleRecoveryTimeSelect">
                                    </div>
                                </div>
                                <!-- Oracle-新实例配置 -->
                                <div class="row static-info recoveryDiv display-none oracleCreateNewInstanceDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG'] ?>:
                                    </div>
                                    <div class="col-md-8 value">
                                        <a href="javascript:void(0)" id="oracleCreateNewInstance" class="colorgreen">
                                            <?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_VIEW']; ?>
                                        </a>
                                    </div>
                                </div>
                                <!-- Oracle-数据恢复路径 -->
                                <div class="row static-info recoveryDiv display-none oracleRecoveryDataPathDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_ORACLE_DATA_PATH'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="oracleRecoveryDataPath">
                                    </div>
                                </div>
                                <!-- Oracle-恢复内容 -->
                                <div class="row static-info recoveryDiv display-none oracleRecoveryContentDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?>:
                                    </div>
                                    <div class="col-md-8 value">
                                        <a href="javascript:void(0)" id="oracleRecoveryContentBtn" class="colorgreen">
                                            <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT_VIEW']; ?>
                                        </a>
                                    </div>
                                </div>
                                <!-- Oracle-指定文件夹目录 -->
                                <div class="row static-info recoveryDiv oraclePathDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_SPECIFY_FOLDER_DIRECTORY'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="oraclePath">
                                    </div>
                                </div>
                                <!-- Oracle-导出恢复目录 -->
                                <div class="row static-info recoveryDiv exportDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_JOB_EXPORT_RECOVERY_DIR'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="exportDir">
                                    </div>
                                </div>
                                <!-- Oracle还原归档日志路径 -->
                                <div class="row static-info recoveryDiv oracleRestoreArchivelogPathDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_FOLDER'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="restoreArchivelogPath">
                                    </div>
                                </div>
                                <!-- Oracle还原归档日志方式 -->
                                <div class="row static-info recoveryDiv oracleRestoreArchivelogTypeDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_TYPE'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="oracleRestoreArchivelogType"></div>
                                </div>
                                <!-- Oracle还原归档日志时间范围 -->
                                <div class="row static-info recoveryDiv oracleRestoreArchivelogTimeDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_RANGE_TIME'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="oracleRestoreArchivelogTime">
                                    </div>
                                </div>
                                <!-- Oracle还原归档日志SCN范围 -->
                                <div class="row static-info recoveryDiv oracleRestoreArchivelogSCNDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RESTORE_ARCHIVELOG_RANGE_SCN'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="oracleRestoreArchivelogSCN">
                                    </div>
                                </div>

                                <!-- MySQL-打开数据库 -->
                                <div class="row static-info recoveryDiv mysqlOpenDbDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_OPEN_DB'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="mysqlOpenDb">
                                    </div>
                                </div>
                                <!-- MySQL-数据库启动方式 -->
                                <div class="row static-info recoveryDiv mysqlStartTypeDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_MYSQL_START_TYPE'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="mysqlStartType">
                                    </div>
                                </div>
                                <!-- MySQL-数据库服务名 -->
                                <div class="row static-info recoveryDiv commandDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_BACKUP_START_COMMAND'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="command">
                                    </div>
                                </div>
                                <!-- MySQL-命令行启动 -->
                                <div class="row static-info recoveryDiv mysqlCustomStartDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_MYSQL_CUSTOM_START'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="mysqlCustomStart">
                                    </div>
                                </div>
                                <!-- MySQL-停止命令 -->
                                <div class="row static-info recoveryDiv mysqlCustomStopDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_MYSQL_CUSTOM_STOP'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="mysqlCustomStop">
                                    </div>
                                </div>
                                <!-- MySQL-重定向目录 -->
                                <div class="row static-info recoveryDiv mysqlRedirectDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_REDIRECT_DIR'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="redirectPath">
                                    </div>
                                </div>

                                <!-- pg - 数据库文件路径 -->
                                <div class="row static-info recoveryDiv pgDataFilePathDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_DATABASE_FILE_PATH'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="pgDataFilePath">
                                    </div>
                                </div>
                                <!-- pg - 归档日志文件路径 -->
                                <div class="row static-info recoveryDiv pgArchivelogFilePathDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_ARCHIVELOG_FILE_PATH'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="pgArchivelogFilePath">
                                    </div>
                                </div>
                                <!-- DM、pg-指定文件夹路径 -->
                                <div class="row static-info recoveryDiv specifiedPathDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_SPECIFIED_FILE_PATH'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="specifiedPath">
                                    </div>
                                </div>
                                <!-- pg-自定义归档目录 -->
                                <div class="row static-info recoveryDiv pgCustomArchivelogDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_CUSTOM_ARCHIVE_DIR'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="pgCustomArchivelog">
                                    </div>
                                </div>

                                <!-- SAP HANA-时间点恢复方式 -->
                                <div class="row static-info recoveryDiv display-none sapHanaRecoveryTimeTypeDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_SAP_HANA_RECOVERY_TIMEPOINT'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="sapHanaRecoveryTimeType">
                                    </div>
                                </div>

                                <!-- SAP HANA-时间点恢复时间 -->
                                <div class="row static-info recoveryDiv display-none sapHanaRecoveryTimeDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_SAP_HANA_RECOVERY_TIME_SELECT'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="sapHanaRecoveryTime">
                                    </div>
                                </div>

                                <!-- SAP HANA-恢复备份点 -->
                                <div class="row static-info recoveryDiv display-none sapHanaRecoveryTimepointDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_SAP_HANA_RECOVERY_BACKUP_TIMEPOINT'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="sapHanaRecoveryTimepoint">
                                    </div>
                                </div>
                                <!-- SAP HANA、SQL Server-查看数据库配置 -->
                                <div class="row static-info recoveryDiv dbConfigDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_DB_CONFIG'] ?>:
                                    </div>
                                    <div class="col-md-8 value">
                                        <a href="javascript:void(0)" id="dbConfigBtn" class="colorgreen">
                                            <?php echo $LANG['UI_DB_RECOVERY_VIEW_DB_CONFIG']; ?>
                                        </a>
                                    </div>
                                </div>

                                <!-- TiDB-时间点恢复方式 -->
                                <div class="row static-info recoveryDiv display-none tidbRecoveryTimepointDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_TIDB_RECOVERY_TIMEPOINT'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="tidbRecoveryTimepoint">
                                    </div>
                                </div>
                                <!-- TiDB-恢复时间 -->
                                <div class="row static-info recoveryDiv display-none tidbRecoveryTimeSelectDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_TIDB_RECOVERY_TIME_SELECT'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="tidbRecoveryTimeSelect">
                                    </div>
                                </div>

                                <!-- Oracle、PG系、TiDB打开数据库 -->
                                <div class="row static-info recoveryDiv openDbDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_OPEN_DB'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="openDb">
                                    </div>
                                </div>
                                <!-- 日志回滚时间 -->
                                <div class="row static-info recoveryDiv logrollDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_LOG_ROLL_TIME'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="logtime">
                                    </div>
                                </div>
                                <!-- 归档日志回滚时间 -->
                                <div class="row static-info recoveryDiv archiverollDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_ARCHIVE_LOG_ROLL_TIME'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="archivelogTime">
                                    </div>
                                </div>
                                <!-- MongoDB-实例路径 -->
                                <div class="row static-info recoveryDiv mongodbInstancePathDiv display-none">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_DB_RECOVERY_MONGODB_INSTANCE_PATH'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="mongodbInstancePath">
                                    </div>
                                </div>
                            </div><!-- //END: tab_1_6 -->
                        </div>
                    </div>
                </div>
                <!--END TABS-->
            </div>
        </div>
        <!-- END PORTLET-->

        <!-- BEGIN db config MODAL -->
        <div id="dbConfigModal" class="modal xmodal fade form-horizontal " tabindex="-1"
             data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"> <?php echo $LANG['UI_DB_RECOVERY_DB_CONFIG'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div>
                        <table class="table table-hover" id="db-config-content">
                            <thead>
                            <tr role="row" class="heading">
                                <th width="15%" class="dbConfigOldName">
                                    <?php echo $LANG['UI_DB_RECOVERY_OLD_DB_NAME'] ?>
                                </th>
                                <th width="20%" class="createDbRecovery dbConfigNewName">
                                    <?php echo $LANG['UI_DB_RECOVERY_NEW_DB_NAME'] ?>
                                </th>
                                <th width="15%" class="createDbRecovery">
                                    <?php echo $LANG['UI_DB_DATABASE_FILE_PATH'] ?>
                                </th>
                                <th width="15%" class="createDbRecovery">
                                    <?php echo $LANG['UI_DB_LOG_FILE_PATH'] ?>
                                </th>
                                <th width="15%" class="dbConfigRollTime">
                                    <?php echo $LANG['UI_DB_LOG_ROLL_TIME'] ?>
                                </th>
                                <th width="15%" class="dbConfigRecoveryTime">
                                    <?php echo $LANG['UI_DB_RECOVERY_RECOVERY_TIME'] ?>
                                </th>
                                <th width="15%" class="dbConfigInitLogArea">
                                    <?php echo $LANG['UI_DB_INIT_LOG_AREA'] ?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default">
                    <?php echo $LANG['UI_PUBLIC_OFF_TWO']; ?>
                </button>
            </div>
        </div>
        <!-- END db config MODAL -->
    </div>
    <div class="col-md-12 job-detail__halfbottom">
        <!-- BEGIN TAB PORTLET-->
        <div class="job-detail__halfbottom__portlet">
            <div class="portlet-title">
                <ul class="nav nav-tabs">
                    <li class="active ">
                        <a href="#log" data-toggle="tab">
                            <i class="viconfont viconfont vicon-tasklog"></i>
                            <span class="text"><?php echo $LANG['UI_JOB_RUNING_LOG'] ?></span>
                        </a>
                    </li>
                    <li>
                        <a href="#dbs" data-toggle="tab">
                            <i class="viconfont vicon-ge_database_tasks"></i>
                            <span class="text"><?php echo $LANG['UI_DB_DATABASE_LIST'] ?></span>
                        </a>
                    </li>
                    <li id="historyli">
                        <a href="#history" data-toggle="tab">
                            <i class="viconfont viconfont vicon-histroytask"></i>
                            <span class="text"><?php echo $LANG['UI_JOB_HISTORY'] ?></span>
                        </a>
                    </li>
                    <li id="associatedTaskLi" style="display: none">
                        <a href="#associatedTask" data-toggle="tab">
                            <i class="viconfont vicon-guanlianrenwu"></i>
                            <span class="text"><?php echo $LANG['UI_DB_ASSOCIATETED_TASK'] ?></span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="portlet-body">
                <div class="tab-content">
                    <div class="tab-pane active running-log-pane" id="log">
                        <div class="time-range-wrapper">
                            <div id="running_log_daterangepicker_wrapper">
                                <i class="viconfont vicon-shijiankongjian"></i>
                                <span class="running-log-search"><?php echo $LANG['UI_TOOLS_TABLE_START_END_TIME'] ?></span>
                            </div>
                        </div>
                        <div class="running-log-content">
                            <ul id="runninglog"></ul>
                        </div>
                    </div>

                    <div class="tab-pane" id="dbs">
                        <div class="table-toolbar">
                            <div class="row">
                                <div class="col-md-12" style="font-size: 0">
                                    <div class="form-group search" id="detailSearchDiv">
                                        <input type="text" class="form-control" placeholder="<?php echo $LANG['UI_DB_DETAIL_SEARCH_DB_NAME_HOST_PLACEHOLDER'] ?>" id="detailSearch" />
                                        <div class="position0" style="width:auto;height:34px">
                                            <button class="b-btn clear position0" id="detailClearSearch">
                                                <i class="icon-close-small"></i>
                                            </button>
                                        </div>
                                        <div class="search-btn positionL0" style="width:auto;height:34px;">
                                            <button id="detailSearchBtn" class="b-btn search-btn">
                                                <i class="icon-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="btn-group dbStartDiv dropdown-wrapper ">
                                        <button type="button"
                                                class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle"
                                                data-toggle="dropdown" data-hover="dropdown" data-delay="1000"
                                                data-close-others="true">
                                            <?php echo $LANG['UI_PUBLIC_OPERATION'] ?>
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" id="">
                                            <li id="startFull">
                                                <button class="btn dropdown-menu__item me-0" type="button"><i
                                                            class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?>
                                                </button>
                                            </li>
                                            <li id="startIncr">
                                                <button class="btn dropdown-menu__item me-0" type="button"><i
                                                            class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?>
                                                </button>
                                            </li>
                                            <li id="startDiff">
                                                <button class="btn dropdown-menu__item me-0" type="button"><i
                                                            class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?>
                                                </button>
                                            </li>
                                            <li id="startLog">
                                                <button class="btn dropdown-menu__item me-0" type="button"><i
                                                            class="viconfont vicon-ge_log me-4"></i><?php echo $LANG['UI_DB_BACKUP_LOG'] ?>
                                                </button>
                                            </li>
                                            <li id="startArchiveLog">
                                                <button class="btn dropdown-menu__item me-0" type="button"><i
                                                            class="viconfont vicon-ge_log me-4"></i><?php echo $LANG['UI_BACKUP_ARCHIVE_LOG'] ?>
                                                </button>
                                            </li>
                                        </ul>
                                    </div><!--//END dbStartDiv-->
                                    <div class="btn-group oracleStartDiv dropdown-wrapper display-none">
                                        <button type="button"
                                                class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle"
                                                data-toggle="dropdown" data-hover="dropdown" data-delay="1000"
                                                data-close-others="true">
                                            <?php echo $LANG['UI_DB_ORACLE_ADVANCE_OPERATION'] ?>
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" id="">
                                            <li id="startCrosscheckFull">
                                                <button class="btn dropdown-menu__item me-0" type="button">
                                                    <i class="viconfont vicon-ge_play me-4"></i>
                                                    <?php echo $LANG['UI_BACKUP_FULL'] . '(' . $LANG['UI_DB_ORACLE_CROSSCHECK'] . ')' ?>
                                                </button>
                                            </li>
                                            <li id="startCrosscheckIncr">
                                                <button class="btn dropdown-menu__item me-0" type="button">
                                                    <i class="viconfont vicon-ge_increment me-4"></i>
                                                    <?php echo $LANG['UI_BACKUP_INCREMENT'] . '(' . $LANG['UI_DB_ORACLE_CROSSCHECK'] . ')' ?>
                                                </button>
                                            </li>
                                            <li id="startCrosscheckDiff">
                                                <button class="btn dropdown-menu__item me-0" type="button">
                                                    <i class="viconfont vicon-ge_differentia_backup me-4"></i>
                                                    <?php echo $LANG['UI_BACKUP_DIFFERENCE'] . '(' . $LANG['UI_DB_ORACLE_CROSSCHECK'] . ')' ?>
                                                </button>
                                            </li>
                                        </ul>
                                    </div><!--//END oracleStartDiv-->
                                </div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table class="table table-hover" id="dbstable">
                                <thead>
                                <tr role="row" class="heading">
                                    <th width="2%">
                                        <input type="checkbox" class="group-checkable">
                                    </th>
                                    <th width="2%">
                                        <?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
                                    </th>
                                    <th width="20%">
                                        <?php echo $LANG['UI_DB_DATABASE_NAME'] ?>
                                    </th>
                                    <th width="20%">
                                        <?php echo $LANG['UI_PLATFORM_AGENT'] ?>
                                    </th>
                                    <th width="10%">
                                        <?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>
                                    </th>
                                    <th width="8%">
                                        <?php echo $LANG['UI_JOB_TRANSFER_SIZE'] ?>
                                    </th>
                                    <th width="8%">
                                        <?php echo $LANG['UI_JOB_REAL_SIZE'] ?>
                                    </th>
                                    <th width="8%">
                                        <?php echo $LANG['UI_JOB_SPEED'] ?>
                                    </th>
                                    <th width="8%">
                                        <?php echo $LANG['UI_TENANT_DELETE_RESULT'] ?>
                                    </th>
                                    <th width="14%">
                                        <?php echo $LANG['UI_PUBLIC_DESCRIPTION'] ?>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <div class="alert alert-block alert-info fade in display-hide" id="dbbackuptips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class="alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                    <li>
                                        <?php echo $LANG['UI_DB_JOB_SELECT_DB_BACKUP_TIPS'] ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="history">
                        <div class="table-container">
                            <table class="table table-hover" id="historytable">
                                <thead>
                                <tr role="row" class="heading">
                                    <th width="4%">
                                        <?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
                                    </th>
                                    <th width="18%">
                                        <?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>
                                    </th>
                                    <th width="10%">
                                        <?php echo $LANG['UI_TENANT_DELETE_RESULT'] ?>
                                    </th>
                                    <th width="14%">
                                        <?php echo $LANG['UI_JOB_TRANSFER_SIZE'] ?>
                                    </th>
                                    <th width="14%">
                                        <?php echo $LANG['UI_JOB_REAL_SIZE'] ?>
                                    </th>
                                    <th width="20%">
                                        <?php echo $LANG['UI_JOB_START_TIME'] ?>
                                    </th>
                                    <th width="20%">
                                        <?php echo $LANG['UI_JOB_OVER_TIME'] ?>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 关联任务 -->
                    <div class="tab-pane" id="associatedTask">
                        <div class="table-container">
                            <table class="table" id="associatedTaskTable"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END TAB PORTLET-->

    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN DRAWER -->
<!-- BEGIN SCRIPT CONTENT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="scriptContentDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <!-- header -->
        <div class="drawer-header" style="padding: 16px 20px">
            <h4 class="drawer-title" style="margin: 0; height: 20px;">
                <span>
				<i class="viconfont vicon-jiaobenguanli"></i>
                </span>
                <span style="position: relative; top: -1px; font-size: 16px; color: #333;"
                      class="name"><?php echo $LANG['WEB_LOG_TASK_ERROR_DETAIL'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <div class="drawer-body" style="padding: 20px">
            <div class="portlet-body" style="height: 100%">
                <div class="form-group" style="height: 20px; margin-bottom: 16px">
                    <label class="col-md-3" for=""><?php echo $LANG['UI_PUBLIC_SCRIPT_TYPE'] ?></label>
                    <div class="col-md-6">
                        <div id="scriptContentType"></div>
                    </div>
                </div>
                <div class="col-md-12 pd0" style="height: calc(100% - 36px)">
                    <pre id="scriptContent" style="height: 100%"></pre>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default cancel" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END SCRIPT CONTENT DRAWER -->

<!-- BEGIN DB VALIDATE REPORT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="dbValidateReportDrawer"
     style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="document" data-db-index="">
        <!-- header -->
        <div class="drawer-header" style="padding: 16px 20px">
            <h4 class="drawer-title" style="margin: 0; height: 20px;">
                <span>
                    <i class="viconfont vicon-baogaoliebiao"></i>
                </span>
                <span style="position: relative; top: -1px; font-size: 16px; color: #333;"
                      class="name"><?php echo $LANG['UI_DB_RECOVERY_VALIDATE_REPORT'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body" style="padding: 40px 28px">
            <div class="portlet-body" style="height: 100%">
                <div id="dbValidateReport"></div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="col-md-9" style="float: right; padding-right: 10px;">
                    <button type="button" class="btn green-haze btn-confirm send-email" style="width: 100px">
                        <i class="viconfont vicon-fasongyoujian"></i>
                        <span><?php echo $LANG['UI_PUBLIC_SEND_EMAIL'] ?></span>
                    </button>
                    <button type="button" class="btn green-haze btn-confirm download" style="width: 72px">
                        <i class="viconfont vicon-baogaoxiazaishijian"></i>
                        <span><?php echo $LANG['UI_PUBLIC_DOWNLOAD'] ?></span>
                    </button>
                    <button type="button" class="btn default cancel"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END DB VALIDATE REPORT DRAWER -->

<!-- BEGIN MORE CONFIGURATION DETAIL DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="moreConfigurationDetailDrawer"
     style="width: 600px">
    <div class="drawer-content drawer-content-scrollable" role="document" data-db-index="">
        <!-- header -->
        <div class="drawer-header" style="padding: 16px 20px">
            <h4 class="drawer-title" style="margin: 0; height: 20px;">
                <span>
                    <i class="viconfont vicon-baogaoliebiao"></i>
                </span>
                <span style="position: relative; top: -1px; font-size: 16px; color: #333;"
                      class="name"><?php echo $LANG['UI_PUBLIC_MORE_DETAIL'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
            <!-- 备份节点 -->
            <div class="config-detail-wrap__group strategy-group" id="recoveryBackupNodeWrapper">
                <!-- 标题 -->
                <div class="strategy-group__header">
                    <i class="viconfont vicon-shuju me-8"></i>
                    <span class="strategy-group__header__text"><?php echo $LANG['UI_JOB_BACKUP_NODE'] ?></span>
                </div>
                <!-- 备份节点 -->
                <div class="strategy-group__form">
                    <div class="strategy-group__form__item align-items-baseline recoveryDiv nodeDiv">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_BACKUP_NODE'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="nodeReInfo"></div>
                    </div>
                </div>
            </div>
            <!-- 通用策略 -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-celve1 me-8"></i><span
                            class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
                </div>
                <!-- BEGIN TIME STRATEGY STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span>
                    <span class="strategy-group__title__text"><?php echo $LANG['WEB_COMMON_TIME_STRATEGY'];?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 时间策略 -->
                    <!-- 创建/修改时间 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_CREATE_TIME'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="createTime"></div>
                    </div>

                    <!-- 下次开始时间 -->
                    <div class="strategy-group__form__item align-items-baseline backup_div next-time-div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8">
							<span class="label label-success" id="nextTime"></span>
                        </div>
                    </div>

                    <!-- 备份方式 -->
                    <div class="strategy-group__form__item align-items-baseline backup_div time-strategy-backup-type-div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_TYPE'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="timeStrategyBackupType"></div>
                    </div>

                    <!-- 完全备份 -->
                    <div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div full-div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_FULL'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="fulldes"></div>
                    </div>

                    <!-- 增量备份 -->
                    <div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div increment-div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_INCREMENT'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="incdes"></div>
                    </div>

                    <!-- 差异备份 -->
                    <div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div difference-div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="diffdes"></div>
                    </div>

                    <!-- 永久增量备份 -->
                    <div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div permanent-increment-div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_PERMANENT_INCREMENT'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="pincrdes"></div>
                    </div>

                    <!-- 日志/归档日志备份 -->
                    <div class="strategy-group__form__item align-items-baseline backup_div backup_time_strategy_div log-archivelog-div">
                        <div class="strategy-group__form__item__label col-md-4 display-none backup_time_strategy_log_div">
                            <?php echo $LANG['UI_DB_BACKUP_LOG'] ?>:
                        </div>
                        <div class="strategy-group__form__item__label col-md-4 display-none backup_time_strategy_archivelog_div">
                            <?php echo $LANG['UI_BACKUP_ARCHIVE_LOG'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="logdes"></div>
                    </div>

                    <!-- 恢复时间策略 -->
                    <!-- 时间策略 -->
                    <div class="strategy-group__form__item align-items-baseline recovery_div recovery-time-strategy-div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['WEB_COMMON_TIME_STRATEGY'] ?>:
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="recoveryTimeStrategy"></div>
                    </div>
                </div>
                <!-- END TIME STRATEGY STRATEGY -->

                <!-- BEGIN SPEED LIMIT STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-8"></span><span
                            class="strategy-group__title__text"><?php echo $LANG['WEB_COMMON_SPEED_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 限速策略 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['WEB_COMMON_SPEED_STRATEGY'] ?></div>
                        <div id="speed_limit_backup" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END SPEED LIMIT STRATEGY -->

                <!-- BEGIN STORAGE STRATEGY -->
                <div class="strategy-group__title is-backup-static-info storageStrategyDetailTitle">
                    <span class="decoration me-8"></span><span
                            class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form is-backup-static-info storageStrategyDetailContent">
                    <!-- 存储设备 -->
                    <div class="strategy-group__form__item strategy-backup-storage">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_JOB_STORAGE_DEV'] ?></div>
                        <div id="storage_device" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 备份节点 -->
                    <div class="strategy-group__form__item strategy-backup-node">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_JOB_BACKUP_NODE'] ?></div>
                        <div id="backup_node" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 重复数据删除 -->
                    <div class="strategy-group__form__item backup-deduplication-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_DEDUPLICATION'] ?></div>
                        <div id="backup_deduplication" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 压缩存储 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?></div>
                        <div id="compress_storage" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 压缩等级 -->
                    <div class="strategy-group__form__item compress-method-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'] ?></div>
                        <div id="compress_method" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 数据加密 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?></div>
                        <div id="encrypt_storage" class="strategy-group__form__item__value col-md-8"><span
                                    class="label label-warning">关闭</span></div>
                    </div>
                    <!-- 存储加密算法 -->
                    <div class="strategy-group__form__item display-none storage-encrypt-method-form-item"
                         style="display: none;">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
                        <div id="encrypt_storage_method" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 自动生成密码 -->
                    <div class="strategy-group__form__item display-none auto-password-form-item mb-20"
                         style="display: none;">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?></div>
                        <div id="storage_auto_password" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END STORAGE STRATEGY -->

                <!-- BEGIN RESERVE STRATEGY -->
                <div class="reserve-div reserveStrategyDetailWrapper">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
                    </div>
                    <div class="strategy-group__form mb-20">
                        <!-- 保留策略 -->
                        <div class="strategy-group__form__item reserve-strategy-form-item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></div>
                            <div id="reserve_strategy" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
                <!-- END RESERVE STRATEGY -->
            </div>

            <!-- 传输策略 -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-a-chuanshu me-8"></i><span
                            class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 加密传输 -->
                    <div class="strategy-group__form__item backup-encrypt-transmit-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></div>
                        <div id="backup_encrypt_transmit" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 传输加密算法 -->
                    <div class="strategy-group__form__item backup-transfer-encrypt-method-form-item display-none"
                         style="display: none;">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
                        <div id="backup_transmit_encrypt_method" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                    <!-- 传输网络 -->
                    <div class="strategy-group__form__item backup-transmit-network-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?></div>
                        <div id="backup_transmit_network" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- MongoDB-传输线程 -->
                    <div class="strategy-group__form__item mongodbThreadNumDiv">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?></div>
                        <div id="mongodbThreadNum" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- Oracle-通道数 -->
                    <div class="strategy-group__form__item oracleChannelCountDiv">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_CHANNEL_NUM'] ?></div>
                        <div id="oracleChannelCount" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- SAP Hana-通道数 -->
                    <div class="strategy-group__form__item sapHanaChannelCountDiv">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_SAPHANA_CHANNEL_NUM'] ?></div>
                        <div id="sapHanaChannelCount" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
            </div>

            <!-- 安全策略 -->
            <div class="config-detail-wrap__group strategy-group is-backup-static-info" id="safeStrategyDiv">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-anquancelve me-8"></i><span
                            class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- WORM防护 -->
                    <div class="strategy-group__form__item backupSafeStrategyItem wormConfigDiv">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT'] ?></div>
                        <div id="backup_worm_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- WORM保护期限 -->
                    <div class="strategy-group__form__item backup_worm_date-form-item backupSafeStrategyItem wormConfigDiv"
                         style="display: none;">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD'] ?></div>
                        <div id="backup_worm_date" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                    <!-- 病毒检测 -->
                    <!-- <div class="strategy-group__form__item backupSafeStrategyItem">
                        <div class="strategy-group__form__item__label col-md-4">病毒检测</div>
                        <div id="virus_check_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div> -->
                    <!-- 病毒检测方式 -->
                    <!-- <div class="strategy-group__form__item virus-check-way-div backupSafeStrategyItem" style="display: none;">
                        <div class="strategy-group__form__item__label col-md-4">病毒检测方式</div>
                        <div id="virus_check_way" class="strategy-group__form__item__value col-md-8"></div>
                    </div> -->
                    <!-- 完整性校验 -->
                    <div class="strategy-group__form__item backupSafeStrategyItem integrityCheckItemDiv">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK'] ?></div>
                        <div id="integrity_check_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 恢复-完整性校验 -->
                    <div class="strategy-group__form__item recoverySafeStrategyItem">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK_STRATEGY'] ?></div>
                        <div id="integrity_verification_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
            </div>

            <!-- 高级配置 -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-gaojipeizhi me-8"></i><span
                            class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
                </div>

                <!-- 归档日志 -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_archivelogDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_ARCHIVELOG'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- Oracle-归档日志备份次数 -->
                        <div class="strategy-group__form__item"
                             id="configuration_detail_oracle_archivelogBackupTimesCheckDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_TIMES'] ?></div>
                            <div id="configuration_detail_oracle_archivelogBackupTimesCheck"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-最大重复备份次数 -->
                        <div class="strategy-group__form__item"
                             id="configuration_detail_oracle_archivelogBackupTimesDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_TIMES_TITLE'] ?></div>
                            <div id="configuration_detail_oracle_archivelogBackupTimes"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-归档日志备份天数 -->
                        <div class="strategy-group__form__item"
                             id="configuration_detail_oracle_archivelogBackupDaysCheckDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_DAYS'] ?></div>
                            <div id="configuration_detail_oracle_archivelogBackupDaysCheck"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-日志最近备份天数 -->
                        <div class="strategy-group__form__item"
                             id="configuration_detail_oracle_archivelogBackupDaysDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_BACKUP_ARCHIVELOG_DAYS_TITLE'] ?></div>
                            <div id="configuration_detail_oracle_archivelogBackupDays"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-归档日志保留策略 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_deleteArchivelogDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_DELETE_ARCHIVELOG_TITLE'] ?></div>
                            <div id="configuration_detail_oracle_deleteArchivelog"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-检查归档日志文件 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_checkArchivelogDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_CHECK_ARCHIVELOG'] ?></div>
                            <div id="configuration_detail_oracle_checkArchivelog"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- DM-删除归档日志 -->
                        <div class="strategy-group__form__item" id="configuration_detail_dm_deleteArchivelogDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_IF_DELETE_ARCHIVE_LOG'] ?></div>
                            <div id="configuration_detail_dm_deleteArchivelog"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- PG-删除归档日志配置 -->
                        <div class="strategy-group__form__item" id="configuration_detail_pg_deleteArchivelogDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_IF_DELETE_ARCHIVE_LOG'] ?></div>
                            <div id="configuration_detail_pg_deleteArchivelog"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- PG-归档日志告警 -->
                        <div class="strategy-group__form__item" id="configuration_detail_pg_archivelogAlarmDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ARCHIVE_STORAGE_ALARM'] ?></div>
                            <div id="configuration_detail_pg_archivelogAlarm"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- PG-告警指标 -->
                        <div class="strategy-group__form__item" id="configuration_detail_pg_archivelogAlarmTypeDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STORAGE_ALERT_TYPE'] ?></div>
                            <div id="configuration_detail_pg_archivelogAlarmType"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- PG-告警閾值 -->
                        <div class="strategy-group__form__item"
                             id="configuration_detail_pg_archivelogAlarmThresholdDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STORAGE_ALERT_THRESHOLD'] ?></div>
                            <div id="configuration_detail_pg_archivelogAlarmThreshold"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>

                <!-- 异常处理 -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_warningDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_WARNING'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- Oracle-跳过不可访问文件 -->
                        <div class="strategy-group__form__item"
                             id="configuration_detail_oracle_skipInaccessibleFileFlagDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_INACCESSIBLE_FILE'] ?></div>
                            <div id="configuration_detail_oracle_skipInaccessibleFileFlag"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-跳过脱机文件 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_skipOfflineFileFlagDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_OFFLINE_FILE'] ?></div>
                            <div id="configuration_detail_oracle_skipOfflineFileFlag"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-跳过数据文件坏块 --><!-- 这里放在数据库列表详情页里面 -->
                        <!-- <div class="strategy-group__form__item" id="configuration_detail_oracle_skipDatafileBadBlockDiv">
            	            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_DATAFILE_BAD_BLOCK'] ?></div>
            	            <div id="configuration_detail_oracle_skipDatafileBadBlock" class="strategy-group__form__item__value col-md-8"></div>
            	        </div> -->
                    </div>
                </div>

                <!-- 校验 -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_checkDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_CHECK'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- SQL Server-检查数据库 -->
                        <div class="strategy-group__form__item" id="configuration_detail_sqlserver_checkDatabaseDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_CHECK_DATABASE'] ?></div>
                            <div id="configuration_detail_sqlserver_checkDatabase"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- SQL Server-校验 -->
                        <div class="strategy-group__form__item" id="configuration_detail_sqlserver_checksumDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_SQLSERVER_CHECK_SUM'] ?></div>
                            <div id="configuration_detail_sqlserver_checksum"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>

                <!-- 有效数据 -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_validateDataDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_VALIDATE_DATA'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                    </div>
                </div>

                <!-- 快照 -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_snapshotDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_SNAPSHOT'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                    </div>
                </div>

                <!-- 增量 -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_incrementDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_INCREMENT'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                    </div>
                </div>

                <!-- 性能优化 -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_performanceDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_PERFORMANCE'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 数据块大小 -->
                        <div class="strategy-group__form__item" id="configuration_detail_dataBlockSizeDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_BACKUP_BLOCK_SIZE'] ?></div>
                            <div id="configuration_detail_dataBlockSize"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- SQL Server-压缩 -->
                        <div class="strategy-group__form__item" id="configuration_detail_sqlserver_compressDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_SQLSERVER_COMPRESS'] ?></div>
                            <div id="configuration_detail_sqlserver_compress"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- Oracle-压缩 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_compressDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_COMPRESS'] ?></div>
                            <div id="configuration_detail_oracle_compress"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-设置filesperset配置 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_filesPersetDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_FILES_PERSET'] ?></div>
                            <div id="configuration_detail_oracle_filesPerset"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-数据文件filesperset个数 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_filesPersetDatafileDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_FILES_PERSET_DATAFILE'] ?></div>
                            <div id="configuration_detail_oracle_filesPersetDatafile"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-归档日志文件filesperset个数 -->
                        <div class="strategy-group__form__item"
                             id="configuration_detail_oracle_filesPersetArchivelogDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_FILES_PERSET_ARCHIVELOG'] ?></div>
                            <div id="configuration_detail_oracle_filesPersetArchivelog"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-BCT配置 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_bctFlagDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_ENABLE_BCT_FLAG'] ?></div>
                            <div id="configuration_detail_oracle_bctFlag"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-多段传输 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_setSectionSizeFlagDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_SET_SECTION_SIZE_FLAG'] ?></div>
                            <div id="configuration_detail_oracle_setSectionSizeFlag"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-分块大小 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_setSectionSizeDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_SET_SECTION_SIZE'] ?></div>
                            <div id="configuration_detail_oracle_setSectionSize"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- Oracle-自定义RMAN命令 -->
                        <div class="strategy-group__form__item" id="configuration_detail_oracle_customRmanCmdDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_ORACLE_CUSTOM_RMAN_CMD'] ?></div>
                            <div id="configuration_detail_oracle_customRmanCmd"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- MySQL-处理线程 -->
                        <div class="strategy-group__form__item" id="configuration_detail_mysql_parallelDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_MYSQL_PARALLEL'] ?></div>
                            <div id="configuration_detail_mysql_parallel"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- MySQL-源端压缩 -->
                        <div class="strategy-group__form__item" id="configuration_detail_mysql_srcCompressedDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_MYSQL_SRC_COMPRESSED'] ?></div>
                            <div id="configuration_detail_mysql_srcCompressed"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- DM-压缩 -->
                        <div class="strategy-group__form__item" id="configuration_detail_dm_compressDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_DM_COMPRESS'] ?></div>
                            <div id="configuration_detail_dm_compress"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- DM-压缩等级 -->
                        <div class="strategy-group__form__item" id="configuration_detail_dm_compressLevelDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_DM_COMPRESS_LEVEL'] ?></div>
                            <div id="configuration_detail_dm_compressLevel"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- PG-源端压缩 -->
                        <div class="strategy-group__form__item" id="configuration_detail_pg_srcCompressedDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_SRC_COMPRESS'] ?></div>
                            <div id="configuration_detail_pg_srcCompressed"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- SAP HANA-压缩 -->
                        <div class="strategy-group__form__item" id="configuration_detail_saphana_compressDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_SAPHANA_COMPRESS'] ?></div>
                            <div id="configuration_detail_saphana_compress"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- MongoDB-客户端并行数量 -->
                        <div class="strategy-group__form__item" id="configuration_detail_mongodb_parallelNumDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_CLIENT_PARALLEL_NUM'] ?></div>
                            <div id="configuration_detail_mongodb_parallelNum"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!-- TIDB-压缩算法 -->
                        <div class="strategy-group__form__item" id="configuration_detail_tidb_compressMethodDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_TIDB_COMPRESS_METHOD'] ?></div>
                            <div id="configuration_detail_tidb_compressMethod"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- TIDB-压缩等级 -->
                        <div class="strategy-group__form__item" id="configuration_detail_tidb_compressLevelDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_DB_TIDB_COMPRESS_LEVEL'] ?></div>
                            <div id="configuration_detail_tidb_compressLevel"
                                 class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>

                <!-- BEGIN RETRY STRATEGY -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_retryStrategyDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 网络重连次数 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?></div>
                            <div id="network_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 网络重连间隔时间 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?></div>
                            <div id="network_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常自动重试 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?></div>
                            <div id="op_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常重连次数 -->
                        <div class="strategy-group__form__item op-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?></div>
                            <div id="op_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常重连间隔时间 -->
                        <div class="strategy-group__form__item op-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?></div>
                            <div id="op_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务自动重试 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?></div>
                            <div id="task_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务重连对象 -->
                        <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?></div>
                            <div id="task_retry_object" class="strategy-group__form__item__value col-md-8">
                                仅重试任务中失败的对象
                            </div>
                        </div>
                        <!-- 任务重连次数 -->
                        <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?></div>
                            <div id="task_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务重连间隔时间 -->
                        <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?></div>
                            <div id="task_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
                <!-- END RETRY STRATEGY -->

                <!-- 过载保护 -->
                <div class="config-detail-wrap__group strategy-group" id="configuration_detail_overloadStrategyDiv">
                    <div class="strategy-group__title">
                        <span class="decoration me-8"></span><span
                                class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 忽略节点资源限制 -->
                        <div class="strategy-group__form__item passfilealarm-form-item"
                             id="configuration_detail_ignoreResourceLimitDiv">
                            <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></div>
                            <div id="ignoreResourceLimit" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn green-haze" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
        </div>
    </div>
</div>
<!-- END MORE CONFIGURATION DETAIL DRAWER -->

<!-- BEGIN SKIP DATAFILE BAD BLOCK DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="skipDatafileBadBlockModal"
     style="width: 600px">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <!-- header -->
        <div class="drawer-header" style="padding: 16px 20px">
            <h4 class="drawer-title" style="margin: 0; height: 20px;">
                <span>
                    <i class="viconfont vicon-gaojipeizhi"></i>
                </span>
                <span style="position: relative; top: -1px; font-size: 16px; color: #333;"
                      class="name"><?php echo $LANG['UI_DB_ORACLE_BACKUP_SKIP_DATAFILE_BAD_BLOCK_CONFIG'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body" style="padding: 20px">
            <div class="portlet-body" style="height: 100%">
                <div id="skipDatafileBadBlock"
                     style="border: 1px solid #e6e6e6; height: 100%; overflow: auto; padding: 8px; background: #f0f0f0;">
                    <ul class="ztree" id="skipDatafileBadBlockTree"></ul>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default cancel" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END SKIP DATAFILE BAD BLOCK DRAWER -->

<!-- BEGIN RECOVERY CONTENT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="oracleRecoveryContentDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <!-- header -->
        <div class="drawer-header">
            <h4 class="drawer-title">
                <span>
                    <i class="viconfont vicon-gaojipeizhi"></i>
                </span>
                <span class="name"><?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body">
            <div class="portlet-body">
                <!-- 恢复内容树 -->
                <div class="form-group">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_ORACLE_RECOVERY_CONTENT'] ?>
                    </label>
                    <div class="col-md-10">
                        <ul id="oracle-recovery-content-tree" class="ztree"></ul>
                    </div>
                </div>

                <!-- 文件内容 -->
                <!-- spfile -->
                <div class="form-group pfileDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_RECOVERY_PFILE'] ?>
                    </label>
                    <div class="col-md-10">
                        <div id="pfile-content"></div>
                    </div>
                </div>
                <!-- listener.ora-->
                <div class="form-group listenerDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_RECOVERY_LISTENER'] ?>
                    </label>
                    <div class="col-md-10">
                        <div id="listener-content"></div>
                    </div>
                </div>
                <!-- tnsnames.ora-->
                <div class="form-group tnsnamesDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_RECOVERY_TNSNAMES'] ?>
                    </label>
                    <div class="col-md-10">
                        <div id="tnsnames-content"></div>
                    </div>
                </div>
                <!-- sqlnet.ora-->
                <div class="form-group sqlnetDiv">
                    <label class="control-label col-md-2">
                        <?php echo $LANG['UI_DB_RECOVERY_SQLNET'] ?>
                    </label>
                    <div class="col-md-10">
                        <div id="sqlnet-content"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default cancel" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END RECOVERY CONTENT DRAWER -->

<!-- BEGIN CREATE NEW INSTANCE DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="oracleCreateNewInstanceDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <!-- header -->
        <div class="drawer-header" style="padding: 16px 20px">
            <h4 class="drawer-title" style="margin: 0; height: 20px;">
                <span>
                    <i class="viconfont vicon-shili1"></i>
                </span>
                <span style="position: relative; top: -1px; font-size: 16px; color: #333;"
                      class="name"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body" style="padding: 20px">
            <div class="portlet-body" style="height: 100%">
                <!-- 实例名 -->
                <div class="form-group" style="height: 22px">
                    <label class="col-md-3"><?php echo $LANG['UI_DB_NEW_INSTANCE_CONFIG_NAME'] ?></label>
                    <div class="col-md-6">
                        <div id="oracleCreateNewInstanceName"></div>
                    </div>
                </div>
                <!-- spfile内容 -->
                <div class="col-md-12 pd0" style="height: calc(100% - 36px)">
                    <pre id="oracleCreateNewInstanceSpfile" style="height: 100%"></pre>
                </div>
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default cancel" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END CREATE NEW INSTANCE DRAWER -->
<!-- END DRAWER -->

<!-- BEGIN RUNNING LOG DETAIL DRAWER -->
<div id="runningLogDetailDrawer" class="drawer slide" tabindex="-1" data-placement="right" role="dialog">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <!-- header -->
        <div class="drawer-header">
            <h4 class="drawer-title">
                <i class="viconfont vicon-tenant-detail"></i>
                <span class="text"><?php echo $LANG['WEB_LOG_TASK_ERROR_DETAIL']; ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <!-- body -->
        <div class="drawer-body">
            <div class="portlet-body">
            </div>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <button type="button" class="btn default cancel" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END RUNNING LOG DETAIL DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>

<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/dbprotect/db_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->