<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./scripts/plugins/popModal/popModal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<link rel="stylesheet" type="text/css" href="./css/platform/verification.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb <?php if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){echo "display-hide";};?>">
    <li>
        <?php
        if ($_GET['orcPlan']) {
            $url = './content/platform/orchestration/orchestration_details.php?uuid=' . $_GET['orcPlan'];
            echo "<a href=\"$url\" class='ajaxify' name='task'><span>" .  $LANG['UI_JOB_TASK_ORCHESTRATION_DETAILS'] . "</span></a>";
        } else {
            echo '<a class="ajaxify" name="task" href=" ./content/platform/jobs/jobs.php"><span>' .  $LANG['UI_PLATFORM_CURRENT_JOB'] . '</span></a>';
        }
        ?>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_JOB_DETAILS'] ?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail" id="jobDetail">
    <div class="col-md-12 job-detail__halftop">
        <div class="col-md-8 showDiv job-detail__halftop__charts">
            <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
            <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
            <input id="oem_version" value="<?php echo $CONF['SYSTEM_INFO']['vendor'];?>" class="display-none"></input>
            <!-- BEGIN DYNAMIC CHART PORTLET-->
            <div class="portlet-charts">
                <div class="portlet-charts__title">
                    <div class="caption">
                        <i class="viconfont vicon-ge_vm"></i> <?php echo $LANG['UI_VERIFY_HOST_INFO'] ?>
                        <a class="popovers ml15" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_VERIFY_HOST_INFO_TIPS'] ?>">
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <div class="portlet-charts__body overflowy-auto">
                    <div class="tab-content row">
                        <div class="tab-pane active plr15" >
                            <ul id="objectList" >

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END DYNAMIC CHART PORTLET-->


        <div class="col-md-4 configDiv job-detail__halftop__navtabs">
            <!-- BEGIN PORTLET-->
            <div class="portlet">
                <div class="portlet-body">
                    <!--BEGIN TABS-->
                    <div class="portlet-title init_tab_title">
                        <i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
                    </div>
                    <div class="tab-content" style="padding:16px;">
                        <div class="tab-pane active" id="tab_1_1">
                            <div class="portlet-body">
                                <div class="row static-info ">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>:
                                    </div>
                                    <div class="col-md-8 col-operate value">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle hover-initialized" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                                <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
                                            </button>
                                            <ul class="dropdown-menu min-width100" role="menu">
                                                <li id="startJob"><a href="javascript:;"><i class="viconfont vicon-ge_play"></i><?php echo $LANG['WEB_JOB_START'] ?></a></li>
                                                <li id="stopJob"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i><?php echo $LANG['UI_VERIFY_STOP'] ?></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_JOB_RNAME'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="taskName" style="word-break:break-word; max-width:300px;">
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="taskType">
                                    </div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_JOB_TYPE'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="status">
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
                                <div class="row static-info">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="taskStage">
                                    </div>
                                </div>
                                <div class="row static-info <?php if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){ echo 'display-hide';}?>">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_VERIFY_MODE'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="verifyMode">
                                    </div>
                                </div>
                                <div class="row static-info verifyTypeDiv">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_VERIFY_TYPE'] ?>:
                                    </div>
                                    <div class="col-md-8 value" id="verifyType">
                                    </div>
                                </div>
                                <!-- 更多详请 -->
                                <div class="row static-info">
                                    <div class="col-md-4 name">
                                        <?php echo $LANG['UI_PUBLIC_MORE_CONFIGURATION'] ?>:
                                    </div>
                                    <div class="col-md-8 value">
                                        <a id="details_more" class="green-haze" data-toggle="drawer"
                                           data-target="#drawer-1" href="javascript:void(0)" style="color: #0FBF98;">
                                            <?php echo $LANG['UI_VERIFY_CHECK_DETAILS'] ?>
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!--END TABS-->
                </div>
            </div>
            <!-- END PORTLET-->
        </div>

    </div>
    <div class="col-md-12 job-detail__halfbottom">
        <!-- BEGIN TAB PORTLET-->
        <div class="job-detail__halfbottom__portlet">
            <div class="portlet-title">
                <ul class="nav nav-tabs floatl">
                    <li class="active ">
                        <a href="#log" data-toggle="tab">
                            <i class="viconfont viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
                    </li>
                    <li>
                        <a href="#objects" data-toggle="tab">
                            <i class="viconfont vicon-pt_report_vm_report "></i> <?php echo $LANG['UI_VERIFY_OBJECT_LIST'] ?> </a>
                    </li>
                    <li id="historyli">
                        <a href="#history" data-toggle="tab">
                            <i class="viconfont viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_HISTORY'] ?> </a>
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

                    <div class="tab-pane" id="objects">
                        <div class="table-container">
                            <div class="vin_toolbar" id="object_detail_toolbar">
                                <div class="leftTool">

                                </div>
                                <div class="rightTool">
                                    <div class="vin_btnToolbar"></div>
                                </div>
                            </div>
                            <table id="objectTable"></table>
                        </div>
                    </div>

                    <div class="tab-pane" id="history">
                        <div class="vin_toolbar" id="history_toolbar">
                            <div class="leftTool">
                            </div>
                            <div class="rightTool">
                                <div class="vin_btnToolbar"></div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table id="historyTable"></table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- END TAB PORTLET-->
        <!-- BEGIN MODAL -->
        <div id="reportModal" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-add_verification_job"></i><?php echo $LANG['UI_VERIFY_REPORT'] ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div id="reportContent">
                    </div>
                </div>
            </div>

            <!-- modal-footer -->
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="downloadReport"><?php echo $LANG['UI_VERIFY_REPORT_DOWNLOAD'] ?></button>
                <button type="button" class="btn btn-primary" id="reportEmail"><?php echo $LANG['UI_VERIFY_REPORT_SEND_TO_EMAIL'] ?></button>
            </div>
        </div>
        <!-- END MODAL -->


        <!-- BEGIN DETAIL DRAWER -->
        <div class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
             aria-labelledby="drawer-1-title" aria-hidden="true" id="object_detail_drawer" style="width: 600px;">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <h4 class="drawer-title">
                   <span id="objectname">
                    </span>
                        <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                                    class="viconfont vicon-guanbi"></i></span>
                    </h4>
                </div>
                <div class="drawer-body">
                    <!-- 基本信息 -->
                    <div class="row static-info">
                        <div class="advanced-conf-title-icon floatl mt2"></div>
                        <div class="floatl ">
                            <span class="ms-12"><?php echo $LANG['UI_VM_MACHINE_BASIC_INFO'] ?></span>
                        </div>
                    </div>
                    <div class="row static-info hypervisorDiv display-hide">
                        <div class="col-md-3 name">
                            <?php echo $LANG['UI_VCENTER_TYPE'] ?>
                        </div>
                        <div class="col-md-8 value" id="hypervisor">
                        </div>
                    </div>
                    <div class="row static-info">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_APP_GROUP_SELECT_POINT'] ?>
                        </div>
                        <div class="col-md-8 value" id="timepoint">
                        </div>
                    </div>
                     <div class="row static-info timepointDiv display-hide">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_TIMEPOINT_INFO'] ?>
                        </div>
                        <div class="col-md-8 value" id="timepointInfo">
                        </div>
                    </div>
                    <div class="row static-info virusDiv display-hide">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK'] ?>
                        </div>
                        <div class="col-md-8 value" id="virus_check_flag">
                        </div>
                    </div>
                   
                    <!-- 主机配置 -->
                    <div class="row hostSetDiv static-info">
                        <div class="advanced-conf-title-icon floatl mt2"></div>
                        <div class="floatl ">
                            <span class="ms-12"><?php echo $LANG['UI_DB_AGENT_CONIFG'] ?></span>
                        </div>
                    </div>
                    <div class="row hostSetDiv static-info">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VIRTUAL_MACHINE_CPU_MODE'] ?>
                        </div>
                        <div class="col-md-8 value" id="cpuMode">
                        </div>
                    </div>
                    <div class="row hostSetDiv static-info">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_CPU_NUM'] ?>
                        </div>
                        <div class="col-md-8 value" id="cpuNum">
                        </div>
                    </div>
                    <div class="row hostSetDiv static-info">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_CORE_NUM'] ?>
                        </div>
                        <div class="col-md-8 value" id="coreNum">
                        </div>
                    </div>
                    <div class="row hostSetDiv static-info">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_MEMORY_SIZE'] ?>
                        </div>
                        <div class="col-md-8 value" id="memorySize">
                        </div>
                    </div>
                    <div class="row hostSetDiv static-info osTypeDiv">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_OS_TYPE'] ?>
                        </div>
                        <div class="col-md-8 value" id="osType">
                        </div>
                    </div>
                    <div class="row hostSetDiv static-info osTypeDiv">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_PUBLIC_OS_VERSION'] ?>
                        </div>
                        <div class="col-md-8 value" id="osVersion">
                        </div>
                    </div>

                    <div class="row hostSetDiv static-info ">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_OBJECT_DISK_TARGET_BUS'] ?>
                        </div>
                        <div class="col-md-8 value" id="disk_target_bus">
                        </div>
                    </div>

                    <div class="row hostSetDiv static-info ">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_OBJECT_NETCARD_TARGET_BUS'] ?>
                        </div>
                        <div class="col-md-8 value" id="netcard_target_bus">
                        </div>
                    </div>

                    <div class="row hostSetDiv static-info display-hide">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_VERIFY_OBJECT_PING_WAIT_TIME'] ?>
                        </div>
                        <div class="col-md-8 value" id="max_ping_wait_time">
                        </div>
                    </div>

                    <div class="row hostSetDiv static-info ">
                        <div class="col-md-3 name ">
                            <?php echo $LANG['UI_SYSTEM_MONITOR_NET_MSG'] ?>
                        </div>
                        <div class="col-md-9 value" id="networkInfo">
                        </div>
                    </div>
                    <table id="networkTable" class="hostSetDiv"></table>
                </div>
                <!-- footer -->
                <div class="drawer-footer">
                    <div class="drawer-footer-div">
                        <a href="javascript:;" class="btn default mr10 floatr" data-dismiss="drawer" aria-label="Close" >
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- END ADD DRAWER -->

        <!-- popover stard -->
        <!-- PopoverX content -->
        <div id="networkModal" class="display-none">
            <div class="modal-body">
                <div class="portlet-body">
                    <div id="networkList">
                    </div>
                </div>
            </div>
        </div>
        <!-- popover end -->
    </div>
</div>
<!-- END PAGE CONTENT-->
<!-- drawer开始 -->
<div class="drawer slide aws-drawer-width_en" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <div class="drawer-title" id="nas_more_detail_drawer_title"  style="display:flex;justify-content:space-between;align-items:center">
                <div class="drawer-title-left">
                    <i class="viconfont vicon-tenant-detail mr10"></i>
                    <span><?php echo $LANG['UI_MORE_CONFIG_DETAIL'] ?></span>
                </div>
                <div class="drawer-title-right drawer-close" data-dismiss="drawer" aria-label="Close">
                    <i class="viconfont vicon-guanbi"></i>
                </div>
            </div>
        </div>
        <div class="drawer-body config-detail-wrap config-detail-drawer-backup">
            <!-- 通用策略 -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-celve1 me-4"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
                </div>

                <!-- BEGIN TIME STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                            class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_TIME'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 时间策略 -->
                    <div class="strategy-group__form__item align-items-baseline">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_CREATE_TIME'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="createTime">
                        </div>
                    </div>
                    <div class="strategy-group__form__item align-items-baseline backup_style">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8">

							<span class="label label-success" id="nextTime">
							</span>
                        </div>
                    </div>

                    <div class="strategy-group__form__item align-items-baseline backup_style">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_STRATEGY_TIME'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="timeStrategy">
                        </div>
                    </div>
                </div>
                <!-- END TIME STRATEGY -->
            </div>
            <!-- BEGIN ADVANCED CONFIG GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-gaojipeizhi me-4"></i><span
                            class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
                </div>

                <!-- BEGIN VERIFICATION GROUP -->
                <div class="">
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_PLATFORM_VERIFICATION'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 备份节点IP地址 -->
                        <div class="strategy-group__form__item passfilealarm-form-item ">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VERIFY_BACKUP_SERVER_IP'] ?></div>
                            <div id="backupServerIP" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 存储挂载IP/域名 -->
                        <div class="strategy-group__form__item passfilealarm-form-item mountDiv display-hide">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VERIFY_STORAGE_MOUNT'] ?></div>
                            <div id="serverIP" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 并发验证对象数量 -->
                        <div class="strategy-group__form__item passfilealarm-form-item threadNumDiv">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_VERIFY_MANAGE_HOST_NUM_MEANWHILE'] ?></div>
                            <div id="deal_vm_num" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 忽略节点资源限制 -->
                            <div class="strategy-group__form__item passfilealarm-form-item">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></div>
                                <div id="ignore_resource_limit" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                        </div>
                        <!-- 应用组 -->
                        <div class="strategy-group__form__item passfilealarm-form-item  <?php if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){echo "display-hide";};?>">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_PLATFORM_LAB_APP_GROUP'] ?></div>
                            <div id="appgroupname" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 虚拟演练室 -->
                        <div class="strategy-group__form__item passfilealarm-form-item  <?php if($CONF['SYSTEM_INFO']['vendor'] == $CONF['VENDOR_LIST']['gmp']){echo "display-hide";};?> labshowDiv">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_PLATFORM_VIRTUAL_LAB'] ?></div>
                            <div id="labname" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
                <!-- END VERIFICATION GROUP -->
            </div>
            <!-- END ADVANCED CONFIG GROUP -->
        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
        </div>
    </div>
</div>
<!-- drawer结束 -->
<!-- 截屏报告分析 -->
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/industry/job_report.php';?>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="/assets/global/plugins/g2/g2.min.js"></script>
<script type="text/javascript" src="/assets/global/plugins/anime/anime.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/exportpdf/html2canvas.js"></script>
<script type="text/javascript" src="./scripts/plugins/exportpdf/jspdf.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/popModal/popModal.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/dataverification/verification_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->