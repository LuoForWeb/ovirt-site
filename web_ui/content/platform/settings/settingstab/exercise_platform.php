<?php include_once '../../../../tpl/permission.php'; ?>

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php" >
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_EXERCISE_PLATFORM'] ?></span>
</h3>

<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki" id=''>
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-ge_disaster_recovery"></i><?php echo $LANG['UI_EXERCISE_PLATFORM'] ?>
                </div>
            </div>
            <div class="portlet-body form position-relative">
                <!-- BEGIN FORM-->
                <form action="#" id="systemrecoveryform" class="form-horizontal mh520">
                    <div class="form-body-wrapper">
                        <div class="form-body wp-50 hp-100 overflow-visible width80p_en">
                            <div class="form-group">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_EXERCISE_PLATFORM_IP_OR_NAME'] ?> <span class="required">
                                * </span>
                                </label>
                                <div class="col-md-9">
                                    <div class="col-md-3" style="padding: 0 !important;">
                                        <select class="form-control " id="httpType">
                                            <option value="http://">http://</option>
                                            <option value="https://">https://</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" style="padding: 0 !important;">
                                        <div class="input-icon right">
                                            <i class="fa"></i>
                                            <input type="text" maxlength="128" class="form-control" name="ipaddr" placeholder="192.168.1.110"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="padding: 0 !important;">
                                        <button type="button" id="recoveryInto" class="btn green-haze"><?php echo $LANG['UI_EXERCISE_PLATFORM_INTO'] ?></button>
                                    </div>

                                    <div class="col-md-12" style="padding: 0 !important;">
                                        <span class="help-block iptips"><?php echo $LANG['UI_EXERCISE_PLATFORM_IP_OR_NAME_TIPS'] ?></span>
                                        <span class="help-block smartxtips display-none"><?php echo $LANG['UI_EXERCISE_PLATFORM_IP_OR_NAME_TIPS'] ?></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions flex-items-center justify-content-center">
                        <div class="wp-50">
                            <label class="control-label col-md-3"></label>
                            <div class="col-md-6">
                                <button type="button" id="recoverycancel" class="btn default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                <button type="button" id="recoverysubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
                <div class="alert alert-block alert-info fade in position-absolute" style="top: 90px;left: 33.5%;">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class = "alert-ol">
                        <li>
                            <?php echo $LANG['UI_EXERCISE_PLATFORM_TIPS1'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_EXERCISE_PLATFORM_TIPS2'] ?>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/settings/settingstab/exercise_platform.js"></script>

