<?php include_once '../../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>
    <span>></span>
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/settingstab/system_br.php">
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_BAK_REC'] ?></span>
        </a>
    </li>
    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_RC_AUTOBAK'] ?></span>
</h3>
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('rc_autobak_setting', 'rc_autobak_list');
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
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <ul class="nav nav-tabs floatl">
                    <li class="<?php echo $activeClassArr['rc_autobak_setting'] ?> <?php echo $displayArr['rc_autobak_setting'] ?>">
                        <a href="#ipdiv" data-toggle="tab">
                            <i class="iconfont icon-baksetting"></i>
                            <?php echo $LANG['UI_PLATFORM_RC_AUTOBAK_SETTING'] ?> </a>
                    </li>
                    <?php
                    if (in_array("p_rc_autobak_list_list", $_SESSION['permissionArr'])) {
                        echo '<li class="' . $activeClassArr['rc_autobak_list'] . ' ' . $displayArr['rc_autobak_list'] . '">
                            <a href="#dnsdiv" data-toggle="tab"><i class="iconfont icon-sysbakpoint"></i>' . $LANG['UI_PLATFORM_RC_AUTOBAK_LIST'] . '</a>
                        </li>';
                    }
                    ?>
                </ul>
            </div>
            <div class="portlet-body form">
                <div class="tab-content row margin0">
                    <div class="tab-pane <?php echo $activeClassArr['rc_autobak_setting'] ?>" id="ipdiv">
                        <?php include_once './system_br_autobak_setting.php'; ?>
                    </div>
                    <?php
                    if (in_array("p_rc_autobak_list_list", $_SESSION['permissionArr'])) {
                        echo '<div class="tab-pane ' . $activeClassArr["rc_autobak_list"] . '" id="dnsdiv">';
                        include_once "./system_br_autobak_list.php";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- END FORM-->
    </div>
</div>
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>

<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript"
    src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js">
</script>
<script type="text/javascript"
    src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/libs/base64.min.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->