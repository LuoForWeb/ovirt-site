<style>
    #jump_outlook:hover{
        background: #CFEDE6!important;
    }
    #emailDelete:hover{
        background: #CFEDE6!important;
    }
    #emailConfirm:hover{
        background: #CFEDE6!important;
    }
    #sendtestmail:hover{
        background: #CFEDE6 !important;
        color: #0FBF98;
    }
    .disabled {
        background: #F0F0F0;
        color: #CCCCCC;
    }
    .enabled {
        background-color: #E7F7F3;
        color: #0FBF98;
    }


</style>

<form action="#" class="form-horizontal">
    <div class="form-body-wrapper">
        <div class="form-body wp-50 hp-100 overflow-visible width80p_en">
            <div class="form-group">
                <label
                        class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL'] ?></label>
                <div class="col-md-6 col-md-4_en">
                    <input type="checkbox" id="emailcheck" checked class="make-switch" data-on-color="primary"
                           data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_POWER_ON_TIP'] ?></span>
                    </div>
                </div>
                <div class="col-md-3 col-md-4_en">
                    <span class="help-block">
                        <a id="testemail"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_HOST'] ?></a>
                    </span>
                </div>
            </div>

            <div class="emailcontent">
                <div class="form-group">
                    <label
                            class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?></label>
                    <div class="col-md-2">
                        <input type="checkbox" id="systemchecke" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group systemcheckediv">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_LEVEL_SYSTEM'] ?></label>
                    <div class="col-md-8 lh30">
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level1">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP1'] ?>
                        </label><br>
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level2">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP2'] ?>
                        </label><br>
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level3">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP3'] ?>
                        </label><br>
                    </div>
                </div>

                <div class="form-group">
                    <label
                            class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_PLATFORM_ALARM_TASK'] ?></label>
                    <div class="col-md-2">
                        <input type="checkbox" id="taskchecke" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group taskcheckediv">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_LEVEL_TASK'] ?></label>
                    <div class="col-md-8 lh30">
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level1">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_TASK_ALARM_TIP1'] ?>
                        </label><br>
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level2">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP2'] ?>
                        </label><br>
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level3">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP3'] ?>
                        </label><br>
                    </div>
                </div>

                <div class="form-group <?php if (!in_array('data_verification', $userAllPermission)) {
                    echo 'display-none';
                } ?>">
                    <label
                            class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_VERIFY_REPORT'] ?></label>
                    <div class="col-md-2">
                        <input type="checkbox" id="verifychecke" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group verifycheckediv <?php if (!in_array('data_verification', $userAllPermission)) {
                    echo 'display-none';
                } ?>">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_VERIFY_REPORT_LEVEL'] ?></label>
                    <div class="col-md-8">
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level1">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_TASK_ALARM_TIP1'] ?>
                        </label><br>
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level2">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP2'] ?>
                        </label><br>
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" name="level3">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_ALARM_TIP3'] ?>
                        </label><br>
                    </div>
                </div>

                <!-- 报表邮件通知 -->
                <!--<div class="form-group">
                    <label
                        class="control-label col-md-3 form-group-top4-label"><?php /*echo $LANG['UI_REPORT_NOTICE'] */?></label>
                    <div class="col-md-2">
                        <input type="checkbox" id="reportCheck" class="make-switch" data-on-color="primary"
                            data-size="small" data-off-color="info" data-on-text="<?php /*echo $LANG['UI_PUBLIC_ON'] */?>"
                            data-off-text="<?php /*echo $LANG['UI_PUBLIC_OFF'] */?>">
                    </div>
                </div>

                <div class="form-group reportcheckediv display-none">
                    <label class="control-label col-md-3"><?php /*echo $LANG['UI_REPORT_TYPE'] */?></label>
                    <div class="col-md-8 lh30">
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" id="storageReport">
                            <?php /*echo $LANG['UI_REPORT_STORAGE'] */?>
                        </label><br>
                        <label class="checkbox-inline mb-10 pt-0 ps-0">
                            <input type="checkbox" class="icheck level" id="vmReport">
                            <?php /*echo $LANG['UI_REPORT_VM'] */?>
                        </label><br>
                    </div>
                </div>

                <div class="form-group reportcheckediv display-none">
                    <label class="control-label col-md-3"><?php /*echo $LANG['UI_STRATEGY_TIME'] */?></label>
                    <div class="col-md-9 lh30" style="padding-top:5px;">
                        <div class="row" style="border:1px solid #e0e0e0;margin:0">
                            <div class="col-md-4 col-sm-4 col-xs-4">
                                <ul class="nav nav-tabs tabs-left">
                                    <li class="dwm active" data-type="1">
                                        <a href="#tab_day" data-toggle="tab" aria-expanded="true">
                                            <?php /*echo $LANG['UI_REPORT_DAILY'] */?><i
                                                class="viconfont vicon-ge_authorization2 font-green-seagreen opacity-0"
                                                id="dayIcon" style="margin-left:14px;"></i>
                                        </a>
                                    </li>
                                    <li class="dwm" data-type="2">
                                        <a href="#tab_week" data-toggle="tab" aria-expanded="true">
                                            <?php /*echo $LANG['UI_REPORT_WEEKLY'] */?> <i
                                                class="viconfont vicon-ge_authorization2 font-green-seagreen opacity-0"
                                                id="weekIcon" style="margin-left:10px;"></i>
                                        </a>
                                    </li>
                                    <li class="dwm" data-type="3">
                                        <a href="#tab_month" data-toggle="tab" aria-expanded="true">
                                            <?php /*echo $LANG['UI_REPORT_MONTHLY'] */?> <i
                                                class="viconfont vicon-ge_authorization2 font-green-seagreen opacity-0"
                                                id="monthIcon" style="margin-left:10px;"></i>
                                        </a>
                                    </li>

                                    <li class="dwm" data-type="3">
                                        <a href="#tab_year" data-toggle="tab" aria-expanded="true">
                                            <?php /*echo $LANG['UI_REPORT_ANNALS'] */?> <i
                                                class="viconfont vicon-ge_authorization2 font-green-seagreen opacity-0"
                                                id="yearIcon" style="margin-left:10px;"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-8 col-sm-8 col-xs-8" style="padding-left:0;padding-right:0">
                                <div class="tab-content">
                                    <div id="tab_day" class="tab-pane fade in active">
                                        <div class="form-group">
                                            <div class="flex-items-center mt-20">
                                                <label class="control-label form-group-top4-label"
                                                    style="width: 80px;text-align: left;"><?php /*echo $LANG['UI_REPORT_CONFIG_DAILY'] */?></label>
                                                <input type="checkbox" id="dayCheck" class="make-switch"
                                                    data-on-color="primary" data-off-color="info" data-size="small"
                                                    data-on-text="<?php /*echo $LANG['UI_PUBLIC_ON'] */?>"
                                                    data-off-text="<?php /*echo $LANG['UI_PUBLIC_OFF'] */?>">
                                            </div>
                                        </div>
                                        <div id="dayDiv" class="display-none">

                                        </div>
                                    </div>

                                    <div id="tab_week" class="tab-pane fade in">
                                        <div class="form-group">
                                            <div class="flex-items-center mt-20">
                                                <label class="control-label form-group-top4-label"
                                                    style="width: 80px;text-align: left;"><?php /*echo $LANG['UI_REPORT_CONFIG_WEEKLY'] */?></label>
                                                <input type="checkbox" id="weekCheck" class="make-switch"
                                                    data-on-color="primary" data-off-color="info" data-size="small"
                                                    data-on-text="<?php /*echo $LANG['UI_PUBLIC_ON'] */?>"
                                                    data-off-text="<?php /*echo $LANG['UI_PUBLIC_OFF'] */?>">
                                            </div>
                                        </div>
                                        <div id="weekDiv" class="display-none">

                                        </div>
                                    </div>
                                    <div id="tab_month" class="tab-pane fade in ">
                                        <div class="form-group">
                                            <div class="flex-items-center mt-20">
                                                <label class="control-label form-group-top4-label"
                                                    style="width: 80px;text-align: left;"><?php /*echo $LANG['UI_REPORT_CONFIG_MONTHLY'] */?></label>
                                                <input type="checkbox" id="monthCheck" class="make-switch"
                                                    data-on-color="primary" data-off-color="info" data-size="small"
                                                    data-on-text="<?php /*echo $LANG['UI_PUBLIC_ON'] */?>"
                                                    data-off-text="<?php /*echo $LANG['UI_PUBLIC_OFF'] */?>">
                                            </div>
                                        </div>
                                        <div id="monthDiv" class="display-none">

                                        </div>
                                    </div>
                                    <div id="tab_year" class="tab-pane fade in ">
                                        <div class="form-group">
                                            <div class="flex-items-center mt-20">
                                                <label class="control-label form-group-top4-label"
                                                    style="width: 80px;text-align: left"><?php /*echo $LANG['UI_REPORT_CONFIG_ANNALS'] */?></label>
                                                <input type="checkbox" id="yearCheck" class="make-switch"
                                                    data-on-color="primary" data-off-color="info" data-size="small"
                                                    data-on-text="<?php /*echo $LANG['UI_PUBLIC_ON'] */?>"
                                                    data-off-text="<?php /*echo $LANG['UI_PUBLIC_OFF'] */?>">
                                            </div>
                                        </div>
                                        <div id="yearDiv" class="display-none">
                                            <div class="input-group date form_datetime" style="width: 300px;">
                                                <input type="text" size="16" readonly id="yearTime"
                                                    class="form-control">
                                                <span class="input-group-btn">
                                                    <button class="btn default" id="resetStartTime" type="button"><i
                                                            class="fa fa-times"></i></button>
                                                    <button class="btn default date-set" type="button"><i
                                                            class="viconfont vicon-ge_calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->

                <div class="form-group emailtimediv display-none">
                    <label
                            class="control-label col-md-2 lh30 textalignr"><?php echo $LANG['UI_SETTINGS_NOTICE_TIME'] ?>:</label>
                    <div class="col-md-2">
                        <div class="input-group">
                            <input type="text" class="form-control timepicker timepicker-24 emailstarttime">
                            <span class="input-group-btn">
                                <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                            </span>
                        </div>
                    </div>
                    <label
                            class="control-label col-md-1 lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_TO'] ?></label>
                    <div class="col-md-2">
                        <div class="input-group">
                            <input type="text" class="form-control timepicker timepicker-24 emailendtime">
                            <span class="input-group-btn">
                                <button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="form-group <?php if (!in_array('db_drill', $userAllPermission)) {
                    echo 'display-none';
                } ?>">
                    <label
                            class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_SETTINGS_NOTICE_DATABASE_EMAIL'] ?></label>
                    <div class="col-md-2">
                        <input type="checkbox" id="databsecheck" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>
                <div class="form-group recemaildiv">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_RECV_EMAIL'] ?>
                    </label>
                    <div class="col-md-9">
                        <textarea class="form-control" id="emaillist" rows="4"
                                  placeholder="example@example.com"></textarea>
                        <div><span class="help-block ">
                                <?php echo $LANG['UI_SETTINGS_NOTICE_RECV_EMAIL_TIP1'] ?><br>
                                <?php echo $LANG['UI_SETTINGS_NOTICE_RECV_EMAIL_TIP2'] ?>
                            </span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions flex-items-center justify-content-center">
        <div class="wp-50 width80p_en">
            <label class="control-label col-md-3"></label>
            <div class="col-md-6">
                <button type="button" id="emailcancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <?php
                if (in_array("p_emial_notice", $_SESSION['permissionArr'])) {
                    echo '<button type="button" id="emailsubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] .'</button>';
                }
                ?>
            </div>
        </div>
    </div>
</form>

<!-- BEGIN EMAIL MODAL -->

<!-- END EMAIL MODAL -->

<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="testemaildiv" style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header ">
            <h4 class="drawer-title" id="drawer-1-title" style="vertical-align: top;padding-left:0;font-weight:400;font-size:16px;">
                <i class="levelchild viconfont vicon-gaojipeizhi" style="padding-top: 3px"></i>
                <span style="vertical-align: top"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_HOST'];?></span>
                <button type="button" class="close" data-dismiss="drawer" aria-hidden="true" style="transform: scale(0.66);"></button>
            </h4>
        </div>
        <!-- BEGIN FORM-->
        <div class="drawer-body form-horizontal" style="overflow: hidden;padding: 0px;">
            <div class="modal-body" style="padding: 0 !important;height: 89%;">
                <form action="#" id="testemailform" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group is_enterprise_en" style="margin-bottom: 16px;">
                            <label class="control-label col-md-3" style="padding-right: 5px;"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE'] ?>
                            </label>
                            <div class="col-md-8" style="width: 400px;height: 34px;">
                                <select class="form-control select2me" name="email_mode">
                                    <option value="1"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT'] ?></option>
                                    <option value="2"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_PERSON'] ?></option>
                                </select>
                                <div><span class="help-block ">

                            </span></div>
                            </div>
                        </div>

                        <div class="form-group show_email_type show_email_types">
                            <label class="control-label col-md-3" style="padding-right: 5px;"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_TYPE']?>
                            </label>
                            <div class="col-md-8" style="width: 400px;height: 34px;">
                                <select class="form-control select2me" name="email_model">
                                    <option value="1"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_TYPE_OTHER']?></option>
                                    <option value="2">outlook</option>
                                </select>
                                <div><span class="help-block ">
                            </span></div>
                            </div>
                        </div>

                        <div class="form-group show_email_types display-none">
                            <label class="col-md-3 control-label" style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_CLIENT_ID']?></label>
                            <div class="col-md-8" style="width: 400px; height: 34px;">
                                <div class="input-icon right ">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="64" name="client_id">
                                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_CLIENT_ID_TIPS']?>
                                </span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group show_email_types display-none">
                            <label class="col-md-3 control-label" style="margin-top: 20px; padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_CLIENT_SECRET']?></label>
                            <div class="col-md-8" style="width: 400px; height: 34px; margin-top: 20px;">
                                <div class="input-icon right ">
                                    <i class="fa"></i>
                                    <input type="password" class="form-control" maxlength="64" name="client_secret">
                                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_CLIENT_SECRET_TIPS']?>
                                </span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group show_email_types display-none">
                            <label class="col-md-3 control-label" style="margin-top: 20px;padding-right: 5px;"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_CLIENT_TENANT']?></label>
                            <div class="col-md-8" style="width: 400px; height: 34px; margin-top: 20px;">
                                <div class="input-icon right ">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="64" name="tenant_id">
                                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_CLIENT_TENANT_TIPS']?>
                                </span></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group show_email_type" style="margin-bottom: 8px;">
                            <label class="col-md-3 control-label" style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_SMTP'] ?></label>
                            <div class="col-md-8" style="width: 400px;">
                                <div class="input-icon right ">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="128" name="emailhost">
                                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMTP_TIP'] ?> </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group show_email_type" style="margin-bottom: 0px;">
                            <label class="col-md-3 control-label" style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_PORT'] ?></label>
                            <div class="col-md-8" style="width: 400px;">
                                <div class="input-icon right ">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="6" name="emailport">
                                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_PORT_TIP'] ?> </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group show_email_type show_email_types">
                            <label class="col-md-3 control-label" style="margin-top: 9px;padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_FROM'] ?></label>
                            <div class="col-md-8" style="width: 400px; height: 34px; margin-top: 9px;">
                                <div class="input-icon right ">
                                    <i class="fa"></i>
                                    <input type="text" class="form-control" maxlength="128" name="emailuser">
                                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_FROM_TIP'] ?>
                                </span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group show_email_type"style="margin-bottom: 8px;">
                            <label
                                    class="col-md-3 control-label" style="padding-right: 5px;margin-top: 20px;"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_FROM_PASS'] ?></label>
                            <div class="col-md-8" style="width: 400px;">
                                <div class="input-icon right ">
                                    <i class="fa"></i>
                                    <input type="password" autocomplete="off" class="form-control" maxlength="64"
                                           name="emailpass"
                                           oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"style="margin-top: 20px;">
                                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_FROM_PASS_TIP'] ?>
                                </span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group show_email_type" style="margin-bottom: 0px;">
                            <label
                                    class="control-label col-md-3" style="padding-right: 5px;"><span class="required">*</span><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_ENCRYPTION_CONNECTION'] ?>
                            </label>
                            <div class="col-md-8" style="width: 400px;">
                                <select class="form-control select2me" name="encryption">
                                    <option value="0"><?php echo $LANG['WEB_PLATFORM_PUBLIC_NONE'] ?></option>
                                    <option value="1">SSL</option>
                                    <option value="2">TLS</option>
                                </select>
                                <div><span class="help-block ">
                                <?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_ENCRYPTION_CONNECTION_TIPS'] ?>
                            </span></div>
                            </div>
                        </div>
                        <div class="form-group show_email_common" style="margin-bottom: 0px;">
                            <label class="control-label col-md-3" style="padding-right: 5px;"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_IS_SSL']?>
                            </label>
                            <div class="col-md-8" style="padding-top: 7px;">
                                <input type="checkbox" id="ssl_check" class="make-switch" data-on-color="primary"
                                       data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <div><span class="help-block ">
                                <?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_IS_SSL_TIPS']?>
                            </span></div>
                            </div>
                        </div>
                        <div class="form-group show_email_common" style="margin-bottom: 9px;">
                            <label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_IS_DEBUG']?>
                            </label>
                            <div class="col-md-8" style="padding-top: 7px;">
                                <input type="checkbox" id="debug_check" class="make-switch" data-on-color="primary"
                                       data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                       data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <div><span class="help-block ">
                                <?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_IS_DEBUG_TIPS']?>
                            </span></div>
                            </div>
                        </div>
                        <div class="form-group show_email_types display-none">
                            <div class="col-md-offset-3 col-md-8" style="margin-top: 20px;">
                                <button type="button" class="btn btn-default textalignr" id="jump_outlook"
                                        style="background: #E7F7F3; width: 208px; color: #0FBF98;"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_OAUTH']?></button>
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: -8px;">
                            <div class="col-md-offset-3 col-md-8" style="margin-top: 4px;">
                                <button type="button" disabled class="btn btn-default textalignr"
                                        id="sendtestmail" style="color: #0FBF98; background: #E7F7F3;"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_SEND'] ?></button>
                                <label id="emailconfig"
                                       style="color: #666666; font-size: 12px!important; padding-top: 8px;margin-left: 4px;color: #0FBF98;cursor: pointer;"
                                       class="control-label lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_CONFIGURATION'] ?></label>
                                <label id="emailconfig2"
                                       style="color: #666666; font-size: 12px!important; padding-top: 4px;color: #999999;display: none;margin-left: 4px;"
                                       class="control-label lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_TO'] ?></label>
                                <label class="control-label lh30 textalignc" id="testrecemail" style="display: none;font-size: 12px!important; padding-top: 4px;"></label>

                                <i id="xiugai2" class="levelchild viconfont vicon-a-Editbianji1" style="width: 16px; height: 16px;display: none;color: #0FBF98;cursor: pointer;"></i>

                                <div id="emailconfig_div" class="display-none">
                                    <input type="text" placeholder="<?php echo $LANG['UI_SETTINGS_NOTICE_INPUT_EMAIL'] ?>" id="EmailAddress" maxlength="128" class="form-control" name="email" style="margin-top: 12px;width: 316px !important;">
                                    <button class="btn viconfont vicon-a-Close-oneguanbi-copy b-btn brr2 mr12 green-haze" id="emailDelete"
                                            style="margin-left: 324px;margin-top: -34px;background: #E7F7F3;color: #0FBF98;"title=<?php echo $LANG['UI_PUBLIC_CANCEL'] ?>></button>
                                    <button class="btn viconfont vicon-Frame-15 b-btn brr2 mr12 green-haze" id="emailConfirm"
                                            style="margin-left: 366px;margin-top: -34px;background: #E7F7F3;color: #0FBF98;"title=<?php echo $LANG['UI_PUBLIC_CONFIRM'] ?>></button>
                                    <div><span class="help-block ">
							                <?php echo $LANG['UI_SETTINGS_NOTICE_INPUT_EMAIL_TIPS']?>
                                        </span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group show_email_type2" style="height: 100px;">

                        </div>
                    </div>
                </form>

            </div>
            <div class="alert alert-block alert-info fade in mt15 show_email_common" id="tabletips"
                 style="display: block;margin-bottom: 0; bottom: 77px;position: fixed;width: 762px;margin-left: 19px;">
                <button id="cencel" type="button" class="close" data-dismiss="alert"></button>
                <h4 class="alert-heading"><strong style="font-size: 16px;"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                <ol class="alert-ol" style="font-size: 12px;">
                    <li>
                        <?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_SEND_TIP1'] ?>
                    </li>
                    <li>
                        <?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL_SEND_TIP2'] ?>
                    </li>
                </ol>
            </div>
        </div>
        <div class="alert alert-block alert-info fade in mt15 show_email_types display-none" id="tabletips2"
             style="margin-left: 19px;width: 762px;margin-top: -118px!important;">
            <button id="cencel2" type="button" class="close" data-dismiss="alert"></button>
            <h4 class="alert-heading"><strong style="font-size: 16px;"><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
            <ol class="alert-ol" style="font-size: 12px;">
                <li>
                    <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_OUTLOOK_TIPS1']?>
                </li>
                <li>
                    <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_OUTLOOK_TIPS2']?>
                </li>
                <li>
                    <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_OUTLOOK_TIPS3']?>
                </li>
                <li>
                    <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_EMAIL_OUTLOOK_TIPS4']?>
                </li>
            </ol>
        </div>
        <div class="drawer-footer">
            <div id="bottom-btn">
                <button type="button" id="testemailsubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-default" style=" margin-right:0;"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="./scripts/platform/settings/settingstab/email_notice.js"></script>