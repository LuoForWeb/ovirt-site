<!-- BEGIN PAGE CONTENT-->
<div class="row">
    <div class="">
        <div class="portlet box blue-hoki" style="border: none;">
            <!-- Begin: life time stats grey-cararra -->
            <div class="portlet-body mlr10">

                <div class="table-toolbar" style="margin-bottom: 0px;">
                    <div class="vin_toolbar" id="vin_current_toolbar">
                        <div class="leftTool leftTool_vm">

                        </div>

                        <div class="rightTool">
                            <div class="vin_btnToolbar"></div>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table id="vm_list_table"></table>
                </div>
            </div>

            <!-- BEGIN SEARCH MODAL -->
            <div id="vm_searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <input id="task_vcenteruuid" class="display-none"></input>
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="">
                        <!-- 日志时间范围 -->
                        <div class="list-option hide" id="startTimeDiv">
                            <div class="row">
                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_TIME_RANGE']?></label>
                                <div class="col-md-7 daterangepickerdiv" >
                                    <input type="text" id="daterangepickerTaskAlarm" class="form-control" autocomplete="off">
                                    <i class="viconfont vicon-ge_calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- 所在节点 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_BACKUP_DATA_REPORT_NODE_NAME']?></label>
                                <div class="col-md-7">
                                    <select class="form-control select2me" id="nodeSelect" >
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="list-option">
                            <div class="row">
                                <!-- 任务名 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_JOB_RNAME']?></label>
                                <div class="col-md-7">
                                    <input id="taskName" type="text" maxlength="64" class="table-group-action-input form-control" aria-controls="example">
                                </div>
                            </div>
                        </div>

                        <div class="list-option">
                            <div class="row">
                                <!-- 状态 -->
                                <label class="control-label col-md-3"><?php echo $LANG['UI_PUBLIC_STATUS']?></label>
                                <div class="col-md-7">
                                    <select id="vmstatus" class="table-group-action-input form-control">
                                        <option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
                                        <option value="1"><?php echo $LANG['UI_VM_MACHINE_ONLINE']?></option>
                                        <option value="2"><?php echo $LANG['UI_VM_MACHINE_NOTONLINE']?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                    <button type="button" class="btn btn-primary" id="current_search_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                </div>
            </div>
            <!-- END SEARCH MODAL -->


            <!--- 虚拟机信息--->
            <div id="vm_machine_modal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
                <input id="task_vcenteruuid" class="display-none"></input>
                <div class="modal-header ">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title"><i class="viconfont vicon-a-Editbianji c0FBF98"></i> <?php echo $LANG['UI_PUBLIC_MODIFY']?>
                    </h4>
                </div>
                <div class="modal-body">
                    <div>
                        <?php include_once(ROOT_PATH . '/content/platform/vm_machine/machine.php'); ?>
                    </div>
                </div>

                <!-- modal-footer -->
                <div class="modal-footer">
                    <button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
                    <button type="button" class="btn btn-primary" id="current-vm-machine-submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
                </div>
            </div>
            <!--- 虚拟机信息 end--->
        </div>

        <!-- End: life time stats -->
    </div>
</div>


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./scripts/platform/vm_machine/vm_list.js"></script>
<!-- END PAGE LEVEL PLUGINS -->