<?php include_once '../../../tpl/permission.php';
$userAllPermission = $_SESSION['permission']; ?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css">
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" />
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/icheck/skins/all.css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css" />
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />
<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/backup/backupresources.php">
            <span><?php echo $LANG['UI_PLATFORM_BACKUP_RESOURCE'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <li>
        <a class="ajaxify" name="backup_manager" href="./content/platform/strategy/global_strategy.php">
            <?php echo $LANG['UI_GLOBAL_STRATEGY_LIST'] ?>
        </a>
    </li>
    <span>></span>
    <span class="current">
        <?php if ($_GET['uuid']) {
            echo $LANG['UI_PUBLIC_MODIFY'];
        } else {
            echo $LANG['UI_PUBLIC_ADD'];
        } ?>
    </span>
</h3>
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN VALIDATION STATES-->
        <span class="display-none" id="servertime"></span>
        <input id="strategy_uuid" value="<?php echo $_GET['uuid']; ?>" class="display-none"></input>
        <div class="portlet box blue-hoki" id="backupStrategyContent">
            <div class="portlet-title">
                <div class="caption">
                    <i class="viconfont vicon-ge_modify"></i>
                    <?php
                    if ($_GET['uuid']) {
                        echo $LANG['UI_GLOBAL_STRATEGY_EDIT'];
                    } else {
                        echo $LANG['UI_GLOBAL_STRATEGY_ADD'];
                    } ?>
                </div>
            </div>
            <div class="portlet-body form">
                <!-- BEGIN FORM-->
                <form action="#" id="setStrategyForm" class="form-horizontal">
                    <div class="form-body">
                        <div class="form-group form-group-name pt50">
                            <label class="control-label col-md-3">
                                <span class="required">* </span>
                                <?php echo $LANG['UI_GLOBAL_STRATEGY_NAME'] ?>
                            </label>
                            <div class="col-md-4 mb15">
                                <div class="input-icon input-icon-name right">
                                    <input type="text" onkeyup="customInputValidate('string', this.value, $(this))" maxlength="128" class="form-control" name="strategyname" id="strategyName" />
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_REMARK'] ?>
                            </label>
                            <div class="col-md-4">
                                <div class="input-icon right">
                                    <i class="fa"></i>
                                    <input id="remark" onkeyup="customInputValidate('string', this.value, $(this))" type="text" maxlength="128" class="form-control" placeholder="" />
                                    <div><span class="help-block "><?php echo $LANG['UI_GLOBAL_STRATEGY_REMARK_TIPS'] ?></span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group module_type-select">
                            <label class="control-label col-md-3"><?php echo $LANG['UI_GLOBAL_STRATEGY_TYPE'] ?>
                            </label>
                            <div class="col-md-4">
                                <div class="width100p">
                                    <select class="bs-select width100p form-control"  data-show-subtext="true" id="strategyType"></select>
                                    <div><span class="help-block "><?php echo $LANG['UI_GLOBAL_STRATEGY_MODULE_TYPE_TIPS'] ?></span></div>
                                </div>
                            </div>
                        </div>
                        <!-- 策略位置 -->
                        <div class="form-group">
                            <div id="backupStrategy">
                            </div>
                        </div>

                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-offset-6 col-md-6">
                                <button type="button" id="cancelBtn" class="btn default btn-cancle"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                                <button type="button" id="addsubmit" class="btn green-haze btn-confirm"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- END FORM-->
            </div>
        </div>
        <!-- END VALIDATION STATES-->
        <div id="strategyDiffDiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
            <div class="modal-header ">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title"><i class="viconfont vicon-a-Efferent-threechuanchu3"></i> <?php echo $LANG['UI_GLOBAL_STRATEGY_DISPENSE'] ?></h4>
            </div>
            <div class="modal-body">
                <div class="portlet-body">
                    <div class="table-container">
                        <table id="data_table"></table>
                    </div>
                </div>
                <div class="portlet-body">
                    <div class="alert alert-info">
                        <button type="button" class="close" data-dismiss="alert"></button>
                        <ol class="alert-ol">
                            <li><?php echo $LANG['UI_GLOBAL_STRATEGY_SELECT_DISPENSE_TIPS'] ?></li>
                            <li><?php echo $LANG['UI_GLOBAL_STRATEGY_DISPENSE_TIPS1'] ?></li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default" id="close_modal"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
                <button type="button" class="btn btn-primary" id="dispense_submit"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
            </div>
        </div>
        <!-- END MODAL -->

        <!-- 差异表格 -->
        <div class="drawer slide" id="diffDrawer" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title" aria-hidden="true">
            <div class="drawer-content drawer-content-scrollable" role="document">
                <div class="drawer-header">
                    <h4 class="drawer-title" id="drawer-1-title"><i class="viconfont vicon-a-Editorbianji"></i><?php echo $LANG['UI_GLOBAL_STRATEGY_DIFF_STRATEGY'] ?></h4>
                </div>
                <div class="drawer-body">
                    <table id="diffStrategyTable"></table>
                </div>
                <div class="drawer-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CONFIRM'] ?></button>
                    <button type="button" class="btn btn-default" data-dismiss="drawer" aria-label="Close"><?php echo $LANG['UI_PUBLIC_CANCEL'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE CONTENT-->
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/jquery-validation/js/additional-methods.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}
if ($_SESSION['language'] == "zh-cn" || $_SESSION['language'] == "zh-tw") {
    echo '<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>';
}
?>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.strategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.speedStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.GFSStrategy.js"></script>
<script type="text/javascript" src="./scripts/plugins/jquery/jquery.taskCrowd.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/platform/global_strategy/speed_strategy.js"></script>

<script type="text/javascript" src="./scripts/plugins/jquery/backup-strategy.js"></script>
<script src="./scripts/platform/strategy/add_strategy.js" type="text/javascript"></script>