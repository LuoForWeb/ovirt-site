<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<!-- END PAGE LEVEL STYLES -->
<style type="text/css">
    .drawer-body .details_more_box{
        margin-bottom: 20px;

    }
    .drawer-body .details_more_head{
        font-weight: 400;
        font-size: 14px;
        color: #333333;
        line-height: 14px;
        font-style: normal;
        text-transform: none;
        margin-left: 10px;
    }
    .drawer-body .details_more_content_body{
        padding: 20px 0;
    }
    .drawer-body .sub-title{
        padding: 0;
    }

    .drawer-body .details_more_hr{
        border-bottom: dashed 1px #E6E6E6;
        margin: 0 0 20px 0;
    }
    .drawer-body .static-info .name{
        font-size: 12px;
    }
    .drawer-body .static-info .value{
        font-size: 12px;
    }

    #vm-table li span{
        color: #999999;
    }
    .dropdown-menu li a i{
        margin-right: 4px;
    }

</style>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
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
    <span class="curent"><?php echo $LANG['UI_PLATFORM_JOB_DETAILS']?></span>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="row job-detail" id="jobDetail">
    <div class="col-md-12 job-detail__halftop">
        <div class="col-md-8 job-detail__halftop__charts">
            <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
            <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
            <input id="task_type" value="<?php echo $_GET['type']; ?>" class="display-none"></input>
            <!-- BEGIN DYNAMIC CHART PORTLET-->
            <div class="portlet-charts">
                <div class="portlet-charts__title">
                    <div class="caption">
                        <i class="viconfont vicon-ge_task_flow"></i> <?php echo $LANG['UI_JOB_FLOW'] ?>
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
                            <div id="total-progress" class="progress-bar progress-bar-success" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
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
                    <div class="portlet-title init_tab_title">
                        <i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
                    </div>
                    <div class="tab-content" style="padding:16px;overflow-y: scroll;">
                        <div class="portlet-body">
                            <div class="row static-info <?php if (!in_array("p_current_job_manager", $_SESSION['permissionArr'])) {echo 'display-none';}?>">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>:
                                </div>
                                <div class="col-md-8 col-operate value">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle hover-initialized" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                            <?php echo $LANG['UI_PUBLIC_OPERATION']?> <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu min-width100" role="menu">
                                            <li id="startJob"><a href="javascript:;"><i class="viconfont vicon-ge_play"></i><?php echo $LANG['WEB_JOB_START'] ?></a></li>
                                            <li id="stopJob"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i><?php echo $LANG['UI_VERIFY_STOP'] ?></a></li>
                                            <li id="delJob"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i><?php echo $LANG['UI_PUBLIC_DELETE'] ?></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_TASK_REPORT_TASK_NAME'] ?>:
                                </div>
                                <div class="col-md-8 value col-taskname" id="taskName" style="word-break:break-word; max-width:300px;">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_TASK_REPORT_TASK_TYPE'] ?>:
                                </div>
                                <div class="col-md-8 value" id="taskType">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
                                </div>
                                <div class="col-md-8 value" id="taskStage">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PLATFORM_RECOVERY_DIRECTION'];?>:
                                </div>
                                <div class="col-md-8 value" id="taskDirection">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE'] ?>:
                                </div>
                                <div class="col-md-8 value" id="status">
                                </div>
                            </div>
                            <div class="row static-info">
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

                            <!-- 更多详请 -->
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PUBLIC_MORE_CONFIGURATION'];?>:
                                </div>
                                <div class="col-md-8 value">
                                    <a id="details_more" class="green-haze" data-toggle="drawer" data-target="#drawer-1" style="color:#0fbf98;">
                                        <?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_DETAILS'];?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- END PORTLET-->
        </div>

    </div>
    <div class="col-md-12 job-detail__halfbottom">
        <!-- BEGIN TAB PORTLET-->
        <div class="job-detail__halfbottom__portlet">
            <div class="portlet-title">
                <ul class="nav nav-tabs">
                    <li class="active ">
                        <a href="#log" data-toggle="tab">
                            <i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
                    </li>
                    <li>
                        <a href="#vms" data-toggle="tab">
                            <i class="viconfont vicon-a-View-listxiangqingliebiao "></i> <?php echo $LANG['UI_PLATFORM_RECOVERY_JOB_OBJECT'];?> </a>
                    </li>
                    <li id="historyli">
                        <a href="#history" data-toggle="tab">
                            <i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_HISTORY'] ?> </a>
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

                    <div class="tab-pane" id="vms">
                        <div class="table-toolbar">
                            <div class="leftTool">
                                <div class="search input-group mr12">
                                    <input type="search" maxlength="128" id="searchVal" class="searchinput customSearch" autocomplete="off"
                                           style="padding-right:32px;min-width: 220px;" maxlength="64" type="text"
                                           placeholder="<?php echo $LANG['UI_RECOVERY_PLATFORM_SERACH'] ?>">
                                    <div class="position0" style="width:auto;height:34px">
                                        <button class="b-btn clear hide position0" id="clearSearchBtn">
                                            <i class="icon-close-small"></i></button>
                                    </div>
                                    <div class="search-btn positionL0" style="width:auto;height:34px;">
                                        <button class="b-btn search-btn" id="searchSubmit"><i class="icon-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table id="vm-table"></table>
                        </div>
                    </div>

                    <div class="tab-pane" id="history">
                        <div class="table-container">
                            <table id="historytable"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END TAB PORTLET-->

    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- drawer开始 -->
<div class="drawer slide aws-drawer-width_en" data-placement="right" tabindex="-1" role="dialog" style="width: 600px" aria-labelledby="drawer-1-title" aria-hidden="true" id="drawer-1">
    <div class="drawer-content drawer-content-scrollable strategy-group" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title">
                <i class="viconfont vicon-tenant-detail mr8"></i><?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_DETAILS'];?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body strategy-group__form config-detail-wrap">
            <div class="details_more_box" style="margin-top: 20px;">
                <div class="details_more_head">
                    <div class="row">
                        <i class="viconfont vicon-celve1 mr8"></i><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'];?>
                    </div>
                </div>
                <div class="details_more_content">
                    <div class="details_more_content_body">
                        <div class="row static-info">
                            <div class="advanced-conf-title-icon floatl mt2"></div>
                            <div class="floatl ">
                                <span class="ms-12"><?php echo $LANG['WEB_COMMON_TIME_STRATEGY'];?></span>
                            </div>
                        </div>

                        <!-- 恢复任务时间策略 -->
                        <div class="strategy-group__form__item row static-info recoveryDiv recoveryTimeDiv">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_RECOVERY_START_TYPE'] ?>
                            </div>
                            <div class="col-md-8 strategy-group__form__item__value" id="recoveryTimeDes">
                            </div>
                        </div>

                        <!-- 定时恢复时间 -->
                        <div class="row static-info strategy-group__form__item start-time-form">
                            <div class="col-md-4 strategy-group__form__item__label">
                                <?php echo $LANG['UI_RECOVERY_START_TYPE_TIMING_TIME'] ?>
                            </div>
                            <div id="start_time" class="strategy-group__form__item__value col-md-8"></div>
                        </div>

                        <!--限速-->
                        <div class="row static-info">
                            <div class="advanced-conf-title-icon floatl mt2"></div>
                            <div class="floatl ">
                                <span class="ms-12"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'];?></span>
                            </div>
                        </div>
                        <div class="row static-info strategy-group__form__item">
                            <div class="col-md-4 strategy-group__form__item__label">
                                <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?>
                            </div>
                            <div class="col-md-8 strategy-group__form__item__value" id="speedlimit" style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;">
                            </div>
                        </div>

                        <!-- 任务等级 -->
                        <div class="row static-info taskPriorityDiv strategy-group__form__item">
                            <div class="col-md-4 strategy-group__form__item__label">
                                <?php echo $LANG['UI_JOB_TASK_PRIORITY'] ?>
                            </div>
                            <div class="col-md-8 strategy-group__form__item__value" id="taskPriority">
                            </div>
                        </div>
                    </div>
                    <hr class="details_more_hr">
                </div>
            </div>

            <div class="details_more_box">
                <div class="details_more_head">
                    <div class="row">
                        <i class="viconfont vicon-a-chuanshu mr8"></i><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'];?>
                    </div>
                </div>
                <div class="details_more_content">
                    <div class="details_more_content_body">
                        <span class="vol_cdp_strategy display-none">
                            <!--实时策略 start -->
                            <!--传输模式-->
                            <div class="row static-info strategy-group__form__item transportNetworkDiv">
                                <div class="col-md-4 strategy-group__form__item__label">
                                    <?php echo $LANG['UI_BACKUP_TRANSPORT_MODE']; ?>
                                </div>
                                <div class="col-md-8 strategy-group__form__item__value" id="transportNetworkMode">
                                </div>
                            </div>
                            <!-- 传输网络 -->
                            <div class="row static-info strategy-group__form__item transportNetworkDiv">
                                <div class="col-md-4 strategy-group__form__item__label">
                                    <?php echo $LANG['UI_NODE_TRANSFER_NETWORK']; ?>
                                </div>
                                <div class="col-md-8 strategy-group__form__item__value" id="transportNetworkInfo">
                                </div>
                            </div>
                            <div class="transportpolicyview">
                                <!-- 传输加密 -->
                                <div class="row static-info strategy-group__form__item">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportEncrypt">
                                    </div>
                                </div>
                                <!-- 传输加密算法 -->
                                <div class="row static-info transfer-encrypt-method-div strategy-group__form__item">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transferEncryptMethod">
                                    </div>
                                </div>
                                <!-- 压缩传输 -->
                                <div class="row static-info strategy-group__form__item transportCompressdiv">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_COPY_COMPRESS_TRANSFER']; ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportCompress">
                                    </div>
                                </div>
                                <!-- 压缩等级 -->
                                <div class="row static-info strategy-group__form__item transportCompressMethodDiv display-none">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportCompressMethod">
                                    </div>
                                </div>
                                <!-- 传输线程个数 -->
                                <div class="row static-info strategy-group__form__item transportThreadNumdiv">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS']; ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportThreadNum">
                                    </div>
                                </div>

                                <!-- 传输大小 -->
                                <div class="row static-info strategy-group__form__item transportPacketSizediv">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_VOL_CDP_TRANSMISSION_PACKET_SIZE']; ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportPacketSize">
                                    </div>
                                </div>
                            </div>
                            <!--实时策略 end -->
                        </span>

                        <span class="machine_strategy display-none">
                            <!--整机策略 start -->
                            <div class="transportpolicyview">
                                <!-- 传输加密 -->
                                <div class="row static-info strategy-group__form__item">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportEncrypt1">
                                    </div>
                                </div>
                                <!-- 传输加密算法 -->
                                <div class="row static-info transfer-encrypt-method-div1 strategy-group__form__item">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transferEncryptMethod1">
                                    </div>
                                </div>

                                <!-- 传输线程个数 -->
                                <div class="row static-info  transportThreadNumdiv strategy-group__form__item">
                                    <div class="col-md-4 strategy-group__form__item__label">
                                        <?php echo $LANG['UI_VOL_CDP_NUMBERS_THREADS']; ?>
                                    </div>
                                    <div class="col-md-8 strategy-group__form__item__value" id="transportThreadNum1">
                                    </div>
                                </div>
                            </div>
                            <!--整机策略 end -->
                        </span>

                        <span class="vm_strategy strategy-group display-none">
                            <!--虚拟机策略 start -->
                            <?php include_once '../../platform/component/vm_job_transfer.php'; ?>
                            <!--虚拟机策略 end -->
                        </span>

                    </div>
                    <hr class="details_more_hr">
                </div>
            </div>
            <div class="details_more_box safe-and-complete">
                <div class="details_more_head">
                    <div class="row">
                        <i class="viconfont vicon-anquancelve mr8"></i><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'];?>
                    </div>
                </div>
                <div class="details_more_content">
                    <div class="details_more_content_body">
                        <div class="row static-info virusConfig strategy-group__form__item">
                            <div class="col-md-4 strategy-group__form__item__label">
                                <?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK'];?>
                            </div>
                            <div class="col-md-8 strategy-group__form__item__value" id="virusConfig">

                            </div>
                        </div>
                        <div class="row static-info completeConfig strategy-group__form__item">
                            <div class="col-md-4 strategy-group__form__item__label">
                                <?php echo $LANG['UI_RECOVERY_STRATEGY_COMPLETE'];?>
                            </div>
                            <div class="col-md-8 strategy-group__form__item__value" id="completeConfig">
                            </div>
                        </div>
                    </div>
                    <hr class="details_more_hr">
                </div>
            </div>

            <div class="details_more_box">
                <div class="details_more_head">
                    <div class="row">
                        <i class="viconfont vicon-gaojipeizhi mr8"></i><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'];?>
                    </div>
                </div>
                <div class="details_more_content">
                    <div class="details_more_content_body">

                        <!---------- 重试策略 ---------->
                        <div class="retryDiv">
                            <div class="row static-info">
                                <div class="advanced-conf-title-icon floatl mt2"></div>
                                <div class="floatl ">
                                    <span class="ms-12"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
                                </div>
                            </div>
                            <!-- 网络重试次数 -->
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4 strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="network_retry_times">
                                </div>
                            </div>
                            <!-- 网络重连时间间隔 -->
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="network_retry_interval">
                                </div>
                            </div>
                            <!-- 操作异常自动重试 -->
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="op_retry_flag">
                                </div>
                            </div>
                            <!-- 操作异常重连次数 -->
                            <div class="row static-info op_retry_div strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="op_retry_times">
                                </div>
                            </div>
                            <!-- 操作异常重连时间间隔 -->
                            <div class="row static-info op_retry_div strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="op_retry_interval">
                                </div>
                            </div>
                            <!-- 任务自动重试 -->
                            <div class="row static-info strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="task_retry_flag">
                                </div>
                            </div>
                            <!-- 任务重连对象 -->
                            <div class="row static-info task_retry_div strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="task_retry_object">
                                </div>
                            </div>
                            <!-- 任务重连次数 -->
                            <div class="row static-info task_retry_div strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="task_retry_times">
                                </div>
                            </div>
                            <!-- 任务重连间隔时间 -->
                            <div class="row static-info task_retry_div strategy-group__form__item">
                                <div class="col-md-4  strategy-group__form__item__label">
                                    <?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?>
                                </div>
                                <div class="col-md-8  strategy-group__form__item__value" id="task_retry_interval">
                                </div>
                            </div>
                        </div>

                        <!---------- 过载保护 ---------->
                        <div class="row static-info">
                            <div class="advanced-conf-title-icon floatl mt2"></div>
                            <div class="floatl ">
                                <span class="ms-12"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
                            </div>
                        </div>
                        <!-- 忽略节点资源限制 -->
                        <div class="row static-info strategy-group__form__item">
                            <div class="col-md-4  strategy-group__form__item__label">
                                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
                            </div>
                            <div class="col-md-8  strategy-group__form__item__value" id="ignore_resource_limit">
                            </div>
                        </div>

                        <!---------- 重置主机名 ---------->
                        <div class="row static-info reset_host_names">
                            <div class="advanced-conf-title-icon floatl mt2"></div>
                            <div class="floatl ">
                                <span class="ms-12"><?php echo $LANG['UI_DB_RECOVERY_RECOVERY_CONFIG'] ?></span>
                            </div>
                        </div>
                        <!-- 忽略节点资源限制 -->
                        <div class="row static-info strategy-group__form__item reset_host_names">
                            <div class="col-md-4  strategy-group__form__item__label">
                                <?php echo $LANG['UI_VIRTUAL_MACHINE_RESET_HOSTNAME'] ?>
                            </div>
                            <div class="col-md-8 strategy-group__form__item__value" id="reset_host_name">
                            </div>
                        </div>

                    </div>
                </div>
            </div>


        </div>

        <div class="drawer-footer">
            <button type="button" class="btn green-haze" data-dismiss="drawer"
                    aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
        </div>
    </div>
</div>
<!-- drawer结束 -->

<!-- BEGIN SCRIPT CONTENT DRAWER -->
<div class="drawer slide " data-placement="right" tabindex="-1" role="dialog" id="scriptContentDrawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title">
                <span>
                    <i class="viconfont vicon-jiaobenguanli"></i>
                </span>
                <span style="position: relative; top: -1px" class="name"></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                    <i class="viconfont vicon-guanbi"></i>
                </span>
            </h4>
        </div>
        <div class="drawer-body">
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
            <button type="button" class="btn default" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
<!-- END SCRIPT CONTENT DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./scripts/platform/component/safe_virus_detection.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script src="./scripts/platform/recovery/platform_job.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->