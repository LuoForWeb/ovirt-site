<?php include_once '../../../../tpl/permission.php'; ?>
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>

    <span>></span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_SAFE_SETTING'] ?></span>
</h3>
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('account_safe', 'storage_safe', 'os_safe', 'data_safe');
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

?>
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <ul class="nav nav-tabs floatl">
                    <li class="<?php echo $activeClassArr['account_safe'] ?> <?php echo $displayArr['account_safe'] ?>">
                        <a href="#ipdiv" data-toggle="tab">
                            <i class="iconfont icon-accountsafe"></i> <?php echo $LANG['UI_PLATFORM_ACCOUNT_SAFE'] ?>
                        </a>
                    </li>
                    <li class="<?php echo $activeClassArr['storage_safe'] ?> <?php echo $displayArr['storage_safe'] ?>">
                        <a href="#dnsdiv" data-toggle="tab">
                            <i class="iconfont icon-storagesafe"></i> <?php echo $LANG['UI_PLATFORM_STORAGE_SAFE'] ?>
                        </a>
                    </li>
                    <li class="<?php echo $activeClassArr['os_safe'] ?> <?php echo $displayArr['os_safe'] ?>">
                        <a href="#osdiv" data-toggle="tab">
                            <i class="iconfont icon-ossafe"></i> <?php echo $LANG['UI_PLATFORM_OS_SAFE'] ?> </a>
                    </li>
                    <li class="<?php echo $activeClassArr['data_safe'] ?> <?php echo $displayArr['data_safe'] ?>">
                        <a href="#datadiv" data-toggle="tab">
                            <i class="viconfont vicon-ge_log"></i> <?php echo $LANG['UI_PLATFORM_DATA_SAFE']; ?> </a>
                    </li>
                </ul>
            </div>
            <div class="portlet-body form">
                <div class="tab-content">
                    <div class="tab-pane <?php echo $activeClassArr['account_safe'] ?>" id="ipdiv">
                        <?php
                        if (empty($displayArr['account_safe'])) {
                            // 显示才加载
                            include_once './account_safe.php';
                        }
                        ?>
                    </div>

                    <div class="tab-pane <?php echo $activeClassArr['storage_safe'] ?>" id="dnsdiv">
                        <?php
                        if (empty($displayArr['storage_safe'])) {
                            // 显示才加载
                            include_once './storage_safe.php';
                        }
                        ?>
                    </div>

                    <div class="tab-pane <?php echo $activeClassArr['os_safe'] ?>" id="osdiv">
                        <?php
                        if (empty($displayArr['os_safe'])) {
                            // 显示才加载
                            include_once './os_safe.php';
                        }
                        ?>
                    </div>
                    <div class="tab-pane <?php echo $activeClassArr['data_safe'] ?>" id="datadiv">
                        <?php
                        if (empty($displayArr['data_safe'])) {
                            // 显示才加载
                            include_once './data_safe.php';
                        }
                        ?>
                    </div>

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