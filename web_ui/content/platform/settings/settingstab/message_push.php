<?php include_once '../../../../tpl/permission.php'; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php" >
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH'] ?></span>
</h3>
<?php
$userAllPermission = $_SESSION['permission'];
$tabNameArr = ['message_push_third', 'message_push_old'];
$activeClassArr = array();
$setFlag = false;
foreach ($tabNameArr as $key => $value) {
    if (in_array($value, $userAllPermission)) {
        //如果有权限
        $displayArr[$value] = "";
        if (!$setFlag) {
            $activeClassArr[$value] = " active ";
            $setFlag = true;
        } else {
            $activeClassArr[$value] = "";
        }
    } else {
        //如果没有权限
        $activeClassArr[$value] = "";
        $displayArr[$value] = "displaynone";
    }
}
?>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki" id='pushMangerDiv'>
            <div class="portlet-title">
                <ul class="nav nav-tabs floatl" id="updateUL">
                    <li class="<?php echo $activeClassArr['message_push_third']; ?>">
                        <a href="#thirdpush_tab" data-toggle="tab" aria-expanded="false">
                            <i class="levelchild viconfont vicon-disanfangxiaoxituisong"></i> <?php echo $LANG['UI_PLATFORM_THIRD_MESSAGE_PUSH']; ?> </a>
                    </li>
                    <li class="<?php echo $activeClassArr['message_push_old']; ?>">
                        <a href="#messagepushform" data-toggle="tab" aria-expanded="false">
                            <i class="levelchild viconfont vicon-xiaoxituisong"></i> <?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH']; ?></a>
                    </li>
                </ul>
            </div>
            <div class="portlet-body form">
                <div class="tab-content">
                    <!-- BEGIN FORM-->
                    <form action="#" id="messagepushform" class="tab-pane form-horizontal mh400 <?php echo $activeClassArr['message_push_old']; ?>">
                        <div class="form-body mb210">
                            <div class="form-group pt50">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH_CONFIG'] ?><span class="required">
                            * </span>
                                </label>
                                <div class="col-md-4">
                                    <input type="checkbox" id="pushCheck"  class="make-switch" data-on-color="primary" data-off-color="info"
                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                </div>
                            </div>
                            <div class="form-group display-hide pushChild">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH_MIDDLEWARE_TYPE'] ?> <span class="required">
                            * </span>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control" id="pushType">
                                        <option value="1">ActiveMQ</option>
                                        <!--<option value="2">RebbitMQ</option> -->
                                    </select>
                                </div>
                            </div>

                            <div class="form-group display-hide pushChild">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH_MIDDLEWARE_PROTOCOL'] ?> <span class="required">
                            * </span>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control" id="protocol">
                                        <option value="1">Stomp</option>
                                        <option value="2">OpenWire</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group display-hide pushChild">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH_MIDDLEWARE_TRANSMODE'] ?> <span class="required">
                            * </span>
                                </label>
                                <div class="col-md-4">
                                    <select class="form-control" id="pushMode">
                                        <option value="1">Queue</option>
                                        <option value="2">Topic</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group display-hide pushChild">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH_MIDDLEWARE_DOMAIN'] ?> <span class="required">
                            * </span>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="128" class="form-control" name="pushDomain"/>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group display-hide pushChild">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH_MIDDLEWARE_PORT'] ?> <span class="required">
                            * </span>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="128" class="form-control" value="61613" name="pushPort"/>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group display-hide pushChild">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH_USERNAME'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="text" maxlength="128" class="form-control" name="pushName"/>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group display-hide pushChild">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_LOGIN_PASSWORD'] ?>
                                </label>
                                <div class="col-md-4">
                                    <div class="input-icon right">
                                        <i class="fa"></i>
                                        <input type="password" autocomplete="off" maxlength="128" class="form-control" name="pushPassword" oninput="value=value.replace(/[\u4E00-\u9FA5]|[\uFE30-\uFFA0]|\s+/g,'')"/>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="form-actions pt50">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-4">
                                    <button type="button" id="messageCancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                    <?php
                                    if (in_array("p_message_push_old_operate", $_SESSION['permissionArr'])) {
                                        echo '<button type="button" id="messageSubmit" class="btn green-haze">' . $LANG['UI_PUBLIC_YES'] . '</button>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- END FORM-->

                    <!-- BEGIN TABLE-->
                    <div class="tab-pane <?php echo $activeClassArr['message_push_third']; ?>" id="thirdpush_tab">
                        <div class="portlet-body pding20" id="thirdpush">
                            <div class="table-container">
                                <div class="vin_toolbar" id="vin_third_push_toolbar">
                                    <div class="leftTool">
                                    </div>

                                    <div class="rightTool">
                                        <div class="vin_btnToolbar">
                                        </div>
                                    </div>
                                </div>
                                <table class="table table-hover" id="thirdpushTable">
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- END TABLE-->
                </div>
            </div>
        </div>
        <!-- END TAB PORTLET-->

        <!-- BEGIN ADD DRAWER -->
        <div style="width: 45%;" class="drawer slide col-md-6_en" data-placement="right" tabindex="-1" role="dialog"
             aria-labelledby="drawer-1-title" aria-hidden="true" id="add_third_strategy_drawer">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <h4 class="drawer-title" id="drawer-1-title">

                        <div class="add_push_title display-none">
                            <span id="drawer-1-title-1"><i class="viconfont vicon-danchuangtianjia1"></i></span>
                            <span style="vertical-align: top" id="">
                            <?php echo $LANG['UI_PLATFORM_THIRD_MESSAGE_ADD']; ?>
                            </span>
                        </div>

                        <div class="edit_push_title display-none">
                            <span id="drawer-1-title-1"><i class="viconfont vicon-a-Editbianji1"></i></span>
                            <span style="vertical-align: top" id="" class="">
                                <?php echo $LANG['UI_PLATFORM_THIRD_MESSAGE_EDIT']; ?>
                            </span>
                        </div>

                        <div class="view_push_title display-none">
                            <span id="drawer-1-title-1"><i class="viconfont vicon-a-Eyesyanjing"></i></span>
                            <span style="vertical-align: top" id="" class="">
                            <?php echo $LANG['BILLING_VIEW_DETAILS']; ?>
                            </span>
                        </div>

                        <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                                    class="viconfont vicon-guanbi"></i></span>
                    </h4>
                </div>
                <div class="drawer-body">
                    <!-- BEGIN FORM-->
                    <form action="#" id="form_sample_1" class="form-horizontal">
                        <div class="form-body">
                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                    <?php echo $LANG['UI_VCENTER_RNAME'] ?>
                                </label>
                                <div class="col-md-8">
                                    <input type="text" maxlength="64" id="strategyName" class="form-control" name="strategyName">
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                    <?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TYPE'] ?>
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" name="strategy_pushType" id="strategy_pushType">
                                        <option value="1"><?php echo $LANG['UI_PLATFORM_THIRD_TASK_ALARM']; ?></option>
                                        <option value="2"><?php echo $LANG['UI_PLATFORM_THIRD_SYSTEM_ALARM']; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                    <?php echo $LANG['UI_PLATFORM_THIRD_MONITOR_PLATFORM'] ?>
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" name="monitorPlatform" id="monitorPlatform">
                                    </select>
                                    <div>
                                        <span class="selectedLabel"><?php echo $LANG['UI_PLATFORM_THIRD_SELECT']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                    <?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE'] ?>
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" name="pushTimeType" id="pushTimeType">
                                        <option value="1"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE1']; ?></option>
                                        <option value="2"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE2']; ?></option>
                                        <option value="3"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE3']; ?></option>
                                        <option value="4"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_TIME_TYPE4']; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group lastPushTimeDiv " style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                </label>
                                <div class="spinner-group col-md-8 lastPushTimeSpinnerDiv">
                                    <input type="text" id="lastPushTime" name="lastPushTime" onkeyup="value=value.replace(/[^\d]/g,'')" class="spinner-input form-control">
                                    <div class="spinner-buttons input-group-btn spinner-group-btn">
                                        <button type="button" class="btn spinner-up default">
                                            <i class="fa fa-angle-up"></i>
                                        </button>
                                        <button type="button" class="btn spinner-down default">
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group col-md-2 mt10"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_DAYS']; ?></div>
                            </div>
                            <div class="form-group timePickerDiv display-none" style="margin-top: 15px;margin-left: -100px;">
                                <!--                                时间控件-->
                                <label class="control-label col-md-3 col-md-4_en">
                                </label>
                                <div class="col-md-8 form_datetimeDiv">
                                    <div class="input-group date form_datetime">
                                        <input type="text" size="16"  id="add_lastPushTime" name="timeinput" class="form-control w-300px">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set" type="button"><i class="viconfont vicon-ge_calendar"></i></button>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="help-block ">
                                            <?php echo $LANG['UI_SETTINGS_TIME_SET_TIP'] ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group task_Div" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                    <?php echo $LANG['UI_PLATFORM_THIRD_PUSH_ASSOCIATE_TASK'] ?>
                                </label>
                                <div class="col-md-8">
                                    <select id="taskList" class="bootstrap-mutiple-select selectpicker show-tick select2me" name="taskList" multiple data-live-search="true" data-actions-box="true"></select>
                                    <span id="taskListErrorTips" class="display-none font-danger"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_SELECT_TASKS']; ?></span>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                    <?php echo $LANG['UI_PLATFORM_THIRD_PUSH_CONTENT'] ?>
                                </label>
                                <div class="col-md-8">
                                    <select class="form-control select2me" name="pushContent" id="pushContent">
                                        <option value="1"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_CONTENT1']; ?></option>
                                        <option value="2"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_CONTENT2']; ?></option>
                                    </select>
                                    <div>
                                        <span class="selectedLabel"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_CONTENT_TIP']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <!--  推送内容模板 -->
                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                </label>
                                <div class="col-md-8">
                                    <div class="pushTemplate_div">
                                        <ul id="pushTemplate" class="pd0">
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group addTemplate_div display-none" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                </label>
                                <div class="col-md-4 mt5">
                                    <button type="button" id="add_template_btn" class="btn green-haze"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_ADD_FIELD']; ?></button>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                    <?php echo $LANG['UI_PLATFORM_THIRD_PUSH_ALARM_AUTO_PUSH_FLAG'] ?>
                                </label>
                                <div class="col-md-4 mt5">
                                    <input type="checkbox" id="autoAlarmPush" class="make-switch"
                                           data-on-color="primary" data-off-color="info"
                                           data-size="small"
                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                       data-placement="right"
                                       data-content="<?php echo $LANG['UI_PLATFORM_THIRD_PUSH_AUTO_ALARM_PUSH']; ?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>

                            </div>
                            <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                                <label class="control-label col-md-3 col-md-4_en">
                                    <?php echo $LANG['UI_PLATFORM_THIRD_PUSH_RESPONSE_AUTO_PUSH_FLAG'] ?>
                                </label>
                                <div class="col-md-4 mt5">
                                    <input type="checkbox" id="autoResponsePush" class="make-switch"
                                           data-on-color="primary" data-off-color="info"
                                           data-size="small"
                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    <a class="popovers ml15" data-container="body" data-trigger="hover"
                                       data-placement="right"
                                       data-content="<?php echo $LANG['UI_PLATFORM_THIRD_PUSH_AUTO_RESPONSE_PUSH']; ?>">
                                        <i class="viconfont vicon-tishi"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    <!-- END FORM-->
                </div>
                <!-- footer -->
                <div class="drawer-footer">
                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-6" style="float: right;padding-right: 10px;">
                                <button type="button" id="add_submit" class="btn green-haze btn-confirm">
                                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                                </button>
                                <button type="button" id="edit_submit" class="btn green-haze btn-confirm display-none">
                                    <?php echo $LANG['UI_PUBLIC_YES'] ?>
                                </button>
                                <button type="button" id="" class="btn default cancel">
                                    <?php echo $LANG['UI_PUBLIC_NO'] ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END ADD DRAWER -->
    </div>
</div>

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/sortable/Sortable.js"></script>
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
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/message_push.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	
