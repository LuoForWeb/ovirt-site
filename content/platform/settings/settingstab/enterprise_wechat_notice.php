<style>

    /* 启用状态：已配置，显示绿色 */
    .enabled {
        background-color:#E7F7F3;
        color: #0FBF98;
    }

    .enabled:hover{
        background-color: #CFEDE6!important;
        color: #0FBF98;
    }

    /* 禁用状态：未配置，显示灰色 */
    .disabled {
        background-color: #F0F0F0!important;
        color: #CCCCCC;
    }
    #sendtestwechat2:focus{
        background: #E7F7F3;
        color: #0FBF98;
    }
    #sendtestwechat2:disabled{
        background: #F0F0F0 !important;
    }
</style>

<form action="#" class="form-horizontal">
    <div class="form-body-wrapper">
        <div class="form-body wp-50 hp-100 overflow-visible width80p_en">
            <div class="form-group">
                <label
                        class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'] ?></label>
                <div class="col-md-6 col-md-4_en">
                    <input type="checkbox" id="wechatcheck2" checked class="make-switch" data-on-color="primary"
                           data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    <div><span
                                class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP_ENTER'] ?></span>
                    </div>
                </div>
                <div class="col-md-3 col-md-4_en">
                    <span class="help-block lh30 lh-22_en">
                        <a id="testwechat2"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_WECHAT_ENTER_HOST'] ?></a>
                    </span>
                </div>
            </div>

            <div class="wechatcontent2">
                <div class="form-group">
                    <label
                            class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SENDTYPE'] ?></label>
                    <div class="col-md-3">
                        <div class="mt-5"><label>
                                <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'] ?>
                            </label></div>
                        <div><span
                                    class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SENDTYPE_ENTER_TIPS'] ?></span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label
                            class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?></label>
                    <div class="col-md-2">
                        <input type="checkbox" id="systemchecks22" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group systemchecks22div">
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
                        <input type="checkbox" id="taskchecks22" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group taskchecks22div">
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
            </div>

            <div class="form-group cmstimediv display-none">
                <label
                        class="control-label col-md-3 lh30 textalignr"><?php echo $LANG['UI_SETTINGS_NOTICE_TIME'] ?>:</label>
                <div class="form-group-content flex-items-center">
                    <div class="input-group" style="width:160px;margin-left:15px">
                        <input type="text" class="form-control timepicker timepicker-24 emailstarttime">
                        <span class="input-group-btn">
                            <button class="btn default btn-time" type="button"><i class="fa fa-clock-o"></i></button>
                        </span>
                    </div>
                    <span class="mx-15"><?php echo $LANG['UI_SETTINGS_NOTICE_TO'] ?></span>
                    <div class="input-group" style="width:160px">
                        <input type="text" class="form-control timepicker timepicker-24 emailendtime">
                        <span class="input-group-btn">
                            <button class="btn default btn-time" type="button"><i class="fa fa-clock-o"></i></button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions flex-items-center justify-content-center">
        <div class="wp-50 width80p_en">
            <label class="control-label col-md-3"></label>
            <div class="col-md-6">
                <button type="button" id="wechat2cancel"
                        class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <?php
                if (in_array("p_enterprise_wechat_notice", $_SESSION['permissionArr'])) {
                    echo '<button type="button" id="wechat2submit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] .'</button>';
                }
                ?>
            </div>
        </div>
    </div>
</form>

<!-- BEGIN WECHAT2 MODAL -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="current_pending_task_drawer3" style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header" style="height: 52px;">
            <h4 class="drawer-title" id="settings-drawer-title" style="margin-top: 0px;font-size: 16px;">
                <i class="levelchild viconfont vicon-gaojipeizhi"></i>
                <button type="button" class="close" data-dismiss="drawer" aria-hidden="true" style="transform: scale(0.66);"></button>
                <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_WECHAT_ENTER_HOST'] ?></h4>
        </div>
        <div class="drawer-body" style="padding: 0px;">
            <form action="#" id="testwechat2form" class="form-horizontal">
                <div class="form-body" style="height: 100%; overflow: hidden;">

                    <div class="form-group show_wechat_type2">
                        <label class="col-md-3 control-label" style="padding-right: 5px;">
                            <span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_CORE_ID'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="128" name="wechat_core_id" style="width: 400px;">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_CORE_ID_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type2">
                        <label class="col-md-3 control-label" style="padding-right: 5px;">
                            <span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_APP_ID'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="128" name="wechat_app_id" style="width: 400px;">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_APP_ID_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type2">
                        <label
                                class="col-md-3 control-label" style="padding-right: 5px;">
                            <span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_APP_SECRET'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="128" name="wechat_app_secret" style="width: 400px;">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_APP_SECRET_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group show_wechat_type2" style="margin-bottom: 7px;">
                        <label
                                class="col-md-3 control-label" style="padding-right: 5px;">
                            <span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_URL'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="128" placeholder="" name="wechat_url" style="width: 400px;">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_URL_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group show_wechat_type2">
                        <div class="col-md-offset-3 col-md-8">
                            <button disabled type="button" class="btn btn-default textalignr"
                                    id="sendtestwechat2"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEST']; ?></button>
                        </div>
                    </div>
                    <div class="form-group show_wechat_type2">
                        <div class="col-md-offset-3 col-md-8">
                            <div class="alert alert-block alert-info fade in" style="position: fixed;width: 762px;margin-left: -206px;bottom: 61px;">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <h4 class="alert-heading" style="font-size: 16px;color: #717C7A"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                <ol class="alert-ol" style="font-size: 12px;">
                                    <li>
                                        <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SEND2_TIP1'] ?>
                                    </li>
                                    <li>
                                        <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SEND2_TIP2'] ?>
                                    </li>
                                    <li>
                                        <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SEND2_TIP3'] ?>
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #F1F3F5;">
            <div id="bottom-btn">
                <button type="button" data-dismiss="drawer" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
                <button type="button" class="btn btn-primary" id="testwechat2submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
    </div>
    <!-- END WECHAT2 MODAL -->

    <script type="text/javascript" src="./scripts/platform/settings/settingstab/enterprise_wechat_notice.js"></script>