<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->

<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
    type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
    type="text/css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css"
    href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<style>
    .bootstrap-mutiple-select{
        width: 100%!important;
    }
</style>
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('storage_manager_list', 'storage_pool');
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
<!-- BEGIN PAGE HEADER -->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="storage_manager" href="./content/platform/storage/storagedevice.php">
            <span><?php echo $LANG['UI_PLATFORM_STORAGE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_STORAGE_BACKUP'] ?></span>
</h3>
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE CONTENT -->
<div class="resource-manager-wrap">
    <div class="resource-manager-wrap__header">
        <ul class="nav nav-tabs">
            <li class = "<?php echo $activeClassArr['storage_manager_list'] ?> <?php echo $displayArr['storage_manager_list'] ?>" id="storage_li">
                <a href="#storage_list_div" data-toggle="tab">
                    <i class="levelchild viconfont vicon-beifencunchu"></i>
                    <?php echo $LANG['UI_STORAGE_LIST'] ?>
                </a>
            </li>
            <li class="<?php echo $activeClassArr['storage_pool'] ?> <?php echo $displayArr['storage_pool'] ?>" id="storagePoolLi">
                <a href="#storagePoolDiv" data-toggle="tab">
                    <i class="levelchild viconfont vicon-cunchuziyuanchi"></i>
                    <?php echo $LANG['UI_STORAGE_POOL'] ?>
                </a>
            </li>
        </ul>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="tab-content">
            <div class="tab-pane <?php echo $activeClassArr['storage_manager_list'] ?>" id="storage_list_div">
                <?php
                if (empty($displayArr['storage_manager_list'])) {
                    // 显示才加载
                    include_once './storage_manager.php';
                }
                ?>
            </div>
            <div class="tab-pane <?php echo $activeClassArr['storage_pool'] ?>" id="storagePoolDiv" >
                 <?php
                    if (in_array('storage_pool', $_SESSION['permission'])) {
                        include_once './storage_pool.php';
                    }
                    ?>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/fuelux/js/spinner.min.js"></script>
<script src="./scripts/libs/base64.js" type="text/javascript"></script>
<script type="text/javascript"
    src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<!-- END PAGE LEVEL PLUGINS -->