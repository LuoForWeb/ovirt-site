<?php include_once '../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="task" href="./content/platform/jobs/jobs.php?tab=2">
            <?php echo $LANG['UI_JOB_TASK_ORCHESTRATION'] ?>
        </a>
    </li>
    <span>></span>
    <li class="current"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_DETAILS']; ?></li>
</h3>
<!-- BEGIN PAGE CONTENT-->
<div class="orchestration-detail" id="orchestrationContent">
    <input id="plan_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
    <div class="portlet box blue-hoki" id="orchestrationDetails">
        <div class="orchestration-detail-top">
            <div class="col-md-8">
                <div class="caption">
                    <i class="viconfont vicon-renwuliuliang"></i><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_STREAM'] ?>
                </div>
            </div>
            <div class="col-md-12 section-wrapper">
            </div>
        </div>
        <div class="orchestration-detail-bottom">
            <!-- BEGIN TAB PORTLET-->
            <div class="job-detail__halfbottom__portlet">
                <div class="portlet-title">
                    <ul class="nav nav-tabs floatl">
                        <li class="active ">
                            <a href="#detail" data-toggle="tab">
                                <i class="viconfont vicon-tishi"></i> <?php echo $LANG['UI_JOB_SUMMARY'] ?> </a>
                        </li>
                        <li>
                            <a href="#history" data-toggle="tab">
                                <i class="viconfont vicon-histroytask"></i> <?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_HISTORY'] ?> </a>
                        </li>
                    </ul>
                </div>
                <div class="portlet-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="detail">
                            <div class="portlet-body">
                                <!-- 操作 -->
                                <div class="row static-info">
                                    <div class="col-md-1 name width20_en"><?php echo $LANG['UI_PUBLIC_OPERATION'] ?>：</div>
                                    <div class="col-md-8 value">
                                        <div class="btn-group dropdown-wrapper">
                                            <button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
                                                <i class="glyphicon glyphicon-hand-up"></i> <?php echo $LANG['UI_PUBLIC_OPERATION'] ?> <i class="fa fa-angle-down"></i>
                                            </button>
                                            <ul class="dropdown-menu" role="menu" id="<?php echo $_GET['uuid']; ?>">
                                                <li class="start"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_OPERATE_START'] ?></button></li>
                                                <li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_OPERATE_STOP'] ?></button></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- 名称 -->
                                <div class="row static-info">
                                    <div class="col-md-1 name width20_en"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_NAME'] ?>：</div>
                                    <div class="col-md-8 value col-plan_nickname"></div>
                                </div>
                                <!-- 状态 -->
                                <div class="row static-info">
                                    <div class="col-md-1 name width20_en"><?php echo $LANG['UI_PUBLIC_STATUS'] ?>：</div>
                                    <div class="col-md-8 value col-plan_state"></div>
                                </div>
                                <div class="row static-info">
                                    <div class="col-md-1 name width20_en"><?php echo $LANG['UI_STRATEGY_TIME'] ?>：</div>
                                    <div class="col-md-8 value col-time_strategy"></div>
                                </div>
                                <!-- 任务数量 -->
                                <div class="row static-info">
                                    <div class="col-md-1 name width20_en"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_TASK_NUMBERS'] ?>：</div>
                                    <div class="col-md-8 value col-task_numbers"></div>
                                </div>
                                <!-- 累计执行次数 -->
                                <div class="row static-info">
                                    <div class="col-md-1 name width20_en"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_EXECUTE_COUNT'] ?>：</div>
                                    <div class="col-md-8 value col-execute_count"></div>
                                </div>
                                <!-- 下次执行时间 -->
                                <div class="row static-info">
                                    <div class="col-md-1 name width20_en"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_NEXT_TIME'] ?>：</div>
                                    <div class="col-md-8 value col-next_time"></div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="history">
                            <div class="vin_toolbar" id="historyToolbar">
                                <div class="leftTool">
                                </div>
                                <div class="rightTool">
                                    <div class="vin_btnToolbar"></div>
                                </div>
                            </div>
                            <div class="table-container">
                                <table id="historyTable">
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END TAB PORTLET-->


        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
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

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/platform/orchestration/orchestration_details.js"></script>
<!-- END PAGE LEVEL PLUGINS -->