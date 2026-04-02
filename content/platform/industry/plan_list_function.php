<!-- Begin: life time stats grey-cararra -->
<div class="vin_toolbar" id="vin_plan_function_toolbar">
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

<table id="plan_function_table"></table>

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
                    <!-- 方案名称 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_GMP_NAME_DESC'] ?>：</label>
                    <div class="col-md-4">
                        <input style="width:235px; height:34px;" id="plan_title1" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>

                    <!-- 编号 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_PUBLIC_NUMBER'] ?>：</label>
                    <div class="col-md-4">
                        <input style="width:235px; height:34px;" id="plan_num1" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>
                </div>
            </div>

            <div class="list-option">
                <div class="row">
                    <!-- 版本 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_LOGIN_AGENT_VERSION'] ?>：</label>
                    <div class="col-md-4">
                        <input style="width:235px; height:34px;" id="plan_version1" type="text" maxlength="64"
                               class="table-group-action-input form-control input-inline input input-sm"
                               aria-controls="example">
                    </div>

                    <!-- 审批流 -->
                    <label class="control-label col-md-2 vmbackdata-padding_en"><?php echo $LANG['UI_VERIFY_APPROVAL'] ?>：</label>
                    <div class="col-md-4">
                        <select id="plan_approval1" class="form-control">
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

<script>
    $(function (){
        var searchInstance2 = planSearch();
        searchInstance2.init({'type': 'plan-function-search','level':1})

        var listInstance2 = planList();
        listInstance2.init({'level':1})
    })
</script>
