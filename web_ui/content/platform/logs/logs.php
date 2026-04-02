<?php include_once '../../../tpl/permission.php'; ?>
<?php include_once '../../../content/platform/public/bs_table.php'; ?>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/datatables/extensions/Buttons/css/buttons.dataTables.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css" />
<!-- BEGIN PAGE HEADER-->
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('job_log', 'system_log', 'ha_log');
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

<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="vinchin-wrapper">
    <div class="vinchin-wrapper__tabs">
        <!-- BEGIN TAB PORTLET-->
        <input type="text" class="display-hide userLevel" value="<?php echo $_SESSION['userLevel']; ?>">
        <div class="nav-tabs-wrapper">
            <ul class="nav nav-tabs nav-line-tabs"  id='logs_nav'>
                <li class="<?php echo $activeClassArr['job_log'] ?> <?php echo $displayArr['job_log'] ?>  nav-item" data-type="task_log">
                    <a href="#joblogdiv" data-toggle="tab" class=" nav-link">
                        <i class="levelchild viconfont vicon-pt_log_task_operation_logs "></i> <?php echo $LANG['UI_LOG_TASK_LOG'] ?></a>
                </li>
                <li class="<?php echo $activeClassArr['system_log'] ?> <?php echo $displayArr['system_log'] ?>  nav-item" data-type="system_log">
                    <a href="#systemlogdiv" data-toggle="tab" class=" nav-link">
                        <i class="levelchild viconfont vicon-pt_log_system_operation_logs "></i> <?php echo $LANG['UI_LOG_SYSTEM_LOG'] ?> </a>
                </li>
                <li class="<?php echo $activeClassArr['ha_log'] ?> <?php echo $displayArr['ha_log'] ?>  nav-item" data-type="ha_log">
                    <a href="#halogdiv" data-toggle="tab" class=" nav-link">
                        <i class="levelchild viconfont vicon-gaokeyongrizhi "></i> <?php echo $LANG['UI_PLATFORM_HA_LOG'] ?> </a>
                </li>
            </ul>
            <div class="tab-content  hover-scroll-y">
                <div class="tab-pane <?php echo $activeClassArr['job_log'] ?>" id="joblogdiv">
                    <?php
                    if (empty($displayArr['job_log'])) {
                        // 显示才加载
                        include_once './job_log.php';
                    }
                    ?>
                </div>

                <div class="tab-pane <?php echo $activeClassArr['system_log'] ?>" id="systemlogdiv">
                    <?php
                    if (empty($displayArr['system_log'])) {
                        // 显示才加载
                        include_once './system_log.php';
                    }
                    ?>
                </div>

                <div class="tab-pane <?php echo $activeClassArr['ha_log'] ?>" id="halogdiv">
                    <?php
                    if (empty($displayArr['ha_log'])) {
                        // 显示才加载
                        include_once './ha_log.php';
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- END TAB PORTLET-->
    </div>
</div>
<!-- END PAGE CONTENT-->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/jszip.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/pdfmake.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/vfs_fonts.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/buttons.print.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>

<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>

<script type="text/javascript" src="./scripts/platform/logs/job_log.js"></script>
<script type="text/javascript" src="./scripts/platform/logs/system_log.js"></script>
<script type="text/javascript" src="./scripts/platform/logs/ha_log.js"></script>
<!-- END PAGE LEVEL PLUGINS -->