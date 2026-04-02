<?php include_once '../../../tpl/permission.php'; ?>

<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link href="./css/platform/report.css" rel="stylesheet" type="text/css"/>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE HEADER-->

<!-- END PAGE HEADER -->
<!-- BEGIN PAGE CONTENT-->
<div class="custom-report-wrapper">
    <div class="custom-report-wrapper__header">
        <i class="viconfont vicon-moban me-8"></i>
        <span class="custom-report-wrapper__header__text"><?php echo $LANG['UI_REPORT_STRATEGY_LIST'] ?></span>
    </div>
    <div class="custom-report-wrapper__content hover-scroll-y">
        <div class="obs-manager-toolbar" id="custom_report_toolbar">
            <div class="tool-left">
                <div class="tool-left-item <?php if (!in_array('p_storage_report_delete', $_SESSION['permissionArr'])) {
                    echo 'display-none';
                } ?>">
                    <button type="button" id="delete" disabled
                        class="btn btn-toolbar-delete disabled">
                        <i class="viconfont vicon-a-Deleteshanchu1"></i>
                    </button>
                </div>
                <div class="tool-left-item">
                    <div class="search input-group">
                        <input id="search" class="currentSearch customSearch"
                            style="min-width:200px;padding-right:30px" autocomplete="off" type="text"
                            placeholder="<?php echo $LANG['UI_SEARCY_BY_REPORT_TEMPLATE'] ?>">
                        <div class="position0" style="width:auto;height:34px">
                            <button class="b-btn clear hide position0" id="report_clear_search"><i
                                    class="icon-close-small"></i></button>
                        </div>
                        <div class="search-btn positionL0" style="width:auto;height:34px;">
                            <button id="report_search" class="b-btn search-btn"><i class="icon-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="tool-left-item <?php if (!in_array('p_storage_report_add', $_SESSION['permissionArr'])) {
                    echo 'display-none';
                } ?>" style="margin-right: 0;">
                    <button type="button" id="openReportDrawer" class="btn-font btn-title">
                        <i class="viconfont vicon-ge_add_task me-4"></i>
                        <?php echo $LANG['UI_PUBLIC_ADDNEW'] ?>
                    </button>
                </div>
            </div>
            <div class="tool-right rightTool vin_toolbar">
                <div class="vin_btnToolbar"></div>
            </div>
        </div>
        <div class="table-container">
            <table id="report_table"></table>
        </div>
        <div class="alert alert-block alert-info fade in m0" id="custom_report_tip">
            <div class="alert-wrapper">
                <button id="custom_report_tip_close" type="button" class="alert-close-btn" data-dismiss="alert"></button>
                <div class="alert-title"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</div>
                <div class="alert-wrapper__content">
                    <div class="header"><?php echo $LANG['UI_CUSTOM_REPOSRT_INFO_HEADER'] ?></div>
                    <div class="ol-wrapper">
                        <ol>
                            <li class="ol-wrapper__item"><?php echo $LANG['UI_CUSTOM_REPOSRT_INFO_BODY_TIPS1'] ?></li>
                            <li class="ol-wrapper__item"><?php echo $LANG['UI_CUSTOM_REPOSRT_INFO_BODY_TIPS2'] ?></li>
                            <li class="ol-wrapper__item"><?php echo $LANG['UI_CUSTOM_REPOSRT_INFO_BODY_TIPS3'] ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BEGIN ADD/EDIT CUSTOM REPORT DRAWER -->
<div class="custom-report-drawer drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="custom_report_drawer_title" id="custom_report_drawer">
    <div class="drawer-header">
        <span class="drawer-header__title" id="custom_report_drawer_title">
            <i class="viconfont vicon-danchuangtianjia1"></i>
            <span id="title"></span>
        </span>
        <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
    </div>
    <div class="drawer-body">
        <div class="custom-strategy-form">
            <form action="#" id="custom_report_form">
                <div class="custom-strategy-form-item template-name-form-item form-group">
                    <div class="custom-strategy-form-item__label control-label col-md-2 col-md-3_en">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_REPORT_STRATEGY_NAME'] ?>
                    </div>
                    <div class="custom-strategy-form-item__content col-md-10 col-md-9_en">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <input id="template_name" type="text" maxlength="128" class="form-control" name="template_name"/>
                        </div>
                        <div class="custom-validate-tip validate-template-name-tip display-none"><?php echo $LANG['UI_TEMPLATE_REPORT_NAME_CANNOT_EMPTY'] ?></div>
                    </div>
                </div>
                <div class="custom-strategy-form-item">
                    <div class="custom-strategy-form-item__label col-md-2 col-md-3_en">
                        <?php echo $LANG['UI_REPORT_STRATEGY_TYPE'] ?>
                    </div>
                    <div class="custom-strategy-form-item__content col-md-10 col-md-9_en">
                        <select id="template_type" class="form-control">
                            <?php echo in_array("vcenter_manager", $_SESSION['permission']) ? '<option value="1">' . $LANG['UI_VCENTER_VC'] . '</option>' : '' ?>">
                            <?php echo in_array("client", $_SESSION['permission']) ? '<option value="2">' . $LANG['UI_JOB_CLIENT'] . '</option>' : '' ?>">
                            <?php echo in_array("vol_cdp_protect", $_SESSION['permission']) ? '<option value="3">' . $LANG['UI_CONTINUOUS_DATA_PROTECT'] . '</option>' : '' ?>">
                            <?php echo in_array("nas_protect", $_SESSION['permission']) ? '<option value="4">' . $LANG['WEB_PLATFORM_DES_NAS'] . '</option>' : '' ?>">
                            <?php echo in_array("storage_manager", $_SESSION['permission']) ? '<option value="5">' . $LANG['UI_PALTFORM_STORAGE'] . '</option>' : '' ?>">
                            <?php echo in_array("task", $_SESSION['permission']) ? '<option value="6">' . $LANG['UI_PUBLIC_JOB'] . '</option>' : '' ?>">
                            <?php echo in_array("office365_protect", $_SESSION['permission']) ? '<option value="7">' . $LANG['UI_PLATFORM_MICROSOFT365'] . '</option>' : '' ?>">
                            <?php echo in_array("cloud_platform", $_SESSION['permission']) ? '<option value="8">' . $LANG['WEB_PLATFORM_DES_PUBLIC_CLOUD'] . '</option>' : '' ?>">
                            <?php echo in_array("cloud_platform_private", $_SESSION['permission']) ? '<option value="9">' . $LANG['WEB_PLATFORM_DES_PRIVATE_CLOUD'] . '</option>' : '' ?>">
                            <?php echo in_array("obs_protect", $_SESSION['permission']) ? '<option value="10">' . $LANG['UI_PLATFORM_OBS_PROTECT'] . '</option>' : '' ?>">
                            <?php echo in_array("hadoop_protect", $_SESSION['permission']) ? '<option value="11">' . $LANG['WEB_HADOOP_PROTECT'] . '</option>' : '' ?>">
                            <?php echo in_array("file_copy_protect", $_SESSION['permission']) ? '<option value="12">' . $LANG['UI_FILE_COPY'] . '</option>' : '' ?>">
                            <?php echo in_array("k8s_protect", $_SESSION['permission']) ? '<option value="13">' . $LANG['UI_ARCHIVE_CLOUD_CONTAIN'] . '</option>' : '' ?>">
                        </select>
                    </div>
                </div>
                <div class="custom-strategy-form-item align-items-baseline mb-0">
                    <div class="custom-strategy-form-item__label col-md-2 col-md-3_en"><?php echo $LANG['UI_REPORT_STRATEGY_OVERVIEW'] ?></div>
                    <div class="custom-strategy-form-item__content col-md-10 col-md-9_en">
                        <div class="operate-btns">
                            <span id="select_all" class="operate-btns__item"><?php echo $LANG['UI_RECOVERY_FILE_SELECT_ALL'] ?></span>
                            <span class="separator-vertical mx-8"></span>
                            <span id="clean_all" class="operate-btns__item"><?php echo $LANG['UI_JOB_TASK_ORCHESTRATION_FILTER_CLEAR'] ?>
                        </div>
                        <div class="checkbox-wrapper mt-10" id="overview_data_checkbox_group"></div>
                    </div>
                </div>
                <div class="custom-strategy-form-item mt-6">
                    <div class="custom-strategy-form-item__label col-md-2 col-md-3_en"><?php echo $LANG['UI_REPORT_DENCY'] ?></div>
                    <div class="custom-strategy-form-item__content col-md-10 col-md-9_en">
                        <div class="checkbox-wrapper" id="running_tendency_checkbox_group"></div>
                    </div>
                </div>
                <div class="custom-strategy-form-item form-group">
                    <div class="custom-strategy-form-item__label control-label col-md-2 col-md-3_en">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_REPORT_CUSTOMIZE_DATA'] ?>
                    </div>
                    <div class="custom-strategy-form-item__content position-relative col-md-10 col-md-9_en">
                        <select 
                            id="customized_data_select" 
                            class="bootstrap-mutiple-select selectpicker show-tick"
                            multiple
                            data-actions-box="true" 
                            data-select-all-text="<?php echo $LANG['UI_REPORT_ALL_SELECT'] ?>"
                            data-deselect-all-text="<?php echo $LANG['UI_REPORT_ALL_CANCEL'] ?>"
                            data-none-selected-text="<?php echo $LANG['UI_PUBLIC_SELECT'] ?>"
                        >
                        </select>
                        <div class="custom-validate-tip validate-customized-data-tip display-none"><?php echo $LANG['UI_SELECTED_ATLEAST_THREE_CUSTOMIZED_DATA'] ?></div>
                    </div>
                </div>
                <div class="custom-strategy-form-item">
                    <div class="custom-strategy-form-item__label col-md-2 col-md-3_en"><?php echo $LANG['UI_REPORT_EMAIL'] ?></div>
                    <div class="custom-strategy-form-item__content email-notice-content col-md-10 col-md-9_en">
                        <input
                            id="email_notice"
                            type="checkbox" 
                            class="make-switch"
                            data-on-color="primary" 
                            data-off-color="info" 
                            data-size="small"
                            data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                            data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>"
                        />
                        <a 
                            class="popovers ml15" 
                            data-container="body" 
                            data-trigger="hover"
                            data-placement="right"
                            data-content="<?php echo $LANG['UI_REPORT_EMALI_NOTE'] ?>"
                        >
                            <i class="viconfont vicon-tishi"></i>
                        </a>
                    </div>
                </div>
                <div class="custom-strategy-form-item email-notice-strategy-form-item display-none">
                    <div class="custom-strategy-form-item__label col-md-2 col-md-3_en"></div>
                    <div class="custom-strategy-form-item__content col-md-10 col-md-9_en">
                        <div class="panel panel-default strategy-panel">
                            <div class="panel-heading">
                                <h4 class="panel-title">
                                    <a class="accordion-toggle popovers recoverytimecollapseview" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent=".confrecoverytime" href="#recovery_timepoint_view" >
                                        <i class="levelchild viconfont vicon-pt_setting_email_notification font-green-seagreen"></i>
                                        <span class="font-green-seagreen"><?php echo $LANG['UI_REPORT_EMAIL_STRATEGY'] ?></span>
                                    </a>
                                </h4>
                            </div>
                            <div id="recovery_timepoint_view" class="panel-collapse collapse in recoverytimecollapseview">
                                <div class="panel-body row">
                                    <div class="col-md-3 nav-tab-radios">
                                        <ul class="nav nav-tabs tabs-left m0">
                                            <li class="nav-tabs-item daily-report-tabs-item active">
                                                <a href="#daily_report_pane" data-toggle="tab" aria-expanded="true">
                                                    <?php echo $LANG['UI_REPORT_DAILY'] ?>
                                                    <i id="daily_report_i" class="viconfont vicon-ge_authorization2 font-green-seagreen ms-10 opacity-0"></i>
                                                </a>
                                            </li>
                                            <li class="nav-tabs-item weekly-report-tabs-item">
                                                <a href="#weekly_report_pane" data-toggle="tab" aria-expanded="true">
                                                    <?php echo $LANG['UI_REPORT_WEEKLY'] ?>
                                                    <i id="weekly_report_i" class="viconfont vicon-ge_authorization2 font-green-seagreen ms-10 opacity-0"></i>
                                                </a>
                                            </li>
                                            <li class="nav-tabs-item monthly-report-tabs-item">
                                                <a href="#monthly_report_pane" data-toggle="tab" aria-expanded="true">
                                                    <?php echo $LANG['UI_REPORT_MONTHLY'] ?>
                                                    <i id="monthly_report_i" class="viconfont vicon-ge_authorization2 font-green-seagreen ms-10 opacity-0"></i>
                                                </a>
                                            </li>
                                            <li class="nav-tabs-item annual-report-tabs-item">
                                                <a href="#annual_report_pane" data-toggle="tab" aria-expanded="true">
                                                    <?php echo $LANG['UI_REPORT_ANNALS'] ?>
                                                    <i id="annual_report_i" class="viconfont vicon-ge_authorization2 font-green-seagreen ms-10 opacity-0"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-9 tab-content" id="notice_strategy_content">
                                        <div id="daily_report_pane" class="tab-pane fade in active">
                                            <div class="custom-strategy-form-item">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_REPORT_CONFIG_DAILY'] ?></div>
                                                <div class="custom-strategy-form-item__content col-md-9">
                                                    <input
                                                        id="daily_report_notice"
                                                        type="checkbox" 
                                                        class="make-switch" 
                                                        data-on-color="primary" 
                                                        data-off-color="info"
                                                        data-size="small"
                                                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                </div>
                                            </div>
                                            <div class="custom-strategy-form-item daily-report-time-form-item display-none">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_TIME'] ?></div>
                                                <div class="custom-strategy-form-item__content col-md-9">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24 speedstarttime" id="daily_report_time" placeholder="00:00:00">
                                                        <span class="input-group-btn">
                                                            <button class="btn default btn-time h-34px" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>
                                                        </span>
                                                    </div>
                                                    <div class="custom-validate-tip validate-daily-time-tip display-none"><?php echo $LANG['UI_PLEASE_SELECT_NOTICE_TIME'] ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="weekly_report_pane" class="tab-pane position-relative fade in">
                                            <div class="custom-strategy-form-item">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_REPORT_CONFIG_WEEKLY'] ?></div>
                                                <div class="custom-strategy-form-item__content col-md-9">
                                                    <input
                                                        id="weekly_report_notice"
                                                        type="checkbox" 
                                                        class="make-switch" 
                                                        data-on-color="primary" 
                                                        data-off-color="info"
                                                        data-size="small"
                                                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                </div>
                                            </div>

                                            <div class="custom-strategy-form-item weekly-checkbox-form-item align-items-baseline display-none">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_STRATEGY_EVERY_WEEK'] ?></div>
                                                <div class="custom-strategy-form-item__content checkbox-wrapper col-md-9" id="weekly_checkbox_group"></div>
                                            </div>
                                            <div class="custom-validate-tip validate-weekly-checkbox-tip display-none"><?php echo $LANG['UI_PLAEASE_ATLEAST_ONE_OPTION'] ?></div>

                                            <div class="custom-strategy-form-item weekly-report-time-form-item display-none">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_TIME'] ?></div>
                                                <div class="custom-strategy-form-item__content col-md-9">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24 speedstarttime" id="weekly_report_time" placeholder="00:00:00">
                                                        <span class="input-group-btn">
                                                            <button class="btn default btn-time h-34px" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>
                                                        </span>
                                                    </div>
                                                    <div class="custom-validate-tip validate-weekly-time-tip display-none"><?php echo $LANG['UI_PLEASE_SELECT_NOTICE_TIME'] ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="monthly_report_pane" class="tab-pane position-relative fade in">
                                            <div class="custom-strategy-form-item">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_REPORT_CONFIG_MONTHLY'] ?></div>
                                                <div class="custom-strategy-form-item__content col-md-9">
                                                    <input
                                                        id="monthly_report_notice"
                                                        type="checkbox" 
                                                        class="make-switch" 
                                                        data-on-color="primary" 
                                                        data-off-color="info"
                                                        data-size="small"
                                                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                </div>
                                            </div>

                                            <div class="custom-strategy-form-item monthly-checkbox-form-item align-items-baseline display-none">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_STRATEGY_EVERY_MONTH'] ?></div>
                                                <div class="custom-strategy-form-item__content checkbox-wrapper monthly-checkbox-wrapper col-md-9" id="monthly_checkbox_group"></div>
                                            </div>
                                            <div class="custom-validate-tip validate-monthly-checkbox-tip display-none"><?php echo $LANG['UI_PLAEASE_ATLEAST_ONE_OPTION'] ?></div>

                                            <div class="custom-strategy-form-item monthly-report-time-form-item display-none">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_TIME'] ?></div>
                                                <div class="custom-strategy-form-item__content col-md-9">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control timepicker timepicker-24 speedstarttime" id="monthly_report_time" placeholder="00:00:00">
                                                        <span class="input-group-btn">
                                                            <button class="btn default btn-time h-34px" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button>
                                                        </span>
                                                    </div>
                                                    <div class="custom-validate-tip validate-monthly-time-tip display-none"><?php echo $LANG['UI_PLEASE_SELECT_NOTICE_TIME'] ?></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="annual_report_pane" class="tab-pane fade in">
                                            <div class="custom-strategy-form-item">
                                                <div class="custom-strategy-form-item__label col-md-3"><?php echo $LANG['UI_REPORT_CONFIG_ANNALS'] ?></div>
                                                <div class="custom-strategy-form-item__content col-md-9">
                                                    <input
                                                        id="annual_report_notice"
                                                        type="checkbox" 
                                                        class="make-switch" 
                                                        data-on-color="primary" 
                                                        data-off-color="info"
                                                        data-size="small"
                                                        data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="custom-validate-tip validate-notice-strategy-tip display-none"></div>
                    </div>
                </div>
                <div class="custom-strategy-form-item form-group email-address-form-item align-items-baseline display-none">
                    <div class="custom-strategy-form-item__label control-label col-md-2 col-md-3_en">
                        <span class="required">* </span>
                        <?php echo $LANG['UI_REPORT_EMAIL_ADDRESS'] ?>
                    </div>
                    <div class="custom-strategy-form-item__content col-md-10 col-md-9_en">
                        <div class="input-icon right">
                            <i class="fa"></i>
                            <textarea id="emails" class="form-control" name="emails" rows="5" placeholder="<?php echo $LANG['UI_REPORT_ADDRESS_NOTICE'] ?>"></textarea>
                        </div>
                        <div class="custom-validate-tip validate-emails-tip display-none"></div>
                    </div>
                </div>
                <div class="custom-strategy-form-item description-form-item align-items-baseline">
                    <div class="custom-strategy-form-item__label col-md-2 col-md-3_en"><?php echo $LANG['BILLING_TABLE_DES'] ?></div>
                    <div class="custom-strategy-form-item__content col-md-10 col-md-9_en">
                        <textarea class="form-control" id="description" name="description" rows="5" placeholder="<?php echo $LANG['UI_RESOURCE_GROUP_DETAIL_INFO'] ?>"></textarea>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
    <div class="drawer-footer">
        <button type="button" class="btn btn-primary" id="custom_report_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
        <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
    </div>
</div>
<!-- END ADD/EDIT CUSTOM REPORT DRAWER -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./scripts/platform/reports/strategy.js"></script>
<!-- END PAGE LEVEL PLUGINS -->