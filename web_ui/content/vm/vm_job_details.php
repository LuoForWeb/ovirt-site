<?php include_once '../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css"/>
<!-- END PAGE LEVEL STYLES -->
<style type="text/css">
    .colorgreen {color: #0fbf98}
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



</style>
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
            <input id="pageUri" value="<?php echo $_SERVER['REQUEST_URI']; ?>" class="display-none"></input>
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
                    <!--BEGIN TABS-->
                    <div class="portlet-title init_tab_title">
                        <i class="viconfont vicon-gaiyao1"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?>
                    </div>
                    <div class="tab-content">
                        <div class="portlet-body">
                            <div class="row static-info taskOperateDiv <?php if (!in_array("global_write", $_SESSION['permission']) && in_array("global_observer", $_SESSION['permission'])) {
                                echo "display-none";
                            } ?> ">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_EMERGENCY_PLAN_OPERATE'] ?>:
                                </div>
                                <div class="col-md-8 col-operate value">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary text-white btn-dropdown-operate  dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                            <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu min-width100" role="menu">
                                            <div class="display-none" id="taskOperateDivBackup">
                                                <li id="taskStartFull"><a href="javascript:;"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></a></li>
                                                <li id="taskStartIncr"><a href="javascript:;"><i class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></a></li>
                                                <li id="taskStartDiff"><a href="javascript:;"><i class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></a></li>
                                                <li id="stoptask"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?></a></li>
                                            </div>
                                            <div class="display-none" id="taskOperateDivRecovery">
                                                <li id="startJob"><a href="javascript:;"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['WEB_JOB_START'] ?></a></li>
                                                <li id="stopJob"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?></a></li>
                                                <li id="delJob"><a href="javascript:;"><i class="viconfont vicon-ge_delete me-4"></i><?php echo $LANG['UI_PUBLIC_DELETE'] ?></a></li>
                                            </div>
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
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
                                </div>
                                <div class="col-md-8 value" id="taskStage">
                                </div>
                            </div>
                            <!-- 更多详请 -->
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PUBLIC_MORE_CONFIGURATION'] ?>:
                                </div>
                                <div class="col-md-8 value">
                                    <a id="details_more" class="green-haze colorgreen" data-toggle="drawer"
                                       data-target="#drawer-1" href="javascript:void(0)">
                                        <?php echo $LANG['UI_PUBLIC_MORE_DETAIL'] ?>
                                    </a>
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
                <ul class="nav nav-tabs" id="job_navs">
                    <li id="logli" class="active ">
                        <a href="#log" data-toggle="tab">
                            <i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
                    </li>
                    <li id="vmli" style="display: none">
                        <a href="#vms" data-toggle="tab">
                            <i class="viconfont vicon-pt_report_vm_report "></i> <span><?php echo $LANG['UI_VCENTER_MACHINE_LIST'] ?></span> </a>
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
                            <div class="row">
                                <div class="col-md-12" style="display: inline-flex">
                                    <div class="search input-group mr12">
                                        <input type="search" maxlength="128" id="searchInput" class="searchinput customSearch" autocomplete="off"
                                               style="padding-right:32px;min-width: 220px;" maxlength="64" type="text"
                                               placeholder="<?php echo $LANG['UI_TASK_VM_SEARCH_TIPS'] ?>">
                                        <div class="position0" style="width:auto;height:34px">
                                            <button class="b-btn clear hide position0" id="clearSearchBtn">
                                                <i class="icon-close-small"></i></button>
                                        </div>
                                        <div class="search-btn positionL0" style="width:auto;height:34px;">
                                            <button class="b-btn search-btn" id="searchbtn"><i class="icon-search"></i></button>
                                        </div>
                                    </div>
                                    <div class="btn-group dropdown-wrapper <?php if (in_array("global_write", $_SESSION['permission']) || !in_array("global_observer", $_SESSION['permission'])) {
                                        echo "vmStartDiv";
                                    } ?> display-none">
                                        <button type="button" class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true" style="height: 34px">
                                            <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" id="" style="margin-top: 5px;z-index: 9999">
                                            <li id="startFull"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button></li>
                                            <li id="startIncr"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button></li>
                                            <li id="startDiff"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button></li>
                                            <li id="deleteVm"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-a-Deleteshanchu me-4"></i><?php echo $LANG['UI_BACKUP_DELETE_FROM_JOB'] ?></button></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table id="vm-table"></table>

                            <div class="alert alert-block alert-info fade in display-hide" id="vmbackuptips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class="alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                    <li>
                                        <?php echo $LANG['UI_BACKUP_SELECT_VM_BACKUP_TIPS'] ?>
                                    </li>
                                </ul>
                            </div>
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
            <!-- 过滤 -->
            <div class="config-detail-wrap__group strategy-group filter-div">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-ge_filter me-4"></i><span class="strategy-group__header__text"><?php echo $LANG['UI_JOB_FILTER_RULES'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 前缀或关键词 -->
                    <div class="strategy-group__form__item align-items-baseline backup_style globalSelectDiv">
                        <div class="strategy-group__form__item__label col-md-4 select_conditions">

                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="select_condition">
                        </div>
                    </div>
                    <!-- 自动加入备份 -->
                    <div class="strategy-group__form__item align-items-baseline backup_style">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_AUTO_JOIN_BACKUP'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="auto_join">
                        </div>
                    </div>
                </div>
            </div>
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
                            <?php echo $LANG['UI_BACKUP_TYPE'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="timeStrategyBackupType">
                        </div>
                    </div>
                    <div class="strategy-group__form__item align-items-baseline backup_style backup_time_strategy_div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_FULL'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="fulldes">
                        </div>
                    </div>
                    <div class="strategy-group__form__item align-items-baseline backup_style backup_time_strategy_div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_INCREMENT'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="incdes">
                        </div>
                    </div>
                    <div class="strategy-group__form__item align-items-baseline backup_style backup_time_strategy_div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="diffdes">
                        </div>
                    </div>
                    <div class="strategy-group__form__item align-items-baseline backup_style permanent-increment-div backup_time_strategy_div">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_PERMANENT_INCREMENT'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="pincrdes">
                        </div>
                    </div>
                    <!-- 恢复任务时间策略 -->
                    <div class="strategy-group__form__item align-items-baseline recoveryDiv">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_STRATEGY_TIME'] ?>
                        </div>
                        <div class="strategy-group__form__item__value col-md-8" id="recoveryTimeDes">
                        </div>
                    </div>
                </div>
                <!-- END TIME STRATEGY -->

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
                        <div id="speedlimit" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务等级 -->
                    <div class="strategy-group__form__item taskPriorityDiv">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_TASK_PRIORITY'] ?></div>
                        <div id="taskPriority" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END SPEED LIMIT STRATEGY -->

                <div class="backup_style">
                    <!-- BEGIN STORAGE STRATEGY -->
                    <div class="strategy-group__title storagemodelDiv">
                        <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
                    </div>
                    <div class="strategy-group__form storagemodelDiv">
                        <!-- 存储设备 -->
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_BACKUP_TARGET_STORAGE'] ?></div>
                            <div id="storageinfo" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 备份节点 -->
                        <div class="strategy-group__form__item align-items-baseline">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_BACKUP_TARGET_NODE'] ?></div>
                            <div id="nodeinfo" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 重复数据删除 -->
                        <div class="strategy-group__form__item deduplicationdiv">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_BACKUP_DEDUPLICATION'] ?></div>
                            <div id="deduplication" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 压缩存储 -->
                        <div class="strategy-group__form__item compresseddiv">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?></div>
                            <div id="compressed" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 数据加密 -->
                        <div class="strategy-group__form__item encryptStoragediv">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?></div>
                            <div id="encryptStorage" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 自动生成密码 -->
                        <div class="strategy-group__form__item passwordAutodiv mb-20" style="display: none;">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?></div>
                            <div id="passwordAuto" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                    <!-- END STORAGE STRATEGY -->

                    <!-- BEGIN RESERVE STRATEGY -->
                    <div class="reserve-div reservedDiv">
                        <div class="strategy-group__title">
                            <span class="decoration me-4"></span><span
                                    class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 保留策略 -->
                            <div class="strategy-group__form__item reserve-strategy-form-item">
                                <div class="strategy-group__form__item__label col-md-4 name">
                                    <?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></div>
                                <div id="reservedStrategy" class="strategy-group__form__item__value col-md-8 value"></div>
                            </div>
                        </div>
                    </div>
                    <!-- END RESERVE STRATEGY -->
                </div>
            </div>
            <!-- BEGIN TRANSMIT STRATEGY GROUP -->
            <div class="config-detail-wrap__group strategy-group threadDiv">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-a-chuanshu me-4"></i><span
                            class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
                </div>
                <?php include_once '../platform/component/vm_job_transfer.php'; ?>
            </div>
            <!-- END TRANSMIT STRATEGY GROUP -->
            <!-- BEGIN SAFE STRATEGY GROUP -->
            <div class="config-detail-wrap__group strategy-group safemodeDiv">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-anquancelve me-4"></i><span
                            class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form backup-safe-strategy">
                    <!-- WORM防护 -->
                    <div class="strategy-group__form__item backup-safe-worm">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT'] ?></div>
                        <div id="backup_worm_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 病毒检测 -->
                    <div class="strategy-group__form__item backup-safe-virus">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_SAFE_STRATEGY_VIRUS_CHECK'] ?></div>
                        <div id="virus_check_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 完整性校验 -->
                    <div class="strategy-group__form__item backup-safe-integrity">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK'] ?></div>
                        <div id="integrity_check_flag" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                </div>
                <div class="strategy-group__form recovery-safe-strategy">
                    <!-- 备份点病毒扫描策略 -->
                    <div class="strategy-group__form__item recovery-safe-virus">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_PLATFORM_BACKUP_TIMEPOINT_VIRUS_SCAN_STRATEGY'] ?></div>
                        <div id="virus_scan_way" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 完整性校验异常处理 -->
                    <div class="strategy-group__form__item recovery-safe-integrity">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_PLATFORM_INTERGRITY_VERIFICACTION'] ?></div>
                        <div id="integrity_policy" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                </div>
            </div>
            <!-- END SAFE STRATEGY GROUP -->
            <!-- BEGIN ADVANCED CONFIG GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-gaojipeizhi me-4"></i><span
                            class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
                </div>
                <div class="backup_style">
                    <!--BEGIN SNAPSHOT GROUP -->
                    <div class="snapshotDiv">
                        <div class="strategy-group__title">
                            <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_FILE_SHOOTSNAP'] ?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 快照模式 -->
                            <div class="strategy-group__form__item">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_SNAPSHOT_MODE'] ?></div>
                                <div id="serialSnapshot" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 存储快照 -->
                            <div class="strategy-group__form__item storageSnapshotDiv" style="display: none">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_LUN_STORAGE_SNAPSHOT'] ?></div>
                                <div id="storageSnapshot" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 静默快照 -->
                            <div class="strategy-group__form__item silentSnapshotdiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_DB_SILENT_SNAPSHOT'] ?></div>
                                <div id="silentSnapshot" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 提前创建快照 -->
                            <div class="strategy-group__form__item presnapshotDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_PRE_CREATE_SNAPSHOT'] ?></div>
                                <div id="presnapshot" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 快照删除速度 -->
                            <div class="strategy-group__form__item snapshotDelSpeedDiv display-none">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_SNAPSHOT_DELETE_SPEED'] ?></div>
                                <div id="snapshotDelSpeed" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                        </div>
                    </div>
                    <!--END SNAPSHOT GROUP -->
                    <!--BEGIN INCREMENT GROUP -->
                    <div class="incrementDiv">
                        <div class="strategy-group__title">
                            <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['WEB_PLATFORM_DES_INCRIMENT'] ?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 增量模式 -->
                            <div class="strategy-group__form__item incLeveldiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_INCMODE'] ?></div>
                                <div id="incMode" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 重置CBT -->
                            <div class="strategy-group__form__item resetCbtDiv display-none">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_RESET_CBT'] ?></div>
                                <div id="resetcbt" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 高速磁盘CBT -->
                            <div class="strategy-group__form__item highspeedDiskCbtDiv display-none">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_HIGHSPEED_DISK_CBT'] ?></div>
                                <div id="highspeedDiskCbt" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                        </div>
                    </div>
                    <!--END INCREMENT GROUP -->
                    <!--BEGIN VCBT GROUP -->
                    <div class="vcbtDiv">
                        <div class="strategy-group__title">
                            <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_DB_BACKUP_ADVANCED_TAB_VALIDATE_DATA'] ?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 深度有效数据提取 -->
                            <div class="strategy-group__form__item parsefsDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_VCBT'] ?></div>
                                <div id="parseFs" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 排除交换文件块 -->
                            <div class="strategy-group__form__item parsefsDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_SWAP'] ?></div>
                                <div id="noSwapFile" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 排除分区间隙 -->
                            <div class="strategy-group__form__item parsefsDiv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_GAP'] ?></div>
                                <div id="noPartitionGap" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                        </div>
                    </div>
                    <!--END VCBT GROUP -->
                    <!--BEGIN STORAGE GROUP -->
                    <div class="storageDiv">
                        <div class="strategy-group__title">
                            <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_PALTFORM_STORAGE'] ?></span>
                        </div>
                        <div class="strategy-group__form">
                            <!-- 数据块大小 -->
                            <div class="strategy-group__form__item blocksizediv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_BLOCK_SIZE'] ?></div>
                                <div id="blocksize" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 数据文件大小 -->
                            <div class="strategy-group__form__item datacontainersizediv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_DATA_CONTAINER_SIZE'] ?></div>
                                <div id="dataContainerSize" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                            <!-- 合并冗余数据比例 -->
                            <div class="strategy-group__form__item mergemodediv">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_BACKUP_MERGE_REDUNDANT_DATA_PROPORTION'] ?></div>
                                <div id="redundantDataProportion" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                        </div>
                    </div>
                    <!--BEGIN STORAGE GROUP -->
                </div>

                <!-- BEGIN RETRY STRATEGY -->
                <div class="">
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 网络重连次数 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?></div>
                            <div id="network_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 网络重连间隔时间 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?></div>
                            <div id="network_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常自动重试 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?></div>
                            <div id="op_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常重连次数 -->
                        <div class="strategy-group__form__item op-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?></div>
                            <div id="op_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 操作异常重连间隔时间 -->
                        <div class="strategy-group__form__item op-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?></div>
                            <div id="op_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务自动重试 -->
                        <div class="strategy-group__form__item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?></div>
                            <div id="task_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务重连对象 -->
                        <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?></div>
                            <div id="task_retry_object" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务重连次数 -->
                        <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?></div>
                            <div id="task_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                        <!-- 任务重连间隔时间 -->
                        <div class="strategy-group__form__item task-retry-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?></div>
                            <div id="task_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
                <!-- END RETRY STRATEGY -->
                <!-- BEGIN OVERLOAD GROUP -->
                <div class="">
                    <div class="strategy-group__title">
                        <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <!-- 忽略节点资源限制 -->
                        <div class="strategy-group__form__item passfilealarm-form-item">
                            <div class="strategy-group__form__item__label col-md-4">
                                <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></div>
                            <div id="ignore_resource_limit" class="strategy-group__form__item__value col-md-8"></div>
                        </div>
                    </div>
                </div>
                <!-- END OVERLOAD GROUP -->
            </div>
            <!-- END ADVANCED CONFIG GROUP -->
        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        </div>
    </div>
</div>
<!-- drawer结束 -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./assets/global/plugins/echarts/V5.6.0/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./assets/global/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_virus_detection.js"></script>
<script type="text/javascript" src="./scripts/platform/component/safe_complete.js"></script>
<script type="text/javascript" src="./scripts/platform/component/speed_chart.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/vm/vm_job_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->