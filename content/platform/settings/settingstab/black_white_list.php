<?php
include_once '../../../../tpl/permission.php';
// $userAllPermission = $_SESSION['permission'];
include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php';
?>
<link href="./content/platform/settings/css/blackWhiteList.css" rel="stylesheet" type="text/css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="setting_manager" href="./content/platform/settings/setting_manager.php">
            <span><?php echo $LANG['UI_PLATFORM_SYSTEM_SET'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST'] ?></span>
</h3>
<?php
$userAllPermission = $_SESSION['permission'];

$tabNameArr = array('white_list', 'black_list');
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
        <div class="portlet box blue-hoki" id="listMangerDiv">
            <div class="portlet-title">
                <ul class="nav nav-tabs floatl">
                    <li class="<?php echo $activeClassArr['white_list'] ?> <?php echo $displayArr['black_list'] ?>">
                        <a id="black_list" href="#blackListdiv" data-toggle="tab">
                            <i class="levelchild viconfont vicon-a-Wrong-usercuowuyonghu"></i> <?php echo $LANG['UI_PLATFORM_BLACKLIST'] ?> </a>
                    </li>
                    <li class="<?php echo $activeClassArr['black_list'] ?> <?php echo $displayArr['white_list'] ?>">
                        <a id="white_list" href="#whiteListdiv" data-toggle="tab">
                            <i class="levelchild viconfont vicon-a-Right-userzhengqueyonghu"></i> <?php echo $LANG['UI_PLATFORM_WHITELIST'] ?> </a>
                    </li>
                </ul>
            </div>
            <div class="portlet-body">
                <div class="tab-content row margin10">

                    <div class="table-toolbar">
                        <div id="vin_list_toolbar" class="vin_toolbar" >
                            <div class="leftTool">
                            </div>
                            <div class="rightTool">
                                <div class="vin_btnToolbar">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="black_list_table"></table>
                    </div>

                </div>



            </div>
        </div>
        <!-- END FORM-->
    </div>
</div>

<!-- BEGIN OBS DETAIL DRAWER -->
<div class="drawer slide width650" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true" id="list_detail">
    <form id="listForm" class="drawer-content drawer-content-scrollable form-horizontal" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title">
                <i class="viconfont vicon-a-Wrong-usercuowuyonghu mr8 iconList"></i>
                <span id="titleDes"><?php echo $LANG['UI_PLATFORM_BLACKLIST_ADD_BLACKLIST'] ?> </span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body" style="padding-bottom: 0;">
            <div class="form-group">
                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_IP'] ?></label>
                <div class="col-md-8" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center;">
                        <input type="text" id="start_ip" name="start_ip" class="form-control" autocomplete="off" placeholder="<?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_ENTER_START_IP'] ?> ">
                        <div style="margin: 0 5px;">-</div>
                        <input type="text" id="end_ip" name="end_ip" class="form-control" autocomplete="off" placeholder="<?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_ENTER_END_IP'] ?> ">
                    </div>
                    <div style="margin-bottom: 5px;">
                        <div class="error-div"></div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_PERMANENT'] ?></label>
                <div class="col-md-1 form-group-content">
                    <input type="checkbox" id="permanentTimeCheck" data-size="small" class="make-switch" data-on-color="primary" data-off-color="info" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>" data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
                <div class="col-md-2 mt7 ml10">
                    <a class="popovers" data-container="body" data-trigger="hover" data-html="true" data-placement="right" data-content="<?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_TIPS'] ?>">
                        <i class="viconfont vicon-tishi"></i>
                    </a>
                </div>
            </div>

            <div class="form-group selecTimeDiv">
                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_SELECT_TIME'] ?></label>
                <div class="col-md-8 daterangepickerdiv">
                    <input type="text" id="validDateRangePicker" class="form-control" name="validDateRangePicker" autocomplete="off" readonly style="cursor: pointer"><!-- 时间选择禁用手动输入 -->
                    <div class="error-div"></div>
                    <i class="viconfont vicon-ge_calendar"></i>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-md-3"><?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_REMARKS'] ?></label>
                <div class="col-md-8" style="display: flex; align-items: center;">
                    <textarea class="form-control" name="description" id="description" cols="45" rows="10"></textarea>
                </div>
            </div>
            <div class="col-md-12 sec-position">
                <div class="alert alert-block alert-info fade in" id="marktips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong></h4>
                    <ol class="alert-ol">
                        <li>
                            <?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_REMARKS_TIPS1'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_REMARKS_TIPS2'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_REMARKS_TIPS3'] ?>
                        </li>
                        <li>
                            <?php echo $LANG['UI_PLATFORM_BLACKLIST_WHITELIST_REMARKS_TIPS4'] ?>
                        </li>
                    </ol>
                </div>
            </div>

        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary" id="addsubmit">
                <?php echo $LANG['UI_PUBLIC_YES'] ?>
            </button>
            <button type="button" data-dismiss="drawer" aria-label="Close" class="btn btn-primary display-none" id="modifysubmit">
                <?php echo $LANG['UI_PUBLIC_YES'] ?>
            </button>
            <button type="button" data-dismiss="drawer" aria-label="Close"
                    class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?>
            </button>
        </div>
    </form>
</div>
<!-- END OBS DETAIL DRAWER -->



<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>

<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}

?>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./scripts/platform/settings/settingstab/black_white_list.js"></script>
<!-- END PAGE LEVEL PLUGINS -->
