<?php
include_once '../../tpl/permission.php';
$authfun = $_SESSION['authfun'];
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/swiper/css/swiper.min.css" />
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
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
<div class="row job-detail" id="obs_job_detail">
    <!-- BEGIN HALF TOP -->
    <div class="col-md-12 job-detail__halftop">
        <div class="col-md-8 job-detail__halftop__charts">
            <input id="task_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
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
                            <div id="total-progress" class="progress-bar progress-bar-success" role="progressbar"
                                aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                            </div>
                        </div>
                        <div class="taskprogress" id="progressright"></div>
                    </div>
                    <!-- 多客户端时进度条出显示的信息 -->
                    <div class="multiProgressDiv display-none">
                        <div class="multiProgressDiv__label">
                            <span>
                                <?php echo $LANG['UI_JOB_BACKUP_CLIENT'] ?>
                            </span>
                        </div>
                        <div class="agentName">--</div>
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
                    <div class="tab-content">
                        <div class="portlet-body">
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_PUBLIC_OPERATION'] ?>:
                                </div>
                                <div class="col-md-8 col-operate value">
                                    <div class="btn-group dropdown-wrapper">
                                        <button 
                                            type="button"
                                            class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle"
                                            data-toggle="dropdown" 
                                            data-hover="dropdown" 
                                            data-delay="1000"
                                            data-close-others="true"
                                        >
                                            <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> 
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" id="fsOpList">
                                            <li class="startFull"><button class="btn dropdown-menu__item"
                                                    type="button"><i
                                                        class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button>
                                            </li>
                                            <li class="startIncr"><button class="btn dropdown-menu__item"
                                                    type="button"><i
                                                        class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button>
                                            </li>
                                            <li class="startDiff"><button class="btn dropdown-menu__item"
                                                    type="button"><i
                                                        class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button>
                                            </li>
                                            <li class="stop"><button class="btn dropdown-menu__item"
                                                    type="button"><i
                                                        class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_GRAIN_OPERATE_STOP'] ?></button>
                                            </li>
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
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_DETAIL_JOB_TYPE'] ?>:
                                </div>
                                <div class="col-md-8 value" id="status">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_JOB_TASK_STAGE'] ?>:
                                </div>
                                <div class="col-md-8 value" id="task_stage">
                                </div>
                            </div>
                            <div class="row static-info">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_FILE_JOB_ACQUIRED_CAPACITY'] ?>:
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
                            <div class="row static-info flex-items-center">
                                <div class="col-md-4 name">
                                    <?php echo $LANG['UI_COPY_JOB_DETAIL_LABEL_SETTING_MORE'] ?>:
                                </div>
                                <div class="col-md-8 value">
                                    <a id="details_more" class="colorgreen" data-toggle="drawer" data-target="#obs_more_detail_drawer" href="javascript:void(0)">
                                        <?php echo $LANG['BILLING_VIEW_DETAILS'] ?>    
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
    <!-- END HALF TOP -->

    <!-- BEGIN HALF BOTTOM -->
    <div class="col-md-12 job-detail__halfbottom">
        <div class="job-detail__halfbottom__portlet">
            <div class="portlet-title">
                <ul class="nav nav-tabs" id="job_detail_bottom_nav">
                    <li class="active ">
                        <a href="#log" data-toggle="tab">
                            <i class="viconfont vicon-tasklog"></i> <?php echo $LANG['UI_JOB_RUNING_LOG'] ?> </a>
                    </li>
                    <li>
                        <a href="#files" data-toggle="tab" id="srcList"></a>
                    </li>
                    <li>
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

                    <div class="tab-pane" id="files">
                        <div class="table-toolbar">
                            <div class="table-toolbar__item me-10" id="table_toolbar_search">
                                <div class="search input-group">
                                    <input id="obs_detail_seach_ipt" class="currentSearch customSearch"
                                        style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                                        placeholder="<?php echo $LANG['UI_ONS_MANAGER_SEARCH_PLACEHOLDER'] ?>">
                                    <div class="position0" style="width:auto;height:34px">
                                        <button class="b-btn clear hide position0" id="obs_detail_clear_search"><i
                                                class="icon-close-small"></i></button>
                                    </div>
                                    <div class="search-btn positionL0">
                                        <button id="obs_detail_search_btn" class="b-btn search-btn"><i
                                                class="icon-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-toolbar__item">
                                <div class="btn-group dropdown-wrapper fsStartDiv">
                                    <button 
                                        type="button"
                                        class="btn btn-primary text-white btn-dropdown-operate dropdown-toggle"
                                        data-toggle="dropdown" 
                                        data-hover="dropdown" 
                                        data-delay="1000"
                                        data-close-others="true"
                                    >
                                        <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li class="startFullTable"><button class="btn dropdown-menu__item"
                                                type="button"><i
                                                    class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_BACKUP_FULL'] ?></button>
                                        </li>
                                        <li class="startIncrTable"><button class="btn dropdown-menu__item"
                                                type="button"><i
                                                    class="viconfont vicon-ge_increment me-4"></i><?php echo $LANG['UI_BACKUP_INCREMENT'] ?></button>
                                        </li>
                                        <li class="startDiffTable"><button class="btn dropdown-menu__item"
                                                type="button"><i
                                                    class="viconfont vicon-ge_differentia_backup me-4"></i><?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="table-container table-container-detail">
                            <table class="table table-hover table-borderless" id="filestable"></table>
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
                            <table class="table table-hover table-borderless" id="historytable"></table>
                        </div>
                    </div>

                    <!-- BEGIN PASS FILE MODAL -->
                    <div id="passFileModal" style="z-index: 100000;top: 400px;"
                        class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
                        data-backdrop="static">
                        <div class="modal-header ">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title"><i class="viconfont icon-v-xnjbf"
                                    style="color: gray;margin-right: 8px;"></i><?php echo $LANG['BILLING_VIEW_DETAILS'] ?>
                            </h4>
                        </div>
                        <div class="modal-body">
                            <div class="portlet-body">
                                <div class="btn-group">
                                    <button type="button" id="downloadTxt" class="btn btn-sm green-haze">
                                        <i class="fa fa-download"></i><?php echo $LANG['UI_OBS_DOWNLOAD_SKIP_OBJECT'] ?>
                                    </button>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3 pl0"
                                        style="text-align: left;"><?php echo $LANG['UI_OBS_KIP_OBJECT_DETAIL'] ?>:</label>
                                    <div class="col-md-12 p-0 obs-passfile-detail-wrapper">
                                        <table id="pass_files_details_table"></table>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-3 pl0"
                                        style="text-align: left;"><?php echo $LANG['UI_FILE_SKIP_REASON_DETAILS'] ?>:</label>
                                    <div class="col-md-12 p-0 obs-passfile-detail-wrapper">
                                        <table id="pass_files_reason_table"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" data-dismiss="modal"
                                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?></button>
                            <!-- <button type="button"class="btn btn-primary" id="mountsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button> -->
                        </div>
                    </div>
                    <!-- END PASS FILE MODAL -->
                </div>
            </div>
        </div>
    </div>
    <!-- END HALF BOTTOM -->
</div>
<!-- END PAGE CONTENT-->
<!-- BEGIN TASK DETAIL DRAWER -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" style="width: 600px"
    aria-labelledby="obs_detail_drawer_title" aria-hidden="true" id="obs_more_detail_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <div class="drawer-title" id="obs_detail_drawer_title"
                style="display:flex;justify-content:space-between;align-items:center">
                <div class="drawer-title-left">
                    <i class="viconfont vicon-tenant-detail mr10"></i>
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
                <!-- BEGIN TIME STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['WEB_COMMON_TIME_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 创建/修改时间 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_CREATE_TIME'] ?></div>
                        <div id="createTime" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 下次开始时间 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_NEXT_START_TIME'] ?></div>
                        <div id="nextTime" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 备份类型 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_TYPE'] ?></div>
                        <div id="timeStrategyBackupType" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 完全备份 -->
                    <div class="strategy-group__form__item static-info-backup-full">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_FULL'] ?></div>
                        <div id="fulldes" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 增量备份 -->
                    <div class="strategy-group__form__item static-info-backup-increment">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_INCREMENT'] ?></div>
                        <div id="incdes" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 差异备份 -->
                    <div class="strategy-group__form__item static-info-backup-diff">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_DIFFERENCE'] ?></div>
                        <div id="diffdes" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 永久增量 -->
                    <div class="strategy-group__form__item static-info-backup-permanent">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_PERMANENT_INCREMENT'] ?></div>
                        <div id="permanent_incr" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END TIME STRATEGY -->

                <!-- BEGIN SPEED LIMIT STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 限速策略 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></div>
                        <div id="speed_limit_backup" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务等级 -->
                    <div class="strategy-group__form__item task-priority-form-item-backup">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_TASK_PRIORITY'] ?></div>
                        <div id="task_priority_backup" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END SPEED LIMIT STRATEGY -->

                <!-- BEGIN STORAGE STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_STORAGE_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 存储设备 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_STORAGE_DEV'] ?></div>
                        <div id="storage_device" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 备份节点 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_BACKUP_NODE'] ?></div>
                        <div id="backup_node" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 压缩存储 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_COMPRESS_TRANSFER'] ?></div>
                        <div id="compress_storage" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 压缩等级 -->
                    <div class="strategy-group__form__item compress-method-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_JOB_COMPRESS_PRIORITY']; ?></div>
                        <div id="compress_method" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 数据加密 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_DATA_ENCRYPT'] ?></div>
                        <div id="encrypt_storage" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 存储加密算法 -->
                    <div class="strategy-group__form__item display-none storage-encrypt-method-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
                        <div id="encrypt_storage_method" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 自动生成密码 -->
                    <div class="strategy-group__form__item display-none auto-password-form-item mb-20">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_PASSOWRD_AUTO'] ?></div>
                        <div id="storage_auto_password" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END STORAGE STRATEGY -->

                <!-- BEGIN RESERVE STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 保留策略 -->
                    <div class="strategy-group__form__item reserve-strategy-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_RESERVE_STRATEGY'] ?></div>
                        <div id="reserve_strategy" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END RESERVE STRATEGY -->
            </div>
            <!-- END COMMON STRATEGY GROUP -->

            <!-- BEGIN TRANSMIT STRATEGY GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-a-chuanshu me-4"></i><span
                        class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 传输网络 -->
                    <div class="strategy-group__form__item backup-transmit-network-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?>
                        </div>
                        <div id="backup_transmit_network" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                    <!-- 传输代理 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_IS_OPEN_AGENCY'] ?></div>
                        <div id="backup_appliance_agency_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 选择的传输代理 -->
                    <div class="strategy-group__form__item backup-appliance-agency-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></div>
                        <div id="backup_appliance_agency" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 加密传输 -->
                    <div class="strategy-group__form__item backup-encrypt-transmit-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></div>
                        <div id="backup_encrypt_transmit" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 传输加密算法 -->
                    <div class="strategy-group__form__item backup-transfer-encrypt-method-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
                        <div id="backup_transmit_encrypt_method" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                    <!-- 传输线程 -->
                    <div class="strategy-group__form__item backup-transmit-thread-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?></div>
                        <div id="backup_transimit_thread_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 扫描线程 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_FILE_BAK_SCAN_THREAD'] ?></div>
                        <div id="backup_scan_thread_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 扫描文件速度 -->
                    <div class="strategy-group__form__item backup-scan-file-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_OBS_SCAN_OBJECT_SPEED'] ?></div>
                        <div id="backup_scan_file_speed" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 对象权限备份 -->
                    <div class="strategy-group__form__item obj-permission-backup-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_OBS_PERMISSION_BACKUP'] ?>
                        </div>
                        <div id="obj_permission_backup" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TRANSMIT STRATEGY GROUP -->

            <!-- BEGIN SAFE STRATEGY GROUP -->
            <div class="config-detail-safe-strategy-wrapper">
                <div class="config-detail-wrap__group strategy-group backup-safety-strategy-group">
                    <div class="strategy-group__header">
                        <i class="viconfont vicon-anquancelve me-4"></i>
                        <span class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?></span>
                    </div>
                    <div class="strategy-group__form">
                        <div class="worm-config-wrapper">
                            <!-- WORM防护 -->
                            <div class="strategy-group__form__item backup-worm-protect-form-item">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT'] ?>
                                </div>
                                <div id="backup_worm_protect_check" class="strategy-group__form__item__value col-md-8">
                                </div>
                            </div>
                            <!-- WORM保护期限 -->
                            <div class="strategy-group__form__item backup-worm-protect-term-form-item display-none">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_SAFE_STRATEGY_WORM_PROTECT_PERIOD'] ?>
                                </div>
                                <div id="backup_worm_protect_term" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                        </div>
                        <div class="integrity-check-wrapper">
                            <!-- 完整性校验 -->
                            <div class="strategy-group__form__item backup-integrity-check-form-item">
                                <div class="strategy-group__form__item__label col-md-4">
                                    <?php echo $LANG['UI_SAFE_STRATEGY_INTEGRITY_CHECK'] ?>
                                </div>
                                <div id="backup_integrity_check" class="strategy-group__form__item__value col-md-8"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>              
            <!-- END SAFE STRATEGY GROUP -->

            <!-- BEGIN ADVANCED CONFIG GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-a-chuanshu me-4"></i><span
                        class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 通配符备份 -->
                    <div class="strategy-group__form__item wildcardmodeDiv">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_FILE_WILDCARD_WAYS'] ?></div>
                        <div id="wildcardmode" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>

                <!-- BEGIN ABNORMAL HANDLE -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_EXCEPTION_HANDLE'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 跳过对象告警智能判断 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_OBD_SKIP_OBS_ALARM_INTELLIGENT_JUDGMENT'] ?></div>
                        <div id="pass_obj_alarm" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过对象告警个数 -->
                    <div class="strategy-group__form__item pass-obj-alarm-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_OBS_SKIP_THE_NUMBER_OF_OBJECT_ALARMS'] ?></div>
                        <div id="pass_obj_alarm_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 跳过对象告警比例 -->
                    <div class="strategy-group__form__item pass-obj-alarm-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_OBS_SKIP_THE_OBJECT_ALARM_RATIO'] ?></div>
                        <div id="pass_obj_alarm_percent" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END ABNORMAL HANDLE -->

                <!-- BEGIN RETRY STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 网络重连次数 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?></div>
                        <div id="backup_network_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 网络重连间隔时间 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?></div>
                        <div id="backup_network_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 操作异常自动重试 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?></div>
                        <div id="backup_op_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 操作异常重连次数 -->
                    <div class="strategy-group__form__item backup-op-retry-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?></div>
                        <div id="backup_op_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 操作异常重连间隔时间 -->
                    <div class="strategy-group__form__item backup-op-retry-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?></div>
                        <div id="backup_op_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务自动重试 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?></div>
                        <div id="backup_task_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务重连对象 -->
                    <div class="strategy-group__form__item backup-task-retry-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?></div>
                        <div id="backup_task_retry_object" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务重连次数 -->
                    <div class="strategy-group__form__item backup-task-retry-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?></div>
                        <div id="backup_task_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务重连间隔时间 -->
                    <div class="strategy-group__form__item backup-task-retry-form-item mb-16">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?></div>
                        <div id="backup_task_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END RETRY STRATEGY -->

                <!-- BEIGIN OVERLOAD PROTECTION -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 是否忽略节点资源限制 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?></div>
                        <div id="backup_ignore_resource_limit" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END OVERLOAD PROTECTION -->
            </div>
            <!-- END ADVANCED CONFIG GROUP -->
        </div>
        <!-- END BACKUP TASK DETAIL DRAWER BODY -->

        <!-- BEGIN RECOVER TASK DETAIL DRAWER BODY -->
        <div class="drawer-body config-detail-wrap config-detail-drawer-recover">
            <!-- BEGIN COMMON STRATEGY GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-celve1 me-4"></i><span
                        class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_COMMON_STRATEGY'] ?></span>
                </div>
                <!-- BEIGIN TIME STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['WEB_COMMON_TIME_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_RECOVERY_TYPE'] ?></div>
                        <div id="recover_type" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END TIME STRATGEY -->

                <!-- BEGIN SPEED LIMIT STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span
                        class="strategy-group__title__text"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 限速策略 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT'] ?></div>
                        <div id="speed_limit_recover" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END SPEED LIMIT STRATEGY -->
            </div>
            <!-- END COMMON STRATEGY GROUP -->

            <!-- BEGIN TRANSMIT STRATEGY GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-a-chuanshu me-4"></i><span
                        class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_TRANSFER_STRATEGY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 传输网络 -->
                    <div class="strategy-group__form__item recover-transmit-network-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_NODE_TRANSFER_NETWORK'] ?>
                        </div>
                        <div id="recover_transmit_network" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                    <!-- 传输线程 -->
                    <div class="strategy-group__form__item recover-transmit-thread-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_THREAD_NUM'] ?></div>
                        <div id="recover_transimit_thread_num" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 传输代理 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_IS_OPEN_AGENCY'] ?></div>
                        <div id="recover_appliance_agency_flag" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                    <!-- 选择的传输代理 -->
                    <div class="strategy-group__form__item recover-appliance-agency-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_PLATFORM_APPLIANCE'] ?></div>
                        <div id="recover_appliance_agency" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 加密传输 -->
                    <div class="strategy-group__form__item recover-encrypt-transmit-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_ENCRYPTION_TRANSFER'] ?></div>
                        <div id="recover_encrypt_transmit" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 传输加密算法 -->
                    <div class="strategy-group__form__item recover-transfer-encrypt-method-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_BACKUP_STORAGE_ENCRYPT_METHOD'] ?></div>
                        <div id="recover_transmit_encrypt_method" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TRANSMIT STRATEGY GROUP -->

            <!-- BEGIN SAFETY STRATEGY GROUP -->
            <div class="config-detail-wrap__group strategy-group recover-safety-strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-anquancelve me-4"></i><span
                        class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_SAFE_STRATEGY'] ?></span>
                </div>

                <!-- BEIGIN INTEGRITY CHECK -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_CBR_SYNC_INTEGRALITY_CHECK'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 完整性检验异常处理 -->
                    <div class="strategy-group__form__item integrity-check-recover-form-item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_PLATFORM_INTERGRITY_VERIFICACTION'] ?></div>
                        <div id="integrity_check_abnormal_handle" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                </div>
                <!-- END INTEGRITY CHECK -->
            </div>
            <!-- END SAFETY STRATEGY GROUP -->

            <!-- BEGIN ADVANCED CONFIG GROUP -->
            <div class="config-detail-wrap__group strategy-group">
                <div class="strategy-group__header">
                    <i class="viconfont vicon-gaojipeizhi me-4"></i><span
                        class="strategy-group__header__text"><?php echo $LANG['UI_BACKUP_HIGH_CONFIG'] ?></span>
                </div>
                
                <!-- BEGIN ABNORMAL HANDLE -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_EXCEPTION_HANDLE'] ?></span>
                </div>
                <div class="strategy-group__form mb-20">
                    <!-- 去除前缀 -->
                    <div class="strategy-group__form__item remove-prefix-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_OBS_REMOVE_PREFIX'] ?></div>
                        <div id="remove_prefix" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                    <!-- 同名对象处理 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_RECOVERY_OBS_PROCESS_SAME_OBS'] ?></div>
                        <div id="same_obj_handle_type" class="strategy-group__form__item__value col-md-8">
                        </div>
                    </div>
                    <!-- 对象权限恢复 -->
                    <div class="strategy-group__form__item obj-permission-recover-form-item display-none">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_OBS_RECOVERY_PERMISSION'] ?></div>
                        <div id="obj_permission_recover" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END ABNORMAL HANDLE -->

                <!-- BEGIN RETRY STRATEGY -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_RETRY'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 网络重连次数 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_TIMES'] ?></div>
                        <div id="recover_network_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 网络重连间隔时间 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_NETWORK_RETRY_INTERVAL'] ?></div>
                        <div id="recover_network_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 操作异常自动重试 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_AUTO_RETRY'] ?></div>
                        <div id="recover_op_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 操作异常重连次数 -->
                    <div class="strategy-group__form__item recover-op-retry-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_RETRY_TIMES'] ?></div>
                        <div id="recover_op_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 操作异常重连间隔时间 -->
                    <div class="strategy-group__form__item recover-op-retry-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_OP_RETRY_INTERVAL'] ?></div>
                        <div id="recover_op_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务自动重试 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_AUTO_RETRY'] ?></div>
                        <div id="recover_task_retry_flag" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务重连对象 -->
                    <div class="strategy-group__form__item recover-task-retry-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_OBJECT'] ?></div>
                        <div id="recover_task_retry_object" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务重连次数 -->
                    <div class="strategy-group__form__item recover-task-retry-form-item">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_TIMES'] ?></div>
                        <div id="recover_task_retry_times" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                    <!-- 任务重连间隔时间 -->
                    <div class="strategy-group__form__item recover-task-retry-form-item mb-16">
                        <div class="strategy-group__form__item__label col-md-4"><?php echo $LANG['UI_STRATEGY_TASK_RETRY_INTERVAL'] ?></div>
                        <div id="recover_task_retry_interval" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END RETRY STRATEGY -->

                <!-- BEIGIN OVERLOAD PROTECTION -->
                <div class="strategy-group__title">
                    <span class="decoration me-4"></span><span class="strategy-group__title__text"><?php echo $LANG['UI_STRATEGY_OVER_LOAD_LABEL'] ?></span>
                </div>
                <div class="strategy-group__form">
                    <!-- 是否忽略节点资源限制 -->
                    <div class="strategy-group__form__item">
                        <div class="strategy-group__form__item__label col-md-4">
                            <?php echo $LANG['UI_NODE_RESOURCE_LIMIT_IGNORE'] ?>
                        </div>
                        <div id="recover_ignore_resource_limit" class="strategy-group__form__item__value col-md-8"></div>
                    </div>
                </div>
                <!-- END OVERLOAD PROTECTION -->
            </div>
            <!-- END ADVANCED CONFIG GROUP -->
        </div>
        <!-- END RECOVER TASK DETAIL DRAWER BODY -->

        <div class="drawer-footer">
            <button type="button" class="btn green-haze" data-dismiss="drawer"
                aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
        </div>
    </div>
</div>
<!-- END TASK DETAIL DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/echarts/V5.4.3/echarts.common.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js">
</script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/swiper/js/swiper.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jsencrypt/jsencrypt.min.js"></script>
<script type="text/javascript" src="./scripts/platform/component/speed_chart.js"></script>
<script type="text/javascript" src="./scripts/platform/component/job_running_log.js"></script>
<script type="text/javascript" src="./scripts/s3/obsjobdetail.js"></script>
<!-- END PAGE LEVEL PLUGINS -->