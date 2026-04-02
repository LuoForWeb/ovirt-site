<?php
include_once '../../../tpl/permission.php';
include_once '../../../content/platform/public/bs_table.php';
include_once '../component/ueditor.php';
?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"
    xmlns="http://www.w3.org/1999/html" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />

<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('p_industry_plan', 'p_industry_plan_function', 'p_industry_plan_share');
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
        <ul class="nav nav-tabs nav-line-tabs" id="plan_ul_div">
            <li class="<?php echo $activeClassArr['p_industry_plan'] . ' ' . $displayArr['p_industry_plan'] ?> nav-item" id="plan_data">
                <a href="#plan_data_li" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-shujuyanzheng"></i>
                    <?php echo $LANG['UI_INDUSTRY_PLAN_DATA'] ?>
                </a>
            </li>
            <li class = "<?php echo $activeClassArr['p_industry_plan_function'] . ' ' . $displayArr['p_industry_plan_function'] ?> nav-item" id="plan_function">
                <a href="#plan_function_li" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-gongnengyanzheng"></i>
                    <?php echo $LANG['UI_INDUSTRY_PLAN_FUNCTION'] ?>
                </a>
            </li>
            <li class = "<?php echo $activeClassArr['p_industry_plan_share'] . ' ' . $displayArr['p_industry_plan_share'] ?> nav-item" id="plan_share">
                <a href="#plan_share_li" data-toggle="tab" class="nav-link">
                    <i class="levelchild viconfont vicon-fenxiang"></i>
                    <?php echo $LANG['UI_GMP_REPORT_SHARE_OR_COPYSEND'] ?>
                </a>
            </li>
        </ul>

        <div class="tab-content hover-scroll-y overflow-x-hidden" id="show_plan_div">
            <div class="tab-pane <?php echo $activeClassArr['p_industry_plan'] ?>" id="plan_data_li">
                <?php
                if (empty($displayArr['p_industry_plan'])) {
                    // 显示才加载
                    include_once './plan_list_data.php';
                }
                ?>
            </div>

            <div class="tab-pane <?php echo $activeClassArr['p_industry_plan_function'] ?>" id="plan_function_li">
                <?php
                if (empty($displayArr['p_industry_plan_function'])) {
                    // 显示才加载
                    include_once './plan_list_function.php';
                }
                ?>
            </div>

            <div class="tab-pane <?php echo $activeClassArr['p_industry_plan_share'] ?>" id="plan_share_li">
                <?php
                if (empty($displayArr['p_industry_plan_share'])) {
                    // 显示才加载
                    include_once './plan_list_share.php';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php  include_once './plan_drawer.php';?>

<script src="./scripts/libs/md5.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script src="./scripts/platform/industry/plan_list.js"></script>
<script src="./scripts/platform/industry/search_plan.js"></script>
