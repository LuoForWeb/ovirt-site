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
        color: #CCCCCC !important;
    }
    #sendtestwechat:focus{
        background: #E7F7F3;
        color: #0FBF98;
    }
    #sendtestwechat:disabled{
        background: #F0F0F0 !important;
    }
    .help-block{
        color: #999999!important;
    }
    .form-control{
        width: 400px!important;
    }
    .input-icon {
        width: 400px!important;
    }
</style>

<form action="#" class="form-horizontal">
    <div class="form-body-wrapper">
        <div class="form-body wp-50 hp-100 overflow-visible width80p_en">
            <div class="form-group">
                <label
                        class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT'] ?></label>
                <div class="col-md-6 col-md-4_en">
                    <input type="checkbox" id="wechatcheck" checked class="make-switch" data-on-color="primary"
                           data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_POWER_ON_TIP'] ?></span>
                    </div>
                </div>
                <div class="col-md-3 col-md-4_en">
                    <span class="help-block lh30">
                        <a id="testwechat"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_WECHAT'] ?></a>
                    </span>
                </div>
            </div>
            <div class="wechatcontent">
                <div class="form-group">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE'] ?></label>
                    <div class="col-md-6">
                        <div class="mt-5"><label>
                                <span id="wechatsendtypedes">
                                    <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT'] ?></span>
                            </label></div>
                        <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_TIPS'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label
                            class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_WECHAT_MEMBER_SET'] ?></label>
                    <div class="col-md-6">
                        <div class="mt-5"><label>
                                <a id="testwechat_member">
                                    <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_WECHAT_MEMBER'] ?></a>
                            </label></div>
                    </div>
                </div>
                <div class="form-group">
                    <label
                            class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SENDTYPE'] ?></label>
                    <div class="col-md-6">
                        <div class="mt-5"><label>
                                <span> <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_INTERNET'] ?></span>
                            </label></div>
                        <div><span
                                    class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SENDTYPE_TIPS'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label
                            class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?></label>
                    <div class="col-md-2">
                        <input type="checkbox" id="systemchecks2" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>
                <div class="form-group systemchecks2div">
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
                        <input type="checkbox" id="taskchecks2" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>
                <div class="form-group taskchecks2div">
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
                        class="control-label col-md-3 lh30 textalignr"><?php echo $LANG['UI_SETTINGS_NOTICE_TIME'] ?></label>
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
                <button type="button" id="wechatcancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <?php
                if (in_array("p_wechat_notice", $_SESSION['permissionArr'])) {
                    echo '<button type="button" id="wechatsubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] .'</button>';
                }
                ?>
            </div>
        </div>
    </div>
</form>

<!-- BEGIN WECHAT MODAL -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="current_pending_task_drawer2" style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header" style="height: 52px;">
            <h4 class="drawer-title" id="settings-drawer-title" style="font-size: 16px;margin-top: 0px;">
                <i class="levelchild viconfont vicon-gaojipeizhi"></i>
                <button type="button" class="close" data-dismiss="drawer" aria-hidden="true" style="transform: scale(0.66);"></button>
                <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_WECHAT_HOST'] ?></h4>
        </div>
        <div class="drawer-body" style="padding-block: 0px; padding-inline: 0px;">
            <form action="#" id="testwechatform" class="form-horizontal">
                <div class="form-body" style="height: 80%;">

                    <div class="form-group" style="margin-bottom: 8px;">
                        <label class="control-label col-md-3" style="padding-right: 5px;"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control select2me" name="wechat_mode" style="width: 400px;">
                                <option value="1"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT'] ?></option>
                                <option value="2"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_PERSON'] ?></option>
                            </select>
                            <div><span class="help-block ">
                                <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_TIPS'] ?>
                            </span></div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type" style="margin-bottom: 8px;">
                        <label class="col-md-3 control-label" style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_GH_ID'] ?></label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="20" name="wechat_gh_id">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_GH_ID_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type">
                        <label class="col-md-3 control-label" style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_APPID'] ?></label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="18" name="wechat_appid">
                            </div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type">
                        <label
                                class="col-md-3 control-label" style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_APPSECRET'] ?></label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="32" name="wechat_appsecret">
                            </div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type" style="margin-bottom: 8px;">
                        <label
                                class="col-md-3 control-label"style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_ID'] ?></label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="128" name="wechat_template_id">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_ID_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type" style="margin-bottom: 7px;">
                        <label
                                class="col-md-3 control-label" style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_PARAM1'] ?></label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="128" name="wechat_template_param1">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_PARAM1_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type" style="margin-bottom: 8px;">
                        <label
                                class="col-md-3 control-label"style="padding-right: 5px;"><span class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_PARAM2'] ?></label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="128" name="wechat_template_param2">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_PARAM2_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type2" style="margin-bottom: 10px;">
                        <label
                                class="col-md-3 control-label" style="padding-right: 5px;"><span
                                    class="required"> * </span><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_URL'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right ">
                                <i class="fa"></i>
                                <input type="text" class="form-control" maxlength="128" placeholder=""
                                       name="wechat_template_url" style="width: 400px;">
                                <div><span
                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEMPLATE_URL_TIP'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="form-group show_wechat_type2" style="margin-bottom: 0px;">
                        <div class="col-md-3"></div>
                        <div class="col-md-4" style="width: 190px;">
                            <div><img style="height: 160px;width: 160px" id="wechat_qrcode" src=""></div>
                            <div><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_QRCODE_TIP'] ?></div>
                        </div>
                        <div class="col-md-4" style="margin-left: -17px;">
                            <div><img style="height: 160px;width: 160px" id="auth_qrcode" src=""></div>
                            <div><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_QRCODE_TIP2'] ?></div>
                        </div>
                        <div class="col-md-3"></div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-offset-3 col-md-8">
                            <button disabled type="button" class="btn btn-default textalignr disabled"
                                    id="sendtestwechat" style="margin-top: 12px;margin-bottom: 24px;"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_TEST']; ?></button>
                        </div>
                    </div>

                    <div class="form-group show_wechat_type2">
                    </div>


                </div>
            </form>
            <div class="col-md-offset-3 col-md-8" style="margin-left: 5px; bottom: 62px;width: 776px;padding-right: 14px;position: fixed;">
                <div class="alert alert-block alert-info fade in" id="tabletips" style="width: 762px;margin-left: -1px;bottom: 20px;margin-block: 0px;">
                    <button id="cencel3" type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading" style="font-size: 16px;color: #5F6D6B"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class="alert-ol" style="font-size: 12px;">
                        <li>
                            <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SEND_TIP1'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SEND_TIP2'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_SEND_TIP3'] ?>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #F1F3F5;">
            <div id="bottom-btn">
                <input type="hidden" id="wechat_type" value="<?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT'] ?>">
                <input type="hidden" id="wechat_type2" value="<?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_PERSON'] ?>">
                <button type="button" data-dismiss="drawer" class="btn btn-default" ><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
                <button type="button" class="btn btn-primary" id="testwechatsubmit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
    </div>
</div>
<!-- END WECHAT MODAL -->

<!-- BEGIN WECHAT_MEMBER MODAL -->
<div id="testwechatmemberdiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="levelchild viconfont vicon-pt_setting_wechat_notice"></i>
            <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_WECHAT_MEMBER'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">
            <div class="form-group alldirdiv">
                <label class="col-md-1 control-label"></label>
                <div class="col-md-12">
                    <!-- 取消授权 -->
                    <div class="btn-group">
                        <button type="button" id="authClientRemove" class="btn btn-sm green-haze">
                            <i class="glyphicon glyphicon-remove"></i> <?php echo $LANG['UI_VCENTER_AUTH_DELETE'] ?>
                        </button>
                    </div>

                    <div class="table-container">
                        <table id="memberIpDatatable">
                        </table>
                        <div class="alert alert-block alert-info fade in" id="marktips" style="margin-top:20px;">
                            <button type="button" class="close" data-dismiss="alert"></button>
                            <ul class="alert-ul">
                                <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                <li>
                                    <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MEMBER_TIPS'] ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
    </div>
</div>
<!-- END WECHAT_MEMBER MODAL -->

<script type="text/javascript" src="./scripts/platform/settings/settingstab/wechat_notice.js"></script>