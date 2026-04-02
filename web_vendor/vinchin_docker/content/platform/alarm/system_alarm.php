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
								<button id="deletesystemalarm" class="btn btn-sm green-haze">
								<i class="fa fa-trash-o"></i> <?php echo $LANG['UI_PUBLIC_DELETE']?>
								</button>
							</div>
							<div class="btn-group">
								<button id="solvesystemalarm" class="btn btn-sm green-haze">
								<i class="fa fa-check"></i> <?php echo $LANG['UI_ALARM_RESPONSE_DO']?>
								</button>
							</div>
						</div>
						
						<div class="col-md-8">
							<div class="table-actions-wrapper page-right">
								<select id="systemalarmlevelselect" class="table-group-action-input form-control input-inline input-small input-sm">
									<option value=""><?php echo $LANG['UI_PUBLIC_ALL']?></option>
									<option value="3"><?php echo $LANG['WEB_PLATFORM_DES_ERROR']?></option>
									<option value="2"><?php echo $LANG['WEB_PLATFORM_DES_WARNING']?></option>
									<option value="1"><?php echo $LANG['WEB_PLATFORM_DES_GENERAL']?></option>
								</select>
								<button id="system_searchAll" class="btn btn-sm green-haze">
								<i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
								</button>
							</div>
						</div>
						
						<div class="col-md-12">
							<div id="system_searchDiv" class="searchDiv display-none"><?php echo $LANG['UI_PUBLIC_SEARCH_CONDITION']?> <span class="searchContent"></span><span class="clearSearch">清除筛选</span> </div>
						</div>
					</div>
				</div>
				
				<div class="table-container">
					<table class="table table-striped table-bordered table-hover" id="systemAlarm">
					<thead>
					<tr role="row" class="heading">
						<th width="2%">
							<input type="checkbox" class="group-checkable">
						</th>
						<th width="5%">
							<?php echo $LANG['UI_PUBLIC_NUMBER']?>
						</th>
						<th width="6%">
							 <?php echo $LANG['UI_ALARM_LEVLE']?>
						</th>
						<th width="14%">
							 <?php echo $LANG['UI_ALARM_TIME']?>
						</th>
						<th width="40%">
							<?php echo $LANG['UI_ALARM_CONTENT']?>
						</th>
						<th width="10%">
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
		<div id="systemalarmmodaldiv" class="modal xmodal fade form-horizontal " tabindex="-1" data-focus-on="input:first" data-backdrop="static">
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="icon-bell"></i> <?php echo $LANG['UI_ALARM_DETAILS']?></h4>
			</div>
			<div class="modal-body">
				<div class="row static-info">
			        <div class="col-md-4 name textalignr">
						 <?php echo $LANG['UI_ALARM_ID']?>:
					</div>
                	<div class="col-md-8 value" id="alarmid">
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
			<div class="modal-footer">
				<button type="button" class="btn green-haze" id="sasolvedbtn"><?php echo $LANG['UI_ALARM_RESPONSE_NO_DO']?></button>
				<button type="button" data-dismiss="modal" class="btn btn-primary"><?php echo $LANG['UI_PUBLIC_CLOSE']?></button>
			</div>
		</div>	
		<!-- END MODAL -->
		
		<!-- BEGIN SEARCH MODAL -->
		<div id="system_searchmodal" class="modal xmodal fade form-horizontal high-search" tabindex="-1" data-focus-on="input:first" data-backdrop="static">
		    <input id="vcenteruuid" class="display-none"></input>
			<div class="modal-header ">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title"><i class="fa fa-search"></i> <?php echo $LANG['UI_PUBLIC_ADVANCED_SEARCH']?>
				</h4>
			</div>
			<div class="modal-body">
    			<div class="portlet-body">
    				
    				<div class="list-option">
    					<div class="row">
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
    				
    				<!-- 日志时间范围 -->
    			    <div class="list-option" id="startTimeDiv">
    					<div class="row">
    						<label class="control-label col-md-2"><?php echo $LANG['UI_ALARM_TIME_RANGE']?> ：
    						</label>
    						<div class="col-md-4">
    						    <div class="input-group date form_datetime">
    								<input type="text" size="16" readonly id="startTime" class="form-control">
    								<span class="input-group-btn">
	    								<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
    								</span>
    							</div>
    						</div>
    						<div class="col-md-4">
    						    <div class="input-group date form_datetime">
    								<input type="text" size="16" readonly id="endTime" class="form-control">
    								<span class="input-group-btn">
	    								<button class="btn default date-set" type="button"><i class="fa fa-calendar"></i></button>
    								</span>
    							</div>
    						</div>
    					</div>
    				</div>
    				
    				
				</div>
			</div>
			
			<!-- modal-footer -->
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn btn-default"><?php echo $LANG['UI_PUBLIC_NO']?></button>
				<button type="button"class="btn btn-primary" id="system_serach_submit"><?php echo $LANG['UI_PUBLIC_YES']?></button>
			</div>
		</div>	
		<!-- END SEARCH MODAL -->
		
	</div>
</div>
<!-- END PAGE CONTENT-->
	

<!-- BEGIN PAGE LEVEL PLUGINS -->
<!-- BEGIN PAGE LEVEL PLUGINS -->
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/bootstrap-datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="./assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="./scripts/plugins/datatable.js"></script>
<script type="text/javascript" src="./scripts/platform/alarm/system_alarm.js"></script>
<!-- END PAGE LEVEL PLUGINS -->	