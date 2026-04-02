<style>
    #smsDelete:hover{
        background: #CFEDE6!important;
    }
    #smsConfirm:hover{
        background: #CFEDE6!important;
    }
    #sendmodemsms:hover{
        background-color: #CFEDE6!important;
    }
    /* 启用状态：已配置，显示绿色 */
    .enabled {
        background-color:#E7F7F3;
        color: #0FBF98;
    }

    /* 禁用状态：未配置，显示灰色 */
    .disabled {
        background-color: #F0F0F0 ;
        color: #CCCCCC;
    }
    #sendtestsms {
        color: #0FBF98;
        background: #E7F7F3;
    }
    #testrecphone {
        display: none;
        font-size: 12px !important;
        padding-top: 4px;
    }

    #smsconfig {
        font-size: 12px !important;
        padding-top: 4px;
        color: #0FBF98;
        cursor: pointer;
    }
    #xiugai {
        width: 16px;
        height: 16px;
        display: none;
        color: #0FBF98;
        cursor: pointer;
    }

    #smsquantity{
        margin-left: 3px;text-align: left;padding-left: 12px;
    }
    #smsconfig2 {
        color: #666666;
        font-size: 12px !important;
        padding-top: 4px;
        color: #999999;
        display: none;
    }
    #smsDelete {
        position: relative;
        left: 595px;
        top: -35px;
        background: #E7F7F3;
        color: #0FBF98;
    }
    #smsConfirm {
        position: relative;
        left: 637px;
        top: -70px;
        background: #E7F7F3;
        color: #0FBF98;
    }
    #sendmodemsms {
        color: #0FBF98;
        background-color: #E7F7F3
    }
    #telephone {
        width: 361px;
        margin-left: 271px;
    }
    .alert-heading {
        font-size: 16px;
        color: #5F6D6B
    }

</style>

<form action="#" class="form-horizontal">
    <div class="form-body-wrapper">
        <div class="form-body wp-50 hp-100 overflow-visible width80p_en">
            <div class="form-group">
                <label
                        class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS'] ?></label>
                <div class="col-md-6 col-md-4_en">
                    <input type="checkbox" id="smscheck" checked class="make-switch" data-on-color="primary"
                           data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    <div><span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_POWER_ON_TIP'] ?></span>
                    </div>
                </div>
                <div class="col-md-3 col-md-4_en">
                    <span class="help-block lh30">
                        <a id="testsms"><?php echo $LANG['UI_SETTINGS_NOTICE_TEST_SMS'] ?></a>
                    </span>
                </div>
            </div>

            <div class="smscontent">
                <div class="form-group">
                    <label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_SENDTYPE'] ?></label>
                    <div class="col-md-6">
                        <div class="mt-5"><label id="smssendtypedes"><i class="fa fa-globe font-green-seagreen"></i>
                                <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_INTERNET'] ?></label></div>
                        <div>
                            <span class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_SENDTYPE_TIPS'] ?></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label
                            class="control-label col-md-3 form-group-top4-label"><?php echo $LANG['UI_PLATFORM_ALARM_SYSTEM'] ?></label>
                    <div class="col-md-2">
                        <input type="checkbox" id="systemchecks" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group systemchecksdiv">
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
                        <input type="checkbox" id="taskchecks" checked class="make-switch" data-on-color="primary"
                               data-size="small" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                               data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    </div>
                </div>

                <div class="form-group taskchecksdiv">
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
                    <div class="form-group telphonediv">
                        <label class="control-label col-md-3">
                            <?php echo $LANG['UI_SETTINGS_NOTICE_RECV_EMAIL'] ?>
                        </label>
                        <div class="col-md-9">
                        <textarea class="form-control" id="telephonelist" rows="4" placeholder="">
                        </textarea>
                            <div>
                            <span class="help-block">
                                <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIP1'] ?><br>
                                <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIP2'] ?>
                            </span>
                            </div>
                        </div>
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
                <button type="button" id="smscancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <?php
                if (in_array("p_sms_notice", $_SESSION['permissionArr'])) {
                    echo '<button type="button" id="smssubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] .'</button>';
                }
                ?>
            </div>
        </div>
    </div>
</form>





<!-- 抽屉组件 -->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true"
     id="current_pending_task_drawer" style="width: 800px">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header" style="height: 51px;">
            <h4 class="drawer-title" id="drawer-1-title" style="font-size: 16px; padding-left: 0px;">
                <i class="levelchild viconfont vicon-gaojipeizhi" style="width: 16px; height: 16px;">
                    <button type="button" class="close" data-dismiss="drawer" aria-hidden="true" style="transform: scale(0.66);"></button>
                </i>
                <?php echo $LANG['UI_SETTINGS_NOTICE_TEST_SMS'] ?>
            </h4>
        </div>
        <div class="drawer-body form-horizontal" style="padding: 0px; overflow: hidden;">

            <div class="portlet box blue-hoki">
                <div class="portlet-title" style="padding-bottom: 9px;width: 760px;margin: 0 auto;padding-left: 0px;padding-top: 8px;height: 56px;">
                    <ul class="nav nav-tabs floatl">
                        <li class="active">
                            <a href="#internettab" data-toggle="tab" aria-expanded="false">
                                <i class=""></i>
                                <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_INTERNET'] ?> </a>
                        </li>
                        <li class="" id="smsDiv">
                            <a href="#smsmodemtab" data-toggle="tab" aria-expanded="false">
                                <i class=""></i>
                                <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM'] ?> </a>
                        </li>

                    </ul>
                </div>
                <div class="portlet-body form">
                    <div class="tab-content">
                        <div class="tab-pane active" id="internettab">
                            <form action="#" id="internet" class="form-horizontal mh520">
                                <div class="form-body" style="height: calc(100% - 130px);">
                                    <div class="form-group">
                                        <label class="control-label col-md-4 pr5">
                                            <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE'];?>
                                        </label>
                                        <div class="col-md-6">
                                            <select class="form-control select2me" name="sms_mode" id="" style="width: 400px;">
                                                <option value="1"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_DEFAULT'];?></option>
                                                <option value="2"><?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_MODE_PERSON'];?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group show_sms_type2 display-none">
                                        <label class="control-label col-md-4 pr5">
                                            <span class="required">* </span>
                                            appid
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right" style="width: 400px;">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control" name="sms_appid" style="width: 400px"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group show_sms_type2 display-none">
                                        <label class="control-label col-md-4 pr5">
                                            <span class="required">* </span>
                                            appsecret
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right" style="width: 400px;">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control" name="sms_appsecret" style="width: 400px"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group show_sms_type2 display-none">
                                        <label class="control-label col-md-4 pr5">
                                            <span class="required">* </span>
                                            <?php echo $LANG['UI_SETTINGS_NOTICE_COMPANY_ID']?>
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right" style="width: 400px;">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control" name="company" style="width: 400px"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group show_sms_type2 display-none">
                                        <label class="control-label col-md-4 pr5">
                                            <span class="required">* </span>
                                            url
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right" style="width: 400px;">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control" name="url" style="width: 400px"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group show_sms_type1">
                                        <label class="col-md-4 control-label width30_en" style="text-align: right;">
                                            <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_QUANTITY'] ?>: </label>
                                        <label class="col-md-6 control-label" id="smsquantity"> </label>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-4" style="text-align: right; margin-left: 143px;">
                                            <button type="button" disabled class="btn btn-default textalignr disabled"
                                                    id="sendtestsms" ><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_SEND'] ?></button>
                                        </div>
                                        <div class="col-md-6" style="margin-left: -22px;">
                                            <label id="smsconfig"
                                                   class="control-label lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_CONTACT_PHONE'] ?></label>
                                            <label id="smsconfig2"
                                                   class="control-label lh30 textalignc"><?php echo $LANG['UI_SETTINGS_NOTICE_TO'] ?></label>
                                            <label class="control-label lh30 textalignc testrecphone" id="testrecphone">

                                            </label>
                                            <i id="xiugai" class="levelchild viconfont vicon-a-Editbianji1"></i>
                                        </div>
                                        <div id="smsconfig_div" class="display-none" style="margin-top: 51px;">
                                            <input type="text" placeholder="<?php echo $LANG['UI_SETTINGS_NOTICE_INPUT_PHONE'] ?>" id="telephone" maxlength="20" class="form-control" name="number" style="width: 316px !important;">
                                            <button class="btn viconfont vicon-a-Close-oneguanbi-copy b-btn brr2 mr12 green-haze" id="smsDelete" title=<?php echo $LANG['UI_PUBLIC_CANCEL'] ?>></button>
                                            <button class="btn viconfont vicon-Frame-15 b-btn brr2 mr12 green-haze" id="smsConfirm" title=<?php echo $LANG['UI_PUBLIC_CONFIRM'] ?>></button>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="col-md-12" style="position: fixed; bottom: 57px; margin-left: -18px; width: 800px;">
                                            <div class="alert alert-block alert-info fade in" id="step1tips">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                <ol class="alert-ol" style="font-size: 12px;">
                                                    <li>
                                                        <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS1'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS2'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS3'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_TIPS4'] ?>
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane" id="smsmodemtab">
                            <form action="#" id="setmodem" class="form-horizontal mh520">
                                <div class="form-body" style="height: calc(100% - 130px);">
                                    <div class="form-group">
                                        <label
                                                class="control-label col-md-4" style="padding-right: 5px;">
                                            <span class="required">
                                            * </span><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_IP'] ?>
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right" style="width: 400px;">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="128" class="form-control"
                                                       name="modemipaddr" style="width: 400px;"/>
                                                <div>
                                                    <span class="help-block "><?php echo $LANG['UI_SETTINGS_IP_TIP'] ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label
                                                class="control-label col-md-4" style="padding-right: 5px;">
                                            <span class="required">
                                            * </span><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_DATABASE'] ?>
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right"style="width: 400px;">
                                                <i class="fa"></i>
                                                <input style="display:none">
                                                <input type="text" maxlength="128" class="form-control"
                                                       name="modemdatabase" style="width: 400px;"/>
                                                <div><span
                                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_DATABASE_TIPS'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label
                                                class="control-label col-md-4" style="padding-right: 5px;">
                                            <span class="required">
                                            * </span><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_PORT'] ?>
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right"style="width: 400px;">
                                                <i class="fa"></i>
                                                <input style="display:none"><!-- for disable autocomplete on chrome -->
                                                <input type="text" maxlength="1280" class="form-control" name="modemport" style="width: 400px;"/>
                                                <div><span
                                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_PORT_TIPS'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label
                                                class="control-label col-md-4" style="padding-right: 5px;">
                                            <span class="required">
                                            * </span><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_USER'] ?>
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right "style="width: 400px;">
                                                <i class="fa"></i>
                                                <input type="text" maxlength="64" class="form-control"
                                                       name="modemusername" style="width: 400px;"/>
                                                <div><span
                                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_USER_TIPS'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label
                                                class="control-label col-md-4" style="padding-right: 5px;">
                                            <span class="required">
                                            * </span><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_PASS'] ?>
                                        </label>
                                        <div class="col-md-6">
                                            <div class="input-icon right "style="width: 400px;">
                                                <i class="fa"></i>
                                                <input type="password" autocomplete="off" maxlength="64"
                                                       class="form-control" name="modempassword"style="width: 400px;"
                                                       oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')" />
                                                <div><span
                                                            class="help-block "><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_PASS_TIPS'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-offset-4 col-md-8">
                                            <button type="button" class="btn btn-default textalignr"
                                                    id="sendmodemsms"><?php echo $LANG['UI_SETTINGS_NOTICE_SMS_SEND'] ?></button>

                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-md-12">
                                            <div class="alert alert-block alert-info fade in" id="step1tips"
                                                 style="position: fixed; bottom: 57px; margin-left: -18px; width: 763px;">
                                                <button type="button" class="close" data-dismiss="alert"></button>
                                                <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                                                <ol class="alert-ol" style="font-size: 12px;">
                                                    <li>
                                                        <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_TIPS1'] ?>
                                                    </li>
                                                    <li>
                                                        <?php echo $LANG['UI_SETTINGS_NOTICE_SMS_MODEM_TIPS2'] ?>
                                                    </li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <div class="drawer-footer">
        <div id="bottom-btn">
            <button type="button" data-dismiss="drawer"
                    aria-label="Close" class="btn btn-default" style="margin-right:0;"><?php echo $LANG['UI_PUBLIC_CLOSE'] ?></button>
        </div>
    </div>
</div>
</div>

<!-- END SMS MODAL -->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/sms_notice.js"></script>