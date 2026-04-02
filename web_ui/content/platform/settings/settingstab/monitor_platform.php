<?php include_once '../../../../tpl/permission.php';?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php" >
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET']?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent">
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/message_push.php" >
            <span><?php echo $LANG['UI_PLATFORM_MESSAGE_PUSH'];?></span>
        </a>
    </span>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_THIRD_MONITOR_CONFIG'];?></span>
</h3>
<div class="row">
    <div class="col-md-12">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki" id='monitorPlatformDiv'>
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-a-Electrocardiogramxindiantu"></i><?php echo $LANG['UI_PLATFORM_THIRD_MONITOR_CONFIG'];?>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <div class="form-body">
                    <div class="table-container">
                        <div class="vin_toolbar" id="vin_monitor_platform_toolbar">
                            <div class="leftTool">
                            </div>

                            <div class="rightTool">
                                <div class="vin_btnToolbar">
                                </div>
                            </div>
                        </div>
                        <table class="table table-hover" id="monitorPlatformTable">
                        </table>
                    </div>
                </div>
                <!-- END FORM-->
            </div>
        </div>

        <!-- BEGIN ADD DRAWER -->
        <div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
             aria-labelledby="drawer-1-title" aria-hidden="true" id="add_monitor_platform_drawer">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <h4 class="drawer-title" id="drawer-1-title">
                        <span id="drawer-1-title-1"><i class="viconfont vicon-danchuangtianjia1"></i></span>
                        <span style="vertical-align: top" id="" class="add_monitor_platform display-none">
                            <?php echo $LANG['WEB_BD_SYSTEMLOG_DESC_KEY_ADD_THIRD_MONITOR_PLATFORM'];?>
                        </span>
                        <span style="vertical-align: top" id="" class="edit_monitor_platform display-none">
                            <?php echo $LANG['WEB_BD_SYSTEMLOG_DESC_KEY_MOD_THIRD_MONITOR_PLATFORM'];?>
                        </span>
                        <span data-dismiss="drawer" aria-label="Close" class="drawer-close ">
                            <i class="viconfont vicon-guanbi"></i></span>
                    </h4>
                </div>
                <div class="drawer-body">
                    <!-- BEGIN FORM-->
                    <form action="#" id="form_sample_1" class="form-horizontal">
                        <select id="node_value" class="display-none" type="text">
                            <option value=""></option>
                        </select>
                        <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                            <label class="control-label col-md-4">
                                <?php echo $LANG['UI_VCENTER_RNAME'] ?>
                            </label>
                            <div class="col-md-7">
                                <input type="text" maxlength="64" id="monitorPlatformName" class="form-control" name="monitorPlatformName">
                            </div>
                        </div>
                        <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                            <label class="control-label col-md-4">
                                <?php echo $LANG['UI_NAS_AGREEMENT_TYPE'];?>
                            </label>
                            <div class="col-md-7">
                                <select class="form-control select2me" name="protocolType" id="protocolType">
                                    <option value="1"><?php echo $LANG['UI_PLATFORM_THIRD_MONITOR_AGREEMENT_TYPE1'];?></option>
<!--                                    <option value="2">--><?php //echo $LANG['UI_PLATFORM_THIRD_MONITOR_AGREEMENT_TYPE2'];?><!--</option>-->
                                </select>
                            </div>
                        </div>
                        <div class="form-group agreementDiv display-none" style="margin-top: 15px;margin-left: -100px;">
                            <label class="control-label col-md-4"><?php echo $LANG['UI_PLATFORM_THIRD_MONITOR_AGREEMENT'];?></label>
                            <div id="check_cycle" class="col-md-7 pt7 textalignl">
                                <label>
                                    <input type="radio" checked class="icheck-verify-cycle" name="icheckbox1" data-mode="1">TCP
                                </label>
                                <label>
                                    <input type="radio" class="icheck-verify-cycle" name="icheckbox1" data-mode="2">UDP
                                </label>
                            </div>
                        </div>
                        <div class="form-group ipDiv display-none" style="margin-top: 15px;margin-left: -100px;">
                            <label class="control-label col-md-4">
                                <?php echo $LANG['UI_PLATFORM_THIRD_MONITOR_TARGET_IP'];?>
                            </label>
                            <div class="col-md-7">
                                <input type="text" maxlength="64" id="targetIp" class="form-control" name="targetIp">
                            </div>
                        </div>
                        <div class="form-group testDiv display-none" style="margin-top: 15px;">
                            <label class="control-label col-md-3">
                            </label>
                            <button type="button" id="testTool" class="btn green-haze"><?php echo $LANG['UI_PLATFORM_THIRD_PUSH_IP_TEST']?></button>
                        </div>

                        <div class="form-group portDiv display-none" style="margin-top: 15px;margin-left: -100px;">
                            <label class="control-label col-md-4">
                                <?php echo $LANG['UI_PLATFORM_THIRD_MONITOR_PORT'];?>
                            </label>
                            <div class="col-md-7">
                                <input type="text" maxlength="64" id="port" class="form-control" name="port" value="514">
                            </div>
                        </div>
                        <div class="form-group urlDiv display-none" style="margin-top: 15px;margin-left: -100px;">
                            <label class="control-label col-md-4">
                                URL
                            </label>
                            <div class="col-md-7">
                                <input type="text" maxlength="64" id="url" class="form-control" name="url">
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
                                <button type="button" id="add_submit" class="btn green-haze btn-confirm display-none">
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
<?php
if($_SESSION['language'] != "en-us"){
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/monitor_platform.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
