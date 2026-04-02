<?php
include_once '../../../tpl/permission.php';
include_once '../../../content/platform/public/bs_table.php';
?>
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/> -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- BEGIN PAGE LEVEL STYLES -->
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<!-- <link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/> -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/cascader/css/cascader.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/menu/css/menu.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/filter/css/filter.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/daterangepicker/css/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="./scripts/components/advancedSearch/css/advancedSearch.css" />
<link rel="stylesheet" type="text/css" href="./content/platform/jobs/css/jobs.css" />
<!-- END PAGE LEVEL STYLES -->
 
<!-- BEGIN PAGE HEADER-->
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('current_job', 'history_job', 'task_orchestration');
$activeClassArr = array();  //active类
$displayArr = array();      //是否显示

$setFlag = false;
$valus = '';

foreach ($tabNameArr as $key => $value) {
    if (in_array($value, $userAllPermission)) {
        //如果有权限
        $displayArr[$value] = "";
        if (!$setFlag) {
            $activeClassArr[$value] = " active ";
            $setFlag = true;
        } else {
            $valus != $value && $activeClassArr[$value] = "";
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
        <ul class="nav nav-tabs nav-line-tabs" id="job_navs">
            <li id="currentLi" class="nav-item <?php echo $activeClassArr['current_job'] ?> <?php echo $displayArr['current_job'] ?>" data-type="current">
                <a href="#current_job_tabpane" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-pt_job_current_task"></i>
                    <?php echo $LANG['UI_JOB_CURRENT'] ?> </a>
            </li>
            <li id="historyLi" class="nav-item <?php echo $activeClassArr['history_job'] ?> <?php echo $displayArr['history_job'] ?>" data-type="history">
                <a href="#history_job_tabpane" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-pt_job_historical_task"></i>
                    <?php echo $LANG['UI_JOB_HISTORY'] ?> </a>
            </li>
            <li id="jobOrchestrationLi" class="nav-item <?php echo $activeClassArr['task_orchestration'] ?> <?php echo $displayArr['task_orchestration'] ?>" data-type="orchestration">
                <a href="#orchestration_job_tabpane" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-ge_advanced"></i>
                    <?php echo $LANG['UI_JOB_TASK_ORCHESTRATION'] ?> </a>
            </li>
        </ul>
        <div class="tab-content hover-scroll-y overflow-x-hidden" id="jobs_tab_content">
            <div class="tab-pane <?php echo $activeClassArr['current_job'] ?> " id="current_job_tabpane">
                <?php
                if (empty($displayArr['current_job'])) {
                    // 显示才加载
                    include_once './current_job.php';
                }
                ?>
            </div>

            <div class="tab-pane <?php echo $activeClassArr['history_job'] ?> " id="history_job_tabpane">
                <?php
                if (empty($displayArr['history_job'])) {
                    include_once './history_job.php';
                }
                ?>
            </div>
            <!-- 任务编排计划 -->
            <div class="tab-pane <?php echo $activeClassArr['task_orchestration'] ?> " id="orchestration_job_tabpane">
                <?php
                if (empty($displayArr['task_orchestration'])) {
                    include_once '../orchestration/orchestration_manage.php';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jsencrypt/jsencrypt.min.js"></script>
<script type="text/javascript" src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>
<script type="text/javascript" src="./assets/global/plugins/cascader/js/cascader.js"></script>
<script type="text/javascript" src="./scripts/components/menu/js/menu.js"></script>
<script type="text/javascript" src="./scripts/components/filter/js/filter.js"></script>
<script type="text/javascript" src="./scripts/components/daterangepicker/js/daterangepicker.js"></script>
<script type="text/javascript" src="./scripts/components/advancedSearch/js/advancedSearch.js"></script>
<script type="text/javascript" src="./scripts/platform/jobs/jobs.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	