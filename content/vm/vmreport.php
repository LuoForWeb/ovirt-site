<?php include_once '../../tpl/permission.php';?>
<?php include_once $_SESSION['ROOTPATH'] . 'content/platform/public/bs_table.php'; ?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" type="text/css">
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<link href="./assets/global/plugins/datatables/extensions/Buttons/css/buttons.dataTables.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/daterangepicker/daterangepicker.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<style>
    #vm_table {
        table-layout: fixed;
    }

    #vm_table td {
        word-wrap: break-word;
    }

    #vm-report .fixed-table-container .fixed-table-body {
        height: 600px;
        overflow: visible !important;
    }
</style>
<div class="row" id="vm-report">
    <input type="hidden" id="subModuleType" value="<?php echo $_GET['sub_module_type'] ?? 1 ?>">
	<div class="col-md-12">
		<!-- Begin: life time stats grey-cararra -->
		<div class="portlet box blue-hoki" >
			<div class="portlet-title">
				<div class="caption">
					<i class="levelchild viconfont vicon-vm_overview"></i><?php echo $LANG['UI_VCENTER_MACHINE_LIST']?>
				</div>
				<div class="tools">
							</div>
			</div>
			<div class="portlet-body margin10">
			
				<div class="table-toolbar">
					<div class="vin_toolbar" id="vm-report-toolbar">
						<div class="leftTool" style="display: flex;">
                            <div class="search input-group mr12">
                                <input type="search" maxlength="128" id="search" class="searchinput customSearch"
                                       autocomplete="off"
                                       style="padding-right:32px;min-width: 220px;"
                                       placeholder="<?php echo $LANG['UI_VM_REPORT_SEARCH_TIPS']?>">
                                <div class="position0" style="width:auto;height:34px">
                                    <button class="b-btn clear hide position0" id="clearSearchBtn">
                                        <i class="icon-close-small"></i></button>
                                </div>
                                <div class="search-btn positionL0" style="width:auto;height:34px;">
                                    <button class="b-btn search-btn" id="searchbtn"><i class="icon-search"></i></button>
                                </div>
                            </div>
							<div class="btn-group">
								<button type="button" id="addtojob" class="btn table-toolbar-btn">
								<i class="viconfont vicon-biaogetianjia"></i> <?php echo $LANG['UI_VM_REPORT_ADD_TO_BACKUP']?>
								</button>
							</div>
						    <div class="btn-group">
						        <button type="button" id="createjob" class="btn table-toolbar-btn">
								<i class="viconfont vicon-xinjianbeifen1" style="font-weight: bold"></i> <?php echo $LANG['UI_VM_REPORT_CREATE_JOB']?>
								</button>
						    </div>
						</div>
						<div class="rightTool" style="display: flex;justify-content: flex-end">
                            <button type="button" id="searchAll" class="btn btn-primary adv_btn brr2 p-lr8 list">
                                <i class="adv-search"></i>
                                <span style="color: #FFFFFF"><?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH'] ?></span>
                            </button>
                            <div class="vm_report_toolbar ml5">
                            </div>
						</div>
					</div>
                    <div class="col-md-12">
                        <div id="searchDiv" class="searchDiv display-none" style="margin-top: 12px;">
                            <?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION'] ?>
                            <span class="searchContent"></span>
                            <span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR'] ?></span>
                        </div>
                    </div>
				</div>
				<div class="table-container">
					<table id="vm_table" data-export-types="['excel','csv']"></table>
				</div>
			</div>
		</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="modaldiv" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		    <input id="vmuuid" class="display-none"></input>
		    <input id="vcenteruuid" class="display-none"></input>
		    <input id="vmshowtype" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="levelchild viconfont vicon-ge_add_task"></i> <?php echo $LANG['WEB_VM_VCENTER_ADD_VM_TO_TASK']?></h4>
			</div>
			<div class="modal-body">
                <div class="form-group vmnameDiv">
			        <label class="col-md-3 control-label "><?php echo $LANG['UI_VCENTER_MACHINE_NAME']?></label>
                	<div class="col-md-8 margintop10" id="vmname">
                	</div>
                </div>
                <div class="form-group">
			        <label class="col-md-3 control-label"><?php echo $LANG['UI_VCENTER_ADD_TO_TASK']?><span class="required"> * </span></label>
                	<div class="col-md-8">
                		<select class="form-control select2me" id="task">
						</select>
                		<span class="help-block">
                		<?php echo $LANG['UI_VCENTER_ADD_TO_TASK_TIPS']?> </span>
                	</div>
                </div>
                <div class="alert alert-danger display-none" id="modaltips">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <ul class="alert-ul">
                        <strong class="alert-ul-head"><?php echo $LANG['UI_PUBLIC_TIPS']?>:</strong>
                        <li>
                            <?php echo $LANG['UI_VCENTER_ADD_TO_TASK_WARNING_TIPS']?>
                        </li>
                    </ul>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button" class="btn btn-primary" id="submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
		<!-- BEGIN SEARCH MODAL -->
		<div id="searchmodal" class="modal xmodal fade form-horizontal " tabindex="-1"  data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
				</h4>
			</div>
			<div class="modal-body">
    			<div class="portlet-body">
    				
    				<!-- 上次备份时间范围 -->
    			    <div class="list-option" id="startTimeDiv">
    					<div class="row">
    						<label class="control-label col-md-2"><?php echo $LANG['UI_VM_REPORT_LAST_TIME']?> :
    						</label>
    						<div class="col-md-6 daterangepickerdiv">
                                <input type="text" id="daterangepickerVmReport" class="form-control" autocomplete="off">
                                <i class="viconfont vicon-ge_calendar"></i>
    						</div>
    					</div>
    				</div>
    				
    			    <div class="list-option">
    					<div class="row">
    						<!-- 虚拟化类型 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_VCENTER_TYPE']?> :
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="vmtype" name="vmtype">
								</select>
							</div>
							

							<!-- 虚拟化中心 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_PLATFORM_VCENTER']?> :
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="vcenterSelect" >
								</select>
							</div>
    					</div>
    				</div>
    				
    				<div class="list-option">
    					<div class="row">
							<!-- 虚拟机名 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_VCENTER_MACHINE_NAME']?> :
							</label>
							<div class="col-md-4">
								<input style="width:235px; height:34px;" id="vmName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>

							<!-- 集群名 -->
							<label class="control-label col-md-2"><?php echo $LANG['WEB_VM_TREE_OVIRT_CLUSTER_FOLDER']?> :
							</label>
							<div class="col-md-4">
								<input style="width:235px; height:34px;" id="vmCluster" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
							
    					</div>
    				</div>
    				
    				<div class="list-option">
    					<div class="row">
							<!-- 备份状态 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_VM_REPORT_BACKUP_STATUS']?> :
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="backupFlag">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
									<option value="1"><?php echo $LANG['UI_VM_REPORT_IN_BACKUP']?></option>
									<option value="2"><?php echo $LANG['UI_VM_REPORT_NOIN_BACKUP']?></option>
								</select>
							</div>

							<!-- 备份任务名 -->
							<div id="taskNameDiv">
								<label class="control-label col-md-2"><?php echo $LANG['UI_VM_REPORT_BACKUP_TASK']?> :
								</label>
								<div class="col-md-4">
									<input style="width:235px; height:34px;" id="taskName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
								</div>
							</div>
							
    					</div>
    				</div>
    				
    				<div class="list-option">
    					<div class="row">
							<!-- 虚拟机IP -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_VM_REPORT_IP']?> :
							</label>
							<div class="col-md-4">
								<input style="width:235px; height:34px;" id="vmIP" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>

                            <!-- 宿主机/租户 -->
                            <label class="control-label col-md-2"><?php echo $LANG['UI_VM_REPORT_HOST_TENANT']?> :
                            </label>
                            <div class="col-md-4">
                                <input style="width:235px; height:34px;" id="hostName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
                            </div>
    					</div>
    				</div>
    				
    				
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button" class="btn btn-primary" id="serach_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
			</div>
		</div>	
		<!-- END SEARCH MODAL -->
		
		
	</div>
</div>
<!-- END PAGE CONTENT-->

<!--导出（p_vm_overview_export）、打印（p_vm_overview_print）权限控制-->
<input type="hidden" id="show_p_vm_overview_export" value="<?php echo (in_array('p_vm_overview_export', $_SESSION['permissionArr']) ? 1: 0);?>"/>
<input type="hidden" id="show_p_vm_overview_print" value="<?php echo (in_array('p_vm_overview_print', $_SESSION['permissionArr']) ? 1 : 0);?>"/>


<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-select/bootstrap-select.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Scroller/js/dataTables.scroller.min.js"></script>

<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>

<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/jszip.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/pdfmake.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/vfs_fonts.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/extensions/Buttons/js/buttons.print.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/moment.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="./assets/global/plugins/daterangepicker/daterangepicker.locales.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/vm/vmreport.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	