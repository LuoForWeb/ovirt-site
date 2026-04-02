<?php
include_once '../../../tpl/permission.php';
include_once  '../public/bs_table.php';
?>

<!-- BEGIN PAGE HEADER-->
<h3 class="breadcrumb">
    <li>
        <a class="ajaxify" name="industry_approval" href="./content/platform/industry/approval.php">
            <span><?php echo $LANG['UI_GMP_APPROVAL_NAME'] ?></span>
        </a>
    </li>
    <span>
        >
    </span>
    <span class="curent"><?php echo $LANG['UI_GMP_APPROVAL_CLASSIFY'] ?></span>
</h3>

<div class="row row-manager">
    <div class="col-md-12 col-manager">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-fenlei"></i><?php echo $LANG['UI_GMP_APPROVAL_CLASSIFY'] ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="tab-content row margin10" id="classify_tab">

                    <div>
                        <div id="vin_classify_toolbar" class="vin_toolbar">
                            <div class="leftTool">
                            </div>
                            <div class="rightTool">
                                <div class="vin_btnToolbar">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="classify_table"></table>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>


<!-- BEGIN ADD DRAWER -->
<div class="drawer slide width600" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title"
    aria-hidden="true" id="classify_drawer">
    <form id="view_drawer" class="drawer-content drawer-content-scrollable form-horizontal" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-2-title">
                <i class="viconfont vicon-danchuangtianjia1 mr8 iconList"></i>
                <span  id="titleDes"><?php echo $LANG['UI_GMP_APPROVAL_ADD_CLASSIFY'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i
                        class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body" style="padding-bottom: 0;">

            <!-- 名称 -->
            <div class="form-group">
                <label class="control-label col-md-2"><?php echo $LANG['UI_GMP_APPROVAL_CLASSIFY_NAME'] ?></label>
                <div class="col-md-9" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center;">
                        <input type="text" id="name" name="name" class="form-control" autocomplete="off" placeholder="">
                    </div>
                </div>
            </div>

            <!-- 描述 -->
            <div class="form-group">
                <label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_DESCRIPTION'] ?></label>
                <div class="col-md-9" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center;">
                        <textarea class="form-control" id="remark" name="remark" rows="6" placeholder="<?php echo $LANG['UI_GMP_APPROVAL_CLASSIFY_DESC'] ?>"></textarea>
                    </div>
                </div>
            </div>

            <!-- 状态 -->
            <div class="form-group" id="status_switch">
                <div class="control-label col-md-2">
                    <?php echo $LANG['UI_PUBLIC_STATUS'] ?>
                </div>
                <div class="col-md-9" style=" margin-top: 3px;">
                    <input type="checkbox" id="status" class="make-switch" data-on-color="primary" data-off-color="info"
                        data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                </div>
            </div>

        </div>
        <div class="drawer-footer">
            <button type="button" aria-label="Close" class="btn btn-primary" id="submit">
                <?php echo $LANG['UI_PUBLIC_YES'] ?>
            </button>
            <button type="button" data-dismiss="drawer" aria-label="Close"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?>
            </button>
        </div>
    </form>
</div>
<!-- END ADD DRAWER -->


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
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./scripts/platform/industry/approval_classify.js"></script>
<!-- END PAGE LEVEL PLUGINS -->