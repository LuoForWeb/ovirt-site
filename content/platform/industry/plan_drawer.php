<style>
    .basic-plan-info{
        margin-bottom: 20px;
    }
    .basic-info-title{
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }
    .basic-info-title .decoration{
        background-color: #2A87C8;
        display: inline-block;
        width: 4px;
        height: 14px;
        border-radius: 2px !important;
    }
    .basic-info-title .basic_title__text{
        font-size: 14px;
        color: #333333;
        font-family: Microsoft YaHei UI, Microsoft YaHei UI;
        font-weight: 400;
        text-align: left;
        height: 22px;
        line-height: 22px;
    }
    .basic-group__form__item{
        margin-bottom: 12px;
        display: flex;
        font-size: 14px;
    }
    .basic-group__form__item .basic-item-title{
        padding-left: 0;
        height: 22px;
        font-family: Microsoft YaHei UI, Microsoft YaHei UI;
        font-weight: 400;
        font-size: 14px;
        color: #666666;
        line-height: 22px;
    }
    .basic-group__form__item .basic-item-value{
        height: 22px;
        font-family: Microsoft YaHei UI, Microsoft YaHei UI;
        font-weight: 400;
        font-size: 14px;
        color: #333333;
        line-height: 22px;
        text-align: left;
        font-style: normal;
        text-transform: none;
    }
    .content-group__form{
        font-family: Microsoft YaHei UI, Microsoft YaHei UI;
        font-weight: 400;
        font-size: 14px;
        color: #333333;
        line-height: 24px;
        font-style: normal;
        text-transform: none;
    }
    .item-plan-right{
        background: rgba(42, 135, 200, 0.1);
        border-radius: 2px !important;
    }
    .item-plan-right:hover{
        background: rgba(42,135,200,0.2);
    }
    .item-plan-right i{
        color: #4295CE;
    }
    .drawer-title-text{
        height: 20px;
        display: inline-block;
        font-family: Microsoft YaHei UI, Microsoft YaHei UI;
        font-weight: 400;
        font-size: 16px;
        color: #333333;
        text-align: center;
        font-style: normal;
        text-transform: none;
        margin-left: 5px;
    }
    #drawer-plan-title{
        margin-top: 0;
    }
    #drawer-1-title{
        margin-top: 0;
        padding-left: 0;
    }
    .col-md-offset-6{
        padding-right: 0;
    }
</style>
<!-- 查看子版本弹窗start -->
<div style="width: 1000px; z-index: 10051;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" data-backdrop="pointListDrawer" id="more_version_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title"><i class="viconfont vicon-a-View-grid-listliebiaochakanmoshi"></i>
                <span class="drawer-title-text"><?php echo $LANG['UI_GMP_REPORT_PLAN_VERSION_TITLE'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                            class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">

            <table id="plan_table_version"></table>

        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" id="" data-dismiss="drawer" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 查看子版本弹窗end -->

<!-- 新建-修改-复制drawer start -->
<div style="width: 800px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" id="plan_add_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-plan-title"><i class="title-i viconfont vicon-danchuangtianjia1"></i>
                <span id="drawer-plan-titles" class="drawer-title-text"><?php echo $LANG['UI_GMP_REPORT_PLAN_ADD_TITLE'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                            class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">

            <!-- BEGIN FORM-->
            <form action="#" id="share_form" class="form-horizontal">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>

                    </div>

                    <!-- 方案名称 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <span class="required">*</span>
                            <?php echo $LANG['UI_GMP_REPORT_PLAN_TITLE'] ?>
                        </label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="plan_name" id="plan_name" placeholder="" />
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 编号 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_PUBLIC_NUMBER'] ?>
                        </label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" name="serial_number" id="serial_number" placeholder="" />
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 审批流 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <span class="required">*</span>
                            <?php echo $LANG['UI_VERIFY_APPROVAL'] ?>
                        </label>
                        <div class="col-md-6">
                            <select class="form-control select2me"  id="approval_uuid" name="approval_uuid">
                                <option value="0">
                                    <?php echo $LANG['UI_PUBLIC_SELECT'] ?>
                                </option>
                            </select>
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 内容 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;" id="plan_content_div">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_REPORT_PLAN_CONTENT'] ?>

                        </label>
                        <div class="col-md-6">
                            <textarea id="content_editor"></textarea>

                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                        <button class="btn b-btn item-plan-right" title="<?php echo $LANG['UI_PLATFORM_INDUSTRY_TEMPLATE_TOOL_TIPS']?>" type="button">
                            <i class="viconfont vicon-gongjuyincang"></i>
                        </button>
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
                        <button type="button" id="plan_submit"
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
<!-- 新建-修改-复制drawer end -->

<!-- 查看详情drawer start -->
<div style="width: 800px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" id="plan_look_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title"><i class="viconfont vicon-xiangqing"></i>
                <span class="drawer-title-text"><?php echo $LANG['UI_INDUSTRY_PLAN_DETAIL'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                            class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">
            <div class="basic-plan-info">
                <!-- START TIME STRATEGY -->
                <div class="basic-info-title">
                    <span class="decoration me-8"></span>
                    <span class="basic_title__text"><?php echo $LANG['UI_PUBLIC_BASE_INFO']?></span>
                </div>
                <div class="basic-group__form">
                    <!-- 方案名称 -->
                    <div class="basic-group__form__item">
                        <div class="col-md-3 basic-item-title">
                            <?php echo $LANG['UI_GMP_REPORT_PLAN_TITLE']?>
                        </div>
                        <div id="plan_name_look" class="col-md-6 basic-item-value"></div>
                    </div>

                    <!-- 编号 -->
                    <div class="basic-group__form__item">
                        <div class="col-md-3 basic-item-title">
                            <?php echo $LANG['UI_PUBLIC_NUMBER']?>
                        </div>
                        <div id="plan_serial_number_look" class="col-md-6 basic-item-value"></div>
                    </div>
                    <!-- 版本号 -->
                    <div class="basic-group__form__item">
                        <div class="col-md-3 basic-item-title">
                            <?php echo $LANG['UI_VCENTER_VERSION']?>
                        </div>
                        <div id="plan_version_look" class="col-md-6 basic-item-value"></div>
                    </div>
                    <!-- 审批流 -->
                    <div class="basic-group__form__item">
                        <div class="col-md-3 basic-item-title">
                            <?php echo $LANG['WEB_INDUSTRY_APPROVAL']?>
                        </div>
                        <div id="plan_approval_uuid_look" class="col-md-6 basic-item-value"></div>
                    </div>

                </div>

                <!-- END STORAGE STRATEGY -->
            </div>
            <div class="content-plan-info">
                <!-- START TIME STRATEGY -->
                <div class="basic-info-title">
                    <span class="decoration me-8"></span>
                    <span class="basic_title__text"><?php echo $LANG['UI_GMP_REPORT_PLAN_CONTENT']?></span>
                </div>
                <div class="content-group__form" id="plan_content_look">
                    <!-- 方案内容 -->

                </div>

                <!-- END STORAGE STRATEGY -->
            </div>

        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" id="" data-dismiss="drawer" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 查看详情drawer end -->

<!-- 审批弹窗start -->
<div style="width: 600px; z-index: 10051;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" id="approve_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-1-title"><i class="viconfont vicon-shenpi"></i>
                <span class="drawer-title-text">审批方案</span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                            class="viconfont vicon-guanbi"></i></span>
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
                                <label class="passDiv" style="padding-right: 20px;">
                                    <input type="checkbox" id="passCheck" data-checkbox="icheckbox_square-blue" data-mode="1"
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
                    <div class="form-group display-none" id="add_copy" style="margin-left: -100px;">
                        <div class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_COPY_SEND_USER'] ?>
                        </div>
                        <div class="col-md-8 mt5" style=" margin-top: 3px;">
                            <span class="cs-info"></span>
                        </div>
                    </div>

                    <!-- 添加抄送 -->
                    <div class="form-group display-none" id="add_copy" style="margin-left: -100px;">
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
                        <button type="button" id="approve_submit"
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
                <span class="drawer-title-text"><?php echo $LANG['UI_GMP_APPROVAL_NAME'] ?></span>
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

                    <!-- 所属方案 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_PLAN'] ?>
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
                <span class="drawer-title-text"><?php echo $LANG['UI_GMP_APPROVAL_CHANGE'] ?></span>
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
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_PLAN'] ?>
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
                        <button type="button" id="edit_approval_submit"
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
                <span class="drawer-title-text"><?php echo $LANG['UI_GMP_APPROVAL_USER_CHANGE'] ?></span>
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
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_PLAN'] ?>
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
                        <button type="button" id="edit_user_submit"
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
                <span class="drawer-title-text"><?php echo $LANG['WEB_PLATFORM_INDUSTRY_PLAN_SHARE'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                            class="viconfont vicon-guanbi"></i></span>
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
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_PLAN'] ?>
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
                        <button type="button" id="share_submit"
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

<!-- 查看评论drawer start -->
<div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" id="comment_modal">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-3-title"><i class="viconfont vicon-a-Eyesyanjing"></i>
                <span class="drawer-title-text"><?php echo $LANG['UI_GMP_REPORT_COMMENT_VIEW'] ?></span>
                <span class="own_approver" style="color: #999999;">（<?php echo $LANG['UI_GMP_REPORT_OWN_APPROVAL_USER'] ?>：
                    <span class="name" style="color: #666666;"></span>）</span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                            class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">
            <table id="cs_table"></table>
        </div>
        <!-- footer -->
        <div class="drawer-footer">
            <div class="form-actions">
                <div class="row">
                    <div class="col-md-offset-6 col-md-7">
                        <button type="button" id="" data-dismiss="drawer" class="btn default cancel">
                            <?php echo $LANG['UI_PUBLIC_OFF_TWO'] ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 查看评论drawer end -->

<!-- 评论弹窗start -->
<div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" id="remark_drawer">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-remark-title"><i class="viconfont vicon-shenpi"></i>
                <span class="drawer-title-text"><?php echo $LANG['UI_GMP_REPORT_SUBMIT_COMMENT'] ?></span>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i
                            class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">

            <!-- BEGIN FORM-->
            <form action="#" id="remark_form" class="form-horizontal">
                <div class="form-body">
                    <div class="alert alert-danger display-hide">
                        <button type="button" class="close" data-close="alert"></button>

                    </div>

                    <!-- 所属方案 -->
                    <div class="form-group" style="margin-top: 15px;margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_APPROVAL_OWN_PLAN'] ?>
                        </label>
                        <div class="col-md-8 mt5">
                            <span id="report_name_remark"></span>
                            <div>
                                <span class="help-block ">
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 评论内容 -->
                    <div class="form-group" class="form-control" style="margin-left: -100px;">
                        <label class="control-label col-md-4">
                            <?php echo $LANG['UI_GMP_REPORT_COMMENT_CONTENT'] ?>
                        </label>
                        <div class="col-md-8">
                            <div class="input-icon right">
                                <i class="fa"></i>
                                <textarea name="approve_advice" class="form-control" id="remark_content" cols="30"
                                          rows="10"></textarea>
                                <span class="help-block ">

                                </span>
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
                        <button type="button" id="remark_submit"
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
<!-- 评论弹窗end -->

<input type="hidden" id="nowAction" value="0">
<input type="hidden" id="planUUid" value="0">
<input type="hidden" id="approvalUuid" value="0">
<input type="hidden" id="planType" value="1">
<input type="hidden" id="planLevel" value="0">