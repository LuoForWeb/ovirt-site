
<div class="vin_toolbar" id="vin_report_archived_toolbar">
    <div class="leftTool">
    </div>

    <div class="table-toolbar-wrapper__right">
        <div class="page-right">
            <button type="button" id="searchAll1" class="btn btn-primary adv_btn brr2 p-lr8">
                <i class="adv-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?>
            </button>
        </div>
    </div>
</div>

<div id="searchDiv1" class="search-content searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?>
    <span class="searchContent1 searchContent"></span>
    <span class="clearSearch1 clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
</div>

<table id="archived_report_table"></table>


<!-- BEGIN SEARCH MODAL -->
<div id="searchmodal1" class="modal xmodal fade form-horizontal" tabindex="-1" data-focus-on="input:first"
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
                        <input style="width:235px; height:34px;" id="report_title1" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>

                    <!-- 关联任务 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_REPORT_TASK_VOL'] ?>：</label>
                    <div class="col-md-4">
                        <input style="width:235px; height:34px;" id="report_task1" type="text" maxlength="64"
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
                        <input style="width:235px; height:34px;" id="report_agent1" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>

                    <!-- 所属模板 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_GMP_REPORT_REPORT_TEMPLATE'] ?>：</label>
                    <div class="col-md-4">
                        <select id="report_template1" class="form-control">
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
                id="serach_submit1"><?php echo $LANG['UI_PUBLIC_YES'] ?></button>
    </div>
</div>
<!-- END SEARCH MODAL -->

<!-- 查看审批流start -->
<div style="width: 600px;" class="drawer slide" data-placement="right" tabindex="-1" role="dialog"
     aria-labelledby="drawer-1-title" id="approval_drawer2">
    <div class="drawer-content drawer-content-scrollable" role="document">
        <div class="drawer-header">
            <h4 class="drawer-title" id="drawer-2-title"><i class="viconfont vicon-shenpiliu"></i>
                <?php echo $LANG['UI_GMP_APPROVAL_NAME'] ?>
                <span data-dismiss="drawer" aria-label="Close" class="drawer-close "><i class="viconfont vicon-guanbi"></i></span>
            </h4>
        </div>
        <div class="drawer-body">

            <!-- BEGIN FORM-->
            <form action="#" id="approval_form2" class="form-horizontal">
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
                            <span id="report_name2"></span>
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
                        <div class="col-md-8">
                            <div style="display: flex; align-items: center;">
                                <div id="depth_div2">

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

<script src="./scripts/platform/industry/report_list_archived.js"></script>
<script>
    var reportSearch2 = reportSearch();
    reportSearch2.init({'type': 'report-archived-search','level':1})
</script>
