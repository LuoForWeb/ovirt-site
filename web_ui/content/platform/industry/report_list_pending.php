<!-- Begin: life time stats grey-cararra -->
<div class="vin_toolbar" id="vin_report_pending_toolbar">
    <div class="leftTool">
    </div>

    <div class="table-toolbar-wrapper__right">
        <div class="page-right">
            <button type="button" id="searchAll0" class="btn btn-primary adv_btn brr2 p-lr8">
                <i class="adv-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
            </button>
        </div>
    </div>
</div>

<div id="searchDiv0" class="search-content searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?>
    <span class="searchContent0 searchContent"></span>
    <span class="clearSearch0 clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
</div>

<table id="report_table"></table>


<!-- BEGIN SEARCH MODAL -->
<div id="searchmodal0" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first"
     data-backdrop="static">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
        <h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
        </h4>
    </div>
    <div class="modal-body">
        <div class="portlet-body">

            <div class="list-option">
                <div class="row">
                    <!-- 报告名称 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['WEB_PLATFORM_INDUSTRY_REPORT_TITLE'] ?>：</label>
                    <div class="col-md-4">
                        <input style="width:235px; height:34px;" id="report_title0" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>

                    <!-- 关联任务 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_REPORT_TASK_VOL'] ?>：</label>
                    <div class="col-md-4">
                        <input style="width:235px; height:34px;" id="report_task0" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>
                </div>
            </div>

            <div class="list-option">
                <div class="row">
                    <!-- 设备名称 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_ITEM_HOST_NAME'] ?>：</label>
                    <div class="col-md-4">
                        <input style="width:235px; height:34px;" id="report_agent0" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>

                    <!-- 所属模板 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_GMP_REPORT_REPORT_TEMPLATE'] ?>：</label>
                    <div class="col-md-4">
                        <select id="report_template0" class="form-control">
                        </select>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- modal-footer -->
    <div class="modal-footer">
        <button type="button" data-dismiss="modal"
                class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO'] ?></button>
        <button type="button" class="btn btn-primary"
                id="serach_submit0"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END SEARCH MODAL -->

<!-- 审批弹窗start -->
<div style="width: 600px; z-index: 10051;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
    aria-labelledby="drawer-1-title" id="approve_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title"><i class="viconfont vicon-shenpi"></i>
                <?php echo $LANG['UI_GMP_REPORT_APPROVE'] ?>
            </h4>
        </div>
        <div class="drawer-body">

            <!-- BEGIN FORM-->
            <form action="#" id="approve_form" class="form-horizontal">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>

                    </div>

                    <!-- 审批结果 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_REPORT_APPROVE_RESULT'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-group mt3" id="approval_result">
                                <label class="passDiv" style="padding-right: 20px;"><input type="checkbox"
                                        id="passCheck" data-checkbox="icheckbox_square-blue" data-mode="1"
                                        class="icheck"><?php echo $LANG['UI_GMP_REPORT_APPROVE_RESULT_PASS'] ?></label>
                                <label class="rejectDiv" style="padding-right: 20px;"><input type="checkbox"
                                        id="rejectCheck" data-checkbox="icheckbox_square-blue" data-mode="2"
                                        class="icheck"><?php echo $LANG['UI_GMP_REPORT_APPROVE_RESULT_REJECT'] ?></label>
                            </div>
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 审批建议 -->
                    <div class="form-group" class="form-control" style="margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_REPORT_APPROVE_ADVICE'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <textarea name="approve_advice" class="form-control" id="approve_advice" cols="30"
                                    rows="10"></textarea>
                                <span class="help-block ">

                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 抄送人员 -->
                    <div class="form-group" id="add_copy" style="margin-left: -100px;">
                        <div class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_COPY_SEND_USER'] ?>
                        </div>
                        <div class="col-md-8 mt5" style=" margin-top: 3px;">
                            <span class="cs-info"></span>
                        </div>
                    </div>
                    
                    <!-- 添加抄送 -->
                    <div class="form-group" id="add_copy" style="margin-left: -100px;">
                        <div class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_ADD_COPY_SEND_USER'] ?>
                        </div>
                        <div class="col-md-8" style=" margin-top: 3px;">
                            <input type="checkbox" id="add_copy_switch" class="make-switch" data-on-color="primary"
                                data-off-color="info" data-size="small"
                                data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="right"
                                data-content="<?php echo $LANG['UI_GMP_APPROVAL_NEED_COPY_SEND_USER_OR_USER_GROUP'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                    <div class="copy-send-div display-none">
                        <!-- 选择用户 -->
                        <div class="form-group" style="margin-left: -100px;">
                            <label class="control-label col-md-4">
                                <?php echo $LANG['UI_GMP_APPROVAL_SELECT_COPY_SEND_USER2'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="bootstrap-mutiple-select selectpicker show-tick" multiple data-live-search="true"
                                    data-actions-box="true" id="user" name="user">
                                </select>
                            </div>
                        </div>

                        <!-- 选择用户组 -->
                        <div class="form-group" style="margin-left: -100px;">
                            <label class="control-label col-md-4">
                                <?php echo $LANG['UI_GMP_APPROVAL_SELECT_COPY_SEND_USER_GROUP'] ?>
                            </label>
                            <div class="col-md-8">
                                <select class="bootstrap-mutiple-select selectpicker show-tick" multiple data-live-search="true"
                                    data-actions-box="true" id="user_group" name="user_group">

                                </select>
                            </div>
                        </div>

                        <!-- 是否开启通知 -->
                        <div class="form-group" id="notice" style="margin-left: -100px;">
                            <div class="control-label col-md-4">
                                <?php echo $LANG['UI_GMP_APPROVAL_OPEN_NOTICE_OR_NOT'] ?>
                            </div>
                            <div class="col-md-8" style=" margin-top: 3px;">
                                <input type="checkbox" id="copy_switch" class="make-switch" data-on-color="primary"
                                    data-off-color="info" data-size="small"
                                    data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                    data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                                <a class="popovers ml15" data-container="body" data-trigger="hover"
                                    data-placement="right" data-content="<?php echo $LANG['UI_GMP_APPROVAL_OPEN_EMAIL_NOTICE_OR_NOT'] ?>">
                                    <i class="viconfont vicon-tishi"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" style="margin-right: 12px;" id="approve_submit"
                            class="btn green-haze btn-confirm submit">
                            <?php echo $LANG['UI_PUBLIC_YES'] ?>
                        </button>
                        <button type="button" id="" data-dismiss="drawer" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 审批弹窗end -->

<!-- 查看审批流start -->
<div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
    aria-labelledby="drawer-1-title" id="approval_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-2-title"><i class="viconfont vicon-shenpiliu"></i>
                <?php echo $LANG['UI_GMP_APPROVAL_NAME'] ?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                        class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">

            <!-- BEGIN FORM-->
            <form action="#" id="approval_form" class="form-horizontal">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>

                    </div>

                    <!-- 所属报告 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_REPORT'] ?>
                        </label>
                        <div class="col-md-8 mt5">
                            <span id="report_name"></span>
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 审批流程 -->
                    <div class="form-group" class="form-control" style="margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_NAME'] ?>
                        </label>
                        <div class="col-md-8 mt5">
                            <div style="display: flex; align-items: center;">
                                <div id="depth_div">

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" data-dismiss="drawer" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 查看审批流end -->

<!--更改审批流start -->
<div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
    aria-labelledby="drawer-1-title" id="edit_approval_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-4-title"><i class="viconfont vicon-genggaishenpiliu"></i>
                <?php echo $LANG['UI_GMP_APPROVAL_CHANGE'] ?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                        class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">

            <!-- BEGIN FORM-->
            <form action="#" id="edit_approval_form" class="form-horizontal">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>

                    </div>

                    <!-- 所属报告 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_REPORT'] ?>
                        </label>
                        <div class="col-md-8 mt5">
                            <span id="report_name_edit"></span>
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 审批流 -->
                    <div class="form-group" class="form-control" style="margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_NAME'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control select2me" id="approve_list" name="approve_list">
                                <option value="0">
                                    <?php echo $LANG['UI_PUBLIC_SELECT'] ?>
                                </option>
                            </select>
                        </div>
                    </div>

                </div>

            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" style="margin-right: 12px;" id="edit_approval_submit"
                            class="btn green-haze btn-confirm submit">
                            <?php echo $LANG['UI_PUBLIC_YES'] ?>
                        </button>
                        <button type="button" data-dismiss="drawer" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 更改审批流end -->

<!--更改审批人start -->
<div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
    aria-labelledby="drawer-1-title" id="edit_user_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-5-title"><i class="viconfont vicon-genggaishenpiliu"></i>
                <?php echo $LANG['UI_GMP_APPROVAL_CHANGE'] ?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                        class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">

            <!-- BEGIN FORM-->
            <form action="#" id="edit_user_form" class="form-horizontal">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>
                    </div>

                    <!-- 所属报告 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_REPORT'] ?>
                        </label>
                        <div class="col-md-8 mt5">
                            <span id="report_name_edit_user"></span>
                        </div>
                    </div>

                    <!-- 更改审批人 -->
                    <div class="form-group" class="form-control" style="margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_USER_CHANGE'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="form-control select2me" id="edit_user_list" name="edit_user_list">
                                <option value="0">
                                    <?php echo $LANG['UI_PUBLIC_SELECT'] ?>
                                </option>
                            </select>
                        </div>
                    </div>

                </div>

            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" style="margin-right: 12px;" id="edit_user_submit"
                            class="btn green-haze btn-confirm submit">
                            <?php echo $LANG['UI_PUBLIC_YES'] ?>
                        </button>
                        <button type="button" data-dismiss="drawer" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 更改审批人end -->

<!-- 分享drawer start -->
<div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
    aria-labelledby="drawer-1-title" id="share_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-3-title"><i class="viconfont vicon-shenpi"></i>
                <?php echo $LANG['UI_GMP_REPORT_SHARE'] ?>
            </h4>
        </div>
        <div class="drawer-body">

            <!-- BEGIN FORM-->
            <form action="#" id="share_form" class="form-horizontal">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>

                    </div>

                    <!-- 所属报告 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_REPORT'] ?>
                        </label>
                        <div class="col-md-8 mt5">
                            <span id="report_name_share"></span>
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 分享用户 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_REPORT_SHARE_USER'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="bootstrap-mutiple-select selectpicker show-tick" multiple data-live-search="true"
                                data-actions-box="true" id="share_user" name="share_user">
                            </select>
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 分享用户组 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_REPORT_SHARE_USER_GROUP'] ?>
                        </label>
                        <div class="col-md-8">
                            <select class="bootstrap-mutiple-select selectpicker show-tick" multiple data-live-search="true"
                                data-actions-box="true" id="share_user_group" name="share_user_group">
                            </select>
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 是否开启通知 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;" id="notice">
                        <div class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_OPEN_NOTICE_OR_NOT'] ?>
                        </div>
                        <div class="col-md-8" style=" margin-top: 3px;">
                            <input type="checkbox" id="notice_switch" class="make-switch" data-on-color="primary"
                                data-off-color="info" data-size="small"
                                data-on-text="<?php echo $LANG['UI_PUBLIC_ON'] ?>"
                                data-off-text="<?php echo $LANG['UI_PUBLIC_OFF'] ?>">
                            <a class="popovers ml15" data-container="body" data-trigger="hover" data-placement="left"
                                data-content="<?php echo $LANG['UI_GMP_APPROVAL_OPEN_EMAIL_NOTICE_OR_NOT'] ?>">
                                <i class="viconfont vicon-tishi"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </form>
            <!-- END FORM-->
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" style="margin-right: 12px;" id="share_submit"
                            class="btn green-haze btn-confirm submit">
                            <?php echo $LANG['UI_PUBLIC_YES'] ?>
                        </button>
                        <button type="button" id="" data-dismiss="drawer" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 分享drawer end -->

<!-- BEGIN COMMENT MODAL -->
<div id="comment_modal" class="modal xmodal fade form-horizontal" style="z-index: 10055;margin-top: 15%;">
    <div class="modal-header ">
        <button type="button" class="close" data-dismiss="modal"></button>
        <h4 class="modal-title"><i class="viconfont vicon-a-Eyesyanjing"></i>
            <?php echo $LANG['UI_GMP_REPORT_COMMENT_VIEW'] ?>
            <span class="own_approver" style="color: #999999;">（<?php echo $LANG['UI_GMP_REPORT_OWN_APPROVAL_USER'] ?>：<span class="name"
                    style="color: #666666;"></span>）</span>
        </h4>
    </div>
    <div class="modal-body" style="overflow: auto;">
    <div class="table-container">
                        <!-- <div class="vin_toolbar" id="vin_copy_send_toolbar">
                            <div class="leftTool">
                                <div class='customBtn1'></div>
                            </div>

                            <div class="rightTool">
                                <div class="vin_btnToolbar">
                                </div>
                            </div>
                        </div> -->
                        <table id="cs_table">
                        </table>
                    </div>
                    
        
    </div>

    <div class="modal-footer">
        <div class="form-actions">
            <div class="row">
                <div class="col-md-7" style="float: right;padding-right: 20px;">
                    <button type="button" class="btn default cancel">
                        <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- END COMMENT MODAL -->

<script src="./scripts/platform/industry/report_list_pending.js"></script>
<script src="./scripts/platform/industry/search.js"></script>
<script>
    var reportSearch1 = reportSearch();
    reportSearch1.init({'type': 'report-pending-search','level':0})
</script>
