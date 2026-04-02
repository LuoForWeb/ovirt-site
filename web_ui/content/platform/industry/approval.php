<?php
include_once '../../../tpl/permission.php';
include_once '../public/bs_table.php';
?>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.css" rel="stylesheet" type="text/css" />

<div class="row">
    <div class="col-md-12 col-manager">
        <!-- BEGIN TAB PORTLET-->
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="levelchild viconfont vicon-shenpiliu"></i><?php echo $LANG['WEB_INDUSTRY_APPROVAL_LIST'] ?>
                </div>
            </div>
            <div class="portlet-body">
                <div class="tab-content row margin10" id="approval_tab">

                    <div>
                        <div id="vin_approval_toolbar" class="vin_toolbar">
                            <div class="leftTool">
                            </div>
                            <div class="rightTool">
                                <div class="vin_btnToolbar">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-container">
                        <table id="approval_table"></table>
                    </div>

                </div>
            </div>
        </div>
        <!-- END FORM-->
    </div>
</div>

<!-- BEGIN ADD DRAWER -->
<div class="drawer slide width750" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title"
    id="approval_drawer">
    <form id="approvalForm" class="drawer-content drawer-content-scrollable form-horizontal" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title">
                <i class="viconfont vicon-danchuangtianjia1 mr8 iconList"></i>
                <span id="titleDes"><?php echo $LANG['UI_GMP_APPROVAL_ADD'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i
                        class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body" style="padding-bottom: 0;">

            <!-- 名称 -->
            <div class="form-group">
                <label class="control-label col-md-2"><?php echo $LANG['UI_GMP_NAME_DESC'] ?></label>
                <div class="col-md-9">
                    <div>
                        <input type="text" id="name" name="name" class="form-control" autocomplete="off" placeholder="">
                        <div>
                            <span class="help-block "><?php echo $LANG['UI_GMP_APPROVAL_NAME_DESC'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 分组 -->
            <div class="form-group">
                <label class="control-label col-md-2"><?php echo $LANG['UI_GMP_APPROVAL_CLASSIFY'] ?></label>
                <div class="col-md-9">
                    <div>
                        <select class="form-control select2me" name="classify">
                            <option value="0"><?php echo $LANG['UI_GMP_APPROVAL_PLEASE_SELECT_CLASSIFY'] ?></option>
                        </select>
                        <div>
                            <span class="help-block "><?php echo $LANG['UI_GMP_APPROVAL_ADD_CLASSIFY_TIP1'] ?><a class="ajaxify add_classify"
                                    href="./content/platform/industry/approval_classify.php"
                                    style="color:#00A3FF">
                                    <?php echo $LANG['UI_GMP_APPROVAL_ADD_CLASSIFY'] ?></a></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 类型 -->
            <!-- <div class="form-group">
                <label class="control-label col-md-2">类型</label>
                <div class="col-md-9">
                    <div>
                        <select class="form-control select2me" id="actions">
                            <option value="0">请选择类型</option>
                            <option value="1">报告</option>
                            <option value="2">任务</option>
                        </select>
                        <div>
                            <span class="help-block ">审批流的类型</span>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- 层级 -->
            <div class="form-group">
                <label class="control-label col-md-2 mt10"><?php echo $LANG['UI_GMP_APPROVAL_CONTENT'] ?></label>

                <div id="depth" style="display: flex; flex-wrap: wrap;">

                    <div class="col-md-11 sort-div-parent">
                        <div class="sort-div" id="item1" style="display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center;">
                                <select class="form-control select2me" name="depth">
                                    <option value="0"><?php echo $LANG['UI_GMP_APPROVAL_SELECT_USER'] ?></option>
                                </select>
                                <input type="text" name="description" class="form-control ml8" style="width: 117px;"
                                    autocomplete="off" placeholder="" value=<?php echo $LANG['UI_GMP_REPORT_PENDING'] ?>>
                                <span class="ml8 close-btn"><i class="viconfont vicon-a-Reduce-onejianshao"></i></span>
                            </div>
                        </div>
                    </div>

                    <div class="arrow">
                        <i class="viconfont vicon-jiantou1"></i>
                    </div>

                    <div class="col-md-11 sort-div-parent">
                        <div class="sort-div" id="item2" style="display: flex; flex-direction: column;">
                            <div>
                                <select class="form-control select2me" name="depth">
                                    <option value="0"><?php echo $LANG['UI_GMP_APPROVAL_SELECT_USER'] ?></option>
                                </select>
                                <input type="text" name="description" class="form-control ml8" style="width: 117px;"
                                    autocomplete="off" placeholder="" value=<?php echo $LANG['UI_GMP_REPORT_PENDING'] ?>>
                                <span class="ml8 close-btn"><i class="viconfont vicon-a-Reduce-onejianshao"></i></span>
                            </div>
                        </div>
                    </div>

                </div>
                <div>
                    <span class="help-block" style="margin-left: 150px;"><?php echo $LANG['UI_GMP_APPROVAL_CONTENT_DESC'] ?></span>
                </div>
            </div>

            <!-- 添加层级 -->
            <div class="form-group">
                <label class="control-label col-md-2"></label>
                <div class="col-md-9" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center;">
                        <a class="add-depth" style="color:#00A3FF"><i class="viconfont vicon-zhediekuanganniu"></i>
                        <?php echo $LANG['UI_GMP_APPROVAL_ADD'] ?></a>
                    </div>
                    <div style="margin-bottom: 5px;">
                        <div class="error-div"></div>
                    </div>
                </div>
            </div>

            <!-- 状态 -->
            <div class="form-group" id="status_switch">
                <div class="control-label col-md-2">
                    <?php echo $LANG['WEB_PLATFORM_ENABLE'] ?>
                </div>
                <div class="col-md-9" style=" margin-top: 3px;">
                    <input type="checkbox" id="status" class="make-switch" data-on-color="primary" data-off-color="info"
                        data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                        data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                    <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right"
                        data-content="<?php echo $LANG['UI_GMP_APPROVAL_USE'] ?>">
                        <i class="fa fa-info-circle fa-lg"></i>
                    </a>
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


<!-- BEGIN VIEW DRAWER -->
<div class="drawer slide width600" data-placement="right" tabindex="-1" role="dialog" aria-labelledby="drawer-1-title"
    id="view_drawer">
    <form id="view_form" class="drawer-content drawer-content-scrollable form-horizontal" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-2-title">
                <i class="viconfont vicon-shenpiliu mr8 iconList"></i>
                <span><?php echo $LANG['UI_GMP_APPROVAL_CLASSIFY'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close"><i
                        class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body" style="padding-bottom: 0;">

            <!-- 名称 -->
            <div class="form-group">
                <label class="control-label col-md-2"><?php echo $LANG['UI_GMP_NAME_DESC'] ?></label>
                <div class="col-md-9" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center;">
                        <span id="name_display"></span>
                    </div>
                </div>
            </div>

            <!-- 审批流程 -->
            <div class="form-group">
                <label class="control-label col-md-2"><?php echo $LANG['UI_GMP_APPROVAL_CLASSIFY'] ?></label>
                <div class="col-md-9" style="display: flex; flex-direction: column;">
                    <div style="display: flex; align-items: center;">
                        <div id="depth_div">

                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="drawer-footer">
            <button type="button" data-dismiss="drawer" aria-label="Close"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
            </button>
        </div>
    </form>
</div>
<!-- END VIEW DRAWER -->

<!-- BEGIN COPY SEND MODAL -->
<div id="copy_send_modal" class="modal xmodal form-horizontal" style="z-index: 10055;margin-top: 15%;">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title"><i class="viconfont vicon-a-Pie-twojindu2"></i>
        <?php echo $LANG['UI_GMP_APPROVAL_SELECT_COPY_SEND_USER'] ?>
        </h4>
    </div>
    <div class="modal-body" style="overflow: auto;height: 300px;">
        <!-- 选择用户 -->
        <div class="form-group">
            <label class="control-label col-md-3">
            <?php echo $LANG['WEB_M365_RECOVERY_SELECT_USER'] ?>
            </label>
            <div class="col-md-9">
                <select class="bootstrap-mutiple-select selectpicker show-tick" multiple data-live-search="true" data-actions-box="true"  id="user" name="user">
                </select>
            </div>
        </div>

        <!-- 选择用户组 -->
        <div class="form-group">
            <label class="control-label col-md-3">
            <?php echo $LANG['UI_GMP_APPROVAL_SELECT_USER_GROUP'] ?>
            </label>
            <div class="col-md-9">
                <select class="bootstrap-mutiple-select selectpicker show-tick" multiple data-live-search="true" data-actions-box="true" id="user_group" name="user_group">

                </select>
            </div>
        </div>

        <!-- 是否开启通知 -->
        <div class="form-group" id="notice">
            <div class="control-label col-md-3">
            <?php echo $LANG['UI_GMP_APPROVAL_OPEN_NOTICE_OR_NOT'] ?>
            </div>
            <div class="col-md-9" style=" margin-top: 3px;">
                <input type="checkbox" id="notice_switch" class="make-switch" data-on-color="primary"
                    data-off-color="info" data-size="small" data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                    data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right"
                    data-content="<?php echo $LANG['UI_GMP_APPROVAL_OPEN_EMAIL_NOTICE_OR_NOT'] ?>">
                    <i class="fa fa-info-circle fa-lg"></i>
                </a>
            </div>
        </div>
        <!-- 提示信息 -->
        <!-- <div class="alert alert-block alert-info fade in ml15 mt15 mb0" id="copy_tips">
            <button type="button" class="close" data-dismiss="alert"></button>
            <ul class="alert-ul">
                <strong class="alert-ul-head">提示：</strong>
                <li></li>
            </ul>
        </div> -->
    </div>

    <div class="modal-footer">
        <div class="form-actions">
            <div class="row">
                <div class="col-md-7" style="float: right;padding-right: 20px;">
                    <button type="button" class="btn default cancel">
                        <?php echo $LANG['UI_PUBLIC_NO'] ?>
                    </button>
                    <button type="button" class="btn green-haze btn-confirm add_submit">
                        <?php echo $LANG['UI_PUBLIC_YES'] ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- END COPY SEND MODAL -->


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script src="./assets/global/plugins/jquery-validation/js/jquery.validate.min.js" type="text/javascript"></script>
<script type="text/javascript" src="./assets/global/plugins/sortable/Sortable.js"></script>
<script type="text/javascript" src="./assets/global/plugins/icheck/icheck.min.js"></script>
<!-- 
<?php
if ($_SESSION['language'] != "en-us") {
    //如果不是英文,加载语言包
    echo '<script src="./assets/global/plugins/jquery-validation/js/localization/messages-' .
        $_SESSION['language'] . '.js" type="text/javascript"></script>';
}

?> -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="./scripts/platform/industry/approval.js"></script>
<!-- END PAGE LEVEL PLUGINS -->