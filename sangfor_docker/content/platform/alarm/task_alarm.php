<?php include_once '../../../tpl/permission.php';?>
<!-- BEGIN PAGE LEVEL STYLES -->
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css" rel="stylesheet" type="text/css"/>
<link href="./assets/global/plugins/bootstrap-modal/css/bootstrap-modal.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css"/>
<link rel="stylesheet" type="text/css" href="./assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"/>
<!-- END PAGE LEVEL STYLES -->
<!-- BEGIN PAGE HEADER-->
<!-- END PAGE HEADER-->
<!-- BEGIN PAGE CONTENT-->
<div class="row">
	<div class="col-md-12">
		<!-- Begin: life time stats -->
		<div class="portlet-body">
			    <div class="table-toolbar">
					<div class="row">
						<div class="col-md-4">
							<div class="btn-group">
								<button id="deletetaskalarm" class="btn btn-sm green-haze">
								<i class="fa fa-trash-o"></i> <?php echo $LANG['UI_PUBLIC_DELETE']?>
								</button>
							</div>
							<div class="btn-group">
								<button id="solvetaskalarm" class="btn btn-sm green-haze">
								<i class="fa fa-check"></i> <?php echo $LANG['UI_ALARM_RESPONSE_DO']?>
								</button>
							</div>
						</div>
						<div class="col-md-8">
							<div class="table-actions-wrapper page-right">
								<select id="taskalarmlevelselect" class="table-group-action-input form-control input-inline input-small input-sm">
									<option value=""><?php echo $LANG['UI_PUBLIC_ALL']?></option>
									<option value="3"><?php echo $LANG['WEB_PLATFORM_DES_ERROR']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_WARNING']?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_GENERAL']?></option>
								</select>
								<input style="margin-top:1px;" type="search" maxlength="128" class="searchinput table-group-action-input form-control input-inline input input-sm" placeholder="<?php echo $LANG['UI_JOB_SEARCH_JOB_NAME']?>" aria-controls="example">
								<input id="task_searchbtn" type="button" class="btn btn-sm default" value="<?php echo $LANG['UI_PUBLIC_SEARCH']?>">
								<button id="task_searchAll" class="btn btn-sm green-haze">
								<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
								</button>
							</div>
						</div>
						
						<div class="col-md-12">
							<div id="task_searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']?> <span class="searchContent"></span><span class="clearSearch"><?php echo $LANG['UI_SEARCH_CLEAR']?></span> </div>
						</div>
					</div>
				</div>
				
				<div class="table-container">
					<table class="table table-striped table-bordered table-hover" id="taskAlarm">
					<thead>
					<tr role="row" class="heading">
						<th width="2%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="5%">
							<?php echo $LANG['UI_PUBLIC_NUMBER']?>
						</th>
						<th width="15%">
							<?php echo $LANG['UI_JOB_RNAME']?>
						</th>
						<th width="5%">
							 <?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>
						</th>
						<th width="5%">
							 <?php echo $LANG['UI_ALARM_LEVLE']?>
						</th>
						<th width="15%">
							 <?php echo $LANG['UI_ALARM_TIME']?>
						</th>
						<th width="40%">
							<?php echo $LANG['UI_ALARM_CONTENT']?>
						</th>
						<th width="8%">
							<?php echo $LANG['UI_ALARM_RESPONSE_FLAG']?>
						</th>
						<th width="10%">
							<?php echo $LANG['UI_ALARM_DETAILS']?>
						</th>
					</tr>
					</thead>
					<tbody>
					</tbody>
					</table>
				</div>
			</div>
		<!-- End: life time stats -->
		
		<!-- BEGIN MODAL -->
		<div id="taskalarmmodaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-bell"></i> <?php echo $LANG['UI_ALARM_DETAILS']?></h4>
			</div>
			<div class="modal-body max-height500 row">
			    <div class="tabbable-custom max-height500">
            		<ul class="nav nav-tabs ">
            			<li id="baseinfotab"class="active">
            				<a href="#baseinfo_tab" data-toggle="tab" aria-expanded="false">
            				<i class="fa fa-info-circle font-green-seagreen"></i> <?php echo $LANG['UI_ALARM_BASE_INFO']?></a>
            			</li>
            			<li class="" id="vminfotab">
            				<a href="#vminfo_tab" data-toggle="tab" aria-expanded="false">
            				<i class="fa fa-info-circle font-green-seagreen"></i> <?php echo $LANG['UI_JOB_VM_INFO']?> </a>
            			</li>
            			<li class="" id="logtab">
            				<a href="#loginfo_tab" data-toggle="tab" aria-expanded="false">
            				<i class="fa fa-edit font-green-seagreen"></i> <?php echo $LANG['UI_ALARM_LOG_INFO']?> </a>
            			</li>
            		</ul>
            		<div class="tab-content alaramDetails">
            			<div class="tab-pane active" id="baseinfo_tab">
            			    <div class="row static-info">
            			        <div class="col-md-4 name textalignr">
            						 <?php echo $LANG['UI_ALARM_ID']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmid">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_JOB_RNAME']?>:
            					</div>
                            	<div class="col-md-8 value" id="taskname">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?>:
            					</div>
                            	<div class="col-md-8 value" id="taskmodule">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_PUBLIC_TASK_TYPE']?>:
            					</div>
                            	<div class="col-md-8 value" id="tasktype">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['WEB_PLATFORM_DES_NODE']?>:
            					</div>
                            	<div class="col-md-8 value" id="tasknode">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_PALTFORM_STORAGE']?>:
            					</div>
                            	<div class="col-md-8 value" id="taskstorage">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_ALARM_LEVLE']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmlevel">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_ALARM_TIME']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmtime">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_ALARM_CONTENT']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmcontent">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_ALARM_RESPONSE_FLAG']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmsolveddes">
                            	</div>
                            </div>
                            <div class="row static-info solveddiv">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_ALARM_RESPONSE_USER']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmsolveduser">
                            	</div>
                            </div>
                            <div class="row static-info solveddiv">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_ALARM_RESPONSE_TIME']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmsolvedtime">
                            	</div>
                            </div>
                            <div class="row static-info">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_SETTINGS_NOTICE_EMAIL']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmemail">
                            	</div>
                            </div>
                            <div class="row static-info display-none">
                            	<div class="col-md-4 name textalignr" >
            						 <?php echo $LANG['UI_SETTINGS_NOTICE_SMS']?>:
            					</div>
                            	<div class="col-md-8 value" id="alarmsms">
                            	</div>
                            </div>
            			</div>
            			
            			<div class="tab-pane" id="vminfo_tab" style="height: 420px; overflow-y:auto; background: #fff; padding: 20px;">
            				<div class="table-container">
            					<table class="table table-striped table-bordered table-hover" id="vminfo_table">
            					<thead>
            					<tr role="row" class="heading">
            						<th width="10%">
            							<?php echo $LANG['UI_PUBLIC_NUMBER']?>
            						</th>
            						<th width="70%">
            							<?php echo $LANG['UI_VCENTER_MACHINE_NAME']?>
            						</th>
            						<th width="20%">
            							<?php echo $LANG['UI_PUBLIC_STATUS']?>
            						</th>
            					</tr>
            					</thead>
            					<tbody>
            					</tbody>
            					</table>
            				</div>
            			</div>
            			
            			<div class="tab-pane" id="loginfo_tab">
        			        <!-- BEGIN FORM-->
                            <form action="#" id="" class="form-horizontal mt10">
                            	<div class="form-body">
                            		<div class="form-group">
                            		    <div class="col-md-12">
                            			    <div class="changelog-list" id="tasklog" style="height: 420px; overflow-y:auto; border: 1px solid #ddd; background: #fff; padding: 20px;">
                         
                                            </div>
                            			</div>
									</div>
                            	</div>
                            </form>
                            <!-- END FORM-->
            			</div>
            		</div>
            	</div>
                
			</div>
			<div class="modal-footer">
			    <button type="button" class="btn green-haze" id="tadownloadbtn"><?php echo $LANG['UI_ALARM_LOG_DOWNLOAD']?></button>
				<button type="button" class="btn green-haze" id="tasolvedbtn"><?php echo $LANG['UI_ALARM_RESPONSE_NO_DO']?></button>
				<button type="button" data-dismiss="modal" class="btn btn-primary"><?php echo $LANG['UI_PUBLIC_CLOSE']?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
		<!-- BEGIN SEARCH MODAL -->
		<div id="task_searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		    <input id="vcenteruuid" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
				</h4>
			</div>
			<div class="modal-body">
    			<div class="portlet-body">
    				
    				<!-- 日志时间范围 -->
    			    <div class="list-option" id="startTimeDiv">
    					<div class="row">
    						<label class="control-label col-md-2"><?php echo $LANG['UI_ALARM_TIME_RANGE']?> ：
    						</label>
    						<div class="col-md-4" >
    						    <div class="input-group date form_datetime">
    								<input type="text" size="16" readonly id="startTime" class="form-control">
    								<span class="input-group-btn">
    									<button class="btn default" id="resetStartTime" type="button"><i class="fa fa-times"></i></button>
	    								<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
    								</span>
    							</div>
    						</div>
    						<div class="col-md-4" >
    						    <div class="input-group date form_datetime">
    								<input type="text" size="16" readonly id="endTime" class="form-control">
    								<span class="input-group-btn">
    									<button class="btn default" id="resetEndTime" type="button"><i class="fa fa-times"></i></button>
	    								<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
    								</span>
    							</div>
    						</div>
    					</div>
    				</div>
    				<div class="list-option">
    					<div class="row">
							
							<!-- 所在节点 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_STORAGE_IN_NODE']?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="nodeSelect" >
								</select>
							</div>
							
							<!-- 告警级别 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_ALARM_LEVLE']?> ：
							</label>
							<div class="col-md-4">
								<select id="alarmLevel" class="table-group-action-input form-control input-inline input-small input-sm">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_GENERAL']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_WARNING']?></option>
									<option value="3"><?php echo $LANG['WEB_PLATFORM_DES_ERROR']?></option>
								</select>
							</div>
    					</div>
    				</div>
    				
    				<div class="list-option">
    					<div class="row">
    						<!-- 模块类型 -->
    						<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_MODULE_TYPE']?>  ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="moduletype">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_VM']?></option>
									<option value="3"><?php echo $LANG['WEB_PLATFORM_DES_FS']?></option>
									<option value="9"><?php echo $LANG['UI_PLATFORM_COPY_OR_ARCHIVE']?></option>
								</select>
							</div>
							<!-- 任务名 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_JOB_RNAME']?> ：
							</label>
							<div class="col-md-4">
								<input style="width:235px; height:34px;" id="taskName" type="text" maxlength="64" class="table-group-action-input form-control input-inline input input-sm" aria-controls="example">
							</div>
							
    					</div>
    				</div>
    				
    				 <div class="list-option">
    					<div class="row">
    						<!-- 任务类型 -->
							<label class="control-label col-md-2"><?php echo $LANG['UI_PUBLIC_TASK_TYPE']?> ：
							</label>
							<div class="col-md-4">
								<select class="form-control select2me" id="vmTasktype">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_BACKUP']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY']?></option>
									<option value="7"><?php echo $LANG['WEB_PLATFORM_DES_INSTANT_RECOVERY']?></option>
									<option value="8"><?php echo $LANG['WEB_PLATFORM_DES_MOTION']?></option>
								</select>
								<select class="form-control select2me display-none" id="fsTasktype">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_BACKUP']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_RECOVERY']?></option>
								</select>
								<select class="form-control select2me display-none" id="copyTasktype">
									<option value="0"><?php echo $LANG['UI_PUBLIC_ALL']?></option>
									<option value="17"><?php echo $LANG['UI_PLATFORM_BACKUP_COPY']?></option>
									<option value="18"><?php echo $LANG['UI_COPY_BACK']?></option>
									<option value="19"><?php echo $LANG['UI_PLATFORM_ARCHIVE']?></option>
									<option value="20"><?php echo $LANG['UI_PLATFORM_ARCHIVE_FETCH']?></option>
								</select>
							</div>
    						<!-- 虚拟化类型 -->
    						<div class="display-none" id="vmTypeDiv">
	    						<label class="control-label col-md-2"><?php echo $LANG['UI_VCENTER_TYPE']?> ：
								</label>
								<div class="col-md-4">
									<select class="form-control select2me" id="vmtype" name="vmtype">
									</select>
								</div>
							</div>
							
    					</div>
    				</div>
    				
    				
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button"class="btn btn-primary" id="task_serach_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
			</div>
		</div>	
		<!-- END SEARCH MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/alarm/task_alarm.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	