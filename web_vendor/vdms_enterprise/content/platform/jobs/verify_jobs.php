<?php
include_once '../../../tpl/permission.php';
include_once '../../../content/platform/public/bs_table.php';
?>

<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet"
      type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css"
      href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/menu/css/menu.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/filter/css/filter.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/daterangepicker/css/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="./content/platform/jobs/css/jobs.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->


<?php
$tab = intval($_GET['tab']);
$tabActive = array("", "", "", "", "", "");
$tabActive[$tab] = "active";
$userAllPermission = $_SESSION['permission'];

?>
<!-- END PAGE HEADER-->

<!-- BEGIN PAGE CONTENT-->
<div class="vinchin-wrapper">
    <div class="vinchin-wrapper__tabs">
        <ul class="nav nav-tabs nav-line-tabs">
            <?php
            echo '  <li id="currentLi" class="' . $tabActive[0] . ' nav-item" >
                            <a href="#currentjobdiv" class="nav-link" data-toggle="tab">
                                <i class="levelchild viconfont vicon-pt_job_current_task"></i> ' . '验证任务' . ' 
                            </a>
                        </li>';

            $li = !in_array("current_job", $userAllPermission) ? $tabActive[0] : $tabActive[1];
            echo '  <li id="historyLi" class="' . $li . ' nav-item" >
                        <a href="#historyjobdiv" class="nav-link" data-toggle="tab">
                            <i class="levelchild viconfont vicon-pt_job_historical_task"></i> ' . '历史验证任务' . ' 
                        </a>
                    </li>';
            ?>
        </ul>
        <div class="tab-content hover-scroll-y overflow-x-hidden">
            <div class="tab-pane <?php echo $tabActive[0]; ?> " id="currentjobdiv">
                <?php
                include_once './verify_job.php';
                ?>
            </div>
            <div class="tab-pane <?php echo !in_array("current_job", $userAllPermission) ? $tabActive[0] : $tabActive[1]; ?> " id="historyjobdiv">
                <?php
                include_once './verify_history_job.php';
                ?>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT -->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript"
        src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>
<script type="text/javascript" src="./scripts/components/filter/js/filter.js"></script>
<script type="text/javascript" src="./scripts/components/daterangepicker/js/daterangepicker.js"></script>
<!-- END PAGE LEVEL PLUGINS -->