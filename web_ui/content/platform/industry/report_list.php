<?php
include_once '../../../tpl/permission.php';
include_once '../../../content/platform/public/bs_table.php';
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"
    xmlns="http://www.w3.org/1999/html" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />

<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('p_industry_report_pending', 'p_industry_report_archived', 'p_industry_report_share');
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

<div class="vinchin-wrapper">
    <div class="vinchin-wrapper__tabs">
        <ul class="nav nav-tabs nav-line-tabs">
            <li class="<?php echo $activeClassArr['p_industry_report_pending'] . ' ' . $displayArr['p_industry_report_pending'] ?> nav-item" id="pending_li">
                <a href="#pending_div" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-a-Group1000003105"></i>
                    <?php echo $LANG['UI_GMP_REPORT_PENDING'] ?>
                </a>
            </li>
            <li class = "<?php echo $activeClassArr['p_industry_report_archived'] . ' ' . $displayArr['p_industry_report_archived'] ?> nav-item" id="archived_li">
                <a href="#archived_div" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-shenpiguidang"></i>
                    <?php echo $LANG['UI_GMP_REPORT_ARCHIVED'] ?>
                </a>
            </li>
            <li class = "<?php echo $activeClassArr['p_industry_report_share'] . ' ' . $displayArr['p_industry_report_share'] ?> nav-item" id="share_li">
                <a href="#share_div" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-fenxiang"></i>
                    <?php echo $LANG['UI_GMP_REPORT_SHARE_OR_COPYSEND'] ?>
                </a>
            </li>
        </ul>

        <div class="tab-content hover-scroll-y overflow-x-hidden">
            <div class="tab-pane <?php echo $activeClassArr['p_industry_report_pending'] ?>" id="pending_div">
                <?php
                if (empty($displayArr['p_industry_report_pending'])) {
                    // 显示才加载
                    include_once './report_list_pending.php';
                }
                ?>
            </div>

            <div class="tab-pane <?php echo $activeClassArr['p_industry_report_archived'] ?>" id="archived_div">
                <?php
                if (empty($displayArr['p_industry_report_archived'])) {
                    // 显示才加载
                    include_once './report_list_archived.php';
                }
                ?>
            </div>

            <div class="tab-pane <?php echo $activeClassArr['p_industry_report_share'] ?>" id="share_div">
                <?php
                if (empty($displayArr['p_industry_report_share'])) {
                    // 显示才加载
                    include_once './report_list_share.php';
                }
                ?>
            </div>
        </div>
    </div>
</div>
<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>

<!--引入报告配置-->
<?php require_once 'job_report.php';?>
