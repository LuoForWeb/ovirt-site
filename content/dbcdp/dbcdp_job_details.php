        <?php include_once '../../tpl/permission.php'; ?>
        <?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
        <!-- BEGIN PAGE LEVEL STYLES -->
        <link rel="stylesheet" type="text/css" href="./scripts/plugins/ztree/css/metroStyle/metroStyle.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
        <link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
        <link href="./css/dbcdp/dbcdp.css" rel="stylesheet" type="text/css"/>
        <!-- END PAGE LEVEL STYLES -->
        <!-- BEGIN PAGE HEADER-->
        <h3 class="breadcrumb">
            <li>
                <a href='./content/platform/jobs/jobs.php' name="task" class="ajaxify">
                    <span><?php echo $LANG['UI_PLATFORM_CURRENT_JOB'];?></span>
                </a>
            </li>
            <span>></span>
            <span class="current"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS'];?></span>
        </h3>
        <!-- BEGIN PAGE CONTENT-->

        <div class="row job-detail" id="jobDetailDiv">
            <div class="col-md-12 job-detail__halftop">
                <div class="portlet-body" id="jobDetail">
                    <div class="col-md-8 job-detail__halftop__charts">
                        <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
                        <input id="task_type" value="<?php echo $_GET['type']; ?>" class="display-none"></input>
                        <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
                        <!-- BEGIN DYNAMIC CHART PORTLET-->
                        <div class="portlet-charts">
                            <div class="portlet-body">
                                <!--BEGIN TABS-->
                                <div class="tabbable tabbable-custom" style="overflow: visible;" id="taskFlowOrMapTabs">
                                    <ul class="nav nav-tabs" id="taskFlowOrMapNavTabs" style="border-bottom: 1px solid #ddd;">
                                        <li class="active" id="task_plot_li">
                                            <a href="#tab_task_map" data-toggle="tab" aria-expanded="true">
                                                <i class="viconfont vicon-renwushujuliuxiang"></i> <?php echo $LANG['UI_JOB_TASK_DATA_FLOW']; ?>
                                            </a>
                                        </li>
                                        <li class="">
                                            <a href="#tab_chart" data-toggle="tab" aria-expanded="false">
                                                <i class="viconfont vicon-renwuliuliang"></i><?php echo $LANG['UI_JOB_FLOW']; ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <div class="tab-pane active display-none " id="tab_task_map">
                                        <div class="portlet-body">
                                            <div class="tab-content row ">
                                                <div class="tab-pane active " id="info">
                                                    <div class="portlet-body width600 margin0auto">
                                                        <div style="position: relative" id="topContent">
                                                        </div>
                                                        <div style="position: relative;" id="machineContent">
                                                        </div>
                                                        <div style="position: relative" id="bottomContent">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane " id="tab_chart">
                                        <div class="portlet-charts__body" style="height: 100%;">
                                            <div class="portlet-charts__body__speedchart">
                                                <div id="speedcharthover"></div>
                                                <div id="speedchart">
                                                </div>
                                            </div>
                                            <div class="display-hide" id="progressInfo" style="height: 50px;">
                                                <div style="display: flex;align-items: center;height: 20px;">
                                                    <span style="color: #999999;font-size: 12px;width: 100px;" id="currentObjTitle"></span>
                                                    <span class="text-overflow-ellipsis vmDetail" id="currentSynObj" title=""></span>
                                                </div>
                                                <div class="progressDiv display-none">
                                                    <div class="progressDiv__label">
                                                        <span><?php echo $LANG['UI_JOB_TOTAL_PROGRESS']; ?></span>
                                                    </div>
                                                    <div class="progress progress-striped active" style="display: flex;">
                                                        <div class="col-md-3" style="position:relative;height: 50px;">
                                                            <div id="dictExportInfo" class="progress-bar progress-bar-export" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="position: absolute;top:0;left:0;height:100%;background-color: #84A6FF;width: "></div>
                                                            <span id="exportprogressVal" style="position: absolute; top: 20%;left: 50%;transform: translate(-50%, -50%);color: #666666;" class="dbcdp-table-bottom_en"></span>
                                                        </div>
                                                        <div class="col-md-3" style="position:relative;height: 50px;">
                                                            <div id="dictImportInfo" class="progress-bar progress-bar-import" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="position: absolute;top:0;left:0;height:100%;background-color: #0DCAF0;width: "></div>
                                                            <span id="importprogressVal" style="position: absolute; top: 20%;left: 50%;transform: translate(-50%, -50%);color: #666666;" class="dbcdp-table-bottom_en"></span>
                                                        </div>
                                                        <div class="col-md-3" style="position:relative;height: 50px;">
                                                            <div id="synTabInfo" class="progress-bar progress-bar-table" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="position: absolute;top:0;left:0;height:100%;background-color: #0FBF98;width: "></div>
                                                            <apan id="syntabprogressVal" style="position: absolute; top: 20%;left: 50%;transform: translate(-50%, -50%);color: #666666;" class="dbcdp-table-bottom_en"></apan>
                                                        </div>
                                                        <div class="col-md-3" style="position:relative;height: 50px;">
                                                            <div id="constraintDictInfo" class="progress-bar progress-bar-table" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="position: absolute;top:0;left:0;height:100%;background-color: '01DACF';width: "></div>
                                                            <apan id="constraintprogressVal" style="position: absolute; top: 20%;left: 50%;transform: translate(-50%, -50%);color: #666666;" class="dbcdp-table-bottom_en"></apan>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 job-detail__halftop__navtabs">
                        <!-- BEGIN PORTLET-->
                        <div class="portlet paddingless">
                            <div class="portlet-body">
                                <!--BEGIN TABS-->
                                <div class="tabbable tabbable-custom" style="overflow: visible;">
                                    <ul class="nav nav-tabs">
                                        <li class="active nolb">
                                            <a href="#tab_1_1" data-toggle="tab" aria-expanded="true">
                                                <i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY']; ?>
                                            </a>
                                        </li>
                                        <li id="takeoverli">
                                            <a href="#tab_takeover" data-toggle="tab" aria-expanded="false">
                                                <i class="viconfont vicon-jieguan"></i> <?php echo $LANG['UI_PLATFORM_VOL_CDP_TAKEOVER']; ?>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content min-height280">
                                        <div class="tab-pane active" id="tab_1_1">
                                            <div class="portlet-body">
                                                <div class="row static-info taskOperateDiv">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE']; ?>:
                                                    </div>
                                                    <div class="col-md-8 col-operate value">
                                                        <div class="btn-group">
                                                            <button type="button"
                                                                    class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle"
                                                                    data-toggle="dropdown" data-hover="dropdown"
                                                                    data-delay="1000" data-close-others="true">
                                                                <?php echo $LANG['UI_PUBLIC_OPERATION']; ?>
                                                                <i class="fa fa-angle-down"></i>
                                                            </button>
                                                            <ul class="dropdown-menu min-width100" role="menu" id="dbCdpOpList">
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-4 name" id="">
                                                        <?php echo $LANG['UI_JOB_RNAME']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value col-taskname" id="taskName"
                                                         style="word-break:break-word; max-width:300px;">
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_PUBLIC_TASK_TYPE']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="type">
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_JOB_TYPE']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="status">
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_JOB_TASK_PHASE']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="taskStage">
                                                    </div>
                                                </div>

                                                <div class="row static-info totalsizeDiv">
                                                    <div class="col-md-4 name" id="tablesSizeStr">
                                                        <?php echo $LANG['UI_DB_CDP_DETAILS_TOTAL_TABLE_COUNT'];?>:
                                                    </div>
                                                    <div class="col-md-4 value" id="totalSize"></div>
                                                    <div class="col-md-4">
                                                        <a href="javascript:void(0)" id="import_export_details" class="colorgreen" data-toggle="drawer" data-target="#drawer-2"><?php echo $LANG['UI_DB_CDP_DETAILS_IMP_EXP_DETAILS'];?></a>
                                                    </div>
                                                </div>
                                                <div class="row static-info replay_error_transaction_num_div">
                                                    <div class="col-md-4 name"><?php echo $LANG['UI_DB_CDP_REPLAY_ERROR_TRANSACTION_NUM'];?>:</div>
                                                    <div class="col-md-4 value" id="replay_error_transaction_num"></div>
                                                    <div class="col-md-4 value">
                                                        <a href="javascript:void(0)" id="transactions_details" class="colorgreen" data-toggle="drawer" data-target="#drawer-3"><?php echo $LANG['UI_DB_CDP_TRANSACTION_DETAILS'];?></a>
                                                    </div>
                                                </div>
                                                <div class="row static-info" id="currentSizeView">
                                                    <div class="col-md-4 processeddata" id="currentSizeStr">
                                                        <?php echo $LANG['UI_JOB_ALREADY_COMPLETED_SIZE']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="currentSize">
                                                    </div>
                                                </div>
                                                <div class="failbackTimeDiv">
                                                    <div class="row static-info">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_DB_CDP_FAILBACK_LATEST_REDO_TIME'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="latestRedoTime">
                                                        </div>
                                                    </div>
                                                    <div class="row static-info">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_DB_CDP_FAILBACK_LAST_REDO_REPLAY_TIME'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="lastRedoReplayTime">
                                                        </div>
                                                    </div>
                                                    <div class="row static-info mb-12">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_DB_CDP_FAILBACK_DATA_DELAY'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="dataDelayTime">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_JOB_START_TIME']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="startTime">
                                                    </div>
                                                </div>
                                                <div class="row static-info">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_JOB_INTERVAL_TIME']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="intervalTime">
                                                    </div>
                                                </div>
                                                <div class="row static-info display-none takeoverTypeDiv">
                                                    <div class="col-md-4 name" id="takeoverTypeStr">
                                                        <?php echo $LANG['UI_JOB_TAKEOVER_TYPE']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="takeoverType">
                                                    </div>
                                                </div>
                                                <!-- 更多详请 -->
                                                <div class="row static-info">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_PUBLIC_MORE_DETAIL'] ?>:
                                                    </div>
                                                    <div class="col-md-8 value">
                                                        <a href="javascript:void(0)" id="details_more" class="colorgreen" data-toggle="drawer" data-target="#drawer-1"><?php echo $LANG['UI_PUBLIC_MORE_CONFIGURATION']; ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane" id="tab_takeover">
                                            <div class="portlet-body">
                                                <!-- 自动接管 -->
                                                <div class="row static-info cdpAutoTakeoverConfDiv display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="cdpAutoTakeoverConf">
                                                    </div>
                                                </div>
                                                <div class="row static-info autoTakeoverStandbyDiv display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="autoTakeoverStandby">
                                                    </div>
                                                </div>

                                                <div class="row static-info autotakeovercatbackupipdiv display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_VOL_CDP_TAKEOVER_RECOVER_IP']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="autoTakeoverCatbackIp">
                                                    </div>
                                                </div>
                                                <!-- 心跳间隔 -->
                                                <div class=" row static-info display-none heartbeatFailureTimeView">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="heartbeatFailureTime">
                                                    </div>
                                                </div>

                                                <!-- 应用故障接管 -->
                                                <div class="row static-info volCdpAppTakeoverDiv display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_VOL_CDP_APPLICATION_TAKEOVER']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="volCdpAppTakeover">
                                                    </div>
                                                </div>

                                                <!-- 应用故障监测-->
                                                <div class="row static-info volCdpAppMonitorDiv display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_VOL_CDP_ENABLE_FAULT_MONITOR_TAKEOVER'];?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="volCdpTaskAppMonitor">
                                                    </div>
                                                </div>

                                                <!--连续故障次数 -->
                                                <div class="row static-info appConseFailureNumDiv display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_DB_CDP_BACKUP_APP_CONTINUOUS_FAILURE_TIMES']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="appConseFailureNum">
                                                    </div>
                                                </div>
                                                <!-- 故障监测间隔 -->
                                                <div class="row static-info appFaultDetectionInterDiv display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_DB_CDP_BACKUP_APP_FAILURE_MONITORING_INTERVAL']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="appFaultDetectionInter">
                                                    </div>
                                                </div>
                                                <!-- 接管应用-->
                                                <div class="row static-info cdpTakeoverAppDiv display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_VOL_CDP_TAKEOVER_APPLICATION']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="cdpTakeoverApp">
                                                    </div>
                                                </div>
                                                <!-- 自动接管 -->
                                                <div class="row static-info takeoverDiv">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_VOL_CDP_AUTO_TAKEOVER']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="takeoverStr">
                                                    </div>
                                                </div>
                                                <div id="auto_takeover_config" class="display-none">
                                                    <!-- 备机 -->
                                                    <div class="row static-info standByDiv ">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_VOL_CDP_STANDBY_MACHINE'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="standByStr">
                                                        </div>
                                                    </div>

                                                    <!-- 接管回切通信IP -->
                                                    <div class="row static-info takeoverSwitchIpDiv">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_VOL_CDP_TAKEOVER_RECOVER_IP'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="takeoverSwitchIpStr">
                                                        </div>
                                                    </div>

                                                    <!-- 服务IP漂移-->
                                                    <div class="row static-info takeoverIpdriftDiv">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_DB_CDP_BACKUP_SERVICE_IP_DRIFT'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="takeoverIpdriftStr">
                                                        </div>
                                                    </div>
                                                    <!-- 网卡配置 -->
                                                    <div class="row static-info takeoverBusinessIpMapDiv">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_DB_CDP_DETAILS_NETWORK_CARD_CONFIGURATION'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="takeoverBusinessIpMapStr">
                                                        </div>
                                                    </div>

                                                    <!-- 心跳失效时间: -->
                                                    <div class="row static-info heartFailurevalDiv ">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_VOL_CDP_HEARTBEAT_NOT_ENABLE_TIME'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="heartFailurevalDivNum">
                                                        </div>
                                                    </div>

                                                    <!-- 连续故障次数: -->
                                                    <div class="row static-info faultIntervalDiv ">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_DB_CDP_BACKUP_APP_CONTINUOUS_FAILURE_TIMES'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="conseFailureNum">
                                                        </div>
                                                    </div>

                                                    <!-- 故障监测间隔: -->
                                                    <div class="row static-info mb-12 faultIntervalDiv ">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_DB_CDP_BACKUP_APP_FAILURE_MONITORING_INTERVAL'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="faultIntervalStr">
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 手动接管配置 -->
                                                <div id="manual_takeover_config" class="mb-12 display-none">
                                                    <div class="row static-info manualTakeoverDiv">
                                                        <div class="col-md-4 name">
                                                            <?php echo $LANG['UI_DB_CDP_DETAILS_TAKEOVER'];?>:
                                                        </div>
                                                        <div class="col-md-8 value" id="manualTakeoverStr">
                                                        </div>
                                                    </div>
                                                    <div id="manual_takeover_config_view">
                                                        <div class="row static-info takeoverTimePointDiv">
                                                            <div class="col-md-4 name">
                                                                <?php echo $LANG['UI_DB_CDP_BACKUP_TAKEOVER_TIME_POINT_TYPE'];?>:
                                                            </div>
                                                            <div class="col-md-8 value" id="takeoverTimePointStr">
                                                            </div>
                                                        </div>
                                                        <div class="row static-info takeoverScnDiv display-none">
                                                            <div class="col-md-4 name">
                                                                <?php echo $LANG['UI_DB_CDP_TAKEOVER_SCN'];?>:
                                                            </div>
                                                            <div class="col-md-8 value" id="takeoverScnStr">
                                                            </div>
                                                        </div>
                                                        <div class="row static-info failbackIPDiv">
                                                            <div class="col-md-4 name">
                                                                <?php echo $LANG['UI_DB_CDP_BACKUP_FAILBACK_IP'];?>:
                                                            </div>
                                                            <div class="col-md-8 value" id="failbackIPStr">
                                                            </div>
                                                        </div>
                                                        <div class="row static-info serviceIpDriftDiv">
                                                            <div class="col-md-4 name">
                                                                <?php echo $LANG['UI_DB_CDP_BACKUP_SERVICE_IP_DRIFT'];?>:
                                                            </div>
                                                            <div class="col-md-8 value" id="serviceIpDriftStr">
                                                            </div>
                                                        </div>
                                                        <div class="row static-info takeoverNetDiv">
                                                            <div class="col-md-4 name">
                                                                <?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF'];?>:
                                                            </div>
                                                            <div class="col-md-8 value" id="takeoverNetStr">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 任务回切配置按钮 -->
                                                <div class="row static-info taskSwitchbackConfigDiv mt12 display-none">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_JOB_TASK_FAILBACK_CONFIGURE']; ?>:
                                                    </div>
                                                    <div class="col-md-8 value" id="">
                                                        <div class="btn-group">
                                                            <button id="taskSwitchbackConf" type="button" class="btn btn-success btn-sm dropdown-toggle taskOperateButton btn-operate hover-initialized" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">
                                                                <?php echo $LANG['UI_JOB_VIEW_MODIFY_FAILBACK']; ?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- 回切配置 -->
                                                <div class="row static-info failback_config_show">
                                                    <div class="col-md-4 name">
                                                        <?php echo $LANG['UI_DB_CDP_DETAILS_CONFIG_FAILBACK'];?>:
                                                    </div>
                                                    <div class="col-md-8 value">
                                                        <a href="javascript:void(0)" id="failback_config_more" class="colorgreen" data-toggle="drawer" data-target="#drawer-4"><?php echo $LANG['UI_PUBLIC_MORE_CONFIGURATION']; ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 job-detail__halfbottom">
                <!-- BEGIN TAB PORTLET-->
                <div class="job-detail__halfbottom__portlet">
                    <div class="portlet-title">
                        <ul class="nav nav-tabs floatl">
                            <li class="active ">
                                <a href="#log" data-toggle="tab">
                                    <i class="viconfont vicon-tasklog "></i> <?php echo $LANG['UI_JOB_RUNING_LOG']; ?>
                                </a>
                            </li>
                            <li>
                                <a href="#monitorData" data-toggle="tab">
                                    <i class="viconfont vicon-jiankongshuju"></i> <?php echo $LANG['UI_DB_CDP_DETAILS_MONITORING_DATA'];?> </a>
                            </li>
                            <li id="historyli">
                                <a href="#history" data-toggle="tab">
                                    <i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_HISTORY']; ?>
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
                            <div class="tab-pane" id="monitorData">
                                <div class="table-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table id="monitorTable">
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane" id="history">
                                <div class="table-container">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <table id="historyTable">
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END TAB PORTLET-->
            </div>
        </div>

        <!-- BEGIN TASK  FAILBACK MODAL-->
        <div id="taskFailbackupModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
             data-backdrop="static">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">
                    <i class="viconfont vicon-huiqiepeizhi"></i> <?php echo $LANG['UI_JOB_FAILBACK_CONFIGURE']; ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="tabbable tabbable-custom margintop-10" style="overflow: visible;margin-bottom:-10px;">
                        <div class="row">
                            <div class="col-md-11">
                                <!-- 回切目标  -->
                                <div class="form-group margintop20 ml25 failbackuphostview">
                                    <label class="control-label col-md-3 applianceselectlabel">
                                        <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_TARGET'];?>
                                    </label>
                                    <div class="col-md-6">
                                        <ul id="target_agent_tree" class="ztree"></ul>
                                    </div>
                                </div>

                                <div class="form-group margintop20 ml25 dbMapDetaildiv">
                                    <label class="control-label col-md-3 applianceselectlabel">
                                        <?php echo $LANG['UI_DB_CDP_BACKUP_DATABASE_MAPPING_DETAILS'];?>
                                    </label>
                                    <div id="instanceMapList" class="col-md-9">
                                    </div>
                                </div>
                                <div class="form-group margintop20 ml25 synNodediv">
                                    <label class="control-label col-md-3 applianceselectlabel">
                                        <?php echo $LANG['UI_DB_CDP_BACKUP_DATA_SYNC_NODE'];?>
                                    </label>
                                    <div id="synNode" class="col-md-9"></div>
                                </div>
                                <div class="form-group margintop20 ml25">
                                    <label class="control-label col-md-3">
                                        <?php echo $LANG['UI_DB_CDP_BACKUP_DATA_TABLE_SPACE_STORAGE_DIRECTORY'];?>
                                    </label>
                                    <div class="col-md-6">
                                        <select id="tablespaceDir" class="form-control select2me" type="text">
                                            <option value="0"><?php echo $LANG['UI_DB_CDP_BACKUP_SYSTEM_TABLE_SPACE_DIRECTORY'];?></option>
                                            <option value="1"><?php echo $LANG['UI_PLATFORM_SETTING_TEMPLATE3'];?></option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mt8">
                                        <a class="popovers ml15" data-container="body"
                                           data-trigger="hover"
                                           data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_DATA_TABLE_SPACE_STORAGE_DIRECTORY_TIPS'];?>">
                                            <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="form-group margintop20 ml25 customDirectory_div display-none">
                                    <label class="control-label col-md-3"></label>
                                    <div class="col-md-6">
                                        <input id="customDirectory" class="form-control select2me input-sm" type="text">
                                    </div>
                                </div>

                                <!-- 回切网络配置-->
                                <div class="form-group ml25 failbackNetConf display-none">
                                    <label class="control-label col-md-3 applianceselectlabel">
                                        <?php echo $LANG['UI_DB_CDP_DETAILS_CONFIG_NETWORK_FAILBACK']; ?>
                                    </label>
                                    <div class="col-md-9">
                                        <button type="button" class="btn green-haze" id="faiback_network_conf" data-toggle="drawer" data-target="#takeover_conf_drawer"><?php echo $LANG['UI_DB_CDP_DETAILS_CONFIG_NETWORK_FAILBACK']; ?></button>
                                        <ul id="failback_ip_map_list" style="padding-left: 0;width:90%;">
                                        </ul>
                                    </div>
                                </div>

                                <!-- 设置数据类型-->
                                <div class="form-group ml25 dataTypedivconf">
                                    <label class="control-label col-md-3 dataTypelabelconf">
                                        <?php echo $LANG['UI_BACKUP_DATA_REPORT_BACKUP_MODE'];?>
                                    </label>
                                    <div class="col-md-6">
                                        <select class="form-control select2me" id="selectdataTypeconf">
                                            <option value="1"><?php echo $LANG['UI_DB_CDP_DETAILS_DATA_TYPE1'];?></option>
                                            <!--                                    <option value="2">--><?php //echo $LANG['UI_DB_CDP_DETAILS_DATA_TYPE2'];?><!--</option>-->
                                        </select>
                                    </div>
                                    <div class="col-md-3 width15p mt8">
                                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                           data-placement="right"
                                           data-content= "<?php echo $LANG['UI_DB_CDP_DETAILS_DATA_TYPE_TIPS'];?>">
                                           <i class="viconfont vicon-tishi"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="form-group ml25">
                                    <label class="control-label col-md-3 failBackStrategylabel"><?php echo $LANG['UI_DB_CDP_FAILBACK_STRATEGY'];?></label>
                                    <div class="col-md-9 accordion">
                                        <div class="panel panel-default strategy-panel">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle accordion-toggle-styled popovers switch-strategy-view collapsed"
                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".switch-strategy-view" href="#switch_strategy_conf" aria-expanded="true">
                                                        <i class="viconfont vicon-ge_transfer font-green-seagreen"></i>
                                                        <span class="font-green-seagreen"><?php echo $LANG['UI_DB_CDP_FAILBACK_STRATEGY'];?></span>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div id="switch_strategy_conf" class="panel-collapse collapse" aria-expanded="true">
                                                <div class="panel-body">
                                                    <!-- 回切策略-->
                                                    <div class="form-group failBackStrategydiv">
                                                        <label class="control-label col-md-3 failBackStrategylabel">
                                                            <?php echo $LANG['BILLING_TABLE_TYPE'];?>
                                                        </label>
                                                        <div class="pl0 col-md-5">
                                                            <select class="form-control select2me" id="failBackStrategy_select">
                                                                <option value="3" selected> <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_STRATEGY_TYPE_MANUAL'];?> </option>
                                                                <option value="1"> <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_STRATEGY_TYPE_AUTO'];?> </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group manualStrategyTypediv display-none">
                                                        <label class="control-label col-md-3"><?php echo $LANG['UI_DB_CDP_FAILBACK_FREE_TIME_INTERVAL'];?></label>
                                                        <div class="pl0 col-md-5">
                                                            <div class="input-group spinner-group free_time_interval_div">
                                                                <input type="number" id="free_time_interval"
                                                                       onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                       class="spinner-input form-control input-sm">
                                                                <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                    <button type="button" class="btn spinner-up default">
                                                                        <i class="fa fa-angle-up"></i>
                                                                    </button>
                                                                    <button type="button" class="btn spinner-down default ">
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 width15p mt8">
                                                            <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true"
                                                               data-placement="right"
                                                               data-content="<?php echo $LANG['UI_DB_CDP_FAILBACK_FREE_TIME_INTERVAL_TIPS'];?>">
                                                                <i class="viconfont vicon-tishi"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <!-- 时间控件 -->
                                                    <div class="form-group failBackStrategyTimediv display-none">
                                                        <label class="control-label col-md-3 failBackStrategyTimelabel"><?php echo $LANG['UI_DB_CDP_FAILBACK_TIME_RANGE'];?></label>
                                                        <div class="pl0 col-md-7 daterangepickerdiv">
                                                            <input type="text" name="failback_strategy_time_picker" id="failback_strategy_time_picker" class="form-control" autocomplete="off">
                                                            <i class="viconfont vicon-ge_calendar"></i>
                                                        </div>
                                                        <div class="pl0 col-md-7 any_time_div display-none">
                                                            <input class="form-control input-sm" value="<?php echo $LANG['UI_DB_CDP_FAILBACK_ANY_TIME'];?>" style="display: inline" maxlength="128" readonly="">
                                                        </div>
                                                        <div class="col-md-2 pl0">
                                                            <button class="timeRangeButton any_time_toggle" data-value = "time_range_button">
                                                                <?php echo $LANG['UI_DB_CDP_SWITCH_ANY_TIME']?>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-info">
                                                        <button type="button" class="close" data-dismiss="alert"></button>
                                                        <strong><?php echo $LANG['UI_DB_CDP_FAILBACK_STRATEGY'] ?>:</strong>
                                                        <p><?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_TIPS']?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ml25">
                                    <label class="control-label col-md-3"><?php echo $LANG['UI_NODE_CACHE_CONFIG']; ?></label>
                                    <div class="col-md-9 accordion">
                                        <div class="panel panel-default strategy-panel">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle accordion-toggle-styled popovers transfer_view collapsed"
                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".cache_view" href="#failback_cache_conf" aria-expanded="true">
                                                        <i class="viconfont vicon-ge_transfer font-green-seagreen"></i>
                                                        <label class="font-green-seagreen"><?php echo  $LANG['UI_VOL_CDP_FAILBACK_ADVANCED_CONF_LABLE']; ?></label>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div id="failback_cache_conf" class="panel-collapse collapse" aria-expanded="true">
                                                <div class="panel-body">
                                                    <div class="col-md-12">
                                                        <!--源机文件缓存大小-->
                                                        <div class="form-group source_failbackfilecachediv">
                                                            <label class="control-label col-md-4 fileCacheSizelabel"><?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_SIZE'];?></label>
                                                            <div class="col-md-5 source_custom_cache_size_div">
                                                                <div class="col-md-6 pl0">
                                                                    <div class="input-group spinner-group">
                                                                        <input id="failback_source_fileCacheSize" class="spinner-input form-control" type="text" onkeyup="value=value.replace(/[^\d]/g,'')">
                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                            <button type="button" class="btn spinner-up default">
                                                                                <i class="fa fa-angle-up"></i>
                                                                            </button>
                                                                            <button type="button" class="btn spinner-down default">
                                                                                <i class="fa fa-angle-down"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <select name="source_file_cache_failback_unit" class="form-control select2me">
                                                                        <option value="1">GB</option>
                                                                        <option value="2">TB</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-5 source_cache_size_unlimited_div display-none">
                                                                <input class="form-control input-sm" value="<?php echo $LANG['UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE'];?>" style="display: inline" maxlength="128" readonly>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <button class="fileCachePathButton unlimit_limit_toggle source_unlimit_limit_toggle" data-value = "source_cache_button"><?php echo $LANG['UI_DB_CDP_BACKUP_SWITCH_TO_UNLIMITED'];?></button>
                                                            </div>
                                                        </div>
                                                        <!--备机文件缓存大小-->
                                                        <div class="form-group failbackfilecachediv">
                                                            <label class="control-label col-md-4 fileCacheSizelabel"><?php echo $LANG['UI_DB_CDP_BACKUP_STANDBY_FILE_CACHE_SIZE'];?></label>
                                                            <div class="col-md-5 backup_custom_cache_size_div">
                                                                <div class="col-md-6 pl0">
                                                                    <div class="input-group spinner-group">
                                                                        <input id="failback_backup_fileCacheSize" class="spinner-input form-control" type="text" onkeyup="value=value.replace(/[^\d]/g,'')">
                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                            <button type="button" class="btn spinner-up default">
                                                                                <i class="fa fa-angle-up"></i>
                                                                            </button>
                                                                            <button type="button" class="btn spinner-down default">
                                                                                <i class="fa fa-angle-down"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <select name="backup_file_cache_failback_unit" id="" class="form-control select2me">
                                                                        <option value="1">GB</option>
                                                                        <option value="2">TB</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-5 backup_cache_size_unlimited_div display-none">
                                                                <input class="form-control input-sm" value="<?php echo $LANG['UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE'];?>" style="display: inline" maxlength="128" readonly>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <button class="fileCachePathButton unlimit_limit_toggle backup_unlimit_limit_toggle" data-value = "backup_cache_button"><?php echo $LANG['UI_DB_CDP_BACKUP_SWITCH_TO_UNLIMITED'];?></button>
                                                            </div>
                                                        </div>
                                                        <!-- 客户端文件缓存路径-->
                                                        <div class="form-group">
                                                            <label class="control-label col-md-4 filecachepathlabel">
                                                                <?php echo $LANG['UI_DB_CDP_BACKUP_STANDBY_FILE_CACHE_PATH']; ?>
                                                            </label>

                                                            <div class="col-md-6">
                                                                <input id="file_cache_path" type="text" maxlength="128" class="form-control" value="" title="" readonly>
                                                            </div>

                                                            <div class="col-md-2">
                                                                <button id="customFileCachePath" class="fileCachePathButton"><?php echo $LANG['UI_JOB_CHANGE_CUSTOM']; ?></button>
                                                                <button id="defaultFileCachePath" class="fileCachePathButton display-none"><?php echo $LANG['UI_JOB_CHANGE_SYSTEM_DEFAULT']; ?></button>
                                                            </div>
                                                            <div class="col-md-2 width15p mt8 display-none">
                                                                <a class="popovers" data-container="body" data-trigger="hover" data-html="true"
                                                                   data-placement="right"
                                                                   data-content="<?php echo $LANG['UI_VOL_CDP_CLIENT_CACHE_PATH_TIPS']; ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ml25">
                                    <label class="control-label col-md-3 transfercompress"><?php echo $LANG['UI_VOL_CDP_FAILBACK_TRANSFER_CONF_TEXT']; ?></label>
                                    <div class="col-md-9 accordion">
                                        <div class="panel panel-default strategy-panel">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle accordion-toggle-styled popovers transfer_view collapsed"
                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".transfer_view" href="#failback_transfer_conf" aria-expanded="true">
                                                        <i class="viconfont vicon-ge_transfer font-green-seagreen"></i>
                                                        <label class="font-green-seagreen"><?php echo  $LANG['UI_JOB_TRANSMISSION']; ?></label>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div id="failback_transfer_conf" class="panel-collapse collapse" aria-expanded="true">
                                                <div class="panel-body">
                                                    <div class="col-md-12">
                                                        <!-- 传输压缩 -->
                                                        <div class="form-group ml25 failbacktrancompressdiv">
                                                            <label class="control-label col-md-3 transfercompress"><?php echo $LANG['UI_COPY_COMPRESS_TRANSFER']; ?></label>
                                                            <div class="col-md-5">
                                                                <input type="checkbox" id="failbackTranCompressSwitch"
                                                                       class="make-switch" data-on-color="primary"
                                                                       data-off-color="info" data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover" data-html="true"
                                                                   data-placement="right"
                                                                   data-content="<?php echo $LANG['UI_VOL_CDP_TRANSMISSION_COMPRESSION_TIPS']; ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>

                                                        <div class="form-group ml25 compressgradediv display-none">
                                                            <label class="control-label col-md-3 compresstransferlabel form-group-label form-group-label"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'];?></label>
                                                            <div class="col-md-6 form-group-content">
                                                                <select name="" id="compresstransferselect" class="form-control select2me">
                                                                    <option value="1"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_FAST'];?></option>
                                                                    <option value="2"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_NORMAL'];?></option>
                                                                    <option value="3"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BETTER'];?></option>
                                                                    <option value="4"><?php echo $LANG['UI_BACKUP_COMPRESS_GRADE_BEST'];?></option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- 传输加密 -->
                                                        <div class="form-group ml25 failbacktranencryptdiv">
                                                            <label class="control-label col-md-3 transferencrypt"><?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']; ?></label>
                                                            <div class="col-md-5">
                                                                <input type="checkbox" id="failbackTranEncryptSwitch"
                                                                       class="make-switch" data-on-color="primary"
                                                                       data-off-color="info" data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover" data-html="true"
                                                                   data-placement="right"
                                                                   data-content="<?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER_TIPS'] ?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>

                                                        <div class="form-group ml25 encrypttransfermethoddiv display-none">
                                                            <label class="control-label col-md-3 encrypttransfermethodlabel"><?php echo $LANG['UI_DB_CDP_BACKUP_ENCRYPTED_TRANSMISSION_METHOD'];?></label>
                                                            <div class="col-md-6">
                                                                <select name="" id="encrypttransfermethodselect" class="form-control select2me">
                                                                    <option value="1"><?php echo $LANG['UI_BACKUP_TRANSFER_ENCRYPT_RSA'];?></option>
                                                                    <option value="2"><?php echo $LANG['UI_BACKUP_TRANSFER_ENCRYPT_SM'];?></option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- 传输线程个数 -->
                                                        <div class="form-group ml25">
                                                            <label class="control-label col-md-3 transferthreadlabel"><?php echo $LANG['UI_DB_CDP_BACKUP_EXP_THREADS'];?></label>
                                                            <div class="col-md-6 exp_thread_div">
                                                                <div class="input-group spinner-group">
                                                                    <input type="num" id="exp_thread"
                                                                           onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                           class="spinner-input form-control input-sm">
                                                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                        <button type="button" class="btn spinner-up default">
                                                                            <i class="fa fa-angle-up"></i>
                                                                        </button>
                                                                        <button type="button" class="btn spinner-down default">
                                                                            <i class="fa fa-angle-down"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 mt8">
                                                                <a class="popovers ml15"
                                                                   data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right"
                                                                   data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_EXP_THREADS_TIPS'];?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group ml25">
                                                            <label class="control-label col-md-3 transferthreadlabel"><?php echo $LANG['UI_DB_CDP_BACKUP_IMP_THREADS'];?></label>
                                                            <div class="col-md-6 imp_thread_div">
                                                                <div class="input-group spinner-group">
                                                                    <input type="num" id="imp_thread"
                                                                           onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                           class="spinner-input form-control input-sm">
                                                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                        <button type="button" class="btn spinner-up default">
                                                                            <i class="fa fa-angle-up"></i>
                                                                        </button>
                                                                        <button type="button" class="btn spinner-down default">
                                                                            <i class="fa fa-angle-down"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-2 mt8">
                                                                <a class="popovers ml15"
                                                                   data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right"
                                                                   data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_IMP_THREADS_TIPS'];?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group ml25">
                                    <label class="control-label col-md-3"><?php echo $LANG['UI_VOL_CDP_FAILBACK_ADVANCED_CONF_TEXT'];?></label>
                                    <div class="col-md-9 accordion">
                                        <div class="panel panel-default strategy-panel">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">
                                                    <a class="accordion-toggle accordion-toggle-styled popovers high_view collapsed"
                                                       data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".high_view" href="#failback_high_conf" aria-expanded="true">
                                                        <i class="viconfont vicon-ge_transfer font-green-seagreen"></i>
                                                        <label class="font-green-seagreen"><?php echo $LANG['UI_JOB_HIGH']; ?></label>
                                                    </a>
                                                </h4>
                                            </div>
                                            <div id="failback_high_conf" class="panel-collapse collapse" aria-expanded="true">
                                                <div class="panel-body">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_USE_DBMS'];?></label>
                                                            <div class="col-md-5 form-group-content">
                                                                <input type="checkbox" id="use_dbms_Switch" class="make-switch"
                                                                       data-on-color="primary" data-off-color="info"
                                                                       data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_USE_DBMS_TIPS'];?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_SYNC_BIG_OBJ'];?></label>
                                                            <div class="col-md-5 form-group-content">
                                                                <input type="checkbox" id="sync_big_object_Switch" class="make-switch"
                                                                       data-on-color="primary" data-off-color="info"
                                                                       data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_RECOVERY_SYNC_BIG_OBJ_TIPS'];?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group display-none">
                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_TIMED_GEN_TXN'];?></label>
                                                            <div class="col-md-5 form-group-content">
                                                                <input type="checkbox" id="timed_gen_txn_Switch" class="make-switch"
                                                                       data-on-color="primary" data-off-color="info"
                                                                       data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right" data-content="">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_REPLAY_DDL'];?></label>
                                                            <div class="col-md-5 form-group-content">
                                                                <input type="checkbox" id="replay_ddl_Switch" class="make-switch"
                                                                       data-on-color="primary" data-off-color="info"
                                                                       data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_REPLAY_DDL_TIPS'];?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_IGNORE_NONSUPPORT_DATA_TYPE'];?></label>
                                                            <div class="col-md-5 form-group-content">
                                                                <input type="checkbox" id="ignore_nonsupport_data_type_Switch" class="make-switch"
                                                                       data-on-color="primary" data-off-color="info"
                                                                       data-size="small"
                                                                       data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                                <a class="popovers ml15" data-container="body"
                                                                   data-trigger="hover"
                                                                   data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_IGNORE_NONSUPPORT_DATA_TYPE_TIPS'];?>">
                                                                    <i class="viconfont vicon-tishi"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-4 networkRetryTimeLabel">
                                                                <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?>
                                                            </label>
                                                            <div class="col-md-8">
                                                                <div id="network_retry_times_spinner" class="max-w-150px">
                                                                    <div class="input-group spinner-group">
                                                                        <input type="text"
                                                                                id="network_retry_times"
                                                                                onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                class="spinner-input form-control input-sm"
                                                                                maxlength="3">
                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                            <button type="button" class="btn spinner-up default input-sm">
                                                                                <i class="fa fa-angle-up"></i>
                                                                            </button>
                                                                            <button type="button" class="btn spinner-down default input-sm">
                                                                                <i class="fa fa-angle-down"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="help-block"><?php echo $LANG['UI_DB_CDP_FAILBACK_NETWORK_RETRY_TIMES_TIPS'];?></div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label col-md-4 retrytimelabel">
                                                                <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?>
                                                            </label>
                                                            <div class="col-md-8">
                                                                <div id="network_retry_interval_spinner" class="max-w-150px">
                                                                    <div class="input-group spinner-group">
                                                                        <input type="text"
                                                                                id="network_retry_interval"
                                                                                onkeyup="value=value.replace(/[^\d]/g,'')"
                                                                                class="spinner-input form-control input-sm"
                                                                                maxlength="3">
                                                                        <div class="spinner-buttons input-group-btn spinner-group-btn">
                                                                            <button type="button" class="btn spinner-up default input-sm">
                                                                                <i class="fa fa-angle-up"></i>
                                                                            </button>
                                                                            <button type="button" class="btn spinner-down default input-sm">
                                                                                <i class="fa fa-angle-down"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="help-block"><?php echo $LANG['UI_DB_CDP_FAILBACK_NETWORK_RETRY_INTERVAL_TIPS'];?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="alert alert-info">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <h4 class="alert-heading">
                                                                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                                            </h4>
                                                            <ol class="alert-ol">
                                                                <li><?php echo $LANG['UI_DB_CDP_BACKUP_IGNORE_NONSUPPORT_DATA_TYPE_TIP'];?></li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
                <button type="button" class="btn btn-primary"
                        id="failBackConfigSubmit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
            </div>
        </div>

        <!-- END TASK  FAILBACK MODAL-->
        <!-- 接管配置  START -->
        <div id="taskTakeOverModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">
                    <i class="viconfont vicon-huiqiepeizhi"></i> <?php echo $LANG['UI_VOL_CDP_TAKEOVER_CONFIGURE']; ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="col-md-12 accordion">
                        <div class="panel panel-default strategy-panel">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle accordion-toggle-styled popovers"
                                       data-container="body"
                                       data-trigger="hover" data-placement="top"
                                       data-toggle="collapse" href="#hostDetail"
                                       aria-expanded="true">
                                        <i class="viconfont vicon-shijian font-green-seagreen"></i>
                                        <span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TIME_FRAME'];?></span>
                                        <span id="timeRange"></span>
                                        (<span class="font-green-seagreen">scn:</span>
                                        <span id="replay_scn"></span>
                                        -
                                        <span id="transaction_scn"></span>)
                                    </a>
                                </h4>
                            </div>
                            <div id="hostDetail" class="panel-collapse collapse in">
                                <div class="panel-body min-height300">
                                    <div class="col-md-12 tabbable-custom pl15 pr15">
                                        <ul class="nav nav-tabs " id="timepointType">
                                            <li class="commonLi active" id="recoveryAnytimeLi">
                                                <a href="#anyPointTime" class="popovers" data-content="" data-container="" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                    <i class="viconfont vicon-renyishijiandian"></i>
                                                    <?php echo $LANG['UI_VOL_CDP_ANY_TIME_POINT'];?>
                                                </a>
                                            </li>
                                            <li class="highLi" id="agentTransactionInfoLi">
                                                <a href="#transactionInfo" class="popovers" data-content="" data-container="body" data-trigger="hover" data-placement="top" data-toggle="tab">
                                                    <i class="viconfont vicon-shijian1"></i>
                                                    <?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_TRANSACTION_INFORMATION'];?>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content min-height360" style="height: 90%;overflow-x:hidden;overflow-y: auto;">
                                            <div class="tab-pane active " id="anyPointTime">
                                                <div class="form-group" style="position: relative;z-index: 10;">
                                                    <div class="col-md-2 pl30 pt10"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE'];?></div>
                                                    <div class="col-md-4">
                                                        <select class="volcdp-time-range-select form-control select2me" id="changeTimeInterval">
                                                            <option value="1" selected><?php echo $LANG['UI_VOL_CDP_RECENT_TENMINS'];?></option>
                                                            <option value="2"><?php echo $LANG['UI_VOL_CDP_RECENT_ONEHOUR'];?></option>
                                                            <option value="3"><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_RECENT_ONEDAY'];?></option>
                                                            <option value="4"><?php echo $LANG['UI_REPORT_RECENT_WEEK'];?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div id="takeoverTimeline" style="width: 880px;height:300px;"></div>
                                            </div>
                                            <div class="tab-pane"  id="transactionInfo">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="alert alert-block alert-info fade in mb10">
                                                            <button type="button" class="close" data-dismiss="alert"></button>
                                                            <ul class="alert-ul">
                                                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                                                <li><?php echo $LANG['UI_DB_CDP_DATA_DISASTER_RECOVERY_TRANSACTION_INFORMATION_TIP'];?></li>
                                                            </ul>
                                                        </div>
                                                        <div class="portlet-body">
                                                        </div>
                                                        <table id="transactionInfoTable">
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt10 pd0">
                                            <label class="floatl pt-0" style="margin: 5px 65px 0 0;"><?php echo $LANG['UI_CM_CDP_TAKEOVER_MODE'];?></label>
                                            <div class="col-md-3">
                                                <select id="takeover_time_type" class="takeoverTimeType form-control select2me">
                                                </select>
                                            </div>
                                            <div class="col-md-2 mt5">
                                                <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-html="true" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_TAKEOVER_TIME_POINT_TYPE_TIPS']?>">
                                                    <i class="viconfont vicon-tishi"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt10 pd0 inputTakeoverTimeDIv display-none">
                                            <label class="floatl pt-0" style="margin: 5px 48px 0 0;"><?php echo $LANG['UI_DB_CDP_DETAILS_TAKE_OVER_TIME_POINT'];?></label>
                                            <div class="col-md-3">
                                                <input type="text" class="inputTakeoverTimeInput form-control input-sm" readonly>
                                            </div>
                                            <div class="col-md-2 mt5">
                                                <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_DETAILS_TAKEOVER_POINT_TIPS'];?>">
                                                    <i class="viconfont vicon-tishi"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt10 pd0 takeoverScnDIv display-none">
                                            <label class="floatl pt-0" style="margin: 5px 71px 0 0;"><?php echo $LANG['UI_DB_CDP_TAKEOVER_SCN'];?></label>
                                            <div class="col-md-3 mt5">
                                                <input type="number" class="form-control input-sm takeoverScn">
                                            </div>
                                        </div>

                                        <div class="col-md-12 mt10 pd0 takeoverTimeDiv display-none">
                                            <label class="floatl pt-0" style="margin: 5px 51px 0 0;"><?php echo $LANG['UI_DB_CDP_DETAILS_TAKE_OVER_TIME_POINT'];?></label>
                                            <div class="col-md-4">
                                                <div class="takeovertimepointview input-group date form_datetime">
                                                    <input id="inputTakeoverTimepoint" class="form-control" type="text" size="16">
                                                    <span class="input-group-btn">
                                                        <button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-1 mt5">
                                                <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_DETAILS_TAKEOVER_POINT_TIPS'];?>">
                                                    <i class="viconfont vicon-tishi"></i>
                                                </a>
                                            </div>
                                            <div id="timePointValidity" class="control-label col-md-2 pl0"><?php echo $LANG['UI_DB_CDP_BACKUP_VALID_TIME_POINT_TYPE'];?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt10 plr30 serviceIpDriftDiv ">
                                <label class="floatl pt-0" style="margin: 5px 55px 0 0;"><?php echo $LANG['UI_DB_CDP_BACKUP_SERVICE_IP_DRIFT'];?></label>
                                <div class="col-md-3 form-group-timepoint">
                                    <input type="checkbox" id="serviceIpDriftTakeover"
                                           class="make-switch"
                                           data-on-color="primary"
                                           data-off-color="info"
                                           data-size="small"
                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                </div>
                                <div class="col-md-2 mt5">
                                    <a class="popovers" data-container="body"
                                       data-trigger="hover"
                                       data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_ENABLE_SERVICE_IP_DRIFT_TO_HOST'];?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                            <div id="failbackTargetIpContent" class="col-md-12 display-none">
                                <label class="col-md-2 mt20"><?php echo $LANG['UI_DB_CDP_BACKUP_FAILBACK_IP'];?></label>
                                <div class="col-md-4 mt10">
                                    <input type="text" id="failbackTargetIp" class="form-control input-sm" value="">
                                </div>
                                <div class="col-md-2 mt15">
                                    <a class="popovers ml15" data-container="body"
                                       data-trigger="hover"
                                       data-placement="right" data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_FAILBACK_IP_NOTES'];?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                                <div class="col-md-3 ml-50 mt10">
                                    <button type="button" class="btn table-toolbar-btn" id="linkSelectIP">
                                        <span class="font-green-seagreen"><?php echo $LANG['UI_VOL_CDP_TAKEOVER_FAILBACK_IP_PING']; ?></span>
                                    </button>
                                </div>
                            </div>
                            <!-- 接管网络配置 -->
                            <div class="col-md-12 mt10 plr30 takeoverNetConf display-none">
                                <label class="floatl pt-0" style="margin: 15px 41px 0 0;"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></label>
                                <div class="col-md-8 mt10">
                                    <button type="button" class="btn green-haze" id="takeover_network_conf" data-toggle="drawer" data-target="#takeover_conf_drawer"><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF']; ?></button>
                                    <ul id="takeover_ip_map_list" style="padding-left: 0;width:90%;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
                <button type="button" class="btn btn-primary" id="takeOverConfigSubmit"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
            </div>
        </div>
        <div id="eventDetailModal" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-huiqiepeizhi"></i> <?php echo $LANG['UI_DB_CDP_RECOVER_EVENT_DETAILS'];?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="eventContent"></div>
                </div>
            </div>

        </div>
        <!-- 接管配置  END -->

        <!-- drawer开始 -->
        <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px"
             aria-labelledby="drawer-1_title" aria-hidden="true" id="drawer-1">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <div class="drawer-title" id="drawer-1_title"
                         style="display:flex;justify-content:space-between;align-items:center">
                        <div class="drawer-title-left">
                            <i class="levelchild viconfont vicon-tenant-detail"></i>
                            <span><?php echo $LANG['UI_MORE_CONFIG_DETAIL'] ?></span>
                        </div>
                        <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                            <i class="viconfont vicon-guanbi"></i>
                        </div>
                    </div>
                </div>

                <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
                <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
                    <!-- BEGIN COMMON STRATEGY GROUP -->
                    <div class="config-detail-wrap__group strategy-group">
                        <div class="strategy-group__header">
                            <i class="viconfont vicon-celve1 me-4"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
                        </div>
                        <!-- BEGIN SPEED LIMIT STRATEGY -->
                        <div class="strategy-group__title">
                            <span class="decoration me-4"></span><span
                                    class="strategy-group__title__text"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 限速策略 -->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></div>
                                <div id="limitTimeInfo" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                        </div>
                    </div>
                    <!-- BEGIN TRANSMIT STRATEGY GROUP -->
                    <div class="config-detail-wrap__group strategy-group">
                        <div class="strategy-group__header">
                            <i class="viconfont vicon-a-chuanshu me-4"></i>
                            <span class="strategy-group__header__text">
                                <?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?>
                            </span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 传输模式 -->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="transportMode">
                                </div>
                            </div>

                            <!-- 压缩传输 -->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_COPY_COMPRESS_TRANSFER']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="transportCompress">
                                </div>
                            </div>

                            <!--压缩方式-->
                            <div class="strategy-group__form__item align-items-baseline display-none transportCompressMethoddiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="transportCompressMethod">
                                </div>
                            </div>

                            <!-- 传输加密 -->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="transportEncrypt">
                                </div>
                            </div>

                            <!--加密方式-->
                            <div class="strategy-group__form__item align-items-baseline display-none transportEncryptMethoddiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_ENCRYPTED_TRANSMISSION_METHOD'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="transportEncryptMethod">
                                </div>
                            </div>

                            <!-- 导出线程个数 -->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_EXP_THREADS'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="exp_thread_num">
                                </div>
                            </div>
                            <!-- 导入线程个数 -->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_IMP_THREADS'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="imp_thread_num">
                                </div>
                            </div>

                            <!-- 传输网络 -->
                            <div class="strategy-group__form__item align-items-baseline transportNetworkDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="transportNetworkInfo">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- BEGIN ADVANCED CONFIG GROUP -->
                    <div class="config-detail-wrap__group strategy-group">
                        <div class="strategy-group__header">
                            <i class="viconfont vicon-a-chuanshu me-4"></i><span
                                    class="strategy-group__header__text"><?php echo $LANG['UI_VM_MACHINE_HIGH_SETT'];?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!--延迟时间-->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item delayLoadTimestatusdiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_DELAYED_LOADING'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="delayLoadStatus"></div>
                            </div>

                            <!-- 延迟加载时间 -->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item delayLoadTimediv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_DELAYED_LOADING_TIME'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="delayLoadTimeStr">
                                </div>
                            </div>

                            <!-- 源机文件缓存大小 -->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item sourceFileCacheSizeDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_SIZE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="source_fileCacheSizeStr">
                                </div>
                            </div>

                            <!-- 源机文件缓存路径 -->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item sourceFileCachePathDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_PATH'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="source_fileCachePathStr">
                                </div>
                            </div>

                            <!-- 备机文件缓存大小 -->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item backupFileCacheSizeDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_STANDBY_FILE_CACHE_SIZE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="backup_fileCacheSizeStr">
                                </div>
                            </div>

                            <!-- 备机文件缓存路径 -->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item backupFileCachePathDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_STANDBY_FILE_CACHE_PATH'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="backup_fileCachePathStr">
                                </div>
                            </div>

                            <!-- 数据类型 -->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item dataTypediv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_DATA_REPORT_BACKUP_MODE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="recover_dataTypeStr">
                                </div>
                            </div>

                            <!--dbms接口-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_USE_DBMS'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="use_dbms_value">
                                </div>
                            </div>

                            <!--同步大对象-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_SYNC_BIG_OBJ'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="sync_big_object_value">
                                </div>
                            </div>

                            <!--源端自动定时生成事务-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_TIMED_GEN_TXN'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="timed_gen_txn_value">
                                </div>
                            </div>

                            <!--日志同步时重放ddl-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_REPLAY_DDL'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="replay_ddl_value">
                                </div>
                            </div>

                            <!--忽略不支持的数据类型-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_IGNORE_NONSUPPORT_DATA_TYPE'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="ignore_nonsupport_data_type_value">
                                </div>
                            </div>

                            <!--是否开启资源限制-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="ignore_resource_limiting_value">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- drawer结束 -->

        <!-- 暂停状态修改配置START-->
        <div id="pauseEditConfigModel" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-huiqiepeizhi"></i> <?php echo $LANG['UI_CLUSTER_CONFIG_CHANGE'];?> </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="form-group delay_time_switch_div">
                        <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_DELAYED_LOADING'];?></label>
                        <div class="col-md-7">
                            <input type="checkbox"
                                   class="make-switch delay_time_switch" data-on-color="primary"
                                   data-off-color="info" data-size="small"
                                   data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                   data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            <a class="popovers ml15"
                               data-container="body"
                               data-trigger="hover"
                               data-html="true"
                               data-placement="right"
                               data-content="<?php echo $LANG['UI_DB_CDP_BACKUP_DELAYED_LOADING_TIP1'];?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div id="delay_time_div" class="form-group display-none">
                        <label class="control-label col-md-4"><?php echo $LANG['UI_DB_CDP_BACKUP_DELAYED_LOADING_TIME'];?></label>
                        <div class="col-md-7 form-group-content custom-input-group">
                            <input type="number" class="form-control w-50px days">
                            <span class="separator-text"><?php echo $LANG['WEB_SYSTEM_AUTH_DAY'];?></span>
                            <input type="number" class="form-control w-45px hours">
                            <span class="separator-text"><?php echo $LANG['WEB_PLATFORM_DC_HOURS'];?></span>
                            <input type="number" class="form-control w-45px minutes">
                            <span class="separator-text"><?php echo $LANG['WEB_UTILS_MINUTE'];?></span>
                            <input type="number" class="form-control w-45px seconds">
                            <span class="separator-text"><?php echo $LANG['WEB_UTILS_SECOND'];?></span>
                        </div>
                    </div>
                    <div class="form-group source_fileCacheSizeDiv">
                        <label class="control-label col-md-4 fileCacheSizelabel"><?php echo $LANG['UI_DB_CDP_BACKUP_SOURCE_MACHINE_FILE_CACHE_SIZE'];?></label>
                        <div class="col-md-4 source_custom_cache_size_div">
                            <div class="col-md-6 pl0">
                                <div class="input-group spinner-group">
                                    <input id="source_fileCacheSize" class="spinner-input form-control" type="text" onkeyup="value=value.replace(/[^\d]/g,'')">
                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                        <button type="button" class="btn spinner-up default">
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button" class="btn spinner-down default">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select name="source_file_cache_unit" id="" class="form-control select2me">
                                    <option value="1">GB</option>
                                    <option value="2">TB</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 source_cache_size_unlimited_div display-none">
                            <input class="form-control input-sm" value="<?php echo $LANG['UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE'];?>" style="display: inline" maxlength="128" readonly>
                        </div>
                        <div class="col-md-4">
                            <button class="fileCachePathButton unlimit_limit_toggle source_unlimit_limit_toggle" data-value = "source_cache_button"><?php echo $LANG['UI_DB_CDP_BACKUP_SWITCH_TO_UNLIMITED'];?></button>
                        </div>
                    </div>
                    <div class="form-group backup_fileCacheSizeDiv">
                        <label class="control-label col-md-4 fileCacheSizelabel"><?php echo $LANG['UI_DB_CDP_BACKUP_STANDBY_FILE_CACHE_SIZE'];?></label>
                        <div class="col-md-4 backup_custom_cache_size_div">
                            <div class="col-md-6 pl0">
                                <div class="input-group spinner-group">
                                    <input id="backup_fileCacheSize" class="spinner-input form-control" type="text" onkeyup="value=value.replace(/[^\d]/g,'')">
                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                        <button type="button" class="btn spinner-up default">
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button" class="btn spinner-down default">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select name="backup_file_cache_unit" id="" class="form-control select2me">
                                    <option value="1">GB</option>
                                    <option value="2">TB</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 backup_cache_size_unlimited_div display-none">
                            <input class="form-control input-sm" value="<?php echo $LANG['UI_DB_CDP_BACKUP_UNLIMITED_CACHE_SIZE'];?>" style="display: inline" maxlength="128" readonly>
                        </div>
                        <div class="col-md-4">
                            <button class="fileCachePathButton unlimit_limit_toggle backup_unlimit_limit_toggle" data-value = "backup_cache_button"><?php echo $LANG['UI_DB_CDP_BACKUP_SWITCH_TO_UNLIMITED'];?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']; ?></button>
                <button type="button" class="btn btn-primary" id="submit_high_config"><?php echo $LANG['UI_PUBLIC_YES']; ?></button>
            </div>
        </div>
        <!-- 暂停状态修改配置END-->

        <!--导入/导出详情drawer开始-->
        <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px"
             aria-labelledby="drawer-2_title" aria-hidden="true" id="drawer-2">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <div class="drawer-title" id="drawer-2_title"
                         style="display:flex;justify-content:space-between;align-items:center">
                        <div class="drawer-title-left">
                            <i class="levelchild viconfont vicon-a-Group1000003052"></i>
                            <span><?php echo $LANG['UI_DB_CDP_DETAILS_IMP_EXP_DETAILS'];?></span>
                        </div>
                        <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                            <i class="viconfont vicon-guanbi"></i>
                        </div>
                    </div>
                </div>
                <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
                <div id="import_export_div" class="drawer-body config-detail-wrap config-detail-drawer-backup">
                    <div class="config-detail-wrap__group strategy-group">
                        <div id="import_export_table"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--导入/导出详情drawer结束-->

        <!--事务详情drawer开始-->
        <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px"
             aria-labelledby="drawer-3_title" aria-hidden="true" id="drawer-3">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <div class="drawer-title" id="drawer-3_title"
                         style="display:flex;justify-content:space-between;align-items:center">
                        <div class="drawer-title-left">
                            <i class="levelchild viconfont vicon-shijian1"></i>
                            <span><?php echo $LANG['UI_DB_CDP_TRANSACTION_DETAILS'];?></span>
                        </div>
                        <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                            <i class="viconfont vicon-guanbi"></i>
                        </div>
                    </div>
                </div>
                <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
                <div id="transaction_details_div" class="drawer-body config-detail-wrap config-detail-drawer-backup">
                    <div class="config-detail-wrap__group strategy-group">
                        <div>
                            <div class="table-container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <table id="transaction_details_table">

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--事务详情drawer结束-->
        <!--回切配置详情显示drawer开始-->
        <div class="drawer slide failbackConfig" data-placement="right" tabindex="-1" role="dialog" style="width: 600px"
             aria-labelledby="drawer-4_title" aria-hidden="true" id="drawer-4">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <div class="drawer-title" id="drawer-4_title"
                         style="display:flex;justify-content:space-between;align-items:center">
                        <div class="drawer-title-left">
                            <i class="levelchild viconfont vicon-tenant-detail"></i>
                            <span><?php echo $LANG['UI_MORE_CONFIG_DETAIL'] ?></span>
                        </div>
                        <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                            <i class="viconfont vicon-guanbi"></i>
                        </div>
                    </div>
                </div>

                <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
                <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
                    <!-- BEGIN COMMON STRATEGY GROUP -->
                    <div class="config-detail-wrap__group strategy-group">
                        <div class="strategy-group__header">
                            <i class="viconfont vicon-celve1 me-4"></i>
                            <span class="strategy-group__header__text">
                                <?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?>
                            </span>
                        </div>
                        <!-- BEGIN SPEED LIMIT STRATEGY -->
                        <div class="strategy-group__form">
                            <!-- 回切目标 -->
                            <div class="strategy-group__form__item align-items-baseline failbackTargetDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_TARGET'];?>:
                                </div>
                                <div id="failbackTargetStr" class="strategy-group__form__item__value col-md-8"></div>
                            </div>

                            <!-- 回切网络配置 -->
                            <div class="strategy-group__form__item align-items-baseline failbackNetStrDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_NETWORK_CONF_TEXT'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="failbackNetStr">
                                </div>
                            </div>

                            <!-- 回切数据类型 -->
                            <div class="strategy-group__form__item align-items-baseline dataTypeDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_DATA_TYPE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="dataTypeStr">
                                </div>
                            </div>

                            <!--数据表空间存储目录-->
                            <div class="strategy-group__form__item align-items-baseline failbackAppTableSpaceDirDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_DATA_TABLE_SPACE_STORAGE_DIRECTORY'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="tableSpaceDirStr">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- BEGIN TRANSMIT STRATEGY GROUP -->
                    <div class="config-detail-wrap__group strategy-group">
                        <div class="strategy-group__header">
                            <i class="viconfont vicon-a-chuanshu me-4"></i>
                            <span class="strategy-group__header__text">
                                <?php echo $LANG['UI_DB_CDP_FAILBACK_STRATEGY'] ?>
                            </span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 回切策略 -->
                            <div class="strategy-group__form__item align-items-baseline failbackStrategyDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['BILLING_TABLE_TYPE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="failbackStrategyStr">
                                </div>
                            </div>

                            <!-- 触发自动回切阈值 -->
                            <div class="strategy-group__form__item align-items-baseline display-none freeTimeIntervalDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_FAILBACK_FREE_TIME_INTERVAL']?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="freeTimeIntervalStr">
                                </div>
                            </div>

                            <!-- 回切开始时间 -->
                            <div class="strategy-group__form__item align-items-baseline display-none serviceFailbackStartTimeDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_START_TIME']?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="serviceFailbackStartTimeStr">
                                </div>
                            </div>

                            <!-- 回切结束时间 -->
                            <div class="strategy-group__form__item align-items-baseline display-none serviceFailbackEndTimeDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_END_TIME'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="serviceFailbackEndTimeStr">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="config-detail-wrap__group strategy-group">
                        <div class="strategy-group__header">
                            <i class="viconfont vicon-a-chuanshu me-4"></i>
                            <span class="strategy-group__header__text">
                                <?php echo $LANG['UI_CM_CDP_BACKUP_CACHE_CONF'] ?>
                            </span>
                        </div>
                        <div class="strategy-group__form">
                            <!--源文件缓存大小-->
                            <div class="strategy-group__form__item align-items-baseline fileCacheSizeDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_FAILBACK_SOURCE_FILE_CACHE_SIZE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="failback_source_fileCacheSizeStr">
                                </div>
                            </div>

                            <!-- 文件缓存大小 -->
                            <div class="strategy-group__form__item align-items-baseline fileCacheSizeDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_FILE_CACHE_SIZE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="fileCacheSizeStr">
                                </div>
                            </div>

                            <!-- 文件缓存路径 -->
                            <div class="strategy-group__form__item align-items-baseline fileCachePathDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_FILE_CACHE_PATH'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="fileCachePathStr">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="config-detail-wrap__group strategy-group">
                        <div class="strategy-group__header">
                            <i class="viconfont vicon-a-chuanshu me-4"></i>
                            <span class="strategy-group__header__text">
                                <?php echo $LANG['UI_VOL_CDP_FAILBACK_TRANSFER_CONF_TEXT'] ?>
                            </span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 传输数据压缩 -->
                            <div class="strategy-group__form__item align-items-baseline dataCompressDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_DATA_COMPRESSION'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="dataCompressFlag">
                                </div>
                            </div>

                            <!-- 压缩等级 -->
                            <div class="strategy-group__form__item align-items-baseline dataCompressStrDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_COMPRESS_GRADE'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="dataCompressStr">
                                </div>
                            </div>

                            <!--加密传输-->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item encryptTransmissDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_DETAILS_FAILBACK_ENCRYPTED_TRANSMISSION'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="encryptTransmissFlag"></div>
                            </div>

                            <!--加密传输方式-->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item encryptTransmissStrDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_ENCRYPTED_TRANSMISSION_METHOD'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="encryptTransmissStr"></div>
                            </div>

                            <!-- 导出线程个数 -->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item threadNumDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_FAILBACK_EXP_THREAD_NUM'];?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="exp_thread_num_str">
                                </div>
                            </div>

                            <!-- 导入线程个数 -->
                            <div class="strategy-group__form__item backup-encrypt-transmit-form-item threadNumDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_FAILBACK_IMP_THREAD_NUM']?>:
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="imp_thread_num_str">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BEGIN ADVANCED CONFIG GROUP -->
                    <div class="config-detail-wrap__group strategy-group">
                        <div class="strategy-group__header">
                            <i class="viconfont vicon-a-chuanshu me-4"></i>
                            <span class="strategy-group__header__text">
                                <?php echo $LANG['UI_VM_MACHINE_HIGH_SETT'];?>
                            </span>
                        </div>
                        <div class="strategy-group__form">
                            <!--dbms接口-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_USE_DBMS'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="use_dbms_value">
                                </div>
                            </div>

                            <!--同步大对象-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_SYNC_BIG_OBJ'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="sync_big_object_value">
                                </div>
                            </div>

                            <!--日志同步时重放ddl-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_REPLAY_DDL'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="replay_ddl_value">
                                </div>
                            </div>

                            <!--忽略不支持的数据类型-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_CDP_BACKUP_IGNORE_NONSUPPORT_DATA_TYPE'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="ignore_nonsupport_data_type_value">
                                </div>
                            </div>

                            <!--网络重连次数-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="network_retry_times_value">
                                </div>
                            </div>
                            <!--网络重连时间间隔(秒)-->
                            <div class="strategy-group__form__item align-items-baseline">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'];?>
                                </div>
                                <div class="strategy-group__form__item__value col-md-8" id="network_retry_interval_value">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--回切配置详情显示drawer结束-->
        <!-- 接管网络drawer开始 -->
        <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 800px"
             aria-labelledby="drawer-1_title" aria-hidden="true" id="takeover_conf_drawer">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <div class="drawer-title" id="drawer-1_title"
                         style="display:flex;justify-content:space-between;align-items:center">
                        <div class="drawer-title-left">
                            <i class="viconfont vicon-a-Setting-twoshezhi"></i>
                            <span><?php echo $LANG['UI_VOL_CDP_TASK_TAKEOVER_NET_CONF'];?></span>
                        </div>
                        <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                            <i class="viconfont vicon-guanbi"></i>
                        </div>
                    </div>
                </div>
                <!-- BEGIN BACKUP TASK DETAIL DRAWER BODY -->
                <div class="drawer-body">
                    <!-- BEGIN COMMON STRATEGY GROUP -->
                    <div class="col-md-12 accordion pl0">
                        <div class="portlet">
                            <div class="portlet-body">
                                <div id="" class="panel-collapse collapse in form-horizontal">
                                    <div class="nav-tabs-wrapper">
                                        <ul class="nav nav-tabs nav-line-tabs" id="">
                                            <!-- 通用配置-->
                                            <li class="nav-item active">
                                                <a href="#ipConfig" class="nav-link" data-toggle="tab" aria-expanded="true">
                                                    <?php echo $LANG['UI_VOL_CDP_TASK_IP_SERVICE_CONF'];?>
                                                </a>
                                            </li>
                                            <li class="nav-item backupGateway_li display-none">
                                                <a href="#backupGateway" class="nav-link" data-toggle="tab" aria-expanded="true">
                                                    <?php echo $LANG['UI_VOL_CDP_STANDBY_GATEWAY_CONF'];?>
                                                </a>
                                            </li>
                                        </ul>
                                        <div class="tab-content border-bottom-none pt-0">
                                            <div id="ipConfig" class="tab-pane active">
                                                <div id="host_ip_server_conf" class="pt-24">
                                                </div>
                                            </div>
                                            <div id="backupGateway" class="tab-pane backupGateway_div">
                                                <div id="standby_gateway_conf">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="drawer-footer">
                    <div class="row">
                        <div class="col-md-6" style="float: right;padding-right: 10px;">
                            <button type="button" class="btn green-haze btn-confirm" id="submit_host_network_conf">
                                <?php echo $LANG['UI_PUBLIC_YES'] ?>
                            </button>
                            <button type="button" class="btn default cancel">
                                <?php echo $LANG['UI_PUBLIC_NO'] ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- 接管网络drawer结束 -->

        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
        <script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
        <script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.js"></script>
        <?php
        if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
            echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
        }
        ?>

        <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.core.min.js"></script>
        <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.excheck.min.js"></script>
        <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exedit.min.js"></script>
        <script type="text/javascript" src="./scripts/plugins/ztree/js/jquery.ztree.exhide.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
        <script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
        <script type="text/javascript" src="./scripts/dbcdp/takeover_ip_server_map.js"></script>
        <script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
        <script type="text/javascript" src="./scripts/dbcdp/dbcdp_job_details.js"></script>

