<?php include_once '../../../../tpl/permission.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/jquery-editable-select/jquery-editable-select.min.css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_SET_TIME'] ?></span>
</h3>
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki" id=''>
            <div class="portlet-title">
                <div class="caption">
                    <i class="iconfont icon-shezhishijian"></i><?php echo $LANG['UI_PLATFORM_SET_TIME'] ?>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form action="#" id="settimeform" class="form-horizontal">
                    <div class="form-body-wrapper">
                        <div class="form-body form-body wp-50 hp-100 overflow-visible width80p_en">
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_SETTINGS_TIME_CITY'] ?>
                                </label>
                                <div class="col-md-6">
                                    <select class="form-control select2me" name="cityinput">
                                    </select>
                                    <div><span class="help-block ">
											<?php echo $LANG['UI_SETTINGS_TIME_CITY_TIP'] ?>
										</span></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_SETTINGS_TIME_SET'] ?>
                                </label>
                                <div class="col-md-6">
                                    <div class="input-group date form_datetime">
                                        <input type="text" size="16" name="timeinput" class="form-control">
                                        <span class="input-group-btn">
											<button class="btn default date-set" type="button"><i
                                                        class="viconfont vicon-ge_calendar"></i></button>
										</span>
                                    </div>
                                    <div><span class="help-block ">
											<?php echo $LANG['UI_SETTINGS_TIME_SET_TIP'] ?>
										</span></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_SETTINGS_TIME_NTP'] ?>
                                </label>
                                <div class="col-md-6">
                                    <input type="checkbox" id="ntpcheck" data-size="small" class="make-switch"
                                           data-on-color="primary" data-off-color="info"
                                           data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                           data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                    <div><span class="help-block ">
											<?php echo $LANG['UI_SETTINGS_TIME_NTP_TIPS'] ?>
										</span></div>
                                </div>
                            </div>

                            <div class="form-group display-none" id="ntphostdiv">
                                <label class="control-label col-md-3">
                                    <span class="required">* </span>
                                    <?php echo $LANG['UI_SETTINGS_TIME_NTP_HOST'] ?>
                                </label>

                                <div class="col-md-6">
                                    <div class="input-group" style="width: 100%;">
                                        <select class="form-control select2me" name="ntphost" id="ntphost">
                                        </select>
                                        <span class="input-group-btn">
                					         <button type="button" class="btn btn-default" id="ntpupdate"><?php echo $LANG['UI_SETTINGS_TIME_NTP_SYNC'] ?></button>
                					    </span>
                                    </div>
                                    <div>
                                        <span class="help-block"><?php echo $LANG['UI_SETTINGS_TIME_NTP_HOST_TIPS'] ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-md-3">
                                </label>
                                <div class="col-md-6">
                                    <div class="alert alert-block alert-info fade in" id="marktips">
                                        <button type="button" class="close" data-dismiss="alert"></button>
                                        <ul class="alert-ul">
                                            <strong
                                                    class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS'] ?>:</strong>
                                            <li>
                                                <?php echo $LANG['UI_SETTINGS_TIME_SYNC_TIPS'] ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions flex-items-center justify-content-center">
                        <div class="wp-50">
                            <label class="control-label col-md-3"></label>
                            <div class="col-md-6">
                                <button type="button" id="timecancel"
                                        class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                <?php
                                if (in_array("p_setting_manager_time", $_SESSION['permissionArr'])) {
                                    echo '<button type="button" id="timesubmit" class="btn green-haze btn-confirm">' . $LANG['UI_PUBLIC_YES'] .'</button>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
    </div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-editable-select/jquery-editable-select.min.js">
</script>
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
<script src="./assets/global/plugins/jsencrypt/jsencrypt.min.js" type="text/javascript"></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/set_time.js"></script>
<!-- END PAGE LEVEL PLUGINS -->