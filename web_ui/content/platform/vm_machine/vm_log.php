<link rel="stylesheet" type="text/css" href="./css/platform/vm_machine/vm_log.css" />
<!-- BEGIN PAGE CONTENT-->
<div class="row" id="vm_log_list">
    <div class="">
        <div class="portlet box blue-hoki" style="border: none;">
            <!-- Begin: life time stats grey-cararra -->
            <div class="portlet-body mlr10">

                <div class="table-toolbar" style="margin-bottom: 0px;">
                    <div class="vin_toolbar" id="vin_current_log_toolbar">
                        <div class="leftTool leftTool_log">

                        </div>

                        <div class="rightTool">
                            <div class="vin_log_btnToolbar"></div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table id="vm_log_table"></table>
                </div>
            </div>

            <!-- BEGIN SEARCH MODAL -->
            <div id="vm_search_log_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <input id="task_vcenteruuid" class="display-none"></input>
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <!-- 日志时间范围 -->
                        <div class="list-option" id="startTimeLogDiv">
                            <div class="row">
                                <label class="control-label col-md-4"><?php echo $LANG['UI_JOB_TIME_RANGE']?>
                                </label>
                                <div class="col-md-5 daterangepickerdiv" >
                                    <input type="text" id="daterangepickerLog" class="form-control" autocomplete="off">
                                    <i class="viconfont vicon-ge_calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- 所在节点 -->
                                <label class="control-label col-md-4"><?php echo $LANG['UI_STORAGE_IN_NODE1']?>
                                </label>
                                <div class="col-md-5">
                                    <select class="form-control select2me" id="nodeSelectLog" >
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- 任务名 -->
                                <label class="control-label col-md-4"><?php echo $LANG['UI_JOB_RNAME']?>
                                </label>
                                <div class="col-md-5">
                                    <input id="taskNameLog" type="text" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                </div>
                            </div>
                        </div>

                        <div class="list-option">
                            <div class="row">
                                <!-- 操作名 -->
                                <label class="control-label col-md-4"><?php echo $LANG['UI_VM_MACHINE_LOG_OPERATE_USER_NAME']?>
                                </label>
                                <div class="col-md-5">
                                    <input id="item_name" type="text" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- 状态 -->
                                <label class="control-label col-md-4"><?php echo $LANG['UI_PUBLIC_STATUS']?>
                                </label>
                                <div class="col-md-5">
                                    <select id="logstatus" class="table-group-action-input form-control">
                                        <option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
                                        <option value="1"><?php echo $LANG['WEB_PLATFORM_DES_NORMAL']?></option>
                                        <option value="2"><?php echo $LANG['WEB_PUBLIC_FAILURE_HOMEPAGE']?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                    <button type="button" class="btn btn-primary" id="current_search_log_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                </div>
            </div>
            <!-- END SEARCH MODAL -->
        </div>

        <!-- End: life time stats -->
    </div>
</div>


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/vm_machine/vm_log.js"></script>
<!-- END PAGE LEVEL PLUGINS -->