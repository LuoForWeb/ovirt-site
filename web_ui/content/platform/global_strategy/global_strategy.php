<style>
    .nav-tab-radios {
        height: 425px;
    }
    .input-invalid_wrap .fa.fa-warning{
        right: -27px;
    }
    #current_job_datepicker:hover{
        background-color: #FAFAFA !important;
    }
    .dropdown-menu{
        z-index:100055 !important;
    }
</style>

<?php
include_once '../../../tpl/permission.php';
// $userAllPermission = $_SESSION['permission'];
include_once '../../platform/public/bs_table.php';
?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/jquery-file-upload/css/jquery.fileupload.css" rel="stylesheet"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/square/blue.css" />
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER -->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php">
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_LIST'] ?></span>
</h3>
<!-- END PAGE HEADER -->

<!-- BEGIN PAGE CONTENT-->
<div class="resource-manager-wrap" id="show-global-speed">
    <div class="resource-manager-wrap__header">
        <span>
            <i class="levelchild iconfont icon-xiansucelve me-10"></i>
            <span class="resource-manager-wrap__header__text"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_LIST'] ?></span>
        </span>
    </div>
    <div class="resource-manager-wrap__content">
        <div class="table-toolbar-wrapper vin_toolbar" id="global_strategy_toolbar">
            <div class="table-toolbar-wrapper__left" id="left_btn" style="display: flex;">

            </div>
            <div class="table-toolbar-wrapper__right rightTool">
                <div class="table-actions-wrapper page-right">

                </div>
                <div class="vin_btnToolbar"></div>
            </div>

        </div>

        <div class="table-container speed-limit-table-container">
            <table id="strategy_speed_table"></table>
        </div>

        <!--提示信息-->
        <div class="alert alert-block alert-info fade in h-100px" id="speed_limit_tips">
            <button type="button" class="close" data-dismiss="alert"></button>
            <h4 class="alert-heading">
                <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
            </h4>
            <ol class = "alert-ol">
                <li>
                    <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_LIST_TIPS1'] ?>
                </li>
                <li>
                    <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_LIST_TIPS2'] ?>
                </li>
            </ol>
        </div>
    </div>
</div>

<!--添加模态框开始-->
<div class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-add-strategy-title" aria-hidden="true"
     id="mAddDrawer" style="width: 800px; z-index: 10051;">
    <div id="uuid" class="display-none"></div>
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header" style="height: 52px;">
            <button type="button" class="close" data-dismiss="drawer" aria-hidden="true" style="transform: scale(0.66);"></button>
            <h4 class="drawer-title add-title" style="margin-top: 0px;">
                <i class="viconfont vicon-ge_add_task mr8" style="height: 18px;font-size: 18px;margin-right: 6px !important;"></i> <span id="global_strategy_show_title" style="font-size: 16px;"><?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD_PLAN'] ?></span>
            </h4>
        </div>
        <div class="drawer-body">
            <div class="portlet-body form">
                <form action="#" class="form-horizontal" id="submit_form" method="POST" style="height:100%;">
                    <div class="form-wizard" style="height: 100%;">
                        <div class="form-body" style="padding-top: 0px;">
                            <div class="tab-content" style="height:100%;overflow-x:hidden;overflow-y:auto;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <!-- 策略名称开始 -->
                                        <div class="form-group">
                                            <label class="control-label col-md-2">
                                                <?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME'] ?>
                                            </label>
                                            <div class="col-md-6">
                                                <div class="input-icon input-icon-name right">
                                                    <input type="text" maxlength="128" onkeyup="customInputValidate('string', this.value, $(this))" autocomplete="off" placeholder="<?php echo $LANG['UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME'] ?>" class="form-control" id="name" style="width: 364px;">
                                                </div>
                                            </div>
                                        </div>
                                        <!-- 策略名称 -->
                                        <?php require('speedLimitStrategy.php'); ?>
                                        <!-- 全局限速策略 -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- 抽屉底部 -->
        <div class="drawer-footer">
            <input type="hidden" id="strategy_uuid" value="0">
            <button type="button" class="btn green-haze button-submit" id="add_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            <button type="button" data-dismiss="drawer" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>

        </div>
    </div>
</div>
<!--添加模态框结束-->

<!-- BEGIN MODAL 分发-->
<div id="distributeDrawerDiv" class="drawer slide" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-distribute-title"
     aria-hidden="true" style="width: 800px;">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header " style="height: 52px;">
            <button type="button" class="close" data-dismiss="drawer" aria-hidden="true" style="transform: scale(0.66);"></button>
            <h4 class="drawer-title" style="margin-top: 0px;"><i class="viconfont vicon-a-Efferent-threechuanchu3"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_ASSIGN_POLICY'] ?></h4>
        </div>
        <div class="drawer-body" style="padding-top: 3px;">
            <div class="portlet-body">
                <div class="form-group">
                    <div class="col-md-6">
                        <div class="">

                            <!--<a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right" data-content="<?php echo $LANG['UI_GLOBAL_STRATEGY_TASK_LEVEL_TIPS']; ?>" data-original-title="" title="" style="float: left;margin-top: -25px;margin-left: 215px !important;">
                        <i class="viconfont vicon-tishi"></i>
                    </a>-->
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-10" style="width: 760px;padding-inline: 0px;">
                        <div class="table-toolbar" style="width: 332px;height: 34px;">
                            <div class="vin_toolbar" id="vin_speed_task_toolbar">
                                <div class="leftTool" style="width: 180px;">
                                </div>
                                <div id="distinct_daterangepicker_wrapper" class="btn btn-dropdown-daterange" style="background-color: rgb(255, 255, 255);padding: 7px 20px;">
                                    <i class="viconfont vicon-rili2 me-4" style="color:rgb(15, 191, 152);"></i><span class="daterangepicker-text running-log-search"><?php echo $LANG['UI_TASK_REPORT_CREATE_TIME'] ?></span></div>
                            </div>
                        </div>
                        <div class="table-container">
                            <table id="speed_task_table"></table>
                            <div class="alert alert-block alert-info fade in" style="position: fixed;bottom: 57px;width: 760px;">
                                <button type="button" class="close" data-dismiss="alert"></button>
                                <h4 class="alert-heading">
                                    <strong><?php echo $LANG['UI_PUBLIC_TIPS'] ?></strong>
                                </h4>
                                <ol class="alert-ol">
                                    <li><?php echo $LANG['UI_GLOBAL_STRATEGY_DISPENSE_TIPS1'] ?></li>
                                    <li><?php echo $LANG['UI_GLOBAL_STRATEGY_DISPENSE_TIPS2'] ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn btn-primary" id="dispense_speed_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            <button type="button" data-dismiss="drawer" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>

        </div>
    </div>
</div>
<!-- END MODAL 分发-->

<!-- END PAGE CONTENT-->

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<?php
if ($_SESSION['language'] != 'en-us') {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
?>

<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js"></script>
<script src="./assets/global/plugins/jquery-file-upload/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>

<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script src="./scripts/platform/global_strategy/global_strategy.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->