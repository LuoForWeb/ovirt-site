<?php include_once '../../../../tpl/permission.php';
include_once '../../../../content/platform/public/bs_table.php';
?>
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
    type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" /> -->
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_SYSTEM_NOTICE'] ?></span>
</h3>
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('emial_notice', 'sms_notice', 'wechat_notice', 'enterprise_wechat_notice');
$activeClassArr = array();  //active类
$displayArr = array();      //是否显示

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

// var_dump($activeClassArr, $displayArr);

?>
<!-- BEGIN FORM-->
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <ul class="nav nav-tabs floatl">
                    <li class="<?php echo $activeClassArr['emial_notice'] ?> <?php echo $displayArr['emial_notice'] ?>">
                        <a href="#emailtab" data-toggle="tab" aria-expanded="false">
                            <i class="levelchild viconfont vicon-pt_setting_email_notification"></i>
                            <?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL'] ?> </a>
                    </li>
                    <li class="<?php echo $activeClassArr['sms_notice'] ?> <?php echo $displayArr['sms_notice'] ?>"
                        id="smsDiv">
                        <a href="#smstab" data-toggle="tab" aria-expanded="false">
                            <i class="levelchild viconfont vicon-pt_setting_short_note"></i>
                            <?php echo $LANG['UI_SETTINGS_NOTICE_SMS'] ?> </a>
                    </li>
                    <li class="<?php echo $activeClassArr['wechat_notice'] ?> <?php echo $displayArr['wechat_notice'] ?>"
                        id="wechatDiv">
                        <a href="#wechattab" data-toggle="tab" aria-expanded="false">
                            <i class="levelchild viconfont vicon-pt_setting_wechat_notice"></i>
                            <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT'] ?> </a>
                    </li>
                    <li class="<?php echo $activeClassArr['enterprise_wechat_notice'] ?> <?php echo $displayArr['enterprise_wechat_notice'] ?>"
                        id="wechatDiv2">
                        <a href="#wechattab2" data-toggle="tab" aria-expanded="false">
                            <i class="levelchild viconfont vicon-pt_setting_wechat2_notice"></i>
                            <?php echo $LANG['UI_SETTINGS_NOTICE_WECHAT_INTERNET2'] ?> </a>
                    </li>
                </ul>
            </div>
            <div class="portlet-body form">
                <div class="tab-content">
                    <div class="tab-pane <?php echo $activeClassArr['emial_notice'] ?>" id="emailtab">
                        <?php
                        if (empty($displayArr['emial_notice'])) {
                            // 显示才加载
                            include_once './email_notice.php';
                        }
                        ?>
                    </div>
                    <div class="tab-pane <?php echo $activeClassArr['sms_notice'] ?>" id="smstab">
                        <?php
                        if (empty($displayArr['sms_notice'])) {
                            // 显示才加载
                            include_once './sms_notice.php';
                        }
                        ?>
                    </div>
                    <div class="tab-pane <?php echo $activeClassArr['wechat_notice'] ?>" id="wechattab">
                        <?php
                        if (empty($displayArr['wechat_notice'])) {
                            // 显示才加载
                            include_once './wechat_notice.php';
                        }
                        ?>
                    </div>
                    <div class="tab-pane <?php echo $activeClassArr['enterprise_wechat_notice'] ?>" id="wechattab2">
                        <?php
                        if (empty($displayArr['enterprise_wechat_notice'])) {
                            // 显示才加载
                            include_once './enterprise_wechat_notice.php';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- END FORM-->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js">
</script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript"
    src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js">
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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/libs/base64.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<!-- END PAGE LEVEL PLUGINS -->