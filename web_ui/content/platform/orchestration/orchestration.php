<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-drawer/css/bootstrap-drawer.min.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href="./content/platform/jobs/jobs.php?tab=2">
            <?php echo $LANG['UI_JOB_TASK_ORCHESTRATION'] ?>
        </a>
    </li>
    <span>></span>
    <li class="current"><?php if ($_GET['uuid']) {
                            echo $LANG['UI_JOB_TASK_ORCHESTRATION_MODIFY'];
                        } else {
                            echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD'];
                        } ?></li>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN VALIDATION STATES-->
        <span class="display-none" id="servertime"></span>
        <input id="plan_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
        <div class="portlet box blue-hoki" id="orchestrationContent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-tenant-detail"></i> <?php
                                                                                    if ($_GET['uuid']) {
                                                                                        echo $LANG['UI_JOB_TASK_ORCHESTRATION_MODIFY'];
                                                                                    } else {
                                                                                        echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD'];
                                                                                    } ?>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form action="#" id="setOrchestrationForm" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group">
                            <div class="alert alert-block alert-info fade in" id="orchestrationTips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <ul class="alert-ul">
                                    <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                    <li><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_STEP_FIRST'] ?></li>
                                </ul>
                            </div>
                        </div>
                        <!-- 名称 -->
                        <div class="form-group form-group-name">
                            <label class="control-label col-md-1">
                                <?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_NAME'] ?>
                            </label>
                            <div class="col-md-3" style="padding-right: 12px;">
                                <div class="input-icon input-icon-name right">
                                    <input type="text" maxlength="128" class="form-control" id="planName" />
                                </div>
                            </div>
                        </div>
                        <!-- 时间策略 -->
                        <div class="form-group">
                            <label class="control-label col-md-1"><?php echo $LANG['UI_STRATEGY_TIME'] ?></label>
                            <div class="col-md-3" style="padding-right: 12px;">
                                <select class="bs-select form-control" id="timeSelect">
                                    <option value="manual"><?php echo $LANG['UI_BACKUP_AS_MANUAL'] ?></option>
                                    <option value="custom"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_STRATEGY_CUSTOM'] ?></option>
                                    <option value="1"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_STRATEGY_SELECT'] ?></option>
                                </select>
                            </div>
                            <div class="label-tips">
                                <a class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-html="true" data-content="
                                <?php
                                echo $LANG['UI_JOB_TASK_ORCHESTRATION_STRATEGY_MANUAL_TIPS'] . "<br><br>" .
                                    $LANG['UI_JOB_TASK_ORCHESTRATION_STRATEGY_CUSTOM_TIPS'] . "<br><br>" .
                                    $LANG['UI_JOB_TASK_ORCHESTRATION_STRATEGY_SELECT_TIPS']; ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>

                        <div class="form-group strategy-div display-none">
                            <label class="control-label col-md-1"><?php echo $LANG['UI_BACKUP_SELECT_STRATEGY'] ?></label>
                            <div class="col-md-3">
                                <select class="bs-select form-control" id="strategySelect">
                                </select>
                            </div>
                        </div>
                        <div class="form-group strategy-time display-none">
                            <div class="col-md-1"></div>
                            <div class="panel-group accordion col-md-8" id="backupTimestrategy"></div>
                        </div>
                        <!-- 任务阶段 -->
                        <div class="form-group">
                            <div class="control-label col-md-1 pl0_en"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_TASK_SECTION'] ?></div>
                            <div class="col-md-10 section-wrapper" style="width: 88%">
                                <div class="section-card" id="sectionCard1">
                                    <div class="plan-section">
                                        <div class="form-group section-header">
                                            <div class="header-add col-md-12">
                                                <div class="card-button col-md-10">
                                                    <button type="button" id="addTaskBtn" class="btn green-haze btn-add" data-index="1"><i class="viconfont vicon-ge_add_task mr5"></i><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD_JOBS'] ?></button>
                                                </div>
                                                <div class="card-index col-md-2"><span>01</span></div>
                                            </div>
                                        </div>
                                        <div class="section-event">
                                            <div class="form-group" style="margin-bottom: 8px;margin-top: 8px;">
                                                <div class="form-group-content col-md-12">
                                                    <div style="display: flex;align-items: center;width: 100%;">
                                                        <label class="event-label"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_SECTION_REMARK'] ?></label>
                                                        <div class="input-group" style="flex:1"><input type="text" class="form-control plan-remarks" maxlength="64"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group section-body-fixed">
                                            <div class="card-items-list" id="itemList1">
                                                <div class="empty_tips-div"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD_TASK_TIPS'] ?>...</div>
                                            </div>
                                        </div>
                                        <div class="task_total-div" id="totalWarn1"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_TASK_NUMBERS'] ?>: <span class="total">0</span><span class="warn-tips display-none"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_WARN_TIPS'] ?></span>
                                    </div>
                                    </div>
                                </div>
                                <div class="next-vector add-section-before"></div>
                                <!-- 添加任务阶段 -->
                                <div class="section-card section-add-wrapper">
                                    <div id="addSection" class="add_section-card">
                                        <div class="add_button-icon">+</div>
                                        <div class="add_button-text"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD_SECTION'] ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-1"></div>
                            <div class="col-md-6">
                                <button type="button" id="cancelBtn" class="btn default btn-cancle"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                <button type="button" id="addSubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
        <!-- END VALIDATION STATES-->
        <!-- 添加任务 -->
        <div class="drawer slide" id="addTaskDrawer" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <h3 class="drawer-title" id="drawer-1-title"><i class="viconfont vicon-ge_add_task mr5"></i><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD_JOBS'] ?></h3>
                    <div class="drawer-close" data-dismiss="drawer" aria-label="Close"><i class="viconfont vicon-guanbi"></i></div>
                </div>
                <div class="drawer-body">
                    <div class="form-horizontal task-form">
                        <div class="form-body">
                            <!-- 任务类型 -->
                            <div class="form-group">
                                <label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_TASK_TYPE'] ?></label>
                                <div class="col-md-4">
                                    <select class="form-control" id="taskType">
                                        <option value="1"><?php echo $LANG['UI_COPY_SOURCE_BACKUP_TASK'] ?></option>
                                        <option value="2"><?php echo $LANG['WEB_OS_RECOVERY_TASK'] ?></option>
                                        <option value="3"><?php echo $LANG['UI_PLATFORM_COPY_JOB'] ?></option>
                                        <option value="4"><?php echo $LANG['UI_PLATFORM_ARCHIVE_JOB'] ?></option>
                                        <option value="5"><?php echo $LANG['UI_JOB_TASK_TYPE_VERIFY'] ?></option>
                                        <option value="6"><?php echo $LANG['UI_JOB_TASK_TYPE_REPLICATION'] ?></option>
                                    </select>
                                </div>
                            </div>
                            <!-- 备份模式 -->
                            <div class="form-group backup-mode-group">
                                <label class="control-label col-md-2"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_EVENT_SELECT_MODE'] ?></label>
                                <div class="col-md-10">
                                    <div class="input-group form-group-boxes" id="backupMode"></div>
                                </div>
                            </div>
                            <!-- 任务表格 -->
                            <div class="form-group">
                                <label class="control-label col-md-2"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_EVENT_SELECT_SOME'] ?></label>
                                <div class="col-md-10">
                                    <div class="table-toolbar">
                                        <div class="vin_toolbar" id="orchestrationTaskToolbar">
                                            <div class="leftTool">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-container">
                                        <table id="dataTable"></table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="alert alert-block alert-info fade in" id="addTaskTips">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                <ol>
                                    <li><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD_TASK_TIPS_TWO'] ?></li>
                                    <li><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD_TASK_TIPS_ONE'] ?></li>
                                    <li><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_ADD_TASK_TIPS_THREE'] ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="drawer-footer">
                    <button type="button" class="btn btn-primary btn-addTask"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-drawer/js/bootstrap-drawer.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/sortable/Sortable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/platform/orchestration/orchestration.js"></script>